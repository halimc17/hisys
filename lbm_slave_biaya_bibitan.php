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

$proses = checkPostGet('proses', '');
$kdUnit = checkPostGet('kdUnit', '');
$periode = checkPostGet('periode', '');


$tahun = substr($periode, 0, 4);


$unitId = $_SESSION['lang']['all'];
$nmPrshn = "Holding";
$purchaser = $_SESSION['lang']['all'];
if ($periode == '') {
    exit("Error: " . $_SESSION['lang']['periode'] . " required");
}
if ($kdUnit != '') {
    $unitId = $optNmOrg[$kdUnit];
} else {
    exit("Error:" . $_SESSION['lang']['unit'] . " Tidak boleh kosong");
}
$thn = explode("-", $periode);
$bln = intval($thn[1]);
$thnLalu = $thn[0];
if (strlen($bln) < 2) {
    if ($thn[1] == '1') {
        $blnLalu = 12;
        $thnLalu = $thn[0] - 1;
    } else {

        $blnLalu = "0" . ($bln - 1);
    }
} else {
    $blnLalu = $bln - 1;
}
$blnLalu = addZero($blnLalu,2);
//exit("error:".$blnLalu);
$addstr = "";
$prdblnlalu = $thnLalu . "-" . $blnLalu;
$addstr.="(";
for ($w = 1; $w <= $bln; $w++) {
    if ($w < 10) {
        $jack = "rp0" . $w;
    } else {
        $jack = "rp" . $w;
    }
    if ($w < $bln)
        $addstr.=$jack . "+";
    else
        $addstr.=$jack;
}
$addstr.=")";
//A1
//exit("Error:".$addstr);
$sData = "select distinct volume from " . $dbname . ".bgt_budget_bibit_vw where kebun='" . $kdUnit . "' and tahunbudget='" . $thn[0] . "'";

$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
$rData = $qData->fetch();
$dtA1 = $rData['volume'];

//A2
$sDataA2 = "select distinct  rp" . $blnLalu . "  from " . $dbname . ".bgt_lbm_porsi_kebun_vw where 
         kegiatan ='128020202' and tahunbudget='" . $thn[0] . "' and kebun='" . $kdUnit . "'";

$qDataA2 = $owlPDO->query($sDataA2) or die(print " Gagal: " . PDOException::getMessage());
$qDataA2->setFetchMode(PDO::FETCH_ASSOC);
$rDataA2 = $qDataA2->fetch();
$dtA2 = $dtA1 * $rDataA2['rp' . $blnLalu];


//A3
$sDataA3 = "select $addstr as jumlah  from " . $dbname . ".bgt_lbm_porsi_kebun_vw 
         where kegiatan ='128020202' and tahunbudget='" . $thn[0] . "' and kebun='" . $kdUnit . "'";

$qDataA3 = $owlPDO->query($sDataA3) or die(print " Gagal: " . PDOException::getMessage());
$qDataA3->setFetchMode(PDO::FETCH_ASSOC);
$rDataA3 = $qDataA3->fetch();
$dtA3 = $dtA1 * $rDataA3['jumlah'];




//A4
//$sDataA4="select sum(jumlah) as jlhM1 from ".$dbname.".bibitan_mutasi  where tanggal < LAST_DAY('".$periode."-15')    and kodeorg like '".$kdUnit."%'";
$sDataA4 = "select sum(jumlah) as jlhM1 from " . $dbname . ".bibitan_mutasi  where tanggal like '%" . $periode . "%'   and kodeorg like '" . $kdUnit . "%'";

$qDataA4 = $owlPDO->query($sDataA4) or die(print " Gagal: " . PDOException::getMessage());
$qDataA4->setFetchMode(PDO::FETCH_ASSOC);
$rDataA4 = $qDataA4->fetch();
$dtA4 = $rDataA4['jlhM1'];
//echo $sDataA4;
//A5
$sDataA5 = "select sum(jumlah) as jlhM1 from " . $dbname . ".bibitan_mutasi  
          where tanggal < LAST_DAY('" . $periode . "-15')  and kodetransaksi in('TMB','AFB','DBT')   and kodeorg like '" . $kdUnit . "%'";

