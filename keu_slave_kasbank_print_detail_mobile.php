<?php
ini_set('display_errors',0);
ini_set("session.auto_start", 0);
// error_reporting(0);
session_start();
require_once('config/connection.php');
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	$validasiGetMobile = explode(" ", $_GET['par']);
	if($validasiGetMobile[0] == "owlApp" or $validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
        $strlang=$owlPDO->query("select legend,ID from ".$dbname.".bahasa order by legend");
        $strlang->setFetchMode(PDO::FETCH_NUM);
        while($barlang=$strlang->fetch()) {
            $_SESSION['lang'][$barlang[0]]=$barlang[1];
        }
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
}
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');


require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$param 				= $_GET;
$urlefil			= checkPostGet('urlefil','0');

$nmMt 				=  makeOption($dbname, 'setup_matauang', 'kode,matauang');
$nmorg 				=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmakun 			=  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$nmket 				=  makeOption($dbname, 'keu_5keterangan', 'id_ket,keterangan');
$nmbank 			=  makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');


$nmaruskas 			= makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas');


#= approval karyawan
$str = "select * from ".$dbname.".datakaryawan  where karyawanid in (select karyawanid from ".$dbname.".approval where jenispersetujuan='KASBANK') ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$nmkaryawan[$bar['karyawanid']]=$bar['namakaryawan'];
	
}


/** Report Prep **/
$cols 	= array();

#=============================== Header ======================================= keu_kasbankht
$whereH = "notransaksi='".$param['notransaksi'].
    "' and kodeorg='".$param['kodeorg'].
    "' and noakun='".$param['noakun'].
    "' and tipetransaksi='".$param['tipetransaksi']."'";
$queryH = selectQuery($dbname,'keu_kasbankht','*',$whereH);
$resH 	= fetchData($queryH);


# Get Nama Pembuat
$userId = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',
    "karyawanid='".$resH[0]['userid']."'");
# Get Nama Akun Hutang
$namaakunhutang = makeOption($dbname,'keu_5akun','noakun,namaakun',
    "noakun='".$resH[0]['noakunhutang']."'");
#Get tipe Lokasi Tugas
$tipeLokasiTugas = makeOption($dbname,'organisasi','kodeorganisasi,tipe');

#=============================== Detail =======================================
# Data
$col1 = 'noakun,jumlah,noaruskas,matauang,kode,keterangan3';
$cols = array('nourut','noakun','namaakun','noaruskas','debet','kredit');
$colshtml = array('nourut','noakun','namaakun','noaruskas','debet','kredit','keterangan');
//$col1 = 'noakun,jumlah,noaruskas,matauang,kode,hutangunit1';
//$cols = array('nomor','noakun','namaakun','matauang','debet','kredit','hutangunit');
$where = "notransaksi='".$param['notransaksi'].
    "' and kodeorg='".$param['kodeorg'].
    "' and noakun2a='".$param['noakun'].
    "' and tipetransaksi='".$param['tipetransaksi']."'";
$query = selectQuery($dbname,'keu_kasbankdt',$col1,$where);
$res = fetchData($query);

# Data Empty
if(empty($res)) {
    echo 'Data Empty';
    exit;
}

# Options
$whereAkun = "noakun in (";
$whereAkun .= "'".$resH[0]['noakun']."'";
$whereAkun .= ",'".$resH[0]['noakunhutang']."'"; // tambahin kamus nama akun hutangunit 
foreach($res as $key=>$row) {
    $whereAkun .= ",'".$row['noakun']."'";
}
$whereAkun .= ")";
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereAkun);
$optHutangUnit = array('0'=>'Tidak','1'=>'Ya');

# Data Show
$data = array();

#================================ Prep Data ===================================
# Total
$totalDebet = 0;$totalKredit = 0;

# Dari Header
$i=1;
$data[$i] = array(
    'nomor'=>@$i,
    'noakun'=>@$resH[0]['noakun'],
    'namaakun'=>@$optAkun[$resH[0]['noakun']],
    'noaruskas'=>@$resH[0]['noaruskas'],
    'debet'=>0,
    'kredit'=>0,
    'keterangan3'=>$resH[0]['keterangan'],
);

if($param['tipetransaksi']=='M') {
    $data[$i]['debet'] = $resH[0]['jumlah'];
    $totalDebet += $resH[0]['jumlah'];

} else {
    $data[$i]['kredit'] = $resH[0]['jumlah'];
    $totalKredit += $resH[0]['jumlah'];

}

if(substr($resH[0]['noakun'],0,5)=='11102')
{
        if($resH[0]['tipetransaksi']=='K'){
                $title = strtoupper($_SESSION['lang']['bank']." (".$_SESSION['lang']['keluar'].")");
        }else{
                $title = strtoupper($_SESSION['lang']['bank']." (".$_SESSION['lang']['masuk'].")");
        }
}
else
{
        if($resH[0]['tipetransaksi']=='K'){
                $title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['keluar'].")");
        }else{
                $title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['masuk'].")");
        }
}


