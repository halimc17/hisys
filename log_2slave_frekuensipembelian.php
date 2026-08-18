<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if (isset($_POST['proses'])) {
    $proses = $_POST['proses'];
} else {
    $proses = $_GET['proses'];
}

if($proses!='changefilter'){
$sKlmpk = "select kode,kelompok from " . $dbname . ".log_5klbarang order by kode";
$qKlmpk=$owlPDO->query($sKlmpk) or die(print " Gagal: ".PDOException::getMessage());
$qKlmpk->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmpk = $qKlmpk->fetch()) {
    $rKelompok[$rKlmpk['kode']] = $rKlmpk['kelompok'];
}

$kdUnit = empty($_POST['kdUnit']) ? (isset($_GET['kdUnit']) ? $_GET['kdUnit'] : '') : $_POST['kdUnit'];
$periode = empty($_POST['periode']) ? (isset($_GET['periode']) ? $_GET['periode'] : '') : $_POST['periode'];
$filter = empty($_POST['filter']) ? (isset($_GET['filter']) ? $_GET['filter'] : '') : $_POST['filter'];
$purId = empty($_POST['purId']) ? (isset($_GET['purId']) ? $_GET['purId'] : '') : $_POST['purId'];
$jenis = empty($_POST['jenis']) ? (isset($_GET['jenis']) ? $_GET['jenis'] : '') : $_POST['jenis'];

$tglAwal = isset($_GET['tglAwal']) ? $_GET['tglAwal'] : '';
$tglAkhir = isset($_GET['tglAkhir']) ? $_GET['tglAkhir'] : '';
$purchasing = isset($_GET['purchasing']) ? $_GET['purchasing'] : '';
//get data kod budget barang
$thn = explode("-", $periode);
$unitId = $_SESSION['lang']['all'];
$nmPrshn = "Holding";
$purchaser = $_SESSION['lang']['all'];
if ($periode != '' && $filter=='Bulanan') {
    $whered = " substr(tanggal,1,7)='" . $periode . "'";
    $whereb = " and substr(tanggal,1,7)='" . $periode . "'";
}
elseif ($periode != '' && $filter=='Tahunan') {
    $whered = " substr(tanggal,1,4)='" . $periode . "'";
    $whereb = " and substr(tanggal,1,4)='" . $periode . "'";
}
elseif ($periode != '' && $filter=='') {
    $whered = " substr(tanggal,1,7)='" . $periode . "'";
    $whereb = " and substr(tanggal,1,7)='" . $periode . "'";
} else {
    exit("Error: " . $_SESSION['lang']['periode'] . " tidak boleh kosong" . $periode);
}

if($jenis=='Barang')
{

$optNmOrang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNmBarang = $optNmOrang;
}
else
{

$optNmOrang = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optNmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
}
$optSatuan = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$optFranco = makeOption($dbname, 'setup_franco', 'id_franco,franco_name');
$where2 = "tipetransaksi=1 and substr(tanggal,1,7)>='" . $periode . "'";
$arrTanggal = makeOption($dbname, 'log_transaksiht', 'nopo,tanggal', $where2);
$arrTanggal = makeOption($dbname, 'log_transaksiht', 'nopo,notransaksi', $where2);
$arrBln = array("01" => $_SESSION['lang']['jan'], "02" => $_SESSION['lang']['peb'], "03" => $_SESSION['lang']['mar'], "04" => $_SESSION['lang']['apr'], "05" => $_SESSION['lang']['mei'], "06" => $_SESSION['lang']['jun']
    , "07" => $_SESSION['lang']['jul'], "08" => $_SESSION['lang']['agt'], "09" => $_SESSION['lang']['sep'], "10" => $_SESSION['lang']['okt'], "11" => $_SESSION['lang']['nov'], "12" => $_SESSION['lang']['dec']);

$where = "";
if ($kdUnit != '') {
    $where.=" and kodeorg='" . $kdUnit . "'";
    $whered.=" and kodeorg='" . $kdUnit . "'";
    $whereb.=" and kodeorg='" . $kdUnit . "'";
    $unitId = isset($optNmOrg[$kdUnit]) ? $optNmOrg[$kdUnit] : '';
}

$brdr = 0;
$bgcoloraja = '';

