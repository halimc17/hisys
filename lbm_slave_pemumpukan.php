<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$optNmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optKelompok = makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$optSatuan = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');


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
$bln = intval($thn[1]);
if (strlen($bln) < 2) {
    if ($thn[1] == '1') {
        $blnLalu = 12;
        $thnLalu = $thn[0] - 1;
    } else {

        $blnLalu = "0" . $bln;
    }
} else {
    $blnLalu = $bln - 1;
}
$thnLalu = $thn[0] - 1;

//get barang
$sGetBrg = "select distinct kodebarang from " . $dbname . ".bgt_lbm_material_vw 
          where tahunbudget='" . $thn[0] . "' and substr(kodeorg,1,4)='" . $kdUnit . "' and substr(kodebarang,1,3)='311' order by kodebarang asc";
if ($afdId != '') {
    $sGetBrg = "select distinct kodebarang from " . $dbname . ".bgt_lbm_material_vw 
          where tahunbudget='" . $thn[0] . "' and substr(kodeorg,1,6)='" . $afdId . "' and substr(kodebarang,1,3)='311' order by kodebarang asc";
}


$qGetBrg = $owlPDO->query($sGetBrg) or die(print " Gagal: " . PDOException::getMessage());
$qGetBrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rGetBrg = $qGetBrg->fetch()) {
    //$dtBarang[]=$rGetBrg['kodebarang'];
    $dtBarang[$rGetBrg['kodebarang']] = $rGetBrg['kodebarang'];
}

###################################
#################get barang dari PTA
#bentuk unit / afd
if ($afdId == '') {
    $sortUnit = $kdUnit;
} else {
    $sortUnit = $afdId;
}


$iGetBrg = "select distinct kodebarang from " . $dbname . ".pta_dt where alokasibiaya like '%" . $sortUnit . "%' "
        . " and tanggal like '%" . $thn[0] . "%' and substr(kodebarang,1,3)='311'";


$nGetBrg = $owlPDO->query($iGetBrg) or die(print " Gagal: " . PDOException::getMessage());
$nGetBrg->setFetchMode(PDO::FETCH_ASSOC);
while ($dGetBrg = $nGetBrg->fetch()) {
    //$dtBarang[]=$dGetBrg['kodebarang'];
    $dtBarang[$dGetBrg['kodebarang']] = $dGetBrg['kodebarang'];
}




$sThTnm = "select distinct tahuntanam from " . $dbname . ".setup_blok where statusblok='TM' and substr(kodeorg,1,4)='" . $kdUnit . "' order by tahuntanam asc";
if ($afdId != '') {
    $sThTnm = "select distinct tahuntanam from " . $dbname . ".setup_blok where statusblok='TM' and substr(kodeorg,1,6)='" . $afdId . "' order by tahuntanam asc";
}

$qThTnm = $owlPDO->query($sThTnm) or die(print " Gagal: " . PDOException::getMessage());
$qThTnm->setFetchMode(PDO::FETCH_ASSOC);
while ($rThTnm = $qThTnm->fetch()) {
    $thnTanamTm[] = $rThTnm['tahuntanam'];
}


$sThTnm = "select distinct tahuntanam from " . $dbname . ".setup_blok where statusblok='TBM' and substr(kodeorg,1,4)='" . $kdUnit . "' order by tahuntanam asc";
if ($afdId != '') {
    $sThTnm = "select distinct tahuntanam from " . $dbname . ".setup_blok where statusblok='TBM' and substr(kodeorg,1,6)='" . $afdId . "' order by tahuntanam asc";
}

$qThTnm = $owlPDO->query($sThTnm) or die(print " Gagal: " . PDOException::getMessage());
$qThTnm->setFetchMode(PDO::FETCH_ASSOC);
while ($rThTnm = $qThTnm->fetch()) {
    $thnTanamTbm[] = $rThTnm['tahuntanam'];
}
$sThTnm = "select distinct tahuntanam from " . $dbname . ".setup_blok where statusblok='TB' and substr(kodeorg,1,4)='" . $kdUnit . "' order by tahuntanam asc";
if ($afdId != '') {
    $sThTnm = "select distinct tahuntanam from " . $dbname . ".setup_blok where statusblok='TB' and substr(kodeorg,1,6)='" . $afdId . "' order by tahuntanam asc";
}

$qThTnm = $owlPDO->query($sThTnm) or die(print " Gagal: " . PDOException::getMessage());
$qThTnm->setFetchMode(PDO::FETCH_ASSOC);
while ($rThTnm = $qThTnm->fetch()) {
    $thnTanamTb[] = $rThTnm['tahuntanam'];
}













//luas tm
$sThnTnm = "select distinct tahuntanam,sum(luasareaproduktif) as luas,sum(jumlah) as jumlah,kodebarang from " . $dbname . ".setup_blok  a 
    left join " . $dbname . ". bgt_lbm_material_vw b on a.kodeorg=b.kodeorg
    where substr(a.kodeorg,1,4)='" . $kdUnit . "' and statusblok='TM' and tahunbudget='" . $thn[0] . "' and substr(kodebarang,1,3)='311' 
    group by tahuntanam,kodebarang order by a.tahuntanam asc";
if ($afdId != '') {
    $sThnTnm = "select distinct tahuntanam,sum(luasareaproduktif) as luas,sum(jumlah) as jumlah,kodebarang from " . $dbname . ".setup_blok  a 
    left join " . $dbname . ". bgt_lbm_material_vw b on a.kodeorg=b.kodeorg
    where substr(a.kodeorg,1,6)='" . $afdId . "' and statusblok='TM' and tahunbudget='" . $thn[0] . "' and substr(kodebarang,1,3)='311' 
    group by tahuntanam,kodebarang order by a.tahuntanam asc";
}

