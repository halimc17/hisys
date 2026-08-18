<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses = checkPostGet('proses', '');
$kebun = checkPostGet('kdUnit', '');
$periode = checkPostGet('periode', '');
$judul = checkPostGet('judul', '');
$afdeling = checkPostGet('afdId', '');

$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

// ambil tahun tanam + data dari blok BUDGET
$sApa = "select thntnm, hathnini from " . $dbname . ".bgt_blok where 	
          kodeblok like'" . $kebun . "%' and kodeblok like '" . $afdeling . "%' and tahunbudget='" . substr($periode, 0, 4) . "' and closed='1' and
          hathnini>0 order by thntnm asc";

$qApa = $owlPDO->query($sApa) or die(print " Gagal: " . PDOException::getMessage());
$qApa->setFetchMode(PDO::FETCH_ASSOC);
while ($rApa = $qApa->fetch()) {
    $dzThntnm[$rApa['thntnm']] = $rApa['thntnm'];
    $dzUmurThntnm[$rApa['thntnm']] = substr($periode, 0, 4) - $rApa['thntnm'];
    @$dzLuasBudget[$rApa['thntnm']]+=$rApa['hathnini'];
}

$tahunini = date('Y');

// ambil tahun tanam + data dari blok REAL
$sApa = "select kodeorg, tahuntanam, luasareaproduktif from " . $dbname . ".setup_blok_tahunan where 	
    kodeorg like'" . $kebun . "%' and kodeorg like '" . $afdeling . "%' and tahun='" . substr($periode, 0, 4) . "' and
    luasareaproduktif>0 order by tahuntanam asc";

$qApa = $owlPDO->query($sApa) or die(print " Gagal: " . PDOException::getMessage());
$qApa->setFetchMode(PDO::FETCH_ASSOC);
while ($rApa = $qApa->fetch()) {
    $dzThntnm[$rApa['tahuntanam']] = $rApa['tahuntanam'];
    $dzKodeorg[$rApa['kodeorg']] = $rApa['kodeorg'];
    $dzUmurThntnm[$rApa['tahuntanam']] = substr($periode, 0, 4) - $rApa['tahuntanam'];
    $dzLuasReal[$rApa['tahuntanam']]+=$rApa['luasareaproduktif'];
    $blokThntnm[$rApa['kodeorg']] = $rApa['tahuntanam'];
}
// kalo yang dipilih tahun ini, ambil dari setup blok
if (substr($periode, 0, 4) == $tahunini) {
    $sApa = "select kodeorg, tahuntanam, luasareaproduktif from " . $dbname . ".setup_blok where 	
        kodeorg like'" . $kebun . "%' and kodeorg like '" . $afdeling . "%' and
        luasareaproduktif>0 order by tahuntanam asc";

    $qApa = $owlPDO->query($sApa) or die(print " Gagal: " . PDOException::getMessage());
    $qApa->setFetchMode(PDO::FETCH_ASSOC);
    while ($rApa = $qApa->fetch()) {
        $dzThntnm[$rApa['tahuntanam']] = $rApa['tahuntanam'];
        $dzKodeorg[$rApa['kodeorg']] = $rApa['kodeorg'];
        $dzUmurThntnm[$rApa['tahuntanam']] = substr($periode, 0, 4) - $rApa['tahuntanam'];
        @$dzLuasReal[$rApa['tahuntanam']]+=$rApa['luasareaproduktif'];
        $blokThntnm[$rApa['kodeorg']] = $rApa['tahuntanam'];
    }
}

// ambil BJR BUDGET
$sApa = "select thntanam, bjr from " . $dbname . ".bgt_bjr where 	
    kodeorg like'" . $kebun . "%' and close = '1'";

$qApa = $owlPDO->query($sApa) or die(print " Gagal: " . PDOException::getMessage());
$qApa->setFetchMode(PDO::FETCH_ASSOC);
while ($rApa = $qApa->fetch()) {
    $dzBudgetBjr[$rApa['thntanam']] = $rApa['bjr'];
}

// ambil BJR REAL
$sApa = "select kodeorg, bjr from " . $dbname . ".kebun_5bjr where 	
    kodeorg like'" . $kebun . "%' and kodeorg like'" . $afdeling . "%' and tahunproduksi = '" . substr($periode, 0, 4) . "'";

