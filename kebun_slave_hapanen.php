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
$blok      = checkPostGet('blok', '');
$nik    = checkPostGet('nik', '');
$nikmandor    = checkPostGet('nikmandor', '');
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
$jab   = getPostingJabatan('panen');

function hapanenFilterWhere($karyawansch, $tglsch, $unitsch, $periodesch)
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

function hapanenUnitScope()
{
    global $dbname;
    $petaInduk = array('CAR' => "'CAR','LAN'", 'LAN' => "'CAR','LAN'", 'DMA' => "'DMA','MHA'", 'MHA' => "'DMA','MHA'", 'PPP' => "'PPP'");
    $pt = getindukPT($_SESSION['empl']['lokasitugas']);
    if (!isset($petaInduk[$pt])) {
        return "''";
    }
    $res = fetchdata("select kodeorganisasi from " . $dbname . ".organisasi where induk in (" . $petaInduk[$pt] . ")");
    $list = array();
    foreach ($res as $v) {
        $list[] = "'" . $v['kodeorganisasi'] . "'";
    }
    return count($list) ? implode(',', $list) : "''";
}

function hapanenExportRows($where)
{
    global $dbname;
    $sql = "select g.nikmandor, g.tanggal, g.namamandor, g.hapanen, g.posting, g.postingby, k.subbagian, p.namakaryawan as namaposting
        from (select nikmandor,tanggal,max(namamandor) as namamandor,sum(hapanen) as hapanen,min(posting) as posting,max(postingby) as postingby
            from " . $dbname . ".kebun_rekaphancakpanen_vw
            where 1=1 and nik in (select karyawanid from " . $dbname . ".datakaryawan where lokasitugas in (" . hapanenUnitScope() . ")) " . $where . "
            group by tanggal,nikmandor) g
        left join " . $dbname . ".datakaryawan k on k.karyawanid=g.nikmandor
        left join " . $dbname . ".datakaryawan p on p.karyawanid=g.postingby
        order by g.tanggal desc, g.namamandor asc, g.nikmandor asc";
    return fetchdata($sql);
}

function hapanenFilterInfo($nmorg, $karyawansch, $tglsch, $unitsch, $periodesch)
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

