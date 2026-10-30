<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method', '');
$notransaksi = checkPostGet('notransaksi', '');
$tanggal = tanggalsystemn(checkPostGet('tanggal', ''));
$kodeorg = checkPostGet('kodeorg', '');
$kategori = checkPostGet('kategori', '');
$namapemesan = checkPostGet('namapemesan', '');
$lokasipemesan = checkPostGet('lokasipemesan', '');
$tujuan = checkPostGet('tujuan', '');
$satuan = checkPostGet('satuan', '');
$deskripsi = checkPostGet('deskripsi', '');
$rupiah = checkPostGet('rupiah', '');
$rekening = checkPostGet('rekening', '');
$tipe = checkPostGet('tipe', '');
$tipebayar = checkPostGet('tipebayar', '');
$atasnama = checkPostGet('atasnama', '');
$npwp = checkPostGet('npwp', '');
$ptsrc = checkPostGet('ptsrc', '');
$kasbanksrc = checkPostGet('kasbanksrc', '');
$katsrc = checkPostGet('katsrc', '');
$status = checkPostGet('status', '');
$tanggalmulai = tanggalsystemn(checkPostGet('tanggalmulai', ''));
$tanggalsampai = tanggalsystemn(checkPostGet('tanggalsampai', ''));
$tgldokumenstart = tanggalsystemn(checkPostGet('tgldokumenstart', ''));
$tgldokumenend = tanggalsystemn(checkPostGet('tgldokumenend', ''));

$numrow = checkPostGet('numrow', '');
$kepada = checkPostGet('kepada', '');
$rupiah=str_replace(',','',$rupiah);

$keterangan = checkPostGet('keterangan', '');
$namafile = checkPostGet('namafile', '');

$divsch = checkPostGet('divsch', '');
$namasrc = checkPostGet('namasrc', '');
$tanggalsch = checkPostGet('periodesch', '');
$bloksch = checkPostGet('bloksch', '');

$unitexp = checkPostGet('unitexp', '');
$perexp = checkPostGet('perexp', '');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmkat = makeOption($dbname, 'lgl_kategoribansos', 'kode,nama');

$jenistrk = "BANSOS";
$jenisApp = "BANSOS";

$tmpTgl = explode('-',$tanggal);	
$path	= "fileupload/lgl_bansos/";
$maksUpload = 10 * 1024 * 1024; #batas ukuran file upload (byte)
$formatUpload = array('.jpeg', '.jpg', '.png', '.pdf', '.xls', '.xlsx', '.doc', '.docx');
$todayhis=date('Y-m-d H:i:s');

$urlefil=checkPostGet('urlefil','0');
if($urlefil!='0'){
	$method = $_GET['method'];
	$notransaksi = $_GET['notransaksi'];
}

#=== Pengamanan: format parameter, hak akses unit, dan status ===
#format parameter diperiksa sebelum dipakai di query (kosong boleh); teks undefined/null dari JS lama dianggap kosong
$polaId   = '/^[A-Za-z0-9\/\-_\.]{1,56}$/';
$polaOrg  = '/^[A-Za-z0-9]{1,6}$/';
$polaKode = '/^[A-Za-z0-9\-\.]{1,24}$/';
$polaBNS = array(
	'notransaksi'  => $polaId,
	'kodeorg'      => $polaOrg,
	'lokasipemesan'=> $polaOrg,
	'divsch'       => $polaOrg,
	'ptsrc'        => $polaOrg,
	'unitexp'      => $polaOrg,
	'kategori'     => $polaKode,
	'katsrc'       => $polaKode,
	'rekening'     => $polaKode,
	'tipe'         => '/^[A-Za-z]{1,10}$/',
	'tipebayar'    => '/^[A-Za-z]{1,12}$/',
	'status'       => '/^[x0-9]$/',
	'kasbanksrc'   => '/^[01]$/',
	'numrow'       => '/^[0-9]{1,6}$/',
	'kepada'       => '/^[0-9]{1,10}$/',
	'tanggalsch'   => '/^[0-9\-]{1,10}$/',
	'bloksch'      => '/^[A-Za-z0-9\-\.]{1,24}$/',
	'perexp'       => '/^[0-9\-]{1,10}$/'
);
foreach ($polaBNS as $kunciBNS => $polaX) {
	$nilaiX = $$kunciBNS;
	if ($nilaiX === 'undefined' || $nilaiX === 'null') {
		$nilaiX = $$kunciBNS = '';
	}
	if ($nilaiX !== '' && (!is_string($nilaiX) || !preg_match($polaX, $nilaiX))) {
		exit("Warning : Parameter " . $kunciBNS . " tidak valid.");
	}
}

#angka: koma ribuan dibuang, sisanya harus angka tidak negatif
$rupiah = is_string($rupiah) ? str_replace(',', '', $rupiah) : $rupiah;
if ($rupiah !== '' && (!is_string($rupiah) || !preg_match('/^[0-9]+(\.[0-9]+)?$/', $rupiah))) {
	exit("Warning : Nilai rupiah harus berupa angka tidak negatif.");
}

#tanggal (hasil konversi tanggalsystemn; kosong menjadi --) harus berformat YYYY-MM-DD
foreach (array('tanggal', 'tanggalmulai', 'tanggalsampai', 'tgldokumenstart', 'tgldokumenend') as $kunciBNS) {
	if ($$kunciBNS !== '' && $$kunciBNS !== '--' && (!is_string($$kunciBNS) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $$kunciBNS))) {
		exit("Warning : Format tanggal tidak valid.");
	}
}

#PDF efill hanya boleh ditulis ke folder efillinv
if ($urlefil !== '0' && (!is_string($urlefil) || !preg_match('#^fileupload/efillinv/[^\x00-\x1f\\\\:*?"<>|]+\.pdf$#', $urlefil) || strpos($urlefil, '..') !== false)) {
	exit("Warning : Parameter urlefil tidak valid.");
}

#teks bebas: karakter khusus SQL di-escape sekali di sini, query di bawah memakai nilai ini
$namafileAsli = is_string($namafile) ? $namafile : '';
$bnsRaw = array('namasrc' => is_string($namasrc) ? $namasrc : '');
foreach (array('namapemesan', 'tujuan', 'deskripsi', 'satuan', 'atasnama', 'npwp', 'keterangan', 'namasrc', 'namafile') as $kunciBNS) {
	$$kunciBNS = is_string($$kunciBNS) ? substr($owlPDO->quote($$kunciBNS), 1, -1) : '';
}

if (!function_exists('bnsHeader')) {
#header bansos (baris pertama nomor transaksi) + cek hak akses unit; $mode 'baca' juga mengizinkan penyetuju yang tercatat
function bnsHeader($notransaksi, $mode = 'ubah', $harusEdit = false, $bolehKosong = false)
{
	global $owlPDO, $dbname;
	$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_bansos where notransaksi=? limit 1");
	$stmt->execute(array($notransaksi));
	$hdr = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!$hdr) {
		if ($bolehKosong) {
			return null;
		}
		exit("Warning : Data bansos tidak ditemukan.");
	}
	$unitAkses = getOrgDetail(1);
	$boleh = isset($unitAkses[$hdr['kodeorg']]);
	if (!$boleh && $mode == 'baca') {
		$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".approval where notransaksi=? and karyawanid=?");
		$stmt->execute(array($notransaksi, $_SESSION['standard']['userid']));
		$boleh = $stmt->fetchColumn() > 0;
	}
	if (!$boleh) {
		exit("Warning : Unit " . $hdr['kodeorg'] . " tidak sesuai hak akses Anda.");
	}
	if ($harusEdit && $hdr['posting'] != '0') {
		exit("Warning : Bansos ini sudah diposting, tidak dapat diubah.");
	}
	return $hdr;
}

#filter daftar bansos; hanya unit yang boleh diakses user
function bnsWhereList()
{
	global $divsch, $namasrc, $tanggalmulai, $tanggalsampai, $ptsrc, $kasbanksrc, $katsrc, $status, $dbname;
	$where = " and a.kodeorg in (" . getOrgDetail(2) . ")";
	if ($divsch != '') {
		$where .= " and a.kodeorg='" . $divsch . "' ";
	}
	if ($namasrc != '') {
		$where .= " and a.atasnama='" . $namasrc . "' ";
	}
	if ($tanggalmulai != '--' and $tanggalsampai == '--') {
		$where .= " and a.tanggal='" . $tanggalmulai . "' ";
	} elseif ($tanggalmulai == '--' and $tanggalsampai != '--') {
		$where .= " and a.tanggal='" . $tanggalsampai . "' ";
	} elseif ($tanggalmulai != '--' and $tanggalsampai != '--') {
		$where .= " and a.tanggal between '" . $tanggalmulai . "' and '" . $tanggalsampai . "'";
	}
	if ($ptsrc != '') {
		$where .= " and a.kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk ='" . $ptsrc . "')";
	}
	if ($kasbanksrc == '1') {
		$where .= " and exists (select 1 from " . $dbname . ".keu_kasbankdt d where d.nodok=a.notransaksi) ";
	} elseif ($kasbanksrc == '0') {
		$where .= " and not exists (select 1 from " . $dbname . ".keu_kasbankdt d where d.nodok=a.notransaksi) ";
	}
	if ($katsrc != '') {
		$where .= " and a.kategori='" . $katsrc . "' ";
	}
	if ($status == 'x') {
		$where .= " and a.posting='0' and a.statuspersetujuan='0' ";
	} elseif ($status != 'x' and $status != '') {
		$where .= " and a.posting='1' and a.statuspersetujuan='" . $status . "' ";
	}
	return $where;
}
#teks untuk argumen JS di dalam atribut onclick (kutip, backslash, dan baris baru aman)
function bnsJs($s)
{
	return htmlspecialchars(str_replace(array("\r\n", "\r", "\n"), '\n', addslashes($s)), ENT_QUOTES);
}
#status bansos untuk tarikan
function bnsLabelStatus($r)
{
	if ($r['posting'] == 0) {
		return 'Belum diposting';
	}
	if ($r['statuspersetujuan'] == 1) {
		return 'Disetujui (lama)';
	}
	if ($r['statuspersetujuan'] == 3) {
		return 'Ditolak (lama)';
	}
	return 'Diposting';
}

