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
    exit("Error:Field Tidak Boleh Kosong");
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

// areal statement
$sOrg = "select sum(luasareaproduktif) as luas from " . $dbname . ".setup_blok where kodeorg like '" . $unit . "%' and tahuntanam <= '" . $tahun . "'";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $luas = $rOrg['luas'];
}
$jabrwt = array('154','184','241','242','245','250','251','253','254','255','256','268','269');
$jabspv = array('185','186','188','189','190','191','192','193','194','195','196','197','198','199','200','201','202','203','204','205','206','274','275');



// $kantor = " and (kodejabatan between '166' and '181') and (subbagian='' or subbagian is null)";
// $super = " and kodejabatan not in ('154','184','241','242','245','250','251','252','253','254','255','256','268','269','252') and (subbagian<>'' and subbagian is not null)";
// $pelihara = " and kodejabatan in ('154','184','241','242','245','250','251','252','253','254','255','256','268','269')";
// $panen = " and kodejabatan in (252)";

$khl = " and tipekaryawan = 4 ";
$kht = " and ((tipekaryawan in (2,3)) or (sistemgaji='Harian' and tipekaryawan = 6)) ";
$bln = " and ((tipekaryawan = 1) or (sistemgaji='Bulanan' and tipekaryawan = 6)) ";
//$khl=" and tipekaryawan = 4 ";
//$kht=" and tipekaryawan in (2,3) ";
//$bln=" and tipekaryawan = 1 ";
// 0 = staf
// 1 = bulanan
// 2 = kontrak
// 3 = kht
// 4 = khl
// 5 = magang
// 6 = kontrak karyawan usia lanjut

$laki = " and jeniskelamin = 'L'";
$pere = " and jeniskelamin = 'P'";
$addTmbh = "and lokasitugas = '" . $unit . "'";
if ($afdId != '') {
    $addTmbh = " and subbagian='" . $afdId . "'";
}
$backs = " " . $addTmbh . " and tanggalmasuk <= '" . $periode . "-15' and (tanggalkeluar='0000-00-00' or tanggalkeluar>='" . $periode . "-15')";


// langsung panen kht khl
$staf = $stafl = $stafp = 0;
$panenl = $panenll = $panenlp = 0;
$panent = $panentl = $panentp = 0;
$sOrg = "select * from " . $dbname . ".datakaryawan where nik != '' " . $backs;

$kantortl = $kantortp = $kantorbl = $kantorbp = 0;
$kantort = $kantorll = $kantorl = $peliharalp = $peliharal = $kantorlp = 0;
$supertl = $supert = $peliharall = $peliharatl = $peliharatp = 0;
$peliharat = $superbl = $superll = $supertp = $superbp = $superlp = 0;
$superb = $superl = $kantorb = 0;

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    if ($rOrg['alokasi'] == '1') { // staf
        if ($rOrg['jeniskelamin'] == 'L')
            $stafl+=1;
        if ($rOrg['jeniskelamin'] == 'P')
            $stafp+=1;
        $staf+=1;
    }
    if ($rOrg['alokasi'] == '0') { // selain staf
        if ($rOrg['kodejabatan'] == '252') { // panen
            if (($rOrg['tipekaryawan'] == '2')or ( $rOrg['tipekaryawan'] == '3')
                    or ( ($rOrg['sistemgaji'] = 'Harian')and ( $rOrg['tipekaryawan'] == '6'))) { // kht
                if ($rOrg['jeniskelamin'] == 'L')
                    $panentl+=1;
                if ($rOrg['jeniskelamin'] == 'P')
                    $panentp+=1;
                $panent+=1;
            }
            if ($rOrg['tipekaryawan'] == ('4')) { // khl
                if ($rOrg['jeniskelamin'] == 'L')
                    $panenll+=1;
                if ($rOrg['jeniskelamin'] == 'P')
                    $panenlp+=1;
                $panenl+=1;
            }
        }
		
        if (in_array($rOrg['kodejabatan'],$jabrwt)){
		//(($rOrg['kodejabatan'] == '13')or ( $rOrg['kodejabatan'] == '18')or ( $rOrg['kodejabatan'] == '131')) { // pelihara
            if (($rOrg['tipekaryawan'] == '2')or ( $rOrg['tipekaryawan'] == '3')
                    or ( ($rOrg['sistemgaji'] = 'Harian')and ( $rOrg['tipekaryawan'] == '6'))) { // kht
                if ($rOrg['jeniskelamin'] == 'L')
                    $peliharatl+=1;
                if ($rOrg['jeniskelamin'] == 'P')
                    $peliharatp+=1;
                $peliharat+=1;
            }
            if ($rOrg['tipekaryawan'] == ('4')) { // khl
                if ($rOrg['jeniskelamin'] == 'L')
                    $peliharall+=1;
                if ($rOrg['jeniskelamin'] == 'P')
                    $peliharalp+=1;
                $peliharal+=1;
            }
        }
		if (in_array($rOrg['kodejabatan'],$jabspv)){
        // if (($rOrg['kodejabatan'] == '6')or ( $rOrg['kodejabatan'] == '10')or ( $rOrg['kodejabatan'] == '11')or ( $rOrg['kodejabatan'] == '12')or ( $rOrg['subbagian'] == '16')or ( $rOrg['subbagian'] == '19')or ( $rOrg['subbagian'] == '92')or ( $rOrg['subbagian'] == '94')or ( $rOrg['subbagian'] == '100')or ( $rOrg['subbagian'] == '104')or ( $rOrg['subbagian'] == '128')) { // supervisi
            if (($rOrg['tipekaryawan'] == '1')
                    or ( ($rOrg['sistemgaji'] == 'Bulanan')and ( $rOrg['tipekaryawan'] == '1'))) { // bulanan
                if ($rOrg['jeniskelamin'] == 'L')
                    $superbl+=1;
                if ($rOrg['jeniskelamin'] == 'P')
                    $superbp+=1;
                $superb+=1;
            }
            if (($rOrg['tipekaryawan'] == '2')or ( $rOrg['tipekaryawan'] == '3')
                    or ( ($rOrg['sistemgaji'] = 'Harian')and ( $rOrg['tipekaryawan'] == '6'))) { // kht
                if ($rOrg['jeniskelamin'] == 'L')
                    $supertl+=1;
                if ($rOrg['jeniskelamin'] == 'P')
                    $supertp+=1;
                $supert+=1;
            }
            if ($rOrg['tipekaryawan'] == ('4')) { // khl
                if ($rOrg['jeniskelamin'] == 'L')
                    $superll+=1;
                if ($rOrg['jeniskelamin'] == 'P')
                    $superlp+=1;
                $superl+=1;
            }
        }
		if (!in_array($rOrg['kodejabatan'],$jabrwt) and !in_array($rOrg['kodejabatan'],$jabspv) and ($rOrg['kodejabatan']!='252')){
        // if (($rOrg['kodejabatan'] != '6')and ( $rOrg['kodejabatan'] != '10')and ( $rOrg['kodejabatan'] != '11')and ( $rOrg['kodejabatan'] != '12')and ( $rOrg['subbagian'] != '16')and ( $rOrg['subbagian'] != '19')and ( $rOrg['subbagian'] != '92')and ( $rOrg['subbagian'] != '94')and ( $rOrg['subbagian'] != '100')and ( $rOrg['subbagian'] != '104')and ( $rOrg['subbagian'] != '128')and ( $rOrg['kodejabatan'] != '14')and ( $rOrg['kodejabatan'] != '13')and ( $rOrg['kodejabatan'] != '18')and ( $rOrg['kodejabatan'] != '131')) { // kantor
            if (($rOrg['tipekaryawan'] == '1')
                    or ( ($rOrg['sistemgaji'] == 'Bulanan')and ( $rOrg['tipekaryawan'] == '6'))) { // bulanan
                if ($rOrg['jeniskelamin'] == 'L')
                    $kantorbl+=1;
                if ($rOrg['jeniskelamin'] == 'P')
                    $kantorbp+=1;
                $kantorb+=1;
            }
            if (($rOrg['tipekaryawan'] == '2')or ( $rOrg['tipekaryawan'] == '3')
                    or ( ($rOrg['sistemgaji'] = 'Harian')and ( $rOrg['tipekaryawan'] == '6'))) { // kht
                if ($rOrg['jeniskelamin'] == 'L')
                    $kantortl+=1;
                if ($rOrg['jeniskelamin'] == 'P')
                    $kantortp+=1;
                $kantort+=1;
            }
            if ($rOrg['tipekaryawan'] == ('4')) { // khl
                if ($rOrg['jeniskelamin'] == 'L')
                    $kantorll+=1;
                if ($rOrg['jeniskelamin'] == 'P')
                    $kantorlp+=1;
                $kantorl+=1;
            }
        }
    } // selain staf
}
@$rstafl = $stafl / $luas;
@$rstafp = $stafp / $luas;
@$rstaf = $staf / $luas;

