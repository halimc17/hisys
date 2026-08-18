<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

/*		
function cekakunkblama($noakun,$kodekegiatan,$kodeasset,$nik,$kodecustomer,$kodesupplier,$kodevhc,$orgalokasi,$noinvoice,$nodok){
	
	#= akun hutang
	if(substr($noakun,0,3)=='211'){
		// if($noinvoice==''){
			// exit("Warning:Jika akun hutang, maka invoice AP tidak boleh kosong");
		// }
		if($nodok==''){
			exit("Warning:Jika akun hutang, maka nomor dokumen tidak boleh kosong");
		}
		if($kodesupplier==''){
			exit("Warning:Jika akun hutang, maka asignment/supplier tidak boleh kosong");
		}
	}
	
	#= akun piutang
	if(substr($noakun,0,3)=='113'){
		// if($noinvoice==''){
			// exit("Warning:Jika akun piutang, maka nomor invoice AR tidak boleh kosong");
		// }
		if($nodok==''){
			exit("Warning:Jika akun piutang, maka nomor dokumen tidak boleh kosong");
		}
		if($kodecustomer==''){
			exit("Warning:Jika akun piutang, maka pelanggan/customer tidak boleh kosong");
		}
		
	}
	
	#= akun biaya langsung kebun
	if(substr($noakun,0,3)=='126' || substr($noakun,0,3)=='128' || substr($noakun,0,3)=='611' || substr($noakun,0,3)=='621'){
		if($kodekegiatan==''){
			exit("Warning:Jika akun tanaman, maka kegiatan/pekerjaan tidak boleh kosong");
		}
		if($orgalokasi==''){
			exit("Warning:Jika akun tanaman, maka alokasi (blok) tidak boleh kosong");
		}
		
		#= cek juga kodekegiatan sudah sama dengan coa belum
		if($noakun!=substr($kodekegiatan,0,7)){
			exit("Warning:pekerjaan/kegiatan tidak sama dinokor akunnya, kodekegiatan : ".$kodekegiatan.", nomor akun : ".$noakun." ");
		}
		
	}
	
	#= akun biaya langsung pabrik
	if(substr($noakun,0,3)=='631' || substr($noakun,0,3)=='632'){
		if($kodekegiatan==''){
			exit("Warning:Jika akun pabrik, maka kegiatan/pekerjaan tidak boleh kosong");
		}
		if($orgalokasi==''){
			exit("Warning:Jika akun pabrik, maka alokasi (mesin) tidak boleh kosong");
		}
	}

	#= akun biaya langsung pabrik
	if(substr($noakun,0,3)=='631' || substr($noakun,0,3)=='632'){
		if($kodekegiatan==''){
			exit("Warning:Jika akun pabrik, maka kegiatan/pekerjaan tidak boleh kosong");
		}
		if($orgalokasi==''){
			exit("Warning:Jika akun pabrik, maka alokasi (mesin) tidak boleh kosong");
		}
	}
	
	#= akun biaya langsung pabrik
	if(substr($noakun,0,3)=='631' || substr($noakun,0,3)=='632'){
		if($kodekegiatan==''){
			exit("Warning:Jika akun pabrik, maka kegiatan/pekerjaan tidak boleh kosong");
		}
		if($orgalokasi==''){
			exit("Warning:Jika akun pabrik, maka alokasi (mesin) tidak boleh kosong");
		}
	}
	
	#= akun adk
	if(substr($noakun,0,5)=='11803'){
		if($nodok==''){
			exit("Warning:Jika akun uang muka, maka nomor dokumen tidak boleh kosong");
		}
		if($nik=='' and $kodesupplier==''){
			exit("Warning:Jika akun uang muka,, maka karyawan atau asignment/supplier tidak boleh kosong");
		}
	}
	
	#= akun traksi
	if(substr($noakun,0,5)=='41102'){
		if($kodevhc==''){
			exit("Warning:Jika akun transit kendaraan, maka kendaraan tidak boleh kosong");
		}
	}
}
*/

