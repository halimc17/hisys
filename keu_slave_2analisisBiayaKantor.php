<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if(isset($_POST['proses'])){
	$proses=$_POST['proses'];
}else{
	$proses=$_GET['proses'];
}

$pt = checkPostGet('pt','');
if($pt == '') {
    exit("Warning: PT harus dipilih");
}
$periode = checkPostGet('periodeId','');
if($periode == '') {
    exit("Warning: Periode harus dipilih");
}

$intiplasma = checkPostGet('intiplasma','');

$inplas="";
if($intiplasma!=''){
    $inplas=" AND inti='".$intiplasma."'";
}

// exit("warning : ".$inplas);

$border = ($proses=='excel') ? 1 : 0;
$bgHeader = ($proses=='excel') ? 'bgcolor=#DEDEDE' : '';

if($proses=='excel'){
    $tabelHtml = "<table cellspacing=1 cellpadding=1 border=0>";
    $tabelHtml .= "<tr><td colspan=11>Laporan Analisis Biaya Kantor</td></tr>";
    $tabelHtml .= "</table>";
} else {
	$tabelHtml = "";
}

$tabelHtml .= "<table cellspacing=1 cellpadding=5 border='".$border."' class=sortable>";
$tabelHtml .= "<thead>";

// Get PT Name
if($pt == '') {
	$namaPt = 'SEMUA PT';
    $ptFilter = "";
} else {
    $ptArr = explode(',', $pt);
    $ptList = "'".implode("','", $ptArr)."'";
    $sqlPT = "SELECT namaorganisasi FROM ".$dbname.".organisasi WHERE kodeorganisasi IN (".$ptList.")";
    $resPT = fetchData($sqlPT);
    $names = [];
    foreach($resPT as $rPT) {
        $names[] = $rPT['namaorganisasi'];
    }
    $namaPt = implode(', ', $names);
    if(empty($namaPt)) $namaPt = "PT. ".$pt;
    $ptFilter = "induk IN (".$ptList.")";
}

$tahunList = explode('-', $periode);
$tahun = isset($tahunList[0]) && $tahunList[0] != '' ? $tahunList[0] : date('Y');

// Level 1 Header
$tabelHtml .= "<tr class=rowheader>";
$tabelHtml .= "<th rowspan=3 align=center ".$bgHeader.">Deskripsi</th>";
$tabelHtml .= "<th rowspan=3 align=center ".$bgHeader.">Unit</th>";
$tabelHtml .= "<th colspan=9 align=center ".$bgHeader.">".$namaPt."</th>";
$tabelHtml .= "</tr>";

// Level 2 Header
$tabelHtml .= "<tr class=rowheader>";
$tabelHtml .= "<th colspan=3 align=center ".$bgHeader.">Bulan ini</th>";
$tabelHtml .= "<th colspan=3 align=center ".$bgHeader.">S/D Bulan ini</th>";
$tabelHtml .= "<th rowspan=2 align=center ".$bgHeader.">YEAR TD SML Y</th>";
$tabelHtml .= "<th rowspan=2 align=center ".$bgHeader.">Anggaran ".$tahun."</th>";
$tabelHtml .= "<th rowspan=2 align=center ".$bgHeader.">%</th>";
$tabelHtml .= "</tr>";

// Level 3 Header
$tabelHtml .= "<tr class=rowheader>";
$tabelHtml .= "<th align=center ".$bgHeader.">Aktual</th>";
$tabelHtml .= "<th align=center ".$bgHeader.">Anggaran</th>";
$tabelHtml .= "<th align=center ".$bgHeader.">%</th>";
$tabelHtml .= "<th align=center ".$bgHeader.">Aktual</th>";
$tabelHtml .= "<th align=center ".$bgHeader.">Anggaran</th>";
$tabelHtml .= "<th align=center ".$bgHeader.">%</th>";
$tabelHtml .= "</tr>";
$tabelHtml .= "</thead><tbody>";