#keterangan filter yang aktif untuk kop tarikan
function bnsInfoFilter()
{
	global $nmorg, $nmkat, $divsch, $ptsrc, $katsrc, $status, $tanggalmulai, $tanggalsampai, $bnsRaw;
	$info = array();
	$info[] = 'Unit: ' . ($divsch != '' ? $divsch . (isset($nmorg[$divsch]) ? ' - ' . $nmorg[$divsch] : '') : 'Semua');
	$info[] = 'Kategori: ' . ($katsrc != '' && isset($nmkat[$katsrc]) ? $nmkat[$katsrc] : 'Semua');
	$arrStatus = array('x' => 'Belum Diposting', '0' => 'Sudah Diposting', '1' => 'Disetujui (lama)', '3' => 'Ditolak (lama)');
	$info[] = 'Status: ' . ($status != '' && isset($arrStatus[$status]) ? $arrStatus[$status] : 'Semua');
	if ($ptsrc != '') {
		$info[] = 'PT: ' . $ptsrc;
	}
	if ($tanggalmulai != '--' || $tanggalsampai != '--') {
		$info[] = 'Tanggal: ' . ($tanggalmulai != '--' ? tanggalnormal($tanggalmulai) : '...') . ' s/d ' . ($tanggalsampai != '--' ? tanggalnormal($tanggalsampai) : '...');
	}
	if ($bnsRaw['namasrc'] != '') {
		$info[] = 'Atas Nama: ' . $bnsRaw['namasrc'];
	}
	return implode(' | ', $info);
}

#seluruh bansos (satu baris per nomor transaksi) sesuai filter untuk tarikan Excel/PDF
function bnsDataList()
{
	global $dbname;
	return fetchdata("SELECT a.notransaksi, a.kodeorg, a.tanggal, a.kategori, a.namapemesan, a.lokasipemesan, a.tipebayar, a.atasnama, a.posting, a.statuspersetujuan, a.updateby, sum(a.rupiah) AS biaya,
			(select group_concat(distinct d.notransaksi) from " . $dbname . ".keu_kasbankdt d where d.nodok=a.notransaksi) AS kas
		FROM " . $dbname . ".lgl_bansos a WHERE 1=1 " . bnsWhereList() . " GROUP BY a.notransaksi, a.kodeorg ORDER BY a.notransaksi DESC");
}

#teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
function bnsFit($pdf, $txt, $w, $f = 7)
{
	$txt = utf8_decode($txt);
	$pdf->SetFont('Arial', '', $f);
	if ($pdf->GetStringWidth($txt) <= $w - 2) {
		return $txt;
	}
	while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
		$txt = substr($txt, 0, -1);
	}
	return rtrim($txt) . '...';
}
#angka menjadi kata (bahasa Indonesia), untuk "Terbilang" pada dokumen
function bnsTerbilang($n)
{
	$n = (int)floor(abs($n));
	$satuan = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');
	if ($n < 12) {
		return $satuan[$n];
	}
	if ($n < 20) {
		return bnsTerbilang($n - 10) . ' belas';
	}
	if ($n < 100) {
		return trim(bnsTerbilang(intdiv($n, 10)) . ' puluh ' . bnsTerbilang($n % 10));
	}
	if ($n < 200) {
		return trim('seratus ' . bnsTerbilang($n - 100));
	}
	if ($n < 1000) {
		return trim(bnsTerbilang(intdiv($n, 100)) . ' ratus ' . bnsTerbilang($n % 100));
	}
	if ($n < 2000) {
		return trim('seribu ' . bnsTerbilang($n - 1000));
	}
	if ($n < 1000000) {
		return trim(bnsTerbilang(intdiv($n, 1000)) . ' ribu ' . bnsTerbilang($n % 1000));
	}
	if ($n < 1000000000) {
		return trim(bnsTerbilang(intdiv($n, 1000000)) . ' juta ' . bnsTerbilang($n % 1000000));
	}
	if ($n < 1000000000000) {
		return trim(bnsTerbilang(intdiv($n, 1000000000)) . ' milyar ' . bnsTerbilang($n % 1000000000));
	}
	return trim(bnsTerbilang(intdiv($n, 1000000000000)) . ' trilyun ' . bnsTerbilang($n % 1000000000000));
}
#unit harus terisi dan sesuai hak akses user
function bnsCekUnit($kodeorg)
{
	if ($kodeorg == '') {
		exit("Warning : Unit wajib diisi.");
	}
	$unitAkses = getOrgDetail(1);
	if (!isset($unitAkses[$kodeorg])) {
		exit("Warning : Unit " . $kodeorg . " tidak sesuai hak akses Anda.");
	}
}

#hanya file yang tercatat pada bansos yang boleh dibaca/dihapus
function bnsBarisFile($namaFile)
{
	global $owlPDO, $dbname;
	$stmt = $owlPDO->prepare("select * from " . $dbname . ".listfile_lgl_bansos where namafile=? limit 1");
	$stmt->execute(array(basename($namaFile)));
	return $stmt->fetch(PDO::FETCH_ASSOC);
}
}
#=== akhir pengamanan ===

// APAKAH USER YG LOGIN SAAT INI BISA POSTING 
$sStr = selectQuery($dbname,"setup_posting","*", "kodeaplikasi='csr'");
$qStr = fetchData($sStr);
$kodejabatan = makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
$userPosting = 0;
foreach ($qStr as $key => $value) {
	if ($value['jabatan'] == $kodejabatan[$_SESSION['standard']['userid']]) {
		$userPosting = 1;
		break;
	}
}

