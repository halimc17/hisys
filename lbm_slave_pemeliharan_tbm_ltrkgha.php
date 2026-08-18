<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');



$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$judul = checkPostGet('judul', '');
$afdId = checkPostGet('afdId', '');
$qwe = explode('-', $periode);
$tahun = $qwe[0];
$bulan = $qwe[1];

$optNm = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

if ($unit == '' || $periode == '') {
    exit("Error:Field Required");
}

$optBulan['01'] = $_SESSION['lang']['jan'];
$optBulan['02'] = $_SESSION['lang']['peb'];
$optBulan['03'] = $_SESSION['lang']['mar'];
$optBulan['04'] = $_SESSION['lang']['apr'];
$optBulan['05'] = $_SESSION['lang']['mei'];
$optBulan['06'] = $_SESSION['lang']['jun'];
$optBulan['07'] = $_SESSION['lang']['jul'];
$optBulan['08'] = $_SESSION['lang']['agt'];
$optBulan['09'] = $_SESSION['lang']['sep'];
$optBulan['10'] = $_SESSION['lang']['okt'];
$optBulan['11'] = $_SESSION['lang']['nov'];
$optBulan['12'] = $_SESSION['lang']['dec'];

// building array: dzArr (main data) =========================================================================
// as seen on sdm_slave_2prasarana.php
$dzArr = array();



##update indra
##kegiatannya dibuat dinamis
###lama
//$kodekode=" kodekegiatan in ('621010201', '621010401', '621020101', '621020201')";
//$kodekode2=" kodekegiatan in ('126060201', '126060401', '126070101', '126070201')";
//$kodekode3=" and kodekegiatan in ('621010201', '621010401', '621020101', '621020201', '126060201', '126060401', '126070101', '126070201')";
#bentuk unit / afd
if ($afdId == '') {
    $sortUnit = $unit;
} else {
    $sortUnit = $afdId;
}

##bentuk TM
$iKegtm = "select distinct kodekegiatan from " . $dbname . ".kebun_pakai_material_vw a where"
        . " tanggal like '%" . $tahun . "%' and kodeorg in (select kodeorg from " . $dbname . ".setup_blok where "
        . " kodeorg like '%" . $sortUnit . "%' and statusblok='TM') and "
        . " kodebarang in ('31201011', '31201014', '31201012', '31201006','31201010','31201001') ";


$nKegtm = $owlPDO->query($iKegtm) or die(print " Gagal: " . PDOException::getMessage());
$eKegtm = owlBaris($nKegtm);
$kodekode = "kodekegiatan in (";
$notm = 0;

$nKegtm->setFetchMode(PDO::FETCH_ASSOC);
while ($dKegtm = $nKegtm->fetch()) {
    $notm++;
    if ($notm == $eKegtm) {
        $kodekode.="'" . $dKegtm['kodekegiatan'] . "'";
    } else {
        if($notm=='1'){
             $kodekode.="'" . $dKegtm['kodekegiatan'] . "'";
         }else{
            $kodekode.=",'" . $dKegtm['kodekegiatan'] . "'";
        }
    }
}
$kodekode.=")";



##bentuk tbm
$iKegtbm = "select distinct kodekegiatan from " . $dbname . ".kebun_pakai_material_vw where "
        . " tanggal like '%" . $tahun . "%' and kodeorg in (select kodeorg from " . $dbname . ".setup_blok where "
        . " kodeorg like '%" . $sortUnit . "%' and statusblok='TM') and "
        . " kodebarang in ('31201011', '31201014', '31201012', '31201006','31201010','31201001') ";

$nKegtbm = $owlPDO->query($iKegtbm) or die(print " Gagal: " . PDOException::getMessage());
$eKegtbm = owlBaris($nKegtbm);
$kodekode2 = "kodekegiatan in (";
$notbm = 0;

