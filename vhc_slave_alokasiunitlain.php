<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

if(count($_POST)>0){
	$param = $_POST;	
}else{
	$param = $_GET;	
}

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
			$tab.="<td align=center>".$bar['tanggal']."</td>";
			$tab.="<td align=center>".$bar['notransaksi']."</td>";
			$tab.="<td align=center>".$param['unitsumber']."</td>";
			$tab.="<td align=center>".$param['unitkirim']."</td>";
			$tab.="<td align=left>".$optakun[$bar['jenispekerjaan']]." - ".$optcoa[$optakun[$bar['jenispekerjaan']]]."</td>";
			$tab.="<td align=left>".$bar['jenispekerjaan']." - ".$optkeg[$bar['jenispekerjaan']]."</td>";
			$tab.="<td align=left>".$bar['keterangan']."</td>";
			$tab.="<td align=right>".number_format($bar['jumlah'],2)."</td>";
			$tab.="</tr>";
			
		}
		$tab.="</tbody>";
		$tab.="</table>";
		echo $tab;
	break;
}
?>