$i++;

# Dari Detail
foreach($res as $row) {
    $data[$i] = array(
                'nomor'=>$i,
                'noakun'=>$row['noakun'],
                'namaakun'=>$optAkun[$row['noakun']],
                'noaruskas'=>$row['noaruskas'],
                'debet'=>0,
                'kredit'=>0,
        'keterangan3'=>$row['keterangan3'],
    );
//	'hutangunit1'=>$optHutangUnit[$row['hutangunit1']]
    if($param['tipetransaksi']=='M' and $row['jumlah']>0) {
        $data[$i]['kredit'] = $row['jumlah'];
        $totalKredit += $row['jumlah'];
    }
    else if($param['tipetransaksi']=='K' and $row['jumlah']<0){
        $data[$i]['kredit'] = $row['jumlah']*-1;
        $totalKredit += $row['jumlah']*-1;        
    }
    else if($param['tipetransaksi']=='M' and $row['jumlah']<0){
        $data[$i]['debet'] = $row['jumlah']*-1;
        $totalDebet += $row['jumlah']*-1;        
    }    
    else {
        $data[$i]['debet'] = $row['jumlah'];
        $totalDebet += $row['jumlah'];
    }
    $i++;
}

// nyusun berdasarkan debet dulu, abis itu baru kredit. by dz
if(!empty($data)) foreach($data as $c=>$key) {
    $sort_debet[] = $key['debet'];
    $sort_kredit[] = $key['kredit'];
}

// sort
if(!empty($data))array_multisort($sort_debet, SORT_DESC, $sort_kredit, SORT_ASC, $data);

$align 				= explode(",","R,R,L,L,R,R,L,L");
$length 			= explode(",","7,12,35,10,18,18,10");
$titleDetail 		= 'Detail';

$str 				= "select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";
$res 				= $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar 				= $res->fetch();
$tanggalht 			= $bar['tanggal'];
$novoucherht		= $bar['novoucher'];
$noakunht 			= $bar['noakun'];
$rekeninght 		= $bar['rekening'];
$cgttuht 			= $bar['cgttu'];
$nocekht 			= $bar['nocek'];
$jumlahht 			= $bar['jumlah'];
$tipetransaksiht 	= $bar['tipetransaksi'];
$kodeorght 			= $bar['kodeorg'];
$bayarkepada 		= $bar['bayarkepada'];

$str 				= "select * from ".$dbname.".keu_5akunbank where noakun='".$rekeninght."'";
$res 				= $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar 				= $res->fetch();	
$norekeningsetup 	= $bar['rekening'];
$kodebanksetup 		= $bar['namabank'];
$atasnamasetup 		= $bar['atasnama'];

$str 				= "select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' and kodesupplier!=''";
$res 				= $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar 				= $res->fetch();			
$kodesupplier 		= $bar['kodesupplier'];	

$str 				= "select * from ".$dbname.".log_5supplier where supplierid='".$kodesupplier."'";
$res 				= $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar 				= $res->fetch();	
$namasupplier 		= $bar['namasupplier'];		


$cellpadding 		= 2;	

$tab = "<style>
@page {
	margin-top: 25px;
	margin-left: 30px;
	margin-right: 50px;
	margin-bottom: 50px;
}
body {
	font-family: Tahoma, Verdana, Segoe, sans-serif;
}

footer {
	position: fixed; 
	bottom: -20px; 
	left: 0px; 
	right: 0px;
	height: 50px; 
}

</style>";


if($tipetransaksiht=='M'){
	$namavoucher='BUKTI MASUK';
}else{
	$namavoucher='BUKTI PENGELUARAN';
}			

$cellpadding=1;
$fontsize='10px';
$tab.="<table width=115% style='font-size:".$fontsize."' border=0 cellpadding=".$cellpadding.">";	
$tab.="<tr>";
$tab.="<td colspan=4><b>".$nmorg[$kodeorght]."</b></td>"; 
$tab.="<td colspan=2 align=left><b>".$namavoucher."</b></td>"; 
$tab.="</tr>";
$tab.="<tr>";
$tab.="<td colspan=4></td>";  
$tab.="<td>".$_SESSION['lang']['novoucher']."</td>"; 
$tab.="<td>: ".$novoucherht."</td>"; 
$tab.="</tr>";

$tab.="<tr>";
if($tipetransaksiht=='M'){
	$tab.="<td style='width:30px'>Terima Dari</td>"; 
	$tab.="<td style='width:200px' colspan=3>: ".$bayarkepada."</td>"; 
}else{
	$tab.="<td style='width:30px'>".$_SESSION['lang']['bayarke']."</td>"; 
	$tab.="<td style='width:200px' colspan=3>: ".$bayarkepada."</td>"; 
}
$tab.="<td style='width:30px'>".$_SESSION['lang']['tanggal']."</td>"; 
$tab.="<td style='width:30px'>: ".tanggalnormal($tanggalht)."</td>"; 
$tab.="</tr>";
$tab.="<tr>";
$tab.="<td colspan=4></td>"; 
$tab.="<td style='width:30px'>".$_SESSION['lang']['noreferensi']."</td>"; 
$tab.="<td style='width:30px'>: ".$param['notransaksi']."</td>"; 
$tab.="</tr>";
$tab.="</table>";

