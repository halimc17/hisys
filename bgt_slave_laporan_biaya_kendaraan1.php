<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
$proses   = checkPostGet('proses', '');
$kodeOrg  = checkPostGet('kdUnit1', '');
$thnBudget= checkPostGet('thnBudget1', '');
if ($thnBudget == '') {
    exit("Error:Tahun Budget Tidak Boleh Kosong");
}
if ($kodeOrg == '') {
    exit("Error:Kode Traksi Tidak Boleh Kosong");
}
$optNm = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
//get org
$sOrg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kodeOrg."' ";
$qOrg = $owlPDO->query($sOrg)or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $nmOrg = $rOrg['namaorganisasi'];
}
if (!$nmOrg)
    $nmOrg = $kodeOrg;
//get nama karyawan
$str = "select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid']."";
$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $namakar[$bar->karyawanid] = $bar->namakaryawan;
}
if ($_GET['proses'] == 'excel') {
    $bg = " bgcolor=#DEDEDE";
    $brdr = 1;
    $tab.= "<table>
        <tr><td colspan=4 align=left>". @ $optNm[$kdTraksi]."</td></tr>
        <tr><td colspan=4>".$_SESSION['lang']['rekap']." ".$_SESSION['lang']['byTraski']." ".$_SESSION['lang']['budgetyear'].": ".$thnBudget."</td></tr>
        </table>";
} else {
    $bg = "";
    $brdr = 0;
}

$str = "select * from " . $dbname . ".bgt_kode"; 
$res = fetchdata($str);
foreach($res as $bar){
	$kodebudget[$bar['kodebudget']] = $bar['noakuntrk'];
}

$sdata="SELECT * FROM ".$dbname.".bgt_budget WHERE tipebudget = 'TRK' and tahunbudget = '".$thnBudget."' and kodeorg = '".$kodeOrg."' ";
$qdata=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
$qdata->setFetchMode(PDO::FETCH_ASSOC);
while($rdata=$qdata->fetch()){
    $listvhc[$rdata['kodevhc']]=$rdata['kodevhc'];
    // if(substr($rdata['kodebudget'],0,3)=='M-3'){ // suku cadang, minyak dan pelumas
        // if(substr($rdata['kodebarang'],0,2)=='35'){ // minyak dan pelumas
            // @$dzdata[$rdata['kodevhc']]['bbmo']+=$rdata['rupiah'];
        // }else{ // suku cadang
            // @$dzdata[$rdata['kodevhc']]['suku']+=$rdata['rupiah'];
        // }
    // }else if(substr($rdata['kodebudget'],0,3)=='SDM' or substr($rdata['kodebudget'],0,4)=='EXPL'){ // gaji dan premi
        // if(substr($rdata['kodebudget'],0,7)=='SDM-PRE' or substr($rdata['kodebudget'],0,11)=='EXPL-LEMBUR'){ // premi
            // @$dzdata[$rdata['kodevhc']]['prem']+=$rdata['rupiah'];
        // }else{ // gaji
            // @$dzdata[$rdata['kodevhc']]['gaji']+=$rdata['rupiah'];
        // }
    // }else if($rdata['kodebudget']=='TRANSIT'){ // transit dan biaya umum
        // if($rdata['noakun']=='4110207'){ // pajak dan asuransi
            // @$dzdata[$rdata['kodevhc']]['asur']+=$rdata['rupiah'];
        // }else{ // umum
            // @$dzdata[$rdata['kodevhc']]['umum']+=$rdata['rupiah'];
        // }
    // }else if($rdata['kodebudget']=='SERVICE'){ // servis
        // @$dzdata[$rdata['kodevhc']]['serv']+=$rdata['rupiah'];
    // }else{
        // @$dzdata[$rdata['kodevhc']]['lain']+=$rdata['rupiah']; // untuk trap kalau2 ada yang tidak tertangkap sama yang di atas...
    // }
	
	
	if($rdata['noakun']==''){
		$rdata['noakun'] = $kodebudget[$rdata['kodebudget']]; 
	}
	@$dzdata[$rdata['kodevhc']][$rdata['noakun']]+=$rdata['rupiah'];

	$nakun[$rdata['noakun']] = getNamaAkun($rdata['noakun']);
}

ksort($nakun);

if(!empty($listvhc))sort($listvhc);
$tab.="<table cellspacing=1 cellpadding=5 border=".$brdr." class=sortable><thead>";
$tab.="<tr class=rowheader>";
$tab.="<th align=center ".$bg.">No.</th>";
$tab.="<th align=center ".$bg.">".$_SESSION['lang']['kodevhc']."</th>";
$tab.="<th align=center ".$bg.">".$_SESSION['lang']['nopol']."</th>";
$tab.="<th align=center ".$bg.">".$_SESSION['lang']['detail']."</th>";


foreach($nakun as $noakun => $namaakun){	
	$tab.="<th align=center ".$bg.">".$namaakun."</th>";
}
$tab.="<th align=center ".$bg.">".$_SESSION['lang']['total']."</th></tr></thead><tbody>";
$no=0;
if(!empty($listvhc))foreach($listvhc as $vhc){
    $no+=1;
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=center>".$no."</td>";
    $tab.="<td>".$vhc."</td>";
    $tab.="<td>".getNopol($vhc)."</td>";
    $tab.="<td>".getNopol($vhc,'x')."</td>";
	$subtotal=0;
	foreach($nakun as $noakun => $namaakun){	
		$tab.="<td align=right>".@number_format($dzdata[$vhc][$noakun])."</td>";
		@$subtotal+=$dzdata[$vhc][$noakun];
		$data[$noakun]+=$dzdata[$vhc][$noakun];
	}
	
    $tab.="<td align=right>".@number_format($subtotal)."</td>";
    $tab.="</tr>";
}else{
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=center colspan=8>".$_SESSION['lang']['dataempty']."</td>";
    $tab.="</tr>";
}
$tab.="</tbody><thead><tr class=rowheader  bgcolor=#DEDEDE>";
$tab.="<td align=center colspan=4>".$_SESSION['lang']['total']."</td>";

foreach($nakun as $noakun => $namaakun){	
	$tab.="<td align=right>".@number_format($data[$noakun])."</td>";
    @$total+=$data[$noakun];
}
$tab.="<td align=right>".number_format($total)."</td>";
$tab.="</tr>";
$tab.="</thead></table>";
switch ($proses) {
case 'preview':
    echo $tab;
    break;
case 'excel':
    $tab.= "Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $dte = date("YmdHis");
    $nop_ = "RekaplaporanBiayaKendaran_".$dte;
    if (strlen($tab) > 0) {
        if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != "index.html") {
                     @ unlink('tempExcel/'.$file);
                }
            }
            closedir($handle);
        }
        $handle = fopen("tempExcel/".$nop_.".xls", 'w');
        if (!fwrite($handle, $tab)) {
            echo "<script language=javascript1.2>
            parent.window.alert('Can't convert to excel format');
            </script>";
            exit;
        } else {
            echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls';
            </script>";
        }
        // closedir($handle);
    }
    break;
default ;
    break;
}
?>