$qThTnm = $owlPDO->query($sThTnm) or die(print " Gagal: " . PDOException::getMessage());
$qThTnm->setFetchMode(PDO::FETCH_ASSOC);
while ($rThTnm = $qThTnm->fetch()) {

    $dtBarangTm[$rThnTnm['kodebarang']] = $rThnTnm['kodebarang'];
    $lsProduktifTm[$rThnTnm['tahuntanam']] = $rThnTnm['luas'];
    $jmTm[$rThnTnm['tahuntanam']][$rThnTnm['kodebarang']] = $rThnTnm['jumlah'];
}



$sRealisasi = "select distinct kodebarang,sum(kwantitas) as jumlah,tahuntanam from " . $dbname . ".kebun_pakai_material_vw a
             left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg where 
             a.kodeorg like '" . $kdUnit . "%' and notransaksi like '%TM%' and substr(tanggal,1,7) between '" . $thn[0] . "-01' and '" . $periode . "'
             and substr(kodebarang,1,3)='311' group by tahuntanam,kodebarang order by tahuntanam asc";
if ($afdId != '') {
    $sRealisasi = "select distinct kodebarang,sum(kwantitas) as jumlah,tahuntanam from " . $dbname . ".kebun_pakai_material_vw a
             left join " . $dbname . ".setup_blok b on a.kodeorg=b.kodeorg where 
             a.kodeorg like '" . $kdUnit . "%' and notransaksi like '%TM%' and substr(tanggal,1,7) between '" . $thn[0] . "-01' and '" . $periode . "'
             and substr(kodebarang,1,3)='311' group by tahuntanam,kodebarang order by tahuntanam asc";
}


$qRealisasi = $owlPDO->query($sRealisasi) or die(print " Gagal: " . PDOException::getMessage());
$qRealisasi->setFetchMode(PDO::FETCH_ASSOC);
while ($rRealisasi = $qRealisasi->fetch()) {
    $jmTmRi[$rRealisasi['tahuntanam']][$rRealisasi['kodebarang']] = $rRealisasi['jumlah'];
}


//luas tbm
$sThnTnm = "select distinct tahuntanam,sum(luasareaproduktif) as luas,sum(jumlah) as jumlah,kodebarang from " . $dbname . ".setup_blok  a 
    left join " . $dbname . ". bgt_lbm_material_vw b on a.kodeorg=b.kodeorg
    where substr(a.kodeorg,1,4)='" . $kdUnit . "' and statusblok='TBM' and tahunbudget='" . $thn[0] . "' and substr(kodebarang,1,3)='311' group by tahuntanam,kodebarang  order by a.kodeorg asc";
if ($afdId != '') {
    $sThnTnm = "select distinct tahuntanam,sum(luasareaproduktif) as luas,sum(jumlah) as jumlah,kodebarang from " . $dbname . ".setup_blok  a 
    left join " . $dbname . ". bgt_lbm_material_vw b on a.kodeorg=b.kodeorg
    where substr(a.kodeorg,1,6)='" . $afdId . "' and statusblok='TBM' and tahunbudget='" . $thn[0] . "' and substr(kodebarang,1,3)='311' group by tahuntanam,kodebarang  order by a.kodeorg asc";
}

$qThTnm = $owlPDO->query($sThTnm) or die(print " Gagal: " . PDOException::getMessage());
$qThTnm->setFetchMode(PDO::FETCH_ASSOC);
while ($rThTnm = $qThTnm->fetch()) {

    $dtBarangTbm[$rThnTnm['kodebarang']] = $rThnTnm['kodebarang'];
    $lsProduktifTbm[$rThnTnm['tahuntanam']]+=$rThnTnm['luas'];
    $jmTbm[$rThnTnm['tahuntanam']][$rThnTnm['kodebarang']]+=$rThnTnm['jumlah'];
}

$sRealisasi = "select distinct tahuntanam,kodebarang,sum(kwantitas) as jumlah from " . $dbname . ".setup_blok a  
    left join " . $dbname . ".kebun_pakai_material_vw b on a.kodeorg=b.kodeorg
    where substr(a.kodeorg,1,4)='" . $kdUnit . "' and statusblok='TBM' and substr(notransaksi,15,3)='TBM' and (substr(tanggal,1,7) between '" . $thn[0] . "-01' and '" . $periode . "') and substr(kodebarang,1,3)='311' group by tahuntanam,kodebarang order by a.tahuntanam asc";

$qRealisasi = $owlPDO->query($sRealisasi) or die(print " Gagal: " . PDOException::getMessage());
$qRealisasi->setFetchMode(PDO::FETCH_ASSOC);
while ($rRealisasi = $qRealisasi->fetch()) {
    @$jmTbmRi[$rRealisasi['tahuntanam']][$rRealisasi['kodebarang']]+=$rRealisasi['jumlah'];
}
//luas tb
$sThnTnm = "select distinct tahuntanam,sum(luasareaproduktif) as luas,sum(jumlah) as jumlah,kodebarang from " . $dbname . ".setup_blok  a 
    left join " . $dbname . ". bgt_lbm_material_vw b on a.kodeorg=b.kodeorg
    where substr(a.kodeorg,1,4)='" . $kdUnit . "' and statusblok='TB' and tahunbudget='" . $thn[0] . "' and substr(kodebarang,1,3)='311' group by tahuntanam,kodebarang order by a.kodeorg asc";
