<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
require_once('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$tipeprint=checkPostGet('tipeprint','');

$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$periode=checkPostGet('periode','');

switch($method){
	case'getUnit':
		$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe = 'KEBUN' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optunit;
	break;
	
	case'preview':
		$tab="";

		if($unit ==''){
			exit("Warning  : Unit wajib diisi" );
		}


		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='3'>".$_SESSION['lang']['nourut']."</th>
				<th colspan='7'>Daftar Sengketa</th>
				<th rowspan='3'>Alamat</th>
				<th rowspan='3'>Deskripsi Sengketa</th>
				<th colspan='4'>Data Survey Pengukuran dan Pemetaan</th>
				<th colspan='2'>Status</th>
				<th rowspan='3'>Catatan</th>";
		$tab.="</tr>";
		$tab.="<tr class=rowheader style='text-align:center ;font-weight:bold'>";
			$tab.="<th rowspan=2>Areal</th>";
			$tab.="<th rowspan=2>Diklaim</th>";
			$tab.="<th rowspan=2>Block / Desa</th>";
			$tab.="<th rowspan=2>Status Tanam</th>";
			$tab.="<th colspan=2>HA</th>";
			$tab.="<th rowspan=2>Kategori</th>";
			$tab.="<th rowspan=2>Tanggal Kasus</th>";
			$tab.="<th colspan=2>Data Pembasan</th>";
			$tab.="<th rowspan=2>Penyelesaian</th>";
			$tab.="<th colspan=2>Kasus</th>";
		$tab.="</tr>";
		$tab.="<tr class=rowheader style='text-align:center ;font-weight:bold'>";
			$tab.="<th>Kadiv</th>";
			$tab.="<th>SPP</th>";
			$tab.="<th>ID</th>";
			$tab.="<th>Name</th>";
			$tab.="<th>Lama</th>";
			$tab.="<th>Baru</th>";
		$tab.="</tr>";
		$tab.="</thead><tbody>";

			$str="select * from ".$dbname.".pad_lahan where unit = '".$unit."'  ";
			$res=fetchdata($str);
			foreach($res as $val){
				$listData[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['idlahan'];
				$lokasi[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['lokasi'];
				$kodeblok[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['kodeblok'];
				$statuskawasan[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['statuskawasan'];
				$alamat[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['alamat'];
			}

			$str="select * from ".$dbname.".pad_pembebasanlahan where unit = '".$unit."'   ";
			$res=fetchdata($str);
			foreach($res as $val){
				$listData[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['idlahan'];
				$kategorisengketa[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['kategorisengketa'];
				$deskripsisengketa[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['deskripsisengketa'];
				$tanggalsengketa[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['tanggalsengketa'];
				$penyelesaian[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['penyelesaian'];
				$catatan[$val['idlahan']][$val['pemilik']][$val['unit']] = $val['penyelesaian'];
			}
		

			$nmpemilik=makeOption($dbname,'pad_5masyarakat','padid,nama');

			$no =0 ;
			foreach($listData as $idlahan => $bar1){
				foreach($bar1 as $pemilik => $bar2){
					foreach($bar2 as $unit => $value){			
						$no++;
						$tab.="<tr class='rowcontent'>";
							$tab.="<td>".$no."</td>";
							$tab.="<td>".$lokasi[$idlahan][$pemilik][$unit]."</td>";
							$tab.="<td>".$nmpemilik[$pemilik]."</td>";
							$tab.="<td>".$kodeblok[$idlahan][$pemilik][$unit]."</td>";
							$tab.="<td>".$statuskawasan[$idlahan][$pemilik][$unit]."</td>";
							$tab.="<td></td>";
							$tab.="<td></td>";
							$tab.="<td>".$kategorisengketa[$idlahan][$pemilik][$unit]."</td>";
							$tab.="<td>".$alamat[$idlahan][$pemilik][$unit]."</td>";
							$tab.="<td>".$deskripsisengketa[$idlahan][$pemilik][$unit]."</td>";
							$tab.="<td>".$tanggalsengketa[$idlahan][$pemilik][$unit]."</td>";
							$tab.="<td></td>";
							$tab.="<td></td>";
							$tab.="<td>".$penyelesaian[$idlahan][$pemilik][$unit]."</td>";
							$tab.="<td></td>";
							$tab.="<td></td>";
							$tab.="<td>".$catatan[$idlahan][$pemilik][$unit]."</td>";
						$tab.="</tr>";
					}
				}
			}



			
		$tab.="</tbody></table>";
	
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_LuasanArae_".$pt."_".$periode;
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
}


?>