// 1. Definisikan mapping akun per Tipe Unit
$layoutKebun = [
    'Biaya Karyawan' => [
        'Gaji' => ['7110201'],
        'Lembur & Tunjangan' => ['7110202', '7110205', '7110207', '7110208', '7110214', '7110215', '7110216'],
        'THR, Bonus, Insentif' => ['7110206'],
        'BPJS Kesehatan dan Ketenagakerjaan' => ['7110209', '7110210'],
        'Pengobatan dan Kesehatan' => ['7110203', '7110213'],
        'Kesejahteraan, Pendidikan dan Pelatihan' => ['7110204', '7110211', '7110212', '7110117'],
    ],
    'Biaya Umum' => [
        'Pemeliharaan Kendaraan' => ['7140907', '7140908'],
        'Pemeliharaan Mesin' => ['7130110'],
        'Peralatan, Perlengkapan dan Kebutuhan Kantor' => ['7130111', '7130112', '7130113', '7130114'],
        'Listrik, Air, Telepon, Internet' => ['7130105', '7130106', '7130107', '7130108'],
        'Perjalanan Dinas' => ['7130204', '7121000'],
        'Sumbangan, Dana Taktis' => ['7130225', '7130805'],
        'Pemeliharaan Bangunan' => ['7120100', '7130109'],
        'Olahraga, Keagamaan & Hari Besar' => ['7120800', '7130801', '7130802'],
    ],
    'Biaya Administrasi' => [
        'Keamanan' => ['7130115'],
        'Perizinan dan Legalitas' => ['7130223', '7130701', '7130702', '7130703', '7130704', '7130705', '7130706', '7130707', '7130708', '7130709', '7130710', '7130711', '7130712'],
        'Pajak Kendaraan' => ['7130606'],
        'Pajak Bumi dan Bangunan' => ['7130601'],
        'Biaya Cari Karyawan' => ['7121100'],
        'Materai' => ['7130206'],
        'Sumbangan' => ['7130804', '7130805'],
    ],
    'Biaya Investasi' => [
        'Jalan & Jembatan' => ['1270301', '1270302'],
        'Bangunan' => ['1270201'],
        'Inventaris' => ['1270601'],
        'Mesin dan Instalasi' => ['1270501', '1270502'],
        'Kendaraan & Alat Berat' => ['1270401', '1270402'],
        'Kendaraan & Alat Berat (Angsuran)' => ['1270403'],
    ],
    'Biaya Land Clearing' => [
        'Land Clearing' => [],
    ],
];

$layoutKanwil = [
    'Biaya Karyawan' => [
        'Gaji' => ['8210101'],
        'Lembur & Tunjangan PPh 21' => ['8210102', '8210103', '8210105', '8210108', '8210109'],
        'THR, Bonus, Insentif' => ['8210104'],
        'BPJS Kesehatan dan Ketenagakerjaan' => ['8210110', '8210111'],
        'Tunjangan Sewa Rumah' => ['8210107'],
        'Pengobatan dan Kesehatan' => ['8210106', '8221319'],
        'Pendidikan dan Pelatihan' => ['8210113', '8221307'],
    ],
    'Biaya Umum' => [
        'Pemeliharaan Kendaraan' => ['8220807', '8220808'],
        'Pemeliharaan Kantor & Bangunan' => ['8220801', '8220802', '8220813'],
        'Pemeliharaan Mesin' => ['8220809', '8220810'],
        'Asuransi' => ['8220601', '8220604', '8220605', '8220606', '8220607', '8220699'],
        'Peralatan, Perlengkapan dan Kebutuhan Kantor' => ['8220101', '8220102', '8220108', '8220109', '8221315', '8221316', '8221324'],
        'Listrik, Air, Telepon, Internet' => ['8220301', '8220302', '8220201', '8220202'],
        'Perjalanan Dinas' => ['8220901', '8221001', '8221002', '8221003', '8221101'],
        'Biaya Mess' => ['8221317', '8220701'],
        'Rekreasi, Olahraga dan Keagamaan' => ['8221306'],
    ],
    'Biaya Administrasi' => [
        'Keamanan' => ['8221309'],
        'Perizinan dan Legalitas' => ['8220402', '8220403', '8221326'],
        'Pajak' => ['8220404', '8220406'],
        'Pajak Bumi dan Bangunan' => ['8220401'],
        'Adm Bank' => ['8221301'],
        'Materai' => ['8220104'],
        'Taktis & Sumbangan' => ['8220405', '8221401', '8221409', '8221499'],
    ],
    'Biaya Investasi' => [
        'Bangunan' => ['1270201'],
        'Peralatan' => ['1270801'],
        'Kendaraan' => ['1270401', '1270402'],
    ],
];

