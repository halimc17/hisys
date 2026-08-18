<?
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/utilities.php');
include_once('lib/HtmlExcel.php');

$proses = checkPostGet('proses', '');
$view = "";

#= Get Param
$param = $_POST;
if (count($param) == 0) $param = $_GET;

#= Convert Data
$nik = makeOption($dbname, "datakaryawan", "karyawanid,nik", "", "nik asc");
$namakaryawan = makeOption($dbname, "datakaryawan", "karyawanid,namakaryawan");
$subbagian = makeOption($dbname, "datakaryawan", "karyawanid,subbagian");
$divisi = makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi");
$kegiatan = makeOption($dbname, "setup_kegiatan", "kodekegiatan,namakegiatan");
$satuan = makeOption($dbname, "setup_kegiatan", "kodekegiatan,satuan");


$qPeriod = selectQuery($dbname, 'sdm_5periodegaji', 'tanggalmulai,tanggalsampai,sudahproses', "periode='" . substr($param['tgl'], 0, 7) . "' and kodeorg='" .
	$param['kodeorg'] . "' ");
@$tanggal1 = $resPeriod[0]['tanggalmulai'];
if ($resPeriod[0]['sudahproses'] == 0) {
	$database = 'datakaryawan ';
	$wheredatabase = '1=1';
} else {
	$database = 'datakaryawan_hist ';
	$wheredatabase = "c.approval_status='8' and c.version_type='B' and c.periodegaji='" . substr($param['tgl'], 0, 7) . "'";
}

#= Filter tanggal by Notransaksi
#= Karena tidak ada kolom tanggal
$tglAwal = explode("-", $param['tgl']);
$tglAkhir = explode("-", $param['tglx']);

$tglAwal = $tglAwal[2] . $tglAwal[1] . $tglAwal[0];
$tglAkhir = $tglAkhir[2] . $tglAkhir[1] . $tglAkhir[0];

// exit('warning'.$tglAwal);
#= Get Data Prestasi
$whereKp = "a.notransaksi NOT LIKE '%PNN%'";


if ($param['unit'] != "") {
	$whereKp .= " and a.kodeorg LIKE '" . $param['unit'] . "%'";
}

if ($param['div'] != "") {
	$whereKp .= " and a.kodeorg LIKE '%" . $param['div'] . "%'";
} else {
	$whereKp .= " and substr(a.kodeorg,1,6) IN (" . getOrgDetail(26) . ")";
}

if ($param['tipe'] != "") {
	$whereKp .= " and c.tipekaryawan='" . $param['tipe'] . "'";
}



if ($param['tgl'] != "" && $param['tglx'] != "") {
	// $whereKp .= " and left(a.notransaksi,8) BETWEEN '".$tglAwal."' and '".$tglAkhir."'";
	$whereKp .= " and left(a.notransaksi,8) >= '" . $tglAwal . "' and  left(a.notransaksi,8) <='" . $tglAkhir . "'";
}


// ambil kegiatan dari kelompok kegiatan
$str0 = "SELECT * FROM `setup_kegiatan` where kelompok = '" . $param['kegiatan'] . "' ";
$result0 = fetchData($str0);

foreach ($result0 as $val0) {
	$kegiatan_d[$val0['kodekegiatan']] = $val0['kodekegiatan'];
}
// akhir ambil kegiatan dari kelompok kegiatan



if ($param['kegiatan'] != "") {
	$whereKp .= " and a.kodekegiatan IN ('" . implode("','", $kegiatan_d) . "') ";
}
// $whereKp .= " group by nikpemel,kodekegiatan order by left(notransaksi,8) asc";
//$whereKp .= " group by a.nikpemel,a.kodekegiatan,notransaksi";


$col = "a.notransaksi,
left(a.notransaksi,8) as tglbkm,
a.nikpemel,
a.kodekegiatan,
a.kodeorg,
a.hasilkerja as hasilkerja,
a.jumlahhk as hk,
(b.umr) as upah,
(
        IFNULL(b.insentif,0)
    ) AS upahpremi,

    (
        IFNULL(b.umr,0)
        + IFNULL(b.insentif,0)
    ) AS upahpluspremi";

$query = "select " . $col . " from " . $dbname . ".kebun_prestasi a 
left join  " . $dbname . "." . $database . " c on a.nikpemel=c.karyawanid
left join " . $dbname . ".kebun_kehadiran b on a.notransaksi = b.notransaksi 
and a.nikpemel = b.nik and a.nourut = b.nourut
where " . $wheredatabase . " and " . $whereKp . " order by left(a.notransaksi,8) asc";
// echo $query;
$result = fetchData($query);

