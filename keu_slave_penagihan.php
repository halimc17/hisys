<?
ini_set('display_errors', 0);
error_reporting(0);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
require_once('phpqrcode/qrlib.php');

require('lib/fpdf.php');
require('lib/htmlparser.inc');
require('lib/htmltofpdf.php');

$optjenis = $optkapalponton = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";


$optjenis .= "<option value='KPL'>" . $_SESSION['lang']['kapal'] . "</option>";
$optjenis .= "<option value='PNT'>" . $_SESSION['lang']['ponton'] . "</option>";
$optjenis .= "<option value='TRK'>Truck</option>";



#= array kodesupplier
$str = "SELECT a.supplierid,a.namasupplier,a.kodept FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSKUD','SUPPLIERTBSAFI') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$nmsupplier[$bar['supplierid']] = $bar['namasupplier'];
	$kodesupplier[$bar['kodept']] = $bar['supplierid'];
}


$str = "select * from " . $dbname . ".organisasi  where length(kodeorganisasi)='4'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$kodept[$bar['kodeorganisasi']] = $bar['induk'];
	if ($bar['tipe'] == 'KANWIL') {
		$kodero[$bar['induk']] = $bar['kodeorganisasi'];
	}
}


$param = $_POST;
if ($_GET['proses'] != '') {
	$param = $_GET;
}
$proses = checkPostGet('proses', '');
$optnmCust =  makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$namasupplier =  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$kodeorgkud = makeOption($dbname, 'kebun_5namakud', 'kodesupplier,afdeling');
$path   = "fileupload/keu_penagihan/";

$noinvoice = checkPostGet('noinvoice', '');
$kriteriaefil = checkPostGet('kriteriaefil', '');
$emodul = "PGH";

$jnsp = '';
$jnsp = array(
	'1' => 'Badan Usaha Industri Semen',
	'2' => 'Badan Usaha Industri Rokok',
	'3' => 'Badan Usaha Industri Kertas',
	'4' => 'Badan Usaha Industri Baja',
	'5' => 'Badan Usaha Industri Otomotif',
	'6' => 'Pembelian Barang Oleh Bendaharawan',
	'7' => 'Nilai Impor Bank Devisa/Ditjen Bea dan Cukai',
	'8' => 'Hasil Lelang',
	'9' => 'Penjualan Migas Oleh Pertamina',
	'10' => 'Pembelian Barang Keperluan Industri Dalam Sektor Perhutanan',
	'11' => 'Pembelian Barang Keperluan Industri Dalam Sektor Perkebunan',
	'12' => 'Pembelian Barang Keperluan Industri Dalam Sektor Pertanian',
	'13' => 'Pembelian Barang Keperluan Industri Dalam Sektor Perikanan'
);
$nmakun = '';
$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

