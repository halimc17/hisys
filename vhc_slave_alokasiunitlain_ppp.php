<?php
# CABANG PPP dari vhc_slave_alokasiunitlain.php (salinan awal, belum ada perbedaan). Dipanggil otomatis oleh js/keu_3posting.js (tujuanProses)
# hanya untuk unit yang induknya PPP; unit lain tetap memakai vhc_slave_alokasiunitlain.php. Perubahan khusus PPP tandai "#PPP".
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

if(count($_POST)>0){
	$param = $_POST;	
}else{
	$param = $_GET;	
}

#PPP: parameter divalidasi sebelum dipakai di query (hanya kode unit/kendaraan/periode/jenis yang sah); keluaran di-escape
$pesanParam = '';
$aturanAlk = array('method' => '/^alokasikelain$/', 'kodevhc' => '/^[A-Za-z0-9 ._\/-]{1,40}$/', 'unitsumber' => '/^[A-Za-z0-9]{1,10}$/', 'unitkirim' => '/^[A-Za-z0-9]{1,10}$/',
	'periode' => '/^[0-9]{4}-(0[1-9]|1[0-2])$/', 'jenis' => '/^(kirim|terima)$/', 'tipe' => '/^(Kegiatan|Workshop)$/');
foreach ($aturanAlk as $namaP => $polaP) {
	if (!isset($param[$namaP]) || !is_string($param[$namaP]) || !preg_match($polaP, $param[$namaP])) {
		$pesanParam = 'Warning : Parameter ' . $namaP . ' tidak valid.';
		break;
	}
}
if ($pesanParam != '') {
	exit($pesanParam);
}
$no = 0;
switch($param['method']){
	case'alokasikelain':
		$tab="<table class=sortable cellspacing=1 width=100%>";
		$tab.="<thead><tr class=rowheader>";
		$tab.="<th align=center>No</th>";
		$tab.="<th align=center>Tanggal</th>";
		$tab.="<th align=center>Notransaksi</th>";
		$tab.="<th align=center>Dari</th>";
		$tab.="<th align=center>Tujuan</th>";
		$tab.="<th align=center>Akun</th>";
		$tab.="<th align=center>Kegiatan</th>";
		$tab.="<th align=center>Keterangan</th>";
		$tab.="<th align=center>Jumlah<br>(HM/KM)</th>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";
		
		if($param['jenis']=='kirim'){
			if($param['tipe']=='Kegiatan'){				
				$str="select * from ".$dbname.".vhc_rundt_vw
					where tanggal like '".$param['periode']."%' and kodevhc='".$param['kodevhc']."'
					and substr(alokasibiaya,1,4) ='".$param['unitkirim']."' and   
					kodevhc in(select kodevhc from ".$dbname.".vhc_5master 
					where kodetraksi like '".$param['unitsumber']."%')"; 
			}else{
				$str="select a.*, a.kerusakan as keterangan,a.downtime as jumlah from ".$dbname.".vhc_penggantianht a 
					left join ".$dbname.".vhc_5master b on a.kodevhc=b.kodevhc
					where a.tanggal like '".$param['periode']."%' and  a.kodeorg like  '".$param['unitsumber']."%' and 
					b.kodetraksi not like '".$param['unitsumber']."%' and b.kodetraksi like '".$param['unitkirim']."%'
					and a.kodevhc='".$param['kodevhc']."'"; 
			}
		}else{
			if($param['tipe']=='Kegiatan'){		
				$str="select * from ".$dbname.".vhc_rundt_vw
					where tanggal like '".$param['periode']."%' and kodevhc='".$param['kodevhc']."'
					and substr(alokasibiaya,1,4) ='".$param['unitkirim']."' and   
					kodevhc in(select kodevhc from ".$dbname.".vhc_5master 
					where kodetraksi like '".$param['unitsumber']."%')"; 
			}else{
				$str="select a.*, a.kerusakan as keterangan,a.downtime as jumlah from ".$dbname.".vhc_penggantianht a 
					left join ".$dbname.".vhc_5master b on a.kodevhc=b.kodevhc
					where a.tanggal like '".$param['periode']."%' and  a.kodeorg not like  '".$param['unitkirim']."%' and 
					b.kodetraksi like '".$param['unitkirim']."%' and b.kodetraksi not like '".$param['unitsumber']."%'
					and a.kodevhc='".$param['kodevhc']."'"; 
			}
		}
		$res = fetchdata($str);
		$optkeg=makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
		$optcoa=makeOption($dbname,'keu_5akun','noakun,namaakun');
		foreach($res as $bar){
			$optakun=makeOption($dbname,'vhc_kegiatan','kodekegiatan,noakun');
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".htmlspecialchars($bar['tanggal'])."</td>";
			$tab.="<td align=center>".htmlspecialchars($bar['notransaksi'])."</td>";
			$tab.="<td align=center>".$param['unitsumber']."</td>";
			$tab.="<td align=center>".$param['unitkirim']."</td>";
			$tab.="<td align=left>".$optakun[$bar['jenispekerjaan']]." - ".$optcoa[$optakun[$bar['jenispekerjaan']]]."</td>";
			$tab.="<td align=left>".$bar['jenispekerjaan']." - ".$optkeg[$bar['jenispekerjaan']]."</td>";
			$tab.="<td align=left>".htmlspecialchars($bar['keterangan'])."</td>";
			$tab.="<td align=right>".number_format($bar['jumlah'],2)."</td>";
			$tab.="</tr>";
			
		}
		$tab.="</tbody>";
		$tab.="</table>";
		echo $tab;
	break;
}
?>