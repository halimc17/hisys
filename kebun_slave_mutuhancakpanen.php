<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$tgl       = tanggalsystemn(checkPostGet('tgl', ''));
$tgl2       = checkPostGet('tgl2', '');
$method    = checkPostGet('method', '');
$kodeorg      = checkPostGet('kodeorg', '');
$nik    = checkPostGet('nik', '');
$nikpemanen = checkPostGet('nikpemanen', '');
$luaspnn   = checkPostGet('luaspnn', '');
$jjgbuahbesar = checkPostGet('jjgbuahbesar', '');
$jjgbuahkecil = checkPostGet('jjgbuahkecil', '');
$mode         = checkPostGet('mode', '');
$totaljjg = checkPostGet('totaljjg', '');
$karyawansch = checkPostGet('karyawansch', '');
$tglsch    = tanggalsystem(checkPostGet('tglsch', ''));
$unitsch   = checkPostGet('unitsch', '');
$periodesch = checkPostGet('periodesch', '');
$nmorg     = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmindk     = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');
$nmkar     = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$jab   = getPostingJabatan('mutu_ancak');
if (count($_POST) > 0) {
    $param = $_POST;
} else {
    $param = $_GET;
}

function mutuFilterWhere($karyawansch, $tglsch, $unitsch, $periodesch)
{
    $where = "";
    if ($karyawansch != '') {
        $where .= " and namamandor like '%" . $karyawansch . "%' ";
    }
    if ($tglsch != '') {
        $where .= " and tanggal='" . $tglsch . "' ";
    }
    if ($unitsch != '') {
        $where .= " and left(kodeorg,4)='" . $unitsch . "' ";
    }
    if (preg_match('/^\d{4}-\d{2}$/', $periodesch)) {
        $where .= " and tanggal between '" . $periodesch . "-01' and '" . date('Y-m-t', strtotime($periodesch . '-01')) . "' ";
    }
    return $where;
}

#kode denda yang punya kolom penalti<id> di tabel rekap (id di luar itu tidak bisa disimpan)
function mutuDendaList()
{
    global $dbname;
    $kolom = array();
    foreach (fetchdata("show columns from " . $dbname . ".kebun_rekapmutuhancakpanen like 'penalti%'") as $c) {
        $kolom[$c['Field']] = 1;
    }
    $list = array();
    foreach (fetchdata("SELECT id, kodedenda, deskripsi, status FROM " . $dbname . ".kebun_5kodedendapanen order by id asc") as $d) {
        if (isset($kolom['penalti' . $d['id']])) {
            $list[] = $d;
        }
    }
    return $list;
}

function mutuPostedCount($tanggal, $nikmandor)
{
    global $dbname;
    $r = fetchdata("select count(*) as c from " . $dbname . ".kebun_rekapmutuhancakpanen where tanggal='" . $tanggal . "' and nikmandor='" . $nikmandor . "' and posting='1'");
    return (int)$r[0]['c'];
}

function mutuPeriodeTertutup($tanggal, $nikmandor)
{
    global $dbname;
    $resU = fetchdata("select distinct left(kodeorg,4) as unit from " . $dbname . ".kebun_rekapmutuhancakpanen where tanggal='" . $tanggal . "' and nikmandor='" . $nikmandor . "'");
    $units = array();
    foreach ($resU as $v) {
        $units[] = "'" . $v['unit'] . "'";
    }
    if (count($units) == 0) {
        return '';
    }
    $in = implode(',', $units);
    $akt = fetchdata("select count(*) as c from " . $dbname . ".setup_periodeakuntansi where kodeorg in (" . $in . ") and tutupbuku='1' and '" . $tanggal . "' between tanggalmulai and tanggalsampai");
    $gaji = fetchdata("select count(*) as c from " . $dbname . ".sdm_5periodegaji where kodeorg in (" . $in . ") and sudahproses<>0 and '" . $tanggal . "' between tanggalmulai and tanggalsampai");
    $alasan = array();
    if ($akt[0]['c'] > 0) {
        $alasan[] = 'Periode Akuntansi';
    }
    if ($gaji[0]['c'] > 0) {
        $alasan[] = 'Periode Gaji';
    }
    return count($alasan) ? implode(' & ', $alasan) . ' sudah ditutup' : '';
}

