<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method = checkPostGet('method', '');
$kdSarana = checkPostGet('kdSarana', '');
$tglKonSarana = tanggalsystem(checkPostGet('tglKonSarana', ''));
$kondId = checkPostGet('kondId', '');
$idProgress = checkPostGet('idProgress', '');
$jmlhSarana = checkPostGet('jmlhSarana', '');

$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$optKlmpk2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sKlmpk2 = "select distinct jenis,nama,satuan from " . $dbname . ".sdm_5jenis_prasarana order by nama asc";
$qKlmpk2 = $owlPDO->query($sKlmpk2) or die(print " Gagal: " . PDOException::getMessage());
$qKlmpk2->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmpk2 = $qKlmpk2->fetch()) {
    $orgNmKlmpk2[$rKlmpk2['jenis']] = $rKlmpk2['nama'];
    $arrSat[$rKlmpk2['jenis']] = $rKlmpk2['satuan'];
}
switch ($method) {
    case'insert':
        if (($kdSarana == '') || ($tglKonSarana == '') || ($kondId == '') || ($idProgress == '')) {
            echo"warning:Semua Field tidak boleh kosong";
            exit();
        }

        if ($jmlhSarana == '' || $jmlhSarana == '0') {
            exit("Error:Jumlah tidak boleh kosong");
        }
        $sCek2 = "select distinct jumlah,jenisprasarana from " . $dbname . ".sdm_prasarana where kodeprasarana='" . $kdSarana . "'";
        $qCek2 = $owlPDO->query($sCek2) or die(print " Gagal: " . PDOException::getMessage());
        $qCek2->setFetchMode(PDO::FETCH_ASSOC);
        while ($rCek2 = $qCek2->fetch())
            if ($rCek2['jumlah'] < $jmlhSarana) {
                exit("Error:Jumlah tidak boleh lebih dari " . $arrSat[$rCek2['jenisprasarana']] . " yang tersedia");
            }
        $sCek = "select * from " . $dbname . ".sdm_kondisi_prasarana where kodeprasarana='" . $kdSarana . "' and tanggal='" . $tglKonSarana . "'";
        $qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
        $rCek = owlBaris($qCek);
        if ($rCek > 0) {
            echo"warning:Data Sudah ada";
            exit();
        } else {
            $sIns = "insert into " . $dbname . ".sdm_kondisi_prasarana (kodeprasarana, jumlah, kondisi, tanggal, progress, karyawanid) 
                           values ('" . $kdSarana . "','" . $jmlhSarana . "','" . $kondId . "','" . $tglKonSarana . "','" . $idProgress . "','" . $_SESSION['standard']['userid'] . "')";
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
        $arrProgrs = array("1" => $_SESSION['lang']['slsiPerbaikan'], "2" => $_SESSION['lang']['dlmPerbaikan']);
        $str = "select a.* from " . $dbname . ".sdm_kondisi_prasarana a  left join " . $dbname . ".sdm_prasarana b on a.kodeprasarana=b.kodeprasarana where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "'
                           order by tahunperolehan,bulanperolehan desc";
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

            $sql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_kondisi_prasarana a  left join " . $dbname . ".sdm_prasarana b on a.kodeprasarana=b.kodeprasarana where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "'
                           order by tahunperolehan,bulanperolehan desc";
            $query2 = $owlPDO->query($sql2) or die(print " Gagal: " . PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while ($jsl = $query2->fetch()) {
                $jlhbrs = $jsl->jmlhrow;
            }
            $str = "select a.*,b.jenisprasarana,b.lokasi  from " . $dbname . ".sdm_kondisi_prasarana a  left join " . $dbname . ".sdm_prasarana b on a.kodeprasarana=b.kodeprasarana where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "'
                           order by tahunperolehan,bulanperolehan desc limit " . $offset . "," . $limit . " ";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar = $res->fetch()) {
                $no+=1;
                echo"<tr class=rowcontent>
                    <td>" . $no . "</td>
                    <td>" . $bar['kodeprasarana'] . "</td>
                    <td>" . $orgNmKlmpk2[$bar['jenisprasarana']] . "</td>
                    <td>" . $bar['lokasi'] . "</td>
                    <td>" . tanggalnormal($bar['tanggal']) . "</td>
                    <td>" . $bar['kondisi'] . "</td>
                    <td>" . $arrProgrs[$bar['progress']] . "</td>
                    <td align=right>" . number_format($bar['jumlah'], 0) . "</td>
                    <td>" . $arrSat[$bar['jenisprasarana']] . "</td>
                    <td>
                      <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar['kodeprasarana'] . "','" . tanggalnormal($bar['tanggal']) . "');\"> 
                      <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('" . $bar['kodeprasarana'] . "','" . tanggalnormal($bar['tanggal']) . "');\">
                      </td>
                    </tr>";
            }
            echo" <tr><td colspan=10 align=center>
                        " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                        <button class=mybutton onclick=cariBast2(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                        <button class=mybutton onclick=cariBast2(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                        </td>
                        </tr>";
        } else {
            echo "<tr class=rowcontent><td colspan=10>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        break;
    case'update':
        if (($kdSarana == '') || ($tglKonSarana == '') || ($kondId == '') || ($idProgress == '')) {
            echo"warning:Semua Field tidak boleh kosong";
            exit();
        }

        if ($jmlhSarana == '' || $jmlhSarana == '0') {
            exit("Error:Jumlah tidak boleh kosong");
        }
        $sCek2 = "select distinct jumlah,jenisprasarana from " . $dbname . ".sdm_prasarana where kodeprasarana='" . $kdSarana . "'";
        $qCek2 = $owlPDO->query($sCek2) or die(print " Gagal: " . PDOException::getMessage());
        $qCek2->setFetchMode(PDO::FETCH_ASSOC);
        while ($rCek2 = $qCek2->fetch())
            if ($rCek2['jumlah'] < $jmlhSarana) {
                exit("Error:Jumlah tidak boleh lebih dari " . $arrSat[$rCek2['jenisprasarana']] . " yang tersedia");
            }
        $sUpd = "update " . $dbname . ".sdm_kondisi_prasarana set `jumlah`='" . $jmlhSarana . "',`kondisi`='" . $kondId . "',`progress`='" . $idProgress . "',`karyawanid`='" . $_SESSION['standard']['userid'] . "'
                       where kodeprasarana='" . $kdSarana . "' and tanggal='" . $tglKonSarana . "'";
        try {
            $owlPDO->exec($sUpd);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;
    case'delData':
        $sDel = "delete from " . $dbname . ".sdm_kondisi_prasarana where  kodeprasarana='" . $kdSarana . "' and tanggal='" . $tglKonSarana . "'";
        try {
            $owlPDO->exec($sDel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case'getData':
        $sDt = "select * from " . $dbname . ".sdm_kondisi_prasarana where kodeprasarana='" . $kdSarana . "' and tanggal='" . $tglKonSarana . "'";
        $qDt = $owlPDO->query($sDt) or die(print " Gagal: " . PDOException::getMessage());
        $qDt->setFetchMode(PDO::FETCH_ASSOC);
        $rDet = $qDt->fetch();
        echo $rDet['jumlah'] . "###" . $rDet['kondisi'] . "###" . $rDet['progress'];
        break;
    case'getSatuan':
        $sSatuan2 = "select distinct jenisprasarana from " . $dbname . ".sdm_prasarana where kodeprasarana='" . $kdSarana . "'";
        $qSatuan2 = $owlPDO->query($sSatuan2) or die(print " Gagal: " . PDOException::getMessage());
        $qSatuan2->setFetchMode(PDO::FETCH_ASSOC);
        $rSatuan2 = $qSatuan2->fetch();
        $sSatuan = "select distinct satuan from " . $dbname . ".sdm_5jenis_prasarana where jenis='" . $rSatuan2['jenisprasarana'] . "'";
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