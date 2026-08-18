<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$per=checkPostGet('per','');
$tpKary=checkPostGet('tpKary','');
$jabatan_ff=checkPostGet('jabatan','');

$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$optgol=  makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');

$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmtipekar=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$namabank=  makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');

$whrtp="";

if($tpKary!='a'){
	$whrtp.=" and c.tipekaryawan='".$tpKary."'";
}

if($jabatan_ff != ''){
	$whrtp.=" and c.kodejabatan='".$jabatan_ff."'";
}

$whereDatabase = 'sdm_5gajipokok';


#bentuk list karyawan
$str="select c.* from ".$dbname.".".$whereDatabase." a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id left join ".$dbname.".datakaryawan_hist c on (a.karyawanid=c.karyawanid and a.tahun=c.periodegaji and c.version_type = 'B' and c.approval_status = '8') where a.kodeorg='".$unit."' and a.tahun='".$per."' ".$whrtp." group by c.karyawanid order by c.namakaryawan asc";
// exit('warning:'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if ($bar['karyawanid']) {
		$dtkarid   		[$bar['karyawanid']]=$bar['karyawanid'];
		$listidkar 		[$bar['karyawanid']]=$bar['karyawanid'];
		$nik       		[$bar['karyawanid']]=$bar['nik'];
		$tipekaryawan   [$bar['karyawanid']]=$bar['tipekaryawan'];
		$nmkar     		[$bar['karyawanid']]=$bar['namakaryawan'];
		$divkar     	[$bar['karyawanid']]=$bar['subbagian'];
		$jabatan   		[$bar['karyawanid']]=$bar['kodejabatan'];
		$golongan	    [$bar['karyawanid']]=$bar['kodegolongan'];
		$lokasitugas	[$bar['karyawanid']]=$bar['lokasitugas'];
		$bagian	   		[$bar['karyawanid']]=$bar['bagian'];
		@$stpajak  		[$bar['karyawanid']]=$bar['statuspajak'];
		@$kodecatu  	[$bar['karyawanid']]=$bar['kodecatu'];	
		$bank      		[$bar['karyawanid']]=$bar['namabank'];
		$rekening  		[$bar['karyawanid']]=$bar['norekeningbank'];
	}
}

#ambil data gaji pokok
$strgj="select a.*,b.* from ".$dbname.".".$whereDatabase." a left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id left join ".$dbname.".datakaryawan_hist c on (a.karyawanid=c.karyawanid and a.tahun=c.periodegaji and c.version_type = 'B' and c.approval_status = '8') where a.kodeorg='".$unit."' and a.tahun='".$per."' ".$whrtp." order by a.idkomponen asc";
// exit('warning:'.$strgj);
$res=$owlPDO->query($strgj) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$dtkomtetp[$bar['id']]=$bar['id'];
	@$nmkom[$bar['id']]=$bar['name'];
	@$rupiah[$bar['karyawanid']][$bar['id']]=$bar['jumlah'];
}

##ambil setup natura kg
$RpNatura = 0;
$sNatura = "select * from " . $dbname . ".sdm_5hargacatukg where unit='" . $unit . "' and status='1'";
$rNatura = fetchData($sNatura);
if (count($rNatura) == 0) {
	exit('warning: Harga catu per kg belum diinput silahkan input pada setup harga catu per kg');
} else {
	$RpNatura = $rNatura[0]['nilai'];
}

$RpNaturaperkelompok = array();
$sNatura = "select * from " . $dbname . ".sdm_5catu where kodeorg='" . $unit . "' and tahun='" . substr($per, 0, 4) . "'";
$rNatura = fetchData($sNatura);
if (count($rNatura) == 0) {
	exit('warning: Kelompok Catu Kg belum disetup, silahkan disetup terlebih dahulu');
} else {
	foreach ($rNatura as $key  => $val) {
		$RpNaturaperkelompok[$val['kelompok']] = ($val['jumlah'] * $RpNatura);
	}
}

/*****************************************************************************************************************/

@$tdttjtetap = count($dtkomtetp)+3;
if($tdttjtetap == 0){
	$hd0 = 'hidden';
}

/*****************************************************************************************************************/
$stream = '';

if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.= "<div class='table-scroll'><table class=sortable cellpadding=7 style='width:100%;' cellspacing=1>";
}

$style_upper = "style=text-transform:uppercase;  align=center";