@$rhapanenl = ($panentl + $panenll) / $luas;
@$rhapanenp = ($panentp + $panenlp) / $luas;
@$rhapanen = ($panent + $panenl) / $luas;

//rasio pelihara/ha
@$rhapeliharal = ($peliharatl + $peliharall) / $luas;
@$rhapeliharap = ($peliharatp + $peliharalp) / $luas;
@$rhapelihara = ($peliharat + $peliharal) / $luas;

//langsung
$langsungl = $panentl + $panenll + $peliharatl + $peliharall;
$langsungp = $panentp + $panenlp + $peliharatp + $peliharalp;
$langsung = $panent + $panenl + $peliharat + $peliharal;

//rasio supervisi/ha
@$rhasuperl = ($superbl + $supertl + $superll) / $luas;
@$rhasuperp = ($superbp + $supertp + $superlp) / $luas;
@$rhasuper = ($superbl + $supert + $superl) / $luas;

//rasio kantor/ha
@$rhakantorl = ($kantorbl + $kantortl + $kantorll) / $luas;
@$rhakantorp = ($kantorbp + $kantortp + $kantorlp) / $luas;
@$rhakantor = ($kantorbl + $kantort + $kantorl) / $luas;

//tidak langsung
$tlangsungl = $superbl + $supertl + $superll + $kantorbl + $kantortl + $kantorll;
$tlangsungp = $superbp + $supertp + $superlp + $kantorbp + $kantortp + $kantorlp;
$tlangsung = $superb + $supert + $superl + $kantorb + $kantort + $kantorl;

//langsung + tidak langsung
$ltlangsung_l = $langsungl + $tlangsungl;
$ltlangsung_p = $langsungp + $tlangsungp;
$ltlangsung_ = $langsung + $tlangsung;

@$rhaltlangsungl = $ltlangsung_l / $luas;
@$rhaltlangsungp = $ltlangsung_p / $luas;
@$rhaltlangsung = $ltlangsung_ / $luas;

$ltlangsungbl = $superbl + $kantorbl;
$ltlangsungbp = $superbp + $kantorbp;
$ltlangsungb = $superb + $kantorb;

$ltlangsungtl = $panentl + $peliharatl + $supertl + $kantortl;
$ltlangsungtp = $panentp + $peliharatp + $supertp + $kantortp;
$ltlangsungt = $panent + $peliharat + $supert + $kantort;

$ltlangsungll = $panenll + $peliharall + $superll + $kantorll;
$ltlangsunglp = $panenlp + $peliharalp + $superlp + $kantorlp;
$ltlangsungl = $panenl + $peliharal + $superl + $kantorl;

//karyawan
$karyawanl = $ltlangsung_l + $stafl;
$karyawanp = $ltlangsung_p + $stafp;
$karyawan = $ltlangsung_ + $staf;

@$rhakaryawanl = $karyawanl / $luas;
@$rhakaryawanp = $karyawanp / $luas;
@$rhakaryawan = $karyawan / $luas;

//pake awal, jangan pake backs
$awal = " lokasitugas = '" . $unit . "' and tglmasuk <= '" . $periode . "-15' and (tglkeluar='0000-00-00' or tglkeluar>'" . $periode . "-15')";

$laki2 = " and kelminkeluarga = 'L'";
$pere2 = " and kelminkeluarga = 'W'";

$istri = " and hubungankeluarga = 'Pasangan'";
$anak = " and hubungankeluarga = 'Anak'";


$anak0l = $anak0p = $anak6l = $anak6p = $anak0 = $anak6 = $anak18l = $anak18p = $anak18 = 0;
$istrip = $istri = $istril = 0;
$sOrg = "select * from " . $dbname . ".sdm_tanggungan_vw where " . $awal . " order by namaanggota asc";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $diff = abs(strtotime($periode . '-01') - strtotime($rOrg['tanggallahir']));
    $umur = floor($diff / (365 * 60 * 60 * 24));
    if ($rOrg['hubungankeluarga'] == 'Pasangan') { // istri
        if ($rOrg['kelminkeluarga'] == 'L')
            $istril = 0;
        if ($rOrg['kelminkeluarga'] == 'P')
            $istrip+=1;
        if ($rOrg['kelminkeluarga'] == 'P')
            $istri+=1;
    }
    if ($rOrg['hubungankeluarga'] == 'Anak') { // anak
        if ($umur <= 5) { // anak0
            if ($rOrg['kelminkeluarga'] == 'L')
                $anak0l+=1;
            if ($rOrg['kelminkeluarga'] == 'P')
                $anak0p+=1;
            $anak0+=1;
        }
        if (($umur > 5)and ( $umur <= 18)) { // anak6
            if ($rOrg['kelminkeluarga'] == 'L')
                $anak6l+=1;
            if ($rOrg['kelminkeluarga'] == 'P')
                $anak6p+=1;
            $anak6+=1;
        }
        if (($umur > 5)and ( $umur > 18)) { // anak18
            if ($rOrg['kelminkeluarga'] == 'L')
                $anak18l+=1;
            if ($rOrg['kelminkeluarga'] == 'P')
                $anak18p+=1;
            $anak18+=1;
        }
    }
}


@$rhaistril = 0;
@$rhaistrip = $istrip / $karyawanl;
@$rhaistri = $istri / $karyawanl;

$anakl = $anak0l + $anak6l + $anak18l;
$anakp = $anak0p + $anak6p + $anak18p;
$anak = $anak0 + $anak6 + $anak18;

@$rhaanakl = $anakl / $karyawan;
@$rhaanakp = $anakp / $karyawan;
@$rhaanak = $anak / $karyawan;

//tanggungan
$tanggungl = $istril + $anakl;
$tanggungp = $istrip + $anakp;
$tanggung = $istri + $anak;

@$rhatanggungl = $tanggungl / $karyawan;
@$rhatanggungp = $tanggungp / $karyawan;
@$rhatanggung = $tanggung / $karyawan;

//penduduk
$pendudukl = $karyawanl + $tanggungl;
$pendudukp = $karyawanp + $tanggungp;
$penduduk = $karyawan + $tanggung;

//pake awal, khusus turnover bulan ini
$awalmut = " lokasitugas = '" . $unit . "' and tanggalkeluar like '" . $periode . "%' and tanggalkeluar like '" . $tahun . "%'";

//pake awal, khusus turnover sd bulan ini
$awalmutsd = " lokasitugas = '" . $unit . "' and tanggalkeluar< '" . $periode . "-99' and tanggalkeluar like '" . $tahun . "%'";
$turnl = $turnp = $turn = 0;
$sOrg = "select * from " . $dbname . ".datakaryawan where " . $awalmut . " ";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    if ($rOrg['jeniskelamin'] == 'L')
        $turnl+=1;
    if ($rOrg['jeniskelamin'] == 'P')
        $turnp+=1;
    $turn+=1;
}
$turnsdl = $turnsdp = $turnsd = 0;
$sOrg = "select * from " . $dbname . ".datakaryawan where " . $awalmutsd . " ";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    if ($rOrg['jeniskelamin'] == 'L')
        $turnsdl+=1;
    if ($rOrg['jeniskelamin'] == 'P')
        $turnsdp+=1;
    $turnsd+=1;
}

@$rhaturnl = 100 * $turnl / $karyawanl;
@$rhaturnp = 100 * $turnp / $karyawanp;
@$rhaturn = 100 * $turn / $karyawan;

@$rhaturnsdl = 100 * $turnsdl / $karyawanl;
@$rhaturnsdp = 100 * $turnsdp / $karyawanp;
@$rhaturnsd = 100 * $turnsd / $karyawan;