if ($afdId != '') {
    $sThnTnm = "select distinct tahuntanam,sum(luasareaproduktif) as luas,sum(jumlah) as jumlah,kodebarang from " . $dbname . ".setup_blok  a 
    left join " . $dbname . ". bgt_lbm_material_vw b on a.kodeorg=b.kodeorg
    where substr(a.kodeorg,1,6)='" . $afdId . "' and statusblok='TB' and tahunbudget='" . $thn[0] . "' and substr(kodebarang,1,3)='311' group by tahuntanam,kodebarang order by a.kodeorg asc";
}

$qThTnm = $owlPDO->query($sThTnm) or die(print " Gagal: " . PDOException::getMessage());
$qThTnm->setFetchMode(PDO::FETCH_ASSOC);
while ($rThTnm = $qThTnm->fetch()) {

    $dtBarangTb[$rThnTnm['kodebarang']] = $rThnTnm['kodebarang'];
    $lsProduktifTb[$rThnTnm['tahuntanam']] = $rThnTnm['luas'];
    @$jmTb[$rThnTnm['tahuntanam']][$rThnTnm['kodebarang']]+=$rThnTnm['jumlah'];
}
$sRealisasi = "select distinct tahuntanam,kodebarang,sum(kwantitas) as jumlah from " . $dbname . ".setup_blok a  
    left join " . $dbname . ".kebun_pakai_material_vw b on a.kodeorg=b.kodeorg
    where substr(a.kodeorg,1,4)='" . $kdUnit . "' and statusblok='TBM' and substr(notransaksi,15,2)='TB' and (substr(tanggal,1,7) between '" . $thn[0] . "-01' and '" . $periode . "') and substr(kodebarang,1,3)='311' group by tahuntanam,kodebarang order by a.tahuntanam asc";
//echo $sRealisasi;
if ($afdId != '') {
    $sRealisasi = "select distinct tahuntanam,kodebarang,sum(kwantitas) as jumlah from " . $dbname . ".setup_blok a  
    left join " . $dbname . ".kebun_pakai_material_vw b on a.kodeorg=b.kodeorg
    where substr(a.kodeorg,1,6)='" . $afdId . "' and statusblok='TBM' and substr(notransaksi,15,2)='TB' and (substr(tanggal,1,7) between '" . $thn[0] . "-01' and '" . $periode . "') and substr(kodebarang,1,3)='311' group by tahuntanam,kodebarang order by a.tahuntanam asc";
}

$qRealisasi = $owlPDO->query($sRealisasi) or die(print " Gagal: " . PDOException::getMessage());
$qRealisasi->setFetchMode(PDO::FETCH_ASSOC);
while ($rRealisasi = $qRealisasi->fetch()) {
    @$jmTbRi[$rRealisasi['tahuntanam']][$rRealisasi['kodebarang']]+=$rRealisasi['jumlah'];
}

################################TM#############
##ambil dari PTA 1 tahun
$iPtathn = "select sum(a.jumlah) as jumlah,b.tahuntanam as tahuntanam,a.kodebarang as kodebarang"
        . " from " . $dbname . ".pta_dt a left join " . $dbname . ".setup_blok b"
        . " on a.alokasibiaya=b.kodeorg where a.alokasibiaya like '%" . $sortUnit . "%' "
        . " and a.tanggal like '%" . $thn[0] . "%' and substr(a.kodebarang,1,3)='311' "
        . " and b.statusblok='TM' group by tahuntanam,kodebarang";

$nPtathn = $owlPDO->query($iPtathn) or die(print " Gagal: " . PDOException::getMessage());
$nPtathn->setFetchMode(PDO::FETCH_ASSOC);
while ($dPtathn = $nPtathn->fetch()) {
    $ptathntm[$dPtathn['tahuntanam']][$dPtathn['kodebarang']] = $dPtathn['jumlah'];
}
#pta sb
$iPtasb = "select sum(a.jumlah) as jumlah,b.tahuntanam as tahuntanam,a.kodebarang as kodebarang"
        . " from " . $dbname . ".pta_dt a left join " . $dbname . ".setup_blok b"
        . " on a.alokasibiaya=b.kodeorg where a.alokasibiaya like '%" . $sortUnit . "%' "
        . " and substr(a.kodebarang,1,3)='311' and "
        . " tanggal between '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') "
        . " and b.statusblok='TM' group by tahuntanam,kodebarang";

$nPtasb = $owlPDO->query($iPtasb) or die(print " Gagal: " . PDOException::getMessage());
$nPtasb->setFetchMode(PDO::FETCH_ASSOC);
while ($dPtasb = $nPtasb->fetch()) {
    $ptasbtm[$dPtasb['tahuntanam']][$dPtasb['kodebarang']] = $dPtasb['jumlah'];
}


################################TBM#############
##ambil dari PTA 1 tahun
$iPtathn = "select sum(a.jumlah) as jumlah,b.tahuntanam as tahuntanam,a.kodebarang as kodebarang"
        . " from " . $dbname . ".pta_dt a left join " . $dbname . ".setup_blok b"
        . " on a.alokasibiaya=b.kodeorg where a.alokasibiaya like '%" . $sortUnit . "%' "
        . " and a.tanggal like '%" . $thn[0] . "%' and substr(a.kodebarang,1,3)='311' "
        . " and b.statusblok='TBM' group by tahuntanam,kodebarang";

