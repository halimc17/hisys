<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses'])){
	$proses=$_POST['proses'];
}else{
	$proses=$_GET['proses'];
}

$periode = checkPostGet('periodeId','');
$pt = checkPostGet('pt','');
// $intiplasma = checkPostGet('intiplasma','');

// if($intiplasma!=''){
//     $inplas=" AND sb.intiplasma='".$intiplasma."'";
// }

$border = ($proses=='excel') ? 1 : 0;

$bgHeader = ($proses=='excel') ? 'bgcolor=#DEDEDE' : '';

if($proses=='excel'){
    $bgHeader = 'bgcolor=#DEDEDE';
    $tabelHtml = "<style> .angka { mso-number-format:\"\\#\\,\\#\\#0\"; } </style>\n";
    $tabelHtml .= "<table cellspacing=1 cellpadding=1 border=0>";
    $tabelHtml .= "<tr><td colspan=22>Laporan Kinerja Alat Berat</td></tr>";
    $tabelHtml .= "</table>";
}

$tabelHtml .= "<table cellspacing=1 cellpadding=5 border='".$border."' class=sortable>";
$tabelHtml .= "<thead>";

$tabelHtml .= "<tr class=rowheader>";
$tabelHtml .= "<th rowspan=2 align=center ".$bgHeader.">No.</th>";
$tabelHtml .= "<th rowspan=2 align=center ".$bgHeader.">Nama Alat Berat</th>";
$tabelHtml .= "<th rowspan=2 align=center ".$bgHeader.">Tahun</th>";
$tabelHtml .= "<th rowspan=2 align=center ".$bgHeader.">Umur Alat (Tahun)</th>";
$tabelHtml .= "<th colspan=6 align=center ".$bgHeader.">Bahan Bakar dan Oli</th>";
$tabelHtml .= "<th colspan=6 align=center ".$bgHeader.">Sparepart</th>";
$tabelHtml .= "<th colspan=6 align=center ".$bgHeader.">Tenaga Kerja</th>";
$tabelHtml .= "</tr>";

$tabelHtml .= "<tr class=rowheader>";
for($i=1; $i<=3; $i++) {
    $tabelHtml .= "<th align=center ".$bgHeader.">Nama Bahan</th>";
    $tabelHtml .= "<th align=center ".$bgHeader.">Jumlah</th>";
    $tabelHtml .= "<th align=center ".$bgHeader.">Satuan</th>";
    $tabelHtml .= "<th align=center ".$bgHeader.">Rotasi</th>";
    $tabelHtml .= "<th align=center ".$bgHeader.">Harga</th>";
    $tabelHtml .= "<th align=center ".$bgHeader.">Biaya</th>";
}
$tabelHtml .= "</tr>";
$tabelHtml .= "</thead><tbody>";

// --- DATA QUERY & PROCESSING ---

if($periode=='') {
    exit("Warning: Periode harus dipilih!");
}
if($pt=='') {
    exit("Warning: Perusahaan/PT harus dipilih!");
}

$pt_arr = explode(',', $pt);
$pt_wh_blok = [];
$pt_wh_org = [];
foreach($pt_arr as $p) {
    if($p != '') {
        $pt_wh_blok[] = "a.kodeblok LIKE '".$p."%'";
        $pt_wh_org[] = "a.kodeorg LIKE '".$p."%'";
    }
}
$wherePtBlok = " AND (".implode(' OR ', $pt_wh_blok).")";
$wherePtOrg = " AND (".implode(' OR ', $pt_wh_org).")";

$dataMaster = array();

$sQuery = "SELECT 
    b.kodevhc as kode_alat,
    b.detailvhc as nama_alat, 
    b.tahunperolehan as tahun, 
    c.kelompokbarang,
    c.namabarang, 
    MAX(a.satuan) as satuan, 
    SUM(a.jumlah) as jumlah, 
    COUNT(a.jumlah) as rotasi,
    (SUM(a.hartot) / SUM(a.jumlah)) as harga, 
    SUM(a.hartot) as biaya
