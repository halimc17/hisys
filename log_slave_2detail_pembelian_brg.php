<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

//$arr="##klmpkBrg##kdBrg##tglDr##tanggalSampai";
$proses = empty($_POST['proses']) ? (isset($_GET['proses']) ? $_GET['proses'] : '') : $_POST['proses'];
$klmpkBrg = empty($_POST['klmpkBrg']) ? (isset($_GET['klmpkBrg']) ? $_GET['klmpkBrg'] : '') : $_POST['klmpkBrg'];
$subklmpkBrg = empty($_POST['subklmpkBrg']) ? (isset($_GET['subklmpkBrg']) ? $_GET['subklmpkBrg'] : '') : $_POST['subklmpkBrg'];
$kdBrg = empty($_POST['kdBrg']) ? (isset($_GET['kdBrg']) ? $_GET['kdBrg'] : '') : $_POST['kdBrg'];
$tglDr = empty($_POST['tglDr']) ? (isset($_GET['tglDr']) ? tanggalsystem($_GET['tglDr']) : '') : tanggalsystem($_POST['tglDr']);
$tanggalSampai = empty($_POST['tanggalSampai']) ? (isset($_GET['tanggalSampai']) ? tanggalsystem($_GET['tanggalSampai']) : '') : tanggalsystem($_POST['tanggalSampai']);
$lokBeli = empty($_POST['lokBeli']) ? (isset($_GET['lokBeli']) ? $_GET['lokBeli'] : '') : $_POST['lokBeli'];
$nmBrg = empty($_POST['nmBrg']) ? (isset($_GET['nmBrg']) ? $_GET['nmBrg'] : '') : $_POST['nmBrg'];
$sKlmpk = "select kode,kelompok from " . $dbname . ".log_5klbarang order by kode";
$qKlmpk=$owlPDO->query($sKlmpk) or die(print " Gagal: ".PDOException::getMessage());
$qKlmpk->setFetchMode(PDO::FETCH_ASSOC);

while ($rKlmpk = $qKlmpk->fetch()) {
    $rKelompok[$rKlmpk['kode']] = $rKlmpk['kelompok'];
}

$sTgl = "select nopp,tanggal from " . $dbname . ".log_prapoht order by tanggal";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while ($rTgl = $qTgl->fetch()) {
    $rTglNopp[$rTgl['nopp']] = $rTgl['tanggal'];
}

$where = "";
if (($tglDr != '') || ($tanggalSampai != '')) {
    $where.=" and (tanggal between '" . $tglDr . "' and '" . $tanggalSampai . "')";
}
if ($lokBeli != '') {
    $where.=" and lokalpusat='" . $lokBeli . "'";
}
if ($klmpkBrg != '') {
    $where.=" and substr(kodebarang,1,3)='" . $klmpkBrg . "'";

}
if ($subklmpkBrg != '') {
    $where.=" and kode='" . $subklmpkBrg . "'";

}
if ($kdBrg != '') {
    $where.=" and kodebarang='" . $kdBrg . "'";
}


