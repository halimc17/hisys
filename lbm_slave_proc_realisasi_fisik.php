<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
$proses = checkPostGet('proses', '');
$kdPt = checkPostGet('kdPt', '');
$periode = checkPostGet('periode', '');
$judul = checkPostGet('judul', '');
$regDt = checkPostGet('regDt', '');


$qwe = explode('-', $periode);
$tahun = $qwe[0];
$bulan = $qwe[1];


$optNm = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
if ($proses == 'excel' || $proses == 'preview') {
    if ($periode == '') {
        exit("Error:Field Tidak Boleh Kosong");
    }
}
if ($regDt != '') {
    $whrtd = "regional='" . $regDt . "'";
    if ($regDt == 'SUMSEL') {
        $whrtd = " regional in ('SUMSEL','LAMPUNG')";
    }
    $sUnit = "select distinct kodeunit from " . $dbname . ".bgt_regional_assignment where " . $whrtd . "";
} else {
    $sUnit = "select distinct kodeunit from " . $dbname . ".bgt_regional_assignment order by kodeunit";
}
$arte = "";
$ader = 0;

$qUnit = $owlPDO->query($sUnit) or die(print " Gagal: " . PDOException::getMessage());
$qUnit->setFetchMode(PDO::FETCH_ASSOC);
$rUnit = $qUnit->fetch(); {
    $ader+=1;
    if ($ader == 1) {
        $arte.="'" . $rUnit['kodeunit'] . "'";
    } else {
        $arte.=",'" . $rUnit['kodeunit'] . "'";
    }
}
$whrbgt = " and substr(kodeorg,1,4) in (" . $arte . ")";
$whrKapt = " and substr(kodeunit,1,4) in (" . $arte . ")";
$sPt = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi in (" . $arte . ")";

$qPt = $owlPDO->query($sPt) or die(print " Gagal: " . PDOException::getMessage());
$qPt->setFetchMode(PDO::FETCH_ASSOC);
$ert = 0;
$dtPete = "";
while ($rPt = $qPt->fetch()) {
    $ert+=1;
    if ($ert == 1) {
        $dtPete.="'" . $rPt['induk'] . "'";
    } else {
        $dtPete.=",'" . $rPt['induk'] . "'";
    }
}
$whr = " and kodeorg in (" . $dtPete . ")";

if ($kdPt != '') {
    $whr = " and kodeorg='" . $kdPt . "'";
    $sBgt = "select distinct kodeorganisasi from " . $dbname . ".organisasi where induk='" . $kdPt . "'";

    $qBgt = $owlPDO->query($sBgt) or die(print " Gagal: " . PDOException::getMessage());
    $qBgt->setFetchMode(PDO::FETCH_ASSOC);
    $ater = 0;
    while ($rBgt = $qBgt->fetch()) {
        $ater+=1;
        if ($ater == 1) {
            $aretd = "'" . $rBgt['kodeorganisasi'] . "'";
        } else {
            $aretd.=",'" . $rBgt['kodeorganisasi'] . "'";
        }
    }
    $whrbgt = " and substr(kodeorg,1,4) in (" . $aretd . ")";
    $whrKapt = " and substr(kodeunit,1,4) in (" . $aretd . ")";
}

