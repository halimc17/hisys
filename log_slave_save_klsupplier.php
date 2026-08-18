<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$tipe = isset($_POST['tipe']) ? $_POST['tipe'] : '';
$nama = isset($_POST['nama']) ? $_POST['nama'] : '';
$tipe2 = isset($_POST['tipe2']) ? $_POST['tipe2'] : '';
$noakun = isset($_POST['noakun']) ? $_POST['noakun'] : '';
$method = isset($_POST['method']) ? $_POST['method'] : '';
$sync = isset($_POST['sync']) ? $_POST['sync'] : '';
$kelompok = isset($_POST['kelompok']) ? $_POST['kelompok'] : '';

$strx = "";
switch ($method) {
	case 'delete':
		$str = "select * from " . $dbname . ".log_5supkelompok where tipe='" . $tipe . "'";
		$res = fetchData($str);
		if (count($res) != 0) {
			exit("warning : Tipe " . $tipe . " sudah dipakai pada data supplier.");
		}
		$strx = "delete from " . $dbname . ".log_5klsupplier where tipe='" . $tipe . "'";
		break;
	case 'update':
		// $str = "select * from ".$dbname.".log_5supkelompok where tipe='".$tipe2."'";
		// $res=fetchData($str);
		// if(count($res)!=0){
		// 	exit("warning : Tipe ".$tipe." sudah dipakai pada data supplier.");
		// }		

		# Cek jika sudah ada supplier yang menggunakan kelompok ini, jika iya maka update table kolom noakun log_5supkelompok
		$cekSupKelompok = getCountRows($dbname, 'log_5supkelompok', "tipe='" . $tipe2 . "'");
		if ($cekSupKelompok > 0) {
			$strUpdateSupKelompok = "update " . $dbname . ".log_5supkelompok set noakun='" . $noakun . "' where tipe='" . $tipe2 . "'";
			try {
				$owlPDO->exec($strUpdateSupKelompok);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
				exit();
			}
		}
		$tipe = str_replace(' ', '', $tipe);
		$strx = "update " . $dbname . ".log_5klsupplier set tipe='" . $tipe . "',
				  noakun='" . $noakun . "',kode='" . $nama . "',kelompok='" . $kelompok . "' where tipe='" . $tipe2 . "'";
		break;
	case 'insert':
		$tipe = str_replace(' ', '', $tipe);
		$strx = "insert into " . $dbname . ".log_5klsupplier(
				   tipe,noakun,sync,kode,kelompok)
				values('" . $tipe . "','" . $noakun . "','1','" . $nama . "','" . $kelompok . "')";
		break;
	default:
		break;
}

if ($strx != '') {
	try {
		$owlPDO->exec($strx);
	} catch (PDOException $e) {
		echo " Gagal," . addslashes($e->getMessage());
	}
}

$optPt = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$str = " select * from " . $dbname . ".log_5klsupplier order by tipe";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
while ($bar = $res->fetch()) {
	$no += 1;
	echo "<tr class=rowcontent id=trlist" . $no . ">
			  <td align=center>" . $no . "</td>
			  <td>" . $bar->tipe . "</td>
			  <td>" . $bar->kode . "</td>
			  ";
	echo "<td align=center>" . $bar->noakun . "</td>
		<td align=center>" . $bar->kelompok . "</td>
			  <td align=center><img src=images/application/application_edit.png class=resicon  title='Update' onclick=\"editKlSupplier('" . $bar->tipe . "','" . $bar->kode . "','" . $bar->noakun . "','" . $bar->kelompok . "');\"></td>
			 
			 </tr>";
}
