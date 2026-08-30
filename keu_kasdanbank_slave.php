<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/cekakun.php');
// exit("Error:A");
require_once('dompdf/autoload.inc.php');
include_once('lib/terbilang.php');
include_once('lib/HtmlExcel.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';

use Dompdf\Dompdf;
// error_reporting(0);



$method = checkPostGet('method', '');
$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}
// $kodept = checkPostGet('kodept','');

$stylehidden = "style='display:none'";
// $path   = "fileupload/keu_kasbankht/";
$path   = "fileupload/keu_kasbankx/";

$str = "select * from " . $dbname . ".setup_filesize where transaksi='keu_kasbank'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$filesize = $bar['filesize'];
}

#= coa ayat silang
$str = "select * from " . $dbname . ".setup_parameterappl where kodeaplikasi='GL' and kodeparameter='GLAS'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$noakunayatsilang = $bar['nilai'];
}

#= coa ayat silang
$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun='" . $noakunayatsilang . "' and noaruskas like '1%'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$noaruskasayatsilang = $bar['noaruskas'];
}

#= setup matauang
$str = "select * from " . $dbname . ".setup_matauang";
$res = fetchdata($str);
foreach ($res as $bar) {
	$namamatauang[$bar['kode']] = $bar['matauang'];
}

$table = 'keu_kasbankht';
$tabledt = 'keu_kasbankdt';
$arrhutangunit = array("0" => "Tidak", "1" => "Ya");

$tab = "";

$optunit = $optsupplier = $optsumberlain = $optunitap = $optcustomer = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)='4'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmorganisasi[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
	$tipeorganisasi[$bar['kodeorganisasi']] = $bar['tipe'];
	$kodept[$bar['kodeorganisasi']] = $bar['induk'];
	$optunit .= "<option value='" . $bar['kodeorganisasi'] . "'>[" . $bar['kodeorganisasi'] . "] " . $bar['namaorganisasi'] . "</option>";
}


$str = "select * from " . $dbname . ".log_5supplier order by namasupplier asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optsupplier .= "<option value='" . $bar['supplierid'] . "'>" . $bar['namasupplier'] . "</option>";
	$nmsupplier[$bar['supplierid']] = $bar['namasupplier'];
}

$str = "select * from " . $dbname . ".log_5masterbarang where kelompokbarang='400'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmbarangpabrik[$bar['kodebarang']] = $bar['namabarang'];
}

$arrtipeinvoice = makeOption($dbname, 'keu_5jenistagihan', 'kode,namajenis');

// $arrtransaksi=array("PJDUM"=>"Uang Muka Perjalanan Dinas",
// "PJD"=>"Perjalanan Dinas",
// "OBAT"=>"Pengobatan",
// "KONTAN"=>"Kontanan",
// "VATINOUT"=>"Tax PPn"); 
// foreach($arrtransaksi as $val=>$nama){
// $optsumberlain.="<option value='".$val."'>".$nama."</option>";
// }  

$optsumberlain .= "<option value='feepanen'>Fee Panen</option>";
$optsumberlain .= "<option value='umpjdinas'>Pemby Uang Muka Pj. Dinas</option>";
// $optsumberlain.="<option value='realpjdinas'>Pemby Pj. Dinas (tiket pesawat dll)</option>";
$optsumberlain .= "<option value='claimpjdinas'>Pemby Klaim Pj. Dinas</option>";
$optsumberlain .= "<option value='batalpjd'>Pengembalian UM Pj. Dinas (BATAL DINAS)</option>";



$str = "select * from " . $dbname . ".keu_5akun";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmakun[$bar['noakun']] = $bar['namaakun'];
}

$str = "select * from " . $dbname . ".keu_5akunbank_vw";
$res = fetchdata($str);
foreach ($res as $bar) {
	$dtnamabank[$bar['noakun']] = $bar['namabank'];
}

$str = "select * from " . $dbname . ".keu_5aruskas where level=3";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmaruskas[$bar['noaruskas']] = $bar['nama_aruskas'];
}

$str = "select * from " . $dbname . ".pmn_4customer";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optcustomer .= "<option value='" . $bar['kodecustomer'] . "'>" . $bar['kodecustomer'] . " - " . $bar['namacustomer'] . "</option>";
	$nmcustomer[$bar['kodecustomer']] = $bar['namacustomer'];
}


