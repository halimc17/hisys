<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$optSupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$proses = checkPostGet('proses','');
$jenisId = checkPostGet('jenisId','');
$suppId = checkPostGet('suppId','');
$kdUnit = checkPostGet('kdUnit','');
$lstPo = checkPostGet('lstPo','');
$periode = checkPostGet('periode','');
$periode2 = checkPostGet('periode2','');
$nmSupplier = checkPostGet('nmSupplier','');
$unitId = $_SESSION['lang']['all'];
$dktlmpk = $_SESSION['lang']['all'];

if ($periode2 != '') {
    if ($periode == '') {
        exit("Error: Period required");
    }
    $tgl1 = $periode . "-01";
    $tgl2 = $periode2 . "-01";
    $sSel = "SELECT datediff('" . $tgl2 . "','" . $tgl1 . "') as selisih";
	$qSel=$owlPDO->query($sSel) or die(print " Gagal: ".PDOException::getMessage());
	$qSel->setFetchMode(PDO::FETCH_ASSOC);
    $rSel = $qSel->fetch();
    if ($rSel['selisih'] < 0) {
        exit("Error: Period incorrect");
    }
    $whre.=" and substr(tanggal,1,7) between '" . $periode . "' and  '" . $periode2 . "'";
} else if ($periode != '') {
    $whre.=" and substr(tanggal,1,7)='" . $periode . "'";
}

$sto=0;
if ($kdUnit != '') {
    $whre.=" and kodeorg like '%" . $kdUnit . "%'";
    $unitId = $optNmOrg[$kdUnit];
    $sUnit = "select kodeorganisasi from " . $dbname . ".organisasi where induk like '%" . $kdUnit . "%'";
	$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
	$qUnit->setFetchMode(PDO::FETCH_ASSOC);
    while ($rUnit = $qUnit->fetch()) {
        $sto++;
        if ($sto == 1) {
            $wer = "'" . $rUnit['kodeorganisasi'] . "'";
        } else {
            $wer.=",'" . $rUnit['kodeorganisasi'] . "'";
        }
    }
    $whr = " and a.kodeorg in (" . $wer . ")";
} else {
    $sUnit = "select kodeorganisasi from " . $dbname . ".organisasi where char_length(kodeorganisasi)='4'";
	$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
	$qUnit->setFetchMode(PDO::FETCH_ASSOC);
    while ($rUnit = $qUnit->fetch()) {
        $sto++;
        if ($sto == 1) {
            $wer = "'" . $rUnit['kodeorganisasi'] . "'";
        } else {
            $wer.=",'" . $rUnit['kodeorganisasi'] . "'";
        }
    }
    $whr = " and a.kodeorg in (" . $wer . ")";
}
if ($jenisId != '') {
    $jenisId == '0' ? $dr = "k" : $dr = "p";
    $whre.=" and tipeinvoice='" . $dr . "'";
    $jenisId == '0' ? $dre = " and substr(kodesupplier,1,1)='K'" : $dre = " and substr(kodesupplier,1,1)='S'";
    $whr.=$dre;
}

if ($suppId != '') {
    $whre.=" and kodesupplier='" . $suppId . "'";
}

