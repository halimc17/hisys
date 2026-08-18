<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


//$arr="##klmpkBrg##kdUnit##periode##lokasi##statId##purId";
$sKlmpk = "select kode,kelompok from " . $dbname . ".log_5klbarang order by kode";

$qKlmpk = $owlPDO->query($sKlmpk) or die(print " Gagal: " . PDOException::getMessage());
$qKlmpk->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmpk = $qKlmpk->fetch()) {
    $rKelompok[$rKlmpk['kode']] = $rKlmpk['kelompok'];
}
$optNmOrang = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optInduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');


$proses = checkPostGet('proses', '');
$kdUnit = checkPostGet('kdUnit', '');
$periode = checkPostGet('periode', '');
$judul = checkPostGet('judul', '');
$afdId = checkPostGet('afdId', '');


$unitId = $_SESSION['lang']['all'];
$nmPrshn = "Holding";
$purchaser = $_SESSION['lang']['all'];
if ($periode == '') {
    exit("Error: " . $_SESSION['lang']['periode'] . " required");
}
if ($kdUnit != '') {
    $unitId = $optNmOrg[$kdUnit];
} else {
    exit("Error:" . $_SESSION['lang']['unit'] . " required");
}
$thn = explode("-", $periode);
//get tahun tanam
$sthnTnm = "select distinct tahuntanam from " . $dbname . ".kebun_prestasi_vw 
          where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)='" . $periode . "'  order by tahuntanam asc";
if ($afdId != '') {
    $sthnTnm = "select distinct tahuntanam from " . $dbname . ".kebun_prestasi_vw 
             where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)='" . $periode . "'  order by tahuntanam asc";
}

$qthnTnm = $owlPDO->query($sthnTnm) or die(print " Gagal: " . PDOException::getMessage());
$qthnTnm->setFetchMode(PDO::FETCH_ASSOC);
while ($rthnTnm = $qthnTnm->fetch()) {
    if ($rthnTnm['tahuntanam'] != '') {
        $dtThnTnm[] = $rthnTnm['tahuntanam'];
    }
}


@$varCek = count($dtThnTnm);
if ($varCek < 1) {
    exit("Error:Data Kosong");
}


$sJmlHk = "select karyawanid,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)='" . $periode . "'";
if ($afdId != '') {
    $sJmlHk = "select karyawanid,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
             where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)='" . $periode . "'";
}

$qJmlHk = $owlPDO->query($sJmlHk) or die(print " Gagal: " . PDOException::getMessage());
$qJmlHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rJmlHk = $qJmlHk->fetch()) {
    @$dtJmlHk[$rJmlHk['tahuntanam']]+=1;
}

$sJmlHk = "select karyawanid,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7) between '" . $thn[0] . "-01' and '" . $periode . "'";
if ($afdId != '') {
    $sJmlHk = "select karyawanid,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7) between '" . $thn[0] . "-01' and '" . $periode . "'";
}

$qJmlHk = $owlPDO->query($sJmlHk) or die(print " Gagal: " . PDOException::getMessage());
$qJmlHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rJmlHk = $qJmlHk->fetch()) {
    @$dtJmlHkSbi[$rJmlHk['tahuntanam']]+=1;
}

//get jumlah hk siap borong bi
$sJmlHk = "select karyawanid,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)='" . $periode . "' and hasilkerjakg>norma";
if ($afdId != '') {
    $sJmlHk = "select karyawanid,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)='" . $periode . "' and hasilkerjakg>norma";
}

$qJmlHk = $owlPDO->query($sJmlHk) or die(print " Gagal: " . PDOException::getMessage());
$qJmlHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rJmlHk = $qJmlHk->fetch()) {
    @$dtJmlHkSiapBrg[$rJmlHk['tahuntanam']]+=1;
}

//get jumlah hk siap borong sbi
$sJmlHk = "select karyawanid,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' 
         and hasilkerjakg>norma";
if ($afdId != '') {
    $sJmlHk = "select karyawanid,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' 
         and hasilkerjakg>norma";
}

$qJmlHk = $owlPDO->query($sJmlHk) or die(print " Gagal: " . PDOException::getMessage());
$qJmlHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rJmlHk = $qJmlHk->fetch()) {
    @$dtJmlHkSiapBrgSbi[$rJmlHk['tahuntanam']]+=1;
}

foreach ($dtThnTnm as $lsThnTnm) {
    @$persenBi[$lsThnTnm] = $dtJmlHkSiapBrg[$lsThnTnm] / $dtJmlHk[$lsThnTnm] * 100;
    @$persensBi[$lsThnTnm] = $dtJmlHkSiapBrgSbi[$lsThnTnm] / $dtJmlHkSbi[$lsThnTnm] * 100;
}

