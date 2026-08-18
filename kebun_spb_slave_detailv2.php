<?php
session_start();
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('config/connection.php');

$proses=checkPostGet('proses','');
$tahuntanam=checkPostGet('tahuntanam','');
$kdOrg=checkPostGet('kdDiv','');
$noSpb=checkPostGet('noSpb','');
$kerani=checkPostGet('kerani','');
$periode=checkPostGet('periode','');
$tglpanen=tanggalsystemn(checkPostGet('tglpanen',''));
$tglspb=tanggalsystemn(checkPostGet('tglspb',''));
$tgl=explode('-',checkPostGet('tgl',''));
$tglThn= count($tgl)>2? $tgl[2]: '';
$tglBln= count($tgl)>1? $tgl[1]: '';
$periodeB=$tglThn."-".$tglBln;

if(count($_POST)>0){	
	$param = $_POST;
}else{
	$param = $_GET;
}

switch($proses){
	case 'UbahHeader':

		$str = "update ".$dbname.".kebun_spbht set kontanan='".$param['kontanan']."', noreferensi = '".$param['referensimb']."' where nospb ='".$param['noSpb']. "'";

		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case 'createTable':
		$cekPost="select distinct posting from ".$dbname.".kebun_spbht where nospb='".$noSpb."'";
		$qcekPost=$owlPDO->query($cekPost) or die(print " Gagal: ".PDOException::getMessage());
		$qcekPost->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=$qcekPost->fetch();
		if($rCek['posting']!=0){
			exit("Error : Nomor SPB Sudah di Posting");
		}
		if($periode!=$periodeB){
			echo"warning : Tanggal dan Periode tidak sama";
			exit();
		}

		
		$str = "select * from ".$dbname.".setup_blok 
			where kodeorg like '".$kdOrg."%' and intiplasma = 'P' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$cekdata+=1; 
		}


		if (@$cekdata > 0) {

			if(!isset($_POST['statusCek']) or $_POST['statusCek']==0){
	            $where=" and tipe='BLOK' and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,6)='".$kdOrg."' and luasareaproduktif!=0 and intiplasma = 'P')"; //echo"warning:".$where;exit();
	        }else{
	           $where=" and tipe='BLOK' and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".substr($kdOrg,0,4)."' and luasareaproduktif!=0)"; //echo"warning:".$where;exit();
	        }
		}else{
			if(!isset($_POST['statusCek']) or $_POST['statusCek']==0){
	            $where=" and tipe='BLOK' and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,6)='".$kdOrg."' and luasareaproduktif!=0)"; //echo"warning:".$where;exit();
	        }else{
	           $where=" and tipe='BLOK' and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".substr($kdOrg,0,4)."' and luasareaproduktif!=0)"; //echo"warning:".$where;exit();
	        }
			
		}
		$str = "select * from ".$dbname.".organisasi 
			where 1=1 ".$where." group by indukblok order by indukblok asc"; 
			// exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optBlok="<option value=''></option>";
		while($bar=$res->fetch()){
			$optBlok.="<option value=".$bar['indukblok'].">".$bar['namaindukblok']."</option>";
		}
		
		$namaisi = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		#and kodeorg='".$_SESSION['empl']['lokasitugas']."'
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BMTBS'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$nama=explode(',',$bar['nilai']);
		$optkeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($nama as $list => $isi){
			@$optkeg.="<option value=".$isi.">".$namaisi[$isi]."</option>";
		}

		# Header
		$table = "<thead>";
			$table .= "<tr class='rowheader'>";
			$table .= "<td align=center >No</td>";
			$table .= "<td align=center >".$_SESSION['lang']['noreferensi']."</td>";
			$table .= "<td align=center >".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['panen']."</td>";
			$table .= "<td align=center  hidden>".$_SESSION['lang']['pemanen']."</td>";
			$table .= "<td align=center colspan=2>".$_SESSION['lang']['blok']."</td>";
			$table .= "<td align=center>".$_SESSION['lang']['tph']."</td>";
			$table .= "<td align=center>Sesi</td>";
			$table .= "<td align=center >".$_SESSION['lang']['bjr'].' (Kg)'."</td>";
			$table .= "<td align=center >".$_SESSION['lang']['janjang']."</td>";
			$table .= "<td align=center >".$_SESSION['lang']['brondolan']."</td>";
			$table .= "<td align=center style='display:none'>".$_SESSION['lang']['kegiatan']."</td>";
			$table .= "<td hidden><font size=0.5>Kg WB (Khusus ke Pabrik Luar)</font></td>";
			$table .= "<td align=center style='display:none' >".$_SESSION['lang']['mentah']."</td>";
			$table .= "<td align=center style='display:none' >".$_SESSION['lang']['busuk']."</td>";
			$table .= "<td align=center style='display:none' >".$_SESSION['lang']['matang']."</td>";
			$table .= "<td align=center style='display:none' >".$_SESSION['lang']['lewatmatang']."</td>";
			$table .= "<td  align=center >Action</td>";
			$table .= "</tr>";
			$table .= "</thead>";
		$table .= "<tbody id='detailBody'>";
			
		$table .= "<tr id='detail_tr' class='rowcontent'>";
		$table .= "<td align=center>1</td>";
		$table .= "<td align=center>
			<input type='text' id='noreferensidt' size='18' style='width:125px;' maxlength='30' class='myinputtext'>
		</td>";
		// if (@$cekdata > 0) {
		// 	$table .= "<td><input type='text' style='width:85px;' class='myinputtext' id='tglpanen' onmousemove='setCalendar(this.id)' onkeypress='return false'; readonly/>
		// </td>";
		// }else{
			$table .= "<td><input type='text' style='width:85px;' onchange='getBlokSma()' class='myinputtext' id='tglpanen' onmousemove='setCalendar(this.id)' onkeypress='return false'; readonly/>
		</td>";
		//}
		$optsesi="<option value='1'>1</option>";
		$optsesi.="<option value='2'>2</option>";
		$optsesi.="<option value='3'>3</option>";
		$optpemanen=$opttph="<option value=''></option>";
		$table .= "<td  hidden><select style=width:150px id=pemanendt>".$optpemanen."</select></td>";
		$table .= "<td hidden>
			<img id='pemanendtimg' onclick=z.elSearch('pemanendt',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>";
		$table .= "<td><select style=width:130px onchange=getBjr() id=blok>".$optBlok."</select></td>";
		$table .= "<td style=width:20px><img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>";
		$table .= "<td><select style=width:50px id='tphdt'>".$opttph."</select></td>";
		$table .= "<td><select style=width:40px id='sesidt'>".$optsesi."</select></td>";
		
		$table .= "
		<input type=hidden id=oldBlok name=oldBlok value='' />
		<input type=hidden id=oldTph name=oldBlok value='' />
		<input type=hidden id=oldSesi name=oldBlok value='' />
		<input type=hidden id=oldPemanen name=oldBlok value='' />
		<input type=hidden id=oldQrcode name=oldBlok value='' />
		<input type=hidden id=oldtglpanen name=oldtglpanen value='' />
		</td>";
		// $table .= "<td>".makeElement("blok",'select','',
		// array('style'=>'width:150px','onchange'=>"getBjr()"),$optBlok)."<img src=images/onebit_02.png style=position:relative;top:3px;left:3px; class=resicon onclick=\"searchBrg('".$_SESSION['lang']['find']." ".$_SESSION['lang']['blok']."','<fieldset>".$_SESSION['lang']['blok']." : <input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>".$_SESSION['lang']['find']."</button></fieldset><div id=container></div><input type=hidden id=kdafd value=".$kdOrg." />',event)\"; /><input type=hidden id=oldBlok name=oldBlok value='' /></td>";
		//array('style'=>'width:150px','onchange'=>""),$optBlok)."<input type=hidden id=oldBlok name=oldBlok value='' /></td>";
		$table .= "<td>".makeElement("bjr",'textnum','0',
		array('style'=>'width:80px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5','disabled'=>'true'))."</td>";
		$table .= "<td>".makeElement("jjng",'textnum','0',
		array('style'=>'width:80px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5'))."</td>";
		$table .= "<td>".makeElement("brondln",'textnum','0',
		array('style'=>'width:85px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5'))."</td>";
		
		$table .= "<td  hidden><select style=width:100px id=kegiatan>".$optkeg."</select></td>";
			
			$table .= "<td hidden>".makeElement("kgwb",'textnum','0',
		array('style'=>'width:135px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5'))."</td>";
		
			$table .= "<td hidden>".makeElement("mnth",'textnum','0',
		array('style'=>'width:60px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5','disabled'=>'true'))."</td>";
		$table .= "<td hidden>".makeElement("bsk",'textnum','0',
		array('style'=>'width:60px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5','disabled'=>'true'))."</td>";
		$table .= "<td hidden>".makeElement("mtng",'textnum','0',
		array('style'=>'width:60px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5','disabled'=>'true'))."</td>";
		$table .= "<td hidden>".makeElement("lwtmtng",'textnum','0',
		array('style'=>'width:60px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5','disabled'=>'true'))."</td>";
		
		# Add, Container Delete
		$table .= "<td align=center ><img id='detail_add' title=".$_SESSION['lang']['save']." class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/>";
		$table .= "&nbsp;<img id='detail_delete' /></td>";
		$table .= "</tr>";
		$table .="<tr hidden><td colspan=10><font color=red>KG WB di isi untuk Kebun yang belum memiliki Mill (Pabrik)</font></td></tr>";
		$table .= "</tbody>";
	  //  $table .= "</table>";
		echo $table;
	break;
	case 'detail_add' :
	 exit("error : Silahkan Hubungi Team IT jika melihat pesan ini.");
	break;
	case'loadDetail':
		$tab="";
	$no=0;
	$arrblok=array();
	$str="select * from ".$dbname.".kebun_spbdt where nospb='".$noSpb."' order by blok desc";
	$res=fetchdata($str);
	$jlhitem=count($res);
	foreach($res as $val){
		if($tempblok!=''){
			if($tempblok!=$val['blok']){
				$tab.="<tr class=rowcontent style=background-color:#FEF5E7;font-weight:bold>
					<td align=center colspan=7>SUB TOTAL BLOK ".substr($tempblok,6,4)."</td>
					<td align=right >".number_format($ttljjg,0)."</td>
					<td align=right >".number_format($ttlbrd,2)."</td>
					<td align=right colspan=2></td>
				</tr>";
				$ttljjg=$ttlbrd=0;
			}
		}
		$keterangan_tph=makeOption($dbname,'kebun_5tph','kode,keterangan');
		$no++;
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=left>".$val['qrcode']."</td>";
		$tab.="<td align=center nowrap>".tanggalnormal($val['tanggalpanen'])."</td>";
		$tab.="<td align=left hidden>".getNK($val['pemanen'])." - ".getNK($val['pemanen'],'nik')."</td>";
		$tab.="<td align=left>".$val['blok']."</td>";
		// $tab.="<td align=left>".substr($val['tph'],10,3)."</td>";
		$tab.="<td align=left>".$keterangan_tph[$val['tph']]."</td>";
		$tab.="<td align=center>".($val['sesi']=='0'?'-':$val['sesi'])."</td>";
		$tab.="<td align=right>".number_format($val['bjr'],2)."</td>";
		$tab.="<td align=right>".number_format($val['jjg'],0)."</td>";
		$tab.="<td align=right>".number_format($val['brondolan'],2)."</td>";
		$tab.="<td align=center width=20px>
			<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editDetail('".$noSpb."','".$val['blok']."','".$val['tph']."','".$val['sesi']."','".$val['pemanen']."','".$val['qrcode']."','".$val['jjg']."','".$val['bjr']."','".$val['brondolan']."','".$val['mentah']."','".$val['busuk']."','".$val['matang']."','".$val['lewatmatang']."','".$val['kgwb']."','".tanggalnormal($val['tanggalpanen'])."','".$val['kegiatan']."');\">
		</td>";
		$tab.="<td align=center width=20px>
			<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDetail('".$noSpb."','".$val['qrcode']."','".$val['tanggalpanen']."','".$val['pemanen']."','".$val['blok']."','".$val['tph']."','".$val['sesi']."');\" >
		</td>";
		$tab.="</tr>";
		$tempblok=$val['blok'];
		$ttljjg+=$val['jjg'];
		$ttlbrd+=$val['brondolan'];
		
		if($no==$jlhitem){
			$tab.="<tr class=rowcontent style=background-color:#FEF5E7;font-weight:bold>
				<td align=center colspan=7>SUB TOTAL BLOK ".$tempblok."</td>
				<td align=right >".number_format($ttljjg,0)."</td>
				<td align=right >".number_format($ttlbrd,2)."</td>
				<td align=right colspan=2></td>
			</tr>";
		}
		
		$gttljjg+=$val['jjg'];
		$gttlbrd+=$val['brondolan'];
	}
	
	$tab.="<tr class=rowcontent style=background-color:#CACFD2;font-weight:bold>
			<td align=center colspan=7>TOTAL</td>
			<td align=right >".number_format($gttljjg,0)."</td>
			<td align=right >".number_format($gttlbrd,2)."</td>
			<td align=right colspan=2></td>
			</tr>
			";	
			echo $tab;
	
	break;
    case'getBlokSma':

        $optKdBlok="<option value=''></option>";    
		if($tglpanen>$tglspb){
			exit("Warning : Tanggal panen tidak boleh lebih besar dari tanggal SPB.");
		}
		
		$where='';
		if(($tglpanen!='' or $tglpanen!='--') and $_POST['plasma']==0){
			$where=" and indukblok in (select blok from ".$dbname.".kebun_rekappnn where tanggal='".$tglpanen."')";
		}

        $sdt="select distinct indukblok,namaindukblok from ".$dbname.".organisasi where
              induk like '".substr($_POST['kdAfd'],0,4)."%'  and tipe='BLOK' ".$where." order by namaindukblok asc";
		$qdt=$owlPDO->query($sdt) or die(print " Gagal: ".PDOException::getMessage());
		$qdt->setFetchMode(PDO::FETCH_ASSOC);
		while($rdt=$qdt->fetch()){
			if($param['blokdt']==$rdt['indukblok']){
				$optKdBlok.="<option value='".$rdt['indukblok']."' selected>".$rdt['namaindukblok']."</option>";				
			}else{
				$optKdBlok.="<option value='".$rdt['indukblok']."'>".$rdt['indukblok']." - ".$rdt['namaindukblok']."</option>";
			}
        }

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}

		if($dataunitx != ''){
			$whereUnit = "and unit in (".$dataunitx.")";
		}else{
			$whereUnit = "";
		}

		$optpemanen.="<option value=''></option>";
		// $str="select distinct(karyawanid) as karyawanid from ".$dbname.".kebun_prestasi_vw where tanggal='".$tglpanen."' ".$whereUnit." ";
		// $res=fetchdata($str);
		// foreach($res as $val){
		// 	if($param['nikdt']==$val['karyawanid']){
		// 		$optpemanen.="<option value='".$val['karyawanid']."' selected>".getNK($val['karyawanid'])." - ".getNK($val['karyawanid'],'nik')."</option>";
		// 	}else{
		// 		$optpemanen.="<option value='".$val['karyawanid']."'>".getNK($val['karyawanid'])." - ".getNK($val['karyawanid'],'nik')."</option>";
		// 	}
		// }
		
        echo $optKdBlok."####".$optpemanen;
    break;

    case'getBlokNor':
        $optKdBlok="<option value=''></option>";   
		
		if($tglpanen>$tglspb){
			exit("Warning : Tanggal panen tidak boleh lebih besar dari tanggal SPB.");
		}
		
		
		$where='';
		if(($tglpanen!='' or $tglpanen!='--') and $_POST['plasma']==0){
			$where=" and indukblok in (select kodeorg from ".$dbname.".kebun_prestasi_vw where tanggal='".$tglpanen."')";
		}
		
		if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$datadivisix='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (".$dataunitx.") and tipe in ('AFDELING')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($datadivisix==""){
					$datadivisix.="'".$val['kodeorganisasi']."'";				
				}else{
					$datadivisix.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$datadivisix='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (".$dataunitx.") and tipe in ('AFDELING')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($datadivisix==""){
					$datadivisix.="'".$val['kodeorganisasi']."'";				
				}else{
					$datadivisix.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$datadivisix='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (".$dataunitx.") and tipe in ('AFDELING')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($datadivisix==""){
					$datadivisix.="'".$val['kodeorganisasi']."'";				
				}else{
					$datadivisix.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}

		if($dataunitx != ''){
			$whereUnit = "and unit in (".$dataunitx.")";
		}else{
			$whereUnit = "";
		}

		if($datadivisix != ''){
			$whereInduk = "and induk in (".$datadivisix.")";
		}else{
			$whereInduk = "";
		}		

		if($param['blokdt'] != ''){
			$str="select distinct(karyawanid) as karyawanid from ".$dbname.".kebun_prestasi_vw where tanggal='".$tglpanen."' and kodeorg = '".$param['blokdt']."' ".$whereUnit." ";
			$res=fetchdata($str);

			if (count($res) == 0 || count($res) == '' ) {
				exit("Warning data tidak bisa di edit: Data panen tidak ada untuk tanggal : ".$tglpanen." di blok : ".$param['blokdt']." (Pastikan kegiatan panen sudah di download dari mobile / buat baru kegiatan panen) ");
			}

		}

        $sdt="select distinct induk,indukblok,namaindukblok from ".$dbname.".organisasi where tipe='BLOK' ".$whereInduk." ".$where." order by namaindukblok asc";
		$qdt=$owlPDO->query($sdt) or die(print " Gagal: ".PDOException::getMessage());
		$qdt->setFetchMode(PDO::FETCH_ASSOC);
		while($rdt=$qdt->fetch()){
			if($param['blokdt']==$rdt['indukblok']){
				$optKdBlok.="<option value='".$rdt['indukblok']."' selected>".$rdt['namaindukblok']."</option>";				
			}else{
				$optKdBlok.="<option value='".$rdt['indukblok']."'>".$rdt['indukblok']." - ".$rdt['namaindukblok']."</option>";
			}
        }
		
		
		$optpemanen.="<option value=''></option>";
		$str="select distinct(karyawanid) as karyawanid from ".$dbname.".kebun_prestasi_vw where tanggal='".$tglpanen."' ".$whereUnit." ";
		$res=fetchdata($str);
		foreach($res as $val){
            if($param['nikdt']==$val['karyawanid']){
				$optpemanen.="<option value='".$val['karyawanid']."' selected>".getNK($val['karyawanid'])." - ".getNK($val['karyawanid'],'nik')."</option>";
			}else{
				$optpemanen.="<option value='".$val['karyawanid']."'>".getNK($val['karyawanid'])." - ".getNK($val['karyawanid'],'nik')."</option>";
			}
		}
		
        echo $optKdBlok."####".$optpemanen;
    break;
	case'cariBlok':
        $tab.="<fieldset>
               <legend>Result</legend>
               <div style=\"overflow:auto; max-height:300px;width:300px;\" >
               <table cellpadding=1 cellspacing=1 border=0 class=sortable>";
        $tab.="<thead><tr><td>No.</td>";
        $tab.="<td>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td>".$_SESSION['lang']['namaorganisasi']."</td></tr></thead><tbody>";
        if($_POST['idCer']==1){
            $dhr=" induk like '".substr($_POST['kdAfd'],0,4)."%' 
                    and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".substr($_POST['kdAfd'],0,4)."' and luasareaproduktif!=0)";
        }else{
            $dhr=" induk='".$_POST['kdAfd']."' and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,6)='".$_POST['kdAfd']."' and luasareaproduktif!=0)";
        }
        $sdt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where
              ".$dhr." and tipe='BLOK' and namaorganisasi like '%".$_POST['txtfind']."%' order by namaorganisasi asc";
		$qdt=$owlPDO->query($sdt) or die(print " Gagal: ".PDOException::getMessage());
		$qdt->setFetchMode(PDO::FETCH_ASSOC);
		while($rdt=$qdt->fetch()){		
            $ert+=1;
            $tab.="<tr class=rowcontent onclick=\"setBlok('".$rdt['kodeorganisasi']."')\" style='cursor:pointer;'><td align=center>".$ert."</td>";
            $tab.="<td>".$rdt['kodeorganisasi']."</td>";
            $tab.="<td>".$rdt['namaorganisasi']."</td></tr>";
        }
        $tab.="</tbody></table></div></fieldset>";
        echo $tab;
        break;
}
?>