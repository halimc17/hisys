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
//$_POST['kdUnit']==''?$kdUnit=$_GET['kdUnit']:$kdUnit=$_POST['kdUnit'];
//$_POST['periode']==''?$periode=$_GET['periode']:$periode=$_POST['periode'];
//

$proses = checkPostGet('proses', '');
$kdUnit = checkPostGet('kdUnit', '');
$periode = checkPostGet('periode', '');
$judul = checkPostGet('judul', '');


$unitId = $_SESSION['lang']['all'];
$nmPrshn = "Holding";
$purchaser = $_SESSION['lang']['all'];
if ($periode == '') {
    exit("Error: " . $_SESSION['lang']['periode'] . " required");
}
if ($kdUnit != '') {
    $unitId = $optNmOrg[$kdUnit];
} else {
    exit("Error:" . $_SESSION['lang']['unit'] . " Tidak required");
}
$thn = explode("-", $periode);
$bln = intval($thn[1]);
$thnLalu = $thn[0];
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
//abcd
$sKend = "select b.jenisvhc,c.namabarang,a.kodevhc,a.tahunperolehan from " . $dbname . ".vhc_5master a 
        left join " . $dbname . ".vhc_5jenisvhc b on a.jenisvhc=b.jenisvhc left join 
        " . $dbname . ".log_5masterbarang c on a.kodebarang=c.kodebarang where a.kodetraksi like '" . $kdUnit . "%' 
        and b.jenisvhc in (select distinct jenisvhc from " . $dbname . ".vhc_5jenisvhc where kelompokvhc='KD')";

$qKend = $owlPDO->query($sKend) or die(print " Gagal: " . PDOException::getMessage());
$qKend->setFetchMode(PDO::FETCH_ASSOC);
while ($rKend = $qKend->fetch()) {
    $lsJenis[$rKend['kodevhc']] = $rKend['jenisvhc'];
    $lsNama[$rKend['kodevhc']] = $rKend['namabarang'];
    $lsThnPerolehan[$rKend['kodevhc']] = $rKend['tahunperolehan'];
    $dtKend[] = $rKend['kodevhc'];
}

//e
$sDataE = "select a.kodevhc,sum(a.jumlahjam) hmsetahun  from " . $dbname . ".bgt_vhc_jam a 
         left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.tahunbudget='" . $thn[0] . "' group by kodevhc";

$qDataE = $owlPDO->query($sDataE) or die(print " Gagal: " . PDOException::getMessage());
$qDataE->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataE = $qDataE->fetch()) {
    $lsKm[$rDataE['kodevhc']] = $rDataE['hmsetahun'];
}

//f
$sDataF = "select b.kodevhc, sum(a.jumlah) as hmkm from " . $dbname . ".vhc_rundt a 
         left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi
         left join " . $dbname . ".vhc_5master b on b.kodevhc=c.kodevhc
         where b.kodetraksi like '" . $kdUnit . "%' and c.tanggal like '" . $periode . "%' group by b.kodevhc";

$qDataF = $owlPDO->query($sDataF) or die(print " Gagal: " . PDOException::getMessage());
$qDataF->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataF = $qDataF->fetch()) {
    $dtBi[$rDataF['kodevhc']] = $rDataF['hmkm'];
}

//g
$sDataG = "select b.kodevhc, sum(a.jumlah) as hmkm from " . $dbname . ".vhc_rundt a 
         left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi
         left join " . $dbname . ".vhc_5master b on b.kodevhc=c.kodevhc
         where b.kodetraksi like '" . $kdUnit . "%' and (c.tanggal between '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15')) group by b.kodevhc";

$qDataG = $owlPDO->query($sDataG) or die(print " Gagal: " . PDOException::getMessage());
$qDataG->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataG = $qDataG->fetch()) {
    $dtSbi[$rDataG['kodevhc']] = $rDataG['hmkm'];
}

//h
$sDataH = "select a.kodevhc, sum(a.jlhbbm) as jlhbbm from " . $dbname . ".vhc_runht a  
         LEFT JOIN " . $dbname . ".vhc_5master b ON a.kodevhc=b.kodevhc where
         kodetraksi like '" . $kdUnit . "%' and tanggal like '" . $periode . "%' group by a.kodevhc";

$qDataH = $owlPDO->query($sDataH) or die(print " Gagal: " . PDOException::getMessage());
$qDataH->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataH = $qDataH->fetch()) {
    $jmlhBbm[$rDataH['kodevhc']] = $rDataH['jlhbbm'];
}

