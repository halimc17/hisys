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
$kegId = checkPostGet('kegId', '');

$qwe = explode("-", $periode);
$tahun = $qwe[0];
$bulan = $qwe[1];
//exit("Error:".$periode."___".$tahun."___".$bulan);
$optNm = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optKeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$optKegSat = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
$optNmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
if ($unit == '' || $periode == '' || $kegId == '') {
    exit("Error:Field Tidak Boleh Kosong");
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

$addstr2 = "(";
for ($W = 1; $W <= intval($bulan); $W++) {
    if ($W < 10)
        $jack = "fis0" . $W;
    else
        $jack = "fis" . $W;
    if ($W < intval($bulan))
        $addstr2.=$jack . "+";
    else
        $addstr2.=$jack;
}
$addstr2.=")";
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
$bg = "";
$brdr = 0;
#barang#
$dtAfd = array();
$sData = "select distinct kodebarang,sum(kwantitas) as jmlhbrg,tahuntanam,left(a.kodeorg,6) as afd from " . $dbname . ".kebun_pakai_material_vw a
        left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg
        where kodekegiatan like '" . $kegId . "%' and a.kodeorg like '" . $unit . "%'
        and tanggal like '" . $periode . "%' and tahuntanam!='' group by tahuntanam,left(a.kodeorg,6),
        kodebarang order by left(a.kodeorg,6) asc,tahuntanam asc";
if ($afdId != '') {
    $sData = "select distinct kodebarang,sum(kwantitas) as jmlhbrg,tahuntanam,left(a.kodeorg,6) as afd from " . $dbname . ".kebun_pakai_material_vw a
        left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg
        where kodekegiatan like '" . $kegId . "%' and a.kodeorg like '" . $afdId . "%'
        and tanggal like '" . $periode . "%' and tahuntanam!='' group by tahuntanam,left(a.kodeorg,6),
        kodebarang order by left(a.kodeorg,6) asc,tahuntanam asc";
}

$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while ($rData = $qData->fetch()) {
    if ($rData['jmlhbrg'] != '') {
        $dtBarang[$rData['kodebarang']] = $rData['kodebarang'];
        $dtAfd[$rData['afd']] = $rData['afd'];
        $dtThnTnm[$rData['tahuntanam']] = $rData['tahuntanam'];
        $dtJmlhBrg[$rData['afd'] . $rData['tahuntanam']][$rData['kodebarang']] = $rData['jmlhbrg'];
    }
}


$sData2 = "select distinct kodebarang,sum(kwantitas) as jmlhbrg,tahuntanam,left(a.kodeorg,6) as afd from " . $dbname . ".kebun_pakai_material_vw a
        left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg
        where kodekegiatan like '" . $kegId . "%' and a.kodeorg like '" . $unit . "%'
        and left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "%' and tahuntanam!=''
        group by tahuntanam,left(kodeorg,6),kodebarang order by left(a.kodeorg,6) asc,tahuntanam asc";
if ($afdId != '') {
    $sData2 = "select distinct kodebarang,sum(kwantitas) as jmlhbrg,tahuntanam,left(a.kodeorg,6) as afd from " . $dbname . ".kebun_pakai_material_vw a
        left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg
        where kodekegiatan like '" . $kegId . "%' and a.kodeorg like '" . $afdId . "%'
        and left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "%' and tahuntanam!=''
        group by tahuntanam,left(kodeorg,6),kodebarang order by left(a.kodeorg,6) asc,tahuntanam asc";
}

$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while ($rData = $qData->fetch()) {
    if ($rData['jmlhbrg'] != '') {
        $dtBarangSi[$rData['kodebarang']] = $rData['kodebarang'];
        $dtAfd[$rData['afd']] = $rData['afd'];
        $dtThnTnm[$rData['tahuntanam']] = $rData['tahuntanam'];
        $dtJmlhBrgSi[$rData['afd'] . $rData['tahuntanam']][$rData['kodebarang']] = $rData['jmlhbrg'];
    }
}
$sBgt = "select distinct sum(fis" . $bulan . ") as jmlhbrg,left(kodeorg,6) as afd,thntnm as tahuntanam,kodebarang from " . $dbname . ".bgt_budget_kebun_perblok_vw
       where kodeorg like '" . $unit . "%' and tahunbudget='" . $tahun . "' and kodebarang!='' and kegiatan like '" . $kegId . "%' and thntnm!=''
       group by  thntnm,left(kodeorg,6),kodebarang order by left(kodeorg,6) asc,tahuntanam asc";
if ($afdId != '') {
    $sBgt = "select distinct sum(fis" . $bulan . ") as jmlhbrg,left(kodeorg,6) as afd,thntnm as tahuntanam,kodebarang from " . $dbname . ".bgt_budget_kebun_perblok_vw
       where kodeorg like '" . $afdId . "%' and tahunbudget='" . $tahun . "' and kodebarang!='' and kegiatan like '" . $kegId . "%' and thntnm!=''
       group by  thntnm,left(kodeorg,6),kodebarang order by left(kodeorg,6) asc,tahuntanam asc";
}

$qBgt = $owlPDO->query($sBgt) or die(print " Gagal: " . PDOException::getMessage());
$qBgt->setFetchMode(PDO::FETCH_ASSOC);
while ($rBgt = $qBgt->fetch()) {
    if ($rBgt['jmlhbrg'] != '') {
        $dtAfd[$rBgt['afd']] = $rBgt['afd'];
        $dtThnTnm[$rBgt['tahuntanam']] = $rBgt['tahuntanam'];
        $dtBarangBgt[$rBgt['kodebarang']] = $rBgt['kodebarang'];
        $dtJmlhBgt[$rBgt['afd'] . $rBgt['tahuntanam']][$rBgt['kodebarang']] = $rBgt['jmlhbrg'];
    }
}

$sBgt = "select distinct sum(" . $addstr2 . ") as jmlhbrg,left(kodeorg,6) as afd,thntnm as tahuntanam,kodebarang 
       from " . $dbname . ".bgt_budget_kebun_perblok_vw
       where kodeorg like '" . $unit . "%' and tahunbudget='" . $tahun . "' and kodebarang!='' and kegiatan like '" . $kegId . "%'  and thntnm!=''
       group by  thntnm,left(kodeorg,6),kodebarang order by left(kodeorg,6) asc,tahuntanam asc";
if ($afdId != '') {
    $sBgt = "select distinct sum(" . $addstr2 . ") as jmlhbrg,left(kodeorg,6) as afd,thntnm as tahuntanam,kodebarang 
       from " . $dbname . ".bgt_budget_kebun_perblok_vw
       where kodeorg like '" . $afdId . "%' and tahunbudget='" . $tahun . "' and kodebarang!='' and kegiatan like '" . $kegId . "%'  and thntnm!=''
       group by  thntnm,left(kodeorg,6),kodebarang order by left(kodeorg,6) asc,tahuntanam asc";
}

$qBgt = $owlPDO->query($sBgt) or die(print " Gagal: " . PDOException::getMessage());
$qBgt->setFetchMode(PDO::FETCH_ASSOC);
while ($rBgt = $qBgt->fetch()) {
    if ($rBgt['jmlhbrg'] != '') {
        $dtAfd[$rBgt['afd']] = $rBgt['afd'];
        $dtThnTnm[$rBgt['tahuntanam']] = $rBgt['tahuntanam'];
        $dtBarangBgtSi[$rBgt['kodebarang']] = $rBgt['kodebarang'];
        $dtJmlhBgtSi[$rBgt['afd'] . $rBgt['tahuntanam']][$rBgt['kodebarang']] = $rBgt['jmlhbrg'];
    }
}
@$colsBrg = intval(count($dtBarang));
@$colsBgtBrg = intval(count($dtBarangBgt));
@$colsBrgSi = intval(count($dtBarangSi));
@$colsBgtBrgSi = intval(count($dtBarangBgtSi));
#barang end#
#hk #
$sDataHk = "select distinct sum(jhk) as jmlhk,tahuntanam,left(a.kodeorg,6) as afd from " . $dbname . ".kebun_kehadiran_vw a
        left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg
        where kodekegiatan like '" . $kegId . "%' and a.kodeorg like '" . $unit . "%'
        and tanggal like '" . $periode . "%' group by tahuntanam,left(a.kodeorg,6) and tahuntanam!=''
        order by left(a.kodeorg,6) asc,tahuntanam asc";

if ($afdId != '') {
    $sDataHk = "select distinct sum(jhk) as jmlhk,tahuntanam,left(a.kodeorg,6) as afd from " . $dbname . ".kebun_kehadiran_vw a
        left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg
        where kodekegiatan like '" . $kegId . "%' and a.kodeorg like '" . $afdId . "%'
        and tanggal like '" . $periode . "%' group by tahuntanam,left(a.kodeorg,6) and tahuntanam!=''
        order by left(a.kodeorg,6) asc,tahuntanam asc";
}

$qDataHk = $owlPDO->query($sDataHk) or die(print " Gagal: " . PDOException::getMessage());
$qDataHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataHk = $qDataHk->fetch()) {
    $dtAfd[$rDataHk['afd']] = $rDataHk['afd'];
    $dtThnTnm[$rDataHk['tahuntanam']] = $rDataHk['tahuntanam'];
    @$dtHk[$rDataHk['afd'] . $rDataHk['tahuntanam']]+=$rDataHk['jmlhk'];
}
$sDataHkSi = "select distinct sum(jhk) as jmlhk,tahuntanam,left(a.kodeorg,6) as afd from " . $dbname . ".kebun_kehadiran_vw a
            left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg
            where kodekegiatan like '" . $kegId . "%' and a.kodeorg like '" . $unit . "%' 
            and left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "'  and tahuntanam!=''
            group by tahuntanam,left(a.kodeorg,6) order by left(a.kodeorg,6) asc,tahuntanam asc";