$qApa = $owlPDO->query($sApa) or die(print " Gagal: " . PDOException::getMessage());
$qApa->setFetchMode(PDO::FETCH_ASSOC);
while ($rApa = $qApa->fetch()) {
    $dzRealBjr[$rApa['kodeorg']] = $rApa['bjr'];
}

$bulanperiode = substr($periode, 5, 2);
$bulanperiode2 = 0 + $bulanperiode;

// ambil janjang produksi kebun BUDGET
$sApa = "select a.tahunbudget, a.kodeblok, a.jjg01, a.jjg02, a.jjg03, a.jjg04, a.jjg05, a.jjg06, a.jjg07, a.jjg08, a.jjg09, a.jjg10, a.jjg11, a.jjg12, b.thntnm 
    from " . $dbname . ".bgt_produksi_kebun a 
    left join " . $dbname . ".bgt_blok b on a.kodeblok=b.kodeblok
    where a.kodeblok like'" . $kebun . "%' and a.kodeblok like '" . $afdeling . "%' and b.kodeblok like'" . $kebun . "%' and b.kodeblok like '" . $afdeling . "%' and
        a.tahunbudget='" . substr($periode, 0, 4) . "' and b.tahunbudget='" . substr($periode, 0, 4) . "' and 
        a.tutup = '1' and b.closed='1'";
//echo $sApa;

$qApa = $owlPDO->query($sApa) or die(print " Gagal: " . PDOException::getMessage());
$qApa->setFetchMode(PDO::FETCH_ASSOC);
while ($rApa = $qApa->fetch()) {
    // BUDGET produksi jjg bulan ini
    $dzProduksiBudgetJjgBI[$rApa['thntnm']]+=$rApa['jjg' . $bulanperiode];
    // BUDGET produksi jjg sd bulan ini
    for ($i = 1; $i <= $bulanperiode2; $i++) {
        if (strlen($i) == 1)
            $ii = '0' . $i;
        else
            $ii = $i;
        $dzProduksiBudgetJjgSBI[$rApa['thntnm']]+=$rApa['jjg' . $ii];
    }
    // BUDGET produksi jjg tahun ini
    for ($i = 1; $i <= 12; $i++) {
        if (strlen($i) == 1)
            $ii = '0' . $i;
        else
            $ii = $i;
        $dzProduksiBudgetJjgTI[$rApa['thntnm']]+=$rApa['jjg' . $ii];
    }
}

// ambil Kg SENSUS
$sApa = "select a.kodeblok, a.jumlah, a.bulan from " . $dbname . ".kebun_rencanapanen a 
    where a.kodeblok like'" . $kebun . "%' and a.kodeblok like '" . $afdeling . "%' and a.tahun = '" . substr($periode, 0, 4) . "' and a.bulan <= '" . $bulanperiode2 . "'";

$qApa = $owlPDO->query($sApa) or die(print " Gagal: " . PDOException::getMessage());
$qApa->setFetchMode(PDO::FETCH_ASSOC);
while ($rApa = $qApa->fetch()) {
    if ($rApa['bulan'] == $bulanperiode2)
        @$dzProduksiSensusBlokBI[$rApa['kodeblok']] = $rApa['jumlah'] * $dzRealBjr[$rApa['kodeblok']];
    @$dzProduksiSensusBlokSBI[$rApa['kodeblok']]+=$rApa['jumlah'] * $dzRealBjr[$rApa['kodeblok']];
}

// ambil Kg SENSUS semester
if ($bulanperiode2 < 7) { // semester I
    $sApa = "select a.kodeblok, a.jumlah, a.bulan from " . $dbname . ".kebun_rencanapanen a 
        where a.kodeblok like'" . $kebun . "%' and a.kodeblok like '" . $afdeling . "%' and a.tahun = '" . substr($periode, 0, 4) . "' and a.bulan < '7'";
} else { // semester II
    $sApa = "select a.kodeblok, a.jumlah, a.bulan from " . $dbname . ".kebun_rencanapanen a 
        where a.kodeblok like'" . $kebun . "%' and a.kodeblok like '" . $afdeling . "%' and a.tahun = '" . substr($periode, 0, 4) . "' and a.bulan >= '7'";
}