$lakib = " and b.jeniskelamin = 'L'";
$pereb = " and b.jeniskelamin = 'P'";

$bulaninia = " and a.tanggal like '" . $periode . "%'";
$sdbulaninia = " and a.tanggal < '" . $periode . "-99' and a.tanggal like '" . $tahun . "%'";

$awala = " and b.lokasitugas = '" . $unit . "' and b.tanggalmasuk<='" . $periode . "-15' and (b.tanggalkeluar='0000-00-00' or b.tanggalkeluar>='" . $periode . "-15')";

$kodebayar = "select kodeabsen from " . $dbname . ".sdm_5absensi where kelompok=1";

$ljdatakaryawan = " left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid";

// absensi
$sOrg = "select a.absensi,a.tanggal,a.karyawanid,b.jeniskelamin from " . $dbname . ".sdm_absensidt a
    left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
    where a.tanggal like '" . $tahun . "%' and a.kodeorg like '" . $unit . "%'";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $dzabsen[$rOrg['karyawanid']][$rOrg['tanggal']] = $rOrg['absensi'];
    $dzkelamin[$rOrg['karyawanid']] = $rOrg['jeniskelamin'];
    $dzkaryawanid[$rOrg['karyawanid']] = $rOrg['karyawanid'];
    $dztanggal[$rOrg['tanggal']] = $rOrg['tanggal'];
}

// perawatan
$sOrg = "select a.absensi,a.tanggal,a.karyawanid,b.jeniskelamin from " . $dbname . ".kebun_kehadiran_vw a
    left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
    where a.tanggal like '" . $tahun . "%' and a.kodeorg like '" . $unit . "%'";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $dzabsen[$rOrg['karyawanid']][$rOrg['tanggal']] = $rOrg['absensi'];
    $dzkelamin[$rOrg['karyawanid']] = $rOrg['jeniskelamin'];
    $dzkaryawanid[$rOrg['karyawanid']] = $rOrg['karyawanid'];
    $dztanggal[$rOrg['tanggal']] = $rOrg['tanggal'];
}

// panen
$sOrg = "select b.tanggal,a.nik as karyawanid,c.jeniskelamin from " . $dbname . ".kebun_prestasi a
    left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
    left join " . $dbname . ".datakaryawan c on a.nik=c.karyawanid
    where b.tanggal like '" . $tahun . "%' and b.notransaksi like '%PNN%' and b.kodeorg like '" . $unit . "%' ";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $dzabsen[$rOrg['karyawanid']][$rOrg['tanggal']] = 'H';
    $dzkelamin[$rOrg['karyawanid']] = $rOrg['jeniskelamin'];
    $dzkaryawanid[$rOrg['karyawanid']] = $rOrg['karyawanid'];
    $dztanggal[$rOrg['tanggal']] = $rOrg['tanggal'];
}

// mandor 
$dzstr = "SELECT tanggal,nikmandor,jeniskelamin FROM " . $dbname . ".kebun_aktifitas a
    left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join " . $dbname . ".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal like '" . $tahun . "%' and b.kodeorg like '" . $unit . "%' and c.namakaryawan is not NULL
    union select tanggal,nikmandor1,jeniskelamin FROM " . $dbname . ".kebun_aktifitas a 
    left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join " . $dbname . ".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal like '" . $tahun . "%' and b.kodeorg like '" . $unit . "%' and c.namakaryawan is not NULL";