if ($afdId != '') {
    $sDataHkSi = "select distinct sum(jhk) as jmlhk,tahuntanam,left(a.kodeorg,6) as afd from " . $dbname . ".kebun_kehadiran_vw a
            left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg
            where kodekegiatan like '" . $kegId . "%' and a.kodeorg like '" . $afdId . "%' 
            and left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "'  and tahuntanam!=''
            group by tahuntanam,left(a.kodeorg,6) order by left(a.kodeorg,6) asc,tahuntanam asc";
}

$qDataHkSi = $owlPDO->query($sDataHkSi) or die(print " Gagal: " . PDOException::getMessage());
$qDataHkSi->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataHkSi = $qDataHkSi->fetch()) {
    if ($rDataHkSi['jmlhk'] != '') {
        $dtAfd[$rDataHkSi['afd']] = $rDataHkSi['afd'];
        $dtThnTnm[$rDataHkSi['tahuntanam']] = $rDataHkSi['tahuntanam'];
        @$dtHkSi[$rDataHkSi['afd'] . $rDataHkSi['tahuntanam']]+=$rDataHkSi['jmlhk'];
    }
}
$sDtHkBgt = "select distinct sum(fis" . $bulan . ") as jmlhk,left(kodeorg,6) as afd,thntnm as tahuntanam 
           from " . $dbname . ".bgt_budget_kebun_perblok_vw where 
           kodeorg like '" . $unit . "%' and tahunbudget='" . $tahun . "' and kodebudget like 'SDM%'  and kegiatan like '" . $kegId . "%' and thntnm!=''
           group by left(kodeorg,6),thntnm order by left(kodeorg,6)asc,thntnm asc";
