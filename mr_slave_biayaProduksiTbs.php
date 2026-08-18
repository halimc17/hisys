<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');


$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$pt = checkPostGet('pt', '');
$afdeling = checkPostGet('afdeling', '');
$inti = checkPostGet('inti', '');

// dapatkan tahun bulan
$qwe = explode('-', $periode);
$tahun = $qwe[0];
$bulan = $qwe[1];

$unitunit = "('')";

// default: pt
$kodeorg = " like '" . $pt . "%'";

// kalo pt doang, dapatkan unit-unitnya
if ($unit == '') {
    $unitunit = "(";
    $str = "select kodeorganisasi from " . $dbname . ".organisasi 
        where induk='" . $pt . "' and tipe='KEBUN'";
    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $unitunit.="'" . $res['kodeorganisasi'] . "',";
    }
    $unitunit = substr($unitunit, 0, -1);
    $unitunit = $unitunit . ")";
    $kodeorg = " in " . $unitunit;
} else if ($afdeling == '')
    $kodeorg = " like '" . $unit . "%'"; // semua afdeling
else if ($afdeling != '')
    $kodeorg = " like '" . $afdeling . "%'"; // afdeling tertentu

if ($pt == '')
    $kodeorg = " like '" . $pt . "%'";

// selain preview excel pdf
if ($proses == 'getkebun' || $proses == 'getafdeling') {
    if ($proses == 'getkebun') {
        $optkebun = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi 
            where induk='" . $pt . "' and tipe='KEBUN'";
        $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while ($res = $query->fetch()) {
            $optkebun.="<option value=" . $res['kodeorganisasi'] . ">" . $res['namaorganisasi'] . "</option>";
        }
        if ($pt == '')
            $optkebun = "<option value=''></option>";
        echo $optkebun;
    }
    if ($proses == 'getafdeling') {
        $optkebun = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi 
            where induk='" . $unit . "' and tipe='AFDELING'";
        $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        while ($res = $query->fetch()) {
            $optkebun.="<option value=" . $res['kodeorganisasi'] . ">" . $res['namaorganisasi'] . "</option>";
        }
        if ($unit == '')
            $optkebun = "<option value=''></option>";
        echo $optkebun;
    }
// preview excel pdf    
}else {
    if ($afdeling != '')
        $kode_org = " kodeorg " . $kodeorg;
    else
        $kode_org = " substr(kodeorg,1,4) " . $kodeorg;
    if ($afdeling != '')
        $kode_blk = " kodeblok " . $kodeorg;
    else
        $kode_blk = " substr(kodeblok,1,4) " . $kodeorg;
    // $kode_blk2 khusus untuk data bulan ini dan sd bulan ini
    // kalo pilihan afdeling kosong, pake kodeorg, kalo ada afdeling pake kodeblok
    if ($afdeling != '')
        $kode_blk2 = " kodeblok " . $kodeorg;
    else
        $kode_blk2 = " substr(kodeorg,1,4) " . $kodeorg;
    if ($afdeling != '')
        $blk = " blok " . $kodeorg;
    else
        $blk = " substr(blok,1,4) " . $kodeorg;

//    echo $kode_org.'<br>';
//    echo $kode_blk.'<br>';
//    echo $kode_blk2.'<br>';
//    echo $blk.'<br>';
    // noakun panen
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '61101' and '61102' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akuntm[$res['noakun']] = $res['noakun'];
        $akunpnn[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun pemeliharaan tm 1
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '62101' and '62102' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akuntm[$res['noakun']] = $res['noakun'];
        $akunpml[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun pemeliharaan tm 2
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '62104' and '62111' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akuntm[$res['noakun']] = $res['noakun'];
        $akunpml[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun pemupukan
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '62103' and '62103' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akuntm[$res['noakun']] = $res['noakun'];
        $akunppk[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun 711
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '71101' and '71199' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akun711[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun 712
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '71201' and '71299' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akun712[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun 713
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '71301' and '71399' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akun713[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun 714
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '71401' and '71499' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akun714[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun 71501
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '71501' and '71501' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akun71501[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun 716
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '71601' and '71699' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akun716[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun 71502
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 5 and noakun between '71502' and '71502' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akun71502[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }

    // noakun 7199999
    $str = "SELECT noakun, namaakun FROM " . $dbname . ".keu_5akun
        WHERE length( noakun ) = 7 and noakun between '7199999' and '7199999' order by noakun";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $akun7199999[$res['noakun']] = $res['noakun'];
        $namaakun[$res['noakun']] = $res['namaakun'];
    }


    // luas aktual
    $str = "SELECT kodeorg, intiplasma, (luasareaproduktif) as luas FROM " . $dbname . ".setup_blok
        WHERE " . $kode_org . " and statusblok='TM'";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        @$luasinmaaktu+=$res['luas'];
        if ($res['intiplasma'] == 'I')
            @$luasintiaktu+=$res['luas'];
        if ($res['intiplasma'] == 'P')
            @$luasplasaktu+=$res['luas'];
    }

    // produksi aktual bulan ini
    $str = "SELECT blok, intiplasma, (kgwbtanpabrondolan) as luas FROM " . $dbname . ".kebun_spb_vs_rencana_blok_vw
        WHERE " . $blk . " and periode = '" . $periode . "'";


    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        @$prodinmaaktu+=$res['luas'];
        if ($res['intiplasma'] == 'I')
            @$prodintiaktu+=$res['luas'];
        if ($res['intiplasma'] == 'P')
            @$prodplasaktu+=$res['luas'];
    }

    // produksi aktual sd bulan ini
    $str = "SELECT blok, intiplasma, (kgwbtanpabrondolan) as luas FROM " . $dbname . ".kebun_spb_vs_rencana_blok_vw
        WHERE " . $blk . " and periode between '" . $tahun . "-01' and '" . $periode . "'";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        @$prsdinmaaktu+=$res['luas'];
        if ($res['intiplasma'] == 'I')
            @$prsdintiaktu+=$res['luas'];
        if ($res['intiplasma'] == 'P')
            @$prsdplasaktu+=$res['luas'];
    }

    // luas budget
    $str = "SELECT kodeblok, intiplasma, (hathnini) as luas FROM " . $dbname . ".bgt_blok
        WHERE " . $kode_blk . " and statusblok='TM'";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        @$luasinmaangg+=$res['luas'];
        if ($res['intiplasma'] == 'I')
            @$luasintiangg+=$res['luas'];
        if ($res['intiplasma'] == 'P')
            @$luasplasangg+=$res['luas'];
    }

    //produksi budget bulan ini
    $str = "SELECT kodeblok, intiplasma, (kg" . $bulan . ") as luas FROM " . $dbname . ".bgt_produksi_kbn_kg_vw
        WHERE " . $kode_blk . " and tahunbudget = '" . $tahun . "'";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        @$prodinmaangg+=$res['luas'];
        if ($res['intiplasma'] == 'I')
            @$prodintiangg+=$res['luas'];
        if ($res['intiplasma'] == 'P')
            @$prodplasangg+=$res['luas'];
    }

    $addstr = "(";
    for ($W = 1; $W <= intval($bulan); $W++) {
        if ($W < 10)
            $jack = "kg0" . $W;
        else
            $jack = "kg" . $W;
        if ($W < intval($bulan))
            $addstr.=$jack . "+";
        else
            $addstr.=$jack;
    }
    $addstr.=")";

    //produksi budget sd bulan ini
    $str = "SELECT kodeblok, intiplasma, " . $addstr . " as luas FROM " . $dbname . ".bgt_produksi_kbn_kg_vw
        WHERE " . $kode_blk . " and tahunbudget = '" . $tahun . "'";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        @$prsdinmaangg+=$res['luas'];
        if ($res['intiplasma'] == 'I')
            @$prsdintiangg+=$res['luas'];
        if ($res['intiplasma'] == 'P')
            @$prsdplasangg+=$res['luas'];
    }

    $addstr = "(";
    for ($W = 1; $W <= 12; $W++) {
        if ($W < 10)
            $jack = "kg0" . $W;
        else
            $jack = "kg" . $W;
        if ($W < 12)
            $addstr.=$jack . "+";
        else
            $addstr.=$jack;
    }
    $addstr.=")";

    //produksi budget setahun
    $str = "SELECT kodeblok, intiplasma, " . $addstr . " as luas FROM " . $dbname . ".bgt_produksi_kbn_kg_vw
        WHERE " . $kode_blk . " and tahunbudget = '" . $tahun . "'";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        @$prthinmaangg+=$res['luas'];
        if ($res['intiplasma'] == 'I')
            @$prthintiangg+=$res['luas'];
        if ($res['intiplasma'] == 'P')
            @$prthplasangg+=$res['luas'];
    }

    // data aktual bulan ini (total)
    $str = "SELECT kodeblok, intiplasma, substr(noakun,1,5) as noakun, debet, kredit FROM " . $dbname . ".keu_jurnalsum_blok_vw
        WHERE " . $kode_blk2 . " and periode = '" . $periode . "' and noakun between '6110000' and '7199999'";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        @$datainmaaktu[$res['noakun']]+=($res['debet'] - $res['kredit']);
        if ($res['intiplasma'] == 'I')
            @$dataintiaktu[$res['noakun']]+=($res['debet'] - $res['kredit']);
        else
        if ($res['intiplasma'] == 'P')
            @$dataplasaktu[$res['noakun']]+=($res['debet'] - $res['kredit']);
        else
            @$dataundeaktu[$res['noakun']]+=($res['debet'] - $res['kredit']);
        if (substr($res['noakun'], 0, 3) == '711') {
            @$d7tainmaaktu[711]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7taintiaktu[711]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7taplasaktu[711]+=($res['debet'] - $res['kredit']);
            else
                @$d7taundeaktu[711]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 3) == '712') {
            @$d7tainmaaktu[712]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7taintiaktu[712]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7taplasaktu[712]+=($res['debet'] - $res['kredit']);
            else
                @$d7taundeaktu[712]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 3) == '713') {
            @$d7tainmaaktu[713]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7taintiaktu[713]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7taplasaktu[713]+=($res['debet'] - $res['kredit']);
            else
                @$d7taundeaktu[713]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 3) == '714') {
            @$d7tainmaaktu[714]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7taintiaktu[714]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7taplasaktu[714]+=($res['debet'] - $res['kredit']);
            else
                @$d7taundeaktu[714]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 5) == '71501') {
            @$d7tainmaaktu[71501]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7taintiaktu[71501]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7taplasaktu[71501]+=($res['debet'] - $res['kredit']);
            else
                @$d7taundeaktu[71501]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 3) == '716') {
            @$d7tainmaaktu[716]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7taintiaktu[716]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7taplasaktu[716]+=($res['debet'] - $res['kredit']);
            else
                @$d7taundeaktu[716]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 5) == '71502') {
            @$d9tainmaaktu[71502]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d9taintiaktu[71502]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d9taplasaktu[71502]+=($res['debet'] - $res['kredit']);
            else
                @$d9taundeaktu[71502]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 5) == '71999') {
            @$d9tainmaaktu[71999]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d9taintiaktu[71999]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d9taplasaktu[71999]+=($res['debet'] - $res['kredit']);
            else
                @$d9taundeaktu[71999]+=($res['debet'] - $res['kredit']);
        }
    }


    // data aktual sd bulan ini (total)
    $str = "SELECT kodeblok, intiplasma, substr(noakun,1,5) as noakun, debet, kredit FROM " . $dbname . ".keu_jurnalsum_blok_vw
        WHERE " . $kode_blk2 . " and periode between '" . $tahun . "-01' and '" . $periode . "' and (noakun like '621%' or noakun like '611%' or noakun like '7%')";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        @$dasdinmaaktu[$res['noakun']]+=($res['debet'] - $res['kredit']);
        if ($res['intiplasma'] == 'I')
            @$dasdintiaktu[$res['noakun']]+=($res['debet'] - $res['kredit']);
        else
        if ($res['intiplasma'] == 'P')
            @$dasdplasaktu[$res['noakun']]+=($res['debet'] - $res['kredit']);
        else
            @$dasdundeaktu[$res['noakun']]+=($res['debet'] - $res['kredit']);
        if (substr($res['noakun'], 0, 3) == '711') {
            @$d7sdinmaaktu[711]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7sdintiaktu[711]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7sdplasaktu[711]+=($res['debet'] - $res['kredit']);
            else
                @$d7sdundeaktu[711]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 3) == '712') {
            @$d7sdinmaaktu[712]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7sdintiaktu[712]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7sdplasaktu[712]+=($res['debet'] - $res['kredit']);
            else
                @$d7sdundeaktu[712]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 3) == '713') {
            @$d7sdinmaaktu[713]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7sdintiaktu[713]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7sdplasaktu[713]+=($res['debet'] - $res['kredit']);
            else
                @$d7sdundeaktu[713]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 3) == '714') {
            @$d7sdinmaaktu[714]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7sdintiaktu[714]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7sdplasaktu[714]+=($res['debet'] - $res['kredit']);
            else
                @$d7sdundeaktu[714]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 5) == '71501') {
            $d7sdinmaaktu[71501]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7sdintiaktu[71501]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7sdplasaktu[71501]+=($res['debet'] - $res['kredit']);
            else
                $d7sdundeaktu[71501]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 3) == '716') {
            @$d7sdinmaaktu[716]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d7sdintiaktu[716]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d7sdplasaktu[716]+=($res['debet'] - $res['kredit']);
            else
                @$d7sdundeaktu[716]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 5) == '71502') {
            @$d9sdinmaaktu[71502]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d9sdintiaktu[71502]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d9sdplasaktu[71502]+=($res['debet'] - $res['kredit']);
            else
                @$d9sdundeaktu[71502]+=($res['debet'] - $res['kredit']);
        }
        if (substr($res['noakun'], 0, 5) == '71999') {
            @$d9sdinmaaktu[71999]+=($res['debet'] - $res['kredit']);
            if ($res['intiplasma'] == 'I')
                @$d9sdintiaktu[71999]+=($res['debet'] - $res['kredit']);
            else
            if ($res['intiplasma'] == 'P')
                @$d9sdplasaktu[71999]+=($res['debet'] - $res['kredit']);
            else
                @$d9sdundeaktu[71999]+=($res['debet'] - $res['kredit']);
        }
    }


    // data budget bulan ini (total) dan setahun
    $str = "SELECT kodeorg, intiplasma, substr(noakun,1,5) as noakun, (rp" . $bulan . ") as rp, rupiah FROM " . $dbname . ".bgt_budget_kebun_perblok_vw
        WHERE " . $kode_org . " and tahunbudget = '" . $tahun . "' and (noakun like '621%' or noakun like '611%' or noakun like '7%')";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        @$datainmaangg[$res['noakun']]+=$res['rp'];
        if ($res['intiplasma'] == 'I')
            @$dataintiangg[$res['noakun']]+=$res['rp'];
        if ($res['intiplasma'] == 'P')
            @$dataplasangg[$res['noakun']]+=$res['rp'];
        @$dathinmaangg[$res['noakun']]+=$res['rupiah'];
        if ($res['intiplasma'] == 'I')
            @$dathintiangg[$res['noakun']]+=$res['rupiah'];
        if ($res['intiplasma'] == 'P')
            @$dathplasangg[$res['noakun']]+=$res['rupiah'];
        if (substr($res['noakun'], 0, 3) == '711') {
            @$d7tainmaangg[711]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                @$d7taintiangg[711]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                @$d7taplasangg[711]+=$res['rp'];
            $d7thinmaangg[711]+=$res['rupiah'];
            if ($res['intiplasma'] == 'I')
                @$d7thintiangg[711]+=$res['rupiah'];
            if ($res['intiplasma'] == 'P')
                @$d7thplasangg[711]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 3) == '712') {
            @$d7tainmaangg[712]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                @$d7taintiangg[712]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                @$d7taplasangg[712]+=$res['rp'];
            @$d7thinmaangg[712]+=$res['rupiah'];
            if ($res['intiplasma'] == 'I')
                @$d7thintiangg[712]+=$res['rupiah'];
            if ($res['intiplasma'] == 'P')
                @$d7thplasangg[712]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 3) == '713') {
            @$d7tainmaangg[713]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                @$d7taintiangg[713]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                @$d7taplasangg[713]+=$res['rp'];
            @$d7thinmaangg[713]+=$res['rupiah'];
            if ($res['intiplasma'] == 'I')
                @$d7thintiangg[713]+=$res['rupiah'];
            if ($res['intiplasma'] == 'P')
                @$d7thplasangg[713]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 3) == '714') {
            @$d7tainmaangg[714]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                @$d7taintiangg[714]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                @$d7taplasangg[714]+=$res['rp'];
            @$d7thinmaangg[714]+=$res['rupiah'];
            if ($res['intiplasma'] == 'I')
                @$d7thintiangg[714]+=$res['rupiah'];
            if ($res['intiplasma'] == 'P')
                @$d7thplasangg[714]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 5) == '71501') {
            @$d7tainmaangg[71501]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                @$d7taintiangg[71501]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                @$d7taplasangg[71501]+=$res['rp'];
            @$d7thinmaangg[71501]+=$res['rupiah'];
            if ($res['intiplasma'] == 'I')
                @$d7thintiangg[71501]+=$res['rupiah'];
            if ($res['intiplasma'] == 'P')
                @$d7thplasangg[71501]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 3) == '716') {
            @$d7tainmaangg[716]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                @$d7taintiangg[716]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                @$d7taplasangg[716]+=$res['rp'];
            @$d7thinmaangg[716]+=$res['rupiah'];
            if ($res['intiplasma'] == 'I')
                @$d7thintiangg[716]+=$res['rupiah'];
            if ($res['intiplasma'] == 'P')
                @$d7thplasangg[716]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 5) == '71502') {
            @$d9tainmaangg[71502]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                @$d9taintiangg[71502]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                @$d9taplasangg[71502]+=$res['rp'];
            @$d9thinmaangg[71502]+=$res['rupiah'];
            if ($res['intiplasma'] == 'I')
                @$d9thintiangg[71502]+=$res['rupiah'];
            if ($res['intiplasma'] == 'P')
                @$d9thplasangg[71502]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 5) == '71999') {
            @$d9tainmaangg[71999]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                @$d9taintiangg[71999]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                @$d9taplasangg[71999]+=$res['rp'];
            @$d9thinmaangg[71999]+=$res['rupiah'];
            if ($res['intiplasma'] == 'I')
                @$d9thintiangg[71999]+=$res['rupiah'];
            if ($res['intiplasma'] == 'P')
                @$d9thplasangg[71999]+=$res['rupiah'];
        }
    }