$nPtathn = $owlPDO->query($iPtathn) or die(print " Gagal: " . PDOException::getMessage());
$nPtathn->setFetchMode(PDO::FETCH_ASSOC);
while ($dPtathn = $nPtathn->fetch()) {
    $ptathntbm[$dPtathn['tahuntanam']][$dPtathn['kodebarang']] = $dPtathn['jumlah'];
}
#pta sb
$iPtasb = "select sum(a.jumlah) as jumlah,b.tahuntanam as tahuntanam,a.kodebarang as kodebarang"
        . " from " . $dbname . ".pta_dt a left join " . $dbname . ".setup_blok b"
        . " on a.alokasibiaya=b.kodeorg where a.alokasibiaya like '%" . $sortUnit . "%' "
        . " and substr(a.kodebarang,1,3)='311' and "
        . " tanggal between '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') "
        . " and b.statusblok='TBM' group by tahuntanam,kodebarang";

$nPtasb = $owlPDO->query($iPtasb) or die(print " Gagal: " . PDOException::getMessage());
$nPtasb->setFetchMode(PDO::FETCH_ASSOC);
while ($dPtasb = $nPtasb->fetch()) {
    $ptasbtbm[$dPtasb['tahuntanam']][$dPtasb['kodebarang']] = $dPtasb['jumlah'];
}


################################TB#############
##ambil dari PTA 1 tahun
$iPtathn = "select sum(a.jumlah) as jumlah,b.tahuntanam as tahuntanam,a.kodebarang as kodebarang"
        . " from " . $dbname . ".pta_dt a left join " . $dbname . ".setup_blok b"
        . " on a.alokasibiaya=b.kodeorg where a.alokasibiaya like '%" . $sortUnit . "%' "
        . " and a.tanggal like '%" . $thn[0] . "%' and substr(a.kodebarang,1,3)='311' "
        . " and b.statusblok='TB' group by tahuntanam,kodebarang";

$nPtathn = $owlPDO->query($iPtathn) or die(print " Gagal: " . PDOException::getMessage());
$nPtathn->setFetchMode(PDO::FETCH_ASSOC);
while ($dPtathn = $nPtathn->fetch()) {
    $ptathntb[$dPtathn['tahuntanam']][$dPtathn['kodebarang']] = $dPtathn['jumlah'];
}


#pta sb
$iPtasb = "select sum(a.jumlah) as jumlah,b.tahuntanam as tahuntanam,a.kodebarang as kodebarang"
        . " from " . $dbname . ".pta_dt a left join " . $dbname . ".setup_blok b"
        . " on a.alokasibiaya=b.kodeorg where a.alokasibiaya like '%" . $sortUnit . "%' "
        . " and substr(a.kodebarang,1,3)='311' and "
        . " tanggal between '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') "
        . " and b.statusblok='TB' group by tahuntanam,kodebarang";

$nPtasb = $owlPDO->query($iPtasb) or die(print " Gagal: " . PDOException::getMessage());
$nPtasb->setFetchMode(PDO::FETCH_ASSOC);
while ($dPtasb = $nPtasb->fetch()) {
    $ptasbtb[$dPtasb['tahuntanam']][$dPtasb['kodebarang']] = $dPtasb['jumlah'];
}




@$varCek = count($dtBarang);
if ($varCek < 1) {
    exit("Error: No data found");
}
$brdr = 0;
$bgcoloraja = '';
$cols = count($dtBarang);
@$colsTbm = count($dtBarangTbm);
@$colsTb = count($dtBarangTb);
$da = $cols * 3;
$daTblm = $colsTbm * 3;
$daTb = $colsTb * 3;
if ($proses == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE align=center";
    $brdr = 1;
    $tab.="
    <table>
    <tr><td colspan=5 align=left><b>13. " . strtoupper($_SESSION['lang']['pemupukan']) . "</b></td><td colspan=7 align=right><b>" . $_SESSION['lang']['bulan'] . " : " . substr(tanggalnormal($periode), 1, 7) . "</b></td></tr>
    <tr><td colspan=5 align=left>" . $_SESSION['lang']['unit'] . " : " . $optNmOrg[$kdUnit] . " </td></tr>";
    if ($afdId != '') {
        $tab.="<tr><td colspan=5 align=left>" . $_SESSION['lang']['afdeling'] . " : " . $optNmOrg[$afdId] . " </td></tr>";
    }
    $tab.="<tr><td colspan=5 align=left>&nbsp;</td></tr>
    </table>";
}

$tab.=strtoupper($_SESSION['lang']['tm']) . " ( TM )<br/><table cellspacing=1 border=" . $brdr . " class=sortable>
	<thead class=rowheader>
	<tr>
        <td rowspan=2>" . $_SESSION['lang']['tahuntanam'] . "</td>
        <td rowspan=2>" . $_SESSION['lang']['luas'] . " HA</td>
        ";


