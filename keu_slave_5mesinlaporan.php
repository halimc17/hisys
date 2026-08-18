<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
error_reporting(0);

// $method=$_POST['method'];
// $param=$_POST;

$method = checkPostGet('method','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}


// print_r($param);

// exit("Error:asd");

$str = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where tipe='HOLDING' and induk=''";
$res=fetchdata($str);
foreach($res as $bar){
    @$optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}  

$sudahada=[];
$optakun=$opttipeunit=$optkodejurnal=$opttipekode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$bla=false;
if($param['nmLaporan']=='CASHFLOW' or $param['namaLaporanDt']=='CASHFLOW' or  $param['nmLaporan']=='CASH FLOW'  or $param['namaLaporanDt']=='CASH FLOW'){	
	$str = "SELECT noaruskas as noakun,nama_aruskas as namaakun FROM " . $dbname . ".keu_5aruskas where level>1";
	$nmakun=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas');
    $bla=true;	
}else{
	$str = "SELECT * FROM " . $dbname . ".keu_5akun where length(noakun)=7 and aktif='1' ";
	$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
	
	if($param['namaLaporanDt']!=''){
		$sql = "SELECT * FROM " . $dbname . ".keu_5mesinlaporandt where namalaporan='".$param['namaLaporanDt']."'";
		$res=fetchdata($sql);
		foreach($res as $bar){
			$namaurut[$bar['nourut']]=$bar['keterangandisplay'];
		}
		$sql = "SELECT * FROM " . $dbname . ".keu_5mesinlaporandt_akun where namalaporan='".$param['namaLaporanDt']."'";
		$res=fetchdata($sql);
		foreach($res as $bar){
			$sudahada[$bar['noakun']]=$bar['nourut'];
		}
	}
}
$res=fetchdata($str);
$d=$n='';
foreach($res as $bar){
    if($bla){

        $d=$bar['noakun'];

        // exit ("ERROR 1:  ".$d." - ".$n." + ".substr($d,3,2));

	    if($d!=$n && substr($d,3,2)=='00'){		
            if($d!=''){
	    	    $optakun.="</optgroup>";
            }	
            //exit ("ERROR :  <optgroup label='".$d." - ".$nmakun[$d]."'>");
	    	$optakun.= "<optgroup label='".$d." - ".$nmakun[$d]."'>";
            $n=$d;
	    } else {
            $optakun.="<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " - " . $nmakun[$bar['noakun']] . "</option>";
        }
    } else {
        $d=substr($bar['noakun'],0,3);
	    if($d!=$n){			
	    	$optakun.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
	    }
    
	    $e=substr($bar['noakun'],0,5);
	    if($e!=$m){			
	    	$optakun.="<optgroup label='".$e." - ".getNamaAkun($e)."'>";
	    }
	    $ada="";
	    if($sudahada[$bar['noakun']]!=''){
	    	$ada=" (".$sudahada[$bar['noakun']].")";
	    }
        $optakun.="<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " - " . $bar['namaakun'] . "".$ada."</option>";
	    $m=$e;
	    if($e!=$m){			
	    	$optakun.="</optgroup>";
	    }
	    $n=$d;
	    if($d!=$n){			
	    	$optakun.="</optgroup>";
	    }
    }
	
}

$str = "SELECT distinct(tipe) as tipe FROM " . $dbname . ".organisasi where length(kodeorganisasi)=4";
$res=fetchdata($str);
foreach($res as $bar){
	$opttipeunit.="<option value=" . $bar['tipe'] . ">" . $bar['tipe'] . "</option>";
}

@$optposisi.="<option value='1'>1</option>";
@$optposisi.="<option value='-1'>-1</option>";


// $optdetailtampil="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optdetailtampil="<option value='0'>" . $_SESSION['lang']['tidak'] . "</option>";
$optdetailtampil.="<option value='1'>" . $_SESSION['lang']['ya'] . "</option>";
$arrdetailtampil=array("0"=>"","1"=>"Y");
// echo"<pre>";
// print_r($param);
// echo"</pre>";

$opttipekode.="<option value='realisasi'>" . $_SESSION['lang']['realisasi'] . "</option>";
$opttipekode.="<option value='budget'>" . $_SESSION['lang']['anggaran'] . "</option>";


 $arrtipe=getEnum($dbname,'keu_5mesinlaporandt','tipe');
        foreach($arrtipe as $kei=>$fal){
            // if($fal=='Header'){
                // continue;
            // }
            @$optTipe.="<option value='".$fal."'>".$fal."</option>";
        }

$str = "SELECT distinct(jurnalid) as jurnalid,keterangan FROM " . $dbname . ".keu_5parameterjurnal";
$res=fetchdata($str);
foreach($res as $bar){
	$namajurnal[$bar['jurnalid']]=$bar['keterangan'];
}

$str = "SELECT * FROM " . $dbname . ".bgt_kode";
$res=fetchdata($str);
foreach($res as $bar){
	$namajurnal[$bar['kodebudget']]=$bar['nama'];
}	


switch ($method) {
	
	case'getkodejurnal':
		$optkodejurnal="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		if($param['tipe3']=='realisasi'){
			$str = "SELECT distinct(jurnalid) as jurnalid,keterangan FROM " . $dbname . ".keu_5parameterjurnal";
			$res=fetchdata($str);
			foreach($res as $bar){
				$optkodejurnal.="<option value=" . $bar['jurnalid'] . ">" . $bar['jurnalid'] . " - " . $bar['keterangan'] . "</option>";
			}
		}
		
		if($param['tipe3']=='budget'){
			$str = "SELECT * FROM " . $dbname . ".bgt_kode";
			$res=fetchdata($str);
			foreach($res as $bar){
				$optkodejurnal.="<option value=" . $bar['kodebudget'] . ">" . $bar['kodebudget'] . " - " . $bar['nama'] . "</option>";
			}
		}
		echo $optkodejurnal;
	
	break;
	
	
	case'exceldetail':
		$tab.="<table border=1>";
		$tab.="
		<tr class=rowheader>
			 <td  align=center>".$_SESSION['lang']['nourut']."</td>
			 <td  align=center>".$_SESSION['lang']['nama']."</td>
			 <td  align=center>".$_SESSION['lang']['noakun']."</td>
			 <td  align=center>".$_SESSION['lang']['namaakun']."</td>
			 <td  align=center>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['unit']."</td>
		</tr>";
		if($param['nmLaporan']=='CASHFLOW' or $param['namaLaporanDt']=='CASHFLOW' or  $param['nmLaporan']=='CASH FLOW'  or $param['namaLaporanDt']=='CASH FLOW'){	
			$nmakun=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas');	
		}else{
			$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');	
		}
		$str="select keterangandisplay,tipeunit,nourut from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$param['namalaporan']."' and tipe='Detail' order by nourut asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$strdt = "select * from ".$dbname.".keu_5mesinlaporandt_akun  where nourut='".$bar['nourut']."' and namalaporan='".$param['namalaporan']."' order by noakun asc ";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$tab.="
				<tr class=rowcontent>	
				 <td  align=center>".$bar['nourut']."</td>
				 <td  align=left>".$bar['keterangandisplay']."</td>
				 <td  align=center>".$bardt['noakun']."</td>
				 <td  align=left>".$nmakun[$bardt['noakun']]."</td>
				 <td  align=left>".$bar['tipeunit']."</td>
				</tr>";
			}
		}
		$tab.="</table>";
		// exit("Error:".$tab);
		#===================================
		#===================================
		#===================================
		#===================================
		$nop = "".$param['namalaporan'].".xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("detail", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
	break;

	
    case 'loadData':
		$where="where aktif=1";
        if (@$param['namalaporan']!= '') {
            $where.=" and  namalaporan like '%" . $param['namalaporan'] . "%' ";
        }
        
        $limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".keu_5mesinlaporanht  ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=3>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }
        else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_5mesinlaporanht ".$where."  limit ".$offset.",".$limit."";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
				$no++;
                $tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$bar->namalaporan."</td>";
					$tab.="<td>".$bar->ket1."</td>";
					$tab.="<td>".getNamaKaryawan($bar->createby)." ".waktunormal($bar->createtime)."</td>";
					$tab.="<td>".getNamaKaryawan($bar->updateby)." ".waktunormal($bar->updatetime)."</td>";
					$tab.="<td align=center><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"editht('".$bar->namalaporan."','".$bar->ket1."')\"></td>";
					$tab.="<td align=center><img src=images/excel.jpg class=resicon caption='Excel' title='Excel' onclick=\"exceldetail('".$bar->namalaporan."');\"></td>";
                $tab.="</tr>";
            }

            $totrows=ceil($jlhbrs/$limit);
            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=7  align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;
    case'insertht':
	// print_r($param);
    if($param['nmLaporan']==''){
        exit('warning: '.$_SESSION['lang']['namalaporan'].' '.$_SESSION['lang']['notifobligatory']);
    }
	
	if($param['kdHo']==''){
        exit('warning: Unit kantor pusat masih kosong');
    }
	
	if($param['ket1']==''){
        exit('warning: Keterangan laporan masih kosong');
    }
    $rCek=array();
    $sCek="select * from ".$dbname.".keu_5mesinlaporanht where namalaporan = '".$param['nmLaporan']."'";
    $rCek=fetchdata($sCek);
    if(count($rCek)!=0){
		$sInsert="update ".$dbname.".keu_5mesinlaporanht set ket1='".$param['ket1']."',updateby='".$_SESSION['standard']['userid']."',updatetime='".date('Y-m-d H:i:s')."' where kodeorg='".$param['kdHo']."' and namalaporan='".$param['nmLaporan']."'";
		try {
			$owlPDO->exec($sInsert);
		} catch (PDOException $e) {
			print " Gagal: " . $e->getMessage() . "\n".$sInsert;
			die();
		}
    }else{
		$sInsert="insert into ".$dbname.".keu_5mesinlaporanht (kodeorg,namalaporan,periode,ket1,aktif,createby,updateby,createtime,updatetime) values "; //indra
		$sInsert.="('".$param['kdHo']."','".$param['nmLaporan']."','bulanan','".$param['ket1']."','1','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."')";
		try {
			$owlPDO->exec($sInsert);
		} catch (PDOException $e) {
			print " Gagal: " . $e->getMessage() . "\n".$sInsert;
			die();
		}
	}
        
	
    break;
    case'detail':
        $tab="";
        
        // $tab.="<fieldset style=width:400px;float:left><legend>".$_SESSION['lang']['detail']."</legend>";
        $tab.="<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0>";
		$tab.="<tr>";
			$tab.="<td>".$_SESSION['lang']['namalaporan']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><input type=text id=namaLaporanDt disabled=disabled style='width:195px' class=myinputtext value='".$param['nmLaporan']."' /></td>";
			
			$tab.="<td>".$_SESSION['lang']['keterangan']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><input type=text id=ketdata style='width:195px' class=myinputtext onkeypress='return tanpa_kutip(event)' /></td>";
			
			$tab.="<td>".$_SESSION['lang']['tampilkan']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><select id=tampildt  style='width:200px'>".$optdetailtampil."</select></td>";
		
		$tab.="</tr>";
		
        $tab.="<tr>";
		
			$tab.="<td>".$_SESSION['lang']['tipe']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><select id=tipeDt  style='width:200px'>".$optTipe."</select></td>";
			
			$tab.="<td>".$_SESSION['lang']['total']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><input type=text id=datadt style='width:195px' class=myinputtext onkeypress='return tanpa_kutip(event)' /></td>";	
			
			$tab.="<td>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['unit']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><select id=tipeunitdt  style='width:200px'>".$opttipeunit."</select></td>";
			
        $tab.="</tr>";

        $tab.="<tr>";
       
			$tab.="<td>".$_SESSION['lang']['urutan']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><input type=text id=nourut style='width:195px' class=myinputtextnumber onkeypress='return angka_doang(event)' /></td>";
			
			$tab.="<td>".$_SESSION['lang']['detail']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><select id=detaildt  style='width:200px'>".$optdetailtampil."</select></td>";
			
			$tab.="<td>+/-</td>";
			$tab.="<td>:</td>";
			$tab.="<td><select id=posisidt  style='width:200px'>".$optposisi."</select></td>";
        
		$tab.="</tr>";	
		
        
		
		
		
		$tab.="<tr hidden><td>Colspan</td>";
        $tab.="<td>:</td>";
        $tab.="<td><input type=text id=colspandt style='width:195px' class=myinputtextnumber onkeypress='return angka_doang(event)' /></td></tr>";

		
        // $optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        // $sAkun="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7 order by noakun asc";
        // $rAkun=fetchdata($sAkun);
        // foreach($rAkun as $kei=>$fal){
        //         $optAkun.="<option value='".$fal['noakun']."'>".$fal['noakun']."-".$fal['namaakun']."</option>";
        // }  
        // $tab.="<td><select id=noakundari  style='width:200px'>".$optAkun."</select></td></tr>";

        // $optAkun2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        // $sAkun2="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7 order by noakun asc";
        // $rAkun2=fetchdata($sAkun2);
        // foreach($rAkun2 as $kei=>$fal){
        //         $optAkun2.="<option value='".$fal['noakun']."'>".$fal['noakun']."-".$fal['namaakun']."</option>";
        // } 
        // $tab.="<tr><td>".$_SESSION['lang']['noakunsampai']."</td>";
        // $tab.="<td>:</td>";
        // $tab.="<td><select id=noakunsampai  style='width:200px'>".$optAkun2."</select></td></tr>";
        // $tab.="<tr><td>".$_SESSION['lang']['noakundari']."</td>";
        // $tab.="<td>:</td>";
        // $tab.="<td><select id=noakunsampai  style='width:200px'>".$optAkun2."</select></td></tr>";
        $tab.="<tr><td colspan=2>&nbsp;</td>";
        $tab.="<td colspan><button class=mybutton style='cursor:pointer;' onclick='saveDetHead()'>".$_SESSION['lang']['save']."</button></td></tr>";
        $tab.="</table>
		<input hidden id=oldNourut /><input hidden  id=methoddt /></fieldset>";
		//type=hidden
		
		/*
		$tab.="<fieldset><legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['detail']."</legend>";
        $tab.="<li>Tipe :</li>";
        $tab.="<ol>Header : Untuk <b>Header</b> berupa judul</ol>";
        $tab.="<ol>Detail : Untuk <b>Detail</b> berupa rincian dari header, dan di-isikan isi dari nomor akun pendukungnya dengan menekan tombol <img src='images/skyblue/zoom.png' class='zImgBtn'></ol>";
        $tab.="<ol>Total : Untuk <b>Total</b> berupa total dari detail</ol>";
		$tab.="<li>No Urut : Urutan didalam laporan, harap membuat spare nourut dengan total, agar jika ada penambahan dapat ditempatkan ber-urutan</li>";
		$tab.="<li>Keterangan : Nama laporan</li>";
		$tab.="<li>Total : Jika tipe-nya adalah <b>total</b>, maka kolom ini berisikan <b>No. Urut</b> yang akan dijumlahkan, dan diberi tanda pemisah berupa <b>, (koma)</b></li>";
		$tab.="<li>Colspan : Berupa merge kolom, untuk membentuk laporan sub-total dengan grand-total, sub-total isikan 2, grand total isikan 0</li>";
        
		$tab.="</fieldset>";
		$tab.="<div style=clear:both></div>";
		$tab.="<hr>";
		*/
                          
        $tab.="<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
        $tab.="<div class='table-scroll'>";
        $tab.="<table cellspacing=1 cellpadding=5 border=0 class=sortable>";
        $tab.="<thead><tr class=rowheader align=center>";
        $tab.="<th>".$_SESSION['lang']['tipe']."</th>";
        $tab.="<th>".$_SESSION['lang']['urutan']."</th>";
        $tab.="<th>".$_SESSION['lang']['keterangan']."</th>";
        $tab.="<th>".$_SESSION['lang']['total']."</th>";
        $tab.="<th>".$_SESSION['lang']['detail']."</th>";
        $tab.="<th>".$_SESSION['lang']['tampilkan']."</th>";
        $tab.="<th>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['unit']."</th>";
		$tab.="<th>+/-</th>";
		$tab.="<th>" . $_SESSION['lang']['dbuat_oleh'] . "</th>";
		$tab.="<th>" . $_SESSION['lang']['perubahan'] . "</th>";
        $tab.="<th colspan=4>".$_SESSION['lang']['action']."</th></tr></thead><tbody>";
		
        $sDetData="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$param['nmLaporan']."' ";
        $rDetData=fetchdata($sDetData);
        foreach ($rDetData as $key => $val){
            $tab.="<tr class=rowcontent>";
            $tab.="<td>".$val['tipe']."</td>";
            $tab.="<td>".$val['nourut']."</td>";
            $tab.="<td nowrap>".$val['keterangandisplay']."</td>";
            $tab.="<td>".str_replace(",",", ",$val['noakundisplay'])."</td>";
            $tab.="<td align=center>".$arrdetailtampil[$val['detail']]."</td>";
            $tab.="<td align=center>".$arrdetailtampil[$val['tampil']]."</td>";
			 $tab.="<td>".$val['tipeunit']."</td>";
			 $tab.="<td>".$val['posisi']."</td>";
            // $tab.="<td>".$val['variableoutput']."</td>";
			$tab.="<td>".getNamaKaryawan($val['createby'])." ".waktunormal($val['createtime'])."</td>";
			// $tab.="<td></td>";
			$tab.="<td>".getNamaKaryawan($val['updateby'])." ".waktunormal($val['updatetime'])."</td>";
			// $tab.="<td></td>";
            $tab.="<td><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit ".$val['keterangandisplay']."' onclick=\"editdet('".$val['nourut']."','".$val['keterangandisplay']."','".$val['variableoutput']."','".$val['tipe']."','".$val['noakundisplay']."','".$val['detail']."','".$val['tampil']."','".$val['tipeunit']."','".$val['posisi']."')\"></td>";
            $tab.="<td><img src='images/skyblue/delete.png' class='zImgBtn' title='Delete ".$val['keterangandisplay']."' onclick=\"delDet('".$val['kodeorg']."','".$val['nourut']."','".$param['nmLaporan']."')\"></td>";
            // $tab.="<td><img src='images/skyblue/zoom.png' class='zImgBtn' title='Add Detail ".$val['keterangandisplay']."' onclick=\"viewdetail('".$val['kodeorg']."','".$val['nourut']."','".$param['nmLaporan']."')\"></td>";
			if($val['tipe']=='Detail'){
				$tab.="<td><img src='images/skyblue/zoom.png' class='zImgBtn' title='Add Detail COA/Aruskas".$val['keterangandisplay']."' onclick=\"viewdetailbaru('".$val['kodeorg']."','".$val['nourut']."','".$param['nmLaporan']."')\"></td>";
				$tab.="<td><img src='images/skyblue/zoom.png' class='zImgBtn' title='Add Detail Kodejurnal".$val['keterangandisplay']."' onclick=\"viewdetailbarukodejurnal('".$val['kodeorg']."','".$val['nourut']."','".$param['nmLaporan']."')\"></td>";
			}else{
				$tab.="<td></td>";
				$tab.="<td></td>";
			}
			$tab.="</tr>";
        }
        $tab.="</tbody></table></div>";
        $tab.="</fieldset>";
		
		
		
		
        echo $tab;
    break;
	
	

	
    case'saveDetHead':
		
        if($param['tipeDt']==''){
            exit('warning:'.$_SESSION['lang']['tipe']." ".$_SESSION['lang']['kosong']);            
        }
		if($param['ketdata']==''){
            exit('warning:'.$_SESSION['lang']['keterangan']." ".$_SESSION['lang']['header']." ".$_SESSION['lang']['kosong']);            
        }
        if($param['nourut']==''){
            exit('warning:'.$_SESSION['lang']['urutan']." ".$_SESSION['lang']['kosong']);            
        }
        // if($param['colspandt']==''){
            // exit('warning: Col Span'.$_SESSION['lang']['urutan']." ".$_SESSION['lang']['kosong']);            
        // }
        
		// #delete			
		// $hwr="nourut='".$param['oldNourut']."' and namalaporan='".$param['namaLaporanDt']."' and tipe='".$param['tipeDt']."' and kodeorg='".$param['kdOrg']."'";
        // $str="delete from ".$dbname.".keu_5mesinlaporandt where ".$hwr."";
		// try{
            // $owlPDO->exec($str);
        // } catch (PDOException $e) {
            // print " Gagal: " . $e->getMessage() . "\n".$str;
            // die();
        // }
		
		// echo"<pre>";
		// print_r($param);exit("Error:");
		
		if($param['methoddt']=='updatedt'){
			$str="update  ".$dbname.".keu_5mesinlaporandt set  nourut='".$param['nourut']."',tipe='".$param['tipeDt']."',noakundisplay='".$param['datadt']."',keterangandisplay='".$param['ketdata']."',detail='".$param['detaildt']."',tampil='".$param['tampildt']."',tipeunit='".$param['tipeunitdt']."',posisi='".$param['posisidt']."',updateby='".$_SESSION['standard']['userid']."',updatetime='".date('Y-m-d H:i:s')."' where nourut='".$param['oldNourut']."' and kodeorg='".$param['kdOrg']."' and namalaporan='".$param['namaLaporanDt']."'";
			try{
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				// print " Gagal: " . $e->getMessage() . "\n".$sUpdate;
				print " Gagal: " . $e->getMessage() . "\n Data nomor urut ".$param['nourut']." sudah ada ";
				die();
			}
			#= update
		}else{
			#= insert
			$str="insert into ".$dbname.".keu_5mesinlaporandt  (kodeorg,namalaporan,nourut,keterangandisplay,tipe,noakundisplay,detail,tampil,tipeunit,posisi,createby,updateby,createtime,updatetime) values ";
			$str.=" ('".$param['kdOrg']."','".$param['namaLaporanDt']."','".$param['nourut']."','".$param['ketdata']."','".$param['tipeDt']."','".$param['datadt']."','".$param['detaildt']."','".$param['tampildt']."','".$param['tipeunitdt']."','".$param['posisidt']."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."')";      
			try{
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal: " . $e->getMessage() . "\n Data nomor urut ".$param['nourut']." sudah ada ";
				// print " Gagal: " . $e->getMessage() . "\n".$str;
				die();
			}
			
		}
		    
		
    break;
    case'delDetHead':
        $hwr="nourut='".$param['nourut']."' and namalaporan='".$param['namaLaporanDt']."' and kodeorg='".$param['kdOrg']."'";
        // $sCek="select * from ".$dbname.".keu_5mesinlaporandt where ".$hwr."";
        // $rCek=fetchdata($sCek);
        // if(count($rCek)!=0){
            // exit('warning:'.$_SESSION['lang']['data'].' '.$_SESSION['lang']['detail'].' Di hapus dulu');
        // }
		
		$sDelA="delete from ".$dbname.".keu_5mesinlaporandt_akun where ".$hwr."";
		try{
            $owlPDO->exec($sDelA);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$sDelA;
            die();
        }
		
        $sDel="delete from ".$dbname.".keu_5mesinlaporandt where ".$hwr."";
        try{
            $owlPDO->exec($sDel);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$sDel;
            die();
        }
    break;
	
	
	
	
	
	
	
	
    case'viewdetail':
        $tab="";
        
        $tab.="<fieldset><legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['detail']."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0>";
        $tab.="<tr><td>".$_SESSION['lang']['namalaporan']."</td>";
        $tab.="<td>:</td>";
        $tab.="<td><input type=text id=namaLaporanDt disabled=disabled style='width:195px' class=myinputtext value='".$param['namaLaporanDt']."' /></td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['tipe']."</td>";
        $tab.="<td>:</td>";
       
        $tab.="<td><select id=tipeDtdt style='width:200px'>".$optTipe."</select></td></tr>";

        $tab.="<tr><td>".$_SESSION['lang']['urutan']."</td>";
        $tab.="<td>:</td>";
        $tab.="<td><input type=text id=nourutdt style='width:195px' class=myinputtext onkeypress='return angka_doang(event)' placeholder='No.urut Otomatis' readonly/></td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['keterangan']."</td>";
        $tab.="<td>:</td>";
        $tab.="<td><input type=text id=ketdt style='width:195px' class=myinputtext onkeypress='return tanpa_kutip(event)' /></td></tr>";
        $optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sAkun="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7 and aktif='1' order by noakun asc";
        $rAkun=fetchdata($sAkun);
        foreach($rAkun as $kei=>$fal){
                $optAkun.="<option value='".$fal['noakun']."'>".$fal['noakun']."-".$fal['namaakun']."</option>";
        }  
        $tab.="<tr><td>".$_SESSION['lang']['noakundari']."</td>";
        $tab.="<td>:</td>";
        $tab.="<td><select id=noakundari  style='width:200px'>".$optAkun."</select></td></tr>";
        $optAkun2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sAkun2="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7 and aktif='1' order by noakun asc";
        $rAkun2=fetchdata($sAkun2);
        foreach($rAkun2 as $kei=>$fal){
                $optAkun2.="<option value='".$fal['noakun']."'>".$fal['noakun']."-".$fal['namaakun']."</option>";
        } 
        $tab.="<tr><td>".$_SESSION['lang']['noakunsampai']."</td>";
        $tab.="<td>:</td>";
        $tab.="<td><select id=noakunsampai  style='width:200px'>".$optAkun2."</select></td></tr>";
        $tab.="<tr><td>Colspan</td>";
        $tab.="<td>:</td>";
        $tab.="<td><input type=text id=colsdt style='width:195px' class=myinputtextnumber onkeypress='return angka_doang(event)' /></td></tr>";
        $tab.="<tr><td>Data</td>";
        $tab.="<td>:</td>";
        $tab.="<td><input type=text id=datadt style='width:195px' class=myinputtext onkeypress='return tanpa_kutip(event)' /></td></tr>";
        $optstatus="<option value='0'>".$_SESSION['lang']['pilihdata']."</option>";
        $optstatus.="<option value='1'>Sum Data</option>";
        $optstatus.="<option value='2'>".$_SESSION['lang']['pengecualian']."</option>";
        $tab.="<tr><td>Status</td>";
        $tab.="<td>:</td>";
        $tab.="<td><select id=statusdt  style='width:200px' onchange='ubahdt()'>".$optstatus."</select></td></tr>";
        $tab.="<tr><td>&nbsp;</td>";
        $tab.="<td colspan=2><button class=mybutton style='cursor:pointer;' onclick='saveDetDetail()'>".$_SESSION['lang']['save']."</button></td></tr>";
        $tab.="</table><input type=hidden id=nourutht value='".$param['nourut']."' /></fieldset>";
        //<input type=hidden id=oldNourutdt />
        $hwr="induk='".$param['nourut']."' and namalaporan='".$param['namaLaporanDt']."' and tipe<>'Header' and kodeorg='".$param['kdOrg']."'";
        $sCek="select * from ".$dbname.".keu_5mesinlaporandt where ".$hwr."";
        $rCek=fetchdata($sCek);
        if(!empty($rCek)){
            $tab.="<fieldset><legend>".$_SESSION['lang']['data']."</legend><table cellpadding=1 cellspacing=1 border=0 class=sortable>";
            $tab.="<thead><tr class=rowheader align=center>";
            $tab.="<td rowspan=2>".$_SESSION['lang']['urutan']."</td>";
            $tab.="<td rowspan=2>".$_SESSION['lang']['tipe']."</td>";
            $tab.="<td rowspan=2>".$_SESSION['lang']['keterangan']."</td>";
            $tab.="<td rowspan=2>".$_SESSION['lang']['noakundari']."</td>";
            $tab.="<td rowspan=2>".$_SESSION['lang']['noakunsampai']."</td>";
            $tab.="<td colspan=2>".$_SESSION['lang']['urutan']."/".$_SESSION['lang']['noakundisplay']."</td>";
            $tab.="<td rowspan=2>Colspan</td>";
            $tab.="<td rowspan=2 colspan=2>".$_SESSION['lang']['action']."</td>";
            $tab.="</tr>";
            $tab.="<tr class=rowheader align=center>";
            $tab.="<td>".$_SESSION['lang']['data']."</td>";
            $tab.="<td>".$_SESSION['lang']['status']."</td>";
            $tab.="</tr></thead><tbody>";
            foreach($rCek as $row=>$lstData){
                switch ($lstData['rubahoperatr']) {
                    case '0':$stat='';
                        break;
                    case '1':$stat='Sum Data';
                        break;
                    case '2':$stat='Pengecualian';
                        break;
                    default:
                        break;
                }

                $tab.="<tr class=rowcontent>";
                $tab.="<td>".$lstData['nourut']."</td>";
                $tab.="<td>".$lstData['tipe']."</td>";
                $tab.="<td>".$lstData['keterangandisplay']."</td>";
                $tab.="<td>".$lstData['noakundari']."</td>";
                $tab.="<td>".$lstData['noakunsampai']."</td>";
                $tab.="<td>".$lstData['noakundisplay']."</td>";
                $tab.="<td>".$stat."</td>";
                $tab.="<td>".$lstData['variableoutput']."</td>";
                $tab.="<td><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit ".$lstData['keterangandisplay']."' onclick=\"editdetdt('".$lstData['induk']."','".$lstData['nourut']."','".$lstData['tipe']."','".$lstData['keterangandisplay']."','".$lstData['noakundari']."','".$lstData['noakunsampai']."','".$lstData['noakundisplay']."','".$lstData['rubahoperatr']."','".$lstData['variableoutput']."')\"></td>";
                $tab.="<td><img src='images/skyblue/delete.png' class='zImgBtn' title='Delete ".$lstData['keterangandisplay']."' onclick=\"delDetdt('".$lstData['induk']."','".$lstData['kodeorg']."','".$lstData['nourut']."','".$param['namaLaporanDt']."')\"></td>";
                $tab.="</tr>";
            }
            $tab.="<tbody></table>";
        }
        echo $tab;
    break;

    case'saveDetDetail':
        if($param['statusdt']!=1){
            if($param['noakundari']==''){
                exit('warning:'.$_SESSION['lang']['noakun']." ".$_SESSION['lang']['kosong']);            
            }
            if($param['noakunsampai']==''){
                exit('warning:'.$_SESSION['lang']['noakun']." ".$_SESSION['lang']['kosong']);
            }
        }

        if($param['datadt']==''){
            exit('warning:'.$_SESSION['lang']['data']." ".$_SESSION['lang']['detail']." ".$_SESSION['lang']['kosong']);            
        }
        if($param['ketdata']==''){
            exit('warning:'.$_SESSION['lang']['keterangan']." ".$_SESSION['lang']['detail']." ".$_SESSION['lang']['kosong']);            
        }
        if($param['colspandt']==''){
            exit('warning: Col Span'.$_SESSION['lang']['urutan']." ".$_SESSION['lang']['kosong']);            
        }

        $exdata=explode(',', $param['datadt']);
        if ($exdata[1]==''){
            exit('warning : Data harus berkoma');
        }

        $hwr="induk='".$param['nourutht']."' and namalaporan='".$param['namaLaporanDt']."' and nourut='".$param['nourut']."' and kodeorg='".$param['kdOrg']."'";
        $sCek="select * from ".$dbname.".keu_5mesinlaporandt where ".$hwr."";
        $rCek=fetchdata($sCek);
        if(count($rCek)==1){
            $sUpdate="update ".$dbname.".keu_5mesinlaporandt set tipe='".$param['tipeDtdt']."',noakundari='".$param['noakundari']."',noakunsampai='".$param['noakunsampai']."',keterangandisplay='".$param['ketdata']."',variableoutput='".$param['colspandt']."',noakundisplay='".$param['datadt']."',rubahoperatr='".$param['statusdt']."'
                      where ".$hwr."";
        }else{
            $strCount = "select nourut from " . $dbname . ".keu_5mesinlaporandt where kodeorg='".$param['kdOrg']."' and namalaporan='".$param['namaLaporanDt']."' and induk='".$param['nourutht']."' order by nourut desc limit 1";
            $rData=fetchData($strCount);
            $nourut=$rData[0]['nourut']+1;
            $sUpdate="insert into ".$dbname.".keu_5mesinlaporandt (kodeorg,namalaporan,nourut,tipe,noakundari,noakunsampai,keterangandisplay,variableoutput,noakundisplay,rubahoperatr,induk) values ";
            $sUpdate.=" ('".$param['kdOrg']."','".$param['namaLaporanDt']."','".$nourut."','".$param['tipeDtdt']."','".$param['noakundari']."','".$param['noakunsampai']."','".$param['ketdata']."','".$param['colspandt']."','".$param['datadt']."','".$param['statusdt']."','".$param['nourutht']."')";
        }
        try{
            $owlPDO->exec($sUpdate);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$sUpdate;
            die();
        }
    break;


    case'delDetdt':
        $hwr="induk='".$param['nourutht']."' and namalaporan='".$param['namaLaporanDt']."' and nourut='".$param['nourut']."' and kodeorg='".$param['kdOrg']."'";
        $sDel="delete from ".$dbname.".keu_5mesinlaporandt where ".$hwr."";
        // exit('warning '.$sDel);
        try{
            $owlPDO->exec($sDel);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$sDel;
            die();
        }
    break;
	
	
	
	#=================================================================================================================================
	#=================================================================================================================================
	
	case'deldt2':
		$del="delete from  ".$dbname.".keu_5mesinlaporandt_akun  where
			kodeorg='".$param['kodeorg2']."' and namalaporan='".$param['namalaporan2']."' 
			and nourut='".$param['nourut2']."' and noakun='".$param['noakun2']."' and keterangan='".$param['keterangan2']."'";
        try {
            $owlPDO->exec($del);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$sInsert;
            die();
        }
		
	break;
	
	case'deldt3':
		$str="delete from  ".$dbname.".keu_5mesinlaporandt_kodejurnal where kodeorg='".$param['kodeorg3']."' and namalaporan='".$param['namalaporan3']."'  and nourut='".$param['nourut3']."' and tipe='".$param['tipe3']."' and kodejurnal='".$param['kodejurnal3']."'";
		// exit("Error:$str;");
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$str;
            die();
        }
	break;
	
	case'savedt2':
		
		if($param['noakun2']==''){
			exit("Warning:Nomor akun / aruskas masih kosong");
		}
	
		$sInsert="insert into ".$dbname.".keu_5mesinlaporandt_akun (`kodeorg`, `namalaporan`, `nourut`, `noakun`, `keterangan`) values ";
        $sInsert.="('".$param['kodeorg2']."','".$param['namalaporan2']."','".$param['nourut2']."','".$param['noakun2']."','".$param['keterangan2']."')";
        try {
            $owlPDO->exec($sInsert);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$sInsert;
            die();
        }
	break;
	
	case'savedt3':
		$str="INSERT INTO `keu_5mesinlaporandt_kodejurnal` (`kodeorg`, `namalaporan`, `nourut`,`tipe`,`kodejurnal`) VALUES ";
        $str.="('".$param['kodeorg3']."','".$param['namalaporan3']."','".$param['nourut3']."','".$param['tipe3']."','".$param['kodejurnal3']."')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$str;
            die();
        }
	break;
	
	case'viewdetailbaru':
	
        $tab="";
		$nmdetail=makeOption($dbname,'keu_5mesinlaporandt','nourut,keterangandisplay',"nourut='".$param['nourut']."' and namalaporan='".$param['namaLaporanDt']."'");		
		
        $tab.="";
        $tab.="<table cellpadding=1 cellspacing=1 border=0>";
		$tab.="<tr>
                <td>".$_SESSION['lang']['ho']."</td>
                <td>:</td>
                <td><select disabled id=kodeorg2 style='width:250px;'>".$optunit."</select></td>
            </tr>";
        $tab.="<tr>
			<td>".$_SESSION['lang']['namalaporan']."</td>
			<td>:</td>
			<td>
				<input  type=text id=namalaporan2 disabled=disabled style='width:245px' class=myinputtext value='".$param['namaLaporanDt']."' />
			</td>
		</tr>";
		
		
		$tab.="<tr><td>".$_SESSION['lang']['urutan']."</td>";
        $tab.="<td>:</td>";
        $tab.="<td>
		
				<input hidden type=text id=nourut2 style='width:245px' class=myinputtext value='".$param['nourut']."' onkeypress='return angka_doang(event)' disabled=disabled>
				<input type=text style='width:245px' class=myinputtext value='".$param['nourut']." - ".$nmdetail[$param['nourut']]."' onkeypress='return angka_doang(event)' disabled=disabled></td></tr>";
				
			$tab.="<tr>
                <td>".$_SESSION['lang']['keterangan']."</td>
                <td>:</td>
                <td><input type=text id=keterangan2 style='width:245px' class=myinputtext ></td>
            </tr>";
				
		$tab.="<tr>
			<td>".$_SESSION['lang']['noakun']."</td>
			<td>:</td>
			<td><select class=select2 id=noakun2 style='width:250px;'>".$optakun."</select></td>
		</tr>";
			
		$tab.="<td colspan=2></td><td colspan=2><button class=mybutton style='cursor:pointer;' onclick='savedt2()'>".$_SESSION['lang']['save']."</button></td></tr>";
        
		$tab.="</table>";	
		// $tab.="</fieldset>";	
		
		// $tab.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
		$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead><tr>
                <th>".$_SESSION['lang']['nourut']."</th>
                <th>".$_SESSION['lang']['noakun']."</th>
                <th>".$_SESSION['lang']['namaakun']."</th>
                <th>".$_SESSION['lang']['keterangan']."</th>
                <th>".$_SESSION['lang']['action']."</th>
            </tr></thead>";
		#= isi data
		$str = "SELECT * FROM " . $dbname . ".keu_5mesinlaporandt_akun where kodeorg='".$param['kdOrg']."' and namalaporan='".$param['namaLaporanDt']."' and	nourut='".$param['nourut']."' order by noakun";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			@$no+=1;
			$d=substr($bar['noakun'],0,3);
			if($d!=$n){			
				$tab.="<tr class=rowcontent style=font-weight:bold>";
				$tab.="<td></td>";
				$tab.="<td>".$d."</td>";
				$tab.="<td colspan=3>".getNamaAkun($d)."</td>";
				$tab.="</tr>";
			}
			$e=substr($bar['noakun'],0,5);
			if($e!=$m){			
				$tab.="<tr class=rowcontent style=font-weight:bold>";
				$tab.="<td></td>";
				$tab.="<td>".$e."</td>";
				$tab.="<td colspan=3>".getNamaAkun($e)."</td>";
				$tab.="</tr>";
			}
			
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
                <td>".$bar['noakun']."</td>
                <td>".$nmakun[$bar['noakun']]."</td>
                <td>".$bar['keterangan']."</td>
                <td align=center><img src='images/skyblue/delete.png' class='zImgBtn' title='Delete ".$bar['noakun']."' onclick=\"deldt2('".$bar['kodeorg']."','".$bar['namalaporan']."','".$bar['nourut']."','".$bar['noakun']."')\"></td>               
            </tr>";
			$n=$d;
			$m=$e;
		}
		
		$tab.="</table>";	
		$tab.="</fieldset>";
		
		echo $tab;
		
	break;
	
	
	
	
	case'viewdetailbarukodejurnal':
	
        $tab="";
		$nmdetail=makeOption($dbname,'keu_5mesinlaporandt','nourut,keterangandisplay',"nourut='".$param['nourut']."' and namalaporan='".$param['namaLaporanDt']."'");		
		
        //$tab.="<fieldset><legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['input']."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0>";
		$tab.="<tr>
                <td>".$_SESSION['lang']['ho']."</td>
                <td>:</td>
                <td><select disabled id=kodeorg3 style='width:250px;'>".$optunit."</select></td>
            </tr>";
        $tab.="<tr>
			<td>".$_SESSION['lang']['namalaporan']."</td>
			<td>:</td>
			<td>
				<input  type=text id=namalaporan3 disabled=disabled style='width:245px' class=myinputtext value='".$param['namaLaporanDt']."' />
			</td>
		</tr>";
		
		
		$tab.="<tr><td>".$_SESSION['lang']['urutan']."</td>";
        $tab.="<td>:</td>";
        $tab.="<td>
		
				<input hidden type=text id=nourut3 style='width:245px' class=myinputtext value='".$param['nourut']."' onkeypress='return angka_doang(event)' disabled=disabled>
				<input type=text style='width:245px' class=myinputtext value='".$param['nourut']." - ".$nmdetail[$param['nourut']]."' onkeypress='return angka_doang(event)' disabled=disabled></td></tr>";
				
		$tab.="<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td><select class=select2 id=tipe3 onchange=getkodejurnal(); style='width:250px;'>".$opttipekode."</select></td>
		</tr>";	
		$tab.="<tr>
			<td>".$_SESSION['lang']['kodejurnal']."</td>
			<td>:</td>
			<td><select class=select2  id=kodejurnal3 style='width:250px;'>".$optkodejurnal."</select></td>
		</tr>";
			
		$tab.="<td colspan=2></td><td colspan=2><button class=mybutton style='cursor:pointer;' onclick='savedt3()'>".$_SESSION['lang']['save']."</button></td></tr>";
        
		$tab.="</table>";	
		// $tab.="</fieldset>";	
		
		// $tab.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
		$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead><tr>
                <th>".$_SESSION['lang']['nourut']."</th>
                <th>".$_SESSION['lang']['tipe']."</th>
                <th>".$_SESSION['lang']['kodejurnal']."</th>
                <th>".$_SESSION['lang']['namajurnal']."</th>
                <th>".$_SESSION['lang']['action']."</th>
            </tr></thead>";
		#= isi data
		$str = "SELECT * FROM " . $dbname . ".keu_5mesinlaporandt_kodejurnal where kodeorg='".$param['kdOrg']."' and namalaporan='".$param['namaLaporanDt']."' and	nourut='".$param['nourut']."' order by tipe";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			@$no+=1;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
                <td>".ucfirst($bar['tipe'])."</td>
                <td>".$bar['kodejurnal']."</td>
                <td>".$namajurnal[$bar['kodejurnal']]."</td>
                <td align=center><img src='images/skyblue/delete.png' class='zImgBtn' title='Delete ".$bar['noakun']."' onclick=\"deldt3('".$bar['kodeorg']."','".$bar['namalaporan']."','".$bar['nourut']."','".$bar['tipe']."','".$bar['kodejurnal']."')\"></td>               
            </tr>";
		}
		
		$tab.="</table>";	
		$tab.="</fieldset>";
		
		echo $tab;
		
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

    default:
        # code...
        break;
}


?>