if ($afdId != '') {
    $sDtHkBgt = "select distinct sum(fis" . $bulan . ") as jmlhk,left(kodeorg,6) as afd,thntnm as tahuntanam 
           from " . $dbname . ".bgt_budget_kebun_perblok_vw where 
           kodeorg like '" . $afdId . "%' and tahunbudget='" . $tahun . "' and kodebudget like 'SDM%'  and kegiatan like '" . $kegId . "%' and thntnm!=''
           group by left(kodeorg,6),thntnm order by left(kodeorg,6)asc,thntnm asc";
}

$qDtHkBgt = $owlPDO->query($sDtHkBgt) or die(print " Gagal: " . PDOException::getMessage());
$qDtHkBgt->setFetchMode(PDO::FETCH_ASSOC);
while ($rDtBgt = $qDtHkBgt->fetch()) {
    if ($rDtBgt['jmlhk'] != '') {
        $dtAfd[$rDtBgt['afd']] = $rDtBgt['afd'];
        $dtThnTnm[$rDtBgt['tahuntanam']] = $rDtBgt['tahuntanam'];
        $dtHkBgt[$rDtBgt['afd'] . $rDtBgt['tahuntanam']] = $rDtBgt['jmlhk'];
    }
}
$sDtHkBgtSi = "select distinct sum(" . $addstr2 . ") as jmlhk,left(kodeorg,6) as afd,thntnm as tahuntanam 
           from " . $dbname . ".bgt_budget_kebun_perblok_vw where 
           kodeorg like '" . $unit . "%' and tahunbudget='" . $tahun . "'  and kegiatan like '" . $kegId . "%'  and kodebudget like 'SDM%' and thntnm!=''
           group by left(kodeorg,6),thntnm order by left(kodeorg,6) asc,thntnm asc";
if ($afdId != '') {
    $sDtHkBgtSi = "select distinct sum(" . $addstr2 . ") as jmlhk,left(kodeorg,6) as afd,thntnm as tahuntanam 
           from " . $dbname . ".bgt_budget_kebun_perblok_vw where 
           kodeorg like '" . $afdId . "%' and tahunbudget='" . $tahun . "'  and kegiatan like '" . $kegId . "%'  and kodebudget like 'SDM%' and thntnm!=''
           group by left(kodeorg,6),thntnm order by left(kodeorg,6) asc,thntnm asc";
}

$qDtHkBgtSi = $owlPDO->query($sDtHkBgtSi) or die(print " Gagal: " . PDOException::getMessage());
$qDtHkBgtSi->setFetchMode(PDO::FETCH_ASSOC);
while ($rDtBgtSi = $qDtHkBgtSi->fetch()) {
    if ($rDtBgtSi['jmlhk'] != '') {
        $dtAfd[$rDtBgtSi['afd']] = $rDtBgtSi['afd'];
        $dtThnTnm[$rDtBgtSi['tahuntanam']] = $rDtBgtSi['tahuntanam'];
        $dtHkBgtSi[$rDtBgtSi['afd'] . $rDtBgtSi['tahuntanam']] = $rDtBgtSi['jmlhk'];
    }
}
#hk end#
#rupiah#
$sRup = "select distinct sum(jumlah) as jmlrp,left(a.kodeblok,6) as afd,b.tahuntanam as tahuntanam
       from " . $dbname . ".keu_jurnaldt a left join " . $dbname . ".setup_blok b 
       on a.kodeblok=b.kodeorg where a.kodeblok!='' and a.kodeblok like '" . $unit . "%' and kodekegiatan like '" . $kegId . "%'
       and tanggal like '" . $periode . "%' and b.tahuntanam!='' group by left(a.kodeblok,6),b.tahuntanam 
       order by left(a.kodeblok,6) asc,b.tahuntanam asc";