//// jamhari
    $str = "select (rp" . $bulan . ") as rp,substr(noakun,1,5) as noakun,rupiah from " . $dbname . ".bgt_budget_detail 
          where " . $kode_org . " and tahunbudget='" . $tahun . "' and substr(noakun,1,1)='7'";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        if (substr($res['noakun'], 0, 3) == '711') {
            @$d7tainmaangg[711]+=$res['rp'];
            @$d7taintiangg[711]+=$res['rp'];
            @$d7taplasangg[711]+=$res['rp'];
            @$d7thinmaangg[711]+=$res['rupiah'];
            @$d7thintiangg[711]+=$res['rupiah'];
            @$d7thplasangg[711]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 3) == '712') {
            @$d7tainmaangg[712]+=$res['rp'];
            @$d7taintiangg[712]+=$res['rp'];
            @$d7taplasangg[712]+=$res['rp'];
            @$d7thinmaangg[712]+=$res['rupiah'];
            @$d7thintiangg[712]+=$res['rupiah'];
            @$d7thplasangg[712]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 3) == '713') {
            @$d7tainmaangg[713]+=$res['rp'];
            @$d7taintiangg[713]+=$res['rp'];
            @$d7taplasangg[713]+=$res['rp'];
            @$d7thinmaangg[713]+=$res['rupiah'];
            @$d7thintiangg[713]+=$res['rupiah'];
            @$d7thplasangg[713]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 3) == '714') {
            @$d7tainmaangg[714]+=$res['rp'];
            @$d7taintiangg[714]+=$res['rp'];
            @$d7taplasangg[714]+=$res['rp'];
            @$d7thinmaangg[714]+=$res['rupiah'];
            @$d7thintiangg[714]+=$res['rupiah'];
            @$d7thplasangg[714]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 5) == '71501') {
            @$d7tainmaangg[71501]+=$res['rp'];
            @$d7taintiangg[71501]+=$res['rp'];
            @$d7taplasangg[71501]+=$res['rp'];
            @$d7thinmaangg[71501]+=$res['rupiah'];
            @$d7thintiangg[71501]+=$res['rupiah'];
            @$d7thplasangg[71501]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 3) == '716') {
            @$d7tainmaangg[716]+=$res['rp'];
            @$d7taintiangg[716]+=$res['rp'];
            @$d7taplasangg[716]+=$res['rp'];
            @$d7thinmaangg[716]+=$res['rupiah'];
            @$d7thintiangg[716]+=$res['rupiah'];
            @$d7thplasangg[716]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 5) == '71502') {
            @$d9tainmaangg[71502]+=$res['rp'];
            @$d9taintiangg[71502]+=$res['rp'];
            @$d9taplasangg[71502]+=$res['rp'];
            @$d9thinmaangg[71502]+=$res['rupiah'];
            @$d9thintiangg[71502]+=$res['rupiah'];
            @$d9thplasangg[71502]+=$res['rupiah'];
        }
        if (substr($res['noakun'], 0, 5) == '71999') {
            @$d9tainmaangg[71999]+=$res['rp'];
            @$d9taintiangg[71999]+=$res['rp'];
            @$d9taplasangg[71999]+=$res['rp'];
            @$d9thinmaangg[71999]+=$res['rupiah'];
            @$d9thintiangg[71999]+=$res['rupiah'];
            @$d9thplasangg[71999]+=$res['rupiah'];
        }
    }


    $addstr = "(";
    for ($W = 1; $W <= intval($bulan); $W++) {
        if ($W < 10)
            $jack = "rp0" . $W;
        else
            $jack = "rp" . $W;
        if ($W < intval($bulan))
            $addstr.=$jack . "+";
        else
            $addstr.=$jack;
    }
    $addstr.=")";

    // data budget sd bulan ini (total)
    $str = "SELECT kodeorg, intiplasma, substr(noakun,1,5) as noakun, " . $addstr . " as rp, rupiah as rupiah FROM " . $dbname . ".bgt_budget_kebun_perblok_vw
        WHERE " . $kode_org . " and tahunbudget = '" . $tahun . "' and (noakun like '621%' or noakun like '611%' or noakun like '7%')";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        $dasdinmaangg[$res['noakun']]+=$res['rp'];
        if ($res['intiplasma'] == 'I')
            $dasdintiangg[$res['noakun']]+=$res['rp'];
        if ($res['intiplasma'] == 'P')
            $dasdplasangg[$res['noakun']]+=$res['rp'];
        if (substr($res['noakun'], 0, 3) == '711') {
            $d7sdinmaangg[711]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                $d7sdintiangg[711]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                $d7sdplasangg[711]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 3) == '712') {
            $d7sdinmaangg[712]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                $d7sdintiangg[712]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                $d7sdplasangg[712]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 3) == '713') {
            $d7sdinmaangg[713]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                $d7sdintiangg[713]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                $d7sdplasangg[713]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 3) == '714') {
            $d7sdinmaangg[714]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                $d7sdintiangg[714]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                $d7sdplasangg[714]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 5) == '71501') {
            $d7sdinmaangg[71501]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                $d7sdintiangg[71501]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                $d7sdplasangg[71501]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 3) == '716') {
            $d7sdinmaangg[716]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                $d7sdintiangg[716]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                $d7sdplasangg[716]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 5) == '71502') {
            $d9sdinmaangg[71502]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                $d9sdintiangg[71502]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                $d9sdplasangg[71502]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 5) == '71999') {
            $d9sdinmaangg[71999]+=$res['rp'];
            if ($res['intiplasma'] == 'I')
                $d9sdintiangg[71999]+=$res['rp'];
            if ($res['intiplasma'] == 'P')
                $d9sdplasangg[71999]+=$res['rp'];
        }
    }

//// jamhari
    $str = "select " . $addstr . " as rp,substr(noakun,1,5) as noakun,rupiah as rupiah from " . $dbname . ".bgt_budget_detail 
          where " . $kode_org . " and tahunbudget='" . $tahun . "' and substr(noakun,1,1)='7'";

    $query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    while ($res = $query->fetch()) {
        if (substr($res['noakun'], 0, 3) == '711') {
            $d7sdinmaangg[711]+=$res['rp'];
            $d7sdintiangg[711]+=$res['rp'];
            $d7sdplasangg[711]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 3) == '712') {
            $d7sdinmaangg[712]+=$res['rp'];
            $d7sdintiangg[712]+=$res['rp'];
            $d7sdplasangg[712]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 3) == '713') {
            $d7sdinmaangg[713]+=$res['rp'];
            $d7sdintiangg[713]+=$res['rp'];
            $d7sdplasangg[713]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 3) == '714') {
            $d7sdinmaangg[714]+=$res['rp'];
            $d7sdintiangg[714]+=$res['rp'];
            $d7sdplasangg[714]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 5) == '71501') {
            $d7sdinmaangg[71501]+=$res['rp'];
            $d7sdintiangg[71501]+=$res['rp'];
            $d7sdplasangg[71501]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 3) == '716') {
            $d7sdinmaangg[716]+=$res['rp'];
            $d7sdintiangg[716]+=$res['rp'];
            $d7sdplasangg[716]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 5) == '71502') {
            $d9sdinmaangg[71502]+=$res['rp'];
            $d9sdintiangg[71502]+=$res['rp'];
            $d9sdplasangg[71502]+=$res['rp'];
        }
        if (substr($res['noakun'], 0, 5) == '71999') {
            $d9sdinmaangg[71999]+=$res['rp'];
            $d9sdintiangg[71999]+=$res['rp'];
            $d9sdplasangg[71999]+=$res['rp'];
        }
    }


    if ($inti == 'inti') {
        $tampilinti = ' INTI';
    } else if ($inti == 'plasma') {
        $tampilinti = ' PLASMA';
    } else {
        $tampilinti = ' INTI DAN PLASMA';
    }
    if ($proses == 'excel') {
        $bg = " bgcolor=#DEDEDE";
        $brdr = 1;
        if ($pt == '')
            $tab.='PT (seluruhnya)';
        else
            $tab.='PT ' . $pt;
        if ($unit != '')
            $tab.=', UNIT ' . $unit;
        if ($afdeling != '')
            $tab.=', Afdeling ' . $afdeling;
        $tab.='<br>LAPORAN BIAYA PRODUKSI TANDAN BUAH SEGAR (' . $tampilinti . ')<br>01-' . $tahun . 'S/D ' . $bulan . '-' . $tahun;
    }
    else {
        $bg = "";
        $brdr = 0;
    }

    // hitung varian produksi
    @$prodinmavari = $prodinmaaktu - $prodinmaangg;
    @$prodintivari = $prodintiaktu - $prodintiangg;
    @$prodplasvari = $prodplasaktu - $prodplasangg;
    @$prsdinmavari = $prsdinmaaktu - $prsdinmaangg;
    @$prsdintivari = $prsdintiaktu - $prsdintiangg;
    @$prsdplasvari = $prsdplasaktu - $prsdplasangg;

    // hitung varian akun, hitung total
    if (!empty($akuntm))
        foreach ($akuntm as $akun) {
            @$datainmavari[$akun] = $datainmaaktu[$akun] - $datainmaangg[$akun];
            @$dataintivari[$akun] = $dataintiaktu[$akun] - $dataintiangg[$akun];
            @$dataplasvari[$akun] = $dataplasaktu[$akun] - $dataplasangg[$akun];
            @$dasdinmavari[$akun] = $dasdinmaaktu[$akun] - $dasdinmaangg[$akun];
            @$dasdintivari[$akun] = $dasdintiaktu[$akun] - $dasdintiangg[$akun];
            @$dasdplasvari[$akun] = $dasdplasaktu[$akun] - $dasdplasangg[$akun];

            // total inti
            @$totaintiaktu+=$dataintiaktu[$akun];
            @$totaintiangg+=$dataintiangg[$akun];
            @$totaintivari+=$dataintivari[$akun];
            @$tosdintiaktu+=$dasdintiaktu[$akun];
            @$tosdintiangg+=$dasdintiangg[$akun];
            @$tosdintivari+=$dasdintivari[$akun];
            @$tothintiangg+=$dathintiangg[$akun];

            // total plasma
            @$totaplasaktu+=$dataplasaktu[$akun];
            @$totaplasangg+=$dataplasangg[$akun];
            @$totaplasvari+=$dataplasvari[$akun];
            @$tosdplasaktu+=$dasdplasaktu[$akun];
            @$tosdplasangg+=$dasdplasangg[$akun];
            @$tosdplasvari+=$dasdplasvari[$akun];
            @$tothplasangg+=$dathplasangg[$akun];

            // total undef
            @$totaundeaktu+=$dataundeaktu[$akun];
            @$tosdundeaktu+=$dasdundeaktu[$akun];

            // total inti plasma
            @$totainmaaktu+=$datainmaaktu[$akun];
            @$totainmaangg+=$datainmaangg[$akun];
            @$totainmavari+=$datainmavari[$akun];
            @$tosdinmaaktu+=$dasdinmaaktu[$akun];
            @$tosdinmaangg+=$dasdinmaangg[$akun];
            @$tosdinmavari+=$dasdinmavari[$akun];
            @$tothinmaangg+=$dathinmaangg[$akun];
        }
    // hitung varian akun, hitung total

    if (!empty($akunpnn))
        foreach ($akunpnn as $akun) {
            // total inti
            @$pntaintiaktu+=$dataintiaktu[$akun];
            @$pntaintiangg+=$dataintiangg[$akun];
            @$pntaintivari+=$dataintivari[$akun];
            @$pnsdintiaktu+=$dasdintiaktu[$akun];
            @$pnsdintiangg+=$dasdintiangg[$akun];
            @$pnsdintivari+=$dasdintivari[$akun];
            @$pnthintiangg+=$dathintiangg[$akun];

            // total plasma
            @$pntaplasaktu+=$dataplasaktu[$akun];
            @$pntaplasangg+=$dataplasangg[$akun];
            @$pntaplasvari+=$dataplasvari[$akun];
            @$pnsdplasaktu+=$dasdplasaktu[$akun];
            @$pnsdplasangg+=$dasdplasangg[$akun];
            @$pnsdplasvari+=$dasdplasvari[$akun];
            @$pnthplasangg+=$dathplasangg[$akun];

            // total undef
            @$pntaundeaktu+=$dataundeaktu[$akun];
            @$pnsdundeaktu+=$dasdundeaktu[$akun];

            // total inti plasma
            @$pntainmaaktu+=$datainmaaktu[$akun];
            @$pntainmaangg+=$datainmaangg[$akun];
            @$pntainmavari+=$datainmavari[$akun];
            @$pnsdinmaaktu+=$dasdinmaaktu[$akun];
            @$pnsdinmaangg+=$dasdinmaangg[$akun];
            @$pnsdinmavari+=$dasdinmavari[$akun];
            @$pnthinmaangg+=$dathinmaangg[$akun];
        }

    if (!empty($akunpml))
        foreach ($akunpml as $akun) {
            // total inti
            @$pmtaintiaktu+=$dataintiaktu[$akun];
            @$pmtaintiangg+=$dataintiangg[$akun];
            @$pmtaintivari+=$dataintivari[$akun];
            @$pmsdintiaktu+=$dasdintiaktu[$akun];
            @$pmsdintiangg+=$dasdintiangg[$akun];
            @$pmsdintivari+=$dasdintivari[$akun];
            @$pmthintiangg+=$dathintiangg[$akun];

            // total plasma
            @$pmtaplasaktu+=$dataplasaktu[$akun];
            @$pmtaplasangg+=$dataplasangg[$akun];
            @$pmtaplasvari+=$dataplasvari[$akun];
            @$pmsdplasaktu+=$dasdplasaktu[$akun];
            @$pmsdplasangg+=$dasdplasangg[$akun];
            @$pmsdplasvari+=$dasdplasvari[$akun];
            @$pmthplasangg+=$dathplasangg[$akun];

            // total undef
            @$pmtaundeaktu+=$dataundeaktu[$akun];
            @$pmsdundeaktu+=$dasdundeaktu[$akun];

            // total inti plasma
            @$pmtainmaaktu+=$datainmaaktu[$akun];
            @$pmtainmaangg+=$datainmaangg[$akun];
            @$pmtainmavari+=$datainmavari[$akun];
            @$pmsdinmaaktu+=$dasdinmaaktu[$akun];
            @$pmsdinmaangg+=$dasdinmaangg[$akun];
            @$pmsdinmavari+=$dasdinmavari[$akun];
            @$pmthinmaangg+=$dathinmaangg[$akun];
        }

    if (!empty($akunppk))
        foreach ($akunppk as $akun) {
            // total inti
            @$pktaintiaktu+=$dataintiaktu[$akun];
            @$pktaintiangg+=$dataintiangg[$akun];
            @$pktaintivari+=$dataintivari[$akun];
            @$pksdintiaktu+=$dasdintiaktu[$akun];
            @$pksdintiangg+=$dasdintiangg[$akun];
            @$pksdintivari+=$dasdintivari[$akun];
            @$pkthintiangg+=$dathintiangg[$akun];

            // total plasma
            @$pktaplasaktu+=$dataplasaktu[$akun];
            @$pktaplasangg+=$dataplasangg[$akun];
            @$pktaplasvari+=$dataplasvari[$akun];
            @$pksdplasaktu+=$dasdplasaktu[$akun];
            @$pksdplasangg+=$dasdplasangg[$akun];
            @$pksdplasvari+=$dasdplasvari[$akun];
            @$pkthplasangg+=$dathplasangg[$akun];

            // total undef
            @$pktaundeaktu+=$dataundeaktu[$akun];
            @$pksdundeaktu+=$dasdundeaktu[$akun];

            // total inti plasma
            @$pktainmaaktu+=$datainmaaktu[$akun];
            @$pktainmaangg+=$datainmaangg[$akun];
            @$pktainmavari+=$datainmavari[$akun];
            @$pksdinmaaktu+=$dasdinmaaktu[$akun];
            @$pksdinmaangg+=$dasdinmaangg[$akun];
            @$pksdinmavari+=$dasdinmavari[$akun];
            @$pkthinmaangg+=$dathinmaangg[$akun];
        }
    $bawah[711] = 711;
    $bawah[712] = 712;
    $bawah[713] = 713;
    $bawah[714] = 714;
    $bawah[71501] = 71501;
    $bawah[716] = 716;
    $bawah[0] = 0;
    $nomorbawah[0] = '7XXXXXX';
    $nomorbawah[711] = '711XXXX';
    $nomorbawah[712] = '712XXXX';
    $nomorbawah[713] = '713XXXX';
    $nomorbawah[714] = '714XXXX';
    $nomorbawah[71501] = '71501XX';
    $nomorbawah[716] = '716XXXX';
    $namabawah[0] = 'Biaya Umum';
    $namabawah[711] = 'Karyawan';
    $namabawah[712] = 'Operasional Karyawan';
    $namabawah[713] = 'Operasional Mess dan Kantor';
    $namabawah[714] = 'Pemeliharaan';
    $namabawah[71501] = 'Pembelian Barang Inventaris';
    $namabawah[716] = 'Biaya Lainnya';
