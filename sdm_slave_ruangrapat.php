<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

//$arr="##tanggalDt##tglAwal##tglEnd##method##agenda##room##pic##jam1##mnt1##jam2##mnt2";
//$method=$_POST['method'];
$method = checkPostGet('method', '');

//$tanggalDt=tanggalsystem($_POST['tanggalDt']);
$tanggalDt = tanggalsystemn(checkPostGet('tanggalDt', ''));

//$tglAwal=explode("-",$_POST['tglAwal']);
//$tgl1=$tglAwal[2]."-".$tglAwal[1]."-".$tglAwal[0];
$tgl1 = tanggalsystemn(checkPostGet('tglAwal', ''));


//$tglEnd=explode("-",$_POST['tglEnd']);
//$tgl2=$tglEnd[2]."-".$tglEnd[1]."-".$tglEnd[0];
$tgl2 = tanggalsystemn(checkPostGet('tglEnd', ''));

$tglCari = tanggalsystem(checkPostGet('tglCari', ''));


//$jamDr=$_POST['jam1'].":".$_POST['mnt1'];
//$jamSmp=$_POST['jam2'].":".$_POST['mnt2'];
$jam1 = checkPostGet('jam1', '');
$jam2 = checkPostGet('jam2', '');
$mnt1 = checkPostGet('mnt1', '');
$mnt2 = checkPostGet('mnt2', '');
$jamDr = $jam1 . ":" . $mnt1;
$jamSmp = $jam2 . ":" . $mnt2;

$jamDr1 = $tgl1 . " " . $jamDr;
$jamSmp1 = $tgl2 . " " . $jamSmp;



$agenda = checkPostGet('agenda', '');
$room = checkPostGet('room', '');
$pic = checkPostGet('pic', '');
$idData = checkPostGet('idData', '');



$er = " lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "'";
$optNm = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $er);
$arrenum = getEnum($dbname, 'qc_5final', 'color');

foreach ($arrenum as $key => $val) {

    $optJK[$key] = $val;
}
//$idData=$_POST['idData'];
#validasi
if ($tgl1 > $tgl2) {
    exit('Error: Tanggal Salah');
} else if ($tgl1 == $tgl2 and $jam1 > $jam2) {
    exit("Error: Tanggal atau Jam Salah");
}

#validasi jika waktu sudah digunakan orang lain
if ($method == 'insert' or $method == 'updateData') {
    $str = "select * from " . $dbname . ".sdm_ruangrapat where roomname='" . $room . "' and tanggal='" . $tanggalDt . "' and status='Reserved'";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        if ((substr($bar->sampai, 0, 16) > $jamDr1 and substr($bar->sampai, 0, 16) < $jamSmp1) or ( substr($bar->mulai, 0, 16) > $jamDr1 and substr($bar->mulai, 0, 16) < $jamSmp1)) {

            exit("Error1: Waktu tersebut sudah direservasi orang lain");
        }
    }
}

