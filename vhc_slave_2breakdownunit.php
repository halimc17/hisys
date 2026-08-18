<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses  = checkPostGet('proses','');
$comId   = checkPostGet('comId','');
$kdVhc   = checkPostGet('kdVhc','');
$jnsVhc  = checkPostGet('jnsVhc','');
$alokasi = checkPostGet('alokasi','');
$tglAwal = tanggalsystem(checkPostGet('tglAwal',''));
$tglAkhir= tanggalsystem(checkPostGet('tglAkhir',''));
$where2  = 'kelompokbarang=351';
$optBrg  =makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang',$where2); 


switch($proses){
	case'getJnsVhc':
	$optOrg=makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
	$optJnsvhc="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sjnsVhc="select distinct jenisvhc from ".$dbname.".vhc_5master where kodeorg='".substr($comId,0,4)."'"; 
	//echo "warning:".$sjnsVhc;
	$qjnsVhc = $owlPDO->query($sjnsVhc) or die(print " Gagal: " . PDOException::getMessage());
	$qjnsVhc->setFetchMode(PDO::FETCH_ASSOC);		
	while($rjnsVhc=$qjnsVhc->fetch()){
		$optJnsvhc.="<option value='".$rjnsVhc['jenisvhc']."'>".$optOrg[$rjnsVhc['jenisvhc']]."</option>";
	}
	echo $optJnsvhc;
	break;
	
	case'getKdvhc':
	$optKvhc="<option value='%'>".$_SESSION['lang']['all']."</option>";
	$skdVhc="select kodevhc from ".$dbname.".vhc_5master where jenisvhc='".$jnsVhc."'"; //echo "warning:".$skdVhc;
	$qkdVhc = $owlPDO->query($skdVhc) or die(print " Gagal: " . PDOException::getMessage());
	$qkdVhc->setFetchMode(PDO::FETCH_ASSOC);
	while($rkdVhc=$qkdVhc->fetch()){
		$e="";
		if(getNopol($rkdVhc['kodevhc'])!=''){
			$e.= " - ".getNopol($rkdVhc['kodevhc']);
		}
		if(getNopol($rkdVhc['kodevhc'],'d')!=''){
			$e.= " - ".getNopol($rkdVhc['kodevhc'],'d');
		}
		
		$optKvhc.="<option value='".$rkdVhc['kodevhc']."'>".$rkdVhc['kodevhc'].$e."</option>";
	}
	echo $optKvhc;
	break;
	
	case'get_result':
		if($comId=='')
		{
			echo"Warning : Unit Tidak Boleh Kosong";
			exit();
		}
		//  if($jnsVhc=='')
		// {
		// 	echo"Warning : Jenis Kendaraan Tidak Boleh Kosong";
		// 	exit();
		// }
		//  if($kdVhc=='')
		// {
		// 	echo"Warning : Kode Kendaraan Tidak Boleh Kosong";
		// 	exit();
		// }
		if($tglAkhir==''||$tglAwal=='')
		{
			echo"Warning : Tanggal Tidak Boleh Kosong";
			exit();
		}
		if($jnsVhc != ''){
			$whrjns = "and c.jenisvhc = '".$jnsVhc."'";
		}else{
			$whrjns = "";
		}
		if($kdVhc != ''){
			$whrvhc = "and a.kodevhc like '".$kdVhc."'";
		}else{
			$whrvhc = "";
		}
		$sql="select a.notransaksi,a.tanggal, a.tanggalkeluar, a.kodevhc, a.downtime, 
			a.kmmasuk, a.kmkeluar, a.kerusakan, a.terlambat, c.namajenisvhc from ".$dbname.".vhc_penggantianht a
			left join ".$dbname.".vhc_5master b
			on a.kodevhc = b.kodevhc 
			left join ".$dbname.".vhc_5jenisvhc c 
			on b.jenisvhc = c.jenisvhc 
			where a.tanggal between '".$tglAwal."' and '".$tglAkhir."' 
			".$whrjns."
			".$whrvhc." 
			order by a.notransaksi, a.kodevhc asc";
		echo"
			<table cellspacing=1 cellpadding=5 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<th align=center>No.</th>
					<th align=center>".$_SESSION['lang']['notransaksi']."</th>
					<th align=center>".$_SESSION['lang']['tanggalmasuk']." </th>
					<th align=center>".$_SESSION['lang']['tanggalkeluar']." </th>
					<th align=center>".$_SESSION['lang']['jenisvch']."</th>
					<th align=center>".$_SESSION['lang']['kodevhc']."</th>
					<th align=center>".$_SESSION['lang']['nopol']."</th>
					<th align=center>".$_SESSION['lang']['detail']."</th>
					<th align=center width=75px>".$_SESSION['lang']['downtime']." (".$_SESSION['lang']['jmlhJam'].")"."</th>
					<th align=center>KM/HM ".$_SESSION['lang']['masuk']."</th>
					<th align=center>KM/HM ".$_SESSION['lang']['keluar']."</th>
					<th align=center>".$_SESSION['lang']['descDamage']."</th>
					<th align=center>".$_SESSION['lang']['alasan']." </th>
				</tr>
			</thead>
			<tbody>";
		if(count(fetchData($sql))>0){
			$qRvhc = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
			$qRvhc->setFetchMode(PDO::FETCH_ASSOC); 
			$old='';
			while($res=$qRvhc->fetch())
			{
				$no+=1;
				echo"<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td>".$res['notransaksi']."</td>
					<td align=center>".tanggalnormal($res['tanggal'])."</td>
					<td align=center>".tanggalnormal($res['tanggalkeluar'])."</td>
					<td>".$res['namajenisvhc']."</td>
					<td>".$res['kodevhc']."</td>
					<td>".getNopol($res['kodevhc'])."</td>
					<td>".getNopol($res['kodevhc'],'d')."</td>
					<td align=center>".$res['downtime']."</td>
					<td align=right>".$res['kmmasuk']."</td>
					<td align=right>".$res['kmkeluar']."</td>
					<td>".$res['kerusakan']."</td>
					<td>".$res['terlambat']."</td>
					</tr>";
				$ttl+=$res['downtime'];	
			}
			echo"<tr class=rowcontent>
					<td align=center colspan=8>TOTAL</td>
					<td align=center>".$ttl."</td>
					<td align=center colspan=4></td>
				</tr>";
		}else{
			echo "<tr class=rowcontent><td colspan=13 align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}
		
		echo"</tbody></table>";
	
	break;
	
	case'getResultKry':
	$sRvhc="select a.*,b.jenispekerjaan,b.jumlahrit,b.keterangan from ".$dbname.".vhc_runht 
	a inner join ".$dbname.".vhc_rundt b on a.notransaksi=b.notransaksi 
	inner join ".$dbname.".vhc_runhk c on b.notransaksi=c.notransaksi 
	where c.idkaryawan='".$kryId."' order by a.tanggal asc"; 
	$qRvhc = $owlPDO->query($sRvhc) or die(print " Gagal: " . PDOException::getMessage());
	$qRvhc->setFetchMode(PDO::FETCH_ASSOC); 
	while($rRvhc=$qRvhc->fetch())
	{
	$no+=1;
	echo"
	<tr class=rowcontent>
		<td align=center>".$no."</td>
		<td align=center>".$rRvhc['notransaksi']."</td>
		<td align=center>".tanggalnormal($rRvhc['tanggal'])."</td>
		<td align=center>".$rRvhc['kmhmawal']."</td>
		<td align=center>".$rRvhc['kmhmakhir']."</td>
		<td align=center>".$rRvhc['jumlah']."</td>
		<td align=center>".$rRvhc['jenispekerjaan']."</td>
		<td align=center>".$rRvhc['keterangan']."</td>
		<td align=center>".$rRvhc['jumlahrit']."</td>
		<td align=center>".$rRvhc['jlhbbm']."</td>
	</tr>
	";
	}
	break;
	
	case'excel':
		if($comId=='')
		{
			echo"Warning : Unit Tidak Boleh Kosong";
			exit();
		}
		//  if($jnsVhc=='')
		// {
		// 	echo"Warning : Jenis Kendaraan Tidak Boleh Kosong";
		// 	exit();
		// }
		//  if($kdVhc=='')
		// {
		// 	echo"Warning : Kode Kendaraan Tidak Boleh Kosong";
		// 	exit();
		// }
		if($tglAkhir==''||$tglAwal=='')
		{
			echo"Warning : Tanggal Tidak Boleh Kosong";
			exit();
		}
		
		if($jnsVhc != ''){
			$whrjns = "and c.jenisvhc = '".$jnsVhc."'";
		}else{
			$whrjns = "";
		}
		if($kdVhc != ''){
			$whrvhc = "and a.kodevhc like '".$kdVhc."'";
		}else{
			$whrvhc = "";
		}
		$sVhc="select a.notransaksi,a.tanggal, a.tanggalkeluar, a.kodevhc, a.downtime, 
			a.kmmasuk, a.kmkeluar, a.kerusakan, a.terlambat, c.namajenisvhc from ".$dbname.".vhc_penggantianht a
			left join ".$dbname.".vhc_5master b
			on a.kodevhc = b.kodevhc 
			left join ".$dbname.".vhc_5jenisvhc c 
			on b.jenisvhc = c.jenisvhc 
			where a.tanggal between '".$tglAwal."' and '".$tglAkhir."' 
			".$whrjns."
			".$whrvhc." 
			order by a.notransaksi asc";
			
		$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".substr($comId,0,4)."'";
		$namapt='COMPANY NAME';
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ); 	
		while($bar=$res->fetch()){
			$namapt=strtoupper($bar->namaorganisasi);
		}

		$stream="
		<table>
		<tr><td colspan=11 align=center>".$_SESSION['lang']['breakdown']." / ".$_SESSION['lang']['unit']."</td></tr>";
		if($comId!='')
		{
			$stream.="
		<tr><td>".$_SESSION['lang']['unit']."<td colspan=3> : ".$namapt."</td></tr>";
		}
		
		$stream.="
		<tr><td>".$_SESSION['lang']['periode']."<td colspan=3> : ".$_GET['tglAwal']."-".$_GET['tglAkhir']."</td></tr>";
		
		$stream.="
		<tr><td>&nbsp;</td></tr>
		</table>
		<table border=1 bgcolor=#DEDEDE >
		<tr>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['tanggalmasuk']." </td>
			<td align=center>".$_SESSION['lang']['tanggalkeluar']." </td>
			<td align=center>".$_SESSION['lang']['jenisvch']."</td>
			<td align=center>".$_SESSION['lang']['kodevhc']."</td>
			<td align=center>".$_SESSION['lang']['downtime']."(".$_SESSION['lang']['jmlhJam'].")"."</td>
			<td align=center>KM/HM ".$_SESSION['lang']['masuk']."</td>
			<td align=center>KM/HM ".$_SESSION['lang']['keluar']."</td>
			<td align=center>".$_SESSION['lang']['descDamage']."</td>
			<td align=center>".$_SESSION['lang']['alasan']."</td>
		</tr>
		</table>						
		";
		
		$stream.="<table border='1'>";
		$no=0;
		$arrPos=array("Sopir","Kondektur");
		$rVhc = $owlPDO->query($sVhc) or die(print " Gagal: " . PDOException::getMessage());
		$rVhc->setFetchMode(PDO::FETCH_ASSOC); 
		while($bVhc=$rVhc->fetch())
		{
			$no+=1;
			$stream.="
			<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td>".$bVhc['notransaksi']."</td>
				<td>".tanggalnormal($bVhc['tanggal'])."</td>
				<td>".tanggalnormal($bVhc['tanggalkeluar'])."</td>
				<td>".$bVhc['namajenisvhc']."</td>
				<td>".$bVhc['kodevhc']."</td>
				<td>".$bVhc['downtime']."</td>
				<td>".$bVhc['kmmasuk']."</td>
				<td>".$bVhc['kmkeluar']."</td>
				<td>".$bVhc['kerusakan']."</td>
				<td>".$bVhc['terlambat']."</td>
			</tr>";
		}
	
		$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
		$dte=date("Hms");
		$nop_="BreakdownUnit__".$dte;
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		gzwrite($gztralala, $stream);
		gzclose($gztralala);
		echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls.gz';
			</script>";

	break;
	default:
	break;
}

?>