if ($afdId != '') {
    $sRup = "select distinct sum(jumlah) as jmlrp,left(a.kodeblok,6) as afd,b.tahuntanam as tahuntanam
       from " . $dbname . ".keu_jurnaldt a left join " . $dbname . ".setup_blok b 
       on a.kodeblok=b.kodeorg where a.kodeblok!='' and a.kodeblok like '" . $afdId . "%' and kodekegiatan like '" . $kegId . "%'
       and tanggal like '" . $periode . "%' and b.tahuntanam!='' group by left(a.kodeblok,6),b.tahuntanam 
       order by left(a.kodeblok,6) asc,b.tahuntanam asc";
}

$qRup = $owlPDO->query($sRup) or die(print " Gagal: " . PDOException::getMessage());
$qRup->setFetchMode(PDO::FETCH_ASSOC);
while ($rRup = $qRup->fetch()) {
    if ($rRup['jmlrp'] != '') {
        $dtAfd[$rRup['afd']] = $rRup['afd'];
        $dtThnTnm[$rRup['tahuntanam']] = $rRup['tahuntanam'];
        $dtJmlhRp[$rRup['afd'] . $rRup['tahuntanam']] = $rRup['jmlrp'];
    }
}
$sRupSi = "select distinct sum(jumlah) as jmlrp,left(a.kodeblok,6) as afd,b.tahuntanam as tahuntanam
       from " . $dbname . ".keu_jurnaldt a left join " . $dbname . ".setup_blok b 
       on a.kodeblok=b.kodeorg where a.kodeblok!='' and a.kodeblok like '" . $unit . "%' and kodekegiatan like '" . $kegId . "%'
       and left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "' and b.tahuntanam!='' group by left(a.kodeblok,6),b.tahuntanam 
       order by left(a.kodeblok,6) asc,b.tahuntanam asc";
if ($afdId != '') {
    $sRupSi = "select distinct sum(jumlah) as jmlrp,left(a.kodeblok,6) as afd,b.tahuntanam as tahuntanam
       from " . $dbname . ".keu_jurnaldt a left join " . $dbname . ".setup_blok b 
       on a.kodeblok=b.kodeorg where a.kodeblok!='' and a.kodeblok like '" . $afdId . "%' and kodekegiatan like '" . $kegId . "%'
       and left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "' and b.tahuntanam!='' group by left(a.kodeblok,6),b.tahuntanam 
       order by left(a.kodeblok,6) asc,b.tahuntanam asc";
}

$qRupSi = $owlPDO->query($sRupSi) or die(print " Gagal: " . PDOException::getMessage());
$qRupSi->setFetchMode(PDO::FETCH_ASSOC);
while ($rRupSi = $qRupSi->fetch()) {
    if ($rRupSi['jmlrp'] != '') {
        $dtAfd[$rRupSi['afd']] = $rRupSi['afd'];
        $dtThnTnm[$rRupSi['tahuntanam']] = $rRupSi['tahuntanam'];
        $dtJmlhRpSi[$rRupSi['afd'] . $rRupSi['tahuntanam']] = $rRupSi['jmlrp'];
    }
}

$sRupBgt = "select distinct sum(rp" . $bulan . ") as jmlhrp,left(kodeorg,6) as afd,thntnm as tahuntanam 
          from " . $dbname . ".bgt_budget_kebun_perblok_vw where  kodeorg like '" . $unit . "%' and tahunbudget='" . $tahun . "' 
          and kegiatan like '" . $kegId . "%'  and thntnm!=''
          group by left(kodeorg,6),thntnm order by left(kodeorg,6)asc,thntnm asc";
if ($afdId != '') {
    $sRupBgt = "select distinct sum(rp" . $bulan . ") as jmlhrp,left(kodeorg,6) as afd,thntnm as tahuntanam 
          from " . $dbname . ".bgt_budget_kebun_perblok_vw where  kodeorg like '" . $afdId . "%' and tahunbudget='" . $tahun . "' 
          and kegiatan like '" . $kegId . "%'  and thntnm!=''
          group by left(kodeorg,6),thntnm order by left(kodeorg,6)asc,thntnm asc";
}

$qRupBgt = $owlPDO->query($sRupBgt) or die(print " Gagal: " . PDOException::getMessage());
$qRupBgt->setFetchMode(PDO::FETCH_ASSOC);
while ($rRupBgt = $qRupBgt->fetch()) {
    if ($rRupBgt['jmlrp'] != '') {
        $dtAfd[$rRupBgt['afd']] = $rRupBgt['afd'];
        $dtThnTnm[$rRupBgt['tahuntanam']] = $rRupBgt['tahuntanam'];
        $dtJmlhRpBgt[$rRupBgt['afd'] . $rRupBgt['tahuntanam']] = $rRupBgt['jmlrp'];
    }
}
$sRupBgtSi = "select distinct sum(" . $addstr . ") as jmlhrp,left(kodeorg,6) as afd,thntnm as tahuntanam 
          from " . $dbname . ".bgt_budget_kebun_perblok_vw where  kodeorg like '" . $unit . "%' and tahunbudget='" . $tahun . "' 
          and kegiatan like '" . $kegId . "%' and thntnm!=''
          group by left(kodeorg,6),thntnm order by left(kodeorg,6) asc,thntnm asc";
