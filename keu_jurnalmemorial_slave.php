<?php
// ini_set('display_errors',1);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/cekakun.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';

use Dompdf\Dompdf;


$method = checkPostGet('method', '');
$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

$stylehidden = "style='display:none'";
$path   = "fileupload/jm/";

$str = "select * from " . $dbname . ".setup_filesize where transaksi='keu_jurnalmemorial'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$filesize = $bar['filesize'];
}

$table = 'keu_jurnalmemorial';
$tabledt = 'keu_jurnalmemorialdt';
$tablevw = 'keu_jurnalmemorialdt_vw';

$tab = $tab2 = $countApp = '';
$no = $tdebet = $tkredit = 0;

$optunit = $optsupplier = $optsumberlain = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)='4'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmorganisasi[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
	$tipeorganisasi[$bar['kodeorganisasi']] = $bar['tipe'];
}
$str = "select * from " . $dbname . ".log_5supplier";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmsupplier[$bar['supplierid']] = $bar['namasupplier'];
}

$bgcolor = "bgcolor=gray";

$str = "select * from " . $dbname . ".keu_5akun";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmakun[$bar['noakun']] = $bar['namaakun'];
}

$str = "select * from " . $dbname . ".keu_5aruskas where level=3";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmaruskas[$bar['noaruskas']] = $bar['nama_aruskas'];
}

@$periodejurnal = substr(tanggalsystemn($param['tanggal']), 0, 7);

#= approval karyawan
$str = "select * from " . $dbname . ".datakaryawan  where karyawanid in (select karyawanid from " . $dbname . ".approval where jenispersetujuan='JM') ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$nmkaryawan[$bar['karyawanid']] = $bar['namakaryawan'];
}

