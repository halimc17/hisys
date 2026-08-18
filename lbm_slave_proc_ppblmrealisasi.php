<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$cetak = checkPostGet('cetak','');
$tipe = checkPostGet('tipe','');
$periode = checkPostGet('periode','');
$judul = checkPostGet('judul','');
$bln = checkPostGet('bln','');
$purchaser = checkPostGet('purchaser','');
$optNm = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
if ($proses == 'preview' || $proses == 'excel') {
    if ($periode == '') {
        exit("Error: Field required");
    }
}
$arr = "##periode##judul";
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

$sBln = "select kodebarang, count(kodebarang) as totBlmpp,substr(tgl_sdt,6,2) as periode from " . $dbname . ".log_prapodt a left join 
       " . $dbname . ".log_prapoht b on a.nopp=b.nopp where create_po=0 and 
       ditolakoleh=0000000000 and left(tgl_sdt,4)='" . $periode . "'
       group by kodebarang,substr(tgl_sdt,6,2)  order by substr(tgl_sdt,6,2) asc";
$qBln=$owlPDO->query($sBln) or die(print " Gagal: ".PDOException::getMessage());
$qBln->setFetchMode(PDO::FETCH_ASSOC);
while ($rBln = $qBln->fetch()) {
    $dtJmlhPP[$rBln['kodebarang']][$rBln['periode']] = $rBln['totBlmpp'];
    $dtPur[$rBln['kodebarang']] = $rBln['kodebarang'];
    @$totPur[$rBln['kodebarang']]+=$rBln['totBlmpp'];
}
$sBln2 = "select kodebarang, count(kodebarang) as totBlmpp,purchaser,substr(tgl_sdt,6,2) as periode from " . $dbname . ".log_prapodt a left join 
       " . $dbname . ".log_prapoht b on a.nopp=b.nopp where   create_po=1 and 
       ditolakoleh=0000000000 and left(tgl_sdt,4)='" . $periode . "'
       group by kodebarang,substr(tgl_sdt,6,2)  order by substr(tgl_sdt,6,2) asc";
$qBln2=$owlPDO->query($sBln2) or die(print " Gagal: ".PDOException::getMessage());
$qBln2->setFetchMode(PDO::FETCH_ASSOC);
while ($rBln2 = $qBln2->fetch()) {
    @$totPur2[$rBln2['kodebarang']]+=$rBln2['totBlmpp'];
}

$bg = "";
$brdr = 0;
if ($proses == 'excel') {
    $bg = "align=center bgcolor=#DEDEDE";
    $brdr = 1;
    $tab.="<table border=0>
     <tr>
        <td colspan=4 align=left><font size=3>" . $judul . "</font></td>
        <td colspan=3 align=right>" . $_SESSION['lang']['tahun'] . " : " . $periode . "</td>
     </tr>    
</table>";
}
$cekDt = count(@$dtPur);

