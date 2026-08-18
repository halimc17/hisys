<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method         = checkPostGet('method', '');
$pages          = checkPostGet('page', '');
$param          = $_POST;
$nopp           = checkPostGet('nopp', '');
$kodebarang     = checkPostGet('kodebarang', '');
$namabarang     = checkPostGet('namabarang', '');
$satuan         = checkPostGet('satuan', '');
$stok           = checkPostGet('stok', '');
$hargasatuan    = checkPostGet('hargasatuan', '');
$realisasi      = checkPostGet('realisasi', '');
$budget         = checkPostGet('budget', '');
$jmlhdiminta    = checkPostGet('jmlhdiminta', '');
$prioritas      = checkPostGet('prioritas', '');
$tglsdt         = checkPostGet('tglsdt', '');
$kodevhc        = checkPostGet('kodevhc', '');
$kmhm           = checkPostGet('kmhm', '');
$keterangan     = checkPostGet('keterangan', '');
$kd_project     = checkPostGet('kd_project', '');

$tglheader      = checkPostGet('tglheader', '');
$unit           = checkPostGet('unit', '');
$tipe           = checkPostGet('tipe', '');
$requester      = checkPostGet('requester', '');


$carinopppr     = checkPostGet('carinopppr', '');
$caritanggalpppr = checkPostGet('caritanggalpppr', '');

$id             = checkPostGet('id', '');
$rkd_bag        = checkPostGet('rkd_bag', '');

$kdorg          = checkPostGet('kdorg', '');

$txtfind        = checkPostGet('txtfind', '');
$rtgl_pp        = checkPostGet('rtgl_pp', '');

$kd_brg         = checkPostGet('kd_brg', '');
$namafile       = checkPostGet('namafile', '');

$user_id        = checkPostGet('usr_id', '');

$kriteriaefil   = checkPostGet('kriteriaefil', '');
$emodul         = 'PR';
$jenisApp       = 'PR';