$qApa = $owlPDO->query($sApa) or die(print " Gagal: " . PDOException::getMessage());
$qApa->setFetchMode(PDO::FETCH_ASSOC);
while ($rApa = $qApa->fetch()) {
    @$dzProduksiSemesterBlok[$rApa['kodeblok']]+=$rApa['jumlah'] * $dzRealBjr[$rApa['kodeblok']];
}

// ambil Kg REAL
$sApa = "select a.blok, a.kgwb, b.tanggal from " . $dbname . ".kebun_spbdt a 
    left join " . $dbname . ".kebun_spbht b on a.nospb=b.nospb
    where a.blok like'" . $kebun . "%' and a.blok like '" . $afdeling . "%' and substr(b.tanggal,1,7) <= '" . $periode . "%' and substr(b.tanggal,1,4) = '" . substr($periode, 0, 4) . "'";

$qApa = $owlPDO->query($sApa) or die(print " Gagal: " . PDOException::getMessage());
$qApa->setFetchMode(PDO::FETCH_ASSOC);
while ($rApa = $qApa->fetch()) {
    if (substr($rApa['tanggal'], 5, 2) == $bulanperiode)
        @$dzProduksiRealBlokBI[$rApa['blok']]+=$rApa['kgwb'];
    @$dzProduksiRealBlokSBI[$rApa['blok']]+=$rApa['kgwb'];
}

$periodetahunlalu = (substr($periode, 0, 4) - 1) . '-' . (substr($periode, 5, 2));

// ambil Kg REAL tahun lalu
$sApa = "select a.blok, a.kgwb, b.tanggal from " . $dbname . ".kebun_spbdt a 
    left join " . $dbname . ".kebun_spbht b on a.nospb=b.nospb
    where a.blok like'" . $kebun . "%' and a.blok like '" . $afdeling . "%' and substr(b.tanggal,1,4) = '" . substr($periodetahunlalu, 0, 4) . "'";

$qApa = $owlPDO->query($sApa) or die(print " Gagal: " . PDOException::getMessage());
$qApa->setFetchMode(PDO::FETCH_ASSOC);
while ($rApa = $qApa->fetch()) {
    if (substr($rApa['tanggal'], 5, 2) <= $bulanperiode)
        @$dzProduksiRealBlokSBITL[$rApa['blok']]+=$rApa['kgwb'];
    @$dzProduksiRealBlokTL[$rApa['blok']]+=$rApa['kgwb'];
}

// grupkan Kg SENSUS by tahun tanam
if(count($dzKodeorg)>0){
    foreach ($dzKodeorg as $listKo) {
        @$dzProduksiSensusKgBI[$blokThntnm[$listKo]]+=$dzProduksiSensusBlokBI[$listKo];
        @$dzProduksiSensusKgSBI[$blokThntnm[$listKo]]+=$dzProduksiSensusBlokSBI[$listKo];

        @$dzProduksiRealKgBI[$blokThntnm[$listKo]]+=$dzProduksiRealBlokBI[$listKo];
        @$dzProduksiRealKgSBI[$blokThntnm[$listKo]]+=$dzProduksiRealBlokSBI[$listKo];

        @$dzProduksiRealKgSBITL[$blokThntnm[$listKo]]+=$dzProduksiRealBlokSBITL[$listKo];

        @$dzProduksiSemesterKg[$blokThntnm[$listKo]]+=$dzProduksiSemesterBlok[$listKo];
        @$dzProduksiRealKgTL[$blokThntnm[$listKo]]+=$dzProduksiRealBlokTL[$listKo];
    }
}
foreach ($dzThntnm as $listTt) {
    @$dzProduksiBudgetKgBI[$listTt] = $dzProduksiBudgetJjgBI[$listTt] * $dzBudgetBjr[$listTt] / 1000;
    @$dzProduksiBudgetKgSBI[$listTt] = $dzProduksiBudgetJjgSBI[$listTt] * $dzBudgetBjr[$listTt] / 1000;
    @$dzProduksiBudgetKgTI[$listTt] = $dzProduksiBudgetJjgTI[$listTt] * $dzBudgetBjr[$listTt] / 1000;

    @$dzProduksiSensusKgBI[$listTt] = $dzProduksiSensusKgBI[$listTt] / 1000;
    @$dzProduksiSensusKgSBI[$listTt] = $dzProduksiSensusKgSBI[$listTt] / 1000;

    @$dzProduksiRealKgBI[$listTt] = $dzProduksiRealKgBI[$listTt] / 1000;
    @$dzProduksiRealKgSBI[$listTt] = $dzProduksiRealKgSBI[$listTt] / 1000;

    @$dzProduksiRealKgSBITL[$listTt] = $dzProduksiRealKgSBITL[$listTt] / 1000;

    @$dzProduksiSemesterKg[$listTt] = $dzProduksiSemesterKg[$listTt] / 1000;
    @$dzProduksiRealKgTL[$listTt] = $dzProduksiRealKgTL[$listTt] / 1000;

    // hitung-hitung varian
    @$snVsRealibi[$listTt] = $dzProduksiRealKgBI[$listTt] / $dzProduksiSensusKgBI[$listTt] * 100;
    @$snVsRealisbi[$listTt] = $dzProduksiRealKgSBI[$listTt] / $dzProduksiSensusKgSBI[$listTt] * 100;
    @$angVsRealibi[$listTt] = $dzProduksiRealKgBI[$listTt] / $dzProduksiBudgetKgBI[$listTt] * 100;
    @$angVsRealisbi[$listTt] = $dzProduksiRealKgSBI[$listTt] / $dzProduksiBudgetKgSBI[$listTt] * 100;

    // susun warna
//    if(@number_format($dzLuasReal[$listTt],2)<@number_format($dzLuasBudget[$listTt],2))$warnaluas[$listTt]=" bgcolor=pink"; else $warnaluas[$listTt]="";    
}

