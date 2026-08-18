<?php

require_once 'master_validation.php';
require_once 'lib/zLib.php';
require_once 'lib/fpdf.php';
require_once 'lib/nangkoelib.php';
require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

include_once 'lib/HtmlExcel.php';

// ini_set('display_errors', 1);
// error_reporting(1);

$aruskaspjd = '121009';
$aruskasbbm = '118001';
$rekeningbank = checkPostGet('rekeningbank', '');
$tipe = checkPostGet('tipe', '');
$tipex = checkPostGet('tipex', '');
$method = checkPostGet('method', '');
$nopdo = checkPostGet('nopdo', '');
$kepada = checkPostGet('kepada', '');
$notransaksi = checkPostGet('notransaksi', '');
$numrow = checkPostGet('numrow', '');
$nourut = checkPostGet('nourut', '');
$kodejb = checkPostGet('kodejb', '');
$perjb = checkPostGet('perjb', '');
$unit = checkPostGet('unit', '');
$kodeorght = $unit;
$per = checkPostGet('per', '');
$bag = checkPostGet('bag', '');
$mode = checkPostGet('mode', '');
$namafile = checkPostGet('namafile', '');
$explnopdo = explode('/', $nopdo);
//upah
$noupah = checkPostGet('noupah', '');
$shi = checkPostGet('shi', '');
$estimasi = checkPostGet('estimasi', '');
$sdbi = checkPostGet('sdbi', '');
$idpdo = checkPostGet('idpdo', '');
$keterangan = checkPostGet('keterangan', '');
$tkid = checkPostGet('tipekaryawan', '');
$kelkomponengaji = checkPostGet('kelkomponengaji', '');
//tutup upah

//Umar
$sesi = checkPostGet('sesi', '');
$check = checkPostGet('check', '');
$document = checkPostGet('document', '');
$where = '';
$arSesi = array('1' => 'Pertama', '2' => 'Kedua');
$perLalujb = date("Y-m", strtotime("-1 Month", strtotime($perjb)));
$perLalu = date("Y-m", strtotime("-1 Month", strtotime($per)));
$perNow = date("Y-m", strtotime("0 Month", strtotime($per)));
$path = "fileupload/pdo/";
$emodul = "PDO";
$dpp = checkPostGet('dpp', '');
$noinvoice = checkPostGet('noinvoice', '');
$supplier = checkPostGet('supplier', '');
$kegiatan = checkPostGet('kegiatan', '');
//End Umar

//kas
$nopdo = checkPostGet('nopdo', '');
$unit = checkPostGet('unit', '');

$nokas = checkPostGet('nokas', '');
$nohutangkas = checkPostGet('nohutangkas', '');
$nopjd = checkPostGet('nopjd', '');
$noothers = checkPostGet('noothers', '');
$notanaman = checkPostGet('notanaman', '');
$notraksi = checkPostGet('notraksi', '');

$notrankas = checkPostGet('notrankas', '');
$notranhutangkas = checkPostGet('notranhutangkas', '');
$notranpjd = checkPostGet('notranpjd', '');
$notranothers = checkPostGet('notranothers', '');
$notrantanaman = checkPostGet('notrantanaman', '');
$notrantraksi = checkPostGet('notrantraksi', '');
$notrankontraktor = checkPostGet('notrankontraktor', '');
$notransupplier = checkPostGet('notransupplier', '');

$novoucher = checkPostGet('novoucher', '');
$notransaksix = checkPostGet('notransaksix', '');
$noakunkas = checkPostGet('noakunkas', '');
$noakunkasx = checkPostGet('noakunkasx', '');
$noaruskasx = checkPostGet('noaruskasx', '');
$noakunbayarx = checkPostGet('noakunbayarx', '');
$ketkasx = checkPostGet('ketkasx', '');
$jumlahkasx = checkPostGet('jumlahkasx', '');
$checkedx = checkPostGet('checkedx', '');
$nourutkas = checkPostGet('nourutkas', '');
$uraian = checkPostGet('uraian', '');
$kodekeg = checkPostGet('kodekeg', '');

$nokontraktor = checkPostGet('nokontraktor', '');
$nosupplier = checkPostGet('nosupplier', '');

//tutup kas
//hutang
$notranhutang = checkPostGet('notranhutang', '');
$suphutang = checkPostGet('suphutang', '');
$pohutang = checkPostGet('pohutang', '');
$nilpohutang = checkPostGet('nilpohutang', '');
$ppnhutang = checkPostGet('ppnhutang', '');
$pphhutang = checkPostGet('pphhutang', '');
$kashutang = checkPostGet('kashutang', '');
$sisahutang = checkPostGet('sisahutang', '');
$cekhutang = checkPostGet('cekhutang', '');
$nouruthutang = checkPostGet('nouruthutang', '');
$noakunhutang = checkPostGet('noakunhutang', '');
$noakunkashutang = checkPostGet('noakunkashutang', '');
$rekeningbankhutang = checkPostGet('rekeningbankhutang', '');
//
//bapp
$notranbapp = checkPostGet('notranbapp', '');
$divisibapp = checkPostGet('divisibapp', '');
$nobapp = checkPostGet('nobapp', '');
$termin = checkPostGet('termin', '');
$supbapp = checkPostGet('supbapp', '');
$kegbapp = checkPostGet('kegbapp', '');
$tglbapp = tanggalsystemn(checkPostGet('tglbapp', ''));
$satbapp = checkPostGet('satbapp', '');
$fisbapp = checkPostGet('fisbapp', '');
$rpsatbapp = checkPostGet('rpsatbapp', '');
$nilbapp = checkPostGet('nilbapp', '');
$kasbapp = checkPostGet('kasbapp', '');
$sisabapp = checkPostGet('sisabapp', '');
$cekbapp = checkPostGet('cekbapp', '');
$nourutbapp = checkPostGet('nourutbapp', '');
$noakunbapp = checkPostGet('noakunbapp', '');
$noakunkasbapp = checkPostGet('noakunkasbapp', '');
$rekeningbankbapp = checkPostGet('rekeningbankbapp', '');
///
//spk
$divisispk = checkPostGet('divisispk', '');
$notranspk = checkPostGet('notranspk', '');
$nospk = checkPostGet('nospk', '');
$kdsupspk = checkPostGet('kdsupspk', '');
$nmsupspk = checkPostGet('nmsupspk', '');
$kegspk = checkPostGet('kegspk', '');
$tglspk1 = tanggalsystemn(checkPostGet('tglspk1', ''));
$tglspk2 = tanggalsystemn(checkPostGet('tglspk2', ''));
$blokspk = checkPostGet('blokspk', '');
$satspk = checkPostGet('satspk', '');
$fisikspk = checkPostGet('fisikspk', '');
$rptotspk = checkPostGet('rptotspk', '');
$hargaspk = checkPostGet('hargaspk', '');
$nourutspk = checkPostGet('nourutspk', '');
$textcarisupspk = checkPostGet('textcarisupspk', '');

//spk
$checklist = checkPostGet('checklist', '');

//tutup spk
//pad
$nourutpad = checkPostGet('nourutpad', '');
$notranpad = checkPostGet('notranpad', '');
$akunpad = checkPostGet('akunpad', '');
$ketpad = checkPostGet('ketpad', '');
$satpad = checkPostGet('satpad', '');
$fisikpad = checkPostGet('fisikpad', '');
$rupsatpad = checkPostGet('rupsatpad', '');
$totpad = checkPostGet('totpad', '');
$noakunpad = checkPostGet('noakunpad', '');
$rekeningbankpad = checkPostGet('rekeningbankpad', '');
//tutuppad

##BEGIN BBM##
$notranbbm = checkPostGet('notranbbm', '');
$notransaksibbm = checkPostGet('notransaksibbm', '');
$karyawanid = checkPostGet('karyawanid', '');
$jlhbbm = checkPostGet('jlhbbm', '');
$pembayaran = checkPostGet('pembayaran', '');
$cekbbm = checkPostGet('cekbbm', '');
$nourutbbm = checkPostGet('nourutbbm', '');
$currRowbbm = checkPostGet('currRowbbm', '');
$noakunbbm = checkPostGet('noakunbbm', '');
$rekeningbankbbm = checkPostGet('rekeningbankbbm', '');
##END BBM##

##BEGIN IO##
$notranio = checkPostGet('notranio', '');
$notransaksiio = checkPostGet('notransaksiio', '');
$kodevhc = checkPostGet('kodevhc', '');
$jenisbiaya = checkPostGet('jenisbiaya', '');
$biaya = checkPostGet('biaya', '');
$cekio = checkPostGet('cekio', '');
$nourutio = checkPostGet('nourutio', '');
$noakunio = checkPostGet('noakunio', '');
$rekeningbankio = checkPostGet('rekeningbankio', '');
##END IO##

##BEGIN PJD##
$notranpjd = checkPostGet('notranpjd', '');
$unitpjd = checkPostGet('unitpjd', '');
$totalpjd = checkPostGet('totalpjd', '');
$ketpjd = checkPostGet('ketpjd', '');
$noakunpjd = checkPostGet('noakunpjd', '');
$rekeningbankpjd = checkPostGet('rekeningbankpjd', '');
##END PJD##

//lainnya
$nourutlnn = checkPostGet('nourutlnn', '');
$notranlnn = checkPostGet('notranlnn', '');
$akunlnn = checkPostGet('akunlnn', '');
$ketlnn = checkPostGet('ketlnn', '');
$satlnn = checkPostGet('satlnn', '');
$fisiklnn = checkPostGet('fisiklnn', '');
$rupsatlnn = checkPostGet('rupsatlnn', '');
$totlnn = checkPostGet('totlnn', '');
$noakunlnn = checkPostGet('noakunlnn', '');
$rekeningbanklnn = checkPostGet('rekeningbanklnn', '');
//tutuplainnya

//pembayaran supplier
$nobyrsup = checkPostGet('nobyrsup', '');
$akhir = checkPostGet('akhir', '');
$jumlahbyrsupx = checkPostGet('jumlahbyrsupx', '');
$byrlain = checkPostGet('byrlain', '');
$noakunbyrsup = checkPostGet('noakunbyrsup', '');
$byrlain = checkPostGet('byrlain', '');
//end pembayaran supplier

//pembayaran supplier
$nokontraktor = checkPostGet('nokontraktor', '');
$akhir = checkPostGet('akhir', '');
$jumlahkontraktorx = checkPostGet('jumlahkontraktorx', '');
$byrlain = checkPostGet('byrlain', '');
$noakunkontraktor = checkPostGet('noakunkontraktor', '');
$byrlain = checkPostGet('byrlain', '');
//end pembayaran supplier

#= pencarian
$thnsch = checkPostGet('thnsch', '');
$notransaksisch = checkPostGet('notransaksisch', '');
$sesisch = checkPostGet('sesisch', '');
$persch = checkPostGet('persch', '');
$kodeorgsch = checkPostGet('kodeorgsch', '');
$data = checkPostGet('data', '');

#              = income
$nourutincome = checkPostGet('nourutincome', '');
$notranincome = checkPostGet('notranincome', '');
$notranincome2 = checkPostGet('notranincome2', '');
$akunincome = checkPostGet('akunincome', '');
$ketincome = checkPostGet('ketincome', '');
$satincome = checkPostGet('satincome', '');
$fisikincome = checkPostGet('fisikincome', '');
$rupsatincome = checkPostGet('rupsatincome', '');
$totincome = checkPostGet('totincome', '');
$noakunincome = checkPostGet('noakunincome', '');
$rekeningbankincome = checkPostGet('rekeningbankincome', '');

$sesidet = checkPostGet('sesidet', '');
$tiperekap = checkPostGet('tiperekap', '');
$noakunpil = checkPostGet('noakunpil', '');
$arrtipekar = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$arrcompgaji = makeOption($dbname, 'sdm_ho_component', 'id,name');
$arrnmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$kept = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
$arrnmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', 'aktif=1');
$arrnmsupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$arrnmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$arrnmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$arrnmaruskas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas');
$arrnmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optket = makeOption($dbname, 'keu_5keterangan', 'id_ket,keterangan');
$opttipeunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$str = "select tanggalsampai from " . $dbname . ".setup_periodeakuntansi where periode='" . $per . "' limit 1 ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$tglakhirper = $bar['tanggalsampai'];
$tglakhir = explode('-', $tglakhirper);
if ($bag == 'I') {
	$tglawalper = $per . '-01';
} else {
	$tglawalper = $per . '-16';
}
$tglawal = explode('-', $tglawalper);
$tglawalbesok = $tglawal[2] + 1;
$perawal = $tglawal[0] . '-' . $tglawal[1];
$thn = substr($per, 0, 4);
$bln = substr($per, 5, 2);
$perkemarin = periodelalu($per);
$opt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optakun2 = $optakun = $optsat = $optblok = $optaruskas = $optaruskas2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select * from " . $dbname . ".keu_5akun where level=5
	  and (left(noakun,3) in ('621', '631', '126', '128') or left(noakun,1) in ('7', '8')) and aktif=1
	  order by noakun asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optakun .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " " . $bar['namaakun'] . "</option>";
}
$str = "select * from " . $dbname . ".keu_5akun where level=5
	  and left(noakun,1) in ('7', '8') and aktif=1 order by noakun asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optakun2 .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " " . $bar['namaakun'] . "</option>";
}
$str = "select * from " . $dbname . ".keu_5akun where level=5
	  and left(noakun,1) in ('7', '8') and aktif=1 order by noakun asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optakun2 .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " " . $bar['namaakun'] . "</option>";
}
#= arus kas
$where = '';
$optTipe = makeOption($dbname, "organisasi", "kodeorganisasi,tipe", "kodeorganisasi='" . $unit . "'");
if ($optTipe[$unit] == 'HOLDING') {
	$where .= " and pemilik_aruskas in ('GLOBAL','HOLDING') and status='1' and level='3'";
} else if ($optTipe[$unit] == 'KANWIL') {
	$where .= " and pemilik_aruskas in ('GLOBAL','KANWIL') and status='1' and level='3'";
} else {
	$where .= " and pemilik_aruskas in ('GLOBAL','UNIT') and status='1' and level='3' and tipetransaksi='K'";
}
$str = "SELECT * FROM " . $dbname . ".keu_5aruskas where 1=1 " . $where . " order by noaruskas asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optaruskas .= "<option value=" . $bar['noaruskas'] . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
	if ($bar['jenis_pengeluaran'] == 'V') {
		$optaruskas2 .= "<option value=" . $bar['noaruskas'] . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
	}
}
$optaruskas2 = "<option value=''></option>";
$str = "SELECT * FROM " . $dbname . ".keu_5aruskas where 1=1 and status='1' and level='3' and tipetransaksi='M' order by noaruskas asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optaruskas2 .= "<option value=" . $bar['noaruskas'] . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
}
#= satuan
$str = "SELECT * FROM " . $dbname . ".setup_satuan";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optsat .= "<option value=" . $bar['satuan'] . ">" . $bar['satuan'] . "</option>";
}

//Umar
$tporg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$optnamaunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optKegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$optAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', 'aktif=1');

$machine = array();
$query = "SELECT * FROM $dbname.keu_5mesinlaporandt WHERE namalaporan = 'LAPORAN_PDO'";
$result = fetchData($query, 'OBJECT');
foreach ($result as $key => $value) {
	$machine[$value->nourut]['tipe'] = $value->tipe;
	$machine[$value->nourut]['noakundisplay'] = $value->noakundisplay;
	$machine[$value->nourut]['keterangandisplay'] = $value->keterangandisplay;
}

foreach ($machine as $nourut => $arr) {
	$query = "SELECT nourut, noakun FROM $dbname.keu_5mesinlaporandt_akun WHERE namalaporan = 'LAPORAN_PDO' AND nourut = '$nourut'";
	$result = fetchData($query, 'OBJECT');
	foreach ($result as $key => $value) {
		$machine[$value->nourut]['noakun'][] = $value->noakun;
	}
}

//End Umar

# Jalanin aja langsung delete transaksi abnormal, soalnya belum tau pas apa bisa terbentuk


switch ($method) {

	case 'lockTombol':
		$isiDt = explode("/", $nopdo);
		$kodeorght = $isiDt[2];
		#ambil induk organisasi
		$str = "select tipe,induk from " . $dbname . ".organisasi where  kodeorganisasi='" . $kodeorght . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tipe = $bar['tipe'];
		}
		echo $tipe;
		break;

	case 'getdetailjabatan':
		$border = " border=0";
		$header = "class=rowheader";
		$content = "class=rowcontent";
		$sortable = "class=sortable";
		$collapse = '';

		$tab .= " <table style='width:100%;" . $collapse . "' cellpading=1 cellspacing=1 " . $border . " " . $sortable . ">
                <thead>
                    <tr " . $header . ">
                        <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
                        <td align=center>" . $_SESSION['lang']['karyawan'] . "</td>
                        <td align=center >" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['jabatan'] . "</td>
                        <td align=center> " . $_SESSION['lang']['keterangan'] . "</td>
                        <td align=center> " . $_SESSION['lang']['noakun'] . "</td>
                        <td align=center> Komponen</td>
                        <td align=center> " . $_SESSION['lang']['rupiah'] . "</td>
                    </tr>
                </thead>";
		$tab .= "<tbody id=contjabatan>";

		// $str="SELECT a.kode,SUM(c.jumlah) AS rupiahgaji
		//         FROM ".$dbname.".keu_5pdo_dt_akun a
		//         LEFT JOIN ".$dbname.".datakaryawan b ON a.kode=b.kodejabatan
		//         LEFT JOIN ".$dbname.".sdm_gaji c ON b.karyawanid=c.karyawanid
		//         WHERE a.tipe = 'jabatan' and periodegaji between '".$perLalu."' and '".$perNow."'
		//         and nourut ='".$nourut."'
		//         GROUP BY a.kode ";

		$str = "SELECT a.kode,a.nourut,a.akun,c.jumlah,c.karyawanid,c.idkomponen
			FROM " . $dbname . ".keu_5pdo_dt_akun a
			LEFT JOIN " . $dbname . ".datakaryawan b ON a.kode=b.kodejabatan
			LEFT JOIN " . $dbname . ".sdm_gaji c ON b.karyawanid=c.karyawanid
			WHERE a.tipe = 'jabatan'  and a.kode='" . $kodejb . "'
			and periodegaji between '" . $perLalujb . "' and '" . $perjb . "'
		 	 ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no += 1;
			$jabatannye = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $bar['kode'] . "'");
			$ketnye = makeOption($dbname, 'keu_5mesinlaporandt', 'nourut,keterangandisplay', "nourut='" . $bar['nourut'] . "'");
			$akunnye = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $bar['akun'] . "' and aktif=1");
			$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['karyawanid'] . "'");
			$nmkompo = makeOption($dbname, 'sdm_ho_component', 'id,name', "id='" . $bar['idkomponen'] . "'");
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center >" . $no . "</td>";
			$tab .= "<td align=left >" . $nmkar[$bar['karyawanid']] . "</td>";
			$tab .= "<td align=left >" . $jabatannye[$bar['kode']] . "</td>";
			$tab .= "<td align=left >" . $ketnye[$bar['nourut']] . "</td>";
			$tab .= "<td align=left >" . $akunnye[$bar['akun']] . "</td>";
			$tab .= "<td align=left >" . $nmkompo[$bar['idkomponen']] . "</td>";
			$tab .= "<td align=right >" . number_format($bar['jumlah']) . "</td>";
			$tab .= "</tr>";

			$jbttl += $bar['jumlah'];
		}
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=left colspan=6><b>Sub Total</b></td>";
		$tab .= "<td align=right ><b>" . number_format($jbttl, 2) . "</b></td>";
		$tab .= "</tr>";

		$tab .= "</tbody>";
		$tab .= "</table>";
		echo $tab;
		break;

	case 'htmlexcelrekap':
		// Global Variable
		$prevMonth = date('Y-m', strtotime('-1 month', strtotime($per)));
		$sesi = $sesidet;

		$stream = "<div style='width:100%;height:auto;margin-left:auto;margin-right:auto'>";

		if ($tiperekap != 'excel' || $tiperekap != 'pdf') {
			$stream .= "<link rel=stylesheet type=text/css href=style/generic.css>";
			$stream .= "<script language=javascript1.2 src='js/keu_pdo3.js?v=" . time() . "'></script>";
		}

		$labelIntiOrg = $header = "SELURUH UNIT";
		if ($unit != "") {
			$intiOrg = makeOption($dbname, "organisasi", "kodeorganisasi,inti", "kodeorganisasi='{$unit}'")[$unit];
			$labelIntiOrg = $intiOrg == 1 ? "INTI" : "PLASMA KONVERSI DIKELOLA INTI";
			$header = getNamaOrg($unit);
		}

		$stream .= "
        <div style='text-align:center;'>
            <p style='font-size:1rem;font-weight:bold;'>REKAP PERMINTAAN DANA OPERASIONAL " . $header . "</p>
            <p style='font-size:1rem;font-weight:bold;'>PERIODE : {$per}</p>
        </div>";

		if ($tiperekap == 'excel' || $tiperekap == 'pdf') {
			$border = " border=1";
			$header = $content = $sortable = '';
			$collapse = 'border-collapse:collapse';
		} else {
			$border = " border=0";
			$header = "class=rowheader";
			$content = "class=rowcontent";
			$sortable = "class=sortable";
			$collapse = '';
		}

		$arrKomponenPdo = ["UPAH" => "UPAH", "KAS" => "PENGELUARAN TUNAI", "KTRK" => "KONTRAKTOR", "HTGK" => "HUTANGKAS", "PJD" => "PERJALANAN DINAS", "OTH" => "PMK-L"];
		$listKomponenPdo = "";
		foreach ($arrKomponenPdo as $idpdo => $val) {
			$listKomponenPdo .= "<td align='center'>{$val}</td>";
		}

		$stream .= "
                <table style='width:100%;" . $collapse . "' cellpading=1 cellspacing=1 " . $border . " " . $sortable . ">
                <thead>
                    <tr " . $header . ">
                        <td align='center' rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
                        <td align='center' rowspan='2'>Uraian</td>
                        <td align='center' colspan='" . count($arrKomponenPdo) . "'></td>
                        <td align='center' rowspan='3'>TOTAL PMK</td>
                    </tr>
                    <tr " . $header . ">
                        <td align='center' colspan='" . count($arrKomponenPdo) . "'></td>
                    </tr>
					<tr " . $header . ">
						<td align='center'>A.</td>
						<td align='center'></td>
                        {$listKomponenPdo}
					</tr>
                </thead>";

		################################
		## Prepare Data
		################################

		$tipeOrg = makeOption($dbname, "organisasi", "kodeorganisasi,tipe", "kodeorganisasi='{$unit}'")[$unit];
		$nmTNM = "PDO TANAMAN";
		if ($tipeOrg == "PABRIK") {
			$nmTNM = "PDO STATION";
		}
		$arrPdo = [
			"TNM" => $nmTNM,
			"PUL" => "PDO PUL",
			"TEKH" => "PDO TEKHNIK",
		];

		$where = "";
		if ($unit != "") {
			$where .= "AND a.kodeorg='{$unit}'";
		}

		################################
		## Biaya Pembibitan Gak dipake lagi
		################################
		// Get Transaksi Previous Month
		// $qPdoPrevMonthBbt = "SELECT b.rupiahdiajukan, b.rupiahreal, b.potbpjs, b.potalatpnn, b.potpenalty, b.potkontanan, left(b.noakun,3) as noakun, a.periode FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$prevMonth}' {$where} AND a.tipepdo='TNM' AND left(b.noakun, 3)='128' {$where}";
		// $rPdoPrevMonthBbt = fetchData($qPdoPrevMonthBbt);
		// foreach ($rPdoPrevMonthBbt as $bar) {
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiah'] += $bar['rupiahdiajukan'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiahreal'] += $bar['rupiahreal'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potbpjs'] += $bar['potbpjs'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potalatpnn'] += $bar['potalatpnn'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potpenalty'] += $bar['potpenalty'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potkontanan'] += $bar['potkontanan'];
		// }

		// Get Transaksi Current Month
		// $qPdoCurrMonthBbt = "SELECT b.rupiahdiajukan, b.rupiahreal, b.potbpjs, b.potalatpnn, b.potpenalty, b.potkontanan, left(b.noakun,3) as noakun, a.periode FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$per}' AND a.tipepdo='TNM' AND left(b.noakun, 3)='128' {$where}";
		// $rPdoCurrMonthBbt = fetchData($qPdoCurrMonthBbt);
		// foreach ($rPdoCurrMonthBbt as $bar) {
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiah'] += $bar['rupiahdiajukan'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiahreal'] += $bar['rupiahreal'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potbpjs'] += $bar['potbpjs'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potalatpnn'] += $bar['potalatpnn'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potpenalty'] += $bar['potpenalty'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potkontanan'] += $bar['potkontanan'];
		// }

		################################
		## Biaya Pemeliharaan TBM Gak dipake lagi
		################################
		// Get Transaksi Previous Month
		// $qPdoPrevMonthTbm = "SELECT b.rupiahdiajukan, b.rupiahreal, b.potbpjs, b.potalatpnn, b.potpenalty, b.potkontanan, left(b.noakun,3) as noakun, a.periode FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$prevMonth}' {$where} AND a.tipepdo='TNM' AND left(b.noakun, 3)='126' {$where}";
		// $rPdoPrevMonthTbm = fetchData($qPdoPrevMonthTbm);
		// foreach ($rPdoPrevMonthTbm as $bar) {
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiah'] += $bar['rupiahdiajukan'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiahreal'] += $bar['rupiahreal'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potbpjs'] += $bar['potbpjs'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potalatpnn'] += $bar['potalatpnn'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potpenalty'] += $bar['potpenalty'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potkontanan'] += $bar['potkontanan'];
		// }

		// Get Transaksi Current Month
		// $qPdoCurrMonthTbm = "SELECT b.rupiahdiajukan, b.rupiahreal, b.potbpjs, b.potalatpnn, b.potpenalty, b.potkontanan, left(b.noakun,3) as noakun, a.periode FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$per}' AND a.tipepdo='TNM' AND left(b.noakun, 3)='126' {$where}";
		// $rPdoCurrMonthTbm = fetchData($qPdoCurrMonthTbm);
		// foreach ($rPdoCurrMonthTbm as $bar) {
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiah'] += $bar['rupiahdiajukan'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiahreal'] += $bar['rupiahreal'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potbpjs'] += $bar['potbpjs'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potalatpnn'] += $bar['potalatpnn'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potpenalty'] += $bar['potpenalty'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potkontanan'] += $bar['potkontanan'];
		// }

		################################
		## Biaya Pemeliharaan TM Gak dipake lagi
		################################
		// Get Transaksi Previous Month
		// $qPdoPrevMonthTm = "SELECT b.rupiahdiajukan, b.rupiahreal, b.potbpjs, b.potalatpnn, b.potpenalty, b.potkontanan, left(b.noakun,3) as noakun, a.periode FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$prevMonth}' {$where} AND a.tipepdo='TNM' AND left(b.noakun, 3)='621' {$where}";
		// $rPdoPrevMonthTm = fetchData($qPdoPrevMonthTm);
		// foreach ($rPdoPrevMonthTm as $bar) {
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiah'] += $bar['rupiahdiajukan'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiahreal'] += $bar['rupiahreal'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potbpjs'] += $bar['potbpjs'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potalatpnn'] += $bar['potalatpnn'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potpenalty'] += $bar['potpenalty'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potkontanan'] += $bar['potkontanan'];
		// }

		// Get Transaksi Current Month
		// $qPdoCurrMonthTm = "SELECT b.rupiahdiajukan, b.rupiahreal, b.potbpjs, b.potalatpnn, b.potpenalty, b.potkontanan, left(b.noakun,3) as noakun, a.periode FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$per}' AND a.tipepdo='TNM' AND left(b.noakun, 3)='621' {$where}";
		// $rPdoCurrMonthTm = fetchData($qPdoCurrMonthTm);
		// foreach ($rPdoCurrMonthTm as $bar) {
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiah'] += $bar['rupiahdiajukan'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiahreal'] += $bar['rupiahreal'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potbpjs'] += $bar['potbpjs'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potalatpnn'] += $bar['potalatpnn'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potpenalty'] += $bar['potpenalty'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potkontanan'] += $bar['potkontanan'];
		// }

		################################
		## Biaya Panen & Angkut
		################################
		// Get Transaksi Previous Month
		// $qPdoPrevMonthPanen = "SELECT b.rupiahdiajukan, b.rupiahreal, b.potbpjs, b.potalatpnn, b.potpenalty, b.potkontanan, left(b.noakun,3) as noakun, a.periode FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$prevMonth}' {$where} AND a.tipepdo='TNM' AND left(b.noakun, 3)='611' {$where}";
		// $rPdoPrevMonthPanen = fetchData($qPdoPrevMonthPanen);
		// foreach ($rPdoPrevMonthPanen as $bar) {
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiah'] += $bar['rupiahdiajukan'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiahreal'] += $bar['rupiahreal'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potbpjs'] += $bar['potbpjs'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potalatpnn'] += $bar['potalatpnn'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potpenalty'] += $bar['potpenalty'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potkontanan'] += $bar['potkontanan'];
		// }

		// Get Transaksi Current Month
		// $qPdoCurrMonthPanen = "SELECT b.rupiahdiajukan, b.rupiahreal, b.potbpjs, b.potalatpnn, b.potpenalty, b.potkontanan, left(b.noakun,3) as noakun, a.periode FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$per}' AND a.tipepdo='TNM' AND left(b.noakun, 3)='611' {$where}";
		// $rPdoCurrMonthPanen = fetchData($qPdoCurrMonthPanen);
		// foreach ($rPdoCurrMonthPanen as $bar) {
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiah'] += $bar['rupiahdiajukan'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['rupiahreal'] += $bar['rupiahreal'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potbpjs'] += $bar['potbpjs'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potalatpnn'] += $bar['potalatpnn'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potpenalty'] += $bar['potpenalty'];
		//     $dataPdo[$bar['periode']][$bar['noakun']]['potkontanan'] += $bar['potkontanan'];
		// }

		################################
		## PUL (Pengeluaran Tunai, Kontraktor, Hutang Kas, Perjalanan Dinas, PMK Lainnya, Upah dengan idpdo PUL)
		################################
		$arrTipePdoPUL = ["KAS", "OTH", "PJD", "HTGK"];
		$listPUL = join("','", $arrTipePdoPUL);

		// Get Transaksi Current Month
		$qPdoCurrMonthPUL = "SELECT b.rupiahdiajukan, a.periode, b.rupiahreal, a.tipepdo FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$per}' AND (a.tipepdo IN ('{$listPUL}') OR (a.tipepdo='UPAH' AND b.idpdo='PUL')) {$where}";
		$rPdoCurrMonthPUL = fetchData($qPdoCurrMonthPUL);
		foreach ($rPdoCurrMonthPUL as $bar) {
			$dataPdo['PUL'][$bar['tipepdo']]['rupiah'] += $bar['rupiahdiajukan'];
		}

		################################
		## Tanaman (Upah dengan idpdo Tanaman)
		################################
		// Get Transaksi Current Month
		$qPdoCurrMonthTnm = "SELECT b.rupiahdiajukan, a.periode, b.rupiahreal, a.tipepdo FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$per}' {$where} AND ((a.tipepdo='UPAH' AND b.idpdo='TANAMAN') OR a.tipepdo='KTRK')";
		$rPdoCurrMonthTnm = fetchData($qPdoCurrMonthTnm);
		foreach ($rPdoCurrMonthTnm as $bar) {
			$dataPdo['TNM'][$bar['tipepdo']]['rupiah'] += $bar['rupiahdiajukan'];
		}

		################################
		## Teknik (Upah dengan idpdo Teknik)
		################################
		// Get Transaksi Current Month
		$qPdoCurrMonthTeknik = "SELECT b.rupiahdiajukan, a.periode, b.rupiahreal, a.tipepdo FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.periode='{$per}' {$where} AND a.tipepdo = 'UPAH' AND b.idpdo='TEKNIK'";
		$rPdoCurrMonthTeknik = fetchData($qPdoCurrMonthTeknik);
		foreach ($rPdoCurrMonthTeknik as $bar) {
			$dataPdo['TEKH'][$bar['tipepdo']]['rupiah'] += $bar['rupiahdiajukan'];
		}

		// echo "<pre>";
		// print_r($dataPdo);
		// exit("Warning");

		$no = 1;
		foreach ($arrPdo as $idPdo => $keterangan) {
			$stream .= "<tr class='rowcontent'>";
			$stream .= "<td align='center'>" . $no++ . "</td>";
			$stream .= "<td>" . $keterangan . "</td>";
			foreach ($arrKomponenPdo as $komp => $val) {
				$stream .= "
                <td align='right'>" . number_format($dataPdo[$idPdo][$komp]['rupiah'], 2) . "</td>";

				$ttlPmk[$idPdo] += $dataPdo[$idPdo][$komp]['rupiah'];
				$ttlKomponen[$komp] += $dataPdo[$idPdo][$komp]['rupiah'];
			}
			$stream .= "<td align='right'>" . number_format($ttlPmk[$idPdo], 2) . "</td>";
			$stream .= "</tr>";

			$grandTtl += $ttlPmk[$idPdo];
		}

		## GRAND TOTAL

		$stream .= "
			<tr " . $content . ">
                <td></td>
				<td align='center'><b>TOTAL PDO {$labelIntiOrg}</b></td>";
		foreach ($arrKomponenPdo as $komp => $val) {
			$stream .= "<td align='right'><b>" . number_format($ttlKomponen[$komp], 2) . "</b></td>";
		}
		$stream .= "<td align='right'><b>" . number_format($grandTtl, 2) . "</b></td>";
		$stream .= "</tr>";
		$stream .= "</table>";

		$countApprove = getCountApproval('PDO', $unit);
		$str = " select * from " . $dbname . ".keu_pdoht where  nopdo='" . $nopdo . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();

		$stream .= "<br><table border=0 cellspacing=1 " . $sortable . " style='width:100%;" . $collapse . "'>
		<thead>
		<tr style='font-weight:bold'>
			<td style='text-align:center'>" . $_SESSION['lang']['dbuat_oleh'] . "</td>";
		for ($i = 1; $i <= $countApprove; $i++) {
			$stream .= "<td style='text-align:center'>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</td>";
		}
		$stream .= "
		</tr>
		</thead>
		<tbody>";

		$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$arrHsl = array("9" => $_SESSION['lang']['wait_approval'], "1" => $_SESSION['lang']['disetujui'], "2" => $_SESSION['lang']['ditolak']);
		$stream .= "<tr " . $content . ">
				<td valign=top>" . $nmkar[$bar['updateby']] . "<br>
					" . waktunormal($bar['updatetime']) . "</td>";
		for ($i = 1; $i <= $countApprove; $i++) {
			$strx = "select * from " . $dbname . ".setup_approval where jenispersetujuan='PDO' and level='" . $i . "' and kodeunit='" . $bar['unit'] . "'";
			$resx = fetchData($strx);
			$tipeapp = $resx[0]['tipe'];
			$departemenapp = $resx[0]['departemen'];
			$tipekaryawanapp = $resx[0]['tipekaryawan'];
			$jabatanapp = $resx[0]['jabatan'];

			$arrApp = detailApprove($i, $nopdo, 'PDO');

			if ($tipeapp == '1' && $arrApp['status'] != '') {
				if ($arrApp['status'] != '1') {
					if ($departemenapp != '') {
						$opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
						$arrApp['nama'] = $opttipe[$departemenapp];
					}

					if ($tipekaryawanapp != '') {
						$opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
						$arrApp['nama'] = $opttipe[$tipekaryawanapp];
					}

					if ($jabatanapp != '0') {
						$opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
						$arrApp['nama'] = $opttipe[$jabatanapp];
					}
				}
			}

			if ($arrApp['tanggal'] == '' || $arrApp['tanggal'] == '0000-00-00 00:00:00') {
				$tngl = '';
			} else {
				$tngl = tanggalnormal($arrApp['tanggal']);
			}

			if (($arrApp['karyawanid'] != '') && ($arrApp['karyawanid'] != 0)) {
				$stream .= "<td>" . $arrApp['nama'] . "
						<br />" . $arrHsl[$arrApp['status']] . "
						<br />" . $tngl . "
						<br />" . $arrApp['komentar'] . "
					</td>";
			} else {
				$stream .= "<td>&nbsp;</td>";
			}
		}

		$stream .= "</tbody>
		</table><hr>";

		if ($tiperekap == 'html') {
			echo $stream;
		} else if ($tiperekap == 'pdf') {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$dompdf->stream('Laporan PDO', array("Attachment" => 0));
		} else {
			$tglSkrg = date("Ymd");
			$nop_ = "excel_pdo" . $unit;
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
							window.alert('Can't convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		}
		break;

	case 'deletepad':
		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranpad . "' "
			. " and nourut='" . $nourutpad . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'updatepad':
		$str = "update " . $dbname . ".keu_pdodt set noakun='" . $akunpad . "',rincian='" . $ketpad . "',
            tanggal='" . $tglawalper . "',satuan='" . $satpad . "',fisik='" . $fisikpad . "',rupiah='" . $totpad . "',noakunkas='" . $noakunpad . "',rekeningbank='" . $rekeningbankpad . "'
            where nopdo='" . $nopdo . "' and notransaksi='" . $notranpad . "' and nourut='" . $nourutpad . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'datajumlahpad':

		if ($noakunpad == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunpad == '1110101') {
			if ($rekeningbankpad == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		$periodesblm = date("Y-m", strtotime("-2 Month", strtotime($per)));

		if ($akunpad == '11700') {
			$str = "SELECT notransaksi,sum(totalrp) as rupiah,jenis FROM " . $dbname . ".`lgl_pembebasanlahan` where periode <'" . $per . "' and
    			periode>'" . $periodesblm . "' and kodeorg='" . $unit . "' and notransaksi not in (select nodocument FROM " . $dbname . ".`keu_pdodt`)
    			and posting=1 group by jenis";
		} else {
			$str = "select notransaksi,sum(rupiah) as rupiah,concat(notransaksi,' - ',tujuan) as jenis from " . $dbname . ".lgl_bansos  where kodeorg='" . $unit . "' and left(tanggal,7)<='" . $per . "' and posting=1 and statuspersetujuan='1' and notransaksi not in (select nodocument FROM " . $dbname . ".`keu_pdodt`) group by notransaksi";
		}
		//exit('Warning : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			#cek apakah HT sudah di-insert
			$str2 = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='PAD' limit 1";
			$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$cekht = $bar2['jumlah'];
			if ($cekht <= 0) {
				$strht = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('" . $nopdo . "', '" . $notranpad . "', '" . $unit . "', '" . $per . "', 'PAD','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($strht);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			#cek nourut
			$str1 = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranpad . "'"
				. " order by nourut desc limit 1 ";
			$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1 = $res1->fetch();
			$nourutbaru = $bar1['nourut'] + 1;
			$strdt = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
	                `tanggal`, `satuan`,`fisik`, `rupiah`, `nodocument`,`noakunkas`, `rekeningbank`)
	                VALUES ('" . $nopdo . "', '" . $notranpad . "', '" . $nourutbaru . "', '" . $akunpad . "', '" . str_replace('####', ' ', $bar['jenis']) . "',
	                '" . $tglawalper . "', '" . $satpad . "', '" . $fisikpad . "','" . $bar['rupiah'] . "','" . $bar['notransaksi'] . "','" . $noakunpad . "','" . $rekeningbankpad . "')";
			try {
				$owlPDO->exec($strdt);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}

		break;

	case 'savepad':

		if ($noakunpad == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunpad == '1110101') {
			if ($rekeningbankpad == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		#cek apakah HT sudah di-insert
		$str = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='PAD' limit 1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$cekht = $bar['jumlah'];
		if ($cekht <= 0) {
			$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
			VALUES ('" . $nopdo . "', '" . $notranpad . "', '" . $unit . "', '" . $per . "', 'PAD','" . $_SESSION['standard']['userid'] . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		#cek nourut
		$str = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranpad . "'"
			. " order by nourut desc limit 1 ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$nourutbaru = $bar['nourut'] + 1;
		$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                `tanggal`, `satuan`,`fisik`, `rupiah`,`noakunkas`, `rekeningbank`)
                VALUES ('" . $nopdo . "', '" . $notranpad . "', '" . $nourutbaru . "', '" . $akunpad . "', '" . $ketpad . "',
                '" . $tglawalper . "', '" . $satpad . "', '" . $fisikpad . "','" . $totpad . "','" . $noakunpad . "','" . $rekeningbankpad . "')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'listpad':
		$stream .= "<fieldset><legend><b>List PAD</b></legend >
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td align=center width=30px>" . $_SESSION['lang']['nourut'] . "</td>
                        <td align=center >" . $_SESSION['lang']['noakun'] . "</td>
                        <td align=center >" . $_SESSION['lang']['noaruskas'] . "</td>
                        <td align=center >" . $_SESSION['lang']['keterangan'] . "</td>
                        <td align=center width=40px hidden>" . $_SESSION['lang']['satuan'] . "</td>
                        <td align=center width=50px hidden>" . $_SESSION['lang']['kuantitas'] . "</td>
                        <td align=center width=60px hidden>" . $_SESSION['lang']['rupiahsatuan'] . "</td>
                        <td align=center width=90px>" . $_SESSION['lang']['rupiahsatuan'] . "</td>
                        <td align=center width=30px>" . $_SESSION['lang']['action'] . "</td>
                    </tr>
                </thead>";
		//$notrankas=$explnopdo[0].'/'.$explnopdo[2].'/KAS/001';
		$str = "select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%PAD%' ";
		//$str="select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranpad."' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no += 1;
			$stream .= "<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
                        <td align=center>" . @$arrnmakun[$bar['noakunkas']] . "</td>
                        <td align=left>" . $arrnmaruskas[$bar['noakun']] . "</td>
                        <td align=left>" . $bar['rincian'] . "</td>
                        <td align=center hidden>" . $bar['satuan'] . "</td>
                        <td align=right hidden>" . @number_format($bar['fisik']) . "</td>
                        <td align=right hidden>" . @number_format($bar['rupiah'] / $bar['fisik']) . "</td>
                        <td align=right>" . @number_format($bar['rupiah']) . "</td>
                        <td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn title='Edit'
                                onclick=\"editpad('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "',
                                    '" . $bar['noakun'] . "','" . $bar['rincian'] . "','" . $bar['satuan'] . "','" . $bar['fisik'] . "',
                                    '" . $bar['rupiah'] . "','" . $bar['noakunkas'] . "','" . $bar['rekeningbank'] . "');\">
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete'
                                onclick=\"deletepad('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\">
                        </td>
                </tr>";
		}
		echo $stream;
		break;
	case 'detailpad':
		$notranpad = $explnopdo[0] . '/' . $explnopdo[2] . '/PAD' . '/' . $explnopdo[3] . '/001';

		$optaruskaspad = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "SELECT noaruskas,nama_aruskas FROM " . $dbname . ".keu_5aruskas where 1=1 and noaruskas like '1270%' or noaruskas ='129009	' order by noaruskas asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optaruskaspad .= "<option value='" . $bar['noaruskas'] . "'>" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
		}

		$optrek = $optkas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select noakun,namaakun from " . $dbname . ".keu_5akun where noakun in ('1112101','1112102','1110101') and aktif=1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optkas .= "<option value='" . $bar['noakun'] . "'>" . $bar['namaakun'] . "</option>";
		}

		$str = "select * from " . $dbname . ".keu_5akunbank";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$wheredz = " kodebank='" . $bar['namabank'] . "'";
			$optnama = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank', $wheredz);
			$optrek .= "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
		}

		$stream .= "<fieldset><legend><b>Form Input</b></legend >";
		$stream .= "
            " . $_SESSION['lang']['notransaksi'] . " : <input type=text id=notranpad disabled value='" . $notranpad . "' onkeypress=\"return tanpa_kutip(event)\" class=myinputtext>
            " . $_SESSION['lang']['noakun'] . " : <select onchange='getrekeningpad()' id=noakunpad style=\"width:150px;\">" . $optkas . "</select>
            " . $_SESSION['lang']['rekening'] . " : <select id=rekeningbankpad style=\"width:150px;\">" . $optrek . "</select><hr>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td hidden>nourutdb</td>
                        <td align=center>" . $_SESSION['lang']['aruskas'] . "</td>
                        <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
                        <td align=center width=40px hidden>" . $_SESSION['lang']['satuan'] . "</td>
                        <td align=center width=50px hidden>" . $_SESSION['lang']['kuantitas'] . "</td>
                        <td align=center width=60px hidden>" . $_SESSION['lang']['rupiahsatuan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['total'] . "</td>
                        <td align=center width=30px>" . $_SESSION['lang']['action'] . "
                            </td>
                    </tr>
                </thead>";
		$stream .= "
                <tr class=rowcontent>
                    <td align=left hidden><input type=text id=nourutpad onkeypress=\"return tanpa_kutip(event)\" class=myinputtext></td>
                    <td align=left>
						<select id=akunpad  style=width:250px onchange='datajumlahpad()' >'" . $optaruskaspad . "'</select>
						<img onclick=\"z.elSearch('akunpad',event)\" class=resicon src=images/onebit_02.png style=position:relative;top:5px>
					</td>
                    <td align=left><input type=text id=ketpad class=myinputtext ></td>
                    <td align=left hidden><select id=satpad>'" . $optsat . "'</select></td>
                    <td align=right hidden><input type=text id=fisikpad onkeyup=totalpad() onkeypress='return angka_doang(event)' class=myinputtextnumber   style=width:50px ></td>
                    <td align=left hidden><input type=text id=rupsatpad onkeyup=totalpad() onkeypress='return angka_doang(event)' class=myinputtextnumber   style=width:90px ></td>
                    <td align=center ><input type=text id=totpad onkeypress='return angka_doang(event)' class=myinputtextnumber style=width:150px ></td>
                    <td align=center width=30px>
						<img title=" . $_SESSION['lang']['save'] . " class='zImgBtn' onclick='savepad()' src='images/save.png'>
						</td>
               </tr>
               <input type=hidden id=methodpad value='savepad'>";
		$stream .= "</table></fieldset>";
		echo $stream;
		break;
	case 'updatespk':
		//$rptotspk=$fisikspk*$hargaspk;
		$str = "update " . $dbname . ".keu_pdodt set nodocument='" . $nospk . "',kodesupplier='" . $kdsupspk . "',kegiatan='" . $kegspk . "',
				noakun='" . substr($kegspk, 0, 7) . "',rincian='" . $arrnmkeg[$kegspk] . "',tglmulai='" . $tglspk1 . "',tglsampai='" . $tglspk2 . "',
				kodeblok='" . $blokspk . "',satuan='" . $satspk . "',fisik='" . $fisikspk . "',rupiah='" . $rptotspk . "',divisi='" . $divisispk . "'
				where nopdo='" . $nopdo . "' and notransaksi='" . $notranspk . "' and nourut='" . $nourutspk . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'deletespk':
		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranspk . "' "
			. " and nourut='" . $nourutspk . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	case 'listspk':
		$stream .= "<fieldset style='float:left;'><legend><b>List Data SPK</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
                    <thead>
                        <tr>
                            <td align=center width=30px >" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center width=150px >" . $_SESSION['lang']['nospk'] . "</td>
							<td align=center width=280px >" . $_SESSION['lang']['kegiatan'] . "</td>
                            <td align=center width=90px >" . $_SESSION['lang']['blok'] . "</td>
                            <td align=center width=50px >" . $_SESSION['lang']['satuan'] . "</td>
                            <td align=center width=50px >" . $_SESSION['lang']['kuantitas'] . "</td>
                            <td align=center width=80px >" . $_SESSION['lang']['harga'] . "</td>
							<td align=center width=100px >" . $_SESSION['lang']['rupiah'] . "</td>
							<td align=center width=50px >" . $_SESSION['lang']['action'] . "</td>
                        </tr>
                    </thead>";
		$str = " select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%SPK%' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no += 1;
			$harga = $bar['rupiah'] / $bar['fisik'];
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center >" . $no . "</td>";
			$stream .= "<td align=left >" . $bar['nodocument'] . "</td>";
			$stream .= "<td align=left >" . $arrnmkeg[$bar['kegiatan']] . "</td>";
			$stream .= "<td align=center >" . $bar['kodeblok'] . "</td>";
			$stream .= "<td align=center >" . $bar['satuan'] . "</td>";
			$stream .= "<td align=right >" . @number_format($bar['fisik'], 2) . "</td>";
			$stream .= "<td align=right >" . @number_format($harga) . "</td>";
			$stream .= "<td align=right >" . @number_format($bar['rupiah']) . "</td>";
			$stream .= "<td align=center >
						<img src=images/application/application_edit.png class=zImgBtn title='Edit'
                                 onclick=\"editspk('" . $bar['divisi'] . "','" . $bar['notransaksi'] . "','" . $bar['nodocument'] . "',
								 '" . $bar['kodesupplier'] . "','" . $arrnmsupp[$bar['kodesupplier']] . "','" . $bar['kegiatan'] . "',
								 '" . tanggalnormal($bar['tglmulai']) . "','" . tanggalnormal($bar['tglsampai']) . "','" . $bar['kodeblok'] . "','" . $bar['satuan'] . "',
								 '" . $bar['fisik'] . "','" . $harga . "','" . $bar['rupiah'] . "','" . $bar['nourut'] . "');\">
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete'
                                 onclick=\"deletespk('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\">
					</td>";
			$stream .= "</tr>";
		}
		echo $stream;
		break;
	case 'savespk':
		#cek apakah sudah di-input
		#parameter : blok,sup,keg,nopdo,notran,nospk
		$str = "select count(*) as jumlah from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranspk . "'
				and kodeblok='" . $blokspk . "' and kodesupplier='" . $kdsupspk . "' and kegiatan='" . $kegspk . "' and nodocument='" . $nospk . "'
				order by nourut desc limit 1 ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$cekdt = $bar['jumlah'];
		if ($cekdt > 0) {
			exit("Warning : Data sudah pernah di-input");
		}
		#cek apakah HT sudah di-insert
		$str = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "'
					and notransaksi='" . $notranspk . "' and tipepdo='SPK' limit 1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$cekht = $bar['jumlah'];
		if ($cekht <= 0) {
			$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('" . $nopdo . "', '" . $notranspk . "', '" . $unit . "', '" . $per . "', 'SPK','" . $_SESSION['standard']['userid'] . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		$str = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranspk . "'"
			. " order by nourut desc limit 1 ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$nourutbaru = $bar['nourut'] + 1;
		//$rptotspk=$fisikspk*$hargaspk;
		$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`,`kegiatan`, `rincian`,
                    `nodocument`,`tglmulai`,`tglsampai`,`divisi`,`satuan`,`fisik`,
                    `rupiah`,`kodeblok`,`kodesupplier`)
                    VALUES ('" . $nopdo . "', '" . $notranspk . "', '" . $nourutbaru . "','" . substr($kegspk, 0, 7) . "', '" . $kegspk . "', '" . $arrnmkeg[$kegspk] . "',
                    '" . $nospk . "','" . $tglspk1 . "','" . $tglspk2 . "','" . $divisispk . "','" . $satspk . "','" . $fisikspk . "',
                    '" . $rptotspk . "','" . $blokspk . "','" . $kdsupspk . "')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'carisupspklist':
		$stream = "";
		$stream .= "<fieldset style='float:left;'><legend><b>List Data SPK</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
                    <thead>
                        <tr>
                            <td align=center width=30px >" . $_SESSION['lang']['nourut'] . "</td>
                            <td align=center width=100px >" . $_SESSION['lang']['kode'] . "</td>
                            <td align=center width=100px >" . $_SESSION['lang']['namasupplier'] . "</td>
                            <td align=center width=50px >" . $_SESSION['lang']['kota'] . "</td>
                            <td align=center width=100px >" . $_SESSION['lang']['alamat'] . "</td>
                        </tr>
                    </thead>";
		$str = " select * from " . $dbname . ".log_5supplier where left(kodekelompok,1)='K' and status=1 and namasupplier like '%" . $textcarisupspk . "%' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no += 1;
			$stream .= "<tr class=rowcontent style='cursor:pointer;' title='Click It' onclick=\"movesupspk('" . $bar['supplierid'] . "','" . $bar['namasupplier'] . "');\">";
			$stream .= "<td>" . $no . "</td>";
			$stream .= "<td>" . $bar['supplierid'] . "</td>";
			$stream .= "<td>" . $bar['namasupplier'] . "</td>";
			$stream .= "<td>" . $bar['kota'] . "</td>";
			$stream .= "<td>" . $bar['alamat'] . "</td>";
			$stream .= "</tr>";
		}
		echo $stream;
		break;
	case 'getbloknotranspk':
		$thn = substr($per, 0, 4);
		$per = str_replace('-', '', $per);
		if ($notranspk == '') {
			$str = " select notransaksi from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%SPK%'"
				. " and divisi='" . $divisispk . "'  "
				. " order by notransaksi desc limit 1 ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$nolama = $bar['notransaksi'];
			if ($nolama == '') {
				$str = " select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%SPK%' "
					. " order by notransaksi desc limit 1 ";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$notran = $bar['notransaksi'];
				$num = explode('/', $notran);
				$num = @$num[3] + 1;
				if ($num < 10) {
					$num = '00' . $num;
				} else if ($num < 100) {
					$num = '0' . $num;
				} else {
					$num = $num;
				}

				$notranspkbaru = $per . '/' . $unit . '/SPK/' . $num;
			} else {
				$notranspkbaru = $nolama;
			}
		} else {
			$str = " select notransaksi from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%SPK%'"
				. " and divisi='" . $divisispk . "'  "
				. " order by notransaksi desc limit 1 ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$nolama = $bar['notransaksi'];
			if ($nolama == '') {
				$str = " select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%SPK%' "
					. " order by notransaksi desc limit 1 ";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$notran = $bar['notransaksi'];
				$num = explode('/', $notran);
				$num = @$num[3] + 1;
				if ($num < 10) {
					$num = '00' . $num;
				} else if ($num < 100) {
					$num = '0' . $num;
				} else {
					$num = $num;
				}

				$notranspkbaru = $per . '/' . $unit . '/SPK/' . $num;
			} else {
				$notranspkbaru = $nolama;
			}
		}
		##bentuk blok
		$str = "select * from " . $dbname . ".organisasi where induk = '" . $divisispk . "'  order by kodeorganisasi asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($blokspk == $bar['kodeorganisasi']) {
				$select = "selected=selected";
			} else {
				$select = "";
			}
			$optblok .= "<option " . $select . " value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
		}
		echo $notranspkbaru . '####' . $optblok;
		break;
	case 'deletebapp':
		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and nodocument='" . $notranbapp . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'savebapp':

		if ($noakunkasbapp == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunkasbapp == '1110101') {
			if ($rekeningbankbapp == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		if ($notranbapp == '') {
			exit("Warning:Data belum lengkap, silahkan proses ulang");
		}
		if ($cekbapp == 1) {
			#cek apakah HT sudah di-insert
			$str = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "'
					and notransaksi='" . $notranbapp . "' and tipepdo='BAPP' limit 1";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$cekht = $bar['jumlah'];
			if ($cekht <= 0) {
				$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('" . $nopdo . "', '" . $notranbapp . "', '" . $unit . "', '" . $per . "', 'BAPP','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
			##delete 1st
			$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranbapp . "' "
				. " and nodocument='" . $nobapp . "' and divisi='" . $divisibapp . "' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
			$str = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranbapp . "'"
				. " order by nourut desc limit 1 ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$nourutbaru = $bar['nourut'] + 1;

			$sCek = "select left(kodekegiatan,7) as noakun,sum(jumlahrealisasi) as nilai from " . $dbname . ".log_baspk where notransaksi='" . $nobapp . "' group by left(kodekegiatan,7)";
			$rCek = fetchData($sCek);
			if (count($rCek) != 0) {
				$rData = fetchData($sCek);
				foreach ($rData as $key => $val) {
					$optArusKas = makeOption($dbname, "keu_5aruskas_detail", "noakun,noaruskas", "noakun='" . $val['noakun'] . "'");
					$dtArus[$optArusKas[$val['noakun']]] = $val['nilai'];
				}
			} else {
				$sCek = "select noaruskas,sum(nilai) as nilai  from " . $dbname . ".keu_tagihandt where noinvoice='" . $nobapp . "' and left(noakun,3) not in ('117','213') group by noaruskas";
				$rData = fetchData($sCek);

				if (count($rData) != 0) {
					foreach ($rData as $key => $val) {
						//exit('warning'.($val['nilai']+$isiPPn)."-".$pengurangDt);
						$dtArus[$val['noaruskas']] = ($sisabapp / count($rData));
					}
				}
			}
			$nourutbaru = $bar['nourut'] + 1;
			foreach ($dtArus as $key => $val) {
				$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`,
	                    `nodocument`,`tanggal`,`divisi`,`rupiah`,`kodesupplier`,`rincian`,`noakunkas`,`rekeningbank`)
	                    VALUES ('" . $nopdo . "', '" . $notranbapp . "', '" . $nourutbaru . "', '" . $key . "',
	                    '" . $nobapp . "','" . $tglbapp . "','" . $divisibapp . "','" . $val . "','" . $supbapp . "','" . $arrnmsupp[$supbapp] . "','" . $noakunkasbapp . "','" . $rekeningbankbapp . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
				$nourutbaru += 1;
			}
		} else {
			$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranbapp . "' "
				. " and nodocument='" . $nobapp . "' and divisi='" . $divisibapp . "' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'listbapp':

		#ambil data HT
		$strht = "select kodeorg from " . $dbname . ".keu_pdoht where  nopdo='" . $nopdo . "'";
		$resht = $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht = $resht->fetch();
		$kodeorght = $barht['kodeorg'];

		#ambil induk organisasi
		$strht = "select tipe from " . $dbname . ".organisasi where  kodeorganisasi='" . $kodeorght . "'";
		$resht = $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht = $resht->fetch();
		$tipe = $barht['tipe'];

		// if ($barht['tipe']!='HOLDING') {
		//     continue;
		// }

		$stream .= "<fieldset><legend><b>List Data BAPP</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
                    <thead>
                        <tr>
                            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
                            <td align=center>" . $_SESSION['lang']['noakun'] . "</td>
                            <td align=center>" . $_SESSION['lang']['kontraktor'] . "</td>
                            <td align=center>" . $_SESSION['lang']['noinvoice'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . " BAPP</td>
                            <td align=center>" . $_SESSION['lang']['rupiah'] . "</td>";
		//$stream.="<td align=center width=100px >".$_SESSION['lang']['terbayar']."</td><td align=center width=100px >".$_SESSION['lang']['sisa']."</td>";
		$stream .= "<td align=center colspan=2 >" . $_SESSION['lang']['action'] . "</td>
                        </tr>
                    </thead>";
		$str = " select sum(rupiah) as rupiah,nodocument,tanggal,nodocument,kodesupplier,noakunkas,rekeningbank from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%BAPP%' group by nodocument ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nobaspk[$bar['nodocument']] = $bar['nodocument'];
			$tgl[$bar['nodocument']] = $bar['tanggal'];
			$sisa[$bar['nodocument']] = $bar['rupiah'];
			//$nourut[$bar['nodocument']]=$bar['nourut'];
			$notran[$bar['nodocument']] = $bar['notransaksi'];
			$nil[$bar['nodocument']] = $bar['rupiahreal'];
			$divisi[$bar['nodocument']] = $bar['divisi'];
			$sup[$bar['nodocument']] = $bar['kodesupplier'];
			$noakunkas[$bar['nodocument']] = $bar['noakunkas'];
			$rekeningbank[$bar['nodocument']] = $bar['rekeningbank'];
		}
		if (!empty($nobaspk)) {
			foreach ($nobaspk as $noba) {
				$kas[$noba] = abs($sisa[$noba] - $nil[$noba]);
				$no += 1;
				$stream .= "<tr class=rowcontent id=row" . $no . ">";
				$stream .= "<td align=center>" . $no . "</td>";
				$stream .= "<td align=left>" . $arrnmakun[$noakunkas[$noba]] . "</td>";
				$stream .= "<td align=left>" . $arrnmsupp[$sup[$noba]] . "</td>";
				$stream .= "<td align=left>" . $noba . "</td>";
				$stream .= "<td align=center>" . tanggalnormal($tgl[$noba]) . "</td>";
				//$stream.="<td align=right>".@number_format($nil[$noba])."</td>";
				$stream .= "<td align=right>" . @number_format($sisa[$noba]) . "</td>";
				//$stream.="<td align=right>".@number_format($sisa[$noba])."</td>";
				$stream .= "<td align=center>
						<img src=images/application/application_edit.png class=zImgBtn title='Edit'
									 onclick=\"editbapp('" . $notran[$noba] . "','" . $divisi[$noba] . "','" . $noakunkas[$noba] . "','" . $rekeningbank[$noba] . "');\">
								<img src=images/application/application_delete.png class=zImgBtn title='Delete'
									 onclick=\"deletebapp('" . $nopdo . "','" . $noba . "','');\">
						</td>";
				$stream .= "</tr>";
			}
		}

		echo $stream;

		break;
	case 'detailbapp':

		#ambil data HT
		$strht = "select kodeorg from " . $dbname . ".keu_pdoht where  nopdo='" . $nopdo . "'";
		$resht = $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht = $resht->fetch();
		$kodeorght = $barht['kodeorg'];

		#ambil induk organisasi
		$strht = "select tipe,induk from " . $dbname . ".organisasi where  kodeorganisasi='" . $unit . "'";
		$resht = $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht = $resht->fetch();
		$tipe = $barht['tipe'];
		$induk = $barht['induk'];

		// if ($barht['tipe']!='HOLDING') {
		//     continue;
		// }

		$stream = "";
		$stream .= "<fieldset><legend><b>Detail BAPP</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                    <thead>
                        <tr>
                            <td align=center width=30px >" . $_SESSION['lang']['nourut'] . "</td>
                            <td align=center width=170px >" . $_SESSION['lang']['noinvoice'] . "</td>
                            <td align=center width=170px >" . $_SESSION['lang']['kontraktor'] . "</td>
							<td align=center width=80px >" . $_SESSION['lang']['tanggal'] . " BAPP</td>
                            <td align=center width=100px >" . $_SESSION['lang']['rupiah'] . "</td>
							<td align=center width=100px >" . $_SESSION['lang']['terbayar'] . "</td>
							<td align=center width=100px >" . $_SESSION['lang']['sisa'] . "</td>
                            <td align=center width=50px >" . $_SESSION['lang']['action'] . "
							<br><input type=checkbox id=cekallbapp onclick=cekallbapp()>
                            </td>
                        </tr>
                    </thead>
					<tbody id=contentdetailbapp>";
		#berdasarkan log baspk
		$noinvoice2 = array();
		$sBapsk = "select sum(a.jumlahrealisasi) as rphutang,a.notransaksi,b.koderekanan as kodesupplier,a.tanggal  from " . $dbname . ".log_baspk a
		         left join " . $dbname . ".log_spkht b on a.notransaksi=b.notransaksi
		         where left(a.tanggal,7)<='" . $per . "' and left(b.kodeorg,4) in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $induk . "') and a.posting=1
		         group by a.notransaksi";
		//echo $sBapsk;
		$rBaspk = fetchData($sBapsk);
		foreach ($rBaspk as $key => $val) {
			// $sCek="select * from ".$dbname.".keu_tagihanht where nopo='".$val['notransaksi']."'";
			// $rCek=fetchData($sCek);
			$sPdo2 = "select * from " . $dbname . ".keu_pdodt where nodocument='" . $val['notransaksi'] . "' and nopdo='" . $nopdo . "'";
			$rPdo2 = fetchData($sPdo2);
			if (count($rPdo2) == 1) {
				continue;
			}
			// if(count($rCek)==1){
			//     continue;
			// }
			$optAkun = makeOption($dbname, "log_5supkelompok", "supplierid,noakun", "supplierid='" . $val['kodesupplier'] . "' and tipe='KONTRAKTOR'");
			$noinvoice2[$val['notransaksi']] = $val['notransaksi'];
			$sup2[$val['notransaksi']] = $val['kodesupplier'];
			$tgl2[$val['notransaksi']] = $val['tanggal'];
			$nil2[$val['notransaksi']] = $val['rphutang'];
			$noakun2[$val['noinvoice']] = $$optAkun[$val['kodesupplier']];
			$kas2[$val['noinvoice']] = 0;
		}
		// echo"<pre>";
		// print_r($noinvoice2);
		// echo"</pre>";

		#berdasarkan tagihan dan kas bank yang belum terbayar
		$str = "select b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,(sum(c.jumlah)-b.nilaiinvoice) as selisih, b.kodesupplier,b.noakun,b.tanggal from " . $dbname . ".keu_tagihanht b "
			. " left join " . $dbname . ".keu_kasbankdtht_vw c on b.noinvoice=c.keterangan1"
			. " where b.kodeorg='" . $induk . "' and b.tipeinvoice in ('k','trs','tck') and b.nilaiinvoice>0 and c.jumlah>0 and left(b.tanggal,7)<='" . $per . "' and c.posting<>1
                 group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
		//exit('warning'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {

			#= cek apakah
			$noinvoice[$bar['noinvoice']] = $bar['noinvoice'];
			$sup[$bar['noinvoice']] = $bar['kodesupplier'];
			$tgl[$bar['noinvoice']] = $bar['tanggal'];
			$nil[$bar['noinvoice']] = $bar['nilaiinvoice'];
			$noakun[$bar['noinvoice']] = $bar['noakun'];
			$kas[$bar['noinvoice']] = $bar['jumlah'];
		}

		// $str=" select * from ".$dbname.".keu_tagihanht where kodeorg='".$induk."' and tipeinvoice='k'  and left(tanggal,7)<='".$per."' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		//     $noinvoice[$bar['noinvoice']]=$bar['noinvoice'];
		//     $sup[$bar['noinvoice']]=$bar['kodesupplier'];
		//     $tgl[$bar['noinvoice']]=$bar['tanggal'];
		//     $nil[$bar['noinvoice']]=$bar['nilaiinvoice'];
		//     $noakun[$bar['noinvoice']]=$bar['noakun'];
		// }
		// if(isset($noinvoice)){
		//     $str=" select * from ".$dbname.".keu_kasbankdtht_vw where keterangan1 in ('".implode("','",$noinvoice)."')   and left(tanggal,7)<='".$per."'";
		//     $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		//     $res->setFetchMode(PDO::FETCH_ASSOC);
		//     while($bar=$res->fetch()){
		//         $kas[$bar['keterangan1']]=$bar['jumlah'];
		//     }
		// }
		$str = " select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranbapp . "'  ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nobaspksave[$bar['nodocument']] = 1;
		}
		// $stream.="<tr class=rowcontent id=rowbapp".$no.">";
		// $stream.="<td align=center>".$no."</td>";
		// $stream.="<td align=left id=nobapp".$no." >".$noba."</td>";
		// $stream.="<tr  class=rowcontent>";
		// $stream.="<td colspan=12 align=right><button class=mybutton onclick=saveallbapp(".$no.");>".$_SESSION['lang']['proses']."</button></td>";
		// $stream.="</tr>";
		$no = 0;
		if (!empty($noinvoice2)) {
			foreach ($noinvoice2 as $noinv) {
				@$sisa[$noinv] = abs($kas2[$noinv] - $nil2[$noinv]);

				if ($sisa[$noinv] != 0) {
					if (@$nobaspksave[$noinv] == 1) {
						$cek = "checked=true";
					} else {
						$cek = "";
					}
					$no += 1;
					$stream .= "<tr class=rowcontent id=rowbapp" . $no . ">";
					$stream .= "<td align=center>" . $no . "</td>";
					$stream .= "<td align=left id=nobapp" . $no . " >" . $noinv . "</td>";
					$stream .= "<td align=left id=supbapp" . $no . " hidden>" . $sup2[$noinv] . "</td>";
					$stream .= "<td align=left>" . $arrnmsupp[$sup2[$noinv]] . "</td>";
					$stream .= "<td align=center id=tglbapp" . $no . " >" . tanggalnormal($tgl2[$noinv]) . "</td>";
					$stream .= "<td align=right id=nilbapp" . $no . " >" . @number_format($nil2[$noinv]) . "</td>";
					$stream .= "<td align=right id=kasbapp" . $no . " >" . @number_format($kas2[$noinv]) . "</td>";
					$stream .= "<td align=right id=sisabapp" . $no . " >" . @number_format($sisa[$noinv]) . "</td>";
					$stream .= "<td align=center><input type=checkbox id=cekbapp" . $no . " " . $cek . "></td>";
					$stream .= "<input type=hidden id=noakunbapp" . $no . " value='" . $noakun2[$noinv] . "'></td>";
					$stream .= "</tr>";
				}
			}
		}
		if (!empty($noinvoice)) {
			foreach ($noinvoice as $noinv) {
				@$sisa[$noinv] = abs($kas[$noinv] - $nil[$noinv]);

				if ($sisa[$noinv] != 0) {
					if (@$nobaspksave[$noinv] == 1) {
						$cek = "checked=true";
					} else {
						$cek = "";
					}
					$no += 1;
					$stream .= "<tr class=rowcontent id=rowbapp" . $no . ">";
					$stream .= "<td align=center>" . $no . "</td>";
					$stream .= "<td align=left id=nobapp" . $no . " >" . $noinv . "</td>";
					$stream .= "<td align=left id=supbapp" . $no . " hidden>" . $sup[$noinv] . "</td>";
					$stream .= "<td align=left>" . $arrnmsupp[$sup[$noinv]] . "</td>";
					$stream .= "<td align=center id=tglbapp" . $no . " >" . tanggalnormal($tgl[$noinv]) . "</td>";
					$stream .= "<td align=right id=nilbapp" . $no . " >" . @number_format($nil[$noinv]) . "</td>";
					$stream .= "<td align=right id=kasbapp" . $no . " >" . @number_format($kas[$noinv]) . "</td>";
					$stream .= "<td align=right id=sisabapp" . $no . " >" . @number_format($sisa[$noinv]) . "</td>";
					$stream .= "<td align=center><input type=checkbox id=cekbapp" . $no . " " . $cek . "></td>";
					$stream .= "<input type=hidden id=noakunbapp" . $no . " value='" . $noakun[$noinv] . "'></td>";
					$stream .= "</tr>";
				}
			}
			$stream .= "<tr  class=rowcontent>";
			$stream .= "<td colspan=8 align=right><button class=mybutton onclick=saveallbapp(" . $no . ");>" . $_SESSION['lang']['proses'] . "</button></td>";
			$stream .= "</tr>";
		}
		/*
        if(!empty($nobaspk))
        {
        foreach($nobaspk as $noba)
        {
        @$sisa[$noba]=abs($kas[$noba]-$nil[$noba]);
        if($sisa[$noba]>0)
        {
        if(@$nobaspksave[$noba]==1)
        {
        $cek="checked=true";
        }
        else
        {
        $cek="";
        }
        @$rpsat[$noba]=$nil[$noba]/$fis[$noba];
        @$no+=1;
        $stream.="<tr class=rowcontent id=rowbapp".$no.">";
        $stream.="<td align=center>".$no."</td>";
        $stream.="<td align=left id=nobapp".$no." >".$noba."</td>";
        $stream.="<td align=left id=supbapp".$no." hidden>".$sup[$noba]."</td>";
        $stream.="<td align=left>".$arrnmsupp[$sup[$noba]]."</td>";
        $stream.="<td align=center id=tglbapp".$no." >".tanggalnormal($tgl[$noba])."</td>";
        $stream.="<td align=right id=nilbapp".$no." >".@number_format($nil[$noba])."</td>";
        $stream.="<td align=right id=kasbapp".$no." >".@number_format($kas[$noba])."</td>";
        $stream.="<td align=right id=sisabapp".$no." >".@number_format($sisa[$noba])."</td>";
        $stream.="<td align=center><input type=checkbox id=cekbapp".$no." ".$cek."></td>";
        $stream.="</tr>";
        }
        }
        }
        $stream.="<tr  class=rowcontent>";
        $stream.="<td colspan=12 align=right><button class=mybutton onclick=saveallbapp(".$no.");>".$_SESSION['lang']['proses']."</button></td>";
        $stream.="</tr>";
         */
		echo $stream;
		break;
	case 'nobapp':
		$thn = substr($per, 0, 4);
		$per = str_replace('-', '', $per);
		if ($notranbapp == '') {
			##cek apakah sudah pernah ada data diinput
			##param : nopdo - periode - divisi - tipekaryawan
			$str = " select notransaksi from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%BAPP%'"
				. " and divisi='" . $divisibapp . "'  "
				. " order by notransaksi desc limit 1 ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$nolama = $bar['notransaksi'];
			if ($nolama == '') {
				$str = " select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%BAPP%' "
					. " order by notransaksi desc limit 1 ";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$notran = $bar['notransaksi'];
				$num = explode('/', $notran);
				@$num = $num[3] + 1;
				if ($num < 10) {
					$num = '00' . $num;
				} else if ($num < 100) {
					$num = '0' . $num;
				} else {
					@$num = $num;
				}

				$noupahbaru = $per . '/' . $unit . '/BAPP/' . $num;
			} else {
				$noupahbaru = $nolama;
			}
		} else {
			$str = " select notransaksi from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%BAPP%'"
				. " and divisi='" . $divisibapp . "'  "
				. " order by notransaksi desc limit 1 ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$nolama = $bar['notransaksi'];
			if ($nolama == '') {
				$str = " select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%BAPP%' "
					. " order by notransaksi desc limit 1 ";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$notran = $bar['notransaksi'];
				$num = explode('/', $notran);
				@$num = $num[3] + 1;
				if ($num < 10) {
					$num = '00' . $num;
				} else if ($num < 100) {
					$num = '0' . $num;
				} else {
					$num = $num;
				}

				$noupahbaru = $per . '/' . $unit . '/BAPP/' . $num;
			} else {
				$noupahbaru = $nolama;
			}
			// $noupahbaru=$notranbapp;
		}
		echo $noupahbaru;
		break;
	#######################################################################################
	#######################################################################################
	case 'deletehutang':
		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and nodocument='" . $notranhutang . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'savehutang':

		if ($noakunkashutang == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunkashutang == '1110101') {
			if ($rekeningbankhutang == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		if ($cekhutang == 1) {
			#cek apakah HT sudah di-insert
			$str = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='HUTANG' limit 1";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$cekht = $bar['jumlah'];
			if ($cekht <= 0) {
				$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('" . $nopdo . "', '" . $notranhutang . "', '" . $unit . "', '" . $per . "', 'HUTANG','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			##delete 1st
			$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranhutang . "' "
				. " and nodocument='" . $pohutang . "' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
			$str = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranhutang . "'"
				. " order by nourut desc limit 1 ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$sCek = "select left(kodebarang,3) as klmpk,noakun,sum(hargasatuan*jumlah) as nilai  from " . $dbname . ".log_transaksi_vw a left join " . $dbname . ".log_5klbarang b on left(a.kodebarang,3)=b.kode
			       where notransaksi='" . $pohutang . "' group by b.noakun";
			$rCek = fetchData($sCek);
			if (count($rCek) != 0) {
				$rData = fetchData($sCek);
				foreach ($rData as $key => $val) {
					$optArusKas = makeOption($dbname, "keu_5aruskas_detail", "noakun,noaruskas", "noakun='" . $val['noakun'] . "'");
					$dtArus[$optArusKas[$val['noakun']]] = $val['nilai'];
				}
			} else {
				$sCek = "select noaruskas,sum(nilai) as nilai  from " . $dbname . ".keu_tagihandt where noinvoice='" . $pohutang . "' and left(noakun,3) not in ('117','213') group by noaruskas";
				$rData = fetchData($sCek);

				if (count($rData) != 0) {
					foreach ($rData as $key => $val) {
						//exit('warning'.($val['nilai']+$isiPPn)."-".$pengurangDt);
						$dtArus[$val['noaruskas']] = ($kashutang / count($rData));
					}
				}
			}
			$nourutbaru = $bar['nourut'] + 1;
			foreach ($dtArus as $key => $val) {
				$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                    `nodocument`,`tanggal`,`rupiah`,`kodesupplier`,`noakunkas`,`rekeningbank`)
                    VALUES ('" . $nopdo . "', '" . $notranhutang . "', '" . $nourutbaru . "', '" . $key . "', '" . $arrnmsupp[$suphutang] . "',
                    '" . $pohutang . "','" . $tglawalper . "','" . $val . "','" . $suphutang . "','" . $noakunkashutang . "','" . $rekeningbankhutang . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
				$nourutbaru += 1;
			}
		} else {
			$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranhutang . "' "
				. " and nodocument='" . $pohutang . "' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'listhutang':

		#ambil data HT
		$strht = "select kodeorg from " . $dbname . ".keu_pdoht where  nopdo='" . $nopdo . "'";
		$resht = $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht = $resht->fetch();
		$kodeorght = $barht['kodeorg'];

		#ambil induk organisasi
		$strht = "select tipe,induk from " . $dbname . ".organisasi where  kodeorganisasi='" . $kodeorght . "'";
		$resht = $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht = $resht->fetch();
		$tipe = $barht['tipe'];
		$induk = $barht['induk'];

		// if ($barht['tipe']!='HOLDING') {
		//     continue;
		// }

		// $stream.="<fieldset><legend><b>List ".$_SESSION['lang']['hutang']."</b></legend>
		//           <table cellpading=1 cellspacing=1 border=0 class=sortable>
		//               <thead>
		//                   <tr>
		//                       <td align=center rowspan=2 width=30px >".$_SESSION['lang']['nourut']."</td>
		//                       <td align=center rowspan=2 width=180px >".$_SESSION['lang']['namasupplier']."</td>
		//                       <td align=center rowspan=2 width=180px >".$_SESSION['lang']['nopo']."</td>
		//                       <td align=center colspan=4 width=50px >".$_SESSION['lang']['hutang']."</td>
		//                       <td align=center colspan=2 width=50px >".$_SESSION['lang']['pembayaran']."</td>
		//                 <td align=center rowspan=2 width=30px >".$_SESSION['lang']['action']."</td>
		//                   </tr>
		//                   <tr>
		//                       <td align=center width=80px >".$_SESSION['lang']['rupiah']."</td>
		//                       <td align=center width=80px >".$_SESSION['lang']['ppn']."</td>
		//                       <td align=center width=80px >PPh</td>
		//                       <td align=center width=80px >".$_SESSION['lang']['total']."</td>
		//                       <td align=center width=80px >".$_SESSION['lang']['terbayar']."</td>
		//                       <td align=center width=80px >".$_SESSION['lang']['sisa']."</td>
		//                   </tr>
		//               </thead>";
		$stream .= "<fieldset><legend><b>List " . $_SESSION['lang']['hutang'] . "</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr>
                        <td align=center width=30px >" . $_SESSION['lang']['nourut'] . "</td>
                        <td align=center width=30px >" . $_SESSION['lang']['noakun'] . "</td>
                        <td align=center width=180px >" . $_SESSION['lang']['namasupplier'] . "</td>
                        <td align=center width=180px >" . $_SESSION['lang']['nopo'] . "</td>
                        <td align=center width=50px >" . $_SESSION['lang']['hutang'] . "</td>
						<td align=center colspan=2>" . $_SESSION['lang']['action'] . "</td>
                    </tr>
                </thead>";

		$sData = "select sum(rupiah) as rup,nodocument,rincian,noakunkas,rekeningbank from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranhutang . "' group by nodocument order by nodocument";
		$rData = fetchData($sData);

		foreach ($rData as $key => $val) {
			$no += 1;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center>" . $no . "</td>";
			$stream .= "<td>" . $arrnmakun[$val['noakunkas']] . "</td>";
			$stream .= "<td>" . $val['rincian'] . "</td>";
			$stream .= "<td>" . $val['nodocument'] . "</td>";
			$stream .= "<td align=right>" . number_format($val['rup']) . "</td>";
			$stream .= "<td align=center><img src=images/application/application_edit.png class=zImgBtn title='Edit'
								 onclick=\"edithutang('" . $nopdo . "','" . $notranhutang . "','" . $val['noakunkas'] . "','" . $val['rekeningbank'] . "');\"></td>
							<td align=center><img src=images/application/application_delete.png class=zImgBtn title='Delete'
								 onclick=\"deletehutang('" . $nopdo . "','" . $val['nodocument'] . "','');\">
									</td>
									";
			$stream .= "</tr>";
			$totHutang += $val['rup'];
		}
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td colspan=4>" . $_SESSION['lang']['total'] . "</td>";
		$stream .= "<td align=right>" . number_format($totHutang) . "</td>";
		$stream .= "<td colspan=2>&nbsp;</td>";
		$stream .= "</tr>";
		#data nopo
		//       $str=" select * from ".$dbname.".log_transaksi_vw where kodept='".$induk."' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		//           $nilaipo[$bar['nopo']]=$bar['subtotal'];
		//           $totalpo[$bar['nopo']]=$bar['nilaipo'];
		//           $ppn[$bar['nopo']]=$bar['ppn'];
		//           $pph[$bar['nopo']]=$bar['pph'];
		//           $sup[$bar['nopo']]=$bar['kodesupplier'];
		//       }
		//       $str=" select a.nopo,b.noinvoice,b.nilaiinvoice,c.jumlah,(c.jumlah-b.nilaiinvoice) as selisih from ".$dbname.".log_po_terima_vw2 a "
		//               . " left join ".$dbname.".keu_tagihanht b on a.nopo=b.nopo "
		//               . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
		//               . " where a.kodeorg='".$induk."' and  ((c.jumlah-b.nilaiinvoice < 0) or (jumlah is NULL)) ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		//           $noinv[$bar['nopo']]=$bar['noinvoice'];
		//           $nilaikas[$bar['nopo']]=$bar['jumlah'];
		//       }
		//       //$str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi='".$notranhutang."' ";
		// $str=" select * from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi like '%HUTANG%' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		//           $nomorpo[$bar['nodocument']]=$bar['nodocument'];
		//           $nourut[$bar['nodocument']]=$bar['nourut'];
		//       }

		//       #ambil statjurnal
		//       $kodeall=array();
		//       $kodejurnal=array();
		//       $kodetdkjurnal=array();
		//       $strsup="select kode,jurnal from ".$dbname.".keu_5jenistagihan where kode not in ('p','pj','poa','um','k','p21','p22','p23','p25','ps4') ";
		// $ressup=$owlPDO->query($strsup) or die(print " Gagal: ".PDOException::getMessage());
		// $ressup->setFetchMode(PDO::FETCH_ASSOC);
		// while ($barsup=$ressup->fetch()) {
		//     if ($barsup['jurnal']==1) {
		//         $kodejurnal[$barsup['kode']]=$barsup['kode'];
		//     }
		//     if ($barsup['jurnal']==0) {
		//         $kodetdkjurnal[$barsup['kode']]=$barsup['kode'];
		//     }
		// }

		// $str=" select b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,(sum(c.jumlah)-b.nilaiinvoice) as selisih, b.kodesupplier from ".$dbname.".keu_tagihanht b "
		//               . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
		//               . " where b.kodeorg='".$induk."' and b.tipeinvoice in ('".implode("','",$kodejurnal)."') and b.nilaiinvoice>0 and c.jumlah>0 group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		//           $noinvjurnal[$bar['noinvoice']]=$bar['noinvoice'];
		//           $nilaiht[$bar['noinvoice']]=$bar['nilaiinvoice'];
		//           $nilaikas[$bar['noinvoice']]=$bar['jumlah'];
		//           $noakunhutang[$bar['noinvoice']]=$bar['noakun'];
		//           $sup[$bar['noinvoice']]=$bar['kodesupplier'];
		//       }

		//       $str=" select b.noinvoice,b.noakun,b.nilai as pph from ".$dbname.".keu_tagihandt b where b.noinvoice in ('".implode("','",$noinvjurnal)."') and left(noakun,3)='213'";
		//       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		//           $pph[$bar['noinvoice']]=$bar['pph']*-1;
		//       }

		//       $str=" select b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,(sum(c.jumlah)-b.nilaiinvoice) as selisih, b.kodesupplier from ".$dbname.".keu_tagihanht b "
		//               . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
		//               . " where b.kodeorg='".$induk."' and b.tipeinvoice in ('".implode("','",$kodetdkjurnal)."') and b.nilaiinvoice>0 and c.jumlah>0 group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		//           $noinvtdkjurnal[$bar['noinvoice']]=$bar['noinvoice'];
		//           $nilaiht[$bar['noinvoice']]=$bar['nilaiinvoice'];
		//           $nilaikas[$bar['noinvoice']]=$bar['jumlah'];
		//           $noakunhutang[$bar['noinvoice']]=$bar['noakun'];
		//           $sup[$bar['noinvoice']]=$bar['kodesupplier'];
		//       }

		//       $str=" select b.noinvoice,b.noakun,b.nilai from ".$dbname.".keu_tagihandt b where b.noinvoice in ('".implode("','",$noinvtdkjurnal)."') and (left(noakun,3)='213' or left(noakun,3)='117')";
		//       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		//     if (substr($bar['noakun'],0,3)=='213') {
		//         $pph[$bar['noinvoice']]=$bar['nilai']*-1;
		//     }else{
		//         $ppn[$bar['noinvoice']]=$bar['nilai'];
		//     }
		//       }

		//       if(count(@$nomorpo)>0){
		//     foreach($nomorpo as $nopo)
		//     {

		//         if ($nilaipo[$nopo]==0) {
		//             $nilaipo[$nopo]=$nilaiht[$nopo]-$ppn[$nopo];
		//             $totalpo[$nopo]=$nilaiht[$nopo]+$ppn[$nopo]-$pph[$nopo];
		//         }

		//         $no+=1;
		//         $sisa[$nopo]=abs($nilaikas[$nopo]-$totalpo[$nopo]);
		//         $stream.="<tr class=rowcontent>";
		//         $stream.="<td align=center>".$no."</td>";
		//         $stream.="<td align=left>".$arrnmsupp[$sup[$nopo]]."</td>";
		//         $stream.="<td align=center>".$nopo."</td>";
		//         $stream.="<td align=right>".@number_format($nilaipo[$nopo])."</td>";
		//         $stream.="<td align=right>".@number_format($ppn[$nopo])."</td>";
		//         $stream.="<td align=right>".@number_format($pph[$nopo])."</td>";
		//         $stream.="<td align=right>".@number_format($totalpo[$nopo])."</td>";
		//         $stream.="<td align=right>".@number_format($nilaikas[$nopo])."</td>";
		//         $stream.="<td align=right>".@number_format($sisa[$nopo])."</td>";
		//         $stream.="
		//                         <td align=center>
		//                             <img src=images/application/application_edit.png class=zImgBtn title='Edit'
		//                                  onclick=\"edithutang('".$nopdo."','".$notranhutang."');\">
		//                             <img src=images/application/application_delete.png class=zImgBtn title='Delete'
		//                                  onclick=\"deletehutang('".$nopdo."','".$notranhutang."','".$nourut[$nopo]."');\">
		//                         </td>
		//                         ";
		//         $stream.="</tr>";
		//     }
		// }
		$stream .= "</table></fieldset>";

		echo $stream;

		break;
	case 'detailhutang':
		$isiDt = explode("/", $nopdo);
		$kodeorght = $isiDt[2];
		#ambil induk organisasi
		$strht = "select tipe,induk from " . $dbname . ".organisasi where  kodeorganisasi='" . $unit . "'";
		$resht = $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht = $resht->fetch();
		$tipe = $barht['tipe'];
		$induk = $barht['induk'];

		// if ($barht['tipe']!='HOLDING') {
		//     continue;
		// }

		$stream = "";
		$stream .= "<fieldset><legend><b>Detail " . $_SESSION['lang']['hutang'] . "</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr>
                        <td align=center rowspan=2 width=30px >" . $_SESSION['lang']['nourut'] . "</td>
                        <td align=center rowspan=2 width=180px >" . $_SESSION['lang']['namasupplier'] . "</td>
                        <td align=center rowspan=2 width=180px >" . $_SESSION['lang']['nopo'] . "</td>
                        <td align=center colspan=4 width=50px >" . $_SESSION['lang']['hutang'] . "</td>
                        <td align=center colspan=2 width=50px >" . $_SESSION['lang']['pembayaran'] . "</td>
                            <td align=center rowspan=2 width=30px >" . $_SESSION['lang']['action'] . "
                                <br><input type=checkbox id=cekallhutang onclick=cekallhutang()></td>
                    </tr>
                    <tr>
                        <td align=center width=80px >" . $_SESSION['lang']['rupiah'] . "</td>
                        <td align=center width=80px >" . $_SESSION['lang']['ppn'] . "</td>
                        <td align=center width=80px >PPh</td>
                        <td align=center width=80px >" . $_SESSION['lang']['total'] . "</td>
                        <td align=center width=80px >" . $_SESSION['lang']['terbayar'] . "</td>
                        <td align=center width=80px >" . $_SESSION['lang']['sisa'] . "</td>
                    </tr>
                </thead><tbody id=contentdetailhutang>";
		// log_po_terima_vw
		#data nopo
		$str = " select * from " . $dbname . ".log_poht where kodeorg='" . $induk . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nilaipo[$bar['nopo']] = $bar['subtotal'];
			$totalpo[$bar['nopo']] = $bar['nilaipo'];
			$ppn[$bar['nopo']] = $bar['ppn'];
			$pph[$bar['nopo']] = $bar['pph'];
			$sup[$bar['nopo']] = $bar['kodesupplier'];
		}
		#ambil noakun supplier untuk po
		$strsup = "select noakun from " . $dbname . ".log_5klsupplier where tipe='SUPPLIER'";
		$ressup = $owlPDO->query($strsup) or die(print " Gagal: " . PDOException::getMessage());
		$ressup->setFetchMode(PDO::FETCH_ASSOC);
		$barsup = $ressup->fetch();
		$noakunsupp = $barsup['noakun'];

		/*$str=" select a.nopo,b.noinvoice,b.nilaiinvoice,b.noakun,c.jumlah,(c.jumlah-b.nilaiinvoice) as selisih from ".$dbname.".log_po_terima_vw2 a "
        . " left join ".$dbname.".keu_tagihanht b on a.nopo=b.nopo "
        . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
        . " where a.kodeorg='".$induk."' and  ((c.jumlah-b.nilaiinvoice < 0) or (jumlah is NULL)) and c.jumlah>0 ";*/
		// $str=" select a.nopo,b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,sum(c.jumlah)-b.nilaiinvoice as selisih from ".$dbname.".log_po_terima_vw2 a "
		//         . " left join ".$dbname.".keu_tagihanht b on a.nopo=b.nopo "
		//         . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
		//         . " where a.kodeorg='".$induk."' and b.nilaiinvoice>0 and c.jumlah>0  "
		//         . "group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
		#ambil dari GR yang belum terdaftar pada tagihan
		$str = "select sum(hargasatuan*jumlah) as nilaipo,notransaksi as nopo,idsupplier as kodesupplier from " . $dbname . ".log_transaksi_vw where
              tipetransaksi=1 and kodept='" . $induk . "' and left(tanggal,7)<='" . $per . "' group by notransaksi having sum(hargasatuan*jumlah)>0";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$sCek = "select * from " . $dbname . ".keu_tagihanht where notransaksi_gr='" . $bar['nopo'] . "'";
			$rCek = fetchData($sCek);
			if (count($rCek) != 0) {
				continue;
			}
			$periodeData = explode("-", $per);
			$sCek2 = "select * from " . $dbname . ".keu_pdodt where nodocument='" . $bar['nopo'] . "' and notransaksi like '" . $periodeData[0] . $periodeData[1] . "%'";
			//exit('warning'.$sCek2);
			$rCek2 = fetchData($sCek2);
			if (count($rCek2) != 0) {
				continue;
			}
			$nomorpo[$bar['nopo']] = $bar['nopo'];
			$nilaipo[$bar['nopo']] = $bar['nilaipo'];
			$noinv[$bar['nopo']] = $bar['noinvoice'];
			$nilaikas[$bar['nopo']] = $bar['jumlah'];
			//$totalpo[$bar['nopo']]=$bar['jumlah'];
			$noakunhutang[$bar['nopo']] = $noakunsupp;
			$sup[$bar['nopo']] = $bar['kodesupplier'];
		}

		#ambil statjurnal
		$kodeall = array();
		$kodejurnal = array();
		$kodetdkjurnal = array();
		$strsup = "select kode,jurnal from " . $dbname . ".keu_5jenistagihan where kode not in ('um','k','p21','p22','p23','p25','ps4','upd') ";
		$ressup = $owlPDO->query($strsup) or die(print " Gagal: " . PDOException::getMessage());
		$ressup->setFetchMode(PDO::FETCH_ASSOC);
		while ($barsup = $ressup->fetch()) {
			if ($barsup['jurnal'] == 1) {
				$kodejurnal[$barsup['kode']] = $barsup['kode'];
			}
			if ($barsup['jurnal'] == 0) {
				$kodetdkjurnal[$barsup['kode']] = $barsup['kode'];
			}
		}

		$str = " select b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,(sum(c.jumlah)-b.nilaiinvoice) as selisih, b.kodesupplier,b.noakun from " . $dbname . ".keu_tagihanht b "
			. " left join " . $dbname . ".keu_kasbankdtht_vw c on b.noinvoice=c.keterangan1"
			. " where b.kodeorg='" . $induk . "' and b.tipeinvoice in ('" . implode("','", $kodejurnal) . "') and b.nilaiinvoice>0 and c.jumlah>0 and left(b.tanggal,7)<='" . $per . "' and c.posting<>1
                 group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$sCek = "select * from " . $dbname . ".keu_pdodt where nodocument='" . $bar['noinvoice'] . "'";
			$rCek = fetchData($sCek);
			if (count($rCek) != 0) {
				continue;
			}
			$noinvtagihan[$bar['noinvoice']] = $bar['noinvoice'];
			$noinvjurnal[$bar['noinvoice']] = $bar['noinvoice'];
			$nilaiht[$bar['noinvoice']] = $bar['nilaiinvoice'];
			$nilaikas[$bar['noinvoice']] = $bar['jumlah'];
			$noakunhutang[$bar['noinvoice']] = $bar['noakun'];
			$sup[$bar['noinvoice']] = $bar['kodesupplier'];
			$noakunhutang[$bar['nopo']] = $bar['noakun'];
		}

		$str = " select b.noinvoice,b.noakun,b.nilai as pph from " . $dbname . ".keu_tagihandt b where b.noinvoice in ('" . implode("','", $noinvjurnal) . "') and left(noakun,3)='213'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$pph[$bar['noinvoice']] = $bar['pph'] * -1;
		}

		$str = " select b.noinvoice,b.nilaiinvoice,b.noakun,sum(c.jumlah) as jumlah,(sum(c.jumlah)-b.nilaiinvoice) as selisih, b.kodesupplier from " . $dbname . ".keu_tagihanht b "
			. " left join " . $dbname . ".keu_kasbankdtht_vw c on b.noinvoice=c.keterangan1"
			. " where b.kodeorg='" . $induk . "' and b.tipeinvoice in ('" . implode("','", $kodetdkjurnal) . "') and b.nilaiinvoice>0 and c.jumlah>0  and left(b.tanggal,7)<='" . $per . "'
                group by c.keterangan1 having ((sum(c.jumlah)-b.nilaiinvoice<-1) or (jumlah is NULL)) ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$sCek = "select * from " . $dbname . ".keu_pdodt where nodocument='" . $bar['noinvoice'] . "'";
			$rCek = fetchData($sCek);
			if (count($rCek) != 0) {
				continue;
			}
			$noinvtagihan[$bar['noinvoice']] = $bar['noinvoice'];
			$noinvtdkjurnal[$bar['noinvoice']] = $bar['noinvoice'];
			$nilaiht[$bar['noinvoice']] = $bar['nilaiinvoice'];
			$nilaikas[$bar['noinvoice']] = $bar['jumlah'];
			$noakunhutang[$bar['noinvoice']] = $bar['noakun'];
			$sup[$bar['noinvoice']] = $bar['kodesupplier'];
		}

		$str = " select b.noinvoice,b.noakun,b.nilai from " . $dbname . ".keu_tagihandt b where b.noinvoice in ('" . implode("','", $noinvtdkjurnal) . "') and (left(noakun,3)='213' or left(noakun,3)='117')";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if (substr($bar['noakun'], 0, 3) == '213') {
				$pph[$bar['noinvoice']] = $bar['nilai'] * -1;
			} else {
				$ppn[$bar['noinvoice']] = $bar['nilai'];
			}
		}

		$str = " select nodocument from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranhutang . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$posave[$bar['nodocument']] = 1;
		}

		if (isset($nomorpo)) {
			foreach ($nomorpo as $nopo) {
				if (@$posave[$nopo] == 1) {
					$cek = "checked=true";
				} else {
					$cek = "";
				}
				$no += 1;
				$sisa[$nopo] = abs($nilaipo[$nopo] - $nilaikas[$nopo]);
				$stream .= "<tr class=rowcontent id=rowhutang" . $no . ">";
				$stream .= "<td align=center>" . $no . "</td>";
				$stream .= "<td align=left id=suphutang" . $no . " hidden>" . $sup[$nopo] . "</td>";
				$stream .= "<td align=left>" . $arrnmsupp[$sup[$nopo]] . "</td>";
				$stream .= "<td align=left id=pohutang" . $no . ">" . $nopo . "</td>";
				$stream .= "<td align=right>" . @number_format($nilaipo[$nopo]) . "</td>";
				$stream .= "<td align=right id=ppnhutang" . $no . ">" . @number_format($ppn[$nopo]) . "</td>";
				$stream .= "<td align=right id=pphhutang" . $no . ">" . @number_format($pph[$nopo]) . "</td>";
				$stream .= "<td align=right id=nilpohutang" . $no . ">" . @number_format($totalpo[$nopo]) . "</td>";
				$stream .= "<td align=right id=kashutang" . $no . ">" . @number_format($nilaikas[$nopo]) . "</td>";
				$stream .= "<td align=right id=sisahutang" . $no . ">" . @number_format($sisa[$nopo]) . "</td>";
				$stream .= "<td align=center><input type=checkbox id=cekhutang" . $no . " " . $cek . "></td>";
				$stream .= "<input type=hidden id=noakunhutang" . $no . " value='" . $noakunhutang[$nopo] . "'>";
				$stream .= "</tr>";
			}
		}

		if (isset($noinvtagihan)) {
			foreach ($noinvtagihan as $invoice) {
				if (@$posave[$invoice] == 1) {
					$cek = "checked=true";
				} else {
					$cek = "";
				}
				$no += 1;
				$nilaipo[$invoice] = $nilaiht[$invoice] - $ppn[$invoice];
				$totalpo[$invoice] = $nilaiht[$invoice] + $ppn[$invoice] - $pph[$invoice];
				$sisa[$invoice] = abs($nilaikas[$invoice] - $totalpo[$invoice]);
				$stream .= "<tr class=rowcontent id=rowhutang" . $no . ">";
				$stream .= "<td align=center>" . $no . "</td>";
				$stream .= "<td align=left id=suphutang" . $no . " hidden>" . $sup[$invoice] . "</td>";
				$stream .= "<td align=left>" . $arrnmsupp[$sup[$invoice]] . "</td>";
				$stream .= "<td align=left id=pohutang" . $no . ">" . $invoice . "</td>";
				$stream .= "<td align=right>" . @number_format($nilaipo[$invoice]) . "</td>";
				$stream .= "<td align=right id=ppnhutang" . $no . ">" . @number_format($ppn[$invoice]) . "</td>";
				$stream .= "<td align=right id=pphhutang" . $no . ">" . @number_format($pph[$invoice]) . "</td>";
				$stream .= "<td align=right id=nilpohutang" . $no . ">" . @number_format($totalpo[$invoice]) . "</td>";
				$stream .= "<td align=right id=kashutang" . $no . ">" . @number_format($nilaikas[$invoice]) . "</td>";
				$stream .= "<td align=right id=sisahutang" . $no . ">" . @number_format($sisa[$invoice]) . "</td>";
				$stream .= "<td align=center><input type=checkbox id=cekhutang" . $no . " " . $cek . "></td>";
				$stream .= "<input type=hidden id=noakunhutang" . $no . " value='" . $noakunhutang[$invoice] . "'>";
				$stream .= "</tr>";
			}
		}

		$stream .= "<tr  class=rowcontent>";
		$stream .= "<td colspan=10 align=right ><button class=mybutton onclick=saveallhutang(" . $no . ");>" . $_SESSION['lang']['proses'] . "</button></td>";
		$stream .= "</tr>";
		if ($optTipe[$unit] == 'HOLDING') {
			echo $stream;
		}
		break;
	#################################################
	#################################################
	#################################################
	case 'deleteincome':
		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranincome . "' "
			. " and nourut='" . $nourutincome . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'updateincome':
		$str = "update " . $dbname . ".keu_pdodt set noakun='" . $akunincome . "',rincian='" . $ketincome . "',
            tanggal='" . $tglawalper . "',satuan='" . $satincome . "',fisik='" . $fisikincome . "',rupiah='" . $totincome . "',
            noakunkas='" . $noakunincome . "',rekeningbank='" . $rekeningbankincome . "'
            where nopdo='" . $nopdo . "' and notransaksi='" . $notranincome . "' and nourut='" . $nourutincome . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'saveincome2':

		if ($noakunincome == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunincome == '1110101') {
			if ($rekeningbankincome == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		#cek apakah HT sudah di-insert
		$str = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='INCOME'
				and notransaksi like '%INCOME/002' limit 1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$cekht = $bar['jumlah'];
		if ($cekht <= 0) {
			$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
			VALUES ('" . $nopdo . "', '" . $notranincome2 . "', '" . $unit . "', '" . $per . "', 'INCOME','" . $_SESSION['standard']['userid'] . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		#delete 1st
		$strins = "delete from " . $dbname . ".`keu_pdodt` where nopdo='" . $nopdo . "' and notransaksi='" . $notranincome2 . "'";
		try {
			$owlPDO->exec($strins);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		$str = "select * from " . $dbname . ".pmn_estimasipenerimaan where pt='" . $kept[$unit] . "' and periode='" . $per . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			#cek nourut
			$strn = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranincome2 . "'"
				. " order by nourut desc limit 1 ";
			$resn = $owlPDO->query($strn) or die(print " Gagal: " . PDOException::getMessage());
			$resn->setFetchMode(PDO::FETCH_ASSOC);
			$barn = $resn->fetch();
			$nourutbaru = $barn['nourut'] + 1;
			$noakunx = '';
			//noaruskas komoditi
			$sappl = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='NAKON'";
			$rappl = fetchData($sappl);
			$noarus = $rappl[0]['nilai'];
			$noarus = explode(',', $noarus);

			switch ($bar['kodebarang']) {
				case '40000001':
					$noakunx = $noarus[0];
					break;
				case '40000002':
					$noakunx = $noarus[1];
					break;
				case '40000003':
					$noakunx = $noarus[2];
					break;
				case '40000005':
					$noakunx = $noarus[3];
					break;
				case '40000004':
					$noakunx = $noarus[4];
					break;
				case '40000016':
					$noakunx = $noarus[5];
					break;
			}
			$strins = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
					`tanggal`, `satuan`,`fisik`, `rupiah`,`noakunkas`, `rekeningbank`)
					VALUES ('" . $nopdo . "', '" . $notranincome2 . "', '" . $nourutbaru . "', '" . $noakunx . "','Estimasi Masuk barang " . $arrnmbrg[$bar['kodebarang']] . "',
					'" . $bar['periode'] . "', 'KG', '" . $bar['qty'] . "','" . ($bar['harga'] * $bar['qty']) . "','" . $noakunincome . "','" . $rekeningbankincome . "')";
			try {
				$owlPDO->exec($strins);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'saveincome':

		if ($noakunincome == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunincome == '1110101') {
			if ($rekeningbankincome == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		#cek apakah HT sudah di-insert
		$str = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='INCOME'
				and notransaksi like '%INCOME/001' limit 1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$cekht = $bar['jumlah'];
		if ($cekht <= 0) {
			$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
			VALUES ('" . $nopdo . "', '" . $notranincome . "', '" . $unit . "', '" . $per . "', 'INCOME','" . $_SESSION['standard']['userid'] . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		#cek nourut
		$str = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranincome . "'"
			. " order by nourut desc limit 1 ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$nourutbaru = $bar['nourut'] + 1;
		$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                `tanggal`, `satuan`,`fisik`, `rupiah`,`noakunkas`, `rekeningbank`)
                VALUES ('" . $nopdo . "', '" . $notranincome . "', '" . $nourutbaru . "', '" . $akunincome . "', '" . $ketincome . "',
                '" . $tglawalper . "', '" . $satincome . "','" . $fisikincome . "','" . $totincome . "','" . $noakunincome . "','" . $rekeningbankincome . "')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'listincome':
		// style='float:left;'
		$stream .= "<fieldset><legend><b>" . $_SESSION['lang']['penerimaandana'] . "</b></legend >
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td align=center width=30px>" . $_SESSION['lang']['nourut'] . "</td>
                        <td align=center>" . $_SESSION['lang']['noakun'] . "</td>
                        <td align=center>" . $_SESSION['lang']['aruskas'] . "</td>
                        <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['kuantitas'] . "</td>
                        <td align=center>" . $_SESSION['lang']['rupiahsatuan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['total'] . "</td>
                        <td align=center width=30px>" . $_SESSION['lang']['action'] . "</td>
                    </tr>
                </thead>";
		//$notrankas=$explnopdo[0].'/'.$explnopdo[2].'/KAS/001';
		$str = "select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%INCOME%' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no += 1;
			$stream .= "<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
                        <td align=center>" . $arrnmakun[$bar['noakunkas']] . "</td>
                        <td align=center>" . $bar['noakun'] . " - " . (($arrnmaruskas[$bar['noakun']] == '') ? $arrnmakun[$bar['noakun']] : $arrnmaruskas[$bar['noakun']]) . "</td>
                        <td align=left>" . (($optket[$bar['rincian']] == '') ? $bar['rincian'] : $optket[$bar['rincian']]) . "</td>
                        <td align=center>" . $bar['satuan'] . "</td>
                        <td align=right>" . @number_format($bar['fisik']) . "</td>
                        <td align=right>" . @number_format($bar['rupiah'] / $bar['fisik']) . "</td>
                        <td align=right>" . @number_format($bar['rupiah']) . "</td>";
			$stream .= "<td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn title='Edit'
                                onclick=\"editincome('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "',
                                    '" . $bar['noakun'] . "','" . $bar['rincian'] . "','" . $bar['satuan'] . "','" . $bar['fisik'] . "',
                                    '" . $bar['rupiah'] / $bar['fisik'] . "','" . $bar['rupiah'] . "','" . $bar['noakunkas'] . "','" . $bar['rekeningbank'] . "');\">";
			$stream .= "            <img src=images/application/application_delete.png class=zImgBtn title='Delete'
                                onclick=\"deletekas('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\">
                        </td>
                </tr>";
		}
		echo $stream;
		break;
	case 'detailincome':

		#ambil data HT
		$strht = "select kodeorg from " . $dbname . ".keu_pdoht where  nopdo='" . $nopdo . "'";
		$resht = $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht = $resht->fetch();
		$kodeorght = $barht['kodeorg'];

		#ambil induk organisasi
		$strht = "select tipe from " . $dbname . ".organisasi where  kodeorganisasi='" . $kodeorght . "'";
		$resht = $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
		$resht->setFetchMode(PDO::FETCH_ASSOC);
		$barht = $resht->fetch();
		$tipe = $barht['tipe'];

		$optrek = $optkas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select noakun,namaakun from " . $dbname . ".keu_5akun where noakun in ('1112101','1112102','1110101') and aktif=1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optkas .= "<option value='" . $bar['noakun'] . "'>" . $bar['namaakun'] . "</option>";
		}

		$str = "select * from " . $dbname . ".keu_5akunbank";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$wheredz = " kodebank='" . $bar['namabank'] . "'";
			$optnama = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank', $wheredz);
			$optrek .= "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
		}

		$stream .= "<fieldset><legend><b>Form</b></legend>";
		$notrankas = $explnopdo[0] . '/' . $explnopdo[2] . '/INCOME' . '/' . $explnopdo[3] . '/001';
		$stream .= "<fieldset style=float:left><legend><b>Input</b></legend>";
		$stream .= "
            <table cellpading=1 cellspacing=1 border=0>
						<tr>
							<td>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td>:</td>
							<td align=left><input type=text id=notranincome style=width:150px disabled value='" . $notrankas . "' onkeypress=\"return tanpa_kutip(event)\" class=myinputtext></td>
						</tr>
						<tr hidden>
							<td>nourutdb</td>
							<td>:</td>
							<td align=left><input type=text id=nourutincome onkeypress=\"return tanpa_kutip(event)\" class=myinputtext></td>
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['noakun'] . "</td>
							<td>:</td>
							<td>
								<select onchange='getrekeningincome()' id=noakunincome style=\"width:155px;\">" . $optkas . "</select>
							</td>
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['rekening'] . "</td>
							<td>:</td>
							<td>
								<select id=rekeningbankincome style=\"width:155px;\">" . $optrek . "</select>
							</td>
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['aruskas'] . "</td>
							<td>:</td>
							<td>
								<select id=akunincome  style=width:155px onchange=getket('dana') >'" . $optaruskas . "'</select>
								<img onclick=\"z.elSearch('akunincome',event)\" class=resicon src=images/onebit_02.png style=position:relative;top:5px>
							</td>
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['keterangan'] . "</td>
							<td>:</td>
							<td align=left>
							<select id=ketincome  style=width:155px >'" . $opt . "'</select></td>
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['satuan'] . "</td>
							<td>:</td>
							<td><select id=satincome style=width:155px>'" . $optsat . "'</select></td>
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['kuantitas'] . "</td>
							<td>:</td>
							<td><input type=text id=fisikincome onkeyup=totalincome() onkeypress='return angka_doang(event)' class=myinputtextnumber  style=width:150px ></td>
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['rupiahsatuan'] . "</td>
							<td>:</td>
							<td><input type=text id=rupsatincome onkeyup=totalincome() onkeypress='return angka_doang(event)' class=myinputtextnumber  style=width:150px ></td>
						</tr>
						<tr>
							<td>" . $_SESSION['lang']['total'] . "</td>
							<td>:</td>
							<td id=totincome align=center></td>
						</tr>";
		if ($barht['tipe'] == 'HOLDING') {
			$stream .= "<tr>
							<td></td><td></td><td><button class=mybutton onclick=saveincome()>" . $_SESSION['lang']['save'] . "</button>
							<button class=mybutton onclick=batalincome()>" . $_SESSION['lang']['cancel'] . "</button></td>
							<input type=hidden id=methodincome value='saveincome'>
						</tr>";
		}

		$stream .= "</table></fieldset>";
		$notrankas2 = $explnopdo[0] . '/' . $explnopdo[2] . '/INCOME' . '/' . $explnopdo[3] . '/002';
		$stream .= "<fieldset style=float:left><legend><b>Otomatis</b></legend>";
		$stream .= "" . $_SESSION['lang']['notransaksi'] . "  :  <input type=text id=notranincome2 disabled value='" . $notrankas2 . "' onkeypress=\"return tanpa_kutip(event)\" class=myinputtext>";

		if ($barht['tipe'] == 'HOLDING') {
			$stream .= "<button class=mybutton onclick=saveincome2()>" . $_SESSION['lang']['proses'] . "</button></td>
							<input type=hidden id=methodincome2 value='saveincome2'>";
		}

		$stream .= "</fieldset>";
		$stream .= "</fieldset>";
		echo $stream;
		break;
	#################################################
	#################################################
	#################################################

	case 'getrekening':
		$optbank = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		if ($noakunpil == '1110101') {
			$str = "select * from " . $dbname . ".keu_5akunbank where pemilik='" . $unit . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$wheredz = " kodebank='" . $bar['namabank'] . "'";
				$optnama = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank', $wheredz);
				if ($rekeningbank == $bar['noakun']) {
					$optbank .= "<option value='" . $bar['noakun'] . "' selected>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
				} else {
					$optbank .= "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
				}
			}
		}

		echo $optbank;

		break;

	case 'getket':
		$optket = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select keterangan,id_ket from " . $dbname . ".keu_5keterangan where noaruskas='" . $akunkas . "'";
		// exit('warning : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($ketkas != '') {
				$optket .= "<option value=" . $bar['id_ket'] . " selected>" . $bar['keterangan'] . "</option>";
			} else {
				$optket .= "<option value=" . $bar['id_ket'] . ">" . $bar['keterangan'] . "</option>";
			}
		}
		echo $optket;
		break;

	case 'datajumlah':

		$note = "";
		$jumlahbaris = array();
		$periodesblm = date("Y-m", strtotime("-2 Month", strtotime($per)));

		$str = "select keterangan1 as notransaksi,noaruskas,sum(a.jumlah) as nilairupiah,b.rekening as rekening from " . $dbname . ".keu_kasbankdt a
    			left join " . $dbname . ".keu_kasbankht b on a.notransaksi=b.notransaksi where noakun2a='" . $noakunkas . "' and a.kodeorg='" . $unit . "'
    			and left(b.tanggal,7)<'" . $per . "' and left(b.tanggal,7)>'" . $periodesblm . "' and a.tipetransaksi='K' and noaruskas<>'' and noaruskas
    			not in ('11700','11800')  and keterangan1 not in (select nodocument FROM " . $dbname . ".`keu_pdodt`) and a.jumlah>0 group by noaruskas";

		//exit('Warning : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			#cek apakah HT sudah di-insert
			$str2 = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "' and sesi='" . $bag . "' and tipepdo='KAS' limit 1";
			$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$cekht = $bar2['jumlah'];
			if ($cekht <= 0) {
				$strht = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('" . $nopdo . "', '" . $notrankas . "', '" . $unit . "', '" . $per . "', 'KAS','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($strht);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			if ($noakunkas != '1110101') {
				$bar['rekening'] = '';
			}

			$strbrs = "select count(*) as jumlahbaris,noakun from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and left(tanggal,7)='" . $per . "' and (notransaksi like '%PJD%' or notransaksi like '%BBM%') and noakun in ('10403','10404') group by noakun";
			$resbrs = $owlPDO->query($strbrs) or die(print " Gagal: " . PDOException::getMessage());
			$resbrs->setFetchMode(PDO::FETCH_ASSOC);
			while ($barbrs = $resbrs->fetch()) {
				$jumlahbaris[$barbrs['noakun']] = $barbrs['jumlahbaris'];
			}

			if ($bar['noaruskas'] == $aruskasbbm || $bar['noaruskas'] == $aruskaspjd) {
				if ($jumlahbaris[$bar['noaruskas']] == 0) {
					$note = "biaya BBM/PJD pada tab Bahan Bakar / Perjalanan Dinas belum diinput. Sedangkan ada biaya BBM dan PJD pada kas.";
					continue;
				}
			}

			#cek nourut
			$str1 = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notrankas . "'"
				. " order by nourut desc limit 1 ";
			$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1 = $res1->fetch();
			$nourutbaru = $bar1['nourut'] + 1;
			$strin = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`,`noakunkas`, `nourut`, `noakun`, `rincian`,
	                `tanggal`, `satuan`,`fisik`, `rupiah`, `nodocument`, `rekeningbank`)
	                VALUES ('" . $nopdo . "', '" . $notrankas . "', '" . $noakunkas . "', '" . $nourutbaru . "', '" . $bar['noaruskas'] . "',
	                '" . $tglawalper . "', '" . $satkas . "', '" . $fisikkas . "','" . $bar['nilairupiah'] . "','" . $bar['notransaksi'] . "','" . $bar['rekening'] . "')";
			// exit('Warning : '.$strin);
			try {
				$owlPDO->exec($strin);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}

		#= arus kas
		$where = '';
		if ($noakunkas == '1112102') {
			#KK
			$where .= " and (akses_rekening='KK' or akses_rekening='') ";
		} else {
			$where .= " and (akses_rekening='KB' or akses_rekening='')";
		}
		if ($optTipe[$unit] == 'HOLDING') {
			$where .= " and pemilik_aruskas in ('GLOBAL','HOLDING') and status='1' and level='3'";
		} else if ($optTipe[$unit] == 'KANWIL') {
			$where .= " and pemilik_aruskas in ('GLOBAL','KANWIL') and status='1' and level='3'";
		} else {
			$where .= " and pemilik_aruskas in ('GLOBAL','UNIT') and status='1' and level='3' and tipetransaksi='K'";
		}

		$optaruskas = "";
		$optaruskas .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "SELECT * FROM " . $dbname . ".keu_5aruskas where 1=1 " . $where . " order by noaruskas asc";
		// exit('warning : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optaruskas .= "<option value=" . $bar['noaruskas'] . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
		}

		$optbank = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		if ($noakunkas == '1110101') {
			$str = "select * from " . $dbname . ".keu_5akunbank where pemilik='" . $unit . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$wheredz = " kodebank='" . $bar['namabank'] . "'";
				$optnama = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank', $wheredz);
				$optbank .= "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
			}
		}

		echo $optaruskas . "##" . $optbank . "##" . $note;

		break;

	case 'deletekas':
		$cekRows = getCountRows($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$notrankas}'");

		if ($cekRows > 1) {
			$str = "delete from " . $dbname . ".keu_pdodt where notransaksi='" . $notrankas . "' and nopdo='" . $nopdo . "' and nourut='" . $idpdo . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$deleteHead = deleteQuery($dbname, "keu_pdoht", "notransaksi='{$notrankas}' AND nopdo='{$nopdo}'");
			try {
				$owlPDO->exec($deleteHead);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}

		break;
	case 'deletehutangkas':
		$cekRows = getCountRows($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$notranhutangkas}'");

		if ($cekRows > 1) {
			$str = "delete from " . $dbname . ".keu_pdodt where notransaksi='" . $notranhutangkas . "' and nopdo='" . $nopdo . "' and nourut='" . $idpdo . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$deleteHead = deleteQuery($dbname, "keu_pdoht", "notransaksi='{$notranhutangkas}' AND nopdo='{$nopdo}'");
			try {
				$owlPDO->exec($deleteHead);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'deletepjd':
		$cekRows = getCountRows($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$notranpjd}'");

		if ($cekRows > 1) {
			$str = "delete from " . $dbname . ".keu_pdodt where notransaksi='" . $notranpjd . "' and nopdo='" . $nopdo . "' and nourut='" . $idpdo . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$deleteHead = deleteQuery($dbname, "keu_pdoht", "notransaksi='{$notranpjd}' AND nopdo='{$nopdo}'");
			try {
				$owlPDO->exec($deleteHead);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'deleteothers':
		$cekRows = getCountRows($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$notranothers}'");

		if ($cekRows > 1) {
			$str = "delete from " . $dbname . ".keu_pdodt where notransaksi='" . $notranothers . "' and nopdo='" . $nopdo . "' and nourut='" . $idpdo . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$deleteHead = deleteQuery($dbname, "keu_pdoht", "notransaksi='{$notranothers}' AND nopdo='{$nopdo}'");
			try {
				$owlPDO->exec($deleteHead);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'deletetanaman':
		$cekRows = getCountRows($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$notrantanaman}'");

		if ($cekRows > 1) {
			$str = "delete from " . $dbname . ".keu_pdodt where notransaksi='" . $notrantanaman . "' and nopdo='" . $nopdo . "' and nourut='" . $idpdo . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$deleteHead = deleteQuery($dbname, "keu_pdoht", "notransaksi='{$notrantanaman}' AND nopdo='{$nopdo}'");
			try {
				$owlPDO->exec($deleteHead);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'deletetraksi':
		$cekRows = getCountRows($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$notrantraksi}'");

		if ($cekRows > 1) {
			$str = "delete from " . $dbname . ".keu_pdodt where notransaksi='" . $notrantraksi . "' and nopdo='" . $nopdo . "' and nourut='" . $idpdo . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$deleteHead = deleteQuery($dbname, "keu_pdoht", "notransaksi='{$notrantraksi}' AND nopdo='{$nopdo}'");
			try {
				$owlPDO->exec($deleteHead);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'deletebyrsup':
		$str = "delete from " . $dbname . ".keu_pdodt where notransaksi='" . $notranbyrsup . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'deletesupplier':
		$cekRows = getCountRows($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$notransupplier}'");

		if ($cekRows > 1) {
			$str = "delete from " . $dbname . ".keu_pdodt where notransaksi='" . $notransupplier . "' and nopdo='" . $nopdo . "' and nourut='" . $idpdo . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$deleteHead = deleteQuery($dbname, "keu_pdoht", "notransaksi='{$notransupplier}' AND nopdo='{$nopdo}'");
			try {
				$owlPDO->exec($deleteHead);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'deletekontraktor':
		$cekRows = getCountRows($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$notrankontraktor}'");

		if ($cekRows > 1) {
			$str = "delete from " . $dbname . ".keu_pdodt where notransaksi='" . $notrankontraktor . "' and nopdo='" . $nopdo . "' and nourut='" . $idpdo . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$deleteHead = deleteQuery($dbname, "keu_pdoht", "notransaksi='{$notrankontraktor}' AND nopdo='{$nopdo}'");
			try {
				$owlPDO->exec($deleteHead);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'updatekas':

		##ambil jumlah rupiah yg diedit
		$jumlahrupiahkasedit = 0;
		$str = "select rupiah as jumlahrupiahkasedit from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notrankas . "' and nourut='" . $nourutkas . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$jumlahrupiahkasedit = $bar['jumlahrupiahkasedit'];

		$jumlahrupiahbbm = 0;
		$jumlahrupiahpjd = 0;
		$$jumlahrupiahkas = 0;
		if ($akunkas == '10403') {
			$str = "select sum(rupiah) as jumlahrupiahbbm from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and left(tanggal,7)='" . $per . "' and notransaksi like '%BBM%'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$jumlahrupiahbbm = $bar['jumlahrupiahbbm'];

			$str = "select sum(rupiah) as jumlahrupiahkas from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and left(tanggal,7)='" . $per . "' and notransaksi like '%KAS%' and noakun='" . $akunkas . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$jumlahrupiahkas = $bar['jumlahrupiahkas'] - $jumlahrupiahkasedit + $totkas;

			if ($jumlahrupiahbbm <= 0 || is_null($jumlahrupiahbbm) || $jumlahrupiahbbm == '') {
				exit('warning : Bahan bakar belum diinput.');
			}

			if ($jumlahrupiahkas > $jumlahrupiahbbm) {
				exit('warning : Jumlah kas melebihi dari tab bahan bakar.');
			}
		}

		if ($akunkas == '10404') {
			$str = "select sum(rupiah) as jumlahrupiahpjd from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and left(tanggal,7)='" . $per . "' and notransaksi like '%PJD%'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$jumlahrupiahpjd = $bar['jumlahrupiahpjd'];

			$str = "select sum(rupiah) as jumlahrupiahkas from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and left(tanggal,7)='" . $per . "' and notransaksi like '%KAS%' and noakun='" . $akunkas . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$jumlahrupiahkas = $bar['jumlahrupiahkas'] - $jumlahrupiahkasedit + $totkas;

			if ($jumlahrupiahpjd <= 0 || is_null($jumlahrupiahpjd) || $jumlahrupiahpjd == '') {
				exit('warning : Perjalanan dinas belum diinput.');
			}

			if ($jumlahrupiahkas > $jumlahrupiahpjd) {
				exit('warning : Jumlah kas melebihi dari tab perjalanan dinas.');
			}
		}

		$str = "update " . $dbname . ".keu_pdodt set noakun='" . $akunkas . "',rincian='" . $ketkas . "',tanggal='" . $tglawalper . "',
        satuan='" . $satkas . "',fisik='" . $fisikkas . "',rupiah='" . $totkas . "',rupiahdiajukan='" . $totkas . "'
            where nopdo='" . $nopdo . "' and notransaksi='" . $notrankas . "' and nourut='" . $nourutkas . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'savekas':
		/* Notransaksi NoPDO Baru */
		if ($nopdo == '') {
			$nopdo = generateKodeTransaksi($unit, $per, "PDO");
		}

		if ($nokas == '') {
			$nokas = generateKodeTransaksi($unit, $per, "KAS");
		}

		try {
			$owlPDO->beginTransaction();

			#cek apakah HT sudah di-insert
			$cekHT = getCountRows($dbname, "keu_pdoht", "nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='KAS' AND sesi='{$sesi}' AND notransaksi='{$nokas}'");
			if ($cekHT == 0) {
				$dataHT = [
					"nopdo" => $nopdo,
					"notransaksi" => $nokas,
					"kodeorg" => $unit,
					"periode" => $per,
					"sesi" => $sesi,
					"tipepdo" => "KAS",
					"updateby" => $_SESSION['standard']['userid'],
				];
				$colsHT = array_keys($dataHT);
				$insertHT = insertQuery($dbname, "keu_pdoht", $dataHT, $colsHT);
				try {
					$owlPDO->exec($insertHT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert HT \n" . addslashes($e->getMessage()));
				}
			}

			// Delete DT First
			$deleteDT = deleteQuery($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$nokas}'");
			$owlPDO->exec($deleteDT);

			$dataArr = explode('$$', $data);
			$counter = 1;
			$startingCounterNodok = getStartingCounter($unit, $per, $tipeKasbank);

			foreach ($dataArr as $val) {
				[$noakun, $nilaiBI, $keterangan, $rpreal, $rupiah, $novoucher, $tipeKasbank, $isManual] = explode('##', $val);

				$nodok = '';
				if ($isManual) {
					$month = date('m', strtotime($per));
					$year = date('Y', strtotime($per));
					$padCounter = str_pad($startingCounterNodok, 4, '0', STR_PAD_LEFT);
					$nodok = $year . $month . "/" . $unit . "/DOK" . $tipeKasbank . "/" . $padCounter;

					$startingCounterNodok++;
				}

				/* Data DT */
				$dataDT = array(
					'nopdo' => $nopdo,
					'notransaksi' => $nokas,
					'nourut' => $counter++,
					'rincian' => $keterangan,
					'rupiahreal' => $rpreal,
					'rupiah' => $rupiah,
					'rupiahdiajukan' => $nilaiBI,
					'nodocument' => $novoucher,
					'noakun' => $noakun,
					'tipekasbank' => $tipeKasbank,
					'nodok' => $nodok,
					'ismanual' => $isManual
				);

				$colsDT = array_keys($dataDT);
				$insertDT = insertQuery($dbname, 'keu_pdodt', $dataDT, $colsDT);

				// EXEC HARUS AKTIF
				$owlPDO->exec($insertDT);
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			exit("Warning: \n" . addslashes($e->getMessage()));
		}
		echo $nopdo . '##' . $nokas;
		break;

	case 'saveothers':
		/* Notransaksi NoPDO Baru */
		if ($nopdo == '') {
			$nopdo = generateKodeTransaksi($unit, $per, "PDO");
		}

		if ($noothers == '') {
			$noothers = generateKodeTransaksi($unit, $per, "OTH");
		}

		try {
			$owlPDO->beginTransaction();

			#cek apakah HT sudah di-insert
			$cekHT = getCountRows($dbname, "keu_pdoht", "nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='OTH' AND sesi='{$sesi}' AND notransaksi='{$noothers}'");
			if ($cekHT == 0) {
				$dataHT = [
					"nopdo" => $nopdo,
					"notransaksi" => $noothers,
					"kodeorg" => $unit,
					"periode" => $per,
					"sesi" => $sesi,
					"tipepdo" => "OTH",
					"updateby" => $_SESSION['standard']['userid'],
				];
				$colsHT = array_keys($dataHT);
				$insertHT = insertQuery($dbname, "keu_pdoht", $dataHT, $colsHT);
				try {
					$owlPDO->exec($insertHT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert HT \n" . addslashes($e->getMessage()));
				}
			}

			// Delete DT First
			$deleteDT = deleteQuery($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$noothers}'");
			$owlPDO->exec($deleteDT);

			$data = explode('$$', $data);
			$counter = 1;
			foreach ($data as $val) {
				[$noakun, $nilaiBI, $keterangan] = explode('##', $val);

				/* Data DT */
				$dataDT = array(
					'nopdo' => $nopdo,
					'notransaksi' => $noothers,
					'nourut' => $counter++,
					'rincian' => $keterangan,
					'rupiahreal' => $nilaiBI,
					'rupiahdiajukan' => $nilaiBI,
					'noakun' => $noakun,
				);
				$colsDT = array_keys($dataDT);
				$insertDT = insertQuery($dbname, 'keu_pdodt', $dataDT, $colsDT);
				try {
					$owlPDO->exec($insertDT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert DT \n" . addslashes($e->getMessage()));
				}
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			exit("Warning: \n" . addslashes($e->getMessage()));
		}
		// exit("Warning: ".$insertHT."; \n".$insertDT);

		echo $nopdo . '##' . $noothers;
		break;

	case 'savetanaman':
		/* Notransaksi NoPDO Baru */
		if ($nopdo == '') {
			$nopdo = generateKodeTransaksi($unit, $per, "PDO");
		}

		if ($notanaman == '') {
			$notanaman = generateKodeTransaksi($unit, $per, "TNM");
		}

		try {
			$owlPDO->beginTransaction();

			#cek apakah HT sudah di-insert
			$cekHT = getCountRows($dbname, "keu_pdoht", "nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='TNM' AND sesi='{$sesi}' AND notransaksi='{$notanaman}'");
			if ($cekHT == 0) {
				$dataHT = [
					"nopdo" => $nopdo,
					"notransaksi" => $notanaman,
					"kodeorg" => $unit,
					"periode" => $per,
					"sesi" => $sesi,
					"tipepdo" => "TNM",
					"updateby" => $_SESSION['standard']['userid'],
				];
				$colsHT = array_keys($dataHT);
				$insertHT = insertQuery($dbname, "keu_pdoht", $dataHT, $colsHT);
				try {
					$owlPDO->exec($insertHT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert HT \n" . addslashes($e->getMessage()));
				}
			}

			// Delete DT First
			$deleteDT = deleteQuery($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$notanaman}'");
			$owlPDO->exec($deleteDT);

			$data = explode('$$', $data);
			$counter = 1;
			foreach ($data as $val) {
				[$noakun, $nilaiBI, $rpReal, $potBpjs, $potAlatpanen, $potPenalty, $potKontanan] = explode('##', $val);

				/* Data DT */
				$dataDT = array(
					'nopdo' => $nopdo,
					'notransaksi' => $notanaman,
					'nourut' => $counter++,
					'rupiahreal' => $rpReal,
					'rupiahdiajukan' => $nilaiBI,
					'potbpjs' => $potBpjs,
					'potalatpnn' => $potAlatpanen,
					'potpenalty' => $potPenaly,
					'potkontanan' => $potKontanan,
					'noakun' => $noakun,
				);
				$colsDT = array_keys($dataDT);
				$insertDT = insertQuery($dbname, 'keu_pdodt', $dataDT, $colsDT);
				try {
					$owlPDO->exec($insertDT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert DT \n" . addslashes($e->getMessage()));
				}
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			exit("Warning: \n" . addslashes($e->getMessage()));
		}

		echo $nopdo . '##' . $notanaman;
		break;

	case 'savetraksi':
		/* Notransaksi NoPDO Baru */
		if ($nopdo == '') {
			$nopdo = generateKodeTransaksi($unit, $per, "PDO");
		}

		if ($notraksi == '') {
			$notraksi = generateKodeTransaksi($unit, $per, "TNM");
		}

		try {
			$owlPDO->beginTransaction();

			#cek apakah HT sudah di-insert
			$cekHT = getCountRows($dbname, "keu_pdoht", "nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='TRK' AND sesi='{$sesi}' AND notransaksi='{$notraksi}'");
			if ($cekHT == 0) {
				$dataHT = [
					"nopdo" => $nopdo,
					"notransaksi" => $notraksi,
					"kodeorg" => $unit,
					"periode" => $per,
					"sesi" => $sesi,
					"tipepdo" => "TRK",
					"updateby" => $_SESSION['standard']['userid'],
				];
				$colsHT = array_keys($dataHT);
				$insertHT = insertQuery($dbname, "keu_pdoht", $dataHT, $colsHT);
				try {
					$owlPDO->exec($insertHT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert HT \n" . addslashes($e->getMessage()));
				}
			}

			// Delete DT First
			$deleteDT = deleteQuery($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$notraksi}'");
			$owlPDO->exec($deleteDT);

			$data = explode('$$', $data);
			$counter = 1;
			foreach ($data as $val) {
				[$noakun, $nilaiBI, $rpReal, $rupiah] = explode('##', $val);

				/* Data DT */
				$dataDT = array(
					'nopdo' => $nopdo,
					'notransaksi' => $notraksi,
					'nourut' => $counter++,
					'rupiahreal' => $rpReal,
					'rupiahdiajukan' => $nilaiBI,
					'rupiah' => $rupiah,
					'noakun' => $noakun,
				);
				$colsDT = array_keys($dataDT);
				$insertDT = insertQuery($dbname, 'keu_pdodt', $dataDT, $colsDT);
				try {
					$owlPDO->exec($insertDT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert DT \n" . addslashes($e->getMessage()));
				}
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			exit("Warning: \n" . addslashes($e->getMessage()));
		}

		echo $nopdo . '##' . $notraksi;
		break;

	case 'savehutangkas':
		/* Notransaksi NoPDO Baru */
		if ($nopdo == '') {
			$nopdo = generateKodeTransaksi($unit, $per, "PDO");
		}

		if ($nohutangkas == '') {
			$nohutangkas = generateKodeTransaksi($unit, $per, "HTGK");
		}

		try {
			$owlPDO->beginTransaction();

			#cek apakah HT sudah di-insert
			$cekHT = getCountRows($dbname, "keu_pdoht", "nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='HTGK' AND sesi='{$sesi}' AND notransaksi='{$nohutangkas}'");
			if ($cekHT == 0) {
				$dataHT = [
					"nopdo" => $nopdo,
					"notransaksi" => $nohutangkas,
					"kodeorg" => $unit,
					"periode" => $per,
					"sesi" => $sesi,
					"tipepdo" => "HTGK",
					"updateby" => $_SESSION['standard']['userid'],
				];
				$colsHT = array_keys($dataHT);
				$insertHT = insertQuery($dbname, "keu_pdoht", $dataHT, $colsHT);
				try {
					$owlPDO->exec($insertHT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert HT \n" . addslashes($e->getMessage()));
				}
			}

			// Delete DT First
			$deleteDT = deleteQuery($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$nohutangkas}'");
			$owlPDO->exec($deleteDT);

			$data = explode('$$', $data);
			$counter = 1;
			foreach ($data as $val) {
				[$noakun, $nilaiBI, $rpreal, $rupiah, $keterangan] = explode('##', $val);

				/* Data DT */
				$dataDT = array(
					'nopdo' => $nopdo,
					'notransaksi' => $nohutangkas,
					'nourut' => $counter++,
					'rincian' => $keterangan,
					'rupiahreal' => $rpreal,
					"rupiah" => $rupiah,
					'rupiahdiajukan' => $nilaiBI,
					'noakun' => $noakun,
				);
				$colsDT = array_keys($dataDT);
				$insertDT = insertQuery($dbname, 'keu_pdodt', $dataDT, $colsDT);
				try {
					$owlPDO->exec($insertDT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert DT \n" . addslashes($e->getMessage()));
				}
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			exit("Warning: \n" . addslashes($e->getMessage()));
		}

		echo $nopdo . '##' . $nohutangkas;
		break;

	case 'savepjd':
		/* Notransaksi NoPDO Baru */
		if ($nopdo == '') {
			$nopdo = generateKodeTransaksi($unit, $per, "PDO");
		}

		if ($nopjd == '') {
			$nopjd = generateKodeTransaksi($unit, $per, "PJD");
		}

		try {
			$owlPDO->beginTransaction();

			#cek apakah HT sudah di-insert
			$cekHT = getCountRows($dbname, "keu_pdoht", "nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='PJD' AND sesi='{$sesi}' AND notransaksi='{$nopjd}'");
			if ($cekHT == 0) {
				$dataHT = [
					"nopdo" => $nopdo,
					"notransaksi" => $nopjd,
					"kodeorg" => $unit,
					"periode" => $per,
					"sesi" => $sesi,
					"tipepdo" => "PJD",
					"updateby" => $_SESSION['standard']['userid'],
				];
				$colsHT = array_keys($dataHT);
				$insertHT = insertQuery($dbname, "keu_pdoht", $dataHT, $colsHT);
				try {
					$owlPDO->exec($insertHT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert HT \n" . addslashes($e->getMessage()));
				}
			}

			// Delete DT First
			$deleteDT = deleteQuery($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$nopjd}'");
			$owlPDO->exec($deleteDT);

			$data = explode('$$', $data);
			$counter = 1;
			foreach ($data as $val) {
				[$noakun, $nilaiBI, $rpreal, $rupiah, $tanggal, $notransaksi] = explode('##', $val);

				/* Data DT */
				$dataDT = array(
					'nopdo' => $nopdo,
					'notransaksi' => $nopjd,
					'nourut' => $counter++,
					'tanggal' => $tanggal,
					'nodocument' => $notransaksi,
					'rupiahdiajukan' => $nilaiBI,
					'rupiahreal' => $rpreal,
					'rupiah' => $rupiah,
					'noakun' => $noakun,
				);
				$colsDT = array_keys($dataDT);
				$insertDT = insertQuery($dbname, 'keu_pdodt', $dataDT, $colsDT);
				try {
					$owlPDO->exec($insertDT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert DT \n" . addslashes($e->getMessage()));
				}
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			exit("Warning: \n" . addslashes($e->getMessage()));
		}

		echo $nopdo . '##' . $nopjd;
		break;

	case 'savekontraktor':
		/* Notransaksi NoPDO Baru */
		if ($nopdo == '') {
			$nopdo = generateKodeTransaksi($unit, $per, "PDO");
		}

		if ($nokontraktor == '') {
			$nokontraktor = generateKodeTransaksi($unit, $per, "KTRK");
		}

		try {
			$owlPDO->beginTransaction();

			#cek apakah HT sudah di-insert
			$cekHT = getCountRows($dbname, "keu_pdoht", "nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='KTRK' AND sesi='{$sesi}' AND notransaksi='{$nokontraktor}'");
			if ($cekHT == 0) {
				$dataHT = [
					"nopdo" => $nopdo,
					"notransaksi" => $nokontraktor,
					"kodeorg" => $unit,
					"periode" => $per,
					"sesi" => $sesi,
					"tipepdo" => "KTRK",
					"updateby" => $_SESSION['standard']['userid'],
				];
				$colsHT = array_keys($dataHT);
				$insertHT = insertQuery($dbname, "keu_pdoht", $dataHT, $colsHT);
				try {
					$owlPDO->exec($insertHT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert HT \n" . addslashes($e->getMessage()));
				}
			}

			// Delete DT First
			$deleteDT = deleteQuery($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$nokontraktor}'");
			$owlPDO->exec($deleteDT);

			$data = explode('$$', $data);
			$counter = 1;
			foreach ($data as $val) {
				[$noinvoice, $nopo, $tipeinvoice, $kodesupplier, $nilaiinvoice, $kodekegiatan] = explode('##', $val);

				/* Data DT */
				$dataDT = array(
					'nopdo' => $nopdo,
					'notransaksi' => $nokontraktor,
					'nourut' => $counter++,
					"nodocument" => $nopo,
					"noinvoice" => $noinvoice,
					"kodesupplier" => $kodesupplier,
					"rupiahreal" => $nilaiinvoice,
					"rupiahdiajukan" => $nilaiinvoice,
					"noakun" => $kodekegiatan,
				);
				$colsDT = array_keys($dataDT);
				$insertDT = insertQuery($dbname, 'keu_pdodt', $dataDT, $colsDT);
				try {
					$owlPDO->exec($insertDT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert DT \n" . addslashes($e->getMessage()));
				}
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			exit("Warning: \n" . addslashes($e->getMessage()));
		}
		// exit("Warning: ".$insertHT."; \n".$insertDT);

		echo $nopdo . '##' . $nokontraktor;
		break;

	case 'savesupplier':
		/* Notransaksi NoPDO Baru */
		if ($nopdo == '') {
			$nopdo = generateKodeTransaksi($unit, $per, "PDO");
		}

		if ($nosupplier == '') {
			$nosupplier = generateKodeTransaksi($unit, $per, "SUPP");
		}

		try {
			$owlPDO->beginTransaction();

			#cek apakah HT sudah di-insert
			$cekHT = getCountRows($dbname, "keu_pdoht", "nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='SUPPLIER' AND sesi='{$sesi}' AND notransaksi='{$nosupplier}'");
			if ($cekHT == 0) {
				$dataHT = [
					"nopdo" => $nopdo,
					"notransaksi" => $nosupplier,
					"kodeorg" => $unit,
					"periode" => $per,
					"sesi" => $sesi,
					"tipepdo" => "SUPPLIER",
					"updateby" => $_SESSION['standard']['userid'],
				];
				$colsHT = array_keys($dataHT);
				$insertHT = insertQuery($dbname, "keu_pdoht", $dataHT, $colsHT);
				try {
					$owlPDO->exec($insertHT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert HT \n" . addslashes($e->getMessage()));
				}
			}

			// Delete DT First
			$deleteDT = deleteQuery($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$nosupplier}'");
			$owlPDO->exec($deleteDT);

			$data = explode('$$', $data);
			$counter = 1;
			foreach ($data as $val) {
				[$noinvoice, $nopo, $tipeinvoice, $kodesupplier, $nilaiinvoice, $kodekegiatan, $nogrn] = explode('##', $val);

				/* Data DT */
				$dataDT = array(
					'nopdo' => $nopdo,
					'notransaksi' => $nosupplier,
					'nourut' => $counter++,
					"nodocument" => $nopo,
					"noinvoice" => $noinvoice,
					"kodesupplier" => $kodesupplier,
					"rupiahdiajukan" => $nilaiinvoice,
					"kegiatan" => $kodekegiatan,
					"rincian" => $nogrn,
				);
				$colsDT = array_keys($dataDT);
				$insertDT = insertQuery($dbname, 'keu_pdodt', $dataDT, $colsDT);
				try {
					$owlPDO->exec($insertDT);
				} catch (PDOException $e) {
					exit("Warning: Failed Insert DT \n" . addslashes($e->getMessage()));
				}
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			exit("Warning: \n" . addslashes($e->getMessage()));
		}
		// exit("Warning: ".$insertHT."; \n".$insertDT);

		echo $nopdo . '##' . $nosupplier;
		break;

	case 'savekasdisetujui':
		$str = "update " . $dbname . ".keu_pdodt set rupiah='" . $kasdisetujui . "'
            where nopdo='" . $nopdo . "' and notransaksi='" . $notrankas . "' and nourut='" . $nourutkas . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	########################################################
	#################  T A B   U P A H  ####################
	########################################################
	case 'deleteupah':
		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'simpanupah':

		$strsch = "select notransaksi from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi LIKE '%UPAH%' group by notransaksi order by notransaksi desc";
		$ressch = fetchdata($strsch);
		if (count($ressch) > 0) {
			$noupah = $ressch[0]['notransaksi'];
		}
		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $noupah . "'   and nourut='" . $idpdo . "' ";
		// echo $str="delete from ".$dbname.".keu_pdodt where nopdo='".$nopdo."' and notransaksi LIKE '".($upah!=''?$upah:'%UPAH%')."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		$strcek = "select * from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "'";
		$rescek = fetchData($strcek);
		if (count($rescek) == 0) {
			$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `kodeorg`, `periode`, `sesi`, `updateby`)
					VALUES ('" . $nopdo . "',  '" . $unit . "', '" . $per . "','" . $sesi . "','" . $_SESSION['standard']['userid'] . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}

		if ($check == 'true') {
			$estimasi = 0 - $estimasi;
		}
		if ($tporg[$unit] == 'PABRIK' || $tporg[$unit] == 'KANWIL') {
			$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `divisi`, `rincian`, `rupiahreal`, `rupiah`,`rupiahdiajukan`,`tipekaryawan`,`kelkomponengaji`) VALUES ('" . $nopdo . "', '" . $noupah . "', '" . $idpdo . "', '" . $uraian . "','" . $keterangan . "','" . $shi . "', '" . $estimasi . "','" . $sdbi . "','" . $tkid . "','" . $kelkomponengaji . "')";
		} else {
			$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`, `rupiahreal`, `rupiah`,`rupiahdiajukan`,`tipekaryawan`,`kelkomponengaji`) VALUES ('" . $nopdo . "', '" . $noupah . "', '" . $idpdo . "', '" . $uraian . "','" . $keterangan . "','" . $shi . "', '" . $estimasi . "','" . $sdbi . "','" . $tkid . "','" . $kelkomponengaji . "')";
		}

		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal !: " . $e->getMessage() . "\n";
			die();
		}

		break;
	case 'nopdo':
		/* cek apakah data sudah posting atau belum */
		$str = selectQuery($dbname, 'keu_pdoht', 'COUNT(*) AS posting', "kodeorg = '" . $unit . "' AND periode = '" . $per . "' AND sesi = '" . $sesi . "' AND posting = '1'");
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$posting = $bar['posting'];
		}
		if ($posting > 0) {
			exit("Warning : PDO untuk " . $unit . " di bulan-tahun " . $per . " periode " . $sesi . " sudah di posting ");
		}
		break;

	case 'noupah':
		$noupah = generateKodeTransaksi($unit, $per, "UPAH");
		echo $noupah;
		break;

	case 'detailupah':
		/* HEADER */
		$head = array();

		# Dapetin komponen BPJS buat di exclude
		$arrKomponenBPJS = fetchData(selectQuery($dbname, "sdm_ho_component", "id", "name LIKE '%BPJS%'"));
		foreach ($arrKomponenBPJS as $row) {
			$notExclude = [44, 3, 61, 67, 81];
			if (!in_array($row['id'], $notExclude)) {
				$arrBpjs[$row['id']] = $row['id'];
			}
		}
		$listBpjs = join("','", $arrBpjs);

		/* ID Componen */
		$dtkomplus = [];
		$dtkommin = [];
		$sCom = "SELECT a.idkomponen,b.name, b.plus FROM " . $dbname . ".sdm_gaji a LEFT JOIN " . $dbname . ".sdm_ho_component b ON a.idkomponen=b.id
                WHERE a.kodeorg = '{$unit}' AND idkomponen NOT IN ('{$listBpjs}') GROUP BY a.idkomponen";
		$qCom = fetchData($sCom);
		foreach ($qCom as $val) {
			if ($val['plus'] == 1) {
				$dtkomplus[$val['idkomponen']] = $val['name'];
			} else {
				$dtkommin[$val['idkomponen']] = $val['name'];
			}
		}
		// Penambahan komponen diluar gaji
		if (!array_key_exists('32', $dtkomplus)) {
			$dtkomplus['32'] = "Premi";
		}
		$dtkomplus['899'] = "Upah Angkut dan Langsir";
		$dtkomplus['990'] = "Premi Reward Karyawan";
		$dtkomplus['991'] = "Premi Reward Sembako";
		$dtkomplus['992'] = "Premi Angkut dan Langsir";
		ksort($dtkomplus);
		ksort($dtkommin);
		// Merge Array
		$head = $dtkomplus + $dtkommin;

		/* Divisi */
		$div = array();
		$sDiv = selectQuery($dbname, "organisasi", "kodeorganisasi,namaorganisasi,tipe", "(kodeorganisasi = '" . $unit . "' OR induk = '" . $unit . "') AND (tipe = 'AFDELING' OR tipe = 'BIBITAN' OR tipe = 'KEBUN' OR tipe = 'PABRIK' OR tipe = 'TRAKSI' OR tipe = 'WORKSHOP' OR tipe = 'STATION' OR tipe = 'GUDANG')", "kodeorganisasi");
		$qDiv = fetchData($sDiv);
		foreach ($qDiv as $val) {
			if (strlen($val['kodeorganisasi']) == 4) {
				$div[''] = "KANTOR";
			} else {
				$div[$val['kodeorganisasi']] = $val['namaorganisasi'];
			}
		}

		/* NON STAFF, SKU, KHT, BORONGAN */
		$idTipeKar = array(1, 3, 4, 15);
		$tanggalAkhirBulan = getLastDayOfMonth($per);

		/* Tipe Karyawan */
		$sTpKar = selectQuery($dbname, "sdm_5tipekaryawan", "id,tipe", "id IN (" . implode(",", $idTipeKar) . ")");
		$qTpKar = fetchData($sTpKar);

		/* Jumlah Tenaga Kerja */
		$sTk = selectQuery($dbname, "datakaryawan", "subbagian,tipekaryawan,COUNT(*) AS total", "subbagian IN ('" . implode("','", array_keys($div)) . "') AND tipekaryawan IN (" . implode(',', $idTipeKar) . ") GROUP BY subbagian,tipekaryawan");
		$qTk = fetchData($sTk);
		$tenagaKerja = array();
		foreach ($qTk as $val) {
			$tenagaKerja[$val['subbagian']][$val['tipekaryawan']] = $val['total'];
			$tenagaKerja[$val['subbagian']]['total'] += $val['total'];
			$tenagaKerja['grandtotal'] += $val['total'];
		}

		/* Data SDM GAJI */
		# Bisa di refactor tapi nanti lah
		$qGaji = selectQuery($dbname, "sdm_gaji_vw", "hk, SUM(jumlah) as jumlah, subbagian, tipekaryawan, idkomponen", "kodeorg='{$unit}' AND periodegaji='{$per}' AND tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND idkomponen NOT IN('{$listBpjs}') GROUP BY subbagian, tipekaryawan, idkomponen");
		$rGaji = fetchData($qGaji);
		$dataUpah = [];
		$pengaliKomponen = makeOption($dbname, "sdm_ho_component", "id,plus");
		$tipe_org = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		foreach ($rGaji as $val) {
			// Non-staff ambil gaji lemburnya aja
			if ($val['tipekaryawan'] == 1) {
				if ($val['idkomponen'] == 32) { // Non-Staff tambah premi
					$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];
					$dataUpah[$divisi][$val['tipekaryawan']]['hk'] += $val['hk'];

					// If komponen minus/pengurang
					if ($pengaliKomponen[$val['idkomponen']] == 0) {
						$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'] * -1;
						$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'] * -1;

						$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'] * -1;
						$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'] * -1;
						$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'] * -1;
						$dataUpah['grandtotal'] += $val['jumlah'] * -1;
					} else {
						// Default komponen plus
						$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'];
						$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'];

						$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
						$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
						$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];
						$dataUpah['grandtotal'] += $val['jumlah'];
					}

					$dataUpah[$divisi]['totalhk'] += $val['hk'];
				}
				if ($val['idkomponen'] == 33) { // Non-Staff tambah lembur
					$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];
					$dataUpah[$divisi][$val['tipekaryawan']]['hk'] += $val['hk'];

					// If komponen minus/pengurang
					if ($pengaliKomponen[$val['idkomponen']] == 0) {
						$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'] * -1;
						$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'] * -1;

						$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'] * -1;
						$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'] * -1;
						$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'] * -1;
						$dataUpah['grandtotal'] += $val['jumlah'] * -1;
					} else {
						// Default komponen plus
						$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'];
						$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'];

						$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
						$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
						$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];
						$dataUpah['grandtotal'] += $val['jumlah'];
					}

					$dataUpah[$divisi]['totalhk'] += $val['hk'];
				}
			} else {
				$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];
				$dataUpah[$divisi][$val['tipekaryawan']]['hk'] += $val['hk'];

				// If komponen minus/pengurang
				if ($pengaliKomponen[$val['idkomponen']] == 0) {
					$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'] * -1;
					$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'] * -1;

					$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'] * -1;
					$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'] * -1;
					$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'] * -1;
					$dataUpah['grandtotal'] += $val['jumlah'] * -1;
				} else {
					// Default komponen plus
					$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'];
					$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'];

					$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
					$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
					$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];
					$dataUpah['grandtotal'] += $val['jumlah'];
				}

				$dataUpah[$divisi]['totalhk'] += $val['hk'];
			}
		}

		# Tambahkan komponen premi dengan premi kemandoran dan premi konsumsi
		$qPremikemandoran = "SELECT SUM(a.premiinput) as jumlah, b.tipekaryawan, b.subbagian, a.divisi, a.karyawanid FROM kebun_premikemandoran a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.kodeorg='{$unit}' AND a.periode='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND a.posting='1' AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY a.karyawanid, b.subbagian, b.tipekaryawan";
		$rPremikemandoran = fetchData($qPremikemandoran);
		foreach ($rPremikemandoran as $val) {
			// Ini akomodir buat karyawan double, jika subbagian asal datakaryawan beda dengan unit maka dia pakai subbagian dari kebun_premikemandoran
			$asalUnit = makeOption($dbname, "datakaryawan_bulanan", "karyawanid,lokasitugas", "karyawanid='{$val['karyawanid']}' AND periode='{$per}'")[$val['karyawanid']];
			if (substr($val['divisi'], 0, 4) != $asalUnit) {
				// $xxx .= substr($val['divisi'], 0, 4) . " - " . $asalUnit . " - " . $unit . " - " . $val['karyawanid'] . "<br/>";
				$divisi = ($val['divisi'] == "") ? "KANTOR" : $val['divisi'];
			} else {
				$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];
			}

			$dataUpah[$divisi][$val['tipekaryawan']]['32'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['32'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}

		$qPremikonsumsi = "SELECT SUM(a.nilaikonsumsi) as jumlah, b.tipekaryawan, b.subbagian FROM kebun_premikonsumsi a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.unit='{$unit}' AND b.lokasitugas='{$unit}' AND left(a.tanggal,7)='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND a.fisikkonsumsi='0' AND a.posting='1' AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY b.subbagian, b.tipekaryawan";
		$rPremikonsumsi = fetchData($qPremikonsumsi);
		foreach ($rPremikonsumsi as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['32'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['32'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}
		// echo "<pre>";
		// print_r($dataUpah["KANTOR"]['1']);
		// exit("Warning");

		# Dapatkan upah & premi diluar gaji
		$qAngkutMuatLangsir = "SELECT SUM(a.nilairupiah) as jumlah, b.tipekaryawan, b.subbagian FROM kebun_spbbmlangsir_vw a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.unit='{$unit}' AND b.lokasitugas='{$unit}' AND a.periode='{$per}' AND b.periode='{$per}' AND a.posting='1' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' AND a.isKontan='0' GROUP BY b.subbagian, b.tipekaryawan";
		$rAngkutMuatLangsir = fetchData($qAngkutMuatLangsir);
		foreach ($rAngkutMuatLangsir as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['899'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['899'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}
		### Premi Reward Produksi ###
		$qPremirewardproduksi = "SELECT SUM(a.totalbonus) as jumlah, b.tipekaryawan, b.subbagian FROM kebun_3prestasipemanen a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.kodeorg='{$unit}' AND b.lokasitugas='{$unit}' AND a.periode='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND a.posting='1' AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY b.subbagian, b.tipekaryawan";
		$rPremirewardproduksi = fetchData($qPremirewardproduksi);
		foreach ($rPremirewardproduksi as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['990'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['990'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}
		### Premi Reward Sembako ###
		$qPremirewardsembako = "SELECT SUM(a.rprewardbonus) as jumlah, b.tipekaryawan, b.subbagian FROM kebun_3rewardpemanen a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.kodeorg='{$unit}' AND b.lokasitugas='{$unit}' AND a.periode='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND a.posting='1' AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY b.subbagian, b.tipekaryawan";
		$rPremirewardsembako = fetchData($qPremirewardsembako);
		foreach ($rPremirewardsembako as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['991'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['991'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}
		### Premi BM & Langsir ###
		$qPremibmlangsir = "SELECT SUM(a.totalbonus) as jumlah, b.tipekaryawan, b.subbagian FROM kebun_3prestasimuatlangsir a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.kodeorg='{$unit}' AND b.lokasitugas='{$unit}' AND a.periode='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND a.posting='1' AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY b.subbagian, b.tipekaryawan";
		$rPremibmlangsir = fetchData($qPremibmlangsir);
		foreach ($rPremibmlangsir as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['992'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['992'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}
		### Premi PMK ###
		$qPremipmk = "SELECT SUM(a.premipmk) as jumlah, b.tipekaryawan, b.subbagian FROM sdm_absensidt a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.kodeorg='{$unit}' AND b.lokasitugas='{$unit}' AND left(a.tanggal,7)='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY b.subbagian, b.tipekaryawan";
		$rPremipmk = fetchData($qPremipmk);
		foreach ($rPremipmk as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['32'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['32'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}
		// echo "<pre>";
		// print_r($dataUpah["KANTOR"][1]);
		// exit("Warning");

		$border = 0;
		if ($tipex == "excel") {
			$border = 1;
		}

		$tab = "<fieldset>
                    <legend><b>Detail Input</b></legend>
                    <table cellpadding=7 cellspacing=1 border={$border} class='sortable table-scroll' style='margin-bottom: 20px;width:100%'>
                        <thead>
                            <tr class='rowheader'>";
		$tab .= "<td align='center'>" . $_SESSION['lang']['nourut'] . "</td>";
		$tab .= "<td align='center'>Bagian/Sub Bagian</td>";
		$tab .= "<td align='center'>" . $_SESSION['lang']['tipekaryawan'] . "</td>";
		$tab .= "<td align='center'>Jumlah<br>Tenaga Kerja</td>";
		$tab .= "<td align='center'>Jumlah<br>Tenaga HK</td>";
		foreach ($head as $val) {
			$tab .= "<td align='center'>" . $val . "</td>";
		}
		$tab .= "<td align='center' style='display:none'>Nilai Realiasasi BI</td>";
		$tab .= "<td align='center' style='display:none'>Pengurang</td>";
		$tab .= "<td align='center' style='display:none'>(+/-)</td>";
		$tab .= "<td align='center'>Nilai PDO BI</td>";
		$tab .= "<td align='center'>" . $_SESSION['lang']['keterangan'] . "</td>";
		$tab .= "</tr>
                        </thead>
                        <tbody>";

		$noo = 0;
		foreach ($div as $codediv => $namediv) {
			$codediv = ($codediv == "") ? "KANTOR" : $codediv;
			$noo++;
			foreach ($qTpKar as $key => $tipe) {
				$tab .= "<tr class='rowcontent input' data-subbagian='" . $codediv . "' data-tipekaryawan='" . $tipe['id'] . "' data-jlhtk='" . intval($tenagaKerja[$codediv][$tipe['id']]) . "' data-jlhhk='" . number_format($dataUpah[$codediv][$tipe['id']]['hk'], 2) . "'>";
				if ($key == 0) {
					$tab .= "<td valign='top' align='center' rowspan='" . (count($qTpKar) + 1) . "'>" . $noo . "</td>";
					if (strlen($codediv) == 4) {
						$tab .= "<td valign='top' align='center' rowspan='" . (count($qTpKar)) . "'>KANTOR</td>";
					} else {
						$tab .= "<td valign='top' align='center' rowspan='" . (count($qTpKar)) . "'>" . getNamaOrg($codediv) . "</td>";
					}
				}
				$tab .= "<td>" . $tipe['tipe'] . "</td>";
				$tab .= "<td align='center'>" . intval($tenagaKerja[$codediv][$tipe['id']]) . "</td>";
				$tab .= "<td align='center'>" . number_format($dataUpah[$codediv][$tipe['id']]['hk'], 2) . "</td>";
				foreach ($head as $idcomp => $val) {
					$tab .= "<td align='center'><input type='text' class='myinputtextnumber komponengaji_rp' data-idkomponen='{$idcomp}' oninput=\"numberOnly(this); calcBIUpah('{$codediv}','{$tipe['id']}')\" style='width:100px' value='" . number_format($dataUpah[$codediv][$tipe['id']][$idcomp], 2) . "' id='{$noo}_{$codediv}_{$tipe['id']}_{$idcomp}_komponengaji'/> </td>";
				}
				$tab .= "<td class='nilairealisasi' data-nilairealisasi='{$dataUpah[$codediv][$tipe['id']]['totalbi']}' style='display:none'>" . number_format($dataUpah[$codediv][$tipe['id']]['totalbi'], 2) . "</td>";
				$tab .= "<td align='center' style='display:none'> <input type='checkbox' class='pengurang' onchange=\"countBI('" . $codediv . "', '" . $tipe['id'] . "')\" value='0'> </td>";
				$tab .= "<td style='display:none'><input type='text' class='myinputtextnumber plusminus' oninput=\"countBI('" . $codediv . "', '" . $tipe['id'] . "')\" onkeyup=\"z.numberFormat('{$noo}_{$tipe['id']}_plusminus',0);\" id='{$noo}_{$tipe['id']}_plusminus' onkeypress=return angka_doang(event); style='width:50px;'> </td>";
				$tab .= "<td align='center' class='nilaibi' data-nilaibi='{$dataUpah[$codediv][$tipe['id']]['totalbi']}'>" . number_format($dataUpah[$codediv][$tipe['id']]['totalbi'], 2) . "</td>";
				$tab .= "<td> <input type='text' class='myinputtext keterangan' style='width:145px;'> </td>";
				$tab .= "</tr>";
			}

			/* TOTAL */

			$tab .= "<tr class='rowcontent total' data-subbagian='" . $codediv . "' style='background-color: #50edd2'>";
			$tab .= "<td align='center'><b>JUMLAH</b></td>";
			$tab .= "<td></td>";
			$tab .= "<td align='center'><b>" . intval($tenagaKerja[$codediv]['total']) . "</b></td>";
			$tab .= "<td align='center'></td>";
			foreach ($head as $idcomp => $val) {
				$tab .= "<td align='right'>" . number_format($dataUpah[$codediv][$idcomp]['totalidkomponen'], 2) . "</td>";
			}
			$tab .= "<td style='display:none'></td>";
			$tab .= "<td style='display:none'></td>";
			$tab .= "<td align='right' class='totalpengurang' style='display:none'></td>";
			$tab .= "<td align='center' class='totalnilaibi'>" . number_format($dataUpah[$codediv]['grandtotalbi'], 2) . "</td>";
			$tab .= "<td></td>";
			$tab .= "</tr>";

			$grandTotalBi += $dataUpah[$codediv]['grandtotalbi'];
		}
		$tab .= "<tr class='rowcontent' style='background-color: #50edd2'>";
		$tab .= "<td align='center'></td>";
		$tab .= "<td align='center'></td>";
		$tab .= "<td align='center'><b></b></td>";
		$tab .= "<td align='center'></td>";
		foreach ($head as $idcomp => $val) {
			$tab .= "<td align='right'></td>";
		}
		$tab .= "<td style='display:none'></td>";
		$tab .= "<td style='display:none'></td>";
		$tab .= "<td align='right' style='display:none'></td>";
		$tab .= "<td align='center'><b>GRAND JUMLAH</b></td>";
		$tab .= "<td align='center'>" . number_format($grandTotalBi, 2) . "</td>";
		$tab .= "<td></td>";
		$tab .= "</tr>";
		$tab .= "</tbody></table>";

		if ($tipex == "excel") {
			$nop = "PDO Upah.xls";
			$xls = new HtmlExcel();
			$borderStyle = 'border: 1px solid #000;';
			$css = "table { border-collapse: collapse; } th, td { $borderStyle padding: 5px; }";
			$xls->setCss($css);
			$xls->addSheet('PDO Upah', $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		} else {
			$tab .= "<button class=mybutton onclick=saveupah()>" . $_SESSION['lang']['save'] . "</button>";
			echo $tab;
		}

		break;

	case 'detailupahv2':
		/* HEADER */
		$head = array();

		# Dapetin komponen BPJS buat di exclude
		$arrKomponenBPJS = fetchData(selectQuery($dbname, "sdm_ho_component", "id", "name LIKE '%BPJS%'"));
		foreach ($arrKomponenBPJS as $row) {
			$notExclude = [44, 3, 61, 67, 81];
			if (!in_array($row['id'], $notExclude)) {
				$arrBpjs[$row['id']] = $row['id'];
			}
		}
		$listBpjs = join("','", $arrBpjs);

		/* ID Componen */
		$dtkomplus = [];
		$dtkommin = [];
		$sCom = "SELECT a.idkomponen,b.name, b.plus FROM " . $dbname . ".sdm_gaji a LEFT JOIN " . $dbname . ".sdm_ho_component b ON a.idkomponen=b.id
                WHERE a.kodeorg = '{$unit}' AND idkomponen NOT IN ('{$listBpjs}') GROUP BY a.idkomponen";
		$qCom = fetchData($sCom);
		foreach ($qCom as $val) {
			if ($val['plus'] == 1) {
				$dtkomplus[$val['idkomponen']] = $val['name'];
			} else {
				$dtkommin[$val['idkomponen']] = $val['name'];
			}
		}
		// Penambahan komponen diluar gaji
		if (!array_key_exists('32', $dtkomplus)) {
			$dtkomplus['32'] = "Premi";
		}
		$dtkomplus['899'] = "Upah Angkut dan Langsir";
		$dtkomplus['990'] = "Premi Reward Karyawan";
		$dtkomplus['991'] = "Premi Reward Sembako";
		$dtkomplus['992'] = "Premi Angkut dan Langsir";
		ksort($dtkomplus);
		ksort($dtkommin);
		// Merge Array
		$head = $dtkomplus + $dtkommin;

		/* Divisi */
		$div = array();
		$sDiv = selectQuery($dbname, "organisasi", "kodeorganisasi,namaorganisasi,tipe", "(kodeorganisasi = '" . $unit . "' OR induk = '" . $unit . "') AND (tipe = 'AFDELING' OR tipe = 'BIBITAN' OR tipe = 'KEBUN' OR tipe = 'PABRIK' OR tipe = 'TRAKSI' OR tipe = 'WORKSHOP' OR tipe = 'STATION' OR tipe = 'GUDANG')", "kodeorganisasi");
		$qDiv = fetchData($sDiv);
		foreach ($qDiv as $val) {
			if (strlen($val['kodeorganisasi']) == 4) {
				$div[''] = "KANTOR";
			} else {
				$div[$val['kodeorganisasi']] = $val['namaorganisasi'];
			}
		}

		/* NON STAFF, SKU, KHT, BORONGAN */
		$idTipeKar = array(1, 3, 4, 15);
		$tanggalAkhirBulan = getLastDayOfMonth($per);

		/* Tipe Karyawan */
		$sTpKar = selectQuery($dbname, "sdm_5tipekaryawan", "id,tipe", "id IN (" . implode(",", $idTipeKar) . ")");
		$qTpKar = fetchData($sTpKar);

		/* Jumlah Tenaga Kerja */
		$sTk = selectQuery($dbname, "datakaryawan", "subbagian,tipekaryawan,COUNT(*) AS total", "subbagian IN ('" . implode("','", array_keys($div)) . "') AND tipekaryawan IN (" . implode(',', $idTipeKar) . ") GROUP BY subbagian,tipekaryawan");
		$qTk = fetchData($sTk);
		$tenagaKerja = array();
		foreach ($qTk as $val) {
			$tenagaKerja[$val['subbagian']][$val['tipekaryawan']] = $val['total'];
			$tenagaKerja[$val['subbagian']]['total'] += $val['total'];
			$tenagaKerja['grandtotal'] += $val['total'];
		}

		/* Data SDM GAJI */
		# Bisa di refactor tapi nanti lah
		$qGaji = selectQuery($dbname, "sdm_gaji_vw", "hk, SUM(jumlah) as jumlah, subbagian, tipekaryawan, idkomponen", "kodeorg='{$unit}' AND periodegaji='{$per}' AND tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND idkomponen NOT IN('{$listBpjs}') GROUP BY subbagian, tipekaryawan, idkomponen");
		$rGaji = fetchData($qGaji);
		$dataUpah = [];
		$pengaliKomponen = makeOption($dbname, "sdm_ho_component", "id,plus");
		$tipe_org = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		foreach ($rGaji as $val) {
			// Non-staff ambil gaji lemburnya aja
			if ($val['tipekaryawan'] == 1) {
				if ($val['idkomponen'] == 32) { // Non-Staff tambah premi
					$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];
					$dataUpah[$divisi][$val['tipekaryawan']]['hk'] += $val['hk'];

					// If komponen minus/pengurang
					if ($pengaliKomponen[$val['idkomponen']] == 0) {
						$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'] * -1;
						$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'] * -1;

						$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'] * -1;
						$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'] * -1;
						$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'] * -1;
						$dataUpah['grandtotal'] += $val['jumlah'] * -1;
					} else {
						// Default komponen plus
						$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'];
						$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'];

						$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
						$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
						$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];
						$dataUpah['grandtotal'] += $val['jumlah'];
					}

					$dataUpah[$divisi]['totalhk'] += $val['hk'];
				}
				if ($val['idkomponen'] == 33) { // Non-Staff tambah lembur
					$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];
					$dataUpah[$divisi][$val['tipekaryawan']]['hk'] += $val['hk'];

					// If komponen minus/pengurang
					if ($pengaliKomponen[$val['idkomponen']] == 0) {
						$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'] * -1;
						$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'] * -1;

						$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'] * -1;
						$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'] * -1;
						$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'] * -1;
						$dataUpah['grandtotal'] += $val['jumlah'] * -1;
					} else {
						// Default komponen plus
						$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'];
						$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'];

						$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
						$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
						$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];
						$dataUpah['grandtotal'] += $val['jumlah'];
					}

					$dataUpah[$divisi]['totalhk'] += $val['hk'];
				}
			} else {
				$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];
				$dataUpah[$divisi][$val['tipekaryawan']]['hk'] += $val['hk'];

				// If komponen minus/pengurang
				if ($pengaliKomponen[$val['idkomponen']] == 0) {
					$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'] * -1;
					$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'] * -1;

					$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'] * -1;
					$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'] * -1;
					$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'] * -1;
					$dataUpah['grandtotal'] += $val['jumlah'] * -1;
				} else {
					// Default komponen plus
					$dataUpah[$divisi][$val['tipekaryawan']][$val['idkomponen']] += $val['jumlah'];
					$dataUpah[$divisi]['totalkomponen'][$val['idkomponen']] += $val['jumlah'];

					$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
					$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
					$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];
					$dataUpah['grandtotal'] += $val['jumlah'];
				}

				$dataUpah[$divisi]['totalhk'] += $val['hk'];
			}
		}
		// echo "<pre>";
		// print_r($dataUpah['KPDE01']['15']);
		// exit("Warning");

		# Tambahkan komponen premi dengan premi kemandoran dan premi konsumsi
		$qPremikemandoran = "SELECT SUM(a.premiinput) as jumlah, b.tipekaryawan, b.subbagian, a.divisi, a.karyawanid FROM kebun_premikemandoran a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.kodeorg='{$unit}' AND a.periode='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND a.posting='1' AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY a.karyawanid, b.subbagian, b.tipekaryawan";
		$rPremikemandoran = fetchData($qPremikemandoran);
		foreach ($rPremikemandoran as $val) {
			// Ini akomodir buat karyawan double, jika subbagian asal datakaryawan beda dengan unit maka dia pakai subbagian dari kebun_premikemandoran
			$asalUnit = makeOption($dbname, "datakaryawan_bulanan", "karyawanid,lokasitugas", "karyawanid='{$val['karyawanid']}' AND periode='{$per}'")[$val['karyawanid']];
			if (substr($val['divisi'], 0, 4) != $asalUnit) {
				// $xxx .= substr($val['divisi'], 0, 4) . " - " . $asalUnit . " - " . $unit . " - " . $val['karyawanid'] . "<br/>";
				$divisi = ($val['divisi'] == "") ? "KANTOR" : $val['divisi'];
			} else {
				$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];
			}

			$dataUpah[$divisi][$val['tipekaryawan']]['32'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['32'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}

		$qPremikonsumsi = "SELECT SUM(a.nilaikonsumsi) as jumlah, b.tipekaryawan, b.subbagian FROM kebun_premikonsumsi a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.unit='{$unit}' AND b.lokasitugas='{$unit}' AND left(a.tanggal,7)='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND a.fisikkonsumsi='0' AND a.posting='1' AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY b.subbagian, b.tipekaryawan";
		$rPremikonsumsi = fetchData($qPremikonsumsi);
		foreach ($rPremikonsumsi as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['32'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['32'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}

		# Dapatkan upah & premi diluar gaji
		$qAngkutMuatLangsir = "SELECT SUM(a.nilairupiah) as jumlah, b.tipekaryawan, b.subbagian FROM kebun_spbbmlangsir_vw a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.unit='{$unit}' AND b.lokasitugas='{$unit}' AND a.periode='{$per}' AND b.periode='{$per}' AND a.posting='1' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' AND a.isKontan='0' GROUP BY b.subbagian, b.tipekaryawan";
		$rAngkutMuatLangsir = fetchData($qAngkutMuatLangsir);
		foreach ($rAngkutMuatLangsir as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['899'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['899'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}
		### Premi Reward Produksi ###
		$qPremirewardproduksi = "SELECT SUM(a.totalbonus) as jumlah, b.tipekaryawan, b.subbagian FROM kebun_3prestasipemanen a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.kodeorg='{$unit}' AND b.lokasitugas='{$unit}' AND a.periode='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND a.posting='1' AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY b.subbagian, b.tipekaryawan";
		$rPremirewardproduksi = fetchData($qPremirewardproduksi);
		foreach ($rPremirewardproduksi as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['990'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['990'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}
		### Premi Reward Sembako ###
		$qPremirewardsembako = "SELECT SUM(a.rprewardbonus) as jumlah, b.tipekaryawan, b.subbagian FROM kebun_3rewardpemanen a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.kodeorg='{$unit}' AND b.lokasitugas='{$unit}' AND a.periode='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND a.posting='1' AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY b.subbagian, b.tipekaryawan";
		$rPremirewardsembako = fetchData($qPremirewardsembako);
		foreach ($rPremirewardsembako as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['991'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['991'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}
		### Premi BM & Langsir ###
		$qPremibmlangsir = "SELECT SUM(a.totalbonus) as jumlah, b.tipekaryawan, b.subbagian FROM kebun_3prestasimuatlangsir a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.kodeorg='{$unit}' AND b.lokasitugas='{$unit}' AND a.periode='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND a.posting='1' AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY b.subbagian, b.tipekaryawan";
		$rPremibmlangsir = fetchData($qPremibmlangsir);
		foreach ($rPremibmlangsir as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['992'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['992'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}
		### Premi PMK ###
		$qPremipmk = "SELECT SUM(a.premipmk) as jumlah, b.tipekaryawan, b.subbagian FROM sdm_absensidt a LEFT JOIN datakaryawan_bulanan b ON a.karyawanid=b.karyawanid WHERE a.kodeorg='{$unit}' AND b.lokasitugas='{$unit}' AND left(a.tanggal,7)='{$per}' AND b.periode='{$per}' AND b.tipekaryawan IN (" . implode(',', $idTipeKar) . ") AND (b.tanggalkeluar = '0000-00-00') AND b.tanggalmasuk <= '{$tanggalAkhirBulan}' GROUP BY b.subbagian, b.tipekaryawan";
		$rPremipmk = fetchData($qPremipmk);
		foreach ($rPremipmk as $val) {
			$divisi = ($val['subbagian'] == "") ? "KANTOR" : $val['subbagian'];

			$dataUpah[$divisi][$val['tipekaryawan']]['32'] += $val['jumlah'];
			$dataUpah[$divisi]['totalkomponen']['32'] += $val['jumlah'];

			$dataUpah[$divisi][$val['tipekaryawan']]['totalbi'] += $val['jumlah'];
			$dataUpah[$divisi][$val['idkomponen']]['totalidkomponen'] += $val['jumlah'];
			$dataUpah[$divisi]['grandtotalbi'] += $val['jumlah'];

			$dataUpah['grandtotal'] += $val['jumlah'];
		}

		$tab = "<fieldset>
                    <legend><b>Detail Input</b></legend>
                    <table cellpadding=7 cellspacing=1 border=1 class='sortable table-scroll' style='margin-bottom: 20px;width:100%'>
                        <thead>
                            <tr class='rowheader'>";
		$tab .= "<td align='center'>" . $_SESSION['lang']['nourut'] . "</td>";
		$tab .= "<td align='center'>Bagian/Sub Bagian</td>";
		$tab .= "<td align='center'>" . $_SESSION['lang']['tipekaryawan'] . "</td>";
		$tab .= "<td align='center'>Jumlah<br>Tenaga Kerja</td>";
		$tab .= "<td align='center'>Jumlah<br>Tenaga HK</td>";
		foreach ($head as $val) {
			$tab .= "<td align='center'>" . $val . "</td>";
		}
		$tab .= "<td align='center'>Nilai PDO BI</td>";
		$tab .= "</tr>
        </thead>
        <tbody>";

		$noo = 0;
		foreach ($div as $codediv => $namediv) {
			$codediv = ($codediv == "") ? "KANTOR" : $codediv;
			$noo++;
			foreach ($qTpKar as $key => $tipe) {
				$tab .= "<tr class='rowcontent input' data-subbagian='" . $codediv . "' data-tipekaryawan='" . $tipe['id'] . "' data-jlhtk='" . intval($tenagaKerja[$codediv][$tipe['id']]) . "' data-jlhhk='" . number_format($dataUpah[$codediv][$tipe['id']]['hk'], 2) . "'>";
				if ($key == 0) {
					$tab .= "<td valign='top' align='center' rowspan='" . (count($qTpKar) + 1) . "'>" . $noo . "</td>";
					if (strlen($codediv) == 4) {
						$tab .= "<td valign='top' align='center' rowspan='" . (count($qTpKar)) . "'>KANTOR</td>";
					} else {
						$tab .= "<td valign='top' align='center' rowspan='" . (count($qTpKar)) . "'>" . getNamaOrg($codediv) . "</td>";
					}
				}
				$tab .= "<td>" . $tipe['tipe'] . "</td>";
				$tab .= "<td align='center'>" . intval($tenagaKerja[$codediv][$tipe['id']]) . "</td>";
				$tab .= "<td align='center'>" . number_format($dataUpah[$codediv][$tipe['id']]['hk'], 2) . "</td>";
				foreach ($head as $idcomp => $val) {
					$tab .= "<td align='center'>" . number_format($dataUpah[$codediv][$tipe['id']][$idcomp], 2) . "</td>";
				}
				$tab .= "<td align='center' class='nilaibi' data-nilaibi='{$dataUpah[$codediv][$tipe['id']]['totalbi']}'>" . number_format($dataUpah[$codediv][$tipe['id']]['totalbi'], 2) . "</td>";
				$tab .= "</tr>";
			}

			/* TOTAL */

			$tab .= "<tr class='rowcontent total' data-subbagian='" . $codediv . "' style='background-color: #50edd2'>";
			$tab .= "<td align='center'><b>JUMLAH</b></td>";
			$tab .= "<td></td>";
			$tab .= "<td align='center'><b>" . intval($tenagaKerja[$codediv]['total']) . "</b></td>";
			$tab .= "<td align='center'></td>";
			foreach ($head as $idcomp => $val) {
				$tab .= "<td align='right'>" . number_format($dataUpah[$codediv][$idcomp]['totalidkomponen'], 2) . "</td>";
			}
			$tab .= "<td align='center' class='totalnilaibi'>" . number_format($dataUpah[$codediv]['grandtotalbi'], 2) . "</td>";
			$tab .= "</tr>";

			$grandTotalBi += $dataUpah[$codediv]['grandtotalbi'];
		}
		$tab .= "<tr class='rowcontent' style='background-color: #50edd2'>";
		$tab .= "<td align='center'></td>";
		$tab .= "<td align='center'></td>";
		$tab .= "<td align='center'><b></b></td>";
		$tab .= "<td align='center'></td>";
		foreach ($head as $idcomp => $val) {
			$tab .= "<td align='right'></td>";
		}
		$tab .= "<td align='center'><b>GRAND JUMLAH</b></td>";
		$tab .= "<td align='center'>" . number_format($grandTotalBi, 2) . "</td>";
		$tab .= "</tr>";
		$tab .= "</tbody></table>";

		$nop = "PDO Upah.xls";
		$xls = new HtmlExcel();
		$borderStyle = 'border: 1px solid #000;';
		$css = "table { border-collapse: collapse; } th, td { $borderStyle padding: 5px; }";
		$xls->setCss($css);
		$xls->addSheet('PDO Upah', $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
		break;

	case 'saveupah':
		/* Notransaksi NoPDO Baru */
		if ($nopdo == '') {
			$nopdo = generateKodeTransaksi($unit, $per, "PDO");
		}

		if ($noupah == '') {
			$noupah = generateKodeTransaksi($unit, $per, "UPAH");
		}

		try {
			$owlPDO->beginTransaction();

			// Cek jika belum ada HTnya
			$cekHT = getCountRows($dbname, "keu_pdoht", "nopdo='{$nopdo}' AND notransaksi='{$noupah}' AND kodeorg='{$unit}' AND periode='{$per}' AND sesi='{$sesi}'");
			if ($cekHT == 0) {
				/* Data HT*/
				$dataHT = array(
					'nopdo' => $nopdo,
					'notransaksi' => $noupah,
					'kodeorg' => $unit,
					'periode' => $per,
					'sesi' => $sesi,
					'updateby' => $_SESSION['standard']['userid'],
					'tipepdo' => "UPAH",
				);
				$colsHT = array_keys($dataHT);
				$sInsHT = insertQuery($dbname, 'keu_pdoht', $dataHT, $colsHT);
				$owlPDO->exec($sInsHT);
			}

			$data = explode('$$', $data);
			$counter = 0;

			// Delete DT First
			$deleteDT = deleteQuery($dbname, "keu_pdodt", "nopdo='{$nopdo}' AND notransaksi='{$noupah}'");
			$owlPDO->exec($deleteDT);

			foreach ($data as $val) {
				[$subbagian, $tipekaryawan, $nilaiBI, $keterangan, $nilairealisasi, $estimasi, $unit, $jlhHk, $jlhTk, $arrKomponenGaji] = explode('##', $val);

				$arrKomponenGaji = json_decode($arrKomponenGaji, true);

				$subbagian = ($subbagian == "KANTOR") ? $unit : $subbagian;
				if (strlen($subbagian) == 4) {
					$idpdo = "PUL";
				} else {
					$tipeSubbagian = makeOption($dbname, "organisasi", "kodeorganisasi,tipe", "kodeorganisasi='{$subbagian}'")[$subbagian];
					$idpdo = "TANAMAN";
					if ($tipeSubbagian == "TRAKSI" || $tipeSubbagian == "WORKSHOP") {
						$idpdo = "TEKNIK";
					}
				}

				// Looping per Komponen Gaji untuk di insert nilai biayanya
				foreach ($arrKomponenGaji as $idComp => $val) {
					// Kelompok Komponen Gaji
					$kelompokKomponenGaji = makeOption($dbname, "keu_5klgajidt", "idkomponen,idkelompok", "idkomponen='{$idComp}'");

					/* Data DT */
					$dataDT = array(
						'nopdo' => $nopdo,
						'notransaksi' => $noupah,
						'nourut' => $counter++, /* Dikarenakan informasi sebelumnya, belum jelas, makanya di patok = 0 */
						'idpdo' => $idpdo,
						'divisi' => $subbagian,
						'rincian' => $keterangan,
						'rupiahdiajukan' => $val,
						'rupiahreal' => $val,
						'tipekaryawan' => $tipekaryawan,
						'jumlahorang' => $jlhTk,
						'fisik' => $jlhHk,
						'komponengaji' => $idComp,
						'kelkomponengaji' => $kelompokKomponenGaji[$idComp],
					);
					$colsDT = array_keys($dataDT);
					$sInsDT = insertQuery($dbname, 'keu_pdodt', $dataDT, $colsDT);
					$owlPDO->exec($sInsDT);
				}
			}
			//   exit("Warning: ".$sInsHT." \n ".$sInsDT);
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			exit("Warning: {$idComp} \n" . addslashes($e->getMessage()));
		}

		echo $nopdo . '##' . $noupah;
		break;

	case 'listupah':
		$unitPDO = makeOption($dbname, "keu_pdoht", "nopdo,kodeorg", "nopdo='{$nopdo}'");

		/* ID Componen */
		$sCom = "SELECT a.idkomponen,b.name, b.plus FROM " . $dbname . ".sdm_gaji a LEFT JOIN " . $dbname . ".sdm_ho_component b ON a.idkomponen=b.id
                WHERE a.kodeorg = '{$unit}' GROUP BY a.idkomponen";
		$qCom = fetchData($sCom);
		foreach ($qCom as $val) {
			if ($val['plus'] == 1) {
				$dtkomplus[$val['idkomponen']] = $val['name'];
			} else {
				$dtkommin[$val['idkomponen']] = $val['name'];
			}
		}
		ksort($dtkomplus);
		ksort($dtkommin);
		// Merge Array
		$head = $dtkomplus + $dtkommin;

		$tab = "<fieldset>
				<legend><b>List Data " . $_SESSION['lang']['upah'] . "</b></legend>
				<table cellpadding=7 cellspacing=1 border=0 class=sortable style=width:80%>
					<thead>
					<tr>
						<td align=center>" . $_SESSION['lang']['divisi'] . "</td>
						<td align=center>" . $_SESSION['lang']['tipekaryawan'] . "</td>";

		foreach ($head as $val) {
			$tab .= "
						<td align='center'>" . $val . "</td>";
		}

		$tab .= "
						<td align=center>Nilai BI</td>
					</tr>
					</thead>
					<tbody>";

		$sDt = selectQuery($dbname, "keu_pdodt", "divisi, tipekaryawan, rupiahdiajukan, komponengaji", "nopdo = '" . $nopdo . "' AND notransaksi = '" . $noupah . "'");
		$qDt = fetchData($sDt);
		foreach ($qDt as $bar) {
			$dataUpah[$bar['divisi']][$bar['tipekaryawan']][$bar['komponengaji']]['rupiah'] = $bar['rupiahdiajukan'];

			$dataUpah[$bar['divisi']][$bar['tipekaryawan']]['totalrupiah'] += $bar['rupiahdiajukan'];
		}

		foreach ($dataUpah as $div => $bar) {
			foreach ($bar as $tipeKary => $barx) {
				$divisi = strlen($div) == 4 ? "KANTOR" : getNamaOrg($div);

				$tab .= "<tr class='rowcontent'>";
				$tab .= "<td align='center'>" . $divisi . "</td>";
				$tab .= "<td align='center'>" . getNamaTipeKary($tipeKary) . "</td>";
				foreach ($head as $idcomp => $val) {
					$tab .= "<td align='right'>" . number_format($dataUpah[$div][$tipeKary][$idcomp]['rupiah'], 2) . "</td>";
				}
				$tab .= "<td align='right'>" . number_format($barx['totalrupiah'], 2) . "</td>";
				$tab .= "</tr>";
			}
		}
		$tab .= "</tbody>";

		echo $tab;
		break;

	case 'simpankas':

		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $nokas . "' and nourut='" . $idpdo . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		if ($akhir == 'iya') {
			$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`,`rupiah`)
							VALUES ('" . $nopdo . "', '" . $nokas . "', '" . $idpdo . "','" . $byrlain . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$strcek = "select * from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "'";
			$rescek = fetchData($strcek);
			if (count($rescek) == 0) {
				$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `kodeorg`, `periode`, `sesi`, `updateby`)
				VALUES ('" . $nopdo . "', '" . $unit . "', '" . $per . "','" . $sesi . "','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			//Umar
			if ($check == 'true') {
				$estimasi = 0 - $estimasi;
			}
			//End Umar
			// $kodkeg='';
			// if(strlen($noakunkas)>7){
			//     $akuntemp=$noakunkas;
			//     $kodkeg= $akuntemp;
			//     $noakunkas=substr($akuntemp,0,7);
			// }

			// echo "<pre>";
			// print_r($kodekeg);
			// exit ("<label hidden> WARNING </label>");
			// echo "</pre>";
			$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `kegiatan`,`rupiahreal`,`rupiah`,`rupiahdiajukan`,`nodocument`)
			VALUES ('" . $nopdo . "', '" . $nokas . "', '" . $idpdo . "', '" . $noakunkas . "','" . $kodekeg . "','" . $shi . "','" . $estimasi . "','" . $sdbi . "','" . $novoucher . "')";
			// exit ("WARNING    :".$str);
			try {
				if ($estimasi != 0 || $sdbi != 0) {
					$owlPDO->exec($str);
				}
			} catch (PDOException $e) {
				print " Gagal !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;

	case 'nokas':
		$nokas = generateKodeTransaksi($unit, $per, "KAS");
		echo $nokas;
		break;
	case 'detailkas':
		$opttipeunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		$periodex = date("m-Y", strtotime("0 Month", strtotime($per)));
		$periodexz = date("Y-m", strtotime("0 Month", strtotime($per)));
		$periodexzz = date("Ym", strtotime("0 Month", strtotime($per)));

		$hidden = '';
		$colspan = 4;
		if ($tporg[$unit] == 'PABRIK' || $tporg[$unit] == 'KANWIL') {
			$hidden = "hidden";
			$colspan = 4;
		}

		$stream = "";
		$stream .= "<fieldset><legend><b>Detail Input</b></legend>";
		// Start Table; Table Head
		$stream .= "<br/><b>Kasbank</b>";
		$stream .= "
                <table cellpading=1 cellspacing=1 border=0 class=sortable style='width:100%'>
                    <thead>
                        <tr>
                            <td align=center rowspan=2 style='width:3%'>" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center style='width:8%'>Tipe</td>
							<td align=center style='width:8%'>Akun Biaya</td>
							<td align=center style='width:21%'>" . $_SESSION['lang']['uraian'] . "</td>
							<td align=center hidden>" . $_SESSION['lang']['kodekegiatan'] . "</td>";
		$stream .= "        <td align=center style='width:12%'>No. Voucher</td>";
		$stream .= "        <td align=center style='width:12%'>No. Dokumen</td>";
		$stream .= "        <td align=center style='width:15%'>Nilai Rupiah Real</td>";
		$stream .= "        <td align=center>Pengurang</td> ";
		$stream .= "	    <td align=center style='width:10%'>(+/-)</td>
							<td align=center style='width:10%'>Nilai Rupiah BI</td>
							<td align='center'>" . $_SESSION['lang']['action'] . " <br/> <input type='checkbox' id='checkAll' onclick=\"toggleCheck(this, 'checkPemby')\" /></td>
                        </tr>
                    </thead>";

		// END Thead

		$where = " AND left(tanggal,7) like '" . $per . "%'";

		// Options
		// COA Biaya
		$qCoa = selectQuery($dbname, "keu_5akun", "noakun, namaakun", "level='5' AND aktif='1' AND kasbankdetail='1'");
		$rCoa = fetchData($qCoa);
		$optAkunBy = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($rCoa as $bar) {
			$optAkunBy .= "<option value='{$bar['noakun']}'>{$bar['noakun']} - {$bar['namaakun']}</option>";
		}

		$arrTipeKasbank = ["KAS", "BANK"];
		$optTipeKasbank = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($arrTipeKasbank as $val) {
			$optTipeKasbank .= "<option value='{$val}'>{$val}</option>";
		}

		//Nilai Kas Bank
		$arrnilai = array();
		$str = "select notransaksi,noakun,noakun2a,jumlah,kodekegiatan, keterangan2,novoucher,nodok from " . $dbname . ".keu_kasbankdtht_vw where posting='1' and kodeorg='" . $unit . "' and noakun2a in (SELECT noakun FROM " . $dbname . ".keu_5akun WHERE namaakun LIKE 'KAS%' and aktif='1')  " . $where;
		$resx = fetchdata($str);
		if (!empty($resx)) {
			foreach ($resx as $val) {
				@$noxz++;

				if (isset($val['kodekegiatan']) || $val['kodekegiatan'] != '') {
					$nmkeg = $arrnmkeg[$val['kodekegiatan']];
				} else {
					$val['kodekegiatan'] = '';
					$nmkeg = '';
				}

				if (!isset($val['novoucher']) || $val['novoucher'] == '') {
					$val['novoucher'] = '';
				}

				$stream .= "<tr class='rowcontent input'>";
				$stream .= "<td align=center>" . $noxz . "</td>";
				$stream .= "<td align=center hidden><input type='hidden' id=ismanual_" . $noxz . " class='ismanual' value='0'/></td>";
				$stream .= "<td align=center>KAS <input type='hidden' id=tipekasbank_" . $noxz . " class='tipekasbank' value='KAS'/></td>";
				$stream .= "<td align=left>" . $val['noakun'] . " - " . $arrnmakun[$val['noakun']] . " <input type='hidden'  id=noakun_" . $noxz . " class='noakun' value='" . $val['noakun'] . "'/></td>";
				$stream .= "<td align='left'>" . $val['keterangan2'] . " <input type='hidden' class='keterangan' value='" . $val['keterangan2'] . "'/></td>";
				$stream .= "<td align=left id=kodekeg_" . $noxz . " hidden>" . $val['kodekegiatan'] . "</td>";
				$stream .= "<td align=center id=novoucher_" . $noxz . " class='novoucher'>" . $val['novoucher'] . "</td>";
				$stream .= "<td align=center id=nodocument_" . $noxz . " class='nodocument'>" . $val['nodok'] . "</td>";
				// $arrnilai[$val['noakun']][$val['novoucher']]=$val['jumlah'];
				$stream .= "<td align=right id=jlhkas_" . $noxz . " class='rpreal'>" . number_format($val['jumlah'], 2) . "</td>";
				$stream .= "<td align=center><input type='checkbox' class='checkPengurang' onchange=\"calcPlusMinus(this, 'sdbikas')\"></td>";
				$stream .= "<td align=right><input type=text id='plusminuskas_{$noxz}' value='" . number_format(0) . "' onkeyup=\"calcPlusMinus(this, 'sdbikas');z.numberFormat('plusminuskas_{$noxz}', 2);\" onkeypress=\"return angka_doang(event)\" class='myinputtextnumber plusminus' /></td>";
				$stream .= "<td align=right><input type=text id='" . $noxz . "_sdbikas' value='" . number_format($val['jumlah'], 2) . "' disabled onkeypress='return angka_doang(event)' class='myinputtextnumber sdbikas'  ></td>";
				$stream .= "<td align='center'><input type='checkbox' class='checkPemby' title='Centang untuk menambahkan Kasbank ke Biaya PDO' onchange=\"checkPemby()\"/></td>";
				$stream .= "<td style='display:none'><input type='hidden' id='totalKas' value='0'/></td>";
				$stream .= "</tr>";

				$totalBI += $val['jumlah'];
			}

			# Tambahin transaksi kasbank pdo manual yang tersimpan di keu_pdodt
			$qKasbankManual = selectQuery($dbname, "keu_pdodt", "*", "nopdo='{$nopdo}' AND notransaksi='{$nokas}' AND ismanual='1'");
			$rKasbankManual = fetchData($qKasbankManual);
			foreach ($rKasbankManual as $val) {
				@$noxz++;

				if (!isset($val['nodocument']) || $val['nodocument'] == '') {
					$val['nodocument'] = '';
				}

				$stream .= "<tr class='rowcontent input'>";
				$stream .= "<td align=center>" . $noxz . "</td>";
				$stream .= "<td align=center hidden><input type='hidden' id=ismanual_" . $noxz . " class='ismanual' value='1'/></td>";
				$stream .= "<td align=center>" . $val['tipekasbank'] . " <input type='hidden' id=tipekasbank_" . $noxz . " class='tipekasbank' value='" . $val['tipekasbank'] . "'/></td>";
				$stream .= "<td align=left>" . $val['noakun'] . " - " . $arrnmakun[$val['noakun']] . " <input type='hidden'  id=noakun_" . $noxz . " class='noakun' value='" . $val['noakun'] . "'/></td>";
				$stream .= "<td align='left'>" . $val['rincian'] . " <input type='hidden' class='keterangan' value='" . $val['rincian'] . "'/></td>";
				$stream .= "<td align=left id=kodekeg_" . $noxz . " hidden></td>";
				$stream .= "<td align=center id=novoucher_" . $noxz . " class='novoucher'>" . $val['nodocument'] . "</td>";
				$stream .= "<td align=center id=nodocument_" . $noxz . " class='nodocument'>" . $val['nodok'] . "</td>";
				$stream .= "<td align=right id=jlhkas_" . $noxz . " class='rpreal'>" . number_format($val['rupiahdiajukan'], 2) . "</td>";
				$stream .= "<td align=center><input type='checkbox' class='checkPengurang' onchange=\"calcPlusMinus(this, 'sdbikas')\"></td>";
				$stream .= "<td align=right><input type=text id='plusminuskas_{$noxz}' value='" . number_format(0) . "' onkeyup=\"calcPlusMinus(this, 'sdbikas');z.numberFormat('plusminuskas_{$noxz}', 2);\" onkeypress=\"return angka_doang(event)\" class='myinputtextnumber plusminus' /></td>";
				$stream .= "<td align=right><input type=text id='" . $noxz . "_sdbikas' value='" . number_format($val['rupiahdiajukan'], 2) . "' disabled onkeypress='return angka_doang(event)' class='myinputtextnumber sdbikas'  ></td>";
				$stream .= "<td align='center'><input type='checkbox' class='checkPemby' title='Centang untuk menambahkan Kasbank ke Biaya PDO' onchange=\"checkPemby()\"/></td>";
				$stream .= "<td style='display:none'><input type='hidden' id='totalKas' value='0'/></td>";
				$stream .= "</tr>";

				$totalBI += $val['rupiahdiajukan'];
			}

			$stream .= "<tr class='rowcontent manual-rows input'>";
			$stream .= "<td align='center'><input type='hidden' style='display:none' id=ismanual_" . $noxz . " class='ismanual' value='1'/></td>";
			$stream .= "<td align='center'><select class='select2 tipekasbank' style='width:120px'>{$optTipeKasbank}</select></td>";
			$stream .= "<td align='center' class='selectParent'><select class='select2 noakun' style='width:220px'>{$optAkunBy}</select></td>";
			$stream .= "<td align='center'><input type='text' class='keterangan myinputtext' style='width:220px'/></td>";
			$stream .= "<td class='novoucher'></td>";
			$stream .= "<td class='nodocument'></td>";
			$stream .= "<td class='rpreal' align='right'>0</td>";
			$stream .= "<td></td>";
			$stream .= "<td align='right'><input type='hidden' class='plusminus' value='0'/></td>";
			$stream .= "<td align=right><input type=text id='" . $noxz . "_sdbikas' value='0' onkeypress='return angka_doang(event)' class='myinputtextnumber sdbikas' oninput=\"calcBIKas()\" onkeyup=\"autoChecked(this)\"/></td>";
			$stream .= "
				<td>
					<div style='display:flex; gap:5px; justify-content:center;'>
						<img class='zImgBtn' src='images/plus.png' alt='btn-add' onclick=\"addRows(this)\" title='Tambah Baris' style='width:15px;height:15px;' />
						<img class='zImgBtn' src='images/delete_32.png' alt='btn-delete' onclick=\"deleteRows(this)\" title='Hapus Baris' style='width:15px;height:15px;' />

						<input type='checkbox' class='checkPemby' title='Centang untuk menambahkan Kasbank ke Biaya PDO' onchange=\"checkPemby()\" disabled style='display:none'/>
					</div>
				</td>";
			$stream .= "</tr>";

			// Grand Total
			$stream .= "<tr class=rowcontent style='background-color: #50edd2'>";
			$stream .= "<td colspan='6' style='font-transform:uppercase; font-weight:bold; font-size:1rem; color:#333' align='right'>Grand Total</td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td align='right' class='totalpengurang'>0</td>";
			$stream .= "<td align='right' class='totalnilaibi'>" . number_format($totalBI, 2) . "</td>";
			$stream .= "<td></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center colspan=11>
				<button class=mybutton onclick=savekas()>" . $_SESSION['lang']['save'] . "</button></td>";
			$stream .= "</tr>";
			$stream .= "</tbody></table>";
		}
		// END KAS BANK

		echo $stream;
		break;
	case 'listkas':

		$hidden = '';
		$tporg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		$colspan = 5;
		// style='float:left;'

		$border = 0;
		if ($tipex == "excel") {
			$border = 1;
		}

		$stream .= "<fieldset><legend><b>List Data " . $_SESSION['lang']['pengeluarantunai'] . "</b></legend>
                <table cellpading=1 cellspacing=1 border={$border} class=sortable>
                    <thead>
						<tr>
							<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center>Tipe</td>
							<td align=center>Akun Biaya</td>
							<td align=center>" . $_SESSION['lang']['uraian'] . "</td>
							<td align=center >No. Voucher</td>
							<td align=center >No. Dokumen</td>
							<td align=center >Nilai Rupiah Real</td>
							<td align=center>(+/-)</td>
							<td align=center>Nilai Rupiah BI</td>";
		if ($tipex != "excel") {
			$stream .= "<td align=center>" . $_SESSION['lang']['action'] . "</td>";
		}
		$stream .= "</tr></thead>";

		$akuntemp = $kdkegtemp = '';

		##ambil data yang udah ada
		$str = "select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi = '{$nokas}'";
		$hsl = fetchdata($str);
		if (count($hsl) > 0) {
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($bar['noakun'] == '8110101' || $bar['noakun'] == '8110102') {
					$namaakun = $arrnmakun[$bar['noakun']] . " DARAT";
				} else if ($bar['noakun'] == '8110201' || $bar['noakun'] == '8110202') {
					$namaakun = $arrnmakun[$bar['noakun']] . " LAUT";
				} else {
					$namaakun = $arrnmakun[$bar['noakun']];
				}

				@$no++;
				$stream .= "<tr class='rowcontent'>";
				$stream .= "<td align='center' >" . $no . "</td>";
				$stream .= "<td align='center' >" . $bar['tipekasbank'] . "</td>";
				$stream .= "<td align='left' >" . $bar['noakun'] . " - " . $arrnmakun[$bar['noakun']] . "</td>";
				$stream .= "<td align='left' >" . $bar['rincian'] . "</td>";
				$stream .= "<td align='left'>" . $bar['nodocument'] . "</td>";
				$stream .= "<td align='left'>" . $bar['nodok'] . "</td>";

				$stream .= "<td align='right'>" . number_format($bar['rupiahreal'], 2) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['rupiah'], 2) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";
				if ($tipex != "excel") {
					$stream .= "<td align='center'><img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deletekas('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\"></td></tr>";
				}
				$next = $no;

				@$ttlakunesti[$bar['noakun']] += $bar['rupiah'];
				@$ttlakunbi[$bar['noakun']] += $bar['rupiahdiajukan'];
				@$tlesti += $bar['rupiah'];
				@$tlbi += $bar['rupiahdiajukan'];

				$akuntemp = $bar['noakun'];
			}

			$stream .= "<tr class=rowcontent>
						<td colspan=" . $colspan . " align=center style=font-weight:bold>" . $_SESSION['lang']['total'] . "</td>
						<td></td>
						<td></td>
						<td align=right>" . number_format($tlesti, 2) . "</td>
						<td align=right>" . number_format($tlbi, 2) . "</td>";
			if ($tipex != "excel") {
				$stream .= "<td></td>";
			}
			$stream .= "</tr>";
		} else {
			$stream .= "<tr class=rowcontent><td colspan=8 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}

		$stream .= "</table></fieldset>";

		if ($tipex != "excel") {
			echo $stream;
		} else {
			$nop = "PDO Kas.xls";
			$xls = new HtmlExcel();
			$borderStyle = 'border: 1px solid #000;';
			$css = "table { border-collapse: collapse; } th, td { $borderStyle padding: 5px; }";
			$xls->setCss($css);
			$xls->addSheet('PDO Kas', $stream);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
		break;

	case 'nohutangkas':
		$nohutangkas = generateKodeTransaksi($unit, $per, "HTGK");
		echo $nohutangkas;
		break;
	case 'detailhutangkas':
		$stream = "";
		$stream .= "<fieldset><legend><b>Detail Input</b></legend>";
		// Start Table; Table Head
		$stream .= "<br/><b>Hutang Kas</b>";
		$stream .= "
                <table cellpading=1 cellspacing=1 border=0 class=sortable style='width:100%'>
                    <thead>
                        <tr>
                            <td align=center rowspan=2 style='width:3%'>" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center style='width:8%'>Akun Biaya</td>
							<td align=center style='width:21%'>" . $_SESSION['lang']['uraian'] . "</td>";
		$stream .= "		<td align=center style='width:15%'>Nilai Rupiah Real</td>";
		$stream .= "		<td align=center style='width:10%'>Pengurang</td>
                            <td align=center style='width:10%'>(+/-)</td>
							<td align=center style='width:10%'>Nilai Rupiah BI</td>
							<td align='center'>" . $_SESSION['lang']['action'] . " <br/> <input type='checkbox' id='checkAll' onclick=\"toggleCheck(this, 'checkHutangkas')\" /></td>
                        </tr>
                    </thead>";

		$where = " AND left(tanggal,7) like '" . $per . "%'";
		//Nilai Hutang kas
		$str = "SELECT a.noinvoice, a.tipeinvoice, a.tanggal, b.noakun, b.nilai, b.keterangan FROM keu_tagihanht a LEFT JOIN keu_tagihandt b ON a.noinvoice=b.noinvoice WHERE a.tipeinvoice='ot' AND unit='{$unit}' AND posting='1' {$where}";
		$resx = fetchdata($str);
		if (!empty($resx)) {
			foreach ($resx as $val) {
				@$noxz++;
				$arrnmakun = makeOption($dbname, "keu_5akun", "noakun,namaakun", "noakun='{$val['noakun']}'");

				$stream .= "<tr class='rowcontent input'>";
				$stream .= "<td align=center>" . $noxz . "</td>";
				$stream .= "<td align=left>" . $val['noakun'] . " - " . $arrnmakun[$val['noakun']] . " <input type='hidden'  id=noakun_" . $noxz . " class='noakun' value='" . $val['noakun'] . "'/></td>";
				$stream .= "<td align='left'>" . $val['keterangan'] . " <input type='hidden' class='keterangan' value='" . $val['keterangan'] . "'/></td>";
				$stream .= "<td align=right id=jlhkas_" . $noxz . " class='rpreal'>" . number_format($val['nilai'], 2) . "</td>";
				$stream .= "<td align=center><input type='checkbox' class='checkPengurang' onchange=\"calcPlusMinus(this, 'sdbihutangkas')\"></td>";
				$stream .= "<td align=right><input type=text id='plusminushutangkas_{$noxz}' value='" . number_format(0) . "' onkeyup=\"calcPlusMinus(this, 'sdbihutangkas');z.numberFormat('plusminushutangkas_{$noxz}', 2);\" onkeypress=\"return angka_doang(event)\" class='myinputtextnumber plusminus' /></td>";
				$stream .= "<td align=right><input type=text id='" . $noxz . "_sdbihutangkas' value='" . number_format($val['nilai'], 2) . "' disabled onkeypress='return angka_doang(event)' class='myinputtextnumber sdbihutangkas'  ></td>";
				$stream .= "<td align='center'><input type='checkbox' class='checkHutangkas' title='Centang untuk menambahkan Hutang Kas ke Biaya PDO' onchange=\"checkHutangkas()\"/></td>";
				$stream .= "<td style='display:none'><input type='hidden' id='totalHutangkas' value='0'/></td>";
				$stream .= "</tr>";

				$totalBI += $val['nilai'];
			}

			// Grand Total
			$stream .= "<tr class=rowcontent  style='background-color: #50edd2'>";
			$stream .= "<td colspan='3' style='font-transform:uppercase; font-weight:bold; font-size:1rem; color:#333' align='right'>Grand Total</td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td align='right' class='totalpengurang'>0</td>";
			$stream .= "<td align='right' class='totalnilaibi'>" . number_format($totalBI, 2) . "</td>";
			$stream .= "<td></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center colspan=9>
				<button class=mybutton onclick=savehutangkas()>" . $_SESSION['lang']['save'] . "</button></td>";
			$stream .= "</tr>";
			$stream .= "</tbody></table>";
		}
		// END Hutang KAS

		echo $stream;
		break;
	case 'listhutangkas':

		$border = 0;
		if ($tipex == "excel") {
			$border = 1;
		}

		$hidden = '';
		$colspan = 5;
		$tporg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		$colspan = 3;
		$stream .= "<fieldset><legend><b>List Data Hutang Kas</b></legend>
                <table cellpading=1 cellspacing=1 border={$border} class=sortable>
                    <thead>
						<tr>
							<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center>Akun Biaya</td>
							<td align=center>" . $_SESSION['lang']['uraian'] . "</td>
							<td align=center >Nilai Rupiah Real</td>
							<td align=center>(+/-)</td>
							<td align=center>Nilai Rupiah BI</td>";
		if ($tipex != "excel") {
			$stream .= "<td align=center>" . $_SESSION['lang']['action'] . "</td>";
		}
		$stream .= "</tr></thead>";

		$akuntemp = $kdkegtemp = '';

		##ambil data yang udah ada
		$str = "select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi = '{$nohutangkas}'";
		$hsl = fetchdata($str);
		if (count($hsl) > 0) {
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($bar['noakun'] == '8110101' || $bar['noakun'] == '8110102') {
					$namaakun = $arrnmakun[$bar['noakun']] . " DARAT";
				} else if ($bar['noakun'] == '8110201' || $bar['noakun'] == '8110202') {
					$namaakun = $arrnmakun[$bar['noakun']] . " LAUT";
				} else {
					$namaakun = $arrnmakun[$bar['noakun']];
				}

				@$no++;
				$stream .= "<tr class='rowcontent'>";
				$stream .= "<td align='center' >" . $no . "</td>";
				$stream .= "<td align='left' >" . $bar['noakun'] . " - " . $arrnmakun[$bar['noakun']] . "</td>";
				$stream .= "<td align='left' >" . $bar['rincian'] . "</td>";
				$stream .= "<td align='right'>" . number_format($bar['rupiahreal'], 2) . "</td>";
				$stream .= "<td align='right'>" . number_format($bar['rupiah'], 2) . "</td>";
				$stream .= "<td align='right'>" . number_format($bar['rupiahdiajukan'], 2) . "</td>";

				if ($tipex != "excel") {
					$stream .= "<td align='center'><img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deletehutangkas('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\"></td></tr>";
				}
				$next = $no;

				@$ttlakunesti[$bar['noakun']] += $bar['rupiah'];
				@$ttlakunbi[$bar['noakun']] += $bar['rupiahdiajukan'];
				@$tlesti += $bar['rupiah'];
				@$tlbi += $bar['rupiahdiajukan'];

				$akuntemp = $bar['noakun'];
			}

			$stream .= "<tr class=rowcontent>
						<td colspan=" . $colspan . " align=center style=font-weight:bold>" . $_SESSION['lang']['total'] . "</td>
						<td></td>
						<td align=right>" . number_format($tlesti, 2) . "</td>
						<td align=right>" . number_format($tlbi, 2) . "</td>";
			if ($tipex != "excel") {
				$stream .= "<td></td>";
			}
			$stream .= "</tr>";
		} else {
			$stream .= "<tr class=rowcontent><td colspan=8 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}

		$stream .= "</table></fieldset>";
		if ($tipex != "excel") {
			echo $stream;
		} else {
			$nop = "PDO Hutang Kas.xls";
			$xls = new HtmlExcel();
			$borderStyle = 'border: 1px solid #000;';
			$css = "table { border-collapse: collapse; } th, td { $borderStyle padding: 5px; }";
			$xls->setCss($css);
			$xls->addSheet('PDO Hutang Kas', $stream);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
		break;

	case 'nopjd':
		$nopjd = generateKodeTransaksi($unit, $per, "PJD");
		echo $nopjd;
		break;
	case 'detailpjd':
		$stream = "";
		$stream .= "<fieldset><legend><b>Detail Input</b></legend>";
		// Start Table; Table Head
		$stream .= "<br/><b>Perjalanan Dinas</b>";

		$stream .= "<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		$stream .= "<thead><tr class=rowheader>
                                <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
                                <td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
                                <td align=center>" . $_SESSION['lang']['nodok'] . "</td>
                                <td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
                                <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
                                <td align=center style='width:8%'>" . $_SESSION['lang']['noakun'] . "</td>
                                <td align=center style='width:15%'>Nilai Rupiah Real</td>
                                <td align='center' style='width:10%'>Pengurang</td>";
		$stream .= "		    <td align=center style='width:10%'>(+/-)</td>
                                <td align=center style='width:10%'>Nilai Rupiah BI</td>
                                <td align='center'>" . $_SESSION['lang']['action'] . " <br/> <input type='checkbox' id='checkAll' onclick=\"toggleCheck(this, 'checkPjd')\" /></td>";
		$stream .= "</tr>";
		$stream .= "</thead>";

		$arrLainnya = array('BULKING', 'TC', 'RND', 'HOLDING', 'KANWIL'); //ikutin yg udh ada patok
		$arrakun = array('BULKING' => '8122101', 'TC' => '8212101', 'RND' => '8260604', 'KANWIL' => '8212101', 'HOLDING' => '8212101');
		$where = " AND left(tanggal,7) like '" . $per . "%'";
		//Nilai Perjalanan Dinas
		$str = "SELECT *  FROM " . $dbname . ".sdm_pjdinasht a  left join " . $dbname . ".sdm_pjdinasdt b
                    on a.notransaksi=b.notransaksi where 1=1 " . $where . " and b.sumber='1' and b.tanggungan='1' and a.statuspengajuan='1' and a.statusrealisasi='1' and b.jumlahhrd>'0' and kodeorg='{$unit}' and b.statusverifikasihrd='1'
                    order by a.notransaksi desc";
		$resx = fetchdata($str);
		foreach ($resx as $bar) {
			$dataxx[$bar['notransaksi']] = $bar['notransaksi'];
			$jlhrp[$bar['notransaksi']] += $bar['jumlahhrd'];
			$kary[$bar['notransaksi']] = $bar['karyawanid'];
			$kdorg[$bar['notransaksi']] = $bar['kodeorg'];
			$tanggal[$bar['notransaksi']] = $bar['tanggal'];
		}

		if (!empty($dataxx)) {
			foreach ($dataxx as $notransaksi) {
				@$noxz++;
				$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $kary[$notransaksi] . "'");
				$namaid = $nmkar[$kary[$notransaksi]];

				$opttipeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='" . $kdorg[$notransaksi] . "'");
				$kodeorg = $kdorg[$notransaksi];
				$tipeorg = $opttipeorg[$kdorg[$notransaksi]];

				if (in_array($tipeorg, $arrLainnya)) {
					$noakun = $arrakun[$tipeorg];
				} else {
					#ini unit kebun dan pks
					$noakun = '7111501';
				}
				$optnmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $noakun . "'");

				$stream .= "<tr class='rowcontent input'>";
				$stream .= "<td align=center>" . $noxz . "</td>";
				$stream .= "<td align=center>" . $kodeorg . "</td>";
				$stream .= "<td align='center' class='notransaksi'>" . $notransaksi . "</td>";
				$stream .= "<td align=center>" . $namaid . "</td>";
				$stream .= "<td align='center' class='tanggal'>" . $tanggal[$notransaksi] . "</td>";
				$stream .= "<td align=left>" . $noakun . "-" . $optnmakun[$noakun] . " <input type='hidden'  id=noakun_" . $noxz . " class='noakun' value='" . $noakun . "'/></td>";
				$stream .= "<td align=right id=jlhkas_" . $noxz . " class='rpreal'>" . number_format($jlhrp[$notransaksi], 2) . "</td>";
				$stream .= "<td align=center><input type='checkbox' class='checkPengurang' onchange=\"calcPlusMinus(this, 'sdbipjd')\"></td>";
				$stream .= "<td align=right><input type=text id='plusminuspjd_{$noxz}' value='" . number_format(0) . "' onkeyup=\"calcPlusMinus(this, 'sdbipjd');z.numberFormat('plusminuspjd_{$noxz}', 2);\" onkeypress=\"return angka_doang(event)\" class='myinputtextnumber plusminus' /></td>";
				$stream .= "<td align=right><input type=text id='" . $noxz . "_sdbipjd' value='" . number_format($jlhrp[$notransaksi], 2) . "' disabled onkeypress='return angka_doang(event)' class='myinputtextnumber sdbipjd'  ></td>";
				$stream .= "<td align='center'><input type='checkbox' class='checkPjd' title='Centang untuk menambahkan Perjalanan Dinas ke Biaya PDO'/></td>";
				$stream .= "</tr>";

				$totalBI += $jlhrp[$notransaksi];
			}

			// Grand Total
			$stream .= "<tr class=rowcontent  style='background-color: #50edd2'>";
			$stream .= "<td colspan='6' style='font-transform:uppercase; font-weight:bold; font-size:1rem; color:#333' align='right'>Grand Total</td>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td align='right' class='totalpengurang'>0</td>";
			$stream .= "<td align='right' class='totalnilaibi'>" . number_format($totalBI, 2) . "</td>";
			$stream .= "<td></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center colspan=11>
                        <button class=mybutton onclick=savepjd()>" . $_SESSION['lang']['save'] . "</button></td>";
			$stream .= "</tr>";
			$stream .= "</tbody></table>";
		}
		// END Perjalanan Dinas

		echo $stream;
		break;
	case 'listpjd':
		$border = 0;
		if ($tipex == "excel") {
			$border = 1;
		}

		$tporg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		$colspan = 6;
		$stream .= "<fieldset><legend><b>List Data Perjalanan Dinas</b></legend>
                        <table cellpading=1 cellspacing=1 border={$border} class=sortable>
                            <thead>
                                <tr>
                                    <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
                                    <td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
                                    <td align=center>" . $_SESSION['lang']['nodok'] . "</td>
                                    <td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
                                    <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
                                    <td align=center style='width:8%'>" . $_SESSION['lang']['noakun'] . "</td>
                                    <td align=center style='width:15%'>Nilai Rupiah Real</td>";
		$stream .= "		<td align=center style='width:10%'>(+/-)</td>
                                    <td align=center style='width:10%'>Nilai Rupiah BI</td>";
		if ($tipex != "excel") {
			$stream .= "<td align=center>" . $_SESSION['lang']['action'] . "</td>";
		}
		$stream .= "</tr></thead>";

		$akuntemp = $kdkegtemp = '';

		##ambil data yang udah ada
		$str = "SELECT a.kodeorg, b.* FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.nopdo='{$nopdo}' AND a.notransaksi='{$nopjd}'";
		$hsl = fetchdata($str);
		if (count($hsl) > 0) {
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$nmKary = makeOption($dbname, "sdm_pjdinasht", "notransaksi,karyawanid", "notransaksi='{$bar['nodocument']}'");
				if ($bar['noakun'] == '8110101' || $bar['noakun'] == '8110102') {
					$namaakun = $arrnmakun[$bar['noakun']] . " DARAT";
				} else if ($bar['noakun'] == '8110201' || $bar['noakun'] == '8110202') {
					$namaakun = $arrnmakun[$bar['noakun']] . " LAUT";
				} else {
					$namaakun = $arrnmakun[$bar['noakun']];
				}

				@$no++;
				$stream .= "<tr class='rowcontent'>";
				$stream .= "<td align='center' >" . $no . "</td>";
				$stream .= "<td align='left' >" . $bar['kodeorg'] . "</td>";
				$stream .= "<td align='left' >" . $bar['nodocument'] . "</td>";
				$stream .= "<td align='left' >" . getNamaKaryawan($nmKary[$bar['nodocument']]) . "</td>";
				$stream .= "<td align='left' >" . $bar['tanggal'] . "</td>";
				$stream .= "<td align='left' >" . $bar['noakun'] . " - " . $arrnmakun[$bar['noakun']] . "</td>";
				$stream .= "<td align='right'>" . number_format($bar['rupiahreal'], 2) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['rupiah'], 2) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";

				if ($tipex != "excel") {
					$stream .= "<td align='center'><img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deletepjd('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\"></td></tr>";
				}
				$next = $no;

				@$ttlakunesti[$bar['noakun']] += $bar['rupiah'];
				@$ttlakunbi[$bar['noakun']] += $bar['rupiahdiajukan'];
				@$tlesti += $bar['rupiah'];
				@$tlbi += $bar['rupiahdiajukan'];

				$akuntemp = $bar['noakun'];
			}

			$stream .= "<tr class=rowcontent>
                                <td colspan=" . $colspan . " align=center style=font-weight:bold>" . $_SESSION['lang']['total'] . "</td>
                                <td></td>
                                <td align=right>" . number_format($tlesti, 2) . "</td>
                                <td align=right>" . number_format($tlbi, 2) . "</td>";
			if ($tipex != "excel") {
				$stream .= "<td></td>";
			}
			$stream .= "</tr>";
		} else {
			$stream .= "<tr class=rowcontent><td colspan=8 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}

		$stream .= "</table></fieldset>";
		if ($tipex != "excel") {
			echo $stream;
		} else {
			$nop = "PDO Perjalanan Dinas.xls";
			$xls = new HtmlExcel();
			$borderStyle = 'border: 1px solid #000;';
			$css = "table { border-collapse: collapse; } th, td { $borderStyle padding: 5px; }";
			$xls->setCss($css);
			$xls->addSheet('PDO Perjalanan Dinas', $stream);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
		break;

	case 'noothers':
		$noothers = generateKodeTransaksi($unit, $per, "OTH");
		echo $noothers;
		break;
	case 'detailothers':
		$stream = "";
		$stream .= "<fieldset><legend><b>Detail Input</b></legend>";
		// Start Table; Table Head
		$stream .= "<br/><b>PMK Lainnya</b>";

		$stream .= "
                <table cellpading=1 cellspacing=1 border=0 class=sortable style='width:100%'>
                    <thead>
                        <tr>
                            <td align=center style='width:3%'>" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center style='width:8%'>Nomor Akun</td>
							<td align=center style='width:21%'>" . $_SESSION['lang']['uraian'] . "</td>";
		$stream .= "	    <td align=center style='width:10%'>Nilai Rupiah BI</td>
							<td align='center' style='width:8%'>" . $_SESSION['lang']['action'] . "</td>
                        </tr>
                    </thead>";
		$stream .= "<tbody>";

		##ambil data yang udah ada
		$str = "select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi = '{$noothers}'";
		$hsl = fetchdata($str);
		if (count($hsl) > 0) {
			$no = 1;
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$optAkun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
				$qAkun = selectQuery($dbname, "keu_5akun", "noakun, namaakun", "kasbankdetail='1' AND aktif='1'");
				$rAkun = fetchData($qAkun);
				foreach ($rAkun as $row) {
					if ($row['noakun'] == $bar['noakun']) {
						$optAkun .= "<option value='{$row['noakun']}' selected>{$row['noakun']} - {$row['namaakun']}</option>";
					} else {
						$optAkun .= "<option value='{$row['noakun']}'>{$row['noakun']} - {$row['namaakun']}</option>";
					}
				}

				$stream .= "<tr class='rowcontent manual-rows2 input'>";
				$stream .= "<td align='center'>" . $no++ . "</td>";
				$stream .= "<td align='center' class='selectParent'><select class='select2 noakun' style='width:98%'>{$optAkun}</select></td>";
				$stream .= "<td align='center'><input type='text' class='keterangan myinputtext' style='width:98%' value='" . $bar['rincian'] . "'/></td>";
				$stream .= "<td align=right><input type=text id='sdbiothers' value='" . $bar['rupiahdiajukan'] . "' onkeypress='return angka_doang(event)' class='myinputtextnumber sdbiothers' oninput=\"calcBIOthers()\" onkeyup=\"autoChecked(this)\"/></td>";
				$stream .= "
                <td>
                    <div style='display:flex; gap:5px; justify-content:center;'>
                        <img class='zImgBtn' src='images/plus.png' alt='btn-add' onclick=\"addRowsOthers(this)\" title='Tambah Baris' style='width:15px;height:15px;display:none' />
                        <img class='zImgBtn' src='images/delete_32.png' alt='btn-delete' onclick=\"deleteRowsOthers(this)\" title='Hapus Baris' style='width:15px;height:15px;' />

                        <input type='checkbox' class='checkPemby' title='Centang untuk menambahkan PMK Lainnya ke Biaya PDO' onchange=\"checkPemby()\" disabled style='display:none' checked/>
                    </div>
                </td>";
				$stream .= "</tr>";
			}
		}

		$optAkun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$qAkun = selectQuery($dbname, "keu_5akun", "noakun, namaakun", "kasbankdetail='1' AND aktif='1'");
		$rAkun = fetchData($qAkun);
		foreach ($rAkun as $row) {
			$optAkun .= "<option value='{$row['noakun']}'>{$row['noakun']} - {$row['namaakun']}</option>";
		}
		$stream .= "<tr class='rowcontent manual-rows2 input'>";
		$stream .= "<td align='center'></td>";
		$stream .= "<td align='center' class='selectParent'><select class='select2 noakun' style='width:98%'>{$optAkun}</select></td>";
		$stream .= "<td align='center'><input type='text' class='keterangan myinputtext' style='width:98%'/></td>";
		$stream .= "<td align=right><input type=text id='sdbiothers' value='0' onkeypress='return angka_doang(event)' class='myinputtextnumber sdbiothers' oninput=\"calcBIOthers()\" onkeyup=\"autoChecked(this)\"/></td>";
		$stream .= "
            <td>
                <div style='display:flex; gap:5px; justify-content:center;'>
                    <img class='zImgBtn' src='images/plus.png' alt='btn-add' onclick=\"addRowsOthers(this)\" title='Tambah Baris' style='width:15px;height:15px;' />
                    <img class='zImgBtn' src='images/delete_32.png' alt='btn-delete' onclick=\"deleteRowsOthers(this)\" title='Hapus Baris' style='width:15px;height:15px;' />

                    <input type='checkbox' class='checkPemby' title='Centang untuk menambahkan PMK Lainnya ke Biaya PDO' onchange=\"checkPemby()\" disabled style='display:none'/>
                </div>
            </td>";
		$stream .= "</tr>";

		$stream .= "</tbody>";
		$stream .= "<tfoot>";
		// Grand Total
		$stream .= "<tr class=rowcontent  style='background-color: #50edd2'>";
		$stream .= "<td colspan='3' style='font-transform:uppercase; font-weight:bold; font-size:1rem; color:#333' align='right'>Grand Total</td>";
		$stream .= "<td align='right' class='totalnilaibi'>0</td>";
		$stream .= "<td></td>";
		$stream .= "</tr>";

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center colspan=11>
                <button class=mybutton onclick=saveothers()>" . $_SESSION['lang']['save'] . "</button></td>";
		$stream .= "</tr>";
		$stream .= "</tfoot>";

		$stream .= "</table>";

		// END PMK Lainnya

		echo $stream;
		break;
	case 'listothers':
		$border = 0;
		if ($tipex == "excel") {
			$border = 1;
		}

		$tporg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		$colspan = 6;
		$stream .= "<fieldset><legend><b>List Data PMK Lainnya</b></legend>
                <table cellpading=1 cellspacing=1 border={$border} class=sortable>
                    <thead>
                    <tr>
                    <td align=center style='width:3%'>" . $_SESSION['lang']['nourut'] . "</td>
                    <td align=center style='width:8%'>Nomor Akun</td>
                    <td align=center style='width:21%'>" . $_SESSION['lang']['uraian'] . "</td>";
		$stream .= "<td align=center style='width:10%'>Nilai Rupiah BI</td>";
		if ($tipex != "excel") {
			$stream .= "<td align=center>" . $_SESSION['lang']['action'] . "</td>";
		}
		$stream .= "</tr></thead>";

		##ambil data yang udah ada
		$str = "SELECT a.kodeorg, b.* FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.nopdo='{$nopdo}' AND a.notransaksi='{$noothers}'";
		$hsl = fetchdata($str);
		if (count($hsl) > 0) {
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$nmAkun = makeOption($dbname, "keu_5akun", "noakun,namaakun", "noakun='{$bar['noakun']}'");
				@$no++;
				$stream .= "<tr class='rowcontent'>";
				$stream .= "<td align='center' >" . $no . "</td>";
				$stream .= "<td align='left' >" . $bar['noakun'] . " - {$nmAkun[$bar['noakun']]}</td>";
				$stream .= "<td align='left' >" . $bar['rincian'] . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";
				if ($tipex != "excel") {
					$stream .= "<td align='center'><img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deleteothers('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\"></td></tr>";
				}
				$next = $no;

				@$tlbi += $bar['rupiahdiajukan'];
			}

			$stream .= "<tr class=rowcontent>
                        <td colspan='3' align=center style=font-weight:bold>" . $_SESSION['lang']['total'] . "</td>
                        <td align=right>" . number_format($tlbi, 2) . "</td>";

			if ($tipex != "excel") {
				$stream .= "<td></td>";
			}
			$stream .= "</tr>";
		} else {
			$stream .= "<tr class=rowcontent><td colspan=8 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}

		$stream .= "</table></fieldset>";
		if ($tipex != "excel") {
			echo $stream;
		} else {
			$nop = "PDO Others.xls";
			$xls = new HtmlExcel();
			$borderStyle = 'border: 1px solid #000;';
			$css = "table { border-collapse: collapse; } th, td { $borderStyle padding: 5px; }";
			$xls->setCss($css);
			$xls->addSheet('PDO Others', $stream);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
		break;

	case 'notanaman':
		$notanaman = generateKodeTransaksi($unit, $per, "TNM");
		echo $notanaman;
		break;
	case 'detailtanaman':
		$stream = "";
		$stream .= "<fieldset><legend><b>Detail Input</b></legend>";
		// Start Table; Table Head
		$stream .= "<br/><b>PMK Tanaman</b>";

		$stream .= "
                    <table cellpading=1 cellspacing=1 border=0 class=sortable style='width:100%'>
                        <thead>
                            <tr>
                                <td align=center style='width:3%'>" . $_SESSION['lang']['nourut'] . "</td>
                                <td align=center style='width:25%'>Nomor Akun</td>
                                <td align=center style='width:15%'>Nilai Rupiah Real</td>
                                <td align=center style='width:10%'>Pot. BPJS Beban Karyawan</td>
                                <td align=center style='width:10%'>Pot. Alat Panen</td>
                                <td align=center style='width:10%'>Potongan Penalty</td>
                                <td align=center style='width:10%'>Potongan Kontanan</td>";
		$stream .= "	        <td align=center style='width:10%'>Nilai Rupiah BI</td>
                            </tr>
                        </thead>";
		$stream .= "<tbody>";

		# Data
		$data = [];
		$str = "select * from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,3) in ('611','621','126','128') AND left(a.kodeorg, 4) = '{$unit}' and periode = '{$per}'";
		$res = fetchdata($str);
		foreach ($res as $row) {
			$jobgroup = substr($row['noakun'], 0, 5);
			$data[$jobgroup]['nilai'] += $row['jumlah'];

			# Pot BPJS Beban Karyawan
			$qKomponen = selectQuery($dbname, "setup_parameterappl", "nilai", "kodeparameter='BPJSBK'");
			$rKomponen = fetchData($qKomponen)[0];
			$listKomponen = $rKomponen['nilai'];

			$qPotBpjsKaryawan = selectQuery($dbname, "sdm_gaji_vw", "SUM(jumlah) as jumlah", "periodegaji='{$per}' AND kodeorg='{$unit}' AND karyawanid='{$row['nik']}' AND idkomponen IN ({$listKomponen})");
			$rPotBpjsKaryawan = fetchData($qPotBpjsKaryawan)[0];
			$data[$jobgroup]['potbpjs'] += $rPotBpjsKaryawan['jumlah'];

			# Pot Alat Panen
			$qPotAlatPanen = selectQuery($dbname, "sdm_potongandt", "SUM(jumlahpotongan) as jumlah", "kodeorg='{$unit}' AND periodegaji='{$per}' AND nik='{$row['nik']}' AND tipepotongan='88'");
			$rPotAlatPanen = fetchData($qPotAlatPanen)[0];
			$data[$jobgroup]['potalatpnn'] += $rPotAlatPanen['jumlah'];

			# Pot Penalty
			$qPotPenalty = selectQuery($dbname, "keu_jurnaldt_vw", "SUM(debet) as jumlah", "periode='{$per}' AND kodeorg='{$unit}' AND nik='{$row['nik']}' AND kodejurnal='PNN05'");
			$rPotPenalty = fetchData($qPotPenalty)[0];
			$data[$jobgroup]['potpenalty'] += $rPotPenalty['jumlah'];

			# Pot Kontanan
			$qPotKontanan = selectQuery($dbname, "keu_jurnaldt_vw", "SUM(debet) as jumlah", "periode='{$per}' AND kodeorg='{$unit}' AND nik='{$row['nik']}' AND keterangan LIKE '%Pot Kontanan%'");
			$rPotKontanan = fetchData($qPotKontanan)[0];
			$data[$jobgroup]['potkontanan'] += $rPotKontanan['jumlah'];
		}

		if (count($data) > 0) {
			$no = 1;
			foreach ($data as $noakun => $bar) {
				@$noxz++;
				$sdbitanaman = $bar['nilai'] - $bar['potbpjs'] - $bar['potalatpnn'] - $bar['potpenalty'] - $bar['potkontanan'];

				$stream .= "<tr class='rowcontent input'>";
				$stream .= "<td align='center'>" . $no++ . "</td>";
				$stream .= "<td align=left>" . $noakun . " - " . $arrnmakun[$noakun] . " <input type='hidden'  id=noakun_" . $noxz . " class='noakun' value='" . $noakun . "'/></td>";
				$stream .= "<td align=right class='rpreal' id=jlhkas_" . $noxz . ">" . number_format($bar['nilai'], 2) . "</td>";
				$stream .= "<td align=right><input type=text id='" . $noxz . "_potbpjs' value='" . number_format($bar['potbpjs'], 2) . "' onkeyup=\"z.numberFormat('" . $noxz . "_potbpjs', 2);calcBITanaman(this)\" onkeypress='return angka_doang(event)' class='myinputtextnumber potbpjs'  ></td>";
				$stream .= "<td align=right><input type=text id='" . $noxz . "_potalatpnn' value='" . number_format($bar['potalatpnn'], 2) . "' onkeyup=\"z.numberFormat('" . $noxz . "_potalatpnn', 2);calcBITanaman(this)\" onkeypress='return angka_doang(event)' class='myinputtextnumber potalatpnn'  ></td>";
				$stream .= "<td align=right><input type=text id='" . $noxz . "_potpenalty' value='" . number_format($bar['potpenalty'], 2) . "' onkeyup=\"z.numberFormat('" . $noxz . "_potpenalty', 2);calcBITanaman(this)\" onkeypress='return angka_doang(event)' class='myinputtextnumber potpenalty'  ></td>";
				$stream .= "<td align=right><input type=text id='" . $noxz . "_potkontanan' value='" . number_format($bar['potkontanan'], 2) . "' onkeyup=\"z.numberFormat('" . $noxz . "_potkontanan', 2);calcBITanaman(this)\" onkeypress='return angka_doang(event)' class='myinputtextnumber potkontanan'  ></td>";
				$stream .= "<td align=right><input type=text id='" . $noxz . "_sdbitanaman' value='" . number_format($sdbitanaman, 2) . "' disabled onkeypress='return angka_doang(event)' class='myinputtextnumber sdbitanaman'  ></td>";
				$stream .= "</tr>";

				$gRpreal += $bar['nilai'];
				$gPotBpjs += $bar['potbpjs'];
				$gPotAlatPanen += $bar['potalatpnn'];
				$gPotPenalty += $bar['potpenalty'];
				$gPotKontanan += $bar['potkontanan'];
				$gTtl += $sdbitanaman;
			}
		}

		$stream .= "</tbody>";
		$stream .= "<tfoot>";
		// Grand Total
		$stream .= "<tr class=rowcontent  style='background-color: #50edd2'>";
		$stream .= "<td colspan='2' style='font-transform:uppercase; font-weight:bold; font-size:1rem; color:#333' align='right'>Grand Total</td>";
		$stream .= "<td align='right' class='totalnilaireal'>" . number_format($gRpreal) . "</td>";
		$stream .= "<td align='right' class='totalnilaipotbpjs'>" . number_format($gPotBpjs) . "</td>";
		$stream .= "<td align='right' class='totalnilaipotalatpnn'>" . number_format($gPotAlatPanen) . "</td>";
		$stream .= "<td align='right' class='totalnilaipotpenalty'>" . number_format($gPotPenalty) . "</td>";
		$stream .= "<td align='right' class='totalnilaipotkontanan'>" . number_format($gPotPenalty) . "</td>";
		$stream .= "<td align='right' class='totalnilaibi'>" . number_format($gTtl) . "</td>";
		$stream .= "</tr>";

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center colspan=11>
                    <button class=mybutton onclick=savetanaman()>" . $_SESSION['lang']['save'] . "</button></td>";
		$stream .= "</tr>";
		$stream .= "</tfoot>";

		$stream .= "</table>";

		// END PMK Tanaman

		echo $stream;
		break;
	case 'listtanaman':
		$tporg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		$colspan = 6;
		$stream .= "<fieldset><legend><b>List Data PMK Tanaman</b></legend>";

		$stream .= "
                    <table cellpading=1 cellspacing=1 border=0 class=sortable style='width:100%'>
                        <thead>
                            <tr>
                                <td align=center style='width:3%'>" . $_SESSION['lang']['nourut'] . "</td>
                                <td align=center style='width:25%'>Nomor Akun</td>
                                <td align=center style='width:15%'>Nilai Rupiah Real</td>
                                <td align=center style='width:10%'>Pot. BPJS Beban Karyawan</td>
                                <td align=center style='width:10%'>Pot. Alat Panen</td>
                                <td align=center style='width:10%'>Potongan Penalty</td>
                                <td align=center style='width:10%'>Potongan Kontanan</td>";
		$stream .= "	        <td align=center style='width:10%'>Nilai Rupiah BI</td>
                                <td align=center style='width:10%'>" . $_SESSION['lang']['aksi'] . "</td>
                            </tr>
                        </thead>";

		##ambil data yang udah ada
		$str = "SELECT a.kodeorg, b.* FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.nopdo='{$nopdo}' AND a.notransaksi='{$notanaman}'";
		$hsl = fetchdata($str);
		if (count($hsl) > 0) {
			$res = fetchdata($str);
			foreach ($res as $bar) {
				@$no++;
				$stream .= "<tr class='rowcontent'>";
				$stream .= "<td align='center' >" . $no . "</td>";
				$stream .= "<td align='left' >" . $bar['noakun'] . " - " . getNamaAkun($bar['noakun']) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['rupiahreal'], 2) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['potbpjs'], 2) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['potalatpnn'], 2) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['potpenalty'], 2) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['potkontanan'], 2) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";
				$stream .= "<td align='center'><img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deletetanaman('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\"></td></tr>";
				$next = $no;

				@$tlbireal += $bar['rupiahreal'];
				@$tlbi += $bar['rupiahdiajukan'];
			}

			$stream .= "<tr class=rowcontent>
                            <td colspan='2' align=center style=font-weight:bold>" . $_SESSION['lang']['total'] . "</td>
                            <td align=right>" . number_format($tlbireal, 2) . "</td>
                            <td align=right>" . number_format(0, 2) . "</td>
                            <td align=right>" . number_format(0, 2) . "</td>
                            <td align=right>" . number_format(0, 2) . "</td>
                            <td align=right>" . number_format(0, 2) . "</td>
                            <td align=right>" . number_format($tlbi, 2) . "</td>
                            <td></td>
                            </tr>";
		} else {
			$stream .= "<tr class=rowcontent><td colspan=8 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}

		$stream .= "</table></fieldset>";
		echo $stream;
		break;

	case 'notraksi':
		$notraksi = generateKodeTransaksi($unit, $per, "TRK");
		echo $notraksi;
		break;
	case 'detailtraksi':
		$stream = "";
		$stream .= "<fieldset><legend><b>Detail Input</b></legend>";
		// Start Table; Table Head
		$stream .= "<br/><b>PMK Traksi</b>";

		$stream .= "
                        <table cellpading=1 cellspacing=1 border=0 class=sortable style='width:100%'>
                            <thead>
                                <tr>
                                    <td align=center style='width:3%'>" . $_SESSION['lang']['nourut'] . "</td>
                                    <td align=center style='width:25%'>Uraian</td>
                                    <td align=center style='width:15%'>Nilai Rupiah Real</td>
                                    <td align='center' style='width:10%'>Pengurang</td>";
		$stream .= "		        <td align=center style='width:10%'>(+/-)</td>
                                    <td align=center style='width:10%'>Nilai Rupiah BI</td>
                                    <td align='center'>" . $_SESSION['lang']['action'] . " <br/> <input type='checkbox' id='checkAll' onclick=\"toggleCheck(this, 'checkTraksi')\" /></td>
                                </tr>
                            </thead>";
		$stream .= "<tbody>";

		# Data
		$data = [];
		$str = "SELECT SUM(c.premi) as premi, b.jenispekerjaan
        FROM vhc_runht a
        RIGHT JOIN vhc_rundt b ON a.notransaksi=b.notransaksi
        RIGHT JOIN vhc_runhk c ON a.notransaksi=c.notransaksi
        WHERE a.tanggal LIKE '" . $per . "%' AND a.kodeorg='{$unit}'
        GROUP BY b.jenispekerjaan";
		$res = fetchdata($str);
		foreach ($res as $row) {
			$data[$row['jenispekerjaan']]['jumlah'] = $row['premi'];
		}

		$str = selectQuery($dbname, "sdm_gaji_vw", "jumlah, idkomponen", "tipekaryawan IN (4,15) AND subbagian IN ('{$unit}50','{$unit}70') AND periodegaji='{$per}'");
		$res = fetchData($str);
		foreach ($res as $row) {
			$arrKomponen = makeOption($dbname, "sdm_ho_component", 'id,plus');
			if ($arrKomponen[$row['idkomponen']] == 1) {
				$dataGaji += $row['jumlah'];
			} else {
				$dataGaji += $row['jumlah'] * -1;
			}
		}

		$nmAkunVhc = makeOption($dbname, "vhc_kegiatan", "kodekegiatan,namakegiatan");
		if (count($data) > 0) {
			$noakungaji = 7111102;
			$stream .= "<tr class='rowcontent input'>";
			$stream .= "<td align='center'>1</td>";
			$stream .= "<td align=left>" . $noakungaji . " - " . $arrnmakun[$noakungaji] . " <input type='hidden'  id=noakun_1 class='noakun' value='" . $noakungaji . "'/></td>";
			$stream .= "<td align=right id=jlhkas_" . $noxz . " class='rpreal'>" . number_format($dataGaji, 2) . "</td>";
			$stream .= "<td align=center><input type='checkbox' class='checkPengurang' onchange=\"calcPlusMinus(this, 'sdbitraksi')\"></td>";
			$stream .= "<td align=right><input type=text id='plusminustraksi_{$noxz}' value='" . number_format(0) . "' onkeyup=\"calcPlusMinus(this, 'sdbitraksi');z.numberFormat('plusminustraksi_{$noxz}', 2);\" onkeypress=\"return angka_doang(event)\" class='myinputtextnumber plusminus' /></td>";
			$stream .= "<td align=right><input type=text id='" . $noxz . "_sdbitraksi' value='" . number_format($dataGaji, 2) . "' disabled onkeypress='return angka_doang(event)' class='myinputtextnumber sdbitraksi'  ></td>";
			$stream .= "<td align='center'><input type='checkbox' class='checkTraksi' title='Centang untuk menambahkan Traksi ke Biaya PDO'/></td>";
			$stream .= "</tr>";
			$noxz = 2;
			foreach ($data as $kodekegiatan => $bar) {
				@$noxz++;

				$stream .= "<tr class='rowcontent input'>";
				$stream .= "<td align='center'>" . $noxz++ . "</td>";
				$stream .= "<td align=left>" . $kodekegiatan . " - " . $nmAkunVhc[$kodekegiatan] . " <input type='hidden'  id=noakun_" . $noxz . " class='noakun' value='" . $kodekegiatan . "'/></td>";
				$stream .= "<td align=right id=jlhkas_" . $noxz . " class='rpreal'>" . number_format($bar['jumlah'], 2) . "</td>";
				$stream .= "<td align=center><input type='checkbox' class='checkPengurang' onchange=\"calcPlusMinus(this, 'sdbitraksi')\"></td>";
				$stream .= "<td align=right><input type=text id='plusminustraksi_{$noxz}' value='" . number_format(0) . "' onkeyup=\"calcPlusMinus(this, 'sdbitraksi');z.numberFormat('plusminustraksi_{$noxz}', 2);\" onkeypress=\"return angka_doang(event)\" class='myinputtextnumber plusminus' /></td>";
				$stream .= "<td align=right><input type=text id='" . $noxz . "_sdbitraksi' value='" . number_format($bar['jumlah'], 2) . "' disabled onkeypress='return angka_doang(event)' class='myinputtextnumber sdbitraksi'  ></td>";
				$stream .= "<td align='center'><input type='checkbox' class='checkTraksi' title='Centang untuk menambahkan Traksi ke Biaya PDO'/></td>";
				$stream .= "</tr>";

				$gTtl += $bar['jumlah'];
			}
		}

		$optKegiatan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$qKegiatan = selectQuery($dbname, "vhc_kegiatan", "kodekegiatan, namakegiatan");
		$rKegiatan = fetchData($qKegiatan);
		foreach ($rKegiatan as $row) {
			$optKegiatan .= "<option value='{$row['kodekegiatan']}'>{$row['kodekegiatan']} - {$row['namakegiatan']}</option>";
		}
		$stream .= "<tr class='rowcontent manual-rows3 input'>";
		$stream .= "<td align='center'></td>";
		$stream .= "<td align='center' class='selectParent'><select class='select2 noakun' style='width:98%'>{$optKegiatan}</select></td>";
		$stream .= "<td class='rpreal' align='right'>0</td>";
		$stream .= "<td></td>";
		$stream .= "<td><input type='hidden' class='plusminus' value='0' /></td>";
		$stream .= "<td align=right><input type=text id='sdbitraksi_999' value='0' onkeyup=\"z.numberFormat('sdbitraksi_999', 2);valueRupiahReal(this)\" onkeypress=\"return angka_doang(event)\" class='myinputtextnumber sdbitraksi' oninput=\"calcBITraksi()\" onkeyup=\"autoChecked(this)\"/></td>";
		$stream .= "
            <td>
                <div style='display:flex; gap:5px; justify-content:center;'>
                    <img class='zImgBtn' src='images/plus.png' alt='btn-add' onclick=\"addRowsTraksi(this)\" title='Tambah Baris' style='width:15px;height:15px;' />
                    <img class='zImgBtn' src='images/delete_32.png' alt='btn-delete' onclick=\"deleteRowsTraksi(this)\" title='Hapus Baris' style='width:15px;height:15px;' />

                    <input type='checkbox' class='checkTraksi' disabled title='Centang untuk menambahkan Traksi ke Biaya PDO' style='display:none'/>
                </div>
            </td>";
		$stream .= "</tr>";

		$stream .= "</tbody>";
		$stream .= "<tfoot>";
		// Grand Total
		$stream .= "<tr class=rowcontent  style='background-color: #50edd2'>";
		$stream .= "<td colspan='2' style='font-transform:uppercase; font-weight:bold; font-size:1rem; color:#333' align='right'>Grand Total</td>";
		$stream .= "<td align='right' class='totalnilaireal'>" . number_format($gTtl) . "</td>";
		$stream .= "<td align='right'></td>";
		$stream .= "<td align='right' class='totalnilaiplusminus'>" . number_format(0) . "</td>";
		$stream .= "<td align='right' class='totalnilaibi'>" . number_format($gTtl) . "</td>";
		$stream .= "<td></td>";
		$stream .= "</tr>";

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center colspan=11>
                        <button class=mybutton onclick=savetraksi()>" . $_SESSION['lang']['save'] . "</button></td>";
		$stream .= "</tr>";
		$stream .= "</tfoot>";

		$stream .= "</table>";

		// END PMK Traksi

		echo $stream;
		break;
	case 'listtraksi':
		$tporg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
		$colspan = 6;
		$stream .= "<fieldset><legend><b>List Data PMK Traksi</b></legend>";

		$stream .= "
                        <table cellpading=1 cellspacing=1 border=0 class=sortable style='width:100%'>
                            <thead>
                                <tr>
                                    <td align=center style='width:3%'>" . $_SESSION['lang']['nourut'] . "</td>
                                    <td align='center' style='width:25%'>Uraian</td>
                                    <td align='center' style='width:15%'>Nilai Rupiah Real</td>
                                    <td align='center' style='width:10%'>Pengurang</td>";
		$stream .= "		        <td align='center' style='width:10%'>(+/-)</td>
                                    <td align='center' style='width:10%'>Nilai Rupiah BI</td>
                                    <td align='center' style='width:10%'>" . $_SESSION['lang']['aksi'] . "</td>
                                </tr>
                            </thead>";

		##ambil data yang udah ada
		$str = "SELECT a.kodeorg, b.* FROM keu_pdoht a LEFT JOIN keu_pdodt b ON a.nopdo=b.nopdo AND a.notransaksi=b.notransaksi WHERE a.nopdo='{$nopdo}' AND a.notransaksi='{$notraksi}'";
		$hsl = fetchdata($str);
		if (count($hsl) > 0) {
			$res = fetchdata($str);
			foreach ($res as $bar) {
				@$no++;
				$stream .= "<tr class='rowcontent'>";
				$stream .= "<td align='center' >" . $no . "</td>";
				$noakun = getNamaAkun($bar['noakun']);
				if ($noakun == "") {
					$noakun = getNamaKeg($bar['noakun']);
				}
				$stream .= "<td align='left' >" . $bar['noakun'] . " - " . $noakun . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['rupiahreal'], 2) . "</td>";
				$stream .= "<td align='right' ></td>";
				$stream .= "<td align='right' >" . number_format($bar['rupiah'], 2) . "</td>";
				$stream .= "<td align='right' >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";
				$stream .= "<td align='center'><img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deletetraksi('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\"></td></tr>";
				$next = $no;

				@$tlbireal += $bar['rupiahreal'];
				@$tlrupiah += $bar['rupiah'];
				@$tlbi += $bar['rupiahdiajukan'];
			}

			$stream .= "<tr class=rowcontent>
                            <td colspan='2' align=center style=font-weight:bold>" . $_SESSION['lang']['total'] . "</td>
                            <td align=right>" . number_format($tlbireal, 2) . "</td>
                            <td></td>
                            <td align=right>" . number_format($tlrupiah, 2) . "</td>
                            <td align=right>" . number_format($tlbi, 2) . "</td>
                            <td></td>
                        </tr>";
		} else {
			$stream .= "<tr class=rowcontent><td colspan=8 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}

		$stream .= "</table></fieldset>";
		echo $stream;
		break;
	########################################################
	#####  T A B   P E M B A Y A R A N S U P P L I E R  ####
	########################################################
	case 'simpanbyrsup':

		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $nobyrsup . "' and nourut='" . $idpdo . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		if ($akhir == 'iya') {
			$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`,`rupiah`)
							VALUES ('" . $nopdo . "', '" . $nobyrsup . "', '" . $idpdo . "','" . $byrlain . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$strcek = "select * from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "'";
			$rescek = fetchData($strcek);
			if (count($rescek) == 0) {
				$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `kodeorg`, `periode`,`updateby`)
						VALUES ('" . $nopdo . "', '" . $unit . "', '" . $per . "','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`,`rupiah`)
							VALUES ('" . $nopdo . "', '" . $nobyrsup . "', '" . $idpdo . "', '" . $noakunbyrsup . "','" . $jumlahbyrsupx . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal !: " . $e->getMessage() . "\n";
				die();
			}
		}

		break;
	case 'nobyrsup':
		if ($perawal != $per) {
			exit("Error: Tanggal diluar periode \nPeriode aktip adalah : " . $per . "");
		}
		$thn = substr($per, 0, 4);
		$per = str_replace('-', '', $per);
		if ($nobyrsup == '') {
			##cek apakah sudah pernah ada data diinput
			##param : nopdo - periode - divisi - tipekaryawan
			$str = " select notransaksi from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%SUPP%' and tipekaryawan='" . $tkbyrsup . "'  order by notransaksi desc limit 1 ";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$nolama = $bar['notransaksi'];
			}

			if ($nolama == '') {
				$str = " select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%SUPP%' order by notransaksi desc limit 1 ";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$notran = $bar['notransaksi'];
				}
				$num = explode('/', $notran);
				$num = @$num[3] + 1;
				if ($num < 10) {
					$num = '00' . $num;
				} else if ($num < 100) {
					$num = '0' . $num;
				} else {
					$num = $num;
				}

				//'201506/DUKE/PAD/001
				$nobyrsupbaru = $per . '/' . $unit . '/SUPP/' . $num;
			} else {
				$nobyrsupbaru = $nolama;
			}
		} else {
			$nobyrsupbaru = $nobyrsup;
		}
		echo $nobyrsupbaru;
		break;
	case 'detailbyrsup':
		$stream = "";
		$stream .= "<fieldset><legend><b>Detail Input</b></legend>";
		$stream .= "
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                    <thead>
                        <tr>
                            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center>Akun Biaya</td>
							<td align=center>" . $_SESSION['lang']['uraian'] . "</td>
							<td align=center>Nilai Rupiah</td>
                        </tr>
                    </thead>";
		//ambil supplier(Stock)
		// $sql     = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='SUPP'";
		// $rst     = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		// $rst->setFetchMode(PDO::FETCH_ASSOC);
		// $rby     = $rst->fetch();
		// $ps        = explode(',',$rby['nilai']);
		$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='SUPP'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$rby = $bar['nilai'];
		}
		$ps = explode(',', $rby['nilai']);
		$str = "select notransaksi,noakun,noakun2a,jumlah from " . $dbname . ".keu_kasbankdtht_vw where left(tanggal,7) like '" . $per . "%' and posting='1' and jurnal='1' and pembayaran='1' and kodeorg='" . $unit . "' and noakun in ('" . implode("','", $ps) . "') ";
		$resx = fetchdata($str);
		foreach ($resx as $val) {
			$noxz++;
			// $subtotal+=$totaldetail;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center>" . $noxz . "</td>";
			$stream .= "<td align=left id=noakun_" . $noxz . ">" . $val['noakun'] . "</td>";
			$stream .= "<td align=left>" . $arrnmakun[$val['noakun']] . "</td>";
			$stream .= "<td align=right id=jlh_" . $noxz . ">" . number_format($val['jumlah'], 2) . "</td>";
			$stream .= "</tr>";
			$akhir = $noxz;
		}
		//baris biaya lain
		$akhir += 1;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center>" . $akhir . "</td>";
		$stream .= "<td align=left></td>";
		$stream .= "<td align=left>Lain - lain</td>";
		$stream .= "<td align=right><input type=text id=byrlain onkeypress=\"return angka_doang(event)\" class=myinputtextnumber></td>";
		$stream .= "</tr>";

		//tombol simpan
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center colspan=4><button class=mybutton onclick=simpanbyrsup('" . $akhir . "')>" . $_SESSION['lang']['save'] . "</button></td>";
		$stream .= "</tr>";
		echo $stream;
		break;
	case 'listbyrsup':
		$stream .= "<fieldset><legend><b>List Data " . $_SESSION['lang']['pemabayaran'] . " " . $_SESSION['lang']['pemasok'] . "</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                    <thead>
						<tr>
							<td align=center rowspan=2 >" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center>Akun Biaya</td>
							<td align=center>" . $_SESSION['lang']['uraian'] . "</td>
							<td align=center>Nilai Rupiah</td>
							<td align=center>" . $_SESSION['lang']['action'] . "</td>
						</tr>
                    </thead>";
		##ambil data yang udah ada
		$str = "select noakun,nourut,rupiah,notransaksi,nopdo from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%SUPP%'";
		$res = fetchdata($str);
		if (count($hsl) > 0) {
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$no++;
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center >" . $bar['nourut'] . "</td>";
				$stream .= "<td align=left >" . $bar['noakun'] . "</td>";
				if ($no == count($hsl)) {
					$stream .= "<td align=left >Lain - lain</td>";
				} else {
					$stream .= "<td align=left >" . $arrnmakun[$bar['noakun']] . "</td>";
				}
				$stream .= "<td align=right >" . number_format($bar['rupiah'], 2) . "</td>";
				$stream .= "<td align=center><img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deletebyrsup('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\"></td>";
				$next = $no;
			}
		} else {
			$stream .= "<tr class=rowcontent><td colspan=5 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}

		$stream .= "</table></fieldset>";
		echo $stream;
		break;

	case 'nosupplier':
		$nosupplier = generateKodeTransaksi($unit, $per, "SUPP");
		echo $nosupplier;
		break;
	case 'detailkontraktor':
		$stream = "";
		$stream .= "<fieldset><legend><b>Detail Input</b></legend>";
		$stream .= "
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                    <thead>
                        <tr>
                            <td align=center rowspan=2 >" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center>" . $_SESSION['lang']['noinvoice'] . "</td>
							<td align=center>" . $_SESSION['lang']['noreferensi'] . "</td>
							<td align=center>" . $_SESSION['lang']['tipe'] . "</td>
							<td align=center>Termin</td>
							<td align=center>Nama Pihak Ketiga</td>
							<td align=center>Kegiatan</td>
							<td align=center>Nilai Invoice</td>
							<td align=center>" . $_SESSION['lang']['action'] . " <br/> <input type='checkbox' id='checkAll' onclick=\"toggleCheck(this, 'checkKontrak')\" /></td>
                        </tr>
                    </thead>";
		$where = " AND left(a.tanggal,7) like '" . $per . "%'";
		// if ($sesi == 1) {
		//     $where = " AND a.tanggal >= '".$per."-01' AND a.tanggal <= '".$per."-15'";
		// } else if ($sesi == 2) {
		//     $where = " AND a.tanggal >= '".$per."-16' AND a.tanggal <= '".$per."-31'";
		// }

		$tporg = makeOption($dbname, "organisasi", "kodeorganisasi,tipe");
		if ($tporg[$unit] == 'HOLDING' || $tporg[$unit] == 'KANWIL') {
			$tipespk = 'PUSAT';
		} else {
			$tipespk = 'LOKAL';
		}

		$str = "SELECT a.noinvoice, a.nopo, a.kodesupplier as koderekanan, a.tipeinvoice, sum(b.jumlah) as jumlah, b.noakun FROM keu_tagihan_vw a LEFT JOIN keu_jurnaldt_vw b ON a.nopo=b.noreferensi WHERE a.tipeinvoice in ('bas','batr') AND a.unit = '" . $unit . "'" . $where . " AND b.jumlah > 0 AND a.posting='1' GROUP BY a.noinvoice, a.nopo";
		$resx = fetchdata($str);
		foreach ($resx as $bar) {
			$dataKontraktor[$bar['noinvoice']][$bar['nopo']]['tipeinvoice'] = $bar['tipeinvoice'];
			$dataKontraktor[$bar['noinvoice']][$bar['nopo']]['koderekanan'] = $bar['koderekanan'];
			$dataKontraktor[$bar['noinvoice']][$bar['nopo']]['noakun'] = $bar['noakun'];
			$dataKontraktor[$bar['noinvoice']][$bar['nopo']]['jumlah'] = $bar['jumlah'];
		}

		$str = "SELECT a.noinvoice, a.nopo, a.kodesupplier as koderekanan, a.tipeinvoice, sum(b.nilai) as nilai, b.noakun FROM keu_tagihanht a LEFT JOIN keu_tagihandt b ON a.noinvoice=b.noinvoice AND a.nopo=b.nopo WHERE a.tipeinvoice in ('k','r') AND a.unit = '" . $unit . "'" . $where . " AND a.posting='1' GROUP BY a.noinvoice, a.nopo";
		$resx = fetchdata($str);
		foreach ($resx as $bar) {
			$dataKontraktor[$bar['noinvoice']][$bar['nopo']]['tipeinvoice'] = $bar['tipeinvoice'];
			$dataKontraktor[$bar['noinvoice']][$bar['nopo']]['koderekanan'] = $bar['koderekanan'];
			$dataKontraktor[$bar['noinvoice']][$bar['nopo']]['noakun'] = $bar['noakun'];
			$dataKontraktor[$bar['noinvoice']][$bar['nopo']]['jumlah'] = $bar['nilai'];
		}

		$subtotal = array();
		foreach ($dataKontraktor as $noinv => $bar) {
			foreach ($bar as $nopo => $val) {
				$nmTipe = makeOption($dbname, "keu_5jenistagihan", "kode,namajenis", "kode='" . $val['tipeinvoice'] . "'");

				$noxz++;
				$subtotal['invoice'] += $val['jumlah'];
				$stream .= "<tr class='rowcontent input'>";
				$stream .= "<td align='center'>" . $noxz . "</td>";
				$stream .= "<td align='left' id='invoice_" . $noxz . "' class='noinvoice'>" . $noinv . "</td>";
				$stream .= "<td align='left' id='notransaksikontraktor_" . $noxz . "' class='nopo'>" . $nopo . "</td>";
				$stream .= "<td align='left'>" . $nmTipe[$val['tipeinvoice']] . " <input type='hidden' class='tipeinvoice' value='" . $val['tipeinvoice'] . "' /></td>";
				$stream .= "<td align='left' id='terminkontraktor_" . $noxz . "' class='termin'>" . $val['termin'] . "</td>";
				$stream .= "<td align='left'><input type='hidden' id='supplier_" . $noxz . "' value='" . $val['koderekanan'] . "' class='kodesupplier' />" . $arrnmsupp[$val['koderekanan']] . "</td>";
				$stream .= "<td align='left' id='kegiatan_" . $noxz . "'>" . $val['noakun'] . " - " . getNamaAkun($val['noakun']) . " <input type='hidden' class='kodekegiatan' value='" . $val['noakun'] . "'/></td>";
				$stream .= "<td align='right' id='jlhkontraktor_" . $noxz . "' class='nilaiinvoice'>" . number_format($val['jumlah'], 2) . "</td>";
				$stream .= "<td align='right'><input type='checkbox' id=checklist_" . $noxz . " class='checkKontrak'></td>";
				$stream .= "</tr>";
			}
		}
		//baris biaya lain
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center colspan='7'>Total</td>";
		$stream .= "<td align=right>" . number_format($subtotal['invoice'], 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";

		//tombol simpan
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center colspan=10><button class=mybutton onclick=savekontraktor()>" . $_SESSION['lang']['save'] . "</button></td>";
		$stream .= "</tr>";
		echo $stream;
		break;
	case 'listkontraktor':
		$border = 0;
		if ($tipex == "excel") {
			$border = 1;
		}

		$stream .= "<fieldset><legend><b>List Data " . $_SESSION['lang']['kontraktor'] . "</b></legend>
                <table cellpading=1 cellspacing=1 border={$border} class=sortable>
                    <thead>
						<tr>
							<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center>" . $_SESSION['lang']['noinvoice'] . "</td>
							<td align=center>" . $_SESSION['lang']['noreferensi'] . "</td>
							<td align=center>" . $_SESSION['lang']['tipe'] . "</td>
							<td align=center>Termin</td>
							<td align=center>Nama Pihak Ketiga</td>
							<td align=center>Kegiatan</td>
							<td align=center>Nilai Invoice</td>";
		if ($tipex != "excel") {
			$stream .= "<td align=center>" . $_SESSION['lang']['action'] . "</td>";
		}
		$stream .= "</tr></thead>";
		##ambil data yang udah ada
		// $str = "SELECT a.*, c.koderekanan, b.kodekegiatan FROM ".$dbname.".keu_pdodt a
		//         JOIN ".$dbname.".log_baspk b ON a.nodocument = b.notransaksi
		//         JOIN ".$dbname.".lgl_pengajuanspkht c ON a.nodocument = c.notransaksi
		//         where nopdo='".$nopdo."' and a.notransaksi like '%KONTRAKTOR%'";
		// $str = "SELECT a.*, c.kodesupplier AS koderekanan, b.kodekegiatan FROM ".$dbname.".keu_pdodt AS a
		// JOIN ".$dbname.".log_baspk b ON a.nodocument = b.notransaksi
		// JOIN ".$dbname.".keu_tagihanht AS c ON a.nodocument = c.nopo
		// WHERE a.nopdo = '".$nopdo."' AND a.notransaksi LIKE '%KONTRAKTOR%'";

		// $str = "SELECT * from ".$dbname.".keu_pdodt where notransaksi LIKE '%KONTRAKTOR%' and nopdo = '".$nopdo."'";
		$str = "SELECT b.*, c.tipeinvoice from " . $dbname . ".keu_pdoht a LEFT JOIN " . $dbname . ".keu_pdodt b on a.nopdo=b.nopdo LEFT JOIN " . $dbname . ".keu_tagihanht c on b.noinvoice=c.noinvoice where b.notransaksi = '{$nokontraktor}' and a.nopdo = '" . $nopdo . "' and c.tipeinvoice in ('k','r','bas','batr') GROUP BY noinvoice";
		$hsl = fetchdata($str);
		$total = array();
		if (count($hsl) > 0) {
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$nmTipe = makeOption($dbname, "keu_5jenistagihan", "kode,namajenis", "kode='" . $bar['tipeinvoice'] . "'");

				$no++;
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center >" . $bar['nourut'] . "</td>";
				$stream .= "<td align=left >" . $bar['noinvoice'] . "</td>";
				$stream .= "<td align=left >" . $bar['nodocument'] . "</td>";
				$stream .= "<td align=left >" . $nmTipe[$bar['tipeinvoice']] . "</td>";
				$stream .= "<td align=left >" . $bar['rincian'] . "</td>";
				$stream .= "<td align=left >" . $arrnmsupp[$bar['kodesupplier']] . "</td>";
				$stream .= "<td align=left >" . $bar['noakun'] . " - " . getNamaAkun($bar['noakun']) . "</td>";
				$stream .= "<td align=right >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";

				if ($tipex != "excel") {
					$stream .= "<td align=center><img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deletekontraktor('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nodocument'] . "','" . $bar['nourut'] . "');\"></td>";
				}
				$next = $no;
				$total['rupiah'] += $bar['rupiahdiajukan'];
			}
		} else {
			$stream .= "<tr class=rowcontent><td colspan=10 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}

		$stream .= "<tr class=rowcontent>
						<td colspan='7' align=center style=font-weight:bold>" . $_SESSION['lang']['total'] . "</td>
						<td align=right>" . number_format($total['rupiah'], 2) . "</td>";
		if ($tipex != "excel") {
			$stream .= "<td></td>";
		}
		$stream .= "</tr>";

		$stream .= "</table></fieldset>";
		if ($tipex != "excel") {
			echo $stream;
		} else {
			$nop = "PDO Kontraktor.xls";
			$xls = new HtmlExcel();
			$borderStyle = 'border: 1px solid #000;';
			$css = "table { border-collapse: collapse; } th, td { $borderStyle padding: 5px; }";
			$xls->setCss($css);
			$xls->addSheet('PDO Kontraktor', $stream);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
		break;

	########################################################
	#############  T A B   K O N T R A K T O R  ############
	########################################################
	case 'simpankontraktor':

		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $nokontraktor . "' and nourut='" . $idpdo . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		if ($akhir == 'iya') {
			$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`,`rupiah`)
							VALUES ('" . $nopdo . "', '" . $nokontraktor . "', '" . $idpdo . "','" . $byrlain . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$strcek = "select * from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "'";
			$rescek = fetchData($strcek);
			if (count($rescek) == 0) {
				$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `kodeorg`, `periode`, `sesi`, `updateby`)
				VALUES ('" . $nopdo . "', '" . $unit . "', '" . $per . "','" . $sesi . "','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			if ($checklist == '1') {
				$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `nodocument`, `rincian`, `rupiah`, `nilaidpp`, `kegiatan`, `kodesupplier`, `noinvoice`)
					VALUES ('" . $nopdo . "', '" . $nokontraktor . "', '" . $idpdo . "', '" . $nobapp . "', '" . $termin . "','" . $jumlahkontraktorx . "', '" . $dpp . "', '" . $kegiatan . "', '" . $supplier . "', '" . $noinvoice . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal !: " . $e->getMessage() . "\n";
					die();
				}
			}
		}

		break;
	case 'nokontraktor':
		$nokontraktor = generateKodeTransaksi($unit, $per, "KTRK");
		echo $nokontraktor;
		break;
	case 'detailsupplier':
		$stream = "";
		$stream .= "<fieldset><legend><b>Detail Input</b></legend>";
		$stream .= "
				Lakukan checklist dikolom aksi untuk menandakan PO yang akan diajukan di PDO

                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                    <thead>
                        <tr>
                            <td align=center rowspan=2 >" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center>" . $_SESSION['lang']['noinvoice'] . "</td>
							<td align=center>" . $_SESSION['lang']['nopo'] . "</td>
							<td align=center>" . $_SESSION['lang']['tipe'] . "</td>
							<td align=center>Nomor GRN</td>
							<td align=center>Nama Pihak Ketiga</td>
							<td align=center>Kegiatan</td>
							<td align=center>Nilai Invoice</td>
							<td align=center>" . $_SESSION['lang']['action'] . " <br/> <input type='checkbox' id='checkAll' onclick=\"toggleCheck(this, 'checkSupplier')\" /></td>
                        </tr>
                    </thead>";
		$where = " AND left(tanggal,7) like '" . $per . "%'";

		$str = selectQuery($dbname, "keu_tagihan_vw", "*", "tipeinvoice in ('p') AND unit = '" . $unit . "'" . $where . " AND posting='1' AND notransaksi != '' GROUP BY noinvoice, nopo, notransaksi");
		$resx = fetchdata($str);
		foreach ($resx as $bar) {
			// Cek jika PO itu non invent atau bukan
			$tipePO = makeOption($dbname, "log_poht", "nopo,tipepo", "nopo='{$bar['nopo']}'");
			if ($tipePO[$bar['nopo']] != "PO") {
				$qJurnalGRN = selectQuery($dbname, "keu_jurnaldt_vw", "noakun", "noreferensi='{$bar['notransaksi']}' AND jumlah > 0 AND nodok='{$bar['nopo']}'");
				$rJurnalGRN = fetchData($qJurnalGRN);
				foreach ($rJurnalGRN as $val) {
					$dataSupplier[$bar['noinvoice']][$bar['nopo']][$bar['notransaksi']]['noakun'] = $val['noakun'];
				}
			} else {
				$dataSupplier[$bar['noinvoice']][$bar['nopo']][$bar['notransaksi']]['noakun'] = $bar['noakun'];
			}
			$dataSupplier[$bar['noinvoice']][$bar['nopo']][$bar['notransaksi']]['tipeinvoice'] = $bar['tipeinvoice'];
			$dataSupplier[$bar['noinvoice']][$bar['nopo']][$bar['notransaksi']]['koderekanan'] = $bar['kodesupplier'];
			$dataSupplier[$bar['noinvoice']][$bar['nopo']][$bar['notransaksi']]['jumlah'] = $bar['nilai'];
		}
		// echo "<pre>"; print_r($dataSupplier); exit("Warning");

		$subtotal = array();
		foreach ($dataSupplier as $noinv => $bar) {
			foreach ($bar as $nopo => $barx) {
				foreach ($barx as $nogrn => $val) {
					$nmTipe = makeOption($dbname, "keu_5jenistagihan", "kode,namajenis", "kode='" . $val['tipeinvoice'] . "'");

					$noxz++;
					$subtotal['invoice'] += $val['jumlah'];
					$stream .= "<tr class='rowcontent input'>";
					$stream .= "<td align='center'>" . $noxz . "</td>";
					$stream .= "<td align='left' id='invoice_" . $noxz . "' class='noinvoice'>" . $noinv . "</td>";
					$stream .= "<td align='left' id='notransaksisupplier_" . $noxz . "' class='nopo'>" . $nopo . "</td>";
					$stream .= "<td align='left'>" . $nmTipe[$val['tipeinvoice']] . " <input type='hidden' class='tipeinvoice' value='" . $val['tipeinvoice'] . "' /></td>";
					$stream .= "<td align='left' class='nogrn'>" . $nogrn . "</td>";
					$stream .= "<td align='left'><input type='hidden' id='supplier_" . $noxz . "' value='" . $val['koderekanan'] . "' class='kodesupplier' />" . $arrnmsupp[$val['koderekanan']] . "</td>";
					$stream .= "<td align='left' id='kegiatan_" . $noxz . "'>" . $val['noakun'] . " - " . getNamaAkun($val['noakun']) . " <input type='hidden' class='kodekegiatan' value='" . $val['noakun'] . "'/></td>";
					$stream .= "<td align='right' id='jlhsupplier_" . $noxz . "' class='nilaiinvoice'>" . number_format($val['jumlah'], 2) . "</td>";
					$stream .= "<td align='right'><input type='checkbox' id=checklist_" . $noxz . " class='checkSupplier'></td>";
					$stream .= "</tr>";
				}
			}
		}
		//baris biaya lain
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center colspan='7'>Total</td>";
		$stream .= "<td align=right>" . number_format($subtotal['invoice'], 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";

		//tombol simpan
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center colspan=10><button class=mybutton onclick=savesupplier()>" . $_SESSION['lang']['save'] . "</button></td>";
		$stream .= "</tr>";
		echo $stream;
		break;
	case 'listsupplier':
		$stream .= "<fieldset><legend><b>List Data " . $_SESSION['lang']['supplier'] . "</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                    <thead>
						<tr>
							<td align=center rowspan=2 >" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center>" . $_SESSION['lang']['noinvoice'] . "</td>
							<td align=center>" . $_SESSION['lang']['nopo'] . "</td>
							<td align=center>" . $_SESSION['lang']['tipe'] . "</td>
							<td align=center>Nomor GRN</td>
							<td align=center>Nama Pihak Ketiga</td>
							<td align=center>Kegiatan</td>
							<td align=center>Nilai Invoice</td>
							<td align=center>" . $_SESSION['lang']['action'] . "</td>
						</tr>
                    </thead>";
		$str = "SELECT b.*, c.tipeinvoice from " . $dbname . ".keu_pdoht a LEFT JOIN " . $dbname . ".keu_pdodt b on a.nopdo=b.nopdo LEFT JOIN " . $dbname . ".keu_tagihanht c on b.noinvoice=c.noinvoice where b.notransaksi = '{$nosupplier}' and a.nopdo = '" . $nopdo . "' and c.tipeinvoice in ('p') GROUP BY noinvoice";
		$hsl = fetchdata($str);
		$total = array();
		if (count($hsl) > 0) {
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$nmTipe = makeOption($dbname, "keu_5jenistagihan", "kode,namajenis", "kode='" . $bar['tipeinvoice'] . "'");

				$no++;
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center >" . $bar['nourut'] . "</td>";
				$stream .= "<td align=left >" . $bar['noinvoice'] . "</td>";
				$stream .= "<td align=left >" . $bar['nodocument'] . "</td>";
				$stream .= "<td align=left >" . $nmTipe[$bar['tipeinvoice']] . "</td>";
				$stream .= "<td align=left >" . $bar['rincian'] . "</td>";
				$stream .= "<td align=left >" . $arrnmsupp[$bar['kodesupplier']] . "</td>";
				$stream .= "<td align=left >" . $bar['kegiatan'] . " - " . getNamaAkun($bar['kegiatan']) . "</td>";
				$stream .= "<td align=right >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";
				$stream .= "<td align=center><img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deletesupplier('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nodocument'] . "','" . $bar['nourut'] . "');\"></td>";
				$next = $no;
				$total['rupiah'] += $bar['rupiahdiajukan'];
			}
		} else {
			$stream .= "<tr class=rowcontent><td colspan=10 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}

		$stream .= "<tr class=rowcontent>
						<td colspan='7' align=center style=font-weight:bold>" . $_SESSION['lang']['total'] . "</td>
						<td align=right>" . number_format($total['rupiah'], 2) . "</td>
						<td></td>
					  </tr>";

		$stream .= "</table></fieldset>";
		echo $stream;
		break;
	case 'loaddata':
		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0) {
				$page = 0;
			}
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);
		$where = "";

		if ($thnsch != '') {
			$where .= " and periode like '" . $thnsch . "%' ";
		}
		if ($notransaksisch != '') {
			$where .= " and notransaksi like '%" . $notransaksisch . "%' ";
		}
		if ($sesisch != '') {
			$where .= " and sesi='" . $sesisch . "' ";
		}
		if ($persch != '') {
			$where .= " and periode='" . $persch . "' ";
		}

		if ($kodeorgsch != '') {
			$where .= " and kodeorg='" . $kodeorgsch . "' ";
		}

		# Setiap load data hapus transaksi abnormal
		$cekTransAbnormal = getCountRows($dbname, "keu_pdoht", "nopdo LIKE '%//%'");
		if ($cekTransAbnormal > 0) {
			$deleteTransAbnormal = deleteQuery($dbname, "keu_pdoht", "nopdo LIKE '%//%'");
			$owlPDO->exec($deleteTransAbnormal);
		}

		$listPdo = ["UPAH", "KAS", "HTGK", "KTRK", "PJD", "OTH"];
		$encodedListPdo = htmlspecialchars(json_encode($listPdo), ENT_QUOTES, 'UTF-8');

		$str = "select count(*) as jmlhrow from " . $dbname . ".keu_pdoht where kodeorg in (" . getOrgDetail(2) . ") " . $where . " group by nopdo  ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$jlhbrs = owlBaris($res);
		$no = 0;
		$no = $maxdisplay;
		$str = "SELECT * from " . $dbname . ".keu_pdoht where kodeorg in (" . getOrgDetail(2) . ") " . $where . " group by nopdo order by nopdo desc  limit " . $offset . "," . $limit . " ";
		$tab = "";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$no += 1;

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
						WHERE notransaksi = '" . $bar['nopdo'] . "' AND " . $whereapp . "
						ORDER BY level " . $order . " LIMIT 1";
				$res = fetchdata($str);
				$statusapp = $ket . "<br> (" . $res[0]['namakaryawan'] . ")";
			}

			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $bar['kodeorg'] . "'");
			$tab .= "<tr class='rowcontent'>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=left>" . $bar['nopdo'] . "</td>";
			$tab .= "<td align=left>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
			$tab .= "<td align=center>" . $bar['periode'] . "</td>";
			$tab .= "<td align=center>" . $arSesi[$bar['sesi']] . "</td>";
			$tab .= "<td valign=top align=center><label style='cursor:pointer;color:blue' onclick=\"gethistoriapproval('" . $bar['nopdo'] . "',event,'PDO')\">" . $statusapp . "</label></td>";
			$tab .= "
            <td align=center>";
			if ($bar['posting'] == 1) {
				$tab .= "
                    &nbsp;&nbsp;<img src='images/skyblue/posted.png' class='zImgOffBtn' title='Posted'>
                    &nbsp;&nbsp;<img src='images/skyblue/zoom.png' class='zImgBtn' title='Detail'
                    onclick=\"detail('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $bar['sesi'] . "','html','event');\">
                    &nbsp;&nbsp;<img src='images/pdf.jpg' class='resicon' title='PDF'
                    onclick=\"detailpdf('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','pdf','event');\">
                    &nbsp;&nbsp;<img src='images/excel.jpg' class='resicon' title='MS.Excel'
                    onclick=\"detailexcel('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','excel','event');\">
                    &nbsp;&nbsp;<img src='images/excel.jpg' class='resicon' title='MS.Excel'
                    onclick=\"detailexcelAll('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','excel', '" . $encodedListPdo . "');\">
                ";
			} else if ($bar['posting'] == 0 || $bar['posting'] == 3) { #and $bar['approval']!=1 and $bar['approval']!=9
				$tab .= "
                    &nbsp;&nbsp;<img src='images/application/application_edit.png' class='zImgBtn' title='Edit'
                        onclick=\"edit('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $bar['sesi'] . "');\">
                    &nbsp;&nbsp;<img src='images/application/application_delete.png' class='zImgBtn' title='Delete'
                        onclick=\"deletehead('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "');\">
                    &nbsp;&nbsp;<img src='images/skyblue/submit.jpg' class='resicon' height='30' title='Ajukan ???'
                        onclick=\"form_ajukan('" . $bar['nopdo'] . "');\">
                    &nbsp;&nbsp;<img src='images/skyblue/zoom.png' class='zImgBtn' title='Detail'
                        onclick=\"detail('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $bar['sesi'] . "','html','event');\">
                    &nbsp;&nbsp;<img src='images/pdf.jpg' class='resicon' title='PDF'
                        onclick=\"detailpdf('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','pdf','event');\">
                    &nbsp;&nbsp;<img src='images/excel.jpg' class='resicon' title='MS.Excel'
                        onclick=\"detailexcel('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','excel','event');\">
                    &nbsp;&nbsp;<img src='images/excel.jpg' class='resicon' title='MS.Excel'
                        onclick=\"detailexcelAll('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','excel','" . $encodedListPdo . "');\">
                ";

				$tab .= "&nbsp;&nbsp;<img src=images/upload-2-xxl.png class=zImgBtn title=Upload onclick=\"showupload('event','" . $bar['nopdo'] . "')\">";
			} else if ($bar['posting'] == 9 || $bar['posting'] == 2) { # and $bar['approval']==9
				$tab .= "
                    &nbsp;&nbsp;<img src='images/skyblue/zoom.png' class='zImgBtn' title='Detail'
                        onclick=\"detail('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $bar['sesi'] . "','html','event');\">
                    &nbsp;&nbsp;<img src='images/pdf.jpg' class='resicon' title='PDF'
                        onclick=\"detailpdf('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','pdf','event');\">
                    &nbsp;&nbsp;<img src='images/excel.jpg' class='resicon' title='MS.Excel'
                        onclick=\"detailexcel('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','excel','event');\">
                    &nbsp;&nbsp;<img src='images/excel.jpg' class='resicon' title='MS.Excel'
                        onclick=\"detailexcelAll('" . $bar['nopdo'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','excel','" . $encodedListPdo . "');\">
                ";
			}
			$tab .= "</td>";
			$tab .= "</tr>";
		}
		$totrows = ceil($jlhbrs / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}
		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
		}
		$footd = "
            <tr><td colspan=7 align=center>
            <button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>
            <button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
		echo $tab . "####" . $footd;
		break;
	case 'form_ajukan':
		$str1 = "select * from " . $dbname . ".keu_pdoht where nopdo='" . $notransaksi . "'";
		$res1 = fetchdata($str1);
		$kodeunit = $res1[0]['kodeorg']; //ambil unit sesuai invoice

		## APPROVAL DINAMIS SESUAI SETUP##
		$jenisapprv = 'PDO';

		$tab .= "<table cellspacing=1 border=0 class=sortable cellpadding=5 align=center>";
		$tab .= "<tr>
					<td colspan=3 style='font-weight:bold' align=center><h3>Permintaan Dana Operasional (PDO)</h3></td>
				</tr>";
		$tab .= "
		<tr class=rowcontent>
			<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
			<td width=5px>:</td>
			<td id=notran_aju>" . $notransaksi . "</td>
		</tr>";

		//APPROVAL PAKE NANGKOELIB Paling simpel

		$jumlahlevel = getCountApproval($jenisapprv, $kodeunit);

		if ($jumlahlevel > 0) {
			$karyiduser = makeOption($dbname, 'user', 'namauser,karyawanid', 'status=1');
			for ($i = 1; $i <= $jumlahlevel; $i++) {
				$arrApprv = listApprove($i, $jenisapprv, $kodeunit);
				$optApprv = "";
				foreach ($arrApprv as $row) {
					$optApprv .= "<option value='{$row['karyawanid']}'>{$row['nama']}</option>";
				}
				$tab .= "
                <tr class='rowcontent'>
                    <td>APPROVAL KE-{$i} </td>
                    <td style='width:5px'>:</td>
                    <td><select id='kepada{$i}' style='width:200px'>{$optApprv}</select></td>
                </tr>";
			}

			$tab .= "
			<tr class=rowcontent>
				<td hidden><input id=jenisapprv style=display:none value=" . $jenisapprv . "></td><td><input id=numrow style=display:none value='1'></td>
				<td align=left></td>
				<td align=left>
                    <button class=mybutton onclick=saveajukan('" . $notransaksi . "','{$jenisapprv}','" . $jumlahlevel . "')>" . $_SESSION['lang']['diajukan'] . "</button>
                </td>
			</tr>";
		} else {
			exit('warning : Persetujuan ' . $jenisapprv . ' untuk unit ' . $kodeunit . ' belum di setting. Silahkan setting pada menu Setup > Persetujuan.');
		}

		$tab .= "</table>";

		echo $tab;
		break;
	case 'ajukan':
		$notransaksi = checkPostGet('notransaksi', '');
		$kepada = checkPostGet('persetujuan', '');
		$jenisapprv = checkPostGet('jenisapprv', '');
		$maxaproval = checkPostGet('maxaproval', '');

		for ($i = 1; $i <= count($kepada); $i++) {
			if ($kepada[$i] == '') {
				exit("Warning: Persetujuan " . $i . " belum dipilih.");
			}
		}

		//update flag menjadi 9
		$str2 = "update " . $dbname . ".keu_pdoht set posting='9' where nopdo = '" . $notransaksi . "'";
		try {
			$owlPDO->exec($str2);
		} catch (PDOException $e) {
			echo "DB Header Error: " . addslashes($e->getMessage());
		}

		//cek apakah sudah terdapat approval sebelum jika ada delete
		$sql = "select * from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' ";
		$hsl = fetchData($sql);
		if (count($hsl) > 0) {
			$string = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "'";
			try {
				$owlPDO->exec($string);
			} catch (PDOException $e) {
				echo "DB Header Error: " . addslashes($e->getMessage());
			}
		}

		//insert ke table approval
		for ($i = 1; $i <= $maxaproval; $i++) {
			$str = "INSERT INTO " . $dbname . ".approval
            		(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
                   	VALUES
                   	('" . $notransaksi . "','{$jenisapprv}','" . $i . "','" . $kepada[$i] . "','0','','','0000-00-00 00:00:00')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				$str2 = "update " . $dbname . ".keu_pdoht set posting='0' where nopdo = '" . $notransaksi . "'";
				try {
					$owlPDO->exec($str2);
				} catch (PDOException $e) {
					echo "DB Header Error: " . addslashes($e->getMessage());
				}
			}
		}
		break;

	case 'deletehead':
		$str = "delete from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and kodeorg='" . $unit . "' and periode='" . $per . "'  ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'posting':

		$jumlahrupiahbbm = 0;
		$jumlahrupiahpjd = 0;
		$jumlahrupiahkas = 0;
		$strbrs = "select count(*) as jumlahbaris,noakun from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and left(tanggal,7)='" . $per . "' and notransaksi like '%KAS%' and noakun in ('10403','10404') group by noakun";
		// exit('warning : '.$strbrs);
		$resbrs = $owlPDO->query($strbrs) or die(print " Gagal: " . PDOException::getMessage());
		$resbrs->setFetchMode(PDO::FETCH_ASSOC);
		while ($barbrs = $resbrs->fetch()) {
			if ($barbrs['jumlahbaris'] > 0) {
				if ($bar['noakun'] == '10403') {
					$str = "select sum(rupiah) as jumlahrupiahbbm from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and left(tanggal,7)='" . $per . "' and notransaksi like '%BBM%'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar = $res->fetch();
					$jumlahrupiahbbm = floatval($bar['jumlahrupiahbbm']);

					$str = "select sum(rupiah) as jumlahrupiahkas from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and left(tanggal,7)='" . $per . "' and notransaksi like '%KAS%' and noakun='10403'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar = $res->fetch();
					$jumlahrupiahkas = floatval($bar['jumlahrupiahkas']);

					if ($jumlahrupiahbbm > 0 && $jumlahrupiahkas == 0) {
						exit('warning : akun bbm pada tab kas belum diinput.');
					}
					if ($jumlahrupiahkas < $jumlahrupiahbbm) {
						exit('warning : Jumlah bbm pada tab kas < tab bahan bakar.');
					}
				}

				if ($bar['noakun'] == '10404') {
					$str = "select sum(rupiah) as jumlahrupiahbbm from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and left(tanggal,7)='" . $per . "' and notransaksi like '%PJD%'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar = $res->fetch();
					$jumlahrupiahbbm = floatval($bar['jumlahrupiahbbm']);

					$str = "select sum(rupiah) as jumlahrupiahkas from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and left(tanggal,7)='" . $per . "' and notransaksi like '%KAS%' and noakun='10404'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar = $res->fetch();
					$jumlahrupiahkas = floatval($bar['jumlahrupiahkas']);

					if ($jumlahrupiahbbm > 0 && $jumlahrupiahkas == 0) {
						exit('warning : akun pjd pada tab kas belum diinput.');
					}
					if ($jumlahrupiahkas < $jumlahrupiahbbm) {
						exit('warning : Jumlah pjd pada tab kas < tab perjalanan dinas.');
					}
				}
			}
		}

		#ambil data HT
		$nama = array('KAS' => $_SESSION['lang']['kas'], 'LNN' => $_SESSION['lang']['lain'], 'PJD' => $_SESSION['lang']['perdin']);
		$str = "select distinct tipepdo from " . $dbname . ".keu_pdo_vw where  nopdo='" . $nopdo . "' and tipepdo in ('KAS','LNN','PJD')";
		$res = fetchData($str);
		foreach ($res as $key => $val) {
			$strcek = "select * from " . $dbname . ".listfileupload where  notransaksi like '" . $nopdo . "%" . $val['tipepdo'] . "%'";
			$rescek = fetchData($strcek);
			if (count($rescek) == 0) {
				exit('warning: Tab ' . $nama[$val['tipepdo']] . ' wajib upload file');
			}
		}

		$str = "update  " . $dbname . ".keu_pdoht set posting='1',postingby='" . $_SESSION['standard']['userid'] . "'
				,postingtime=now() where nopdo='" . $nopdo . "' and kodeorg='" . $unit . "' and periode='" . $per . "'  ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'listbbm':
		$stream .= "<fieldset><legend><b>List " . $_SESSION['lang']['bbm'] . "</b></legend >
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center width=30px>" . $_SESSION['lang']['nourut'] . "</td>
					<td align=center>" . $_SESSION['lang']['noakun'] . "</td>
					<td align=center>" . $_SESSION['lang']['aruskas'] . "</td>
					<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
					<td align=center>" . $_SESSION['lang']['satuan'] . "</td>
					<td align=center>" . $_SESSION['lang']['vhc_jumlah_bbm'] . "</td>
					<td align=center>" . $_SESSION['lang']['total'] . "</td>
					<td align=center width=30px>" . $_SESSION['lang']['action'] . "</td>
				</tr>
			</thead>";

		$str = "select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%BBM%' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optNmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['rincian'] . "'");
			$no += 1;
			$stream .= "<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
                        <td align=center>" . $bar['noakunkas'] . " - " . @$arrnmakun[$bar['noakunkas']] . "</td>
                        <td align=left>" . $bar['noakun'] . " - " . @$arrnmaruskas[$bar['noakun']] . "</td>
                        <td align=left>" . $optNmKar[$bar['rincian']] . "</td>
                        <td align=center>" . $bar['satuan'] . "</td>
                        <td align=right>" . @number_format($bar['fisik']) . "</td>
                        <td align=right>" . @number_format($bar['rupiah']) . "</td>
                        <td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn title='Edit'
                                onclick=\"editbbm('" . $nopdo . "','" . $notranbbm . "','" . $bar['noakunkas'] . "','" . $bar['rekeningbank'] . "');\">
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete'
                                onclick=\"deletebbm('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\">
                        </td>
                </tr>";
		}
		echo $stream;
		break;

	case 'detailbbm':

		if ($noakunbbm == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunbbm == '1110101') {
			if ($rekeningbankbbm == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		$stream = "";
		$stream .= "<fieldset><legend><b>Detail " . $_SESSION['lang']['hutang'] . "</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr>
                        <td align=center width=30px >" . $_SESSION['lang']['nourut'] . "</td>
                        <td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
                        <td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['vhc_jumlah_bbm'] . "</td>
                        <td align=center>" . $_SESSION['lang']['pembayaran'] . "</td>
						<td align=center rowspan=2 width=30px >" . $_SESSION['lang']['action'] . "<br>
							<input type=checkbox id=cekallbbm onclick=cekallbbm()>
						</td>
                    </tr>
                </thead><tbody id=contentdetailbbm>";

		$str = " select nodocument from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranbbm . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$posave[$bar['nodocument']] = 1;
		}

		$str = "select a.notransaksi,a.karyawanid,b.jlhbbm, b.hargatotal as dibayar from " . $dbname . ".sdm_penggantiantransport a
			left join " . $dbname . ".sdm_penggantiantransportdt b on a.notransaksi=b.notransaksi
			where a.kodeorg='" . $unit . "' and a.periode='" . $per . "'";
		$res = fetchData($str);
		$no = 0;
		foreach ($res as $key => $val) {
			if (@$posave[$val['notransaksi']] == 1) {
				$cek = "checked=true";
			} else {
				$cek = "";
			}
			$optNmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $val['karyawanid'] . "'");
			$no += 1;
			$stream .= "<tr class=rowcontent id=rowbbm" . $no . ">";
			$stream .= "<td align=center>" . $no . "</td>";
			$stream .= "<td align=left id=notransaksibbm" . $no . ">" . $val['notransaksi'] . "</td>";
			$stream .= "<td align=center style='display:none' id=karyawanid" . $no . ">" . $val['karyawanid'] . "</td>";
			$stream .= "<td align=left>" . $optNmKar[$val['karyawanid']] . "</td>";
			$stream .= "<td align=right id=jlhbbm" . $no . ">" . @number_format($val['jlhbbm']) . "</td>";
			$stream .= "<td align=right id=pembayaran" . $no . ">" . @number_format($val['dibayar']) . "</td>";
			$stream .= "<td align=center><input type=checkbox id=cekbbm" . $no . " " . $cek . "></td>";
			$stream .= "</tr>";
		}
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td colspan=6 align=right >
			<button class=mybutton onclick=saveallbbm(" . $no . ");>" . $_SESSION['lang']['proses'] . "</button></td>";
		$stream .= "</tr>";
		echo $stream;
		break;

	case 'savebbm':

		if ($noakunbbm == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunbbm == '1110101') {
			if ($rekeningbankbbm == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		if ($cekbbm == 1) {
			#cek apakah HT sudah di-insert
			$str = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='BBM' limit 1";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$cekht = $bar['jumlah'];
			if ($cekht <= 0) {
				$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('" . $nopdo . "', '" . $notranbbm . "', '" . $unit . "', '" . $per . "', 'BBM','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			if ($currRowbbm == 1) {
				##delete 1st
				$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranbbm . "'";
				//exit("error".$str);
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			$optNoAkun = makeOption($dbname, 'keu_5parameterjurnal', 'jurnalid,noakundebet', "jurnalid='PDO01'");
			$noakun = $optNoAkun['PDO01'];

			if ($noakun == '') {
				$noakun = $aruskasbbm;
			}

			$str = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranbbm . "' order by nourut desc limit 1 ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$nourutbaru = $bar['nourut'] + 1;

			$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                    `nodocument`,`tanggal`,`satuan`,`fisik`,`rupiah`,`noakunkas`,`rekeningbank`)
                    VALUES ('" . $nopdo . "', '" . $notranbbm . "', '" . $nourutbaru . "', '" . $noakun . "', '" . $karyawanid . "',
                    '" . $notransaksibbm . "','" . $tglawalper . "','Liter','" . $jlhbbm . "','" . $pembayaran . "','" . $noakunbbm . "','" . $rekeningbankbbm . "')";
			//exit("error".$str);
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranbbm . "' and nodocument='" . $notransaksibbm . "' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;

	case 'deletebbm':
		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranbbm . "' and nourut='" . $nourutbbm . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	#############################################################

	case 'listio':
		$stream .= "<fieldset><legend><b>List Ijin Operasional</b></legend >
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center width=30px>" . $_SESSION['lang']['nourut'] . "</td>
					<td align=center>" . $_SESSION['lang']['noakun'] . "</td>
					<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>
					<td align=center>" . $_SESSION['lang']['jenisbiaya'] . "</td>
					<td align=center>" . $_SESSION['lang']['biaya'] . "</td>
					<td align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
				</tr>
			</thead>";

		$str = "select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%IO%' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optNmVhc = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc', "kodevhc='" . $bar['rincian'] . "'");
			$optNmJns = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $bar['noakun'] . "'");
			$no += 1;
			$stream .= "<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
                        <td align=center>" . @$arrnmakun[$bar['noakunkas']] . "</td>
                        <td align=center>" . $bar['nodocument'] . "</td>
                        <td align=left>" . $bar['rincian'] . "-" . $optNmVhc[$bar['rincian']] . "</td>
                        <td align=left>" . $optNmJns[$bar['noakun']] . "</td>
                        <td align=right>" . @number_format($bar['rupiah']) . "</td>
                        <td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn title='Edit'
                                onclick=\"editio('" . $nopdo . "','" . $notranbbm . "','" . $bar['noakunkas'] . "','" . $bar['rekeningbank'] . "');\">
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete'
                                onclick=\"deleteio('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\">
                        </td>
                </tr>";
		}
		echo $stream;
		break;

	case 'detailio':

		if ($noakunio == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunio == '1110101') {
			if ($rekeningbankio == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		$stream = "";
		$stream .= "<fieldset><legend><b>Detail Ijin Operasional</b></legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr>
                        <td align=center width=30px >" . $_SESSION['lang']['nourut'] . "</td>
                        <td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
                        <td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>
                        <td align=center>" . $_SESSION['lang']['jenisbiaya'] . "</td>
                        <td align=center>" . $_SESSION['lang']['biaya'] . "</td>
						<td align=center rowspan=2 width=30px >" . $_SESSION['lang']['action'] . "<br>
							<input type=checkbox id=cekallio onclick=cekallio()>
						</td>
                    </tr>
                </thead><tbody id=contentdetailio>";

		$str = " select nodocument,rincian from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranio . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$posave[$bar['nodocument']][$bar['rincian']] = 1;
		}

		$str = "select a.notransaksi,a.kodevhc,b.detailvhc,a.jenisbiaya,c.nama_aruskas,a.biaya
			from " . $dbname . ".vhc_byyijinops a
			left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc
			left join " . $dbname . ".keu_5aruskas c on a.jenisbiaya=c.noaruskas
			where a.kodeorg='" . $unit . "' and a.periode='" . $per . "'";
		$res = fetchData($str);
		$no = 0;
		foreach ($res as $key => $val) {
			if (@$posave[$val['notransaksi']][$val['kodevhc']] == 1) {
				$cek = "checked=true";
			} else {
				$cek = "";
			}
			$optNmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $val['karyawanid'] . "'");
			$no += 1;
			$stream .= "<tr class=rowcontent id=rowio" . $no . ">";
			$stream .= "<td align=center>" . $no . "</td>";
			$stream .= "<td align=left id=notransaksiio" . $no . ">" . $val['notransaksi'] . "</td>";
			$stream .= "<td align=center style='display:none' id=kodevhc" . $no . ">" . $val['kodevhc'] . "</td>";
			$stream .= "<td align=center>" . $val['detailvhc'] . "</td>";
			$stream .= "<td align=center style='display:none' id=jenisbiaya" . $no . ">" . $val['jenisbiaya'] . "</td>";
			$stream .= "<td align=center>" . $val['nama_aruskas'] . "</td>";
			$stream .= "<td align=center id=biaya" . $no . ">" . @number_format($val['biaya']) . "</td>";
			$stream .= "<td align=center><input type=checkbox id=cekio" . $no . " " . $cek . "></td>";
			$stream .= "</tr>";
		}
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td colspan=6 align=right >
			<button class=mybutton onclick=saveallio(" . $no . ");>" . $_SESSION['lang']['proses'] . "</button></td>";
		$stream .= "</tr>";
		echo $stream;
		break;

	case 'saveio':

		if ($noakunio == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunio == '1110101') {
			if ($rekeningbankio == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		if ($cekio == 1) {
			#cek apakah HT sudah di-insert
			$str = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='IO' limit 1";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$cekht = $bar['jumlah'];
			if ($cekht <= 0) {
				$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
				VALUES ('" . $nopdo . "', '" . $notranio . "', '" . $unit . "', '" . $per . "', 'IO','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}

			##delete 1st
			$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranio . "' and nodocument='" . $notransaksiio . "' and rincian='" . $kodevhc . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}

			$str = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranio . "' order by nourut desc limit 1 ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$nourutbaru = $bar['nourut'] + 1;

			$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                    `nodocument`,`tanggal`,`rupiah`,`noakunkas`,`rekeningbank`)
                    VALUES ('" . $nopdo . "', '" . $notranio . "', '" . $nourutbaru . "', '" . $jenisbiaya . "', '" . $kodevhc . "',
                    '" . $notransaksiio . "','" . $tglawalper . "','" . $biaya . "','" . $noakunio . "','" . $rekeningbankio . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranio . "' and nodocument='" . $notransaksiio . "' and rincian='" . $kodevhc . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;

	case 'deleteio':
		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranio . "' and nourut='" . $nourutio . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	########################################################

	case 'listpjd':
		$border = 0;
		if ($tipex == "excel") {
			$border = 1;
		}

		$stream .= "<fieldset><legend><b>List " . $_SESSION['lang']['perdin'] . "</b></legend >
			<table cellpading=1 cellspacing=1 border={$border} class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center width=30px>" . $_SESSION['lang']['nourut'] . "</td>
					<td align=center>" . $_SESSION['lang']['unit'] . "</td>
					<td align=center>" . $_SESSION['lang']['periode'] . "</td>
					<td align=center>" . $_SESSION['lang']['noakun'] . "</td>
					<td align=center>" . $_SESSION['lang']['total'] . "</td>
					<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>";
		$stream .= "<td align=center>" . $_SESSION['lang']['action'] . "</td>";
		$stream .= "</tr></thead>";

		$str = "select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%" . $unit . "/PJD/%' order by tanggal desc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$expnotran = explode('/', $bar['notransaksi']);
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $expnotran[1] . "'");
			$optNmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['rincian'] . "'");
			$no += 1;
			$stream .= "<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
                        <td>" . $expnotran[1] . "-" . $optNmOrg[$expnotran[1]] . "</td>
                        <td align=center>" . substr($bar['tanggal'], 0, 7) . "</td>
                        <td align=center>" . $bar['noakunkas'] . " - " . @$arrnmakun[$bar['noakunkas']] . "</td>
                        <td align=right>" . @number_format($bar['rupiah']) . "</td>
                        <td>" . $bar['rincian'] . "</td>";

			if ($per == substr($bar['tanggal'], 0, 7)) {
				$stream .= "<td align=center>
					<img src=images/application/application_edit.png class=zImgBtn title='Edit'
						onclick=\"editpjd('" . $bar['notransaksi'] . "','" . $bar['rupiah'] . "','" . $bar['rincian'] . "','" . $bar['rekeningbank'] . "','" . $bar['noakunkas'] . "');\">
					<img src=images/application/application_delete.png class=zImgBtn title='Delete'
                                onclick=\"deletepjd('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\">
                    <img src=images/addplus.png title='Upload' class=resicon onclick=showupload('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "',event)>
				</td>";
			} else {
				$stream .= "<td align=center><img src=images/addplus.png title='Upload' class=resicon onclick=showupload('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "',event)></td>";
			}

			$stream .= "</tr>";
		}
		echo $stream;
		break;

	case 'insertpjd':

		if ($noakunpjd == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunpjd == '1110101') {
			if ($rekeningbankpjd == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		$str = "select * from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='PJD' and kodeorg='" . $unit . "' limit 1";
		$res = fetchData($str);
		if (count($res) > 0) {
			exit("Gagal, Sudah anda transaksi PDO Perjalanan dinas untuk periode " . $per);
		}

		$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
		VALUES ('" . $nopdo . "', '" . $notranpjd . "', '" . $unit . "', '" . $per . "', 'PJD','" . $_SESSION['standard']['userid'] . "')";
		try {
			$owlPDO->exec($str);

			$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `rincian`, `noakun`,
                `tanggal`, `rupiah`, `noakunkas`, `rekeningbank`)
                VALUES ('" . $nopdo . "', '" . $notranpjd . "', '1', '" . $ketpjd . "', '" . $aruskaspjd . "',
                '" . $tglawalper . "','" . $totalpjd . "','" . $noakunpjd . "','" . $rekeningbankpjd . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'updatepjd':

		if ($noakunpjd == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunpjd == '1110101') {
			if ($rekeningbankpjd == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		$str = "update " . $dbname . ".`keu_pdodt` set rupiah='" . $totalpjd . "',rincian='" . $ketpjd . "',noakunkas='" . $noakunpjd . "',rekeningbank='" . $rekeningbankpjd . "' where nopdo='" . $nopdo . "' and notransaksi='" . $notranpjd . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'deletepjd':
		$str = "delete from " . $dbname . ".`keu_pdoht` where nopdo='" . $nopdo . "' and notransaksi='" . $notranpjd . "'";
		try {
			$owlPDO->exec($str);

			$str = "delete from " . $dbname . ".`keu_pdodt` where nopdo='" . $nopdo . "' and notransaksi='" . $notranpjd . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	######################################################################
	case 'detaillnn':

		$optrek = $optkas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select noakun,namaakun from " . $dbname . ".keu_5akun where noakun in ('1112101','1112102','1110101') and aktif=1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optkas .= "<option value='" . $bar['noakun'] . "'>" . $bar['namaakun'] . "</option>";
		}

		$str = "select * from " . $dbname . ".keu_5akunbank";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$wheredz = " kodebank='" . $bar['namabank'] . "'";
			$optnama = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank', $wheredz);
			$optrek .= "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
		}

		$notranlnn = $explnopdo[0] . '/' . $explnopdo[2] . '/LNN' . '/' . $explnopdo[3] . '/001';
		$stream .= "<fieldset><legend><b>Form Input</b></legend >";
		$stream .= "
            " . $_SESSION['lang']['notransaksi'] . " : <input type=text id=notranlnn disabled value='" . $notranlnn . "' onkeypress=\"return tanpa_kutip(event)\" class=myinputtext>
            " . $_SESSION['lang']['noakun'] . " : <select onchange='getrekeninglnn()' id=noakunlnn style=\"width:150px;\">" . $optkas . "</select>
            " . $_SESSION['lang']['rekening'] . " : <select id=rekeningbanklnn style=\"width:150px;\">" . $optrek . "</select><hr>
            <table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
                    <tr class=rowheader>
                        <td hidden>nourutdb</td>
                        <td align=center>" . $_SESSION['lang']['aruskas'] . "</td>
                        <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['kuantitas'] . "</td>
                        <td align=center>" . $_SESSION['lang']['rupiahsatuan'] . "</td>
                        <td align=center width=100px>" . $_SESSION['lang']['total'] . "</td>
                        <td align=center width=30px>" . $_SESSION['lang']['action'] . "
                            </td>
                    </tr>
                </thead>";
		$stream .= "
                <tr class=rowcontent>
                    <td align=left hidden><input type=text id=nourutlnn onkeypress=\"return tanpa_kutip(event)\" class=myinputtext></td>
                    <td align=left>
						<select id=akunlnn  style=width:150px onchange=getket('lain')>'" . $optaruskas . "'</select>
						<img onclick=\"z.elSearch('akunlnn',event)\" class=resicon src=images/onebit_02.png style=position:relative;top:5px>
					</td>
                    <td align=left><select id=ketlnn style=width:400px >'" . $opt . "'</select></td>
                    <td align=left><select id=satlnn>'" . $optsat . "'</select></td>
                    <td align=right><input type=text id=fisiklnn onkeyup=totallnn() onkeypress='return angka_doang(event)' class=myinputtextnumber   style=width:50px ></td>
                    <td align=center><input type=text id=rupsatlnn onkeyup=totallnn() onkeypress='return angka_doang(event)' class=myinputtextnumber   style=width:90px ></td>
                    <td align=right id=totlnn></td>
                    <td align=center width=30px>
						<img title=" . $_SESSION['lang']['save'] . " class='zImgBtn' onclick='savelnn()' src='images/save.png'>
						</td>
               </tr>
               <input type=hidden id=methodlnn value='savelnn'>";
		$stream .= "</table></fieldset>";
		echo $stream;
		break;

	case 'listlnn':
		$stream .= "<fieldset><legend><b>List Lainnya</b></legend >
                <table cellpading=1 cellspacing=1 border=0 class=sortable style=min-width:940px>
                <thead>
                    <tr class=rowheader>
                        <td align=center width=30px>" . $_SESSION['lang']['nourut'] . "</td>
                        <td align=center>" . $_SESSION['lang']['noakun'] . "</td>
                        <td align=center>" . $_SESSION['lang']['aruskas'] . "</td>
                        <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['kuantitas'] . "</td>
                        <td align=center>" . $_SESSION['lang']['rupiahsatuan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['total'] . "</td>
                        <td align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
                    </tr>
                </thead>";
		$str = "select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%LNN%' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no += 1;
			$stream .= "<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
                        <td align=center>" . $arrnmakun[$bar['noakunkas']] . "</td>
                        <td align=left>" . $bar['noakun'] . " - " . $arrnmaruskas[$bar['noakun']] . "</td>
                        <td align=left>" . $optket[$bar['rincian']] . "</td>
                        <td align=center>" . $bar['satuan'] . "</td>
                        <td align=right>" . @number_format($bar['fisik']) . "</td>
                        <td align=right>" . @number_format($bar['rupiah'] / $bar['fisik']) . "</td>
                        <td align=right>" . @number_format($bar['rupiah']) . "</td>
                        <td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn title='Edit'
                                onclick=\"editlnn('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "',
                                    '" . $bar['noakun'] . "','" . $bar['rincian'] . "','" . $bar['satuan'] . "','" . $bar['fisik'] . "',
                                    '" . $bar['rupiah'] / $bar['fisik'] . "','" . $bar['rupiah'] . "','" . $bar['noakunkas'] . "','" . $bar['rekeningbank'] . "');\">
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete'
                                onclick=\"deletelnn('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\">
                        	<img src=images/addplus.png title='Upload' class=resicon onclick=showupload('" . $bar['nopdo'] . "','" . $bar['notransaksi'] . "','" . $bar['nourut'] . "',event)>
                        </td>
                </tr>";
		}
		echo $stream;
		break;

	case 'savelnn':

		if ($noakunlnn == '') {
			exit('warning : Noakun harus diisi.');
		}

		if ($noakunlnn == '1110101') {
			if ($rekeningbanklnn == '') {
				exit('warning : Jika noakun bank, rekening harus diisi.');
			}
		}

		#cek apakah HT sudah di-insert
		$str = "select count(*) as jumlah from " . $dbname . ".keu_pdoht where nopdo='" . $nopdo . "' and periode='" . $per . "' and tipepdo='LNN' limit 1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$cekht = $bar['jumlah'];
		if ($cekht <= 0) {
			$str = "INSERT INTO " . $dbname . ".`keu_pdoht` (`nopdo`, `notransaksi`, `kodeorg`, `periode`, `tipepdo`,`updateby`)
			VALUES ('" . $nopdo . "', '" . $notranlnn . "', '" . $unit . "', '" . $per . "', 'LNN','" . $_SESSION['standard']['userid'] . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		#cek nourut
		$str = "select nourut from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranlnn . "'"
			. " order by nourut desc limit 1 ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$nourutbaru = $bar['nourut'] + 1;
		$str = "INSERT INTO " . $dbname . ".`keu_pdodt` (`nopdo`, `notransaksi`, `nourut`, `noakun`, `rincian`,
                `tanggal`, `satuan`,`fisik`, `rupiah`,`noakunkas`, `rekeningbank`)
                VALUES ('" . $nopdo . "', '" . $notranlnn . "', '" . $nourutbaru . "', '" . $akunlnn . "', '" . $ketlnn . "',
                '" . $tglawalper . "', '" . $satlnn . "', '" . $fisiklnn . "','" . $totlnn . "', '" . $noakunlnn . "','" . $rekeningbanklnn . "')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'updatelnn':
		$str = "update " . $dbname . ".keu_pdodt set noakun='" . $akunlnn . "',rincian='" . $ketlnn . "',
            tanggal='" . $tglawalper . "',satuan='" . $satlnn . "',fisik='" . $fisiklnn . "',rupiah='" . $totlnn . "',noakunkas='" . $noakunlnn . "',rekeningbank='" . $rekeningbanklnn . "'
            where nopdo='" . $nopdo . "' and notransaksi='" . $notranlnn . "' and nourut='" . $nourutlnn . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'deletelnn':
		$str = "delete from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi='" . $notranlnn . "' "
			. " and nourut='" . $nourutlnn . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'lihatDetail':
		$no = 0;
		$tab = "";

		$tab .= "<fieldset>
            <legend>" . $_SESSION['lang']['list'] . "</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center'>No.</td>
                    <td align='center'>" . $_SESSION['lang']['pdo'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['notransaksi'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['nourut'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['aruskas'] . "</td>
                    <td align='center'>File Type</td>
                    <td align='center'>Filename</td>
                    <td align='center'>Action</td>
                </tr>
                </thead>
                <tbody>";
		$str = "select * from " . $dbname . ".listfileupload where notransaksi like '" . $nopdo . "%" . $tipe . "%' and status='1'";
		if (($tipe == 'PAD') || ($tipe == 'IO')) {
			$sData = "select nodocument from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "' and notransaksi like '%" . $tipe . "%'";
			$rData = fetchData($sData);
			$listNodoc = "";
			if (count($rData) != 0) {
				foreach ($rData as $key => $val) {
					if ($key == 0) {
						$listNodoc = "'" . $val['nodocument'] . "'";
					} else {
						$listNodoc .= ",'" . $val['nodocument'] . "'";
					}
				}
			}
			if ($listNodoc != "") {
				if ($tipe == 'PAD') {
					$str = "select * from " . $dbname . ".listfile_lgl_bansos where notransaksi in (" . $listNodoc . ") and status='1'";
				}
				if ($tipe == 'IO') {
					$str = "select * from " . $dbname . ".listfilebyyijinops where notransaksi in (" . $listNodoc . ") and status='1'";
				}
			} else {
				$str = "select * from " . $dbname . ".listfileupload where notransaksi = '" . $listNodoc . "' and status='1'";
			}
		}

		$res = fetchData($str);
		if (empty($res)) {
			$tab .= "<tr class=rowcontent><td colspan=8 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			foreach ($res as $key => $val) {
				if ($listNodoc != "") {
					$strdt = "select distinct notransaksi,noakun from " . $dbname . ".keu_pdo_vw where nopdo='" . $nopdo . "' and nodocument='" . $val['notransaksi'] . "'";
					$resdt = fetchData($strdt);
					$notrans[0] = $nopdo;
					$notrans[1] = $val['notransaksi'];
					$notrans[2] = $resdt[0]['noakun'];
				} else {
					$notrans = explode('##', $val['notransaksi']);
					$strdt = "select noakun from " . $dbname . ".keu_pdodt where nopdo='" . $notrans[0] . "' and notransaksi='" . $notrans[1] . "' and nourut='" . $notrans[2] . "'";
					$resdt = fetchData($strdt);
				}

				$no++;
				$tab .= "<tr id='ppDetailTable' class=rowcontent>
                    <td style='text-align:center'>" . $no . "</td>
                    <td style='text-align:center'>" . $notrans[0] . "</td>
                    <td style='text-align:center'>" . $notrans[1] . "</td>
                    <td style='text-align:center'>" . $notrans[2] . "</td>
                    <td style='text-align:center'>" . $arrnmaruskas[$resdt[0]['noakun']] . "</td>";
				$alamafolder = "pdo";
				if ($tipe == 'PAD') {
					$alamafolder = "lgl_bansos";
				}
				if ($tipe == 'IO') {
					$alamafolder = "ijin_ops";
				}
				if ($val['formaticon'] == '.jpeg' || $val['formaticon'] == '.jpg') {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/" . $alamafolder . "/" . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
                    </td>";
				} elseif ($val['formaticon'] == '.png') {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/" . $alamafolder . "/" . $val['namafile'] . "' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
                    </td>";
				} elseif ($val['formaticon'] == '.pdf') {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/" . $alamafolder . "/" . $val['namafile'] . "' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
                    </td>";
				} elseif ($val['formaticon'] == '.xls' || $val['formaticon'] == '.xlsx') {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/" . $alamafolder . "/" . $val['namafile'] . "' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
                    </td>";
				} elseif ($val['formaticon'] == '.doc' || $val['formaticon'] == '.docx') {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/" . $alamafolder . "/" . $val['namafile'] . "' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
                    </td>";
				} else {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/" . $alamafolder . "/" . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
                    </td>";
				}

				$tab .= "<td style='text-align:left'>" . $val['namafile'] . "</td>
                    <td align=center>
                        <a href='fileupload/" . $alamafolder . "/" . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				$tab . "  </td>
                </tr>";
			}
		}
		$tab .= "</tbody>
            </table>
        </fieldset>";

		echo $tab;
		break;

	case 'detailrealisasi':

		$no = 0;
		$total = 0;
		$tab = "";

		$tab .= "<table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center'>" . $_SESSION['lang']['nourut'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['notransaksi'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['noreferensi'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['unit'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['noakun'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['rekening'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['customer'] . "/" . $_SESSION['lang']['supplier'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['keterangan'] . "</td>
                    <td align='center'>" . $_SESSION['lang']['jumlah'] . "</td>
                </tr>
                </thead>
                <tbody>";

		$supplier = "kodesupplier";
		if ($tipe == 'M') {
			$supplier = "kodecustomer as kodesupplier";
		}

		$whr = " and noaruskas='" . $akunkas . "'";
		if (strlen($akunkas) > 5) {
			$str = " select noakun from " . $dbname . ".log_5supkelompok where supplierid='" . $akunkas . "' ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$noakun = $bar['noakun'];

			$whr = " and noakun='" . $noakun . "' and kodesupplier='" . $akunkas . "'";
		}

		#=realisasi
		$str = "select notransaksi,keterangan1,kodeorg,noakun,rekening,tanggal," . $supplier . ",keterangan,jumlah from " . $dbname . ".keu_kasbankdtht_vw where tipetransaksi='" . $tipe . "' and tanggal like '" . $per . "%' and kodeorg='" . $unit . "' " . $whr;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no++;
			$strrek = "select rekening,b.namabank as namabank from " . $dbname . ".keu_5akunbank a left join " . $dbname . ".keu_5daftarbank b  on a.namabank=b.kodebank where noakun='" . $bar['rekening'] . "' ";
			$resrek = $owlPDO->query($strrek) or die(print " Gagal: " . PDOException::getMessage());
			$resrek->setFetchMode(PDO::FETCH_ASSOC);
			$barrek = $resrek->fetch();

			$tab .= "<tr class=rowcontent>
                <td style='text-align:center'>" . $no . "</td>
                <td>" . $bar['notransaksi'] . "</td>
                <td>" . $bar['keterangan1'] . "</td>
                <td>" . $bar['kodeorg'] . "</td>
                <td>" . $bar['noakun'] . "<br>(" . $arrnmakun[$bar['noakun']] . ")</td>
                <td>" . $barrek['rekening'] . "<br>(" . $barrek['namabank'] . ")</td>
                <td>" . tanggalnormal($bar['tanggal']) . "</td>
                <td>" . $bar['kodesupplier'] . "<br>(" . (($arrnmsupp[$bar['kodesupplier']] == '') ? $arrnmcust[$bar['kodesupplier']] : $arrnmsupp[$bar['kodesupplier']]) . ")</td>
                <td>" . $bar['keterangan'] . "</td>
                <td align=right>" . number_format($bar['jumlah']) . "</td></tr>";
			$total += $bar['jumlah'];
		}
		$tab .= "<tr class=rowcontent>
			<td colspan=9>" . $_SESSION['lang']['total'] . "</td>
			<td>" . number_format($total) . "</td>
			</tr>";

		$tab .= "</tbody>
            </table>";

		echo $tab;
		break;

	//Umar
	case 'showupload':
		$tab = "";

		$tab .= "<table cellspacing='1' border='0' id='uploadpopup'>
            <tr>
                <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                <td>:</td>
                <td>
                    <label id='notransupload' style='font-weight:bold'>" . $notransaksi . "</label>
                </td>
            </tr>
            <tr>
                <td>Filename</td>
                <td>:</td>
                <td>
                    <input type='file' name='upload' id='upload' class=mybutton>
                </td>
            </tr>
            <tr>
                <td colspan=2></td>
                <td>
                    <button class=mybutton onclick=\"submitfile('" . $notransaksi . "')\">Submit</button>
                </td>
            </tr>
        </table>
        <p />";

		$tab .= "<fieldset>
            <legend>" . $_SESSION['lang']['list'] . "</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center'>No.</td>
                    <td align='center'>File Type</td>
                    <td align='center'>Filename</td>
                    <td align='center'>Action</td>
                </tr>
                </thead>
                <tbody id='listfiles'>
                </tbody>
            </table>
        </fieldset> ";

		echo $tab;
		break;

	case 'submitfile':
		$tgl = date("YmdHis");
		// exit("error : ".$tgl);
		$data = $_POST;

		if ($data['fileupload'] != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$newfilename = str_replace($filetype, '', $_FILES['file']['name']);
				$filename = $newfilename . "_" . $tgl . "" . $filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];

				if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx') || ($filetype == '.rar')) {
					if ($_FILES['file']['size'] <= 250000) {
						$str = "insert into " . $dbname . ".listfileupload (`id`, `notransaksi`, `namafile`, `formaticon`, `status`,`createdby`,`createdtime`) values ('','" . $data['notransaksi'] . "','" . $filename . "','" . $filetype . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
						try {
							$owlPDO->exec($str);
							move_uploaded_file($file_tmpname, "fileupload/pdo/$filename");
						} catch (PDOException $e) {
							echo " Gagal," . addslashes($e->getMessage());
						}
					} else {
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				} else {
					exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
				}
			}
		}
		break;

	case 'loadfiles':
		$no = 0;
		$tab = "";
		$str = "select * from " . $dbname . ".keu_pdodt where nopdo='" . $nopdo . "'";
		$resv = fetchData($str);
		foreach ($resv as $bar => $barv) {
			$close = $barv['close'];
		}

		$str = "select * from " . $dbname . ".listfileupload where notransaksi = '" . $nopdo . "' and status='1'";
		$res = fetchData($str);
		if (empty($res)) {
			$tab .= "<tr class=rowcontent><td colspan=4 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			foreach ($res as $key => $val) {
				$no++;
				$tab .= "<tr id='ppDetailTable' class=rowcontent>
                    <td style='text-align:center'>" . $no . "</td>";

				if ($val['formaticon'] == '.jpeg' || $val['formaticon'] == '.jpg') {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/pdo/" . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
                    </td>";
				} elseif ($val['formaticon'] == '.png') {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/pdo/" . $val['namafile'] . "' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
                    </td>";
				} elseif ($val['formaticon'] == '.pdf') {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/pdo/" . $val['namafile'] . "' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
                    </td>";
				} elseif ($val['formaticon'] == '.xls' || $val['formaticon'] == '.xlsx') {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/pdo/" . $val['namafile'] . "' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
                    </td>";
				} elseif ($val['formaticon'] == '.doc' || $val['formaticon'] == '.docx') {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/pdo/" . $val['namafile'] . "' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
                    </td>";
				} else {
					$tab .= "<td style='text-align:center'>
                        <a href='fileupload/pdo/" . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
                    </td>";
				}

				$tab .= "<td style='text-align:left'>" . $val['namafile'] . "</td>
                    <td align=center>
                        <a href='fileupload/pdo/" . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				$tab .= "<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $nopdo . "','" . $val['namafile'] . "');\" >";
				$tab . "  </td>
                </tr>";
			}
		}
		echo $tab;
		break;

	case 'deletefile':
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $nopdo . "' and namafile='" . $namafile . "'";
		try {
			$owlPDO->exec($str);
			$path = "fileupload/pdo/" . $namafile;
			unlink($path);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;
	//End Umar

	case 'getAkunBiaya':
		$kpl = '7';
		if ($tporg[$unit] == 'BULKING') {
			$kpl = "8";
		}

		$qCoa = selectQuery($dbname, "keu_5akun", "noakun, namaakun", "level='5' AND left(noakun,1) = '{$kpl}' AND aktif='1'");
		$rCoa = fetchData($qCoa);
		$optAkunBy = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($rCoa as $bar) {
			$optAkunBy .= "<option value='{$bar['noakun']}'>{$bar['noakun']} - {$bar['namaakun']}</option>";
		}

		echo $optAkunBy;
		break;

	case 'getAkunOthers':
		$optAkun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$qAkun = selectQuery($dbname, "keu_5akun", "noakun, namaakun", "kasbankdetail='1' AND aktif='1'");
		$rAkun = fetchData($qAkun);
		foreach ($rAkun as $row) {
			$optAkun .= "<option value='{$row['noakun']}'>{$row['noakun']} - {$row['namaakun']}</option>";
		}

		echo $optAkun;
		break;

	case 'getAkunTraksi':
		$optAkun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$qAkun = selectQuery($dbname, "vhc_kegiatan", "kodekegiatan, namakegiatan");
		$rAkun = fetchData($qAkun);
		foreach ($rAkun as $row) {
			$optAkun .= "<option value='{$row['kodekegiatan']}'>{$row['kodekegiatan']} - {$row['namakegiatan']}</option>";
		}

		echo $optAkun;
		break;

	case 'getNotrans':
		/* cek apakah data sudah posting atau belum */
		$str = selectQuery($dbname, 'keu_pdoht', 'COUNT(*) AS count', "kodeorg = '" . $unit . "' AND periode = '" . $per . "' AND sesi = '" . $sesi . "'");
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$count = $bar['count'];
		}
		if ($count > 0 && $mode == "save") {
			exit("Warning : PDO untuk " . $unit . " Periode " . $per . " dengan Sesi " . $sesi . " sudah di ada.");
		}

		$qPdo = selectQuery($dbname, "keu_pdodt", "notransaksi", "nopdo='{$nopdo}' GROUP BY notransaksi");
		$rPdo = fetchData($qPdo);
		$data = [];
		foreach ($rPdo as $bar) {
			$idTrans = explode("/", $bar['notransaksi'])[2];
			$data[$idTrans] = $bar['notransaksi'];
		}
		echo json_encode($data);
		break;

	case 'detailAll':
		$tipePdo = checkPostGet("tipePdo", "");
		$header = getNamaOrg($unit);
		$stream = "
        <div style='text-align:center;'>
            <p style='font-size:1rem;font-weight:bold;'>DETAIL PERMINTAAN DANA OPERASIONAL " . $header . "</p>
            <p style='font-size:1rem;font-weight:bold;'>PERIODE : {$per}</p>
        </div>";

		if (generateTableDetail($nopdo, $unit, $per, $tipePdo) != "") {
			$stream .= generateTableDetail($nopdo, $unit, $per, $tipePdo);
			$nop = "PDO {$tipePdo}.xls";
			$xls = new HtmlExcel();
			$borderStyle = 'border: 1px solid #000;';
			$css = "table { border-collapse: collapse; } th, td { $borderStyle padding: 5px; }";
			$xls->setCss($css);
			$xls->addSheet("PDO {$tipePdo}", $stream);
			$xls->headers($nop);
			echo $xls->buildFile();
		}

		break;
}

function generateTableDetail($nopdo, $unit, $periode, $tipePdo)
{
	global $dbname;
	global $arrnmakun;
	global $nmTipe;
	global $nmKary;

	$stream = "";
	$tableBody = "";
	$tableFoot = "";
	$ttl = 0;
	$no = 1;
	switch ($tipePdo) {
		case 'UPAH':
			break;
		case 'KAS':
			$head = [
				"{$_SESSION['lang']['nourut']}" => ["width" => "3%"],
				"Akun Biaya" => [],
				"{$_SESSION['lang']['uraian']}" => [],
				"Novoucher" => [],
				"Nilai Rupiah BI" => [],
			];

			$dataTransaksi = selectQuery($dbname, "keu_pdodt", "*", "nopdo='{$nopdo}' AND notransaksi LIKE '%{$tipePdo}%'");
			$res = fetchData($dataTransaksi);
			foreach ($res as $bar) {
				$tableBody .= "<tr class='rowcontent'>";
				$tableBody .= "<td align='center'>" . $no++ . "</td>";
				$tableBody .= "<td align='left' >" . $bar['noakun'] . " - " . $arrnmakun[$bar['noakun']] . "</td>";
				$tableBody .= "<td align='left' >" . $bar['rincian'] . "</td>";
				$tableBody .= "<td align='left'>" . $bar['nodocument'] . "</td>";
				$tableBody .= "<td align='right' >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";
				$tableBody .= "</tr>";

				$ttl += $bar['rupiahdiajukan'];
			}

			$tableFoot .= "
                <td colspan='4' align='right' style='font-weight:bold'>" . $_SESSION['lang']['total'] . "</td>
                <td align=right>" . number_format($ttl, 2) . "</td>";
			break;
		case 'HTGK':
			$head = [
				"{$_SESSION['lang']['nourut']}" => ["width" => "3%"],
				"Akun Biaya" => [],
				"{$_SESSION['lang']['uraian']}" => [],
				"Nilai Rupiah BI" => [],
			];

			$dataTransaksi = selectQuery($dbname, "keu_pdodt", "*", "nopdo='{$nopdo}' AND notransaksi LIKE '%{$tipePdo}%'");
			$res = fetchData($dataTransaksi);
			foreach ($res as $bar) {
				$tableBody .= "<tr class='rowcontent'>";
				$tableBody .= "<td align='center'>" . $no++ . "</td>";
				$tableBody .= "<td align='left' >" . $bar['noakun'] . " - " . $arrnmakun[$bar['noakun']] . "</td>";
				$tableBody .= "<td align='left' >" . $bar['rincian'] . "</td>";
				$tableBody .= "<td align='right'>" . number_format($bar['rupiahdiajukan'], 2) . "</td>";
				$tableBody .= "</tr>";

				$ttl += $bar['rupiahdiajukan'];
			}

			$tableFoot .= "
                <td colspan='3' align='center' style='font-weight:bold'>" . $_SESSION['lang']['total'] . "</td>
                <td align=right>" . number_format($ttl, 2) . "</td>";
			break;
		case 'KTRK':
			$head = [
				"{$_SESSION['lang']['nourut']}" => ["width" => "3%"],
				"{$_SESSION['lang']['noinvoice']}" => [],
				"{$_SESSION['lang']['noreferensi']}" => [],
				"{$_SESSION['lang']['tipe']}" => [],
				"Termin" => [],
				"Nama Pihak Ketiga" => [],
				"Kegiatan" => [],
				"Nilai Invoice" => [],
			];

			$dataTransaksi = selectQuery($dbname, "keu_pdodt", "*", "nopdo='{$nopdo}' AND notransaksi LIKE '%{$tipePdo}%'");
			$res = fetchData($dataTransaksi);
			foreach ($res as $bar) {
				$tableBody .= "<tr class='rowcontent'>";
				$tableBody .= "<td align='center'>" . $no++ . "</td>";
				$tableBody .= "<td align=left >" . $bar['noinvoice'] . "</td>";
				$tableBody .= "<td align=left >" . $bar['nodocument'] . "</td>";
				$tableBody .= "<td align=left >" . $nmTipe[$bar['tipeinvoice']] . "</td>";
				$tableBody .= "<td align=left >" . $bar['rincian'] . "</td>";
				$tableBody .= "<td align=left >" . getNamaSupplier($bar['kodesupplier']) . "</td>";
				$tableBody .= "<td align=left >" . $bar['noakun'] . " - " . getNamaAkun($bar['noakun']) . "</td>";
				$tableBody .= "<td align=right >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";
				$tableBody .= "</tr>";

				$ttl += $bar['rupiahdiajukan'];
			}
			$tableFoot .= "
                <td colspan='7' align='right' style='font-weight:bold'>" . $_SESSION['lang']['total'] . "</td>
                <td align=right>" . number_format($ttl, 2) . "</td>";

			break;
		case 'PJD':
			$head = [
				"{$_SESSION['lang']['nourut']}" => ["width" => "3%"],
				"{$_SESSION['lang']['kodeorg']}" => [],
				"{$_SESSION['lang']['nodok']}" => [],
				"{$_SESSION['lang']['namakaryawan']}" => [],
				"{$_SESSION['lang']['tanggal']}" => [],
				"{$_SESSION['lang']['noakun']}" => ["width" => "8%"],
				"Nilai Rupiah BI" => ["width" => "10%"],
			];

			$dataTransaksi = selectQuery($dbname, "keu_pdodt", "*", "nopdo='{$nopdo}' AND notransaksi LIKE '%{$tipePdo}%'");
			$res = fetchData($dataTransaksi);
			foreach ($res as $bar) {
				$tableBody .= "<tr class='rowcontent'>";
				$tableBody .= "<td align='center'>" . $no++ . "</td>";
				$tableBody .= "<td align='left' >" . $bar['kodeorg'] . "</td>";
				$tableBody .= "<td align='left' >" . $bar['nodocument'] . "</td>";
				$tableBody .= "<td align='left' >" . getNamaKaryawan($nmKary[$bar['nodocument']]) . "</td>";
				$tableBody .= "<td align='left' >" . $bar['tanggal'] . "</td>";
				$tableBody .= "<td align='left' >" . $bar['noakun'] . " - " . $arrnmakun[$bar['noakun']] . "</td>";
				$tableBody .= "<td align='right' >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";
				$tableBody .= "</tr>";

				$ttl += $bar['rupiahdiajukan'];
			}
			$tableFoot .= "
                <td colspan='6' align='right' style='font-weight:bold'>" . $_SESSION['lang']['total'] . "</td>
                <td align=right>" . number_format($ttl, 2) . "</td>";
			break;
		case 'OTH':
			$head = [
				"{$_SESSION['lang']['nourut']}" => ["width" => "3%"],
				"{$_SESSION['lang']['noakun']}" => ["width" => "8%"],
				"{$_SESSION['lang']['uraian']}" => ["width" => "21%"],
				"Nilai Rupiah BI" => ["width" => "10%"],
			];

			$dataTransaksi = selectQuery($dbname, "keu_pdodt", "*", "nopdo='{$nopdo}' AND notransaksi LIKE '%{$tipePdo}%'");
			$res = fetchData($dataTransaksi);
			foreach ($res as $bar) {
				$tableBody .= "<tr class='rowcontent'>";
				$tableBody .= "<td align='center'>" . $no++ . "</td>";
				$tableBody .= "<td align='left' >" . $bar['noakun'] . " - {$arrnmakun[$bar['noakun']]}</td>";
				$tableBody .= "<td align='left' >" . $bar['rincian'] . "</td>";
				$tableBody .= "<td align='right' >" . number_format($bar['rupiahdiajukan'], 2) . "</td>";
				$tableBody .= "</tr>";

				$ttl += $bar['rupiahdiajukan'];
			}

			$tableFoot .= "
                <td colspan='3' align='right' style='font-weight:bold'>" . $_SESSION['lang']['total'] . "</td>
                <td align=right>" . number_format($ttl, 2) . "</td>";
			break;
	}

	$tableHead = generateHead($head);

	if (!empty($res)) {
		$stream .= "
            <table cellpading='1' cellspacing='1' border=1 class='sortable'>
                <thead>
                    <tr class='rowheader'>
                        {$tableHead}
                    </tr>
                </thead>
                <tbody>
                    {$tableBody}
                </tbody>
                <tfoot>
                    <tr class='rowcontent'>
                        {$tableFoot}
                    </tr>
                </tfoot>
            </table>";
	}

	return $stream;
}

function generateHead($head)
{
	$html = "";
	foreach ($head as $title => $styles) {
		$styleString = '';
		foreach ($styles as $key => $value) {
			$styleString .= "$key:$value;";
		}
		$html .= "<th align='center' style='$styleString'>$title</th>";
	}

	return $html;
}

function generateKodeTransaksi($unit, $periode, $inisial)
{
	global $owlPDO;
	global $dbname;

	$notransaksi = "";
	$month = date('m', strtotime($periode));
	$year = date('Y', strtotime($periode));

	$pt = makeOption($dbname, "organisasi", "kodeorganisasi,induk", "kodeorganisasi='{$unit}'")[$unit];

	switch (strtoupper($inisial)) {
		case 'PDO':
			$queryPDO = selectQuery($dbname, "keu_pdoht", "MAX(CAST(SUBSTRING_INDEX(nopdo, '/', -1) AS SIGNED)) AS max_counter", "kodeorg='{$unit}' AND periode='{$periode}'");
			break;
		case 'UPAH':
			$queryPDO = selectQuery($dbname, "keu_pdoht", "MAX(CAST(SUBSTRING_INDEX(notransaksi, '/', -1) AS SIGNED)) AS max_counter", "kodeorg='{$unit}' AND periode='{$periode}' AND notransaksi LIKE '%UPAH%'");
			break;
		case 'KAS':
			$queryPDO = selectQuery($dbname, "keu_pdoht", "MAX(CAST(SUBSTRING_INDEX(notransaksi, '/', -1) AS SIGNED)) AS max_counter", "kodeorg='{$unit}' AND periode='{$periode}' AND notransaksi LIKE '%KAS%'");
			break;
		case 'HTGK':
			$queryPDO = selectQuery($dbname, "keu_pdoht", "MAX(CAST(SUBSTRING_INDEX(notransaksi, '/', -1) AS SIGNED)) AS max_counter", "kodeorg='{$unit}' AND periode='{$periode}' AND notransaksi LIKE '%HTGK%'");
			break;
		case 'KTRK':
			$queryPDO = selectQuery($dbname, "keu_pdoht", "MAX(CAST(SUBSTRING_INDEX(notransaksi, '/', -1) AS SIGNED)) AS max_counter", "kodeorg='{$unit}' AND periode='{$periode}' AND notransaksi LIKE '%KTRK%'");
			break;
		case 'SUPP':
			$queryPDO = selectQuery($dbname, "keu_pdoht", "MAX(CAST(SUBSTRING_INDEX(notransaksi, '/', -1) AS SIGNED)) AS max_counter", "kodeorg='{$unit}' AND periode='{$periode}' AND notransaksi LIKE '%SUPP%'");
			break;
		case 'PJD':
			$queryPDO = selectQuery($dbname, "keu_pdoht", "MAX(CAST(SUBSTRING_INDEX(notransaksi, '/', -1) AS SIGNED)) AS max_counter", "kodeorg='{$unit}' AND periode='{$periode}' AND notransaksi LIKE '%PJD%'");
			break;
		case 'OTH':
			$queryPDO = selectQuery($dbname, "keu_pdoht", "MAX(CAST(SUBSTRING_INDEX(notransaksi, '/', -1) AS SIGNED)) AS max_counter", "kodeorg='{$unit}' AND periode='{$periode}' AND notransaksi LIKE '%OTH%'");
			break;
		case 'TNM':
			$queryPDO = selectQuery($dbname, "keu_pdoht", "MAX(CAST(SUBSTRING_INDEX(notransaksi, '/', -1) AS SIGNED)) AS max_counter", "kodeorg='{$unit}' AND periode='{$periode}' AND notransaksi LIKE '%TNM%'");
			break;
		case 'TRK':
			$queryPDO = selectQuery($dbname, "keu_pdoht", "MAX(CAST(SUBSTRING_INDEX(notransaksi, '/', -1) AS SIGNED)) AS max_counter", "kodeorg='{$unit}' AND periode='{$periode}' AND notransaksi LIKE '%TRK%'");
			break;
		default:
			exit("Warning: Gagal Membentuk nomor Transaksi " . $inisial . " karena belum tersetupkan!");
			break;
	}

	$resultPDO = fetchData($queryPDO);
	$counter = (empty($resultPDO)) ? 1 : $resultPDO[0]['max_counter'] + 1;
	$counter = str_pad($counter, 5, '0', STR_PAD_LEFT);
	$notransaksi = $pt . "/" . $unit . "/" . $inisial . "/" . substr($year, 2, 2) . "/" . $month . "/" . $counter;

	return $notransaksi;
}

function getStartingCounter($unit, $periode, $tipe)
{
	global $owlPDO;
	global $dbname;

	$month = date('m', strtotime($periode));
	$year = date('Y', strtotime($periode));

	$query = "SELECT MAX(CAST(SUBSTRING_INDEX(nodok, '/', -1) AS SIGNED)) AS max_counter 
              FROM " . $dbname . ".keu_pdodt 
              WHERE notransaksi LIKE '%" . $unit . "/KAS/{$year}/{$month}%' 
              AND nodok LIKE '" . $year . $month . "/" . $unit . "/DOK" . $tipe . "/%'";
	$res = fetchData($query);
	$max = (empty($res[0]['max_counter'])) ? 0 : (int)$res[0]['max_counter'];

	return $max + 1;
}
