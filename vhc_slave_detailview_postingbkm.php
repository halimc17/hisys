<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses = checkPostGet('proses','');
$tipe= checkPostGet('tipe','');
$jenis= checkPostGet('jenis','');
$param = $_POST;
if(count($param)==0){$param = $_GET;}


$optKegiatan=makeOption($dbname, 'vhc_kegiatan', 'kodekegiatan,namakegiatan');
$optSatKegiatan =makeOption($dbname, 'vhc_kegiatan', 'kodekegiatan,satuan');
$optNamaKary    =makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNIKary      =makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$optNamaBrg     =makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
// $optGudang=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$optkodeorg=makeOption($dbname,'vhc_spl_aktifitas','notransaksi,kodeorg',"notransaksi='".$param['notransaksi']."'");


switch($tipe) {
    case "LC":
        $title = strtoupper("Land Clearing");
    break;
    case "BBT":
		$title = strtoupper($_SESSION['lang']['pembibitan']);
	break;
    case "TBM":
		$title = strtoupper("UPKEEP-".$_SESSION['lang']['tbm']);
	break;
    case "TM":
		$title = strtoupper("UPKEEP-".$_SESSION['lang']['tm']);
	break;
	case "PNN":
		$title = strtoupper($_SESSION['lang']['panen']);
	break;
    case "TB":
		$title = strtoupper("UPKEEP-".$_SESSION['lang']['tbm']);
	break;
	case "BKM":
		$title = strtoupper("BUKU KEGIATAN MANDOR");
	break;
	case "SPL":
		$title = strtoupper("BKM SIPIL");
	break;
    default:
	echo "Error : Atribut not Defined";
	exit;
	break;
}


	$tab.="<table cellpadding=5 ".$border." class=sortable >";
		$tab.="<tr class=rowcontent>";
				$tab.="<td >Transaksi</td>";
				$tab.="<td align=center>:</td>";
				$tab.="<td align=center>".$title."</td>";
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
				$tab.="<td >No.Transaksi</td>";
				$tab.="<td align=center>:</td>";
				$tab.="<td align=center>".$param['notransaksi']."</td>";
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
				$tab.="<td >Unit</td>";
				$tab.="<td align=center>:</td>";
				$tab.="<td align=center>".$optkodeorg[$param['notransaksi']]."</td>";
		$tab.="</tr>";
		
		$tab.="</table><br>";



	$tab.="<table cellpadding=5 ".$border." class=sortable width=100%><thead>";
	$tab.="<tr class=rowheader>";
	$tab.="<th align=center>No</th>";
	$tab.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
	$tab.="<th align=center>No BKM</th>";
	$tab.="<th align=center>".$_SESSION['lang']['namakaryawan']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['namakegiatan']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['satuan']."</th>";
	$tab.="<th align=center>Alokasi</th>";
	$tab.="<th align=center>".$_SESSION['lang']['keterangan']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['hasilkerjad']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['jhk']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['umr']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['upahpremi']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['total']."</th>";
	$tab.="</tr></thead><tbody>";

	$no = 0;
    $str = "select * from ".$dbname.". vhc_spl_kehadiran_vw where notransaksi = '".$param['notransaksi']."'";
	$res=fetchdata($str);
	foreach($res as $bar){
		$no++;
		$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['notransaksi']."</td>";
			$tab.="<td align=center>".$bar['nobkm']."</td>";
			$tab.="<td>".$optNIKary[$bar['nik']]." - ".strtoupper($optNamaKary[$bar['nik']])."</td>";
			$tab.="<td align=center>".$bar['kodekegiatan']." - ".$optKegiatan[$bar['kodekegiatan']]."</td>";
			$tab.="<td align=center>".$optSatKegiatan[$bar['kodekegiatan']]."</td>";
			$tab.="<td align=center>".$bar['alokasi']."</td>";
			$tab.="<td align=center>".$bar['keterangan']."</td>";
			$tab.="<td align=center>".number_format($bar['hasilkerja'],2)."</td>";
			$tab.="<td align=center>".number_format($bar['jhk'],2)."</td>";
			$tab.="<td align=center>".number_format($bar['umr'],2)."</td>";
			$tab.="<td align=center>".number_format($bar['premi'],2)."</td>";
			$tab.="<td align=center>".number_format($bar['premi'] + $bar['umr'],2)."</td>";
		$tab.="</tr>";

		$totalHJ += $bar['hasilkerja'];
		$totalHK += $bar['jhk'];
		$totalUM += $bar['umr'];
		$totalPR += $bar['premi'];
		$totalTT += $bar['premi'] + $bar['umr'];
	}

	$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=8><b>TOTAL</b></td>";
		$tab.="<td align=center>".number_format($totalHJ,2)."</td>";
		$tab.="<td align=center>".number_format($totalHK,2)."</td>";
		$tab.="<td align=center>".number_format($totalUM,2)."</td>";
		$tab.="<td align=center>".number_format($totalPR,2)."</td>";
		$tab.="<td align=center>".number_format($totalTT,2)."</td>";
	$tab.="</tr></tbody></table><br><hr>";

   	$tab.="<button id=postingbtn class=mybutton onclick=postingDataDetail('".$param['notransaksi']."')>" . $_SESSION['lang']['posting'] . "</button>";

	echo $tab;
		
   
?>