FROM ".$dbname.".log_transaksi_vw a
INNER JOIN ".$dbname.".vhc_5master b ON a.kodemesin = b.kodevhc
INNER JOIN ".$dbname.".log_5masterbarang c ON a.kodebarang = c.kodebarang 
WHERE a.tanggal LIKE '".$periode."%'
AND b.kelompokvhc IN ('AB', 'MS') ".$wherePtBlok." AND a.tipetransaksi = '5'
GROUP BY 
    b.kodevhc, 
    b.detailvhc, 
    b.tahunperolehan, 
    c.kelompokbarang, 
    c.namabarang
ORDER BY b.kodevhc, c.kelompokbarang, c.namabarang";

// exit('Warning: '.$sQuery);

try {
    $qData = $owlPDO->query($sQuery);
    while($r = $qData->fetch(PDO::FETCH_ASSOC)) {
        
        $kodeAlat = $r['kode_alat'];
        
        if(!isset($dataMaster[$kodeAlat])) {
            $thnBerjalan = (int) substr($periode, 0, 4); 
            $umur = $thnBerjalan - (int)$r['tahun'];
            
            $dataMaster[$kodeAlat] = array(
                'nama_alat' => $r['nama_alat'],
                'tahun'     => $r['tahun'],
                'umur'      => $umur,
                'bbm'       => array(), 
                'sparepart' => array(), 
                'tk'        => array(), 
                'total_biaya_bbm'    => 0,
                'total_biaya_sparepart' => 0,
                'total_biaya_tk'     => 0,
                'grand_total_biaya'  => 0,
                'total_jam_kerja'    => 0, 
                'hour_machine_bbm'   => 0,
                'hour_machine_sparepart'   => 0,
                'hour_machine_tk'    => 0,
                'grand_hour_machine' => 0
            );
        }
        
        $rowBarang = array(
            'nama'   => $r['namabarang'],
            'jumlah' => $r['jumlah'],
            'satuan'   => $r['satuan'],
            'rotasi' => $r['rotasi'], 
            'harga'  => $r['harga'],
            'biaya'  => $r['biaya']
        );
        
        if ($r['kelompokbarang'] == '351' || $r['kelompokbarang'] == '352') {
            $dataMaster[$kodeAlat]['bbm'][] = $rowBarang;
            $dataMaster[$kodeAlat]['total_biaya_bbm'] += $r['biaya'];
        } else {
            $dataMaster[$kodeAlat]['sparepart'][] = $rowBarang;
            $dataMaster[$kodeAlat]['total_biaya_sparepart'] += $r['biaya'];
        }
        
        $dataMaster[$kodeAlat]['grand_total_biaya'] += $r['biaya'];
    }
} catch (PDOException $e) {
    echo "Query Error: " . $e->getMessage();
}

// --- DATA QUERY TENAGA KERJA (GAJI & PREMI) ---
$sQueryTk = "SELECT 
    a.kodevhc AS kode_alat,
    c.detailvhc as nama_alat, 
    c.tahunperolehan as tahun, 
    SUM(CASE WHEN a.posisi = 1 THEN a.upah ELSE 0 END) AS gaji_operator,
    SUM(CASE WHEN a.posisi = 0 THEN a.upah ELSE 0 END) AS gaji_helper,
    SUM(CASE WHEN a.posisi = 1 THEN a.premi ELSE 0 END) AS premi_operator,
    SUM(CASE WHEN a.posisi = 0 THEN a.premi ELSE 0 END) AS premi_helper
FROM ".$dbname.".vhc_runhk_vw a
INNER JOIN ".$dbname.".vhc_5master c ON a.kodevhc = c.kodevhc
WHERE 
    a.tanggal LIKE '".$periode."%'
    ".$wherePtOrg."
    AND c.kelompokvhc IN ('AB', 'MS')
GROUP BY 
    a.kodevhc, c.detailvhc, c.tahunperolehan";

// exit('warning: '.$sQueryTk);

