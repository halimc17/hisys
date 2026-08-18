<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

$prdlist     		= checkPostGet('prdlist', '');
$unitlist     		= checkPostGet('unitlist', '');
$afdlist     		= checkPostGet('afdlist', '');

$proses     		= checkPostGet('proses', '');
$prd              = checkPostGet('prd', '');
$unit             = checkPostGet('unit', '');
$afd              = checkPostGet('afd', '');
$notransaksi      = checkPostGet('notransaksi', '');
$karid            = checkPostGet('karid', '');
$tglpnn           = checkPostGet('tglpnn', '');
$jenispremi       = checkPostGet('jenispremi', '');
$nospb            = checkPostGet('nospb', '');
$blokbesar        = checkPostGet('blokbesar', '');
$blokkecil        = checkPostGet('blokkecil', '');
$tahuntanam       = checkPostGet('tahuntanam', '');

$hektarpanen      = checkPostGet('hektarpanen', '');
$brondol          = checkPostGet('brondol', '');
$jjg              = checkPostGet('jjg', '');
$bjr              = checkPostGet('bjr', '');
$totalkg          = checkPostGet('totalkg', '');
$basistahuntanam  = checkPostGet('basistahuntanam', '');
$hk               = checkPostGet('hk', '');
$pothk            = checkPostGet('pothk', '');
$basispakai       = checkPostGet('basispakai', '');
$basisbaru        = checkPostGet('basisbaru', '');
$lebihbasis       = checkPostGet('lebihbasis', '');
$upah             = checkPostGet('upah', '');
$upahpot          = checkPostGet('upahpot', '');
$upahlb           = checkPostGet('upahlb', '');
$upahbro          = checkPostGet('upahbro', '');
$premiks          = checkPostGet('premiks', '');
$premikh          = checkPostGet('premikh', '');
$dendapn          = checkPostGet('dendapn', '');
$totalupah        = checkPostGet('totalupah', '');

$mandor1       = checkPostGet('mandor1', '');
$mandor        = checkPostGet('mandor', '');
$kerani        = checkPostGet('kerani', '');


$hektarpanen    = str_replace(',', '', $hektarpanen);
$brondol        = str_replace(',', '', $brondol);
$jjg      		= str_replace(',', '', $jjg);
$bjr        	= str_replace(',', '', $bjr);
$totalkg        = str_replace(',', '', $totalkg);
$basistahuntanam  = str_replace(',', '', $basistahuntanam);
$hk        		= str_replace(',', '', $hk);
$pothk        	= str_replace(',', '', $pothk);
$basispakai     = str_replace(',', '', $basispakai);
$basisbaru      = str_replace(',', '', $basisbaru);
$lebihbasis     = str_replace(',', '', $lebihbasis);
$upah       	= str_replace(',', '', $upah);
$upahlb       	= str_replace(',', '', $upahlb);
$upahbro       	= str_replace(',', '', $upahbro);
$premiks       	= str_replace(',', '', $premiks);
$premikh       	= str_replace(',', '', $premikh);
$dendapn       	= str_replace(',', '', $dendapn);
$totalupah      = str_replace(',', '', $totalupah);



$nikkar       = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$nmorg        = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$jab          = getPostingJabatan('premipanen');
$tglEntry     = date('Ymd');

