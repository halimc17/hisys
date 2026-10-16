<?php
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	if($validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$session_id = '';
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
	$session_id = $_SESSION['standard']['userid'];
}else{
	if(!empty($_POST['namafile']) || !empty($_GET['namafile'])){		
		$str="select legend,ID from ".$dbname.".bahasa order by legend";
		$res=fetchdata($str);
		foreach($res as $bar){
			$_SESSION['lang'][$bar['legend']]=$bar['ID'];
		}
	}
}

require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');



// echo "<pre>";
// print_r($_GET);
// print_r($_POST);
// exit;
# Get Data
$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

#====================== Prepare Data
$ql="select a.unit,a.nopp,a.tanggal,a.dibuat, a.keterangan from ".$dbname.".`log_prapoht` a where a.nopp='".$column."'";
$pq=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
$pq->setFetchMode(PDO::FETCH_ASSOC);
$hsl=$pq->fetch();
$kdr=$hsl['unit'];
$unit=substr($column,15,4);
$keterangan=$hsl['keterangan'];


$sNmKry="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$hsl['dibuat']."'";
$qNmKry=$owlPDO->query($sNmKry) or die(print " Gagal: ".PDOException::getMessage());
$qNmKry->setFetchMode(PDO::FETCH_ASSOC);
$rNmKry=$qNmKry->fetch();
$dibuat=$rNmKry['namakaryawan'];


$sNmkntr="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdr."'";
$qNmkntr=$owlPDO->query($sNmkntr) or die(print " Gagal: ".PDOException::getMessage());
$qNmkntr->setFetchMode(PDO::FETCH_ASSOC);
$rNmkntr=$qNmkntr->fetch();
$nmKntr=$rNmkntr['namaorganisasi'];
$tgl=tanggalnormal($hsl['tanggal']);

$query="select a.*,b.*,c.namabarang,c.satuan,d.spesifikasi from ".$dbname.".".$table." a inner join ".$dbname.".`log_prapodt` b on a.nopp=b.nopp inner join ".$dbname.".`log_5masterbarang` c on b.kodebarang=c.kodebarang  left join ".$dbname.".`log_5photobarang` d on c.kodebarang=d.kodebarang where a.nopp='".$column."'"; //echo $query; exit();
$result = fetchData($query);

$expNopp = explode('/',$column);

#====================== Tampilan dokumen
if (!function_exists('pprTxt')) {
	function pprTxt($t)
	{
		return utf8_decode(str_replace("\r", '', (string)$t));
	}
}
if (!function_exists('pprAngka')) {
	function pprAngka($v)
	{
		$s = number_format((float)$v, 2, '.', ',');
		return rtrim(rtrim($s, '0'), '.');
	}
}
if (!function_exists('pprTgl')) {
	function pprTgl($t)
	{
		$t = trim((string)$t);
		if ($t == '' or substr($t, 0, 4) == '0000') {
			return '-';
		}
		return tanggalnormal(substr($t, 0, 10));
	}
}

#PT induk unit, kop (nama, alamat, telepon, logo)
$optInduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $unit . "'");
$kodePt = isset($optInduk[$unit]) ? $optInduk[$unit] : '';
$arrHead = setheadreport($unit, $kodePt);
$pathLogo = $arrHead['logo'];
if (!file_exists($pathLogo)) {
	$pathLogo = 'images/logo/' . $kodePt . '.jpg';
}
if (!file_exists($pathLogo)) {
	$pathLogo = '';
}
$jenisDok = ($expNopp[3] == 'PR' ? 'PURCHASE REQUEST' : 'SERVICE REQUEST');