foreach ($result as $val) {
	$tanggalxx = substr($val['tglbkm'], 0, 4) . "-" . substr($val['tglbkm'], 4, 2) . "-" . substr($val['tglbkm'], -2);
	$tglnya[$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']] = substr($val['tglbkm'], -2);
	$blnnya[$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']] = substr($val['tglbkm'], 4, 2);
	$thnnya[$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']] = substr($val['tglbkm'], 0, 4);

	$karyawan[$val['notransaksi']][$tanggalxx][$val['kodekegiatan']][$val['nikpemel']][$val['kodeorg']] = $val['kodekegiatan'];
	$prestasiKegiatan[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']] = $val['hasilkerja'];
	$upahKp[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']] = $val['upah'];
	$upahPremiKp[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']] = $val['upahpremi'];
	$upahPlusPremi[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']] = $val['upahpluspremi'];
	$hk[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']] = $val['hk'];
	// $premiBasis[$val['nikpemel']][$tanggalxx][$val['kodekegiatan']] = $val['premibasis'];
	// $upahPremiLebih[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']] = $val['extrafooding'];

	$gtotPrestasi += $prestasiKegiatan[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']];
	$gtotHk += $hk[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']];
	$gtotUpah += $upahKp[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']];
	$gttotupahpremi += $upahPremiKp[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']];
	$gtotpendapatan += $upahPlusPremi[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']];
	// $gtotPremiBasis += $premiBasis[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']]; 
	// $gtotPremiLebih += $upahPremiLebih[$val['notransaksi']][$tanggalxx][$val['nikpemel']][$val['kodekegiatan']][$val['kodeorg']]; 
}

#= Awal Tabel

if ($proses == "excel") {
	$view .= "<table class=sortable cellspacing=1 border=1>";
} else {
	$view .= "<div> <table class=sortable cellspacing=1 border=0 width=100%>";
}
#= Tabel Header
$view .= "<thead>";
$view .= "<tr class=rowheader>";
$view .= "<th align=center rowspan=2>No</th>";
$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['notransaksi'] . "</th>";
$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</th>";
$view .= "<th align=center rowspan=2>NIK</th>";
$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['namakaryawan'] . "</th>";
$view .= "<th align=center rowspan=2>Divisi</th>";
$view .= "<th align=center rowspan=2>Blok</th>";
$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['kegiatan'] . "</th>";
$view .= "<th align=center colspan=2>Hasil Kerja</th>";
$view .= "<th align=center colspan=2>Jumlah</th>";
$view .= "<th align=center rowspan=2>Premi</th>";
$view .= "<th align=center rowspan=2>Total Pendapatan</th>";
$view .= "</tr>";

$view .= "<tr>";
$view .= "<th>Satuan</th>";
$view .= "<th>Jumlah</th>";

$view .= "<th>HK</th>";
$view .= "<th>Upah</th>";
$view .= "</tr>";
$view .= "</thead>";

#= Tabel Body
$view .= "<tbody>";

#= Grand Total 
$view .= "<tr style='background-color:#c3f3fa'>";
$view .= "<td align=center colspan=9><b>GRANDTOTAL</b></td>";
$view .= "<td align=right>" . number_format($gtotPrestasi, 2) . "</td>";
$view .= "<td align=right>" . number_format($gtotHk, 2) . "</td>";
$view .= "<td align=right>" . number_format($gtotUpah, 2) . "</td>";
$view .= "<td align=right>" . number_format($gttotupahpremi, 2) . "</td>";
$view .= "<td align=right>" . number_format($gtotpendapatan, 2) . "</td>";
$view .= "</tr>";
#= Akhir Grand Total

$tampung = array();
$no = 0;
if (count($karyawan) > 0) {

	foreach ($karyawan as $notransaksi => $arrtglx) {
		foreach ($arrtglx as $tglbkmx => $val) {
			foreach ($val as $kodekeg => $valx) {
				#= Header Kegiatan
				// $view .= "<tr>";
				// 	$view .= "<td align=left colspan=14><b>".$kegiatan[$kodekeg]."</b></td>";
				// $view .= "</tr>";

				foreach ($valx as $karyid => $valz) {
					foreach ($valz as $blok => $valzx) {
						$no += 1;

						$view .= "<tr class=rowcontent>";
						$view .= "<td align=center>" . $no . "</td>";
						$view .= "<td align=left>" . $notransaksi . "</td>";
						$view .= "<td align=left>" . tanggalnormal($tglbkmx) . "</td>";
						$view .= "<td align=left>" . $nik[$karyid] . "</td>";
						$view .= "<td align=left>" . $namakaryawan[$karyid] . "</td>";
						$view .= "<td align=left>" . ($subbagian[$karyid] == "" ? "KANTOR" : ($subbagian[$karyid] == "UMUM" ? "UMUM" : $divisi[$subbagian[$karyid]])) . "</td>";
						$view .= "<td align=left>" . $blok . "</td>";
						if (isset($kegiatan[$kodekeg])) {
							$view .= "<td align=left>" . $kegiatan[$kodekeg] . "</td>";
						} else {
							$view .= "<td align=left>" . $kodekeg . "</td>";
						}
						$view .= "<td align=center>" . $satuan[$kodekeg] . "</td>";
						$view .= "<td align=right>" . number_format($prestasiKegiatan[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok], 2) . "</td>";
						$view .= "<td align=right>" . number_format($hk[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok], 2) . "</td>";
						$view .= "<td align=right>" . number_format($upahKp[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok], 2) . "</td>";
						$view .= "<td align=right>" . number_format($upahPremiKp[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok], 2) . "</td>";

						$totPendapatan[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok] = ($upahPlusPremi[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok]) . "</td>";

						$view .= "<td align=right>" . number_format($totPendapatan[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok], 2) . "</td>";
						#= Semua Total
						$totPrestasi[$tglbkmx][$kodekeg] += $prestasiKegiatan[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok];
						$totHk[$tglbkmx][$kodekeg] += $hk[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok];
						$totUpah[$tglbkmx][$kodekeg] += $upahPlusPremi[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok];
						$totPremiLebih[$tglbkmx][$kodekeg] += $upahPremiKp[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok];
						$totPendapatanx[$tglbkmx][$kodekeg] += $totPendapatan[$notransaksi][$tglbkmx][$karyid][$kodekeg][$blok];
					}
				}
				#= Total 
				// $view .= "<tr style='background-color:#c3f3fa'>";
				// 	$view .= "<td align=center colspan=8><b>SUBTOTAL (".$tglbkm[$karyid][$kodekeg][$blok].") ".$kegiatan[$kodekeg]."</b></td>";
				// 	$view .= "<td align=right>".number_format($totPrestasi[$tglbkmx][$kodekeg],2)."</td>";
				// 	$view .= "<td align=right>".number_format($totHk[$tglbkmx][$kodekeg],2)."</td>";
				// 	$view .= "<td align=right>".number_format($totUpah[$tglbkmx][$kodekeg])."</td>";
				// 	$view .= "<td align=right>".number_format($totPremiBasis[$tglbkmx][$kodekeg])."</td>";
				// 	$view .= "<td align=right></td>";
				// 	$view .= "<td align=right>".number_format($totPremiLebih[$tglbkmx][$kodekeg])."</td>";
				// 	$view .= "<td align=right>".number_format($totextraFooding[$tglbkmx][$kodekeg])."</td>";
				// 	$view .= "<td align=right>".number_format($totPendapatanx[$tglbkmx][$kodekeg])."</td>";
				// $view .= "</tr>";

				// $gtotPrestasi += $totPrestasi[$tglbkmx][$kodekeg]; 
				// $gtotHk += $totHk[$tglbkmx][$kodekeg]; 
				// $gtotUpah += $totUpah[$tglbkmx][$kodekeg]; 
				// $gtotPremiBasis += $totPremiBasis[$tglbkmx][$kodekeg]; 
				// $gtotPremiLebih += $totPremiLebih[$tglbkmx][$kodekeg]; 
				// $gtotextraFooding += $totextraFooding[$tglbkmx][$kodekeg]; 
			}
		}
	}

	// #= Grand Total 
	// 	$view .= "<tr style='background-color:#c3f3fa'>";
	// 		$view .= "<td align=center colspan=8><b>GRANDTOTAL</b></td>";
	// 		$view .= "<td align=right>".number_format($gtotPrestasi,2)."</td>";
	// 		$view .= "<td align=right>".number_format($gtotHk,2)."</td>";
	// 		$view .= "<td align=right>".number_format($gtotUpah)."</td>";
	// 		$view .= "<td align=right>".number_format($gtotPremiBasis)."</td>";
	// 		$view .= "<td align=right></td>";
	// 		$view .= "<td align=right>".number_format($gtotPremiLebih)."</td>";
	// 		$view .= "<td align=right>".number_format($gtotextraFooding)."</td>";
	// 	$view .= "</tr>";
	// #= Akhir Grand Total

} else {
	$view .= "<tr class=rowcontent>";
	$view .= "<td align=center colspan=15><b style=color:red>Tidak ada data</b></td>";
	$view .= "</tr>";
}

$view .= "</tbody>";

$view .= "</table></div>";
#= Akhir Tabel
// echo "<pre>";
// print_r($karyawan);

switch ($proses) {
	case "getDivisi":

		$optDiv = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

		$where = "induk='" . $param['unit'] . "'";
		// $where .= " AND tipe in ('AFDELING','BIBITAN')";
		$where .= " AND kodeorganisasi in (" . getOrgDetail(26) . ")";

		$query = selectQuery($dbname, "organisasi", "kodeorganisasi,namaorganisasi", $where);
		$result = fetchData($query);

		foreach ($result as $val) {
			$optDiv .= "<option value=" . $val['kodeorganisasi'] . ">" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
		}

		echo $optDiv;
		break;

	case "preview":
		if ($param['tgl'] == "" || $param['tglx'] == "") {
			exit("Warning : Tanggal wajib di Isi !");
		}
		if ($param['unit'] == "") {
			exit('Warning: Unit usaha harus dipilih');
		}
		echo $view;
		break;

	case "excel":
		$nop = "Laporan Upah Dan Premi BKM Rawat.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet('Laporan Upah dan Premi BKM Rawat', $view);
		$xls->headers($nop);
		echo $xls->buildFile();
		break;
}