switch ($method) {
    case'insert':
        if ($tanggalDt == '' || $tgl1 == '' || $tgl2 == '' || $agenda == '' || $pic == '') {
            exit("Error:Inputan Tanggal,Tanggal Mulai,Tanggal Sampai,Agenda,Ruangan dan PIC Tidak Boleh Kosong");
        }
        $sIns = "insert into " . $dbname . ".sdm_ruangrapat 
                          (tanggal, mulai, sampai, agenda, roomname, reservedby, pic, status)
                          values ('" . $tanggalDt . "','" . $jamDr1 . "','" . $jamSmp1 . "','" . $agenda . "','" . $room . "','" . $_SESSION['standard']['userid'] . "','" . $pic . "','Reserved')";
        try {
            $owlPDO->exec($sIns);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case'loadData':
        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_ruangrapat where reservedby='" . $_SESSION['standard']['userid'] . "'  order by `id` desc"; // echo $ql2;
        $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        $no = 0;
        $str = "select * from " . $dbname . ". sdm_ruangrapat  where reservedby='" . $_SESSION['standard']['userid'] . "' order by id desc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $rowd = owlBaris($res);
        if ($rowd == 0) {
            echo"<tr class=rowcontent><td colspan=8>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        } else {
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar = $res->fetch()) {
                echo"<tr class=rowcontent>
                    <td>" . $bar['tanggal'] . "</td>
                    <td>" . $bar['roomname'] . "</td>
                    <td>" . tanggalnormald($bar['mulai']) . "</td>
                    <td>" . tanggalnormald($bar['sampai']) . "</td>
                    <td>" . $bar['agenda'] . "</td>
                    <td>" . $optNm[$bar['pic']] . "</td>
                    <td>" . $bar['status'] . "</td>
                    <td align=center>
";
                if ($bar['status'] != 'Canceled') {
                    if ($_SESSION['standard']['userid'] == $bar['reservedby']) {
                        echo"<img src=images/application/application_edit.png class=resicon  title='Edit' 
                            onclick=\"fillField('" . $bar['id'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['roomname'] . "','" . $bar['mulai'] . "','" . $bar['sampai'] . "','" . $bar['agenda'] . "','" . $bar['pic'] . "','" . $bar['status'] . "');\">";
                        echo"<img src=images/clear2.png class=resicon  title='Cancel' 
                            onclick=\"kancel('" . $bar['id'] . "');\">";
                    }
                }
                echo"<!--<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('" . $bar['id'] . "');\">-->
                      </td>

                    </tr>";
            }
            echo"
                    </tr><tr class=rowheader><td colspan=8 align=center>
                    " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                    <button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                    <button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                    </td>
                    </tr>";
        }
        break;


    case'loadData2':
        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $whr = '';
        if ($tglCari != '') {
            $whr = "where tanggal='" . $tglCari . "'";
        }
        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_ruangrapat " . $whr . " order by `id` desc"; // echo $ql2;
        $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        $no = 0;
        $str = "select * from " . $dbname . ". sdm_ruangrapat " . $whr . " order by id desc";

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $rowd = owlBaris($res);
        if ($rowd == 0) {
            echo"<tr class=rowcontent><td colspan=8>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        } else {
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar = $res->fetch()) {
                echo"<tr class=rowcontent>
                    <td>" . $bar['tanggal'] . "</td>
                    <td>" . $bar['roomname'] . "</td>
                    <td>" . tanggalnormald($bar['mulai']) . "</td>
                    <td>" . tanggalnormald($bar['sampai']) . "</td>
                    <td>" . $bar['agenda'] . "</td>
                    <td>" . $optNm[$bar['pic']] . "</td>
                    <td>" . $optNm[$bar['reservedby']] . "</td>
                    <td>" . $bar['status'] . "</td>
                    <td>" . $bar['reservetime'] . "</td>

                    </tr>";
            }
            echo"
                    </tr><tr class=rowheader><td colspan=8 align=center>
                    " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                    <button class=mybutton onclick=cariBast2(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                    <button class=mybutton onclick=cariBast2(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                    </td>
                    </tr>";
        }
        break;
    case'updateData':

        $sUpd = "update " . $dbname . ".sdm_ruangrapat set `tanggal`='" . $tanggalDt . "',`mulai`='" . $jamDr1 . "',`sampai`='" . $jamSmp1 . "'
                               ,`agenda`='" . $agenda . "',`roomname`='" . $room . "',`reservedby`='" . $_SESSION['standard']['userid'] . "',`pic`='" . $pic . "'
                              where id='" . $idData . "'";
        try {
            $owlPDO->exec($sUpd);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;
    case'kancelDat':
        $sUpd = "update " . $dbname . ".sdm_ruangrapat set `status`='Canceled'
                              where id='" . $idData . "'";
        try {
            $owlPDO->exec($sUpd);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case'delData':
        $sDel = "delete from " . $dbname . ".setup_franco where id_franco='" . $idFranco . "'";
        try {
            $owlPDO->exec($sUpd);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    default:
        break;
}
?>