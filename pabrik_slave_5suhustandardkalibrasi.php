<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$kodeorg = checkPostGet('kodeorg', '');
$kodetangki = checkPostGet('kodetangki', '');
$periode = checkPostGet('periode', '');
$optPeriode = checkPostGet('optPeriode', '');
$suhu = checkPostGet('suhu', '0');

switch ($proses) {
    case 'insert':
        if ($kodeorg == '' || $kodetangki == '' || $suhu == '') {
            echo 'Gagal : Semua field harus diisi.';
        } else {
            $strCount = "select * from " . $dbname . ".pabrik_5standardsuhu_kalibrasi where millcode='" . $kodeorg . "' and kodetangki='" . $kodetangki . "'";
            $qryCount = $owlPDO->query($strCount) or die(print " Gagal: " . PDOException::getMessage());
            $numRows = owlBaris($qryCount);
            if ($numRows >= 1) {
                echo "Gagal : Item ini sudah ada didatabase.";
            } else {
                $str = "insert into " . $dbname . ".pabrik_5standardsuhu_kalibrasi(millcode,kodetangki,suhu_kalibrasi,createby,createtime) values ('" . $kodeorg . "','" . $kodetangki . "','" . $suhu . "','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
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
        $str = "update " . $dbname . ".pabrik_5standardsuhu_kalibrasi set suhu_kalibrasi='" . $suhu . "'
        ,updateby='" . $_SESSION['standard']['userid'] . "' where millcode='" . $kodeorg . "' and kodetangki='" . $kodetangki . "'";
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
        $str = "delete from " . $dbname . ".pabrik_5standardsuhu_kalibrasi where millcode='" . $kodeorg . "' and kodetangki='" . $kodetangki . "' ";
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

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                $this->SetFont('Arial', 'B', 8);
                $this->Cell(20, $height, $_SESSION['org']['namaorganisasi'], '', 1, 'L');
                $this->SetFont('Arial', 'B', 12);

                $this->Cell($width, $height, strtoupper($_SESSION['lang']['suhustandardkalibrasi']), '', 1, 'C');
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
                $this->Cell(80, 1.5 * $height, $_SESSION['lang']['periode'], 'TBR', 0, 'C');
                $this->Cell(80, 1.5 * $height, $_SESSION['lang']['suhu'], 'TBR', 0, 'C');
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
        $str = "select * from " . $dbname . ".pabrik_5standardsuhu_kalibrasi where millcode = '" . $kodeorg . "' and periode like '%" . $optPeriode . "%' order by millcode ASC, kodetangki ASC, periode DESC";
        $result = fetchData($str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $numRows = owlBaris($res);
        if ($numRows == 0) {
            $pdf->Cell(300, $height, $_SESSION['lang']['errdatanotexist'], 'BRL', 0, 'C');
        } else {
            foreach ($result as $data) {
                $pdf->Cell(70, $height, $data['millcode'], 'BRL', 0, 'L');
                $pdf->Cell(70, $height, $data['kodetangki'], 'BRL', 0, 'L');
                $pdf->Cell(80, $height, $data['periode'], 'BRL', 0, 'L');
                $pdf->Cell(80, $height, $data['suhu_kalibrasi'], 'BRL', 0, 'R');
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
    global $optPeriode;
    global $owlPDO;
    $kodeorg = $_SESSION['empl']['lokasitugas'];
    $dateNow = date("Y-m-01");
    $str = "select * from " . $dbname . ".pabrik_5standardsuhu_kalibrasi where millcode = '" . $kodeorg . "' and periode like '%" . $optPeriode . "%' order by millcode ASC, kodetangki ASC, periode DESC";
    $qry = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $numRows = owlBaris($qry);
    if ($numRows == 0) {
        echo"<tr class=rowcontent>
                <td colspan='9' style='text-align:center;'>" . $_SESSION['lang']['errdatanotexist'] . "</td>
            </tr>";
    } else {
        $no=0;
        $qry->setFetchMode(PDO::FETCH_OBJ);
        while ($res = $qry->fetch()) {
                $s1 = date('Y-m-d', strtotime($res->periode . '-01'));
                $interval = strtotime($dateNow) - strtotime($s1);
                $month = floor($interval / 86400 / 30);
                //$sCpo = "select * from " . $dbname . ".pabrik_masukkeluartangki where tanggal like '%" . $res->periode . "%' and kodeorg = '" . $res->millcode . "' and kodetangki='" . $res->kodetangki . "'";
                $no+=1;
                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$res->updateby."'");
                echo"<tr class=rowcontent>
                <td style='text-align:center;'>" . $no . "</td>
                <td>" . $res->millcode . "</td>
                <td>" . $res->kodetangki . "</td>
                <td hidden>" . $res->periode . "</td>
                <td style='text-align:right;'>" . $res->suhu_kalibrasi . "</td>
                <td style='text-align:center;'>" . $nmKar[$res->updateby] . "</td>";
                //$qryv = $owlPDO->query($sCpo) or die(print " Gagal: " . PDOException::getMessage());
                //$numRowsv = owlBaris($qryv);
				
               // if ($numRowsv == 0 && $month == 0) {
                    echo "<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('" . $res->millcode . "','" . $res->kodetangki . "','" . $res->periode . "','" . $res->suhu_kalibrasi . "')\"></td>
                    <td><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('" . $res->millcode . "','" . $res->kodetangki . "','" . $res->periode . "')\"></td>";
                // } else {
                    // echo"<td></td><td></td>";
                // }
                echo "</tr>";
            }
        }
    }
?>