switch ($method) {
    case'html':
			bnsHeader($notransaksi, 'baca', false, true);
			if ($tipe == 'excel') {
				exit("Warning : Gunakan tombol Excel pada halaman ini.");
			}
		$tab= "";
		@$countApprove = getCountApproval($jenisApp,$kodeorg);
		$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);
		
		$rekbank = makeOption($dbname,'keu_5akunbank','noakun,rekening');
		$kodebank = makeOption($dbname,'keu_5akunbank','noakun,namabank');
		$bank = makeOption($dbname,'keu_5daftarbank','kodebank,namabank');
		
		$str=" select * from ".$dbname.".lgl_bansos where  notransaksi='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		$tab.= "<fieldset style=min-width:900px><legend>Bansos</legend>
				";
		$tab.= "<table border=0 cellspacing=1 cellpadding=5 class=sortable>
				<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']." </td>";
				// for($i=1;$i<=$countApprove;$i++){
				// 	$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				// }
					
		$tab.= "</tr></thead><tbody>";
		$tab.= "<tr class=rowcontent>
				<td>".$nmkar[$bar['updateby']]."<br>
					".$bar['updatetime']."</td>";
		
	$tab.= "</tbody></table><hr>";
	$tab.= "<table cellspacing=1 cellpadding=5 border=0>
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td>".$bar['notransaksi']."</td>
				
				<td>" . $_SESSION['lang']['kategori'] . "</td> 
				<td>:</td>
				<td>".$nmkat[$bar['kategori']]."</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>
				
				<td>Nama Pemesan</td> 
				<td>:</td>
				<td>".htmlspecialchars($bar['namapemesan'])."</td>
			</tr> 
			<tr>
				<td>" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td>".tanggalnormal($bar['tanggal'])."</td>
				
				<td>Lokasi Pemesan</td> 
				<td>:</td>
				<td>".htmlspecialchars($bar['lokasipemesan'])." - ".htmlspecialchars($nmorg[$bar['lokasipemesan']])."</td>
			</tr>
			<tr>
				<td>Tipe Pembayaran</td> 
				<td>:</td>
				<td>".htmlspecialchars($bar['tipebayar'])."</td>
				
				<td>Nomor Rekening</td> 
				<td>:</td>
				<td>".@$bank[$kodebank[$bar['rekening']]]." - ".@$rekbank[$bar['rekening']]."</td>
			</tr>
			<tr>
				<td></td> 
				<td></td>
				<td></td>
				
				<td>Atas Nama (Penerima)</td> 
				<td>:</td>
				<td>".htmlspecialchars($bar['atasnama'])."</td>
			</tr>
			<tr>
				<td valign=top>Tujuan</td> 
				<td valign=top>:</td>
				<td colspan=4>".str_replace('####','<br>',nl2br(htmlspecialchars($bar['tujuan'])))."</td>
			</tr>";
	$tab.= "</table><hr>";
	$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable style=width:100%>
            <thead><tr class=rowheader>
            <td align=center width=30px>No</td>
            <td align=center>" . $_SESSION['lang']['deskripsi'] . "</td>
            <td align=center width=75px>" . $_SESSION['lang']['rupiah'] . "</td>
        </tr>
		</thead>";
		$no = 0;
        $str = "select * from " . $dbname . ".lgl_bansos where notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==1){
				$xx.=" style=background-color:#F5EEF8 ";
			}
			$tab.="<tr class=rowcontent ".$xx.">";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td align=left>" . htmlspecialchars($bar['deskripsi']) . "</td>";
			$tab.="<td align=right>" . hidezerodecimal($bar['rupiah'],2) . "</td>";
			
			@$trupiah+=$bar['rupiah'];
		}
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>
				<td colspan=2 align=center>TOTAL</td>
				<td align=right>".@hidezerodecimal($trupiah,2)."</td>
				</tr>";
		
        $tab.="</table><hr>";
		
		$tab.="<table class='sortable' cellpadding=5 cellspacing='1' border='0' style=width:100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='loadfilesdetail'>
				</tbody>
			</table>";
		$tab.= "</fieldset>";
		
		$stream = $tab;
		
		if($tipe!='excel'){
			echo $stream;
		} else{
			$nop_ = "bansos_" . date('Ymd_His');
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
								parent.window.alert('Cant convert to excel format');
								</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
				}
				closedir($handle);
			}
		}
		
	break;
	case'getnotransaksi':
		#form belum lengkap: nomor dikosongkan tanpa pesan
		if ($tanggal == '--' || $kodeorg == '') {
			exit();
		}
		bnsCekUnit($kodeorg);
		#format: YYYYMMDD/BNS/INISIAL/NNN ; urutan dihitung per inisial organisasi dan tanggal
		$tempPrd = str_replace('-', '', $tanggal);
		$stmtI = $owlPDO->prepare("select inisialisasiorganisasi from " . $dbname . ".organisasi where kodeorganisasi=?");
		$stmtI->execute(array($kodeorg));
		$inisialOrg = (string)$stmtI->fetchColumn();
		if ($inisialOrg == '') {
			exit('Warning : Inisial / Kode Organisasi belum ada, silahkan ditambah melalui menu : Administrator - Organization Chart');
		}
		$awalanNo = $tempPrd . "/BNS/" . addZero($inisialOrg, 3) . "/";
		$stmtN = $owlPDO->prepare("select max(cast(substring_index(notransaksi,'/',-1) as unsigned)) from " . $dbname . ".lgl_bansos where notransaksi like ?");
		$stmtN->execute(array($awalanNo . '%'));
		$notranbaru = $awalanNo . addZero(((int)$stmtN->fetchColumn()) + 1, 3);
		#nomor dicatat untuk sesi ini; kalau saat disimpan nomor sudah dipakai user lain, simpan ditolak
		$_SESSION['bnsbaru'][$notranbaru] = 1;
		echo $notranbaru;
    break;
	case'getrekening':
		$str=" select a.*,b.namabank as namakodebank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where  a.pemilik='".$lokasipemesan."' and a.status ='1'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		while($bar=$res->fetch()){
			$optb.="<option value=" . $bar['noakun'] . ">" . $bar['namakodebank'] . " - " . $bar['rekening'] . "</option>";
		}
		echo $optb;
	break;
	case'getatasnama':
		$str=" select a.*,b.namabank as namakodebank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where  a.noakun='".$rekening."'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		echo $bar['atasnama'];
	break;
    case'detail':
		bnsCekUnit($kodeorg);
		bnsHeader($notransaksi, 'ubah', true, true);
		if (trim($namapemesan) == '' || trim($tujuan) == '') {
			exit("Warning : Nama pemesan dan tujuan wajib diisi.");
		}
		if (strlen(checkPostGet('namapemesan', '')) > 124 || strlen(checkPostGet('tujuan', '')) > 767 || strlen(checkPostGet('atasnama', '')) > 256 || strlen(checkPostGet('npwp', '')) > 256) {
			exit("Warning : Isian terlalu panjang (pemesan maks 124, tujuan maks 767, atas nama dan NPWP maks 256 karakter).");
		}
		if ($tipebayar != '' && $tipebayar != 'transfer' && $tipebayar != 'cash') {
			exit("Warning : Tipe pembayaran harus transfer atau cash.");
		}
		if ($tipebayar == 'transfer') {
			$stmtV = $owlPDO->prepare("select count(*) from " . $dbname . ".keu_5akunbank where noakun=? and status='1'");
			$stmtV->execute(array($rekening));
			if ($rekening == '' || $stmtV->fetchColumn() == 0) {
				exit("Warning : Pembayaran transfer wajib memilih nomor rekening yang valid.");
			}
		}
		#cek dulu sudah ada atau belum ? jika ada = update
		$str = "select * from " . $dbname . ".lgl_bansos where notransaksi='" . $notransaksi . "'";
		$res=fetchData($str);
		if(count($res)>0){
			# update header
			$str = "update " . $dbname . ".lgl_bansos set namapemesan='".$namapemesan."', tujuan='".$tujuan."', updateby='".$_SESSION['standard']['userid']."', rekening='".$rekening."', tipebayar='".$tipebayar."', atasnama='".$atasnama."', npwp='".$npwp."'";
			if ($tgldokumenstart != '' && $tgldokumenstart != '--' && $tgldokumenstart != '0000-00-00') {
				$str .= ", tgldokumenstart='".$tgldokumenstart."'";
			}
			if ($tgldokumenend != '' && $tgldokumenend != '--' && $tgldokumenend != '0000-00-00') {
				$str .= ", tgldokumenend='".$tgldokumenend."'";
			}
			$str .= " where notransaksi = '" . $notransaksi . "'";
			try {
				$owlPDO->exec($str);			
			} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
		// echo"<pre>";
		// print_r($str);
		// echo"</pre>"; exit('error');
		$query = selectQuery($dbname,'setup_satuan','*');
		$hasil = fetchData($query);
		$optsat = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($hasil as $bar) {
			$optsat.="<option value=" . $bar['satuan'] . ">" . $bar['satuan'] . "</option>";
		}
        OPEN_BOX();
	echo"<fieldset>
        <legend>" . $_SESSION['lang']['form'] . "</legend>
		<table><td valign=top>
		<fieldset style=height:75px>
        <legend>" . $_SESSION['lang']['input'] . "</legend>
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <td align=center width=30px>No</td>
            <td align=center width=350px>" . $_SESSION['lang']['deskripsi'] . "</td>
            <td align=center width=350px>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center width=100px>" . $_SESSION['lang']['jumlah'] . "</td>
            <td align=center rowspan=2 width=50px>" . $_SESSION['lang']['action'] . "</td>
        </tr>
		
		</thead>
        <tr class=rowcontent>
            <td align=center>#</td>    
			<td><input id=deskripsi class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:99%;\"></td>
            <td><select id=satuan style='width:100%;'>".$optsat."</select></td>
			<td><input id=rupiah style=\"width:97%;\" onkeyup=\"z.numberFormat('rupiah',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)';></td>
			
			<td align=center><input type=hidden id=method value='insert'>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
                <img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetail()\" src='images/clear.png'/>
            </td>
        </tr><tr>
			<td colspan=3 align=right>
			<td colspan=1 align=center>
			<img title='Refresh' class=resicon onclick=\"loaddatadetail('".$notransaksi."')\" src='images/refresh2.png'/>
			<img title='" . $_SESSION['lang']['selesai'] . "' class=resicon onclick=\"displayList()\" src='images/foldoq.png'/>
			</td>
        </tr></table></fieldset>
		</td><td  valign=top >
		<fieldset style=height:75px;min-width:310px><legend>Upload</legend>
			<table>
				<tr>
					<td>Filename</td>
					<td>:</td>
					<td>
						<input type='file' name='upload' id='upload' >
					</td>
				</tr>
					<tr>
						<td colspan=2></td>
						<td>Format " . implode(', ', $formatUpload) . ", maksimal " . ($maksUpload / 1048576) . " MB</td>
					</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button id=btnsubmit class=mybutton onclick=\"submitfile()\">Submit</button>
					</td>
				</tr>
			</table>
		</fieldset>
		</td></table>
        </fieldset><hr>";
	echo"
        <fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
        <div id=loaddatadetail>
            <script>loaddatadetail()</script>
         </fieldset>";
	CLOSE_BOX();
	break;

    case'insert':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi belum ada, pilih unit dan tanggal terlebih dahulu.");
		}
		bnsCekUnit($kodeorg);
		if ($tanggal == '--') {
			exit("Warning : Tanggal wajib diisi.");
		}
		if ($tgldokumenstart == '' || $tgldokumenstart == '--' || $tgldokumenstart == '0000-00-00') {
			exit("Warning : Tanggal dokumen dikeluarkan wajib diisi.");
		}
		if ($tgldokumenend == '' || $tgldokumenend == '--' || $tgldokumenend == '0000-00-00') {
			exit("Warning : Tanggal dokumen berakhir wajib diisi.");
		}
		$stmtV = $owlPDO->prepare("select count(*) from " . $dbname . ".organisasi where kodeorganisasi=?");
		$stmtV->execute(array($lokasipemesan));
		if ($lokasipemesan == '' || $stmtV->fetchColumn() == 0) {
			exit("Warning : Lokasi pemesan tidak valid.");
		}
		$stmtV = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_kategoribansos where kode=?");
		$stmtV->execute(array($kategori));
		if ($kategori == '' || $stmtV->fetchColumn() == 0) {
			exit("Warning : Kategori bansos tidak valid.");
		}
		if (trim($namapemesan) == '' || trim($tujuan) == '') {
			exit("Warning : Nama pemesan dan tujuan wajib diisi.");
		}
		if (strlen(checkPostGet('namapemesan', '')) > 124 || strlen(checkPostGet('tujuan', '')) > 767 || strlen(checkPostGet('atasnama', '')) > 256 || strlen(checkPostGet('npwp', '')) > 256) {
			exit("Warning : Isian terlalu panjang (pemesan maks 124, tujuan maks 767, atas nama dan NPWP maks 256 karakter).");
		}
		if ($tipebayar != '' && $tipebayar != 'transfer' && $tipebayar != 'cash') {
			exit("Warning : Tipe pembayaran harus transfer atau cash.");
		}
		if ($tipebayar == 'transfer') {
			$stmtV = $owlPDO->prepare("select count(*) from " . $dbname . ".keu_5akunbank where noakun=? and status='1'");
			$stmtV->execute(array($rekening));
			if ($rekening == '' || $stmtV->fetchColumn() == 0) {
				exit("Warning : Pembayaran transfer wajib memilih nomor rekening yang valid.");
			}
		}
		if (trim($deskripsi) == '' || trim($satuan) == '') {
			exit("Warning : Deskripsi dan satuan wajib diisi.");
		}
		if (strlen(checkPostGet('deskripsi', '')) > 256 || strlen(checkPostGet('satuan', '')) > 256) {
			exit("Warning : Deskripsi dan satuan maksimal 256 karakter.");
		}
		if ($rupiah === '' || $rupiah <= 0) {
			exit("Warning : Jumlah (rupiah) harus lebih dari 0.");
		}
		$hdrAda = bnsHeader($notransaksi, 'ubah', true, true);
		if ($hdrAda) {
			if (isset($_SESSION['bnsbaru'][$notransaksi])) {
				exit("Warning : Nomor " . $notransaksi . " sudah dipakai bansos lain. Tutup form ini lalu buat bansos baru.");
			}
			if ($hdrAda['kodeorg'] != $kodeorg || $hdrAda['tanggal'] != $tanggal || $hdrAda['kategori'] != $kategori) {
				exit("Warning : Unit, tanggal, dan kategori tidak boleh berbeda dari bansos yang sudah tersimpan.");
			}
		}

        $sql = "select count(*) as jmlhrow from " . $dbname . ".lgl_bansos where "
		. " notransaksi='" . $notransaksi . "' and kodeorg ='".$kodeorg."' and kategori='".$kategori."' and tujuan='" . $tujuan . "' and deskripsi='".$deskripsi."' and tanggal='".$tanggal."'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
			exit("Error : Transaksi sudah ada.");
        }
		
		// cek notransaksi sudah ada atau tidak
		// if($notransaksi == ''){
		// 	$notransaksi = generateNoTrans($kodeorg, 'csr', $tanggal, '');
		// }
		// exit("warning " . $variable);

		$sStr = selectQuery($dbname,"lgl_bansos","posting,postingdate", "notransaksi='".$notransaksi."'");
		$qStr = fetchData($sStr);
		if ($qStr[0]['posting'] == 1) {
			exit("warning : Sudah diposting " . date('d-m-Y H:i:s',strtotime($qStr[0]['postingdate'])));
		}
		
		$str = "insert into " . $dbname . ".lgl_bansos (`notransaksi`,`kodeorg`,`tanggal`,`tgldokumenstart`,`tgldokumenend`,`kategori`,`namapemesan`,`lokasipemesan`,`tujuan`,`deskripsi`,`rupiah`,`statuspersetujuan`,`createby`,`createtime`,`updateby`,`updatetime`,`rekening`,`tipebayar`,`atasnama`,`npwp`,`satuan`)
		values ('".$notransaksi."','".$kodeorg."','".$tanggal."','".$tgldokumenstart."','".$tgldokumenend."','".$kategori."','".$namapemesan."','".$lokasipemesan."','".$tujuan."','".$deskripsi."','".$rupiah."','0','" . $_SESSION['standard']['userid'] . "','".$todayhis."','" . $_SESSION['standard']['userid'] . "','".$todayhis."','".$rekening."','".$tipebayar."','".$atasnama."','".$npwp."','".$satuan."')";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		unset($_SESSION['bnsbaru'][$notransaksi]);
		echo $notransaksi;
	break;
    case'delete':
		$hdrH = bnsHeader($notransaksi, 'ubah', true);
		$kodeorg = $hdrH['kodeorg'];
		$tanggal = $hdrH['tanggal'];
		// cek apakah sudah diposting antisipasi jika membuka 2 tab
        $sPos = selectQuery($dbname, "lgl_bansos", 'posting,postingdate', "notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'");
        $qPos = fetchData($sPos);
        if ($qPos[0]['posting'] == 1) {
            exit("warning : Sudah diposting " . date('d-m-Y H:i:s',strtotime($qPos[0]['postingdate'])));
        }

        $str = "delete from " . $dbname . ".lgl_bansos where notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'"; //exit('error'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		$str="select * from ".$dbname.".listfile_lgl_bansos where notransaksi='".$notransaksi."'"; 
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$str="delete from ".$dbname.".listfile_lgl_bansos where notransaksi='".$notransaksi."' and namafile='".$bar['namafile']."'"; 
			try{
				$owlPDO->exec($str);
				$pathx = $path.basename($bar['namafile']);
				if(is_file($pathx)){unlink($pathx);}
			}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
		}
	break;

	case'deletedetail':
		$hdrH = bnsHeader($notransaksi, 'ubah', true);
		$deskripsiAsli = checkPostGet('deskripsi', '');
		$satuanAsli = checkPostGet('satuan', '');
		$stmtC = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_bansos where notransaksi=?");
		$stmtC->execute(array($notransaksi));
		$jlhbrs = $stmtC->fetchColumn();
		#hapus data transaksinya
		try {
			$stmtD = $owlPDO->prepare("delete from " . $dbname . ".lgl_bansos where notransaksi=? and deskripsi=? and satuan=?");
			$stmtD->execute(array($notransaksi, $deskripsiAsli, $satuanAsli));
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		if ($stmtD->rowCount() == 0) {
			exit("Warning : Detail tidak ditemukan.");
		}
		#hapus filenya bila ini detail terakhir
		if ($jlhbrs == 1) {
			$stmtF = $owlPDO->prepare("select namafile from " . $dbname . ".listfile_lgl_bansos where notransaksi=?");
			$stmtF->execute(array($notransaksi));
			foreach ($stmtF->fetchAll(PDO::FETCH_ASSOC) as $barF) {
				try {
					$owlPDO->prepare("delete from " . $dbname . ".listfile_lgl_bansos where notransaksi=? and namafile=?")->execute(array($notransaksi, $barF['namafile']));
					$pathx = $path . basename($barF['namafile']);
					if (is_file($pathx)) {
						unlink($pathx);
					}
				} catch (PDOException $e) {
					echo " Gagal," . addslashes($e->getMessage());
				}
			}
		}
	break;
	case'form_ajukan';
		exit("Warning : Bansos diposting langsung, tidak memakai pengajuan persetujuan.");

		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='".$jenisApp."' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
	
	$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>".$_SESSION['lang']['notransaksi']."</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$notransaksi."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>".$_SESSION['lang']['kepada']."</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:100%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=LEFT><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	
    case'ajukan':
		exit("Warning : Bansos diposting langsung, tidak memakai pengajuan persetujuan.");
		if($kepada=='' or $notransaksi==''){
			exit('Error : Isikan nama penyetuju.');
		}
		# update flag menjadi 1
        $str = "update " . $dbname . ".lgl_bansos set posting='1', statuspersetujuan='0' ,postingdate='" . date('Y-m-d H:i:s') . "',"."postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);
			# insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
					 values ('','".$notransaksi."','".$jenisApp."','1','" . $kepada."','0','','','')";
			try {$owlPDO->exec($str);
			
			} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}			
        } catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
	break;
	
	case'ajukandireksi':
		exit("Warning : Bansos diposting langsung, tidak memakai pengajuan persetujuan.");

		# update flag menjadi 1
        $str = "update " . $dbname . ".lgl_bansos set posting='1', statuspersetujuan='0' ,postingdate='" . date('Y-m-d H:i:s') . "',"."postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);

            $tglapp=date('y-m-d h:i:s');
            $strkry="select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='7' and bagian='BOD'";
			$reskry=$owlPDO->query($strkry) or die(print " Gagal: ".PDOException::getMessage());
			$reskry->setFetchMode(PDO::FETCH_ASSOC);
			while($barkry=$reskry->fetch()){
				# insert ke table approval
				$strapp = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
						`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
						 values ('','".$notransaksi."','".$jenisApp."','1','".$barkry['karyawanid']."','0','','','".$tglapp."')";
				try {
					$owlPDO->exec($strapp);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";die();
				}	
			}
		
        } catch (PDOException $e) {
        	print " Gagal  !: " . $e->getMessage() . "\n";die();
        }
		
        
	break;

    case'loaddatadetail':
		bnsHeader($notransaksi, 'baca', false, true);
		$tab="<table><td valign=top>";
		$tab.="<fieldset><legend>Data</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style=min-width:590px>
            <thead><tr class=rowheader>
            <td align=center width=30px>No</td>
            <td align=center>" . $_SESSION['lang']['deskripsi'] . "</td>
            <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center width=100px>" . $_SESSION['lang']['jumlah'] . "</td>
			<td align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
        </tr>
		</thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".lgl_bansos where notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$nmkode=$nmtantum=$nama=$nmalamat='';
			while ($bar = $res->fetch()) {
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==0){
					$xx.=" style=background-color:#F5EEF8 ";
				}
				$tab.="<tr class=rowcontent ".$xx.">";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left>" . htmlspecialchars($bar['deskripsi']) . "</td>";
				$tab.="<td align=left>" . htmlspecialchars($bar['satuan']) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['rupiah'],2) . "</td>";
				$tab.="<td align=center>";
				$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' 
					onclick=\"deletedetail('" . bnsJs($bar['notransaksi']) . "','" . bnsJs($bar['deskripsi']) . "','" . bnsJs($bar['satuan']) . "');\" >
				</td>";
				@$trupiah+=$bar['rupiah'];
			}
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>
				<td colspan=3 align=center>TOTAL</td>
				<td align=right>".@hidezerodecimal($trupiah,2)."</td>
				<td align=right></td>
				</tr>";
		}
        $tab.="</table>";
		$tab.="</fieldset></td><td valign=top>";
		$tab.="<fieldset><legend>File</legend>";
		$tab.="<table class='sortable' cellspacing='1' border='0' style=min-width:310px>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>";
		$tab.="</fieldset></td></table>";
		$tab.="</fieldset>";

        echo $tab;
	break;

    case'loaddata':
		if ($tipe == 'excel') {
			exit("Warning : Gunakan tombol Excel pada halaman ini.");
		}

    $arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);


    	

		
		$where = bnsWhereList();

        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

		$nmkategori=makeOption($dbname,'lgl_kategoribansos','kode,nama');
        $sql = "select count(*) as jmlhrow from " . $dbname . ".lgl_bansos a  where 1=1 " . $where . " group by a.notransaksi, a.kodeorg";
        #exit('error '.$sql);
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;
		
		$countApp = getCountApproval($jenisApp,'');

       $str = "SELECT sum(a.rupiah) as biaya,(select group_concat(distinct d.notransaksi) from " . $dbname . ".keu_kasbankdt d where d.nodok=a.notransaksi) as kas,a.notransaksi, a.kodeorg, a.tanggal, a.tgldokumenstart, a.tgldokumenend, a.updateby, a.posting,a.kategori,a.namapemesan,a.lokasipemesan,a.tujuan,a.rekening,a.tipebayar,a.atasnama, a.npwp, a.statuspersetujuan,a.posting FROM " . $dbname . ".lgl_bansos  a  
		where 1=1 " . $where . " group by a.notransaksi, a.kodeorg order by a.notransaksi desc ";
		if ($tipe == 'html') {
			$str .= "limit " . $offset . "," . $limit . "";
		}
		
		$tab = "";
		if ($tipe == 'excel') {
			$tab .= "<table border=1>
								<tr class=rowheader>
									<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
									<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
									<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
									<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
									<td align=center>" . $_SESSION['lang']['kategori'] . "</td>
									<td align=center>" . $_SESSION['lang']['biaya'] . "</td>
									<td align=center>" . $_SESSION['lang']['kasbank'] . "</td>
								</tr>";
		}
        $no = $maxdisplay;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $isi = '';
            $no+=1;
			$a=$no%2;
			$xx='';
			if($a==1 && $tipe == 'html'){
				$xx.=" style=background-color:#F5EEF8 ";
			}
			
			
            $tab.="<tr class=rowcontent ".$xx." id=tr_".$no.">";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
            $tab.="<td>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
            $tab.="<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab.="<td align=left>" . $nmkategori[$bar['kategori']] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['biaya'],2) . "</td>";
            $tab.="<td>" . htmlspecialchars((string)$bar['kas']) . "</td>";

						if ($tipe == 'html') {
							$tab.="<td>" . $nmkar[$bar['updateby']] . "</td>";
						}

						if ($tipe == 'html') {
							if($bar['posting'] == 0) {
									$isi .= "<td align=center>
								<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"edit('" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['notransaksi'] . "','" . $bar['kategori'] . "','" . bnsJs($bar['namapemesan']) . "','" . $bar['lokasipemesan'] . "','" . bnsJs(str_replace('####', "\n", $bar['tujuan'])) . "','".$bar['rekening']."','".$bar['tipebayar']."','" . bnsJs($bar['atasnama']) . "','" . bnsJs($bar['npwp']) . "','" . ($bar['tgldokumenstart'] != '0000-00-00' ? tanggalnormal($bar['tgldokumenstart']) : '') . "','" . ($bar['tgldokumenend'] != '0000-00-00' ? tanggalnormal($bar['tgldokumenend']) : '') . "');\" >
							</td>";
									$isi .= "<td align=center>
								<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['notransaksi'] . "');\" >
							</td>";               
									$isi .= "<td align=center>
								<img src=images/red/posting.png class=resicon  title='Posting' onclick=\"postingin('" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['notransaksi'] . "')\">
							</td>";               
							}
				if($bar['posting'] == 1){
					// $isi .= "<td></td><td></td><td></td>";
					if($userPosting == 1){
						// KETIKA TERDAFTAR DI SETUP POSTING
						$isi     .= "<td></td><td></td><td align=center> <img src=images/icons/04/16/04.png class=resicon  title='Posted' onclick=\"unposting('" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['notransaksi'] . "');\"> </td>";
					}else{
						// KETIKA TIDAK TERDAFTAR DI SETUP POSTING
						$isi     .= "<td></td><td></td><td align=center> <img src=images/icons/04/16/02.png class=resicon  title='Posted'> </td>";
					}
				}
	
				
							$isi .= "<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View HTML' onclick=\"html('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
				
				$isi .= "<td align=center><img src=images/pdf.jpg class=resicon class=zImgBtn height='30'  title='View PDF' onclick=\"pdf('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
								$isi .= "<td align=center><img src=images/excel.jpg class=resicon height='30'  title='Excel' onclick=\"previewexcelbansos('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','excel');\" ></td>";
							
							$tab .= $isi;
						}

            $tab .= "</tr>";
        }

				if ($tipe == 'html') {
					$totrows = ceil($jlhbrs / $limit);
					if ($totrows == 0) {
							$totrows = 1;
					}
					$isiRow = '';
					for ($er = 1; $er <= $totrows; $er++) {
							$sel = ($page == $er - 1) ? 'selected' : '';
							$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
					}
					$footd = "";
					$footd.="</tr>
											 <tr><td colspan=17 align=center>";
	
					if ($page == '0') {
							$footd.="<button class=mybutton disabled=true>Prev</button>";
					} else {
							$footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
					}
	
					$footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
	
					if (($page + 1) == $totrows) {
							$footd.="<button class=mybutton disabled=true>Next</button>";
					} else {
							$footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
					}
					$footd.="</td>
							</tr>";
	
					echo $tab . "#####" . $footd;
				}
				if ($tipe == 'excel') {
					$tab .= "</table>";

					$tglNow = date('d-m-Y');
					$nop_ 	= "Report Dokumen Keluar_".$tglNow."";
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
									parent.window.alert('Cant convert to excel format');
								</script>";
							exit;
						} else {
							echo "<script language=javascript1.2>
									window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
						}
						closedir($handle);
					}
				}

    break;
	
case 'submitfile':
		//melebihi post_max_size: PHP mengosongkan seluruh $_POST dan $_FILES
		if (empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
			exit("Warning : Ukuran file melebihi batas, maksimal " . ($maksUpload / 1048576) . " MB.");
		}
		if ($notransaksi == '') {
			exit("Warning : Silahkan isikan detail bansos terlebih dahulu !");
		}
		#cek data
		if (!bnsHeader($notransaksi, 'ubah', true, true)) {
			exit('Warning : Silahkan isikan dan save detail bansos terlebih dahulu !');
		}
		$stmtF = $owlPDO->prepare("select count(*) from " . $dbname . ".listfile_lgl_bansos where notransaksi=? and status='1'");
		$stmtF->execute(array($notransaksi));
		if ($stmtF->fetchColumn() >= 10) {
			exit("Warning : Limit upload hanya 10 file.");
		}
		if (!isset($_FILES['file']) || !is_array($_FILES['file']) || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) {
			exit("Warning : Silahkan pilih file yang akan diupload.");
		}
		$errUpload = $_FILES['file']['error'];
		if ($errUpload == UPLOAD_ERR_INI_SIZE || $errUpload == UPLOAD_ERR_FORM_SIZE) {
			exit("Warning : Ukuran file melebihi batas, maksimal " . ($maksUpload / 1048576) . " MB.");
		}
		if ($errUpload != UPLOAD_ERR_OK) {
			exit("Warning : Upload file gagal (kode " . $errUpload . "), silahkan coba lagi.");
		}
		if ($_FILES['file']['size'] > $maksUpload) {
			exit("Warning : Ukuran file melebihi batas, maksimal " . ($maksUpload / 1048576) . " MB.");
		}
		$filetype = strtolower('.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
		if (!in_array($filetype, $formatUpload)) {
			exit("Warning : Format file upload harus " . implode(', ', $formatUpload) . ".");
		}
		if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
			exit("Warning : Upload file tidak valid.");
		}
		//nama file tetap dikenali dari nama aslinya, ditambah penanda unik supaya tidak menimpa file bansos lain
		$namaAsli = preg_replace('/[^A-Za-z0-9 ._\-\(\)]/', '_', pathinfo($_FILES['file']['name'], PATHINFO_FILENAME));
		$namaAsli = trim(substr($namaAsli, 0, 80));
		if ($namaAsli == '') {
			$namaAsli = 'BANSOS';
		}
		$filename = $namaAsli . '_' . date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 6) . $filetype;
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}
		if (!move_uploaded_file($_FILES['file']['tmp_name'], $path . $filename)) {
			exit("Gagal, file tidak dapat disimpan.");
		}
		try {
			$owlPDO->prepare("insert into " . $dbname . ".listfile_lgl_bansos (notransaksi,namafile,formaticon,status,createdby,createdtime) values (?,?,?,'1',?,?)")
				->execute(array($notransaksi, $filename, $filetype, $_SESSION['standard']['userid'], date('Y-m-d H:i:s')));
		} catch (PDOException $e) {
			unlink($path . $filename);
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
case 'loadfiles':
		$hdrL = bnsHeader($notransaksi, 'baca', false, true);
		$posting = $hdrL ? $hdrL['posting'] : '';
		$no = 0;
		$tab = "";
		$stmtF = $owlPDO->prepare("select * from " . $dbname . ".listfile_lgl_bansos where notransaksi=? and status='1' order by id");
		$stmtF->execute(array($notransaksi));
		$res = $stmtF->fetchAll(PDO::FETCH_ASSOC);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$nfile = htmlspecialchars($val['namafile']);
				$jsFile = bnsJs($val['namafile']);
				$linkFile = htmlspecialchars($path . rawurlencode($val['namafile']), ENT_QUOTES);
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>
					<td style='text-align:center'>
						<a href='".$linkFile."' download><img src=".seticonfile($val['formaticon'])." class=resicon title='".htmlspecialchars($val['formaticon'], ENT_QUOTES)."'></a>
					</td>
					<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$jsFile."')\">".$nfile."</td>
					<td align=center>
						<a href='".$linkFile."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				if($posting==0){
					$tab.="<img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"deletefile('".$val['notransaksi']."','".$jsFile."');\" >";
				}
				$tab.="</td>
					</tr>";
			}
		}
		echo $tab;
	break;
	case'viewfile':
		$tab="";
			$barisF = bnsBarisFile($namafileAsli);
			if (!$barisF) {
				exit("Warning : File tidak ditemukan.");
			}
			bnsHeader($barisF['notransaksi'], 'baca');
			$tab.="<img src='".htmlspecialchars($path.rawurlencode(basename($namafileAsli)), ENT_QUOTES)."' style='width:600px;height:400px;'>";
		
		echo $tab;
	break;
	
	case 'deletefile':
			bnsHeader($notransaksi, 'ubah', true);
		$sStr = selectQuery($dbname,"lgl_bansos","posting,postingdate", "notransaksi='" . $notransaksi . "'");
		$qStr = fetchData($sStr);
		if ($qStr[0]['posting'] == 1) {
			exit("warning : Sudah diposting " . date('d-m-Y H:i:s',strtotime($qStr[0]['postingdate'])));
		}

		$str="delete from ".$dbname.".listfile_lgl_bansos where notransaksi='".$notransaksi."' and namafile='".$namafile."'"; //exit('error'.$str);
		try{
			$owlPDO->exec($str);
				$pathx = $path.basename($namafileAsli);
				#file hanya dihapus bila tidak dipakai bansos lain
				$stmtF = $owlPDO->prepare("select count(*) from ".$dbname.".listfile_lgl_bansos where namafile=?");
				$stmtF->execute(array(basename($namafileAsli)));
				if ($stmtF->fetchColumn() == 0 && is_file($pathx)) {
					unlink($pathx);
				}
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'pdf':
		$hdrP = bnsHeader($notransaksi, 'baca');
		$stmtP = $owlPDO->prepare("select induk from " . $dbname . ".organisasi where kodeorganisasi=?");
		$stmtP->execute(array($hdrP['kodeorg']));
		$ptP = (string)$stmtP->fetchColumn();
		$ptP = ($ptP != '') ? $ptP : $hdrP['kodeorg'];
		$hdpt = setheadreport($ptP, $ptP);
		$rekbank = makeOption($dbname, 'keu_5akunbank', 'noakun,rekening');
		$kodebank = makeOption($dbname, 'keu_5akunbank', 'noakun,namabank');
		$bank = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
		$nmRek = '';
		if ($hdrP['rekening'] != '') {
			$nmRek = (isset($kodebank[$hdrP['rekening']]) && isset($bank[$kodebank[$hdrP['rekening']]]) ? $bank[$kodebank[$hdrP['rekening']]] . ' - ' : '') . (isset($rekbank[$hdrP['rekening']]) ? $rekbank[$hdrP['rekening']] : '');
		}
		$stmtR = $owlPDO->prepare("select deskripsi, satuan, rupiah from " . $dbname . ".lgl_bansos where notransaksi=? order by deskripsi");
		$stmtR->execute(array($notransaksi));
		$barisR = $stmtR->fetchAll(PDO::FETCH_ASSOC);
		$totalR = 0;
		foreach ($barisR as $r) {
			$totalR += $r['rupiah'];
		}
		$idPembuat = ($hdrP['createby'] != 0) ? $hdrP['createby'] : $hdrP['updateby'];
		$jabPembuat = getJabatan($idPembuat);
		$jabPosting = ($hdrP['posting'] == 1 && $hdrP['postingby'] != 0) ? getJabatan($hdrP['postingby']) : '';

		if (!class_exists('PDFDokBNS')) {
			class PDFDokBNS extends FPDF
			{
				public $hdpt = array();
				public $dicetak = '';
				public $wTbl = array(10, 100, 30, 40);

				function Header()
				{
					$w = $this->w - $this->lMargin - $this->rMargin;
					$y0 = $this->tMargin;
					if (file_exists($this->hdpt['logo'])) {
						$this->Image($this->hdpt['logo'], $this->lMargin, $y0, 18);
					}
					$this->SetXY($this->lMargin + 22, $y0 + 1);
					$this->SetFont('Arial', 'B', 13);
					$this->Cell($w - 22, 6, utf8_decode(strtoupper($this->hdpt['nama'])), 0, 1, 'L');
					$this->SetX($this->lMargin + 22);
					$this->SetFont('Arial', '', 8);
					$kontak = trim((string)$this->hdpt['alamat'] . ($this->hdpt['telepon'] != '' ? '  Telp. ' . $this->hdpt['telepon'] : '') . ($this->hdpt['fax'] != '' ? '  Fax. ' . $this->hdpt['fax'] : ''));
					$this->MultiCell($w - 22, 4, utf8_decode($kontak), 0, 'L');
					$yGaris = max($this->GetY(), $y0 + 19) + 1;
					$this->SetLineWidth(0.6);
					$this->Line($this->lMargin, $yGaris, $this->w - $this->rMargin, $yGaris);
					$this->SetLineWidth(0.2);
					$this->SetY($yGaris + 4);
				}

				function Footer()
				{
					$this->SetY(-14);
					$this->SetFont('Arial', 'I', 7);
					$this->Cell(0, 5, utf8_decode($this->dicetak), 0, 0, 'L');
					$this->SetX($this->lMargin);
					$this->Cell(0, 5, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
				}

				function Cukup($h)
				{
					return ($this->GetY() + $h <= $this->PageBreakTrigger);
				}

				#jumlah baris yang dibutuhkan teks pada lebar tertentu (dari contoh resmi FPDF)
				function NbLines($w, $txt)
				{
					$cw = &$this->CurrentFont['cw'];
					if ($w == 0) {
						$w = $this->w - $this->rMargin - $this->x;
					}
					$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
					$s = str_replace("\r", '', $txt);
					$nb = strlen($s);
					if ($nb > 0 && $s[$nb - 1] == "\n") {
						$nb--;
					}
					$sep = -1;
					$i = 0;
					$j = 0;
					$l = 0;
					$nl = 1;
					while ($i < $nb) {
						$ch = $s[$i];
						if ($ch == "\n") {
							$i++;
							$sep = -1;
							$j = $i;
							$l = 0;
							$nl++;
							continue;
						}
						if ($ch == ' ') {
							$sep = $i;
						}
						$l += $cw[$ch];
						if ($l > $wmax) {
							if ($sep == -1) {
								if ($i == $j) {
									$i++;
								}
							} else {
								$i = $sep + 1;
							}
							$sep = -1;
							$j = $i;
							$l = 0;
							$nl++;
						} else {
							$i++;
						}
					}
					return $nl;
				}

				function Bagian($judul)
				{
					if (!$this->Cukup(14)) {
						$this->AddPage();
					}
					$this->Ln(2);
					$this->SetFont('Arial', 'B', 9);
					$this->SetFillColor(225, 232, 240);
					$this->Cell(0, 6, utf8_decode($judul), 0, 1, 'L', true);
					$this->Ln(1);
				}

				#satu baris "label : isi" dengan isi yang bisa lebih dari satu baris
				function KV($label, $isi, $tebal = false)
				{
					$lw = 44;
					$iw = $this->w - $this->lMargin - $this->rMargin - $lw - 4;
					$isi = ($isi === '' || $isi === null) ? '-' : $isi;
					$this->SetFont('Arial', $tebal ? 'B' : '', 9);
					$nb = $this->NbLines($iw, utf8_decode($isi));
					if (!$this->Cukup(5 * $nb)) {
						$this->AddPage();
					}
					$y = $this->GetY();
					$this->SetFont('Arial', '', 9);
					$this->SetXY($this->lMargin, $y);
					$this->Cell($lw, 5, utf8_decode($label), 0, 0, 'L');
					$this->Cell(4, 5, ':', 0, 0, 'C');
					$this->SetFont('Arial', $tebal ? 'B' : '', 9);
					$this->MultiCell($iw, 5, utf8_decode($isi), 0, 'L');
				}

				function HeaderTbl()
				{
					$this->SetFont('Arial', 'B', 8);
					$this->SetFillColor(225, 232, 240);
					foreach (array('No', 'Deskripsi', 'Satuan', 'Jumlah (Rp)') as $i => $j) {
						$this->Cell($this->wTbl[$i], 6, $j, 1, 0, 'C', true);
					}
					$this->Ln();
				}

				#baris tabel dengan tinggi mengikuti sel yang paling panjang
				function Baris($cells, $widths, $aligns, $h = 4.6)
				{
					$this->SetFont('Arial', '', 8);
					$nb = 1;
					foreach ($cells as $i => $c) {
						$nb = max($nb, $this->NbLines($widths[$i], $c));
					}
					$hh = $h * $nb;
					if (!$this->Cukup($hh)) {
						$this->AddPage();
						$this->HeaderTbl();
					}
					$x = $this->lMargin;
					$y = $this->GetY();
					foreach ($cells as $i => $c) {
						$this->Rect($x, $y, $widths[$i], $hh);
						$this->SetXY($x, $y);
						$this->MultiCell($widths[$i], $h, $c, 0, $aligns[$i]);
						$x += $widths[$i];
					}
					$this->SetXY($this->lMargin, $y + $hh);
				}

				function Fit($txt, $w, $f = 8, $gaya = '')
				{
					$txt = utf8_decode($txt);
					$this->SetFont('Arial', $gaya, $f);
					if ($this->GetStringWidth($txt) <= $w - 2) {
						return $txt;
					}
					while (strlen($txt) > 1 && $this->GetStringWidth($txt . '...') > $w - 2) {
						$txt = substr($txt, 0, -1);
					}
					return rtrim($txt) . '...';
				}

				#kotak pembuat / posting
				function Kotak($x, $y, $w, $h, $judul, $kode, $nama, $jabatan, $tanggal)
				{
					$warna = array(
						'dibuat' => array(array(227, 242, 253), array(21, 101, 192), 'DIMINTA', ''),
						'posting' => array(array(232, 245, 233), array(27, 94, 32), 'DIPOSTING', 'Diposting secara digital'),
						'belum' => array(array(245, 245, 245), array(97, 97, 97), 'BELUM DIPOSTING', '')
					);
					$w0 = isset($warna[$kode]) ? $warna[$kode] : $warna['belum'];
					$this->SetDrawColor(120, 120, 120);
					$this->Rect($x, $y, $w, $h);
					$this->SetFillColor(230, 230, 230);
					$this->SetXY($x, $y);
					$this->SetFont('Arial', 'B', 7.5);
					$this->SetTextColor(0, 0, 0);
					$this->Cell($w, 5.5, utf8_decode($judul), 1, 0, 'C', true);
					$this->SetFillColor($w0[0][0], $w0[0][1], $w0[0][2]);
					$this->Rect($x, $y + 5.5, $w, 11, 'F');
					$this->Rect($x, $y + 5.5, $w, 11);
					$this->SetTextColor($w0[1][0], $w0[1][1], $w0[1][2]);
					$this->SetFont('Arial', 'B', 9);
					$this->SetXY($x, $y + 6.5);
					$this->Cell($w, 5, $w0[2], 0, 0, 'C');
					if ($w0[3] != '') {
						$this->SetFont('Arial', 'I', 6.5);
						$this->SetXY($x, $y + 11.5);
						$this->Cell($w, 4, $w0[3], 0, 0, 'C');
					}
					$this->SetTextColor(0, 0, 0);
					$this->SetXY($x, $y + 18);
					$this->SetFont('Arial', 'B', 8);
					$this->Cell($w, 4.5, $this->Fit($nama, $w, 8, 'B'), 0, 2, 'C');
					$this->SetFont('Arial', 'I', 7);
					$this->Cell($w, 4, $this->Fit($jabatan, $w, 7, 'I'), 0, 2, 'C');
					$this->SetFont('Arial', '', 7);
					$this->Cell($w, 4, utf8_decode($tanggal), 0, 2, 'C');
					$this->SetDrawColor(0, 0, 0);
				}
			}
		}

		$pdf = new PDFDokBNS('P', 'mm', 'A4');
		$pdf->hdpt = $hdpt;
		$pdf->dicetak = 'Dicetak oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s');
		$pdf->SetMargins(15, 12, 15);
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(true, 18);
		$pdf->AddPage();
		$lebar = $pdf->w - $pdf->lMargin - $pdf->rMargin;

		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(0, 7, 'FORM BANTUAN SOSIAL (BANSOS)', 0, 1, 'C');
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(0, 5, 'Nomor : ' . $notransaksi, 0, 1, 'C');

		$pdf->Bagian('DATA PEMESAN');
		$pdf->KV('Kategori', isset($nmkat[$hdrP['kategori']]) ? $nmkat[$hdrP['kategori']] : $hdrP['kategori']);
		$pdf->KV('Unit', $hdrP['kodeorg'] . ' - ' . (isset($nmorg[$hdrP['kodeorg']]) ? $nmorg[$hdrP['kodeorg']] : ''));
		$pdf->KV('Tanggal', tanggalnormal($hdrP['tanggal']));
		$pdf->KV('Nama Pemesan', $hdrP['namapemesan']);
		$pdf->KV('Lokasi Pemesan', $hdrP['lokasipemesan'] . ' - ' . (isset($nmorg[$hdrP['lokasipemesan']]) ? $nmorg[$hdrP['lokasipemesan']] : ''));

		$pdf->Bagian('PEMBAYARAN');
		$pdf->KV('Tipe Pembayaran', strtoupper($hdrP['tipebayar']));
		$pdf->KV('Nomor Rekening', $nmRek);
		$pdf->KV('Atas Nama (Penerima)', $hdrP['atasnama']);
		$pdf->KV('NPWP (Penerima)', $hdrP['npwp']);

		$pdf->Bagian('TUJUAN');
		$pdf->SetFont('Arial', '', 9);
		$tujuanTxt = trim(str_replace(array("\r", '####'), array('', "\n"), $hdrP['tujuan']));
		$pdf->MultiCell(0, 5, utf8_decode($tujuanTxt == '' ? '-' : $tujuanTxt), 0, 'L');

		$pdf->Bagian('RINCIAN');
		$pdf->HeaderTbl();
		$no = 0;
		foreach ($barisR as $r) {
			$no++;
			$pdf->Baris(array($no, utf8_decode($r['deskripsi']), utf8_decode($r['satuan']), number_format($r['rupiah'])), $pdf->wTbl, array('C', 'L', 'C', 'R'));
		}
		$pdf->SetFont('Arial', 'B', 8);
		$pdf->Cell($pdf->wTbl[0] + $pdf->wTbl[1] + $pdf->wTbl[2], 6, 'TOTAL', 1, 0, 'C');
		$pdf->Cell($pdf->wTbl[3], 6, number_format($totalR), 1, 1, 'R');
		$pdf->Ln(1);
		$pdf->SetFont('Arial', 'I', 8);
		$pdf->MultiCell(0, 4.5, utf8_decode('Terbilang : ' . ucfirst(bnsTerbilang($totalR)) . ' rupiah'), 0, 'L');

		//pengesahan
		if (!$pdf->Cukup(52)) {
			$pdf->AddPage();
		}
		$pdf->Bagian('PENGESAHAN');
		$gap = 6;
		$wKotak = 58;
		$tinggi = 38;
		$x = $pdf->lMargin;
		$y = $pdf->GetY() + 1;
		$pdf->Kotak($x, $y, $wKotak, $tinggi, 'Diminta oleh', 'dibuat', getNamaKaryawan($idPembuat), $jabPembuat, ($hdrP['createtime'] == '0000-00-00 00:00:00' ? '' : date('d-m-Y H:i', strtotime($hdrP['createtime']))));
		if ($hdrP['posting'] == 1) {
			$pdf->Kotak($x + $wKotak + $gap, $y, $wKotak, $tinggi, 'Diposting oleh', 'posting', getNamaKaryawan($hdrP['postingby']), $jabPosting, ($hdrP['postingdate'] == '0000-00-00 00:00:00' ? '' : date('d-m-Y H:i', strtotime($hdrP['postingdate']))));
		} else {
			$pdf->Kotak($x + $wKotak + $gap, $y, $wKotak, $tinggi, 'Diposting oleh', 'belum', '', '', '');
		}
		$pdf->SetY($y + $tinggi + 2);

		## Print Out
		if ($urlefil == '0') {
			$pdf->Output();
		} else {
			$pdf->Output($urlefil, 'F');
		}
	break;
case 'excellist':
		$rows = bnsDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$kolom = 13;
		$stream = "<table>
			<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
			<tr><td colspan=" . $kolom . "><b>BANSOS</b></td></tr>
			<tr><td colspan=" . $kolom . ">" . htmlspecialchars(bnsInfoFilter()) . "</td></tr>
			<tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
			</table>";
		$stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
			<thead><tr>
				<th align=center bgcolor=#CCCCCC>No</th>
				<th align=center bgcolor=#CCCCCC>No Transaksi</th>
				<th align=center bgcolor=#CCCCCC>Unit</th>
				<th align=center bgcolor=#CCCCCC>Tanggal</th>
				<th align=center bgcolor=#CCCCCC>Kategori</th>
				<th align=center bgcolor=#CCCCCC>Nama Pemesan</th>
				<th align=center bgcolor=#CCCCCC>Lokasi Pemesan</th>
				<th align=center bgcolor=#CCCCCC>Tipe Bayar</th>
				<th align=center bgcolor=#CCCCCC>Atas Nama</th>
				<th align=center bgcolor=#CCCCCC>Total (Rp)</th>
				<th align=center bgcolor=#CCCCCC>Kas/Bank</th>
				<th align=center bgcolor=#CCCCCC>Status</th>
				<th align=center bgcolor=#CCCCCC>Dibuat Oleh</th>
			</tr></thead><tbody>";
		$no = 0;
		foreach ($rows as $r) {
			$no++;
			$stream .= "<tr>
				<td align=center>" . $no . "</td>
				<td>" . htmlspecialchars($r['notransaksi']) . "</td>
				<td>" . htmlspecialchars($r['kodeorg'] . ' - ' . (isset($nmorg[$r['kodeorg']]) ? $nmorg[$r['kodeorg']] : '')) . "</td>
				<td align=center>" . tanggalnormal($r['tanggal']) . "</td>
				<td>" . htmlspecialchars(isset($nmkat[$r['kategori']]) ? $nmkat[$r['kategori']] : $r['kategori']) . "</td>
				<td>" . htmlspecialchars($r['namapemesan']) . "</td>
				<td>" . htmlspecialchars($r['lokasipemesan']) . "</td>
				<td align=center>" . htmlspecialchars($r['tipebayar']) . "</td>
				<td>" . htmlspecialchars($r['atasnama']) . "</td>
				<td align=right>" . round($r['biaya']) . "</td>
				<td>" . htmlspecialchars((string)$r['kas']) . "</td>
				<td>" . bnsLabelStatus($r) . "</td>
				<td>" . htmlspecialchars(isset($nmkar[$r['updateby']]) ? $nmkar[$r['updateby']] : '') . "</td>
			</tr>";
		}
		if ($no == 0) {
			$stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
		}
		$stream .= "</tbody></table>";
		$xls = new HtmlExcel();
		$xls->addSheet("Bansos", $stream);
		$xls->headers("Bansos_" . date('YmdHis') . ".xls");
		echo $xls->buildFile();
		exit;

case 'pdflist':
		$rows = bnsDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$infoFilter = bnsInfoFilter();
		if (!class_exists('PDFListBNS')) {
			class PDFListBNS extends FPDF
			{
				public $colW = array();

				function Header()
				{
					global $hdpt, $infoFilter;
					$width = $this->w - $this->lMargin - $this->rMargin;
					if (file_exists($hdpt['logo'])) {
						$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
					}
					$this->SetFont('Arial', 'B', 11);
					$this->SetXY($this->lMargin + 22, $this->tMargin + 2);
					$this->Cell(160, 6, utf8_decode($hdpt['nama']), 0, 1, 'L');
					$this->SetY($this->tMargin + 15);
					$this->SetFont('Arial', 'B', 13);
					$this->Cell($width, 7, 'BANSOS', 0, 1, 'C');
					$this->SetFont('Arial', '', 8);
					$this->Cell($width, 5, utf8_decode($infoFilter), 0, 1, 'C');
					$this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
					$this->Ln(1);
					$this->SetFillColor(220, 220, 220);
					$this->SetFont('Arial', 'B', 8);
					$judul = array('No', 'No Transaksi', 'Unit', 'Tanggal', 'Kategori', 'Nama Pemesan', 'Lokasi', 'Bayar', 'Atas Nama', 'Total (Rp)', 'Kas/Bank', 'Status', 'Dibuat Oleh');
					foreach ($judul as $i => $j) {
						$this->Cell($this->colW[$i], 6, $j, 1, 0, 'C', true);
					}
					$this->Ln();
				}

				function Footer()
				{
					$this->SetY(-12);
					$this->SetFont('Arial', 'I', 7);
					$this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
				}
			}
		}
		$pdf = new PDFListBNS('L', 'mm', 'A4');
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(false, 16);
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$colFixed = array(7, 36, 20, 18, 20, 28, 16, 14, 26, 24, 18, 20); #semua kolom kecuali Dibuat Oleh
		$pdf->colW = array_merge($colFixed, array($width - array_sum($colFixed)));
		$pdf->AddPage();
		$h = 6;
		$no = 0;
		foreach ($rows as $r) {
			$no++;
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
			$c = $pdf->colW;
			$pdf->SetFont('Arial', '', 7);
			$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
			$pdf->Cell($c[1], $h, bnsFit($pdf, $r['notransaksi'], $c[1]), 1, 0, 'L');
			$pdf->Cell($c[2], $h, $r['kodeorg'], 1, 0, 'C');
			$pdf->Cell($c[3], $h, tanggalnormal($r['tanggal']), 1, 0, 'C');
			$pdf->Cell($c[4], $h, bnsFit($pdf, isset($nmkat[$r['kategori']]) ? $nmkat[$r['kategori']] : $r['kategori'], $c[4]), 1, 0, 'L');
			$pdf->Cell($c[5], $h, bnsFit($pdf, $r['namapemesan'], $c[5]), 1, 0, 'L');
			$pdf->Cell($c[6], $h, $r['lokasipemesan'], 1, 0, 'C');
			$pdf->Cell($c[7], $h, bnsFit($pdf, $r['tipebayar'], $c[7]), 1, 0, 'C');
			$pdf->Cell($c[8], $h, bnsFit($pdf, $r['atasnama'], $c[8]), 1, 0, 'L');
			$pdf->Cell($c[9], $h, number_format($r['biaya']), 1, 0, 'R');
			$pdf->Cell($c[10], $h, bnsFit($pdf, (string)$r['kas'], $c[10]), 1, 0, 'L');
			$pdf->Cell($c[11], $h, bnsFit($pdf, bnsLabelStatus($r), $c[11]), 1, 0, 'L');
			$pdf->Cell($c[12], $h, bnsFit($pdf, isset($nmkar[$r['updateby']]) ? $nmkar[$r['updateby']] : '', $c[12]), 1, 1, 'L');
		}
		if ($no == 0) {
			$pdf->Cell(array_sum($pdf->colW), $h, 'Data tidak ditemukan', 1, 1, 'C');
		}
		$pdf->Output();
		exit;

case 'excelBNS':
		$hdrE = bnsHeader($notransaksi, 'baca');
		$stmtP = $owlPDO->prepare("select induk from " . $dbname . ".organisasi where kodeorganisasi=?");
		$stmtP->execute(array($hdrE['kodeorg']));
		$ptE = (string)$stmtP->fetchColumn();
		$hdpt = setheadreport($ptE != '' ? $ptE : $hdrE['kodeorg'], $ptE != '' ? $ptE : $hdrE['kodeorg']);
		$rekbank = makeOption($dbname, 'keu_5akunbank', 'noakun,rekening');
		$kodebank = makeOption($dbname, 'keu_5akunbank', 'noakun,namabank');
		$bank = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
		$e = function ($v) {
			return htmlspecialchars((string)$v);
		};
		$kv = function ($label, $isi) use ($e) {
			return "<tr><td bgcolor=#EEEEEE>" . $e($label) . "</td><td colspan=3 align=left>" . $e($isi) . "</td></tr>";
		};
		$nmRek = (isset($kodebank[$hdrE['rekening']]) && isset($bank[$kodebank[$hdrE['rekening']]]) ? $bank[$kodebank[$hdrE['rekening']]] : '') . ($hdrE['rekening'] != '' && isset($rekbank[$hdrE['rekening']]) ? ' - ' . $rekbank[$hdrE['rekening']] : '');
		$stream = "<table>
			<tr><td colspan=4><b>" . $e($hdpt['nama']) . "</b></td></tr>
			<tr><td colspan=4><b>BANSOS</b></td></tr>
			<tr><td colspan=4>Nomor : " . $e($notransaksi) . "</td></tr>
			<tr><td colspan=4>Ditarik oleh " . $e($_SESSION['empl']['name']) . " (" . $e($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=4>&nbsp;</td></tr>
			</table>";
		$stream .= "<table cellspacing=1 cellpadding=3 border=1>"
			. $kv('No Transaksi', $notransaksi)
			. $kv('Kategori', isset($nmkat[$hdrE['kategori']]) ? $nmkat[$hdrE['kategori']] : $hdrE['kategori'])
			. $kv('Unit', $hdrE['kodeorg'] . ' - ' . (isset($nmorg[$hdrE['kodeorg']]) ? $nmorg[$hdrE['kodeorg']] : ''))
			. $kv('Tanggal', tanggalnormal($hdrE['tanggal']))
			. $kv('Nama Pemesan', $hdrE['namapemesan'])
			. $kv('Lokasi Pemesan', $hdrE['lokasipemesan'] . ' - ' . (isset($nmorg[$hdrE['lokasipemesan']]) ? $nmorg[$hdrE['lokasipemesan']] : ''))
			. $kv('Tipe Pembayaran', $hdrE['tipebayar'])
			. $kv('Nomor Rekening', $nmRek)
			. $kv('Atas Nama (Penerima)', $hdrE['atasnama'])
			. $kv('NPWP (Penerima)', $hdrE['npwp'])
			. $kv('Tujuan', str_replace('####', ' ', $hdrE['tujuan']))
			. $kv('Status', bnsLabelStatus($hdrE))
			. $kv('Dibuat oleh', (isset($nmkar[$hdrE['updateby']]) ? $nmkar[$hdrE['updateby']] : '') . ' (' . $hdrE['updatetime'] . ')')
			. "</table><br />";
		$stream .= "<table cellspacing=1 cellpadding=3 border=1>
			<tr><th bgcolor=#CCCCCC>No</th><th bgcolor=#CCCCCC>Deskripsi</th><th bgcolor=#CCCCCC>Satuan</th><th bgcolor=#CCCCCC>Jumlah (Rp)</th></tr>";
		$stmtE = $owlPDO->prepare("select deskripsi, satuan, rupiah from " . $dbname . ".lgl_bansos where notransaksi=? order by deskripsi");
		$stmtE->execute(array($notransaksi));
		$no = 0;
		$total = 0;
		foreach ($stmtE->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$no++;
			$total += $r['rupiah'];
			$stream .= "<tr><td align=center>" . $no . "</td><td>" . $e($r['deskripsi']) . "</td><td align=center>" . $e($r['satuan']) . "</td><td align=right>" . round($r['rupiah'], 2) . "</td></tr>";
		}
		$stream .= "<tr><td colspan=3 align=center><b>TOTAL</b></td><td align=right><b>" . round($total, 2) . "</b></td></tr></table>";
		$xls = new HtmlExcel();
		$xls->addSheet("Bansos", $stream);
		$xls->headers("Bansos_" . preg_replace('/[^A-Za-z0-9]/', '', $notransaksi) . "_" . date('YmdHis') . ".xls");
		echo $xls->buildFile();
		exit;
	case 'posting':
        $hdrH = bnsHeader($notransaksi, 'ubah', true);
        $kodeorg = $hdrH['kodeorg'];
        $tanggal = $hdrH['tanggal'];
        // cek apakah sudah diposting antisipasi jika membuka 2 tab
        $sPos = selectQuery($dbname, 'lgl_bansos', 'posting,postingdate', "notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'");
        $qPos = fetchData($sPos);
        if ($qPos[0]['posting'] == 1) {
            exit("warning : Sudah diposting " . date('d-m-Y H:i:s',strtotime($qPos[0]['postingdate'])));
        }

        $data = array();
        $data['posting'] = 1;
        $data['postingby'] = $_SESSION['standard']['userid'];
        $data['postingdate'] = date("YmdHis");
        
        try {
            $sPost = updateQuery($dbname, 'lgl_bansos', $data, "notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'");
            $owlPDO->exec($sPost);
        } catch (Throwable $e) {
            echo "DB Error : " . $e->getMessage();
            die();
        }    
    break;

	case 'unposting':
        $hdrH = bnsHeader($notransaksi, 'ubah');
        $kodeorg = $hdrH['kodeorg'];
        $tanggal = $hdrH['tanggal'];
        // cek apakah sudah unposting antisipasi jika membuka 2 tab
        $sPos = selectQuery($dbname, "lgl_bansos", 'posting', "notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'");
        $qPos = fetchData($sPos);
        if ($qPos[0]['posting'] == 0) {
            exit("warning : Sudah diunposting");
        }

        // cek siapa yang posting
        if($userPosting != 1){
            exit("warning : Anda tidak diizinkan untuk unposting!");
        }

        $data = array();
        $data['posting'] = 0;
        $data['postingby'] = $_SESSION['standard']['userid'];
        $data['postingdate'] = '0000-00-00 00:00:00';

        try {
            $sUnPost = updateQuery($dbname, "lgl_bansos", $data, "notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'");
            $owlPDO->exec($sUnPost);
        } catch (Throwable $e) {
            echo "DB Error : " . $e->getMessage();
            die();
        }
    break;
}

# Fungsi di pakai pada saat menyimpan hasil dari <textarea></textarea>
# $a => value yang akan di rubah
# $x => akan di replace menggunakan ??, default = ####
/* function replaceEnter($a, $x="####"){
	$a = nl2br($a);
	$i = explode('<br />',$a);
	$no =''; $t='';
	foreach($i as $r => $e){
		$no+=1;
		if($no < count($i)){
			$t.=trim($e).$x;
		}else{
			$t.=trim($e);
		}
	}
	return $t;
}
 */
// function getJabatan($karyawanid){
	// global $dbname;
	// global $owlPDO;
	// $i = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$karyawanid."'");
	// $x = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$i[$karyawanid]."'");
	// return $x[$i[$karyawanid]];
// }

function tambahspasi($a, $x=20){
	$q=strlen($a);
	$t='';
	$s=($x-$q);
	if($q<$x){
		for($i=1;$i<$s;$i++){
			$t.="x";
		}
	}
	return $a.$t;
}
?>	