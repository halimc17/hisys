<?php
error_reporting(0);
$mobileValid = false;
if (isset($_POST['par']) or isset($_GET['par'])) {
	$validasiPostMobile = explode(" ", $_POST['par']);
	$validasiGetMobile = explode(" ", $_GET['par']);
	if ($validasiGetMobile[0] == "owlApp" or $validasiPostMobile[0] == "owlApp") {
		$mobileValid = true;
	}
	;
}

if ($mobileValid == false) { //untuk redirec dari mobile
	require_once('master_validation.php');
}
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');
require_once('lib/zLib.php');
include_once('lib/zPdfMaster.php');
require_once('dompdf/autoload.inc.php');

use Dompdf\Dompdf;

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];

$nopo = $column;
$exnopo = explode('/', $nopo);
$exnopo2 = explode('-', $exnopo[3]);
$jnsnopo = $exnopo2[0];
$jnsnopr = ($jnsnopo == 'PO' ? 'PR' : ($jnsnopo == 'SO' ? 'SR' : 'CAPEX'));
$kodept = $exnopo[5];
$kodeunit = $exnopo[4];

$urlefil = checkPostGet('urlefil', '0');

$tab = "";
$spasi = "<span style='color:white'>_.</span>";

$optnmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optnamabarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

$rekeningbank = makeOption($dbname, 'log_5rekbank', 'supplierid,bank');
$rekening = makeOption($dbname, 'log_5rekbank', 'supplierid,rekening');
$rekeningbank_an = makeOption($dbname, 'log_5rekbank', 'supplierid,an');

## GET PO HT
$str = "select penambahpph22,pph22,pph,nilaipo,purchaser,tanggal,syaratbayar,kodesupplier,alamatsup,npwpsup,kodeorg,matauang,idFranco,deliverytime,nodph,uraian,subtotal,diskonpersen,nilaidiskon,pbbkb, pphfinal, pphfinal,persenppn,ppn,addcost,waktucetak,idFrancoinvc,persenpph,tgledit,ongkosangkutan,tglrelease from " . $dbname . ".log_poht where nopo='" . $nopo . "'";
$res = fetchdata($str);
$purchaser = $res[0]['purchaser'];
$tgledit = $res[0]['tgledit'];
$tglpo = tanggalnormal($res[0]['tglrelease']);
// $tglpo = tanggalnormal($res[0]['tanggal']);
$syaratbayar = $res[0]['syaratbayar'];
$kodesupplier = $res[0]['kodesupplier'];
$alamatsup = $res[0]['alamatsup'];
$npwpsupplier = $res[0]['npwpsup'];
$kodept = $res[0]['kodeorg'];
$matauang = $res[0]['matauang'];
$idfranco = $res[0]['idFranco'];
$idfrancoinvc = $res[0]['idFrancoinvc'];
$iddeliverytime = $res[0]['deliverytime'];
$nodph = $res[0]['nodph'];
$keterangan = $res[0]['uraian'];
$subtotal = $res[0]['subtotal'];
$diskonpersen = $res[0]['diskonpersen'];
$nilaidiskon = $res[0]['nilaidiskon'];
$pbbkb = $res[0]['pbbkb'];
//pphfinal
$pphfinal = $res[0]['pphfinal'];
$persenppn = $res[0]['persenppn'];
$persenpph = $res[0]['persenpph'];
$ppn = $res[0]['ppn'];
$pph = $res[0]['pph'];
$pph22 = $res[0]['pph22'];
$penambahpph22 = $res[0]['penambahpph22'];
$addcost = $res[0]['addcost'];
// $grandtotal=$res[0]['nilaipo'];
$ongkir = $res[0]['ongkosangkutan'];
if ($pph22 != 0) {
	$pph = $pph22;

	if ($penambahpph22 == '1') {
		$grandtotal = (($subtotal - $nilaidiskon) + $pbbkb) - $pphfinal + $ppn + $pph + $addcost;
	} else {
		$grandtotal = (($subtotal - $nilaidiskon) + $pbbkb) - $pphfinal + $ppn - $pph + $addcost;
	}
} else {
	$grandtotal = (($subtotal - $nilaidiskon) + $pbbkb) - $pphfinal + $ppn - $pph + $addcost;
}
// $grandtotal=(($subtotal-$nilaidiskon))+$ppn-$pph;
$waktucetak = ($res[0]['waktucetak'] == '0000-00-00 00:00:00' ? '' : tglnmblnsec($res[0]['waktucetak'], 'E', ''));

$hsltax = "";
if ($persenpph == '' || $persenppn == 0) {
	if ($persenpph == '' || $persenpph == 0) {
	} else {
		$hsltax = "PPH";
	}
} else {
	$hsltax = "PPN";
	if ($persenpph == '' || $persenpph == 0) {
	} else {
		$hsltax = ", PPH";
	}
}

