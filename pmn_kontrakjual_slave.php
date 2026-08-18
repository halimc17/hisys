<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once 'lib/terbilang.php';

use Dompdf\Dompdf;

require_once('dompdf/autoload.inc.php');

$param = (($_POST == array()) ? $_GET : $_POST);
$lokasiTugas = substr($_SESSION['empl']['lokasitugas'], 0, 4);
$txtSearch = checkPostGet('txtSearch', '');
$kurs = checkPostGet('kurs', '');
$ptSch = checkPostGet('ptSch', '');
$posisictr = checkPostGet('posisictr', '');
$daerahctr = checkPostGet('daerahctr', '');
$termbyr = checkPostGet('termbyr', '');
$urlefil = checkPostGet('urlefil', '0');

// $optOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
// $optBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
// $optCust=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmpt = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "tipe='PT'");
$nopp = checkPostGet('rnopp', '');
$namafile = checkPostGet('namafile', '');
$nmcustsomer = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$namabarangsales = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namasales', "kelompokbarang='400'");
$nmkomoditi = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kelompokbarang='400'");
$method = checkPostGet('method', '');
$table = 'pmn_kontrakjual';
$path   = "fileupload/kontrakjual/";
$optjns = array("PM" => "Pengiriman", "PK" => "Pemenuhan Kontrak", "UM" => "Uang Muka", "BA" => "Berita Acara Serah Terima");
// print_r($param);
// exit("Error:$method");

$arrBulan = array("01" => "I", "02" => "II", "03" => "III", "04" => "IV", "05" => "V", "06" => "VI", "07" => "VII", "08" => "VIII", "09" => "IX", "10" => "X", "11" => "XI", "12" => "XII");



$str = "select * from " . $dbname . ".pmn_5daerahkontrak";
$res = fetchdata($str);
foreach ($res as $bar) {
	$namakotakontrak[$bar['id']] = $bar['lokasi'];
}

$str = "select * from " . $dbname . ".setup_filesize where transaksi='pmn_kontrakjual'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$filesize = $bar['filesize'];
}

