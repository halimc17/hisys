<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method       = checkPostGet('method', '');
$kdOrg        = checkPostGet('kdOrg', '');
$idKlmpk      = checkPostGet('idKlmpk', '');
$idJenis      = checkPostGet('idJenis', '');
$idLokasi     = checkPostGet('idLokasi', '');
$jmlhSarana   = checkPostGet('jmlhSarana', '');
$thnPerolehan = checkPostGet('thnPerolehan', '');
$blnPerolehan = checkPostGet('blnPerolehan', '');
$statFr       = checkPostGet('statFr', '');
$idData       = checkPostGet('idData', '');


$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$sKlmpk = "select distinct * from " . $dbname . ".sdm_5kl_prasarana order by kode asc";
$qKlmpk = $owlPDO->query($sKlmpk) or die(print " Gagal: " . PDOException::getMessage());
$qKlmpk->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmpk = $qKlmpk->fetch()) {
    $orgNmKlmpk[$rKlmpk['kode']] = $rKlmpk['nama'];
}
$optKlmpk2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sKlmpk2 = "select distinct jenis,nama from " . $dbname . ".sdm_5jenis_prasarana order by nama asc";
$qKlmpk2 = $owlPDO->query($sKlmpk2) or die(print " Gagal: " . PDOException::getMessage());
$qKlmpk2->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmpk2 = $qKlmpk2->fetch()) {
    $orgNmKlmpk2[$rKlmpk2['jenis']] = $rKlmpk2['nama'];
}
switch ($method) {
    case'insert':
        if (($thnPerolehan == '') || ($blnPerolehan == '') || ($idLokasi == '') || ($idJenis == '') || ($idKlmpk == '') || ($kdOrg == '')) {
            echo"warning:Semua Field tidak boleh kosong";
            exit();
        }
        if ($blnPerolehan > 12) {
            exit("Error:Bulan di luar standard");
        }
        if ($jmlhSarana == '' || $jmlhSarana == '0') {
            exit("Error:Jumlah tidak boleh kosong");
        }
        $sCek = "select * from " . $dbname . ".sdm_prasarana where tahunperolehan='" . $thnPerolehan . "' and bulanperolehan='" . $blnPerolehan . "' and 
                       lokasi='" . $idLokasi . "' and kelompokprasarana='" . $idKlmpk . "' and jenisprasarana='" . $idJenis . "'";
        $qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
        $rCek = owlBaris($qCek);
        if ($rCek > 0) {
            echo"warning:Data Sudah ada";
            exit();
        } else {
            $sIns = "insert into " . $dbname . ".sdm_prasarana (kodeorg,  tahunperolehan, bulanperolehan, jumlah, kelompokprasarana, status, lokasi, jenisprasarana) 
                           values ('" . $kdOrg . "','" . $thnPerolehan . "','" . $blnPerolehan . "','" . $jmlhSarana . "','" . $idKlmpk . "','" . $statFr . "','" . $idLokasi . "','" . $idJenis . "')";

            try {
                $owlPDO->exec($sIns);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        break;



    case'loadData':
        $no = 0;
        $arr = array("0" => "Tidak Aktif", "1" => "Aktif");
        $str = "select * from " . $dbname . ".sdm_prasarana where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' order by tahunperolehan,bulanperolehan desc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row = owlBaris($res);
        if ($row > 0) {
            $limit = 20;
            $page = 0;
            if (isset($_POST['page'])) {
                $page = $_POST['page'];
                if ($page < 0)
                    $page = 0;
            }
            $offset = $page * $limit;

            $sql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_prasarana where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' order by tahunperolehan,bulanperolehan desc";
            $query2 = $owlPDO->query($sql2) or die(print " Gagal: " . PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while ($jsl = $query2->fetch()) {
                $jlhbrs = $jsl->jmlhrow;
            }
            $str = "select * from " . $dbname . ".sdm_prasarana where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' order by tahunperolehan,bulanperolehan desc limit " . $offset . "," . $limit . " ";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar = $res->fetch()) {
                $no+=1;
                echo"<tr class=rowcontent>
                    <td align=center>" . $no . "</td>
                    <td>" . $optNmOrg[$bar['kodeorg']] . "</td>
                    <td>" . $orgNmKlmpk[$bar['kelompokprasarana']] . "</td>
                    <td>" . $orgNmKlmpk2[$bar['jenisprasarana']] . "</td>
                    <td>" . $optNmOrg[$bar['lokasi']] . "</td>
                    <td align=right>" . number_format($bar['jumlah'], 0) . "</td>
                    <td align=right>" . $bar['tahunperolehan'] . "</td>
                    <td align=right>" . numToMonth($bar['bulanperolehan'],'E','long') . "</td>
                    <td>" . $arr[$bar['status']] . "</td>
                    <td align=center>
                      <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar['kodeprasarana'] . "');\"> 
                      </td>
                    <td align=center>
                      <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('" . $bar['kodeprasarana'] . "');\">
                      </td>
                    </tr>";
            }
            echo" <tr><td colspan=11 align=center>
                        " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                        <button class=mybutton onclick=cariBast2(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                        <button class=mybutton onclick=cariBast2(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                        </td>
                        </tr>";
        } else {
            echo "<tr class=rowcontent><td colspan=11>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        break;



    case'update':
        if (($thnPerolehan == '') || ($blnPerolehan == '') || ($idLokasi == '') || ($idJenis == '') || ($idKlmpk == '') || ($kdOrg == '')) {
            echo"warning:Semua Field tidak boleh kosong";
            exit();
        }
        if ($blnPerolehan > 12) {
            exit("Error:Bulan di luar standard");
        }
        if ($jmlhSarana == '' || $jmlhSarana == '0') {
            exit("Error:Jumlah tidak boleh kosong");
        }
        $sUpd = "update " . $dbname . ".sdm_prasarana set `tahunperolehan`='" . $thnPerolehan . "',`bulanperolehan`='" . $blnPerolehan . "',`jumlah`='" . $jmlhSarana . "',`kelompokprasarana`='" . $idKlmpk . "',
                               status='" . $statFr . "',lokasi='" . $idLokasi . "',jenisprasarana='" . $idJenis . "'
                               where kodeprasarana='" . $idData . "'";
        try {
            $owlPDO->exec($sUpd);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;
    case'delData':
        $sDel = "delete from " . $dbname . ".sdm_prasarana where kodeprasarana='" . $idData . "'";
        try {
            $owlPDO->exec($sDel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case'getData':
        $sDt = "select * from " . $dbname . ".sdm_prasarana where kodeprasarana='" . $idData . "'";
        $qDt = $owlPDO->query($sDt) or die(print " Gagal: " . PDOException::getMessage());
        $qDt->setFetchMode(PDO::FETCH_ASSOC);
        $rDet = $qDt->fetch();
        echo $rDet['tahunperolehan'] . "###" . $rDet['bulanperolehan'] . "###" . $rDet['jumlah'] . "###" . $rDet['kelompokprasarana'] . "###" . $rDet['status'] . "###" . $rDet['lokasi'] . "###" . $rDet['jenisprasarana'] . "###" . $rDet['kodeprasarana'];
        break;
    case'getSatuan':
        $sSatuan = "select distinct satuan from " . $dbname . ".sdm_5jenis_prasarana where jenis='" . $idJenis . "'";
        $qSatuan = $owlPDO->query($sSatuan) or die(print " Gagal: " . PDOException::getMessage());
        $qSatuan->setFetchMode(PDO::FETCH_ASSOC);
        $rSatuan = $qSatuan->fetch();

        echo $rSatuan['satuan'];
        break;
    case'getJenis':
        $optKlmpk2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sKlmpk2 = "select distinct jenis,nama from " . $dbname . ".sdm_5jenis_prasarana where kelompok='" . $idKlmpk . "' order by nama asc";
        $qKlmpk2 = $owlPDO->query($sKlmpk2) or die(print " Gagal: " . PDOException::getMessage());
        $qKlmpk2->setFetchMode(PDO::FETCH_ASSOC);
        while ($rKlmpk2 = $qKlmpk2->fetch()) {
            if ($idJenis != '') {
                $optKlmpk2.="<option value='" . $rKlmpk2['jenis'] . "'  " . ($rKlmpk2['jenis'] == $idJenis ? "selected" : "") . ">" . $rKlmpk2['nama'] . "</option>";
            } else {
                $optKlmpk2.="<option value='" . $rKlmpk2['jenis'] . "'>" . $rKlmpk2['nama'] . "</option>";
            }
        }
        echo $optKlmpk2;
        break;
    default:
        break;
}
?>