if ($proses == 'excel') {
    //exit("error:".$arrPilMode[$pilMode]."__".$pilMode);
    $bgcoloraja = "bgcolor=#DEDEDE";
    $brdr = 1;
    $tab = "
    <table>
    <tr><td colspan=17 align=left><b> Laporan Frekuensi Pembelian </b></td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['pt'] . " : " . $unitId . "</td></tr>
    <tr><td colspan=17 align=left> Jenis : " . $jenis . "</td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['periode'] . " : " . $periode . "</td></tr>
    </table>";
}
if($filter=='Bulanan')
{
$jumHari = cal_days_in_month(CAL_GREGORIAN, $thn[1], $thn[0]);
}
$mingguke = 1;
$tanggaldlm = 1;
$jmlh = array();
$kode = array();
if($filter=='Tahunan')
{
    foreach ($arrBln as $ky => $vle) {

            if($jenis=='Barang')
            {
            $sCount = "select  kodebarang as kode,sum(jumlahpesan) as jmlh from
            " . $dbname . ".log_po_vw where  tanggal like '" . $periode . "-" . $ky . "%'  " . $where . "  group by kodebarang";
            }
            else
            {
            $sCount = "select distinct kodesupplier as kode,count(nopo) as jmlh from
            " . $dbname . ".log_poht where  tanggal like '" . $periode . "-" . $ky . "%'  " . $where . "  group by kodesupplier";
                
            }//exit($sCount);
            $qCount=$owlPDO->query($sCount) or die(print " Gagal: ".PDOException::getMessage());
            $qCount->setFetchMode(PDO::FETCH_ASSOC);
            while ($rCount = $qCount->fetch()) {

                if ($rCount['kode'] != '') {
                    if (!isset($jmlh[$rCount['kode']][$vle]))
                        $jmlh[$rCount['kode']][$vle] = 0;
                    $jmlh[$rCount['kode']][$vle]+=intval($rCount['jmlh']);
                    $kode[$rCount['kode']] = $rCount['kode'];
                }
            }      
    }

    if($jenis=='Barang')
    {
    $sTotalPo = "select sum(jumlahpesan) as jmlh,kodebarang as kode from " . $dbname . ".log_po_vw where " . $whered . " group by kodebarang";
    }
    else
    {
    $sTotalPo = "select  sum(jumlahpesan) as jmlh,kodesupplier as kode from " . $dbname . ".log_po_vw where " . $whered . " group by kodesupplier";
    }
    $qTotalPo=$owlPDO->query($sTotalPo) or die(print " Gagal: ".PDOException::getMessage());
    $qTotalPo->setFetchMode(PDO::FETCH_ASSOC);
    while ($rTotalPo = $qTotalPo->fetch()) {
        $totalPo[$rTotalPo['kode']] = $rTotalPo['jmlh'];
    }

    $sFrek = "";
    if($jenis=='Barang')
    {
    $sFrek = "select distinct count(nopo) as jmlh,kodebarang as kode from " . $dbname . ".log_po_vw where " . $whered . " group by kodebarang";
    }
    else
    {
    $sFrek = "select distinct count(nopo) as jmlh,kodesupplier as kode from " . $dbname . ".log_poht where " . $whered . " group by kodesupplier";
    }
    $qFrek=$owlPDO->query($sFrek) or die(print " Gagal: ".PDOException::getMessage());
    $qFrek->setFetchMode(PDO::FETCH_ASSOC);
    while ($rFrek = $qFrek->fetch()) {
        $totalFrek[$rFrek['kode']] = $rFrek['jmlh'];
    }

    /*$sDrPP = "select distinct a.nopp,purchaser from " . $dbname . ".log_prapoht a
             left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
             where close=2 and purchaser!='0000000000' and status!=3  " . $whereb . "  
             group by nopp,purchaser order by nopp asc";
    $qDrPP=$owlPDO->query($sDrPP) or die(print " Gagal: ".PDOException::getMessage());
    $qDrPP->setFetchMode(PDO::FETCH_ASSOC);
    while ($rDrPP = $qDrPP->fetch()) {
        if (!isset($totPP[$rDrPP['purchaser']]))
            $totPP[$rDrPP['purchaser']] = 0;
        $totPP[$rDrPP['purchaser']]+=1;
    }

    //outstanding pp
    $sOutPp = "select distinct a.nopp,purchaser from " . $dbname . ".log_prapoht a
             left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
             where close=2 and purchaser!='0000000000' and create_po!=1 and status!=3 " . $whereb . "  
             group by nopp,purchaser order by nopp asc";
    $qOutpp=$owlPDO->query($sOutPp) or die(print " Gagal: ".PDOException::getMessage());
    $qOutpp->setFetchMode(PDO::FETCH_ASSOC);
    $outPP = array();
    while ($rOutpp = $qOutpp->fetch()) {
        if (!isset($outPP[$rOutpp['purchaser']]))
            $outPP[$rOutpp['purchaser']] = 0;
        $outPP[$rOutpp['purchaser']]+=1;
    }*/

    $tab = "<table cellspacing=1 border=" . $brdr . " class=sortable>
        <thead class=rowheader>
        <tr>
            <td " . $bgcoloraja . " rowspan=2  align=center>No.</td>
            <td " . $bgcoloraja . " rowspan=2  align=center>" . $_SESSION['lang']['supplier'] . " / " . $_SESSION['lang']['kodebarang'] . "</td>
            <td " . $bgcoloraja . " rowspan=2  align=center>Frekuensi</td>";
    foreach ($arrBln as $kyd => $bln) {
    $tab.=" <td " . $bgcoloraja . " rowspan=2 align=center>" . $bln . "</td>";
    }
    $tab.="<td " . $bgcoloraja . " rowspan=2  align=center>" . $_SESSION['lang']['total'] . " Qty</td>";
    $tab.="</tr>";
    $tab.="</thead>
        <tbody>";

    $dtcek = count($kode);
    if ($dtcek != 0) {
        $not = 0;
        foreach ($kode as $dtPur) {
            $not++;
            $tab.="<tr class=rowcontent><td align=center>" . $not . "</td>";
            $tab.="<td>" . $optNmOrang[$dtPur] . "</td>";
            $tab.="<td  align=right onclick=\"detailPP(event,'log_2slave_frekuensipembelian.php','" . $dtPur . "','0')\" style=cursor:pointer>".(isset($totalFrek[$dtPur]) ? $totalFrek[$dtPur] : 0)."</td>";
            foreach ($arrBln as $ky => $blns) {
                if (empty($jmlh[$dtPur][$blns])) {
                    $tab.="<td align=right style=cursor:pointer>0</td>";
                } else {
                    $tab.="<td align=right onclick=detailData(event,'log_2slave_frekuensipembelian.php','".$ky."-01','" .$ky."-31','" . $dtPur . "')
                           style=cursor:pointer>" . $jmlh[$dtPur][$blns] . "</td>";
                }
            }
            $tab.="<td  align=right onclick=detailData(event,'log_2slave_frekuensipembelian.php','','','" . $dtPur . "')
                       style=cursor:pointer>" . $totalPo[$dtPur] . "</td>";
           $tab.="</tr>";
        }
    } else {
        $tab.="<tr class=rowcontent><td colspan=31>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
    }
$tab.="</tbody></table>";
}
else{
    for ($ard = 1; $ard <= $jumHari;) {
        $tanggal = $periode . "-" . $ard;
        $query = "SELECT datediff('$tanggal', CURDATE()) as selisih";
    	$hasil=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
    	$hasil->setFetchMode(PDO::FETCH_ASSOC);
        $data = $hasil->fetch();
    	
        $selisih = $data['selisih'];

        $x = mktime(0, 0, 0, date("m"), date("d") + $selisih, date("Y"));
        $namahari = date("l", $x);

        if ($namahari == "Sunday")
            $awalitung = 1;
        else if ($namahari == "Monday")
            $awalitung = 2;
        else if ($namahari == "Tuesday")
            $awalitung = 3;
        else if ($namahari == "Wednesday")
            $awalitung = 4;
        else if ($namahari == "Thursday")
            $awalitung = 5;
        else if ($namahari == "Friday")
            $awalitung = 6;
        else if ($namahari == "Saturday")
            $awalitung = 7;
        //$tanggaldlm=0;
        $tglAwal[$mingguke] = $ard;
        for ($awal = $awalitung; $awal <= 7; $awal++) {
            $tglan = $tanggaldlm;

            if ($tanggaldlm < 10) {
                $tglan = "0" . $tanggaldlm;
            }

            $sCount ="";
            if($jenis=='Barang')
            {
                $sCount = "select distinct kodebarang as kode,sum(jumlahpesan) as jmlh from
            " . $dbname . ".log_po_vw where  tanggal='" . $periode . "-" . $tglan . "'  " . $where . "  group by kodebarang";
            }
            else
            {
                $sCount = "select distinct kodesupplier as kode,count(nopo) as jmlh from
            " . $dbname . ".log_poht where  tanggal='" . $periode . "-" . $tglan . "'  " . $where . "  group by kodesupplier";
            }
            
    		$qCount=$owlPDO->query($sCount) or die(print " Gagal: ".PDOException::getMessage());
    		$qCount->setFetchMode(PDO::FETCH_ASSOC);
            while ($rCount = $qCount->fetch()) {

                if ($rCount['kode'] != '') {
                    if (!isset($jmlh[$rCount['kode']][$mingguke]))
                        $jmlh[$rCount['kode']][$mingguke] = 0;
                    $jmlh[$rCount['kode']][$mingguke]+=intval($rCount['jmlh']);
                    $kode[$rCount['kode']] = $rCount['kode'];
                }
            }
            $tanggaldlm+=1;
        }
        if ($tanggaldlm > $jumHari) {
            $tglAkhir[$mingguke] = $jumHari;
        } else {
            $tglAkhir[$mingguke] = $tanggaldlm - 1;
        }
        $ard = $tanggaldlm;
        if ($ard < $jumHari) {
            $mingguke+=1;
        }
    }
    $sTotalPo = "";
    if($jenis=='Barang')
    {
    $sTotalPo = "select sum(jumlahpesan) as jmlh,kodebarang as kode from " . $dbname . ".log_po_vw where " . $whered . " group by kodebarang";
    }
    else
    {
    $sTotalPo = "select  sum(jumlahpesan) as jmlh,kodesupplier as kode from " . $dbname . ".log_po_vw where " . $whered . " group by kodesupplier";
    }
    $qTotalPo=$owlPDO->query($sTotalPo) or die(print " Gagal: ".PDOException::getMessage());
    $qTotalPo->setFetchMode(PDO::FETCH_ASSOC);
    while ($rTotalPo = $qTotalPo->fetch()) {
        $totalPo[$rTotalPo['kode']] = $rTotalPo['jmlh'];
    }

    $sFrek = "";
    if($jenis=='Barang')
    {
    $sFrek = "select distinct count(nopo) as jmlh,kodebarang as kode from " . $dbname . ".log_po_vw where " . $whered . " group by kodebarang";
    }
    else
    {
    $sFrek = "select distinct count(nopo) as jmlh,kodesupplier as kode from " . $dbname . ".log_poht where " . $whered . " group by kodesupplier";
    }
    $qFrek=$owlPDO->query($sFrek) or die(print " Gagal: ".PDOException::getMessage());
    $qFrek->setFetchMode(PDO::FETCH_ASSOC);
    while ($rFrek = $qFrek->fetch()) {
        $totalFrek[$rFrek['kode']] = $rFrek['jmlh'];
    }

    //total pp
    // $sDrPP="select distinct nopp from ".$dbname.".log_prapoht 
    //         where close=2 ".$whereb."   group by nopp order by nopp asc";
    // $sDrPP = "select distinct a.nopp,purchaser from " . $dbname . ".log_prapoht a
    //          left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
    //          where close=2 and purchaser!='0000000000' and status!=3  " . $whereb . "  
    //          group by nopp,purchaser order by nopp asc";
    // $qDrPP=$owlPDO->query($sDrPP) or die(print " Gagal: ".PDOException::getMessage());
    // $qDrPP->setFetchMode(PDO::FETCH_ASSOC);
    // while ($rDrPP = $qDrPP->fetch()) {
    //     if (!isset($totPP[$rDrPP['purchaser']]))
    //         $totPP[$rDrPP['purchaser']] = 0;
    //     $totPP[$rDrPP['purchaser']]+=1;
    // }

    //outstanding pp
    // $sOutPp = "select distinct a.nopp,purchaser from " . $dbname . ".log_prapoht a
    //          left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
    //          where close=2 and purchaser!='0000000000' and create_po!=1 and status!=3 " . $whereb . "  
    //          group by nopp,purchaser order by nopp asc";
    // $qOutpp=$owlPDO->query($sOutPp) or die(print " Gagal: ".PDOException::getMessage());
    // $qOutpp->setFetchMode(PDO::FETCH_ASSOC);
    // $outPP = array();
    // while ($rOutpp = $qOutpp->fetch()) {
    //     if (!isset($outPP[$rOutpp['purchaser']]))
    //         $outPP[$rOutpp['purchaser']] = 0;
    //     $outPP[$rOutpp['purchaser']]+=1;
    // }

    $tab = "<table cellspacing=1 border=" . $brdr . " class=sortable>
    	<thead class=rowheader>
    	<tr>
            <td " . $bgcoloraja . " rowspan=2  align=center>No.</td>
            <td " . $bgcoloraja . " rowspan=2  align=center>" . $_SESSION['lang']['supplier'] . " / " . $_SESSION['lang']['kodebarang'] . "</td>
            <td " . $bgcoloraja . " rowspan=2  align=center>Frekuensi</td>
            <td " . $bgcoloraja . " colspan=" . $mingguke . "  align=center>" . $arrBln[substr($periode, 5, 2)] . "</td>
            <td " . $bgcoloraja . " rowspan=2  align=center>" . $_SESSION['lang']['total'] . " Qty</td>";
    $tab.="</tr>";
    $tab.="<tr>";
    for ($ard = 1; $ard <= $mingguke; $ard++) {
        $tab.="<td width=30px " . $bgcoloraja . " align=center>" . $ard . "</td>"; //manual
    }
    $tab.="</tr></thead>
    	<tbody>";

    $dtcek = count($kode);
    if ($dtcek != 0) {
        $not = 0;
        foreach ($kode as $dtPur) {
            $not++;
            $tab.="<tr class=rowcontent><td align=center>" . $not . "</td>";
            $tab.="<td>" . $optNmOrang[$dtPur] . "</td>";
            $tab.="<td  align=right onclick=\"detailData(event,'log_2slave_frekuensipembelian.php','" . $tglAwal[$awalmngg] . "','" . $tglAkhir[$awalmngg] . "','" . $dtPur . "','".$jenis."')\" style=cursor:pointer>".(isset($totalFrek[$dtPur]) ? $totalFrek[$dtPur] : 0)."</td>";
            for ($awalmngg = 1; $awalmngg <= $mingguke; $awalmngg++) {
                if (empty($jmlh[$dtPur][$awalmngg])) {
                    $tab.="<td align=right style=cursor:pointer>0</td>";
                } else {
                    $tab.="<td align=right onclick=detailData(event,'log_2slave_frekuensipembelian.php','" . $tglAwal[$awalmngg] . "','" . $tglAkhir[$awalmngg] . "','" . $dtPur . "','".$jenis."')
    					   style=cursor:pointer>" . $jmlh[$dtPur][$awalmngg] . "</td>";
                }
            }
            $tab.="<td  align=right onclick=detailData(event,'log_2slave_frekuensipembelian.php','','','" . $dtPur . "','".$jenis."')
                       style=cursor:pointer>" . $totalPo[$dtPur] . "</td>";
            $tab.="</tr>";
        }
    } else {
        $tab.="<tr class=rowcontent><td colspan=31>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
    }
}

