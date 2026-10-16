<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('lib/fpdf.php');

$method=		isset($_POST['method'])? $_POST['method']: '';
#tarikan Excel/PDF dibuka lewat GET (iframe popup); proses lain tetap hanya lewat POST
if ($method == '' and isset($_GET['method']) and in_array($_GET['method'], array('excellist', 'pdflist'))) {
	$method = $_GET['method'];
}
$pages = checkPostGet('page','');
$nopo=			isset($_POST['nopo'])? $_POST['nopo']: '';
#nomor PO hanya berisi huruf, angka, garis miring, titik, strip, garis bawah, dan spasi
if ($nopo != '' and !preg_match('/^[A-Za-z0-9\/._ -]+$/', $nopo)) {
	exit('warning: Nomor PO tidak valid');
}
$user_id=		$_SESSION['standard']['userid'];
$rlse_user_id=	isset($_POST['id_user'])? $_POST['id_user']: '';
$this_date=		date("Y-m-d");
$tglR=			isset($_POST['tglR'])? $_POST['tglR']: '';
$ket=			isset($_POST['ket'])? $_POST['ket']: '';
$texkKrsi=		isset($_POST['texkKrsi'])? $_POST['texkKrsi']: '';
$tipeApp = "PO";
$jenisApp = "PO";

