<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
$per = checkPostGet('per', '');
$tpKary = checkPostGet('tpKary', '');
$jabatan_ff = checkPostGet('jabatan', '');
$tipegaji = checkPostGet('tipegaji', '');

$optNmKomponen =  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab =  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$optgol =  makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');

$nmorg =  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmtipekar =  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$namabank =  makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');

$whrtp = "";

if ($tpKary != 'a') {
	$whrtp .= " and tipekaryawan='" . $tpKary . "'";
}

if ($jabatan_ff != '') {
	$whrtp .= " and kodejabatan='" . $jabatan_ff . "'";
}

## Komponen tunjangan tetap
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='KOMTJTETAP'";
$res = fetchdata($str);
$komponentjtetap = $res[0]['nilai'];

## Komponen all bpjs
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='HRBPJSSLIP'";
$res = fetchdata($str);
$komponentjslip = $res[0]['nilai'];

$whereDatabase = '';
if ($tipegaji == 0) {
	$whereDatabase = 'sdm_gaji_vw';
} else {
	$whereDatabase = 'sdm_gaji_kecil_vw';
}



$str = "select distinct(a.idkomponen),plus,name,id from " . $dbname . "." . $whereDatabase . " a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id where id in (" . $komponentjslip . ") and kodeorg='" . $unit . "' and periodegaji='" . $per . "' " . $whrtp;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$dtkombpjs[$bar['id']] = $bar['id'];
	$nmkom[$bar['id']] = $bar['name'];
}

$str = "select distinct(a.idkomponen),plus,name,id from " . $dbname . "." . $whereDatabase . " a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id where id in (" . $komponentjtetap . ") and kodeorg='" . $unit . "' and periodegaji='" . $per . "' " . $whrtp;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$dtkomtetp[$bar['id']] = $bar['id'];
	$nmkom[$bar['id']] = $bar['name'];
}



$str = "select distinct(a.idkomponen),plus,name,id from " . $dbname . "." . $whereDatabase . " a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id where id not in (" . $komponentjtetap . "," . $komponentjslip . ") and kodeorg='" . $unit . "' and periodegaji='" . $per . "' " . $whrtp;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
// if($row<1){
// 	exit("Warning:Data Kosong");
// }

while ($bar = $res->fetch()) {
	if ($bar['plus'] == 1) {
		@$dtkomplus[$bar['id']] = $bar['id'];
	} else {
		@$dtkommin[$bar['id']] = $bar['id'];
	}
	$nmkom[$bar['id']] = $bar['name'];
}

## Ambil tarif BPJS JK Karyawan
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='HRBPJSKER'";
$res = fetchdata($str);
$bpjskar = $res[0]['nilai'];

## RUPIAH BPJS JK KARYAWAN
$str = "select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from " . $dbname . "." . $whereDatabase . " a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id where id in (" . $bpjskar . ") and kodeorg='" . $unit . "' and periodegaji='" . $per . "' " . $whrtp;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$dtkombpjs_jk_kar[$bar['karyawanid']] += $bar['rupiah'];
}

## Ambil tarif BPJS JK PERUSAHAAN
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='HRBPJSPERU'";
$res = fetchdata($str);
$bpjsperusahaan = $res[0]['nilai'];

## RUPIAH BPJS JK PERUSAHAAN
$str = "select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from " . $dbname . "." . $whereDatabase . " a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id where id in (" . $bpjsperusahaan . ") and kodeorg='" . $unit . "' and periodegaji='" . $per . "' " . $whrtp;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$dtkombpjs_jk_per[$bar['karyawanid']] += $bar['rupiah'];
}

## Ambil tarif BPJS Kesehatan Karyawan
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='HRBPJSKES'";
$res = fetchdata($str);
$bpjskes = $res[0]['nilai'];

## RUPIAH BPJS KESEHATAN KARYAWAN
$str = "select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from " . $dbname . "." . $whereDatabase . " a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id where id in (" . $bpjskes . ") and kodeorg='" . $unit . "' and periodegaji='" . $per . "' " . $whrtp;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$dtkombpjs_kesehatan_kar[$bar['karyawanid']] += $bar['rupiah'];
}