if ($afdId != '') {
    $sRupBgtSi = "select distinct sum(" . $addstr . ") as jmlhrp,left(kodeorg,6) as afd,thntnm as tahuntanam 
          from " . $dbname . ".bgt_budget_kebun_perblok_vw where  kodeorg like '" . $afdId . "%' and tahunbudget='" . $tahun . "' 
          and kegiatan like '" . $kegId . "%' and thntnm!=''
          group by left(kodeorg,6),thntnm order by left(kodeorg,6) asc,thntnm asc";
}

$qRupBgtSi = $owlPDO->query($sRupBgtSi) or die(print " Gagal: " . PDOException::getMessage());
$qRupBgtSi->setFetchMode(PDO::FETCH_ASSOC);
while ($rRupBgtSi = $qRupBgtSi->fetch()) {
    if ($rRupBgtSi['jmlrp'] != '') {
        $dtAfd[$rRupBgtSi['afd']] = $rRupBgtSi['afd'];
        $dtThnTnm[$rRupBgtSi['tahuntanam']] = $rRupBgtSi['tahuntanam'];
        $dtJmlhRpBgtSi[$rRupBgtSi['afd'] . $rRupBgtSi['tahuntanam']] = $rRupBgtSi['jmlrp'];
    }
}
#rupiah end#
#luas#
$sLuas = "select distinct sum(hasilkerja) as luaskrja,left(a.kodeorg,6) as afd,b.tahuntanam from 
        " . $dbname . ".`kebun_perawatan_vw` a left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg
        where a.kodeorg like '" . $unit . "%' and tanggal like '" . $periode . "%' and kodekegiatan like '" . $kegId . "%' and b.tahuntanam!='' and hasilkerja!=''
        group by left(a.kodeorg,6),b.tahuntanam order by left(a.kodeorg,6) asc,b.tahuntanam asc";
if ($afdId != '') {
    $sLuas = "select distinct sum(hasilkerja) as luaskrja,left(a.kodeorg,6) as afd,b.tahuntanam from 
        " . $dbname . ".`kebun_perawatan_vw` a left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg
        where a.kodeorg like '" . $afdId . "%' and tanggal like '" . $periode . "%' and kodekegiatan like '" . $kegId . "%' and b.tahuntanam!='' and hasilkerja!=''
        group by left(a.kodeorg,6),b.tahuntanam order by left(a.kodeorg,6) asc,b.tahuntanam asc";
}

$qLuas = $owlPDO->query($sLuas) or die(print " Gagal: " . PDOException::getMessage());
$qLuas->setFetchMode(PDO::FETCH_ASSOC);
while ($rLuas = $qLuas->fetch()) {
    $dtAfd[$rLuas['afd']] = $rLuas['afd'];
    $dtThnTnm[$rLuas['tahuntanam']] = $rLuas['tahuntanam'];
    $dtLuas[$rLuas['afd'] . $rLuas['tahuntanam']] = $rLuas['luaskrja'];
}
#luas end#
#setahun semuanya,hk,brg,rp#
$sThn = "select distinct sum(jumlah) as jmlh,kodebarang,left(kodeorg,6) as afd,thntnm as tahuntanam from
      " . $dbname . ".bgt_budget_kebun_perblok_vw where  kodeorg like '" . $unit . "%' and tahunbudget='" . $tahun . "' 
      and kegiatan like '" . $kegId . "%' and thntnm!='' and kodebarang!=''
      group by kodebarang,left(kodeorg,6),thntnm order by left(kodeorg,6) asc,thntnm asc";
if ($afdId != '') {
    $sThn = "select distinct sum(jumlah) as jmlh,kodebarang,left(kodeorg,6) as afd,thntnm as tahuntanam from
      " . $dbname . ".bgt_budget_kebun_perblok_vw where  kodeorg like '" . $afdId . "%' and tahunbudget='" . $tahun . "' 
      and kegiatan like '" . $kegId . "%' and thntnm!='' and kodebarang!=''
      group by kodebarang,left(kodeorg,6),thntnm order by left(kodeorg,6) asc,thntnm asc";
}

$qThn = $owlPDO->query($sThn) or die(print " Gagal: " . PDOException::getMessage());
$qThn->setFetchMode(PDO::FETCH_ASSOC);
while ($rThn = $qThn->fetch()) {
    $dtAfd[$rThn['afd']] = $rThn['afd'];
    $dtThnTnm[$rThn['tahuntanam']] = $rThn['tahuntanam'];
    $dtBrgSthn[$rThn['kodebarang']] = $rThn['kodebarang'];
    $dtJmlBrgSthn[$rThn['afd'] . $rThn['tahuntanam']][$rThn['kodebarang']] = $rThn['jmlh'];
}
$sThn = "select distinct sum(jumlah) as jmlh,left(kodeorg,6) as afd,thntnm as tahuntanam from
      " . $dbname . ".bgt_budget_kebun_perblok_vw where  kodeorg like '" . $unit . "%' and tahunbudget='" . $tahun . "' 
      and kegiatan like '" . $kegId . "%' and thntnm!='' and kodebudget like 'SDM%'
      group by left(kodeorg,6),thntnm order by left(kodeorg,6) asc,thntnm asc";