#=== Filter list PO serta tarikan Excel/PDF (dipakai bersama oleh list, Excel, dan PDF) ===
#filter dibersihkan dulu: no PO bebas (di-escape), tanggal (dd-mm-yyyy atau yyyy-mm[-dd]), status 1-5, supplier alfanumerik
if (!function_exists('rpoFilter')) {
	function rpoFilter()
	{
		global $owlPDO;
		$f = array('nopo' => '', 'tgl' => '', 'tglfull' => false, 'status' => '', 'supplier' => '', 'tahun' => '');
		$nopo = trim((string)checkPostGet('txtSearchrpo', ''));
		if ($nopo != '' and $nopo != 'undefined' and $nopo != 'null') {
			$f['nopo'] = substr($owlPDO->quote($nopo), 1, -1);
		}
		$tgl = trim((string)checkPostGet('tglCarirpo', ''));
		if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $tgl, $m)) {
			$f['tgl'] = $m[3] . '-' . $m[2] . '-' . $m[1];
			$f['tglfull'] = true;
		} elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
			$f['tgl'] = $tgl;
			$f['tglfull'] = true;
		} elseif (preg_match('/^\d{4}-\d{2}$/', $tgl)) {
			$f['tgl'] = $tgl;
		}
		$st = (string)checkPostGet('filterId', '');
		if (in_array($st, array('1', '2', '3', '4', '5'), true)) {
			$f['status'] = $st;
		}
		$sp = trim((string)checkPostGet('filterSupplier', ''));
		if ($sp != '' and preg_match('/^[A-Za-z0-9_\-\.]+$/', $sp)) {
			$f['supplier'] = $sp;
		}
		#tahun PO (kosong = semua tahun)
		$th = trim((string)checkPostGet('filterTahun', ''));
		if (preg_match('/^\d{4}$/', $th)) {
			$f['tahun'] = $th;
		}
		return $f;
	}
}
if (!function_exists('rpoWhereList')) {
	function rpoWhereList()
	{
		$f = rpoFilter();
		$where = "";
		if ($f['nopo'] != '') {
			$where .= " and nopo LIKE  '%" . $f['nopo'] . "%' ";
		}
		if ($f['tgl'] != '') {
			$where .= $f['tglfull'] ? " and tanggal='" . $f['tgl'] . "' " : " and tanggal LIKE '" . $f['tgl'] . "%' ";
		}
		if ($f['supplier'] != '') {
			$where .= " and kodesupplier='" . $f['supplier'] . "' ";
		}
		if ($f['status'] == '1') {
			##RELEASE
			$where .= " and statuspo in ('2','3') and stat_release='1' and closed='0'";
		}
		if ($f['status'] == '2') {
			##UNRELEASE
			$where .= " and statuspo in ('0','1')";
		}
		if ($f['status'] == '3') {
			##BECOME OUT STANDING
			$where .= " and statuspo in ('2','3') and closed='1' and keteranganclose like '%,tanggal tutup : %'";
		}
		if ($f['status'] == '4') {
			##CLOSE
			$where .= " and statuspo in ('2','3') and closed='1' and (keterangan like '%,tanggal tutup : %' or keteranganclose like '%Tutup By System%')";
		}
		if ($f['status'] == '5') {
			##CENCEL
			$where .= " and statuspo in ('4') and closed='1' and (keteranganclose like '%,tanggal cancel : %')";
		}
		if ($f['tahun'] != '') {
			$where .= " and tanggal>='" . $f['tahun'] . "-01-01' and tanggal<='" . $f['tahun'] . "-12-31' ";
		}
		##sesuai detail akses
		$where .= " and left(kodeunit,4) in (" . getOrgDetail(2) . ")";
		return $where;
	}
}
#status, PIC, dan keterangan satu PO (dipakai tampilan list dan tarikan Excel/PDF)
if (!function_exists('rpoStatusPo')) {
	function rpoStatusPo($val)
	{
		global $dbname;
				##CEK INVOICE
				$strx="select count(*) as juminv from ".$dbname.".keu_tagihanht where nopo='".$val['nopo']."' and posting=1 ";
				$resx=fetchdata($strx);
				$juminv=$resx[0]['juminv'];
				
				##KETERANGAN
				$keterangan="";
				if($juminv>0){
					$keterangan="- Sudah ada Invoice<br>";
				}
				
				##USER RELEASE
				$pic="";
				$optPic = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['useridreleasae']."'");
				$picrelease = $optPic[$val['useridreleasae']];
				$optPic = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['userclosed']."'");
				$picclose = $optPic[$val['userclosed']];
				
				##STATUS PO
				$postatus="";
				$dsplycancel="";
				$dsplyclose="";
				$dsplybos="";
				if($val['statuspo']=='0'){
					$postatus="Unrelease";
					$keterangan.="- Belum diajukan";
					$dsplycancel="style='display:none'";
					$dsplyclose="style='display:none'";
					$dsplybos="style='display:none'";
				}else if($val['statuspo']=='1'){
					$postatus="Unrelease";
					$keterangan.="- Proses persetujuan";
					$dsplyclose="style='display:none'";
					$dsplybos="style='display:none'";
				}else if($val['statuspo']=='2'){
					if($val['closed']=='0'){
						$postatus="Release";
						$keterangan.="";
						$pic=$picrelease;
					}else{
						if(strpos($val['keteranganclose'], ",tanggal tutup : ")){
							$postatus = "Become Out Standing";
							$keterangan.=$val['keteranganclose'];
							$pic=$picclose;
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
						}
						if(strpos($val['keterangan'], ",tanggal tutup : ")){
							$postatus = "Close";
							$keterangan.=$val['keterangan'];
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
							$dsplybos="style='display:none'";
							$pic=$picclose;
						}
						if(strpos($val['keteranganclose'], "Tutup By System")){
							$postatus = "Close";
							$keterangan.="- Semua Barang Sudah Diterimakan";
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
							$dsplybos="style='display:none'";
							$pic="bysystem";
						}
					}
				}else if($val['statuspo']=='3'){
					if($val['closed']=='0'){
						$postatus="Release";
						$keterangan.="- Sudah ada penerimaan";
						$pic=$picrelease;
						$dsplycancel="style='display:none'";
					}else{
						if(strpos($val['keteranganclose'], ",tanggal tutup : ")){
							$postatus = "Become Out Standing";
							$keterangan.="- Sudah ada penerimaan<br>";
							$keterangan.="- ".$val['keteranganclose'];
							$pic=$picclose;
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
						}
						if(strpos($val['keterangan'], ",tanggal tutup : ")){
							$postatus = "Close";
							$keterangan.="- Sudah ada penerimaan<br>";
							$keterangan.="- ".$val['keterangan'];
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
							$dsplybos="style='display:none'";
							$pic=$picclose;
						}
						if(strpos($val['keteranganclose'], "Tutup By System")){
							$postatus = "Close";
							$keterangan.="- Semua Barang Sudah Diterimakan";
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
							$dsplybos="style='display:none'";
							$pic="bysystem";
						}
					}
				}else if($val['statuspo']=='4'){
					$postatus = "Cancel";
					$keterangan.="- ".$val['keteranganclose'];
					$pic=$picclose;
					$dsplycancel="style='display:none'";
					$dsplyclose="style='display:none'";
					$dsplybos="style='display:none'";
				}

				if($postatus == 'Close'){
					$unclosebtn="";
				}else{
					$unclosebtn="style='display:none'";
				}
		return array('postatus' => $postatus, 'keterangan' => $keterangan, 'pic' => $pic, 'dsplycancel' => $dsplycancel, 'dsplyclose' => $dsplyclose, 'dsplybos' => $dsplybos, 'unclosebtn' => $unclosebtn);
	}
}
#teks keterangan (berisi tag br) menjadi teks biasa
if (!function_exists('rpoTeksDatar')) {
	function rpoTeksDatar($html, $pemisah)
	{
		$t = preg_replace('/<br\s*\/?>/i', "\n", (string)$html);
		$t = trim(strip_tags($t));
		$t = preg_replace('/\n+/', $pemisah, $t);
		return trim($t);
	}
}
#nomor dan tanggal penerimaan gudang sebuah PO (teks biasa)
if (!function_exists('rpoPenerimaan')) {
	function rpoPenerimaan($nopo)
	{
		global $dbname, $owlPDO;
		$baris = array();
		foreach (array('log_transaksiht', 'log_noninventory') as $tb) {
			$stmt = $owlPDO->prepare("select notransaksi,tanggal from " . $dbname . "." . $tb . " where nopo=?");
			$stmt->execute(array($nopo));
			foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
				$baris[] = 'No.Transaksi : ' . $b['notransaksi'] . ' (Tanggal Penerimaan : ' . $b['tanggal'] . ')';
			}
		}
		return implode("\n", $baris);
	}
}
#keterangan filter yang aktif untuk kop tarikan
if (!function_exists('rpoInfoFilter')) {
	function rpoInfoFilter()
	{
		global $dbname, $rpoTerpotong;
		$f = rpoFilter();
		$arrStatus = array('1' => 'Release', '2' => 'Unrelease', '3' => 'Become Out Standing', '4' => 'Close', '5' => 'Cancel');
		$info = array();
		$info[] = 'Tahun: ' . ($f['tahun'] != '' ? $f['tahun'] : 'Semua');
		$info[] = 'Status: ' . ($f['status'] != '' ? $arrStatus[$f['status']] : 'Semua');
		if ($f['supplier'] != '') {
			$nmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
			$info[] = 'Supplier: ' . (isset($nmsup[$f['supplier']]) ? $nmsup[$f['supplier']] : $f['supplier']);
		}
		if ($f['nopo'] != '') {
			$info[] = 'No. PO: ' . stripslashes($f['nopo']);
		}
		if ($f['tgl'] != '') {
			$info[] = 'Tanggal: ' . ($f['tglfull'] ? tanggalnormal($f['tgl']) : $f['tgl']);
		}
		if (!empty($rpoTerpotong)) {
			$info[] = 'Dibatasi 3000 data terbaru, persempit filter bila perlu';
		}
		return implode(' | ', $info);
	}
}
#PO sesuai filter untuk tarikan Excel/PDF (dibatasi supaya tidak berat)
if (!function_exists('rpoDataList')) {
	function rpoDataList($maks = 3000)
	{
		global $dbname, $rpoTerpotong;
		$rows = fetchdata("select * from " . $dbname . ".log_poht where nopo<>'' " . rpoWhereList() . " order by tanggal desc limit " . ((int)$maks + 1));
		$rpoTerpotong = count($rows) > $maks;
		return array_slice($rows, 0, $maks);
	}
}
#warna latar status persetujuan satu tingkat (0 menunggu, 1 disetujui, 2 dikoreksi, 3 ditolak)
if (!function_exists('rpoWarnaApproval')) {
	function rpoWarnaApproval($status)
	{
		$w = array('0' => '#ffeb9c', '1' => '#c6efce', '2' => '#fce4d6', '3' => '#ffc7ce');
		return isset($w[(string)$status]) ? $w[(string)$status] : '';
	}
}
#warna status PO: latar dan teks (hijau Release, kuning Unrelease, oranye Become Out Standing, abu Close, merah Cancel)
if (!function_exists('rpoWarnaStatus')) {
	function rpoWarnaStatus($status)
	{
		$w = array(
			'Release' => array('#c6efce', '#006100'),
			'Unrelease' => array('#ffeb9c', '#9c5700'),
			'Become Out Standing' => array('#fce4d6', '#a04000'),
			'Close' => array('#d9d9d9', '#404040'),
			'Cancel' => array('#ffc7ce', '#9c0006')
		);
		return isset($w[$status]) ? $w[$status] : array('', '');
	}
}
#pejabat/jabatan sebuah tingkat persetujuan PO beserta statusnya
if (!function_exists('rpoLevelApproval')) {
	function rpoLevelApproval($i, $nopo, $kodeunit, $jenisApp)
	{
		global $dbname;
		$strx="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$i."' and kodeunit='".$kodeunit."'";
		$resx=fetchData($strx);
		$tipeapp = $resx[0]['tipe'];
		$departemenapp = $resx[0]['departemen'];
		$tipekaryawanapp = $resx[0]['tipekaryawan'];
		$jabatanapp = $resx[0]['jabatan'];

		$arrDetail = detailApprove($i,$nopo,$jenisApp);
		if($tipeapp=='1' && $arrDetail['status']!=''){
			if($arrDetail['status']!='1'){
				if($departemenapp!=''){
					$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
					$arrDetail['nama'] = $opttipe[$departemenapp];
				}

				if($tipekaryawanapp!=''){
					$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
					$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
				}

				if($jabatanapp!='0'){
					$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
					$arrDetail['nama'] = $opttipe[$jabatanapp];
				}
			}
		}
		return $arrDetail;
	}
}
#jumlah transaksi penerimaan gudang sebuah PO dan tanggal terakhirnya
if (!function_exists('rpoPenerimaanRingkas')) {
	function rpoPenerimaanRingkas($nopo)
	{
		global $dbname, $owlPDO;
		$hasil = array('jumlah' => 0, 'terakhir' => '');
		foreach (array('log_transaksiht', 'log_noninventory') as $tb) {
			$stmt = $owlPDO->prepare("select count(*) as n, max(tanggal) as t from " . $dbname . "." . $tb . " where nopo=?");
			$stmt->execute(array($nopo));
			$b = $stmt->fetch(PDO::FETCH_ASSOC);
			$hasil['jumlah'] += (int)$b['n'];
			if ($b['t'] != '' and $b['t'] > $hasil['terakhir']) {
				$hasil['terakhir'] = $b['t'];
			}
		}
		return $hasil;
	}
}
#PO milik unit yang boleh diakses pengguna (untuk popup detail)
if (!function_exists('rpoAmbilPo')) {
	function rpoAmbilPo($nopo)
	{
		global $dbname, $owlPDO;
		$stmt = $owlPDO->prepare("select * from " . $dbname . ".log_poht where nopo=? and left(kodeunit,4) in (" . getOrgDetail(2) . ")");
		$stmt->execute(array($nopo));
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
}
#alasan sebuah PO berstatus Close tidak boleh di-unclose (kosong bila boleh); dipakai tombol, popup, dan proses unclose
if (!function_exists('rpoAlasanUnclose')) {
	function rpoAlasanUnclose($nopo)
	{
		global $dbname, $owlPDO;
		$stmt = $owlPDO->prepare("select tipepo, userclosed, keteranganclose from " . $dbname . ".log_poht where nopo=?");
		$stmt->execute(array($nopo));
		$po = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$po) {
			return 'PO tidak ditemukan';
		}
		#ambil jumlah PO
		$stmt = $owlPDO->prepare("select sum(jumlahpesan-jmlhstlhclose) from " . $dbname . ".log_podt where nopo=?");
		$stmt->execute(array($nopo));
		$totBrg = $stmt->fetchColumn();
		#ambil sudah diterimakan
		if ($po['tipepo'] == 'PO') {
			$stmt = $owlPDO->prepare("select sum(jumlah) from " . $dbname . ".log_transaksidt where nopo=? and statussaldo='1'");
		} else {
			$stmt = $owlPDO->prepare("select sum(jumlah) from " . $dbname . ".log_noninventorydt_vw where nopo=? and posting='1'");
		}
		$stmt->execute(array($nopo));
		$totBrgterima = $stmt->fetchColumn();
		if ($po['userclosed'] != '0000000000' || $po['userclosed'] == '') {
			return "PO Sudah di close oleh user " . getNamaKaryawan($po['userclosed']) . " dengan catatan = " . $po['keteranganclose'] . " ";
		}
		if ($totBrg == $totBrgterima) {
			return "Barang sudah diterimakan semua!!!";
		}
		return '';
	}
}
#invoice sebuah PO (langsung atau lewat rincian tagihan); tanpagr = jumlah baris rincian yang belum menyebut no penerimaan
if (!function_exists('rpoInvoicePo')) {
	function rpoInvoicePo($nopo)
	{
		global $dbname, $owlPDO;
		$stmt = $owlPDO->prepare("select h.noinvoice, h.tipeinvoice, h.posting, (select count(*) from " . $dbname . ".keu_tagihandt d where d.noinvoice=h.noinvoice and (d.notransaksi is null or d.notransaksi='')) as tanpagr from " . $dbname . ".keu_tagihanht h where h.nopo=? or h.noinvoice in (select noinvoice from " . $dbname . ".keu_tagihandt where nopo=?) order by h.tanggal, h.noinvoice");
		$stmt->execute(array($nopo, $nopo));
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}
#alasan Cancel/Close/Become Out Standing ditolak karena invoice (kosong bila boleh)
#cancel: ada invoice apa pun; close dan bos: ada invoice diposting yang rinciannya belum terikat no penerimaan
if (!function_exists('rpoAlasanInvoice')) {
	function rpoAlasanInvoice($nopo, $aksi, $inv = null)
	{
		if ($inv === null) {
			$inv = rpoInvoicePo($nopo);
		}
		$kena = array();
		foreach ($inv as $i) {
			if ($aksi == 'cancel' or ($i['posting'] == '1' and $i['tanpagr'] > 0)) {
				$kena[] = $i['noinvoice'] . ' (' . $i['tipeinvoice'] . ', ' . ($i['posting'] == '1' ? 'diposting' : 'belum diposting') . ')';
			}
		}
		if (count($kena) == 0) {
			return '';
		}
		$daftar = implode(', ', array_slice($kena, 0, 3)) . (count($kena) > 3 ? ' dan ' . (count($kena) - 3) . ' lainnya' : '');
		if ($aksi == 'cancel') {
			return 'Tidak dapat cancel PO, sudah ada invoice ' . $daftar . '. Batalkan atau buka posting invoice tersebut terlebih dahulu.';
		}
		return 'Tidak dapat ' . ($aksi == 'bos' ? 'Become Out Standing' : 'close') . ' PO, ada invoice diposting yang belum terikat nomor penerimaan: ' . $daftar . '. Batalkan atau buka posting invoice tersebut terlebih dahulu.';
	}
}
#jumlah barang sebuah PO yang sudah diterima (satuan PO), sama dengan perhitungan di dialog Become Out Standing
if (!function_exists('rpoSudahDiterima')) {
	function rpoSudahDiterima($nopo, $kodebarang)
	{
		global $dbname, $owlPDO;
		$sudah = 0;
		$stmt = $owlPDO->prepare("select sum(jumlahpesan) as jumlahpesan, sum(jumlahterima) as jumlahterima, kodebarang, nopo, satuanpo, satuanterima from " . $dbname . ".log_po_terima_vwx where nopo=? and kodebarang=?");
		$stmt->execute(array($nopo, $kodebarang));
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $v) {
			$sudah += $v['jumlahterima'];
			if ($v['satuanpo'] != $v['satuanterima']) {
				$stk = $owlPDO->prepare("select jumlah from " . $dbname . ".log_5stkonversi where satuankonversi=? and darisatuan=? and kodebarang=?");
				$stk->execute(array($v['satuanpo'], $v['satuanterima'], $v['kodebarang']));
				$sudah += ($v['jumlahterima'] * (float)$stk->fetchColumn());
			}
		}
		#penerimaan non-inventory (jasa/SO) yang sudah diposting tidak ada di view di atas
		$stmt = $owlPDO->prepare("select sum(jumlah) from " . $dbname . ".log_noninventorydt_vw where nopo=? and kodebarang=? and posting='1'");
		$stmt->execute(array($nopo, $kodebarang));
		$sudah += (float)$stmt->fetchColumn();
		return $sudah;
	}
}
#catatan di form Cancel/Close/Become Out Standing bila PO sudah ada penerimaan, pembayaran, atau invoice yang belum diposting (hanya informasi, tidak memblokir)
if (!function_exists('rpoCatatanAksi')) {
	function rpoCatatanAksi($nopo)
	{
		global $dbname, $owlPDO;
		$baris = array();
		$stmt = $owlPDO->prepare("select count(*) as n, coalesce(sum(post=1),0) as p from " . $dbname . ".log_transaksiht where nopo=? and tipetransaksi='1'");
		$stmt->execute(array($nopo));
		$a = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt = $owlPDO->prepare("select count(*) as n, coalesce(sum(posting=1),0) as p from " . $dbname . ".log_noninventory where nopo=?");
		$stmt->execute(array($nopo));
		$b = $stmt->fetch(PDO::FETCH_ASSOC);
		$n = (int)$a['n'] + (int)$b['n'];
		if ($n > 0) {
			$baris[] = 'Sudah ada ' . $n . ' penerimaan (' . ((int)$a['p'] + (int)$b['p']) . ' diposting).';
		}
		$stmt = $owlPDO->prepare("select count(distinct notransaksi) from " . $dbname . ".keu_kasbankdt where nodok=?");
		$stmt->execute(array($nopo));
		$byr = (int)$stmt->fetchColumn();
		if ($byr > 0) {
			$baris[] = 'Ada pembayaran kas/bank atas PO ini (' . $byr . ' transaksi).';
		}
		$belum = array();
		foreach (rpoInvoicePo($nopo) as $i) {
			if ($i['posting'] != '1') {
				$belum[] = $i['noinvoice'] . ' (' . $i['tipeinvoice'] . ')';
			}
		}
		if (count($belum) > 0) {
			$baris[] = 'Ada invoice belum diposting: ' . implode(', ', $belum) . '.';
		}
		if (count($baris) == 0) {
			return '';
		}
		return "<div style='margin-bottom:8px;padding:6px;font-size:12px;line-height:1.4;background-color:#ffeb9c;color:#9c5700'><b>Perhatian</b><br>" . htmlspecialchars(implode(' ', $baris)) . " Pastikan sudah dikoordinasikan dengan gudang dan keuangan.</div>";
	}
}
#apakah masih ada barang PO yang bisa dikembalikan lewat Become Out Standing (hitungan sama dengan dialog BOS)
if (!function_exists('rpoAdaSisaBos')) {
	function rpoAdaSisaBos($nopo)
	{
		global $dbname, $owlPDO;
		$stmt = $owlPDO->prepare("select kodebarang, jumlahpesan, jmlhstlhclose from " . $dbname . ".log_podt where nopo=?");
		$stmt->execute(array($nopo));
		$terima = array();
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			if (!isset($terima[$r['kodebarang']])) {
				$terima[$r['kodebarang']] = rpoSudahDiterima($nopo, $r['kodebarang']);
			}
			if ($r['jumlahpesan'] - $terima[$r['kodebarang']] - $r['jmlhstlhclose'] > 0) {
				return true;
			}
		}
		return false;
	}
}
#aksi (Cancel/Close/Become Out Standing/Unclose) yang tersedia untuk sebuah PO beserta alasan bila tidak tersedia
if (!function_exists('rpoAksiPo')) {
	function rpoAksiPo($val)
	{
		global $dbname, $owlPDO;
		$sembunyi = "style='display:none'";
		$st = rpoStatusPo($val);
		$d = array('cancel' => $st['dsplycancel'], 'close' => $st['dsplyclose'], 'bos' => $st['dsplybos'], 'unclose' => $st['unclosebtn']);
		$alasan = array('cancel' => '', 'close' => '', 'bos' => '', 'unclose' => '');
		$s = (string)$val['statuspo'];
		foreach (array('cancel', 'close', 'bos') as $k) {
			if ($d[$k] == '') {
				continue;
			}
			if ($s == '0') {
				$alasan[$k] = 'PO belum diajukan';
			} elseif ($s == '1') {
				$alasan[$k] = 'PO masih proses persetujuan';
			} elseif ($s == '4') {
				$alasan[$k] = 'PO sudah di-cancel';
			} elseif ($st['postatus'] == 'Close' or $st['postatus'] == 'Become Out Standing') {
				$alasan[$k] = 'PO sudah berstatus ' . $st['postatus'];
			} elseif ($k == 'cancel' and $s == '3') {
				$alasan[$k] = 'Sudah ada penerimaan barang';
			}
		}
		if ($d['unclose'] != '') {
			$alasan['unclose'] = 'Hanya untuk PO berstatus Close';
		} else {
			$alasan['unclose'] = rpoAlasanUnclose($val['nopo']);
			if ($alasan['unclose'] != '') {
				$d['unclose'] = $sembunyi;
			}
		}
		#penerimaan lewat PO referensi SO: semua aksi ditutup
		$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".log_transaksiht where nopo in (select nopo from " . $dbname . ".log_sorefrensi where noso=?)");
		$stmt->execute(array($val['nopo']));
		if ($stmt->fetchColumn() > 0) {
			foreach (array('cancel', 'close', 'bos') as $k) {
				$d[$k] = $sembunyi;
				if ($alasan[$k] == '') {
					$alasan[$k] = 'SO ini sudah diterimakan lewat PO referensinya';
				}
			}
		}
		#barang dalam transit: Cancel dan Become Out Standing ditutup
		$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".log_transit where nopo=?");
		$stmt->execute(array($val['nopo']));
		if ($stmt->fetchColumn() > 0) {
			foreach (array('cancel', 'bos') as $k) {
				$d[$k] = $sembunyi;
				if ($alasan[$k] == '') {
					$alasan[$k] = 'Barang dalam transit';
				}
			}
		}
		#PO dipakai sebagai referensi SO: semua aksi ditutup
		$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".log_sorefrensi where nopo=?");
		$stmt->execute(array($val['nopo']));
		if ($stmt->fetchColumn() > 0) {
			foreach (array('cancel', 'close', 'bos') as $k) {
				$d[$k] = $sembunyi;
				if ($alasan[$k] == '') {
					$alasan[$k] = 'PO ini dipakai sebagai referensi SO';
				}
			}
		}
		#Become Out Standing hanya bila masih ada sisa barang yang belum diterima
		if ($d['bos'] == '' and !rpoAdaSisaBos($val['nopo'])) {
			$d['bos'] = $sembunyi;
			$alasan['bos'] = 'Semua barang sudah diterima, tidak ada sisa untuk dikembalikan';
		}
		#invoice: Cancel ditutup bila ada invoice; Close dan Become Out Standing bila ada invoice diposting yang belum terikat penerimaan
		if ($d['cancel'] == '' or $d['close'] == '' or $d['bos'] == '') {
			$inv = rpoInvoicePo($val['nopo']);
			foreach (array('cancel', 'close', 'bos') as $k) {
				if ($d[$k] == '') {
					$alasan[$k] = rpoAlasanInvoice($val['nopo'], $k, $inv);
					if ($alasan[$k] != '') {
						$d[$k] = $sembunyi;
					}
				}
			}
		}
		return array('dsplycancel' => $d['cancel'], 'dsplyclose' => $d['close'], 'dsplybos' => $d['bos'], 'unclosebtn' => $d['unclose'], 'alasan' => $alasan, 'postatus' => $st['postatus']);
	}
}
#tanggal (atau tanggal-jam) untuk jejak PO; kosong bila belum terisi
if (!function_exists('rpoTgl')) {
	function rpoTgl($t)
	{
		$t = trim((string)$t);
		if ($t == '' or substr($t, 0, 4) == '0000') {
			return '-';
		}
		$hasil = tanggalnormal(substr($t, 0, 10));
		if (strlen($t) > 10) {
			$hasil .= ' ' . substr($t, 11, 5);
		}
		return $hasil;
	}
}
#nama karyawan (kosong bila id tidak terisi)
if (!function_exists('rpoNamaOrang')) {
	function rpoNamaOrang($id)
	{
		$id = trim((string)$id);
		if ($id == '' or $id == '0' or $id == '0000000000') {
			return '';
		}
		return getNamaKaryawan($id);
	}
}
#jejak satu PO: dibuat, persetujuan, release, penerimaan, invoice, pembayaran, close/cancel, lalu aksi yang masih tersedia
if (!function_exists('rpoJejakHtml')) {
	function rpoJejakHtml($po)
	{
		global $dbname, $owlPDO;
		$nopo = $po['nopo'];
		$sHijau = 'background-color:#c6efce;color:#006100;font-weight:bold';
		$sKuning = 'background-color:#ffeb9c;color:#9c5700;font-weight:bold';
		$sMerah = 'background-color:#ffc7ce;color:#9c0006;font-weight:bold';
		$sOranye = 'background-color:#fce4d6;color:#a04000;font-weight:bold';
		$sAbu = 'background-color:#d9d9d9;color:#404040;font-weight:bold';
		$baris = array();
		$stPo = rpoStatusPo($po);

		##PO dibuat
		$baris[] = array('PO dibuat', rpoTgl($po['tanggal']), $nopo, 'Purchaser : ' . rpoNamaOrang($po['purchaser']), 'Dibuat', $sHijau);

		##persetujuan
		$stmt = $owlPDO->prepare("select level, karyawanid, status, komentar, tanggal from " . $dbname . ".approval where notransaksi=? and jenispersetujuan='PO' order by level, nourut");
		$stmt->execute(array($nopo));
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$arrAppSt = array('0' => array('Menunggu', $sKuning), '1' => array('Disetujui', $sHijau), '2' => array('Dikoreksi', $sOranye), '3' => array('Ditolak', $sMerah));
		foreach ($app as $a) {
			$s = isset($arrAppSt[(string)$a['status']]) ? $arrAppSt[(string)$a['status']] : array('-', $sAbu);
			$baris[] = array('Persetujuan ' . $a['level'], rpoTgl($a['tanggal']), rpoNamaOrang($a['karyawanid']), $a['komentar'], $s[0], $s[1]);
		}
		if (count($app) == 0 and $po['statuspo'] == '0') {
			$baris[] = array('Persetujuan', '-', '', 'PO belum diajukan', 'Belum diajukan', $sKuning);
		}

		##release
		if ($po['statuspo'] == '2' or $po['statuspo'] == '3' or ($po['statuspo'] == '4' and substr((string)$po['tglrelease'], 0, 4) != '0000' and $po['tglrelease'] != '')) {
			$baris[] = array('Release', rpoTgl($po['tglrelease']), rpoNamaOrang($po['useridreleasae']), '', 'Release', $sHijau);
		} elseif ($po['statuspo'] != '4') {
			$baris[] = array('Release', '-', '', '', 'Belum release', $sKuning);
		}

		##invoice PO ini (langsung atau lewat rincian tagihan)
		$stmt = $owlPDO->prepare("select noinvoice, tipeinvoice, tanggal, tanggalinvoice, nilaiinvoice, posting, postingdate, postingby, createby, createtime from " . $dbname . ".keu_tagihanht where nopo=? or noinvoice in (select noinvoice from " . $dbname . ".keu_tagihandt where nopo=?) order by tanggal, noinvoice");
		$stmt->execute(array($nopo, $nopo));
		$inv = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$noInv = array();
		$invPosting = 0;
		foreach ($inv as $i) {
			$noInv[] = $i['noinvoice'];
			if ($i['posting'] == '1') {
				$invPosting++;
			}
		}
		#penerimaan mana yang sudah ditarik ke invoice
		$tarik = array();
		$adaRefGr = false;
		if (count($noInv) > 0) {
			$ph = implode(',', array_fill(0, count($noInv), '?'));
			$stmt = $owlPDO->prepare("select distinct notransaksi, noinvoice from " . $dbname . ".keu_tagihandt where noinvoice in (" . $ph . ") and notransaksi<>''");
			$stmt->execute($noInv);
			foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
				$tarik[$t['notransaksi']][] = $t['noinvoice'];
				$adaRefGr = true;
			}
		}

		##penerimaan
		$gr = array();
		$stmt = $owlPDO->prepare("select notransaksi, tanggal, kodegudang, post, postedby from " . $dbname . ".log_transaksiht where nopo=? and tipetransaksi='1' order by tanggal, notransaksi");
		$stmt->execute(array($nopo));
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$gr[] = array($r['notransaksi'], $r['tanggal'], 'Gudang ' . $r['kodegudang'], $r['post'] == '1', rpoNamaOrang($r['postedby']), '');
		}
		$stmt = $owlPDO->prepare("select notransaksi, tanggal, unit, posting, postedby, postedtime from " . $dbname . ".log_noninventory where nopo=? order by tanggal, notransaksi");
		$stmt->execute(array($nopo));
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$gr[] = array($r['notransaksi'], $r['tanggal'], 'Unit ' . $r['unit'], $r['posting'] == '1', rpoNamaOrang($r['postedby']), $r['postedtime']);
		}
		foreach ($gr as $g) {
			$ket = $g[2];
			if ($g[3]) {
				$ket .= ' | Diposting' . ($g[4] != '' ? ' oleh ' . $g[4] : '') . ($g[5] != '' ? ' ' . rpoTgl($g[5]) : '');
			}
			if (isset($tarik[$g[0]])) {
				$ket .= ' | Ditarik di invoice ' . implode(', ', $tarik[$g[0]]);
			} elseif ($adaRefGr) {
				$ket .= ' | Belum ditarik invoice';
			}
			$baris[] = array('Penerimaan', rpoTgl($g[1]), $g[0], $ket, $g[3] ? 'Diposting' : 'Belum diposting', $g[3] ? $sHijau : $sKuning);
		}
		if (count($gr) == 0 and ($po['statuspo'] == '2' or $po['statuspo'] == '3')) {
			$baris[] = array('Penerimaan', '-', '', '', 'Belum ada penerimaan', $sAbu);
		}

		##invoice
		foreach ($inv as $i) {
			$ket = 'Terima dokumen ' . rpoTgl($i['tanggalinvoice']) . ' | Nilai Rp ' . number_format((float)$i['nilaiinvoice'], 0, '.', ',');
			$ket .= ' | Dibuat ' . rpoNamaOrang($i['createby']) . ' ' . rpoTgl($i['createtime']);
			if ($i['posting'] == '1') {
				$ket .= ' | Diposting ' . rpoNamaOrang($i['postingby']) . ' ' . rpoTgl($i['postingdate']);
			}
			$baris[] = array('Invoice (' . $i['tipeinvoice'] . ')', rpoTgl($i['tanggal']), $i['noinvoice'], $ket, $i['posting'] == '1' ? 'Diposting' : 'Belum diposting', $i['posting'] == '1' ? $sHijau : $sKuning);
		}
		if (count($inv) == 0 and ($po['statuspo'] == '2' or $po['statuspo'] == '3')) {
			$baris[] = array('Invoice', '-', '', '', 'Belum ada invoice', $sAbu);
		}

		##pembayaran (kas/bank) atas invoice PO ini atau yang langsung menyebut no PO
		$param = array($nopo);
		$whrInv = '';
		if (count($noInv) > 0) {
			$whrInv = " or a.keterangan1 in (" . implode(',', array_fill(0, count($noInv), '?')) . ")";
			$param = array_merge($param, $noInv);
		}
		$stmt = $owlPDO->prepare("select a.notransaksi, b.tanggal, b.posting, b.postingtime, a.keterangan1 as noinvoice, sum(a.jumlah) as jumlah from " . $dbname . ".keu_kasbankdt a left join " . $dbname . ".keu_kasbankht b on a.notransaksi=b.notransaksi where a.nodok=?" . $whrInv . " group by a.notransaksi, b.tanggal, b.posting, b.postingtime, a.keterangan1 order by b.tanggal, a.notransaksi");
		$stmt->execute($param);
		$bayar = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($bayar as $b) {
			$ket = ($b['noinvoice'] != '' ? 'Untuk invoice ' . $b['noinvoice'] : 'Atas PO ini (tanpa invoice)') . ' | Rp ' . number_format((float)$b['jumlah'], 0, '.', ',');
			if ($b['posting'] == '1') {
				$ket .= ' | Diposting' . (rpoTgl($b['postingtime']) != '-' ? ' ' . rpoTgl($b['postingtime']) : '');
			}
			$baris[] = array('Pembayaran', rpoTgl($b['tanggal']), $b['notransaksi'], $ket, $b['posting'] == '1' ? 'Diposting' : 'Belum diposting', $b['posting'] == '1' ? $sHijau : $sKuning);
		}
		if (count($bayar) == 0 and count($inv) > 0) {
			$baris[] = array('Pembayaran', '-', '', '', 'Belum dibayar', $sAbu);
		}

		##close / cancel / become out standing
		if ($stPo['postatus'] == 'Close' or $stPo['postatus'] == 'Become Out Standing' or $stPo['postatus'] == 'Cancel') {
			$teks = $stPo['postatus'] == 'Cancel' ? $po['keteranganclose'] : ($stPo['postatus'] == 'Close' ? $po['keterangan'] : $po['keteranganclose']);
			$tgl = '-';
			if (preg_match('/tanggal (?:tutup|cancel) : (\d{2})-(\d{2})-(\d{4})/', (string)$teks, $m)) {
				$tgl = $m[1] . '-' . $m[2] . '-' . $m[3];
			}
			$pic = $stPo['pic'] == 'bysystem' ? 'Sistem' : rpoNamaOrang($po['userclosed']);
			$sty = $stPo['postatus'] == 'Cancel' ? $sMerah : ($stPo['postatus'] == 'Close' ? $sAbu : $sOranye);
			$baris[] = array($stPo['postatus'], $tgl, $pic, rpoTeksDatar($teks, ' | '), $stPo['postatus'], $sty);
		}

		$tab = "<div style='font-weight:bold;margin:6px 0 3px 0'>Jejak PO</div>";
		$tab .= "<table class=sortable cellspacing=1 cellpadding=4 border=0 style='width:100%'>
			<thead><tr class=rowheader>
				<th align=center style='width:130px'>Tahap</th>
				<th align=center style='width:110px'>Tanggal</th>
				<th align=center style='width:200px'>Referensi</th>
				<th align=center>Keterangan</th>
				<th align=center style='width:130px'>Status</th>
			</tr></thead><tbody>";
		foreach ($baris as $b) {
			$tab .= "<tr class=rowcontent style='vertical-align:top'>
				<td>" . htmlspecialchars($b[0]) . "</td>
				<td align=center>" . htmlspecialchars($b[1]) . "</td>
				<td>" . htmlspecialchars($b[2]) . "</td>
				<td>" . htmlspecialchars($b[3]) . "</td>
				<td align=center style='" . $b[5] . "'>" . htmlspecialchars($b[4]) . "</td>
			</tr>";
		}
		$tab .= "</tbody></table>";

		##aksi yang masih tersedia
		$aksi = rpoAksiPo($po);
		$tab .= "<div style='font-weight:bold;margin:10px 0 3px 0'>Aksi pada PO ini</div>";
		$tab .= "<table class=sortable cellspacing=1 cellpadding=4 border=0 style='width:100%'>
			<thead><tr class=rowheader>
				<th align=center style='width:160px'>Aksi</th>
				<th align=center style='width:130px'>Status</th>
				<th align=center>Keterangan</th>
			</tr></thead><tbody>";
		$arrAksi = array('cancel' => array('Cancel', 'dsplycancel'), 'close' => array('Close', 'dsplyclose'), 'bos' => array('Become Out Standing', 'dsplybos'), 'unclose' => array('Unclose', 'unclosebtn'));
		foreach ($arrAksi as $k => $a) {
			$ada = ($aksi[$a[1]] == '');
			$tab .= "<tr class=rowcontent>
				<td>" . $a[0] . "</td>
				<td align=center style='" . ($ada ? $sHijau : $sAbu) . "'>" . ($ada ? 'Tersedia' : 'Tidak tersedia') . "</td>
				<td>" . htmlspecialchars($ada ? '' : $aksi['alasan'][$k]) . "</td>
			</tr>";
		}
		$tab .= "</tbody></table>";
		if (count($inv) > 0) {
			$tab .= "<div style='margin-top:6px;padding:6px;" . str_replace('font-weight:bold', 'font-weight:normal', $sKuning) . "'>PO ini sudah punya " . count($inv) . " invoice (" . $invPosting . " diposting). Cancel ditutup bila ada invoice; Close dan Become Out Standing ditutup bila ada invoice diposting yang belum terikat nomor penerimaan.</div>";
		}
		return $tab;
	}
}
#angka tanpa nol di belakang koma yang tidak perlu
if (!function_exists('rpoAngka')) {
	function rpoAngka($v)
	{
		$s = number_format((float)$v, 2, '.', ',');
		return rtrim(rtrim($s, '0'), '.');
	}
}