$nKegtbm->setFetchMode(PDO::FETCH_ASSOC);
while ($dKegtbm = $nKegtbm->fetch()) {
    $notbm++;
    if ($notbm == $eKegtbm) {
        $kodekode2.="'" . $dKegtbm['kodekegiatan'] . "'";
    } else {
        if($notbm=='1'){
            $kodekode2.="'" . $dKegtbm['kodekegiatan'] . "'";
        }else{
            $kodekode2.=",'" . $dKegtbm['kodekegiatan'] . "'";
        }   
    }
}
$kodekode2.=")";


##kegiatan global
$iKegtbmtm = "select distinct kodekegiatan from " . $dbname . ".kebun_pakai_material_vw where "
        . " tanggal like '%" . $tahun . "%' and kodeorg in (select kodeorg from " . $dbname . ".setup_blok where "
        . " kodeorg like '%" . $sortUnit . "%') and "
        . " kodebarang in ('31201011', '31201014', '31201012', '31201006','31201010','31201001') ";

$nKegtbmtm = $owlPDO->query($iKegtbmtm) or die(print " Gagal: " . PDOException::getMessage());
$eKegtbmtm = owlBaris($nKegtbmtm);



$kodekode3 = "and kodekegiatan in (";
$notbmtm = 0;

$nKegtbmtm->setFetchMode(PDO::FETCH_ASSOC);
while ($dKegtbmtm = $nKegtbmtm->fetch()) {
    $notbmtm++;
    if ($notbmtm == $eKegtbmtm) {
        $kodekode3.="'" . $dKegtbmtm['kodekegiatan'] . "'";
    } else {
        if($notbmtm==1){
            $kodekode3.="'" . $dKegtbmtm['kodekegiatan'] . "'";
        }else{
            $kodekode3.=",'" . $dKegtbmtm['kodekegiatan'] . "'";
        }
        
    }
}
$kodekode3.=")";

if ($eKegtbmtm < 1) {
    exit("Error:Data Kosong");
}



##tutup buat kegiatannya
// cari kegiatan
if ($eKegtm != 0) {
    $kegiatan = "SELECT kodekegiatan, namakegiatan,namakegiatan1 FROM " . $dbname . ".setup_kegiatan WHERE " . $kodekode . " order by kodekegiatan";
    $query = $owlPDO->query($kegiatan) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $dzArr[$res['kodekegiatan']]['kode'] = $res['kodekegiatan'];
        $listkegiatan[$res['kodekegiatan']] = $res['kodekegiatan'];
        if ($_SESSION['language'] == 'EN') {
            $kamuskegiatan[$res['kodekegiatan']] = $res['namakegiatan1'];
        } else {
            $kamuskegiatan[$res['kodekegiatan']] = $res['namakegiatan'];
        }
    }
}

if ($eKegtbm != 0) {
    $kegiatan = "SELECT kodekegiatan, namakegiatan,namakegiatan1 FROM " . $dbname . ".setup_kegiatan WHERE " . $kodekode2 . " order by kodekegiatan";

    $query = $owlPDO->query($kegiatan) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $dzArr[$res['kodekegiatan']]['kode'] = $res['kodekegiatan'];
        $listkegiatan2[$res['kodekegiatan']] = $res['kodekegiatan'];
        if ($_SESSION['language'] == 'EN') {
            $kamuskegiatan[$res['kodekegiatan']] = $res['namakegiatan1'];
        } else {
            $kamuskegiatan[$res['kodekegiatan']] = $res['namakegiatan'];
        }
    }
}


// cari barang
$kegiatan = "SELECT kodebarang, namabarang, satuan FROM " . $dbname . ".log_5masterbarang WHERE "
        . "kodebarang in ('31201011', '31201014', '31201012', '31201006','31201010','31201001')";

//. "kodebarang in ('31200049', '31200040', '31200006', '31200035')";
//31200049	GLYPHOSATE ( 480 AS )
//31200040	STARANE 250 EC (BAHAN AKTIF-FLOROKSIPIR)
//31200006	PARAQUAT/PARATOP
//31200035	METIL METSULFURON
##
//31201011	Prima Up
//31201014	Roundup
//31201012	Primaxone
//31201006	Gramaxone
//31201010	Meta Prima 20 WD
//31201001	Agristik

