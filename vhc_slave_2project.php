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

$optSupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$kdOrg = checkPostGet('kdOrg', '');
$thnId = checkPostGet('thnId', '');
$kdProj = checkPostGet('kdProj', '');

///$unitId=$_SESSION['lang']['all'];
$dktlmpk = $_SESSION['lang']['all'];

if ($proses == 'preview' || $proses == 'excel') {


    $brdr = 0;
    $bgcoloraja = $tab = '';
    if ($proses == 'excel') {
        $bgcoloraja = "bgcolor=#DEDEDE align=center";
        $brdr = 1;
        $tab.="
    <table>
    <tr><td colspan=7 align=left><b><font size=5>Laporan Project</font></b></td></tr>
    <tr><td colspan=7 align=left>" . $_SESSION['lang']['unit'] . " : " . $optNmOrg[$kdOrg] . "</td></tr>
    <tr><td colspan=7 align=left>" . $_SESSION['lang']['tahun'] . " : " . $thnId . "</td></tr>
    </table>";
    }

    $tab.="<table cellspacing=1 cellpadding=5 border=" . $brdr . " class=sortable>
	<thead class=rowheader>
	<tr>
        <th " . $bgcoloraja . " align=center>No.</th>
        <th " . $bgcoloraja . " align=center width=75px>" . $_SESSION['lang']['kodeorg'] . "</th>
        <th " . $bgcoloraja . " align=center>Project Code</th>
        <th " . $bgcoloraja . " align=center>" . $_SESSION['lang']['nama'] . "</th>
        <th " . $bgcoloraja . " align=center>" . $_SESSION['lang']['tanggalmulai'] . "</th>
        <th " . $bgcoloraja . " align=center>" . $_SESSION['lang']['tanggalselesai'] . "</th>
        <th " . $bgcoloraja . " align=center>" . $_SESSION['lang']['biaya'] . "</th></tr>";
    $tab.="</tr></thead><tbody>";
    $sData = "select distinct * from " . $dbname . ".project
        where substr(tanggalmulai,1,4)='" . $thnId . "' and kodeorg='" . $kdOrg . "'";
    $nor = 0;
    $qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
    $qData->setFetchMode(PDO::FETCH_ASSOC);
    $tbiaya = 0;
    while ($rData = $qData->fetch()) {
        $nor+=1;
        $sBiaya = "select distinct sum(jumlah) as biaya from " . $dbname . ".keu_jurnaldt where (kodeasset='" . $rData['kode'] . "' or  kodeblok='" . $rData['kode'] . "') and noakun like '129%'";
        $qBiaya = $owlPDO->query($sBiaya) or die(print " Gagal: " . PDOException::getMessage());
        $qBiaya->setFetchMode(PDO::FETCH_ASSOC);
        $rBiaya = $qBiaya->fetch();
        $tab.="<tr class=rowcontent style='cursor:pointer;' onclick=getDetail('" . $rData['kode'] . "')><td align=center>" . $nor . "</td>";
        $tab.="<td align=center>" . $rData['kodeorg'] . "</td>";
        $tab.="<td>" . $rData['kode'] . "</td>";
        $tab.="<td>" . $rData['nama'] . "</td>";
        $tab.="<td align=center>" . tanggalnormal($rData['tanggalmulai']) . "</td>";
        $tab.="<td align=center>" . tanggalnormal($rData['tanggalselesai']) . "</td>";
        $tab.="<td  align=right>" . number_format($rBiaya['biaya'], 2) . "</td>";
        $tbiaya+=$rBiaya['biaya'];
    }
    $tab.="<tr class=rowcontent><td  colspan=6 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>";
    $tab.="<td align=right><b>" . number_format($tbiaya, 2) . "</b></td></tr>";
    $tab.="</tbody></table>";
}