try {
    $qTk = $owlPDO->query($sQueryTk);
    while($rTk = $qTk->fetch(PDO::FETCH_ASSOC)) {
        $kodeAlat = $rTk['kode_alat'];
        
        if(!isset($dataMaster[$kodeAlat])) {
            $thnBerjalan = (int) substr($periode, 0, 4); 
            $umur = $thnBerjalan - (int)$rTk['tahun'];
            
            $dataMaster[$kodeAlat] = array(
                'nama_alat' => $rTk['nama_alat'],
                'tahun'     => $rTk['tahun'],
                'umur'      => $umur,
                'bbm'       => array(), 'sparepart' => array(), 'tk' => array(), 
                'total_biaya_bbm' => 0, 'total_biaya_sparepart' => 0, 'total_biaya_tk' => 0,
                'grand_total_biaya' => 0, 'total_jam_kerja' => 0, 
                'hour_machine_bbm' => 0, 'hour_machine_sparepart' => 0, 'hour_machine_tk' => 0, 'grand_hour_machine' => 0
            );
        }
            
        $gajiOpr = (float)$rTk['gaji_operator'];
        $gajiHelp = (float)$rTk['gaji_helper'];
        
        $premiOpr = (float)$rTk['premi_operator'];
        $premiHelp = (float)$rTk['premi_helper'];
            
            // Masukkan ke array TK jika nilainya > 0
            if ($gajiOpr > 0) {
                $dataMaster[$kodeAlat]['tk'][] = array('nama'=>'Gaji Operator', 'jumlah'=>'', 'satuan'=>'', 'rotasi'=>'', 'harga'=>'', 'biaya'=>$gajiOpr);
                $dataMaster[$kodeAlat]['total_biaya_tk'] += $gajiOpr;
                $dataMaster[$kodeAlat]['grand_total_biaya'] += $gajiOpr;
            }
            if ($gajiHelp > 0) {
                $dataMaster[$kodeAlat]['tk'][] = array('nama'=>'Gaji Helper', 'jumlah'=>'', 'satuan'=>'', 'rotasi'=>'', 'harga'=>'', 'biaya'=>$gajiHelp);
                $dataMaster[$kodeAlat]['total_biaya_tk'] += $gajiHelp;
                $dataMaster[$kodeAlat]['grand_total_biaya'] += $gajiHelp;
            }
            if ($premiOpr > 0) {
                $dataMaster[$kodeAlat]['tk'][] = array('nama'=>'Premi Operator', 'jumlah'=>'', 'satuan'=>'', 'rotasi'=>'', 'harga'=>'', 'biaya'=>$premiOpr);
                $dataMaster[$kodeAlat]['total_biaya_tk'] += $premiOpr;
                $dataMaster[$kodeAlat]['grand_total_biaya'] += $premiOpr;
            }
            if ($premiHelp > 0) {
                $dataMaster[$kodeAlat]['tk'][] = array('nama'=>'Premi Helper', 'jumlah'=>'', 'satuan'=>'', 'rotasi'=>'', 'harga'=>'', 'biaya'=>$premiHelp);
                $dataMaster[$kodeAlat]['total_biaya_tk'] += $premiHelp;
                $dataMaster[$kodeAlat]['grand_total_biaya'] += $premiHelp;
            }
    }
} catch (PDOException $e) {
    echo "Query TK Error: " . $e->getMessage();
}

// --- DATA QUERY JASA SERVICE ---
$wherePtSrv = str_replace("a.kodeorg", "a_srv.kodeorg", $wherePtOrg);
$sQueryJasa = "SELECT 
    a_srv.kodevhc as kode_alat,
    c.detailvhc as nama_alat, 
    c.tahunperolehan as tahun,
    SUM(gaji.total_gaji / counts.jml_service) as biaya_jasa
FROM ".$dbname.".vhc_pengajuanservice a_srv
INNER JOIN ".$dbname.".vhc_5master c ON a_srv.kodevhc = c.kodevhc
INNER JOIN ".$dbname.".vhc_pengajuanservicedt_karyawan b_srv ON a_srv.nopengajuan = b_srv.nopengajuan
INNER JOIN (
    SELECT b2.karyawanid, COUNT(a2.nopengajuan) as jml_service
    FROM ".$dbname.".vhc_pengajuanservice a2
    INNER JOIN ".$dbname.".vhc_pengajuanservicedt_karyawan b2 ON a2.nopengajuan = b2.nopengajuan
    WHERE a2.tanggalpengajuan LIKE '".$periode."%'
    GROUP BY b2.karyawanid
) counts ON b_srv.karyawanid = counts.karyawanid
INNER JOIN (
    SELECT a.karyawanid, SUM(a.jumlah) as total_gaji
    FROM ".$dbname.".sdm_gaji a
    INNER JOIN ".$dbname.".sdm_ho_component d ON a.idkomponen = d.id
    WHERE a.periodegaji = '".$periode."' AND a.pengali = '1' AND d.plus = '1' AND a.idkomponen != 32
    GROUP BY a.karyawanid
) gaji ON b_srv.karyawanid = gaji.karyawanid
WHERE a_srv.tanggalpengajuan LIKE '".$periode."%'
".$wherePtSrv."
AND c.kelompokvhc IN ('AB', 'MS')
GROUP BY a_srv.kodevhc, c.detailvhc, c.tahunperolehan";