switch ($method) {
	case 'get_isi':
		if (isset($kdorg)) {
			$kodeorg = trim($kdorg);


			#= cek tipe unit
			$str = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $kodeorg . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$tipeunit = $bar['tipe'];
				$induk = $bar['indu'];
			}

			if ($tipe == 'PR' and ($tipeunit == 'HOLDING' || $tipeunit == 'KANWIL') && $induk == "PPP") {
				exit("Warningsistem:Untuk tipe unit HOLDING dan RO/KANWIL tidak diperbolehkan untuk membuat PR Inventory");
			}



			if ($tipe == 'SR') {
				$tipe = 'SR';
			} else {
				$tipe = 'PR';
			}


			$tgl = date('Ymd');
			if (substr($param['tgl_pp'], 3, 2) != '') {
				$bln = substr($param['tgl_pp'], 3, 2);
			} else {
				$bln = date('m');
			}
			$thn = date('Y');

			$nopp = "/" . date('Y') . "/" . $tipe . "/" . $kodeorg;

			$str = "select nopp from " . $dbname . ".log_prapoht where nopp like '%" . $nopp . "%' order by nopp desc limit 1";
			$res = fetchdata($str);
			@$awal = substr($res[0]['nopp'], 0, 3);
			@$awal = intval($awal);
			@$cekbln = substr($res[0]['nopp'], 4, 2);
			@$cekthn = substr($res[0]['nopp'], 7, 4);

			if ($thn != $cekthn) {
				$awal = 1;
			} else {
				$awal++;
			}

			$counter = addZero($awal, 3);
			$nopp = $counter . "/" . $bln . "/" . $thn . "/" . $tipe . "/" . $kodeorg;
			echo $nopp;
		}
		break;

	case 'getrequester':
		$chk = "0";

		$kodept = getNamaOrg($kdorg, 'induk');
		if (getNamaOrg($kdorg, 'inti') == '1') {
			$whereLokasi = " AND lokasitugas in (".getOrgDetail(2).")";
		} else {
			$whereLokasi = " AND lokasitugas IN (SELECT kodeorganisasi FROM {$dbname}.organisasi WHERE induk='{$kodept}' AND tipe NOT IN ('HOLDING', 'KANWIL'))";
		}

		$str = "select karyawanid,namakaryawan,nik,bagian from " . $dbname . ".datakaryawan where 1=1 {$whereLokasi} and (tanggalkeluar='0000-00-00' or tanggalkeluar>='" . $as . "') order by namakaryawan asc";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$nmdept = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $val['bagian'] . "'");
			if ($_SESSION['standard']['userid'] == $val['karyawanid']) {
				$chk = '1';
			}
			if ($val['karyawanid'] == $requester) {
				$optkar .= "<option value='" . $val['karyawanid'] . "' selected>" . $val['namakaryawan'] . " (" . $nmdept[$val['bagian']] . ")</option>";
			} else {
				if ($_SESSION['standard']['userid'] == $val['karyawanid']) {
					$optkar .= "<option value='" . $val['karyawanid'] . "' selected>" . $val['namakaryawan'] . " (" . $nmdept[$val['bagian']] . ")</option>";
				} else {
					$optkar .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . " (" . $nmdept[$val['bagian']] . ")</option>";
				}
			}
		}


		if (getNamaOrg($kdorg, 'tipe') == 'KANWIL') {
			$dtaregion = '';
			$str = "select kodeunit,regional from " . $dbname . ".bgt_regional_assignment where kodeunit='" . $kdorg . "'";
			$res = fetchdata($str);
			foreach ($res as $val) {
				$dtaregion = $val['regional'];
			}

			$dtunitregion = '';
			$str = "select kodeunit,regional from " . $dbname . ".bgt_regional_assignment where regional='" . $dtaregion . "' and kodeunit like '%RO%'";
			$res = fetchdata($str);
			foreach ($res as $val) {
				if ($dtunitregion == '') {
					$dtunitregion = "'" . $val['kodeunit'] . "'";
				} else {
					$dtunitregion .= ",'" . $val['kodeunit'] . "'";
				}
			}

			if ($dtunitregion != '') {
				$str = "select karyawanid,namakaryawan,nik,bagian from " . $dbname . ".datakaryawan where lokasitugas in (" . $dtunitregion . ") and (tanggalkeluar='0000-00-00' or tanggalkeluar>='" . $as . "') order by namakaryawan asc";
				$res = fetchdata($str);
				foreach ($res as $val) {
					$nmdept = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $val['bagian'] . "'");
					if ($_SESSION['standard']['userid'] == $val['karyawanid']) {
						$chk = '1';
					}
					if ($val['karyawanid'] == $requester) {
						$optkar .= "<option value='" . $val['karyawanid'] . "' selected>" . $val['namakaryawan'] . " (" . $nmdept[$val['bagian']] . ")</option>";
					} else {
						if ($_SESSION['standard']['userid'] == $val['karyawanid']) {
							$optkar .= "<option value='" . $val['karyawanid'] . "' selected>" . $val['namakaryawan'] . " (" . $nmdept[$val['bagian']] . ")</option>";
						} else {
							$optkar .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . " (" . $nmdept[$val['bagian']] . ")</option>";
						}
					}
				}
			}
		}

		if ($chk == '0') {
			if ($_SESSION['empl']['tipekaryawan'] == '0') {
				$nmdept = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $_SESSION['empl']['bagian'] . "'");
				$optkar .= "<option value='" . $_SESSION['standard']['userid'] . "' selected>" . $_SESSION['empl']['name'] . " (" . $nmdept[$_SESSION['empl']['bagian']] . ")</option>";
			}
		}

		echo $optkar;
		break;

	case 'createheader':
		## CEK HEADER
		$str = "select * from " . $dbname . ".log_prapoht where nopp='" . $id . "'";
		$res = fetchdata($str);
		$countdata =  count($res);

		if ($countdata <= 0) {
			## GET PT
			$str = selectQuery($dbname, 'organisasi', "induk", "kodeorganisasi='" . $rkd_bag . "'");
			$res = fetchData($str);
			$pt = $res[0]['induk'];
			$nopp = $id;
			$tgl = date('Y-m-d');
			$id_user = $_SESSION['standard']['userid'];

			$str = "insert into " . $dbname . ".log_prapoht (pt,unit,tipepp,nopp,tanggal,dibuat,requester,close) values ('" . $pt . "','" . $rkd_bag . "','" . $tipe . "','" . $nopp . "','" . tanggalsystem($param['rtgl_pp']) . "','" . $id_user . "','" . $requester . "','0')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;

	case 'loaddata':
		$arrTipe = array('PR' => 'Purchase Request', 'SR' => 'Service Request', 'CP' => 'Capex Request', 'NR' => 'Non-Inventory Requset');
		$arrorgdet = getOrgDetail(2);
		$where = "1=1";

		if ($carinopppr != '') {
			$where .= " and nopp like '%" . $carinopppr . "%'";
		}

		if ($caritanggalpppr != '') {
			$where .= " and tanggal = '" . tanggalsystem($caritanggalpppr) . "'";
		}

		$limit = 20;
		$page = 0;
		if (isset($pages)) {
			$page = $pages;
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;

		$no = (($page * $limit));

		$str = "select * from " . $dbname . ".log_prapoht where " . $where . " and unit in (" . $arrorgdet . ")";
		$res = fetchdata($str);
		$jlhbrs = count($res);

		if ($jlhbrs <= 0) {
			$tab .= "<tr class=rowcontent><td colspan='10' style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			$str = "select * from " . $dbname . ".log_prapoht where " . $where . " and unit in (" . $arrorgdet . ") order by tanggal desc, nopp desc limit " . $offset . "," . $limit . "";
			$res = fetchdata($str);
			foreach ($res as $val) {
				$no++;

				## GET NAMA PEMBUAT PR
				$optnama = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $val['dibuat'] . "'");
				$namapembuat = $optnama[$val['dibuat']];

				## GET Status
				$stt = "";
				$countApp = getCountApproval($jenisApp, $val['unit']);
				if ($val['close'] == '0') {
					$totalrow = getTotalRow($dbname, 'log_prapodt', "nopp='" . $val['nopp'] . "'");
					$stt = "<a href=# id=seeprog onclick=frm_ajun('" . $val['nopp'] . "','" . $val['close'] . "','" . $totalrow . "') title=\"Click untuk mengubah status\">Belum Diajukan</a>";
				} elseif ($val['close'] == '1') {
					$stt = "<a href=# id=seeprog onclick=frm_ajun('" . $val['nopp'] . "','" . $val['close'] . "','1') title=\"Menunggu Keputusan\">Menunggu Persetujuan</a>";
				} else {
					$counttolak = 0;
					for ($i = 1; $i <= $countApp; $i++) {
						$strx = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $jenisApp . "' and level='" . $i . "' and kodeunit='" . $val['unit'] . "'";
						$resx = fetchData($strx);
						$tipeapp = $resx[0]['tipe'];
						$departemenapp = $resx[0]['departemen'];
						$tipekaryawanapp = $resx[0]['tipekaryawan'];
						$jabatanapp = $resx[0]['jabatan'];

						$arrDetail = detailApprove($i, $val['nopp'], $jenisApp);
						if ($tipeapp == '1' && $arrDetail['status'] != '') {
							if ($arrDetail['status'] != '1') {
								if ($departemenapp != '') {
									$opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
									$arrDetail['nama'] = $opttipe[$departemenapp];
								}

								if ($tipekaryawanapp != '') {
									$opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
									$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
								}

								if ($jabatanapp != '0') {
									$opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
									$arrDetail['nama'] = $opttipe[$jabatanapp];
								}
							}
						}
						if ($arrDetail['status'] == '3') {
							$counttolak++;
						}
					}
					if ($counttolak > 0) {
						$stt = "<a href=# id=seeprog  title=\"Not Available\">" . $_SESSION['lang']['ditolak'] . "</a>";
					} else {
						if ($val['close'] == '3') {
							$stt = "<a href=# id=seeprog  title=\"Not Available\">PR/SR Return</a>";
						} else {
							$stt = "<a href=# id=seeprog  title=\"Available\">" . $_SESSION['lang']['disetujui'] . "</a>";
						}
					}
				}

				$bgcolor = '';
				if ($val['close'] == '3') {
					$bgcolor = 'style=background:orange';
				} else if ($val['close'] == '2') {
					$strx = "select count(nopp) as nopp from " . $dbname . ".log_prapodt where nopp='" . $val['nopp'] . "' and status='3'";
					$resx = fetchdata($strx);
					$countx = $resx[0]['nopp'];
					if ($countx > 0) {
						$bgcolor = 'style=background:#42f5c2';
					}
				}

				// kamus kelompok barang
				$sql = "select a.nopp, a.kodebarang, b.kelompok FROM " . $dbname . ".log_prapodt a LEFT JOIN " . $dbname . ".log_5klbarang b on substr(a.kodebarang,1,3)=b.kode where 1";
				$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
				$qry->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $qry->fetch()) {
					$kelompok = substr($bar['kodebarang'], 0, 3);
					$kelompok2 = $bar['kelompok'];
					$kamuskelompokbarangpp[$bar['nopp']] = $kelompok . ' ' . $kelompok2;
				}

				// echo "<pre>";
				// print_r($kamuskelompokbarangpp);
				// echo "</pre>";

				## Cek Chat
				$ingChat = "chat0";
				$strx = "select * from " . $dbname . ".log_pp_chat where nopp='" . $val['nopp'] . "'";
				$resx = fetchdata($strx);
				if (count($resx) > 0) {
					$ingChat = "chat1";
				}

				$tab .= "<tr class=rowcontent id='tr_" . $no . "' " . $bgcolor . ">
					<td align=center>" . $no . "</td>
					<td align=center>" . $val['pt'] . "</td>
					<td align=center>" . $val['unit'] . "</td>";
				if ($val['ket_balik'] != '') {
					$tab .= "<td align=center style='background-color:#64FB76 '>" . $val['nopp'] . "<br>Become Out Standing : " . $val['ket_balik'] . "</td>";
				} else {
					$tab .= "<td align=center>" . $val['nopp'] . "</td>";
				}
				$tab .= "<td align=center>" . $arrTipe[$val['tipepp']] . "</td>
					<td>" . @$kamuskelompokbarangpp[$val['nopp']] . "</td>
					<td align=center style='min-width:80px'>" . tanggalnormal($val['tanggal']) . "</td>
					<td align=center>
						<img src='images/" . $ingChat . ".png' onclick=\"loadPPChat('" . $val['nopp'] . "','',event);\" class='resicon'>
					</td>
					<td align=center>" . $namapembuat . "</td>
					<td align=center>" . $stt . "</td>";

				if ($val['dibuat'] == $_SESSION['standard']['userid']) {
					if ($val['close'] == '0') {
						$tab .= "<td align=center nowrap>
								<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $val['nopp'] . "','" . $val['tipepp'] . "','" . tanggalnormal($val['tanggal']) . "','" . $val['unit'] . "','" . $val['close'] . "','0','" . $val['requester'] . "');\" >
								<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPp('" . $val['nopp'] . "','" . $val['close'] . "','0');\" >
								<img onclick=\"previewDetail('" . $val['nopp'] . "',event);\" title=\"Detail PP\" class=\"resicon\" src=\"images/zoom.png\">
								<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_prapoht','" . $val['nopp'] . "','','log_slave_print_log_pp_new',event);\">
							</td>";
					} else {
						$tab .= "<td align=center nowrap>
								<img onclick=\"previewDetail('" . $val['nopp'] . "',event);\" title=\"Detail PP\" class=\"resicon\" src=\"images/zoom.png\">
								<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_prapoht','" . $val['nopp'] . "','','log_slave_print_log_pp_new',event);\">
							</td>";
					}
				} else {
					$tab .= "<td align=center nowrap>
							<img onclick=\"previewDetail('" . $val['nopp'] . "',event);\" title=\"Detail PP\" class=\"resicon\" src=\"images/zoom.png\">
							<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_prapoht','" . $val['nopp'] . "','','log_slave_print_log_pp_new',event);\"></td>";
				}

				$tab .= "</tr>";
			}

			## PAGING
			$tab .= createpaging($jlhbrs, $limit, $page, '10', 'loadData', 'getPage');
			$tab .= "</table>";
		}

		echo $tab;
		break;



	case 'createTable':
		# Get Data
		$query = selectQuery($dbname, 'log_prapodt', "*", "nopp='" . $id . "'");
		$data = fetchData($query);

		// Cek Header
		$queryH = selectQuery($dbname, 'log_prapoht', "*", "nopp='" . $id . "'");
		$dataH = fetchData($queryH);

		if (empty($dataH)) {

			// Get PT
			$qPt = selectQuery($dbname, 'organisasi', "induk", "`kodeorganisasi`='" . $rkd_bag . "'");
			$resPt = fetchData($qPt);

			$nopp = $id;
			$tgl = date('Y-m-d');
			$kodeorg = $resPt[0]['induk'];
			$id_user = $_SESSION['standard']['userid'];

			$str = "insert into " . $dbname . ".log_prapoht (`nopp`,`tipepp`,`kodeorg`,`tanggal`,`dibuat`) values ('" . $nopp . "','" . $tipe . "','" . $kodeorg . "','" . $tgl . "','" . $id_user . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}

		# Create Detail Table
		createTabDetail($id, $data);
		break;

	case 'delete':
		$str = "delete from " . $dbname . ".log_prapoht where nopp='" . $nopp . "'";
		try {
			$owlPDO->exec($str);

			$str = "delete from " . $dbname . ".log_prapodt where nopp='" . $nopp . "'";
			try {
				$owlPDO->exec($str);

				$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $nopp . "'";
				try {
					$owlPDO->exec($str);

					$str = "delete from " . $dbname . ".approval where notransaksi='" . $nopp . "'";
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
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'getDetailPP':
		$tab = "";
		$tab .= "<script language=\"javascript\" src=\"js/log_pp.js\"></script>";

		$kodeorg = substr($nopp, 15, 4);

		## GET HEADER
		$str = "select * from " . $dbname . ".log_prapoht where nopp='" . $nopp . "'";
		$res = fetchdata($str);
		$expketht = explode('/', $res[0]['keterangan']);
		if (@$expketht[1] == 'FRM') {
			$jnsapp = "CPX";
			$noppx = $res[0]['keterangan'];
			$ketcapex = $res[0]['keterangan'];
		} else {
			$jnsapp = "PR";
			$noppx = $nopp;
			$ketcapex = "-";
		}
		$unitht   = $res[0]['unit'];
		$tipepp   = $res[0]['tipepp'];

		$tanggalht = $res[0]['tanggal'];
		$dibuat   = $res[0]['dibuat'];
		$requester = $res[0]['requester'];



		$optdepartmen = makeOption($dbname, 'datakaryawan', 'karyawanid,bagian', "karyawanid='" . $requester . "'");
		$departemen = $optdepartmen[$requester];

		$countApprove = getCountApproval($jnsapp, $kodeorg, $departemen);

		$dept = makeOption($dbname, 'datakaryawan', 'karyawanid,bagian', "karyawanid='" . $requester . "'");
		$nmdept = makeOption($dbname, 'sdm_5departemen', 'kode,nama');
		$optpurchaser = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $dibuat . "' or karyawanid='" . $requester . "'");
		$purchaser = $optpurchaser[$dibuat];

		$tab .= "<div style='width:auto;overflow:auto;'>
			<table border=0 cellspacing=1 cellpadding=5 class=sortable>
				<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>" . $_SESSION['lang']['nomor'] . " PR/SR</td>
					<td style='text-align:center'>" . $_SESSION['lang']['tanggal'] . " PR/SR</td>
					<td style='text-align:center'>" . $_SESSION['lang']['dbuat_oleh'] . "</td>
					<td style='text-align:center'>" . $_SESSION['lang']['prmntaanPembelian'] . "</td>";
		for ($i = 1; $i <= $countApprove; $i++) {
			$tab .= "<td style='text-align:center'>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
		}
		$tab .= "</tr>
				</thead>
				<tbody>";
		$d = "";
		if ($nmdept[$dept[$requester]] != '') {
			$d = "Dept : " . $nmdept[$dept[$requester]];
		} else {
			$d = "Dept : " . $dept[$requester];
		}

		$tab .= "<tr class=rowcontent>
				<td>" . $nopp . "</td>
				<td style='text-align:center'>" . tanggalnormal($tanggalht) . "</td>
				<td>" . $purchaser . "</td>
				<td>" . $optpurchaser[$requester] . "<br>" . $d . "</td>";

		$arrHsl = array("0" => $_SESSION['lang']['wait_approval'], "1" => $_SESSION['lang']['disetujui'], "2" => $_SESSION['lang']['ditolak']);

		for ($i = 1; $i <= $countApprove; $i++) {
			$strx = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $jnsapp . "' and level='" . $i . "' and kodeunit='" . $kodeorg . "'";
			$resx = fetchData($strx);
			$tipeapp = $resx[0]['tipe'];
			$departemenapp = $resx[0]['departemen'];
			$tipekaryawanapp = $resx[0]['tipekaryawan'];
			$jabatanapp = $resx[0]['jabatan'];

			$arrDetail = detailApprove($i, $noppx, $jnsapp);
			if ($tipeapp == '1' && $arrDetail['status'] != '') {
				if ($arrDetail['status'] != '1') {
					if ($departemenapp != '') {
						$opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
						$arrDetail['nama'] = $opttipe[$departemenapp];
					}

					if ($tipekaryawanapp != '') {
						$opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
						$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
					}

					if ($jabatanapp != '0') {
						$opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
						$arrDetail['nama'] = $opttipe[$jabatanapp];
					}
				}
			}
			$tab .= "<td align=center style='vertical-align:top'>
					" . $arrDetail['nama'] . "
					<br>" . (($arrDetail['status'] == '9' || $arrDetail['status'] == '') ? "" : "(" . $arrDetail['namastatus'] . ")") . "
					<br>
					" . (tanggalnormal($arrDetail['tanggal']) == '--' || tanggalnormal($arrDetail['tanggal']) == '00-00-0000' ? '' : tanggalnormal($arrDetail['tanggal'])) . "
					<br>
					" . $arrDetail['komentar'] . "
				</td>";
		}
		$tab .= "</tr>
			</tbody>
		</table>
		
		<br />";

		## GET DETAIL RETURN PR/SR
		$str = "select *, max(level) as level from " . $dbname . ".approval_return where notransaksi='" . $nopp . "' group by keterangan";
		$res = fetchdata($str);
		$row = count($res);
		if ($row > 0) {
			$no = 0;
			foreach ($res as $key => $val) {
				$no++;
				$tab .= "<div style='width:auto;overflow:auto;'>
					<table border=0 cellspacing=1 cellpadding=3 class=sortable>
						<thead>
						<tr style='font-weight:bold'>
							<td colspan='" . (2 + $val['level']) . "'>Return - " . $no . "</td>
						</tr>
						<tr style='font-weight:bold'>
							<td style='text-align:center'>" . $_SESSION['lang']['tanggal'] . " PR</td>
							<td style='text-align:center'>" . $_SESSION['lang']['dbuat_oleh'] . "</td>";
				for ($i = 1; $i <= $val['level']; $i++) {
					$tab .= "<td style='text-align:center'>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
				}
				$tab .= "</tr>
					</thead>
					<tbody>
						<tr class=rowcontent>
							<td>" . tanggalnormal($tanggalht) . "</td>
							<td>" . $purchaser . "</td>";
				for ($i = 1; $i <= $val['level']; $i++) {
					$strx = "select * from " . $dbname . ".approval_return where notransaksi='" . $nopp . "' and level='" . $i . "' and keterangan='" . $val['keterangan'] . "'";
					$resx = fetchdata($strx);
					$namakaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $resx[0]['karyawanid'] . "'");
					$tab .= "<td style='text-align:center'>" . $namakaryawan[$resx[0]['karyawanid']] . "
									<br>	
									" . $arrHsl[$resx[0]['status']] . "
									<br>	
									" . ($resx[0]['status'] < 1 ? '' : tanggalnormal(substr($resx[0]['tanggal'], 0, 10))) . "
								</td>";
				}

				$exdata = explode('##', $val['keterangan']);
				if (count($exdata) > 1) {
					$nmbarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $exdata[0] . "'");
					$nmbrgreturn = $nmbarang[$exdata[0]];
					$tab .= "</tr>
							<tr class=rowcontent>
								<td colspan='" . (2 + $val['level']) . "'>Barang Return : " . $nmbrgreturn . " (" . $exdata[0] . ")</td>
							</tr>
							<tr class=rowcontent>
								<td colspan='" . (2 + $val['level']) . "'>Keterangan Return : " . $exdata[1] . "</td>
							</tr>";
				} else {
					$tab .= "</tr>
							<tr class=rowcontent>
								<td colspan='" . (2 + $val['level']) . "'>Keterangan Return : " . $val['keterangan'] . "</td>
							</tr>";
				}
				$tab .= "
					</tbody>
					</table>
				</div>";
				$tab .= "<br>";
			}
		}

		$tab .= "<br>";

		$arrvalbudget = validasibudget($tipepp, $unitht);
		## GET DETAIL PR
		$tab .= "<table border=0 cellspacing=1 cellpadding=3 class=sortable>
			<thead>
			<tr style='font-weight:bold'>
				<td rowspan=2 style='text-align:center'>No</td>
				<td rowspan=2 style='text-align:center'>Chat</td>
				<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['kodebarang'] . "</td>
				<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['namabarang'] . "</td>
				<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['satuan'] . "</td>
				<td rowspan=2 style='text-align:center' width='50px'>" . $_SESSION['lang']['jmlhDiminta'] . "</td>
				<td rowspan=2 style='text-align:center' width='50px'>Jumlah di Setujui</td>
				<td rowspan=2 style='text-align:center'>Prioritas</td>
				<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['kodevhc'] . "</td>
				<td rowspan=2 style='text-align:center'>KM/HM</td>
				<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['tanggal'] . " PR/SR</td>
				<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['tgldibutuhkan'] . "</td>   
				<td rowspan=2 style='text-align:center' width='50px'>" . $_SESSION['lang']['stock'] . "</td>
				<td rowspan=2 style='text-align:center' width='50px'>" . $_SESSION['lang']['hargasatuan'] . "</td>";
		if ($tipepp == 'SR') {
			$tab .= "<td colspan=2 style='text-align:center'>Per Nomor Akun</td>";
		} else {
			$tab .= "<td colspan=2 style='text-align:center'>Per Kode Barang</td>";
		}
		if ($arrvalbudget['digit'] < 9) {
			$tab .= "<td colspan=2 style='text-align:center;'>Per " . $arrvalbudget['digit'] . " Digit<br>Kode Barang</td>";
		}
		if ($tipepp == 'SR') {
			$tab .= "<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['noakun'] . " " . $_SESSION['lang']['budget'] . "</td>";
		}
		$tab .= "<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['nopo'] . "</td>
				<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['tanggal'] . " PO/SO</td>
				<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['keterangan'] . "</td>
				<td rowspan=2 style='text-align:center'>" . $_SESSION['lang']['cdproject'] . "</td>
			</tr>
			<tr style='font-weight:bold'>
				<td style='text-align:center' width='50px'>" . $_SESSION['lang']['realisasi'] . "</td>
				<td style='text-align:center' width='50px'>" . $_SESSION['lang']['anggaran'] . "</td>";
		if ($arrvalbudget['digit'] < 9) {
			$tab .= "<td style='text-align:center;' width='50px'>" . $_SESSION['lang']['realisasi'] . "</td>
					<td style='text-align:center;' width='50px'>" . $_SESSION['lang']['anggaran'] . "</td>";
		}
		$tab .= "</tr>
		</thead>
		<tbody>";

		$str = "select * from " . $dbname . ".log_prapodt where nopp='" . $nopp . "'";
		$res = fetchdata($str);
		$countdata = count($res);
		$no = 0;
		$totalpo = 0;
		if ($countdata <= 0) {
			$tab .= "<tr class=rowcontent style='text-align:center'><td colspan=21>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			foreach ($res as $val) {
				$no++;

				$tmltgl = explode('-', $tanggalht);
				$tgldari = "01-01-" . $tmltgl[0];
				$prddari = $tmltgl[0] . "-01";
				$prdsampai = $tmltgl[0] . "-" . $tmltgl[1];

				## Cek Chat
				$ingChat = "chat0";
				$strx = "select * from " . $dbname . ".log_pp_chat where kodebarang='" . $val['kodebarang'] . "' and nopp='" . $nopp . "'";
				$resx = fetchdata($strx);
				if (count($resx) > 0) {
					$ingChat = "chat1";
				}

				## GET NAMA BARANG
				$optnmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $val['kodebarang'] . "'");
				$namabarang = $optnmbrg[$val['kodebarang']];

				## GET PRIORITAS
				$optprioritas = makeOption($dbname, 'log_5prioritas', 'kode,nama', "kode='" . $val['prioritas'] . "'");
				$prioritas = $optprioritas[$val['prioritas']];

				## GET PLAT KENDARAAN
				$optplat = makeOption($dbname, 'vhc_5master', 'kodevhc,nopol', "kodevhc='" . $val['kodevhc'] . "'");
				if ($optplat[$val['kodevhc']] != '') {
					$noplat = $val['kodevhc'] . " - " . $optplat[$val['kodevhc']];
				} else {
					$noplat = $val['kodevhc'];
				}

				## GET NO PO
				$strx = "select nopo, tanggal from " . $dbname . ".log_po_vw where nopp='" . $val['nopp'] . "' and kodebarang='" . $val['kodebarang'] . "'  and jmlhstlhclose='0' and statuspo IN (1,2,3)";
				$resx = fetchdata($strx);
				if (count($resx) > 0) {
					$nopo = $resx[0]['nopo'];
					$tglpo = tanggalnormal($resx[0]['tanggal']);
				} else {
					$nopo = "";
					$tglpo = "";
					$totalpo++;
				}

				#= get realisasi per

				$hketerangan = "";
				if ($val['keterangan'] == '') {
					if ($val['alasanstatus'] != '') {
						$hketerangan .= "<i><b>" . $val['alasanstatus'] . "</b></i>";
					}
				} else {
					$hketerangan .= $val['keterangan'];
					if ($val['alasanstatus'] != '') {
						$hketerangan .= "<br><i><b>" . $val['alasanstatus'] . "</b></i>";
					}
				}

				$kdbarang = $val['kodebarang'];

				$tab .= "<tr class=rowcontent>
					<td style='text-align:right'>" . $no . "</td>
					<td style='text-align:center'>
						<img src='images/" . $ingChat . ".png' onclick=\"loadPPChat('" . $nopp . "','" . $val['kodebarang'] . "',event);\" class='resicon'>
					</td>
					<td style='cursor:pointer;text-align:center' onclick=showdocpakaibarang('" . $tgldari . "','" . tanggalnormal($tanggalht) . "','" . substr($nopp, -4) . "','" . $val['kodebarang'] . "',event) title='Detail Pemakaian Barang'><font color=blue>" . $val['kodebarang'] . "</font></td>
					<td style=cursor:pointer onclick=showdocpakaibarang('" . $tgldari . "','" . tanggalnormal($tanggalht) . "','" . substr($nopp, -4) . "','" . $val['kodebarang'] . "',event) title='Detail Pemakaian Barang'><font color=blue>" . $namabarang . "</font></td>
					<td style='text-align:center'>" . $val['satuanpp'] . "</td>
					<td style='text-align:right'>" . hidezerodecimal($val['jumlahpp'], 2) . "</td>
					<td style='text-align:right'>" . hidezerodecimal($val['jumlah'], 2) . "</td>
					<td style='text-align:left' nowrap>" . $val['prioritas'] . " - " . $prioritas . "</td>";

				$tab .= "<td>";
				if ($val['kodevhc'] != '') {
					$tab .= "<table>";
					$nomor = 0;
					$arrkdvhc = explode(",", $val['kodevhc']);
					foreach ($arrkdvhc as $kodekend) {
						$temp = "";
						$temp = explode("=", $kodekend);
						$kodekend = $temp[0];
						$optplat = makeOption($dbname, 'vhc_5master', 'kodevhc,nopol', "kodevhc='" . $kodekend . "'");
						if ($optplat[$kodekend] != '') {
							$noplat = $kodekend . " - " . $optplat[$kodekend];
						} else {
							$noplat = $kodekend;
						}
						$nomor++;
						$tab .= "<tr>";
						$tab .= "<td style='text-align:left;cursor:pointer' nowrap onclick=showdocsparepart('" . $prddari . "','" . $prdsampai . "','" . substr($val['nopp'], -4) . "','" . $kodekend . "',event)><font color=blue>" . $nomor . "." . $noplat . " = " . $temp[1] . "</font></td>";
						$tab .= "</tr>";
					}
					$tab .= "</table>";
				}
				$tab .= "</td>";

				//$tab.="<td style='text-align:center;cursor:pointer' onclick=showdocsparepart('".$prddari."','".$prdsampai."','".substr($nopp,-4)."','".$val['kodevhc']."',event)><font color=blue>".$noplat."</font></td>

				$tab .= "<td align=center>" . hidezerodecimal($val['kmhm'], 2) . "</td>
					<td align=center nowrap>" . tanggalnormal($tanggalht) . "</td>
					<td align=center nowrap>" . tanggalnormal($val['tgl_sdt']) . "</td>
					<td align=center>" . hidezerodecimal($val['stock'], 2) . "</td>
					<td align=right>" . hidezerodecimal($val['hargasatuan'], 2) . "</td>";
				$tab .= "<td align=center>" . hidezerodecimal($val['realperbarang']) . "</td>";
				$tab .= "<td align=center>" . hidezerodecimal($val['bgtperbarang']) . "</td>";
				if ($no == 1) {
					if ($arrvalbudget['digit'] < 9) {
						$tab .= "<td align=center rowspan=" . $countdata . ">" . hidezerodecimal($val['realisasi']) . "</td>";
						$tab .= "<td align=center rowspan=" . $countdata . ">" . hidezerodecimal($val['anggaran']) . "</td>";
					}
				}
				if ($tipepp == 'SR') {
					$tab .= "<td style='text-align:center'>" . getNamaAkun($val['noakunbudget']) . "</td>";
				}
				#== abdul
				$tab .= "<td style='text-align:center'>" . $nopo . "</td>
					<td style='text-align:center;min-width:80px'>" . $tglpo . "</td>
					<td>" . $hketerangan . "</td>
					<td>" . $val['kodeproject'] . "</td>
				</tr>";
			}
		}
		$tab .= "</tbody>
		</table>
		</div>";
		if ($kdbarang != '') {
			$arrsts = array('1' => 'Aktif', '0' => 'Non Aktif');
			$arrdgt = array('3' => 'Per Kelompok Barang', '5' => 'Per Sub Kelompok Barang', '9' => 'Per Kode Barang');
			$tab .= "<table><tr>";
			$tab .= "<td style=vertical-align:top>";
			$tab .= "<fieldset style=float:left><legend><b>Info Validasi Anggaran</b></legend>";
			$tab .= "<ul>Status validasi : <b>" . $arrsts[$arrvalbudget['status']] . "</b></ul>";
			$tab .= "<ul>Update validasi : <b>" . $arrvalbudget['update'] . "</b></ul>";
			$tab .= "<ul>Digit kodebarang : <b>" . $arrvalbudget['digit'] . " digit atau " . $arrdgt[$arrvalbudget['digit']] . "</b></ul>";
			$tab .= "</fieldset>";
			$tab .= "</td>";
			$tab .= "<td style=vertical-align:top>";
			if ($tipepp == 'SR') {
				$tab .= "<fieldset style=float:left><legend><b><font style=color:red>= </font>Penjelasan budget dan realisasi untuk jenis SR - Service Request<font style=color:red> =</font></b></legend>";
				$tab .= "<ul>Update : <b>08-07-2022</b></ul>";
				$tab .= "<ul>Budget Rp : <b><font style=color:blue>Rupiah budget per Kode Barang ditambah Rupiah per nomor akun (jika akun terisi).</font></b></ul>";
				$tab .= "<ul>Realisasi Rp : <b><font style=color:blue>Rupiah budget per Kode Barang ditambah Rupiah Jurnal per nomor akun (jika akun terisi).</font></b></ul>";
				$tab .= "</fieldset>";
			}
			$tab .= "</td>";
			$tab .= "</tr>";
			$tab .= "</table>";

			$tab .= "<div style=clear:both></div>";
			$tab .= "<fieldset style=float:left><legend><b>Info Rupiah Per Kode Barang</b></legend>
				<ul>Nilai rupiah realisasi dan budget diperoleh pertanggal pembuatan PR (" . tglnormal($tanggalht) . ").</ul>";
			$tab .= "<ul>Budget dan Realisasi per kode barang hanya akan muncul jika :
					<ul><li>Barang sudah pernah di terimakan di gudang (GR).</li></ul>";
			if ($tanggalht <= '2021-11-17') {
				$tab .= "<ul><li><b>Tanggal pembuatan PR diatas tanggal 17 November 2021.</b></li></ul>";
			}
			$tab .= "</ul>";
			$tab .= "</fieldset>";
			$tab .= "<div style=clear:both></div>";

			$optnmklbrg = makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
			$tab .= "<fieldset style=float:left;display:none;><legend><b>Info Rupiah Per Kelompok Barang</b></legend>";
			if (substr($kdbarang, 0, 1) == 9) {
				$tab .= "<ul>Update per 07-01-2022 khusus untuk barang Asset (9xx) penguncian terhadap budget adalah per kode barang.</ul>";
			} else {
				$tab .= "<ul>Nilai rupiah realisasi dan budget diperoleh pertanggal pembuatan PR (" . tglnormal($tanggalht) . ").</ul>
				<ul>Realisasi : Jumlah realisasi penerimaan barang dari PO (GR) berdasarkan Kelompok Barang " . substr($kdbarang, 0, 3) . " - " . $optnmklbrg[substr($kdbarang, 0, 3)] . "</ul>
				<ul>Budget : Jumlah budget barang (modul Anggaran) berdasarkan Kelompok Barang " . substr($kdbarang, 0, 3) . " - " . $optnmklbrg[substr($kdbarang, 0, 3)] . "</ul>
				<ul>Kelompok Barang : 3 digit pertama dari kode barang.</ul>";
			}
			$tab .= "</fieldset><div style=clear:both></div>";
		}


		## GET FILE UPLOAD
		$tab .= "<br>
		<div style='width:auto;overflow:auto;'>
			<table border=0 cellspacing=1 cellpadding=5 class=sortable>
				<thead>
				<tr style='font-weight:bold'>
					<td align='center'>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>Kriteria</td>
					<td align='center'>Filename</td>
					<td align='center'>Action</td>
				</tr>
				</thead>
				<tbody id='listfilesview'>
				</tbody>";
		if ($totalpo > 0) {
			$arrmodul = getmodulefil($emodul);
			foreach ($arrmodul as $key => $val) {
				$optkriteria .= "<option value='" . $key . "'>" . $val['kriteria'] . "</option>";
			}
			$tab .= "<tr>
					<td colspan=2></td>
					<td>
						<select id='kriteriaefil'>" . $optkriteria . "</select>
					</td>
					<td>
						<input type='file' name='upload' id='upload' class=mybutton>
					</td>
					<td>
						<button class=mybutton onclick=\"submitfilex('" . $nopp . "')\">Submit</button>
					</td>
				</tr>";
		}
		$tab .= "</table>
		</div>";

		$tab .= "<br />";

		## GET DETAIL ANGGARAN
		$tab .= "<div id=dtFormDetail style=\"overflow:auto;\"></div>";

		echo $tab;
		break;

	case 'getDetailPP2':
		$kodeorg = substr($nopp, 15, 4);

		## GET HEADER
		$str = "select * from " . $dbname . ".log_prapoht where nopp='" . $nopp . "'";
		$res = fetchdata($str);
		$expketht = explode('/', $res[0]['keterangan']);
		if (@$expketht[1] == 'FRM') {
			$jnsapp = "CPX";
			$noppx = $res[0]['keterangan'];
			$ketcapex = $res[0]['keterangan'];
		} else {
			$jnsapp = "PR";
			$noppx = $nopp;
			$ketcapex = "-";
		}
		$tanggalht = $res[0]['tanggal'];
		$dibuat = $res[0]['dibuat'];
		$countApprove = getCountApproval($jnsapp, $kodeorg);

		$optpurchaser = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $dibuat . "'");
		$purchaser = $optpurchaser[$dibuat];

		$tab .= "<div style='width:auto;overflow:auto;'>
			<table border=0 cellspacing=1 cellpadding=3 class=sortable>
				<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>" . $_SESSION['lang']['tanggal'] . " PR/SR</td>
					<td style='text-align:center'>" . $_SESSION['lang']['dbuat_oleh'] . "</td>";
		for ($i = 1; $i <= $countApprove; $i++) {
			$tab .= "<td style='text-align:center'>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
		}
		$tab .= "<td style='text-align:center'>No.Capex</td>
				</tr>
				</thead>
				<tbody>";
		$tab .= "<tr class=rowcontent>
				<td style='text-align:center'>" . tanggalnormal($tanggalht) . "</td>
				<td>" . $purchaser . "</td>";

		$arrHsl = array("0" => $_SESSION['lang']['wait_approval'], "1" => $_SESSION['lang']['disetujui'], "2" => $_SESSION['lang']['ditolak']);

		for ($i = 1; $i <= $countApprove; $i++) {
			$strx = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $jnsapp . "' and level='" . $i . "' and kodeunit='" . $kodeorg . "'";
			$resx = fetchData($strx);
			$tipeapp = $resx[0]['tipe'];
			$departemenapp = $resx[0]['departemen'];
			$tipekaryawanapp = $resx[0]['tipekaryawan'];
			$jabatanapp = $resx[0]['jabatan'];

			$arrDetail = detailApprove($i, $noppx, $jnsapp);
			if ($tipeapp == '1' && $arrDetail['status'] != '') {
				if ($arrDetail['status'] != '1') {
					if ($departemenapp != '') {
						$opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
						$arrDetail['nama'] = $opttipe[$departemenapp];
					}

					if ($tipekaryawanapp != '') {
						$opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
						$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
					}

					if ($jabatanapp != '0') {
						$opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
						$arrDetail['nama'] = $opttipe[$jabatanapp];
					}
				}
			}
			$tab .= "<td align=center style='vertical-align:top'>
					" . $arrDetail['nama'] . "
					<br>" . (($arrDetail['status'] == '9' || $arrDetail['status'] == '') ? "" : "(" . $arrDetail['namastatus'] . ")") . "
					<br>
					" . (tanggalnormal($arrDetail['tanggal']) == '--' || tanggalnormal($arrDetail['tanggal']) == '00-00-0000' ? '' : tanggalnormal($arrDetail['tanggal'])) . "
				</td>";
		}
		$tab .= "<td align=center>" . $ketcapex . "</td>
			</tr>
			</tbody>
		</table>
		
		<br />";

		## GET DETAIL RETURN PR/SR
		$str = "select *, max(level) as level from " . $dbname . ".approval_return where notransaksi='" . $nopp . "' group by keterangan";
		$res = fetchdata($str);
		$row = count($res);
		if ($row > 0) {
			$no = 0;
			foreach ($res as $key => $val) {
				$no++;
				$tab .= "<div style='width:auto;overflow:auto;'>
					<table border=0 cellspacing=1 cellpadding=3 class=sortable>
						<thead>
						<tr style='font-weight:bold'>
							<td colspan='" . (2 + $val['level']) . "'>Return - " . $no . "</td>
						</tr>
						<tr style='font-weight:bold'>
							<td style='text-align:center'>" . $_SESSION['lang']['tanggal'] . " PR</td>
							<td style='text-align:center'>" . $_SESSION['lang']['dbuat_oleh'] . "</td>";
				for ($i = 1; $i <= $val['level']; $i++) {
					$tab .= "<td style='text-align:center'>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
				}
				$tab .= "</tr>
					</thead>
					<tbody>
						<tr class=rowcontent>
							<td>" . tanggalnormal($tanggalht) . "</td>
							<td>" . $purchaser . "</td>";
				for ($i = 1; $i <= $val['level']; $i++) {
					$strx = "select * from " . $dbname . ".approval_return where notransaksi='" . $nopp . "' and level='" . $i . "' and keterangan='" . $val['keterangan'] . "'";
					$resx = fetchdata($strx);
					$namakaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $resx[0]['karyawanid'] . "'");
					$tab .= "<td style='text-align:center'>" . $namakaryawan[$resx[0]['karyawanid']] . "
									<br>	
									" . $arrHsl[$resx[0]['status']] . "
									<br>	
									" . ($resx[0]['status'] < 1 ? '' : tanggalnormal(substr($resx[0]['tanggal'], 0, 10))) . "
								</td>";
				}

				$exdata = explode('##', $val['keterangan']);
				if (count($exdata) > 1) {
					$nmbarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $exdata[0] . "'");
					$nmbrgreturn = $nmbarang[$exdata[0]];
					$tab .= "</tr>
							<tr class=rowcontent>
								<td colspan='" . (2 + $val['level']) . "'>Barang Return : " . $nmbrgreturn . " (" . $exdata[0] . ")</td>
							</tr>
							<tr class=rowcontent>
								<td colspan='" . (2 + $val['level']) . "'>Keterangan Return : " . $exdata[1] . "</td>
							</tr>";
				} else {
					$tab .= "</tr>
							<tr class=rowcontent>
								<td colspan='" . (2 + $val['level']) . "'>Keterangan Return : " . $val['keterangan'] . "</td>
							</tr>";
				}
				$tab .= "
					</tbody>
					</table>
				</div>";
				$tab .= "<br>";
			}
		}

		$tab .= "<br>";

		## GET DETAIL PR
		$tab .= "<table border=0 cellspacing=1 cellpadding=3 class=sortable>
			<thead>
			<tr style='font-weight:bold'>
				<td style='text-align:center'>No</td>
				<td style='text-align:center'>Chat</td>
				<td style='text-align:center'>" . $_SESSION['lang']['kodebarang'] . "</td>
				<td style='text-align:center'>" . $_SESSION['lang']['namabarang'] . "</td>
				<td style='text-align:center'>" . $_SESSION['lang']['satuan'] . "</td>
				<td style='text-align:center' width='50px'>" . $_SESSION['lang']['jmlhDiminta'] . "</td>
				<td style='text-align:center' width='50px'>Jumlah di Setujui</td>
				<td style='text-align:center'>Prioritas</td>
				<td style='text-align:center'>" . $_SESSION['lang']['kodevhc'] . "</td>
				<td style='text-align:center'>KM/HM</td>
				<td style='text-align:center'>" . $_SESSION['lang']['tanggal'] . " PR/SR</td>
				<td style='text-align:center'>" . $_SESSION['lang']['tgldibutuhkan'] . "</td>   
				<td style='text-align:center' width='50px'>" . $_SESSION['lang']['stock'] . "</td>
				<td style='text-align:center' width='50px'>" . $_SESSION['lang']['hargasatuan'] . "</td>
				<td style='text-align:center' width='50px'>" . $_SESSION['lang']['realisasi'] . "</td>
				<td style='text-align:center' width='50px'>" . $_SESSION['lang']['anggaran'] . "</td>
				<td style='text-align:center'>" . $_SESSION['lang']['nopo'] . "</td>
				<td style='text-align:center'>" . $_SESSION['lang']['tanggal'] . " PO</td>
				<td style='text-align:center'>" . $_SESSION['lang']['keterangan'] . "</td>
			</tr>
		</thead>
		<tbody>";

		$str = "select * from " . $dbname . ".log_prapodt where nopp='" . $nopp . "'";
		$res = fetchdata($str);
		$countdata = count($res);
		$no = 0;
		if ($countdata <= 0) {
			$tab .= "<tr class=rowcontent style='text-align:center'><td colspan=19>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			foreach ($res as $val) {
				$no++;

				$tmltgl = explode('-', $tanggalht);
				$tgldari = "01-01-" . $tmltgl[0];
				$prddari = $tmltgl[0] . "-01";
				$prdsampai = $tmltgl[0] . "-" . $tmltgl[1];

				## Cek Chat
				$ingChat = "chat0";
				$strx = "select * from " . $dbname . ".log_pp_chat where kodebarang='" . $val['kodebarang'] . "' and nopp='" . $nopp . "'";
				$resx = fetchdata($strx);
				if (count($resx) > 0) {
					$ingChat = "chat1";
				}

				## GET NAMA BARANG
				$optnmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $val['kodebarang'] . "'");
				$namabarang = $optnmbrg[$val['kodebarang']];

				## GET PRIORITAS
				$optprioritas = makeOption($dbname, 'log_5prioritas', 'kode,nama', "kode='" . $val['prioritas'] . "'");
				$prioritas = $optprioritas[$val['prioritas']];

				## GET PLAT KENDARAAN
				$optplat = makeOption($dbname, 'vhc_5master', 'kodevhc,nopol', "kodevhc='" . $val['kodevhc'] . "'");
				if ($optplat[$val['kodevhc']] != '') {
					$noplat = $val['kodevhc'] . " - " . $optplat[$val['kodevhc']];
				} else {
					$noplat = $val['kodevhc'];
				}

				## GET NO PO
				$strx = "select nopo, tanggal from " . $dbname . ".log_po_vw where nopp='" . $val['nopp'] . "' and kodebarang='" . $val['kodebarang'] . "'  and jmlhstlhclose='0'";
				$resx = fetchdata($strx);
				if (count($resx) > 0) {
					$nopo = $resx[0]['nopo'];
					$tglpo = tanggalnormal($resx[0]['tanggal']);
				} else {
					$nopo = "";
					$tglpo = "";
				}

				$stylebg = "";
				if ($kd_brg == $val['kodebarang']) {
					$stylebg = "background-color:#D6F097";
				}


				$tab .= "<tr class=rowcontent style='" . $stylebg . "'>
					<td style='text-align:right'>" . $no . "</td>
					<td style='text-align:center'>
						<img src='images/" . $ingChat . ".png' onclick=\"loadPPChat('" . $nopp . "','" . $val['kodebarang'] . "',event);\" class='resicon'>
					</td>
					<td style='cursor:pointer;text-align:center' onclick=showdocpakaibarang('" . $tgldari . "','" . tanggalnormal($tanggalht) . "','" . substr($nopp, -4) . "','" . $val['kodebarang'] . "',event) title='Detail Pemakaian Barang'><font color=blue>" . $val['kodebarang'] . "</font></td>
					<td style=cursor:pointer onclick=showdocpembelianterakhir('" . $tgldari . "','" . tanggalnormal($tanggalht) . "','" . substr($nopp, -4) . "','" . $val['kodebarang'] . "',event) title='Detail Pemakaian Barang'><font color=blue>" . $namabarang . "</font></td>
					<td style='text-align:center'>" . $val['satuanpp'] . "</td>
					<td style='text-align:right'>" . hidezerodecimal($val['jumlahpp'], 2) . "</td>
					<td style='text-align:right'>" . hidezerodecimal($val['jumlah'], 2) . "</td>
					<td style='text-align:left'>" . $val['prioritas'] . " - " . $prioritas . "</td>
					<td style='text-align:center;cursor:pointer' onclick=showdocsparepart('" . $prddari . "','" . $prdsampai . "','" . substr($nopp, -4) . "','" . $val['kodevhc'] . "',event)><font color=blue>" . $noplat . "</font></td>
					<td align=center>" . hidezerodecimal($val['kmhm'], 2) . "</td>
					<td align=center>" . tanggalnormal($tanggalht) . "</td>
					<td align=center>" . tanggalnormal($val['tgl_sdt']) . "</td>
					<td align=center>" . hidezerodecimal($val['stock'], 2) . "</td>
					<td align=right>" . hidezerodecimal($val['hargasatuan'], 2) . "</td>
					<td align=right>" . hidezerodecimal($val['realisasi'], 2) . "</td>
					<td align=right>" . hidezerodecimal($val['anggaran'], 2) . "</td>
					<td style='text-align:center'>" . $nopo . "</td>
					<td style='text-align:center;min-width:80px'>" . $tglpo . "</td>
					<td>" . $val['keterangan'] . "</td>
				</tr>";
			}
		}
		$tab .= "</tbody>
		</table>
		</div>";

		## GET FILE UPLOAD
		$tab .= "<br>
		<div style='width:auto;overflow:auto;'>
			<table border=0 cellspacing=1 class=sortable>
				<thead>
				<tr style='font-weight:bold'>
					<td align='center'>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>Kriteria</td>
					<td align='center'>Filename</td>
					<td align='center'>Action</td>
				</tr>
				</thead>
				<tbody id='listfilesview'>
				</tbody>
			</table>
		</div>";

		$tab .= "<br />";

		## GET DETAIL ANGGARAN
		$tab .= "<div id=dtFormDetail style=\"overflow:auto;\"></div>";

		echo $tab;
		break;



	case 'cariBarangDlmDtBs':
		$tab = "<link rel=stylesheet type=text/css href='style/generic.css'>";
		$tab .= "
			<table class=sortable cellspacing=1 cellpadding=5  border=0>
				<thead>
				<tr class=rowheader>
					<th align=center>No.</th>
					<th align=center>" . $_SESSION['lang']['kelompokbarang'] . "</th>
					<th align=center>" . $_SESSION['lang']['subkelompokbarang'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
					<th>" . $_SESSION['lang']['namabarang'] . "</th>
					<th>" . $_SESSION['lang']['noakun'] . " " . $_SESSION['lang']['budget'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['saldo'] . "</th>
					<th align=center>" . $_SESSION['lang']['hargabarang'] . "</th>
					<th align=center>" . $_SESSION['lang']['realisasi'] . "</th>
					<th align=center>" . $_SESSION['lang']['anggaran'] . "<br>sdbi</th>
					<th align=center>" . $_SESSION['lang']['anggaran'] . "<br>Setahun</th>
				</tr>
				</thead>
				<tbody>";

		$where = "";
		$disakun = "disabled";
		if ($tipe == 'PR' || $tipe == 'NR') {
			$where .= " and left(kodebarang,1)='3'";
		}

		if ($tipe == 'SR') {
			$where .= " and left(kodebarang,1)='8'";
			$disakun = "";
		}

		if ($tipe == 'CP') {
			$where .= " and left(kodebarang,1)='9'";
		}

		$str = "select left(kodebarang,3) as kelompokbarang from " . $dbname . ".log_prapodt where nopp='" . $nopp . "' limit 1";
		$res = fetchdata($str);
		if (count($res) > 0) {
			// $where.=" and kelompokbarang='".$res[0]['kelompokbarang']."'";
		}

		$kodept = getNamaOrg($_POST['rkd_bag'], 'induk');

		## GET SALDO BARANG
		$arrsaldoqty = array();
		/* $strx="select kodebarang, sum(saldoqty) as saldoqty from ".$dbname.".log_5masterbarangdt where kodeorg='".$_SESSION['empl']['kodeorganisasi']."' and kodegudang like '".$rkd_bag."%' group by kodebarang";
		$resx=fetchdata($strx);
		foreach($resx as $val){
			$arrsaldoqty[$val['kodebarang']]=$val['saldoqty'];
		} */

		$strx = "select kodebarang, sum(saldoakhirqty) as saldoqty from " . $dbname . ".log_5saldobulanan where kodegudang like '" . $rkd_bag . "%' and periode in (select max(periode) from " . $dbname . ".log_5saldobulanan where kodegudang like '" . $rkd_bag . "%')group by kodebarang";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrsaldoqty[$val['kodebarang']] = $val['saldoqty'];
		}


		## GET PENERIMAAN BARANG YANG BELUM DI POSTING (BARANG MASUK,TERIMA MUTASI)
		$arrqtynotpostedin = array();
		$strx = "select sum(b.jumlah) as jumlah, b.kodebarang as kodebarang FROM " . $dbname . ".log_transaksiht a left join " . $dbname . ".log_transaksidt b on a.notransaksi=b.notransaksi where a.kodept='" . $kodept . "' and kodegudang like '" . $rkd_bag . "%' and a.tipetransaksi<5 and a.post=0 group by b.kodebarang";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrqtynotpostedin[$val['kodebarang']] = $val['jumlah'];
		}

		## GET PENGELUARAN BARANG YANG BELUM DIPOSTING
		$arrqtynotposted = array();
		$strx = "select sum(b.jumlah) as jumlah, b.kodebarang as kodebarang FROM " . $dbname . ".log_transaksiht a left join " . $dbname . ".log_transaksidt b on a.notransaksi=b.notransaksi where a.kodept='" . $kodept . "' and kodegudang like '" . $rkd_bag . "%'  and a.tipetransaksi>4 and a.post=0 group by b.kodebarang";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrqtynotposted[$val['kodebarang']] = $val['jumlah'];
		}

		## GET VALIDASI BUDGET
		$arrvalbudget = validasibudget($tipe, $rkd_bag);

		## GET JUMLAH BUDGET

		$arrqtybudget = $arrbudgetsdbi = array();
		$crtahun = explode('-', $rtgl_pp);

		$e = "(";
		$s = "(";
		for ($i = 1; $i <= intval($crtahun[1]); $i++) {
			$r = "rp" . addZero($i, 2);
			$n = "k" . addZero($i, 2);
			if ($i < intval($crtahun[1])) {
				$e .= $r . "+";
				$s .= $n . "+";
			} else {
				$e .= $r;
				$s .= $n;
			}
		}
		$e .= ")";
		$s .= ")";

		$strx = "select kelompokbarang, jumlah, " . $e . " as sdbi from " . $dbname . ".bgt_procrutment_vw where unit='" . $rkd_bag . "' and tahunbudget='" . $crtahun[2] . "' and kelompokbarang not like '9%'";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			@$arrqtybudget[substr($val['kelompokbarang'], 0, $arrvalbudget['digit'])] += $val['jumlah'];
			//$arrbudgetsdbi[substr($val['kelompokbarang'],0,$arrvalbudget['digit'])]+=$val['sdbi']; #jika pake sebaran
			@$arrbudgetsdbi[substr($val['kelompokbarang'], 0, $arrvalbudget['digit'])] += $val['jumlah']; #jika pake total setahun
		}
		$strx = "select kodebarang, sum(rupiah) as jumlah, " . $e . " as sdbi from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_distribusi b on a.kunci=b.kunci where a.kodeorg like '" . $rkd_bag . "%' and a.tahunbudget='" . $crtahun[2] . "' and a.kodebarang like '9%' and (a.pta='BGT' or (a.pta='PTA' and a.statuspta='1')) and a.kodebarang!='' group by a.kodebarang";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrqtybudget[substr($val['kodebarang'], 0, $arrvalbudget['digit'])] += $val['jumlah'];
			// $arrbudgetsdbi[substr($val['kodebarang'],0,$arrvalbudget['digit'])]+=$val['sdbi']; #jika pake sebaran
			$arrbudgetsdbi[substr($val['kodebarang'], 0, $arrvalbudget['digit'])] += $val['jumlah']; #jika pake total setahun
		}

		#kapital
		$strx = "select kodebarang, hargatotal as jumlah, " . $s . " as sdbi from " . $dbname . ".bgt_kapital where kodeunit='" . $rkd_bag . "' and tahunbudget='" . $crtahun[2] . "' and kodebarang!='' and (pta='BGT' or (pta='PTA' and statuspta='1'))";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrqtybudget[substr($val['kodebarang'], 0, $arrvalbudget['digit'])] += $val['jumlah'];
			// $arrbudgetsdbi[substr($val['kodebarang'],0,$arrvalbudget['digit'])]+=$val['sdbi']; #jika pake sebaran
			$arrbudgetsdbi[substr($val['kodebarang'], 0, $arrvalbudget['digit'])] += $val['jumlah']; #jika pake total setahun
		}




		## GET HARGA TERAKHIR
		$arrdtakhir = array();
		$strx = "select kodebarang,hargasatuan,tanggal,nopo from " . $dbname . ".log_5hargaterakhir where unit='' and status='1' order by tanggal desc";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrdtakhir[$val['kodebarang']]['harga'] = $val['hargasatuan'];
			$arrdtakhir[$val['kodebarang']]['tanggal'] = $val['tanggal'];

			#cek pakai konversi gak ?
			$sql = "select * from " . $dbname . ".log_5stkonversi where kodebarang='" . $val['kodebarang'] . "'";
			$req = fetchdata($sql);
			if (count($req) > 0 and $val['nopo'] != '') {
				$query = "select * from " . $dbname . ".log_podt where kodebarang='" . $val['kodebarang'] . "' and nopo='" . $val['nopo'] . "'";
				$queryH = fetchdata($query);
				if ($queryH[0]['satuan'] != $req[0]['darisatuan']) {
					if (count($queryH) > 0) {
						@$jlhpo = @$queryH[0]['jumlahpesan'] / @$req[0]['jumlah'];
						$arrdtakhir[$val['kodebarang']]['harga'] = $val['hargasatuan'] / $jlhpo;
					}
				}
			}
		}

		$tipeorganisasi = getNamaOrg($rkd_bag, 'tipe');

		if ($tipe == 'SR') {
			$whr = "";
			if ($tipeorganisasi == 'KEBUN') {
				$whr .= " and noakun like '7%'";
			}
			if ($tipeorganisasi == 'PABRIK') {
				$whr .= " and noakun like '7%'";
			}
			if ($tipeorganisasi == 'TC') {
				$whr .= " and noakun like '82%'";
			}
			if ($tipeorganisasi == 'RND') {
				$whr .= " and noakun like '82%'";
			}
			if ($tipeorganisasi == 'KANWIL') {
				$whr .= " and noakun like '82%'";
			}
			if ($tipeorganisasi == 'BULKING') {
				$whr .= " and noakun like '81%'";
			}
			if ($tipeorganisasi == 'HOLDING') {
				$whr .= " and noakun like '82%'";
			}

			# nomor akun
			$str = "select * from " . $dbname . ".keu_5akun where 1=1 " . $whr . " and noakun in (select distinct noakun from " . $dbname . ".bgt_budget where kodeorg like '" . $rkd_bag . "%' and tahunbudget='" . $crtahun[2] . "' and (pta='BGT' or (pta='PTA' and statuspta='1'))) order by noakun";
			$resnoakun = fetchdata($str);


			# apakah SR ini sebelumnya sudah ada nomor akunnya ???
			$datalama = array();
			if ($param['noakun'] != "undefined" and $param['kodebarang'] != 'undefined') {
				$datalama[$param['noakun']][$param['kodebarang']] = $param['kodebarang'];
			}

			$sql = "select distinct noakunbudget, kodebarang from " . $dbname . ".log_prapodt where nopp like '%" . $crtahun[2] . "%" . $rkd_bag . "%' and noakunbudget!=''";
			$req = fetchdata($sql);
			foreach ($req as $bar) {
				$akunprlama[$bar['kodebarang']] = $bar['noakunbudget'];
				$datalama[$bar['noakunbudget']][$bar['kodebarang']] = $bar['kodebarang'];
			}

			$strx = "select a.noakun, sum(rupiah) as jumlah, " . $e . " as sdbi from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_distribusi b on a.kunci=b.kunci where a.kodeorg like '" . $rkd_bag . "%' " . $whr . " and a.tahunbudget='" . $crtahun[2] . "' and (a.pta='BGT' or (a.pta='PTA' and a.statuspta='1')) group by a.noakun";
			$resx = fetchdata($strx);
			foreach ($resx as $val) {
				foreach ($datalama as $akun => $v) {
					foreach ($v as $kodbarang) {
						if ($val['noakun'] == $akun) {
							$arrqtybudget[substr($kodbarang, 0, $arrvalbudget['digit'])] += $val['jumlah'];
							// $arrbudgetsdbi[substr($kodbarang,0,$arrvalbudget['digit'])]+=$val['sdbi']; #jika pake sebaran
							$arrbudgetsdbi[substr($kodbarang, 0, $arrvalbudget['digit'])] += $val['jumlah']; #jika pake total setahun
						}
					}
				}
			}
		}



		$str = "select * from " . $dbname . ".log_5masterbarang where 1=1 " . $where . " and (namabarang like '%" . $txtfind . "%' or kodebarang like '%" . $txtfind . "%') and inactive='0'";
		$res = fetchdata($str);
		$countitem = count($res);
		if ($countitem <= 0) {
			$tab .= "<td class=firsttd  align=center colspan=15>" . $_SESSION['lang']['datanotfound'] . "</td>";
		} else {
			$no = 0;
			foreach ($res as $val) {
				$no++;

				## GET SALDO BARANG
				@$saldoqty = $arrsaldoqty[$val['kodebarang']];
				if ($saldoqty == '') {
					$saldoqty = 0;
				}

				## GET PENERIMAAN BARANG YANG BELUM DI POSTING (BARANG MASUK,TERIMA MUTASI)
				@$qtynotpostedin = $arrqtynotpostedin[$val['kodebarang']];
				if ($qtynotpostedin == '') {
					$qtynotpostedin = 0;
				}

				## GET PENGELUARAN BARANG YANG BELUM DIPOSTING
				@$qtynotposted = $arrqtynotposted[$val['kodebarang']];
				if ($qtynotposted == '') {
					$qtynotposted = 0;
				}

				## GET JUMLAH BUDGET
				// if(substr($val['kodebarang'],0,1)=='9'){
				// $qtybudget = $arrqtybudget[$val['kodebarang']];
				// }else{					
				// $qtybudget = $arrqtybudget[substr($val['kodebarang'],0,3)];
				// }

				#penguncian vs budget per kode barang, update permintaan pak danny tanggal 11/01/2022 jam 14:24
				#update berdasarkan setup
				@$qtybudget   = $arrbudgetsdbi[substr($val['kodebarang'], 0, $arrvalbudget['digit'])];
				@$qtybudgetthn = $arrqtybudget[substr($val['kodebarang'], 0, $arrvalbudget['digit'])];


				if ($qtybudget == '') {
					$qtybudget = 0;
				}
				if ($qtybudgetthn == '') {
					$qtybudgetthn = 0;
				}

				## GET HARGA TERAKHIR
				if ($val['hargasatuan'] != '') {
					$exphargasatuan = explode(',', $val['hargasatuan']);
					if (in_array($rkd_bag, $exphargasatuan)) {
						$strx = "select kodebarang,hargasatuan,tanggal, nopo from " . $dbname . ".log_5hargaterakhir where unit='" . $rkd_bag . "' and status='1' order by tanggal desc";
						$resx = fetchdata($strx);
						foreach ($resx as $valx) {
							$arrdtakhir[$valx['kodebarang']]['harga'] = $valx['hargasatuan'];
							$arrdtakhir[$valx['kodebarang']]['tanggal'] = $valx['tanggal'];

							#cek pakai konversi gak ?
							$sql = "select * from " . $dbname . ".log_5stkonversi where kodebarang='" . $valx['kodebarang'] . "'";
							$req = fetchdata($sql);
							if (count($req) > 0) {
								$query = "select * from " . $dbname . ".log_podt where kodebarang='" . $valx['kodebarang'] . "' and nopo='" . $valx['nopo'] . "'";
								$queryH = fetchdata($query);
								$jlhpo = $queryH[0]['jumlahpesan'] / $req[0]['jumlah'];

								$arrdtakhir[$valx['kodebarang']]['harga'] = $valx['hargasatuan'] / $jlhpo;
							}
						}
					}
				}
				@$hargabarang = ($arrdtakhir[$val['kodebarang']]['harga'] == '' ? 0 : $arrdtakhir[$val['kodebarang']]['harga']);
				@$tglhargatrk = $arrdtakhir[$val['kodebarang']]['tanggal'];

				## GET NILAI REALISASI
				$nilairealisasi = getrealproc($rkd_bag, $val['kodebarang'], $crtahun[2]);

				## TOTAL SALDO
				$saldoqty = ($saldoqty + $qtynotpostedin) - $qtynotposted;

				$optNm = makeOption($dbname, 'log_5klbarang', 'kode,kelompok', "kode='" . substr($val['kodebarang'], 0, 3) . "'");
				$optsklbrg = makeOption($dbname, 'log_5subklbarang', 'kode,namasubkelompok', "kode='" . substr($val['kodebarang'], 0, 5) . "'");

				if ($val['inactive'] == '1') {
					$tab .= "<tr bgcolor='red' style='cursor:pointer;'  title='Inactive' >";
					$val['namabarang'] = $val['namabarang'] . " [Inactive]";
					$bgr = " bgcolor='red'";
				} else {
					$click = "onclick=\"setBrg('" . $val['kodebarang'] . "','" . changeKutipChar($val['namabarang']) . "','" . $val['satuan'] . "','" . $saldoqty . "','" . number_format($hargabarang, 2) . "','" . number_format($nilairealisasi, 2) . "','" . number_format($qtybudget, 2) . "','" . $no . "')\" title='Click' ";
					$tabx = "<tr class=rowcontent style='cursor:pointer;'>";
					if ($arrvalbudget['status'] == '1') {
						if ($qtybudget <= 0) {
							$click = "";
							$tabx = "<tr bgcolor='#ebe8e8' style='cursor:pointer;'  title='Belum Ada Anggaran' >";
							//$tabx="<tr class=rowcontent style='cursor:pointer;' title='Belum Ada Anggaran' onclick=\"setBrg('".$val['kodebarang']."','".changeKutipChar($val['namabarang'])."','".$val['satuan']."','".$saldoqty."','".number_format($hargabarang,2)."','".number_format($nilairealisasi,2)."','".number_format($qtybudget,2)."')\">";
						}

						$nilaitoleransi = $qtybudget * ($arrvalbudget['toleransi'] / 100);
						if ($nilairealisasi > ($qtybudget + $nilaitoleransi)) {
							$click = "";
							$tabx = "<tr bgcolor='#C3ACE1' style='cursor:pointer;color:red'  title='Nilai Realisasi lebih besar dari Nilai Anggaran, toleransi = " . $arrvalbudget['toleransi'] . "%' >";
							//$tabx="<tr class=rowcontent style='cursor:pointer;'  title='Nilai Realisasi lebih besar dari Nilai Anggaran' onclick=\"setBrg('".$val['kodebarang']."','".changeKutipChar($val['namabarang'])."','".$val['satuan']."','".$saldoqty."','".number_format($hargabarang,2)."','".number_format($nilairealisasi,2)."','".number_format($qtybudget,2)."')\">";							
						}
					}
					/* if($hargabarang <= 0){
						// $tabx="<tr bgcolor='#F7B882' style='cursor:pointer;'  title='Belum Ada Harga Barang' >";
						$tabx="<tr class=rowcontent style='cursor:pointer;'  title='Belum Ada Harga Barang' onclick=\"setBrg('".$val['kodebarang']."','".changeKutipChar($val['namabarang'])."','".$val['satuan']."','".$saldoqty."','".number_format($hargabarang,2)."','".number_format($nilairealisasi,2)."','".number_format($qtybudget,2)."')\">";
					}
					if($tglhargatrk!=''){
						$selisitgl = selisitgl(tanggalsystem($rtgl_pp),$tglhargatrk);
						if($selisitgl >= 180){
							// $tabx="<tr bgcolor='#87B1F7' style='cursor:pointer;'  title='Harga Pembelian terakhir sudah melebihi 180 Hari\nPembelian terakhir : ".tanggalnormal($tglhargatrk)."' >";
							$tabx="<tr class=rowcontent style='cursor:pointer;'  title='Harga Pembelian terakhir sudah melebihi 180 Hari\nPembelian terakhir : ".tanggalnormal($tglhargatrk)."' onclick=\"setBrg('".$val['kodebarang']."','".changeKutipChar($val['namabarang'])."','".$val['satuan']."','".$saldoqty."','".number_format($hargabarang,2)."','".number_format($nilairealisasi,2)."','".number_format($qtybudget,2)."')\">";
						}
					} */
					$tab .= $tabx;
				}

				$optakun = "<option value=''></option>";
				$disakun = "disabled";
				$dispakun = "display:none;";
				if ($tipe == 'SR') {
					$disakun = "";
					$dispakun = "";
					foreach ($resnoakun as $row) {
						$d = substr($row['noakun'], 0, 3);
						if ($d != $n) {
							$optakun .= "<optgroup label='" . getNamaAkun($d) . "'>";
						}
						$sel = "";
						if ($param['noakun'] == $row['noakun'] and $param['kodebarang'] == $val['kodebarang']) {
							$sel = "selected";
						}
						if ($akunprlama[$val['kodebarang']] == $row['noakun']) {
							$sel = "selected";
							$disakun = "disabled";
						}
						$optakun .= "<option value=" . $row['noakun'] . " " . $sel . ">" . $row['noakun'] . " - " . $row['namaakun'] . "</option>";
						$n = $d;
						if ($d != $n) {
							$optakun .= "</optgroup>";
						}
					}
				}

				$tab .= "<td class=firsttd " . $click . " align=center>" . $no . "</td>
					<td " . $click . ">" . $optNm[substr($val['kodebarang'], 0, 3)] . "</td>
					<td " . $click . ">" . $optsklbrg[substr($val['kodebarang'], 0, 5)] . "</td>
					<td " . $click . " align=center>" . $val['kodebarang'] . "</td>
					<td " . $click . ">" . $val['namabarang'] . "</td>
					<td><select " . $disakun . " style=\"width:150px;" . $dispakun . "\" onchange=findBrg('" . $val['kodebarang'] . "',this.value); title=\"Untuk menampilkan data budget.\" id=noakun" . $no . ">" . $optakun . "</select></td>
					<td " . $click . " align=center>" . $val['satuan'] . "</td>
					<td " . $click . " align=right>" . hidezerodecimal($saldoqty, 2, ',', '.') . "</td>
					<td " . $click . " align=right>" . hidezerodecimal($hargabarang, 2, ',', '.') . "</td>
					<td " . $click . " align=right>" . hidezerodecimal($nilairealisasi, 2, ',', '.') . "</td>
					<td " . $click . " align=right style=color:blue;><b>" . hidezerodecimal($qtybudget, 2, ',', '.') . "</b></td>
					<td " . $click . " align=right>" . hidezerodecimal($qtybudgetthn, 2, ',', '.') . "</td>
				</tr>";
			}
		}

		$tab .= "</tbody>
			<tfoot>
			</tfoot>
		</table>
		<br><i>*Validasi anggaran = " . $arrvalbudget['digit'] . " digit kode barang.</i>
		<br>
		<i>*Saldo(termasuk divisi) = [saldo(termasuk divisi) saat ini + penerimaan yang belum di posting(termasuk divisi)] - pengeluaran yang belum di posting(termasuk divisi)</i>
		<ul>Untuk tipe Service Request jika budget tidak ada silahkan pilih nomor akun dimana Budget SR tersebut.</ul>
		";

		echo $tab;
		break;

	case 'cekdokumen':
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $nopp . "'";
		$res = fetchData($str);
		$file = count($res);
		$kdbrgpp = array();
		$str = "select * from " . $dbname . ".log_prapodt where nopp='" . $nopp . "'";
		$res2 = fetchData($str);
		if (count($res2) != 0) {
			foreach ($res2 as $key => $bar) {
				$kdbrgpp[$bar['kodebarang']] = $bar['kodebarang'];
			}
		} else {
			exit('warning:' . $_SESSION['lang']['detail'] . " " . $_SESSION['lang']['kosong']);
		}


		//ambil list dari pend pp
		$where = '';
		$jlh = 0;
		$kdbrgdoc = "";
		if (count($kdbrgpp) != 0) {
			$where = " and a.kodebarang in('" . implode("','", $kdbrgpp) . "')";
		}
		if ($where != "") {
			$str = "select a.*,b.namabarang from " . $dbname . ".log_5docassign a left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang where 1=1 " . $where . "";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$kdbrgdoc = $jlh = '';
			while ($bar = $res->fetch()) {
				$jlh += 1;
				$kdbrgdoc = $kdbrgdoc . $bar['namabarang'] . ", ";
			}
		}

		echo $jlh . "####" . $kdbrgdoc . "####" . $file;
		//exit('warning');
		break;

	case 'formPersetujuan':
		$kd = substr($nopp, 17, 2);
		$unit = substr($nopp, 15, 4);

		##GET REQUESTER
		$str = "select requester from " . $dbname . ".log_prapoht where nopp='" . $nopp . "'";
		$res = fetchdata($str);
		$requester = $res[0]['requester'];
		$optdepartmen = makeOption($dbname, 'datakaryawan', 'karyawanid,bagian', "karyawanid='" . $requester . "'");
		$departemen = $optdepartmen[$requester];

		##CEK PER DEPARTEMEN
		$str = "select count(kodeunit) as kodeunit from " . $dbname . ".setup_approval where kodeunit='" . $unit . "' and jenispersetujuan='PR' and departemen='" . $departemen . "' and level='1'";
		$res = fetchdata($str);
		$perdepartemen = $res[0]['kodeunit'];
		$where = "";
		if ($perdepartemen > 0) {
			$where .= " and a.departemen='" . $departemen . "'";
		} else {
			$where .= " and a.departemen=''";
		}

		$noppapr = '';
		$strk = "select * from " . $dbname . ".log_prapodt where nopp='" . $nopp . "'";
		$res = fetchdata($strk);
		if (count($res) > 0) {
			$noppapr = $res[0]['kodeproject'];
		}
		##  CEK KONDISI JIKA SUDAH MENGAJUKAN APPROVAL PROJECT

		// if ($noppapr!='') {  
		// 	$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".project_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='".$_SESSION['standard']['userid']."'  and a.level='1'  and kode='".$noppapr."' order by b.namakaryawan asc";
		// } else { 
		// 	$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='PR' and a.level='1' and a.kodeunit='".$unit."' ".$where." order by b.namakaryawan asc"; 
		// } 
		if ($noppapr != '') {
			$str = "select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from " . $dbname . ".project_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='" . $_SESSION['standard']['userid'] . "'  and a.level='1'  and kode='" . $noppapr . "' order by b.namakaryawan asc";
			$rescek = fetchdata($str);
			if (count($rescek) > 0) {
				$str = "select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from " . $dbname . ".project_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='" . $_SESSION['standard']['userid'] . "'  and a.level='1'  and kode='" . $noppapr . "' order by b.namakaryawan asc";
			} else {
				$str = "select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='" . $_SESSION['standard']['userid'] . "' and a.jenispersetujuan='PR' and a.level='1' and a.kodeunit='" . $unit . "' " . $where . " order by b.namakaryawan asc";
			}
		} else {
			$str = "select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='" . $_SESSION['standard']['userid'] . "' and a.jenispersetujuan='PR' and a.level='1' and a.kodeunit='" . $unit . "' " . $where . " order by b.namakaryawan asc";
		}
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry = "";
		while ($rkry = $res->fetch()) {
			$optKry .= "<option value='" . $rkry['karyawanid'] . "'>" . $rkry['namakaryawan'] . " [" . $rkry['lokasitugas'] . "]</option>";
		}

		$tab = "<fieldset style=width:300px;>
			<legend>" . $_SESSION['lang']['pengajuan'] . "</legend>";
		$tab .= "<table cellspacing=1 border=0>
			<tr>
				<td>No. PP/PR</td>
				<td>:</td>
				<td><input class=myinputtext style=width:165px type=\"text\" id=\"fnopp\" name=\"fnopp\" disabled value='" . $nopp . "' /></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['kepada'] . "</td>
				<td>:</td>
				<td><select style=width:170px  id=\"karywn_id\" name=\"karywn_id\">" . $optKry . "</select></td>
			</tr>
			<input type=\"hidden\" id=\"cls_stat\" name=\"cls_stat\" value=0 />
			<tr>
				<td><td><td>
					<button class=mybutton onclick=reset_data_setuju()>" . $_SESSION['lang']['cancel'] . "</button>
					<button class=mybutton onclick=save_persetujuan() >" . $_SESSION['lang']['diajukan'] . "</button>
				</td>
			</tr>
		</table>
		</fieldset>";
		echo $tab;
		break;

	case 'insertdt': #mahe

		if ($jmlhdiminta == '0' || $jmlhdiminta < 0) {
			exit("Gagal, Harga tidak boleh kurang dari 0.");
		}

		if ($kodebarang == '' || $jmlhdiminta == '' || $jmlhdiminta == '0' || $prioritas == '') {
			exit("Gagal, Form isian belum lengkap.");
		}

		if ($kodevhc == '' and cekkodevhc($param) == 1) {
			exit("Gagal, Untuk barang ini harus mengisikan kode kendaraan.");
		}

		if ($tipe == 'CP') {
			if ($kd_project == '') {
				exit("Gagal, Kode Project harus diisi jika pilih Tipe Capex Request (ASET)");
			}
		}

		$tglsdt = tanggalsystem($tglsdt);
		$tglpp = tanggalsystem($tglheader);

		$days = selisitgl($tglsdt, $tglpp);

		if ($days < 7) {
			exit("Gagal, Tanggal SDT(" . tanggalnormal($tglsdt) . ") harus lebih besar 7 hari dari tanggal PR/SR(" . tanggalnormal($tglpp) . ")");
		}

		### CEK kodeproject yang sudah pernah diinput menggunakan approval atau tidak

		$strpr = "SELECT kodeproject,dgnapproval from " . $dbname . ".log_prapodt a LEFT JOIN " . $dbname . ".project b ON a.kodeproject=b.kode where nopp='" . $nopp . "'";
		$resx = fetchdata($strpr);
		$valdgnapproval = $resx[0]['dgnapproval'];

		if ($valdgnapproval == '1') {
			if (count($resx) > 0) {
				$valkdproject = $resx[0]['kodeproject'];
				if ($valkdproject != $kd_project) {
					exit("Gagal, Kode Project harus sama dengan kodeproject yang sebelumnya !");
				}
			}
		}

		// echo"---valkode nya".$val['kodeproject'];
		// echo"---inikode nya".$val;
		// echo"---kode nya".$kd_project;

		$data = $_POST;
		$tglsdt = tanggalsystem($tglsdt);
		$tglpp = tanggalsystem($tglheader);

		## GET VALIDASI BUDGET
		$arrvalbudget = validasibudget($tipe, $unit);


		# Check Valid Data
		// $starttime=strtotime($tglpp); ## Tanggal PP
		// $endtime=strtotime($tglsdt); ## Tanggal SDT

		$days = selisitgl($tglsdt, $tglpp);

		if ($days < 7) {
			// exit("Gagal, Tanggal SDT(".tanggalnormal($tglsdt).") harus lebih besar 7 hari dari tanggal PR/SR(".tanggalnormal($tglpp).")");
		}

		$realkdbrg = $bgtkdbrg = 0;

		## GET JUMLAH BUDGET
		$arrqtybudget = array();
		$strx = "select kelompokbarang, jumlah from " . $dbname . ".bgt_procrutment_vw where unit='" . $unit . "' and tahunbudget='" . substr($tglpp, 0, 4) . "' and kelompokbarang not like '9%'";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrqtybudget[$val['kelompokbarang']] += $val['jumlah'];
		}
		#kapital
		$strx = "select kodebarang, sum(rupiah) as jumlah from " . $dbname . ".bgt_budget where kodeorg like '" . $unit . "%' and tahunbudget='" . substr($tglpp, 0, 4) . "' and kodebarang like '9%' and (pta='BGT' or (pta='PTA' and statuspta='1')) group by kodebarang";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrqtybudget[$val['kodebarang']] += $val['jumlah'];
		}

		$strx = "select kodebarang, hargatotal as jumlah from " . $dbname . ".bgt_kapital where kodeunit='" . $unit . "' and tahunbudget='" . substr($tglpp, 0, 4) . "' and kodebarang!='' and (pta='BGT' or (pta='PTA' and statuspta='1'))";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrqtybudget[$val['kodebarang']] += $val['jumlah'];
		}


		/* $sql = "select kodebarang, sum(hartot) as hartot from ".$dbname.".log_transaksi_vw where tipetransaksi='1' and post='1' and left(kodegudang,4)='".$unit."' and kodebarang = '".$kodebarang."' and tanggal like '".substr($tglpp,0,4)."%' group by left(kodegudang,4), kodebarang";
		$req = fetchdata($sql);
		foreach($req as $bar){
			$realkdbrg+=$bar['hartot'];
		} */

		$bgtkdbrg = $arrqtybudget[$kodebarang];
		$timenow    = date('Y-m-d H:i:s');
		$stok       = str_replace(',', '', $stok);
		$jmlhdiminta = str_replace(',', '', $jmlhdiminta);
		$realisasi  = str_replace(',', '', $realisasi);
		$hargasatuan = str_replace(',', '', $hargasatuan);
		$budget     = str_replace(',', '', $budget);
		$realkdbrg  = getrealproc($unit, $kodebarang, substr($tglpp, 0, 4));
		if ($param['noakunbudget'] != '') {
			$realkdbrg = getrealakun($unit, $param['noakunbudget'], substr($tglpp, 0, 4)) + $realkdbrg;
			$bgtkdbrg = $budget;
		}


		if ($hargasatuan == 0) {
			exit("Gagal, Harga satuan belum ada.");
		}

		if ($arrvalbudget['status'] == '1') {
			$nilaitoleransi = intval($budget * ($arrvalbudget['toleransi'] / 100));
			if (intval($realkdbrg + ($jmlhdiminta * $hargasatuan)) > intval($budget + $nilaitoleransi)) {
				exit("error : Jumlah realisasi dan jumlah diminta telah melebihi jumlah budget.\nJumlah realisasi = " . number_format($realkdbrg) . "\nJumlah diminta = " . number_format($jmlhdiminta * $hargasatuan) . "\nBudget + toleransi tahun " . substr(tanggalnormal($tglpp), -4) . " = " . number_format($budget + $nilaitoleransi) . "");
			}
		}


		## INSERT DETAIL
		$data['tgl_sdt'] = tanggalsystem($data['tgl_sdt']);
		$str = "insert into " . $dbname . ".log_prapodt (nopp,kodebarang,stock,jumlah,realisasi,hargasatuan,satuanpp,satuankonversi,keterangan,anggaran,tgl_sdt,prioritas,create_po,pembelian,lokalpusat,status,tglAlokasi,alasanstatus,purchaser,ditolakoleh,kodevhc,jumlahpp,updateby,updatetime,keteranganubah,kmhm,realperbarang,bgtperbarang,kodeproject,noakunbudget) values ('" . $nopp . "','" . $kodebarang . "','" . $stok . "','" . $jmlhdiminta . "','" . $realisasi . "','" . $hargasatuan . "','" . $satuan . "','','" . $keterangan . "','" . $budget . "','" . $tglsdt . "','" . $prioritas . "','0','0','0','0','0000-00-00','','0','0','" . $kodevhc . "', '" . $jmlhdiminta . "','" . $_SESSION['standard']['userid'] . "','" . $timenow . "','','" . $kmhm . "','" . $realkdbrg . "', '" . $bgtkdbrg . "', '" . $kd_project . "', '" . $param['noakunbudget'] . "')";
		//  exit("error".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			$msgerr = $e->getMessage();
			if (strpos($msgerr, 'Duplicate entry') >= 0) {
				$msgerr = "Item ini sudah pernah diinput.";
			}
			print " Gagal : " . $msgerr . "\n";
			die();
		}

		break;

	case 'updatedt':
		if ($kodebarang == '' || $jmlhdiminta == '' || $jmlhdiminta == '0' || $prioritas == '') {
			exit("Gagal, Form isian belum lengkap.");
		}

		if ($kodevhc == '' and cekkodevhc($param) == 1) {
			exit("Gagal, Untuk barang ini harus mengisikan kode kendaraan.");
		}

		if ($kodevhc != '') {
			if ($kmhm == '') {
				// exit("Gagal, Kmhm harus diisi jika menggunakan kendaraan");
			}
		}


		### CEK kodeproject yang sudah pernah diinput
		// $strpr="SELECT kodeproject from ".$dbname.".log_prapodt where nopp='".$nopp."'";
		// $resx=fetchdata($strpr); 
		// if (count($resx)>0) {
		// 	$val=$resx[0]['kodeproject'];
		// 	if($val!=$kd_project){ 
		// 		exit("Gagal, Kode Project harus sama dengan kodeproject yang sebelumnya !"); 
		// 	}
		// }

		$data = $_POST;
		$tglsdt = tanggalsystem($tglsdt);
		$tglpp = tanggalsystem($tglheader);

		$days = selisitgl($tglsdt, $tglpp);

		if ($days < 7) {
			exit("Gagal, Tanggal SDT(" . tanggalnormal($tglsdt) . ") harus lebih besar 7 hari dari tanggal PR/SR(" . tanggalnormal($tglpp) . ")");
		}

		$str = "insert into " . $dbname . ".log_prapodt (nopp,kodebarang,stock,jumlah,realisasi,hargasatuan,satuanpp,satuankonversi,keterangan,anggaran,tgl_sdt,prioritas,create_po,pembelian,lokalpusat,status,tglAlokasi,alasanstatus,purchaser,ditolakoleh,kodevhc,jumlahpp,updateby,updatetime,keteranganubah,kmhm,kodeproject,noakunbudget) values ('" . $nopp . "','" . $kodebarang . "','" . $stok . "','" . $jmlhdiminta . "','" . $realisasi . "','" . $hargasatuan . "','" . $satuan . "','','" . $keterangan . "','" . $budget . "','" . $tglsdt . "','" . $prioritas . "','0','0','0','0','0000-00-00','','" . $_SESSION['standard']['userid'] . "','0','" . $kodevhc . "','" . $jmlhdiminta . "','" . $_SESSION['standard']['userid'] . "','" . $timenow . "','','" . $kmhm . "','" . $kd_project . "','" . $param['noakunbudget'] . "')";

		## UPDATE DETAIL
		$timenow    = date('Y-m-d H:i:s');
		$stok       = str_replace(',', '', $stok);
		$jmlhdiminta = str_replace(',', '', $jmlhdiminta);
		$realisasi  = str_replace(',', '', $realisasi);
		$hargasatuan = str_replace(',', '', $hargasatuan);
		$budget     = str_replace(',', '', $budget);

		## GET VALIDASI BUDGET
		$arrvalbudget = validasibudget($tipe, $unit);
		$realkdbrg   = getrealproc($unit, $kodebarang, substr($tglpp, 0, 4));
		if ($param['noakunbudget'] != '') {
			$realkdbrg = getrealakun($unit, $param['noakunbudget'], substr($tglpp, 0, 4)) + $realkdbrg;
			$bgtkdbrg = $budget;
		}

		if ($arrvalbudget['status'] == '1') {
			$nilaitoleransi = $budget * ($arrvalbudget['toleransi'] / 100);
			if (($realkdbrg + ($jmlhdiminta * $hargasatuan)) > ($budget + $nilaitoleransi)) {
				exit("error : Jumlah realisasi dan jumlah diminta telah melebihi jumlah budget.");
			}
		}


		$query = "update " . $dbname . ".log_prapodt set stock='" . $stok . "', jumlah='" . $jmlhdiminta . "', realisasi='" . $realisasi . "', hargasatuan='" . $hargasatuan . "', satuanpp='" . $satuan . "', keterangan='" . $keterangan . "', anggaran='" . $budget . "', tgl_sdt='" . $tglsdt . "', prioritas='" . $prioritas . "', purchaser='" . $_SESSION['standard']['userid'] . "', kodevhc='" . $kodevhc . "', jumlahpp='" . $jmlhdiminta . "', updateby='" . $_SESSION['standard']['userid'] . "', updatetime='" . $timenow . "', kmhm='" . $kmhm . "', kodeproject='" . $kd_project . "', noakunbudget='" . $param['noakunbudget'] . "' where nopp='" . $nopp . "' and kodebarang='" . $kodebarang . "'";

		# Update Data
		try {
			$owlPDO->exec($query);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'listprsr':
		$no = 0;
		$tab = "";
		$str = "select * from " . $dbname . ".log_prapodt where nopp = '" . $nopp . "'";
		$res = fetchData($str);
		if (count($res) <= 0) {
			$tab .= "<tr class=rowcontent><td colspan=18 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			foreach ($res as $key => $val) {
				$no++;
				$optNamaBarang = makeOption($dbname, "log_5masterbarang", 'kodebarang,namabarang', "kodebarang='" . $val['kodebarang'] . "'");
				$optprioritas = makeOption($dbname, "log_5prioritas", 'kode,nama', "kode='" . $val['prioritas'] . "'");


				$tglbuatpp = makeOption($dbname, 'log_prapoht', 'nopp,tanggal', "nopp='" . $nopp . "'");
				$tglsampai = tanggalnormal($tglbuatpp[$nopp]);
				$tmpTgl = explode('-', $tglbuatpp[$nopp]);
				$tgldari = "01-01-" . $tmpTgl[0];
				$prddari = $tmpTgl[0] . "-01";
				$prdsampai = $tmpTgl[0] . "-" . $tmpTgl[1];

				$tab .= "<tr id='ppDetailTable' class=rowcontent>
					<td style='text-align:center'>" . $no . "</td>
					<td style='text-align:center;cursor:pointer' onclick=showdocpakaibarang('" . $tgldari . "','" . $tglsampai . "','" . substr($val['nopp'], -4) . "','" . $val['kodebarang'] . "',event)><font color=blue>" . $val['kodebarang'] . "</font></td>
					<td style=';cursor:pointer' onclick=showdocpakaibarang('" . $tgldari . "','" . $tglsampai . "','" . substr($val['nopp'], -4) . "','" . $val['kodebarang'] . "',event)><font color=blue>" . $optNamaBarang[$val['kodebarang']] . "</font></td>
					<td style='text-align:center'>" . $val['satuanpp'] . "</td>
					<td style='text-align:right'>" . hidezerodecimal($val['jumlahpp'], 2) . "</td>
					<td style='text-align:left'>" . $val['prioritas'] . " - " . $optprioritas[$val['prioritas']] . "</td>
					<td style='text-align:center'>" . tanggalnormal($val['tgl_sdt']) . "</td>";

				if ($val['kodevhc'] != "") {
					$arrkdvhc = explode(",", $val['kodevhc']);
					$tab .= "<td>";
					$tab .= "<table>";
					$nomor = 0;
					foreach ($arrkdvhc as $kodekend) {
						$nomor++;
						$tab .= "<tr>";
						$tab .= "<td style='text-align:left;cursor:pointer' onclick=showdocsparepart('" . $prddari . "','" . $prdsampai . "','" . substr($val['nopp'], -4) . "','" . $kodekend . "',event)><font color=blue>" . $nomor . ". " . $kodekend . "</font></td>";
						$tab .= "</tr>";
					}
					$tab .= "</table>";
					$tab .= "</td>";
				} else {
					$tab .= "<td style='text-align:center'>" . $val['kodevhc'] . "</td>";
				}

				$tab .= "<td style='text-align:right'>" . hidezerodecimal($val['kmhm'], 2) . "</td>
					<td style='text-align:right'>" . hidezerodecimal($val['stock'], 2) . "</td>
					<td style='text-align:right'>" . hidezerodecimal($val['hargasatuan'], 2) . "</td>
					<td style='text-align:right'>" . hidezerodecimal($val['realisasi'], 2) . "</td>
					<td style='text-align:right'>" . hidezerodecimal($val['anggaran'], 2) . "</td>					
					<td>" . $val['keterangan'] . "</td>
					<td>" . $val['kodeproject'] . "</td>";

				$tab .= "<td align=center>
						<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editdt('" . $nopp . "','" . $val['kodebarang'] . "','" . $optNamaBarang[$val['kodebarang']] . "','" . $val['satuanpp'] . "','" . hidezerodecimal($val['stock'], 2) . "','" . hidezerodecimal($val['hargasatuan'], 2) . "','" . hidezerodecimal($val['realisasi'], 2) . "','" . hidezerodecimal($val['anggaran'], 2) . "','" . $val['jumlahpp'] . "','" . $val['prioritas'] . "','" . tanggalnormal($val['tgl_sdt']) . "','" . $val['kodevhc'] . "','" . $val['kmhm'] . "','" . $val['keterangan'] . "','" . $val['kodeproject'] . "');\" >
					</td>
					<td align=center>
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletedt('" . $nopp . "','" . $val['kodebarang'] . "');\" >
					</td>
				</tr>";
			}
		}
		echo $tab;
		break;

	case 'deletedt':
		$str = "delete from " . $dbname . ".log_prapodt where nopp='" . $nopp . "' and kodebarang='" . $kodebarang . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'showupload':
		$tab = "";

		$arrmodul = getmodulefil($emodul);
		foreach ($arrmodul as $key => $val) {
			$optkriteria .= "<option value='" . $key . "'>" . $val['kriteria'] . "</option>";
		}

		$tab .= "<table cellspacing='1' border='0' id='uploadpopup'>
			<tr>
				<td>No. PR/SR</td>
				<td>:</td>
				<td>
					<label id='noppupload' style='font-weight:bold'>" . $nopp . "</label>
				</td>
			</tr>
			<tr>
				<td>Kriteria</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>" . $optkriteria . "</select>
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
					<td align='center'>Kriteria</td>
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

	case 'submitfile':
		$tgl = date("YmdHis");
		$data = $_POST;

		if ($data['fileupload'] != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$newfilename = str_replace($filetype, '', $_FILES['file']['name']);
				$filename = $newfilename . "_" . $tgl . "" . $filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];

				if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx') || ($filetype == '.rar')) {
					if ($_FILES['file']['size'] <= 250000) {
						$str = "insert into " . $dbname . ".listfileupload values ('','" . $nopp . "','" . $filename . "','" . $filetype . "','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
						try {
							$owlPDO->exec($str);
							move_uploaded_file($file_tmpname, "fileupload/pp/$filename");
						} catch (PDOException $e) {
							echo " Gagal," . addslashes($e->getMessage());
						}
					} else {
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				} else {
					exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
				}
			}
		}
		break;

	case 'loadfiles':
		$no = 0;
		$tab = "";
		$str = "select * from " . $dbname . ".log_prapoht where nopp = '" . $nopp . "'";
		$resv = fetchData($str);
		foreach ($resv as $bar => $barv) {
			$close = $barv['close'];
			$dibuat = $barv['dibuat'];
		}

		$str = "select * from " . $dbname . ".listfileupload where (notransaksi = '" . $nopp . "' or notransaksi in (select keterangan from " . $dbname . ".log_prapoht where nopp='" . $nopp . "')) and status='1'";
		$res = fetchData($str);
		if (empty($res)) {
			$tab .= "<tr class=rowcontent><td colspan=5 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			foreach ($res as $key => $val) {
				$no++;
				$tab .= "<tr id='ppDetailTable' class=rowcontent>
					<td style='text-align:center'>" . $no . "</td>";

				if ($val['formaticon'] == '.jpeg' || $val['formaticon'] == '.jpg') {
					$tab .= "<td style='text-align:center'>
						<a href='fileupload/pp/" . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
				} elseif ($val['formaticon'] == '.png') {
					$tab .= "<td style='text-align:center'>
						<a href='fileupload/pp/" . $val['namafile'] . "' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
				} elseif ($val['formaticon'] == '.pdf') {
					$tab .= "<td style='text-align:center'>
						<a href='fileupload/pp/" . $val['namafile'] . "' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
				} elseif ($val['formaticon'] == '.xls' || $val['formaticon'] == '.xlsx') {
					$tab .= "<td style='text-align:center'>
						<a href='fileupload/pp/" . $val['namafile'] . "' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
				} elseif ($val['formaticon'] == '.doc' || $val['formaticon'] == '.docx') {
					$tab .= "<td style='text-align:center'>
						<a href='fileupload/pp/" . $val['namafile'] . "' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
				} else {
					$tab .= "<td style='text-align:center'>
						<a href='fileupload/pp/" . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
				}

				$tab .= "<td style='text-align:left'>" . getcriterianame($val['kriteriaefil']) . "</td>
					<td style='text-align:left'>" . $val['namafile'] . "</td>
					<td align=center>
						<a href='fileupload/pp/" . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				if ($close == 0) {
					$tab .= "<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $nopp . "','" . $val['namafile'] . "');\" >";
				}
				if ($close != 0 and $dibuat == $_SESSION['standard']['userid']) {
					$tab .= "<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $nopp . "','" . $val['namafile'] . "');\" >";
				}
				$tab . "	</td>
				</tr>";
			}
		}
		echo $tab;
		break;

	case 'deletefile':
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $nopp . "' and namafile='" . $namafile . "'";
		try {
			$owlPDO->exec($str);
			$path = "fileupload/pp/" . $namafile;
			unlink($path);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'insert_persetujuan':

		$kd = substr($nopp, 17, 2);
		$unit = substr($nopp, 15, 4);

		##GET REQUESTER
		$str = "select requester from " . $dbname . ".log_prapoht where nopp='" . $nopp . "'";
		$res = fetchdata($str);
		$requester = $res[0]['requester'];
		$optdepartmen = makeOption($dbname, 'datakaryawan', 'karyawanid,bagian', "karyawanid='" . $requester . "'");
		$departemen = $optdepartmen[$requester];

		##CEK PER DEPARTEMEN
		$str = "select count(kodeunit) as kodeunit from " . $dbname . ".setup_approval where kodeunit='" . $unit . "' and jenispersetujuan='PR' and departemen='" . $departemen . "' and level='1'";
		$res = fetchdata($str);
		$perdepartemen = $res[0]['kodeunit'];
		$where = "";
		if ($perdepartemen > 0) {
			$where .= " and a.departemen='" . $departemen . "'";
		} else {
			$where .= " and a.departemen=''";
		}

		$where .= " and a.karyawanid!='" . $user_id . "'";

		$datanotif = array();

		$str = "select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from " . $dbname . ".setup_approval_notif a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='" . $_SESSION['standard']['userid'] . "' and a.jenispersetujuan='PR' and a.level='1' and a.kodeunit='" . $unit . "' " . $where . " order by b.namakaryawan asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$datanotif[$bar['karyawanid']] = $bar['karyawanid'];
		}


		$str = "SELECT * FROM " . $dbname . ".`log_prapoht` WHERE `nopp`='" . $nopp . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();

		if ($bar['close'] > 1) {
			echo "Warning: Status closed, Can't update the status";
			exit();
		} else {
			$stat_cls = 1;
			$strx = "update " . $dbname . ".log_prapoht set close='" . $stat_cls . "' where nopp='" . $nopp . "'";
			try {
				$owlPDO->query($strx);

				$strx = "insert into " . $dbname . ".approval values ('','" . $nopp . "','PR','1','" . $user_id . "','0','','','')";
				try {
					$owlPDO->query($strx);
					// notifemailpr($nopp,'1',$user_id);
					// if(count($datanotif)>0){						
					// 	onlynotifemailpr($nopp,'1',$user_id);
					// }
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;




























































	case 'insert':
		$str = "select count(id) as count from " . $dbname . ".pmn_5lokasikontrak where UPPER(lokasi) = '" . strtoupper($lokasi) . "' or inisial='" . $inisial . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();

		$countitem = $bar['count'];

		if ($countitem >= 1) {
			exit("Warning : Lokasi atau Inisial sudah pernah terdaftar sebelumnya.");
		} else {
			$str = "insert into " . $dbname . ".pmn_5lokasikontrak values ('','" . $lokasi . "','" . $inisial . "','" . $status . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "','')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
		break;

	case 'update':
		$str = "update " . $dbname . ".pmn_5lokasikontrak set status='" . $status . "', updateby='" . $_SESSION['standard']['userid'] . "' where lokasi = '" . $lokasi . "' and inisial='" . $inisial . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;
}














function createTabDetail($id, $data)
{
	global $dbname;
	global $key;
	global $owlPDO;

	$table = "<b>" . $_SESSION['lang']['nopp'] . "</b> : " . makeElement("detail_kode", 'text', $id, array('disabled' => 'disabled', 'style' => 'width:150px'));
	$table .= "<table id='ppDetailTable'>";

	# Header
	$table .= "<thead>";
	$table .= "<tr>";
	$table .= "<td>" . $_SESSION['lang']['kodebarang'] . "</td>";
	$table .= "<td>" . $_SESSION['lang']['namabarang'] . "</td>";
	$table .= "<td>" . $_SESSION['lang']['satuan'] . "</td>";
	$table .= "<td style='display:none'>" . $_SESSION['lang']['kodeanggaran'] . "</td>";
	$table .= "<td>" . $_SESSION['lang']['kodevhc'] . "</td>";
	$table .= "<td>KM/HM</td>";
	$table .= "<td>" . $_SESSION['lang']['jmlhDiminta'] . "</td>";
	$table .= "<td>" . $_SESSION['lang']['tanggalSdt'] . "</td>";
	$table .= "<td>" . $_SESSION['lang']['keterangan'] . "</td>";
	//    $table .= "<td>"."<a href=# onclick=addNewRow(detailBody,true)><img src='images\newfile.png'></a>"."</td>";
	$table .= "<td colspan=3>Action</td>";
	$table .= "</tr>";
	$table .= "</thead>";

	# Data
	$table .= "<tbody id='detailBody'>";

	$i = 0;

	#======= Display Data =======
	if ($data != array()) {
		foreach ($data as $key => $row) {
			$ql = "select * from " . $dbname . ".`log_5masterbarang` where `kodebarang`='" . $row['kodebarang'] . "'"; //echo $ql;
			$qry = $owlPDO->query($ql) or die(print " Gagal: " . PDOException::getMessage());
			$qry->setFetchMode(PDO::FETCH_ASSOC);
			$res = $qry->fetch();

			$tmpTgl = tanggalnormal($row['tgl_sdt']);
			$row['tgl_sdt'] = $tmpTgl;
			$table .= "<tr id='detail_tr_" . $key . "' class='rowcontent'>";
			$table .= "<td onclick=\"searchBrg('" . $_SESSION['lang']['findBrg'] . "','<fieldset>Find<input type=text class=myinputtext id=no_brg onkeypress=enterkey(event,findBrg)><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=" . $key . ">',event)\";>" . makeElement("kd_brg_" . $key . "", 'txt', $row['kodebarang'], array('style' => 'width:120px', 'disabled' => 'disabled', 'class=myinputtext')) . "<input type=hidden id=oldKdbrg_" . $key . " name=oldKdbrg_" . $key . " value='" . $row['kodebarang'] . "'></td>";
			$table .= "<td onclick=\"searchBrg('" . $_SESSION['lang']['findBrg'] . "','<fieldset>Find<input type=text class=myinputtext id=no_brg onkeypress=enterkey(event,findBrg)><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=" . $key . ">',event)\";>" . makeElement("nm_brg_" . $key . "", 'txt', $res['namabarang'], array('style' => 'width:120px', 'disabled' => 'disabled', 'class=myinputtext')) . "<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=" . $_SESSION['lang']['find'] . " onclick=\"searchBrg('" . $_SESSION['lang']['findBrg'] . "','<fieldset>Find<input type=text class=myinputtext id=no_brg onkeypress=enterkey(event,findBrg)><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=" . $key . ">',event)\";></td>";

			$table .= "<td onclick=\"searchBrg('" . $_SESSION['lang']['findBrg'] . "','<fieldset>Find<input type=text class=myinputtext id=no_brg onkeypress=enterkey(event,findBrg)><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=" . $key . ">',event)\";>" . makeElement("sat_" . $key . "", 'txt', $res['satuan'], array('style' => 'width:70px', 'disabled' => 'disabled', 'class=myinputtext')) . "</td>";

			$table .= "<td style='display:none'>" . makeElement("kd_angrn_" . $key . "", 'txt', '', array('style' => 'width:70px', 'disabled' => 'disabled', 'class=myinputtext')) . "<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=" . $_SESSION['lang']['find'] . " onclick=\"searchAngrn('" . $_SESSION['lang']['findAngrn'] . "','<fieldset>Find<input type=text class=myinputtext id=no_angrn><button class=mybutton onclick=findAngrn()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=" . $key . ">',event)\";></td>";
			$table .= "<td>" . makeElement("kd_vhc_" . $key . "", 'txt', $row['kodevhc'], array('style' => 'width:70px', 'disabled' => 'disabled', 'class=myinputtext')) . "<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' 'disabled=disabled' class='resicon' title=" . $_SESSION['lang']['find'] . " onclick=\"searchVhc('" . $_SESSION['lang']['findvhc'] . "','Find<input type=text class=myinputtext id=no_vhc><button class=mybutton onclick=findVhc()>Find</button><div style=clear:both></div><div id=container></div><input type=hidden id=nomor name=nomor value=" . $key . ">','" . $key . "',event)\";></td>";
			$table .= "<td>" . makeElement("kmhm_" . $key . "", 'textnum', $row['jumlah'], array('style' => 'width:70px', 'onkeypress' => 'return angka_doang(event)', 'class=myinputtext')) . "</td>";

			$table .= "<td>" . makeElement("jmlhDiminta_" . $key . "", 'textnum', $row['jumlah'], array('style' => 'width:70px', 'onkeypress' => 'return angka_doang(event)', 'class=myinputtext')) . "</td>";
			$table .= "<td>" . makeElement("tgl_sdt_" . $key . "", 'txt', $row['tgl_sdt'], array('style' => 'width:70px', 'onkeypress' => 'return tanpa_kutip(event)', 'onmousemove' => 'setCalendar(this.id)', 'readonly' => 'readonly', 'class=myinputtext')) . "</td>";
			$table .= "<td>" . makeElement("ket_" . $key . "", 'txt', $row['keterangan'], array('style' => 'width:130px', 'class=myinputtext', 'onkeypress' => 'return tanpa_kutip(event)')) . "</td>";
			$table .= "<td><img id='detail_edit_" . $key . "' title='Edit' class=zImgBtn onclick=\"editDetail('" . $key . "')\" src='images/save.png'/>";
			$table .= "&nbsp;<img id='detail_delete_" . $key . "' title='Hapus' class=zImgBtn onclick=\"deleteDetail('" . $key . "')\" src='images/delete_32.png'/></td>";
			$table .= "</tr>";
			$i = $key;
		}
		$i++;
	}

	#======= New Row ===========
	$table .= "<tr id='detail_tr_" . $i . "' class='rowcontent'>";
	$table .= "<td>" . makeElement("kd_brg_" . $i . "", 'txt', '', array('style' => 'width:120px', 'disabled' => 'disabled', 'class=myinputtext')) . "</td>";
	$table .= "<td>" . makeElement("nm_brg_" . $i . "", 'txt', '', array('style' => 'width:120px', 'disabled' => 'disabled', 'class=myinputtext')) . "<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=" . $_SESSION['lang']['find'] . " onclick=\"searchBrg('" . $_SESSION['lang']['findBrg'] . "','<fieldset>Find<input type=text class=myinputtext id=no_brg onkeypress=enterkey(event,findBrg)><button class=mybutton onclick=findBrg()>Find</button></fieldset><input type=hidden id=nomor name=nomor value=" . $i . "><div id=container></div>',event)\";><input type=hidden id=oldKdbrg_" . $i . " name=oldKdbrg_" . $i . ">" . "</td>";
	$table .= "<td>" . makeElement("sat_" . $i . "", 'txt', '', array('style' => 'width:70px', 'disabled' => 'disabled', 'class=myinputtext')) . "</td>";
	$table .= "<td style='display:none'>" . makeElement("kd_angrn_" . $i . "", 'txt', '', array('style' => 'width:70px', 'disabled' => 'disabled', 'class=myinputtext')) . "<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=" . $_SESSION['lang']['find'] . " onclick=\"searchAngrn('" . $_SESSION['lang']['findAngrn'] . "','<fieldset>Find<input type=text class=myinputtext id=no_angrn><button class=mybutton onclick=findAngrn()>Find</button></fieldset><input type=hidden id=nomor name=nomor value=" . $i . "><div id=container></div>',event)\";></td>";
	$table .= "<td>" . makeElement("kd_vhc_" . $i . "", 'txt', '', array('style' => 'width:70px', 'disabled' => 'disabled', 'class=myinputtext')) . "<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=" . $_SESSION['lang']['find'] . " onclick=\"searchVhc('" . $_SESSION['lang']['findvhc'] . "','Find<input type=text class=myinputtext id=no_vhc><button class=mybutton onclick=findVhc()>Find</button><div style=clear:both></div><input type=hidden id=nomor name=nomor value=" . $i . "><div id=container></div>','" . $i . "',event)\";></td>";

	$table .= "<td>" . makeElement("kmhm_" . $i . "", 'textnum', '', array('style' => 'width:70px', 'onkeypress' => 'return angka_doang(event)', 'class=myinputtext')) . "</td>";

	$table .= "<td>" . makeElement("jmlhDiminta_" . $i . "", 'textnum', '', array('style' => 'width:70px', 'onkeypress' => 'return angka_doang(event)', 'class=myinputtext')) . "</td>";
	$table .= "<td>" . makeElement("tgl_sdt_" . $i . "", 'txt', '', array('style' => 'width:70px', 'onkeypress' => 'return tanpa_kutip(event)', 'onmousemove' => 'setCalendar(this.id)', 'readonly' => 'readonly', 'class=myinputtext')) . "</td>";
	$table .= "<td>" . makeElement("ket_" . $i . "", 'txt', '', array('style' => 'width:130px', 'class=myinputtext', 'onkeypress' => 'return tanpa_kutip(event)')) . "</td>";
	$table .= makeElement("nopp_" . $i . "", 'hidden', $id, array('style' => 'width:70px', 'onkeypress' => 'return tanpa_kutip(event)')) . "</td>";


	# Add, Container Delete
	$table .= "<td><img id='detail_add_" . $i . "' title='Simpan' class=zImgBtn onclick=\"addDetail('" . $i . "')\" src='images/save.png'/>";
	$table .= "&nbsp;<img id='detail_delete_" . $i . "' /></td>";
	$table .= "</tr>";

	$table .= "</tbody>";
	$table .= "</table>";
	echo $table;
}

function getrealproc($unit, $kelompokbrg, $tahun)
{
	global $dbname;
	global $owlPDO;

	$val = 0;

	$str = "select sum(hartot) as hartot from " . $dbname . ".log_transaksi_vw where tipetransaksi='1' and post='1' and left(kodegudang,4)='" . $unit . "' and kodebarang like '" . $kelompokbrg . "%' and left(tanggal,4)='" . $tahun . "' and nopo like '%/" . $tahun . "/%'";
	$res = fetchdata($str);
	$val = $res[0]['hartot'];

	return $val;
}
function getrealakun($unit, $noakun, $tahun)
{
	global $dbname;
	global $owlPDO;

	$val = 0;

	$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt where kodeorg='" . $unit . "' and noakun = '" . $noakun . "' and tanggal like '" . $tahun . "%'"; #exit("error".$str);
	$res = fetchdata($str);
	$val = $res[0]['jumlah'];

	return $val;
}

function cekkodevhc($param)
{
	global $dbname;
	global $owlPDO;

	$val = 0;

	$str = "select kodevhc from " . $dbname . ".log_5masterbarang where kodebarang='" . $param['kodebarang'] . "'"; #exit("error".$str);
	$res = fetchdata($str);
	if ($res[0]['kodevhc'] == '1') {
		$val = 1;
	} else {
		$str = "select kodevhc from " . $dbname . ".log_5subklbarang where kode='" . substr($param['kodebarang'], 0, 5) . "'"; #exit("error".$str);
		$res = fetchdata($str);
		if ($res[0]['kodevhc'] == '1') {
			$val = 1;
		}
	}

	return $val;
}