//i
$sDataI = "select a.kodevhc, sum(a.jlhbbm) as jlhbbm from " . $dbname . ".vhc_runht a
         LEFT JOIN " . $dbname . ".vhc_5master b ON a.kodevhc=b.kodevhc
         where kodetraksi like '" . $kdUnit . "%' and (tanggal between '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15')) group by a.kodevhc";

$qDataI = $owlPDO->query($sDataI) or die(print " Gagal: " . PDOException::getMessage());
$qDataI->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataI = $qDataI->fetch()) {
    $jmlhBbmSbi[$rDataI['kodevhc']] = $rDataI['jlhbbm'];
}
//k
$sDataK = "select a.kodevhc, a.rpsetahun/1000 as rp from " . $dbname . ".bgt_biaya_kend_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.tahunbudget ='" . $thn[0] . "' order by kodevhc";

$qDataK = $owlPDO->query($sDataK) or die(print " Gagal: " . PDOException::getMessage());
$qDataK->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataK = $qDataK->fetch()) {
    $dtAnggrn[$rDataK['kodevhc']] = $rDataK['rp'];
}

//l
$sDataL = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110201' and tanggal like '" . $periode . "%' group by kodevhc";

$qDataL = $owlPDO->query($sDataL) or die(print " Gagal: " . PDOException::getMessage());
$qDataL->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataL = $qDataL->fetch()) {
    $biGaji[$rDataL['kodevhc']] = $rDataL['gaji'];
}

//m
$sDataM = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110202'  and tanggal like '" . $periode . "%' group by kodevhc";

$qDataM = $owlPDO->query($sDataM) or die(print " Gagal: " . PDOException::getMessage());
$qDataM->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataM = $qDataM->fetch()) {
    $biLembur[$rDataM['kodevhc']] = $rDataM['gaji'];
}

//n
$sDataN = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110203'  and tanggal like '" . $periode . "%' group by kodevhc";

$qDataN = $owlPDO->query($sDataN) or die(print " Gagal: " . PDOException::getMessage());
$qDataN->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataN = $qDataN->fetch()) {
    $biBbm[$rDataN['kodevhc']] = $rDataN['gaji'];
}

//o
$sDataO = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110204'  and tanggal like '" . $periode . "%' group by kodevhc";

$qDataO = $owlPDO->query($sDataO) or die(print " Gagal: " . PDOException::getMessage());
$qDataO->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataO = $qDataO->fetch()) {
    $biSukuCdng[$rDataO['kodevhc']] = $rDataO['gaji'];
}

//p
$sDataP = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110205'  and tanggal like '" . $periode . "%' group by kodevhc";

$qDataP = $owlPDO->query($sDataP) or die(print " Gagal: " . PDOException::getMessage());
$qDataP->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataP = $qDataP->fetch()) {
    $biReparasi[$rDataP['kodevhc']] = $rDataP['gaji'];
}
//Q
$sDataQ = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110206'  and tanggal like '" . $periode . "%' group by kodevhc";

$qDataQ = $owlPDO->query($sDataQ) or die(print " Gagal: " . PDOException::getMessage());
$qDataQ->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataQ = $qDataQ->fetch()) {
    $biAsuransi[$rDataQ['kodevhc']] = $rDataQ['gaji'];
}

//Q
$sDataQ = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110207'  and tanggal like '" . $periode . "%' group by kodevhc";

$qDataQ = $owlPDO->query($sDataQ) or die(print " Gagal: " . PDOException::getMessage());
$qDataQ->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataQ = $qDataQ->fetch()) {
    $biUmum[$rDataQ['kodevhc']] = $rDataQ['gaji'];
}

//Biaya Penyusutan
$sDataQ = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110208'  and tanggal like '" . $periode . "%' group by kodevhc";

$qDataQ = $owlPDO->query($sDataQ) or die(print " Gagal: " . PDOException::getMessage());
$qDataQ->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataQ = $qDataQ->fetch()) {
    $biPenyusutan[$rDataQ['kodevhc']] = $rDataQ['gaji'];
}

//s
$sDataL = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110201' and tanggal between  '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') group by kodevhc";

