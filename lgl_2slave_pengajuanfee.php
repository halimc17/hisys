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

$kdUnit = empty($_POST['kdUnit']) ? (isset($_GET['kdUnit']) ? $_GET['kdUnit'] : '') : $_POST['kdUnit'];
$periode = empty($_POST['periode']) ? (isset($_GET['periode']) ? $_GET['periode'] : '') : $_POST['periode'];
$filter = empty($_POST['filter']) ? (isset($_GET['filter']) ? $_GET['filter'] : '') : $_POST['filter'];


$thn = explode("-", $periode);
$unitId = $_SESSION['lang']['all'];

if ($periode == '') {
    exit("Error: " . $_SESSION['lang']['periode'] . " tidak boleh kosong" . $periode);
}

if ($kdUnit != '') {
    $where.=" and a.kodeorg='" . $kdUnit . "'";
    $unitId = isset($optNmOrg[$kdUnit]) ? $optNmOrg[$kdUnit] : '';
}

$strprn="select a.*, b.namaorganisasi ,c.namapihak from ".$dbname.".lgl_pengajuanfee a 
         left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi 
         left join ".$dbname.".legal_5pihak c on a.instansi=c.kodepihak where a.notransaksi!='' ".$where." and substr(a.tanggal,1,7)='".$periode."'";
$resprn = fetchData($strprn);



$no=0;
$data=array();
foreach ($resprn as $key => $val) {
    $data[$key]['no']=(intval($key)+1);
    $data[$key]['initial']=$val['kodeorg'];
    $data[$key]['pt']=$val['namaorganisasi'];
    if($val['tipe']=='biasa')
    {
        $xxyy='Tidak';
    }
    else
    {
        $xxyy='Urgent';
    }
    $data[$key]['tipe']=$xxyy;
    $data[$key]['nomor']=$val['notransaksi'];
    $data[$key]['tanggal']=$val['tanggal'];
    $data[$key]['keperluan']=$val['deskripsi'];
    $data[$key]['intansi']=$val['namapihak'];
    $data[$key]['permintaan']=$val['rupiah'];
    $strtgn="select noinvoice from ".$dbname.".keu_tagihanht  where nopo='".$val['notransaksi']."' and keterangan2='".$val['deskripsi']."'";
    $restgn = fetchData($strtgn);
    $strks="select jumlah from ".$dbname.".keu_kasbankdt  where keterangan1='".$restgn[0]['noinvoice']."' and nodok='".$val['notransaksi']."'";
    $resks=fetchData($strks);
    if($resks[0]['jumlah']=='')
    {
        $realisasi=0;
    }
    else
    {
        $realisasi=$resks[0]['jumlah'];
    }
    $data[$key]['realisasi']=$realisasi;
    $data[$key]['keterangan']=$val['keterangan'];

}

$brdr = 0;
$bgcoloraja = '';

if ($proses == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE";
    $brdr = 1;
    $tab = "
    <table>
    <tr><td colspan=17 align=left><b>Rekap Pengajuan Pembayaran</b></td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['pt'] . " : " . $unitId . "</td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['periode'] . " : " . $periode . "</td></tr>
    </table>";
}
$bgcoloraja = "bgcolor=#DEDEDE";
$bgcolorajax = "bgcolor=#5a63ec";
$tab = "<table cellspacing=1 border=" . $brdr . " class=sortable >
	<thead class=rowheader>
	<tr>
        <td " . $bgcoloraja . " align=center rowspan=2>NO</td>
        <td " . $bgcoloraja . " align=center rowspan=2>INITAL</td>
        <td " . $bgcoloraja . " align=center rowspan=2>PT</td>
        <td " . $bgcoloraja . " align=center >TIPE</td>
        <td " . $bgcoloraja . " align=center colspan=2>DASAR PERMINTAAN</td>
        <td " . $bgcoloraja . " align=center rowspan=2>KEPERLUAN</td>
        <td " . $bgcoloraja . " align=center rowspan=2>INTASI YANG MEMPROSES</td>
        <td " . $bgcoloraja . " align=center colspan=2>JUMLAH RP</td>
        <td " . $bgcoloraja . " align=center rowspan=2>KETERANGAN</td>
                ";
$tab.="</tr><tr>
        
        <td " . $bgcoloraja . " align=center>URGENT/TIDAK</td>
        <td " . $bgcoloraja . " align=center>NOMOR</td>
        <td " . $bgcoloraja . " align=center>TANGGAL</td>
        <td " . $bgcoloraja . " align=center>PERMINTAAN</td>
        <td " . $bgcoloraja . " align=center>REALISASI</td>
        </tr>";
$tab.="</thead>
	<tbody>";

foreach ($data as $row => $col) {
    $tab.= "<tr class='rowcontent'>";
    foreach ($col as $key => $value) {
        $tab.= "<td>".$value."</td>";
    }
    $tab.= "</tr>";
}

$tab.="</tbody></table>";

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
        $nop_ = "rekappengajuanpembayaran_" . $purId . "_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls.gz';
            </script>";

        break;

    
    default:
        break;
}
?>