## Ambil tarif BPJS Kesehatan perusahaan
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='HRBPJSKESP'";
$res = fetchdata($str);
$bpjskes_perusahaan = $res[0]['nilai'];

## RUPIAH BPJS KESEHATAN PERUSAHAAN
$str = "select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from " . $dbname . "." . $whereDatabase . " a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id where id in (" . $bpjskes_perusahaan . ") and kodeorg='" . $unit . "' and periodegaji='" . $per . "' " . $whrtp;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$dtkombpjs_kesehatan_per[$bar['karyawanid']] += $bar['rupiah'];
}

## Ambil tarif BPJS Pensiun Karyawan
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='HRBPJSPEN'";
$res = fetchdata($str);
$bpjspensiun = $res[0]['nilai'];

## RUPIAH BPJS PENSIUN KARYAWAN
$str = "select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from " . $dbname . "." . $whereDatabase . " a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id where id in (" . $bpjspensiun . ") and kodeorg='" . $unit . "' and periodegaji='" . $per . "' " . $whrtp;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$dtkombpjs_pensiun_kar[$bar['karyawanid']] += $bar['rupiah'];
}

## Ambil tarif BPJS Pensiun Karyawan
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='HRBPJSPENP'";
$res = fetchdata($str);
$bpjspensiun_perusahaan = $res[0]['nilai'];

## RUPIAH BPJS PENSIUN PERUSAHAAN
$str = "select distinct(a.idkomponen),plus,name,id,karyawanid,jumlah as rupiah from " . $dbname . "." . $whereDatabase . " a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id where id in (" . $bpjspensiun_perusahaan . ") and kodeorg='" . $unit . "' and periodegaji='" . $per . "' " . $whrtp;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$dtkombpjs_pensiun_per[$bar['karyawanid']] += $bar['rupiah'];
}

$optTipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$tipeOrg = $optTipe[$unit];

## TARIF BPJS KESEHATAN KARYAWAN
$str = "select bebankaryawan,jenisbpjs from " . $dbname . ".sdm_5bpjs where lokasibpjs = '" . $tipeOrg . "' and jenisbpjs in(" . $bpjskes . ")";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$tarifbpjskesehatan_kar += $bar['bebankaryawan'];
}

## TARIF BPJS KESEHATAN PERUSAHAAN
$str = "select bebanperusahaan,jenisbpjs from " . $dbname . ".sdm_5bpjs where lokasibpjs = '" . $tipeOrg . "' and jenisbpjs in(" . $bpjskes . ")";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$tarifbpjskesehatan_per += $bar['bebanperusahaan'];
}

## TARIF BPJS TK KARYAWAN
$str = "select bebankaryawan,jenisbpjs from " . $dbname . ".sdm_5bpjs where lokasibpjs = '" . $tipeOrg . "' and jenisbpjs in(" . $bpjskar . ")";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$tarifbpjskar += $bar['bebankaryawan'];
}

## TARIF BPJS TK PERUSAHAAN
$str = "select bebanperusahaan,jenisbpjs from " . $dbname . ".sdm_5bpjs where lokasibpjs = '" . $tipeOrg . "' and jenisbpjs in(" . $bpjskar . ")";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$tarifbpjsper += $bar['bebanperusahaan'];
}

## TARIF BPJS JP KARYAWAN
$str = "select bebankaryawan,jenisbpjs from " . $dbname . ".sdm_5bpjs where lokasibpjs = '" . $tipeOrg . "' and jenisbpjs in(" . $bpjspensiun . ")";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$tarifbpjskar_jp += $bar['bebankaryawan'];
}

## TARIF BPJS TK PERUSAHAAN
$str = "select bebanperusahaan,jenisbpjs from " . $dbname . ".sdm_5bpjs where lokasibpjs = '" . $tipeOrg . "' and jenisbpjs in(" . $bpjspensiun . ")";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = $res->rowCount();
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$tarifbpjsper_jp += $bar['bebanperusahaan'];
}


array_multisort($dtkomplus, SORT_ASC);
array_multisort($dtkommin, SORT_ASC);

// $str="select tipelembur,jamaktual,karyawanid,kodeorg from ".$dbname.".sdm_lemburdt where substr(kodeorg,1,4)='".$unit."' and tanggal like '".$per."%'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	if(strlen($bar['kodeorg'])==4){
// 		$bar['kodeorg']='';
// 	}else{
// 		$bar['kodeorg']=$bar['kodeorg'];
// 	}

