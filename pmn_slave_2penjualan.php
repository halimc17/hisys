<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$tanggalmulai = tanggalsystem(checkPostGet('tanggalmulai', ''));
$tanggalakhir = tanggalsystem(checkPostGet('tanggalakhir', ''));
$proses = checkPostGet('proses', '');
$kdBrg = checkPostGet('kdBrg', '');
$idPabrik = checkPostGet('idPabrik', '');

$total1=$total2=$total3=$total4=0;
$total1e=$total2e=$total3e=$total4e=0;
$total1p=$total2p=$total3p=$total4p=0;



if (($tanggalakhir - $tanggalmulai) < 0) {
    echo " Gagal: Periksa kembali periode tanggal, Tanggal akhir lebih kecil dari tanggal mulai.";
} else {
    switch ($proses) {
        case'preview':
            echo" <table class=sortable cellspacing=1 border=0  style=width:175% >
          <thead>
              <tr class=rowheader>
              <td align=center rowspan=2>No.</td>
              <td align=center rowspan=2>" . $_SESSION['lang']['NoKontrak'] . "</td>
             <td align=center rowspan=2>" . $_SESSION['lang']['statusjual'] . "</td>
              <td align=center rowspan=2>" . $_SESSION['lang']['nmcust'] . "</td>
              <td align=center rowspan=2>" . $_SESSION['lang']['tglKontrak'] . "</td>
              <td align=center rowspan=2>" . $_SESSION['lang']['tanggalkirim'] . "</td>
              <td align=center rowspan=2>" . $_SESSION['lang']['komoditi'] . "</td>
              <td align=center rowspan=2>" . $_SESSION['lang']['jmlhBrg'] . "<br>(Kg)</td>
              <td align=center colspan=5>" . $_SESSION['lang']['kualitas'] . "</td>   
                       
              <td align=center rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>
              <td align=center rowspan=2>" . $_SESSION['lang']['kurs'] . "</td>
              <td align=center rowspan=2>" . $_SESSION['lang']['hargasatuan'] . "</td>         
                  
              <td align=center colspan=4>Delivery Term</td>    
              <td align=center colspan=3>" . $_SESSION['lang']['pemenuhan'] . "</td>

              <td align=center rowspan=2>No SPP</td>
              </tr>
              
                            <tr>
                                <td align=center>FFA<br>%</td>  
                                <td align=center>DOBI</td>
                                <td align=center>M & I<br>%</td>
                                <td align=center>Moisture<br>%</td>
                                <td align=center>Impurities<br>%</td>
                                <td align=center>I</td>
                                <td align=center>II</td>
                                <td align=center>III</td>
                                <td align=center>IV</td>
                                <td align=center>Timbangan Pabrik</td>
                                <td align=center>Timbangan Pembeli</td>
                                <td align=center>Selisih</td>
                          </tr>
              </thead><tbody>
            ";
            $arrKurs = array("IDR" => "1", "USD" => "");
            /* $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,kuantitaskontrak,kodept,hargasatuan,matauang,tanggalkirim  from ".$dbname.".pmn_kontrakjual where (tanggalkontrak between '".$tanggalmulai."' and '".$tanggalakhir."') and kodebarang='".$kdBrg."' and kodept='".$idPabrik."' order by tanggalkontrak asc";
             */
            $sql = "select *  from " . $dbname . ".pmn_kontrakjual where (tanggalkontrak between '" . $tanggalmulai . "' and '" . $tanggalakhir . "') and kodebarang like '%" . $kdBrg . "%' and kodept='" . $idPabrik . "' order by tanggalkontrak asc";
             $query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
            $row = owlBaris($query);
            $optKomoditi = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

            if ($row > 0) {
                $query->setFetchMode(PDO::FETCH_ASSOC);
                while ($res = $query->fetch()) {
                    $no+=1;
                    $sBrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $res['kodebarang'] . "'";
                    $qBrg = $owlPDO->query($sBrg) or die(print " Gagal: " . PDOException::getMessage());
                    $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                    $rBrg = $qBrg->fetch();

                    $sCust = "select namacustomer  from " . $dbname . ".pmn_4customer where kodecustomer='" . $res['koderekanan'] . "'";
                    $qCust = $owlPDO->query($sCust) or die(print " Gagal: " . PDOException::getMessage());
                    $qCust->setFetchMode(PDO::FETCH_ASSOC);
                    $rCust = $qCust->fetch();

                    $sTimb = "select sum(beratbersih) as beratbersih,sum(kgpembeli) as kgpembeli  from " . $dbname . ".pabrik_timbangan where nokontrak='" . $res['nokontrak'] . "'";
                    $qTimb = $owlPDO->query($sTimb) or die(print " Gagal: " . PDOException::getMessage());
                    $qTimb->setFetchMode(PDO::FETCH_ASSOC);
                    $rTimb = $qTimb->fetch();

                    $sStat = "select penjualan  from " . $dbname . ".pmn_5franco where id_franco='" . $res['franco'] . "'";
                    $qStat = $owlPDO->query($sStat) or die(print " Gagal: " . PDOException::getMessage());
                    $qStat->setFetchMode(PDO::FETCH_ASSOC);
                    $rStat = $qStat->fetch();

                    $sOrg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $res['kodept'] . "'";
                     $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
                    $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                    $rOrg = $qOrg->fetch();
                    $isi = "";
                    $iSpp = "select * from " . $dbname . ".pmn_suratperintahpengiriman where nokontrak='" . $res['nokontrak'] . "' ";
                    $nSpp = $owlPDO->query($iSpp) or die(print " Gagal: " . PDOException::getMessage());
                    $nSpp->setFetchMode(PDO::FETCH_ASSOC);
                    while ($dSpp = $nSpp->fetch()) {
                        $isi.=$dSpp['nodo'] . ",\n";
                    }


                    $arr = "nokontrak" . "##" . $res['nokontrak'];
                    echo"<tr class=rowcontent onclick=\"zDetail(event,'pmn_slave_laporanPemenuhanKontrak.php','" . $arr . "')\">
                            <td align=center>" . $no . "</td>
                            <td>" . $res['nokontrak'] . "</td>
                            <td>" . $rStat['penjualan'] . "</td>
                            <td>" . $rCust['namacustomer'] . "</td>
                            <td align=center>" . tanggalnormal($res['tanggalkontrak']) . "</td>
                            <td align=center>" . tanggalnormal($res['tanggalkirim']) . "</td>  
                            <td>" . $optKomoditi[$res['kodebarang']] . "</td>  
                            <td align=right>" . number_format($res['kuantitaskontrak'], 0) . "</td>
                            <td align=right>" . number_format($res['ffa'], 2) . "</td>
                            <td align=right>" . number_format($res['dobi'], 2) . "</td>
                            <td align=right>" . number_format($res['mdani'], 2) . "</td>
                            <td align=right>" . number_format($res['moist'], 2) . "</td>
                            <td align=right>" . number_format($res['dirt'], 2) . "</td>
                            <td align=center>" . $res['matauang'] . "</td>  
                            <td align=center>" . $arrKurs[$res['matauang']] . "</td> 
                            <td align=right>" . number_format($res['hargasatuan'], 2) . "</td>";

                            $total1+=$res['kuantitaskontrak'];
                            // $total2+=$res['hargasatuan'];
                            $total2+=$rTimb['beratbersih'];
                            $total3+=$rTimb['kgpembeli'];
                            $total4+=abs($rTimb['kgpembeli'] - $rTimb['beratbersih']);

                    for ($i = 0; $i <= 3; $i++) {
                        if ($i == 0) {
                            $i = "";
                        }
                        $isiMulai = tanggalnormal($res['tanggalkirim' . $i]);
                        $isiSd = tanggalnormal($res['sdtanggal' . $i]);
                        $sd = "s/d";

                        if ($isiMulai == '00-00-0000' || $isiSd == '00-00-0000') {
                            $isiMulai = "";
                            $isiSd = "";
                            $sd = "";
                        }



                        echo"<td align=center>" . $isiMulai . " " . $sd . " " . $isiSd . "</td>";
                    }

                    echo"
                                                        
                        <td align=right>" . number_format($rTimb['beratbersih'], 2) . "</td>
                        <td align=right>" . number_format($rTimb['kgpembeli'], 2) . "</td>
                        <td align=right>" . number_format(abs($rTimb['kgpembeli'] - $rTimb['beratbersih']), 2) . "</td>
                        <td>" . $isi . "</td>
                        </tr>
                        ";
                         // $total3+=$res['beratbersih'];
                }

                echo"<tr class=rowcontent>
                <td colspan=7>".$_SESSION['lang']['total']."</td>
                <td align=right>".number_format($total1)."</td>
                <td colspan=12></td>
                <td align=right>".number_format($total2, 2)."</td>
                <td align=right>".number_format($total3, 2)."</td>
                <td align=right>".number_format($total4, 2)."</td>
                <td align=right></td>
                </tr>";

            } else {
                echo"<tr class=rowcontent align=center><td colspan=23>Not Found</td></tr>";
            }
            echo"</tbody></table>";
            break;
        case'pdf':
            $kdBrg = $_GET['kdBrg'];
            $idPabrik = $_GET['idPabrik'];

            class PDF extends FPDF {

                function Header() {
                    global $conn;
                    global $dbname;
                    global $align;
                    global $length;
                    global $colArr;
                    global $title;
                    global $owlPDO;
                    global $tanggalmulai;
                    global $tanggalakhir;
                    global $kdBrg;
                    global $idPabrik;

                    $sBrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $kdBrg . "'";
                   $qBrg = $owlPDO->query($sBrg) or die(print " Gagal: " . PDOException::getMessage());
                    $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                    $rBrg = $qBrg->fetch();

                    $sOrg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $idPabrik . "'";
                    $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
                    $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                    $rOrg = $qOrg->fetch();

                    if ($kdBrg == '') {
                        $judulNamaBarang = $_SESSION['lang']['all'];
                    } else {
                        $judulNamaBarang = $rBrg['namabarang'];
                    }

                    # Alamat & No Telp
                    $query = selectQuery($dbname, 'organisasi', 'alamat,telepon', "kodeorganisasi='" . $_SESSION['org']['kodeorganisasi'] . "'");
                    $orgData = fetchData($query);

                    $width = $this->w - $this->lMargin - $this->rMargin;
                    $height = 15;
                    $path = 'images/logo.jpg';
                    $this->Image($path, $this->lMargin, $this->tMargin, 0, 55);
                    $this->SetFont('Arial', 'B', 9);
                    $this->SetFillColor(255, 255, 255);
                    $this->SetX(100);
                    $this->Cell($width - 100, $height, $_SESSION['org']['namaorganisasi'], 0, 1, 'L');
                    $this->SetX(100);
                    $this->Cell($width - 100, $height, $orgData[0]['alamat'], 0, 1, 'L');
                    $this->SetX(100);
                    $this->Cell($width - 100, $height, "Tel: " . $orgData[0]['telepon'], 0, 1, 'L');
                    $this->Line($this->lMargin, $this->tMargin + ($height * 4), $this->lMargin + $width, $this->tMargin + ($height * 4));
                    $this->Ln();

                    $this->SetFont('Arial', 'B', 12);
                    $this->Cell((20 / 100 * $width) - 5, $height, strtoupper($_SESSION['lang']['laporanPenjualan']), '', 0, 'L');
                    $this->Ln();
                    $this->SetFont('Arial', '', 8);
                    $this->Cell((20 / 100 * $width) - 5, $height, $_SESSION['lang']['periode'], '', 0, 'L');
                    $this->Cell(5, $height, ':', '', 0, 'L');
                    $this->Cell(45 / 100 * $width, $height, tanggalnormal($tanggalmulai) . ' s/d ' . tanggalnormal($tanggalakhir), '', 0, 'L');
                    $this->Ln();
                    $this->Cell((20 / 100 * $width) - 5, $height, $_SESSION['lang']['nm_perusahaan'], '', 0, 'L');
                    $this->Cell(5, $height, ':', '', 0, 'L');
                    $this->Cell(45 / 100 * $width, $height, $rOrg['namaorganisasi'], '', 0, 'L');
                    $this->Ln();
                    $this->Cell((20 / 100 * $width) - 5, $height, $_SESSION['lang']['komoditi'], '', 0, 'L');
                    $this->Cell(5, $height, ':', '', 0, 'L');
                    $this->Cell(45 / 100 * $width, $height, $judulNamaBarang, '', 0, 'L');



                    $this->Ln();
                    $this->Ln();
                    $this->SetFont('Arial', 'U', 12);

                    $this->SetFont('Arial', 'B', 8);
                    $this->SetFillColor(220, 220, 220);


                    $this->Cell(3 / 100 * $width, $height, 'No', 1, 0, 'C', 1);
                    $this->Cell(12 / 100 * $width, $height, $_SESSION['lang']['NoKontrak'], 1, 0, 'C', 1);
                    $this->Cell(14 / 100 * $width, $height, $_SESSION['lang']['nmcust'], 1, 0, 'C', 1);
                    $this->Cell(13 / 100 * $width, $height, $_SESSION['lang']['tglKontrak'], 1, 0, 'C', 1);
                    $this->Cell(12 / 100 * $width, $height, $_SESSION['lang']['tgl_kirim'], 1, 0, 'C', 1);
                    $this->Cell(12 / 100 * $width, $height, $_SESSION['lang']['komoditi'], 1, 0, 'C', 1);
                    $this->Cell(12 / 100 * $width, $height, $_SESSION['lang']['jmlhBrg'], 1, 0, 'C', 1);
                    $this->Cell(10 / 100 * $width, $height, $_SESSION['lang']['hargasatuan'], 1, 0, 'C', 1);
                    $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['satuan'], 1, 0, 'C', 1);
                    $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['kurs'], 1, 1, 'C', 1);
                }

                function Footer() {
                    $this->SetY(-15);
                    $this->SetFont('Arial', 'I', 8);
                    $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
                }

            }

            $pdf = new PDF('L', 'pt', 'A4');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 12;
            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 8);
            $sDet = "select nokontrak,franco,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,kuantitaskontrak,kodept,hargasatuan,matauang,satuan  from " . $dbname . ".pmn_kontrakjual where (tanggalkontrak between '" . $tanggalmulai . "' and '" . $tanggalakhir . "') and kodebarang like '%" . $kdBrg . "%' and kodept='" . $idPabrik . "' order by tanggalkontrak asc ";
             $qDet = $owlPDO->query($sDet) or die(print " Gagal: " . PDOException::getMessage());
            $row = owlBaris($qDet);

            $namabarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

            if ($row > 0) {

                $qDet->setFetchMode(PDO::FETCH_ASSOC);
                while ($rDet = $qDet->fetch()) {
                    $no+=1;
                    $arrKurs = array("IDR" => "1", "USD" => "");

                    $sCust = "select namacustomer  from " . $dbname . ".pmn_4customer where kodecustomer='" . $rDet['koderekanan'] . "'";
                    $qCust = $owlPDO->query($sCust) or die(print " Gagal: " . PDOException::getMessage());
                    $qCust->setFetchMode(PDO::FETCH_ASSOC);
                    $rCust = $qCust->fetch();

                    $awalY = $pdf->GetY();
                    $pdf->SetY($awalY);
                    $pdf->SetX(1000);
                    $pdf->MultiCell(12 / 100 * $width, $height, $rDet['nokontrak'], '0', 'L');
                    $akhirYKontrak = $pdf->GetY();

                    $pdf->SetY($awalY);
                    $pdf->SetX(1000);
                    $pdf->MultiCell(14 / 100 * $width, $height, $rCust['namacustomer'], '0', 'L');
                    $akhirYCustomer = $pdf->GetY();

                    $pdf->SetY($awalY);
                    $pdf->SetX(1000);
                    $pdf->MultiCell(12 / 100 * $width, $height, $namabarang[$rDet['kodebarang']], '0', 'L');
                    $akhirYBarang = $pdf->GetY();

                    $akhirY = max($akhirYKontrak, $akhirYCustomer, $akhirYBarang);
                    $height2 = $akhirY - $awalY;
                    $pdf->SetY($awalY);

                    if ($akhirY == $akhirYKontrak) {
                        $multiHeightKontrak = $height;
                    } else {
                        $multiHeightKontrak = $height2;
                    }

                    if ($akhirY == $akhirYCustomer) {
                        $multiHeightCustomer = $height;
                    } else {
                        $multiHeightCustomer = $height2;
                    }

                    if ($akhirY == $akhirYBarang) {
                        $multiHeightBarang = $height;
                    } else {
                        $multiHeightBarang = $height2;
                    }


                    $currentX = $pdf->GetX();
                    $pdf->Cell(3 / 100 * $width, $height2, $no, 1, 0, 'C', 1);
                    $pdf->SetY($awalY);
                    $pdf->SetX($currentX + (3 / 100 * $width));
                    $currentX = $pdf->GetX();
                    $pdf->MultiCell(12 / 100 * $width, $multiHeightKontrak, $rDet['nokontrak'], 1, 'L', 1);
                    $pdf->SetY($awalY);
                    $pdf->SetX($currentX + (12 / 100 * $width));
                    $currentX = $pdf->GetX();
                    $pdf->MultiCell(14 / 100 * $width, $multiHeightCustomer, $rCust['namacustomer'], 1, 'L', 1);
                    $pdf->SetY($awalY);
                    $pdf->SetX($currentX + (14 / 100 * $width));
                    $currentX = $pdf->GetX();
                    $pdf->Cell(13 / 100 * $width, $height2, tanggalnormal($rDet['tanggalkontrak']), 1, 0, 'C', 1);

                    $currentX = $pdf->GetX();
                    $pdf->Cell(12 / 100 * $width, $height2, tanggalnormal($rDet['tanggalkirim']), 1, 0, 'C', 1);
                    $pdf->SetY($awalY);
                    $pdf->SetX($currentX + (12 / 100 * $width));
                    $currentX = $pdf->GetX();
                    $pdf->MultiCell(12 / 100 * $width, $multiHeightBarang, $namabarang[$rDet['kodebarang']], 1, 'L', 1);
                    $pdf->SetY($awalY);
                    $pdf->SetX($currentX + (12 / 100 * $width));
                    $pdf->Cell(12 / 100 * $width, $height2, number_format($rDet['kuantitaskontrak'], 2), 1, 0, 'R', 1);
                    $pdf->Cell(10 / 100 * $width, $height2, number_format($rDet['hargasatuan'], 2), 1, 0, 'R', 1);
                    $pdf->Cell(6 / 100 * $width, $height2, $rDet['satuan'], 1, 0, 'C', 1);
                    $pdf->Cell(6 / 100 * $width, $height2, $arrKurs[$rDet['matauang']], 1, 0, 'C', 1);
                    $pdf->Ln($height2);
                }
            } else {
                $pdf->Cell($width, $height, "Not Found", 1, 1, 'C', 1);
            }

            $pdf->Output();
            break;
        case'excel':

            $periode = checkPostGet('periode', '');
            $idPabrik = checkPostGet('idPabrik', '');
            $kdBrg = checkPostGet('kdBrg', '');



            $sBrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang like '%" . $kdBrg . "%'";
            $qBrg = $owlPDO->query($sBrg) or die(print " Gagal: " . PDOException::getMessage());
            $qBrg->setFetchMode(PDO::FETCH_ASSOC);
            $rBrg = $qBrg->fetch();

            if ($kdBrg == '') {
                $judulNamaBarang = $_SESSION['lang']['all'];
            } else {
                $judulNamaBarang = $rBrg['namabarang'];
            }

            $sOrg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $idPabrik . "'";
           $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
            $qOrg->setFetchMode(PDO::FETCH_ASSOC);
            $rOrg = $qOrg->fetch();
            $stream.="
                        <table>
                        <tr><td colspan=9 align=center><b>" . strtoupper($_SESSION['lang']['laporanPenjualan']) . "</b></td></tr>
                        <tr><td colspan=3>" . $_SESSION['lang']['periode'] . "</td><td>" . tanggalnormal($tanggalmulai) . " s/d " . tanggalnormal($tanggalakhir) . "</td></tr>
                        <tr><td colspan=3>" . $_SESSION['lang']['komoditi'] . "</td><td>" . $judulNamaBarang . "</td></tr>
                        <tr><td colspan=3>" . $_SESSION['lang']['nm_perusahaan'] . "</td><td>" . $rOrg['namaorganisasi'] . "</td></tr>
                        <tr><td colspan=3></td><td></td></tr>
                        </table>
                        <table border=1>
                       
                        

                        <tr>
              <td bgcolor=#DEDEDE align=center rowspan=2>No.</td>
              <td bgcolor=#DEDEDE align=center rowspan=2>" . $_SESSION['lang']['NoKontrak'] . "</td>
             <td bgcolor=#DEDEDE align=center rowspan=2>" . $_SESSION['lang']['statusjual'] . "</td>
              <td bgcolor=#DEDEDE align=center rowspan=2>" . $_SESSION['lang']['nmcust'] . "</td>
              <td bgcolor=#DEDEDE align=center rowspan=2>" . $_SESSION['lang']['tglKontrak'] . "</td>
              <td bgcolor=#DEDEDE align=center rowspan=2>" . $_SESSION['lang']['tanggalkirim'] . "</td>
              <td bgcolor=#DEDEDE align=center rowspan=2>" . $_SESSION['lang']['komoditi'] . "</td>
              <td bgcolor=#DEDEDE align=center rowspan=2>" . $_SESSION['lang']['jmlhBrg'] . "<br>(Kg)</td>
              <td bgcolor=#DEDEDE align=center colspan=5>" . $_SESSION['lang']['kualitas'] . "</td>   
                       
              <td bgcolor=#DEDEDE align=center rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>
              <td bgcolor=#DEDEDE align=center rowspan=2>" . $_SESSION['lang']['kurs'] . "</td>
              <td bgcolor=#DEDEDE align=center rowspan=2>" . $_SESSION['lang']['hargasatuan'] . "</td>         
                  
              <td bgcolor=#DEDEDE align=center colspan=4>Delivery Term</td>    
              <td bgcolor=#DEDEDE align=center colspan=3>" . $_SESSION['lang']['pemenuhan'] . "</td>

              <td bgcolor=#DEDEDE align=center rowspan=2>No SPP</td>
              </tr>
              
                <tr>
                    <td bgcolor=#DEDEDE align=center>FFA<br>%</td>  
                    <td bgcolor=#DEDEDE align=center>DOBI</td>
                    <td bgcolor=#DEDEDE align=center>M & I<br>%</td>
                    <td bgcolor=#DEDEDE align=center>Moisture<br>%</td>
                    <td bgcolor=#DEDEDE align=center>Impurities<br>%</td>
                    <td bgcolor=#DEDEDE align=center>I</td>
                    <td bgcolor=#DEDEDE align=center>II</td>
                    <td bgcolor=#DEDEDE align=center>III</td>
                    <td bgcolor=#DEDEDE align=center>IV</td>
                    <td bgcolor=#DEDEDE align=center>Timbangan Pabrik</td>
                    <td bgcolor=#DEDEDE align=center>Timbangan Pembeli</td>
                    <td bgcolor=#DEDEDE align=center>Selisih</td>
              </tr>
                         
                        "; //<tr><td bgcolor=#DEDEDE align=center>Loco</td><td bgcolor=#DEDEDE align=center>Franco</td></tr>
            $strx = "select *  
                               from " . $dbname . ".pmn_kontrakjual where (tanggalkontrak between '" . $tanggalmulai . "' and '" . $tanggalakhir . "') and kodebarang like '%" . $kdBrg . "%' and kodept='" . $idPabrik . "' 
                               order by tanggalkontrak asc";
             $resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
            $row = owlBaris($resx);
            if ($row < 1) {
                $stream.="  <tr class=rowcontent><td>Not Found</td></tr>
                     
                        ";
            } else {
                $no = 0;
                $resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());

                $resx->setFetchMode(PDO::FETCH_ASSOC);
                while ($barx = $resx->fetch()) {


                    $no+=1;

                    $sBrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $barx['kodebarang'] . "'";
                     $qBrg = $owlPDO->query($sBrg) or die(print " Gagal: " . PDOException::getMessage());
                    $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                    $rBrg = $qBrg->fetch();

                    $sCust = "select namacustomer  from " . $dbname . ".pmn_4customer where kodecustomer='" . $barx['koderekanan'] . "'";
                    $qCust = $owlPDO->query($sCust) or die(print " Gagal: " . PDOException::getMessage());
                    $qCust->setFetchMode(PDO::FETCH_ASSOC);
                    $rCust = $qCust->fetch();

                    $sStat = "select penjualan  from " . $dbname . ".pmn_5franco where id_franco='" . $barx['franco'] . "'";
                    $qStat = $owlPDO->query($sStat) or die(print " Gagal: " . PDOException::getMessage());
                    $qStat->setFetchMode(PDO::FETCH_ASSOC);
                    $rStat = $qStat->fetch();

                    $sTimb = "select sum(beratbersih) as beratbersih,sum(kgpembeli) as kgpembeli  from " . $dbname . ".pabrik_timbangan where nokontrak='" . $barx['nokontrak'] . "'";
                     $qTimb = $owlPDO->query($sTimb) or die(print " Gagal: " . PDOException::getMessage());
                    $qTimb->setFetchMode(PDO::FETCH_ASSOC);
                    $rTimb = $qTimb->fetch();

                    $sOrg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $barx['kodept'] . "'";
                     $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
                    $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                    $rOrg = $qOrg->fetch();
                    $isi = "";
                    $iSpp = "select * from " . $dbname . ".pmn_suratperintahpengiriman where nokontrak='" . $barx['nokontrak'] . "' ";
                   $nSpp = $owlPDO->query($iSpp) or die(print " Gagal: " . PDOException::getMessage());
                    $nSpp->setFetchMode(PDO::FETCH_ASSOC);
                    while ($dSpp = $nSpp->fetch()) {
                        $isi.=$dSpp['nodo'] . ",\n";
                    }

                    $arrKurs = array("IDR" => "1", "USD" => "");

                   $stream.="<tr class=rowcontent>
                                 <td>" . $no . "</td>
                                <td>" . $barx['nokontrak'] . "</td>
                                <td>" . $rStat['penjualan'] . "</td>
                                <td>" . $rCust['namacustomer'] . "</td>
                                <td>" . tanggalnormal($barx['tanggalkontrak']) . "</td>

                                <td>" . tanggalnormal($barx['tanggalkirim']) . "</td>  
                                <td>" . $rBrg['namabarang'] . "</td>  

                                <td align=right>" . number_format($barx['kuantitaskontrak'], 0) . "</td>

                                <td align=right>" . number_format($barx['ffa'], 2) . "</td>
                                <td align=right>" . number_format($barx['dobi'], 2) . "</td>
                                <td align=right>" . number_format($barx['mdani'], 2) . "</td>
                                <td align=right>" . number_format($barx['moist'], 2) . "</td>
                                <td align=right>" . number_format($barx['dirt'], 2) . "</td>

                                <td>" . $barx['matauang'] . "</td>  
                                <td>" . $arrKurs[$barx['matauang']] . "</td> 

                                <td align=right>" . number_format($barx['hargasatuan'], 2) . "</td>";

                             $total1+=$barx['kuantitaskontrak'];
                            // $total2+=$res['hargasatuan'];
                            $total2+=$rTimb['beratbersih'];
                            $total3+=$rTimb['kgpembeli'];
                            $total4+=abs($rTimb['kgpembeli'] - $rTimb['beratbersih']);

                    for ($i = 0; $i <= 3; $i++) {
                        if ($i == 0) {
                            $i = "";
                        }
                        $isiMulai = tanggalnormal($barx['tanggalkirim' . $i]);
                        $isiSd = tanggalnormal($barx['sdtanggal' . $i]);
                        $sd = "s/d";

                        if ($isiMulai == '00-00-0000' || $isiSd == '00-00-0000') {
                            $isiMulai = "";
                            $isiSd = "";
                            $sd = "";
                        }


                        $stream.="<td>" . $isiMulai . " " . $sd . " " . $isiSd . "</td>";
                    }


                    $stream.="

                                <td align=right>" . number_format($rTimb['beratbersih'], 2) . "</td>
                                <td align=right>" . number_format($rTimb['kgpembeli'], 2) . "</td>
                                <td align=right>" . number_format(abs($rTimb['kgpembeli'] - $rTimb['beratbersih']), 2) . "</td>
                                 <td>" . $isi . "</td>
                                </tr>";




                }
                $stream.="<tr class=rowcontent>
                                <td colspan=7>".$_SESSION['lang']['total']."</td>
                                <td align=right>".number_format($total1)."</td>
                                <td colspan=12></td>
                                <td align=right>".number_format($total2, 2)."</td>
                                <td align=right>".number_format($total3, 2)."</td>
                                <td align=right>".number_format($total4, 2)."</td>
                                <td align=right></td>
                                </tr>";


            }


            //echo "warning:".$strx;
            //=================================================
            $stream.="</table>";
            $stream.="</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];

            $nop_ = "PemenuhanKontrak";
            if (strlen($stream) > 0) {
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            @unlink('tempExcel/' . $file);
                        }
                    }
                    closedir($handle);
                }
                $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
                if (!fwrite($handle, $stream)) {
                    echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                    exit;
                } else {
                    echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
                }
                fclose($handle);
            }
            break;
        case'getDetail':
            echo"<link rel=stylesheet type=text/css href=style/generic.css>";
            $nokontrak = $_GET['nokontrak'];
            $sHed = "select  a.tanggalkontrak,a.koderekanan,a.kodebarang from " . $dbname . ".pmn_kontrakjual a where a.nokontrak='" . $nokontrak . "'";
            $qHead = $owlPDO->query($sHed) or die(print " Gagal: " . PDOException::getMessage());
            $qHead->setFetchMode(PDO::FETCH_ASSOC);  
            $rHead=$qHead->fetch();          
            $sBrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $rHead['kodebarang'] . "'";
            $qBrg = $owlPDO->query($sBrg) or die(print " Gagal: " . PDOException::getMessage());
            $qBrg->setFetchMode(PDO::FETCH_ASSOC);  
            $rBrg=$qBrg->fetch();   

            $sCust = "select namacustomer  from " . $dbname . ".pmn_4customer where kodecustomer='" . $rHead['koderekanan'] . "'";
            $qCust = $owlPDO->query($sCust) or die(print " Gagal: " . PDOException::getMessage());
            $qCust->setFetchMode(PDO::FETCH_ASSOC);  
            $rCust=$qCust->fetch();            
            echo"<fieldset><legend>" . $_SESSION['lang']['detailPengiriman'] . "</legend>
        <table cellspacing=1 border=0 class=myinputtext>
        <tr>
                <td>" . $_SESSION['lang']['NoKontrak'] . "</td><td>:</td><td>" . $nokontrak . "</td>
        </tr>
        <tr>
                <td>" . $_SESSION['lang']['tglKontrak'] . "</td><td>:</td><td>" . tanggalnormal($rHead['tanggalkontrak']) . "</td>
        </tr>
        <tr>
                <td>" . $_SESSION['lang']['komoditi'] . "</td><td>:</td><td>" . $rBrg['namabarang'] . "</td>
        </tr>
        <tr>
                <td>" . $_SESSION['lang']['Pembeli'] . "</td><td>:</td><td>" . $rCust['namacustomer'] . "</td>
        </tr>
        </table><br />
        <table cellspacing=1 border=0 class=sortable><thead>
        <tr class=data>
        <td>" . $_SESSION['lang']['notransaksi'] . "</td>
        <td>" . $_SESSION['lang']['tanggal'] . "</td>
        <td>" . $_SESSION['lang']['nodo'] . "</td>
        <td>" . $_SESSION['lang']['nosipb'] . "</td>
        <td>" . $_SESSION['lang']['beratBersih'] . "</td>
        <td>" . $_SESSION['lang']['kodenopol'] . "</td>
        <td>" . $_SESSION['lang']['sopir'] . "</td>
        </tr></thead><tbody>
        ";
            /*  $sDet="select a.tanggalkontrak,a.pembeli,a.komoditi,b.* from ".$dbname.".pmn_kontrakjual a inner join ".$dbname.".pabrik_timbangan on a.nokontrak=b.nokontrak where a.nokontrak='".$nokontrak."'";
             */

            $sDet = "select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir from " . $dbname . ".pabrik_timbangan where nokontrak='" . $nokontrak . "'";
            $qDet = $owlPDO->query($sDet) or die(print " Gagal: " . PDOException::getMessage());
            $qDet->setFetchMode(PDO::FETCH_ASSOC);  
            $rCek = owlBaris($qDet);
            if ($rCek > 0) {
                while ($rDet = $qDet->fetch()) {
                    echo"<tr class=rowcontent>
                        <td>" . $rDet['notransaksi'] . "</td>
                        <td>" . tanggalnormal($rDet['tanggal']) . "</td>
                        <td>" . $rDet['nodo'] . "</td>
                        <td>" . $rDet['nosipb'] . "</td>
                        <td align=right>" . number_format($rDet['beratbersih'], 2) . "</td>
                        <td>" . $rDet['nokendaraan'] . "</td>
                        <td>" . ucfirst($rDet['supir']) . "</td>
                        </tr>";
                }
            } else {
                echo"<tr><td colspan=7>Not Found</td></tr>";
            }
            echo"</tbody></table></fieldset>";

            break;
        default:
            break;
    }
}
?>