$qDataA5 = $owlPDO->query($sDataA5) or die(print " Gagal: " . PDOException::getMessage());
$qDataA5->setFetchMode(PDO::FETCH_ASSOC);
$rDataA5 = $qDataA5->fetch();
$dtA5 = $rDataA5['jlhM1'];

//B1
//$sDataB1="select (rp01+rp02+rp03+rp04+rp05+rp06+rp07+rp08+rp09+rp10+rp11+rp12) as setahun from ".$dbname.".bgt_lbm_porsi_kebun_vw 
//          where kebun='".$kdUnit."' and tahunbudget='".$thn[0]."' and kegiatan='126050501'";
// 126050501 PENGECERAN DAN PENANAMAN BIBIT TB
// 126050502 Tanam Sawit Completering TBM
// 126050503 Pengeceran dan Penanaman Bibit TBM
// 126050504 Tanam Sawit Sisipan TBM
// 126120101 PENYISIPAN TBM
// 621070101 PENYISIPAN TM
$sDataB1 = "select (rp01+rp02+rp03+rp04+rp05+rp06+rp07+rp08+rp09+rp10+rp11+rp12) as setahun from " . $dbname . ".bgt_lbm_porsi_kebun_vw 
          where kebun='" . $kdUnit . "' and tahunbudget='" . $thn[0] . "' and kegiatan in  ('126050501', '126050502', '126050503', '126050504', '126120101', '621070101')";

$qDataB1 = $owlPDO->query($sDataB1) or die(print " Gagal: " . PDOException::getMessage());
$qDataB1->setFetchMode(PDO::FETCH_ASSOC);
$rDataB1 = $qDataB1->fetch();
$dtB1 = $rDataB1['setahun'];

//B2
$sDataB2 = "select rp" . $blnLalu . " as BI from " . $dbname . ".bgt_lbm_porsi_kebun_vw 
          where kebun='" . $kdUnit . "' and tahunbudget='" . $thn[0] . "' and kegiatan in  ('126050501', '126050502', '126050503', '126050504', '126120101', '621070101')";

$qDataB2 = $owlPDO->query($sDataB2) or die(print " Gagal: " . PDOException::getMessage());
$qDataB2->setFetchMode(PDO::FETCH_ASSOC);
$rDataB2 = $qDataB2->fetch();
$dtB2 = $rDataB2['rp' . $blnLalu] * $dtA1;

//B3
$sDataB3 = "select $addstr as SBI from " . $dbname . ".bgt_lbm_porsi_kebun_vw 
          where kebun='" . $kdUnit . "' and tahunbudget='" . $thn[0] . "' and kegiatan in  ('126050501', '126050502', '126050503', '126050504', '126120101', '621070101')";

$qDataB3 = $owlPDO->query($sDataB3) or die(print " Gagal: " . PDOException::getMessage());
$qDataB3->setFetchMode(PDO::FETCH_ASSOC);
$rDataB3 = $qDataB3->fetch();
$dtB3 = $rDataB3['SBI'] * $dtA1;

//B4
$sDataB4 = "select sum(jumlah) as jlhM1 from " . $dbname . ".bibitan_mutasi  
          where tanggal like '" . $periode . "%'  and kodetransaksi in('PNB')   and kodeorg like '" . $kdUnit . "%'";

$qDataB4 = $owlPDO->query($sDataB4) or die(print " Gagal: " . PDOException::getMessage());
$qDataB4->setFetchMode(PDO::FETCH_ASSOC);
$rDataB4 = $qDataB4->fetch();
$dtB4 = $rDataB4['jlhM1'];

//B5
$sDataB5 = "select sum(jumlah)*-1 as jlhM1 from " . $dbname . ".bibitan_mutasi 
          where tanggal <LAST_DAY('" . $periode . "-15')  and kodetransaksi in('PNB')   and kodeorg like '" . $kdUnit . "%'";

