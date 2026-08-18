<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}



$divisi=checkPostGet('divisi','');
$tipe  =checkPostGet('tipe','');
$proses=checkPostGet('proses','');
$prd   =checkPostGet('prd','');
$unit  =checkPostGet('unit','');
$afd   =checkPostGet('afd','');
$tahap =checkPostGet('tahap','');
$tgl1  =tanggalsystemn(checkPostGet('tgl1',''));
$tgl2  =tanggalsystemn(checkPostGet('tgl2',''));


$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');
$nmorg =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$jab   =getPostingJabatan('premipanen');
switch($proses){
	case'viewdetail2':
	if($unit==''){
		exit("warningcode : Unit kerja harus diisi.");
	}
	if($divisi==''){
		exit("warningcode : Divisi harus diisi.");
	}if($prd==''){
		exit("warningcode : Periode harus diisi.");
	}if($tahap==''){
		exit("warningcode : Tahap harus diisi.");
	}if($tgl1=='--'){
		exit("warningcode : Tanggal dari harus diisi.");
	}if($tgl2==''){
		exit("warningcode : Tanggal sampai harus diisi.");
	}
	
	
	## GET PREMI PEMANEN Detail
	$str="select * from ".$dbname.".kebun_3premipemanen where kodeorg='".$unit."' and divisi='".$divisi."' and periode='".$prd."' and tahap='".$tahap."' and tanggalpanen between '".$tgl1."' and '".$tgl2."'";
	$res=fetchdata($str);
	if(count($res)==0){
		exit("Warning : Proses premi pemanen belum dilakukan.");
	}
	
	$tahap = $res[0]['tahap'];
	if($tahap=='1'){
		$tglawal = '01';
		$tglakhir = '15';
	}else{
		$vtglakhir = tglakhir($prd);
		$extglakhir = explode('-',$vtglakhir);
		$tglawal = '16';
		$tglakhir = $extglakhir[2];
	}
	$jlhtgl = $tglakhir-$tglawal+1;
	$arrdata=array();
	$arrhsl=array();
	$arrtotupah=array();
	$arrtotkg=array();
	$arrtotjjg=array();
	foreach($res as $val){
		$opttopografi = makeOption($dbname,'setup_topografi','topografi,keterangan',"topografi='".$val['status']."'");
		$arrtopografi[$val['status']] = $opttopografi[$val['status']];
		
		$optnamakaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
		$optsubbag = makeOption($dbname,'datakaryawan','karyawanid,subbagian',"karyawanid='".$val['karyawanid']."'");
		
		$arrdata[$val['karyawanid']]['karyawanid'] = $val['karyawanid'];
		$arrdata[$val['karyawanid']]['namakaryawan'] = $optnamakaryawan[$val['karyawanid']];
		$arrdata[$val['karyawanid']]['subbagian'] = $optsubbag[$val['karyawanid']];
		
		$exptgl = explode('-',$val['tanggalpanen']);
		$arrhsl[$val['karyawanid']][addZero($exptgl[2],2)]['rp'] += $val['rplb1'];
		$arrhsl[$val['karyawanid']][addZero($exptgl[2],2)]['kg'] += $val['kglb1'];
		$arrhsl[$val['karyawanid']][addZero($exptgl[2],2)]['jjg'] += $val['jjgpanen'];
		$arrtotupah[$val['karyawanid']]+=$val['rplb1'];
		$arrtotkg[$val['karyawanid']]+=$val['kglb1'];
		$arrkgbrd[$val['karyawanid']] += $val['kgbrd'];
		$arrtotjjg[$val['karyawanid']]+=$val['jjgpanen'];
		$arrdata[$val['karyawanid']]['insentif']+=$val['kehadiran'];
		$arrdata[$val['karyawanid']]['tambahan']+=$val['tambahan'];
		$arrdata[$val['karyawanid']]['denda']+=$val['denda'];
		$arrdata[$val['karyawanid']]['upah']+=$val['rplb1'];
		$arrdata[$val['karyawanid']]['jjg']+=$val['jjgpanen'];
		$arrdata[$val['karyawanid']]['kgbrd']+=$val['kgbrd'];
		$arrdata[$val['karyawanid']]['rpbrd']+=$val['rpbrd'];
		
		$harga=0;
		$s="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$val['kodeorg']."' and tahun='".$val['periode']."' and tahuntanam ='".$val['tahuntanam']."' and topografi='".$val['status']."'";
		$jlhbss = count(fetchdata($s));
		if($jlhbss==0){
			$s="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$val['kodeorg']."' and tahun<='".$val['periode']."' and tahuntanam ='".$val['tahuntanam']."' and topografi='".$val['status']."' order by tahun desc limit 1";	
		}
		$r=fetchdata($s);
		$harga=$r[0]['premitopografi'];
		$arrjlhtpg[$val['karyawanid']][$val['status']]+=$val['kehadiran']/$harga;
	}
	
	// echo "<pre>";
	// print_r($arrhsl);
	// //exit();
	
	
	
	$stream='';
	
	if ($tipe == 'excel') {
		$stream.="<table class=sortable cellspacing=1 border=1>";
	} else 	{
		$stream.="<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
	}

	$stream.="<thead>";
	$stream.="<tr class=rowheader>";
		$stream.="<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>";
		$stream.="<th align=center rowspan=2>".$_SESSION['lang']['nama']."</th>";
		$stream.="<th align=center rowspan=1 colspan='".($jlhtgl)."'>".$_SESSION['lang']['tanggal']."</th>";
		$stream.="<th align=center width=50px rowspan=2>Brd (Kg)</th>";
		$stream.="<th align=center rowspan=2>".$_SESSION['lang']['total']."</th>";
		$stream.="<th align=center width=50px rowspan=2>Upah Panen (Rp/Kg)</th>";
		$stream.="<th align=center width=50px colspan='".count($arrtopografi)."'>Insentif Kehadiran</th>";
		$stream.="<th align=center colspan='6'>".$_SESSION['lang']['total']." Upah</th>";
		$stream.="<th align=center rowspan='2'>Tth</th>";
	$stream.="</tr>";
	$stream.="<tr>";
		for($i=$tglawal;$i<=$tglakhir;$i++){
			$stream.="<th align=center>".addZero($i,2)."</th>";
		}
		foreach($arrtopografi as $key=>$val){
			$stream.="<th align=center>".$val."</th>";
		}
		$stream.="<th align=center>Panen</th>";
		$stream.="<th align=center>Brondolan</th>";
		$stream.="<th align=center>Insentif</th>";
		$stream.="<th align=center>Tambahan</th>";
		$stream.="<th align=center>Denda</th>";
		$stream.="<th align=center>Total</th>";
	$stream.="</tr>";
	// $stream.="<tr>";
		// $stream.="<td align=center colspan='".(count($arrtopografi)+4)."'></td>";
	// $stream.="</tr>";
	$stream.="</thead><tbody>";
	
	array_multisort(array_column($arrdata, 'namakaryawan'), SORT_ASC, $arrdata);
	
	$no=0;
	foreach($arrdata as $key=>$val){
		$no++;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=left>".$val['namakaryawan']."</td>";
		for($i=$tglawal;$i<=$tglakhir;$i++){
			$stream.="<td align=right>".hidezerodecimal($arrhsl[$key][addZero($i,2)]['kg'],2)."</td>";
			$ttlkg[addZero($i,2)]+=$arrhsl[$key][addZero($i,2)]['kg'];
		}
		$stream.="<td align=center>".hidezerodecimal($val['kgbrd'])."</td>"; 
		$stream.="<td align=right>".hidezerodecimal(($arrtotkg[$key]+$val['kgbrd']),2)."</td>";
		$stream.="<td align=right>".hidezerodecimal(($arrtotupah[$key]/$arrtotkg[$key]),0)."</td>";
		foreach($arrtopografi as $key2=>$val2){
			$stream.="<td align=center>".hidezerodecimal($arrjlhtpg[$key][$key2])."</td>";
			$ttltopo[$key2]+=$arrjlhtpg[$key][$key2];
		}
		$stream.="<td align=right>".hidezerodecimal($val['upah'],2)."</td>";
		$stream.="<td align=right>".hidezerodecimal($val['rpbrd'],2)."</td>";
		$stream.="<td align=right>".hidezerodecimal($val['insentif'],2)."</td>";
		$stream.="<td align=right>".hidezerodecimal($val['tambahan'],2)."</td>";
		$stream.="<td align=right>".hidezerodecimal($val['denda'],2)."</td>";
		$stream.="<td align=right>".hidezerodecimal(($val['upah']+$val['rpbrd']+$val['insentif']+$val['tambahan']-$val['denda']),2)."</td>";
		$stream.="<td align=left rowspan=2></td>";
		$stream.="</tr>";
		
		$ttl['brd']+=$val['kgbrd'];
		$ttl['ttl']+=($arrtotkg[$key]+$val['kgbrd']);
		$ttl['upah']+=$val['upah'];
		$ttl['rpbrd']+=$val['rpbrd'];
		$ttl['insentif']+=$val['insentif'];
		$ttl['tambahan']+=$val['tambahan'];
		$ttl['denda']+=$val['denda'];
		$ttl['gtl']+=($val['upah']+$val['rpbrd']+$val['insentif']+$val['tambahan']-$val['denda']);
		
		###################################
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=2 align=center>".$val['subbagian']."</td>";
		for($i=$tglawal;$i<=$tglakhir;$i++){
			$stream.="<td align=right>".hidezerodecimal($arrhsl[$key][addZero($i,2)]['jjg'],2)."</td>";
			$ttljjg[addZero($i,2)]+=$arrhsl[$key][addZero($i,2)]['jjg'];
		}
		$stream.="<td align=left></td>";
		$stream.="<td align=right>".hidezerodecimal($val['jjg'],2)."</td>";
		$stream.="<td align=left></td>";
		foreach($arrtopografi as $key2=>$val2){
			$stream.="<td align=left></td>";
		}
		$stream.="<td align=left></td>";
		$stream.="<td align=left></td>";
		$stream.="<td align=left></td>";
		$stream.="<td align=left></td>";
		$stream.="<td align=left></td>";
		$stream.="<td align=left></td>";
		$stream.="</tr>";
		
		$ttl['jjg']+=$val['jjg'];
		## SPACE
		$stream.="<tr class=rowcontent>";
		$stream.="<td colspan='".(13+$jlhtgl+count($arrtopografi))."'></td>";
		$stream.="</tr>";
	}
	
	#KG
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=left rowspan=2>TOTAL</td>";
	$stream.="<td align=right>Kg</td>";
	for($i=$tglawal;$i<=$tglakhir;$i++){
		$stream.="<td align=right>".hidezerodecimal($ttlkg[addZero($i,2)],2)."</td>";
	}
	$stream.="<td align=right>".hidezerodecimal($ttl['brd'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['ttl'],2)."</td>";
	$stream.="<td align=right></td>";
	foreach($arrtopografi as $key2=>$val2){
		$stream.="<td align=center>".hidezerodecimal($ttltopo[$key2],0)."</td>";
	}
	$stream.="<td align=right>".hidezerodecimal($ttl['upah'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['rpbrd'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['insentif'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['tambahan'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['denda'],2)."</td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['gtl'],2)."</td>";
	$stream.="<td></td>";
	$stream.="</tr>";
	
	#JJG
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=right>Jjg</td>";
	for($i=$tglawal;$i<=$tglakhir;$i++){
		$stream.="<td align=right>".hidezerodecimal($ttljjg[addZero($i,2)],2)."</td>";
	}
	$stream.="<td></td>";
	$stream.="<td align=right>".hidezerodecimal($ttl['jjg'],2)."</td>";
	$stream.="<td></td>";
	foreach($arrtopografi as $key2=>$val2){
		$stream.="<td align=left></td>";
	}
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="<td align=left></td>";
	$stream.="</tr>";
	
	$stream.="</tr>";
	$stream.="</tbody></table>";
		
	
	if($tipe!='excel'){
		echo $stream;
	}else{
		$nop_="daftar_premi";
		if(strlen($stream)>0){
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						@unlink('tempExcel/'.$file);
					}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream)){
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}       
	}
	break;
}

?>