// 	$bar['kodeorg']=getKary($bar['karyawanid'],'subbagian');
// 	@$jamlembur[$bar['kodeorg']][$bar['karyawanid']]+=$bar['jamaktual'];
// }

#bentuk list karyawan
$str = "select * from " . $dbname . "." . $whereDatabase . " a where kodeorg='" . $unit . "' and periodegaji='" . $per . "' " . $whrtp . " order by namakaryawan asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$dtkarid[$bar['karyawanid']] = $bar['karyawanid'];
	$listidkar[$bar['karyawanid']] = $bar['karyawanid'];
	$nik[$bar['karyawanid']] = $bar['nik'];
	$tipekaryawan[$bar['karyawanid']] = $bar['tipekaryawan'];
	$nmkar[$bar['karyawanid']] = $bar['namakaryawan'];
	$divkar[$bar['karyawanid']] = $bar['subbagian'];
	$jabatan[$bar['karyawanid']] = $bar['kodejabatan'];
	$golongan[$bar['karyawanid']] = $bar['kodegolongan'];
	$bagian[$bar['karyawanid']] = $bar['bagian'];
	@$stpajak[$bar['karyawanid']] = $bar['statuspajak'];
	@$kodecatu[$bar['karyawanid']] = $bar['kodecatu'];
	$rupiah[$bar['karyawanid']][$bar['idkomponen']] = $bar['jumlah'];

	$bank[$bar['karyawanid']] = $bar['namabank'];
	$rekening[$bar['karyawanid']] = $bar['norekeningbank'];

	if ($bar['idkomponen'] == 1) {
		$hk[$bar['karyawanid']] = $bar['hk'];
	}
}

/*****************************************************************************************************************/

@$tdttjtetap = count($dtkomtetp);
if ($tdttjtetap == 0) {
	$hd0 = 'hidden';
}

@$tbrskommin = count($dtkommin);
if ($tbrskommin == 0) {
	$hd1 = 'hidden';
}

@$tbrskomplus = count($dtkomplus);
if ($tbrskomplus == 0) {
	$hd2 = 'hidden';
}

/*****************************************************************************************************************/
$stream = '';

if ($proses == 'excel') {
	$stream .= "<table class=sortable cellspacing=1 border=1>";
} else {
	$stream .= "<div class='table-scroll'><table class=sortable cellpadding=7 style='width:100%;' cellspacing=1>";
}

$style_upper = "style=text-transform:uppercase;  align=center";

$stream .= "<thead><tr class=rowcontent>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['nomor'] . "</th>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['nik'] . "</th>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['nama'] . "</th>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['divisi'] . "</th>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['tipekaryawan'] . "</th>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['kodegolongan'] . "</th>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['status'] . " Pajak</th>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['status'] . " Catu</th>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['bagian'] . "</th>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['jabatan'] . "</th>";
$stream .= "<th " . $style_upper . " rowspan=3>" . $_SESSION['lang']['hk'] . "</th>";
$stream .= "<th " . $style_upper . " " . $hd0 . " colspan=" . $tdttjtetap . ">GAJI / UPAH</th>";
$stream .= "<th " . $style_upper . " rowspan=3>JUMLAH GAJI</th>";
$stream .= "<th " . $style_upper . " " . $hd2 . " colspan=" . $tbrskomplus . ">PREMI/LEMBUR/DLL</th>";
$stream .= "<th " . $style_upper . " rowspan=3>JUMLAH <br>PREMI/LEMBUR/DLL</th>";

$stream .= "<th " . $style_upper . " colspan=2>BPJS KES</th>";
$stream .= "<th " . $style_upper . " colspan=2>BPJS TK</th>";
$stream .= "<th " . $style_upper . " colspan=2>BPJS JP</th>";
$stream .= "<th " . $style_upper . " " . $hd1 . " colspan=" . $tbrskommin . ">POTONGAN</th>";
$stream .= "<th " . $style_upper . " rowspan=3>JUMLAH POTONGAN</th>";
$stream .= "<th " . $style_upper . " rowspan=3>GAJI BRUTO</th>";