## GET FRANCO
$str = "select franco_name,alamat,contact,handphone,fax,contact from " . $dbname . ".setup_franco where id_franco='" . $idfranco . "'";
$res = fetchdata($str);
$franco = $res[0]['franco_name'];
$francoalamat = $res[0]['alamat'];
$francophone = $res[0]['handphone'];
$francofax = $res[0]['fax'];
$francokontak = $res[0]['contact'];

## GET FRANCO INVC
$str = "select franco_name,alamat,contact,handphone,fax,contact from " . $dbname . ".setup_franco where id_franco='" . $idfrancoinvc . "'";
$res = fetchdata($str);
$francoinvc = $res[0]['franco_name'];
$francoinvcalamat = $res[0]['alamat'];
$francoinvcphone = $res[0]['handphone'];
$francoinvcfax = $res[0]['fax'];
$francoinvcfax = $res[0]['fax'];
$francokontakinv = $res[0]['contact'];

## GET Delivery TIME
$optdeltime = makeOption($dbname, 'log_5delivtime', 'kode,nama', "kode='" . $iddeliverytime . "'");
$deliverytime = $optdeltime[$iddeliverytime];

## GET APPROVAL
$aprvkar = "";
$aprvtgl = "";
$str = "select karyawanid,status,tanggal from " . $dbname . ".approval where notransaksi='" . $nopo . "' order by level desc limit 1";
$res = fetchdata($str);
if ($res[0]['status'] == '1') {
	$aprvkar = $optnmkar[$res[0]['karyawanid']];
	$aprvkarid = $res[0]['karyawanid'];
	$expaprvtgl = explode("-", substr($res[0]['tanggal'], 0, 10));
	$bulanaprv = $expaprvtgl[1];
	$aprvtgl = "Date : " . $expaprvtgl[2] . ' ' . getnmbln($bulanaprv) . ' ' . $expaprvtgl[0];
}

## GET NO REFERENSI
$norefrensi = 0;
$refrensi = "";
$str = "select nopo from " . $dbname . ".log_sorefrensi where noso='" . $nopo . "' group by nopo";
$res = fetchdata($str);
foreach ($res as $val) {
	$norefrensi++;
	if ($norefrensi == 1) {
		$refrensi = $val['nopo'];
	} else {
		$refrensi .= ', ' . $val['nopo'];
	}
}

$str_po = "select * from " . $dbname . ".log_podt where nopo='" . $nopo . "'";
$res_po = fetchdata($str_po);
$total_jumlahPO = count($res_po);

## GET NO PP
$str = "select a.nopp,b.tanggal,a.kodebarang,c.tgl_sdt from " . $dbname . ".log_podt a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp left join " . $dbname . ".log_prapodt c on a.nopp=c.nopp and a.kodebarang=c.kodebarang where a.nopo='" . $nopo . "'";
$res = fetchdata($str);
$countpp = 0;
$refpp = "";
$tglpp = "";
$arrsdt = array();
foreach ($res as $val) {
	$countpp++;
	// if($countpp==1){
	$refpp = $val['nopp'];
	$tglpp = $val['tanggal'];
	// }else{
	// $refpp.=', '.$val['nopp'];
	// $tglpp=', '.$val['tanggal'];
	// }
	$exptglsdt = explode("-", $val['tgl_sdt']);
	$bulansdt = $exptglsdt[1];
	$arrsdt[$val['kodebarang']] = $exptglsdt[2] . ' ' . getnmbln($bulansdt) . ' ' . $exptglsdt[0];
}
if ($nodph != '') {
	## GET NO RFQ
	$str = "select nomor,nourut from " . $dbname . ".log_permintaanhargadt where norph='" . $nodph . "'";
	$res = fetchdata($str);
	$norpq = $res[0]['nomor'];
	$norpqurt = $res[0]['nourut'];

	## GET GARANSI
	$str = "select garansiproduk from " . $dbname . ".log_perintaanhargaht where nomor='" . $norpq . "' and nourut='" . $norpqurt . "'";
	$res = fetchdata($str);
	$garansi = $res[0]['garansiproduk'];
}

## CREATE FORMAT DATE PO/SO
$exptglpo = explode("-", $tglpo);
$exptglpr = explode("-", $tglpp);
$exptgledit = explode("-", $tgledit);
$bulanpo = $exptglpo[1];
$bulanpr = $exptglpr[1];
$bulanedit = $exptgledit[1];
function getnmbln($bulan)
{
	$hasil = "";
	if ($bulan == '01') {
		$hasil = 'Jan';
	}
	if ($bulan == '02') {
		$hasil = 'Feb';
	}
	if ($bulan == '03') {
		$hasil = 'Mar';
	}
	if ($bulan == '04') {
		$hasil = 'Apr';
	}
	if ($bulan == '05') {
		$hasil = 'Mei';
	}
	if ($bulan == '06') {
		$hasil = 'Jun';
	}
	if ($bulan == '07') {
		$hasil = 'Jul';
	}
	if ($bulan == '08') {
		$hasil = 'Agu';
	}
	if ($bulan == '09') {
		$hasil = 'Sep';
	}
	if ($bulan == '10') {
		$hasil = 'Okt';
	}
	if ($bulan == '11') {
		$hasil = 'Nov';
	}
	if ($bulan == '12') {
		$hasil = 'Des';
	}

	return $hasil;
}

