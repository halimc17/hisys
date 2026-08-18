<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$rekeningbank = checkPostGet('rekeningbank', '');
$notransaksi  = checkPostGet('notransaksi', '');
$tipe         = checkPostGet('tipe', '');
$method       = checkPostGet('method', '');
$tanggal      = tanggalsystemn(checkPostGet('tanggal', ''));
$tanggal1     = tanggalsystemn(checkPostGet('tanggal1', ''));
$tanggal2     = tanggalsystemn(checkPostGet('tanggal2', ''));
$nokontrak    = checkPostGet('nokontrak', '');
$kodecustomer = checkPostGet('kodecustomer', '');
$kodebarang   = checkPostGet('kodebarang', '');
$kodept       = checkPostGet('kodept', '');
$kodeorg      = checkPostGet('kodeorg', '');
$noakun2a     = checkPostGet('noakun2a', '');
$noakun       = checkPostGet('noakun', '');
$bayarke      = checkPostGet('bayarke', '');
$tipetransaksi = checkPostGet('tipetransaksi', '');
$novoucher    = checkPostGet('novoucher', '');
$numRow       = checkPostGet('numRow', '');
$nocek        = checkPostGet('nocek', '');
$supplier     = checkPostGet('supplier', '');
$pembayaran   = checkPostGet('pembayaran', '');
$cgttu        = checkPostGet('cgttu', '');
$ceklist        = checkPostGet('list', '');
$kodeunit        = checkPostGet('kodeunit', '');
$rekening        = checkPostGet('rekening', '');
$rekeningext        = checkPostGet('rekeningext', '');

$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

// $param = $_POST;
//exit("error".$ceklist);

$optnotransaksi = $optctg = $optrekening = $optsupplier = $optbank = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
//$optnotransaksi=$optsupplier=$optbank=$optctg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$whereJam = " kasbank=1 and detail=1 and (pemilik='" . $_SESSION['empl']['tipelokasitugas'] . "' or pemilik='GLOBAL' or pemilik='" . $_SESSION['empl']['lokasitugas'] . "')";
if ($_SESSION['language'] == 'EN') {
	$optAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun1', $whereJam, null, true);
} else {
	$optAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', $whereJam, null, true);
}

$str = "SELECT * from " . $dbname . ".keu_5akunbank_vw";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$norekening[$bar['noakun']] = $bar['rekening'];
	$namabank[$bar['noakun']] = $bar['namabank'];
}

// rekening default
$str = "SELECT * from " . $dbname . ".log_5rekbank order by def";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$norekdef[$bar['supplierid']] = "Rek. " . $bar['rekening'] . ', an. ' . $bar['an'];
}

$str = "SELECT * from " . $dbname . ".log_5supplier where status=1";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	// $optsupplier.="<option value='".$bar['supplierid']."'>".$bar['supplierid']." - ".$bar['namasupplier']."</option>";
	if ($norekdef[$bar['supplierid']] != '') {
		$nomorek = "(" . $norekdef[$bar['supplierid']] . ")";
	} else {
		$nomorek = "";
	}

	$optsupplier .= "<option value='" . $bar['supplierid'] . "'>" . $bar['namasupplier'] . " " . $nomorek . "</option>";
}


