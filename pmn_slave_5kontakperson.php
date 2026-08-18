<?php

include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kode_cus = checkPostGet('kode_cus', '');
$nama = checkPostGet('nama', '');
$telepon = checkPostGet('telepon', '');
$email = checkPostGet('email', '');
$idkontak = checkPostGet('idkontak', '');
$method = checkPostGet('method', '');
$proses = checkPostGet('proses', '');

switch ($proses) {
	case'generatekode':
		$custcode = generateCustCode($nama);
		
		echo $custcode;
	break;
	
    case 'loadKontakPerson' :
        $query = selectQuery($dbname, 'pmn_4customercontact', "*", "`kodecustomer`='" . $kode_cus . "'");
        $data = fetchData($query);
        createTabDetail($kode_cus, $data);
        break;

    case 'addKontakPerson' :
        if ($kode_cus == '') {
            echo 'Gagal : Kode Pelanggan harus diisi.';
        } else {
            $sKp = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $kode_cus . "'";
            $rKp = $owlPDO->query($sKp) or die(print " Gagal: " . PDOException::getMessage());
            $row = owlBaris($rKp);


            if ($row >= 1 && $method == 'insert') {
                echo 'Gagal : Kode Pelanggan sudah terdaftar didatabase.';
            } else {
                if ($nama == '' || $telepon == '' || $email == '') {
                    echo 'Gagal : Semua field kontak person harus diisi.';
                } else {
                    $sInsertKp = "insert into " . $dbname . ".pmn_4customercontact (kodecustomer,nama,telepon,email) values ('" . $kode_cus . "','" . $nama . "','" . $telepon . "','" . $email . "')";
                    try {
                        $owlPDO->exec($sInsertKp);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }
            }
        }
        break;

    case 'deleteKontakPerson' :
        $sKp = "delete from " . $dbname . ".pmn_4customercontact where idkontak='" . $idkontak . "'";
        try {
            $owlPDO->exec($sKp);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    default :
        break;
}

function createTabDetail($id, $data) {
    $table = "<table id='kontakDetailTable'>";
    # Header
    $table .= "<thead>";
    $table .= "<tr>";
    $table .= "<td style='width: 150px;'>" . $_SESSION['lang']['nama'] . "</td>";
    $table .= "<td style='width: 150px;'>" . $_SESSION['lang']['telepon'] . "</td>";
    $table .= "<td style='width: 150px;'>" . $_SESSION['lang']['email'] . "</td>";
    $table .= "<td style='width: 50px; text-align:center;'>" . $_SESSION['lang']['action'] . "</td>";
    $table .= "</tr>";
    $table .= "</thead></table>";

    # Data
    $table .= "<div style='overflow:auto;max-height:200px'><table><tbody id='detailBody'>";

    $i = 0;

    #======= Display Data =======
    if ($data != array()) {
        foreach ($data as $key => $row) {
            $table .= "<tr id='detail_tr_" . $key . "' class='rowcontent'>";
            $table .= "<td style='width: 150px;'>" . $row['nama'] . "</td>";
            $table .= "<td style='width: 150px;'>" . $row['telepon'] . "</td>";
            $table .= "<td style='width: 150px;'>" . $row['email'] . "</td>";

            $table .= "<td style='text-align:center;'><img id='detail_delete_" . $key . "' title='Hapus' class=resicon onclick=\"deleteKontakPerson('" . $row['idkontak'] . "')\" src='images/delete_32.png'/></td>";
            $i = $key;
        }
        $i++;
    }

    #======= New Row ===========
    $table .= "<tr id='detail_tr_" . $i . "' class='rowcontent'>";
    $table .= "<td style='width: 150px;'>" . makeElement("nama_" . $i . "", 'txt', '', array('onkeypress' => 'return tanpa_kutip(event)')) . "</td>";
    $table .= "<td style='width: 150px;'>" . makeElement("telepon_" . $i . "", 'txt', '', array('onkeypress' => 'return angka_doang(event)')) . "</td>";
    $table .= "<td style='width: 150px;'>" . makeElement("email_" . $i . "", 'txt', '', array('onkeypress' => 'return tanpa_kutip(event)')) . "</td>";

    # Add, Container Delete
    $table .= "<td style='width: 50px; text-align:center'><img id='detail_add_" . $i . "' title='Tambah' class=resicon onclick=\"addKontakPerson('" . $i . "')\" src='images/plus.png'/>";
    $table .= "&nbsp;<img id='detail_delete_" . $i . "' /></td>";
    $table .= "</tr>";

    $table .= "</tbody>";
    $table .= "</table></div>";
    echo $table;
}

?>