$query = $owlPDO->query($kegiatan) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $kamusbarang[$res['kodebarang']] = $res['namabarang'];
    $satuanbarang[$res['kodebarang']] = $res['satuan'];
}

// ambil data aktual bulan ini
$str = "SELECT kodekegiatan, sum(kwantitasha) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $unit . "%' " . $kodekode3 . "
    GROUP BY kodekegiatan";

if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitasha) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $afdId . "%' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}


$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['aktbin'] = $res['aktual'];
}

// ambil data aktual sampai dengan bulan ini
$str = "SELECT kodekegiatan, sum(kwantitasha) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $unit . "%' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitasha) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $afdId . "%' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['aktsdb'] = $res['aktual'];
}

//lama
//                                  gly         flu         par         met
//$barangbarang=" and kodebarang in ('31200049', '31200040', '31200006', '31200035')";


$barangbarang = " and kodebarang in ('31201011', '31201014', '31201012', '31201006','31201010','31201001')";




//31201011	Prima Up
//31201014	Roundup
//31201012	Primaxone
//31201006	Gramaxone
// ambil data Prima Up bulan ini 31201011 31200018 31200028
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $unit . "%' and kodebarang = '31201011' " . $kodekode3 . "
    GROUP BY kodekegiatan";

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['glybin'] = $res['aktual'];
}

// ambil data gly sampai dengan bulan ini
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $unit . "%' and kodebarang = '31201011' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $afdId . "%' and kodebarang = '31201011' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['glysdb'] = $res['aktual'];
}

// ambil data flo bulan ini 31201014
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $unit . "%' and kodebarang = '31201014' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $afdId . "%' and kodebarang = '31201014' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['flobin'] = $res['aktual'];
}

// ambil data flo sampai dengan bulan ini
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $unit . "%' and kodebarang = '31201014' " . $kodekode3 . "
    GROUP BY kodekegiatan";

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['flosdb'] = $res['aktual'];
}

// ambil data par bulan ini 31201014
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $unit . "%' and kodebarang = '31201012' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $afdId . "%' and kodebarang = '31201012' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['parbin'] = $res['aktual'];
}

// ambil data par sampai dengan bulan ini
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $unit . "%' and kodebarang = '31201012' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $afdId . "%' and kodebarang = '31201012' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['parsdb'] = $res['aktual'];
}

// ambil data met bulan ini 31201006
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $unit . "%' and kodebarang = '31201006' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $afdId . "%' and kodebarang = '31201006' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['metbin'] = $res['aktual'];
}

// ambil data met sampai dengan bulan ini
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $unit . "%' and kodebarang = '31201006' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $afdId . "%' and kodebarang = '31201006' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['metsdb'] = $res['aktual'];
}


######################################################################################3
##tambahan 2 kodebarang###############
// ambil data  bulan ini 31201010	Meta Prima 20 WD
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $unit . "%' and kodebarang = '31201010' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $afdId . "%' and kodebarang = '31201010' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['metabin'] = $res['aktual'];
}

// ambil data sampai dengan bulan ini 31201010	Meta Prima 20 WD
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $unit . "%' and kodebarang = '31201010' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $afdId . "%' and kodebarang = '31201010' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['metasdb'] = $res['aktual'];
}


// ambil data  bulan ini 31201001	Agristik
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $unit . "%' and kodebarang = '31201001' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE tanggal like '" . $periode . "%' and kodeorg like '" . $afdId . "%' and kodebarang = '31201001' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['agribin'] = $res['aktual'];
}

// ambil data sampai dengan bulan ini 31201001	Agristik
$str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $unit . "%' and kodebarang = '31201001' " . $kodekode3 . "
    GROUP BY kodekegiatan";
if ($afdId != '') {
    $str = "SELECT kodekegiatan, sum(kwantitas) as aktual FROM " . $dbname . ".kebun_pakai_material_vw 
    WHERE (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $afdId . "%' and kodebarang = '31201001' " . $kodekode3 . "
    GROUP BY kodekegiatan";
}

$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodekegiatan']]['agrisdb'] = $res['aktual'];
}