if (!class_exists('PDFPP')) {
	class PDFPP extends FPDF
	{
		public $kop = array();
		public $logo = '';
		public $judulDok = '';
		public $noDok = '';
		public $kolom = array();

		function Header()
		{
			$w = $this->w - $this->lMargin - $this->rMargin;
			$y0 = $this->tMargin;
			$textX = $this->lMargin;
			if ($this->logo != '') {
				$tinggi = 44;
				$ukuran = @getimagesize($this->logo);
				$lebar = ($ukuran && $ukuran[1] > 0) ? $tinggi * $ukuran[0] / $ukuran[1] : 60;
				$this->Image($this->logo, $this->lMargin, $y0, $lebar, $tinggi);
				$textX = $this->lMargin + $lebar + 12;
			}
			$this->SetXY($textX, $y0 + 1);
			$this->SetFont('Arial', 'B', 13);
			$this->Cell($w - ($textX - $this->lMargin), 16, pprTxt($this->kop['nama']), 0, 1, 'L');
			$this->SetX($textX);
			$this->SetFont('Arial', '', 9);
			$this->Cell($w - ($textX - $this->lMargin), 12, pprTxt($this->kop['unit']), 0, 1, 'L');
			$kontak = array();
			if ($this->kop['alamat'] != '') {
				$kontak[] = $this->kop['alamat'];
			}
			if ($this->kop['telepon'] != '') {
				$kontak[] = 'Telp. ' . $this->kop['telepon'];
			}
			if ($this->kop['fax'] != '') {
				$kontak[] = 'Fax. ' . $this->kop['fax'];
			}
			if (count($kontak) > 0) {
				$this->SetX($textX);
				$this->SetFont('Arial', '', 7.5);
				$this->SetTextColor(90, 90, 90);
				$this->Cell($w - ($textX - $this->lMargin), 10, pprTxt(implode('  |  ', $kontak)), 0, 1, 'L');
				$this->SetTextColor(0, 0, 0);
			}
			$yGaris = $y0 + 50;
			$this->SetDrawColor(0, 0, 0);
			$this->SetLineWidth(1.4);
			$this->Line($this->lMargin, $yGaris, $this->lMargin + $w, $yGaris);
			$this->SetLineWidth(0.4);
			$this->Line($this->lMargin, $yGaris + 3, $this->lMargin + $w, $yGaris + 3);
			$this->SetY($yGaris + 12);
			if ($this->PageNo() == 1) {
				$this->SetFont('Arial', 'B', 14);
				$this->Cell($w, 18, $this->judulDok, 0, 1, 'C');
				$this->SetFont('Arial', '', 9.5);
				$this->Cell($w, 13, 'No. ' . $this->noDok, 0, 1, 'C');
				$this->Ln(8);
			} else {
				$this->SetFont('Arial', 'B', 9);
				$this->Cell($w, 12, $this->judulDok . '  -  No. ' . $this->noDok . '  (lanjutan)', 0, 1, 'L');
				$this->Ln(4);
			}
		}

		function Footer()
		{
			$w = $this->w - $this->lMargin - $this->rMargin;
			$this->SetY(-34);
			$this->SetDrawColor(150, 150, 150);
			$this->SetLineWidth(0.4);
			$this->Line($this->lMargin, $this->GetY(), $this->lMargin + $w, $this->GetY());
			$this->SetFont('Arial', 'I', 7);
			$this->SetTextColor(90, 90, 90);
			$pencetak = isset($_SESSION['empl']['name']) ? $_SESSION['empl']['name'] : '';
			$this->Cell($w / 2, 12, pprTxt('Dicetak oleh ' . $pencetak . ' pada ' . date('d-m-Y H:i')), 0, 0, 'L');
			$this->Cell($w / 2, 12, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
			$this->SetTextColor(0, 0, 0);
			$this->SetDrawColor(0, 0, 0);
		}

		//jumlah baris yang dibutuhkan teks pada lebar tertentu (untuk menentukan tinggi baris tabel)
		function NbLines($w, $txt)
		{
			$cw = &$this->CurrentFont['cw'];
			if ($w == 0) {
				$w = $this->w - $this->rMargin - $this->x;
			}
			$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
			$s = str_replace("\r", '', (string)$txt);
			$nb = strlen($s);
			if ($nb > 0 and $s[$nb - 1] == "\n") {
				$nb--;
			}
			$sep = -1;
			$i = 0;
			$j = 0;
			$l = 0;
			$nl = 1;
			while ($i < $nb) {
				$c = $s[$i];
				if ($c == "\n") {
					$i++;
					$sep = -1;
					$j = $i;
					$l = 0;
					$nl++;
					continue;
				}
				if ($c == ' ') {
					$sep = $i;
				}
				$l += $cw[$c];
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

		function CekHalaman($h)
		{
			if ($this->GetY() + $h > $this->PageBreakTrigger) {
				$this->AddPage();
				return true;
			}
			return false;
		}

		function KepalaTabel()
		{
			$judul = array('No.', 'Kode Barang', 'Nama Barang', 'Jumlah', 'Satuan', 'Dibutuhkan', 'Keterangan');
			$this->SetFillColor(225, 225, 225);
			$this->SetDrawColor(60, 60, 60);
			$this->SetLineWidth(0.5);
			$this->SetFont('Arial', 'B', 8);
			$x = $this->lMargin;
			$y = $this->GetY();
			foreach ($judul as $i => $j) {
				$this->Rect($x, $y, $this->kolom[$i], 20, 'DF');
				$this->SetXY($x, $y + 6);
				$this->Cell($this->kolom[$i], 8, $j, 0, 0, 'C');
				$x += $this->kolom[$i];
			}
			$this->SetXY($this->lMargin, $y + 20);
		}

		//satu baris barang: tinggi mengikuti isi terpanjang, kolom digaris dengan kotak
		function BarisBarang($no, $nama, $kode, $jml, $sat, $tgl, $ket)
		{
			$lh = 10;
			$this->SetFont('Arial', '', 8);
			$baris = max($this->NbLines($this->kolom[2], $nama), $this->NbLines($this->kolom[6], $ket), 1);
			$h = $baris * $lh + 6;
			if ($this->CekHalaman($h)) {
				$this->KepalaTabel();
			}
			$x = $this->lMargin;
			$y = $this->GetY();
			$this->SetFont('Arial', '', 8);
			$isi = array($no, $kode, $nama, $jml, $sat, $tgl, $ket);
			$rata = array('C', 'L', 'L', 'R', 'C', 'C', 'L');
			$this->SetDrawColor(60, 60, 60);
			$this->SetLineWidth(0.4);
			foreach ($isi as $i => $teks) {
				$this->Rect($x, $y, $this->kolom[$i], $h);
				$this->SetXY($x, $y + 3);
				$this->MultiCell($this->kolom[$i], $lh, $teks, 0, $rata[$i]);
				$x += $this->kolom[$i];
			}
			$this->SetXY($this->lMargin, $y + $h);
		}
	}
}

#====================== Susun PDF
$pdf = new PDFPP('P', 'pt', 'A4');
$pdf->SetMargins(40, 36, 40);
$pdf->SetAutoPageBreak(true, 48);
$pdf->AliasNbPages();
$pdf->kop = array('nama' => ($arrHead['nama'] != '' ? $arrHead['nama'] : $nmKntr), 'unit' => $nmKntr, 'alamat' => (string)$arrHead['alamat'], 'telepon' => (string)$arrHead['telepon'], 'fax' => (string)$arrHead['fax']);
$pdf->logo = $pathLogo;
$pdf->judulDok = $jenisDok;
$pdf->noDok = $column;
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
$pdf->kolom = array(24, 62, 0, 44, 44, 60, 124);
$pdf->kolom[2] = $width - (array_sum($pdf->kolom));
$pdf->AddPage();

#--- keterangan dokumen
$nocapex = "";
$ket = explode('/', $keterangan);
if (@$ket[1] == 'FRM') {
	$nocapex = $keterangan;
}
$jabPemohon = '';
$optJab = fetchdata("select b.namajabatan from " . $dbname . ".datakaryawan a left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where a.karyawanid='" . $hsl['dibuat'] . "'");
if (count($optJab) > 0) {
	$jabPemohon = $optJab[0]['namajabatan'];
}
$info = array(
	array('No. ' . ($expNopp[3] == 'PR' ? 'PR' : 'SR'), $column, 'Dibuat oleh', $dibuat),
	array('Tanggal', $tgl, 'Jabatan', $jabPemohon),
	array('Unit', $nmKntr . ' (' . $unit . ')', 'Jumlah item', count($result) . ' barang')
);
if ($nocapex != '') {
	$info[] = array('No. Capex', $nocapex, '', '');
}
$wKiri = $width * 0.54;
$pdf->SetFont('Arial', '', 8.5);
foreach ($info as $b) {
	$pdf->SetX($pdf->lMargin);
	$pdf->SetFont('Arial', '', 8.5);
	$pdf->Cell(62, 13, pprTxt($b[0]), 0, 0, 'L');
	$pdf->Cell(8, 13, ':', 0, 0, 'C');
	$pdf->SetFont('Arial', 'B', 8.5);
	$pdf->Cell($wKiri - 70, 13, pprTxt($b[1]), 0, 0, 'L');
	if ($b[2] != '') {
		$pdf->SetFont('Arial', '', 8.5);
		$pdf->Cell(62, 13, pprTxt($b[2]), 0, 0, 'L');
		$pdf->Cell(8, 13, ':', 0, 0, 'C');
		$pdf->SetFont('Arial', 'B', 8.5);
		$pdf->Cell($width - $wKiri - 70, 13, pprTxt($b[3]), 0, 0, 'L');
	}
	$pdf->Ln(13);
}
$pdf->Ln(8);

#--- tabel barang
$pdf->KepalaTabel();
$no = 0;
foreach ($result as $data) {
	$no++;
	$catatan = trim((string)$data['keterangan']);
	if ($data['keteranganubah'] != '') {
		$catatan .= ($catatan != '' ? "\n" : '') . 'Diubah: ' . $data['keteranganubah'];
	}
	if ($data['status'] == '3') {
		$nmKartolak = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $data['ditolakoleh'] . "'");
		$catatan .= ($catatan != '' ? "\n" : '') . 'Ditolak oleh ' . $nmKartolak[$data['ditolakoleh']] . ($data['alasanstatus'] != '' ? ': ' . $data['alasanstatus'] : '');
	}
	$pdf->BarisBarang($no, pprTxt($data['namabarang']), $data['kodebarang'], pprAngka($data['jumlah']), pprTxt($data['satuan']), pprTgl($data['tgl_sdt']), pprTxt($catatan));
}
if ($no == 0) {
	$pdf->SetFont('Arial', 'I', 8.5);
	$pdf->Cell($width, 22, 'Tidak ada data barang', 1, 1, 'C');
}
$pdf->Ln(14);

#--- persetujuan: kotak tanda tangan per pihak
$jenisapp = 'PR';
if ($nocapex != '') {
	$jenisapp = 'CPX';
}
$countApp = getCountApproval($jenisapp, $expNopp[4]);
$pihak = array(array('judul' => 'Dibuat oleh', 'status' => 'Dibuat', 'nama' => $dibuat, 'jabatan' => $jabPemohon, 'tanggal' => $tgl, 'komentar' => ''));
$arrStatusApp = array('0' => 'Menunggu', '1' => 'Disetujui', '2' => 'Dikoreksi', '3' => 'Ditolak');
for ($i = 1; $i <= $countApp; $i++) {
	$noapp = ($nocapex != '') ? $nocapex : $column;
	$ad = detailApprove($i, $noapp, $jenisapp);
	$st = (string)$ad['status'];
	$pihak[] = array(
		'judul' => 'Persetujuan ' . $i,
		'status' => isset($arrStatusApp[$st]) ? $arrStatusApp[$st] : '',
		'nama' => (string)$ad['nama'],
		'jabatan' => (string)$ad['namajabatan'],
		'tanggal' => ($st == '1' || $st == '2' || $st == '3') ? pprTgl($ad['tanggal']) : '',
		'komentar' => trim((string)$ad['komentar'])
	);
}
$jml = count($pihak);
$tinggiBox = 104;
if ($pdf->GetY() + $tinggiBox + 22 > $pdf->PageBreakTrigger) {
	$pdf->AddPage();
}
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($width, 14, 'PERSETUJUAN', 0, 1, 'L');
$lebarBox = $width / $jml;
$fs = ($jml > 5) ? 7 : 8;
$x = $pdf->lMargin;
$y = $pdf->GetY();
$pdf->SetDrawColor(60, 60, 60);
$pdf->SetLineWidth(0.5);
foreach ($pihak as $p) {
	$pdf->SetFillColor(225, 225, 225);
	$pdf->Rect($x, $y, $lebarBox, 16, 'DF');
	$pdf->Rect($x, $y + 16, $lebarBox, $tinggiBox - 16);
	$pdf->SetFont('Arial', 'B', $fs);
	$pdf->SetXY($x, $y + 4);
	$pdf->Cell($lebarBox, 8, pprTxt($p['judul']), 0, 0, 'C');
	//status dan tanggal di ruang tanda tangan
	$pdf->SetFont('Arial', 'I', $fs);
	$pdf->SetXY($x, $y + 24);
	$pdf->Cell($lebarBox, 9, pprTxt($p['status']), 0, 0, 'C');
	if ($p['tanggal'] != '') {
		$pdf->SetXY($x, $y + 33);
		$pdf->Cell($lebarBox, 9, pprTxt($p['tanggal']), 0, 0, 'C');
	}
	//nama dan jabatan di bagian bawah kotak
	$pdf->SetFont('Arial', 'BU', $fs);
	$pdf->SetXY($x + 2, $y + 66);
	$pdf->Cell($lebarBox - 4, 10, pprTxt($p['nama']), 0, 0, 'C');
	$pdf->SetFont('Arial', '', $fs - 0.5);
	$pdf->SetXY($x + 2, $y + 78);
	$pdf->MultiCell($lebarBox - 4, 8.5, pprTxt($p['jabatan']), 0, 'C');
	$x += $lebarBox;
}
$pdf->SetXY($pdf->lMargin, $y + $tinggiBox + 10);

#--- catatan dari penyetuju (bila ada)
$adaCatatan = false;
foreach ($pihak as $p) {
	if ($p['komentar'] != '') {
		$adaCatatan = true;
	}
}
if ($adaCatatan) {
	$pdf->CekHalaman(40);
	$pdf->SetFont('Arial', 'B', 8.5);
	$pdf->Cell($width, 12, 'Catatan persetujuan:', 0, 1, 'L');
	$pdf->SetFont('Arial', '', 8);
	foreach ($pihak as $p) {
		if ($p['komentar'] != '') {
			$pdf->CekHalaman(20);
			$pdf->SetX($pdf->lMargin);
			$pdf->MultiCell($width, 10, pprTxt('- ' . $p['nama'] . ' (' . $p['judul'] . '): ' . $p['komentar']), 0, 'L');
		}
	}
}

# Print Out
$urlefil=checkPostGet('urlefil','0');
if($urlefil=='0'){
	$pdf->Output();
}else{
	$pdf->Output($urlefil);
}

?>