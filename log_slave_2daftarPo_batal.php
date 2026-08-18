<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
$method = $_POST['method'];
switch ($method) {
    case 'list_new_data':
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $txt_search = '';
        $txt_tgl = '';
        if (!empty($_POST['txtSearch'])) {
            $txt_search = $_POST['txtSearch'];
        }

        if (!empty($_POST['tglCari'])) {
            $txt_tgl = tanggalsystem($_POST['tglCari']);
            $txt_tgl_t = substr($txt_tgl, 0, 4);
            $txt_tgl_b = substr($txt_tgl, 4, 2);
            $txt_tgl_tg = substr($txt_tgl, 6, 2);
            $txt_tgl = $txt_tgl_t . "-" . $txt_tgl_b . "-" . $txt_tgl_tg;
        }
        $where = "";
        if ($txt_search != '') {
            $where = " and nopo LIKE  '%" . $txt_search . "%'";
        } elseif ($txt_tgl != '') {
            $where.=" and tanggal LIKE '" . $txt_tgl . "'";
        }

        $addTmbh = " ";
        if (($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') && ($_SESSION['empl']['tipelokasitugas'] != 'KANWIL')) {
            $sPt = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "'";
            $qPt = $owlPDO->query($sPt) or die(print " Gagal: " . PDOException::getMessage());
            $qPt->setFetchMode(PDO::FETCH_ASSOC);
            $rPt = $qPt->fetch();
            $addTmbh = " and kodeorg='" . $rPt['induk'] . "'";
        }

        $sql2 = "SELECT count(*) as jmlhrow FROM " . $dbname . ".log_poht_del where statuspo>1   " . $addTmbh . " " . $where . " order by tanggal desc ";
        $query2 = $owlPDO->query($sql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        $strx = "SELECT * FROM " . $dbname . ".log_poht_del where statuspo>1  " . $addTmbh . " " . $where . " order by tanggal desc limit " . $offset . "," . $limit . "";
        $res = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $no = 0;
        while ($bar = $res->fetch()) {
            $kodeorg = $bar['kodeorg'];
            $spr = "select * from  " . $dbname . ".organisasi where  kodeorganisasi='" . $kodeorg . "' or induk='" . $kodeorg . "'";
            $rep = $owlPDO->query($spr) or die(print " Gagal: " . PDOException::getMessage());
            $rep->setFetchMode(PDO::FETCH_OBJ);
            $bas = $rep->fetch();
            $no+=1;

            if ($bar['stat_release'] == 1)
                $st = $_SESSION['lang']['release_po'];
            else
                $st = $_SESSION['lang']['un_release_po'];

            echo"<tr class=rowcontent id='tr_" . $no . "'>
				<td align=center>" . $no . "</td>
				<td id=td_" . $no . ">" . $bar['nopo'] . "</td>
				<td>" . tanggalnormal($bar['tanggal']) . "</td>
				<td>" . $bas->namaorganisasi . "</td>
				<td>" . $st . "</td>";

            $sql = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $bar['persetujuan1'] . "'";
            $query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
            $query->setFetchMode(PDO::FETCH_ASSOC);
            $yrs = $query->fetch();
            echo"<td>" . $yrs['namakaryawan'] . "</td>";
            ?>

            <td>
                <button class=mybutton style='cursor:pointer' onclick="masterPDF('log_poht_del', '<?php echo $bar['nopo'] ?>', '', 'log_slave_print_po_batal', event);" ><?php echo $_SESSION['lang']['print'] ?>
                </button>
            </td>
            <?php
            echo"</tr>";
        }
        echo"<tr><td colspan=9 align=center>
			" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
			<button class=mybutton onclick=cariPage(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
			<button class=mybutton onclick=cariPage(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
			</td>
			</tr><input type=hidden id=nopp_" . $no . " name=nopp_" . $no . " value='" . $bar['nopp'] . "' />";
        break;

    case 'loadData':
        $addTmbh = "";
        if (($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') && ($_SESSION['empl']['tipelokasitugas'] != 'KANWIL')) {
            $sPt = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "'";
			$qPt=$owlPDO->query($sPt) or die(print " Gagal: ".PDOException::getMessage());
			$qPt->setFetchMode(PDO::FETCH_ASSOC);
            $rPt = $qPt->fetch();
            $addTmbh = " and kodeorg='" . $rPt['induk'] . "'";
        }
		
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $sql2 = "select count(*) as jmlhrow from " . $dbname . ".log_poht_del where statuspo>1 " . $addTmbh . "  ORDER BY nopo DESC";
        $query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
        $str = "SELECT * FROM " . $dbname . ".log_poht_del where statuspo>1 " . $addTmbh . "  ORDER BY tanggal DESC limit " . $offset . "," . $limit . "";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = 0;
		while ($bar = $res->fetch()) {
			$kodeorg = $bar['kodeorg'];
			$spr = "select * from  " . $dbname . ".organisasi where  kodeorganisasi='" . $kodeorg . "' or induk='" . $kodeorg . "'";
			$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
			$rep->setFetchMode(PDO::FETCH_OBJ);
			$bas = $rep->fetch();
			$no+=1;
			if ($bar['stat_release'] == 1)
				$st = $_SESSION['lang']['release_po'];
			else
				$st = $_SESSION['lang']['un_release_po'];
			echo"<tr class=rowcontent id='tr_" . $no . "'>
			  <td align=center>" . $no . "</td>
			  <td id=td_" . $no . ">" . $bar['nopo'] . "</td>
			  <td>" . tanggalnormal($bar['tanggal']) . "</td>
			  <td>" . $bas->namaorganisasi . "</td>
			  <td>" . $st . "</td>";
			$sql = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $bar['persetujuan1'] . "'";
			$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_ASSOC);
			$yrs = $query->fetch();
			echo"<td>" . $yrs['namakaryawan'] . "</td>";
			?>
			<td>			
				<button class=mybutton style='cursor:pointer' onclick="masterPDF('log_poht_del', '<?php echo $bar['nopo'] ?>', '', 'log_slave_print_po_batal', event);" ><? echo $_SESSION['lang']['print'] ?>
				</button>
			</td>

			<?php
			echo"</tr>";
		} echo"
			 <tr><td colspan=8 align=center>
			" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
			<button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
			<button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
			</td>
			</tr><input type=hidden id=nopp_" . $no . " name=nopp_" . $no . " value='" . $bar['nopp'] . "' />";
        break;

    default:
        break;
}
?>