if ($afdId != '') {
    $sThn = "select distinct sum(jumlah) as jmlh,left(kodeorg,6) as afd,thntnm as tahuntanam from
      " . $dbname . ".bgt_budget_kebun_perblok_vw where  kodeorg like '" . $afdId . "%' and tahunbudget='" . $tahun . "' 
      and kegiatan like '" . $kegId . "%' and thntnm!='' and kodebudget like 'SDM%'
      group by left(kodeorg,6),thntnm order by left(kodeorg,6) asc,thntnm asc";
}

$qThn = $owlPDO->query($sThn) or die(print " Gagal: " . PDOException::getMessage());
$qThn->setFetchMode(PDO::FETCH_ASSOC);
while ($rThn = $qThn->fetch()) {
    $dtAfd[$rThn['afd']] = $rThn['afd'];
    $dtThnTnm[$rThn['tahuntanam']] = $rThn['tahuntanam'];
    $dtJmlHkSthn[$rThn['afd'] . $rThn['tahuntanam']] = $rThn['jmlh'];
}
$sThn = "select distinct sum(rupiah) as jmlh,left(kodeorg,6) as afd,thntnm as tahuntanam from
      " . $dbname . ".bgt_budget_kebun_perblok_vw where  kodeorg like '" . $unit . "%' and tahunbudget='" . $tahun . "' 
      and kegiatan like '" . $kegId . "%' and thntnm!=''  
      group by left(kodeorg,6),thntnm order by left(kodeorg,6) asc,thntnm asc";
if ($afdId != '') {
    $sThn = "select distinct sum(rupiah) as jmlh,left(kodeorg,6) as afd,thntnm as tahuntanam from
      " . $dbname . ".bgt_budget_kebun_perblok_vw where  kodeorg like '" . $afdId . "%' and tahunbudget='" . $tahun . "' 
      and kegiatan like '" . $kegId . "%' and thntnm!=''  
      group by left(kodeorg,6),thntnm order by left(kodeorg,6) asc,thntnm asc";
}


$qThn = $owlPDO->query($sThn) or die(print " Gagal: " . PDOException::getMessage());
$qThn->setFetchMode(PDO::FETCH_ASSOC);
while ($rThn = $qThn->fetch()) {
    $dtAfd[$rThn['afd']] = $rThn['afd'];
    $dtThnTnm[$rThn['tahuntanam']] = $rThn['tahuntanam'];
    $dtJmlRpSthn[$rThn['afd'] . $rThn['tahuntanam']] = $rThn['jmlh'];
}
#setahun semuanya,hk,brg,rp end#
if ($proses == 'excel') {
    $optKeg[$kegId] = isset($optKeg[$kegId]) ? $optKeg[$kegId] : '';
    $bg = " bgcolor=#DEDEDE";
    $brdr = 1;
    $tab.="<table border=0>
         <tr>
            <td colspan=8 align=left><font size=3>" . $optKeg[$kegId] . "</font></td>
            <td colspan=6 align=right>" . $_SESSION['lang']['bulan'] . " : " . $optBulan[$bulan] . " " . $tahun . "</td>
         </tr> 
         <tr><td colspan=14 align=left>" . $_SESSION['lang']['unit'] . " : " . $optNm[$unit] . " (" . $unit . ")</td></tr>";
    if ($afdId != '') {
        $tab.="<tr><td colspan=14 align=left>" . $_SESSION['lang']['afdeling'] . " : " . $optNm[$afdId] . " (" . $afdId . ")</td></tr>";
    }
    $tab.="</table>";
}



$brdr0;
$bgcoloraja = "";
$preview = isset($preview) ? $preview : '';
if ($preview == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE";
    $brdr = 1;
}
$tab.=$judul;

$tab.="<table cellpadding=1 cellspacing=1 border=" . $brdr . " class=sortable style='width:100%;'>
    <thead class=rowheader>
    <tr>";
$ard5 = $ard4 = $ard3 = $ard2 = $ard = 1;
@$colsthun = intval(count($dtBrgSthn));
if (empty($dtBarang)) {
    //exit("Error:masup");
    $colsBrg = 1;
    $ard = 0;
}
if (empty($dtBarangBgt)) {
    $colsBgtBrg = 1;
    $ard2 = 0;
}
if (empty($dtBarangSi)) {
    $colsBrgSi = 1;
    $ard3 = 0;
}
if (empty($dtBarangBgtSi)) {
    $colsBgtBrgSi = 1;
    $ard4 = 0;
}
if (empty($dtBrgSthn)) {
    $colsthun = 1;
    $ard5 = 0;
}
//$colsBrg=intval(count($dtBarang));
//$colsBgtBrg=intval(count($dtBarangBgt));
//$colsBrgSi=intval(count($dtBarangSi));
//$colsBgtBrgSi=intval(count($dtBarangBgtSi));
//

