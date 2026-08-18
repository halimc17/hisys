<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$method = checkPostGet('method', '');
$jumlah_fisik = checkPostGet('jumlah_fisik', '');
$jumlah_fisik = str_replace(",","",$jumlah_fisik);

$jumlah_fisik_old = checkPostGet('jumlah_fisik_old', '');
$jumlah_fisik_old = str_replace(",","",$jumlah_fisik_old);

$nmkary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
switch ($method) {
	case 'insert':
		// Cek Dulu Apakah Datanya pernah dibuat sebelumnya
		$scek = "SELECT * FROM $dbname.keu_5jumlahfisik WHERE jumlahfisik='".$jumlah_fisik."'";
		$rcek = fetchData($scek);
		$countcek = count($rcek);
		if ($countcek > 0) {
			exit("Warning: Data Jumlah Fisik " . number_format($jumlah_fisik,2) . " sudah pernah disimpan !");
		}

		try {
			$ha = "insert into " . $dbname . ".keu_5jumlahfisik (`jumlahfisik`,`updateby`,`createby`,`createtime`)
			values ('" . $jumlah_fisik . "','" . $_SESSION['standard']['userid'] . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "')";
			$owlPDO->exec($ha);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case 'update':
		// Cek Dulu Apakah Datanya pernah dibuat sebelumnya
		$scek = "SELECT * FROM $dbname.keu_5jumlahfisik WHERE jumlahfisik='".$jumlah_fisik."'";
		$rcek = fetchData($scek);
		$countcek = count($rcek);
		if ($countcek > 0) {
			exit("Warning: Data Jumlah Fisik " . number_format($jumlah_fisik,2) . " sudah pernah disimpan !");
		}

		try {
			$ha = "update " . $dbname . ".keu_5jumlahfisik set jumlahfisik='" . $jumlah_fisik . "', updateby='" . $_SESSION['standard']['userid'] . "' 
			where jumlahfisik='" . $jumlah_fisik_old . "'";
			$owlPDO->exec($ha);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case 'loadData':
		echo "<div id=container>
		<table class=sortable cellspacing=1 border=0 style=min-width:650px>
		<thead>
		<tr class=rowheader>
		<td align=center>No</td>
		<td align=center>Jumlah Fisik</td>
		<td align=center>" . $_SESSION['lang']['updateby'] . "</td>
		<td align=center width=50px>Action</td>
		</tr>
		</thead>
		<tbody>";
			$no = 0;
			$iList = "select jumlahfisik, updateby from " . $dbname . ".keu_5jumlahfisik order by jumlahfisik desc, updatetime desc";
			$res = $owlPDO->query($iList) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$countbrs = owlBaris($res);
			if ($countbrs  == 0) {
				echo "<tr class=rowcontent>";
					echo "<td align=center align='center' colspan='4'>" . $_SESSION['lang']['errdatanotexist'] . "</td>";
				echo "</tr>";
			} else {
				while ($dList = $res->fetch()) {
					$no += 1;
					echo "<tr class=rowcontent>";
					echo "<td align=center>" . $no . "</td>";
					echo "<td align=right>" . number_format($dList['jumlahfisik'],2) . "</td>";
					echo "<td align=left>" . $nmkary[$dList['updateby']] . "</td>";
					echo "<td align=center>";
						echo "<img src=images/application/application_edit.png class=resicon  title='Edit' style='margin-right:4px;'
						onclick=\"fillField('" . number_format($dList['jumlahfisik'],2) . "')\">";
					echo "</td>";
					echo "</tr>";
				}
				
			}
			
	break;
}