$dzres = $owlPDO->query($dzstr) or die(print " Gagal: " . PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while ($dzbar = $dzres->fetch()) {
    $dzabsen[$dzbar->nikmandor][$dzbar->tanggal] = 'H';
    $dzkelamin[$dzbar->nikmandor] = $dzbar->jeniskelamin;
    $dzkaryawanid[$dzbar->nikmandor] = $dzbar->nikmandor;
    $dztanggal[$dzbar->tanggal] = $dzbar->tanggal;
}

// kerani    
$dzstr = "SELECT tanggal,nikmandor,jeniskelamin FROM " . $dbname . ".kebun_aktifitas a
    left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join " . $dbname . ".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal like '" . $tahun . "%' and b.kodeorg like '" . $unit . "%' and c.namakaryawan is not NULL
    union select tanggal,keranimuat,jeniskelamin FROM " . $dbname . ".kebun_aktifitas a 
    left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join " . $dbname . ".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal like '" . $tahun . "%' and b.kodeorg like '" . $unit . "%' and c.namakaryawan is not NULL";

$dzres = $owlPDO->query($dzstr) or die(print " Gagal: " . PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while ($dzbar = $dzres->fetch()) {
    $dzabsen[$dzbar->nikmandor][$dzbar->tanggal] = 'H';
    $dzkelamin[$dzbar->nikmandor] = $dzbar->jeniskelamin;
    //$dzkaryawanid[$dzbar->karyawanid]=$dzbar->karyawanid;
    $dzkaryawanid[$dzbar->nikmandor] = $dzbar->nikmandor;
    $dztanggal[$dzbar->tanggal] = $dzbar->tanggal;
}

// traksi              
$dzstr = "SELECT a.tanggal,a.idkaryawan,b.jeniskelamin FROM " . $dbname . ".vhc_runhk a
left join " . $dbname . ".datakaryawan b on a.idkaryawan=b.karyawanid
where a.tanggal like '" . $tahun . "%' and a.notransaksi like '%" . $unit . "%'";

$dzres = $owlPDO->query($dzstr) or die(print " Gagal: " . PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while ($dzbar = $dzres->fetch()) {
    $dzabsen[$dzbar->idkaryawan][$dzbar->tanggal] = 'H';
    $dzkelamin[$dzbar->idkaryawan] = $dzbar->jeniskelamin;
    $dzkaryawanid[$dzbar->idkaryawan] = $dzbar->idkaryawan;
    $dztanggal[$dzbar->tanggal] = $dzbar->tanggal;
}


// dibayar ato nggak              
$dzstr = "SELECT * FROM " . $dbname . ".sdm_5absensi
where 1";

$dzres = $owlPDO->query($dzstr) or die(print " Gagal: " . PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while ($dzbar = $dzres->fetch()) {
    if ($dzbar->kelompok == '1')
        $ardibayar[] = $dzbar->kodeabsen;
    if ($dzbar->kelompok == '0')
        $artidakdibayar[] = $dzbar->kodeabsen;
}

function dates_inbetween($date1, $date2) {
    $day = 60 * 60 * 24;
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $days_diff = round(($date2 - $date1) / $day); // Unix time difference devided by 1 day to get total days in between
    $dates_array = array();
    $dates_array[] = date('Y-m-d', $date1);
    for ($x = 1; $x < $days_diff; $x++) {
        $dates_array[] = date('Y-m-d', ($date1 + ($day * $x)));
    }
    $dates_array[] = date('Y-m-d', $date2);
    if ($date1 == $date2) {
        $dates_array = array();
        $dates_array[] = date('Y-m-d', $date1);
    }
    return $dates_array;
}

$sTgl = "select distinct tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji 
   where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and periode='" . $periode . "' 
   and jenisgaji='H'";

$sddibayarl = $sddibayarp = $sddibayar = 0;
$sdtdibayarl = $sdtdibayarp = $sdtdibayar = 0;
$dibayarl = $dibayarp = $dibayar = 0;
$tdibayarl = $tdibayarp = $tdibayar = 0;


$qTgl = $owlPDO->query($sTgl) or die(print " Gagal: " . PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
$rTgl = $qTgl->fetch();
$tanggalperiode = dates_inbetween($rTgl['tanggalmulai'], $rTgl['tanggalsampai']);

// itung2...
if (!empty($dzkaryawanid))
    foreach ($dzkaryawanid as $karyawanid) {
        if (!empty($dztanggal))
            foreach ($dztanggal as $tanggal) {
                $dzabsen[$karyawanid][$tanggal] = isset($dzabsen[$karyawanid][$tanggal]) ? $dzabsen[$karyawanid][$tanggal] : '';
                if (in_array($dzabsen[$karyawanid][$tanggal], $ardibayar)) {
                    if ($dzkelamin[$karyawanid] == 'L')
                        $sddibayarl+=1;
                    if ($dzkelamin[$karyawanid] == 'P')
                        $sddibayarp+=1;
                    $sddibayar+=1;
                }
                $dzabsen[$karyawanid][$tanggal] = isset($dzabsen[$karyawanid][$tanggal]) ? $dzabsen[$karyawanid][$tanggal] : '';
                if (in_array($dzabsen[$karyawanid][$tanggal], $artidakdibayar)) {
                    if ($dzkelamin[$karyawanid] == 'L')
                        $sdtdibayarl+=1;
                    if ($dzkelamin[$karyawanid] == 'P')
                        $sdtdibayarp+=1;
                    $sdtdibayar+=1;
                }
                if (in_array($tanggal, $tanggalperiode)) {
                    $dzabsen[$karyawanid][$tanggal] = isset($dzabsen[$karyawanid][$tanggal]) ? $dzabsen[$karyawanid][$tanggal] : '';
                    if (in_array($dzabsen[$karyawanid][$tanggal], $ardibayar)) {
                        if ($dzkelamin[$karyawanid] == 'L')
                            $dibayarl+=1;
                        if ($dzkelamin[$karyawanid] == 'P')
                            $dibayarp+=1;
                        $dibayar+=1;
                    }
                    $dzabsen[$karyawanid][$tanggal] = isset($dzabsen[$karyawanid][$tanggal]) ? $dzabsen[$karyawanid][$tanggal] : '';
                    if (in_array($dzabsen[$karyawanid][$tanggal], $artidakdibayar)) {
                        if ($dzkelamin[$karyawanid] == 'L')
                            $tdibayarl+=1;
                        if ($dzkelamin[$karyawanid] == 'P')
                            $tdibayarp+=1;
                        $tdibayar+=1;
                    }
                }
            }
    }

$sdlibur = $sdhke = 0;
//hari minggu+libur
$sOrg = "select periode, (minggu+libur) as minggu, hkefektif from " . $dbname . ".sdm_hk_efektif where periode between '" . $tahun . "01' and '" . $tahun . $bulan . "'";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    if ($rOrg['periode'] == ($tahun . $bulan)) {
        $libur = $rOrg['minggu'];
        $hke = $rOrg['hkefektif'];
    }
    $sdlibur+=$rOrg['minggu'];
    $sdhke+=$rOrg['hkefektif'];
}



$he = $libur + $hke;
$sdhe = $sdlibur + $sdhke;

@$phe = 100 * $hke / $he;
@$sdphe = 100 * $sdhke / $sdhe;

$sOrg = "select * from " . $dbname . ".sdm_perumahanht where kodeorg = '" . $unit . "' and tahunpembuatan <= '" . $tahun . "' and kondisi <>'2'";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$rumah = owlBaris($qOrg);

@$rharumah = 100 * $rumah / $karyawan;

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
        <td colspan=12 align=left><font size=3>04.1 KARYAWAN DAN PERUMAHAN</font></td>
        <td colspan=12 align=right>" . $_SESSION['lang']['bulan'] . " : " . $optBulan[$bulan] . " " . $tahun . "</td>
     </tr> ";
    $tab.="<tr><td colspan=24 align=left>" . $_SESSION['lang']['unit'] . " : " . $optNm[$unit] . " (" . $unit . ")</td></tr>  ";
    if ($afdId != '') {
        $tab.="<tr><td colspan=24 align=left>" . $_SESSION['lang']['afdeling'] . " : " . $optNm[$afdId] . " (" . $afdId . ")</td></tr>  ";
    }
    $tab.="</table>";
} else {
    $bg = "";
    $brdr = 0;
}
if ($proses != 'excel')
    $tab.=$judul;
$tab.="<table cellpadding=1 cellspacing=1 border=" . $brdr . " class=sortable style='width:100%;'><thead>";
$tab.="<tr class=rowheader>";
$tab.="<td colspan=6 rowspan=3 align=center " . $bg . ">" . $_SESSION['lang']['uraian'] . "</td>";
$tab.="<td colspan=3 align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>";
$tab.="<td colspan=3 align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>";
$tab.="<td colspan=6 rowspan=3 align=center " . $bg . ">" . $_SESSION['lang']['uraian'] . "</td>";
$tab.="<td colspan=3 rowspan=2 align=center " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>";
$tab.="<td colspan=3 rowspan=2 align=center " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowheader>";
$tab.="<td colspan=3 align=center " . $bg . ">" . $_SESSION['lang']['luas'] . ": " . number_format($luas, 2) . " Ha</td>";
$tab.="<td colspan=3 align=center " . $bg . ">" . $_SESSION['lang']['luas'] . ": " . number_format($luas, 2) . " Ha</td>";
$tab.="</tr>";
$tab.="<tr class=rowheader>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['pria'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['wanita'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['jumlah'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['pria'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['wanita'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['jumlah'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['pria'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['wanita'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['jumlah'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['pria'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['wanita'] . "</td>";
$tab.="<td align=center " . $bg . ">" . $_SESSION['lang']['jumlah'] . "</td>";
$tab.="</tr>";
$tab.="</thead><tbody>";

$tab.="<tr class=rowcontent>";
$tab.="<td align=left><b>I.</b></td>";
$tab.="<td align=left colspan=4><b>Karyawan</b></td>";
$tab.="<td align=right>1+2+3</td>";
$tab.="<td align=right>" . numberformat($karyawanl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($karyawanp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($karyawan, 0) . "</td>";
$tab.="<td align=right>" . numberformat($karyawanl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($karyawanp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($karyawan, 0) . "</td>";
$tab.="<td align=left><b>II.</b></td>";
$tab.="<td align=left colspan=4><b>Tanggungan</b></td>";
$tab.="<td align=right>1+2</td>";
$tab.="<td align=right>" . numberformat($tanggungl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tanggungp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tanggung, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tanggungl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tanggungp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tanggung, 0) . "</td>";
$tab.="</tr>";

$tab.="<tr class=rowcontent>";
$tab.="<td></td>";
$tab.="<td align=left colspan=5>- Rasio Total Karyawan/Ha</td>";
$tab.="<td align=right>" . numberformat($rhakaryawanl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhakaryawanp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhakaryawan, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhakaryawanl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhakaryawanp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhakaryawan, 2) . "</td>";
$tab.="<td></td>";
$tab.="<td align=left colspan=5>- Rasio Tanggungan/Karyawan</td>";
$tab.="<td align=right>" . numberformat($rhatanggungl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhatanggungp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhatanggung, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhatanggungl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhatanggungp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhatanggung, 2) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td align=right colspan=2>1.</td>";
$tab.="<td align=left colspan=4>STAF</td>";
$tab.="<td align=right>" . numberformat($stafl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($stafp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($staf, 0) . "</td>";
$tab.="<td align=right>" . numberformat($stafl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($stafp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($staf, 0) . "</td>";
$tab.="<td align=right colspan=2>1.</td>";
$tab.="<td align=left colspan=4>Istri (tidak bekerja)</td>";
$tab.="<td align=right>" . numberformat($istril, 0) . "</td>";
$tab.="<td align=right>" . numberformat($istrip, 0) . "</td>";
$tab.="<td align=right>" . numberformat($istri, 0) . "</td>";
$tab.="<td align=right>" . numberformat($istril, 0) . "</td>";
$tab.="<td align=right>" . numberformat($istrip, 0) . "</td>";
$tab.="<td align=right>" . numberformat($istri, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=2></td>";
$tab.="<td align=left colspan=4>- Rasio Staf/Ha</td>";
$tab.="<td align=right>" . numberformat($rstafl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rstafp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rstaf, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rstafl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rstafp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rstaf, 2) . "</td>";
$tab.="<td colspan=2></td>";
$tab.="<td align=left colspan=4>- Rasio Istri/Karyawan</td>";
$tab.="<td align=right>" . numberformat($rhaistril, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaistrip, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaistri, 2) . "</td>";
//$tab.="<td align=right>".numberformat($thaistril,2)."</td>";
//$tab.="<td align=right>".numberformat($thaistrip,2)."</td>";
$tab.="<td align=right>" . numberformat($rhaistril, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaistrip, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaistri, 2) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td align=right colspan=2>2.</td>";
$tab.="<td align=left colspan=3>KARYAWAN LANGSUNG</td>";
$tab.="<td align=right>1)+2)</td>";
$tab.="<td align=right>" . numberformat($langsungl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($langsungp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($langsung, 0) . "</td>";
$tab.="<td align=right>" . numberformat($langsungl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($langsungp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($langsung, 0) . "</td>";
$tab.="<td align=right colspan=2>2.</td>";
$tab.="<td align=left colspan=3>Anak</td>";
$tab.="<td align=right>1)+2)+3)</td>";
$tab.="<td align=right>" . numberformat($anakl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anakp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anakl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anakp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td align=right colspan=3>1).</td>";
$tab.="<td align=left>Panen</td>";
$tab.="<td align=left colspan=2>KHT</td>";
$tab.="<td align=right>" . numberformat($panentl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($panentp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($panent, 0) . "</td>";
$tab.="<td align=right>" . numberformat($panentl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($panentp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($panent, 0) . "</td>";
$tab.="<td align=right colspan=3>1).</td>";
$tab.="<td align=left colspan=3>Balita (0-5 Tahun)</td>";
$tab.="<td align=right>" . numberformat($anak0l, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak0p, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak0, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak0l, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak0p, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak0, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=4></td>";
$tab.="<td align=left colspan=2>KHL</td>";
$tab.="<td align=right>" . numberformat($panenll, 0) . "</td>";
$tab.="<td align=right>" . numberformat($panenlp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($panenl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($panenll, 0) . "</td>";
$tab.="<td align=right>" . numberformat($panenlp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($panenl, 0) . "</td>";
$tab.="<td align=right colspan=3>2).</td>";
$tab.="<td align=left colspan=3>Usia Sekolah (6-18 Tahun)</td>";
$tab.="<td align=right>" . numberformat($anak6l, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak6p, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak6, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak6l, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak6p, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak6, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=3></td>";
$tab.="<td align=left colspan=3>- Rasio Karyawan Panen/Ha</td>";
$tab.="<td align=right>" . numberformat($rhapanenl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhapanenp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhapanen, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhapanenl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhapanenp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhapanen, 2) . "</td>";
$tab.="<td align=right colspan=3>3).</td>";
$tab.="<td align=left colspan=3>Usia Karyawan (>18 Tahun)</td>";
$tab.="<td align=right>" . numberformat($anak18l, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak18p, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak18, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak18l, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak18p, 0) . "</td>";
$tab.="<td align=right>" . numberformat($anak18, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td align=right colspan=3>2).</td>";
$tab.="<td align=left>Pemeliharaan</td>";
$tab.="<td align=left colspan=2>KHT</td>";
$tab.="<td align=right>" . numberformat($peliharatl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($peliharatp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($peliharat, 0) . "</td>";
$tab.="<td align=right>" . numberformat($peliharatl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($peliharatp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($peliharat, 0) . "</td>";
$tab.="<td colspan=2></td>";
$tab.="<td align=left colspan=4>- Rasio Anak/Karyawan</td>";
$tab.="<td align=right>" . numberformat($rhaanakl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaanakp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaanak, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaanakl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaanakp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaanak, 2) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=3></td>";
$tab.="<td align=left>(Afdeling & Bibitan)</td>";
$tab.="<td align=left colspan=2>KHL</td>";
$tab.="<td align=right>" . numberformat($peliharall, 0) . "</td>";
$tab.="<td align=right>" . numberformat($peliharalp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($peliharal, 0) . "</td>";
$tab.="<td align=right>" . numberformat($peliharall, 0) . "</td>";
$tab.="<td align=right>" . numberformat($peliharalp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($peliharal, 0) . "</td>";
$tab.="<td colspan=6></td>";
$tab.="<td colspan=3></td>";
$tab.="<td colspan=3></td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=3></td>";
$tab.="<td align=left colspan=3>- Rasio Karyawan Pemeliharaan/Ha</td>";
$tab.="<td align=right>" . numberformat($rhapeliharal, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhapeliharap, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhapelihara, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhapeliharal, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhapeliharap, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhapelihara, 2) . "</td>";
$tab.="<td align=left><b>III.</b></td>";
$tab.="<td align=left colspan=4><b>Total Penduduk</b></td>";
$tab.="<td align=right>I+II</td>";
$tab.="<td align=right>" . numberformat($pendudukl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($pendudukp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($penduduk, 0) . "</td>";
$tab.="<td align=right>" . numberformat($pendudukl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($pendudukp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($penduduk, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td align=right colspan=2>3.</td>";
$tab.="<td align=left colspan=3>KARYAWAN TIDAK LANGSUNG</td>";
$tab.="<td align=right>1)+2)</td>";
$tab.="<td align=right>" . numberformat($tlangsungl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tlangsungp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tlangsung, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tlangsungl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tlangsungp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tlangsung, 0) . "</td>";
$tab.="<td colspan=6></td>";
$tab.="<td colspan=3></td>";
$tab.="<td colspan=3></td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td align=right colspan=3>1).</td>";
$tab.="<td align=left>Supervisi</td>";
$tab.="<td align=left colspan=2>Bulanan</td>";
$tab.="<td align=right>" . numberformat($superbl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($superbp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($superb, 0) . "</td>";
$tab.="<td align=right>" . numberformat($superbl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($superbp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($superb, 0) . "</td>";
$tab.="<td align=left><b>IV.</b></td>";
$tab.="<td align=left colspan=5><b>Mutasi</b></td>";
$mutasil = $mutasip = $mutasi = $mutasip = $mutasi = 0;
$tab.="<td align=right>" . number_format($mutasil, 0) . "</td>";
$tab.="<td align=right>" . number_format($mutasip, 0) . "</td>";
$tab.="<td align=right>" . number_format($mutasi, 0) . "</td>";
$tab.="<td align=right>" . number_format($mutasil, 0) . "</td>";
$tab.="<td align=right>" . number_format($mutasip, 0) . "</td>";
$tab.="<td align=right>" . number_format($mutasi, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=3></td>";
$tab.="<td align=left>(Afdeling & Bibitan)</td>";
$tab.="<td align=left colspan=2>KHT</td>";
$tab.="<td align=right>" . numberformat($supertl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($supertp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($supert, 0) . "</td>";
$tab.="<td align=right>" . numberformat($supertl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($supertp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($supert, 0) . "</td>";
$tab.="<td colspan=6></td>";
$tab.="<td colspan=3></td>";
$tab.="<td colspan=3></td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=4></td>";
$tab.="<td align=left colspan=2>KHL</td>";
$tab.="<td align=right>" . numberformat($superll, 0) . "</td>";
$tab.="<td align=right>" . numberformat($superlp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($superl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($superll, 0) . "</td>";
$tab.="<td align=right>" . numberformat($superlp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($superl, 0) . "</td>";
$tab.="<td align=left><b>V.</b></td>";
$tab.="<td align=left colspan=5><b>Turn Over Karyawan (%)</b></td>";
$tab.="<td align=right>" . numberformat($rhaturnl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaturnp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaturn, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaturnsdl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaturnsdp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaturnsd, 2) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=3></td>";
$tab.="<td align=left colspan=3>- Rasio Karyawan Supervisi/Ha</td>";
$tab.="<td align=right>" . numberformat($rhasuperl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhasuperp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhasuper, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhasuperl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhasuperp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhasuper, 2) . "</td>";
$tab.="<td colspan=6></td>";
$tab.="<td colspan=3></td>";
$tab.="<td colspan=3></td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td align=right colspan=3>2).</td>";
$tab.="<td align=left>Kantor & Lain-lain</td>";
$tab.="<td align=left colspan=2>Bulanan</td>";
$tab.="<td align=right>" . numberformat($kantorbl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantorbp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantorb, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantorbl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantorbp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantorb, 0) . "</td>";
$tab.="<td align=left><b>VI.</b></td>";
$tab.="<td align=left colspan=5><b>% Hari Kerja Efektif</b></td>";
$tab.="<td colspan=3></td>";
$tab.="<td colspan=3></td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=4></td>";
$tab.="<td align=left colspan=2>KHT</td>";
$tab.="<td align=right>" . numberformat($kantortl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantortp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantort, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantortl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantortp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantort, 0) . "</td>";
$tab.="<td align=right colspan=2>1.</td>";
$tab.="<td align=left colspan=4>Absensi Dibayar</td>";
$tab.="<td align=right>" . numberformat($dibayarl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($dibayarp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($dibayar, 0) . "</td>";
$tab.="<td align=right>" . numberformat($sddibayarl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($sddibayarp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($sddibayar, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=4></td>";
$tab.="<td align=left colspan=2>KHL</td>";
$tab.="<td align=right>" . numberformat($kantorll, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantorlp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantorl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantorll, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantorlp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($kantorl, 0) . "</td>";
$tab.="<td align=right colspan=2>2.</td>";
$tab.="<td align=left colspan=4>Absensi Tidak Dibayar</td>";
$tab.="<td align=right>" . numberformat($tdibayarl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tdibayarp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($tdibayar, 0) . "</td>";
$tab.="<td align=right>" . numberformat($sdtdibayarl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($sdtdibayarp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($sdtdibayar, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=3></td>";
$tab.="<td align=left colspan=3>- Rasio Karyawan Kantor & Lain-lain/Ha</td>";
$tab.="<td align=right>" . numberformat($rhakantorl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhakantorp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhakantor, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhakantorl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhakantorp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhakantor, 2) . "</td>";
$tab.="<td align=right colspan=2>3.</td>";
$tab.="<td align=left colspan=4>Hari Libur (Besar dan Minggu)</td>";
$tab.="<td align=right colspan=3>" . numberformat($libur, 0) . "</td>";
$tab.="<td align=right colspan=3>" . numberformat($sdlibur, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=2></td>";
$tab.="<td align=left colspan=3><b><i>KARYAWAN LANGSUNG + TIDAK LANGSUNG</i></b></td>";
$tab.="<td align=right>2 + 3</td>";
$tab.="<td align=right>" . numberformat($ltlangsung_l, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsung_p, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsung_, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsung_l, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsung_p, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsung_, 0) . "</td>";
$tab.="<td align=right colspan=2>4.</td>";
$tab.="<td align=left colspan=4>Hari Efektif</td>";
$tab.="<td align=right colspan=3>" . numberformat($he, 0) . "</td>";
$tab.="<td align=right colspan=3>" . numberformat($sdhe, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=3></td>";
$tab.="<td align=left colspan=1>- Karyawan</td>";
$tab.="<td align=left colspan=2>Bulanan</td>";
$tab.="<td align=right>" . numberformat($ltlangsungbl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungbp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungb, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungbl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungbp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungb, 0) . "</td>";
$tab.="<td align=right colspan=2>5.</td>";
$tab.="<td align=left colspan=4>Hari Kerja Efektif</td>";
$tab.="<td align=right colspan=3>" . numberformat($hke, 0) . "</td>";
$tab.="<td align=right colspan=3>" . numberformat($sdhke, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=4></td>";
$tab.="<td align=left colspan=2>KHT</td>";
$tab.="<td align=right>" . numberformat($ltlangsungtl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungtp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungt, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungtl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungtp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungt, 0) . "</td>";
$tab.="<td align=right colspan=2>6.</td>";
$tab.="<td align=left colspan=4>% Hari Efektif</td>";
$tab.="<td align=right colspan=3>" . numberformat($phe, 0) . "</td>";
$tab.="<td align=right colspan=3>" . numberformat($sdphe, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=4></td>";
$tab.="<td align=left colspan=2>KHL</td>";
$tab.="<td align=right>" . numberformat($ltlangsungll, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsunglp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungl, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungll, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsunglp, 0) . "</td>";
$tab.="<td align=right>" . numberformat($ltlangsungl, 0) . "</td>";
$tab.="<td align=left><b>VII.</b></td>";
$tab.="<td align=left colspan=5><b>Perumahan</b></td>";
$tab.="<td align=right colspan=3>" . numberformat($rumah, 0) . "</td>";
$tab.="<td align=right colspan=3>" . numberformat($rumah, 0) . "</td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=3></td>";
$tab.="<td align=left colspan=3>- Rasio Karyawan L+TL/Ha</td>";
$tab.="<td align=right>" . numberformat($rhaltlangsungl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaltlangsungp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaltlangsung, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaltlangsungl, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaltlangsungp, 2) . "</td>";
$tab.="<td align=right>" . numberformat($rhaltlangsung, 2) . "</td>";
$tab.="<td colspan=1></td>";
$tab.="<td align=left colspan=5>- Rasio Rumah/Karyawan</td>";
$tab.="<td align=right colspan=3>" . numberformat($rharumah, 2, 0) . "</td>";
$tab.="<td align=right colspan=3>" . numberformat($rharumah, 2, 0) . "</td>";
$tab.="</tr>";
$tab.="</tbody></table>";

switch ($proses) {
    case'preview':
        if ($unit == '' || $periode == '') {
            exit("Error:Field Tidak Boleh Kosong");
        }
        echo $tab;
        break;

    case'excel':
        if ($unit == '' || $periode == '') {
            exit("Error:Field Tidak Boleh Kosong");
        }

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
        $dte = date("YmdHis");
        $nop_ = "lbm_karyawanperumahan_" . $unit . $periode;
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
            exit("Error:Field Tidak Boleh Kosong");
        }

        $w1 = 10;
        $w2 = 10;
        $w3 = 10;
        $w4 = 70;
        $w5 = 35;
        $w6 = 30;
        $w7 = 35;
        $w8 = 35;
        $w9 = 40;
        $w10 = $w7; //20;
        $w11 = $w8; //20;
        $w12 = $w9; //30;
        $w13 = $w1; //5;
        $w14 = $w2; //5;
        $w15 = $w3; //81;
        $w16 = $w4; //15;
        $w17 = $w5; //64;
        $w18 = $w6; //24;
        $w19 = $w7; //19;
        $w20 = $w8; //19;
        $w21 = $w9; //23;
        $w22 = $w10; //19;
        $w23 = $w11; //19;
        $w24 = $w12; //23;

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
                global $afdId;
                global $w1, $w2, $w3, $w4, $w5, $w6, $w7, $w8, $w9;
                $width = $this->w - $this->lMargin - $this->rMargin;
                $w10 = $w7; //20;
                $w11 = $w8; //20;
                $w12 = $w9; //30;
                $w13 = $w1; //5;
                $w14 = $w2; //5;
                $w15 = $w3; //81;
                $w16 = $w4; //15;
                $w17 = $w5; //64;
                $w18 = $w6; //24;
                $w19 = $w7; //19;
                $w20 = $w8; //19;
                $w21 = $w9; //23;
                $w22 = $w10; //19;
                $w23 = $w11; //19;
                $w24 = $w12; //23;        

                $height = 20;
                $this->SetFillColor(220, 220, 220);
                $this->SetFont('Arial', 'B', 12);
//        $this->Cell($w1,$height,'1',1,0,'C',1);
//        $this->Cell($w2,$height,'2',1,0,'C',1);
//        $this->Cell($w3,$height,'3',1,0,'C',1);
//        $this->Cell($w4,$height,'4',1,0,'C',1);
//        $this->Cell($w5,$height,'5',1,0,'C',1);
//        $this->Cell($w6,$height,'6',1,0,'C',1);
//        $this->Cell($w7,$height,'7',1,0,'C',1);
//        $this->Cell($w8,$height,'8',1,0,'C',1);
//        $this->Cell($w9,$height,'9',1,0,'C',1);
//        $this->Cell($w10,$height,'10',1,0,'C',1);
//        $this->Cell($w11,$height,'11',1,0,'C',1);
//        $this->Cell($w12,$height,'12',1,0,'C',1);
//        $this->Cell($w13,$height,'13',1,0,'C',1);
//        $this->Cell($w14,$height,'14',1,0,'C',1);
//        $this->Cell($w15,$height,'15',1,0,'C',1);
//        $this->Cell($w16,$height,'16',1,0,'C',1);
//        $this->Cell($w17,$height,'17',1,0,'C',1);
//        $this->Cell($w18,$height,'18',1,0,'C',1);
//        $this->Cell($w19,$height,'19',1,0,'C',1);
//        $this->Cell($w20,$height,'20',1,0,'C',1);
//        $this->Cell($w21,$height,'21',1,0,'C',1);
//        $this->Cell($w22,$height,'22',1,0,'C',1);
//        $this->Cell($w23,$height,'23',1,0,'C',1);
//        $this->Cell($w24,$height,'24',1,0,'C',1);
//        $this->Ln();
                $this->Cell($w1 + $w2 + $w3 + $w4 + $w5 + $w6 + $w7 + $w8 + $w9 + $w10 + $w11 + $w12, $height, '04.1 KARYAWAN DAN PERUMAHAN', NULL, 0, 'L', 1);
                $this->Cell($w1 + $w2 + $w3 + $w4 + $w5 + $w6 + $w7 + $w8 + $w9 + $w10 + $w11 + $w12, $height, $_SESSION['lang']['bulan'] . " : " . $optBulan[$bulan] . " " . $tahun, NULL, 0, 'R', 1);
                $this->Ln();
                $this->Cell(($w1 + $w2 + $w3 + $w4 + $w5 + $w6 + $w7 + $w8 + $w9 + $w10 + $w11 + $w12) * 2, $height, $_SESSION['lang']['unit'] . " : " . $optNm[$unit] . " (" . $unit . ")", NULL, 0, 'L', 1);
                $this->Ln();
                if ($afdId != '') {
                    $this->Cell(($w1 + $w2 + $w3 + $w4 + $w5 + $w6 + $w7 + $w8 + $w9 + $w10 + $w11 + $w12) * 2, $height, $_SESSION['lang']['afdeling'] . " : " . $optNm[$afdId] . " (" . $afdId . ")", NULL, 0, 'L', 1);
                }
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 8);
                $this->Cell($w1 + $w2 + $w3 + $w4 + $w5 + $w6, $height, '', 'TRL', 0, 'C', 1);
                $this->Cell($w7 + $w8 + $w9, $height, $_SESSION['lang']['bulanini'], 1, 0, 'C', 1);
                $this->Cell($w10 + $w11 + $w12, $height, $_SESSION['lang']['sdbulanini'], 1, 0, 'C', 1);
                $this->Cell($w13 + $w14 + $w15 + $w16 + $w17 + $w18, $height, '', 'TRL', 0, 'C', 1);
                $this->Cell($w19 + $w20 + $w21, $height, $_SESSION['lang']['bulanini'], 'TRL', 0, 'C', 1);
                $this->Cell($w22 + $w23 + $w24, $height, $_SESSION['lang']['sdbulanini'], 'TRL', 0, 'C', 1);
                $this->Ln();
                $this->Cell($w1 + $w2 + $w3 + $w4 + $w5 + $w6, $height, $_SESSION['lang']['uraian'], 'RL', 0, 'C', 1);
                $this->Cell($w7, $height, $_SESSION['lang']['luas'], 1, 0, 'C', 1);
                $this->Cell($w8 + $w9, $height, number_format($luas, 2) . ' Ha', 1, 0, 'C', 1);
                $this->Cell($w10, $height, $_SESSION['lang']['luas'], 1, 0, 'C', 1);
                $this->Cell($w11 + $w12, $height, number_format($luas, 2) . ' Ha', 1, 0, 'C', 1);
                $this->Cell($w13 + $w14 + $w15 + $w16 + $w17 + $w18, $height, $_SESSION['lang']['uraian'], 'RL', 0, 'C', 1);
                $this->Cell($w19 + $w20 + $w21, $height, '', 'RLB', 0, 'C', 1);
                $this->Cell($w22 + $w23 + $w24, $height, '', 'RLB', 0, 'C', 1);
                $this->Ln();
                $this->Cell($w1 + $w2 + $w3 + $w4 + $w5 + $w6, $height, '', 'RLB', 0, 'C', 1);
                $this->Cell($w7, $height, $_SESSION['lang']['pria'], 1, 0, 'C', 1);
                $this->Cell($w8, $height, $_SESSION['lang']['wanita'], 1, 0, 'C', 1);
                $this->Cell($w9, $height, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
                $this->Cell($w10, $height, $_SESSION['lang']['pria'], 1, 0, 'C', 1);
                $this->Cell($w11, $height, $_SESSION['lang']['wanita'], 1, 0, 'C', 1);
                $this->Cell($w12, $height, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
                $this->Cell($w13 + $w14 + $w15 + $w16 + $w17 + $w18, $height, '', 'RLB', 0, 'C', 1);
                $this->Cell($w19, $height, $_SESSION['lang']['pria'], 1, 0, 'C', 1);
                $this->Cell($w20, $height, $_SESSION['lang']['wanita'], 1, 0, 'C', 1);
                $this->Cell($w21, $height, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
                $this->Cell($w22, $height, $_SESSION['lang']['pria'], 1, 0, 'C', 1);
                $this->Cell($w23, $height, $_SESSION['lang']['wanita'], 1, 0, 'C', 1);
                $this->Cell($w24, $height, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
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
        $pdf->SetFont('Arial', '', 8);

        $pdf->Cell($w1, $height, 'I.', 'TL', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5, $height, 'KARYAWAN', 'T', 0, 'L', 1);
        $pdf->Cell($w6, $height, '1+2+3', 'TR', 0, 'R', 1);
        $pdf->Cell($w7, $height, numberformat($karyawanl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($karyawanp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($karyawan, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($karyawanl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($karyawanp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($karyawan, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, 'II.', 'TL', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5, $height, 'TANGGUNGAN', 'T', 0, 'L', 1);
        $pdf->Cell($w6, $height, '1+2', 'TR', 0, 'R', 1);
        $pdf->Cell($w7, $height, numberformat($tanggungl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($tanggungp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($tanggung, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($tanggungl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($tanggungp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($tanggung, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, '- Rasio Total Karyawan/Ha', 'BR', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rhakaryawanl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rhakaryawanp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rhakaryawan, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rhakaryawanl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rhakaryawanp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rhakaryawan, 2), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, '- Rasio Tanggungan/Karyawan', 'BR', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rhatanggungl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rhatanggungp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rhatanggung, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rhatanggungl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rhatanggungp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rhatanggung, 2), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '1.', 'T', 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, 'STAF', 'TR', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($stafl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($stafp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($staf, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($stafl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($stafp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($staf, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '1.', 'T', 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, 'Istri (tidak bekerja)', 'TR', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($istril, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($istrip, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($istri, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($istril, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($istrip, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($istri, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, '- Rasio Staf/Ha', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rstafl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rstafp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rstaf, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rstafl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rstafp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rstaf, 2), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '', 'B', 0, 'C', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, '- Rasio Istri/Karyawan', 'BR', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rhaistril, 2), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rhaistrip, 2), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rhaistri, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rhaistril, 2), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rhaistrip, 2), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rhaistri, 2), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '2.', NULL, 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5, $height, 'KARYAWAN LANGSUNG', NULL, 0, 'L', 1);
        $pdf->Cell($w6, $height, '1)+2)', 'R', 0, 'R', 1);
        $pdf->Cell($w7, $height, numberformat($langsungl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($langsungp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($langsung, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($langsungl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($langsungp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($langsung, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '2.', 'T', 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5, $height, 'Anak (tidak bekerja)', 'T', 0, 'L', 1);
        $pdf->Cell($w6, $height, '1)+2)+3)', 'TR', 0, 'R', 1);
        $pdf->Cell($w7, $height, numberformat($anakl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($anakp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($anak, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($anakl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($anakp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($anak, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4, $height, '1). Panen', NULL, 0, 'L', 1);
        $pdf->Cell($w5 + $w6, $height, ': KHT', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($panentl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($panentp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($panent, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($panentl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($panentp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($panent, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, '1). Balita (0-5 Tahun)', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($anak0l, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($anak0p, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($anak0, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($anak0l, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($anak0p, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($anak0, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3 + $w4, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w5 + $w6, $height, ': KHL', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($panenll, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($panenlp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($panenl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($panenll, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($panenlp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($panenl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, '2). Usia Sekolah (6-18 Tahun)', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($anak6l, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($anak6p, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($anak6, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($anak6l, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($anak6p, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($anak6, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w4 + $w5 + $w6, $height, '- Rasio Karyawan Panen/Ha', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rhapanenl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rhapanenp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rhapanen, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rhapanenl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rhapanenp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rhapanen, 2), 1, 0, 'R', 1);
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, '3). Usia Karyawan >18 Tahun', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($anak18l, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($anak18p, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($anak18, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($anak18l, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($anak18p, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($anak18, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4, $height, '2). Pemeliharaan', NULL, 0, 'L', 1);
        $pdf->Cell($w5 + $w6, $height, ': KHT', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($peliharatl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($peliharatp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($peliharat, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($peliharatl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($peliharatp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($peliharat, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1 + $w2 + $w3, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w4 + $w5 + $w6, $height, '- Rasio Anak/Karyawan', 'BR', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rhaanakl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rhaanakp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rhaanak, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rhaanakl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rhaanakp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rhaanak, 2), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4, $height, '(Afdeling & Bibitan)', NULL, 0, 'L', 1);
        $pdf->Cell($w5 + $w6, $height, ': KHL', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($peliharall, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($peliharalp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($peliharal, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($peliharall, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($peliharalp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($peliharal, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, '', 'TRB', 0, 'C', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, '', 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, '', 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w4 + $w5 + $w6, $height, '- Rasio Karyawan Pemeliharaan/Ha', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rhapeliharal, 2), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rhapeliharap, 2), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rhapelihara, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rhapeliharal, 2), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rhapeliharap, 2), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rhapelihara, 2), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, 'III.', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5, $height, 'TOTAL PENDUDUK', 'T', 0, 'L', 1);
        $pdf->Cell($w6, $height, 'I+II', 'TR', 0, 'R', 1);
        $pdf->Cell($w7, $height, numberformat($pendudukl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($pendudukp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($penduduk, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($pendudukl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($pendudukp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($penduduk, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '3.', NULL, 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5, $height, 'KARYAWAN TIDAK LANGSUNG', NULL, 0, 'L', 1);
        $pdf->Cell($w6, $height, '1)+2)', 'R', 0, 'R', 1);
        $pdf->Cell($w7, $height, numberformat($tlangsungl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($tlangsungp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($tlangsung, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($tlangsungl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($tlangsungp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($tlangsung, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, '', 'B', 0, 'C', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, '', 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, '', 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4, $height, '1). Supervisi', NULL, 0, 'L', 1);
        $pdf->Cell($w5 + $w6, $height, ': Bulanan', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($superbl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($superbp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($superb, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($superbl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($superbp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($superb, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, 'IV.', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, 'MUTASI', 'TR', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($mutasil, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($mutasip, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($mutasi, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($mutasil, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($mutasip, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($mutasi, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4, $height, '(Afdeling & Bibitan)', NULL, 0, 'L', 1);
        $pdf->Cell($w5 + $w6, $height, ': KHT', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($supertl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($supertp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($supert, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($supertl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($supertp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($supert, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, '', 'B', 0, 'C', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, '', 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, '', 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3 + $w4, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w5 + $w6, $height, ': KHL', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($superll, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($superlp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($superl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($superll, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($superlp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($superl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, 'V.', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, 'TURN OVER KARYAWAN (%)', 'TR', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rhaturnl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rhaturnp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rhaturn, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rhaturnsdl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rhaturnsdp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rhaturnsd, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w4 + $w5 + $w6, $height, '- Rasio Supervisi/Ha', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rhasuperl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rhasuperp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rhasuper, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rhasuperl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rhasuperp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rhasuper, 2), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, '', 'B', 0, 'C', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, '', 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, '', 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4, $height, '2).Kantor & Lain-lain', NULL, 0, 'L', 1);
        $pdf->Cell($w5 + $w6, $height, ': Bulanan', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($kantorbl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($kantorbp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($kantorb, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($kantorbl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($kantorbp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($kantorb, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, 'VI.', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, '% HARI KERJA EFEKTIF', 'TR', 0, 'L', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, '', 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, '', 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3 + $w4, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w5 + $w6, $height, ': KHT', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($kantortl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($kantortp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($kantort, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($kantortl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($kantortp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($kantort, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '1.', NULL, 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, 'Absensi Dibayar', 'R', 0, 'L', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, numberformat($dibayar, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, numberformat($sddibayar, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3 + $w4, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w5 + $w6, $height, ': KHL', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($kantorll, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($kantorlp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($kantorl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($kantorll, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($kantorlp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($kantorl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '2.', NULL, 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, 'Absensi Tidak Dibayar', 'R', 0, 'L', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, numberformat($tdibayar, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, numberformat($sdtdibayar, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w4 + $w5 + $w6, $height, '- Rasio Karyawan Kantor & Lain-lain/Ha', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rhakantorl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rhakantorp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rhakantor, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rhakantorl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rhakantorp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rhakantor, 2), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '3.', NULL, 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, 'Hari Libur (Besar dan Minggu)', 'R', 0, 'L', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, numberformat($libur, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, numberformat($sdlibur, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w3 + $w4 + $w5, $height, 'KARYAWAN LANGSUNG + TIDAK LANGSUNG', NULL, 0, 'L', 1);
        $pdf->Cell($w6, $height, '2+3', 'R', 0, 'R', 1);
        $pdf->Cell($w7, $height, numberformat($ltlangsungbl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($ltlangsungbp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($ltlangsungb, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($ltlangsungbl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($ltlangsungbp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($ltlangsungb, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '4.', NULL, 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, 'Hari Efektif', 'R', 0, 'L', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, numberformat($he, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, numberformat($sdhe, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w4, $height, '- Karyawan', NULL, 0, 'L', 1);
        $pdf->Cell($w5 + $w6, $height, ': Bulanan', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($ltlangsungbl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($ltlangsungbp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($ltlangsungb, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($ltlangsungbl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($ltlangsungbp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($ltlangsungb, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '5.', NULL, 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, 'Hari Kerja Efektif', 'R', 0, 'L', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, numberformat($hke, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, numberformat($sdhke, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3 + $w4, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w5 + $w6, $height, ': KHT', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($ltlangsungtl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($ltlangsungtp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($ltlangsungt, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($ltlangsungtl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($ltlangsungtp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($ltlangsungt, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w2, $height, '6.', NULL, 0, 'R', 1);
        $pdf->Cell($w3 + $w4 + $w5 + $w6, $height, '% Hari Efektif', 'R', 0, 'L', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, numberformat($phe, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, numberformat($sdphe, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3 + $w4, $height, '', 'L', 0, 'C', 1);
        $pdf->Cell($w5 + $w6, $height, ': KHL', 'R', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($ltlangsungll, 0), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($ltlangsunglp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($ltlangsungl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($ltlangsungll, 0), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($ltlangsunglp, 0), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($ltlangsungl, 0), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, 'VII.', 'L', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, 'PERUMAHAN', 'TR', 0, 'L', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, numberformat($rumah, 0), 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, numberformat($rumah, 0), 1, 0, 'R', 1);
        $pdf->Ln();
        $pdf->Cell($w1 + $w2 + $w3, $height, '', 'LB', 0, 'C', 1);
        $pdf->Cell($w4 + $w5 + $w6, $height, '- Rasio Karyawan L+TL/Ha', 'RB', 0, 'L', 1);
        $pdf->Cell($w7, $height, numberformat($rhaltlangsungl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w8, $height, numberformat($rhaltlangsungp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w9, $height, numberformat($rhaltlangsung, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10, $height, numberformat($rhaltlangsungl, 2), 1, 0, 'R', 1);
        $pdf->Cell($w11, $height, numberformat($rhaltlangsungp, 2), 1, 0, 'R', 1);
        $pdf->Cell($w12, $height, numberformat($rhaltlangsung, 2), 1, 0, 'R', 1);
        $pdf->Cell($w1, $height, '', 'LB', 0, 'C', 1);
        $pdf->Cell($w2 + $w3 + $w4 + $w5 + $w6, $height, '- Rasio Rumah/Karyawan', 'RB', 0, 'L', 1);
        $pdf->Cell($w7 + $w8 + $w9, $height, numberformat($rharumah, 2), 1, 0, 'R', 1);
        $pdf->Cell($w10 + $w11 + $w12, $height, numberformat($rharumah, 2), 1, 0, 'R', 1);
        $pdf->Ln();

        $pdf->Output();
        break;

    default:
        break;
}
?>
