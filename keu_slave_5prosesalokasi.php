<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
$method=$_POST['method'];
$param=$_POST;

$str = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where tipe='HOLDING' and length(kodeorganisasi) = 4";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    @$optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}


$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if(@$param['nmLaporan']=='CASH FLOW' or @$param['namaLaporanDt']=='ARUS KAS'){	
	$str = "SELECT noaruskas as noakun,nama_aruskas as namaakun FROM " . $dbname . ".keu_5aruskas where level=3";
	$nmakun=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas');	
}else{
	$str = "SELECT * FROM " . $dbname . ".keu_5akun where length(noakun)=7";
	$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');	
}
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optakun.="<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " " . $bar['namaakun'] . "</option>";
}


// echo"<pre>";
// print_r($param);
// echo"</pre>";




 $arrtipe=getEnum($dbname,'keu_5prosesalokasidt','tipe');
        foreach($arrtipe as $kei=>$fal){
            // if($fal=='Header'){
                // continue;
            // }
            @$optTipe.="<option value='".$fal."'>".$fal."</option>";
        }

	


switch ($method) {
    case 'loadData':
		$where="where aktif=1";
        if (@$param['namalaporan']!= '') {
            $where.=" and  namalaporan like '%" . $param['namalaporan'] . "%' ";
        }
        if (@$param['kodeorg']!= '') {
            $where.=" and  kodeorg like '%" . $param['kodeorg'] . "%' ";
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
        $str="select * from ".$dbname.".keu_5prosesalokasi  ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=3>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }
        else{
            $nmpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_5prosesalokasi ".$where."  limit ".$offset.",".$limit."";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){  
                $tab.="<tr class=rowcontent>
                        <td>".$nmpt[$bar->kodeorg]."</td>
                        <td>".$bar->namalaporan."</td>
                        <td>".$bar->ket1."</td>";
                $tab.="<td align=center><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editht('".$bar->kodeorg."','".$bar->namalaporan."','".$bar->ket1."')\"></td>";
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
                <tr><td colspan=4  align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".@$footd;
    break;
    case'insertht':
    if($param['nmLaporan']==''){
        exit('warning: '.$_SESSION['lang']['namalaporan'].' '.$_SESSION['lang']['notifobligatory']);
    }
    $rCek=array();
    $sCek="select * from ".$dbname.".keu_5prosesalokasi where kodeorg = '".$param['kdHo']."' and namalaporan='".$param['nmLaporan']."'";
	
    $rCek=fetchdata($sCek);
    // if(count($rCek)!=0){
    //     exit('warning:Data sudah ada');
    // }
	
	if(count($rCek)==0){
		$str="insert into ".$dbname.".keu_5prosesalokasi (kodeorg,namalaporan,periode,ket1,aktif) values ";
		$str.="('".$param['kdHo']."','".$param['nmLaporan']."','bulanan','".$param['ket1']."','1')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal: " . $e->getMessage() . "\n".$str;
			die();
		}
	}else{
		$str="update ".$dbname.".keu_5prosesalokasi set ket1='".$param['ket1']."' where kodeorg='".$param['kdHo']."' and namalaporan='".$param['nmLaporan']."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal: " . $e->getMessage() . "\n".$str;
			die();
		}
	}
    break;
    case'detail':
        $tab="";
        
        $tab.="<fieldset style=width:1100px;><legend>".$_SESSION['lang']['detail']."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0>";
        $tab.="<tr>";
			$tab.="<td>".$_SESSION['lang']['namalaporan']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><input type=text id=namaLaporanDt disabled=disabled style='width:195px' class=myinputtext value='".$param['nmLaporan']."' /></td>";
			$tab.="<td>".$_SESSION['lang']['keterangan']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><input type=text id=ketdata class='myinputtext' style='width:200px;' /></td>";
			
			$tab.="<td rowspan=4 valign=top>Total</td>";
        $tab.="<td rowspan=4 valign=top>:</td>";
		$tab.="<td valign=top rowspan=4 valign=top><textarea rows='3' id=datadt placeholder='nilai total' type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:400px;\"></textarea></td>";
		$tab.="</tr>";
		$tab.="<tr>";
			$tab.="<td>".$_SESSION['lang']['tipe']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><select id=tipeDt  style='width:200px'>".$optTipe."</select></td>";
			
		 $tab.="<td>".$_SESSION['lang']['noakun']."</td>"; ### GW
        $tab.="<td>:</td>";
        $tab.="<td><select id=noakundt  style='width:200px'>".$optakun."</select><img id=noakundt onclick=z.elSearch('noakundt',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>";	
		$tab.="</tr>";	
		$tab.="<tr>";	
			$tab.="<td>".$_SESSION['lang']['urutan']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><input type=text id=nourut style='width:195px' class=myinputtext onkeypress='return angka_doang(event)'/></td>";
        $tab.="</tr>";

      
		$tab.="<tr>";
       


		$tab.="</tr>";
		$tab.="<tr>";
		
		
		
		$tab.="<tr hidden><td>Colspan</td>";
        $tab.="<td>:</td>";
        $tab.="<td><input type=text id=colspandt style='width:195px' class=myinputtextnumber onkeypress='return angka_doang(event)' /></td></tr>";

		
        
        $tab.="<tr><td colspan=2>&nbsp;</td>";
        $tab.="<td><button class=mybutton style='cursor:pointer;' onclick='saveDetHead()'>".$_SESSION['lang']['save']."</button>";
        $tab.="<button onclick='batalrincian()' style='cursor:pointer;' class=mybutton id=btnBatal>".$_SESSION['lang']['cancel']."</button></td></tr>";
        $tab.="</table><input type=hidden id=oldNourut /></fieldset>";
		
		

		
                          
        // $tab.="<fieldset  style=width:95%;><legend>".$_SESSION['lang']['data']."</legend>";
		$tab.="<fieldset style='overflow:auto;max-width:1100px;max-height:300px;'><legend>".$_SESSION['lang']['data']."</legend>";
        $tab.="<table cellspacing=1 cellpadding=1 border=0 class=sortable>";
        $tab.="<thead><tr class=rowheader align=center>";
        $tab.="<td>".$_SESSION['lang']['tipe']."</td>";
        $tab.="<td>".$_SESSION['lang']['urutan']."</td>";
        $tab.="<td>".$_SESSION['lang']['noakun']."</td>";
        $tab.="<td>".$_SESSION['lang']['keterangan']."</td>";
        $tab.="<td>".$_SESSION['lang']['total']."</td>";
        // $tab.="<td>Col Span</td>";
        $tab.="<td colspan=3>".$_SESSION['lang']['action']."</td></tr></thead><tbody>";
        // $sDetData="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$param['nmLaporan']."' and tipe='Header'";
        // $sDetData="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$param['nmLaporan']."' and tipe in ('Header','Total')";
        $nmAkdt = makeOption($dbname,'keu_5akun','noakun,namaakun');
        $sDetData="select * from ".$dbname.".keu_5prosesalokasidt where namalaporan='".$param['nmLaporan']."' and kodeorg='".$param['kodept']."' ";
		// print_r($param);
        $rDetData=fetchdata($sDetData);
        foreach ($rDetData as $key => $val) {
            $tab.="<tr class=rowcontent>";
            $tab.="<td>".$val['tipe']."</td>";
            $tab.="<td>".$val['nourut']."</td>";
            $tab.="<td>".$val['noakun']." - ".@$nmAkdt[$val['noakun']]." </td>"; ## EDIT GSW
            
            $tab.="<td>".$val['keterangandisplay']."</td>";

			// if(strlen($val['noakundisplay'])>100){
			// 	 $tab.="<td>".number_format($val['noakundisplay'],2)." ....</td>";
			// }else{
		    $tab.="<td align=right>".$val['noakundisplay']."</td>";
			// }
           
            // $tab.="<td>".$val['variableoutput']."</td>";
            $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit ".$val['keterangandisplay']."' onclick=\"editdet('".$val['nourut']."','".$val['keterangandisplay']."','".$val['variableoutput']."','".$val['tipe']."','".$val['noakundisplay']."','".$val['noakun']."')\"></td>";
            
            $tab.="<td><img src='images/skyblue/delete.png' class='resicon' title='Delete ".$val['keterangandisplay']."' onclick=\"delDet('".$val['kodeorg']."','".$val['nourut']."','".$param['nmLaporan']."')\"></td>";
            // $tab.="<td><img src='images/skyblue/zoom.png' class='resicon' title='Add Detail ".$val['keterangandisplay']."' onclick=\"viewdetail('".$val['kodeorg']."','".$val['nourut']."','".$param['nmLaporan']."')\"></td>";
            if($val['tipe']=='Detail'){
				$tab.="<td><img src='images/skyblue/zoom.png' class='resicon' title='Add Detail ".$val['keterangandisplay']."' onclick=\"viewdetailbaru('".$val['kodeorg']."','".$val['nourut']."','".$param['nmLaporan']."')\"></td>";
			}else{
				$tab.="<td></td>";
			}
			$tab.="</tr>";
        }
        $tab.="</tbody></table>";
        $tab.="</fieldset>";
		
		
		
		
        echo $tab;
    break;
	
	

	
    case'saveDetHead':
		
        if($param['tipeDt']==''){
            exit('Warning: '.$_SESSION['lang']['tipe']." ".$_SESSION['lang']['kosong']);            
        }
		if($param['ketdata']==''){
            exit('Warning: '.$_SESSION['lang']['keterangan']." ".$_SESSION['lang']['header']." ".$_SESSION['lang']['kosong']);            
        }
        if($param['nourut']==''){
            exit('Warning: '.$_SESSION['lang']['urutan']." ".$_SESSION['lang']['kosong']);            
        }
        if($param['noakundt']==''){
            exit('Warning: '.$_SESSION['lang']['noakun']." ".$_SESSION['lang']['kosong']);            
        }
        // if($param['colspandt']==''){
            // exit('warning: Col Span'.$_SESSION['lang']['urutan']." ".$_SESSION['lang']['kosong']);            
        // }
        
		#delete			
		// $hwr="nourut='".$param['oldNourut']."' and namalaporan='".$param['namaLaporanDt']."' and tipe='".$param['tipeDt']."' and kodeorg='".$param['kdOrg']."'";
        // $str="delete from ".$dbname.".keu_5mesinlaporandt where ".$hwr."";
		// try{
            // $owlPDO->exec($str);
        // } catch (PDOException $e) {
            // print " Gagal: " . $e->getMessage() . "\n".$str;
            // die();
        // }
		$datadt = checkPostGet('datadt','');	  
		
		$str="select * from ".$dbname.".keu_5prosesalokasidt where kodeorg='".$param['kdOrg']."' and namalaporan='".$param['namaLaporanDt']."' and nourut='".$param['nourut']."'";
		$res=fetchdata($str);
		if(count($res)==0){
			#= insert
			$str="insert into ".$dbname.".keu_5prosesalokasidt  (kodeorg,namalaporan,nourut,noakun,keterangandisplay,tipe,noakundisplay) values ";
			$str.=" ('".$param['kdOrg']."','".$param['namaLaporanDt']."','".strtoupper($param['nourut'])."','".$param['noakundt']."','".$param['ketdata']."','".$param['tipeDt']."','".$datadt."')";      
			try{
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal: " . $e->getMessage() . "\n".$sUpdate;
				die();
			}
		}else{
			#= update
			$str="update ".$dbname.".keu_5prosesalokasidt set tipe='".$param['tipeDt']."',keterangandisplay='".$param['ketdata']."',noakundisplay='".$datadt."',noakun='".$param['noakundt']."' where kodeorg='".$param['kdOrg']."' and namalaporan='".$param['namaLaporanDt']."' and nourut='".$param['nourut']."'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal: " . $e->getMessage() . "\n".$str;
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
		
		$sDelA="delete from ".$dbname.".keu_5prosesalokasidt_akun where ".$hwr."";
		try{
            $owlPDO->exec($sDelA);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$sDelA;
            die();
        }
		
        $sDel="delete from ".$dbname.".keu_5prosesalokasidt where ".$hwr."";
        try{
            $owlPDO->exec($sDel);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$sDel;
            die();
        }
    break;
	
	
	
	
	
	
	
	
    // case'viewdetail':
    //     $tab="";
        
    //     $tab.="<fieldset><legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['detail']."</legend>";
    //     $tab.="<table cellpadding=1 cellspacing=1 border=0>";
    //     $tab.="<tr><td>".$_SESSION['lang']['namalaporan']."</td>";
    //     $tab.="<td>:</td>";
    //     $tab.="<td><input type=text id=namaLaporanDt disabled=disabled style='width:195px' class=myinputtext value='".$param['namaLaporanDt']."' /></td></tr>";
    //     $tab.="<tr><td>".$_SESSION['lang']['tipe']."</td>";
    //     $tab.="<td>:</td>";
       
    //     $tab.="<td><select id=tipeDtdt style='width:200px'>".$optTipe."</select></td></tr>";

    //     $tab.="<tr><td>".$_SESSION['lang']['urutan']."</td>";
    //     $tab.="<td>:</td>";
    //     $tab.="<td><input type=text id=nourutdt style='width:195px' class=myinputtext onkeypress='return angka_doang(event)' placeholder='No.urut Otomatis' readonly/></td></tr>";
    //     $tab.="<tr><td>".$_SESSION['lang']['keterangan']."</td>";
    //     $tab.="<td>:</td>";
    //     $tab.="<td><input type=text id=ketdt style='width:195px' class=myinputtext onkeypress='return tanpa_kutip(event)' /></td></tr>";
    //     $optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    //     $sAkun="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7 order by noakun asc";
    //     $rAkun=fetchdata($sAkun);
    //     foreach($rAkun as $kei=>$fal){
    //             $optAkun.="<option value='".$fal['noakun']."'>".$fal['noakun']."-".$fal['namaakun']."</option>";
    //     }  
    //     $tab.="<tr><td>".$_SESSION['lang']['noakundari']."</td>";
    //     $tab.="<td>:</td>";
    //     $tab.="<td><select id=noakundari  style='width:200px'>".$optAkun."</select></td></tr>";
    //     $optAkun2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    //     $sAkun2="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7 order by noakun asc";
    //     $rAkun2=fetchdata($sAkun2);
    //     foreach($rAkun2 as $kei=>$fal){
    //             $optAkun2.="<option value='".$fal['noakun']."'>".$fal['noakun']."-".$fal['namaakun']."</option>";
    //     } 
    //     $tab.="<tr><td>".$_SESSION['lang']['noakunsampai']."</td>";
    //     $tab.="<td>:</td>";
    //     $tab.="<td><select id=noakunsampai  style='width:200px'>".$optAkun2."</select></td></tr>";
    //     $tab.="<tr><td>Colspan</td>";
    //     $tab.="<td>:</td>";
    //     $tab.="<td><input type=text id=colsdt style='width:195px' class=myinputtextnumber onkeypress='return angka_doang(event)' /></td></tr>";
    //     $tab.="<tr><td>Data</td>";
    //     $tab.="<td>:</td>";
    //     $tab.="<td><input type=text id=datadt style='width:195px' class=myinputtext onkeypress='return tanpa_kutip(event)' /></td></tr>";
    //     $optstatus="<option value='0'>".$_SESSION['lang']['pilihdata']."</option>";
    //     $optstatus.="<option value='1'>Sum Data</option>";
    //     $optstatus.="<option value='2'>".$_SESSION['lang']['pengecualian']."</option>";
    //     $tab.="<tr><td>Status</td>";
    //     $tab.="<td>:</td>";
    //     $tab.="<td><select id=statusdt  style='width:200px' onchange='ubahdt()'>".$optstatus."</select></td></tr>";
    //     $tab.="<tr><td>&nbsp;</td>";
    //     $tab.="<td colspan=2><button class=mybutton style='cursor:pointer;' onclick='saveDetDetail()'>".$_SESSION['lang']['save']."</button></td></tr>";
    //     $tab.="</table><input type=hidden id=nourutht value='".$param['nourut']."' /></fieldset>";
    //     //<input type=hidden id=oldNourutdt />
    //     $hwr="induk='".$param['nourut']."' and namalaporan='".$param['namaLaporanDt']."' and tipe<>'Header' and kodeorg='".$param['kdOrg']."'";
    //     $sCek="select * from ".$dbname.".keu_5prosesalokasidt where ".$hwr."";
    //     $rCek=fetchdata($sCek);
    //     if(!empty($rCek)){
    //         $tab.="<fieldset><legend>".$_SESSION['lang']['data']."</legend><table cellpadding=1 cellspacing=1 border=0 class=sortable>";
    //         $tab.="<thead><tr class=rowheader align=center>";
    //         $tab.="<td rowspan=2>".$_SESSION['lang']['urutan']."</td>";
    //         $tab.="<td rowspan=2>".$_SESSION['lang']['tipe']."</td>";
    //         $tab.="<td rowspan=2>".$_SESSION['lang']['keterangan']."</td>";
    //         $tab.="<td rowspan=2>".$_SESSION['lang']['noakundari']."</td>";
    //         $tab.="<td rowspan=2>".$_SESSION['lang']['noakunsampai']."</td>";
    //         $tab.="<td colspan=2>".$_SESSION['lang']['urutan']."/".$_SESSION['lang']['noakundisplay']."</td>";
    //         // $tab.="<td rowspan=2>Colspan</td>";
    //         $tab.="<td rowspan=2 colspan=2>".$_SESSION['lang']['action']."</td>";
    //         $tab.="</tr>";
    //         $tab.="<tr class=rowheader align=center>";
    //         $tab.="<td>".$_SESSION['lang']['data']."</td>";
    //         $tab.="<td>".$_SESSION['lang']['status']."</td>";
    //         $tab.="</tr></thead><tbody>";
    //         foreach($rCek as $row=>$lstData){

    //             switch ($lstData['rubahoperatr']) {
    //                 case '0':$stat='';
    //                     break;
    //                 case '1':$stat='Sum Data';
    //                     break;
    //                 case '2':$stat='Pengecualian';
    //                     break;
    //                 default:
    //                     break;
    //             }

    //             $tab.="<tr class=rowcontent>";
    //             $tab.="<td>".$lstData['nourut']."</td>";
    //             $tab.="<td>".$lstData['tipe']."</td>";
    //             $tab.="<td>".$lstData['keterangandisplay']."</td>";
    //             $tab.="<td>".$lstData['noakundari']."</td>";
    //             $tab.="<td>".$lstData['noakunsampai']."</td>";
    //             $tab.="<td>".$lstData['noakundisplay']."</td>";
    //             // $tab.="<td>".$stat."</td>";
    //             $tab.="<td>".$lstData['variableoutput']."</td>";
    //             $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit ".$lstData['keterangandisplay']."' onclick=\"editdetdt('".$lstData['induk']."','".$lstData['nourut']."','".$lstData['tipe']."','".$lstData['keterangandisplay']."','".$lstData['noakundari']."','".$lstData['noakunsampai']."','".$lstData['noakundisplay']."','".$lstData['rubahoperatr']."','".$lstData['variableoutput']."')\"></td>";
    //             $tab.="<td><img src='images/skyblue/delete.png' class='resicon' title='Delete ".$lstData['keterangandisplay']."' onclick=\"delDetdt('".$lstData['induk']."','".$lstData['kodeorg']."','".$lstData['nourut']."','".$param['namaLaporanDt']."')\"></td>";
    //             $tab.="</tr>";
    //         }
    //         $tab.="<tbody></table>";
    //     }
    //     echo $tab;
    // break;

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
        $sCek="select * from ".$dbname.".keu_5prosesalokasindt where ".$hwr."";
        $rCek=fetchdata($sCek);
        if(count($rCek)==1){
            $sUpdate="update ".$dbname."keu_5prosesalokasidt set tipe='".$param['tipeDtdt']."',noakundari='".$param['noakundari']."',noakunsampai='".$param['noakunsampai']."',keterangandisplay='".$param['ketdata']."',variableoutput='".$param['colspandt']."',noakundisplay='".$param['datadt']."',rubahoperatr='".$param['statusdt']."'
                      where ".$hwr."";
        }else{
            $strCount = "select nourut from " . $dbname . ".keu_5prosesalokasidt where kodeorg='".$param['kdOrg']."' and namalaporan='".$param['namaLaporanDt']."' and induk='".$param['nourutht']."' order by nourut desc limit 1";
            $rData=fetchData($strCount);
            $nourut=$rData[0]['nourut']+1;
            $sUpdate="insert into ".$dbname.".keu_5prosesalokasidt (kodeorg,namalaporan,nourut,tipe,noakundari,noakunsampai,keterangandisplay,variableoutput,noakundisplay,rubahoperatr,induk) values ";
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
        $sDel="delete from ".$dbname.".keu_5prosesalokasidt where ".$hwr."";
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
	
		$del="delete from  ".$dbname.".keu_5prosesalokasidt_akun where
			kodeorg='".$param['kodeorg2']."' and namalaporan='".$param['namalaporan2']."' 
			and nourut='".$param['nourut2']."' and noakun='".$param['noakun2']."'";
        try {
            $owlPDO->exec($del);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n".$sInsert;
            die();
        }
		
	break;
	
	
	case'savedt2':
	
		// $sInsert="insert into ".$dbname.".keu_5prosesalokasidt_akun values ";
  //       $sInsert.="('".$param['kodeorg2']."','".$param['namalaporan2']."','".$param['nourut2']."','".$param['noakun2']."','".$param['keterangan2']."')";
  //       try {
  //           $owlPDO->exec($sInsert);
  //       } catch (PDOException $e) {
  //           print " Gagal: " . $e->getMessage() . "\n".$sInsert;
  //           die();
  //       }

        // $rCek=array();
        // $sCek="select * from ".$dbname.".keu_5prosesalokasidt_akun where noakun = '".$param['noakun2']."'";
        // $rCek=fetchdata($sCek);
        // if(count($rCek)!=0){
            // exit('warning:Data sudah ada');
        // }
        if ($param['keterangan2']=='') {
            exit('Warning: '.$_SESSION['lang']['keterangan']." ".$_SESSION['lang']['kosong']);
        }
        if ($param['noakun2']=='') {
            exit('Warning: '.$_SESSION['lang']['noakun']." ".$_SESSION['lang']['kosong']);
        }
        // if(count($rCek)==0){
            $sInsert="insert into ".$dbname.".keu_5prosesalokasidt_akun values ";
            $sInsert.="('".$param['kodeorg2']."','".$param['namalaporan2']."','".$param['nourut2']."','".$param['noakun2']."','".$param['keterangan2']."')";
            try {
                $owlPDO->exec($sInsert);
            } catch (PDOException $e) {
                print " Gagal: " . $e->getMessage() . "\n".$sInsert;
                die();
            }
        // }
        // else{
        //  $str="update ".$dbname.".keu_5prosesalokasidt_akun set noakun='".$param['noakun2']."' where nourut='".$param['nourut2']."' and namalaporan='".$param['nmLaporan']."'";
        //  try {
        //      $owlPDO->exec($str);
        //  } catch (PDOException $e) {
        //      print " Gagal: " . $e->getMessage() . "\n".$str;
        //      die();
        //  }
        // }
		
	break;
	### YANG DIPAKE DISINI
	case'viewdetailbaru':
	
        $tab="";
		$nmdetail=makeOption($dbname,'keu_5prosesalokasidt','nourut,keterangandisplay',"nourut='".$param['nourut']."' and namalaporan='".$param['namaLaporanDt']."'");	
        $nmHo = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');	
		
        $tab.="<fieldset><legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['input']."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0>";
		$tab.="<tr>
                <td>".$_SESSION['lang']['ho']."</td>
                <td>:</td>
                <td>
                <input  type=text  disabled=disabled style='width:195px' placeholder='".$nmHo[$param['kdOrg']]."' class=myinputtext />
                <input  type=hidden id=kodeorg2 disabled=disabled style='width:195px' class=myinputtext value='".$param['kdOrg']."' />
                </td>
            </tr>";
        $tab.="<tr>
			<td>".$_SESSION['lang']['namalaporan']."</td>
			<td>:</td>
			<td>
				<input  type=text id=namalaporan2 disabled=disabled style='width:195px' class=myinputtext value='".$param['namaLaporanDt']."' />
			</td>
		</tr>";
		
		
		$tab.="<tr><td>".$_SESSION['lang']['urutan']."</td>";
        $tab.="<td>:</td>";
        $tab.="<td>
		
				<input hidden type=text id=nourut2 style='width:195px' class=myinputtext value='".$param['nourut']."' onkeypress='return angka_doang(event)' disabled=disabled>
				<input type=text style='width:195px' class=myinputtext value='".$param['nourut']." - ".$nmdetail[$param['nourut']]."' onkeypress='return angka_doang(event)' disabled=disabled></td></tr>";
			$tab.="<tr>
                <td>".$_SESSION['lang']['keterangan']."</td>
                <td>:</td>
                <td><input type=text id=keterangan2 style='width:195px' class=myinputtext ></td>
            </tr>";
		$tab.="<tr>
                <td>".$_SESSION['lang']['noakun']."</td>
                <td>:</td>
                <td><select  id=noakun2 style='width:195px;'>".$optakun."</select>
			     <img id=noakun2 onclick=z.elSearch('noakun2',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
			</td>
            </tr>";
			
		$tab.="<td colspan=2></td><td colspan=2><button class=mybutton style='cursor:pointer;' onclick='savedt2()'>".$_SESSION['lang']['save']."</button>";
        $tab.="<button onclick='batalviewdetailbaru()' style='cursor:pointer;' class=mybutton id=btnBatal>".$_SESSION['lang']['cancel']."</button></td></tr>";
        
		$tab.="</table>";	
		$tab.="</fieldset>";	
		
		$tab.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead><tr>
                <td>".$_SESSION['lang']['nourut']."</td>
                <td>".$_SESSION['lang']['keterangan']."</td>
                <td>".$_SESSION['lang']['noakun']."</td>
                <td>".$_SESSION['lang']['namaakun']."</td>
                <td>".$_SESSION['lang']['action']."</td>
            </tr></thead>";
		#= isi data
		$str = "SELECT * FROM " . $dbname . ".keu_5prosesalokasidt_akun where kodeorg='".$param['kdOrg']."' and namalaporan='".$param['namaLaporanDt']."' and nourut='".$param['nourut']."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			@$no+=1;
			$tab.="<tr class=rowcontent>
				<td>".$no."</td>
				 <td>".$bar['keterangan']."</td> 
                <td>".$bar['noakun']."</td>
				<td>".@$nmakun[$bar['noakun']]."</td>
                <td><img src='images/skyblue/delete.png' class='resicon' title='Delete ".$bar['noakun']."' onclick=\"deldt2('".$bar['kodeorg']."','".$bar['namalaporan']."','".$bar['nourut']."','".$bar['noakun']."')\"></td>               
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