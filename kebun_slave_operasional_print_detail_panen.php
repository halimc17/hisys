<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$param = $_GET;
if (!empty($_GET)) {
    $param = $_GET;
} else {
    $param = $_POST;
}
$proses = $param['proses'];
$tipe = $param['tipe'];

$notran = $param['notransaksi'];

/** Report Prep **/
$cols = array();

# Prestasi
//$col1 = 'nik,kodekegiatan,kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$col1 = 'tanggal,nik,a.kodeorg,hasilkerja,jumlahhk,upahkerja,upahpenalty,upahpremi,premibasis,rupiahpenalty,luaspanen';
$cols[] = explode(',', $col1);
//$query = selectQuery($dbname,'kebun_prestasi',$col1,
//    "notransaksi='".$param['notransaksi']."'");
$query = "select " . $col1 . " from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi='" . $param['notransaksi'] . "'";
//exit("Error".$query);
$data[] = fetchData($query);
$align[] = explode(",", "L,L,L,R,R,R,R,R");
$length[] = explode(",", "10,10,15,10,10,15,15,15");



//getNamakaryawan
$sDtKaryawn = "select karyawanid,namakaryawan from " . $dbname . ".datakaryawan order by namakaryawan asc";
$rData = fetchData($sDtKaryawn);
foreach ($rData as $brKary => $rNamakaryawan) {
    $RnamaKary[$rNamakaryawan['karyawanid']] = $rNamakaryawan['namakaryawan'];
}



$sOrg = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi order by namaorganisasi asc";
//exit("Error".$sOrg);
$rDataOrg = fetchData($sOrg);
foreach ($rDataOrg as $brOrg => $rNamaOrg) {
    $rNmOrg[$rNamaOrg['kodeorganisasi']] = $rNamaOrg['namaorganisasi'];
}
switch ($tipe) {
    case "LC":
        $title = strtoupper("Land Clearing");
        break;
    case "BBT":
        $title = strtoupper($_SESSION['lang']['pembibitan']);
        break;
    case "TBM":
        $title = strtoupper("UPKEEP-" . $_SESSION['lang']['tbm']);
        break;
    case "TM":
        $title = strtoupper("UPKEEP-" . $_SESSION['lang']['tm']);
        break;
    case "PNN":
        $title = strtoupper($_SESSION['lang']['panen']);
        break;
    case "TB":
        $title = strtoupper("UPKEEP-" . $_SESSION['lang']['tbm']);
        break;
    default:
        echo "Error : Atribut not Defined";
        exit;
        break;
}
$titleDetail = array($_SESSION['lang']['prestasi'], $_SESSION['lang']['absensi'], $_SESSION['lang']['material']);

// Init Total
$totJanjang = $totUpahKerja = $totUpahKerjapenalty = $totUpahPremi = 0;
$totUpahPremibasis = $totUpahDenda = $totLuas = $totSisa = 0;