#atribut sel Excel untuk status (latar dan huruf tebal berwarna)
if (!function_exists('rpoStyleExcel')) {
	function rpoStyleExcel($status)
	{
		$w = rpoWarnaStatus($status);
		return $w[0] != '' ? " bgcolor=" . $w[0] . " style='color:" . $w[1] . ";font-weight:bold'" : '';
	}
}
#teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
if (!function_exists('rpoFit')) {
	function rpoFit($pdf, $txt, $w, $f = 7)
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
}
switch ($method) {
	case 'release_po' :
		$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$res=$query->fetch();
		if(($res['persetujuan1']!='') || ($res['persetujuan2']!='')) {
			if(($res['stat_release']==0 or $res['stat_release']==2) && ($res['useridreleasae']==0000000000)) {		
				$unopo="update ".$dbname.".log_poht set stat_release='1', useridreleasae='".$rlse_user_id."',tglrelease='".$this_date."' where nopo='".$nopo."' ";
				try{
					$owlPDO->exec($unopo); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			} else {
				exit("warning:PO Sudah Di Release atau sedang koreksi");
			}
		} else {
			exit("Error: Belum Ada Penanda Tangan Dari P0 ".$nopo."");
		}
		break;
	
	case 'un_release_po' :
		$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$res=$query->fetch();

		if(($res['stat_release']==1) && ($res['useridreleasae']==$rlse_user_id))
		{		
			$unopo="update ".$dbname.".log_poht set statuspo='0', stat_release='0', useridreleasae='0000000000',tglrelease='0000-00-00' where nopo='".$nopo."' ";
			try{
				$owlPDO->exec($unopo); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		else
		{
			echo "warning:You Don`t Have Autorize to Unrelease This PO No. ".$nopo;
			exit();
		}
		break;
	
	case 'list_new_data_release_po':
		$txt_search = checkPostGet('txtSearchrpo','');
		$txt_tgl = checkPostGet('tglCarirpo','');
		$txt_filter = checkPostGet('filterId','');
		$filterSupplier = checkPostGet('filterSupplier','');

		##Cek jumlah penerimaan di gudang
		$brgCompr=array();
		$totBrg=array();
		$nomoPo="";
		$str="select sum(jumlahpesan) as jumlahpesan,sum(jumlahterima) as jumlahterima, sum(jumlahclose) as jumlahclose,kodebarang,nopo,satuanpo,satuanterima from ".$dbname.".log_po_terima_vw where nopo<>'' and closed=0 group by nopo,kodebarang order by nopo asc";
		#= Untuk Cek
		// $str="select sum(jumlahpesan) as jumlahpesan,sum(jumlahterima) as jumlahterima, sum(jumlahclose) as jumlahclose,kodebarang,nopo,satuanpo,satuanterima from ".$dbname. ".log_po_terima_vw where nopo<>'' and kodebarang='347040130' group by nopo,kodebarang order by nopo asc";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $val){
			#= Add Abdul
			// Data sesuai pengurangannya
				$selisih=$val['jumlahpesan']-$val['jumlahterima']-$val['jumlahclose'];
			#= Keluarkan satuan konversi dari Line 96 karena tidak terbaca jika di taruh situ
				if($val['satuanpo']!=$val['satuanterima']){
					$strx="select jumlah from ".$dbname.".log_5stkonversi where satuankonversi='".$val['satuanpo']."' and darisatuan='".$val['satuanterima']."' and kodebarang='".$val['kodebarang']."'";
					$resx=fetchdata($strx);
					$nilaikonversi=$resx[0]['jumlah'];
					// $hasilkonversi=($nilaikonversi*$val['jumlahterima'])+($val['jumlahclose']);
					$hasilkonversi=($val['jumlahpesan']/$nilaikonversi);
					if($hasilkonversi==$val['jumlahpesan']){
						$selisih=0;
					} else {
						$selisih=$hasilkonversi-$val['jumlahterima'];
					}
				}
			if($nomoPo!=$val['nopo']){
				// Jika selisih di taruh di sini, nilai dalam 1 po akan ikut data pertama (salah naruh)
				// $selisih=$val['jumlahpesan']-$val['jumlahterima']-$val['jumlahclose'];
				$nomoPo=$val['nopo'];
				
				$strx="select sum(jumlahpesan) as jmlbrg from ".$dbname.".log_podt where nopo='".$val['nopo']."'";
				$resx=fetchdata($strx);
				$totBrg[$nomoPo]=$resx[0]['jmlbrg'];
			}
			if($selisih==0){
				$brgCompr[$val['nopo']]+=$val['jumlahterima'];
			}
			// echo "<br/>";
			// echo $val['satuanpo'];
			// echo "<br/>";
			// echo $val['satuanterima'];
			// echo "<br/>";
			// echo $val['jumlahpesan'];
			// echo "<br/>";
			// echo $hasilkonversi;
			// echo "<br/>";
			// echo $selisih;
			# Di buat untuk cek, ketika ada PO barang yang belum di terimakan semua (jumlahnya), update flag nya dari script
			# line 120 agar barang bisa di terimakan
			if($selisih > 0) {
				$str="update ".$dbname.".log_poht set keteranganclose='',closed=0 where nopo='".$val['nopo']."'";
					try{$owlPDO->exec($str); }catch(PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n".$sUpdateClose; die(); }
			}
			#= End Abdul
		}
	   
		if(count($brgCompr)!=0){
			foreach($brgCompr as $nopoLst=>$isiDt){
				if($totBrg[$nopoLst]==$isiDt){
					$str="update ".$dbname.".log_poht set keteranganclose='No PO:".$nopoLst.",Semua Barang Sudah Diterimakan,Tutup By System',closed=1 where nopo='".$nopoLst."'";
					try{$owlPDO->exec($str); }catch(PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n".$sUpdateClose; die(); }
				}
			}
		}
		
		
		$limit=20;
        $page=0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		
		$no=(($page*$limit));
		
		
		
			$where = rpoWhereList();
			$whrdetailakses = "";
		
		$countApp = getCountApproval($jenisApp,'');
		$str="select count(*) as jmlhrow from ".$dbname.".log_poht where nopo<>'' ".$where." ".$whrdetailakses."  order by tanggal desc";
		$res=fetchdata($str);
		$jlhbrs=$res[0]['jmlhrow'];
		$colspan=(13+$countApp);
		$tab="";
		
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='".$colspan."' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$str="select * from ".$dbname.".log_poht where nopo<>'' ".$where." ".$whrdetailakses." order by tanggal desc limit ".$offset.",".$limit." ";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
					$stPo = rpoStatusPo($val);
					$keterangan = $stPo['keterangan'];
					$pic = $stPo['pic'];
					$postatus = $stPo['postatus'];
					$dsplycancel = $stPo['dsplycancel'];
					$dsplyclose = $stPo['dsplyclose'];
					$dsplybos = $stPo['dsplybos'];
					$unclosebtn = $stPo['unclosebtn'];

				$aksiPo = rpoAksiPo($val);
				$dsplycancel = $aksiPo['dsplycancel'];
				$dsplyclose = $aksiPo['dsplyclose'];
				$dsplybos = $aksiPo['dsplybos'];
				$unclosebtn = $aksiPo['unclosebtn'];


				$ringkas = rpoPenerimaanRingkas($val['nopo']);

				#periksa chat
				$strChat="select *  from ".$dbname.".log_po_chat where nopo='".$val['nopo']."'";
				$resChat=$owlPDO->query($strChat) or die(print " Gagal: ".PDOException::getMessage());
				if(owlBaris($resChat)>0)
				{
					$ingChat="<img src='images/chat1.png' onclick=\"loadPOChat('".$val['nopo']."',event);\" class=resicon>";
				}
				else
				{
					$ingChat="<img src='images/chat0.png'  onclick=\"loadPOChat('".$val['nopo']."',event);\" class=resicon>";
				}
				
				$namasupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier');
				$tab.="<tr id='tr_".$no."' ".($val['stat_release']==2?"bgcolor='orange'":"class=rowcontent")." style=vertical-align:top;>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td id=td_".$no.">".$val['nopo']."</td>";
				$tab.="<td style='text-align:center;min-width:70px'>".tanggalnormal($val['tanggal'])."</td>";
				$tab.="<td align=center>".$val['kodeorg']."</td>";
				$tab.="<td align=center>".$namasupplier[$val['kodesupplier']]."</td>";
				$tab.="<td align=left>".nl2br($val['uraian'])."</td>";
				$tab.="<td ".$bgcolor." align=center>".$ingChat."</td>";
				
				##BEGIN APPROVAL##
				for($i=1;$i<=$countApp;$i++){
					$arrDetail = rpoLevelApproval($i,$val['nopo'],$val['kodeunit'],$jenisApp);
					$bgApp = ($arrDetail['nama'] != '') ? rpoWarnaApproval($arrDetail['status']) : '';
					$tab.="<td align=center style='vertical-align:top".($bgApp != '' ? ";background-color:".$bgApp : "")."'>
						<b>".$arrDetail['nama']."</b>";
						if($arrDetail['nama']!=''){
							$tab.="<br>Status : ".(($arrDetail['status']=='9'||$arrDetail['status']=='')?"":$arrDetail['namastatus']);
							if($arrDetail['komentar']!=''){
								$tab.="<br>Comment : ".$arrDetail['komentar'];
								$tab.="<br><i>".tanggalnormal($arrDetail['tanggal'])."</i>";
							}
						}
					$tab.="</td>";
				}
				##END APPROVAL##
				
				$wrn = rpoWarnaStatus($postatus);
				$tab.="<td align=center".($wrn[0] != '' ? " style='background-color:".$wrn[0].";color:".$wrn[1].";font-weight:bold'" : "").">".$postatus."</td>";
				$tab.="<td align=center>".$pic."</td>";
				$linkPop = "color:#001f6b;font-weight:bold;text-decoration:underline;cursor:pointer;white-space:nowrap";
				$ketBaris = array();
				foreach (preg_split('/<br\s*\/?>/i', (string)$keterangan) as $ln) {
					$ln = trim(preg_replace('/^-\s*/', '', trim($ln)));
					if ($ln == '' or $ln == 'Sudah ada penerimaan') {
						continue;
					}
					$ketBaris[] = $ln;
				}
				$ketHtml = "";
				foreach ($ketBaris as $kb) {
					$ketHtml .= "<div style='margin-bottom:4px;line-height:1.35'>" . $kb . "</div>";
				}
				$tab.="<td style='min-width:200px;vertical-align:top;text-align:center'>".$ketHtml."</td>";
				$linkTelusur = "<a style=\"" . $linkPop . "\" onclick=\"lihatPenerimaanRPO('" . htmlspecialchars(addslashes($val['nopo']), ENT_QUOTES) . "',event)\">Telusur PO</a>";
				if ($ringkas['jumlah'] > 0) {
					$gudangHtml = "<b>" . $ringkas['jumlah'] . "</b> penerimaan<br><span style='font-size:11px'>Terakhir " . tanggalnormal($ringkas['terakhir']) . "</span><br>" . $linkTelusur;
				} else {
					$gudangHtml = (($val['statuspo'] >= 2) ? "<span style='color:#808080'>Belum ada penerimaan</span><br>" : "") . $linkTelusur;
				}
				$tab.="<td style='width:170px;vertical-align:top;text-align:center'>".$gudangHtml."</td>";
				$tab.="<td align=center>
					<img src=images/zoom.png class=resicon  title='Print' onclick=\"previewlinkpemenang('".$val['nodph']."', '".$val['kodesupplier']."', 'Detail Riwayat Perbandingan Harga' ,event)\">&nbsp;
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$val['nopo']."','','log_slave_print_detail_po',event);\">
				</td>";
				#tombol aksi: hanya yang tersedia, tersusun ke bawah dengan lebar sama
				$stBtn = "display:block;width:140px;margin:0 auto 3px auto";
				$tab.="<td style='vertical-align:top;text-align:center;width:150px'>";
				if($dsplycancel==''){
					$tab.="<button class=mybutton style='".$stBtn."' onclick=\"cancelpoform('Cancel','".$val['nopo']."',event)\">Cancel</button>";
				}
				if($dsplyclose==''){
					$tab.="<button class=mybutton style='".$stBtn."' onclick=\"closeedPo('Close','".$val['nopo']."',event)\">Close</button>";
				}
				if($unclosebtn==''){
					$tab.="<button class=mybutton style='".$stBtn."' onclick=\"unclose('".$val['nopo']."',event)\">Unclose</button>";
				}
				if($dsplybos==''){
					$tab.="<button class=mybutton style='".$stBtn."' onclick=\"bospo('Become Out Standing','".$val['nopo']."',event)\">Become Out Standing</button>";
				}
				$tab.="</td>";
				$tab.="</tr>";
			}
			
			## PAGING
			$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loadData','getPage');
			$tab.="</table>";
		}
		
		echo $tab;
		break;
		
	case 'detail_penerimaan':
		$po = rpoAmbilPo($nopo);
		if (!$po) {
			exit("warning:Data PO tidak ditemukan atau tidak ada akses");
		}
		$namasup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
		$nmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
		$tab = "<div style='padding:10px'>";
		$tab .= "<table cellspacing=0 cellpadding=2 style='margin-bottom:8px'>
			<tr><td>No. PO</td><td>:</td><td><b>" . htmlspecialchars($po['nopo']) . "</b></td></tr>
			<tr><td>Supplier</td><td>:</td><td>" . htmlspecialchars(trim(isset($namasup[$po['kodesupplier']]) ? $namasup[$po['kodesupplier']] : $po['kodesupplier'])) . "</td></tr>
			</table>";
		$tab .= rpoJejakHtml($po);
		##RINGKASAN PER BARANG
		$stmt = $owlPDO->prepare("select kodebarang, sum(jumlahpesan) as jumlahpesan, sum(jumlahterima) as jumlahterima, sum(jumlahclose) as jumlahclose, satuanpo, satuanterima from " . $dbname . ".log_po_terima_vw where nopo=? group by kodebarang, satuanpo, satuanterima order by kodebarang");
		$stmt->execute(array($po['nopo']));
		$ring = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($ring) > 0) {
			$tab .= "<div style='font-weight:bold;margin:6px 0 3px 0'>Ringkasan per barang</div>";
			$tab .= "<table class=sortable cellspacing=1 cellpadding=4 border=0 style='width:100%'>
				<thead><tr class=rowheader>
					<th align=center style='width:30px'>No.</th>
					<th align=center>Barang</th>
					<th align=center style='width:110px'>Dipesan</th>
					<th align=center style='width:110px'>Diterima</th>
					<th align=center style='width:90px'>Ditutup</th>
					<th align=center style='width:110px'>Sisa</th>
				</tr></thead><tbody>";
			$no = 0;
			foreach ($ring as $r) {
				$no++;
				$sisaTxt = '-';
				if ($r['satuanpo'] == $r['satuanterima']) {
					$sisa = $r['jumlahpesan'] - $r['jumlahterima'] - $r['jumlahclose'];
					$sisaTxt = rpoAngka($sisa < 0 ? 0 : $sisa) . ' ' . $r['satuanpo'];
				}
				$tab .= "<tr class=rowcontent>
					<td align=center>" . $no . "</td>
					<td>" . htmlspecialchars((isset($nmbrg[$r['kodebarang']]) ? $nmbrg[$r['kodebarang']] : '') . ' - [' . $r['kodebarang'] . ']') . "</td>
					<td align=right>" . rpoAngka($r['jumlahpesan']) . ' ' . htmlspecialchars($r['satuanpo']) . "</td>
					<td align=right>" . rpoAngka($r['jumlahterima']) . ' ' . htmlspecialchars($r['satuanterima']) . "</td>
					<td align=right>" . rpoAngka($r['jumlahclose']) . "</td>
					<td align=right>" . $sisaTxt . "</td>
				</tr>";
			}
			$tab .= "</tbody></table>";
		}
		##RINCIAN PENERIMAAN
		$trx = array();
		$stmt = $owlPDO->prepare("select notransaksi, tanggal, kodegudang, kodebarang, jumlah, satuan, post from " . $dbname . ".log_transaksi_vw where nopo=? and tipetransaksi='1' order by tanggal, notransaksi");
		$stmt->execute(array($po['nopo']));
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$trx[] = array($r['notransaksi'], $r['tanggal'], $r['kodegudang'], $r['kodebarang'], $r['jumlah'], $r['satuan'], $r['post'] == '1');
		}
		$stmt = $owlPDO->prepare("select notransaksi, tanggal, unit, kodebarang, jumlah, satuan, posting from " . $dbname . ".log_noninventorydt_vw where nopo=? order by tanggal, notransaksi");
		$stmt->execute(array($po['nopo']));
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$trx[] = array($r['notransaksi'], $r['tanggal'], $r['unit'], $r['kodebarang'], $r['jumlah'], $r['satuan'], $r['posting'] == '1');
		}
		$tab .= "<div style='font-weight:bold;margin:10px 0 3px 0'>Rincian penerimaan</div>";
		$tab .= "<table class=sortable cellspacing=1 cellpadding=4 border=0 style='width:100%'>
			<thead><tr class=rowheader>
				<th align=center style='width:30px'>No.</th>
				<th align=center>No. Transaksi</th>
				<th align=center style='width:90px'>Tanggal</th>
				<th align=center style='width:90px'>Gudang</th>
				<th align=center>Barang</th>
				<th align=center style='width:110px'>Jumlah</th>
				<th align=center style='width:90px'>Status</th>
			</tr></thead><tbody>";
		$no = 0;
		foreach ($trx as $t) {
			$no++;
			$tab .= "<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td>" . htmlspecialchars($t[0]) . "</td>
				<td align=center>" . tanggalnormal($t[1]) . "</td>
				<td align=center>" . htmlspecialchars((string)$t[2]) . "</td>
				<td>" . htmlspecialchars((isset($nmbrg[$t[3]]) ? $nmbrg[$t[3]] : '') . ' - [' . $t[3] . ']') . "</td>
				<td align=right>" . rpoAngka($t[4]) . ' ' . htmlspecialchars($t[5]) . "</td>
				<td align=center style='" . ($t[6] ? "background-color:#c6efce;color:#006100;font-weight:bold" : "background-color:#ffeb9c;color:#9c5700;font-weight:bold") . "'>" . ($t[6] ? 'Diposting' : 'Belum diposting') . "</td>
			</tr>";
		}
		if ($no == 0) {
			$tab .= "<tr class=rowcontent><td colspan=7 align=center>Belum ada penerimaan</td></tr>";
		}
		$tab .= "</tbody></table></div>";
		echo $tab;
		break;
	case 'excellist':
		$rows = rpoDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$nmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
		$kolom = 10;
		$stream = "<table>
			<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
			<tr><td colspan=" . $kolom . "><b>RELEASE PO</b></td></tr>
			<tr><td colspan=" . $kolom . ">" . htmlspecialchars(rpoInfoFilter()) . "</td></tr>
			<tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
			</table>";
		$stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
			<thead><tr>
				<th align=center bgcolor=#CCCCCC>No</th>
				<th align=center bgcolor=#CCCCCC>No. PO</th>
				<th align=center bgcolor=#CCCCCC>Tanggal PO</th>
				<th align=center bgcolor=#CCCCCC>Perusahaan</th>
				<th align=center bgcolor=#CCCCCC>Supplier</th>
				<th align=center bgcolor=#CCCCCC>Uraian</th>
				<th align=center bgcolor=#CCCCCC>Status</th>
				<th align=center bgcolor=#CCCCCC>PIC</th>
				<th align=center bgcolor=#CCCCCC>Keterangan</th>
				<th align=center bgcolor=#CCCCCC>Penerimaan Gudang</th>
			</tr></thead><tbody>";
		$no = 0;
		foreach ($rows as $val) {
			$no++;
			$stPo = rpoStatusPo($val);
			$stream .= "<tr>
				<td align=center>" . $no . "</td>
				<td>" . htmlspecialchars($val['nopo']) . "</td>
				<td align=center>" . tanggalnormal($val['tanggal']) . "</td>
				<td align=center>" . htmlspecialchars($val['kodeorg']) . "</td>
				<td>" . htmlspecialchars(isset($nmsup[$val['kodesupplier']]) ? $nmsup[$val['kodesupplier']] : $val['kodesupplier']) . "</td>
				<td>" . nl2br(htmlspecialchars($val['uraian'])) . "</td>
				<td align=center" . rpoStyleExcel($stPo['postatus']) . ">" . htmlspecialchars($stPo['postatus']) . "</td>
				<td align=center>" . htmlspecialchars($stPo['pic']) . "</td>
				<td>" . nl2br(htmlspecialchars(rpoTeksDatar($stPo['keterangan'], "\n"))) . "</td>
				<td>" . nl2br(htmlspecialchars(rpoPenerimaan($val['nopo']))) . "</td>
			</tr>";
		}
		if ($no == 0) {
			$stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
		}
		$stream .= "</tbody></table>";
		$xls = new HtmlExcel();
		$xls->addSheet("ReleasePO", $stream);
		$xls->headers("ReleasePO_" . date('YmdHis') . ".xls");
		echo $xls->buildFile();
		exit;

	case 'pdflist':
		$rows = rpoDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$nmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
		$infoFilter = rpoInfoFilter();
		if (!class_exists('PDFListRPO')) {
			class PDFListRPO extends FPDF
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
					$this->Cell($width, 7, 'RELEASE PO', 0, 1, 'C');
					$this->SetFont('Arial', '', 8);
					$this->Cell($width, 5, utf8_decode($infoFilter), 0, 1, 'C');
					$this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
					$this->Ln(1);
					$this->SetFillColor(220, 220, 220);
					$this->SetFont('Arial', 'B', 8);
					$judul = array('No', 'No. PO', 'Tanggal', 'PT', 'Supplier', 'Uraian', 'Status', 'PIC', 'Keterangan');
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
		$pdf = new PDFListRPO('L', 'mm', 'A4');
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(false, 16);
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$colFixed = array(8, 46, 18, 11, 44, 56, 24, 28); #semua kolom kecuali Keterangan
		$pdf->colW = array_merge($colFixed, array($width - array_sum($colFixed)));
		$pdf->AddPage();
		$h = 6;
		$no = 0;
		foreach ($rows as $val) {
			$no++;
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
			$stPo = rpoStatusPo($val);
			$c = $pdf->colW;
			$pdf->SetFont('Arial', '', 7);
			$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
			$pdf->Cell($c[1], $h, rpoFit($pdf, $val['nopo'], $c[1]), 1, 0, 'L');
			$pdf->Cell($c[2], $h, tanggalnormal($val['tanggal']), 1, 0, 'C');
			$pdf->Cell($c[3], $h, $val['kodeorg'], 1, 0, 'C');
			$pdf->Cell($c[4], $h, rpoFit($pdf, isset($nmsup[$val['kodesupplier']]) ? $nmsup[$val['kodesupplier']] : $val['kodesupplier'], $c[4]), 1, 0, 'L');
			$pdf->Cell($c[5], $h, rpoFit($pdf, str_replace(array("\r", "\n"), ' ', $val['uraian']), $c[5]), 1, 0, 'L');
			$wrn = rpoWarnaStatus($stPo['postatus']);
			if ($wrn[0] != '') {
				sscanf($wrn[0], '#%02x%02x%02x', $br, $bg, $bb);
				sscanf($wrn[1], '#%02x%02x%02x', $fr, $fg, $fb);
				$pdf->SetFillColor($br, $bg, $bb);
				$pdf->SetTextColor($fr, $fg, $fb);
			}
			$pdf->Cell($c[6], $h, rpoFit($pdf, $stPo['postatus'], $c[6]), 1, 0, 'L', $wrn[0] != '');
			$pdf->SetTextColor(0, 0, 0);
			$pdf->Cell($c[7], $h, rpoFit($pdf, $stPo['pic'], $c[7]), 1, 0, 'L');
			$pdf->Cell($c[8], $h, rpoFit($pdf, rpoTeksDatar($stPo['keterangan'], '; '), $c[8]), 1, 1, 'L');
		}
		if ($no == 0) {
			$pdf->Cell(array_sum($pdf->colW), $h, 'Data tidak ditemukan', 1, 1, 'C');
		}
		$pdf->Output();
		exit;
	case'unclose':
		// aturan unclose sama dengan yang dipakai tombol dan popup (rpoAlasanUnclose)
		$alasanUnclose = rpoAlasanUnclose($nopo);
		if($alasanUnclose != ''){
			exit("warning: ".$alasanUnclose);
		}

		$str="update ".$dbname.".log_poht set keteranganclose='',closed=0 where nopo='".addslashes($nopo)."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	case'getFormTolak':
		echo"<br /><div id=rejected_form>
		<fieldset>
		<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo." class=myinputtext  style=\"width:150px;\" maxlength=\"50\" /></legend>
		<table cellspacing=1 border=0>
		<tr>
		<td colspan=3>
		Apakah Anda Akan Menolak No.PO Di Atas </td></tr>
		<tr><td>".$_SESSION['lang']['keterangan']."</td><td>:</td><td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event)\" id=ket name=ket style=\"width:150px;\" /></td></tr>
		<tr><td colspan=3 align=center>
		<button class=mybutton onclick=tolakPo() >".$_SESSION['lang']['yes']."</button>
		<button class=mybutton onclick=cancel_po() >".$_SESSION['lang']['no']."</button>
		</td></tr></table>
		
		</fieldset>
		</div>
		<input type=hidden name=method id=method  /> 
		<input type=hidden name=user_id id=user_id value=".$user_id." />
		<input type=hidden name=nopo id=nopo value=".$nopo."  />
		";
		break;
            
            
	case'tolakPo':
		if($ket=="")
		{
			echo"warning:Keterangan Tidak Boleh Kosong";
			exit();
		}
		$sUp="update ".$dbname.".log_poht set hasilpersetujuan2='2',persetujuan2='".$user_id."',tglp2='".$this_date."',keterangan='".$ket."',stat_release='1', useridreleasae='".$user_id."',tglrelease='".$this_date."', tanggal='".$this_date."' where nopo='".$nopo."'";
		try{
			$owlPDO->exec($sUp); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
	
	case'insertKoreksi':
		$sUpd="update ".$dbname.".log_poht set catatanrelease='".$texkKrsi."',stat_release='2' where nopo='".$nopo."'";
		try{
			$owlPDO->exec($sUpd); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
		
	case'cancelpoform':
		$tab=rpoCatatanAksi($nopo);
		$tab.="<fieldset><table cellpadding=1 cellspacing=1>";
		$tab.="<table cellpadding=3>
			<tr>
				<td>".$_SESSION['lang']['nopo']."</td>
				<td>:</td>
				<td>".$nopo."</td>
			</tr>
			<tr>
				<td valign=top>".$_SESSION['lang']['keterangan']."</td>
				<td valign=top>:</td>
				<td valign=top>
					<textarea id=ketClose></textarea>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=cancelpo('".$_POST['nopo']."')>".$_SESSION['lang']['save']."</button>
				</td>
			</tr>
		</table></fieldset>";
		echo $tab;
	break;
		
	case'closeForm':
		$optPil="";
		$aarpil=array("0"=>"Tutup PO","1"=>"Become Out Standing");
		foreach($aarpil as $lstPil=>$disPil){
			$optPil.="<option value='".$lstPil."'>".$disPil."</option>";
		}
		$tab.="<script language=JavaScript1.2 src=js/generic.js></script>
			   <script type=\"text/javascript\" src=\"js/log_release_po.js\"></script>";
		$tab.="<link rel=stylesheet type=text/css href=style/generic.css>";
		$tab.=rpoCatatanAksi($nopo);
		$tab.="<fieldset><table cellpadding=1 cellspacing=1>";
		$tab.="<table cellpadding=3>
			<tr>
				<td>".$_SESSION['lang']['nopo']."</td>
				<td>:</td>
				<td>".$nopo."</td>
			</tr>
			<tr>
				<td valign=top>".$_SESSION['lang']['keterangan']."</td>
				<td valign=top>:</td>
				<td valign=top>
					<textarea id=ketClose></textarea>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=tutpDt('".$_POST['nopo']."')>".$_SESSION['lang']['tutup']."</button>
				</td>
			</tr>
		</table></fieldset>";
		echo $tab;
	break;
	
	case'bospo':
		$tab=rpoCatatanAksi($nopo);
		
		$tab.="<fieldset>
		<legend style='font-weight:bold'><i>".$_SESSION['lang']['nopo']." : ".$nopo."</i></legend>";
		$tab.="<table border=0 cellspacing=1 cellpadding=3 class=sortable>
			<thead>
			<tr class=rowheader style='text-align:center'>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>".$_SESSION['lang']['nopp']."</td>
				<td>".$_SESSION['lang']['sudahditerima']."</td>
				<td>Become Out Standing</td>
				<td>".$_SESSION['lang']['kuantitaspo']."</td>
				<td width=75px>Return</td>
			</tr>
			</thead>
			<tbody>";
		
		##GET DETAIL PO
		$no=0;
		$str="select * from ".$dbname.".log_podt where nopo='".$nopo."' order by kodebarang asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
			$optnamabarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			
			##CEK JUMLAH YANG SUDAH DITERIMA
			$sudahditerima=rpoSudahDiterima($nopo,$val['kodebarang']);
			$sisa=$val['jumlahpesan']-$sudahditerima-$val['jmlhstlhclose'];
			$disabled="";
			if($sisa==0){
				$disabled="disabled";
			}
			
			$tab.="<tr class=rowcontent id='tr_".$no."' style='vertical-align:top'>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center id='kodebarang_".$no."'>".$val['kodebarang']."</td>";
			$tab.="<td align=left id='namabarang_".$no."'>".$optnamabarang[$val['kodebarang']]."</td>";
			$tab.="<td id='satuan_".$no."'>".$val['satuan']."</td>";
			$tab.="<td id='nopp_".$no."'>".$val['nopp']."</td>";
			$tab.="<td id='sudahditerima_".$no."' align=right>".hidezerodecimal($sudahditerima,3)."</td>";
			$tab.="<td id='bos_".$no."' align=right>".hidezerodecimal($val['jmlhstlhclose'],3)."</td>";
			$tab.="<td id='jumlahpesan_".$no."' align=right>".hidezerodecimal($val['jumlahpesan'],3)."</td>";
			$tab.="<td align=center>
				<input type=text ".$disabled." class=myinputtextnumber id='diterima_".$no."' onkeypress=\"return angka_doang(event);\" placeholder=0 style=width:70px maxlength=12>
			</td>";
			$tab.="</tr>";
		}
		
		## GET PURCHASER
		$str="select purchaser from ".$dbname.".log_poht where nopo='".$nopo."'";
		$res=fetchdata($str);
		$purchaser=$res[0]['purchaser'];
			
		$tab.="<tr>
			<td colspan=8 align=center>
			<table cellpadding=3>
				<tr>
					<td valign=top>".$_SESSION['lang']['purchaser']."</td>
					<td valign=top>:</td>
					<td valign=top>".getNamaKaryawan($purchaser)."</td>
				</tr>
				<tr>
					<td valign=top>".$_SESSION['lang']['keterangan']."</td>
					<td valign=top>:</td>
					<td valign=top>
						<textarea id=ketClose></textarea>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=closebos('".$nopo."','".$no."')>".$_SESSION['lang']['save']."</button>
					</td>
				</tr>
			</table>
			</td>
		</tr></tbody>";
		
			
		$tab.="</fieldset>";
		
		echo $tab;
	break;
		
	case'closepo':
		try {
			$owlPDO->beginTransaction();
			
			$nopo = checkPostGet('nopo','');
			$keterangan = substr($owlPDO->quote(checkPostGet('ketClose','')),1,-1);
			$isiketerangan=$keterangan.' ,tanggal tutup : '.date('d-m-Y');

			##CEK INVOICE
			$alasanInv = rpoAlasanInvoice($nopo,'close');
			if($alasanInv != ''){
				throw new PDOException($alasanInv);
			}
			
			##CEK SPK
			$str="select spk from ".$dbname.".log_podt where nopo='".$nopo."'";
			$res=fetchdata($str);
			$incspk = '0';
			foreach($res as $key=>$val){
				if($val['spk']=='1'){
					$incspk = '1';
				}
			}
			if($incspk=='1'){
				$str="select * from ".$dbname.".lgl_pengajuanspkht where divisi='".$nopo."'";
				$res=fetchdata($str);
				$jlhspk = count($res);
				if($jlhspk > 0){
					throw new PDOException("Tidak dapat tutup PO, sudah ada terbentuk transaksi di Pengajuan SPK silahkan hapus terlebih dahulu transaksi Pengajuan SPK");
				}
			}
		
			##STATUS RELEASE
			$stats='';
			$str="select stat_release from ".$dbname.".log_poht where nopo='".$nopo."'";
			$res=fetchdata($str);
			foreach($res as $key=>$val){
				$stats=$val['stat_release'];
			}
		
			##TUTUP PO 
			$str="select kodebarang,nopp,jumlahpesan from ".$dbname.".log_podt where nopo='".$nopo."'";
			$res=fetchdata($str);
			foreach($res as $val){
				$strx="update ".$dbname.".log_prapodt set status=1,ditolakoleh='".$_SESSION['standard']['userid']."',alasanstatus='".$keterangan."' where nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."'";
				$owlPDO->exec($strx); 
			}
			
			$str="update ".$dbname.".log_poht set closed=1,keterangan='".$isiketerangan."',userclosed='".$_SESSION['standard']['userid']."' where nopo='".$nopo."'";
			$owlPDO->exec($str); 
			
			$str="update ".$dbname.".log_sorefrensi set jumlah='0' where noso='".$nopo."'";
			$owlPDO->exec($str);
			
			if($stats=='' or $stats=='null' or $stats==0){
				$str="delete from ".$dbname.".approval where notransaksi='".$nopo."'";
				$owlPDO->exec($str); 
			}
		
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}		
	break;
	
	case'cancelpo':
		try {
			$owlPDO->beginTransaction();
			
			$nopo = checkPostGet('nopo','');
			$keterangan = substr($owlPDO->quote(checkPostGet('ketClose','')),1,-1);
			$isiketerangan=$keterangan.' ,tanggal cancel : '.date('d-m-Y');

			##CEK INVOICE
			$alasanInv = rpoAlasanInvoice($nopo,'cancel');
			if($alasanInv != ''){
				throw new PDOException($alasanInv);
			}
			$tglskrg=date("Y-m-d H:i:S");
			
			##CEK SPK
			$lkdbrg="";
			$str="select spk,kodebarang from ".$dbname.".log_podt where nopo='".$nopo."'";
			$res=fetchdata($str);
			$incspk = '0';
			$no=0;
			foreach($res as $key=>$val){
				if($val['spk']=='1'){
					$incspk = '1';
				}
				if($no==0){
					$lkdbrg="'".$val['kodebarang']."'";
				}else{
					$lkdbrg.=",'".$val['kodebarang']."'";
				}
				$no++;
			}
			
			if($incspk=='1'){
				$str="select * from ".$dbname.".lgl_pengajuanspkht where divisi='".$nopo."'";
				$res=fetchdata($str);
				$jlhspk = count($res);
				if($jlhspk > 0){
					throw new PDOException("Tidak dapat cancel PO, sudah ada terbentuk transaksi di Pengajuan SPK silahkan hapus terlebih dahulu transaksi Pengajuan SPK");
				}
			}
			
			##GET DATA PO HT
			$str="select nodph from ".$dbname.".log_poht where nopo='".$nopo."'";
			$res=fetchdata($str);
			$norph=$res[0]['nodph'];
			
			##GET NO RFQ
			$lnodph="";
			$strx="select nomor from ".$dbname.".log_permintaanhargadt where norph='".$norph."' and kodebarang in (".$lkdbrg.")";
			$resx=fetchdata($strx);
			$no=0;
			foreach($resx as $valx){
				if($no==0){
					$lnodph="'".$valx['nomor']."'";
				}else{
					$lnodph.=",'".$valx['nomor']."'";
				}
				$no++;
			}
			// $nodph=$res[0]['nomor'];
			// print_r($lnodph);
			
			## INSERT NEW PERMINTAAN
			$no=0;
			$newnorfq="";
			$str="select * from ".$dbname.".log_permintaanhargadt where nomor in (".$lnodph.") and kodebarang in (".$lkdbrg.") order by nomor";
			$res=fetchdata($str);
			$tempno="";
			foreach($res as $val){
				$no++;
				if($tempno!=$val['nomor']){
					## CREATE NEW NO RFQ
					$expno = explode('/',$val['nomor']);
					$myno = "/".$expno[2]."/".$expno[3]."/".$expno[4];
					$strx="select nomor from ".$dbname.".log_perintaanhargaht where nomor like '%".$myno."%' order by nomor desc limit 0,1";
					$resx=fetchdata($strx);
					$dt=explode("/",$resx[0]['nomor']);
					$newnorfq = addZero(($dt[0]+1),3)."/".$expno[1]."".$myno;
					
					$komentarrfq="No. Ref RPH : ".$val['nomor']."<br>Note : PO/SO ".$nopo." di-cancel oleh ".getNamaKaryawan($_SESSION['standard']['userid'])." dengan alasan ".$keterangan;
					$komentarrfqnew="No. Ref RPH Baru : ".$newnorfq."<br>Note : PO/SO ".$nopo." di-cancel oleh ".getNamaKaryawan($_SESSION['standard']['userid'])." dengan alasan ".$keterangan;
					
					$strx="insert into ".$dbname.".log_perintaanhargaht (nomor,tanggal,purchaser,supplierid,id_alamat_supplier,nourut,id_franco,stock,catatan,sisbayar,sisbayar2,ppn,subtotal,diskonpersen,nilaidiskon,nilaipermintaan,matauang,kurs,tgldari,tglsmp,flag,catatanmenang,po,pbbkb,tolakrph,nodphlama,keterangan,lokasikirim,statuskirim,durasipengiriman,durasipekerjaan,garansiproduk,posisistok,asuransi,komentar,nilai1s,nilai1f,nilai2s,nilai2f,nilai3s,nilai3f,nilai4s,nilai4f,nilai5s,nilai5f) select '".$newnorfq."','".date('Y-m-d')."',purchaser,supplierid,id_alamat_supplier,nourut,id_franco,stock,catatan,sisbayar,sisbayar2,ppn,subtotal,diskonpersen,nilaidiskon,nilaipermintaan,matauang,kurs,tgldari,tglsmp,'0','','0',pbbkb,'0','".$val['nomor']."',keterangan,lokasikirim,statuskirim,durasipengiriman,durasipekerjaan,garansiproduk,posisistok,asuransi,'".$komentarrfq."',nilai1s,nilai1f,nilai2s,nilai2f,nilai3s,nilai3f,nilai4s,nilai4f,nilai5s,nilai5f from ".$dbname.".log_perintaanhargaht where nomor='".$val['nomor']."'";
					$owlPDO->exec($strx);
					
					$strx="update ".$dbname.".log_perintaanhargaht set komentar='".$komentarrfqnew."' where nomor='".$val['nomor']."'";
					$owlPDO->exec($strx);
				}
				$tempno=$val['nomor'];
				
				$strx="insert into ".$dbname.".log_permintaanhargadt (nomor,nourut,kodebarang,hargaterakhir,harga,merk,spec,jumlah,nopp,flag,norph,verificator,tanggalverifikasi,score,factor) values ('".$newnorfq."','".$val['nourut']."','".$val['kodebarang']."','".$val['hargaterakhir']."','".$val['harga']."','".$val['merk']."','".$val['spec']."','".$val['jumlah']."','".$val['nopp']."','0','','','','".$val['score']."','".$val['factor']."')";
				$owlPDO->exec($strx);
				
				##CREATE OUT STANDING PURCHASER RFQ
				$optPurchaser=makeOption($dbname,"log_poht","nopo,purchaser","nopo='".$nopo."'");
				$purchaser=$optPurchaser[$nopo];
				$str="update ".$dbname.".log_listverifikasi set pemenang='0' where nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."'";
				$owlPDO->exec($str);			

				##UPDATE PR/SR DETAIL
				$sup="update ".$dbname.".log_prapodt set create_po=0,status=0 where nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."'";
				$owlPDO->exec($sup);
			}
			
			##UPDATE STATUS PO
			$str="update ".$dbname.".log_poht set statuspo='4',closed='1',keteranganclose='".$isiketerangan."',userclosed='".$_SESSION['standard']['userid']."' where nopo='".$nopo."'";
			$owlPDO->exec($str); 
			
			##DELETE APPROVAL WAITING
			$str="delete from ".$dbname.".approval where notransaksi='".$nopo."' and status='0'";
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}		
	break;
	
	case'closebos':
		try {
			$owlPDO->beginTransaction();
			$nopo = checkPostGet('nopo','');
			$keterangan = substr($owlPDO->quote(checkPostGet('keterangan','')),1,-1);
			$tglskrg=date("Y-m-d H:i:S");
			$isiketerangan="";

			##CEK INVOICE
			$alasanInv = rpoAlasanInvoice($nopo,'bos');
			if($alasanInv != ''){
				throw new PDOException($alasanInv);
			}

			$str="select keteranganclose,purchaser  from ".$dbname.".log_poht where nopo='".$nopo."'";
			$res=fetchdata($str);
			$ktclose=$res[0]['keteranganclose'];
			$purchaser=$res[0]['purchaser'];
			if($ktclose!=''){
				$isiketerangan.=$ktclose."<br>";
			}
			$isiketerangan.=$keterangan.' ,tanggal tutup : '.date('d-m-Y');
			
			$errmsg="";
			$newnopp="";
			$no=0;
			$countno=0;
			foreach($_POST['kodebarang'] as $key=>$val){
				$no++;
				$kodebarang=$val;
				if(!preg_match('/^[A-Za-z0-9\/._ -]+$/',$kodebarang)){
					throw new PDOException("Kode barang tidak valid");
				}
				
				##GET DETAIL PO
				$jlhclose=0;
				$strx="select jmlhstlhclose from ".$dbname.".log_podt where nopo='".$nopo."' and kodebarang='".$kodebarang."'";
				$resx=fetchdata($strx);
				foreach($resx as $valx){
					$jlhclose+=$valx['jmlhstlhclose'];					
				}
				
				$nopp=$_POST['nopp'][$key];
				if(!preg_match('/^[A-Za-z0-9\/._ -]*$/',$nopp)){
					throw new PDOException("Nomor PP tidak valid");
				}
				#jumlah dipesan dan sudah diterima dihitung ulang di server, bukan dari isi tabel di browser
				$strx="select jumlahpesan from ".$dbname.".log_podt where nopo=? and kodebarang=? and nopp=? limit 1";
				$stmtx=$owlPDO->prepare($strx);
				$stmtx->execute(array($nopo,$kodebarang,$nopp));
				$jumlahpesan=$stmtx->fetchColumn();
				if($jumlahpesan===false){
					throw new PDOException("Kode barang ".$kodebarang." tidak ditemukan pada PO ini");
				}
				$sudahditerima=rpoSudahDiterima($nopo,$kodebarang);
				$diterima=str_replace(",","",$_POST['diterima'][$key]);
				$retur=($diterima==''?0:$diterima);
				$totretur=$jumlahpesan-$sudahditerima-$jlhclose;
				
				if($retur!=0){
					if($retur > $totretur){
						if($totretur < 0){
							$totretur=0;
						}
						$errmsg.="Kode barang ".$kodebarang." hanya bisa di return sebanyak ".$totretur;
					}
				}else{
					$countno++;
				}
				// exit('warning '.$retur." || ".$diterima." || ".$totretur);
				if($no==1){
					
					##CREATE NEW PR
					$awal=0;
					$nourut=substr($nopp,0,3);
					$crnopp=str_replace($nourut,'',$nopp);
					$str="select nopp from ".$dbname.".log_prapoht where nopp like '%".$crnopp."' order by nopp desc limit 1";
					$res=fetchdata($str);
					$awal=substr($res[0]['nopp'],0,3);
					$awal=intval($awal) + 1;
					$counter=addZero($awal,3);
					$newnopp=$counter."".$crnopp;
					
					$strx="select * from ".$dbname.".log_prapoht where nopp='".$nopp."'";
					$resx=fetchdata($strx);
					foreach($resx as $valx){
						$strx="insert into ".$dbname.".log_prapoht values ('".$valx['pt']."','".$valx['unit']."','".$valx['tipepp']."','".$newnopp."','".$valx['tanggal']."','".$valx['keterangan']."','".$valx['dibuat']."','".$valx['requester']."','".$valx['close']."','".$nopo."')";
						$owlPDO->exec($strx);
					}
					
					
					##CREATE LIST FILE
					$strx="select * from ".$dbname.".listfileupload where notransaksi='".$nopp."'";
					$resx=fetchdata($strx);
					foreach($resx as $valx){
						$strx="insert into ".$dbname.".listfileupload values ('','".$newnopp."','".$valx['namafile']."','".$valx['formaticon']."','".$valx['kriteriaefil']."','".$valx['status']."','".$valx['createdby']."','".$valx['createdtime']."')";
						$owlPDO->exec($strx);
					}
					
					##CREATE APPROVAL
					$strx="select * from ".$dbname.".approval where notransaksi='".$nopp."'";
					$resx=fetchdata($strx);
					foreach($resx as $valx){
						$strx="insert into ".$dbname.".approval values ('','".$newnopp."','".$valx['jenispersetujuan']."','".$valx['level']."','".$valx['karyawanid']."','".$valx['status']."','".$valx['komentar']."','".$valx['keterangan']."','".$valx['tanggal']."')";
						$owlPDO->exec($strx);
					}
					
					##UPDATE PO
					$isiketerangan.="<br>No. Ref PR/SR : ".$newnopp;
					$strx="update ".$dbname.".log_poht set closed='1', keteranganclose='".$isiketerangan."', userclosed='".$_SESSION['standard']['userid']."' where nopo='".$nopo."'";
					$owlPDO->exec($strx);
				}
				
				if($retur>0){

					#= Cek apakah ada konversi
					$sql = "select * from ".$dbname.".log_5stkonversi";
					$res = fetchData($sql);

					foreach($res as $val) {
						$jumlahKonversi[$val['kodebarang']] = $val['jumlah'];
					}

					#= Cek apakah ada nilai konversi
					if ($jumlahKonversi[$kodebarang] > 0) {
						#= Get Data Jika yang di kembalikan sebagian
						#= Become Out Standing sebagian
						#= Caranya 
						#= Berapa jumlah retur * jumlah konversi
						#= Itulah nilai satuan sebelumnya untuk jumlah pp
						$jumlahRetur[$kodebarang] = round($retur/$jumlahKonversi[$kodebarang]);
						// exit('warning '.print_r($jumlahRetur));
						##INSERT PR DETAIL
						$strx="select * from ".$dbname.".log_prapodt where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
						$resx=fetchdata($strx);
						foreach($resx as $valx){
							$strx="insert into ".$dbname.".log_prapodt values ('".$newnopp."','".$kodebarang."','".$valx['stock']."','".$jumlahRetur[$valx['kodebarang']]."','".$jumlahRetur[$valx['kodebarang']]."','".$jumlahRetur[$valx['kodebarang']]."','".$valx['hargasatuan']."','".$valx['satuanpp']."','".$valx['satuankonversi']."','".$retur."','".$valx['keterangan']."','".$valx['anggaran']."','".$valx['tgl_sdt']."','".$valx['prioritas']."','0','".$valx['pembelian']."','".$valx['lokalpusat']."','0','".$valx['tglAlokasi']."','".$valx['alasanstatus']."','".$purchaser."','".$valx['ditolakoleh']."','".$valx['kodevhc']."','".$valx['updateby']."','".$valx['updatetime']."','".$valx['keteranganubah']."','".$valx['hargalama']."','".$valx['spk']."','".$valx['kmhm']."','".$valx['nokontrak']."','','','','".$valx['noakunbudget']."')";
							$owlPDO->exec($strx);
						}
					} else {	
						// exit('warning');
						##INSERT PR DETAIL
						$strx="select * from ".$dbname.".log_prapodt where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
						$resx=fetchdata($strx);
						foreach($resx as $valx){
							$strx="insert into ".$dbname.".log_prapodt values ('".$newnopp."','".$kodebarang."','".$valx['stock']."','".$retur."','".$retur."','".$retur."','".$valx['hargasatuan']."','".$valx['satuanpp']."','".$valx['satuankonversi']."','".$valx['hasilkonversi']."','".$valx['keterangan']."','".$valx['anggaran']."','".$valx['tgl_sdt']."','".$valx['prioritas']."','0','".$valx['pembelian']."','".$valx['lokalpusat']."','0','".$valx['tglAlokasi']."','".$valx['alasanstatus']."','".$purchaser."','".$valx['ditolakoleh']."','".$valx['kodevhc']."','".$valx['updateby']."','".$valx['updatetime']."','".$valx['keteranganubah']."','".$valx['hargalama']."','".$valx['spk']."','".$valx['kmhm']."','".$valx['nokontrak']."','','','','".$valx['noakunbudget']."')";
							$owlPDO->exec($strx);
						}
					}
						
					##INSERT PR VERIFIKASI
					$strx="insert into ".$dbname.".log_listverifikasi values ('','".$newnopp."','".$kodebarang."','".$purchaser."','0','0','0','".$_SESSION['standard']['userid']."','".$tglskrg."','".$_SESSION['standard']['userid']."','".$tglskrg."')";
					$owlPDO->exec($strx);
					
					$strx="update ".$dbname.".log_podt set jmlhstlhclose=jmlhstlhclose+'".$retur."' where nopo='".$nopo."' and kodebarang='".$kodebarang."'";
					$owlPDO->exec($strx);
				}
			}
			
			if($countno==$no){
				throw new PDOException("Periksa kembali item barang, jumlah item masih 0");
			}
			
			if($errmsg!=''){
				throw new PDOException($errmsg);
			}
			
			// throw new PDOException($purchaser);
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			// echo "Warning \n" . addslashes($e->getMessage()."__".$strx);
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
		
	case'tutupData':
		$_POST['ketClose']=substr($owlPDO->quote($_POST['ketClose']),1,-1);
		$isiketerangan=$_POST['ketClose'].' ,tanggal tutup : '.date('d-m-Y');

		##CEK INVOICE
		$alasanInv = rpoAlasanInvoice($_POST['nopo'],'close');
		if($alasanInv != ''){
			exit("Gagal, ".$alasanInv);
		}
		
		$str="select spk from ".$dbname.".log_podt where nopo='".$_POST['nopo']."'";
		$res=fetchdata($str);
		$incspk = '0';
		foreach($res as $key=>$val){
			if($val['spk']=='1'){
				$incspk = '1';
			}
		}

		$stats='';
		$str="select stat_release from ".$dbname.".log_poht where nopo='".$_POST['nopo']."'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$stats=$val['stat_release'];
		}
		
		if($incspk=='1'){
			$str="select * from ".$dbname.".lgl_pengajuanspkht where divisi='".$_POST['nopo']."'";
			$res=fetchdata($str);
			$jlhspk = count($res);
			if($jlhspk > 0){
				exit("Gagal, Tidak dapat tutup PO, sudah ada terbentuk transaksi di Pengajuan SPK silahkan hapus terlebih dahulu transaksi Pengajuan SPK");
			}
		}
		
		$_POST['pilDt']=0;
		
		if($_POST['pilDt']==0){
			##TUTUP PO 
			$sdata="select kodebarang,nopp,jumlahpesan from ".$dbname.".log_podt where nopo='".$_POST['nopo']."'";
			$qdata=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
			$qdata->setFetchMode(PDO::FETCH_ASSOC);
			while($rdata=$qdata->fetch()){
				$sup="update ".$dbname.".log_prapodt set status=1,ditolakoleh='".$_SESSION['standard']['userid']."',alasanstatus='".$_POST['ketClose']."'
					  where nopp='".$rdata['nopp']."' and kodebarang='".$rdata['kodebarang']."'";
				try{
					$owlPDO->exec($sup); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
			
			$supdate="update ".$dbname.".log_poht set closed=1,keterangan='".$isiketerangan."',userclosed='".$_SESSION['standard']['userid']."' where nopo='".$_POST['nopo']."'";
			try{
				$owlPDO->exec($supdate); 
				
				$str="update ".$dbname.".log_sorefrensi set jumlah='0' where noso='".$_POST['nopo']."'";
				$owlPDO->exec($str);
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}

			if($stats=='' or $stats=='null' or $stats==0)
			{
				
				$sdelete="delete from ".$dbname.".approval where notransaksi='".$_POST['nopo']."'";
			//exit('Error : '.$sdelete);
				
				try{
					$owlPDO->exec($sdelete); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		}else{
			$subTotal=0;
			$sdata="select kodebarang,nopp,jumlahpesan,hargasbldiskon from ".$dbname.".log_podt where nopo='".$_POST['nopo']."'";
			$qdata=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
			$qdata->setFetchMode(PDO::FETCH_ASSOC);
			while($rdata=$qdata->fetch()){
				$optPurchaser=makeOption($dbname,"log_poht","nopo,purchaser","nopo='".$_POST['nopo']."'");
				$purchaser=$optPurchaser[$_POST['nopo']];
				$str = "insert into ".$dbname.".log_listverifikasi (nopp,kodebarang,karyawanid,status,skip,pemenang,createdby,createdtime,updateby,updatetime) values ('".$rdata['nopp']."','".$rdata['kodebarang']."','".$purchaser."','0','0','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
				try{ $owlPDO->exec($str); } catch(PDOException $e){ echo " Gagal," . addslashes($e->getMessage());}			


				$hitung=0;
				$sjmlhgdng="select distinct sum(jumlah) as jmlh from ".$dbname.".log_transaksi_vw 
							where nopo='".$_POST['nopo']."' and kodebarang='".$rdata['kodebarang']."' and tipetransaksi=1";
				$qjmlhgdng=$owlPDO->query($sjmlhgdng) or die(print " Gagal: ".PDOException::getMessage());
				$qjmlhgdng->setFetchMode(PDO::FETCH_ASSOC);
				$angkPengurang=0;
			   
						$rjmlgdng=$qjmlhgdng->fetch();
						if(($rjmlgdng['jmlh']=='')||intval($rjmlgdng['jmlh'])==0){
								$angkPengurang=0;
						}else{
								$angkPengurang=$rjmlgdng['jmlh'];
						}
				
				//$hitung=$rdata['jumlahpesan']-$angkPengurang;
				
				if($angkPengurang!=''){
					$jmlclose=$rdata['jumlahpesan']-$angkPengurang;
					$hitung=$rdata['jumlahpesan']-$jmlclose;
					
				}else{
					$jmlclose=$rdata['jumlahpesan'];
					$hitung=0;  
				}
				/*if($hitung==0){
					$hitung=$rdata['jumlahpesan'];
					$jmlclose=0;
				}else{
					//exit("error:".$rdata['kodebarang']."__masuk sini".$rdata['jumlahpesan']."__".$angkPengurang);
					$jmlclose=$hitung;
					$hitung=0;  
				}*/
				$subTotal+=$hitung*$rdata['hargasbldiskon'];
				$supdate="update ".$dbname.".log_podt set jmlhstlhclose='".$jmlclose."',jumlahpesan='".$hitung."'
				where nopo='".$_POST['nopo']."' and kodebarang='".$rdata['kodebarang']."'";
				try{
					$owlPDO->exec($supdate);
					$sup="update ".$dbname.".log_prapodt set create_po=0,status=0 where nopp='".$rdata['nopp']."' and kodebarang='".$rdata['kodebarang']."' ";
					try{
						$owlPDO->exec($sup); 
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n".$sup; 
						die(); 
					}
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n".$sup; 
					die(); 
				}	
			}
			$sHt="select distinct diskonpersen,subtotal,nilaidiskon,ppn from ".$dbname.".log_poht where nopo='".$_POST['nopo']."'";
			$qHt=$owlPDO->query($sHt) or die(print " Gagal: ".PDOException::getMessage());
			$qHt->setFetchMode(PDO::FETCH_ASSOC);	
			$rHt=$qHt->fetch();
			
			@$persenPPn=($rHt['ppn']/(($rHt['subtotal'])-$rHt['nilaidiskon']))*100;
			
			@$nilDis=($subTotal*$rHt['diskonpersen'])/100;
			@$ppn=((($subTotal)-$nilDis)*intval($persenPPn))/100;
			$nilTotal=(($subTotal)-$nilDis)+$rHt['ongkirimppn']+$rHt['ongkosangkutan']+$ppn;
			//$isiketerangan='';
			
			$supdateht="update ".$dbname.".log_poht set "
					. "closed=1,keteranganclose='".$isiketerangan."',userclosed='".$_SESSION['standard']['userid']."',nilaidiskon='".$nilDis."' "
					. ",subtotal='".$subTotal."',ppn='".$ppn."',nilaipo='".$nilTotal."' "
					. "where nopo='".$_POST['nopo']."'";
			try{
				$owlPDO->exec($supdateht); 
				
				$str="update ".$dbname.".log_sorefrensi set jumlah='0' where noso='".$_POST['nopo']."'";
				$owlPDO->exec($str); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}

			if($stats=='' or $stats=='null' or $stats==0)
			{
				
				$sdelete="delete from ".$dbname.".approval where notransaksi='".$_POST['nopo']."'";
				try{
					$owlPDO->exec($sdelete); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		}
		
		break;
	
	default:
	break;
	}
	    