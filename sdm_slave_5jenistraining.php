<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kode = checkPostGet('kode', '');
$jenistraining = checkPostGet('jenistraining', '');
$status = checkPostGet('status', '');
$method = checkPostGet('method', '');
$kelompok = checkPostGet('kelompok', '');

switch ($method) {
    case 'loaddata':
        getContainer();
        break;

    case 'insert':
        if ($kode == '' || $jenistraining == '' || $kelompok=='') {
            echo "Gagal : Semua field harus diisi.";
            exit();
        }
        $str = "select * from " . $dbname . ".sdm_5jenistraining where kodetraining='" . $kode . "'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numRows=owlBaris($qry);
        if ($numRows >= 1) {
            echo "Warning: Kode training sudah pernah terdaftar sebelumnya.";
        } else {
            $strIns = "insert into " . $dbname . ".sdm_5jenistraining (kodetraining,jenistraining,status,updateby, kelompok) 
				values ('" . $kode . "','" . $jenistraining . "','1','" . $_SESSION['standard']['userid'] . "','".$kelompok."')";
            try{
				$owlPDO->exec($strIns); 
				getContainer();
			}
			catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
        }
        break;

    case 'edit':
        if ($jenistraining == '' || $kelompok=='') {
            echo "Gagal : Semua field harus diisi.";
            exit();
        }
        $str = "update " . $dbname . ".sdm_5jenistraining set jenistraining='" . $jenistraining . "', kelompok='".$kelompok."', updateby = '" . $_SESSION['standard']['userid'] . "' where kodetraining='" . $kode . "'";
        try{
			$owlPDO->exec($str); 
			getContainer();
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
        break;

    case 'delete':
        $str = "select * from " . $dbname . ".sdm_karyawantraining where jenistraining='" . $kode . "'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($qry);
        if ($numRows >= 1) {
            echo "Warning: Jenis training ini sudah terdaftar/digunakan untuk data karyawan.";
        } else {
            $strDel = "delete from " . $dbname . ".sdm_5jenistraining where kodetraining='" . $kode . "'";
            try{
				$owlPDO->exec($strDel); 
			}
			catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
        }
        break;

    case 'updStatus':
        if ($status == 1) {
            $stat = 0;
        } else {
            $stat = 1;
        }
        $str = "update " . $dbname . ".sdm_5jenistraining set status = '" . $stat . "', updateby = '" . $_SESSION['standard']['userid'] . "' where kodetraining='" . $kode . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
        break;

    default:
        break;
}

function getContainer() {
    global $conn;
    global $dbname;
    global $owlPDO;

    $str = "select * from " . $dbname . ".sdm_5jenistraining order by status desc, jenistraining asc";
	$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_OBJ);
    $no=0;
    while ($res = $qry->fetch()) {
        $no+=1;
        $opt = '';
        $bg = "class=rowcontent";
        if ($res->status == 0) {
            $opt.="<input type=checkbox id=" . $res->kodetraining . " title='Click to activate' onclick=\"updateStatus('" . $res->kodetraining . "','" . $res->status . "');\">";
            $bg = "bgcolor=orange";
        } else {
            $opt.="<input type=checkbox id=" . $res->kodetraining . " checked  title='Click to deActivate' onclick=\"updateStatus('" . $res->kodetraining . "','" . $res->status . "');\">";
        }
        echo"<tr " . $bg . ">
					<td style='text-align:right;'>" . $no . "</td>
					<td>" . $res->kodetraining . "</td>
					<td>" . $res->kelompok . "</td>
					<td>" . $res->jenistraining . "</td>";
        if ($res->status == 0) {
            $stat = "Not Active";
        } else {
            $stat = "Active";
        }
        echo"<td align=center>" . $opt . " " . $stat . "</td>
					<td align=center><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('" . $res->kodetraining . "','" . $res->jenistraining . "','" . $res->kelompok . "')\"></td>
					<td align=center><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('" . $res->kodetraining . "')\"></td>
				</tr>";
    }
}
?>