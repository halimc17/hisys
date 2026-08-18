<?php

session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$kodeorg = checkPostGet('kodeorg', '');
$kodetangki = checkPostGet('kodetangki', '');
$suhu = checkPostGet('suhu', '0');
$beratjenis = checkPostGet('beratjenis', '0');
$varian = checkPostGet('varian', '0');

switch ($proses) {
    case 'insert':
        if ($kodeorg == '' || $kodetangki == '' || $suhu == '' || $beratjenis == '' || $varian == '') {
            echo 'Gagal : Semua field harus diisi.';
        } else {
            $strCount = "select * from " . $dbname . ".pabrik_5suhu where millcode='" . $kodeorg . "' and kodetangki='" . $kodetangki . "' and suhu='" . $suhu . "'";
            $qryCount = $owlPDO->query($strCount) or die(print " Gagal: " . PDOException::getMessage());
            $numRows = owlBaris($qryCount);
            if ($numRows >= 1) {
                echo "Gagal : Item ini sudah ada didatabase.";
            } else {
                $str = "insert into " . $dbname . ".pabrik_5suhu(millcode,kodetangki,suhu,berat_jenis,varian,updateby) values ('" . $kodeorg . "','" . $kodetangki . "','" . $suhu . "','" . $beratjenis . "','" . $varian . "','" . $_SESSION['standard']['userid'] . "')";
                try
                {
                    $owlPDO->exec($str);
                    loadData();
                }
                catch (PDOException $e)
                {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }
        }
        break;

    case 'update':
        $str = "update " . $dbname . ".pabrik_5suhu set berat_jenis='" . $beratjenis . "', varian='" . $varian . "', updateby='" . $_SESSION['standard']['userid'] . "' where millcode='" . $kodeorg . "' and kodetangki='" . $kodetangki . "' and suhu ='" . $suhu . "'";
        try {
            $owlPDO->exec($str);
            loadData();
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'loadData':
        loadData();
        break;

    case 'delete':
        $str = "delete from " . $dbname . ".pabrik_5suhu where millcode='" . $kodeorg . "' and kodetangki='" . $kodetangki . "' and suhu='".$suhu."' ";
        try {
            $owlPDO->exec($str);
            loadData();
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'pdf':

        class masterpdf extends FPDF {

            function Header() {
                global $conn;
                global $dbname;
                global $owlPDO;

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(20, $height, $_SESSION['org']['namaorganisasi'], '', 1, 'L');
                $this->SetFont('Arial', 'B', 12);

                $this->Cell($width, $height, strtoupper($_SESSION['lang']['suhu']), '', 1, 'C');
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(415, $height, ' ', '', 0, 'R');
                $this->Cell(40, $height, $_SESSION['lang']['tanggal'], '', 0, 'L');
                $this->Cell(5, $height, ':', '', 0, 'L');
                $this->Cell(40, $height, date('d-m-Y H:i'), '', 1, 'L');
                $this->Cell(415, $height, ' ', '', 0, 'R');
                $this->Cell(40, $height, $_SESSION['lang']['page'], '', 0, 'L');
                $this->Cell(8, $height, ':', '', 0, 'L');
                $this->Cell(15, $height, $this->PageNo(), '', 1, 'L');

                $this->Cell(100, $height, '', '', 0, 'L');
                $this->Cell(315, $height, ' ', '', 0, 'R');
                $this->Cell(40, $height, $_SESSION['lang']['user'], '', 0, 'L');
                $this->Cell(8, $height, ':', '', 0, 'L');
                $this->Cell(20, $height, $_SESSION['standard']['username'], '', 1, 'L');
                $this->Ln();

                $this->Cell(70, 1.5 * $height, $_SESSION['lang']['kodeorganisasi'], 'TBLR', 0, 'C');
                $this->Cell(70, 1.5 * $height, $_SESSION['lang']['kodetangki'], 'TBR', 0, 'C');
                $this->Cell(80, 1.5 * $height, $_SESSION['lang']['suhu'], 'TBR', 0, 'C');
                $this->Cell(80, 1.5 * $height, $_SESSION['lang']['beratjenis'], 'TBR', 0, 'C');
                $this->Cell(150, 1.5 * $height, $_SESSION['lang']['varian'], 'TBR', 0, 'C');
                $this->Ln();
            }

        }

        #====================== Prepare PDF Setting
        $pdf = new masterpdf('P', 'pt', 'A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 14;
        $pdf->SetFont('Arial', '', 8);
        $pdf->AddPage();

        # Generate Data
        $kodeorg = $_SESSION['empl']['lokasitugas'];
        $str = "select * from " . $dbname . ".pabrik_5suhu where millcode = '" . $kodeorg . "'";
        $result = fetchData($str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $numRows = owlBaris($res);
        if ($numRows == 0) {
            $pdf->Cell(380, $height, $_SESSION['lang']['errdatanotexist'], 'BRL', 0, 'C');
        } else {
            foreach ($result as $data) {
                $pdf->Cell(70, $height, $data['millcode'], 'BRL', 0, 'L');
                $pdf->Cell(70, $height, $data['kodetangki'], 'BRL', 0, 'L');
                $pdf->Cell(80, $height, $data['suhu'], 'BRL', 0, 'R');
                $pdf->Cell(80, $height, $data['berat_jenis'], 'BRL', 0, 'R');
                $pdf->Cell(150, $height, $data['varian'], 'BRL', 0, 'R');
                $pdf->Ln();
            }
        }

        # Print Out
        $pdf->Output();
        break;

    default:
        break;
}

function loadData() {
    global $conn;
    global $dbname;
    global $kodeorg;
    global $owlPDO;

    $kodeorg = $_SESSION['empl']['lokasitugas'];
    $str = "select * from " . $dbname . ".pabrik_5suhu where millcode = '" . $kodeorg . "'";
    $qry = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $numRows = owlBaris($qry);
    if ($numRows == 0) {
        echo"<tr class=rowcontent>
					<td colspan='9' style='text-align:center;'>" . $_SESSION['lang']['errdatanotexist'] . "</td>
				</tr>";
    } else {
        $no = 0;
        $qry->setFetchMode(PDO::FETCH_OBJ);
        while ($res = $qry->fetch()) {
            $no+=1;
            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$res->updateby."'");
            echo"<tr class=rowcontent>
                        <td style='text-align:center;'>" . $no . "</td>
                        <td>" . $res->millcode . "</td>
                        <td>" . $res->kodetangki . "</td>
                        <td style='text-align:right;'>" . $res->suhu . "</td>
                        <td style='text-align:right;'>" . $res->berat_jenis . "</td>
                        <td style='text-align:right;'>" . $res->varian . "</td>
                        <td style='text-align:right;'>" . $nmKar[$res->updateby] . "</td>
                        <td style='text-align:center;'><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('" . $res->millcode . "','" . $res->kodetangki . "','" . $res->suhu . "','" . $res->berat_jenis . "','" . $res->varian . "')\"></td>
                        <td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('" . $res->millcode . "','" . $res->kodetangki . "','" . $res->suhu . "')\"></td>
                    </tr>";
        }
    }
}

?>