$qDataB5 = $owlPDO->query($sDataB5) or die(print " Gagal: " . PDOException::getMessage());
$qDataB5->setFetchMode(PDO::FETCH_ASSOC);
$rDataB5 = $qDataB5->fetch();
$dtB5 = $rDataB5['jlhM1'];



//noakun
$sDnoakun = "select noakun,namaakun,namaakun1 from " . $dbname . ".keu_5akun 
           where length(noakun)=7 and left(noakun,5) between '12801' and '12812' order by noakun asc";

$qDnoakun = $owlPDO->query($sDnoakun) or die(print " Gagal: " . PDOException::getMessage());
$qDnoakun->setFetchMode(PDO::FETCH_ASSOC);
while ($rDnoakun = $qDnoakun->fetch()) {
    $lstNoakun[] = $rDnoakun['noakun'];
    if ($_SESSION['language'] == 'EN') {
        $nmAkun[$rDnoakun['noakun']] = $rDnoakun['namaakun1'];
    } else {
        $nmAkun[$rDnoakun['noakun']] = $rDnoakun['namaakun'];
    }
}

//xx
$sDataxx = "select  noakun, count(noakun) as rotasi from " . $dbname . ".kebun_perawatan_vw where tanggal like '" . $periode . "%' and unit='" . $kdUnit . "' and noakun 
         in(select noakun from " . $dbname . ".keu_5akun  where length(noakun)=7 and left(noakun,5) between '12801' and '12812' order by noakun)  group by noakun";

$qDataxx = $owlPDO->query($sDataxx) or die(print " Gagal: " . PDOException::getMessage());
$qDataxx->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataxx = $qDataxx->fetch()) {
    @$dtXxRot[$rDataxx['noakun']]+=$rDataxx['rotasi'];
}
//z1
$dr = str_replace("-", "", $periode);


$sDataZ1 = "select noakun,sum(jumlah) as awal" . $blnLalu . " from " . $dbname . ".keu_jurnaldt
          where left(tanggal,7) between '" . $thnLalu . "-01' and '" . $prdblnlalu . "' and kodeorg='" . $kdUnit . "' 
          and noakun in(select noakun from " . $dbname . ".keu_5akun where length(noakun)=7 and left(noakun,5) between '12801' and '12812' order by noakun)
          group by noakun order by noakun";

$qDataZ1 = $owlPDO->query($sDataZ1) or die(print " Gagal: " . PDOException::getMessage());
$qDataZ1->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataZ1 = $qDataZ1->fetch()) {
    @$dtZ1[$rDataZ1['noakun']]+=$rDataZ1['awal' . $blnLalu];
}

//z2
$sDataZ2 = "select noakun,setahun from " . $dbname . ".bgt_summary_biaya_vw where 
         tahunbudget='" . $thn[0] . "' and unit='" . $kdUnit . "' and 
         noakun in(select noakun from " . $dbname . ".keu_5akun where length(noakun)=7 and left(noakun,5) between '12801' and '12812' order by noakun) order by noakun";
$qDataZ2 = $owlPDO->query($sDataZ2) or die(print " Gagal: " . PDOException::getMessage());
$qDataZ2->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataZ2 = $qDataZ2->fetch()) {
    $dtZ2[$rDataZ2['noakun']]+=$rDataZ2['setahun'];
}

//z3
$sDataZ3 = "select rp" . $blnLalu . " as BI,noakun from " . $dbname . ".bgt_summary_biaya_vw 
          where tahunbudget='" . $thn[0] . "' and unit='" . $kdUnit . "' and 
          noakun in(select noakun from " . $dbname . ".keu_5akun where length(noakun)=7 and left(noakun,5) between '12801' and '12812' order by noakun) order by noakun";