$tab.="<td rowspan=5  align=center " . $bgcoloraja . ">" . $_SESSION['lang']['afdeling'] . "</td>";
$tab.="<td rowspan=5  align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tahuntanam'] . "</td>";
$tab.="<td rowspan=5  align=center " . $bgcoloraja . ">" . $_SESSION['lang']['luas'] . "</td>";
$tab.="<td align=center colspan=" . ($colsBrg + $colsBgtBrg + 4) . " " . $bgcoloraja . ">" . $optBulan[$bulan] . "-" . $tahun . "</td>";
$tab.="<td align=center colspan=" . ($colsBrgSi + $colsBgtBrgSi + 4) . " " . $bgcoloraja . ">SAMPAI DENGAN BULAN INI</td>";
$tab.="<td align=center colspan=" . ($colsthun + 2) . " rowspan=3 " . $bgcoloraja . ">BUDGET SETAHUN</td></tr>";
$tab.="<tr><td align=center colspan=" . ($colsBrg + $colsBgtBrg + 4) . " " . $bgcoloraja . ">PRESTASI KERJA</td>";
$tab.="<td align=center colspan=" . ($colsBrgSi + $colsBgtBrgSi + 4) . " " . $bgcoloraja . ">PRESTASI KERJA</td></tr>";
$tab.="<tr><td align=center colspan=" . ($colsBrg + 2) . " " . $bgcoloraja . ">AKTUAL (FISIK)</td>";
$tab.="<td align=center colspan=" . ($colsBgtBrg + 2) . " " . $bgcoloraja . ">BUDGET (FISIK)</td>";
$tab.="<td align=center colspan=" . ($colsBrgSi + 2) . " " . $bgcoloraja . ">AKTUAL (FISIK)</td>";
$tab.="<td align=center colspan=" . ($colsBgtBrgSi + 2) . " " . $bgcoloraja . ">BUDGET (FISIK)</td></tr>";
$tab.="<tr><td align=center rowspan=2 " . $bgcoloraja . ">HK/HA</td>";
$tab.="<td align=center colspan=" . ($colsBrg) . " " . $bgcoloraja . ">BAHAN PER HA</td>";
$tab.="<td align=center rowspan=2 " . $bgcoloraja . ">RP/HA</td>";
$tab.="<td align=center rowspan=2 " . $bgcoloraja . ">HK/HA</td>";
$tab.="<td align=center colspan=" . ($colsBgtBrg) . " " . $bgcoloraja . ">BAHAN PER HA</td>";
$tab.="<td align=center rowspan=2 " . $bgcoloraja . ">RP/HA</td>";
$tab.="<td align=center rowspan=2 " . $bgcoloraja . ">HK/HA</td>";
$tab.="<td align=center colspan=" . ($colsBrgSi) . " " . $bgcoloraja . ">BAHAN PER HA</td>";
$tab.="<td align=center rowspan=2 " . $bgcoloraja . ">RP/HA</td>";
$tab.="<td align=center rowspan=2 " . $bgcoloraja . ">HK/HA</td>";
$tab.="<td align=center colspan=" . ($colsBgtBrgSi) . " " . $bgcoloraja . ">BAHAN PER HA</td>";
$tab.="<td align=center rowspan=2 " . $bgcoloraja . ">RP/HA</td>";
$tab.="<td align=center rowspan=2 " . $bgcoloraja . ">HK/HA</td>";
$tab.="<td align=center colspan=" . ($colsthun) . " " . $bgcoloraja . ">BAHAN PER HA</td>";
$tab.="<td align=center rowspan=2 " . $bgcoloraja . ">RP/HA</td></tr><tr>";
if ($ard == 0) {
    $tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['dataempty'] . "</td>";
} else {
    foreach ($dtBarang as $lstBarang) {
        $tab.="<td align=center " . $bgcoloraja . ">" . $optNmBarang[$lstBarang] . "</td>";
    }
}
if ($ard2 == 0) {
    $tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['dataempty'] . "</td>";
} else {
    foreach ($dtBarangBgt as $lstBarang) {
        $tab.="<td align=center " . $bgcoloraja . ">" . $optNmBarang[$lstBarang] . "</td>";
    }
}
if ($ard3 == 0) {
    $tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['dataempty'] . "</td>";
} else {
    foreach ($dtBarangSi as $lstBarang) {
        $tab.="<td align=center " . $bgcoloraja . ">" . $optNmBarang[$lstBarang] . "</td>";
    }
}
if ($ard4 == 0) {
    $tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['dataempty'] . "</td>";
} else {
    foreach ($dtBarangBgtSi as $lstBarang) {
        $tab.="<td align=center " . $bgcoloraja . ">" . $optNmBarang[$lstBarang] . "</td>";
    }
}
if ($ard5 == 0) {
    $tab.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['dataempty'] . "</td>";
} else {
    foreach ($dtBrgSthn as $lstBarang) {
        $tab.="<td align=center " . $bgcoloraja . ">" . $optNmBarang[$lstBarang] . "</td>";
    }
}
$tab.="</tr></thead><tbody>";


//    $countx=count($dtAfd);