if ($proses == 'preview' || $proses == 'excel') {
    if ($cekDt == 0) {
        exit("Error:Barang Kosong");
    }
}
if ($proses == 'preview' || $proses == 'excel') {
    if ($proses != 'excel')
        $tab.=$judul;
//exit('Error : '.$sBln2);
    $tab.="<table cellpadding=1 cellspacing=1 border=" . $brdr . " class=sortable style='width:100%;'>
    <thead class=rowheader>
    <tr>
    <th align=center " . $bg . ">No.</th>
    <th align=center " . $bg . ">" . $_SESSION['lang']['namabarang'] . "</th>";
    foreach ($optBulan as $lstBulan => $dtBulan) {
        $tab.="<th align=center width=75px " . $bg . ">" . $dtBulan . "</th>";
    }
    $tab.="<th align=center " . $bg . ">" . $_SESSION['lang']['total'] . " Pending Items</th>";
    $tab.="<th align=center " . $bg . ">Purchased Items</th>";
    $tab.="</tr></thead><tbody>";
    foreach ($dtPur as $pur) {
        $no+=1;
        $tab.="<tr class=rowcontent>";
        $tab.="<td align=center>" . $no . "</td>";
        $tab.="<td>" . $optNmBrg[$pur] . "</td>";
        foreach ($optBulan as $lstBulan => $dtBulan) {
            if ((isset($dtJmlhPP[$pur][$lstBulan]) ? $dtJmlhPP[$pur][$lstBulan] : '') != '') {
                $tab.="<td " . $bg . " align=right style='cursor:pointer;' onclick=getDetailPP('" . $pur . "','" . $lstBulan . "','" . $periode . "')>" . $dtJmlhPP[$pur][$lstBulan] . "</td>";
            } else {
                $tab.="<td align=right>0</td>";
            }
            @$totBulan[$lstBulan]+=$dtJmlhPP[$pur][$lstBulan];
        }
        $tab.="<td align=right>" . (isset($totPur[$pur]) ? $totPur[$pur] : 0) . "</td>";
        $tab.="<td align=right>" . (isset($totPur2[$pur]) ? $totPur2[$pur] : 0) . "</td>";
        @$totalSemua+=$totPur[$pur];
        @$totalSemua2+=$totPur2[$pur];
        $tab.="</tr>";
    }
    $tab.="<tr class=rowcontent>";
    $tab.="<td colspan=2 align=center>" . $_SESSION['lang']['total'] . "</td>";
    foreach ($optBulan as $lstBulan => $dtBulan) {
        if ((isset($totBulan[$lstBulan]) ? $totBulan[$lstBulan] : 0) != 0) {
        $tab.="<td align=right style='cursor:pointer;' onclick=getDetailPPTOT('" . $lstBulan . "','" . $periode . "')>" . $totBulan[$lstBulan] . "</td>";
        }
        else
        {
        $tab.="<td align=right>0</td>";
        }
    }
    $tab.="<td align=right>" . $totalSemua . "</td>";
    $tab.="<td align=right>" . $totalSemua2 . "</td>";
    $tab.="</tbody></table>";
}
switch ($proses) {
    case'preview':
        //    exit("error:masuk");
        if ($periode == '') {
            exit("Error: Field required");
        }
        echo $tab;
        break;

    case'excel':
        if ($periode == '') {
            exit("Error: Field required");
        }

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
        $dte = date("His");
        $nop_ = "ppBlmRealiasi_" . $dte;
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

    case'getDetPP':
        if ($cetak != 'excel') {
        $tab.="<img onclick=\"zExcel2x(event,'lbm_slave_proc_ppblmrealisasi.php','getDetPP','".$purchaser."','".$bln."')\" src='images/excel.jpg' class='resicon' title='MS.Excel'>Excel</br>";
        }
        $sget = "select distinct nopp,kodebarang,realisasi,tgl_sdt from " . $dbname . ".log_prapodt 
               where left(tgl_sdt,7)='" . $bln . "' and ditolakoleh=0000000000 and
               kodebarang='" . $purchaser . "' and create_po=0";
		$qget=$owlPDO->query($sget) or die(print " Gagal: ".PDOException::getMessage());
		$qget->setFetchMode(PDO::FETCH_ASSOC);
        if ($cetak != 'excel') {
        $tab.="<button onclick=zBack()>Back</button><br>";
        }
		$tab.="Periode : " . $bln . "<br>Nama Barang : " . $optNmBrg[$purchaser] . "";
        if ($cetak == 'excel') {
		$tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable>";
        }
        else
        {
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";    
        }
        $tab.="<thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td align=center>No.</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['nopp'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['tanggal'] . " SDT</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['namabarang'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['realisasi'] . "</td>";
        if ($cetak != 'excel') {
        $tab.="<td align=center>Document</td>";
        }
        $tab.="</tr>";
        $tab.="</thead><tbody>";
        // $tab.="<tr class=rowcontent><td colspan=2>Bulan : " . $bln . "</td><td colspan=4>Purchaser : " . $optNm[$purchaser] . "</td></tr>";
		$noe=0;
        while ($rget = $qget->fetch()) {
            $noe+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $noe . "</td>";
            $tab.="<td>" . $rget['nopp'] . "</td>";
            $tab.="<td align=center>" . $rget['tgl_sdt'] . "</td>";
            $tab.="<td align=center>" . $rget['kodebarang'] . "</td>";
            $tab.="<td>" . $optNmBrg[$rget['kodebarang']] . "</td>";
            $tab.="<td align=right>" . $rget['realisasi'] . "</td>";
            if ($cetak != 'excel') {
            $tab.="<td align=right><img src='images/pdf.jpg' class='resicon' title='Print' onclick=masterPDF('log_prapoht','".$rget['nopp']."','','log_slave_print_log_pp',event);></td>";
            }
            $tab.="</tr>";
        }
        $tab.="</tbody></table>";
        if ($cetak != 'excel') {
        $tab.="<button onclick=zBack()>Back</button>";
        }
        if($cetak=='excel'){
            $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
            $dte = date("His");
            $nop_ = "ppBlmRealiasidet_" . $dte;
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
        }
        else{
		echo $tab;
        }
        break;
        case'getDetPPTOT':
        if ($cetak != 'excel') {
        $tab.="<img onclick=\"zExcel2x(event,'lbm_slave_proc_ppblmrealisasi.php','getDetPPTOT','','".$bln."')\" src='images/excel.jpg' class='resicon' title='MS.Excel'>Excel</br>";
        }
        $sget = "select distinct nopp,kodebarang,realisasi,tgl_sdt from " . $dbname . ".log_prapodt 
               where left(tgl_sdt,7)='" . $bln . "' and ditolakoleh=0000000000  and create_po=0";
        $qget=$owlPDO->query($sget) or die(print " Gagal: ".PDOException::getMessage());
        $qget->setFetchMode(PDO::FETCH_ASSOC);
        if ($cetak != 'excel') {
        $tab.="<button onclick=zBack()>Back</button><br>";
        }
        $tab.="Periode : " . $bln . "<br>";
        if ($cetak == 'excel') {
        $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable>";
        }
        else
        {
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";    
        }
        $tab.="<thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td align=center>No.</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['nopp'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['tanggal'] . " SDT</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['namabarang'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['realisasi'] . "</td>";
        if ($cetak != 'excel') {
        $tab.="<td align=center>Document</td>";
        }
        $tab.="</tr>";
        $tab.="</thead><tbody>";
        // $tab.="<tr class=rowcontent><td colspan=2>Bulan : " . $bln . "</td><td colspan=4>Purchaser : " . $optNm[$purchaser] . "</td></tr>";
        $noe=0;
        while ($rget = $qget->fetch()) {
            $noe+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $noe . "</td>";
            $tab.="<td>" . $rget['nopp'] . "</td>";
            $tab.="<td align=center>" . $rget['tgl_sdt'] . "</td>";
            $tab.="<td align=center>" . $rget['kodebarang'] . "</td>";
            $tab.="<td>" . $optNmBrg[$rget['kodebarang']] . "</td>";
            $tab.="<td align=right>" . $rget['realisasi'] . "</td>";
            if ($cetak != 'excel') {
            $tab.="<td align=right><img src='images/pdf.jpg' class='resicon' title='Print' onclick=masterPDF('log_prapoht','".$rget['nopp']."','','log_slave_print_log_pp',event);></td>";
            }
            $tab.="</tr>";
        }
        $tab.="</tbody></table>";
        if ($cetak != 'excel') {
        $tab.="<button onclick=zBack()>Back</button>";
        }
        if($cetak=='excel'){
            $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
            $dte = date("His");
            $nop_ = "ppBlmRealiasidet_all_" . $dte;
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
        }
        else{
        echo $tab;
        }
        break;
    case'getDetPt':
        $sUnit = "select distinct kodeunit from " . $dbname . ".bgt_regional_assignment where regional='" . $_POST['regional'] . "'";
		$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
		$qUnit->setFetchMode(PDO::FETCH_ASSOC);
        while ($rUnit = $qUnit->fetch()) {
            $ader+=1;
            if ($ader == 1) {
                $arte.="'" . $rUnit['kodeunit'] . "'";
            } else {
                $arte.=",'" . $rUnit['kodeunit'] . "'";
            }
        }
        $optPt = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sPt = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi in (" . $arte . ")";
		$qPt=$owlPDO->query($sPt) or die(print " Gagal: ".PDOException::getMessage());
		$qPt->setFetchMode(PDO::FETCH_ASSOC);
        while ($rPt = $qPt->fetch()) {
            $optPt.="<option value='" . $rPt['induk'] . "'>" . $optNmOrg[$rPt['induk']] . "</option>";
        }
        echo $optPt;
        break;

    default:
        break;
}
?>