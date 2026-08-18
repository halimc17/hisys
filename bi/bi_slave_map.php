<?
	include('../config/connection.php');
	include('../lib/nangkoelib.php');
	include('master_validation.php');
	include('lib/zLib.php');
	include("../lib/mharvest/getContentAPI.php");

	$urlocal = $_SERVER['HTTP_ORIGIN'] . '/' . implode("/", $data);
	/** GET OPTIONS API */
	$options = array(
		'client_id' => 'USERSYSTEM',
		'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
		'username' => $_SESSION['standard']['username']
	);

	// GET URI FOR PRODUCTION
	$expri = explode("/", $_SERVER['REQUEST_URI']);

	$kebun = checkPostGet('kebun', '');
	$method = checkPostGet('method', '');
	$vChecked = checkPostGet('vChecked', '');
	$kodept = checkPostGet('kodept', '');
	$idsvg = checkPostGet('idsvg', '');
	$tipesvg = checkPostGet('tipesvg', '');
	$periodeawal = checkPostGet('periodeawal', '');
	$periodeakhir = checkPostGet('periodeakhir', '');
	$detailtipedokumen = checkPostGet('detailtipedokumen', '');
	$detailkegiatan = checkPostGet('detailkegiatan', '');
	$noakun = checkPostGet('noakun', '');
	$detaillaporan2 = checkPostGet('detaillaporan2', '');
	$showstatusblok = checkPostGet('showstatusblok', '');
	$divNewDetail = checkPostGet('divNewDetail', '');
	$detInfo = checkPostGet('detInfo', '');
	$detailreport = checkPostGet('detailreport', '');
	$karyawanid = checkPostGet('karyawanid', '');
	$tanggalhistory = checkPostGet('tanggalhistory', '');
	$tipetracking = checkPostGet('tipetracking', '');
	$namafile = checkPostGet('namafile', '');
	
	$kbnarr = strToArray($kebun, '##');
	
	$arrnmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');

	switch ($method) {
		case 'loadmenu':
			break;
		case 'checkedmap':
			//Get From master warna
			$str = "select * from " . $dbname . ".bi_5warna";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$fill[$bar['tipe']] = $bar['fill'];
				$line[$bar['tipe']] = $bar['line'];
				$width[$bar['tipe']] = $bar['width'];
			}

			//Get Tipe feature map
			$str = "select * from " . $dbname . ".bi_map_basic where tipepeta = '" . $vChecked . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$str2 = "select * from " . $dbname . ".bi_5tipepeta where id_tipepeta = '" . $bar['tipepeta'] . "'";
				$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_ASSOC);
				$bar2 = $res2->fetch();
				$tipefeature = $bar2['tipefeature'];

				if ($fill[$vChecked] == '') {
					$fill[$vChecked] = 'none';
				} else {
					$fill[$vChecked] = $fill[$vChecked];
				}

				if ($line[$vChecked] == '') {
					$line[$vChecked] == 'none';
				} else {
					$line[$vChecked] == $line[$vChecked];
				}

				if ($width[$vChecked] == '') {
					$width[$vChecked] = 0.05;
				} else {
					$width[$vChecked] = $width[$vChecked];
				}

				$expTitle = explode('##', $bar['keterangan']);
				if ($tipefeature == 'path') {
					$style = "style='fill:" . $fill[$vChecked] . ";stroke-linejoin:round;stroke:" . $line[$vChecked] . ";stroke-width:" . $width[$vChecked] . ";cursor:pointer;' vector-effect='non-scaling-stroke'";
					$result .= "<path id='" . $bar['idsvg'] . "' d='" . $bar['path'] . "' title='" . $expTitle[0] . "' " . $style . " onclick=\"showinfosvg('" . $bar['idsvg'] . "',0,event)\"><title>" . $expTitle[0] . "</title></path>";
				} else {
					$pieces = explode(',', $bar['path']);
					$result .= "<g class='non-scaling'>";
					$result .= "<circle class='non-scaling' transform='translate(" . $pieces[0] . " " . $pieces[1] . ")' title='" . $expTitle[0] . "' id='" . $bar['tipepeta'] . "' fill='" . $fill[$vChecked] . "' r=" . $width[$vChecked] . " onclick=\"showinfosvg('" . $bar['idsvg'] . "',0,event)\"><title>" . $expTitle[0] . "</title></circle>";
					$result .= "</g>";
				}
			}

			echo $result;
			break;

		case 'preview':
			if (str_replace('-', '', $periodeawal) > str_replace('-', '', $periodeakhir)) {
				exit("error : Periode awal harus lebih kecil dari periode akhir.");
			}

			if ($detailtipedokumen == '') {
				exit("error : Tipe Dokumen harus dipilih.");
			}

			if ($detailkegiatan == '') {
				exit("error : Kegiatan harus dipilih.");
			}

			//Get From master warna
			$str = "select * from " . $dbname . ".bi_5warna";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$fill[$bar['tipe']] = $bar['fill'];
				$line[$bar['tipe']] = $bar['line'];
				$width[$bar['tipe']] = $bar['width'];
			}

			$str = "select * from " . $dbname . ".bi_map_transaksi where (periode between '" . $periodeawal . "' and '" . $periodeakhir . "') and kodeorg = '" . $kebun . "' and tipedok = '" . $detailtipedokumen . "' and kodekegiatan = '" . $detailkegiatan . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$str2 = "select * from " . $dbname . ".bi_5tipepeta where tipekelompok = '" . $bar['tipepeta'] . "' and keterangan = '" . $bar['tipedok'] . "'";
				$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_ASSOC);
				$bar2 = $res2->fetch();
				$tipefeature = $bar2['tipefeature'];
				$expTitle = explode('##', $bar['keterangan']);
				if ($tipefeature == 'path') {
					$style = "style='fill:" . ($bar['fitur'] == 'Polygon' ? $bar['warna'] : 'none') . ";stroke-linejoin:round;stroke:" . ($bar['fitur'] == 'Polygon' ? '' : $bar['warna']) . ";stroke-width:1;cursor:pointer;' vector-effect='non-scaling-stroke'";
					$result .= "<path id='" . $bar['idsvg'] . "' d='" . $bar['path'] . "' title='" . $expTitle[0] . "' " . $style . " onclick=\"showinfosvg('" . $bar['idsvg'] . "',2,event)\"><title>" . $expTitle[0] . "</title></path>";
				} else {
					$pieces = explode(',', $bar['path']);
					$result .= "<circle class='non-scaling' transform='translate(" . $pieces[0] . "," . $pieces[1] . ")' r=0.001 title='" . $expTitle[0] . "' id='" . $bar['tipepeta'] . "' fill='" . $bar['warna'] . "' onclick=\"showinfosvg('" . $bar['idsvg'] . "',2,event)\"><title>" . $expTitle[0] . "</title></cicle>";
				}
			}

			echo $result;
			break;

		case 'getkebun':
			$optdata = array();

			$str = "SELECT DISTINCT(unit) AS unit FROM ".$dbname.".bi_map_pt WHERE kodeorg = '".$kodept."' ORDER BY unit";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optNamaUnit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$bar['unit']."'");
				$data['unit']	 = $bar['unit'];
				$data['namaunit'] = $optNamaUnit[$bar['unit']];
				$optdata[] = $data;
			}

			$optUnit = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			//$optUnit .= "<option value='ALL'>".$_SESSION['lang']['all']."</option>";
			
			if ($data > 0) {
				$optUnit .= "<option value='";
				$optUnit .= $optdata[0]['unit'];
				for ($i = 1; $i < count($optdata); $i++) {
					$optUnit .= "##".$optdata[$i]['unit'];
				}
				$optUnit .= "'>".$_SESSION['lang']['all']."</option>";
				for ($i = 0; $i < count($optdata); $i++) {
					$optUnit .= "<option value='".$optdata[$i]['unit']."'>".$optdata[$i]['namaunit']."</option>";
				}
			}

			echo $optUnit;
			
			break;
		case 'getdetailkebun':
			//Get Master Warna
			$str = "SELECT * FROM ".$dbname.".bi_5warna";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$fill[$bar['tipe']] = $bar['fill'];
				$line[$bar['tipe']] = $bar['line'];
				$width[$bar['tipe']] = $bar['width'];
			}

			//Get MAP Blok
			// if(count($kbnarr)>1){
			// 	$kebunall = $kbnarr;
			// 	$kebun = "'".$kebunall[0]."'";
			// 	for($i=1; $i<count($kebunall); $i++){
			// 		$kebun .= ",'".$kebunall[$i]."'";
			// 	}
			// 	$where_unit = "And unit in (".$kebun.")";
			// 	$where_unit2 = "t1.kodeorg in (".$kebun.")";
			// 	$where_unit3 = "c.lokasitugas in (".$kebun.")";
			// }else{
			// 	$where_unit = "And unit = '".$kbnarr."'";
			// 	$where_unit2 = "t1.kodeorg = '".$kbnarr."'";
			// 	$where_unit3 = "c.lokasitugas = '".$kbnarr."'";
			// }
			//exit("ERROR:".$where_unit);

			// Tambahkan kondisi AND a.path NOT LIKE '%<%' untuk menghindari path yg ada tag html nya
			$str = "
				SELECT a.*, IFNULL(b.namaorganisasi, SUBSTRING_INDEX(a.keterangan, '##', 1)) AS namaorganisasi
				FROM ".$dbname.".bi_map_pt a
				LEFT JOIN ".$dbname.".organisasi b ON SUBSTRING_INDEX(a.keterangan, '##', 1) = b.kodeorganisasi
				WHERE a.kodeorg = '".$kodept."' ".forKebunAll($kbnarr, 'a.unit', 'in')." ORDER BY a.tipepeta ASC
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);

			$no = "";
			$pointx = "";
			$pointy = "";
			$result = "";
			while ($bar = $res->fetch()) {
				if ($no != "" && $no != $bar['tipepeta']) {
					$result .= "</g>";
				}

				if ($no == "" || $no != $bar['tipepeta']) {
					$pointx1 = $bar['viewbox'];
					$pointx1 = explode(' ', $pointx1);
					$pointx = ($pointx1[0] + ($pointx1[2] / 2));
					$pointy = ($pointx1[1] + ($pointx1[3] / 2));

					// if($tipefeature == 'path'){
					// 	$pointx1 = explode('l', $bar['path']);
					// 	$pointx1 = $pointx1[0];
					// 	$pointx1 = explode('M', $pointx1);
					// 	$pointx1 = $pointx1[1];
					// 	$pointx1 = explode(',', $pointx1);
					// 	$pointx = $pointx1[0];
					// 	$pointy = $pointx1[1];
					// }else{
					// 	$pointx1 = explode(',', $bar['path']);
					// 	$pointx = $pointx1[0];
					// 	$pointy = $pointx1[1];
					// }

					if ($bar['tipepeta'] == $firstPT || $bar['tipepeta'] == $textBlok) {
						$vDisplay = "";
					} else {
						// $vDisplay = 'none';
						$vDisplay = "style='display:none'";
					}

					// $result .= "
					// 	<g id='".$bar['tipepeta']."' style='display:".$vDisplay."'>
					// 		<desc>Layer ".$bar['tipepeta']."</desc>
					// ";
					$result .= "
						<g id='".$bar['tipepeta']."' ".$vDisplay.">
							<desc>Layer ".$bar['tipepeta']."</desc>
					";

					$no = $bar['tipepeta'];
				}

				if ($fill[$bar['tipepeta']] == '') {
					$fill[$bar['tipepeta']] = 'none';
				} else {
					$fill[$bar['tipepeta']] = $fill[$bar['tipepeta']];
				}

				if ($line[$bar['tipepeta']] == '') {
					$line[$bar['tipepeta']] == 'none';
				} else {
					$line[$bar['tipepeta']] == $line[$bar['tipepeta']];
				}

				if ($width[$bar['tipepeta']] == '') {
					$width[$bar['tipepeta']] = 0.05;
				} else {
					$width[$bar['tipepeta']] = $width[$bar['tipepeta']];
				}

				$str2 = "SELECT * FROM ".$dbname.".bi_5tipepeta WHERE id_tipepeta = '".$bar['tipepeta']."'";
				try {
					$res2 = $owlPDO->query($str2);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res2->setFetchMode(PDO::FETCH_ASSOC);
				$bar2 = $res2->fetch();
				$tipefeature = $bar2['tipefeature'];
				$expTitle = explode('##', $bar['keterangan']);
				if ($tipefeature == 'path') {
					$style = "fill='".$fill[$bar['tipepeta']]."' style='stroke:".$line[$bar['tipepeta']].";stroke-width:".$width[$bar['tipepeta']].";stroke-linejoin:round;cursor:help;' vector-effect='non-scaling-stroke'";

					$result .= "
						<path id='".$bar['idsvg']."' d='".$bar['path']."' title='".$expTitle[0]."' ".$style." onclick=\"showinfosvg('".$bar['idsvg']."', 1, event)\" fill-opacity='0.4'>
							<title>".$bar['namaorganisasi']."</title>
						</path>
					";
				} else {
					// ada nilai path yang tidak sesuai format
					$pieces = explode(',', $bar['path']);

					if ($bar['tipepeta'] == $textBlok) {
						$result .= "
							<g font-family='verdana' font-size='0.5' kerning='0' font-weight='50' fill='#000000' xml:space='preserve'>
								<text transform='matrix(0.001 0 0 0.001 ".($pieces[0] - 0.001)." ".($pieces[1] + 0.0001).")' tipepeta='".$textBlok."'>
									".$bar['namaorganisasi']."
								</text>
							</g>
						";
					} else {
						$result .= "
							<circle cx='".$pieces[0]."' cy='".$pieces[1]."' title='".$expTitle[0]."' id='".$bar['tipepeta']."' fill='".$fill[$bar['tipepeta']]."' r='".$width[$bar['tipepeta']]."' onclick=\"showinfosvg('".$bar['idsvg']."', 1, event)\" style='cursor:help'>
								<title>".$bar['namaorganisasi']."</title>
							</circle>
						";
					}
				}
			}

			$result .= "</g>";

			// $str = "select * from ".$dbname.".bi_map_pt where kodeorg = '".$kodept."' and unit = '".$kebun."' order by tipepeta asc";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// while($bar = $res->fetch()){
			// if($bar['tipepeta'] == $firstPT){
			// $str2 = "select * from ".$dbname.".bi_5tipepeta where id_tipepeta = '".$bar['tipepeta']."'";
			// $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			// $res2->setFetchMode(PDO::FETCH_ASSOC);
			// $bar2 = $res2->fetch();
			// $tipefeature = $bar2['tipefeature'];
			// $expTitle = explode('##', $bar['keterangan']);

			// $coorSvg = str_replace(' ',',',$bar['path']);
			// $coorSvg = str_replace('L',',',$coorSvg);
			// $coorSvg = str_replace('l',',',$coorSvg);
			// $coorSvg = str_replace('M','',$coorSvg);
			// $coorSvg = str_replace('m','',$coorSvg);
			// $coorSvg = explode(',',$coorSvg);

			// $result .= "<g font-family='verdana' font-size='1' kerning='0' font-weight='100' fill='#000000' xml:space='preserve'>
			// <text transform='matrix(0.001 0 0 0.001 ".$coorSvg[0]." ".$coorSvg[1].")'>".substr($expTitle[0],-4)."</text>
			// </g>";
			// }
			// }

			//Get List Map PT
			$result2 = "";
			$result2 = "
				<table>
					<tr>
						<td align=center></td>
						<td style='text-align:center'><b>Fill</b></td>
						<td style='width:20px'>&nbsp;</td>
						<td style='text-align:center'><b>Line</b></td>
					</tr>
			";

			$str = "
				SELECT DISTINCT(tipepeta) AS tipepeta
				FROM ".$dbname.".bi_map_pt
				WHERE kodeorg = '".$kodept."' ".forKebunAll($kbnarr, 'unit', 'in')
			;
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optKetTipe = makeOption($dbname, 'bi_5tipepeta', 'id_tipepeta,keterangan', "id_tipepeta='".$bar['tipepeta']."'");
				
				$result2 .= "
					<tr>
						<td style='float:left;width:100%;padding-bottom:4px;list-style-type:none;padding-right:15px;'>
				";
				
				if ($bar['tipepeta'] == $firstPT || $bar['tipepeta'] == $textBlok) {
					$result2 .= "
						<input type='checkbox' id='tipepetapt' name='tipepetapt[]' value='".$bar['tipepeta']."' checked onclick=checkMarkListPt(this) />
						".$optKetTipe[$bar['tipepeta']]."
						<input type='hidden' id='MARK_".$bar['tipepeta']."' value='1'>
					";
				} else {
					$result2 .= "
						<input type='checkbox' id='tipepetapt' name='tipepetapt[]' value='".$bar['tipepeta']."' onclick=checkMarkListPt(this) />
						".$optKetTipe[$bar['tipepeta']]."
						<input type='hidden' id='MARK_".$bar['tipepeta']."' value='0'>
					";
				}

				$result2 .= "
						</td>
						<td style='width:25px;border-top:1px solid #FFF;border-left:1px solid #FFF;border-bottom:1px solid #FFF;border-right:1px solid #FFF;' bgcolor=".(@$fill[$bar['tipepeta']] == 'none' ? '' : @$fill[$bar['tipepeta']])."></td>
						<td></td>
						<td style='width:25px;border-top:1px solid #FFF;border-left:1px solid #FFF;border-bottom:1px solid #FFF;border-right:1px solid #FFF;' bgcolor=".(@$line[$bar['tipepeta']] == 'none' ? '' : @$line[$bar['tipepeta']])."></td>
					</tr>
				";
			}

			$str = "SELECT DISTINCT(periode) FROM ".$dbname.".setup_periodeakuntansi ORDER BY periode DESC";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);

			$optPeriode = "";
			while ($bar = $res->fetch()) {
				$optPeriode .= "<option value='".$bar['periode']."'>".$bar['periode']."</option>";
			}

			$optKegiatan = $optTipeDok = $optTipeLap = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			
			$str = "
				SELECT DISTINCT(t1.tipedok), t2.nama_tipe AS nama_tipe
				FROM ".$dbname.".bi_map_transaksi t1 
				LEFT JOIN ".$dbname.".bi_5tipedok t2 ON t1.tipedok = t2.id_tipedok
				WHERE 1 = 1 ".forKebunAll($kbnarr, 't1.kodeorg', 'in')
			;
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optTipeDok .= "<option value='".$bar['tipedok']."'>".$bar['nama_tipe']."</option>";
			}

			$str = "
				SELECT DISTINCT(idlap), namalaporan
				FROM ".$dbname.".bi_5laporan
				WHERE tipe = 'performance'
				ORDER BY namalaporan
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optTipeLap .= "<option value='".$bar['idlap']."'>".$bar['namalaporan']."</option>";
			}

			$optNoAkun = '';
			$optNoAkun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str = "SELECT DISTINCT(noakun) AS noakun FROM ".$dbname.".bi_5siklusht ORDER BY noakun ASC";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optNamaAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='".$bar['noakun']."'");
				if (isset($optNamaAkun[$bar['noakun']])) {
					$optNoAkun .= "<option value='".$bar['noakun']."'>".$bar['noakun']." ".$optNamaAkun[$bar['noakun']]."</option>";
				}
			}

			$optsik = '';

			//Get data karyawan tracking
			$getApi = new getContentAPI;

			if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
				// Jika URI yang array [1] panjang string <= 7, Maka munculkan
				if (strlen($expri[1]) <= 7) {
					$url = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/access_token/api_key";
					$urlData = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/module/dashboard/traffic_user/send";
				} else {
					$url = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/access_token/api_key";
					$urlData = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/module/dashboard/traffic_user/send";
				}
			} else {
				// Jika Server local / localhost maka munculkan URL localhost
				$url = $urlocal."palmaprima/mobile/index.php/api/access_token/api_key";
				$urlData = $urlocal."palmaprima/mobile/index.php/api/module/dashboard/traffic_user/send";
			}

			//"?tanggal=".date("Y-m-d");
			$getApi->init($url, $options);

			// $paramData = array(
			// 	'tanggal' => date("Y-m-d")
			// );
			$getData = $getApi->get($urlData);
			$optKaryawan = "";
			// var_dump($getData);

			$str = "
				SELECT b.namauser AS username, b.karyawanid, c.namakaryawan, c.nik
				FROM ".$dbname.".user b
				LEFT JOIN ".$dbname.".datakaryawan c ON b.karyawanid=c.karyawanid
				WHERE 1 = 1 ".forKebunAll($kbnarr, 'c.lokasitugas', 'in')." ORDER BY c.namakaryawan ASC
			";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$numrows = $res->rowCount();
			$res->setFetchMode(PDO::FETCH_ASSOC);
			
			$listkar = array();
			if ($numrows >= 1) {
				while ($bar = $res->fetch()) {
					$listkar[$bar['username']] = $bar['username']." - ".$bar['namakaryawan'];
				}
			}
			
			$optKaryawan = "<option value='0'>".$_SESSION['lang']['pilihdata']."</option>";
			
			if (count($getData->response['result']) > 0) {
				foreach ($getData->response['result'] as $apiData) {
					if (isset($listkar[$apiData['namauser']])) {
						$optKaryawan .= "<option value='".$apiData['namauser']."'>".$listkar[$apiData['namauser']]."</option>";
					}
				}
			}

			//Get Filter Informasi blok
			$optfilterblok = "";
			$optfilterblok = "
				<option value=''>".$_SESSION['lang']['pilihdata']."</option>
				<option value='1'>".$_SESSION['lang']['tahuntanam']."</option>
				<option value='2'>".$_SESSION['lang']['statusblok']."</option>
				<option value='3'>Topografi</option>
				<option value='4'>".$_SESSION['lang']['jenisbibit']."</option>
				<option value='5'>".$_SESSION['lang']['intiplasma']."</option>
			";

			$result3 = "";
			$result3 .= "
				<hr>
				<table width=100%>
					<tr>
						<td style='text-align:center'>
							<select id='chkdetail' onchange=\"getChkDetail();\">
								<option value=''>".$_SESSION['lang']['pilihdata']."</option>
								<!--<option value='activitymonitoring'>Activity Monitoring</option>-->
								<option value='performance'>Performance</option>
								<option value='siklus'>".$_SESSION['lang']['siklus']."</option>
								<option value='tracking'>Tracking</option>
								<option value='informasiblok'>".$_SESSION['lang']['informasi']." ".$_SESSION['lang']['blok']."</option>
							</select>
						</td>
					</tr>
					<!--<tr>
						<td>
							<input type=radio name=chk id=chkKegiatan onclick=\"checkChkTipe();\" value='0' checked>&nbsp;Kegiatan
						</td>
						<td style='padding-left:10px;'>
							<input type=radio name=chk id=chkLaporan onclick=\"checkChkTipe();\" value='1'>&nbsp;Laporan
						</td>
					</tr>-->
				</table>
				<hr>
				<div id='divChkKegiatan' style='display:none;'>
					<table>
						<tr>
							<td>".$_SESSION['lang']['periode']."</td>
							<td>:</td>
							<td>
								<select id='periodeawal' onchange=\"getdetailkegiatan();\">".$optPeriode."</select>
							</td>
							<td>".$_SESSION['lang']['sd']."</td>
							<td>
								<select id='periodeakhir' onchange=\"getdetailkegiatan();\">".$optPeriode."</select>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['tipedokumen']."</td>
							<td>:</td>
							<td colspan=3>
								<select id='detailtipedokumen' onchange=\"getdetailkegiatan();\" style='max-width:120px;'>".$optTipeDok."</select>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kegiatan']."</td>
							<td>:</td>
							<td colspan=3>
								<select id='detailkegiatan' onchange='clearAMSvgDetail();' style='max-width:120px'>".$optKegiatan."</select>
							</td>
						</tr>
						<tr>
							<td colspan=2></td>
							<td colspan=3>
								<button class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
							</td>
						</tr>
					</table>
				</div>
				<div id='divChkLaporan' style='display:none;'>
					<table>
						<tr>
							<td>".$_SESSION['lang']['periode']."</td>
							<td>:</td>
							<td>
								<select id='periodeawal2' onchange=\"clearPFLaporan();\" >".$optPeriode."</select>
							</td>
							<td>".$_SESSION['lang']['sd']."</td>
							<td>
								<select id='periodeakhir2' onchange=\"clearPFLaporan();\" >".$optPeriode."</select>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['laporan']."</td>
							<td>:</td>
							<td colspan=3>
								<select id='detaillaporan2' onchange=\"getnamafile();\" style='max-width:185px;'>".$optTipeLap."</select>
								<input type=hidden id=namafile2 value=''>
							</td>
						</tr>
						<tr>
							<td colspan=2></td>
							<td colspan=3><button class=mybutton onclick=preview2()>".$_SESSION['lang']['preview']."</button></td>
						</tr>
					</table>
				</div>
				<div id='divChkSiklus' style='display:none;'>
					<table>
						<tr>
							<td>".$_SESSION['lang']['periode']."</td>
							<td>:</td>
							<td>
								<select id='periodeawal3' onchange=clearPFLaporan()>".$optPeriode."</select>
							</td>
							<td>".$_SESSION['lang']['sd']."</td>
							<td>
								<select id='periodeakhir3' onchange=clearPFLaporan()>".$optPeriode."</select>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['noakun']."</td>
							<td>:</td>
							<td colspan=3>
								<select id='noakun3' style='max-width:185px;' onchange=getkegiatan()>".$optNoAkun."</select>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kegiatan']."</td>
							<td>:</td>
							<td colspan=3>
								<select id='detailkegiatan3' style='max-width:185px;' onchange=getidsiklus()>".$optsik."</select>
								<input type=hidden id=namafile3 value='bi_map_siklus.php'>
								<input type=hidden id=detaillaporan3 value=''>
							</td>
						</tr>
						<tr>
							<td colspan=2></td>
							<td colspan=3><button class=mybutton onclick=preview3()>".$_SESSION['lang']['preview']."</button></td>
						</tr>
					</table>
				</div>
				<div id='divChkTracking' style='display:none;'>
					<table>
						<tr style='display:none;'>
							<td>".$_SESSION['lang']['tipe']."</td>
							<td>:</td>
							<td>
								<input type='radio' name='tipetracking' id='realtime' value='realtime' onclick='changeTipeTracking()' style='display:none;' />
								<input type='radio' name='tipetracking' id='history' value='history' onclick='changeTipeTracking()' checked/>
								History
							</td>
						</tr>
						<tr id='tanggaltracking'>
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td>:</td>
							<td>
								<input id='tanggalhistory' class='myinputtext' onkeypress='return tanpa_kutip(event)' style='width:60px' readonly='readonly' onmousemove='setCalendar(this.id)' type='text' value='".date('d-m-Y')."'>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['namakaryawan']."</td>
							<td>:</td>
							<td>
								<select id='karyawanid4' style='max-width:180px;' onchange='clearTracking()'>".$optKaryawan."</select>
							</td>
						</tr>
						<tr>
							<td colspan=2></td>
							<td>
								<button class=mybutton onclick=showDataTracking()>Tracking</button>
							</td>
						</tr>
					</table>
				</div>
				<div id='divChkInformasiBlok' style='display:none;'>
					<table>
						<tr>
							<td>".$_SESSION['lang']['searchdata']."</td>
							<td>:</td>
							<td>
								<select id='filterblok' onchange='clearChkDetail()'>".$optfilterblok."</select>
							</td>
						</tr>
						<tr>
							<td colspan=2></td>
							<td colspan=3>
								<button class=mybutton onclick=preview4()>".$_SESSION['lang']['preview']."</button>
							</td>
						</tr>
					</table>
				</div>
				<div id='divLegend' style='display:none;padding-top:5px;'></div>
			";

			// echo $result."####".$result2."####".$result3."####".$pointx."####".$pointy;
			echo json_encode([$result, $result2, $result3, $pointx, $pointy]);

			break;
		case 'getdetailkegiatan':
			$optKegiatan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			$str = "SELECT DISTINCT(kodekegiatan) FROM " . $dbname . ".bi_map_transaksi WHERE kodeorg = '" . $kebun . "' AND (periode BETWEEN '" . $periodeawal . "' AND '" . $periodeakhir . "') AND tipedok = '" . $detailtipedokumen . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optNamaKegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $bar['kodekegiatan'] . "'");
				$optKegiatan .= "<option value='" . $bar['kodekegiatan'] . "'>" . $optNamaKegiatan[$bar['kodekegiatan']] . "</option>";
			}
			echo $optKegiatan;
			break;

		case 'showinfosvg':
			$result = "";
			$result .= "
				<div>
					<table id='tblInformasi'>
						<tr>
							<td>
			";

			if ($tipesvg == 0) {
				$str = "SELECT * FROM ".$dbname.".bi_map_pt WHERE idsvg = '".$idsvg."'";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$expKtr = explode('##', $bar['keterangan']);

				$str = "SELECT * FROM ".$dbname.".setup_blok WHERE indukblok = '".$expKtr[0]."'";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$blokchild = array();
				while ($bar = $res->fetch()) {
					$blokchild[] = $bar;
				}

				// $bar = $res->fetch();
				// $sttBlok = $bar['statusblok'];
				// $thnTanam = $bar['tahuntanam'];
				// $intiplasma = ($bar['intiplasma']=='P' ? 'Plasma' : 'Inti');
				// $jnsBibit = $bar['jenisbibit'];
				// $topgra = $bar['topografi'];
				// $luasPlant = $bar['luasareaproduktif'];
				// $luasUnplant = $bar['luasareanonproduktif'];
				// $jlhPokok = $bar['jumlahpokok'];
				// $sPH = @($jlhPokok/$luasPlant);

				##manager
				$str = "
					SELECT *
					FROM ".$dbname.".datakaryawan
					WHERE lokasitugas = '".substr($expKtr[0], 0, 4)."' AND kodejabatan = '49' AND kodegolongan = '5' AND tanggalkeluar = '0000-00-00'
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$manager = $bar['namakaryawan'];

				##asisten
				$str = "
					SELECT *
					FROM ".$dbname.".datakaryawan
					WHERE lokasitugas = '".substr($expKtr[0], 0, 4)."' AND kodejabatan = '50' AND kodegolongan = '24' AND subbagian = '".substr($expKtr[0], 0, 6)."' AND tanggalkeluar = '0000-00-00'
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$asisten = $bar['namakaryawan'];

				$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

				$result .= "
					<table>
						<tr>
							<td style='vertical-align:top'>".$_SESSION['lang']['perusahaan']."</td>
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".$optNmOrg[$kodept]."</td>
						</tr>
						<tr>
							<td style='vertical-align:top'>Induk ".$_SESSION['lang']['blok']."</td>
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".$expKtr[0]."</td>
						</tr>
					</table>
					<hr style='margin-bottom:5px;border-top: 1px solid red;'>
				";

				$frm[0] .= "
					<table>
						<tr>
							<td style='vertical-align:top'>".$_SESSION['lang']['kebun']."</td>
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".$optNmOrg[substr($expKtr[0], 0, 4)]."</td>
						</tr>
						<tr>
							<td style='vertical-align:top'>".$_SESSION['lang']['divisi']."</td>
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".substr($expKtr[0], 0, 6)."</td>
						</tr>
						<tr>
							<td style='vertical-align:top'>Induk ".$_SESSION['lang']['blok']."</td>
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".$expKtr[0]."</td>
						</tr>
				";

				if (count($blokchild) > 0) {
					foreach ($blokchild as $k => $v) {
						// $frm[0] .= "
						// 	<tr>
						// 		<td colspan='3'>
						// 			<hr style='margin-bottom:5px;border-top: 1px solid gray;'>
						// 		</td>
						// 	</tr>
						// 	<tr>
						// 		<td style='vertical-align:top'>".$_SESSION['lang']['blok']."</td>
						// 		<td style='vertical-align:top'>:</td>
						// 		<td style='vertical-align:top'>".$optNmOrg[$v['kodeorg']]."</td>
						// 	</tr>
						// 	<tr>
						// 		<td style='vertical-align:top'>".$_SESSION['lang']['statusblok']."</td>
						// 		<td style='vertical-align:top'>:</td>
						// 		<td style='vertical-align:top'>".$v['statusblok']."</td>
						// 	</tr>
						// 	<tr>
						// 		<td style='vertical-align:top'>".$_SESSION['lang']['tahuntanam']."</td>
						// 		<td style='vertical-align:top'>:</td>
						// 		<td style='vertical-align:top'>".$v['tahuntanam']."</td>
						// 	</tr>
						// 	<tr>
						// 		<td style='vertical-align:top'>".$_SESSION['lang']['intiplasma']."</td>
						// 		<td style='vertical-align:top'>:</td>
						// 		<td style='vertical-align:top'>".($v['intiplasma'] == 'P' ? 'Plasma' : 'Inti')."</td>
						// 	</tr>
						// 	<tr>
						// 		<td style='vertical-align:top'>".$_SESSION['lang']['jenisbibit']."</td>
						// 		<td style='vertical-align:top'>:</td>
						// 		<td style='vertical-align:top'>".$v['jenisbibit']."</td>
						// 	</tr>
						// 	<tr>
						// 		<td style='vertical-align:top'>".$_SESSION['lang']['topografi']."</td>
						// 		<td style='vertical-align:top'>:</td>
						// 		<td style='vertical-align:top'>".$v['topografi']."</td>
						// 	</tr>
						// 	<tr>
						// 		<td style='vertical-align:top'>".$_SESSION['lang']['luasareaproduktif']." (Ha)</td>
						// 		<td style='vertical-align:top'>:</td>
						// 		<td style='vertical-align:top'>".number_format($v['luasareaproduktif'])."</td>
						// 	</tr>
						// 	<tr>
						// 		<td style='vertical-align:top'>".$_SESSION['lang']['luasareanonproduktif']." (Ha)</td>
						// 		<td style='vertical-align:top'>:</td>
						// 		<td style='vertical-align:top'>".number_format($v['luasareanonproduktif'])."</td>
						// 	</tr>
						// 	<tr>
						// 		<td style='vertical-align:top'>".$_SESSION['lang']['jmlhpokok']."</td>
						// 		<td style='vertical-align:top'>:</td>
						// 		<td style='vertical-align:top'>".number_format($v['jumlahpokok'])."</td>
						// 	</tr>
						// 	<tr>
						// 		<td style='vertical-align:top'>".$_SESSION['lang']['sph']."</td>
						// 		<td style='vertical-align:top'>:</td>
						// 		<!-- <td style='vertical-align:top'>
						// 			".number_format((((float)$bar['luasareaproduktif'] <= 0) ? 0 : ((int)$bar['jumlahpokok'] / (float)$bar['luasareaproduktif'])), 2)."
						// 		</td> -->
								
						// 		<td style='vertical-align:top'>
						// 			".($bar['luasareaproduktif'] <= 0 ? 'Yes' : 'No')."
						// 		</td>
						// 	</tr>
						// ";

						// $sph = ((int)$bar['jumlahpokok']/(double)$bar['luasareaproduktif']);
					}
				}

				$frm[0] .= "</table>";

				/*$frm[0] .= "<table>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['kebun']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".$optNmOrg[$kebun]."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['divisi']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".substr($expKtr[0],0,6)."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['blok']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".$optNmOrg[$expKtr[0]]."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['statusblok']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".$sttBlok."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['tahuntanam']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".$thnTanam."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['intiplasma']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".$intiplasma."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['jenisbibit']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".$jnsBibit."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['topografi']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".$topgra."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['luasareaproduktif']." (Ha)</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".number_format($luasareaproduktif)."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['luasareanonproduktif']." (Ha)</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".number_format($luasareanonproduktif)."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['jmlhpokok']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".number_format($jlhPokok)."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['sph']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".number_format($sPH,2)."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['asisten']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".$asisten."</td>
					</tr>
					<tr>
						<td style='vertical-align:top'>".$_SESSION['lang']['manager']."</td>
						<td style='vertical-align:top'>:</td>
						<td style='vertical-align:top'>".$manager."</td>
					</tr>
				</table>";*/

				$frm[1] .= 'form 2';

				$hfrm[0] = $_SESSION['lang']['detail'];
				$hfrm[1] = $_SESSION['lang']['produksi'];

				$result .= drawTabBI('FRM', $hfrm, $frm, 120, '');
			} else if ($tipesvg == 1) {
				$str = "SELECT * FROM ".$dbname.".bi_map_pt WHERE idsvg = '".$idsvg."'";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$expKtr = explode('##', $bar['keterangan']);

				$str = "SELECT * FROM ".$dbname.".setup_blok WHERE indukblok = '".$expKtr[0]."'";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$blokchild = array();
				$bloklist = array();
				while ($bar = $res->fetch()) {
					$blokchild[] = $bar;
					$bloklist[] = $bar['kodeorg'];
				}

				// $bar = $res->fetch();
				// $sttBlok = $bar['statusblok'];
				// $thnTanam = $bar['tahuntanam'];
				// $intiplasma = ($bar['intiplasma']=='P' ? 'Plasma' : 'Inti');
				// $jnsBibit = $bar['jenisbibit'];
				// $topgra = $bar['topografi'];
				// $luasPlant = $bar['luasareaproduktif'];
				// $luasUnplant = $bar['luasareanonproduktif'];
				// $jlhPokok = $bar['jumlahpokok'];
				// $sPH = @($jlhPokok/$luasPlant);

				##manager
				$str = "
					SELECT *
					FROM ".$dbname.".datakaryawan
					WHERE lokasitugas = '".substr($expKtr[0], 0, 4)."' AND kodejabatan = '49' AND tanggalkeluar = '0000-00-00'
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$manager = $bar['namakaryawan'];

				##asisten
				$str = "
					SELECT *
					FROM ".$dbname.".datakaryawan
					WHERE lokasitugas = '".substr($expKtr[0], 0, 4)."' AND kodejabatan = '50' AND subbagian = '".substr($expKtr[0], 0, 6)."' AND tanggalkeluar = '0000-00-00'
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$asisten = $bar['namakaryawan'];

				$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

				$result .= "
					<table>
						<tr>
							<td style='vertical-align:top'>".$_SESSION['lang']['perusahaan']."</td>
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".$optNmOrg[$kodept]."</td>
						</tr>
						<tr>
							<td style='vertical-align:top'>Induk ".$_SESSION['lang']['blok']."</td>
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".$expKtr[0]."</td>
						</tr>
					</table>
					<hr style='margin-bottom:5px;border-top: 1px solid red;'>
				";
				
				$frm[0] .= "
					<table>
						<tr>
							<td style='vertical-align:top'>".$_SESSION['lang']['kebun']."</td>
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".$optNmOrg[substr($expKtr[0], 0, 4)]."</td>
						</tr>
						<tr>
							<td style='vertical-align:top'>".$_SESSION['lang']['divisi']."</td>
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".substr($expKtr[0], 0, 6)."</td>
						</tr>
						<tr>
							<td style='vertical-align:top'>Induk ".$_SESSION['lang']['blok']."</td>
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".$expKtr[0]."</td>
						</tr>
				";
				
				if (count($blokchild) > 0) {
					foreach ($blokchild as $k => $v) {
						$frm[0] .= "
							<tr>
								<td colspan='3'> <hr style='margin-bottom:5px;border-top: 1px solid gray;'></td>
							</tr>
							<tr>
								<td style='vertical-align:top'>".$_SESSION['lang']['blok']."</td>
								<td style='vertical-align:top'>:</td>
								<td style='vertical-align:top'>".$optNmOrg[$v['kodeorg']]."</td>
							</tr>
							<tr>
								<td style='vertical-align:top'>".$_SESSION['lang']['statusblok']."</td>
								<td style='vertical-align:top'>:</td>
								<td style='vertical-align:top'>".$v['statusblok']."</td>
							</tr>
							<tr>
								<td style='vertical-align:top'>".$_SESSION['lang']['tahuntanam']."</td>
								<td style='vertical-align:top'>:</td>
								<td style='vertical-align:top'>".$v['tahuntanam']."</td>
							</tr>
							<tr>
								<td style='vertical-align:top'>".$_SESSION['lang']['intiplasma']."</td>
								<td style='vertical-align:top'>:</td>
								<td style='vertical-align:top'>".($v['intiplasma'] == 'P' ? 'Plasma' : 'Inti')."</td>
							</tr>
							<tr>
								<td style='vertical-align:top'>".$_SESSION['lang']['jenisbibit']."</td>
								<td style='vertical-align:top'>:</td>
								<td style='vertical-align:top'>".$v['jenisbibit']."</td>
							</tr>
							<tr>
								<td style='vertical-align:top'>".$_SESSION['lang']['topografi']."</td>
								<td style='vertical-align:top'>:</td>
								<td style='vertical-align:top'>".$v['topografi']."</td>
							</tr>
							<tr>
								<td style='vertical-align:top'>".$_SESSION['lang']['luasareaproduktif']." (Ha)</td>
								<td style='vertical-align:top'>:</td>
								<td style='vertical-align:top'>".number_format($v['luasareaproduktif'])."</td>
							</tr>
							<tr>
								<td style='vertical-align:top'>".$_SESSION['lang']['luasareanonproduktif']." (Ha)</td>
								<td style='vertical-align:top'>:</td>
								<td style='vertical-align:top'>".number_format($v['luasareanonproduktif'])."</td>
							</tr>
							<tr>
								<td style='vertical-align:top'>".$_SESSION['lang']['jmlhpokok']."</td>
								<td style='vertical-align:top'>:</td>
								<td style='vertical-align:top'>".number_format($v['jumlahpokok'])."</td>
							</tr>
							<tr>
								<td style='vertical-align:top'>".$_SESSION['lang']['sph']."</td>
								<td style='vertical-align:top'>:</td>
								<td style='vertical-align:top'>
									".($v['luasareaproduktif'] <= 0 ? 0 : number_format($v['jumlahpokok'] / $v['luasareaproduktif'], 2))."
								</td>
							</tr>
						";

						// $sph = ((int)$bar['jumlahpokok']/(double)$bar['luasareaproduktif']);
					}
				}

				$frm[0] .= "</table>";

				// $frm[0] .= "<table>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['kebun']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".$optNmOrg[substr($expKtr[0],0,4)]."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['divisi']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".substr($expKtr[0],0,6)."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['blok']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".$optNmOrg[$expKtr[0]]."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['statusblok']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".$sttBlok."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['tahuntanam']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".$thnTanam."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['intiplasma']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".$intiplasma."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['jenisbibit']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".$jnsBibit."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['topografi']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".$topgra."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['luasareaproduktif']." (Ha)</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".number_format($luasPlant)."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['luasareanonproduktif']." (Ha)</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".number_format($luasUnplant)."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['jmlhpokok']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".number_format($jlhPokok)."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['sph']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".number_format($sPH,2)."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['asisten']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".$asisten."</td>
				// 	</tr>
				// 	<tr>
				// 		<td style='vertical-align:top'>".$_SESSION['lang']['manager']."</td>
				// 		<td style='vertical-align:top'>:</td>
				// 		<td style='vertical-align:top'>".$manager."</td>
				// 	</tr>
				// </table>";

				$thnSkrg = date("Y");
				$thnLalu = date("Y") - 1;

				$arrBulan = array();
				for ($i = 0; $i < 12; $i++) {
					$val = date("M Y", strtotime("-".$i." month"));
					$key = date("Y-m", strtotime("-".$i." month"));

					$arrBulan[$key] = $val;
				}

				$arrReal = array();
				
				$str = "
					SELECT SUM(kgwb) AS kg, LEFT(tanggal, 7) AS tanggal
					FROM ".$dbname.".kebun_spb_vw2
					WHERE blok = '".$expKtr[0]."' AND LEFT(tanggal, 4) IN ('".$thnSkrg."', '".$thnLalu."')
					GROUP BY LEFT(tanggal, 7)
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$arrReal[$bar['tanggal']] = @($bar['kg'] / 1000);
				}

				$arrAngg = array();

				$str = "
					SELECT SUM(kg01) AS kg01, SUM(kg02) AS kg02, SUM(kg03) AS kg03, SUM(kg04) AS kg04, SUM(kg05) AS kg05, SUM(kg06) AS kg06, SUM(kg07) AS kg07, SUM(kg08) AS kg08, SUM(kg09) AS kg09, SUM(kg10) AS kg10, SUM(kg11) AS kg11, SUM(kg12) AS kg12, tahunbudget AS tahun
					FROM ".$dbname.".bgt_produksi_kbn_kg_vw
					WHERE kodeblok IN ('".implode("', '", $bloklist)."') AND tahunbudget IN ('".$thnSkrg."','".$thnLalu."')
					GROUP BY tahunbudget
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$arrAngg[$bar['tahun']."-01"] = @($bar['kg01'] / 1000);
					$arrAngg[$bar['tahun']."-02"] = @($bar['kg02'] / 1000);
					$arrAngg[$bar['tahun']."-03"] = @($bar['kg03'] / 1000);
					$arrAngg[$bar['tahun']."-04"] = @($bar['kg04'] / 1000);
					$arrAngg[$bar['tahun']."-05"] = @($bar['kg05'] / 1000);
					$arrAngg[$bar['tahun']."-06"] = @($bar['kg06'] / 1000);
					$arrAngg[$bar['tahun']."-07"] = @($bar['kg07'] / 1000);
					$arrAngg[$bar['tahun']."-08"] = @($bar['kg08'] / 1000);
					$arrAngg[$bar['tahun']."-09"] = @($bar['kg09'] / 1000);
					$arrAngg[$bar['tahun']."-10"] = @($bar['kg10'] / 1000);
					$arrAngg[$bar['tahun']."-11"] = @($bar['kg11'] / 1000);
					$arrAngg[$bar['tahun']."-12"] = @($bar['kg12'] / 1000);
				}

				//GRAPH
				$maxReal = 0;
				$maxAngg = 0;
				$maxAll = 0;

				if (!empty($arrReal) || !empty($arrAngg)) {
					$maxReal = @round(max($arrReal));
					$maxAngg = @round(max($arrAngg));
					$maxAll = @max($maxReal, $maxAngg);
				}

				$frm[1] .= "
					<span style='font-size:85%'>
						<i>".$_SESSION['lang']['historiproduksi']." 12 ".$_SESSION['lang']['bulan']." (ton)</i>
					</span>
					<table width=100% cellpadding=0 cellspacing=0>
						<tr>
							<td colspan=2></td>
							<td style='border-left:1px solid rgb(30, 88, 150);padding-left:5px;'></td>
							<td style='text-align:center'><i>".$_SESSION['lang']['real']."</i></td>
							<td style='text-align:center'><i>".$_SESSION['lang']['anggaran']."</i></td>
						</tr>
				";
				
				foreach ($arrBulan as $key => $val) {
					$widthReal = @((100 / $maxAll) * round($arrReal[$key]));
					$widthAngg = @((100 / $maxAll) * round($arrAngg[$key]));

					$frm[1] .= "
						<tr>
							<td rowspan=2 style='width:80px;'>".$val."</td>
							<td style='width:100px;font-size:50%;padding-right:1%'>
								<table cellpadding=0 cellspacing=0 style='width:".$widthReal."%'>
									<tr>
										<td style='background-color:blue'>&nbsp;</td>
									</tr>
								</table>
							</td>
							<td rowspan=2 style='border-left:1px solid rgb(30, 88, 150);padding-left:5px;width:80px;'>".$val."</td>
							<td rowspan=2 style='text-align:right;color:blue'>".number_format($arrReal[$key])."</td>
							<td rowspan=2 style='text-align:right;color:orange'>".number_format($arrAngg[$key])."</td>
						</tr>
						<tr>
							<td style='width:100px;font-size:50%;padding-right:1%;padding-bottom:2%;'>
								<table cellpadding=0 cellspacing=0 style='width:".$widthAngg."%;'>
									<tr>
										<td style='background-color:orange;'>&nbsp;</td>
									</tr>
								</table>
							</td>
						</tr>
					";
				}

				$frm[1] .= "</table>";

				if ($showstatusblok != 0) {
					$frm[2] .= $detailreport;
					$hfrm[2] = $_SESSION['lang']['preview'];
				}

				$hfrm[0] = $_SESSION['lang']['detail'];
				$hfrm[1] = $_SESSION['lang']['produksi'];

				$result .= drawTabBI('FRM', $hfrm, $frm, 120, '');
			} else {
				$str = "SELECT * FROM ".$dbname.".bi_map_transaksi WHERE idsvg = '".$idsvg."'";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();

				$optOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi = '".$bar['kodeorg']."'");
				$optTipeDok = makeOption($dbname, 'bi_5tipedok', 'id_tipedok,nama_tipe', "id_tipedok = '".$bar['tipedok']."'");
				$optKegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan = '".$bar['kodekegiatan']."'");

				$str2 = "SELECT * FROM ".$dbname.".bi_5tipedok WHERE id_tipedok='".$bar['tipedok']."'";
				try {
					$res2 = $owlPDO->query($str2);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res2->setFetchMode(PDO::FETCH_ASSOC);
				$bar2 = $res2->fetch();

				$tTabel = $bar2['tabel'];
				$tKolom1 = $bar2['nodok'];
				$tKolom2 = $bar2['jnskgtn'];
				$tKolom3 = $bar2['kodeorg'];
				$tKolom4 = $bar2['periode'];

				$vPeriode = $bar['periode'];
				$vKegiatan = $optKegiatan[$bar['kodekegiatan']];

				$result .= "
					<table cellpading=1 cellspacing=1 border=0 class=sortable>
						<thead style='background:black'>
							<tr align=center style='background:black'>
								<td>".$_SESSION['lang']['notransaksi']."</td>
								<td>".$_SESSION['lang']['kodeblok']."</td>
								<td>".$_SESSION['lang']['kegiatan']."</td>
								<td>".$_SESSION['lang']['periode']."</td>
								<td>".$_SESSION['lang']['tanggal']."</td>
								<td>".$_SESSION['lang']['hasilkerjad']."</td>
								<td>".$_SESSION['lang']['hkrealisasi']."</td>
								<td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['realisasi']."</td>
								<td>Photo</td>
							</tr>
						</thead>
						<tbody>
				";

				//Get List Document
				$str = "
					SELECT DISTINCT(nodok) AS nodok
					FROM ".$dbname.".bi_map_transaksi_dok
					WHERE idsvg = '".$idsvg."'
					ORDER BY nodok asc
				";
				try {
					$res = $owlPDO->query($str);
				} catch (PDOException $e) {
					die(print "Gagal: ". $e->getMessage());
				}
				$res->setFetchMode(PDO::FETCH_ASSOC);

				$no = 0;
				while ($bar = $res->fetch()) {
					$str3 = "
						SELECT ".$tKolom1.",".$tKolom2.",".$tKolom3.",".$tKolom4."
						FROM ".$dbname.".".$tTabel."
						WHERE ".$tKolom1." = '".$bar['nodok']."'
					";
					try {
						$res3 = $owlPDO->query($str3);
					} catch (PDOException $e) {
						die(print "Gagal: ". $e->getMessage());
					}
					$res3->setFetchMode(PDO::FETCH_ASSOC);
					$bar3 = $res3->fetch();

					$str2 = "
						SELECT *
						FROM ".$dbname.".bi_map_transaksi_dok_photo
						WHERE nodok = '".$bar['nodok']."'
						ORDER BY nourut ASC
					";
					// $numrows = owlBaris($str2);
					try {
						$res2 = $owlPDO->query($str2);
					} catch (PDOException $e) {
						die(print "Gagal: ". $e->getMessage());
					}
					$res2->setFetchMode(PDO::FETCH_ASSOC);

					$numrows = $res2->rowCount();
					$no = $no + 1;
					
					$result .= "
						<tr class=rowcontent align=center>
							<td style='vertical-align:top'>".$bar['nodok']."</td>
							<td style='vertical-align:top'>".$bar3[$tKolom3]."</td>
							<td style='vertical-align:top'>".$vKegiatan."</td>
							<td style='vertical-align:top'>".$vPeriode."</td>
							<td style='vertical-align:top'>".tanggalnormal(substr($bar3[$tKolom4], 0, 10))."</td>
							<td style='vertical-align:top'>-</td>
							<td style='vertical-align:top'>-</td>
							<td style='vertical-align:top'>-</td>
							<td style='vertical-align:top'>
					";

					if ($numrows <= 0) {
						$result .= "-";
					} else {
						$result .= "
							<table cellpading=1 cellspacing=1 border=0 class=sortable>
								<tr class=rowcontent>
						";
						
						while ($bar2 = $res2->fetch()) {
							$result .= "
								<td style='cursor:pointer;' onclick=\"isifile('".$bar2['namafile']."','event');\">
									<img src='../fileupload/photodok/".$bar2['namafile']."' style='width:50px;height:50px'>
								</td>
							";
						}

						$result .= "
								</tr>
							</table>
						";
					}

					$result .= "
							</td>
						</tr>
					";
				}

				$result .= "
						</tbody>
					</table>
				";
			}

			$result .= "
							</td>
						</tr>
					</table>
				</div>
			";

			echo $result;

			// $result .= "<div>";

			// if($tipesvg == '0'){
			// $str = "select * from ".$dbname.".bi_map_basic where idsvg = '".$idsvg."'";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $bar = $res->fetch();

			// $expKtr = explode('##',$bar['keterangan']);
			// $optTpPeta = makeOption($dbname,'bi_5tipepeta','id_tipepeta,keterangan',"id_tipepeta = '".$bar['tipepeta']."'");
			// $vTipePeta = $optTpPeta[$bar['tipepeta']];
			// $vNamaPeta = $bar['namapeta']." / ".$expKtr[0];
			// if($bar['tipepeta'] == $firstTipe){
			// $optNmProvinsi = makeOption($dbname,'provinsi','id,provinsi',"id = '".$bar['namapeta']."'");
			// $vNamaPeta = $optNmProvinsi[$bar['namapeta']]." / ".$expKtr[0];
			// }

			// $result .= "<table cellpading=1 cellspacing=1 border=0 class=sortable style='background:none'>
			// <tr>
			// <td>ID SVG</td>
			// <td>:</td>
			// <td>".$idsvg."</td>
			// </tr>
			// <tr>
			// <td>Kelompok Peta</td>
			// <td>:</td>
			// <td>Peta Dasar</td>
			// </tr>
			// <tr>
			// <td>Tipe Peta</td>
			// <td>:</td>
			// <td>".$vTipePeta." (".$bar['tipepeta'].")</td>
			// </tr>
			// <tr>
			// <td>Nama Peta</td>
			// <td>:</td>
			// <td>".$vNamaPeta."</td>
			// </tr>
			// </table>";
			// }else if($tipesvg == '1'){
			// $str = "select * from ".$dbname.".bi_map_pt where idsvg = '".$idsvg."'";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $bar = $res->fetch();

			// $expKtr = explode('##',$bar['keterangan']);
			// $optTpPeta = makeOption($dbname,'bi_5tipepeta','id_tipepeta,keterangan',"id_tipepeta = '".$bar['tipepeta']."'");
			// $vTipePeta = $optTpPeta[$bar['tipepeta']];
			// $vNamaPeta = $bar['namapeta']." / ".$expKtr[0];
			// if($bar['tipepeta'] == $firstTipe){
			// $optNmProvinsi = makeOption($dbname,'provinsi','id,provinsi',"id = '".$bar['namapeta']."'");
			// $vNamaPeta = $optNmProvinsi[$bar['namapeta']]." / ".$expKtr[0];
			// }

			// $result .= "<table cellpading=1 cellspacing=1 border=0 class=sortable style='background:none'>
			// <tr>
			// <td>ID SVG</td>
			// <td>:</td>
			// <td>".$idsvg."</td>
			// </tr>
			// <tr>
			// <td>Kelompok Peta</td>
			// <td>:</td>
			// <td>Peta PT</td>
			// </tr>
			// <tr>
			// <td>Tipe Peta</td>
			// <td>:</td>
			// <td>".$vTipePeta." (".$bar['tipepeta'].")</td>
			// </tr>
			// <tr>
			// <td>Nama Peta</td>
			// <td>:</td>
			// <td>".$vNamaPeta."</td>
			// </tr>
			// </table>";

			// $str="select * from ".$dbname.".setup_blok where kodeorg='".$expKtr[0]."'";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $bar = $res->fetch();			
			// $optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan',"topografi='".$bar['topografi']."'");

			// $detailreport2 = "<table cellpading=1 cellspacing=1 border=0 class=sortable>
			// <thead style='background:black'>
			// <tr align=center style='background:black'>
			// <td>".$_SESSION['lang']['statusblok']."</td>
			// <td>".$_SESSION['lang']['tahuntanam']."</td>
			// <td>".$_SESSION['lang']['intiplasma']."</td>
			// <td>".$_SESSION['lang']['jenisbibit']."</td>
			// <td>".$_SESSION['lang']['topografi']."</td>
			// <td>HA Planted</td>
			// <td>HA Unplanted</td>
			// <td>".$_SESSION['lang']['jumlahpokok']."</td>
			// <td>SPH</td>
			// </tr>
			// </thead>
			// <tbody>
			// <tr class=rowcontent align=center>
			// <td>".$bar['statusblok']."</td>
			// <td>".$bar['tahuntanam']."</td>
			// <td>".($bar['intiplasma']=='I' ? 'Inti' : ($bar['intiplasma']=='P') ? 'Plasma' : '')."</td>
			// <td>".$bar['jenisbibit']."</td>
			// <td>".$optTopografi[$bar['topografi']]."</td>
			// <td>".number_format($bar['luasareaproduktif'])."</td>
			// <td>".number_format($bar['luasareanonproduktif'])."</td>
			// <td>".number_format($bar['jumlahpokok'])."</td>
			// <td>".number_format(@($bar['jumlahpokok']/$bar['luasareaproduktif']),2)."</td>
			// </tr>
			// </tbody>
			// </table>";

			// $result .= "<div style='padding-top:5px;overflow:auto'><b>Detail : </b>".$detailreport2."".$detailreport."</div>";
			// }else if($tipesvg == '2'){
			// $str = "select * from ".$dbname.".bi_map_transaksi where idsvg = '".$idsvg."'";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);

			// $bar = $res->fetch();
			// $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi = '".$bar['kodeorg']."'");
			// $optTipeDok = makeOption($dbname,'bi_5tipedok','id_tipedok,nama_tipe',"id_tipedok = '".$bar['tipedok']."'");
			// $optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan = '".$bar['kodekegiatan']."'");

			// $str2="select * from ".$dbname.".bi_5tipedok where id_tipedok='".$bar['tipedok']."'";
			// $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			// $res2->setFetchMode(PDO::FETCH_ASSOC);
			// $bar2 = $res2->fetch();
			// $tTabel = $bar2['tabel'];
			// $tKolom1 = $bar2['nodok'];
			// $tKolom2 = $bar2['jnskgtn'];
			// $tKolom3 = $bar2['kodeorg'];
			// $tKolom4 = $bar2['periode'];

			// $vPeriode = $bar['periode'];
			// $vKegiatan = $optKegiatan[$bar['kodekegiatan']];

			// $result .= "<table cellpading=1 cellspacing=1 border=0 class=sortable style='background:none'>
			// <tr>
			// <td>ID SVG</td>
			// <td>:</td>
			// <td>".$idsvg."</td>
			// </tr>
			// <tr>
			// <td>Kelompok Peta</td>
			// <td>:</td>
			// <td>Peta Transaksi</td>
			// </tr>
			// <tr>
			// <td>".$_SESSION['lang']['unit']."</td>
			// <td>:</td>
			// <td>".$bar['kodeorg']."-".$optOrg[$bar['kodeorg']]."</td>
			// </tr>
			// <tr>
			// <td>Layer</td>
			// <td>:</td>
			// <td>Activity Monitoring / ".$optTipeDok[$bar['tipedok']]."</td>
			// </tr>
			// </table>";

			// $detailreport = "<table cellpading=1 cellspacing=1 border=0 class=sortable>
			// <thead style='background:black'>
			// <tr align=center style='background:black'>
			// <td>".$_SESSION['lang']['notransaksi']."</td>
			// <td>".$_SESSION['lang']['kodeblok']."</td>
			// <td>".$_SESSION['lang']['kegiatan']."</td>
			// <td>".$_SESSION['lang']['periode']."</td>
			// <td>".$_SESSION['lang']['tanggal']."</td>
			// <td>Hasil Kerja</td>
			// <td>HK Realisasi</td>
			// <td>Jumlah Realisasi</td>
			// <td>Photo</td>
			// </tr>
			// </thead>
			// <tbody>";

			// //Get List Document
			// $str = "select distinct(nodok) as nodok from ".$dbname.".bi_map_transaksi_dok where idsvg = '".$idsvg."' order by nodok asc";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $no = 0;
			// while($bar = $res->fetch()){
			// $str3 = "select ".$tKolom1.",".$tKolom2.",".$tKolom3.",".$tKolom4." from ".$dbname.".".$tTabel." where ".$tKolom1." = '".$bar['nodok']."'";
			// $res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
			// $res3->setFetchMode(PDO::FETCH_ASSOC);
			// $bar3 = $res3->fetch();

			// $str2 = "select * from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$bar['nodok']."' order by nourut asc";
			// // $numrows = owlBaris($str2);
			// $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			// $res2->setFetchMode(PDO::FETCH_ASSOC);
			// $numrows=$res2->rowCount();
			// $no = $no + 1;
			// $detailreport .= "<tr class=rowcontent align=center>
			// <td style='vertical-align:top'>".$bar['nodok']."</td>
			// <td style='vertical-align:top'>".$bar3[$tKolom3]."</td>
			// <td style='vertical-align:top'>".$vKegiatan."</td>
			// <td style='vertical-align:top'>".$vPeriode."</td>
			// <td style='vertical-align:top'>".tanggalnormal(substr($bar3[$tKolom4],0,10))."</td>
			// <td style='vertical-align:top'>-</td>
			// <td style='vertical-align:top'>-</td>
			// <td style='vertical-align:top'>-</td>
			// <td style='vertical-align:top'>";
			// if($numrows <= 0){
			// $detailreport .= "-";
			// }else{
			// $detailreport .= "<table cellpading=1 cellspacing=1 border=0 class=sortable>";
			// while($bar2 = $res2->fetch()){
			// $detailreport .= "<tr>
			// <td style='cursor:pointer;' onclick=\"parent.isifile('".$bar2['namafile']."','event');\"><u><font color=blue>".$bar2['namafile']."</td>
			// </tr>";
			// }
			// $detailreport .= "</table>";
			// }
			// $detailreport .= "</td>
			// </tr>";
			// }
			// $detailreport .= "</tbody>
			// </table><br>";

			// $result .= "<b>Detail : </b>".$detailreport;

			// }
			// $result .= "</div>";
			// echo $result;
			break;
		case 'isifile':
			$expNamafile = explode('.', $namafile);
			if ($expNamafile[1] == 'pdf') {
				echo "<embed src='../fileupload/photodok/".$namafile . "' width=780px height=370px>";
			} else {
				echo "<img src='../fileupload/photodok/" . $namafile . "'>";
			}
			break;

		case 'getnamafile':
			$str = "SELECT * FROM ".$dbname.".bi_5laporan where idlap = '".$detaillaporan2."'";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();

			echo $bar['namafile'];
			break;
		case 'getkegiatan':
			$optsik = '';

			$str = "select distinct(kegiatan) as kegiatan from ".$dbname.".bi_5siklusht where noakun = '".$noakun."' order by kegiatan asc";
			try {
				$res = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optNamaKegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='".$bar['kegiatan']."' and status='1'");
				if ($bar['kegiatan'] == '') {
					$optsik = "<option value=''>".$_SESSION['lang']['all']."</option>";
				} else {
					if (isset($optNamaKegiatan[$bar['kegiatan']])) {
						$optsik .= "<option value='".$bar['kegiatan']."'>".$bar['kegiatan']." ".$optNamaKegiatan[$bar['kegiatan']]."</option>";
					}
				}
			}

			echo $optsik;
			break;
		case 'getidsiklus':
			$str = "select idsiklus from " . $dbname . ".bi_5siklusht where noakun = '" . $noakun . "' and kegiatan='" . $detailkegiatan . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();

			echo $bar['idsiklus'];

			break;

		case 'showDataTracking':
			$getApi = new getContentAPI;

			if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
				// Jika URI yang array [1] panjang string <= 7, Maka munculkan
				if (strlen($expri[1]) <= 7) {
					$url = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/access_token/api_key";
					$urlData = $_SERVER['HTTP_ORIGIN']."/".$expri[1]."/mobile/index.php/api/module/dashboard/traffic_locations/send";
				} else {
					$url = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/access_token/api_key";
					$urlData = $_SERVER['HTTP_ORIGIN']."/mobile/index.php/api/module/dashboard/traffic_locations/send";
				}
			} else {
				// Jika Server local / localhost maka munculkan URL localhost
				$url = $urlocal."palmaprima/mobile/index.php/api/access_token/api_key";
				$urlData = $urlocal."palmaprima/mobile/index.php/api/module/dashboard/traffic_locations/send";
			}

			$getApi->init($url, $options);

			$paramData = array(
				'tanggal' => date("Y-m-d", strtotime($tanggalhistory)),
				'username' => $karyawanid,
				'tipe' => '1'
			);
			$getData = $getApi->post($urlData, $paramData);

			$result = "
				<filter id='location_fp' x='-50%' y='-150%' width='200%' height='200%'>
					<feImage xlink:href='images/fp-location.png' />
				</filter>
			";

			if ($getData->response['status'] == '200' && !empty($getData->response['result'])) {
				$response = array_shift($getData->response['result']);
				$count = 0;
				$coordinates = $response['coordinates'];
				foreach ($coordinates as $bar) {
					if ($count == 0) {
						$firstCoorX = $bar['lng'];
						$firstCoorY = ($bar['lat'] * (-1));
					}

					$coordinat[] = $bar['lng'].",".$bar['lat'] * (-1);
					$count++;
				}

				$joinArrCoor = implode(" ", $coordinat);
				// $result .= "<polyline points='".$joinArrCoor."' stroke='white' stroke-width='0.00010' stroke-linecap='butt' fill='none' stroke-linejoin='miter' title='' />";
				$result .= "<polyline points='".$joinArrCoor."' stroke='#a052f8' stroke-width='0.00008' stroke-linecap='butt' fill='none' stroke-linejoin='miter' title='' />";

				foreach ($coordinates as $bar) {
					$result .= "<circle cx='".($bar['lng'])."' stroke='white' stroke-width='0.00001' cy='".($bar['lat'] * (-1))."' r='0.00005' fill='red' onclick=\"showinfogps('tracker',event);\" />";
				}

				try {
					$radiusMax = 1;

					$owlPDO->exec("SET @long := false;");
					$owlPDO->exec("SET @lat := false;");
					$owlPDO->exec("SET @sn :='';");
					$owlPDO->exec("SET @distance :=null;");
					$owlPDO->exec("SET @radius :=null;");
					$owlPDO->exec("SET @num :=1;");
					$owlPDO->exec("SET @uom :=3959;");
					$owlPDO->exec("SET @maxrad :=".$radiusMax.";");
					$owlPDO->exec("SET @cm := 6;");

					// echo "SET @long := false;
					// SET @lat := false;
					// SET @sn :='';
					// SET @distance :=null;
					// SET @radius :=null;
					// SET @num :=1;
					// SET @uom :=3959;
					// SET @maxrad :=".$radiusMax.";
					// SET @cm := 6;";

					// $str = "
					// 	SELECT sn, lat_as as lat, long_as as `long`, FORMAT((ifnull(max(radius),0)),@cm) as radius, waktuupload, GROUP_CONCAT(Distinct scan_date) as punchtime FROM (
					// 		SELECT sn, scan_date, @distance:= FORMAT((@uom * acos( cos(radians(if(@lat=false,latitude,if(@sn != sn,latitude,@lat))) ) * cos(radians(latitude)) * cos( radians(longitude) - radians(if(@long=false,longitude,if(@sn != sn,longitude,@long)))) + sin(radians(if(@lat=false,latitude,if(@sn != sn,latitude,@lat)))) * sin(radians(latitude)))),@cm) as distance, waktuupload, @radius:= FORMAT(if(@distance>=@maxrad,0,@distance),@cm) as radius, if(@distance>=@maxrad,@num:=@num+1,if(@sn!=sn,@num:=1,@num)) as flag,latitude,longitude,if(@distance>=1 or @radius is null,@lat:=latitude,@lat) as lat_as, if(@distance>=1 or @distance is null,@long:=longitude,@long) as long_as,@sn:=sn as sn_as
					// 		FROM ".$dbname.".att_log 
					// 		where scan_date >= '".date('Y-m-d 00:00:00', strtotime($tanggalhistory))."' and scan_date < '".date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($tanggalhistory)))."' and latitude != 0 AND latitude is not null
					// 		ORDER BY sn,scan_date
					// 	) as t1
					// 	group by sn,flag
					// ";

					$str = "
						SELECT 
							sn,
							lat_as AS lat,
							long_as AS `long`,
							FORMAT(IFNULL(MAX(radius),0), 6) AS radius,
							MIN(waktuupload) AS waktuupload,
							GROUP_CONCAT(DISTINCT scan_date ORDER BY scan_date) AS punchtime
						FROM (
							SELECT 
								sn,
								scan_date,
								@new_sn := IF(@sn <> sn, 1, 0) AS new_sn,
								@dist_raw := 3959 * ACOS(
									COS(RADIANS(IF(@new_sn=1 OR @lat IS FALSE, latitude, @lat))) *
									COS(RADIANS(latitude)) *
									COS(RADIANS(longitude) - RADIANS(IF(@new_sn=1 OR @long IS FALSE, longitude, @long))) +
									SIN(RADIANS(IF(@new_sn=1 OR @lat IS FALSE, latitude, @lat))) *
									SIN(RADIANS(latitude))
								) AS dist_raw,
								@radius := IF(@dist_raw >= 1 OR @dist_raw IS NULL, 0, @dist_raw) AS radius,
								@num := IF(@new_sn=1, 1, IF(@dist_raw >= 1, @num+1, @num)) AS flag,
								@lat  := IF(@new_sn=1 OR @dist_raw >= 1 OR @lat IS FALSE,  latitude, @lat)  AS lat_as,
								@long := IF(@new_sn=1 OR @dist_raw >= 1 OR @long IS FALSE, longitude, @long) AS long_as,
								@sn := sn AS sn_as,
								waktuupload,
								latitude,
								longitude
							FROM ".$dbname.".att_log 
							where scan_date >= '".date('Y-m-d 00:00:00', strtotime($tanggalhistory))."' and scan_date < '".date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($tanggalhistory)))."' and latitude != 0 AND latitude is not null
							ORDER BY sn,scan_date
						) as t1
						group by sn,flag
					";
					// echo $str;
					$resabs = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$resabs->setFetchMode(PDO::FETCH_ASSOC);
					while ($barabs = $resabs->fetch()) {
						$result .= "
							<circle cx='".($barabs['long'])."' cy='".($barabs['lat'] * (-1))."' r='".(((float)$barabs['radius'] <= 0) ? ((float)$barabs['radius'] * (0.00008)) : "0.00008")."' fill='#5100ff45' />
							<circle filter='url(#location_fp)' cx='".($barabs['long'])."' cy='".($barabs['lat'] * (-1))."' r=\"0.00008\" onclick=\"showinfogps('fingerprint','".$barabs['sn'].",".$barabs['lat'].",".$barabs['long'].",".$radiusMax."',event)\" />
						";
					}
				} catch (Exception $e) {
					$str = $e->getMessage();
				} 
				echo $result."####".$firstCoorX."####".$firstCoorY;
			} else {
				// var_dump($getData->response);
				exit("warning : ".$_SESSION['lang']['datanotfound']);
			}
			break;
		case 'showDataTrackingDetail':
			$sn = checkPostGet('sn', '');
			$lat = checkPostGet('lat', '');
			$long = checkPostGet('long', '');
			$radiusMax = checkPostGet('maxrd', '');

			$dateStart = date('Y-m-d 00:00:00', strtotime($tanggalhistory));
			$dateMax = date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($tanggalhistory)));

			$owlPDO->exec("SET @uom :=3959;");
			$owlPDO->exec("SET @maxrad :=".$radiusMax.";");

			// echo "SET @uom :=3959; SET @maxrad :=".$radiusMax.";";

			$str = "
				SELECT pin as karyawanid, b.namakaryawan, group_concat(Distinct inoutmode) as inoutmode, MIN(scan_date) as waktu1, MAX(scan_date) as waktu2
				FROM ".$dbname.".`att_log`
				left join ".$dbname.".datakaryawan b on pin = b.karyawanid
				WHERE
					`sn` = '".$sn."'
					AND scan_date >= '".$dateStart."'
					AND scan_date < '".$dateMax."' 
					AND latitude != 0
					AND latitude is not null
					AND (
						(`latitude` = '".$lat."' AND `longitude` = '".$long."')
						OR (FORMAT(IFNULL((@uom * acos(cos(radians(".$lat.")) * cos(radians(latitude)) * cos( radians(longitude) - radians(".$long."))+sin(radians(".$lat.")) * sin(radians(latitude)))),0),6) < ".$radiusMax."))
					GROUP BY pin
					ORDER BY scan_date
			";
			// echo $str;
			try {
				$resabs = $owlPDO->query($str);
			} catch (PDOException $e) {
				die(print "Gagal: ". $e->getMessage());
			}
			$resabs->setFetchMode(PDO::FETCH_ASSOC);

			$result = "
				<div>
					<table id='tblInformasi' cellpadding='5' cellspacing='1' border='0' class='sortable'>
						<thead>
							<tr class='rowheader'>
								<th>".$_SESSION['lang']['namakaryawan']."</th>
								<th>First</th>
								<th>Last</th>
							</tr>
						</thead>
						<tbody>
			";
			
			$count = 0;
			while ($barabs = $resabs->fetch()) {
				// echo "<br>".$barabs['karyawanid']."####".$barabs['waktu1']."####".$barabs['waktu2'];
				$result .= "
					<tr class='rowcontent'>
						<td>".$barabs['namakaryawan']."</td>
						<td>".date('H:i', strtotime($barabs['waktu1']))."</td>
						<td>".(($barabs['waktu2'] != $barabs['waktu1']) ? date('H:i', strtotime($barabs['waktu2'])) : "")."</td>
					</tr>
				";

				$count++;
			}

			if ($count == 0) {
				$result .= "
					<tr class='rowcontent'>
						<td colspan='3' align='center'>".$_SESSION['lang']['datanotfound']."</td>
					</tr>
				";
			}

			$result .= "
						</tbody>
					</table>
				</div>
			";
			
			echo $result;
			break;
		case 'showDataTrackingRealtime':
			$getApi = new getContentAPI;
			if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
				// Jika URI yang array [1] panjang string <= 7, Maka munculkan
				if (strlen($expri[1]) <= 7) {
					$url = $_SERVER['HTTP_ORIGIN'] . "/" . $expri[1] . "/mobile/index.php/api/access_token/api_key";
					$urlData = $_SERVER['HTTP_ORIGIN'] . "/" . $expri[1] . "/mobile/index.php/api/module/dashboard/traffic_locations/load";
				} else {
					$url = $_SERVER['HTTP_ORIGIN'] . "/mobile/index.php/api/access_token/api_key";
					$urlData = $_SERVER['HTTP_ORIGIN'] . "/mobile/index.php/api/module/dashboard/traffic_locations/load";
				}
			} else {
				// Jika Server local / localhost maka munculkan URL localhost
				$url = $urlocal . "mobile/index.php/api/access_token/api_key";
				$urlData = $urlocal . "mobile/index.php/api/module/dashboard/traffic_locations/load";
			}
			//"?tanggal=".date("Y-m-d");
			$getApi->init($url, $options);

			$paramData = array(
				'tanggal' => date("Y-m-d"),
				'username' => $karyawanid,
				'tipe' => '1'
			);
			$getData = $getApi->post($urlData, $paramData);

			// $waktuMin = tambahmenit(1);
			// $waktuMax = kurangmenit(1);

			// $str = "select * from ".$dbname.".gps_location where username = '".$karyawanid."' and updatetime between '".$waktuMin."' and '".$waktuMax."' order by updatetime desc LIMIT 2";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $count = 0;

			// while($bar = $res->fetch()){
			// 	if($count==0){
			// 		$xAwal = $bar['logitude'];
			// 		$yAwal = ($bar['latitude']*(-1));
			// 	}else{
			// 		$xAkhir = $bar['logitude'];
			// 		$yAkhir = ($bar['latitude']*(-1));
			// 	}
			// 	$count++;
			// }

			echo $xAwal . "####" . $yAwal . "####" . $xAkhir . "####" . $yAkhir;
			break;

		case 'clearColorSvgBlok':
			$str = "select fill from " . $dbname . ".bi_5warna where tipe='" . $firstPT . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$warnaBlok = $bar['fill'];

			//Get MAP Blok
			$str = "select idsvg from " . $dbname . ".bi_map_pt where kodeorg = '" . $kodept . "' " . forKebunAll($kbnarr, 'unit', 'in') . " and tipepeta='" . $firstPT . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$arrBlok[]['idsvg'] = $bar['idsvg'];
			}

			echo json_encode($arrBlok) . "####" . $warnaBlok;
			break;
	}

	function randomColor() {
		$colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6', '#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
		return $colorArray[array_rand($colorArray)];
	}