//get jumlah Target Basis
$sJmlHk = "select distinct sum(norma) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)='" . $periode . "'  group by tahuntanam,norma";
if ($afdId != '') {
    $sJmlHk = "select distinct sum(norma) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)='" . $periode . "'  group by tahuntanam,norma";
}

$qJmlHk = $owlPDO->query($sJmlHk) or die(print " Gagal: " . PDOException::getMessage());
$qJmlHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rJmlHk = $qJmlHk->fetch()) {
    @$dtJmlHkNorma[$rJmlHk['tahuntanam']]+=$rJmlHk['totJmlhHk'];
}

//get jumlah Target Basis sbi
$sJmlHk = "select distinct sum(norma) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' 
         group by tahuntanam,norma";
if ($afdId != '') {
    $sJmlHk = "select distinct sum(norma) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' 
         group by tahuntanam,norma";
}

$qJmlHk = $owlPDO->query($sJmlHk) or die(print " Gagal: " . PDOException::getMessage());
$qJmlHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rJmlHk = $qJmlHk->fetch()) {
    @$dtJmlHkNormaSbi[$rJmlHk['tahuntanam']]+=$rJmlHk['totJmlhHk'];
}

//get kg bi
$sJmlHk = "select sum(hasilkerjakg) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)='" . $periode . "'  group by tahuntanam";
/*
$sJmlHk = "select distinct sum(hasilkerjakg) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)='" . $periode . "'  group by tahuntanam,hasilkerjakg";
*/		 
if ($afdId != '') {
    $sJmlHk = "select sum(hasilkerjakg) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)='" . $periode . "'  group by tahuntanam";
	/*
	$sJmlHk = "select distinct sum(hasilkerjakg) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)='" . $periode . "'  group by tahuntanam,hasilkerjakg";
	*/	 
}
$qJmlHk = $owlPDO->query($sJmlHk) or die(print " Gagal: " . PDOException::getMessage());
$qJmlHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rJmlHk = $qJmlHk->fetch()) {
    @$dtJmlkgbi[$rJmlHk['tahuntanam']]+=$rJmlHk['totJmlhHk'];
}

//get kg sbi
$sJmlHk = "select sum(hasilkerjakg) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' 
         group by tahuntanam";
/*
$sJmlHk = "select distinct sum(hasilkerjakg) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' 
         group by tahuntanam,hasilkerjakg";
*/		 
if ($afdId != '') {
    $sJmlHk = "select sum(hasilkerjakg) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' 
         group by tahuntanam";
	/*
	$sJmlHk = "select distinct sum(hasilkerjakg) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' 
         group by tahuntanam,hasilkerjakg";
	*/	 
}
$qJmlHk = $owlPDO->query($sJmlHk) or die(print " Gagal: " . PDOException::getMessage());
$qJmlHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rJmlHk = $qJmlHk->fetch()) {
    @$dtJmlkgsbi[$rJmlHk['tahuntanam']]+=$rJmlHk['totJmlhHk'];
}
//indra

//get jumlah Lebih Borong Sbi
$sJmlHk = "select distinct sum(hasilkerja-norma) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' 
         group by tahuntanam,norma";
if ($afdId != '') {
    $sJmlHk = "select distinct sum(hasilkerja-norma) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' 
         group by tahuntanam,norma";
}

$qJmlHk = $owlPDO->query($sJmlHk) or die(print " Gagal: " . PDOException::getMessage());
$qJmlHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rJmlHk = $qJmlHk->fetch()) {
    @$dtJmlLbhBrgSbi[$rJmlHk['tahuntanam']]+=$rJmlHk['totJmlhHk'];
}

//get jumlah Lebih Borong Bi
$sJmlHk = "select distinct sum(hasilkerja-norma) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,4)='" . $kdUnit . "' and substr(tanggal,1,7)='" . $periode . "' 
         group by tahuntanam,norma";
if ($afdId != '') {
    $sJmlHk = "select distinct sum(hasilkerja-norma) as totJmlhHk,tahuntanam from " . $dbname . ".kebun_prestasi_vw 
         where substr(kodeorg,1,6)='" . $afdId . "' and substr(tanggal,1,7)='" . $periode . "' 
         group by tahuntanam,norma";
}