// switch($param['method']){
switch ($method) {


	case 'pdfpanjang':

		$getNPWPOrg = makeOption($dbname, "setup_org_npwp", 'kodeorg,npwp');
		$getalamat = makeOption($dbname, "organisasi", 'kodeorganisasi,alamat');
		$arrayppn = array('0'=>'Exclude','1'=>'Include');

		#= ambil data dari kontrakjual
		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $param['nokontrak'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodept = $bar['kodept'];
			$tanggalkontrak = $bar['tanggalkontrak'];
			$hargasatuan = $bar['hargasatuan'];
			$persenppn = $bar['persenppn']; // sudah mewakili berikat / tidak , jika ppn 0 => berikat; jika 10 maka tidak berikat
			$ffakontrak = $bar['ffa'];
			$dobikontrak = $bar['dobi'];
			$mdanikontrak = $bar['mdani'];
			$moistkontrak = $bar['moist'];
			$dirtkontrak = $bar['dirt'];
			$impuritieskontrak = $bar['grading'];
			$penandatangan = $bar['penandatangan'];
			$satuanbarang = $bar['satuan'];
			$matauang = $matauang[$bar['matauang']];
			$franco = $bar['franco'];
			$tipepenjualan = $bar['tipepenjualan'];
			$tglbayar = $bar['tglpembayarpertama'];
			$kodebarang = $bar['kodebarang'];
			$flagtglbayar = $bar['flagtglbayar'];
			$daerahkontrak = $bar['daerahkontrak'];
			$koderekanan = $bar['koderekanan'];
			$terbilang = $bar['terbilang'];
			$ppn = $bar['ppn'];
			$kuantitaskontrak = $bar['kuantitaskontrak'];
			[$persenDp, $persenPelunasan] = explode(":", $bar['kdtermin']);
			$defaultpersenppn = $bar['defaultpersenppn']; // secara default 10
		}

		$npwporg = $getNPWPOrg[$kodept];
		#= jabatan ttd
		$str = "select * from " . $dbname . ".pmn_5ttd where nama='" . $penandatangan . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jabatanpenandatangan = $bar['jabatan'];
		}
		$str = "select * from " . $dbname . ".log_5masterbarang where kodebarang='" . $kodebarang . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$inisial = $bar['inisial'];
		}
		$str = "select * from " . $dbname . ".pmn_5franco where id_franco='" . $franco . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tempatpenyerahan = $bar['franco_name'];
			$alamatpenyerahan = $bar['alamat'];
		}

		$str = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $koderekanan . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$namacustomer = $bar['namacustomer'];
			$penandatangancustomer = $bar['penandatangan'];
			$jabatancustomer = $bar['jabatan'];
			$npwpcustomer = $bar['npwp'];
			$alamatcustomer = $bar['alamat'];
		}


		$datattdcustomer = explode('/', $penandatangancustomer);
		if ($datattdcustomer[1] != '') {
			$penandatangancustomer = ucwords(strtolower($datattdcustomer[0])) . '/' . ucwords(strtolower($datattdcustomer[1]));
		} else {
			$penandatangancustomer = ucwords(strtolower($datattdcustomer[0]));
		}

		// images/logo/CAR.png
		$arrHead    = setheadreport('', $kodept);

		$logoPath = 'images/logo/CAR.png';



		$cellpadding = 1;
		$cellspacing = 1;
		$sizefont = '14';
		$tab = "<head>
			<meta charset=\"UTF-8\">
			<title>SURAT PERJANJIAN JUAL BELI</title>
			<style>
				body {
					font-family: Arial, sans-serif;
					font-size: 10pt;
					margin: 0px; /* Sesuaikan margin sesuai kebutuhan */
				}
				.header {
					text-align: center;
					font-size: 12pt;
					font-weight: bold;
					margin-bottom: 20px;
				}
				table {
					width: 100%;
					border-collapse: collapse;
					margin-bottom: 15px;
				}
				table td {
					padding: 4px 0;
					vertical-align: top;
				}
				.label {
					width: 150px; /* Lebar kolom label */
					font-weight: normal; /* Labels should not be bold unless specified */
				}
				.content {
					width: auto;
				}
				.quality-table td {
					padding: 2px 0;
				}
				.notes {
					margin-top:-30px;
					font-size: 9pt;
				}
				.signatures {
					margin-top:0px;
					width: 100%;
					display: table; /* Untuk tata letak dua kolom */
				}
				.signature-col {
					width: 90%;
					display: table-cell;
					text-align: left;
				}
				.signature-name {
					margin-top: 120px; /* Jarak untuk tanda tangan dan stempel */
					font-weight: bold;
					text-decoration: underline;
				}
				.signature-title {
					font-size: 9pt;
				}
			</style>
		</head>
		<body>


		<div style='text-align:left; padding-left:-60px;'><img src='" . $logoPath . "' style='height:100px;width:200px;'></div>

			<div class=\"header\">
				<u>SURAT PERJANJIAN JUAL BELI</u><br>
				" . $param['nokontrak'] . "
			</div>

			<table cellpadding=0 cellspacing=0 border='0'>
				<tr>
					<td class=\"label\">PENJUAL</td>
					<td style='width:5'>:</td>
					<td class=\"content\">" . getNamaOrg($kodept) . " </td>
				</tr>
				<tr>
					<td></td>
					<td style='width:5'></td>
					<td class=\"content\">NPWP " . $npwporg . "</td>
				</tr>
				<tr>
					<td></td>
					<td style='width:5'></td>
					<td class=\"content\">" . $getalamat[$kodept] . "</td>
				</tr>
				<tr>
					<td></td>
					<td style='width:5'></td>
					<td class=\"content\">UML ID PO1000008613</td>
				</tr>
				
				<tr>
					<td class=\"label\">PEMBELI</td>
					<td style='width:5'>:</td>
					<td class=\"content\">$namacustomer </td>
				</tr>
				<tr>
					<td></td>
					<td style='width:5'></td>
					<td class=\"content\">NPWP $npwpcustomer</td>
				</tr>
				<tr>
					<td></td>
					<td style='width:5'></td>
					<td class=\"content\">$alamatcustomer </td>
				</tr>
		
				<tr>
					<td class=\"label\">NAMA BARANG</td>
					<td style='width:5'>:</td>
					<td class=\"content\">" . getNamaBrg($kodebarang) . " (" . $inisial . ")</td>
				</tr>";

		$tab .= "<tr>
					<td class=\"label\">KUALITAS</td>
					<td style='width:5'>:</td>";

		$tab .= "<td class=\"content\">";
		$qualities = [];
		if (!empty($ffakontrak)) {
			$qualities[] = "FFA Maksimum " . $ffakontrak . "%";
		}
		if (!empty($mdanikontrak)) {
			$qualities[] = "M & I Maksimum " . $mdanikontrak . "%";
		}
		if (!empty($moistkontrak)) {
			$qualities[] = "Moisture Maksimum " . $moistkontrak . "%";
		}
		if (!empty($dobikontrak)) {
			$qualities[] = "DOBI Minimum " . $dobikontrak . "%";
		}
		if (!empty($dirtkontrak)) {
			$qualities[] = "Dirt Maksimum " . $dirtkontrak . "%";
		}
		if (!empty($impuritieskontrak)) {
			$qualities[] = "Grading " . $impuritieskontrak . "%";
		}
		$tab .= "" . implode("<br>", $qualities);

		$tab .= "</td>";
		$jlhharga = $kuantitaskontrak * $hargasatuan;
		$qtybilang = terbilang(number_format($kuantitaskontrak, 0), 3);

		$keteranganbayar = "";
		if ($flagtglbayar=='1') {
			$keteranganbayar .= "Pembayaran DP {$persenDp}% tanggal " . tglnmbln($tglbayar, 'I', 'long') . " <br>";
		} else{
			$keteranganbayar .= "Pembayaran DP {$persenDp}% maksimal 5 hari setelah SPJB disetujui. <br>";
		}

		if ($tipepenjualan == 'FRANCO') { 
			$ket_timbangan="Timbangan Pembeli " . $tempatpenyerahan . " " . $alamatpenyerahan . "";
		} else if($tipepenjualan == 'LOCO') {
			$ket_timbangan="Timbangan Penjual " . $tempatpenyerahan . " " . $alamatpenyerahan . "";
		}

		$cekppn = ($ppn==0)?'':''.$persenppn.' %';

		$tab .= "</tr>	
				<tr>
					<td class=\"label\">DASAR TIMBANGAN</td>
					<td style='width:5'>:</td>
					<td class=\"content\">$ket_timbangan</td>
				</tr>
				<tr>
					<td class=\"label\">KUANTITAS</td>
					<td style='width:5'>:</td>
					<td class=\"content\">" . number_format($kuantitaskontrak, 0, ',', '.') . " (" . $qtybilang . " Ribu) Kg. </td>
				</tr>
				<tr>
					<td class=\"label\">HARGA SATUAN</td>
					<td style='width:5'>:</td>
					<td class=\"content\">Rp." . number_format($hargasatuan, 2, ',', '.') . "/Kg (".$arrayppn[$ppn]." PPN ".$cekppn.") </td>
				</tr>

				<tr>
					<td class=\"label\">PENYERAHAN</td>
					<td style='width:5'></td>
					<td class=\"content\">Franco " . $tempatpenyerahan . " " . $alamatpenyerahan . " </td>
				</tr>
				<tr>
					<td class=\"label\">WAKTU PENYERAHAN</td>
					<td style='width:5'>:</td>
					<td class=\"content\">Setelah DP Dibayar Lunas </td>
				</tr>
				<tr>
					<td class=\"label\">PEMBAYARAN</td>
					<td style='width:5'>:</td>
					<td class=\"content\">
						<b> $keteranganbayar
						Pelunasan {$persenPelunasan}% setelah selesai pengiriman {$inisial} sesuai BAP dan 5 Hari </b>
						setelah Invoice dan Faktur Pajak Asli diterima. 
						Mohon pembayaran {$inisial} dapat ditransfer ke Rekening bank: 
						Bank Mandiri KC Jakarta Jatinegara Timur <br>
						No. Rekening 006-00-0971441-5 <br>
						Atas Nama PT. Candi Artha 
					</td>
				</tr>
				<tr>
					<td class=\"label\">JUMLAH HARGA</td>
					<td style='width:5'>:</td>
					<td class=\"content\"><b>Rp. " . number_format($jlhharga, 0, ',', '.') . ",- ($terbilang rupiah).</b></td>
				</tr>
			</table>";

			$str = "select * from " . $dbname . ".pmn_5catatan where nokontrak='" . $param['nokontrak'] . "' ";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$mdani1 = $bar['mdani1'];
				$mdani2 = $bar['mdani2'];
				$mdani3 = $bar['mdani3'];
				$mdani4 = $bar['mdani4'];
				$ffa1 = $bar['ffa1'];
				$ffa2 = $bar['ffa2'];
				$ffa3 = $bar['ffa3'];
				$ffa4 = $bar['ffa4'];
				$dobi1 = $bar['dobi1'];
				$dobi2 = $bar['dobi2'];
				$dobi3 = $bar['dobi3'];
				$dobi4 = $bar['dobi4'];
			}

			$note="";
			if($koderekanan=='SMG' || $koderekanan=='TND'){
				$note.="<li>Supplier menjamin <b>$namacustomer</b> bahwa <b>" . getNamaBrg($kodebarang) . "</b> yang dijual Supplier kepada <b>$namacustomer</b> adalah merupakan produk yang legal dan bukan berasal dari suatu perbuatan pidana. Apabila kemudian hari terbukti bahwa <b>" . getNamaBrg($kodebarang) . "</b> tersebut berasal dari suatu perbuatan pidana, maka hal tersebut menjadi tanggung jawab sepenuhnya Supplier dan dengan ini Supplier membebaskan <b>$namacustomer</b> dari tanggung jawab apapun.</li>";
			}
			$tab.="<div class=\"notes\">
				<p>Catatan: </p>
				<ol>";
			if ($kodept == 'PPP' || !in_array($kodebarang, ['40000010', '40000043'])) {
				$tab.="
					<li>Final Mutu berdasarkan hasil Analisa mutu Pembeli, klaim diberlakukan sebagai berikut: 
						<table class=\"quality-table\">
							<tr>
								<td>$mdani1</td>
								<td>$ffa1</td> 
								<td>$dobi1</td> 
							</tr>
							<tr>
								<td>$mdani2</td>
								<td>$ffa2</td> 
								<td>$dobi2</td> 
							</tr>
							<tr>
								<td>$mdani3</td>
								<td>$ffa3</td> 
								<td>$dobi3</td> 
							</tr>
							<tr>
								<td>$mdani4</td>
								<td>$ffa4</td> 
								<td>$dobi4</td> 
							</tr>
				 
						</table>
					</li>";
			}
			$tab.="
					<li>" . $inisial . " yang dijual adalah produksi Perusahaan kami yang berasal dari PKS PT. Candi Artha dan lokasi Dusun Batu Brajang RT 004 RW 002 Desa Tajau Pecah Kec. Batu Ampar Kab. Tanah Laut Kalimantan Selatan.
					</li>
					
					$note

				</ol>
			</div>

			<div style='text-align: left; margin-top: 30px;'>
				Jakarta, " . tglnmbln($tanggalkontrak, 'I', 'long') . " 
			</div>

			<div class='signatures'>
				<div class=\"signature-col\">
					PIHAK PEMBELI 
					<div class=\"signature-name\">$penandatangancustomer </div>
					<div class=\"signature-title\">$jabatancustomer </div>
				</div>
				<div class=\"signature-col\" style='padding-left: 100px;'>
					PIHAK PENJUAL 
					<div class=\"signature-name\"> " . getNamaKaryawan($penandatangan) . "</div>
					<div class=\"signature-title\"> $jabatanpenandatangan </div> 
				</div>
			</div>

		</body>
		";
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('legal', 'portrait');

		$dompdf->render();

		$font = $dompdf->getFontMetrics()->get_font("Times-Roman", "");

		// $dompdf->getCanvas()->page_text('525','50', "{PAGE_NUM} / {PAGE_COUNT} ".$a." ", $font, ($sizefont-4), array(0,0,0),0,0,0);


		// $dompdf->getCanvas()->page_text('75','800',$_SESSION['lang']['NoKontrak'].' : '.$param['nokontrak'], $font, ($sizefont-4), array(0,0,0));



		if ($urlefil == '0') {
			$dompdf->stream("Print_BAST_" . $nobast, array("Attachment" => 0));
		} else {
			file_put_contents($urlefil, $dompdf->output());
		}
		break;

	/*
	$x = 72;
        $y = 18;
        $text = "{PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->get_font("helvetica", "bold");
        $size = 6;
        $color = array(255,0,0);
        $word_space = 0.0;  //  default
        $char_space = 0.0;  //  default
        $angle = 0.0;   //  default
        $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
	*/







	case 'submitfile':

		// $filesize=1;

		#= jadikan try commit
		try {

			$owlPDO->beginTransaction();

			$tgl = date("YmdHis");
			$his = date("His");
			$nmTemp = str_replace('-', '', str_replace('/', '', $param['nokontrak']));

			if ($_FILES['file']['size'] > $filesize) {
				throw new PDOException("Ukuran File melebihi " . number_format($filezie / 1024) . " KB; ukuran file ini " . number_format($_FILES['file']['size'] / 1024, 2) . " Kb");
			}
			// print_r($_FILES);exit("Error:A");

			if ($param['fileupload'] != '') {
				if ($_FILES['file']['error'] == 0) {
					$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
					$filename = $param['kriteriaefil'] . "_" . $nmTemp . "_" . $his . "" . $filetype;
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
					if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.rar') || ($filetype == '.gz') || ($filetype == '.zip') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
						$str = "insert into " . $dbname . ".listfileupload values ('','" . $param['nokontrak'] . "','" . $filename . "','" . $filetype . "','" . $param['kriteriaefil'] . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path . $filename, $file_tmpname);
					} else {
						throw new PDOException("Format file upload tidak boleh " . $filetype);
					}
				}
			}

			if (!file_exists($path . $filename)) {
				throw new PDOException("File gagal diupload");
			}

			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan penyimpanan data \n" . addslashes($e->getMessage());
		}

		break;

	case 'deletefile':
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['nokontrak'] . "' and namafile='" . $param['namafile'] . "'";
		try {
			$owlPDO->exec($str);
			$pathx = $path . str_replace('/', '', $param['namafile']);
			unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'loadfiles':
		$form = '';
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['nokontrak'] . "' ";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form .= "<tr class=rowcontent >";
			$form .= "<td style='text-align:center'>" . $no . "</td>";
			$form .= "<td align='center'><img src=" . $icon . " class=zImgBtn></a></td>";
			$form .= "<td>" . getcriterianame($bar['kriteriaefil']) . "</td>";
			$form .= "<td><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download>" . $bar['namafile'] . "</td>";
			$form .= "<td><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a>&nbsp<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('" . $bar['notransaksi'] . "','" . $bar['namafile'] . "');\" ></td>";
			$form .= "<tr>";
		}
		echo $form;
		break;

	case 'viewlistfile':
		$param = $_POST;
		$form = "
		<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
				<th align='center'>" . $_SESSION['lang']['nourut'] . "</th>
				<th align='center'>File Type</th>
				<th align='center'>Kriteria</th>
				<th align='center'>Filename</th>
				<th align='center'>Action</th>
			</tr>
			</thead>
			
		";
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['nokontrak'] . "' ";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form .= "<tr class=rowcontent >";
			$form .= "<td style='text-align:center'>" . $no . "</td>";
			$form .= "<td align='center'><img src=" . $icon . " class=zImgBtn></a></td>";
			$form .= "<td>" . getcriterianame($bar['kriteriaefil']) . "</td>";
			$form .= "<td><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download>" . $bar['namafile'] . "</td>";
			$form .= "<td align=center><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a></td>";
			$form .= "</tr>";
		}
		$form .= "</table>
		";
		echo $form;
		break;

	case 'updatedt':
		#= update
		$str = "update " . $dbname . ".`pmn_kontrakjualdt_kontrakpanjang` set keterangan='" . $param['keterangan'] . "',updateby='" . $_SESSION['standard']['userid'] . "' where pasal='" . $param['pasal'] . "' and  kodebarang='" . $param['kodebarang'] . "' and nokontrak='" . $param['nokontrak'] . "'  ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
		}
		break;

	case 'simpandt':
		if ($param['pasal'] != '') {
			#=delete dlu
			$str = "delete from " . $dbname . ".`pmn_kontrakjualdt_kontrakpanjang` where pasal='" . $param['pasal'] . "' and  kodebarang='" . $param['kodebarang'] . "' and nokontrak='" . $param['nokontrak'] . "'  ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo "DB Error : " . $e->getMessage();
			}

			$str = " INSERT INTO " . $dbname . ".`pmn_kontrakjualdt_kontrakpanjang` (`nokontrak`,`kodebarang`, `pasal`, `keterangan`, `createdby`, `createtime`, `updateby`)  values ('" . $param['nokontrak'] . "','" . $param['kodebarang'] . "','" . $param['pasal'] . "','" . $param['keterangan'] . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo "DB Error : " . $e->getMessage();
			}
		}

		break;

	case 'deletedt':
		$str = "delete from " . $dbname . ".`pmn_kontrakjualdt_kontrakpanjang` where pasal='" . $param['pasal'] . "' and  kodebarang='" . $param['kodebarang'] . "' and nokontrak='" . $param['nokontrak'] . "'  ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
		}
		break;


	case 'datadetail':

		#= cek apakah detail sudah ada atau belum
		$str = "select count(*) as jumrow from " . $dbname . ".pmn_kontrakjualdt_kontrakpanjang  where nokontrak='" . $param['nokontrak'] . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			@$datatransaksi = $bar['jumrow'];
		}


		if ($datatransaksi == 0) {
			$str = "select * from " . $dbname . ".pmn_5kontrakpanjang where  kodebarang='" . $param['kodebarang'] . "' order by pasal asc ";
			// echo $str;
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$arrpasal[$bar['pasal']] = $bar['pasal'];
				$dtketerangan[$bar['pasal']] = $bar['keterangan'];
			}
		} else {
			$str = "select * from " . $dbname . ".pmn_kontrakjualdt_kontrakpanjang where nokontrak='" . $param['nokontrak'] . "' and kodebarang='" . $param['kodebarang'] . "' order by pasal asc ";
			// echo $str;
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$arrpasal[$bar['pasal']] = $bar['pasal'];
				$dtketerangan[$bar['pasal']] = $bar['keterangan'];
			}
		}

		$border = 'border=0';
		$no = 0;
		$stream .= "<table class=sortable cellspacing=1 " . $border . " width=100%>";
		// $stream.="<thead>";
		// 	$stream.="<tr class=rowheader>";		
		// 		$stream.="<th align=center 20%>Pasal</th>";
		// 		$stream.="<th align=center>".$_SESSION['lang']['keterangan']."</th>";
		// 		$stream.="<th align=center width=20% colspan=2>".$_SESSION['lang']['action']."</th>";
		// 	$stream.="</tr>";
		// 	$stream.="</thead>";
		$stream .= "<tbody>";
		if (isset($arrpasal)) {
			foreach ($arrpasal as $dtpasal) {
				$no++;
				$stream .= "<tr class=rowcontent id=row" . $no . ">";
				$stream .= "<td><input type=text disabled class=myinputtext id=pasal" . $no . " name=pasal maxlength=20 onkeypress=\"return tanpa_kutip(event)\" style=\"width:100px;\" value=" . $dtpasal . " /></td>";
				$stream .= "<td><textarea name=keterangan id=keterangan" . $no . " style=\"width:900px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20' >" . $dtketerangan[$dtpasal] . "</textarea></td>";

				if ($datatransaksi == 0) {
					// $stream.="<td><button class=mybutton onclick=simpandt('".$no."')>".$_SESSION['lang']['save']."</button></td>";
					$stream .= "<td></td>";
					$stream .= "<td></td>";
				} else {
					// $stream.="<td><button class=mybutton onclick=updatedt('".$no."')>".$_SESSION['lang']['save']."</button></td>";
					$stream .= "<td></td>";
					$stream .= "<td><button class=mybutton onclick=deletedt('" . $param['nokontrak'] . "','" . $param['kodebarang'] . "','" . $dtpasal . "')>" . $_SESSION['lang']['delete'] . "</button></td>";
				}

				$stream .= "</tr>";
			}
		}

		// if($datatransaksi>0){
		// 	$no++;
		// 	$stream.="<tr class=rowcontent>";	
		// 		$stream.="<td><input type=text class=myinputtext id=pasal".$no." name=pasal maxlength=20 onkeypress=\"return tanpa_kutip(event)\" style=\"width:100px;\"  /></td>";
		// 		$stream.="<td><textarea name=keterangan id=keterangan".$no."  style=\"width:900px;\"  onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>";
		// 		// $stream.="<td><button class=mybutton onclick=simpandt('".$no."')>".$_SESSION['lang']['save']."</button></td>";
		// 		$stream.="<td></td>";
		// 		$stream.="<td></td>";
		// 	$stream.="</tr>";
		// 	$stream.="<tr class=rowcontent ".$no.">";	
		// 		$stream.="<td colspan=4 align=center><button class=mybutton onclick=simpandtall('".$no."')>".$_SESSION['lang']['save']."</button></td>";
		// 	$stream.="</tr>";
		// }else{
		// 	$stream.="<tr class=rowcontent>";	
		// 		$stream.="<td colspan=4 align=center><button class=mybutton onclick=simpandtall('".$no."')>".$_SESSION['lang']['save']."</button></td>";


		// 	$stream.="</tr>";	

		// }

		$stream .= "</tbody>";
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td colspan=4 align=center><img src=images/pdf.jpg  style='cursor:pointer' title='Print Short Contract : " . $param['nokontrak'] . "' onclick=\"masterPDF('pmn_kontrakjual','" . $param['nokontrak'] . "','','pmn_kontakjual_pdf',event)\">&nbsp;&nbsp;&nbsp;<img src=images/pdf.jpg style='cursor:pointer'  caption='PDF'  title='Print Long Contract : " . $param['nokontrak'] . "' onclick=\"pdfpanjang('" . $param['nokontrak'] . "');\"></td>";
		$stream .= "</tr>";
		$stream .= "</table>";

		echo $stream;
		break;


	case 'loaddata':
		// exit('Error: Akses tidak diperkenankan');
		// $whrunit="and kodeorg in (".getOrgDetail(2).")";

		$where = '1=1';

		if ($param['tanggalselesaisch'] != '' and $param['tanggalmulaisch'] != '') {
			$where .= " and tanggalkontrak between '" . tanggalsystemn($param['tanggalmulaisch']) . "' and '" . tanggalsystemn($param['tanggalselesaisch']) . "'";
		}
		if ($param['nokontraksch'] != '') {
			$where .= " and nokontrak like '%" . $param['nokontraksch'] . "%'";
		}
		if ($param['kodeptsch'] != '') {
			$where .= " and kodept='" . $param['kodeptsch'] . "'";
		}
		if ($param['kodecustomersch'] != '') {
			$where .= " and koderekanan='" . $param['kodecustomersch'] . "'";
		}
		if ($param['produksch'] != '') {
			$where .= " and kodebarang='" . $param['produksch'] . "'";
		}

		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay = ($page * $limit);
		$colspan = 16;

		$offset = $page * $limit;
		$str = "select count(*) as jumrow from " . $dbname . "." . $table . " where " . $where . " $whrunit and kodebarang !='40000003' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$jlhbrs = $bar['jumrow'];
		}
		$no = 0;
		$no = $maxdisplay;
		$str = "select * from " . $dbname . "." . $table . "  where " . $where . " $whrunit and kodebarang !='40000003' order by tanggalkontrak desc,nokontrak desc limit " . $offset . "," . $limit . " ";

		$res = fetchdata($str);
		foreach ($res as $bar) {
			$no++;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td>" . $bar['nokontrak'] . "</td>";
			$tab .= "<td>" . $nmpt[$bar['kodept']] . "</td>";
			$tab .= "<td>" . $nmcustsomer[$bar['koderekanan']] . "</td>";
			$tab .= "<td align=center>" . tanggalnormal($bar['tanggalkontrak']) . "</td>";
			$tab .= "<td>" . $nmkomoditi[$bar['kodebarang']] . "</td>";
			$tab .= "<td align=center>" . tanggalnormal($bar['tanggalkirim']) . "</td>";
			$tab .= "<td align=center>" . $optjns[$bar['termbayar']] . "</td>";
			$tab .= "<td>" . $bar['tipepenjualan'] . "</td>";
			$tab .= "<td>" . getKary($bar['updateby']) . "</td>";
			$tab .= "<td style='text-align:center;vertical-align:middle'><label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('" . $bar['nokontrak'] . "',event)\">History Approval</label></td>";


			if ($bar['posting'] == 0 || $bar['posting'] == 3) {
				$tab .= "<td align=center> <img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $bar['nokontrak'] . "');\"> </td>";
				$tab .= "<td align=center> <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('" . $bar['nokontrak'] . "');\" > </td>";
				$tab .= "<td align=center></td>";

				$tab .= "<td align=center> <img src='images/skyblue/submit.jpg' class='zImgBtn' title='Ajukan' onclick='form_ajukan(`" . $bar['nokontrak'] . "`)'> </td>";
			} else if ($bar['posting'] == 9) {
				$tab .= "<td colspan=2></td>";
				$tab .= "<td align=center> <img src='images/icons/04/16/04.png' class='zImgBtn' height='30' title='On Progress Approval'> </td>";
				$tab .= "<td align=center></td>";

			} else if ($bar['posting'] == 2) {
				$tab .= "<td colspan=2></td>";
				$tab .= "<td align=center> <img src='images/icons/04/16/01.png' class='zImgBtn' height='30' title='Approval Rejected'> </td>";
				$tab .= "<td align=center></td>";

			} else {
				$tab .= "<td colspan=2></td>";
				$tab .= "<td align=center> <img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posted'> </td>";
								//     $tab .= "<td align=center></td>";

				// if ($bar['statusaddendum'] == 0 && $bar['close'] == 0 ) {
				//     $tab .= "<td align=center> <img src='images/icons/Basic_set_Png/address_64.png' class='zImgBtn' title='Addendum Kontrak' onclick='fillField(`" . $bar['nokontrak'] . "`)'></td>";
				// } else {
				//     $tab .= "<td align=center></td>";
				// }                
				// Query untuk cek apakah kontrak sudah ditarik di invoice dan sudah diposting
				$htg = (int)fetchData(selectQuery($dbname, "keu_penagihanht", "count(*) as htg", "nokontrak = '" . $bar['nokontrak'] . "' and posting='1'"))[0]['htg'];

				if ($htg > 0) {
					$tab .= ($bar['close'] == 0)? "<td align=center><img src='images/cadenas.png' class='zImgBtn' title='Close Kontrak' onclick='tutupkont(`" . $bar['nokontrak'] . "`)'></td>" : "<td style='text-align:center;vertical-align:middle'>1<label style='color:blue;cursor:pointer' onclick=\"gethistclose('" . $bar['nokontrak'] . "',event)\">Contract <br> Closed Manual</label></td>";
				} else {
					// $tab .= "<td align=center> </td>";
				}
			}
			$tab .= "<td align=center> <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"pdfpanjang('" . $bar['nokontrak'] . "','1')\"> </td>";
			$tab .= "</tr>";
		}

		## PAGING
		$footd .= createpaging($jlhbrs, $limit, $page, $colspan, 'loaddata', 'getpage');

		echo $tab . "####" . $footd;
		break;




	case 'LoadNew':
		if ($txtSearch != '') {
			$sort = " and nokontrak like '%" . $txtSearch . "%' ";
		}

		if ($ptSch != '') {
			$sort .= " and kodept like '%" . $ptSch . "%' ";
		}
		// exit("Error:$sort");

		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;

		@$ql2 = "select count(*) as jmlhrow from " . $dbname . ".pmn_kontrakjual where kodebarang!='' " . $sort . "  order by `tanggalkontrak` desc";
		$query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}
		$optjns = array("PM" => "Pengiriman", "PK" => "Pemenuhan Kontrak", "UM" => "Uang Muka", "BA" => "Berita Acara Serah Terima");
		@$slvhc = "select * from " . $dbname . ".pmn_kontrakjual where kodebarang!='' " . $sort . "  order by `tanggalkontrak` desc limit " . $offset . "," . $limit . "";
		$qlvhc = $owlPDO->query($slvhc) or die(print " Gagal: " . PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
		$user_online = $_SESSION['standard']['userid'];
		while ($res = $qlvhc->fetch()) {
			$no += 1;
			$arr = "##'" . $res['nokontrak'] . "'";
			echo "
                        <tr class=rowcontent id=tr_$no>
                        <td align=center>" . $no . "</td>
                        <td id=detail_kode" . $no . ">" . $res['nokontrak'] . "</td>
                        <td>" . $nmpt[$res['kodept']] . "</td>
                        <td>" . $nmcustsomer[$res['koderekanan']] . "</td>
                        <td align=center>" . tanggalnormal($res['tanggalkontrak']) . "</td>
                        <td align=center>" . $res['kodebarang'] . "</td>
                        <td>" . $nmkomoditi[$res['kodebarang']] . "</td>
                        <td align=center>" . $res['tanggalkirim'] . "</td>
                        <td align=center>" . $optjns[$res['termbayar']] . "</td>
                        <td align=center>" . getNamaKaryawan($res['updateby']) . "</td>
                        ";
			#cek apakah sudah terjurnal atau belum
			if ($res['posting'] == 0) {
				$isi1 = "<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $res['nokontrak'] . "');\">";
				$isi2 = "<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('" . $res['nokontrak'] . "');\" >";
				$isi3 = "<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('" . $res['nokontrak'] . "','" . $no . "');\" >";
			} else {
				$isi1 = "";
				$isi2 = "";
				$isi3 = "<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posted' >";
			}
			echo "<td align=center style='padding:3px;width:10px;'>" . $isi1 . "</td>
                    <td align=center style='padding:3px;width:10px;'>" . $isi2 . "</td>
                    <td align=center style='padding:3px;width:10px;'>" . $isi3 . "</td>";
			echo "<td align=center style='padding:3px;'>
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pmn_kontrakjual','" . $res['nokontrak'] . "','','pmn_kontakjual_pdf',event)\">
				</td>
				<td align=center style='padding:3px;'>
					<img onclick=dataKeExcel(event,'pmn_slave_kontrakjual_excel.php','" . $res['nokontrak'] . "') src=images/excel.jpg class=resicon title='MS.Excel'>
				</td>";
			if ($res['koderekanan'] == 'API') {
				echo "<img src=images/plus.png class=resicon title='Add " . $_SESSION['lang']['nokontrakinduk'] . " " . $_SESSION['lang']['dari'] . " " . $res['nokontrak'] . "' onclick=addDetail('" . $res['nokontrak'] . "','" . $res['kuantitaskontrak'] . "','" . $res['kodebarang'] . "',event) />";
			}
			echo "
				<td align=center><img src=images/foldoq.png class=resicon  title='Document' onclick='showupload(event," . $no . ")' ></td>";

			echo "</td>
                    </tr>";
		}
		echo "
                <tr class=rowheader><td colspan=10 align=center>
                " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " .  $jlhbrs . "<br />
                <button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
		break;

	case 'getSatuan':
		$optSatuan .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sSat2 = "select distinct satuan from " . $dbname . ".log_5masterbarang where kodebarang='" . $param['kdBrg'] . "'";
		$qSat2 = $owlPDO->query($sSat2) or die(print " Gagal: " . PDOException::getMessage());
		$qSat2->setFetchMode(PDO::FETCH_ASSOC);
		$rsat2 = $qSat2->fetch();

		$optSatuan .= "<option value=" . $rsat2['satuan'] . "  " . ($rsat2['satuan'] == $param['satuan'] ? 'selected' : '') . ">" . $rsat2['satuan'] . "</option>";
		echo $optSatuan;
		break;
	case 'getLastData':
	case 'getEditData':
		
		$sql = "select a.*, b.* from " . $dbname . ".pmn_kontrakjual a left join " . $dbname . ".pmn_5catatan b on a.nokontrak=b.nokontrak where a.nokontrak='" . $param['noKntrk'] . "'";
		// exit('error ' . $sql);
		$query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$res = $query->fetch();
		#ambil satuan
		@$optSatuan .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sSat2 = "select distinct satuan from " . $dbname . ".log_5masterbarang where kodebarang='" . $res['kodebarang'] . "'";
		$qSat2 = $owlPDO->query($sSat2) or die(print " Gagal: " . PDOException::getMessage());
		$qSat2->setFetchMode(PDO::FETCH_ASSOC);
		$rsat2 = $qSat2->fetch();
		$optSatuan .= "<option value='" . $rsat2['satuan'] . "' selected>" . $rsat2['satuan'] . "</option>";

		#ambil data kontak
		$optKom = $optCon = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sCust = "select distinct idkontak,nama,telepon  from " . $dbname . ".pmn_4customercontact where kodecustomer = '" . $res['koderekanan'] . "' order by nama";
		$qCUst = $owlPDO->query($sCust) or die(print " Gagal: " . PDOException::getMessage());
		$qCUst->setFetchMode(PDO::FETCH_ASSOC);
		while ($rCust = $qCUst->fetch()) {
			$optCon .= "<option value='" . $rCust['idkontak'] . "' " . ($rCust['idkontak'] == $res['idkontak'] ? 'selected' : '') . ">" . $rCust['nama'] . "," . $rCust['telepon'] . "</option>";
		}
		#ambil data komoditi
		$sCust2 = "select distinct kodebarang  from " . $dbname . ".pmn_4komoditi where kodecustomer = '" . $res['koderekanan'] . "' order by kodebarang";
		$qCUst2 = $owlPDO->query($sCust2) or die(print " Gagal: " . PDOException::getMessage());
		$qCUst2->setFetchMode(PDO::FETCH_ASSOC);
		while ($rCust2 = $qCUst2->fetch()) {
			@$optKom .= "<option value='" . $rCust2['kodebarang'] . "' " . ($rCust2['kodebarang'] == $res['kodebarang'] ? 'selected' : '') . ">" . $nmkomoditi[$rCust2['kodebarang']] . "</option>";
		}
		#ambil toleransi
		@$sTol = "select distinct toleransipenyusutan  from " . $dbname . ".pmn_4customer where kodecustomer='" . $param['custId'] . "'";
		$qTol = $owlPDO->query($sTol) or die(print " Gagal: " . PDOException::getMessage());
		$qTol->setFetchMode(PDO::FETCH_ASSOC);
		$rTol = $qTol->fetch();




		#bayar ke
		$optData = $optRek = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sRek = "select rekening,noakun,namabank,pemilik from " . $dbname . ".keu_5akunbank 
								where pemilik in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $res['kodept'] . "') order by namabank asc";
		$qRek = $owlPDO->query($sRek) or die(print " Gagal: " . PDOException::getMessage());
		$qRek->setFetchMode(PDO::FETCH_ASSOC);
		while ($rCek = $qRek->fetch()) {



			$optNamaBank = makeOption($dbname, "keu_5daftarbank", 'kodebank,namabank', "kodebank='" . $rCek['namabank'] . "'");
			if ($res['rekening'] == $rCek['noakun']) {

				$optRek .= "<option value='" . $rCek['noakun'] . "' selected>" . $rCek['pemilik'] . ":" . $optNamaBank[$rCek['namabank']] . " " . $rCek['rekening'] . "</option>";
			} else {
				$optRek .= "<option value='" . $rCek['noakun'] . "'>" . $rCek['pemilik'] . ":" . $optNamaBank[$rCek['namabank']] . " " . $rCek['rekening'] . "</option>";
			}
 		}
 

		$optIniTmptCtr = makeOption($dbname, 'pmn_5lokasikontrak', 'id,inisial', "id='" . $res['lokasikontrak'] . "'");
		$optTmptCtr = makeOption($dbname, 'pmn_5lokasikontrak', 'id,lokasi', "id='" . $res['lokasikontrak'] . "'");
		$optLocKontrak = "<option value='" . $res['lokasikontrak'] . "'>" . $optIniTmptCtr[$res['lokasikontrak']] . "-" . $optTmptCtr[$res['lokasikontrak']] . "</option>";

		$optTmptDaerahCtr = makeOption($dbname, 'pmn_5daerahkontrak', 'id,lokasi', "id='" . $res['daerahkontrak'] . "'");
		$optDaerahCtr = "<option value='" . $res['daerahkontrak'] . "'>" . $optTmptDaerahCtr[$res['daerahkontrak']] . "</option>";
		

		// exit('error ' . $param['noKntrk']);


		echo $param['noKntrk'] . "###" . $res['koderekanan'] . "###" . tanggalnormal($res['tanggalkontrak']) . "###" . $optKom . "###" . $optSatuan . "###" . $res['hargasatuan'] . "###" . $res['matauang'] . "###" . $res['terbilang'] . "###" . $res['kuantitaskontrak'] . "###" . tanggalnormal($res['tanggalkirim']) . "###" . tanggalnormal($res['sdtanggal']) . "###" . tanggalnormal($res['tanggalkirim1']) . "###" . tanggalnormal($res['sdtanggal1']) . "###" . tanggalnormal($res['tanggalkirim2']) . "###" . tanggalnormal($res['sdtanggal2']) . "###" . tanggalnormal($res['tanggalkirim3']) . "###" . tanggalnormal($res['sdtanggal3']) . "###" . $res['kuantitaskirim'] . "###" . $res['kuantitaskirim1'] . "###" . $res['kuantitaskirim2'] . "###" . $res['kuantitaskirim3'] . "###" . $res['franco'] . "###" . $res['ffa'] . "###" . $res['dobi'] . "###" . $res['mdani'] . "###" . $res['toleransi'] . "###" . $res['kdtermin'] . "###" . $optRek . "###" . $res['penandatangan'] . "###" . $res['namajabatan'] . "###" . $res['penandatangan2'] . "###" . $res['namajabatan2'] . "###" . $res['catatanlain'] . "###" . $optCon . "###" . $res['kodept'] . "###" . $res['ppn'] . "###" . tanggalnormal($res['tglpembayarpertama']) . "###" . $res['moist'] . "###" . $res['dirt'] . "###" . $res['grading'] . "###" . $optData . "###" . $res['ketbayardp'] . "###" . $res['ketbayarpelunasan'] . "###" . $res['berikat'] . "###" . $res['forcemajuere'] . "###" . $res['perselisihan'] . "###" . $res['nokontrakexternal'] . "###" . $optLocKontrak . "###" . $optDaerahCtr . "###" . $res['nokontrak_ref'] . "###" . $res['termbayar'] . "###" . $res['millcode'] . "###" . $res['tipepenjualan'] . "###" . $res['persenppn'] . "###" . $res['hargaspot']."###".$res['mdani1']."###".$res['mdani2']."###".$res['mdani3']."###".$res['mdani4']."###".$res['ffa1']."###".$res['ffa2']."###".$res['ffa3']."###".$res['ffa4']."###".$res['dobi1']."###".$res['dobi2']."###".$res['dobi3']."###".$res['dobi4']."###".$res['flagtglbayar'];

		break;
	case 'insert':

		#097/CA-SMART/PERJ/CPO/VIII/2025
		$inisialcustomer = getinisialcustomer($param['custId']);
		$inisialpt = getinisialorg($param['kdPt']);
		$inisialbrg = getinisialbrg($param['kdBrg']);


		$tgl = explode("-", $param['tlgKntrk']);
		$whr = "kodebarang='" . $param['kdBrg'] . "'";
		$optKd = makeOption($dbname, 'log_5masterbarang', 'kodebarang,inisial', $whr);

		$ceknokontrak = "/" . $inisialpt . "-" . $inisialcustomer . "/PERJ/" . $inisialbrg . "/" . romawi(intval($tgl[1])) . "/" . substr($tgl[2],0, 4);

		$sCek = "
			SELECT MAX(CAST(SUBSTRING_INDEX(nokontrak, '/', 1) AS UNSIGNED)) AS lastNo
			FROM " . $dbname . ".pmn_kontrakjual
			WHERE kodept='" . $param['kdPt'] . "'
			AND LEFT(tanggalkontrak,4)='" . $tgl[2] . "' 
		";

		$qCek = $owlPDO->query($sCek) or die(print " Gagal: " . $sCek);
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
		$rCek = $qCek->fetch();

		if (!empty($rCek['lastNo'])) {
			$nourut = addZero(($rCek['lastNo'] + 1), 3);
		} else {
			$nourut = addZero(1, 3);
		}


		if ($optKd[$param['kdBrg']] == '') {
			exit("Warning : Kode inisialisasi barang/komoditi belum di input di master barang. Silahkan hubungi Administrator.");
		}

		if ($param['franco'] == '') {
			exit("Warning : Tempat Penyerahan belum dipilih.");
		}

		$optIniTmptCtr = makeOption($dbname, 'pmn_5lokasikontrak', 'id,inisial', "id='" . $posisictr . "'");
		$optDaerahCtr = makeOption($dbname, 'pmn_5lokasikontrak', 'id,lokasi', "id='" . $daerahctr . "'");


		$nokontrak = $nourut . $ceknokontrak;

		if (($param['custId'] == '') || ($param['kdBrg'] == '') || ($param['HrgStn'] == '') || ($param['qty'] == '') || ($param['tlgKntrk'] == '') || ($param['satuan'] == '') || ($termbyr == '')) {
			echo "Tolong Cek :";
			echo "<ol type=1>";
			echo "<li>" . $_SESSION['lang']['nmcust'] . "</li>";
			echo "<li>" . $_SESSION['lang']['namabarang'] . "</li>";
			echo "<li>" . $_SESSION['lang']['hargasatuan'] . "</li>";
			echo "<li>" . $_SESSION['lang']['jmlhBrg'] . "</li>";
			echo "<li>" . $_SESSION['lang']['tglKontrak'] . "</li>";
			echo "<li>" . $_SESSION['lang']['satuan'] . "</li>";
			echo "<li>" . $_SESSION['lang']['payment'] . "</li>";
			echo "</ol>";
			echo "Warning:" . $_SESSION['lang']['kosong'];
			exit();
		}

		$param['tglKrm0'] == '' ? $param['tglKrm0'] = '0000-00-00' : tanggalsystem($param['tglKrm0']);
		$param['tglKrm1'] == '' ? $param['tglKrm1'] = '0000-00-00' : tanggalsystem($param['tglKrm1']);
		$param['tglKrm2'] == '' ? $param['tglKrm2'] = '0000-00-00' : tanggalsystem($param['tglKrm2']);
		$param['tglKrm3'] == '' ? $param['tglKrm3'] = '0000-00-00' : tanggalsystem($param['tglKrm3']);
		$param['tglSd0'] == '' ? $param['tglSd0'] = '0000-00-00' : tanggalsystem($param['tglSd0']);
		$param['tglSd1'] == '' ? $param['tglSd1'] = '0000-00-00' : tanggalsystem($param['tglSd1']);
		$param['tglSd2'] == '' ? $param['tglSd2'] = '0000-00-00' : tanggalsystem($param['tglSd2']);
		$param['tglSd3'] == '' ? $param['tglSd3'] = '0000-00-00' : tanggalsystem($param['tglSd3']);
		$param['jmlh0'] == '' ? $param['jmlh0'] = 0 : $param['jmlh0'] = $param['jmlh0'];
		$param['jmlh1'] == '' ? $param['jmlh1'] = 0 : $param['jmlh1'] = $param['jmlh1'];
		$param['jmlh2'] == '' ? $param['jmlh2'] = 0 : $param['jmlh2'] = $param['jmlh2'];
		$param['jmlh3'] == '' ? $param['jmlh3'] = 0 : $param['jmlh3'] = $param['jmlh3'];
		$param['moist'] == '' ? $param['moist'] = 0 : $param['moist'] = $param['moist'];
		$param['dirt'] == '' ? $param['dirt'] = 0 : $param['dirt'] = $param['dirt'];
		$param['grading'] == '' ? $param['grading'] = 0 : $param['grading'] = $param['grading'];
		$param['kualitasffa'] == '' ? $param['kualitasffa'] = 0 : $param['kualitasffa'] = $param['kualitasffa'];
		$param['kualitasdob'] == '' ? $param['kualitasdob'] = 0 : $param['kualitasdob'] = $param['kualitasdob'];
		$param['kualitasmdani'] == '' ? $param['kualitasmdani'] = 0 : $param['kualitasmdani'] = $param['kualitasmdani'];

		try {
		$owlPDO->beginTransaction();
		
		$sIns = "insert into " . $dbname . ".pmn_kontrakjual (`nokontrak`, `tanggalkontrak`, `koderekanan`, `kodebarang`, `satuan`, 
									`hargasatuan`, `hargaspot`, `terbilang`, `tanggalkirim`, `sdtanggal`, `tanggalkirim1`, `sdtanggal1`, `tanggalkirim2`, 
									`sdtanggal2`, `tanggalkirim3`, `sdtanggal3`, `rekening`, `kdtermin`, `franco`, `ffa`, `dobi`, `mdani`, 
									`kuantitaskirim`, `kuantitaskirim1`, `kuantitaskirim2`, `kuantitaskirim3`, `penandatangan`, `penandatangan2`, 
									`namajabatan`, `namajabatan2`, `catatanlain`, `kuantitaskontrak`, `toleransi`, `kodeorg`, `kodept`, `matauang`,
									`idkontak`,`ppn`,`tglpembayarpertama`,`moist`,`dirt`,`grading`,`nokontrak_ref`,`ketbayardp`,`ketbayarpelunasan`,
									`berikat`,`forcemajuere`,`perselisihan`,`nokontrakexternal`,`lokasikontrak`,`daerahkontrak`,`termbayar`,`millcode`,
									tipepenjualan,updateby,persenppn,flagtglbayar) 
											   values ('" . $nokontrak . "','" . tanggalsystem($param['tlgKntrk']) . "','" . $param['custId'] . "','" . $param['kdBrg'] . "',
											   '" . $param['satuan'] . "','" . $param['HrgStn'] . "','" . $param['hrgspot'] . "','" . $param['tBlg'] . "','" . tanggalsystem($param['tglKrm0']) . "',
											   '" . tanggalsystem($param['tglSd0']) . "','" . tanggalsystem($param['tglKrm1']) . "',
											   '" . tanggalsystem($param['tglSd1']) . "','" . tanggalsystem($param['tglKrm2']) . "',
											   '" . tanggalsystem($param['tglSd2']) . "','" . tanggalsystem($param['tglKrm3']) . "',
											   '" . tanggalsystem($param['tglSd3']) . "','" . $param['byrKe'] . "','" . $param['syrtByr'] . "',
											   '" . $param['franco'] . "','" . $param['kualitasffa'] . "','" . $param['kualitasdob'] . "',
											   '" . $param['kualitasmdani'] . "','" . $param['jmlh0'] . "','" . $param['jmlh1'] . "','" . $param['jmlh2'] . "',
											   '" . $param['jmlh3'] . "','" . $param['tndtng'] . "','" . $param['tndtngPembli'] . "','" . $param['tndtngJbtn'] . "',
											   '" . $param['jtbnPembli'] . "','" . $param['cttnLain'] . "','" . $param['qty'] . "','" . $param['tlransi'] . "',
											   '" . $_SESSION['empl']['lokasitugas'] . "','" . $param['kdPt'] . "','" . $param['kurs'] . "',
											   '" . $param['nmPerson'] . "','" . $param['ppnId'] . "','" . tanggalsystem($param['tglByr']) . "',
											   " . $param['moist'] . "," . $param['dirt'] . "," . $param['grading'] . ",'" . $param['kntrkRef'] . "',
											   '" . $param['ketdp'] . "','" . $param['ketplns'] . "',
											   '" . $param['berikat'] . "','" . $param['forcemajuere'] . "','" . $param['perselisihan'] . "','" . $param['noext'] . "','" . $posisictr . "',
											   '" . $daerahctr . "','" . $termbyr . "','" . $param['millcode'] . "',
											   '" . $param['tppenjualan'] . "','" . $_SESSION['standard']['userid'] . "','" . $param['persenppn'] . "','" . $param['ketbyr'] . "')";

		$sctt = "insert into " . $dbname . ".pmn_5catatan (`nokontrak`,`mdani1`,`mdani2`,`mdani3`,`mdani4`,`ffa1`,`ffa2`,`ffa3`,`ffa4`,`dobi1`,`dobi2`,`dobi3`,`dobi4`) 
											   values ('" . $nokontrak . "','" . $param['md_1'] . "','" . $param['md_2'] . "','" . $param['md_3'] . "','" . $param['md_4'] . "',
											   '" . $param['ffa_1'] . "','" . $param['ffa_2'] . "','" . $param['ffa_3'] . "','" . $param['ffa_4'] . "',
											   '" . $param['dobi_1'] . "','" . $param['dobi_2'] . "','" . $param['dobi_3'] . "','" . $param['dobi_4'] . "')";

	
			$owlPDO->exec($sIns);
			$owlPDO->exec($sctt);
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollBack();
			echo "DB Error : " . $e->getMessage();
		}
		
		break;
	case 'update':
		if (($param['custId'] == '') || ($param['kdBrg'] == '') || ($param['HrgStn'] == '') || ($param['qty'] == '') || ($param['tlgKntrk'] == '') || ($param['satuan'] == '') || ($termbyr == '')) {
			echo "Tolong Cek :";
			echo "<ol type=1>";
			echo "<li>" . $_SESSION['lang']['nmcust'] . "</li>";
			echo "<li>" . $_SESSION['lang']['namabarang'] . "</li>";
			echo "<li>" . $_SESSION['lang']['hargasatuan'] . "</li>";
			echo "<li>" . $_SESSION['lang']['jmlhBrg'] . "</li>";
			echo "<li>" . $_SESSION['lang']['tglKontrak'] . "</li>";
			echo "<li>" . $_SESSION['lang']['satuan'] . "</li>";
			echo "<li>" . $_SESSION['lang']['payment'] . "</li>";
			echo "</ol>";
			echo "Warning:" . $_SESSION['lang']['kosong'];
			exit();
		}
		
		try {
		$owlPDO->beginTransaction();

		$str = "update " . $dbname . ".pmn_kontrakjual set `tanggalkontrak`='" . tanggalsystem($param['tlgKntrk']) . "', `koderekanan`='" . $param['custId'] . "', 
                      `kodebarang`='" . $param['kdBrg'] . "', `satuan`='" . $param['satuan'] . "', `hargasatuan`='" . $param['HrgStn'] . "', `hargaspot`='" . $param['hrgspot'] . "', `terbilang`='" . $param['tBlg'] . "', 
                      `tanggalkirim`='" . tanggalsystem($param['tglKrm0']) . "', `sdtanggal`='" . tanggalsystem($param['tglSd0']) . "', `tanggalkirim1`='" . tanggalsystem($param['tglKrm1']) . "', 
                      `sdtanggal1`='" . tanggalsystem($param['tglSd1']) . "', `tanggalkirim2`='" . tanggalsystem($param['tglKrm2']) . "', `sdtanggal2`='" . tanggalsystem($param['tglSd2']) . "', 
                      `tanggalkirim3`='" . tanggalsystem($param['tglKrm3']) . "', `sdtanggal3`='" . tanggalsystem($param['tglSd3']) . "', `rekening`='" . $param['byrKe'] . "', `kdtermin`='" . $param['syrtByr'] . "', 
                      `franco`='" . $param['franco'] . "', `ffa`='" . $param['kualitasffa'] . "', `dobi`='" . $param['kualitasdob'] . "', `mdani`='" . $param['kualitasmdani'] . "', `kuantitaskirim`='" . $param['jmlh0'] . "', 
                      `kuantitaskirim1`='" . $param['jmlh1'] . "', `kuantitaskirim2`='" . $param['jmlh2'] . "', `kuantitaskirim3`='" . $param['jmlh3'] . "', `penandatangan`='" . $param['tndtng'] . "', `penandatangan2`='" . $param['tndtngPembli'] . "', 
                      `namajabatan`='" . $param['tndtngJbtn'] . "', `namajabatan2`='" . $param['jtbnPembli'] . "', `catatanlain`='" . $param['cttnLain'] . "', `kuantitaskontrak`='" . $param['qty'] . "', `toleransi`='" . $param['tlransi'] . "', `kodept`='" . $param['kdPt'] . "', 
                      `matauang`='" . $param['kurs'] . "',idkontak='" . intval($param['nmPerson']) . "',ppn='" . $param['ppnId'] . "',`tglpembayarpertama`='" . tanggalsystem($param['tglByr']) . "',moist='" . $param['moist'] . "',dirt='" . $param['dirt'] . "',grading='" . $param['grading'] . "',
                      `nokontrak_ref`='" . $param['kntrkRef'] . "',`ketbayardp`='" . $param['ketdp'] . "',`ketbayarpelunasan`='" . $param['ketplns'] . "',
					  `berikat`='" . $param['berikat'] . "',`forcemajuere`='" . $param['forcemajuere'] . "',`perselisihan`='" . $param['perselisihan'] . "',
					  `nokontrakexternal`='" . $param['noext'] . "',`termbayar`='" . $param['termbyr'] . "',`millcode`='" . $param['millcode'] . "',
					  `tipepenjualan`='" . $param['tppenjualan'] . "',updateby='" . $_SESSION['standard']['userid'] . "',persenppn='" . $param['persenppn'] . "',flagtglbayar='" . $param['ketbyr'] . "'
					  where nokontrak='" . $param['noKntrk'] . "'";
		 
		$strupdate = "INSERT INTO ".$dbname.".pmn_5catatan
			(nokontrak, mdani1, mdani2, mdani3, mdani4, ffa1, ffa2, ffa3, ffa4, dobi1, dobi2, dobi3, dobi4)
		VALUES
			('".$param['noKntrk']."',
			'".$param['md_1']."','".$param['md_2']."','".$param['md_3']."','".$param['md_4']."',
			'".$param['ffa_1']."','".$param['ffa_2']."','".$param['ffa_3']."','".$param['ffa_4']."',
			'".$param['dobi_1']."','".$param['dobi_2']."','".$param['dobi_3']."','".$param['dobi_4']."')
		ON DUPLICATE KEY UPDATE
			mdani1 = VALUES(mdani1),
			mdani2 = VALUES(mdani2),
			mdani3 = VALUES(mdani3),
			mdani4 = VALUES(mdani4),
			ffa1   = VALUES(ffa1),
			ffa2   = VALUES(ffa2),
			ffa3   = VALUES(ffa3),
			ffa4   = VALUES(ffa4),
			dobi1  = VALUES(dobi1),
			dobi2  = VALUES(dobi2),
			dobi3  = VALUES(dobi3),
			dobi4  = VALUES(dobi4)";


  		$owlPDO->exec($str);
		$owlPDO->exec($strupdate);
		$owlPDO->commit();

		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
		}
		break;
	case 'getCust':
		$optKom = $optCon = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sCust = "select distinct idkontak,nama,telepon  from " . $dbname . ".pmn_4customercontact where kodecustomer = '" . $param['custId'] . "' order by nama";
		$qCUst = $owlPDO->query($sCust) or die(print " Gagal: " . PDOException::getMessage());
		$qCUst->setFetchMode(PDO::FETCH_ASSOC);
		while ($rCust = $qCUst->fetch()) {
			$optCon .= "<option value='" . $rCust['idkontak'] . "'>" . $rCust['nama'] . "," . $rCust['telepon'] . "</option>";
		}
		$sCust2 = "select distinct kodebarang  from " . $dbname . ".pmn_4komoditi where kodecustomer = '" . $param['custId'] . "' order by kodebarang";
		$qCUst2 = $owlPDO->query($sCust2) or die(print " Gagal: " . PDOException::getMessage());
		$qCUst2->setFetchMode(PDO::FETCH_ASSOC);
		while ($rCust2 = $qCUst2->fetch()) {
			$whr = "kodebarang='" . $rCust2['kodebarang'] . "'";
			$optKom .= "<option value='" . $rCust2['kodebarang'] . "'>" . $nmkomoditi[$rCust2['kodebarang']] . "</option>";
		}
		$sTol = "select toleransipenyusutan,statusberikat  from " . $dbname . ".pmn_4customer where kodecustomer='" . $param['custId'] . "'";
		$qTol = $owlPDO->query($sTol) or die(print " Gagal: " . PDOException::getMessage());
		$qTol->setFetchMode(PDO::FETCH_ASSOC);
		$rTol = $qTol->fetch();

		echo $optCon . "###" . $optKom . "###" . $rTol['statusberikat'] . "###" . $rTol['toleransipenyusutan'];
		// exit("Error:MASUK");
		break;

	case 'dataDel':
		try {
			$owlPDO->beginTransaction();

 			$sDelCat = "DELETE FROM " . $dbname . ".pmn_5catatan WHERE nokontrak='" . $param['noKntrk'] . "'";
			$owlPDO->exec($sDelCat);

 			$sDel = "DELETE FROM " . $dbname . ".pmn_kontrakjual WHERE nokontrak='" . $param['noKntrk'] . "'";
			$owlPDO->exec($sDel);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollBack();
			echo "DB Error : " . $e->getMessage();
		}
		break;
	case 'getRek':
		$optData = $optRek = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sRek = "select distinct rekening,noakun,namabank,pemilik from " . $dbname . ".keu_5akunbank where pemilik in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $_POST['kdpt'] . "') order by namabank asc";
		$qRek = $owlPDO->query($sRek) or die(print " Gagal: " . PDOException::getMessage());
		$qRek->setFetchMode(PDO::FETCH_ASSOC);
		while ($rCek = $qRek->fetch()) {

			// $optByrke.="<option value='".$rByr['noakun']."'>".$rByr['pemilik'].":".$optNamaBank[$rByr['namabank']]." ".$rByr['rekening']."</option>";
			$optNamaBank = makeOption($dbname, "keu_5daftarbank", 'kodebank,namabank', "kodebank='" . $rCek['namabank'] . "'");
			$optRek .= "<option value='" . $rCek['noakun'] . "' selected>" . $rCek['pemilik'] . ":" . $optNamaBank[$rCek['namabank']] . " " . $rCek['rekening'] . "</option>";
		}
		if ($_POST['kdpt'] != 'AMP') {
			$sData = "select sum(beratbersih) as jmlh,kuantitaskontrak,a.nokontrak from " . $dbname . ".pabrik_timbangan a left join " . $dbname . ".pmn_kontrakjual b on a.nokontrak=b.nokontrak where b.kodept='AMP' group by a.nokontrak";
			$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while ($rData = $qData->fetch()) {
				if ($rData['jmlh'] < $rData['kuantitaskontrak']) {
					$optData .= "<option value='" . $rData['nokontrak'] . "'>" . $rData['nokontrak'] . "</option>";
				}
			}
		}

		echo $optRek . "####" . $optData;
		break;
	case 'getFormDet':
		$optData = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sData = "select kuantitaskontrak,nokontrak from " . $dbname . ".pmn_kontrakjual"
			. " where kodept='AMP' and kodebarang='" . $_POST['komoditi'] . "' order by nokontrak";
		$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while ($rData = $qData->fetch()) {
			$sSum = "select sum(beratbersih) as jmlh from " . $dbname . ".pabrik_timbangan where nokontrak='" . $rData['nokontrak'] . "'";
			$qSum = $owlPDO->query($sSum) or die(print " Gagal: " . PDOException::getMessage());
			$qSum->setFetchMode(PDO::FETCH_ASSOC);
			$rSum = $qSum->fetch();
			$optData .= "<option value='" . $rData['nokontrak'] . "'>" . $rData['nokontrak'] . "</option>";
		}
		//echo $sData;
		$tab .= "<table cellpadding=1 cellspacing=1 border=0>";
		$tab .= "<thead><tr>";
		$tab .= "<td>" . $_SESSION['lang']['NoKontrak'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['volumekontrak'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['nokontrakinduk'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['jumlah'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['action'] . "</td>";
		$tab .= "<tr></thead><tbody>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td><input type=text id=nokontrak class=myinputtext value='" . $_POST['nokontrak'] . "' readonly=readonly style=width:150px /></td>";
		$tab .= "<td><input type=text id=jmlHnokontrak class=myinputtextnumber value='" . number_format($_POST['totKontrak'], 0) . "' readonly=readonly /></td>";
		$tab .= "<td><select id=nokntr_ref>" . $optData . "</select></td>";
		$tab .= "<td><input type=text class=myinputtextnumber id=jmlhRef onkeypress='return angka_doang(event)' /></td>";
		$tab .= "<td><input type=hidden id=nokntr_ref2 value='' /><img src=images/save.png class=resicon onclick=saveDet() /></td>";
		$tab .= "</tr>";
		$tab .= "</tbody></table><br />";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 width=100%>";
		$tab .= "<thead><tr>";
		$tab .= "<td>" . $_SESSION['lang']['NoKontrak'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['volumekontrak'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['nokontrakinduk'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['kuota'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['terpenuhi'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['sisa'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['action'] . "</td>";
		$tab .= "<tr></thead><tbody id=isidetail>";
		$sData = "select * from " . $dbname . ".pmn_kontrakjualdt where nokontrak='" . $_POST['nokontrak'] . "'";
		$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		$rwDt = owlBaris($qData);
		if ($rwDt == 0) {
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td colspan=7>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
		} else {
			while ($rData = $qData->fetch()) {
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td>" . $rData['nokontrak'] . "</td>";
				$tab .= "<td align=right>" . number_format($_POST['totKontrak'], 0) . "</td>";
				$tab .= "<td>" . $rData['nokontrak_ref'] . "</td>";
				$tab .= "<td align=right>" . number_format($rData['kuota'], 0) . "</td>";
				$tab .= "<td align=right>" . number_format($rData['terpenuhi'], 0) . "</td>";
				$rData['sisa'] = $rData['kuota'] - $rData['terpenuhi'];
				$tab .= "<td align=right>" . number_format($rData['sisa'], 0) . "</td>";
				if ($rData['terpenuhi'] == 0) {
					$tab .= "<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField2('" . $rData['nokontrak'] . "','" . $rData['nokontrak_ref'] . "');\">
                                       <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData2('" . $rData['nokontrak'] . "','" . $rData['nokontrak_ref'] . "');\" ></td>";
				} else {
					$tab .= "<td>&nbsp;</td>";
				}

				$tab .= "</tr>";
			}
		}

		$tab .= "</tbody></table>";
		echo $tab;
		break;
	case 'loadDet':
		$whr = "nokontrak='" . $_POST['nokontrak'] . "'";
		$optTot =  makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,kuantitaskontrak', $whr);
		$sData = "select * from " . $dbname . ".pmn_kontrakjualdt where nokontrak='" . $_POST['nokontrak'] . "'";
		$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		$rwDt = owlBaris($qData);
		if ($rwDt == 0) {
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td colspan=7>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
		} else {
			while ($rData = $qData->fetch()) {
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td>" . $rData['nokontrak'] . "</td>";
				$tab .= "<td align=right>" . number_format($optTot[$_POST['nokontrak']], 0) . "</td>";
				$tab .= "<td>" . $rData['nokontrak_ref'] . "</td>";
				$tab .= "<td align=right>" . number_format($rData['kuota'], 0) . "</td>";
				$tab .= "<td align=right>" . number_format($rData['terpenuhi'], 0) . "</td>";
				$rData['sisa'] = $rData['kuota'] - $rData['terpenuhi'];
				$tab .= "<td align=right>" . number_format($rData['sisa'], 0) . "</td>";
				if ($rData['terpenuhi'] == 0) {
					$tab .= "<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField2('" . $rData['nokontrak'] . "','" . $rData['nokontrak_ref'] . "');\">
                                       <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData2('" . $rData['nokontrak'] . "','" . $rData['nokontrak_ref'] . "');\" ></td>";
				} else {
					$tab .= "<td>&nbsp;</td>";
				}
				$tab .= "</tr>";
			}
		}
		echo $tab;
		break;
	case 'saveDet':

		$_POST['jmlHnokontrak'] =  str_replace(",", "", $_POST['jmlHnokontrak']);
		$sCek = "select terpenuhi from " . $dbname . ".pmn_kontrakjualdt where nokontrak='" . $_POST['nokontrak'] . "' and nokontrak_ref='" . $_POST['nokntr_ref'] . "'";
		$qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
		$rCek = $qCek->fetch();
		if ($rCek['terpenuhi'] == 0) {
			#cek apakah pembagian kuantitas kontrak induk sudah lebih atau belum
			#query mengambil kuantitaskontrak nokontrak induk
			$sCekKontrakInduk = "select kuantitaskontrak from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $_POST['nokntr_ref'] . "'";
			$qCekKontrakInduk = $owlPDO->query($sCekKontrakInduk) or die(print " Gagal: " . PDOException::getMessage());
			$qCekKontrakInduk->setFetchMode(PDO::FETCH_ASSOC);
			$rCekKontrakInduk = $qCekKontrakInduk->fetch();
			#query cari data totalan kuota atas nokontrak induk
			$sSum2 = "select sum(kuota) as total from " . $dbname . ".pmn_kontrakjualdt where nokontrak_ref='" . $_POST['nokntr_ref'] . "'";
			$qSum2 = $owlPDO->query($sSum2) or die(print " Gagal: " . PDOException::getMessage());
			$qSum2->setFetchMode(PDO::FETCH_ASSOC);
			$rSum2 = $qSum2->fetch();
			if (intval($rSum2['total']) > $rCekKontrakInduk['kuantitaskontrak']) {
				exit("warning: Total distribusi " . $_SESSION['lang']['kuota'] . " (" . $rSum2['total'] . ") melebihi " . $_SESSION['lang']['volumekontrak'] . " (" . $rCekKontrakInduk['kuantitaskontrak'] . ") " . $_SESSION['lang']['nokontrakinduk'] . " : " . $_POST['nokntr_ref']);
			}

			#cek apakah sudah melebihi kuota kontrak detail
			$sSum = "select sum(kuota) as total from " . $dbname . ".pmn_kontrakjualdt where nokontrak='" . $_POST['nokontrak'] . "'";
			$qSum = $owlPDO->query($sSum) or die(print " Gagal: " . PDOException::getMessage());
			$qSum->setFetchMode(PDO::FETCH_ASSOC);
			$rSum = $qSum->fetch();
			if (($rSum['total'] + $_POST['jmlhRef']) > $_POST['jmlHnokontrak']) {
				exit("warning: Total " . $_SESSION['lang']['kuota'] . " melebihi " . $_SESSION['lang']['volumekontrak'] . "  " . $_POST['nokontrak']);
			}
			if ($_POST['nokntr_ref2'] == '') {
				#insert detail dari no induk
				$sdel = "delete from " . $dbname . ".pmn_kontrakjualdt where nokontrak='" . $_POST['nokontrak'] . "' and nokontrak_ref='" . $_POST['nokntr_ref'] . "'";
				try {
					$owlPDO->exec($sdel);
					$sInsert = "insert into " . $dbname . ".pmn_kontrakjualdt values ('" . $_POST['nokontrak'] . "','" . $_POST['nokntr_ref'] . "','" . $_POST['jmlhRef'] . "','0')";
					try {
						$owlPDO->exec($sInsert);
					} catch (PDOException $e) {
						exit("warning: " . $e->getMessage() . "___" . $sInsert);
					}
				} catch (PDOException $e) {
					exit("warning: " . $e->getMessage() . "___" . $sdel);
				}
			} else {
				$supdate = "update " . $dbname . ".pmn_kontrakjualdt set kuota='" . $_POST['jmlhRef'] . "',nokontrak_ref='" . $_POST['nokntr_ref'] . "' where nokontrak='" . $_POST['nokontrak'] . "' and nokontrak_ref='" . $_POST['nokntr_ref2'] . "'";
				try {
					$owlPDO->exec($supdate);
				} catch (PDOException $e) {
					exit("warning: " . $e->getMessage() . "___" . $supdate);
				}
			}
		} else {
			exit("warning:  Jurnal Sudah Terbentuk");
		}
		break;

	case 'delDet':
		$sCek = "select terpenuhi from " . $dbname . ".pmn_kontrakjualdt where nokontrak='" . $_POST['nokontrak'] . "' and nokontrak_ref='" . $_POST['nokntr_ref'] . "'";
		$qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
		$rCek = $qCek->fetch();
		if ($rCek['terpenuhi'] == 0) {
			$sdel = "delete from " . $dbname . ".pmn_kontrakjualdt where nokontrak='" . $_POST['nokontrak'] . "' and nokontrak_ref='" . $_POST['nokntr_ref'] . "'";
			try {
				$owlPDO->exec($sdel);
			} catch (PDOException $e) {
				exit("warning: " . $e->getMessage() . "___" . $sdel);
			}
		} else {
			exit("warning: Jurnal Sudah Terbentuk");
		}
		break;

	case 'editDet':
		$sCek = "select * from " . $dbname . ".pmn_kontrakjualdt where nokontrak='" . $_POST['nokontrak'] . "' and nokontrak_ref='" . $_POST['nokntr_ref'] . "'";
		$qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
		$rCek = $qCek->fecth();
		if ($rCek['terpenuhi'] == 0) {
			$optData = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			$sData = "select sum(beratbersih) as jmlh,kuantitaskontrak,a.nokontrak from " . $dbname . ".pabrik_timbangan a left join " . $dbname . ".pmn_kontrakjual b on a.nokontrak=b.nokontrak where b.kodept='AMP' group by a.nokontrak";
			$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while ($rData = $qData->fetch()) {
				//if($rData['jmlh']<$rData['kuantitaskontrak']){
				$optData .= "<option value='" . $rData['nokontrak'] . "' " . ($rCek['nokontrak_ref'] == $rData['nokontrak'] ? "selected" : "") . ">" . $rData['nokontrak'] . "</option>";
				//}
			}
			echo $rCek['nokontrak'] . "####" . $optData . "####" . $rCek['kuota'] . "####" . $rCek['nokontrak_ref'];
		} else {
			exit("warning: Jurnal Sudah Terbentuk");
		}
		break;

	case 'posting':
		$str = "update " . $dbname . ".pmn_kontrakjual set posting='1',postingby='" . $_SESSION['standard']['userid'] . "' where nokontrak='" . $param['nokontrak'] . "'";

		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		$msgdt = "Pemberitahuan bahwa kontrak penjualan sudah dibuat dengan nomor " . $param['nokontrak'] . " ";
		$str = "select * from " . $dbname . ".setup_notification_dt where kodejenis='PMNKONTRAK'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			createnotif($param['nokontrak'], 'PMNKONTRAK', $msgdt, $bar['karyawanid'], date('Y-m-d H:i:s'));
		}
		#= buat notif
		// function createnotif($notrk,$tipe,$msgdt,$createby,$tanggal){
		// global $dbname;
		// global $owlPDO;

		// $stry="insert into ".$dbname.".list_notification (kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('".$notrk."','".$tipe."','".$msgdt."','".$createby."','0','0','".$tanggal."')";
		// $owlPDO->exec($stry);
		// }


		/*
                $slvhc="select * from ".$dbname.".pmn_kontrakjual where nokontrak='" . $param['nokontrak'] . "'";
                $qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
                $qlvhc->setFetchMode(PDO::FETCH_ASSOC);
                $user_online=$_SESSION['standard']['userid'];
                $bar=$qlvhc->fetch();
                $kodeorg=substr($param['nokontrak'],4,3);
                $kdCust=$bar['koderekanan'];
                $kdbrg=$bar['kodebarang'];
                if($bar['sdtanggal3']=='0000-00-00'){
                    if($bar['sdtanggal2']=='0000-00-00'){
                        if($bar['sdtanggal1']=='0000-00-00'){
                            $tglAkhir = $bar['sdtanggal'];
                        }else{
                            $tglAkhir = $bar['sdtanggal1'];
                        }
                    }else{
                        $tglAkhir = $bar['sdtanggal2'];
                    }
                }else{
                    $tglAkhir = $bar['sdtanggal3'];
                }
                $kettgl= tanggalnormal($bar['tanggalkirim'])." s/d ".tanggalnormal($tglAkhir);

                $ffaData="FFA ".number_format($bar['ffa'],2)." % Max";
                $dobiData="Dobi ".number_format($bar['dobi'],2)." Min";
                $mdaniData="M & I ".number_format($bar['mdani'],2)." % Max";
                $moistData="Moisture ".number_format($bar['moist'],2)." % Max";
                $dirtData="Impurities ".number_format($bar['dirt'],2)." % Max";
                $gradingData="Grading ".number_format($bar['grading'],2)." %";

                $ss="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='PM' and kodeparameter='PMKONTRAK' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
                $rs=$owlPDO->query($ss) or die(print " Gagal: ".PDOException::getMessage());
                $rs->setFetchMode(PDO::FETCH_ASSOC);
                $bs=$rs->fetch();
                $nilai=$bs['nilai'];

                $ss="select karyawanid,email from ".$dbname.".datakaryawan  where kodejabatan='".$nilai."' and kodeorganisasi='".$kodeorg."' and lokasitugas='".$_SESSION['empl']['lokasitugas']."' ";
                $rs=$owlPDO->query($ss) or die(print " Gagal: ".PDOException::getMessage());
                $rs->setFetchMode(PDO::FETCH_ASSOC);
                $bs=$rs->fetch();
                $karyawanid=$bs['karyawanid'];
                $email=$bs['email'];

                $ss="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
                $rs=$owlPDO->query($ss) or die(print " Gagal: ".PDOException::getMessage());
                $rs->setFetchMode(PDO::FETCH_ASSOC);
                $bs=$rs->fetch();
                $namaorganisasi=$bs['namaorganisasi'];

                $str="select * from ".$dbname.".pmn_4customer  where kodecustomer='".$kdCust."' ";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $b=$res->fetch();

                $s="select * from ".$dbname.".log_5masterbarang where kodebarang='".$kdbrg."' ";
                $r=$owlPDO->query($s) or die(print " Gagal: ".PDOException::getMessage());
                $r->setFetchMode(PDO::FETCH_ASSOC);
                $br=$r->fetch();

                if ($email!=''){
                $to = getUserEmail($karyawanid);
                $subject = "[Notifikasi] Kontrak Penjualan";
                $body = "<html>
                            <head>
                             <body>
                               <dd>Dengan Hormat,</dd><br>
                               <br>
                               Pada hari ini, tanggal " . date('d-m-Y') . " Kontrak Penjualan dengan No.Kontrak ".$param['nokontrak']." telah dirilis. Berikut detail dari Kontrak Penjualan Tersebut : <br>
                               <br>
                               <table border=0 cellspacing=0 valign=top>
                                    <tr>
                                        <td>Pembeli</td>
                                        <td> : </td>
                                        <td>".$b['namacustomer']."</td>
                                    </tr>

                                    <tr>
                                        <td>NPWP</td>
                                        <td> : </td>
                                        <td>".$b['npwp']."</td>
                                    </tr>
                                    <tr>
                                        <td>Jenis Barang</td>
                                        <td> : </td>
                                        <td>".$br['namabarang']."</td>
                                    </tr>
                                    <tr>
                                        <td>Kuantitas</td>
                                        <td> : </td>
                                        <td>".$bar['kuantitaskontrak']."</td>             
                                    </tr>

                                    <tr>
                                        <td>Kualitas</td>
                                        <td> : </td>
                                        <td>
                                            ".$ffaData."<br>
                                            ".$dobiData."<br>
                                            ".$mdaniData."<br>
                                            ".$moistData."<br>
                                            ".$dirtData."<br>
                                            ".$gradingData."<br>
                                        </td>             
                                    </tr>

                                    <tr>
                                        <td>Waktu Penyerahan</td>
                                        <td> : </td>
                                        <td>".$kettgl."</td>             
                                    </tr>
                                </table></td></tr>
                               <br>
                               Regards,<br>
                               ".$namaorganisasi.".
                             </body>
                            </head>
                         </html>";
                 $kirim = kirimEmail($to, '', $subject, $body);
			}
			*/

		break;

	case 'gocarinorefrensi':
		$textnoref = checkPostGet('textnoref', '');
		$tab = "";

		$tab .= "<table class=sortable cellspacing=1 border=0>
				<thead>
					<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>" . $_SESSION['lang']['noreferensi'] . "</td>
					<td align=center>" . $_SESSION['lang']['unit'] . "</td>
				</tr>
				</thead>
				<tbody>";

		$str = "select * from " . $dbname . ".pmn_scr where notransaksi like '%" . $textnoref . "%' and flag='0' and status='1'";
		$res = fetchData($str);
		$no = 0;
		foreach ($res as $key => $val) {
			$no++;

			$optPt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $val['kodeorg'] . "'");

			$tab .= " <tr class=rowcontent style='cursor:pointer' onclick=\"fillnorefrensi('" . $val['notransaksi'] . "','" . $optPt[$val['kodeorg']] . "','" . $val['buyer'] . "','" . $val['berikat'] . "','" . $val['komoditi'] . "','" . $val['kuantitas'] . "','" . $val['harga'] . "','" . $val['ppn'] . "','" . tanggalnormal($val['paymentdate']) . "','" . $val['bayarke'] . "','" . $val['kualitas1'] . "','" . $val['kualitas2'] . "','" . $val['kualitas3'] . "','" . $val['kualitas4'] . "')\">
					<td>" . $no . "</td>
					<td>" . $val['notransaksi'] . "</td>
					<td>" . $val['kodeorg'] . "</td>
				</tr>";
		}

		$tab .= "</tbody>";

		echo $tab;
		break;

 

	case 'showupload':
		$tab = "";

		$tab .= "<table cellspacing='1' border='0' id='uploadpopup'>
			<tr>
				<td>No. Kontrak</td>
				<td>:</td>
				<td>
					<label id='noppupload' style='font-weight:bold'>" . $nopp . "</label>
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
					<button class=mybutton onclick=\"submitfile()\">Submit</button>
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

	case 'form_ajukan':
		$tab = "";

		$optKrylevel = array();
		$jenispersetujuanx = "KTRKJUAL";
		$lokasitugas = $_SESSION['empl']['lokasitugas'];

		$optper4 = $optper3 = $optper2 = $optper1 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $jenispersetujuanx . "' and kodeunit='" . $lokasitugas . "'  order by level asc";
		$res = fetchData($str);
		foreach ($res as $key => $bar) {
			$whr		= " karyawanid='" . $bar['karyawanid'] . "'";
			$optnama 	= makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whr);

			$optKryx[$bar['level']][$bar['karyawanid']] = "<option value=" . $bar['karyawanid'] . ">" . $optnama[$bar['karyawanid']] . "</option>";
			$optKrylevel[$bar['level']] = $bar['level'];
		}
		$tab .= "<div><b>Nomor : " . $param['nokontrak'] . "</b></div><br>";
		$tab .= "<table cellspacing=1 border=0>
		<tr class=rowcontent hidden> 
			<td id=notran_aju>" . $param['nokontrak'] . "</td>
		</tr>";

		$jumlahlevel = count($optKrylevel);
		if ($jumlahlevel > 0) {
			for ($i = 1; $i <= $jumlahlevel; $i++) {
				$optKry = '';
				foreach ($optKryx[$i] as $key2 => $val) {
					$optKry .= $val;
				}
				$tab .= "<tr class=rowcontent>
						<td>Approval ke-" . $i . "</td>
						<td width=5px>:</td>
						<td><select id=kepada" . $i . " style='width:200px;'>" . $optKry . "</select></td>
					</tr>";
			}
		} else {
			$jumlahlevel = 1;
			$tab .= "<tr class=rowcontent>
					<td>Approval ke-1</td>
					<td width=5px>:</td>
					<td><select id=kepada1 style='width:200px;'></select></td>
				</tr>";
		}
		$tab .= "<tr class=rowcontent>
					<td hidden><input id=jenispersetujuanx style=display:none value=" . $jenispersetujuanx . "></td><td><input id=numrow style=display:none value=" . $jumlahlevel . "></td>
					<td align=left></td>
					</tr>
				<tr>
					<td align=left></td>
					<td align=left></td>
					<td align=center><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>
				</table>";

		echo $tab;
		break;

	case 'ajukan':
		$kepada = checkPostGet('kepada', '');
		$jenispersetujuanx = checkPostGet('jenispersetujuanx', '');
		$nokontrak = checkPostGet('nokontrak', '');

		if ($kepada == '') {
			throw new PDOException('Isikan nama penyetuju.');
		}

		try {
			// Update status kontrak menjadi 'diajukan'
			$str2 = "update " . $dbname . ".pmn_kontrakjual set posting='9' where nokontrak = '" . $nokontrak . "'";
			$owlPDO->exec($str2);

			// Insert ke tabel approval untuk setiap level
			$arrkepada = explode('###', $kepada);
			foreach ($arrkepada as $i => $karyawanid) {
				if (trim($karyawanid) != '') {
					$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
						`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
						values ('','" . $nokontrak . "','" . $jenispersetujuanx . "','" . ($i + 1) . "','" . $karyawanid . "','0','','','')";
					$owlPDO->exec($str);
				}
			}
			echo "OK";
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
		}

		break;



	// case 'submitfile':

	// $tgl = date("YmdHis");
	// // exit("error : ".$tgl);
	// $data = $_POST;

	// if($data['fileupload']!='')
	// {
	// if($_FILES['file']['error']==0)
	// {
	// $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
	// $newfilename = str_replace($filetype,'',$_FILES['file']['name']);
	// $filename = $newfilename."_".$tgl."".$filetype;
	// $file_tmpname = $_FILES['file']['tmp_name'];		

	// if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.rar'))
	// {
	// if($_FILES['file']['size'] <= 250000)
	// {

	// $str = "insert into ".$dbname.".listfileupload (id,notransaksi, namafile, formaticon, status, createdby, createdtime) values ('','".$data['rnopp']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
	// try
	// {
	// $owlPDO->exec($str);
	// move_uploaded_file($file_tmpname,"fileupload/kontrakjual/$filename");
	// }
	// catch(PDOException $e)
	// {
	// echo " Gagal," . addslashes($e->getMessage());
	// }
	// }
	// else
	// {
	// exit("warning : Ukuran file upload maksimal 250kb");
	// }
	// }else{
	// exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
	// }
	// }
	// }
	// break;



	// case 'loadfiles':
	// $no = 0;
	// $tab = "";
	// $str="select * from ".$dbname.".pmn_kontrakjual where nokontrak = '".$nopp."'";
	// $resv=fetchData($str);
	// foreach($resv as $bar => $barv){
	// $close = $barv['close'];	
	// }

	// $str="select * from ".$dbname.".listfileupload where notransaksi = '".$nopp."' and status='1'";
	// $res=fetchData($str);
	// if(empty($res))
	// {
	// $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	// }
	// else
	// {
	// foreach($res as $key=>$val)
	// {
	// $no++;
	// $tab.="<tr id='ppDetailTable' class=rowcontent>
	// <td style='text-align:center'>".$no."</td>";

	// if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
	// {
	// $tab.="<td style='text-align:center'>
	// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
	// </td>";
	// }
	// elseif($val['formaticon']=='.png')
	// {
	// $tab.="<td style='text-align:center'>
	// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
	// </td>";
	// }
	// elseif($val['formaticon']=='.pdf')
	// {
	// $tab.="<td style='text-align:center'>
	// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
	// </td>";
	// }
	// elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
	// {
	// $tab.="<td style='text-align:center'>
	// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
	// </td>";
	// }
	// elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
	// {
	// $tab.="<td style='text-align:center'>
	// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
	// </td>";
	// }
	// else
	// {
	// $tab.="<td style='text-align:center'>
	// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
	// </td>";
	// }

	// $tab.="<td style='text-align:left'>".$val['namafile']."</td>
	// <td align=center>
	// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
	// if($close==0){
	// $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$nopp."','".$val['namafile']."');\" >";
	// }
	// $tab."	</td>
	// </tr>";
	// }	
	// }
	// echo $tab;
	// break;	


	// case 'deletefile':
	// $str="delete from ".$dbname.".listfileupload where notransaksi='".$nopp."' and namafile='".$namafile."'";
	// try
	// {
	// $owlPDO->exec($str);
	// $path = "fileupload/kontrakjual/".$namafile;
	// unlink($path);
	// }
	// catch(PDOException $e)
	// {
	// echo " Gagal," . addslashes($e->getMessage());
	// }
	// break;	


	default:
		break;
}