$stream .= "<th " . $style_upper . " rowspan=3>PENERIMAAN BERSIH</th>";
$stream .= "<th " . $style_upper . " colspan=4>PEMBAYARAN</th>";
$stream .= "<th " . $style_upper . " rowspan=3>TANDA TERIMA</th>";

$stream .= "</tr>";
$stream .= "<tr>";
foreach ($dtkomtetp as $komplus) {
	$stream .= "<th " . $style_upper . " align=center rowspan=2>" . $nmkom[$komplus] . "</th>";
}


foreach ($dtkomplus as $komplus) {
	$stream .= "<th " . $style_upper . " align=center rowspan=2>" . $nmkom[$komplus] . "</th>";
}
$stream .= "<th " . $style_upper . " >PESERTA</th>";
$stream .= "<th " . $style_upper . " >PERUSAHAAN</th>";

$stream .= "<th " . $style_upper . " >PESERTA</th>";
$stream .= "<th " . $style_upper . " >PERUSAHAAN</th>";

$stream .= "<th " . $style_upper . " >PESERTA</th>";
$stream .= "<th " . $style_upper . " >PERUSAHAAN</th>";

foreach ($dtkommin as $kommin) {
	$stream .= "<th " . $style_upper . " align=center rowspan=2>" . $nmkom[$kommin] . "</th>";
}

$stream .= "<th " . $style_upper . " rowspan=2>" . $_SESSION['lang']['bank'] . "</th>";
$stream .= "<th " . $style_upper . " rowspan=2>" . $_SESSION['lang']['norek'] . "</th>";
$stream .= "<th " . $style_upper . " rowspan=2>TUNAI</th>";
$stream .= "<th " . $style_upper . " rowspan=2>TRANSFER</th>";


$stream .= "</tr>";
$stream .= "<tr>";

if ($tarifbpjskar == '') {
	$tarifbpjskar = 0;
}
if ($tarifbpjsper == '') {
	$tarifbpjsper = 0;
}
if ($tarifbpjskesehatan_kar == '') {
	$tarifbpjskesehatan_kar = 0;
}
if ($tarifbpjskesehatan_per == '') {
	$tarifbpjskesehatan_per = 0;
}
if ($tarifbpjskar_jp == '') {
	$tarifbpjskar_jp = 0;
}
if ($tarifbpjsper_jp == '') {
	$tarifbpjsper_jp = 0;
}

$stream .= "<th " . $style_upper . " >" . number_format($tarifbpjskesehatan_kar, 2) . " %</th>";
$stream .= "<th " . $style_upper . " >" . number_format($tarifbpjskesehatan_per, 2) . " %</th>";
$stream .= "<th " . $style_upper . " >" . number_format($tarifbpjskar, 2) . " %</th>";
$stream .= "<th " . $style_upper . " >" . number_format($tarifbpjsper, 2) . " %</th>";
$stream .= "<th " . $style_upper . " >" . number_format($tarifbpjskar_jp, 2) . " %</th>";
$stream .= "<th " . $style_upper . " >" . number_format($tarifbpjsper_jp, 2) . " %</th>";
$stream .= "</tr>";