$layoutHolding = [
    'Biaya Karyawan' => [
        'Gaji, THR, Bonus, Tunjangan, Lembur' => ['8221318', '8221320', '8221323'],
        'BPJS Kesehatan dan Ketenagakerjaan' => ['8210110', '8210111'],
        'Pengobatan dan Kesehatan' => ['8221319', '8221325'],
        'Pendidikan dan Pelatihan' => ['8221307', '8221308'],
    ],
    'Biaya Umum' => [
        'Pemeliharaan Kantor, Bangunan, Kendaraan' => ['8220801', '8220802', '8220803', '8220804', '8220805', '8220806', '8220807', '8220808', '8220809', '8220810', '8220811'],
        'Asuransi' => ['8220601', '8220604', '8220605', '8220606', '8220607', '8220699'],
        'Peralatan, Perlengkapan dan Kebutuhan Kantor' => ['8221303', '8221311', '8221312', '8221313', '8221314', '8221315', '8221316', '8221317', '8221324'],
        'Listrik, Air, Telepon, Internet' => ['8220301', '8220302', '8220201', '8220202'],
        'Perjalanan Dinas' => ['8220901', '8221310'],
        'Transportasi Non Perdin' => ['8221302'],
        'Sewa Kendaraan, Bangunan, Peralatan' => ['8220701', '8220703', '8220704', '8220799'],
        'Jasa Konsultan, Notaris, Lawyer, Auditor' => ['8221321', '8221327'],
    ],
    'Biaya Administrasi' => [
        'Keamanan' => ['8221309'],
        'Perizinan dan Legalitas' => ['8221326'],
        'Pajak Kendaraan' => ['8220406'],
        'Pajak Bumi dan Bangunan' => ['8220401'],
        'Bank' => ['8221301'],
        'Materai' => ['8220104'],
        'Sumbangan' => ['8221304', '8221305', '8221306', '8221401', '8221402', '8221403', '8221404', '8221405', '8221406', '8221407', 
                        '8221408', '8221409', '8221499', '8310101', '8310102', '8310103', '8310104', '8310105', '8310106', '8310199'],
        'Denda dan Bunga Pajak' => ['9310503', '9310504'],
    ],
    'Biaya Bunga Pinjaman' => [
        'Bunga Pinjaman Bank' => ['9310101', '9310202'],
        'Bunga Pinjaman Leasing' => ['9310301'],
        'Bunga Pinjaman Pemegang Saham' => ['9310203'],
        'Bunga Pinjaman Pihak Lain' => ['9310402'],
    ],
    'Biaya Investasi' => [
        'Bangunan' => ['1270201'],
        'Peralatan' => ['1270601'],
        'Kendaraan' => ['1270401', '1270402'],
    ],
];

$allAkun = [];
foreach($layoutKebun as $grp => $items) { foreach($items as $row => $coas) { $allAkun = array_merge($allAkun, $coas); } }
foreach($layoutKanwil as $grp => $items) { foreach($items as $row => $coas) { $allAkun = array_merge($allAkun, $coas); } }
foreach($layoutHolding as $grp => $items) { foreach($items as $row => $coas) { $allAkun = array_merge($allAkun, $coas); } }
$allAkun = array_unique($allAkun);
$inAkun = "'".implode("','", $allAkun)."'";

// 2. Cari Organisasi Terlebih Dahulu (Unit Kantor)
$wherePT = ($ptFilter == "") ? "1=1" : $ptFilter;
$sOrg = "SELECT kodeorganisasi, namaorganisasi, tipe FROM ".$dbname.".organisasi 
         WHERE ".$wherePT.$inplas." AND tipe IN ('KEBUN', 'KANWIL', 'HOLDING') 
         ORDER BY FIELD(tipe, 'KEBUN', 'KANWIL', 'HOLDING')";
$resOrg = fetchData($sOrg);
// exit("warning : ".$sOrg);

// Bangun filter budget berdasarkan list organisasi
$listOrg = [];
$filterOrg = " (1=0) "; // Default false
if(!empty($resOrg)) {
    $filterOrg = " (";
    foreach($resOrg as $idx => $rOrg) {
        $listOrg[] = $rOrg['kodeorganisasi'];
        if($idx > 0) $filterOrg .= " OR ";
        $filterOrg .= "kodeorg LIKE '".$rOrg['kodeorganisasi']."%'";
    }
    $filterOrg .= ") ";
}

// 3. Fetch Data Anggaran
$rawBudget = [];
$blnNum = str_pad((int)substr($periode, 5, 2), 2, "0", STR_PAD_LEFT); 
$blnInt = (int)$blnNum; 

// Menggunakan filter organisasi spesifik dan grouping yang lebih aman
$sBgt = "SELECT kodeorg, noakun, 
         sum(rupiah) as anggaran_tahun,
         sum(rp01) as rp01, sum(rp02) as rp02, sum(rp03) as rp03, sum(rp04) as rp04, 
         sum(rp05) as rp05, sum(rp06) as rp06, sum(rp07) as rp07, sum(rp08) as rp08, 
         sum(rp09) as rp09, sum(rp10) as rp10, sum(rp11) as rp11, sum(rp12) as rp12
         FROM ".$dbname.".bgt_budget_detail 
         WHERE tahunbudget='".$tahun."' AND ".$filterOrg." AND noakun IN (".$inAkun.")
         GROUP BY kodeorg, noakun";
