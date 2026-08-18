<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kode_org = checkPostGet('kd_org', '');
$blok = checkPostGet('blok', '');
$normh = checkPostGet('no_rmh', '');
$type_rmh = checkPostGet('tipermh', '');
$thn_bgn = checkPostGet('thnbgn', '');
$kndsi = checkPostGet('kndsi_rmh', '');
$pintu_rmh = checkPostGet('pintu_rmh', '');
$catatan = checkPostGet('note', '');
$alamat = checkPostGet('almt_rmh', '');
$kde_asset = checkPostGet('kd_asset', '');
$method = checkPostGet('method', '');
$user_id = $_SESSION['standard']['userid'];
$kary_id = checkPostGet('kd_kary', '');
$org_code = checkPostGet('code_org', '');
$kompleks = checkPostGet('kmplk', '');
$oldasset = checkPostGet('oldasset', '');

switch ($method) {
	case 'save_header':
		if (($kode_org == '') || ($blok == '') || ($normh == '')) {
			echo "warning:Please Complete The Form";
			exit();
		} else {
			if (strlen($blok) < 2) {
				echo "Warning : Blok Rumah Minimal 2 Karakter.";
				exit();
			}
			$normh = $kode_org . $blok . $normh;
			$sql = "select * from " . $dbname . ".sdm_perumahanht where norumah='" . $normh . "'";
			$query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
			if (owlBaris($query) > 0) {

				echo "warning:data telah terinput silahkan ulangi kembali";
			} else {
				$insrt = "insert into " . $dbname . ".sdm_perumahanht (`kodeorg`,`kompleks`,`blok`,`norumah`,`tipe`,`tahunpembuatan`,`kondisi`,`keterangan`,`alamat`,`user`, `jumlahpintu`) 
					values ('" . $kode_org . "','" . $kompleks . "','" . $blok . "','" . $normh . "','" . $type_rmh . "','" . $thn_bgn . "','" . $kndsi . "','" . $catatan . "','" . $alamat . "','" . $user_id . "','" . $pintu_rmh . "')";
				try {
					$owlPDO->exec($insrt);
				} catch (PDOException $e) {
					echo "Gagal : " . $e->getMessage();
				}
			}
		}
		break;

	case 'save_asset':
		if (($kode_org == '') || ($blok == '') || ($normh == '') || ($kde_asset == '')) {
			echo "Warning:Please Complete The Form";
			exit();
		} else {
			// $normh=$kode_org.$blok.$normh;
			$sql = "insert into " . $dbname . ".sdm_perumahandt (`kodeorg`,`blok`,`norumah`,`kodeasset`) values ('" . $kode_org . "','" . $blok . "','" . $normh . "','" . $kde_asset . "')";
			try {
				$owlPDO->exec($sql);
			} catch (PDOException $e) {
				echo "Gagal : " . $e->getMessage();
			}
		}
		break;
	case 'save_penghuni':
		if (($kode_org == '') || ($blok == '') || ($normh == '') || ($kary_id == '')) {
			echo "warning:Please Complete The Form";
			exit();
		} else {
			$sql2 = "select karyawanid from " . $dbname . ".sdm_penghunirumah where `karyawanid`='" . $kary_id . "'";
			$query2 = $owlPDO->query($sql2) or die(print " Gagal: " . PDOException::getMessage());

			if (owlBaris($query2) > 0) {
				echo "Warning:Can`t Use That Name";
				exit();
			} else {
				$sql = "insert into " . $dbname . ".sdm_penghunirumah (`kodeorg`,`blok`,`norumah`,`user`,`karyawanid`) values ('" . $kode_org . "','" . $blok . "','" . $normh . "','" . $user_id . "','" . $kary_id . "')";
				try {
					$owlPDO->exec($sql);
				} catch (PDOException $e) {
					echo "Gagal : " . $e->getMessage();
				}
			}
		}
		break;

	//al about update 
	case 'update_headher':
		$normh = $kode_org . $blok . $normh;
		$sql = "update " . $dbname . ".sdm_perumahanht set `kompleks`='" . $kompleks . "',`tipe`='" . $type_rmh . "',`tahunpembuatan`='" . $thn_bgn . "',`kondisi`='" . $kndsi . "',`keterangan`='" . $catatan . "',`alamat`='" . $alamat . "',`user`='" . $user_id . "', `jumlahpintu`='" . $pintu_rmh . "' where `kodeorg`='" . $kode_org . "' and `blok`='" . $blok . "' and `norumah`='" . $normh . "'";
		try {
			$owlPDO->exec($sql);
		} catch (PDOException $e) {
			echo "Gagal : " . $e->getMessage();
		}
		break;

	case 'update_asset':


		$sql = "update " . $dbname . ".sdm_perumahandt set kodeasset='" . $kde_asset . "' where `kodeorg`='" . $kode_org . "' and `blok`='" . $blok . "' and `norumah`='" . $normh . "' and kodeasset='" . $oldasset . "'";
		// exit('warning : '.$sql);
		try {
			$owlPDO->exec($sql);
		} catch (PDOException $e) {
			echo "Gagal : " . $e->getMessage();
		}
		break;
	case 'update_penghuni':

		$sql = "update " . $dbname . ".sdm_penghunirumah set karyawanid='" . $kary_id . "',`user`='" . $user_id . "' where `kodeorg`='" . $kode_org . "' and `blok`='" . $blok . "' and `norumah`='" . $normh . "'";
		try {
			$owlPDO->exec($sql);
		} catch (PDOException $e) {
			echo "Gagal : " . $e->getMessage();
		}
		break;

	//load new data
	case 'load_new_data':
		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$sql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_perumahanht where kodeorg='" . $org_code . "'";
		$str = "select * from " . $dbname . ".sdm_perumahanht where kodeorg='" . $org_code . "' order by `updatetime` desc limit " . $offset . "," . $limit . "";
		$query2 = $owlPDO->query($sql2) or die(print " Gagal: " . PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}
		$isiOpt = array('B-BD' => 'Baik Bisa Dipakai', 'B-TD' => 'Baik Tidak Dipakai', 'R-BD' => 'Rusak Bisa dipakai', 'R-TD' => 'Rusak Tidak Dipakai');
		$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		while ($res = $query->fetch()) {
			$no += 1;
			echo "<tr class=\"rowcontent\" id=detail_tra_" . $no . ">
		<td>" . $no . "</td>
		<td>" . $res['kodeorg'] . "</td>
                    <td>" . $res['kompleks'] . "</td>    
		<td>" . $res['blok'] . "</td>
		<td align=center>" . substr($res['norumah'], 6) . "</td>
		<td>" . $res['tipe'] . "</td>
		<td>" . $res['tahunpembuatan'] . "</td>
		<td align=center>" . $isiOpt[$res['kondisi']] . "</td>
		<td align=center>" . $res['jumlahpintu'] . " Pintu</td>
		<td>" . $res['keterangan'] . "</td><td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $res['kodeorg'] . "','" . $res['blok'] . "','" . substr($res['norumah'], 6) . "','" . $res['tipe'] . "','" . $res['tahunpembuatan'] . "','" . $res['kondisi'] . "','" . $res['keterangan'] . "','" . $res['alamat'] . "');\" >
		<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delHeader('" . $res['kodeorg'] . "','" . $res['blok'] . "','" . $res['norumah'] . "', '" . $res['jumlahpintu'] . "');\" >
		</td>";
		}
		echo "<tr>
		<td colspan=9 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " .  $jlhbrs . "
		<br>
		<button class=mybutton onclick=cariBast('" . ($page - 1) . "','" . $org_code . "');>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=cariBast('" . ($page + 1) . "','" . $org_code . "');>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>
		</tr>";
		break;
	///load new data  assset
	case 'load_new_data_asset':
		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$sql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_perumahandt where kodeorg='" . $org_code . "'";
		$str = "select * from " . $dbname . ".sdm_perumahandt where kodeorg='" . $org_code . "' ORDER BY `updatetime` desc limit " . $offset . "," . $limit . "";
		$query2 = $owlPDO->query($sql2) or die(print " Gagal: " . PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}
		$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		while ($res = $query->fetch()) {
			$sql3 = "select namasset from " . $dbname . ".sdm_daftarasset where `kodeasset`='" . $res['kodeasset'] . "'";
			$query3 = $owlPDO->query($sql3) or die(print " Gagal: " . PDOException::getMessage());
			$query3->setFetchMode(PDO::FETCH_ASSOC);
			$res3 = $query3->fetch();
			//$res['kodeasset']=$res3['namasset'];
			$no += 1;
			echo "<tr class=\"rowcontent\" id=detail_trp_" . $no . ">
		<td>" . $no . "</td>
		<td>" . $res['kodeorg'] . "</td>
		<td>" . $res['blok'] . "</td>
		<td align=center>" . substr($res['norumah'], 6) . "</td>
		<td>" . $res3['namasset'] . "</td>
		<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillFieldAsset('" . $res['kodeorg'] . "','" . $res['blok'] . "','" . $res['norumah'] . "','" . $res['kodeasset'] . "','" . $res3['namasset'] . "');\" >
	<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delAsset('" . $res['kodeorg'] . "','" . $res['blok'] . "','" . $res['norumah'] . "','" . $res['kodeasset'] . "');\" >
	</td>";
		}
		echo "<tr>
	<td colspan=7 align=center>
	" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " .  $jlhbrs . "
	<br>
	<button class=mybutton onclick=cariBastAsset('" . ($page - 1) . "','" . $org_code . "');>" . $_SESSION['lang']['pref'] . "</button>
	<button class=mybutton onclick=cariBastAsset('" . ($page + 1) . "','" . $org_code . "');>" . $_SESSION['lang']['lanjut'] . "</button>
	</td>
	</tr>";
		break;
	///load new data  penghuni
	case 'load_new_data_penghuni':
		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$sql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_penghunirumah where kodeorg='" . $org_code . "'";
		$str = "select * from " . $dbname . ".sdm_penghunirumah where kodeorg='" . $org_code . "' order by `updatetime` desc limit " . $offset . "," . $limit . "";
		$query2 = $owlPDO->query($sql2) or die(print " Gagal: " . PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}
		$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		while ($res = $query->fetch()) {
			$sdt_kry = "select namakaryawan from " . $dbname . ".datakaryawan where `karyawanid`='" . $res['karyawanid'] . "'";
			$qdt_kry = $owlPDO->query($sdt_kry) or die(print " Gagal: " . PDOException::getMessage());
			$qdt_kry->setFetchMode(PDO::FETCH_ASSOC);
			$rdt_kry = $qdt_kry->fetch();
			$no += 1;
			echo "<tr class=\"rowcontent\" id=detail_tr>
		<td>" . $no . "</td>
		<td>" . $res['kodeorg'] . "</td>
		<td>" . $res['blok'] . "</td>
		<td>" . substr($res['norumah'], 6) . "</td>
		<td>" . $rdt_kry['namakaryawan'] . "</td>
		<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillFieldPenghuni('" . $res['kodeorg'] . "','" . $res['blok'] . "','" . $res['norumah'] . "','" . $res['karyawanid'] . "');\" >
	<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPenghuni('" . $res['kodeorg'] . "','" . $res['blok'] . "','" . $res['norumah'] . "','" . $res['karyawanid'] . "');\" >
	</td>";
		}
		echo "<tr>
	<td colspan=7 align=center>
	" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " .  $jlhbrs . "
	<br>
	<button class=mybutton onclick=cariBastPenghuni('" . ($page - 1) . "','" . $org_code . "');>" . $_SESSION['lang']['pref'] . "</button>
	<button class=mybutton onclick=cariBastPenghuni('" . ($page + 1) . "','" . $org_code . "');>" . $_SESSION['lang']['lanjut'] . "</button>
	</td>
	</tr>";
		break;

	//delete section 
	case 'delHeader':
		$sql = "delete from " . $dbname . ".sdm_perumahanht where `kodeorg`='" . $kode_org . "' and `blok`='" . $blok . "' and `norumah`='" . $normh . "'";
		try {
			$owlPDO->exec($sql);

			$sql2 = "delete from " . $dbname . ".sdm_perumahandt where `kodeorg`='" . $kode_org . "' and `blok`='" . $blok . "' and `norumah`='" . $normh . "'";
			try {
				$owlPDO->exec($sql2);
			} catch (PDOException $e) {
				echo "Gagal : " . $e->getMessage();
			}

			$sql3 = "delete from " . $dbname . ".sdm_penghunirumah where `kodeorg`='" . $kode_org . "' and `blok`='" . $blok . "' and `norumah`='" . $normh . "'";
			try {
				$owlPDO->exec($sql3);
			} catch (PDOException $e) {
				echo "Gagal : " . $e->getMessage();
			}
		} catch (PDOException $e) {
			echo "Gagal : " . $e->getMessage();
		}
		break;

	case 'delAsset':
		$sql = "delete from " . $dbname . ".sdm_perumahandt where `kodeorg`='" . $kode_org . "' and `blok`='" . $blok . "' and `norumah`='" . $normh . "' and `kodeasset`='" . $kde_asset . "'";
		try {
			$owlPDO->exec($sql);
		} catch (PDOException $e) {
			echo "Gagal : " . $e->getMessage();
		}
		break;

	case 'delPenghuni':
		$sql = "delete from " . $dbname . ".sdm_penghunirumah where `kodeorg`='" . $kode_org . "' and `blok`='" . $blok . "' and `norumah`='" . $normh . "' and `karyawanid`='" . $kary_id . "'";
		try {
			$owlPDO->exec($sql);
		} catch (PDOException $e) {
			echo "Gagal : " . $e->getMessage();
		}
		break;

	case 'getData':
		$normh = $org_code . $blok . $normh;
		$sGet = "select * from " . $dbname . ".sdm_perumahanht where norumah='" . $normh . "'";
		$qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
		$qGet->setFetchMode(PDO::FETCH_ASSOC);
		$rGet = $qGet->fetch();
		echo $rGet['tipe'] . "###" . $rGet['tahunpembuatan'] . "###" . $rGet['kondisi'] . "###" . $rGet['keterangan'] . "###" . $rGet['alamat'] . "###" . $rGet['kompleks'] . "###" . $rGet['jumlahpintu'];
		break;
	default:
		break;
}