$qJmlHk = $owlPDO->query($sJmlHk) or die(print " Gagal: " . PDOException::getMessage());
$qJmlHk->setFetchMode(PDO::FETCH_ASSOC);
while ($rJmlHk = $qJmlHk->fetch()) {
    @$dtJmlLbhBrg[$rJmlHk['tahuntanam']]+=$rJmlHk['totJmlhHk'];
}
foreach ($dtThnTnm as $lsThnTnm) {
    @$totBi[$lsThnTnm] = $dtJmlHkNorma[$lsThnTnm] + $dtJmlLbhBrg[$lsThnTnm];
    @$totSbi[$lsThnTnm] = $dtJmlHkNormaSbi[$lsThnTnm] + $dtJmlLbhBrgSbi[$lsThnTnm];
}

//jjg dikirim bi
$sJjg = "select distinct sum(jjg) jmlhJjg,b.tahuntanam from " . $dbname . ".kebun_spb_vw a left join " . $dbname . ".setup_blok b 
       on a.blok=b.kodeorg where a.kodeorg='" . $kdUnit . "' and substr(tanggal,1,7)='" . $periode . "' group by b.tahuntanam";

$qJjg = $owlPDO->query($sJjg) or die(print " Gagal: " . PDOException::getMessage());
$qJjg->setFetchMode(PDO::FETCH_ASSOC);
while ($rJgg = $qJjg->fetch()) {
    @$jumJjg[$rJgg['tahuntanam']]+=$rJgg['jmlhJjg'];
}

//jjg dikirim sbi
$sJjg = "select distinct sum(jjg) jmlhJjg,b.tahuntanam from " . $dbname . ".kebun_spb_vw a left join " . $dbname . ".setup_blok b 
       on a.blok=b.kodeorg where a.kodeorg='" . $kdUnit . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' group by b.tahuntanam";
if ($afdId != '') {
    $sJjg = "select distinct sum(jjg) jmlhJjg,b.tahuntanam from " . $dbname . ".kebun_spb_vw a left join " . $dbname . ".setup_blok b 
       on a.blok=b.kodeorg where a.blok like '" . $afdId . "%' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' group by b.tahuntanam";
}

$qJjg = $owlPDO->query($sJjg) or die(print " Gagal: " . PDOException::getMessage());
$qJjg->setFetchMode(PDO::FETCH_ASSOC);
while ($rJgg = $qJjg->fetch()) {
    @$jumJjgSbi[$rJgg['tahuntanam']]+=$rJgg['jmlhJjg'];
}

//kg dikirim bi
$sJjg = "select distinct sum(kgwb) jmlhJjg,b.tahuntanam from " . $dbname . ".kebun_spb_vw a left join " . $dbname . ".setup_blok b 
       on a.blok=b.kodeorg where a.kodeorg='" . $kdUnit . "' and substr(tanggal,1,7)='" . $periode . "' group by b.tahuntanam";
$qJjg = $owlPDO->query($sJjg) or die(print " Gagal: " . PDOException::getMessage());
$qJjg->setFetchMode(PDO::FETCH_ASSOC);
while ($rJgg = $qJjg->fetch()) {
    @$jumKgBi[$rJgg['tahuntanam']]+=$rJgg['jmlhJjg'];
}
//kg dikirim sbi
$sJjg = "select distinct sum(kgwb) jmlhJjg,b.tahuntanam from " . $dbname . ".kebun_spb_vw a left join " . $dbname . ".setup_blok b 
       on a.blok=b.kodeorg where a.kodeorg='" . $kdUnit . "' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' group by b.tahuntanam";
if ($afdId != '') {
    $sJjg = "select distinct sum(kgwb) jmlhJjg,b.tahuntanam from " . $dbname . ".kebun_spb_vw a left join " . $dbname . ".setup_blok b 
       on a.blok=b.kodeorg where a.blok like '" . $afdId . "%' and substr(tanggal,1,7)  between '" . $thn[0] . "-01' and '" . $periode . "' group by b.tahuntanam";
}

$qJjg = $owlPDO->query($sJjg) or die(print " Gagal: " . PDOException::getMessage());
$qJjg->setFetchMode(PDO::FETCH_ASSOC);
while ($rJgg = $qJjg->fetch()) {
    @$jumKgSbi[$rJgg['tahuntanam']]+=$rJgg['jmlhJjg'];
}