// exit('warning:'.$sQueryJasa);

try {
    $qJasa = $owlPDO->query($sQueryJasa);
    while($rJasa = $qJasa->fetch(PDO::FETCH_ASSOC)) {
        $kodeAlat = $rJasa['kode_alat'];
        
        if(!isset($dataMaster[$kodeAlat])) {
            $thnBerjalan = (int) substr($periode, 0, 4); 
            $umur = $thnBerjalan - (int)$rJasa['tahun'];
            
            $dataMaster[$kodeAlat] = array(
                'nama_alat' => $rJasa['nama_alat'],
                'tahun'     => $rJasa['tahun'],
                'umur'      => $umur,
                'bbm'       => array(), 'sparepart' => array(), 'tk' => array(), 
                'total_biaya_bbm' => 0, 'total_biaya_sparepart' => 0, 'total_biaya_tk' => 0,
                'grand_total_biaya' => 0, 'total_jam_kerja' => 0, 
                'hour_machine_bbm' => 0, 'hour_machine_sparepart' => 0, 'hour_machine_tk' => 0, 'grand_hour_machine' => 0
            );
        }
            
        $biayaJasa = (float)$rJasa['biaya_jasa'];
        
        if ($biayaJasa > 0) {
            $dataMaster[$kodeAlat]['sparepart'][] = array('nama'=>'Jasa Service', 'jumlah'=>'', 'satuan'=>'', 'rotasi'=>'', 'harga'=>'', 'biaya'=>$biayaJasa);
            $dataMaster[$kodeAlat]['total_biaya_sparepart'] += $biayaJasa;
            $dataMaster[$kodeAlat]['grand_total_biaya'] += $biayaJasa;
        }
    }
} catch (PDOException $e) {
    echo "Query Jasa Error: " . $e->getMessage();
}

// --- DATA QUERY JAM KERJA ---
$sQueryJam = "SELECT 
    a.kodevhc as kode_alat,
    c.detailvhc as nama_alat, 
    c.tahunperolehan as tahun, 
    SUM(b.jumlah) as total_jam
FROM ".$dbname.".vhc_runht a
INNER JOIN ".$dbname.".vhc_rundt b ON a.notransaksi = b.notransaksi
INNER JOIN ".$dbname.".vhc_5master c ON a.kodevhc = c.kodevhc
WHERE 
    a.tanggal LIKE '".$periode."%'
    ".$wherePtOrg."
    AND c.kelompokvhc IN ('AB', 'MS')
GROUP BY 
    a.kodevhc, c.detailvhc, c.tahunperolehan";
// exit('warning:'.$sQueryJam);
try {
    $qJam = $owlPDO->query($sQueryJam);
    while($rJam = $qJam->fetch(PDO::FETCH_ASSOC)) {
        $kodeAlat = $rJam['kode_alat'];
        
        if(!isset($dataMaster[$kodeAlat])) {
            $thnBerjalan = (int) substr($periode, 0, 4); 
            $umur = $thnBerjalan - (int)$rJam['tahun'];
            
            $dataMaster[$kodeAlat] = array(
                'nama_alat' => $rJam['nama_alat'],
                'tahun'     => $rJam['tahun'],
                'umur'      => $umur,
                'bbm'       => array(), 'sparepart' => array(), 'tk' => array(), 
                'total_biaya_bbm' => 0, 'total_biaya_sparepart' => 0, 'total_biaya_tk' => 0,
                'grand_total_biaya' => 0, 'total_jam_kerja' => 0, 
                'hour_machine_bbm' => 0, 'hour_machine_sparepart' => 0, 'hour_machine_tk' => 0, 'grand_hour_machine' => 0
            );
        }
            
        $jam = (float)$rJam['total_jam'];
        $dataMaster[$kodeAlat]['total_jam_kerja'] = $jam;
        
        if($jam > 0) {
            $dataMaster[$kodeAlat]['hour_machine_bbm'] = $dataMaster[$kodeAlat]['total_biaya_bbm'] / $jam;
            $dataMaster[$kodeAlat]['hour_machine_sparepart'] = $dataMaster[$kodeAlat]['total_biaya_sparepart'] / $jam;
            $dataMaster[$kodeAlat]['hour_machine_tk'] = $dataMaster[$kodeAlat]['total_biaya_tk'] / $jam;
            $dataMaster[$kodeAlat]['grand_hour_machine'] = $dataMaster[$kodeAlat]['grand_total_biaya'] / $jam;
        }
    }
} catch (PDOException $e) {
    echo "Query Jam Kerja Error: " . $e->getMessage();
}