function mutuExportRows($where)
{
    global $dbname;
    $sql = "select g.nikmandor, g.tanggal, g.namamandor, g.jmlpemanen, g.totaljjg, g.jjgbuahbesar, g.jjgbuahkecil, g.posting, g.postingby, k.subbagian, p.namakaryawan as namaposting
        from (select nikmandor,tanggal,max(namamandor) as namamandor,count(distinct nik) as jmlpemanen,sum(totaljjg) as totaljjg,sum(jjgbuahbesar) as jjgbuahbesar,sum(jjgbuahkecil) as jjgbuahkecil,max(posting) as posting,max(postingby) as postingby
            from " . $dbname . ".kebun_rekapmutuhancakpanen_vw
            where 1=1 and nikmandor in (select karyawanid from " . $dbname . ".datakaryawan where lokasitugas in (" . getOrgDetail(2) . ")) " . $where . "
            group by tanggal,nikmandor) g
        left join " . $dbname . ".datakaryawan k on k.karyawanid=g.nikmandor
        left join " . $dbname . ".datakaryawan p on p.karyawanid=g.postingby
        order by g.tanggal desc, g.namamandor asc, g.nikmandor asc";
    return fetchdata($sql);
}

function mutuFilterInfo($nmorg, $karyawansch, $tglsch, $unitsch, $periodesch)
{
    $info = array();
    $info[] = "Unit: " . ($unitsch != '' ? $unitsch . " - " . $nmorg[$unitsch] : "Semua");
    $info[] = "Periode: " . ($periodesch != '' ? $periodesch : "Semua");
    if ($tglsch != '') {
        $info[] = "Tanggal: " . tanggalnormal($tglsch);
    }
    if ($karyawansch != '') {
        $info[] = "Mandor: " . $karyawansch;
    }
    return implode("   |   ", $info);
}