$stream .= "</thead>";
$no = 0;
foreach ($dtkarid as $karid) {
	$no++;
	$stream .= "<tr class=rowcontent>";
	$stream .= "<td align=center>" . $no . "</td>";
	$stream .= "<td >" . $nik[$karid] . "</td>";
	$stream .= "<td >" . $nmkar[$karid] . "</td>";
	$stream .= "<td >" . $divkar[$karid] . "</td>";
	$stream .= "<td >" . $nmtipekar[$tipekaryawan[$karid]] . "</td>";
	$stream .= "<td >" . $optgol[$golongan[$karid]] . "</td>";
	$stream .= "<td >" . $stpajak[$karid] . "</td>";
	$stream .= "<td >" . $kodecatu[$karid] . "</td>";
	$stream .= "<td >" . $bagian[$karid] . "</td>";
	$stream .= "<td >" . $optnmjab[$jabatan[$karid]] . "</td>";
	$stream .= "<td align=center>" . $hk[$karid] . "</td>";

	## TotalHK
	@$tthk += $hk[$karid];


	foreach ($dtkomtetp as $komplus) {
		$stream .= "<td align=right>" . number_format($rupiah[$karid][$komplus], 0) . "</td>";
		@$ttlTjtetap[$karid] += $rupiah[$karid][$komplus];
		@$gtlTjtetap += $rupiah[$karid][$komplus];
		@$grandKomTetap[$komplus] += $rupiah[$karid][$komplus];
	}

	$stream .= "<td align=right>" . number_format($ttlTjtetap[$karid], 0) . "</td>";

	foreach ($dtkomplus as $komplus) {
		$stream .= "<td align=right>" . number_format($rupiah[$karid][$komplus], 0) . "</td>";
		@$ttlKomplus[$karid] += $rupiah[$karid][$komplus];
		@$gtlKomplus += $rupiah[$karid][$komplus];
		@$grandKomPlus[$komplus] += $rupiah[$karid][$komplus];
	}

	$stream .= "<td align=right>" . number_format($ttlKomplus[$karid], 0) . "</td>";


	$stream .= "<td align=right>" . number_format($dtkombpjs_kesehatan_kar[$karid], 0) . "</td>";
	$stream .= "<td align=right>" . number_format($dtkombpjs_kesehatan_per[$karid], 0) . "</td>";
	$stream .= "<td align=right>" . number_format($dtkombpjs_jk_kar[$karid], 0) . "</td>";
	$stream .= "<td align=right>" . number_format($dtkombpjs_jk_per[$karid], 0) . "</td>";
	$stream .= "<td align=right>" . number_format($dtkombpjs_pensiun_kar[$karid], 0) . "</td>";
	$stream .= "<td align=right>" . number_format($dtkombpjs_pensiun_per[$karid], 0) . "</td>";

	## Grand Total BPJS per kolom
	@$grandBpjsKesKar += $dtkombpjs_kesehatan_kar[$karid];
	@$grandBpjsKesPer += $dtkombpjs_kesehatan_per[$karid];
	@$grandBpjsTkKar += $dtkombpjs_jk_kar[$karid];
	@$grandBpjsTkPer += $dtkombpjs_jk_per[$karid];
	@$grandBpjsJpKar += $dtkombpjs_pensiun_kar[$karid];
	@$grandBpjsJpPer += $dtkombpjs_pensiun_per[$karid];

	## TotalBPJS Perusahaan + Peserta
	@$totalbpjs_seluruhnya[$karid] = $dtkombpjs_jk_kar[$karid] + $dtkombpjs_kesehatan_kar[$karid] + $dtkombpjs_pensiun_kar[$karid] + $dtkombpjs_jk_per[$karid] + $dtkombpjs_kesehatan_per[$karid] + $dtkombpjs_pensiun_per[$karid];

	## TotalBPJS Karyawan
	@$totalbpjs_karyawan[$karid] = $dtkombpjs_jk_kar[$karid] + $dtkombpjs_kesehatan_kar[$karid] + $dtkombpjs_pensiun_kar[$karid];

	## TotalBPJS Perusahaan
	// @$totalbpjs_perusahaan[$karid] = $dtkombpjs_jk_per[$karid] + $dtkombpjs_kesehatan_per[$karid] + $dtkombpjs_pensiun_per[$karid];

	foreach ($dtkommin as $kommin) {
		$stream .= "<td align=right>" . number_format($rupiah[$karid][$kommin], 0) . "</td>";
		@$ttlkommin[$karid] += $rupiah[$karid][$kommin];
		@$grandKomMin[$kommin] += $rupiah[$karid][$kommin];
	}

	$totalPengurangGaji[$karid] = $ttlkommin[$karid] + $totalbpjs_karyawan[$karid];
	$Gtlkommin += $ttlkommin[$karid] + $totalbpjs_karyawan[$karid];

	$stream .= "<td align=right>" . number_format($totalPengurangGaji[$karid], 0) . "</td>";

	## GAJI BRUTO
	## GAJI BRUTO BPJS PERUSAHAAN 
	@$gajiBruto[$karid] = $ttlTjtetap[$karid]  + $ttlKomplus[$karid];
	@$GTgajiBruto += $ttlTjtetap[$karid]  + $ttlKomplus[$karid];

	$stream .= "<td align=right>" . number_format($gajiBruto[$karid], 0) . "</td>";

	@$gajiBersih[$karid] = $gajiBruto[$karid] - $totalPengurangGaji[$karid];
	@$GTgajiBersih += $gajiBruto[$karid] - $totalPengurangGaji[$karid];
	$stream .= "<td align=right>" . number_format($gajiBersih[$karid], 0) . "</td>";

	$stream .= "<td >" . $namabank[$bank[$karid]] . "</td>";
	$stream .= "<td >" . $rekening[$karid] . "</td>";

	if ($rekening[$karid] == '') {
		$stream .= "<td align=right>" . number_format($gajiBersih[$karid], 0) . "</td>";
		$stream .= "<td align=center>-</td>";
		@$grandTunai += $gajiBersih[$karid];
	} else {
		$stream .= "<td align=center>-</td>";
		$stream .= "<td align=center>" . number_format($gajiBersih[$karid], 0) . "</td>";
		@$grandTransfer += $gajiBersih[$karid];
	}

	if ($no % 2 == 0) {
		$stream .= "<td align=center>" . $no . "</td>";
	} else {
		$stream .= "<td align=left >" . $no . "</td>";
	}
	$stream .= "</tr>";
}
$stream .= "<tr class=rowcontent>";
$stream .= "<td colspan=10 align=center><b>GRAND TOTAL</b></td>";
$stream .= "<td align=center><b>" . $tthk . "</b></td>";