function cekakunkb(
	$noakun,
	$kodekegiatan = "",
	$kodeasset = "",
	$nik = "",
	$kodecustomer = "",
	$kodesupplier = "",
	$kodevhc = "",
	$kodeblok = "",
	$noinvoice = "",
	$nodok = ""
) {
	global $dbname;

	if ($noakun === "") {
		exit("Warning: Nomor akun tidak boleh kosong.");
	}

	$query = "SELECT * FROM {$dbname}.keu_5akun WHERE noakun = '{$noakun}'";
	$akunData = fetchdata($query);

	if (empty($akunData)) {
		exit("Warning: Akun '{$noakun}' tidak ditemukan di tabel keu_5akun.");
	}

	$akun = $akunData[0];
	$missingFields = [];

	$fields = [
		'kodekegiatan' => ['input' => $kodekegiatan, 'label' => 'Pekerjaan/Kegiatan'],
		'kodeasset' => ['input' => $kodeasset, 'label' => 'Kode Aset/Proyek'],
		'nik' => ['input' => $nik, 'label' => 'Karyawan'],
		'kodecustomer' => ['input' => $kodecustomer, 'label' => 'Customer'],
		'kodesupplier' => ['input' => $kodesupplier, 'label' => 'Supplier'],
		'kodevhc' => ['input' => $kodevhc, 'label' => 'Kendaraan'],
		'kodeblok' => ['input' => $kodeblok, 'label' => 'Blok/Mesin'],
		'nodok' => ['input' => $nodok, 'label' => 'Nomor Dokumen'],
	];

	foreach ($fields as $key => $config) {
		if (!empty($akun[$key]) && $akun[$key] == '1' && $config['input'] === '') {
			$missingFields[] = $config['label'];
		}
	}

	if (!empty($missingFields)) {
		$fieldList = implode(', ', $missingFields);
		$errInfo = "Akun '{$noakun} - " . getNamaAkun($noakun) . "' membutuhkan data: {$fieldList}.\n";
		exit("Warning: " . $errInfo);
	}
}


function cekakunjm($noakun, $kodekegiatan, $kodeasset, $nik, $kodecustomer, $kodesupplier, $kodevhc, $kodeblok, $noinvoice, $nodok)
{
	$str = "select * from " . $dbname . ".keu_5akun where noakun='" . $noakun . "'";
	// exit("Error:$str");
	$res = fetchdata($str);
	foreach ($res as $bar) {
		$dtkodekegiatan = $bar['kodekegiatan'];
		$dtkodeasset = $bar['kodeasset'];
		$dtnik = $bar['nik'];
		$dtkodecustomer = $bar['kodecustomer'];
		$dtkodesupplier = $bar['kodesupplier'];
		$dtkodevhc = $bar['kodevhc'];
		$dtkodeblok = $bar['kodeblok'];
		$dtnodok = $bar['nodok'];
	}

	if ($dtkodekegiatan == '1') {
		#= cek apakah coa dan kodekegiatan sama atau tidak
		if (substr($kodekegiatan, 0, 7) != $noakun) {
			$warningcoakegiatan .= " Kode kegiatan/pekerjaan dengan nomor akun tidak sama";
		}
	}

	if ($kodekegiatan == '' and $dtkodekegiatan == '1') {
		$warningcode .= "pekerjaan/kegiatan,";
	}
	if ($kodeasset == '' and $dtkodeasset == '1') {
		$warningcode .= " kode adk/project,";
	}
	if ($nik == '' and $dtnik == '1') {
		$warningcode .= " karyawan,";
	}
	if ($kodecustomer == '' and $dtkodecustomer == '1') {
		$warningcode .= " pelanggan/customer,";
	}
	if ($kodesupplier == '' and $dtkodesupplier == '1') {
		$warningcode .= " supplier/kontraktor/vendor,";
	}
	if ($kodevhc == '' and $dtkodevhc == '1') {
		$warningcode .= " kendaraan,";
	}
	if ($kodeblok == '' and $dtkodeblok == '1') {
		$warningcode .= " blok/mesin,";
	}
	if ($nodok == '' and $dtnodok == '1') {
		$warningcode .= " nomor dokumen,";
	}

	if ($warningcode != '') {
		echo " Untuk Akun " . $noakun . " harus mengisikan " . $warningcode . "\n";
		exit("warningsystem");
	}
	if ($warningcoakegiatan != '') {
		echo "" . $warningcoakegiatan . "\n";
		exit("warningsystem");
	}
}
