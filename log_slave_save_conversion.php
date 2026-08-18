<?php

require_once('master_validation.php');
require_once('config/connection.php');

$jumlah = isset($_POST['jumlah']) ? $_POST['jumlah'] : '';
$kodebarang = $_POST['kodebarang'];
$dari = $_POST['dari'];
$ke = $_POST['ke'];
$method = $_POST['method'];
$keterangan = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';
$strx='';

switch ($method) {
    case 'delete':
        $strx = "delete from " . $dbname . ".log_5stkonversi where kodebarang='" . $kodebarang . "' 
			       and satuankonversi='" . $ke . "'
				   and darisatuan='" . $dari . "'";
        break;
    case 'update':

        break;
    case 'insert':
        $strx = "insert into " . $dbname . ".log_5stkonversi(
			       kodebarang,satuankonversi,darisatuan,jumlah,keterangan)
			values('" . $kodebarang . "','" . $ke . "','"
                . $dari . "'," . $jumlah . ",'" . $keterangan . "')";
        break;
    default:
        break;
}

if($strx!=''){
	try{
		$owlPDO->exec($strx); 
	}catch(PDOException $e){
		echo " Gagal," . addslashes($e->getMessage());
	}
}

$str = " select * from " . $dbname . ".log_5stkonversi where kodebarang='" . $kodebarang . "' order by jumlah";
$no = 0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$no+=1;
	echo"<tr class=rowcontent>
		  <td align=center>" . $no . "</td>
		  <td align=center>" . $bar->darisatuan . "</td>
		  <td align=center>" . $bar->satuankonversi . "</td>
		  <td align=right>" . $bar->jumlah . "</td>
		  <td>" . $bar->keterangan . "</td>
		  <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delConversi('" . $bar->kodebarang . "','" . $bar->darisatuan . "','" . $bar->satuankonversi . "');\"></td>
		 </tr>";
}
?>