$resBgt = fetchData($sBgt);
foreach($resBgt as $r) {
    // Cari unit mana yang cocok dengan record budget ini (prefix matching)
    $unitMatch = "";
    foreach($listOrg as $oCode) {
        if(strpos($r['kodeorg'], $oCode) === 0) {
            $unitMatch = $oCode;
            break;
        }
    }
    if($unitMatch == "") continue;

    if(!isset($rawBudget[$unitMatch][$r['noakun']])) {
        $rawBudget[$unitMatch][$r['noakun']] = [
            'anggaran_tahun' => 0, 'bulan_anggaran' => 0, 'sd_anggaran' => 0
        ];
    }
    $rawBudget[$unitMatch][$r['noakun']]['anggaran_tahun'] += $r['anggaran_tahun'];
    $rawBudget[$unitMatch][$r['noakun']]['bulan_anggaran'] += $r['rp'.$blnNum];
    
    $totSD = 0;
    for($i = 1; $i <= $blnInt; $i++) {
        $monthStr = str_pad($i, 2, "0", STR_PAD_LEFT);
        $totSD += $r['rp'.$monthStr];
    }
    $rawBudget[$unitMatch][$r['noakun']]['sd_anggaran'] += $totSD;
}

// 3.5 Fetch Data Anggaran Hektar dari bgt_blok
$rawBgtArea = [];
$filterBlok = str_replace("kodeorg", "kodeblok", $filterOrg);
$sBgtArea = "SELECT kodeblok, statusblok, hathnini FROM ".$dbname.".bgt_blok 
             WHERE tahunbudget='".$tahun."' AND ".$filterBlok;
$resBgtArea = fetchData($sBgtArea);
foreach($resBgtArea as $r) {
    $unitMatch = "";
    foreach($listOrg as $oCode) {
        if(strpos($r['kodeblok'], $oCode) === 0) {
            $unitMatch = $oCode;
            break;
        }
    }
    if($unitMatch == "") continue;
    
    $status = strtoupper($r['statusblok']);
    if(!isset($rawBgtArea[$unitMatch][$status])) $rawBgtArea[$unitMatch][$status] = 0;
    $rawBgtArea[$unitMatch][$status] += $r['hathnini'];
}

// 4. Fetch Data Aktual (Realisasi) dari keu_jurnaldt_vw
$rawActual = [];
$sAct = "SELECT kodeorg, noakun, periode, sum(jumlah) as total
         FROM ".$dbname.".keu_jurnaldt_vw
         WHERE periode <= '".$periode."' AND periode LIKE '".$tahun."-%' 
           AND ".$filterOrg." AND noakun IN (".$inAkun.")
         GROUP BY kodeorg, noakun, periode";
$resAct = fetchData($sAct);
foreach($resAct as $r) {
    // Cari unit mana yang cocok (prefix matching)
    $unitMatch = "";
    foreach($listOrg as $oCode) {
        if(strpos($r['kodeorg'], $oCode) === 0) {
            $unitMatch = $oCode;
            break;
        }
    }
    if($unitMatch == "") continue;

    if(!isset($rawActual[$unitMatch][$r['noakun']])) {
        $rawActual[$unitMatch][$r['noakun']] = [
            'bulan_aktual' => 0, 'sd_aktual' => 0
        ];
    }
    
    // Nilai Bulan Ini
    if($r['periode'] == $periode) {
        $rawActual[$unitMatch][$r['noakun']]['bulan_aktual'] += $r['total'];
    }
    // Nilai S/D Bulan Ini (semua yang ditarik query adalah periode <= chosen_periode)
    $rawActual[$unitMatch][$r['noakun']]['sd_aktual'] += $r['total'];
}

// 5. Fetch Data Aktual Tahun Lalu (YEAR TD SML Y)
$tahunLalu = (int)$tahun - 1;
$periodeLalu = $tahunLalu . "-" . substr($periode, 5, 2);
$rawLastYear = [];
$sLast = "SELECT kodeorg, noakun, sum(jumlah) as total
          FROM ".$dbname.".keu_jurnaldt_vw
          WHERE periode <= '".$periodeLalu."' AND periode LIKE '".$tahunLalu."-%' 
            AND ".$filterOrg." AND noakun IN (".$inAkun.")
          GROUP BY kodeorg, noakun";
$resLast = fetchData($sLast);
foreach($resLast as $r) {
    // Cari unit mana yang cocok (prefix matching)
    $unitMatch = "";
    foreach($listOrg as $oCode) {
        if(strpos($r['kodeorg'], $oCode) === 0) {
            $unitMatch = $oCode;
            break;
        }
    }
    if($unitMatch == "") continue;
    
    if(!isset($rawLastYear[$unitMatch][$r['noakun']])) {
        $rawLastYear[$unitMatch][$r['noakun']] = 0;
    }
    $rawLastYear[$unitMatch][$r['noakun']] += $r['total'];
}