$tglpobaru = $exptglpo[0] . ' ' . getnmbln($bulanpo) . ' ' . $exptglpo[2];
$tglprbaru = $exptglpr[2] . ' ' . getnmbln($bulanpr) . ' ' . $exptglpr[0];
$tgleditbaru = $exptgledit[2] . ' ' . getnmbln($bulanedit) . ' ' . $exptgledit[0];

## KETERANGAN SYARAT BAYAR
$opttop = makeOption($dbname, 'log_5syaratbayar', 'kode,keterangan', "kode='" . $syaratbayar . "'");
$top = $opttop[$syaratbayar];

## GET NPWP PT
$str = "select npwp,alamatnpwp from " . $dbname . ".setup_org_npwp where kodeorg='" . $kodept . "'";
$res = fetchdata($str);
$npwppt = $res[0]['npwp'];
$alamatnpwppt = $res[0]['alamatnpwp'];

## GET DETAIL SUPPLIER/Vendor
$str = "select namasupplier,namapenanggungjawab from " . $dbname . ".log_5supplier where supplierid='" . $kodesupplier . "'";
$res = fetchdata($str);
$namasupplier = $res[0]['namasupplier'];
$namapic = $res[0]['namapenanggungjawab'];

## GET NPWP SUPPLIER/Vendor
$str = "select alamat_lengkap from " . $dbname . ".log_5supnpwp where npwp='" . $npwpsupplier . "'";
$res = fetchdata($str);
$alamatnpwpsup = $res[0]['alamat_lengkap'];

$str = "select alamat,kota,telepon,fax,kontakperson,provinsi,kodepos from " . $dbname . ".log_5supalamat where id_alamat='" . $alamatsup . "'";
$res = fetchData($str);
$alamatsupplier = $res[0]['alamat'] . "" . ($res[0]['kota'] == '' ? '' : ', ' . $res[0]['kota']) . "" . ($res[0]['provinsi'] == '' ? '' : ', ' . $res[0]['provinsi']) . "" . ($res[0]['kodepos'] == '' ? '' : ' - ' . $res[0]['kodepos']);
$kotasupplier = $res[0]['kota'];
$teleponsupplier = $res[0]['telepon'];
$faxsupplier = ($res[0]['fax'] == '0' ? '' : $res[0]['fax']);
$kontaksupplier = $res[0]['kontakperson'];
$emailsupplier = $res[0]['email_koresponden'];

$qOrg = selectQuery($dbname, "organisasi", "kodeorganisasi,tipe", "kodeorganisasi='" . $kodeunit . "'");
$dataOrg = fetchData($qOrg);
$tipeOrg = $dataOrg[0]['tipe'];

## HEADER KOP
// $kodept = "CAR";
// $kodeunit = "CARO";
$arrHead = setheadreport($kodept, $kodept);
// $arrHeadUnit = setheadreport('', $kodeunit);
// echo "<pre>";
// print_r($arrHead);
// exit("Warning");
$pathx = $arrHead['logo'];
//@page { margin: 20; }
$tab .= "<html>
<style type='text/css'>
	@page {
		margin-top: 50px;
		margin-left: 20px;
		margin-right: 20px;
		margin-bottom: 120px;
	}
	footer {
		position: fixed;
		bottom: 80px; 
		left: 0;
		right: 0;
	}
	tr { page-break-inside: auto; }
	td { page-break-inside: avoid; }
	table {
		page-break-inside: auto;
	}
	thead {
		display: table-header-group;
	}
</style>";