$qDataZ3 = $owlPDO->query($sDataZ3) or die(print " Gagal: " . PDOException::getMessage());
$qDataZ3->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataZ3 = $qDataZ3->fetch()) {
    @$dtZ3[$rDataZ3['noakun']]+=$rDataZ3['BI'];
}
//Z4
$sDataZ4 = "select $addstr as SBI,noakun from " . $dbname . ".bgt_summary_biaya_vw 
          where tahunbudget='" . $thn[0] . "' and unit='" . $kdUnit . "' and 
          noakun in(select noakun from " . $dbname . ".keu_5akun where length(noakun)=7 and left(noakun,5) between '12801' and '12812' order by noakun) group by noakun order by noakun";
$qDataZ4 = $owlPDO->query($sDataZ4) or die(print " Gagal: " . PDOException::getMessage());
$qDataZ4->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataZ4 = $qDataZ4->fetch()) {
    $dtZ4[$rDataZ4['noakun']]+=$rDataZ4['SBI'];
}

//Z5
$sDataZ5 = "select sum(jumlah) as Bi,noakun from " . $dbname . ".keu_jurnaldt 
          where kodeorg='" . $kdUnit . "' and tanggal like '" . $periode . "%' and noakun 
          in(select noakun from " . $dbname . ".keu_5akun where length(noakun)=7 and left(noakun,5) between '12801' and '12812' order by noakun) group by noakun order by noakun";
$qDataZ5 = $owlPDO->query($sDataZ5) or die(print " Gagal: " . PDOException::getMessage());
$qDataZ5->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataZ5 = $qDataZ5->fetch()) {
    @$dtZ5[$rDataZ5['noakun']]+=$rDataZ5['Bi'];
}
//echo $sDataZ5;
//Z6
$sDataZ6 = "select sum(jumlah) as SBI,noakun from " . $dbname . ".keu_jurnaldt where 
          kodeorg='" . $kdUnit . "' and (tanggal between '" . $thn[0] . "-01-01' and LAST_DAY('" . $periode . "-15')) and noakun in 
          (select noakun from " . $dbname . ".keu_5akun where length(noakun)=7 and left(noakun,5) between '12801' and '12812' order by noakun) group by noakun order by noakun";
$qDataZ6 = $owlPDO->query($sDataZ6) or die(print " Gagal: " . PDOException::getMessage());
$qDataZ6->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataZ6 = $qDataZ6->fetch()) {
    @$dtZ6[$rDataZ6['noakun']]+=$rDataZ6['SBI'];
}


#######
##pta##
#######
##setahun 
$iptarpthn = "select sum(rupiah) as rupiah,noakun from " . $dbname . ".pta_dt_blok_vw where "
        . " tahun='" . $thn[0] . "' and unit='" . $kdUnit . "' "
        . " and length(noakun)=7 and left(noakun,5) between '12801' and '12812' group by noakun";
$nptarpthn = $owlPDO->query($iptarpthn) or die(print " Gagal: " . PDOException::getMessage());
$nptarpthn->setFetchMode(PDO::FETCH_ASSOC);
while ($dptarpthn = $nptarpthn->fetch()) {
    $ptathn[$dptarpthn['noakun']] = $dptarpthn['rupiah'];
}


#bulan
$iptarpbln = "select sum(rupiah) as rupiah,noakun from " . $dbname . ".pta_dt_blok_vw where "
        . " bulan='" . $periode . "' and unit='" . $kdUnit . "' "
        . " and length(noakun)=7 and left(noakun,5) between '12801' and '12812' group by noakun";
$nptarpbln = $owlPDO->query($iptarpbln) or die(print " Gagal: " . PDOException::getMessage());
$nptarpbln->setFetchMode(PDO::FETCH_ASSOC);
while ($dptarpbln = $nptarpbln->fetch()) {
    $ptabln[$dptarpbln['noakun']] = $dptarpbln['rupiah'];
}


##sd bulan ini 
$iptarpsdbln = "select sum(rupiah) as rupiah,noakun from " . $dbname . ".pta_dt_blok_vw where "
        . " (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and unit='" . $kdUnit . "'"
        . " and length(noakun)=7 and left(noakun,5) between '12801' and '12812' group by noakun";