/** Output Format **/
switch ($proses) {
    case 'pdf':

        $pdf = new zPdfMaster('P', 'pt', 'A4');
        $pdf->_noThead = true;
        $pdf->setAttr1($title, $align, $length, array());
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
        $pdf->AddPage();
        $pdf->Ln();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($width, $height, $_SESSION['lang']['notransaksi'] . " : " . $param['notransaksi'], 0, 1, 'L', 1);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 8);
        $getX = $pdf->GetX();
        $getY = $pdf->GetY();
        $pdf->MultiCell(10 / 100 * $width, $height * 3, $_SESSION['lang']['tanggal'], 1, 'C', 1);
        $pdf->SetXY($getX + (10 / 100 * $width), $getY);
        $getX = $pdf->GetX();
        $pdf->MultiCell(15 / 100 * $width, $height * 3, $_SESSION['lang']['namakaryawan'], 1, 'C', 1);
        $pdf->SetXY($getX + (15 / 100 * $width), $getY);
        $getX = $pdf->GetX();
        $pdf->MultiCell(13 / 100 * $width, $height * 3, $_SESSION['lang']['kodeorg'], 1, 'C', 1);
        $pdf->SetXY($getX + (13 / 100 * $width), $getY);
        $getX = $pdf->GetX();
        $pdf->MultiCell(5 / 100 * $width, $height * 3, $_SESSION['lang']['jjg'], 1, 'C', 1);
        $pdf->SetXY($getX + (5 / 100 * $width), $getY);
        $getX = $pdf->GetX();
        $pdf->MultiCell(6 / 100 * $width, $height * 3, $_SESSION['lang']['luas'], 1, 'C', 1);
        $pdf->SetXY($getX + (6 / 100 * $width), $getY);
        $getX = $pdf->GetX();
        $pdf->MultiCell(8 / 100 * $width, $height + 6, $_SESSION['lang']['upahkerja'], 1, 'C', 1);
        $pdf->SetXY($getX + (8 / 100 * $width), $getY);
        $getX = $pdf->GetX();
        $pdf->MultiCell(8 / 100 * $width, $height, $_SESSION['lang']['upahpenalty'], 1, 'C', 1);
        $pdf->SetXY($getX + (8 / 100 * $width), $getY);
        $getX = $pdf->GetX();
        $pdf->MultiCell(8 / 100 * $width, $height + 6, $_SESSION['lang']['premibasis'], 1, 'C', 1);
        $pdf->SetXY($getX + (8 / 100 * $width), $getY);
        $getX = $pdf->GetX();
        $pdf->MultiCell(8 / 100 * $width, $height + 6, $_SESSION['lang']['upahpremi'], 1, 'C', 1);
        $pdf->SetXY($getX + (8 / 100 * $width), $getY);
        $getX = $pdf->GetX();
        $pdf->MultiCell(8 / 100 * $width, $height + 6, $_SESSION['lang']['rupiahpenalty'], 1, 'C', 1);
        $pdf->SetXY($getX + (8 / 100 * $width), $getY);
        $getX = $pdf->GetX();
        $pdf->MultiCell(8 / 100 * $width, $height * 3, $_SESSION['lang']['total'], 1, 'C', 1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 8);
        $qData = $owlPDO->query($query) or die(print " Gagal: " . PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        while ($rData = $qData->fetch()) {
            $pdf->Cell(10 / 100 * $width, $height, tanggalnormal($rData['tanggal']), 1, 0, 'C', 1);
            $pdf->Cell(15 / 100 * $width, $height, $RnamaKary[$rData['nik']], 1, 0, 'L', 1);
            $pdf->Cell(13 / 100 * $width, $height, getNamaOrg($rData['kodeorg']), 1, 0, 'C', 1);
            $pdf->Cell(5 / 100 * $width, $height, $rData['hasilkerja'], 1, 0, 'R', 1);
            $pdf->Cell(6 / 100 * $width, $height, number_format($rData['luaspanen'], 2), 1, 0, 'R', 1);
            $pdf->Cell(8 / 100 * $width, $height, number_format($rData['upahkerja'], 0), 1, 0, 'R', 1);
            $pdf->Cell(8 / 100 * $width, $height, number_format($rData['upahpenalty'], 0), 1, 0, 'R', 1);
            $pdf->Cell(8 / 100 * $width, $height, number_format($rData['premibasis'], 0), 1, 0, 'R', 1);
            $pdf->Cell(8 / 100 * $width, $height, number_format($rData['upahpremi'], 0), 1, 0, 'R', 1);
            $pdf->Cell(8 / 100 * $width, $height, number_format($rData['rupiahpenalty'], 0), 1, 0, 'R', 1);
            $sisa = $rData['upahkerja'] - $rData['upahpenalty'] + $rData['premibasis'] + $rData['upahpremi'] - $rData['rupiahpenalty'];
            $pdf->Cell(8 / 100 * $width, $height, number_format($sisa, 0), 1, 1, 'R', 1);
            $totJanjang += $rData['hasilkerja'];
            $totUpahKerja += $rData['upahkerja'];
            $totUpahKerjapenalty += $rData['upahpenalty'];
            $totUpahPremi += $rData['upahpremi'];
            $totUpahPremibasis += $rData['premibasis'];
            $totUpahDenda += $rData['rupiahpenalty'];
            $totLuas += $rData['luaspanen'];
            $totSisa += $sisa;
        }
        $pdf->Cell(38 / 100 * $width, $height, $_SESSION['lang']['total'], 1, 0, 'C', 1);
        $pdf->Cell(5 / 100 * $width, $height, number_format($totJanjang, 0), 1, 0, 'R', 1);
        $pdf->Cell(6 / 100 * $width, $height, number_format($totLuas, 2), 1, 0, 'R', 1);
        $pdf->Cell(8 / 100 * $width, $height, number_format($totUpahKerja, 0), 1, 0, 'R', 1);
        $pdf->Cell(8 / 100 * $width, $height, number_format($totUpahKerjapenalty, 0), 1, 0, 'R', 1);
        $pdf->Cell(8 / 100 * $width, $height, number_format($totUpahPremibasis, 0), 1, 0, 'R', 1);
        $pdf->Cell(8 / 100 * $width, $height, number_format($totUpahPremi, 0), 1, 0, 'R', 1);
        $pdf->Cell(8 / 100 * $width, $height, number_format($totUpahDenda, 0), 1, 0, 'R', 1);
        $pdf->Cell(8 / 100 * $width, $height, number_format($totSisa, 0), 1, 1, 'R', 1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 8);
        $sAsis = "select distinct nikmandor,nikmandor1,nikasisten,keranimuat,tanggal,kodeorg from " . $dbname . ".kebun_aktifitas where notransaksi='" . $param['notransaksi'] . "'";
        $qAsis = $owlPDO->query($sAsis) or die(print " Gagal: " . PDOException::getMessage());
        $qAsis->setFetchMode(PDO::FETCH_ASSOC);
        $rAsis = $qAsis->fetch();
        setIt($RnamaKary[$rAsis['nikasisten']], '');
        setIt($RnamaKary[$rAsis['nikmandor1']], '');
        setIt($RnamaKary[$rAsis['nikmandor']], '');
        $pdf->ln(10);
        $pdf->Cell(85 / 100 * $width, $height, $rAsis['kodeorg'] . "," . tanggalnormal($rAsis['tanggal']), 0, 1, 'R', 0);
        $pdf->ln(35);
        $pdf->Cell(28 / 100 * $width, $height, $_SESSION['lang']['dbuat_oleh'], 0, 0, 'C', 0);
        $pdf->Cell(29 / 100 * $width, $height, $_SESSION['lang']['diperiksa'], 0, 0, 'C', 0);
        $pdf->Cell(28 / 100 * $width, $height, $_SESSION['lang']['disetujui'], 0, 1, 'C', 0);
        $pdf->ln(65);
        $pdf->SetFont('Arial', 'U', 8);
        $pdf->Cell(28 / 100 * $width, $height, $RnamaKary[$rAsis['nikasisten']], 0, 0, 'C', 0);
        $pdf->Cell(29 / 100 * $width, $height, $RnamaKary[$rAsis['nikmandor']], 0, 0, 'C', 0);
        $pdf->Cell(28 / 100 * $width, $height, $RnamaKary[$rAsis['nikmandor1']], 0, 1, 'C', 0);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(28 / 100 * $width, $height, $_SESSION['lang']['kerani'], 0, 0, 'C', 0);
        $pdf->Cell(29 / 100 * $width, $height, $_SESSION['lang']['mandor'], 0, 0, 'C', 0);
        $pdf->Cell(28 / 100 * $width, $height, $_SESSION['lang']['nikmandor1'], 0, 1, 'C', 0);
        $pdf->Output();
        break;

    case 'html':

        $theme = $_SESSION['theme'];
        if ($theme == 'skyblue' || $theme == '') {
            $men = 'menu.css';
            $gen = 'generic.css';
        } else if ($theme == 'red') {
            $men = 'menuRed.css';
            $gen = 'genericRed.css';
        } else {
            $men = 'menuGray.css';
            $gen = 'genericGray.css';
        }

        $arrJenisPanen = array("0" => "Normal", "1" => "Panen HA");

        $tab = "<link rel=stylesheet type=text/css href=style/" . $gen . ">";
        //$tab.="<fieldset><legend>".$title."</legend>";
        $tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable><tbody class=rowcontent>";
        $tab .= "<tr><td>" . $_SESSION['lang']['kodeorganisasi'] . "</td><td> :</td><td> " . $_SESSION['empl']['lokasitugas'] . "</td></tr>";
        $tab .= "<tr><td>" . $_SESSION['lang']['notransaksi'] . "</td><td> :</td><td> " . $param['notransaksi'] . "</td></tr>";
        $tab .= "<tr><td>Jenis Panen</td><td> :</td><td> " . $arrJenisPanen[$param['jenispanen']] . "</td></tr>";
        $tab .= "</tbody></table>";
        $tab .= "<br />" . $titleDetail[0] . "<br />";
        $tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
        $tab .= "<tr class=rowheader>";
        $tab .= "<th align=center>No</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>";
        $tab .= "<th  align=center>" . $_SESSION['lang']['nik'] . "</th>";
        $tab .= "<th  align=center>" . $_SESSION['lang']['nama'] . "</th>";
        $tab .= "<th  align=center>" . $_SESSION['lang']['blok'] . "</th>";
        $tab .= "<th  align=center>" . $_SESSION['lang']['nospb'] . "</th>";
        $tab .= "<th  align=center>" . $_SESSION['lang']['hasilkerja'] . "</th>";
        $tab .= "<th  align=center>" . $_SESSION['lang']['luas'] . "</th>";
        $tab .= "<th  align=center>" . $_SESSION['lang']['brondolan'] . "</th>";
        $tab .= "<th  align=center>" . $_SESSION['lang']['upahkerja'] . "</th>";
        $tab .= "<th  align=center>" . $_SESSION['lang']['upahpenalty'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['premibasis'] . " (Rp)</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['premlebihbasis'] . " (Rp)</th>";
        $tab .= "<th align=center>Total " . $_SESSION['lang']['upahpremi'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['rupiahpenalty'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['total'] . "</th>";
        $tab .= "<td align=center>" . $_SESSION['lang']['photo'] . " Awal</td>";
        $tab .= "<td align=center>" . $_SESSION['lang']['photo'] . " Akhir</td>";
        $tab .= "</tr></thead><tbody>";

        $str = "select * from " . $dbname . ".kebun_prestasi_vw where notransaksi='" . $param['notransaksi'] . "' order by karyawanid asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $no = '';
        while ($bar = $res->fetch()) {
            $no++;
            $bgcolor = $title = $color = '';
            $strx = "select count(nik) as jmlkary, nik from " . $dbname . ".kebun_prestasi where notransaksi='" . $bar['notransaksi'] . "' and nik='" . $bar['karyawanid'] . "' group by nik";
            $resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
            $resx->setFetchMode(PDO::FETCH_ASSOC);
            $barx = $resx->fetch();
            if (($bar['karyawanid'] == $barx['nik']) and ($barx['jmlkary'] > 1)) {
                $bgcolor = "style=background-color:orange;";
                $title = " title = 'Karyawan Panen lebih dari 1 blok !'";
            }
            $tab .= "<tr class=rowcontent " . $bgcolor . " " . $title . ">";
            $tab .= "<td align=center>" . $no . "</td>";
            $tab .= "<td>" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab .= "<td>" . getKary($bar['karyawanid'], 'nik') . "</td>";
            $tab .= "<td>" . $RnamaKary[$bar['karyawanid']] . "</td>";
            $tab .= "<td>" . getNamaOrg($bar['kodeorg']) . "</td>";
            $tab .= "<td align=center>" . $bar['nospb'] . "</td>";
            $tab .= "<td align=right>" . $bar['hasilkerja'] . "</td>";
            $tab .= "<td align=right>" . number_format($bar['luaspanen'], 2) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['brondolan'], 0) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['upahkerja'], 0) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['upahpenalty'], 0) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['upahpremi'], 0) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['upahpremilebihbasis'], 0) . "</td>";
            $totPremi = $bar['upahpremi'] + $bar['upahpremilebihbasis'];
            $tab .= "<td align=right>" . number_format($totPremi, 0) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['rupiahpenalty'], 0) . "</td>";
            $sisa = ($bar['upahkerja'] - $bar['upahpenalty']) + ($totPremi - $bar['rupiahpenalty']);
            $tab .= "<td align=right>" . number_format($sisa, 0) . "</td>";
            $tab .= "<td align=right>
                        <a href='" . $bar['photo'] . "' class='popup-img'>
                            <img onclick=\"popupimage()\" class='resiconn' style='height:50px;width:50px;' src='" . $bar['photo'] . "'>
                        </a>
                    </td>";
            $tab .= "<td align=right>
                        <a href='" . $bar['photo2'] . "' class='popup-img'>
                            <img onclick=\"popupimage()\" class='resiconn' style='height:50px;width:50px;' src='" . $bar['photo2'] . "'>
                        </a>
                    </td>";
            $tab .= "</tr>";
            @$totJanjang += $bar['hasilkerja'];
            @$totLuas += $bar['luaspanen'];
            @$totUpahKerja += $bar['upahkerja'];
            @$totUpahKerjapenalty += $bar['upahpenalty'];
            @$totUpahPremi += $bar['upahpremi'];
            @$totUpahPremiLebihBasis += $bar['upahpremilebihbasis'];
            @$totPremiAll += $totPremi;
            @$totUpahDenda += $bar['rupiahpenalty'];
            @$totbrondolan += $bar['brondolan'];
            @$totSisa += $sisa;
        }
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td colspan=6 align=center>" . $_SESSION['lang']['total'] . "</td>";
        $tab .= "<td align=right>" . number_format($totJanjang, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totLuas, 2) . "</td>";
        $tab .= "<td align=right>" . number_format($totbrondolan, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totUpahKerja, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totUpahKerjapenalty, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totUpahPremi, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totUpahPremiLebihBasis, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totPremiAll, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totUpahDenda, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totSisa, 0) . "</td>";
        $tab .= "<td colspan=2></td>";
        $tab .= "</tr></tbody></table>";

        $tab .= "<br><label>File Upload</label>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=30px colspan=2>Action</td>
				</tr>
				</thead>
				<tbody>";
        $path = "fileupload/bkm/";
        $str = "select * from " . $dbname . ".listfileupload where notransaksi = '" . $param['notransaksi'] . "' and status='1'";
        $res = fetchData($str);
        if (empty($res)) {
            $tab .= "<tr class=rowcontent><td colspan=5 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
        } else {
            $no = 0;
            foreach ($res as $key => $val) {
                $no++;
                $tab .= "<tr class=rowcontent>
							<td style='text-align:center'>" . $no . "</td>";
                $icon = seticonfile($val['formaticon']);
                $tab .= "<td style='text-align:center'>
							<a href='" . $path . $val['namafile'] . "' download><img src=" . $icon . " class=resicon></a>
						</td>";
                $tab .= "<td style='text-align:left;cursor:pointer' onclick=\"viewfile('" . $val['id'] . "')\">" . $val['namafile'] . "</td>";

                $tab .= "<td align=center width=30px colspan=2><a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon title='download'></a></td>";
                $tab .= "</tr>";
            }
        }
        $tab .= "</tbody>
			</table>
		";

        echo $tab;
        break;

    case 'excel':

        //$tab="<link rel=stylesheet type=text/css href=style/generic.css>";
        //$tab.="<fieldset><legend>".$title."</legend>";
        $tab .= "<table border=1 cellpadding=5 cellspacing=1 class=sortable><tbody class=rowcontent>";
        $tab .= "<tr><td bgcolor=#CCCCCC>" . $_SESSION['lang']['kodeorganisasi'] . "</td><td> :</td><td> " . $_SESSION['empl']['lokasitugas'] . "</td></tr>";
        $tab .= "<tr><td bgcolor=#CCCCCC>" . $_SESSION['lang']['notransaksi'] . "</td><td> :</td><td> " . $param['notransaksi'] . "</td></tr>";
        $tab .= "</tbody></table>";
        $tab .= "<br />" . $titleDetail[0] . "<br />";
        $tab .= "<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
        $tab .= "<tr class=rowheader>";
        $tab .= "<td bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['nik'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC  align=center>" . $_SESSION['lang']['blok'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['hasilkerja'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['brondolan'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['luas'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['upahkerja'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['upahpenalty'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['premibasis'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['premlebihbasis'] . " (Rp)</td>";
        $tab .= "<td bgcolor=#CCCCCC align=center>Total " . $_SESSION['lang']['upahpremi'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['rupiahpenalty'] . "</td>";
        $tab .= "<td bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['total'] . "</td>";
        $tab .= "</tr></thead><tbody>";


        $str = "select * from " . $dbname . ".kebun_prestasi_vw where notransaksi='" . $param['notransaksi'] . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td>" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab .= "<td>" . $RnamaKary[$bar['karyawanid']] . "</td>";
            $tab .= "<td>" . getNamaOrg($bar['kodeorg']) . "</td>";
            $tab .= "<td align=right>" . $bar['hasilkerja'] . "</td>";
            $tab .= "<td align=right>" . $bar['brondolan'] . "</td>";
            $tab .= "<td align=right>" . number_format($bar['luaspanen'], 2) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['upahkerja'], 0) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['upahpenalty'], 0) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['upahpremi'], 0) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['upahpremilebihbasis'], 0) . "</td>";
            $totPremi = $bar['upahpremi'] + $bar['upahpremilebihbasis'];
            $tab .= "<td align=right>" . number_format($totPremi, 0) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['rupiahpenalty'], 0) . "</td>";
            $sisa = ($bar['upahkerja'] - $bar['upahpenalty']) + ($totPremi - $bar['rupiahpenalty']);
            $tab .= "<td align=right>" . number_format($sisa, 0) . "</td>";
            $tab .= "</tr>";
            @$totJanjang += $bar['hasilkerja'];
            @$totBrondolan += $bar['brondolan'];
            @$totLuas += $bar['luaspanen'];
            @$totUpahKerja += $bar['upahkerja'];
            @$totUpahKerjapenalty += $bar['upahpenalty'];
            @$totUpahPremi += $bar['upahpremi'];
            @$totUpahPremiLebihBasis += $bar['upahpremilebihbasis'];
            @$totPremiAll += $totPremi;
            @$totUpahDenda += $bar['rupiahpenalty'];
            @$totSisa += $sisa;
        }
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td colspan=3 align=center>" . $_SESSION['lang']['total'] . "</td>";
        $tab .= "<td align=right>" . number_format($totJanjang, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totBrondolan, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totLuas, 2) . "</td>";
        $tab .= "<td align=right>" . number_format($totUpahKerja, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totUpahKerjapenalty, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totUpahPremi, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totUpahPremiLebihBasis, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totPremiAll, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totUpahDenda, 0) . "</td>";
        $tab .= "<td align=right>" . number_format($totSisa, 0) . "</td>";
        $tab .= "</tr></tbody></table>";



        $tab .= "Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        //$nop_="PNN:".$param['notransaksi'];
        $nop_ = "Laporan_PNN";
        if (strlen($tab) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
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
    default:

    case 'gettahuntanam':
        $blok = $param['blok'];

        ## Ambil Tahun tanam Blok kecil
        $strx = "select * from " . $dbname . ".setup_blok where kodeorg ='" . $blok . "'";
        $resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
        $resx->setFetchMode(PDO::FETCH_ASSOC);
        while ($barx = $resx->fetch()) {
            $tahuntanam = $barx['tahuntanam'];
        }

        echo $tahuntanam;
        break;

    case 'proporsitahuntanam':
        $flagsimpan = makeOption($dbname, 'kebun_proporsitahuntanam', 'notransaksi,flag');

        if ($flagsimpan[$param['notransaksi']] == '1') {
            $status = "Data Saved";
            $st = "style='text-align:center;background-color:green'";
        } else {
            $status = "Not Saved";
            $st = "style='text-align:center;background-color:red'";
        }


        $tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable><tbody class=rowcontent>";
        $tab .= "<tr><td>" . $_SESSION['lang']['kodeorganisasi'] . "</td><td> :</td><td> " . $_SESSION['empl']['lokasitugas'] . "</td></tr>";
        $tab .= "<tr><td>" . $_SESSION['lang']['notransaksi'] . "</td><td> :</td><td> " . $param['notransaksi'] . "</td></tr>";
        $tab .= "<tr " . $st . "><td>" . $_SESSION['lang']['status'] . "</td><td> :</td><td> " . $status . "</td></tr>";
        $tab .= "</tbody></table><br>";


        $tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
        $tab .= "<tr class=rowheader>";
        $tab .= "<th align=center>" . $_SESSION['lang']['nourut'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['blok'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['nik'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['nama'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['luas'] . " (HA)</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['hasilkerja'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['brondolan'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['blok'] . " Kecil</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['tahuntanam'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['jjg'] . " Proporsi</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['brondolan'] . " Proporsi</th>";
        $tab .= "<th align=center>Banjir</th>";
        $tab .= "<th align=center>Action</th>";
        $tab .= "</tr></thead><tbody>";

        $arrBanjir = array(
            '0' => 'TIDAK',
            '1' => 'YA'
        );

        foreach ($arrBanjir as $row => $val) {
            $optBanjir .= "<option value=" . $row . ">" . $val . "</option>";
        }

        $penampungkary = array();

        $nour = '';
        ## Inputan proporsi panen (DMA)
        $str = "select *,sum(hasilkerja) as jumlahjjg, sum(luaspanen) as jumlahluas, sum(brondolan) as jumlahbrondolan from " . $dbname . ".kebun_prestasi_vw where notransaksi='" . $param['notransaksi'] . "' group by karyawanid,kodeorg order by karyawanid asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $penampungkary[$bar['karyawanid']] = $bar['karyawanid'];

            ## Ambil Blok kecil
            $optBlokkecil = "<option value=''>Pilih Data</option>";
            $strx = "select * from " . $dbname . ".setup_blok where indukblok ='" . $bar['kodeorg'] . "'";
            $resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
            $resx->setFetchMode(PDO::FETCH_ASSOC);
            while ($barx = $resx->fetch()) {
                $optBlokkecil .= "<option value='" . $barx['kodeorg'] . "'>" . $barx['kodeorg'] . "</option>";
            }


            // $optBanjir="<option value='0'>TIDAK</option>";
            // $optBanjir.="<option value='1'>YA</option>";



            $nour++;
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td >" . $nour . "</td>";
            $tab .= "<td>" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab .= "<td >" . getNamaOrg($bar['kodeorg']) . "</td>";
            $tab .= "<td hidden id=inputblok_" . $nour . ">" . getNamaOrg($bar['kodeorg']) . "</td>";
            $tab .= "<td>" . getKary($bar['karyawanid'], 'nik') . "</td>";
            $tab .= "<td hidden id=inputkaryawanid_" . $nour . ">" . $bar['karyawanid'] . "</td>";
            $tab .= "<td>" . $RnamaKary[$bar['karyawanid']] . "</td>";
            $tab .= "<td align=right>" . number_format($bar['jumlahluas'], 2) . "</td>";
            $tab .= "<td align=right>" . $bar['jumlahjjg'] . "</td>";
            $tab .= "<td align=right>" . number_format($bar['jumlahbrondolan'], 2) . "</td>";
            $tab .= "<td align=center><select style=\"width:150px;\"  onchange=\"gettahuntanam(" . $nour . ")\" id=blokkecil_" . $nour . ">" . $optBlokkecil . "</select></td>";
            $tab .= "<td align=center><input disabled id=inputtahuntanam_" . $nour . " maxlength=4 class=myinputtextnumber placeholder='Tahun Tanam'onkeypress=\"return angka_doang(event)\" style=\"width:99%;align:center;\"></td>";
            $tab .= "<td align=center><input id=inputjjgproporsi_" . $nour . " value='0' class=myinputtextnumber placeholder='JJG'onkeypress=\"return angka_doang(event)\" style=\"width:99%;align:center;\"></td>";
            $tab .= "<td align=center><input id=inputbrondolanproporsi_" . $nour . " value='0' class=myinputtextnumber placeholder='Brondolan'onkeypress=\"return angka_doang(event)\" style=\"width:99%;align:center;\"></td>";
            $tab .= "<td align=center><select style=\"width:150px;\" id=banjir_" . $nour . ">" . $optBanjir . "</select></td>";
            if ($bar['jurnal'] == '0') {
                $tab .= "<td align=center><img src='images/plus.png' class='zImgBtn' title='Save'; onclick=addproporsijjg('" . $nour . "','" . $bar['notransaksi'] . "','" . $bar['tanggal'] . "','" . $bar['kodeorg'] . "'," . $bar['karyawanid'] . "); style='position:relative;top:3px;left:3px;'></td>";
            } else {
                $tab .= "<td></td>";
            }
            $tab .= "</tr>";

            $strx = "select * from " . $dbname . ".kebun_proporsitahuntanam where notransaksi='" . $bar['notransaksi'] . "' and tanggal ='" . $bar['tanggal'] . "' and kodeorg='" . $bar['kodeorg'] . "' and karyawanid='" . $bar['karyawanid'] . "'";
            $resx = fetchdata($strx);
            if (count($resx) > 0) {
                $nour2 = 1;
                foreach ($resx as $valx) {
                    $tab .= "<tr style='text-align:center;background-color:#50edd2' class='rowcontent'>";
                    $tab .= "<td colspan =8></td>";
                    $tab .= "<td align=right>" . $valx['blokkecil'] . "</td>";
                    $tab .= "<td align=right>" . $valx['tahuntanam'] . "</td>";
                    $tab .= "<td align=right>" . $valx['jjg'] . "</td>";
                    $tab .= "<td align=right>" . number_format($valx['brondolan'], 2) . "</td>";
                    $tab .= "<td align=center>" . $arrBanjir[$valx['banjir']] . "</td>";
                    if ($bar['jurnal'] == '0') {
                        $tab .= "<td align=center><img src=images/application/application_delete.png class='zImgBtn' title='Delete' onclick=deleteproporsijjg('" . $valx['id'] . "','" . $bar['notransaksi'] . "'); style='position:relative;top:3px;left:3px;'></td>";
                    } else {
                        $tab .= "<td></td>";
                    }
                    $tab .= "</tr>";

                    $ttljjgpro[$bar['notransaksi']][$bar['tanggal']][$bar['kodeorg']][$bar['karyawanid']] += $valx['jjg'];
                    $ttljjgbro[$bar['notransaksi']][$bar['tanggal']][$bar['kodeorg']][$bar['karyawanid']] += $valx['brondolan'];
                }

                $tab .= "<tr style='text-align:center;background-color:cyan' class=rowcontent>";
                $tab .= "<td colspan=10><b>TOTAL</b></td>";
                $tab .= "<td align=center><b>" . number_format($ttljjgpro[$bar['notransaksi']][$bar['tanggal']][$bar['kodeorg']][$bar['karyawanid']], 2) . "</b></td>";
                $tab .= "<td align=center><b>" . number_format($ttljjgbro[$bar['notransaksi']][$bar['tanggal']][$bar['kodeorg']][$bar['karyawanid']], 2) . "</b></td>";
                $tab .= "<td></td>";
                $tab .= "<td></td>";
                $tab .= "</tr>";
            }

            @$totLuasx += $bar['jumlahluas'];
            @$totJanjangx += $bar['jumlahjjg'];
            @$totBrondolx += $bar['jumlahbrondolan'];


            @$totJjgPro += $ttljjgpro[$bar['notransaksi']][$bar['tanggal']][$bar['kodeorg']][$bar['karyawanid']];
            @$totBrdPro += $ttljjgbro[$bar['notransaksi']][$bar['tanggal']][$bar['kodeorg']][$bar['karyawanid']];


            if ($bar['jurnal'] == '1') {
                $hdd = 'hidden';
            } else {
                $hdd = '';
            }
        }

        $sty = '';
        $sty1 = '';
        $notd = '';
        if ($totJanjangx != $totJjgPro) {
            $sty = "style='background:red; color:black;'";
            $notd = "Proporsi tidak sama dengan kegiatan panen";
        }

        if ($totBrondolx != $totBrdPro) {
            $sty1 = "style='background:red; color:black;'";
            $notd = "Proporsi tidak sama dengan kegiatan panen";
        }

        $tab .= "<tr class=rowcontent>";
        $tab .= "<td colspan=5> <b> GRAND TOTAL </b></td>";
        $tab .= "<td align=center ><b>" . number_format($totLuasx, 2) . "</b></td>";
        $tab .= "<td align=center " . $sty . "><b>" . number_format($totJanjangx, 2) . "</b></td>";
        $tab .= "<td align=center " . $sty1 . "><b>" . number_format($totBrondolx, 2) . "</b></td>";
        $tab .= "<td colspan=2></td>";
        $tab .= "<td align=center " . $sty . " ><b>" . number_format($totJjgPro, 2) . "</b></td>";
        $tab .= "<td align=center " . $sty1 . " ><b>" . number_format($totBrdPro, 2) . "</b></td>";
        $tab .= "<td align=center colspan=2>" . $notd . "</td>";
        $tab .= "</tr>";


        ## Di kegiatan Panen tidak ada tapi di proporsi panen sudah diinput
        $nourx = 0;
        $strx = "select p.* from " . $dbname . ".kebun_proporsitahuntanam p
            where p.notransaksi='" . $param['notransaksi'] . "'
            and not exists (
                select 1 from " . $dbname . ".kebun_prestasi d
                where d.notransaksi=p.notransaksi
                and cast(d.nik as unsigned)=p.karyawanid
                and d.kodeorg=p.kodeorg
            )
            order by p.karyawanid,p.kodeorg,p.blokkecil";
        $resx = fetchdata($strx);
        if (count($resx) > 0) {
            $tab .= "<tr class=rowcontent style='background-color:red;'>";
            $tab .= "<td colspan = 14 ><b>Proporsi yang tidak mempunyai detail kegiatan panen (karyawan / blok besar) </b></td>";
            $tab .= "</tr>";
            foreach ($resx as $valx) {
                $nourx++;

                $tab .= "<tr class=rowcontent >";
                $tab .= "<td >" . $nourx . "</td>";
                $tab .= "<td colspan = 12 >" . getNamaKaryawan($valx['karyawanid']) . " - Blok Besar: " . $valx['kodeorg'] . " - Blok Kecil: " . $valx['blokkecil'] . "</td>";
                $tab .= "<td align=center><img src=images/application/application_delete.png class='zImgBtn' title='Delete' onclick=deleteproporsijjg('" . $valx['id'] . "','" . $valx['notransaksi'] . "'); style='position:relative;top:3px;left:3px;'></td>";
                $tab .= "</tr>";
            }
        }

        $tab .= "<tr " . $hdd . " class=rowcontent>";
        $tab .= "<td align=center colspan=14><button id=tomboldetail class=mybutton onclick=saveproporsi('" . $param['notransaksi'] . "')>" . $_SESSION['lang']['save'] . "</button></td>";
        $tab .= "</tr>";


        $tab .= "</tbody></table>";



        echo $tab;
        break;

    case 'addproporsijjg':

        $notransaksi             = $param['notransaksi'];
        $tanggal                 = $param['tanggal'];
        $blok                    = $param['kodeorg'];
        $karyawanid              = $param['karyawanid'];
        $inputjjgproporsi        = $param['inputjjgproporsi'];
        $inputbrondolanproporsi  = $param['inputbrondolanproporsi'];
        $inputtahuntanam         = $param['inputtahuntanam'];
        $inputblokkecil          = $param['inputblokkecil'];
        $banjir                  = $param['banjir'];

        if ($inputjjgproporsi  == '') {
            $inputjjgproporsi = 0;
        }

        if ($inputbrondolanproporsi  == '') {
            $inputbrondolanproporsi = 0;
        }

        $str = "select sum(hasilkerja) as jumlahjjg, sum(luaspanen) as jumlahluas, sum(brondolan) as jumlahbrondolan from " . $dbname . ".kebun_prestasi_vw where notransaksi='" . $notransaksi . "' and tanggal ='" . $tanggal . "' and kodeorg = '" . $blok . "' and karyawanid = '" . $karyawanid . "' group by karyawanid,kodeorg order by karyawanid asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $adaPrestasi = false;
        while ($bar = $res->fetch()) {
            $adaPrestasi = true;
            $totalJanjangpanen = $bar['jumlahjjg'];
            $totalBrondolanpanen = $bar['jumlahbrondolan'];
        }

        if (!$adaPrestasi) {
            exit("Warning : Detail kegiatan panen untuk karyawan dan blok tersebut sudah tidak ada. Silahkan refresh data BKM.");
        }

        if ($inputjjgproporsi >  $totalJanjangpanen) {
            exit("Warning : Jumlah JJG PROPORSI tidak boleh lebih dari JJG PANEN ");
        }

        if ($inputbrondolanproporsi >  $totalBrondolanpanen) {
            exit("Warning : Jumlah BRONDOLAN PROPORSI tidak boleh lebih dari BRONDOLAN PANEN ");
        }

        if ($inputtahuntanam == '') {
            exit("Warning : Tahun tanam wajib diisi ");
        }

        // if($inputjjgproporsi == '' || $inputjjgproporsi == '0'){
        //         exit("Warning : JJG wajib diisi dan harus lebih dari 0 ");
        // }

        $strx = "select * from " . $dbname . ".kebun_proporsitahuntanam where notransaksi='" . $notransaksi . "' and tanggal ='" . $tanggal . "' and kodeorg='" . $blok . "' and karyawanid='" . $karyawanid . "' and tahuntanam = '" . $inputtahuntanam . "'";
        $resx = fetchdata($strx);
        if (count($resx) > 0) {
            exit("Warning : Data sudah ada....");
        }

        $strx = "select sum(jjg) as jumlahjjgpro, sum(brondolan) as jumlahbropro from " . $dbname . ".kebun_proporsitahuntanam where notransaksi='" . $notransaksi . "' and tanggal ='" . $tanggal . "' and kodeorg='" . $blok . "' and karyawanid='" . $karyawanid . "' ";
        $resx = fetchdata($strx);
        foreach ($resx as $valx) {
            $totaljjgpro = $valx['jumlahjjgpro'];
            $totalbropro = $valx['jumlahbropro'];
        }

        if (($inputjjgproporsi + $totaljjgpro) >  $totalJanjangpanen) {
            exit("Warning : Gagal simpan, jumlah JJG PROPORSI yang sudah diinput melebihi JJG PANEN");
        }

        if (($inputbrondolanproporsi + $totalbropro) >  $totalBrondolanpanen) {
            exit("Warning : Gagal simpan, jumlah BRONDOLAN PROPORSI yang sudah diinput melebihi BRONDOLAN PANEN");
        }

        try {
            $owlPDO->beginTransaction();


            $data = array(
                'notransaksi' => $notransaksi,
                'tanggal'     => $tanggal,
                'kodeorg'     => $blok,
                'blokkecil'   => $inputblokkecil,
                'tahuntanam'  => $inputtahuntanam,
                'karyawanid'  => $karyawanid,
                'jjg'         => $inputjjgproporsi,
                'brondolan'   => $inputbrondolanproporsi,
                'banjir'      => $banjir,
                'createby'    => $_SESSION['standard']['userid'],
                'createdate'  => date('Y-m-d H:i:s'),
                'updateby'    => $_SESSION['standard']['userid']
            );


            $cols = array();
            foreach ($data as $key => $row) {
                $cols[] = $key;
            }

            # Insert
            $query = insertQuery($dbname, 'kebun_proporsitahuntanam', $data, $cols);
            $owlPDO->exec($query);

            $str = "update " . $dbname . ".kebun_proporsitahuntanam set flag = '0' where notransaksi = '" . $notransaksi . "'";
            $owlPDO->exec($str);

            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;

    case 'deleteproporsijjg':
        $noid             = $param['noid'];
        $notransaksi      = $param['notransaksi'];

        try {
            $owlPDO->beginTransaction();
            $str = "delete from " . $dbname . ".kebun_proporsitahuntanam where id='" . $noid . "'";
            $owlPDO->exec($str);

            $str = "update " . $dbname . ".kebun_proporsitahuntanam set flag = '0' where notransaksi = '" . $notransaksi . "'";
            $owlPDO->exec($str);

            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;

    case 'saveproporsi':
        $notransaksi             = $param['notransaksi'];

        ## Pastikan header BKM masih ada.
        $str = "select notransaksi from " . $dbname . ".kebun_aktifitas where notransaksi='" . $notransaksi . "' limit 1";
        $resHeader = fetchdata($str);
        if (count($resHeader) == 0) {
            exit("Warning : Header BKM panen sudah tidak ada. Proporsi tidak dapat disimpan.");
        }

        $str = "select * from " . $dbname . ".kebun_prestasi_vw where notransaksi='" . $notransaksi . "' order by karyawanid asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $adaPrestasi = false;
        while ($bar = $res->fetch()) {
            $adaPrestasi = true;
            $totalJanjangpanen += $bar['hasilkerja'];
            $totalBrondolanpanen += $bar['brondolan'];
        }

        if (!$adaPrestasi) {
            exit("Warning : Detail kegiatan panen sudah tidak ada. Proporsi tidak dapat disimpan.");
        }

        $strOrphan = "select p.id from " . $dbname . ".kebun_proporsitahuntanam p
            where p.notransaksi='" . $notransaksi . "'
            and not exists (
                select 1 from " . $dbname . ".kebun_prestasi d
                where d.notransaksi=p.notransaksi
                and cast(d.nik as unsigned)=p.karyawanid
                and d.kodeorg=p.kodeorg
            )";
        $resOrphan = fetchdata($strOrphan);
        if (count($resOrphan) > 0) {
            exit("Warning : Masih ada proporsi yang tidak mempunyai detail kegiatan panen. Hapus data proporsi yang berwarna merah terlebih dahulu.");
        }

        $strx = "select * from " . $dbname . ".kebun_proporsitahuntanam where notransaksi='" . $notransaksi . "'";
        $resx = fetchdata($strx);
        foreach ($resx as $valx) {
            $totaljjgpro += $valx['jjg'];
            $totalbropro += $valx['brondolan'];
        }

        if (round($totalJanjangpanen, 2) != round($totaljjgpro, 2)) {
            exit("Warning : Total JJG PROPORSI = " . $totaljjgpro . " tidak sama dengan Total JJG PANEN = " . $totalJanjangpanen . "");
        }

        if (round($totalBrondolanpanen, 2) != round($totalbropro, 2)) {
            exit("Warning : Total BRONDOLAN PROPORSI = " . $totalbropro . " tidak sama dengan Total BRONDOLAN PANEN = " . $totalBrondolanpanen . " ");
        }

        try {
            $owlPDO->beginTransaction();

            $str = "update " . $dbname . ".kebun_proporsitahuntanam set flag = '1' where notransaksi = '" . $notransaksi . "'";
            $owlPDO->exec($str);

            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;

        break;
}