$nmkapalponton = makeOption($dbname, 'pmn_5kapalponton', 'kode,nama');
$namajeniskapalponton = array(
	'KPL' => 'Kapal',
	'PNT' => 'Ponton',
	'TRK' => 'Truck'
);
$stream = '';
switch ($proses) {

	case 'getbarang':

		# Make Option Barang
		$namabarang = makeOption($dbname, "log_5masterbarang", "kodebarang,namabarang", "kelompokbarang='400'");

		$optionBarang = $optionCust = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		# Others
		if ($param['tipeinvoice'] != 'others') {
			$sql = selectQuery($dbname, "keu_5jenispenagihandt", "*", "kodejenis='" . $param['tipeinvoice'] . "'");
			$res = fetchData($sql, "OBJECT");

			foreach ($res as $v) {
				$selectedtix = "";

				if ($param['kodebarang'] != '') {
					if ($param['kodebarang'] == $v->kodebarang) {
						$selectedtix = "selected";
					}
				}

				$optionBarang .= "<option $selectedtix value='" . $v->kodebarang . "'>" . $namabarang[$v->kodebarang] . "</option>";
			}
		} else {
			$sql = "select namabarang,kodebarang from " . $dbname . ".log_5masterbarang where `kelompokbarang`='400'";
			$res = fetchData($sql, "OBJECT");
			foreach ($res as $v) {
				$selectedtix = "";

				if ($param['kodebarang'] != '') {
					if ($param['kodebarang'] == $v->kodebarang) {
						$selectedtix = "selected";
					}
				}

				$optionBarang .= "<option $selectedtix value=" . $v->kodebarang . ">" . $v->namabarang . "</option>";
			}
		}

		if ($param['tipeinvoice'] == 'FEM') {
			$qCustomer = selectQuery($dbname, "setup_parameterappl", "SUBSTRING_INDEX(kodeparameter, '/', -1) as kodecustomer", "kodeparameter LIKE 'MF_COA/%'");
			$rCustomer = fetchData($qCustomer);
			foreach ($rCustomer as $row) {
				$optionCust .= "<option value='" . $row['kodecustomer'] . "'>" . $optnmCust[$row['kodecustomer']] . "</option>";
			}
		} else {
			$qCustomer = selectQuery($dbname, "pmn_4customer", "distinct kodecustomer, namacustomer", "", "namacustomer asc");
			$rCustomer = fetchData($qCustomer);
			foreach ($rCustomer as $row) {
				$optionCust .= "<option value='" . $row['kodecustomer'] . "'>" . $row['namacustomer'] . "</option>";
			}
		}

		$data = [
			'optKodebarang' => $optionBarang,
			'optKodecustomer' => $optionCust,
		];

		echo json_encode($data);
		break;

	case 'loaddatadetail':
		$stream .= "<div id=detail1 style=display:block;>";
		$stream .= "<fieldset><legend><b>" . $_SESSION['lang']['detail'] . "</b></legend>";

		$stream .= " <table cellpading=1 cellspacing=1 border=0 class=sortable>
             <thead>
                <tr class=rowheader>
                    <td  align=center>ITEM</td>
                    <td  align=center>QUANTITY<br>(Kg)</td>
                    <td  align=center>DESCRIPTION</td>
                    <td  align=center>UNIT<br>PRICE<br>(RP)</td>
                    <td  align=center>AMOUNT</td> 
                    <td  align=center>ROUNDING AMOUNT</td> 
                </tr>  
            </thead>";


		#= jika TBS
		$str = "select 
				sum(totalrp) as totalrp,
				sum(kgnetto) as kgnetto,
				periode,rpkg,intiplasma,tanggalreferensi 
				from " . $dbname . ".keu_penagihandt where noinvoice='" . $param['noinvoice'] . "' group by periode,rpkg";
		// echo $str;exit("Error:A");
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$arrperdt[$bar['periode']] = $bar['periode'];
			$arrrpkgdt[$bar['rpkg']] = $bar['rpkg'];
			$listrpkgdt[$bar['periode']][$bar['rpkg']] = $bar['rpkg'];
			$totalkgnetto[$bar['periode']][$bar['rpkg']] = $bar['kgnetto'];
			$totalrpdt[$bar['periode']][$bar['rpkg']] = round($bar['totalrp']);
			$totalrpdtactual[$bar['periode']][$bar['rpkg']] = $bar['totalrp'];
			$tanggalreferensi = $bar['tanggalreferensi'];
			$intiplasma[$bar['periode']][$bar['rpkg']] = $bar['intiplasma'];
			// $namamatauang[$bar['kode']]=$bar['matauang'];
		}


		#= cari min max tahun
		$str = "select max(tahuntanam) as maxthntnm,min(tahuntanam) as minthntnm,rpkg,periode 
					from " . $dbname . ".keu_penagihandt 
					where noinvoice='" . $param['noinvoice'] . "' group by periode,rpkg";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$maxthntnm[$bar['periode']][$bar['rpkg']] = $bar['maxthntnm'];
			$minthntnm[$bar['periode']][$bar['rpkg']] = $bar['minthntnm'];
			// $namamatauang[$bar['kode']]=$bar['matauang'];
		}

		foreach ($arrperdt as $perdt) {
			$nodt = 0;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td></td>";
			$stream .= "<td></td>";
			$stream .= "<td colspan=4><u>Periode " . romawi($perdt) . "</u></td>";

			$stream .= "</tr>";
			foreach ($arrrpkgdt as $rpkgdt) {
				if ($listrpkgdt[$perdt][$rpkgdt] != '') {
					// $keterangandt="";

					if ($intiplasma[$perdt][$rpkgdt] == 'kud') {
						$intiplasmadata = "TBS Petani ";
					} else {
						$intiplasmadata = "TBS Inti ";
					}
					$expltanggalreferensi = explode('-', $tanggalreferensi);

					$keterangandt = " " . $intiplasmadata . " - " . numToMonth(intval($expltanggalreferensi[1]), 'I', 'long') . " " . $expltanggalreferensi[0] . " 
											tahun  tanam " . $minthntnm[$perdt][$rpkgdt] . " s/d " . $maxthntnm[$perdt][$rpkgdt] . " ";
					$nodt++;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td>" . $nodt . "</td>";
					$stream .= "<td align=right>" . number_format($totalkgnetto[$perdt][$rpkgdt]) . "&nbsp;&nbsp;</td>";
					$stream .= "<td>" . $keterangandt . "</td>";
					$stream .= "<td>" . number_format($rpkgdt, 2) . "</td>";
					$stream .= "<td align=right>" . number_format($totalrpdtactual[$perdt][$rpkgdt], 2) . "</td>";
					$stream .= "<td align=right>" . number_format($totalrpdt[$perdt][$rpkgdt], 2) . "</td>";
					$stream .= "</tr>";
					@$subtotal[$perdt] += round($totalrpdt[$perdt][$rpkgdt]);
					@$subtotalactual[$perdt] += $totalrpdtactual[$perdt][$rpkgdt];
				}
			}
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td colspan=4>" . $_SESSION['lang']['subtotal'] . "</td>";
			$stream .= "<td align=right>" . number_format($subtotalactual[$perdt], 2) . "</td>";
			$stream .= "<td align=right>" . number_format($subtotal[$perdt], 2) . "</td>";
			@$grandtotal += $subtotal[$perdt];
			@$grandtotalactual += $subtotalactual[$perdt];
			$stream .= "</tr>";
		}
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td colspan=4><b>" . $_SESSION['lang']['grnd_total'] . "</b></td>";
		$stream .= "<td align=right><b>" . number_format($grandtotalactual, 2) . "</b></td>";
		$stream .= "<td align=right><b>" . number_format($grandtotal, 2) . "</b></td>";
		$stream .= "</tr>";
		$stream .= "</table><br>";
		$stream .= "<button class=mybutton onclick=showdetaildata2()>" . $_SESSION['lang']['tampilkan'] . "</button>";
		$stream .= "<button class=mybutton onclick=hidedetaildata2()>" . $_SESSION['lang']['tutup'] . "</button>";
		$stream .= "</fieldset>";
		$stream .= "</div><br>";

		$stream .= "<div id=detail2 style=display:none;>";
		$stream .= "<fieldset><legend><b>" . $_SESSION['lang']['detail'] . "</b></legend>
             <table cellpading=1 cellspacing=1 border=0 class=sortable>
             <thead>
                <tr class=rowheader>
                    <td  align=center>" . $_SESSION['lang']['nourut'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['noTiket'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['nospb'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['periode'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tbs'] . " 1</td>
                    <td  align=center>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tbs'] . " 2</td>
                    <td  align=center>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['berat'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['potongan'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['beratBersih'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['rpperkg'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['total'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['noreferensi'] . "</td>      
                    <td  align=center>" . $_SESSION['lang']['supplier'] . "</td>      
                </tr>  
            </thead>";

		$str = "select * from " . $dbname . ".keu_penagihandt where noinvoice='" . $param['noinvoice'] . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {

			#= nomor supplier afiliasi
			$str1 = "select supplier from " . $dbname . ".kebun_tbskud  
				where notransaksi='" . $bar['noreferensi'] . "'";
			$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1 = $res1->fetch();
			$supplierkud = $bar1['supplier'];


			@$no += 1;
			$stream .= "<tr class=rowcontent id=rowdetail" . $no . ">";
			$stream .= "<td align=center>" . $no . "</td>";
			$stream .= "<td align=left>" . $bar['notransaksi'] . "</td>";
			$stream .= "<td align=left>" . $bar['notiket'] . "</td>";
			$stream .= "<td align=left>" . $bar['nospb'] . "</td>";
			$stream .= "<td align=center>" . romawi($bar['periode']) . "</td>";
			$stream .= "<td align=left>" . tanggalnormal($bar['tanggalreferensi']) . "</td>";
			$stream .= "<td align=left>" . tanggalnormal($bar['tanggaltbs1']) . "</td>";
			$stream .= "<td align=left>" . tanggalnormal($bar['tanggaltbs2']) . "</td>";
			$stream .= "<td align=left>" . $bar['tahuntanam'] . "</td>";
			$stream .= "<td align=right>" . number_format($bar['kgbruto'], 2) . "</td>";
			$stream .= "<td align=right>" . number_format($bar['kgpotongan'], 2) . "</td>";
			$stream .= "<td align=right>" . number_format($bar['kgnetto'], 2) . "</td>";
			$stream .= "<td align=right>" . number_format($bar['rpkg'], 2) . "</td>";
			$stream .= "<td align=right>" . number_format($bar['totalrp'], 2) . "</td>";
			$stream .= "<td align=left>" . $bar['noreferensi'] . "</td>";
			$stream .= "<td align=left>" . $nmsupplier[$supplierkud] . "</td>";
			$stream .= "</tr>";
			$tkgbruto += $bar['kgbruto'];
			$tkgpotongan += $bar['kgpotongan'];
			$tkgnetto += $bar['kgnetto'];
			$ttotalrp += $bar['totalrp'];
		}



		$stream .= "<tr class=rowcontent id=rowdetail" . $no . ">";
		$stream .= "<td align=center colspan=9>Total</td>";
		$stream .= "<td align=right>" . number_format($tkgbruto, 2) . "</td>";
		$stream .= "<td align=right>" . number_format($tkgpotongan, 2) . "</td>";
		$stream .= "<td align=right>" . number_format($tkgnetto, 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . number_format($ttotalrp, 2) . "</td>";
		$stream .= "<td align=right colspan=2></td>";
		$stream .= "</tr>";
		$stream .= "</table>";
		$stream .= "</fieldset>";
		$stream .= "</div>";
		echo $stream;
		break;
	case 'detailtbs':
		$bgcp = "bgcolor=#ff9900";
		$stream .= "
             <table cellpading=1 cellspacing=1 border=1 class=sortable>
             <thead>
                <tr class=rowheader>
                    <td " . $bgcp . " align=center>" . $_SESSION['lang']['nourut'] . "</td>
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['noTiket'] . "</td>
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['nospb'] . "</td>
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['periode'] . "</td>
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tbs'] . " 1</td>
                    <td " . $bgcp . " align=center>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tbs'] . " 2</td>
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['berat'] . "</td>    
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['potongan'] . "</td>    
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['beratBersih'] . "</td>    
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['rpperkg'] . "</td>    
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['total'] . "</td>    
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['noreferensi'] . "</td>      
                    <td " . $bgcp . "  align=center>" . $_SESSION['lang']['supplier'] . "</td>      
                </tr>  
            </thead>";

		$str = "select * from " . $dbname . ".keu_penagihandt where noinvoice='" . $param['noinvoice'] . "' ";

		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {

			// #= nomor supplier afiliasi
			$supplierkud = '';
			if ($bar['noreferensi'] != '') {
				$str1 = "select distinct supplier from " . $dbname . ".kebun_tbskud  
                        where notransaksi='" . $bar['noreferensi'] . "'";
				$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_ASSOC);
				$bar1 = $res1->fetch();
				$supplierkud = $bar1['supplier'];
			}



			@$no += 1;
			$stream .= "<tr class=rowcontent id=rowdetail" . $no . ">";
			$stream .= "<td align=center>" . $no . "</td>";
			$stream .= "<td align=left>" . $bar['notransaksi'] . "</td>";
			$stream .= "<td align=left>" . $bar['notiket'] . "</td>";
			$stream .= "<td align=left>" . $bar['nospb'] . "</td>";
			$stream .= "<td align=center>" . romawi($bar['periode']) . "</td>";
			$stream .= "<td align=left>" . tanggalnormal($bar['tanggalreferensi']) . "</td>";
			$stream .= "<td align=left>" . tanggalnormal($bar['tanggaltbs1']) . "</td>";
			$stream .= "<td align=left>" . tanggalnormal($bar['tanggaltbs2']) . "</td>";
			$stream .= "<td align=left>" . $bar['tahuntanam'] . "</td>";
			$stream .= "<td align=right>" . number_format($bar['kgbruto'], 2) . "</td>";
			$stream .= "<td align=right>" . number_format($bar['kgpotongan'], 2) . "</td>";
			$stream .= "<td align=right>" . number_format($bar['kgnetto'], 2) . "</td>";
			$stream .= "<td align=right>" . number_format($bar['rpkg'], 2) . "</td>";
			$stream .= "<td align=right>" . number_format($bar['totalrp'], 2) . "</td>";
			$stream .= "<td align=left>" . $bar['noreferensi'] . "</td>";
			$stream .= "<td align=left>" . $nmsupplier[$supplierkud] . "</td>";
			$stream .= "</tr>";
			$tkgbruto += $bar['kgbruto'];
			$tkgpotongan += $bar['kgpotongan'];
			$tkgnetto += $bar['kgnetto'];
			$ttotalrp += $bar['totalrp'];
		}


		$stream .= "<tr class=rowcontent id=rowdetail" . $no . ">";
		$stream .= "<td align=center colspan=9>Total</td>";
		$stream .= "<td align=right>" . number_format($tkgbruto, 2) . "</td>";
		$stream .= "<td align=right>" . number_format($tkgpotongan, 2) . "</td>";
		$stream .= "<td align=right>" . number_format($tkgnetto, 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . number_format($ttotalrp, 2) . "</td>";
		$stream .= "<td align=right colspan=2></td>";
		$stream .= "</tr>";
		$stream .= "</table>";
		$stream .= "Print Time : " . date('H:i:s, d/m/Y') . "<br>By : " . $_SESSION['empl']['name'];
		$tglSkrg = date("YmdHis");
		$nop_ = "detailTbs__" . $tglSkrg;
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

	case 'savedetail':

		if (tanggalsystemn($param['tanggal']) < '2022-04-01') {
			$persentasesatu = '1.1';
			$persentasedua = '0.1';
		} else {
			$persentasesatu = '1.11';
			$persentasedua = '0.11';
		}

		#= cek apakah noinvoice kosong tidak bisa insert
		if ($param['noinvoice'] == '') {
			exit("Warning:No. Invoice Masih Kosong");
		}
		// exit("Error".$param['currRow']);
		#= jika baris pertama delete detail dlu semua
		if ($param['currRow'] == '1') {
			#= delete 1st
			$str = "delete from " . $dbname . ".keu_penagihandt where noinvoice='" . $param['noinvoice'] . "'";
			// exit("Error:$str");
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		}


		if ($param['cekdetail'] == '1') {

			$data = array(
				'noinvoice' => $param['noinvoice'],
				'nokontrak' => '',
				'nodo' => '',
				'beratkirim' => '',
				'tanggalkirim' => '',
				'nilairupiah' => '',
				'notransaksi' => $param['notransaksidetail'],
				'notiket' => $param['notiketdetail'],
				'nospb' => $param['nospbdetail'],
				'periode' => $param['periodedetail'],
				'tanggalreferensi' => tanggalsystemn($param['tanggalreferensidetail']),
				'tanggaltbs1' => tanggalsystemn($param['tanggaltbs1detail']),
				'tanggaltbs2' => tanggalsystemn($param['tanggaltbs2detail']),
				'tanggalspb' => tanggalsystemn($param['tanggalspbdetail']),
				'tanggalpks' => tanggalsystemn($param['tanggalpksdetail']),
				'blok' => $param['blokdetail'],
				'tahuntanam' => $param['tahuntanamdetail'],
				'kgbruto' => $param['kgbrutodetail'],
				'kgpotongan' => $param['kgpotongandetail'],
				'kgnetto' => $param['kgnettodetail'],
				'rpkg' => $param['rpkgdetail'],
				'totalrp' => $param['totalrpdetail'],
				'intiplasma' => $param['tipetbsdetail'],
				'kodesupplier' => $param['kodesupplierdetail'],
				'noreferensi' => $param['noreferensidetail']
			);
			// print_r($data);
			// exit("Error");
			$cols = array();
			foreach ($data as $key => $row) {
				$cols[] = $key;
			}
			$str = insertQuery($dbname, 'keu_penagihandt', $data, $cols);
			// exit("Error:$str");

			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		}


		#= update nilai ht
		#= ambil total nilai dulu
		$str = "select sum(totalrp) as totalrpdt,sum(kgnetto) as kgdt,notransaksi from " . $dbname . ".keu_penagihandt where noinvoice='" . $param['noinvoice'] . "' group by notransaksi ";
		// $bar=fetchdata($str);
		// $totalrpdt = $bar[0]['totalrpdt'];
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$totalrpdt +=  floor($bar['totalrpdt']);
			$kgdt += $bar['kgdt'];
		}

		# Nilai PPn di bulatkan kebawah
		$nilppndt = floor($totalrpdt * $persentasedua);
		// $nilppndt =  number_format($nilppndt,2);
		// $nilppndt =  explode('.',$nilppndt);
		// $nilppndt = $nilppndt[0];
		$nilppndt =  str_replace(',', '', $nilppndt);

		# ========================= #
		# PPH TBS
		# ========================= #
		$sql = selectQuery($dbname, "keu_penagihanht", "*", "noinvoice='" . $param['noinvoice'] . "'");
		$res = fetchData($sql, "OBJECT")[0];
		$param['kodecustomersvdetail'] = $res->kodecustomer;

		$sql = selectQuery($dbname, "pmn_5akunpajak", "*", "kodecustomer='" . $param['kodecustomersvdetail'] . "'");
		$respphcust = fetchData($sql, "OBJECT")[0];

		if (count(fetchData($sql)) <= 0) {
			exit("<label hidden>Warning</label> Pajak PPh untuk Kode Pelanggan {$param['kodecustomersvdetail']} belum di Setup di Menu (Pemasaran > Setup > Pelanggan > Setup Pajak)");
		}

		$param['pphcustnoakun'] = $respphcust->noakun;
		$param['tarifcustnoakun'] = $respphcust->tarif;
		$param['nilaipphcust'] = floor(($totalrpdt * $param['tarifcustnoakun']) / 100);
		# ========================= #
		# END	
		# ========================= #

		// echo "<pre>";
		// print_r($param);
		// exit('warning');

		#= update ht
		$str = "update " . $dbname . ".keu_penagihanht set 
			nilaiinvoice='" . $totalrpdt . "',kuantitas='" . $kgdt . "',nilaippn='" . $nilppndt . "',
			noakunpph='" . $param['pphcustnoakun'] . "', persenpph='" . $param['tarifcustnoakun'] . "', nilaipph='" . $param['nilaipphcust'] . "'
			where noinvoice='" . $param['noinvoice'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}

		echo number_format($totalrpdt) . "####" . number_format($nilppndt) . "####" . $kgdt;

		// exit("Error:$str");

		break;


	case 'previewdetail':

		$whrt = '';
		if ($param['tipetbsdetail'] == '') {
			exit("Error:Tipe TBS tidak boleh kosong");
		}

		if ($param['tahuntanam'] != '') {
			$whrt .= "and tahuntanam='" . $param['tahuntanam'] . "'";
		}

		$stream = "";
		$stream .= "<fieldset><legend><b>" . $_SESSION['lang']['data'] . " " . $_SESSION['lang']['detail'] . "</b></legend>
             <table cellpading=1 cellspacing=1 border=0 class=sortable>
             <thead>
                <tr class=rowheader>
                    <td  align=center>" . $_SESSION['lang']['nourut'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['noTiket'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['nospb'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['tahuntanam'] . " " . $_SESSION['lang']['nospb'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['periode'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['tanggal'] . "<br>" . $_SESSION['lang']['dokumen'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['tanggal'] . "<br>SPB</td>
                    <td  align=center>" . $_SESSION['lang']['tanggal'] . "<br>" . $_SESSION['lang']['pabrik'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['blok'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td  align=center>" . $_SESSION['lang']['berat'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['potongan'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['beratBersih'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['rpperkg'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['total'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['noreferensi'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['kodesupplier'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['namasupplier'] . "</td>    
                    <td  align=center>" . $_SESSION['lang']['action'] . "
						<br><input type=checkbox id=cekalldetail onclick=cekalldetail()>
					</td>
                </tr>  
            </thead><tbody id=contentdetail>";

		$str = " select notiket from " . $dbname . ".keu_penagihandt where noinvoice='" . $noinvoice . "'";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$notiketsave[$bar['notiket']] = 1;
		}


		// $arrthntanamspb=makeOption($dbname,'kebun_spb_vw','nospb,tahuntanam');
		$arrthntanamspb = makeOption($dbname, 'kebun_spb_detail_vw', 'nospb,tahuntanam');

		if ($param['tipetbsdetail'] == 'ext') {

			$str = "select * from " . $dbname . ".kebun_tbsjual where kodero='" . $param['kodeorganisasi'] . "' and tanggalpks between '" . tanggalsystemn($param['tanggal1detail']) . "'  and '" . tanggalsystemn($param['tanggal2detail']) . "' " . $whrt . " and posting=1 ";
			$res = fetchdata($str);
			foreach ($res as $bar) {

				$no += 1;
				$stream .= "<tr class=rowcontent id=rowdetail" . $no . ">";
				$stream .= "<td align=center>" . $no . "</td>";
				$stream .= "<td align=left id=notransaksidetail" . $no . ">" . $bar['notransaksi'] . "</td>";
				$stream .= "<td align=left id=notiketdetail" . $no . ">" . $bar['notiket'] . "</td>";
				$stream .= "<td align=left id=nospbdetail" . $no . ">" . $bar['nospb'] . "</td>";
				// $stream.="<td align=right id=tahuntanamdetail".$no.">".$arrthntanamspb[$bar['nospb']]."</td>";
				$stream .= "<td align=right id=tahuntanamdetail" . $no . ">" . $bar['tahuntanam'] . "</td>";
				// $stream.="<td align=center id=periodedetail".$no.">".$periode."</td>";
				$stream .= "<td align=center id=periodedetail" . $no . ">" . substr($bar['tanggal'], 0, 7) . "</td>";
				$stream .= "<td align=left id=tanggalreferensidetail" . $no . ">" . tanggalnormal($bar['tanggal']) . "</td>";
				$stream .= "<td align=left id=tanggaltbs1detail" . $no . " hidden>" . tanggalnormal($bar['tanggaltbs1']) . "</td>";
				$stream .= "<td align=left id=tanggaltbs2detail" . $no . " hidden>" . tanggalnormal($bar['tanggaltbs2']) . "</td>";
				$stream .= "<td align=left id=tanggalspbdetail" . $no . ">" . tanggalnormal($bar['tanggalspb']) . "</td>";
				$stream .= "<td align=left id=tanggalpksdetail" . $no . ">" . tanggalnormal($bar['tanggalpks']) . "</td>";
				$stream .= "<td align=left id=blokdetail" . $no . ">" . $bar['blok'] . "</td>";
				$stream .= "<td align=right id=thntanamspb" . $no . ">" . $bar['tahuntanam'] . "</td>";
				$stream .= "<td align=right id=kgbrutodetail" . $no . ">" . number_format($bar['kgbruto'], 2) . "</td>";
				$stream .= "<td align=right id=kgpotongandetail" . $no . ">" . number_format($bar['kgpotongan'], 2) . "</td>";
				$stream .= "<td align=right id=kgnettodetail" . $no . ">" . number_format($bar['kgnetto'], 2) . "</td>";
				$stream .= "<td align=right id=rpkgdetail" . $no . ">" . number_format($bar['rpkg'], 2) . "</td>";
				$stream .= "<td align=right id=totalrpdetail" . $no . ">" . number_format($bar['totalrp'], 2) . "</td>";
				$stream .= "<td align=left id=noreferensidetail" . $no . "></td>";
				$stream .= "<td align=left id=kodesupplierdetail" . $no . ">" . $bar['kodecustomer'] . "</td>";
				$stream .= "<td align=left>" . $optnmCust[$bar['kodecustomer']] . "</td>";
				$stream .= "<td align=center><input type=checkbox id=cekdetail" . $no . " " . $cek . "></td>";
				$stream .= "</tr>";
			}
		} else {

			if ($param['tipetbsdetail'] != '') {
				if ($param['tipetbsdetail'] == 'inti') {
					$wheretipetbsdetail .= " and noreferensi=''";
				} else {
					$wheretipetbsdetail .= " and noreferensi!=''";
				}
			}

			// $str="select * from ".$dbname.".kebun_tbsafiliasi where ropemilik='".$param['kodeorganisasi']."' 
			// $str="select * from ".$dbname.".kebun_tbsafiliasi where rounit='".$param['kodeorganisasi']."' 
			$str = "select * from " . $dbname . ".kebun_tbsafiliasi where ropemilik='" . $param['kodeorganisasi'] . "' 
				and substr(tanggalpks,1,10) between '" . tanggalsystemn($param['tanggal1detail']) . "'  and '" . tanggalsystemn($param['tanggal2detail']) . "'
				" . $wheretipetbsdetail . " " . $whrt . " and posting=1";


			// if($_SESSION['standard']['username']=='tim.owl3'){
			// echo $str;exit();
			// }


			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if (@$notiketsave[$bar['notiket']] == 1) {
					$cek = "checked=true";
				} else {
					$cek = "";
				}


				#= nomor supplier afiliasi
				$str1 = "select supplier from " . $dbname . ".kebun_tbskud  
				where notransaksi='" . $bar['noreferensi'] . "' ";
				$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_ASSOC);
				$bar1 = $res1->fetch();
				$supplierkud = $bar1['supplier'];


				if (substr($bar['tanggal'], 8, 2) <= '15') {
					$periode = '1';
				} else {
					$periode = '2';
				}


				$no += 1;
				$stream .= "<tr class=rowcontent id=rowdetail" . $no . ">";
				$stream .= "<td align=center>" . $no . "</td>";
				$stream .= "<td align=left id=notransaksidetail" . $no . ">" . $bar['notransaksi'] . "</td>";
				$stream .= "<td align=left id=notiketdetail" . $no . ">" . $bar['notiket'] . "</td>";
				$stream .= "<td align=left id=nospbdetail" . $no . ">" . $bar['nospb'] . "</td>";
				$stream .= "<td align=center id=periodedetail" . $no . ">" . $periode . "</td>";
				$stream .= "<td align=left id=tanggalreferensidetail" . $no . ">" . tanggalnormal($bar['tanggal']) . "</td>";
				$stream .= "<td align=left id=tanggaltbs1detail" . $no . " hidden>" . tanggalnormal($bar['tanggaltbs1']) . "</td>";
				$stream .= "<td align=left id=tanggaltbs2detail" . $no . " hidden>" . tanggalnormal($bar['tanggaltbs2']) . "</td>";
				$stream .= "<td align=left id=tanggalspbdetail" . $no . ">" . tanggalnormal($bar['tanggalspb']) . "</td>";
				$stream .= "<td align=left id=tanggalpksdetail" . $no . ">" . tanggalnormal($bar['tanggalpks']) . "</td>";
				$stream .= "<td align=left id=blokdetail" . $no . ">" . $bar['blok'] . "</td>";
				$stream .= "<td align=right id=thntanamspb" . $no . ">" . $bar['tahuntanam'] . "</td>";
				$stream .= "<td align=right id=tahuntanamdetail" . $no . ">" . $arrthntanamspb[$bar['nospb']] . "</td>";
				$stream .= "<td align=right id=kgbrutodetail" . $no . ">" . number_format($bar['kgbruto'], 2) . "</td>";
				$stream .= "<td align=right id=kgpotongandetail" . $no . ">" . number_format($bar['kgpotongan'], 2) . "</td>";
				$stream .= "<td align=right id=kgnettodetail" . $no . ">" . number_format($bar['kgnetto'], 2) . "</td>";
				$stream .= "<td align=right id=rpkgdetail" . $no . ">" . number_format($bar['rpkg'], 2) . "</td>";
				$stream .= "<td align=right id=totalrpdetail" . $no . ">" . number_format($bar['totalrp'], 2) . "</td>";
				$stream .= "<td align=left id=noreferensidetail" . $no . ">" . $bar['noreferensi'] . "</td>";
				$stream .= "<td align=left id=kodesupplierdetail" . $no . ">" . $supplierkud . "</td>";
				$stream .= "<td align=left>" . $nmsupplier[$supplierkud] . "</td>";
				$stream .= "<td align=center><input type=checkbox id=cekdetail" . $no . " " . $cek . "></td>";
				$stream .= "</tr>";
			}
		}
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td colspan=20 align=center>
			<button class=mybutton onclick=savealldetail(" . $no . ");>" . $_SESSION['lang']['proses'] . "</button></td>";
		$stream .= "</tr>";
		echo $stream;
		break;

	case 'getaddkapalponton':
		$str = "SELECT * FROM " . $dbname . ".pmn_5kapalponton where jenis='" . $param['jeniskapalponton'] . "'";
		// exit("Error:$str");
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optkapalponton .= "<option value=" . $bar['kode'] . ">" . $bar['nama'] . "</option>";
		}
		echo $optkapalponton;
		break;

	case 'saveaddkapalponton':
		$str = "insert into " . $dbname . ".keu_penagihandt_kapalponton 
            (noinvoice,jenis,kode,createby,createtime) values 
            ('" . $param['noinvoice'] . "','" . $param['jeniskapalponton'] . "','" . $param['namakapalponton'] . "',
			'" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d') . "')";
		// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " error: code 11256\n " . $e->getMessage() . "<br/>";
			die();
		}
		break;

	case 'deleteaddkapalponton':
		$str = "delete from  " . $dbname . ".keu_penagihandt_kapalponton 
            where noinvoice='" . $param['noinvoice'] . "' and jenis='" . $param['jeniskapalponton'] . "' and kode='" . $param['namakapalponton'] . "'";
		// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " error: code 11256\n " . $e->getMessage() . "<br/>";
			die();
		}
		break;



	case 'addkapalponton':

		$tab = "";

		$tab .= "<fieldset style=float:left>
			<legend>" . $_SESSION['lang']['entryForm'] . "</legend> 
				<table border=0 cellpadding=1 cellspacing=1>
					<tr>
					
					
					<td>" . $_SESSION['lang']['jenis'] . "</td>
						<td>:</td>		
						<td>
							
							<select id=jeniskapalponton onchange=getaddkapalponton(); style=\"width:150px;\">'" . $optjenis . "'</select>
						</td>
					</tr>	
					<tr>
						<td>" . $_SESSION['lang']['namakapal'] . " / " . $_SESSION['lang']['namaponton'] . "</td>
						<td>:</td>		
						<td>
							<select id=namakapalponton style=\"width:150px;\">'" . $optkapalponton . "'</select>
						</td>
					</tr>	
					<tr>
					<td colspan=2></td>
					<td>  <button class=mybutton onclick=saveaddkapalponton('" . $noinvoice . "')>Simpan</button></td>
					</tr>
					</table></fieldset>";

		$tab .= "<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab .= "<thead><tr>
                <td>" . $_SESSION['lang']['jenis'] . "</td>
                <td>" . $_SESSION['lang']['nama'] . "</td>
                <td>" . $_SESSION['lang']['action'] . "</td>
            </tr></thead>";
		#= isi data
		$str = "SELECT * FROM " . $dbname . ".keu_penagihandt_kapalponton where noinvoice='" . $param['noinvoice'] . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			@$no += 1;
			$tab .= "<tr class=rowcontent>
                <td>" . $namajeniskapalponton[$bar['jenis']] . "</td>
                <td>" . $nmkapalponton[$bar['kode']] . "</td>
                <td><img src='images/skyblue/delete.png' class='resicon' title='Delete " . $bar['kode'] . "' onclick=\"deleteaddkapalponton('" . $bar['noinvoice'] . "','" . $bar['jenis'] . "','" . $bar['kode'] . "')\"></td>               
            </tr>";
		}

		$tab .= "</table>";
		$tab .= "</fieldset>";

		echo $tab;

		break;


	case 'searchnotransaksikasbank':
		// echo $param['kodeorganisasi'];
		// exit("Error:A");

		$tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab .= "<thead><tr>";
		$tab .= "<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['novoucher'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>";
		$tab .= "</tr></thead><tbody>";


		// $whr.=" and kodeorg='".$param['kodeorganisasi']."' and noakun='1130101' and tipetransaksi='M' and nodok!='' and keterangan1=''";
		$whr .= " and kodeorg='" . $param['kodeorganisasi'] . "' and noakun='1130100' and tipetransaksi='M' and nodok!='' and keterangan1=''";

		if ($param['txtfind'] != '') {
			$whr .= " and (notransaksi like '%" . $param['txtfind'] . "%' or novoucher like '%" . $param['txtfind'] . "%')";
		}

		$str = " select * from " . $dbname . ".keu_kasbankdtht_vw where 1=1 " . $whr . " ";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$set = "style=cursor:pointer; onclick=movenotransaksikasbank('" . $bar['notransaksi'] . "')";
			$tab .= "<tr " . $set . " title='click to add'  class=rowcontent>
						<td align=center style=cursor:pointer>" . $bar['notransaksi'] . "</td>
						<td align=center style=cursor:pointer>" . $bar['novoucher'] . "</td>
						<td align=center style=cursor:pointer>" . $bar['jumlah'] . "</td>";
			$tab .= "</tr>";
		}
		$tab .= "</table>";
		echo $tab;

		break;


	case 'viewpdf':
		$str = "select * from " . $dbname . ".keu_penagihanht where noinvoice='" . $noinvoice . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$novalidasi = $bar['novalidasi'];
		$tanggal = $bar['tanggal'];
		$kodecustomer = $bar['kodecustomer'];
		$nilaiinvoice = $bar['nilaiinvoice'];
		$nilaippn = $bar['nilaippn'];
		$nokontrak = $bar['nokontrak'];
		$nilaiinv = $nilaiinvoice + $nilaippn;
		$rupiah1 = $bar['rupiah1'];
		$rupiah2 = $bar['rupiah2'];
		$rupiah3 = $bar['rupiah3'];
		$rupiah4 = $bar['rupiah4'];
		$rupiah5 = $bar['rupiah5'];
		$rupiah6 = $bar['rupiah6'];
		$rupiah7 = $bar['rupiah7'];
		$rupiah8 = $bar['rupiah8'];
		$pphrupiah = $bar['pphrupiah'];
		$nilaiKlaimPengurang = $rupiah1 + $rupiah2 + $rupiah3
			+ $rupiah4 + $rupiah5 + $rupiah6 + $rupiah7 - $rupiah8;


		$str = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $kodecustomer . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$namacustomer = $bar['namacustomer'];
		$alamat = $bar['alamat'];


		$sPpn = $owlPDO->query("select a.ppn  from " . $dbname . ".pmn_kontrakjual a  where a.nokontrak='" . $nokontrak . "' or a.nokontrakexternal='" . $nokontrak . "'");
		$sPpn->setFetchMode(PDO::FETCH_ASSOC);
		$rPpn = $sPpn->fetch();

		$ppnKlaim = 0;
		if (@$rPpn['ppn'] == 1) {
			$ppnKlaim = 10 / 100 * $nilaiKlaimPengurang;
		}
		$nilaiTot = $nilaiinv - $nilaiKlaimPengurang - $ppnKlaim;



		class PDF extends FPDF {}

		// $pdf=new FPDF('L','mm',array(75,170));
		// $pdf->SetAutoPageBreak(false);
		// $pdf->AddPage();

		$pdf = new PDF('P', 'mm', 'A4');
		$pdf->SetMargins(20, '', 20);
		$pdf->AddPage();


		$sizefont = 8;
		$height = 5;
		$xawal = 10;

		$pdf->SetY(10);
		$pdf->SetFont('Arial', 'B', $sizefont + 2);
		$pdf->Cell(170, $height, 'NOTA VALIDASI', 0, 1, 'C');
		$pdf->Ln();

		$pdf->SetFont('Arial', '', $sizefont);

		$pdf->SetX($xawal);

		$pdf->Cell(20, $height, 'Customer', 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'C');
		$pdf->Cell(100, $height, $namacustomer, 0, 0, 'L');


		$pdf->Cell(30, $height, 'No Validasi', 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'C');
		$pdf->Cell(75, $height, $novalidasi, 0, 1, 'L');

		$pdf->SetX($xawal);
		$yalamat = $pdf->GetY();

		$pdf->Cell(20, $height, 'Alamat', 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'C');
		$pdf->MultiCell(100, $height, $alamat, 0, 'L', 0);

		$pdf->SetXY(135, $yalamat);
		$pdf->Cell(30, $height, 'Tanggal', 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'C');
		$pdf->Cell(75, $height, tglnmbln($tanggal, 'I', 'short'), 0, 1, 'L');

		$pdf->Ln(10);
		$pdf->SetX($xawal);
		$pdf->SetFont('Arial', 'B', $sizefont);
		$pdf->Cell(7, $height, 'No.', 'TB', 0, 'L');
		$pdf->Cell(30, $height, 'Tanggal', 'TB', 0, 'L');
		$pdf->Cell(40, $height, 'Invoice No', 'TB', 0, 'L');
		$pdf->Cell(40, $height, 'Description', 'TB', 0, 'L');
		$pdf->Cell(30, $height, 'Jumlah Faktur', 'TB', 0, 'L');
		$pdf->Cell(30, $height, 'Potongan PPh', 'TB', 0, 'L');
		$pdf->Cell(30, $height, 'Jumlah Tagihan', 'TB', 1, 'L');

		$pdf->SetX($xawal);
		$pdf->SetFont('Arial', '', $sizefont);
		$pdf->Cell(7, $height, '1', 'T', 0, 'L');
		$pdf->Cell(30, $height, tglnmbln($tanggal, 'I', 'short'), 'T', 0, 'L');
		$pdf->Cell(40, $height, $noinvoice, 'T', 0, 'L');
		$pdf->Cell(40, $height, '', 'T', 0, 'L');
		$pdf->Cell(30, $height, number_format($nilaiTot, 2), 'T', 0, 'L');
		$pdf->Cell(30, $height, number_format($pphrupiah * (-1), 2), 'T', 0, 'C');
		$nilaiTot = $nilaiTot - $pphrupiah;
		$pdf->Cell(30, $height, number_format($nilaiTot, 2), 'T', 1, 'L');

		$pdf->Ln(50);
		$pdf->SetX($xawal);
		$pdf->SetFont('Arial', 'B', $sizefont);
		$pdf->Cell(152, $height, 'Total', 'T', 0, 'R');
		$pdf->Cell(15, $height, '', 'TB', 0, 'R');
		$pdf->SetFont('Arial', '', $sizefont);
		$pdf->Cell(30, $height, number_format($nilaiTot, 2), 'BT', 1, 'L');

		$pdf->Ln(1);

		$pdf->SetX($xawal);
		$pdf->SetFont('Arial', '', $sizefont);
		$pdf->Cell(40, $height, 'Dibuat', 0, 0, 'C');
		$pdf->Cell(40, $height, 'Di Ketahui', 0, 0, 'C');
		$pdf->Cell(40, $height, 'Di Setujui', 0, 0, 'C');
		$pdf->Cell(32, $height, '', 0, 0, 'R');
		$pdf->Cell(45, $height, '', 'T', 1, 'R');

		$pdf->Ln(20);
		$pdf->SetX($xawal);
		$pdf->SetFont('Arial', '', $sizefont);
		$pdf->Cell(40, $height, '', 0, 0, 'C');
		$pdf->Cell(40, $height, '(                                           )', 0, 0, 'C');
		$pdf->Cell(40, $height, '(                                          )', 0, 0, 'C');
		$pdf->Cell(32, $height, '', 0, 0, 'R');
		$pdf->Cell(45, $height, '', '', 1, 'R');


		$pdf->Output();
		break;

	case 'insert':

		#= Abdul
		#= Cek Sudah Ada Notransaksi Penagihan yang sama atau tidak
		#= Case
		#= Ketika Insert dengan noinvoice yang sama, melakukan replace / update data 
		#= yang sebelumnya menjadi yang terbaru
		$whr = "noinvoice='" . $param['noinvoice'] . "'";
		$data = getCountRows($dbname, "keu_penagihanht", $whr);

		if ($data > 0) {
			exit('Warning : No Invoice : ' . $param['noinvoice'] . ' sudah ada!');
		}
		#= End Abdul
		#===========================================================

		#= cek data
		$skdorg = "select a.kodept,a.kodebarang from " . $dbname . ".pmn_kontrakjual a  where a.nokontrak='" . $param['noorder'] . "' or a.nokontrakexternal='" . $param['noorder'] . "'";
		if ($param['jenis'] == 'DS') {
			$skdorg = "select left(kodeasset,3) as kodept  from " . $dbname . ".keu_disposalasset where notransaksi='" . $param['noorder'] . "'";
		}
		$rkdorg = $owlPDO->query($skdorg);
		$rkdorg->setFetchMode(PDO::FETCH_ASSOC);
		$bkdorg = $rkdorg->fetch();
		$kdorg = $bkdorg['kodept'];
		$kodebarang = $bkdorg['kodebarang'];

		if ($param['kodebarang'] == '40000003' || $param['kodebarang'] == '40000033') {
			$skdorg = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $param['kodeorganisasi'] . "'";
			$rkdorg = $owlPDO->query($skdorg);
			$rkdorg->setFetchMode(PDO::FETCH_ASSOC);
			$bkdorg = $rkdorg->fetch();
			$kdorg = $bkdorg['induk'];
		}
		// exit("Error:".$kdorg._.$skdorg);

		if (trim($param['noinvoice']) == '') {
			exit("warning : No. Invoice masih kosong");
		}
		if ($param['tanggal'] == '') {
			exit("warning : " . $_SESSION['lang']['notiftanggal']);
		}
		if ($param['nilaiinvoice'] == '') {
			exit("warning : " . $_SESSION['lang']['notifnilaiinvoice']);
		}

		if ($param['noorder'] == '') {
			if ($param['kodebarang'] != '40000003') {
				exit("warning : " . $_SESSION['lang']['notifkontrak']);
			}
		}
		// if($param['nofakturpajak']==''){
		// exit("warning : No.Faktur Pajak tidak boleh kosong");
		// }
		if ($param['nilaippn'] == '') {
			$param['nilaippn'] = 0;
		}
		if ($param['jatuhtempo'] == '') {
			$param['jatuhtempo'] = '0000-00-00';
		}
		if (($param['nobuktipotong'] == '' and $param['tglbuktipotong'] != '') or ($param['nobuktipotong'] != '' and $param['tglbuktipotong'] == '')) {
			exit('Error : No bukti potong dan tanggal bukti potong harus diisi keduanya.');
		}

		if ($param['kodeorganisasi'] == '') {
			exit("Warning: Kode Organisasi masih kosong");
		}

		# OTPI => Biaya Investasi memakai supplierid bukan pelanggan jadi tidak ada NPWP
		if ($param['tipeinvoice'] != 'OTPI' && $param['tipeinvoice'] != 'OTPB') {
			if ($param['npwp'] == '') {
				exit("Warning: NPWP Pelanggan masih kosong <br/><br/> <span style=color:blue;><b>Informasi :</b> Lakukan Setup NPWP Pelanggan di Modul (PEMASARAN > SETUP > PELANGGAN)</span>");
			}
		}

		if ($param['npwpunit'] == '') {
			exit("Warning: NPWP Unit masih kosong");
		}
		if ($param['nilaiinvoice'] == '') {
			exit("Warning: Nilai Invoice masih kosong");
		}
		if ($param['jatuhtempo'] == '') {
			exit("Warning: Tanggal Jatuh Tempo masih kosong");
		}
		if ($param['jatuhtempo'] == '') {
			exit("Warning: Tanggal Jatuh Tempo masih kosong");
		}

		if ($param['tipearuskasht'] == '') {
			exit("Warning: Tipe Arus Kas masih kosong");
		}

		if ($param['tipeinvoice'] == '') {
			exit("Warning: Tipe Invoice masih kosong");
		}

		// if($kodebarang=='40000003'){
		// 	if(($param['jenispph']=='' and ($param['pphrupiah']!='0'and $param['pphrupiah']!='')) or ($param['jenispph']!='' and ($param['pphrupiah']=='0' or $param['pphrupiah']==''))){
		//     	exit('Error : Jenis dan Rupiah PPH harus diisi keduanya.');
		// 	}	
		// }

		// echo "<pre>";
		// print_r($param);
		// exit('warning');
		#= bentuk novalidasi
		$novalidasi = 'VP' . date('Ymdhis');


		$param['nilaiinvoice'] = str_replace(",", "", $param['nilaiinvoice']);
		$param['nilaippn'] = str_replace(",", "", $param['nilaippn']);
		$param['nilaipph'] = str_replace(",", "", $param['nilaipph']);
		$whrBrg = "nokontrak='" . $param['noorder'] . "' or nokontrakexternal='" . $param['noorder'] . "'";
		$optBrg = makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,kodebarang', $whrBrg);
		$optKdpt = makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,kodept', $whrBrg);
		$param['rupiah8'] == '' ? $param['rupiah8'] = 0 : $param['rupiah8'] = $param['rupiah8'];
		$param['rupiah7'] == '' ? $param['rupiah7'] = 0 : $param['rupiah7'] = $param['rupiah7'];
		$param['rupiah6'] == '' ? $param['rupiah6'] = 0 : $param['rupiah6'] = $param['rupiah6'];
		$param['rupiah5'] == '' ? $param['rupiah5'] = 0 : $param['rupiah5'] = $param['rupiah5'];
		$param['rupiah4'] == '' ? $param['rupiah4'] = 0 : $param['rupiah4'] = $param['rupiah4'];
		$param['rupiah3'] == '' ? $param['rupiah3'] = 0 : $param['rupiah3'] = $param['rupiah3'];
		$param['rupiah2'] == '' ? $param['rupiah2'] = 0 : $param['rupiah2'] = $param['rupiah2'];
		$param['rupiah1'] == '' ? $param['rupiah1'] = 0 : $param['rupiah1'] = $param['rupiah1'];
		$param['kuantitas'] = str_replace(",", "", $param['kuantitas']);
		$param['kuantitas'] == '' ? $param['kuantitas'] = 0 : $param['kuantitas'] = $param['kuantitas'];
		$param['pphrupiah'] = str_replace(",", "", $param['pphrupiah']);
		$param['pphrupiah'] == '' ? $param['pphrupiah'] = 0 : $param['pphrupiah'] = $param['pphrupiah'];
		$tipeinvoice = $param['tipeinvoice'];

		// $str="select nofakturpajak from ".$dbname.".keu_penagihanht where nofakturpajak='".$param['nofakturpajak']."'";
		// $res=fetchdata($str);
		// $jlhbrs=count($res);


		// if ($param['jenis']=='OT') {
		// $whr="kodeorganisasi='".$param['kodeorganisasi']."'";
		// $optinduk = makeOption($dbname, 'organisasi','kodeorganisasi,induk',$whr);
		// $kdorg=$optinduk[$param['kodeorganisasi']];
		// }

		#= tipeinvoice ambil dari kodebarang
		// $str="select * from ".$dbname.".keu_5jenispenagihandt where (kodejenis='CIPP' or kodejenis='CITBS') and kodebarang='".$param['kodebarang']."' ";
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// 	$tipeinvoice=$bar['kodejenis'];
		// }

		#=============================#
		# CEK JIKA SUPPLIER
		#=============================#
		if ($optnmCust[$param['kodecustomer']] == '') {
			$param['kodesupplier'] = $param['kodecustomer'];
			$param['kodecustomer'] = '';
		}

		$str = "insert into " . $dbname . ".keu_penagihanht 
		(noinvoice,kodeorg,kodept,tanggal,nokontrak,
		kodecustomer,nilaiinvoice,nilaippn,nilaipph,jatuhtempo,matauang,
		kurs,bayarke,debet,kredit,nofakturpajak,
		keterangan1,keterangan2,keterangan3,keterangan4,keterangan5,
		rupiah1,rupiah2,rupiah3,rupiah4,rupiah5,
		kodebarang,keterangan6,rupiah6,keterangan7,rupiah7,
		keterangan8,rupiah8,ttd,jenis,jenisinvoice,kuantitas,
		periode,nobuktipotong,tglbuktipotong,jenispph,pphrupiah,
		jenispenghasilan,carabayar,npwp,berikat,novalidasi,tipeinvoice,
		keterangantambahan,noreferensi,nopo,notransaksikasbank,transport,npwpunit,createby,createtime,updateby,tipearuskas,kodesupplier) values 
		('" . $param['noinvoice'] . "','" . $param['kodeorganisasi'] . "','" . $kdorg . "','" . tanggalsystem($param['tanggal']) . "','" . $param['noorder'] . "',
		'" . $param['kodecustomer'] . "','" . $param['nilaiinvoice'] . "','" . $param['nilaippn'] . "','" . $param['nilaipph'] . "','" . tanggalsystem($param['jatuhtempo']) . "','" . $param['matauang'] . "',
		'" . $param['kurs'] . "','" . $param['bayarke'] . "','" . $param['debet'] . "','" . $param['kredit'] . "','" . $param['nofakturpajak'] . "',
		'" . $param['keterangan1'] . "','" . $param['keterangan2'] . "','" . $param['keterangan3'] . "','" . $param['keterangan4'] . "','" . $param['keterangan5'] . "',
		'" . $param['rupiah1'] . "','" . $param['rupiah2'] . "','" . $param['rupiah3'] . "','" . $param['rupiah4'] . "','" . $param['rupiah5'] . "',
		'" . $param['kodebarang'] . "','" . $param['keterangan6'] . "','" . $param['rupiah6'] . "','" . $param['keterangan7'] . "','" . $param['rupiah7'] . "',
		'" . $param['keterangan8'] . "','" . $param['rupiah8'] . "','" . $param['ttd'] . "','" . $param['jenis'] . "','" . $param['jenisinvoice'] . "','" . $param['kuantitas'] . "',
		'" . $param['periode'] . "','" . $param['nobuktipotong'] . "','" . tanggalsystem($param['tglbuktipotong']) . "','" . $param['jenispph'] . "','" . $param['pphrupiah'] . "',
		'" . $param['jenispenghasilan'] . "','" . $param['carabayar'] . "','" . $param['npwp'] . "','" . $param['berikat'] . "','" . $novalidasi . "','" . $tipeinvoice . "',
		'" . $param['keterangantambahan'] . "','" . $param['noref'] . "','" . $param['nopo'] . "','" . $param['notransaksikasbank'] . "','" . $param['transport'] . "','" . $param['npwpunit'] . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "','" . $param['tipearuskasht'] . "','" . $param['kodesupplier'] . "')";


		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " error: code 11256\n " . $e->getMessage() . "<br/>";
			die();
		}

		//update faktur pajak
		$dt = "update " . $dbname . ".keu_fakturpajakdt set notransaksi='" . $param['noinvoice'] . "', updateby='" . $_SESSION['standard']['userid'] . "' where faktur='" . $param['nofakturpajak'] . "'";
		try {
			$owlPDO->exec($dt);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		$filenameqrcode = "/images/qrcode/" . $param['noinvoice'] . ".png";
		if (file_exists($filenameqrcode)) {
		} else {
			$folder = "images/qrcode/";
			$file_name = $param['noinvoice'] . ".png";
			$file_name = $folder . $file_name;
			QRcode::png($param['noinvoice'], $file_name);

			header("Content-type: image/png");
			$imgPath = $file_name;
			$image = imagecreatefrompng($imgPath);
			$color = imagecolorallocate($image, 0, 0, 0);
			$string = $param['noinvoice'];
			$fontSize = 2;
			$x = 20;
			$y = 74;
			imagestring($image, $fontSize, $x, $y, $string, $color);

			imagepng($image, $file_name);
			imagedestroy($image);
		}

		// echo ($param['noinvoice']!=''?$param['noinvoice']:$nomorinvoice);
		break;

	case 'update':

		#= bentuk novalidasi
		$novalidasi = 'VP' . date('Ymdhis');

		#= cek data
		$skdorg = "select a.kodept,a.kodebarang from " . $dbname . ".pmn_kontrakjual a  where a.nokontrak='" . $param['noorder'] . "' or a.nokontrakexternal='" . $param['noorder'] . "'";
		if ($param['jenis'] == 'DS') {
			$skdorg = "select left(kodeasset,3) as kodept  from " . $dbname . ".keu_disposalasset where notransaksi='" . $param['noorder'] . "'";
		}
		$rkdorg = $owlPDO->query($skdorg);
		$rkdorg->setFetchMode(PDO::FETCH_ASSOC);
		$bkdorg = $rkdorg->fetch();
		$kdorg = $bkdorg['kodept'];
		if ($param['kodebarang'] == '') {
			$kodebarang = $bkdorg['kodebarang'];
		} else {
			$kodebarang = $param['kodebarang'];
		}
		if ($param['kodebarang'] == '40000003' || $param['kodebarang'] == '40000033') {
			$skdorg = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $param['kodeorganisasi'] . "'";
			$rkdorg = $owlPDO->query($skdorg);
			$rkdorg->setFetchMode(PDO::FETCH_ASSOC);
			$bkdorg = $rkdorg->fetch();
			$kdorg = $bkdorg['induk'];
		}

		#= tipeinvoice ambil dari kodebarang
		$str = "select * from " . $dbname . ".keu_5jenispenagihandt where (kodejenis='CIPP' or kodejenis='CITBS')  and kodebarang='" . $param['kodebarang'] . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tipeinvoice = $bar['kodejenis'];
		}

		$param['nilaiinvoice'] = str_replace(",", "", $param['nilaiinvoice']);
		$param['nilaippn'] = str_replace(",", "", $param['nilaippn']);
		$param['nilaipph'] = str_replace(",", "", $param['nilaipph']);
		$whrBrg = "nokontrak='" . $param['noorder'] . "' or nokontrakexternal='" . $param['noorder'] . "'";
		$optBrg = makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,kodebarang', $whrBrg);
		$optKdpt = makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,kodept', $whrBrg);
		$param['rupiah8'] == '' ? $param['rupiah8'] = 0 : $param['rupiah8'] = $param['rupiah8'];
		$param['rupiah7'] == '' ? $param['rupiah7'] = 0 : $param['rupiah7'] = $param['rupiah7'];
		$param['rupiah6'] == '' ? $param['rupiah6'] = 0 : $param['rupiah6'] = $param['rupiah6'];
		$param['rupiah5'] == '' ? $param['rupiah5'] = 0 : $param['rupiah5'] = $param['rupiah5'];
		$param['rupiah4'] == '' ? $param['rupiah4'] = 0 : $param['rupiah4'] = $param['rupiah4'];
		$param['rupiah3'] == '' ? $param['rupiah3'] = 0 : $param['rupiah3'] = $param['rupiah3'];
		$param['rupiah2'] == '' ? $param['rupiah2'] = 0 : $param['rupiah2'] = $param['rupiah2'];
		$param['rupiah1'] == '' ? $param['rupiah1'] = 0 : $param['rupiah1'] = $param['rupiah1'];
		$param['kuantitas'] = str_replace(",", "", $param['kuantitas']);
		$param['kuantitas'] == '' ? $param['kuantitas'] = 0 : $param['kuantitas'] = $param['kuantitas'];
		$param['pphrupiah'] = str_replace(",", "", $param['pphrupiah']);
		$param['pphrupiah'] == '' ? $param['pphrupiah'] = 0 : $param['pphrupiah'] = $param['pphrupiah'];
		$param['nilaipph'] == '' ? $param['nilaipph'] = 0 : $param['nilaipph'] = $param['nilaipph'];

		// exit('warning'.$param['kodeorganisasi']);
		//update penagihanht
		$dtupdate = "update " . $dbname . ".keu_penagihanht set tanggal='" . tanggalsystem($param['tanggal']) . "',kodeorg='" . $param['kodeorganisasi'] . "',kodept='" . $kdorg . "',nilaiinvoice='" . $param['nilaiinvoice'] . "',
         nilaippn='" . $param['nilaippn'] . "', nilaipph='" . $param['nilaipph'] . "',jatuhtempo='" . tanggalsystem($param['jatuhtempo']) . "',matauang='" . $param['matauang'] . "',tipeinvoice='" . $tipeinvoice . "',
         kurs='" . $param['kurs'] . "',bayarke='" . $param['bayarke'] . "',debet='" . $param['debet'] . "',
         kredit='" . $param['kredit'] . "',nofakturpajak='" . $param['nofakturpajak'] . "',keterangan1='" . $param['keterangan1'] . "',
         keterangan2='" . $param['keterangan2'] . "',keterangan3='" . $param['keterangan3'] . "',keterangan4='" . $param['keterangan4'] . "',
         keterangan5='" . $param['keterangan5'] . "',rupiah1='" . $param['rupiah1'] . "',rupiah2='" . $param['rupiah2'] . "',
         rupiah3='" . $param['rupiah3'] . "',rupiah4='" . $param['rupiah4'] . "',rupiah5='" . $param['rupiah5'] . "',
         kodebarang='" . $kodebarang . "',keterangan6='" . $param['keterangan6'] . "',rupiah6='" . $param['rupiah6'] . "',
         keterangan7='" . $param['keterangan7'] . "',rupiah7='" . $param['rupiah7'] . "',keterangan8='" . $param['keterangan8'] . "',
         rupiah8='" . $param['rupiah8'] . "',ttd='" . $param['ttd'] . "',jenis='" . $param['jenis'] . "',jenisinvoice='" . $param['jenisinvoice'] . "',
         kuantitas='" . $param['kuantitas'] . "',periode='" . $param['periode'] . "',nobuktipotong='" . $param['nobuktipotong'] . "',
         tglbuktipotong='" . $param['tglbuktipotong'] . "',jenispph='" . $param['jenispph'] . "',pphrupiah='" . $param['pphrupiah'] . "',
         jenispenghasilan='" . $param['jenispenghasilan'] . "',carabayar='" . $param['carabayar'] . "',npwp='" . $param['npwp'] . "',
         berikat='" . $param['berikat'] . "',novalidasi='" . $novalidasi . "',keterangantambahan='" . $param['keterangantambahan'] . "',noreferensi='" . $param['noref'] . "',nopo='" . $param['nopo'] . "',notransaksikasbank='" . $param['notransaksikasbank'] . "',transport='" . $param['transport'] . "',npwpunit='" . $param['npwpunit'] . "',updateby='" . $_SESSION['standard']['userid'] . "' where noinvoice='" . $param['noinvoice'] . "'";

		try {
			$owlPDO->exec($dtupdate);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'getKursInvoice':
		if ($param['noorder'] == '') {
			echo "warning : " . $_SESSION['lang']['notifkontrak'];
		} else {
			if ($param['matauang'] == 'IDR') {
				echo '1';
			} else {
				$tanggal = tanggalsystem($param['tanggal']);
				$sKurs = $owlPDO->query("select * from " . $dbname . ".setup_matauangrate where kode='" . $param['matauang'] . "' and daritanggal='" . $tanggal . "'");
				$sKurs->setFetchMode(PDO::FETCH_ASSOC);
				$qKurs = owlBaris($sKurs);
				if ($qKurs <= 0) {
					echo 'warning : ' . $_SESSION['lang']['notifkurstanggal'] . ' : ' . $param['tanggal'];
				} else {
					$bKurs = $sKurs->fetch();
					echo $bKurs['kurs'];
				}
			}
		}
		break;

	case 'getnpwpunit':
		# ambil npwp
		$ptkont = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $param['kodeorganisasi'] . "'");
		$optnpwp = $optrekening = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select npwp from " . $dbname . ".setup_org_npwp where kodeorg='" . $ptkont[$param['kodeorganisasi']] . "'";
		// exit("Error:$str");
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$selected = '';
			if ($bar['npwp'] == $param['npwpunit']) {
				$selected = 'selected';
			}
			$optnpwp .= "<option value='" . $bar['npwp'] . "' " . $selected . ">" . $bar['npwp'] . "</option>";
		}
		// exit("error".$param['bayarke']);
		// @$optAkun.="<option value='".$rakun['noakun']."'>".$nmbank[$rakun['namabank']].", Rek: ".$rakun['rekening'].", An. ".$rakun['atasnama']."</option>";
		$str = "select * from " . $dbname . ".keu_5akunbank_vw where pemilik in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $ptkont[$param['kodeorganisasi']] . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$selected = '';
			if ($bar['noakun'] == $param['bayarke']) {
				$selected = 'selected';
			} else {
				if ($bar['fungsi'] == 'Pendapatan') {
					$selected = 'selected';
				}
			}
			$optrekening .= "<option value='" . $bar['noakun'] . "' " . $selected . ">" . $bar['namabank'] . ", Rek : " . $bar['rekening'] . ", An. " . $bar['atasnama'] . "</option>";
		}

		echo $optnpwp . "####" . $optrekening;
		// exit("Error:MASUK");
		break;


	case 'getNilai':
		if ($param['matauang'] != 'IDR') {
			$ppn = $param['kurs'] * $param['nilaippn'];
			$nilInv = $param['kurs'] * $param['nilaiinvoice'];
		} else {
			$ppn = $param['nilaippn'];
			$nilInv = $param['nilaiinvoice'];
		}

		echo $ppn . '###' . $nilInv;

		break;

	case 'loadData':
		$where = '';
		if (!empty($param['noinvoice'])) {
			$where .= " and noinvoice like '%" . $param['noinvoice'] . "%'";
		}
		if (!empty($param['tanggalCr'])) {

			$where .= " and left(tanggal,7) = '" . $param['tanggalCr'] . "'";
		}
		if ($param['statId'] != '') {
			$where .= " and posting = '" . $param['statId'] . "'";
		}
		if ($param['unitkerjasch'] != '') {
			$where .= " and kodeorg = '" . $param['unitkerjasch'] . "'";
		}

		if ($param['nokontrak'] != '') {
			$where .= " and nokontrak like '%" . $param['nokontrak'] . "%'";
		}
		if ($param['customer'] != '') {
			$where .= " and kodecustomer = '" . $param['customer'] . "'";
		}
		$sdel = "";
		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0) $page = 0;
		}
		$offset = $page * $limit;
		// $sql=$owlPDO->query("select count(*) jmlhrow from ".$dbname.".keu_penagihanht where 1=1 ".$where." order by tanggal desc,posting asc");
		$sql = "select count(*) jmlhrow from " . $dbname . ".keu_penagihanht where 1=1 " . $where . " order by tanggal desc,posting asc";
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($jsl = $res->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}

		// $str=$owlPDO->query("select * from ".$dbname.".keu_penagihanht where 1=1 ".$where."  order by tanggal desc,posting asc  limit ".$offset.",".$limit." ");
		$str = "select * from " . $dbname . ".keu_penagihanht where 1=1 " . $where . "  order by tanggal desc,posting asc  limit " . $offset . "," . $limit . " ";

		// $a="select * from ".$dbname.".keu_penagihanht where  nokontrak!=''  ".$where."  order by tanggal desc,posting asc  limit ".$offset.",".$limit."";
		// // echo $a;
		// $strdt="select * from ".$dbname.".keu_penagihanht where  nokontrak!=''  ".$where."  order by tanggal desc,posting asc  limit ".$offset.",".$limit." ";
		//echo $strdt;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$tab = '';
		$nor = 0;
		while ($rstr =  $res->fetch()) {


			#=datakaryawan
			$strdt = "select namakaryawan,karyawanid from " . $dbname . ".datakaryawan where karyawanid in ('" . $rstr['createby'] . "','" . $rstr['updateby'] . "') ";
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				$namakaryawan[$bardt['karyawanid']] = $bardt['namakaryawan'];
			}

			$sPpn = $owlPDO->query("select a.ppn  from " . $dbname . ".pmn_kontrakjual a  where a.nokontrak='" . $rstr['nokontrak'] . "' or a.nokontrakexternal='" . $rstr['nokontrak'] . "'");
			$sPpn->setFetchMode(PDO::FETCH_ASSOC);
			$rPpn = $sPpn->fetch();

			$nilaiinv = $rstr['nilaiinvoice'] + $rstr['nilaippn'];

			$nilaiKlaimPengurang = $rstr['rupiah1'] + $rstr['rupiah2'] + $rstr['rupiah3']
				+ $rstr['rupiah4'] + $rstr['rupiah5'] + $rstr['rupiah6'] + $rstr['rupiah7'] - $rstr['rupiah8'];
			// $totalkurang=$nilaiinv-$nilaiKlaimPengurang; 
			$ppnKlaim = 0;
			if (@$rPpn['ppn'] == 0) {
				$ppnKlaim = 10 / 100 * $nilaiKlaimPengurang;
			}
			$nilaiTot = $nilaiinv - $nilaiKlaimPengurang - $ppnKlaim;
			$excldata = '';
			if ($rstr['kodebarang'] == '40000003') {
				$excldata = "<img class=zImgBtn src='images/skyblue/excel.jpg' style='cursor:pointer' onclick=detailtbs('" . $rstr['noinvoice'] . "',event) title='Print XLS Detail TBS " . $rstr['noinvoice'] . "'>";
			}
			// echo $nilaiinv._.$nilaiKlaimPengurang._.$totalkurang;

			$nor += 1; //<td align=right>".number_format($rstr['nilaiinvoice'],0)."</td>
			$tab .= "<tr class=rowcontent>
                    <td align=center>" . $nor . "</td>
                    <td id='noinvoice_" . $nor . "' align=left value='" . $rstr['noinvoice'] . "'>" . $rstr['noinvoice'] . "</td>
                    <td align=center>" . $rstr['jenisinvoice'] . "</td>
                    <td id='kodeorg_" . $nor . "' align=center value='" . $rstr['kodeorg'] . "'>" . $rstr['kodeorg'] . "</td>
                    <td id='tanggal_" . $nor . "' align=center value='" . $rstr['tanggal'] . "'>" . tanggalnormal(substr($rstr['tanggal'], 0, 10)) . "</td>
                    <td id='noakun_" . $nor . "' align=center value='" . $rstr['nokontrak'] . "'>" . $rstr['nokontrak'] . "</td>
                    <td>" . $optnmCust[$rstr['kodecustomer']] . "</td>    
                    <td>" . ($namasupplier[$rstr['kodesupplier']] != '' ? "[" . $kodeorgkud[$rstr['kodesupplier']] . "] " . $namasupplier[$rstr['kodesupplier']] : $optnmCust[$rstr['kodecustomer']]) . "</td>    
					<td align=center>" . getNamaBrg($rstr['kodebarang']) . "</td>
                    <td align=right>" . number_format($rstr['kuantitas'], 0) . "</td>
                    <td align=right>" . number_format($rstr['nilaiinvoice'], 0) . "</td>
                    <td align=right>" . number_format($rstr['nilaippn'], 0) . "</td>
                    <td align=right>" . number_format($nilaiTot, 0) . "</td>
					<td>" . $rstr['transport'] . "</td>"; //<td align=left>".$rstr['keterangan']."</td>"
			$filepdf = 'keu_slave_pdf_faktur';
			if ($rstr['posting'] == 0) {

				if ($rstr['kodebarang'] == '40000003') {
					if ($rstr['kodept'] == 'PPP') {
						$larikesini = 'keu_slave_print_penagihanPalma';
					} else {
						$larikesini = 'keu_slave_print_penagihanNonPalma'; #Belum ada file nya per 05 october 2025 (15:07)
					}
				} else {
					$larikesini = 'keu_slave_print_penagihanKomoditi';
				}
				$tab .= "<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit " . $rstr['noinvoice'] . "' onclick=\"fillField('" . $rstr['noinvoice'] . "');\" ></td>";
				$tab .= "<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Hapus " . $rstr['noinvoice'] . "' onclick=\"delData('" . $rstr['noinvoice'] . "','" . $rstr['nofakturpajak'] . "');\" ></td>";
				$tab .= "<td align=center><img src=images/skyblue/posting.png class=zImgBtn  title='Posting " . $rstr['noinvoice'] . "' onclick=\"postingData('" . $rstr['noinvoice'] . "');\" ></td>";
				$tab .= "<td align=center>" . $excldata . "</td>";
				$tab .= "<td align=center><img title='" . $_SESSION['lang']['upload'] . "' class=zImgBtn onclick=\"viewlistfile(event,'" . $rstr['noinvoice'] . "')\" src='images/upload-2-xxl.png'/></td>";
				$tab .= "<td align=center>
					<img src=images/pdf.jpg class=zImgBtn  title='Detail " . $rstr['noinvoice'] . "' onclick=\"masterPDF('keu_penagihanht','" . $rstr['noinvoice'] . "','','" . $larikesini . "',event);\" > </td>";
				$tab .= "<td align=center>
					<img src=images/pdf.jpg class=zImgBtn  title='Faktur Pajak " . $rstr['noinvoice'] . "' onclick=\"masterPDF('keu_penagihanht','" . $rstr['noinvoice'] . "','','" . $filepdf . "',event);\" >
					</td>";

				$tab .= "<td align=center><img title='namakapal dan ponton' class=zImgBtn onclick=\"addkapalponton('" . $rstr['noinvoice'] . "')\" src='images/icons/anchor.png'/></td>";
			} else {

				if ($rstr['kodebarang'] == '40000003') {
					if ($rstr['kodept'] == 'PPP') {
						$larikesini = 'keu_slave_print_penagihanPalma';
					} else {
						$larikesini = 'keu_slave_print_penagihanNonPalma'; #Belum ada file nya per 05 october 2025 (15:07)
					}
				} else {
					$larikesini = 'keu_slave_print_penagihanKomoditi';
				}

				$tab .= "<td></td>";
				$tab .= "<td align=center><img src=images/application/exchange.png class=zImgBtn  title='Rubah Faktur dan Bukti Potong' onclick=\"changefaktur('" . $rstr['noinvoice'] . "','" . $rstr['kodept'] . "',event);\" ></td>";
				$tab .= "<td align=center><img src=images/skyblue/posted.png class=zImgBtn  title='Posted " . $rstr['noinvoice'] . "'></td>";
				$tab .= "<td align=center>" . $excldata . "</td>";
				$tab .= "<td align=center><img title='" . $_SESSION['lang']['view'] . "' class=zImgBtn onclick=\"viewlistfile(event,'" . $rstr['noinvoice'] . "')\" src='images/download.png'/></td>";
				$tab .= "<td align=center>
					<img src=images/pdf.jpg class=zImgBtn  title='Detail " . $rstr['noinvoice'] . "' onclick=\"masterPDF('keu_penagihanht','" . $rstr['noinvoice'] . "','','" . $larikesini . "',event);\" >
					</td>";
				$tab .= "<td align=center>
					<img src=images/pdf.jpg class=zImgBtn  title='Faktur Pajak " . $rstr['noinvoice'] . "' onclick=\"masterPDF('keu_penagihanht','" . $rstr['noinvoice'] . "','','" . $filepdf . "',event);\" >
					</td>";

				$tab .= "<td align=center></td>";
			}


			##Cek Effil
			$strx = "select count(noinvoice) as count from " . $dbname . ".keu_efillinv where noinvoice='" . $rstr['noinvoice'] . "'";
			$resx = fetchdata($strx);
			$countefil = $resx[0]['count'];
			if ($countefil > 0) {
				$tab .= "<td style='text-align:center'><img src='images/efill.png' class='zImgBtn' onclick=\"viewefill('" . $rstr['noinvoice'] . "',event)\" title='E-Filling System'></td>";
			} else {
				$tab .= "<td>&nbsp;</td>";
			}

			$tab .= "<td>" . $namakaryawan[$rstr['createby']] . "</td>";
			$tab .= "<td>" . tanggalnormal($rstr['createtime']) . " " . substr($rstr['createtime'], 11, 8) . "</td>";
			$tab .= "<td>" . $namakaryawan[$rstr['updateby']] . "</td>";
			$tab .= "<td>" . tanggalnormal($rstr['updatetime']) . " " . substr($rstr['updatetime'], 11, 8) . "</td>";



			$tab .= "</tr>";
		}
		$skeupenagih = $owlPDO->query("select count(*) as rowd from " . $dbname . ".keu_penagihanht where nokontrak!=''  " . $where);
		$skeupenagih->setFetchMode(PDO::FETCH_ASSOC);
		$rkeupenagih = $skeupenagih->fetch();
		$totrows = ceil($rkeupenagih['rowd'] / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}
		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
		}
		$footd = "</tr>
        <tr><td colspan=27 align=center>

        <button class=mybutton onclick=cariData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
        <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>
        <button class=mybutton onclick=cariData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
        </td>
        </tr>";
		echo $tab . "####" . $footd;
		break;

	case 'getData':
		$sdata = $owlPDO->query("select distinct * from " . $dbname . ".keu_penagihanht where noinvoice='" . $param['noinvoice'] . "'");
		$sdata->setFetchMode(PDO::FETCH_ASSOC);
		$rdata = $sdata->fetch();

		if ($rdata['tglbuktipotong'] == '0000-00-00') {
			$tanggalbupot = "";
		} else {
			$tanggalbupot = tanggalnormal($rdata['tglbuktipotong']);
		}

		echo $rdata['noinvoice'] . "###" . $rdata['kodeorg'] . "###" . tanggalnormal(substr($rdata['tanggal'], 0, 10)) . "###" . $rdata['nokontrak'] . "###" .
			$rdata['kodecustomer'] . "###" . number_format($rdata['nilaiinvoice'], 0) . "###" . number_format($rdata['nilaippn']) . "###" .
			tanggalnormal(substr($rdata['jatuhtempo'], 0, 10)) . "###" . $rdata['bayarke'] . "###" . $rdata['debet'] . "###" . $rdata['kredit'] . "###" .
			$rdata['nofakturpajak'] . "###" . $rdata['keterangan1'] . "###" . $rdata['keterangan2'] . "###" . $rdata['keterangan3'] . "###" .
			$rdata['keterangan4'] . "###" . $rdata['keterangan5'] . "###" . $rdata['rupiah1'] . "###" . $rdata['rupiah2'] . "###" . $rdata['rupiah3'] . "###" .
			$rdata['rupiah4'] . "###" . $rdata['rupiah5'] . "###" . $rdata['matauang'] . "###" . $rdata['kurs'] . "###" . $rdata['keterangan6'] . "###" .
			$rdata['rupiah6'] . "###" . $rdata['keterangan7'] . "###" . $rdata['rupiah7'] . "###" . $rdata['keterangan8'] . "###" . $rdata['rupiah8'] . "###" .
			$rdata['ttd'] . "###" . $rdata['jenis'] . "###" . number_format($rdata['kuantitas']) . "###" . $rdata['periode'] . "###" . $rdata['nobuktipotong'] . "###" .
			$tanggalbupot . "###" . $rdata['jenispph'] . "###" . number_format($rdata['pphrupiah']) . "###" . $rdata['jenispenghasilan'] . "###" .
			$rdata['carabayar'] . "###" . $rdata['npwp'] . "###" . $rdata['berikat'] . "###" . $nmakun[$rdata['jenispph']] . "###" .
			$jnsp[$rdata['jenispenghasilan']] . "###" . $rdata['keterangantambahan'] . "###" . $rdata['notransaksikasbank'] . "###" .
			$rdata['jenisinvoice'] . "###" . $rdata['kodebarang'] . "###" . $rdata['transport'] . "###" . $rdata['npwpunit'] . "###" . $rdata['tipeinvoice']. "###" . $rdata['nilaipph'];

		break;

	case 'getFormNosipb':
		$optSupplierCr = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sSuplier = $owlPDO->query("select distinct kodecustomer,namacustomer from " . $dbname . ".pmn_4customer where kodecustomer='" . $param['kodecustomer'] . "' order by namacustomer asc");
		$sSuplier->setFetchMode(PDO::FETCH_ASSOC);
		while ($rSupplier = $sSuplier->fetch()) {
			$optSupplierCr = "<option value='" . $rSupplier['kodecustomer'] . "'>" . $rSupplier['namacustomer'] . "</option>";
		}

		$optjenis .= "<option value='kontrak'>" . $_SESSION['lang']['NoKontrak'] . "</option>";
		$optjenis .= "<option value='disposal'>" . $_SESSION['lang']['disposal'] . "</option>";

		$form = "<fieldset style=float: left;>
               <legend><i>" . $_SESSION['lang']['find'] . "</i></legend>
                   <table>
                   <tr><td>" . $_SESSION['lang']['nodok'] . "</td><td>:</td><td><input type=text class=myinputtext id=nosipbcr onkeypress='enterkey(event,findNosipb)' style='width:145px' /></td></tr>
                   <tr hidden><td>" . $_SESSION['lang']['nmcust'] . "</td><td>:</td><td><select id=custId style='width:150px'>" . $optSupplierCr . "</select></td></tr>
                   <tr><td colspan=2></td><td><button class=mybutton onclick=findNosipb()>" . $_SESSION['lang']['find'] . "</button></td></tr></table></fieldset>
               <fieldset><legend><i>" . $_SESSION['lang']['result'] . "</i></legend><div id=container2 ></fieldset></div>";
		echo $form;
		break;

	case 'getFormFaktur':
		$form = "<fieldset>
            <table>
                <tr>
                    <td>Search</td>
                    <td><input type=text class=myinputtext id=fakturcari onkeypress='enterkey(event,findnofaktur)' style='width:145px' /></td>
                    <td><input type=text style='display:none' id=kodeorgcari value='" . $param['kodeorganisasi'] . "'></td>
                    <td><button class=mybutton onclick=findnofaktur()>Search</button></td>
                </tr>
            </table>
            </fieldset>
            
            <fieldset>
            <legend><i>Result</i></legend>
            <div id=containerfaktur style=overflow:auto;max-width:100%;max-height:335px;></fieldset></div>";
		echo $form;
		break;

	case 'getnofaktur':
		$tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
		$tab .= "<thead>";
		$tab .= "<tr><td align=center>No</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['pt'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['nofaktur'] . "</td>";
		$tab .= "</tr></thead><tbody>";
		if ($param['fakturcari'] != '') {
			$whr .= " and faktur like '%" . $param['fakturcari'] . "%'";
		}

		$ptkont = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
		$str = " select * from " . $dbname . ".keu_fakturpajakdt where 1=1 and status='1' and expired='0' and notransaksi='' and pt='" . $ptkont[$param['kodeorgcari']] . "' " . $whr . " and faktur not in (select nofakturpajak from " . $dbname . ".keu_penagihanht) order by faktur asc";

		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = '';
		while ($bar = $res->fetch()) {
			$no += 1;
			if ($no == 1) {
				$bolt = "<b>";
				$bolt2 = "</b>";
			} else {
				$bolt = $bolt2 = "";
			}
			$set = "style=cursor:pointer; onclick=setFaktur('" . $no . "','" . $bar['faktur'] . "')";
			$tab .= "<tr " . $set . " title='click to add'  class=rowcontent>
                    <td align=center style=cursor:pointer>" . $bolt . "" . $no . "" . $bolt2 . "</td>
                    <td align=center  style=cursor:pointer>" . $bolt . "" . $bar['pt'] . "" . $bolt2 . "</td>
                    <td style=cursor:pointer>" . $bolt . "" . $bar['faktur'] . "" . $bolt2 . "</td>";
		}
		$tab .= "</tbody></table>";
		echo $tab;
		break;

	case 'changefaktur':
		$str = " select * from " . $dbname . ".keu_fakturpajakdt where 1=1 and status='1' and expired='0' and notransaksi='' and pt='" . $param['pt'] . "' and faktur not in (select nofakturpajak from " . $dbname . ".keu_penagihanht) order by faktur asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optfaktur = "<option value=''></option>";
		while ($bar = $res->fetch()) {
			@$optfaktur .= "<option value='" . $bar['faktur'] . "'>" . $bar['faktur'] . "</option>";
		}

		$str = " select * from " . $dbname . ".keu_penagihanht where noinvoice='" . $param['noinvoice'] . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();

		$form = "<fieldset>
           <table>
                <tr>
                    <input type=text id=tempnoinvoice style=display:none value='" . $param['noinvoice'] . "'>
                    <td>" . $_SESSION['lang']['nofakturlama'] . "</td><td>:</td>
                    <td><input type=text class=myinputtext disabled id=fakturlama style='width:145px' value='" . $bar['nofakturpajak'] . "' /></td>
                </tr><tr>
                    <td>" . $_SESSION['lang']['nofakturbaru'] . "</td><td>:</td>
                    <td><select id=fakturbaru  style=width:150px;>" . $optfaktur . "</select>
                        <img id='fakturbaru' onclick=z.elSearch('fakturbaru',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
                </tr><tr>
                    <td colspan=3><hr></td>
                </tr><tr>
                    <td>" . $_SESSION['lang']['buktipotonglama'] . "</td><td>:</td>
                    <td><input type=text class=myinputtext disabled id=bupotlama value='" . $bar['nobuktipotong'] . "' style='width:145px' /></td>
                </tr><tr>
                    <td>" . $_SESSION['lang']['buktipotongbaru'] . "</td><td>:</td>
                    <td><input type=text class=myinputtext id=bupotbaru value='" . $bar['nobuktipotong'] . "' style='width:145px' /></td>
                </tr><tr>
                    <td colspan=2></td><td><button class=mybutton onclick=savechfaktur()>" . $_SESSION['lang']['save'] . "</button>
                                           <button class=mybutton onclick=cancelchfaktur()>" . $_SESSION['lang']['cancel'] . "</button>
                    </td>
                </tr>
            </table>
            </fieldset>";
		echo $form;
		break;

	case 'savechfaktur':
		$set = "";
		if ($param['fakturbaru'] != '') {
			//update faktur pajak
			$fp = "update " . $dbname . ".keu_fakturpajakdt set notransaksi='" . $param['noinvoice'] . "', expired='1', updateby='" . $_SESSION['standard']['userid'] . "' where faktur='" . $param['fakturbaru'] . "'";
			try {
				$owlPDO->exec($fp);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}

		if ($param['fakturbaru'] != '' && $param['bupotbaru'] == '') {
			$set = " nofakturpajak='" . $param['fakturbaru'] . "' ";
		}

		if ($param['fakturbaru'] == '' && $param['bupotbaru'] != '') {
			$set = " nobuktipotong='" . $param['bupotbaru'] . "' ";
		}

		if ($param['fakturbaru'] != '' && $param['bupotbaru'] != '') {
			$set = " nofakturpajak='" . $param['fakturbaru'] . "',nobuktipotong='" . $param['bupotbaru'] . "' ";
		}

		if ($param['fakturbaru'] != '' || $param['bupotbaru'] != '') {
			//update penagihan
			$tg = "update " . $dbname . ".keu_penagihanht set " . $set . " where noinvoice='" . $param['noinvoice'] . "'";
			try {
				$owlPDO->exec($tg);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;

	case 'getFormAfiliasi':
		$form = "<fieldset style='float: left;'>
                <table><tr>
                <td>" . $_SESSION['lang']['noinvoiceafiliasi'] . "</td><td>:</td>
                <td><input type=text class=myinputtext id=noafiliasi /></td>
                </tr><tr>
                <td colspan=2></td><td><button class=mybutton onclick=inputAfiliasi('" . $param['noinvoice'] . "')>" . $_SESSION['lang']['save'] . "</button></td></tr>
                <table>
                </fieldset>";
		echo $form;
		break;

	case 'getnpwp':
		$strkd = " select npwp from " . $dbname . ".pmn_4customer where kodecustomer='" . $param['kodecustomer'] . "'";
		$reskd = $owlPDO->query($strkd) or die(print " Gagal: " . PDOException::getMessage());
		$reskd->setFetchMode(PDO::FETCH_ASSOC);
		$barkd = $reskd->fetch();
		$npwp = $barkd['npwp'];

		echo $npwp;
		break;

	case 'getnosibp':

		#= perhitungan persen / perubahan ppn dari 10 ke 11
		if (tanggalsystemn($param['tanggal']) < '2022-04-01') {
			$persentasesatu = '1.1';
			$persentasedua = '0.1';
		} else {
			$persentasesatu = '1.11';
			$persentasedua = '0.11';
		}


		switch ($param['jenisdok']) {
			case 'DS':

				$tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
				$tab .= "<thead><tr>";
				$tab .= "<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['kodeasset'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['namaasset'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['jenis'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['status'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['nilaibuku'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['akumulasipenyusutan'] . "</td>";
				$tab .= "</tr></thead><tbody>";

				$str = "select * from " . $dbname . ".keu_disposalasset where jenisket=11 and statuspersetujuan=1 and notransaksi not in (select nokontrak from " . $dbname . ".keu_penagihanht) ";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$no = $maxdisplay;
				while ($bar = $res->fetch()) {

					$strkd = " select noakun from " . $dbname . ".keu_5akunbank where pemilik='" . substr($bar['notransaksi'], 0, 4) . "'";
					$reskd = $owlPDO->query($strkd) or die(print " Gagal: " . PDOException::getMessage());
					$reskd->setFetchMode(PDO::FETCH_ASSOC);
					$barkd = $reskd->fetch();
					$bayarke = $barkd['noakun'];
					$jenispenghasilan = '';

					$ppn = 0;
					$ppn = floor($bar['nilaibuku'] * $persentasedua);

					$arrstatus = array('1' => 'Disposal', '2' => 'Write-off');
					$whr = "id='" . $bar['jenisket'] . "'";
					$optjns = makeOption($dbname, 'keu_5jenisdisposalasset', 'id,keterangan', $whr);
					$whras = "kodeasset='" . $barkd['kodeasset'] . "'";
					$nmasset = makeOption($dbname, 'sdm_daftarasset', 'kodeasset,namasset', $whras);
					$no += 1;
					$brt = "style=cursor:pointer; onclick=setData('" . $bar['notransaksi'] . "','','" . substr($bar['notransaksi'], 0, 4) . "','IDR','" . number_format($bar['nilaibuku'], 0) . "','" . $bayarke . "','" . $ppn . "','','','','','','','','" . $jenispenghasilan . "')";
					$tab .= "<tr " . $brt . " class=rowcontent>";
					$tab .= "<td style=cursor:pointer; align=center>" . $bar['notransaksi'] . "</td>";
					$tab .= "<td style=cursor:pointer; align=center>" . $bar['kodeasset'] . "</td>";
					$tab .= "<td style=cursor:pointer; align=center>" . $nmasset[$bar['kodeasset']] . "</td>";
					$tab .= "<td style=cursor:pointer; align=left>" . $arrstatus[substr($bar['jenisket'], 0, 1)] . "</td>";
					$tab .= "<td style=cursor:pointer; align=left>" . $optjns[$bar['jenisket']] . "</td>";
					$tab .= "<td style=cursor:pointer; align=left>" . $bar['nilaibuku'] . "</td>";
					$tab .= "<td style=cursor:pointer; align=left>" . $bar['akumulasipenyusutan'] . "</td></tr>";
				}

				break;

			case 'BA':
			case 'Termin':

				$tab = '';

				# Make Option Barang
				$namabarang = makeOption($dbname, "log_5masterbarang", "kodebarang,namabarang", "kelompokbarang='400'");

				if ($param['kodebarang'] == '40000003') { # New karena dasar TBS ga pake Nodok
					$tab .= "Tipe :" . $param['jenisinvoice'] . " ";
					$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
					$tab .= "<thead><tr>";
					$tab .= "<td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['kodecustomer'] . "</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['namabarang'] . "</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['tanggal'] . " Kontrak</td>";
					$tab .= "</tr></thead><tbody>";

					if ($param['txtfind'] != '') {
						// $whr.=" and nokontrak like '%".$param['txtfind']."%'";
						$whr .= " and (nokontrak_manual like '%" . $param['txtfind'] . "%' or nokontrak like '%" . $param['txtfind'] . "%')";
					}
					if ($param['kodebarang'] != '') {
						$whr .= " and kodebarang='" . $param['kodebarang'] . "'";
					}
					if ($param['kodecustomer'] != '') {
						$whr .= " and koderekanan='" . $param['kodecustomer'] . "'";
					}

					#= ambil data kodept
					$sdata = " select induk from " . $dbname . ".organisasi where  kodeorganisasi='" . $param['kodeorganisasi'] . "'";
					$sdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
					$sdata->setFetchMode(PDO::FETCH_ASSOC);
					$rdata = $sdata->fetch();
					$kodept = $rdata['induk'];

					$sdata = " select * from " . $dbname . ".pmn_kontrakjual where 1=1 " . $whr . " and (termbayar='" . $param['jenisdok'] . "' or termbayar='BA') and kodept='" . $kodept . "'";
					$sdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
					$sdata->setFetchMode(PDO::FETCH_ASSOC);
					while ($rdata = $sdata->fetch()) {

						if ($rdata['ppn'] == 1) {
							$rdata['hargasatuan'] = $rdata['hargasatuan'] / $persentasesatu;
						}

						$nilaiQty = $rdata['kuantitaskontrak'];
						$nilaiSatuan = $rdata['hargasatuan'];
						$nilaiKontrak = $rdata['hargasatuan'] * $rdata['kuantitaskontrak'];


						// if($param['jenisinvoice']=='UM'){
						// 	##= uang muka

						// 	$optPersen=makeOption($dbname,'pmn_5terminbayar','kode,satu'," kode='".$rdata['kdtermin']."'");
						// 	$persen=$optPersen[$rdata['kdtermin']];

						// 	$jumlahkg=($rdata['kuantitaskontrak'])*($persen/100);
						// 	$jumlahrp=($rdata['hargasatuan'])*($rdata['kuantitaskontrak'])*($persen/100);

						// 	$nilInvoice=($rdata['hargasatuan'])*($rdata['kuantitaskontrak'])*($persen/100);
						// 	$sisaKuantitas=($rdata['kuantitaskontrak'])*($persen/100);


						// 	#= cek uang muka sudah ada?
						// 	$str =" select count(*) as jumlahinv,sum(nilaiinvoice) as jumnlahnilai, sum(kuantitas) as jumlahkuantitas 
						// 			from ".$dbname.".keu_penagihanht where nokontrak='".$rdata['nokontrak']."' 
						// 			and tanggal<'".tanggalsystemn($param['tanggal'])."'";
						// 			// echo $str;
						// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						// 	$res->setFetchMode(PDO::FETCH_ASSOC);
						// 	$bar=$res->fetch();
						// 		$jumnlahnilai=$bar['jumnlahnilai'];
						// 		$jumlahkuantitas=$bar['jumlahkuantitas'];
						// 		$jumlahinv=$bar['jumlahinv'];

						// 	if($jumlahinv>0){
						// 		$sisaKuantitas=$sisaKuantitas-$jumlahkuantitas;
						// 		$nilInvoice=$nilInvoice-$jumnlahnilai;
						// 	}

						// }else{

						// 	##= pelunasan
						// 	$str =" select count(*) as jumlahinv,sum(nilaiinvoice) as jumnlahnilai, sum(kuantitas) as jumlahkuantitas 
						// 			from ".$dbname.".keu_penagihanht where nokontrak='".$rdata['nokontrak']."' 
						// 			and tanggal<'".tanggalsystemn($param['tanggal'])."'";
						// 			// echo $str;
						// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						// 	$res->setFetchMode(PDO::FETCH_ASSOC);
						// 	$bar=$res->fetch();

						// 	$jumlahRupiah=floatval($bar['jumnlahnilai']);
						// 	$jumlahkuantitas=$bar['jumlahkuantitas'];

						// 	#= ambil dari BAST
						// 	$str =" select count(*) as jumlahbast, sum(jumlah) as jumlahkuantitas 
						// 		from ".$dbname.".pmn_bast where nokontrak='".$rdata['nokontrak']."' and posting='1'";
						// 	/*	
						// 		$str =" select count(*) as jumlahbast, sum(kg) as jumlahkuantitas 
						// 		from ".$dbname.".pmn_billofloading where nokontrak='".$rdata['nokontrak']."' and posting='1'";
						// 	*/
						// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						// 	$res->setFetchMode(PDO::FETCH_ASSOC);
						// 	$bar=$res->fetch();
						// 		$jumlahbast=$bar['jumlahbast'];
						// 		$jumlahkgbl=$bar['jumlahkuantitas'];
						// 	$sisaKuantitas=($jumlahkgbl-(intval($jumlahkuantitas)));
						// 	$nilInvoice=($rdata['hargasatuan']*$jumlahkgbl)-$jumlahRupiah;

						// 	$jumlahkg=$jumlahkgbl;
						// 	$jumlahrp=($rdata['hargasatuan']*$jumlahkgbl);

						// }
						//jumlahRupiah


						#cek apakah sudah ada termin bayar atau belum
						#buat if, jika sudah ada maka pake sisa termin
						#sisa termin

						#statusberikat 0 ppn dimunculkan
						#statusberikat 1 ppn tidak dimunculkan (tidak menjurnal)
						if ($rdata['berikat'] == '0') {
							$ppnnya = floor($nilInvoice * $persentasedua);
						} else {
							$ppnnya = 0;
						}

						$strkd = " select jenispph,pphpersen,carabayar,jenispenghasilan,npwp from " . $dbname . ".pmn_4customer where 
							kodecustomer='" . $rdata['koderekanan'] . "'";
						$reskd = $owlPDO->query($strkd) or die(print " Gagal: " . PDOException::getMessage());
						$reskd->setFetchMode(PDO::FETCH_ASSOC);
						$barkd = $reskd->fetch();
						$carabayar = $barkd['carabayar'];
						$jenispenghasilan = $barkd['jenispenghasilan'];
						$npwp = $barkd['npwp'];

						// $whrorg="induk='".$rdata['kodept']."' and tipe='HOLDING'";
						$whrorg = "induk='" . $rdata['kodept'] . "' and tipe='KANWIL'";
						$optKdHo = makeOption($dbname, 'organisasi', 'induk,kodeorganisasi', $whrorg);
						$whrCus = "kodecustomer='" . $rdata['koderekanan'] . "'";
						$optnmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer', $whrCus);

						$brt = "style=cursor:pointer; onclick=setDataTbs('" . $rdata['nokontrak_manual'] . "','" . $rdata['koderekanan'] . "','" . $optKdHo[$rdata['kodept']] . "','" . $rdata['matauang'] . "','" . number_format($nilInvoice, 0) . "','" . $rdata['rekening'] . "','" . number_format($ppnnya, 0) . "','" . number_format($sisaKuantitas, 0) . "','" . $npwp . "','" . $rdata['berikat'] . "','" . $rdata['kodebarang'] . "','" . $jenispph . "','" . $pphpersen . "','" . $carabayar . "','" . $jenispenghasilan . "')";
						$tab .= "<tr " . $brt . " class=rowcontent><td style=cursor:pointer>" . $rdata['nokontrak_manual'] . "</td>";
						$tab .= "<td style=cursor:pointer align=center>" . $rdata['koderekanan'] . "</td>";
						$tab .= "<td style=cursor:pointer align=center>" . $namabarang[$rdata['kodebarang']] . "</td>";
						$tab .= "<td style=cursor:pointer align=center>" . tanggalnormal($rdata['tanggalkontrak']) . "</td>";
					}
				} else {
					// echo"<pre>";
					// print_r($param);
					// echo"</pre>";
					// exit("Error:A");
					$tab .= "Tipe :" . $param['jenisinvoice'] . " ";
					$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
					$tab .= "<thead><tr>";
					$tab .= "<td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['kodecustomer'] . "</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['namacust'] . "</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['kuantitas'] . " " . $_SESSION['lang']['kontrak'] . "</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['persen'] . " " . $param['jenisinvoice'] . "</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['kuantitas'] . " " . $param['jenisinvoice'] . "</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['rp'] . " " . $param['jenisinvoice'] . "</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['sisa'] . "(Kg)</td>";
					$tab .= "<td align=center>" . $_SESSION['lang']['sisa'] . "(Rp)</td>";
					$tab .= "</tr></thead><tbody>";

					// if($param['custId']!=''){
					// $whr.=" and koderekanan='".$param['custId']."'";
					// }
					if ($param['txtfind'] != '') {
						$whr .= " and nokontrak like '%" . $param['txtfind'] . "%'";
					}
					if ($param['kodebarang'] != '') {
						$whr .= " and kodebarang='" . $param['kodebarang'] . "'";
					}
					if ($param['kodecustomer'] != '') {
						$whr .= " and koderekanan='" . $param['kodecustomer'] . "'";
					}


					#= ambil data kodept
					$sdata = " select induk from " . $dbname . ".organisasi where  kodeorganisasi='" . $param['kodeorganisasi'] . "'";
					// echo $sdata;exit();
					$sdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
					$sdata->setFetchMode(PDO::FETCH_ASSOC);
					$rdata = $sdata->fetch();
					$kodept = $rdata['induk'];


					$sdata = " select * from " . $dbname . ".pmn_kontrakjual where 1=1 " . $whr . " and termbayar='" . $param['jenisdok'] . "' and kodept='" . $kodept . "'";
					// echo $sdata;
					// echo $sdata;exit();
					$sdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
					$sdata->setFetchMode(PDO::FETCH_ASSOC);
					while ($rdata = $sdata->fetch()) {

						if ($rdata['ppn'] == 1) {
							$rdata['hargasatuan'] = $rdata['hargasatuan'] / $persentasesatu;
						}

						$nilaiQty = $rdata['kuantitaskontrak'];
						$nilaiSatuan = $rdata['hargasatuan'];
						$nilaiKontrak = $rdata['hargasatuan'] * $rdata['kuantitaskontrak'];


						if ($param['jenisinvoice'] == 'UM') {
							##= uang muka

							$optPersen = makeOption($dbname, 'pmn_5terminbayar', 'kode,satu', " kode='" . $rdata['kdtermin'] . "'");
							$persen = $optPersen[$rdata['kdtermin']];

							$jumlahkg = ($rdata['kuantitaskontrak']) * ($persen / 100);
							$jumlahrp = ($rdata['hargasatuan']) * ($rdata['kuantitaskontrak']) * ($persen / 100);

							$nilInvoice = ($rdata['hargasatuan']) * ($rdata['kuantitaskontrak']) * ($persen / 100);
							$sisaKuantitas = ($rdata['kuantitaskontrak']) * ($persen / 100);


							#= cek uang muka sudah ada?
							$str = " select count(*) as jumlahinv,sum(nilaiinvoice) as jumnlahnilai, sum(kuantitas) as jumlahkuantitas 
									from " . $dbname . ".keu_penagihanht where nokontrak='" . $rdata['nokontrak'] . "' 
									and tanggal<'" . tanggalsystemn($param['tanggal']) . "'";
							// echo $str;
							$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							$bar = $res->fetch();
							$jumnlahnilai = $bar['jumnlahnilai'];
							$jumlahkuantitas = $bar['jumlahkuantitas'];
							$jumlahinv = $bar['jumlahinv'];

							if ($jumlahinv > 0) {
								$sisaKuantitas = $sisaKuantitas - $jumlahkuantitas;
								$nilInvoice = $nilInvoice - $jumnlahnilai;
							}
						} else {

							##= pelunasan
							$str = " select count(*) as jumlahinv,sum(nilaiinvoice) as jumnlahnilai, sum(kuantitas) as jumlahkuantitas 
									from " . $dbname . ".keu_penagihanht where nokontrak='" . $rdata['nokontrak'] . "'";
							// echo $str;
							$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							$bar = $res->fetch();

							$jumlahRupiah = floatval($bar['jumnlahnilai']);
							$jumlahkuantitas = $bar['jumlahkuantitas'];

							#= ambil dari BAST
							$str = " select count(*) as jumlahbast, sum(kgpembeli) as jumlahkuantitas, sum(rpclaimffa) as rpclaimffa, sum(rpclaimmoisture) as rpclaimmoisture, sum(rpclaimdirt) as rpclaimdirt, sum(rpclaimdobi) as rpclaimdobi, sum(rpclaimbroken) as rpclaimbroken, sum(rpclaimmdani) as rpclaimmdani, sum(rpclaimimpurities) as rpclaimimpurities
								from " . $dbname . ".pmn_bast where nokontrak='" . $rdata['nokontrak'] . "' and posting='1'";
							/*	
								$str =" select count(*) as jumlahbast, sum(kg) as jumlahkuantitas 
								from ".$dbname.".pmn_billofloading where nokontrak='".$rdata['nokontrak']."' and posting='1'";
							*/
							$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							$bar = $res->fetch();
							$jumlahbast = $bar['jumlahbast'];
							$jumlahkgbl = $bar['jumlahkuantitas'];
							$rpclaimmutu = $bar['rpclaimffa'] + $bar['rpclaimmoisture'] + $bar['rpclaimdirt'] + $bar['rpclaimdobi'] + $bar['rpclaimbroken'] + $bar['rpclaimmdani'] + $bar['rpclaimimpurities'];

							$sisaKuantitas = ($jumlahkgbl - (intval($jumlahkuantitas)));
							$nilInvoice = ($rdata['hargasatuan'] * $jumlahkgbl) - $jumlahRupiah - $rpclaimmutu;

							$jumlahkg = $jumlahkgbl;
							$jumlahrp = ($rdata['hargasatuan'] * $jumlahkgbl);
						}
						//jumlahRupiah


						#cek apakah sudah ada termin bayar atau belum
						#buat if, jika sudah ada maka pake sisa termin
						#sisa termin

						#statusberikat 0 ppn dimunculkan
						#statusberikat 1 ppn tidak dimunculkan (tidak menjurnal)
						if ($rdata['berikat'] == '0') {
							$ppnnya = floor($nilInvoice * $persentasedua);
						} else {
							$ppnnya = 0;
						}

						$strkd = " select jenispph,pphpersen,carabayar,jenispenghasilan,npwp from " . $dbname . ".pmn_4customer where 
							kodecustomer='" . $rdata['koderekanan'] . "'";
						$reskd = $owlPDO->query($strkd) or die(print " Gagal: " . PDOException::getMessage());
						$reskd->setFetchMode(PDO::FETCH_ASSOC);
						$barkd = $reskd->fetch();
						$carabayar = $barkd['carabayar'];
						$jenispenghasilan = $barkd['jenispenghasilan'];
						$npwp = $barkd['npwp'];

						// $whrorg="induk='".$rdata['kodept']."' and tipe='HOLDING'";
						$whrorg = "induk='" . $rdata['kodept'] . "' and tipe='KANWIL'";
						$optKdHo = makeOption($dbname, 'organisasi', 'induk,kodeorganisasi', $whrorg);
						$whrCus = "kodecustomer='" . $rdata['koderekanan'] . "'";
						$optnmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer', $whrCus);

						// if ($nilInvoice>0) {
						$brt = "style=cursor:pointer; onclick=setData('" . $rdata['nokontrak'] . "','" . $rdata['koderekanan'] . "','" . $optKdHo[$rdata['kodept']] . "','" . $rdata['matauang'] . "','" . number_format($nilInvoice, 0) . "','" . $rdata['rekening'] . "','" . number_format($ppnnya, 0) . "','" . number_format($sisaKuantitas, 0) . "','" . $npwp . "','" . $rdata['berikat'] . "','" . $rdata['kodebarang'] . "','" . $jenispph . "','" . $pphpersen . "','" . $carabayar . "','" . $jenispenghasilan . "')";
						$tab .= "<tr " . $brt . " class=rowcontent><td style=cursor:pointer>" . $rdata['nokontrak'] . "</td>";
						$tab .= "<td style=cursor:pointer>" . $rdata['koderekanan'] . "</td>";
						$tab .= "<td style=cursor:pointer>" . $optnmcust[$rdata['koderekanan']] . "</td>";
						$tab .= "<td style=cursor:pointer align=right>" . number_format($nilaiQty) . "</td>";
						$tab .= "<td style=cursor:pointer align=right>" . number_format($persen) . "</td>";
						$tab .= "<td style=cursor:pointer align=right>" . number_format($jumlahkg) . "</td>";
						$tab .= "<td style=cursor:pointer align=right>" . number_format($jumlahrp) . "</td>";
						$tab .= "<td style=cursor:pointer align=right>" . number_format($sisaKuantitas) . "</td>";
						$tab .= "<td align=right style=cursor:pointer>" . number_format($nilInvoice) . "</td></tr>";
						// }
					}
				}

				break;

			case 'UM':

				$tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
				$tab .= "<thead><tr>";
				$tab .= "<td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['kodecustomer'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['namacust'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['sisa'] . "</td>";
				$tab .= "</tr></thead><tbody>";

				if ($param['custId'] != '') {
					$whr .= " and koderekanan='" . $param['custId'] . "'";
				}
				if ($param['txtfind'] != '') {
					$whr .= " and nokontrak like '%" . $param['txtfind'] . "%'";
				}

				$sdata = " select * from " . $dbname . ".pmn_kontrakjual where 1=1 " . $whr . " and termbayar='" . $param['jenisdok'] . "'";
				$sdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
				$sdata->setFetchMode(PDO::FETCH_ASSOC);
				while ($rdata = $sdata->fetch()) {

					$nilaiQty = $rdata['kuantitaskontrak'];
					$nilaiSatuan = $rdata['hargasatuan'];
					$nilaiKontrak = $rdata['hargasatuan'] * $rdata['kuantitaskontrak'];

					if ($rdata['ppn'] == 1) {
						$sqlnilai = " sum(nilaiinvoice + nilaippn) as jumnlahnilai ";
					} else {
						$sqlnilai = " sum(nilaiinvoice) as jumnlahnilai ";
					}

					#termin pertama
					$iCek = $owlPDO->query(" select count(*) as jumlahinv," . $sqlnilai . ", sum(kuantitas) as jumlahkuantitas 
                            from " . $dbname . ".keu_penagihanht where nokontrak='" . $rdata['nokontrak'] . "' ");
					$iCek->setFetchMode(PDO::FETCH_ASSOC);
					$dCek =  $iCek->fetch();
					$bnykInv = intval($dCek['jumlahinv']);
					$jumlahRupiah = floatval($dCek['jumnlahnilai']);

					if ($bnykInv == 0) {
						$sisaKuantitas = $nilaiQty;
					} else if ($bnykInv == 1) {
						$sisaKuantitas = $nilaiQty;
						if ($rdata['ppn'] == 1 and $rdata['berikat'] == '1') {
							$nilaiKontrak = $nilaiKontrak / $persentasesatu;
						}
					} else {
						$sisaKuantitas = ($nilaiQty - (intval($dCek['jumlahkuantitas']) - $nilaiQty));
						if ($rdata['ppn'] == 1 and $rdata['berikat'] == '1') {
							$nilaiKontrak = $nilaiKontrak / $persentasesatu;
						}
					}
					$sisaRupiah = $nilaiKontrak - $jumlahRupiah;

					if ($bnykInv > 0) { //jika sudah ada inputan penagihan
						$masuk = 1;
						$nilInvoice = $sisaRupiah;
					} else { //jika blm pernah ada inputan penagihan
						$optPersen = makeOption($dbname, 'pmn_5terminbayar', 'kode,satu', " kode='" . $rdata['kdtermin'] . "'");
						$persen = $optPersen[$rdata['kdtermin']];

						$nilInvoice = ($rdata['hargasatuan']) * ($rdata['kuantitaskontrak']) * ($persen / 100);
						$sisaKuantitas = ($rdata['kuantitaskontrak']) * ($persen / 100);
					}

					#cek apakah sudah ada termin bayar atau belum
					#buat if, jika sudah ada maka pake sisa termin
					#sisa termin

					#statusberikat 0 ppn dimunculkan
					#statusberikat 1 ppn tidak dimunculkan (tidak menjurnal)
					if ($rdata['ppn'] == 1 and $rdata['berikat'] == '0') {
						$nilInvoice = $nilInvoice / $persentasesatu;
						$ppnnya = floor($nilInvoice * $persentasedua);
					} else {
						if ($bnykInv == 0) {
							if ($rdata['ppn'] == 1 and $rdata['berikat'] == '1') {
								$nilInvoice = $nilInvoice / $persentasesatu;
							}
						}

						$ppnnya = 0;
					}

					if ($rdata['ppn'] == 0 and $rdata['berikat'] == '0') {
						$ppnnya = floor($nilInvoice * $persentasedua);
					}

					$strkd = " select jenispph,pphpersen,carabayar,jenispenghasilan,npwp from " . $dbname . ".pmn_4customer where kodecustomer='" . $rdata['koderekanan'] . "'";
					$reskd = $owlPDO->query($strkd) or die(print " Gagal: " . PDOException::getMessage());
					$reskd->setFetchMode(PDO::FETCH_ASSOC);
					$barkd = $reskd->fetch();
					$carabayar = $barkd['carabayar'];
					$jenispenghasilan = $barkd['jenispenghasilan'];
					$npwp = $barkd['npwp'];

					// $whrorg="induk='".$rdata['kodept']."' and tipe='HOLDING'";
					$whrorg = "induk='" . $rdata['kodept'] . "' and tipe='KANWIL'";
					$optKdHo = makeOption($dbname, 'organisasi', 'induk,kodeorganisasi', $whrorg);
					$whrCus = "kodecustomer='" . $rdata['koderekanan'] . "'";
					$optnmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer', $whrCus);

					if ($nilInvoice > 0) {
						$brt = "style=cursor:pointer; onclick=setData('" . $rdata['nokontrak'] . "','" . $rdata['koderekanan'] . "','" . $optKdHo[$rdata['kodept']] . "','" . $rdata['matauang'] . "','" . number_format($nilInvoice, 0) . "','" . $rdata['rekening'] . "','" . number_format($ppnnya, 0) . "','" . number_format($sisaKuantitas, 0) . "','" . $npwp . "','" . $rdata['berikat'] . "','" . $rdata['kodebarang'] . "','" . $jenispph . "','" . $pphpersen . "','" . $carabayar . "','" . $jenispenghasilan . "')";
						$tab .= "<tr " . $brt . " class=rowcontent><td style=cursor:pointer>" . $rdata['nokontrak'] . "</td>";
						$tab .= "<td style=cursor:pointer>" . $rdata['koderekanan'] . "</td>";
						$tab .= "<td style=cursor:pointer>" . $optnmcust[$rdata['koderekanan']] . "</td>";
						$tab .= "<td align=right style=cursor:pointer>" . number_format($nilInvoice) . "</td></tr>";
					}
				}

				break;

			case 'PK':

				$tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
				$tab .= "<thead><tr>";
				$tab .= "<td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['kodecustomer'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['namacust'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['sisa'] . "</td>";
				$tab .= "</tr></thead><tbody>";

				if ($param['custId'] != '') {
					$whr .= " and koderekanan='" . $param['custId'] . "'";
				}
				if ($param['txtfind'] != '') {
					$whr .= " and nokontrak like '%" . $param['txtfind'] . "%'";
				}

				$sdata = " select * from " . $dbname . ".pmn_kontrakjual where 1=1 " . $whr . " and termbayar='" . $param['jenisdok'] . "' and nokontrak not in (select nokontrak from " . $dbname . ".keu_penagihanht where jenis='" . $param['jenisdok'] . "')";
				$sdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
				$sdata->setFetchMode(PDO::FETCH_ASSOC);
				while ($rdata = $sdata->fetch()) {

					#get dasar timbangan
					$sdatatimb = " select dasartimbangan from " . $dbname . ".pmn_5franco where id_franco='" . $rdata['franco'] . "'";
					$iCek = $owlPDO->query($sdatatimb) or die(print " Gagal: " . PDOException::getMessage());
					$iCek->setFetchMode(PDO::FETCH_ASSOC);
					$dCek = $iCek->fetch();
					$dasartimbangan = $dCek['dasartimbangan'];

					if ($dasartimbangan == 0) {
						$field = "beratbersih";
					}

					if ($dasartimbangan == 1) {
						$field = "kgpembeli";
					}

					#cek data timbangan
					$sdatatimb = " select sum(" . $field . ") as jumlah from " . $dbname . ".pabrik_timbangan where nokontrak='" . $rdata['nokontrak'] . "'";
					$iCek = $owlPDO->query($sdatatimb) or die(print " Gagal: " . PDOException::getMessage());
					$iCek->setFetchMode(PDO::FETCH_ASSOC);
					$dCek = $iCek->fetch();
					$jumlahtimb = $dCek['jumlah'];
					$jumlahseluruh = $rdata['kuantitaskontrak'] - $jumlahtimb;
					$nilaiQty = $rdata['kuantitaskontrak'];
					$nilaiSatuan = $rdata['hargasatuan'];
					$nilaiKontrak = $rdata['hargasatuan'] * $rdata['kuantitaskontrak'];

					#cek apakah sudah ada termin bayar atau belum
					#buat if, jika sudah ada maka pake sisa termin
					#sisa termin

					if ($rdata['ppn'] == 1 and $rdata['berikat'] == '0') {
						$nilaiKontrak = $nilaiKontrak / $persentasesatu;
						$ppnnya = floor($nilaiKontrak * $persentasedua);
					} else {
						if ($rdata['ppn'] == 1 and $rdata['berikat'] == '1') {
							$nilaiKontrak = $nilaiKontrak / $persentasesatu;
						}
						$ppnnya = 0;
					}

					if ($rdata['ppn'] == 0 and $rdata['berikat'] == '0') {
						$ppnnya = floor($nilaiKontrak * $persentasedua);
					}

					$strkd = " select jenispph,pphpersen,carabayar,jenispenghasilan,npwp from " . $dbname . ".pmn_4customer where kodecustomer='" . $rdata['koderekanan'] . "'";
					$reskd = $owlPDO->query($strkd) or die(print " Gagal: " . PDOException::getMessage());
					$reskd->setFetchMode(PDO::FETCH_ASSOC);
					$barkd = $reskd->fetch();
					$carabayar = $barkd['carabayar'];
					$jenispenghasilan = $barkd['jenispenghasilan'];
					$npwp = $barkd['npwp'];

					// $whrorg="induk='".$rdata['kodept']."' and tipe='HOLDING'";
					$whrorg = "induk='" . $rdata['kodept'] . "' and tipe='KANWIL'";
					$optKdHo = makeOption($dbname, 'organisasi', 'induk,kodeorganisasi', $whrorg);
					$whrCus = "kodecustomer='" . $rdata['koderekanan'] . "'";
					$optnmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer', $whrCus);

					if ($jumlahseluruh <= 0) {
						$brt = "style=cursor:pointer; onclick=setData('" . $rdata['nokontrak'] . "','" . $rdata['koderekanan'] . "','" . $optKdHo[$rdata['kodept']] . "','" . $rdata['matauang'] . "','" . number_format($nilaiKontrak, 0) . "','" . $rdata['rekening'] . "','" . number_format($ppnnya, 0) . "','" . number_format($nilaiQty, 0) . "','" . $npwp . "','" . $rdata['berikat'] . "','" . $rdata['kodebarang'] . "','" . $jenispph . "','" . $pphpersen . "','" . $carabayar . "','" . $jenispenghasilan . "')";
						$tab .= "<tr " . $brt . " title='kontrak sudah terpenuhi' class=rowcontent><td style=cursor:pointer>" . $rdata['nokontrak'] . "</td>";
						$tab .= "<td style=cursor:pointer>" . $rdata['koderekanan'] . "</td>";
						$tab .= "<td style=cursor:pointer>" . $optnmcust[$rdata['koderekanan']] . "</td>";
						$tab .= "<td align=right style=cursor:pointer>" . number_format($nilaiKontrak) . "</td></tr>";
					}
				}

				break;

			case 'PM':

				$tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
				$tab .= "<thead><tr>";
				$tab .= "<td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['kodecustomer'] . "</td>";
				$tab .= "<td align=center>" . $_SESSION['lang']['namacust'] . "</td>";
				$tab .= "</tr></thead><tbody>";

				if ($param['custId'] != '') {
					$whr .= " and koderekanan='" . $param['custId'] . "'";
				}
				if ($param['txtfind'] != '') {
					$whr .= " and (nokontrak like '%" . $param['txtfind'] . "%' or nokontrakexternal like '%" . $param['txtfind'] . "%' )";
				}

				$sdata = " select * from " . $dbname . ".pmn_kontrakjual where 1=1 " . $whr . " and termbayar='" . $param['jenisdok'] . "' and close=0";
				$sdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
				$sdata->setFetchMode(PDO::FETCH_ASSOC);
				while ($rdata =  $sdata->fetch()) {

					if ($rdata['kodebarang'] == '40000003') {
						if ($rdata['nokontrakexternal'] != '') {
							$rdata['nokontrak'] = $rdata['nokontrakexternal'];
						}
					}

					$nilaiQty = $rdata['kuantitaskontrak'];
					$nilaiSatuan = $rdata['hargasatuan'];
					$nilaiKontrak = $rdata['hargasatuan'] * $rdata['kuantitaskontrak'];

					#cek apakah sudah ada termin bayar atau belum
					#buat if, jika sudah ada maka pake sisa termin
					#sisa termin

					if ($rdata['ppn'] == 1 and $rdata['berikat'] == '0') {
						$nilaiKontrak = $nilaiKontrak / $persentasesatu;
						$ppnnya = $nilaiKontrak * $persentasedua;
					} else {
						if ($rdata['ppn'] == 1 and $rdata['berikat'] == '1') {
							$nilaiKontrak = $nilaiKontrak / $persentasesatu;
						}
						$ppnnya = 0;
					}

					if ($rdata['ppn'] == 0 and $rdata['berikat'] == '0') {
						$ppnnya = floor($nilaiKontrak * $persentasedua);
					}

					#termin pertama
					$iCek = $owlPDO->query(" select sum(beratkirim) as kirimdt from " . $dbname . ".keu_penagihandt where nokontrak='" . $rdata['nokontrak'] . "' ");
					$iCek->setFetchMode(PDO::FETCH_ASSOC);
					$dCek =  $iCek->fetch();
					$kirimdt = $dCek['kirimdt'];
					$jumlahkirim = $nilaiQty - $kirimdt;

					// $whrorg="induk='".$rdata['kodept']."' and tipe='HOLDING'";
					$whrorg = "induk='" . $rdata['kodept'] . "' and tipe='KANWIL'";
					$optKdHo = makeOption($dbname, 'organisasi', 'induk,kodeorganisasi', $whrorg);
					$whrCus = "kodecustomer='" . $rdata['koderekanan'] . "'";
					$optnmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer', $whrCus);

					#ambil jenis pph, nilai pph, cara bayar
					$strkd = " select jenispph,pphpersen,carabayar,jenispenghasilan,npwp from " . $dbname . ".pmn_4customer where kodecustomer='" . $rdata['koderekanan'] . "'";
					$reskd = $owlPDO->query($strkd) or die(print " Gagal: " . PDOException::getMessage());
					$reskd->setFetchMode(PDO::FETCH_ASSOC);
					$barkd = $reskd->fetch();
					$jenispph = $barkd['jenispph'];
					$pphpersen = $barkd['pphpersen'];
					$carabayar = $barkd['carabayar'];
					$jenispenghasilan = $barkd['jenispenghasilan'];
					$npwp = $barkd['npwp'];

					// if ($jumlahkirim>0) {
					$brt = "style=cursor:pointer; onclick=detaildata('" . $rdata['nokontrak'] . "','" . $rdata['koderekanan'] . "','" . $optKdHo[$rdata['kodept']] . "','" . $rdata['matauang'] . "','" . $rdata['rekening'] . "','" . number_format($ppnnya, 0) . "','" . $npwp . "','" . $rdata['berikat'] . "','" . $rdata['kodebarang'] . "','" . $jenispph . "','" . $pphpersen . "','" . $carabayar . "','" . $jenispenghasilan . "')";
					$tab .= "<tr " . $brt . " title='Klik untuk melihat detail kontrak per tanggal.' class=rowcontent><td style=cursor:pointer>" . $rdata['nokontrak'] . "</td>";
					$tab .= "<td style=cursor:pointer>" . $rdata['koderekanan'] . "</td>";
					$tab .= "<td style=cursor:pointer>" . @$optnmcust[$rdata['koderekanan']] . "</td></tr>";
					// }


				}
				break;

			default:

				break;
		}

		$tab .= "</tbody></table>";
		echo $tab;
		break;

	case 'detaildata':
		$tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab .= "<thead>";
		$tab .= "<tr><td colspan=6><button class=mybutton onclick=adddetail('" . $param['nokontrak'] . "','" . $param['kodebarang'] . "','" . $param['kdcust'] . "')>" . $_SESSION['lang']['addtodetail'] . "</button></td></tr><tr>";
		$tab .= "<td align=center>" . $_SESSION['lang']['nourut'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['kirim'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</td>";
		$tab .= "<td align=center><input type='checkbox' id='btnall' onclick='checkAll()'></td>";
		$tab .= "</tr></thead><tbody>";

		$no = 0;
		$sKontrak = "select nokontrak,nokontrakexternal from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $param['nokontrak'] . "' or nokontrakexternal='" . $param['nokontrak'] . "'";
		$rKontrak = fetchData($sKontrak);


		if ($param['kodebarang'] == '40000003') {
			$whre = " and notransaksi like '%" . $rKontrak[0]['nokontrak'] . "%'";
			if ($rKontrak[0]['nokontrakexternal'] != '') {
				$whre = " and (notransaksi like '%" . $rKontrak[0]['nokontrak'] . "%' or notransaksi like '%" . $rKontrak[0]['nokontrakexternal'] . "%')";
			}
		} else {
			$whre = " and notransaksi like '%" . $rKontrak[0]['nokontrak'] . "%'";
			if ($rKontrak[0]['nokontrakexternal'] != '') {
				$whre = " and (notransaksi like '%" . $rKontrak[0]['nokontrak'] . "%' or notransaksi like '%" . $rKontrak[0]['nokontrakexternal'] . "%')";
			}
		}

		$sdata = " select * from " . $dbname . ".keu_pengakuanjual where 1=1 " . $whre . " and tanggalpengakuan<='" . tanggalsystemn($param['tanggal']) . "' order by tanggalpengakuan";
		$sdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
		$sdata->setFetchMode(PDO::FETCH_ASSOC);
		while ($rdata =  $sdata->fetch()) {
			if ($rdata['tanggalpengakuan'] < "2018-03-01") {
				continue;
			}

			#explode data untuk mendapatkan nokontrak
			$expl = explode("##", $rdata['notransaksi']);

			$scek = "select tanggalkirim from " . $dbname . ".keu_penagihandt where 
			tanggalkirim='" . $rdata['tanggalpengakuan'] . "' and (nokontrak='" . $param['nokontrak'] . "' or nokontrak='" . $expl[1] . "')";
			$rcek = fetchData($scek);
			if (count($rcek) > 0) {
				continue;
			}

			$no++;
			$brt = "style=cursor:pointer";
			$tab .= "<tr " . $brt . " title='Klik untuk melihat detail kontrak per tanggal.' class=rowcontent>";
			$tab .= "<td style=cursor:pointer>" . $no . "</td>";
			$tab .= "<td style=cursor:pointer>" . $param['nokontrak'] . "</td>";
			$tab .= "<td style=cursor:pointer id='tglpengakuan_" . $no . "'>" . tanggalnormal($rdata['tanggalpengakuan']) . "</td>";
			$tab .= "<td style=cursor:pointer align=right id='kgkirim_" . $no . "'>" . number_format($rdata['kgkirim']) . "</td>";
			$tab .= "<td style=cursor:pointer align=right id='totrp_" . $no . "'>" . number_format($rdata['totalrupiah']) . "</td>";
			$tab .= "<td style=cursor:pointer><input type='checkbox' id='no_" . $no . "'></td></tr>";
			$hKuantitas += $rdata['kgkirim'];
			$nilInvoice += $rdata['totalrupiah'];
		}
		$tab .= "<input type=hidden id=totrow value=" . $no . "></tbody></table>";
		echo $tab;
		break;

	case 'adddetail':
		if (tanggalsystemn($param['tanggal']) < '2022-04-01') {
			$persentasesatu = '1.1';
			$persentasedua = '0.1';
		} else {
			$persentasesatu = '1.11';
			$persentasedua = '0.11';
		}
		$nomorinvoice = generateNoInvoice($param['nokontrak'], $param['tanggal'], $param['kdcust']);

		$sinser = "insert into " . $dbname . ".keu_penagihanht (noinvoice,tanggal,nokontrak,kodecustomer) values 
            ('" . $nomorinvoice . "','" . tanggalsystem($param['tanggal']) . "','" . $param['nokontrak'] . "','" . $param['kdcust'] . "')";
		// exit('warning: masukk'.$sinser);
		try {
			$owlPDO->exec($sinser);
		} catch (PDOException $e) {
			print " error: insert\n " . $e->getMessage() . "<br/>";
			die();
		}

		$nilaiKontrak = 0;
		$sDet = "insert into " . $dbname . ".keu_penagihandt values ";
		for ($arDt = 0; $arDt < $_POST['totrow']; $arDt++) {

			$_POST['kgkirim'][$arDt] = str_replace(',', '', $_POST['kgkirim'][$arDt]);
			$_POST['totrp'][$arDt] = str_replace(',', '', $_POST['totrp'][$arDt]);
			$nilaiKontrak += $_POST['totrp'][$arDt];

			if ($arDt == 0) {
				$sDet .= " ('" . $nomorinvoice . "','" . $param['nokontrak'] . "','','" . $_POST['kgkirim'][$arDt] . "','" . tanggalsystem($_POST['tglpengakuan'][$arDt]) . "','" . $_POST['totrp'][$arDt] . "')";
			} else {
				$sDet .= ",('" . $nomorinvoice . "','" . $param['nokontrak'] . "','','" . $_POST['kgkirim'][$arDt] . "','" . tanggalsystem($_POST['tglpengakuan'][$arDt]) . "','" . $_POST['totrp'][$arDt] . "')";
			}
		}
		// exit('warning: masukk'.$sDet);
		try {
			$owlPDO->exec($sDet);
		} catch (PDOException $e) {
			echo " Gagal " . addslashes($e->getMessage() . "__" . $sDet);
		}

		$sdata = " select ppn,berikat from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $param['nokontrak'] . "' or nokontrakexternal='" . $param['nokontrak'] . "'";
		$sdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
		$sdata->setFetchMode(PDO::FETCH_ASSOC);
		$rdata =  $sdata->fetch();

		if ($rdata['ppn'] == 1 and $rdata['berikat'] == '0') {
			$nilaiKontrak = $nilaiKontrak / $persentasesatu;
			$ppnnya = floor($nilaiKontrak * $persentasedua);
		} else {
			if ($rdata['ppn'] == 1 and $rdata['berikat'] == '1') {
				$nilaiKontrak = $nilaiKontrak / $persentasesatu;
			}
			$ppnnya = 0;
		}

		if ($rdata['ppn'] == 0 and $rdata['berikat'] == '0') {
			$ppnnya = floor($nilaiKontrak * $persentasedua);
		}

		echo $nomorinvoice . "##" . $ppnnya;

		break;

	case 'inputNoAfiliasi':
		$sUpdate = "update " . $dbname . ".keu_penagihanht set noinvoice_afiliasi='" . $param['noafiliasi'] . "' where noinvoice='" . $param['noinvoice'] . "'";
		try {
			$owlPDO->exec($sUpdate);
		} catch (PDOException $e) {
			print " Gagal err 1 update !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;

	case 'delData':
		#= del ht
		$sdel = "delete from " . $dbname . ".keu_penagihanht where noinvoice='" . $param['noinvoice'] . "'";
		try {
			$owlPDO->exec($sdel);
		} catch (PDOException $e) {
			print " Gagal err 2 delete!: " . $e->getMessage() . "<br/>";
			die();
		}

		#= del dt
		$sdel = "delete from " . $dbname . ".keu_penagihandt where noinvoice='" . $param['noinvoice'] . "'";
		try {
			$owlPDO->exec($sdel);
		} catch (PDOException $e) {
			print " Gagal err 2 delete!: " . $e->getMessage() . "<br/>";
			die();
		}

		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['noinvoice'] . "'"; //exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$pathx = $path . $bar['namafile'];
			unlink($pathx);
		}

		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['noinvoice'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'updatefaktur':
		//update faktur pajak
		// if ($param['nofakturpajak'] == '') {
		// 	exit('Error : Tidak ada faktur yang di gunakan untuk transaksi ' . $param['noinvoice']);
		// }
		$dt = "update " . $dbname . ".keu_fakturpajakdt set notransaksi='' where faktur='" . $param['nofakturpajak'] . "'";
		try {
			$owlPDO->exec($dt);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'getdate30':
		$tgl30 = date('d-m-Y', strtotime('+30 days', strtotime($param['tanggal'])));
		echo $tgl30 . "####" . substr(tanggalsystemn($param['tanggal']), 0, 7);
		break;

	case 'postingData':

		// if($rdata['jenis']=='PM'){
		//     $supd="update ".$dbname.".keu_penagihanht set posting=1,jurnalstatus=0 where noinvoice='".$rdata['noinvoice']."'";
		//     try{$test=$owlPDO->exec($supd); }catch (PDOException $e) {print "Flag update  Error: " . $e->getMessage() . "<br/>";}

		//     continue;
		// }
		$sdata = $owlPDO->query("select * from " . $dbname . ".keu_penagihanht where noinvoice='" . $param['noinvoice'] . "'");
		$sdata->setFetchMode(PDO::FETCH_ASSOC);
		$roc = owlBaris($sdata);
		$rdata = $sdata->fetch();

		if ($rdata['tanggal'] < '2022-04-01') {
			$persentasesatu = '1.1';
			$persentasedua = '0.1';
		} else {
			$persentasesatu = '1.11';
			$persentasedua = '0.11';
		}
		#=== Cek if posted ===
		$error0 = "";
		if ($rdata['posting'] == 1) {
			$error0 .= $_SESSION['lang']['errisposted'];
		}
		if ($error0 != '') {
			echo "Data Error :\n" . $error0;
			exit;
		}
		#=== Cek if data not exist ===
		$error1 = "";
		if ($roc == 0) {
			$error1 .= $_SESSION['lang']['errheadernotexist'] . "\n";
		}
		if ($error1 != '') {
			echo "Data Error :\n" . $error1;
			exit;
		}

		$tgl = str_replace("-", "", $rdata['tanggal']);
		$sPeriode = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $rdata['kodeorg'] . "' and tutupbuku=0 order by periode desc";
		$rPeriode = fetchdata($sPeriode);
		$tglakutansi = str_replace("-", "", $rPeriode[0]['tanggalmulai']);
		if ($tglakutansi > $tgl) {
			exit('Warning:Gagal posting, Tanggal dibawah periode aktif unit ' . $rdata['kodeorg'] . ', tanggal invoice ' . tanggalnormal($rdata['tanggal']) . ', periode aktif : ' . $rPeriode[0]['periode'] . ' (' . tanggalnormal($rPeriode[0]['tanggalmulai']) . ' s/d ' . tanggalnormal($rPeriode[0]['tanggalsampai']) . ') ');
		}

		#= prepare jurnal
		/*
        - Uang Muka : 
        D. : 110 AR (Piutang) 
        K. : 10 PPn Keluaran (berdasarkan no faktur pajak)
        K. : 100 Uang Muka Penjualan
        - Disposal : 
        D. : 1130101 AR (Piutang) 
        K. : 2130601 PPn Keluaran
        K. : 9190201 Gaint lost
        - Others : 
        D. : 1130101 AR (Piutang) 
        K. : 2130601 PPn Keluaran
        K. :9120201 Other Income
		- Biaya Investasi :
		D. : 1130101 AR (Piutang)
		K. : 2130601 PPn Keluaran
		K. : 9120201 Other Income
        - Pemenuhan Kontrak : 
        D. : 1130101 AR (Piutang) 
        K. : 2130601 PPn Keluaran
        K. : Penjualan
        - Pengiriman = Tidak ada jurnal
		
		#= tambahan jurnal claim
		D:
		K:5115101
		
		#= BL
		
							Kontrak				Realisasi
		
		Kg	 				 1,000,000		 	1,000,500 	
		rp/kg exc				 7,000 		 
		exclude			 7,000,000,000 	 	7,003,500,000 
		rp/kg inc			  6,363.64		 
		include   	  6,363,636,363.64 	 6,366,818,181.82 


			
					
		invoice DP 
		90%		 6,300,000,000 	 6,300,000,000 
		ppn		 630,000,000 	 630,000,000 

		
		#=======Jika tidak berikat=============
		#=exclude
		D	AR	 				6,930,000,000 	
		K	Uang Muka			6,300,000,000 
		K	Ppn		 			  630,000,000 
		#=include
		D	AR	 				6,300,000,000 
		K	Uang Muka			5,727,272,727
		K	Ppn					 5,727,272,72
		
		#=exclude		
		D	Uang Muka			6,300,000,000 	
		D	AR	 				  770,000,000 	
		K	Sales		 		7,000,000,000 
		K	Ppn		 			   70,000,000
		#=include
		D	Uang Muka			5,727,272,727  	
		D	AR	 				  703,500,000  
		K	Sales		 		6,366,818,182
		K	Ppn		 			   63,954,545.5


		#===Jika  berikat
		#=exclude
		D	AR	 				6,300,000,000 	
		K	Uang Muka			6,300,000,000 
		#=include
		D	AR	 				5,727,272,727
		K	Uang Muka			5,727,272,727
		
		#=exclude		
		D	Uang Muka			6,300,000,000 	
		D	AR	 				  700,000,000 	
		K	Sales		 		7,000,000,000 
		#=include
		D	Uang Muka			5,727,272,727 	
		D	AR	 				  639,545,455 	
		K	Sales		 		6,366,818,182

        */
		// exit("Error:".$rdata['jenis']);
		$dtpphtbs = 0;
		$jumlahRupiah = 0;
		// switch ($rdata['jenis']) {
		// case 'BA':
		// #= cek sudah ada brp invoice
		// $str =" select count(*) as jumlahinv,sum(nilaiinvoice) as jumnlahnilai, sum(kuantitas) as jumlahkuantitas 
		// from ".$dbname.".keu_penagihanht where nokontrak='".$rdata['nokontrak']."' 
		// and tanggal<'".$rdata['tanggal']."'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
		// $bnykInv=intval($bar['jumlahinv']);
		// $jumlahRupiah=floatval($bar['jumnlahnilai']);

		// $kodeaplikasi='PJ';
		// $kodejurnal='PJINV';
		// $ket1='Piutang atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// $ket2='Ppn atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// $ket3='Penjualan atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';	

		// break;
		// case 'UM':
		// $kodeaplikasi='PJ';
		// $kodejurnal='PJINV';
		// $ket1='Piutang atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// $ket2='Ppn atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// $ket3='Penjualan atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// break;
		// case 'DS':
		// $kodeaplikasi='DS';
		// $kodejurnal='PNDS';
		// $ket1='Piutang atas penagihan : '.$rdata['noinvoice'].'; No.Disposal Asset : '.$rdata['nokontrak'].';';
		// $ket2='Ppn atas penagihan : '.$rdata['noinvoice'].'; No.Disposal Asset : '.$rdata['nokontrak'].';';
		// $ket3='Penjualan atas penagihan : '.$rdata['noinvoice'].'; No.Disposal Asset : '.$rdata['nokontrak'].';';
		// break;
		// case 'OT':
		// $kodeaplikasi='OT';
		// $kodejurnal='PNOT';
		// $ket1='Piutang atas penagihan : '.$rdata['noinvoice'].'; No.dokumen/Kontrak : '.$rdata['nokontrak'].';';
		// $ket2='Ppn atas penagihan : '.$rdata['noinvoice'].'; No.dokumen/Kontrak : '.$rdata['nokontrak'].';';
		// $ket3='Penjualan atas penagihan : '.$rdata['noinvoice'].'; No.dokumen/Kontrak : '.$rdata['nokontrak'].';';
		// break;
		// case'Termin':
		// $kodeaplikasi='PJ';
		// $kodejurnal='PJINV';
		// $ket1='Piutang atas penagihan : '.$rdata['noinvoice'].'; ';
		// $ket2='Ppn atas penagihan : '.$rdata['noinvoice'].'; ';
		// $ket3='Penjualan atas penagihan : '.$rdata['noinvoice'].'; ';
		// break;
		// case 'PK':
		// $skontrak=$owlPDO->query("select kodebarang from ".$dbname.".pmn_kontrakjual where nokontrak='".$rdata['nokontrak']."'");
		// $skontrak->setFetchMode(PDO::FETCH_ASSOC);
		// $rkontrak=$skontrak->fetch();

		// if ($rkontrak['kodebarang']=='40000001') {//CPO
		// $kodeaplikasi='SCPO';
		// }
		// if ($rkontrak['kodebarang']=='40000002') {//Kernel
		// $kodeaplikasi='SKER';
		// }
		// if ($rkontrak['kodebarang']=='40000003') {//TBS
		// $kodeaplikasi='STBS';

		// #bentuk nilai pph
		// //$dtpphtbs=0.25/100*$rdata['nilaiinvoice'];
		// $dtpphtbs=0;
		// if($rdata['pphrupiah']!=0){
		// $dtpphtbs=$rdata['pphrupiah'];
		// }
		// $ketpph='PPH atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// }
		// if ($rkontrak['kodebarang']=='40000005') {//Cangkang
		// $kodeaplikasi='SCKG';
		// }

		// $kodejurnal='SLE';
		// $ket1='Piutang atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// $ket2='Ppn atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// $ket3='Penjualan atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// break;
		// case 'PM':
		// $skontrak=$owlPDO->query("select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$rdata['nokontrak']."' or nokontrakexternal='".$rdata['nokontrak']."'");
		// $skontrak->setFetchMode(PDO::FETCH_ASSOC);
		// $rkontrak=$skontrak->fetch();

		// $ket2='Ppn atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// if($rkontrak['berikat']=="1"){
		// $ket2="";
		// }
		// if ($rkontrak['kodebarang']=='40000001') {//CPO
		// $kodeaplikasi='SCPO';
		// }
		// if ($rkontrak['kodebarang']=='40000002') {//Kernel
		// $kodeaplikasi='SKER';
		// }
		// if ($rkontrak['kodebarang']=='40000003') {//TBS
		// $kodeaplikasi='STBS';
		// #bentuk nilai pph $dtpphtbs=0.25/100*$rdata['nilaiinvoice'];
		// $dtpphtbs=0;
		// if($rdata['pphrupiah']!=0){
		// $dtpphtbs=$rdata['pphrupiah'];
		// }
		// $ketpph='PPH atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// }
		// if ($rkontrak['kodebarang']=='40000005') {//Cangkang
		// $kodeaplikasi='SCKG';
		// }
		// $ket3="";
		// $kodejurnal='SLE';
		// $ket1='Piutang atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
		// if($ket2==""){
		// $ket1="";
		// }
		// break;
		// }

		# ========================= #
		# PPH
		# ========================= #
		if ($rdata['kodebarang'] == '40000003') { //TBS
			// $kodeaplikasi='STBS';
			#bentuk nilai pph $dtpphtbs=0.25/100*$rdata['nilaiinvoice'];
			$dtpphtbs = 0;
			if ($rdata['nilaipph'] != 0) {
				$dtpphtbs = $rdata['nilaipph'];
			}
			$ketpph = 'PPH atas penagihan : ' . $rdata['noinvoice'] . '; No.Kontrak : ' . $rdata['nokontrak'] . ';';
		}
		# ========================= #
		# END PPH
		# ========================= #

		# ========================= #
		# PARAMETER APPL
		# ========================= #
		$sql = selectQuery($dbname, "setup_parameterappl", "*", "kodeaplikasi='PG' AND kodeparameter='PGPPN'");
		$resppnkas = fetchData($sql, "OBJECT")[0];
		if (count($resppnkas) <= 0 or $resppnkas->nilai == "") {
			$param['ppnkas'] = 0;
		}
		$param['ppnkas'] = $resppnkas->nilai;

		# ========================= #
		# END PARAMETER APPL
		# ========================= #

		if ($param['ppnkas'] == 0) {
			$nilaitotal = $rdata['nilaiinvoice'] + $rdata['nilaippn'];
		} else {
			$nilaitotal = $rdata['nilaiinvoice'];
		}

		$totalpinalti = 0;
		$rupiahpinalti1 = $rdata['rupiah1'];
		$rupiahpinalti2 = $rdata['rupiah2'];
		$rupiahpinalti3 = $rdata['rupiah3'];
		$rupiahpinalti4 = $rdata['rupiah4'];
		$rupiahpinalti5 = $rdata['rupiah5'];
		$rupiahpinalti6 = $rdata['rupiah6'];
		$rupiahpinalti7 = $rdata['rupiah7'];
		$rupiahpinalti8 = $rdata['rupiah8'];

		$totalpinalti = $rupiahpinalti1 + $rupiahpinalti2 + $rupiahpinalti3 + $rupiahpinalti4 + $rupiahpinalti5 + $rupiahpinalti6 + $rupiahpinalti7 - $rupiahpinalti8;

		#= prepare noakunnya kodeaplikasi   jurnalid
		// $str="SELECT noakundebet,noakunkredit from ".$dbname.".keu_$arameterjurnal where kodeaplikasi='".$kodeaplikasi."' and jurnalid='".$kodejurnal."'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
		// $noakundebet=$bar['noakundebet'];
		// $noakunkredit=$bar['noakunkredit'];

		#= prepare akun ppn
		// $kodeaplikasippn='SPPN';
		// $kodejurnalppn='SLE';
		// $str="SELECT noakunkredit from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='".$kodeaplikasippn."' and jurnalid='".$kodejurnalppn."'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
		// $noakunppn=$bar['noakunkredit'];


		#=============================================================================================
		#= batas baru

		// exit("Error:".$noakundebet."-".$noakunkredit._.$noakunppn);

		$kodejurnal = 'PJINV';

		# query ambil jurnal 
		$strj =  selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodeorg='" . $rdata['kodept'] . "' and kodeunit='" . $rdata['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "'");
		$resj = fetchData($strj);
		$counterj = addZero($resj[0]['nokounter'] + 1, 3);

		# Transform No Jurnal dari No Transaksi
		$nojurnal = str_replace("-", "", $rdata['tanggal']) . "/" . $rdata['kodeorg'] . "/" . $kodejurnal . "/" . $counterj;


		$ket1 = 'Piutang atas penagihan : ' . $rdata['noinvoice'] . '; No.dokumen/Kontrak : ' . $rdata['nokontrak'] . ';';
		$ket2 = 'Ppn atas penagihan : ' . $rdata['noinvoice'] . '; No.dokumen/Kontrak : ' . $rdata['nokontrak'] . ';';
		$ket3 = 'Penjualan atas penagihan : ' . $rdata['noinvoice'] . '; No.dokumen/Kontrak : ' . $rdata['nokontrak'] . ';';


		// $str="select * from ".$dbname.".keu_5jenispenagihandt where (kodejenis='CIPP' or kodejenis='CITBS')  and kodebarang='".$rdata['kodebarang']."' ";
		$str = "select * from " . $dbname . ".keu_5jenispenagihandt where kodejenis='" . $rdata['tipeinvoice'] . "'  and kodebarang='" . $rdata['kodebarang'] . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$noakundebet = $bar['noakunpiutang'];
			if ($rdata['jenisinvoice'] == 'UM') {
				$noakunkredit = $bar['noakunuangmuka'];
			}
			if ($rdata['jenisinvoice'] == 'PL') {
				$noakunkredit = $bar['noakunsales'];
			}

			$noakunppn = $bar['noakunppn'];
		}

		# Get Data KUD
		$sqlkud = selectQuery($dbname, "kebun_5namakud", "*", "kodesupplier='" . $rdata['kodesupplier'] . "'");
		$reskud = fetchData($sqlkud, "OBJECT")[0];

		if ($rdata['tipeinvoice'] == 'OTPI') {
			$noakunkredit = $reskud->noakuninvestasi;
		}

		# Get Data Intra/Interco
		$sqlrkp = selectQuery($dbname, "keu_5caco", "*", "jenis='intra' AND kodeorg='" . $reskud->afdeling . "'");
		$resrkp = fetchData($sqlrkp, "OBJECT")[0];

		if ($rdata['tipeinvoice'] == 'OTPB') {
			$noakunkredit = $resrkp->akunpiutang;
		}

		// if ($_SESSION['standard']['userid'] == "0000000001") {
		// 	exit("Warning {$strxx}: " . $noakundebet . " || " . $noakunkredit . " || " . $noakunppn);
		// }
		if ($noakundebet == '' || $noakunkredit == '' || $noakunppn == '') {
			exit('warning : Noakun may not empty / Terdapat nomor akun yang kosong silahkan cek setup : Keuangan->Setup->Penagihan/Invoice AR');
		}
		if (($rdata['kodebarang'] == '40000033') and $rdata['jenisinvoice'] == 'PL') {
			$nilaitotal = $rdata['nilaiinvoice'] + $rdata['nilaippn'];
			$akunpiutang = empty(fetchData(selectQuery($dbname, "setup_parameterappl", "nilai", "kodeparameter= CONCAT('MF_COA/', '" . $rdata['kodecustomer'] . "')"))) ? exit("Warning : Akun piutang untuk customer " . $rdata['kodecustomer'] . " belum disetup di parameter aplikasi") : fetchData(selectQuery($dbname, "setup_parameterappl", "nilai", "kodeparameter= CONCAT('MF_COA/', '" . $rdata['kodecustomer'] . "')"))[0]['nilai'];
			$noakundebet = $akunpiutang;

			$dataRes['header'] = array();
			$dataRes['detail'] = array();

			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodejurnal,
				'tanggal' => $rdata['tanggal'],
				'tanggalentry' => date('Ymd'),
				'posting' => '0',
				'totaldebet' => $nilaitotal,
				'totalkredit' => $nilaitotal * -1,
				'amountkoreksi' => '0',
				'noreferensi' => $rdata['noinvoice'],
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);

			$noUrut = 1;
			#= debet piutang
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $rdata['tanggal'],
				'nourut' => $noUrut,
				'noakun' => $noakundebet,
				'keterangan' => $ket1,
				'jumlah' => $nilaitotal,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $rdata['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => ($rdata['kodecustomer'] == '' ? '' : $rdata['kodecustomer']),
				'kodesupplier' => ($rdata['kodecustomer'] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
				'noreferensi' => $rdata['noinvoice'],
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => $rdata['nokontrak'],
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => ''
			);
			$noUrut++;

			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $rdata['tanggal'],
				'nourut' => $noUrut,
				'noakun' => $noakunkredit,
				'keterangan' => $ket1,
				'jumlah' => $rdata['nilaiinvoice'] * -1,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $rdata['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => ($rdata['kodecustomer'] == '' ? '' : $rdata['kodecustomer']),
				'kodesupplier' => ($rdata['kodecustomer'] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
				'noreferensi' => $rdata['noinvoice'],
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => $rdata['nokontrak'],
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => ''
			);
			$noUrut++;
			if ($rdata['nilaippn'] != 0) {
				#= cr ppn
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $rdata['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakunppn,
					'keterangan' => $ket2,
					'jumlah' => $rdata['nilaippn'] * -1,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $rdata['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => ($rdata['kodecustomer'] == '' ? '' : $rdata['kodecustomer']),
					'kodesupplier' => ($rdata['kodecustomer'] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
					'noreferensi' => $rdata['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $rdata['nokontrak'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
				#= debet piutang
			}
		} else {

			$dataRes['header'] = array();
			$dataRes['detail'] = array();

			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodejurnal,
				'tanggal' => $rdata['tanggal'],
				'tanggalentry' => date('Ymd'),
				'posting' => '0',
				'totaldebet' => $nilaitotal,
				'totalkredit' => $nilaitotal * -1,
				'amountkoreksi' => '0',
				'noreferensi' => $rdata['noinvoice'],
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);
			// $nilaiPPh=0;
			if ($dtpphtbs != 0) {
				//$nilaitotal=$nilaitotal-$dtpphtbs;
				$nilaiPPh = $dtpphtbs;
			}

			$noUrut = 1;
			#= debet piutang
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $rdata['tanggal'],
				'nourut' => $noUrut,
				'noakun' => $noakundebet,
				'keterangan' => $ket1,
				'jumlah' => $nilaitotal,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $rdata['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => ($rdata['kodecustomer'] == '' ? '' : $rdata['kodecustomer']),
				'kodesupplier' => ($rdata['kodecustomer'] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
				'noreferensi' => $rdata['noinvoice'],
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => $rdata['nokontrak'],
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => ''
			);
			$noUrut++;
			if ($rdata['nilaippn'] != 0) {
				if ($param['ppnkas'] == 1) {
					#= db ppn
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $rdata['tanggal'],
						'nourut' => $noUrut,
						'noakun' => $noakundebet,
						'keterangan' => $ket2,
						'jumlah' => $rdata['nilaippn'] * 1,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $rdata['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => ($rdata['kodecustomer'] == '' ? '' : $rdata['kodecustomer']),
						'kodesupplier' => ($rdata['kodecustomer'] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
						'noreferensi' => $rdata['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => $rdata['nokontrak'],
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;
				}

				#= cr ppn
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $rdata['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakunppn,
					'keterangan' => $ket2,
					'jumlah' => $rdata['nilaippn'] * -1,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $rdata['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => ($rdata['kodecustomer'] == '' ? '' : $rdata['kodecustomer']),
					'kodesupplier' => ($rdata['kodecustomer'] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
					'noreferensi' => $rdata['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $rdata['nokontrak'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
				#= debet piutang
			}

			//1170101 (akun PPH 22)
			##jurnal pph tbs
			if ($nilaiPPh != 0) {
				//noakun PPH
				// $sappl = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='NAPPH'";
				// $rappl = fetchData($sappl);
				// $noparam = $rappl[0]['nilai'];
				// $noparam = explode(',', $noparam);
				$noakunpph = '1160103';
				$noaruspph = '10803';

				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $rdata['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakunpph,
					'keterangan' => $ketpph,
					'jumlah' => $dtpphtbs,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $rdata['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => ($rdata['kodecustomer'] == '' ? '' : $rdata['kodecustomer']),
					'kodesupplier' => ($rdata['kodecustomer'] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
					'noreferensi' => $rdata['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $rdata['nokontrak'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $rdata['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakundebet,
					'keterangan' => $ket1,
					'jumlah' => $nilaiPPh * (-1),
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $rdata['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => ($rdata['kodecustomer'] == '' ? '' : $rdata['kodecustomer']),
					'kodesupplier' => ($rdata['kodecustomer'] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
					'noreferensi' => $rdata['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $rdata['nokontrak'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}



			if ($ket3 != "") {
				#= cr penjualan
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $rdata['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakunkredit,
					'keterangan' => $ket3,
					'jumlah' => $rdata['nilaiinvoice'] * -1,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $rdata['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => ($rdata['kodecustomer'] == '' ? '' : $rdata['kodecustomer']),
					'kodesupplier' => ($rdata['kodecustomer'] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
					'noreferensi' => $rdata['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $rdata['nokontrak'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}
		}

		#====================================================invoice pelunasan BA ===============================================#

		#= update kasbank
		if ($rdata['jenisinvoice'] == 'UM' and $rdata['jenis'] == 'BA') {
			$strupdate = " update " . $dbname . ".keu_kasbankdt set keterangan1='" . $rdata['noinvoice'] . "' where 
					notransaksi='" . $rdata['notransaksikasbank'] . "' and
					noakun='1130101' and  tipetransaksi='M' and  nodok='" . $rdata['nokontrak'] . "' and keterangan1=''";
			try {
				$owlPDO->exec($strupdate);
			} catch (PDOException $e) {
				print " error: code 11258\n " . $e->getMessage() . "<br/>";
				die();
			}

			// exit("Error:".$strupdate);
		}

		// if($rdata['jenisinvoice']=='PL' and $rdata['jenis']=='BA'){

		$jurnalsales = 0;
		$str = "select count(*) as jurnalsales from " . $dbname . ".keu_jurnaldt_vw where nodok='" . $rdata['nokontrak'] . "' and kodejurnal='SLE' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jurnalsales = $bar['jurnalsales'];
		}

		// if($rdata['jenisinvoice']=='PL'){

		if ($rdata['jenisinvoice'] == 'PL' and $jurnalsales < 1 && $rdata['kodebarang'] != '40000033') {

			#= jurnal baru untuk BA pelunasan
			#= jurnal atas dihapus dahulu
			$dataRes['header'] = array();
			$dataRes['detail'] = array();

			$skontrak = $owlPDO->query("select kodebarang,hargasatuan,ppn from " . $dbname . ".pmn_kontrakjual 
					where nokontrak='" . $rdata['nokontrak'] . "'");
			$skontrak->setFetchMode(PDO::FETCH_ASSOC);
			$rkontrak = $skontrak->fetch();
			if ($rkontrak['ppn'] == 1) {
				$rkontrak['hargasatuan'] = $rkontrak['hargasatuan'] / 1.1;
			}

			// if ($rkontrak['kodebarang']=='40000001') {//CPO
			// $kodeaplikasi='SCPO';
			// }
			// if ($rkontrak['kodebarang']=='40000002') {//Kernel
			// $kodeaplikasi='SKER';
			// }
			// // if ($rkontrak['kodebarang']=='40000003') {//TBS
			// // $kodeaplikasi='STBS';

			// // #bentuk nilai pph
			// // //$dtpphtbs=0.25/100*$rdata['nilaiinvoice'];
			// // $dtpphtbs=0;
			// // if($rdata['pphrupiah']!=0){
			// // $dtpphtbs=$rdata['pphrupiah'];
			// // }
			// // $ketpph='PPH atas penagihan : '.$rdata['noinvoice'].'; No.Kontrak : '.$rdata['nokontrak'].';';
			// // }
			// if ($rkontrak['kodebarang']=='40000005') {//Cangkang
			// $kodeaplikasi='SCKG';
			// }

			// if ($rdata['kodebarang']=='40000003') {//TBS
			// $kodeaplikasi='STBS';
			// }


			// $kodejurnal='SLE';
			// $str="SELECT noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where  kodeaplikasi='".$kodeaplikasi."' and jurnalid='".$kodejurnal."'";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $bar=$res->fetch();
			// $noakundebet1=$bar['noakundebet'];
			// $noakunkredit=$bar['noakunkredit'];

			$kodeaplikasi = 'PJ';
			$kodejurnal = 'PJINV';

			#= ganti ke query uang muka
			// $str="SELECT noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='".$kodeaplikasi."' and jurnalid='".$kodejurnal."'";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $bar=$res->fetch();
			// $noakuadebet2=$bar['noakunkredit'];


			#= query baru untuk ambil data COA
			$str = "select * from " . $dbname . ".keu_5jenispenagihandt where (kodejenis='CIPP' or kodejenis='CITBS') and kodebarang='" . $rdata['kodebarang'] . "'";
			// echo $str;exit("Error");
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$noakundebet1 = $bar['noakunpiutang']; // akun piutang
				$noakunkredit = $bar['noakunsales']; // sales
				$noakuadebet2 = $bar['noakunuangmuka']; // uang muka
				$noakunppn = $bar['noakunppn']; // ppn
			}


			#= ambil data jumlah sales dari BA
			// $str =" select count(*) as jumlahbast, sum(kg) as jumlahkuantitas  from ".$dbname.".pmn_billofloading where nokontrak='".$rdata['nokontrak']."' and posting='1'";
			$str = " select count(*) as jumlahbast, sum(jumlah) as jumlahkuantitas  from " . $dbname . ".pmn_bast where nokontrak='" . $rdata['nokontrak'] . "' and posting='1'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$jumlahbast = $bar['jumlahbast'];
			$jumlahkgbl = $bar['jumlahkuantitas'];
			$nilaikontrak = ($rkontrak['hargasatuan'] * $jumlahkgbl);


			if ($rdata['jenis'] == 'BA') {
				#= BA => untuk cpo/pk, jika jenis ba harus sudah posting bast
				#= TBS => pakai termin
				$postingbast = 0;
				$str = " select posting  from " . $dbname . ".pmn_bast where nokontrak='" . $rdata['nokontrak'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$postingbast = $bar['posting'];
				}
				// if($postingbast=='0'){
				// 	exit("Warning:BAST belum diposting, silahkan posting BAST untuk kontrak ".$rdata['nokontrak']."  di menu : pemasaran->transaksi->berita acara serah terima");
				// }
			}


			#= trap apakah bast sudah diposting dan close kontrak

			// $nilaikontrak = nilai salesnya
			//

			if ($rdata['kodebarang'] == '40000003') { //TBS
				$nilaikontrak = $rdata['nilaiinvoice'];
			}

			// exit("Error:".$noakundebet1._.$noakuadebet2._.$noakunkredit._.$nilaikontrak);

			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodejurnal,
				'tanggal' => $rdata['tanggal'],
				'tanggalentry' => date('Ymd'),
				'posting' => '0',
				'totaldebet' => $nilaitotal,
				'totalkredit' => $nilaitotal * -1,
				'amountkoreksi' => '0',
				'noreferensi' => $rdata['noinvoice'],
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);

			$noUrut = 1;

			#= debet1 piutang
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $rdata['tanggal'],
				'nourut' => $noUrut,
				'noakun' => $noakundebet1,
				'keterangan' => $ket1,
				'jumlah' => $nilaitotal,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $rdata['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => ($optnmcust[$rdata['kodecustomer']] == '' ? '' : $rdata['kodecustomer']),
				'kodesupplier' => ($optnmcust[$rdata['kodecustomer']] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
				'noreferensi' => $rdata['noinvoice'],
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => $rdata['nokontrak'],
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => ''
			);
			$noUrut++;
			#= debet2 uang muka
			if ($jumlahRupiah > 0) {
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $rdata['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakuadebet2,
					'keterangan' => $ket1,
					'jumlah' => $jumlahRupiah,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $rdata['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => ($optnmcust[$rdata['kodecustomer']] == '' ? '' : $rdata['kodecustomer']),
					'kodesupplier' => ($optnmcust[$rdata['kodecustomer']] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
					'noreferensi' => $rdata['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $rdata['nokontrak'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}

			if ($param['ppnkas'] == 1) {
				if ($rdata['nilaippn'] != 0) {
					#= debet piutang
					#= db ppn
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $rdata['tanggal'],
						'nourut' => $noUrut,
						'noakun' => $noakundebet,
						'keterangan' => $ket2,
						'jumlah' => $rdata['nilaippn'] * 1,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $rdata['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => ($optnmcust[$rdata['kodecustomer']] == '' ? '' : $rdata['kodecustomer']),
						'kodesupplier' => ($optnmcust[$rdata['kodecustomer']] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
						'noreferensi' => $rdata['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => $rdata['nokontrak'],
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;
				}
			}

			##jurnal pph tbs
			if ($nilaiPPh != 0) {
				//noakun PPH
				// $sappl = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='NAPPH'";
				// $rappl = fetchData($sappl);
				// $noparam = $rappl[0]['nilai'];
				// $noparam = explode(',', $noparam);
				$noakunpph = '1160103';
				$noaruspph = '10803';

				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $rdata['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakunpph,
					'keterangan' => $ketpph,
					'jumlah' => $dtpphtbs,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $rdata['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => ($optnmcust[$rdata['kodecustomer']] == '' ? '' : $rdata['kodecustomer']),
					'kodesupplier' => ($optnmcust[$rdata['kodecustomer']] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
					'noreferensi' => $rdata['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $rdata['nokontrak'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}

			#= kredit sales
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $rdata['tanggal'],
				'nourut' => $noUrut,
				'noakun' => $noakunkredit,
				'keterangan' => $ket3,
				'jumlah' => $nilaitotal * -1,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $rdata['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => ($optnmcust[$rdata['kodecustomer']] == '' ? '' : $rdata['kodecustomer']),
				'kodesupplier' => ($optnmcust[$rdata['kodecustomer']] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
				'noreferensi' => $rdata['noinvoice'],
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => $rdata['nokontrak'],
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => ''
			);
			$noUrut++;


			if ($rdata['nilaippn'] != 0) {
				#= debet piutang

				#= cr ppn
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $rdata['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakunppn,
					'keterangan' => $ket2,
					'jumlah' => $rdata['nilaippn'] * -1,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $rdata['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => ($optnmcust[$rdata['kodecustomer']] == '' ? '' : $rdata['kodecustomer']),
					'kodesupplier' => ($optnmcust[$rdata['kodecustomer']] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
					'noreferensi' => $rdata['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $rdata['nokontrak'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}

			##jurnal pph tbs
			if ($nilaiPPh != 0) {
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $rdata['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakundebet,
					'keterangan' => $ket1,
					'jumlah' => $nilaiPPh * (-1),
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $rdata['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => ($optnmcust[$rdata['kodecustomer']] == '' ? '' : $rdata['kodecustomer']),
					'kodesupplier' => ($optnmcust[$rdata['kodecustomer']] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
					'noreferensi' => $rdata['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $rdata['nokontrak'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}
		}

		// invoice pelunasan	
		// D	Uang Muka 	x
		// D	AR			
		// K	Sales
		// K	Ppn 		x



		// echo "<pre>";
		// print_r($dataRes['header']);
		// print_r($dataRes['detail']);
		// echo "</pre>";
		// exit('warning');

		if ($ket1 != "") {
			$str = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				$errorDB .= "Header Error :" . $e->getMessage() . "\n";
			}


			foreach ($dataRes['detail'] as $key => $dataDet) {
				$str = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					$errorDB .= "Detail Error " . $key . " :" . $e->getMessage() . "\n";
				}
			}


			$queryJ = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $resj[0]['nokounter'] + 1),
				"kodeorg='" . $rdata['kodept'] . "' and kodeunit='" . $rdata['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "'"
			);
			$errCounter = "";
			try {
				$owlPDO->exec($queryJ);
			} catch (PDOException $e) {
				$errCounter .= "Update Counter Parameter Jurnal Error :" . $e->getMessage();
			}
		}



		if ($totalpinalti != 0 and $jurnalsales < 1) {

			$ppntotalpinalti = floor($persentasedua * $totalpinalti);
			$grandtotalpinalti = $totalpinalti + $ppntotalpinalti;
			$ketclaim = 'Claim penjualan penagihan : ' . $rdata['noinvoice'] . '; No.Kontrak : ' . $rdata['nokontrak'] . ';';
			$ketclaimppn = 'PPn Claim penjualan penagihan  : ' . $rdata['noinvoice'] . '; No.Kontrak : ' . $rdata['nokontrak'] . ';';

			#= bentuk jurnal claim
			$kodejurnal = 'SLE';
			$kodeaplikasi = 'CLAIM';
			$str = "SELECT noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where 
			kodeaplikasi='" . $kodeaplikasi . "' and jurnalid='" . $kodejurnal . "'";
			// exit("Error:$str");
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$noakundebet = $bar['noakundebet'];
			$noakunkredit = $bar['noakunkredit'];

			/*
			$kodeaplikasi='PJ';
			$str="SELECT noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='".$kodeaplikasi."' and jurnalid='".$kodejurnal."'";
			// exit("Error:$str");
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$noakuadebet2=$bar['noakunkredit'];
			*/

			# query ambil jurnal 
			$kodejurnal = 'PJINV';
			$strj =  selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodeorg='" . $rdata['kodept'] . "' and kodeunit='" . $rdata['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "'");
			$resj = fetchData($strj);
			$counterj = addZero($resj[0]['nokounter'] + 1, 3);

			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-", "", $rdata['tanggal']) . "/" . $rdata['kodeorg'] . "/" . $kodejurnal . "/" . $counterj;

			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodejurnal,
				'tanggal' => $rdata['tanggal'],
				'tanggalentry' => date('Ymd'),
				'posting' => '0',
				'totaldebet' => $grandtotalpinalti,
				'totalkredit' => $grandtotalpinalti * -1,
				'amountkoreksi' => '0',
				'noreferensi' => $rdata['noinvoice'],
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);

			$noUrut = 1;
			#= debet claim
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $rdata['tanggal'],
				'nourut' => $noUrut,
				'noakun' => $noakundebet,
				'keterangan' => $ketclaim,
				'jumlah' => $totalpinalti,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $rdata['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => ($optnmcust[$rdata['kodecustomer']] == '' ? '' : $rdata['kodecustomer']),
				'kodesupplier' => ($optnmcust[$rdata['kodecustomer']] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
				'noreferensi' => $rdata['noinvoice'],
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => $rdata['nokontrak'],
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => ''
			);

			$noUrut++;
			#= kredit piutang
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $rdata['tanggal'],
				'nourut' => $noUrut,
				'noakun' => $noakunkredit,
				'keterangan' => $ketclaim,
				'jumlah' => $grandtotalpinalti * -1,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $rdata['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => ($optnmcust[$rdata['kodecustomer']] == '' ? '' : $rdata['kodecustomer']),
				'kodesupplier' => ($optnmcust[$rdata['kodecustomer']] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
				'noreferensi' => $rdata['noinvoice'],
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => $rdata['nokontrak'],
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => ''
			);
			$noUrut++;


			if ($ppntotalpinalti != 0) {
				#= db ppn
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $rdata['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakunppn,
					'keterangan' => $ketclaimppn,
					'jumlah' => $ppntotalpinalti,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $rdata['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => ($optnmcust[$rdata['kodecustomer']] == '' ? '' : $rdata['kodecustomer']),
					'kodesupplier' => ($optnmcust[$rdata['kodecustomer']] == '' ? $rdata['kodesupplier'] : $rdata['kodecustomer']),
					'noreferensi' => $rdata['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $rdata['nokontrak'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}

			$str = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				$errorDB .= "Header Error :" . $e->getMessage() . "\n";
			}

	
			foreach ($dataRes['detail'] as $key => $dataDet) {
				$str = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					$errorDB .= "Detail Error " . $key . " :" . $e->getMessage() . "\n";
				}
			}


			$queryJ = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $resj[0]['nokounter'] + 1),
				"kodeorg='" . $rdata['kodept'] . "' and kodeunit='" . $rdata['kodeorg'] . "'  and kodekelompok='" . $kodejurnal . "'"
			);
			$errCounter = "";
			try {
				$owlPDO->exec($queryJ);
			} catch (PDOException $e) {
				$errCounter .= "Update Counter Parameter Jurnal Error :" . $e->getMessage();
			}
		}


		// echo "<pre>";
		// print_r($dataRes['header']);
		// print_r($dataRes['detail']);
		// echo "</pre>";
		// exit('warning:'.$jurnalsales);



		#= cek jurnal yang terbentuk
		if ($jurnalsales > 0) {
			$str = "select sum(debet) as debet,sum(kredit) as kredit,sum(jumlah) jumlahjurnal from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $rdata['noinvoice'] . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$jumlahjurnal = abs($bar['jumlahjurnal']);
				$keteranganjurnal = "Debet : " . number_format($bar['debet']) . "\nKredit : " . number_format($bar['kredit']) . "\nSelisih : " . number_format($bar['jumlahjurnal']) . "";
			}

			if ($jumlahjurnal > 1) {
				$delH = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal . "'");
				try {
					$owlPDO->exec($delH);
				} catch (PDOException $e) {
					print "Rollback 2 Error: " . $e->getMessage() . "<br/>";
					die();
				}
				exit("error:Jurnal Tidak Balance, silahkan lakukan pengecekan data kembali " . $keteranganjurnal . " ");
			}
		}

		if ($rdata['jenisinvoice'] == 'UM') {
			$msgdt = "Pemberitahuan bahwa Invoice uang muka penjualan (AR) sudah dibuat dengan nomor " . $rdata['noinvoice'] . " dengan nomor kontrak : " . $rdata['nokontrak'] . "";
			$str = "select * from " . $dbname . ".setup_notification_dt where kodejenis='KEUINVARUM'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				createnotif($rdata['noinvoice'], 'KEUINVARUM', $msgdt, $bar['karyawanid'], date('Y-m-d H:i:s'));
			}
		}

		$supd = "update " . $dbname . ".keu_penagihanht set posting=1,jurnalstatus=0 where noinvoice='" . $rdata['noinvoice'] . "'";
		try {
			$test = $owlPDO->exec($supd);
		} catch (PDOException $e) {
			print "Flag update  Error: " . $e->getMessage() . "<br/>";
		} //dont exit here
		if (!$test) {
			$delH = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal . "'");
			try {
				$owlPDO->exec($delH);
			} catch (PDOException $e) {
				print "Rollback 2 Error: " . $e->getMessage() . "<br/>";
				die();
			}
		}

		break;


	case 'submitfile':
		$param = $_POST;
		$tgl = date("YmdHis");
		$his = date("His");
		$nmTemp = str_replace('-', '', str_replace('/', '', $param['notransaksi']));
		/*echo"<pre>";
        print_r($_FILES['file']);
        echo"</pre>";
        exit('error');*/
		if ($param['fileupload'] != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$filename = $kriteriaefil . "_" . $nmTemp . "_" . $his . "" . $filetype;
				// exit("Error:".$filename);
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				// listfile_keu_kasbank
				// listfileupload
				if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.rar') || ($filetype == '.gz') || ($filetype == '.zip') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
					$str = "insert into " . $dbname . ".listfileupload values ('','" . $noinvoice . "','" . $filename . "','" . $filetype . "','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
					try {
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path . $filename, $file_tmpname);
					} catch (PDOException $e) {
						echo " Gagal," . addslashes($e->getMessage());
					}
				} else {
					exit("Warning : Format file upload tidak boleh " . $filetype);
				}
			}
		}
		break;

	case 'deletefile':
		$param = $_POST;
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['noinvoice'] . "' and namafile='" . $param['namafile'] . "'";
		// exit('error'.$str);
		try {
			$owlPDO->exec($str);
			$pathx = $path . str_replace('/', '', $param['namafile']);
			unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'loadfiles':
		$param = $_POST;
		$form = '';
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' ";
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
		$form = "<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<table class='sortable' cellspacing='1' border='0' width=100%>
			<thead>
			<tr class=rowheader>
				<td align='center'>" . $_SESSION['lang']['nourut'] . "</td>
				<td align='center'>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center'>Action</td>
			</tr>
			</thead>
			
		";
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $noinvoice . "' ";
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
			$form .= "<td><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a></td>";
			$form .= "<tr>";
		}
		$form .= "</table>
		</fieldset>";
		echo $form;
		break;













	case 'setpph':
		// $str="SELECT * from ".$dbname.".pmn_4customer where kodecustomer='".$param['kodecustomer']."'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
		//  $jpph=$bar['jenispph'];
		//  $pphpersen=$bar['pphpersen'];

		// $str="SELECT * from ".$dbname.".pmn_5pajak where id='".$jpph."'";
		// $res=fetchData($str);
		// $jenispph=$res[0]['jenispph'];
		// $carapembayaran=$res[0]['carapembayaran'];
		// $jenispenghasilan=$res[0]['jenispenghasilan'];

		$strkd = " select jenispph,pphpersen,carabayar,jenispenghasilan from " . $dbname . ".pmn_4customer where kodecustomer='" . $param['kodecustomer'] . "'";
		$reskd = $owlPDO->query($strkd) or die(print " Gagal: " . PDOException::getMessage());
		$reskd->setFetchMode(PDO::FETCH_ASSOC);
		$barkd = $reskd->fetch();
		$jenispph = $barkd['jenispph'];
		$pphpersen = $barkd['pphpersen'];
		$carapembayaran = $barkd['carabayar'];
		$jenispenghasilan = $barkd['jenispenghasilan'];

		echo $jenispph . "####" . $nmakun[$jenispph] . "####" . $jenispenghasilan . "####" . $jnsp[$jenispenghasilan] . "####" . $carapembayaran . "####" . $pphpersen;
		break;
}

function generateNoInvoice($nokontrak, $tgl, $cust, $jenis, $kodeorg)
{
	global $dbname;
	global $conn;
	global $owlPDO;
	#no invoice
	$tgldt = explode("-", $tgl);
	$bulan = $tgldt[1];
	$thn = date('Y');
	$sPt = $owlPDO->query("select kodept,koderekanan from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "' or nokontrakexternal='" . $nokontrak . "'");
	$sPt->setFetchMode(PDO::FETCH_ASSOC);
	$rPt = $sPt->fetch();
	$arrayRomawi = array("I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII");
	$resultRomawi = $arrayRomawi[(int)$bulan - 1];

	if ($jenis == 'DS') {
		$sPt = $owlPDO->query("select left(kodeasset,3) as kodept from " . $dbname . ".keu_disposalasset where notransaksi='" . $nokontrak . "'");
		$sPt->setFetchMode(PDO::FETCH_ASSOC);
		$rPt = $sPt->fetch();
		$rPt['koderekanan'] = $cust;
	}

	if ($jenis == 'OT' || $jenis == 'FEM') {
		$whr = "kodeorganisasi='" . $kodeorg . "'";
		$optinduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', $whr);
		$rPt['kodept'] = $optinduk[$kodeorg];
		$rPt['koderekanan'] = $cust;
	}

	$ql = $owlPDO->query("select `noinvoice` from " . $dbname . ".`keu_penagihanht` where kodept = '" . $rPt['kodept'] . "' 
                    and left(tanggal,4) = '" . $tgldt[2] . "' and jenis!='PJD' and left(noinvoice,2)!='AR' order by noinvoice desc limit 1");
	$ql->setFetchMode(PDO::FETCH_OBJ);
	$data = $ql->fetch();
	$countNoInvoice = substr($data->noinvoice, 0, 3);
	$noInvoice = addZero($countNoInvoice + 1, 3) . "/INV/" . $rPt['kodept'] . "-" . $rPt['koderekanan'] . "/" . $resultRomawi . "/" . $tgldt[2];
	return $noInvoice;
}