$brdr = 0;
$bgcoloraja = '';
if ($proses == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE align=center";
    $brdr = 1;
    $tab.="<table>
        <tr><td colspan=8 align=left><b>" . $_GET['judul'] . "</b></td><td colspan=3 align=right><b>" . $_SESSION['lang']['bulan'] . " : " . substr(tanggalnormal($periode), 1, 7) . "</b></td></tr>
        <tr><td colspan=8 align=left>" . $_SESSION['lang']['unit'] . " : " . $optNmOrg[$kebun] . " </td></tr>";
    if ($afdeling != '') {
        $tab.="<tr><td colspan=8 align=left>" . $_SESSION['lang']['afdeling'] . " : " . $optNmOrg[$afdeling] . " </td></tr>";
    }
    $tab.="<tr><td colspan=8 align=left>&nbsp;</td></tr>
        </table>";
}

$tab.="<table cellspacing=1 border=" . $brdr . " class=sortable>
    <thead class=rowheader>
    <tr>
    <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tahuntanam'] . "</td>
    <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['umur'] . " (" . $_SESSION['lang']['tahun'] . ")</td>
    <td " . $bgcoloraja . " colspan=2>" . $_SESSION['lang']['luas'] . " (Ha)</td>";
$tab.="<td " . $bgcoloraja . " colspan=3>" . $_SESSION['lang']['anggaran'] . " (TON)</td>";
$tab.="<td " . $bgcoloraja . " colspan=2>" . $_SESSION['lang']['sensus'] . " (TON)</td>";
$tab.="<td " . $bgcoloraja . " colspan=2>" . $_SESSION['lang']['realisasi'] . " (TON)</td>";
$tab.="<td " . $bgcoloraja . " colspan=2>% VARIAN REAL VS " . strtoupper($_SESSION['lang']['sensus']) . "</td>";
$tab.="<td " . $bgcoloraja . " colspan=2>% VARIAN REAL VS BUDGET</td>";
$tab.="<td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['sbi'] . " (" . $_SESSION['lang']['tahunlalu'] . ")</td><td " . $bgcoloraja . " rowspan=2>" . strtoupper($_SESSION['lang']['sensus']) . "  SM-I/II</td>";
$tab.="<td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tahunlalu'] . "</td><td " . $bgcoloraja . " rowspan=2>Potency " . $_SESSION['lang']['produksi'] . "</td></tr>";
$tab.="<tr><td " . $bgcoloraja . " >" . $_SESSION['lang']['anggaran'] . "</td><td " . $bgcoloraja . " >REAL</td><td " . $bgcoloraja . " >" . $_SESSION['lang']['setahun'] . "</td><td " . $bgcoloraja . " >" . $_SESSION['lang']['bi'] . "</td>
    <td " . $bgcoloraja . " >" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . " >" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . " >" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . " >" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . " >" . $_SESSION['lang']['sbi'] . "</td>
    <td " . $bgcoloraja . " >" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . " >" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . " >" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . " >" . $_SESSION['lang']['sbi'] . "</td></tr>";
