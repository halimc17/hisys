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


// $tgl1 = tanggalsystem(checkPostGet('tgl1', ''));
// $tgl2 = tanggalsystem(checkPostGet('tgl2', ''));
$proses     = checkPostGet('proses', '');
$status1    = checkPostGet('status1', '');
$pt1        = checkPostGet('pt1', '');
$terima1    = checkPostGet('terima1', '');
$tgl1       = checkPostGet('tgl1', '');
$tgl2       = checkPostGet('tgl2', '');
$nmsigment1 = checkPostGet('nmsigment1', '');
$stsposo1   = checkPostGet('stsposo1', '');

// $tgl1 = empty($_POST['tgl1']) ? (isset($_GET['tgl1']) ? $_GET['tgl1'] : '') : $_POST['tgl1'];
// $tgl2 = empty($_POST['tgl2']) ? (isset($_GET['tgl2']) ? $_GET['tgl2'] : '') : $_POST['tgl2'];
// $status1 = empty($_POST['status1']) ? (isset($_GET['status1']) ? $_GET['status1'] : '') : $_POST['status1'];
// $pt1 = empty($_POST['pt1']) ? (isset($_GET['pt1']) ? $_GET['pt1'] : '') : $_POST['pt1'];
// $terima1 = !isset($_POST['terima1']) ? (isset($_GET['terima1']) ? $_GET['terima1'] : '') : $_POST['terima1'];

function putertanggal($tanggal) {
    $qwe = explode("-", $tanggal);
    if ($tanggal == '')
        $asd = '';
    else
        $asd = $qwe[2] . "-" . $qwe[1] . "-" . $qwe[0];
    return $asd;
}

$tanggal1 = putertanggal($tgl1);
$tanggal2 = putertanggal($tgl2);

$brdr = 0;
$bgcoloraja = '';
if ($proses == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE ";
    $brdr = 1;
}

// kamus barang
$sPo = "select kodebarang, namabarang from " . $dbname . ".log_5masterbarang
        where 1";
$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
$qPo->setFetchMode(PDO::FETCH_ASSOC);
while ($rPo = $qPo->fetch()) {
    $namabarang[$rPo['kodebarang']] = $rPo['namabarang'];
}

// kamus supplier
$sPo = "select supplierid, namasupplier from " . $dbname . ".log_5supplier
        where 1";
$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
$qPo->setFetchMode(PDO::FETCH_ASSOC);
while ($rPo = $qPo->fetch()) {
    $namasupplier[$rPo['supplierid']] = $rPo['namasupplier'];
}

$tab="<style>
.freezetbl {
	position: relative;
	max-height: 350px;
	background-color:#56B5E7;
}
.freezetbl thead {
	position: -webkit-sticky;
	position: sticky;
	top: 0;
	z-index: 2;
}
</style>";

$tab.= "<table cellspacing=1 cellpadding=5 border=" . $brdr . " class=freezetbl>
    <thead class=rowheader>";