//    $batal[0]=0;
    $batal[71502] = 71502;
    $batal[71999] = 71999;
    $nomorbatal[0] = '';
    $nomorbatal[71502] = '71502XX';
    $nomorbatal[71999] = '7199999';
    $namabatal[0] = 'Depresiasi dan Alokasi';
    $namabatal[71502] = 'Penyusutan';
    $namabatal[71999] = 'Biaya Overhead Alokasi';

                         

    if (!empty($bawah))
        foreach ($bawah as $nomor) {
            @$d7taintivari[$nomor] = $d7taintiaktu[$nomor] - $d7taintiangg[$nomor];
            @$d7sdintivari[$nomor] = $d7sdintiaktu[$nomor] - $d7sdintiangg[$nomor];

            @$d7taplasvari[$nomor] = $d7taplasaktu[$nomor] - $d7taplasangg[$nomor];
            @$d7sdplasvari[$nomor] = $d7sdplasaktu[$nomor] - $d7sdplasangg[$nomor];

            @$d7tainmavari[$nomor] = $d7tainmaaktu[$nomor] - $d7tainmaangg[$nomor];
            @$d7sdinmavari[$nomor] = $d7sdinmaaktu[$nomor] - $d7sdinmaangg[$nomor];
        }

    // jumlahkan 711-716
    if (!empty($bawah))
        foreach ($bawah as $nomor) {
            if ($nomor != 0) { // harus di giniin biar ga ndobel
                // total inti
                @$d7taintiaktu[0]+=$d7taintiaktu[$nomor];
                @$d7taintiangg[0]+=$d7taintiangg[$nomor];
                @$d7taintivari[0]+=$d7taintivari[$nomor];
                @$d7sdintiaktu[0]+=$d7sdintiaktu[$nomor];
                @$d7sdintiangg[0]+=$d7sdintiangg[$nomor];
                @$d7sdintivari[0]+=$d7sdintivari[$nomor];
                @$d7thintiangg[0]+=$d7thintiangg[$nomor];

                // total plasma
                @$d7taplasaktu[0]+=$d7taplasaktu[$nomor];
                @$d7taplasangg[0]+=$d7taplasangg[$nomor];
                @$d7taplasvari[0]+=$d7taplasvari[$nomor];
                @$d7sdplasaktu[0]+=$d7sdplasaktu[$nomor];
                @$d7sdplasangg[0]+=$d7sdplasangg[$nomor];
                @$d7sdplasvari[0]+=$d7sdplasvari[$nomor];
                @$d7thplasangg[0]+=$d7thplasangg[$nomor];

                // total undef
                @$d7taundeaktu[0]+=$d7taundeaktu[$nomor];
                @$d7sdundeaktu[0]+=$d7sdundeaktu[$nomor];

                // total inti plasma
                @$d7tainmaaktu[0]+=$d7tainmaaktu[$nomor];
                @$d7tainmaangg[0]+=$d7tainmaangg[$nomor];
                @$d7tainmavari[0]+=$d7tainmavari[$nomor];
                @$d7sdinmaaktu[0]+=$d7sdinmaaktu[$nomor];
                @$d7sdinmaangg[0]+=$d7sdinmaangg[$nomor];
                @$d7sdinmavari[0]+=$d7sdinmavari[$nomor];
                @$d7thinmaangg[0]+=$d7thinmaangg[$nomor];
            }
        }

    // jumlahkan 71502-7199999
    if (!empty($batal))
        foreach ($batal as $nomor) {
            // total inti
            @$d9taintiaktu[0]+=$d9taintiaktu[$nomor];
            @$d9taintiangg[0]+=$d9taintiangg[$nomor];
            @$d9taintivari[0]+=$d9taintivari[$nomor];
            @$d9sdintiaktu[0]+=$d9sdintiaktu[$nomor];
            @$d9sdintiangg[0]+=$d9sdintiangg[$nomor];
            @$d9sdintivari[0]+=$d9sdintivari[$nomor];
            @$d9thintiangg[0]+=$d9thintiangg[$nomor];

            // total plasma
            @$d9taplasaktu[0]+=$d9taplasaktu[$nomor];
            @$d9taplasangg[0]+=$d9taplasangg[$nomor];
            @$d9taplasvari[0]+=$d9taplasvari[$nomor];
            @$d9sdplasaktu[0]+=$d9sdplasaktu[$nomor];
            @$d9sdplasangg[0]+=$d9sdplasangg[$nomor];
            @$d9sdplasvari[0]+=$d9sdplasvari[$nomor];
            @$d9thplasangg[0]+=$d9thplasangg[$nomor];

            // total undef
            @$d9taundeaktu[0]+=$d9taundeaktu[$nomor];
            @$d9sdundeaktu[0]+=$d9sdundeaktu[$nomor];

            // total inti plasma
            @$d9tainmaaktu[0]+=$d9tainmaaktu[$nomor];
            @$d9tainmaangg[0]+=$d9tainmaangg[$nomor];
            @$d9tainmavari[0]+=$d9tainmavari[$nomor];
            @$d9sdinmaaktu[0]+=$d9sdinmaaktu[$nomor];
            @$d9sdinmaangg[0]+=$d9sdinmaangg[$nomor];
            @$d9sdinmavari[0]+=$d9sdinmavari[$nomor];
            @$d9thinmaangg[0]+=$d9thinmaangg[$nomor];
        }


    // total sebelum depresiasi
    {
        // total inti
        @$tstaintiaktu+=$totaintiaktu + $d7taintiaktu[0];
        @$tstaintiangg+=$totaintiangg + $d7taintiangg[0];
        @$tstaintivari+=$tstaintiaktu - $tstaintiangg;
        @$tssdintiaktu+=$tosdintiaktu + $d7sdintiaktu[0];
        @$tssdintiangg+=$tosdintiangg + $d7sdintiangg[0];
        @$tssdintivari+=$tssdintiaktu - $tssdintiangg;
        @$tsthintiangg+=$tothintiangg + $d7thintiangg[0];

        // total plasma
        @$tstaplasaktu+=$totaplasaktu + $d7taplasaktu[0];
        @$tstaplasangg+=$totaplasangg + $d7taplasangg[0];
        @$tstaplasvari+=$tstaplasaktu - $tstaplasangg;
        @$tssdplasaktu+=$tosdplasaktu + $d7sdplasaktu[0];
        @$tssdplasangg+=$tosdplasangg + $d7sdplasangg[0];
        @$tssdplasvari+=$tssdplasaktu - $tssdplasangg;
        @$tsthplasangg+=$tothplasangg + $d7thplasangg[0];

        // total undef
        @$tstaundeaktu+=$totaundeaktu + $d7taundeaktu[0];
        @$tssdundeaktu+=$tosdundeaktu + $d7sdundeaktu[0];
//echo "<br>sebelum:".$tssdundeaktu;    
//echo "<br>total:".$tosdundeaktu;         
//echo "<br>kepala7:".$d7sdundeaktu[0];         
        // total inti plasma
        @$tstainmaaktu+=$totainmaaktu + $d7tainmaaktu[0];
        @$tstainmaangg+=$totainmaangg + $d7tainmaangg[0];
        @$tstainmavari+=$tstainmaaktu - $tstainmaangg;
        @$tssdinmaaktu+=$tosdinmaaktu + $d7sdinmaaktu[0];
        @$tssdinmaangg+=$tosdinmaangg + $d7sdinmaangg[0];
        @$tssdinmavari+=$tssdinmaaktu - $tssdinmaangg;
        @$tsthinmaangg+=$tothinmaangg + $d7thinmaangg[0];
    }

    // total 
    {
        // total inti
        @$tttaintiaktu+=$tstaintiaktu + $d9taintiaktu[0];
        @$tttaintiangg+=$tstaintiangg + $d9taintiangg[0];
        @$tttaintivari+=$tttaintiaktu - $tttaintiangg;
        @$ttsdintiaktu+=$tssdintiaktu + $d9sdintiaktu[0];
        @$ttsdintiangg+=$tssdintiangg + $d9sdintiangg[0];
        @$ttsdintivari+=$ttsdintiaktu - $ttsdintiangg;
        @$ttthintiangg+=$tsthintiangg + $d9thintiangg[0];

        // total plasma
        @$tttaplasaktu+=$tstaplasaktu + $d9taplasaktu[0];
        @$tttaplasangg+=$tstaplasangg + $d9taplasangg[0];
        @$tttaplasvari+=$tttaplasaktu - $tttaplasangg;
        @$ttsdplasaktu+=$tssdplasaktu + $d9sdplasaktu[0];
        @$ttsdplasangg+=$tssdplasangg + $d9sdplasangg[0];
        @$ttsdplasvari+=$ttsdplasaktu - $ttsdplasangg;
        @$ttthplasangg+=$tsthplasangg + $d9thplasangg[0];

        // total undef
        @$tttaundeaktu+=$tstaundeaktu + $d9taundeaktu[0];
        @$ttsdundeaktu+=$tssdundeaktu + $d9sdundeaktu[0];

        // total inti plasma
        @$tttainmaaktu+=$tstainmaaktu + $d9tainmaaktu[0];
        @$tttainmaangg+=$tstainmaangg + $d9tainmaangg[0];
        @$tttainmavari+=$tttainmaaktu - $tttainmaangg;
        @$ttsdinmaaktu+=$tssdinmaaktu + $d9sdinmaaktu[0];
        @$ttsdinmaangg+=$tssdinmaangg + $d9sdinmaangg[0];
        @$ttsdinmavari+=$ttsdinmaaktu - $ttsdinmaangg;
        @$ttthinmaangg+=$tsthinmaangg + $d9thinmaangg[0];
    }

    // header ==================================================================

    if ($inti == 'inti') {
        $showinti = 'Inti';
        $showluasaktu = $luasintiaktu;
        $showluasangg = $luasintiangg;

        $showprodaktu = $prodintiaktu;
        $showprodangg = $prodintiangg;
        $showprodvari = $prodintivari;
        $showprsdaktu = $prsdintiaktu;
        $showprsdangg = $prsdintiangg;
        $showprsdvari = $prsdintivari;
        $showprthangg = $prthintiangg;
    } else
    if ($inti == 'plasma') {
        $showinti = 'Plasma';
        $showluasaktu = $luasplasaktu;
        $showluasangg = $luasplasangg;

        $showprodaktu = $prodplasaktu;
        $showprodangg = $prodplasangg;
        $showprodvari = $prodplasvari;
        $showprsdaktu = $prsdplasaktu;
        $showprsdangg = $prsdplasangg;
        $showprsdvari = $prsdplasvari;
        $showprthangg = $prthplasangg;
    } else {
        $showinti = 'Inti dan Plasma';
        $showluasaktu = $luasinmaaktu;
        $showluasangg = $luasinmaangg;

        $prodinmaaktu = isset($prodinmaaktu) ? $prodinmaaktu : '';
        $showprodaktu = $prodinmaaktu;
        $showprodangg = $prodinmaangg;
        $showprodvari = $prodinmavari;
        $showprsdaktu = $prsdinmaaktu;
        $showprsdangg = $prsdinmaangg;
        $showprsdvari = $prsdinmavari;
        $showprthangg = $prthinmaangg;
    }

    $lebkolkiri = 200;

    $tab.="
        <table cellpadding=1 cellspacing=1 border=" . $brdr . " class=sortable style='width:100%'>
        <thead class=rowheader>
        <tr>
        <td align=center width=" . $lebkolkiri . " colspan=3 rowspan=3 " . $bg . ">Keterangan</td>
        <td align=center colspan=2 " . $bg . ">Undefined Block</td>
        <td align=center colspan=19 " . $bg . ">" . $showinti . "</td>
        </tr>";
    $tab.="<tr><td align=center " . $bg . ">Bulan Ini</td>
        <td align=center " . $bg . ">SD BI</td>
        <td align=center colspan=6 " . $bg . ">Bulan Ini</td>
        <td align=center colspan=2 rowspan=2 " . $bg . ">Varian</td>
        <td align=center colspan=6 " . $bg . ">SD BI</td>
        <td align=center colspan=2 rowspan=2 " . $bg . ">Varian</td>
        <td align=center colspan=3 rowspan=2 " . $bg . ">Budget Setahun</td>
        </tr>";
    $tab.="<tr><td align=center " . $bg . ">Aktual</td>
        <td align=center " . $bg . ">Aktual</td>
        <td align=center colspan=3 " . $bg . ">Aktual</td>
        <td align=center colspan=3 " . $bg . ">Budget</td>
        <td align=center colspan=3 " . $bg . ">Aktual</td>
        <td align=center colspan=3 " . $bg . ">Budget</td>
        </tr>";
    $tab.="<tr><td align=center width=" . $lebkolkiri . " colspan=3 " . $bg . ">Luas TM (ha)</td>
        <td align=right " . $bg . "></td>
        <td align=right " . $bg . "></td>
        <td align=right colspan=3 " . $bg . ">" . number_format($showluasaktu, 2) . "</td>
        <td align=right colspan=3 " . $bg . ">" . number_format($showluasangg, 2) . "</td>
        <td align=right colspan=2 " . $bg . "></td>
        <td align=right colspan=3 " . $bg . ">" . number_format($showluasaktu, 2) . "</td>
        <td align=right colspan=3 " . $bg . ">" . number_format($showluasangg, 2) . "</td>
        <td align=right colspan=2 " . $bg . "></td>
        <td align=right colspan=3 " . $bg . ">" . number_format($showluasangg, 2) . "</td>
        </tr>";

    @$prodintiakturibu = $prodintiaktu / 1000;
    @$prodintianggribu = $prodintiangg / 1000;
    @$prodintivariribu = $prodintivari / 1000;
    @$prsdintiakturibu = $prsdintiaktu / 1000;
    @$prsdintianggribu = $prsdintiangg / 1000;
    @$prsdintivariribu = $prsdintivari / 1000;
    @$prthintianggribu = $prthintiangg / 1000;

    @$prodplasakturibu = $prodplasaktu / 1000;
    @$prodplasanggribu = $prodplasangg / 1000;
    @$prodplasvariribu = $prodplasvari / 1000;
    @$prsdplasakturibu = $prsdplasaktu / 1000;
    @$prsdplasanggribu = $prsdplasangg / 1000;
    @$prsdplasvariribu = $prsdplasvari / 1000;
    @$prthplasanggribu = $prthplasangg / 1000;

    @$prodinmaakturibu = $prodinmaaktu / 1000;
    @$prodinmaanggribu = $prodinmaangg / 1000;
    @$prodinmavariribu = $prodinmavari / 1000;
    @$prsdinmaakturibu = $prsdinmaaktu / 1000;
    @$prsdinmaanggribu = $prsdinmaangg / 1000;
    @$prsdinmavariribu = $prsdinmavari / 1000;
    @$prthinmaanggribu = $prthinmaangg / 1000;
    $tab.="<tr><td align=center width=" . $lebkolkiri . " colspan=3 " . $bg . ">Produksi (ton)</td>
        <td align=right " . $bg . "></td>
        <td align=right " . $bg . "></td>
        <td align=right colspan=3 " . $bg . ">" . number_format($showprodaktu / 1000, 2) . "</td>
        <td align=right colspan=3 " . $bg . ">" . number_format($showprodangg / 1000, 2) . "</td>
        <td align=right colspan=2 " . $bg . ">" . number_format($showprodvari / 1000, 2) . "</td>
        <td align=right colspan=3 " . $bg . ">" . number_format($showprsdaktu / 1000, 2) . "</td>
        <td align=right colspan=3 " . $bg . ">" . number_format($showprsdangg / 1000, 2) . "</td>
        <td align=right colspan=2 " . $bg . ">" . number_format($showprsdvari / 1000, 2) . "</td>
        <td align=right colspan=3 " . $bg . ">" . number_format($showprthangg / 1000, 2) . "</td>
        </tr>";
    $tab.="<tr><td align=center width=" . $lebkolkiri . " colspan=3 " . $bg . "></td>
        <td align=center colspan=1 " . $bg . ">Rp.(000)</td>
        <td align=center colspan=1 " . $bg . ">Rp.(000)</td>
 
        <td align=center colspan=1 " . $bg . ">Rp.(000)</td>
        <td align=center colspan=1 " . $bg . ">Rp./kg</td>
        <td align=center colspan=1 " . $bg . ">Rp./Ha</td>
        <td align=center colspan=1 " . $bg . ">Rp.(000)</td>
        <td align=center colspan=1 " . $bg . ">Rp./kg</td>
        <td align=center colspan=1 " . $bg . ">Rp./Ha</td>
        <td align=center colspan=1 " . $bg . ">Rp.(000)</td>
        <td align=center colspan=1 " . $bg . ">Rp./kg</td>
        <td align=center colspan=1 " . $bg . ">Rp.(000)</td>
        <td align=center colspan=1 " . $bg . ">Rp./kg</td>
        <td align=center colspan=1 " . $bg . ">Rp./Ha</td>
        <td align=center colspan=1 " . $bg . ">Rp.(000)</td>
        <td align=center colspan=1 " . $bg . ">Rp./kg</td>
        <td align=center colspan=1 " . $bg . ">Rp./Ha</td>
        <td align=center colspan=1 " . $bg . ">Rp.(000)</td>
        <td align=center colspan=1 " . $bg . ">Rp./kg</td>
        <td align=center colspan=1 " . $bg . ">Rp.(000)</td>
        <td align=center colspan=1 " . $bg . ">Rp./kg</td>
        <td align=center colspan=1 " . $bg . ">Rp./Ha</td>
        </tr>";
    $tab.="</thead><tbody></tbody></table>
    ";
    $tab.="
        <table cellpadding=1 cellspacing=1 border=" . $brdr . " class=sortable style='width:100%'>
        <thead class=rowheader></thead><tbody>";
    $totin = array();
    // Biaya Langsung===========================================================   
    $totat = array();
    if (!empty($akuntm))
        foreach ($akuntm as $akun) {
            if ($akun == '61101') {
                // Biaya Panen  ====================================================
                $tab.="<tr class=rowtitle>
            <td align=left colspan=3 " . $bg . ">Panen dan Pengumpulan</td>";
                $pntal = 0;
                // inti bulan ini per
                @$pntaintiaktuperkg = $pntaintiaktu / $prodintiaktu;
                @$pntaintiaktuperha = $pntaintiaktu / $luasintiaktu;
                @$pntaintianggperkg = $pntaintiangg / $prodintiangg;
                @$pntaintianggperha = $pntaintiangg / $luasintiangg;
                @$pntaintivariperkg = $pntaintivari / $prodintivari;
                // inti sd bulan ini per
                @$pnsdintiaktuperkg = $pnsdintiaktu / $prsdintiaktu;
                @$pnsdintiaktuperha = $pnsdintiaktu / $luasintiaktu;
                @$pnsdintianggperkg = $pnsdintiangg / $prsdintiangg;
                @$pnsdintianggperha = $pnsdintiangg / $luasintiangg;
                @$pnsdintivariperkg = $pnsdintivari / $prsdintivari;
                // inti setahun per
                @$pnthintianggperkg = $pnthintiangg / $prthintiangg;
                @$pnthintianggperha = $pnthintiangg / $luasintiangg;

                // plasma bulan ini per
                @$pntaplasaktuperkg = $pntaplasaktu / $prodplasaktu;
                @$pntaplasaktuperha = $pntaplasaktu / $luasplasaktu;
                @$pntaplasanggperkg = $pntaplasangg / $prodplasangg;
                @$pntaplasanggperha = $pntaplasangg / $luasplasangg;
                @$pntaplasvariperkg = $pntaplasvari / $prodplasvari;
                // plasma sd bulan ini per
                @$pnsdplasaktuperkg = $pnsdplasaktu / $prsdplasaktu;
                @$pnsdplasaktuperha = $pnsdplasaktu / $luasplasaktu;
                @$pnsdplasanggperkg = $pnsdplasangg / $prsdplasangg;
                @$pnsdplasanggperha = $pnsdplasangg / $luasplasangg;
                @$pnsdplasvariperkg = $pnsdplasvari / $prsdplasvari;
                // plasma setahun per
                @$pnthplasanggperkg = $pnthplasangg / $prthplasangg;
                @$pnthplasanggperha = $pnthplasangg / $luasplasangg;

                // inti plasma bulan ini per
                @$pntainmaaktuperkg = $pntainmaaktu / $prodinmaaktu;
                @$pntainmaaktuperha = $pntainmaaktu / $luasinmaaktu;
                @$pntainmaanggperkg = $pntainmaangg / $prodinmaangg;
                @$pntainmaanggperha = $pntainmaangg / $luasinmaangg;
                @$pntainmavariperkg = $pntainmavari / $prodinmavari;
                // inti plasma sd bulan ini per
                @$pnsdinmaaktuperkg = $pnsdinmaaktu / $prsdinmaaktu;
                @$pnsdinmaaktuperha = $pnsdinmaaktu / $luasinmaaktu;
                @$pnsdinmaanggperkg = $pnsdinmaangg / $prsdinmaangg;
                @$pnsdinmaanggperha = $pnsdinmaangg / $luasinmaangg;
                @$pnsdinmavariperkg = $pnsdinmavari / $prsdinmavari;
                // inti plasma setahun per
                @$pnthinmaanggperkg = $pnthinmaangg / $prthinmaangg;
                @$pnthinmaanggperha = $pnthinmaangg / $luasinmaangg;

                @$pntaintiaktuperrb = $pntaintiaktu / 1000;
                @$pntaintianggperrb = $pntaintiangg / 1000;
                @$pntaintivariperrb = $pntaintivari / 1000;
                @$pnsdintiaktuperrb = $pnsdintiaktu / 1000;
                @$pnsdintianggperrb = $pnsdintiangg / 1000;
                @$pnsdintivariperrb = $pnsdintivari / 1000;
                @$pnthintianggperrb = $pnthintiangg / 1000;

                @$pntaplasaktuperrb = $pntaplasaktu / 1000;
                @$pntaplasanggperrb = $pntaplasangg / 1000;
                @$pntaplasvariperrb = $pntaplasvari / 1000;
                @$pnsdplasaktuperrb = $pnsdplasaktu / 1000;
                @$pnsdplasanggperrb = $pnsdplasangg / 1000;
                @$pnsdplasvariperrb = $pnsdplasvari / 1000;
                @$pnthplasanggperrb = $pnthplasangg / 1000;

                @$pntaundeaktuperrb = $pntaundeaktu / 1000;
                @$pnsdundeaktuperrb = $pnsdundeaktu / 1000;

                @$pntainmaaktuperrb = $pntainmaaktu / 1000;
                @$pntainmaanggperrb = $pntainmaangg / 1000;
                @$pntainmavariperrb = $pntainmavari / 1000;
                @$pnsdinmaaktuperrb = $pnsdinmaaktu / 1000;
                @$pnsdinmaanggperrb = $pnsdinmaangg / 1000;
                @$pnsdinmavariperrb = $pnsdinmavari / 1000;
                @$pnthinmaanggperrb = $pnthinmaangg / 1000;


                if ($inti == 'inti') {
                    $showpntaaktuperrb = $pntaintiaktuperrb;
                    $showpntaaktuperkg = $pntaintiaktuperkg;
                    $showpntaaktuperha = $pntaintiaktuperha;
                    $showpntaanggperrb = $pntaintianggperrb;
                    $showpntaanggperkg = $pntaintianggperkg;
                    $showpntaanggperha = $pntaintianggperha;
                    $showpntavariperrb = $pntaintivariperrb;
                    $showpntavariperkg = $pntaintivariperkg;

                    $showpnsdaktuperrb = $pnsdintiaktuperrb;
                    $showpnsdaktuperkg = $pnsdintiaktuperkg;
                    $showpnsdaktuperha = $pnsdintiaktuperha;
                    $showpnsdanggperrb = $pnsdintianggperrb;
                    $showpnsdanggperkg = $pnsdintianggperkg;
                    $showpnsdanggperha = $pnsdintianggperha;
                    $showpnsdvariperrb = $pnsdintivariperrb;
                    $showpnsdvariperkg = $pnsdintivariperkg;

                    $showpnthanggperrb = $pnthintianggperrb;
                    $showpnthanggperkg = $pnthintianggperkg;
                    $showpnthanggperha = $pnthintianggperha;
                } else if ($inti == 'plasma') {
                    $showpntaaktuperrb = $pntaplasaktuperrb;
                    $showpntaaktuperkg = $pntaplasaktuperkg;
                    $showpntaaktuperha = $pntaplasaktuperha;
                    $showpntaanggperrb = $pntaplasanggperrb;
                    $showpntaanggperkg = $pntaplasanggperkg;
                    $showpntaanggperha = $pntaplasanggperha;
                    $showpntavariperrb = $pntaplasvariperrb;
                    $showpntavariperkg = $pntaplasvariperkg;

                    $showpnsdaktuperrb = $pnsdplasaktuperrb;
                    $showpnsdaktuperkg = $pnsdplasaktuperkg;
                    $showpnsdaktuperha = $pnsdplasaktuperha;
                    $showpnsdanggperrb = $pnsdplasanggperrb;
                    $showpnsdanggperkg = $pnsdplasanggperkg;
                    $showpnsdanggperha = $pnsdplasanggperha;
                    $showpnsdvariperrb = $pnsdplasvariperrb;
                    $showpnsdvariperkg = $pnsdplasvariperkg;

                    $showpnthanggperrb = $pnthplasanggperrb;
                    $showpnthanggperkg = $pnthplasanggperkg;
                    $showpnthanggperha = $pnthplasanggperha;
                } else {
                    $showpntaaktuperrb = $pntainmaaktuperrb;
                    $showpntaaktuperkg = $pntainmaaktuperkg;
                    $showpntaaktuperha = $pntainmaaktuperha;
                    $showpntaanggperrb = $pntainmaanggperrb;
                    $showpntaanggperkg = $pntainmaanggperkg;
                    $showpntaanggperha = $pntainmaanggperha;
                    $showpntavariperrb = $pntainmavariperrb;
                    $showpntavariperkg = $pntainmavariperkg;

                    $showpnsdaktuperrb = $pnsdinmaaktuperrb;
                    $showpnsdaktuperkg = $pnsdinmaaktuperkg;
                    $showpnsdaktuperha = $pnsdinmaaktuperha;
                    $showpnsdanggperrb = $pnsdinmaanggperrb;
                    $showpnsdanggperkg = $pnsdinmaanggperkg;
                    $showpnsdanggperha = $pnsdinmaanggperha;
                    $showpnsdvariperrb = $pnsdinmavariperrb;
                    $showpnsdvariperkg = $pnsdinmavariperkg;

                    $showpnthanggperrb = $pnthinmaanggperrb;
                    $showpnthanggperkg = $pnthinmaanggperkg;
                    $showpnthanggperha = $pnthinmaanggperha;
                }
                $tab.="<td align=right " . $bg . ">" . number_format($pntaundeaktuperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($pnsdundeaktuperrb) . "</td>
                ";
                $tab.="
            <td align=right " . $bg . ">" . number_format($showpntaaktuperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpntaaktuperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpntaaktuperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpntaanggperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpntaanggperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpntaanggperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpntavariperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpntavariperkg) . "</td>

            <td align=right " . $bg . ">" . number_format($showpnsdaktuperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpnsdaktuperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpnsdaktuperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpnsdanggperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpnsdanggperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpnsdanggperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpnsdvariperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpnsdvariperkg) . "</td>

            <td align=right " . $bg . ">" . number_format($showpnthanggperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpnthanggperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpnthanggperha) . "</td>
                ";
                $tab.="</tr>";
            }
            if ($akun == '62101') {
                // Biaya Panen  ====================================================
                $tab.="<tr class=rowtitle>
            <td align=left colspan=3 " . $bg . ">Pemeliharaan TM</td>";
                $pmtal = 0;
                // inti bulan ini per
                @$pmtaintiaktuperkg = $pmtaintiaktu / $prodintiaktu;
                @$pmtaintiaktuperha = $pmtaintiaktu / $luasintiaktu;
                @$pmtaintianggperkg = $pmtaintiangg / $prodintiangg;
                @$pmtaintianggperha = $pmtaintiangg / $luasintiangg;
                @$pmtaintivariperkg = $pmtaintivari / $prodintivari;
                // inti sd bulan ini per
                @$pmsdintiaktuperkg = $pmsdintiaktu / $prsdintiaktu;
                @$pmsdintiaktuperha = $pmsdintiaktu / $luasintiaktu;
                @$pmsdintianggperkg = $pmsdintiangg / $prsdintiangg;
                @$pmsdintianggperha = $pmsdintiangg / $luasintiangg;
                @$pmsdintivariperkg = $pmsdintivari / $prsdintivari;
                // inti setahun per
                @$pmthintianggperkg = $pmthintiangg / $prthintiangg;
                @$pmthintianggperha = $pmthintiangg / $luasintiangg;

                // plasma bulan ini per
                @$pmtaplasaktuperkg = $pmtaplasaktu / $prodplasaktu;
                @$pmtaplasaktuperha = $pmtaplasaktu / $luasplasaktu;
                @$pmtaplasanggperkg = $pmtaplasangg / $prodplasangg;
                @$pmtaplasanggperha = $pmtaplasangg / $luasplasangg;
                @$pmtaplasvariperkg = $pmtaplasvari / $prodplasvari;
                // plasma sd bulan ini per
                @$pmsdplasaktuperkg = $pmsdplasaktu / $prsdplasaktu;
                @$pmsdplasaktuperha = $pmsdplasaktu / $luasplasaktu;
                @$pmsdplasanggperkg = $pmsdplasangg / $prsdplasangg;
                @$pmsdplasanggperha = $pmsdplasangg / $luasplasangg;
                @$pmsdplasvariperkg = $pmsdplasvari / $prsdplasvari;
                // plasma setahun per
                @$pmthplasanggperkg = $pmthplasangg / $prthplasangg;
                @$pmthplasanggperha = $pmthplasangg / $luasplasangg;

                // inti plasma bulan ini per
                @$pmtainmaaktuperkg = $pmtainmaaktu / $prodinmaaktu;
                @$pmtainmaaktuperha = $pmtainmaaktu / $luasinmaaktu;
                @$pmtainmaanggperkg = $pmtainmaangg / $prodinmaangg;
                @$pmtainmaanggperha = $pmtainmaangg / $luasinmaangg;
                @$pmtainmavariperkg = $pmtainmavari / $prodinmavari;
                // inti plasma sd bulan ini per
                @$pmsdinmaaktuperkg = $pmsdinmaaktu / $prsdinmaaktu;
                @$pmsdinmaaktuperha = $pmsdinmaaktu / $luasinmaaktu;
                @$pmsdinmaanggperkg = $pmsdinmaangg / $prsdinmaangg;
                @$pmsdinmaanggperha = $pmsdinmaangg / $luasinmaangg;
                @$pmsdinmavariperkg = $pmsdinmavari / $prsdinmavari;
                // inti plasma setahun per
                @$pmthinmaanggperkg = $pmthinmaangg / $prthinmaangg;
                @$pmthinmaanggperha = $pmthinmaangg / $luasinmaangg;

                @$pmtaintiaktuperrb = $pmtaintiaktu / 1000;
                @$pmtaintianggperrb = $pmtaintiangg / 1000;
                @$pmtaintivariperrb = $pmtaintivari / 1000;
                @$pmsdintiaktuperrb = $pmsdintiaktu / 1000;
                @$pmsdintianggperrb = $pmsdintiangg / 1000;
                @$pmsdintivariperrb = $pmsdintivari / 1000;
                @$pmthintianggperrb = $pmthintiangg / 1000;

                @$pmtaplasaktuperrb = $pmtaplasaktu / 1000;
                @$pmtaplasanggperrb = $pmtaplasangg / 1000;
                @$pmtaplasvariperrb = $pmtaplasvari / 1000;
                @$pmsdplasaktuperrb = $pmsdplasaktu / 1000;
                @$pmsdplasanggperrb = $pmsdplasangg / 1000;
                @$pmsdplasvariperrb = $pmsdplasvari / 1000;
                @$pmthplasanggperrb = $pmthplasangg / 1000;

                @$pmtaundeaktuperrb = $pmtaundeaktu / 1000;
                @$pmsdundeaktuperrb = $pmsdundeaktu / 1000;

                @$pmtainmaaktuperrb = $pmtainmaaktu / 1000;
                @$pmtainmaanggperrb = $pmtainmaangg / 1000;
                @$pmtainmavariperrb = $pmtainmavari / 1000;
                @$pmsdinmaaktuperrb = $pmsdinmaaktu / 1000;
                @$pmsdinmaanggperrb = $pmsdinmaangg / 1000;
                @$pmsdinmavariperrb = $pmsdinmavari / 1000;
                @$pmthinmaanggperrb = $pmthinmaangg / 1000;


                if ($inti == 'inti') {
                    $showpmtaaktuperrb = $pmtaintiaktuperrb;
                    $showpmtaaktuperkg = $pmtaintiaktuperkg;
                    $showpmtaaktuperha = $pmtaintiaktuperha;
                    $showpmtaanggperrb = $pmtaintianggperrb;
                    $showpmtaanggperkg = $pmtaintianggperkg;
                    $showpmtaanggperha = $pmtaintianggperha;
                    $showpmtavariperrb = $pmtaintivariperrb;
                    $showpmtavariperkg = $pmtaintivariperkg;

                    $showpmsdaktuperrb = $pmsdintiaktuperrb;
                    $showpmsdaktuperkg = $pmsdintiaktuperkg;
                    $showpmsdaktuperha = $pmsdintiaktuperha;
                    $showpmsdanggperrb = $pmsdintianggperrb;
                    $showpmsdanggperkg = $pmsdintianggperkg;
                    $showpmsdanggperha = $pmsdintianggperha;
                    $showpmsdvariperrb = $pmsdintivariperrb;
                    $showpmsdvariperkg = $pmsdintivariperkg;

                    $showpmthanggperrb = $pmthintianggperrb;
                    $showpmthanggperkg = $pmthintianggperkg;
                    $showpmthanggperha = $pmthintianggperha;
                } else if ($inti == 'plasma') {
                    $showpmtaaktuperrb = $pmtaplasaktuperrb;
                    $showpmtaaktuperkg = $pmtaplasaktuperkg;
                    $showpmtaaktuperha = $pmtaplasaktuperha;
                    $showpmtaanggperrb = $pmtaplasanggperrb;
                    $showpmtaanggperkg = $pmtaplasanggperkg;
                    $showpmtaanggperha = $pmtaplasanggperha;
                    $showpmtavariperrb = $pmtaplasvariperrb;
                    $showpmtavariperkg = $pmtaplasvariperkg;

                    $showpmsdaktuperrb = $pmsdplasaktuperrb;
                    $showpmsdaktuperkg = $pmsdplasaktuperkg;
                    $showpmsdaktuperha = $pmsdplasaktuperha;
                    $showpmsdanggperrb = $pmsdplasanggperrb;
                    $showpmsdanggperkg = $pmsdplasanggperkg;
                    $showpmsdanggperha = $pmsdplasanggperha;
                    $showpmsdvariperrb = $pmsdplasvariperrb;
                    $showpmsdvariperkg = $pmsdplasvariperkg;

                    $showpmthanggperrb = $pmthplasanggperrb;
                    $showpmthanggperkg = $pmthplasanggperkg;
                    $showpmthanggperha = $pmthplasanggperha;
                } else {
                    $showpmtaaktuperrb = $pmtainmaaktuperrb;
                    $showpmtaaktuperkg = $pmtainmaaktuperkg;
                    $showpmtaaktuperha = $pmtainmaaktuperha;
                    $showpmtaanggperrb = $pmtainmaanggperrb;
                    $showpmtaanggperkg = $pmtainmaanggperkg;
                    $showpmtaanggperha = $pmtainmaanggperha;
                    $showpmtavariperrb = $pmtainmavariperrb;
                    $showpmtavariperkg = $pmtainmavariperkg;

                    $showpmsdaktuperrb = $pmsdinmaaktuperrb;
                    $showpmsdaktuperkg = $pmsdinmaaktuperkg;
                    $showpmsdaktuperha = $pmsdinmaaktuperha;
                    $showpmsdanggperrb = $pmsdinmaanggperrb;
                    $showpmsdanggperkg = $pmsdinmaanggperkg;
                    $showpmsdanggperha = $pmsdinmaanggperha;
                    $showpmsdvariperrb = $pmsdinmavariperrb;
                    $showpmsdvariperkg = $pmsdinmavariperkg;

                    $showpmthanggperrb = $pmthinmaanggperrb;
                    $showpmthanggperkg = $pmthinmaanggperkg;
                    $showpmthanggperha = $pmthinmaanggperha;
                }
                $tab.="
            <td align=right " . $bg . ">" . number_format($pmtaundeaktuperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($pmsdundeaktuperrb) . "</td>
                ";
                $tab.="
            <td align=right " . $bg . ">" . number_format($showpmtaaktuperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmtaaktuperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmtaaktuperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmtaanggperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmtaanggperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmtaanggperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmtavariperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmtavariperkg) . "</td>

            <td align=right " . $bg . ">" . number_format($showpmsdaktuperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmsdaktuperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmsdaktuperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmsdanggperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmsdanggperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmsdanggperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmsdvariperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmsdvariperkg) . "</td>

            <td align=right " . $bg . ">" . number_format($showpmthanggperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmthanggperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpmthanggperha) . "</td>
                ";
                $tab.="</tr>";
            }
            if ($akun == '62103') {
                // Biaya Pemupukan =================================================
                $tab.="<tr class=rowtitle>
            <td align=left colspan=3 " . $bg . ">Pemupukan TM</td>";
                $pktal = 0;
                // inti bulan ini per
                @$pktaintiaktuperkg = $pktaintiaktu / $prodintiaktu;
                @$pktaintiaktuperha = $pktaintiaktu / $luasintiaktu;
                @$pktaintianggperkg = $pktaintiangg / $prodintiangg;
                @$pktaintianggperha = $pktaintiangg / $luasintiangg;
                @$pktaintivariperkg = $pktaintivari / $prodintivari;
                // inti sd bulan ini per
                @$pksdintiaktuperkg = $pksdintiaktu / $prsdintiaktu;
                @$pksdintiaktuperha = $pksdintiaktu / $luasintiaktu;
                @$pksdintianggperkg = $pksdintiangg / $prsdintiangg;
                @$pksdintianggperha = $pksdintiangg / $luasintiangg;
                @$pksdintivariperkg = $pksdintivari / $prsdintivari;
                // inti setahun per
                @$pkthintianggperkg = $pkthintiangg / $prthintiangg;
                @$pkthintianggperha = $pkthintiangg / $luasintiangg;

                // plasma bulan ini per
                @$pktaplasaktuperkg = $pktaplasaktu / $prodplasaktu;
                @$pktaplasaktuperha = $pktaplasaktu / $luasplasaktu;
                @$pktaplasanggperkg = $pktaplasangg / $prodplasangg;
                @$pktaplasanggperha = $pktaplasangg / $luasplasangg;
                @$pktaplasvariperkg = $pktaplasvari / $prodplasvari;
                // plasma sd bulan ini per
                @$pksdplasaktuperkg = $pksdplasaktu / $prsdplasaktu;
                @$pksdplasaktuperha = $pksdplasaktu / $luasplasaktu;
                @$pksdplasanggperkg = $pksdplasangg / $prsdplasangg;
                @$pksdplasanggperha = $pksdplasangg / $luasplasangg;
                @$pksdplasvariperkg = $pksdplasvari / $prsdplasvari;
                // plasma setahun per
                @$pkthplasanggperkg = $pkthplasangg / $prthplasangg;
                @$pkthplasanggperha = $pkthplasangg / $luasplasangg;

                // inti plasma bulan ini per
                @$pktainmaaktuperkg = $pktainmaaktu / $prodinmaaktu;
                @$pktainmaaktuperha = $pktainmaaktu / $luasinmaaktu;
                @$pktainmaanggperkg = $pktainmaangg / $prodinmaangg;
                @$pktainmaanggperha = $pktainmaangg / $luasinmaangg;
                @$pktainmavariperkg = $pktainmavari / $prodinmavari;
                // inti plasma sd bulan ini per
                @$pksdinmaaktuperkg = $pksdinmaaktu / $prsdinmaaktu;
                @$pksdinmaaktuperha = $pksdinmaaktu / $luasinmaaktu;
                @$pksdinmaanggperkg = $pksdinmaangg / $prsdinmaangg;
                @$pksdinmaanggperha = $pksdinmaangg / $luasinmaangg;
                @$pksdinmavariperkg = $pksdinmavari / $prsdinmavari;
                // inti plasma setahun per
                @$pkthinmaanggperkg = $pkthinmaangg / $prthinmaangg;
                @$pkthinmaanggperha = $pkthinmaangg / $luasinmaangg;

                @$pktaintiaktuperrb = $pktaintiaktu / 1000;
                @$pktaintianggperrb = $pktaintiangg / 1000;
                @$pktaintivariperrb = $pktaintivari / 1000;
                @$pksdintiaktuperrb = $pksdintiaktu / 1000;
                @$pksdintianggperrb = $pksdintiangg / 1000;
                @$pksdintivariperrb = $pksdintivari / 1000;
                @$pkthintianggperrb = $pkthintiangg / 1000;

                @$pktaplasaktuperrb = $pktaplasaktu / 1000;
                @$pktaplasanggperrb = $pktaplasangg / 1000;
                @$pktaplasvariperrb = $pktaplasvari / 1000;
                @$pksdplasaktuperrb = $pksdplasaktu / 1000;
                @$pksdplasanggperrb = $pksdplasangg / 1000;
                @$pksdplasvariperrb = $pksdplasvari / 1000;
                @$pkthplasanggperrb = $pkthplasangg / 1000;

                @$pktaundeaktuperrb = $pktaundeaktu / 1000;
                @$pksdundeaktuperrb = $pksdundeaktu / 1000;

                @$pktainmaaktuperrb = $pktainmaaktu / 1000;
                @$pktainmaanggperrb = $pktainmaangg / 1000;
                @$pktainmavariperrb = $pktainmavari / 1000;
                @$pksdinmaaktuperrb = $pksdinmaaktu / 1000;
                @$pksdinmaanggperrb = $pksdinmaangg / 1000;
                @$pksdinmavariperrb = $pksdinmavari / 1000;
                @$pkthinmaanggperrb = $pkthinmaangg / 1000;


                if ($inti == 'inti') {
                    $showpktaaktuperrb = $pktaintiaktuperrb;
                    $showpktaaktuperkg = $pktaintiaktuperkg;
                    $showpktaaktuperha = $pktaintiaktuperha;
                    $showpktaanggperrb = $pktaintianggperrb;
                    $showpktaanggperkg = $pktaintianggperkg;
                    $showpktaanggperha = $pktaintianggperha;
                    $showpktavariperrb = $pktaintivariperrb;
                    $showpktavariperkg = $pktaintivariperkg;

                    $showpksdaktuperrb = $pksdintiaktuperrb;
                    $showpksdaktuperkg = $pksdintiaktuperkg;
                    $showpksdaktuperha = $pksdintiaktuperha;
                    $showpksdanggperrb = $pksdintianggperrb;
                    $showpksdanggperkg = $pksdintianggperkg;
                    $showpksdanggperha = $pksdintianggperha;
                    $showpksdvariperrb = $pksdintivariperrb;
                    $showpksdvariperkg = $pksdintivariperkg;

                    $showpkthanggperrb = $pkthintianggperrb;
                    $showpkthanggperkg = $pkthintianggperkg;
                    $showpkthanggperha = $pkthintianggperha;
                } else if ($inti == 'plasma') {
                    $showpktaaktuperrb = $pktaplasaktuperrb;
                    $showpktaaktuperkg = $pktaplasaktuperkg;
                    $showpktaaktuperha = $pktaplasaktuperha;
                    $showpktaanggperrb = $pktaplasanggperrb;
                    $showpktaanggperkg = $pktaplasanggperkg;
                    $showpktaanggperha = $pktaplasanggperha;
                    $showpktavariperrb = $pktaplasvariperrb;
                    $showpktavariperkg = $pktaplasvariperkg;

                    $showpksdaktuperrb = $pksdplasaktuperrb;
                    $showpksdaktuperkg = $pksdplasaktuperkg;
                    $showpksdaktuperha = $pksdplasaktuperha;
                    $showpksdanggperrb = $pksdplasanggperrb;
                    $showpksdanggperkg = $pksdplasanggperkg;
                    $showpksdanggperha = $pksdplasanggperha;
                    $showpksdvariperrb = $pksdplasvariperrb;
                    $showpksdvariperkg = $pksdplasvariperkg;

                    $showpkthanggperrb = $pkthplasanggperrb;
                    $showpkthanggperkg = $pkthplasanggperkg;
                    $showpkthanggperha = $pkthplasanggperha;
                } else {
                    $showpktaaktuperrb = $pktainmaaktuperrb;
                    $showpktaaktuperkg = $pktainmaaktuperkg;
                    $showpktaaktuperha = $pktainmaaktuperha;
                    $showpktaanggperrb = $pktainmaanggperrb;
                    $showpktaanggperkg = $pktainmaanggperkg;
                    $showpktaanggperha = $pktainmaanggperha;
                    $showpktavariperrb = $pktainmavariperrb;
                    $showpktavariperkg = $pktainmavariperkg;

                    $showpksdaktuperrb = $pksdinmaaktuperrb;
                    $showpksdaktuperkg = $pksdinmaaktuperkg;
                    $showpksdaktuperha = $pksdinmaaktuperha;
                    $showpksdanggperrb = $pksdinmaanggperrb;
                    $showpksdanggperkg = $pksdinmaanggperkg;
                    $showpksdanggperha = $pksdinmaanggperha;
                    $showpksdvariperrb = $pksdinmavariperrb;
                    $showpksdvariperkg = $pksdinmavariperkg;

                    $showpkthanggperrb = $pkthinmaanggperrb;
                    $showpkthanggperkg = $pkthinmaanggperkg;
                    $showpkthanggperha = $pkthinmaanggperha;
                }
                $tab.="
            <td align=right " . $bg . ">" . number_format($pktaundeaktuperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($pksdundeaktuperrb) . "</td>
                ";
                $tab.="
            <td align=right " . $bg . ">" . number_format($showpktaaktuperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpktaaktuperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpktaaktuperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpktaanggperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpktaanggperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpktaanggperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpktavariperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpktavariperkg) . "</td>

            <td align=right " . $bg . ">" . number_format($showpksdaktuperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpksdaktuperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpksdaktuperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpksdanggperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpksdanggperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpksdanggperha) . "</td>
            <td align=right " . $bg . ">" . number_format($showpksdvariperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpksdvariperkg) . "</td>

            <td align=right " . $bg . ">" . number_format($showpkthanggperrb) . "</td>
            <td align=right " . $bg . ">" . number_format($showpkthanggperkg) . "</td>
            <td align=right " . $bg . ">" . number_format($showpkthanggperha) . "</td>
                ";
                $tab.="</tr>";
            }
            $tab.="<tr class=rowcontent>
        <td align=right colspan=2>" . $akun . "XX</td>
        <td align=left colspan=1>" . $namaakun[$akun] . "</td>";
            $total = 0;
            // inti bulan ini per
            @$dataintiaktuperkg = $dataintiaktu[$akun] / $prodintiaktu;
            @$dataintiaktuperha = $dataintiaktu[$akun] / $luasintiaktu;
            @$dataintianggperkg = $dataintiangg[$akun] / $prodintiangg;
            @$dataintianggperha = $dataintiangg[$akun] / $luasintiangg;
            @$dataintivariperkg = $dataintivari[$akun] / $prodintivari;
            // inti sd bulan ini per
            @$dasdintiaktuperkg = $dasdintiaktu[$akun] / $prsdintiaktu;
            @$dasdintiaktuperha = $dasdintiaktu[$akun] / $luasintiaktu;
            @$dasdintianggperkg = $dasdintiangg[$akun] / $prsdintiangg;
            @$dasdintianggperha = $dasdintiangg[$akun] / $luasintiangg;
            @$dasdintivariperkg = $dasdintivari[$akun] / $prsdintivari;
            // inti setahun per
            @$dathintianggperkg = $dathintiangg[$akun] / $prthintiangg;
            @$dathintianggperha = $dathintiangg[$akun] / $luasintiangg;

            // plasma bulan ini per
            @$dataplasaktuperkg = $dataplasaktu[$akun] / $prodplasaktu;
            @$dataplasaktuperha = $dataplasaktu[$akun] / $luasplasaktu;
            @$dataplasanggperkg = $dataplasangg[$akun] / $prodplasangg;
            @$dataplasanggperha = $dataplasangg[$akun] / $luasplasangg;
            @$dataplasvariperkg = $dataplasvari[$akun] / $prodplasvari;
            // plasma sd bulan ini per
            @$dasdplasaktuperkg = $dasdplasaktu[$akun] / $prsdplasaktu;
            @$dasdplasaktuperha = $dasdplasaktu[$akun] / $luasplasaktu;
            @$dasdplasanggperkg = $dasdplasangg[$akun] / $prsdplasangg;
            @$dasdplasanggperha = $dasdplasangg[$akun] / $luasplasangg;
            @$dasdplasvariperkg = $dasdplasvari[$akun] / $prsdplasvari;
            // plasma setahun per
            @$dathplasanggperkg = $dathplasangg[$akun] / $prthplasangg;
            @$dathplasanggperha = $dathplasangg[$akun] / $luasplasangg;

            // inti plasma bulan ini per
            @$datainmaaktuperkg = $datainmaaktu[$akun] / $prodinmaaktu;
            @$datainmaaktuperha = $datainmaaktu[$akun] / $luasinmaaktu;
            @$datainmaanggperkg = $datainmaangg[$akun] / $prodinmaangg;
            @$datainmaanggperha = $datainmaangg[$akun] / $luasinmaangg;
            @$datainmavariperkg = $datainmavari[$akun] / $prodinmavari;
            // inti plasma sd bulan ini per
            @$dasdinmaaktuperkg = $dasdinmaaktu[$akun] / $prsdinmaaktu;
            @$dasdinmaaktuperha = $dasdinmaaktu[$akun] / $luasinmaaktu;
            @$dasdinmaanggperkg = $dasdinmaangg[$akun] / $prsdinmaangg;
            @$dasdinmaanggperha = $dasdinmaangg[$akun] / $luasinmaangg;
            @$dasdinmavariperkg = $dasdinmavari[$akun] / $prsdinmavari;
            // inti plasma setahun per
            @$dathinmaanggperkg = $dathinmaangg[$akun] / $prthinmaangg;
            @$dathinmaanggperha = $dathinmaangg[$akun] / $luasinmaangg;

            @$dataintiaktuperrb = $dataintiaktu[$akun] / 1000;
            @$dataintianggperrb = $dataintiangg[$akun] / 1000;
            @$dataintivariperrb = $dataintivari[$akun] / 1000;
            @$dasdintiaktuperrb = $dasdintiaktu[$akun] / 1000;
            @$dasdintianggperrb = $dasdintiangg[$akun] / 1000;
            @$dasdintivariperrb = $dasdintivari[$akun] / 1000;
            @$dathintianggperrb = $dathintiangg[$akun] / 1000;

            @$dataplasaktuperrb = $dataplasaktu[$akun] / 1000;
            @$dataplasanggperrb = $dataplasangg[$akun] / 1000;
            @$dataplasvariperrb = $dataplasvari[$akun] / 1000;
            @$dasdplasaktuperrb = $dasdplasaktu[$akun] / 1000;
            @$dasdplasanggperrb = $dasdplasangg[$akun] / 1000;
            @$dasdplasvariperrb = $dasdplasvari[$akun] / 1000;
            @$dathplasanggperrb = $dathplasangg[$akun] / 1000;

            @$dataundeaktuperrb = $dataundeaktu[$akun] / 1000;
            @$dasdundeaktuperrb = $dasdundeaktu[$akun] / 1000;

            @$datainmaaktuperrb = $datainmaaktu[$akun] / 1000;
            @$datainmaanggperrb = $datainmaangg[$akun] / 1000;
            @$datainmavariperrb = $datainmavari[$akun] / 1000;
            @$dasdinmaaktuperrb = $dasdinmaaktu[$akun] / 1000;
            @$dasdinmaanggperrb = $dasdinmaangg[$akun] / 1000;
            @$dasdinmavariperrb = $dasdinmavari[$akun] / 1000;
            @$dathinmaanggperrb = $dathinmaangg[$akun] / 1000;



            if ($inti == 'inti') {
                $showdataaktuperrb = $dataintiaktuperrb;
                $showdataaktuperkg = $dataintiaktuperkg;
                $showdataaktuperha = $dataintiaktuperha;
                $showdataanggperrb = $dataintianggperrb;
                $showdataanggperkg = $dataintianggperkg;
                $showdataanggperha = $dataintianggperha;
                $showdatavariperrb = $dataintivariperrb;
                $showdatavariperkg = $dataintivariperkg;

                $showdasdaktuperrb = $dasdintiaktuperrb;
                $showdasdaktuperkg = $dasdintiaktuperkg;
                $showdasdaktuperha = $dasdintiaktuperha;
                $showdasdanggperrb = $dasdintianggperrb;
                $showdasdanggperkg = $dasdintianggperkg;
                $showdasdanggperha = $dasdintianggperha;
                $showdasdvariperrb = $dasdintivariperrb;
                $showdasdvariperkg = $dasdintivariperkg;

                $showdathanggperrb = $dathintianggperrb;
                $showdathanggperkg = $dathintianggperkg;
                $showdathanggperha = $dathintianggperha;
            } else if ($inti == 'plasma') {
                $showdataaktuperrb = $dataplasaktuperrb;
                $showdataaktuperkg = $dataplasaktuperkg;
                $showdataaktuperha = $dataplasaktuperha;
                $showdataanggperrb = $dataplasanggperrb;
                $showdataanggperkg = $dataplasanggperkg;
                $showdataanggperha = $dataplasanggperha;
                $showdatavariperrb = $dataplasvariperrb;
                $showdatavariperkg = $dataplasvariperkg;

                $showdasdaktuperrb = $dasdplasaktuperrb;
                $showdasdaktuperkg = $dasdplasaktuperkg;
                $showdasdaktuperha = $dasdplasaktuperha;
                $showdasdanggperrb = $dasdplasanggperrb;
                $showdasdanggperkg = $dasdplasanggperkg;
                $showdasdanggperha = $dasdplasanggperha;
                $showdasdvariperrb = $dasdplasvariperrb;
                $showdasdvariperkg = $dasdplasvariperkg;

                $showdathanggperrb = $dathplasanggperrb;
                $showdathanggperkg = $dathplasanggperkg;
                $showdathanggperha = $dathplasanggperha;
            } else {
                $showdataaktuperrb = $datainmaaktuperrb;
                $showdataaktuperkg = $datainmaaktuperkg;
                $showdataaktuperha = $datainmaaktuperha;
                $showdataanggperrb = $datainmaanggperrb;
                $showdataanggperkg = $datainmaanggperkg;
                $showdataanggperha = $datainmaanggperha;
                $showdatavariperrb = $datainmavariperrb;
                $showdatavariperkg = $datainmavariperkg;

                $showdasdaktuperrb = $dasdinmaaktuperrb;
                $showdasdaktuperkg = $dasdinmaaktuperkg;
                $showdasdaktuperha = $dasdinmaaktuperha;
                $showdasdanggperrb = $dasdinmaanggperrb;
                $showdasdanggperkg = $dasdinmaanggperkg;
                $showdasdanggperha = $dasdinmaanggperha;
                $showdasdvariperrb = $dasdinmavariperrb;
                $showdasdvariperkg = $dasdinmavariperkg;

                $showdathanggperrb = $dathinmaanggperrb;
                $showdathanggperkg = $dathinmaanggperkg;
                $showdathanggperha = $dathinmaanggperha;
            }
            $tab.="
        <td align=right>" . number_format($dataundeaktuperrb) . "</td>
        <td align=right>" . number_format($dasdundeaktuperrb) . "</td>
            ";
            $tab.="
        <td align=right>" . number_format($showdataaktuperrb) . "</td>
        <td align=right>" . number_format($showdataaktuperkg) . "</td>
        <td align=right>" . number_format($showdataaktuperha) . "</td>
        <td align=right>" . number_format($showdataanggperrb) . "</td>
        <td align=right>" . number_format($showdataanggperkg) . "</td>
        <td align=right>" . number_format($showdataanggperha) . "</td>
        <td align=right>" . number_format($showdatavariperrb) . "</td>
        <td align=right>" . number_format($showdatavariperkg) . "</td>
            
        <td align=right>" . number_format($showdasdaktuperrb) . "</td>
        <td align=right>" . number_format($showdasdaktuperkg) . "</td>
        <td align=right>" . number_format($showdasdaktuperha) . "</td>
        <td align=right>" . number_format($showdasdanggperrb) . "</td>
        <td align=right>" . number_format($showdasdanggperkg) . "</td>
        <td align=right>" . number_format($showdasdanggperha) . "</td>
        <td align=right>" . number_format($showdasdvariperrb) . "</td>
        <td align=right>" . number_format($showdasdvariperkg) . "</td>
            
        <td align=right>" . number_format($showdathanggperrb) . "</td>
        <td align=right>" . number_format($showdathanggperkg) . "</td>
        <td align=right>" . number_format($showdathanggperha) . "</td>
            ";
            $tab.="</tr>";

 
        } // for each $akuntm

        
// Biaya Langsung Total ====================================================
    $tab.="<tr class=rowtitle>
    <td align=left width=" . $lebkolkiri . " colspan=3 " . $bg . ">Biaya Langsung</td>";
    $total = 0;
    // inti bulan ini per
    @$totaintiaktuperkg = $totaintiaktu / $prodintiaktu;
    @$totaintiaktuperha = $totaintiaktu / $luasintiaktu;
    @$totaintianggperkg = $totaintiangg / $prodintiangg;
    @$totaintianggperha = $totaintiangg / $luasintiangg;
    @$totaintivariperkg = $totaintivari / $prodintivari;
    // inti sd bulan ini per
    @$tosdintiaktuperkg = $tosdintiaktu / $prsdintiaktu;
    @$tosdintiaktuperha = $tosdintiaktu / $luasintiaktu;
    @$tosdintianggperkg = $tosdintiangg / $prsdintiangg;
    @$tosdintianggperha = $tosdintiangg / $luasintiangg;
    @$tosdintivariperkg = $tosdintivari / $prsdintivari;
    // inti setahun per
    @$tothintianggperkg = $tothintiangg / $prthintiangg;
    @$tothintianggperha = $tothintiangg / $luasintiangg;

    // plasma bulan ini per
    @$totaplasaktuperkg = $totaplasaktu / $prodplasaktu;
    @$totaplasaktuperha = $totaplasaktu / $luasplasaktu;
    @$totaplasanggperkg = $totaplasangg / $prodplasangg;
    @$totaplasanggperha = $totaplasangg / $luasplasangg;
    @$totaplasvariperkg = $totaplasvari / $prodplasvari;
    // plasma sd bulan ini per
    @$tosdplasaktuperkg = $tosdplasaktu / $prsdplasaktu;
    @$tosdplasaktuperha = $tosdplasaktu / $luasplasaktu;
    @$tosdplasanggperkg = $tosdplasangg / $prsdplasangg;
    @$tosdplasanggperha = $tosdplasangg / $luasplasangg;
    @$tosdplasvariperkg = $tosdplasvari / $prsdplasvari;
    // plasma setahun per
    @$tothplasanggperkg = $tothplasangg / $prthplasangg;
    @$tothplasanggperha = $tothplasangg / $luasplasangg;

    // inti plasma bulan ini per
    @$totainmaaktuperkg = $totainmaaktu / $prodinmaaktu;
    @$totainmaaktuperha = $totainmaaktu / $luasinmaaktu;
    @$totainmaanggperkg = $totainmaangg / $prodinmaangg;
    @$totainmaanggperha = $totainmaangg / $luasinmaangg;
    @$totainmavariperkg = $totainmavari / $prodinmavari;
    // inti plasma sd bulan ini per
    @$tosdinmaaktuperkg = $tosdinmaaktu / $prsdinmaaktu;
    @$tosdinmaaktuperha = $tosdinmaaktu / $luasinmaaktu;
    @$tosdinmaanggperkg = $tosdinmaangg / $prsdinmaangg;
    @$tosdinmaanggperha = $tosdinmaangg / $luasinmaangg;
    @$tosdinmavariperkg = $tosdinmavari / $prsdinmavari;
    // inti plasma setahun per
    @$tothinmaanggperkg = $tothinmaangg / $prthinmaangg;
    @$tothinmaanggperha = $tothinmaangg / $luasinmaangg;

    @$totaintiaktuperrb = $totaintiaktu / 1000;
    @$totaintianggperrb = $totaintiangg / 1000;
    @$totaintivariperrb = $totaintivari / 1000;
    @$tosdintiaktuperrb = $tosdintiaktu / 1000;
    @$tosdintianggperrb = $tosdintiangg / 1000;
    @$tosdintivariperrb = $tosdintivari / 1000;
    @$tothintianggperrb = $tothintiangg / 1000;

    @$totaplasaktuperrb = $totaplasaktu / 1000;
    @$totaplasanggperrb = $totaplasangg / 1000;
    @$totaplasvariperrb = $totaplasvari / 1000;
    @$tosdplasaktuperrb = $tosdplasaktu / 1000;
    @$tosdplasanggperrb = $tosdplasangg / 1000;
    @$tosdplasvariperrb = $tosdplasvari / 1000;
    @$tothplasanggperrb = $tothplasangg / 1000;

    @$totaundeaktuperrb = $totaundeaktu / 1000;
    @$tosdundeaktuperrb = $tosdundeaktu / 1000;

    @$totainmaaktuperrb = $totainmaaktu / 1000;
    @$totainmaanggperrb = $totainmaangg / 1000;
    @$totainmavariperrb = $totainmavari / 1000;
    @$tosdinmaaktuperrb = $tosdinmaaktu / 1000;
    @$tosdinmaanggperrb = $tosdinmaangg / 1000;
    @$tosdinmavariperrb = $tosdinmavari / 1000;
    @$tothinmaanggperrb = $tothinmaangg / 1000;



    if ($inti == 'inti') {
        $showtotaaktuperrb = $totaintiaktuperrb;
        $showtotaaktuperkg = $totaintiaktuperkg;
        $showtotaaktuperha = $totaintiaktuperha;
        $showtotaanggperrb = $totaintianggperrb;
        $showtotaanggperkg = $totaintianggperkg;
        $showtotaanggperha = $totaintianggperha;
        $showtotavariperrb = $totaintivariperrb;
        $showtotavariperkg = $totaintivariperkg;

        $showtosdaktuperrb = $tosdintiaktuperrb;
        $showtosdaktuperkg = $tosdintiaktuperkg;
        $showtosdaktuperha = $tosdintiaktuperha;
        $showtosdanggperrb = $tosdintianggperrb;
        $showtosdanggperkg = $tosdintianggperkg;
        $showtosdanggperha = $tosdintianggperha;
        $showtosdvariperrb = $tosdintivariperrb;
        $showtosdvariperkg = $tosdintivariperkg;

        $showtothanggperrb = $tothintianggperrb;
        $showtothanggperkg = $tothintianggperkg;
        $showtothanggperha = $tothintianggperha;
    } else if ($inti == 'plasma') {
        $showtotaaktuperrb = $totaplasaktuperrb;
        $showtotaaktuperkg = $totaplasaktuperkg;
        $showtotaaktuperha = $totaplasaktuperha;
        $showtotaanggperrb = $totaplasanggperrb;
        $showtotaanggperkg = $totaplasanggperkg;
        $showtotaanggperha = $totaplasanggperha;
        $showtotavariperrb = $totaplasvariperrb;
        $showtotavariperkg = $totaplasvariperkg;

        $showtosdaktuperrb = $tosdplasaktuperrb;
        $showtosdaktuperkg = $tosdplasaktuperkg;
        $showtosdaktuperha = $tosdplasaktuperha;
        $showtosdanggperrb = $tosdplasanggperrb;
        $showtosdanggperkg = $tosdplasanggperkg;
        $showtosdanggperha = $tosdplasanggperha;
        $showtosdvariperrb = $tosdplasvariperrb;
        $showtosdvariperkg = $tosdplasvariperkg;

        $showtothanggperrb = $tothplasanggperrb;
        $showtothanggperkg = $tothplasanggperkg;
        $showtothanggperha = $tothplasanggperha;
    } else {
        $showtotaaktuperrb = $totainmaaktuperrb;
        $showtotaaktuperkg = $totainmaaktuperkg;
        $showtotaaktuperha = $totainmaaktuperha;
        $showtotaanggperrb = $totainmaanggperrb;
        $showtotaanggperkg = $totainmaanggperkg;
        $showtotaanggperha = $totainmaanggperha;
        $showtotavariperrb = $totainmavariperrb;
        $showtotavariperkg = $totainmavariperkg;

        $showtosdaktuperrb = $tosdinmaaktuperrb;
        $showtosdaktuperkg = $tosdinmaaktuperkg;
        $showtosdaktuperha = $tosdinmaaktuperha;
        $showtosdanggperrb = $tosdinmaanggperrb;
        $showtosdanggperkg = $tosdinmaanggperkg;
        $showtosdanggperha = $tosdinmaanggperha;
        $showtosdvariperrb = $tosdinmavariperrb;
        $showtosdvariperkg = $tosdinmavariperkg;

        $showtothanggperrb = $tothinmaanggperrb;
        $showtothanggperkg = $tothinmaanggperkg;
        $showtothanggperha = $tothinmaanggperha;
    }
    $tab.="
    <td align=right " . $bg . ">" . number_format($totaundeaktuperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($tosdundeaktuperrb) . "</td>
        ";
    $tab.="
    <td align=right " . $bg . ">" . number_format($showtotaaktuperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtotaaktuperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtotaaktuperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showtotaanggperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtotaanggperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtotaanggperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showtotavariperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtotavariperkg) . "</td>

    <td align=right " . $bg . ">" . number_format($showtosdaktuperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtosdaktuperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtosdaktuperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showtosdanggperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtosdanggperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtosdanggperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showtosdvariperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtosdvariperkg) . "</td>

    <td align=right " . $bg . ">" . number_format($showtothanggperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtothanggperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtothanggperha) . "</td>
        ";
    $tab.="</tr>";

    // bawah = 711-716
    if (!empty($bawah))
        foreach ($bawah as $nomor) {

            // Biaya Umum ==========================================================
            if ($nomor == 0) {
                $bgarr = $bg;
                $tab.="<tr class=rowtitle>
            <td align=left colspan=3 " . $bgarr . ">" . $namabawah[$nomor] . "</td>";
            } else {
                $bgarr = '';
                $tab.="<tr class=rowcontent>
            <td align=left colspan=2 " . $bgarr . ">" . $nomorbawah[$nomor] . "</td>
                <td align=left>" . $namabawah[$nomor] . "</td>";
            }

            $tjtal = 0;
            // inti bulan ini per
            @$d7taintiaktuperkg[$nomor] = $d7taintiaktu[$nomor] / $prodintiaktu;
            @$d7taintiaktuperha[$nomor] = $d7taintiaktu[$nomor] / $luasintiaktu;
            @$d7taintianggperkg[$nomor] = $d7taintiangg[$nomor] / $prodintiangg;
            @$d7taintianggperha[$nomor] = $d7taintiangg[$nomor] / $luasintiangg;
            @$d7taintivariperkg[$nomor] = $d7taintivari[$nomor] / $prodintivari;
            // inti sd bulan ini per
            @$d7sdintiaktuperkg[$nomor] = $d7sdintiaktu[$nomor] / $prsdintiaktu;
            @$d7sdintiaktuperha[$nomor] = $d7sdintiaktu[$nomor] / $luasintiaktu;
            @$d7sdintianggperkg[$nomor] = $d7sdintiangg[$nomor] / $prsdintiangg;
            @$d7sdintianggperha[$nomor] = $d7sdintiangg[$nomor] / $luasintiangg;
            @$d7sdintivariperkg[$nomor] = $d7sdintivari[$nomor] / $prsdintivari;
            // inti setahun per
            @$d7thintianggperkg[$nomor] = $d7thintiangg[$nomor] / $prthintiangg;
            @$d7thintianggperha[$nomor] = $d7thintiangg[$nomor] / $luasintiangg;

            // plasma bulan ini per
            @$d7taplasaktuperkg[$nomor] = $d7taplasaktu[$nomor] / $prodplasaktu;
            @$d7taplasaktuperha[$nomor] = $d7taplasaktu[$nomor] / $luasplasaktu;
            @$d7taplasanggperkg[$nomor] = $d7taplasangg[$nomor] / $prodplasangg;
            @$d7taplasanggperha[$nomor] = $d7taplasangg[$nomor] / $luasplasangg;
            @$d7taplasvariperkg[$nomor] = $d7taplasvari[$nomor] / $prodplasvari;
            // plasma sd bulan ini per
            @$d7sdplasaktuperkg[$nomor] = $d7sdplasaktu[$nomor] / $prsdplasaktu;
            @$d7sdplasaktuperha[$nomor] = $d7sdplasaktu[$nomor] / $luasplasaktu;
            @$d7sdplasanggperkg[$nomor] = $d7sdplasangg[$nomor] / $prsdplasangg;
            @$d7sdplasanggperha[$nomor] = $d7sdplasangg[$nomor] / $luasplasangg;
            @$d7sdplasvariperkg[$nomor] = $d7sdplasvari[$nomor] / $prsdplasvari;
            // plasma setahun per
            @$d7thplasanggperkg[$nomor] = $d7thplasangg[$nomor] / $prthplasangg;
            @$d7thplasanggperha[$nomor] = $d7thplasangg[$nomor] / $luasplasangg;

            // inti plasma bulan ini per
            @$d7tainmaaktuperkg[$nomor] = $d7tainmaaktu[$nomor] / $prodinmaaktu;
            @$d7tainmaaktuperha[$nomor] = $d7tainmaaktu[$nomor] / $luasinmaaktu;
            @$d7tainmaanggperkg[$nomor] = $d7tainmaangg[$nomor] / $prodinmaangg;
            @$d7tainmaanggperha[$nomor] = $d7tainmaangg[$nomor] / $luasinmaangg;
            @$d7tainmavariperkg[$nomor] = $d7tainmavari[$nomor] / $prodinmavari;
            // inti plasma sd bulan ini per
            @$d7sdinmaaktuperkg[$nomor] = $d7sdinmaaktu[$nomor] / $prsdinmaaktu;
            @$d7sdinmaaktuperha[$nomor] = $d7sdinmaaktu[$nomor] / $luasinmaaktu;
            @$d7sdinmaanggperkg[$nomor] = $d7sdinmaangg[$nomor] / $prsdinmaangg;
            @$d7sdinmaanggperha[$nomor] = $d7sdinmaangg[$nomor] / $luasinmaangg;
            @$d7sdinmavariperkg[$nomor] = $d7sdinmavari[$nomor] / $prsdinmavari;
            // inti plasma setahun per
            @$d7thinmaanggperkg[$nomor] = $d7thinmaangg[$nomor] / $prthinmaangg;
            @$d7thinmaanggperha[$nomor] = $d7thinmaangg[$nomor] / $luasinmaangg;

            @$d7taintiaktuperrb[$nomor] = $d7taintiaktu[$nomor] / 1000;
            @$d7taintianggperrb[$nomor] = $d7taintiangg[$nomor] / 1000;
            @$d7taintivariperrb[$nomor] = $d7taintivari[$nomor] / 1000;
            @$d7sdintiaktuperrb[$nomor] = $d7sdintiaktu[$nomor] / 1000;
            @$d7sdintianggperrb[$nomor] = $d7sdintiangg[$nomor] / 1000;
            @$d7sdintivariperrb[$nomor] = $d7sdintivari[$nomor] / 1000;
            @$d7thintianggperrb[$nomor] = $d7thintiangg[$nomor] / 1000;

            @$d7taplasaktuperrb[$nomor] = $d7taplasaktu[$nomor] / 1000;
            @$d7taplasanggperrb[$nomor] = $d7taplasangg[$nomor] / 1000;
            @$d7taplasvariperrb[$nomor] = $d7taplasvari[$nomor] / 1000;
            @$d7sdplasaktuperrb[$nomor] = $d7sdplasaktu[$nomor] / 1000;
            @$d7sdplasanggperrb[$nomor] = $d7sdplasangg[$nomor] / 1000;
            @$d7sdplasvariperrb[$nomor] = $d7sdplasvari[$nomor] / 1000;
            @$d7thplasanggperrb[$nomor] = $d7thplasangg[$nomor] / 1000;

            @$d7taundeaktuperrb[$nomor] = $d7taundeaktu[$nomor] / 1000;
            @$d7sdundeaktuperrb[$nomor] = $d7sdundeaktu[$nomor] / 1000;

            @$d7tainmaaktuperrb[$nomor] = $d7tainmaaktu[$nomor] / 1000;
            @$d7tainmaanggperrb[$nomor] = $d7tainmaangg[$nomor] / 1000;
            @$d7tainmavariperrb[$nomor] = $d7tainmavari[$nomor] / 1000;
            @$d7sdinmaaktuperrb[$nomor] = $d7sdinmaaktu[$nomor] / 1000;
            @$d7sdinmaanggperrb[$nomor] = $d7sdinmaangg[$nomor] / 1000;
            @$d7sdinmavariperrb[$nomor] = $d7sdinmavari[$nomor] / 1000;
            @$d7thinmaanggperrb[$nomor] = $d7thinmaangg[$nomor] / 1000;


            if ($inti == 'inti') {
                $showd7taaktuperrb[$nomor] = $d7taintiaktuperrb[$nomor];
                $showd7taaktuperkg[$nomor] = $d7taintiaktuperkg[$nomor];
                $showd7taaktuperha[$nomor] = $d7taintiaktuperha[$nomor];
                $showd7taanggperrb[$nomor] = $d7taintianggperrb[$nomor];
                $showd7taanggperkg[$nomor] = $d7taintianggperkg[$nomor];
                $showd7taanggperha[$nomor] = $d7taintianggperha[$nomor];
                $showd7tavariperrb[$nomor] = $d7taintivariperrb[$nomor];
                $showd7tavariperkg[$nomor] = $d7taintivariperkg[$nomor];

                $showd7sdaktuperrb[$nomor] = $d7sdintiaktuperrb[$nomor];
                $showd7sdaktuperkg[$nomor] = $d7sdintiaktuperkg[$nomor];
                $showd7sdaktuperha[$nomor] = $d7sdintiaktuperha[$nomor];
                $showd7sdanggperrb[$nomor] = $d7sdintianggperrb[$nomor];
                $showd7sdanggperkg[$nomor] = $d7sdintianggperkg[$nomor];
                $showd7sdanggperha[$nomor] = $d7sdintianggperha[$nomor];
                $showd7sdvariperrb[$nomor] = $d7sdintivariperrb[$nomor];
                $showd7sdvariperkg[$nomor] = $d7sdintivariperkg[$nomor];

                $showd7thanggperrb[$nomor] = $d7thintianggperrb[$nomor];
                $showd7thanggperkg[$nomor] = $d7thintianggperkg[$nomor];
                $showd7thanggperha[$nomor] = $d7thintianggperha[$nomor];
            } else if ($inti == 'plasma') {
                $showd7taaktuperrb[$nomor] = $d7taplasaktuperrb[$nomor];
                $showd7taaktuperkg[$nomor] = $d7taplasaktuperkg[$nomor];
                $showd7taaktuperha[$nomor] = $d7taplasaktuperha[$nomor];
                $showd7taanggperrb[$nomor] = $d7taplasanggperrb[$nomor];
                $showd7taanggperkg[$nomor] = $d7taplasanggperkg[$nomor];
                $showd7taanggperha[$nomor] = $d7taplasanggperha[$nomor];
                $showd7tavariperrb[$nomor] = $d7taplasvariperrb[$nomor];
                $showd7tavariperkg[$nomor] = $d7taplasvariperkg[$nomor];

                $showd7sdaktuperrb[$nomor] = $d7sdplasaktuperrb[$nomor];
                $showd7sdaktuperkg[$nomor] = $d7sdplasaktuperkg[$nomor];
                $showd7sdaktuperha[$nomor] = $d7sdplasaktuperha[$nomor];
                $showd7sdanggperrb[$nomor] = $d7sdplasanggperrb[$nomor];
                $showd7sdanggperkg[$nomor] = $d7sdplasanggperkg[$nomor];
                $showd7sdanggperha[$nomor] = $d7sdplasanggperha[$nomor];
                $showd7sdvariperrb[$nomor] = $d7sdplasvariperrb[$nomor];
                $showd7sdvariperkg[$nomor] = $d7sdplasvariperkg[$nomor];

                $showd7thanggperrb[$nomor] = $d7thplasanggperrb[$nomor];
                $showd7thanggperkg[$nomor] = $d7thplasanggperkg[$nomor];
                $showd7thanggperha[$nomor] = $d7thplasanggperha[$nomor];
            } else {
                $showd7taaktuperrb[$nomor] = $d7tainmaaktuperrb[$nomor];
                $showd7taaktuperkg[$nomor] = $d7tainmaaktuperkg[$nomor];
                $showd7taaktuperha[$nomor] = $d7tainmaaktuperha[$nomor];
                $showd7taanggperrb[$nomor] = $d7tainmaanggperrb[$nomor];
                $showd7taanggperkg[$nomor] = $d7tainmaanggperkg[$nomor];
                $showd7taanggperha[$nomor] = $d7tainmaanggperha[$nomor];
                $showd7tavariperrb[$nomor] = $d7tainmavariperrb[$nomor];
                $showd7tavariperkg[$nomor] = $d7tainmavariperkg[$nomor];

                $showd7sdaktuperrb[$nomor] = $d7sdinmaaktuperrb[$nomor];
                $showd7sdaktuperkg[$nomor] = $d7sdinmaaktuperkg[$nomor];
                $showd7sdaktuperha[$nomor] = $d7sdinmaaktuperha[$nomor];
                $showd7sdanggperrb[$nomor] = $d7sdinmaanggperrb[$nomor];
                $showd7sdanggperkg[$nomor] = $d7sdinmaanggperkg[$nomor];
                $showd7sdanggperha[$nomor] = $d7sdinmaanggperha[$nomor];
                $showd7sdvariperrb[$nomor] = $d7sdinmavariperrb[$nomor];
                $showd7sdvariperkg[$nomor] = $d7sdinmavariperkg[$nomor];

                $showd7thanggperrb[$nomor] = $d7thinmaanggperrb[$nomor];
                $showd7thanggperkg[$nomor] = $d7thinmaanggperkg[$nomor];
                $showd7thanggperha[$nomor] = $d7thinmaanggperha[$nomor];
            }

            $tab.="
        <td align=right " . $bgarr . ">" . number_format($d7taundeaktuperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($d7sdundeaktuperrb[$nomor]) . "</td>
            ";
            $tab.="
        <td align=right " . $bgarr . ">" . number_format($showd7taaktuperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7taaktuperkg[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7taaktuperha[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7taanggperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7taanggperkg[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7taanggperha[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7tavariperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7tavariperkg[$nomor]) . "</td>

        <td align=right " . $bgarr . ">" . number_format($showd7sdaktuperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7sdaktuperkg[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7sdaktuperha[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7sdanggperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7sdanggperkg[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7sdanggperha[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7sdvariperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7sdvariperkg[$nomor]) . "</td>

        <td align=right " . $bgarr . ">" . number_format($showd7thanggperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7thanggperkg[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd7thanggperha[$nomor]) . "</td>
            ";
            $tab.="</tr>";
        }

    // Biaya Produksi sebelum Depresiasi dan Alokasi ===========================
    $tab.="<tr class=rowtitle>
        <td align=left colspan=3 " . $bg . ">Total Sebelum OH dan Alokasi</td>";
    // inti bulan ini per
    @$tstaintiaktuperkg = $tstaintiaktu / $prodintiaktu;
    @$tstaintiaktuperha = $tstaintiaktu / $luasintiaktu;
    @$tstaintianggperkg = $tstaintiangg / $prodintiangg;
    @$tstaintianggperha = $tstaintiangg / $luasintiangg;
    @$tstaintivariperkg = $tstaintivari / $prodintivari;
    // inti sd bulan ini per
    @$tssdintiaktuperkg = $tssdintiaktu / $prsdintiaktu;
    @$tssdintiaktuperha = $tssdintiaktu / $luasintiaktu;
    @$tssdintianggperkg = $tssdintiangg / $prsdintiangg;
    @$tssdintianggperha = $tssdintiangg / $luasintiangg;
    @$tssdintivariperkg = $tssdintivari / $prsdintivari;
    // inti setahun per
    @$tsthintianggperkg = $tsthintiangg / $prthintiangg;
    @$tsthintianggperha = $tsthintiangg / $luasintiangg;

    // plasma bulan ini per
    @$tstaplasaktuperkg = $tstaplasaktu / $prodplasaktu;
    @$tstaplasaktuperha = $tstaplasaktu / $luasplasaktu;
    @$tstaplasanggperkg = $tstaplasangg / $prodplasangg;
    @$tstaplasanggperha = $tstaplasangg / $luasplasangg;
    @$tstaplasvariperkg = $tstaplasvari / $prodplasvari;
    // plasma sd bulan ini per
    @$tssdplasaktuperkg = $tssdplasaktu / $prsdplasaktu;
    @$tssdplasaktuperha = $tssdplasaktu / $luasplasaktu;
    @$tssdplasanggperkg = $tssdplasangg / $prsdplasangg;
    @$tssdplasanggperha = $tssdplasangg / $luasplasangg;
    @$tssdplasvariperkg = $tssdplasvari / $prsdplasvari;
    // plasma setahun per
    @$tsthplasanggperkg = $tsthplasangg / $prthplasangg;
    @$tsthplasanggperha = $tsthplasangg / $luasplasangg;

    // inti plasma bulan ini per
    @$tstainmaaktuperkg = $tstainmaaktu / $prodinmaaktu;
    @$tstainmaaktuperha = $tstainmaaktu / $luasinmaaktu;
    @$tstainmaanggperkg = $tstainmaangg / $prodinmaangg;
    @$tstainmaanggperha = $tstainmaangg / $luasinmaangg;
    @$tstainmavariperkg = $tstainmavari / $prodinmavari;
    // inti plasma sd bulan ini per
    @$tssdinmaaktuperkg = $tssdinmaaktu / $prsdinmaaktu;
    @$tssdinmaaktuperha = $tssdinmaaktu / $luasinmaaktu;
    @$tssdinmaanggperkg = $tssdinmaangg / $prsdinmaangg;
    @$tssdinmaanggperha = $tssdinmaangg / $luasinmaangg;
    @$tssdinmavariperkg = $tssdinmavari / $prsdinmavari;
    // inti plasma setahun per
    @$tsthinmaanggperkg = $tsthinmaangg / $prthinmaangg;
    @$tsthinmaanggperha = $tsthinmaangg / $luasinmaangg;

    @$tstaintiaktuperrb = $tstaintiaktu / 1000;
    @$tstaintianggperrb = $tstaintiangg / 1000;
    @$tstaintivariperrb = $tstaintivari / 1000;
    @$tssdintiaktuperrb = $tssdintiaktu / 1000;
    @$tssdintianggperrb = $tssdintiangg / 1000;
    @$tssdintivariperrb = $tssdintivari / 1000;
    @$tsthintianggperrb = $tsthintiangg / 1000;

    @$tstaplasaktuperrb = $tstaplasaktu / 1000;
    @$tstaplasanggperrb = $tstaplasangg / 1000;
    @$tstaplasvariperrb = $tstaplasvari / 1000;
    @$tssdplasaktuperrb = $tssdplasaktu / 1000;
    @$tssdplasanggperrb = $tssdplasangg / 1000;
    @$tssdplasvariperrb = $tssdplasvari / 1000;
    @$tsthplasanggperrb = $tsthplasangg / 1000;

    @$tstaundeaktuperrb = $tstaundeaktu / 1000;
    @$tssdundeaktuperrb = $tssdundeaktu / 1000;

    @$tstainmaaktuperrb = $tstainmaaktu / 1000;
    @$tstainmaanggperrb = $tstainmaangg / 1000;
    @$tstainmavariperrb = $tstainmavari / 1000;
    @$tssdinmaaktuperrb = $tssdinmaaktu / 1000;
    @$tssdinmaanggperrb = $tssdinmaangg / 1000;
    @$tssdinmavariperrb = $tssdinmavari / 1000;
    @$tsthinmaanggperrb = $tsthinmaangg / 1000;



    if ($inti == 'inti') {
        $showtstaaktuperrb = $tstaintiaktuperrb;
        $showtstaaktuperkg = $tstaintiaktuperkg;
        $showtstaaktuperha = $tstaintiaktuperha;
        $showtstaanggperrb = $tstaintianggperrb;
        $showtstaanggperkg = $tstaintianggperkg;
        $showtstaanggperha = $tstaintianggperha;
        $showtstavariperrb = $tstaintivariperrb;
        $showtstavariperkg = $tstaintivariperkg;

        $showtsstsktuperrb = $tssdintiaktuperrb;
        $showtsstsktuperkg = $tssdintiaktuperkg;
        $showtsstsktuperha = $tssdintiaktuperha;
        $showtsstsnggperrb = $tssdintianggperrb;
        $showtsstsnggperkg = $tssdintianggperkg;
        $showtsstsnggperha = $tssdintianggperha;
        $showtssdvariperrb = $tssdintivariperrb;
        $showtssdvariperkg = $tssdintivariperkg;

        $showtsthanggperrb = $tsthintianggperrb;
        $showtsthanggperkg = $tsthintianggperkg;
        $showtsthanggperha = $tsthintianggperha;
    } else if ($inti == 'plasma') {
        $showtstaaktuperrb = $tstaplasaktuperrb;
        $showtstaaktuperkg = $tstaplasaktuperkg;
        $showtstaaktuperha = $tstaplasaktuperha;
        $showtstaanggperrb = $tstaplasanggperrb;
        $showtstaanggperkg = $tstaplasanggperkg;
        $showtstaanggperha = $tstaplasanggperha;
        $showtstavariperrb = $tstaplasvariperrb;
        $showtstavariperkg = $tstaplasvariperkg;

        $showtsstsktuperrb = $tssdplasaktuperrb;
        $showtsstsktuperkg = $tssdplasaktuperkg;
        $showtsstsktuperha = $tssdplasaktuperha;
        $showtsstsnggperrb = $tssdplasanggperrb;
        $showtsstsnggperkg = $tssdplasanggperkg;
        $showtsstsnggperha = $tssdplasanggperha;
        $showtssdvariperrb = $tssdplasvariperrb;
        $showtssdvariperkg = $tssdplasvariperkg;

        $showtsthanggperrb = $tsthplasanggperrb;
        $showtsthanggperkg = $tsthplasanggperkg;
        $showtsthanggperha = $tsthplasanggperha;
    } else {
        $showtstaaktuperrb = $tstainmaaktuperrb;
        $showtstaaktuperkg = $tstainmaaktuperkg;
        $showtstaaktuperha = $tstainmaaktuperha;
        $showtstaanggperrb = $tstainmaanggperrb;
        $showtstaanggperkg = $tstainmaanggperkg;
        $showtstaanggperha = $tstainmaanggperha;
        $showtstavariperrb = $tstainmavariperrb;
        $showtstavariperkg = $tstainmavariperkg;

        $showtssdaktuperrb = $tssdinmaaktuperrb;
        $showtssdaktuperkg = $tssdinmaaktuperkg;
        $showtssdaktuperha = $tssdinmaaktuperha;
        $showtssdanggperrb = $tssdinmaanggperrb;
        $showtssdanggperkg = $tssdinmaanggperkg;
        $showtssdanggperha = $tssdinmaanggperha;
        $showtssdvariperrb = $tssdinmavariperrb;
        $showtssdvariperkg = $tssdinmavariperkg;

        $showtsthanggperrb = $tsthinmaanggperrb;
        $showtsthanggperkg = $tsthinmaanggperkg;
        $showtsthanggperha = $tsthinmaanggperha;
    }

    $tab.="
    <td align=right " . $bg . ">" . number_format($tstaundeaktuperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($tssdundeaktuperrb) . "</td>
        ";
    $tab.="
    <td align=right " . $bg . ">" . number_format($showtstaaktuperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtstaaktuperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtstaaktuperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showtstaanggperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtstaanggperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtstaanggperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showtstavariperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtstavariperkg) . "</td>

    <td align=right " . $bg . ">" . number_format($showtssdaktuperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtssdaktuperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtssdaktuperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showtssdanggperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtssdanggperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtssdanggperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showtssdvariperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtssdvariperkg) . "</td>

    <td align=right " . $bg . ">" . number_format($showtsthanggperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtsthanggperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtsthanggperha) . "</td>
        ";
    $tab.="</tr>";

    // total bawah = 71502 - 71999
    if (!empty($batal))
        foreach ($batal as $nomor) {

            // Biaya Depresiasi/Penyusutan dan Alokasi =============================
            if ($nomor == 0) {
                $bgarr = $bg;
                $tab.="<tr class=rowtitle>
            <td align=left colspan=3 " . $bgarr . ">" . $namabatal[$nomor] . "</td>";
            } else {
                $bgarr = '';
                $tab.="<tr class=rowcontent>
            <td align=left colspan=2 " . $bgarr . ">" . $nomorbatal[$nomor] . "</td>
                <td align=left>" . $namabatal[$nomor] . "</td>";
            }

            $tbtal = 0;
            // inti bulan ini per
            @$d9taintiaktuperkg[$nomor] = $d9taintiaktu[$nomor] / $prodintiaktu;
            @$d9taintiaktuperha[$nomor] = $d9taintiaktu[$nomor] / $luasintiaktu;
            @$d9taintianggperkg[$nomor] = $d9taintiangg[$nomor] / $prodintiangg;
            @$d9taintianggperha[$nomor] = $d9taintiangg[$nomor] / $luasintiangg;
            @$d9taintivariperkg[$nomor] = $d9taintivari[$nomor] / $prodintivari;
            // inti sd bulan ini per
            @$d9sdintiaktuperkg[$nomor] = $d9sdintiaktu[$nomor] / $prsdintiaktu;
            @$d9sdintiaktuperha[$nomor] = $d9sdintiaktu[$nomor] / $luasintiaktu;
            @$d9sdintianggperkg[$nomor] = $d9sdintiangg[$nomor] / $prsdintiangg;
            @$d9sdintianggperha[$nomor] = $d9sdintiangg[$nomor] / $luasintiangg;
            @$d9sdintivariperkg[$nomor] = $d9sdintivari[$nomor] / $prsdintivari;
            // inti setahun per
            @$d9thintianggperkg[$nomor] = $d9thintiangg[$nomor] / $prthintiangg;
            @$d9thintianggperha[$nomor] = $d9thintiangg[$nomor] / $luasintiangg;

            // plasma bulan ini per
            @$d9taplasaktuperkg[$nomor] = $d9taplasaktu[$nomor] / $prodplasaktu;
            @$d9taplasaktuperha[$nomor] = $d9taplasaktu[$nomor] / $luasplasaktu;
            @$d9taplasanggperkg[$nomor] = $d9taplasangg[$nomor] / $prodplasangg;
            @$d9taplasanggperha[$nomor] = $d9taplasangg[$nomor] / $luasplasangg;
            @$d9taplasvariperkg[$nomor] = $d9taplasvari[$nomor] / $prodplasvari;
            // plasma sd bulan ini per
            @$d9sdplasaktuperkg[$nomor] = $d9sdplasaktu[$nomor] / $prsdplasaktu;
            @$d9sdplasaktuperha[$nomor] = $d9sdplasaktu[$nomor] / $luasplasaktu;
            @$d9sdplasanggperkg[$nomor] = $d9sdplasangg[$nomor] / $prsdplasangg;
            @$d9sdplasanggperha[$nomor] = $d9sdplasangg[$nomor] / $luasplasangg;
            @$d9sdplasvariperkg[$nomor] = $d9sdplasvari[$nomor] / $prsdplasvari;
            // plasma setahun per
            @$d9thplasanggperkg[$nomor] = $d9thplasangg[$nomor] / $prthplasangg;
            @$d9thplasanggperha[$nomor] = $d9thplasangg[$nomor] / $luasplasangg;

            // inti plasma bulan ini per
            @$d9tainmaaktuperkg[$nomor] = $d9tainmaaktu[$nomor] / $prodinmaaktu;
            @$d9tainmaaktuperha[$nomor] = $d9tainmaaktu[$nomor] / $luasinmaaktu;
            @$d9tainmaanggperkg[$nomor] = $d9tainmaangg[$nomor] / $prodinmaangg;
            @$d9tainmaanggperha[$nomor] = $d9tainmaangg[$nomor] / $luasinmaangg;
            @$d9tainmavariperkg[$nomor] = $d9tainmavari[$nomor] / $prodinmavari;
            // inti plasma sd bulan ini per
            @$d9sdinmaaktuperkg[$nomor] = $d9sdinmaaktu[$nomor] / $prsdinmaaktu;
            @$d9sdinmaaktuperha[$nomor] = $d9sdinmaaktu[$nomor] / $luasinmaaktu;
            @$d9sdinmaanggperkg[$nomor] = $d9sdinmaangg[$nomor] / $prsdinmaangg;
            @$d9sdinmaanggperha[$nomor] = $d9sdinmaangg[$nomor] / $luasinmaangg;
            @$d9sdinmavariperkg[$nomor] = $d9sdinmavari[$nomor] / $prsdinmavari;
            // inti plasma setahun per
            @$d9thinmaanggperkg[$nomor] = $d9thinmaangg[$nomor] / $prthinmaangg;
            @$d9thinmaanggperha[$nomor] = $d9thinmaangg[$nomor] / $luasinmaangg;

            @$d9taintiaktuperrb[$nomor] = $d9taintiaktu[$nomor] / 1000;
            @$d9taintianggperrb[$nomor] = $d9taintiangg[$nomor] / 1000;
            @$d9taintivariperrb[$nomor] = $d9taintivari[$nomor] / 1000;
            @$d9sdintiaktuperrb[$nomor] = $d9sdintiaktu[$nomor] / 1000;
            @$d9sdintianggperrb[$nomor] = $d9sdintiangg[$nomor] / 1000;
            @$d9sdintivariperrb[$nomor] = $d9sdintivari[$nomor] / 1000;
            @$d9thintianggperrb[$nomor] = $d9thintiangg[$nomor] / 1000;

            @$d9taplasaktuperrb[$nomor] = $d9taplasaktu[$nomor] / 1000;
            @$d9taplasanggperrb[$nomor] = $d9taplasangg[$nomor] / 1000;
            @$d9taplasvariperrb[$nomor] = $d9taplasvari[$nomor] / 1000;
            @$d9sdplasaktuperrb[$nomor] = $d9sdplasaktu[$nomor] / 1000;
            @$d9sdplasanggperrb[$nomor] = $d9sdplasangg[$nomor] / 1000;
            @$d9sdplasvariperrb[$nomor] = $d9sdplasvari[$nomor] / 1000;
            @$d9thplasanggperrb[$nomor] = $d9thplasangg[$nomor] / 1000;

            @$d9taundeaktuperrb[$nomor] = $d9taundeaktu[$nomor] / 1000;
            @$d9sdundeaktuperrb[$nomor] = $d9sdundeaktu[$nomor] / 1000;

            @$d9tainmaaktuperrb[$nomor] = $d9tainmaaktu[$nomor] / 1000;
            @$d9tainmaanggperrb[$nomor] = $d9tainmaangg[$nomor] / 1000;
            @$d9tainmavariperrb[$nomor] = $d9tainmavari[$nomor] / 1000;
            @$d9sdinmaaktuperrb[$nomor] = $d9sdinmaaktu[$nomor] / 1000;
            @$d9sdinmaanggperrb[$nomor] = $d9sdinmaangg[$nomor] / 1000;
            @$d9sdinmavariperrb[$nomor] = $d9sdinmavari[$nomor] / 1000;
            @$d9thinmaanggperrb[$nomor] = $d9thinmaangg[$nomor] / 1000;



            if ($inti == 'inti') {
                $showd9taaktuperrb = $d9taintiaktuperrb;
                $showd9taaktuperkg = $d9taintiaktuperkg;
                $showd9taaktuperha = $d9taintiaktuperha;
                $showd9taanggperrb = $d9taintianggperrb;
                $showd9taanggperkg = $d9taintianggperkg;
                $showd9taanggperha = $d9taintianggperha;
                $showd9tavariperrb = $d9taintivariperrb;
                $showd9tavariperkg = $d9taintivariperkg;

                $showd9sd9ktuperrb = $d9sdintiaktuperrb;
                $showd9sd9ktuperkg = $d9sdintiaktuperkg;
                $showd9sd9ktuperha = $d9sdintiaktuperha;
                $showd9sd9nggperrb = $d9sdintianggperrb;
                $showd9sd9nggperkg = $d9sdintianggperkg;
                $showd9sd9nggperha = $d9sdintianggperha;
                $showd9sdvariperrb = $d9sdintivariperrb;
                $showd9sdvariperkg = $d9sdintivariperkg;

                $showd9thanggperrb = $d9thintianggperrb;
                $showd9thanggperkg = $d9thintianggperkg;
                $showd9thanggperha = $d9thintianggperha;
            } else if ($inti == 'plasma') {
                $showd9taaktuperrb = $d9taplasaktuperrb;
                $showd9taaktuperkg = $d9taplasaktuperkg;
                $showd9taaktuperha = $d9taplasaktuperha;
                $showd9taanggperrb = $d9taplasanggperrb;
                $showd9taanggperkg = $d9taplasanggperkg;
                $showd9taanggperha = $d9taplasanggperha;
                $showd9tavariperrb = $d9taplasvariperrb;
                $showd9tavariperkg = $d9taplasvariperkg;

                $showd9sd9ktuperrb = $d9sdplasaktuperrb;
                $showd9sd9ktuperkg = $d9sdplasaktuperkg;
                $showd9sd9ktuperha = $d9sdplasaktuperha;
                $showd9sd9nggperrb = $d9sdplasanggperrb;
                $showd9sd9nggperkg = $d9sdplasanggperkg;
                $showd9sd9nggperha = $d9sdplasanggperha;
                $showd9sdvariperrb = $d9sdplasvariperrb;
                $showd9sdvariperkg = $d9sdplasvariperkg;

                $showd9thanggperrb = $d9thplasanggperrb;
                $showd9thanggperkg = $d9thplasanggperkg;
                $showd9thanggperha = $d9thplasanggperha;
            } else {
                $showd9taaktuperrb = $d9tainmaaktuperrb;
                $showd9taaktuperkg = $d9tainmaaktuperkg;
                $showd9taaktuperha = $d9tainmaaktuperha;
                $showd9taanggperrb = $d9tainmaanggperrb;
                $showd9taanggperkg = $d9tainmaanggperkg;
                $showd9taanggperha = $d9tainmaanggperha;
                $showd9tavariperrb = $d9tainmavariperrb;
                $showd9tavariperkg = $d9tainmavariperkg;

                $showd9sdaktuperrb = $d9sdinmaaktuperrb;
                $showd9sdaktuperkg = $d9sdinmaaktuperkg;
                $showd9sdaktuperha = $d9sdinmaaktuperha;
                $showd9sdanggperrb = $d9sdinmaanggperrb;
                $showd9sdanggperkg = $d9sdinmaanggperkg;
                $showd9sdanggperha = $d9sdinmaanggperha;
                $showd9sdvariperrb = $d9sdinmavariperrb;
                $showd9sdvariperkg = $d9sdinmavariperkg;

                $showd9thanggperrb = $d9thinmaanggperrb;
                $showd9thanggperkg = $d9thinmaanggperkg;
                $showd9thanggperha = $d9thinmaanggperha;
            }

            $tab.="
        <td align=right " . $bgarr . ">" . number_format($d9taundeaktuperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($d9sdundeaktuperrb[$nomor]) . "</td>
            ";
            $tab.="
        <td align=right " . $bgarr . ">" . number_format($showd9taaktuperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9taaktuperkg[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9taaktuperha[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9taanggperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9taanggperkg[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9taanggperha[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9tavariperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9tavariperkg[$nomor]) . "</td>

        <td align=right " . $bgarr . ">" . number_format($showd9sdaktuperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9sdaktuperkg[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9sdaktuperha[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9sdanggperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9sdanggperkg[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9sdanggperha[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9sdvariperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9sdvariperkg[$nomor]) . "</td>

        <td align=right " . $bgarr . ">" . number_format($showd9thanggperrb[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9thanggperkg[$nomor]) . "</td>
        <td align=right " . $bgarr . ">" . number_format($showd9thanggperha[$nomor]) . "</td>
            ";
            $tab.="</tr>";
        }

    // Biaya Produksi TBS ======================================================
    $tab.="<tr class=rowtitle>
        <td align=left colspan=3 " . $bg . ">Biaya Produksi TBS</td>";
    // inti bulan ini per
    @$tttaintiaktuperkg = $tttaintiaktu / $prodintiaktu;
    @$tttaintiaktuperha = $tttaintiaktu / $luasintiaktu;
    @$tttaintianggperkg = $tttaintiangg / $prodintiangg;
    @$tttaintianggperha = $tttaintiangg / $luasintiangg;
    @$tttaintivariperkg = $tttaintivari / $prodintivari;
    // inti sd bulan ini per
    @$ttsdintiaktuperkg = $ttsdintiaktu / $prsdintiaktu;
    @$ttsdintiaktuperha = $ttsdintiaktu / $luasintiaktu;
    @$ttsdintianggperkg = $ttsdintiangg / $prsdintiangg;
    @$ttsdintianggperha = $ttsdintiangg / $luasintiangg;
    @$ttsdintivariperkg = $ttsdintivari / $prsdintivari;
    // inti setahun per
    @$ttthintianggperkg = $ttthintiangg / $prthintiangg;
    @$ttthintianggperha = $ttthintiangg / $luasintiangg;

    // plasma bulan ini per
    @$tttaplasaktuperkg = $tttaplasaktu / $prodplasaktu;
    @$tttaplasaktuperha = $tttaplasaktu / $luasplasaktu;
    @$tttaplasanggperkg = $tttaplasangg / $prodplasangg;
    @$tttaplasanggperha = $tttaplasangg / $luasplasangg;
    @$tttaplasvariperkg = $tttaplasvari / $prodplasvari;
    // plasma sd bulan ini per
    @$ttsdplasaktuperkg = $ttsdplasaktu / $prsdplasaktu;
    @$ttsdplasaktuperha = $ttsdplasaktu / $luasplasaktu;
    @$ttsdplasanggperkg = $ttsdplasangg / $prsdplasangg;
    @$ttsdplasanggperha = $ttsdplasangg / $luasplasangg;
    @$ttsdplasvariperkg = $ttsdplasvari / $prsdplasvari;
    // plasma setahun per
    @$ttthplasanggperkg = $ttthplasangg / $prthplasangg;
    @$ttthplasanggperha = $ttthplasangg / $luasplasangg;

    // inti plasma bulan ini per
    @$tttainmaaktuperkg = $tttainmaaktu / $prodinmaaktu;
    @$tttainmaaktuperha = $tttainmaaktu / $luasinmaaktu;
    @$tttainmaanggperkg = $tttainmaangg / $prodinmaangg;
    @$tttainmaanggperha = $tttainmaangg / $luasinmaangg;
    @$tttainmavariperkg = $tttainmavari / $prodinmavari;
    // inti plasma sd bulan ini per
    @$ttsdinmaaktuperkg = $ttsdinmaaktu / $prsdinmaaktu;
    @$ttsdinmaaktuperha = $ttsdinmaaktu / $luasinmaaktu;
    @$ttsdinmaanggperkg = $ttsdinmaangg / $prsdinmaangg;
    @$ttsdinmaanggperha = $ttsdinmaangg / $luasinmaangg;
    @$ttsdinmavariperkg = $ttsdinmavari / $prsdinmavari;
    // inti plasma setahun per
    @$ttthinmaanggperkg = $ttthinmaangg / $prthinmaangg;
    @$ttthinmaanggperha = $ttthinmaangg / $luasinmaangg;

    @$tttaintiaktuperrb = $tttaintiaktu / 1000;
    @$tttaintianggperrb = $tttaintiangg / 1000;
    @$tttaintivariperrb = $tttaintivari / 1000;
    @$ttsdintiaktuperrb = $ttsdintiaktu / 1000;
    @$ttsdintianggperrb = $ttsdintiangg / 1000;
    @$ttsdintivariperrb = $ttsdintivari / 1000;
    @$ttthintianggperrb = $ttthintiangg / 1000;

    @$tttaplasaktuperrb = $tttaplasaktu / 1000;
    @$tttaplasanggperrb = $tttaplasangg / 1000;
    @$tttaplasvariperrb = $tttaplasvari / 1000;
    @$ttsdplasaktuperrb = $ttsdplasaktu / 1000;
    @$ttsdplasanggperrb = $ttsdplasangg / 1000;
    @$ttsdplasvariperrb = $ttsdplasvari / 1000;
    @$ttthplasanggperrb = $ttthplasangg / 1000;

    @$tttaundeaktuperrb = $tttaundeaktu / 1000;
    @$ttsdundeaktuperrb = $ttsdundeaktu / 1000;

    @$tttainmaaktuperrb = $tttainmaaktu / 1000;
    @$tttainmaanggperrb = $tttainmaangg / 1000;
    @$tttainmavariperrb = $tttainmavari / 1000;
    @$ttsdinmaaktuperrb = $ttsdinmaaktu / 1000;
    @$ttsdinmaanggperrb = $ttsdinmaangg / 1000;
    @$ttsdinmavariperrb = $ttsdinmavari / 1000;
    @$ttthinmaanggperrb = $ttthinmaangg / 1000;



    if ($inti == 'inti') {
        $showtttaaktuperrb = $tttaintiaktuperrb;
        $showtttaaktuperkg = $tttaintiaktuperkg;
        $showtttaaktuperha = $tttaintiaktuperha;
        $showtttaanggperrb = $tttaintianggperrb;
        $showtttaanggperkg = $tttaintianggperkg;
        $showtttaanggperha = $tttaintianggperha;
        $showtttavariperrb = $tttaintivariperrb;
        $showtttavariperkg = $tttaintivariperkg;

        $showttsttktuperrb = $ttsdintiaktuperrb;
        $showttsttktuperkg = $ttsdintiaktuperkg;
        $showttsttktuperha = $ttsdintiaktuperha;
        $showttsttnggperrb = $ttsdintianggperrb;
        $showttsttnggperkg = $ttsdintianggperkg;
        $showttsttnggperha = $ttsdintianggperha;
        $showttsdvariperrb = $ttsdintivariperrb;
        $showttsdvariperkg = $ttsdintivariperkg;

        $showttthanggperrb = $ttthintianggperrb;
        $showttthanggperkg = $ttthintianggperkg;
        $showttthanggperha = $ttthintianggperha;
    } else if ($inti == 'plasma') {
        $showtttaaktuperrb = $tttaplasaktuperrb;
        $showtttaaktuperkg = $tttaplasaktuperkg;
        $showtttaaktuperha = $tttaplasaktuperha;
        $showtttaanggperrb = $tttaplasanggperrb;
        $showtttaanggperkg = $tttaplasanggperkg;
        $showtttaanggperha = $tttaplasanggperha;
        $showtttavariperrb = $tttaplasvariperrb;
        $showtttavariperkg = $tttaplasvariperkg;

        $showttsttktuperrb = $ttsdplasaktuperrb;
        $showttsttktuperkg = $ttsdplasaktuperkg;
        $showttsttktuperha = $ttsdplasaktuperha;
        $showttsttnggperrb = $ttsdplasanggperrb;
        $showttsttnggperkg = $ttsdplasanggperkg;
        $showttsttnggperha = $ttsdplasanggperha;
        $showttsdvariperrb = $ttsdplasvariperrb;
        $showttsdvariperkg = $ttsdplasvariperkg;

        $showttthanggperrb = $ttthplasanggperrb;
        $showttthanggperkg = $ttthplasanggperkg;
        $showttthanggperha = $ttthplasanggperha;
    } else {
        $showtttaaktuperrb = $tttainmaaktuperrb;
        $showtttaaktuperkg = $tttainmaaktuperkg;
        $showtttaaktuperha = $tttainmaaktuperha;
        $showtttaanggperrb = $tttainmaanggperrb;
        $showtttaanggperkg = $tttainmaanggperkg;
        $showtttaanggperha = $tttainmaanggperha;
        $showtttavariperrb = $tttainmavariperrb;
        $showtttavariperkg = $tttainmavariperkg;

        $showttsdaktuperrb = $ttsdinmaaktuperrb;
        $showttsdaktuperkg = $ttsdinmaaktuperkg;
        $showttsdaktuperha = $ttsdinmaaktuperha;
        $showttsdanggperrb = $ttsdinmaanggperrb;
        $showttsdanggperkg = $ttsdinmaanggperkg;
        $showttsdanggperha = $ttsdinmaanggperha;
        $showttsdvariperrb = $ttsdinmavariperrb;
        $showttsdvariperkg = $ttsdinmavariperkg;

        $showttthanggperrb = $ttthinmaanggperrb;
        $showttthanggperkg = $ttthinmaanggperkg;
        $showttthanggperha = $ttthinmaanggperha;
    }

    $tab.="
    <td align=right " . $bg . ">" . number_format($tttaundeaktuperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($ttsdundeaktuperrb) . "</td>
        ";
    $tab.="
    <td align=right " . $bg . ">" . number_format($showtttaaktuperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtttaaktuperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtttaaktuperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showtttaanggperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtttaanggperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showtttaanggperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showtttavariperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showtttavariperkg) . "</td>

    <td align=right " . $bg . ">" . number_format($showttsdaktuperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showttsdaktuperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showttsdaktuperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showttsdanggperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showttsdanggperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showttsdanggperha) . "</td>
    <td align=right " . $bg . ">" . number_format($showttsdvariperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showttsdvariperkg) . "</td>

    <td align=right " . $bg . ">" . number_format($showttthanggperrb) . "</td>
    <td align=right " . $bg . ">" . number_format($showttthanggperkg) . "</td>
    <td align=right " . $bg . ">" . number_format($showttthanggperha) . "</td>
        ";
    $tab.="</tr>";



    $tab.="</tbody></table></div>";
    switch ($proses) {
        case'preview':
            echo $tab;
            break;
        case'excel':
            $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
            $dte = date("YmdHis");
            $nop_ = "mr_biayaProduksiTbs_" . $pt . $unit . $afdeling . $periode;
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
            $cols = 247.5;
            $wkiri = 5;
            $wlain = 4.5;

            class PDF extends FPDF {

                function Header() {
                    global $periode;
                    global $pt;
                    global $unit;
                    global $optNm;
                    global $optBulan;
                    global $tahun;
                    global $bulan;
                    global $dbname;
                    global $luas;
                    global $wkiri, $wlain;
                    global $luasbudg, $luasreal, $tt;
                    $width = $this->w - $this->lMargin - $this->rMargin;

                    $height = 20;
                    $this->SetFillColor(220, 220, 220);
                    $this->SetFont('Arial', 'B', 12);

                    $kepala = 'PT ' . $pt;
                    if ($unit != '')
                        $kepala.=', UNIT ' . $unit;
                    $this->Cell($width, $height, $kepala, NULL, 0, 'L', 1);
                    $this->Ln();
                    $this->Cell($width, $height, 'LAPORAN SUMMARY PEMBUKAAN DAN TANAMAN BELUM MENGHASILKAN', NULL, 0, 'L', 1);
                    $this->Ln();
                    $this->Cell($width, $height, 'PER TAHUN TANAM', NULL, 0, 'L', 1);
                    $this->Ln();
                    $this->Cell($width, $height, 'S/D ' . $bulan . '-' . $tahun, NULL, 0, 'L', 1);
                    $this->Ln();
                    $this->Ln();

                    $height = 15;
                    $this->SetFont('Arial', 'B', 7);
                    $this->Cell(($wkiri + $wlain + $wlain) / 100 * $width, $height, 'Tahun Tanam', 1, 0, 'C', 1);
                    if (!empty($tt))
                        foreach ($tt as $tata) {
                            if ($tata == 0)
                                $this->Cell(($wlain + $wlain) / 100 * $width, $height, 'Undefined', 1, 0, 'C', 1);
                            else
                                $this->Cell(($wlain + $wlain) / 100 * $width, $height, $tata, 1, 0, 'C', 1);
                        }
                    $this->Cell(($wlain + $wlain) / 100 * $width, $height, 'Total', 1, 0, 'C', 1);
                    $this->Ln();


                }

                function Footer() {
                    $this->SetY(-15);
                    $this->SetFont('Arial', 'I', 8);
                    $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
                }

            }

            //================================

            $pdf = new PDF('L', 'pt', 'A4');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 15;
            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 7);


            $pdf->Output();
            break;
        default:
            break;
    }
}
?>