switch ($method) {

	case 'getoptdetail';

		$optalokasi = $optvhc = $optadk = $optnik = $optakun = $optnoakun = $optkodekegiatan = $optkodeblok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		$str = "select * from " . $dbname . "." . $tabledt . " where nojurnal='" . $param['nojurnal'] . "' and nourut='" . $param['nourut'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodeasset = $bar['kodeasset'];
			$nik = $bar['nik'];
			$kodeblok = $bar['kodeblok'];
			$kodevhc = $bar['kodevhc'];
			$kodeorg = $bar['kodeorg'];
			$noakun = $bar['noakun'];
			$kodekegiatan = $bar['kodekegiatan'];
		}


		$str = "select * from " . $dbname . ".project where posting=0 and kodeorg='" . $param['kodeorg'] . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($kodeasset == $bar['kode']) {
				$optadk .= "<option value='" . $bar['kode'] . "' selected>" . $bar['kodeorg'] . " - " . $bar['kode'] . " - " . $bar['nama'] . "</option>";
			} else {
				$optadk .= "<option value='" . $bar['kode'] . "'>" . $bar['kodeorg'] . " - " . $bar['kode'] . " - " . $bar['nama'] . "</option>";
			}
		}

		$str = "select * from " . $dbname . ".datakaryawan where tanggalkeluar='0000-00-00' and lokasitugas='" . $param['kodeorg'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($nik == $bar['karyawanid']) {
				$optnik .= "<option value='" . $bar['karyawanid'] . "' selected>" . $bar['lokasitugas'] . " - " . $bar['nik'] . " - " . $bar['namakaryawan'] . "</option>";
			} else {
				$optnik .= "<option value='" . $bar['karyawanid'] . "'>" . $bar['lokasitugas'] . " - " . $bar['nik'] . " - " . $bar['namakaryawan'] . "</option>";
			}
		}

		$str = "select * from " . $dbname . ".vhc_5master where status=1 and substr(kodetraksi,1,4)='" . $param['kodeorg'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($kodevhc == $bar['kodevhc']) {
				$optvhc .= "<option value='" . $bar['kodevhc'] . "' selected>" . substr($bar['kodetraksi'], 0, 4) . " - " . $bar['kodevhc'] . " - " . $bar['nopol'] . " - " . $bar['detailvhc'] . "</option>";
			} else {
				$optvhc .= "<option value='" . $bar['kodevhc'] . "'>" . substr($bar['kodetraksi'], 0, 4) . " - " . $bar['kodevhc'] . " - " . $bar['nopol'] . " - " . $bar['detailvhc'] . "</option>";
			}
		}

		$str = "select * from " . $dbname . ".organisasi where kodeorganisasi like '" . $param['kodeorg'] . "%'  and length(kodeorganisasi)>'6'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($kodeblok == $bar['kodeorganisasi']) {
				$optalokasi .= "<option value='" . $bar['kodeorganisasi'] . "' selected>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			} else {
				$optalokasi .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			}
		}

		$arrtipeunit = [$param['kodeorg']];
		$tipeorganisasi[$param['kodeorg']] = $tipeorganisasi[$param['kodeorg']] == "KANWIL" ? "HOLDING" : $tipeorganisasi[$param['kodeorg']];
		// $str = "select * from " . $dbname . ".keu_5akun where jurnalmemorial='1'";
		$str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun WHERE a.kasbankdetail = 1 AND a.detail = 1 AND a.aktif = 1 AND a.level = '5' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('" . implode("','", $arrtipeunit) . "')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik = '{$tipeorganisasi[$param['kodeorg']]}' OR a.pemilik IN ('" . implode("','", $arrtipeunit) . "')))) GROUP BY a.noakun";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($noakun == $bar['noakun']) {
				$optnoakun .= "<option value='" . $bar['noakun'] . "' selected>" . $bar['noakun'] . " - " . $nmakun[$bar['noakun']] . "</option>";
			} else {
				$optnoakun .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $nmakun[$bar['noakun']] . "</option>";
			}
		}


		$str = "select * from " . $dbname . ".setup_kegiatan where status=1";
		// echo $str;exit("Error:A");
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($kodekegiatan == $bar['kodekegiatan']) {
				$optkegiatan .= "<option value='" . $bar['kodekegiatan'] . "' selected>" . $bar['kodekegiatan'] . " " . $bar['namakegiatan'] . " - " . $bar['kelompok'] . "</option>";
			} else {
				$optkegiatan .= "<option value='" . $bar['kodekegiatan'] . "'>" . $bar['kodekegiatan'] . " " . $bar['namakegiatan'] . " - " . $bar['kelompok'] . "</option>";
			}
		}

		echo $optadk . "###" . $optnik . "###" . $optvhc . "###" . $optalokasi . "###" . $optnoakun . "###" . $optkodekegiatan . "###" . $kodekegiatan . "###" . $kodeblok;
		break;

	case 'getkurs':
		$kurs = 1;
		if ($param['matauang'] != 'IDR') {
			$str = "select * from " . $dbname . ".setup_matauangrate where
				kode='" . $param['matauang'] . "' and daritanggal<='" . tanggalsystemn($param['tanggal']) . "' order by daritanggal desc limit 1 ";
			$res = fetchdata($str);
			$kurs = $res[0]['kurs'];
		}
		echo $kurs;

		break;

	case 'getkodekegiatanalokasi':


		$optkegiatan = $optalokasi = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		#= kegiatan
		$str = "select * from " . $dbname . ".setup_kegiatan where status=1 and noakun='" . $param['noakun'] . "'";

		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($param['kodekegiatan'] == $bar['kodekegiatan']) {
				$optkegiatan .= "<option value='" . $bar['kodekegiatan'] . "' selected>" . $bar['kodekegiatan'] . " " . $bar['namakegiatan'] . " - " . $bar['kelompok'] . "</option>";
			} else {
				$optkegiatan .= "<option value='" . $bar['kodekegiatan'] . "'>" . $bar['kodekegiatan'] . " " . $bar['namakegiatan'] . " - " . $bar['kelompok'] . "</option>";
			}
		}
		// echo $optkegiatan;exit("Error:A");

		if (substr($param['noakun'], 0, 3) == '621') {
			$wh = " and statusblok='TM'";
		} elseif (substr($param['noakun'], 0, 3) == '611') {
			$wh = " and statusblok='TM'";
		} elseif (substr($param['noakun'], 0, 3) == '126') {
			$wh = " and statusblok='TBM'";
		} elseif (substr($param['noakun'], 0, 5) == '12801') {
			$wh = " and statusblok='BBT' and kodeorg like '%PN%'";
		} elseif (substr($param['noakun'], 0, 5) == '12802') {
			$wh = " and statusblok='BBT' and kodeorg like '%MN%'";
		}


		// this is
		// cek dulu tipenya dari kodeorg
		$str = "select tipe from " . $dbname . ".organisasi where kodeorganisasi = '" . $param['kodeorg'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tipe = $bar['tipe'];
		}
		if ($tipe != 'PABRIK') {
			$str = "select * from " . $dbname . ".setup_blok where kodeorg like '" . $param['kodeorg'] . "%'  and length(kodeorg)>'6' " . $wh . "";
			// print_r($param);exit("Error".$str);
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($param['kodeblok'] == $bar['kodeorg']) {
					// $optalokasi.="<option value='".$bar['kodeorg']."' selected>".$bar['kodeorg']." - ".getNamaOrg($bar['kodeorg'])."</option>";
					$optalokasi .= "<option value='" . $bar['kodeorg'] . "' selected>" . $bar['kodeorg'] . " - " . getNamaOrg($bar['kodeorg']) . " - " . $bar['statusblok'] . "</option>";
				} else {
					$optalokasi .= "<option value='" . $bar['kodeorg'] . "'>" . $bar['kodeorg'] . " - " . getNamaOrg($bar['kodeorg']) . " - " . $bar['statusblok'] . "</option>";
				}
			}
		} else {
			$str = "select * from " . $dbname . ".organisasi where kodeorganisasi like '" . $param['kodeorg'] . "%' and tipe = 'STENGINE' ";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($param['kodeblok'] == $bar['kodeorg']) {
					$optalokasi .= "<option value='" . $bar['kodeorganisasi'] . "' selected>" . $bar['kodeorganisasi'] . " - " . getNamaOrg($bar['kodeorganisasi']) . "</option>";
				} else {
					$optalokasi .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . getNamaOrg($bar['kodeorganisasi']) . "</option>";
				}
			}
		}
		// this is
		//exit("error".$str);

		echo $optkegiatan . "###" . $optalokasi;

		break;

	case 'pdf':

		# Inisialisasi
		$tab = "";
		$subHeader = "";
		$tipesub = "";

		$subHeader  = "JURNAL MEMORIAL";


		$tab .= "<style>
			th, td {
				word-wrap: break-word;
				overflow-wrap: break-word;
			}
		
			.column-wrap {
				max-width: 40%;
			}
		</style>";

		# Style
		$borderht = 0;
		$borderdt = 1;
		$fontsizeht = 'font-size:18px;';
		$fontsizedt = 'font-size:13px;';

		$styledotted = "<style>
							.dotted-underline {
								border-bottom: 1px dotted black;
								display: inline;
							}
						</style>";

		# Make Option
		$indukmo = makeOption($dbname, "organisasi", "kodeorganisasi,induk");
		$kodeptmo = makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi", "tipe='PT'");
		$namaakunmo = makeOption($dbname, "keu_5akun", "noakun,namaakun");
		$nmkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$nxkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');

		# Select Data
		$sql = "SELECT * FROM $dbname.keu_jurnalmemorialdt_vw WHERE nojurnal='" . $param['nojurnal'] . "'";
		$resht = fetchData($sql, "OBJECT")[0];
		$resdt = fetchData($sql, "OBJECT");

		# Data Result
		$kodeorganisasiht 	= $resht->kodeorg;
		$kodeptht			= $kodeptmo[$indukmo[$resht->kodeorg]];
		$noakunht			= $resht->noakun;
		$aruskasht			= $resht->noaruskas;
		$keteranganht		= $resht->keterangan;
		$jumlahht			= $resht->jumlah;
		$tanggalht			= $resht->tanggal;
		$dibuatoleh			= $nmkaryawan[$resht->createby];
		$noreferensiht		= $resht->noreferensi;

		# Logo
		$arrHead	= setheadreport($indukmo[$kodeorganisasiht], $kodeorganisasiht);
		$path		= $arrHead['logo'];

		# Approval
		$whrnotransaksi = "AND notransaksi='" . $param['nojurnal'] . "'";

		$sql = "SELECT * FROM {$dbname}.approval WHERE 5=5 {$whrnotransaksi}";
		$res = fetchData($sql, "OBJECT");

		if (count($res) > 0) {
			$nos = 0;
			foreach ($res as $v) {
				$nos++;
				$arrdataapp[$v->level][$nos][$v->karyawanid] = $v->karyawanid;
			}
		}


		$tab .= "<table border='" . $borderht . "' style='width:100%;'>";
		$tab .= "<tr>";
		$tab .= "<td rowspan=4 align=center style='width:180px;'><img src='" . $path . "' style='width:180px;height:100px;' /></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td><b style='margin-left: 100px;'>" . $kodeptht . "</b></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td><u><b style='margin-left: 140px;" . $fontsizedt . "'>" . $subHeader . "</b></u></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td><span style='margin-left: 150px;" . $fontsizedt . "' class='dotted-underline'>Nomor : " . $param['nojurnal'] . "</span></td>";
		$tab .= "</tr>";

		# Spasi
		$tab .= "<tr><td style='height:20px;'></td></tr>";

		$tab .= "</table>";

		$tab .= "<table width=100%>";
		# Tanggal
		$tab .= "<tr>";
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% style='" . $fontsizedt . "'>Tgl</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . "' align=right>:</td>";
		$tab .= "<td width=30% style='" . $fontsizedt . "'>" . $tanggalht . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";

		# Slide kanan
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% align=left style='" . $fontsizedt . "'>Departemen</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . "'>:</td>";
		$tab .= "<td width=30% align=left style='" . $fontsizedt . "'>-</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";
		$tab .= "</tr>";

		# Keterangan
		$tab .= "<tr>";
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% style='" . $fontsizedt . ";vertical-align:top!important;'>Ket</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . ";vertical-align:top!important;' align=right>:</td>";
		$tab .= "<td width=30% style='" . $fontsizedt . "'>" . $keteranganht . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";

		# Slide kanan
		$tab .= "<td width=48.5%>";
		$tab .= "<table border='" . $borderht . "' style='width:100%'>";
		$tab .= "<tr>";
		$tab .= "<td width=10% align=left style='" . $fontsizedt . "'>Unit Kerja</td>";
		$tab .= "<td width=5% align=center style='" . $fontsizedt . "'>:</td>";
		$tab .= "<td width=30% align=left style='" . $fontsizedt . "'>" . $kodeorganisasiht . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</td>";
		$tab .= "</tr>";

		$tab .= "</table>";

		# Spasi
		$tab .= "<br/><br/>";

		# =========================================================== #
		# TABEL DETAIL
		# =========================================================== #
		$tab .= "<table border='" . $borderdt . "' style='width:100%' cellpadding=3 cellspacing=0>";
		$tab .= "<thead>";
		$tab .= "<tr>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:5%;' rowspan=2><b>No.</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:12%;' rowspan=2><b>No. Akun</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . "' rowspan=2><b>Nama Akun</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . "' rowspan=2><b>Keterangan</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . "' colspan=3><b>Nominal</b></td>";
		$tab .= "</tr>";

		$tab .= "<tr>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:6%;'><b>Curr</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:15%;'><b>Debet</b></td>";
		$tab .= "<td align=center style='" . $fontsizedt . ";width:15%;'><b>Kredit</b></td>";
		$tab .= "</tr>";
		$tab .= "</thead>";

		$tab .= "<tbody>";

		# Tabel Data
		$nodt = 0;
		foreach ($resdt as $val):
			$nodt++;
			$tab .= "<tr>";
			$tab .= "<td>" . $nodt . "</td>";
			$tab .= "<td>" . $val->noakun . "</td>";
			$tab .= "<td>" . $nmakun[$val->noakun] . "</td>";
			$tab .= "<td>" . $val->keterangan . "</td>";
			$tab .= "<td align=center>Rp.</td>";
			$tab .= "<td align=right>" . hidezerodecimal($val->debet, 2) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($val->kredit, 2) . "</td>";
			$tab .= "</tr>";

			$totalDebet += $val->debet;
			$totalKredit += $val->kredit;
		endforeach;

		# Tabel Row Total
		$tab .= "<tr>";
		$tab .= "<td style='" . $fontsizedt . "' align=center colspan=4><b>Total</b></td>";
		$tab .= "<td style='" . $fontsizedt . "' align=center><b>Rp.</b></td>";
		$tab .= "<td style='" . $fontsizedt . "' align=right><b>" . hidezerodecimal($totalDebet) . "</b></td>";
		$tab .= "<td style='" . $fontsizedt . "' align=right><b>" . hidezerodecimal($totalKredit) . "</b></td>";
		$tab .= "</tr>";

		$tab .= "</tbody>";
		$tab .= "</table>";
		# =========================================================== #
		# END - TABEL DETAIL
		# =========================================================== #

		# Spasi
		$tab .= "<br/><br/>";

		# =========================================================== #
		# TABEL TTD
		# =========================================================== #
		$tab .= "<table border='" . $borderdt . "' width='100' cellpadding=4 cellspacing=0>";
		$tab .= "<thead>";
		$tab .= "<tr>";
		$tab .= "<th style='width:150px;" . $fontsizedt . "' align=center><b>Dibuat,</b></th>";
		$tab .= "<th style='width:150px;" . $fontsizedt . "' align=center><b>Diperiksa,</b></th>";
		$tab .= "<th style='width:150px;" . $fontsizedt . "' align=center><b>Disetujui,</b></th>";
		$tab .= "</tr>";
		$tab .= "</thead>";

		$tab .= "<tbody>";
		# Kolom TTD
		$tab .= "<tr>";
		$tab .= "<td style='height:70px!important;'></td>";
		$tab .= "<td style='height:70px!important;'></td>";
		$tab .= "<td style='height:70px!important;'></td>";
		$tab .= "</tr>";
		# Kolom Nama
		$tab .= "<tr>";
		$tab .= "<td style='height:20px!important;" . $fontsizedt . "' align=center>" . $dibuatoleh . "</td>";

		if (count($arrdataapp) <= 1) {
			$tab .= "<td style='height:20px!important;" . $fontsizedt . "' align=center></td>";
		}

		if (count($arrdataapp) > 0) {
			foreach ($arrdataapp as $level => $data) {
				foreach ($data as $nomor => $datax) {
					foreach ($datax as $karyid => $val) {
						$tab .= "<td style='height:20px!important;" . $fontsizedt . "' align=center>" . $nmkaryawan[$arrdataapp[$level][$nomor][$karyid]] . "</td>";
					}
				}
			}
		} else {
			$tab .= "<td style='height:20px!important;" . $fontsizedt . "' align=center></td>";
		}

		$tab .= "</tr>";
		$tab .= "</tbody>";
		$tab .= "</table>";
		# =========================================================== #
		# END - TTD
		# =========================================================== #


		$dompdf = new Dompdf();
		$dompdf->load_html($tab);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$dompdf->stream("jurnalmemorial", array("Attachment" => 0));
		break;

	case 'pdfori':


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
			footer .pagenum:before {
				content: counter(page);
			}
			
		</style>";

		$cellpadding = 1;
		$cellspacing = 0;
		$sizefont = '10';

		$str = "select * from " . $dbname . "." . $table . " where nojurnal='" . $param['nojurnal'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodeunit = $bar['kodeorg'];
			$nojurnal = $bar['nojurnal'];
			$noreferensi = $bar['noreferensi'];
		}

		$tab .= "<div style='page-break-after: always;'>";

		$arrkodept = setheadreport($kodeunit);

		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0	>"; //logoheight logowidth
		$tab .= "<tr>";
		$tab .= "<td style='width:35px;' align=center><img src=" . $arrkodept['logo'] . " style='width:" . (floatval($arrkodept['logowidth']) - 60) . ";height:" . (floatval($arrkodept['logoheight']) - 60) . "'></td>";
		$tab .= "<td align=left valign=top style='width:400px;font-size:" . ($sizefont + 2) . "px'><b>" . $arrkodept['nama'] . "</b><br>" . $arrkodept['alamat'] . "<br>" . $arrkodept['telepon'] . "</td>";
		$tab .= "<td style='width:35px;'>&nbsp;</td>";
		$tab .= "</tr>";
		// $tab.="<tr>";
		// $tab.="<td style='text-align:left;font-size:".($sizefont+2)."px'>".$arrkodept['alamat']."</td>"; 
		// $tab.="<td>&nbsp;</td>";
		// $tab.="</tr>";
		$tab .= "</table>";

		$tab .= "<hr>";
		$tab .= "<table style='font-size:" . ($sizefont) . "px' cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . ">";
		$tab .= "<tr class=rowheader>
					<th align=left>" . $_SESSION['lang']['nojurnal'] . "</th>
					<th align=center>:</th>
					<th align=left>" . $nojurnal . "</th>
                </tr>";
		$tab .= "<tr class=rowheader>
					<th align=left>" . $_SESSION['lang']['noreferensi'] . "</th>
					<th align=center>:</th>
					<th align=left>" . $noreferensi . "</th>
                </tr>";
		$tab .= "</table>";
		$tab .= "<br>";
		$tab .= "<table width=100% style='font-size:" . ($sizefont) . "px' cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=1>"; //logoheight logowidth
		$tab .= "<thead>
                <tr class=rowheader  " . $bgcolor . ">
					<th align=center>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>
                    <th align=center>" . $_SESSION['lang']['nodok'] . "</th>
                    <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
					<th align=center>" . $_SESSION['lang']['debet'] . "</th>
                    <th align=center>" . $_SESSION['lang']['kredit'] . "</th>
                </tr></thead>";
		$str = "select * from " . $dbname . "." . $tablevw . " where nojurnal='" . $nojurnal . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$no++;
			$tab .= "<tr>";
			$tab .= "<td valign=top>" . $bar['noakun'] . "</td>";
			$tab .= "<td valign=top>" . getNamaAkun($bar['noakun']) . "</td>";
			$tab .= "<td valign=top>" . $bar['nodok'] . "</td>";
			$tab .= "<td valign=top>" . nl2br($bar['keterangan']) . "</td>";
			$tab .= "<td align=right valign=top>" . hidezerodecimal($bar['debet'], 2) . "</td>";
			$tab .= "<td align=right valign=top>" . hidezerodecimal($bar['kredit'], 2) . "</td>";
			$tab .= "</b></tr>";
			$tdebet += $bar['debet'];
			$tkredit += $bar['kredit'];
		}
		$tab .= "<tr class=rowheader>";
		$tab .= "<th valign=top align=center colspan=4>" . $_SESSION['lang']['total'] . "</th>";
		$tab .= "<th align=right valign=top>" . hidezerodecimal($tdebet, 2) . "</th>";
		$tab .= "<th align=right valign=top>" . hidezerodecimal($tkredit, 2) . "</th>";
		$tab .= "</tr>";

		$tab .= "</table>";


		# ============================================ #
		# Approval
		# ============================================ #
		$arrpersetujuan = array("1" => "Dibuat Oleh", "2" => "Disetujui Oleh");

		$sql = selectQuery($dbname, "approval", "*", "notransaksi='" . $nojurnal . "'");
		$res = fetchData($sql);

		if (count($res) > 0):
			$tab .= "<table width=100% style='font-size:" . ($sizefont) . "px;margin-top:100px;' cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>";
			$tab .= "<tr class=rowcontent>";
			foreach ($res as $val):
				$tab .= "<td align=center>" . $arrpersetujuan[$val['level']] . "</td>";
			endforeach;
			$tab .= "</tr>";

			$tab .= "<tr class=rowcontent>";
			foreach ($res as $val):
				$tab .= "<td align=center style='height: 100px;'></td>";
			endforeach;
			$tab .= "</tr>";

			$tab .= "<tr class=rowcontent>";
			foreach ($res as $val):
				$tab .= "<td align=center>_____________________________</td>";
			endforeach;
			$tab .= "</tr>";

			$tab .= "<tr class=rowcontent>";
			foreach ($res as $val):
				$tab .= "<td align=center style='color:transparent;'>" . getNamaKaryawan($val['karyawanid']) . "</td>";
			endforeach;
			$tab .= "</tr>";
			$tab .= "</table>";
		endif;


		// exit("Error");
		$tab .= "<footer>";
		$tab .= "<div style='font-size:" . ($sizefont) . "px' class=pagenum-container>Page <span class=pagenum></span>";
		$tab .= "<br>" . $_SESSION['empl']['name'] . "<br>" . updatetimedata(date('Y-m-d H:i:s')) . "</div>";
		$tab .= "</footer>";

		$tab .= "</div>"; //div page break, untuk halaman berikutnya


		$dompdf = new Dompdf();
		$dompdf->load_html($tab);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$dompdf->stream("jurnalmemorial", array("Attachment" => 0));
		break;


	case 'editht':
		$str = "select * from " . $dbname . "." . $table . "  where nojurnal='" . $param['nojurnal'] . "'";
		$res = fetchdata($str);
		echo
		$res[0]['nojurnal'] . "###" .
			$res[0]['kodeorg'] . "###" .
			tanggalnormal($res[0]['tanggal']) . "###" .
			$res[0]['matauang'] . "###" .
			hidezerodecimal($res[0]['kurs'], 2) . "###" .
			$res[0]['noreferensi'] . "###" .
			$res[0]['revisi'];
		break;

	case 'editdt':
		$str = "select * from " . $dbname . "." . $tabledt . "  where nojurnal='" . $param['nojurnal'] . "' and nourut='" . $param['nourut'] . "'";
		// echo $str;
		$res = fetchdata($str);
		echo
		$res[0]['nodok'] . "###" .
			$res[0]['noakun'] . "###" .
			hidezerodecimal($res[0]['jumlah'], 2) . "###" .
			$res[0]['keterangan'] . "###" .
			$res[0]['kodekegiatan'] . "###" .
			$res[0]['kodeasset'] . "###" .
			$res[0]['nik'] . "###" .
			$res[0]['kodecustomer'] . "###" .
			$res[0]['kodesupplier'] . "###" .
			$res[0]['kodevhc'] . "###" .
			$res[0]['kodeblok'] . "###" .
			$res[0]['nourut'];
		// exit("Error:A");
		break;



	case 'loaddata':
		$where = '1=1';

		$arrunit = array();
		$arrunit = getOrgDetail(1);
		foreach ($arrunit as $val => $nama) {
			$dtunit[$val] = $val;
		}

		$where .= " and  kodeorg in ('" . implode("','", $dtunit) . "') ";

		if ($param['tanggalmulai'] != '' and $param['tanggalselesai'] != '') {
			$where .= " and tanggal between '" . tanggalsystemn($param['tanggalmulai']) . "' and '" . tanggalsystemn($param['tanggalselesai']) . "'";
		}
		if ($param['nojurnal'] != '') {
			$where .= " and nojurnal like '%" . trim($param['nojurnal']) . "%'";
		}
		if ($param['kodeorg'] != '') {
			$where .= " and kodeorg = '" . $param['kodeorg'] . "'";
		}
		if ($param['noreferensi'] != '') {
			$where .= " and noreferensi like '%" . trim($param['noreferensi']) . "%'";
		}
		if ($param['statsch'] != '') {
			$where .= " and posting = '" . $param['statsch'] . "'";
		}
		if ($param['revisi'] != '') {
			$where .= " and revisi = '" . $param['revisi'] . "'";
		}
		if ($param['tipetransaksi'] != '') {
			if ($param['tipetransaksi'] == 'JM') {
				$where .= " and revisi ='0'";
			} else {
				$where .= " and revisi != '0'";
			}
		}
		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$colspan = 16;
		$maxdisplay = ($page * $limit);
		$offset = $page * $limit;
		$str = "select count(*) as jumrow from " . $dbname . "." . $table . " where " . $where . "";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jumrow = $bar['jumrow'];
		}


		$no = 0;
		$no = $maxdisplay;
		$str = "select * from " . $dbname . "." . $table . " where " . $where . " group by nojurnal order by tanggal desc,nojurnal desc limit " . $offset . "," . $limit . " ";
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
						WHERE notransaksi = '" . $bar['nojurnal'] . "' AND " . $whereapp . "
						ORDER BY level " . $order . " LIMIT 1";
				$res = fetchdata($str);
				$statusapp = $ket . "<br> (" . $res[0]['namakaryawan'] . ")";
			}

			$str = "select sum(debet) as debet,sum(kredit) as kredit,
				nojurnal,tanggal,sum(debet)-sum(kredit) as balance,noreferensi
				from " . $dbname . "." . $tablevw . " where nojurnal='" . $bar['nojurnal'] . "'";
			$res = fetchdata($str);
			$bgcolor = 'class=rowcontent';
			$no++;
			$tab .= "<tr " . $bgcolor . ">";
			$tab .= "<td align=center valign=top>" . $no . "</td>";
			$tab .= "<td valign=top>" . $bar['nojurnal'] . "</td>";
			if ($bar['revisi'] == '0') {
				$tipetransaksi = $_SESSION['lang']['jurnalmemo'];
			} else {
				$tipetransaksi = $_SESSION['lang']['jurnaladjustment'];
			}
			$tab .= "<td valign=top>" . $tipetransaksi . "</td>";
			$tab .= "<td valign=top align=center>" . $bar['revisi'] . "</td>";
			$tab .= "<td valign=top>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td valign=top>" . $bar['kodeorg'] . "</td>";

			$tab .= "<td align=right valign=top>" . hidezerodecimal($res[0]['debet'], 2) . "</td>";
			$tab .= "<td align=right valign=top>" . hidezerodecimal($res[0]['kredit'], 2) . "</td>";
			$tab .= "<td align=right valign=top>" . hidezerodecimal($res[0]['balance'], 2) . "</td>";
			$tab .= "<td align=center valign=top>" . $bar['noreferensi'] . "</td>"; //createtime

			$tab .= "<td valign=top align=center>" . getKary($bar['createby']) . "<br><font style='font-size:10px;'>" . updatetimedata($bar['createtime']) . "</font></td>";
			$tab .= "<td valign=top align=center>" . getKary($bar['updateby']) . "<br><font style='font-size:10px;'>" . updatetimedata($bar['updatetime']) . "</font></td>";
			$tab .= "<td valign=top align=center><label style='cursor:pointer;color:blue' onclick=\"gethistoriapproval('" . $bar['nojurnal'] . "',event,'JM')\">" . $statusapp . "</label></td>";


			if ($bar['posting'] == 0 || $bar['posting'] == 2) {
				$tab .= "<td align=center valign=top><img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('" . $bar['nojurnal'] . "');\"></td>";
				$tab .= "<td align=center valign=top><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('" . $bar['nojurnal'] . "');\"></td>";
				// $tab.="&nbsp;<img src=images/icons/04/16/01.png class=zImgBtn height='30'  title='Ajukan Persetujuan' onclick=\"ajukan('".$bar['nojurnal']."','".$page."');\">";							
			} else if ($bar['posting'] == 9) {
				$tab .= "<td align=center valign=top></td>";
				$tab .= "<td align=center valign=top><img src=images/icons/04/16/04.png class=zImgBtn height='30'  title='Proses Persetujuan'></td>";
			} else {
				$tab .= "<td align=center valign=top></td>";
				$tab .= "<td align=center valign=top><img src=images/icons/04/16/02.png  class=zImgBtn height='30'  title='Disetujui/Tolak' ></td>";
			}
			$tab .= "<td align=center valign=top><img img src=images/skyblue/zoom.png class=zImgBtn height='30'  title='Ajukan Persetujuan' onclick=\"ajukan('" . $bar['nojurnal'] . "','" . $bar['kodeorg'] . "','" . $page . "');\"></td>";

			// $tab.="&nbsp;<img src=images/skyblue/zoom.png class=zImgBtn height='30'  title='Lihat Data' onclick=\"html('".$bar['nojurnal']."','".$page."');\">";											

			$tab .= "<td align=center valign=top><img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF " . $bar['nojurnal'] . "' onclick=\"pdf('" . $bar['nojurnal'] . "');\"></td>";
			$tab .= "<td align=center valign=top><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30' title='Excel Data Detail' onclick=\"excelJurnalmemorial('" . $bar['nojurnal'] . "');\" ></td>";
			$tab .= "</tr>";
			// exit("Error:A");
		}
		$tab2 .= createpaging($jumrow, $limit, $page, $colspan, 'loaddata', 'getpage');
		$tab .= "</table>";
		echo $tab . "####" . $tab2;
		break;


	case 'deleteht':
		$str = "delete from " . $dbname . "." . $table . " where nojurnal='" . $param['nojurnal'] . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'saveht';



		if ($param['tanggal'] == '') {
			exit("Warning:Tanggal masih kosong");
		}
		if ($param['kodeorg'] == '') {
			exit("Warning:Unit masih kosong");
		}
		if ($param['noreferensi'] == '') {
			exit("Warning:No. Referensi masih kosong");
		}
		if ($param['tipetransaksi'] == '') {
			exit("Warning:Tipe Jurnal masih kosong");
		}

		#= jika tipetransaksi JM maka revisi harus 0
		#= jika tipetransaksi adjust audit maka tidak boleh 0

		if ($param['tipetransaksi'] == 'JA') {
			if ($param['revisi'] == '0') {
				exit("Warning:Untuk jurnal adjustment revisi diperbolehkan 1-5");
			}
		}




		try {

			$owlPDO->beginTransaction();


			#= kalau kosong bentuk nomor jurnal
			if ($param['nojurnal'] == '') {

				$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $param['kodeorg'] . "' and periode='" . $periodejurnal . "' and tutupbuku=0 order by periode asc limit 1 ";
				$res = fetchdata($str);
				$tanggalmulai = $res[0]['tanggalmulai'];
				$tanggalsampai = $res[0]['tanggalsampai'];


				if ($tanggalmulai > tanggalsystemn($param['tanggal'])) {
					exit("Warning : Tanggal Transaksi : " . $param['tanggal'] . " melebihi periode aktif, 
						periode aktif untuk unit " . $param['kodeorg'] . " : " . tanggalnormal($tanggalmulai) . " s/d " . tanggalnormal($tanggalsampai) . " ");
				}

				$kodejurnal = 'M';
				$query = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $periodejurnal . "'"
				);
				$tmpKonter = fetchData($query);
				$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
				# Prep No Jurnal
				$param['nojurnal'] = str_replace('-', '', tanggalsystemn($param['tanggal'])) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;


				$str = "insert into " . $dbname . "." . $table . " (nojurnal,kodejurnal,tanggal,tanggalentry,noreferensi,matauang,kurs,createby,createtime,updateby,autojurnal,kodeorg,revisi) 
				values 
				('" . $param['nojurnal'] . "','" . $kodejurnal . "','" . tanggalsystemn($param['tanggal']) . "','" . date('Ymd') . "','" . $param['noreferensi'] . "',
				'" . $param['matauang'] . "','" . $param['kurs'] . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "',
				'0','" . $param['kodeorg'] . "','" . $param['revisi'] . "')";
				$owlPDO->exec($str);

				#= update counter jurnal
				$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
					kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $periodejurnal . "' ";
				$owlPDO->exec($str);
			} else {

				$str = "update " . $dbname . "." . $table . " set 
				tanggal='" . tanggalsystemn($param['tanggal']) . "',
				matauang='" . $param['matauang'] . "',
				kurs='" . $param['kurs'] . "',
				kodeorg='" . $param['kodeorg'] . "',
				noreferensi='" . $param['noreferensi'] . "',
				revisi='" . $param['revisi'] . "',
				updateby='" . $_SESSION['standard']['userid'] . "' 
				where nojurnal = '" . $param['nojurnal'] . "'";
				$owlPDO->exec($str);

				#= update juga dt untuk noref dan tanggal
				$str = "update " . $dbname . "." . $tabledt . " set 
				tanggal='" . tanggalsystemn($param['tanggal']) . "',
				noreferensi='" . $param['noreferensi'] . "',
				revisi='" . $param['revisi'] . "',
				updateby='" . $_SESSION['standard']['userid'] . "',
				updatetime='" . date('Y-m-d H:i:s') . "'				
				where nojurnal = '" . $param['nojurnal'] . "'";
				$owlPDO->exec($str);
			}

			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
		echo $param['nojurnal'];

		break;


	case 'deletedt':
		$str = "delete from " . $dbname . "." . $tabledt . " where nojurnal='" . $param['nojurnal'] . "' and nourut='" . $param['nourut'] . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;


	case 'savedt';

		if ($param['noakun'] == '') {
			exit("Warning:Noakun masih kosong");
		}
		if ($param['jumlah'] == '' || $param['jumlah'] == '0') {
			exit("Warning:Nilai masih kosong");
		}

		if ($param['keterangan'] == '') {
			exit("Warning:Ketarangan masih kosong");
		}

		$param['jumlah'] = str_replace(',', '', $param['jumlah']);

		// if($_SESSION['standard']['username']=='tim.owl3'){
		// echo"<pre>";
		// print_r($param);
		// exit("error:a");
		// }

		// cekakunkb2($noakun,$param['kodekegiatan'],$param['kodeasset'],$param['nik'],$param['kodecustomer'],$param['kodesupplier'],$param['kodevhc'],$param['kodeblok'],$param['nodok']);

		cekakunkb($param['noakun'], $param['kodekegiatan'], $param['kodeasset'], $param['nik'], $param['kodecustomer'], $param['kodesupplier'], $param['kodevhc'], $param['kodeblok'], '', $param['nodok']);

		try {

			$owlPDO->beginTransaction();

			#= kalau kosong bentuk nomor jurnal
			if ($param['methoddt'] == 'insert') {

				#= ambil nomor max
				$str = "select max(nourut) as nourut from " . $dbname . "." . $tabledt . " where  nojurnal='" . $param['nojurnal'] . "'";
				$res = fetchdata($str);
				$param['nourut'] = $res[0]['nourut'] + 1;

				// exit("Error:".$param['keterangan']);
				if ($param['ketjumlah'] == 'debet') {
					$jmlh = $param['jumlah'];
				} else if ($param['ketjumlah'] == 'kredit') {
					$jmlh = "-" . $param['jumlah'];
				}

				$str = "insert into " . $dbname . "." . $tabledt . " 
					(nojurnal,tanggal,nourut,noakun,keterangan,
					 jumlah,matauang,kurs,kodeorg,kodekegiatan,
					 kodeasset,kodebarang,nik,kodecustomer,kodesupplier,
					 noreferensi,noaruskas,kodevhc,nodok,kodeblok,
					 revisi,kodesegment,createby,createtime,updateby) 
					values 
					('" . $param['nojurnal'] . "','" . tanggalsystemn($param['tanggal']) . "','" . $param['nourut'] . "','" . $param['noakun'] . "','" . $param['keterangan'] . "',
					'" . $jmlh . "','" . $param['matauang'] . "','" . $param['kurs'] . "','" . $param['kodeorg'] . "','" . $param['kodekegiatan'] . "',
					'" . $param['kodeasset'] . "','" . $param['kodebarang'] . "','" . $param['nik'] . "','" . $param['kodecustomer'] . "','" . $param['kodesupplier'] . "',
					'" . $param['noreferensi'] . "','" . $param['noaruskas'] . "','" . $param['kodevhc'] . "','" . $param['nodok'] . "','" . $param['kodeblok'] . "',
					'" . $param['revisi'] . "','" . $param['kodesegment'] . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "')";
				$owlPDO->exec($str);
			} else {

				if ($param['ketjumlah'] == 'debet') {
					$jmlh = $param['jumlah'];
				} else if ($param['ketjumlah'] == 'kredit') {
					$jmlh = "-" . $param['jumlah'];
				}

				$str = "update " . $dbname . "." . $tabledt . " set 
				noakun='" . $param['noakun'] . "',
				keterangan='" . $param['keterangan'] . "',
				jumlah='" . $jmlh . "',
				matauang='" . $param['matauang'] . "',
				kurs='" . $param['kurs'] . "',
				kodeorg='" . $param['kodeorg'] . "',
				kodekegiatan='" . $param['kodekegiatan'] . "',
				kodeasset='" . $param['kodeasset'] . "',
				kodebarang='" . $param['kodebarang'] . "',
				nik='" . $param['nik'] . "',
				kodecustomer='" . $param['kodecustomer'] . "',
				kodesupplier='" . $param['kodesupplier'] . "',
				noreferensi='" . $param['noreferensi'] . "',
				noaruskas='" . $param['noaruskas'] . "',
				kodevhc='" . $param['kodevhc'] . "',
				nodok='" . $param['nodok'] . "',
				kodeblok='" . $param['kodeblok'] . "',
				revisi='" . $param['revisi'] . "',
				kodesegment='" . $param['kodesegment'] . "',
				updateby='" . $_SESSION['standard']['userid'] . "'
				where nojurnal = '" . $param['nojurnal'] . "' and nourut = '" . $param['nourut'] . "'";
				// exit("Error:$str");
				$owlPDO->exec($str);
			}


			$str = "update " . $dbname . "." . $table . " set updateby='" . $_SESSION['standard']['userid'] . "' where nojurnal = '" . $param['nojurnal'] . "' ";
			// exit("Error:".$str);
			$owlPDO->exec($str);


			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}


		break;



	case 'loaddatadt':
		$sisa = 0;
		$str = "select * from " . $dbname . "." . $tabledt . "  where nojurnal='" . $param['nojurnal'] . "' ORDER BY jumlah DESC";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {

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


			$strdt = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $bar['kodecustomer'] . "' ";
			$resdt = fetchdata($strdt);
			@$namacustomer = $resdt[0]['namacustomer'];

			$strdt = "select * from " . $dbname . ".vhc_5master where kodevhc='" . $bar['kodevhc'] . "' ";
			$resdt = fetchdata($strdt);
			@$nopol = $resdt[0]['nopol'];
			@$namavhc = $resdt[0]['detailvhc'];

			if ($bar['jumlah'] >= 0) {
				$debet = $bar['jumlah'];
				$kredit = 0;
			} else {
				$debet = 0;
				$kredit = $bar['jumlah'];
			}

			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=left>" . $bar['nodok'] . "</td>";
			$tab .= "<td align=left>" . $bar['noakun'] . " " . @$nmakun[$bar['noakun']] . "</td>";
			$tab .= "<td align=right>" . number_format($debet, 2) . "</td>";
			$tab .= "<td align=right>" . number_format($kredit, 2) . "</td>";
			$tab .= "<td align=left>" . $bar['keterangan'] . "</td>";
			$tab .= "<td align=left>" . $bar['kodekegiatan'] . " " . @$namakegiatan . "</td>";
			$tab .= "<td align=left>" . $bar['kodeasset'] . " " . @$namaproject . "</td>";
			$tab .= "<td align=left>" . $nikkaryawan . " " . @$namakaryawan . "</td>";
			$tab .= "<td align=left>" . $bar['kodecustomer'] . " " . @$namacustomer . "</td>";
			$tab .= "<td align=left>" . $bar['kodesupplier'] . " " . @$nmsupplier[$bar['kodesupplier']] . "</td>";
			$tab .= "<td align=left>" . $bar['kodevhc'] . " " . $nopol . " " . @$namavhc . "</td>";
			$tab .= "<td align=left>" . $bar['kodeblok'] . "</td>";
			$tab .= "<td align=center  valign=top>";
			$tab .= "<img src=images/application/application_edit.png class=zImgBtn caption='Edit' onclick=\"editdt('" . $bar['nojurnal'] . "','" . $bar['nourut'] . "');\">";
			$tab .= "&nbsp;<img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' 
					onclick=\"deletedt('" . $bar['nojurnal'] . "','" . $bar['nourut'] . "');\">";
			$tab .= "</td>";
			$tab .= "</tr>";

			$tdebet += $debet;
			$tkredit += $kredit;
		}

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td colspan=2 align=center>" . $_SESSION['lang']['total'] . "</td>";
		$tab .= "<td align=right>" . hidezerodecimal($tdebet, 2) . "</td>";
		$tab .= "<td align=right>" . hidezerodecimal($tkredit, 2) . "</td>";
		$tab .= "<td colspan=9></td>";
		$tab .= "</tr>";

		echo $tab;
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
	/*
	case'submitfile':
        $tgl = date("YmdHis");
        $his = date("His");
        $nmTemp=str_replace('-','',str_replace('/','',$param['nojurnal']));
        // echo"<pre>";
        // print_r($_FILES['file']);
        // echo"</pre>";
        // exit('error');
		if($param['fileupload']!=''){
			if($_FILES['file']['error']==0){    
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $param['kriteriaefil']."_".$nmTemp."_".$his."".$filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
				// exit("Error:".$path);
				// listfile_keu_kasbank
				// listfileupload
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfileupload values ('','".$param['nojurnal']."','".$filename."','".$filetype."','".$param['kriteriaefil']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
					try{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}else{
					exit("Warning : Format file upload tidak boleh ".$filetype);
				}
			}
		}
    break;
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
				throw new PDOException("Ukuran File melebihi " . number_format($filesize / 1024) . " KB; ukuran file ini " . number_format($_FILES['file']['size'] / 1024, 2) . " Kb");
			}

			if ($param['fileupload'] != '') {
				if ($_FILES['file']['error'] == 0) {
					$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
					$filename = $param['kriteriaefil'] . "_" . $nmTemp . "_" . $his . "" . $filetype;
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
					if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.rar') || ($filetype == '.gz') || ($filetype == '.zip') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
						$str = "insert into " . $dbname . ".listfileupload values ('','" . $param['nojurnal'] . "','" . $filename . "','" . $filetype . "','" . $param['kriteriaefil'] . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";

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
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['nojurnal'] . "' ";
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
			$form .= "<td align=right>" . ukurandokumen(filesize($path . str_replace('/', '', $bar['namafile']))) . "</td>";
			$form .= "<td align=center><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a></td>";
			$form .= "<td align=center>&nbsp<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $bar['notransaksi'] . "','" . $bar['namafile'] . "');\" ></td>";

			$form .= "</tr>";
		}
		echo $form;
		break;

	/*
	case'loadfiles':
		$form='';
		$str="select * from ".$dbname.".listfileupload where notransaksi='".$param['nojurnal']."' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form.= "<tr class=rowcontent >";
				$form.="<td style='text-align:center'>".$no."</td>";
				$form.="<td align='center'><img src=".$icon." class=resicon></a></td>";
				$form.= "<td>".$bar['kriteriaefil']."</td>";
				$form.= "<td><a href='".$path.$bar['namafile']."' download>".$bar['namafile']."</td>";
				$form.= "<td><a href='".$path.$bar['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$bar['notransaksi']."','".$bar['namafile']."');\" ></td>";
			$form.= "<tr>";
		}
		echo $form;
    break;  
	*/

	case 'deletefile':
		$namafile = $param['namafile'];
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['nojurnal'] . "' and namafile='" . $param['namafile'] . "'"; //exit('error'.$str);
		try {
			$owlPDO->exec($str);
			$pathx = $path . $namafile;
			unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'excelJurnalmemorial':
		$tab .= "<table cellpading=1 cellspacing=1 border=0 class=sortable>";
		$tab .= "<thead>";
		$tab .= "<tr class=rowheader>";

		$tab .= "<td  align=center>" . $_SESSION['lang']['nojurnal'] . " </td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['tanggal'] . " </td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['noreferensi'] . " </td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['noakun'] . "</td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['jumlah'] . "</td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['keterangan'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['nodok'] . " </td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['kodekegiatan'] . "</td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['kodeasset'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['nik'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['kodecustomer'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['kodesupplier'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['kodevhc'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['alokasi'] . " </td> ";
		$tab .= "</tr>  ";
		$tab .= "</thead>";

		$sisa = $countdt = 0;
		$str = "select * from " . $dbname . "." . $tabledt . "  where nojurnal='" . $param['nojurnal'] . "'";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$countdt++;

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


			$strdt = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $bar['kodecustomer'] . "' ";
			$resdt = fetchdata($strdt);
			@$namacustomer = $resdt[0]['namacustomer'];

			$strdt = "select * from " . $dbname . ".vhc_5master where kodevhc='" . $bar['kodevhc'] . "' ";
			$resdt = fetchdata($strdt);
			@$nopol = $resdt[0]['nopol'];
			@$namavhc = $resdt[0]['detailvhc'];

			@$tjumlah += $bar['jumlah'];

			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=left>" . $bar['nojurnal'] . "</td>";
			$tab .= "<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td align=left>" . $bar['noreferensi'] . "</td>";
			$tab .= "<td align=left>" . $bar['noakun'] . " - " . @$nmakun[$bar['noakun']] . "</td>";
			$tab .= "<td align=right>" . number_format($bar['jumlah'], 2) . "</td>";
			$tab .= "<td align=left>" . $bar['keterangan'] . "</td>";
			$tab .= "<td align=left>" . $bar['nodok'] . "</td>";
			$tab .= "<td align=left>" . $bar['kodekegiatan'] . " - " . @$namakegiatan . "</td>";
			$tab .= "<td align=left>" . $bar['kodeasset'] . " - " . @$namaproject . "</td>";
			$tab .= "<td align=left>" . $nikkaryawan . " - " . @$namakaryawan . "</td>";
			$tab .= "<td align=left>" . $bar['kodecustomer'] . " - " . @$namacustomer . "</td>";
			$tab .= "<td align=left>" . $bar['kodesupplier'] . " - " . @$nmsupplier[$bar['kodesupplier']] . "</td>";
			$tab .= "<td align=left>" . $bar['kodevhc'] . " - " . $nopol . " - " . @$namavhc . "</td>";
			$tab .= "<td align=left>" . $bar['kodeblok'] . "</td>";

			$tab .= "</tr>";
		}
		$tab .= "</table>";

		$nop = "detail_jurnalmemorial.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("detail_jurnalmemorial", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
		break;


		/***********************************************************************************/
		/***********************************************************************************/
	/***********************************************************************************/


	case 'formajukan':

		$tab .= "<fieldset><legend>Detail</legend>";

		$str = "select * from " . $dbname . ".keu_jurnalmemorial where nojurnal='" . $param['nojurnal'] . "'";
		$res = fetchdata($str);
		$posting = $res[0]['posting'];


		$tab .= "<table cellpading=1 cellspacing=1 border=0 class=sortable>";
		$tab .= "<thead>";
		$tab .= "<tr class=rowheader>";

		$tab .= "<td  align=center>" . $_SESSION['lang']['nodok'] . " </td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['noakun'] . "</td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['jumlah'] . "</td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['keterangan'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['kodekegiatan'] . "</td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['kodeasset'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['nik'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['kodecustomer'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['kodesupplier'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['kodevhc'] . " </td> ";
		$tab .= "<td  align=center>" . $_SESSION['lang']['alokasi'] . " </td> ";
		$tab .= "</tr>  ";
		$tab .= "</thead>";



		$sisa = $countdt = 0;
		$str = "select * from " . $dbname . "." . $tabledt . "  where nojurnal='" . $param['nojurnal'] . "'";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {

			$countdt++;

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


			$strdt = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $bar['kodecustomer'] . "' ";
			$resdt = fetchdata($strdt);
			@$namacustomer = $resdt[0]['namacustomer'];

			$strdt = "select * from " . $dbname . ".vhc_5master where kodevhc='" . $bar['kodevhc'] . "' ";
			$resdt = fetchdata($strdt);
			@$nopol = $resdt[0]['nopol'];
			@$namavhc = $resdt[0]['detailvhc'];

			@$tjumlah += $bar['jumlah'];

			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=left>" . $bar['nodok'] . "</td>";
			$tab .= "<td align=left>" . $bar['noakun'] . " - " . @$nmakun[$bar['noakun']] . "</td>";
			$tab .= "<td align=right>" . number_format($bar['jumlah'], 2) . "</td>";
			$tab .= "<td align=left>" . $bar['keterangan'] . "</td>";
			$tab .= "<td align=left>" . $bar['kodekegiatan'] . " - " . @$namakegiatan . "</td>";
			$tab .= "<td align=left>" . $bar['kodeasset'] . " - " . @$namaproject . "</td>";
			$tab .= "<td align=left>" . $nikkaryawan . " - " . @$namakaryawan . "</td>";
			$tab .= "<td align=left>" . $bar['kodecustomer'] . " - " . @$namacustomer . "</td>";
			$tab .= "<td align=left>" . $bar['kodesupplier'] . " - " . @$nmsupplier[$bar['kodesupplier']] . "</td>";
			$tab .= "<td align=left>" . $bar['kodevhc'] . " - " . $nopol . " - " . @$namavhc . "</td>";
			$tab .= "<td align=left>" . $bar['kodeblok'] . "</td>";

			$tab .= "</tr>";
		}
		$tab .= "</table>";
		$tab .= "</fieldset><br>";


		$tab .= "<b>File Upload</b>";
		$tab .= "<table border=0 cellspacing=1 class=sortable cellpadding=5 style=min-width:400px>
			<thead>
			<tr style='font-weight:bold'>
				<td align='center'>" . $_SESSION['lang']['nourut'] . "</td>
				<td align='center'>" . $_SESSION['lang']['tipedokumen'] . "</td>
				<td align='center'>" . $_SESSION['lang']['kriteria'] . "</td>
				<td align='center'>" . $_SESSION['lang']['namafile'] . "</td>
				<td align='center'>" . $_SESSION['lang']['ukurandokumen'] . "</td>
				<td align='center'>" . $_SESSION['lang']['action'] . "</td>";

		$tab .= "</tr>";
		$tab .= "</thead>";


		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['nojurnal'] . "' ";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$no++;

			@$icon = seticonfile($bar['formaticon']);
			$tab .= "<tr class=rowcontent >";
			$tab .= "<td style='text-align:center'>" . $no . "</td>";
			$tab .= "<td align='center'><img src=" . $icon . " class=resicon></a></td>";
			$tab .= "<td>" . $bar['kriteriaefil'] . "</td>";
			$tab .= "<td><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download>" . $bar['namafile'] . "</td>";
			$tab .= "<td align=right>" . ukurandokumen(filesize($path . str_replace('/', '', $bar['namafile']))) . "</td>";
			$tab .= "<td align=center><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a></td>";

			$tab .= "</tr>";
		}
		$tab .= "</table>";

		# Form Ajukan
		$optapprv = $optkaryawanx = $thpapproval = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$arrthpapproval = array("a" => 'Bertingkat Langsung', "b" => "Bertingkat 1 per 1");

		foreach ($arrthpapproval as $key => $val) {
			$thpapproval .= "<option value='" . $key . "'>" . $val . "</option>";
		}

		$sqlJenisApproval = selectQuery($dbname, "setup_jenisapproval", "*", "status='1' AND nama LIKE '%JURNAL MEMORIAL%'");
		$jenisapproval = fetchData($sqlJenisApproval)[0]['jenis'];

		if ($jenisapproval == '') {
			exit("<label hidden>Warning</label> Informasi : Untuk Jenis Approval Jurnal Memorial belum ada, lakukan setup terlebih dahulu dengan nama JURNAL MEMORIAL");
		}

		# Validasi
		$sql = selectQuery($dbname, "setup_approval", "*", "jenispersetujuan='" . $jenisapproval . "' AND kodeunit='" . $param['kodeorg'] . "'");
		$res = fetchData($sql);

		if (count($res) <= 0) {
			exit("<label hidden>Warning</label> Informasi : Untuk Setup Approval Jurnal Memorial dengan jenisapproval " . $jenisapproval . " belum ada, lakukan setup terlebih dahulu di Menu Setup > Persetujuan");
		}

		# Hitung Approval
		$countApp = getCountApproval($jenisapproval, $param['kodeorg']);

		if ($posting == '0' || $posting == 0 || $posting == 2):
			$tab .= "<br>";
			$tab .= "<input id='notransaksi' style='width:150px;' class='myinputtext' type='text' value='" . $param['nojurnal'] . "' hidden />";
			$tab .= "<input id='kodeunit' style='width:150px;' class='myinputtext' type='text' value='" . $param['kodeorg'] . "' hidden />";
			$tab .= "<input id='jenispersetujuan' style='width:150px;' class='myinputtext' type='text' value='" . $jenisapproval . "' hidden />";
			$tab .= "<table border=0 cellspacing=1 class=sortable cellpadding=5 style=min-width:400px>
					<thead>
					<tr style='font-weight:bold'>
						<td align='center' colspan=4>Pengajuan</td>";

			$tab .= "</tr>";
			$tab .= "</thead>";


			$tab .= "<tbody>";
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td>Tanggal</td>";
			$tab .= "<td align=center>:</td>";
			$tab .= "<td><input id='tanggalapprov' name='tanggalapprov" . date('his') . "' style='width:150px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false'; type='text' value='" . date("Y-m-d") . "' disabled/></td>";
			$tab .= "</tr>";

			$tab .= "<tr class=rowcontent>";
			$tab .= "<td>Tahapan Approval</td>";
			$tab .= "<td align=center>:</td>";
			$tab .= "<td><select id='thpapproval' style='width:155px;' onchange=thpapproval('" . $countApp . "')>" . $thpapproval . "</select></td>";
			$tab .= "</tr>";

			for ($i = 1; $i <= $countApp; $i++) {
				$sql = selectQuery($dbname, "setup_approval", "*", "jenispersetujuan='" . $jenisapproval . "' AND kodeunit='" . $param['kodeorg'] . "' AND level='" . $i . "'");
				$data = fetchData($sql)[0]['karyawanid'];
				$optkaryawan = "<option value='" . $data . "'>" . getNamaKaryawan($data) . " - " . getKary($data, "lokasitugas") . "</option>";

				$tab .= "<tr class=rowcontent id='thpa" . $i . "' hidden>";
				$tab .= "<td>Approval - " . $i . "</td>";
				$tab .= "<td align=center>:</td>";
				$tab .= "<td><select id='approval" . $i . "' style='width:155px;'>" . $optkaryawanx . $optkaryawan . "</select></td>";
				$tab .= "</tr>";
			}

			$sql = selectQuery($dbname, "setup_approval", "*", "jenispersetujuan='" . $jenisapproval . "' AND kodeunit='" . $param['kodeorg'] . "' AND level='1'");
			$data = fetchData($sql)[0]['karyawanid'];
			$optkaryawan = "<option value='" . $data . "'>" . getNamaKaryawan($data) . " - " . getKary($data, "lokasitugas") . "</option>";

			$tab .= "<tr class=rowcontent id='thpb' hidden>";
			$tab .= "<td>Approval - 1</td>";
			$tab .= "<td align=center>:</td>";
			$tab .= "<td><select id='approvalb' style='width:155px;'>" . $optkaryawanx . $optkaryawan . "</select></td>";
			$tab .= "</tr>";

			#================ persetujuan
			if (round($tjumlah, 2) == 0 and $countdt > 0) {
				if ($posting == 0 || $posting == 2) {
					// echo "<button class=mybutton onclick=saveposting('".$param['nojurnal']."','".$countApp."','".$param['page']."')>Posting</button>";

					$tab .= "<tr class=rowcontent>";
					$tab .= "<td></td>";
					$tab .= "<td align=center></td>";
					$tab .= "<td><button class=mybutton onclick=saveajukan('" . $countApp . "')>" . $_SESSION['lang']['save'] . "</button></td>";
					$tab .= "</tr>";
				}
			} else {
				echo "Transaksi belum balance atau tidak ada data detail, belum dapat diposting";
			}


			$tab .= "</tbody>";
			$tab .= "</table>";
		endif;

		echo $tab;

		break;

	case 'saveajukan':
		if ($param['tanggal'] == '') {
			exit("<label hidden>Warning</label> Informasi : Tanggal Belum Dipilih, Pilih terlebih dahulu untuk melanjutkan Proses Persetujuan");
		}

		if ($param['thpapproval'] == '') {
			exit("<label hidden>Warning</label> Informasi : Tahapan Approval Belum Dipilih, Pilih terlebih dahulu untuk melanjutkan Proses Persetujuan");
		}

		try {
			$owlPDO->beginTransaction();

			$countApp = getCountApproval($param['jenispersetujuan'], $param['kodeorg']);

			if ($param['thpapproval'] == 'a') {
				if (count($param['karyidapproval']) != $countApp) {
					// echo "<pre>";
					// print_r($param);
					// echo count($param['karyidapproval']);
					// echo $countApp;
					exit("<label hidden>Warning</label> Informasi : Lakukan Reload Frame, karena terdapat perubahan Setup Approval baru-baru ini, (Tingkat Lama : " . count($param['karyidapproval']) . ", Tingkat Baru : " . $countApp . ") ");
				}

				# Insert Ke Approval
				foreach ($param['karyidapproval'] as $level => $karyid) {
					$str = "INSERT INTO $dbname.approval SET notransaksi='" . $param['notransaksi'] . "', jenispersetujuan='" . $param['jenispersetujuan'] . "', level='" . $level . "', karyawanid='" . $karyid . "', status='0'";
					$owlPDO->exec($str);
				}
			} else if ($param['thpapproval'] == 'b') {
				$str = "INSERT INTO $dbname.approval SET notransaksi='" . $param['notransaksi'] . "', jenispersetujuan='" . $param['jenispersetujuan'] . "', level='1', karyawanid='" . $param['karyidapproval'] . "', status='0'";
				$owlPDO->exec($str);
			}


			# Update Approval
			$str = "UPDATE $dbname.keu_jurnalmemorial SET posting='9', postingby='" . $_SESSION['standard']['userid'] . "' WHERE nojurnal='" . $param['notransaksi'] . "'";
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warning: Gagal mengajukan approval \n" . addslashes($e->getMessage());
		}

		break;


	case 'html':
		$tab .= "<fieldset><legend>Detail</legend>";

		$str = "select * from " . $dbname . ".keu_jurnalmemorialdt_vw where nojurnal='" . $param['nojurnal'] . "'";
		$res = fetchdata($str);

		if ($res[0]['noakun'] == '0') {
			$tab .= "<button id=preview class=mybutton  onclick=\"ajukan('" . $param['nojurnal'] . "','" . $param['page'] . "');\">" . $_SESSION['lang']['proses'] . "</button><br>";
		}
		$tab .= "<table cellpading=1 cellspacing=1 border=0 class=sortable>";
		$tab .= "<thead>";
		$tab .= "<tr class=rowheader>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['noakun'] . "</td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['noakun'] . "</td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['jumlah'] . "</td>";
		$tab .= "<td  align=center>" . $_SESSION['lang']['keterangan'] . "</td>";
		$tab .= "</tr>";
		$tab .= "</thead>";

		$str = "select * from " . $dbname . ".keu_jurnalmemorialdt_vw where nojurnal='" . $param['nojurnal'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {

			@$no++;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td  align=left>" . $bar['noakun'] . "</td>";
			$tab .= "<td  align=left>" . $nmakun[$bar['noakun']] . "</td>";
			$tab .= "<td  align=right>" . number_format($bar['jumlah'], 2) . "</td>";
			$tab .= "<td  align=left>" . $bar['keterangan'] . "</td>";
			$tab .= "</tr>";
			@$tjumlah += $bar['jumlah'];
		}

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td  align=center></td>";
		$tab .= "<td  align=center></td>";
		$tab .= "<td  align=right>" . number_format($tjumlah, 2) . "</td>";
		$tab .= "<td  align=left></td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</fieldset><br>";


		if ($tjumlah == 0) {
			$tab .= "<fieldset><legend>Arppoval</legend>";
			$tab .= "<table cellpading=1 cellspacing=1 border=0 class=sortable>";
			$tab .= "<thead>";
			$tab .= "<tr class=rowheader>";
			$tab .= "<td>" . $_SESSION['lang']['level'] . "</td>
					<td>" . $_SESSION['lang']['karyawanid'] . "</td>
					<td>" . $_SESSION['lang']['status'] . "</td>
					<td>" . $_SESSION['lang']['keterangan'] . "</td>
					<td>" . $_SESSION['lang']['tanggal'] . "</td>
				</tr></thead>";

			$optposting = array('' => $_SESSION['lang']['pilihdata'], '0' => 'Belum Diajukan', '1' => 'Disetujui', '2' => 'Ditolak', '3' => 'Dikoreksi', '9' => 'Proses Persetujuan');
			//0; belum proses; 1:disetujui;3:dikoreksi;2:ditolak;9:proses pengajuan
			$str = "select * from " . $dbname . ".approval where notransaksi='" . $param['nojurnal'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td align=center>" . $bar['level'] . "</td>";
				$tab .= "<td>" . $nmkaryawan[$bar['karyawanid']] . "</td>";
				$tab .= "<td>" . $optposting[$bar['status']] . "</td>";
				$tab .= "<td>" . $bar['komentar'] . "</td>";
				$tab .= "<td>" . tanggalnormal(substr($bar['tanggal'], 0, 10)) . " " . substr($bar['tanggal'], 11, 8) . "</td>";
				$tab .= "</tr>";
			}
			$tab .= "</table>";
			$tab .= "</fieldset><br>";
		} else {

			echo "TIDAK BALANCE";
		}




		echo $tab;

		break;




	case 'saveposting':

		try {
			$owlPDO->beginTransaction();
			#= ht
			$str = "select * from " . $dbname . "." . $table . " where nojurnal='" . $param['nojurnal'] . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$strins = "insert into " . $dbname . ".keu_jurnalht
				(nojurnal,kodejurnal,tanggal,tanggalentry,noreferensi,
				 matauang,kurs,autojurnal) 
				values 
				('" . $bar['nojurnal'] . "','M','" . $bar['tanggal'] . "','" . date('Ymd') . "','" . $bar['noreferensi'] . "',
				'" . $bar['matauang'] . "','" . $bar['kurs'] . "','0')";
				$owlPDO->exec($strins);
			}
			#= dt
			$str = "select * from " . $dbname . "." . $tabledt . " where nojurnal='" . $param['nojurnal'] . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$strins = "insert into " . $dbname . ".keu_jurnaldt
				(nojurnal,tanggal,nourut,noakun,keterangan,
				 jumlah,matauang,kurs,kodeorg,kodekegiatan,
				 kodeasset,kodebarang,nik,kodecustomer,kodesupplier,
				 noreferensi,noaruskas,kodevhc,nodok,kodeblok,
				 revisi,kodesegment)  
				values 
				('" . $bar['nojurnal'] . "','" . $bar['tanggal'] . "','" . $bar['nourut'] . "','" . $bar['noakun'] . "','" . $bar['keterangan'] . "',
				'" . $bar['jumlah'] . "','" . $bar['matauang'] . "','" . $bar['kurs'] . "','" . $bar['kodeorg'] . "','" . $bar['kodekegiatan'] . "',
				'" . $bar['kodeasset'] . "','" . $bar['kodebarang'] . "','" . $bar['nik'] . "','" . $bar['kodecustomer'] . "','" . $bar['kodesupplier'] . "',
				'" . $bar['noreferensi'] . "','" . $bar['noaruskas'] . "','" . $bar['kodevhc'] . "','" . $bar['nodok'] . "','" . $bar['kodeblok'] . "',
				'" . $bar['revisi'] . "','" . $bar['kodesegment'] . "')";
				$owlPDO->exec($strins);
			}

			$strins = "update " . $dbname . ".keu_jurnalmemorial set posting='1' where nojurnal = '" . $param['nojurnal'] . "'";
			$owlPDO->exec($strins);


			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warning: Gagal melakukan approval \n" . addslashes($e->getMessage());
		}

		break;



	case 'saveajukan':

		// echo"<pre>";
		// print_r($param);
		// echo"</pre>";exit("Error:A");





		try {
			$owlPDO->beginTransaction();

			for ($i = 1; $i <= count($param['persetujuan']); $i++) {
				if ($param['persetujuan'][$i] == '') {
					exit("Warning: Persetujuan " . $i . " belum dipilih.");
				}
			}
			#= delete 1st untuk aprovalnya
			$str = "delete from " . $dbname . ".approval where notransaksi='" . $param['nojurnal'] . "' and jenispersetujuan='JM'";
			$owlPDO->exec($str);

			$str = "update " . $dbname . ".keu_jurnalmemorial set posting=9 where nojurnal='" . $param['nojurnal'] . "'";
			$owlPDO->exec($str);
			for ($i = 1; $i <= $param['maxaproval']; $i++) {
				#= insert
				// $str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal,requester,tanggalrequest)
				// 	   values('".$param['nojurnal']."','JM','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";	
				$str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan,requester)
					   values('" . $param['nojurnal'] . "','JM','" . $i . "','" . $param['persetujuan'][$i] . "','0','','','" . $_SESSION['standard']['userid'] . "')";
				// exit("Error:".$str);
				$owlPDO->exec($str);
			}

			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warning: Gagal melakukan pengajuan \n" . addslashes($e->getMessage());
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

				$range = range('A', 'T');
				$header = array('nojurnal', 'tanggal', 'nourut', 'noakun', 'keterangan', 'jumlah', 'matauang', 'kurs', 'kodeorg', 'kodekegiatan', 'kodeasset', 'kodebarang', 'nik', 'kodecustomer', 'kodesupplier', 'noreferensi', 'kodevhc', 'kodeblok', 'revisi', 'kodesegment');

				foreach ($header as $head) {
					$cekhead[$head] = $head;
				}
				$arritem = $tanggallist = $divisilist = $kodebloklist = array();
				$validasiht = "";
				$err = "0";
				foreach ($sheets as $noitem => $sheet) {
					if ($noitem > 1) {
						$tanggal = $sheet['B'];
						$tanggallist[$sheet['B']] = $sheet['B'];
						// if($sheet['C']!=''){							
						// 	$divisilist[$sheet['B']] = $sheet['B'];
						// }
						// if($sheet['C']!=''){							
						// 	$ttlist[$sheet['C']] = $sheet['C'];
						// }
					}
				}

				if (count($tanggallist) != 1) {
					$validasiht .= "Tanggal Jurnal tidak boleh lebih dari satu tanggal.<br>";
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

						// $str = "select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$tahun."'";
						// $res = fetchData($str);
						// foreach($res as $bar){
						// 	$hargabarang[$bar['kodebarang']]=$bar['hargasatuan'];
						// }

						// $str = "select * from ".$dbname.".bgt_blok where tahunbudget='".$tahun."' and kodeblok like '".$param['kodeorg']."%' and closed='1'";
						// $res = fetchData($str);
						// foreach($res as $bar){
						// 	$listtt[$bar['thntnm']]=$bar['thntnm'];
						// 	$listdiv[substr($bar['kodeblok'],0,6)]=substr($bar['kodeblok'],0,6);
						// }

						// $str = "select * from ".$dbname.".bgt_kode";
						// $res = fetchData($str);
						// foreach($res as $bar){
						// 	$namakdbgt[$bar['kodebudget']]=$bar['nama'];
						// 	$akunkdbgt[$bar['kodebudget']]=$bar['noakun'];
						// }
						// $namakdbgt['MATERIAL']='MATERIAL';
					} else {

						$expltgl = explode("-", $sheet['B']);
						$tglnotrans = $expltgl[0] . $expltgl[1] . $expltgl[2];
						$periodejurnal = $expltgl[0] . "-" . $expltgl[1];

						$kodejurnal = 'M';
						$query = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $periodejurnal . "'"
						);
						$tmpKonter = fetchData($query);
						$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

						$validasi  				= "";
						// $keterangan			    = getNamaKeg($sheet['D'],'namakegiatan');
						// $matauang				= getNamaAruskas($sheet['F']);
						$nojurnal   			= $tglnotrans . "/" . $sheet['I'] . "/" . $kodejurnal . "/" . $konter;
						$tanggal     			= $sheet['B'];
						$nourut         		= $sheet['C'];
						$noakun    				= $sheet['D'];
						$keterangan			    = $sheet['E'];
						$rupiah    				= $sheet['F'];
						$matauang				= $sheet['G'];
						$kurs 					= $sheet['H'];
						$kodeorg     			= $sheet['I'];
						$kodekegiatan     		= $sheet['J'];
						$kodeasset     			= $sheet['K'];
						$kodebarang 			= $sheet['L'];
						$namabarang 			= getNamaBrg($sheet['L']);
						$nik   				    = $sheet['M'];
						$kodecustomer     		= $sheet['N'];
						$kodesupplier     		= $sheet['O'];
						$noreferensi     		= $sheet['P'];
						$kodevhc     			= $sheet['Q'];
						$kodeblok     			= $sheet['R'];
						$revisi     			= $sheet['S'];
						$kodesegment     		= $sheet['T'];

						if ($tanggal == '') {
							$validasi .= "Tanggal Kosong.<br>";
							$err++;
						}
						if (strlen($tanggal) != 10) {
							$validasi .= "Panjang tahun budget tidak sesuai.<br>";
							$err++;
						}
						if ($kodeorg == '') {
							$validasi .= "Kode Organisasi tidak boleh Kosong.<br>";
							$err++;
						}
						if (strlen($kodeorg) != 4) {
							$validasi .= "Panjang Karakter Kode Organisasi tidak sesuai.<br>";
							$err++;
						}
						// if($kodekeg==''){$validasi.="Kode kegiatan tidak boleh kosong.<br>";$err++;}
						// if(strlen($kodekeg)!=9){$validasi.="Panjang kode kegiatan tidak sesuai.<br>";$err++;}
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

						if ($rupiah > 0) {
							$totaldebet += $rupiah;
						}

						if ($rupiah < 0) {
							$totalkredit += $rupiah;
						}

						$method = "simpanupload";

						$color = "";
						if ($validasiht != '' or $validasi != '') {
							$color = "style=color:red";
						}

						$no++;
						$tab .= "<tr class=rowcontent " . $color . " id=baris_" . $no . ">";
						$tab .= "<td hidden>
									<input id=method_" . $no . " value=" . $method . ">
									<input id=kodeorg_" . $no . " value=" . $kodeorg . ">
									<input id=kodejurnal_" . $no . " value=" . $kodejurnal . ">
									<input id=jenis_" . $no . " value=" . $jenis . ">
								</td>";
						$tab .= "<td " . $color . " align=center>" . $no . "</td>";
						$tab .= "<td " . $color . " align=center id=nojurnal_" . $no . ">" . $nojurnal . "</td>";
						$tab .= "<td " . $color . " align=center id=tanggal_" . $no . ">" . $tanggal . "</td>";
						$tab .= "<td " . $color . " align=center id=nourut_" . $no . ">" . $nourut . "</td>";
						$tab .= "<td " . $color . " align=center id=noakun_" . $no . ">" . $noakun . "</td>";
						$tab .= "<td " . $color . " align=center id=keterangan_" . $no . ">" . $keterangan . "</td>";
						$tab .= "<td " . $color . " align=center id=jumlah_" . $no . ">" . $rupiah . "</td>";
						$tab .= "<td " . $color . " align=center id=matauang_" . $no . ">" . $matauang . "</td>";
						$tab .= "<td " . $color . " align=center id=kurs_" . $no . ">" . $kurs . "</td>";
						$tab .= "<td " . $color . " align=center>" . $kodeorg . "</td>";
						$tab .= "<td " . $color . " align=center id=kodekegiatan_" . $no . ">" . $kodekegiatan . "</td>";
						$tab .= "<td " . $color . " align=center id=kodeasset_" . $no . ">" . $kodeasset . "</td>";
						$tab .= "<td " . $color . " align=center id=kodebarang_" . $no . ">" . $kodebarang . "</td>";
						$tab .= "<td " . $color . " align=center id=nik_" . $no . ">" . $nik . "</td>";
						$tab .= "<td " . $color . " align=center id=kodecustomer_" . $no . ">" . $kodecustomer . "</td>";
						$tab .= "<td " . $color . " align=center id=kodesupplier_" . $no . ">" . $kodesupplier . "</td>";
						$tab .= "<td " . $color . " align=center id=noreferensi_" . $no . ">" . $noreferensi . "</td>";
						$tab .= "<td " . $color . " align=center id=kodevhc_" . $no . ">" . $kodevhc . "</td>";
						$tab .= "<td " . $color . " align=center id=kodeblok_" . $no . ">" . $kodeblok . "</td>";
						$tab .= "<td " . $color . " align=center id=revisi_" . $no . ">" . $revisi . "</td>";
						$tab .= "<td " . $color . " align=center id=kodesegment_" . $no . ">" . $kodesegment . "</td>";
						// $tab.="<td ".$color." align=left>".$namakeg."</td>";
						// $tab.="<td ".$color." align=center id=aruskas_".$no.">".$aruskas."</td>";
						// $tab.="<td ".$color." align=left>".$namaaruskas."</td>";
						// $tab.="<td ".$color." align=left >".$kodebudget."</td>";
						// $tab.="<td ".$color." align=left hidden id=kodebudget_".$no.">".$kdbudget."</td>";
						// $tab.="<td ".$color." align=right id=rotasi_".$no.">".$rotasi."</td>";
						// $tab.="<td ".$color." align=center id=satvol_".$no.">".$satvol."</td>";
						// $tab.="<td ".$color." align=right id=volume_".$no.">".$volume."</td>";
						// $tab.="<td ".$color." align=center id=kodebarang_".$no.">".$kodebarang."</td>";
						// $tab.="<td ".$color." align=left>".$namabarang."</td>";
						// $tab.="<td ".$color." align=left id=kodevhc_".$no.">".$kodevhc."</td>";
						// $tab.="<td ".$color." align=center id=satjlh_".$no.">".$satjlh."</td>";
						// $tab.="<td ".$color." align=right id=jumlah_".$no.">".$jumlah."</td>";
						// $tab.="<td ".$color." align=right id=rupiah_".$no.">".number_format(round($rupiah))."</td>";
						$tab .= "<td " . $color . " align=left id=validasi_" . $no . ">" . trim(nl2br($validasiht)) . trim(nl2br($validasi)) . $selisih . $varvhc . $varupah . "</td>";
						$tab .= "</tr>";

						$ttlrp += round($rupiah);

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
				$tab .= "<td colspan=20 rowspan=3 align=center style=background-color:cyan;color:black;>T O T A L</td>";
				$tab .= "<td style=background-color:cyan;color:black;>(SELISIH)</td>";
				$tab .= "<td align=right style=background-color:cyan;color:black;>" . number_format(round($ttlrp)) . "</td>";
				// $tab.="<td style=background-color:cyan;color:black;>".number_format($ttlselisih)."</td>";
				$tab .= "</tr>";

				$tab .= "<tr class=rowcontent>";
				$tab .= "<td align=center style=background-color:cyan;color:black;>(DEBET)</td>";
				$tab .= "<td align=right style=background-color:cyan;color:black;>" . number_format(round($totaldebet)) . "</td>";
				$tab .= "<td hidden id=totaldebet align=right style=background-color:cyan;color:black;>" . $totaldebet . "</td>";
				// $tab.="<td style=background-color:cyan;color:black;>".number_format($ttlselisih)."</td>";
				$tab .= "</tr>";

				$tab .= "<tr class=rowcontent>";
				$tab .= "<td align=center style=background-color:cyan;color:black;>(KREDIT)</td>";
				$tab .= "<td align=right style=background-color:cyan;color:black;>" . number_format(round($totalkredit)) . "</td>";
				$tab .= "<td hidden id=totalkredit align=right style=background-color:cyan;color:black;>" . $totalkredit . "</td>";
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

	case 'simpanupload':

		try {
			$owlPDO->beginTransaction();

			if ($param['jalanke'] == '1') {
				$data = array(
					'nojurnal' 		=> $param['nojurnal'],
					'kodeorg'    	=> $param['kodeorg'],
					'kodejurnal' 	=> $param['kodejurnal'],
					'tanggal'    	=> $param['tanggalupload'],
					'tanggalentry' 	=> $param['tanggalupload'],
					'posting'	 			=> '0',
					'totaldebet'    => $param['totaldebet'],
					'totalkredit'   => $param['totalkredit'],
					'matauang'    	=> 'IDR',
					'kurs'    			=> '1',
					'createby'   		=> $_SESSION['standard']['userid'],
					'createtime'   	=> date('Y-m-d H:i:s'),
					'updatetime'   	=> date('Y-m-d H:i:s'),
					'updateby'   		=> ''
				);

				$cols = array();
				foreach ($data as $key => $row) {
					$cols[] = $key;
				}

				$query = insertQuery($dbname, 'keu_jurnalmemorial', $data, $cols);
				$owlPDO->exec($query);
			}

			$datadetail = array(
				'nojurnal' => $param['nojurnal'],
				'tanggal'    => $param['tanggalupload'],
				'nourut'    => $param['nourut'],
				'noakun'     => $param['noakun'],
				'keterangan' => $param['keterangan'],
				'jumlah'     => $param['jumlah'],
				'matauang'     => $param['matauang'],
				'kurs'     => $param['kurs'],
				'kodeorg'    => $param['kodeorg'],
				'kodekegiatan'   => $param['kodekegiatan'],
				'kodeasset'   => $param['kodeasset'],
				'kodebarang'   => $param['kodebarang'],
				'nik'   => $param['nik'],
				'kodecustomer'   => $param['kodecustomer'],
				'kodesupplier'   => $param['kodesupplier'],
				'noreferensi'   => $param['noreferensi'],
				'kodevhc'    => $param['kodevhc'],
				'kodeblok'    => $param['kodeblok'],
				'kodesegment'    => $param['kodesegment'],
				'createby'   => $_SESSION['standard']['userid'],
				'createtime'   => date('Y-m-d H:i:s'),
				'updatetime'   => date('Y-m-d H:i:s'),
				// 'updateby'   => $_SESSION['standard']['userid']
				'updateby'   => ''
			);

			$colsdetail = array();
			foreach ($datadetail as $key => $row) {
				$colsdetail[] = $key;
			}

			$query = insertQuery($dbname, 'keu_jurnalmemorialdt', $datadetail, $colsdetail);
			$owlPDO->exec($query);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;

	default:
		break;
}