$nptarpsdbln = $owlPDO->query($iptarpsdbln) or die(print " Gagal: " . PDOException::getMessage());
$nptarpsdbln->setFetchMode(PDO::FETCH_ASSOC);
while ($dptarpsdbln = $nptarpsdbln->fetch()) {
    $ptasdbln[$dptarpsdbln['noakun']] = $dptarpsdbln['rupiah'];
}




$brdr = 0;
$bgcoloraja = '';


$tab.="<div style=clear:both;>&nbsp;</div>";
$tab.="<table border=0 ><tr><td>";
$tab.="<table cellspacing=1 cellpadding=1 border=" . $brdr . " class=sortable>
	<thead class=rowheader>";
$tab.="<tr><td rowspan=3 " . $bgcoloraja . ">" . $_SESSION['lang']['noakun'] . "</td>";
$tab.="<td rowspan=3 " . $bgcoloraja . ">" . $_SESSION['lang']['uraian'] . "</td>";
$tab.="<td rowspan=3 " . $bgcoloraja . ">" . $_SESSION['lang']['rotasi'] . "</td>";
$tab.="<td rowspan=3 " . $bgcoloraja . ">" . $_SESSION['lang']['saldoawal'] . "</td>";
$tab.="<td colspan=10 " . $bgcoloraja . ">" . $_SESSION['lang']['cost'] . " (Rp)</td>";

$tab.="<td colspan=5 " . $bgcoloraja . ">COST/" . $_SESSION['lang']['bibit'] . "</td></tr>";

$tab.="<tr><td colspan=3 " . $bgcoloraja . ">" . $_SESSION['lang']['anggaran'] . "</td>";
$tab.="<td colspan=3 " . $bgcoloraja . ">PTA</td>";
$tab.="<td colspan=2 " . $bgcoloraja . ">realisasi</td>";
$tab.="<td colspan=2 " . $bgcoloraja . ">% " . $_SESSION['lang']['pencapaian'] . "</td>
            <td colspan=3 " . $bgcoloraja . ">" . $_SESSION['lang']['anggaran'] . " </td><td colspan=2 " . $bgcoloraja . ">" . $_SESSION['lang']['realisasi'] . "</td></tr>";


