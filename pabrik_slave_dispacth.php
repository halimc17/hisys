<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param=$_POST;
$arrDt=array("40000001"=>"CPO","40000002"=>"KER");
$jm = $mnt = "";
    for ($t = 0; $t < 24;) {
        if (strlen($t) < 2) {
            $t = "0" . $t;
        }
        $jm.="<option value=" . $t . " " . ($t == 00 ? 'selected' : '') . ">" . $t . "</option>";
        $t++;
    }
    for ($y = 0; $y < 60;) {
        if (strlen($y) < 2) {
            $y = "0" . $y;
        }
        $mnt.="<option value=" . $y . " " . ($y == 00 ? 'selected' : '') . ">" . $y . "</option>";
        $y++;
    }
switch($param['proses']) {
	case'getKomoditi':
		$optData="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sKomo="select kodebarang from ".$dbname.".pabrik_blk_daftar where nokontrak='".$param['nokontrak']."'";
		$rKomo=fetchdata($sKomo);
		foreach ($rKomo as $key) {
			$whrK="kodebarang='".$key['kodebarang']."'";
			$optNm=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whrK);
			$optData.="<option value='".$key['kodebarang']."'>".$optNm[$key['kodebarang']]."</option>";
		}
		echo $optData;
	break;
    case'loadNewData':
		#cek jabatan
		$sCkJbtn="select count(jabatan) as itung from ".$dbname.".setup_posting where jabatan='".$_SESSION['empl']['kodejabatan']."' and kodeaplikasi='keuangan'";
		$qCkJbtn=$owlPDO->query($sCkJbtn) or die(print " Gagal: ".PDOException::getMessage());
		$qCkJbtn->setFetchMode(PDO::FETCH_ASSOC);
		$rCkJbtn=$qCkJbtn->fetch();

		if($param['tgl']!=''){
			$where.=" and tanggal='".tanggalsystemn($param['tgl'])."'";
		}
		if($param['nokontrak']!=''){
			$where.=" and nokontrak like '%".$param['nokontrak']."%'";
		}
		if($param['notransaksi']!=''){
			$where.=" and noba_pengapalan like '%".$param['notransaksi']."%'";
		}
		 

        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".pabrik_blk_dispatchht where kodept = '".$_SESSION['org']['kodeorganisasi']."' ".$where." 
              order by tanggal desc ";
		$res=fetchdata($str);
		//$jlhbrs=owlBaris($res);	
		$jlhbrs=count($res);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
			$tab.="</tr>";
		}else{
			$no=0;
			$no=$maxdisplay;
	        $str="SELECT * from ".$dbname.".pabrik_blk_dispatchht where kodept = '".$_SESSION['org']['kodeorganisasi']."' ".$where." 
	              order by tanggal desc   limit ".$offset.",".$limit."";
	        $tab="";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
	            $no+=1;
	            $optNmKary=array();
	            $optNmKary2=array();
	            	$whr="kodebarang='".$bar['komoditi']."'";
	            	$optNmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whr);
	            
	            if(intval(@$bar['postingby'])!=0){
	            	$whr="karyawanid='".$bar['postingby']."'";
	            	$optNmKary2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
	            }
	            $tab.="<tr class=rowcontent>";
	            $tab.="<td align=right>".$no."</td>";
	            $tab.="<td>".$bar['noba_pengapalan']."</td>";
	            //$tab.="<td align=left>".$bar['kodeunit']."</td>";
				$tab.="<td>".$bar['tanggal']."</td>";
				$tab.="<td>".$bar['nokontrak']."</td>";
				$tab.="<td>".$bar['namakapal']."</td>";
				$tab.="<td>".$bar['tanggalmulai']."</td>";
				$tab.="<td>".$bar['tanggalselesai']."</td>";
				//$tab.="<td align=left>".$optNmKary2[$bar['postingby']]."</td>";
	            $tab.="
	            <td align=center>";
				$postdt='';
				if(@$bar['posting']==1){
					$postdt=" src=images/skyblue/posted.png class=zImgOffBtn title='Posted' ";          
				}
				else{
					if($rCkJbtn['itung']==1){
						$postdt=" src=images/skyblue/posting.png style='cursor:pointer;' title='Posting' onclick=\"posting('".$bar['noba_pengapalan']."','".$_SESSION['lang']['notifandayakin']."');\"";
					}
					$jmMulai=explode(":",substr($bar['tanggalmulai'],10,8));
					$jmSlsi=explode(":",substr($bar['tanggalselesai'],10,8));
					$tab.=" <img src=images/application/application_edit.png class=zImgBtn title='Edit' 
		                onclick=\"edit('".$bar['noba_pengapalan']."','".tanggalnormal($bar['tanggal'])."','".$bar['nokontrak']."',
								'<option value=".$bar['komoditi'].">".$optNmBrg[$bar['komoditi']]."</option>',
								'".tanggalnormal(substr($bar['tanggalmulai'],0,10))."',
								
								'".tanggalnormal(substr($bar['tanggalselesai'],0,10))."','".$bar['asalkirim']."',
								'".$bar['tujuan']."','".$bar['surveyor']."','".$bar['ptsurveyor']."',
								
								'".$bar['cheif']."','".$bar['head_bulking']."','".$bar['namakapal']."','".$bar['kgawal']."','".$bar['kgakhir']."',
								
								'".$bar['totalmuat']."','".$jmMulai[0].":".$jmMulai[1]."','".$jmSlsi[0].":".$jmSlsi[1]."',
								'".$bar['tinggiawal']."','".$bar['suhuakhir']."',
								
								'".$bar['tinggiakhir']."','".$bar['suhuakhir']."',
								
								'".$bar['kodept']."');\">
		                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
		                     onclick=\"deletehead('".$bar['noba_pengapalan']."');\">
		                "; 
				}
				        
	            $tab.="<img src=images/skyblue/pdf.jpg class=zImgBtn title='PDF No. BA Pengapalan : ".$bar['noba_pengapalan']."' 
	                     onclick=masterPDF('pabrik_blk_dispatchht','".$bar['noba_pengapalan']."','','pabrik_dispatch_pdf',event)>
	                     <img  ".$postdt."  class=zImgBtn >";
	            $tab.="</td>";
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
	            <tr><td colspan=8 align=center>
	            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
	            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	            </td>
	            </tr>";
		}
	 
        
        echo $tab."####".$footd;
        break;
	
    case'insert':
    	if($param['notransaksi']==''){
			//tanggal/millcode/jenis/nourut
			#014/BAP-CPO/BPN-BULKING/IX/2016 romawi
			#0/1/2/3/4
			
			$sTran="select noba_pengapalan from ".$dbname.".pabrik_blk_dispatchht where kodept='".$param['kdOrg']."' order by noba_pengapalan desc limit 1";
			$rTran=fetchdata($sTran);
			$nod=explode("/",$rTran[0]['noba_pengapalan']);
			$nourut=intval($nod[0]);
			$thnBerJln=date('Y');
            if($thnBerJln!=$nod[4]){
                $nourut=1;
            }else{
                $nourut=$nourut+1;
            }
            $tgl=explode("-",$param['tgl']);
            $nourut=addZero($nourut,"3");
            $jmMulai=$param['tglMulaiJm'].":".$param['tglMulaiMnt'];
            $jmSlsi=$param['tglSlsiJm'].":".$param['tglSlsiMnt'];
            $notrans=$nourut."/BAP-".$arrDt[$param['komoditi']]."/".$param['kdOrg']."-BULKING/".romawi(intval(date('m')))."/".$thnBerJln;
            $sIns="insert into ".$dbname.".pabrik_blk_dispatchht (`noba_pengapalan`,`tanggal`,`kodept`,`nokontrak`,`komoditi`,`tanggalmulai`,
			`tanggalselesai`,`asalkirim`,`tujuan`,`surveyor`,`ptsurveyor`,
			`cheif`,`head_bulking`,`namakapal`,`kgawal`,`kgakhir`,`totalmuat`,`updateby`,`tinggiawal`,`suhuawal`,`tinggiakhir`,`suhuakhir`)
                   values ('".$notrans."','".tanggalsystemn($param['tgl'])."','".$param['kdOrg']."','".$param['nokontrak']."','".$param['komoditi']."','".tanggalsystemn($param['tglMulai']).":".$jmMulai."','".tanggalsystemn($param['tglSlsi']).":".$jmSlsi."','".$param['aslKrm']."',
                           '".$param['tujuan']."','".$param['surveyor']."','".$param['ptsurveyor']."','".$param['chief']."','".$param['hdBlking']."','".$param['nmKapal']."','".$param['kgAwal']."','".$param['kgAkhir']."','".intval($param['TotMuat'])."','".$_SESSION['standard']['userid']."','".$param['tinggiAwal']."','".$param['suhuAwal']."','".$param['tinggiAkhir']."','".$param['suhuAkhir']."')";
            try{
                $owlPDO->exec($sIns); 
            }catch (PDOException $e){
                exit("error: DB Error ".$e->getMessage()."___".$sIns);
                die();
            }
        }
		echo $notrans;
	break;
	case'editheader':
		$notrans=$param['notransaksi'];
	 	$jmMulai=$param['tglMulaiJm'].":".$param['tglMulaiMnt'];
        $jmSlsi=$param['tglSlsiJm'].":".$param['tglSlsiMnt'];
		$sIns="update ".$dbname.".pabrik_blk_dispatchht set `tanggal`='".tanggalsystemn($param['tgl'])."',`kodept`='".$param['kdOrg']."',
		       `nokontrak`='".$param['nokontrak']."',`komoditi`='".$param['komoditi']."',`tanggalmulai`='".tanggalsystemn($param['tglMulai']).":".$jmMulai."',
		       `tanggalselesai`='".tanggalsystemn($param['tglSlsi']).":".$jmSlsi."',`asalkirim`='".$param['aslKrm']."',
		       `tujuan`='".$param['tujuan']."',`surveyor`='".$param['surveyor']."',`ptsurveyor`='".$param['ptsurveyor']."',`cheif`='".$param['chief']."',
		       `head_bulking`='".$param['hdBlking']."',`namakapal`='".$param['nmKapal']."',`kgawal`='".$param['kgAwal']."',
		       `kgakhir`='".$param['kgAkhir']."',`totalmuat`='".intval($param['TotMuat'])."',`updateby`='".$_SESSION['standard']['userid']."',
		       `tinggiawal`='".$param['tinggiAwal']."',`suhuawal`='".$param['suhuAwal']."',`tinggiakhir`='".$param['tinggiAkhir']."',`suhuakhir`='".$param['suhuAkhir']."'
  				where `noba_pengapalan`='".$notrans."'";
        try{
            $owlPDO->exec($sIns); 
        }catch (PDOException $e){
            exit("error: DB Error ".$e->getMessage()."___".$sIns);
            die();
        }
		echo $notrans;
	break;
	case'deletehead':
		$sDel="delete from ".$dbname.".pabrik_blk_dispatchht where noba_pengapalan='".$param['notransaksi']."'";
		try{
			$owlPDO->exec($sDel); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}
	break;
    case'createTable':
	   	$frm[0]='';
		$frm[1]='';
		$frm[2]='';
		$arrBaa="##nobaa##tanggalbaa##kdPbarik##kdTangki##ffa##moisture##dirt##jamanalisamulai##menitanalisamulai##jamanalisaselesai##menitanalisaselesai";
		
		$sNotr="select komoditi from ".$dbname.".pabrik_blk_dispatchht where noba_pengapalan='".$param['notransaksi']."'";
		
		$rNotr=fetchdata($sNotr);
		
		$optTangki="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sTangki="select * from ".$dbname.".pabrik_5tangki where kodeorg='".$_SESSION['empl']['lokasitugas']."' and komoditi='".$arrDt[$rNotr[0]['komoditi']]."'";
		$rTangki=fetchdata($sTangki);
	    foreach ($rTangki as $key => $value) {
	    	$optTangki.="<option value='".$value['kodetangki']."'>".$value['kodetangki']."</option>";
	    }
	    $sTangki2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	    $rTangki2=fetchdata($sTangki2);
	    foreach ($rTangki2 as $key => $value) {
	    	$optMill.="<option value='".$value['kodeorganisasi']."'>".$value['namaorganisasi']."</option>";
	    }
	    
		
		$frm[0].="<fieldset style=float:left>";
		$frm[0].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";
		$frm[0].="<table border=0 cellpadding=1 cellspacing=1>";
		$frm[0].="<tr><td>No. Berita Acara Analisa</td><td>:</td>
		          <td><input type=text  id=nobaa disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\"></td></tr>
		          <tr><td>".$_SESSION['lang']['tanggal']." Analisa</td><td>:</td>
		          <td><input type=text class='myinputtext' id='tanggalbaa' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' /></td>
				  </tr>
		         
				 <tr>
					<td>Jam Analisa</td>
					<td>:</td>
					<td> <select id=jamanalisamulai>".$jm."</select>:<select id=menitanalisamulai>".$mnt."</select> s/d
					  <select id=jamanalisaselesai>".$jm."</select>:<select id=menitanalisaselesai>".$mnt."</select>
					  </td></td>
				 </tr>
				 
				 <tr><td>".$_SESSION['lang']['kdpabrik']."</td><td>:</td>
		          <td><select id=kdPbarik style=\"width:200px;\">".$optMill."</select></td></tr>
		          
		          <tr><td>".$_SESSION['lang']['kodetangki']."</td><td>:</td>
		          <td><select id=kdTangki style=\"width:200px;\" >".$optTangki."</select></td>
		          </tr>

		          <tr><td>".$_SESSION['lang']['cpoffa']."</td><td>:</td>
		          <td><input type=text  id=ffa onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:200px;\">%</td>
		          </tr>
		          <tr><td>".$_SESSION['lang']['kadarair']."</td><td>:</td>
		          <td><input type=text  id=moisture onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:200px;\">%</td></tr>
		          <tr><td>".$_SESSION['lang']['kotoran']."</td><td>:</td>
		          <td><input type=text  id=dirt onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:200px;\">%</td>
		          </tr>
		        </tr>";
		$frm[0].="<tr class=rowcontent>
		            <td colspan=2></td>
		            <td><button class=mybutton onclick=saveBaa('pabrik_slave_dispacth','".$arrBaa."')>".$_SESSION['lang']['save']."</button></td>

		        </tr>";
		$frm[0].="</table></fieldset>";
		$frm[0].="<fieldset style='float:left;clear:both'>";
		$frm[0].="<legend><b>".$_SESSION['lang']['list']."</b></legend>"; 
		$frm[0].="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
				  <thead>
				  <tr class=rowheader>
				  <td>No. Berita Acara Analisa</td>
				  <td>".$_SESSION['lang']['tanggal']." Analisa</td>
				  <td>".$_SESSION['lang']['kdpabrik']."</td>
				  <td>".$_SESSION['lang']['kodetangki']."</td>
				  <td>".$_SESSION['lang']['cpoffa']."</td>
				  <td>".$_SESSION['lang']['kadarair']."</td>
				  <td>".$_SESSION['lang']['kotoran']."</td>
				  <td>".$_SESSION['lang']['action']."</td>
				  </tr>
				  </thead><tbody id=containListBaa>
				  </tbody>
				  </table>
		          </fieldset>"; 

		$arrSnd="##nopalka##tinggi##volume##suhu##brtjenis##tonase";
		$frm[1].="<fieldset style=float:left>";
		$frm[1].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";
		$frm[1].="<table border=0 cellpadding=1 cellspacing=1>";
		$frm[1].="
		        <tr>
		            <td>No.Palka</td>
		            <td>:</td>      
		            <td><input type=text id=nopalka  onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\"></td>
		        </tr>
		        <tr>
		            <td>".$_SESSION['lang']['tinggi']."</td>
		            <td>:</td>
		            <td><input type=text id=tinggi  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:200px;\">CM</td>
		        </tr>
		        <tr>
		            <td>".$_SESSION['lang']['volume']."</td>
		            <td>:</td>      
		            <td><input type=text id=volume  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:200px;\"></td>
		        </tr>
		        <tr>
		            <td>".$_SESSION['lang']['suhu']."</td>
		            <td>:</td>      
		            <td><input type=text id=suhu  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:200px;\">&deg;C</td>
		        </tr>
		        <tr>
		            <td>".$_SESSION['lang']['beratjenis']."</td>
		            <td>:</td>      
		            <td><input type=text id=brtjenis  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:200px;\"></td>
		        </tr>
		        <tr>
		            <td>Tonase</td>
		            <td>:</td>      
		            <td><input type=text id=tonase  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:200px;\">
		            ".$_SESSION['lang']['kg']."</td>
		        </tr>
		        <tr>
		        	<td colspan=2>&nbsp;</td> 
		            <td><button id=savehead class=mybutton onclick=saveSounding('pabrik_slave_dispacth','".$arrSnd."')>".$_SESSION['lang']['save']."</button></td>
		        </tr>";


		$frm[1].="</table></fieldset>";
		$frm[1].="<fieldset style=float:left;clear:both;>";
		$frm[1].="<legend><b>".$_SESSION['lang']['list']."</b></legend>";// 
		$frm[1].="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
				  <thead>
				  <tr class=rowheader>
				  <td>No.</td>
				  <td>No.Palka</td>
				  <td>".$_SESSION['lang']['tinggi']."</td>
				  <td>".$_SESSION['lang']['volume']."</td>
				  <td>".$_SESSION['lang']['suhu']."</td>
				  <td>".$_SESSION['lang']['beratjenis']."</td>
				  <td>".$_SESSION['lang']['kg']."</td>
				  <td>".$_SESSION['lang']['action']."</td>
				  </tr>
				  </thead><tbody id=containSounding>
				  </tbody>
				  </table>

				</fieldset>";  

		$arrSgl="##nosegel##posisi_segel##total_segel##warna_segel##nosegel_view";
		$frm[2].="<fieldset style=float:left;clear:both>";
		$frm[2].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";
		$frm[2].="<table border=0 cellpadding=1 cellspacing=1>";
		$frm[2].="<tr>
		            <td>No. Segel</td>
		            <td>:</td>      
		            <td><input type=text id=nosegel_view  onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\">
		               <input type=hidden id=nosegel  onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\"></td>
		    	</tr>
		    	<tr>
		            <td>Posisi Segel</td>
		            <td>:</td>      
		            <td><input type=text id=posisi_segel  onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:200px;\"></td>
		    	</tr>
		    	
		    	<tr>
		            <td>Warna Segel</td>
		            <td>:</td>      
		            <td><input type=text id=warna_segel  onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:200px;\"></td>
		    	</tr>
		    	<tr>
		            <td>Total Segel</td>
		            <td>:</td>      
		            <td><input type=text id=total_segel  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber  style=\"width:200px;\"></td>
		    	</tr>
		        <tr>
		        	<td colspan=2>&nbsp;</td> 
		            <td><button id=save class=mybutton onclick=saveSegel('pabrik_slave_dispacth','".$arrSgl."')>".$_SESSION['lang']['save']."</button></td>
		        </tr>";

		$frm[2].="</table></fieldset>";
		$frm[2].="<fieldset style=float:left;clear:both>";
		$frm[2].="<legend><b>".$_SESSION['lang']['list']."</b></legend>";// 
		$frm[2].="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
				  <thead>
				  <tr class=rowheader>
				  <td>No.</td>
				  <td>No. Segel</td>
				  <td>Posisi Segel</td>
				  <td>Total Segel</td>
				  <td>Warna Segel</td>
				  <td>".$_SESSION['lang']['action']."</td>
				  </tr>
				  </thead><tbody id=containSegel>
				  </tbody>
				  </table></fieldset>";
		$optPt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";				  	
		$sPt="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi  where tipe='PT'";
		$rPt=fetchdata($sPt);
		foreach($rPt as $row){
			$optPt.="<option value='".$row['kodeorganisasi']."'>".$row['namaorganisasi']."</option>";				  	
		}
		$arrSgl="##kdPt##porsiPt##stockTersedia";
		$frm[3].="<fieldset style=float:left;clear:both>";
		$frm[3].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";
		$frm[3].="<table border=0 cellpadding=1 cellspacing=1>";
		$frm[3].="<tr>
		            <td>".$_SESSION['lang']['pt']."</td>
		            <td>:</td>      
		            <td><select id=kdPt style=\"width:200px;\" onchange=getStock()>".$optPt."</select></td>
		    	</tr>
		    	<tr>
		            <td>Stock Tersedia</td>
		            <td>:</td>      
		            <td><input type=text id=stockTersedia disabled=disabled  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber  style=\"width:200px;\"></td>
		    	</tr>
		    	<tr>
		            <td>Porsi</td>
		            <td>:</td>      
		            <td><input type=text id=porsiPt  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber  style=\"width:200px;\"></td>
		    	</tr>
		        <tr>
		        	<td colspan=2>&nbsp;</td> 
		            <td><button id=save class=mybutton onclick=savePorsi('pabrik_slave_dispacth','".$arrSgl."')>".$_SESSION['lang']['save']."</button></td>
		        </tr>";

		$frm[3].="</table></fieldset>";
		$frm[3].="<fieldset style=float:left;clear:both>";
		$frm[3].="<legend><b>".$_SESSION['lang']['list']."</b></legend>";// 
		$frm[3].="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
				  <thead>
				  <tr class=rowheader>
				  <td>No.</td>
				  <td>".$_SESSION['lang']['pt']."</td>
				  <td>Stock Tersedia</td>
				  <td>Porsi</td>
				  <td>".$_SESSION['lang']['action']."</td>
				  </tr>
				  </thead><tbody id=containPorsi>
				  </tbody>
				  </table></fieldset>";
		$hfrm[0]="Analisa Lab";
		$hfrm[1]="Sounding";
		$hfrm[2]="Segel";
		$hfrm[3]="Porsi Stock";

		//$hfrm[1]=$_SESSION['lang']['list'];
		//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
		
		echo drawTab('FRM',$hfrm,$frm,150,650);
	break;
 
	case'addBaa':
		if($param['tanggalbaa']==''){exit('warning:'.$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kosong']);}
		if($param['kdPbarik']==''){exit('warning:'.$_SESSION['lang']['kdPbarik']." ".$_SESSION['lang']['kosong']);}
		if($param['kdTangki']==''){exit('warning:'.$_SESSION['lang']['kdTangki']." ".$_SESSION['lang']['kosong']);}
		if($param['ffa']==''){exit('warning:'.$_SESSION['lang']['cpoffa']." ".$_SESSION['lang']['notifemptyzero']);}
		if($param['moisture']==''){exit('warning:'.$_SESSION['lang']['kadarair']." ".$_SESSION['lang']['notifemptyzero']);}
		if($param['dirt']==''){exit('warning:'.$_SESSION['lang']['kotoran']." ".$_SESSION['lang']['notifemptyzero']);}
		$jmMulai=$param['jamanalisamulai'].":".$param['menitanalisamulai'];
            $jmSlsi=$param['jamanalisaselesai'].":".$param['menitanalisaselesai'];
		$sCek="select * from ".$dbname.".pabrik_blk_dispatchlab where noba_pengapalan='".$param['notransaksi']."'";
		$rCek2=fetchdata($sCek);
		if($rCek2[0]['nobaa']==''){
			//014/BAP-CPO/BPN-BULKING/IX/2016
			#cek apakah headernya sudah ada atau belum
			$sCek="select * from ".$dbname.".pabrik_blk_dispatchht where noba_pengapalan='".$param['notransaksi']."'";
			$rCek=fetchdata($sCek);
			if(count($rCek)==0){
				exit('warning: Header belum tersimpan');
			}
			$sTran="select nobaa from ".$dbname.".pabrik_blk_dispatchlab where nobaa like '%".$param['kdOrg']."-BULKING%' order by nobaa desc limit 1";
			$rTran=fetchdata($sTran);
			$nod=explode("/",$rTran[0]['nobaa']);
			$nourut=intval($nod[0]);
			$thnBerJln=date('Y');
            if($thnBerJln!=$nod[4]){
                $nourut=1;
            }else{
                $nourut=$nourut+1;
            }
            $tgl=explode("-",$param['tanggalbaa']);
            $nourut=addZero($nourut,"3");
            $notrans=$nourut."/BAA-".$arrDt[$param['komoditi']]."/".$param['kdOrg']."-BULKING/".romawi(intval(date('m')))."/".$thnBerJln;
            $sIns="insert into ".$dbname.".pabrik_blk_dispatchlab (`noba_pengapalan`,`nobaa`,`tanggalbaa`,`millcode`,
								`kodetangki`,`ffa`,`moisture`,`dirt`,`jammulai`,`jamselesai`)
                   values ('".$param['notransaksi']."','".$notrans."','".tanggalsystemn($param['tanggalbaa'])."',
							'".$param['kdPbarik']."','".$param['kdTangki']."','".$param['ffa']."',
							'".$param['moisture']."','".$param['dirt']."','".$jmMulai."','".$jmSlsi."')";
            try{
                $owlPDO->exec($sIns); 
            }catch (PDOException $e){
                exit("error: DB Error ".$e->getMessage()."___".$sIns);
                die();
            }
            echo $notrans;
		}else{
			$sIns="update ".$dbname.".pabrik_blk_dispatchlab set `tanggalbaa`='".tanggalsystemn($param['tanggalbaa'])."',`kodetangki`='".$param['kdTangki']."',
			      `ffa`='".$param['ffa']."',`moisture`='".$param['moisture']."',`dirt`='".$param['dirt']."',jammulai='".$jmMulai."',jamselesai='".$jmSlsi."'
                   where `nobaa`='".$rCek2[0]['nobaa']."' and `noba_pengapalan`='".$param['notransaksi']."'";
            try{
                $owlPDO->exec($sIns); 
            }catch (PDOException $e){
                exit("error: DB Error ".$e->getMessage()."___".$sIns);
                die();
            }
		}
	break;

	case'addSounding':
		if($param['nopalka']==''){exit('warning:'.$_SESSION['lang']['nopalka']." ".$_SESSION['lang']['kosong']);}
		if($param['tinggi']==''){exit('warning:'.$_SESSION['lang']['tinggi'].' '.$_SESSION['lang']['notifemptyzero']);}
		if($param['volume']==''){exit('warning:'.$_SESSION['lang']['volume'].' '.$_SESSION['lang']['notifemptyzero']);}
		if($param['suhu']==''){exit('warning:'.$_SESSION['lang']['suhu'].' '.$_SESSION['lang']['notifemptyzero']);}
		if($param['brtjenis']==''){exit('warning:'.$_SESSION['lang']['beratjenis'].' '.$_SESSION['lang']['notifemptyzero']);}
		if($param['tonase']==''){exit('warning: Tonase '.$_SESSION['lang']['notifemptyzero']);}
		$sCek="select * from ".$dbname.".pabrik_blk_dispatchdtsound where noba_pengapalan='".$param['notransaksi']."' and `nopalka`='".$param['nopalka']."'";
		$rCek2=fetchdata($sCek);
		if($rCek2[0]['nopalka']==''){
			$sIns="insert into ".$dbname.".pabrik_blk_dispatchdtsound (`noba_pengapalan`,`nopalka`,`tinggi`,`volume`,`suhu`,`beratjenis`,`tonase`)
	               values ('".$param['notransaksi']."','".$param['nopalka']."','".$param['tinggi']."','".$param['volume']."','".$param['suhu']."','".$param['brtjenis']."','".$param['tonase']."')";
	        try{
	            $owlPDO->exec($sIns); 
	        }catch (PDOException $e){
	            exit("error: DB Error ".$e->getMessage()."___".$sIns);
	            die();
	        }
	    }else{
	    	$sIns="update ".$dbname.".pabrik_blk_dispatchdtsound set `tinggi`='".$param['tinggi']."',`volume`='".$param['volume']."',`suhu`='".$param['suhu']."',`beratjenis`='".$param['brtjenis']."',`tonase`='".$param['tonase']."'
	               where `noba_pengapalan`='".$param['notransaksi']."' and `nopalka`='".$param['nopalka']."'";
	        try{
	            $owlPDO->exec($sIns); 
	        }catch (PDOException $e){
	            exit("error: DB Error ".$e->getMessage()."___".$sIns);
	            die();
	        }
	    }
	break;
	case'addSegel':
	    #$arrSgl="##nosegel##posisi_segel##total_segel##warna_segel";
		if($param['nosegel_view']==''){exit('warning: No. Segel '.$_SESSION['lang']['kosong']);}
		if($param['posisi_segel']==''){exit('warning: Posisi Segel'.$_SESSION['lang']['kosong']);}
		if($param['total_segel']==''){exit('warning: Total Segel '.$_SESSION['lang']['notifemptyzero']);}
		if($param['warna_segel']==''){exit('warning: Warna Segel'.$_SESSION['lang']['kosong']);}
		$sCek="select * from ".$dbname.".pabrik_blk_dispatchdtsegel where noba_pengapalan='".$param['notransaksi']."' and `nosegel`='".$param['nosegel']."'";
		$rCek2=fetchdata($sCek);
		$param['nosegel']=str_replace(" ", "", $param['nosegel_view']);
		if($rCek2[0]['nosegel']==''){
			$sIns="insert into ".$dbname.".pabrik_blk_dispatchdtsegel (`noba_pengapalan`,`nosegel`,`nosegel_view`,`posisi_segel`,`total_segel`,`warna_segel`)
	               values ('".$param['notransaksi']."','".$param['nosegel']."','".$param['nosegel_view']."','".$param['posisi_segel']."','".$param['total_segel']."','".strtoupper($param['warna_segel'])."')";
	        try{
	            $owlPDO->exec($sIns); 
	        }catch (PDOException $e){
	            exit("error: DB Error ".$e->getMessage()."___".$sIns);
	            die();
	        }
	    }else{
	    	$sIns="update ".$dbname.".pabrik_blk_dispatchdtsegel set `posisi_segel`='".$param['posisi_segel']."',`total_segel`='".$param['total_segel']."',`warna_segel`='".strtoupper($param['warna_segel'])."'
	               where `noba_pengapalan`='".$param['notransaksi']."' and `nosegel`='".$param['nosegel']."'";
	        try{
	            $owlPDO->exec($sIns); 
	        }catch (PDOException $e){
	            exit("error: DB Error ".$e->getMessage()."___".$sIns);
	            die();
	        }
	    }
	break;
	case'addPorsi':

		$sCek="select * from ".$dbname.".pabrik_blk_dispatchdtporsi
		       where noba_pengapalan='".$param['notransaksi']."' and `kodept`='".$param['kdOrg']."' and `tanggal`='".tanggalsystemn($param['tgl'])."'";
		$rCek2=fetchdata($sCek);
		
		if(count($rCek2)==0){
			$sCek3="select sum(kgporsi) as porsi from ".$dbname.".pabrik_blk_dispatchdtporsi
		           where noba_pengapalan='".$param['notransaksi']."' and `tanggal`='".tanggalsystemn($param['tgl'])."'";
			$rCek3=fetchdata($sCek3);	
			#pengecekan total tonase dengan porsi
			if($param['TotMuat']<($param['porsiPt']+$rCek3[0]['porsi'])){
				exit('warning: Porsi '.number_format(($param['porsiPt']+$rCek3[0]['porsi'])).'lebih besar dibandingkan total muat :'.$param['TotMuat']);
			}
			$sIns="insert into ".$dbname.".pabrik_blk_dispatchdtporsi (`noba_pengapalan`,`kodept`,`tanggal`,`kgporsi`)
	               values ('".$param['notransaksi']."','".$param['kdPt']."','".tanggalsystemn($param['tgl'])."','".$param['porsiPt']."')";
	        try{
	            $owlPDO->exec($sIns); 
	        }catch (PDOException $e){
	            exit("error: DB Error ".$e->getMessage()."___".$sIns);
	            die();
	        }
	    }else{
	    	$sCek3="select sum(kgporsi) as porsi from ".$dbname.".pabrik_blk_dispatchdtporsi
		           where noba_pengapalan='".$param['notransaksi']."' and `tanggal`='".tanggalsystemn($param['tgl'])."'";
			$rCek3=fetchdata($sCek3);	
			#pengecekan total tonase dengan porsi
			if($param['TotMuat']<($param['porsiPt']+$rCek3[0]['porsi'])){
				exit('warning: Porsi lebih besar dibandingkan total muat');
			}
	    	$sIns="update ".$dbname.".pabrik_blk_dispatchdtporsi set `kgporsi`='".$param['porsiPt']."'
	               where `noba_pengapalan`='".$param['notransaksi']."' and `kodept`='".$param['kdPt']."' and tanggal='".tanggalsystemn($param['tgl'])."'";
	        try{
	            $owlPDO->exec($sIns); 
	        }catch (PDOException $e){
	            exit("error: DB Error ".$e->getMessage()."___".$sIns);
	            die();
	        }
	    }
	break;
    case'loadDetail':
    	#ambil data dari pabrik_blk_dispatchlab untuk tab pertama
		$sDet="select * from ".$dbname.".pabrik_blk_dispatchlab where noba_pengapalan='".$param['notransaksi']."' ";
		$rDet=fetchData($sDet);
		$tabLab.="<tr class=rowcontent>";
		$tabLab.="<td colspan=8>".$_SESSION['lang']['dataempty']."</td>";
		$tabLab.="</tr>";
		if(count($rDet)!=0){
			$tabLab='';
			foreach ($rDet as $key => $res){
				$no+=1;
				$tabLab.="<tr class=rowcontent>";
				$tabLab.="<td>".$res['nobaa']."</td>";
				$tabLab.="<td>".tanggalnormal($res['tanggalbaa'])."</td>";
				$tabLab.="<td>".$res['millcode']."</td>";
				$tabLab.="<td>".$res['kodetangki']."</td>";
				$tabLab.="<td align=right>".$res['ffa']."</td>";
				$tabLab.="<td align=right>".$res['moisture']."</td>";
				$tabLab.="<td align=right>".$res['dirt']."</td>";
				$tabLab.="<td>";
				$tabLab.="<img src=images/application/application_edit.png class=resicon  title='Edit BAA ".$res['nobaa']."' 
							onclick=\"editLab('".$res['nobaa']."',
							'".tanggalnormal($res['tanggalbaa'])."','".$res['millcode']."','".$res['kodetangki']."',
							'".$res['ffa']."','".$res['moisture']."','".$res['dirt']."',
							'".substr($res['jammulai'],0,2)."','".substr($res['jammulai'],3,2)."',
							'".substr($res['jamselesai'],0,2)."','".substr($res['jamselesai'],3,2)."');\">
					      <img src=images/application/application_delete.png class=resicon  title='Delete BAA ".$res['nobaa']."' onclick=delDetailLab('".$res['nobaa']."'); ></td>";
				$tabLab.="</tr>";
			}
		}
		
		$no=0;
		#ambil data dari pabrik_blk_dispatchdtsound untuk tab kedua
		$sDet2="select * from ".$dbname.".pabrik_blk_dispatchdtsound where noba_pengapalan='".$param['notransaksi']."' ";
		$rDet2=fetchData($sDet2);
		$tabSnd.="<tr class=rowcontent>";
		$tabSnd.="<td colspan=8>".$_SESSION['lang']['dataempty']."</td>";
		$tabSnd.="</tr>";
		if(count($rDet2)!=0){
			$tabSnd='';
			foreach ($rDet2 as $key => $res) {
				$no+=1;
				$tabSnd.="<tr class=rowcontent>";
				$tabSnd.="<td>".$no."</td>";
				$tabSnd.="<td>".$res['nopalka']."</td>";
				$tabSnd.="<td align=right>".$res['tinggi']."</td>";
				$tabSnd.="<td align=right>".number_format($res['volume'])."</td>";
				$tabSnd.="<td align=right>".$res['suhu']."</td>";
				$tabSnd.="<td align=right>".$res['beratjenis']."</td>";
				$tabSnd.="<td align=right>".number_format($res['tonase'])."</td>";
				$tabSnd.="<td>";
				$tabSnd.="<img src=images/application/application_edit.png class=resicon  title='Edit Palka ".$res['nopalka']."' onclick=editPalka('".$res['nopalka']."','".$res['tinggi']."','".$res['volume']."','".$res['suhu']."','".$res['beratjenis']."','".$res['tonase']."');>
					      <img src=images/application/application_delete.png class=resicon  title='Delete Palka ".$res['nopalka']."' onclick=delDetailPalka('".$res['nopalka']."','".$res['noba_pengapalan']."'); ></td>";
				$tabSnd.="</tr>";
			}
		}
		$no=0;
		#ambil data dari pabrik_blk_dispatchdtsegel untuk tab ketiga
		$sDet3="select * from ".$dbname.".pabrik_blk_dispatchdtsegel where noba_pengapalan='".$param['notransaksi']."' ";
		$rDet3=fetchData($sDet3);
		$tabSgl.="<tr class=rowcontent>";
		$tabSgl.="<td colspan=6>".$_SESSION['lang']['dataempty']."</td>";
		$tabSgl.="</tr>";
		if(count($rDet3)!=0){
			$tabSgl='';
			foreach ($rDet3 as $key => $res) {
				$no+=1;
				$tabSgl.="<tr class=rowcontent>";
				$tabSgl.="<td>".$no."</td>";
				$tabSgl.="<td>".$res['nosegel_view']."</td>";
				$tabSgl.="<td>".$res['posisi_segel']."</td>";
				$tabSgl.="<td align=right>".$res['total_segel']."</td>";
				$tabSgl.="<td>".$res['warna_segel']."</td>";
				$tabSgl.="<td>";
				$tabSgl.="<img src=images/application/application_edit.png class=resicon  title='Edit Segel ".$res['nosegel']."' onclick=editSegel('".$res['nosegel']."','".$res['noba_pengapalan']."');>
					      <img src=images/application/application_delete.png class=resicon  title='Delete Segel ".$res['nosegel']."' onclick=delDetailSegel('".$res['nosegel']."','".$res['noba_pengapalan']."'); ></td>";
				$tabSgl.="</tr>";
			}
		}
		$no=0;
		#ambil data dari pabrik_blk_dispatchdtporsi untuk tab ke empat
		$whr="noba_pengapalan='".$param['notransaksi']."'";
		$optKm=makeOption($dbname,'pabrik_blk_dispatchht','noba_pengapalan,komoditi',$whr);
		$sDtPrsi="select * from ".$dbname.".pabrik_blk_dispatchdtporsi where noba_pengapalan='".$param['notransaksi']."' ";
		$rDtPrsi=fetchData($sDtPrsi);
		$tabPrsi.="<tr class=rowcontent>";
		$tabPrsi.="<td colspan=5>".$_SESSION['lang']['dataempty']."</td>";
		$tabPrsi.="</tr>";
		if(count($rDtPrsi)!=0){
			$tabPrsi='';
			foreach ($rDtPrsi as $key => $res) {
				$sDet3="select * from ".$dbname.".pabrik_blk_5saldo where 
		                kodept='".$res['kodept']."' and kodebarang='".$optKm[$param['notransaksi']]."' and tanggal='".$res['tanggal']."'";
				$rDet3=fetchData($sDet3);
				$no+=1;
				$stok=$rDet3[0]['saldoawal']+$rDet3[0]['masuk'];
				$tabPrsi.="<tr class=rowcontent>";
				$tabPrsi.="<td>".$no."</td>";
				$tabPrsi.="<td>".$res['kodept']."</td>";
				$tabPrsi.="<td align=right>".number_format($stok)."</td>";
				$tabPrsi.="<td align=right>".number_format($res['kgporsi'])."</td>";
				$tabPrsi.="<td>";
				$tabPrsi.="<img src=images/application/application_delete.png class=resicon  title='Delete Porsi ".$res['kodept']."' onclick=delDetailPorsi('".$res['kodept']."','".$res['noba_pengapalan']."','".$res['tanggal']."'); ></td>";
				$tabPrsi.="</tr>";
			}
		}
		
		
		echo $tabLab."####".$tabSnd."####".$tabSgl."####".$tabPrsi;
	break;
	case'delDetailLab':
		$sDel="delete from ".$dbname.".pabrik_blk_dispatchlab where nobaa='".$param['nobaa']."'";
		try{
			$owlPDO->exec($sDel); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}	
	break;
	case'delDetailPalka':
		$sDel="delete from ".$dbname.".pabrik_blk_dispatchdtsound where nopalka='".$param['nopalka']."' and noba_pengapalan='".$param['notransaksi']."'";
		try{
			$owlPDO->exec($sDel); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}
	break;
	case'delDetailSegel':
		$sDel="delete from ".$dbname.".pabrik_blk_dispatchdtsegel where nosegel='".$param['nosegel']."' and noba_pengapalan='".$param['notransaksi']."'";
		try{
			$owlPDO->exec($sDel); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}
	break;
	case'delDetailPorsi':
		$sDel="delete from ".$dbname.".pabrik_blk_dispatchdtporsi where kodept='".$param['kdpt']."' and noba_pengapalan='".$param['notransaksi']."' and tanggal='".$param['tgl']."'";
		try{
			$owlPDO->exec($sDel); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}
	break;
	case'editSegel':
		$sDet3="select * from ".$dbname.".pabrik_blk_dispatchdtsegel where noba_pengapalan='".$param['notransaksi']."' and nosegel='".$param['nosegel']."' ";
		$rDet3=fetchData($sDet3);
		echo $rDet3[0]['posisi_segel']."####".$rDet3[0]['warna_segel']."####".$rDet3[0]['total_segel']."####".$rDet3[0]['nosegel_view'];
	break;
	case'getStock':
		$sDet3="select * from ".$dbname.".pabrik_blk_5saldo where 
		        kodept='".$param['kdPt']."' and kodebarang='".$param['komoditi']."' and tanggal='".tanggalsystemn($param['tgl'])."'";	
		$rDet3=fetchData($sDet3);
		$stcok=($rDet3[0]['saldoawal']+$rDet3[0]['masuk'])-$rDet3[0]['kirim'];
		echo $stcok;
	break;
	case'postData':
			//cek data segel sudah terinput atau belum
			$sCek="select * from ".$dbname.".pabrik_blk_dispatchdtsegel where noba_pengapalan='".$param['notransaksi']."'";
			$rCek=fetchdata($sCek);
			//cek data porsi
			$sCek2="select * from ".$dbname.".pabrik_blk_dispatchdtporsi where noba_pengapalan='".$param['notransaksi']."'";
			$rCek2=fetchdata($sCek2);
			//cek data
			$sCek3="select * from ".$dbname.".pabrik_blk_dispatchdtsound where noba_pengapalan='".$param['notransaksi']."'";
			$rCek3=fetchdata($sCek3);

			$sCekDt="select * from ".$dbname.".pabrik_blk_dispatchlab where noba_pengapalan='".$param['notransaksi']."'";
			$rCekDt=fetchdata($sCekDt);
			if((count($rCek)==0)||(count($rCek2)==0)||(count($rCek3)==0)||(count($rCekDt)==0)){
				exit('warning:'.$_SESSION['lang']['detail']." ".$_SESSION['lang']['dataempty']);
			} 
			$sCekDt="select * from ".$dbname.".pabrik_blk_dispatchht where noba_pengapalan='".$param['notransaksi']."'";
			$rCekDt=fetchdata($sCekDt);
			if($rCekDt[0]['posting']==1){
				exit('warning: Data Sudah Terposting');
			}
			$sCek3="select sum(kgporsi) as porsi from ".$dbname.".pabrik_blk_dispatchdtporsi
		           where noba_pengapalan='".$rCekDt[0]['noba_pengapalan']."' and `tanggal`='".$rCekDt[0]['tanggal']."'";
			$rCek3=fetchdata($sCek3);	
			if($rCekDt[0]['totalmuat']!=$rCek3[0]['porsi']){
				exit('warning: Tonase Muat dengan Porsi Belum Sama, Total Muat :'.number_format($rCekDt[0]['totalmuat']).', Total Porsi: '.number_format($rCek3[0]['porsi']));
			}
			$sUpd="update ".$dbname.".pabrik_blk_dispatchht set posting=1,postingby='".$_SESSION['standard']['userid']."'  where noba_pengapalan='".$param['notransaksi']."'";
			try{
				$owlPDO->exec($sUpd); 
				foreach($rCek2 as $row=>$dt){
					$sUpd2="update ".$dbname.".pabrik_blk_5saldo set kirim=".$dt['kgporsi']." 
					       where kodept='".$dt['kodept']."'  and tanggal='".$dt['tanggal']."' and kodebarang='".$rCekDt[0]['komoditi']."'";	
					try{
						$owlPDO->exec($sUpd2); 
					}catch (PDOException $e){
						exit("error: DB Error ".$e->getMessage()."___".$sUpd2);
						die();
					}
				}

			}catch (PDOException $e){
				exit("error: DB Error ".$e->getMessage()."___".$sUpd);
				die();
			}
		break;
	 
	case'htmlDetail':
		$sHead="select * from ".$dbname.".pabrik_blk_kirimht where nokirim='".$param['notransaksi']."'";
		$rHead=fetchdata($sHead);
		$tab.="<table cellpadding=1 cellspacing=1 border=0>";
		$tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td>";
		$tab.="<td>:</td><td>".$rHead[0]['nokirim']."</td></tr>";
		$tab.="<tr><td>".$_SESSION['lang']['tanggal']."</td>";
		$tab.="<td>:</td><td>".tanggalnormal($rHead[0]['tanggal'])."</td></tr>";
		$tab.="<tr><td>".$_SESSION['lang']['NoKontrak']."</td>";
		$tab.="<td>:</td><td>".$rHead[0]['nokontrak']."</td></tr>";
		$tab.="<tr><td>".$_SESSION['lang']['noberitaacara']."</td>";
		$tab.="<td>:</td><td>".$rHead[0]['noba']."</td></tr>";
		$tab.="<tr><td>".$_SESSION['lang']['lokasi']."</td>";
		$tab.="<td>:</td><td>".$rHead[0]['lokasi']."</td></tr>";
		$tab.="</table>";
		

		$tab.="<table cellspacing='1' border='0' class='sortable' style='width:600px'>
                <thead>
                    <tr class=\"rowheader\">
                        <td align='center'>No.</td>
                        <td align='center'>".$_SESSION['lang']['noTiket']."</td>
                        <td align='center'>".$_SESSION['lang']['komoditi']."</td>
                        <td align='center'>".$_SESSION['lang']['beratBersih']."</td>
                    </tr>
                </thead>";
		$sDet="select * from ".$dbname.".pabrik_blk_kirimdt where nokirim='".$param['notransaksi']."' ";
        $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		$tot=0;

		while($rDet=$qDet->fetch()){
			$no+=1;
			$whr="kodebarang='".$rDet['kodebarang']."'";
			$optNm=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whr);
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=right>".$no."</td>";
			$tab.="<td>".$rDet['notransaksi']."</td>";
			$tab.="<td>".$optNm[$rDet['kodebarang']]."</td>";
			$tab.="<td align=right>".number_format($rDet['beratbersih'],0)."</td>";
			$tab.="</tr>";
			$tot+=$rDet['beratbersih'];
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan=3 align=center><b>".$_SESSION['lang']['total']."</b></td>";
		$tab.="<td align=right><b>".number_format($tot,0)."</b></td></tr>";
		echo $tab;
		break;
		
     
}



?>