// hitung efisiensi kegiatan TM
if (!empty($listkegiatan))
    foreach ($listkegiatan as $keg) {
        @$dzArr[$keg][efiglybin] = $dzArr[$keg]['glybin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efiglysdb] = $dzArr[$keg]['glysdb'] / $dzArr[$keg]['aktsdb'];

        @$dzArr[$keg][efiflobin] = $dzArr[$keg]['flobin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efiflosdb] = $dzArr[$keg]['flosdb'] / $dzArr[$keg]['aktsdb'];

        @$dzArr[$keg][efiparbin] = $dzArr[$keg]['parbin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efiparsdb] = $dzArr[$keg]['parsdb'] / $dzArr[$keg]['aktsdb'];

        @$dzArr[$keg][efimetbin] = $dzArr[$keg]['metbin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efimetsdb] = $dzArr[$keg]['metsdb'] / $dzArr[$keg]['aktsdb'];



        @$dzArr[$keg][efimetabin] = $dzArr[$keg]['metabin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efimetasdb] = $dzArr[$keg]['metasdb'] / $dzArr[$keg]['aktsdb'];

        @$dzArr[$keg][efiagribin] = $dzArr[$keg]['agribin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efiagrisdb] = $dzArr[$keg]['agrisdb'] / $dzArr[$keg]['aktsdb'];
    }

// hitung efisiensi kegiatan TBM
if (!empty($listkegiatan2))
    foreach ($listkegiatan2 as $keg) {
        @$dzArr[$keg][efiglybin] = $dzArr[$keg]['glybin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efiglysdb] = $dzArr[$keg]['glysdb'] / $dzArr[$keg]['aktsdb'];
        @$dzArr[$keg][efiflobin] = $dzArr[$keg]['flobin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efiflosdb] = $dzArr[$keg]['flosdb'] / $dzArr[$keg]['aktsdb'];
        @$dzArr[$keg][efiparbin] = $dzArr[$keg]['parbin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efiparsdb] = $dzArr[$keg]['parsdb'] / $dzArr[$keg]['aktsdb'];
        @$dzArr[$keg][efimetbin] = $dzArr[$keg]['metbin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efimetsdb] = $dzArr[$keg]['metsdb'] / $dzArr[$keg]['aktsdb'];
        @$dzArr[$keg][efimetabin] = $dzArr[$keg]['metabin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efimetasdb] = $dzArr[$keg]['metasdb'] / $dzArr[$keg]['aktsdb'];
        @$dzArr[$keg][efiagribin] = $dzArr[$keg]['agribin'] / $dzArr[$keg]['aktbin'];
        @$dzArr[$keg][efiagrisdb] = $dzArr[$keg]['agrisdb'] / $dzArr[$keg]['aktsdb'];
    }

function numberformat($qwe, $asd) {
    if ($qwe == 0)
        $zxc = '0';
    else {
        $zxc = number_format($qwe, $asd);
    }
    return $zxc;
}

if ($proses == 'excel') {
    $bg = " bgcolor=#DEDEDE";
    $brdr = 1;
    $tab.="<table border=0>
     <tr>
        <td colspan=11 align=left><font size=3>12. " . $judul . "</font></td>
        <td colspan=8 align=right>" . $_SESSION['lang']['bulan'] . " : " . $optBulan[$bulan] . " " . $tahun . "</td>
     </tr> 
     <tr><td colspan=19 align=left>" . $_SESSION['lang']['unit'] . " : " . $optNm[$unit] . " (" . $unit . ")</td></tr>";
    if ($afdId != '') {
        $tab.="<tr><td colspan=19 align=left>" . $_SESSION['lang']['afdeling'] . " : " . $optNm[$afdId] . " (" . $afdId . ")</td></tr>";
    }
    $tab.="</table>";
} else {
    $bg = "";
    $brdr = 0;
}
if ($proses != 'excel')
    $tab.="12. " . $judul;
if ($proses != 'excel')
    $tab.="<br>12. 1. EFISIENSI " . $_SESSION['lang']['pemeltanaman'] . " " . $_SESSION['lang']['tm'] . " (HERBISIDA)";
else {
    $tab.="<br><table border=0>
         <tr><td colspan=11 align=left><font size=3>12. 1. EFISIENSI " . $_SESSION['lang']['pemeltanaman'] . " " . $_SESSION['lang']['tm'] . " (HERBISIDA)</font></td></tr> 
    </table>";
}
$tab.="<table cellpadding=1 cellspacing=1 border=" . $brdr . " class=sortable style='width:100%;'>
    <thead class=rowheader>
    <tr>
    <td align=center rowspan=3 " . $bg . ">" . $_SESSION['lang']['pekerjaan'] . "</td>
    <td align=center colspan=2 " . $bg . ">" . $_SESSION['lang']['aktual'] . " (Ha)</td>
    <td align=center colspan=12 " . $bg . ">" . $_SESSION['lang']['penggunaan'] . " Herbisida</td>
    <td align=center colspan=12 " . $bg . ">" . $_SESSION['lang']['efisiensi'] . " Herbisida</td>
    </tr>
    <tr>
    <td align=center rowspan=2 " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center rowspan=2 " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201011'] . " (" . $satuanbarang['31201011'] . ")</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201014'] . " (" . $satuanbarang['31201014'] . ")</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201012'] . " (" . $satuanbarang['31201012'] . ")</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201006'] . " (" . $satuanbarang['31201006'] . ")</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201010'] . " (" . $satuanbarang['31201010'] . ")</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201001'] . " (" . $satuanbarang['31201001'] . ")</td>    


    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201011'] . " (" . $satuanbarang['31201011'] . "/Ha)</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201014'] . " (" . $satuanbarang['31201014'] . "/Ha)</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201012'] . " (" . $satuanbarang['31201012'] . "/Ha)</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201006'] . " (" . $satuanbarang['31201006'] . "/Ha)</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201010'] . " (" . $satuanbarang['31201010'] . "/Ha)</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201001'] . " (" . $satuanbarang['31201001'] . "/Ha)</td>    

    </tr>
    <tr>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>

<td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
        
<td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>


    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>

<td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
        <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>

    </tr>
    </thead>
    <tbody>
";

$dummy = '';
// excel array content =========================================================================
if (empty($dzArr)) {
    $tab.="<tr class=rowcontent><td colspan=19>Data Empty.</td></tr>";
} else
if (!empty($listkegiatan))
    foreach ($listkegiatan as $keg) {
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td>" . $kamuskegiatan[$keg] . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['aktbin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['aktsdb'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['glybin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['glysdb'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['flobin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['flosdb'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['parbin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['parsdb'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['metbin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['metsdb'], 2) . "</td>";

        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['metabin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['metasdb'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['agribin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['agrisdb'], 2) . "</td>";


        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiglybin], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiglysdb], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiflobin], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiflosdb], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiparbin], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiparsdb], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efimetbin], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efimetsdb], 2) . "</td>";

        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efimetabin], 5) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efimetasdb], 5) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiagribin], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiagrisdb], 2) . "</td>";


        $tab.= "</tr>";
    }