if ($proses == 'preview' || $proses == 'excel') {
    if ($jenisId == '') {
        exit("Error: Transaction type required");
    }

    $sTagi = "select distinct nopo,sum(nilaiinvoice+nilaippn) as jumlah,noinvoice,kodesupplier,jatuhtempo,tanggal from
           " . $dbname . ".keu_tagihanht where tanggal!=''  " . $whre . " group by nopo,noinvoice";
	$qTagi=$owlPDO->query($sTagi) or die(print " Gagal: ".PDOException::getMessage());
	$qTagi->setFetchMode(PDO::FETCH_ASSOC);
    while ($rTagi = $qTagi->fetch()) {
        if ($rTagi['kodesupplier'] != '') {
            $dtNopo[$rTagi['noinvoice']] = $rTagi['nopo'];
            $dtNotrans[$rTagi['noinvoice']] = $rTagi['noinvoice'];
            $dtTagih[$rTagi['noinvoice']] = $rTagi['jumlah'];
            $dtSupp[$rTagi['noinvoice']] = $rTagi['kodesupplier'];
            $dtJth[$rTagi['noinvoice']] = $rTagi['jatuhtempo'];
            $dtTglEn[$rTagi['noinvoice']] = $rTagi['tanggal'];
        }
    }

    $cekdt = count($dtNotrans);
    if ($cekdt == 0) {
        exit("Error: No data found");
    }
    $brdr = 0;
    $bgcoloraja = '';
    if ($proses == 'excel') {
        //exit("error:".$arrPilMode[$pilMode]."__".$pilMode);
        $bgcoloraja = "bgcolor=#DEDEDE align=center";
        $brdr = 1;
        $tab.="
    <table>
    <tr><td colspan=11 align=left><b><font size=5>Riwayat Pembayaran</font></b></td></tr>
    <tr><td colspan=11 align=left>" . $_SESSION['lang']['pt'] . " : " . $unitId . "</td></tr>
    <tr><td colspan=11 align=left>" . $_SESSION['lang']['periode'] . " : " . $periode . "</td></tr>
    </table>";
    }

    $tab.="<table cellspacing=1 border=" . $brdr . " class=sortable>
        <thead class=rowheader>
        <tr>
        <td " . $bgcoloraja . " rowspan=2 align=center>No.</td>
        <td " . $bgcoloraja . " rowspan=2 align=center>" . $_SESSION['lang']['noinvoice'] . "</td>
        <td " . $bgcoloraja . " rowspan=2 align=center>" . $_SESSION['lang']['tanggal'] . "</td>
        <td " . $bgcoloraja . " rowspan=2 align=center>" . $_SESSION['lang']['jatuhtempo'] . "</td>
        <td " . $bgcoloraja . " colspan=4 align=center>" . $_SESSION['lang']['tagihan'] . "</td>
        <td " . $bgcoloraja . " colspan=3 align=center>" . $_SESSION['lang']['dibayar'] . "</td>
        <td " . $bgcoloraja . " rowspan=2 align=center>" . $_SESSION['lang']['sisa'] . "</td></tr>";

    $tab.="<tr>";
    $tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['kodesupplier'] . "</td>";
    $tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['namasupplier'] . "</td>";
    $tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['nopo'] . "/ " . $_SESSION['lang']['nospk'] . "</td>";
    $tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['tagihan'] . "</td>";
    $tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['notransaksi'] . "</td>";
    $tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>";
    $tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['dibayar'] . "</td>";
    $tab.="</tr></thead><tbody>";
    foreach ($dtNotrans as $hutang) {
        $aerta+=1;
        $sByr = "select distinct sum(a.jumlah) as jumlah,b.tanggal,a.notransaksi
                  from " . $dbname . ". keu_kasbankdt a
                  left join " . $dbname . ".keu_kasbankht b on a.notransaksi=b.notransaksi where a.keterangan1='" . $hutang . "'
                  and a.tipetransaksi='K' and b.posting=1
                  group by a.tipetransaksi";
		$qByr=$owlPDO->query($sByr) or die(print " Gagal: ".PDOException::getMessage());
		$qByr->setFetchMode(PDO::FETCH_ASSOC);
        $rByr = $qByr->fetch();
        $penambah = $rByr['jumlah'];
        $sByr = "select distinct sum(a.jumlah) as jumlah  from " . $dbname . ". keu_kasbankdt a
                  left join " . $dbname . ".keu_kasbankht b on a.notransaksi=b.notransaksi where a.keterangan1='" . $hutang . "'
                  and a.tipetransaksi='M'  and b.posting=1 group by a.tipetransaksi";
		$qByr=$owlPDO->query($sByr) or die(print " Gagal: ".PDOException::getMessage());
		$qByr->setFetchMode(PDO::FETCH_ASSOC);
        $rByr2 = $qByr->fetch();

        $pengurang = $rByr2['jumlah'];

        $dibayarsmp = $penambah - $pengurang;
        $tab.="<tr class=rowcontent>";
        $tab.="<td align=center>" . $aerta . "</td>";
        $tab.="<td>" . $hutang . "</td>";
        $tab.="<td>" . $dtTglEn[$hutang] . "</td>";
        $tab.="<td>" . $dtJth[$hutang] . "</td>";
        $tab.="<td>" . $dtSupp[$hutang] . "</td>";
        $tab.="<td>" . $optSupp[$dtSupp[$hutang]] . "</td>";
        $tab.="<td>" . $dtNopo[$hutang] . "</td>";
        $tab.="<td align=right>" . number_format($dtTagih[$hutang], 0) . "</td>";
        $tab.="<td>" . $rByr['notransaksi'] . "</td>";
        $tab.="<td>" . $rByr['tanggal'] . "</td>";
        $tab.="<td align=right>" . number_format($dibayarsmp, 0) . "</td>";
        $sis = $dtTagih[$hutang] - $dibayarsmp;
        $tab.="<td align=right>" . number_format($sis, 0) . "</td>";
        $tab.="</tr>";
    }
}
$tab.="</tbody></table>";
switch ($proses) {
    case'getPt':
        //echo "warning:masuk";
        $optorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sOrg = "select distinct kodeorg  from " . $dbname . ".log_po_vw where substr(tanggal,1,7)='" . $periode . "'";
		$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['kodeorg'] . ">" . $optNmOrg[$rOrg['kodeorg']] . "</option>";
        }
        echo $optorg;
        break;
    case'getJenis':
        $optSu = $optorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $jenisId == '0' ? $whr = " and tipeinvoice='K'" : $whr = " and tipeinvoice='P'";
        if ($kdUnit != '') {
            $whr.=" and kodeorg like '%" . $kdUnit . "%'";
        }

        $sData = "select distinct nopo from " . $dbname . ".keu_tagihanht 
               where substr(tanggal,1,7)='" . $periode . "' " . $whr . "";
        $qOrg=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
		while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['nopo'] . ">" . $rOrg['nopo'] . "</option>";
        }
        $sData = "select distinct kodesupplier from " . $dbname . ".keu_tagihanht
               where substr(tanggal,1,7)='" . $periode . "' " . $whr . "";
		$qOrg=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $optSu.="<option value='" . $rOrg['kodesupplier'] . "'>" . $optSupp[$rOrg['kodesupplier']] . "</option>";
        }

        echo $optorg . "###" . $optSu;
        break;
    case'preview':
        echo $tab;
        break;

    case'excel':

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        $nop_ = "riwayat_pembayaran_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls.gz';
            </script>";

        break;
    case'getTgl':
        if ($periode != '') {
            $tgl = $periode;
            $tanggal = $tgl[0] . "-" . $tgl[1];
        } elseif ($period != '') {
            $tgl = $period;
            $tanggal = $tgl[0] . "-" . $tgl[1];
        }
        if ($kdUnit == '') {
            $kdUnit = $_SESSION['lang']['lokasitugas'];
        }
        $sTgl = "select distinct tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji where kodeorg='" . substr($kdUnit, 0, 4) . "' and periode='" . $tanggal . "' ";
		$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
		$qTgl->setFetchMode(PDO::FETCH_ASSOC);
        $rTgl = $qTgl->fetch();
        echo tanggalnormal($rTgl['tanggalmulai']) . "###" . tanggalnormal($rTgl['tanggalsampai']);
        break;

    case'pdf':
        $kdPt = $_GET['kdPt'];
        //$arr="##kdPt##kdSup##kdUnit##tglDr##tglSmp";
        $kdSup = $_GET['kdSup'];
        $kdUnit = $_GET['kdUnit'];
        $tglDari = tanggalsystem($_GET['tglDr']);
        $tanggalSampai = tanggalsystem($_GET['tanggalSampai']);
        $lokBeli = $_GET['lokBeli'];
        //echo $tglDari."__".$tanggalSampai;exit();
        if (($tglDari == '') || ($tanggalSampai == '')) {
            echo"warning:Tanggal Dari dan Sampai Tanggal Tidak Boleh Kosong";
            exit();
        } else {
            if ($kdPt != '') {
                $where.=" and a.kodeorg='" . $kdPt . "'";
            }
            if ($kdUnit != '') {
                $where.=" and substring(b.nopp,16,4)='" . $kdUnit . "'";
            }
            if ($kdSup != "") {
                $where.=" and a.kodesupplier='" . $kdSup . "'";
            }
            if (($tglDr != '') || ($tanggalSampai != '')) {
                $where.=" and (a.tanggal between '" . $tglDari . "' and '" . tanggalsystem($_GET['tanggalSampai']) . "')";
            }
            if ($lokBeli != '') {
                $where.=" and lokalpusat='" . $lokBeli . "'";
            }
        }

        class PDF extends FPDF {

            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                global $kdPt;
                global $kdSup;
                global $kdUnit;
                global $tglDari;
                global $tanggalSampai;
                global $where;
                global $isi;
                global $owlPDO;

                $isi = array();
                if ($kdPt == "") {
                    $pt = 'MHO';
                } else {
                    $pt = $kdPt;
                }
                # Alamat & No Telp
                /*         $query = selectQuery($dbname,'organisasi','namaorganisasi,alamat,telepon',
                  "kodeorganisasi='".$kdPt."'");
                  $orgData = fetchData($query); */
                $sAlmat = "select namaorganisasi,alamat,telepon from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'";
                $qAlamat=$owlPDO->query($sAlmat) or die(print " Gagal: ".PDOException::getMessage());
				$qAlamat->setFetchMode(PDO::FETCH_ASSOC);
				$rAlamat = $qAlamat->fetch();

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $path = 'images/logo.jpg';
                $this->Image($path, $this->lMargin, $this->tMargin, 0, 55);
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(255, 255, 255);
                $this->SetX(100);
                $this->Cell($width - 100, $height, $rAlamat['namaorganisasi'], 0, 1, 'L');
                $this->SetX(100);
                $this->Cell($width - 100, $height, $rAlamat['alamat'], 0, 1, 'L');
                $this->SetX(100);
                $this->Cell($width - 100, $height, "Tel: " . $rAlamat['telepon'], 0, 1, 'L');
                $this->Line($this->lMargin, $this->tMargin + ($height * 4), $this->lMargin + $width, $this->tMargin + ($height * 4));
                $this->Ln();
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 11);
                $this->Cell($width, $height, $_SESSION['lang']['detPemb'], 0, 1, 'C');
                $this->SetFont('Arial', '', 8);
                $this->Cell($width, $height, "Periode : " . $_GET['tglDr'] . " s.d. " . $_GET['tanggalSampai'], 0, 1, 'C');
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 7);
                $this->SetFillColor(220, 220, 220);


                $this->Cell(3 / 100 * $width, $height, 'No', 1, 0, 'C', 1);
                $this->Cell(15 / 100 * $width, $height, $_SESSION['lang']['supplier'], 1, 0, 'C', 1);
                $this->Cell(12 / 100 * $width, $height, $_SESSION['lang']['nopo'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['tanggal'], 1, 0, 'C', 1);
                $this->Cell(22 / 100 * $width, $height, $_SESSION['lang']['namabarang'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['matauang'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['satuan'], 1, 0, 'C', 1);
                $this->Cell(10 / 100 * $width, $height, $_SESSION['lang']['total'], 1, 0, 'C', 1);
                $this->Cell(7 / 100 * $width, $height, $_SESSION['lang']['tanggal'] . " PP", 1, 0, 'C', 1);
                $this->Cell(7 / 100 * $width, $height, $_SESSION['lang']['tanggal'] . " BAPB", 1, 1, 'C', 1);
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
            }

        }

        $pdf = new PDF('L', 'pt', 'A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 9;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 7);
        $sData = "select a.kodesupplier from " . $dbname . ".log_poht a left join " . $dbname . ".log_podt b on a.nopo=b.nopo where a.statuspo>1 " . $where . " group by kodesupplier order by a.tanggal asc";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while ($rData = $qData->fetch()) {
            $isi[] = $rData;
        }
        $totalAll = array();
        foreach ($isi as $test => $dt) {
            $no+=1;

            $i = 0;
            $afdC = false;
            $sNm = "select namasupplier from " . $dbname . ".log_5supplier where supplierid='" . $dt['kodesupplier'] . "'";
			$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
			$qNm->setFetchMode(PDO::FETCH_ASSOC);
            $rNm = $qNm->fetch();
            if ($afdC == false) {
                $pdf->Cell(3 / 100 * $width, $height, $no, 'TLR', 0, 'C', 1);
                $pdf->Cell(15 / 100 * $width, $height, $rNm['namasupplier'], 'TLR', 0, 'C', 1);
            }

            $sList = "select distinct a.tanggal,a.matauang,b.kodebarang,b.satuan,b.nopo,b.jumlahpesan,b.nopp,b.hargasatuan from " . $dbname . ".log_poht a left join " . $dbname . ".log_podt b on a.nopo=b.nopo where a.kodesupplier='" . $dt['kodesupplier'] . "' and b.nopo!='NULL' and a.tanggal between '" . $tglDari . "' and '" . $tanggalSampai . "'";
            $qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
			$qList->setFetchMode(PDO::FETCH_ASSOC);
			$grandTot = array();

            while ($rList = $qList->fetch()) {
                $limit++;
                $tglBapb = "";
                $sBrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $rList['kodebarang'] . "'";
				$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
				$qBrg->setFetchMode(PDO::FETCH_ASSOC);
				$rBrg = $qBrg->fetch();
                if ($rList['matauang'] != 'IDR') {
                    $sKurs = "select kurs from " . $dbname . ".setup_matauangrate where kode='" . $rList['matauang'] . "' and daritanggal='" . $rList['tanggal'] . "'";
                    $qKurs=$owlPDO->query($sKurs) or die(print " Gagal: ".PDOException::getMessage());
					$qKurs->setFetchMode(PDO::FETCH_ASSOC);
					$rKurs = $qKurs->fetch();
                    if ($rKurs != '') {
                        $hrg = $rKurs['kurs'] * $rList['hargasatuan'];
                        $totHrg = $rList['jumlahpesan'] * $hrg;
                    } else {
                        if ($rList['matauang'] == 'USD') {
                            $hrg = $rList['hargasatuan'] * 8850;
                            $totHrg = $rList['jumlahpesan'] * $hrg;
                            $rList['matauang'] = "IDR";
                        } elseif ($rList['matauang'] == 'EUR') {
                            $hrg = $rList['hargasatuan'] * 12643;
                            $totHrg = $rList['jumlahpesan'] * $hrg;
                            $rList['matauang'] = "IDR";
                        } elseif (($rList['matauang'] == '') || ($rList['matauang'] == 'NULL')) {
                            $totHrg = $rList['jumlahpesan'] * $rList['hargasatuan'];
                        }
                    }
                } else {
                    $totHrg = $rList['jumlahpesan'] * $rList['hargasatuan'];
                }
                //$totHrg=$rList['jumlahpesan']*$rList['hargasatuan'];
                $grandTot['total']+=$totHrg;
                if ($rList['nopp'] != "") {
                    $sTgl = "select tanggal from " . $dbname . ".log_prapoht where nopp='" . $rList['nopp'] . "'";
                    $qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
					$qTgl->setFetchMode(PDO::FETCH_ASSOC);
					$rTgl = $qTgl->fetch();

                    if (($rTgl['tanggal'] != "") || ($rTgl['tanggal'] != "000-00-00")) {
                        $tglPP = tanggalnormal($rTgl['tanggal']);
                    } else {
                        $tglPP = "";
                    }
                } else {
                    $tglPP = "";
                }
                if ($rList['nopo'] != "") {
                    $sTgl2 = "select tanggal from " . $dbname . ".log_transaksiht where nopo='" . $rList['nopo'] . "' and tipetransaksi=1";
                    $qTgl2=$owlPDO->query($sTgl2) or die(print " Gagal: ".PDOException::getMessage());
					$qTgl2->setFetchMode(PDO::FETCH_ASSOC);
					$rTgl2 = $qTgl2->fetch();
                    if ($rTgl2['tanggal'] != "") {
                        $tglBapb = tanggalnormal($rTgl2['tanggal']);
                    } else {
                        $tglBapb = "";
                    }
                } else {
                    $tglBapb = "";
                }
                if ($afdC == true) {
                    $i = 0;
                    $pdf->Cell(3 / 100 * $width, $height, '', 'LR', $align[$i], 1);
                    $pdf->Cell(15 / 100 * $width, $height, '', 'LR', $align[$i], 1);
                    //$pdf->Cell($length[$i]/100*$width,$height,'','LR',$align[$i],1);
                    $i++;
                } else {
                    $afdC = true;
                }
                $pdf->Cell(12 / 100 * $width, $height, $rList['nopo'], 1, 0, 'L', 1);
                $pdf->Cell(6 / 100 * $width, $height, tanggalnormal($rList['tanggal']), 1, 0, 'C', 1);
                $pdf->Cell(22 / 100 * $width, $height, $rBrg['namabarang'], 1, 0, 'L', 1);
                $pdf->Cell(6 / 100 * $width, $height, $rList['matauang'], 1, 0, 'C', 1);
                $pdf->Cell(6 / 100 * $width, $height, $rList['jumlahpesan'], 1, 0, 'R', 1);
                $pdf->Cell(6 / 100 * $width, $height, $rList['satuan'], 1, 0, 'C', 1);
                $pdf->Cell(10 / 100 * $width, $height, number_format($totHrg, 2), 1, 0, 'R', 1);
                $pdf->Cell(7 / 100 * $width, $height, $tglPP, 1, 0, 'C', 1);
                $pdf->Cell(7 / 100 * $width, $height, $tglBapb, 1, 1, 'C', 1);
                //if($limit==46)				
//			{	
//				$limit=0;
//				$pdf->AddPage();
//			}
            }
            $totalAll['totalSemua']+=$grandTot['total'];
            $pdf->Cell(76 / 100 * $width, $height, "Sub Total", 1, 0, 'C', 1);
            $pdf->Cell(10 / 100 * $width, $height, number_format($grandTot['total'], 2), 1, 0, 'R', 1);
            $pdf->Cell(14 / 100 * $width, $height, '', 1, 1, 'R', 1);
        }
        $pdf->Cell(76 / 100 * $width, $height, "Total", 1, 0, 'C', 1);
        $pdf->Cell(10 / 100 * $width, $height, number_format($totalAll['totalSemua'], 2), 1, 0, 'R', 1);
        $pdf->Cell(14 / 100 * $width, $height, '', 1, 1, 'R', 1);
        $pdf->Cell($width, $height, terbilang($totalAll['totalSemua'], 2), 1, 1, 'C', 1);


        $pdf->Output();
        break;
    case'getSupplierNm':
        echo"<fieldset><legend>" . $_SESSION['lang']['result'] . "</legend>
                        <div style=\"overflow:auto;max-height:275px;max-width:455px;\">
                        <table cellpading=1 border=0 class=sortbale>
                        <thead>
                        <tr class=rowheader>
                        <td align=center>No.</td>
                        <td align=center>" . $_SESSION['lang']['kodesupplier'] . "</td>
                        <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
                        </tr><tbody>
                        ";
        $sSupplier = "select namasupplier,supplierid from " . $dbname . ".log_5supplier where namasupplier like '%" . $nmSupplier . "%' and namasupplier!=''";
        $qSupplier=$owlPDO->query($sSupplier) or die(print " Gagal: ".PDOException::getMessage());
		$qSupplier->setFetchMode(PDO::FETCH_ASSOC);	
		while ($rSupplier = $qSupplier->fetch()) {
            $no+=1;
            echo"<tr class=rowcontent  style=cursor:pointer onclick=setData('" . $rSupplier['supplierid'] . "')>
                         <td align=center>" . $no . "</td>
                         <td>" . $rSupplier['supplierid'] . "</td>
                         <td>" . $rSupplier['namasupplier'] . "</td>
                    </tr>";
        }
        echo"</tbody></table></div>";
        break;

    default:
        break;
}
?>