$arr = "##periode##judul##kdPt##regDt";
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
if ($proses == 'excel' || $proses == 'preview') {

##bulan ini##
#total annual pembelian kapital dan non kapital relaisasi#
    $sTot = "select distinct sum(jumlahpesan) as total  from 
       " . $dbname . ".log_po_vw where substr(kodebarang,1,3) in (select distinct kelompokbarang from " . $dbname . ".sdm_5tipeasset order by kodetipe) 
       and tanggal like '" . $periode . "%' " . $whr . "";

    $qTot = $owlPDO->query($sTot) or die(print " Gagal: " . PDOException::getMessage());
    $qTot->setFetchMode(PDO::FETCH_ASSOC);
    while ($rTot = $qTot->fetch()) {
        @$totKapi+=$rTot['total'];
    }

    $sTot = "select distinct sum(jumlahpesan) as total,matauang from 
       " . $dbname . ".log_po_vw where  substr(kodebarang,1,1) not in ('8','9') 
       and tanggal like '" . $periode . "%'  " . $whr . "";

    $qTot = $owlPDO->query($sTot) or die(print " Gagal: " . PDOException::getMessage());
    $qTot->setFetchMode(PDO::FETCH_ASSOC);
    while ($rTot = $qTot->fetch()) {
        @$totNonKapi+=$rTot['total'];
    }
#end total annual pembelian kapital dan non kapital relaisasi#
#pembelian kapital dan non kapital realisasi s.d bulan ini mulai#
    $sTot = "select distinct sum(jumlahpesan) as total from 
       " . $dbname . ".log_po_vw where substr(kodebarang,1,3) in (select distinct kelompokbarang from " . $dbname . ".sdm_5tipeasset order by kodetipe)
       " . $whr . " and substr(tanggal,1,7) between '" . $tahun . "-01' and '" . $periode . "'";

    $qTot = $owlPDO->query($sTot) or die(print " Gagal: " . PDOException::getMessage());
    $qTot->setFetchMode(PDO::FETCH_ASSOC);
    while ($rTot = $qTot->fetch()) {
        @$totKapiSmp+=$rTot['total'];
    }

    $sTot = "select distinct sum(jumlahpesan) as total from 
       " . $dbname . ".log_po_vw where substr(kodebarang,1,1) not in ('8','9') " . $whr . "
       and substr(tanggal,1,7) between '" . $tahun . "-01' and '" . $periode . "'";

    $qTot = $owlPDO->query($sTot) or die(print " Gagal: " . PDOException::getMessage());
    $qTot->setFetchMode(PDO::FETCH_ASSOC);
    while ($rTot = $qTot->fetch()) {
        @$totNonKapiSmp+=$rTot['total'];
    }
#pembelian kapital dan non kapital realisasi s.d bulan ini sudah disini#


    strlen($bulan) < 1 ? $bln = "0" . $bulan : $bln = $bulan;

#anggaran kapital bulan ini mulai#
    $sBgt = "select distinct sum(k" . $bln . ") as total from 
      " . $dbname . ".bgt_kapital_vw where tahunbudget='" . $tahun . "' " . $whrKapt . "";

    $qBgt = $owlPDO->query($sBgt) or die(print " Gagal: " . PDOException::getMessage());
    $qBgt->setFetchMode(PDO::FETCH_ASSOC);
    $rBgt = $qBgt->fetch();
    $bgtKapital = 0;

    $sBgt = "select distinct sum(fis" . $bln . ") as total from 
      " . $dbname . ".bgt_budget_detail where tahunbudget='" . $tahun . "'
      and substr(kodebudget,1,1)='M' " . $whrbgt . "";

    $qBgt = $owlPDO->query($sBgt) or die(print " Gagal: " . PDOException::getMessage());
    $qBgt->setFetchMode(PDO::FETCH_ASSOC);
    $rBgt = $qBgt->fetch();
    $bgtNonKapital = $rBgt['total'];
#anggaran kapital bulan ini end#
#anggaran s.d bulan ini mulai#
    $addstr = "(";
    for ($W = 1; $W <= intval($bulan); $W++) {
        if ($W < 10)
            $jack = "k0" . $W;
        else
            $jack = "k" . $W;
        if ($W < intval($bulan))
            $addstr.=$jack . "+";
        else
            $addstr.=$jack;
    }
    $addstr.=")";


    $aresta = "SELECT sum(" . $addstr . ") as total FROM " . $dbname . ".bgt_kapital_vw
        WHERE tahunbudget = '" . $tahun . "' " . $whrKapt . "";

    $query = $owlPDO->query($aresta) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $res = $query->fetch();
    $bgtKapSmp = 0;


    $addstr = "(";
    for ($W = 1; $W <= intval($bulan); $W++) {
        if ($W < 10)
            $jack = "fis0" . $W;
        else
            $jack = "fis" . $W;
        if ($W < intval($bulan))
            $addstr.=$jack . "+";
        else
            $addstr.=$jack;
    }
    $addstr.=")";


    $aresta = "SELECT sum(" . $addstr . ") as total FROM " . $dbname . ".bgt_budget_detail
         WHERE substr(kodebudget,1,1)='M' and tahunbudget = '" . $tahun . "' " . $whrbgt . "";

    $query = $owlPDO->query($aresta) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $res = $query->fetch();
    $bgtNonKap = $res['total'];
#anggaran s.d bulan ini end#
#annual non kapital dan kapital mulai#
    $aresta = "SELECT sum(harga) as total FROM " . $dbname . ".bgt_kapital_vw
        WHERE tahunbudget = '" . $tahun . "' " . $whrKapt . "";

    $query = $owlPDO->query($aresta) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $res = $query->fetch();
    $annualKap = 0;

    $aresta = "SELECT sum(jumlah) as total FROM " . $dbname . ".bgt_budget_detail
         WHERE substr(kodebudget,1,1)='M' and tahunbudget = '" . $tahun . "' " . $whrbgt . "";

    $query = $owlPDO->query($aresta) or die(print " Gagal: " . PDOException::getMessage());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $res = $query->fetch();
    $annualNonKap = $res['total'];
#annual non kapital dan kapital end#


    $lnkKapital = "style='cursor:pointer' onclick=getDetailKap2('" . $arr . "')";
    $lnkNonKap = "style='cursor:pointer' onclick=getDetailNonKap2('" . $arr . "')";
    $bg = "";
    $brdr = 0;
    if ($proses == 'excel') {
        $bg = " bgcolor=#DEDEDE";
        $brdr = 1;
        $tab.="<table border=0>
     <tr>
        <td colspan=4 align=left><font size=3>" . $judul . "</font></td>
        <td colspan=3 align=right>" . $_SESSION['lang']['bulan'] . " : " . $optBulan[$bulan] . " " . $tahun . "</td>
     </tr>    
</table>";
    }

    if ($proses != 'excel')
        $tab.=$judul;
    $tab.="<table cellpadding=1 cellspacing=1 border=" . $brdr . " class=sortable style='width:100%;'>
    <thead class=rowheader>
    <tr>
    <td align=center rowspan=2 " . $bg . ">Kelompok</td>
    <td align=center colspan=3 " . $bg . ">" . $_SESSION['lang']['bulanini'] . "</td>
    <td align=center colspan=3 " . $bg . ">" . $_SESSION['lang']['sdbulanini'] . "</td>
    <td align=center rowspan=2 " . $bg . ">ANNUAL BUDGET</td>
    <td align=center rowspan=2 " . $bg . ">%</td>
    </tr>
    <tr>
    <td align=center " . $bg . ">" . $_SESSION['lang']['realisasi'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['anggaran'] . "</td>
    <td align=center " . $bg . ">%</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['realisasi'] . "</td>
    <td align=center " . $bg . ">" . $_SESSION['lang']['anggaran'] . "</td>
    <td align=center " . $bg . ">%</td>
    </tr>
    </thead>
    <tbody>
";
    $tab.="<tr class=rowcontent " . $lnkKapital . ">";
    $tab.="<td>KAPITAL</td>";
    $tab.="<td align=right>" . number_format($totKapi, 0) . "</td>";
    $tab.="<td align=right>" . number_format($bgtKapital, 0) . "</td>";
    @$persenBlnini = $bgtKapital > 0 ? ($totKapi / $bgtKapital) * 100 : 0;
    $tab.="<td align=right>" . number_format($persenBlnini, 0) . "</td>";
    $tab.="<td align=right>" . number_format($totKapiSmp, 0) . "</td>";
    $tab.="<td align=right>" . number_format($bgtKapSmp, 0) . "</td>";
    @$persenSmpBlnini = $bgtKapSmp > 0 ? ($totKapiSmp / $bgtKapSmp) * 100 : 0;
    $tab.="<td align=right>" . number_format($persenSmpBlnini, 0) . "</td>";
    @$persenAnnual = $annualKap > 0 ? ($totKapiSmp / $annualKap) * 100 : 0;
    $tab.="<td align=right>" . number_format($annualKap, 0) . "</td>";
    $tab.="<td align=right>" . number_format($persenAnnual, 0) . "</td>";
    $tab.="</tr>";
    $tab.="<tr class=rowcontent " . $lnkNonKap . ">";
    $tab.="<td>NON KAPITAL</td>";
    $tab.="<td align=right>" . number_format($totNonKapi, 0) . "</td>";
    $tab.="<td align=right>" . number_format($bgtNonKapital, 0) . "</td>";
    @$prsnBlnini = $bgtNonKapital > 0 ? ($totNonKapi / $bgtNonKapital) * 100 : 0;
    $tab.="<td align=right>" . number_format($prsnBlnini, 0) . "</td>";
    $tab.="<td align=right>" . number_format($totNonKapiSmp, 0) . "</td>";
    $tab.="<td align=right>" . number_format($bgtNonKap, 0) . "</td>";
    @$prsnSmpBlnini = $bgtNonKap > 0 ? ($totNonKapiSmp / $bgtNonKap) * 100 : 0;
    $tab.="<td align=right>" . number_format($prsnSmpBlnini, 0) . "</td>";
    @$prsnAnnual = $annualNonKap > 0 ? ($totNonKapiSmp / $annualNonKap) * 100 : 0;
    $tab.="<td align=right>" . number_format($annualNonKap, 0) . "</td>";
    $tab.="<td align=right>" . number_format($prsnAnnual, 0) . "</td>";
    $tab.="</tr>";
    $grReal = $totKapi + $totNonKapi;
    $grBudget = $bgtKapital + $bgtNonKapital;
    $grPersen = $grBudget > 0 ? $grReal / $grBudget * 100 : 0;
    $grSmpBgt = $bgtKapSmp + $bgtNonKap;
    $grSmp = $totKapiSmp + $totNonKapiSmp;
    $grPersenSmp = $grSmpBgt > 0 ? $grSmp / $grSmpBgt * 100 : 0;
    $grAnnual = $annualKap + $annualNonKap;
    $grPersenAnn = $grAnnual > 0 ? $grSmp / $grAnnual * 100 : 0;
    $tab.="<tr class=rowcontent>";
    $tab.="<td>GRAND TOTAL</td>";
    $tab.="<td align=right>" . number_format($grReal, 0) . "</td>";
    $tab.="<td align=right>" . number_format($grBudget, 0) . "</td>";
    $tab.="<td align=right>" . number_format($grPersen, 0) . "</td>";
    $tab.="<td align=right>" . number_format($grSmp, 0) . "</td>";
    $tab.="<td align=right>" . number_format($grSmpBgt, 0) . "</td>";
    $tab.="<td align=right>" . number_format($grPersenSmp, 0) . "</td>";
    $tab.="<td align=right>" . number_format($grAnnual, 0) . "</td>";
    $tab.="<td align=right>" . number_format($grPersenAnn, 0) . "</td>";
    $tab.="</tr>";
    $tab.="</tbody></table>";
}
switch ($proses) {
    case'preview':
        //    exit("error:masuk");
        if ($periode == '') {
            exit("Error:Field Tidak Boleh Kosong");
        }
        echo $tab;
        break;

    case'excel':
        if ($periode == '') {
            exit("Error:Field Tidak Boleh Kosong");
        }

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
        $dte = date("His");
        $nop_ = "totalPembelianFis_" . $dte;
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
    case'getDetPt':
        $arte = "";
        $optPt = "";
        $ader = 0;
        $sUnit = "select distinct kodeunit from " . $dbname . ".bgt_regional_assignment where regional='" . $_POST['regional'] . "'";
        $qUnit = $owlPDO->query($sUnit) or die(print " Gagal: " . PDOException::getMessage());
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
        $qPt = $owlPDO->query($sPt) or die(print " Gagal: " . PDOException::getMessage());
        $qPt->setFetchMode(PDO::FETCH_ASSOC);

        while ($rPt = $qPt->fetch()) {
            $optPt.="<option value='" . $rPt['induk'] . "'>" . $optNm[$rPt['induk']] . "</option>";
        }
        echo $optPt;
        break;
    default:
        break;
}
?>