$dataFinal = array();

foreach($dataMaster as $kodeAlat => $alat) {
    $jmlBbm = count($alat['bbm']);
    $jmlSp  = count($alat['sparepart']);
    $jmlTk  = count($alat['tk']); 
    
    $maxRows = max($jmlBbm, $jmlSp, $jmlTk, 1); 
    
    for($i = 0; $i < $maxRows; $i++) {
        $blankRow = array('nama'=>'', 'jumlah'=>'', 'satuan'=>'', 'rotasi'=>'', 'harga'=>'', 'biaya'=>'');
        if(!isset($alat['bbm'][$i]))       $alat['bbm'][$i] = $blankRow;
        if(!isset($alat['sparepart'][$i])) $alat['sparepart'][$i] = $blankRow;
        if(!isset($alat['tk'][$i]))        $alat['tk'][$i] = $blankRow;
    }
    
    $alat['maxRows'] = $maxRows;
    $dataFinal[] = $alat;
}

if(empty($dataFinal)) {
    $tabelHtml .= "<tr class=rowcontent><td colspan=22 align=center>Data Empty</td></tr>";
} else {
    $no = 1;
    $countData = count($dataFinal);
    foreach($dataFinal as $row) {
        $maxRows = $row['maxRows'];
        for($i=0; $i<$maxRows; $i++) {
            $tabelHtml .= "<tr class=rowcontent>";
            
            if($i == 0) {
                $tabelHtml .= "<td align=center valign=top rowspan=".$maxRows.">".$no."</td>";
                $tabelHtml .= "<td align=left valign=top rowspan=".$maxRows.">".$row['nama_alat']."</td>";
                $tabelHtml .= "<td align=center valign=top rowspan=".$maxRows.">".$row['tahun']."</td>";
                $tabelHtml .= "<td align=center valign=top rowspan=".$maxRows.">".$row['umur']."</td>";
            }
            
            // Helper Format Angka
            $fmtHarga = function($val) use ($proses) {
                if ($val == '') return '';
                if ($proses == 'excel') {
                    return str_replace('.', ',', (string)$val); 
                } else {
                    return number_format($val, 2, ',', '.');
                }
            };
            
            $fmtBiaya = function($val) use ($proses) {
                if ($val == '') return '';
                if ($proses == 'excel') {
                    return str_replace('.', ',', (string)$val); 
                }
                return number_format($val, 0, ',', '.');
            };

            $fmtBiayaTk = function($val) use ($proses) {
                if ($val == '') return '';
                if ($proses == 'excel') {
                    return str_replace('.', ',', (string)$val); 
                }
                return number_format($val, 2, ',', '.');
            };

            $fmtJumlah = function($val) use ($proses) {
                if ($val == '') return '';
                if ($proses == 'excel') {
                    return str_replace('.', ',', (string)$val); 
                }
                return number_format($val, 2, ',', '.');
            };

            $fmtRotasi = function($val) use ($proses) {
                if ($val == '') return '';
                if ($proses == 'excel') {
                    return str_replace('.', ',', (string)$val); 
                }
                return number_format($val, 0, ',', '.');
            };

            $cls = ($proses=='excel') ? "class='angka'" : "";

            // BBM
            $tabelHtml .= "<td align=left>".$row['bbm'][$i]['nama']."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtJumlah($row['bbm'][$i]['jumlah'])."</td>";
            $tabelHtml .= "<td align=left>".$row['bbm'][$i]['satuan']."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtRotasi($row['bbm'][$i]['rotasi'])."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtHarga($row['bbm'][$i]['harga'])."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtBiaya($row['bbm'][$i]['biaya'])."</td>";
            
            // Sparepart
            $tabelHtml .= "<td align=left>".$row['sparepart'][$i]['nama']."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtJumlah($row['sparepart'][$i]['jumlah'])."</td>";
            $tabelHtml .= "<td align=left>".$row['sparepart'][$i]['satuan']."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtRotasi($row['sparepart'][$i]['rotasi'])."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtHarga($row['sparepart'][$i]['harga'])."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtBiaya($row['sparepart'][$i]['biaya'])."</td>";
            
            // Tenaga Kerja
            $tabelHtml .= "<td align=left>".$row['tk'][$i]['nama']."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtJumlah($row['tk'][$i]['jumlah'])."</td>";
            $tabelHtml .= "<td align=left>".$row['tk'][$i]['satuan']."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtRotasi($row['tk'][$i]['rotasi'])."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtHarga($row['tk'][$i]['harga'])."</td>";
            $tabelHtml .= "<td align=right {$cls}>".$fmtBiayaTk($row['tk'][$i]['biaya'])."</td>";
            
            $tabelHtml .= "</tr>";
        }
        
        $fmtTotal = function($val) use ($proses) {
            if ($proses == 'excel') return str_replace('.', ',', (string)$val);
            return number_format($val, 0, ',', '.');
        };

        $cls = ($proses=='excel') ? "class='angka'" : "";

        // Total Biaya
        $tabelHtml .= "<tr class=rowcontent style='font-weight:bold; background-color:#c9daf8;'>";
        $tabelHtml .= "<td colspan=3 align=left>Total Biaya</td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['grand_total_biaya'])."</td>";
        $tabelHtml .= "<td colspan=5 align=right></td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['total_biaya_bbm'])."</td>";
        $tabelHtml .= "<td colspan=5 align=right></td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['total_biaya_sparepart'])."</td>";
        $tabelHtml .= "<td colspan=5 align=right></td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['total_biaya_tk'])."</td>";
        $tabelHtml .= "</tr>";
        
        // Total Jam Kerja
        $tabelHtml .= "<tr class=rowcontent style='font-weight:bold; background-color:#fce5cd;'>";
        $tabelHtml .= "<td colspan=3 align=left>Total Jam Kerja</td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['total_jam_kerja'])."</td>";
        $tabelHtml .= "<td colspan=5 align=right></td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['total_jam_kerja'])."</td>";
        $tabelHtml .= "<td colspan=5 align=right></td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['total_jam_kerja'])."</td>";
        $tabelHtml .= "<td colspan=5 align=right></td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['total_jam_kerja'])."</td>";
        $tabelHtml .= "</tr>";
        
        // Hour Machine
        $tabelHtml .= "<tr class=rowcontent style='font-weight:bold; background-color:#efefef;'>";
        $tabelHtml .= "<td colspan=3 align=left>Hour Machine</td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['grand_hour_machine'])."</td>";
        $tabelHtml .= "<td colspan=5 align=right></td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['hour_machine_bbm'])."</td>";
        $tabelHtml .= "<td colspan=5 align=right></td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['hour_machine_sparepart'])."</td>";
        $tabelHtml .= "<td colspan=5 align=right></td>";
        $tabelHtml .= "<td align=right {$cls}>".$fmtTotal($row['hour_machine_tk'])."</td>";
        $tabelHtml .= "</tr>";
        
        // if ($no < $countData) {
        //     $tabelHtml .= "<tr class=rowcontent><td colspan=22 style='background-color:#ffffff;'>&nbsp;</td></tr>";
        // }
        $no++;
    }
}

$tabelHtml .= "</tbody></table>";

switch($proses)
{
	case'preview':
	    echo $tabelHtml;
	break;
	case'excel':
        $tabelHtml.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $namaFileExcel="Laporan_Kinerja_Alat_Berat_".$pt."_".$periode;
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
                parent.window.alert('Can't convert to excel format');
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