$tab.="</thead>
<tbody>";
foreach ($dzThntnm as $lstThnTnm) {
    $tab.="<tr class=rowcontent><td align=center>" . $lstThnTnm . "</td>";
    $tab.="<td align=right>" . @number_format($dzUmurThntnm[$lstThnTnm]) . "</td>";
    $tab.="<td align=right>" . @number_format($dzLuasBudget[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right" . @$warnaluas[$lstThnTnm] . ">" . @number_format($dzLuasReal[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzProduksiBudgetKgTI[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzProduksiBudgetKgBI[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzProduksiBudgetKgSBI[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzProduksiSensusKgBI[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzProduksiSensusKgSBI[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzProduksiRealKgBI[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzProduksiRealKgSBI[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($snVsRealibi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($snVsRealisbi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($angVsRealibi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($angVsRealisbi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzProduksiRealKgSBITL[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzProduksiSemesterKg[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzProduksiRealKgTL[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($potProd[$lstThnTnm], 2) . "</td>";
    $tab.="</tr>";
    @$totLuasBudget+=$dzLuasBudget[$lstThnTnm];
    @$totLuasReal+=$dzLuasReal[$lstThnTnm];
    @$totProduksiBudgetKgTI+=$dzProduksiBudgetKgTI[$lstThnTnm];
    @$totProduksiBudgetKgBI+=$dzProduksiBudgetKgBI[$lstThnTnm];
    @$totProduksiBudgetKgSBI+=$dzProduksiBudgetKgSBI[$lstThnTnm];
    @$totProduksiSensusKgBI+=$dzProduksiSensusKgBI[$lstThnTnm];
    @$totProduksiSensusKgSBI+=$dzProduksiSensusKgSBI[$lstThnTnm];
    @$totProduksiRealKgBI+=$dzProduksiRealKgBI[$lstThnTnm];
    @$totProduksiRealKgSBI+=$dzProduksiRealKgSBI[$lstThnTnm];
    @$totsnVsRealibi = $totProduksiRealKgBI / $totProduksiSensusKgBI * 100;
    @$totsnVsRealisbi = $totProduksiRealKgSBI / $totProduksiSensusKgSBI * 100;
    @$totangVsRealibi = $totProduksiRealKgBI / $totProduksiBudgetKgBI * 100;
    @$totangVsRealisbi = $totProduksiRealKgSBI / $totProduksiBudgetKgSBI * 100;
//    $totsnVsRealibi+=$snVsRealibi[$lstThnTnm];
//    $totsnVsRealisbi+=$snVsRealisbi[$lstThnTnm];
//    $totangVsRealibi+=$angVsRealibi[$lstThnTnm];
//    $totangVsRealisbi+=$angVsRealisbi[$lstThnTnm]; 
    @$totProduksiRealKgSBITL+=$dzProduksiRealKgSBITL[$lstThnTnm];
    @$totProduksiSemesterKg+=$dzProduksiSemesterKg[$lstThnTnm];
    @$totProduksiRealKgTL+=$dzProduksiRealKgTL[$lstThnTnm];
    @$totpotProd+=$potProd[$lstThnTnm];
}
$tab.="<tr class=rowcontent><td colspan=2>" . $_SESSION['lang']['total'] . "</td>";
$tab.="<td align=right>" . @number_format($totLuasBudget, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totLuasReal, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totProduksiBudgetKgTI, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totProduksiBudgetKgBI, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totProduksiBudgetKgSBI, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totProduksiSensusKgBI, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totProduksiSensusKgSBI, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totProduksiRealKgBI, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totProduksiRealKgSBI, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totsnVsRealibi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totsnVsRealisbi, 2) . "</td>";

$tab.="<td align=right>" . @number_format($totangVsRealibi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totangVsRealisbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totProduksiRealKgSBITL, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totProduksiSemesterKg, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totProduksiRealKgTL, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totpotProd, 2) . "</td>";
$tab.="</tr>";
$tab.="</tbody></table>";


switch ($proses) {
    case'preview':
        echo $tab;
        break;
    case'excel':
        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        $nop_ = "LBM_" . $judul . "_" . $periode . "_" . $kebun . "_" . $afdeling . "_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
    window.location='tempExcel/" . $nop_ . ".xls.gz';
    </script>";
        break;
    case'pdf':

        class PDF extends FPDF {

            function Header() {
                global $periode, $judul;
                global $afdeling;
                global $kebun;
                global $optNmOrg;
                global $dbname;
                global $afdId;
                global $pdf;

                $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
                $height = 5;
                $this->SetFont('Arial', 'B', 8);
                $this->Cell($width, $height, strtoupper($judul), 0, 1, 'L');
                $this->Cell(790, $height, $_SESSION['lang']['bulan'] . ' : ' . substr(tanggalnormal($periode), 1, 7), 0, 1, 'R');
                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $this->Cell($width, $height, $_SESSION['lang']['unit'] . ' : ' . $optNmOrg[$kebun], 0, 1, 'L');
                if ($afdeling != '') {
                    $tinggiAkr = $this->GetY();
                    $ksamping = $this->GetX();
                    $this->SetY($tinggiAkr + 20);
                    $this->SetX($ksamping);
                    $this->Cell($width, $height, $_SESSION['lang']['afdeling'] . ' : ' . $optNmOrg[$afdeling], 0, 1, 'L');
                }
                $this->Cell(790, $height, ' ', 0, 1, 'R');
                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $height = 15;
                $this->SetFillColor(220, 220, 220);
                $this->SetFont('Arial', 'B', 7);
                $this->Cell(25, $height, " ", 'TLR', 0, 'C', 1);
                $this->Cell(50, $height, '', 'TLR', 0, 'C', 1);
                $this->Cell(60, $height, " ", 'TLR', 0, 'C', 1);
                $this->Cell(150, $height, " ", 'TLR', 0, 'C', 1);
                $this->Cell(100, $height, " ", 'TLR', 0, 'C', 1);
                $this->Cell(100, $height, " ", 'TLR', 0, 'C', 1);
                $this->Cell(60, $height, "% VARIAN", 'TLR', 0, 'C', 1);
                $this->Cell(70, $height, "% VARIAN", 'TLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TLR', 0, 'C', 1);
                $this->Cell(55, $height, " ", 'TLR', 0, 'C', 1);
                $this->Cell(55, $height, '', 'TLR', 0, 'C', 1);
                $this->Cell(40, $height, " ", 'TLR', 1, 'C', 1);

                $this->Cell(25, $height, $_SESSION['lang']['tahun'], 'LR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['umur'], 'LR', 0, 'C', 1);
                $this->Cell(60, $height, $_SESSION['lang']['luas'] . " (Ha)", 'LR', 0, 'C', 1);
                $this->Cell(150, $height, $_SESSION['lang']['anggaran'] . " (TON)", 'LR', 0, 'C', 1);
                $this->Cell(100, $height, "CENSUS (TON)", 'LR', 0, 'C', 1);
                $this->Cell(100, $height, $_SESSION['lang']['realisasi'] . " (TON)", 'LR', 0, 'C', 1);
                $this->Cell(60, $height, "REAL VS", 'LR', 0, 'C', 1);
                $this->Cell(70, $height, "REAL VS", 'LR', 0, 'C', 1);
                $this->Cell(30, $height, "(" . $_SESSION['lang']['tahunlalu'] . ")", 'LR', 0, 'C', 1);
                $this->Cell(55, $height, "CENSUS", 'LR', 0, 'C', 1);
                $this->Cell(55, $height, $_SESSION['lang']['tahunlalu'], 'LR', 0, 'C', 1);
                $this->Cell(40, $height, "POTENCY", 'LR', 1, 'C', 1);

                $this->Cell(25, $height, $_SESSION['lang']['tanam'], 'LR', 0, 'C', 1);
                $this->Cell(50, $height, '', 'LR', 0, 'C', 1);
                $this->Cell(60, $height, " ", 'LR', 0, 'C', 1);
                $this->Cell(150, $height, " ", 'LR', 0, 'C', 1);
                $this->Cell(100, $height, " ", 'LR', 0, 'C', 1);
                $this->Cell(100, $height, " ", 'LR', 0, 'C', 1);
                $this->Cell(60, $height, "CNS", 'LR', 0, 'C', 1);
                $this->Cell(70, $height, "BUDGET", 'LR', 0, 'C', 1);
                $this->Cell(30, $height, '', 'LR', 0, 'C', 1);
                $this->Cell(55, $height, "SM-I/II ", 'LR', 0, 'C', 1);
                $this->Cell(55, $height, "", 'LR', 0, 'C', 1);
                $this->Cell(40, $height, $_SESSION['lang']['produksi'], 'LR', 1, 'C', 1);

                $this->Cell(25, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(50, $height, " ", 'BLR', 0, 'C', 1);
                $this->SetFont('Arial', 'B', 6);
                $this->Cell(30, $height, "BUDGET", 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, "REAL", 'TBLR', 0, 'C', 1);

                $this->Cell(50, $height, $_SESSION['lang']['setahun'], 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(35, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(35, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->SetFont('Arial', 'B', 7);
                $this->Cell(30, $height, "", 'BLR', 0, 'C', 1); #lalu
                $this->Cell(55, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(55, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(40, $height, " ", 'BLR', 1, 'C', 1);
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
        $height = 20;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 6);
        foreach ($dzThntnm as $lstThnTnm) {
            $pdf->Cell(25, $height, $lstThnTnm, 1, 0, 'C', 1);
            $pdf->Cell(50, $height, @number_format($dzUmurThntnm[$lstThnTnm]), 1, 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dzLuasBudget[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dzLuasReal[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($dzProduksiBudgetKgTI[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($dzProduksiBudgetKgBI[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($dzProduksiBudgetKgSBI[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($dzProduksiSensusKgBI[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($dzProduksiSensusKgSBI[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($dzProduksiRealKgBI[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($dzProduksiRealKgSBI[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($snVsRealibi[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($snVsRealisbi[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(35, $height, @number_format($angVsRealibi[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(35, $height, @number_format($angVsRealisbi[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dzProduksiRealKgSBITL[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(55, $height, @number_format($dzProduksiSemesterKg[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(55, $height, @number_format($dzProduksiRealKgTL[$lstThnTnm], 2), 1, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($potProd[$lstThnTnm], 2), 1, 1, 'R', 1);
        }

        $pdf->Cell(75, $height, $_SESSION['lang']['total'], 1, 0, 'L', 1);

        $pdf->Cell(30, $height, @number_format($totLuasBudget, 2), 1, 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totLuasReal, 2), 1, 0, 'R', 1);
        $pdf->Cell(50, $height, @number_format($totProduksiBudgetKgTI, 2), 1, 0, 'R', 1);
        $pdf->Cell(50, $height, @number_format($totProduksiBudgetKgBI, 2), 1, 0, 'R', 1);
        $pdf->Cell(50, $height, @number_format($totProduksiBudgetKgSBI, 2), 1, 0, 'R', 1);
        $pdf->Cell(50, $height, @number_format($totProduksiSensusKgBI, 2), 1, 0, 'R', 1);
        $pdf->Cell(50, $height, @number_format($totProduksiSensusKgSBI, 2), 1, 0, 'R', 1);
        $pdf->Cell(50, $height, @number_format($totProduksiRealKgBI, 2), 1, 0, 'R', 1);
        $pdf->Cell(50, $height, @number_format($totProduksiRealKgSBI, 2), 1, 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totsnVsRealibi, 2), 1, 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totsnVsRealisbi, 2), 1, 0, 'R', 1);
        $pdf->Cell(35, $height, @number_format($totangVsRealibi, 2), 1, 0, 'R', 1);
        $pdf->Cell(35, $height, @number_format($totangVsRealisbi, 2), 1, 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totProduksiRealKgSBITL, 2), 1, 0, 'R', 1);
        $pdf->Cell(55, $height, @number_format($totProduksiSemesterKg, 2), 1, 0, 'R', 1);
        $pdf->Cell(55, $height, @number_format($totProduksiRealKgTL, 2), 1, 0, 'R', 1);
        $pdf->Cell(40, $height, @number_format($totpotProd, 2), 1, 1, 'R', 1);
        $pdf->Output();
        break;
    default:
        break;
}
?>