if ($cols != 0) {
    if (!empty($dtBarang))
        foreach ($dtBarang as $lstKdBrg) {
            $tab.="<td colspan=5>" . $optNmBrg[$lstKdBrg] . " (" . $lstKdBrg . ")-" . $optSatuan[$lstKdBrg] . "</td>";
        }
} else {
    $tab.="<tr class=rowcontent><td colspan=" . $da . ">" . $_SESSION['lang']['dataempty'] . "</td></tr>";
}
$tab.="</tr><tr>";
for ($awr = 1; $awr <= $cols; $awr++) {
    $tab.="<td>" . $_SESSION['lang']['anggaran'] . " 1 " . $_SESSION['lang']['tahun'] . "</td>
                   <td>PTA 1 " . $_SESSION['lang']['tahun'] . "</td>
                   <td>PTA " . $_SESSION['lang']['sbi'] . "</td>
                   <td>" . $_SESSION['lang']['realisasi'] . " " . $_SESSION['lang']['sbi'] . "</td>
                   <td>" . $_SESSION['lang']['sisa'] . "</td>";
}
$tab.="</tr></thead>
	<tbody>";
$thnTnmCekTM = count($thnTanamTm);
if ($thnTnmCekTM != 0) {

    if (!empty($thnTanamTm))
        foreach ($thnTanamTm as $dtThnTM) {
            $tab.="<tr class=rowcontent>";
            $tab.="<td>" . $dtThnTM . "</td>";
            $tab.="<td align=right>" . @number_format($lsProduktifTm[$dtThnTM], 2) . "</td>";
            if (!empty($dtBarang))
                foreach ($dtBarang as $lstKdBrg) {
                    @$sisa[$dtThnTM][$lstKdBrg] = $jmTm[$dtThnTM][$lstKdBrg] - $jmTmRi[$dtThnTM][$lstKdBrg];
                    $tab.="<td align=right>" . @number_format($jmTm[$dtThnTM][$lstKdBrg], 0) . "</td>";
                    $tab.="<td align=right>" . @number_format($ptathntm[$dtThnTM][$lstKdBrg], 0) . "</td>";
                    $tab.="<td align=right>" . @number_format($ptasbtm[$dtThnTM][$lstKdBrg], 0) . "</td>";
                    $tab.="<td  align=right>" . @number_format($jmTmRi[$dtThnTM][$lstKdBrg], 0) . "</td>";
                    $tab.="<td   align=right>" . @number_format($sisa[$dtThnTM][$lstKdBrg], 0) . "</td>";
                }

            $tab.="</tr>";
        }
} else {
    $tab.="<tr class=rowcontent><td colspan=" . $da . ">" . $_SESSION['lang']['dataempty'] . "</td></tr>";
}





$tab.="</tbody></table><br />";
$tab.=strtoupper($_SESSION['lang']['tbm']) . " ( TBM )<br/><table cellspacing=1 border=" . $brdr . " class=sortable>
            <thead class=rowheader>
            <tr>
            <td rowspan=2>" . $_SESSION['lang']['tahuntanam'] . "</td>
            <td rowspan=2>" . $_SESSION['lang']['luas'] . " HA</td>
            ";
@$cekBrgTbm = count($dtBarangTbm);
if ($cekBrgTbm != 0) {
    if (!empty($dtBarangTbm))
        foreach ($dtBarangTbm as $lstKdBrg) {
            $tab.="<td colspan=3>" . $optNmBrg[$lstKdBrg] . " (" . $lstKdBrg . ")-" . $optSatuan[$lstKdBrg] . "</td>";
        }
} else {
    $tab.="<td colspan=" . $daTblm . ">" . $_SESSION['lang']['dataempty'] . "</td>";
}
$tab.="</tr><tr>";
for ($awr = 1; $awr <= $colsTbm; $awr++) {
    $tab.="<td>" . $_SESSION['lang']['anggran'] . " 1 " . $_SESSION['lang']['tahun'] . "</td>"
            . "<td>PTA 1 " . $_SESSION['lang']['tahun'] . "</td>"
            . "<td>PTA " . $_SESSION['lang']['sbi'] . "</td>"
            . "<td>" . $_SESSION['lang']['realisasi'] . " " . $_SESSION['lang']['sbi'] . "</td>"
            . "<td>" . $_SESSION['lang']['sisa'] . "</td>";
}
$tab.="</tr></thead>
            <tbody>";
@$thnTnmCekTBM = count($thnTanamTbm);
if ($thnTnmCekTBM != 0) {
    if (!empty($thnTanamTbm))
        foreach ($thnTanamTbm as $dtThnTbM) {
            $tab.="<tr class=rowcontent>";
            $tab.="<td>" . $dtThnTbM . "</td>";
            $tab.="<td align=right>" . @number_format($lsProduktifTbm[$dtThnTbM], 2) . "</td>";
            if (!empty($dtBarangTbm))
                foreach ($dtBarangTbm as $lstKdBrg) {
//                    $tab.= '<td>'.$lstKdBrg.'</td>';
                    $sisaTbm[$dtThnTbM][$lstKdBrg] = $jmTbm[$dtThnTbM][$lstKdBrg] - $jmTbmRi[$dtThnTbM][$lstKdBrg];

                    $tab.="<td align=right>" . @number_format($jmTbm[$dtThnTbM][$lstKdBrg], 0) . "</td>";
                    $tab.="<td align=right>" . @number_format($ptathntbm[$dtThnTM][$lstKdBrg], 0) . "</td>";
                    $tab.="<td align=right>" . @number_format($ptasbtbm[$dtThnTM][$lstKdBrg], 0) . "</td>";
                    $tab.="<td align=right>" . @number_format($jmTbmRi[$dtThnTbM][$lstKdBrg], 0) . "</td>";
                    $tab.="<td align=right>" . @number_format($sisaTbm[$dtThnTbM][$lstKdBrg], 0) . "</td>";
                }
            $tab.="</tr>";
        }
} else {
    $tab.="<tr class=rowcontent><td colspan=" . $daTblm . ">" . $_SESSION['lang']['dataempty'] . "</td></tr>";
}