function hapanenPeriodeTertutup($tanggal, $nikmandor)
{
    global $dbname;
    $resU = fetchdata("select distinct left(kodeorg,4) as unit from " . $dbname . ".kebun_rekaphancakpanen where tanggal='" . $tanggal . "' and nikmandor='" . $nikmandor . "'");
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

switch ($method) {
    case 'loaddata':
        $where = hapanenFilterWhere($karyawansch, $tglsch, $unitsch, $periodesch);
        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        if (getindukPT($_SESSION['empl']['lokasitugas']) == 'CAR' or getindukPT($_SESSION['empl']['lokasitugas']) == 'LAN') {
            $dataunitx = '';
            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='CAR'";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($dataunitx == "") {
                    $dataunitx .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }

            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='LAN' ";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($dataunitx == "") {
                    $dataunitx .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }
        }

        if (getindukPT($_SESSION['empl']['lokasitugas']) == 'DMA' or getindukPT($_SESSION['empl']['lokasitugas']) == 'MHA') {
            $dataunitx = '';
            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='DMA'";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($dataunitx == "") {
                    $dataunitx .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }

            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='MHA'";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($dataunitx == "") {
                    $dataunitx .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }
        }

        if (getindukPT($_SESSION['empl']['lokasitugas']) == 'PPP') {
            $dataunitx = '';
            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='PPP' ";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($dataunitx == "") {
                    $dataunitx .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }
        }


        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $sql = "select count(*) as jmlhrow from (select 1 from " . $dbname . ".kebun_rekaphancakpanen_vw where 1=1 and nik in (select karyawanid from " . $dbname . ".datakaryawan where lokasitugas in (" . $dataunitx . "))  " . $where . " group by tanggal,nikmandor) t";
        $resc = fetchdata($sql);
        $jlhbrs = (int)$resc[0]['jmlhrow'];

        $no = 0;
        $str = "SELECT nikmandor,tanggal,max(namamandor) as namamandor,sum(hapanen) as hapanen,min(posting) as posting,max(postingby) as postingby FROM " . $dbname . ".kebun_rekaphancakpanen_vw
		where 1=1 and nik in (select karyawanid from " . $dbname . ".datakaryawan where lokasitugas in (" . $dataunitx . ")) " . $where . " group by tanggal,nikmandor
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
                $tab .= "<tr class=rowcontent  id=tr_$no>";
                $tab .= "<td align=center>" . $no . "</td>";
                $tab .= "<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>";
                $divmandor = getKary($bar['nikmandor'], "subbagian");
                $tab .= "<td align=left>" . ($divmandor != '' ? $divmandor . " - " . getNamaOrg($divmandor) : "-") . "</td>";
                // $tab.="<td>" . $bar['kodeorg'] . " - " .  $nmindk[$bar['kodeorg']] . "</td>";
                $tab .= "<td>" . $bar['namamandor'] . "</td>";
                $tab .= "<td  align=right>" . @number_format($bar['hapanen'], 2) . "</td>";
                $tab .= "<td>" . $nmkar[$bar['postingby']] . "</td>";
                if ($bar['posting'] == 0) {
                    $isi .= "<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
                        onclick=\"edit('" . $bar['tanggal'] . "', '" . $bar['nikmandor'] . "', 'edit');\" ></td>";
                    $isi .= "<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
                        onclick=\"deleteData('" . $bar['tanggal'] . "', '" . $bar['nikmandor'] . "','" . $page . "');\" ></td>";
                    $isi .= "<td align=center><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Posting' 
                        onclick=\"posting('" . $bar['tanggal'] . "', '" . $bar['nikmandor'] . "','" . $page . "');\" ></td>";
                } else {
                    $tertutup = hapanenPeriodeTertutup($bar['tanggal'], $bar['nikmandor']);
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

        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = createpaging($jlhbrs, $limit, $page, '15', 'loaddata', 'getPage');
        echo $tab . "####" . $footd;
        break;

    case 'excel':
        require_once 'dompdf/PHPExcel.php';
        require_once 'dompdf/PHPExcel/IOFactory.php';
        $rows = hapanenExportRows(hapanenFilterWhere($karyawansch, $tglsch, $unitsch, $periodesch));

        $objPHPExcel = new PHPExcel();
        $ws = $objPHPExcel->setActiveSheetIndex(0);
        $ws->setTitle('Ha Panen');
        $ws->setCellValue('A1', 'REKAP HA PANEN');
        $ws->setCellValue('A2', hapanenFilterInfo($nmorg, $karyawansch, $tglsch, $unitsch, $periodesch));
        $ws->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $judul = array('A' => 'No', 'B' => 'Tanggal', 'C' => 'Divisi', 'D' => 'Mandor Panen', 'E' => 'Total Luas Panen (Ha)', 'F' => 'Status', 'G' => 'Diposting Oleh');
        $lebar = array('A' => 6, 'B' => 12, 'C' => 38, 'D' => 28, 'E' => 20, 'F' => 14, 'G' => 26);
        foreach ($judul as $col => $txt) {
            $ws->setCellValue($col . '4', $txt);
            $ws->getColumnDimension($col)->setWidth($lebar[$col]);
        }
        $ws->getStyle('A4:G4')->getFont()->setBold(true);
        $ws->getStyle('A4:G4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('DEDEDE');

        $row = 5;
        $no = 0;
        $total = 0;
        foreach ($rows as $bar) {
            $no++;
            $total += $bar['hapanen'];
            $div = $bar['subbagian'] != '' ? $bar['subbagian'] . " - " . $nmorg[$bar['subbagian']] : "-";
            $ws->setCellValueExplicit('A' . $row, $no, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $ws->setCellValueExplicit('B' . $row, tanggalnormal($bar['tanggal']), PHPExcel_Cell_DataType::TYPE_STRING);
            $ws->setCellValueExplicit('C' . $row, $div, PHPExcel_Cell_DataType::TYPE_STRING);
            $ws->setCellValueExplicit('D' . $row, $bar['namamandor'], PHPExcel_Cell_DataType::TYPE_STRING);
            $ws->setCellValueExplicit('E' . $row, $bar['hapanen'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $ws->setCellValueExplicit('F' . $row, $bar['posting'] == 1 ? 'Posted' : 'Belum Posting', PHPExcel_Cell_DataType::TYPE_STRING);
            $ws->setCellValueExplicit('G' . $row, $bar['posting'] == 1 ? (string)$bar['namaposting'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $row++;
        }
        $ws->setCellValue('A' . $row, 'Total');
        $ws->setCellValueExplicit('E' . $row, $total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $ws->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $ws->getStyle('E5:E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="HaPanen_' . date('YmdHis') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
        break;

    case 'pdf':
        require_once('lib/fpdf.php');
        $rows = hapanenExportRows(hapanenFilterWhere($karyawansch, $tglsch, $unitsch, $periodesch));

        class PDFHapanen extends FPDF
        {
            public $judul = '';
            public $info = '';
            public $lebar = array(10, 24, 72, 54, 30, 24, 46);
            public $kolom = array('No', 'Tanggal', 'Divisi', 'Mandor Panen', 'Total Luas (Ha)', 'Status', 'Diposting Oleh');

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

        $pdf = new PDFHapanen('L', 'mm', 'A4');
        $pdf->judul = 'REKAP HA PANEN';
        $pdf->info = hapanenFilterInfo($nmorg, $karyawansch, $tglsch, $unitsch, $periodesch);
        $pdf->AliasNbPages();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 8);

        $w = $pdf->lebar;
        $no = 0;
        $total = 0;
        foreach ($rows as $bar) {
            $no++;
            $total += $bar['hapanen'];
            $div = $bar['subbagian'] != '' ? $bar['subbagian'] . " - " . $nmorg[$bar['subbagian']] : "-";
            $pdf->Cell($w[0], 5, $no, 1, 0, 'C');
            $pdf->Cell($w[1], 5, tanggalnormal($bar['tanggal']), 1, 0, 'C');
            $pdf->Cell($w[2], 5, substr($div, 0, 44), 1, 0, 'L');
            $pdf->Cell($w[3], 5, substr($bar['namamandor'], 0, 34), 1, 0, 'L');
            $pdf->Cell($w[4], 5, number_format($bar['hapanen'], 2), 1, 0, 'R');
            $pdf->Cell($w[5], 5, $bar['posting'] == 1 ? 'Posted' : 'Belum Posting', 1, 0, 'C');
            $pdf->Cell($w[6], 5, $bar['posting'] == 1 ? substr((string)$bar['namaposting'], 0, 28) : '', 1, 1, 'L');
        }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w[0] + $w[1] + $w[2] + $w[3], 6, 'Total', 1, 0, 'C');
        $pdf->Cell($w[4], 6, number_format($total, 2), 1, 0, 'R');
        $pdf->Cell($w[5] + $w[6], 6, '', 1, 1);
        $pdf->Output('HaPanen_' . date('YmdHis') . '.pdf', 'I');
        exit;
        break;

    case 'html':
        $tTotJjg = $tluaspanen = $tJjgBesar = $tJjgKecil = 0;
        $totDenda = [];
        // Get Kode Denda Panen
        $sDenda = "SELECT id AS nourut, kodedenda, deskripsi, status FROM " . $dbname . ".kebun_5kodedendapanen order by id asc";
        $rDenda = fetchData($sDenda);
        // Get Jumlah Kode Denda Panen
        $countDenda = count($rDenda);

        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['divisikary'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['hasilkerja2'] . " <br> Panen (HA)</td>
        </tr>";
        $tab .= "</thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_rekaphancakpanen_vw where nikmandor='" . $nik . "' and tanggal='" . $tgl2 . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no += 1;
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center>" . $no . "</td>";
            $tab .= "<td align=left>" . getKary($bar['nik'], 'nik') . " - " . getNamaKaryawan($bar['nik']) . "</td>";
            $tab .= "<td align=left>" . getNamaOrg(getKary($bar['nik'], 'subbagian')) . "</td>";
            $tab .= "<td align=center>" . $bar['kodeorg'] . " - " . $nmindk[$bar['kodeorg']] . "</td>";
            $tab .= "<td align=right>" . @number_format($bar['hapanen'], 2) . "</td>";
            @$tTotJjg += $bar['totaljjg'];
            @$tluaspanen += $bar['hapanen'];
            @$tJjgBesar += $bar['jjgbuahbesar'];
            @$tJjgKecil += $bar['jjgbuahkecil'];
        }
        $tab .= "</tr>";
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=center colspan=4><b>" . $_SESSION['lang']['total'] . "</td>";
        $tab .= "<td align=right><b>" . @number_format($tluaspanen, 2) . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";
        echo $tab;
        break;

    case 'delete':
        $str = "delete from " . $dbname . ".kebun_rekaphancakpanen where tanggal='" . $tgl2 . "' and nikmandor='" . $nik . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'posting':
        $str = "UPDATE $dbname.kebun_rekaphancakpanen SET posting='1', postingby='" . $_SESSION['standard']['userid'] . "' 
                WHERE tanggal='" . $tgl2 . "' AND nikmandor='" . $nik . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'loaddatadetail':
        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:905px>
            <thead>
                <tr class=rowheader>
                <th align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</th>
                <th align=center rowspan='2' style='width:100px;'>" . $_SESSION['lang']['luaspanen'] . "</th>
                <th align=center rowspan='2' width=50px>" . $_SESSION['lang']['action'] . "</th>
                </tr>
            </thead>";
        $no = 0;
        $tluaspanen = $tjjgbesar = $tjjgkecil = $totTtljjg = 0;
        if ($mode == "edit") {
            $str = "select * from " . $dbname . ".kebun_rekaphancakpanen_vw where tanggal='" . $tgl2 . "' and nikmandor='" . $nik . "'";
        } else {
            $str = "select * from " . $dbname . ".kebun_rekaphancakpanen_vw where tanggal='" . $tgl . "' and nikmandor='" . $nik . "'";
        }
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no += 1;
            $tab .= "<tr class=rowcontent>
                    <td align=center>" . getIndukBlok($bar['kodeorg']) . "</td>
                    <td hidden id=kodeorgd" . $no . ">" . $bar['kodeorg'] . "</td>
                    <td id=tgld" . $no . " align=center>" . $bar['tanggal'] . "</td>
                    <td>" . getNamaKaryawan($bar['nik']) . " - " . getKary($bar['nik'], 'nik') . " - " . getNamaOrg(getKary($bar['nik'], 'subbagian')) . "</td>
                    <td hidden id=nikd" . $no . ">" . $bar['nik'] . "</td>
                    <td hidden id=nikmandord" . $no . ">" . $bar['nikmandor'] . "</td>
                    <td align=right>" . $bar['hapanen'] . "</td>";
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
            $tluaspanen += $bar['hapanen'];;
        }
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=center colspan=3> <b>" . $_SESSION['lang']['total'] . "</b> </td>";
        $tab .= "<td align=right> <b>" . @number_format($tluaspanen, 2) . "</b> </td>";
        $tab .= "<td></td>";
        $tab .= "</tr>";
        $tab .= "</table>";
        echo $tab;
        break;

    case 'detail':
        // Cek Apakah Datanya sudah diposting
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_rekaphancakpanen_vw where tanggal='" . $tgl . "' and nikmandor='" . $nik . "' and posting='1'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            // Jika ada data sudah diposting maka tidak bisa melakukan pengisian data
            exit("Error : Data untuk Tanggal: " . tanggalnormal($tgl) . " <br> Dengan Mandor " . getNamaKaryawan($nik) . " sudah di posting");
        }

        OPEN_BOX();
        echo "
        <fieldset>
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
        <table border=0 cellpadding=3 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <th align=center rowspan='2' colspan=2>" . $_SESSION['lang']['kodeblok'] . "</th>
            <th align=center rowspan='2' style='width:100px;'>" . $_SESSION['lang']['luasareaproduktif'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</th>
            <th align=center rowspan='2' style='width:100px;'>" . $_SESSION['lang']['luaspanen'] . "</th>
            <th align=center rowspan='2' width=50px>" . $_SESSION['lang']['action'] . "</th>
        </tr>
        </thead>";


        if (getindukPT($_SESSION['empl']['lokasitugas']) == 'CAR' or getindukPT($_SESSION['empl']['lokasitugas']) == 'LAN') {
            $dataunitx = '';
            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='CAR'";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($dataunitx == "") {
                    $dataunitx .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }

            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='LAN' ";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($dataunitx == "") {
                    $dataunitx .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }

            ## Ambil divisi
            $datadivisix = '';
            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk in (" . $dataunitx . ") and tipe in ('KEBUN','AFDELING','BIBITAN')";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($datadivisix == "") {
                    $datadivisix .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $datadivisix .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }
        }

        if (getindukPT($_SESSION['empl']['lokasitugas']) == 'DMA' or getindukPT($_SESSION['empl']['lokasitugas']) == 'MHA') {
            $dataunitx = '';
            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='DMA'";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($dataunitx == "") {
                    $dataunitx .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }

            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='MHA'";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($dataunitx == "") {
                    $dataunitx .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }

            ## Ambil divisi
            $datadivisix = '';
            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk in (" . $dataunitx . ") and tipe in ('KEBUN','AFDELING','BIBITAN')";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($datadivisix == "") {
                    $datadivisix .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $datadivisix .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }
        }

        if (getindukPT($_SESSION['empl']['lokasitugas']) == 'PPP') {
            $dataunitx = '';
            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='PPP' ";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($dataunitx == "") {
                    $dataunitx .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }

            ## Ambil divisi
            $datadivisix = '';
            $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk in (" . $dataunitx . ") and tipe in ('KEBUN','AFDELING','BIBITAN')";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($datadivisix == "") {
                    $datadivisix .= "'" . $val['kodeorganisasi'] . "'";
                } else {
                    $datadivisix .= ",'" . $val['kodeorganisasi'] . "'";
                }
            }
        }


        $optBlok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $str = "SELECT indukblok,SUM(luasareaproduktif) AS luasareaproduktif 
        FROM $dbname.setup_blok WHERE (" . date(("Y")) . " - tahuntanam >= 3) AND statusblok NOT IN ('BBT','TB') and indukblok in (	select indukblok from " . $dbname . ".organisasi where induk in (" . $datadivisix . ") and tipe in ('BLOK'))
        group by indukblok ORDER BY SUBSTR(indukblok,1,6) asc, SUBSTR(indukblok,7,3) asc";
        $res = fetchData($str);
        foreach ($res as $blk) {
            $d = substr($blk['indukblok'], 0, 6);
            if ($d != $n) {
                $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $d . "'");
                $optBlok .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
            }
            $optBlok .= "<option value='" . $blk['indukblok'] . "'>" . getIndukBlok($blk['indukblok']) . " - " . getNamaOrg(substr($blk['indukblok'], 0, 6)) . "</option>";
            $n = $d;
            if ($d != $n) {
                $optBlok .= "</optgroup>";
            }
        }

        $optKary = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $str = "SELECT karyawanid,nik,subbagian,namakaryawan FROM $dbname.datakaryawan
        WHERE 1=1 and lokasitugas in (" . $dataunitx . ") and (tanggalkeluar = '0000-00-00' or tanggalkeluar > " . $_SESSION['org']['period']['start'] . ") order by subbagian asc, namakaryawan asc";
        $res = fetchData($str);
        foreach ($res as $kary) {
            $d = $kary['subbagian'];
            if ($d != $n) {
                $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $d . "'");
                $optKary .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
            }
            $optKary .= "<option value='" . $kary['karyawanid'] . "'>" . $kary['nik'] . " - " . $kary['namakaryawan'] . "</option>";
            $n = $d;
            if ($d != $n) {
                $optKary .= "</optgroup>";
            }
        }

        echo "<tr class=rowcontent>
                <td colspan=2>
                    <select id=blokx class='select2' onchange=\"getLuas()\">" . $optBlok . "</select>
                </td>
                <td>
                    <input class=myinputtextnumber onkeypress=\"return angka_doang(event);\" id=luasarestax style='width:100px;' value='' disabled>
                </td>
                <td>
                    <select id=nikx class='select2'>" . $optKary . "</select>
                </td>
                <td>
                    <input class=myinputtextnumber onkeypress=\"return angka_doang(event);\" id=luaspnnx style='width:100px;' value=''>
                </td>
                ";
        if ($mode != "edit") {
            echo "<td align=center width=25px> <input type=hidden id=method value='insert'>
                        <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
                    </td>";
        } else {
            echo "<td align=center width=25px> <input type=hidden id=method value='insert'>
                        <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail('edit')\" src='images/save.png'/>
                    </td>";
        }
        echo "</tr>";
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
        if ($mode == 'edit') {
            $arrDataIns = array(
                'kodeorg'       => $kodeorg,
                'nik'           => $nikpemanen,
                'tanggal'       => $tgl2,
                'nikmandor'     => $nik,
                'hapanen'       => $luaspnn,
                'posting'       => '0',
                'postingby'     => '0000000000',
            );
        } else {
            $arrDataIns = array(
                'kodeorg'       => $kodeorg,
                'nik'           => $nikpemanen,
                'tanggal'       => $tgl,
                'nikmandor'     => $nik,
                'hapanen'       => $luaspnn,
                'posting'       => '0',
                'postingby'     => '0000000000',
            );
        }


        $colsInsDt = array();
        $countTphNik = array();
        foreach ($arrDataIns as $key => $row) {
            $colsInsDt[] = $key;
        }
        $insDetail = insertQuery($dbname, "kebun_rekaphancakpanen", $arrDataIns, $colsInsDt);
        try {
            $owlPDO->exec($insDetail);

            // Cek Apakah Data Prestasi Di ERP
            if ($mode == 'edit') {
                $sPres = "SELECT * FROM $dbname.kebun_prestasi_new_vw WHERE nikmandor='" . $nik . "' AND tanggal='" . $tgl2 . "' 
                AND karyawanid='" . $nikpemanen . "' AND kodeorg='" . $kodeorg . "'";
            } else {
                $sPres = "SELECT * FROM $dbname.kebun_prestasi_new_vw WHERE nikmandor='" . $nik . "' AND tanggal='" . $tgl . "' 
                AND karyawanid='" . $nikpemanen . "' AND kodeorg='" . $kodeorg . "'";
            }
            $rPres = fetchData($sPres);
            $countPres = count($rPres);
            // Jika ada datanya per mandor,tanggal,kodeblok,dan nik pemanen
            if ($countPres > 0) {
                foreach ($rPres as $prex) {
                    // Hitung Jumlah TPH Per Blok dan Pemanen
                    $tphNik[$prex['karyawanid']][$prex['kodeorg']][$prex['tph']][$prex['sesi']][] = $prex['tph'];
                    $countTphNik = count($tphNik[$prex['karyawanid']][$prex['kodeorg']]);

                    // Hitung Jumlah Pemanen Ada Berapa Blok
                    $countNik[$prex['karyawanid']][$prex['kodeorg']][] = $prex['karyawanid'];
                }

                foreach ($rPres as $pres) {
                    // Get Nilai Luas Panen Diproporsi
                    // $dividedValue = round($luaspnn / $countTphNik,2);
                    $dividedValue = floor(fixnan($luaspnn / $countTphNik) * 100) / 100;
                    // Rumus Pembagian Luas Panen
                    $luaspnndiprx = (count($countNik[$pres['karyawanid']][$pres['kodeorg']]) == $countTphNik)
                        ? $dividedValue + $luaspnn - ($dividedValue * $countTphNik)
                        : $dividedValue;
                    // Jika luas panennya kosong, maka jalan eksekusi update luas panen
                    if ($pres['luaspanen'] == 0 || $pres['luaspanen'] == '' || $pres['luaspanen'] == null) {
                        $arrUpdLuasPanen = array(
                            "luaspanen" => $luaspnndiprx
                        );
                        $updLuasPanen = updateQuery($dbname, "kebun_prestasi", $arrUpdLuasPanen, "notransaksi='" . $pres['notransaksi'] . "' AND nik='" . $pres['karyawanid'] . "' AND kodeorg='" . $pres['kodeorg'] . "' AND tph='" . $pres['tph'] . "'");
                        $owlPDO->exec($updLuasPanen);
                    }
                }
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'deletedetail':
        $str = "delete from " . $dbname . ".kebun_rekaphancakpanen where kodeorg='" . $kodeorg . "' and tanggal='" . $tgl2 . "' and nik='" . $nikpemanen . "' and nikmandor='" . $nik . "'";
        // exit("Warning: ".$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'getLuas':
        $str = "SELECT SUM(luasareaproduktif) AS luasareaproduktif 
        FROM $dbname.setup_blok WHERE indukblok='" . $blok . "' AND statusblok NOT IN ('BBT','TB')
        AND (" . date(("Y")) . " - tahuntanam >= 3) 
        group by indukblok";
        $res = fetchData($str);

        $luasblok = $res[0]['luasareaproduktif'];

        echo $luasblok;
        break;

    case 'unposting':
        $tertutup = hapanenPeriodeTertutup($tgl2, $nikmandor);
        if ($tertutup != '') {
            exit("Warning : Unposting ditolak, " . $tertutup . " (tanggal " . tanggalnormal($tgl2) . ").");
        }
        $str = "UPDATE $dbname.kebun_rekaphancakpanen SET posting='0', postingby='0' 
                WHERE tanggal='" . $tgl2 . "' AND nikmandor='" . $nikmandor . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
}
