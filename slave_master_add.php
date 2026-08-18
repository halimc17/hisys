<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

# Get Parameter
$empty = $_GET['empty'];

$tableName = $_POST['tableName'];
$protected_table = Array(
    'setup_periodeakuntansi',
    'user',
    'auth',
    'setup_kegiatan',
    'setup_approval',
    'tipeakses',
    'organisasi',
    'menu',
    'bahasa',
    'datakaryawan',
    'keu_5akun',
    'sdm_daftarasset',
    'sdm_gaji',
    'setup_blok'
);
foreach ($protected_table as $tab => $tabl) {
    if ($tableName == $tabl) {
        require_once('lib/admin_validation.php');
    }
}

$numRow = $_POST['numRow'];
$idField = $_POST['idField'];
$idVal = $_POST['idVal'];
$data = $_POST;
$opt = json_decode(str_replace('##', '"', $_POST['opt']), true);
unset($data['tableName']);
unset($data['numRow']);
unset($data['opt']);
unset($data['idField']);
unset($data['idVal']);
unset($data['freeze']);

if ($empty == false) {
    foreach ($data as $dt => $isi) {
        if ($isi == '') {
            echo"warning:Please complete the form";
            exit();
        }
    }
}


# Query
$query = "insert into `" . $dbname . "`.`" . $tableName . "`(";
//exit("Error:$query");
$i = 0;
foreach ($data as $key => $row) {
    if ($i == 0) {
        $query .= "`" . $key . "`";
    } else {
        $query .= ",`" . $key . "`";
    }
    $i++;
}
$query .=") values (";
$i = 0;
foreach ($data as $row) {
    # Transform Tanggal
    $tmpStr = explode("-", $row);
    if (count($tmpStr) == 3) {
        $row = tanggalsystemn($row);
    }

    $int = (int) $row;
    if ($i == 0) {
        if ((string) $int == $row and strlen((string) $int) == strlen($row)) {
            $query .= $row;
        } elseif (is_string($row)) {
            $query .= "'" . $row . "'";
        } else {
            $query .= $row;
        }
    } else {
        if ((string) $int == $row and strlen((string) $int) == strlen($row)) {
            $query .= "," . $row;
        } elseif (is_string($row)) {
            $query .= ",'" . $row . "'";
        } else {
            $query .= "," . $row;
        }
    }
    $i++;
}
$query .= ");";

#echo "ERROR".$query;
#echo "error";
# Execute Query

try {
    $owlPDO->exec($query);

    # Append row to Tables in HTML
    #echo "mTable = document.getElementById('mTabBody');";
    #echo "mTable.innerHTML += ";
    echo "<tr id='tr_" . $numRow . "' class='rowcontent'>";
    $tmpField = "";
    $tmpVal = "";
    foreach ($data as $key => $row) {
        if (isset($opt[$key])) {
            $tmpCont = (isset($opt[$key][$row]) ? $opt[$key][$row] : '');
        } else {
            $tmpCont = $row;
        }
        echo "<td align=left id='" . $key . "_" . $numRow . "' value='" . $row . "'>" . $tmpCont . "</td>";
        $tmpField .= "##" . $key;
        $tmpVal .= "##" . $row;
    }

    if (isset($_POST['freeze']) and $_POST['freeze'] != 'null') {
        echo "<td><img id='editRow" . $numRow . "' title='Edit' onclick=\"editRow(" . $numRow . ",'" . $tmpField . "','" . $tmpVal . "','" . $_POST['freeze'] . "')\"
	    class='zImgBtn' src='images/001_45.png' /></td>";
    } else {
        echo "<td><img id='editRow" . $numRow . "' title='Edit' onclick=\"editRow(" . $numRow . ",'" . $tmpField . "','" . $tmpVal . "')\"
	    class='zImgBtn' src='images/001_45.png' /></td>";
    }
    echo "<td><img id='delRow" . $numRow . "' title='Hapus' onclick=\"delRow(" . $numRow . ",'" . $idField . "','" . $idVal . "',null,'" . $tableName . "')\"
	class='zImgBtn' src='images/delete_32.png' /></td>";
    echo "</tr>";
} catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "<br/>";
    die();
}
?>