//avg bjr
if ($thn[1] < 7) {
    $sAvgBjr = "select distinct avg(bjr) as rtBjr,b.tahuntanam from " . $dbname . ".kebun_rencanapanen_vw 
              a left join " . $dbname . ".setup_blok b on a.kodeblok=b.kodeorg where bulan<7 and tahun='" . $thn[0] . "' group by b.tahuntanam";
} else {
    $sAvgBjr = "select distinct avg(bjr) as rtBjr,b.tahuntanam from " . $dbname . ".kebun_rencanapanen_vw a left join " . $dbname . ".setup_blok b on a.kodeblok=b.kodeorg
              where bulan>6 and tahun='" . $thn[0] . "' and tahun='" . $thn[0] . "' group by b.tahuntanam";
}

$qAvgBjr = $owlPDO->query($sAvgBjr) or die(print " Gagal: " . PDOException::getMessage());
$qAvgBjr->setFetchMode(PDO::FETCH_ASSOC);
while ($rAvgBjr = $qAvgBjr->fetch()) {
    @$dtAvgBjr[$rAvgBjr['tahuntanam']]+=$rAvgBjr['rtBjr'];
}
$jmlHari = cal_days_in_month(CAL_GREGORIAN, $thn[1], $thn[0]);
$varCek = count($dtThnTnm);
if ($varCek < 1) {
    exit("Error:Data Kosong");
}
$brdr = 0;
$bgcoloraja = '';
@$cols = count($dataAfd) * 3;
if ($proses == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE align=center";
    $brdr = 1;
    $tab.="
    <table>
    <tr><td colspan=8 align=left><b>06. OUTPUT " . strtoupper($_SESSION['lang']['panen']) . "</b></td><td colspan=8 align=right><b>" . $_SESSION['lang']['bulan'] . " : " . substr(tanggalnormal($periode), 1, 7) . "</b></td></tr>
    <tr><td colspan=8 align=left>" . $_SESSION['lang']['unit'] . " : " . $optNmOrg[$kdUnit] . " </td></tr>";
    if ($afdId != '') {
        $tab.="<tr><td colspan=8 align=left>" . $_SESSION['lang']['afdeling'] . " : " . $optNmOrg[$afdId] . " </td></tr>";
    }
    $tab.="<tr><td colspan=8 align=left>&nbsp;</td></tr>
    </table>";
}
$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
$tab.="<tr><td rowspan=3 " . $bgcoloraja . ">" . $_SESSION['lang']['tahuntanam'] . "</td>";
$tab.="<td colspan=6 " . $bgcoloraja . ">" . $_SESSION['lang']['jumlahhk'] . "</td>";
$tab.="<td colspan=6 " . $bgcoloraja . ">" . $_SESSION['lang']['jumlah'] . " (JJG)</td>";
$tab.="<td colspan=2 rowspan=2 " . $bgcoloraja . ">" . $_SESSION['lang']['janjang'] . " " . $_SESSION['lang']['kirim'] . "</td>";
$tab.="<td colspan=2 rowspan=2 " . $bgcoloraja . ">Total  " . $_SESSION['lang']['produksi'] . " (KG)</td>";
$tab.="<td colspan=2 rowspan=2 " . $bgcoloraja . ">" . $_SESSION['lang']['tbs'] . " " . $_SESSION['lang']['kirim'] . " (KG)</td>";
$tab.="<td colspan=2 rowspan=2 " . $bgcoloraja . ">Var. Prod.</td>";
$tab.="<td colspan=3 " . $bgcoloraja . ">" . $_SESSION['lang']['bjr'] . "</td>";
$tab.="<td colspan=6 " . $bgcoloraja . ">OUTPUT  ( JJG / HK )</td>";
$tab.="</tr>";
$tab.="<tr><td colspan=2 " . $bgcoloraja . ">Yang Bekerja</td><td colspan=4 " . $bgcoloraja . ">HK Siap Borong</td>";
$tab.="<td colspan=2 " . $bgcoloraja . ">Target Basis</td><td colspan=2 " . $bgcoloraja . ">Lebih Borong</td><td colspan=2 " . $bgcoloraja . ">" . $_SESSION['lang']['realisasi'] . "</td>";
$tab.="<td colspan=2 " . $bgcoloraja . ">Actual</td><td rowspan=2 " . $bgcoloraja . ">Census SM-I/II</td>";
$tab.="<td colspan=2 " . $bgcoloraja . ">Target Basis</td><td colspan=2 " . $bgcoloraja . ">Lebih Borong</td><td colspan=2 " . $bgcoloraja . ">" . $_SESSION['lang']['realisasi'] . "</td></tr>";
$tab.="<tr><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">%</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . ">%</td>";
$tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td>";
$tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td>" . $_SESSION['lang']['bi'] . "</td><td>" . $_SESSION['lang']['sbi'] . "</td>";
$tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td>";
$tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td></tr>";
$tab.="</thead><tbody>";
$totaltt = 0;
foreach ($dtThnTnm as $lstThnTnm) {
    $totaltt+=1;
    $tab.="<tr class=rowcontent>";
    $tab.="<td>" . $lstThnTnm . "</td>";
    $tab.="<td align=right>" . @number_format($dtJmlHk[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtJmlHkSbi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtJmlHkSiapBrg[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($persenBi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtJmlHkSiapBrgSbi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($persensBi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtJmlHkNorma[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtJmlHkNormaSbi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtJmlLbhBrg[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtJmlLbhBrgSbi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($totBi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($totSbi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($jumJjg[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($jumJjgSbi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtJmlkgbi[$lstThnTnm], 2) . "</td>";
	
	
    $tab.="<td align=right>" . @number_format($dtJmlkgsbi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($jumKgBi[$lstThnTnm], 2) . "</td>";
	
	
    $tab.="<td align=right>" . @number_format($jumKgSbi[$lstThnTnm], 2) . "</td>";
    $varTbs[$lstThnTnm] = $dtJmlkgbi[$lstThnTnm] - $jumKgBi[$lstThnTnm];
    $varTbsSbi[$lstThnTnm] = $dtJmlkgsbi[$lstThnTnm] - $jumKgSbi[$lstThnTnm];
    $tab.="<td align=right>" . @number_format($varTbs[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($varTbsSbi[$lstThnTnm], 2) . "</td>";
    @$bjrBi[$lstThnTnm] = $jumKgBi[$lstThnTnm] / $jumJjg[$lstThnTnm];
    @$bjrSbi[$lstThnTnm] = $jumKgSbi[$lstThnTnm] / $jumJjgSbi[$lstThnTnm];
    $tab.="<td align=right>" . @number_format($bjrBi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($bjrSbi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtAvgBjr[$lstThnTnm], 2) . "</td>";
    @$outPtTrg[$lstThnTnm] = $dtJmlHkNorma[$lstThnTnm] / $dtJmlHk[$lstThnTnm];
    @$outPtTrgSbi[$lstThnTnm] = $dtJmlHkNormaSbi[$lstThnTnm] / $dtJmlHkSbi[$lstThnTnm];
    $tab.="<td align=right>" . @number_format($outPtTrg[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($outPtTrgSbi[$lstThnTnm], 2) . "</td>";
    @$outPtLbhBrg[$lstThnTnm] = $dtJmlLbhBrg[$lstThnTnm] / $dtJmlHkSiapBrg[$lstThnTnm];
    @$outPtLbhBrgSbi[$lstThnTnm] = $dtJmlLbhBrgSbi[$lstThnTnm] / $dtJmlHkSiapBrgSbi[$lstThnTnm];
    $tab.="<td align=right>" . @number_format($outPtLbhBrg[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($outPtLbhBrgSbi[$lstThnTnm], 2) . "</td>";
    $dtTotRowBi[$lstThnTnm] = $outPtTrg[$lstThnTnm] + $outPtLbhBrg[$lstThnTnm];
    $dtTotRowSbi[$lstThnTnm] = $outPtTrgSbi[$lstThnTnm] + $outPtLbhBrgSbi[$lstThnTnm];
    $tab.="<td align=right>" . @number_format($dtTotRowBi[$lstThnTnm], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtTotRowSbi[$lstThnTnm], 2) . "</td>";
    $tab.="</tr>";

    @$totdtJmlHk+=$dtJmlHk[$lstThnTnm];
    @$totdtJmlHkSbi+=$dtJmlHkSbi[$lstThnTnm];
    @$totdtJmlHkSiapBrg+=$dtJmlHkSiapBrg[$lstThnTnm];
    @$totpersenBi+=$persenBi[$lstThnTnm];
    @$totdtJmlHkSiapBrgSbi+=$dtJmlHkSiapBrgSbi[$lstThnTnm];
    @$totpersensBi+=$persensBi[$lstThnTnm];
    @$totdtJmlHkNorma+=$dtJmlHkNorma[$lstThnTnm];
    @$totdtJmlHkNormaSbi+=$dtJmlHkNormaSbi[$lstThnTnm];
    @$totdtJmlLbhBrg+=$dtJmlLbhBrg[$lstThnTnm];
    @$totdtJmlLbhBrgSbi+=$dtJmlLbhBrgSbi[$lstThnTnm];
    @$tottotBi+=$totBi[$lstThnTnm];
    @$tottotSbi+=$totSbi[$lstThnTnm];
    @$totjumJjg+=$jumJjg[$lstThnTnm];
    @$totjumJjgSbi+=$jumJjgSbi[$lstThnTnm];
    @$totdtJmlkgbi+=$dtJmlkgbi[$lstThnTnm];
    @$totdtJmlkgsbi+=$dtJmlkgsbi[$lstThnTnm];
    @$totjumKgBi+=$jumKgBi[$lstThnTnm];
    @$totjumKgSbi+=$jumKgSbi[$lstThnTnm];
    @$totvarTbs+=$varTbs[$lstThnTnm];
    @$totvarTbsSbi+=$varTbsSbi[$lstThnTnm];
    @$totbjrBi+=$bjrBi[$lstThnTnm];
    @$totbjrSbi+=$bjrSbi[$lstThnTnm];
    @$totdtAvgBjr+=$dtAvgBjr[$lstThnTnm];
    @$totoutPtTrg+=$outPtTrg[$lstThnTnm];
    @$totoutPtTrgSbi+=$outPtTrgSbi[$lstThnTnm];
    @$totoutPtLbhBrg+=$outPtLbhBrg[$lstThnTnm];
    @$totoutPtLbhBrgSbi+=$outPtLbhBrgSbi[$lstThnTnm];
    @$totdtTotRowBi+=$dtTotRowBi[$lstThnTnm];
    @$totdtTotRowSbi+=$dtTotRowSbi[$lstThnTnm];
}
@$avgpersenBi = $totpersenBi / $totaltt;
@$avgpersensBi = $totpersensBi / $totaltt;
@$avgdtAvgBjr = $totdtAvgBjr / $totaltt;
$tab.="<tr class=rowtitle>";
$tab.="<td>" . $_SESSION['lang']['total'] . "</td>";
$tab.="<td align=right>" . @number_format($totdtJmlHk, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totdtJmlHkSbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totdtJmlHkSiapBrg, 2) . "</td>";
$tab.="<td align=right>" . @number_format($avgpersenBi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totdtJmlHkSiapBrgSbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($avgpersensBi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totdtJmlHkNorma, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totdtJmlHkNormaSbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totdtJmlLbhBrg, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totdtJmlLbhBrgSbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tottotBi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($tottotSbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totjumJjg, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totjumJjgSbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totdtJmlkgbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totdtJmlkgsbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totjumKgBi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totjumKgSbi, 2) . "</td>";
$totvarTbs = $totdtJmlkgbi - $totjumKgBi;
$totvarTbsSbi = $totdtJmlkgsbi - $totjumKgSbi;
$tab.="<td align=right>" . @number_format($totvarTbs, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totvarTbsSbi, 2) . "</td>";
@$totbjrBi = $totjumKgBi / $totjumJjg;
@$totbjrSbi = $totjumKgSbi / $totjumJjgSbi;
$tab.="<td align=right>" . @number_format($totbjrBi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totbjrSbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($avgdtAvgBjr, 2) . "</td>";
@$totoutPtTrg = $totdtJmlHkNorma / $totdtJmlHk;
@$totoutPtTrgSbi = $totdtJmlHkNormaSbi / $totdtJmlHkSbi;
$tab.="<td align=right>" . @number_format($totoutPtTrg, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totoutPtTrgSbi, 2) . "</td>";
@$totoutPtLbhBrg = $totdtJmlLbhBrg / $totdtJmlHkSiapBrg;
@$totoutPtLbhBrgSbi = $totdtJmlLbhBrgSbi / $totdtJmlHkSiapBrgSbi;
$tab.="<td align=right>" . @number_format($totoutPtLbhBrg, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totoutPtLbhBrgSbi, 2) . "</td>";
$totdtTotRowBi = $totoutPtTrg + $totoutPtLbhBrg;
$totdtTotRowSbi = $totoutPtTrgSbi + $totoutPtLbhBrgSbi;
$tab.="<td align=right>" . @number_format($totdtTotRowBi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totdtTotRowSbi, 2) . "</td>";
$tab.="</tr>";
$tab.="</tbody></table>";
switch ($proses) {
    case'preview':
        echo $tab;
        break;
    case'excel':
        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        $nop_ = "potong_buah" . $dte;
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
                global $periode;
                global $dataAfd;
                global $kdUnit;
                global $optNmOrg;
                global $dbname;
                global $thn;
                global $pdf;

                $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
                $height = 5;
                global $afdId;


                $this->SetFont('Arial', 'B', 8);
                $this->Cell($width, $height, strtoupper("06. OUTPUT " . $_SESSION['lang']['panen']), 0, 1, 'L');
                $this->Cell(790, $height, $_SESSION['lang']['bulan'] . ' : ' . substr(tanggalnormal($periode), 1, 7), 0, 1, 'R');
                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $this->Cell($width, $height, $_SESSION['lang']['unit'] . ' : ' . $optNmOrg[$kdUnit], 0, 1, 'L');
                if ($afdId != '') {
                    $tinggiAkr = $this->GetY();
                    $ksamping = $this->GetX();
                    $this->SetY($tinggiAkr + 20);
                    $this->SetX($ksamping);
                    $this->Cell($width, $height, $_SESSION['lang']['afdeling'] . ' : ' . $optNmOrg[$afdId], 0, 1, 'L');
                }
                $this->Cell(790, $height, ' ', 0, 1, 'R');
                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $height = 15;
                $this->SetFillColor(220, 220, 220);
                $this->SetFont('Arial', 'B', 5);
                $this->Cell(25, $height, $_SESSION['lang']['tahun'], 'TLR', 0, 'C', 1);
                $this->Cell(120, $height, $_SESSION['lang']['jhk'], 'TBLR', 0, 'C', 1);
                $this->Cell(180, $height, $_SESSION['lang']['jumlah'] . "  (JJG)", 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['janjang'] . " " . $_SESSION['lang']['kirim'], 'TLR', 0, 'C', 1);
                $this->Cell(60, $height, "Total  " . $_SESSION['lang']['produksi'], 'TLR', 0, 'C', 1);
                $this->Cell(60, $height, $_SESSION['lang']['tbs'] . " " . $_SESSION['lang']['kirim'], 'TLR', 0, 'C', 1);
                $this->Cell(60, $height, "Var. Prod.", 'TBLR', 0, 'C', 1);
                $this->Cell(70, $height, $_SESSION['lang']['bjr'], 'TBLR', 0, 'C', 1);
                $this->Cell(180, $height, "OUTPUT  ( JJG / HK )", 'TBLR', 1, 'C', 1);

                $this->Cell(25, $height, $_SESSION['lang']['tanam'], 'LR', 0, 'C', 1);
                $this->Cell(40, $height, "Yang Bekerja", 'TBLR', 0, 'C', 1);
                $this->Cell(80, $height, "HK Siap Borong", 'TBLR', 0, 'C', 1);
                $this->Cell(60, $height, "Target Basis", 'TBLR', 0, 'C', 1);
                $this->Cell(60, $height, "Lebih Borong", 'TBLR', 0, 'C', 1);
                $this->Cell(60, $height, $_SESSION['lang']['realisasi'], 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, "", 'BLR', 0, 'C', 1);
                $this->Cell(60, $height, "( Kg )", 'BLR', 0, 'C', 1);
                $this->Cell(60, $height, "( Kg )", 'BLR', 0, 'C', 1);
                $this->Cell(60, $height, "KBN-PKS(%)", 'BLR', 0, 'C', 1);
                $this->Cell(40, $height, "Actual", 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, "Census", 'TLR', 0, 'C', 1);
                $this->Cell(60, $height, "Target Basis", 'TBLR', 0, 'C', 1);
                $this->Cell(60, $height, "Lebih Borong", 'TBLR', 0, 'C', 1);
                $this->Cell(60, $height, $_SESSION['lang']['realisasi'], 'TBLR', 1, 'C', 1);
                $this->SetFont('Arial', '', 4);
                $this->Cell(25, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, "%", 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, "%", 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(25, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(25, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, "SM-I/II", 'BLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(30, $height, $_SESSION['lang']['sbi'], 'TBLR', 1, 'C', 1);
                $this->SetFont('Arial', 'B', 8);
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
        $height = 10;
        $tnggi = $jmlHari * $height;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 4);
        $totaltt = 0;
        foreach ($dtThnTnm as $lstThnTnm) {
            $totaltt+=1;
            $pdf->Cell(25, $height, $lstThnTnm, 'TBLR', 0, 'C', 1);
            $pdf->Cell(20, $height, @number_format($dtJmlHk[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($dtJmlHkSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($dtJmlHkSiapBrg[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($persenBi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($dtJmlHkSiapBrgSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($persensBi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dtJmlHkNorma[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dtJmlHkNormaSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dtJmlLbhBrg[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dtJmlLbhBrgSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($totBi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($totSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(25, $height, @number_format($jumJjg[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(25, $height, @number_format($jumJjgSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dtJmlkgbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dtJmlkgsbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($jumKgBi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($jumKgSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($varTbs[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($varTbsSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($bjrBi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($bjrSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dtAvgBjr[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($outPtTrg[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($outPtTrgSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($outPtLbhBrg[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($outPtLbhBrgSbi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dtTotRowBi[$lstThnTnm], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(30, $height, @number_format($dtTotRowSbi[$lstThnTnm], 2), 'TBLR', 1, 'R', 1);
//            $totdtJmlHk+=$dtJmlHk[$lstThnTnm];
//            $totdtJmlHkSbi+=$dtJmlHkSbi[$lstThnTnm];
//            $totdtJmlHkSiapBrg+=$dtJmlHkSiapBrg[$lstThnTnm];
//            $totpersenBi+=$persenBi[$lstThnTnm];
//            $totdtJmlHkSiapBrgSbi+=$dtJmlHkSiapBrgSbi[$lstThnTnm];
//            $totpersensBi+=$persensBi[$lstThnTnm];
//            $totdtJmlHkNorma+=$dtJmlHkNorma[$lstThnTnm];
//            $totdtJmlHkNormaSbi+=$dtJmlHkNormaSbi[$lstThnTnm];
//            $totdtJmlLbhBrg+=$dtJmlLbhBrg[$lstThnTnm];
//            $totdtJmlLbhBrgSbi+=$dtJmlLbhBrgSbi[$lstThnTnm];
//            $tottotBi+=$totBi[$lstThnTnm];
//            $tottotSbi+=$totSbi[$lstThnTnm];
//            $totjumJjg+=$jumJjg[$lstThnTnm];
//            $totjumJjgSbi+=$jumJjgSbi[$lstThnTnm];
//            $totdtJmlkgbi+=$dtJmlkgbi[$lstThnTnm];
//            $totdtJmlkgsbi+=$dtJmlkgsbi[$lstThnTnm];
//            $totjumKgBi+=$jumKgBi[$lstThnTnm];
//            $totjumKgSbi+=$jumKgSbi[$lstThnTnm];
//            $totvarTbs+=$varTbs[$lstThnTnm];
//            $totvarTbsSbi+=$varTbsSbi[$lstThnTnm];
//            $totbjrBi+=$bjrBi[$lstThnTnm];
//            $totbjrSbi+=$bjrSbi[$lstThnTnm];
//            $totdtAvgBjr+=$dtAvgBjr[$lstThnTnm];
//            $totoutPtTrg+=$outPtTrg[$lstThnTnm];
//            $totoutPtTrgSbi+=$outPtTrgSbi[$lstThnTnm];
//            $totoutPtLbhBrg+=$outPtLbhBrg[$lstThnTnm];
//            $totoutPtLbhBrgSbi+=$outPtLbhBrgSbi[$lstThnTnm];
//            $totdtTotRowBi+=$dtTotRowBi[$lstThnTnm];
//            $totdtTotRowSbi+=$dtTotRowSbi[$lstThnTnm];                      
        }
//       @$avgpersenBi=$totpersenBi/$totaltt;
//       @$avgpersensBi=$totpersensBi/$totaltt;
//       @$avgdtAvgBjr=$totdtAvgBjr/$totaltt;
        $pdf->Cell(25, $height, $_SESSION['lang']['total'], 'TBLR', 0, 'C', 1);
        $pdf->Cell(20, $height, @number_format($totdtJmlHk, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(20, $height, @number_format($totdtJmlHkSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(20, $height, @number_format($totdtJmlHkSiapBrg, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(20, $height, @number_format($avgpersenBi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(20, $height, @number_format($totdtJmlHkSiapBrgSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(20, $height, @number_format($avgpersensBi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totdtJmlHkNorma, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totdtJmlHkNormaSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totdtJmlLbhBrg, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totdtJmlLbhBrgSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($tottotBi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($tottotSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(25, $height, @number_format($totjumJjg, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(25, $height, @number_format($totjumJjgSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totdtJmlkgbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totdtJmlkgsbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totjumKgBi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totjumKgSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totvarTbs, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totvarTbsSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(20, $height, @number_format($totbjrBi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(20, $height, @number_format($totbjrSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($avgdtAvgBjr, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totoutPtTrg, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totoutPtTrgSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totoutPtLbhBrg, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totoutPtLbhBrgSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totdtTotRowBi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(30, $height, @number_format($totdtTotRowSbi, 2), 'TBLR', 1, 'R', 1);
        $pdf->Output();
        break;



    default:
        break;
}
?>