switch ($method) {
    case 'loaddata':
        $where = mutuFilterWhere($karyawansch, $tglsch, $unitsch, $periodesch);
        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = floatval($page) * $limit;
        $maxdisplay = (floatval($page) * $limit);
        $sql = "select count(*) as jmlhrow from (select 1 from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where 1=1 and nikmandor in (select karyawanid from datakaryawan where lokasitugas in (" . getOrgDetail(2) . ") ) " . $where . " group by tanggal,nikmandor) t";
        $resc = fetchdata($sql);
        $jlhbrs = (int)$resc[0]['jmlhrow'];
        $no = 0;
        $str = "SELECT nikmandor,tanggal,max(namamandor) as namamandor,max(posting) as posting,max(postingby) as postingby FROM " . $dbname . ".kebun_rekapmutuhancakpanen_vw
		where 1=1 " . $where . " and nikmandor in (select karyawanid from datakaryawan where lokasitugas in (" . getOrgDetail(2) . ") ) group by tanggal,nikmandor
        order by tanggal desc, namamandor asc, nikmandor asc limit " . $offset . "," . $limit . "";
        $tab = "";
        $no = $maxdisplay;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        if ($jlhbrs == 0) {
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center colspan=10>" . $_SESSION['lang']['errdatanotexist'] . "</td>";
            $tab .= "</tr>";
        } else {
            while ($bar = $res->fetch()) {
                $isi = '';
                $no += 1;
                $divmandor = getKary($bar['nikmandor'], 'subbagian');
                $tab .= "<tr class=rowcontent  id=tr_$no>";
                $tab .= "<td align=center style='width:5%'>" . $no . "</td>";
                $tab .= "<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>";
                $tab .= "<td align=left>" . ($divmandor != '' ? $divmandor . " - " . getNamaOrg($divmandor) : "-") . "</td>";
                $tab .= "<td>" . $bar['namamandor'] . "</td>";
                $tab .= "<td>" . $nmkar[$bar['postingby']] . "</td>";
                if ($bar['posting'] == 0) {
                    $isi .= "<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit'
                        onclick=\"edit('" . $bar['tanggal'] . "', '" . $bar['nikmandor'] . "', 'edit');\" ></td>";
                    $isi .= "<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete'
                        onclick=\"deleteData('" . $bar['tanggal'] . "', '" . $bar['nikmandor'] . "','" . $page . "');\" ></td>";
                    $isi .= "<td align=center><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Posting'
                        onclick=\"posting('" . $bar['tanggal'] . "', '" . $bar['nikmandor'] . "','" . $page . "');\" ></td>";
                } else {
                    $tertutup = mutuPeriodeTertutup($bar['tanggal'], $bar['nikmandor']);
                    if ($tertutup != '') {
                        $icon = "images/icons/04/16/02.png";
                        $title = "Closed - " . $tertutup;
                        $unpost = '';
                    } elseif (in_array($_SESSION['empl']['jabatan'], $jab)) {
                        $icon = "images/icons/04/16/04.png";
                        $title = "Unposting";
                        $unpost = " onclick=\"unposting('" . $bar['nikmandor'] . "','" . $bar['tanggal'] . "','" . $page . "');\" ";
                    } else {
                        $icon = "images/icons/04/16/02.png";
                        $title = "Posted";
                        $unpost = '';
                    }
                    $isi .= "<td align=center></td><td align=center></td>";
                    $isi .= "<td align=center><img src=" . $icon . " class=zImgBtn class=zImgBtn height='30'  title='" . $title . "' " . $unpost . " ></td>";
                }
                $isi .= "<td align=center><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View HTML'
                        onclick=\"html('" . $bar['nikmandor'] . "','" . $bar['tanggal'] . "');\" ></td>";
                $tab .= $isi;
                $tab .= "</tr>";
            }
        }

        $footd = createpaging($jlhbrs, $limit, $page, '10', 'loaddata', 'getPage');
        echo $tab . "####" . $footd;
        break;

    case 'excel':
        require_once 'dompdf/PHPExcel.php';
        require_once 'dompdf/PHPExcel/IOFactory.php';
        $rows = mutuExportRows(mutuFilterWhere($karyawansch, $tglsch, $unitsch, $periodesch));

        $objPHPExcel = new PHPExcel();
        $ws = $objPHPExcel->setActiveSheetIndex(0);
        $ws->setTitle('Mutu Hancak');
        $ws->setCellValue('A1', 'MUTU HANCAK PANEN');
        $ws->setCellValue('A2', mutuFilterInfo($nmorg, $karyawansch, $tglsch, $unitsch, $periodesch));
        $ws->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $judul = array('A' => 'No', 'B' => 'Tanggal', 'C' => 'Divisi', 'D' => 'Mandor Panen', 'E' => 'Jumlah Pemanen', 'F' => 'Total Janjang', 'G' => 'Basis Besar', 'H' => 'Basis Kecil', 'I' => 'Status', 'J' => 'Diposting Oleh');
        $lebar = array('A' => 6, 'B' => 12, 'C' => 38, 'D' => 28, 'E' => 16, 'F' => 15, 'G' => 13, 'H' => 13, 'I' => 14, 'J' => 26);
        foreach ($judul as $col => $txt) {
            $ws->setCellValue($col . '4', $txt);
            $ws->getColumnDimension($col)->setWidth($lebar[$col]);
        }
        $ws->getStyle('A4:J4')->getFont()->setBold(true);
        $ws->getStyle('A4:J4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('DEDEDE');

        $row = 5;
        $no = 0;
        $tPemanen = $tJjg = $tBesar = $tKecil = 0;
        foreach ($rows as $bar) {
            $no++;
            $tPemanen += $bar['jmlpemanen'];
            $tJjg += $bar['totaljjg'];
            $tBesar += $bar['jjgbuahbesar'];
            $tKecil += $bar['jjgbuahkecil'];
            $div = $bar['subbagian'] != '' ? $bar['subbagian'] . " - " . $nmorg[$bar['subbagian']] : "-";
            $ws->setCellValueExplicit('A' . $row, $no, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $ws->setCellValueExplicit('B' . $row, tanggalnormal($bar['tanggal']), PHPExcel_Cell_DataType::TYPE_STRING);
            $ws->setCellValueExplicit('C' . $row, $div, PHPExcel_Cell_DataType::TYPE_STRING);
            $ws->setCellValueExplicit('D' . $row, $bar['namamandor'], PHPExcel_Cell_DataType::TYPE_STRING);
            $ws->setCellValueExplicit('E' . $row, $bar['jmlpemanen'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $ws->setCellValueExplicit('F' . $row, $bar['totaljjg'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $ws->setCellValueExplicit('G' . $row, $bar['jjgbuahbesar'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $ws->setCellValueExplicit('H' . $row, $bar['jjgbuahkecil'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $ws->setCellValueExplicit('I' . $row, $bar['posting'] == 1 ? 'Posted' : 'Belum Posting', PHPExcel_Cell_DataType::TYPE_STRING);
            $ws->setCellValueExplicit('J' . $row, $bar['posting'] == 1 ? (string)$bar['namaposting'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $row++;
        }
        $ws->setCellValue('A' . $row, 'Total');
        $ws->setCellValueExplicit('E' . $row, $tPemanen, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $ws->setCellValueExplicit('F' . $row, $tJjg, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $ws->setCellValueExplicit('G' . $row, $tBesar, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $ws->setCellValueExplicit('H' . $row, $tKecil, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $ws->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        $ws->getStyle('E5:H' . $row)->getNumberFormat()->setFormatCode('#,##0');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="MutuHancak_' . date('YmdHis') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
        break;

    case 'pdf':
        require_once('lib/fpdf.php');
        $rows = mutuExportRows(mutuFilterWhere($karyawansch, $tglsch, $unitsch, $periodesch));

        class PDFMutuHancak extends FPDF
        {
            public $judul = '';
            public $info = '';
            public $lebar = array(8, 22, 56, 40, 18, 24, 22, 22, 22, 38);
            public $kolom = array('No', 'Tanggal', 'Divisi', 'Mandor Panen', 'Pemanen', 'Total Jjg', 'Basis Besar', 'Basis Kecil', 'Status', 'Diposting Oleh');

            function Header()
            {
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(0, 6, $this->judul, 0, 1, 'L');
                $this->SetFont('Arial', '', 8);
                $this->Cell(0, 5, $this->info, 0, 1, 'L');
                $this->Ln(1);
                $this->SetFont('Arial', 'B', 8);
                $this->SetFillColor(222, 222, 222);
                foreach ($this->kolom as $i => $txt) {
                    $this->Cell($this->lebar[$i], 6, $txt, 1, 0, 'C', true);
                }
                $this->Ln();
            }

            function Footer()
            {
                $this->SetY(-12);
                $this->SetFont('Arial', 'I', 7);
                $this->Cell(0, 5, 'Print Time: ' . date('d-m-Y H:i:s') . '   By: ' . $_SESSION['empl']['name'] . '   Page ' . $this->PageNo() . '/{nb}', 0, 0, 'L');
            }
        }

        $pdf = new PDFMutuHancak('L', 'mm', 'A4');
        $pdf->judul = 'MUTU HANCAK PANEN';
        $pdf->info = mutuFilterInfo($nmorg, $karyawansch, $tglsch, $unitsch, $periodesch);
        $pdf->AliasNbPages();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 8);

        $w = $pdf->lebar;
        $no = 0;
        $tPemanen = $tJjg = $tBesar = $tKecil = 0;
        foreach ($rows as $bar) {
            $no++;
            $tPemanen += $bar['jmlpemanen'];
            $tJjg += $bar['totaljjg'];
            $tBesar += $bar['jjgbuahbesar'];
            $tKecil += $bar['jjgbuahkecil'];
            $div = $bar['subbagian'] != '' ? $bar['subbagian'] . " - " . $nmorg[$bar['subbagian']] : "-";
            $pdf->Cell($w[0], 5, $no, 1, 0, 'C');
            $pdf->Cell($w[1], 5, tanggalnormal($bar['tanggal']), 1, 0, 'C');
            $pdf->Cell($w[2], 5, substr($div, 0, 36), 1, 0, 'L');
            $pdf->Cell($w[3], 5, substr($bar['namamandor'], 0, 26), 1, 0, 'L');
            $pdf->Cell($w[4], 5, number_format($bar['jmlpemanen']), 1, 0, 'R');
            $pdf->Cell($w[5], 5, number_format($bar['totaljjg']), 1, 0, 'R');
            $pdf->Cell($w[6], 5, number_format($bar['jjgbuahbesar']), 1, 0, 'R');
            $pdf->Cell($w[7], 5, number_format($bar['jjgbuahkecil']), 1, 0, 'R');
            $pdf->Cell($w[8], 5, $bar['posting'] == 1 ? 'Posted' : 'Belum Posting', 1, 0, 'C');
            $pdf->Cell($w[9], 5, $bar['posting'] == 1 ? substr((string)$bar['namaposting'], 0, 24) : '', 1, 1, 'L');
        }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w[0] + $w[1] + $w[2] + $w[3], 6, 'Total', 1, 0, 'C');
        $pdf->Cell($w[4], 6, number_format($tPemanen), 1, 0, 'R');
        $pdf->Cell($w[5], 6, number_format($tJjg), 1, 0, 'R');
        $pdf->Cell($w[6], 6, number_format($tBesar), 1, 0, 'R');
        $pdf->Cell($w[7], 6, number_format($tKecil), 1, 0, 'R');
        $pdf->Cell($w[8] + $w[9], 6, '', 1, 1);
        $pdf->Output('MutuHancak_' . date('YmdHis') . '.pdf', 'I');
        exit;
        break;

    case 'html':
        $tTotJjg = $tluaspanen = $tJjgBesar = $tJjgKecil = 0;
        $totDenda = [];
        // Get Kode Denda Panen (hanya yang punya kolom penalti di tabel rekap)
        $rDenda = mutuDendaList();
        // Get Jumlah Kode Denda Panen
        $countDenda = count($rDenda);

        $tab = "";
        $tab .= "<label>Daftar Mutu Hancak</label>";
        $tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['total'] . " <br> " . $_SESSION['lang']['jjg'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['jjg'] . "</td>
            <td align=center colspan='" . $countDenda . "'>" . $_SESSION['lang']['denda'] . "</td>
        </tr>";
        $tab .= "<tr class=rowheader>";
        $tab .= "<th align=center>Basis Besar</th>";
        $tab .= "<th align=center>Basis Kecil</th>";
        $namadenda = array();
        foreach ($rDenda as $dnd) {
            $tab .= "<th align=center title='" . $dnd['deskripsi'] . "'>" . $dnd['kodedenda'] . "</th>";

            $namadenda[$dnd['kodedenda']] = $dnd['deskripsi'];
        }
        $tab .= "</tr>";
        $tab .= "</thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where nikmandor='" . $nik . "' and tanggal='" . $tgl2 . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no += 1;
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center>" . $no . "</td>";
            $tab .= "<td align=left>" . getNamaKaryawan($bar['nik']) . "</td>";
            $tab .= "<td align=center>" . $nmindk[$bar['kodeorg']] . "</td>";
            $tab .= "<td align=right>" . @number_format($bar['totaljjg']) . "</td>";
            $tab .= "<td align=right>" . @number_format($bar['jjgbuahbesar']) . "</td>";
            $tab .= "<td align=right>" . @number_format($bar['jjgbuahkecil']) . "</td>";
            foreach ($rDenda as $dnd) {
                $tab .= "<td align=right>" . @number_format($bar['penalti' . $dnd['id']]) . "</td>";
                @$totDenda[$dnd['id']] += $bar['penalti' . $dnd['id']];
            }
            @$tTotJjg += $bar['totaljjg'];
            @$tJjgBesar += $bar['jjgbuahbesar'];
            @$tJjgKecil += $bar['jjgbuahkecil'];
        }
        $tab .= "</tr>";
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=center colspan=3><b>" . $_SESSION['lang']['total'] . "</td>";
        $tab .= "<td align=right><b>" . @number_format($tTotJjg, 2) . "</td>";
        $tab .= "<td align=right><b>" . @number_format($tJjgBesar, 2) . "</td>";
        $tab .= "<td align=right><b>" . @number_format($tJjgKecil, 2) . "</td>";
        foreach ($rDenda as $dnd) {
            $tab .= "<td align=right><b>" . @number_format($totDenda[$dnd['id']]) . "</td>";
        }
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br><br>";

        $sCek = "SELECT * FROM $dbname.kebun_rekaphancakpanen_photo WHERE tanggal='" . $tgl2 . "' AND nikamandor='" . $nik . "'";
        $rCek = fetchData($sCek);
        $countCek = count($rCek);
        if ($countCek > 0) {
            $tab .= "<label>Daftar Photo Mutu Hancak</label>";
            $tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
                <thead><tr class=rowheader>
                <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
                <td align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</td>
                <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
                <td align=center rowspan='2'>" . $_SESSION['lang']['denda'] . "</td>
                <td align=center rowspan='2'>" . $_SESSION['lang']['photo'] . "</td>
            </tr>";
            $tab .= "</thead>";
            $no = 0;
            $str = "select * from " . $dbname . ".kebun_rekaphancakpanen_photo where nikamandor='" . $nik . "' and tanggal='" . $tgl2 . "'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar = $res->fetch()) {
                $no += 1;
                $tab .= "<tr class=rowcontent>";
                $tab .= "<td align=center>" . $no . "</td>";
                $tab .= "<td align=left>" . getNamaKaryawan($bar['nik']) . "</td>";
                $tab .= "<td align=center>" . $nmindk[$bar['kodeorg']] . "</td>";
                $tab .= "<td align=left>[" . $bar['kodedenda'] . "]  " . $namadenda[$bar['kodedenda']] . "</td>";
                $tab .= "<td align=center>";
                $tab .= "<a href='" . $bar['photo'] . "' class='popup-img'>";
                $tab .= "<img onclick=\"popupimage()\" src='" . $bar['photo'] . "'
                        alt='" . $bar['kodedenda'] . "' width=50px height=50px>";
                $tab .= "</a>";
                $tab .= "</td>";
                $tab .= "</tr>";
            }
            $tab .= "</table>";
        }
        echo $tab;
        break;

    case 'delete':
        if (mutuPostedCount($tgl2, $nik) > 0) {
            exit("Warning : Data sudah diposting, tidak dapat dihapus. Lakukan unposting terlebih dahulu.");
        }
        $str = "delete from " . $dbname . ".kebun_rekapmutuhancakpanen where tanggal='" . $tgl2 . "' and nikmandor='" . $nik . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'posting':
        $str = "UPDATE $dbname.kebun_rekapmutuhancakpanen SET posting='1', postingby='" . $_SESSION['standard']['userid'] . "'
                WHERE tanggal='" . $tgl2 . "' AND nikmandor='" . $nik . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'loaddatadetail':
        // Get Kode Denda Panen (hanya yang punya kolom penalti di tabel rekap)
        $rDenda = mutuDendaList();
        // Get Jumlah Kode Denda Panen
        $countDenda = count($rDenda);

        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:905px>
            <thead>
                <tr class=rowheader>
                <th align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</th>
                <th align=center colspan='2'>" . $_SESSION['lang']['jjg'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['jjg'] . "</th>
                <th align=center colspan='" . $countDenda . "'>" . $_SESSION['lang']['denda'] . "</th>
                <th align=center rowspan='2' width=50px>" . $_SESSION['lang']['action'] . "</th>
                </tr>";
        $tab .= "<tr class=rowheader>";
        $tab .= "<th align=center>Basis Besar</th>";
        $tab .= "<th align=center>Basis Kecil</th>";
        foreach ($rDenda as $dnd) {
            $tab .= "<td align=center title='" . $dnd['deskripsi'] . "'>" . $dnd['kodedenda'] . "</td>";
        }
        $tab .= "</tr>";
        $tab .= "";
        $tab .= "</thead>";
        $no = 0;
        $totalpenalti = array();
        $tluaspanen = $tjjgbesar = $tjjgkecil = $totTtljjg = 0;
        if ($mode == "edit") {
            $str = "select * from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where tanggal='" . $tgl2 . "' and nikmandor='" . $nik . "'";
        } else {
            $str = "select * from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where tanggal='" . $tgl . "' and nikmandor='" . $nik . "'";
        }
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no += 1;
            $tab .= "<tr class=rowcontent>
                    <td id=kodeorgd" . $no . ">" . $bar['kodeorg'] . "</td>
                    <td id=tgld" . $no . ">" . $bar['tanggal'] . "</td>
                    <td>" . getNamaKaryawan($bar['nik']) . " - " . getKary($bar['nik'], 'nik') . "</td>
                    <td hidden id=nikd" . $no . ">" . $bar['nik'] . "</td>
                    <td align=right>" . $bar['jjgbuahbesar'] . "</td>
                    <td align=right>" . $bar['jjgbuahkecil'] . "</td>
                    <td align=right>" . $bar['totaljjg'] . "</td>";
            foreach ($rDenda as $dnd) {
                $tab .= "<td align=right>" . @number_format($bar['penalti' . $dnd['id']]) . "</td>";
                @$totalpenalti[$dnd['id']] += $bar['penalti' . $dnd['id']];
            }
            if ($mode != "") {
                $tab .= "<td align=center width=25px>
                            <img title='" . $_SESSION['lang']['delete'] . "' class=zImgBtn onclick=\"deletedetail('" . $no . "','edit')\" src='images/application/application_delete.png'/>
                        </td>";
            } else {
                $tab .= "<td align=center width=25px>
                            <img title='" . $_SESSION['lang']['delete'] . "' class=zImgBtn onclick=\"deletedetail('" . $no . "')\" src='images/application/application_delete.png'/>
                        </td>";
            }

            $tab .= "</tr>";
            $tjjgbesar  += $bar['jjgbuahbesar'];
            $tjjgkecil  += $bar['jjgbuahkecil'];
            $totTtljjg  += $bar['totaljjg'];
        }
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=center colspan=3> <b>" . $_SESSION['lang']['total'] . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($tjjgbesar, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($tjjgkecil, 2) . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($totTtljjg, 2) . "</b> </td>";

        foreach ($rDenda as $dnd) {
            $tab .= "<td align=right> <b>" . @number_format($totalpenalti[$dnd['id']], 2) . "</b> </td>";
        }

        $tab .= "<td></td>";
        $tab .= "</tr>";
        $tab .= "</table>";
        echo $tab;
        break;

    case 'detail':
        // Cek Apakah Datanya sudah diposting
        $tglcek = ($mode == "edit") ? $tgl2 : $tgl;
        $jlhbrs = mutuPostedCount($tglcek, $nik);
        if ($jlhbrs > 0) {
            // Jika ada data sudah diposting maka tidak bisa melakukan pengisian data
            exit("Error : Data untuk Tanggal: " . tanggalnormal($tglcek) . " <br> Dengan Mandor " . getNamaKaryawan($nik) . " sudah di posting");
        }

        // Get Kode Denda Panen (hanya yang punya kolom penalti di tabel rekap)
        $rDenda = mutuDendaList();
        // Get Jumlah Kode Denda Panen
        $countDenda = count($rDenda);

        OPEN_BOX();
        echo "
        <fieldset>
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
        <table border=0 cellpadding=3 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <th align=center rowspan='2' colspan=2>" . $_SESSION['lang']['kodeblok'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</th>
            <th align=center colspan='2'>" . $_SESSION['lang']['jjg'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['jjg'] . "</th>
            <th align=center colspan='" . $countDenda . "' style='width:100px;'>" . $_SESSION['lang']['dendapanen'] . "</th>
            <th align=center rowspan='2' width=50px>" . $_SESSION['lang']['action'] . "</th>
        </tr>";
        echo "<tr class=rowheader>";
        echo "<th align=center>Basis Besar</th>";
        echo "<th align=center>Basis Kecil</th>";
        foreach ($rDenda as $dnd) {
            echo "<th align=center title='" . $dnd['deskripsi'] . "'>" . $dnd['kodedenda'] . "</th>";
        }
        echo "</tr>";
        echo "</thead>";

        $sumPenalti = "";
        foreach ($rDenda as $dnd) {
            $sumPenalti .= ", SUM(penalti" . $dnd['id'] . ") AS penalti" . $dnd['id'];
        }

        // Jika Mode Edit
        if ($mode != "edit") {
            $sql = "select kodeorg, tanggal,nikmandor, karyawanid, sum(hasilkerja) as totaljjg, sum(jjgbuahbesar) as jjgbuahbesar, sum(jjgbuahkecil) as jjgbuahkecil" . $sumPenalti . "
            from " . $dbname . ".kebun_prestasi_new_vw where tanggal='" . $tgl . "' and nikmandor='" . $nik . "'
            group by kodeorg,karyawanid order by karyawanid asc";
        } else {
            $sql = "select kodeorg, tanggal,nikmandor, karyawanid, sum(hasilkerja) as totaljjg, sum(jjgbuahbesar) as jjgbuahbesar, sum(jjgbuahkecil) as jjgbuahkecil" . $sumPenalti . "
            from " . $dbname . ".kebun_prestasi_new_vw where tanggal='" . $tgl2 . "' and nikmandor='" . $nik . "'
            group by kodeorg,karyawanid order by karyawanid asc";
        }

        $no = 0;
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            // Get Luas Induk Blok
            $sBlok = "SELECT indukblok, SUM(luasareaproduktif) AS luasareaproduktif FROM $dbname.setup_blok WHERE indukblok='" . $bar['kodeorg'] . "' AND (" . date(("Y")) . " - tahuntanam >= 3)";
            $rBlok = fetchData($sBlok);
            foreach ($rBlok as $val) {
                $totluasblok[$val['indukblok']] = $val['luasareaproduktif'];
            }
            $no++;
            // Cek Apakah Ada Data Di Rekap Hancak Panen
            $sqldt = "select count(*) as jmlhrow from " . $dbname . ".kebun_rekapmutuhancakpanen_vw where kodeorg='" . $bar['kodeorg'] . "'
            and tanggal='" . $bar['tanggal'] . "' and nik='" . $bar['karyawanid'] . "'";
            $resdt = $owlPDO->query($sqldt) or die(print " Gagal: " . PDOException::getMessage());
            $resdt->setFetchMode(PDO::FETCH_ASSOC);
            $bardt = $resdt->fetch();
            $jlhbrsdt = $bardt['jmlhrow'];
            if ($jlhbrsdt == 0) {
                // Jika Data belum ada di rekap hancak panen maka munculkan
                echo "<tr class=rowcontent>
                    <td colspan=2 id=blokx" . $no . ">" . $bar['kodeorg'] . "</td>
                    <td id=tglx" . $no . ">" . $bar['tanggal'] . "</td>
                    <td>" . getNamaKaryawan($bar['karyawanid']) . " - " . getKary($bar['karyawanid'], 'nik') . "</td>
                    <td hidden id=nikx" . $no . ">" . $bar['karyawanid'] . "</td>
                    <td hidden id=nikmandorx" . $no . ">" . $bar['nikmandor'] . "</td>
                    <td align=right id=jjgbuahbesarx" . $no . ">" . $bar['jjgbuahbesar'] . "</td>
                    <td align=right id=jjgbuahkecilx" . $no . ">" . $bar['jjgbuahkecil'] . "</td>
                    <td align=right id=totaljjgx" . $no . ">" . $bar['totaljjg'] . "</td>";
                foreach ($rDenda as $dnd) {
                    echo "<td>
                            <input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=penaltix" . $dnd['id'] . "_" . $no . " style='width:50px;' value='" . $bar['penalti' . $dnd['id']] . "'>
                        </td>";
                }
                if ($mode != "edit") {
                    echo "<td align=center width=25px> <input type=hidden id=method value='insert'>
                            <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail(" . $no . ")\" src='images/save.png'/>
                        </td>";
                } else {
                    echo "<td align=center width=25px> <input type=hidden id=method value='insert'>
                            <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail(" . $no . ",'edit')\" src='images/save.png'/>
                        </td>";
                }
                echo "</tr>";
            }
        }

        echo "</table>
        <br/>
        <button id=done class=mybutton onclick=cancel()>" . $_SESSION['lang']['selesai'] . "</button>
        </fieldset>";

        echo "
        <fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
        <div id=loaddatadetail>
            <script></script>
         </fieldset>";
        CLOSE_BOX();
        break;

    case 'insert':
        if (mutuPostedCount($tgl2, $nik) > 0) {
            exit("Warning : Data sudah diposting, tidak dapat diubah. Lakukan unposting terlebih dahulu.");
        }

        // Total janjang diambil ulang dari data prestasi, bukan dari nilai yang dikirim browser
        $sTot = "select count(*) as c, sum(hasilkerja) as totaljjg, sum(jjgbuahbesar) as jjgbuahbesar, sum(jjgbuahkecil) as jjgbuahkecil
            from " . $dbname . ".kebun_prestasi_new_vw where tanggal='" . $tgl2 . "' and nikmandor='" . $nik . "' and kodeorg='" . $kodeorg . "' and karyawanid='" . $nikpemanen . "'";
        $rTot = fetchData($sTot);
        if ($rTot[0]['c'] == 0) {
            exit("Warning : Data prestasi untuk pemanen dan blok ini tidak ditemukan.");
        }

        $arrDataIns = array(
            'kodeorg'       => $kodeorg,
            'nik'           => $nikpemanen,
            'tanggal'       => $tgl2,
            'nikmandor'     => $nik,
            'totaljjg'      => $rTot[0]['totaljjg'],
            'jjgbuahbesar'  => $rTot[0]['jjgbuahbesar'],
            'jjgbuahkecil'  => $rTot[0]['jjgbuahkecil'],
            'posting'       => '0',
            'postingby'     => '0000000000',
        );
        foreach (mutuDendaList() as $dnd) {
            $nilai = isset($param['penalti' . $dnd['id']]) ? $param['penalti' . $dnd['id']] : 0;
            $arrDataIns['penalti' . $dnd['id']] = is_numeric($nilai) ? $nilai : 0;
        }

        $colsInsDt = array();
        foreach ($arrDataIns as $key => $row) {
            $colsInsDt[] = $key;
        }
        $insDetail = insertQuery($dbname, "kebun_rekapmutuhancakpanen", $arrDataIns, $colsInsDt);
        try {
            $owlPDO->exec($insDetail);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'deletedetail':
        $whm = ($nik != '') ? " and nikmandor='" . $nik . "'" : "";
        $rPost = fetchdata("select count(*) as c from " . $dbname . ".kebun_rekapmutuhancakpanen where kodeorg='" . $kodeorg . "' and tanggal='" . $tgl2 . "' and nik='" . $nikpemanen . "'" . $whm . " and posting='1'");
        if ($rPost[0]['c'] > 0) {
            exit("Warning : Data sudah diposting, tidak dapat dihapus. Lakukan unposting terlebih dahulu.");
        }
        $str = "delete from " . $dbname . ".kebun_rekapmutuhancakpanen where kodeorg='" . $kodeorg . "' and tanggal='" . $tgl2 . "' and nik='" . $nikpemanen . "'" . $whm;
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'unposting':
        if (!in_array($_SESSION['empl']['jabatan'], $jab)) {
            exit("Warning : Anda tidak memiliki hak untuk unposting.");
        }
        $tertutup = mutuPeriodeTertutup($tgl2, $nik);
        if ($tertutup != '') {
            exit("Warning : Unposting ditolak, " . $tertutup . " (tanggal " . tanggalnormal($tgl2) . ").");
        }
        $str = "UPDATE $dbname.kebun_rekapmutuhancakpanen SET posting='0', postingby='0'
                WHERE tanggal='" . $tgl2 . "' AND nikmandor='" . $nik . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
}