if (!empty($dtAfd)) {
    foreach ($dtAfd as $lsAfd) {
        foreach ($dtThnTnm as $lstThnTnm) {
            $dtLuas[$lsAfd . $lstThnTnm] = isset($dtLuas[$lsAfd . $lstThnTnm]) ? $dtLuas[$lsAfd . $lstThnTnm] : '';
            if ($dtLuas[$lsAfd . $lstThnTnm] != 0) {
                $tab.="<tr class=rowcontent>";
                $tab.="<td>" . $lsAfd . "</td>";
                $tab.="<td>" . $lstThnTnm . "</td>";
                $tab.="<td align=right>" . number_format($dtLuas[$lsAfd . $lstThnTnm], 0) . "</td>";
                @$hk1[$lsAfd . $lstThnTnm] = $dtHk[$lsAfd . $lstThnTnm] / $dtLuas[$lsAfd . $lstThnTnm];
                $tab.="<td align=right>" . number_format($hk1[$lsAfd . $lstThnTnm], 0) . "</td>";
                if (empty($dtBarang)) {
                    $tab.="<td align=right>&nbsp;</td>";
                } else {
                    foreach ($dtBarang as $lstBarang) {
                        $tab.="<td align=right>" . number_format($dtJmlhBrg[$lsAfd . $lstThnTnm][$lstBarang], 2) . "</td>";
                    }
                }
                @$rp1[$lsAfd . $lstThnTnm] = $dtJmlhRp[$lsAfd . $lstThnTnm] / $dtLuas[$lsAfd . $lstThnTnm];
                $tab.="<td align=right>" . number_format($rp1[$lsAfd . $lstThnTnm], 0) . "</td>";

                @$hk1bgt[$lsAfd . $lstThnTnm] = $dtHkBgt[$lsAfd . $lstThnTnm] / $dtLuas[$lsAfd . $lstThnTnm];
                $tab.="<td align=right>" . number_format($hk1bgt[$lsAfd . $lstThnTnm], 0) . "</td>";
                if (empty($dtBarangBgt)) {
                    $tab.="<td align=right>&nbsp;</td>";
                } else {
                    foreach ($dtBarangBgt as $lstBarang) {
                        $tab.="<td align=right>" . number_format($dtJmlhBgt[$lsAfd . $lstThnTnm][$lstBarang], 2) . "</td>";
                    }
                }
                @$rp1bgt[$lsAfd . $lstThnTnm] = $dtJmlhRpBgt[$lsAfd . $lstThnTnm] / $dtLuas[$lsAfd . $lstThnTnm];
                $tab.="<td align=right>" . number_format($rp1bgt[$lsAfd . $lstThnTnm], 0) . "</td>";
                //end
                //si
                @$hk1Si[$lsAfd . $lstThnTnm] = $dtHkSi[$lsAfd . $lstThnTnm] / $dtLuas[$lsAfd . $lstThnTnm];
                $tab.="<td align=right>" . number_format($hk1Si[$lsAfd . $lstThnTnm], 0) . "</td>";
                if (empty($dtBarangSi)) {
                    $tab.="<td align=right>&nbsp;</td>";
                } else {
                    foreach ($dtBarangSi as $lstBarang) {
                        $tab.="<td align=right>" . number_format($dtJmlhBrgSi[$lsAfd . $lstThnTnm][$lstBarang], 2) . "</td>";
                    }
                }

                @$rp1Si[$lsAfd . $lstThnTnm] = $dtJmlhRpSi[$lsAfd . $lstThnTnm] / $dtLuas[$lsAfd . $lstThnTnm];
                $tab.="<td align=right>" . number_format($rp1Si[$lsAfd . $lstThnTnm], 0) . "</td>";

                @$hk1bgtSi[$lsAfd . $lstThnTnm] = $dtHkBgtSi[$lsAfd . $lstThnTnm] / $dtLuas[$lsAfd . $lstThnTnm];
                $tab.="<td align=right>" . number_format($hk1bgtSi[$lsAfd . $lstThnTnm], 0) . "</td>";
                if (empty($dtBarangBgtSi)) {
                    $tab.="<td align=right>&nbsp;</td>";
                } else {
                    foreach ($dtBarangBgtSi as $lstBarang) {
                        $tab.="<td align=right>" . number_format($dtJmlhBgtSi[$lsAfd . $lstThnTnm][$lstBarang], 2) . "</td>";
                    }
                }
                @$rp1bgtSi[$lsAfd . $lstThnTnm] = $dtJmlhRpBgtSi[$lsAfd . $lstThnTnm] / $dtLuas[$lsAfd . $lstThnTnm];
                $tab.="<td align=right>" . number_format($rp1bgtSi[$lsAfd . $lstThnTnm], 0) . "</td>";
                //end si
                //stahun
                @$hk1Sthn[$lsAfd . $lstThnTnm] = $dtJmlHkSthn[$lsAfd . $lstThnTnm] / $dtLuas[$lsAfd . $lstThnTnm];
                $tab.="<td align=right>" . number_format($hk1Sthn[$lsAfd . $lstThnTnm], 0) . "</td>";
                if (empty($dtBrgSthn)) {
                    $tab.="<td align=right>&nbsp;</td>";
                } else {
                    foreach ($dtBrgSthn as $lstBarang) {
                        $tab.="<td align=right>" . number_format($dtJmlBrgSthn[$lsAfd . $lstThnTnm][$lstBarang], 2) . "</td>";
                    }
                }
                @$rp1Sthn[$lsAfd . $lstThnTnm] = $dtJmlRpSthn[$lsAfd . $lstThnTnm] / $dtLuas[$lsAfd . $lstThnTnm];
                $tab.="<td align=right>" . number_format($rp1Sthn[$lsAfd . $lstThnTnm], 0) . "</td>";
                $tab.="</tr>";
            }
        }
    }
}
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
        $nop_ = "kegiatan_semport" . $unit . $periode . $kegId;
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
        break;
}
?>