if ($kodept == 'PPP') {
	$tab .= "
	<table width=100%>
		<tr style='text-align:center;font-weight:bold;font-size:20px'>
			<td style='width:20%;'>
				<img src='" . $pathx . "' height='100' />
			</td>
			<td style='width:80%;transform:translate(-50,0);font-size:25px'>
				" . strtoupper($jnsnopo == 'PO' ? 'Purchase Order' : 'Service Order') . "
				<br>
				<span style='font-size:14px'>No. " . $nopo . "</span>
			</td>
		</tr>
	</table>
	<br>";

	$rekk_an = "";
	if ($rekeningbank_an[$kodesupplier] != '') {
		$rekk_an = "" . $rekening[$kodesupplier] . " an " . $rekeningbank_an[$kodesupplier];
	} else {
		$rekk_an = $rekening[$kodesupplier];
	}

	$tab .= "
	<table width=100% border=1 cellpadding=0 cellspacing=0>
		<tr style='text-align:center;'>
			<td style='width:50%;height:100px;' valign=top>
				<table>
					<tr>
						<td style='vertical-align:top;text-align:center;font-weight:bold;padding-bottom:10px'>SUPPLIER</td>
					</tr>
					<tr>
						<td>" . $namasupplier . "</td>
					</tr>
					<tr>
						<td>" . $alamatsupplier . "</td>
					</tr>
					<tr>
						<td>Telp: " . $teleponsupplier . "; Fax: " . $faxsupplier . "</td>
					</tr>
					<tr>
						<td>E-mail: " . $emailsupplier . "</td>
					</tr>
				</table>
			</td>
			<td style='width:50%;height:100px;' valign=top>
				<table>
					<tr>
						<td style='vertical-align:top;text-align:center;font-weight:bold;padding-bottom:10px;'>Pembayaran:</td>
					</tr>
					<tr>
						<td>
							1. Syarat Pembayaran : <br> 
							" . $spasi . " " . $top . "
						</td>
					</tr>
					<tr>
						<td>
							2. Pembayaran melalui rekening : <br>
							" . $spasi . " " . $rekeningbank[$kodesupplier] . " <br>
							" . $spasi . " " . $rekk_an . "
						</td>
					</tr>
					";
	// DP/TERMIN
	$strx_dp = "select * from " . $dbname . ".log_potermin where nopo='" . $nopo . "'";
	$resx_dp = fetchdata($strx_dp);
	if (count($resx_dp) > 0) {
		$no_t = 1;
		$tab .= "<tr>
					<td> 3. Termin : ";
		foreach ($resx_dp as $key => $val) {
			$tab .= "<li style='list-style:none'> " . $spasi . " Termin Ke " . $no_t++ . " : " . number_format($val['rupiah'], 2) . " (" . $val['persen'] . "%)</li>";
		}
		//lapar banget anjayy
		$tab .= "</td>
		</tr>";
	}

	// Akhir DP/TERMIN
	$tab .= "</table>
			</td>
		</tr>

		<tr style='text-align:center;'>
			<td style='width:50%;height:100px;' valign=top>
				<table>
					<tr>
						<td style='vertical-align:top;text-align:center;font-weight:bold;padding-bottom:10px'>Alamat Pengiriman</td>
					</tr>
					<tr>
						<td>" . $franco . "</td>
					</tr>
					<tr>
						<td>" . $francoalamat . "</td>
					</tr>
					<tr>
						<td>Up: " . $francokontak . "</td>
					</tr>
					<tr>
						<td>Telp: " . $francophone . "; Fax" . $francofax . "</td>
					</tr>
				</table>
			</td>
			<td style='width:50%;height:100px;' valign=top>
				<table>
					<tr>
						<td style='vertical-align:top;text-align:center;font-weight:bold;padding-bottom:10px'>Alamat Penagihan</td>
					</tr>
					<tr>
						<td>" . $francoinvc . "</td>
					</tr>
					<tr>
						<td>" . $francoinvcalamat . "</td>
					</tr>
					<tr>
						<td>Up: " . $francokontakinv . "</td>
					</tr>
					<tr>
						<td>Telp: " . $francoinvcphone . "; Fax" . $francoinvcfax . "</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
	";



	$tab .= "
	<table width='100%' border=0 cellpadding=0 cellspacing=0>
		<thead>
			<tr>
				<th style='text-align:center;border:1px solid black'>No.</th>
				<th style='text-align:center;border:1px solid black'>Jumlah</th>
				<th style='text-align:center;border:1px solid black'>Nama Barang</th>
				<th style='text-align:center;border:1px solid black'>Harga Satuan <br> (Rp)</th>
				<th style='text-align:center;border:1px solid black'>Jumlah Harga <br> (Rp)</th>
			</tr>
		</thead>
		<tbody>";
	$no = 1;
	$subtotal_0 = 0;
	foreach ($res_po as $val) {
		if ($total_jumlahPO == $no) {

			if ($val['catatan'] != '') {
				$sfekkk = "<br> " . $val['catatan'] . " ";
			} else {
				$sfekkk = "";
			}

			$tab .= "
					<tr>
						<td style='text-align:center;height:50px;border-left:1px solid black;border-right:1px solid black;vertical-align: top'>" . $no++ . "</td>
						<td style='text-align:right;border-left:1px solid black;border-right:1px solid black;vertical-align: top'>" . number_format($val['jumlahpesan'], 2) . " " . $val['satuan'] . " <span style='color:white'>_</span></td>
						<td style='text-align:left;border-left:1px solid black;border-right:1px solid black;vertical-align: top'>" . $optnamabarang[$val['kodebarang']] . " - [" . $val['kodebarang'] . "] " . $sfekkk . " </td>
						<td style='text-align:right;border-left:1px solid black;border-right:1px solid black;vertical-align: top'>" . number_format($val['hargasatuan'], 2) . " <span style='color:white'>_</span></td>
						<td style='text-align:right;border-left:1px solid black;border-right:1px solid black;vertical-align: top'>" . number_format($val['hargasatuan'] * $val['jumlahpesan'], 2) . " <span style='color:white'>_</span></td>
					</tr>";

			// catatan
			$tab .= "
						<tr>
							<td style='text-align:center;height:50px;border-left:1px solid black;border-right:1px solid black;vertical-align: top;border-bottom:1px solid black'></td>
							<td style='text-align:right;border-left:1px solid black;border-right:1px solid black;vertical-align: top;border-bottom:1px solid black'></td>
							<td style='text-align:left;border-left:1px solid black;border-right:1px solid black;vertical-align: top;border-bottom:1px solid black'>
								<span>
									Catatan : 
								</span> <br>
								<span style='color:red;word-wrap: break-word;word-break: break-all;white-space: normal'>
									" . nl2br($keterangan) . "
								</span>
							</td>
							<td style='text-align:right;border-left:1px solid black;border-right:1px solid black;vertical-align: top;border-bottom:1px solid black'></td>
							<td style='text-align:right;border-left:1px solid black;border-right:1px solid black;vertical-align: top;border-bottom:1px solid black'></td>
						</tr>

					";
		} else {
			$tab .= "
					<tr>
						<td style='text-align:center;border-left:1px solid black;border-right:1px solid black'>" . $no++ . "</td>
						<td style='text-align:right;border-left:1px solid black;border-right:1px solid black'>" . number_format($val['jumlahpesan'], 2) . " " . $val['satuan'] . " <span style='color:white'>_</span></td>
						<td style='text-align:left;border-left:1px solid black;border-right:1px solid black'> " . $optnamabarang[$val['kodebarang']] . " - [" . $val['kodebarang'] . "]</td>
						<td style='text-align:right;border-left:1px solid black;border-right:1px solid black'>" . number_format($val['hargasatuan'], 2) . " <span style='color:white'>_</span></td>
						<td style='text-align:right;border-left:1px solid black;border-right:1px solid black'>" . number_format($val['hargasatuan'] * $val['jumlahpesan'], 2) . " <span style='color:white'>_</span></td>
					</tr>";
		}

		$subtotal_0 += $val['hargasatuan'] * $val['jumlahpesan'];
	}

	// subtotal
	$tab .= "<tr>
			<td style='border-left:1px solid black;text-align:right' colspan=4>Sub Total <span style='color:white'>_</span></td>
			<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($subtotal_0, 2) . " <span style='color:white'>_</span></td>
		</tr>";
	// ppn
	if ($persenppn > 0) {
		$tab .= "<tr>
				<td style='border-left:1px solid black;text-align:right' colspan=4>PPN " . $persenppn . "% <span style='color:white'>_</span></td>
				<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($ppn, 2) . " <span style='color:white'>_</span></td>
			</tr>";
	}
	// p[bbkb
	if ($pbbkb > 0) {
		$tab .= "<tr>
				<td style='border-left:1px solid black;text-align:right' colspan=4>PBBKB <span style='color:white'>_</span></td>
				<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($pbbkb, 2) . " <span style='color:white'>_</span></td>
			</tr>";
	}

	//Sabar Martua : Tambah PPH Final pada Detail PO
	//pphfinal

	//pphfinal
	if ($pphfinal > 0) {
		$tab .= "<tr>
				<td style='border-left:1px solid black;text-align:right' colspan=4>PPH Final <span style='color:white'>_</span></td>
				<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($pphfinal, 2) . " <span style='color:white'>_</span></td>
			</tr>";
	}

	if ($persenpph > 0) {
		$ketpph = '23';
		if ($pph22>0) {
			$ketpph = '22';
		}
		$tab .= "<tr>
				<td style='border-left:1px solid black;text-align:right' colspan=4>PPH".$ketpph." " . $persenpph . "% <span style='color:white'>_</span></td>
				<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($pph, 2) . " <span style='color:white'>_</span></td>
			</tr>";
	}
	if ($grandtotal > 0) {
		$tab .= "<tr>
				<td style='border:1px solid black;text-align:right;font-weight:bold' colspan=4>Total <span style='color:white'>_</span></td>
				<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($grandtotal, 2) . " <span style='color:white'>_</span></td>
			</tr>";
	}
	// syarat penagihan
	$tab .= "
			<tr>
				<td style='border:1px solid black;text-align:left' colspan=5>
				<span style='font-weight:bold'>
					Syarat Penagihan
				</span>
				<br>
				<span style='margin-left:20px'>1. Nomor rekening harus dicantumkan dalam Invoice penagihan</span><br>
				<span style='margin-left:20px'>2. Harap mencantumkan nomor PO ini pada Invoice dan Surat Jalan</span><br>
				<span style='margin-left:20px'>3. Melampirkan Invoice, Kuitansi dengan materai secukupnya dan Faktur Pajak Asli</span><br>
				<span style='margin-left:20px'>4. Tidak diperkenankan mengubah item dan jumlah barang tanpa persetujuan kami</span><br>
				</td>
			</tr>
		";


	$tab .= "</tbody>
	</table>";

	$tab .= "<footer>";
	$cellpadding = 1;

	$tab .= "<table width=100% cellspacing=0 cellpadding=1>
		<tr style='text-align:center;vertical-align:top'>
			<!--<td style='width:30%;font-weight:bold;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;'>PREPARED BY</td>-->
			<td style='width:50%;font-weight:bold;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;'>
			Disetujui Oleh
			</td>
			<td rowspan=4 style='width:50%;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;'>
				<br><br><br><br>
				<br>
				<br>
				" . $namasupplier . "</i>
			</td>
		</tr>
		<tr>
			<!--<td style='width:30%;height:40px;border-left:0.5px solid #000;border-right:0.5px solid #000;'>&nbsp;</td>-->
			<td style='width:40%;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center;color:gray;font-size:16px'><i>" . ($aprvkar != '' ? 'ELECTRONICALLY SIGNED BY' : '') . "<i></td>
		</tr>
		<tr>
			<!--<td style='width:30%;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>" . $optnmkar[$purchaser] . "</td>-->
			<td style='width:40%;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>" . $aprvkar . "</td>
		</tr>
		<tr>
			<!--<td style='width:30%;border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>Date : " . $tgleditbaru . "</td>-->
			<td style='width:40%;border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>" . $aprvtgl . "</td>
		</tr>
	</table>

	";

	$tab .= "</table>";
	$tab .= "</footer>";
} else {
	$tab .= "
			<table width='100%' style='margin-top:12px; border-collapse:collapse;'>
				<tr>
					
					<td style='width:20%; text-align:left; vertical-align:middle;'>
						<img src='{$pathx}' style='height:80px; margin-left:10px;'>
					</td>

					
					<td style='width:60%; text-align:center; vertical-align:middle;'>
						<h2 style='margin:0; font-size:18px;'>
							{$arrHead['nama']} ({$tipeOrg})
						</h2>
						<p style='margin:2px 0; font-size:14px;'>
							NPWP : {$npwppt}
						</p>
						<p style='margin:0; font-size:14px;'>
							{$alamatnpwppt}
						</p>
					</td>

					<td style='width:20%;'></td>
				</tr>
			</table>";

	$tab .= "
		<table width='100%' style='margin:20px 5px;'>
			<tr>
				<td style='width:50%; vertical-align:top;'>
					<p style='margin:0;padding:0;font-size:14px;'>PO/SO. No : {$nopo}</p>
					<p style='margin:0;padding:0;font-size:14px;'>PO/SO. Date : {$tglpobaru}</p>
				</td>
				<td style='width:50%; text-align:center; vertical-align:middle;'>
					<h2 style='margin:0;padding:0;font-size:16px;'>" . strtoupper($jnsnopo == 'PO' ? 'Purchase Order' : 'Service Order') . "</h2>
				</td>
			</tr>
		</table>";

	$tab .= "
	   <table width='100%' border=1 cellpadding=4 cellspacing=0>
	   		<tr>
				<td>
					<table width=100% border=0 cellpadding=2 cellspacing=0 style='font-size:14px;'>
						<tr>
							<td style='text-align:center;font-weight:bold;margin-bottom:12px !important;'>Penjual</td>
						</tr>
						<tr>
							<td>{$namasupplier}</td>
						</tr>
						<tr>
							<td>{$namapic}</td>
						</tr>
						<tr>
							<td>{$alamatsupplier}</td>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table width=100% border=0 cellpadding=2 cellspacing=0 style='font-size:14px;'>
						<tr>
							<td colspan=3 style='text-align:center;font-weight:bold;margin-bottom:12px !important;'>Notes</td>
						</tr>
						<tr>
							<td width='25%'>Harga Franco</td>
							<td width='5%'>:</td>
							<td></td>
						</tr>
						<tr>
							<td width='25%'>Pembayaran</td>
							<td width='5%'>:</td>
							<td>{$top}</td>
						</tr>
						<tr>
							<td colspan='3' style='text-align:center;color:red;'>Faktur Pajak dan Invoice Maksimal {$syaratbayar} Hari</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<table width=100% border=0 cellpadding=2 cellspacing=0 style='font-size:14px;'>
						<tr>
							<td style='text-align:center;font-weight:bold;margin-bottom:12px !important;'>Pengiriman Ke</td>
						</tr>
						<tr>
							<td>{$franco}</td>
						</tr>
						<tr>
							<td>UP. {$francokontak}</td>
						</tr>
						<tr>
							<td>{$francoalamat}</td>
						</tr>
						<tr>
							<td>Telp: " . $francophone . "; Fax" . $francofax . "</td>
						</tr>
					</table>
				</td>
				<td>
					<table width=100% border=0 cellpadding=2 cellspacing=0 style='font-size:14px;'>
						<tr>
							<td style='text-align:center;font-weight:bold;margin-bottom:12px !important;'>Pengiriman Tagihan dan Faktur Pajak</td>
						</tr>
						<tr>
							<td>{$francoinvc}</td>
						</tr>
						<tr>
							<td>UP. {$francokontakinv}</td>
						</tr>
						<tr>
							<td>{$francoinvcalamat}</td>
						</tr>
						<tr>
							<td>Telp: " . $francoinvcphone . "; Fax" . $francoinvcfax . "</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=2 style='vertical-align: top;'>
					<span style='word-wrap: break-word;word-break: break-all;white-space: normal'>
						" . nl2br($keterangan) . "
					</span>
				</td>
			</tr>
	   </table>";
	if (count($res_po) > 15) {
		$sizefnt = "font-size:12.5px";
		$pdding = 1;
	} else {
		$pdding = 4;
		$sizefnt = "";
	}
	$tab .= "
		<table width='100%' border=0 cellpadding=" . $pdding . " cellspacing=0>
			<thead>
				<tr>
					<th style='text-align:center;border:1px solid black'>No.</th>
					<th style='text-align:center;border:1px solid black'>Nama Barang</th>
					<th style='text-align:center;border:1px solid black'>QTY</th>
					<th style='text-align:center;border:1px solid black'>Unit</th>
					<th style='text-align:center;border:1px solid black'>Harga Satuan (Rp)</th>
					<th style='text-align:center;border:1px solid black'>Jumlah Harga (Rp)</th>
				</tr>
			</thead>
			<tbody>";
	$no = 1;
	$subtotal_0 = 0;
	foreach ($res_po as $val) {
		$tab .= "
					<tr>
						<td style='text-align:center;border-left:1px solid black;border-right:1px solid black;vertical-align: top;" . $sizefnt . "'>" . $no++ . "</td>
						<td style='text-align:left;border-left:1px solid black;border-right:1px solid black;vertical-align: top;" . $sizefnt . "'>" . $optnamabarang[$val['kodebarang']] . " - [" . $val['kodebarang'] . "]</td>
						<td style='text-align:right;border-left:1px solid black;border-right:1px solid black;vertical-align: top;" . $sizefnt . "'>" . number_format($val['jumlahpesan'], 2) . " <span style='color:white'>_</span></td>
						<td style='text-align:right;border-left:1px solid black;border-right:1px solid black;vertical-align: top;" . $sizefnt . "'>" . $val['satuan'] . "<span style='color:white'>_</span></td>
						<td style='text-align:right;border-left:1px solid black;border-right:1px solid black;vertical-align: top;" . $sizefnt . "'>" . number_format($val['hargasatuan'], 2) . " <span style='color:white'>_</span></td>
						<td style='text-align:right;border-left:1px solid black;border-right:1px solid black;vertical-align: top;" . $sizefnt . "'>" . number_format($val['hargasatuan'] * $val['jumlahpesan'], 2) . " <span style='color:white'>_</span></td>
					</tr>";

		$subtotal_0 += $val['hargasatuan'] * $val['jumlahpesan'];
	}

	// subtotal
	$tab .= "<tr>
				<td style='border-left:1px solid black;border-top: 1px solid black;text-align:right' colspan=5>Sub Total <span style='color:white'>_</span></td>
				<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($subtotal_0, 2) . " <span style='color:white'>_</span></td>
			</tr>";
	// ppn
	if ($persenppn > 0) {
		$tab .= "<tr>
					<td style='border-left:1px solid black;text-align:right' colspan=5>PPN " . $persenppn . "% <span style='color:white'>_</span></td>
					<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($ppn, 2) . " <span style='color:white'>_</span></td>
				</tr>";
	}
	// pbbkb
	if ($pbbkb > 0) {
		$tab .= "<tr>
					<td style='border-left:1px solid black;text-align:right' colspan=5>PBBKB <span style='color:white'>_</span></td>
					<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($pbbkb, 2) . "<span style='color:white'>_</span></td>
				</tr>";
	}

	//Sabar Martua : Tambah PPH Final pada Detail PO
	//pphfinal

	if ($pphfinal > 0) {
		$tab .= "<tr>
					<td style='border-left:1px solid black;text-align:right' colspan=5>PPH Final <span style='color:white'>_</span></td>
					<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($pphfinal, 2) . " <span style='color:white'>_</span></td>
				</tr>";
	}

	if ($persenpph > 0) {
		$ketpph = '23';
		if ($pph22>0) {
			$ketpph = '22';
		}
		$tab .= "<tr>
					<td style='border-left:1px solid black;text-align:right' colspan=5>PPH".$ketpph." " . $persenpph . "% <span style='color:white'>_</span></td>
					<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($pph, 2) . " <span style='color:white'>_</span></td>
				</tr>";
	}
	if ($grandtotal > 0) {
		$tab .= "<tr>
					<td style='border:1px solid black;text-align:right;font-weight:bold' colspan=5>Total <span style='color:white'>_</span></td>
					<td style='border:1px solid black;text-align:right' colspan=1>" . number_format($grandtotal, 2) . " <span style='color:white'>_</span></td>
				</tr>";
	}
	$tab .= "</tbody>
		</table>";


	$tab .= "<footer>";
	$cellpadding = 1;
	$tab .= "
		<table width=100% cellspacing=0 cellpadding=1>
			<tr style='text-align:center;vertical-align:top'>
				<td style='width:25%;text-align:left !important;vertical-align:middle;font-size:12px;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000' rowspan=4>
					- No.Rekening harap dicantumkan pada invoice perusahaan Anda. <br>
					- Harap mencantumkan nomor PO ini pada invoice dan surat jalan. <br>
					- Waktu penagihan mohon dilampirkan <br>
					PO asli & faktur/kwitansi asli & material secukupnya. <br>
					- Item barang tidak dapat diganti tanpa persetujuan dari kami.
				</td>
				<td style='width:25%;font-weight:bold;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>
					Disetujui oleh,
				</td>
				<td style='width:25%;font-weight:bold;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5solid #000;text-align:center'>
					Dibuat oleh,
				</td>
				<td style='width:25%;font-weight:bold;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>
					Penyedia/Penjual Barang 
				</td>
			</tr>
			<tr style='text-align:center;vertical-align:middle'>
				<td style='width:25%;height:60px;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center;color:gray;font-size:14px;vertical-align:middle'>
					<i>" . ($aprvkar != '' ? 'ELECTRONICALLY SIGNED BY' : '') . "</i>
				</td>
				<td style='width:25%;height:60px;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center;vertical-align:middle'>&nbsp;</td>
				<td style='width:25%;height:60px;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center;vertical-align:middle'>&nbsp;</td>
			</tr>
			<tr style='text-align:center;vertical-align:top'>
				<td style='width:25%;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center;padding:10px 0;'>
					" . ($aprvkar != '' ? $aprvkar : '') . "
				</td>
				<td style='width:25%;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center;padding:10px 0;'>
					" . $optnmkar[$purchaser] . "
				</td>
				<td style='width:25%;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center;padding:10px 0;'>
					&nbsp;
				</td>
			</tr>
			<tr style='text-align:center;vertical-align:bottom;font-size:12px;'>
				<td style='width:25%;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;padding:10px 0;'>
					" . ($apprvkarid != '' ? getJabatanKaryawan($aprvkarid) : '') . "
				</td>
				<td style='width:25%;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;padding:10px 0;'>
					" . getJabatanKaryawan($purchaser) . "
				</td>
				<td style='width:25%;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000;text-align:center;padding:10px 0;'>
					" . $namasupplier . "
				</td>
			</tr>
		</table>
		";
	$tab .= "</footer>";
}

