<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method', '');
$idmerk = checkPostGet('idmerk', '');
$merk = checkPostGet('merk', '');
$status = checkPostGet('status', '');
$find_merk = checkPostGet('find_merk', '');
$arrstatus = array("0" => "Tidak aktif", "1" => "Aktif");

switch ($method) {
	case 'insert':
		//ambil nomor terakhir
		$str = "select max(substr(idmerk,2,6)) as idmerk from " . $dbname . ".log_5merkbaranght";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();

		$maxitem = addZero((intval($bar['idmerk'] + 1)), 6);
		$noidmerk = "M" . $maxitem;

		//cek apakah merk sudah ada ??
		$str = "select count(merk) as merk from " . $dbname . ".log_5merkbaranght where UPPER(merk) = '" . trim(strtoupper($merk)) . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$countitem = $bar['merk'];

		if ($countitem >= 1) {
			exit("Warning : Merk Barang sudah pernah terdaftar sebelumnya.");
		} else {
			$str = "insert into " . $dbname . ".log_5merkbaranght values ('" . $noidmerk . "','" . $merk . "','" . $status . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "','')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
		break;

	case 'update':
		$str = "update " . $dbname . ".log_5merkbaranght set status='" . $status . "', merk='{$merk}', updateby='" . $_SESSION['standard']['userid'] . "' where idmerk = '" . $idmerk . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'loaddata':

		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);

		if ($find_merk != '') {
			$where = " and merk LIKE  '%" . $find_merk . "%'";
		}

		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5merkbaranght where 0=0 " . $where . " order by idmerk desc";
		//exit('error '.$ql2);
		$query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}

		$tab = "<table class=sortable cellpadding=1 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>ID " . $_SESSION['lang']['merk'] . "</td>
				<td align=center>" . $_SESSION['lang']['merk'] . "</td>
				<td align=center>" . $_SESSION['lang']['status'] . "</td>
				<td align=center>" . $_SESSION['lang']['updateby'] . "</td>
				<td align=center colspan=2>" . $_SESSION['lang']['action'] . "</td>
			</tr>
			</thead>
			<tbody>";

		$no = 0;
		$str = "select * from " . $dbname . ".log_5merkbaranght where 0=0 " . $where . " order by idmerk desc LIMIT " . $offset . "," . $limit . "";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optNamaKar = makeOption($dbname, "datakaryawan", 'karyawanid,namakaryawan', "karyawanid='" . $bar['updateby'] . "'");
			$no++;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td>" . $bar['idmerk'] . "</td>";
			$tab .= "<td>" . $bar['merk'] . "</td>";
			$tab .= "<td align=center>" . $arrstatus[$bar['status']] . "</td>";
			$tab .= "<td align=left>" . $optNamaKar[$bar['updateby']] . "</td>";
			$tab .= "<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $bar['idmerk'] . "','" . $bar['merk'] . "','" . $bar['status'] . "');\"></td>";
			if ($bar['status'] == 1) {
				$tab .= "<td><img src=images/addplus.png class=resicon  title='Add detail merk per barang' onclick=\"detaildt('" . $_SESSION['lang']['detail'] . "','" . $bar['idmerk'] . "','" . $bar['merk'] . "');\">
			</td>";
			} else {
				$tab .= "<td></td>";
			}

			$tab .= "</tr>";
		}
		$totrows = ceil($jlhbrs / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}
		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
		}

		$tab .= "<tr><td colspan=6 align=center>";
		$tab .= "<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
		$tab .= "<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">" . $isiRow . "</select>";
		$tab .= "<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
		$tab .= "</td></tr>";

		echo $tab;
		break;

	default:
}