// exit("Error:$method");
switch ($method) {
	case 'getakunpenerima':

		// $tipeorgnew = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');

		// if ($tipeorgnew[$param['kodeorg']] == 'HOLDING') {
		// 	$whr = "and (pemilik = '" . $param['kodeorg'] . "' or pemilik = 'HOLDING' or pemilik = 'GLOBAL')";
		// } else if ($tipeorgnew[$param['kodeorg']] == 'KEBUN') {
		// 	$whr = "and (pemilik = '" . $param['kodeorg'] . "' or pemilik = 'KEBUN' or pemilik = 'GLOBAL')";
		// } else if ($tipeorgnew[$param['kodeorg']] == 'KANWIL') {
		// 	$whr = "and (pemilik = '" . $param['kodeorg'] . "' or pemilik = 'KANWIL' or pemilik = 'GLOBAL')";
		// } else {
		// 	$whr = "and (pemilik = '" . $param['kodeorg'] . "' or pemilik = 'PABRIK' or pemilik = 'GLOBAL')";
		// }

		$optnoakun .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		if ($param['notransaksi'] != '') {
			$str = "select * from " . $dbname . "." . $table . " where notransaksi='" . $param['notransaksi'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$norek = $bar['noakun2'];
		}

		if ($param['kodeorg'] != '') {
			$whnoakun = '';
			if ($param['noakun'] != '') {
				$whnoakun = " AND a.noakun not in ('" . $param['noakun2'] . "', '" . $noakunayatsilang . "')";
			}

			// $str = "SELECT * FROM " . $dbname . ".keu_5akun where noakun like '111%' and noakun < '11103' " . $whr . " and " . $whnoakun . " detail=1 and aktif=1";
			$str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun WHERE a.kasbank = 1 AND a.detail = 1 AND a.aktif = 1 AND a.level = '5' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('" . $param['kodeorg'] . "')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik = '{$tipeorganisasi[$param['kodeorg']]}' OR a.pemilik IN ('" . $param['kodeorg'] . "')))) {$whnoakun} GROUP BY a.noakun";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				// $optnoakun[$bar['noakun']]=$bar['noakun'];
				if ($bar['noakun'] == $norek) {
					$optnoakun .= "<option selected value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
				} else {
					$optnoakun .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
				}
			}
		}

		echo $optnoakun;

		break;

	case 'pilihautokb':
		# Get PT berdasarkan detail akses
		$listPT = join("','", array_keys(getOrgDetail(3)));
		$qOrganisasi = selectQuery($dbname, "organisasi", "kodeorganisasi,namaorganisasi", "LENGTH(kodeorganisasi) = 4 AND induk IN ('{$listPT}')");
		$rOrganisasi = fetchData($qOrganisasi);
		$optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($rOrganisasi as $row) {
			$optOrg .= "<option value='" . $row['kodeorganisasi'] . "'>{$row['kodeorganisasi']} - " . $row['namaorganisasi'] . "</option>";
		}

		echo $optOrg;
		break;

	case 'getakunpengirim':

		// $tipeorgnew = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');

		// if ($tipeorgnew[$param['kodeorg']] == 'HOLDING') {
		// 	$whr = "and (pemilik = '" . $param['kodeorg'] . "' or pemilik = 'HOLDING' or pemilik = 'GLOBAL')";
		// } else if ($tipeorgnew[$param['kodeorg']] == 'KEBUN') {
		// 	$whr = "and (pemilik = '" . $param['kodeorg'] . "' or pemilik = 'KEBUN' or pemilik = 'GLOBAL')";
		// } else if ($tipeorgnew[$param['kodeorg']] == 'KANWIL') {
		// 	$whr = "and (pemilik = '" . $param['kodeorg'] . "' or pemilik = 'KANWIL' or pemilik = 'GLOBAL')";
		// } else {
		// 	$whr = "and (pemilik = '" . $param['kodeorg'] . "' or pemilik = 'PABRIK' or pemilik = 'GLOBAL')";
		// }

		$optnoakun .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		if ($param['notransaksi'] != '') {
			$str = "select * from " . $dbname . "." . $table . " where notransaksi='" . $param['notransaksi'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$norek = $bar['noakun'];
		}

		#= Cek jika ada noakun penerima
		#= Maka not in noakun pengirim ke noakun penerima
		if ($param['noakun2'] != '') {
			$whnoakun = '';
			if ($param['noakun2'] != '') {
				$whnoakun = " AND a.noakun not in ('" . $param['noakun2'] . "', '" . $noakunayatsilang . "')";
			}

			// $str = "SELECT * FROM " . $dbname . ".keu_5akun where noakun like '111%' and noakun < '11103' " . $whr . " and " . $whnoakun . " detail=1 and aktif=1";
			$str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun WHERE a.kasbank = 1 AND a.detail = 1 AND a.aktif = 1 AND a.level = '5' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('" . $param['kodeorg'] . "')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik = '{$tipeorganisasi[$param['kodeorg']]}' OR a.pemilik IN ('" . $param['kodeorg'] . "')))) {$whnoakun} GROUP BY a.noakun";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				// $optnoakun[$bar['noakun']]=$bar['noakun'];
				if ($bar['noakun'] == $norek) {
					$optnoakun .= "<option selected value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
				} else {
					$optnoakun .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
				}
			}
		} else { #= Cek jika, noakun penerima kosong, maka normal
			// $str = "SELECT * FROM " . $dbname . ".keu_5akun where noakun like '111%' and noakun < '11103' " . $whr . " and detail=1 and aktif=1";

			$str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun WHERE a.kasbank = 1 AND a.detail = 1 AND a.aktif = 1 AND a.level = '5' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('" . $param['kodeorg'] . "')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik = '{$tipeorganisasi[$param['kodeorg']]}' OR a.pemilik IN ('" . $param['kodeorg'] . "')))) GROUP BY a.noakun";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				// $optnoakun[$bar['noakun']]=$bar['noakun'];
				if ($bar['noakun'] == $norek) {
					$optnoakun .= "<option selected value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
				} else {
					$optnoakun .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
				}
			}
		}

		echo $optnoakun;

		break;

	case 'viewfilekasbank':
		$tab = "";
		// $str= "select * from ".$dbname.".listfileupload where id = '".$param['idfile']."'";
		$str = "select * from " . $dbname . ".listfileupload where namafile = '" . $param['namafile'] . "'";
		$res = fetchData($str);
		if ($res[0]['formaticon'] == '.xls' or $res[0]['formaticon'] == '.xlsx' or $res[0]['formaticon'] == '.doc' or $res[0]['formaticon'] == '.docx') {
			exit("Warning: Tidak bisa ditampilkan, silahkan download.");
		}

		if ($res[0]['formaticon'] == '.pdf') {
			$tab .= "<embed src='" . $path . $res[0]['namafile'] . "' style='width:100%;height:97%;' type='application/pdf'>";
		} else {
			$tab .= "<img src='" . $path . $res[0]['namafile'] . "'>";
		}

		echo $tab;
		break;
	case 'getdataautokb':
		$tab = "";

		# Get Data 
		$sql = "SELECT * FROM {$dbname}.{$table} WHERE notransaksi='" . $param['notransaksi'] . "' AND novoucher='" . $param['novoucher'] . "'";
		$res = fetchData($sql, "OBJECT")[0];

		$notransaksikk = $res->notransaksi;
		$novoucherkk = $res->novoucher;
		$noreferensikk = $res->noreferensi;
		$waktukk = $res->createtime;

		# Get Data Referensi KM
		$sql = "SELECT * FROM {$dbname}.{$table} WHERE notransaksi='" . $noreferensikk . "'";
		$res = fetchData($sql, "OBJECT")[0];

		$notransaksikm = $res->notransaksi;
		$novoucherkm = $res->novoucher;
		$noreferensikm = $res->noreferensi;
		$waktukm = $res->createtime;


		$tab .= "<table class=sortable border=0 cellpadding=5 cellspacing=1>";
		$tab .= "<thead>";
		$tab .= "<tr>";
		$tab .= "<th colspan=4 align=center>Transaksi Auto Kas Bank</th>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		$tab .= "<th>Deskripsi</th>";
		$tab .= "<th>Tipe</th>";
		$tab .= "<th>Isi</th>";
		$tab .= "<th>Waktu</th>";
		$tab .= "</tr>";
		$tab .= "</thead>";

		$tab .= "<tbody>";
		#===================================#
		# DATA HEADER (KAS KELUAR)
		#===================================#
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td rowspan=3>Kas / Bank Keluar</td>";
		$tab .= "<td>Notransaksi</td>";
		$tab .= "<td align=center>" . $notransaksikk . "</td>";
		$tab .= "<td align=center rowspan=3>" . $waktukk . "</td>";
		$tab .= "</tr>";

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>Noreferensi</td>";
		$tab .= "<td align=center>" . $noreferensikk . "</td>";
		$tab .= "</tr>";

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>Novoucher</td>";
		$tab .= "<td align=center>" . $novoucherkk . "</td>";
		$tab .= "</tr>";


		#===================================#
		# DATA NOREFERENSI
		#===================================#

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td rowspan=3>Kas / Bank Masuk</td>";
		$tab .= "<td>Notransaksi</td>";
		$tab .= "<td align=center>" . $notransaksikm . "</td>";
		$tab .= "<td align=center rowspan=3>" . $waktukm . "</td>";
		$tab .= "</tr>";

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>Noreferensi</td>";
		$tab .= "<td align=center>" . $noreferensikm . "</td>";
		$tab .= "</tr>";

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>Novoucher</td>";
		$tab .= "<td align=center>" . $novoucherkm . "</td>";
		$tab .= "</tr>";

		$tab .= "</tbody>";

		$tab .= "</table>";

		echo $tab;
		break;
	case 'viewfile':
		$tab = "";
		$res[0]['formaticon'] = strtolower('.' . substr($param['idfile'], strripos($param['idfile'], '.') + 1));

		if ($res[0]['formaticon'] == '.xls' or $res[0]['formaticon'] == '.xlsx' or $res[0]['formaticon'] == '.doc' or $res[0]['formaticon'] == '.docx') {
			exit("Warning: Tidak bisa ditampilkan, silahkan download.");
		}

		if ($res[0]['formaticon'] == '.pdf') {
			$tab .= "<embed src='" . $param['idfile'] . "' style='width:100%;height:97%;' type='application/pdf'>";
		} else {
			$tab .= "<img src='" . $param['idfile'] . "'>";
		}

		echo $tab;
		break;
	case 'proseskk':
		$jumlahkas = 0;
		try {

			$owlPDO->beginTransaction();

			for ($i = 1; $i <= $param['maxrow']; $i++) {
				if (@$param['notransaksikk'][$i] != '') {
					$param['jumlahkk'][$i] = str_replace(',', '', $param['jumlahkk'][$i]);
					$str = " INSERT INTO " . $dbname . ".`keu_kasbankdt_kk` (`notransaksi`, `kodeorg`, `noakun`, `notransaksikk`, `novoucherkk`, `kodeorgkk`, `noakun2`, `jumlahkk`, `createby`, `createtime`) VALUES ('','" . $param['kodeorg'] . "','" . $param['noakun'] . "','" . $param['notransaksikk'][$i] . "','" . $param['novoucherkk'][$i] . "','" . $param['kodeorgkk'][$i] . "','" . $param['noakunkk'][$i] . "','" . $param['jumlahkk'][$i] . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "')";
					$owlPDO->exec($str);

					$jumlahkas += $param['jumlahkk'][$i];
				}
			}

			#= insert data ke detail
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning: Gagal melakukan simpan data \n" . addslashes($e->getMessage());
		}
		echo $jumlahkas;

		break;


	case 'findkk':

		#= delete 1st
		#= karna saat tombol proses, insert table ini dahulu, baru ke keu_kasbankdt_kk, 
		#= jika sudah proses ini, tapi transaksi kasbank batal dibuat, maka jika tarik nomor kk lagi, delete dlu data yang tidak jadi dibuat (notransaksi kosong, unit param kirim)
		$str = "delete from " . $dbname . ".keu_kasbankdt_kk where kodeorgkk='" . $param['namapenerima'] . "' and noakun2='" . $param['noakun2'] . "' and notransaksi='' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}

		$where = '';

		#= data buat tagihan
		$str = "select * from " . $dbname . ".keu_kasbankht where kodeorg='" . $param['namapenerima'] . "' and noakun='" . $param['noakun2'] . "' and tanggal between '" . tanggalsystemn($param['tanggalkk1']) . "' and '" . tanggalsystemn($param['tanggalkk2']) . "' order by tanggal asc,novoucher asc,notransaksi asc";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$notransaksidata = '';
			#= cek apakah sudah pernah ditarik atau belum
			$strdt = "select notransaksi from " . $dbname . ".keu_kasbankdt_kk where notransaksikk='" . $bar['notransaksi'] . "' ";
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				@$notransaksidata = $bardt['notransaksi'];
			}

			$no++;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td id=notransaksikk" . $no . ">" . $bar['notransaksi'] . "</td>";
			$tab .= "<td id=novoucherkk" . $no . ">" . $bar['novoucher'] . "</td>";
			$tab .= "<td id=jumlahkk" . $no . " align=right>" . hidezerodecimal($bar['jumlah'], 2) . "</td>";
			$tab .= "<td id=kodeorgkk" . $no . ">" . $bar['kodeorg'] . "</td>";
			$tab .= "<td id=noakunkk" . $no . ">" . $bar['noakun'] . "</td>";
			if ($notransaksidata != '') {
				$tab .= "<td><input hidden title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . ">" . $notransaksidata . "</td>";
			} else if ($bar['novoucher'] == '') {
				$tab .= "<td><input hidden title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . ">Belum Ada Nomor Voucher / Belum Transaksi Kasir</td>";
			} else {
				$tab .= "<td><input title='check => Ya, uncheck => Tidak' type=checkbox id=checkboxdt" . $no . "></td>";
			}
			$tab .= "</tr>";
		}
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td style=cursor:pointer align=right colspan=8><button class=mybutton onclick=proseskk('" . $no . "')>" . $_SESSION['lang']['proses'] . "</button></td>";
		$tab .= "</tr>";
		echo $tab;
		break;

	case 'getkk':
		$tab .= "<fieldset><legend>" . $_SESSION['lang']['form'] . "</legend>";
		$tab .= "<table>";
		$tab .= "<tr>
					<td>" . $_SESSION['lang']['tanggal'] . "</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext id=tanggalkk1 name=tanggal  style=\"width:150px;\" value=" . date('d-m-Y') . " readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>	s/d
						<input type=text class=myinputtext id=tanggalkk2 name=tanggal  style=\"width:150px;\" value=" . date('d-m-Y') . " readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:61px;/>	
					</td>
			</tr>";
		$tab .= "<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=findkk()>" . $_SESSION['lang']['find'] . "</button></td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</fieldset>";
		$tab .= "<br>";

		// $tab.="<button class=mybutton onclick=saveap()>AP</button>&nbsp;";
		$tab .= " <div class=table-scroll style='height:400px'>";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		$tab .= "<thead><tr class=rowheader>
					<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align=center>" . $_SESSION['lang']['novoucher'] . "</th>
					<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
					<th align=center>" . $_SESSION['lang']['unit'] . "</th>
					<th align=center>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['action'] . "</th>";
		$tab .= "</tr>";
		$tab .= "</thead>";
		$tab .= "<tbody id=formpencariantampil></tbody>";
		$tab .= "</table>";
		$tab .= "</div>";


		echo $tab;

		break;


	case 'saveajukan':
		try {
			$owlPDO->beginTransaction();

			$adafile = 0;

			$str = "select keterangan1,tipetransaksi from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$tipetransaksi = $bar['tipetransaksi'];
				$arrnoinvoice[$bar['keterangan1']] = $bar['keterangan1'];
			}


			@$carrnoinvoice = count($arrnoinvoice);

			if ($carrnoinvoice > 0 and $tipetransaksi == 'K') {
				$str = "select * from " . $dbname . ".listfileupload where notransaksi in ('" . implode("','", $arrnoinvoice) . "')";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$adafile++;
				}
			}

			// $str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "'";
			// $res = fetchdata($str);
			// foreach ($res as $bar) {
			// 	$adafile++;
			// }


			// if ($adafile == 0) {
			// 	throw new PDOException("Gagal untuk mengajukan, belum ada upload file !!");
			// }

			// echo"<pre>";
			// print_r($param);
			// echo"</pre>";exit("Error:A");

			if ($param['tanggalpengajuan'] == '') {
				throw new PDOException("Tanggal pengajuan masih kosong");
			}

			for ($i = 1; $i <= $param['maxaproval']; $i++) {
				if ($param['persetujuan'][$i] == '') {
					throw new PDOException("Persetujuan " . $i . " belum dipilih.");
				}
			}
			// echo"<pre>";
			// print_r($param['persetujuan']);
			// echo"</pre>";
			// exit("error");
			#= delete 1st untuk aprovalnya
			// $str = "delete from " . $dbname . ".approval where notransaksi='".$param['notransaksi']."' and jenispersetujuan = '".$param['kasbank']."'";
			// $owlPDO->exec($str);
			date_default_timezone_set('Asia/Jakarta');

			movetoappreturn($param['notransaksi']);

			$str = "update " . $dbname . ".keu_kasbankht set posting=9,tanggalpengajuan='" . tanggalsystemn($param['tanggalpengajuan']) . " " . date('H:i:s') . "',submitby='" . $_SESSION['standard']['userid'] . "' where notransaksi='" . $param['notransaksi'] . "'";
			$owlPDO->exec($str);
			// exit("Error:MASUKKK");
			// echo $param['maxaproval'];exit("error");
			for ($i = 1; $i <= $param['maxaproval']; $i++) {
				$str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
					   values('" . $param['notransaksi'] . "','" . $param['kasbank'] . "','" . $i . "','" . $param['persetujuan'][$i] . "','0','','','0000-00-00 00:00:00')";
				$owlPDO->exec($str);
			}

			$str = "select * from " . $dbname . ".approval where notransaksi='" . $param['notransaksi'] . "' and jenispersetujuan = '" . $param['kasbank'] . "'";
			$res = fetchdata($str);
			if (count($res) != $param['maxaproval']) {
				throw new PDOException("Proses tidak berhasil, silahkan ajukan ulang.");
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning:\n" . addslashes($e->getMessage());
		}


		#= cek apakah approval sudah semua?

		#= ambil kodeorg transaksi
		$str = "select * from " . $dbname . ".keu_kasbankht where  notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$param['kodeorg'] = $bar['kodeorg'];
		}

		$str = "select * from " . $dbname . ".approval where notransaksi='" . $param['notransaksi'] . "' ";
		$res = fetchdata($str);
		$jumlahtableapproval = count($res);

		$countApp = getCountApprovalkasbank($param['kasbank'], $param['kodeorg']);

		if ($countApp != $jumlahtableapproval) {

			#= delete approval yang terbentuk
			$str = "update " . $dbname . ".keu_kasbankht set posting=0,tanggalpengajuan='' where notransaksi='" . $param['notransaksi'] . "'";
			$owlPDO->exec($str);

			#= update status persetujuan
			$str = "delete from " . $dbname . ".approval where notransaksi='" . $param['notransaksi'] . "'";
			$owlPDO->exec($str);

			exit("<br>Warning: Pengajuan gagal, silahkan ajukan kembali");
		}


		break;

	### POP UP AJUKAN
	case 'formajukan':


		/** Report Prep **/
		$cols = array();

		#=============================== Header ======================================= keu_kasbankht
		$whereH = "notransaksi='" . $param['notransaksi'] . "'";
		$queryH = selectQuery($dbname, 'keu_kasbankht', '*', $whereH);
		$resH = fetchData($queryH);


		# Get Nama Pembuat
		$userId = makeOption(
			$dbname,
			'datakaryawan',
			'karyawanid,namakaryawan',
			"karyawanid='" . $resH[0]['userid'] . "'"
		);
		# Get Nama Akun Hutang
		$namaakunhutang = makeOption(
			$dbname,
			'keu_5akun',
			'noakun,namaakun',
			"noakun='" . $resH[0]['noakunhutang'] . "'"
		);
		#Get tipe Lokasi Tugas
		$tipeLokasiTugas = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		$nmkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$nxkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');

		#=============================== Detail =======================================
		# Data
		$col1 = 'noakun,jumlah,noaruskas,matauang,kode,keterangan2,hutangunit1,pemilikhutang1,keterangan1,kodekegiatan,kodeasset,nik,kodecustomer,kodesupplier,kodevhc,	orgalokasi,nodok,departemen';
		$cols = array('nourut', 'noakun', 'namaakun', 'noaruskas', 'debet', 'kredit');
		$colshtml = array('nourut', 'noakun', 'namaakun', 'noaruskas', 'debet', 'kredit', 'keterangan', 'hutangunit1', 'pemilikhutang1', 'invoice', 'kodekegiatan', 'kodeasset', 'nik', 'kodecustomer', 'kodesupplier', 'kodevhc', 'alokasi', 'nodok', 'departemen');
		//$col1 = 'noakun,jumlah,noaruskas,matauang,kode,hutangunit1';
		//$cols = array('nomor','noakun','namaakun','matauang','debet','kredit','hutangunit');
		$where = "notransaksi='" . $param['notransaksi'] . "'";
		$query = selectQuery($dbname, 'keu_kasbankdt', $col1, $where);

		$res = fetchData($query);

		# Data Empty
		if (empty($res)) {
			echo 'Data Empty';
			exit;
		}

		# Options
		$whereAkun = "noakun in (";
		$whereAkun .= "'" . $resH[0]['noakun'] . "'";
		$whereAkun .= ",'" . $resH[0]['noakunhutang'] . "'"; // tambahin kamus nama akun hutangunit 
		foreach ($res as $key => $row) {
			$whereAkun .= ",'" . $row['noakun'] . "'";
		}
		$whereAkun .= ")";
		$optAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', $whereAkun);
		$optHutangUnit = array('0' => 'Tidak', '1' => 'Ya');

		# Data Show
		$data = array();

		#================================ Prep Data ===================================
		# Total
		$totalDebet = 0;
		$totalKredit = 0;

		# Dari Header
		$i = 1;
		$data[$i] = array(
			'nomor' => @$i,
			'noakun' => @$resH[0]['noakun'],
			'namaakun' => @$optAkun[$resH[0]['noakun']],
			'noaruskas' => @$resH[0]['noaruskas'],
			'debet' => 0,
			'kredit' => 0,
			'keterangan2' => $resH[0]['keterangan'],
			'hutangunit1' => '99',
			'pemilikhutang1' => '',
			'keterangan1' => '',
			'kodekegiatan' => '',
			'kodeasset' => '',
			'nik' => '',
			'kodecustomer' => '',
			'kodesupplier' => '',
			'kodevhc' => '',
			'orgalokasi' => '',
			'nodok' => '',
			'departemen' => ''
		);

		if (@$resH[0]['tipetransaksi'] == 'M') {
			$data[$i]['debet'] = $resH[0]['jumlah'];
			$totalDebet += $resH[0]['jumlah'];
		} else {
			$data[$i]['kredit'] = $resH[0]['jumlah'];
			$totalKredit += $resH[0]['jumlah'];
		}

		// if(substr($resH[0]['noakun'],0,5)<='1111101')
		if ($resH[0]['noakun'] <= '1111101') {
			if ($resH[0]['tipetransaksi'] == 'K') {
				$title = strtoupper($_SESSION['lang']['bank'] . " (" . $_SESSION['lang']['keluar'] . ")");
			} else {
				$title = strtoupper($_SESSION['lang']['bank'] . " (" . $_SESSION['lang']['masuk'] . ")");
			}
		} else {
			if ($resH[0]['tipetransaksi'] == 'K') {
				$title = strtoupper($_SESSION['lang']['kas'] . " (" . $_SESSION['lang']['keluar'] . ")");
			} else {
				$title = strtoupper($_SESSION['lang']['kas'] . " (" . $_SESSION['lang']['masuk'] . ")");
			}
		}


		$strdt = "select * from " . $dbname . ".project where kode='" . $row['kodeasset'] . "' ";
		$resdt = fetchdata($strdt);
		@$namaproject = $resdt[0]['nama'];

		$strdt = "select * from " . $dbname . ".setup_kegiatan where kodekegiatan='" . $row['kodekegiatan'] . "' ";
		$resdt = fetchdata($strdt);
		@$namakegiatan = $resdt[0]['namakegiatan'];

		$strdt = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $row['nik'] . "' ";
		$resdt = fetchdata($strdt);
		@$nikkaryawan = $resdt[0]['nik'];
		@$namakaryawan = $resdt[0]['namakaryawan'];



		$i++;

		# Dari Detail
		foreach ($res as $row) {
			$strdt = "select * from " . $dbname . ".keu_tagihanht where noinvoice='" . $row['keterangan1'] . "' ";
			$resinv = fetchdata($strdt);

			$strdt = "select * from " . $dbname . ".vhc_5master_hist where kodevhc='" . $row['kodevhc'] . "' ";
			$resdt = fetchdata($strdt);
			@$nopol = $resdt[0]['nopol'];
			@$namavhc = $resdt[0]['detailvhc'];
			@$nmdepart = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $row['departemen'] . "'");
			$data[$i] = array(
				'nomor' => $i,
				'noakun' => $row['noakun'],
				'namaakun' => $optAkun[$row['noakun']],
				'noaruskas' => $row['noaruskas'],
				'debet' => 0,
				'kredit' => 0,
				'keterangan2' => $row['keterangan2'],
				'hutangunit1' => $row['hutangunit1'],
				'pemilikhutang1' => $row['pemilikhutang1'],
				'keterangan1' => $row['keterangan1'] . " - " . $resinv[0]['noinvoicesupplier'],
				'kodekegiatan' => $row['kodekegiatan'] . ' - ' . $namakegiatan,
				'kodeasset' => $row['kodeasset'] . ' - ' . $namaproject,
				'nik' => $nxkaryawan[$row['nik']] . ' - ' . $nmkaryawan[$row['nik']],
				'kodecustomer' => $row['kodecustomer'] . ' - ' . $nmcustomer[$row['kodecustomer']],
				'kodesupplier' => $row['kodesupplier'] . ' - ' . $nmsupplier[$row['kodesupplier']],
				'kodevhc' => $row['kodevhc'] . ' - ' . $namavhc . ' - ' . $nopol,
				'orgalokasi' => $row['orgalokasi'],
				'nodok' => $row['nodok'],
				'departemen' => $row['departemen'] . ' - ' . $nmdepart[$row['departemen']]
			);
			//	'hutangunit1'=>$optHutangUnit[$row['hutangunit1']]
			if (@$resH[0]['tipetransaksi'] == 'M' and $row['jumlah'] > 0) {
				$data[$i]['kredit'] = $row['jumlah'];
				$totalKredit += $row['jumlah'];
			} else if (@$resH[0]['tipetransaksi'] == 'K' and $row['jumlah'] < 0) {
				$data[$i]['kredit'] = $row['jumlah'] * -1;
				$totalKredit += $row['jumlah'] * -1;
			} else if (@$resH[0]['tipetransaksi'] == 'M' and $row['jumlah'] < 0) {
				$data[$i]['debet'] = $row['jumlah'] * -1;
				$totalDebet += $row['jumlah'] * -1;
			} else {
				$data[$i]['debet'] = $row['jumlah'];
				$totalDebet += $row['jumlah'];
			}
			$i++;
		}

		// nyusun berdasarkan debet dulu, abis itu baru kredit. by dz
		if (!empty($data)) foreach ($data as $c => $key) {
			$sort_debet[] = $key['debet'];
			$sort_kredit[] = $key['kredit'];
		}

		// sort
		if (!empty($data)) array_multisort($sort_debet, SORT_DESC, $sort_kredit, SORT_ASC, $data);

		$align = explode(",", "R,R,L,L,R,R,L,L");
		$length = explode(",", "7,12,35,10,18,18,10");
		$titleDetail = 'Detail';


		$theme = $_SESSION['theme'];
		if ($theme == 'skyblue' || $theme == '') {
			$men = 'menu.css';
			$gen = 'generic.css';
		} else if ($theme == 'red') {
			$men = 'menuRed.css';
			$gen = 'genericRed.css';
		} else {
			$men = 'menuGray.css';
			$gen = 'genericGray.css';
		}

		$tab = "<link rel=stylesheet type=text/css href=style/" . $gen . ">";
		//$tab.="<fieldset><legend>".$title."</legend>";
		// exit("Error:A");
		$tab .= "<label>" . $title . "</label>";
		$tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable><tbody class=rowcontent>";
		$tab .= "<thead><tr  style='font-weight:bold'><td colspan=3>" . $_SESSION['lang']['header'] . "</td></tr></thead>";
		// $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
		$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['kodeorganisasi'] . "</td><td> :</td><td> " . $resH[0]['kodeorg'] . "</td></tr>";
		$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['notransaksi'] . "</td><td> :</td><td>" . $param['notransaksi'] . "</td></tr>";
		$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['novoucher'] . "</td><td> :</td><td>" . $resH[0]['novoucher'] . "</td></tr>";
		$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['cgttu'] . "</td><td> :</td><td> " . $resH[0]['cgttu'] . "</td></tr>";
		$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['bayarke'] . "</td><td> :</td><td> " . $resH[0]['bayarkepada'] . "</td></tr>";
		## GW GANTI DISINI
		// $tab.="<tr><td>".$_SESSION['lang']['cgttu']."</td><td> :</td><td> ".$resH[0]['tipetransaksi']."</td></tr>";
		$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['jumlah'] . "</td><td> :</td><td> " . number_format($resH[0]['jumlah'], 2) . "</td></tr>";
		$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['terbilang'] . "</td><td> :</td><td> " . strtolower(terbilang($resH[0]['jumlah'], 2) . " " . $namamatauang[$resH[0]['matauang']]) . "</td></tr>"; //indra

		if ($resH[0]['hutangunit'] == 1) {
			$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['hutangunit'] . "</td><td> :</td><td> " . 'Unit payable Account ' . $resH[0]['pemilikhutang'] . ' : ' . $namaakunhutang[$resH[0]['noakunhutang']] . "</td></tr>";
		}
		$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['autokb'] . "</td><td> :</td><td>" . $arrhutangunit[$resH[0]['autokb']] . "</td></tr>";
		$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['noreferensi'] . "</td><td> :</td><td>" . $resH[0]['noreferensi'] . "</td></tr>";
		$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['keterangan'] . "</td><td> :</td><td>" . $resH[0]['keterangan'] . "</td></tr>";
		$tab .= "</tbody></table><br />";

		$tab .= "<table cellpadding=3 cellspacing=1 border=0 class=sortable><thead><tr class=rowheader  style='font-weight:bold'>";

		$tab .= "<button onclick=\"showandhide(0)\" id=tombolshow class=mybutton style=display:none;>Show Column</button>";


		foreach ($colshtml as $column) {
			if ($column == 'hutangunit1') {
				$tab .= "<td align=center name=col0[]>" . $_SESSION['lang']['hutangunit'] . "</td>";
			} else if ($column == 'pemilikhutang1') {
				$tab .= "<td align=center name=col0[]>" . $_SESSION['lang']['pemilikhutang'] . "</td>";
			} else {
				$tab .= "<td align=center name=col0[]>" . $_SESSION['lang'][$column] . "</td>";
			}
		}
		$tab .= "</tr></thead><tbody class=rowcontent>";



		// nyusun ulang nomor setelah disort by debet. dz
		$nyomor = 0;
		foreach ($data as $key => $row) {
			$nyomor += 1;
			$tab .= "<tr>";
			foreach ($row as $key => $cont) {
				if ($key == 'nomor') {
					$tab .= "<td align=center name=col" . $nyomor . "[]>" . $nyomor . "</td>";
				} else {
					if ($key == 'debet' or $key == 'kredit') {
						$tab .= "<td align=right name=col" . $nyomor . "[]>" . number_format($cont, 0) . "</td>";
					} else  if ($key == 'noaruskas') {
						$tab .= "<td name=col" . $nyomor . "[]>" . $cont . "<br>" . @$nmaruskas[$cont] . "</td>";
					} else  if ($key == 'hutangunit1') {
						if ($cont == 0) {
							$tab .= "<td align=center name=col" . $nyomor . "[]>Tidak</td>";
						} else if ($cont == 1) {
							$tab .= "<td align=center name=col" . $nyomor . "[]>Ya</td>";
						} else {
							$tab .= "<td name=col" . $nyomor . "[]></td>";
						}
					} else  if ($key == 'pemilikhutang1') {
						$tab .= "<td name=col" . $nyomor . "[]>" . $cont . "</td>";
					} else {
						$tab .= "<td name=col" . $nyomor . "[]>" . $cont . "</td>";
					}
				}
			}
			$tab .= "</tr>";
		}
		$tab .= "<tr><td colspan=4 align=center>Total</td><td align=right>" . number_format($totalDebet, 2) . "</td>
								<td align=right>" . number_format($totalKredit, 2) . "</td>
								<td colspan=13 ><input style=display:none id=tempjumlahrow value=" . $nyomor . "></td></tr>";
		$tab .= "</tbody></table> <br />";


		//$tab.="<table><tr><td style=vertical-align:top;>";
		$tab .= "<b>Daftar Persetujuan</b>";
		$tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:400px>";
		$tab .= "<thead>
							<tr style=font-weight:bold;font-align:center; class=rowheader>
							<td align=center>" . $_SESSION['lang']['level'] . "</td>
							<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
							<td align=center>" . $_SESSION['lang']['status'] . "</td>
							<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
						</tr></thead>";
		$nmkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$optposting = array('' => $_SESSION['lang']['pilihdata'], '0' => 'Belum Diajukan/Proses Persetujuan', '1' => 'Disetujui', '2' => 'Ditolak', '3' => 'Dikoreksi', '9' => 'Proses Persetujuan');
		//0; belum proses; 1:disetujui;3:dikoreksi;2:ditolak;9:proses pengajuan
		$str = "select * from " . $dbname . ".approval where notransaksi='" . $param['notransaksi'] . "' order by level";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$tab .= "<tr class=rowcontent>";

			$tab .= "<td align=center>" . $bar['level'] . "</td>";
			$tab .= "<td>" . @$nmkaryawan[$bar['karyawanid']] . "</td>";
			$tab .= "<td>" . $optposting[$bar['status']] . "</td>";
			$tab .= "<td>" . $bar['komentar'] . "</td>";
			$tab .= "<td>" . tanggalnormal(substr($bar['tanggal'], 0, 10)) . " " . substr($bar['tanggal'], 11, 8) . "</td>";
			$tab .= "</tr>";
		}
		$tab .= "</tbody></table><br>";
		// $tab.="</td><td style=vertical-align:top;>";	 


		$tab .= "<b>File Upload Kas/Bank</b>";
		$tab .= "<table border=0 cellspacing=1 class=sortable cellpadding=5 style=min-width:400px>
						<thead>
						<tr style='font-weight:bold'>
							<td align='center'>No.</td>
							<td align='center'>File Type</td>
							<td align='center'>Kriteria</td>
							<td align='center'>Filename</td>
							<td align='center'>Action</td>
						</tr>
						</thead>
						<tbody id='listfile'>";



		// @$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download>".$bar['namafile']."</td>";
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "' and kriteriaefil != 'KSR'";
		$res = fetchdata($str);
		foreach ($res as $key => $val) {
			@$icon = seticonfile($val['formaticon']);
			$no++;
			$tab .= "<tr id='ppDetailTable' class=rowcontent>
									<td style='text-align:center'>" . $no . "</td>";
			$tab .= "<td align='center'><img src=" . $icon . " class=resicon></a></td>";
			$tab .= "<td style='text-align:left'>" . getcriterianame($val['kriteriaefil']) . "</td>
									<td style='text-align:left;cursor:pointer;' onclick=\"viewfile('" . $path . str_replace('/', '', $val['namafile']) . "','KASBANK')\">" . $val['namafile'] . "</td>
									<td align=center>
										<a href='" . $path . str_replace('/', '', $val['namafile']) . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
			$tab . "	</td>
								</tr>";
		}
		$tab .= "</tbody>
					</table>";


		// $tab.="</td></tr>
		// <tr><td style=vertical-align:top;>";	 

		#= ambil data noinvoice	
		$tab .= "<b><br>File Upload Invoice AP</b>";
		$tab .= "<table border=0 cellspacing=1 class=sortable cellpadding=5 style=min-width:400px>
					<thead>
					<tr style='font-weight:bold'>
						<td align='center'>No.</td>
						<td align='center'>Invoice</td>
						<td align='center'>File Type</td>
						<td align='center'>Kriteria</td>
						<td align='center'>Filename</td>
						<td align='center'>Action</td>
					</tr>
					</thead>
					<tbody id='listfilesview'>";


		#= ambil data noinvoice	
		$path   = "fileupload/keu_tagihan/";
		$pathlama   = "filegis/";
		$tempnamafile = '';
		$strinv = "select keterangan1,tipetransaksi from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "'";
		$resinv = $owlPDO->query($strinv) or die(print " Gagal: " . PDOException::getMessage());
		$resinv->setFetchMode(PDO::FETCH_ASSOC);
		while ($barinv = $resinv->fetch()) {
			$tipetransaksi = $barinv['tipetransaksi'];
			$arrnoinvoice[$barinv['keterangan1']] = $barinv['keterangan1'];
		}


		@$carrnoinvoice = count($arrnoinvoice);

		if ($carrnoinvoice > 0 and $tipetransaksi == 'K') {
			$str = "select * from " . $dbname . ".listfileupload where notransaksi in ('" . implode("','", $arrnoinvoice) . "')";
			// echo $str;
			$arrnamafile = array();
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$lsformaticon[$bar['namafile']] = $bar['formaticon'];
				$lskriteriaefil[$bar['namafile']] = $bar['kriteriaefil'];
				// $lsnamafile[$bar['namafile']]=$bar['namafile'];
				$arrnamafile[$bar['namafile']] = $bar['namafile'];
				$lsnoinvoice[$bar['namafile']] = $bar['notransaksi'];
			}

			// if(){

			foreach ($arrnamafile as $lsnamafile) {
				@$icon = seticonfile($lsformaticon[$lsnamafile]);
				$no++;
				$tab .= "<tr id='ppDetailTable' class=rowcontent>
							<td style='text-align:center'>" . $no . "</td>";
				$tab .= "<td style='text-align:center'>" . $lsnoinvoice[$lsnamafile] . "</td>";
				$tab .= "<td align='center'><img src=" . $icon . " class=resicon></a></td>";
				$tab .= "<td style='text-align:left'>" . getcriterianame($lskriteriaefil[$lsnamafile]) . "</td>
							<td style='text-align:left;' onclick=\"viewfile('" . $path . str_replace('/', '', $lsnamafile) . "','KASBANK')\">" . $lsnamafile . "</td>
							<td nowrap align=center>
								<a href='" . $path . str_replace('/', '', $lsnamafile) . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>
								&nbsp
								<a href='" . $pathlama . str_replace('/', '', $lsnamafile) . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>
								";
				$tab . "	</td>
						</tr>";
			}
		}

		$tab .= "</tbody>
			</table><br>";

		// $tab.="</td><td style=vertical-align:top;>";

		//permintaan bu vienny transaksi sudah dibayar masih bisa upload file
		if ($param['page'] == 'FROMKASIR') {
			$tampilkan = "style=float:left;";
			$kirim = true;
		} else {
			$tampilkan = "style=display:none;";
			$kirim = false;
		}
		// 1 = sudah bayar, 0 = belum bayar
		if ($resH[0]['pembayaran'] == '1') {
			$str = "select * from " . $dbname . ".setup_parameterappl where kodeparameter='UPLOADFILE'";
			$res = fetchdata($str);
			foreach ($res as $key => $val) {
				$tempuser = $val['nilai'];
			}
			$userupload = "";
			$tempuser = explode(",", $tempuser);
			foreach ($tempuser as $user) {
				$userupload .= ", " . $user;
			}

			$info = "'Yang bisa melakukan upload file tambahan pada transaksi ini adalah user <b>" . getKary($resH[0]['kasir']) . $userupload . "</b><br>silahkan login menggunakan user tersebut diatas.'";
			$info = "<img src=images/onebit_37.png class=zImgBtn onclick=\"getInfoKasir(" . $info . ")\";>";
			$tampilkan = "style=display:none;";
			$kirim = false;
			if ($_SESSION['standard']['userid'] == $resH[0]['kasir'] or in_array($_SESSION['standard']['username'], $tempuser)) {
				$tampilkan = "style=float:left;";
				$kirim = true;
			}
		}

		$optkriteria = "<option value='KSR'>KASIR</option>";

		$tab .= "<b>File Upload Kasir " . $info . "</b>";
		$tab .= "<div style=clear:both></div>";
		$tab .= "<fieldset $tampilkan><table cellspacing='1' border='0'>
				<tr style=display:none;>
					<td>" . $_SESSION['lang']['kriteria'] . "</td>
					<td>:</td>
					<td>
						<input id=notransaksi value=" . $param['notransaksi'] . ">
						<select id='kriteriaefil'>" . $optkriteria . "</select>
					</td>
				</tr>
				<tr>
					<td>Filename</td>
					<td>:</td>
					<td>
						<input type='file' name='upload' id='upload'>
					</td>
				</tr>
				<tr>
					<td style=vertical-align:top>Status</td>
					<td style=vertical-align:top>:</td>
					<td>
						<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
						<p id='statusbar'></p>
						<p id='loaded_n_total'></p>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick='submitfile()'>Submit</button>
						<button class=mybutton onclick='loadfiles()'>Selesai</button>
					</td>
					
				</tr>
			</table></fieldset>";
		$tab .= "<div style=clear:both></div>";
		$tab .= "<table border=0 cellspacing=1 class=sortable cellpadding=5 style=min-width:400px>
				<thead>
				<tr style='font-weight:bold'>
					<th align='center'>No.</th>
					<th align='center'>File Type</th>
					<th align='center'>Filename</th>
					<th align='center'>Upload By</th>
					<th align='center' colspan=2>Action</th>
				</tr>
				</thead>
				<tbody>";
		$path   = "fileupload/keu_kasbankx/";
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "'  and kriteriaefil = 'KSR'";
		$res = fetchdata($str);
		if (count($res) > 0) {
			$kirimfile = true;
		} else {
			$kirimfile = false;
		}
		foreach ($res as $key => $val) {
			@$icon = seticonfile($val['formaticon']);
			$no++;
			$tab .= "<tr id='ppDetailTable" . $no . "' class=rowcontent>
							<td style='text-align:center'>" . $no . "</td>";
			$tab .= "<td align='center'><img src=" . $icon . " class=resicon></a></td>";
			$tab .= "
							<td style='text-align:left;cursor:pointer;' onclick=\"viewfile('" . $path . str_replace('/', '', $val['namafile']) . "')\">" . $val['namafile'] . "</td>
							<td style='text-align:left'>" . getKary($val['createdby']) . "</td>";
			if ($resH[0]['kirimemail'] > 0) {
				$tab .= "<td align=center colspan=2><a href='" . $path . str_replace('/', '', $val['namafile']) . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a></td>";
			} else {
				$tab .= "<td align=center><a href='" . $path . str_replace('/', '', $val['namafile']) . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a></td>";
				$tab .= "<td style='text-align:center'><img src=images/application/application_delete.png class=resicon title=Delete onclick=deletefile('" . $param['notransaksi'] . "','" . str_replace('/', '', $val['namafile']) . "','" . $no . "');></td>";
			}
			$tab .= "</tr>";
		}
		$str = "select * from " . $dbname . ".setup_parameterappl where kodeparameter='KIRIMEMAIL'";
		$res = fetchdata($str);
		foreach ($res as $key => $val) {
			$tempuser = $val['nilai'];
		}

		$tempuser = explode(",", $tempuser);
		if (in_array($_SESSION['standard']['username'], $tempuser)) {
			$kirim = true;
		}

		$tab .= "</tbody>";
		if ($kirim == true and $kirimfile == true) {
			$tab .= "<tfoot>";
			$tab .= "<tr>";
			$tab .= "<td colspan=6 align=center><button class=mybutton onclick=\"kirimemail('" . $param['notransaksi'] . "')\">Send Email</button></td>";

			$tab .= "</tr>";
			$tab .= "</tfoot>";
		}
		$tab .= "</table><br>";

		// $tab.="</td></tr>";	 
		// $tab.="</table>";	 
		// echo $resH[0]['posting'];exit("Error");

		if ($resH[0]['posting'] == 0 || $resH[0]['posting'] == 3) {
			$tab .= "Persetujuan";
			$tab .= "<table cellpadding=2 cellspacing=1 border=0 class=sortable >
					<thead>
					<tr style='font-weight:bold'>
						<td align='center' colspan=2>" . $_SESSION['lang']['keterangan'] . "</td>
						<td align='center'>" . $_SESSION['lang']['action'] . "</td>
					</tr>
					</thead>
					<tbody id='listfile'>";

			#ambil dt
			// $str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "' and keterangan3 NOT LIKE '%KAS%'";
			$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "'";
			$res = fetchData($str);
			// $jlhdt = round($res[0]['jumlah'],2); # Di Round karena pembulatan Upload File Ongkos Angkut Per Blok
			$jlhdt = $res[0]['jumlah']; # Di Round karena pembulatan Upload File Ongkos Angkut Per Blok

			#ambil ht
			$str = "select sum(jumlah) as jumlah,kodeorg,tanggal from " . $dbname . ".keu_kasbankht where notransaksi='" . $param['notransaksi'] . "'";
			$res = fetchData($str);
			$jlhht = $res[0]['jumlah'];
			$param['kodeorg'] = $res[0]['kodeorg'];
			$param['tanggal'] = $res[0]['tanggal'];

			// echo $param['kodeorg']._.$param['page'];

			### DISINI YANG GW BAKAL DIUBAH

			if ($resH[0]['tipetransaksi'] == 'M') {
				$kasbank = 'KASBANK';
			} else {
				// $kasbank='KASBANKKELUAR';
				$kasbank = 'KASBANK';
			}
			$countApp = getCountApprovalkasbank($kasbank, $param['kodeorg']);
			// $countApp = getCountApprovalkasbank('KASBANKKELUAR',$param['kodeorg']);
			// echo $jlhdt;
			// exit('warning');
			$persanpersetujuan = '';
			$balance = $jlhdt - $jlhht;
			if ($balance != 0) {
				// echo"Transaksi belum balance";
				$tab .= "<tr  class=rowcontent>
					<td colspan=3>Transaksi belum balance <br/> Jumlah Header : " . hidezerodecimal($jlhht, 6) . " <br/> Jumlah Detail : " . $jlhdt . " <br/> Selisih : " . $balance . "</td> 
					</tr></table>";
			} else if ($countApp == 0) {
				// echo"Persetujuan untuk unit ".$param['kodeorg']." belum disetting ";
				// $persanpersetujuan.="Persetujuan untuk unit ".$param['kodeorg']." belum disetting ";
				$tab .= "<tr  class=rowcontent>
					<td colspan=3>Persetujuan untuk unit " . $param['kodeorg'] . " belum disetting</td> 
					</tr></table>";
			} else {
				$str = "select * from " . $dbname . ".filemanager where namafile='" . $param['notransaksi'] . "'";
				$res = fetchdata($str);
				if (count($res) > 0) {
					$showiconefil = "display:none";
				} else {
					$showiconefil = "display:none";
				}
				$countApp = getCountApprovalkasbank($kasbank, $param['kodeorg']);

				// $countApp = getHitungApproval($kasbank,$param['kodeorg'],'','','',$resH[0]['jumlah']);

				for ($i = 1; $i <= $countApp; $i++) {
					$arrList = listApprove($i, $kasbank, $param['kodeorg']);

					// echo"<pre>";
					// print_r($arrList);
					// $optpersetujuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";


					$arrDetail = detailApprove($i, $param['notransaksi'], $kasbank);
					$optpersetujuan = "";
					foreach ($arrList as $key => $val) {
						$optpersetujuan .= "<option value='" . $val['karyawanid'] . "'>" . $val['nama'] . "</option>";
					}
					$tab .= "<tr  class=rowcontent>
							<td>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</td> 
							<td>:</td>
							<td colspan=1><select style=\"width:154px;\" id=persetujuan" . $i . ">" . $optpersetujuan . "</select></td>
							</tr>";
				}
				$tab .= "
						<tr  class=rowcontent>
							<td>" . $_SESSION['lang']['tanggal'] . "</td> 
							<td>:</td>
							<td>
								<input type=text class=myinputtext disabled value='" . date('d-m-Y') . "'  id=tanggalpengajuan onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\">									
							</td>
						</tr>
						<tr class=rowcontent>
							<td colspan=2></td>
							<td style='text-align:left'>
								<button class=mybutton onclick=saveajukan('" . $param['notransaksi'] . "','" . $kasbank . "','" . $param['page'] . "','" . $countApp . "')>Simpan</button>
								<label style='color:blue;cursor:pointer;" . $showiconefil . "' onclick=viewefill('" . $param['notransaksi'] . "','',event) title='View E-Fill'>View E-Fill</label>
							</td>
						</tr>
					</table>
					";
			}
		}
		echo $tab;

		break;
	case 'kirimemail':

		$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodeorg = $bar['kodeorg'];
			$jumlah = $bar['jumlah'];
		}
		$str = "select * from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodesupplier = $bar['kodesupplier'];
		}

		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "'  and kriteriaefil = 'KSR'";
		$res = fetchdata($str);
		foreach ($res as $key => $val) {
			$file = $path . str_replace('/', '', $val['namafile']);
			$namafile = str_replace('/', '', $val['namafile']);
		}

		$str = "select * from " . $dbname . ".log_5supalamat where supplierid='" . $kodesupplier . "' and status='1'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$email[$bar['email_koresponden']] = $bar['email_koresponden'];
			$email[$bar['email_konfirmasi']] = $bar['email_konfirmasi'];
		}
		$alamatemail = "";
		foreach ($email as $mail) {
			$urut++;
			if ($urut > 1) {
				$alamatemail .= "," . $mail;
			} else {
				$alamatemail .= $mail;
			}
		}

		$body = "Dear " . getNamaSupplier($kodesupplier);
		$body .= "\n\nTerlampir bukti transfer atas pembayaran dari " . getNamaOrg(getNamaOrg($kodeorg, 'induk')) . " ";
		$body .= "sebesar Rp." . number_format($jumlah, 2) . "";
		$body .= "\nTerbilang #" . terbilang($jumlah, 2) . " rupiah.";
		$body .= "\n\nTerima kasih.";
		$body .= "\n\nBest Regards,";
		$body .= "\n" . getKary($_SESSION['standard']['userid']);

		$tab .= "<center><table cellspacing='1' cellpadding=3 border='0'>
				<tr><td>To :</td></tr>
				<tr><td><input class=myinputtext style=width:700px;height:30px;padding-left:15px name='email' id='email' value=" . $alamatemail . "></td></tr>
				<tr><td>Cc :</td></tr>
				<tr><td><input class=myinputtext style=width:700px;height:30px;padding-left:15px name='cc' id='cc' value=" . getKary($_SESSION['standard']['userid'], 'email') . "></td></tr>
				
				<tr><td>Subject :</td></tr>
				<tr><td><input class=myinputtext style=width:700px;height:30px;padding-left:15px name='subject' id='subject' value='Bukti Pembayaran dari " . getNamaOrg(getNamaOrg($kodeorg, 'induk')) . "'></td></tr>
				<tr><td>Attachment :</td></tr>
				<tr><td>
						<input class=myinputtext disabled style=width:700px;height:30px;padding-left:15px value=" . $namafile . ">
						</td></tr>
				<tr><td>Message :</td></tr>
				<tr>
					<td><textarea row=15 class=myinputtext style=width:700px;height:150px;padding-left:15px name='body' id='body'>" . $body . "</textarea></td>
				</tr>
					<input style=display:none; id=file value=" . $file . ">
					<input style=display:none; id=notransaksiemail value=" . $param['notransaksi'] . ">
				<tr>
					<td>";
		if ($file != '') {
			$tab .= "<button class=mybutton onclick='kirimkanemail()'>Submit</button>";
		} else {
			$tab .= "Silahkan upload terlebih dahulu.";
		}
		$tab .= "</td>
				</tr>
			</table></center>";
		echo $tab;
		break;
	case 'kirimkanemail':
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "'  and kriteriaefil = 'KSR'";
		$res = fetchdata($str);
		if (count($res) > 0) {
			kirimEmailatt($param['email'], $param['cc'], $param['subject'], str_replace("\n", "<br>", $param['body']), $mailType = 'text/html', $param['file']);

			$data = array(
				'kirimemail' => '1',
				'emailtime'  => date('Y-m-d H:i:s')
			);
			$where = "notransaksi='" . $param['notransaksi'] . "'";
			$str = updateQuery($dbname, 'keu_kasbankht', $data, $where);
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			exit("Warning: Silahkan upload file terlebih dahulu.");
		}

		break;

	case 'loaddatapdf':

		$tab = "<style>
			@page {
				margin-top: 10px;
				margin-left: 10px;
				margin-right: 10px;
				margin-bottom: 10px;
			}
			body {
				font-family: Serif, Times-Roman;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>";


		#= untuk unit ht


		$cellpadding = 1;
		$cellspacing = 0;
		$sizefont = '10';
		// style='width:".$arrkodept['logowidth'].";height:".$arrkodept['logoheight']."'
		$tab .= "<div style='page-break-after: always;'>";
		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>"; //logoheight logowidth
		$tab .= "<tr>";
		$tab .= "<td align=left style='width:50px;' align=center><img src=images/owl2.png style='width:200;height:25'></td>";
		$tab .= "<tr>";
		$tab .= "</table>";
		$tab .= "<br>";
		$tab .= "<br>";

		$tab .= "<table width=100% style='font-size:" . ($sizefont) . "px' cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=1>"; //logoheight logowidth
		$tab .= "<thead>
                <tr class=rowheader>
					<th  align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
					<th  align=center rowspan=2>" . $_SESSION['lang']['notransaksi'] . "</th>
                    <th  align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</th>
					<th  align=center rowspan=2>" . $_SESSION['lang']['tipe'] . "</th>
                    <th  align=center rowspan=2>" . $_SESSION['lang']['unit'] . "</th>
                    <th  align=center rowspan=2>" . $_SESSION['lang']['noakun'] . "</th>
                    <th  align=center rowspan=2>" . $_SESSION['lang']['rekening'] . "</th>
                    <th  align=center colspan=3>" . $_SESSION['lang']['jumlah'] . "</th> 
                    <th  align=center rowspan=2 style=width:250px>" . $_SESSION['lang']['keterangan'] . " </th> 
                    <th  align=center rowspan=2>" . $_SESSION['lang']['novoucher'] . " </th> 
                    <th  align=center rowspan=2>" . $_SESSION['lang']['bayarke'] . " </th> 
                    <th  align=center rowspan=2>" . $_SESSION['lang']['dibuatoleh'] . "</th> 
                    <th  align=center rowspan=2>" . $_SESSION['lang']['approval_status'] . "</th> 
                </tr>  
				<tr class=rowheader>
					<th  align=center>" . $_SESSION['lang']['header'] . "</th>
					<th  align=center>" . $_SESSION['lang']['detail'] . "</th>
                    <th  align=center>Balance</th>
				</tr>";

		#= untuk unit ht
		$arrunit = array();
		$arrunit = getOrgDetail(1);
		foreach ($arrunit as $val => $nama) {
			$dtunit[$val] = $val;
		}


		$where = "1=1 and  kodeorg in ('" . implode("','", $dtunit) . "') ";

		if ($param['tanggalmulai'] != '' and $param['tanggalselesai'] != '') {
			$where .= " and tanggal between '" . tanggalsystemn($param['tanggalmulai']) . "' and '" . tanggalsystemn($param['tanggalselesai']) . "'";
		}

		if ($param['notransaksi'] != '') {
			$where .= " and notransaksi like '%" . $param['notransaksi'] . "%'";
		}

		if ($param['kodeorg'] != '') {
			$where .= " and kodeorg = '" . $param['kodeorg'] . "'";
		}

		if ($param['tipetransaksi'] != '') {
			$where .= " and tipetransaksi='" . $param['tipetransaksi'] . "'";
		}
		if ($param['dibuat'] != '') {
			$where .= " and createby in (select karyawanid from " . $dbname . ".datakaryawan where namakaryawan like '%" . $param['dibuat'] . "%')";
		}
		if ($param['keterangan'] != '') {
			$where .= " and keterangan like '%" . $param['keterangan'] . "%'";
		}
		if ($param['noakun'] != '') {
			$where .= " and noakun = '" . $param['noakun'] . "'";
		}
		if ($param['rekening'] != '') {
			$where .= " and rekening = '" . $param['rekening'] . "'";
		}
		if ($param['appstatus'] != '') {
			$where .= " and posting = '" . $param['appstatus'] . "'";
		}

		if ($param['appstatus'] != '') {
			$where .= " and posting = '" . $param['appstatus'] . "'";
		}
		if ($param['appstatus'] != '') {
			$where .= " and posting = '" . $param['appstatus'] . "'";
		}

		if ($param['jumlah'] != '') {
			$where .= " and jumlah like '%" . $param['jumlah'] . "%'";
		}
		if ($param['noinvoice'] != '') {
			$where .= " and notransaksi in (select distinct notransaksi from " . $dbname . ".keu_kasbankdt where keterangan1 like '%" . $param['noinvoice'] . "%')";
		}
		if ($param['kodesupplier'] != '') {
			$where .= " and notransaksi in (select notransaksi from " . $dbname . ".keu_kasbankdt where kodesupplier='" . $param['kodesupplier'] . "')";
		}
		if ($param['bayarke'] != '') {
			$where .= " and bayarkepada like '%" . $param['bayarke'] . "%'";
		}
		if ($param['pembayaran'] != '') {
			$where .= " and pembayaran = '" . $param['pembayaran'] . "'";
		}

		// $str = "select * from ".$dbname.".".$table."  ".$where." order by tanggal desc,notransaksi desc ";
		$str = "select * from " . $dbname . "." . $table . " where " . $where . " order by tanggal desc,notransaksi desc";
		$res = fetchdata($str);
		foreach ($res as $bar) {

			# Status Approval
			$order = 'ASC';
			if ($bar['posting'] == 0) {
				$statusapp = $_SESSION['lang']['belumdiajukan'];
			} else {
				if ($bar['posting'] == 1) {
					$table = "approval";
					$whereapp = "status = '1'";
					$ket = $_SESSION['lang']['disetujui'];
					$order = 'DESC';
				} else if ($bar['posting'] == 9) {
					$table = "approval";
					$whereapp = "status = '0'";
					$ket = $_SESSION['lang']['wait_approval'];
				} else if ($bar['posting'] == 2) {
					$table = "approval";
					$whereapp = "status = '2'";
					$ket = $_SESSION['lang']['ditolak'];
				} else if ($bar['posting'] == 3) {
					$table = "approval";
					$whereapp = "status = '3'";
					$ket = "Di" . $_SESSION['lang']['koreksi'];
				}

				$str = "SELECT a.karyawanid, b.namakaryawan FROM " . $dbname . "." . $table . " a
						JOIN " . $dbname . ".datakaryawan b ON a.karyawanid = b.karyawanid
						WHERE notransaksi = '" . $bar['notransaksi'] . "' AND " . $whereapp . "
						ORDER BY level " . $order . " LIMIT 1";
				$res = fetchdata($str);
				$statusapp = $ket . "<br> (" . $res[0]['namakaryawan'] . ")";
			}

			#= ambil nilai dt
			$strdt = "select sum(jumlah) as jumlah from " . $dbname . "." . $tabledt . "  where notransaksi='" . $bar['notransaksi'] . "'";
			$resdt = fetchdata($strdt);
			@$jumlahdt = $resdt[0]['jumlah'];
			$sisa = $bar['jumlah'] - $jumlahdt;



			#=datakaryawan
			$strdt = "select namakaryawan,karyawanid from " . $dbname . ".datakaryawan where karyawanid in ('" . $bar['createby'] . "','" . $bar['updateby'] . "') ";
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				$namakaryawan[$bardt['karyawanid']] = $bardt['namakaryawan'];
			}

			#= ambil rekening 
			$strdt = "select * from " . $dbname . ".keu_5akunbank_vw  where noakun='" . $bar['rekening'] . "'";
			$resdt = fetchdata($strdt);
			@$namabank = $resdt[0]['namabank'];

			$bgcolor = 'class=rowcontent';
			if ($bar['posting'] == 3) {
				$bgcolor = "bgcolor='orange'  title='Koreksi'";
			}
			if ($bar['posting'] == 2) {
				$bgcolor = "bgcolor='red'  title='Ditolak'";
			}
			$no++;
			$tab .= "<tr " . $bgcolor . ">";
			$tab .= "<td align=center valign=top>" . $no . "</td>";
			$tab .= "<td valign=top>" . $bar['notransaksi'] . "</td>";
			$tab .= "<td valign=top>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td align=center valign=top>" . $bar['tipetransaksi'] . "</td>";
			$tab .= "<td valign=top>" . $nmorganisasi[$bar['kodeorg']] . "</td>";
			$tab .= "<td valign=top>" . $bar['noakun'] . "<br>" . $nmakun[$bar['noakun']] . "</td>";
			$tab .= "<td valign=top>" . $namabank . " " . $bar['rekening'] . "</td>";
			$tab .= "<td align=right valign=top>" . number_format($bar['jumlah'], 2) . "</td>";
			$tab .= "<td align=right valign=top>" . number_format($jumlahdt, 2) . "</td>";
			$tab .= "<td align=right valign=top>" . number_format($sisa, 2) . "</td>";
			$lenketerangan = strlen($bar['keterangan']);
			$tab .= "<td valign=top>" . nl2br($bar['keterangan']) . "</td>";
			$tab .= "<td valign=top>" . $bar['novoucher'] . "</td>";
			$tab .= "<td valign=top>" . $bar['bayarkepada'] . "</td>";
			$tab .= "<td valign=top>" . $namakaryawan[$bar['createby']] . "</td>";
			$tab .= "<td valign=top align=center>" . $statusapp . "</td>";

			$tab .= "</tr>";
		}

		$tab .= "</table>";

		// $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";//logoheight logowidth
		// $tab.="<tr>";
		// $tab.="<td></td>";
		// $tab.="<tr>";
		// $tab.="</table>";//logoheight logowidth	

		$tab .= "</div>";


		$dompdf = new Dompdf();
		$dompdf->load_html($tab);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		$dompdf->stream("Kasbank", array("Attachment" => 0));
		break;

	case 'loaddataexcel':

		$tab = "<style>
			@page {
				margin-top: 10px;
				margin-left: 10px;
				margin-right: 10px;
				margin-bottom: 10px;
			}
			body {
				font-family: Serif, Times-Roman;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>";


		#= untuk unit ht


		$cellpadding = 1;
		$cellspacing = 0;
		$sizefont = '10';
		// style='width:".$arrkodept['logowidth'].";height:".$arrkodept['logoheight']."'
		$tab .= "<div style='page-break-after: always;'>";
		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>"; //logoheight logowidth
		$tab .= "<tr>";
		$tab .= "<td align=left style='width:50px;' align=center><img src=images/owl2.png style='width:200;height:25'></td>";
		$tab .= "<tr>";
		$tab .= "</table>";
		$tab .= "<br>";
		$tab .= "<br>";

		$tab .= "<table width=100% style='font-size:" . ($sizefont) . "px' cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=1>"; //logoheight logowidth
		$tab .= "<thead>
                <tr class=rowheader>
					<th  align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
					<th  align=center rowspan=2>" . $_SESSION['lang']['notransaksi'] . "</th>
                    <th  align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</th>
					<th  align=center rowspan=2>" . $_SESSION['lang']['tipe'] . "</th>
                    <th  align=center rowspan=2>" . $_SESSION['lang']['unit'] . "</th>
                    <th  align=center rowspan=2>" . $_SESSION['lang']['noakun'] . "</th>
                    <th  align=center rowspan=2>" . $_SESSION['lang']['rekening'] . "</th>
                    <th  align=center colspan=3>" . $_SESSION['lang']['jumlah'] . "</th> 
                    <th  align=center rowspan=2 style=width:250px>" . $_SESSION['lang']['keterangan'] . " </th> 
                    <th  align=center rowspan=2>" . $_SESSION['lang']['novoucher'] . " </th> 
                    <th  align=center rowspan=2>" . $_SESSION['lang']['bayarke'] . " </th> 
                    <th  align=center rowspan=2>" . $_SESSION['lang']['dibuatoleh'] . "</th> 
                    <th  align=center rowspan=2>" . $_SESSION['lang']['approval_status'] . "</th> 
                </tr>  
				<tr class=rowheader>
					<th  align=center>" . $_SESSION['lang']['header'] . "</th>
					<th  align=center>" . $_SESSION['lang']['detail'] . "</th>
                    <th  align=center>Balance</th>
				</tr>";

		#= untuk unit ht
		$arrunit = array();
		$arrunit = getOrgDetail(1);
		foreach ($arrunit as $val => $nama) {
			$dtunit[$val] = $val;
		}


		$where = "1=1 and  kodeorg in ('" . implode("','", $dtunit) . "') ";

		if ($param['tanggalmulai'] != '' and $param['tanggalselesai'] != '') {
			$where .= " and tanggal between '" . tanggalsystemn($param['tanggalmulai']) . "' and '" . tanggalsystemn($param['tanggalselesai']) . "'";
		}

		if ($param['notransaksi'] != '') {
			$where .= " and notransaksi like '%" . $param['notransaksi'] . "%'";
		}

		if ($param['kodeorg'] != '') {
			$where .= " and kodeorg = '" . $param['kodeorg'] . "'";
		}

		if ($param['tipetransaksi'] != '') {
			$where .= " and tipetransaksi='" . $param['tipetransaksi'] . "'";
		}
		if ($param['dibuat'] != '') {
			$where .= " and createby in (select karyawanid from " . $dbname . ".datakaryawan where namakaryawan like '%" . $param['dibuat'] . "%')";
		}
		if ($param['keterangan'] != '') {
			$where .= " and keterangan like '%" . $param['keterangan'] . "%'";
		}
		if ($param['noakun'] != '') {
			$where .= " and noakun = '" . $param['noakun'] . "'";
		}
		if ($param['rekening'] != '') {
			$where .= " and rekening = '" . $param['rekening'] . "'";
		}
		if ($param['appstatus'] != '') {
			$where .= " and posting = '" . $param['appstatus'] . "'";
		}

		if ($param['appstatus'] != '') {
			$where .= " and posting = '" . $param['appstatus'] . "'";
		}
		if ($param['appstatus'] != '') {
			$where .= " and posting = '" . $param['appstatus'] . "'";
		}

		if ($param['jumlah'] != '') {
			$where .= " and jumlah like '%" . $param['jumlah'] . "%'";
		}
		if ($param['noinvoice'] != '') {
			$where .= " and notransaksi in (select distinct notransaksi from " . $dbname . ".keu_kasbankdt where keterangan1 like '%" . $param['noinvoice'] . "%')";
		}
		if ($param['kodesupplier'] != '') {
			$where .= " and notransaksi in (select notransaksi from " . $dbname . ".keu_kasbankdt where kodesupplier='" . $param['kodesupplier'] . "')";
		}
		if ($param['bayarke'] != '') {
			$where .= " and bayarkepada like '%" . $param['bayarke'] . "%'";
		}
		if ($param['pembayaran'] != '') {
			$where .= " and pembayaran = '" . $param['pembayaran'] . "'";
		}

		// $str = "select * from ".$dbname.".".$table."  ".$where." order by tanggal desc,notransaksi desc ";
		$str = "select * from " . $dbname . "." . $table . " where " . $where . " order by tanggal desc,notransaksi desc";
		$res = fetchdata($str);
		foreach ($res as $bar) {

			# Status Approval
			$order = 'ASC';
			if ($bar['posting'] == 0) {
				$statusapp = $_SESSION['lang']['belumdiajukan'];
			} else {
				if ($bar['posting'] == 1) {
					$table = "approval";
					$whereapp = "status = '1'";
					$ket = $_SESSION['lang']['disetujui'];
					$order = 'DESC';
				} else if ($bar['posting'] == 9) {
					$table = "approval";
					$whereapp = "status = '0'";
					$ket = $_SESSION['lang']['wait_approval'];
				} else if ($bar['posting'] == 2) {
					$table = "approval";
					$whereapp = "status = '2'";
					$ket = $_SESSION['lang']['ditolak'];
				} else if ($bar['posting'] == 3) {
					$table = "approval";
					$whereapp = "status = '3'";
					$ket = "Di" . $_SESSION['lang']['koreksi'];
				}

				$str = "SELECT a.karyawanid, b.namakaryawan FROM " . $dbname . "." . $table . " a
						JOIN " . $dbname . ".datakaryawan b ON a.karyawanid = b.karyawanid
						WHERE notransaksi = '" . $bar['notransaksi'] . "' AND " . $whereapp . "
						ORDER BY level " . $order . " LIMIT 1";
				$res = fetchdata($str);
				$statusapp = $ket . "<br> (" . $res[0]['namakaryawan'] . ")";
			}

			#= ambil nilai dt
			$strdt = "select sum(jumlah) as jumlah from " . $dbname . "." . $tabledt . "  where notransaksi='" . $bar['notransaksi'] . "'";
			$resdt = fetchdata($strdt);
			@$jumlahdt = $resdt[0]['jumlah'];
			$sisa = $bar['jumlah'] - $jumlahdt;



			#=datakaryawan
			$strdt = "select namakaryawan,karyawanid from " . $dbname . ".datakaryawan where karyawanid in ('" . $bar['createby'] . "','" . $bar['updateby'] . "') ";
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				$namakaryawan[$bardt['karyawanid']] = $bardt['namakaryawan'];
			}

			#= ambil rekening 
			$strdt = "select * from " . $dbname . ".keu_5akunbank_vw  where noakun='" . $bar['rekening'] . "'";
			$resdt = fetchdata($strdt);
			@$namabank = $resdt[0]['namabank'];

			$bgcolor = 'class=rowcontent';
			if ($bar['posting'] == 3) {
				$bgcolor = "bgcolor='orange'  title='Koreksi'";
			}
			if ($bar['posting'] == 2) {
				$bgcolor = "bgcolor='red'  title='Ditolak'";
			}
			$no++;
			$tab .= "<tr " . $bgcolor . ">";
			$tab .= "<td align=center valign=top>" . $no . "</td>";
			$tab .= "<td valign=top>" . $bar['notransaksi'] . "</td>";
			$tab .= "<td valign=top>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td align=center valign=top>" . $bar['tipetransaksi'] . "</td>";
			$tab .= "<td valign=top>" . $nmorganisasi[$bar['kodeorg']] . "</td>";
			$tab .= "<td valign=top>" . $bar['noakun'] . "<br>" . $nmakun[$bar['noakun']] . "</td>";
			$tab .= "<td valign=top>" . $namabank . " " . $bar['rekening'] . "</td>";
			$tab .= "<td align=right valign=top>" . number_format($bar['jumlah'], 2) . "</td>";
			$tab .= "<td align=right valign=top>" . number_format($jumlahdt, 2) . "</td>";
			$tab .= "<td align=right valign=top>" . number_format($sisa, 2) . "</td>";
			$lenketerangan = strlen($bar['keterangan']);
			$tab .= "<td valign=top>" . nl2br($bar['keterangan']) . "</td>";
			$tab .= "<td valign=top>" . $bar['novoucher'] . "</td>";
			$tab .= "<td valign=top>" . $bar['bayarkepada'] . "</td>";
			$tab .= "<td valign=top>" . $namakaryawan[$bar['createby']] . "</td>";
			$tab .= "<td valign=top align=center>" . $statusapp . "</td>";

			$tab .= "</tr>";
		}

		$tab .= "</table>";

		// $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";//logoheight logowidth
		// $tab.="<tr>";
		// $tab.="<td></td>";
		// $tab.="<tr>";
		// $tab.="</table>";//logoheight logowidth	


		$tab .= "</div>";

		$tab .= "Print Time : " . date('H:i:s, d/m/Y') . "<br>By : " . $_SESSION['empl']['name'];
		$tglSkrg = date("YmdHis");
		$nop_ = "listkasbank__" . $tglSkrg;
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
		// $dompdf = new Dompdf();
		// $dompdf->load_html($tab);
		// $dompdf->setPaper('A4', 'landscape');
		// $dompdf->render();
		// $dompdf->stream("Kasbank",array("Attachment"=>0));
		break;


	case 'loaddata':
		#= untuk unit ht
		$arrunit = array();
		$arrunit = getOrgDetail(1);
		foreach ($arrunit as $val => $nama) {
			$dtunit[$val] = $val;
		}

		$where = "1=1 and  kodeorg in ('" . implode("','", $dtunit) . "') ";

		if ($param['tanggalmulai'] != '' and $param['tanggalselesai'] != '') {
			$where .= " and tanggal between '" . tanggalsystemn($param['tanggalmulai']) . "' and '" . tanggalsystemn($param['tanggalselesai']) . "'";
		}

		if ($param['notransaksi'] != '') {
			$where .= " and notransaksi like '%" . $param['notransaksi'] . "%'";
		}

		if ($param['kodeorg'] != '') {
			$where .= " and kodeorg = '" . $param['kodeorg'] . "'";
		}

		if ($param['tipetransaksi'] != '') {
			$where .= " and tipetransaksi='" . $param['tipetransaksi'] . "'";
		}
		if ($param['dibuat'] != '') {
			$where .= " and createby in (select karyawanid from " . $dbname . ".datakaryawan where namakaryawan like '%" . $param['dibuat'] . "%')";
		}
		if ($param['keterangan'] != '') {
			$where .= " and keterangan like '%" . $param['keterangan'] . "%'";
		}
		if ($param['noakun'] != '') {
			$where .= " and noakun = '" . $param['noakun'] . "'";
		}
		if ($param['rekening'] != '') {
			$where .= " and rekening = '" . $param['rekening'] . "'";
		}
		if ($param['appstatus'] != '') {
			$where .= " and posting = '" . $param['appstatus'] . "'";
		}

		if ($param['appstatus'] != '') {
			$where .= " and posting = '" . $param['appstatus'] . "'";
		}
		if ($param['appstatus'] != '') {
			$where .= " and posting = '" . $param['appstatus'] . "'";
		}

		if ($param['jumlah'] != '') {
			$where .= " and jumlah like '%" . str_replace(",", "", $param['jumlah']) . "%'";
		}
		if ($param['noinvoice'] != '') {
			$noinvext = [];
			$str = "select * from " . $dbname . ".keu_tagihanht where noinvoicesupplier like '%" . $param['noinvoice'] . "%'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$noinvext[$bar['noinvoice']] = $bar['noinvoice'];
			}
			if (!empty($noinvext)) {
				$where .= " and notransaksi in (select distinct notransaksi from " . $dbname . ".keu_kasbankdt where keterangan1 in ('" . implode("','", $noinvext) . "'))";
			} else {
				$where .= " and notransaksi in (select distinct notransaksi from " . $dbname . ".keu_kasbankdt where keterangan1 like '%" . $param['noinvoice'] . "%')";
			}
		}
		if ($param['kodesupplier'] != '') {
			$where .= " and notransaksi in (select notransaksi from " . $dbname . ".keu_kasbankdt where kodesupplier='" . $param['kodesupplier'] . "')";
		}
		if ($param['bayarke'] != '') {
			$where .= " and bayarkepada like '%" . $param['bayarke'] . "%'";
		}
		if ($param['pembayaran'] != '') {
			$where .= " and pembayaran='" . $param['pembayaran'] . "'";
		}
		// print_r($param);


		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay = ($page * $limit);
		$colspan = 32;

		$offset = $page * $limit;


		// echo $limit._.$page._.$maxdisplay._.$offset;
		// $str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."  group by notransaksi  ";
		$str = "select count(*) as jumrow from " . $dbname . "." . $table . " where " . $where . "";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$jumrow = $bar['jumrow'];
		}

		$nyomor = 1;
		$no = 0;
		$no = $maxdisplay;
		$statusapp = '';
		$str = "select * from " . $dbname . "." . $table . " where " . $where . " order by tanggal desc,notransaksi desc limit " . $offset . "," . $limit . " ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			# Status Approval
			$order = 'ASC';
			if ($bar['posting'] == 0) {
				$statusapp = $_SESSION['lang']['belumdiajukan'];
			} else {
				if ($bar['posting'] == 1) {
					$table = "approval";
					$whereapp = "status = '1'";
					$ket = $_SESSION['lang']['disetujui'];
					$order = 'DESC';
				} else if ($bar['posting'] == 9) {
					$table = "approval";
					$whereapp = "status = '0'";
					$ket = $_SESSION['lang']['wait_approval'];
				} else if ($bar['posting'] == 2) {
					$table = "approval";
					$whereapp = "status = '2'";
					$ket = $_SESSION['lang']['ditolak'];
				} else if ($bar['posting'] == 3) {
					$table = "approval";
					$whereapp = "status = '3'";
					$ket = "Di" . $_SESSION['lang']['koreksi'];
				}

				$str = "SELECT a.karyawanid, b.namakaryawan FROM " . $dbname . "." . $table . " a
						JOIN " . $dbname . ".datakaryawan b ON a.karyawanid = b.karyawanid
						WHERE notransaksi = '" . $bar['notransaksi'] . "' AND " . $whereapp . "
						ORDER BY level " . $order . " LIMIT 1";
				$res = fetchdata($str);
				$statusapp = $ket . "<br> (" . $res[0]['namakaryawan'] . ")";
			}

			#= ambil nilai dt
			// $strdt = "select sum(jumlah) as jumlah,createby,createtime,updateby,updatetime from " . $dbname . "." . $tabledt . "  where notransaksi='" . $bar['notransaksi'] . "' and keterangan3 NOT LIKE '%KAS%'";
			$strdt = "select sum(jumlah) as jumlah,createby,createtime,updateby,updatetime from " . $dbname . "." . $tabledt . "  where notransaksi='" . $bar['notransaksi'] . "'";
			$resdt = fetchdata($strdt);
			@$jumlahdt = $resdt[0]['jumlah'];
			@$createbydt = $resdt[0]['createby'];
			@$createtimedt = $resdt[0]['createtime'];
			@$updatebydt = $resdt[0]['updateby'];
			@$updatetimedt = $resdt[0]['updatetime'];
			$sisa = $bar['jumlah'] - $jumlahdt;

			if (!is_null($createbydt)) {
				$strdt2 = "select createby,createtime,updateby,updatetime from " . $dbname . "." . $tabledt . "  where notransaksi='" . $bar['notransaksi'] . "' and createby!='0000000000' order by updatetime desc limit 1";
				$resdt2 = fetchdata($strdt2);
				@$createbydt = $resdt2[0]['createby'];
				@$createtimedt = $resdt2[0]['createtime'];
				@$updatebydt = $resdt2[0]['updateby'];
				@$updatetimedt = $resdt2[0]['updatetime'];
			}




			#= ambil rekening 
			$strdt = "select * from " . $dbname . ".keu_5akunbank_vw  where noakun='" . $bar['rekening'] . "'";
			$resdt = fetchdata($strdt);
			@$namabank = $resdt[0]['namabank'];

			#= ambil sudah ada file upload atau belum
			$fileupload = '';
			$strdt = "select count(*) as jumlah,createdby,createdtime from " . $dbname . ".listfileupload  where notransaksi='" . $bar['notransaksi'] . "'";
			$resdt = fetchdata($strdt);
			@$fileupload = $resdt[0]['jumlah'];
			@$createdbyfileupload = $resdt[0]['createdby'];
			@$createtimefileupload = $resdt[0]['createdtime'];


			#=datakaryawan
			$strdt = "select namakaryawan,karyawanid from " . $dbname . ".datakaryawan where karyawanid in ('" . $bar['createby'] . "','" . $bar['updateby'] . "','" . $createbydt . "','" . $updatebydt . "','" . $createdbyfileupload . "','" . $bar['submitby'] . "') ";
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				$namakaryawan[$bardt['karyawanid']] = $bardt['namakaryawan'];
			}

			if ($fileupload > 0) {
				$fileupload = "<img src=images/done.png class=resicon title='True'>";
			} else {
				$fileupload = "";
			}


			$onclickKbRef = "";
			$endonclickKbRef = "";

			# Jika Auto Kasbankk
			if ($bar['autokb'] == '1' || $bar['autokb'] == 1) {
				$onclickKbRef = "<label style='cursor:pointer;color:blue' onclick=norefautokb('" . $bar['novoucher'] . "','" . $bar['notransaksi'] . "')>";
				$endonclickKbRef = "</label>";
			}

			// $jumrow++;
			$bgcolor = 'class=rowcontent';
			if ($bar['posting'] == 3) {
				$bgcolor = "bgcolor='orange'  title='Koreksi'";
			}
			if ($bar['posting'] == 2) {
				$bgcolor = "bgcolor='red'  title='Ditolak'";
			}
			$no++;
			$nyomor++;
			$tab .= "<tr " . $bgcolor . " " . $style . " >";
			$tab .= "<td align=center valign=top>" . $no . "</td>";
			$tab .= "<td valign=top>" . $bar['notransaksi'] . "</td>";
			$tab .= "<td valign=top style='min-width:70px;text-align:center'>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td align=center valign=top>" . $bar['tipetransaksi'] . "</td>";
			// $tab.="<td valign=top>[".$bar['kodeorg']."] ".$nmorganisasi[$bar['kodeorg']]."</td>";
			$tab .= "<td valign=top>" . $nmorganisasi[$bar['kodeorg']] . "</td>";
			$tab .= "<td valign=top>" . $bar['noakun'] . "<br>" . $nmakun[$bar['noakun']] . "</td>";
			$tab .= "<td valign=top>" . $namabank . " " . $bar['rekening'] . "</td>";
			// $tab.="<td valign=top>".@$dtnamabank[$bar['rekening']]."<br>".$bar['rekening']."</td>";

			$tab .= "<td align=right valign=top>" . number_format($bar['jumlah'], 2) . "</td>";
			$tab .= "<td align=right valign=top>" . number_format($jumlahdt, 2) . "</td>";
			$tab .= "<td align=right valign=top>" . number_format($sisa, 2) . "</td>";
			$lenketerangan = strlen($bar['keterangan']);
			// if($lenketerangan<=40){
			// $tab.="<td valign=top>".$bar['keterangan']."</td>";
			// }else{
			// $tab.="<td valign=top>".substr($bar['keterangan'],0,40).".....</td>";
			// }
			$tab .= "<td valign=top>" . nl2br($bar['keterangan']) . "</td>";
			$tab .= "<td valign=top>" . $onclickKbRef . $bar['novoucher'] . $endonclickKbRef . "</td>";
			$tab .= "<td valign=top>" . $bar['bayarkepada'] . "</td>";


			$tab .= "<td valign=top align=center><label style='cursor:pointer;color:blue' onclick=\"gethistoriapproval('" . $bar['notransaksi'] . "',event,'KASBANK')\">" . $statusapp . "</label></td>";
			// $tab.="<td valign=top>".$namakaryawan[$bar['updateby']]."</td>";

			if ($bar['posting'] == 0 || $bar['posting'] == 3) {
				$tab .= "<td align=center valign=top  style=\"width:20px;\">";
				$tab .= "<img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('" . $bar['notransaksi'] . "');\"></td>";

				$tab .= "<td align=center valign=top  style=\"width:20px;\">";
				$tab .= "<img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('" . $bar['notransaksi'] . "');\"></td>";
			} else if ($bar['posting'] == 9) {
				$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
				$tab .= "<td align=center valign=top  style=\"width:20px;\">";
				$tab .= "<img src=images/icons/04/16/04.png class=zImgBtn height='30'  title='Proses Persetujuan'></td>";
			} else {
				$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
				$tab .= "<td align=center valign=top  style=\"width:20px;\">";
				$tab .= "<img src=images/icons/04/16/02.png  class=zImgBtn height='30'  title='Disetujui/Tolak' ></td>";
			}
			// $tab.="&nbsp;<img src=images/skyblue/zoom.png class=zImgBtn height='30'  title='Lihat Data' 
			// onclick=\"html('".$bar['notransaksi']."','".$bar['noakun']."','".$bar['tipetransaksi']."','".$bar['kodeorg']."');\">";			
			// $tab.="&nbsp;<img src=images/icons/04/16/01.png class=zImgBtn height='30'  title='Ajukan Persetujuan' onclick=\"ajukan('".$bar['notransaksi']."','".$page."');\">";								
			$tab .= "<td align=center valign=top  style=\"width:20px;\">";
			$tab .= "<img src=images/skyblue/zoom.png class=zImgBtn height='30'  title='Ajukan Persetujuan' onclick=\"ajukan('" . $bar['notransaksi'] . "','" . $page . "');\"></td>";

			$tab .= "<td align=center valign=top  style=\"width:20px;\">";

			// if($tipeorganisasi[$bar['kodeorg']] != 'KEBUN') {
			$tab .= "<img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF Nomor Transaksi : " . $bar['notransaksi'] . "' onclick=\"pdf('" . $bar['notransaksi'] . "','" . $bar['noakun'] . "','" . $bar['tipetransaksi'] . "','" . $bar['kodeorg'] . "');\"></td>";
			// } else {
			// 	$tab.="<img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF Nomor Transaksi : ".$bar['notransaksi']."' onclick=\"pdf('".$bar['notransaksi']."','".$bar['noakun']."','".$bar['tipetransaksi']."','".$bar['kodeorg']."','pdfpalmakebun');\"></td>";
			// }				

			if ($bar['novoucher'] != '') {
				$tab .= "<td align=center valign=top hidden style=\"width:20px;\">";
				$tab .= "<img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF Nomor Voucher : " . $bar['novoucher'] . "' onclick=\"pdfvoucher('" . $bar['novoucher'] . "');\">";
				$tab .= "</td>";
				$tab .= "<td align=center valign=top style=\"width:20px;\">";
				$tab .= "</td>";
			} else {
				$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
			}

			$tab .= "<td valign=top name=colht" . $nyomor . "[] align=center>" . $fileupload . "</td>";
			$tab .= "<td valign=top name=colht" . $nyomor . "[]>" . $namakaryawan[$createdbyfileupload] . "</td>";
			$tab .= "<td valign=top name=colht" . $nyomor . "[] align=center style='min-width:70px;'>" . $createtimefileupload . "</td>";

			$tab .= "<td valign=top name=colht" . $nyomor . "[]>" . $namakaryawan[$bar['createby']] . "</td>";
			$tab .= "<td valign=top name=colht" . $nyomor . "[] align=center style='min-width:70px;'>" . updatetimedata($bar['createtime']) . "</td>";
			$tab .= "<td valign=top name=colht" . $nyomor . "[]>" . $namakaryawan[$bar['updateby']] . "</td>";
			$tab .= "<td valign=top name=colht" . $nyomor . "[] align=center style='min-width:70px;'>" . updatetimedata($bar['updatetime']) . "</td>";

			$tab .= "<td valign=top name=colht" . $nyomor . "[]>" . $namakaryawan[$createbydt] . "</td>";
			$tab .= "<td valign=top name=colht" . $nyomor . "[] align=center style='min-width:70px;'>" . updatetimedata($createtimedt) . "</td>";
			$tab .= "<td valign=top name=colht" . $nyomor . "[]>" . $namakaryawan[$updatebydt] . "</td>";
			$tab .= "<td valign=top name=colht" . $nyomor . "[] align=center style='min-width:70px;'>" . updatetimedata($updatetimedt) . "</td>";

			$tab .= "<td valign=top name=colht" . $nyomor . "[]>" . $namakaryawan[$bar['submitby']] . "</td>";
			$tab .= "<td valign=top name=colht" . $nyomor . "[] align=center style='min-width:70px;'>" . updatetimedata($bar['tanggalpengajuan']) . "</td>";


			$tab .= "</tr>";
		}
		$tab .= "<tr style=display:none><td><input id=tempjumlahrowht value=" . $nyomor . "></td></tr>";
		$tab2 = createpaging($jumrow, $limit, $page, $colspan, 'loaddata', 'getpage');
		//$tab.="</table>";
		echo $tab . "####" . $tab2;
		break;

	case 'geteditht':

		$str = "select * from " . $dbname . "." . $table . "  where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		$res[0]['noakun'];

		echo
		$res[0]['notransaksi'] . "###" .
			$res[0]['tipetransaksi'] . "###" .
			$res[0]['kodeorg'] . "###" .
			$res[0]['noakun'] . "###" .
			tanggalnormal($res[0]['tanggal']) . "###" .
			$res[0]['bayarkepada'] . "###" .
			$res[0]['keterangan'] . "###" .
			$res[0]['matauang'] . "###" .
			number_format($res[0]['kurs'], 2) . "###" .
			number_format($res[0]['jumlah'], 2) . "###" .
			$res[0]['autokb'] . "###" .
			$res[0]['namapenerima'] . "###" .
			$res[0]['noakun2'] . "###" .
			$res[0]['norekpenerima'];
		break;

	case 'deleteht':
		try {
			$owlPDO->beginTransaction();

			// ini dia: ambil semua file
			$str = "select id, namafile from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$listhapusfile[$bar['id']] = $bar['id'];
				$hapusini[$bar['id']]['namafile'] = $bar['namafile'];
			}

			if (!empty($listhapusfile)) foreach ($listhapusfile as $idnyaz) {
				$namafile = $hapusini[$idnyaz]['namafile'];
				$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "' and namafile='" . $namafile . "'";
				// exit('error'.$str);
				try {
					$owlPDO->exec($str);
					// $pathx = $path.$namafile;
					// unlink($pathx);
				} catch (PDOException $e) {
					echo " Gagal," . addslashes($e->getMessage());
				}
			}


			##delete keu_kasbankdt_kk
			$str = "delete from " . $dbname . ".keu_kasbankdt_kk where notransaksi='" . $param['notransaksi'] . "' ";
			$owlPDO->exec($str);

			##Delete kas/bank HT
			$str = "delete from " . $dbname . "." . $table . " where notransaksi='" . $param['notransaksi'] . "' ";
			$owlPDO->exec($str);

			##Delete kas/bank dT
			$str = "delete from " . $dbname . "." . $tabledt . " where notransaksi='" . $param['notransaksi'] . "' ";
			$owlPDO->exec($str);



			## DELETE FROM TABLE APPROVAL
			$str = "delete from " . $dbname . ".approval where notransaksi='" . $param['notransaksi'] . "'";
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "warning, " . addslashes($e->getMessage());
		}



		break;

	case 'getrekening':

		$str = "select distinct(noakuncoa) as noakuncoa from " . $dbname . ".keu_5akunbank";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrnoakunbank[$bar['noakuncoa']] = $bar['noakuncoa'];
		}

		if (!in_array($param['noakun'], $arrnoakunbank)) {
			// $optrekening = "<option value=''>(Penerima adalah akun kas)</option>";
			$optrekening = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		}

		if ($param['notransaksi'] != '') {
			$str = "select * from " . $dbname . "." . $table . " where notransaksi='" . $param['notransaksi'] . "'";
			$res = fetchdata($str);
			$norek = $res[0]['rekening'];
		}

		// print_r($norek);exit("Error:A");
		/*
        if($param['noakun']=='1110101' or $param['noakun']=='1111101'){  
            $whr=""; 
            if ($param['noakun']=='1111101') {
                $whr=" and matauang!='IDR'";
            }else{
                $whr=" and matauang='IDR'";
            }
            $str = "select * from ".$dbname.".keu_5akunbank where status=1 and pemilik='".$param['kodeorg']."' ".$whr;
			
            $res=fetchdata($str);
			foreach($res as $bar){
				$wheredz =" kodebank='".$bar['namabank']."'";
                $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
				if($bar['noakun']==$norek){
					$optrekening.="<option selected value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
				}else{
					$optrekening.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
				}
			}
        }
		*/

		if ($param['noakun'] != '') {
			$whr = "";
			if (substr($param['noakun'], 5) != '11102') {
				$whr = " and noakuncoa='" . $param['noakun'] . "'";


				$str = "select * from " . $dbname . ".keu_5akunbank where status=1 and pemilik='" . $param['kodeorg'] . "' " . $whr;

				$res = fetchdata($str);
				foreach ($res as $bar) {
					$wheredz = " kodebank='" . $bar['namabank'] . "'";
					$optnama = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank', $wheredz);
					if ($bar['noakun'] == $norek) {
						$optrekening .= "<option selected value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
					} else {
						$optrekening .= "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
					}
				}
			} else {
				$optrekening = "<option value=''>(Penerima adalah akun kas)</option>";
			}
		}

		echo $optrekening;

		break;


	case 'getrekeningsch':
		$optrekening = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$tipeorganisasi[$param['kodeorg']] = $tipeorganisasi[$param['pemilikhutang1']];

		$str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun WHERE a.kasbank = 1 AND a.detail = 1 AND a.aktif = 1 AND a.level = '5' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('{$param['kodeorg']}')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik = '{$tipeorganisasi[$param['kodeorg']]}' OR a.pemilik IN ('" . implode("','", $arrtipeunit) . "')))) GROUP BY a.noakun";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optrekening .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $nmakun[$bar['noakun']] . "</option>";
		}
		echo $optrekening;
		break;

	case 'getbank':

		$str = "select distinct(noakuncoa) as noakuncoa from " . $dbname . ".keu_5akunbank";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrnoakunbank[$bar['noakuncoa']] = $bar['noakuncoa'];
		}

		if (!in_array($param['noakun'], $arrnoakunbank)) {
			// $optbank = "<option value=''>(Penerima adalah akun kas)</option>";
			$optbank = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		}

		if ($param['notransaksi'] != '') {
			$str = "select * from " . $dbname . "." . $table . " where notransaksi='" . $param['notransaksi'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$norek = $bar['norekpenerima'];
		}

		if ($param['noakun'] != '') {
			$whr = "";
			if (substr($param['noakun'], 5) != '11102') {
				$whr = " and noakuncoa='" . $param['noakun'] . "'";

				$str = "select * from " . $dbname . ".keu_5akunbank where status=1 and pemilik='" . $param['kodeorg'] . "' " . $whr;

				$res = fetchdata($str);
				foreach ($res as $bar) {
					$wheredz = " kodebank='" . $bar['namabank'] . "'";
					$optnama = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank', $wheredz);
					if ($bar['noakun'] == $norek) {
						$optbank .= "<option selected value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
					} else {
						$optbank .= "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
					}
				}
			} else {
				$optbank = "<option value=''>(Penerima adalah akun kas)</option>";
			}
		}

		/*
        if($param['noakun']=='1110101' or $param['noakun']=='1111101'){  
            $whr=""; 
            if ($param['noakun']=='1111101') {
                $whr=" and matauang!='IDR'";
            }else{
                $whr=" and matauang='IDR'";
            }
            $str = "select * from ".$dbname.".keu_5akunbank where status=1 and pemilik='".$param['kodeorg']."' ".$whr;
            $res=fetchdata($str);
			foreach($res as $bar){
				$wheredz =" kodebank='".$bar['namabank']."'";
                $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
				if($bar['noakun']==$norek){
					$optbank.="<option selected value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
				}else{
					$optbank.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
				}
			}
        }
		*/

		echo $optbank;

		break;




	case 'saveht':

		validasiInput(substr($param['kodeorg'], 0, 4), '', 'KB', tanggalsystemn($param['tanggal']), $exit = '1');

		if ($param['noakun'] == '') {
			exit("Warning:Nomor akun masih kosong");
		}
		if ($param['tanggal'] == '') {
			exit("Warning:Tanggal masih kosong");
		}
		if ($param['tipetransaksi'] == '') {
			exit("Warning:Tipe transaksi masih kosong");
		}
		if ($param['kodeorg'] == '') {
			exit("Warning:Unit masih kosong");
		}
		if ($param['jumlah'] == '') {
			exit("Warning:Jumlah masih kosong");
		}
		if ($param['noakun'] == '') {
			exit("Warning:Noakun tidak boleh kosong");
		}

		if ($param['autokb'] == '1') {
			if ($param['namapenerima'] == '') {
				exit("Warning:Jika auto kas/bank aktif, maka unit penerima tidak boleh kosong");
			}
			if ($param['noakun2'] == '') {
				exit("Warning:Jika auto kas/bank aktif, nama akun/bank penerima tidak boleh kosong");
			}
		}
		if ($param['keterangan'] == '') {
			exit("Warning:Keterangan Harus Terisi");
		}

		$param['kurs'] = str_replace(',', '', $param['kurs']);
		$param['jumlah'] = str_replace(',', '', $param['jumlah']);

		if ($param['notransaksi'] == '') {
			$tipe = "";
			$str = "select * from " . $dbname . ".keu_5parameterjurnal where kodeaplikasi='GL' and aktif=1 and jurnalid!= 'M'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($param['tipetransaksi'] == 'K') {
					if ($param['noakun'] >= $bar['noakunkredit'] and $param['noakun'] <= $bar['sampaikredit']) {
						$tipe = $bar['jurnalid'];
					}
				} else {
					if ($param['noakun'] >= $bar['noakundebet'] and $param['noakun'] <= $bar['sampaidebet']) {
						$tipe = $bar['jurnalid'];
					}
				}
			}
			$notrans = "/" . $param['kodeorg'] . "/" . $tipe . "/";
			// $str="select * from ".$dbname.".keu_kasbankht where 
			// notransaksi like '%".$notrans."%' and tanggalinput like '".substr(tanggalsystemn($param['tanggal']),0,7)."%'
			// order by notransaksi desc";
			$str = "select max(right(notransaksi,5)) as notransaksi from " . $dbname . "." . $table . " where 
				notransaksi like '%" . $notrans . "%' and tanggalinput like '" . substr(tanggalsystemn($param['tanggal']), 0, 7) . "%'
				order by notransaksi desc";
			// echo $str;exit();
			$res = fetchdata($str);
			if (empty($res)) {
				$temp = 1;
			} else {
				$temp = $res[0]['notransaksi'];
				$temp++;
			}
			$param['notransaksi'] = tanggalsystem($param['tanggal']) . $notrans . str_pad($temp, 5, '0', STR_PAD_LEFT);

			# Karena Default Cgttu 
			# Secara DB itu Cash, karena di KSP ga input
			# Di buat jika akunnya Bank (Rekening Bank) => Transfer, Giro, Cheque
			$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='AK' and	kodeparameter='AKAKUNBANK'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$arrdata = explode(',', $bar['nilai']);
			foreach ($arrdata as $key) {
				$arrakunbank[] = $key;
			}

			$arrctg = getEnum($dbname, 'keu_kasbankht', 'cgttu');
			foreach ($arrctg as $kei => $fal) {
				if (!in_array($param['noakun'], $arrakunbank)) {
					$cgttuNew = "Cash";
				} else {
					$cgttuNew = "Transfer";
				}
			}
			# End

			$str = "insert into " . $dbname . "." . $table . " 
				(kurs,jumlah,notransaksi,tipetransaksi,kodeorg,
				 noakun,tanggal,bayarkepada,keterangan,matauang,
				 autokb,noakun2,namapenerima,norekpenerima,tanggalinput,
				 createby,createtime,updateby,rekening,cgttu) 
				values 
			('" . $param['kurs'] . "','" . $param['jumlah'] . "','" . $param['notransaksi'] . "','" . $param['tipetransaksi'] . "','" . $param['kodeorg'] . "',
			'" . $param['noakun'] . "','" . tanggalsystemn($param['tanggal']) . "','" . $param['bayarkepada'] . "','" . $param['keterangan'] . "','" . $param['matauang'] . "',
			'" . $param['autokb'] . "','" . $param['noakun2'] . "','" . $param['namapenerima'] . "','" . $param['norekpenerima'] . "','" . tanggalsystemn($param['tanggal']) . "',
			'" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "','" . $param['rekening'] . "','" . $cgttuNew . "')";

			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}


			#= jika autokas langsung bentuk detail dengan noakun ayat silang
			if ($param['autokb'] == '1') {
				#auto buat detail terhadap dengan akun ayat silang

				$explnotransaksi = explode('/', $param['notransaksi']);
				$param['kode'] = $explnotransaksi[2];

				$str = "insert into " . $dbname . "." . $tabledt . "
				(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
				noakun2a,kode,keterangan1,keterangan2,matauang,
				kurs,kurs2,noaruskas,kodeorg,hutangunit1,lainnya,
				keterangan3,createby,createtime,updateby,updatetime) 
				values 
				('" . $param['notransaksi'] . "','" . $noakunayatsilang . "','" . $param['tipetransaksi'] . "','" . tanggalsystemn($param['tanggal']) . "','" . $param['jumlah'] . "',
				'" . $param['noakun'] . "','" . $param['kode'] . "','','" . $param['keterangan'] . "','" . $param['matauang'] . "',
				'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $noaruskasayatsilang . "','" . $param['kodeorg'] . "','0','AUTOKAS',
				'DPP','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}

				#= update keu_kasbankdt_kk
				$str = "update " . $dbname . ".keu_kasbankdt_kk set  notransaksi='" . $param['notransaksi'] . "',noakun='" . $param['noakun'] . "'
				where kodeorgkk='" . $param['namapenerima'] . "' and noakun2='" . $param['noakun2'] . "' and notransaksi=''";
				// exit("Error".$str);
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					echo " Gagal," . addslashes($e->getMessage());
				}
			}
			#== tutup auto kas


		} else {
			$str = "update " . $dbname . "." . $table . " set 
				kodeorg='" . $param['kodeorg'] . "',
				kurs='" . $param['kurs'] . "',
				jumlah='" . $param['jumlah'] . "',
				tanggal='" . tanggalsystemn($param['tanggal']) . "',
				bayarkepada='" . $param['bayarkepada'] . "',
				keterangan='" . $param['keterangan'] . "',
				matauang='" . $param['matauang'] . "',
				autokb='" . $param['autokb'] . "',
				noakun2='" . $param['noakun2'] . "',
				namapenerima='" . $param['namapenerima'] . "',
				norekpenerima='" . $param['norekpenerima'] . "',
				tanggalinput='" . tanggalsystemn($param['tanggal']) . "',
				updateby='" . $_SESSION['standard']['userid'] . "',
				rekening='" . $param['rekening'] . "',
				updatetime='" . date('Y-m-d H:i:s') . "'
				
				where notransaksi = '" . $param['notransaksi'] . "'";
			// exit("Error".$str);
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}

			#= jika autokas langsung bentuk detail dengan noakun ayat silang
			if($param['autokb']=='1'){
				#auto buat detail terhadap dengan akun ayat silang
				
				$explnotransaksi=explode('/',$param['notransaksi']);
				$param['kode']=$explnotransaksi[2];
				
				$str = "insert into ".$dbname.".".$tabledt."
				(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
				noakun2a,kode,keterangan1,keterangan2,matauang,
				kurs,kurs2,noaruskas,kodeorg,hutangunit1,lainnya,
				keterangan3,createby,createtime,updateby,updatetime) 
				values 
				('".$param['notransaksi']."','".$noakunayatsilang."','".$param['tipetransaksi']."','".tanggalsystemn($param['tanggal'])."','".$param['jumlah']."',
				'".$param['noakun']."','".$param['kode']."','','".$param['keterangan']."','".$param['matauang']."',
				'".$param['kurs']."','".$param['kurs']."','".$noaruskasayatsilang."','".$param['kodeorg']."','0','AUTOKAS',
				'DPP','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
				try{
					$owlPDO->exec($str);
				}catch (PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
				
				#= update keu_kasbankdt_kk
				$str = "update ".$dbname.".keu_kasbankdt_kk set  notransaksi='".$param['notransaksi']."',noakun='".$param['noakun']."'
				where kodeorgkk='".$param['namapenerima']."' and noakun2='".$param['noakun2']."' and notransaksi=''";
				// exit("Error".$str);
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					echo " Gagal," . addslashes($e->getMessage());
				}	
				
			}
			#== tutup auto kas
		}

		echo $param['notransaksi'];

		break;

	case 'getkurs':
		$kurs = 1;
		if ($param['matauang'] != 'IDR') {
			$str = "select * from " . $dbname . ".setup_matauangrate where 
				kode='" . $param['matauang'] . "' and daritanggal<='" . tanggalsystemn($param['tanggal']) . "' order by daritanggal desc limit 1 ";
			// echo $str;
			$res = fetchdata($str);
			$kurs = $res[0]['kurs'];
		}
		echo $kurs;

		break;




	########################################################################################################################################
	########################################################################################################################################
	########################################################################################################################################

	/*
	
	F
	I
	L
	E
	
	*/
	case 'submitfile':

		// $filesize=1;

		#= jadikan try commi
		try {

			$owlPDO->beginTransaction();

			$tgl = date("YmdHis");
			$his = date("His");
			$nmTemp = str_replace('-', '', str_replace('/', '', $param['notransaksi']));

			if ($_FILES['file']['size'] > $filesize) {
				throw new PDOException("Ukuran File melebihi " . number_format($filezie / 1024) . " KB; ukuran file ini " . number_format($_FILES['file']['size'] / 1024, 2) . " Kb");
			}

			if ($param['fileupload'] != '') {
				if ($_FILES['file']['error'] == 0) {
					$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
					$filename = $param['kriteriaefil'] . "_" . $nmTemp . "_" . $his . "" . $filetype;
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
					if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.rar') || ($filetype == '.gz') || ($filetype == '.zip') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
						$str = "insert into " . $dbname . ".listfileupload values ('','" . $param['notransaksi'] . "','" . $filename . "','" . $filetype . "','" . $param['kriteriaefil'] . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path . $filename, $file_tmpname);
					} else {
						throw new PDOException("Format file upload tidak boleh " . $filetype);
					}
				}
			} else {
				throw new PDOException("Upload file gagal.");
			}

			if (!file_exists($path . $filename)) {
				throw new PDOException("File gagal diupload");
			}

			#= cek file size server jika 0 byte maka gagal insert db, tapi file tidak dihapus diserver
			if (filesize($path . $filename) == '' || filesize($path . $filename) == '0') {
				throw new PDOException("Ukuran file terupload 0, Silahkan upload ulang");
			}
			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan upload data \n" . addslashes($e->getMessage());
		}

		break;

	case 'loadfiles':
		$form = '';
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "' ";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$no++;

			@$icon = seticonfile($bar['formaticon']);
			$form .= "<tr class=rowcontent >";
			$form .= "<td style='text-align:center'>" . $no . "</td>";
			$form .= "<td align='center'><img src=" . $icon . " class=resicon></a></td>";
			$form .= "<td>" . $bar['kriteriaefil'] . "</td>";
			$form .= "<td><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download>" . $bar['namafile'] . "</td>";
			$form .= "<td align=right>" . ukurandokumen(@filesize($path . str_replace('/', '', $bar['namafile']))) . "</td>";
			$form .= "<td align=center><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a></td>";
			$form .= "<td align=center>&nbsp<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $bar['notransaksi'] . "','" . $bar['namafile'] . "');\" ></td>";

			$form .= "</tr>";
		}

		#= ambil data noinvoice	
		$path   = "fileupload/keu_tagihan/";
		// $path   = "filegis/";
		$tempnamafile = '';
		$strinv = "select keterangan1,tipetransaksi from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "' AND keterangan1 != ''";
		$resinv = $owlPDO->query($strinv) or die(print " Gagal: " . PDOException::getMessage());
		$resinv->setFetchMode(PDO::FETCH_ASSOC);
		while ($barinv = $resinv->fetch()) {
			$tipetransaksi = $barinv['tipetransaksi'];
			$arrnoinvoice[$barinv['keterangan1']] = $barinv['keterangan1'];
		}

		@$carrnoinvoice = count($arrnoinvoice);

		if ($carrnoinvoice > 0 and $tipetransaksi == 'K') {
			$arrnamafile = array();
			$str = "select * from " . $dbname . ".listfileupload where notransaksi in ('" . implode("','", $arrnoinvoice) . "')";
			// echo $str;
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$lsformaticon[$bar['namafile']] = $bar['formaticon'];
				$lskriteriaefil[$bar['namafile']] = $bar['kriteriaefil'];
				$arrnamafile[$bar['namafile']] = $bar['namafile'];
				$lsnoinvoice[$bar['namafile']] = $bar['notransaksi'];
			}
			foreach ($arrnamafile as $lsnamafile) {
				@$icon = seticonfile($lsformaticon[$lsnamafile]);
				$no++;
				$form .= "<tr class=rowcontent >";
				$form .= "<td style='text-align:center'>" . $no . "</td>";
				$form .= "<td align='center'><img src=" . $icon . " class=resicon></a></td>";
				$form .= "<td>" . getcriterianame($lskriteriaefil[$lsnamafile]) . "</td>";
				$form .= "<td><a href='" . $path . str_replace('/', '', $lsnamafile) . "' download>" . $lsnamafile . "</td>";
				$form .= "<td align=right>" . ukurandokumen(@filesize($lsnamafile)) . "</td>";
				$form .= "<td colspan=2 align=center><a href='" . $path . str_replace('/', '', $lsnamafile) . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a><br>" . $lsnoinvoice[$lsnamafile] . "</td>";
				$form .= "</tr>";
			}
		}

		echo $form;

		break;


	case 'deletefile':
		$namafile = $param['namafile'];
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "' and namafile='" . $param['namafile'] . "'"; //exit('error'.$str);
		try {
			$owlPDO->exec($str);
			// $pathx = $path.$namafile;
			// unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;


	/*
	Lain
	*/

	case 'getlain':

		// indra

		$str = "select * from " . $dbname . ".organisasi where induk='" . $kodept[$param['kodeorg']] . "'";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optunitap .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}

		$tab .= "<fieldset><legend>" . $_SESSION['lang']['form'] . "</legend>";
		$tab .= "<table>";
		$tab .= "<tr>
			<td>" . $_SESSION['lang']['unit'] . "</td>
			<td>:</td>
			<td><select id=kodeorglain  style=\"width:154px;\">'" . $optunitap . "'</select>
			<img id=kodeorglain onclick=z.elSearch('kodeorglain',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>";
		$tab .= "</tr>";
		$tab .= "<tr>
			<td>" . $_SESSION['lang']['nodok'] . "</td>
			<td>:</td>
			<td><input type=text id=nodoklain size=50 value='" . date('Y') . "' class=myinputtext style=\"width:150px;\"></td>";
		$tab .= "</tr>";
		$tab .= "<tr>
			<td>" . $_SESSION['lang']['sumber'] . "</td>
			<td>:</td>
			<td><select id=sumberlain  style=\"width:154px;\">'" . $optsumberlain . "'</select>
			<img id=sumberlain onclick=z.elSearch('sumberlain',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>";
		$tab .= "</tr>";
		$tab .= "<tr>
				<td></td>
				<td></td>
				<td><button class=mybutton onclick=findlain()>" . $_SESSION['lang']['find'] . "</button></td>";
		$tab .= "</tr>";

		$tab .= "</table>";
		$tab .= "</fieldset>";
		$tab .= "<br>";
		$tab .= "<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		$tab .= "<thead><tr class=rowheader>
					<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
					<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td align=center>" . $_SESSION['lang']['nodok'] . "</td>
					<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
					<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
					<td align=center>" . $_SESSION['lang']['noakun'] . "</td>
					<td align=center>" . $_SESSION['lang']['noaruskas'] . "</td>
					<td align=center>" . $_SESSION['lang']['uangmuka'] . "</td>
					<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
					<td align=center>" . $_SESSION['lang']['kasbank'] . "</td>
					<td align=center>" . $_SESSION['lang']['sisa'] . "</td>
					<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
					<td align=center>" . $_SESSION['lang']['lain'] . "</td>
					<td align=center rowspan=2>" . $_SESSION['lang']['action'] . "</td>";
		$tab .= "</tr>";
		$tab .= "</thead>";
		$tab .= "<tbody id=formpencariantampil></tbody>";
		$tab .= "</table>";
		$tab .= "</fieldset>";

		echo $tab;
		break;

	case 'findlain':
		// echo "masuk";
		// print_r($param);



		$tab = '';

		switch ($param['sumberlain']) {

			case 'umpjdinas':

				if ($param['tipetransaksi'] == 'M') {
					exit("Warning : Tipe transaksi yang diperbolehkan hanya keluar");
				}

				if ($param['kodeorglain'] == '') {
					$where .= " and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $kodept[$param['kodeorg']] . "')";
				} else {
					$where .= " and kodeorg = '" . $param['kodeorglain'] . "'";
				}


				if ($param['nodoklain'] != '') {
					$where .= " and a.notransaksi like '%" . $param['nodoklain'] . "%'";
				}


				$optjns = makeOption($dbname, 'sdm_5jenisbiayapjdinas', 'id,keterangan');
				$no = 0;
				$dataxx = array();
				$str = "SELECT *  FROM " . $dbname . ".sdm_pjdinasht a  left join " . $dbname . ".sdm_pjdinasdt b 
				on a.notransaksi=b.notransaksi where 1=1 " . $where . " and b.sumber='0' and a.statuspengajuan='1' 
				and b.jumlah>'0' order by a.notransaksi desc";

				$res = fetchdata($str);
				foreach ($res as $bar) {
					#staffataunonstaff
					if ($bar['tipekary'] == '0') {
						$tipekar = 'staff';
					} else {
						$tipekar = 'nonstaff';
					}

					$dataxx[$bar['notransaksi']] = $bar['notransaksi'];
					$jlhrp[$bar['notransaksi']] += $bar['jumlah'];
					$kary[$bar['notransaksi']] = $bar['karyawanid'];
					$kdorg[$bar['notransaksi']] = $bar['kodeorg'];
					$jenisbyy[$bar['notransaksi']] = $bar['jenisbiaya'];
					$tanggal[$bar['notransaksi']] = $bar['tanggal'];
				}

				#==================================#
				# UMPJD #
				# CEK APAKAH SUDAH ADA TARIK
				#==================================#
				$viewtipedata = "OBJECT";
				$sql = "SELECT * FROM {$dbname}.keu_kasbankdtht_vw WHERE nodok IN ('" . implode("','", $dataxx) . "')";
				$res = fetchData($sql, $viewtipedata);

				foreach ($res as $val) {
					$noumkasbank[$val->nodok] = $val->jumlah;
				}


				foreach ($dataxx as $notransaksi) {
					$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $kary[$notransaksi] . "'");
					$namaid = $nmkar[$kary[$notransaksi]];


					$opttipeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='" . $kdorg[$notransaksi] . "'");
					$kodeorg = $kdorg[$notransaksi];
					$tipeorg = $opttipeorg[$kdorg[$notransaksi]];

					$noakun = '1180104';
					$str1 = "select distinct a.noaruskas,nama_aruskas from " . $dbname . ".keu_5aruskas_detail a left join " . $dbname . ".keu_5aruskas b 
					on a.noaruskas=b.noaruskas where a.noakun='" . $noakun . "' and b.tipetransaksi='K'";
					$res1 = fetchdata($str1);
					$noaruskasdt = $res1[0]['noaruskas'];
					$optaruskas = "";
					foreach ($res1 as $bar1) {
						$optaruskas .= "<option value=" . $bar1['noaruskas'] . ">" . $bar1['noaruskas'] . " - " . $bar1['nama_aruskas'] . "</option>";
					}


					$optnmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $noakun . "'");

					$keterangandata = "Pemby um pjd:" . $namaid . ";Nomor:" . $notransaksi;
					$lainnya = "umpjd#" . $notransaksi;

					// $strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan2='".$keterangan."'";
					// $resk = fetchdata($strk);
					// $jumdata=$resk[0]['jumlah']; 

					$keterangan = "Pemby um pjd:" . $namaid . ";Nomor:" . $notransaksi;

					$strdata = "select notransaksi from " . $dbname . ".keu_kasbankdt where nodok='" . $notransaksi . "' and lainnya='" . $lainnya . "'";
					// echo $strdata;
					$resdata = fetchdata($strdata);
					$notransaksiada = $resdata[0]['notransaksi'];

					// if($jumdata==0){

					$no++;
					$tab .= "<tr  class=rowcontent >";
					$tab .= "<td align=center >" . $no . "</td>";
					$tab .= "<td align=center >" . $kodeorg . "</td>";
					$tab .= "<td align=center >" . $notransaksi . "</td>";
					$tab .= "<td align=left >" . $namaid . "</td>";
					$tab .= "<td align=left >" . $tanggal[$notransaksi] . "</td>";
					// $tab.= "<td align=left >UM</td>";
					$tab .= "<td align=right >" . $optnmakun[$noakun] . "</td>";
					$tab .= "<td align=left ><select id=noaruskaslain" . $no . " style=width:150px>" . $optaruskas . "</select></td>";
					$tab .= "<td align=right >" . number_format($jlhrp[$notransaksi], 2) . "</td>";
					$tab .= "<td align=right >" . number_format($jlhrp[$notransaksi], 2) . "</td>";
					$tab .= "<td align=right >" . number_format($noumkasbank[$notransaksi], 2) . "</td>";
					$tab .= "<td align=right >" . number_format(($jlhrp[$notransaksi] - $noumkasbank[$notransaksi]), 2) . "</td>";
					$tab .= "<td	>" . $keterangan . "</td>";
					$tab .= "<td	>" . $lainnya . "</td>";


					if ($notransaksiada != '') {
						$tab .= "<td	>" . $notransaksiada . "</td>";
					} else {
						$tab .= "<td align=center><img src=images/save.png class=zImgBtn caption='Save' onclick=\"savelain('" . $kodeorg . "','" . $noaruskasdt . "','" . $noakun . "','" . $keterangan . "','" . $notransaksi . "','','" . $kary[$notransaksi] . "','" . $jlhrp[$notransaksi] . "','" . $no . "','','" . $lainnya . "');\"></td>";
					}

					$tab .= "</tr>";
					// }
				}


				break;



			case 'claimpjdinas':

				// if($param['tipetransaksi']=='M'){
				// exit("Warning : Tipe transaksi yang diperbolehkan hanya keluar");
				// }

				if ($param['kodeorglain'] == '') {
					$where .= " and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $kodept[$param['kodeorg']] . "')";
				} else {
					$where .= " and kodeorg = '" . $param['kodeorglain'] . "'";
				}

				if ($param['nodoklain'] != '') {
					$where .= " and a.notransaksi like '%" . $param['nodoklain'] . "%'";
				}


				$optjns = makeOption($dbname, 'sdm_5jenisbiayapjdinas', 'id,keterangan');
				$no = 0;
				$dataxx = array();

				$str = "SELECT *  FROM " . $dbname . ".sdm_pjdinasht a  left join " . $dbname . ".sdm_pjdinasdt b 
				on a.notransaksi=b.notransaksi where 1=1 " . $where . " and b.sumber='1' and b.tanggungan='1' and a.statuspengajuan='1' and a.statusrealisasi='1' and b.jumlahhrd>'0' and b.statusverifikasihrd='1'
				order by a.notransaksi desc";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					#staffataunonstaff
					if ($bar['tipekary'] == '0') {
						$tipekar = 'staff';
					} else {
						$tipekar = 'nonstaff';
					}
					$level = $bar['level'];
					$dataxx[$bar['notransaksi']] = $bar['notransaksi'];
					$jlhrp[$bar['notransaksi']] += $bar['jumlahhrd'];
					$kary[$bar['notransaksi']] = $bar['karyawanid'];
					$kdorg[$bar['notransaksi']] = $bar['kodeorg'];
					$jenisbyy[$bar['notransaksi']] = $bar['jenisbiaya'];
					$tanggal[$bar['notransaksi']] = $bar['tanggal'];
				}
				$arrLainnya = array('BULKING', 'TC', 'RND', 'HOLDING', 'KANWIL'); //ikutin yg udh ada patok
				$arrakun = array('BULKING' => '8122101', 'TC' => '8212101', 'RND' => '8260604', 'KANWIL' => '8212101', 'HOLDING' => '8212101');
				foreach ($dataxx as $notransaksi) {

					$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $kary[$notransaksi] . "'");
					$namaid = $nmkar[$kary[$notransaksi]];

					$opttipeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='" . $kdorg[$notransaksi] . "'");
					$kodeorg = $kdorg[$notransaksi];
					$tipeorg = $opttipeorg[$kdorg[$notransaksi]];

					if (in_array($tipeorg, $arrLainnya)) {
						$noakun = $arrakun[$tipeorg];
					} else {
						#ini unit kebun dan pks
						$noakun = '7121000';
					}

					#info uang muka
					$umdiambil = 0;
					$str = "select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from " . $dbname . ".keu_kasbankdt where nodok='" . $notransaksi . "'  and nik='" . $kary[$notransaksi] . "'";
					$res = fetchdata($str);
					$umdiambil = $res[0]['jumlah'];

					$n = "";
					if ($umdiambil > $jlhrp[$notransaksi]) {
						$n = "style=color:red;";
					}

					$str1 = "select distinct a.noaruskas,nama_aruskas from " . $dbname . ".keu_5aruskas_detail a left join " . $dbname . ".keu_5aruskas b 
					on a.noaruskas=b.noaruskas where a.noakun='" . $noakun . "' and b.tipetransaksi='K'";
					$res1 = fetchdata($str1);
					$noaruskasdt = $res1[0]['noaruskas'];
					$optaruskas = "";
					foreach ($res1 as $bar1) {
						$optaruskas .= "<option value=" . $bar1['noaruskas'] . ">" . $bar1['noaruskas'] . " - " . $bar1['nama_aruskas'] . "</option>";
					}
					$optnmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $noakun . "'");

					$keterangan = "Pemby klaim pjd:" . $namaid . ";Nomor:" . $notransaksi;
					$lainnya = "claimpjd#" . $notransaksi;

					// $strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan2='".$keterangan."'";
					// $resk = fetchdata($strk);
					// $jumdata=$resk[0]['jumlah'];

					$strdata = "select notransaksi from " . $dbname . ".keu_kasbankdt where nodok='" . $notransaksi . "' and lainnya='" . $lainnya . "'";
					// echo $strdata;
					$resdata = fetchdata($strdata);
					$notransaksiada = $resdata[0]['notransaksi'];

					// if($jumdata==0){
					$no++;
					$tab .= "<tr  class=rowcontent >";
					$tab .= "<td align=center >" . $no . "</td>";
					$tab .= "<td align=center >" . $kodeorg . "</td>";
					$tab .= "<td align=center >" . $notransaksi . "</td>";
					$tab .= "<td align=left >" . $namaid . "</td>";
					$tab .= "<td align=left >" . $tanggal[$notransaksi] . "</td>";
					// $tab.= "<td align=left >UM</td>";
					$tab .= "<td align=left >" . $noakun . "-" . $optnmakun[$noakun] . "</td>";
					$tab .= "<td align=left ><select id=noaruskaslain" . $no . " style=width:150px>" . $optaruskas . "</select></td>";
					$tab .= "<td align=right >" . number_format($umdiambil, 2) . "</td>";
					$tab .= "<td align=right ></td>";
					$tab .= "<td align=right ></td>";
					$tab .= "<td align=right >" . number_format($jlhrp[$notransaksi], 2) . "</td>";
					$tab .= "<td>" . $keterangan . "</td>";
					$tab .= "<td>" . $lainnya . "</td>";

					if ($param['tipetransaksi'] == 'M') {
						$jlhrp[$notransaksi] = ($jlhrp[$notransaksi] * -1);
					}

					if ($notransaksiada != '') {
						$tab .= "<td	>" . $notransaksiada . "</td>";
					} else {
						$tab .= "<td align=center><img src=images/save.png class=zImgBtn caption='Save' onclick=\"savelain('" . $kodeorg . "','" . $noaruskasdt . "','" . $noakun . "','" . $keterangan . "','" . $notransaksi . "','','" . $kary[$notransaksi] . "','" . $jlhrp[$notransaksi] . "','" . $no . "','','" . $lainnya . "');\"></td>";
					}

					$tab .= "</tr>";

					// }
				}

				break;

			case 'batalpjd':

				$optjns = makeOption($dbname, 'sdm_5jenisbiayapjdinas', 'id,keterangan');
				$no = 0;
				$dataxx = array();
				$str = "SELECT *  FROM " . $dbname . ".sdm_pjdinasht a  left join " . $dbname . ".sdm_pjdinasdt b 
				on a.notransaksi=b.notransaksi where 1=1 " . $where . " and a.statuspengajuan='3' 
				and b.jumlah>'0' order by a.notransaksi desc";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					#staffataunonstaff
					if ($bar['tipekary'] == '0') {
						$tipekar = 'staff';
					} else {
						$tipekar = 'nonstaff';
					}

					$dataxx[$bar['notransaksi']] = $bar['notransaksi'];
					$kary[$bar['notransaksi']] = $bar['karyawanid'];
					$kdorg[$bar['notransaksi']] = $bar['kodeorg'];
					$jenisbyy[$bar['notransaksi']] = $bar['jenisbiaya'];
					$tanggal[$bar['notransaksi']] = $bar['tanggal'];
				}


				foreach ($dataxx as $notransaksi) {
					$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $kary[$notransaksi] . "'");
					$namaid = $nmkar[$kary[$notransaksi]];


					$opttipeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='" . $kdorg[$notransaksi] . "'");
					$kodeorg = $kdorg[$notransaksi];
					$tipeorg = $opttipeorg[$kdorg[$notransaksi]];

					$noakun = '1180104';
					$str1 = "select distinct a.noaruskas,nama_aruskas from " . $dbname . ".keu_5aruskas_detail a left join " . $dbname . ".keu_5aruskas b 
						on a.noaruskas=b.noaruskas where a.noakun='" . $noakun . "' and b.tipetransaksi='K'";
					$res1 = fetchdata($str1);
					$noaruskasdt = $res1[0]['noaruskas'];
					$optaruskas = "";
					foreach ($res1 as $bar1) {
						$optaruskas .= "<option value=" . $bar1['noaruskas'] . ">" . $bar1['noaruskas'] . " - " . $bar1['nama_aruskas'] . "</option>";
					}

					$optnmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $noakun . "'");

					$keterangan = "Pengembalian um pjd (Batal Dinas):" . $namaid . ";Nomor:" . $notransaksi;
					$wh = "umpjd#" . $notransaksi;
					$lainnya = "pjdbatal#" . $notransaksi;
					$strk = "select sum(jumlah) as jumlah from " . $dbname . ".keu_kasbankdt where lainnya='" . $wh . "'";
					$resk = fetchdata($strk);
					$rupiah = $resk[0]['jumlah'];
					$rupiah = '200000';


					// $strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan2='".$keterangan."'";
					// $resk = fetchdata($strk);
					// $jumdata=$resk[0]['jumlah'];

					$strdata = "select notransaksi from " . $dbname . ".keu_kasbankdt where nodok='" . $notransaksi . "' and keterangan2='" . $lainnya . "'";
					// echo $strdata;
					$resdata = fetchdata($strdata);
					$notransaksiada = $resdata[0]['notransaksi'];


					$adddata = "";
					// if($jumdata==0 and $rupiah>0){ 
					if ($rupiah > 0) {
						// $no++;
						// $adddata=" style='cursor:pointer' onclick=\"getdatadt(
						// '".$notransaksi."','".$kodeorg."','".$noakun."',
						// '".$rupiah."','".$noaruskasdt."',
						// '".$keterangan."','','".$kary[$notransaksi]."',
						// '".$keterangandata."');\"";
						// $tab.= "<tr  class=rowcontent>";
						// $tab.= "<td align=center title='add detail' ".$adddata.">".$no."</td>";
						// $tab.= "<td align=center title='add detail' ".$adddata.">".$notransaksi."</td>";
						// $tab.= "<td align=left title='add detail' ".$adddata.">Batal UM</td>";
						// $tab.= "<td align=left title='add detail' ".$adddata.">".$namaid."</td>";
						// $tab.= "<td align=right title='add detail' ".$adddata.">".$optnmakun[$noakun]."</td>";
						// $tab.= "<td align=left title='add detail'><select id=aruskaspjd style=width:150px>".$optaruskas."</select></td>";
						// $tab.= "<td align=right title='add detail' ".$adddata.">".number_format($rupiah)."</td>";
						// $tab.= "</tr>";

						$no++;
						$tab .= "<tr  class=rowcontent >";
						$tab .= "<td align=center >" . $no . "</td>";
						$tab .= "<td align=center >" . $kodeorg . "</td>";
						$tab .= "<td align=center >" . $notransaksi . "</td>";
						$tab .= "<td align=left >" . $namaid . "</td>";
						$tab .= "<td align=left >" . $tanggal[$notransaksi] . "</td>";
						// $tab.= "<td align=left >UM</td>";
						$tab .= "<td align=right >" . $optnmakun[$noakun] . "</td>";
						$tab .= "<td align=left ><select id=noaruskaslain" . $no . " style=width:150px>" . $optaruskas . "</select></td>";
						$tab .= "<td align=right ></td>";
						$tab .= "<td align=right ></td>";
						$tab .= "<td align=right ></td>";
						$tab .= "<td align=right >" . number_format($rupiah, 2) . "</td>";
						$tab .= "<td>" . $keterangan . "</td>";
						$tab .= "<td>" . $lainnya . "</td>";



						if ($notransaksiada != '') {
							$tab .= "<td	>" . $notransaksiada . "</td>";
						} else {
							$tab .= "<td align=center><img src=images/save.png class=zImgBtn caption='Save' onclick=\"savelain('" . $kodeorg . "','" . $noaruskasdt . "','" . $noakun . "','" . $keterangan . "','" . $notransaksi . "','','" . $kary[$notransaksi] . "','" . $jlhrp[$notransaksi] . "','" . $no . "','','" . $lainnya . "');\"></td>";
						}

						$tab .= "</tr>";
					}
				}

				break;


			default:
				echo "belum ada untuk tipe " . $param['sumberlain'] . " ";
				break;
		}



		echo $tab;

		break;


	case 'savelain':

		if ($param['noaruskas'] == '') {
			exit("Warning:Noaruskas masih kosong untuk akun " . $param['noakundt'] . ", silahkan ditambahkan di keuangan->setup->aruskas ");
		}


		$str = "select * from " . $dbname . "." . $table . " where  notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		$param['tanggal'] = $res[0]['tanggal'];
		$param['kurs'] = $res[0]['kurs'];
		$param['matauang'] = $res[0]['matauang'];
		$param['tipetransaksi'] = $res[0]['tipetransaksi'];
		$param['noakun'] = $res[0]['noakun'];
		$param['kodeorg'] = $res[0]['kodeorg'];

		$explnotransaksi = explode('/', $param['notransaksi']);
		$param['kode'] = $explnotransaksi[2];

		$param['jumlahdt'] = str_replace(',', '', $param['jumlahdt']);

		// echo"<pre>";
		// print_r($param);
		// echo"</pre>";exit("Error:MASUK");
		// if($param['sumberlain']=="claimpjdinas"){
		// exit("Error:MASUK");
		// }


		#= ambil nomor max
		/*
		$str = "select max(nourut) as nourut from ".$dbname.".".$tabledt." where  notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		$param['nourut']= $res[0]['nourut']+1;
		*/

		#= buat keterangan
		// $param['keterangan2']="Pembayaran untuk nomor dokumen ".$param['nodok']."; ".$nmaruskas[$param['noaruskas']]."";

		$param['hutangunit1'] = 1;
		if ($param['pemilikhutang1'] == $param['kodeorg']) {
			$param['hutangunit1'] = '0';
			$param['pemilikhutang1'] = '';
		}

		$umdiambil = 0;
		$str = "select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from " . $dbname . ".keu_kasbankdt where nodok='" . $param['nodok'] . "'  and nik='" . $param['nik'] . "'";
		$res = fetchdata($str);
		if (count($res) > 0) {
			$umdiambil = $res[0]['jumlah'];
		}


		$str = "insert into " . $dbname . "." . $tabledt . "
		(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
		noakun2a,kode,keterangan1,keterangan2,matauang,
		kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
		kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
		orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,keterangan3,lainnya) 
		values 
		('" . $param['notransaksi'] . "','" . $param['noakundt'] . "','" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . $param['jumlahdt'] . "',
		'" . $param['noakun'] . "','" . $param['kode'] . "','','" . $param['keterangan2'] . "','" . $param['matauang'] . "',
		'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $param['noaruskas'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
		'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
		'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','','DPP','" . $param['lainnya'] . "')";
		if ($umdiambil > 0) {
			$noakun = '1180104';
			$str1 = "select distinct a.noaruskas,nama_aruskas from " . $dbname . ".keu_5aruskas_detail a left join " . $dbname . ".keu_5aruskas b 
					on a.noaruskas=b.noaruskas where a.noakun='" . $noakun . "' and b.tipetransaksi='K'";
			$res1 = fetchdata($str1);
			$noaruskasdt = $res1[0]['noaruskas'];

			$umdiambil = $umdiambil * -1;

			$str .= ",('" . $param['notransaksi'] . "','" . $noakun . "','" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . $umdiambil . "',
		'" . $param['noakun'] . "','" . $param['kode'] . "','','" . $param['keterangan2'] . "','" . $param['matauang'] . "',
		'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $param['noaruskas'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
		'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
		'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','','DPP','" . $param['lainnya'] . "')";
		}

		// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		if ($param['sumberlain'] == "claimpjdinas") {
			$str = "select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg,noaruskas from " . $dbname . ".keu_kasbankdt where keterangan1='" . $param['nodok'] . "' and lainnya='umpjd#" . $param['nodok'] . "' and nik='" . $param['nik'] . "' group by noakun";
			// $str="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg,noaruskas from ".$dbname.".keu_kasbankdt where keterangan1='".$param['nodok']."' and keterangan2='umpjd#".$param['nodok']."' and nik='".$param['nik']."' group by noakun";
			$res = fetchdata($str);
			$jlhum = 0;
			$info = "";
			if (count($res) > 0) {
				foreach ($res as $bar) {
					$jlhum += $bar['jumlah'];
					$noakunum = $bar['noakun'];
					$notranum = $bar['notransaksi'];
					$aruskasum = $bar['noaruskas'];
					$ketum = "Ptj um pjdinas, no:" . $notranum;
					$kodeorgum = $bar['kodeorg'];

					$param['hutangunit1'] = 1;
					if ($kodeorgum == $param['kodeorg']) {
						$param['hutangunit1'] = '0';
						$param['pemilikhutang1'] = '';
					}
					#ini jurnal balasan uang muka
					if ($param['tipetransaksi'] == 'K') {
						$jlhum = $jlhum * (-1);
					} else {
						$jlhum = $jlhum;
					}

					// if($param['tipetransaksi']=='K' and abs($jlhum) > abs($param['jumlah'])){
					// exit("Warning : Gunakan tipe transaksi Masuk.");
					// }elseif($param['tipetransaksi']=='M' and abs($param['jumlah']) > abs($jlhum)){
					// exit("Warning : Gunakan tipe transaksi Keluar.");
					// }


					$str = "insert into " . $dbname . "." . $tabledt . "
					(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
					noakun2a,kode,keterangan1,keterangan2,matauang,
					kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
					kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
					orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,keterangan3,lainnya) 
					values 
					('" . $param['notransaksi'] . "','" . $bar['noakun'] . "','" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . $jlhum . "',
					'" . $param['noakun'] . "','" . $param['kode'] . "','','" . $param['keterangan2'] . "','" . $param['matauang'] . "',
					'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $bar['noaruskas'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
					'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
					'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','','DPP','" . $param['lainnya'] . "')";
					// exit("Error:$str");try{
					try {
						$owlPDO->exec($str);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						die();
					}


					// $str="insert into ".$dbname.".keu_kasbankdt (`notransaksi`, `noakun`, `tipetransaksi`, 
					// `tanggal`,`jumlah`, `noakun2a`, `kode`,`keterangan1`,`keterangan2`, `matauang`, `kurs`,
					// `kurs2`,`noaruskas`,`kodeorg`,`nodok`,`bulan`,`tahun`,`keterangan3`,".$fieldPenerima.") 
					// values ('".$param['notransaksi']."','".$noakunum."','".$param['tipetransaksi']."',
					// '".tanggalsystemn($param['tanggal'])."','".$jlhum*(-1)."','".$param['noakun']."',
					// '".$param['kode']."','".$ket1."','".$param['keterangan2']."','".$param['matauang']."',
					// '".$param['kurs']."','1','".$aruskasum."','".$param['kodeorg']."','".$param['notran']."',
					// '".$param['bulan']."','".$param['tahun']."','".$ketum."','".$param['penerima']."')";
					// #exit("error".$str);
					// try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

				}
			}
		}


		break;

	/*
	A
	P
	*/

	case 'getap':

		// $str = "select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."' ";
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// $kodept=$bar['induk'];
		// }

		// $str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)='4' and induk='".$kodept."' ";
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// $optunitap.="<option value='".$bar['kodeorganisasi']."'>".$bar['induk']." - ".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		// }

		$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)='4'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optunitap .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['induk'] . " - " . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}

		$opttipeinvoice = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		$str = "select distinct(tipeinvoice) as tipeinvoice from " . $dbname . ".keu_tagihanht where posting=1";
		$res = fetchData($str);
		foreach ($res as $bar) {
			if (@$arrtipeinvoice[$bar['tipeinvoice']] != '') {
				$opttipeinvoice .= "<option value='" . $bar['tipeinvoice'] . "'>" . $bar['tipeinvoice'] . " - " . $arrtipeinvoice[$bar['tipeinvoice']] . "</option>";
			}
		}


		//$tab.="<fieldset><legend>".$_SESSION['lang']['form']."</legend>";
		$tab .= "<table>";
		$tab .= "<tr>
					<td>" . $_SESSION['lang']['noinvoice'] . "</td>
					<td>:</td>
					<td><input type=text id=noinvoiceap  size=50 class=myinputtext style=\"width:150px;\"></td>
					
					<td>" . $_SESSION['lang']['unit'] . "</td>
					<td>:</td>
					<td><select  class=select2 id=kodeorgap  style=\"width:154px;\">'" . $optunitap . "'</select>
					<img hidden id=kodeorgap onclick=z.elSearch('kodeorgap',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
		
					<td>" . $_SESSION['lang']['tipeinvoice'] . "</td>
					<td>:</td>
					<td><select  class=select2 id=tipeinvoiceap  style=\"width:154px;\">'" . $opttipeinvoice . "'</select>
					<img hidden id=tipeinvoiceap onclick=z.elSearch('tipeinvoiceap',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
		
					<td>" . $_SESSION['lang']['nilaiinvoice'] . "</td>
					<td>:</td>		
					<td><input  class=myinputtextnumber id=nilaiinvoiceap onkeyup=z.numberFormat('nilaiinvoiceap',2); style=\"width:150px;\" onkeypress='return angka_doang(event)' /></td>";
		$tab .= "</tr>";
		$tab .= "<tr>
					<td>" . $_SESSION['lang']['noinvoicesupplier'] . "</td>
					<td>:</td>
					<td><input type=text id=noinvoicesupplierap  size=50 class=myinputtext style=\"width:150px;\"></td>
					
					<td>" . $_SESSION['lang']['nodok'] . "</td>
					<td>:</td>
					<td><input type=text id=nodokap size=50 class=myinputtext style=\"width:150px;\"></td>
					<td>" . $_SESSION['lang']['supplier'] . "</td>
					<td>:</td>
					<td><select class=select2 id=kodesupplierap  style=\"width:154px;\">'" . $optsupplier . "'</select>
					<img  hidden  id=kodesupplierap onclick=z.elSearch('kodesupplierap',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>";
		$tab .= "</tr>";
		$tab .= "<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=findap()>" . $_SESSION['lang']['find'] . "</button></td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		//$tab.="</fieldset>";
		$tab .= "<br>";

		// $tab.="<button class=mybutton onclick=saveap()>AP</button>&nbsp;";
		$tab .= " <div class=table-scroll style='height:60vh'>";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		$tab .= "<thead><tr class=rowheader>
					<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center>" . $_SESSION['lang']['noinvoice'] . "</th>
					<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center>" . $_SESSION['lang']['unit'] . "</th>
					<th align=center>" . $_SESSION['lang']['tipeinvoice'] . "</th>
					<th align=center>" . $_SESSION['lang']['supplier'] . "</th>
					<th align=center>" . $_SESSION['lang']['nodok'] . "</th>
					<th align=center>Tipe Arus Kas</th>
					<th align=center>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>
					<th align=center colspan=2>" . $_SESSION['lang']['keterangan'] . "</th>
					<th align=center>" . $_SESSION['lang']['jatuhtempo'] . "</th>
					<th align=center>" . $_SESSION['lang']['nilai'] . "</th>
					<th align=center>" . $_SESSION['lang']['ppn'] . "</th>
					<th align=center>" . $_SESSION['lang']['pph'] . "</th>
					<th align=center>" . $_SESSION['lang']['total'] . "</th>
					<th align=center>" . $_SESSION['lang']['kasbank'] . "</th>
					<th align=center>" . $_SESSION['lang']['sisa'] . "</th>
					<th align=center hidden>" . $_SESSION['lang']['uangmuka'] . "</th>
					<th align=center hidden>" . $_SESSION['lang']['noaruskas'] . " " . $_SESSION['lang']['uangmuka'] . "</th>
					<th align=center hidden>" . $_SESSION['lang']['noakun'] . " " . $_SESSION['lang']['uangmuka'] . "</th>
					
					
					<th align=center rowspan=2>" . $_SESSION['lang']['action'] . "</th>";
		$tab .= "</tr>";
		// $tab.="<tr class=rowheader>
		// <td align=center>".$_SESSION['lang']['nilai']." DPP</td>
		// <td align=center>".$_SESSION['lang']['ppn']."</td>
		// <td align=center>".$_SESSION['lang']['pph']."</td>
		// <td align=center>".$_SESSION['lang']['notadebet']."</td>
		// <td align=center>".$_SESSION['lang']['uangmuka']."</td>
		// <td align=center>Retensi</td>
		// <td align=center>".$_SESSION['lang']['total']."</td>";
		// $tab.="</tr>";
		$tab .= "</thead>";
		$tab .= "<tbody id=formpencariantampil></tbody>";
		$tab .= "</table>";
		$tab .= "</div>";


		echo $tab;
		break;



	case 'getapmasuk':

		// $str = "select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."' ";
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// $kodept=$bar['induk'];
		// }

		// $str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)='4' and induk='".$kodept."' ";
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// $optunitap.="<option value='".$bar['kodeorganisasi']."'>".$bar['induk']." - ".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		// }

		$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)='4'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optunitap .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['induk'] . " - " . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}

		// $opttipeinvoice="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

		$str = "select * from " . $dbname . ".keu_5jenistagihan  where kode in ('rtg','rtn')";
		$res = fetchData($str);
		foreach ($res as $bar) {
			@$opttipeinvoice .= "<option value='" . $bar['kode'] . "'>" . $bar['kode'] . " - " . $bar['namajenis'] . "</option>";
		}


		$tab .= "<fieldset><legend>" . $_SESSION['lang']['form'] . "</legend>";
		$tab .= "<table>";
		$tab .= "<tr>
					<td>" . $_SESSION['lang']['noinvoice'] . "</td>
					<td>:</td>
					<td><input type=text id=noinvoiceap  size=50 class=myinputtext style=\"width:150px;\"></td>
					
					<td>" . $_SESSION['lang']['unit'] . "</td>
					<td>:</td>
					<td><select  class=select2 id=kodeorgap  style=\"width:154px;\">'" . $optunitap . "'</select>
					<img hidden id=kodeorgap onclick=z.elSearch('kodeorgap',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
		
					<td>" . $_SESSION['lang']['tipeinvoice'] . "</td>
					<td>:</td>
					<td><select  class=select2 id=tipeinvoiceap  style=\"width:154px;\">'" . $opttipeinvoice . "'</select>
					<img hidden id=tipeinvoiceap onclick=z.elSearch('tipeinvoiceap',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
		
					<td>" . $_SESSION['lang']['nilaiinvoice'] . "</td>
					<td>:</td>		
					<td><input  class=myinputtextnumber id=nilaiinvoiceap onkeyup=z.numberFormat('nilaiinvoiceap',2); style=\"width:150px;\" onkeypress='return angka_doang(event)' /></td>";
		$tab .= "</tr>";
		$tab .= "<tr>
					<td>" . $_SESSION['lang']['noinvoicesupplier'] . "</td>
					<td>:</td>
					<td><input type=text id=noinvoicesupplierap  size=50 class=myinputtext style=\"width:150px;\"></td>
					
					<td>" . $_SESSION['lang']['nodok'] . "</td>
					<td>:</td>
					<td><input type=text id=nodokap size=50 class=myinputtext style=\"width:150px;\"></td>
					<td>" . $_SESSION['lang']['supplier'] . "</td>
					<td>:</td>
					<td><select class=select2 id=kodesupplierap  style=\"width:154px;\">'" . $optsupplier . "'</select>
					<img  hidden  id=kodesupplierap onclick=z.elSearch('kodesupplierap',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>";
		$tab .= "</tr>";
		$tab .= "<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=findap()>" . $_SESSION['lang']['find'] . "</button></td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</fieldset>";
		$tab .= "<br>";

		// $tab.="<button class=mybutton onclick=saveap()>AP</button>&nbsp;";
		$tab .= " <div class=table-scroll style='height:280px'>";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		$tab .= "<thead><tr class=rowheader>
					<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center>" . $_SESSION['lang']['noinvoice'] . "</th>
					<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center>" . $_SESSION['lang']['unit'] . "</th>
					<th align=center>" . $_SESSION['lang']['tipeinvoice'] . "</th>
					<th align=center>" . $_SESSION['lang']['supplier'] . "</th>
					<th align=center>" . $_SESSION['lang']['nodok'] . "</th>
					<th align=center>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
					<th align=center>" . $_SESSION['lang']['nilai'] . "</th>
					<th align=center>" . $_SESSION['lang']['ppn'] . "</th>
					<th align=center>" . $_SESSION['lang']['pph'] . "</th>
					<th align=center>" . $_SESSION['lang']['total'] . "</th>
					<th align=center>" . $_SESSION['lang']['kasbank'] . "</th>
					<th align=center>" . $_SESSION['lang']['sisa'] . "</th>
					<th align=center hidden>" . $_SESSION['lang']['uangmuka'] . "</th>
					<th align=center hidden>" . $_SESSION['lang']['noaruskas'] . " " . $_SESSION['lang']['uangmuka'] . "</th>
					<th align=center hidden>" . $_SESSION['lang']['noakun'] . " " . $_SESSION['lang']['uangmuka'] . "</th>
					
					
					<th align=center rowspan=2>" . $_SESSION['lang']['action'] . "</th>";
		$tab .= "</tr>";
		// $tab.="<tr class=rowheader>
		// <td align=center>".$_SESSION['lang']['nilai']." DPP</td>
		// <td align=center>".$_SESSION['lang']['ppn']."</td>
		// <td align=center>".$_SESSION['lang']['pph']."</td>
		// <td align=center>".$_SESSION['lang']['notadebet']."</td>
		// <td align=center>".$_SESSION['lang']['uangmuka']."</td>
		// <td align=center>Retensi</td>
		// <td align=center>".$_SESSION['lang']['total']."</td>";
		// $tab.="</tr>";
		$tab .= "</thead>";
		$tab .= "<tbody id=formpencariantampil></tbody>";
		$tab .= "</table>";
		$tab .= "</div>";


		echo $tab;
		break;


	case 'findap':
		// echo $param['noinvoiceap']._.$param['kodeorg'];

		$param['nilaiinvoiceap'] = str_replace(',', '', $param['nilaiinvoiceap']);
		$where = '';
		// $where="  and kodeorg = '".$kodept[$param['kodeorg']]."' ";
		if ($param['noinvoiceap'] != '') {
			$where .= "  and noinvoice like '%" . $param['noinvoiceap'] . "%' ";
		}
		if ($param['kodeorgap'] != '') {
			$where .= "  and unit = '" . $param['kodeorgap'] . "' ";
		}
		if ($param['nodokap'] != '') {
			$where .= "  and nopo like '%" . $param['nodokap'] . "%' ";
		}
		if ($param['kodesupplierap'] != '') {
			$where .= "  and kodesupplier = '" . $param['kodesupplierap'] . "' ";
		}
		if ($param['nilaiinvoiceap'] != '') {
			$where .= "  and nilaiinvoice = '" . $param['nilaiinvoiceap'] . "' ";
		}
		if ($param['noinvoicesupplierap'] != '') {
			$where .= "  and noinvoicesupplier = '" . $param['noinvoicesupplierap'] . "' ";
		}
		if ($param['tipeinvoiceap'] != '') {
			$where .= "  and tipeinvoice = '" . $param['tipeinvoiceap'] . "' ";
		}





		#= data buat tagihan
		$pt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $param['kodeorght'] . "'");
		$str = "select * from " . $dbname . ".keu_tagihanht where 1=1 " . $where . " and kodeorg='" . $pt[$param['kodeorght']] . "' order by noinvoice desc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrnoinvoice[$bar['noinvoice']] = $bar['noinvoice'];
			$tanggalinv[$bar['noinvoice']] = $bar['tanggal'];

			# Ini case KSP
			# Jadi pembayaran di Kanwil jika tidak ada HO
			# Palma beda
			// if($bar['tipeinvoice']=='pon' || $bar['tipeinvoice']=='p'){
			// 	$unitinv[$bar['noinvoice']]=getunitdata($bar['kodeorg'],$bar['unit'],'KANWIL');
			// 	if($unitinv[$bar['noinvoice']]=="") {
			// 		$unitinv[$bar['noinvoice']]=getunitdata($bar['kodeorg'],$bar['unit'],'HOLDING');
			// 	}
			// }else{
			// 	$unitinv[$bar['noinvoice']]=$bar['unit'];
			// }
			# End

			$unitinv[$bar['noinvoice']] = $bar['unit'];
			$tipeinv[$bar['noinvoice']] = $bar['tipeinvoice'];
			$noakuninv[$bar['noinvoice']] = $bar['noakun'];
			$nodokinv[$bar['noinvoice']] = $bar['nopo'];
			$kodesupplier[$bar['noinvoice']] = $bar['kodesupplier'];
			$postinginv[$bar['noinvoice']] = $bar['posting'];
			$keteranganinv[$bar['noinvoice']] = $bar['keterangan'];
			$noinvoicesupplier[$bar['noinvoice']] = $bar['noinvoicesupplier'];
			$tipearuskasht[$bar['noinvoice']] = $bar['tipearuskasht'];
			$jatuhtempo[$bar['noinvoice']] = $bar['jatuhtempo'];
			// $nilaidppinv[$bar['noinvoice']]=$bar['nilaiinvoice'];
		}

		// if($_SESSION['standard']['username']=='tim.owl3'){
		// echo $str;
		// }

		#= data buat tagihan dt
		// $str="select sum(nilai) as nilai,noaruskas,noinvoice,noakun from ".$dbname.".keu_tagihandt where 1=1 and 
		// noinvoice in ('".implode("','",$arrnoinvoice)."')
		// group by noinvoice,noaruskas";

		$str = "select sum(nilai) as nilai,noaruskas,noinvoice,noakun,tipearuskas from " . $dbname . ".keu_tagihandt where 1=1 and 
		noinvoice in ('" . implode("','", $arrnoinvoice) . "')
		group by noinvoice,noaruskas,noakun order by nilai desc";
		// echo $str;

		// $str="select * from ".$dbname.".keu_tagihandt where 1=1 and 
		// noinvoice in ('".implode("','",$arrnoinvoice)."')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if (substr($bar['noakun'], 0, 5) == '11801' and @$tipeinv[$bar['noinvoice']] != 'um') {
				continue;
			}
			$nopo[$bar['noinvoice']] = $bar['nopo'];
			$arrnoaruskas[$bar['noaruskas']] = $bar['noaruskas'];
			$arrnoinvoice[$bar['noinvoice']] = $bar['noinvoice'];
			$arrnoakun[$bar['noakun']] = $bar['noakun'];
			@$keterangan3[$bar['noinvoice']][$bar['noaruskas']][$bar['noakun']] = 'DPP';
			$listdatadetail[$bar['noinvoice']][$bar['noaruskas']][$bar['noakun']] = $bar['noakun'];
			$listdatadetailtipearuskas[$bar['noinvoice']][$bar['noaruskas']][$bar['noakun']] = $bar['tipearuskas'];
			// $listdatadetailnoakun[$bar['noinvoice']][$bar['noaruskas']][$bar['noakun']]=$bar['noaruskas'];
			// if($bar['noakun']=='1170111'){
			if ($bar['noakun'] == '1160101') {
				@$nilaippn[$bar['noinvoice']] += $bar['nilai'];
				@$keterangan3[$bar['noinvoice']][$bar['noaruskas']][$bar['noakun']] = 'PAJAKPPN';
				// } else if(substr($bar['noakun'],0,5)=='21301'){
			} else if (substr($bar['noakun'], 0, 5) == '21202') {
				if ($tipeinv[$bar['noinvoice']] == 'p' || $tipeinv[$bar['noinvoice']] == 'pon' || $tipeinv[$bar['noinvoice']] == 'ot') {
					# Plus kan
					# Di akuin Hutang PPh di Tagihan
					# PPh di bayarkan oleh Kebun ke Negara
					// @$nilaipph[$bar['noinvoice']]+=($bar['nilai']*-1);
					// $bar['nilai'] = ($bar['nilai'] * -1);
				}

				@$nilaipph[$bar['noinvoice']] += $bar['nilai'];
				@$keterangan3[$bar['noinvoice']][$bar['noaruskas']][$bar['noakun']] = 'PAJAKPPH';
			} else {
				@$nilaidppinv[$bar['noinvoice']] += $bar['nilai'];
			}

			@$nilaidppdetail[$bar['noinvoice']][$bar['noaruskas']][$bar['noakun']] += $bar['nilai'];
			@$nilaiinv[$bar['noinvoice']] += $bar['nilai'];
			@$tnodt[$bar['noinvoice']] += 1;
		}


		// echo "<pre>";
		// print_r($nilaiinv);	
		$str = "select sum(jumlah) as jumlah,keterangan1,noaruskas,noakuninvoice,tipetransaksi from " . $dbname . ".keu_kasbankdt 
			where keterangan1 in ('" . implode("','", $arrnoinvoice) . "') group by keterangan1,noaruskas,noakuninvoice,tipetransaksi";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($bar['tipetransaksi'] == 'M') {
				$bar['jumlah'] = $bar['jumlah'] * -1;
			}

			if (substr($bar['noakuninvoice'], 0, 5) != '21202') {
				@$nilaikb[$bar['keterangan1']] += $bar['jumlah'];
				@$nilaikbdetail[$bar['keterangan1']][$bar['noaruskas']][$bar['noakuninvoice']] += $bar['jumlah'];
			}
		}

		// echo"<pre>";
		// print_r($nilaikbdetail);
		// echo"</pre>";

		#= data uang muka
		// $str="select sum(jumlah) as jumlah,nodok,noaruskas,noakun from ".$dbname.".keu_kasbankdt 
		// 	where nodok in ('".implode("','",$nodokinv)."') and left(noakun,5)='11803' group by nodok";
		$str = "select sum(jumlah) as jumlah,nodok,noaruskas,noakun from " . $dbname . ".keu_kasbankdt 
			where nodok in ('" . implode("','", $nodokinv) . "') and left(noakun,5)='11801' group by nodok";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			// @$nilaiuangmuka[$bar['nodok']]+=$bar['jumlah'];
			// @$noaruskasuangmuka[$bar['nodok']]=$bar['noaruskas'];
			// @$noakunuangmuka[$bar['nodok']]=$bar['noakun'];

			@$nilaiuangmuka[$bar['nodok']] += 0;
			@$noaruskasuangmuka[$bar['nodok']] = 0;
			@$noakunuangmuka[$bar['nodok']] = 0;
		}

		$noht = 0;
		foreach ($arrnoinvoice as $noinvoice) {
			// @$sisainv[$noinvoice] = ($nilaiinv[$noinvoice] - $nilaipph[$noinvoice]) - $nilaikb[$noinvoice] - $nilaiuangmuka[$nodokinv[$noinvoice]];
			@$sisainv[$noinvoice] = ($nilaiinv[$noinvoice]) - $nilaikb[$noinvoice] - $nilaiuangmuka[$nodokinv[$noinvoice]];
			// exit("Warning: " . $nilaiinv[$noinvoice] . " - " . $nilaikb[$noinvoice] . " - " . $nilaiuangmuka[$nodokinv[$noinvoice]]);
			# Nilai DPP di kurangi dengan Nilai PPh
			# Nilai invoice di kurangi dengan Nilai PPh
			// $nilaidppinv[$noinvoice] -= $nilaipph[$noinvoice];
			// $nilaiinv[$noinvoice] -= $nilaipph[$noinvoice];

			// if($tipeinv[$noinvoice]=='rtn' || $tipeinv[$noinvoice]=='rtg'){
			// @$sisainv[$noinvoice]=$nilaiinv[$noinvoice]-$nilaikb[$noinvoice];					
			// }else{
			// @$sisainv[$noinvoice]=$nilaiinv[$noinvoice]-$nilaikb[$noinvoice];
			// }
			/*
			if($sisainv[$noinvoice]<0){
				continue;
			}
			*/


			// if ($sisainv[$noinvoice]!=0) {
			$noht++;
			$showhide = "style='cursor:pointer' title='lihat detail data' onclick=showdetail('" . @$noht . "','" . @$tnodt[$noinvoice] . "')";
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=left " . $showhide . ">" . $noht . "</td>";
			$tab .= "<td " . $showhide . ">" . $noinvoice . "<br><i><b>" . @$noinvoicesupplier[$noinvoice] . "</b><i></td>";
			$tab .= "<td " . $showhide . " nowrap>" . tanggalnormal($tanggalinv[$noinvoice]) . " </td>";
			$tab .= "<td " . $showhide . ">" . $unitinv[$noinvoice] . "<br>" . $nmorganisasi[$unitinv[$noinvoice]] . " </td>";
			$tab .= "<td " . $showhide . ">" . $arrtipeinvoice[$tipeinv[$noinvoice]] . "</td>";
			$tab .= "<td " . $showhide . ">" . $nmsupplier[$kodesupplier[$noinvoice]] . "</td>";
			$tab .= "<td " . $showhide . ">" . $nodokinv[$noinvoice] . "</td>";
			$tab .= "<td " . $showhide . ">" . $tipearuskasht[$noinvoice] . "</td>";
			$tab .= "<td " . $showhide . ">" . $noakuninv[$noinvoice] . "</td>";
			$tab .= "<td " . $showhide . ">" . $nmakun[$noakuninv[$noinvoice]] . "</td>";
			$tab .= "<td " . $showhide . " colspan=2>" . @$keteranganinv[$noinvoice] . "</td>";
			$tab .= "<td " . $showhide . ">" . tanggalnormal($jatuhtempo[$noinvoice]) . "</td>";
			$tab .= "<td " . $showhide . " align=right>" . @number_format($nilaidppinv[$noinvoice], 2) . "</td>";
			$tab .= "<td " . $showhide . " align=right>" . @number_format($nilaippn[$noinvoice], 2) . "</td>";
			$tab .= "<td " . $showhide . " align=right>" . @number_format($nilaipph[$noinvoice], 2) . "</td>";
			$tab .= "<td " . $showhide . " align=right>" . @number_format($nilaiinv[$noinvoice], 2) . "</td>";
			$tab .= "<td " . $showhide . " align=right>" . @number_format($nilaikb[$noinvoice], 2) . "</td>";
			$tab .= "<td " . $showhide . " align=right>" . @number_format($sisainv[$noinvoice], 2) . "</td>";
			// $tab.="<td align=right>".number_format($sisa,2)."</td>";
			// $tab.="<td align=center><img src=images/save.png class=zImgBtn caption='Save' 
			// onclick=\"saveap('".$bar['unit']."','".$bar['noakun']."','".$bar['noinvoice']."','".$bar['nopo']."',
			// '".$bar['kodesupplier']."','".$sisa."');\"></td>";	

			#= validasi check data
			$keterangan[$noinvoice] = '';
			if ($postinginv[$noinvoice] == 0) {
				$keterangan[$noinvoice] = 'Invoice AP belum diposting';
			}

			if ($tipeinv[$noinvoice] == 'rtn' || $tipeinv[$noinvoice] == 'rtg') {
				if ($sisainv[$noinvoice] == 0) {
					$keterangan[$noinvoice] = 'Invoice AP sudah sepenuhnya ditarik transaksi kas/bank';
				}
				if (@$nilaidppinv[$noinvoice] == 0 and @$nilaippn[$noinvoice] == 0 and @$nilaipph[$noinvoice] == 0) {
					$keterangan[$noinvoice] = 'Nilai Invoice AP 0 (tidak ada nilai)';
				}
			} else {
				if ($sisainv[$noinvoice] < 1) {
					$keterangan[$noinvoice] = 'Invoice AP sudah sepenuhnya ditarik transaksi kas/bank';
				}
				if (@$nilaidppinv[$noinvoice] < 1 and @$nilaippn[$noinvoice] < 1 and @$nilaipph[$noinvoice] < 1) {
					$keterangan[$noinvoice] = 'Nilai Invoice AP 0 (tidak ada nilai)';
				}
			}



			$tab .= "<td " . $showhide . " align=right hidden>" . @number_format($nilaiuangmuka[$nodokinv[$noinvoice]], 2) . "</td>";
			$tab .= "<td " . $showhide . " align=right hidden>" . @$noaruskasuangmuka[$nodokinv[$noinvoice]] . "</td>";
			$tab .= "<td " . $showhide . " align=right hidden>" . @$noakunuangmuka[$nodokinv[$noinvoice]] . "</td>";
			if ($keterangan[$noinvoice] == '') {
				// $tab.="<tr class=rowcontent row".$no." onclick=saveap(".$no.") style='cursor:pointer' title='add detail'>";
				$tab .= "<td align=center><img src=images/save.png class=zImgBtn caption='Save' 
								onclick=\"saveap('" . $unitinv[$noinvoice] . "','" . $noakuninv[$noinvoice] . "','" . $noinvoice . "','" . $nodokinv[$noinvoice] . "',
								'" . $kodesupplier[$noinvoice] . "','" . $noht . "','" . $tnodt[$noinvoice] . "',
								'" . @$noaruskasuangmuka[$nodokinv[$noinvoice]] . "','" . @$noakunuangmuka[$nodokinv[$noinvoice]] . "','" . @$nilaiuangmuka[$nodokinv[$noinvoice]] . "','" . $sisainv[$noinvoice] . "','" . $tipearuskasht[$noinvoice] . "');\"></td>";
			} else {
				$tab .= "<td bgcolor=red>" . $keterangan[$noinvoice] . "</td>";
			}
			$tab .= "</tr>";
			// }
			// print_r($keterangan3);
			$nodt = 0;
			foreach (@$arrnoaruskas as $noaruskas) {
				foreach ($arrnoakun as $noakun) {
					if (@$listdatadetail[$noinvoice][$noaruskas][$noakun] != '') {
						$nodt++;
						$tab .= "<tr class=rowcontent id=detaildataap" . $noht . "" . $nodt . "  " . $stylehidden . ">";
						$niluangmukanya = 0;
						if ($keterangan3[$noinvoice][$noaruskas][$noakun] == 'DPP') {
							if ($nilaiuangmuka[$nodokinv[$noinvoice]] > 0) {
								$niluangmukanya = $nilaiuangmuka[$nodokinv[$noinvoice]];
							}
						}

						# Jika Akun adalah
						# Hutang Supplier Lokal
						# Hutang Jasa
						# Maka kurangi dengan Nilai PPH 22
						# Karena langsung di bayar ke Negara
						if ($noakun == '2111101' || substr($noakun, 0, 5) == '21113' || substr($noakun, 0, 5) == '21112') {
							$nilaidppdetail[$noinvoice][$noaruskas][$noakun] -= $nilaipph[$noinvoice];
						} else if ($tipeinv[$noinvoice] == 'pon' && substr($noakun, 0, 5) != '21202' && substr($noakun, 0, 3) != '116') {
							// $nilaidppdetail[$noinvoice][$noaruskas][$noakun] += $nilaipph[$noinvoice];
						}

						$tab .= "<td bgcolor=#ccff66 align=left>" . $noht . "." . $nodt . "</td>";
						$tab .= "<td bgcolor=#ccff66 colspan=5>Detail Transaksi Invoce AP " . $noinvoice . "</td>";
						$tab .= "<td bgcolor=#ccff66 id=keterangan3" . $noht . "" . $nodt . ">" . $keterangan3[$noinvoice][$noaruskas][$noakun] . "</td>";
						$tab .= "<td bgcolor=#ccff66 id=tipearuskasdt" . $noht . "" . $nodt . " align=center>" . $listdatadetailtipearuskas[$noinvoice][$noaruskas][$noakun] . "</td>";
						$tab .= "<td bgcolor=#ccff66 id=noakuninvoice" . $noht . "" . $nodt . " align=right>" . $noakun . "</td>";
						$tab .= "<td bgcolor=#ccff66>" . $nmakun[$noakun] . "</td>";
						$tab .= "<td bgcolor=#ccff66 id=noaruskas" . $noht . "" . $nodt . ">" . $noaruskas . "</td>";
						$tab .= "<td bgcolor=#ccff66>" . $nmaruskas[$noaruskas] . "</td>";
						$tab .= "<td bgcolor=#ccff66 align=right>" . @number_format($nilaidppdetail[$noinvoice][$noaruskas][$noakun], 2) . "</td>";
						$tab .= "<td bgcolor=#ccff66 align=right colspan=3></td>";
						#= proporsi sudah dibuat transaksi kasbank
						// $nilaikbdetail[$noinvoice][$noaruskas]=0;
						$tab .= "<td bgcolor=#ccff66 align=right>" . @number_format($nilaikbdetail[$noinvoice][$noaruskas][$noakun], 2) . "</td>";
						// @$sisadetail[$noinvoice][$noaruskas]=$nilaiinvdetail[$noinvoice][$noaruskas]-$nilaikbdetail[$noinvoice][$noaruskas];
						@$sisadetail[$noinvoice][$noaruskas][$noakun] = $nilaidppdetail[$noinvoice][$noaruskas][$noakun] - $nilaikbdetail[$noinvoice][$noaruskas][$noakun] - $niluangmukanya;
						$tab .= "<td bgcolor=#ccff66 id=sisadetail" . $noht . "" . $nodt . " align=right>" . @number_format($sisadetail[$noinvoice][$noaruskas][$noakun], 2) . "</td>";
						$tab .= "<td bgcolor=#ccff66 hidden></td>";
						$tab .= "<td bgcolor=#ccff66 hidden></td>";
						$tab .= "<td bgcolor=#ccff66 hidden></td>";
						$tab .= "<td bgcolor=#ccff66></td>";
						$tab .= "</tr>";
					}
				}
			}
		}
		echo $tab;
		break;


	case 'saveap':
		try {
			$owlPDO->beginTransaction();
			#= ambil data ht untuk di dt
			$str = "select * from " . $dbname . "." . $table . " where  notransaksi='" . $param['notransaksi'] . "'";
			$res = fetchdata($str);
			$param['tanggal'] = $res[0]['tanggal'];
			$param['kurs'] = $res[0]['kurs'];
			$param['matauang'] = $res[0]['matauang'];
			$param['tipetransaksi'] = $res[0]['tipetransaksi'];
			$param['noakun'] = $res[0]['noakun'];
			$param['kodeorg'] = $res[0]['kodeorg'];

			$explnotransaksi = explode('/', $param['notransaksi']);
			$param['kode'] = $explnotransaksi[2];

			#= ambil nomor max
			/*
			$str = "select max(nourut) as nourut from ".$dbname.".".$tabledt." where  notransaksi='".$param['notransaksi']."'";
			$res=fetchdata($str);
			$param['nourut']= $res[0]['nourut']+1;
			*/
			$param['nourut'] = '';

			$param['hutangunit1'] = 1;
			if ($param['pemilikhutang1'] == $param['kodeorg']) {
				$param['hutangunit1'] = '0';
				$param['pemilikhutang1'] = '';
			}

			#= Buat keterangan
			$str = "select keterangan, noinvoicesupplier, tipeinvoice from " . $dbname . ".keu_tagihanht where  noinvoice='" . $param['keterangan1'] . "'";
			$res = fetchdata($str);

			if ($res[0]['tipeinvoice'] == 'pon' || $res[0]['tipeinvoice'] == 'p') {
				$text = '';

				# tagihandt
				$strdt = "select * from " . $dbname . ".keu_tagihandt where noinvoice='" . $param['keterangan1'] . "' and kelompokbarang <> ''";
				$resdt = fetchdata($strdt);

				# Pisahkan kelompokbarang
				$klbarangjasaangkut = $resdt[0]['kelompokbarang'];

				if ($res[0]['tipeinvoice'] == 'p') {
					$text = 'PO';
				} else if ($res[0]['tipeinvoice'] == 'pon') {
					if ($klbarangjasaangkut == '800' || $klbarangjasaangkut == '801') { # Cek Khusus Barang Jasa Angkut
						$text = 'Ongkos Transportasi Darat ';
					} else {
						$text = 'PO';
					}
				}

				$param['keterangan2'] = $res[0]['keterangan'] . ' Pembayaran ' . $text . ' No. ' . $param['nodok'] . ' ke ' . $nmsupplier[$param['kodesupplier']] . ', Invoice No. ' . $res[0]['noinvoicesupplier'];
			} else {
				$param['keterangan2'] = $res[0]['keterangan'] . ', Assignment : ' . $nmsupplier[$param['kodesupplier']] . ', No. Dok : ' . $param['nodok'] . ', Inv : ' . $res[0]['noinvoicesupplier'];
			}
			#= End Keterangan

			for ($i = 1; $i <= $param['maxrow']; $i++) {
				#= buat keterangan

				$param['jumlahdt'][$i] = str_replace(',', '', $param['jumlahdt'][$i]);

				if ($param['tipetransaksi'] == 'M') {
					$param['jumlahdt'][$i] = $param['jumlahdt'][$i] * -1;
				}

				# Bedakan kalau dia PPH,
				# Dan Tipe PO dan PO Non Inventory
				// if($param['noakuninvoice'][$i]=='2120201' && ($res[0]['tipeinvoice'] == 'pon' || $res[0]['tipeinvoice'] == 'p')) {}
				# Di Insert Bawah kondisinya

				if (abs($param['jumlahdt'][$i]) > 0) {
					// Jika PPN maka kolom nomor akun diisi noakuninvoice karena jika bukan nanti di kasbanknya pakai hutang supplier atau sesuai dengan noakun header tagihan
					// $noakunHead = $param['noakundt'];
					// $noaruskasHead = $param['noaruskasdt'];
					// if (substr($param['noakuninvoice'][$i], 0, 5) == "11601") {
					// 	$noakunHead = $param['noakuninvoice'][$i];
					// 	$noaruskasHead = $param['noaruskas'][$i];
					// }

					$noakunHead = $param['noakundt'];
					$noaruskasHead = $param['noaruskas'][$i];
					// if ($param['noakuninvoice'][$i] == '2120201' && ($res[0]['tipeinvoice'] == 'pon' || $res[0]['tipeinvoice'] == 'p')) {

					# Kas Bank tidak pakai pph lagi
					# Notulen Excel Terakhir Palma
						// if ($param['noakuninvoice'][$i] == '2120201') {
						// 	$noakunHead = $param['noakuninvoice'][$i] ?? $param['noakundt'];
						// }
					# End

					$str = "insert into " . $dbname . "." . $tabledt . "
					(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
					noakun2a,kode,keterangan1,keterangan2,matauang,
					kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
					kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
					orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,
					keterangan3,noakuninvoice,tipearuskas) 
					values 
					('" . $param['notransaksi'] . "',
					'" . $noakunHead . "',
					'" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . $param['jumlahdt'][$i] . "',
					'" . $param['noakun'] . "','" . $param['kode'] . "','" . $param['keterangan1'] . "','" . $param['keterangan3'][$i] . " " . $param['keterangan2'] . "','" . $param['matauang'] . "',
					'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $noaruskasHead . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
					'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
					'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','" . $param['nourut'] . "',
					'" . $param['keterangan3'][$i] . "','" . $param['noakuninvoice'][$i] . "','" . $param['tipearuskasdt'][$i] . "')";
					$owlPDO->exec($str);
				}
			}
			/*
			#= buat insert dokumen upload jika ada
			$pathtagihan   = "filegis/";
			// $pathkb   = "fileupload/keu_kasbankx/";
			$str = "select * from ".$dbname.".listfileupload where notransaksi='".$param['keterangan1']."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$strins = "insert into ".$dbname.".listfileupload values ('','".$param['notransaksi']."','EAP_".$bar['namafile']."','".$bar['formaticon']."','EAP','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
				$owlPDO->exec($strins);
				#= move file
				$source=$pathtagihan.$bar['namafile'];
				$destination=$path.'EAP_'.$bar['namafile'];;
				if( !copy($source, $destination) ) { 
					exit("Warningsistem:gagal upload otomatis dari AP");
				} 
			}
			*/

			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warning: Gagal melakukan penyimpanan data \n" . addslashes($e->getMessage());
		}

		$str = "select bayarkepada from " . $dbname . ".keu_kasbankht where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$bayarkepada = $bar['bayarkepada'];
		}

		if ($bayarkepada == '' || $bayarkepada == '-') {
			$str = "update " . $dbname . ".keu_kasbankht set bayarkepada='" . $nmsupplier[$param['kodesupplier']] . "' where notransaksi='" . $param['notransaksi'] . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}

		echo $nmsupplier[$param['kodesupplier']];



		break;



	/*
	A
	R
	*/

	case 'getar':

		$tab .= "<fieldset><legend>" . $_SESSION['lang']['form'] . "</legend>";
		$tab .= "<table>";
		$tab .= "<tr>
					<td>" . $_SESSION['lang']['noinvoice'] . "</td>
					<td>:</td>
					<td><input type=text id=noinvoicear size=50 class=myinputtext style=\"width:150px;\"></td>
					<td>" . $_SESSION['lang']['NoKontrak'] . "</td>
					<td>:</td>
					<td><input type=text id=nokontrakar  size=50 class=myinputtext style=\"width:150px;\"></td>
					
					<td>" . $_SESSION['lang']['customer'] . "</td>
				<td>:</td>		
				<td>
					<select id=kodecustomerar  style=\"width:154px;\">'" . $optcustomer . "'</select>
				</td>
				</tr>";


		// $tab.="<tr>
		// <td>".$_SESSION['lang']['noinvoice']."</td>
		// <td>:</td>
		// <td><input type=text id=noinvoiceap size=50 class=myinputtext style=\"width:150px;\"></td>";
		// $tab.="</tr>";
		$tab .= "<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=findar()>" . $_SESSION['lang']['find'] . "</button></td>";
		$tab .= "</tr>";

		$tab .= "</table>";
		$tab .= "</fieldset>";
		$tab .= "<br>";
		$tab .= "<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>";
		// $tab.="<button class=mybutton onclick=saveap()>AP</button>&nbsp;";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		$tab .= "<thead><tr class=rowheader>
					<th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['unit'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['tipeinvoice'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['invoice'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['noaruskas'] . "<br>" . $_SESSION['lang']['piutang'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['noakun'] . "<br>" . $_SESSION['lang']['piutang'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['noaruskas'] . "<br>PPn</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['noakun'] . "<br>PPn</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['noaruskas'] . "<br>PPh</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['noakun'] . "<br>PPh</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['noinvoice'] . " &<br>" . $_SESSION['lang']['noinvoice'] . "<br>" . $_SESSION['lang']['supplier'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['nodok'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['customer'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['kodebarang'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['jatuhtempo'] . "</th>
					<th align=center colspan=6>" . $_SESSION['lang']['piutang'] . "</th>
					<th align=center colspan=4>" . $_SESSION['lang']['total'] . "<br>" . $_SESSION['lang']['masuk'] . "</th>
					<th align=center colspan=4>" . $_SESSION['lang']['sisa'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['action'] . "</th>
					 <th align=center rowspan=2>" . $_SESSION['lang']['createby'] . "</th> 
					 <th align=center rowspan=2>" . $_SESSION['lang']['createtime'] . "</th> 
					 <th align=center rowspan=2>" . $_SESSION['lang']['updateby'] . "</th> 
					 <th align=center rowspan=2>" . $_SESSION['lang']['updatetime'] . "</th> 
					";
		$tab .= "</tr>";
		$tab .= "<tr class=rowheader>
					<th align=center>" . $_SESSION['lang']['nilai'] . " DPP</th>
					<th align=center>" . $_SESSION['lang']['klaim'] . " DPP</th>
					<th align=center>" . $_SESSION['lang']['total'] . " DPP</th>
					<th align=center>" . $_SESSION['lang']['ppn'] . "</th>
					<th align=center>" . $_SESSION['lang']['pph'] . "</th>
					<th align=center>" . $_SESSION['lang']['total'] . "</th>
					
					<th align=center>" . $_SESSION['lang']['nilai'] . " DPP</th>
					<th align=center>" . $_SESSION['lang']['ppn'] . "</th>
					<th align=center>" . $_SESSION['lang']['pph'] . "</th>
					<th align=center>" . $_SESSION['lang']['total'] . "</th>
					
					<th align=center>" . $_SESSION['lang']['nilai'] . " DPP</th>
					<th align=center>" . $_SESSION['lang']['ppn'] . "</th>
					<th align=center>" . $_SESSION['lang']['pph'] . "</th>
					<th align=center>" . $_SESSION['lang']['total'] . "</th>
					
					
					";
		$tab .= "</tr>";
		$tab .= "</thead>";
		$tab .= "<tbody id=formpencariantampil></tbody>";
		$tab .= "</table>";
		$tab .= "</fieldset>";

		echo $tab;
		break;


	case 'findar':
		// echo $param['noinvoiceap']._.$param['kodeorg'];
		$where = "  and kodept = '" . $kodept[$param['kodeorg']] . "'";
		if ($param['noinvoicear'] != '') {
			$where .= "  and noinvoice like '%" . $param['noinvoicear'] . "%' ";
		}
		if ($param['kodecustomerar'] != '') {
			$where .= "  and kodecustomer='" . $param['kodecustomerar'] . "' ";
		}
		if ($param['nokontrakar'] != '') {
			$where .= "  and nokontrak like '%" . $param['nokontrakar'] . "%' ";
		}

		$no = 0;
		$str = "select * from " . $dbname . ".keu_penagihanht where tipeinvoice!='PI'  " . $where . " order by tanggal desc";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {

			#=datakaryawan
			$strdt = "select namakaryawan,karyawanid from " . $dbname . ".datakaryawan where karyawanid in ('" . $bar['createby'] . "','" . $bar['updateby'] . "') ";
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				$namakaryawan[$bardt['karyawanid']] = $bardt['namakaryawan'];
			}


			$bar['tipeinvoice'] = 'CIPP';
			if ($bar['kodebarang'] == '40000003') {
				$bar['tipeinvoice'] = 'CITBS';
			}
			if ($bar['kodebarang'] == '40000033') {
				$bar['tipeinvoice'] = 'FEM';
			}


			$no++;

			$nilaitotalinv = $jumlahkb = 0;

			if ($bar['tipeinvoice'] == 'ND') {
				$bar['nilaiinvoice'] = 0;
			}


			$jumlahkb = $jumlahkbppn = $jumlahtotalkb = $jumlahkbgabungan = 0;
			#= Setup khusus per PT (keu_5setuppenagihanpt) - kalau aktif, alokasi kasbank invoice ini
			#= dicatat gabungan (tag 'GABUNGAN'), jadi perhitungan sisa di bawah juga harus digabung
			$gabungPiutangKB2 = '0';
			$strSetupOrgKB2 = "select gabungpiutang from " . $dbname . ".keu_5setuppenagihanpt where kodept='" . $bar['kodept'] . "' and (kodejenis='CIPP' or kodejenis='CITBS') and kodebarang='" . $bar['kodebarang'] . "'";
			$resSetupOrgKB2 = fetchdata($strSetupOrgKB2);
			if (!empty($resSetupOrgKB2)) {
				$gabungPiutangKB2 = $resSetupOrgKB2[0]['gabungpiutang'];
			}

			// $strdt = "select sum(jumlah) as jumlah,keterangan3,notransaksi from " . $dbname . ".keu_kasbankdt where keterangan1='" . $bar['noinvoice'] . "' and keterangan3 NOT LIKE '%KAS%' group by keterangan3";
			$strdt = "select sum(jumlah) as jumlah,keterangan3,notransaksi from " . $dbname . ".keu_kasbankdt where keterangan1='" . $bar['noinvoice'] . "' group by keterangan3";
			// echo $strdt;
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				if ($bardt['keterangan3'] == 'DPP') {
					$jumlahkb = $bardt['jumlah'];
				}
				if ($bardt['keterangan3'] == 'PAJAKPPN') {
					$jumlahkbppn = $bardt['jumlah'];
				}
				if ($bardt['keterangan3'] == 'PAJAKPPH') {
					$jumlahkbpph = ($bardt['jumlah'] * -1);
				}
				if ($bardt['keterangan3'] == 'GABUNGAN') {
					$jumlahkbgabungan = $bardt['jumlah'];
				}
				@$jumlahtotalkb += $bardt['jumlah'];
				@$notransaksikb = $bardt['notransaksi'];
			}

			// echo $jumlahkb;

			#= klaim
			$nilaiklaim = floatval($bar['rupiah1'] + $bar['rupiah2'] + $bar['rupiah3'] + $bar['rupiah4'] + $bar['rupiah5'] + $bar['rupiah6'] + $bar['rupiah7'] - $bar['rupiah8']);
			// $nilaiklaim=0;
			#= jika ada klaim maka nilai invoice dan ppn berubah
			$nilaidppawal = $bar['nilaiinvoice'];
			#= nilai setelah claim
			$bar['nilaiinvoice'] = $bar['nilaiinvoice'] - $nilaiklaim;
			#= bentuk persen ppn 
			// echo $bar['nilaippn']."--".$bar['nilaiinvoice']."<br>";

			$persenppn = round($bar['nilaippn'] / $nilaidppawal, 2);

			//echo $persenppn;
			$bar['nilaippn'] = $bar['nilaippn'] - ($persenppn * $nilaiklaim);
			$nilaitotalinv = $bar['nilaiinvoice'] + $bar['nilaippn'] - $bar['nilaipph'];

			if ($gabungPiutangKB2 == '1') {
				$sisadpp = ($bar['nilaiinvoice'] + $bar['nilaippn'] - $bar['nilaipph']) - $jumlahkbgabungan;
				$sisappn = 0;
				$sisapph = 0;
				$sisatotal = $sisadpp;
			} else {
				$sisadpp = $bar['nilaiinvoice'] - $jumlahkb;
				$sisappn = $bar['nilaippn'] - $jumlahkbppn;
				$sisapph = ($bar['nilaipph'] * -1) + $jumlahkbpph;
				$sisatotal = $sisadpp + $sisappn - ($sisapph * -1);
			}



			#= validasi check data
			$keterangan = '';
			if ($bar['posting'] == 0) {
				$keterangan = 'Invoice AR belum diposting';
			}
			if ($sisadpp < 1 and $sisappn < 1) {
				$keterangan = 'Invoice sudah sepenuhnya ditarik transaksi kas/bank ' . $notransaksikb . ' ';
			}

			$strdt = "select * from " . $dbname . ".keu_5jenispenagihandt where kodejenis='" . $bar['tipeinvoice'] . "' and kodebarang='" . $bar['kodebarang'] . "'";
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				$noakunpiutang = $noakunpp = $bardt['noakunpiutang'];
				$noakunsales = $bardt['noakunsales'];
			}

			if ($bar['tipeinvoice'] == 'FEM' && $bar['kodebarang'] == '40000033') {
				$noakunpiutang = empty(fetchData(selectQuery($dbname, "setup_parameterappl", "nilai", "kodeparameter= CONCAT('MF_COA/', '" . $bar['kodecustomer'] . "')"))) ? exit("Warning : Akun piutang untuk customer " . $bar['kodecustomer'] . " belum disetup di parameter aplikasi") : fetchData(selectQuery($dbname, "setup_parameterappl", "nilai", "kodeparameter= CONCAT('MF_COA/', '" . $bar['kodecustomer'] . "')"))[0]['nilai'];
			}


			// noakunpiutang noakunppn
			#= aruskas
			// $strdt="select noaruskas,noakun from ".$dbname.".keu_5aruskas_detail where noakun ='".$noakunsales."'";
			$strdt = "select noaruskas,noakun from " . $dbname . ".keu_5aruskas_detail where noakun ='" . $noakunsales . "' and noaruskas in (select noaruskas from " . $dbname . ".keu_5aruskas where nama_aruskas not like '%SUBSIDIARI%' and tipetransaksi='M')";
			// echo $strdt;
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				$noaruskasdpp = $noaruskasppn = $noaruskaspph = $bardt['noaruskas'];
			}


			#= cek apakah customer merupakan afiliasi, dengan cara kodecustomer = organisasi, jika ada di organisasi maka lempar aruskas subsidiari
			$strdt = "select count(*) as jumlah from " . $dbname . ".organisasi where kodeorganisasi='" . $bar['kodecustomer'] . "' ";
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				$dtafiliasi = $bardt['jumlah'];
			}

			if ($dtafiliasi > 0) {
				$strdt = "select noaruskas,noakun from " . $dbname . ".keu_5aruskas_detail where noakun ='" . $noakunsales . "' and noaruskas in (select noaruskas from " . $dbname . ".keu_5aruskas where nama_aruskas like '%SUBSIDIARI%' and tipetransaksi='M')";
				// echo $strdt;
				$resdt = fetchdata($strdt);
				foreach ($resdt as $bardt) {
					$noaruskasdpp = $noaruskasppn = $noaruskaspph = $bardt['noaruskas'];
				}
			}


			// if($sisatotal!=0)}
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td>" . $bar['tanggal'] . "</td>";
			$tab .= "<td>" . $bar['kodeorg'] . "</td>";
			$tab .= "<td>" . $bar['tipeinvoice'] . "</td>";
			$tab .= "<td>" . $bar['jenisinvoice'] . "</td>";
			$tab .= "<td>" . $noaruskasdpp . "<br>" . $nmaruskas[$noaruskasdpp] . "</td>";
			$tab .= "<td>" . $noakunpiutang . "<br>" . $nmakun[$noakunpiutang] . "</td>";
			$tab .= "<td>" . $noaruskasppn . "<br>" . $nmaruskas[$noaruskasppn] . "</td>";
			$tab .= "<td>" . $noakunpiutang . "<br>" . $nmakun[$noakunpiutang] . "</td>";
			$tab .= "<td>" . $noaruskasppn . "<br>" . $nmaruskas[$noaruskaspph] . "</td>";
			$tab .= "<td>" . $noakunpiutang . "<br>" . $nmakun[$noakunpiutang] . "</td>";
			$tab .= "<td>" . $bar['noinvoice'] . "<br>" . $bar['noinvoicesupplier'] . "</td>";
			$tab .= "<td>" . $bar['nokontrak'] . "</td>";
			$tab .= "<td>" . $bar['kodecustomer'] . "<br>" . $namacustomer . "</td>";
			$tab .= "<td>" . $bar['kodebarang'] . " " . $nmbarangpabrik[$bar['kodebarang']] . "</td>";
			$tab .= "<td>" . tanggalnormal($bar['jatuhtempo']) . "</td>";

			$tab .= "<td align=right>" . hidezerodecimal($nilaidppawal, 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($nilaiklaim, 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($bar['nilaiinvoice'], 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($bar['nilaippn'], 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($bar['nilaipph'], 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($nilaitotalinv, 2) . "</td>";

			$tab .= "<td align=right>" . hidezerodecimal($jumlahkb, 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($jumlahkbppn, 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($jumlahkbpph, 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($jumlahtotalkb, 2) . "</td>";

			$tab .= "<td align=right>" . hidezerodecimal($sisadpp, 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($sisappn, 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($sisapph, 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($sisatotal, 2) . "</td>";

			if ($keterangan == '') {
				// $tab.="<tr class=rowcontent row".$no." onclick=saveap(".$no.") style='cursor:pointer' title='add detail'>";
				$tab .= "<td align=center><img src=images/save.png class=zImgBtn caption='Save' 
								onclick=\"savear('" . $bar['kodeorg'] . "','" . $noaruskasdpp . "','" . $noaruskasppn . "','" . $noaruskaspph . "','" . $noakunpiutang . "','" . $noakunpiutang . "','" . $noakunpiutang . "','" . $bar['noinvoice'] . "','" . $bar['nokontrak'] . "',
								'" . $bar['kodecustomer'] . "','" . $sisadpp . "','" . $sisappn . "','" . $sisapph . "');\"></td>";
			} else {
				$tab .= "<td>" . $keterangan . "</td>";
			}


			$tab .= "<td>" . $namakaryawan[$bar['createby']] . "</td>";
			$tab .= "<td>" . tanggalnormal($bar['createtime']) . " " . substr($bar['createtime'], 11, 8) . "</td>";
			$tab .= "<td>" . $namakaryawan[$bar['updateby']] . "</td>";
			$tab .= "<td>" . tanggalnormal($bar['updatetime']) . " " . substr($bar['updatetime'], 11, 8) . "</td>";


			$tab .= "</tr>";
			// }## Penutup IF
		}
		echo $tab;
		break;


	case 'savear':
		// exit("Error".$param['notransaksi']._.$param['noakundt']._.$param['keterangan1']._.$param['nodok']._.$param['kodesupplier']._.$param['jumlahdt']);
		// print_r($param);exit();
		#= ambil data ht untuk di dt
		$str = "select * from " . $dbname . "." . $table . " where  notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		$param['tanggal'] = $res[0]['tanggal'];
		$param['kurs'] = $res[0]['kurs'];
		$param['matauang'] = $res[0]['matauang'];
		$param['tipetransaksi'] = $res[0]['tipetransaksi'];
		$param['noakun'] = $res[0]['noakun'];
		$param['kodeorg'] = $res[0]['kodeorg'];

		$explnotransaksi = explode('/', $param['notransaksi']);
		$param['kode'] = $explnotransaksi[2];

		#= ambil nomor max
		/*
		$str = "select max(nourut) as nourut from ".$dbname.".".$tabledt." where  notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		$param['nourut']= $res[0]['nourut']+1;
		*/
		$param['nourut'] = '';
		$param['hutangunit1'] = 1;
		if ($param['pemilikhutang1'] == $param['kodeorg']) {
			$param['hutangunit1'] = '0';
			$param['pemilikhutang1'] = '';
		}

		# ========================= #
		# PARAMETER APPL
		# ========================= #
		$sql = selectQuery($dbname, "setup_parameterappl", "*", "kodeaplikasi='KB' AND kodeparameter='KBPPN'");
		$resppnkas = fetchData($sql, "OBJECT")[0];
		$param['ppnkas'] = $resppnkas->nilai;
		# ========================= #
		# PARAMETER APPL
		# ========================= #

		# Cek tipe invoice AR
		$tipeInvoice = makeOption($dbname, "keu_penagihanht", "noinvoice,tipeinvoice", "noinvoice='" . $param['keterangan1'] . "'")[$param['keterangan1']];
		if ($tipeInvoice == 'FEM') {
			$param['ppnkas'] = 0; // Tidak ada ppn kas untuk fem
		}
		if ($param['ppnkas'] == 1) {
			$sappl = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='NAPPH'";
			$rappl = fetchData($sappl);
			$noparam = $rappl[0]['nilai'];
			$noparam = explode(',', $noparam);
			$noakunpph = $noparam[0];
			$noaruspph = $noparam[1];

			// ini kenapa kodebarang nagmbil nya dari BAST aja ?
			$kodebarang = makeOption($dbname, "pmn_bast", "nokontrak,kodebarang", "nokontrak='{$param['nodok']}'")[$param['nodok']];

			// Jika kodebarang BAST gak ada maka default nya saya kasih TBS
			if($kodebarang == ''){
				$kodebarang = '40000003';
			}

			$str = "select * from " . $dbname . ".keu_5jenispenagihandt where (kodejenis='CIPP' or kodejenis='CITBS') and kodebarang='" . $kodebarang . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$noakunppn = $bar['noakunppn'];
			}

			# Tambahan Karena Jurnal Palma
			# Debet Kas/Bank 1.000.000
			# Kredit Piutang Penjualan (1.000.000)
			# Debet Kas/Bank (110.000) (PPn)
			# Kredit Piutang Penjualan (110.000) (PPn)
			if ($param['jumlahdt2'] != 0) {
				$param['keterangant2'] = "PPN KAS Keluaran Invoice " . $param['keterangan1'] . "; nomor kontrak " . $param['nodok'] . "";
				$param['keterangant3'] = "PAJAKPPNKAS";

				$str = "insert into " . $dbname . "." . $tabledt . "
				(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
				noakun2a,kode,keterangan1,keterangan2,matauang,
				kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
				kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
				orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,
				keterangan3) 
				values 
				('" . $param['notransaksi'] . "','" . $noakunppn . "','" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . ($param['jumlahdt2'] * -1) . "',
				'" . $param['noakun'] . "','" . $param['kode'] . "','" . $param['keterangan1'] . "','" . $param['keterangant2'] . "','" . $param['matauang'] . "',
				'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $param['noaruskas'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
				'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
				'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','" . $param['nourut'] . "',
				'" . $param['keterangant3'] . "')";
				// exit("Error:$str");
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			if ($param['jumlahdt3'] != 0) {
				$param['keterangant2'] = "PPH KAS Invoice " . $param['keterangan1'] . "; nomor kontrak " . $param['nodok'] . "";
				$param['keterangant3'] = "PAJAKPPHKAS";

				$str = "insert into " . $dbname . "." . $tabledt . "
				(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
				noakun2a,kode,keterangan1,keterangan2,matauang,
				kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
				kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
				orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,
				keterangan3) 
				values 
				('" . $param['notransaksi'] . "','" . $noakunpph . "','" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . ($param['jumlahdt3'] * -1) . "',
				'" . $param['noakun'] . "','" . $param['kode'] . "','" . $param['keterangan1'] . "','" . $param['keterangant2'] . "','" . $param['matauang'] . "',
				'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $noaruspph . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
				'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
				'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','" . $param['nourut'] . "',
				'" . $param['keterangant3'] . "')";
				// exit("Error:$str");
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
		}
		#= Setup khusus per PT (keu_5setuppenagihanpt) - tabel yang sama dipakai di invoice posting
		#= (keu_slave_penagihan.php): apakah alokasi DPP/PPN/PPh ke Piutang di kasbank ini digabung
		#= jadi 1 baris, atau dipisah seperti biasa (default). Kalau tidak ada row/flag='0', jalur
		#= lama (3 baris terpisah) tetap dipakai persis seperti sebelumnya, PT lain tidak terpengaruh.
		$gabungPiutangKB = '0';
		$rdataInvKB = fetchdata("select kodept, kodebarang from " . $dbname . ".keu_penagihanht where noinvoice='" . $param['keterangan1'] . "'");
		if (!empty($rdataInvKB)) {
			$strSetupOrgKB = "select gabungpiutang from " . $dbname . ".keu_5setuppenagihanpt where kodept='" . $rdataInvKB[0]['kodept'] . "' and (kodejenis='CIPP' or kodejenis='CITBS') and kodebarang='" . $rdataInvKB[0]['kodebarang'] . "'";
			$resSetupOrgKB = fetchdata($strSetupOrgKB);
			if (count($resSetupOrgKB) > 1) {
				exit('warning : Setup keu_5setuppenagihanpt ganda untuk invoice ' . $param['keterangan1'] . ', silahkan hubungi IT.');
			}
			if (!empty($resSetupOrgKB)) {
				$gabungPiutangKB = $resSetupOrgKB[0]['gabungpiutang'];
			}
		}

		if ($gabungPiutangKB == '1') {
			if (trim($param['noakundt']) == '') {
				exit('warning : Noakun may not empty / Terdapat nomor akun piutang yang kosong untuk invoice ' . $param['keterangan1'] . ', silahkan cek setup akun terkait.');
			}
			#= Gabung DPP+PPN+PPh (PPh biasanya sudah bertanda negatif dari perhitungan sisapph)
			#= jadi 1 baris kasbankdt saja, akun sama-sama pakai noakundt (piutang)
			$jumlahGabungKB = $param['jumlahdt'] + $param['jumlahdt2'] + $param['jumlahdt3'];
			if ($jumlahGabungKB < 0) {
				exit('warning : Nilai piutang gabungan untuk invoice ' . $param['keterangan1'] . ' menjadi negatif (' . number_format($jumlahGabungKB, 2) . '), silahkan cek kembali nilai DPP/PPN/PPh.');
			}
			if ($jumlahGabungKB != '0') {
				$param['keterangan3'] = "GABUNGAN";
				$param['keterangan2'] = "Piutang (Gabungan DPP + PPN + PPh) Invoice " . $param['keterangan1'] . "; nomor kontrak " . $param['nodok'] . "";

				$str = "insert into " . $dbname . "." . $tabledt . "
				(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
				noakun2a,kode,keterangan1,keterangan2,matauang,
				kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
				kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
				orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,
				keterangan3)
				values
				('" . $param['notransaksi'] . "','" . $param['noakundt'] . "','" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . $jumlahGabungKB . "',
				'" . $param['noakun'] . "','" . $param['kode'] . "','" . $param['keterangan1'] . "','" . $param['keterangan2'] . "','" . $param['matauang'] . "',
				'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $param['noaruskas'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
				'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
				'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','" . $param['nourut'] . "',
				'" . $param['keterangan3'] . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
		} else {
			if ($param['jumlahdt'] != '0') {
				$param['keterangan3'] = "DPP";
				$param['keterangan2'] = "DPP Invoice " . $param['keterangan1'] . "; nomor kontrak " . $param['nodok'] . "";

				$str = "insert into " . $dbname . "." . $tabledt . "
				(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
				noakun2a,kode,keterangan1,keterangan2,matauang,
				kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
				kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
				orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,
				keterangan3)
				values
				('" . $param['notransaksi'] . "','" . $param['noakundt'] . "','" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . $param['jumlahdt'] . "',
				'" . $param['noakun'] . "','" . $param['kode'] . "','" . $param['keterangan1'] . "','" . $param['keterangan2'] . "','" . $param['matauang'] . "',
				'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $param['noaruskas'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
				'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
				'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','" . $param['nourut'] . "',
				'" . $param['keterangan3'] . "')";
				// exit("Error:$str");
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			if ($param['jumlahdt2'] != '0') {
				// $param['nourut']++;
				$param['keterangan2'] = "PPN Keluaran Invoice " . $param['keterangan1'] . "; nomor kontrak " . $param['nodok'] . "";
				$param['keterangan3'] = "PAJAKPPN";

				$str = "insert into " . $dbname . "." . $tabledt . "
				(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
				noakun2a,kode,keterangan1,keterangan2,matauang,
				kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
				kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
				orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,
				keterangan3)
				values
				('" . $param['notransaksi'] . "','" . $param['noakundt2'] . "','" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . $param['jumlahdt2'] . "',
				'" . $param['noakun'] . "','" . $param['kode'] . "','" . $param['keterangan1'] . "','" . $param['keterangan2'] . "','" . $param['matauang'] . "',
				'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $param['noaruskas2'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
				'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
				'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','',
				'" . $param['keterangan3'] . "')";
				// exit("Error:$str");
				//".$param['nourut']."
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			if ($param['jumlahdt3'] != '0') {
				// $param['nourut']++;
				$param['keterangan2'] = "PPH Invoice " . $param['keterangan1'] . "; nomor kontrak " . $param['nodok'] . "";
				$param['keterangan3'] = "PAJAKPPH";

				$str = "insert into " . $dbname . "." . $tabledt . "
				(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
				noakun2a,kode,keterangan1,keterangan2,matauang,
				kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
				kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
				orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,
				keterangan3)
				values
				('" . $param['notransaksi'] . "','" . $param['noakundt3'] . "','" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . $param['jumlahdt3'] . "',
				'" . $param['noakun'] . "','" . $param['kode'] . "','" . $param['keterangan1'] . "','" . $param['keterangan2'] . "','" . $param['matauang'] . "',
				'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $param['noaruskas2'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
				'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
				'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','',
				'" . $param['keterangan3'] . "')";
				// exit("Error:$str");
				//".$param['nourut']."
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
		}

		break;


	/*
	
	DETAIL MANUAL
	
	*/


	case 'geteditdt':

		$str = "select * from " . $dbname . "." . $tabledt . "  where 
			notransaksi='" . $param['notransaksi'] . "' and nourut='" . $param['nourut'] . "'";
		// exit("Error:$str");
		$res = fetchdata($str);
		echo
		$res[0]['keterangan1'] . "###" .
			$res[0]['nodok'] . "###" .
			$res[0]['hutangunit1'] . "###" .
			$res[0]['pemilikhutang1'] . "###" .
			$res[0]['noaruskas'] . "###" .
			$res[0]['noakun'] . "###" .
			number_format($res[0]['jumlah'], 2) . "###" .
			$res[0]['keterangan2'] . "###" .
			$res[0]['kodekegiatan'] . "###" .
			$res[0]['kodeasset'] . "###" .
			$res[0]['nik'] . "###" .
			$res[0]['kodecustomer'] . "###" .
			$res[0]['kodesupplier'] . "###" .
			$res[0]['kodevhc'] . "###" .
			$res[0]['orgalokasi'] . "###" .
			$res[0]['nourut'] . "###" .
			$res[0]['departemen'] . "###" .
			$res[0]['keterangan3'];
		break;

	case 'savedtpembulatan':
		$str = "select * from " . $dbname . "." . $table . " where  notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		$param['tanggal'] = $res[0]['tanggal'];
		$param['kurs'] = $res[0]['kurs'];
		$param['matauang'] = $res[0]['matauang'];
		$param['tipetransaksi'] = $res[0]['tipetransaksi'];
		$param['noakun'] = $res[0]['noakun'];
		$param['kodeorg'] = $res[0]['kodeorg'];


		#= get coa
		if ($res[0]['tipetransaksi'] == 'K') {
			$param['noakundt'] = '9230599';
		}
		if ($res[0]['tipetransaksi'] == 'M') {
			$param['noakundt'] = '9110105';
		}
		$str = "select * from " . $dbname . ".keu_5aruskas_detail where noakun='" . $param['noakundt'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$param['noaruskas'] = $bar['noaruskas'];
		}
		#= get aruskas

		$param['keterangan2'] = 'Selisih Pembulatan';

		$explnotransaksi = explode('/', $param['notransaksi']);
		$param['kode'] = $explnotransaksi[2];

		#= ambil nomor max
		/*
		$str = "select max(nourut) as nourut from ".$dbname.".".$tabledt." where  notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		$param['nourut']= $res[0]['nourut']+1;
		*/

		$param['jumlahdt'] = str_replace(',', '', $param['jumlahdt']);

		$str = "insert into " . $dbname . "." . $tabledt . "
		(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
		noakun2a,kode,keterangan1,keterangan2,matauang,
		kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
		kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
		orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,
		keterangan3,createby,createtime,updateby) 
		values 
		('" . $param['notransaksi'] . "','" . $param['noakundt'] . "','" . $param['tipetransaksi'] . "','" . tanggalsystemn($param['tanggal']) . "','" . $param['jumlahdt'] . "',
		'" . $param['noakun'] . "','" . $param['kode'] . "','" . $param['keterangan1'] . "','" . $param['keterangan2'] . "','" . $param['matauang'] . "',
		'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $param['noaruskas'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
		'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
		'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','',
		'DPP','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'savedt':

		#= ambil data ht untuk di dt
		$str = "select * from " . $dbname . "." . $table . " where  notransaksi='" . $param['notransaksi'] . "'";
		// echo $str;exit("Error:A");
		$res = fetchdata($str);
		$param['tanggal'] = $res[0]['tanggal'];
		$param['kurs'] = $res[0]['kurs'];
		$param['matauang'] = $res[0]['matauang'];
		$param['tipetransaksi'] = $res[0]['tipetransaksi'];
		$param['noakun'] = $res[0]['noakun'];
		$param['kodeorg'] = $res[0]['kodeorg'];

		$param['jumlahdt'] = str_replace(',', '', $param['jumlahdt']);
		if ($param['noakun'] == '') {
			exit("Warning:Nomor akun masih kosong");
		}
		if ($param['keterangan2'] == '') {
			exit("Warning:Keterangan detail masih kosong");
		}
		if ($param['noaruskas'] == '') {
			exit("Warning:Nomor arus kas masih kosong");
		}
		if ($param['jumlahdt'] == '' || $param['jumlahdt'] == '0') {
			exit("Warning:Nominal tidak boleh kosong / 0");
		}

		if ($param['noakundt'] == '') {
			exit("Warning:Nomor akun masih kosong");
		}

		if ($param['hutangunit1'] == '1' and $param['pemilikhutang1'] == '') {
			exit("Warningsistem:Pemilik Hutang masih tidak boleh kosong, dikarenakan hutang unit dipilih 'ya'");
		}

		// if (substr($param['noakundt'], 0, 1) == '7' and $param['departemen'] == '') {
		// 	exit("Warning:Nomor akun 7xxx wajib mengisi kolom Dept.");
		// }
		// if (substr($param['noakundt'], 0, 1) == '8' and $param['departemen'] == '') {
		// 	exit("Warning:Nomor akun 8xxx wajib mengisi kolom Dept.");
		// }

		cekakunkb($param['noakundt'], $param['kodekegiatan'], $param['kodeasset'], $param['nik'], $param['kodecustomer'], $param['kodesupplier'], $param['kodevhc'], $param['orgalokasi'], $param['keterangan1'], $param['nodok']);

		if ($param['methoddt'] == 'insert') {

			$explnotransaksi = explode('/', $param['notransaksi']);
			$param['kode'] = $explnotransaksi[2];

			#= ambil nomor max
			/*
			$str = "select max(nourut) as nourut from ".$dbname.".".$tabledt." where  notransaksi='".$param['notransaksi']."'";
			$res=fetchdata($str);
			$param['nourut']= $res[0]['nourut']+1;
			*/
			$param['nourut'] = '';
			$str = "insert into " . $dbname . "." . $tabledt . "
			(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
			noakun2a,kode,keterangan1,keterangan2,matauang,
			kurs,kurs2,noaruskas,kodeorg,kodekegiatan,
			kodeasset,nik,kodecustomer,kodesupplier,kodevhc,
			orgalokasi,nodok,hutangunit1,pemilikhutang1,nourut,
			keterangan3,departemen,createby,createtime,updateby) 
			values 
			('" . $param['notransaksi'] . "','" . $param['noakundt'] . "','" . $param['tipetransaksi'] . "','" . $param['tanggal'] . "','" . $param['jumlahdt'] . "',
			'" . $param['noakun'] . "','" . $param['kode'] . "','" . $param['keterangan1'] . "','" . $param['keterangan2'] . "','" . $param['matauang'] . "',
			'" . $param['kurs'] . "','" . $param['kurs'] . "','" . $param['noaruskas'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
			'" . $param['kodeasset'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "','" . $param['kodevhc'] . "',
			'" . $param['orgalokasi'] . "','" . $param['nodok'] . "','" . $param['hutangunit1'] . "','" . $param['pemilikhutang1'] . "','" . $param['nourut'] . "',
			'" . $param['keterangan3'] . "','" . $param['departemen'] . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$str = "update " . $dbname . "." . $tabledt . " set 
				jumlah='" . $param['jumlahdt'] . "',
				keterangan1='" . $param['keterangan1'] . "',
				keterangan2='" . $param['keterangan2'] . "',
				matauang='" . $param['matauang'] . "',
				kurs='" . $param['kurs'] . "',
				kurs2='" . $param['kurs'] . "',
				noaruskas='" . $param['noaruskas'] . "',
				noakun='" . $param['noakundt'] . "',
				kodekegiatan='" . $param['kodekegiatan'] . "',
				kodeasset='" . $param['kodeasset'] . "',
				nik='" . $param['nik'] . "',
				kodecustomer='" . $param['kodecustomer'] . "',
				kodesupplier='" . $param['kodesupplier'] . "',
				kodevhc='" . $param['kodevhc'] . "',
				orgalokasi='" . $param['orgalokasi'] . "',
				nodok='" . $param['nodok'] . "',
				hutangunit1='" . $param['hutangunit1'] . "',
				pemilikhutang1='" . $param['pemilikhutang1'] . "',
				kodeorg='" . $param['kodeorg'] . "',
				keterangan3='" . $param['keterangan3'] . "',
				departemen='" . $param['departemen'] . "',
				updateby='" . $_SESSION['standard']['userid'] . "'
			where 
				notransaksi='" . $param['notransaksi'] . "' and nourut='" . $param['nourut'] . "'";
			// exit("Error".$str);
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		}

		break;

	case 'getoptdetail':

		$optalokasi = $optvhc = $optadk = $optnik = $optnoakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		if ($param['hutangunit1'] == 0) {
			$param['pemilikhutang1'] = $param['kodeorg'];
		}

		$str = "select * from " . $dbname . "." . $tabledt . " where
			notransaksi='" . $param['notransaksi'] . "' and nourut='" . $param['nourut'] . "'";
		// exit("Error:A");
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodeasset = $bar['kodeasset'];
			$nik = $bar['nik'];
			$orgalokasi = $bar['orgalokasi'];
			$kodevhc = $bar['kodevhc'];
			$noakundt = $bar['noakun'];
			$kodekegiatan = $bar['kodekegiatan'];
			$departemen = $bar['departemen'];
			$noaruskas = $bar['noaruskas'];
		}

		$str = "select * from " . $dbname . ".project where posting=0 and kodeorg='" . $param['pemilikhutang1'] . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($kodeasset == $bar['kode']) {
				$optadk .= "<option value='" . $bar['kode'] . "' selected>" . $bar['kode'] . " - " . $bar['nama'] . "</option>";
			} else {
				$optadk .= "<option value='" . $bar['kode'] . "'>" . $bar['kode'] . " - " . $bar['nama'] . "</option>";
			}
		}

		// $str = "select * from " . $dbname . ".datakaryawan where  statuskaryawan != 'Keluar' and lokasitugas in ('" . $param['pemilikhutang1'] . "','" . $param['kodeorg'] . "')";
		$arrtipeunit = getOrgDetail(10);
		$timOWL = ['0000000001', '0000000002', '0000000003', '0000000004', '0000000005', '0000000006', '0000000007', '0000000008'];
		$str = "select * from " . $dbname . ".datakaryawan where  statuskaryawan != 'Keluar' and lokasitugas in ('" . implode("','", $arrtipeunit) . "') AND karyawanid NOT IN ('" . implode("','", $timOWL) . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($nik == $bar['karyawanid']) {
				$optnik .= "<option value='" . $bar['karyawanid'] . "' selected>" . $bar['nik'] . " - " . $bar['namakaryawan'] . "</option>";
			} else {
				$optnik .= "<option value='" . $bar['karyawanid'] . "'>" . $bar['nik'] . " - " . $bar['namakaryawan'] . "</option>";
			}
		}

		#= cek apakah sudah terdaftar di vhc his / belum
		$str = "select * from " . $dbname . ".vhc_5master where status=1 and substr(kodetraksi,1,4)='" . $param['pemilikhutang1'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($kodevhc == $bar['kodevhc']) {
				$optvhc .= "<option value='" . $bar['kodevhc'] . "' selected>" . $bar['kodevhc'] . " - " . $bar['detailvhc'] . " - " . $bar['nopol'] . "</option>";
			} else {
				$optvhc .= "<option value='" . $bar['kodevhc'] . "'>" . $bar['kodevhc'] . " - " . $bar['detailvhc'] . " - " . $bar['nopol'] . "</option>";
			}
		}

		$str = "select * from " . $dbname . ".organisasi where kodeorganisasi like '" . $param['pemilikhutang1'] . "%'  and length(kodeorganisasi)>'6'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($orgalokasi == $bar['kodeorganisasi']) {
				$optalokasi .= "<option value='" . $bar['kodeorganisasi'] . "' selected>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			} else {
				$optalokasi .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			}
		}

		if ($param['hutangunit1'] == '1' and $param['pemilikhutang1'] == '') {
			$optalokasi = $optvhc = $optadk = $optnik = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		}

		#=cek coa

		$arrtipeunit = [$param['pemilikhutang1']];
		$tipeorganisasi[$param['pemilikhutang1']] = $tipeorganisasi[$param['pemilikhutang1']] == "KANWIL" ? "HOLDING" : $tipeorganisasi[$param['pemilikhutang1']];
		// exit("Warning: " . $tipeorganisasi[$param['pemilikhutang1']]);
		// if ($param['hutangunit1'] == 0) {
		// 	$arrtipeunit = array_keys(getOrgDetail(9));
		// }
		$str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun WHERE a.kasbankdetail = 1 AND a.detail = 1 AND a.aktif = 1 AND a.level = '5' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('" . implode("','", $arrtipeunit) . "')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik = '{$tipeorganisasi[$param['pemilikhutang1']]}' OR a.pemilik IN ('" . implode("','", $arrtipeunit) . "')))) GROUP BY a.noakun";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($noakundt == $bar['noakun']) {
				$optnoakun .= "<option value='" . $bar['noakun'] . "' selected>" . $bar['noakun'] . " - " . $nmakun[$bar['noakun']] . "</option>";
			} else {
				$optnoakun .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $nmakun[$bar['noakun']] . "</option>";
			}
		}

		echo $optadk . "###" . $optnik . "###" . $optvhc . "###" . $optalokasi . "###" . $optnoakun . "###" . $noaruskas . "###" . $kodekegiatan . "###" . $departemen;
		// exit("Error:A");
		break;


	case 'getaruskaskegiatan':
		$optkegiatan = $optaruskas = $optdepart = $optalokasi = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		#= aruskas
		$str = "select distinct(noaruskas) as noaruskas from " . $dbname . ".keu_5aruskas_detail where  noakun='" . $param['noakundt'] . "'";
		// echo $str;exit("Error:A");
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($param['noaruskas'] == $bar['noaruskas']) {
				$optaruskas .= "<option value='" . $bar['noaruskas'] . "' selected>" . $bar['noaruskas'] . " - " . $nmaruskas[$bar['noaruskas']] . "</option>";
			} else {
				$optaruskas .= "<option value='" . $bar['noaruskas'] . "'>" . $bar['noaruskas'] . " - " . $nmaruskas[$bar['noaruskas']] . "</option>";
			}
		}

		#= kegiatan
		$str = "select * from " . $dbname . ".setup_kegiatan where status=1 and noakun='" . $param['noakundt'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($param['kodekegiatan'] == $bar['kodekegiatan']) {
				$optkegiatan .= "<option value='" . $bar['kodekegiatan'] . "' selected>" . $bar['kodekegiatan'] . " " . $bar['namakegiatan'] . " - " . $bar['kelompok'] . "</option>";
			} else {
				$optkegiatan .= "<option value='" . $bar['kodekegiatan'] . "'>" . $bar['kodekegiatan'] . " " . $bar['namakegiatan'] . " - " . $bar['kelompok'] . "</option>";
			}
		}
		if ($param['pemilikhutang1'] == '') {
			$param['pemilikhutang1'] = $param['kodeorg'];
		}
		#= departemen
		if (substr($param['noakundt'], 0, 1) == '7' || substr($param['noakundt'], 0, 1) == '8') {
			$unittipe = getNamaOrg(substr($param['pemilikhutang1'], 0, 4), 'tipe');
			$where = " and kode in (select kode from " . $dbname . ".sdm_5departemen_detail where unittipe='" . $unittipe . "')";
			$str = "select * from " . $dbname . ".sdm_5departemen where aktif=1 " . $where . "";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($param['departemen'] == $bar['kode']) {
					$optdepart .= "<option value='" . $bar['kode'] . "' selected>" . $bar['kode'] . " - " . $bar['nama'] . "</option>";
				} else {
					$optdepart .= "<option value='" . $bar['kode'] . "'>" . $bar['kode'] . " - " . $bar['nama'] . "</option>";
				}
			}
		}


		if (substr($param['noakundt'], 0, 3) == '621') {
			$wh = " and statusblok='TM'";
		} elseif (substr($param['noakundt'], 0, 3) == '611') {
			$wh = " and statusblok='TM'";
		} elseif (substr($param['noakundt'], 0, 3) == '126') {
			$wh = " and statusblok='TBM'";
		} elseif (substr($param['noakundt'], 0, 5) == '12801') {
			$wh = " and statusblok='BBT' and kodeorg like '%PN%'";
		} elseif (substr($param['noakundt'], 0, 5) == '12802') {
			$wh = " and statusblok='BBT' and kodeorg like '%MN%'";
		}

		echo $param['noakundt'];

		// this is
		// cek dulu tipenya dari kodeorg
		$str0 = "select tipe from " . $dbname . ".organisasi where kodeorganisasi = '" . $param['pemilikhutang1'] . "'";
		$res0 = fetchdata($str0);
		foreach ($res0 as $bar0) {
			$tipe_0 = $bar0['tipe'];
		}

		if ($tipe_0 != 'PABRIK') {
			$str = "select * from " . $dbname . ".setup_blok where kodeorg like '" . $param['pemilikhutang1'] . "%'  and length(kodeorg)>'6' " . $wh . "";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($orgalokasi == $bar['kodeorg']) {
					// $optalokasi.="<option value='".$bar['kodeorg']."' selected>".$bar['kodeorg']." - ".getNamaOrg($bar['kodeorg'])."</option>";
					$optalokasi .= "<option value='" . $bar['kodeorg'] . "'>" . $bar['kodeorg'] . " - " . getNamaOrg($bar['kodeorg']) . "</option>";
				} else {
					$optalokasi .= "<option value='" . $bar['kodeorg'] . "'>" . $bar['kodeorg'] . " - " . getNamaOrg($bar['kodeorg']) . "</option>";
				}
			}
		} else {
			$str = "select * from " . $dbname . ".organisasi where kodeorganisasi like '" . $param['pemilikhutang1'] . "%' and tipe = 'STENGINE' ";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($orgalokasi == $bar['kodeorg']) {
					// $optalokasi.="<option value='".$bar['kodeorganisasi']."' selected>".$bar['kodeorganisasi']." - ".getNamaOrg($bar['kodeorganisasi'])."</option>";
					$optalokasi .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . getNamaOrg($bar['kodeorganisasi']) . "</option>";
				} else {
					$optalokasi .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . getNamaOrg($bar['kodeorganisasi']) . "</option>";
				}
			}
		}
		// this is
		//exit("error".$str);

		echo $optaruskas . "###" . $optkegiatan . "###" . $optdepart . "###" . $optalokasi;
		break;

	case 'getpemilikhutang':
		$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		if ($param['hutangunit1'] == '1') {
			$str = "select * from " . $dbname . ".organisasi where kodeorganisasi<>'" . $param['kodeorg'] . "' and length(kodeorganisasi)='4'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($param['pemilikhutang1'] == $bar['kodeorganisasi']) {
					$optunit .= "<option value='" . $bar['kodeorganisasi'] . "' selected>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
				} else {
					$optunit .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
				}
			}
		}

		echo $optunit;
		break;
	/*
	case'getaruskas':
		$optaruskas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".keu_5aruskas where tipetransaksi='".$param['tipetransaksi']."' and level=3";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optaruskas.="<option value='".$bar['noaruskas']."'>[".$bar['noaruskas']."] ".$bar['nama_aruskas']."</option>";
		}
		echo $optaruskas."###";
	break;
	
	
	case'getakun':
		$optnoakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".keu_5aruskas_detail where noaruskas='".$param['noaruskas']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($param['noakun']==$bar['noakun']) {
				$optnoakun.="<option value='".$bar['noakun']."' selected>[".$bar['noakun']."] ".$nmakun[$bar['noakun']]."</option>";
			} else {
				$optnoakun.="<option value='".$bar['noakun']."'>[".$bar['noakun']."] ".$nmakun[$bar['noakun']]."</option>";
			}
		}
		echo $optnoakun;
	break;
	
	
	case'getakun':
		$str="select * from ".$dbname.".keu_5akun where kasbankdetail='1'";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($param['noakun']==$bar['noakun']) {
				$optnoakun.="<option value='".$bar['noakun']."' selected>".$bar['noakun']." - ".$nmakun[$bar['noakun']]."</option>";
			} else {
				$optnoakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$nmakun[$bar['noakun']]."</option>";
			}
		}
	break;
	*/

	case 'loaddatadt':
		$sisa = 0;
		$nyomor = 0;
		$str = "select * from " . $dbname . "." . $tabledt . "  where notransaksi='" . $param['notransaksi'] . "'";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nyomor++;
			$strdt = "select * from " . $dbname . ".project where kode='" . $bar['kodeasset'] . "' ";
			$resdt = fetchdata($strdt);
			@$namaproject = $resdt[0]['nama'];

			$strdt = "select * from " . $dbname . ".setup_kegiatan where kodekegiatan='" . $bar['kodekegiatan'] . "' ";
			$resdt = fetchdata($strdt);
			@$namakegiatan = $resdt[0]['namakegiatan'];

			$strdt = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $bar['nik'] . "' ";
			$resdt = fetchdata($strdt);
			@$nikkaryawan = $resdt[0]['nik'];
			@$namakaryawan = $resdt[0]['namakaryawan'];

			$strdt = "select * from " . $dbname . ".vhc_5master_hist where kodevhc='" . $bar['kodevhc'] . "' ";
			$resdt = fetchdata($strdt);
			@$nopol = $resdt[0]['nopol'];
			@$namavhc = $resdt[0]['detailvhc'];
			@$nmdepart = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $bar['departemen'] . "'");
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['keterangan1'] . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['nodok'] . "</td>";
			$tab .= "<td align=center valign=top>" . $arrhutangunit[$bar['hutangunit1']] . "</td>";
			// if($bar['pemilikhutang1']==''){
			// $tab.="<td align=left></td>";
			// }else{
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['pemilikhutang1'] . " " . @$nmorganisasi[$bar['pemilikhutang1']] . "</td>";
			// }

			$tab .= "<td align=left valign=top>" . $bar['noaruskas'] . " " . @$nmaruskas[$bar['noaruskas']] . "</td>";
			$tab .= "<td align=left valign=top>" . $bar['noakun'] . " " . @$nmakun[$bar['noakun']] . "</td>";
			$tab .= "<td align=right valign=top>" . number_format($bar['jumlah'], 2) . "</td>";
			$tab .= "<td align=left valign=top>" . $bar['keterangan2'] . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['keterangan3'] . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['kodekegiatan'] . " " . @$namakegiatan . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['kodeasset'] . " " . @$namaproject . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $nikkaryawan . " " . @$namakaryawan . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['kodecustomer'] . " " . @$namacustomer . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['kodesupplier'] . " " . @$nmsupplier[$bar['kodesupplier']] . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['kodevhc'] . " " . $nopol . " " . @$namavhc . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['orgalokasi'] . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['departemen'] . " - " . $nmdepart[$bar['departemen']] . "</td>";
			$tab .= "<td align=left name=coldt" . $nyomor . "[] valign=top>" . $bar['lainnya'] . "</td>";
			if ($bar['lainnya'] == '') {
				$tab .= "<td align=center  valign=top width=20px>";
				$tab .= "<img src=images/application/application_edit.png class=zImgBtn caption='Edit' onclick=\"editdt('" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\"></td>";
				$tab .= "<td align=center  valign=top width=20px><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' 
						onclick=\"deletedt('" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\">";
				$tab .= "</td>";
			} else {
				$tab .= "<td align=center  valign=top width=20px>Tidak Dapat diedit</td>";
				$tab .= "<td align=center  valign=top width=20px><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' 
						onclick=\"deletedt('" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\">";
				$tab .= "</td>";
			}
			$tab .= "</tr>";
		}
		$tab .= "<tr style=display:none><td><input id=tempjumlahrowdt value=" . $nyomor . "></td></tr>";

		#= ambil nilai ht
		// $str = "select * from " . $dbname . "." . $table . "  where notransaksi='" . $param['notransaksi'] . "'";
		// $res = fetchdata($str);
		// $jumlahht = $res[0]['jumlah'];

		// #= ambil nilai dt
		// $str = "select sum(jumlah) as jumlah from " . $dbname . "." . $tabledt . "  where notransaksi='" . $param['notransaksi'] . "' and keterangan3 not like '%KAS%'";
		// $res = fetchdata($str);
		// @$jumlahdt = $res[0]['jumlah'];

		// $sisa = $jumlahht - $jumlahdt;

		#= diubah menjadi update nilai ht

		#= ambil nilai dt
		$str = "SELECT SUM(jumlah) AS jumlah FROM " . $dbname . "." . $tabledt . "  where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		@$jumlahdt = $res[0]['jumlah'] ? $res[0]['jumlah'] : 0;
		#= query update kasbank dari total nilai dt
		#= delete approval yang terbentuk
		$str = "update " . $dbname . "." . $table . " set jumlah='" . $jumlahdt . "' where notransaksi='" . $param['notransaksi'] . "'";
		$owlPDO->exec($str);


		echo $tab . "###" . number_format($jumlahdt, 2);
		break;

	case 'getValidasiAkun':
		$data = [];
		$qValidasiAkun = selectQuery($dbname, "keu_5akun", "kodekegiatan,kodeasset,nik,kodecustomer,kodesupplier,kodevhc,kodeblok as orgalokasi", "level='5' AND detail='1' AND noakun='{$param['noakun']}'");
		$rValidasiAkun = fetchData($qValidasiAkun);
		if (!empty($rValidasiAkun)) {
			foreach ($rValidasiAkun as $row) {
				$data[] = $row;
			}
		} else {
			$data[] = [
				"kodekegiatan" => 0,
				"kodeasset" => 0,
				"nik" => 0,
				"kodecustomer" => 0,
				"kodesupplier" => 0,
				"kodevhc" => 0,
				"orgalokasi" => 0,
			];
		}

		echo json_encode($data);

		break;



	case 'deletedt':
		$str = "delete from " . $dbname . "." . $tabledt . " where notransaksi='" . $param['notransaksi'] . "' and nourut='" . $param['nourut'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}

		##delete keu_kasbankdt_kk
		$str = "delete from " . $dbname . ".keu_kasbankdt_kk where notransaksi='" . $param['notransaksi'] . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'fileSelected':
		$data = $_POST;

		$param['kodeorg'] = $_SESSION['empl']['lokasitugas'];
		$kodeorg         = $_SESSION['empl']['lokasitugas'];

		$str = "select * from " . $dbname . ".vhc_5jenisvhc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kelvhc[$bar['jenisvhc']] = $bar['kelompokvhc'];
		}


		if ($_FILES['file']['error'] == 0) {
			$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
			$file    = $_FILES['file']['tmp_name'];

			if ($filetype == '.xlsx') {
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null, true, true, true);

				$range = range('A', 'V');
				$header = array('notransaksi', 'tanggal', 'nourut', 'noakun', 'jumlah', 'nodokumen', 'keterangan', 'noaruskas', 'kodeorg', 'kodekegiatan', 'kodeasset', 'kodebarang', 'nik', 'kodecustomer', 'kodesupplier', 'kodevhc', 'kodeblok', 'hutangunit', 'pemilikhutang', 'kodesegment', 'jenisrincian', 'departemen');

				// echo "<pre>";
				// print_r($param);
				// exit;

				foreach ($header as $head) {
					$cekhead[$head] = $head;
				}
				$arritem = $tanggallist = $divisilist = $kodebloklist = $notransaksilist = $nourutlist = array();
				$validasiht = "";
				$err = "0";
				foreach ($sheets as $noitem => $sheet) {
					if ($noitem > 1) {
						$notransaksi = $sheet['A'];
						$notransaksilist[$sheet['A']] = $sheet['A'];
						// if($sheet['C']!=''){							
						// 	$divisilist[$sheet['B']] = $sheet['B'];
						// }
						// if($sheet['C']!=''){							
						// 	$nourutlist[$sheet['C']] = $sheet['C'];
						// }
					}
				}

				if (count($notransaksilist) != 1) {
					$validasiht .= "Notransaksi tidak boleh lebih dari satu.<br>";
					$err++;
				}

				foreach ($sheets as $noitem => $sheet) {
					if ($noitem == 1) {
						$tab .= "<table class='sortable' cellspacing=1 cellpadding=5 border=0 >
						<thead>
							<tr class=rowheader style=height:25px>";
						$tab .= "<th align=center width=30px>No.</th>";
						foreach ($range as $idcol => $col) {
							$style = "";
							if ($cekhead[$sheet[$col]] == "") {
								$style = "style=color:red; title='Kolom header mengalami perubahan.'";
							}
							$tab .= "<th align=center " . $style . ">" . $sheet[$col] . "</th>";
						}
						$tab .= "<th align=center>Status</th>";
						$tab .= "</tr>
						</thead>";
					} else {

						$validasi  				= "";
						// $keterangan			    = getNamaKeg($sheet['D'],'namakegiatan');
						// $matauang				= getNamaAruskas($sheet['F']);

						$uploadnotransaksi   			= $sheet['A'];
						$uploadtanggal     				= $sheet['B'];
						$uploadnourut         			= $sheet['C'];
						$uploadnoakun    				= $sheet['D'];
						$uploadjumlah    				= $sheet['E'];
						$uploadnodok    				= $sheet['F'];
						$uploadketerangan			    = $sheet['G'];
						$uploadaruskas			    	= $sheet['H'];
						$uploadkodeorg     				= $sheet['I'];
						$uploadkodekegiatan				= $sheet['J'];
						$uploadkodeasset				= $sheet['K'];
						$uploadkodebarang				= $sheet['L'];
						$uploadnik						= $sheet['M'];
						$uploadkodecustomer     		= $sheet['N'];
						$uploadkodesupplier     		= $sheet['O'];
						$uploadkodevhc		     		= $sheet['P'];
						$uploadkodeblok		     		= $sheet['Q'];
						$uploadhutangunit		     	= $sheet['R'];
						$uploadpemilikhutang			= $sheet['S'];
						$uploadkodesegment				= $sheet['T'];
						$uploadjenisrincian				= $sheet['U'];
						$uploaddepartemen				= $sheet['V'];


						if ($uploadnourut == '') {
							$validasi .= "Nourut Tidak Boleh Kosong.<br>";
							$err++;
						}

						if ($uploadtanggal == '') {
							$validasi .= "Tanggal Kosong.<br>";
							$err++;
						}
						if (strlen($uploadtanggal) != 10) {
							$validasi .= "Panjang Tanggal tidak sesuai.<br>";
							$err++;
						}
						if ($uploadkodeorg == '') {
							$validasi .= "Kode Organisasi tidak boleh Kosong.<br>";
							$err++;
						}
						if (strlen($uploadkodeorg) != 4) {
							$validasi .= "Panjang Karakter Kode Organisasi tidak sesuai.<br>";
							$err++;
						}

						if ($uploadkodeblok != '') {
							if ($uploadkodekegiatan == '') {
								$validasi .= "Jika Kode Blok Diisi, Kode kegiatan tidak boleh kosong.<br>";
								$err++;
							}
							if (strlen($uploadkodekegiatan) != 9) {
								$validasi .= "Panjang kode kegiatan tidak sesuai.<br>";
								$err++;
							}
						}

						if ($uploadaruskas == '') {
							$validasi .= "Noaruskas tidak boleh kosong.<br>";
							$err++;
						}
						if ($uploadnoakun == '') {
							$validasi .= "Nomor Akun tidak boleh kosong.<br>";
							$err++;
						}
						if ($uploadketerangan == '') {
							$validasi .= "Keterangan tidak boleh kosong.<br>";
							$err++;
						}

						// if($namakeg==''){$validasi.="Nama kegiatan tidak terdaftar.<br>";$err++;}
						// if($kodebudget=='VHC' and $kodevhc==''){
						// 	if($aruskas==''){$validasi.="Kode kendaraan harus diisi.<br>";$err++;}
						// }
						// if(($kodebudget=='MATERIAL' or $kodebudget=='TOOL') and $kodebarang==''){
						// 	if($aruskas==''){$validasi.="Kode barang harus diisi.<br>";$err++;}
						// }
						// if($kodevhc==''){
						// 	if($aruskas==''){$validasi.="Arus kas kosong.<br>";$err++;}
						// 	if($namaaruskas==''){$validasi.="Nama arus kas tidak terdaftar.<br>";$err++;}
						// 	if($ak==false){
						// 		if($namaaruskas==''){$validasi.="Arus kas tidak sesuai.<br>";$err++;}									
						// 	}
						// }

						$sql = "select kodeorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $kodeorg . "'";
						$res = fetchData($sql);
						$kodeorgval = $res[0]['kodeorganisasi'];

						if ($kodeorg != $kodeorgval) {
							$validasi .= "Kode Organisasi tersebut tidak terdaftar di Organisasi";
						}

						# Method
						$method 	= "savedt";
						$methoddt 	= "insert";
						# End

						$color = "";
						if ($validasiht != '' or $validasi != '') {
							$color = "style=color:red";
						}

						$no++;
						$tab .= "<tr class=rowcontent " . $color . " id=baris_" . $no . ">";
						$tab .= "<td hidden>
									<input id=method_" . $no . " value=" . $method . ">
									<input id=methoddt_" . $no . " value=" . $methoddt . ">
									<input id=kodeorg_" . $no . " value=" . $kodeorg . ">
									<input id=kodejurnal_" . $no . " value=" . $kodejurnal . ">
									<input id=jenis_" . $no . " value=" . $jenis . ">
								</td>";
						$tab .= "<td " . $color . " align=center>" . $no . "</td>";
						$tab .= "<td " . $color . " align=center id=notransaksi_" . $no . ">" . $uploadnotransaksi . "</td>";
						$tab .= "<td " . $color . " align=center id=tanggal_" . $no . ">" . $uploadtanggal . "</td>";
						$tab .= "<td " . $color . " align=center id=nourut_" . $no . ">" . $uploadnourut . "</td>";
						$tab .= "<td " . $color . " align=center id=noakun_" . $no . ">" . $uploadnoakun . "</td>";
						$tab .= "<td " . $color . " align=center id=jumlah_" . $no . ">" . $uploadjumlah . "</td>";
						$tab .= "<td " . $color . " align=center id=nodok_" . $no . ">" . $uploadnodokumen . "</td>";
						$tab .= "<td " . $color . " align=center id=ket2_" . $no . ">" . $uploadketerangan . "</td>";
						$tab .= "<td " . $color . " align=center id=noaruskas_" . $no . ">" . $uploadaruskas . "</td>";
						$tab .= "<td " . $color . " align=center id=kodeorg_" . $no . ">" . $uploadkodeorg . "</td>";
						$tab .= "<td " . $color . " align=center id=kodekegiatan_" . $no . ">" . $uploadkodekegiatan . "</td>";
						$tab .= "<td " . $color . " align=center id=kodeasset_" . $no . ">" . $uploadkodeasset . "</td>";
						$tab .= "<td " . $color . " align=center id=kodebarang_" . $no . ">" . $uploadkodebarang . "</td>";
						$tab .= "<td " . $color . " align=center id=nik_" . $no . ">" . $uploadnik . "</td>";
						$tab .= "<td " . $color . " align=center id=kodecustomer_" . $no . ">" . $uploadkodecustomer . "</td>";
						$tab .= "<td " . $color . " align=center id=kodesupplier_" . $no . ">" . $uploadkodesupplier . "</td>";
						$tab .= "<td " . $color . " align=center id=kodevhc_" . $no . ">" . $uploadkodevhc . "</td>";
						$tab .= "<td " . $color . " align=center id=kodeblok_" . $no . ">" . $uploadkodeblok . "</td>";
						$tab .= "<td " . $color . " align=center id=hutangunit1_" . $no . ">" . $uploadhutangunit . "</td>";
						$tab .= "<td " . $color . " align=center id=pemilikhutang1_" . $no . ">" . $uploadpemilikhutang . "</td>";
						$tab .= "<td " . $color . " align=center id=kodesegment_" . $no . ">" . $uploadkodesegment . "</td>";
						$tab .= "<td " . $color . " align=center id=jenisrincian_" . $no . ">" . $uploadjenisrincian . "</td>";
						$tab .= "<td " . $color . " align=center hidden id=matauang_" . $no . ">" . $param['matauang'] . "</td>";
						$tab .= "<td " . $color . " align=center hidden id=kurs_" . $no . ">" . $param['kurs'] . "</td>";
						$tab .= "<td " . $color . " align=center id=departemen_" . $no . ">" . $uploaddepartemen . "</td>";
						$tab .= "<td " . $color . " align=left id=validasi_" . $no . ">" . trim(nl2br($validasiht)) . trim(nl2br($validasi)) . $selisih . $varvhc . $varupah . "</td>";
						$tab .= "</tr>";

						$ttlrp += round($rupiah, 2);

						// $cekduplicate[$tahun][$divisi][$kodebudget][$kodekeg][$kodebarang][$kodevhc]+=1;
						// $barisduplicate[$tahun][$divisi][$kodebudget][$kodekeg][$kodebarang][$kodevhc]=$no;
					}
				}

				// $duplicate="<br>";
				// foreach($cekduplicate as $t => $v1){
				// 	foreach($v1 as $d => $v2){
				// 		foreach($v2 as $k => $v3){
				// 			foreach($v3 as $g => $v4){
				// 				foreach($v4 as $b => $v5){
				// 					foreach($v5 as $v => $nilai){
				// 						if($nilai>1){
				// 							//$duplicate.=$barisduplicate[$t][$d][$k][$g][$b][$v].", ";
				// 							$duplicate.=$t.",".$d.",".$k.",".$g.",".$b.",".$v.";<br>";
				// 						}
				// 					}
				// 				}
				// 			}
				// 		}
				// 	}
				// }

				// echo"<pre>";
				// print_r($barisduplicate);

				// if($duplicate!=''){					
				// 	$tab.="<tr class=rowcontent>";
				// 	$tab.="<td colspan=19 style=background-color:#fcdede;color:blue;>Ada data yang double : <b>".$duplicate."</b> (jika ada data duplicate maka data pada baris sebelumnya akan di replace dengan data baris terakhir)</td>";
				// 	$tab.="</tr>";
				// }

				$tab .= "</tbody>";
				$tab .= "<tfoot>";
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td colspan=22 align=center style=background-color:cyan;color:black;>T O T A L</td>";
				$tab .= "<td style=background-color:cyan;color:black;>(SELISIH)</td>";
				$tab .= "<td align=right style=background-color:cyan;color:black;>" . number_format(round($ttlrp), 2) . "</td>";
				// $tab.="<td style=background-color:cyan;color:black;>".number_format($ttlselisih)."</td>";
				$tab .= "</tr>";


				if ($err > 0) {
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td colspan=100 align=center style=color:black;font-size:20px;><b>Tombol simpan akan muncul jika tidak ditemukan baris yg berwarna merah.</b></td>";
					$tab .= "</tr>";
				} else {
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td colspan=100 align=center><button id=btnsubmit class=mybutton onclick=\"simpanupload(" . $no . ")\">SaveAll</button></td>";
					$tab .= "</tr>";
				}
				$tab .= "</tfoot>";
				$tab .= "</table>";
			} else {
				exit("Warning : Format file upload harus .xlsx");
			}
		}

		echo $tab;
		break;

	case 'findDetailFromPDO':
		$kepalaAkun = substr($param['noakun'], 0, 5);
		$tipeKasbank = ($kepalaAkun == '11101') ? 'KAS' : 'BANK';

		// Ambil data PDO
		$dataPdo = [];
		$qPdo = "SELECT b.nopdo, b.notransaksi, b.noakun, b.rincian, b.rupiahdiajukan as rupiah, b.tipekasbank, b.nodok FROM keu_pdoht a
				JOIN keu_pdodt b ON a.nopdo = b.nopdo
				WHERE a.kodeorg = '" . $param['kodeorg'] . "' AND a.periode = '" . substr(tanggalsystemn($param['tanggal']), 0, 7) . "' AND a.tipepdo = 'KAS' AND b.tipekasbank = '" . $tipeKasbank . "'";
		$rPdo = fetchData($qPdo);
		if (!empty($rPdo)) {
			foreach ($rPdo as $row) {
				$dataPdo[] = $row;
			}
		}

		$tbody = "";
		if (!empty($dataPdo)) {
			foreach ($dataPdo as $index => $row) {
				$tbody .= "<tr class='rowcontent rowDataPdo'>";
				$tbody .= "<td align=center>" . ($index + 1) . "</td>";
				$tbody .= "<td align=center class='nopdo' data-nopdo='" . $row['nopdo'] . "'>" . $row['nopdo'] . "</td>";
				$tbody .= "<td align=center class='notransaksi' data-notransaksi='" . $row['notransaksi'] . "'>" . $row['notransaksi'] . "</td>";
				$tbody .= "<td align=center class='noakun' data-noakun='" . $row['noakun'] . "'>{$row['noakun']} - " . getNamaAkun($row['noakun']) . "</td>";
				$tbody .= "<td align=center class='rincian' style='text-transform:uppercase;' data-rincian='" . strtoupper($row['rincian']) . "'>" . $row['rincian'] . "</td>";
				$tbody .= "<td align=right class='rupiah' data-rupiah='" . $row['rupiah'] . "'>" . number_format($row['rupiah'], 2) . "</td>";
				$tbody .= "<td align=center class='tipekasbank' data-tipekasbank='" . $row['tipekasbank'] . "'>" . $row['tipekasbank'] . "</td>";
				$tbody .= "<td align=center class='nodok' data-nodok='" . $row['nodok'] . "'>" . $row['nodok'] . "</td>";

				// Cek Jika data ini sudah pernah ditarik ke dalam kasbank detail
				$qCek = "SELECT COUNT(*) as count, notransaksi FROM keu_kasbankdt WHERE keterangan1 = '" . $row['notransaksi'] . "' AND nodok = '" . $row['nodok'] . "'";
				$rCek = fetchData($qCek);
				$countCek = $rCek[0]['count'];
				if ($countCek > 0) {
					$tbody .= "<td align=center><img src=images/approve.png class=zImgBtn caption='Sudah Ditambahkan' disabled><br/>" . $rCek[0]['notransaksi'] . "</td>";
				} else {
					$tbody .= "<td align=center><img src=images/save.png class=zImgBtn caption='Save' onclick=\"saveDetailFromPDO(this)\"></td>";
				}
				$tbody .= "</tr>";
			}
		} else {
			$tbody = "<tr class='rowcontent'><td colspan='9' align='center'>Tidak ada data yang ditemukan.</td></tr>";
		}
		echo $tbody;

		break;

	case 'showDetailFromPDO':
		$table = "
			<div style='font-family: sans-serif; width: 100%; background: #e7f3ff; border: 1px solid #bde0fe; border-left: 5px solid #007bff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);'>
				<h2 style='margin: 0 0 10px 0; font-size: 1.25rem; color: #004085; display: flex; align-items: center;'>
					<span style='margin-right: 10px;'>ℹ️</span> Informasi
				</h2>
				<p style='margin: 0; color: #004085; line-height: 1.6; font-size: 0.95rem;'>
					Data dibawah ini merupakan data <strong>PDO</strong> yang memiliki tipe kasbank sesuai dengan jenis akun yang dipilih. 
					Anda dapat memilih salah satu data PDO untuk ditarik ke dalam detail kasbank. 
					Pastikan untuk memeriksa informasi dengan teliti sebelum melakukan tindakan.
				</p>
			</div>";
		$table .= "
				<table class='sortable' cellspacing=1 cellpadding=5 border=0 style='margin-top: 20px; width: 100%;'>
					<thead>
						<tr class=rowheader style=height:25px>
							<th align=center width=30px>No.</th>
							<th align=center>No. PDO</th>
							<th align=center>Notransaksi</th>
							<th align=center>Noakun</th>
							<th align=center>Rincian</th>
							<th align=center>Rupiah Diajukan</th>
							<th align=center>Tipe Kasbank</th>
							<th align=center>No Dokumen</th>
							<th align=center>Aksi</th>
						</tr>
					</thead>
					<tbody id='tbodyDataPdo'>";
		$table .= "</tbody></table>";
		echo $table;

		break;

	case 'saveDetailFromPDO':
		try {
			$owlPDO->beginTransaction();

			// Data Header
			$qKasbankht = selectQuery($dbname, "keu_kasbankht", "*", "notransaksi='" . $param['notransaksi'] . "'");
			$rKasbankht = fetchData($qKasbankht);
			$kasbankhtData = $rKasbankht[0];
			$tanggal = $kasbankhtData['tanggal'];
			$kodeorg = $kasbankhtData['kodeorg'];
			$kurs = $kasbankhtData['kurs'];
			$mataUang = $kasbankhtData['matauang'];
			$noakunHead = $kasbankhtData['noakun'];
			$tipeTransaksi = $kasbankhtData['tipetransaksi'];
			$kode = explode('/', $param['notransaksi'])[2];
			$nourut = $pemilikhutang = '';
			$hutangunit = 0;

			$dataString = checkPostGet('dataDetailPdo', '');
			$dataDetailPdo = json_decode($dataString, true)[0];

			// Data Detail dari PDO
			$nopdo = $dataDetailPdo['nopdo'];
			$notransaksiPdo = $dataDetailPdo['notransaksiPdo'];
			$nodok = $dataDetailPdo['nodok'];
			$keterangan1 = $notransaksiPdo;
			$keterangan2 = $dataDetailPdo['rincian'];
			$noakundt = $dataDetailPdo['noakun'];
			if ($tipeTransaksi == 'M') {
				$jumlahdt = $dataDetailPdo['rupiah'] * -1;
			} else {
				$jumlahdt = $dataDetailPdo['rupiah'];
			}

			// Insert ke kasbankdt
			$dataIns = [
				'notransaksi' => $param['notransaksi'],
				'noakun' => $noakundt,
				'tipetransaksi' => $tipeTransaksi,
				'tanggal' => $tanggal,
				'jumlah' => $jumlahdt,
				'noakun2a' => $noakunHead,
				'kode' => $kode,
				'keterangan1' => $keterangan1,
				'keterangan2' => $keterangan2,
				'keterangan3' => 'DPP',
				'matauang' => $mataUang,
				'kurs' => $kurs,
				'kurs2' => $kurs,
				'noaruskas' => $value['aruskas'],
				'kodeorg' => $kodeorg,
				'nodok' => $nodok,
				'hutangunit1' => $hutangunit,
				'pemilikhutang1' => $pemilikhutang,
				'kodesegment' => '0000000001',
				'lainnya' => 'PDO#' . $dataDetailPdo['notransaksiPdo'] . "#" . $nodok,
			];
			$cols = array_keys($dataIns);
			$insertQ = insertQuery($dbname, "keu_kasbankdt", $dataIns, $cols);
			if (!$owlPDO->exec($insertQ)) {
				throw new PDOException("Gagal menyimpan data detail dari PDO: " . implode(" ", $owlPDO->errorInfo()));
			}

			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warning: Gagal melakukan penyimpanan data \n" . addslashes($e->getMessage());
		}
		break;

	default:
		break;
}