$stream.="<thead><tr class=rowcontent>";
	$stream.="<th ".$style_upper." rowspan=2>".$_SESSION['lang']['nomor']."</th>";
	$stream.="<th ".$style_upper." rowspan=2>".$_SESSION['lang']['periode']."</th>";
	$stream.="<th ".$style_upper." rowspan=2>".$_SESSION['lang']['unit']." Kerja</th>";
	$stream.="<th ".$style_upper." rowspan=2>".$_SESSION['lang']['nik']."</th>";
	$stream.="<th ".$style_upper." rowspan=2>".$_SESSION['lang']['nama']."</th>";
	$stream.="<th ".$style_upper." rowspan=2>".$_SESSION['lang']['tipekaryawan']."</th>";
	$stream.="<th ".$style_upper." rowspan=2>".$_SESSION['lang']['kodegolongan']."</th>";
	$stream.="<th ".$style_upper." rowspan=2>".$_SESSION['lang']['jabatan']."</th>";
	$stream.="<th ".$style_upper." ".$hd0." colspan=".$tdttjtetap.">KOMPONEN GAJI</th>";
	$stream.="<th ".$style_upper." rowspan=2>TOTAL</th>";
$stream.="</tr>";
$stream.="<tr>";
		foreach ($dtkomtetp as $komplus){
			$stream.="<th ".$style_upper." align=center>".$nmkom[$komplus]."</th>";
		}
		$stream.="<th ".$style_upper." align=center>Natura</th>";
		$stream.="<th ".$style_upper." align=center>Uang Makan/Extra Fooding</th>";
		$stream.="<th ".$style_upper." align=center>Dana Motivasi</th>";
$stream.="</tr>";			

	$stream.="</thead>";
	$no = 0;
	foreach ($dtkarid as $karid){
		$no++;
		$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".$no."</td>";
			$stream.="<td >".$per."</td>";
			$stream.="<td >".$lokasitugas[$karid]."</td>";
			$stream.="<td >".$nik[$karid]."</td>";
			$stream.="<td >".$nmkar[$karid]."</td>";
			$stream.="<td >".$nmtipekar[$tipekaryawan[$karid]]."</td>";
			$stream.="<td >".$optgol[$golongan[$karid]]."</td>";
			$stream.="<td >".$optnmjab[$jabatan[$karid]]."</td>";

			foreach ($dtkomtetp as $komplus){
				$stream.="<td align=right>".number_format($rupiah[$karid][$komplus],0)."</td>";
				@$ttlTjtetap[$karid] += $rupiah[$karid][$komplus];
				@$gtlTjtetap += $rupiah[$karid][$komplus];
			}

			$stream.="<td align=right>".number_format($RpNaturaperkelompok[$kodecatu[$karid]],0)."</td>";
			$stream.="<td >0</td>";
			$stream.="<td >0</td>";

			$gtlTjtetap += $RpNaturaperkelompok[$kodecatu[$karid]];
			$stream.="<td align=right>".number_format($ttlTjtetap[$karid],0)."</td>";
		$stream.="</tr>";	
	}
	$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=8 align=center><b>TOTAL</b></td>";
		$stream.="<td colspan=".$tdttjtetap." align=center></td>";
		$stream.="<td align=center><b>".number_format($gtlTjtetap,0)."</b></td>";
	$stream.="</tr>";	
$stream.="<tbody></table></div>";
switch($proses){
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        $tglSkrg=date("Ymd");
        $nop_="LAPORAN_KOMPONEN_GAJI_KARYAWAN ".$unit."";
        if (strlen($stream) > 0) {
        	if ($handle = opendir('tempExcel')) {
        		while (false !== ($file = readdir($handle))) {
        			if ($file != "." && $file != ".." && $file != "index.html") {
        				 @ unlink('tempExcel/'.$file);
        			}
        		}
        		closedir($handle);
        	}
        	$handle = fopen("tempExcel/".$nop_.".xls", 'w');
        	if (!fwrite($handle, $stream)) {
        		echo "<script language=javascript1.2>
        		parent.window.alert('Can't convert to excel format');
        		</script>";
        		exit;
        	} else {
        		echo "<script language=javascript1.2>
        		window.location='tempExcel/".$nop_.".xls';
        		</script>";
        	}
        	fclose($handle);
        }
        break;
        }
?>