$tab .= "</div>";

// exit("Error:$tab");

// $tab->getCanvas()->text(30, 20, 'text', $font, 12);
$dompdf = new Dompdf();
$dompdf->loadHtml($tab);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$sizefont = 12;
$font = $dompdf->getFontMetrics()->get_font("Times-Roman", "");

$dompdf->getCanvas()->page_text('475', '15', "" . $nopo . "", $font, ($sizefont - 4), array(0, 0, 0), 0, 0, 0);
$dompdf->getCanvas()->page_text('475', '25', "Page : {PAGE_NUM} / {PAGE_COUNT} ", $font, ($sizefont - 4), array(0, 0, 0), 0, 0, 0);
// $dompdf->getCanvas()->text(56, 20, 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', $font, 12);

## Print Out
if ($urlefil == '0') {
	$dompdf->stream("PrintPOSO_" . $column, array("Attachment" => 0));
} else {
	file_put_contents($urlefil, $dompdf->output());
}

// $dompdf = new Dompdf();
// $options = $dompdf->getOptions();
// $options->set(array('isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true));
// $dompdf->loadHtml($dokumen);
// $dompdf->setPaper('A4', 'portrait');
// $dompdf->setOptions($options);
// $dompdf->render();
// $dompdf->stream('Dayoff_Nonstaff.pdf', array('Attachment' => 0));
//asli lapar bangettt