function getValFromRaw($rawBudget, $rawActual, $rawLastYear, $kantor, $coas) {
    $out = [
        'bulan_aktual' => 0, 'bulan_anggaran' => 0,
        'sd_aktual' => 0, 'sd_anggaran' => 0,
        'year_td' => 0, 'anggaran_tahun' => 0
    ];
    if(!empty($coas)) {
        foreach($coas as $noakun) {
            // Nilai Anggaran
            if(isset($rawBudget[$kantor][$noakun])) {
                $d = $rawBudget[$kantor][$noakun];
                $out['anggaran_tahun'] += $d['anggaran_tahun'];
                $out['bulan_anggaran'] += $d['bulan_anggaran'];
                $out['sd_anggaran'] += $d['sd_anggaran'];
            }
            // Nilai Aktual (Januari s/d Sekarang)
            if(isset($rawActual[$kantor][$noakun])) {
                $a = $rawActual[$kantor][$noakun];
                $out['bulan_aktual'] += $a['bulan_aktual'];
                $out['sd_aktual'] += $a['sd_aktual'];
            }
            // Nilai Aktual Tahun Lalu (Januari s/d Bulan Sama)
            if(isset($rawLastYear[$kantor][$noakun])) {
                $out['year_td'] += $rawLastYear[$kantor][$noakun];
            }
        }
    }
    return $out;
}

function calcPct($act, $bgt) {
    if($bgt == 0) return 0;
    return ($act / $bgt) * 100;
}

$dummyData = [];
// 4. Render Table (Loop per Unit Organisasi)
foreach($resOrg as $rOrg){
    $kKode = $rOrg['kodeorganisasi'];
    $kNama = $rOrg['namaorganisasi'];
    $kTipe = $rOrg['tipe'];
    
    $namaSection = "Kantor";
    if ($kTipe == 'KEBUN') $namaSection = "Kantor Kebun";
    if ($kTipe == 'KANWIL') $namaSection = "Kantor Kanwil";
    if ($kTipe == 'HOLDING') $namaSection = "Kantor Holding";
    
    $keyKantor = $namaSection." ".$kNama." (".$kKode.")";
    $sections = [];
    
    if($kTipe == 'KEBUN') {
        $tmBgt  = isset($rawBgtArea[$kKode]['TM']) ? $rawBgtArea[$kKode]['TM'] : 0;
        $tbmBgt = isset($rawBgtArea[$kKode]['TBM']) ? $rawBgtArea[$kKode]['TBM'] : 0;
        $totalBgt = $tmBgt + $tbmBgt;

        $sections['Hektar'] = [
            'Tanaman Menghasilkan' => ['unit' => 'Ha', 'bulan_aktual' => 0, 'bulan_anggaran' => $tmBgt, 'bulan_persen' => 0, 'sd_aktual' => 0, 'sd_anggaran' => $tmBgt, 'sd_persen' => 0, 'year_td' => 0, 'anggaran_tahun' => $tmBgt, 'persen' => 0],
            'Tanaman Belum Menghasilkan' => ['unit' => 'Ha', 'bulan_aktual' => 0, 'bulan_anggaran' => $tbmBgt, 'bulan_persen' => 0, 'sd_aktual' => 0, 'sd_anggaran' => $tbmBgt, 'sd_persen' => 0, 'year_td' => 0, 'anggaran_tahun' => $tbmBgt, 'persen' => 0],
            'Total Planted Inti' => ['unit' => 'Ha', 'bulan_aktual' => 0, 'bulan_anggaran' => $totalBgt, 'bulan_persen' => 0, 'sd_aktual' => 0, 'sd_anggaran' => $totalBgt, 'sd_persen' => 0, 'year_td' => 0, 'anggaran_tahun' => $totalBgt, 'persen' => 0],
        ];
    }
    
    // Pilih Mapping berdasarkan Tipe Unit
    if ($kTipe == 'KEBUN') {
        $activeLayout = $layoutKebun;
    } else if ($kTipe == 'KANWIL') {
        $activeLayout = $layoutKanwil;
    } else {
        $activeLayout = $layoutHolding;
    }

    $sections['Biaya '.$namaSection] = [];

    foreach($activeLayout as $grpName => $grpItems) {
        $sections['Biaya '.$namaSection]['<b>'.$grpName.'</b>'] = 'SUBHEADING';
        foreach($grpItems as $rowName => $coas) {
            $v = getValFromRaw($rawBudget, $rawActual, $rawLastYear, $kKode, $coas);
            $sections['Biaya '.$namaSection]['&nbsp;&nbsp;&nbsp;'.$rowName] = [
                'unit' => 'Rp', 
                'bulan_aktual' => $v['bulan_aktual'], 
                'bulan_anggaran' => $v['bulan_anggaran'], 
                'bulan_persen' => calcPct($v['bulan_aktual'], $v['bulan_anggaran']), 
                'sd_aktual' => $v['sd_aktual'], 
                'sd_anggaran' => $v['sd_anggaran'], 
                'sd_persen' => calcPct($v['sd_aktual'], $v['sd_anggaran']), 
                'year_td' => $v['year_td'], 
                'anggaran_tahun' => $v['anggaran_tahun'], 
                'persen' => calcPct($v['sd_aktual'], $v['anggaran_tahun'])
            ];
        }
    }
    
    $dummyData[$keyKantor] = $sections;
}