switch ($method) {
	case 'savegantibukti':
		$str = "update " . $dbname . ".keu_kasbankht set cgttu='" . $cgttu . "',nocek='" . $nocek . "' where notransaksi='" . $notransaksi . "' ";

		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;

	case 'getrekening':
		$str = "select * from " . $dbname . ".log_5rekbank  where supplierid='" . $supplier . "' and isactive=1";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$selected = '';
			if ($bar['def'] == 1) {
				$selected = " selected";
			}
			$optrekening .= "<option value='" . $bar['rekening'] . "' " . $selected . ">" . $bar['bank'] . " " . $bar['rekening'] . " a/n " . $bar['an'] . "</option>";
		}
		echo $optrekening;
		break;

	case 'getdetailrekening':
		$str = "select a.*, b.namabank from " . $dbname . ".log_5rekbank a left join " . $dbname . ".keu_5daftarbank b on a.idbank = b.kodebank where a.supplierid='" . $supplier . "' and a.rekening='" . $rekeningext . "' order by a.def desc";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		echo $bar['namabank'] . "####" . $bar['an'];
		// exit("Error:A");
		break;

	case 'getketerangan':
		$str = "select a.*, b.namabank from " . $dbname . ".log_5rekbank a left join " . $dbname . ".keu_5daftarbank b on a.idbank = b.kodebank where a.supplierid='" . $supplier . "' order by a.def desc";
		// echo "error ".$str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		echo $bar['namabank'] . "####" . $bar['rekening'] . "####" . $bar['an'];
		// document.getElementById('namabank').innerHTML = data[0];
		// document.getElementById('rekeningext').innerHTML = data[1];
		// document.getElementById('anrekeningext').innerHTML = data[2];
		break;


	case 'addnotransaksi':
		$newdata = array(
			'notransaksi' => $notransaksi,
			'tipe' => '2',
		);

		if ($_SESSION['bgnotrans'] != array()) {
			foreach ($_SESSION['bgnotrans'] as $key => $row) {
				if ($row['notransaksi'] == $notransaksi) {
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['bgnotrans'], $newdata);
		} else {
			array_push($_SESSION['bgnotrans'], $newdata);
		}

		break;

	case 'listtransaksi':
		// echo"<pre>";
		// print_r($_SESSION['tempnotran']);
		// echo"</pre>";

		// // echo $xxx;
		// // exit("error");

		$tab = "";
		$total = 0;
		foreach ($_SESSION['bgnotrans'] as $key => $row) {
			$optrupiah = makeOption($dbname, 'keu_kasbankht', 'notransaksi,jumlah', "notransaksi='" . $row['notransaksi'] . "'");

			$no++;
			$tab .= "<tr class='rowcontent'>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td>" . $row['notransaksi'] . "</td>";
			$tab .= "<td align=right>" . number_format($optrupiah[$row['notransaksi']], 0) . "</td>";
			if ($row['tipe'] == '1') {
				$tab .= "<td style='text-align:center'></td>";

				$strn = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $row['notransaksi'] . "'";
				$resn = fetchdata($strn);
				$kodeorg = $resn[0]['kodeorg'];
				$tipetransaksi = $resn[0]['tipetransaksi'];
				$noakun = $resn[0]['noakun'];
			} else {
				$tab .= "<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletenotransaksi('" . $row['notransaksi'] . "')\" src='images/delete_32.png'/>
				</td>";
			}
			$tab .= "</tr>";

			@$total += $optrupiah[$row['notransaksi']];

			$notrans[$row['notransaksi']] = $row['notransaksi'];


			#= cek autokb
			$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $row['notransaksi'] . "' ";
			// echo $str;
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$autokb = $bar['autokb'];
			$tipetransaksi = $bar['tipetransaksi'];
		}

		$tab .= "<tr class='rowcontent'>";
		$tab .= "<td align=center></td>";
		$tab .= "<td>TOTAL</td>";
		$tab .= "<td align=right>" . number_format($total, 0) . "</td>";
		$tab .= "<td></td>";
		$tab .= "</tr>";


		if ($autokb == 0) {
			$str = "select * from " . $dbname . ".keu_kasbankht where kodeorg='" . $kodeorg . "' and posting=1 
			and pembayaran=0 and tipetransaksi='" . $tipetransaksi . "' and noakun='" . $noakun . "' 
			and notransaksi not in ('" . implode("','", $notrans) . "') and autokb=0";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optnotransaksi .= "<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $bar['keterangan'] . " [" . ($bar['bayarkepada'] == '' ? '-' : $bar['bayarkepada']) . " : " . number_format($bar['jumlah'], 0) . "]</option>";
				$arrnotransaksi[] = $bar['notransaksi'];
			}
		}


		echo $tab . "####" . $optnotransaksi;
		break;

	case 'deletenotransaksi':
		foreach ($_SESSION['bgnotrans'] as $key => $row) {
			if ($row['notransaksi'] == $notransaksi) {
				unset($_SESSION['bgnotrans'][$key]);
			}
		}
		break;

	case 'showformgantibukti':

		$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $notransaksi . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$autokb = $bar['autokb'];
		$kodeorg = $bar['kodeorg'];
		$rekening = $bar['rekening'];
		$noakun = $bar['noakun'];
		$cgttu = $bar['cgttu'];
		$nocek = $bar['nocek'];
		$tanggal = $bar['tanggal'];
		if ($rekening != '') {
			$str = "select * from " . $dbname . ".keu_5akunbank_vw where noakun='" . $rekening . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optbank = "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $bar['namabank'] . "</option>";
			}
		}

		$tab .= "<table cellpadding=3 cellspacing=1 border=0>";
		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext readonly  id=notransaksi value='" . $notransaksi . "' disabled style=\"width:150px;\"/>									
					</td></tr>";
		$tab .= "<tr  class=rowcontent>
					<td>" . $_SESSION['lang']['tanggalbayar'] . "</td> 
					<td>:</td>
					<td width=180px>
						<input type=text class=myinputtext readonly disabled value='" . tanggalnormal($tanggal) . "' id=tglbayar onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\"/>									
					</td>
				</tr>";
		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext readonly  id=kodeorg value='" . $kodeorg . "' disabled style=\"width:150px;\"/>									
				</td></tr>";


		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['rekening'] . "</td>
				<td>:</td>		
				<td>
					<select id=rekening  style=\"width:155px;\">'" . $optbank . "'</select>
				</td></tr>";

		$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='AK' and	kodeparameter='AKAKUNBANK'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$arrdata = explode(',', $bar['nilai']);
		foreach ($arrdata as $key) {
			$arrakunbank[] = $key;
		}
		$arrctg = getEnum($dbname, 'keu_kasbankht', 'cgttu');
		foreach ($arrctg as $kei => $fal) {
			if (!in_array($noakun, $arrakunbank)) {
				if ($fal == 'Cash') {
					$optctg .= "<option value='" . $kei . "'>" . $fal . "</option>";
				}
			} else {
				$optctg .= "<option value='" . $kei . "'>" . $fal . "</option>";
			}
		}



		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['cgttu'] . " " . $_SESSION['lang']['lama'] . "</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext readonly  id='' value='" . $cgttu . "' disabled style=\"width:150px;\"/>
				</td></tr>";
		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['BuktiPembayaran'] . " " . $_SESSION['lang']['lama'] . "</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext readonly  id='' value='" . $nocek . "' disabled style=\"width:150px;\"/>
			</td></tr>";

		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['cgttu'] . " " . $_SESSION['lang']['baru'] . "</td>
				<td>:</td>		
				<td>
					<select id=cgttu onchange=getbuktibayarkasir() style=\"width:155px;\">'" . $optctg . "'</select>
				</td></tr>";

		# ================================================== #
		# Permintaan Baru	
		# Jika Transfer, Pakai Inputan bukan Option
		# ================================================== #

		if ($fal == 'Transfer') {
			$disabledInput = 'disabled'; # Disable inputannya
		} else {
			$disabledOption = 'disabled'; # Disable optionnya
		}

		# Select
		$tab .= "<tr id=nocekopt class=rowcontent><td>" . $_SESSION['lang']['BuktiPembayaran'] . " " . $_SESSION['lang']['baru'] . "</td>
					<td>:</td>		
					<td>
						<select id=nocek style=\"width:155px;\" " . @$disabledOption . ">'" . $optbuktibayar . "'</select>
						<img id='nocek' onclick=z.elSearch('nocek',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;' " . @$disabledOption . ">
				</td></tr>";

		# Input
		$tab .= "
					<tr class=rowcontent>
						<td>" . $_SESSION['lang']['BuktiPembayaran'] . " <br/> Internet Banking / MCM</td>
						<td>:</td>		
						<td>
							<input class='myinputtext' id=nocekInput style=\"width:155px;\" " . @$disabledInput . " />
						</td>
					</tr>";

		# ================================================== #
		# END
		# ================================================== #

		$tab .= "<tr class=rowcontent>
				<td colspan=2></td>
				<td style='text-align:left'>
					<button class=mybutton id=tombolsavegantibukti onclick=savegantibukti()>Simpan</button>
				</td>
			</tr>";

		$tab .= "</table>";

		echo $tab;

		break;

	case 'showformbayar':

		#= pengecekan jika status pembayaran sudah 1 maka exit;



		$_SESSION['bgnotrans'] = array();


		$newdata = array(
			'notransaksi' => $notransaksi,
			'tipe' => '1',
		);

		array_push($_SESSION['bgnotrans'], $newdata);


		$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $notransaksi . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$autokb = $bar['autokb'];
		$tipetransaksi = $bar['tipetransaksi'];
		$rekening = $bar['rekening'];
		$pembayaran = $bar['pembayaran'];
		$cgttu = $bar['cgttu'];
		$tglrenc = $bar['tanggal'];


		#= pengecekan jika status pembayaran sudah 1 maka exit;	
		if ($pembayaran == '1') {
			exit("Warning:Transaksi kasir sudah dilakukan, silahkan klik List data untuk melakukan refresh transaksi");
		}

		if ($autokb == 0) {
			$str = "select * from " . $dbname . ".keu_kasbankht where
			kodeorg='" . $kodeorg . "' and posting=1 and pembayaran=0 and 
			tipetransaksi='" . $tipetransaksi . "' and noakun='" . $noakun . "' 
			and notransaksi!='" . $notransaksi . "' and autokb=0";
			// echo $str;
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optnotransaksi .= "<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $bar['keterangan'] . " [" . ($bar['bayarkepada'] == '' ? '-' : $bar['bayarkepada']) . " : " . number_format($bar['jumlah'], 0) . "]</option>";
				$arrnotransaksi[] = $bar['notransaksi'];
			}
		}


		#= cek jika nomor bukti bayar sama

		// <script>loaddata(0)</script>

		$tab .= "<fieldset style='width:95%'>";
		$tab .= "<div style=max-height:200px;overflow:auto;>";
		$tab .= "<table cellpadding=3 cellspacing=1 border=0 class=sortable style='width:100%'>";
		$tab .= "<thead><tr  class=rowheader>
				<td align=center>No</td> 
				<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td> 
				<td align=center>" . $_SESSION['lang']['jumlah'] . "</td> 
				<td align=center>" . $_SESSION['lang']['action'] . "</td> 
			</tr></thead>";


		$tab .= "<tr class=rowcontent>";
		$tab .= "<td></td>";
		$tab .= "<td colspan=1 nowrap>
					<select id=notransaksi style=\"width:155px;\">" . $optnotransaksi . "</select>
					<img onclick=\"z.elSearch('notransaksi',event)\" class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td><td align=center>	
					<img title='Refresh' class='zImgBtn' onclick=listtransaksi() src='images/refresh2.png'>
				</td>";
		$tab .= "<td align=center><img  title=Tambah class=zImgBtn onclick=addnotransaksi() src=images/plus.png></td>";
		$tab .= "</tr>";

		$tab .= "<tbody id='listnotransaksi'></tbody>";
		$tab .= "</table>";
		$tab .= "</div>";
		$tab .= "<hr>";
		// $tab.="</fieldset><hr>";



		$whereJam = " noakun='" . $noakun . "'";
		// $optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from " . $dbname . ".keu_5akun where " . $whereJam . " order by noakun asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($_SESSION['language'] == 'EN') {
				@$optAkun .= "<option value='" . $bar['noakun'] . "'>" . $bar['namaakun1'] . "</option>";
			} else {
				@$optAkun .= "<option value='" . $bar['noakun'] . "'>" . $bar['namaakun'] . "</option>";
			}
		}


		$str = "select distinct(noakuncoa) as noakuncoa from " . $dbname . ".keu_5akunbank";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrnoakunbank[$bar['noakuncoa']] = $bar['noakuncoa'];
		}

		if (!in_array($param['noakun'], $arrnoakunbank)) {
			// $optrekening = "<option value=''>(Penerima adalah akun kas)</option>";
			$optbank = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		}

		if ($param['noakun'] != '') {
			$whr = "";
			if (substr($param['noakun'], 5) != '11102') {
				$whr = " and noakuncoa='" . $param['noakun'] . "'";

				$str = "select * from " . $dbname . ".keu_5akunbank where pemilik='" . $kodeorg . "' " . $whr;
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$wheredz = " kodebank='" . $bar['namabank'] . "'";
					$optnama = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank', $wheredz);

					if ($rekening == $bar['noakun']) {
						$optbank .= "<option value='" . $bar['noakun'] . "' selected>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
					} else {
						$optbank .= "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
					}
				}
			}
		}



		// $tab.="<fieldset style='width:95%'>";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0  style='width:100%'>";
		if ($rekening != '') {
			$tglrenc = tanggalnormal($tglrenc);
		} else {
			$tglrenc = date('d-m-Y');
		}
		$tab .= "<tr  class=rowcontent>
				<td>" . $_SESSION['lang']['tanggalbayar'] . "</td> 
				<td>:</td>
				<td width=180px>
					<input type=text class=myinputtext readonly value=" . $tglrenc . "  id=tglbayar onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:151px;\"/>									
				</td>
			</tr>";
		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext readonly  id=kodeorg value='" . $kodeorg . "' disabled style=\"width:151px;\"/>									
			</td></tr>";

		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['noakun'] . "</td>
					<td>:</td>		
					<td>
						<select id=noakun2a style=\"width:155px;\" onchange=getbank()>'" . $optAkun . "'</select>
					</td></tr>";

		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['rekening'] . "</td>
					<td>:</td>		
					<td>
						<select id=rekening disabled style=\"width:155px;\">'" . $optbank . "'</select>
					</td></tr>";

		$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='AK' and	kodeparameter='AKAKUNBANK'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$arrdata = explode(',', $bar['nilai']);
		foreach ($arrdata as $key) {
			$arrakunbank[] = $key;
		}





		$arrctg = getEnum($dbname, 'keu_kasbankht', 'cgttu');
		foreach ($arrctg as $kei => $fal) {
			$sel = "";
			if ($cgttu == $kei) {
				$sel = "selected";
			}
			if (!in_array($noakun, $arrakunbank)) {
				if ($fal == 'Cash') {
					$optctg .= "<option " . $sel . " value='" . $kei . "'>" . $fal . "</option>";
				}
			} else {
				if ($fal == 'Cash') {
					$optctg .= "<option hidden " . $sel . " value='" . $kei . "'>" . $fal . "</option>";
				} else {
					$optctg .= "<option " . $sel . " value='" . $kei . "'>" . $fal . "</option>";
				}
			}
		}



		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['cgttu'] . "</td>
					<td>:</td>		
					<td>
						<select id=cgttu onchange=getbuktibayarkasir() style=\"width:155px;\">'" . $optctg . "'</select>
					</td></tr>";

		# ================================================== #
		# Permintaan Baru	
		# Jika Transfer, Pakai Inputan bukan Option
		# ================================================== #

		if ($fal == 'Transfer') {
			$disabledInput = 'disabled'; # Disable inputannya
		} else {
			$disabledOption = 'disabled'; # Disable optionnya
		}

		# Select
		$tab .= "<tr id=nocekopt class=rowcontent><td>" . $_SESSION['lang']['BuktiPembayaran'] . " " . $_SESSION['lang']['baru'] . "</td>
					<td>:</td>		
					<td>
						<select id=nocek style=\"width:155px;\" " . @$disabledOption . ">'" . $optbuktibayar . "'</select>
						<img id='nocek' onclick=z.elSearch('nocek',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;' " . @$disabledOption . ">
				</td></tr>";

		# Input
		$tab .= "
					<tr class=rowcontent>
						<td>" . $_SESSION['lang']['BuktiPembayaran'] . " <br/> Internet Banking / MCM</td>
						<td>:</td>		
						<td>
							<input class='myinputtext' id=nocekInput style=\"width:155px;\" " . @$disabledInput . " />
						</td>
					</tr>";

		# ================================================== #
		# END
		# ================================================== #

		$tab .= "<tr  class=rowcontent><td>" . $_SESSION['lang']['supplier'] . "</td>
					<td>:</td>		
					<td>
						<select id=supplier style=\"width:155px;\"  onchange=getrekening() >'" . $optsupplier . "'</select>
					<img id='supplier' onclick=z.elSearch('supplier',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
				</td>";


		$hidden = 'hidden';
		if ($autokb == 0 and $tipetransaksi == 'K' and $noakun < '1112101') {
			$hidden = '';
		}

		$tab .= "<tr  class=rowcontent " . $hidden . "><td nowrap>" . $_SESSION['lang']['rekening'] . " pembayaran ext)</td>
				<td>:</td>		
				<td>
					<select id=rekeningext style=\"width:155px;\" onchange=getdetailrekening()>'" . $optnorek . "'</select>
					<img id='rekeningext' onclick=z.elSearch('rekeningext',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>							
				</td></tr>";
		$tab .= "<tr  class=rowcontent " . $hidden . "><td nowrap>" . $_SESSION['lang']['namabank'] . " pembayaran ext)</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext  id=namabank  style=\"width:151px;\"/>									
			</td></tr>";

		$tab .= "<tr  class=rowcontent " . $hidden . "><td nowrap>" . $_SESSION['lang']['atasnama'] . " pembayaran ext)</td>
								<td>:</td>		
								<td>
									<input type=text class=myinputtext  id=anrekeningext  style=\"width:151px;\"/>									
						</td></tr>";


		// $tab.="<tr  class=rowcontent><td>".$_SESSION['lang']['BuktiPembayaran']."</td>
		// <td>:</td>		
		// <td>
		// <input type=text class=myinputtext   id=nocek disabled style=\"width:150px;\"/>									
		// </td></tr>";

		$optefill = makeOption($dbname, 'filemanager', 'namafile,id', "namafile='" . $notransaksi . "'");
		@$idefill = $optefill[$notransaksi];

		$showhide = "style='display:none'";
		$efill = '0';
		if ($idefill != '') {
			$showhide = "style='display:'";
			$efill = '1';
		}

		$tab .= "<tr class=rowcontent " . $showhide . ">
				<td>File Bukti Pembayaran</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
				</td>
			</tr>";

		$tab .= "<tr class=rowcontent>
				<td colspan=2></td>
				<td style='text-align:left'>
					<button class=mybutton id=tombolsavekasir onclick=kasbank('" . $notransaksi . "','" . $kodeorg . "','" . $noakun . "','" . $tipetransaksi . "','" . @$novoucher . "','" . $numRow . "','" . $efill . "','" . $autokb . "')>Simpan</button>
				</td>
			</tr>
		</table>";
		$tab .= "</fieldset>";

		echo $tab;
		break;



	case 'loaddata':
		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);
		$where = "";


		//kodeorg

		$optOrg = array();
		$optOrg = getOrgDetail(10);
		ksort($optOrg);

		$where .= "and kodeorg in ('" . implode("','", $optOrg) . "')";
		// if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' || $_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
		// } else {
		// 	$where .= "and kodeorg='" . $_SESSION['empl']['lokasitugas'] . "'";
		// }

		if ($kodeorg != '') {
			$where .= " and kodeorg = '" . $kodeorg . "'";
		}


		if ($notransaksi != '') {
			$where .= " and notransaksi like '%" . trim($notransaksi) . "%' ";
		}
		if ($novoucher != '') {
			$where .= " and novoucher like '%" . $novoucher . "%' ";
		}
		if ($noakun != '') {
			$where .= " and noakun = '" . $noakun . "' ";
		}
		if ($tipetransaksi != '') {
			$where .= " and tipetransaksi = '" . $tipetransaksi . "' ";
		}
		if ($tanggal1 != '--' and $tanggal2 != '--') {
			$where .= " and tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' ";
		}
		if ($supplier != '') {
			$where .= " and notransaksi in (select notransaksi from " . $dbname . ".keu_kasbankdt where kodesupplier='" . $supplier . "')";
		}
		if ($pembayaran != '') {
			$where .= " and pembayaran='" . trim($pembayaran) . "'";
		}
		if ($nocek != '') {
			$where .= " and nocek like '%" . trim($nocek) . "%' ";
		}
		if ($cgttu != '') {
			$where .= " and cgttu='" . trim($cgttu) . "' ";
		}
		if ($bayarke != '') {
			$where .= " and bayarkepada like '%" . trim($bayarke) . "%' ";
		}

		if ($rekening != '') {
			$where .= " and rekening = '" . trim($rekening) . "'";
		}
		$param['jumlah'] = str_replace(",", "", $param['jumlah']);
		if ($param['jumlah'] != '') {
			$where .= " and jumlah like '%" . trim($param['jumlah']) . "%'";
		}
		if ($param['keterangan'] != '') {
			$where .= " and keterangan like '%" . trim($param['keterangan']) . "%'";
		}
		foreach ($_SESSION['bgnotrans'] as $key => $row) {
			$tempnotr[$row['notransaksi']] = $row['notransaksi'];
			$temptipe[$row['notransaksi']] = $row['tipe'];
		}

		$str = "select notransaksi from " . $dbname . ".listfileupload where kriteriaefil='KSR'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$uploadkasir[$bar['notransaksi']] = $bar['notransaksi'];
		}



		$str = "select count(*) as jmlhrow from " . $dbname . ".keu_kasbankht where 1=1 and posting=1 " . $where;
		// echo $str;exit();
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$jlhbrs = owlBaris($res);
		$no = 0;
		$no = $maxdisplay;
		$str = "SELECT * from " . $dbname . ".keu_kasbankht  where 1=1 and posting=1 " . $where . " order by notransaksi desc, pembayaran asc, novoucher desc limit " . $offset . "," . $limit . " ";
		$tab = "";
		// if ($_SESSION['standard']['username'] == 'Zubaidah') {
		// 	exit("Warning: " . $str);
		// }
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$style = "";
			#apakah sudah ada jurnal

			if ($bar['pembayaran'] == '1') {
				$style = "hidden";
			}

			$nmkarcreate = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', " karyawanid='" . $bar['createby'] . "'");
			$nmkarkasir = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', " karyawanid='" . $bar['kasir'] . "'");

			$no += 1;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=left onclick=getvaluethis(this)>" . $bar['notransaksi'] . "</td>";
			$tab .= "<td align=left onclick=getvaluethis(this)>" . $bar['kodeorg'] . "</td>";
			$tab .= "<td align=center onclick=getvaluethis(this) style='min-width:70px'>" . tanggalnormal($bar['tanggalinput']) . "</td>";
			$tab .= "<td align=center onclick=getvaluethis(this) style='min-width:70px'>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td align=left onclick=getvaluethis(this)>" . $optAkun[$bar['noakun']] . "</td>";
			$tab .= "<td align=left onclick=getvaluethis(this)>" . $namabank[$bar['rekening']] . "</td>";
			$onclick = "style=color:blue;";
			if ($norekening[$bar['rekening']] == '') {
				$onclick = " style=cursor:pointer; title='Click untuk menandai rencana pembayaran.' onclick=getRencBayar('" . $bar['notransaksi'] . "')";
			}
			$tab .= "<td align=left " . $onclick . ">" . $norekening[$bar['rekening']] . "</td>";
			$tab .= "<td align=center onclick=getvaluethis(this)>" . $bar['tipetransaksi'] . "</td>";
			$tab .= "<td align=center onclick=getvaluethis(this)>" . $bar['matauang'] . "</td>";
			$tab .= "<td align=right onclick=getvaluethis(this)>" . number_format($bar['jumlah']) . "</td>";
			$tab .= "<td align=left onclick=getvaluethis(this)>" . $bar['keterangan'] . "</td>";
			$tab .= "<td align=left onclick=getvaluethis(this)>" . $bar['bayarkepada'] . "</td>";
			$tab .= "<td align=left onclick=getvaluethis(this)>" . $bar['novoucher'] . "</td>";
			$tab .= "<td align=left onclick=getvaluethis(this)>" . $bar['cgttu'] . "</td>";
			$tab .= "<td align=left onclick=getvaluethis(this)>" . $bar['nocek'] . "</td>";
			$tab .= "<td align=left onclick=getvaluethis(this)>" . $nmkarcreate[$bar['createby']] . "</td>";

			if ($uploadkasir[$bar['notransaksi']] != '') {
				$tab .= "<td align=left style=background-color:cyan; title='File upload kasir sudah dilakukan.' onclick=getvaluethis(this)>" . $nmkarkasir[$bar['kasir']] . "</td>";
			} else {
				$tab .= "<td align=left onclick=getvaluethis(this)>" . $nmkarkasir[$bar['kasir']] . "</td>";
			}

			if (in_array($bar['notransaksi'], $tempnotr) and $temptipe[$bar['notransaksi']] == '1') {
				$n = "disabled checked";
			} elseif (in_array($bar['notransaksi'], $tempnotr)) {
				$n = "checked";
			} else {
				$n = "";
			}

			$hiddenpdfcgttu = "";

			if ($bar['cgttu'] == 'Cash') {
				$hiddenpdfcgttu = "hidden";
			}

			$tab .= "<td align=center width=23px><input type='checkbox' " . $n . " " . $style . " onchange=\"getcheckbox('" . $bar['notransaksi'] . "','" . $no . "')\" id=no_" . $no . "></td>";

			$tab .= "<td align=center valign=center style='padding:5px'>
					<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"pdfkasbank('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['noakun'] . "','" . $bar['tipetransaksi'] . "',event);\">
				</td>
				<td align=center valign=center style='padding:5px'>
					<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"detailkasbank('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['noakun'] . "','" . $bar['tipetransaksi'] . "',event);\">
				</td>
				<td align=center valign=center style='padding:5px'>
					<img " . $style . " src=images/bayar.png class=resicon height='30' title='Bayar' onclick=\"bayar('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['noakun'] . "','" . $bar['tipetransaksi'] . "','" . $no . "',event);\">
				</td>";
			// $tab.="<td align=center valign=center style='padding:5px'>
			// <img ".$style2." src=images/pdf.jpg class=resicon width='30' height='30' title='Print by transaction' onclick=\"printbayar('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['noakun']."','".$bar['tipetransaksi']."','".$bar['cgttu']."','".$bar['rekening']."',event);\">
			// </td>";

			# Kasir
			$tab .= "<td align=center valign=center style='padding:5px'>
					<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print Kasir' onclick=\"printkasir('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['noakun'] . "','" . $bar['tipetransaksi'] . "','" . $bar['cgttu'] . "','" . $bar['rekening'] . "','" . $bar['nocek'] . "',event);\">
				</td>";

			$tab .= "<td align=center valign=center style='padding:5px'>
					<img {$hiddenpdfcgttu} " . $style2 . " src=images/pdf.jpg class=resicon width='30' height='30' title='Print by cheque' onclick=\"printbayarall('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['noakun'] . "','" . $bar['tipetransaksi'] . "','" . $bar['cgttu'] . "','" . $bar['rekening'] . "','" . $bar['nocek'] . "',event);\">
				</td>";
			if ($bar['pembayaran'] == 1) {
				$tab .= "<td align=center valign=center style='padding:5px'>
					<img src=images/tool.png class=resicon height='30' title='Ganti Bukti Bayar' onclick=\"gantibukti('" . $bar['notransaksi'] . "');\">";
			} else {
				$tab .= "<td align=center valign=center style='padding:5px'>
						</td>";
			}
			$tab .= "</tr>";
		}
		$footd = createpaging($jlhbrs, $limit, $page, '24', 'loaddata', 'getPage');

		echo $tab . "####" . $footd;
		break;
	case 'getRencBayar':
		$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $notransaksi . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$autokb = $bar['autokb'];
			$tipetransaksi = $bar['tipetransaksi'];
			$rekening = $bar['rekening'];
			$pembayaran = $bar['pembayaran'];
			$cgttu = $bar['cgttu'];
			$noakun = $bar['noakun'];
			$kodeorg = $bar['kodeorg'];
		}

		#= pengecekan jika status pembayaran sudah 1 maka exit;	
		if ($pembayaran == '1') {
			exit("Warning: Transaksi kasir sudah dilakukan, silahkan klik List data untuk melakukan refresh transaksi");
		}


		if ($noakun == '1110101' or $noakun == '1111101') {
			$whr = "";
			if ($noakun == '1111101') {
				$whr = " and matauang!='IDR'";
			} else {
				$whr = " and matauang='IDR'";
			}
			$str = "select * from " . $dbname . ".keu_5akunbank where pemilik='" . $kodeorg . "' " . $whr;
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$wheredz = " kodebank='" . $bar['namabank'] . "'";
				$optnama = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank', $wheredz);
				if ($rekening == $bar['rekening']) {
					$optbank .= "<option value='" . $bar['noakun'] . "' selected>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
				} else {
					$optbank .= "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
				}
			}
		}



		$tab = "<label>Silahkan isikan tanggal dan nomor rekening untuk rencana pembayaran.";
		$tab .= "</label><br><br>";
		$tab .= "<table>";
		$tab .= "<tr>
				<td>" . $_SESSION['lang']['tanggalbayar'] . "</td> 
				<td>:</td>
				<td width=180px>
					<input type=text class=myinputtext readonly value=" . date('d-m-Y') . "  id=tglrencbayar onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\"/>									
				</td>
			</tr>";

		$tab .= "<tr><td>" . $_SESSION['lang']['rekening'] . "</td>
				<td>:</td>		
				<td>
					<select id=rekeningrencbyr onchange=getsaldokasbank(this,'" . $notransaksi . "'); style=\"width:155px;\">'" . $optbank . "'</select>
				</td></tr>";

		$tab .= "<tr>
				<td colspan=2></td>
				<td style='text-align:left'>
					<button class=mybutton onclick=updateRencBayar('" . $notransaksi . "')>Simpan</button>
				</td>
			</tr>
		</table>
		<div id=contsaldokasbank></div>
		";

		echo $tab;

		break;
	case 'getsaldokasbank':
		$tglbayar = tanggalsystemn(checkPostGet('tglrencbayar', ''));

		$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $notransaksi . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$unit = $bar['kodeorg'];
			if ($bar['tipetransaksi'] == 'K') {
				$jumlah = $bar['jumlah'] * (-1);
			} else {
				$jumlah = $bar['jumlah'];
			}
		}

		$tgl2      = $tglbayar;
		$tgl1      = substr($tgl2, 0, 7) . "-01";
		$per1      = substr($tgl1, 0, 7);
		$tglawalbln = $per1 . '-01';
		$per1      = str_replace('-', '', $per1);
		$dtper1    = substr($per1, 4, 2);


		$bank = $param['norek'];

		$wherebank = " and rekening='" . $param['norek'] . "'";

		$str = "select pembayaran, tipetransaksi, sum(jumlah) as debet,sum(jumlah) as kredit  from " . $dbname . ".keu_kasbankht where kodeorg='" . $unit . "' and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' " . $wherebank . " and posting='1' group by pembayaran, tipetransaksi";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($bar['pembayaran'] == '1') {
				if ($bar['tipetransaksi'] == 'M') {
					$km += $bar['debet'];
				} else {
					$kk += $bar['kredit'];
				}
			} else {
				if ($bar['tipetransaksi'] == 'M') {
					$kmbp += $bar['debet'];
				} else {
					$kkbp += $bar['kredit'];
				}
			}
		}


		$str = "select sum(awal" . $dtper1 . ") as jumlah from " . $dbname . ".keu_saldobank where kodeorg='" . $unit . "' and periode='" . $per1 . "' and norek='" . $bank . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$sawal = $bar['jumlah'];
		}

		#jika saldo awal dari saldobulanan belum ada maka hitung nilai saldo awalnya
		if ($sawal == 0) {
			#cut off per 2022-01-01
			$str = "select sum(awal01) as jumlah from " . $dbname . ".keu_saldobank where kodeorg='" . $unit . "' and periode='202101' and norek='" . $bank . "'";
			foreach ($res as $bar) {
				$awaljan21 = $bar['jumlah'];
			}

			$str = "select tipetransaksi, sum(jumlah) as debet,sum(jumlah) as kredit  from " . $dbname . ".keu_kasbankht where kodeorg='" . $unit . "' and tanggal between '2022-01-01' and '" . tglkemarin($tgl1) . "' " . $wherebank . " and posting='1' group by tipetransaksi";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($bar['tipetransaksi'] == 'M') {
					$kmasuk = $bar['debet'];
				} else {
					$kkeluar = $bar['kredit'];
				}
			}
		}


		if ($sawal == 0) {
			$sawal = $awaljan21 + $kmasuk - $kkeluar;
		}

		$tab = "<table width=100%>";
		$tab .= "<tr  class=rowcontent style=background-color:#7FFFD4>
				<td colspan=3 align=center><b>Sudah dibayar :</b></td> 
			</tr>
			<tr  class=rowcontent>
				<td width=100px>" . $_SESSION['lang']['saldoawal'] . "</td> 
				<td>:</td>
				<td align=right>" . number_format($sawal, 2) . "</td>
			</tr>
			<tr class=rowcontent>
				<td>" . $_SESSION['lang']['debet'] . "</td>
				<td>:</td>		
				<td align=right>" . number_format($km, 2) . "</td>
			</tr>
			<tr class=rowcontent>
				<td>" . $_SESSION['lang']['kredit'] . "</td>
				<td>:</td>		
				<td align=right>" . number_format($kk, 2) . "</td>
			</tr>
			<tr class=rowcontent>
				<td>" . $_SESSION['lang']['saldoakhir'] . "</td>
				<td>:</td>		
				<td align=right><b>" . number_format(($sawal + $km) - $kk, 2) . "</b></td>
			</tr>
			
			<tr  class=rowcontent style=background-color:#F5F5DC>
				<td colspan=3 align=center><b>Belum dibayar :</b></td> 
			</tr>
			
			<tr class=rowcontent>
				<td>" . $_SESSION['lang']['debet'] . "</td>
				<td>:</td>		
				<td align=right>" . number_format($kmbp, 2) . "</td>
			</tr>
			<tr class=rowcontent>
				<td>" . $_SESSION['lang']['kredit'] . "</td>
				<td>:</td>		
				<td align=right>" . number_format($kkbp, 2) . "</td>
			</tr>
			<tr class=rowcontent>
				<td>Renc Bayar</td>
				<td>:</td>		
				<td align=right><i>" . number_format($jumlah, 2) . "</i></td>
			</tr>
			<tr class=rowcontent>
				<td>" . $_SESSION['lang']['saldoakhir'] . "</td>
				<td>:</td>";

		$saldoakhir = (((($sawal + $km) - $kk) + $kmbp) - $kkbp) + $jumlah;

		if ($saldoakhir < 0) {
			$tab .= "<td align=right style=color:red;><b>" . number_format($saldoakhir, 2) . "</b></td>";
		} else {
			$tab .= "<td align=right><b>" . number_format($saldoakhir, 2) . "</b></td>";
		}
		$tab .= "<input hidden id=saldoakhir value=" . $saldoakhir . "></tr>
			
			
		</table>
		";

		echo $tab;
		break;
	case 'updateRencBayar':
		$tglbayar        = tanggalsystemn(checkPostGet('tglbayar', ''));
		$str = "update " . $dbname . ".keu_kasbankht set rekening='" . $rekening . "',tanggal='" . $tglbayar . "' where notransaksi='" . $notransaksi . "' "; #exit("error".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
}