$tab.="</tbody></table><br />";

$tab.=strtoupper($_SESSION['lang']['tb']) . " ( TB )<br/><table cellspacing=1 border=" . $brdr . " class=sortable>
                <thead class=rowheader>
                <tr>
                <td rowspan=2>" . $_SESSION['lang']['tahuntanam'] . "</td>
                <td rowspan=2>" . $_SESSION['lang']['luas'] . " HA</td>
                ";
if ($colsTb != 0) {
    if (!empty($dtBarangTb))
        foreach ($dtBarangTb as $lstKdBrg) {
            $tab.="<td colspan=3>" . $optNmBrg[$lstKdBrg] . " (" . $lstKdBrg . ")-" . $optSatuan[$lstKdBrg] . "</td>";
        }
} else {
    $tab.="<tr class=rowcontent><td colspan=" . $daTb . ">" . $_SESSION['lang']['dataempty'] . "</td></tr>";
}
$tab.="</tr><tr>";
for ($awr = 1; $awr <= $colsTb; $awr++) {
    $tab.="<td>" . $_SESSION['lang']['anggaran'] . " 1 " . $_SESSION['lang']['tahun'] . "</td>"
            . "<td>PTA 1 " . $_SESSION['lang']['tahun'] . "</td>"
            . "<td>PTA " . $_SESSION['lang']['sbi'] . "</td>"
            . "<td>" . $_SESSION['lang']['realisasi'] . " " . $_SESSION['lang']['realisasi'] . "</td>"
            . "<td>" . $_SESSION['lang']['sisa'] . "</td>";
}
$tab.="</tr></thead>
                <tbody>";


