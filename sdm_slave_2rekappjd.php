<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
require_once('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$tipeprint=checkPostGet('tipeprint','');

$unit=checkPostGet('unit','');
$kodeorgnya=checkPostGet('kodeorgnya','');
$subunit=checkPostGet('subunit','');
$tanggaldari=tanggalsystemn(checkPostGet('tanggaldari',''));
$tanggalsampai=tanggalsystemn(checkPostGet('tanggalsampai',''));
$tipekaryawan=checkPostGet('tipekaryawan','');

$notransaksi=checkPostGet('notransaksi','');

switch($method){
	case'getsubunit':
		$optSubUnit="<option value='all'>".$_SESSION['lang']['all']."</option>";
		$optSubUnit.="<option value=''>".$unit." - Kantor</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optSubUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optSubUnit;
	break;
	
	case'preview':		
		$tab="";
		
		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%;'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold;'>
				<th rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2>".$_SESSION['lang']['notransaksi']."</th>
				<th rowspan=2>".$_SESSION['lang']['nik']."</th>
				<th rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>
				<th rowspan=2>".$_SESSION['lang']['jabatan']."</th>
				<th rowspan=2>".$_SESSION['lang']['tujuan']."</th>
				<th rowspan=2>Region Tujuan</th>
				<th colspan=2>".$_SESSION['lang']['tanggal']." Pengajuan Dinas</th>
				<th rowspan=2>Uang Muka PJD</th>
				<th colspan=2>".$_SESSION['lang']['tanggal']." Real Dinas</th>
				<th rowspan=2>Uang Realisasi PJD</th>
				<th rowspan=2>Keterangan</th>
			</tr>";
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold;'>
				<th>".$_SESSION['lang']['tanggal']." Dari</th>
				<th>".$_SESSION['lang']['tanggal']." Sampai</th>

				<th>".$_SESSION['lang']['tanggal']." Dari</th>
				<th>".$_SESSION['lang']['tanggal']." Sampai</th>
		</tr>";
		

		
		$tab.="</thead><tbody>";


		## Uang Muka Perjalan Dinas
		$str = "select * from ".$dbname.".sdm_pjdinasdt where tanggal between '".$tanggaldari."' and '".$tanggalsampai."' and tanggungan =0 order by tanggal asc";
		$res = fetchdata($str);
        foreach($res as $val){
			$umPJD[$val['notransaksi']] += $val['jumlah'];
		}

		## Uang Real Perjalan Dinas
		$str = "select * from ".$dbname.".sdm_pjdinasdt where tanggal between '".$tanggaldari."' and '".$tanggalsampai."' and tanggungan =1 order by tanggal asc";
		$res = fetchdata($str);
        foreach($res as $val){
			$realPJD[$val['notransaksi']] += $val['jumlah'];
		}


		## Make Option
		$namaRegion = makeOption($dbname,'sdm_5regionalpjd','regional,nama');

		$no=0;
		$str = "select * from ".$dbname.".sdm_pjdinasht where kodeorg = '".$unit."' and tgldinasdari between '".$tanggaldari."' and '".$tanggalsampai."' order by tgldinasdari asc";
        $res = fetchdata($str);
        foreach($res as $val){
			$no++;
			$tab.="<tr class='rowcontent'>
					<td align='center'>".$no."</td>
					<td align='center'>".$val['notransaksi']."</td>
					<td align='center'>".getNik($val['karyawanid'])."</td>
					<td>".getNamaKaryawan($val['karyawanid'])."</td>
					<td>".getJabatanKaryawan($val['karyawanid'])."</td>
					<td>".$val['pttujuan']."</td>
					<td>".$namaRegion[$val['regiontujuan']]."</td>
					<td align='center'>".$val['tgldinasdari']."</td>
					<td align='center'>".$val['tgldinassampai']."</td>
					<td align='right' style=color:blue;cursor:pointer; onclick=\"detail('".$val['notransaksi']."')\">".number_format($umPJD[$val['notransaksi']])."</td>
					<td align='center'>".$val['tgldinasdarireal']."</td>
					<td align='center'>".$val['tgldinassampaireal']."</td>
					<td align='right' style=color:blue;cursor:pointer; onclick=\"detail2('".$val['notransaksi']."')\">".number_format($realPJD[$val['notransaksi']])."</td>
					<td>".$val['keterangan']."</td>";

					$ttlUMPD +=$umPJD[$val['notransaksi']];
					$ttlRPD +=$realPJD[$val['notransaksi']];
        }

		$tab.="</tr>";
		$tab.="<tr class='rowcontent'>";
			$tab.="<td align='center' colspan=9><b>TOTAL</b></td>";
			$tab.="<td align='center'><b>".number_format($ttlUMPD)."</b></td>";
			$tab.="<td align='center' colspan=2></td>";
			$tab.="<td align='center'><b>".number_format($ttlRPD)."</b></td>";
			$tab.="<td align='center'></td>";
		$tab.="</tr>";
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_RekapPJD_".$unit."_".$periode;
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				 $handle=fopen("tempExcel/".$nop_.".xls",'w');
				 if(!fwrite($handle,$tab))
				 {
				  echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
				   exit;
				 }
				 else
				 {
				  echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
			}
		}
	break;

	case'detail':

		
		$tab="";
        $tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
        <thead>
        <tr class=rowheader style='text-align:center;font-weight:bold;'>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['nourut']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['jenisbiaya']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['tanggal']."</th>
            <th style='text-transform: uppercase;'>Jumlah</th>
            <th style='text-transform: uppercase;'>Keterangan</th>";
		$tab.="</tr>";
		$tab.="</thead><tbody>";


		## Make Option
		$namaJenisBiaya = makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');

		## Ambil dari pjddinasdt
        $str1 = "select * from ".$dbname.".sdm_pjdinasdt where notransaksi = '".$notransaksi."' and tanggungan = 0 order by tanggal asc";
		$res1 = fetchdata($str1);

		$no = 0;
        foreach($res1 as $val){
			$no++;
			$tab.="<tr class='rowcontent'>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$namaJenisBiaya[$val['jenisbiaya']]."</td>";
				$tab.="<td align=center>".$val['tanggal']."</td>";
				$tab.="<td align=right>".number_format($val['jumlah'],0)."</td>";
				$tab.="<td>".$val['keterangan']."</td>";
				$ttl += $val['jumlah'];
        }
		$tab.="</tr>";
		$tab.="<tr class='rowcontent'>";
			$tab.="<td align=center colspan=3>TOTAL</td>";
			$tab.="<td align=center>".number_format($ttl)."</td>";
			$tab.="<td align=center></td>";

		$tab.="</tr>";
		echo $tab;

	break;
	case'detail2':

		
		$tab="";
        $tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
        <thead>
        <tr class=rowheader style='text-align:center;font-weight:bold;'>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['nourut']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['jenisbiaya']."</th>
            <th style='text-transform: uppercase;'>".$_SESSION['lang']['tanggal']."</th>
            <th style='text-transform: uppercase;'>Jumlah</th>
            <th style='text-transform: uppercase;'>Keterangan</th>";
		$tab.="</tr>";
		$tab.="</thead><tbody>";


		## Make Option
		$namaJenisBiaya = makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');

		## Ambil dari pjddinasdt
        $str1 = "select * from ".$dbname.".sdm_pjdinasdt where notransaksi = '".$notransaksi."' and tanggungan = 1 order by tanggal asc";
		$res1 = fetchdata($str1);

		$no = 0;
        foreach($res1 as $val){
			$no++;
			$tab.="<tr class='rowcontent'>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$namaJenisBiaya[$val['jenisbiaya']]."</td>";
				$tab.="<td align=center>".$val['tanggal']."</td>";
				$tab.="<td align=right>".number_format($val['jumlah'],0)."</td>";
				$tab.="<td>".$val['keterangan']."</td>";
				$ttl += $val['jumlah'];
        }
		$tab.="</tr>";
		$tab.="<tr class='rowcontent'>";
			$tab.="<td align=center colspan=3>TOTAL</td>";
			$tab.="<td align=center>".number_format($ttl)."</td>";
			$tab.="<td align=center></td>";

		$tab.="</tr>";
		echo $tab;

	break;
}


?>