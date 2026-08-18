<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');


$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
//$per1 = checkPostGet('per1', '');
$per2 = checkPostGet('per2', '');



switch ($proses) {
	case'getdivisi':
		// echo"warning:masuk";
		$optDiv="";
		$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe in ('AFDELING','BIBITAN') and induk='".$kdorg."' order by kodeorganisasi asc ";
		

		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
			$optDiv.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
		
		echo $optDiv;
		break;
			

}
?>