switch ($proses) {
    case'getPt':
        //echo "warning:masuk";
        $optorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sOrg = "select distinct kodeorg  from " . $dbname . ".log_po_vw where substr(tanggal,1,7)='" . $periode . "'";
        $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['kodeorg'] . ">" . $optNmOrg[$rOrg['kodeorg']] . "</option>";
        }
        echo $optorg;
        break;

    case'preview':
        echo $tab;
        break;

    case'excel':

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        $nop_ = "Laporan_Project_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls.gz';
            </script>";

        break;

    case'getDetail':
        $shead = "select distinct nama from " . $dbname . ".project
                   where kode='" . $kdProj . "'";
        $qhead = $owlPDO->query($shead) or die(print " Gagal: " . PDOException::getMessage());
        $qhead->setFetchMode(PDO::FETCH_ASSOC);
        $rhead = $qhead->fetch();
        $tab.="<button class=mybutton onclick=fisikKeExcel(event,'vhc_slave_2project.php','" . $kdProj . "')>" . $_SESSION['lang']['excel'] . "</button>";
		$tab.="<tr><td colspan=7><button class=mybutton onclick=kembaliAja()>Back</button></td></tr>";
		$tab.="<table cellpadding=1 cellspacing=1 border=0><tr><td>Project Code</td><td>:</td>";
        $tab.="<td>" . $kdProj . "</td></tr>";
        $tab.="<tr><td>Project Name</td><td>:</td><td>" . $rhead['nama'] . "</td></tr></table>";
        $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead><tr>";
        
        $tab.="<th align=center>No.</th>";
        $tab.="<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>";
        $tab.="<th align=center>" . $_SESSION['lang']['nojurnal'] . "</th>";
        $tab.="<th align=center>Akun " . $_SESSION['lang']['biaya'] . "</th>";
        $tab.="<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>";
        $tab.="<th align=center>" . $_SESSION['lang']['noreferensi'] . "</th>";
        $tab.="<th align=center>" . $_SESSION['lang']['debet'] . "</th>";
        $tab.="<th align=center>" . $_SESSION['lang']['kredit'] . "</th>";
        $tab.="<th align=center>" . $_SESSION['lang']['kegiatan'] . "</th></tr></thead><tbody>";

        $sDetail = "select keterangan,noreferensi,nojurnal,tanggal,debet,kredit,noakun,kodevhc from " . $dbname . ".keu_jurnaldt_vw where (kodeasset='" . $kdProj . "' or kodeblok='" . $kdProj . "') and noakun like '129%'";
        $qDetail = $owlPDO->query($sDetail) or die(print " Gagal: " . PDOException::getMessage());
        $row = owlBaris($qDetail);

        if ($row != 0) {
            $qDetail->setFetchMode(PDO::FETCH_ASSOC);
            $nor = $tdb = $tkr = 0;
            while ($rDetail = $qDetail->fetch()) {
                $nor+=1;

                $svhc= "select jenispekerjaan from ".$dbname.".vhc_rundt_vw
                   where kodevhc='".$rDetail['kodevhc']."' and alokasibiaya='".$kdProj."'";
                $qvhc = $owlPDO->query($svhc) or die(print " Gagal: " . PDOException::getMessage());
                $qvhc->setFetchMode(PDO::FETCH_ASSOC);
                $rvhc = $qvhc->fetch();

                $whrak="noakun='".$rvhc['jenispekerjaan']."'";
                $optakun=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',$whrak);
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $nor . "</td>";
                $tab.="<td align=left>" . $rDetail['tanggal'] . "</td>";
                $tab.="<td align=left>" . $rDetail['nojurnal'] . "</td>";
                $tab.="<td align=left>" . $rDetail['noakun']."</td>";
                $tab.="<td align=left>" . $rDetail['keterangan'] . "</td>";
                $tab.="<td align=left>" . $rDetail['noreferensi'] . "</td>";
                $tab.="<td align=right>" . number_format($rDetail['debet'], 2) . "</td>";
                $tab.="<td align=right>" . number_format($rDetail['kredit'], 2) . "</td>";
                $tab.="<td align=left>" . $rvhc['jenispekerjaan'] . " - " . $optakun[$rvhc['jenispekerjaan']] . "</td></tr>";
                $tdb+=$rDetail['debet'];
                $tkr+=$rDetail['kredit'];
            }
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=6 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>";
            $tab.="<td align=right><b>" . number_format($tdb, 2) . "</td>";
            $tab.="<td align=right><b>" . number_format($tkr, 2) . "</td>";
            $tab.="<td align=right><b></td>";
            $tab.="</tr>";
        } else {
            $tab.="<tr class=rowcontent><td colspan=7>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        $tab.="</tbody></table>";

        echo $tab;
        break;



    case'getdetailexcel':
        $shead = "select distinct nama from " . $dbname . ".project
                   where kode='" . $kdProj . "'";
        $qhead = $owlPDO->query($shead) or die(print " Gagal: " . PDOException::getMessage());
        $qhead->setFetchMode(PDO::FETCH_ASSOC);
        $rhead = $qhead->fetch();
        $tab.="<table cellpadding=1 cellspacing=1 border=1><tr><td>Kode Project</td><td>:</td>";
        $tab.="<td>" . $kdProj . "</td></tr>";
        $tab.="<tr><td>Nama Project</td><td>:</td><td>" . $rhead['nama'] . "</td></tr></table>";
        $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead><tr>";
        $tab.="<td align=center>No.</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['nojurnal'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['noakun'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['noreferensi'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['debet'] . "</td>";
        $tab.="<td align=center>" . $_SESSION['lang']['kredit'] . "</td></tr></thead><tbody>";

        $sDetail = "select keterangan,noreferensi,nojurnal,tanggal,debet,kredit,noakun,kodevhc from " . $dbname . ".keu_jurnaldt_vw where (kodeasset='" . $kdProj . "' or kodeblok='" . $kdProj . "') and noakun like '129%'";
        $qDetail = $owlPDO->query($sDetail) or die(print " Gagal: " . PDOException::getMessage());
        $row = owlBaris($qDetail);
        if ($row != 0) {
            $qDetail->setFetchMode(PDO::FETCH_ASSOC);
            $nor = $tdb = $tkr = 0;
            while ($rDetail = $qDetail->fetch()) {
                $nor+=1;

                $svhc= "select jenispekerjaan from ".$dbname.".vhc_rundt_vw
                   where kodevhc='".$rDetail['kodevhc']."' and alokasibiaya='".$kdProj."'";
                $qvhc = $owlPDO->query($svhc) or die(print " Gagal: " . PDOException::getMessage());
                $qvhc->setFetchMode(PDO::FETCH_ASSOC);
                $rvhc = $qvhc->fetch();

                $whrak="noakun='".$rvhc['jenispekerjaan']."'";
                $optakun=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',$whrak);
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $nor . "</td>";
                $tab.="<td align=left>" . $rDetail['tanggal'] . "</td>";
                $tab.="<td align=left>" . $rDetail['nojurnal'] . "</td>";
                $tab.="<td align=left>" . $rDetail['noakun'] . "</td>";
                $tab.="<td align=left>" . $rDetail['keterangan'] . "</td>";
                $tab.="<td align=left>" . $rDetail['noreferensi'] . "</td>";
                $tab.="<td align=right>" . number_format($rDetail['debet'], 2) . "</td>";
                $tab.="<td align=right>" . number_format($rDetail['kredit'], 2) . "</td>";
                $tab.="<td align=left>" . $rvhc['jenispekerjaan'] . " - " . $optakun[$rvhc['jenispekerjaan']] . "</td></tr>";
                $tdb+=$rDetail['debet'];
                $tkr+=$rDetail['kredit'];
            }
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=6 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>";
            $tab.="<td align=right><b>" . number_format($tdb, 2) . "</td>";
            $tab.="<td align=right><b>" . number_format($tkr, 2) . "</td>";
            $tab.="</tr>";
        } else {
            $tab.="<tr class=rowcontent><td colspan=7>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        $tab.="</tbody></table>";

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
        $nop_ = "detail_transaksi" . $kdProj;
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