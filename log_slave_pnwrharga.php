<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';

use Dompdf\Dompdf;

$method = checkPostGet('method', '');
$xmethod = checkPostGet('xmethod', '');
$pages = checkPostGet('page', '');

$kdPt = checkPostGet('kdPt', '');

##PARAM SEARCH
$crnotransaksi = checkPostGet('crnotransaksi', '');
$crnopp = checkPostGet('crnopp', '');
$crtanggal = checkPostGet('crtanggal', '');
$schnopp = checkPostGet('schnopp', '');
$schjenis = checkPostGet('schjenis', '');
$schunit = checkPostGet('schunit', '');
$schpt = checkPostGet('schpt', '');
$schklbrg = checkPostGet('schklbrg', '');
$schkdbrg = checkPostGet('schkdbrg', '');

$countbaris = checkPostGet('baris', '');

$supplier_id = checkPostGet('id_supplier', '');
$norurut = checkPostGet('norurut', '');
$id_alamat_supplier = checkPostGet('id_alamat_supplier', '');

$nopp2 = checkPostGet('nopp2', '');
$formPil = checkPostGet('formPil', '');

$notransaksi = checkPostGet('notransaksi', '');

$no_prmntan = checkPostGet('ckno_permintaan', '');

$nourut = checkPostGet('nourut', '');
$ongkir = checkPostGet('ongkir', '');
$totalongkir = checkPostGet('totalongkir', '');
$nilDiskon = checkPostGet('nilDiskon', '');
$diskonPersen = checkPostGet('diskonPersen', '');
$nilPPn = checkPostGet('nilPPn', '');
$nilPPh = checkPostGet('nilPPh', '');
$nilPPh22 = checkPostGet('nilPPh22', '');
$pbbkb = checkPostGet('pbbkb', '');
$pphfinal = checkPostGet('pphfinal', '');
$nilaiPermintaan = checkPostGet('nilaiPermintaan', '');
$subTotal = checkPostGet('subTotal', '');
$termPay = checkPostGet('termPay', '');
$idFranco = checkPostGet('idFranco', '');
$stockId = checkPostGet('stockId', '');
$ketUraian = checkPostGet('ketUraian', '');
$tglDari = checkPostGet('tglDari', '');
$tglSmp = checkPostGet('tglSmp', '');
$mtUang = checkPostGet('mtUang', '');
$kurs = checkPostGet('kurs', '');
$kriteriaefil = checkPostGet('kriteriaefil', '');

$notransaksi = checkPostGet('notransaksi', '');
$supplierid = checkPostGet('supplierid', '');
$namafile = checkPostGet('namafile', '');

$no_permintaan = checkPostGet('no_permintaan', '');

$xnomor = checkPostGet('xnomor', '');
$xnourut = checkPostGet('xnourut', '');

$nopp = checkPostGet('nopp', '');
$kodebarang = checkPostGet('kodebarang', '');
$myimage = checkPostGet('myimage', '');
$keterangan = checkPostGet('keterangan', '');
$lokasipengiriman = checkPostGet('lokasipengiriman', '');

$durasipengiriman = checkPostGet('durasipengiriman', '');
$durasipekerjaan = checkPostGet('durasipekerjaan', '');
$garansiprodukjasa = checkPostGet('garansiprodukjasa', '');
$posisistokbarang = checkPostGet('posisistokbarang', '');
$asuransi = checkPostGet('asuransi', '');

$emodul = "RPH";
$optpt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');