$qDataL = $owlPDO->query($sDataL) or die(print " Gagal: " . PDOException::getMessage());
$qDataL->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataL = $qDataL->fetch()) {
    $sBiGaji[$rDataL['kodevhc']] = $rDataL['gaji'];
}

//t
$sDataM = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110202'  and tanggal between  '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') group by kodevhc";

$qDataM = $owlPDO->query($sDataM) or die(print " Gagal: " . PDOException::getMessage());
$qDataM->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataM = $qDataM->fetch()) {
    $sBiLembur[$rDataM['kodevhc']] = $rDataM['gaji'];
}

//u
$sDataN = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110203'  and tanggal between  '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') group by kodevhc";

$qDataN = $owlPDO->query($sDataN) or die(print " Gagal: " . PDOException::getMessage());
$qDataN->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataN = $qDataN->fetch()) {
    $sBiBbm[$rDataN['kodevhc']] = $rDataN['gaji'];
}

//v
$sDataO = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110204'  and tanggal between  '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') group by kodevhc";

$qDataO = $owlPDO->query($sDataO) or die(print " Gagal: " . PDOException::getMessage());
$qDataO->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataO = $qDataO->fetch()) {
    $sBiSukuCdng[$rDataO['kodevhc']] = $rDataO['gaji'];
}

//w
$sDataP = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110205'  and tanggal between  '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') group by kodevhc";

$qDataP = $owlPDO->query($sDataP) or die(print " Gagal: " . PDOException::getMessage());
$qDataP->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataP = $qDataP->fetch()) {
    $sBiReparasi[$rDataP['kodevhc']] = $rDataP['gaji'];
}

//x
$sDataQ = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110206'  and tanggal between  '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') group by kodevhc";

$qDataQ = $owlPDO->query($sDataQ) or die(print " Gagal: " . PDOException::getMessage());
$qDataQ->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataQ = $qDataQ->fetch()) {
    $sBiAsuransi[$rDataQ['kodevhc']] = $rDataQ['gaji'];
}
//x
$sDataQ = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110207'  and tanggal between  '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') group by kodevhc";

$qDataQ = $owlPDO->query($sDataQ) or die(print " Gagal: " . PDOException::getMessage());
$qDataQ->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataQ = $qDataQ->fetch()) {
    $sBiUmum[$rDataQ['kodevhc']] = $rDataQ['gaji'];
}

//sd Biaya Penyusutan
$sDataQ = "select a.kodevhc,sum(debet)/1000 as gaji from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc 
         where b.kodetraksi like '" . $kdUnit . "%' and a.noakun='4110208'  and tanggal between  '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15') group by kodevhc";

$qDataQ = $owlPDO->query($sDataQ) or die(print " Gagal: " . PDOException::getMessage());
$qDataQ->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataQ = $qDataQ->fetch()) {
    $sBiPenyusutan[$rDataQ['kodevhc']] = $rDataQ['gaji'];
}

$varCek = count($dtKend);
if ($varCek < 1) {
    exit("Error:Data Kosong");
}
$brdr = 0;
$bgcoloraja = '';

if ($proses == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE ";
    $brdr = 1;
    $tab.="
    <table>
    <tr><td colspan=5 align=left><b>25.3 TRANSIT " . $_SESSION['lang']['kendaraan'] . "</b></td><td colspan=7 align=right><b>" . $_SESSION['lang']['bulan'] . " : " . substr(tanggalnormal($periode), 1, 7) . "</b></td></tr>
    <tr><td colspan=5 align=left>" . $_SESSION['lang']['unit'] . " : " . $optNmOrg[$kdUnit] . " </td></tr>
    <tr><td colspan=5 align=left>&nbsp;</td></tr>
    </table>";
}


$tab.="<table cellspacing=1 cellpadding=1 border=" . $brdr . " class=sortable>
	<thead class=rowheader>";