$tab.="</tbody></table>";
}
switch ($proses) {
    case'getKdorg':
        //echo "warning:masuk";
        $optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sOrg = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $kdPt . "'";
		$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['namaorganisasi'] . "</option>";
        }
        echo $optorg;
        break;
    case'preview':
        echo $tab;
        break;

    case'excel':

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("YmdHms");
        $nop_ = "permintaanPembeliaan_" . $purId . "_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls.gz';
            </script>";

        break;

    case'getDetail':
        if ($_GET['tglAwal'] != '' && $_GET['tglAkhir'] != '') {
            if ($_GET['tglAwal'] < 10) {
                $_GET['tglAwal'] = "0" . $_GET['tglAwal'];
            }

            if ($_GET['tglAkhir'] < 10) {
                $_GET['tglAkhir'] = "0" . $_GET['tglAkhir'];
            }
            $wheredy = " and a.tanggal between '" . $periode . "-" . $_GET['tglAwal'] . "' and '" . $periode . "-" . $_GET['tglAkhir'] . "'";
            $tglawal = $periode . "-" . $_GET['tglAwal'];
            $tglakhir = $periode . "-" . $_GET['tglAkhir'];
            $dttglaja = $_SESSION['lang']['tanggal'] . ":" . $tglawal . " s.d. " . $tglakhir;
        } else {
            $wheredy = " and substr(a.tanggal,1,7)='" . $periode . "'";
            $dttglaja = $_SESSION['lang']['periode'] . ":" . $_GET['periode'];
        }
        if ($kdUnit != '') {
            $wheredy.=" and c.kodeorg='" . $kdUnit . "'";
        }
        $tab2 = "<link rel=stylesheet type=text/css href=style/generic.css>
            <script language=javascript1.2 src='js/generic.js'></script>
            <script language=javascript1.2 src='js/log_2produktivitas.js'></script>";
        $tab2.="<fieldset style=height:100%><legend>" . $_SESSION['lang']['detail'] . "</legend>";
        if($_GET['jenis']=='Barang')
        {
        $tab2.="" . $_SESSION['lang']['kodebarang'] . ":" . $optNmOrang[$_GET['purchasing']] . "<br />";
        }
        else
        {
        $tab2.="" . $_SESSION['lang']['supplier'] . ":" . $optNmOrang[$_GET['purchasing']] . "<br />";
        }
        $tab2.=$dttglaja . "<br />";
        $tab2.="<input type=hidden id=kdUnit value='" . $kdUnit . "' /><input type=hidden id=periode value='" . $periode . "' />";
        $tab2.="<br /><img onclick=fisikKeExcel2(event,'log_2slave_frekuensipembelian.php','" . $_GET['tglAwal'] . "','" . $_GET['tglAkhir'] . "','" . $_GET['purchasing'] . "') src=images/excel.jpg class=resicon title='MS.Excel'> ";
        if($_GET['jenis']=='Barang')
        {
            $sListData = "select distinct a.nopp,namabarang,a.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
                    c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang 
                    from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
                    left join " . $dbname . ".log_poht c on a.nopo=c.nopo
                    left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
                    where a.nopo!=''  " . $wheredy . " and e.status!='3' and a.kodebarang='" . $_GET['purchasing'] . "' 
                    group by a.kodebarang,a.nopo order by a.nopo asc";
        }
        else
        {
            $sListData = "select distinct a.nopp,namabarang,a.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
                    c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang 
                    from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
                    left join " . $dbname . ".log_poht c on a.nopo=c.nopo
                    left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
                    where a.nopo!=''  " . $wheredy . " and e.status!='3' and c.kodesupplier='" . $_GET['purchasing'] . "' 
                    group by c.kodesupplier,a.nopo order by a.nopo asc";
        }
        //echo $_GET['jenis'];
        $tab2.="<table cellspacing=1 border=" . $brdr . " class=sortable>
			<thead class=rowheader>
			<tr>
				<td align=center " . $bgcoloraja . " rowspan=2>No.</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopp'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PP</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kodebarang'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namabarang'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['satuan'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopo'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PO</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['purchaser'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['alokasi'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>O.std</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jumlahrealisasi'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jmlhPesan'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['totalharga'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tandatangan'] . "</td>";
        $tab2.="<td align=center " . $bgcoloraja . " colspan=6 align=center>" . $_SESSION['lang']['pembayaran'] . "</td>";
        $tab2.="<td align=center " . $bgcoloraja . " colspan=5 align=center>" . $_SESSION['lang']['pengiriman'] . "</td>";
        $tab2.="<td align=center " . $bgcoloraja . " colspan=4 align=center>" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="</tr>";
        $tab2.="<tr><td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tipe'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['syaratPem'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['jatuhtempo'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['noinvoice'] . "</td>"; //tagihan
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>"; //tagihan
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggalbayar'] . "</td>"; //manual
        //pengiriman
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['franco'] . "</td>"; //dari franco tgl kirim di po
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tgl_kirim'] . "</td>"; //dari tgl kirim di po
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tglterima'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['satuan'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['biaya'] . "</td>"; //manual
        //bapb
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">Copy</td>"; //manual
        $tab2.="<td  " . $bgcoloraja . ">Original</td>"; //manual

        $tab2.="</tr></thead>
		<tbody>";
		$qListData=$owlPDO->query($sListData) or die(print " Gagal: ".PDOException::getMessage());
		$qListData->setFetchMode(PDO::FETCH_ASSOC);
		$rAdaData=owlBaris($qListData);
        if ($rAdaData > 0) {
            $nopodtr = 0;
            while ($rListData = $qListData->fetch()) {
                $tglTerima = '';
                $tglEdit = '';
                if (!isset($klmpkBarang) or $klmpkBarang != $rListData['nopo']) {
                    $brs = 1;
                }
                if ($brs == 1) {
                    $no = 0;
                    $nopodtr+=1;
                    $klmpkBarang = $rListData['nopo'];
                    $tab2.="<tr class='rowcontent'>";
                    $tab2.="<td align=center><b>" . $nopodtr . "</b></td><td colspan=5><b>" . $klmpkBarang . "</b></td>";
                    $tab2.="<td colspan=26>&nbsp;</td>";
                    $tab2.="</tr>";
                    $brs = 0;
                }
                $sRealisasi = "select distinct realisasi from " . $dbname . ".log_prapodt where nopp='" . $rListData['nopp'] . "' and kodebarang='" . $rListData['kodebarang'] . "'";
				$qRealisai=$owlPDO->query($sRealisasi) or die(print " Gagal: ".PDOException::getMessage());
				$qRealisai->setFetchMode(PDO::FETCH_ASSOC);
                $rRealisasi = $qRealisai->fetch();
                $tanggalData = '';
                if (isset($statId) and $statId == '1') {
                    if ($rListData['nopo'] != '') {
                        $sTagihan = "select distinct noinvoice,tanggal from " . $dbname . ".keu_tagihanht where nopo='" . $rListData['nopo'] . "'";
						$qTagihan=$owlPDO->query($sTagihan) or die(print " Gagal: ".PDOException::getMessage());
						$qTagihan->setFetchMode(PDO::FETCH_ASSOC);
                        $rTagihan = $qTagihan->fetch();
                        $tglTerima = tanggalnormal($rTagihan['tglterima']);
                        if ($rTagihan['tanggal'] != '') {
                            $tanggalData = tanggalnormal($rTagihan['tanggal']);
                        }
                        $sTransaksi = "select distinct tanggal,notransaksi from " . $dbname . ".log_transaksiht where nopo='" . $rListData['nopo'] . "'";
						$qTransaksi=$owlPDO->query($sTransaksi) or die(print " Gagal: ".PDOException::getMessage());
						$qTransaksi->setFetchMode(PDO::FETCH_ASSOC);
                        $rTransaksi = $qTransaksi->fetch();
                        $tglTerima = tanggalnormal($rTransaksi['tanggal']);
                    }
                }
                if ($rListData['idFranco'] != '') {
                    $lokasi = $optFranco[$rListData['idFranco']];
                    $tglKirim = tanggalnormal(substr($rListData['tanggalkirim'], 0, 10));
                } else {
                    $lokasi = $rListData['lokasipengiriman'];
                    $tglKirim = tanggalnormal(substr($rListData['tanggalkirim'], 0, 10));
                }

                if ($rListData['tgledit'] != '') {
                    $tglEdit = tanggalnormal($rListData['tgledit']);
                }
                if (strlen($tglKirim) < 10) {
                    $tglKirim = '';
                }
                if (strlen($tglTerima) < 10) {
                    $tglTerima = '';
                }
                $no+=1;
                $hargaBarang = 0;
                if ($rListData['jumlahpesan'] != '') {
                    $hargaBarang = $rListData['jumlahpesan'] * $rListData['hargasatuan'];
                }

                $month1 = substr($rListData['tglAlokasi'], 5, 2);
                $date1 = substr($rListData['tglAlokasi'], 8, 2);
                $year1 = substr($rListData['tglAlokasi'], 0, 4);

                $month2 = substr($rListData['tanggal'], 5, 2);
                $date2 = substr($rListData['tanggal'], 8, 2);
                $year2 = substr($rListData['tanggal'], 0, 4);


                $jd1 = GregorianToJD($month1, $date1, $year1);
                $jd2 = GregorianToJD($month2, $date2, $year2);
                $jmlHari = $jd2 - $jd1;
                $tab2.="<tr class='rowcontent'>";
                $tab2.="<td align=center>" . $no . "</td>";
                $tab2.="<td>" . $rListData['nopp'] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglpp']) . "</td>";
                $tab2.="<td>" . $rListData['kodebarang'] . "</td>";
                $tab2.="<td>" . $optNmBarang[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $optSatuan[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $rListData['nopo'] . "</td>";
                $tab2.="<td>" . $rListData['tanggal'] . "</td>";
                $tab2.="<td>" . $optNmOrang[$rListData['purchaser']] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglAlokasi']) . "</td>";
                $tab2.="<td align=right>" . $jmlHari . "</td>";
                $tab2.="<td align=right>" . number_format($rRealisasi['realisasi'], 0) . "</td>";
                $tab2.="<td align=right>" . number_format($rListData['jumlahpesan'], 0) . "</td>";
                $tab2.="<td>" . $rListData['matauang'] . "</td>";
                $tab2.="<td align=right>" . number_format($hargaBarang, 0) . "</td>";
                $tab2.="<td>" . $rListData['namasupplier'] . "</td>";
                $tab2.="<td>" . $tglEdit . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTagihan['noinvoice']) ? $rTagihan['noinvoice'] : '') . "</td>";
                $tab2.="<td>" . $tanggalData . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . $lokasi . "</td>";
                $tab2.="<td>" . $tglKirim . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTransaksi['notransaksi']) ? $rTransaksi['notransaksi'] : '') . "</td>";
                $tab2.="<td>" . $tglTerima . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="</tr>";
            }
        } else {
            $tab2.="<tr class=rowcontent><td colspan=31>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        $tab2.="</tbody></table>";
        $tab2.="</fieldset>";
        echo $tab2;
        break;
    case 'changefilter':
    $optPeriodeCari = "";
    if($_POST['filter']=='Tahunan')
    {
        $sPeriodeCari = "select distinct substr(tanggal,1,4) as periode from " . $dbname . ".log_prapoht order by substr(tanggal,1,4) desc";
        $qPeriodeCari=$owlPDO->query($sPeriodeCari) or die(print " Gagal: ".PDOException::getMessage());
        $qPeriodeCari->setFetchMode(PDO::FETCH_ASSOC);
        while ($rPeriodeCari = $qPeriodeCari->fetch()) {
            $optPeriodeCari.="<option value='" . $rPeriodeCari['periode'] . "'>" . $rPeriodeCari['periode'] . "</option>";
        }
    }
    else
    {
        $sPeriodeCari = "select distinct substr(tanggal,1,7) as periode from " . $dbname . ".log_prapoht order by substr(tanggal,1,7) desc";
        $qPeriodeCari=$owlPDO->query($sPeriodeCari) or die(print " Gagal: ".PDOException::getMessage());
        $qPeriodeCari->setFetchMode(PDO::FETCH_ASSOC);
        while ($rPeriodeCari = $qPeriodeCari->fetch()) {
            $optPeriodeCari.="<option value='" . $rPeriodeCari['periode'] . "'>" . $rPeriodeCari['periode'] . "</option>";
        }
    }
    echo $optPeriodeCari;
    break;
    case'excelDetail':
        $bgcoloraja = "bgcolor=#DEDEDE";
        $brdr = 1;
		$wheredy='';
        if ($_GET['tglAwal'] != '' && $_GET['tglAkhir'] != '') {
            if ($_GET['tglAwal'] < 10) {
                $_GET['tglAwal'] = "0" . $_GET['tglAwal'];
            }

            if ($_GET['tglAkhir'] < 10) {
                $_GET['tglAkhir'] = "0" . $_GET['tglAkhir'];
            }
            $wheredy.=" and a.tanggal between '" . $periode . "-" . $_GET['tglAwal'] . "' and '" . $periode . "-" . $_GET['tglAkhir'] . "'";
            $tglawal = $periode . "-" . $_GET['tglAwal'];
            $tglakhir = $periode . "-" . $_GET['tglAkhir'];
            $dttglaja = $_SESSION['lang']['tanggal'] . ":" . $tglawal . " s.d. " . $tglakhir;
        } else {
            $wheredy.=" and substr(a.tanggal,1,7)='" . $periode . "'";
            $dttglaja = $_SESSION['lang']['periode'] . ":" . $_GET['periode'];
        }
        if ($kdUnit != '') {
            $wheredy.=" and c.kodeorg='" . $kdUnit . "'";
        }
		$tab2='';
        $tab2.=$_SESSION['lang']['detail'];
        $tab2.="" . $_SESSION['lang']['namakaryawan'] . ":" . $optNmOrang[$_GET['purchasing']] . "<br />";
        $tab2.=$dttglaja . "<br />";


        $sListData = "select distinct a.nopp,namabarang,a.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
                    c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang 
                    from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
                    left join " . $dbname . ".log_poht c on a.nopo=c.nopo
                    left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
                    where a.nopo!=''  " . $wheredy . " and e.status!='3' and c.purchaser='" . $_GET['purchasing'] . "' 
                    group by a.kodebarang,a.nopo order by a.nopo asc";
        // exit("Error".$sListData);
        $tab2.="<table cellspacing=1 border=" . $brdr . " class=sortable>
	<thead class=rowheader>
	<tr>
        <td " . $bgcoloraja . " rowspan=2>No.</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopp'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PP</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kodebarang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namabarang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['satuan'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopo'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PO</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['purchaser'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['alokasi'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>O.std</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jumlahrealisasi'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jmlhPesan'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['totalharga'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tandatangan'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=6 align=center>" . $_SESSION['lang']['pembayaran'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=5 align=center>" . $_SESSION['lang']['pengiriman'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=4 align=center>" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="</tr>";
        $tab2.="<tr><td " . $bgcoloraja . ">" . $_SESSION['lang']['tipe'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['syaratPem'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['jatuhtempo'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['noinvoice'] . "</td>"; //tagihan
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>"; //tagihan
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tanggalbayar'] . "</td>"; //manual
        //pengiriman
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['franco'] . "</td>"; //dari franco tgl kirim di po
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tgl_kirim'] . "</td>"; //dari tgl kirim di po
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tglterima'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['satuan'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['biaya'] . "</td>"; //manual
        //bapb
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">Copy</td>"; //manual
        $tab2.="<td  " . $bgcoloraja . ">Original</td>"; //manual

        $tab2.="</tr></thead>
	<tbody>";
		$qListData=$owlPDO->query($sListData) or die(print " Gagal: ".PDOException::getMessage());
		$qListData->setFetchMode(PDO::FETCH_ASSOC);
		$rAdaData=owlBaris($qListData);
		$nopodtr=0;
        if ($rAdaData > 0) {
            while ($rListData = $qListData->fetch()) {
                $tglTerima = '';
                $tglEdit = '';

                if ((isset($klmpkBarang) ? $klmpkBarang : '') != $rListData['nopo']) {
                    $brs = 1;
                }
                if ($brs == 1) {
                    $no = 0;
                    $nopodtr+=1;
                    $klmpkBarang = $rListData['nopo'];
                    $tab2.="<tr class='rowcontent'>";
                    $tab2.="<td><b>" . $nopodtr . "</b></td><td colspan=5><b>" . $klmpkBarang . "</b></td>";
                    $tab2.="<td colspan=25>&nbsp;</td>";
                    $tab2.="</tr>";
                    $brs = 0;
                }
                $sRealisasi = "select distinct realisasi from " . $dbname . ".log_prapodt where nopp='" . $rListData['nopp'] . "' and kodebarang='" . $rListData['kodebarang'] . "'";
				$qRealisai=$owlPDO->query($sRealisasi) or die(print " Gagal: ".PDOException::getMessage());
				$qRealisai->setFetchMode(PDO::FETCH_ASSOC);
                $rRealisasi = $qRealisai->fetch();
                if ((isset($statId) ? $statId : '') == '1') {
                    if ($rListData['nopo'] != '') {
                        $tanggalData = '';
                        $sTagihan = "select distinct noinvoice,tanggal from " . $dbname . ".keu_tagihanht where nopo='" . $rListData['nopo'] . "'";
						$qTagihan=$owlPDO->query($sTagihan) or die(print " Gagal: ".PDOException::getMessage());
						$qTagihan->setFetchMode(PDO::FETCH_ASSOC);
                        $rTagihan = $qTagihan->fetch();
                        $tglTerima = tanggalnormal($rTagihan['tglterima']);
                        if ($rTagihan['tanggal'] != '') {
                            $tanggalData = tanggalnormal($rTagihan['tanggal']);
                        }
                        $sTransaksi = "select distinct tanggal,notransaksi from " . $dbname . ".log_transaksiht where nopo='" . $rListData['nopo'] . "'";
						$qTransaksi=$owlPDO->query($sTransaksi) or die(print " Gagal: ".PDOException::getMessage());
						$qTransaksi->setFetchMode(PDO::FETCH_ASSOC);
                        $rTransaksi = $qTransaksi->fetch();
                        $tglTerima = tanggalnormal($rTransaksi['tanggal']);
                    }
                }
                if ($rListData['idFranco'] != '') {
                    $lokasi = $optFranco[$rListData['idFranco']];
                    $tglKirim = substr($rListData['tanggalkirim'], 0, 10);
                } else {
                    $lokasi = $rListData['lokasipengiriman'];
                    $tglKirim = substr($rListData['tanggalkirim'], 0, 10);
                }

                if ($rListData['tgledit'] != '') {
                    $tglEdit = $rListData['tgledit'];
                }
                if (strlen($tglKirim) < 10) {
                    $tglKirim = '';
                }
                if (strlen($tglTerima) < 10) {
                    $tglTerima = '';
                }
                $no+=1;
                $hargaBarang = 0;
                if ($rListData['jumlahpesan'] != '') {
                    $hargaBarang = $rListData['jumlahpesan'] * $rListData['hargasatuan'];
                }

                $month1 = substr($rListData['tglAlokasi'], 5, 2);
                $date1 = substr($rListData['tglAlokasi'], 8, 2);
                $year1 = substr($rListData['tglAlokasi'], 0, 4);

                $month2 = substr($rListData['tanggal'], 5, 2);
                $date2 = substr($rListData['tanggal'], 8, 2);
                $year2 = substr($rListData['tanggal'], 0, 4);


                $jd1 = GregorianToJD($month1, $date1, $year1);
                $jd2 = GregorianToJD($month2, $date2, $year2);
                $jmlHari = $jd2 - $jd1;
                $tab2.="<tr class='rowcontent'>";
                $tab2.="<td>" . $no . "</td>";
                $tab2.="<td>" . $rListData['nopp'] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglpp']) . "</td>";
                $tab2.="<td>" . $rListData['kodebarang'] . "</td>";
                $tab2.="<td>" . $optNmBarang[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $optSatuan[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $rListData['nopo'] . "</td>";
                $tab2.="<td>" . $rListData['tanggal'] . "</td>";
                $tab2.="<td>" . $optNmOrang[$rListData['purchaser']] . "</td>";
                $tab2.="<td>" . $rListData['tglAlokasi'] . "</td>";
                $tab2.="<td align=right>" . $jmlHari . "</td>";
                $tab2.="<td align=right>" . number_format($rRealisasi['realisasi'], 0) . "</td>";
                $tab2.="<td align=right>" . number_format($rListData['jumlahpesan'], 0) . "</td>";
                $tab2.="<td>" . $rListData['matauang'] . "</td>";
                $tab2.="<td align=right>" . number_format($hargaBarang, 0) . "</td>";
                $tab2.="<td>" . $rListData['namasupplier'] . "</td>";
                $tab2.="<td>" . $tglEdit . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTagihan['noinvoice']) ? $rTagihan['noinvoice'] : '') . "</td>";
                $tab2.="<td>" . (isset($tanggalData) ? $tanggalData : '') . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . $lokasi . "</td>";
                $tab2.="<td>" . $tglKirim . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTransaksi['notransaksi']) ? $rTransaksi['notransaksi'] : '') . "</td>";
                $tab2.="<td>" . $tglTerima . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="</tr>";
            }
        } else {
            $tab2.="<tr class=rowcontent><td colspan=31>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }


        $tab2.="</tbody>";
        $tab2.="</table>Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];

        $nop_ = "detailProduktivitas_" . $optNmOrang[$_GET['purchasing']];
        if (strlen($tab2) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab2)) {
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
    
    case'getPPExcel':
        $bgcoloraja = "bgcolor=#DEDEDE";
        $brdr = 1;
		$tab2='';
        $tab2.="" . $_SESSION['lang']['detail'] . "";
        $tab2.="" . $_SESSION['lang']['namakaryawan'] . ":" . $optNmOrang[$_GET['purchasing']] . "<br />";
        $tab2.= (isset($dttglaja) ? $dttglaja : '') . "<br />";

        //echo $sListData;
        $tab2.="<table cellspacing=1 border=" . $brdr . " class=sortable>
	<thead class=rowheader>
	<tr>
        <td " . $bgcoloraja . " rowspan=2>No.</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopp'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PP</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kodebarang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namabarang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['satuan'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopo'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PO</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['purchaser'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['alokasi'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>O.std</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jumlahrealisasi'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jmlhPesan'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['totalharga'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tandatangan'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=6 align=center>" . $_SESSION['lang']['pembayaran'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=5 align=center>" . $_SESSION['lang']['pengiriman'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=4 align=center>" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="</tr>";
        $tab2.="<tr><td " . $bgcoloraja . ">" . $_SESSION['lang']['tipe'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['syaratPem'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['jatuhtempo'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['noinvoice'] . "</td>"; //tagihan
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>"; //tagihan
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tanggalbayar'] . "</td>"; //manual
        //pengiriman
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['franco'] . "</td>"; //dari franco tgl kirim di po
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tgl_kirim'] . "</td>"; //dari tgl kirim di po
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tglterima'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['satuan'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['biaya'] . "</td>"; //manual
        //bapb
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">Copy</td>"; //manual
        $tab2.="<td  " . $bgcoloraja . ">Original</td>"; //manual

        $tab2.="</tr></thead>
	<tbody>";
        if ($_GET['statSql'] == 0) {
            $sNnopp = "select distinct a.nopp from " . $dbname . ".log_prapoht a 
                 left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
                 where substr(tanggal,1,7)='" . $periode . "' and purchaser='" . $_GET['purchasing'] . "' and status!=3  group by a.nopp";
        } else if ($_GET['statSql'] == 1) {
            $sNnopp = "select distinct a.nopp from " . $dbname . ".log_prapoht a 
                 left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
                 where substr(tanggal,1,7)='" . $periode . "' 
                 and purchaser='" . $_GET['purchasing'] . "' and create_po!=1 and status!=3  group by a.nopp";
        }
		
		$qNopp=$owlPDO->query($sNnopp) or die(print " Gagal: ".PDOException::getMessage());
		$qNopp->setFetchMode(PDO::FETCH_ASSOC);
		$nopodtr=0;
		while ($rNopp = $qNopp->fetch()) {
            if ($_GET['statSql'] == 0) {
                $sListData = "select distinct b.nopp,namabarang,e.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
        c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang, b.close 
        from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
        left join " . $dbname . ".log_poht c on a.nopo=c.nopo
        left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
        where a.nopp='" . $rNopp['nopp'] . "'
        group by a.kodebarang,a.nopo order by a.nopo asc";
            } else if ($_GET['statSql'] == 1) {
                $sListData = "select distinct a.*,b.*,tanggal as tglpp from " . $dbname . ".log_prapoht a
            left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
            where a.nopp='" . $rNopp['nopp'] . "'
            group by a.nopp,purchaser order by a.nopp asc";
            }
			$qListData=$owlPDO->query($sListData) or die(print " Gagal: ".PDOException::getMessage());
			$qListData->setFetchMode(PDO::FETCH_ASSOC);
            $baris=owlBaris($qListData);
            if ($baris == 0) {
                $sdata = "select distinct a.*,b.*,tanggal as tglpp from " . $dbname . ".log_prapoht a
         left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
         where a.nopp='" . $rNopp['nopp'] . "'
         group by a.nopp,purchaser order by a.nopp asc";
				$qListData=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
				$qListData->setFetchMode(PDO::FETCH_ASSOC);
            }
            while ($rListData = $qListData->fetch()) {
                $tglTerima = '';
                $tglEdit = '';

                if ((isset($klmpkBarang) ? $klmpkBarang : '') != $rListData['nopp']) {
                    $brs = 1;
                }
                if ($brs == 1) {
                    $no = 0;
                    $nopodtr+=1;
                    $klmpkBarang = $rListData['nopp'];
                    $tab2.="<tr class='rowcontent'>";
                    $tab2.="<td><b>" . $nopodtr . "</b></td><td colspan=5><b>" . $klmpkBarang . "</b></td>";
                    $tab2.="<td colspan=25>&nbsp;</td>";
                    $tab2.="</tr>";
                    $brs = 0;
                }
                $sRealisasi = "select distinct realisasi from " . $dbname . ".log_prapodt where nopp='" . $rListData['nopp'] . "' and kodebarang='" . $rListData['kodebarang'] . "'";
				$qRealisai=$owlPDO->query($sRealisasi) or die(print " Gagal: ".PDOException::getMessage());
				$qRealisai->setFetchMode(PDO::FETCH_ASSOC);
                $rRealisasi = $qRealisai->fetch();
                if ((isset($statId) ? $statId : '') == '1') {
                    if ($rListData['nopo'] != '') {
                        $tanggalData = '';
                        $sTagihan = "select distinct noinvoice,tanggal from " . $dbname . ".keu_tagihanht where nopo='" . $rListData['nopo'] . "'";
						$qTagihan=$owlPDO->query($sTagihan) or die(print " Gagal: ".PDOException::getMessage());
						$qTagihan->setFetchMode(PDO::FETCH_ASSOC);
                        $rTagihan = $qTagihan->fetch();
                        $tglTerima = tanggalnormal($rTagihan['tglterima']);
                        if ($rTagihan['tanggal'] != '') {
                            $tanggalData = tanggalnormal($rTagihan['tanggal']);
                        }
                        $sTransaksi = "select distinct tanggal,notransaksi from " . $dbname . ".log_transaksiht where nopo='" . $rListData['nopo'] . "'";
						$qTransaksi=$owlPDO->query($sTransaksi) or die(print " Gagal: ".PDOException::getMessage());
						$qTransaksi->setFetchMode(PDO::FETCH_ASSOC);
                        $rTransaksi = $qTransaksi->fetch();
                        $tglTerima = tanggalnormal($rTransaksi['tanggal']);
                    }
                }
                if ((isset($rListData['idFranco']) ? $rListData['idFranco'] : '') != '') {
                    $lokasi = $optFranco[$rListData['idFranco']];
                    $tglKirim = tanggalnormal(substr($rListData['tanggalkirim'], 0, 10));
                } else {
                    $lokasi = (isset($rListData['lokasipengiriman']) ? $rListData['lokasipengiriman'] : '');
                    $tglKirim = tanggalnormal(substr((isset($rListData['tanggalkirim']) ? $rListData['tanggalkirim'] : ''), 0, 10));
                }

                if ((isset($rListData['tgledit']) ? $rListData['tgledit'] : '') != '') {
                    $tglEdit = tanggalnormal($rListData['tgledit']);
                }
                if (strlen($tglKirim) < 10) {
                    $tglKirim = '';
                }
                if (strlen($tglTerima) < 10) {
                    $tglTerima = '';
                }
                $no+=1;
                $hargaBarang = 0;
                if ((isset($rListData['jumlahpesan']) ? $rListData['jumlahpesan'] : '') != '') {
                    $hargaBarang = $rListData['jumlahpesan'] * $rListData['hargasatuan'];
                }
                $jmlHari = 0;
                if ($rListData['close'] == '') {
                    $month1 = substr($rListData['tglAlokasi'], 5, 2);
                    $date1 = substr($rListData['tglAlokasi'], 8, 2);
                    $year1 = substr($rListData['tglAlokasi'], 0, 4);

                    $month2 = substr($rListData['tanggal'], 5, 2);
                    $date2 = substr($rListData['tanggal'], 8, 2);
                    $year2 = substr($rListData['tanggal'], 0, 4);


                    $jd1 = GregorianToJD($month1, $date1, $year1);
                    $jd2 = GregorianToJD($month2, $date2, $year2);
                    $jmlHari = $jd2 - $jd1;
                }
                $tab2.="<tr class='rowcontent'>";
                $tab2.="<td>" . $no . "</td>";
                $tab2.="<td>" . $rListData['nopp'] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglpp']) . "</td>";
                $tab2.="<td>" . $rListData['kodebarang'] . "</td>";
                $tab2.="<td>" . $optNmBarang[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $optSatuan[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $rListData['nopo'] . "</td>";
                $tab2.="<td>" . $rListData['tanggal'] . "</td>";
                $tab2.="<td>" . $optNmOrang[$rListData['purchaser']] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglAlokasi']) . "</td>";
                $tab2.="<td align=right>" . $jmlHari . "</td>";
                $tab2.="<td align=right>" . number_format($rRealisasi['realisasi'], 0) . "</td>";
                $tab2.="<td align=right>" . number_format((isset($rListData['jumlahpesan']) ? $rListData['jumlahpesan'] : 0), 0) . "</td>";
                $tab2.="<td>" . (isset($rListData['matauang']) ? $rListData['matauang'] : '') . "</td>";
                $tab2.="<td align=right>" . number_format($hargaBarang, 0) . "</td>";
                $tab2.="<td>" . (isset($rListData['namasupplier']) ? $rListData['namasupplier'] : '') . "</td>";
                $tab2.="<td>" . $tglEdit . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTagihan['noinvoice']) ? $rTagihan['noinvoice'] : '') . "</td>";
                $tab2.="<td>" . (isset($tanggalData) ? $tanggalData : '') . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . $lokasi . "</td>";
                $tab2.="<td>" . $tglKirim . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTransaksi['notransaksi']) ? $rTransaksi['notransaksi'] : '') . "</td>";
                $tab2.="<td>" . $tglTerima . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="</tr>";
            }
        }

        $tab2.="</tbody>";
        $tab2.="</table>Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];

        $nop_ = "detailProduktivitasPP_" . $optNmOrang[$_GET['purchasing']];
        if (strlen($tab2) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab2)) {
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