$tab.="<tr><td " . $bgcoloraja . ">" . $_SESSION['lang']['setahun'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td>";
$tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['setahun'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td>";
$tab.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td>
            <td " . $bgcoloraja . ">" . $_SESSION['lang']['setahun'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . "</td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . "</td></tr>";

$tab.="</thead><tbody>";
foreach ($lstNoakun as $dtNoakun) {
    $tab.="<tr class=rowcontent><td>" . $dtNoakun . "</td>";
    $tab.="<td>" . $nmAkun[$dtNoakun] . "</td>";
    $tab.="<td align=right>" . @number_format($dtXxRot[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtZ1[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtZ2[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtZ3[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtZ4[$dtNoakun], 0) . "</td>";

    $tab.="<td align=right>" . @number_format($ptathn[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($ptabln[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($ptasdbln[$dtNoakun], 0) . "</td>";


    $tab.="<td align=right>" . @number_format($dtZ5[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtZ6[$dtNoakun], 0) . "</td>";
    //Z6/Z4*100
    @$dtZ7[$dtNoakun] = $dtZ6[$dtNoakun] / $dtZ2[$dtNoakun] * 100;
    @$dtZ8[$dtNoakun] = $dtZ6[$dtNoakun] / $dtZ4[$dtNoakun] * 100;
    $tab.="<td align=right>" . @number_format($dtZ7[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtZ8[$dtNoakun], 0) . "</td>";
//            $tab.="</tr>";

    @$dtZ9[$dtNoakun] = $dtZ2[$dtNoakun] / $dtA1;
    @$dtZ10[$dtNoakun] = $dtZ3[$dtNoakun] / $dtA2;
    @$dtZ11[$dtNoakun] = $dtZ4[$dtNoakun] / $dtA3;
    @$dtZ12[$dtNoakun] = $dtZ5[$dtNoakun] / $dtA4;
    @$dtZ13[$dtNoakun] = $dtZ6[$dtNoakun] / $dtA5;
//             $tab.="<tr class=rowcontent>";
    $tab.="<td align=right>" . @number_format($dtZ9[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtZ10[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtZ11[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtZ12[$dtNoakun], 0) . "</td>";
    $tab.="<td align=right>" . @number_format($dtZ13[$dtNoakun], 0) . "</td>";
    $tab.="</tr>";
    @$totsaldoAwal+=$dtZ1[$dtNoakun];
    @$totAnggarn+=$dtZ2[$dtNoakun];
    @$totAnggarnBi+=$dtZ3[$dtNoakun];
    @$totAnggarnSbi+=$dtZ4[$dtNoakun];
    @$totRealisasi+=$dtZ5[$dtNoakun];
    @$totRealisasiSbi+=$dtZ6[$dtNoakun];
    @$totptathn+=$ptathn[$dtNoakun];
    @$totptabln+=$ptabln[$dtNoakun];
    @$totptasdbln+=$ptasdbln[$dtNoakun];
}
$tab.="<tr class=rowcontent><td colspan=3>" . $_SESSION['lang']['total'] . "</td>";
$tab.="<td align=right>" . @number_format($totsaldoAwal, 0) . "</td>";
$tab.="<td align=right>" . @number_format($totAnggarn, 0) . "</td>";
$tab.="<td align=right>" . @number_format($totAnggarnBi, 0) . "</td>";
$tab.="<td align=right>" . @number_format($totAnggarnSbi, 0) . "</td>";

$tab.="<td align=right>" . @number_format($totptathn, 0) . "</td>";
$tab.="<td align=right>" . @number_format($totptabln, 0) . "</td>";
$tab.="<td align=right>" . @number_format($totptasdbln, 0) . "</td>";




$tab.="<td align=right>" . @number_format($totRealisasi, 0) . "</td>";
$tab.="<td align=right>" . @number_format($totRealisasiSbi, 0) . "</td>";
@$totKinbi = $totRealisasiSbi / $totAnggarnBi * 100;
@$totKinbiSbi = $totRealisasiSbi / $totAnggarnSbi * 100;
$tab.="<td align=right>" . @number_format($totKinbi, 0) . "</td>";
$tab.="<td align=right>" . @number_format($totKinbiSbi, 0) . "</td>";
@$cst1 = $totAnggarn / $dtA1;
@$cst2 = $totAnggarnBi / $dtA2;
@$cst3 = $totAnggarnSbi / $dtA3;
@$cst4 = $totRealisasi / $dtA4;
@$cst5 = $totRealisasiSbi / $dtA5;
$tab.="<td align=right>" . @number_format($cst1, 0) . "</td>";
$tab.="<td align=right>" . @number_format($cst2, 0) . "</td>";
$tab.="<td align=right>" . @number_format($cst3, 0) . "</td>";
$tab.="<td align=right>" . @number_format($cst4, 0) . "</td>";
$tab.="<td align=right>" . @number_format($cst5, 0) . "</td>";
$tab.="</tr>";
$tab.="</tbody></table></td><td>&nbsp;</td><td valign=top>";


$tab.="</table>";

switch ($proses) {
    case'preview':
        echo $tab;
        break;
    case'excel':
        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        $nop_ = "biayabibitan_" . $dte;
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


                $this->SetFont('Arial', 'B', 8);
                $this->Cell($width, $height, "22 " . strtoupper($_SESSION['lang']['stok'] . " " . $_SESSION['lang']['bibit']), 0, 1, 'L');
                $this->Cell($width, $height, $_SESSION['lang']['bulan'] . ' : ' . substr(tanggalnormal($periode), 1, 7), 0, 1, 'R');
                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $this->Cell($width, $height, $_SESSION['lang']['unit'] . ' : ' . $optNmOrg[$kdUnit], 0, 1, 'L');
                $this->Cell(790, $height, ' ', 0, 1, 'R');
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
//            $pdf->SetFillColor(255,255,255);
//            $pdf->SetFont('Arial','B',5);
        $tinggiAkr = $pdf->GetY();
        $ksamping = $pdf->GetX();

        $pdf->SetY($tinggiAkr + 20);
        $pdf->SetX($ksamping + 500);
        $pdf->SetFont('Arial', 'B', 6);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(65, $height, " ", R, 0, 'C', 1);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(135, $height, $_SESSION['lang']['anggaran'], TLR, 0, 'C', 1);
        $pdf->Cell(90, $height, $_SESSION['lang']['realisasi'], TLR, 1, 'C', 1);
        $tinggiAkr = $pdf->GetY();
        $ksamping = $pdf->GetX();

        $pdf->SetY($tinggiAkr);
        $pdf->SetX($ksamping + 500);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(65, $height, " ", R, 0, 'C', 1);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(45, $height, $_SESSION['lang']['setahun'], TBLR, 0, 'C', 1);
        $pdf->Cell(45, $height, "BI", TBLR, 0, 'C', 1);
        $pdf->Cell(45, $height, "S/D BI", TBLR, 0, 'C', 1);
        $pdf->Cell(45, $height, "BI", TBLR, 0, 'C', 1);
        $pdf->Cell(45, $height, "S/D BI", TBLR, 1, 'C', 1);

        $pdf->SetY($tinggiAkr);
        $pdf->SetX($ksamping + 500);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(65, $height, $_SESSION['lang']['stok'] . " " . $_SESSION['lang']['bibit'], TBLR, 0, 'C', 1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(45, $height, @number_format($dtA1, 0), TBLR, 0, 'R', 1);
        $pdf->Cell(45, $height, @number_format($dtA2, 0), TBLR, 0, 'R', 1);
        $pdf->Cell(45, $height, @number_format($dtA3, 0), TBLR, 0, 'R', 1);
        $pdf->Cell(45, $height, @number_format($dtA4, 0), TBLR, 0, 'R', 1);
        $pdf->Cell(45, $height, @number_format($dtA5, 0), TBLR, 1, 'R', 1);
        $pdf->SetY($tinggiAkr + 10);
        $pdf->SetX($ksamping + 500);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(65, $height, "Pengiriman Bibit", TBLR, 0, 'C', 1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(45, $height, @number_format($dtB1, 0), TBLR, 0, 'R', 1);
        $pdf->Cell(45, $height, @number_format($dtB2, 0), TBLR, 0, 'R', 1);
        $pdf->Cell(45, $height, @number_format($dtB3, 0), TBLR, 0, 'R', 1);
        $pdf->Cell(45, $height, @number_format($dtB4, 0), TBLR, 0, 'R', 1);
        $pdf->Cell(45, $height, @number_format($dtB5, 0), TBLR, 1, 'R', 1);
        $pdf->SetY($tinggiAkr + 35);
        $pdf->SetX($ksamping);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(55, $height, $_SESSION['lang']['noakun'], TLR, 0, 'C', 1);
        $pdf->Cell(125, $height, $_SESSION['lang']['uraian'], TLR, 0, 'C', 1);
        $pdf->Cell(50, $height, $_SESSION['lang']['rotasi'], TLR, 0, 'C', 1);
        $pdf->Cell(50, $height, $_SESSION['lang']['saldoawal'], TLR, 0, 'C', 1);
        $pdf->Cell(290, $height, $_SESSION['lang']['biaya'] . " (Rp)", TLR, 0, 'C', 1);
        $pdf->SetY($tinggiAkr + 35);
        $pdf->SetX($ksamping + 580);
        $pdf->Cell(210, $height, "COST/" . $_SESSION['lang']['bibit'], TLR, 1, 'C', 1);

        $pdf->SetY($tinggiAkr + (35 + $height));
        $pdf->SetX($ksamping);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(55, $height, " ", LR, 0, 'C', 1);
        $pdf->Cell(125, $height, " ", LR, 0, 'C', 1);
        $pdf->Cell(50, $height, " ", LR, 0, 'C', 1);
        $pdf->Cell(50, $height, " ", LR, 0, 'C', 1);
        $pdf->Cell(130, $height, $_SESSION['lang']['anggaran'], TBLR, 0, 'C', 1);
        $pdf->Cell(80, $height, $_SESSION['lang']['realisasi'], TBLR, 0, 'C', 1);
        $pdf->Cell(80, $height, "% " . $_SESSION['lang']['pencapaian'], TBLR, 0, 'C', 1);
        $pdf->SetY($tinggiAkr + (35 + $height));
        $pdf->SetX($ksamping + 580);
        $pdf->Cell(130, $height, $_SESSION['lang']['anggaran'], TBLR, 0, 'C', 1);
        $pdf->Cell(80, $height, $_SESSION['lang']['realisasi'], TBLR, 1, 'C', 1);

        $pdf->SetY($tinggiAkr + (45 + $height));
        $pdf->SetX($ksamping);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(55, $height, " ", BLR, 0, 'C', 1);
        $pdf->Cell(125, $height, " ", BLR, 0, 'C', 1);
        $pdf->Cell(50, $height, " ", BLR, 0, 'C', 1);
        $pdf->Cell(50, $height, " ", BLR, 0, 'C', 1);
        $pdf->Cell(50, $height, $_SESSION['lang']['setahun'], TBLR, 0, 'C', 1);
        $pdf->Cell(40, $height, "BI", TBLR, 0, 'C', 1);
        $pdf->Cell(40, $height, "S/D BI", TBLR, 0, 'C', 1);
        $pdf->Cell(40, $height, "BI", TBLR, 0, 'C', 1);
        $pdf->Cell(40, $height, "S/D BI", TBLR, 0, 'C', 1);
        $pdf->Cell(40, $height, $_SESSION['lang']['setahun'], TBLR, 0, 'C', 1);
        $pdf->Cell(40, $height, "S/D BI", TBLR, 0, 'C', 1);

        $pdf->SetY($tinggiAkr + (45 + $height));
        $pdf->SetX($ksamping + 580);
        $pdf->Cell(50, $height, $_SESSION['lang']['setahun'], TBLR, 0, 'C', 1);
        $pdf->Cell(40, $height, "BI", TBLR, 0, 'C', 1);
        $pdf->Cell(40, $height, "S/D BI", TBLR, 0, 'C', 1);
        $pdf->Cell(40, $height, "BI", TBLR, 0, 'C', 1);
        $pdf->Cell(40, $height, "S/D BI", TBLR, 1, 'C', 1);
        $dar = 0;
        $darb = 0;
        $pdf->SetFillColor(255, 255, 255);
        foreach ($lstNoakun as $dtNoakun) {
            $dar+=10;
            $pdf->SetY($tinggiAkr + (55 + $dar));
            $pdf->SetX($ksamping);
            $pdf->Cell(55, $height, $dtNoakun, TBLR, 0, 'C', 1);
            $pdf->Cell(125, $height, $nmAkun[$dtNoakun], TBLR, 0, 'L', 1);
            $pdf->Cell(50, $height, @number_format($dtXxRot[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($dtZ1[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(50, $height, @number_format($dtZ2[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($dtZ3[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($dtZ4[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($dtZ5[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($dtZ6[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($dtZ7[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($dtZ8[$dtNoakun], 0), TBLR, 0, 'R', 1);
        }
        foreach ($lstNoakun as $dtNoakun) {
            $darb+=10;
            $pdf->SetY($tinggiAkr + (55 + $darb));
            $pdf->SetX($ksamping + 580);
            $pdf->Cell(50, $height, @number_format($dtZ9[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($dtZ10[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($dtZ11[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($dtZ12[$dtNoakun], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(40, $height, @number_format($dtZ13[$dtNoakun], 0), TBLR, 1, 'R', 1);
        }

        $pdf->Output();
        break;

    default:
        break;
}
?>