## Grand total per komponen GAJI / UPAH
foreach ($dtkomtetp as $komplus) {
	$stream .= "<td align=right><b>" . number_format($grandKomTetap[$komplus], 0) . "</b></td>";
}
$stream .= "<td align=right><b>" . number_format($gtlTjtetap, 0) . "</b></td>";

## Grand total per komponen PREMI / LEMBUR / DLL
foreach ($dtkomplus as $komplus) {
	$stream .= "<td align=right><b>" . number_format($grandKomPlus[$komplus], 0) . "</b></td>";
}
$stream .= "<td align=right><b>" . number_format($gtlKomplus, 0) . "</b></td>";

## Grand total BPJS per kolom
$stream .= "<td align=right><b>" . number_format($grandBpjsKesKar, 0) . "</b></td>";
$stream .= "<td align=right><b>" . number_format($grandBpjsKesPer, 0) . "</b></td>";
$stream .= "<td align=right><b>" . number_format($grandBpjsTkKar, 0) . "</b></td>";
$stream .= "<td align=right><b>" . number_format($grandBpjsTkPer, 0) . "</b></td>";
$stream .= "<td align=right><b>" . number_format($grandBpjsJpKar, 0) . "</b></td>";
$stream .= "<td align=right><b>" . number_format($grandBpjsJpPer, 0) . "</b></td>";

## Grand total per komponen POTONGAN
foreach ($dtkommin as $kommin) {
	$stream .= "<td align=right><b>" . number_format($grandKomMin[$kommin], 0) . "</b></td>";
}
$stream .= "<td align=right><b>" . number_format($Gtlkommin, 0) . "</b></td>";
$stream .= "<td align=right><b>" . number_format($GTgajiBruto, 0) . "</b></td>";
$stream .= "<td align=right><b>" . number_format($GTgajiBersih, 0) . "</b></td>";

## Pembayaran: BANK dan NO. REK tidak memiliki nilai total
$stream .= "<td align=center>-</td>";
$stream .= "<td align=center>-</td>";
$stream .= "<td align=right><b>" . number_format($grandTunai, 0) . "</b></td>";
$stream .= "<td align=right><b>" . number_format($grandTransfer, 0) . "</b></td>";
$stream .= "<td align=center></td>";
$stream .= "</tr>";
$stream .= "<tbody></table></div>";
switch ($proses) {
	case 'getdivisi':
		$str = "select kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi like '" . $unit . "%' and tipe ='AFDELING' order by kodeorganisasi asc";
		$optdivisi .= "<option value=''></option>";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optdivisi .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}
		echo $optdivisi;
		break;
	case 'preview':
		echo $stream;
		break;

	######EXCEL	
	case 'excel':
		$tglSkrg = date("Ymd");
		$nop_ = "LAPORAN_REKAP_GAJI_KARYAWAN " . $unit . "";
		if (strlen($stream) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/' . $file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
			if (!fwrite($handle, $stream)) {
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
}
