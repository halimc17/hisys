<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method      = checkPostGet('method', '');
$unit        = checkPostGet('unit', '');
$periode	 = checkPostGet('periode', '');
$jenis	     = checkPostGet('jenis', '');

$tglakhir= tglakhir($periode."-01");

switch ($method) {
case 'preview':
	$tab = "";
	if ($jenis != 'excel') {
		$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
	} else {
		$tab.="<table cellpadding=1 cellspacing=1 border=1>";
		$tab.="<thead>
		    <tr>
			  <td rowspan=2 align=center style='width:50px;'>No.</td>
			  <td rowspan=2 align=center style='width:100px;'>" . $_SESSION['lang']['periode'] . "</td>
			  <td rowspan=2 align=center style='width:100px;'>" . $_SESSION['lang']['kodebarang'] . "</td>
			  <td rowspan=2 align=center style='width:300px;'>" . $_SESSION['lang']['namabarang'] . "</td>
			  <td rowspan=2 align=center style='width:50px;'>" . $_SESSION['lang']['satuan'] . "</td>
			  <td rowspan=2 align=center style='width:75px;'>Tgl Terima</td>
			  <td rowspan=2 align=center style='width:100px;'>0 to 90 days</td>
			  <td rowspan=2 align=center style='width:100px;'>90 to 180 days</td>
			  <td rowspan=2 align=center style='width:100px;'>180 t0 360 days</td>
			  <td rowspan=2 align=center style='width:100px;'>Over 360 days</td>
			  <td rowspan=2 align=center style='width:100px;'>Days of inventory</td>
			  <td rowspan=2 align=center style='width:100px;'>Total</td>
			</tr>
		 </thead><tr>";
	}
	#ambil barang dan qty
	$str = "SELECT * FROM ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang like '".$unit."%' 
	order by kodebarang asc";
	$jlh = fetchdata($str); if(count($jlh)=='')exit("Warning : Datang Kosong !");
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$kodebarang[$bar['periode']][$bar['kodebarang']]=$bar['kodebarang'];
		@$saldo[$bar['periode']][$bar['kodebarang']]+=$bar['saldoakhirqty'];
	}
	
	$no='0';
	foreach ($kodebarang as $periode => $val){
		foreach($val as $kdbarang){
			#ambil terima terakhir
			$str = "SELECT * FROM ".$dbname.".log_transaksi_vw where kodebarang='".$kdbarang."' and kodegudang like '".$unit."%' and tipetransaksi='1' and nopo!='-' order by tanggal desc limit 1";
			$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$tglterima[$bar['kodebarang']]=$bar['tanggal'];
			}
			
			$nmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang',"kodebarang='".$kdbarang."'");
			$nmsat = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan',"kodebarang='".$kdbarang."'");
			
			$no += 1;
			$a = $no % 2;
			$xx =$umur= '';
			$umur1 =$umur2=$umur3=$umur4= '';
			if ($a == 1) {
				$xx.=" style=background-color:#F5EEF8 ";
			}
			$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$periode."</td>";
			$tab.="<td>".$kdbarang."</td>";
			$tab.="<td>".$nmbrg[$kdbarang]."</td>";
			$tab.="<td>".$nmsat[$kdbarang]."</td>";
			$tab.="<td align=center>".@$tglterima[$kdbarang]."</td>";
			if(@$tglterima[$kdbarang]==''){
				$tglterima[$kdbarang]=$tglakhir;
			}
			$a = datediff(@$tglterima[$kdbarang],$tglakhir);
			$umur = $a['days_total'];
			if($umur<=90){
				$umur1=$saldo[$periode][$kdbarang];
			}else if($umur > 90 and $umur <= 180){
				$umur2=$saldo[$periode][$kdbarang];
			}else if($umur > 180 and $umur <= 360){
				$umur3=$saldo[$periode][$kdbarang];
			}else{
				$umur4=$saldo[$periode][$kdbarang];
			}
			$tab.="<td align=right>".@number_format($umur1,2)."</td>";
			$tab.="<td align=right>".@number_format($umur2,2)."</td>";
			$tab.="<td align=right>".@number_format($umur3,2)."</td>";
			$tab.="<td align=right>".@number_format($umur4,2)."</td>";
			$tab.="<td align=right>".$a['days_total']."</td>";
			$tab.="<td align=right>".@number_format($saldo[$periode][$kdbarang],2)."</td>";
			$tab.="</tr>";
		}
	}
	
	if ($jenis != 'excel') {
		echo $tab;
	}  else {
		$stream = $tab;
		$nop_ = "Inventory_Ageing";
		if (strlen($stream) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						 @ unlink('tempExcel/'.$file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/".$nop_.".xls", 'w');
			if (!fwrite($handle, $stream)) {
				echo "<script language=javascript1.2>
				parent.window.alert('Cant convert to excel format');
				</script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			closedir($handle);
		}
	}
	break;
}
?>