switch ($proses) {
	case 'getdivisi':
		$optafd = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $param['unit'] . "' and tipe='AFDELING'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optafd .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}
		echo $optafd;
		break;
	case 'getdivisiList':
		$optafd = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $unitlist . "' and tipe='AFDELING'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optafd .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}
		echo $optafd;
		break;

	case 'deleteTrans':
		#Validasi :
		#1. Cek Prd Akuntansi
		$str = "select * from " . $dbname . ".setup_periodeakuntansi
		where periode = '" . $prd . "' and kodeorg='" . $unit . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if ($bar['tutupbuku'] == '1') {
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}

		#2. Cek Prd Gaji
		$str = "select * from " . $dbname . ".sdm_5periodegaji
		where periode = '" . $prd . "' and kodeorg='" . $unit . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if ($bar['sudahproses'] == '1') {
			exit('Error : Periode Gaji Sudah di Tutup.');
		}

		#3. Cek Transaksi sudah di posting belum
		$str = "select * from " . $dbname . ".kebun_3premipemanen_v2
		where periode = '" . $prd . "' and kodeorg='" . $unit . "' and notransaksi = '" . $notransaksi . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if ($bar['posting'] == '1') {
			exit('Error : Transaksi notransaksi : ' . $notransaksi . ' unit : ' . $unit . ' periode : ' . $prd . ' sudah di Posting.');
		}

		if (substr($notransaksi, 0, 6) != str_replace("-", "", $prd)) {
			exit("Error : Nomor Transaksi dan Periode tidak sesuai, silahkan reload.");
		}

		#Hapus Transaksi
		$str = "delete from " . $dbname . ".kebun_3premipemanen_v2 where `notransaksi` ='" . $notransaksi . "'"; #exit("error".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		break;

	case 'savedata':

		if ($param['currRow'] == '1') {

			#Validasi :
			#1. Cek Prd Akuntansi
			$str = "select * from " . $dbname . ".setup_periodeakuntansi
			where periode = '" . $prd . "' and kodeorg='" . $unit . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			if ($bar['tutupbuku'] == '1') {
				exit('Error : Periode Akuntansi Sudah di Tutup.');
			}

			#2. Cek Prd Gaji
			$str = "select * from " . $dbname . ".sdm_5periodegaji
			where periode = '" . $prd . "' and kodeorg='" . $unit . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			if ($bar['sudahproses'] == '1') {
				exit('Error : Periode Gaji Sudah di Tutup.');
			}
		}

		#3. Cek Transaksi sudah di posting belum
		$str = "select * from " . $dbname . ".kebun_3premipemanen_v2 where notransaksi = '" . $notransaksi . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if ($bar['posting'] == '1') {
			exit('Error : Transaksi notransaksi : ' . $notransaksi . ' unit : ' . $unit . ' periode : ' . $prd . ' sudah di Posting.');
		}

		if (substr($notransaksi, 0, 6) != str_replace("-", "", $prd)) {
			exit("Error : Nomor Transaksi dan Periode tidak sesuai, silahkan reload.");
		}

		$str = "select * from " . $dbname . ".kebun_spb_vw where tanggalpanen = '" . $tglpnn . "' and kodeorg='" . $unit . "'  and posting='0' group by nospb";
		$res = fetchdata($str);
		$row = count($res);
		if ($row > 0) {
			$tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr class=rowheader>";
			$tab .= "<th align=center>" . $_SESSION['lang']['nourut'] . "</th>";
			$tab .= "<th align=center>" . $_SESSION['lang']['nospb'] . "</th>";
			$tab .= "<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>";
			$tab .= "</tr></thead><tbody>";

			$nour = 0;
			foreach ($res as $bar) {
				$nour++;
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td>" . $nour . "</td>";
				$tab .= "<td>" . $bar['nospb'] . "</td>";
				$tab .= "<td>" . $bar['tanggal'] . "</td>";
				$tab .= "</tr>";
			}

			$tab .= "</tbody></table>";


			exit('<b> WARNING : Ada SPB yang belum di Posting </b><br><hr>' . $tab);
		}

		$str = "select * from " . $dbname . ".kebun_prestasi_vw where tanggal = '" . $tglpnn . "' and unit='" . $unit . "' and kodeorg like '" . $afd . "%' and jurnal='0' group by notransaksi";
		$res = fetchdata($str);
		$row = count($res);
		if ($row > 0) {
			$tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr class=rowheader>";
			$tab .= "<th align=center>" . $_SESSION['lang']['nourut'] . "</th>";
			$tab .= "<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>";
			$tab .= "<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>";
			$tab .= "</tr></thead><tbody>";

			$nour = 0;
			foreach ($res as $bar) {
				$nour++;
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td>" . $nour . "</td>";
				$tab .= "<td>" . $bar['notransaksi'] . "</td>";
				$tab .= "<td>" . $bar['tanggal'] . "</td>";
				$tab .= "</tr>";
			}

			$tab .= "</tbody></table>";


			exit('<b> WARNING : Ada transaksi kegiatan panen yang belum di posting </b><br><hr>' . $tab);
		}

		if (substr($notransaksi, 0, 6) != str_replace("-", "", $prd)) {
			exit("Error : Nomor Transaksi dan Periode tidak sesuai, silahkan reload.");
		}

		if (substr($tglpnn, 0, 7) != $prd) {
			exit("Error : Periode dan tanggal panen tidak sesuai, proses dibatalkan.");
		}

		if ($jjg > 0  and $bjr == 0) {
			$errorDB .= ' <br> Ada Jjg > 0  dan Bjr masih 0,  pastikan sudah melakukan proses ambil KG timbangan <br>';
		}

		try {
			$owlPDO->beginTransaction();

			if ($param['currRow'] == '1') {
				#Hapus Transaksi
				$str = "delete from " . $dbname . ".kebun_3premipemanen_v2 where `notransaksi` ='" . $notransaksi . "'";
				$owlPDO->exec($str);
			}

			$data = array(
				'notransaksi' 	=> $notransaksi,
				'kodeorg'     	=> $unit,
				'divisi'     	=> $afd,
				'periode'   	=> $prd,
				'tanggalpanen'  => $tglpnn,
				'nospb'  		=> $nospb,
				'mandor1'  		=> $mandor1,
				'mandor'  		=> $mandor,
				'kerani'  		=> $kerani,
				'karyawanid'    => $karid,
				'indukblok'   	=> $blokbesar,
				'blokkecil'   	=> $blokkecil,
				'tahuntanam'    => $tahuntanam,
				'hapanen'    	=> $hektarpanen,
				'jjg'    		=> $jjg,
				'brondol'    	=> $brondol,
				'bjr'    		=> $bjr,
				'kg'    		=> $totalkg,
				'basis'    		=> $basispakai,
				'lbbasis'       => $lebihbasis,
				'hk'    		=> $hk,
				'pothk'    		=> $pothk,
				'upah'    		=> $upah,
				'upahbro'       => $upahbro,
				'potupah'    	=> $upahpot,
				'premilb'    	=> $upahlb,
				'premikehadiran'    	=> $premikh,
				'premikesulitan'    	=> $premiks,
				'denda'    				=> $dendapn,
				'totalupah'    			=> $totalupah,
				'createby'    	=> $_SESSION['standard']['userid'],
				'createdate'  	=> date('Y-m-d H:i:s')
			);


			$cols = array();
			foreach ($data as $key => $row) {
				$cols[] = $key;
			}

			# Insert
			$query = insertQuery($dbname, 'kebun_3premipemanen_v2', $data, $cols);
			$owlPDO->exec($query);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo $errorDB .= " Gagal Simpan Database  : " . addslashes($e->getMessage());
			die();
		}

		if ($errorDB != '') {
			#Hapus Transaksi
			$str = "delete from " . $dbname . ".kebun_3premipemanen_v2 where `notransaksi` ='" . $notransaksi . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Roll Back !: " . $e->getMessage() . "\n";
				die();
			}

			exit("Warning Gagal simpan data : " . $errorDB);
		}




		break;

	case 'loaddata':

		$whereData = '';
		if ($prdlist != '') {
			$whereData .= "and periode = '" . $prdlist . "'";
		}

		if ($unitlist != '') {
			$whereData .= "and kodeorg = '" . $unitlist . "'";
		} else {
			$whereData .= "and kodeorg in (" . getOrgDetail(2) . ")";
		}

		if ($afdlist != '') {
			$whereData .= "and divisi =  '" . $afdlist . "'";
		}

		$limit = 20;
		$page = 0;
		$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
		if (isset($_POST['page'])) {
			$page = intval($_POST['page']);
			if ($page < 0)
				$page = 0;
		}


		$strx = "select * from " . $dbname . ".kebun_3premipemanen_v2 where 1=1 " . $whereData . " group by notransaksi order by notransaksi asc, periode desc, kodeorg asc, divisi asc";
		$resxx = fetchdata($strx);
		$jlhbrs = count($resxx);

		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);
		$tab = "";
		$no = $maxdisplay;


		$str = "select *,sum(jjg) as tojjg,sum(jjg * bjr) as totgk,sum(upah) as totupah,sum(brondol)  as totbrondol,sum(upahbro) as totbro, sum(denda) as totdenda, sum(premilb) as totpremilb,sum(premikehadiran) as totpk,sum(premikesulitan) as totps,sum(totalupah) as totalupah,posting from " . $dbname . ".kebun_3premipemanen_v2 where 1=1 " . $whereData . " group by notransaksi order by notransaksi asc, periode desc, kodeorg asc, divisi asc";
		$res = fetchdata($str);
		$row = count($res);
		if ($row > 0) {
			$no = 0;
			foreach ($res as $bar) {
				$no++;
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td align=center>" . $no . "</td>";
				$tab .= "<td align=center>" . $bar['periode'] . "</td>";
				$tab .= "<td align=center>" . $bar['tanggalpanen'] . "</td>";
				$tab .= "<td align=center>" . $bar['notransaksi'] . "</td>";
				$tab .= "<td align=center>" . $bar['kodeorg'] . "</td>";
				$tab .= "<td align=center>" . $bar['divisi'] . "</td>";
				$tab .= "<td align=center>" . number_format($bar['tojjg'], 2) . "</td>";
				$tab .= "<td align=center>" . number_format($bar['totgk'], 2) . "</td>";
				$tab .= "<td align=center>" . number_format($bar['totupah'], 2) . "</td>";

				$tab .= "<td align=center>" . number_format($bar['totbrondol'], 2) . "</td>";
				$tab .= "<td align=center>" . number_format($bar['totbro'], 2) . "</td>";

				$tab .= "<td align=center>" . number_format($bar['totpremilb'], 2) . "</td>";
				$tab .= "<td align=center>" . number_format($bar['totpk'], 2) . "</td>";
				$tab .= "<td align=center>" . number_format($bar['totps'], 2) . "</td>";
				$tab .= "<td align=center>" . number_format($bar['totdenda'], 2) . "</td>";

				$tab .= "<td align=center>" . number_format($bar['totalupah'], 2) . "</td>";
				$tab .= "<td align=center>" . getNamaKaryawan($bar['createby']) . "</td>";

				if ($bar['posting'] == 1) {
					$text = 'Sudah Posting';
				} else {
					$text = 'Belum Posting';
				}

				$tab .= "<td align=center>" . $text . "</td>";

				if ($bar['posting'] == 0) {
					$tab .= "<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
							onclick=\"del('" . $bar['notransaksi'] . "','" . $bar['periode'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($tglmin) . "','" . tanggalnormal($tglmax) . "');\" ></td>";
					$post = '';
					if (in_array($_SESSION['empl']['jabatan'], $jab, true)) {
						$post = " onclick=\"posting('" . $bar['notransaksi'] . "','" . $bar['periode'] . "','" . $bar['kodeorg'] . "','" . $bar['tanggalpanen'] . "');\" ";
					}
					$tab .= "<td align=center width=20px><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30' " . $post . " title='Posting'></td>";
				} else {
					if (in_array($_SESSION['empl']['jabatan'], $jab, true)) {
						$icon = "images/icons/04/16/04.png";
						$title = "Unposting";
						$unpost = " onclick=\"unposting('" . $bar['notransaksi'] . "','" . $bar['periode'] . "','" . $bar['kodeorg'] . "','" . $bar['tanggalpanen'] . "');\" ";
					} else {
						$icon = "images/icons/04/16/02.png";
						$title = "Posted";
						$unpost = '';
					}
					$tab .= "<td></td>";
					$tab .= "<td align=center width=20px><img src=" . $icon . " class=zImgBtn class=zImgBtn height='30' title='" . $title . "' " . $unpost . " ></td>";
				}

				$tab .= "</tr>";
			}
		} else {
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
		$footd = "";
		$footd .= "</tr>
                     <tr><td colspan=25 align=center>";
		if ($page == '0') {
			$footd .= "<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
		} else {
			$footd .= "<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
		}
		$footd .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
		if (($page + 1) == $totrows) {
			$footd .= "<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
		} else {
			$footd .= "<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
		}
		$footd .= "</td>
            </tr>";

		echo $tab . "####" . $footd;
		break;

	case 'unposting':
		#========================= Validasi Data ===========================
		#1. Cek Prd Akuntansi
		$str = "select * from " . $dbname . ".setup_periodeakuntansi where periode = '" . $prd . "' and kodeorg='" . $unit . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if ($bar['tutupbuku'] == '1') {
			exit('Error : Periode Akuntansi Sudah di Tutup.');
		}
		#2. Cek Prd Gaji
		$str = "select * from " . $dbname . ".sdm_5periodegaji where periode = '" . $prd . "' and kodeorg='" . $unit . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if ($bar['sudahproses'] == '1') {
			exit('Error : Periode Gaji Sudah di Tutup.');
		}
		#========================= End Validasi Data ===========================
		#============================= Update ==================================

		try {
			$owlPDO->beginTransaction();

			# Hapus Jurnal
			$str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $notransaksi . "'";
			$owlPDO->exec($str);

			# Update flag transaksi
			$str = "update " . $dbname . ".kebun_3premipemanen_v2 set posting='0', jurnal = '', postingby ='" . $_SESSION['standard']['userid'] . "', postingdate='" . $tglEntry . "' where notransaksi='" . $notransaksi . "'";
			$owlPDO->exec($str);

			# Hapus Kebun_Aktifitas
			$str = "delete from " . $dbname . ".kebun_aktifitas where noreferensi ='" . $notransaksi . "'";
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		#=========================== End Update ===============================
		break;
}