switch ($proses) {
    case'getBrg':
        //echo "warning:masuk";
        $optorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sOrg = "select kodebarang,namabarang from " . $dbname . ".log_5masterbarang where kelompokbarang='" . $klmpkBrg . "'";
		$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
         //exit('eror'.$klmpkBrg);
        while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['kodebarang'] . ">" . $rOrg['namabarang'] . "</option>";
        }
        echo $optorg;
        break;

        case'subklmpkBrg':
        //echo "warning:masuk";
        $optorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sOrg = "select kode,namasubkelompok from " . $dbname . ".log_5subklbarang where kelompok='" . $klmpkBrg . "' order by kode asc ";
        $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
         //exit('eror'.$sOrg);

        while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['kode'] . ">" . $rOrg['namasubkelompok'] . "</option>";
        }
        echo $optorg;
        break;

    case'preview':

        if (($tglDr == '') || ($tanggalSampai == '')) {
            echo"warning: Period not correct";
            exit();
        }
        $tab = "<table cellspacing=1 border=0 class=sortable>
        <thead >
        <tr class=rowheader>
                <th align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
                <th align=center>" . $_SESSION['lang']['namabarang'] . "</th>
                <th align=center>" . $_SESSION['lang']['nopo'] . "</th>
                <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
                <th align=center>" . $_SESSION['lang']['jmlhPesan'] . "</th>
                <th align=center>" . $_SESSION['lang']['matauang'] . "</th>
                <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
                <th align=center>" . $_SESSION['lang']['total'] . "</th>
                <th align=center>" . $_SESSION['lang']['namasupplier'] . "</th>
                <th align=center>" . $_SESSION['lang']['nopp'] . "</th>
                <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>                    
                <th align=center>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['prmntaanPembelian'] . " </th>


        </tr>
        </thead>
        <tbody>";

        $data = array();
        $brs = 1;
		
        $sData = "select distinct kode,kodebarang,namasupplier,namabarang,kurs,nopo,jumlahpesan,hargasatuan,nopp,satuan,tanggal,matauang 
                from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_5subklbarang b on substr(kodebarang,1,5)=kode where statuspo>1 " . $where . " order by tanggal desc";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
         //exit('error'.$sData) ;
		$kdBrng = "";
        while ($rData = $qData->fetch()) {
            $data[] = $rData;
        }
        $no = $grandTotal = 0;
        foreach ($data as $row => $rList) {
            $totHrg = 0;
            if ($rList['kodebarang'] != '') {
                $no+=1;

                if ($rList['matauang'] != 'IDR') {
                    if ($rList['matauang'] != '') {
                        $hrg = $rList['kurs'] * $rList['hargasatuan'];
                        $totHrg = $rList['jumlahpesan'] * $hrg;
                    } else {
                        $totHrg = $rList['jumlahpesan'] * $rList['hargasatuan'];
                        $hrg = $rList['hargasatuan'];
                    }
                } else {
                    $totHrg = $rList['jumlahpesan'] * $rList['hargasatuan'];
                }

                $grandTotal+=$totHrg;
                if ($rList['nopp'] != "") {
                    if (((isset($rTglNopp[$rList['nopp']]) ? $rTglNopp[$rList['nopp']] : "")  != "") || ((isset($rTglNopp[$rList['nopp']]) ? $rTglNopp[$rList['nopp']] : "") != "000-00-00")) {
                        $tglPP = tanggalnormal(isset($rTglNopp[$rList['nopp']]) ? $rTglNopp[$rList['nopp']] : "");
                    } else {
                        $tglPP = "";
                    }
                } else {
                    $tglPP = "";
                }

                if (isset($klmpkBarang) and $klmpkBarang != substr($rList['kodebarang'], 0, 3)) {
                    $brs = 1;
                }
                if ($brs == 1) {
                    $klmpkBarang = substr($rList['kodebarang'], 0, 3);
                    $tab.="<tr class='rowcontent'>";
                    $tab.="<td><b>" . substr($rList['kodebarang'], 0, 3) . "</b></td><td><b>" . $rKelompok[$klmpkBarang] . "</b></td>";
                    $tab.="<td><b></b></td><td><b></b></td>";
                    $tab.="<td><b></b></td><td><b></b></td>";
                    $tab.="<td><b></b></td><td><b></b></td>";
                    $tab.="<td><b></b></td><td><b></b></td>";
                    $tab.="<td><b></b></td><td><b></b></td>";
                    $tab.="</tr>";
                    $brs = 0;
                }
                //ambil jumlah minta di pp
                $sJmlh = "select distinct jumlah,keterangan from " . $dbname . ".log_prapodt 
                                    where nopp='" . $rList['nopp'] . "' and kodebarang='" . $rList['kodebarang'] . "'";
				$qJmlh=$owlPDO->query($sJmlh) or die(print " Gagal: ".PDOException::getMessage());
				$qJmlh->setFetchMode(PDO::FETCH_ASSOC);
                $rJmlh = $qJmlh->fetch();
                $tab.="<tr class='rowcontent'>";
                $tab.="<td>" . $rList['kodebarang'] . "</td>";
                $tab.="<td>" . $rList['namabarang'] . "</td>";
                $tab.="<td>" . $rList['nopo'] . "</td>";
                $tab.="<td>" . tanggalnormal($rList['tanggal']) . "</td>";
                $tab.="<td align=center>" . $rList['jumlahpesan'] . "</td>";
                $tab.="<td align=center>" . $rList['matauang'] . "</td>";
                $tab.="<td align=right>" . number_format($rList['hargasatuan'], 2) . "</td>";
                $tab.="<td align=right>" . number_format($totHrg, 2) . "</td>";
                $tab.="<td>" . $rList['namasupplier'] . "</td>";
                $tab.="<td>" . $rList['nopp'] . "</td>";
                $tab.="<td>" . $rJmlh['keterangan'] . "</td>";
                $tab.="<td align=center>" . $tglPP . "</td>";

                $tab.="</tr>";

                //}	
            }
        }
        $tab .= "<tr class='rowcontent'>";
        $tab .= "<td colspan='7' align='right'><b>TOTAL </b></td>";
        $tab .= "<td align=right>" . number_format($grandTotal, 2) . "</td>";
        $tab .= "<td colspan='4' >&nbsp;</td>";
        $tab .= "</tr>";
        $tab.="</tbody></table>";
        echo $tab;
        break;
    case'pdf':
        if (($tglDr == '') || ($tanggalSampai == '')) {
            echo"warning: Period is obligatory";
            exit();
        }

        class PDF extends FPDF {

            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                global $klmpkBrg;
                global $kdBrg;
                global $tglDr;
                global $tanggalSampai;
                global $where;
                global $isi;
                global $rNamaBarang;
                global $rNamaSupplier;
                global $where;
                global $owlPDO;

                $isi = array();

                # Alamat & No Telp
				$arrHead = setheadreport('',substr($_SESSION['org']['kodeorganisasi'],0,4));
				
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $path=$arrHead['logo'];
                $this->Image($path,$this->lMargin,($this->tMargin-12),0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(110);   
                $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
                $this->SetX(110); 		
                $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
                $this->SetX(110); 			
                $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
				
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 11);
                $this->Cell($width, $height, $_SESSION['lang']['detPembBrg'], 0, 1, 'C');
                $this->SetFont('Arial', '', 8);
                $this->Cell($width, $height, "Periode : " . $_GET['tglDr'] . " s.d. " . $_GET['tanggalSampai'], 0, 1, 'C');
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 7);
                $this->SetFillColor(220, 220, 220);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['kodebarang'], 1, 0, 'C', 1);
                $this->Cell(18 / 100 * $width, $height, $_SESSION['lang']['namabarang'], 1, 0, 'C', 1);
                $this->Cell(14 / 100 * $width, $height, $_SESSION['lang']['nopo'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['tanggal'], 1, 0, 'C', 1);
                $this->Cell(5 / 100 * $width, $height, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
                $this->Cell(5 / 100 * $width, $height, $_SESSION['lang']['satuan'], 1, 0, 'C', 1);
                $this->Cell(5 / 100 * $width, $height, $_SESSION['lang']['matauang'], 1, 0, 'C', 1);
                $this->Cell(7 / 100 * $width, $height, $_SESSION['lang']['hargasatuan'], 1, 0, 'C', 1);
                $this->Cell(7 / 100 * $width, $height, $_SESSION['lang']['total'], 1, 0, 'C', 1);
                $this->Cell(18 / 100 * $width, $height, $_SESSION['lang']['namasupplier'], 1, 1, 'C', 1);
                //$this->Cell(12/100*$width,$height,$_SESSION['lang']['nopp'],1,0,'C',1);	
                //$this->Cell(6/100*$width,$height,$_SESSION['lang']['tanggal']." PP",1,1,'C',1);					
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
            }

        }

        $pdf = new PDF('L', 'pt', 'A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 11;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 7);
        $sData = "select distinct kode,kodebarang,namasupplier,namabarang,kurs,nopo,jumlahpesan,hargasatuan,nopp,satuan,tanggal,matauang 
                from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_5subklbarang b on substr(kodebarang,1,5)=kode where statuspo>1 " . $where . " order by kodebarang asc";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		$kdBrng = "";
        while ($rData = $qData->fetch()) {
            $data[] = $rData;
        }
        $totalAll = array();
        $grandTot = array('total' => 0);
        foreach ($data as $test => $dt) {

            if ($dt['kodebarang'] != '') {

                if ($dt['matauang'] != 'IDR') {

                    $hrg = $dt['kurs'] * $dt['hargasatuan'];
                    $totHrg = $dt['jumlahpesan'] * $hrg;
                } else {
                    $totHrg = $dt['jumlahpesan'] * $dt['hargasatuan'];
                }
                //$totHrg=$rList['jumlahpesan']*$rList['hargasatuan'];
                $grandTot['total']+=$totHrg;
                if ($dt['nopp'] != "") {
                    if (((isset($rTglNopp[$dt['nopp']]) ? $rTglNopp[$dt['nopp']] : "") != "") || ((isset($rTglNopp[$dt['nopp']]) ? $rTglNopp[$dt['nopp']] :"") != "000-00-00")) {
                        $tglPP = tanggalnormal(isset($rTglNopp[$dt['nopp']]) ? $rTglNopp[$dt['nopp']] : "");
                    } else {
                        $tglPP = "";
                    }
                } else {
                    $tglPP = "";
                }



                if (!isset($klmpkBarang) or $klmpkBarang != substr($dt['kodebarang'], 0, 3)) {
                    $brs = 1;
                }
                if ($brs == 1) {
                    $pdf->SetFont('Arial', 'B', 8);
                    $klmpkBarang = substr($dt['kodebarang'], 0, 3);
                    $pdf->Cell(6 / 100 * $width, $height, substr($dt['kodebarang'], 0, 3), 'TBLR', 0, 'C', 1);
                    $pdf->Cell(18 / 100 * $width, $height, $rKelompok[$klmpkBarang], 'TBLR', 0, 'L', 1);
                    $pdf->Cell(14 / 100 * $width, $height, '', 'TBLR', 0, 'L', 1);
                    $pdf->Cell(6 / 100 * $width, $height, '', 1, 0, 'C', 1);
                    $pdf->Cell(5 / 100 * $width, $height, '', 1, 0, 'R', 1);
                    $pdf->Cell(5 / 100 * $width, $height, '', 1, 0, 'C', 1);
                    $pdf->Cell(5 / 100 * $width, $height, '', 1, 0, 'C', 1);
                    $pdf->Cell(7 / 100 * $width, $height, '', 1, 0, 'R', 1);
                    $pdf->Cell(7 / 100 * $width, $height,'', 1, 0, 'R', 1);
                    $pdf->Cell(18 / 100 * $width, $height, '', 1, 1, 'L', 1);
                    //$pdf->Cell(67 / 100 * $width, $height, 'aku', 'TBLR', 1, 'C',1);
                    $brs = 0;
                }
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(6 / 100 * $width, $height, $dt['kodebarang'], 1, 0, 'C', 1);
                $pdf->Cell(18 / 100 * $width, $height, $dt['namabarang'], 1, 0, 'L', 1);
                $pdf->Cell(14 / 100 * $width, $height, $dt['nopo'], 1, 0, 'L', 1);
                $pdf->Cell(6 / 100 * $width, $height, tanggalnormal($dt['tanggal']), 1, 0, 'C', 1);
                $pdf->Cell(5 / 100 * $width, $height, $dt['jumlahpesan'], 1, 0, 'R', 1);
                $pdf->Cell(5 / 100 * $width, $height, $dt['satuan'], 1, 0, 'C', 1);
                $pdf->Cell(5 / 100 * $width, $height, $dt['matauang'], 1, 0, 'C', 1);
                $pdf->Cell(7 / 100 * $width, $height, number_format($dt['hargasatuan'], 2), 1, 0, 'R', 1);
                $pdf->Cell(7 / 100 * $width, $height, number_format($totHrg, 2), 1, 0, 'R', 1);
                $pdf->Cell(18 / 100 * $width, $height, $dt['namasupplier'], 1, 1, 'L', 1);
                //$pdf->Cell(12/100*$width,$height,$dt['nopp'],1,0,'L',1);	
                //$pdf->Cell(6/100*$width,$height,$tglPP,1,1,'C',1);	
            }
        }


        $pdf->Output();
        break;
    case'excel':

        $tab = "
        <table>
    <tr><td colspan=10 align=center>" . $_SESSION['lang']['detPembBrg'] . "</td></tr>
    <tr><td colspan=10 align=center>Periode : " . $_GET['tglDr'] . " s.d. " . $_GET['tanggalSampai'] . "</td></tr>
    </table>";

        $tab.="<table cellspacing=1 border=1 class=sortable>
        <thead >
        <tr class=rowheader>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['namabarang'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['nopo'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['tanggal'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['jmlhPesan'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['matauang'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['hargasatuan'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['total'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['nopp'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['keterangan'] . "</td>
                <td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['prmntaanPembelian'] . " </td>

        </tr>
        </thead>
        <tbody>";

        $data = array();
        $sData = "select distinct kode,kodebarang,namasupplier,namabarang,kurs,nopo,jumlahpesan,hargasatuan,nopp,satuan,tanggal,matauang 
                from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_5subklbarang b on substr(kodebarang,1,5)=kode where statuspo>1 " . $where . " order by kodebarang asc";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		$kdBrng = "";
        while ($rData = $qData->fetch()) {
            $data[] = $rData;
        }
        $no = $grandTotal = 0;
        foreach ($data as $row => $dt) {
            if ($dt['kodebarang'] != '') {
                $no+=1;


                if ($dt['matauang'] != 'IDR') {

                    $hrg = $dt['kurs'] * $dt['hargasatuan'];
                    $totHrg = $dt['jumlahpesan'] * $hrg;
                } else {
                    $totHrg = $dt['jumlahpesan'] * $dt['hargasatuan'];
                }
                $grandTotal+=$totHrg;
                if ($dt['nopp'] != "") {
                    if (((isset($rTglNopp[$dt['nopp']]) ? $rTglNopp[$dt['nopp']] : "") != "") || ((isset($rTglNopp[$dt['nopp']]) ? $rTglNopp[$dt['nopp']] : "") != "000-00-00")) {
                        $tglPP = isset($rTglNopp[$dt['nopp']]) ? $rTglNopp[$dt['nopp']] : "";
                    } else {
                        $tglPP = "";
                    }
                } else {
                    $tglPP = "";
                }

                if (!isset($klmpkBarang) or $klmpkBarang != substr($dt['kodebarang'], 0, 3)) {
                    $brs = 1;
                }
                if ($brs == 1) {
                    $klmpkBarang = substr($dt['kodebarang'], 0, 3);
                    $tab.="<tr class='rowcontent'>";
                    $tab.="<td><b>" . substr($dt['kodebarang'], 0, 3) . "</b></td><td><b>" . $rKelompok[$klmpkBarang] . "</b></td>";
                    $tab.="<td colspan=9>&nbsp;</td>";
                    $tab.="</tr>";
                    $brs = 0;
                }
                //ambil jumlah minta di pp
                $sJmlh = "select distinct jumlah,keterangan  from " . $dbname . ".log_prapodt 
                                    where nopp='" . $dt['nopp'] . "' and kodebarang='" . $dt['kodebarang'] . "'";
				$qJmlh=$owlPDO->query($sJmlh) or die(print " Gagal: ".PDOException::getMessage());
				$qJmlh->setFetchMode(PDO::FETCH_ASSOC);
                $rJmlh = $qJmlh->fetch();

                $tab.="<tr class='rowcontent'>";
                $tab.="<td>" . $dt['kodebarang'] . "</td>";
                $tab.="<td>" . $dt['namabarang'] . "</td>";
                $tab.="<td>" . $dt['nopo'] . "</td>";
                $tab.="<td>" . $dt['tanggal'] . "</td>";
                $tab.="<td align=center>" . $dt['jumlahpesan'] . "</td>";
                $tab.="<td align=center>" . $dt['matauang'] . "</td>";
                $tab.="<td align=right>" . number_format($dt['hargasatuan'], 2) . "</td>";
                $tab.="<td align=right>" . number_format($totHrg, 2) . "</td>";
                $tab.="<td>" . $dt['namasupplier'] . "</td>";
                $tab.="<td>" . $dt['nopp'] . "</td>";
                $tab.="<td>" . $rJmlh['keterangan'] . "</td>";
                $tab.="<td>" . $tglPP . "</td>";

                $tab.="</tr>";

                //}	
            }
        }
        $tab .= "<tr class='rowcontent'>";
        $tab .= "<td colspan='7' align='right'><b>Sub Total </b></td>";
        $tab .= "<td align=right>" . number_format($grandTotal, 0) . "</td>";
        $tab .= "<td colspan='4' >&nbsp;</td>";
        $tab .= "</tr>";
        $tab.="</tbody></table>";


        //echo "warning:".$strx;
        //=================================================


        $tab.="</table>Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $thisDate = date("YmdHms");
        //$nop_="Laporan_Pembelian";
        $nop_ = "Laporan_Pembelian_Brg_" . $thisDate;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls.gz';
                            </script>";
        /* if(strlen($tab)>0)
          {
          if ($handle = opendir('tempExcel')) {
          while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != ".." && $file != "index.html") {
          @unlink('tempExcel/'.$file);
          }
          }
          closedir($handle);
          }
          $handle=fopen("tempExcel/".$nop_.".xls",'w');
          if(!fwrite($handle,$tab))
          {
          echo "<script language=javascript1.2>
          parent.window.alert('Can't convert to excel format');
          </script>";
          exit;
          }
          else
          {
          echo "<script language=javascript1.2>
          window.location='tempExcel/".$nop_.".xls';
          </script>";
          }
          closedir($handle);
          } */
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
    case'getBarang':
        $tab = "<fieldset><legend>" . $_SESSION['lang']['result'] . "</legend>
                        <div style=\"overflow:auto;max-height:295px;\">
                        <table cellpading=1 border=0 class=sortable>
                        <thead>
                        <tr class=rowheader>
                        <td align=center>No.</td>
                        <td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
                        <td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
                        <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
                        </tr><tbody>
                        ";
        $sLoad = "select kodebarang,namabarang,satuan from " . $dbname . ".log_5masterbarang where  kelompokbarang='" . $klmpkBrg . "' and (kodebarang like '%" . $nmBrg . "%'
            or namabarang like '%" . $nmBrg . "%')";
        $qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
		$qLoad->setFetchMode(PDO::FETCH_ASSOC);
		while ($res = $qLoad->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent style=cursor:pointer onclick=\"setData('" . $res['kodebarang'] . "')\">";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td>" . $res['kodebarang'] . "</td>";
            $tab.="<td>" . $res['namabarang'] . "</td>";
            $tab.="<td>" . $res['satuan'] . "</td>";
            $tab.="</tr>";
        }
        echo $tab;

        break;

    default:
        break;
}
?>