@$thnTnmCekTB = count($thnTanamTb);
if ($thnTnmCekTB != 0) {
    if (!empty($thnTanamTb))
        foreach ($thnTanamTb as $dtThnTb) {
            $tab.="<tr class=rowcontent>";
            $tab.="<td>" . $dtThnTb . "</td>";
            $tab.="<td align=right>" . @number_format($lsProduktifTb[$dtThnTb], 2) . "</td>";
            if (!empty($dtBarangTb))
                foreach ($dtBarangTb as $lstKdBrg) {
                    $sisaTb[$dtThnTb][$lstKdBrg] = $jmTb[$dtThnTb][$lstKdBrg] - $jmTbRi[$dtThnTb][$lstKdBrg];
                    $tab.="<td align=right>" . @number_format($jmTb[$dtThnTb][$lstKdBrg], 0) . "</td>";
                    $tab.="<td align=right>" . @number_format($ptathntb[$dtThnTM][$lstKdBrg], 0) . "</td>";
                    $tab.="<td align=right>" . @number_format($ptasbtb[$dtThnTM][$lstKdBrg], 0) . "</td>";
                    $tab.="<td align=right>" . @number_format($jmTbRi[$dtThnTb][$lstKdBrg], 0) . "</td>";
                    $tab.="<td align=right>" . @number_format($sisaTb[$dtThnTb][$lstKdBrg], 0) . "</td>";
                }
            $tab.="</tr>";
        }
} else {
    $tab.="<tr class=rowcontent><td colspan=" . $daTb . ">" . $_SESSION['lang']['dataempty'] . "</td></tr>";
}
$tab.="</tbody></table>";
switch ($proses) {
    case'preview':
        echo $tab;
        break;
    case'excel':
        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        $nop_ = "distribusi_pupuk_" . $dte;
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
                global $dtBarang;
                global $cols;
                global $lstKodeOrg9;
                global $lstKodeOrg8;
                global $optSatuan;
                global $optNmBrg;

                $this->SetFont('Arial', 'B', 8);
                $this->Cell($width, $height, strtoupper("13. " . strtoupper($_SESSION['lang']['pemupukan'])), 0, 1, 'L');
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
                $this->ln(15);
                $wit = $cols * 25;
                $witda = $wit * 2;
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
        $wit = $cols * 25;
        $witda = ($cols * $wit) + 90;
        $pdf->AddPage();

        $pdf->SetFillColor(255, 255, 255);

        $pdf->Cell(245, $height, strtoupper($_SESSION['lang']['TM']) . " ( TM )", 0, 1, 'L', 1);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->Cell(45, $height, $_SESSION['lang']['tahun'], TLR, 0, 'C', 1);
        $pdf->Cell(45, $height, $_SESSION['lang']['luas'], TLR, 0, 'C', 1);
        if (!empty($dtBarang))
            foreach ($dtBarang as $lstKdBrg) {
                $ard+=1;
                if ($ard != $cols) {
                    $pdf->Cell($wit, $height, $optNmBrg[$lstKdBrg] . " (" . $lstKdBrg . ")-" . $optSatuan[$lstKdBrg], TLR, 0, 'C', 1);
                } else {
                    $pdf->Cell($wit, $height, $optNmBrg[$lstKdBrg] . " (" . $lstKdBrg . ")-" . $optSatuan[$lstKdBrg], TLR, 1, 'C', 1);
                }
            }
        $pdf->Cell(45, $height, $_SESSION['lang']['tanam'], BLR, 0, 'C', 1);
        $pdf->Cell(45, $height, "(HA)", BLR, 0, 'C', 1);

        if (!empty($dtBarang))
            foreach ($dtBarang as $lstKdBrg) {
                $ard5+=1;
                if ($ard5 != $cols) {
                    $pdf->Cell(45, $height, $_SESSION['lang']['anggaran'] . " 1 " . $_SESSION['lang']['tahun'], TBLR, 0, 'C', 1);
                    $pdf->Cell(45, $height, $_SESSION['lang']['realisasi'] . " " . $_SESSION['lang']['sbi'], TBLR, 0, 'C', 1);
                    $pdf->Cell(35, $height, $_SESSION['lang']['sisa'], TBLR, 0, 'C', 1);
                } else {
                    $pdf->Cell(45, $height, $_SESSION['lang']['anggaran'] . " 1 " . $_SESSION['lang']['tahun'], TBLR, 0, 'C', 1);
                    $pdf->Cell(45, $height, $_SESSION['lang']['realisasi'] . " " . $_SESSION['lang']['sbi'], TBLR, 0, 'C', 1);
                    $pdf->Cell(35, $height, $_SESSION['lang']['sisa'], TBLR, 1, 'C', 1);
                }
            }
        $pdf->SetFillColor(255, 255, 255);
        $thnTnmCekTM = count($thnTanamTm);
        $dtCoba = $cols * 3;
        if ($thnTnmCekTM != 0) {

            if (!empty($thnTanamTm))
                foreach ($thnTanamTm as $dtThnTM) {
                    $ard6 = 0;
                    $pdf->Cell(45, $height, $dtThnTM, TBLR, 0, 'C', 1);
                    $pdf->Cell(45, $height, @number_format($lsProduktifTm[$dtThnTM], 2), TBLR, 0, 'C', 1);
                    if (!empty($dtBarang))
                        foreach ($dtBarang as $lstKdBrg2) {
                            $sisa[$dtThnTM][$lstKdBrg2] = $jmTm[$dtThnTM][$lstKdBrg2] - $jmTmRi[$dtThnTM][$lstKdBrg2];
                            $ard6+=1;
                            if ($ard6 != $cols) {

                                $pdf->Cell(45, $height, @number_format($jmTm[$dtThnTM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(45, $height, @number_format($jmTmRi[$dtThnTM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(35, $height, @number_format($sisa[$dtThnTM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                            } else {
                                $pdf->Cell(45, $height, @number_format($jmTm[$dtThnTM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(45, $height, @number_format($jmTmRi[$dtThnTM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(35, $height, @number_format($sisa[$dtThnTM][$lstKdBrg2], 0), TBLR, 1, 'R', 1);
                            }
                        }
                }
        } else {
            $pdf->Cell($witda, $height, $_SESSION['lang']['dataempty'], TBLR, 1, 'L', 1);
        }
        $samping = $pdf->GetX();
        $tinggi = $pdf->GetY();
        $pdf->SetX($samping);
        $pdf->SetY($tinggi + 10);


        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(245, $height, strtoupper($_SESSION['lang']['tbm']) . " ( TBM )", 0, 1, 'L', 1);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->Cell(45, $height, $_SESSION['lang']['tahun'], TLR, 0, 'C', 1);
        $pdf->Cell(45, $height, $_SESSION['lang']['luas'], TLR, 0, 'C', 1);
        $ard = 0;
        if (!empty($dtBarang))
            foreach ($dtBarang as $lstKdBrg) {
                $ard+=1;
                if ($ard != $cols) {
                    $pdf->Cell($wit, $height, $optNmBrg[$lstKdBrg] . " (" . $lstKdBrg . ")-" . $optSatuan[$lstKdBrg], TLR, 0, 'C', 1);
                } else {
                    $pdf->Cell($wit, $height, $optNmBrg[$lstKdBrg] . " (" . $lstKdBrg . ")-" . $optSatuan[$lstKdBrg], TLR, 1, 'C', 1);
                }
            }
        $pdf->Cell(45, $height, $_SESSION['lang']['tanam'], BLR, 0, 'C', 1);
        $pdf->Cell(45, $height, "(HA)", BLR, 0, 'C', 1);
        $ard5 = 0;
        if (!empty($dtBarang))
            foreach ($dtBarang as $lstKdBrg) {
                $ard5+=1;
                if ($ard5 != $cols) {
                    $pdf->Cell(45, $height, $_SESSION['lang']['anggaran'] . " 1 " . $_SESSION['lang']['tahun'], TBLR, 0, 'C', 1);
                    $pdf->Cell(45, $height, $_SESSION['lang']['realisasi'] . " " . $_SESSION['lang']['sbi'], TBLR, 0, 'C', 1);
                    $pdf->Cell(35, $height, "Sisa", TBLR, 0, 'C', 1);
                } else {
                    $pdf->Cell(45, $height, $_SESSION['lang']['anggaran'] . " 1 " . $_SESSION['lang']['tahun'], TBLR, 0, 'C', 1);
                    $pdf->Cell(45, $height, $_SESSION['lang']['realisasi'] . " " . $_SESSION['lang']['sbi'], TBLR, 0, 'C', 1);
                    $pdf->Cell(35, $height, "Sisa", TBLR, 1, 'C', 1);
                }
            }
        $pdf->SetFillColor(255, 255, 255);
        $thnTnmCekTBM = count($thnTanamTbm);

        if ($thnTnmCekTBM != 0) {

            if (!empty($thnTanamTbm))
                foreach ($thnTanamTbm as $dtThnTbM) {
                    $ard6 = 0;
                    $pdf->Cell(45, $height, $dtThnTbM, TBLR, 0, 'C', 1);
                    $pdf->Cell(45, $height, @number_format($lsProduktifTbm[$dtThnTbM], 2), TBLR, 0, 'C', 1);
                    if (!empty($dtBarang))
                        foreach ($dtBarang as $lstKdBrg2) {
                            $sisaTbm[$dtThnTbM][$lstKdBrg2] = $jmTbm[$dtThnTbM][$lstKdBrg2] - $jmTbmRi[$dtThnTbM][$lstKdBrg2];
                            $ard6+=1;
                            if ($ard6 != $cols) {

                                $pdf->Cell(45, $height, @number_format($jmTbm[$dtThnTbM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(45, $height, @number_format($jmTbmRi[$dtThnTbM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(35, $height, @number_format($sisaTbm[$dtThnTbM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                            } else {
                                $pdf->Cell(45, $height, @number_format($jmTbm[$dtThnTbM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(45, $height, @number_format($jmTbmRi[$dtThnTbM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(35, $height, @number_format($sisaTbm[$dtThnTbM][$lstKdBrg2], 0), TBLR, 1, 'R', 1);
                            }
                        }
                }
        } else {
            $pdf->Cell($witda, $height, $_SESSION['lang']['dataempty'], TBLR, 1, 'L', 1);
        }

        $samping = $pdf->GetX();
        $tinggi = $pdf->GetY();
        $pdf->SetX($samping);
        $pdf->SetY($tinggi + 10);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(245, $height, strtoupper($_SESSION['lang']['tb']) . " ( TB )", 0, 1, 'L', 1);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 5);
        $pdf->Cell(45, $height, $_SESSION['lang']['tahun'], TLR, 0, 'C', 1);
        $pdf->Cell(45, $height, $_SESSION['lang']['luas'], TLR, 0, 'C', 1);
        $ard = 0;
        if (!empty($dtBarang))
            foreach ($dtBarang as $lstKdBrg) {
                $ard+=1;
                if ($ard != $cols) {
                    $pdf->Cell($wit, $height, $optNmBrg[$lstKdBrg] . " (" . $lstKdBrg . ")-" . $optSatuan[$lstKdBrg], TLR, 0, 'C', 1);
                } else {
                    $pdf->Cell($wit, $height, $optNmBrg[$lstKdBrg] . " (" . $lstKdBrg . ")-" . $optSatuan[$lstKdBrg], TLR, 1, 'C', 1);
                }
            }
        $pdf->Cell(45, $height, $_SESSION['lang']['tanam'], BLR, 0, 'C', 1);
        $pdf->Cell(45, $height, "(HA)", BLR, 0, 'C', 1);
        $ard5 = 0;
        if (!empty($dtBarang))
            foreach ($dtBarang as $lstKdBrg) {
                $ard5+=1;
                if ($ard5 != $cols) {
                    $pdf->Cell(45, $height, $_SESSION['lang']['anggaran'] . " 1 " . $_SESSION['lang']['tahun'], TBLR, 0, 'C', 1);
                    $pdf->Cell(45, $height, $_SESSION['lang']['realisasi'] . " " . $_SESSION['lang']['sbi'], TBLR, 0, 'C', 1);
                    $pdf->Cell(35, $height, $_SESSION['lang']['sisa'], TBLR, 0, 'C', 1);
                } else {
                    $pdf->Cell(45, $height, $_SESSION['lang']['anggaran'] . " 1 " . $_SESSION['lang']['tahun'], TBLR, 0, 'C', 1);
                    $pdf->Cell(45, $height, $_SESSION['lang']['realisasi'] . " " . $_SESSION['lang']['sbi'], TBLR, 0, 'C', 1);
                    $pdf->Cell(35, $height, $_SESSION['lang']['sisa'], TBLR, 1, 'C', 1);
                }
            }
        $pdf->SetFillColor(255, 255, 255);
        $thnTnmCekTB = count($thnTanamTb);

        if ($thnTnmCekTB != 0) {

            if (!empty($thnTanamTb))
                foreach ($thnTanamTb as $dtThnTb) {
                    $ard6 = 0;
                    $pdf->Cell(45, $height, $dtThnTb, TBLR, 0, 'C', 1);
                    $pdf->Cell(45, $height, @number_format($lsProduktifTbm[$dtThnTb], 2), TBLR, 0, 'C', 1);
                    if (!empty($dtBarang))
                        foreach ($dtBarang as $lstKdBrg2) {
                            $sisaTb[$dtThnTb][$lstKdBrg2] = $jmTb[$dtThnTb][$lstKdBrg2] - $jmTbRi[$dtThnTb][$lstKdBrg2];
                            $ard6+=1;
                            if ($ard6 != $cols) {

                                $pdf->Cell(45, $height, @number_format($jmTb[$dtThnTb][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(45, $height, @number_format($jmTbRi[$dtThnTb][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(35, $height, @number_format($sisaTb[$dtThnTb][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                            } else {
                                $pdf->Cell(45, $height, @number_format($jmTb[$dtThnTbM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(45, $height, @number_format($jmTbRi[$dtThnTbM][$lstKdBrg2], 0), TBLR, 0, 'R', 1);
                                $pdf->Cell(35, $height, @number_format($sisaTb[$dtThnTbM][$lstKdBrg2], 0), TBLR, 1, 'R', 1);
                            }
                        }
                }
        } else {
            $pdf->Cell($witda, $height, $_SESSION['lang']['dataempty'], TBLR, 1, 'L', 1);
        }



        $pdf->Output();
        break;



    default:
        break;
}
?>