$tab.="</tbody></table>";

//if($proses!='excel')$tab.="<br>12. 2. EFISIENSI PEMELIHARAAN TBM (HERBISIDA)";
if ($proses != 'excel')
    $tab.="<br>12. 2. EFISIENSI " . $_SESSION['lang']['pemeltanaman'] . " " . $_SESSION['lang']['tbm'] . " (HERBISIDA)";
else {
    $tab.="<br><table border=0>
         <tr><td colspan=11 align=left><font size=3>12. 2. EFISIENSI " . $_SESSION['lang']['pemeltanaman'] . " " . $_SESSION['lang']['tbm'] . " (HERBISIDA)</font></td></tr> 
    </table>";
}
$tab.="<table cellpadding=1 cellspacing=1 border=" . $brdr . " class=sortable style='width:100%;'>
    <thead class=rowheader>
    <tr>
    <td align=center rowspan=3 " . $bg . ">" . $_SESSION['lang']['pekerjaan'] . "</td>
    <td align=center colspan=2 " . $bg . ">" . $_SESSION['lang']['aktual'] . " (Ha)</td>
    <td align=center colspan=12 " . $bg . ">" . $_SESSION['lang']['penggunaan'] . " Herbisida</td>
    <td align=center colspan=12 " . $bg . ">" . $_SESSION['lang']['efisiensi'] . " Herbisida</td>
    </tr>
    <tr>
    <td align=center rowspan=2 " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center rowspan=2 " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201011'] . " (" . $satuanbarang['31201011'] . ")</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201014'] . " (" . $satuanbarang['31201014'] . ")</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201012'] . " (" . $satuanbarang['31201012'] . ")</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201006'] . " (" . $satuanbarang['31201006'] . ")</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201010'] . " (" . $satuanbarang['31201010'] . ")</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201001'] . " (" . $satuanbarang['31201001'] . ")</td>   

    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201011'] . " (" . $satuanbarang['31201011'] . "/Ha)</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201014'] . " (" . $satuanbarang['31201014'] . "/Ha)</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201012'] . " (" . $satuanbarang['31201012'] . "/Ha)</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201006'] . " (" . $satuanbarang['31201006'] . "/Ha)</td>
    
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201010'] . " (" . $satuanbarang['31201010'] . "/Ha)</td>
    <td align=center colspan=2 " . $bg . ">" . $kamusbarang['31201001'] . " (" . $satuanbarang['31201001'] . "/Ha)</td>
        
    </tr>
    <tr>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