$tab.="<tr>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>No</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopo'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PO</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kodebarang'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namabarang'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['matauang'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kurs'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['harga'] . " " . $_SESSION['lang']['satuan'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jumlah'] . " PO</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['satuan'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['harga'] . " " . $_SESSION['lang']['total'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " colspan=5>" . $_SESSION['lang']['pembayaran'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " colspan=5>" . $_SESSION['lang']['pengiriman'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . " colspan=4>" . $_SESSION['lang']['penerimaan'] . "</th>";
$tab.="</tr>";
$tab.="<tr>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['syaratPem'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['noinvoice'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['jatuhtempo'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggalbayar'] . "</th>";

$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['lokasi'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['notransaksi'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['jumlah'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['satuan'] . "</th>";

$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['bapb'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['jumlah'] . "</th>";
$tab.="<th align=center " . $bgcoloraja . ">" . $_SESSION['lang']['satuan'] . "</th>";
$tab.="</tr>";

// data pembayaran
$sPo = "select a.keterangan1, sum(a.jumlah*a.kurs) as jumlah, b.tanggal, b.cgttu from " . $dbname . ".keu_kasbankdt a
        left join " . $dbname . ".keu_kasbankht b on b.notransaksi = a.notransaksi
        where a.keterangan1 != '' group by a.keterangan1";
$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
$qPo->setFetchMode(PDO::FETCH_ASSOC);
while ($rPo = $qPo->fetch()) {
    $databayar[$rPo['keterangan1']]['noinvoice'] = $rPo['keterangan1'];
    $databayar[$rPo['keterangan1']]['tanggal'] = $rPo['tanggal'];
    $databayar[$rPo['keterangan1']]['cgttu'] = $rPo['cgttu'];
}

// data pengiriman
$sPo = "select a.notransaksi, a.kodebarang, a.jumlah, a.satuan, b.tipetransaksi, b.tanggal, b.nopo, b.kodept from " . $dbname . ".log_lpbdt a
        left join " . $dbname . ".log_lpbht b on b.notransaksi = a.notransaksi
        where 1";
$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
$qPo->setFetchMode(PDO::FETCH_ASSOC);
while ($rPo = $qPo->fetch()) {
    $kunci = strtoupper($rPo['nopo']) . $rPo['kodebarang'];
    if (!isset($datakirim[$kunci]['jumlah']))
        $datakirim[$kunci]['jumlah'] = 0;
    $datakirim[$kunci]['nopo'] = $rPo['nopo'];
    $datakirim[$kunci]['lokasi'] = $rPo['kodept'];
    $datakirim[$kunci]['notransaksi'] = $rPo['notransaksi'];
    $datakirim[$kunci]['tanggal'] = $rPo['tanggal'];
    $datakirim[$kunci]['jumlah']+=$rPo['jumlah'];
    $datakirim[$kunci]['satuan'] = $rPo['satuan'];
}

$sPo = "select * from " . $dbname . ".keu_tagihanht
        where 1";
$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
$qPo->setFetchMode(PDO::FETCH_ASSOC);
while ($rPo = $qPo->fetch()) {
    $datatagih[$rPo['nopo']]['nopo'] = $rPo['nopo'];
    $datatagih[$rPo['nopo']]['noinvoice'] = $rPo['noinvoice'];
    $datatagih[$rPo['nopo']]['jatuhtempo'] = $rPo['jatuhtempo'];
    $datatagih[$rPo['nopo']]['tanggal'] = $rPo['tanggal'];
}

if(!empty($stsposo1)){
    if($stsposo1=='1'){
        ##RELEASE
        $where.=" and b.statuspo in ('2','3') and b.stat_release='1' and closed='0'";
    }
    if($stsposo1=='2'){
        ##UNRELEASE
        $where.=" and b.statuspo in ('0','1')";
    }
    if($stsposo1=='3'){
        ##BECOME OUT STANDING
        $where.=" and b.statuspo in ('2','3') and b.closed='1' and b.keteranganclose like '%,tanggal tutup : %'";	
    }
    if($stsposo1=='4'){
        ##CLOSE
        $where.=" and b.statuspo in ('2','3') and b.closed='1' and (b.keterangan like '%,tanggal tutup : %' or b.keteranganclose like '%Tutup By System%')";
    }
    if($stsposo1=='5'){
        ##CENCEL
        $where.=" and b.statuspo in ('4') and b.closed='1' and (b.keteranganclose like '%,tanggal cancel : %')";
    }
}
// data po
$sPo = "SELECT a.*,b.kodesupplier, b.stat_release, b.statuspo, b.closed, b.keteranganclose, b.keterangan, b.tanggal  from " . $dbname . ".log_po_vw a LEFT JOIN log_poht b ON a.kodesupplier=b.kodesupplier 
where a.tanggal between '" . $tanggal1 . "' 
            and '" . $tanggal2 . "' 
            and a.lokalpusat like '%" . $status1 . "%'
            and a.kodeorg like '%" . $pt1 . "%'
            and a.namasupplier like '%".$nmsigment1."%' 
            ".$where."
            ";
            // exit();
if ($terima1 !== '') {
    $sPo .= " and a.statuspo = '" . $terima1 . "'";
}
$sPo .= "order by nopo, kodebarang";
$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
$qPo->setFetchMode(PDO::FETCH_ASSOC);
while ($rPo = $qPo->fetch()) {
    $kunci = strtoupper($rPo['nopo']) . $rPo['kodebarang'];
    $data[$kunci]['kodebarang'] = $rPo['kodebarang'];
    $data[$kunci]['nopo'] = $rPo['nopo'];
    $data[$kunci]['tanggal'] = $rPo['tanggal'];
    $data[$kunci]['jumlahpesan'] = $rPo['jumlahpesan'];
    $data[$kunci]['satuan'] = $rPo['satuan'];
    $data[$kunci]['matauang'] = $rPo['matauang'];
    $data[$kunci]['kurs'] = $rPo['kurs'];
    $data[$kunci]['hargasatuan'] = $rPo['hargasatuan'];
    $data[$kunci]['totalharga'] = $rPo['hargasatuan'] * $rPo['jumlahpesan'];
    $data[$kunci]['kodesupplier'] = $rPo['kodesupplier'];
	$datapo[] = $rPo['nopo'];
}

$inarrpo = implode("','", $datapo);
## CEK DARI TRANSAKSI DT
$str="select a.notransaksi, b.nopo, a.nopp, a.kodebarang, a.satuan, a.jumlah, b.tanggal from ".$dbname.".log_transaksidt a left join ".$dbname.".log_transaksiht b on a.notransaksi=b.notransaksi where a.nopo in ('".$inarrpo."')";
$res=fetchdata($str);
foreach($res as $val){
	$kunci = strtoupper($val['nopo']) . $val['kodebarang'];
    if (!isset($dataterima[$kunci]['jumlah']))
        $dataterima[$kunci]['jumlah'] = 0;
    $dataterima[$kunci]['notransaksi'] = $val['notransaksi'];
    $dataterima[$kunci]['tanggal'] = $val['tanggal'];
    @$dataterima[$kunci]['jumlah']+=$val['jumlah'];
    @$dataterima[$kunci]['satuan'] = $val['satuan'];
}

## CEK DARI BA SERVICE
$str="select a.nopo,a.kodebarang, a.jumlahpesan as jumlah, b.noba as notransaksi, b.tanggal, a.satuan from ".$dbname.".log_podt a left join ".$dbname.". log_baservis b on a.nopo=b.noso where a.nopo in ('".$inarrpo."') and b.posting='1'";
$res=fetchdata($str);
foreach($res as $val){
	$kunci = strtoupper($val['nopo']) . $val['kodebarang'];
    if (!isset($dataterima[$kunci]['jumlah']))
        $dataterima[$kunci]['jumlah'] = 0;
    $dataterima[$kunci]['notransaksi'] = $val['notransaksi'];
    $dataterima[$kunci]['tanggal'] = $val['tanggal'];
    $dataterima[$kunci]['jumlah']+=$val['jumlah'];
    $dataterima[$kunci]['satuan'] = $val['satuan'];
}

## CEK DARI NON-INVENTORY
$str="select a.nopo,a.kodebarang, a.notransaksi, a.jumlah, b.tanggal, a.satuan  from ".$dbname.".log_penerimaanpodt a left join ".$dbname.".log_penerimaanpoht b on a.notransaksi=b.notransaksi where a.nopo in ('".$inarrpo."')";
$res=fetchdata($str);
foreach($res as $val){
	$kunci = strtoupper($val['nopo']) . $val['kodebarang'];
    if (!isset($dataterima[$kunci]['jumlah']))
        $dataterima[$kunci]['jumlah'] = 0;
    $dataterima[$kunci]['notransaksi'] = $val['notransaksi'];
    $dataterima[$kunci]['tanggal'] = $val['tanggal'];
    $dataterima[$kunci]['jumlah']+=$val['jumlah'];
    $dataterima[$kunci]['satuan'] = $val['satuan'];
}

## CEK DARI NON-INVENTORY
$str="select nopo,kodebarang,jumlah,notransaksi, tanggal, satuan from ".$dbname.".log_noninventorydt_vw where nopo in ('".$inarrpo."')";
$res=fetchdata($str);
foreach($res as $val){
	$kunci = strtoupper($val['nopo']) . $val['kodebarang'];
    if (!isset($dataterima[$kunci]['jumlah']))
        $dataterima[$kunci]['jumlah'] = 0;
    $dataterima[$kunci]['notransaksi'] = $val['notransaksi'];
    $dataterima[$kunci]['tanggal'] = $val['tanggal'];
    @$dataterima[$kunci]['jumlah']+=$val['jumlah'];
    @$dataterima[$kunci]['satuan'] = $val['satuan'];
}

$tab.="</thead><tbody>";
$no=0;
if (!empty($data))
    foreach ($data as $d) {
        $no+=1;
		$tab.="<tr class=rowcontent>";
        $tab.="<td align=center>" . $no . "</td>";
        $tab.="<td>" . $d['nopo'] . "</td>";
        $tab.="<td>" . putertanggal($d['tanggal']) . "</td>";
        $tab.="<td>" . $d['kodebarang'] . "</td>";
        $tab.="<td>" . $namabarang[$d['kodebarang']] . "</td>";
        $tab.="<td>" . $d['matauang'] . "</td>";
        $tab.="<td align=right>" . number_format($d['kurs']) . "</td>";
        $tab.="<td align=right>" . number_format($d['hargasatuan']) . "</td>";
        $tab.="<td align=right>" . number_format($d['jumlahpesan']) . "</td>";
        $tab.="<td>" . $d['satuan'] . "</td>";
        $tab.="<td align=right>" . number_format($d['totalharga']) . "</td>";
        $tab.="<td>" . (isset($namasupplier[$d['kodesupplier']]) ? $namasupplier[$d['kodesupplier']] : '') . "</td>";

        if (!isset($datatagih[$d['nopo']]['noinvoice']))
            $datatagih[$d['nopo']]['noinvoice'] = '';
        if (!isset($datatagih[$d['nopo']]['tanggal']))
            $datatagih[$d['nopo']]['tanggal'] = '';
        if (!isset($datatagih[$d['nopo']]['jatuhtempo']))
            $datatagih[$d['nopo']]['jatuhtempo'] = '';
        if (!isset($databayar[$datatagih[$d['nopo']]['noinvoice']]['cgttu']))
            $databayar[$datatagih[$d['nopo']]['noinvoice']]['cgttu'] = '';
        if (!isset($databayar[$datatagih[$d['nopo']]['noinvoice']]['tanggal']))
            $databayar[$datatagih[$d['nopo']]['noinvoice']]['tanggal'] = '';
        $tab.="<td>" . $databayar[$datatagih[$d['nopo']]['noinvoice']]['cgttu'] . "</td>"; // metode
        $tab.="<td>" . putertanggal($datatagih[$d['nopo']]['tanggal']) . "</td>"; // tanggal invoice
        $tab.="<td>" . $datatagih[$d['nopo']]['noinvoice'] . "</td>"; // no invoice
        $tab.="<td>" . putertanggal($datatagih[$d['nopo']]['jatuhtempo']) . "</td>"; // tanggal jatuh tempo
        $tab.="<td>" . putertanggal($databayar[$datatagih[$d['nopo']]['noinvoice']]['tanggal']) . "</td>"; // tanggal bayar

        $kunci = $d['nopo'] . $d['kodebarang'];
        if (!isset($datakirim[$kunci]['lokasi']))
            $datakirim[$kunci]['lokasi'] = '';
        if (!isset($datakirim[$kunci]['tanggal']))
            $datakirim[$kunci]['tanggal'] = '';
        if (!isset($datakirim[$kunci]['notransaksi']))
            $datakirim[$kunci]['notransaksi'] = '';
        if (!isset($datakirim[$kunci]['jumlah']))
            $datakirim[$kunci]['jumlah'] = 0;
        if (!isset($datakirim[$kunci]['satuan']))
            $datakirim[$kunci]['satuan'] = '';
        $tab.="<td>" . $datakirim[$kunci]['lokasi'] . "</td>";
        $tab.="<td>" . putertanggal($datakirim[$kunci]['tanggal']) . "</td>"; // tanggal pengiriman
        $tab.="<td>" . $datakirim[$kunci]['notransaksi'] . "</td>"; // tanggal pengiriman
        $tab.="<td align=right>" . number_format($datakirim[$kunci]['jumlah']) . "</td>"; // jumlah pengiriman
        $tab.="<td>" . $datakirim[$kunci]['satuan'] . "</td>"; // satuan pengiriman

        if (!isset($dataterima[$kunci]['tanggal']))
            $dataterima[$kunci]['tanggal'] = '';
        if (!isset($dataterima[$kunci]['notransaksi']))
            $dataterima[$kunci]['notransaksi'] = '';
        if (!isset($dataterima[$kunci]['jumlah']))
            $dataterima[$kunci]['jumlah'] = 0;
        if (!isset($dataterima[$kunci]['satuan']))
            $dataterima[$kunci]['satuan'] = '';
        $tab.="<td>" . $dataterima[$kunci]['notransaksi'] . " </td>"; // no transaksi penerimaan gudang
        $tab.="<td>" . putertanggal($dataterima[$kunci]['tanggal']) . "</td>"; // tanggal no transaksi
        $tab.="<td align=right>" . number_format($dataterima[$kunci]['jumlah']) . "</td>"; // jumlah diterima (disum di array)
        $tab.="<td>" . $dataterima[$kunci]['satuan'] . "</td>"; // satuan
        $tab.="</tr>";
    }else {
    $tab.="<tr class=rowcontent>";
    $tab.="<td colspan=26>" . $_SESSION['lang']['dataempty'] . "</td>";
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
        $nop_ = "daftrpo1_" . $dte;
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