if(empty($dummyData)){
    $tabelHtml .= "<tr class=rowcontent><td colspan=11 align=center>Tidak ada data Kantor Kebun / Kanwil / Holding untuk organisasi ini.</td></tr>";
}

$grandTotalGroup = [];

foreach($dummyData as $kantor => $sections) {
    if($proses == 'excel'){
        $bgKantor = "bgcolor=#DEDEDE";
    } else {
        $bgKantor = "style='background-color:#DEDEDE; font-weight:bold;'";
    }

    $tabelHtml .= "<tr class=rowcontent ".$bgKantor."><td colspan=11><b>Kantor : ".$kantor."</b></td></tr>";
    
    $totalKantor = [
        'bulan_aktual' => 0, 'bulan_anggaran' => 0,
        'sd_aktual' => 0, 'sd_anggaran' => 0,
        'year_td' => 0, 'anggaran_tahun' => 0
    ];
    $totalHektar = 0; 

    foreach($sections as $sectionName => $items) {
        $tabelHtml .= "<tr class=rowcontent><td colspan=11 style='padding-left:10px;'><b>".$sectionName."</b></td></tr>";
        
        foreach($items as $desc => $val) {
            if ($val === 'SUBHEADING') {
                $tabelHtml .= "<tr class=rowcontent><td colspan=11 style='padding-left:20px;'>".$desc."</td></tr>";
                continue;
            }
            if ($desc == 'Total Planted Inti') {
                $totalHektar = $val['bulan_aktual']; 
            }
            if (strpos($sectionName, 'Biaya Kantor') !== false) {
                $totalKantor['bulan_aktual'] += $val['bulan_aktual'];
                $totalKantor['bulan_anggaran'] += $val['bulan_anggaran'];
                $totalKantor['sd_aktual'] += $val['sd_aktual'];
                $totalKantor['sd_anggaran'] += $val['sd_anggaran'];
                $totalKantor['year_td'] += $val['year_td'];
                $totalKantor['anggaran_tahun'] += $val['anggaran_tahun'];
            }
            
            $fmtUnit = $val['unit'];
            
            $tabelHtml .= "<tr class=rowcontent>";
            $tabelHtml .= "<td style='padding-left:20px;'>".$desc."</td>";
            $tabelHtml .= "<td align=center>".$fmtUnit."</td>";
            
            if ($fmtUnit == 'Ha') {
                $tabelHtml .= "<td align=right>".number_format($val['bulan_aktual'], 2, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['bulan_anggaran'], 2, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['bulan_persen'], 2, ',', '.')."</td>";
                
                $tabelHtml .= "<td align=right>".number_format($val['sd_aktual'], 2, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['sd_anggaran'], 2, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['sd_persen'], 2, ',', '.')."</td>";
                
                $tabelHtml .= "<td align=right>".number_format($val['year_td'], 2, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['anggaran_tahun'], 2, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['persen'], 2, ',', '.')."</td>";
            } else {
                $tabelHtml .= "<td align=right>".number_format($val['bulan_aktual'], 0, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['bulan_anggaran'], 0, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['bulan_persen'], 2, ',', '.')."</td>";
                
                $tabelHtml .= "<td align=right>".number_format($val['sd_aktual'], 0, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['sd_anggaran'], 0, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['sd_persen'], 2, ',', '.')."</td>";
                
                $tabelHtml .= "<td align=right>".number_format($val['year_td'], 0, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['anggaran_tahun'], 0, ',', '.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($val['persen'], 2, ',', '.')."</td>";

            }
            $tabelHtml .= "</tr>";
        }
    }
    
    // Total per kantor
    $bgTotKantor = ($proses == 'excel') ? "bgcolor=#80FFCC" : "style='background-color:#80FFCC; font-weight:bold;'";
    $tabelHtml .= "<tr class=rowcontent ".$bgTotKantor.">";
    $tabelHtml .= "<td><b>Total Biaya Kantor - ".$kantor."</b></td>";
    $tabelHtml .= "<td align=center>Rp</td>";
    
    $pBulan = $totalKantor['bulan_anggaran'] > 0 ? ($totalKantor['bulan_aktual'] / $totalKantor['bulan_anggaran'] * 100) : 0;
    $pSD = $totalKantor['sd_anggaran'] > 0 ? ($totalKantor['sd_aktual'] / $totalKantor['sd_anggaran'] * 100) : 0;
    $pTahunan = $totalKantor['anggaran_tahun'] > 0 ? ($totalKantor['sd_aktual'] / $totalKantor['anggaran_tahun'] * 100) : 0;

    $tabelHtml .= "<td align=right>".number_format($totalKantor['bulan_aktual'], 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($totalKantor['bulan_anggaran'], 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($pBulan, 2, ',', '.')."</td>";
    
    $tabelHtml .= "<td align=right>".number_format($totalKantor['sd_aktual'], 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($totalKantor['sd_anggaran'], 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($pSD, 2, ',', '.')."</td>";
    
    $tabelHtml .= "<td align=right>".number_format($totalKantor['year_td'], 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($totalKantor['anggaran_tahun'], 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($pTahunan, 2, ',', '.')."</td>";
    $tabelHtml .= "</tr>";
    
    // Biaya Per Ha
    $haBulanAktual = $totalHektar > 0 ? $totalKantor['bulan_aktual'] / $totalHektar : 0;
    $haBulanAnggaran = $totalHektar > 0 ? $totalKantor['bulan_anggaran'] / $totalHektar : 0;
    $pHaBulan = $haBulanAnggaran > 0 ? ($haBulanAktual / $haBulanAnggaran * 100) : 0;
    
    $haSDAktual = $totalHektar > 0 ? $totalKantor['sd_aktual'] / $totalHektar : 0;
    $haSDAnggaran = $totalHektar > 0 ? $totalKantor['sd_anggaran'] / $totalHektar : 0;
    $pHaSD = $haSDAnggaran > 0 ? ($haSDAktual / $haSDAnggaran * 100) : 0;

    $haYearTD = $totalHektar > 0 ? $totalKantor['sd_aktual'] / $totalHektar : 0;
    $haAnggaranTahun = $totalHektar > 0 ? $totalKantor['anggaran_tahun'] / $totalHektar : 0;
    $pHaTahunan = $haAnggaranTahun > 0 ? ($haYearTD / $haAnggaranTahun * 100) : 0;

    $tabelHtml .= "<tr class=rowcontent style='background-color:#F5F5F5; font-style:italic;'>";
    $tabelHtml .= "<td style='padding-left:20px;'>Biaya per Ha</td>";
    $tabelHtml .= "<td align=center>Rp/Ha</td>";
    $tabelHtml .= "<td align=right>".number_format($haBulanAktual, 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($haBulanAnggaran, 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($pHaBulan, 2, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($haSDAktual, 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($haSDAnggaran, 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($pHaSD, 2, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($haYearTD, 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($haAnggaranTahun, 0, ',', '.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($pHaTahunan, 2, ',', '.')."</td>";
    $tabelHtml .= "</tr>";

    // Spacing
    $tabelHtml .= "<tr class=rowcontent><td colspan=11>&nbsp;</td></tr>";

    if(!isset($grandTotalGroup['bulan_aktual'])) {
        $grandTotalGroup['bulan_aktual'] = 0;
        $grandTotalGroup['bulan_anggaran'] = 0;
        $grandTotalGroup['sd_aktual'] = 0;
        $grandTotalGroup['sd_anggaran'] = 0;
        $grandTotalGroup['year_td'] = 0;
        $grandTotalGroup['anggaran_tahun'] = 0;
        $grandTotalGroup['total_hektar'] = 0;
    }

    $grandTotalGroup['bulan_aktual'] += $totalKantor['bulan_aktual'];
    $grandTotalGroup['bulan_anggaran'] += $totalKantor['bulan_anggaran'];
    $grandTotalGroup['sd_aktual'] += $totalKantor['sd_aktual'];
    $grandTotalGroup['sd_anggaran'] += $totalKantor['sd_anggaran'];
    $grandTotalGroup['year_td'] += $totalKantor['year_td'];
    $grandTotalGroup['anggaran_tahun'] += $totalKantor['anggaran_tahun'];
    
    $grandTotalGroup['total_hektar'] += $totalHektar;
}

// Grand Total Semua Kantor
$bgTotSemua = ($proses == 'excel') ? "bgcolor=#80FFCC" : "style='background-color:#80FFCC; font-weight:bold;'";
$tabelHtml .= "<tr class=rowcontent ".$bgTotSemua.">";
$tabelHtml .= "<td><b>Total Biaya Semua Kantor</b></td>";
$tabelHtml .= "<td align=center>Rp</td>";

$gtBulanPersen = $grandTotalGroup['bulan_anggaran'] > 0 ? ($grandTotalGroup['bulan_aktual'] / $grandTotalGroup['bulan_anggaran'] * 100) : 0;
$gtSDPersen = $grandTotalGroup['sd_anggaran'] > 0 ? ($grandTotalGroup['sd_aktual'] / $grandTotalGroup['sd_anggaran'] * 100) : 0;
$gtTahunanPersen = $grandTotalGroup['anggaran_tahun'] > 0 ? ($grandTotalGroup['sd_aktual'] / $grandTotalGroup['anggaran_tahun'] * 100) : 0;

$tabelHtml .= "<td align=right>".number_format($grandTotalGroup['bulan_aktual'], 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($grandTotalGroup['bulan_anggaran'], 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtBulanPersen, 2, ',', '.')."</td>";

$tabelHtml .= "<td align=right>".number_format($grandTotalGroup['sd_aktual'], 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($grandTotalGroup['sd_anggaran'], 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtSDPersen, 2, ',', '.')."</td>";

$tabelHtml .= "<td align=right>".number_format($grandTotalGroup['year_td'], 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($grandTotalGroup['anggaran_tahun'], 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtTahunanPersen, 2, ',', '.')."</td>";
$tabelHtml .= "</tr>";

// Grand Total Biaya Per Ha
$gtHaBulanAktual = $grandTotalGroup['total_hektar'] > 0 ? $grandTotalGroup['bulan_aktual'] / $grandTotalGroup['total_hektar'] : 0;
$gtHaBulanAnggaran = $grandTotalGroup['total_hektar'] > 0 ? $grandTotalGroup['bulan_anggaran'] / $grandTotalGroup['total_hektar'] : 0;
$gtPHaBulan = $gtHaBulanAnggaran > 0 ? ($gtHaBulanAktual / $gtHaBulanAnggaran * 100) : 0;

$gtHaSDAktual = $grandTotalGroup['total_hektar'] > 0 ? $grandTotalGroup['sd_aktual'] / $grandTotalGroup['total_hektar'] : 0;
$gtHaSDAnggaran = $grandTotalGroup['total_hektar'] > 0 ? $grandTotalGroup['sd_anggaran'] / $grandTotalGroup['total_hektar'] : 0;
$gtPHaSD = $gtHaSDAnggaran > 0 ? ($gtHaSDAktual / $gtHaSDAnggaran * 100) : 0;

$gtHaYearTD = $grandTotalGroup['total_hektar'] > 0 ? $grandTotalGroup['year_td'] / $grandTotalGroup['total_hektar'] : 0;
$gtHaAnggaranTahun = $grandTotalGroup['total_hektar'] > 0 ? $grandTotalGroup['anggaran_tahun'] / $grandTotalGroup['total_hektar'] : 0;
$gtPHaTahunan = $gtHaAnggaranTahun > 0 ? ($gtHaYearTD / $gtHaAnggaranTahun * 100) : 0;

$tabelHtml .= "<tr class=rowcontent style='background-color:#F5F5F5; font-style:italic;'>";
$tabelHtml .= "<td style='padding-left:20px;'>Biaya per Ha Semua Kantor</td>";
$tabelHtml .= "<td align=center>Rp/Ha</td>";
$tabelHtml .= "<td align=right>".number_format($gtHaBulanAktual, 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtHaBulanAnggaran, 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtPHaBulan, 2, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtHaSDAktual, 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtHaSDAnggaran, 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtPHaSD, 2, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtHaYearTD, 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtHaAnggaranTahun, 0, ',', '.')."</td>";
$tabelHtml .= "<td align=right>".number_format($gtPHaTahunan, 2, ',', '.')."</td>";
$tabelHtml .= "</tr>";

$tabelHtml .= "</tbody></table>";

switch($proses)
{
	case'preview':
	    echo $tabelHtml;
	break;
	case'excel':
        $tabelHtml.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $namaFileExcel="Laporan_AnalisisBiayaKantor_".str_replace(',', '_', $pt)."_".$periode;
        if(strlen($tabelHtml)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }
            $handle=fopen("tempExcel/".$namaFileExcel.".xls",'w');
            if(!fwrite($handle,$tabelHtml))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can\'t convert to excel format');
                </script>";
                exit;
            }
            else
            {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$namaFileExcel.".xls';
                </script>";
            }
            fclose($handle);
        }
	break;
	default:
	break;
}
?>