if ($xmethod != '') {
	$str = "select a.*,right(a.nomor,4) as kodept, b.alamat, b.telepon, b.fax 
		from " . $dbname . ".log_perintaanhargaht a 
		left join " . $dbname . ".log_5supalamat b on a.id_alamat_supplier = b.id_alamat 
		where a.nomor='" . $xnomor . "' and a.nourut='" . $xnourut . "'";
	$res = fetchdata($str);
	$kodeunit = $res[0]['kodept'];
	$supplierid = $res[0]['supplierid'];
	$alamat = $res[0]['alamat'];
	$telepon = $res[0]['telepon'];
	$fax = $res[0]['fax'];
	$matauang = $res[0]['matauang'];
	$kurs = $res[0]['kurs'];

	$optpt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $kodeunit . "'");
	$kodept = $optpt[$kodeunit];

	$arrHead = setheadreport('', $kodept);
	$path = $arrHead['logo'];

	$str = "select namaorganisasi,alamat,wilayahkota,telepon from " . $dbname . ".organisasi where kodeorganisasi='" . $kodept . "'";
	$res = fetchdata($str);
	$namapt = $res[0]['namaorganisasi'];
	$alamatpt = $res[0]['alamat'] . ", " . $res[0]['wilayahkota'];
	$telp = $res[0]['telepon'];

	$str = "select npwp,alamatnpwp from " . $dbname . ".setup_org_npwp where kodeorg='" . $kodept . "'";
	$res = fetchdata($str);
	$npwp = $res[0]['npwp'];
	$alamatnpwp = $res[0]['alamatnpwp'];

	$str = "select * from " . $dbname . ".log_5supplier where supplierid='" . $supplierid . "'";
	$res = fetchdata($str);
	$namasupplier = $res[0]['namasupplier'];

	$tab .= "<table cellpadding=3 cellspacing=0 border=0 style='width:100%;font-size:11px;margin-top:-30px;'>
		<tr>
			<td colspan=2 rowspan=5 style='border-bottom:1px solid #000000;text-align:center;vertical-align:middle'>
				<img src=" . $arrHead['logo'] . " width=80px></img>
			</td>
			<td colspan=7>" . $arrHead['nama'] . "</td>
		</tr>
		<tr>
			<td colspan=7>" . $arrHead['alamat'] . "</td>
		</tr>
		<tr>
			<td colspan=7>Telp : " . $arrHead['telepon'] . "</td>
		</tr>
		<tr>
			<td colspan=7>NPWP : " . $npwp . "</td>
		</tr>
		<tr>
			<td colspan=7 style='border-bottom:1px solid #000000'>" . $_SESSION['lang']['alamat'] . " NPWP : " . $alamatnpwp . "</td>
		</tr>
		
		<tr>
			<td colspan=9>&nbsp;</td>
		</tr>
		
		<tr>
			<td colspan=9 style='font-weight:bold;text-align:center;size:large'>" . strtoupper($_SESSION['lang']['permintaan_harga']) . "</td>
		</tr>
		
		<tr>
			<td colspan=9>&nbsp;</td>
		</tr>
		
		<tr>
			<td colspan=2 style='width:100px;'>No Transaksi</td>
			<td style='width:5px'>:</td>
			<td colspan=6>" . $xnomor . "</td>
		</tr>
		<tr>
			<td colspan=2>" . $_SESSION['lang']['nm_perusahaan'] . "</td>
			<td>:</td>
			<td colspan=6>" . $namasupplier . "</td>
		</tr>
		<tr>
			<td colspan=2>" . $_SESSION['lang']['alamat'] . "</td>
			<td>:</td>
			<td colspan=6>" . $alamat . "</td>
		</tr>
		<tr>
			<td colspan=2>" . $_SESSION['lang']['telp'] . "</td>
			<td>:</td>
			<td colspan=6>" . $telepon . "</td>
		</tr>
		<tr>
			<td colspan=2>" . $_SESSION['lang']['fax'] . "</td>
			<td>:</td>
			<td colspan=6>" . $fax . "</td>
		</tr>
		<tr>
			<td colspan=2>" . $_SESSION['lang']['matauang'] . "</td>
			<td>:</td>
			<td colspan=6>" . $matauang . "</td>
		</tr>
		<tr>
			<td colspan=2>" . $_SESSION['lang']['kurs'] . "</td>
			<td>:</td>
			<td colspan=6 style='text-align:left'>" . $kurs . "</td>
		</tr>
		
		<tr>
			<td colspan=9>&nbsp;</td>
		</tr>
		
		<tr>
			<td colspan=9>" . strtoupper($_SESSION['lang']['hal']) . "</td>
		</tr>
		
		<tr>
			<td colspan=9>" . $_SESSION['lang']['isi_permintaan'] . "</td>
		</tr>
		
		<tr>
			<td colspan=9>&nbsp;</td>
		</tr>
		
		<tr style='background-color:grey'>
			<td style='text-align:center;border-left:1px solid #000000;border-top:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000;width:5px'>No</td>
			<td  colspan=4 style='text-align:center;border-top:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000'>" . $_SESSION['lang']['namabarang'] . "</td>
			<td style='text-align:center;border-top:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000'>" . $_SESSION['lang']['satuan'] . "</td>
			<td style='text-align:center;border-top:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000'>" . $_SESSION['lang']['jumlah'] . "</td>
			<td style='text-align:center;border-top:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000'>" . $_SESSION['lang']['hargasatuan'] . "</td>
			<td style='text-align:center;border-top:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000'>" . $_SESSION['lang']['keterangan'] . "</td>
		</tr>";

	$str = "select * from " . $dbname . ".log_permintaanhargadt where nomor='" . $xnomor . "' and nourut='" . $xnourut . "'";
	$res = fetchdata($str);
	$no = 0;
	$subTotal = 0;
	foreach ($res as $key => $val) {
		$no++;
		$subTotal += $subTotal;
		$kodebarang = $val['kodebarang'];
		$jumlah = $val['jumlah'];
		$namabarang = '';

		$strx = "select * from " . $dbname . ".log_5masterbarang where kodebarang='" . $kodebarang . "'";
		$resx = fetchdata($strx);
		$namabarang = $resx[0]['namabarang'];
		$satuan = $resx[0]['satuan'];

		$tab .= "<tr>
				<td style='border-left:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000;text-align:center'>" . $no . "</td>
				<td colspan=4 style='border-right:1px solid #000000;border-bottom:1px solid #000000'>" . $namabarang . "<br>" . $val['spec'] . "</td>
				<td style='border-right:1px solid #000000;border-bottom:1px solid #000000;text-align:center'>" . $satuan . "</td>
				<td style='border-right:1px solid #000000;border-bottom:1px solid #000000;text-align:center'>" . number_format($val['jumlah'], 2, '.', ',') . "</td>
				<td style='border-right:1px solid #000000;border-bottom:1px solid #000000'></td>
				<td style='border-right:1px solid #000000;border-bottom:1px solid #000000'></td>
			</tr>";
	}

	$tab .= "<tr>
			<td colspan=9>Note: " . $_SESSION['lang']['note_permintaan'] . "</td>
		</tr>
		<tr>
			<td colspan=9>&nbsp;</td>
		</tr>";

	$str = "select * from " . $dbname . ".log_perintaanhargaht where nomor='" . $xnomor . "' and nourut='" . $xnourut . "'";
	$res = fetchdata($str);
	$xid_franco = $res[0]['id_franco'];
	$xtgldari = $res[0]['tgldari'];
	$xoptFranco =  makeOption($dbname, 'setup_franco', 'id_franco,franco_name');

	$tab .= "<tr>
			<td colspan=2>" . $_SESSION['lang']['franco'] . "</td>
			<td>:</td>
			<td colspan=6>" . $xoptFranco[$xid_franco] . "</td>
		</tr>
		<tr>
			<td colspan=2>" . $_SESSION['lang']['waktu'] . " " . $_SESSION['lang']['pengiriman'] . "</td>
			<td>:</td>
			<td colspan=6 style='text-align:left'>" . tanggalnormal($xtgldari) . "</td>
		</tr>
		
		<tr>
			<td colspan=9>&nbsp;</td>
		</tr>
		
		<tr>";
	if ($_SESSION['language'] == 'EN') {
		$tab .= "<td colspan=9>Please explain the price conditions and tax if included. Thankyou for your coorporation.</td>";
	} else {
		$tab .= "<td colspan=9>Harga harap di jelaskan secara lengkap termasuk PPn atau tidak. Terima kasih atas perhatian dan kerjasamanya.</td>";
	}
	$tab .= "</tr>";

	$tab .= "</table>";

	if ($xmethod == 'excel') {
		$namasupplier = str_replace(" ", "_", $namasupplier);
		$namasupplier = str_replace(",", "", $namasupplier);
		// exit("Error:".$namasupplier);
		$nop = "Perbandingan_Harga_" . $namasupplier . ".xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet($namasupplier, $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
	} else {
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream("Perbandingan_Harga_" . $namasupplier, array("Attachment" => false));
	}
} else {
	switch ($method) {

		case 'getkurs':

			#= ambil tanggal
			$str = "select tanggal from " . $dbname . ".log_perintaanhargaht where nomor='" . $no_permintaan . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$tanggal = $bar['tanggal'];

			$str = "select kurs from " . $dbname . ".setup_matauangrate where kode='" . $mtUang . "' and daritanggal<='" . $tanggal . "' order by daritanggal desc limit 1";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$kurs = $bar['kurs'];

			echo $kurs;


			break;

		case 'loaddata':
			$tab = "";
			$limit = 20;
			$page = 0;
			if (isset($pages)) {
				$page = $pages;
				if ($page < 0)
					$page = 0;
			}
			$offset = $page * $limit;

			$where = "";
			$wheredt = "";
			if (@$crnotransaksi != '') {
				$where .= " and a.nomor like '%" . $crnotransaksi . "%'";
				$wheredt .= " and nomor like '%" . $crnotransaksi . "%'";
			}

			if (@$crnopp != '') {
				$where .= " and b.nopp like '%" . $crnopp . "%'";
			}

			if (@$crtanggal != '') {
				$txt_tgl = tanggalsystemn($crtanggal);
				$where .= " and a.tanggal LIKE '" . $txt_tgl . "'";
			}

			$sNopp = "select distinct nomor,nopp from " . $dbname . ".log_permintaanhargadt b where 1=1 " . $wheredt . "";
			$rNopp = fetchData($sNopp);
			foreach ($rNopp as $key => $val) {
				$arrNopp[$val['nomor']][] = $val['nopp'];
			}

			$jlhbrs = 0;
			$str = "select a.* from " . $dbname . ".log_perintaanhargaht a left join " . $dbname . ".log_permintaanhargadt b on a.nomor=b.nomor  where a.purchaser='" . $_SESSION['standard']['userid'] . "' " . $where . " group by a.nomor order by a.tanggal desc";
			$res = fetchdata($str);
			$jlhbrs = count($res);

			$no = 0;
			$str = "SELECT a.* FROM " . $dbname . ".log_perintaanhargaht a left join " . $dbname . ".log_permintaanhargadt b on a.nomor=b.nomor  where a.purchaser='" . $_SESSION['standard']['userid'] . "' " . $where . " group by a.nomor ORDER BY a.tanggal DESC LIMIT " . $offset . "," . $limit . "";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$no += 1;
				// $strx="select * from ".$dbname.".log_permintaanhargadt where nomor='".$bar['nomor']."' and ";
				// $dtkr="select * from ".$dbname.".datakaryawan where karyawanid='".$res2['purchaser']."'";
				// $qdtkr=$owlPDO->query($dtkr) or die(print " Gagal: ".PDOException::getMessage());
				// $qdtkr->setFetchMode(PDO::FETCH_OBJ);
				// $rdtkr=$qdtkr->fetch();

				// $splr="select * from ".$dbname.".log_5supplier where supplierid='".$res2['supplierid']."'"; //echo $splr;
				// $qsuplr=$owlPDO->query($splr) or die(print " Gagal: ".PDOException::getMessage());
				// $qsuplr->setFetchMode(PDO::FETCH_OBJ);
				// $rsplr=$qsuplr->fetch();

				// if($res2['ppn']!=0){
				// $ppn=($res2['ppn']/($res2['subtotal']-$res2['nilaidiskon']))*100;
				// }

				##pengecekan ditolak PO
				$nopo = '';
				$norph = '';
				$komentar = '';
				$str2 = "select norph from " . $dbname . ".log_permintaanhargadt where nomor='" . $bar['nomor'] . "'  and norph!=''";
				$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_ASSOC);
				$bar2 = $res2->fetch();
				$norphsave = $bar2['norph'];
				$norph = $bar2['norph'];

				if ($bar['tolakrph'] == 0 && $bar['nodphlama'] != '') {
					if ($norph == '') {
						$str2 = "select norph from " . $dbname . ".log_permintaanhargadt where nomor='" . $bar['nomor'] . "'";
						$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
						$res2->setFetchMode(PDO::FETCH_ASSOC);
						$bar2 = $res2->fetch();
						$norph = $bar['nodphlama'];
					}
				}

				if ($norph != '') {
					$str2 = "select nopo from " . $dbname . ".log_poht_del where nodph='" . $norph . "'";
					$res2 = fetchdata($str2);
					$jlhbrs1 = count($res2);
					$bar2 = $res2[0];
					$nopo = $bar2['nopo'];

					if ($jlhbrs1 == 0) {
						$str2 = "select nopo from " . $dbname . ".log_poht where nodph='" . $norph . "'";
						$res2 = fetchdata($str2);
						$bar2 = $res2[0];
						$nopo = $bar2['nopo'];
					}
					$maxDtSetuju = 2;
					$str2 = "select * from " . $dbname . ".approval where notransaksi='" . $nopo . "' and status='2'";
					$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar2 = $res2->fetch()) {
						if ($maxDtSetuju != 0) {
							$optkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar2['karyawanid'] . "'");
							$komentar .= $optkar[$bar2['karyawanid']] . " : " . $bar2['komentar'] . " \n";
						}
						if ($maxDtSetuju <= 0) {
							continue;
						}
						$maxDtSetuju -= 1;
					}
				}

				if ($bar['nodphlama'] != '') {
					$optNoDph = makeOption($dbname, 'log_permintaanhargadt', 'norph,nomor', "norph='" . $bar['nodphlama'] . "'");
					$komentar .= "\n No.referensi DPH lama : " . $bar['nodphlama'];
				}

				$bgcolor = "";
				if ($bar['tolakrph'] == 1) {
					$bgcolor = 'style=background:orange';
				}

				if ($bar['nodphlama'] != '' && $norphsave == '') {
					$bgcolor = 'style=background:Springgreen';
				}

				#periksa chat
				$strChat = "select *  from " . $dbname . ".log_rfq_chat where nomor='" . $bar['nomor'] . "'";
				$resChat = $owlPDO->query($strChat) or die(print " Gagal: " . PDOException::getMessage());
				if (owlBaris($resChat) > 0) {
					$ingChat = "<img src='images/chat1.png' onclick=\"loadRFQChat('" . $bar['nomor'] . "',event);\" class=resicon>";
				} else {
					$ingChat = "<img src='images/chat0.png'  onclick=\"loadRFQChat('" . $bar['nomor'] . "',event);\" class=resicon>";
				}


				$tab .= "<tr class=rowcontent " . $bgcolor . ">
				<td style='text-align:right;vertical-align:top'>" . $no . "</td>
				<td style='vertical-align:top;color:blue;cursor:pointer;min-width:150px' onclick=\"previewlink('" . $bar['nomor'] . "', '', 'Detail Riwayat Perbandingan Harga' ,event)\">" . $bar['nomor'] . "</td>
				<td style='text-align:center;vertical-align:top;cursor:pointer;min-width:70px' title='" . $komentar . "'>" . tanggalnormal($bar['tanggal']) . "</td>
				";
				$tab .= "<td style='text-align:left;vertical-align:top;min-width:150px'><ol type=1>";
				$lstNopp = $arrNopp[$bar['nomor']];
				if (@count($lstNopp) != 0) {
					foreach ($lstNopp as $key) {
						$tab .= "<li>" . $key . "</li>";
					}
				}

				$tab .= "</ol></td>";
				$tab .= "<td style='text-align:center;vertical-align:top'>" . $ingChat . "</td>";
				$tab .= "<td style='text-align:left;vertical-align:top'>";
				$tab .= "<table width=100%>";

				$str2 = "select a.supplierid,b.namasupplier,a.nourut from " . $dbname . ".log_perintaanhargaht a left join " . $dbname . ".log_5supplier b on a.supplierid=b.supplierid where a.nomor='" . $bar['nomor'] . "'";
				$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_ASSOC);
				$nosup = 0;
				while ($bar2 = $res2->fetch()) {
					$nosup++;
					$tab .= "<tr>
					<td width=20px>" . $nosup . ".</td>
					<td>" . $bar2['namasupplier'] . "</td>
					<td width=20px align=center>
						<img src=images/pdf.jpg class=resicon  title='Print Pdf' onclick=\"xPdf(event,'log_slave_pnwrharga.php','" . $bar['nomor'] . "','" . $bar2['nourut'] . "');\">
					</td>
					<td width=20px align=center>
						<img src=images/excel.jpg class=resicon  title='Print Excel' onclick=\"xExcel(event,'log_slave_pnwrharga.php','" . $bar['nomor'] . "','" . $bar2['nourut'] . "');\">
					</td>
				</tr>";
				}

				$tab .= "</table></td>";


				#update baru jika sudah ada dipo (
				#gap terbaru dipo dapat langsung mangambil data dari dph)

				// $str="select count(*) as dtpo from ".$dbname.".log_poht where nodph='".$res2['nomor']."' ";
				// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				// $res->setFetchMode(PDO::FETCH_ASSOC);
				// $bar=$res->fetch();
				// $dtpo=$bar['dtpo'];

				$status = "";
				if ($bar['flag'] == 0 && $bar['purchaser'] == $_SESSION['standard']['userid']) {
					$status .= "
					<img src=images/application/application_edit.png class=resicon  title='Edit Quotation Request' onclick=\"zPreview2('log_slave_pnwrharga','" . $bar['nomor'] . "','printContainer2');\">
					<img src=images/plus.png class=resicon  title='Tambah Supplier ' onclick=\"addSupplierPlus('" . $bar['nomor'] . "','" . $bar['nourut'] . "');\">";
					if ($bar['tolakrph'] == 0) {
						$status .= "&nbsp;<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPer1('" . $bar['nomor'] . "','" . $bar['nourut'] . "');\">";
					}
					$status .= "<img src=images/skyblue/posting.png class=resicon  title='Ajukan RPH' onclick=\"alasan_batal('" . $bar['nomor'] . "','" . $bar['nourut'] . "');\" style='margin-left:4px' >";
				}
				// if($res2['flag']==1){
				// $status.="<img src=images/box/drop-yes.gif title='Pemenang Tender' class=resicon);\">";
				// } else{
				// $status.="<img src=images/box/drop-no.gif class=resicon);\">";
				// }
				$vkomentar = "";
				if ($bar['komentar'] == '') {
					$vkomentar .= $komentar;
				} else {
					if ($komentar != '') {
						$vkomentar .= $bar['komentar'] . "<br>" . $komentar;
					} else {
						$vkomentar .= $bar['komentar'];
					}
				}

				$tab .= "<td style='vertical-align:top;text-align:left;max-width:350px;'>" . $vkomentar . "</td>
			<td style='vertical-align:top;text-align:center;min-width:100px'>
				" . $status . "
				<!--<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_perintaanhargaht','" . $bar['nomor'] . "," . $bar['nourut'] . "','','log_slave_print_permintaan_penawaran',event);\">-->
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_perintaanhargaht','" . $bar['nomor'] . "," . $bar['nourut'] . "','','log_slave_print_permintaan_penawaran_v2',event);\">  
				<img src=images/uploader/dwnld.png class=resicon title=Upload onClick=\"loadUploadPo('" . $bar['nomor'] . "', event)\" />  						
				<img onclick=datakeExcel(event,'" . $bar['nomor'] . "') src=images/excel.jpg class=resicon title='MS.Excel'> 						
			</td>";

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

			$frompage = (($page * $limit) + 1);
			if ((($page + 1) * $limit) > $jlhbrs) {
				$topage = $jlhbrs;
			} else {
				$topage = (($page + 1) * $limit);
			}
			$tab .= "</tr>
		<tr>
			<td colspan=7 align=center>
				" . $frompage . " to " . $topage . " Of " .  $jlhbrs . "
			</td>
		</tr>
		<tr>
			<td colspan=7 align=center>";

			if ($page == '0') {
				$tab .= "";
			} else {
				$tab .= "<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
			}

			$tab .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";

			if (($page + 1) == $totrows) {
				$tab .= "";
			} else {
				$tab .= "<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
			}
			$tab .= "</td></tr>";

			echo $tab;
			break;

		case 'delrfq':
			$kodebarang = checkPostGet('kodebarang', '');
			$notransaksi = checkPostGet('notransaksi', '');

			$str = "select * from " . $dbname . ".log_permintaanhargadt where nomor='" . $notransaksi . "' group by kodebarang";
			$res = fetchdata($str);
			$nopr = $res[0]['nopp'];
			if (count($res) <= 1) {
				exit("Gagal : Jumlah item barang hanya 1 silahkan dihapus dari list data");
			}

			$str = "delete from " . $dbname . ".log_permintaanhargadt where nomor='" . $notransaksi . "' and kodebarang='" . $kodebarang . "'";
			$owlPDO->exec($str);

			$str = "select sum(harga*jumlah) as jumlah, nourut from " . $dbname . ".log_permintaanhargadt where nomor='" . $notransaksi . "' group by nourut";
			$res = fetchdata($str);
			foreach ($res as $val) {
				$strx = "select nilaidiskon,pbbkb from " . $dbname . ".log_perintaanhargaht where nomor='" . $notransaksi . "' and nourut='" . $val['nourut'] . "'";
				$resx = fetchdata($strx);
				$edtnilaidiskon = $resx[0]['nilaidiskon'];
				$edtpbbkb = $resx[0]['pbbkb'];
				$edttotal = $val['jumlah'] - $edtnilaidiskon + $edtpbbkb;

				$strx = "update " . $dbname . ".log_perintaanhargaht set subtotal='" . $val['jumlah'] . "', nilaipermintaan='" . $edttotal . "' where nomor='" . $notransaksi . "' and nourut='" . $val['nourut'] . "'";
				$owlPDO->exec($strx);
			}

			$str = "update " . $dbname . ".log_listverifikasi set status='0' where nopp='" . $nopr . "' and kodebarang='" . $kodebarang . "'";
			$owlPDO->exec($str);
			break;

		case 'getNotifikasi':
			$tab = "";
			$str = "select distinct kodeorganisasi from " . $dbname . ".organisasi where tipe='PT' and kodeorganisasi in (".getOrgDetail(4).")";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$dafUnit[] = $bar['kodeorganisasi'];
			}

			$tab .= "<table border=0><tr>";

			$ared = 0;
			foreach ($dafUnit as $lstKdOrg) {
				// $ared+=1;
				// if($ared==1)
				// {
				// $tab.="<tr>";
				// }

				$str = "select count(*) as jmlhJob from " . $dbname . ".log_listverifikasi a 
			left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
			where a.karyawanid='" . $_SESSION['standard']['userid'] . "' and a.status='0' and a.skip='0' and pemenang='0' and b.pt='" . $lstKdOrg . "'";

				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$rBaros = owlBaris($res);
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				if ($bar['jmlhJob'] != 0) {
					$tab .= "<td style='text-align:center'><table style='border:1px solid #215C1F;border-radius: 5px;'>";

					$tab .= "<tr><td style='text-align:center'>" . $lstKdOrg . "</td></tr>";
					$tab .= "<tr><td style='text-align:center'>" . ($bar['jmlhJob'] == 0 ? $bar['jmlhJob'] : "<a href='#' onclick=\"getDtPP('" . $lstKdOrg . "')\">" . $bar['jmlhJob'] . "</a>") . "</td></tr>";

					$tab .= "</table></td>";
				}
			}

			$tab .= "</tr></table>";

			echo $tab;
			break;

		case 'getBarangPP':
			$no = 0;
			$tab = '';
			$str = "select a.*,c.realisasi,c.satuankonversi,d.jumlahpesan, c.nokontrak,c.hasilkonversi, b.ket_balik from " . $dbname . ".log_listverifikasi a 
			left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
			left join " . $dbname . ".log_prapodt c on a.nopp=c.nopp and a.kodebarang = c.kodebarang 
			left join " . $dbname . ".log_podt d on a.nopp=d.nopp and a.kodebarang = d.kodebarang 
			where a.karyawanid='" . $_SESSION['standard']['userid'] . "' and a.status='0' and skip='0' and pemenang='0' and b.pt='" . $kdPt . "' group by a.karyawanid,a.nopp,a.kodebarang order by c.tgl_sdt asc, a.nopp asc";
			// echo $str;
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$countitem = owlBaris($res);
			while ($bar = $res->fetch()) {
				$jumlah = 0;
				$optBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $bar['kodebarang'] . "'");
				$optSat = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan', "kodebarang='" . $bar['kodebarang'] . "'");
				if ($bar['satuankonversi'] == '' || is_null($bar['satuankonversi'])) {
					$mySatuan = $optSat[$bar['kodebarang']];
					$jumlah = $bar['realisasi'];
				} else {
					$mySatuan = $bar['satuankonversi'];
					$jumlah = $bar['hasilkonversi'];
				}

				// if(($bar['jumlahpesan']=='')||is_null($bar['jumlahpesan'])||$bar['jumlahpesan']==0)
				// {
				// $jumlah = $bar['realisasi'];
				// }
				// elseif($bar['jumlahpesan']!=$bar['realisasi']) 
				// {
				// $jumlah = $bar['realisasi']-$bar['jumlahpesan'];
				// }
				// else 
				// {
				// $jumlah = $bar['realisasi'];
				// }
				$no++;
				$tab .= "<tr class=rowcontent>
				<td style=width:30px align=center>" . $no . "</td>
				<td style='width:180px;cursor:pointer;color:blue' id='nopplst_" . $no . "' onclick=\"previewlinkdt('" . $bar['nopp'] . "','" . $bar['kodebarang'] . "',event)\" >" . $bar['nopp'] . "</td>
				<td style='width:90px' align=center id=kodebrg_" . $no . ">" . $bar['kodebarang'] . "</td>";
				if ($bar['ket_balik'] != '') {
					$tab .= "<td style='width:380px;background-color:#64FB76'>" . $optBarang[$bar['kodebarang']] . "<br>Become Out Standing : " . $bar['ket_balik'] . "</td>";
				} else {
					$tab .= "<td style=width:380px>" . $optBarang[$bar['kodebarang']] . "</td>";
				}
				$tab .= "<td style='width:70px' align=right id=jumlah_" . $no . ">" . number_format($jumlah) . "</td>
				<td style=width:50px>" . $mySatuan . "</td>
				<td style=width:50px id='nokontrak_" . $no . "'>" . $bar['nokontrak'] . "</td>
				<td  style='width:20px' align=center><input type=checkbox id=pilBrg_" . $no . " onclick=\"cekchklist('" . $no . "','" . $countitem . "')\" /></td>
			</tr>";
			}
			$tab .= "<tr><td colspan=7 align=center>
			<button class=mybutton onclick=lanjutAdd() >" . $_SESSION['lang']['lanjut'] . "</button>
			<button class=mybutton onclick=skiprph() style='display:none'>Skip</button>
		</td></tr>";

			$optunit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
			$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $_POST['kdPt'] . "' order by namaorganisasi asc";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optunit .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['namaorganisasi'] . "</option>";
			}

			echo $tab . "###" . $optunit . "###" . $kdPt;
			break;

		case 'schgetDtPP':
			$no = 0;
			$tab = '';

			$where = " and b.pt='" . $schpt . "'";

			if ($schnopp != '') {
				$where .= " and a.nopp like '%" . $schnopp . "%' ";
			}

			if ($schklbrg != '') {
				$where .= " and left(a.kodebarang,3) = '" . $schklbrg . "' ";
			}

			if ($schkdbrg != '') {
				$where .= " and a.kodebarang = '" . $schkdbrg . "' ";
			}

			if ($schunit != '') {
				$where .= " and a.nopp like '%" . $schunit . "%' ";
			}

			if ($schjenis != '') {
				$where .= " and a.kodebarang in (select kodebarang from " . $dbname . ".log_5masterbarang where jenis='" . $schjenis . "')";
			}

			$no = 0;
			$tab = '';
			$str = "select a.*,c.realisasi,d.jumlahpesan, c.nokontrak,c.satuankonversi,c.hasilkonversi from " . $dbname . ".log_listverifikasi a 
			left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
			left join " . $dbname . ".log_prapodt c on a.nopp=c.nopp and a.kodebarang = c.kodebarang 
			left join " . $dbname . ".log_podt d on a.nopp=d.nopp and a.kodebarang = d.kodebarang 
			where a.karyawanid='" . $_SESSION['standard']['userid'] . "' and a.status='0' and skip='0' and pemenang='0'  " . $where . " group by a.karyawanid,a.nopp,a.kodebarang order by c.tgl_sdt asc, a.nopp asc";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$jlhkolom = owlBaris($res);
			$res->setFetchMode(PDO::FETCH_ASSOC);

			if ($jlhkolom <= 0) {
				$tab .= "<tr class=rowcontent><td colspan=7 align=center style='width:838px'>" . $_SESSION['lang']['datanotfound'] . "</td></tr>";
			} else {
				while ($bar = $res->fetch()) {
					$optBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $bar['kodebarang'] . "'");
					$optSat = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan', "kodebarang='" . $bar['kodebarang'] . "'");
					$jumlah = 0;
					if ($bar['satuankonversi'] == '' || is_null($bar['satuankonversi'])) {
						$mySatuan = $optSat[$bar['kodebarang']];
						$jumlah = $bar['realisasi'];
					} else {
						$mySatuan = $bar['satuankonversi'];
						$jumlah = $bar['hasilkonversi'];
					}
					$no++;
					$tab .= "<tr class=rowcontent>
					<td style=width:30px align=center>" . $no . "</td>
					<td style='width:180px' id=nopplst_" . $no . ">" . $bar['nopp'] . "</td>
					<td style='width:90px' align=center id=kodebrg_" . $no . ">" . $bar['kodebarang'] . "</td>
					<td style=width:380px>" . $optBarang[$bar['kodebarang']] . "</td>
					<td style='width:70px' align=right id=jumlah_" . $no . ">" . number_format($jumlah) . "</td>
					<td style=width:50px>" . $mySatuan . "</td>
					<td style=width:50px id='nokontrak_" . $no . "'>" . $bar['nokontrak'] . "</td>
					<td  style='width:25px' align=center><input type=checkbox id=pilBrg_" . $no . " onclick=\"cekchklist('" . $no . "','" . $jlhkolom . "')\" /></td>
				</tr>";
				}
				$tab .= "<tr><td colspan=7 align=center><button class=mybutton onclick=lanjutAdd() >" . $_SESSION['lang']['lanjut'] . "</button></td></tr>";
			}

			echo $tab;
			break;

		case 'cekBarang':
			$_SESSION['bgimage'] = array();
			$tab = "";
			foreach ($_POST['lstnopp'] as $row => $Rslt) {
				for ($a = 0; $a < $row; $a++) {
					for ($b = 0; $b < $_POST['baris']; $b++) {
						if ($a != $b) {
							if (@$_POST['kdbrg'][$a] == @$_POST['kdbrg'][$b]) {
								@$cek += 1;
								$cekBrg2 = $_POST['kdbrg'][$a];
							}
						}
					}
				}
				$optnmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $_POST['kdbrg'][$row] . "'");
				$nmbrg = $optnmbrg[$_POST['kdbrg'][$row]];
				$tab .= "<tr class='rowcontent'>
				<td style='text-align:center;vertical-align:top'>" . $_POST['kdbrg'][$row] . "</td>
				<td style='text-align:left;vertical-align:top'>" . $nmbrg . "</td>
				<td style='text-align:left;vertical-align:top;color:blue;cursor:pointer' onclick=\"previewlinkdt('" . $Rslt . "','" . $_POST['kdbrg'][$row] . "',event)\">" . $Rslt . "</td>
				<td style='text-align:center;vertical-align:top'>
					<img title='Add File Upload' class='resicon' onclick=\"addfileupload('" . $Rslt . "','" . $_POST['kdbrg'][$row] . "',event)\" src='images/plus.png'>
				</td>
				<td style='text-align:left;vertical-align:top'>
					<div id='" . $_POST['kdbrg'][$row] . "_" . $Rslt . "'></div>
				</td>
			</tr>";
			}

			echo $tab;
			break;

		case 'createrfq':
			#ambil kodept jika holding, unit berdasarkan pp jika selain holding
			$noppx = $_POST['lstnopp'][0];
			$nokontrak = $_POST['lstkontrak'][0];
			$nopplist =  explode('/', $noppx);
			$unit = $nopplist[4];
			if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
				$iOrg = "select pt from " . $dbname . ".log_prapoht where nopp='" . $noppx . "'";
				$nOrg = $owlPDO->query($iOrg) or die(print " Gagal: " . PDOException::getMessage());
				$nOrg->setFetchMode(PDO::FETCH_ASSOC);
				$dOrg = $nOrg->fetch();
				$unodph = $dOrg['pt'];
			} else {
				$unodph = $unit;
			}

			$bln = date('m');
			$thn = date('Y');
			$no = "/" . date('Y') . "/DPH/" . $unodph;
			$ql = "select nomor from " . $dbname . ".`log_perintaanhargaht` where nomor like '%" . $no . "%' order by `nomor` desc limit 0,1";

			$qr = $owlPDO->query($ql) or die(print " Gagal: " . PDOException::getMessage());
			$qr->setFetchMode(PDO::FETCH_ASSOC);
			$rp = $qr->fetch();
			$dt = explode("/", $rp['nomor']);
			$awal = $dt[0];
			$awal = intval($awal);
			$cekbln = $dt[1];
			$cekthn = $dt[2];
			if ($thn != $cekthn) {
				$awal = 1;
			} else {
				$awal += 1;
			}
			$counter = addZero($awal, 3);
			$no_permintaan = $counter . "/" . $bln . "/" . $thn . "/DPH/" . $unodph;

			##Get detail Kontral Payung
			$str = "select supplierid,harga from " . $dbname . ".log_kontrakpayung where nokontrak='" . $nokontrak . "'";
			$res = fetchdata($str);
			$supplier_id = $res[0]['supplierid'];

			$str = "select id_alamat from " . $dbname . ".log_5supalamat where supplierid='" . $supplier_id . "' limit 1";
			$res = fetchdata($str);
			$id_alamat_supplier = $res[0]['id_alamat'];

			$tgl = date('Ymd');
			$str = "insert into " . $dbname . ".log_perintaanhargaht (nomor, tanggal, purchaser, supplierid,id_alamat_supplier,nourut,keterangan,lokasikirim) values ('" . $no_permintaan . "','" . $tgl . "','" . $_SESSION['standard']['userid'] . "','" . $supplier_id . "','" . $id_alamat_supplier . "','1','','')";
			try {
				$owlPDO->exec($str);

				foreach ($_POST['kdbrg'] as $row => $Act) {
					$kdbrg = $Act;
					$nopp = $_POST['lstnopp'][$row];

					$str = "select harga from " . $dbname . ".log_kontrakpayungdt where nokontrak='" . $nokontrak . "' and kodebarang='" . $kdbrg . "'";
					$res = fetchdata($str);
					$harga = $res[0]['harga'];

					$str = "select hargasatuan,jumlah from " . $dbname . ".log_prapodt where nopp='" . $nopp . "' and kodebarang='" . $kdbrg . "'";
					$res = fetchdata($str);
					$hargaterakhir = $res[0]['hargasatuan'];
					$jmlh = $res[0]['jumlah'];

					$str = "insert into " . $dbname . ".log_permintaanhargadt (nomor,nourut,kodebarang,hargaterakhir,harga,jumlah,nopp) values('" . $no_permintaan . "','1','" . $kdbrg . "','" . $hargaterakhir . "','" . $harga . "','" . $jmlh . "','" . $nopp . "')";

					try {
						$owlPDO->exec($str);

						$str = "update " . $dbname . ".log_listverifikasi set status='1' where nopp='" . $nopp . "' and kodebarang='" . $kdbrg . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
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
				}
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}

			echo $no_permintaan;
			break;

		case 'addfileupload':
			$tab .= "<table border=0 cellspacing=1 cellpadding=3 class=sortable>
			<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['view'] . "</td>
				<td align=center>" . $_SESSION['lang']['kriteria'] . "</td>
				<td align=center>" . $_SESSION['lang']['namafile'] . "</td>
				<td align=center>" . $_SESSION['lang']['action'] . "</td>
			</tr>
			</thead>
			<tbody>";

			$arrimage = array();
			$no = 0;
			$str = "select * from " . $dbname . ".log_5photobarang where kodebarang='" . $kodebarang . "'";
			$res = fetchdata($str);
			if (count($res) > 0) {
				if ($res[0]['depan'] != '') {
					$no++;
					$arrimage[$no]['kriteria'] = "Tampak Depan";
					$arrimage[$no]['namafile'] = str_replace('photobarang/', '', $res[0]['depan']);
					$arrimage[$no]['href'] = $res[0]['depan'];
				}
				if ($res[0]['samping'] != '') {
					$no++;
					$arrimage[$no]['kriteria'] = "Tampak Samping";
					$arrimage[$no]['namafile'] = str_replace('photobarang/', '', $res[0]['samping']);
					$arrimage[$no]['href'] = $res[0]['samping'];
				}
				if ($res[0]['atas'] != '') {
					$no++;
					$arrimage[$no]['kriteria'] = "Tampak Atas";
					$arrimage[$no]['namafile'] = str_replace('photobarang/', '', $res[0]['atas']);
					$arrimage[$no]['href'] = $res[0]['atas'];
				}
			}


			$str = "select * from " . $dbname . ".listfileupload where notransaksi = '" . $nopp . "' and status='1'";
			$res = fetchdata($str);
			foreach ($res as $val) {
				if ($val['kriteriaefil'] == 'others') {
					$kriteriaefil = 'Others';
				} else {
					$optkritefil = makeOption($dbname, 'fil_5mapcriteria', 'id,kriteria', "id='" . $val['kriteriaefil'] . "'");
					$kriteriaefil = $optkritefil[$val['kriteriaefil']];
				}
				$no++;
				$arrimage[$no]['kriteria'] = $kriteriaefil;
				$arrimage[$no]['namafile'] = $val['namafile'];
				$arrimage[$no]['href'] = "fileupload/pp/" . $val['namafile'];
			}

			$no = 0;
			foreach ($arrimage as $val) {
				$no++;
				$tab .= "<tr class='rowcontent'>
				<td style='text-align:right'>" . $no . "</td>
				<td style='text-align:center'>
					<a href='" . $val['href'] . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>
				</td>
				<td style='text-align:left'>" . $val['kriteria'] . "</td>
				<td style='text-align:left'>" . $val['namafile'] . "</td>
				<td style='text-align:center'>
					<input type='checkbox' id='chkimage' name='chkimage[]' value='" . $val['href'] . "' />
				</td>
			</tr>";
			}

			$tab .= "<tr>
			<td colspan=5 style='text-align:right'>
				<button class=mybutton onclick=\"insertimage('" . $kodebarang . "','" . $nopp . "')\">" . $_SESSION['lang']['save'] . "</button>
			</td>
		</tr>
		</tbody>
		</table>";

			echo $tab;
			break;

		case 'viewdetailupload':
			$tab .= "<table border=0 cellspacing=1 cellpadding=3 class=sortable>
			<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
				<td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
				<td align=center>" . $_SESSION['lang']['nopp'] . "</td>
				<td align=center>" . $_SESSION['lang']['namafile'] . "</td>
				<td align=center>" . $_SESSION['lang']['view'] . "</td>
			</tr>
			</thead>
			<tbody>";

			$no = 0;
			$str = "select * from " . $dbname . ".log_perintaanhargafile where nomor='" . $notransaksi . "' and supplierid='" . $supplierid . "' and kodebarang='" . $kodebarang . "' and nopp='" . $nopp . "'";
			$res = fetchdata($str);
			foreach ($res as $val) {
				$no++;
				$arrNmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $val['kodebarang'] . "'");
				$namafile = str_replace('photobarang/', '', $val['namafile']);
				$namafile = str_replace('fileupload/pp/', '', $namafile);
				$tab .= "<tr class='rowcontent'>
				<td style='text-align:right'>" . $no . "</td>
				<td style='text-align:center'>" . $val['kodebarang'] . "</td>
				<td style='text-align:left'>" . $arrNmBrg[$val['kodebarang']] . "</td>
				<td style='text-align:center'>" . $val['nopp'] . "</td>
				<td style='text-align:left'>" . $namafile . "</td>
				<td style='text-align:center'>
					<a href='" . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>
				</td>
			</tr>";
			}

			$tab .= "</tbody>
		</table>";

			echo $tab;
			break;

		case 'insertimage':
			$_SESSION['bgimage'][$kodebarang][$nopp] = array();
			$expmyimage = explode('|', $myimage);

			if (count($expmyimage) > 0) {
				$no = 0;
				foreach ($expmyimage as $val) {
					$newdata = array(
						'namafile' => $val
					);

					if ($_SESSION['bgimage'][$kodebarang][$nopp] != array()) {
						foreach ($_SESSION['bgimage'][$kodebarang][$nopp] as $key => $row) {
							if ($row['namafile'] == $val) {
								exit("Warning : Item ini sudah pernah diinput sebelumnya.");
							}
						}
						array_push($_SESSION['bgimage'][$kodebarang][$nopp], $newdata);
					} else {
						array_push($_SESSION['bgimage'][$kodebarang][$nopp], $newdata);
					}


					// $no++;
					// $namafile = str_replace('photobarang/','',$val);
					// $namafile = str_replace('fileupload/pp/','',$namafile);
					// $tab.="<tr>
					// <td>".$no.".</td>
					// <td style='color:blue;cursor:pointer'><a href='".$val."' download title='".$namafile."'>".$namafile."</a></td>
					// <td>
					// <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefileupload('".$kodebarang."','".$nopp."','".$val."');\" >
					// </td>
					// </tr>";
				}
			}
			break;

		case 'loadfilesuploaddt':
			$tab = "";
			$no = 0;
			if (count($_SESSION['bgimage'][$kodebarang][$nopp]) > 0) {
				$tab .= "<table cellpadding=3>";
				foreach ($_SESSION['bgimage'][$kodebarang][$nopp] as $key => $val) {
					$no++;

					$namafile = str_replace('photobarang/', '', $val['namafile']);
					$namafile = str_replace('fileupload/pp/', '', $namafile);

					$tab .= "<tr class='rowcontent'>
					<td style='text-align:right'>" . $no . "</td>
					<td><a href='" . $val['namafile'] . "' download>" . $namafile . "</a></td>
					<td>
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefileupload('" . $kodebarang . "','" . $nopp . "','" . $val['namafile'] . "');\" >
					</td>
				</tr>";
				}
				$tab .= "</table>";
			}

			echo $tab;
			break;

		case 'deletefileupload':
			foreach ($_SESSION['bgimage'][$kodebarang][$nopp] as $key => $val) {
				if ($val['namafile'] == $namafile) {
					unset($_SESSION['bgimage'][$kodebarang][$nopp][$key]);
				}
			}
			break;

		case 'skiprph':
			for ($i = 0; $i < $countbaris; $i++) {
				$str = "update " . $dbname . ".log_listverifikasi set skip='1' where nopp='" . $_POST['lstnopp'][$i] . "' and kodebarang='" . $_POST['kdbrg'][$i] . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
			break;

		case 'addData':
			if ($id_alamat_supplier == '') {
				exit("Gagal : Alamat Supplier harus dipilih.");
			}

			if ($keterangan == '') {
				exit("Gagal : Keterangan harus diisi.");
			}

			if ($lokasipengiriman == '') {
				exit("Gagal : Lokasi pengiriman harus diisi.");
			}

			#ambil kodept jika holding, unit berdasarkan pp jika selain holding
			$noppx = $_POST['lstnopp'][0];
			$nopplist =  explode('/', $noppx);
			$unit = $nopplist[4];
			if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
				$iOrg = "select pt from " . $dbname . ".log_prapoht where nopp='" . $noppx . "'";
				$nOrg = $owlPDO->query($iOrg) or die(print " Gagal: " . PDOException::getMessage());
				$nOrg->setFetchMode(PDO::FETCH_ASSOC);
				$dOrg = $nOrg->fetch();
				// $unodph=$dOrg['pt'];
				$unodph = $_SESSION['empl']['lokasitugas'];
			} else {
				$unodph = $unit;
			}

			$tgl = date('Ymd');
			if ($_POST['notransaksi'] == '') {
				$bln = date('m');
				$thn = date('Y');
				$no = "/" . date('Y') . "/DPH/" . $unodph;
				$ql = "select `nomor` from " . $dbname . ".`log_perintaanhargaht` where nomor like '%" . $no . "%' order by `nomor` desc limit 0,1";

				$qr = $owlPDO->query($ql) or die(print " Gagal: " . PDOException::getMessage());
				$qr->setFetchMode(PDO::FETCH_ASSOC);
				$rp = $qr->fetch();
				$dt = explode("/", $rp['nomor']);
				$awal = $dt[0];
				$awal = intval($awal);
				$cekbln = $dt[1];
				$cekthn = $dt[2];
				//exit("warning".$cekthn."___".$awal."___".$rp['nomor']);
				//if(($bln!=$cekbln)&&($thn!=$cekthn))
				if ($thn != $cekthn) {
					$awal = 1;
				} else {
					$awal += 1;
				}
				$counter = addZero($awal, 3);
				$no_permintaan = $counter . "/" . $bln . "/" . $thn . "/DPH/" . $unodph;
				$nourut = 1;
			} else {
				$no_permintaan = $_POST['notransaksi'];
				$scek = "select distinct * from " . $dbname . ".log_perintaanhargaht 
                    where nomor='" . $no_permintaan . "' and supplierid='" . $supplier_id . "'";
				$qcek = $owlPDO->query($scek) or die(print " Gagal: " . PDOException::getMessage());
				$rcek = owlBaris($qcek);
				if ($rcek != 0) {
					exit("error: Data tersebut sudah ada");
				}
				$strx = "select max(nourut) as nourut from " . $dbname . ".log_perintaanhargaht where nomor='" . $no_permintaan . "'";
				$resx = fetchdata($strx);
				$nourut = $resx[0]['nourut'] + 1;
			}

			$wktskrg = date("Y-m-d H:i:s");

			$ins = "insert into " . $dbname . ".log_perintaanhargaht 
                (nomor, tanggal, purchaser, supplierid,id_alamat_supplier,nourut,keterangan,lokasikirim) values 
                ('" . $no_permintaan . "','" . $tgl . "','" . $_SESSION['standard']['userid'] . "','" . $supplier_id . "','" . $id_alamat_supplier . "','" . $nourut . "','" . $keterangan . "','" . $lokasipengiriman . "')";
			try {
				$owlPDO->exec($ins);

				foreach ($_SESSION['bgimage'] as $key => $val) {
					foreach ($_SESSION['bgimage'][$key] as $key2 => $val2) {
						foreach ($_SESSION['bgimage'][$key][$key2] as $key3 => $val3) {
							// echo $key."####".$key2."####".$val3['namafile']."<br>";
							$str = "insert into " . $dbname . ".log_perintaanhargafile (nomor,supplierid,kodebarang,nopp,namafile,status,createdby,createdtime) values ('" . $no_permintaan . "','" . $supplier_id . "','" . $key . "','" . $key2 . "','" . $val3['namafile'] . "','0','" . $_SESSION['standard']['userid'] . "','" . $wktskrg . "')";
							try {
								$owlPDO->exec($str);
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "\n";
								die();
							}
						}
					}
				}

				foreach ($_POST['kdbrg'] as $row => $Act) {
					$kdbrg = $Act;
					$jmlh = str_replace(",", "", $_POST['jmlh'][$row]);
					$nopp = $_POST['lstnopp'][$row];

					$sqp = "insert into " . $dbname . ".log_permintaanhargadt (`nomor`,`kodebarang`,`jumlah`,nopp,nourut) 
                          values('" . $no_permintaan . "','" . $kdbrg . "','" . $jmlh . "','" . $nopp . "','" . $nourut . "')";
					try {
						$owlPDO->exec($sqp);

						$str = "update " . $dbname . ".log_listverifikasi set status='1' where nopp='" . $nopp . "' and kodebarang='" . $kdbrg . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
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
				}
				$nourut += 1;
				echo $no_permintaan . "###" . $nourut;
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}

			$_SESSION['bgimage'] = array();
			break;

		case 'preview2':
			$formPil = 1;
			$optTermPay = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			$optStock = $optTermPay;
			$optKrm = $optTermPay;
			$arrOptTerm = array("1" => "Cash", "2" => "Credit 2 weeks", "3" => "Credit 1 month", "4" => "Spesific Terms", "5" => "Down Payment");
			$arrStock = array("1" => "Ready Stock", "2" => "Not Ready");

			$str = "select count(*) as jumlah from " . $dbname . ".log_perintaanhargaht where nomor='" . $notransaksi . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			if ($bar['jumlah'] <= 0) {
				exit("Gagal : Jumlah penawaran harga minimal 1 supplier.");
			}

			$expnotransaksi = explode('/', $notransaksi);

			$hrgTerAkhir = array();
			$tempBarang = "";
			$sdata = "select distinct kodebarang,hargasatuan from " . $dbname . ".log_po_vw where kodebarang in (select kodebarang from " . $dbname . ".log_permintaanhargadt where nomor='" . $notransaksi . "') order by tanggal desc,kodebarang asc";
			$rdata = fetchData($sdata);
			foreach ($rdata as $key => $val) {
				if ($val['hargasatuan'] != 0) {
					if ($tempBarang != $val['kodebarang']) {
						$tempBarang = $val['kodebarang'];
						$hrgTerAkhir[$val['kodebarang']] = $val['hargasatuan'];
					}
				}
			}

			$str = "select distinct * from " . $dbname . ".log_perintaanhargaht where nomor='" . $notransaksi . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$dtNomor[] = $bar['nourut'];
				$dtSupp[$bar['nourut']] = $bar['supplierid'];
				$dtFranco[$bar['nourut']] = $bar['id_franco'];
				$dtStock[$bar['nourut']] = $bar['stock'];
				$dtCattn[$bar['nourut']] = $bar['catatan'];
				$dtSisbyr[$bar['nourut']] = $bar['sisbayar'];
				$dtSisbyr2[$bar['nourut']] = $bar['sisbayar2'];
				$dtPpn[$bar['nourut']] = $bar['ppn'];
				$dtPph[$bar['nourut']] = $bar['pph'];
				$dtPph22[$bar['nourut']] = $bar['pph22'];
				//pphfinal
				$dtPphFinal[$bar['nourut']] = $bar['pphfinal'];
				
				$dtPbbkb[$bar['nourut']] = $bar['pbbkb'];
				$dtSbtotal[$bar['nourut']] = $bar['subtotal'];
				$dtDisknPrsn[$bar['nourut']] = $bar['diskonpersen'];
				$dtNildis[$bar['nourut']] = $bar['nilaidiskon'];
				$dtNilPer[$bar['nourut']] = $bar['nilaipermintaan'];
				$dtMtuang[$bar['nourut']] = $bar['matauang'];
				$dtTglDr[$bar['nourut']] = $bar['tgldari'];
				$dtTglSmp[$bar['nourut']] = $bar['tglsmp'];
				$kurs[$bar['nourut']] = $bar['kurs'];
				$dtCttn[$bar['nourut']] = $bar['catatan'];
				$dtdpgrm[$bar['nourut']] = $bar['durasipengiriman'];
				$dtdpkrj[$bar['nourut']] = $bar['durasipekerjaan'];
				$dtgrnprd[$bar['nourut']] = $bar['garansiproduk'];
				$dtposstk[$bar['nourut']] = $bar['posisistok'];
				$dtasrn[$bar['nourut']] = $bar['asuransi'];
				$tglbatasrfq[$bar['nourut']] = $bar['tglbatasrfq'];
				$tgltempopembayaran[$bar['nourut']] = $bar['tgltempopembayaran'];
				$deliverytime[$bar['nourut']] = $bar['deliverytime'];
				$tglrph = $bar['tanggal'];
				$ppnjasamaterial = $bar['ppnjasamaterial'];
				$penambahpph22 = $bar['penambahpph22'];
				$dtnilai1s[$bar['nourut']] = $bar['nilai1s'];
				$dtnilai2s[$bar['nourut']] = $bar['nilai2s'];
				$dtnilai3s[$bar['nourut']] = $bar['nilai3s'];
				$dtnilai4s[$bar['nourut']] = $bar['nilai4s'];
				$dtnilai5s[$bar['nourut']] = $bar['nilai5s'];

				$dtnilai1f = $bar['nilai1f'];
				$dtnilai2f = $bar['nilai2f'];
				$dtnilai3f = $bar['nilai3f'];
				$dtnilai4f = $bar['nilai4f'];
				$dtnilai5f = $bar['nilai5f'];
			}

			$str = "select distinct kodebarang,jumlah,nomor,harga,ongkir,merk,spec,nourut,hargaterakhir,hargaestimasi,nopp,score,factor from " . $dbname . ".log_permintaanhargadt where nomor='" . $notransaksi . "' ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$str2 = "select satuankonversi from " . $dbname . ".log_prapodt where nopp='" . $bar['nopp'] . "' and kodebarang='" . $bar['kodebarang'] . "'";
				$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_ASSOC);
				$bar2 = $res2->fetch();
				$arrKonversi[$bar['kodebarang']] = $bar2['satuankonversi'];

				if ($bar['harga'] == '') {
					$bar['harga'] = 0;
				}

				$dtSub[$bar['nourut']][$bar['kodebarang']] = floatval($bar['jumlah']) * floatval($bar['harga']);
				$dtScore[$bar['nourut']][$bar['kodebarang']] = $bar['score'];
				$dtFactor[$bar['nourut']][$bar['kodebarang']] = $bar['factor'];
				$dtHarga[$bar['nourut']][$bar['kodebarang']] = $bar['harga'];
				$dtMerk[$bar['nourut']][$bar['kodebarang']] = $bar['spec'];
				$dtMrk[$bar['nourut']][$bar['kodebarang']] = $bar['merk'];
				$dtJumlah[$bar['nourut']][$bar['kodebarang']] = $bar['jumlah'];
				$arrJmlh[$bar['kodebarang']] = $bar['jumlah'];
				$dtongkir[$bar['nourut']] = $bar['ongkir'];
				$dtjlhitem[$bar['nourut']] += $bar['jumlah'];
				$listBarang[$bar['kodebarang']] = $bar['kodebarang'];
				if ($bar['hargaterakhir'] == 0) {
					$bar['hargaterakhir'] = $hrgTerAkhir[$bar['kodebarang']];
				}
				$hargaestimasi = $bar['hargaestimasi'];
				$dthargaestimasi[$bar['kodebarang']] = $hargaestimasi;

				##Harga Terakhir
				$mypt = "";
				$myunit = "";
				$opthgstn = makeOption($dbname, 'log_5masterbarang', 'kodebarang,hargasatuan', "kodebarang='" . $bar['kodebarang'] . "'");
				if ($opthgstn[$bar['kodebarang']] != '') {
					$exphargasatuan = explode(',', $opthgstn[$bar['kodebarang']]);
					if (in_array($expnotransaksi[4], $exphargasatuan)) {
						$mypt = $optpt[$expnotransaksi[4]];
						$myunit = $expnotransaksi[4];
					}
				}
				$strx = "select hargasatuan,hargaestimasi from " . $dbname . ".log_5hargaterakhir where status='1' and kodebarang='" . $bar['kodebarang'] . "' and unit='" . $myunit . "' order by tanggal desc limit 1";
				$resx = fetchdata($strx);
				$dthargaterakhir[$bar['kodebarang']] = $resx[0]['hargasatuan'];
				if ($hargaestimasi == '0') {
					$dthargaestimasi[$bar['kodebarang']] = $resx[0]['hargaestimasi'];
				}
			}

			$tab = '<input id="fileattach" type="file" delimiter=";," style="display:none;">';
			$tab .= "<table cellspacing=1 border=0 class=sortable >
			<thead class=rowheader>
            <tr>
				<th rowspan=2 align=center></th>
                <th rowspan=2 align=center width=50px>" . $_SESSION['lang']['kodebarang'] . "</th>
                <th rowspan=2 colspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</th>
                <th rowspan=2 align=center width='30px'>" . $_SESSION['lang']['satuan'] . "</th>
                <th rowspan=2 align=center width='30px'>Harga Terakhir</th>
                <th rowspan=2 align=center width='30px'>Harga Estimasi</th>";

			foreach ($dtNomor as $brs) {
				$optSupplier = "";
				$str = "select supplierid,namasupplier from " . $dbname . ".log_5supplier";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$optSupplier .= "<option value='" . $bar['supplierid'] . "' " . ($bar['supplierid'] == $dtSupp[$brs] ? "selected" : "") . ">" . $bar['namasupplier'] . "</option>";
				}

				$tab .= "<th colspan=5 align=center>
					<select style=width:300px disabled id=supplierId_" . $brs . ">" . $optSupplier . "</select>
					
					<button class=\"mybutton\" onclick=\"uploadCSV('fileattach',this)\" supplier-num=\"" . $brs . "\" style='float:right;'>CSV</button>
				</th>
				<th  class='bintang' align=center width=30px rowspan=2>Score</th>";
			}

			$tab .= "</tr><tr>";

			foreach ($dtNomor as $brs) {
				$tab .= "<th align=center width=55px>" . $_SESSION['lang']['spesifikasi'] . "</th>
				<th class='bintang' align=center width=40px>" . $_SESSION['lang']['jumlah'] . "</th>
				<th  class='bintang' align=center width=40px>" . $_SESSION['lang']['harga'] . "</th>
				<th  align=center width=40px>Varian " . $_SESSION['lang']['harga'] . "</th>
				<th align=center width=40px>" . $_SESSION['lang']['subtotal'] . "</th>";
			}

			$tab .= "<tr>
			</thead>
			<tbody>";

			$totRow = count($dtNomor);
			$totBrg = @count($listBarang);
			$tab .= "<input type='hidden' id='jlhtender' value='" . $totRow . "'>";

			if ($totBrg == 0) {
				exit('warning:Detail Data Kosong');
			}

			$no = 0;
			$countso = 0;
			$countongkir = 0;
			foreach ($listBarang as $brsKdBrg) {
				if (substr($brsKdBrg, 0, 1) == '8') {
					$countso++;
				}
				$no += 1;
				$hargasbldiskon = 0;
				$arrNmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $brsKdBrg . "'");
				$optSat = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan', "kodebarang='" . $brsKdBrg . "'");
				if ($arrKonversi[$brsKdBrg] == '' || is_null($arrKonversi[$brsKdBrg])) {
					$mySatuan = $optSat[$brsKdBrg];
				} else {
					$mySatuan = $arrKonversi[$brsKdBrg];
				}

				$str = "select a.hargasbldiskon from " . $dbname . ".log_podt a 
				left join " . $dbname . ".log_poht b on a.nopo=b.nopo
				where a.kodebarang='" . $brsKdBrg . "' order by b.tanggal desc limit 1";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$hargasbldiskon = $bar['hargasbldiskon'];
				}

				$tab .= "<tr class='rowcontent' id=\"row_" . $brsKdBrg . "\" row=\"" . $no . "\">
					<td align=center>
						<img src='images/application/application_delete.png' class='resicon' title='Delete' onclick=\"delrfq('" . $brsKdBrg . "','" . $notransaksi . "');\">
					</td>
					<td id='kd_brg_" . $no . "'>" . $brsKdBrg . "</td>
					<td colspan=2 title='" . $arrNmBrg[$brsKdBrg] . "'>" . $arrNmBrg[$brsKdBrg] . "</td>
					<td align=center>" . $mySatuan . "</td>";
				if ($formPil != '1') {
					$tab .= "<input type=hidden id='hargaterakhirv2_" . $no . "' value='" . ($dthargaterakhir[$brsKdBrg] == 0 ? 0 : hidezerodecimal($dthargaterakhir[$brsKdBrg], 2)) . "'>";
					$tab .= "<td align=right><label>" . ($hargasbldiskon == 0 ? 0 : number_format($hargasbldiskon, 2)) . "</label></td>";
					$tab .= "<td align=right><label>" . ($hargasbldiskon == 0 ? 0 : number_format($hargasbldiskon, 2)) . "</label></td>";
				} else {
					$tab .= "<input type=hidden id='hargaterakhirv2_" . $no . "' value='" . ($dthargaterakhir[$brsKdBrg] == 0 ? 0 : hidezerodecimal($dthargaterakhir[$brsKdBrg], 2)) . "'>";
					$tab .= "<td align=right><label style='cursor:pointer;color:blue' onclick=\"showdetail('" . $mypt . "','" . $myunit . "','" . $brsKdBrg . "')\" id='hargaterakhir_" . $no . "'>" . ($dthargaterakhir[$brsKdBrg] == 0 ? 0 : hidezerodecimal($dthargaterakhir[$brsKdBrg], 2)) . "</label></td>";
					$tab .= "<td align=right>
						<input type=text id=hargaestimasi_" . $no . " value='" . hidezerodecimal($dthargaestimasi[$brsKdBrg], 2) . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('hargaestimasi_" . $no . "',2)\" style='width:75px' /></td>";
				}

				$ard = 0;
				foreach ($dtNomor as $brs) {
					$ard += 1;
					if ($formPil != '1') {
						$tab .= "<td align=left>" . $dtMrk[$brs][$brsKdBrg] . "</td>
						<td align=left>" . $dtMerk[$brs][$brsKdBrg] . "</td>
						<td align=left>" . number_format($dtJumlah[$brs][$brsKdBrg], 2) . "</td>
						<td align=right>" . number_format($dtHarga[$brs][$brsKdBrg], 2) . "</td>
						<td align=right>" . number_format($dtSub[$brs][$brsKdBrg], 2) . "</td>";
					} else {
						$optMrk = "<option value=''></option>";
						$str = "select b.idmerk,b.merk from " . $dbname . ".log_5merkbarangdt a 
							left join " . $dbname . ".log_5merkbaranght b on a.idmerk = b.idmerk 
							where a.kodebarang='" . $brsKdBrg . "' 
							order by b.merk asc";
						$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						while ($bar = $res->fetch()) {
							if ($dtMrk[$brs][$brsKdBrg] != '') {
								$optMrk .= "<option value=" . $bar['idmerk'] . " " . ($dtMrk[$brs][$brsKdBrg] == $bar['idmerk'] ? "selected" : " ") . ">" . $bar['merk'] . "</option>";
							} else {
								$optMrk .= "<option value=" . $bar['idmerk'] . ">" . $bar['merk'] . "</option>";
							}
						}

						$tab .= "<td align=justify>
							<textarea placeholder='Maximal character 255' maxlength=255 id=merk_" . $no . "_" . $brs . "  class='myinputtext' onkeypress='return tanpa_kutip(event)' rows=3>" . $dtMerk[$brs][$brsKdBrg] . "</textarea>
						</td>
						<td align=right>
							<input type=text id=qty_" . $no . "_" . $brs . " value='" . hidezerodecimal($dtJumlah[$brs][$brsKdBrg], 2) . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' onkeyup=\"calculate(" . $no . "," . $brs . "," . $totBrg . ");z.numberFormat('qty_" . $no . "_" . $brs . "',2)\" style='width:75px' />
						</td>
						<td align=right>
							<input type=text id=price_" . $no . "_" . $brs . " value='" . hidezerodecimal($dtHarga[$brs][$brsKdBrg], 5) . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' onkeyup=\"calculate(" . $no . "," . $brs . "," . $totBrg . ");z.numberFormat('price_" . $no . "_" . $brs . "',5)\" style='width:75px' />
						</td>
						<td align=right>
							<input type=text disabled id=varianharga_" . $no . "_" . $brs . " value='" . hidezerodecimal($dtHarga[$brs][$brsKdBrg] - $dthargaterakhir[$brsKdBrg], 2) . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' onkeyup=\"calculate_varianharga(" . $no . "," . $brs . "," . $totBrg . ");z.numberFormat('varianharga_" . $no . "_" . $brs . "',2)\" style='width:75px' />
						</td>
						<td align=right>
							<input type=text id=total_" . $no . "_" . $brs . " disabled value='" . hidezerodecimal($dtSub[$brs][$brsKdBrg], 2) . "'  class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:75px'  />
						</td>
						<td align=center>
							
						</td>";
					}
				}
				$tab .= "</tr>";

				$strx = "select ongkir from " . $dbname . ".log_5masterbarang where kodebarang='" . $brsKdBrg . "'";
				$resx = fetchdata($strx);
				if ($resx[0]['ongkir'] > 0) {
					$countongkir++;
				}
			}

			## BEGIN MATERIAL JASA ##
			// x11
			$_SESSION['somaterial'] = array();
			if ($countso > 0) {
				$tab .= "<input id='tipepo' class='myinputtext' type='hidden' disabled value='SO'>";
			} else {
				$tab .= "<input id='tipepo' class='myinputtext' type='hidden' disabled value='PO'>";
			}
			if ($countso > 0) {
				$tab .= "<tbody id='trmatjasa'>
				</tbody>
				<tr class='rowcontent'>
				<td></td>
				<td align=center>
				<img src='images/plus.png' class='resicon' title='Tambah Material' onclick=\"addmaterialso()\">
				</td>
				<td colspan=2>
				<input id='nm_brg_so' class='myinputtext' type='text' onkeypress=\"return tanpa_kutip(event)\" style='width:285px' placeholder='Nama Barang'>
				</td>
				<td></td>
				<td></td>
				<td></td>
				";
				foreach ($dtNomor as $brs) {
					$tab .= "<td></td>
					<td>
					<input type=text id=jmlhDimintaso_" . $brs . " class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:75px' />
					</td>
					<td>
					<input type=text id=harga_satuan_so_" . $brs . " class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:75px' />
					</td>
					<td></td>
					<td></td>
					<td></td>
					";
				}
				$tab .= "</tr>";
				// list data material SO
				$tab .= "	<tbody id=listmaterialso></tbody>";
			}


			## END MATERIAL JASA ##

			####ONGKIR####
			$tab .= "<tr class='rowcontent'>
				<!--<td rowspan=7 colspan=3 valign=top align=left>&nbsp</td>-->
				
				<td rowspan=9 colspan=3 valign=top align=left>&nbsp
					<!--Tanggal Berlaku Surat <br> Perbandingan Harga : 
					<input type=text class=myinputtext style='width:60px' id=tgl_batas_pnwharga onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='" . tanggalnormal($tgl_batas_pnwharga) . "' readonly />-->
				</td>
				
				<td colspan=4>" . $_SESSION['lang']['ongkoskirim'] . "</td>";

			$disongkir = "disabled";
			if ($countongkir > 0) {
				$disongkir = "";
			}
			foreach ($dtNomor as $brs) {
				$totongkir = 0;
				$totongkir = ($dtjlhitem[$brs] * $dtongkir[$brs]);
				$tab .= "<td align=right colspan=3>
				<input type=text id=ongkir_" . $brs . " value='" . hidezerodecimal($dtongkir[$brs], 2) . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' onkeyup=\"grnd_total(" . $brs . "," . $totBrg . ");z.numberFormat('ongkir_" . $brs . "',2)\" style='width:75px' " . $disongkir . " />
				</td>";
				$tab .= "<td></td>";
				$tab .= "<td>
					<input type=text id=totalongkir_" . $brs . " disabled value='" . hidezerodecimal($totongkir, 2) . "'  class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:75px'  />
				</td>
				<td></td>";
			}
			$tab .= "</tr>";

			####SUBTOTAL####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4>" . $_SESSION['lang']['subtotal'] . "</td>";

			foreach ($dtNomor as $brs) {
				$tab .= "<td align=right colspan=5 id=total_harga_po_" . $brs . " style='font-weight:bold'>" . number_format($dtSbtotal[$brs], 0) . "</td>
				<td>
					<input type=number id=score_" . $brs . " value='" . ($dtnilai1s[$brs] == '' ? '0' : $dtnilai1s[$brs]) . "'  class='myinputtextnumber' onkeypress=\"return isNumber(event);\" onkeyup=\"scorevalidate('score_" . $brs . "')\" style='width:40px;text-align:center' min='0' max='5' />
				</td>";
			}

			$tab .= "</tr>";

			####DISKON####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4>" . $_SESSION['lang']['diskon'] . "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=right colspan=4>" . number_format($dtDisknPrsn[$brs], 0) . "%</td>
					<td align=right>" . number_format($dtNildis[$brs], 0) . "</td>
					<td></td>";
				} else {
					// onkeyup=\"calculate_all_j(".$brs.")
					$tab .= "<td align=right colspan=3>
						<input type=text  id=diskon_" . $brs . " name=diskon_" . $brs . " class=myinputtextnumber onkeyup=\"calculate_all_j(" . $brs . ");z.numberFormat('diskon_" . $brs . "',2)\" maxlength=4 onkeypress=return angka_doang(event) onblur=\"getZero(" . $brs . ")\" value='" . hidezerodecimal($dtDisknPrsn[$brs], 2) . "' style='width:75px'  />
					</td>
					<td></td>
					<td align=right>
						<input type=text  id=angDiskon_" . $brs . " name=angDiskon_" . $brs . " class=myinputtextnumber disabled  onkeyup=\"calculate_all_j(" . $brs . ");z.numberFormat('angDiskon_" . $brs . "',2)\" onkeypress=return angka_doang(event) onblur=\"getZero(" . $brs . ")\" value='" . hidezerodecimal($dtNildis[$brs], 2) . "' style='width:75px' />
						<!--<input type=text style='display:none'  id=ppN_" . $brs . " name=ppN_" . $brs . " class=myinputtextnumber  onkeyup=calculate_all(" . $brs . ")  maxlength=2  onkeypress=return angka_doang(event) onblur=\"validasippn(" . $brs . ")\"  value='" . hidezerodecimal($dtPpn[$brs], 2) . "' style='width:75px' />
						<input type=text style='display:none' id=ppn_" . $brs . " name=ppn_" . $brs . " class=myinputtextnumber  disabled value='" . hidezerodecimal($persen[$brs], 2) . "' style='width:75px' />-->
					</td>
					<td></td>";
				}
			}
			$tab .= "</tr>";

			// ####PPN####
			$tab .= "<tr class='rowcontent'><td colspan=4>" . $_SESSION['lang']['ppn'] . " ";

			if ($ppnjasamaterial == '0') {
				$c_ppnjasamaterial = '';
			} else {
				$c_ppnjasamaterial = 'checked';
			}
			if ($penambahpph22 == '0') {
				$c_penambahpph22 = '';
			} else {
				$c_penambahpph22 = 'checked';
			}


			if ($countso > 0) {
				$tab .= " <span style='color:red'>(&#10004;) => Material + Jasa </span> <input type=checkbox id=masterial_jasa " . $c_ppnjasamaterial . "  onchange=calculate_all_j('kosong') />";
			}

			$tab .= "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=right colspan=4>" . number_format($dtPPN[$brs], 2) . "</td>";
				} else {
					@$persen[$brs] = (($dtSbtotal[$brs] - $dtNildis[$brs]) * ($dtPpn[$brs] / 100));
					$tab .= "<td align=right colspan=3>
							<!-- <input type=text  id=ppN_" . $brs . " name=ppN_" . $brs . " class=myinputtextnumber  onkeyup=calculate_all_j(" . $brs . ")  maxlength=2  onkeypress=return angka_doang(event) onblur=\"validasippn(" . $brs . ")\"  value='" . hidezerodecimal($dtPpn[$brs], 2) . "' style='width:75px' /> -->
							<input type=text  id=ppN_" . $brs . " name=ppN_" . $brs . " class=myinputtextnumber maxlength=4 onkeypress=return angka_doang(event) onblur=\"calculate_all_j(" . $brs . ");validasippn(" . $brs . ")\" value='" . hidezerodecimal($dtPpn[$brs], 2) . "' style='width:75px' />
						</td>";
					$tab .= "<td></td>
					<td align=right>
						<input type=text  id=ppn_" . $brs . " name=ppn_" . $brs . " class=myinputtextnumber  disabled value='" . hidezerodecimal($persen[$brs], 2) . "' style='width:75px' />
					</td>
					<td></td>
					";
				}
			}

			$tab .= "</tr>";
			// ####PPH 22####
			$tab .= "<tr class='rowcontent'><td colspan=4>" . $_SESSION['lang']['pph'] . " Ps 22";
			if ($countso <= 0) {
				$tab .= " <span style='color:red'>(&#10004;) => Penambah</span> <input type=checkbox id=penambahpph22 " . $c_penambahpph22 . " onchange=calculate_all_j('kosong_pph22') />";
			}

			$tab .= "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=right colspan=4>" . number_format($dtPph22[$brs], 2) . "</td>";
				} else {
					@$persen[$brs] = (($dtSbtotal[$brs] - $dtNildis[$brs]) * ($dtPph22[$brs] / 100));
					if ($countso > 0) {
						// <input type=text  id=ppH_".$brs." name=ppH_".$brs." class=myinputtextnumber  onkeyup=calculate_all_j(".$brs.")  maxlength=3  onkeypress=return angka_doang(event) onblur=\"validasipph(".$brs.")\"  value='".hidezerodecimal($dtPph[$brs],2)."' style='width:75px' />
						$tab .= "<td align=right colspan=3>
							<input type=text  id=ppH_22_" . $brs . " name=ppH_22_" . $brs . " class=myinputtextnumber onblur=calculate_all_j(" . $brs . ") disabled  maxlength=4 onkeypress=return angka_doang(event) value='" . hidezerodecimal($dtPph22[$brs], 2) . "' style='width:75px' />
						</td>";
					} else {
						$tab .= "<td align=right colspan=3>
							<input type=text  id=ppH_22_" . $brs . " name=ppH_22_" . $brs . " class=myinputtextnumber onblur=calculate_all_j(" . $brs . ")  maxlength=4 value='" . hidezerodecimal($dtPph22[$brs], 2) . "' style='width:75px' />
						</td>";
					}
					$tab .= "<td></td>
					<td align=right>
						<input type=text  id=pph_22_" . $brs . " name=pph_22_" . $brs . " class=myinputtextnumber  disabled value='" . hidezerodecimal($persen[$brs], 2) . "' style='width:75px' />
					</td>
					<td></td>
					";
				}
			}

			// ####PPH 23####
			$tab .= "<tr class='rowcontent'><td colspan=4>" . $_SESSION['lang']['pph'] . " Ps 23</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=right colspan=4>" . number_format($dtPPh[$brs], 2) . "</td>";
				} else {
					@$persen[$brs] = (($dtSbtotal[$brs] - $dtNildis[$brs]) * ($dtPph[$brs] / 100));
					if ($countso > 0) {
						// <input type=text  id=ppH_".$brs." name=ppH_".$brs." class=myinputtextnumber  onkeyup=calculate_all_j(".$brs.")  maxlength=3  onkeypress=return angka_doang(event) onblur=\"validasipph(".$brs.")\"  value='".hidezerodecimal($dtPph[$brs],2)."' style='width:75px' />
						$tab .= "<td align=right colspan=3>
							<input type=text  id=ppH_" . $brs . " name=ppH_" . $brs . " class=myinputtextnumber  onblur=calculate_all_j(" . $brs . ")  maxlength=4 onkeypress=return angka_doang(event) value='" . hidezerodecimal($dtPph[$brs], 2) . "' style='width:75px' />
						</td>";
					} else {
						$tab .= "<td align=right colspan=3>
							<input type=text  id=ppH_" . $brs . " name=ppH_" . $brs . " class=myinputtextnumber	 onblur=calculate_all_j(" . $brs . ") disabled  maxlength=4  value='" . hidezerodecimal($dtPph[$brs], 2) . "' style='width:75px' />
						</td>";
					}
					$tab .= "<td></td>
					<td align=right>
						<input type=text  id=pph_" . $brs . " name=pph_" . $brs . " class=myinputtextnumber  disabled value='" . hidezerodecimal($persen[$brs], 2) . "' style='width:75px' />
					</td>
					<td></td>
					";
				}
			}

			$tab .= "</tr>";


			####PPH Final####
			$tab .= "<tr class='rowcontent'><td colspan=4>PPH Final</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=right colspan=4>" . number_format($dtPphFinal[$brs], 0) . "</td>
					<td></td>";
				} else {
					$tab .= "<td align=right colspan=4></td>
					<td align=right>
						<input type=text  id=ppH_final_" . $brs . " name=ppH_final_" . $brs . " class=myinputtextnumber  onkeyup=\"calculate_all(" . $brs . ");z.numberFormat('ppH_final_" . $brs . "',2)\" onkeypress='return angka_doang(event)' onblur=\"getZero(" . $brs . ")\"  value='" . hidezerodecimal($dtPphFinal[$brs], 2) . "' style='width:75px' />
					</td>
					<td></td>";
				}
			}

			$tab .= "</tr>";

			####PBBKB####
			$tab .= "<tr class='rowcontent'><td colspan=4>PBBKB</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=right colspan=4>" . number_format($dtPbbkb[$brs], 0) . "</td>
					<td></td>";
				} else {
					$tab .= "<td align=right colspan=4></td>
					<td align=right>
						<input type=text  id=pbbkb_" . $brs . " name=pbbkb_" . $brs . " class=myinputtextnumber  onkeyup=\"calculate_all(" . $brs . ");z.numberFormat('pbbkb_" . $brs . "',2)\" onkeypress='return angka_doang(event)' onblur=\"getZero(" . $brs . ")\"  value='" . hidezerodecimal($dtPbbkb[$brs], 2) . "' style='width:75px' />
					</td>
					<td></td>";
				}
			}

			$tab .= "</tr>";

			####GRANDTOTAL####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4>" . $_SESSION['lang']['grnd_total'] . "</td>";

			foreach ($dtNomor as $brs) {
				$tab .= "<td align=right colspan=5 id=grand_total_" . $brs . " style='font-weight:bold'>" . hidezerodecimal($dtNilPer[$brs], 2) . "</td>
				<td></td>";
			}

			$tab .= "</tr>";

			####NO PERMINTAAN HARGA####
			$tab .= "<tr class='rowcontent'>
				<td rowspan=15 colspan=3 valign=top align=left>" . $_SESSION['lang']['rekomendasi'] . "</td>
				<td colspan=4>No. RPH</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td colspan=7>" . $_POST['notransaksi'] . "</td>";
				} else {
					$tab .= "<td colspan=6><input type=text disabled id=no_prmntan_" . $brs . " value='" . $_POST['notransaksi'] . "' class=myinputtext style='width:150px' /></td>";
				}
			}

			$tab .= "</tr>";

			####MATA UANG####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4>" . $_SESSION['lang']['matauang'] . "</td>";

			foreach ($dtNomor as $brs) {
				$optMt = "";

				$optMt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
				$str = "select kode,kodeiso from " . $dbname . ".setup_matauang order by kode desc";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					if ($dtMtuang[$brs] != '') {
						$optMt .= "<option value=" . $bar['kode'] . " " . ($dtMtuang[$brs] == $bar['kode'] ? "selected" : " ") . ">" . $bar['kodeiso'] . "</option>";
					} else {
						$optMt .= "<option value=" . $bar['kode'] . ">" . $bar['kodeiso'] . "</option>";
					}
				}

				if ($formPil != '1') {
					$tab .= "<td colspan=7>" . $dtMtuang[$brs] . "</td>";
				} else {
					$tab .= "<td colspan=4><select id=\"mtUang_" . $brs . "\" onchange=getkurs(" . $brs . ") name=\"mtUang_" . $brs . "\" >" . $optMt . "</select></td>
					<td style='text-align:right'>Availability</td>
					<td>
						<input type=number id=availability_" . $brs . " value='" . ($dtnilai2s[$brs] == '' ? '0' : $dtnilai2s[$brs]) . "'  class='myinputtextnumber' onkeypress=\"return isNumber(event);\" onkeyup=\"scorevalidate('availability_" . $brs . "')\" style='width:40px;text-align:center' min='0' max='5' />
					</td>";
				}
			}

			$tab .= "</tr>";

			####KURS####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4>" . $_SESSION['lang']['kurs'] . "</td>";

			$ard = 0;
			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td colspan=5>" . $kurs[$brs] . "</td>
					<td colspan=4></td>";
				} else {
					$tab .= "<td colspan=4>
						<input type=\"text\" class=\"myinputtextnumber\" id=\"Kurs_" . $brs . "\" name=\"Kurs_" . $brs . "\" style=\"width:60px;\" onkeypress=\"return angka_doang(event)\" value=" . $kurs[$brs] . "  />
					</td>
					<td rowspan=3 style='text-align:right'>Quality<br>Performance<br>Integrity</td>
					<td rowspan=3>
						<input type=number id=quality_" . $brs . " value='" . ($dtnilai3s[$brs] == '' ? '0' : $dtnilai3s[$brs]) . "'  class='myinputtextnumber' onkeypress=\"return isNumber(event);\" onkeyup=\"scorevalidate('quality_" . $brs . "')\" style='width:40px;text-align:center' min='0' max='5' />
					</td>";
				}
			}

			$tab .= "</tr>";

			####SYARAT PEMBAYARAN####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4  class='bintang'>" . $_SESSION['lang']['syaratPem'] . "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td colspan=4>" . $arrOptTerm[$dtSisbyr[$brs]] . "</td>
					<td colspan=4></td>";
				} else {
					$optTermPay = "";
					$optTermPay = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
					$str = "select kode,jenis,keterangan from " . $dbname . ".log_5syaratbayar order by keterangan asc";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()) {
						if ($dtSisbyr2[$brs] != '') {
							$optTermPay .= "<option value='" . $bar['kode'] . "' " . ($bar['kode'] == $dtSisbyr2[$brs] ? "selected" : "") . ">" . $bar['keterangan'] . " (" . $bar['jenis'] . ")</option>";
						} else {
							$optTermPay .= "<option value=" . $bar['kode'] . ">" . $bar['keterangan'] . " (" . $bar['jenis'] . ")</option>";
						}
					}

					$tab .= "<td colspan=4>
						<select id='term_pay_" . $brs . "'>" . $optTermPay . "</select>
						<select style='display:none' id=stockId_" . $brs . " style='width:150px'>" . $optStock . "</select>
					</td>";
				}
			}

			$tab .= "</tr>";

			$tab .= "</tr>";

			####LOKASI PENGIRIMAN####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4  class='bintang'>" . $_SESSION['lang']['almt_kirim'] . "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td colspan=7>" . $arrFranco[$dtFranco[$brs]] . "</td>";
				} else {
					$optKrm = "";
					$optKrm = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
					$str = "select id_franco,franco_name from " . $dbname . ".setup_franco where status=0 order by franco_name asc";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()) {
						if ($dtFranco[$brs] != '0') {
							$optKrm .= "<option value=" . $bar['id_franco'] . " " . ($bar['id_franco'] == $dtFranco[$brs] ? "selected" : "") . ">" . $bar['franco_name'] . "</option>";
						} else {
							$optKrm .= "<option value=" . $bar['id_franco'] . ">" . $bar['franco_name'] . "</option>";
						}
					}

					$tab .= "<td colspan=4>
						<select id=tmpt_krm_" . $brs . ">" . $optKrm . "</select></td>";
				}
			}

			$tab .= "</tr>";


			####DURASI PENGIRIMAN####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4 valign=top>" . $_SESSION['lang']['durasipengiriman'] . "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=justify colspan=7>" . (isset($dtdpgrm[$brs]) ? $dtdpgrm[$brs] : '') . "</td>";
				} else {
					$tab .= "<td align=justify colspan=4>
						<input type=text id='durasipengiriman_" . $brs . "' size=40 class=myinputtext value='" . $dtdpgrm[$brs] . "'>
					</td>
					<td style='text-align:right'>Service</td>
					<td>
						<input type=number id=service_" . $brs . " value='" . ($dtnilai4s[$brs] == '' ? '0' : $dtnilai4s[$brs]) . "'  class='myinputtextnumber' onkeypress=\"return isNumber(event);\" onkeyup=\"scorevalidate('service_" . $brs . "')\" style='width:40px;text-align:center' min='0' max='5' />
					</td>";
				}
			}
			$tab .= "</tr>";

			####DURASI PEKERJAAN####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4 valign=top>" . $_SESSION['lang']['durasipekerjaan'] . "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=justify colspan=7>" . (isset($dtdpkrj[$ard]) ? $dtdpkrj[$brs] : '') . "</td>";
				} else {
					$tab .= "<td align=justify colspan=4>
						<input type=text id='durasipekerjaan_" . $brs . "' size=40 class=myinputtext value='" . $dtdpkrj[$brs] . "'>
					</td>
					<td style='text-align:right'>Others</td>
					<td>
						<input type=number id=others_" . $brs . " value='" . ($dtnilai5s[$brs] == '' ? '0' : $dtnilai5s[$brs]) . "'  class='myinputtextnumber' onkeypress=\"return isNumber(event);\" onkeyup=\"scorevalidate('others_" . $brs . "')\" style='width:40px;text-align:center' min='0' max='5' />
					</td>";
				}
			}
			$tab .= "</tr>";


			####WAKTU PENYERAHAN####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4  class='bintang'>" . $_SESSION['lang']['waktupenyerahan'] . "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td colspan=8>" . $arrDeliverytime[$deliverytime[$brs]] . "</td>";
				} else {
					$optWaktuPenyerahan = "";
					$optWaktuPenyerahan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
					$str = "select *  from " . $dbname . ".log_5delivtime";
					$optWaktuPenyerahan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()) {
						if ($deliverytime[$brs] == $bar['kode']) {
							$optWaktuPenyerahan .= "<option value='" . $bar['kode'] . "' selected>" . $bar['nama'] . "</option>";
						} else {
							$optWaktuPenyerahan .= "<option value='" . $bar['kode'] . "'>" . $bar['nama'] . "</option>";
						}
					}

					$tab .= "<td colspan=6>
						<select id=waktu_penyerahan_" . $brs . ">" . $optWaktuPenyerahan . "</select></td>";
				}
			}

			$tab .= "</tr>";


			####GARANSI PRODUK/JASA####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4 valign=top>" . $_SESSION['lang']['garansiprodukjasa'] . "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=justify colspan=8>" . (isset($dtgrnprd[$brs]) ? $dtgrnprd[$brs] : '') . "</td>";
				} else {
					$tab .= "<td align=justify colspan=6>
						<input type=text id='garansiprodukjasa_" . $brs . "' size=51 class=myinputtext value='" . $dtgrnprd[$brs] . "'>
					</td>";
				}
			}
			$tab .= "</tr>";

			####POSISI STOK BARANG####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4 valign=top>" . $_SESSION['lang']['posisistokbarang'] . "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=justify colspan=8>" . (isset($dtposstk[$brs]) ? $dtposstk[$brs] : '') . "</td>";
				} else {
					$tab .= "<td align=justify colspan=6>
						<input type=text id='posisistokbarang_" . $brs . "' size=51 class=myinputtext value='" . $dtposstk[$brs] . "'>
					</td>";
				}
			}
			$tab .= "</tr>";

			####ASURANSI####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4 valign=top>" . $_SESSION['lang']['asuransi'] . "</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=justify colspan=8>" . (isset($dtasrn[$brs]) ? $dtasrn[$brs] : '') . "</td>";
				} else {
					$tab .= "<td align=justify colspan=6>
						<input type=text id='asuransi_" . $brs . "' size=51 class=myinputtext value='" . $dtasrn[$brs] . "'>
					</td>";
				}
			}
			$tab .= "</tr>";

			####KETERANGAN####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4 valign=top>" . $_SESSION['lang']['keterangan'] . "</td>";

			foreach ($dtNomor as $brs) {
				$ard += 1;
				if ($formPil != '1') {
					$tab .= "<td align=justify colspan=8>" . (isset($dtCttn[$brs]) ? $dtCttn[$brs] : '') . "</td>";
				} else {
					$tab .= "<td align=justify colspan=6><textarea placeholder='Maximal character 255' maxlength=255 id='ketUraian_" . $brs . "' name='ketUraian_" . $brs . "' onkeypress='return tanpa_kutip(event);' cols=42 rows=3>" . (isset($dtCttn[$brs]) ? $dtCttn[$brs] : '') . "</textarea></td>";
				}
			}

			$tab .= "</tr>";

			// Tanggal Surat
			$tab .= "<tr class='rowcontent'>
				<td colspan=4 valign=top>Tanggal Berlaku Surat Penawaran Harga</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=justify colspan=8>" . (isset($tglbatasrfq[$brs]) ? $tglbatasrfq[$brs] : '') . "</td>";
				} else {
					$tab .= "<td align=justify colspan=6>
						<input type=text class=myinputtext style='width:60px' id=tgl_batas_pnwharga_" . $brs . " onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='" . tanggalnormal($tglbatasrfq[$brs]) . "' readonly />
					</td>";
				}
			}
			$tab .= "</tr>";

			// Tanggal Surat
			$tab .= "<tr class='rowcontent'>
				<td colspan=4 valign=top>Tanggal Jatuh Tempo Pembayaran</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '1') {
					$tab .= "<td align=justify colspan=8>" . (isset($tgltempopembayaran[$brs]) ? $tgltempopembayaran[$brs] : '') . "</td>";
				} else {
					$tab .= "<td align=justify colspan=6>
						<input type=text class=myinputtext style='width:60px' id=tgl_tempo_pembayaran_" . $brs . " onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='" . tanggalnormal($tgltempopembayaran[$brs]) . "' readonly />
					</td>";
				}
			}
			$tab .= "</tr>";

			####FILEUPLOAD####
			$tab .= "<tr class='rowcontent'>
				<td colspan=4 valign=top>" . $_SESSION['lang']['uploaddata'] . "</td>";

			$ard = 0;
			$arrmodul = getmodulefil($emodul);
			foreach ($arrmodul as $key => $val) {
				$optkriteria .= "<option value='" . $key . "'>" . $val['kriteria'] . "</option>";
			}
			foreach ($dtNomor as $brs) {
				$tab .= "<td colspan=6 valign=top>
				<div id='listfiles_" . $_POST['notransaksi'] . "_" . $dtSupp[$brs] . "'><table>";
				$str = "select * from " . $dbname . ".log_permintaanhargafile where nomor='" . $_POST['notransaksi'] . "' and supplierid='" . $dtSupp[$brs] . "' and status='1'";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$nofiles = 0;
				while ($bar = $res->fetch()) {
					$nofiles++;
					$tab .= "<tr>
						<td>" . getcriterianame($bar['kriteriaefil']) . "</td>
						<td><a href='fileupload/rph/" . $bar['namafile'] . "' download title='" . $bar['namafile'] . "'>" . substr($bar['namafile'], 0, 40) . "...</a></td>
						<td>
							<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $_POST['notransaksi'] . "','" . $dtSupp[$brs] . "','" . $bar['namafile'] . "');\" >
						</td>
					</tr>";
				}
				$tab .= "<tr>
					<td>
						<select id='kriteriaefil_" . $_POST['notransaksi'] . "_" . $dtSupp[$brs] . "'>" . $optkriteria . "</select>
					</td>
					<td>
						<input type='file' name='upload_" . $_POST['notransaksi'] . "_" . $dtSupp[$brs] . "' id='upload_" . $_POST['notransaksi'] . "_" . $dtSupp[$brs] . "' class='mybutton'>
					</td>
					<td>
						<img id='detail_add' title='Tambah' class='resicon' onclick=\"addfile('" . $_POST['notransaksi'] . "','" . $dtSupp[$brs] . "')\" src='images/plus.png'>
					</td>
				</tr>
				</table></div>";
				$tab .= "</td>";
			}

			$tab .= "</tr>";

			####SIMPAN####
			$tab .= "<tr class=rowcontent><td colspan=3></td>
			<td colspan=4>
				<table cellspacing=1 border=0 class=sortable width=100%>
					<tr class='rowcontent'>
						<td colspan=2 style='text-align:center'>Tender / Non Tender</td>
						<td colspan=1 style='text-align:center'><input type=checkbox id=tender_yn checked /></td>
					</tr>
					<tr class='rowcontent'>
						<td colspan=2></td>
						<td style='text-align:center'  class='bintang'>Weighted Factor</td>
					</tr>
					<tr class='rowcontent'>
						<td colspan=2>I. Price</td>
						<td style='text-align:center'>
							<input type=number id=weightfactor1 value='" . ($dtnilai1f == '' ? '0' : $dtnilai1f) . "'  class='myinputtextnumber' onkeypress=\"return isNumber(event)\" onkeyup=\"factorvalidate('weightfactor1')\" style='width:40px;text-align:center' min='0' max='100' />
						</td>
					</tr>
					<tr class='rowcontent'>
						<td colspan=2 valign=top align=left>II. Availability</td>
						<td style='text-align:Center'>
							<input type=number id=weightfactor2 value='" . ($dtnilai2f == '' ? '0' : $dtnilai2f) . "'  class='myinputtextnumber' onkeypress=\"return isNumber(event)\" onkeyup=\"factorvalidate('weightfactor2')\" style='width:40px;text-align:center' min='0' max='100' />
						</td>
					</tr>
					<tr class='rowcontent'>
						<td colspan=2 valign=top align=left>III. Quality/ Performance/ Integrity</td>
						<td style='text-align:Center'>
							<input type=number id=weightfactor3 value='" . ($dtnilai3f == '' ? '0' : $dtnilai3f) . "'  class='myinputtextnumber' onkeypress=\"return isNumber(event)\" onkeyup=\"factorvalidate('weightfactor3')\" style='width:40px;text-align:center' min='0' max='100' />
						</td>
					</tr>
					<tr class='rowcontent'>
						<td colspan=2 valign=top align=left>IV. Service</td>
						<td style='text-align:Center'>
							<input type=number id=weightfactor4 value='" . ($dtnilai4f == '' ? '0' : $dtnilai4f) . "'  class='myinputtextnumber' onkeypress=\"return isNumber(event)\" onkeyup=\"factorvalidate('weightfactor4')\" style='width:40px;text-align:center' min='0' max='100' />
						</td>
					</tr>
					<tr class='rowcontent'>
						<td colspan=2 valign=top align=left>V. Other Concerns (payment scheme, etc.)</td>
						<td style='text-align:Center'>
							<input type=number id=weightfactor5 value='" . ($dtnilai5f == '' ? '0' : $dtnilai5f) . "'  class='myinputtextnumber' onkeypress=\"return isNumber(event)\" onkeyup=\"factorvalidate('weightfactor5')\" style='width:40px;text-align:center' min='0' max='100' />
						</td>
					</tr>
				</table>
			</td>";

			foreach ($dtNomor as $brs) {
				if ($formPil != '0') {
					$tab .= "<td align=center colspan=6>
						<button class=mybutton style='width:200px;height:50px;cursor:pointer' id=save_" . $brs . " onclick=simpanSemua2(" . $brs . "," . $totBrg . ")>" . $_SESSION['lang']['save'] . "</button>
					</td>";
				}
			}

			$tab .= "</tr>
			</tbody>
		</table>";

			echo $tab;
			break;

		// joki
		case 'addmaterialso':
			$supplierAll = checkPostGet('supplier', '');
			$nourutAll = checkPostGet('nourut', '');
			$no_prmntanAll = checkPostGet('no_prmntan', '');
			$jlhpesansoAll = checkPostGet('jlhpesanso', '');
			$hargasatuansoAll = checkPostGet('hargasatuanso', '');
			$namabarangso = checkPostGet('namabarangso', '');
			// $jlhpesanso=str_replace(',','',$jlhpesanso);
			// $hargasatuanso=str_replace(',','',$hargasatuanso);
			$t_dataa = count($supplierAll) - 1;

			for ($i = 0; $i <= $t_dataa; $i++) {

				if ($namabarangso == '') {
					exit("Warning : Nama Barang harus diisi.");
				}
				if ($jlhpesansoAll[$i] == '' || $jlhpesansoAll[$i] == 0) {
					exit("Warning : Jumlah Dipesan harus diisi dan lebih besar dari 0.");
				}
				if ($hargasatuansoAll[$i] == '' || $hargasatuansoAll[$i] == 0) {
					exit("Warning : Harga Satuan harus diisi dan lebih besar dari 0.");
				}

				$newdata = array('no_prmntan' => $no_prmntanAll[$i], 'nourut' => $nourutAll[$i], 'supplier' => $supplierAll[$i], 'namabarang' => $namabarangso, 'jumlah' => $jlhpesansoAll[$i], 'hargasatuan' => $hargasatuansoAll[$i]);
				array_push($_SESSION['somaterial'], $newdata);

				// $str = "insert into ".$dbname.".log_somaterial_perbandingan values ('','".$no_prmntanAll[$i]."','".$nourutAll[$i]."','".$supplierAll[$i]."','".$namabarangso."','".$jlhpesansoAll[$i]."','".$hargasatuansoAll[$i]."')";
				// try
				// {
				// 	$owlPDO->exec($str);
				// }
				// catch(PDOException $e)
				// {
				// 	echo " Gagal," . addslashes($e->getMessage());
				// }
			}

			break;
		case 'deletesomaterial':
			$namabarangso = checkPostGet('namabarang', '');
			foreach ($_SESSION['somaterial'] as $key => $row) {
				if ($row['namabarang'] == $namabarangso) {
					unset($_SESSION['somaterial'][$key]);
				}
			}
			break;
		// case 'deletesomaterial':
		// 	$namabarang=checkPostGet('namabarang','');
		// 	$notransaksi=checkPostGet('notransaksi','');
		// 	$str="delete from ".$dbname.".log_somaterial_perbandingan where namabarang='".$namabarang."' and nodph='".$notransaksi."' ";
		// 	try
		// 	{
		// 		$owlPDO->exec($str);
		// 	}
		// 	catch(PDOException $e)
		// 	{
		// 		echo " Gagal," . addslashes($e->getMessage());
		// 	}
		// break;


		case 'loadmaterialso':
			$tab = "";
			$norfq = checkPostGet('norfq', '');

			$countsupplier = 0;
			$str = "select distinct * from " . $dbname . ".log_perintaanhargaht where nomor='" . $norfq . "' order by supplierid asc";
			$res = fetchdata($str);
			foreach ($res as $val) {
				$dtNomor[] = $val['nourut'];
				$countsupplier++;
			}


			if (count($_SESSION['somaterial']) < 1) {
				$nobaris = 0;
				$strx = "select * from " . $dbname . ".log_somaterial_perbandingan where nodph='" . $norfq . "'";
				$resx = fetchdata($strx);
				$tempnamabarang = "";
				foreach ($resx as $valx) {
					if ($tempnamabarang != $valx['namabarang']) {
						$nobaris++;
					}
					$newdata = array('no_prmntan' => $valx['nomor'], 'baris' => $nobaris, 'nourut' => $valx['nourut'], 'supplier' => $valx['supplierid'], 'namabarang' => $valx['namabarang'], 'jumlah' => $valx['jumlah'], 'hargasatuan' => $valx['harga']);
					array_push($_SESSION['somaterial'], $newdata);
					$tempnamabarang = $valx['namabarang'];
				}
			}

			$nobaris = 0;
			$tempnamabarang = "";
			foreach ($_SESSION['somaterial'] as $key => $valx) {
				if ($tempnamabarang != $valx['namabarang']) {
					$nobaris++;
				}
				unset($_SESSION['somaterial'][$key]);
				$newdata = array('no_prmntan' => $valx['no_prmntan'], 'baris' => $nobaris, 'nourut' => $valx['nourut'], 'supplier' => $valx['supplier'], 'namabarang' => $valx['namabarang'], 'jumlah' => $valx['jumlah'], 'hargasatuan' => $valx['hargasatuan']);
				array_push($_SESSION['somaterial'], $newdata);
				$tempnamabarang = $valx['namabarang'];
			}

			$totalrows = (count($_SESSION['somaterial']) / $countsupplier);
			$countcell = max($dtNomor);
			$tempnamabarang = "";
			foreach ($_SESSION['somaterial'] as $key => $val) {
				$namabarang = $val['namabarang'];
				$baris = $val['baris'];
				if ($tempnamabarang != $namabarang) {
					$tab .= "<tr class='rowcontent'>";
					$tab .= "<td style='text-align:center'>
					<!--<img onclick=\"deletesomaterial('" . $val['namabarang'] . "','" . $baris . "','" . $totalrows . "','" . $countcell . "')\" class='resicon' src='images/delete1.png' title='Hapus' style='cursor:pointer'>-->
					<img onclick=\"deletesomaterial('" . $val['namabarang'] . "')\" class='resicon' src='images/delete1.png' title='Hapus' style='cursor:pointer'>
				</td>";
					$tab .= "<td colspan=3 id='dataSO_" . $baris . "'>" . $val['namabarang'] . "</td>";
					$tab .= "<td></td>";
					$tab .= "<td></td>";
					$tab .= "<td></td>";
					foreach ($dtNomor as $cell) {
						foreach ($_SESSION['somaterial'] as $keyx => $valx) {
							if ($valx['baris'] == $baris && $valx['nourut'] == $cell) {
								$qty = $valx['jumlah'];
								$harga = $valx['hargasatuan'];
							}
						}
						$totalharga = $qty * $harga;
						$tab .= "<td></td>";
						$tab .= "<td>
						<input type='text' id='qtyso_" . $baris . "_" . $cell . "' disabled value='" . hidezerodecimal($qty, 3) . "' class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" onkeyup=\"getsubtotalitemsox('" . $baris . "','" . $cell . "','" . $totalrows . "');z.numberFormat('qtyso_" . $baris . "_" . $cell . "',2);\" onblur=\"changeqtyso('" . $baris . "','" . $cell . "','" . $namabarang . "',this.value)\" style='width:75px'>
					</td>";
						$tab .= "<td>
					<input type='text' id='priceso_" . $baris . "_" . $cell . "' disabled value='" . hidezerodecimal($harga, 0) . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' onkeyup=\"getsubtotalitemsox('" . $baris . "','" . $cell . "','" . $totalrows . "');z.numberFormat('priceso_" . $baris . "_" . $cell . "',2);\" onblur=\"changepriceso('" . $baris . "','" . $cell . "','" . $namabarang . "',this.value)\" style='width:75px'>
					</td>";
						$tab .= "<td></td>";
						$tab .= "<td>
						<input type='text' id='totalso_" . $baris . "_" . $cell . "' disabled value='" . number_format($totalharga, 2) . "' class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" style='width:75px'>
					</td>";
						$tab .= "<td></td>";
					}
					$tab .= "</tr>";
					$tempnamabarang = $namabarang;
				}
			}
			echo $tab;
			break;

		case 'updateTransaksi':
			try {
				
				$owlPDO->beginTransaction();

				$ongkir = str_replace(',', '', $ongkir);
				$totalongkir = str_replace(',', '', $totalongkir);
				$subTotal = str_replace(',', '', $subTotal);
				$nilaiPermintaan = str_replace(',', '', $nilaiPermintaan);
				$diskonPersen = str_replace(',', '', $diskonPersen);
				$nilDiskon = str_replace(',', '', $nilDiskon);
				//PPH Final
				$pphfinal = str_replace(',', '', $pphfinal);
				$pbbkb = str_replace(',', '', $pbbkb);
				$str = "select count(supplierid) as supplierid from " . $dbname . ".log_perintaanhargaht where nomor='" . $no_prmntan . "'";
				$res = fetchdata($str);
				$countsup = $res[0]['supplierid'];

				if (strlen($ketUraian) > 255) {
					throw new PDOException("Keterangan melebihi 255 character");
				}

				if ($kurs <= 0 || $kurs == '') {
					throw new PDOException("Kurs harus diisi atau lebih besar dari nol.");
				}

				if ($tglDari == '00-00-0000') {
					throw new PDOException("Tanggal dari harus diisi.");
				}

				if ($tglSmp == '00-00-0000') {
					throw new PDOException("Tanggal sampai harus diisi.");
				}

				if ($termPay == '') {
					throw new PDOException("Syarat pembayaran harus dipilih.");
				}

				if ($idFranco == '') {
					throw new PDOException("Lokasi pengiriman harus dipilih.");
				}
				if (count($_SESSION['somaterial']) > 0) {
					$diskonPersen = $diskonPersen;
				} else {
					if (intval($nilDiskon) != 0) {
						$diskonPersen = ($nilDiskon / $subTotal) * 100;
					}
				}


				$nilai1s = checkPostGet('nilai1s', '0');
				$nilai2s = checkPostGet('nilai2s', '0');
				$nilai3s = checkPostGet('nilai3s', '0');
				$nilai4s = checkPostGet('nilai4s', '0');
				$nilai5s = checkPostGet('nilai5s', '0');
				$nilai1f = checkPostGet('nilai1f', '0');
				$nilai2f = checkPostGet('nilai2f', '0');
				$nilai3f = checkPostGet('nilai3f', '0');
				$nilai4f = checkPostGet('nilai4f', '0');
				$nilai5f = checkPostGet('nilai5f', '0');

				$tgl_batas_pnwharga = checkPostGet('tgl_batas_pnwharga', '');
				$tempo_pembayaran = checkPostGet('tempo_pembayaran', '');
				$waktu_penyerahan = checkPostGet('waktu_penyerahan', '');
				$tender_yn_h = checkPostGet('tender_yn_h', '');
				$masterial_jasa_h = checkPostGet('masterial_jasa_h', '');
				$penambahpph22 = checkPostGet('penambahpph22', '');

				// if($tgl_batas_pnwharga == '' || $tgl_batas_pnwharga == '00-00-0000'){
				// if ($tempo_pembayaran == '' || $tempo_pembayaran == '00-00-0000') {
				// 	throw new PDOException("Tempo Pembayaran Tidak Boleh Kosong...");
				// }
				if ($waktu_penyerahan == '') {
					throw new PDOException("Waktu Penyerahan Tidak Boleh Kosong...");
				}
				if ($tgl_batas_pnwharga == '') {
					throw new PDOException("Tanggal Berlaku Surat Perbandingan Harga Tidak Boleh Kosong...");
				}

				if ($nilai1s == '0' || $nilai1s == '') {
					throw new PDOException("warning : Nilai score masih 0, silahkan dikoreksi kembali");
				}
				if ($nilai2s == '0' || $nilai2s == '') {
					throw new PDOException("warning : Nilai score availability  masih 0, silahkan dikoreksi kembali");
				}
				if ($nilai3s == '0' || $nilai3s == '') {
					throw new PDOException("warning : Nilai score Quality Performance Integrity  masih 0, silahkan dikoreksi kembali");
				}
				if ($nilai4s == '0' || $nilai4s == '') {
					throw new PDOException("warning : Nilai score Service masih 0, silahkan dikoreksi kembali");
				}
				if ($nilai5s == '0' || $nilai5s == '') {
					throw new PDOException("warning : Nilai score others  masih 0, silahkan dikoreksi kembali");
				}

				$nilaitotf = $nilai1f + $nilai2f + $nilai3f + $nilai4f + $nilai5f;
				if ($countsup > 1) {
					if ($nilaitotf != 100) {
						throw new PDOException("warning : Nilai total Weighted Factor harus 100 %. Nilai saat ini adalah " . $nilaitotf . " %");
					}
				}

				//Update + PPH Final

				$str = "update " . $dbname . ".log_perintaanhargaht set tender='" . $tender_yn_h . "',ppnjasamaterial='" . $masterial_jasa_h . "',penambahpph22='" . $penambahpph22 . "', nilai1f='" . $nilai1f . "', nilai2f='" . $nilai2f . "', nilai3f='" . $nilai3f . "', nilai4f='" . $nilai4f . "', nilai5f='" . $nilai5f . "' where nomor='" . $no_prmntan . "'";
				$owlPDO->exec($str);

				$str = "update " . $dbname . ".log_perintaanhargaht set tglbatasrfq='" . tanggalsystem($tgl_batas_pnwharga) . "',tgltempopembayaran='" . tanggalsystem($tempo_pembayaran) . "', deliverytime='" . $waktu_penyerahan . "', id_franco='" . intval($idFranco) . "', stock='" . intval($stockId) . "',catatan='" . $ketUraian . "',sisbayar2='" . $termPay . "',ongkir='" . $totalongkir . "', ppn='" . $nilPPn . "', pph='" . $nilPPh . "', pph22='" . $nilPPh22 . "',pbbkb='" . $pbbkb . "', pphfinal='" . $pphfinal . "',  subtotal='" . $subTotal . "',diskonpersen='" . $diskonPersen . "', nilaidiskon='" . $nilDiskon . "', nilaipermintaan='" . $nilaiPermintaan . "', tgldari='" . tanggalsystem($tglDari) . "', tglsmp='" . tanggalsystem($tglSmp) . "', kurs='" . $kurs . "',matauang='" . $mtUang . "',supplierid='" . $supplierid . "', durasipengiriman='" . $durasipengiriman . "', durasipekerjaan='" . $durasipekerjaan . "', garansiproduk='" . $garansiprodukjasa . "', posisistok='" . $posisistokbarang . "', asuransi='" . $asuransi . "', nilai1s='" . $nilai1s . "', nilai2s='" . $nilai2s . "', nilai3s='" . $nilai3s . "', nilai4s='" . $nilai4s . "', nilai5s='" . $nilai5s . "' where nomor='" . $no_prmntan . "' and nourut='" . $nourut . "'";

				$expnotransaksi = explode('/', $no_prmntan);
				$cekErrorDt = "";
				$totRow = count($_POST['kdbrg']);
				foreach ($_POST['kdbrg'] as $row => $Act) {
					$kdbrg = $Act;
					$mrk = $_POST['mrk'][$row];
					$merk = $_POST['merk'][$row];
					$score = $_POST['score'][$row];
					$factor = $_POST['factor'][$row];
					$hrg = str_replace(',', '', $_POST['price'][$row]);
					$qty = str_replace(',', '', $_POST['qty'][$row]);
					$hargaterakhir = str_replace(',', '', $_POST['hargaterakhir'][$row]);
					$hargaestimasi = str_replace(',', '', $_POST['hargaestimasi'][$row]);
					$optNmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $kdbrg . "'");

					if ($qty == 0 || $qty == '') {
						$cekErrorDt .= "- " . $optNmBarang[$kdbrg] . " : Jumlah/Qty belum diisi\n";
					}

					$strdtc = "select a.realisasi , a.hasilkonversi from " . $dbname . ".log_prapodt a 
					left join " . $dbname . ".log_permintaanhargadt b on a.kodebarang=b.kodebarang and a.nopp=b.nopp 
					where b.nomor='" . $no_prmntan . "' and b.kodebarang='" . $kdbrg . "' and b.nourut='" . $nourut . "'";
					$resdtc = $owlPDO->query($strdtc) or die(print " Gagal: " . PDOException::getMessage());
					$resdtc->setFetchMode(PDO::FETCH_ASSOC);
					while ($bardtc = $resdtc->fetch()) {
						if ($bardtc['hasilkonversi'] > 0) {
							$bardtc['realisasi'] = $bardtc['hasilkonversi'];
						}
						if ($qty > $bardtc['realisasi']) {
							$cekErrorDt .= "- " . $optNmBarang[$kdbrg] . " : Jumlah Permintaan lebih besar dari realisasi : " . $bardtc['realisasi'] . "\n";
						}
					}

					if ($hrg == '') {
						$cekErrorDt .= "- " . $optNmBarang[$kdbrg] . " : Harga belum diisi\n";
						continue;
					}

					$mypt = "";
					$myunit = "";
					$opthgstn = makeOption($dbname, 'log_5masterbarang', 'kodebarang,hargasatuan', "kodebarang='" . $kdbrg . "'");
					if ($opthgstn[$kdbrg] != '') {
						$exphargasatuan = explode(',', $opthgstn[$kdbrg]);
						if (in_array($expnotransaksi[4], $exphargasatuan)) {
							$mypt = $optpt[$expnotransaksi[4]];
							$myunit = $expnotransaksi[4];
						}
					}

					$strx = "select id, hargaestimasi from " . $dbname . ".log_5hargaterakhir where status='1' and kodebarang='" . $kdbrg . "' and pt='" . $mypt . "' and unit='" . $myunit . "' limit 1";
					$resx = fetchdata($strx);
					if (count($resx) > 0) {
						$hrgest = ($resx[0]['hargaestimasi'] == '' ? 0 : $resx[0]['hargaestimasi']);
						if ($hrgest != $hargaestimasi) {
							$strdt3[] = "update " . $dbname . ".log_5hargaterakhir set hargaestimasi='" . $hargaestimasi . "' where id='" . $resx[0]['id'] . "'";
						} else {
							$strdt3[] = "update " . $dbname . ".log_5hargaterakhir set status='1' where id='" . $resx[0]['id'] . "'";
						}
					} else {
						$strdt3[] = "insert into " . $dbname . ".log_5hargaterakhir (pt,unit,kodebarang,tanggal,hargaestimasi,status,createdby,createtime,updateby,updatetime) values ('" . $mypt . "','" . $myunit . "','" . $kdbrg . "','" . date('y-m-d') . "','" . $hargaestimasi . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "')";
					}

					$strdt[] = "update " . $dbname . ".log_permintaanhargadt set `jumlah`='" . $qty . "',`harga`='" . $hrg . "',`ongkir`='" . $ongkir . "',`spec`='" . $merk . "', score='" . $score . "', factor='" . $factor . "' where nomor='" . $no_prmntan . "' and kodebarang='" . $kdbrg . "' and nourut='" . $nourut . "'";
					$strdt2[] = "update " . $dbname . ".log_permintaanhargadt set hargaterakhir='" . $hargaterakhir . "',hargaestimasi='" . $hargaestimasi . "' where nomor='" . $no_prmntan . "' and kodebarang='" . $kdbrg . "'";
				}

				if ($cekErrorDt != "") {
					throw new PDOException("\n" . $cekErrorDt);
				}

				$owlPDO->exec($str);
				for ($i = 0; $i < count($strdt); $i++) {
					$owlPDO->exec($strdt[$i]);
					$owlPDO->exec($strdt2[$i]);
					$owlPDO->exec($strdt3[$i]);
				}

				// MATERIAL SO
				$str = "delete from " . $dbname . ".log_somaterial_perbandingan where nodph='" . $no_prmntan . "'";
				$owlPDO->exec($str);
				if (count($_SESSION['somaterial']) > 0) {
					foreach ($_SESSION['somaterial'] as $key => $val) {
						$str = "insert into " . $dbname . ".log_somaterial_perbandingan (nopo,nodph,nourut,supplierid,namabarang,jumlah,harga) values ('','" . $no_prmntan . "','" . $val['nourut'] . "','" . $val['supplier'] . "','" . $val['namabarang'] . "','" . $val['jumlah'] . "','" . $val['hargasatuan'] . "')";
						$owlPDO->exec($str);
					}
				}



				$owlPDO->commit();
			} catch (PDOException $e) {
				$owlPDO->rollback();
				echo "Warning :<br>" . addslashes($e->getMessage());
			}
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

					if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
						// if($_FILES['file']['size'] <= 250000)
						// {
						$str = "insert into " . $dbname . ".log_permintaanhargafile values ('" . $notransaksi . "','" . $supplierid . "','" . $filename . "','" . $filetype . "','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
						try {
							$owlPDO->exec($str);
							move_uploaded_file($file_tmpname, "fileupload/rph/$filename");
						} catch (PDOException $e) {
							echo " Gagal," . addslashes($e->getMessage());
						}
						// }
						// else
						// {
						// exit("warning : Ukuran file upload maksimal 2500kb");
						// }
					} else {
						exit("Warning : Format file upload harus .jpg .jpeg .png .pdf .xls .xlsx .doc .docx");
					}
				}
			}
			break;

		case 'loadfiles':
			$arrmodul = getmodulefil($emodul);
			foreach ($arrmodul as $key => $val) {
				$optkriteria .= "<option value='" . $key . "'>" . $val['kriteria'] . "</option>";
			}
			$tab = "";
			$tab .= "<table>";
			$str = "select * from " . $dbname . ".log_permintaanhargafile where nomor='" . $notransaksi . "' and supplierid='" . $supplierid . "' and status='1'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$tab .= "<tr>
				<td>" . getcriterianame($bar['kriteriaefil']) . "</td>
				<td><a href='fileupload/rph/" . $bar['namafile'] . "' download title='" . $bar['namafile'] . "'>" . substr($bar['namafile'], 0, 40) . "...</a></td>
				<td>
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $notransaksi . "','" . $supplierid . "','" . $bar['namafile'] . "');\" >
				</td>
			</tr>";
			}
			$tab .= "<tr>
			<td>
				<select id='kriteriaefil_" . $notransaksi . "_" . $supplierid . "'>" . $optkriteria . "</select>
			</td>
			<td>
				<input type='file' name='upload_" . $notransaksi . "_" . $supplierid . "' id='upload_" . $notransaksi . "_" . $supplierid . "' class='mybutton'>
			</td>
			<td>
				<img id='detail_add' title='Tambah' class='resicon' onclick=\"addfile('" . $notransaksi . "','" . $supplierid . "')\" src='images/plus.png'>
			</td>
		</tr>
		</table>";
			echo $tab;
			break;

		case 'deletefile':
			$str = "delete from " . $dbname . ".log_permintaanhargafile where nomor='" . $notransaksi . "' and supplierid='" . $supplierid . "' and namafile='" . $namafile . "'";
			try {
				$owlPDO->exec($str);
				$path = "fileupload/rph/" . $namafile;
				unlink($path);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
			break;

		case 'listBarangDetail':
			$tab .= "<tr class=rowcontent><td colspan=7>&nbsp;</td></tr>";
			$sPp = "select distinct * from " . $dbname . ". log_permintaanhargadt where nomor='" . $_POST['notransaksi'] . "' and nourut='" . $_POST['nourut'] . "'";
			$qPp = $owlPDO->query($sPp) or die(print " Gagal: " . PDOException::getMessage());
			$qPp->setFetchMode(PDO::FETCH_ASSOC);
			while ($rPp = $qPp->fetch()) {
				$no++;
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td style=width:20px>" . $no . "</td>";
				$tab .= "<td style='width:180px' id=nopplst_" . $no . ">" . $rPp['nopp'] . "</td>";
				$tab .= "<td style='width:88px' id=kodebrg_" . $no . ">" . $rPp['kodebarang'] . "</td>";
				$tab .= "<td style=width:380px>" . $optBarang[$rPp['kodebarang']] . "</td>";
				$tab .= "<td style='width:62px' align=right id=jumlah_" . $no . ">" . $rPp['jumlah'] . "</td>";
				$tab .= "<td style=width:55px>" . $optSat[$rPp['kodebarang']] . "</td>";
				$tab .= "<td  style='width:10px' align=center><input type=checkbox id=pilBrg_" . $no . " checked /></td></tr>";
			}
			echo $tab;
			break;

		case 'deleted':
			## DELETE HEADER RPH ##
			$str = "delete from " . $dbname . ".log_perintaanhargaht where nomor='" . $no_permintaan . "'";
			try {
				$owlPDO->exec($str);

				## DELETE DETAIL RPH AND UPDATE VERIFIKASI PP ##
				$str = "select kodebarang,nopp from " . $dbname . ".log_permintaanhargadt where nomor='" . $no_permintaan . "'";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$str = "update " . $dbname . ".log_listverifikasi set status='0' where nopp='" . $bar['nopp'] . "' and kodebarang='" . $bar['kodebarang'] . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
					try {
						$owlPDO->exec($str);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						die();
					}
				}

				$str = "delete from " . $dbname . ".log_permintaanhargadt where nomor='" . $no_permintaan . "'";
				try {
					$owlPDO->exec($str);

					$str = "select namafile from " . $dbname . ".log_permintaanhargafile where nomor='" . $no_permintaan . "'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()) {
						$path = "fileupload/rph/" . $bar['namafile'];
						unlink($path);
					}

					$str = "delete from " . $dbname . ".log_permintaanhargafile where nomor='" . $no_permintaan . "'";
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
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
			break;

		case 'get_alasan_batal':
			$str = "select * from " . $dbname . ".log_perintaanhargaht where nomor='" . $no_permintaan . "'";
			$res = fetchData($str);
			// if(count($res) < 2){
			// exit("Gagal : Jumlah penawaran harga minimal 2 supplier.");
			// }

			$errmsg = 0;
			foreach ($res as $key => $val) {
				if ($val['id_franco'] == '0') {
					$errmsg++;
				}
				if ($val['sisbayar2'] == '0') {
					$errmsg++;
				}
				if ($val['subtotal'] == '0') {
					$errmsg++;
				}
				// if($val['tgldari']=='0000-00-00'){
				// $errmsg++;
				// }
				// if($val['tglsmp']=='0000-00-00'){
				// $errmsg++;
				// }
			}

			if ($errmsg > 0) {
				exit("Warning : Silahkan cek kembali, form perbandingan harga belum lengkap.");
			}

			$errmsg = "";
			$str = "select nopp,kodebarang,jumlah from " . $dbname . ".log_permintaanhargadt where nomor='" . $no_permintaan . "' order by kodebarang asc";
			$res = fetchdata($str);
			$tmpkode = $res[0]['kodebarang'];
			$tmpqty = $res[0]['jumlah'];
			$arrdata = array();
			foreach ($res as $val) {
				if ($tmpkode == $val['kodebarang']) {
					if ($tmpqty != $val['jumlah']) {
						$errmsg .= "\n- " . $val['kodebarang'];
					}
					##CEK KUANTITAS RFQ DENG KUANTITAS PR
					$strx = "select realisasi,hasilkonversi from " . $dbname . ".log_prapodt where nopp='" . $val['nopp'] . "' and kodebarang='" . $val['kodebarang'] . "'";
					$resx = fetchdata($strx);
					foreach ($resx as $valx) {
						if ($valx['hasilkonversi'] > 0) {
							$valx['realisasi'] = $valx['hasilkonversi'];
						}
						if ($valx['realisasi'] != $val['jumlah']) {
							$arrdata[$val['kodebarang']]['kodebarang'] = $val['kodebarang'];
							$arrdata[$val['kodebarang']]['qtyrfq'] = $val['jumlah'];
							$arrdata[$val['kodebarang']]['qtypr'] = $valx['realisasi'];
						}
					}
				} else {
					$tmpqty = $val['jumlah'];
				}
				$tmpkode = $val['kodebarang'];
			}
			if ($errmsg != '') {
				exit("Warning : Jumlah kuantitas pemesanan harus sama disemua tender untuk kode barang berikut:" . $errmsg);
			}

			$value = "";
			if (count($arrdata) > 0) {
				foreach ($arrdata as $val) {
					$value .= "\n- " . $val['kodebarang'] . " : Qty PR/SR=" . $val['qtypr'] . " , Qty RFQ=" . $val['qtyrfq'];
				}
			}

			echo $value;
			break;

		case 'postingrfq':
			try {
				$owlPDO->beginTransaction();
				$createpr = checkPostGet('createpr', '');

				// cek tanggal perbandingan Harga
				$tgl0 = date("Y-m-d");
				$str0 = "select * from " . $dbname . ".log_perintaanhargaht where nomor='" . $no_permintaan . "'";
				$res0 = fetchdata($str0);
				foreach ($res0 as $val0) {
					if ($val0['tglbatasrfq'] != '0000-00-00') {
						// apakah sudah lewat
						if ($tgl0 > $val0['tglbatasrfq']) {
							exit("Warning : Tanggal Perbandingan Sudah Melewati Batas...");
						}
					}
				}

				if ($createpr == '1') {
					$purchaser = $_SESSION['standard']['userid'];
					$tglskrg = date("Y-m-d H:i:S");
					$no = 0;
					$str = "select nopp,kodebarang,jumlah from " . $dbname . ".log_permintaanhargadt where nomor='" . $no_permintaan . "' group by kodebarang";
					$res = fetchdata($str);
					foreach ($res as $val) {
						##CEK KUANTITAS RFQ DENG KUANTITAS PR
						$strxx = "select * from " . $dbname . ".log_prapodt where nopp='" . $val['nopp'] . "' and kodebarang='" . $val['kodebarang'] . "'";
						$resxx = fetchdata($strxx);
						foreach ($resxx as $valxx) {
							if ($valxx['realisasi'] != $val['jumlah']) {
								$retur = $valxx['realisasi'] - $val['jumlah'];
								$no++;
								if ($no == 1) {
									##CREATE NEW PR
									$awal = 0;
									$nopp = $val['nopp'];
									$kodebarang = $val['kodebarang'];
									$nourut = substr($nopp, 0, 3);
									$crnopp = str_replace($nourut, '', $nopp);
									$strx = "select nopp from " . $dbname . ".log_prapoht where nopp like '%" . $crnopp . "' order by nopp desc limit 1";
									$resx = fetchdata($strx);
									$awal = substr($resx[0]['nopp'], 0, 3);
									$awal = intval($awal) + 1;
									$counter = addZero($awal, 3);
									$newnopp = $counter . "" . $crnopp;

									$strx = "select * from " . $dbname . ".log_prapoht where nopp='" . $nopp . "'";
									$resx = fetchdata($strx);
									foreach ($resx as $valx) {
										$strx = "insert into " . $dbname . ".log_prapoht values ('" . $valx['pt'] . "','" . $valx['unit'] . "','" . $valx['tipepp'] . "','" . $newnopp . "','" . $valx['tanggal'] . "','" . $valx['keterangan'] . "','" . $valx['dibuat'] . "','" . $valx['requester'] . "','" . $valx['close'] . "','" . $no_permintaan . "')";
										$owlPDO->exec($strx);
									}

									##CREATE LIST FILE
									$strx = "select * from " . $dbname . ".listfileupload where notransaksi='" . $nopp . "'";
									$resx = fetchdata($strx);
									foreach ($resx as $valx) {
										$strx = "insert into " . $dbname . ".listfileupload values ('','" . $newnopp . "','" . $valx['namafile'] . "','" . $valx['formaticon'] . "','" . $valx['kriteriaefil'] . "','" . $valx['status'] . "','" . $valx['createdby'] . "','" . $valx['createdtime'] . "')";
										$owlPDO->exec($strx);
									}

									##CREATE APPROVAL
									$strx = "select * from " . $dbname . ".approval where notransaksi='" . $nopp . "'";
									$resx = fetchdata($strx);
									foreach ($resx as $valx) {
										$strx = "insert into " . $dbname . ".approval values ('','" . $newnopp . "','" . $valx['jenispersetujuan'] . "','" . $valx['level'] . "','" . $valx['karyawanid'] . "','" . $valx['status'] . "','" . $valx['komentar'] . "','" . $valx['keterangan'] . "','" . $valx['tanggal'] . "')";
										$owlPDO->exec($strx);
									}
								}

								##INSERT PR DETAIL
								$strx = "insert into " . $dbname . ".log_prapodt (`nopp`, `kodebarang`, `stock`, `jumlah`, `jumlahpp`, `realisasi`, `hargasatuan`, `satuanpp`, `satuankonversi`, `hasilkonversi`, `keterangan`, `anggaran`, `tgl_sdt`, `prioritas`, `create_po`, `pembelian`, `lokalpusat`, `status`, `tglAlokasi`, `alasanstatus`, `purchaser`, `ditolakoleh`, `kodevhc`, `updateby`, `updatetime`, `keteranganubah`, `hargalama`, `spk`, `kmhm`, `nokontrak`, `realperbarang`, `bgtperbarang`, `kodeproject`) VALUES   ('" . $newnopp . "','" . $valxx['kodebarang'] . "','" . $valxx['stock'] . "','" . $retur . "','" . $retur . "','" . $retur . "','" . $valxx['hargasatuan'] . "','" . $valxx['satuanpp'] . "','" . $valxx['satuankonversi'] . "','" . $valxx['hasilkonversi'] . "','" . $valxx['keterangan'] . "','" . $valxx['anggaran'] . "','" . $valxx['tgl_sdt'] . "','" . $valxx['prioritas'] . "','0','" . $valxx['pembelian'] . "','" . $valxx['lokalpusat'] . "','0','" . $valxx['tglAlokasi'] . "','" . $valxx['alasanstatus'] . "','" . $purchaser . "','" . $valxx['ditolakoleh'] . "','" . $valxx['kodevhc'] . "','" . $valxx['updateby'] . "','" . $valxx['updatetime'] . "','" . $valxx['keteranganubah'] . "','" . $valxx['hargalama'] . "','" . $valxx['spk'] . "','" . $valxx['kmhm'] . "','" . $valxx['nokontrak'] . "','','','')";
								$owlPDO->exec($strx);

								##INSERT PR VERIFIKASI
								$strx = "insert into " . $dbname . ".log_listverifikasi values ('','" . $newnopp . "','" . $valxx['kodebarang'] . "','" . $purchaser . "','0','0','0','" . $_SESSION['standard']['userid'] . "','" . $tglskrg . "','" . $_SESSION['standard']['userid'] . "','" . $tglskrg . "')";
								$owlPDO->exec($strx);
							}
						}
					}
				}

				$str = "update " . $dbname . ".log_perintaanhargaht set flag='1' where nomor='" . $no_permintaan . "'";
				$owlPDO->exec($str);

				$str = "update " . $dbname . ".list_notification set shownotif='1' where kodenotification='TRPH' and kodetransaksi='" . $no_permintaan . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
				$owlPDO->exec($str);

				$arrkar = picnotification('RFQVER');
				$detail = "RFQ dengan no " . $no_permintaan . " sudah diajukan, Silahkan lakukan verikasi pemenang tender terhadap no RFQ tersebut";
				foreach ($arrkar as $valkar) {
					$strq = "insert into " . $dbname . ".list_notification (id,kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('','" . $no_permintaan . "','RFQVER','" . $detail . "','" . $valkar . "','0','0','" . date('Y-m-d H:i:s') . "')";
					$owlPDO->exec($strq);
				}
				$owlPDO->commit();
			} catch (PDOException $e) {
				$owlPDO->rollback();
				echo "Warning \n" . addslashes($e->getMessage());
			}
			break;

		case 'loadSuppier':
			$no = 0;
			$tabl = '';
			$sData = "select a.nomor,a.supplierid,a.nourut,b.alamat,b.kota,a.keterangan,a.lokasikirim,a.statuskirim from " . $dbname . ".log_perintaanhargaht a 
			left join " . $dbname . ".log_5supalamat b on a.id_alamat_supplier = b.id_alamat
			where a.nomor='" . $_POST['notrans'] . "' 
			order by a.nomor asc";
			$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while ($rData = $qData->fetch()) {
				$no++;
				$sNmsup = "select distinct namasupplier from " . $dbname . ".log_5supplier where supplierid='" . $rData['supplierid'] . "'";
				$qNmsup = $owlPDO->query($sNmsup) or die(print " Gagal: " . PDOException::getMessage());
				$qNmsup->setFetchMode(PDO::FETCH_ASSOC);
				$rNmsup = $qNmsup->fetch();
				$tabl .= "<tr class=rowcontent>";
				$tabl .= "<td align=center>" . $no . "</td>";
				$tabl .= "<td>" . $rData['nomor'] . "</td>";
				$tabl .= "<td>" . $rNmsup['namasupplier'] . "</td>";
				$tabl .= "<td style='display:none'>" . $rData['alamat'] . " " . $rData['kota'] . "</td>";
				$tabl .= "<td>" . $rData['keterangan'] . "</td>";
				$tabl .= "<td>" . $rData['lokasikirim'] . "</td>";
				$tabl .= "<td>
				<table cellpadding=2>";
				$strx = "select * from " . $dbname . ".log_perintaanhargafile where nomor='" . $rData['nomor'] . "' and supplierid='" . $rData['supplierid'] . "' group by kodebarang, nopp";
				$resx = fetchdata($strx);
				$nox = 0;
				foreach ($resx as $valx) {
					$nox++;
					$tabl .= "<tr>
							<td style='text-align:center'>" . $nox . "</td>
							<td style='text-align:center;color:blue;cursor:pointer' onclick=\"viewdetailupload('" . $rData['nomor'] . "','" . $rData['supplierid'] . "','" . $valx['kodebarang'] . "','" . $valx['nopp'] . "',event)\">" . $valx['kodebarang'] . " " . $valx['nopp'] . "</td>
						</tr>";
				}
				$tabl .= "</table>
			</td>";
				$tabl .= "<td align=center>";
				if ($rData['statuskirim'] == '0') {
					$tabl .= "<img src=images/application/application_delete.png class=resicon id='sttkrmd_" . $rData['nomor'] . "_" . $rData['nourut'] . "' title='Delete' onclick=\"delPer('" . $rData['nomor'] . "','" . $rData['nourut'] . "');\">&nbsp;
					<img style='display:none' src=images/upload-2-xxl.png class=resicon id='sttkrmk_" . $rData['nomor'] . "_" . $rData['nourut'] . "' title='Kirim ke Supplier' onclick=\"kirimPer('" . $rData['nomor'] . "','" . $rData['nourut'] . "');\">";
				}
				$tabl .= "</td>
			</tr>";
				$tab .= "asd";
			}
			echo $tabl;
			break;

		case 'deletedsup':
			$_SESSION['bgimage'] = array();
			$str = "select * from " . $dbname . ".log_perintaanhargaht where nomor='" . $_POST['no_permintaan'] . "'";
			$res = fetchData($str);
			if (count($res) <= 1) {
				$str = "select * from " . $dbname . ".log_permintaanhargadt where nomor='" . $_POST['no_permintaan'] . "'";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$i = "update " . $dbname . ".log_listverifikasi set status='0' where nopp='" . $bar['nopp'] . "' and kodebarang='" . $bar['kodebarang'] . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
					try {
						$owlPDO->exec($i);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						die();
					}
				}
			}

			$strx = "delete from " . $dbname . ".log_perintaanhargaht where nomor='" . $_POST['no_permintaan'] . "' and nourut='" . $_POST['nourut'] . "'";
			try {
				$owlPDO->exec($strx);
				$i = "delete from " . $dbname . ".log_permintaanhargadt where nomor='" . $_POST['no_permintaan'] . "' and nourut='" . $_POST['nourut'] . "'";
				try {
					$owlPDO->exec($i);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
			break;

		case 'sendemailper':
			/*$str="update ".$dbname.".log_perintaanhargaht set statuskirim='1' where nomor='".$_POST['no_permintaan']."' and nourut='".$_POST['nourut']."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}*/
			break;
		case 'kirimPer':
			$no_permintaan = $_POST['no_permintaan'];
			$no_ = str_replace("/", "_", $no_permintaan);
			$expno = explode('/', $no_permintaan);
			$nourut = $_POST['nourut'];

			##GET DETAIL PT
			$str = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $expno[4] . "'";
			$res = fetchdata($str);
			$namaorg = $res[0]['namaorganisasi'];
			$alamat = $res[0]['alamat'];
			$telepon = $res[0]['telepon'];

			$str = "select supplierid,lokasikirim,keterangan from " . $dbname . ".log_perintaanhargaht where nomor='" . $no_permintaan . "' and nourut='" . $nourut . "'";
			$res = fetchdata($str);
			$supplierid = $res[0]['supplierid'];
			$lokasikirim = $res[0]['lokasikirim'];
			$keterangan = $res[0]['keterangan'];

			$optnmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $supplierid . "'");
			$optemailsup = makeOption($dbname, 'log_5supuser', 'id_supplier,email', "id_supplier='" . $supplierid . "'");
			$nmsup = $optnmsup[$supplierid];
			$emailsup = $optemailsup[$supplierid];

			## CREATE CSV
			$path = 'fileupload/permintaanharga/';
			$headerFirst = ['KODE', 'NAMA', 'SPESIFIKASI', 'JUMLAH', 'HARGA'];
			$headerSecond = ['ATTRIBUTE'];
			$file = "";
			$filepath = "";
			$attriute = ['Diskon (Rp)', 'PBBKB', 'Durasi Pengiriman', 'Durasi Pekerjaan', 'Garansi Produk/Jasa', 'Posisi Stok Barang', 'Asuransi', 'Keterangan'];
			$str = "select a.*,b.namabarang from " . $dbname . ".log_permintaanhargadt a
		left join " . $dbname . ".log_5masterbarang b on a.kodebarang = b.kodebarang where a.nomor='" . $no_permintaan . "' and a.nourut='" . $nourut . "'";
			$result = fetchdata($str);
			if (count($result) > 0) {
				if (!file_exists($path)) {
					mkdir($path, 0777, true);
				}
				$file = 'PENAWARAN-' . $no_ . '-' . $nmsup . '.csv';
				$filepath = $path . $file;
				$content = implode(",", $headerFirst) . "\n";
				foreach ($result as $res) {
					$newR = [$res['kodebarang'], $res['namabarang'], '', '', '']; //$res['namabarang'],$res['jumlah'],$res['harga']];
					$content .= implode(",", $newR) . "\n";
				}
				$content .= implode(",", $headerSecond) . "\n";
				foreach ($attriute as $v) {
					$newR2 = [$v, ''];
					$content .= implode(",", $newR2) . "\n";
				}
				file_put_contents($filepath, $content);
			}
			$data['nomor'] = $no_permintaan;
			$data['nourut'] = $nourut;
			$data['namefile'] = $file;
			$data['src'] = $filepath;

			## GET FILE RPH
			$str = "select namafile from " . $dbname . ".log_perintaanhargafile where nomor='" . $no_permintaan . "' and supplierid='" . $supplierid . "'";
			$filerph = fetchdata($str);

			### CREATE ZIP
			$zip = new ZipArchive;
			if ($zip->open("fileupload/zip/" . str_replace('.csv', '', $file) . ".zip", ZipArchive::CREATE) === TRUE) {
				$zip->addFile($filepath, $file);

				if (count($filerph) > 0) {
					foreach ($filerph as $val) {
						$zip->addFile($val['namafile'], str_replace('fileupload/pp/', '', $val['namafile']));
					}
				}

				$zip->close();
			}
			$subject = "Request For Quotation";
			$body .= "Dear " . $nmsup . ",<br><br>
		Mohon dikirimkan penawaran harga yang terkait dengan RFQ, dengan kondisi sebagai berikut:<br>
		1. Harga unit/barang<br>
		2. Ketersediaan unit/barang<br>
		3. Durasi pengiriman unit/barang<br>
		4. Garansi unit/barang<br>
		5. Sistem pembayaran<br><br>
		Catatan: Jika ada informasi tambahan diluar 5 point tersebut silahkan ditambahkan<br><br>
		
		<b>Procurement Department<br>
		" . $namaorg . "<br>
		" . $alamat . "<br>
		" . $telepon . "</b><br>";

			// kirimEmailatt($emailsup,$cc="",$subject,$body,$mailType='text/html','fileupload/zip/'.str_replace('.csv','',$file).'.zip');
			// kirimEmailatt('simamora.hendry@gmail.com',$cc="",$subject,$body,$mailType='text/html','fileupload/zip/'.str_replace('.csv','',$file).'.zip');
			kirimEmailatt($emailsup, $cc = "", $subject, $body, $mailType = 'text/html', 'fileupload/zip/' . str_replace('.csv', '', $file) . '.zip');

			$str = "update " . $dbname . ".log_perintaanhargaht set statuskirim='1' where nomor='" . $no_permintaan . "' and nourut='" . $nourut . "'";
			try {
				$owlPDO->exec($str);

				unlink($filepath);
				unlink("fileupload/zip/" . str_replace('.csv', '', $file) . ".zip");
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
			// echo json_encode($data);			
			break;




















































































































		case 'getSupplierNm':

			if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
				$sortkode = "and kodekelompok in ('S001','S002','S004')";
			} else {
				$sortkode = "and kodekelompok in ('S004','S001')";
			}

			echo "<fieldset><legend>" . $_SESSION['lang']['result'] . "</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 class=sortable>
                        <thead>
                        <tr class=rowheader>
                        <td align=center>No.</td>
                        <td align=center>" . $_SESSION['lang']['kodesupplier'] . "</td>
                        <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
                        </tr><tbody>
                        ";
			$no = 0;
			$sSupplier = "select namasupplier,supplierid from " . $dbname . ".log_5supplier"
				. " where supplierid in (select supplierid from " . $dbname . ".log_5supkelompok where tipe='SUPPLIER')"
				. " and namasupplier like '%" . $nmSupplier . "%' and status=1 ";
			$qSupplier = $owlPDO->query($sSupplier) or die(print " Gagal: " . PDOException::getMessage());
			$qSupplier->setFetchMode(PDO::FETCH_ASSOC);
			while ($rSupplier = $qSupplier->fetch()) {
				$no += 1;
				echo "<tr class=rowcontent style=cursor:pointer onclick=setData('" . $rSupplier['supplierid'] . "')>
                         <td align=center>" . $no . "</td>
                         <td>" . $rSupplier['supplierid'] . "</td>
                         <td>" . $rSupplier['namasupplier'] . "</td>
                    </tr>";
			}
			echo "</tbody></table></div>";
			break;




		case 'getNopp':
			echo "<fieldset><legend>" . $_SESSION['lang']['result'] . "</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 cellspacing=1 class=sortbale>
                        <thead>
                        <tr class=rowheader>
                        <td>No.</td>
                        <td>" . $_SESSION['lang']['nopp'] . "</td>
                        
                        </tr><tbody>
                        ";
			//$sSupplier="select a.nopp  from ".$dbname.".log_prapoht a left join ".$dbname.".log_podt b on a.nopp=b.nopp where a.nopp like '%".$kdNopp."%' and close='2' and b.nopo is null";
			$sSupplier = "select distinct nopp from " . $dbname . ".log_prapodt where nopp like '%" . $kdNopp . "%' and create_po='0'";
			$qSupplier = $owlPDO->query($sSupplier) or die(print " Gagal: " . PDOException::getMessage());
			$qSupplier->setFetchMode(PDO::FETCH_ASSOC);
			while ($rSupplier = $qSupplier->fetch()) {

				$no += 1;
				echo "<tr class=rowcontent onclick=setDataNopp('" . $rSupplier['nopp'] . "')>
                         <td>" . $no . "</td>
                         <td>" . $rSupplier['nopp'] . "</td>
                         
                    </tr>";
			}
			echo "</tbody></table></div>";
			break;
		case 'getNopp2':
			if (strlen($kdNopp) < 5) {
				exit("error: Min 4 character");
			}
			echo "<fieldset><legend>" . $_SESSION['lang']['result'] . "</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 cellspacing=1 class=sortbale>
                        <thead>
                        <tr class=rowheader>
                        <td>No.</td>
                        <td>" . $_SESSION['lang']['nopp'] . "</td>
                        
                        </tr><tbody>
                        ";
			//$sSupplier="select a.nopp  from ".$dbname.".log_prapoht a left join ".$dbname.".log_podt b on a.nopp=b.nopp where a.nopp like '%".$kdNopp."%' and close='2' and b.nopo is null";
			$sSupplier = "select distinct nopp from " . $dbname . ".log_perintaanhargaht where nopp like '%" . $kdNopp . "%'";
			$qSupplier = $owlPDO->query($sSupplier) or die(print " Gagal: " . PDOException::getMessage());
			$qSupplier->setFetchMode(PDO::FETCH_ASSOC);

			while ($rSupplier = $qSupplier->fetch()) {
				$no += 1;
				echo "<tr class=rowcontent onclick=setDataNopp('" . $rSupplier['nopp'] . "')>
                         <td>" . $no . "</td>
                         <td>" . $rSupplier['nopp'] . "</td>
                         
                    </tr>";
			}
			echo "</tbody></table></div>";
			break;












		case 'loadSuppier':
			$no = 0;
			$tabl = '';
			$sData = "select a.nomor,a.supplierid,a.nourut,b.alamat,b.kota from " . $dbname . ".log_perintaanhargaht a
				left join " . $dbname . ".log_5supalamat b on a.id_alamat_supplier = b.id_alamat
                 where a.nomor='" . $_POST['notrans'] . "'
                 order by a.nomor asc";
			$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while ($rData = $qData->fetch()) {
				$no++;
				$sNmsup = "select distinct namasupplier from " . $dbname . ".log_5supplier where supplierid='" . $rData['supplierid'] . "'";


				$qNmsup = $owlPDO->query($sNmsup) or die(print " Gagal: " . PDOException::getMessage());
				$qNmsup->setFetchMode(PDO::FETCH_ASSOC);
				$rNmsup = $qNmsup->fetch();
				$tabl .= "<tr class=rowcontent>";
				$tabl .= "<td align=center>" . $no . "</td>";
				$tabl .= "<td>" . $rData['nomor'] . "</td>";
				$tabl .= "<td>" . $rNmsup['namasupplier'] . "</td>";
				$tabl .= "<td>" . $rData['alamat'] . " " . $rData['kota'] . "</td>";
				$tabl .= "<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPer('" . $rData['nomor'] . "','" . $rData['nourut'] . "');\"></td>";
				$tabl .= "</tr>";
				$tab .= "asd";
			}

			echo $tabl;
			break;







		//<input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />


		case 'get_nopp':
			$optNopp = '';
			$sql = "SELECT a.nopp FROM " . $dbname . ".`log_prapodt` a left join " . $dbname . ".`log_prapoht` b on a.nopp=b.nopp where b.close='2' 
                    and (a.create_po is null or create_po='') 
                    and a.kodebarang='" . $kd_brg . "'"; //echo "warning".$sql;

			$query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_ASSOC);
			while ($res = $query->fetch()) {

				$optNopp .= "<option value=" . $res['nopp'] . ">" . $res['nopp'] . "</option>";
			}
			echo $optNopp;
			break;
		case 'getSpek':
			$sSpek = "select spesifikasi from " . $dbname . ".log_5photobarang where kodebarang='" . $kd_brg . "'";

			$qSpek = $owlPDO->query($sSpek) or die(print " Gagal: " . PDOException::getMessage());
			$qSpek->setFetchMode(PDO::FETCH_ASSOC);
			$rSpek = $qSpek->fetch();
			echo $rSpek['spesifikasi'];
			break;
		case 'getKurs':
			$tgl = date("Ymd");
			$sGet = "select distinct kurs from " . $dbname . ".setup_matauangrate where kode='" . $mtUang . "' and daritanggal='" . $tgl . "'";

			$qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
			$qGet->setFetchMode(PDO::FETCH_ASSOC);
			$rGet = $qGet->fetch();
			//echo "warning:".$rGet['kurs'];
			if ($mtUang == 'IDR') {
				$rGet['kurs'] = 1;
			} else {
				if ($rGet['kurs'] != 0) {
					$rGet['kurs'] = $rGet['kurs'];
				} else {
					$rGet['kurs'] = 1;
				}
			}
			echo $rGet['kurs'];
			break;


		case 'printExcel':

			$optTermPay = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			$optStock = $optTermPay;
			$optKrm = $optTermPay;
			$arrOptTerm = array("1" => "Cash", "2" => "Credit 2 weeks", "3" => "Credit 1 month", "4" => "Spesific Terms", "5" => "Down Payment");
			$arrStock = array("1" => "Ready Stock", "2" => "Not Ready");
			$arrFranco = makeOption($dbname, 'setup_franco', 'id_franco,franco_name');
			$arrOptTerm2 =  makeOption($dbname, 'log_5syaratbayar', 'kode,keterangan,jenis', '', 4);
			$sdtheder = "select distinct * from " . $dbname . ".log_perintaanhargaht where nomor='" . $_GET['no_permintaan'] . "'";

			$qdtheder = $owlPDO->query($sdtheder) or die(print " Gagal: " . PDOException::getMessage());
			$qdtheder->setFetchMode(PDO::FETCH_ASSOC);
			while ($rdtheder = $qdtheder->fetch()) {
				$dtNomor[] = $rdtheder['nourut'];
				$dtSupp[$rdtheder['nourut']] = $rdtheder['supplierid'];
				$dtFranco[$rdtheder['nourut']] = $rdtheder['id_franco'];
				$dtStock[$rdtheder['nourut']] = $rdtheder['stock'];
				$dtCattn[$rdtheder['nourut']] = $rdtheder['catatan'];
				$dtSisbyr[$rdtheder['nourut']] = $rdtheder['sisbayar'];
				$dtSisbyr2[$rdtheder['nourut']] = $rdtheder['sisbayar2'];
				$dtPpn[$rdtheder['nourut']] = $rdtheder['ppn'];
				$dtPph[$rdtheder['nourut']] = $rdtheder['pph'];
				$dtPph22[$rdtheder['nourut']] = $rdtheder['pph22'];
				$dtSbtotal[$rdtheder['nourut']] = $rdtheder['subtotal'];
				$dtDisknPrsn[$rdtheder['nourut']] = $rdtheder['diskonpersen'];
				$dtNildis[$rdtheder['nourut']] = $rdtheder['nilaidiskon'];
				$dtNilPer[$rdtheder['nourut']] = $rdtheder['nilaipermintaan'];
				$dtMtuang[$rdtheder['nourut']] = $rdtheder['matauang'];
				$dtTglDr[$rdtheder['nourut']] = $rdtheder['tgldari'];
				$dtTglSmp[$rdtheder['nourut']] = $rdtheder['tglsmp'];
				$kurs[$rdtheder['nourut']] = $rdtheder['kurs'];
				$dtCttn[$rdtheder['nourut']] = $rdtheder['catatan'];
			}


			$sDetail = "select distinct kodebarang,jumlah,nomor,harga,merk,nourut from " . $dbname . ".log_permintaanhargadt where nomor='" . $_GET['no_permintaan'] . "' ";
			$qDetail = $owlPDO->query($sDetail) or die(print " Gagal: " . PDOException::getMessage());
			$qDetail->setFetchMode(PDO::FETCH_ASSOC);
			while ($rDetail = $qDetail->fetch()) {

				if ($rDetail['harga'] == '') {
					$rDetail['harga'] = 0;
				}
				$dtSub[$rDetail['nourut']][$rDetail['kodebarang']] = floatval($rDetail['jumlah']) * floatval($rDetail['harga']);
				$dtHarga[$rDetail['nourut']][$rDetail['kodebarang']] = $rDetail['harga'];
				$dtMerk[$rDetail['nourut']][$rDetail['kodebarang']] = $rDetail['merk'];
				$arrJmlh[$rDetail['kodebarang']] = $rDetail['jumlah'];
				$listBarang[$rDetail['kodebarang']] = $rDetail['kodebarang'];
			}


			$tab = "<table cellspacing=1 border=1 class=sortable >
                <thead class=rowheader>
                <tr>
                <td bgcolor=#DEDEDE rowspan=2 align=center>No.</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>" . $_SESSION['lang']['jumlah'] . "</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>" . $_SESSION['lang']['satuan'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td bgcolor=#DEDEDE colspan=3 align=center>" . $optNmSup[$dtSupp[$ard]] . "</td>";
			}
			$tab .= "</tr><tr>";
			foreach ($dtNomor as $brs) {
				$tab .= "<td   bgcolor=#DEDEDE align=center width=85px>" . $_SESSION['lang']['spesifikasi'] . "</td><td  align=center width=85px bgcolor=#DEDEDE>" . $_SESSION['lang']['harga'] . "</td><td align=center width=85px bgcolor=#DEDEDE>" . $_SESSION['lang']['subtotal'] . "</td>";
			}
			$tab .= "<tr>";
			$tab .= "</thead>
                <tbody>";
			$totRow = count($dtNomor);
			$totBrg = count($listBarang);
			foreach ($listBarang as $brsKdBrg) {
				$no += 1;
				$tab .= "<tr class='rowcontent'>";
				$tab .= "<td>" . $no . "</td>";
				$tab .= "<td id='kd_brg_" . $no . "'>" . $brsKdBrg . "</td>";
				$tab .= "<td title='" . $arrNmBrg[$brsKdBrg] . "'>" . $arrNmBrg[$brsKdBrg] . "</td>";
				$tab .= "<td align=right id='jumlah_" . $no . "'>" . $arrJmlh[$brsKdBrg] . "</td>";
				$tab .= "<td align=center>" . $optSat[$brsKdBrg] . "</td>";
				$ard = 0;
				foreach ($dtNomor as $brs) {
					$ard += 1;
					$tab .= "<td align=left>" . $dtMerk[$ard][$brsKdBrg] . "</td>";
					$tab .= "<td align=right>" . number_format($dtHarga[$ard][$brsKdBrg], 2) . "</td>";
					$tab .= "<td align=right>" . number_format($dtSub[$ard][$brsKdBrg], 2) . "</td>";
				}
				$tab .= "</tr>";
			}
			$tab .= "<tr class='rowcontent'>";

			$tab .= "<td rowspan=4 colspan=3 valign=top align=left>&nbsp</td><td colspan=2>" . $_SESSION['lang']['subtotal'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td align=right colspan=3 id=total_harga_po_" . $ard . ">" . number_format($dtSbtotal[$ard], 2) . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['diskon'] . "</td>";
			foreach ($dtNomor as $brs) {
				$nor += 1;
				$tab .= "<td align=right colspan=2>" . $dtDisknPrsn[$nor] . "%</td>";
				$tab .= "<td align=right>" . number_format($dtNildis[$nor], 2) . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['ppn'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				@$persen[$ard] = ($dtPpn[$ard] / ($dtSbtotal[$ard] - $dtNildis[$ard])) * 100;
				$tab .= "<td align=right colspan=2>" . $persen[$ard] . "</td>";
				$tab .= "<td align=right >" . number_format($dtPPN[$ard], 2) . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['grnd_total'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td align=right colspan=3 id=grand_total_" . $ard . ">" . number_format($dtNilPer[$ard], 2) . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td rowspan=10 colspan=3 valign=top align=left>" . $_SESSION['lang']['rekomendasi'] . "</td>";
			$tab .= "<td colspan=2>" . $_SESSION['lang']['nopermintaan'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td colspan=3>" . $_GET['no_permintaan'] . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['matauang'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td colspan=3>" . $dtMtuang[$ard] . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['kurs'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td colspan=3>" . $kurs[$ard] . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['tgldari'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td colspan=3>" . $dtTglDr[$ard] . "</td>";
			}

			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['tglsmp'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td colspan=3>" . $dtTglSmp[$ard] . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['syaratPem'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				if ($dtSisbyr[$ard] != '0') {
					$hasilSyaratBayar = $arrOptTerm[$dtSisbyr[$ard]];
				} else {
					if ($dtSisbyr2[$ard] != '') {
						$hasilSyaratBayar = $arrOptTerm2[$dtSisbyr2[$ard]];;
					} else {
						$hasilSyaratBayar = "";
					}
				}
				$tab .= "<td colspan=3>" . $hasilSyaratBayar . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['stock'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td colspan=3>" . $arrStock[$dtStock[$ard]] . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['almt_kirim'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td colspan=3>" . $arrFranco[$dtFranco[$ard]] . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'><td colspan=2>" . $_SESSION['lang']['keterangan'] . "</td>";
			$ard = 0;
			foreach ($dtNomor as $brs) {
				$ard += 1;
				$tab .= "<td align=justify colspan=3>" . $dtCttn[$ard] . "</td>";
			}
			$tab .= "</tr>";
			$tab .= "<tr class=rowcontent><td colspan=2></td>";
			$ard = 0;
			$tab .= "<td align=center colspan=3></td>";
			$tab .= "</tr>";


			$tab .= "</tbody></table>";
			$nop_ = "form_permintaan_harga";
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

			break;











		case 'postingData':

			$nomor = $_POST['nomor'];
			$nourut = $_POST['nourut'];
			$alasan = $_POST['alasan'];


			$str = "update " . $dbname . ".log_perintaanhargaht set flag=1,catatanmenang='" . $alasan . "' "
				. "where nomor='" . $nomor . "' and nourut='" . $nourut . "' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}

			break;




		case 'flagdt':
			$nodph = checkPostGet('nodph', '');
			$nourutdph = checkPostGet('nourutdph', '');
			$kdbrg = checkPostGet('kdbrg', '');
			$ckbrg = checkPostGet('ckbrg', '');

			$str = "update " . $dbname . ".log_permintaanhargadt set flag='" . $ckbrg . "' 
			where nomor='" . $nodph . "' and nourut='" . $nourutdph . "' and kodebarang='" . $kdbrg . "' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}


			break;

		case 'getalamat';
			$optalamat = "";
			$str = "select * from " . $dbname . ".log_5supalamat where supplierid = '" . $supplier_id . "' and status='1' order by alamat desc";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optalamat .= "<option value='" . $bar['id_alamat'] . "'>" . $bar['alamat'] . " " . $bar['kota'] . "</option>";
			}
			echo $optalamat;
			break;

		case 'printExcelComparison':
			$notransaksi = checkPostGet('no_permintaan', '');

			## HEADER
			$str = "select * from " . $dbname . ".log_perintaanhargaht where nomor='" . $notransaksi . "' order by nourut asc";
			$res = fetchdata($str);
			foreach ($res as $val) {
				$optnmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $val['supplierid'] . "'");
				$arridsup[$val['supplierid']] = $val['supplierid'];
				$arrurut[$val['nourut']] = $val['supplierid'];
				$arrnamasup[$val['supplierid']] = $optnmsup[$val['supplierid']];

				$arrnilais[$val['supplierid']]['nilai1s'] = $val['nilai1s'];
				$arrnilais[$val['supplierid']]['nilai2s'] = $val['nilai2s'];
				$arrnilais[$val['supplierid']]['nilai3s'] = $val['nilai3s'];
				$arrnilais[$val['supplierid']]['nilai4s'] = $val['nilai4s'];
				$arrnilais[$val['supplierid']]['nilai5s'] = $val['nilai5s'];
				$nilai1f = $val['nilai1f'];
				$nilai2f = $val['nilai2f'];
				$nilai3f = $val['nilai3f'];
				$nilai4f = $val['nilai4f'];
				$nilai5f = $val['nilai5f'];
			}

			## DETAIL
			$str = "select * from " . $dbname . ".log_permintaanhargadt where nomor='" . $notransaksi . "'";
			$res = fetchdata($str);
			foreach ($res as $val) {
				$expnopp = explode('/', $val['nopp']);
				$strx = "select hargasatuan,tanggal from " . $dbname . ".log_5hargaterakhir where kodebarang='" . $val['kodebarang'] . "' and unit='" . $expnopp[4] . "'";
				$resx = fetchdata($strx);
				$hargaterakhir = ($resx[0]['hargasatuan'] == '' ? '-' : $resx[0]['hargasatuan']);
				$hargaterakhirtgl = ($resx[0]['tanggal'] == '' ? '-' : $resx[0]['tanggal']);

				$strx = "select namabarang,satuan from " . $dbname . ".log_5masterbarang where kodebarang='" . $val['kodebarang'] . "'";
				$resx = fetchdata($strx);

				$arrkodebarang[$val['kodebarang']] = $val['kodebarang'];
				$arrdtbarang[$val['kodebarang']]['namabarang'] = $resx[0]['namabarang'];
				$arrdtbarang[$val['kodebarang']]['qty'] = $val['jumlah'];
				$arrdtbarang[$val['kodebarang']]['lastprice'] = $hargaterakhir;
				$arrdtbarang[$val['kodebarang']]['lastpricetgl'] = $hargaterakhirtgl;
				$arrdtbarang[$val['kodebarang']]['factor'] = $val['factor'];
				$arrdtbarang[$val['kodebarang']]['satuan'] = $resx[0]['satuan'];
				$arrbarang[$arrurut[$val['nourut']]][$val['kodebarang']]['harga'] = $val['harga'];
				$arrbarang[$arrurut[$val['nourut']]][$val['kodebarang']]['score'] = $val['score'];
				$arrbarang[$arrurut[$val['nourut']]][$val['kodebarang']]['total'] = $val['harga'] * $val['jumlah'];

				$strx = "select satuankonversi from " . $dbname . ".log_prapodt where nopp='" . $val['nopp'] . "' and kodebarang='" . $val['kodebarang'] . "'";
				$resx = fetchdata($strx);
				if ($resx[0]['satuankonversi'] == '' || is_null($resx[0]['satuankonversi'])) {
				} else {
					$arrdtbarang[$val['kodebarang']]['satuan'] = $resx[0]['satuankonversi'];
				}
			}

			$countsup = count($arridsup);

			$tab = "";
			$tab .= "<table border=1>
			<tr style='text-align:center;font-weight:bold'>
				<td rowspan=2>Evaluation Parameter</td>
				<td rowspan=2>Description</td>
				<td rowspan=2>Unit</td>
				<td rowspan=2>Uom</td>
				<td colspan='" . $countsup . "'>Summary of Information</td>
				<td rowspan=2>LAST PRICE</td>
				<td colspan='" . $countsup . "'>Score (1 - 5, 5 for the Best)</td>
				<td rowspan=2>Weighted Factor</td>
				<td colspan='" . $countsup . "'>Weighted Score</td>
			</tr>
			<tr style='text-align:center;font-weight:bold'>";
			foreach ($arrnamasup as $val) {
				$tab .= "<td>" . $val . "</td>";
			}
			foreach ($arrnamasup as $val) {
				$tab .= "<td>" . $val . "</td>";
			}
			foreach ($arrnamasup as $val) {
				$tab .= "<td>" . $val . "</td>";
			}
			$tab .= "<tr>";

			## PRICE
			$tab .= "<tr>
			<td colspan='" . (6 + (3 * $countsup)) . "'><b>I. Price :</b></td>
		</tr>";
			$no = 0;
			$arrsubhasil = array();
			$arrhasil = array();
			foreach ($arrkodebarang as $val) {
				$no++;
				$tab .= "<tr  style='text-align:center'>
				<td>" . $no . "</td>
				<td style='text-align:left'>" . $arrdtbarang[$val]['namabarang'] . "</td>
				<td>" . hidezerodecimal($arrdtbarang[$val]['qty'], 2) . "</td>
				<td>" . $arrdtbarang[$val]['satuan'] . "</td>";
				foreach ($arridsup as $valx) {
					$tab .= "<td style='text-align:center'>" . number_format($arrbarang[$valx][$val]['harga']) . "</td>";
				}
				$tab .= "<td>" . hidezerodecimal($arrdtbarang[$val]['lastprice'], 2) . "</td>";
				foreach ($arridsup as $valx) {
					$tab .= "<td style='text-align:center'>" . $arrnilais[$valx]['nilai1s'] . "</td>";
				}
				$tab .= "<td>" . hidezerodecimal($nilai1f, 2) . "</td>";
				foreach ($arridsup as $valx) {
					$hasil = $arrnilais[$valx]['nilai1s'] * ($nilai1f / 100);
					$tab .= "<td style='text-align:center'>" . $hasil . "</td>";
					$arrsubhasil[$valx] = $hasil;
				}
				$tab .= "</tr>";
				$tab .= "<tr  style='text-align:center'>
				<td colspan=4></td>";
				foreach ($arridsup as $valx) {
					$tab .= "<td style='text-align:center;font-weight:bold'>" . number_format($arrbarang[$valx][$val]['total']) . "</td>";
				}
				$tab .= "<td>" . ($arrdtbarang[$val]['lastpricetgl'] == '-' ? '-' : tanggalnormal($arrdtbarang[$val]['lastpricetgl'])) . "</td>
				<td colspan='" . (1 + (2 * $countsup)) . "'>&nbsp;</td>";
				$tab .= "</tr>
			<tr><td colspan='" . (6 + (3 * $countsup)) . "'>&nbsp;</td></tr>";
			}

			$tab .= "<tr>
			<td colspan='" . (6 + (2 * $countsup)) . "' style='text-align:right'>Sub Total</td></td>";
			foreach ($arridsup as $val) {
				$tab .= "<td style='text-align:center;font-weight:bold'>" . hidezerodecimal($arrsubhasil[$val], 2) . "</td>";
			}
			$tab .= "</tr>";



			## Availability
			$tab .= "<tr>
			<td colspan='" . (5 + ($countsup)) . "'><b>II. Availability :</b></td>";
			foreach ($arridsup as $val) {
				$tab .= "<td style='text-align:center'>" . $arrnilais[$val]['nilai2s'] . "</td>";
			}
			$tab .= "<td style='text-align:center'>" . $nilai2f . "</td>";
			foreach ($arridsup as $val) {
				$hasil = $arrnilais[$val]['nilai2s'] * ($nilai2f / 100);
				$tab .= "<td style='text-align:center'>" . hidezerodecimal($hasil, 2) . "</td>";
				$arrhasil[$val] += $hasil;
			}
			$tab .= "</tr>";

			## Quality/ Performance/ Integrity
			$tab .= "<tr>
			<td colspan='" . (5 + ($countsup)) . "'><b>III. Quality/ Performance/ Integrity :</b></td>";
			foreach ($arridsup as $val) {
				$tab .= "<td style='text-align:center'>" . $arrnilais[$val]['nilai3s'] . "</td>";
			}
			$tab .= "<td style='text-align:center'>" . $nilai3f . "</td>";
			foreach ($arridsup as $val) {
				$hasil = $arrnilais[$val]['nilai3s'] * ($nilai3f / 100);
				$tab .= "<td style='text-align:center'>" . hidezerodecimal($hasil, 2) . "</td>";
				$arrhasil[$val] += $hasil;
			}
			$tab .= "</tr>";

			## Service
			$tab .= "<tr>
			<td colspan='" . (5 + ($countsup)) . "'><b>IV. Service :</b></td>";
			foreach ($arridsup as $val) {
				$tab .= "<td style='text-align:center'>" . $arrnilais[$val]['nilai4s'] . "</td>";
			}
			$tab .= "<td style='text-align:center'>" . $nilai4f . "</td>";
			foreach ($arridsup as $val) {
				$hasil = $arrnilais[$val]['nilai4s'] * ($nilai4f / 100);
				$tab .= "<td style='text-align:center'>" . hidezerodecimal($hasil, 2) . "</td>";
				$arrhasil[$val] += $hasil;
			}
			$tab .= "</tr>";

			## Other Concerns (payment scheme, etc.)
			$tab .= "<tr>
			<td colspan='" . (5 + ($countsup)) . "'><b>V. Other Concerns (payment scheme, etc.) :</b></td>";
			foreach ($arridsup as $val) {
				$tab .= "<td style='text-align:center'>" . $arrnilais[$val]['nilai5s'] . "</td>";
			}
			$tab .= "<td style='text-align:center'>" . $nilai5f . "</td>";
			foreach ($arridsup as $val) {
				$hasil = $arrnilais[$val]['nilai5s'] * ($nilai5f / 100);
				$tab .= "<td style='text-align:center'>" . hidezerodecimal($hasil, 2) . "</td>";
				$arrhasil[$val] += $hasil;
			}
			$tab .= "</tr>";

			$arrwin = array();
			$tab .= "<tr>
			<td colspan='" . (5 + ($countsup * 2)) . "'></td>
			<td></td>";
			foreach ($arridsup as $val) {
				$hasil = $arrsubhasil[$val] + $arrhasil[$val];
				$tab .= "<td style='text-align:center;font-weight:bold'>" . hidezerodecimal($hasil, 2) . "</td>";
				$arrwin[$val] = $hasil;
			}
			$tab .= "</tr>";

			## PEMENANG TENDER
			$tab .= "<tr><td colspan='" . (6 + (3 * $countsup)) . "'>&nbsp;</td></tr>";
			$no = 0;
			arsort($arrwin);
			foreach ($arrwin as $key => $val) {
				$no++;
				$tab .= "<tr style='font-weight:bold'>
				<td colspan='" . (6 + ($countsup * 2)) . "' style='text-align:right'>Rekomendasi Tender " . $no . "</td>
				<td colspan='" . $countsup . "'>" . $arrnamasup[$key] . "</td>
			</tr>";
			}

			$tab .= "</table>";

			$nop_ = "Form_RRQ_" . str_replace('/', '', $notransaksi);
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
					echo "<script language=javascript1.2>parent.window.alert('Can't convert to excel format');</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>window.location='tempExcel/" . $nop_ . ".xls';</script>";
				}
				fclose($handle);
			}
			break;

		default;
			break;
	}
}
