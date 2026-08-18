<?php
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$tab.="<table id=pvtTable cellpadding=5 cellspacing=0 border=1 class='sortable nowrap'>
	<thead>
		<tr>
			<th align=center >No SPK</th>
			<th align=center >No BAPP</th>
			<th align=center >Jurnal</th>
			<th align=center >BAPP</th>
			<th align=center >Varian</th>
			
		</tr>
	</thead>
	<tbody>";

$str="select * from ".$dbname.".log_baspk";
$res=fetchdata($str);
foreach($res as $bar){
	$sql = "update ".$dbname.".log_baspk set keterangan='".trim($bar['keterangan'])."'
	where notransaksi='".$bar['notransaksi']."' and kodeblok='".$bar['kodeblok']."' and kodekegiatan='".$bar['kodekegiatan']."' and tanggal='".$bar['tanggal']."' and hasilkerjarealisasi='".$bar['hasilkerjarealisasi']."' and nopengajuan='".$bar['nopengajuan']."' and blokspkdt='".$bar['blokspkdt']."' and termin='".$bar['termin']."' and jenis='".$bar['jenis']."'
	 and hkrealisasi='".$bar['hkrealisasi']."' and jumlahrealisasi='".$bar['jumlahrealisasi']."' and jjgkontanan='".$bar['jjgkontanan']."' and posting='".$bar['posting']."' and statusjurnal='".$bar['statusjurnal']."' and statuspengajuan='".$bar['statuspengajuan']."' and keterangan='".$bar['keterangan']."'
	";
	try{
		$owlPDO->exec($sql); 
	}catch (PDOException $e){
		echo "Rollback Delete Header Error : ".$sql."<br>";
		//exit;
	}
}

	
$str="select notransaksi, termin, keterangan, sum(jumlahrealisasi) as jumlahrealisasi from ".$dbname.".log_baspk where statusjurnal='1' group by notransaksi, termin, keterangan";
$res=fetchdata($str);
foreach($res as $bar){
	$bar['keterangan']=trim($bar['keterangan']);
	$data[$bar['notransaksi']][$bar['keterangan']]=$bar['keterangan'];
	$bapp[$bar['notransaksi']][$bar['keterangan']]+=$bar['jumlahrealisasi'];
}

$sql = "select sum(debet) as debet, sum(kredit) as kredit, noreferensi, nodok from ".$dbname.".keu_jurnaldt_vw 
where trim(noreferensi) in (select notransaksi from ".$dbname.".log_baspk where statusjurnal='1') and trim(nodok) in (select trim(keterangan) as keterangan from ".$dbname.".log_baspk where statusjurnal='1') group by nodok, noreferensi";   
$req=fetchdata($sql);
foreach($req as $bar){
	$bar['noreferensi']=trim($bar['noreferensi']);
	$bar['nodok']=trim($bar['nodok']);
	$data[$bar['noreferensi']][$bar['nodok']]=$bar['nodok'];
	$jurnal[$bar['noreferensi']][$bar['nodok']]+=$bar['debet'];
	
	
}

// $sql = "select * from ".$dbname.".keu_jurnaldt where trim(noreferensi) in (select notransaksi from ".$dbname.".log_baspk where statusjurnal='1')";   
// $req=fetchdata($sql);
// foreach($req as $bar){
	// $sql = "update ".$dbname.".keu_jurnaldt set nodok='".trim($bar['nodok'])."', noreferensi='".trim($bar['noreferensi'])."' 
	// where nojurnal='".$bar['nojurnal']."' and tanggal='".$bar['tanggal']."' and nourut='".$bar['nourut']."' and noakun='".$bar['noakun']."' and keterangan='".$bar['keterangan']."' and jumlah='".$bar['jumlah']."' and kodeorg='".$bar['kodeorg']."' and kodekegiatan='".$bar['kodekegiatan']."' and kodeasset='".$bar['kodeasset']."' and kodebarang='".$bar['kodebarang']."' and nik='".$bar['nik']."' and kodecustomer='".$bar['kodecustomer']."' and kodesupplier='".$bar['kodesupplier']."' and noaruskas='".$bar['noaruskas']."' and kodevhc='".$bar['kodevhc']."' and kodeblok='".$bar['kodeblok']."'";
	// try{
		// $owlPDO->exec($sql); 
	// }catch (PDOException $e){
		// echo "Rollback Delete Header Error : ".$e->getMessage();
		// exit;
	// }
// }




foreach($data as $nospk => $v1){
	foreach($v1 as $nobapp){
		$tab.="<tr class=rowcontent>";
		$tab.="<td>".$nospk."</td>
			<td>".$nobapp."</td>
			<td>".number_format($jurnal[$nospk][$nobapp])."</td>
			<td>".number_format($bapp[$nospk][$nobapp])."</td>
			<td>".number_format($bapp[$nospk][$nobapp]-$jurnal[$nospk][$nobapp])."</td>
			";
	}
}

echo $tab;

?>		