$tab.="<br>";

$cellpadding=2;
$cellspacing=0;

$tab.="<table style='font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing." width=100%>";	
$tab.="<tr>";
$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['nourut']."</td>"; 
$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['noakun']."</td>"; 
$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['nodok']."</td>"; 
$tab.="<td style='width=100%;border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['keterangan']."</td>"; 
$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['control']."</td>"; 
$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['tipe']."</td>"; 
$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['jumlah']."</td>"; 
$tab.="</tr>";	

$tab.="<tr>";
$tab.="<td colspan=7>&nbsp;</td>"; 
$tab.="</tr>";
$optketerangan =  makeOption($dbname,'keu_5keterangan','id_ket,keterangan');
$str = "select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$tipe='';
	if($bar['keterangan1']!=''){
		$tipe='AP';
	}
	@$no+=1;
	$tab.="<tr>";
	$tab.="<td align=center valign=top>".$no."</td>"; 
	$tab.="<td align=center valign=top>".$bar['noakun']."</td>"; 
	$tab.="<td align=center valign=top>".$bar['keterangan1']."</td>"; 
	$tab.="<td align=left valign=top>".$nmakun[$bar['noakun']]."<br>".$bar['keterangan3']."</td>"; 
	$tab.="<td align=center valign=top>".$bar['nodok']."</td>"; 
	$tab.="<td align=center valign=top>".$tipe."</td>"; 
	$tab.="<td  align=right valign=top>".number_format($bar['jumlah'],2)."</td>"; 
	$tab.="</tr>";
	@$totaldt+=$bar['jumlah'];
}

$tab.="<tr>";
$tab.="<td colspan=7 height=50px>&nbsp;</td>"; 
$tab.="</tr>";

$tab.="<tr>";
$tab.="<td colspan=6 align=right style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['total']." ".$_SESSION['lang']['jumlah'].":</td>"; 
$tab.="<td  align=right style='width:10px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($totaldt,2)."</td>"; 
$tab.="</tr>";

$tab.="<tr>";
$tab.="<td align=left colspan=3 valign=top>".$noakunht."<br>".$nmakun[$noakunht]."</td>"; 
$tab.="<td align=left  valign=top>".$nmbank[$kodebanksetup]."&nbsp;&nbsp;".$atasnamasetup."&nbsp;&nbsp;".$norekeningsetup."</td>"; 
$isi="";
if($nocekht!=''){
	$isi="".$_SESSION['lang']['nomor']." : ".$nocekht."";
}

$tab.="<td align=left valign=top>".$_SESSION['lang']['tipe']." : ".$cgttuht."<br>".$isi."</td>"; 
$tab.="<td align=right valign=top>".$_SESSION['lang']['total']." ".$_SESSION['lang']['jumlah']."</td>"; 
$tab.="<td  align=right valign=top>".number_format($jumlahht,2)."</td>"; 
$tab.="</tr>";
$tab.="<tr>";
$tab.="<td colspan=7>&nbsp;</td>"; 
$tab.="</tr>";
$tab.="</table>";	

$tab.="<table style='width:100%;font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
$tab.="<tr>";
$tab.="<td align=center>".$_SESSION['lang']['persetujuan']."</td>"; 
$tab.="<td style='width:50px'>&nbsp;</td>"; 
$tab.="<td align=center>".$_SESSION['lang']['diperiksaoleh']."</td>"; 
$tab.="<td style='width:50px'>&nbsp;</td>"; 
$tab.="<td align=center>".$_SESSION['lang']['dibuatoleh']."</td>";
$tab.="<td style='width:50px'>&nbsp;</td>"; 
$tab.="<td align=center>".$_SESSION['lang']['penerima']."</td>"; 
$tab.="</tr>";
$tab.="<tr>";
$tab.="<td style='height:50px;border-bottom:0.5px solid #000000;'>&nbsp;</td>"; 
$tab.="<td align=center></td>"; 
$tab.="<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>";
$tab.="<td align=center></td>"; 				
$tab.="<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>"; 
$tab.="<td align=center></td>"; 
$tab.="<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>"; 
$tab.="</tr>";
$tab.="</table>";	


$dompdf = new Dompdf();
$dompdf->loadHtml($tab);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
if($urlefil=='0'){
	$dompdf->stream("PrintKASBANK_".$param['notransaksi'],array("Attachment"=>0));
}else{
	file_put_contents($urlefil, $dompdf->output());
}

?>