$tab.="<tr " . $bgcoloraja . ">";
$tab.="<td rowspan=3>No.</td>";
$tab.="<td rowspan=3>" . $_SESSION['lang']['jenis'] . "</td>";
$tab.="<td rowspan=3>" . $_SESSION['lang']['nama'] . "</td>";
$tab.="<td rowspan=3>" . $_SESSION['lang']['kodevhc'] . "</td>";
$tab.="<td rowspan=3>" . $_SESSION['lang']['tahunperolehan'] . "</td>";
$tab.="<td rowspan=3>" . $_SESSION['lang']['anggaran'] . " " . $_SESSION['lang']['setahun'] . " (KM)</td>";
$tab.="<td colspan=4>" . $_SESSION['lang']['realisasi'] . "  " . $_SESSION['lang']['penggunaan'] . "  FISIK</td>";
$tab.="<td rowspan=3>RATIO S/D " . $_SESSION['lang']['bi'] . " (LTR/KM)</td>";
$tab.="<td rowspan=3>" . $_SESSION['lang']['anggaran'] . " " . $_SESSION['lang']['setahun'] . " (Rp.000,-)</td>";
$tab.="<td colspan=18>" . $_SESSION['lang']['realisasi'] . " " . $_SESSION['lang']['biaya'] . "  (000)</td>";
$tab.="<td colspan=3>COST / UNIT<br />Rp. / KM</td></tr>";
$tab.="<tr " . $bgcoloraja . "><td colspan=2>KM</td>";
$tab.="<td colspan=2>LTR</td>";
$tab.="<td colspan=9> " . $_SESSION['lang']['bi'] . "</td>";
$tab.="<td colspan=9> " . $_SESSION['lang']['sbi'] . "</td>";
$tab.="<td rowspan=2>" . $_SESSION['lang']['anggaran'] . " " . $_SESSION['lang']['setahun'] . "</td>";
$tab.="<td colspan=2>" . $_SESSION['lang']['realisasi'] . "</td></tr>";
$tab.="<tr " . $bgcoloraja . "><td>" . $_SESSION['lang']['bi'] . "</td><td>" . $_SESSION['lang']['sbi'] . "</td><td>" . $_SESSION['lang']['bi'] . "</td><td>" . $_SESSION['lang']['sbi'] . "</td>";
$tab.="<td>Gaji</td><td>Pre/Lembur</td><td>BBM/Plumas</td><td>S.Cadang</td><td>Reprasi</td><td>Asuransi</td><td>By Umum</td><td>Penyusutan</td><td>Total</td>";
$tab.="<td>Gaji</td><td>Pre/Lembur</td><td>BBM/Plumas</td><td>S.Cadang</td><td>Reprasi</td><td>Asuransi</td><td>By Umum</td><td>Penyusutan</td><td>Total</td>";
$tab.="<td>" . $_SESSION['lang']['bi'] . "</td><td>" . $_SESSION['lang']['sbi'] . "</td>";
$tab.="</tr></thead><tbody>";
foreach ($dtKend as $lstKend) {
    $no+=1;
    @$rasioDt[$lstKend] = $jmlhBbmSbi[$lstKend] / $dtSbi[$lstKend];
    $tab.="<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatDetail('" . $lstKend . "','" . $periode . "','" . $lsNama[$lstKend] . "','" . @$biGaji[$lstKend] . "','" . @$biLembur[$lstKend] . "','" . @$biBbm[$lstKend] . "','" . @$biSukuCdng[$lstKend] . "','" . @$biReparasi[$lstKend] . "','" . @$biAsuransi[$lstKend] . "','" . @$biUmum[$lstKend] . "','" . @$biPenyusutan[$lstKend] . "','" . @$dtBi[$lstKend] . "',event);\">";
    $tab.="<td>" . $no . "</td>";
    $tab.="<td>" . $lsJenis[$lstKend] . "</td>";
    $tab.="<td>" . $lsNama[$lstKend] . "</td>";
    $tab.="<td>" . $lstKend . "</td>";
    $tab.="<td>" . $lsThnPerolehan[$lstKend] . "</td>";
    $tab.="<td align=right>" . @number_format($lsKm[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtBi[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtSbi[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($jmlhBbm[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($jmlhBbmSbi[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($rasioDt[$lstKend], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dtAnggrn[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($biGaji[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($biLembur[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($biBbm[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($biSukuCdng[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($biReparasi[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($biAsuransi[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($biUmum[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($biPenyusutan[$lstKend], 0) . "</td>";
    @$rTotal[$lstKend] = $biGaji[$lstKend] + $biLembur[$lstKend] + $biBbm[$lstKend] + $biSukuCdng[$lstKend] + $biReparasi[$lstKend] + $biAsuransi[$lstKend] + $biUmum[$lstKend] + $biPenyusutan[$lstKend];
    $tab.="<td align=right>" . @number_format($rTotal[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($sBiGaji[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($sBiLembur[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($sBiBbm[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($sBiSukuCdng[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($sBiReparasi[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($sBiAsuransi[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($sBiUmum[$lstKend], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($sBiPenyusutan[$lstKend], 0) . "</td>";
    @$rTotalSbi[$lstKend] = $sBiGaji[$lstKend] + $sBiLembur[$lstKend] + $sBiBbm[$lstKend] + $sBiSukuCdng[$lstKend] + $sBiReparasi[$lstKend] + $sBiAsuransi[$lstKend] + $sBiUmum[$lstKend] + $sBiPenyusutan[$lstKend];
    $tab.="<td align=right>" . @number_format($rTotalSbi[$lstKend], 0) . "</td>";
    @$zData[$lstKend] = $dtAnggrn[$lstKend] / $lsKm[$lstKend];
    @$aaDta[$lstKend] = $rTotal[$lstKend] / $dtBi[$lstKend];
    @$abDta[$lstKend] = $rTotalSbi[$lstKend] / $dtSbi[$lstKend];
    $tab.="<td align=right>" . @number_format($zData[$lstKend], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($aaDta[$lstKend], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($abDta[$lstKend], 2) . "</td>";
    $tab.="</tr>";
}
$tab.="</tbody></table>";

switch ($proses) {
    case'preview':
        echo $tab;
        break;
    case'excel':
        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        $nop_ = "transitKendaraan" . $dte;
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
                global $kdUnit;
                global $optNmOrg;
                global $dbname;
                global $thn;
                global $tot;
                global $pdf;
                $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
                $height = 10;


                $this->SetFont('Arial', 'B', 8);
                $this->Cell($width, $height, strtoupper("25.3 TRANSIT " . $_SESSION['lang']['bi']), 0, 1, 'L');
                $this->Cell($width, $height, $_SESSION['lang']['bulan'] . ' : ' . substr(tanggalnormal($periode), 1, 7), 0, 1, 'R');
                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $this->Cell($width, $height, $_SESSION['lang']['unit'] . ' : ' . $optNmOrg[$kdUnit], 0, 1, 'L');
                $this->Cell(790, $height, ' ', 0, 1, 'R');

                $height = 10;
                $this->SetFillColor(220, 220, 220);
                $this->SetFont('Arial', 'B', 5);

                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $this->Cell(15, $height, "No.", 'TLR', 0, 'C', 1);
                $this->Cell(35, $height, $_SESSION['lang']['jenis'], 'TLR', 0, 'C', 1);
                $this->Cell(80, $height, $_SESSION['lang']['nama'], 'TLR', 0, 'C', 1);
                $this->Cell(45, $height, $_SESSION['lang']['kodevhc'], 'TLR', 0, 'C', 1);
                $this->Cell(25, $height, '', 'TLR', 0, 'C', 1);
                $this->Cell(40, $height, $_SESSION['lang']['anggaran'], 'TLR', 0, 'C', 1);
                $this->Cell(80, $height, $_SESSION['lang']['realisasi'] . " Pengunaan", 'TLR', 0, 'C', 1);
                $this->Cell(50, $height, "RATIO ", 'TLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['anggaran'], 'TLR', 0, 'C', 1);
                $this->Cell(280, $height, $_SESSION['lang']['realisasi'] . "  " . $_SESSION['lang']['biaya'] . "(000) ", 'TLR', 0, 'C', 1);
                $this->Cell(80, $height, "COST / UNIT", 'TLR', 1, 'C', 1);

                $this->Cell(15, $height, " ", 'LR', 0, 'C', 1);
                $this->Cell(35, $height, " ", 'LR', 0, 'C', 1);
                $this->Cell(80, $height, " ", 'LR', 0, 'C', 1);
                $this->Cell(45, $height, '', 'LR', 0, 'C', 1);
                $this->Cell(25, $height, "", 'LR', 0, 'C', 1);
                $this->Cell(40, $height, $_SESSION['lang']['setahun'], 'LR', 0, 'C', 1);
                $this->Cell(40, $height, "KM", 'TLR', 0, 'C', 1);
                $this->Cell(40, $height, "LTR", 'TLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['sbi'], 'LR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['setahun'], 'LR', 0, 'C', 1);
                $this->Cell(140, $height, $_SESSION['lang']['bi'], 'TLR', 0, 'C', 1);
                $this->Cell(140, $height, $_SESSION['lang']['sbi'], 'TLR', 0, 'C', 1);
                $this->Cell(40, $height, $_SESSION['lang']['anggaran'], 'TLR', 0, 'C', 1);
                $this->Cell(40, $height, $_SESSION['lang']['realisasi'], 'TLR', 1, 'C', 1);

                $this->Cell(15, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(35, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(80, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(45, $height, "", 'BLR', 0, 'C', 1);
                $this->Cell(25, $height, $_SESSION['lang']['tahunperolehan'], 'BLR', 0, 'C', 1);
                $this->Cell(40, $height, "(KM)", 'BLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, "(LTR/KM)", 'BLR', 0, 'C', 1);
                $this->Cell(50, $height, " (Rp.000,-) ", 'BLR', 0, 'C', 1);
                $this->SetFont('Arial', 'B', 4);
                $this->Cell(20, $height, "Gaji", 'TBLR', 0, 'C', 1);
                $this->SetFont('Arial', 'B', 3.5);
                $this->Cell(20, $height, "Pre/Lembur", 'TBLR', 0, 'L', 1);
                $this->Cell(20, $height, "BBM/Plumas", 'TBLR', 0, 'L', 1);
                $this->Cell(20, $height, "S.Cadang", 'TBLR', 0, 'L', 1);
                $this->SetFont('Arial', 'B', 4);
                $this->Cell(20, $height, "Reprasi", 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, "Asuransi", 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, "Total", 'TBLR', 0, 'C', 1);
                $this->SetFont('Arial', 'B', 4);
                $this->Cell(20, $height, "Gaji", 'TBLR', 0, 'C', 1);
                $this->SetFont('Arial', 'B', 3.5);
                $this->Cell(20, $height, "Pre/Lembur", 'TBLR', 0, 'L', 1);
                $this->Cell(20, $height, "BBM/Plumas", 'TBLR', 0, 'L', 1);
                $this->Cell(20, $height, "S.Cadang", 'TBLR', 0, 'L', 1);
                $this->SetFont('Arial', 'B', 4);
                $this->Cell(20, $height, "Reprasi", 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, "Asuransi", 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, "Total", 'TBLR', 0, 'C', 1);
                $this->SetFont('Arial', 'B', 5);
                $this->Cell(40, $height, $_SESSION['lang']['setahun'], 'BLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(20, $height, $_SESSION['lang']['sbi'], 'TBLR', 1, 'C', 1);
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
        @$tnggi = $jmlHari * $height;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 5);
        $i = 0;
        foreach ($dtKend as $lstKend) {
            $i+=1;

            $pdf->Cell(15, $height, $i, 'TBLR', 0, 'C', 1);
            $pdf->Cell(35, $height, $lsJenis[$lstKend], 'TBLR', 0, 'C', 1);
            $pdf->Cell(80, $height, $lsNama[$lstKend], 'TBLR', 0, 'L', 1);
            $pdf->Cell(45, $height, $lstKend, 'TBLR', 0, 'L', 1);
            $pdf->Cell(25, $height, $lsThnPerolehan[$lstKend], 'TBLR', 0, 'C', 1);
            $pdf->Cell(40, $height, @number_format($lsKm[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($dtBi[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($dtSbi[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($jmlhBbm[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($jmlhBbmSbi[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($rasioDt[$lstKend], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($dtAnggrn[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($biGaji[$lstKend], 0), 'TBLR', 0, 'R', 1);

            $pdf->Cell(20, $height, @number_format($biLembur[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($biBbm[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($biSukuCdng[$lstKend], 0), 'TBLR', 0, 'R', 1);

            $pdf->Cell(20, $height, @number_format($biReparasi[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($biAsuransi[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($rTotal[$lstKend], 0), 'TBLR', 0, 'R', 1);

            $pdf->Cell(20, $height, @number_format($sBiGaji[$lstKend], 0), 'TBLR', 0, 'R', 1);

            $pdf->Cell(20, $height, @number_format($sBiLembur[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($sBiBbm[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($sBiSukuCdng[$lstKend], 0), 'TBLR', 0, 'R', 1);

            $pdf->Cell(20, $height, @number_format($sBiReparasi[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($sBiAsuransi[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($rTotalSbi[$lstKend], 0), 'TBLR', 0, 'R', 1);

            $pdf->Cell(40, $height, @number_format($zData[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($aaDta[$lstKend], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(20, $height, @number_format($abDta[$lstKend], 0), 'TBLR', 1, 'R', 1);
        }
        $pdf->Output();
        break;



    default:
        break;
}
?>