<td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
        <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
        <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
        <td align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>


    </tr>
    </thead>
    <tbody>
";

// excel array content =========================================================================
if (empty($dzArr)) {
    $tab.="<tr class=rowcontent><td colspan=19>Data Empty.</td></tr>";
} else
if (!empty($listkegiatan2))
    foreach ($listkegiatan2 as $keg) {
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td>" . $kamuskegiatan[$keg] . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['aktbin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['aktsdb'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['glybin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['glysdb'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['flobin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['flosdb'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['parbin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['parsdb'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['metbin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['metsdb'], 2) . "</td>";

        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['metabin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['metasdb'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['agribin'], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg]['agrisdb'], 2) . "</td>";


        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiglybin], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiglysdb], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiflobin], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiflosdb], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiparbin], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiparsdb], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efimetbin], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efimetsdb], 2) . "</td>";


        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efimetabin], 5) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efimetasdb], 5) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiagribin], 2) . "</td>";
        $tab.= "<td align=right>" . @numberformat($dzArr[$keg][efiagrisdb], 2) . "</td>";


        $tab.= "</tr>";
    }
$tab.="</tbody></table>";

switch ($proses) {
    case'preview':
        if ($unit == '' || $periode == '') {
            exit("Error:Field required");
        }
        echo $tab;
        break;

    case'excel':
        if ($unit == '' || $periode == '') {
            exit("Error:Field required");
        }

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
        $dte = date("YmdHis");
        $nop_ = "lbm_pemeliharan_efisiensi_" . $unit . $periode;
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

    case'pdf':
        if ($unit == '' || $periode == '') {
            exit("Error:Field required");
        }

        $cols = 247.5;
        $wkiri = 14;
        $wlain = 5;
        $wlain2 = 4.5;

        class PDF extends FPDF {

            function Header() {
                global $periode;
                global $unit;
                global $optNm;
                global $optBulan;
                global $tahun;
                global $bulan;
                global $dbname;
                global $luas;
                global $wkiri, $wlain, $wlain2, $afdId;
                $width = $this->w - $this->lMargin - $this->rMargin;

                $height = 20;
                $this->SetFillColor(220, 220, 220);
                $this->SetFont('Arial', 'B', 12);

                $this->Cell($width / 2, $height, '12. EFISIENSI  ' . $_SESSION['lang']['pemeltanaman'], NULL, 0, 'L', 1);
                $this->Cell($width / 2, $height, $_SESSION['lang']['bulan'] . " : " . $optBulan[$bulan] . " " . $tahun, NULL, 0, 'R', 1);
                $this->Ln();
                $this->Cell($width, $height, $_SESSION['lang']['unit'] . " : " . $optNm[$unit] . " (" . $unit . ")", NULL, 0, 'L', 1);
                if ($afdId != '') {
                    $this->Ln();
                    $this->Cell($width, $height, $_SESSION['lang']['afdeling'] . " : " . $optNm[$afdId] . " (" . $afdId . ")", NULL, 0, 'L', 1);
                }
                $this->Ln();
                $this->Ln();

                $this->SetFillColor(255, 255, 255);
                $height = 15;
                $this->SetFont('Arial', 'B', 10);
                $this->Cell($width, $height, "12.1 EFISIENSI " . $_SESSION['lang']['pemeltanaman'] . " " . $_SESSION['lang']['TM'] . " (HERBISIDA)", 0, 0, "L", 1);
                $this->Ln();

                $this->SetFillColor(220, 220, 220);
                $this->SetFont('Arial', 'B', 7);
                $this->Cell($wkiri / 100 * $width, $height, '', TRL, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, '', TRL, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, '', TRL, 0, 'C', 1);
                $this->Cell($wlain * 8 / 100 * $width, $height, $_SESSION['lang']['penggunaan'] . ' Herbisida', TRL, 0, 'C', 1);
                $this->Cell($wlain2 * 8 / 100 * $width, $height, $_SESSION['lang']['efisiensi'] . ' Herbisida', TRL, 0, 'C', 1);
                $this->Ln();
                $this->Cell($wkiri / 100 * $width, $height, $_SESSION['lang']['pekerjaan'], RL, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, $_SESSION['lang']['bulanini'], RL, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, 'sd BI', RL, 0, 'C', 1);
                $this->Cell($wlain * 2 / 100 * $width, $height, 'Glyphosate', 1, 0, 'C', 1);
                $this->Cell($wlain * 2 / 100 * $width, $height, 'Flouroksipi', 1, 0, 'C', 1);
                $this->Cell($wlain * 2 / 100 * $width, $height, 'Paraquat', 1, 0, 'C', 1);
                $this->Cell($wlain * 2 / 100 * $width, $height, 'Metsulfuron', 1, 0, 'C', 1);
                $this->Cell($wlain2 * 2 / 100 * $width, $height, 'Glyphosate', 1, 0, 'C', 1);
                $this->Cell($wlain2 * 2 / 100 * $width, $height, 'Flouroksipi', 1, 0, 'C', 1);
                $this->Cell($wlain2 * 2 / 100 * $width, $height, 'Paraquat', 1, 0, 'C', 1);
                $this->Cell($wlain2 * 2 / 100 * $width, $height, 'Metsulfuron', 1, 0, 'C', 1);
                $this->Ln();
                $this->Cell($wkiri / 100 * $width, $height, '', BRL, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, '', BRL, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, '', BRL, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
                $this->Cell($wlain / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
                $this->Cell($wlain2 / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
                $this->Cell($wlain2 / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
                $this->Cell($wlain2 / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
                $this->Cell($wlain2 / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
                $this->Cell($wlain2 / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
                $this->Cell($wlain2 / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
                $this->Cell($wlain2 / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
                $this->Cell($wlain2 / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
                $this->Ln();
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(10, 10, 'Page ' . $this->PageNo() . " / {totalPages}", 0, 0, 'L');
            }

        }

        //================================

        $pdf = new PDF('L', 'pt', 'A4');
        $pdf->AliasNbPages('{totalPages}');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 15;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 6);

// pdf array content =========================================================================
        if (!empty($listkegiatan))
            foreach ($listkegiatan as $keg) {
                $pdf->Cell($wkiri / 100 * $width, $height, $kamuskegiatan[$keg], 1, 0, 'L', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['aktbin'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['aktsdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['glybin'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['glysdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['flobin'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['flosdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['parbin'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['parsdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['metsdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['metsdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiglybin], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiglysdb], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiflobin], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiflosdb], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiparbin], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiparsdb], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efimetbin], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efimetsdb], 2), 1, 0, 'R', 1);
                $pdf->Ln();
            }

        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($width, $height, "12.1 EFISIENSI " . $_SESSION['lang']['pemeltanaman'] . " " . $_SESSION['lang']['tbm'] . " (HERBISIDA)", 0, 0, "L", 1);
        $pdf->Ln();

        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($wkiri / 100 * $width, $height, '', TRL, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, '', TRL, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, '', TRL, 0, 'C', 1);
        $pdf->Cell($wlain * 8 / 100 * $width, $height, $_SESSION['lang']['penggunaan'] . ' Herbisida', TRL, 0, 'C', 1);
        $pdf->Cell($wlain2 * 8 / 100 * $width, $height, $_SESSION['lang']['efisiensi'] . ' Herbisida', TRL, 0, 'C', 1);
        $pdf->Ln();
        $pdf->Cell($wkiri / 100 * $width, $height, $_SESSION['lang']['pekerjaan'], RL, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, $_SESSION['lang']['bulanini'], RL, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, 'sd BI', RL, 0, 'C', 1);
        $pdf->Cell($wlain * 2 / 100 * $width, $height, 'Glyphosate', 1, 0, 'C', 1);
        $pdf->Cell($wlain * 2 / 100 * $width, $height, 'Flouroksipi', 1, 0, 'C', 1);
        $pdf->Cell($wlain * 2 / 100 * $width, $height, 'Paraquat', 1, 0, 'C', 1);
        $pdf->Cell($wlain * 2 / 100 * $width, $height, 'Metsulfuron', 1, 0, 'C', 1);
        $pdf->Cell($wlain2 * 2 / 100 * $width, $height, 'Glyphosate', 1, 0, 'C', 1);
        $pdf->Cell($wlain2 * 2 / 100 * $width, $height, 'Flouroksipi', 1, 0, 'C', 1);
        $pdf->Cell($wlain2 * 2 / 100 * $width, $height, 'Paraquat', 1, 0, 'C', 1);
        $pdf->Cell($wlain2 * 2 / 100 * $width, $height, 'Metsulfuron', 1, 0, 'C', 1);
        $pdf->Ln();
        $pdf->Cell($wkiri / 100 * $width, $height, '', BRL, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, '', BRL, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, '', BRL, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
        $pdf->Cell($wlain / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
        $pdf->Cell($wlain2 / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
        $pdf->Cell($wlain2 / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
        $pdf->Cell($wlain2 / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
        $pdf->Cell($wlain2 / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
        $pdf->Cell($wlain2 / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
        $pdf->Cell($wlain2 / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
        $pdf->Cell($wlain2 / 100 * $width, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
        $pdf->Cell($wlain2 / 100 * $width, $height, 'sd BI', 1, 0, 'C', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetFillColor(255, 255, 255);
// pdf array content =========================================================================
        if (!empty($listkegiatan2))
            foreach ($listkegiatan2 as $keg) {
                $pdf->Cell($wkiri / 100 * $width, $height, $kamuskegiatan[$keg], 1, 0, 'L', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['aktbin'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['aktsdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['glybin'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['glysdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['flobin'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['flosdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['parbin'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['parsdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['metsdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain / 100 * $width, $height, @numberformat($dzArr[$keg]['metsdb'], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiglybin], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiglysdb], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiflobin], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiflosdb], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiparbin], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efiparsdb], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efimetbin], 2), 1, 0, 'R', 1);
                $pdf->Cell($wlain2 / 100 * $width, $height, @numberformat($dzArr[$keg][efimetsdb], 2), 1, 0, 'R', 1);
                $pdf->Ln();
            }

        $pdf->Output();
        break;

    default:
        break;
}
?>
