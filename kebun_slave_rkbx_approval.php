<?php
$mobileValid = false;
if (isset($_POST['par']) || isset($_GET['par'])) {
	$validasiPostMobile = explode(" ", $_POST['par']);
	// $validasiGetMobile = explode(" ", isset($_GET['par']));
	if ($validasiPostMobile[0] == "owlApp") {
		$mobileValid = true;
		$session_id = '';
	};
}

if ($mobileValid == false) { //untuk redirec dari mobile
	require_once('master_validation.php');
	$session_id = $_SESSION['standard']['userid'];
}
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$karyawanid = checkPostGet('karyawanid', $session_id);
$method = checkPostGet('method', '');
$proses = checkPostGet('proses', '');
$level = checkPostGet('level', '');
$notransaksi = checkPostGet('notransaksi', '');
$kolom = checkPostGet('kolom', '');
$comment = checkPostGet('comment', '');
$userid = checkPostGet('userid', '');
$pt = checkPostGet('pt', '');
$kodeorg = checkPostGet('kodeorg', '');
$periode = checkPostGet('periode', '');
$proses = checkPostGet('proses', '');

$tglskrng = date("Y-m-d H:i:s");
$arrstatus = array('0' => 'belum diproses', '1' => 'disetujui', '2' => 'dikoreksi', '3' => 'ditolak');
switch ($method) {
	case 'getdetail':
	case 'RKB':
		$tab .= "<fieldset>
			<legend>" . $_SESSION['lang']['detail'] . "</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
			<td align=center>" . $_SESSION['lang']['periode'] . "</td>
			<td align=center>" . $_SESSION['lang']['unit'] . "</td>
			<td align=center>" . $_SESSION['lang']['detail'] . "</td>
			<td colspan='2' align='center'>Verification</td>";

		$countApp = getCountApproval('RKB');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</td>";
		}
		$tab .= "</tr>
			</thead>
			<tbody>";
		$countApp = getCountApproval('RKB');
		$str = "select * from " . $dbname . ".approval a
			left join " . $dbname . ".kebun_rkbht b on a.notransaksi = b.norkb
			where a.jenispersetujuan='RKB' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by a.tanggal asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nmpt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $bar['kodeorg'] . "'");
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $bar['kodeorg'] . "'");
			$no++;
			$tab .= "<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td align=left>" . $bar['notransaksi'] . "</td>
				<td align=left>" . $bar['periode'] . "</td>
				<td align=left>" . $bar['kodeorg'] . " - " . $optNmOrg[$bar['kodeorg']] . "</td>
				<td align=center>
					<img src=images/zoom.png class=resicon height='30' title='Preview Detail' onclick=\"htmlrkb('" . $bar['norkb'] . "','','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $no . "','event','html');\">					
					
		<img src=images/zoom.png class=resicon height='30' title='Preview Rekap' onclick=\"htmlrkbrekap('" . $bar['norkb'] . "','" . $nmpt[$bar['kodeorg']] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $no . "','event','html');\">					
				</td>";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			$xxx = "";
			for ($i = 1; $i <= $countApp; $i++) {

				$strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
				$resx = fetchdata($strx);
				foreach ($resx as $keyx => $valx) {
					if ($valx['karyawanid'] == $karyawanid) {
						if ($valx['status'] == '' || $valx['status'] == 0) {
							$showaction = $showaction + 1;
						}
					}

					if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
						$level = $valx['level'];
						$xxx = "conte";
						break;
					}
				}

				if ($xxx == "conte") {
					break;
				}
			}

			if ($showaction != $level || $level == 1) {
				$tab .= "<td style='text-align:center'>
					<button class=mybutton onclick=\"getdatarkb('" . $bar['notransaksi'] . "','" . $level . "')\">" . $_SESSION['lang']['approve'] . "</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakrkb('" . $bar['notransaksi'] . "','" . $level . "')\">" . $_SESSION['lang']['ditolak'] . "</button>
					</td>";
			} else {
				$tab .= "<td colspan=2>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'RKB');

				$strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='RKB' and kodeunit='" . $kodeorg . "' and level='" . $i . "'";
				$respo = fetchdata($strpo);
				$tipeapp = $respo[0]['tipe'];
				$departemenapp = $respo[0]['departemen'];
				$tipekaryawanapp = $respo[0]['tipekaryawan'];
				$jabatanapp = $respo[0]['jabatan'];

				if ($tipeapp == '1') {
					if ($arrDetail['komentar'] == '') {
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

				if ($arrDetail['nama'] != '') {
					$tab .= "<td style='vertical-align:top;text-align:center'>
						<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
						Status : " . $arrDetail['namastatus'] . "<br>
						" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
					</td>";
				} else {
					$tab .= "<td style='text-align:center'>-</td>";
				}

				// if ($arrDetail['nama'] != '') {
				// $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
				// } else {
				// $tab.="<td style='text-align:center'>-</td>";
				// }
			}
			$tab .= "</tr>";
		}
		$tab .= "</tbody>
			<tfoot>
			</tfoot>
			</table>
			</fieldset>";
		break;
		break;
	case 'get_form_approval':
		$tab = "";

		$str = "select * from " . $dbname . ".kebun_rkbht where norkb='" . $notransaksi . "'";
		$res = fetchdata($str);
		@$koderorg = $res[0]['kodeorg'];
		$countApp = getCountApproval('RKB', $koderorg);
		for ($i = 1; $i <= $countApp; $i++) {
			// $arrDetail = detailApprove($i,$notransaksi,'RKB');

			$strx = "select * from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $i . "'";
			$resx = fetchdata($strx);
			foreach ($resx as $keyx => $valx) {
				if ($karyawanid == $valx['karyawanid']) {
					if ($i == $countApp) {
						$tab .= "<div id=approve>
							<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
							<table cellspacing=1 border=0>
								<tr>
									<td colspan=3>Approved</td>
								</tr>
								<tr>
									<td colspan=3><hr></td>
								</tr>
								<tr>
									<td>" . $_SESSION['lang']['note'] . "</td>
									<td>:</td>
									<td>
										<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
									</td>
								</tr>
								<tr>
									<td colspan=3 align=center>
										<button id=Ajukan class=mybutton onclick=nextapprovalrkb('approved') >Approved</button>
									</td>
								</tr>
							</table>
						</div>";
					} else {
						$level = $i + 1;
						$arrListApp = listApprove($level, 'RKB', $koderorg);
						foreach ($arrListApp as $key => $val) {
							$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['nama'] . " [" . $val['lokasitugas'] . "]</option>";
						}
						$tab .= "<div id=test style=display:block>
							<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
							<input hidden id=kolom value=" . $_POST['kolom'] . "  />
							<table cellspacing=1 border=0>
								<tr>
									<td colspan=3>Submit to the next approval :</td>
								</tr>
								<tr>
									<td colspan=3><hr></td>
								</tr>
								<tr>
									<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
									<td>:</td>
									<td valign=top>
										<select id=user_id name=user_id  style=\"width:150px;\">" . $optKry . "</select>
									</td>
								</tr>
								<tr>
									<td>" . $_SESSION['lang']['note'] . "</td>
									<td>:</td>
									<td>
										<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
									</td>
								</tr>
									<td colspan=2></td>
									<td>
										<button class=mybutton onclick=nextapprovalrkb() title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
									</td>
								</tr>
							</table>
							<input type=hidden name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
						</div>";
					}
				}
			}
		}
		echo $tab;
		break;
	case 'insert_nextapproval':
		$jenisApp = "RKB";
		if ($userid == '') {
			$user_id = $karyawanid;
		} else {
			$user_id = $userid;
		}

		$str = "select * from " . $dbname . ".kebun_rkbht where norkb='" . $notransaksi . "'";
		$res = fetchdata($str);
		$koderorg = $res[0]['kodeorg'];

		$countApp = getCountApproval('RKB', $koderorg);
		$tglskrng = date("Y-m-d H:i:s");
		$str = "select * from " . $dbname . ".kebun_rkbht where `norkb`='" . $notransaksi . "'"; #exit('error sasas'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if ($bar['statuspersetujuan'] == 1) {
			exit("Warning : Sudah di Approved");
		} else if ($bar['statuspersetujuan'] == 0) {
			$arrDetail = detailApprove($kolom, $notransaksi, 'RKB');
			$level = $kolom + 1;
			if ($kolom != $countApp) {
				if ($user_id == $arrDetail['karyawanid']) {
					exit("Warning : " . getNamaKaryawan($user_id) . " Sudah di gunakan");
				} else if ($user_id == $bar['dibuat']) {
					exit("Warning : " . getNamaKaryawan($user_id) . " Pembuat Transaksi");
				} else {
					$str = "select * from " . $dbname . ".setup_approval where 
							jenispersetujuan='" . $jenisApp . "' and level='" . $level . "' and kodeunit='" . $koderorg . "'";
					$res = fetchData($str);
					$tipeapp = $res[0]['tipe'];
					$departemenapp = $res[0]['departemen'];
					$tipekaryawanapp = $res[0]['tipekaryawan'];
					$jabatanapp = $res[0]['jabatan'];

					if ($tipeapp == '1') {
						if ($departemenapp != '') {
							$str = "select * from " . $dbname . ".datakaryawan where bagian='" . $departemenapp . "'";
							$res = fetchdata($str);
							foreach ($res as $keyx => $valx) {
								$str = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('" . $notransaksi . "','" . $jenisApp . "','" . $level . "','" . $valx['karyawanid'] . "','0')";
								$owlPDO->exec($str);
							}
						}
						if ($tipekaryawanapp != '') {
							$str = "select * from " . $dbname . ".datakaryawan where tipekaryawan='" . $tipekaryawanapp . "'";
							$res = fetchdata($str);
							foreach ($res as $keyx => $valx) {
								$str = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('" . $notransaksi . "','" . $jenisApp . "','" . $level . "','" . $valx['karyawanid'] . "','0')";
								$owlPDO->exec($str);
							}
						}
						if ($jabatanapp != '0') {
							$str = "select * from " . $dbname . ".datakaryawan where kodejabatan='" . $jabatanapp . "'";
							$res = fetchdata($str);
							foreach ($res as $keyx => $valx) {
								$str = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('" . $notransaksi . "','" . $jenisApp . "','" . $level . "','" . $valx['karyawanid'] . "','0')";
								$owlPDO->exec($str);
							}
						}
					} else {
						$str = "insert into " . $dbname . ".approval (nourut,notransaksi,jenispersetujuan,level,karyawanid,status,komentar,keterangan,tanggal) values ('','" . $notransaksi . "','RKB','" . $level . "','" . $user_id . "','0','','','')";
						try {
							$owlPDO->exec($str);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "\n";
							die();
						}
					}

					$strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
					try {
						$owlPDO->exec($strx);

						$str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid!='" . $karyawanid . "' and level='" . $kolom . "'";
						$owlPDO->exec($str);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						die();
					}
				}
			} else {
				$strx = "update " . $dbname . ".kebun_rkbht set statuspersetujuan='1' where `norkb`='" . $notransaksi . "'";
				try {
					$owlPDO->exec($strx);

					$strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
					try {
						$owlPDO->exec($strx);

						$str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid!='" . $karyawanid . "' and level='" . $kolom . "'";
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
		}
		break;

	case 'tolak':
		echo "<div id=rejected_form>
		<input hidden id=notransaksi value=" . $_POST['notransaksi'] . "  />
		<table cellspacing=1 border=0>
		<tr>
		<td colspan=3>
		 Rejection</td></tr>
		<tr>
		<tr><td colspan=3><hr></td></tr>
		<td>" . $_SESSION['lang']['note'] . "</td>
		<td>:</td>
		<td><input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
		</tr>
		<tr><td colspan=3 align=center>
		<button class=mybutton onclick=\"inserttolakrkb(" . $_POST['kolom'] . ")\" >" . $_SESSION['lang']['ditolak'] . "</button>
		</td></tr></table>
		</div>";
		break;
	case 'inserttolak':
		$ardt = 0;
		$temporg = explode("/", $notransaksi);
		$koderorg = $temporg[4];
		$countApp = getCountApproval('RKB', $koderorg);
		$arrDetail = detailApprove($kolom, $notransaksi, 'RKB');
		$tglskrng = date("Y-m-d H:i:s");
		$str = "update " . $dbname . ".kebun_rkbht set statuspersetujuan='3' where norkb='" . $notransaksi . "'";
		try {
			$owlPDO->exec($str);
			$str = "update " . $dbname . ".approval set status='3', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "'";
			try {
				$owlPDO->exec($str);

				$str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid!='" . $karyawanid . "' and level='" . $kolom . "'";
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'rekaprkb':
		if ($proses == 'preview') {
			$tab .= "<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
		} else {
			$tab .= "<table border=1 cellpadding=1 cellspacing=1 class=sortable>";
		}
		$gttttttt = '';
		$data = array();
		$tab .= "<thead><tr class=rowheader>";
		$rows = "rowspan=4";
		$tab .= "<th align=center " . $rows . " width=20px>No</th>
				<th align=center " . $rows . ">" . $_SESSION['lang']['blok'] . "</th>
				<th align=center " . $rows . ">" . $_SESSION['lang']['luas'] . "</th>
				<th align=center " . $rows . ">" . $_SESSION['lang']['sph'] . "</th>
				<th align=center " . $rows . ">TT</th>
				<th align=center " . $rows . ">" . $_SESSION['lang']['pokok'] . "</th>
				<th align=center " . $rows . " style=width:30px>" . $_SESSION['lang']['rotasi'] . "</th>
				<th align=center " . $rows . " style=width:30px>AKP<br>%</th>
				<th align=center " . $rows . " style=width:30px>BJR</th>
				<th align=center " . $rows . ">Jjg</th>
				<th align=center " . $rows . ">Kg</th>
				<th align=center " . $rows . " style=width:30px>Output</th>
				
				<th align=center colspan=20>" . $_SESSION['lang']['biaya'] . "</th>
				<th align=center " . $rows . ">Rp/Kg</th>
			</tr>
			<tr>
				<th align=center colspan=11>Pemanen</th>
				<th align=center colspan=8>Supervisi</th>
				<th align=center rowspan=3>Total Biaya</th>
			</tr>
			<tr>
				<th align=center colspan=4>HK</th>
				<th align=center rowspan=2>Upah</th>
				<th align=center colspan=5>Premi</th>
				<th align=center rowspan=2>Total</th>
				<th align=center colspan=2>Mandor Panen</th>
				<th align=center colspan=2>Kerani Panen</th>
				<th align=center colspan=2>Mandor 1</th>
				<th align=center colspan=2>Total</th>
			</tr>
			<tr>
				<th align=center>KBL</th>
				<th align=center>KHT</th>
				<th align=center>KHL</th>
				<th align=center>Sub TTL</th>
				<th align=center>1</th>
				<th align=center>2</th>
				<th align=center>Kutib Brd</th>
				<th align=center>Borongan</th>
				<th align=center>Sub TTL</th>
				<th align=center >Upah</th>
				<th align=center >Premi</th>
				<th align=center >Upah</th>
				<th align=center >Premi</th>
				<th align=center >Upah</th>
				<th align=center>Premi</th>
				<th align=center>Upah</th>
				<th align=center>Premi</th>
			</tr>
			</thead>";

		$str = "select * from " . $dbname . ".kebun_rkbdt where tipetransaksi='PANEN' and norkb='" . $notransaksi . "' and periode='" . $periode . "' and kodeorg='" . $kodeorg . "'";
		if (count(fetchData($str)) > 0) {
			$strx = $str;
			$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);
			while ($barx = $resx->fetch()) {
				$optluas = makeOption($dbname, 'setup_blok', 'kodeorg,luasareaproduktif', "kodeorg='" . $barx['blok'] . "'");
				$optpokok = makeOption($dbname, 'setup_blok', 'kodeorg,jumlahpokok', "kodeorg='" . $barx['blok'] . "'");
				$opttt = makeOption($dbname, 'setup_blok', 'kodeorg,tahuntanam', "kodeorg='" . $barx['blok'] . "'");

				$data[$barx['divisi']][$barx['tahuntanam']] = $barx['tahuntanam'];
				@$luas[$barx['divisi']][$barx['tahuntanam']] += $optluas[$barx['blok']];
				@$pkk[$barx['divisi']][$barx['tahuntanam']] += $optpokok[$barx['blok']];
				@$tt[$barx['divisi']][$barx['tahuntanam']] = $opttt[$barx['blok']];
				@$jjg[$barx['divisi']][$barx['tahuntanam']] += $barx['hasilkerja'];
				@$kg[$barx['divisi']][$barx['tahuntanam']] += $barx['hasilkerjakg'];
				@$kbt[$barx['divisi']][$barx['tahuntanam']] += $barx['KBL'];
				@$kht[$barx['divisi']][$barx['tahuntanam']] += $barx['KHT'];
				@$khl[$barx['divisi']][$barx['tahuntanam']] += $barx['KHL'];
				@$upah[$barx['divisi']][$barx['tahuntanam']] += $barx['upah'];
				@$premi1[$barx['divisi']][$barx['tahuntanam']] += $barx['premilebihbasis1'];
				@$premi2[$barx['divisi']][$barx['tahuntanam']] += $barx['premilebihbasis2'];
				@$brd[$barx['divisi']][$barx['tahuntanam']] += $barx['premibrondol'];
				@$bor[$barx['divisi']][$barx['tahuntanam']] += $barx['rupiahborongan'];
				@$upahmdr[$barx['divisi']][$barx['tahuntanam']] += $barx['upahmdr'];
				@$premimdr[$barx['divisi']][$barx['tahuntanam']] += $barx['premimdr'];
				@$upahkrn[$barx['divisi']][$barx['tahuntanam']] += $barx['upahkrn'];
				@$premikrn[$barx['divisi']][$barx['tahuntanam']] += $barx['premikrn'];
				@$upahmdr1[$barx['divisi']][$barx['tahuntanam']] += $barx['upahmdr1'];
				@$premimdr1[$barx['divisi']][$barx['tahuntanam']] += $barx['premimdr1'];
				@$rotasi[$barx['divisi']][$barx['tahuntanam']] = $barx['rotasi'];
			}
			$no = '';
			$tab .= "<tr class=rowcontent><td align=left colspan=33><b>P A N E N</b></td></tr>";
			$ttdgt_biaya = 0;
			$gtttbiaya = array();
			foreach ($data as $divisi => $kdtt) {
				foreach ($kdtt as $kodett => $tt) {
					$no += 1;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td align=center>" . $no . "</td>";
					$tab .= "<td align=center>" . $divisi . "</td>";
					$tab .= "<td align=right>" . @number_format($luas[$divisi][$tt], 2) . "</td>";
					$tab .= "<td align=right>" . @number_format($pkk[$divisi][$tt] / $luas[$divisi][$tt], 2) . "</td>";
					$tab .= "<td align=center>" . $tt . "</td>";
					$tab .= "<td align=right>" . @number_format($pkk[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . $rotasi[$divisi][$tt] . "</td>";
					$tab .= "<td align=right>" . @number_format($jjg[$divisi][$tt] / $pkk[$divisi][$tt] / $rotasi[$divisi][$tt] * 100, 2) .	"</td>";
					$tab .= "<td align=right>" . @number_format($kg[$divisi][$tt] / $jjg[$divisi][$tt], 2) . "</td>";
					$tab .= "<td align=right>" . @number_format($jjg[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($kg[$divisi][$tt]) . "</td>";
					$ttthk[$divisi][$tt] = $kbt[$divisi][$tt] + $kht[$divisi][$tt] + $khl[$divisi][$tt];
					$tab .= "<td align=right>" . @number_format($kg[$divisi][$tt] / $ttthk[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($kbt[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($kht[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($khl[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($ttthk[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($upah[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($premi1[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($premi2[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($brd[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($bor[$divisi][$tt]) . "</td>";
					$tttpremi[$divisi][$tt] = $premi1[$divisi][$tt] + $premi2[$divisi][$tt] + $brd[$divisi][$tt] + $bor[$divisi][$tt];
					$tab .= "<td align=right>" . @number_format($tttpremi[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($tttpremi[$divisi][$tt] + $upah[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($upahmdr[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($premimdr[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($upahkrn[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($premikrn[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($upahmdr1[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($premimdr1[$divisi][$tt]) . "</td>";
					$tttupahmdr[$divisi][$tt] = $upahmdr[$divisi][$tt] + $upahkrn[$divisi][$tt] + $upahmdr1[$divisi][$tt];
					$tttpremimdr[$divisi][$tt] = $premimdr[$divisi][$tt] + $premikrn[$divisi][$tt] + $premimdr1[$divisi][$tt];
					$tab .= "<td align=right>" . @number_format($tttupahmdr[$divisi][$tt]) . "</td>";
					$tab .= "<td align=right>" . @number_format($tttpremimdr[$divisi][$tt]) . "</td>";
					$gtttbiaya[$divisi][$tt] = $tttpremi[$divisi][$tt] + $upah[$divisi][$tt] + $tttupahmdr[$divisi][$tt] + $tttpremimdr[$divisi][$tt];
					$tab .= "<td align=right>" . @number_format($gtttbiaya[$divisi][$tt]) . "</td>";
					if ($gtttbiaya[$divisi][$tt] > 0) {
						$tab .= "<td  align=right>" . @number_format($gtttbiaya[$divisi][$tt] / $kg[$divisi][$tt], 2) . "</td>";
					} else {
						$tab .= "<td  align=right></td>";
					}
					$tab .= "</tr>";

					# sub total per divisi
					@$td_luas[$divisi]     += $luas[$divisi][$tt];
					@$td_pkk[$divisi]      += $pkk[$divisi][$tt];
					@$td_jjg[$divisi]      += $jjg[$divisi][$tt];
					@$td_kg[$divisi]       += $kg[$divisi][$tt];
					@$td_kbt[$divisi]      += $kbt[$divisi][$tt];
					@$td_kht[$divisi]      += $kht[$divisi][$tt];
					@$td_khl[$divisi]      += $khl[$divisi][$tt];
					@$td_ttthk[$divisi]    += $ttthk[$divisi][$tt];
					@$td_upah[$divisi]     += $upah[$divisi][$tt];
					@$td_premi1[$divisi]   += $premi1[$divisi][$tt];
					@$td_premi2[$divisi]   += $premi2[$divisi][$tt];
					@$td_brd[$divisi]      += $brd[$divisi][$tt];
					@$td_bor[$divisi]      += $bor[$divisi][$tt];
					@$td_upahmdr[$divisi]  += $upahmdr[$divisi][$tt];
					@$td_premimdr[$divisi] += $premimdr[$divisi][$tt];
					@$td_upahkrn[$divisi]  += $upahkrn[$divisi][$tt];
					@$td_premikrn[$divisi] += $premikrn[$divisi][$tt];
					@$td_upahmdr1[$divisi] += $upahmdr1[$divisi][$tt];
					@$td_premimdr1[$divisi] += $premimdr1[$divisi][$tt];
					$rot[$divisi]			= $rotasi[$divisi][$tt];

					# grand total
					@$gt_luas += $luas[$divisi][$tt];
					@$gt_pkk += $pkk[$divisi][$tt];
					@$gt_jjg += $jjg[$divisi][$tt];
					@$gt_kg += $kg[$divisi][$tt];
					@$gt_kbt += $kbt[$divisi][$tt];
					@$gt_kht += $kht[$divisi][$tt];
					@$gt_khl += $khl[$divisi][$tt];
					@$gt_ttthk += $ttthk[$divisi][$tt];
					@$gt_upah += $upah[$divisi][$tt];
					@$gt_premi1 += $premi1[$divisi][$tt];
					@$gt_premi2 += $premi2[$divisi][$tt];
					@$gt_brd += $brd[$divisi][$tt];
					@$gt_bor += $bor[$divisi][$tt];
					@$gt_upahmdr += $upahmdr[$divisi][$tt];
					@$gt_premimdr += $premimdr[$divisi][$tt];
					@$gt_upahkrn += $upahkrn[$divisi][$tt];
					@$gt_premikrn += $premikrn[$divisi][$tt];
					@$gt_upahmdr1 += $upahmdr1[$divisi][$tt];
					@$gt_premimdr1 += $premimdr1[$divisi][$tt];
					$gtrot = $rotasi[$divisi][$tt];
				}
				$tab .= "<tr class=rowcontent style=background-color:#FAEBD7>";
				$tab .= "<td align=center colspan=2>TOTAL " . $divisi . "</td>";
				$tab .= "<td align=right>" . @number_format($td_luas[$divisi], 2) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_pkk[$divisi] / $td_luas[$divisi], 2) . "</td>";
				$tab .= "<td align=right></td>";
				$tab .= "<td align=right>" . @number_format($td_pkk[$divisi]) . "</td>";
				$tab .= "<td align=right>" . $rot[$divisi] . "</td>";
				$tab .= "<td align=right>" . @number_format($td_jjg[$divisi] / $td_pkk[$divisi] / $rot[$divisi] * 100, 2) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_kg[$divisi] / $td_jjg[$divisi], 2) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_jjg[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_kg[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_kg[$divisi] / $td_ttthk[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_kbt[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_kht[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_khl[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_ttthk[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_upah[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_premi1[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_premi2[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_brd[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_bor[$divisi]) . "</td>";
				$ttd_premi = $td_premi1[$divisi] + $td_premi2[$divisi] + $td_brd[$divisi] + $td_bor[$divisi];
				$tab .= "<td align=right>" . @number_format($ttd_premi) . "</td>";
				$tab .= "<td align=right>" . @number_format($ttd_premi + $td_upah[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_upahmdr[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_premimdr[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_upahkrn[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_premikrn[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_upahmdr1[$divisi]) . "</td>";
				$tab .= "<td align=right>" . @number_format($td_premimdr1[$divisi]) . "</td>";
				$ttd_upahmdr = $td_upahmdr[$divisi] + $td_upahkrn[$divisi] + $td_upahmdr1[$divisi];
				$ttd_premimdr = $td_premimdr[$divisi] + $td_premikrn[$divisi] + $td_premimdr1[$divisi];
				$tab .= "<td align=right>" . @number_format($ttd_upahmdr) . "</td>";
				$tab .= "<td align=right>" . @number_format($ttd_premimdr) . "</td>";
				$ttdgt_biaya = $ttd_premimdr + $ttd_upahmdr + $ttd_premi + $td_upah[$divisi];
				$tab .= "<td align=right>" . @number_format($ttdgt_biaya) . "</td>";
				if ($ttdgt_biaya > 0) {
					$tab .= "<td  align=right>" . @number_format($ttdgt_biaya / $td_kg[$divisi], 2) . "</td>";
				} else {
					$tab .= "<td  align=right></td>";
				}
				$tab .= "</tr>";
			}
			$tab .= "<tr class=rowcontent style=background-color:#DEB887>";
			$tab .= "<td align=center colspan=2>G.TOTAL</td>";
			$tab .= "<td align=right>" . @number_format($gt_luas, 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_pkk / $gt_luas, 2) . "</td>";
			$tab .= "<td align=right></td>";
			$tab .= "<td align=right>" . @number_format($gt_pkk) . "</td>";
			$tab .= "<td align=right>" . $gtrot . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_jjg / $gt_pkk / $gtrot * 100, 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_kg / $gt_jjg, 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_jjg) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_kg) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_kg / $gt_ttthk) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_kbt) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_kht) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_khl) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_ttthk) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_upah) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_premi1) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_premi2) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_brd) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_bor) . "</td>";
			$ttg_premi = $gt_premi1 + $gt_premi2 + $gt_brd;
			$tab .= "<td align=right>" . @number_format($ttg_premi) . "</td>";
			$tab .= "<td align=right>" . @number_format($ttg_premi + $gt_upah) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_upahmdr) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_premimdr) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_upahkrn) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_premikrn) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_upahmdr1) . "</td>";
			$tab .= "<td align=right>" . @number_format($gt_premimdr1) . "</td>";
			$ttg_upahmdr = $gt_upahmdr + $gt_upahkrn + $gt_upahmdr1;
			$ttg_premimdr = $gt_premimdr + $gt_premikrn + $gt_premimdr1;
			$tab .= "<td align=right>" . @number_format($ttg_upahmdr) . "</td>";
			$tab .= "<td align=right>" . @number_format($ttg_premimdr) . "</td>";
			$ttgt_biaya = $ttg_premimdr + $ttg_upahmdr + $ttg_premi + $gt_upah;
			$gttttttt += $ttgt_biaya;

			$tab .= "<td align=right>" . @number_format($ttgt_biaya) . "</td>";
			if ($ttgt_biaya > 0) {
				$tab .= "<td  align=right>" . @number_format($ttgt_biaya / $gt_kg, 2) . "</td>";
			} else {
				$tab .= "<td  align=right></td>";
			}
			$tab .= "</tr>";


			$tab .= "<tr class=rowcontent><td align=center colspan=33><hr></td></tr>";
		}
		$tab .= "</table><br>";

		if ($proses == 'preview') {
			$tab .= "<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
		} else {
			$tab .= "<table border=1 cellpadding=1 cellspacing=1 class=sortable>";
		}
		$tab .= "<thead><tr class=rowheader>";
		$rows = "rowspan=2";
		$tab .= "<th align=center " . $rows . " width=20px>No</th>
				<th align=center " . $rows . ">" . $_SESSION['lang']['pekerjaan'] . "</th>
				<th align=center " . $rows . ">" . $_SESSION['lang']['luas'] . "</th>
				<th align=center " . $rows . ">Output</th>
				<th align=center colspan=5>Tenaga Kerja</th>
				<th align=center " . $rows . ">Premi</th>
				<th align=center colspan=3 width=50px>Borongan</th>
				<th align=center colspan=6 >" . $_SESSION['lang']['material'] . "</th>
				<th align=center " . $rows . " width=30px>Total<br>Rupiah</th>
			</tr>
			<tr>
				<th align=center>KBL</th>
				<th align=center>KHT</th>
				<th align=center>KHL</th>
				<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
				<th align=center>" . $_SESSION['lang']['rupiah'] . "</th>
				<th align=center>Luas</th>
				<th align=center>Rp/Ha</th>
				<th align=center>Rupiah</th>
				<th align=center width=20px>No</th>
				<th align=center width=85px>" . $_SESSION['lang']['nama'] . "</th>
				<th align=center width=30px>Sat</th>
				<th align=center width=40px>Dosis</th>
				<th align=center width=50px>Jumlah</th>
				<th align=center width=80px>Rupiah</th>
			</tr>
			</thead>";
		$tab .= "<tr class=rowcontent><td align=left colspan=33><b>P E M E L I H A R A A N</b></td></tr>";
		$data = array();
		$str = "select norkb,tipetransaksi, kodekegiatan, sum(hasilkerja) as hasilkerja,sum(KBL) as KBL,sum(KHT) as KHT,sum(KHL) as KHL,sum(upah) as upah,sum(premi) as premi,sum(hasilkerjaborongan) as hasilkerjaborongan,sum(rupiahborongan) as rupiahborongan  from " . $dbname . ".kebun_rkbdt where  tipetransaksi='PEMEL' and norkb='" . $notransaksi . "' and periode='" . $periode . "' and kodeorg='" . $kodeorg . "' group by kodekegiatan";
		if (count(fetchData($str)) > 0) {
			$hasilkerja = array();
			$KBL = array();
			$KHT = array();
			$KHL = array();
			$upah = array();
			$premi = array();
			$bor = array();
			$rpbor = array();
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$norkb = $bar['norkb'];
				$kodekegiatan = $bar['kodekegiatan'];
				$jobgroup = substr($bar['kodekegiatan'], 0, 5);
				$data[$jobgroup][$bar['kodekegiatan']] = $bar['kodekegiatan'];
				@$hasilkerja[$jobgroup][$bar['kodekegiatan']] += $bar['hasilkerja'];
				@$KBL[$jobgroup][$bar['kodekegiatan']] += $bar['KBL'];
				@$KHT[$jobgroup][$bar['kodekegiatan']] += $bar['KHT'];
				@$KHL[$jobgroup][$bar['kodekegiatan']] += $bar['KHL'];
				@$upah[$jobgroup][$bar['kodekegiatan']] += $bar['upah'];
				@$premi[$jobgroup][$bar['kodekegiatan']] += $bar['premi'];
				@$bor[$jobgroup][$bar['kodekegiatan']] += $bar['hasilkerjaborongan'];
				@$rpbor[$jobgroup][$bar['kodekegiatan']] += $bar['rupiahborongan'];

				#sub total per job code
				@$thasilkerja[$jobgroup] += $bar['hasilkerja'];
				@$tKBL[$jobgroup] += $bar['KBL'];
				@$tKHT[$jobgroup] += $bar['KHT'];
				@$tKHL[$jobgroup] += $bar['KHL'];
				@$tupah[$jobgroup] += $bar['upah'];
				@$tpremi[$jobgroup] += $bar['premi'];
				@$tbor[$jobgroup] += $bar['hasilkerjaborongan'];
				@$trpbor[$jobgroup] += $bar['rupiahborongan'];
			}

			#bahan
			$strx = "select kodekegiatan,kodebarang, sum(kwantitas) as kwantitas, sum(luas) as luas, sum(jumlahrp) as jumlahrp  from " . $dbname . ".kebun_rkbmaterial where norkb='" . $norkb . "' and tipetransaksi='PEMEL' and periode='" . $periode . "' " . $where . " group by kodekegiatan, kodebarang";
			$jlh = count(fetchData($strx));
			$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);
			while ($barx = $resx->fetch()) {
				$jobgroup = substr($barx['kodekegiatan'], 0, 5);
				$data[$jobgroup][$barx['kodekegiatan']] = $barx['kodekegiatan'];
				$kodebarang[$barx['kodebarang']] = $barx['kodebarang'];
				$namabrng[$jobgroup][$barx['kodekegiatan']][$barx['kodebarang']] = $barx['kodebarang'];
				@$kwantitas[$jobgroup][$barx['kodekegiatan']][$barx['kodebarang']] += $barx['kwantitas'];
				@$luas[$jobgroup][$barx['kodekegiatan']][$barx['kodebarang']] += $barx['luas'];
				@$jumlahrp[$jobgroup][$barx['kodekegiatan']][$barx['kodebarang']] += $barx['jumlahrp'];

				$kegbrg[$jobgroup][$barx['kodekegiatan']] = $barx['kodekegiatan'];
				@$trpbhn[$jobgroup] += $barx['jumlahrp'];
			}

			$no = '';
			foreach ($data as $jobgroup => $valkeg) {
				$nmjob = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $jobgroup . "'");
				$tab .= "<tr class=rowcontent  style=background-color:#FAEBD7>";
				$tab .= "<td valign=top></td>";
				$tab .= "<td valign=top>" . $jobgroup . " - " . strtoupper(strtolower(@$nmjob[$jobgroup])) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($thasilkerja[$jobgroup], 2) . "</td>";
				@$sttltk = @$tKBL[$jobgroup] + @$tKBT[$jobgroup] + @$tKHL[$jobgroup];
				@$tnorma = @$thasilkerja[$jobgroup] / @$sttltk;
				$tab .= "<td valign=top align=right>" . @number_format($tnorma, 2) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($tKBL[$jobgroup], 2) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($tKBT[$jobgroup], 2) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($tKHL[$jobgroup], 2) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($sttltk, 2) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($tupah[$jobgroup]) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($tpremi[$jobgroup]) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($tbor[$jobgroup]) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($trpbor[$jobgroup] / $tbor[$jobgroup]) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($trpbor[$jobgroup]) . "</td>";
				$tab .= "<td valign=top align=right colspan=5></td>";
				$tab .= "<td valign=top align=right>" . @number_format($trpbhn[$jobgroup]) . "</td>";
				@$ttlperjob = $trpbhn[$jobgroup] + $tupah[$jobgroup] + $tpremi[$jobgroup] + $trpbor[$jobgroup];
				$tab .= "<td valign=top align=right>" . @number_format($ttlperjob) . "</td>";
				$tab .= "</tr>";
				$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
				foreach ($valkeg as $kodekeg => $kgkeg) {
					$no += 1;
					$tab .= "<tr class=rowcontent >";
					$tab .= "<td valign=top align=center>" . $no . "</td>";
					$tab .= "<td valign=top>" . $kgkeg . " - " . @$nmkeg[$kgkeg] . "</td>";
					$tab .= "<td valign=top align=right>" . @number_format($hasilkerja[$jobgroup][$kgkeg], 2) . "</td>";
					@$ttltk = $KBL[$jobgroup][$kgkeg] + $KBT[$jobgroup][$kgkeg] + $KHL[$jobgroup][$kgkeg];
					@$norma = $hasilkerja[$jobgroup][$kgkeg] / $ttltk;
					$tab .= "<td valign=top align=right>" . @number_format($norma, 2) . "</td>";
					$tab .= "<td valign=top align=right>" . @number_format($KBL[$jobgroup][$kgkeg], 2) . "</td>";
					$tab .= "<td valign=top align=right>" . @number_format($KBT[$jobgroup][$kgkeg], 2) . "</td>";
					$tab .= "<td valign=top align=right>" . @number_format($KHL[$jobgroup][$kgkeg], 2) . "</td>";
					$tab .= "<td valign=top align=right>" . @number_format($ttltk, 2) . "</td>";
					$tab .= "<td valign=top align=right>" . @number_format($upah[$jobgroup][$kgkeg]) . "</td>";
					$tab .= "<td valign=top align=right>" . @number_format($premi[$jobgroup][$kgkeg]) . "</td>";
					$tab .= "<td valign=top align=right>" . @number_format($bor[$jobgroup][$kgkeg]) . "</td>";
					$tab .= "<td valign=top align=right>" . @number_format($rpbor[$jobgroup][$kgkeg] / $bor[$jobgroup][$kgkeg]) . "</td>";
					$tab .= "<td valign=top align=right>" . @number_format($rpbor[$jobgroup][$kgkeg]) . "</td>";
					$tab .= "<td valign=top align=right colspan=6>";
					$ttlrpbahan = '';
					if (@$kegbrg[$jobgroup][$kodekeg] != '') {
						$nox = '';
						if ($proses == 'preview') {
							$tab .= "<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
						} else {
							$tab .= "<table border=1 cellpadding=1 cellspacing=1 class=sortable>";
						}
						$tab .= "<tbody>";
						foreach ($kodebarang as $nmbarang) {
							if (@$namabrng[$jobgroup][$kodekeg][$nmbarang] != '') {
								$nox++;
								$nmsat = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan', "kodebarang='" . $nmbarang . "'");
								@$optnmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $nmbarang . "'");
								$tab .= "<tr class=rowcontent>";
								$tab .= "<td width=20px align=center>" . $nox . "</td>";
								$tab .= "<td width=85px>" . substr(ucfirst(strtolower($optnmbrg[$nmbarang])), 0, 12) . "...</td>";
								$tab .= "<td width=30px>" . $nmsat[$nmbarang] . "</td>";
								if ($kwantitas[$jobgroup][$kodekeg][$nmbarang] != 0) {
									$tab .= "<td width=40px align=right>" . @number_format($kwantitas[$jobgroup][$kodekeg][$nmbarang] / $luas[$jobgroup][$kodekeg][$nmbarang], 2) . "</td>";
								} else {
									$tab .= "<td width=40px align=right></td>";
								}
								$tab .= "<td width=50px align=right>" . @number_format($kwantitas[$jobgroup][$kodekeg][$nmbarang], 2) . "</td>";
								$tab .= "<td width=80px align=right>" . @number_format($jumlahrp[$jobgroup][$kodekeg][$nmbarang]) . "</td>";
								@$ttlrpbahan[$jobgroup] += $jumlahrp[$jobgroup][$kodekeg][$nmbarang];
							}
						}
						$tab .= "</tr>";
						$tab .= "</tbody>";
						$tab .= "</table>";
						@$ttlbahan += $ttlrpbahan[$jobgroup];
					}


					$tab .= "</td>";
					@$totalrp = $upah[$jobgroup][$kgkeg] + $premi[$jobgroup][$kgkeg] + $rpbor[$jobgroup][$kgkeg] + $ttlrpbahan[$jobgroup];
					$tab .= "<td valign=top align=right>" . @number_format($totalrp) . "</td>";

					@$ttlluas += $hasilkerja[$jobgroup][$kgkeg];
					@$ttlkbl += $KBL[$jobgroup][$kgkeg];
					@$ttlkht += $KHT[$jobgroup][$kgkeg];
					@$ttlkhl += $KHL[$jobgroup][$kgkeg];
					@$ttlupah += $upah[$jobgroup][$kgkeg];
					@$ttlpremi += $premi[$jobgroup][$kgkeg];
					@$ttlluasbor += $bor[$jobgroup][$kgkeg];
					@$ttlrpbor += $rpbor[$jobgroup][$kgkeg];
					@$gtrp += $totalrp;
				}
			}
			$tab .= "</tr>";
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td colspan=2 bgcolor=cyan align=center><b>TOTAL</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlluas, 2) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format(($ttlkbl + $ttlkht + $ttlkhl) / $ttlluas, 2) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlkbl, 2) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlkht, 2) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlkhl, 2) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlkbl + $ttlkht + $ttlkhl, 2) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlupah) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlpremi) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlluasbor, 2) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlrpbor / $ttlluasbor) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlrpbor) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right colspan=5></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($ttlbahan) . "</b></td>";
			$tab .= "<td bgcolor=cyan align=right><b>" . @number_format($gtrp) . "</b></td>";
			$tab .= "</tr>";
			$gttttttt += $gtrp;

			$tab .= "<tr class=rowcontent><td colspan=21><hr></td></tr>";
			$tab .= "</table>";
		}
		$tab .= "<br>";

		if ($proses == 'preview') {
			$tab .= "<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
		} else {
			$tab .= "<table border=1 cellpadding=1 cellspacing=1 class=sortable>";
		}
		$tab .= "<thead><tr class=rowheader>";
		$rows = "rowspan=4";
		$tab .= "<th align=center " . $rows . " width=20px>No</th>
				<th align=center " . $rows . ">" . $_SESSION['lang']['divisi'] . "</th>
				<th align=center " . $rows . ">TT</th>
				<th align=center " . $rows . ">" . $_SESSION['lang']['produksi'] . " (Kg)</th>
				<th align=center " . $rows . " width=50px>Jarak ke PKS (KM)</th>
				
				<th align=center rowspan=1 colspan=8>Angkutan Sendiri</th>
				<th align=center rowspan=1 colspan=4>Angkutan Kontrak</th>
				<th align=center rowspan=1 colspan=4>Langsir Along - Along</th>
				<th align=center rowspan=1 colspan=14>Biaya Bongkar Muat</th>
				<th align=center rowspan=1 colspan=2>Total<br>Biaya</th>
			</tr>
			<tr>
				<th align=center rowspan=3>%</th>
				<th align=center rowspan=3>Kap<br>Kg</th>
				<th align=center rowspan=3>Trip<br>PKS</th>
				<th align=center rowspan=3>KM</th>
				<th align=center rowspan=3>Kg</th>
				<th align=center rowspan=3>Rp/Kg</th>
				<th align=center rowspan=3>Rp/KM</th>
				<th align=center rowspan=3>Total Rp</th>
				
				<th align=center rowspan=3>%</th>
				<th align=center rowspan=3>Kg</th>
				<th align=center rowspan=3>Rp/Kg</th>
				<th align=center rowspan=3>Total Rp</th>
				
				<th align=center rowspan=3>%</th>
				<th align=center rowspan=3>Kg</th>
				<th align=center rowspan=3>Rp/Kg</th>
				<th align=center rowspan=3>Total Rp</th>
				
				<th align=center colspan=9>Upah</th>
				<th align=center colspan=3>Premi</th>
				<th align=center colspan=2>Total</th>
				
				<th align=center rowspan=3>Rp</th>
				<th align=center rowspan=3>Rp/Kg</th>
			</tr>
			<tr>
				<th align=center rowspan=2>Output<br>Kg/HK</th>
				<th align=center rowspan=2>Basis<br>Kg/HK</th>
				<th align=center rowspan=1 colspan=4>HK</th>
				<th align=center rowspan=2>Total<br>Kg Basis</th>
				<th align=center rowspan=1 colspan=2>Total Upah</th>
				<th align=center rowspan=2>Kg</th>
				<th align=center rowspan=2>Rp/Kg</th>
				<th align=center rowspan=2>Rp</th>
				<th align=center rowspan=2>Rp</th>
				<th align=center rowspan=2>Rp/Kg</th>
			</tr>
			<tr>
				<th align=center >KBL</th>
				<th align=center >KHT</th>
				<th align=center >KHL</th>
				<th align=center >Total</th>
				<th align=center >Rp</th>
				<th align=center >Rp/Kg</th>
				
			</tr>
			</thead>";

		$tab .= "<tr class=rowcontent><td align=left colspan=37><b>A N G K U T A N        T B S</b></td></tr>";

		$str = "select sum(tonalong) as tonalong, sum(rpalong) as rpalong, tahuntanam, sum(hasilkerjakg) as hasilkerjakg, sum(km) as km, sum(trippks) as trippks, sum(kgsendiri) as kgsendiri, sum(ttlrpsendiri) as ttlrpsendiri, sum(hasilkerjaborongan) as hasilkerjaborongan, sum(rupiahborongan) as rupiahborongan, sum(KBL) as KBL, sum(KHT) as KHT, sum(KHL) as KHL, sum(ttlkgbasis) as ttlkgbasis, sum(upah) as upah, sum(kgpremi) as kgpremi, sum(premi) as premi from " . $dbname . ".kebun_rkbdt where tipetransaksi='ANGKUT' and norkb='" . $notransaksi . "' and periode='" . $periode . "' and kodeorg='" . $kodeorg . "' group by tahuntanam order by tahuntanam asc";
		if (count(fetchData($str)) > 0) {

			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$no = '';
			while ($bar = $res->fetch()) {
				$no += 1;
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td align=center>" . $no . "</td>";
				$tab .= "<td align=center></td>";
				$tab .= "<td align=center>" . $bar['tahuntanam'] . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['hasilkerjakg']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['km'] / $bar['trippks'] / 2) . "</td>";
				$persensendiri = $bar['kgsendiri'] / $bar['hasilkerjakg'];
				$tab .= "<td align=right>" . @number_format($persensendiri * 100) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['kgsendiri'] / $bar['trippks']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['trippks']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['km']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['kgsendiri']) . "</td>";
				if ($bar['ttlrpsendiri'] != 0) {
					$tab .= "<td align=right>" . @number_format($bar['ttlrpsendiri'] / @$bar['kgsendiri']) . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['ttlrpsendiri'] / @$bar['km']) . "</td>";
				} else {
					$tab .= "<td align=right>0</td>";
					$tab .= "<td align=right>0</td>";
				}
				$tab .= "<td align=right>" . @number_format($bar['ttlrpsendiri']) . "</td>";
				$tab .= "<td align=right>" . @number_format(100 - ($persensendiri * 100)) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['hasilkerjaborongan']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['rupiahborongan'] / $bar['hasilkerjaborongan']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['rupiahborongan']) . "</td>";

				$tab .= "<td align=right>" . @number_format($bar['tonalong'] / $bar['hasilkerjakg'] * 100) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['tonalong']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['rpalong'] / $bar['tonalong']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['rpalong']) . "</td>";



				$tab .= "<td align=right>" . @number_format($bar['hasilkerjakg'] / ($bar['KBL'] + $bar['KHT'] + $bar['KHL'])) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['ttlkgbasis'] / ($bar['KBL'] + $bar['KHT'] + $bar['KHL'])) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['KBL']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['KHT']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['KHL']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['KBL'] + $bar['KHT'] + $bar['KHL']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['ttlkgbasis']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['upah']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['upah'] / $bar['ttlkgbasis'], 2) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['kgpremi']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['premi'] / $bar['kgpremi']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['premi']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['upah'] + $bar['premi']) . "</td>";
				$tab .= "<td align=right>" . @number_format(($bar['upah'] + $bar['premi']) / $bar['kgsendiri']) . "</td>";
				$tab .= "<td align=right>" . @number_format($bar['upah'] + $bar['premi'] + $bar['rupiahborongan'] + $bar['ttlrpsendiri']) . "</td>";
				$tab .= "<td align=right>" . @number_format(($bar['upah'] + $bar['premi'] + $bar['rupiahborongan'] + $bar['ttlrpsendiri']) / $bar['hasilkerjakg']) . "</td>";

				@$t_hasilkerjakg += $bar['hasilkerjakg'];
				@$t_trippks += $bar['trippks'];
				@$t_km += $bar['km'];
				@$t_kgsendiri += $bar['kgsendiri'];
				@$t_ttlrpsendiri += $bar['ttlrpsendiri'];
				@$t_hasilkerjaborongan += $bar['hasilkerjaborongan'];
				@$t_rupiahborongan += $bar['rupiahborongan'];
				@$t_KBL += $bar['KBL'];
				@$t_KHT += $bar['KHT'];
				@$t_KHL += $bar['KHL'];
				@$t_ttlkgbasis += $bar['ttlkgbasis'];
				@$t_upah += $bar['upah'];
				@$t_kgpremi += $bar['kgpremi'];
				@$t_premi += $bar['premi'];
				@$t_tonalong += $bar['tonalong'];
				@$t_rpalong += $bar['rpalong'];
			}

			$tab .= "<tr class=rowcontent  style=background-color:#FAEBD7>";
			$tab .= "<td align=center colspan=3>TOTAL</td>";
			$tab .= "<td align=right>" . @number_format($t_hasilkerjakg) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_km / $t_trippks / 2) . "</td>";
			$t_persensendiri = $t_kgsendiri / $t_hasilkerjakg;
			$tab .= "<td align=right>" . @number_format($t_persensendiri * 100) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_kgsendiri / $t_trippks) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_trippks) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_km) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_kgsendiri) . "</td>";
			if ($t_ttlrpsendiri != 0) {
				$tab .= "<td align=right>" . @number_format($t_ttlrpsendiri / @$t_kgsendiri) . "</td>";
				$tab .= "<td align=right>" . @number_format($t_ttlrpsendiri / @$t_km) . "</td>";
			} else {
				$tab .= "<td align=right>0</td>";
				$tab .= "<td align=right>0</td>";
			}
			$tab .= "<td align=right>" . @number_format($t_ttlrpsendiri) . "</td>";
			$tab .= "<td align=right>" . @number_format(100 - ($t_persensendiri * 100)) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_hasilkerjaborongan) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_rupiahborongan / $t_hasilkerjaborongan) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_rupiahborongan) . "</td>";

			$tab .= "<td align=right>" . @number_format(($t_tonalong / $t_hasilkerjakg) * 100) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_tonalong) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_rpalong / $t_tonalong) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_rpalong) . "</td>";

			$tab .= "<td align=right>" . @number_format($t_hasilkerjakg / ($t_KBL + $t_KHT + $t_KHL)) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_ttlkgbasis / ($t_KBL + $t_KHT + $t_KHL)) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_KBL) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_KHT) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_KHL) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_KBL + $t_KHT + $t_KHL) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_ttlkgbasis) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_upah) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_upah / $t_ttlkgbasis, 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_kgpremi) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_premi / $t_kgpremi) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_premi) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_upah + $t_premi) . "</td>";
			$tab .= "<td align=right>" . @number_format(($t_upah + $t_premi) / $t_kgsendiri) . "</td>";
			$tab .= "<td align=right>" . @number_format($t_upah + $t_premi + $t_rupiahborongan + $t_ttlrpsendiri) . "</td>";
			$tab .= "<td align=right>" . @number_format(($t_upah + $t_premi + $t_rupiahborongan + $t_ttlrpsendiri) / $t_hasilkerjakg) . "</td>";
			$tab .= "</tr>";
			$gttttttt += ($t_upah + $t_premi + $t_rupiahborongan + $t_ttlrpsendiri);

			$tab .= "<tr class=rowcontent><td align=center colspan=37><hr></td></tr>";
		}
		$tab .= "</table><br>";

		if ($proses == 'preview') {
			$tab .= "<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
		} else {
			$tab .= "<table border=1 cellpadding=1 cellspacing=1 class=sortable>";
		}
		$tab .= "
			<thead><tr class=rowheader>";

		$rows = "rowspan=2";
		$tab .= "<th align=center " . $rows . " width=20px>No</th>
				<th align=center " . $rows . ">" . $_SESSION['lang']['divisi'] . "</th>
				<th align=center " . $rows . ">" . $_SESSION['lang']['jabatan'] . "</th>
				
				<th align=center rowspan=1 colspan=4>Tenaga Kerja</th>
				<th align=center rowspan=2 colspan=1>Upah</th>
				<th align=center rowspan=1 colspan=4>Lembur dan Premi</th>
				<th align=center rowspan=1 colspan=3>Material</th>
				<th align=center rowspan=2 colspan=1>Totah Rupiah</th>
			</tr>
			<tr>
				<th align=center>KBL</th>
				<th align=center>KHT</th>
				<th align=center>KHL</th>
				<th align=center>Total</th>
				<th align=center>Jam</th>
				<th align=center>Rp/Jam</th>
				<th align=center>Lembur</th>
				<th align=center>Premi</th>
				<th align=center width=205px>Nama</th>
				<th align=center width=30px>Sat</th>
				<th align=center width=85px>Jumlah</th>
				
			</tr>
			</thead>";
		$tab .= "<tr class=rowcontent><td colspan=17><b>U M U M</b></td></tr>";
		$str = "select * from " . $dbname . ".kebun_rkbdt where   tipetransaksi='UMUM' and norkb='" . $notransaksi . "' and periode='" . $periode . "' and kodeorg='" . $kodeorg . "' order by divisi asc, kodekegiatan asc";
		if (count(fetchData($str)) > 0) {
			$gtrp = '';
			@$ttlkbl = '';
			@$ttlkht = '';
			@$ttlkhl = '';
			@$ttlupah = '';
			@$ttlpremi = '';
			@$ttljamlembur = '';
			@$ttlrplembur = '';
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$no = '';
			while ($bar = $res->fetch()) {
				$nmjab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $bar['kodekegiatan'] . "'");
				$no += 1;
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td valign=top align=center>" . $no . "</td>";
				if ($bar['divisi'] == '') {
					$kodediv = 'UMUM';
				} else {
					$kodediv = $bar['divisi'];
				}
				$tab .= "<td valign=top>" . $kodediv . "</td>";
				$tab .= "<td valign=top>" . $nmjab[$bar['kodekegiatan']] . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($bar['KBL']) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($bar['KHT']) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($bar['KHL']) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($bar['KBL'] + $bar['KHT'] + $bar['KHL']) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($bar['upah']) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($bar['jamlembur']) . "</td>";
				if ($bar['jamlembur'] != 0 && $bar['rplembur'] != 0) {
					$tab .= "<td valign=top align=right>" . @number_format($bar['rplembur'] / $bar['jamlembur']) . "</td>";
				} else {
					$tab .= "<td valign=top align=right>0</td>";
				}
				$tab .= "<td valign=top align=right>" . @number_format($bar['rplembur']) . "</td>";
				$tab .= "<td valign=top align=right>" . @number_format($bar['premi']) . "</td>";
				$tab .= "<td valign=top align=right colspan=3>";
				$strx = "select * from " . $dbname . ".kebun_rkbmaterial where norkb='" . $bar['norkb'] . "' and tipetransaksi='UMUM' and periode='" . $periode . "' and kodeorg='" . $bar['kodeorg'] . "' and divisi='" . $bar['divisi'] . "' and kodekegiatan='" . $bar['kodekegiatan'] . "'";
				$jlh = fetchData($strx);
				$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$nox = '';
				$ttlrpbahan = '';
				if (count($jlh) > 0) {
					if ($proses == 'preview') {
						$tab .= "<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
					} else {
						$tab .= "<table border=1 cellpadding=1 cellspacing=1 class=sortable>";
					}
					$tab .= "<tbody>";
					while ($barx = $resx->fetch()) {
						$nox++;
						$nmsat = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan', "kodebarang='" . $barx['kodebarang'] . "'");
						$optnmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $barx['kodebarang'] . "'");
						$tab .= "<tr class=rowcontent>";
						if (strlen($optnmbrg[$barx['kodebarang']]) > 30) {
							$namabarang = "" . substr(ucfirst(strtolower($optnmbrg[$barx['kodebarang']])), 0, 30) . "...";
						} else {
							$namabarang = "" . ucfirst(strtolower($optnmbrg[$barx['kodebarang']])) . "";
						}
						$tab .= "<td width=205px>" . $namabarang . "</td>";
						$tab .= "<td width=30px>" . $nmsat[$barx['kodebarang']] . "</td>";
						$tab .= "<td width=85px align=right>" . @number_format($barx['kwantitas'], 2) . "</td>";
						$ttlrpbahan += $barx['jumlahrp'];
					}
					$tab .= "</tr>";
					$tab .= "</tbody>";
					$tab .= "</table>";
				}

				$tab .= "</td>";
				$totalrp = $bar['upah'] + $bar['premi'] + $bar['rplembur'];
				$tab .= "<td valign=top align=right>" . @number_format($totalrp) . "</td>";

				@$ttlkbl += $bar['KBL'];
				@$ttlkht += $bar['KHT'];
				@$ttlkhl += $bar['KHL'];
				@$ttlupah += $bar['upah'];
				@$ttlpremi += $bar['premi'];
				@$ttljamlembur += $bar['jamlembur'];
				@$ttlrplembur += $bar['rplembur'];
				@$gtrp += $totalrp;
			}
			$tab .= "</tr>";
			$tab .= "<tr class=rowcontent  style=background-color:#FAEBD7>";
			$tab .= "<td colspan=3 align=center><b>TOTAL</b></td>";
			$tab .= "<td align=right><b>" . @number_format($ttlkbl) . "</b></td>";
			$tab .= "<td align=right><b>" . @number_format($ttlkht) . "</b></td>";
			$tab .= "<td align=right><b>" . @number_format($ttlkhl) . "</b></td>";
			$tab .= "<td align=right><b>" . @number_format($ttlkbl + $ttlkht + $ttlkhl) . "</b></td>";
			$tab .= "<td align=right><b>" . @number_format($ttlupah) . "</b></td>";
			$tab .= "<td align=right><b>" . @number_format($ttljamlembur) . "</b></td>";
			$tab .= "<td align=right><b>" . @number_format($ttlrplembur / $ttljamlembur) . "</b></td>";
			$tab .= "<td align=right><b>" . @number_format($ttlrplembur) . "</b></td>";
			$tab .= "<td align=right><b>" . @number_format($ttlpremi) . "</b></td>";
			$tab .= "<td align=right colspan=3></td>";
			$tab .= "<td align=right><b>" . @number_format($gtrp) . "</b></td>";
			$tab .= "</tr>";
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td colspan=16><hr></td>";
			$tab .= "</tr>";
			$tab .= "</table>";

			$gttttttt += $gtrp;
		}

		$tab .= "<br>";

		if ($proses == 'preview') {
			$tab .= "<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
		} else {
			$tab .= "<table border=1 cellpadding=1 cellspacing=1 class=sortable>";
		}
		$tab .= "<thead><tr class=rowheader>";
		$rows = "rowspan=3";
		$tab .= "<th align=center " . $rows . " width=20px>No</th>
			<th align=center " . $rows . ">" . $_SESSION['lang']['departemen'] . "</th>
			<th align=center " . $rows . ">" . $_SESSION['lang']['jabatan'] . "</th>
			<th align=center " . $rows . ">Komponen Gaji</th>
			<th align=center " . $rows . ">Keterangan</th>
			<th align=center colspan=12>Tipe Karyawan</th>
		</tr>
		<tr>
			<th align=center colspan=3>KBL</th>
			<th align=center colspan=3>KHT</th>
			<th align=center colspan=3>KHL</th>
			<th align=center colspan=3>Total</th>
		</tr>
		<tr>
			<th align=center>TK</th>
			<th align=center>HK</th>
			<th align=center>Rupiah</th>
			<th align=center>TK</th>
			<th align=center>HK</th>
			<th align=center>Rupiah</th>
			<th align=center>TK</th>
			<th align=center>HK</th>
			<th align=center>Rupiah</th>
			<th align=center>TK</th>
			<th align=center>HK</th>
			<th align=center>Rupiah</th>
			
		</tr>
		</thead>";
		$tab .= "<tr class=rowcontent><td colspan=17><b>S U P P O R T</b></td></tr>";
		$where = '';
		if ($divisi !== '') {
			$where = " and divisi='" . $divisi . "'";
		}
		$str = "select * from " . $dbname . ".kebun_rkbsupport where   tipetransaksi='SUPPORT' and norkb='" . $notransaksi . "' and periode='" . $periode . "' and kodeorg='" . $kodeorg . "'";
		if (count(fetchData($str)) > 0) {

			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$data = array();
			while ($bar = $res->fetch()) {
				$data[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']] = $bar['tipekary'];
				@$tk[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']] += $bar['tk'];
				@$hk[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']] += $bar['hk'];
				@$rupiah[$bar['dept']][$bar['jabatan']][$bar['compgaji']][$bar['tipekary']] += $bar['rupiah'];
				$tpkary[$bar['tipekary']] = $bar['tipekary'];
				@$ket[$bar['dept']][$bar['jabatan']][$bar['compgaji']] = $bar['keterangan'];
			}
			$gtttk = '';
			$gtthk = '';
			$gttrp = '';
			if (count($data) > 0) {
				$no = '';
				$optdept = makeOption($dbname, 'sdm_5departemen', 'kode,nama');
				$optjab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
				$optcompt = makeOption($dbname, 'sdm_ho_component', 'id,name');
				foreach ($data as $dept => $valjab) {
					foreach ($valjab as $jabatan => $valkompgaji) {
						foreach ($valkompgaji as $kompgaji => $valtipekary) {
							$no++;
							$tab .= "<tr class=rowcontent>";
							$tab .= "<td align=center>" . $no . "</td>";
							$tab .= "<td>" . $optdept[$dept] . "</td>";
							$tab .= "<td>" . $optjab[$jabatan] . "</td>";
							$tab .= "<td>" . $optcompt[$kompgaji] . "</td>";
							$tab .= "<td>" . $ket[$dept][$jabatan][$kompgaji] . "</td>";
							$ttlrp = $ttlhk = $ttltk = '';
							foreach ($tpkary as $tipekary) {
								$tab .= "<td width=34px align=right>" . @number_format($tk[$dept][$jabatan][$kompgaji][$tipekary]) . "</td>";
								$tab .= "<td width=43px align=right>" . @number_format($hk[$dept][$jabatan][$kompgaji][$tipekary]) . "</td>";
								$tab .= "<td width=62px align=right>" . @number_format($rupiah[$dept][$jabatan][$kompgaji][$tipekary]) . "</td>";
								@$ttltk += $tk[$dept][$jabatan][$kompgaji][$tipekary];
								@$ttlhk += $hk[$dept][$jabatan][$kompgaji][$tipekary];
								@$ttlrp += $rupiah[$dept][$jabatan][$kompgaji][$tipekary];

								@$stttk[$jabatan][$tipekary] += $tk[$dept][$jabatan][$kompgaji][$tipekary];
								@$sttlhk[$jabatan][$tipekary] += $hk[$dept][$jabatan][$kompgaji][$tipekary];
								@$sttlrp[$jabatan][$tipekary] += $rupiah[$dept][$jabatan][$kompgaji][$tipekary];

								@$gstttk[$jabatan] += $tk[$dept][$jabatan][$kompgaji][$tipekary];
								@$gsttlhk[$jabatan] += $hk[$dept][$jabatan][$kompgaji][$tipekary];
								@$gsttlrp[$jabatan] += $rupiah[$dept][$jabatan][$kompgaji][$tipekary];

								@$gtstttk[$tipekary] += $tk[$dept][$jabatan][$kompgaji][$tipekary];
								@$gtsttlhk[$tipekary] += $hk[$dept][$jabatan][$kompgaji][$tipekary];
								@$gtsttlrp[$tipekary] += $rupiah[$dept][$jabatan][$kompgaji][$tipekary];
							}
							$tab .= "<td width=34px align=right>" . @number_format($ttltk) . "</td>";
							$tab .= "<td width=44px align=right>" . @number_format($ttlhk) . "</td>";
							$tab .= "<td width=81px align=right>" . @number_format($ttlrp) . "</td>";
							$tab .= "</tr>";
						}
						$tab .= "<tr class=rowcontent style=background-color:cyan>";
						$tab .= "<td colspan=2></td><td colspan=3 align=left>Sub Total " . $optjab[$jabatan] . "</td>";
						foreach ($tpkary as $tipekary) {
							$tab .= "<td align=right>" . @number_format($stttk[$jabatan][$tipekary]) . "</td>";
							$tab .= "<td align=right>" . @number_format($sttlhk[$jabatan][$tipekary]) . "</td>";
							$tab .= "<td align=right>" . @number_format($sttlrp[$jabatan][$tipekary]) . "</td>";
						}
						$tab .= "<td align=right>" . @number_format($gstttk[$jabatan]) . "</td>";
						$tab .= "<td align=right>" . @number_format($gsttlhk[$jabatan]) . "</td>";
						$tab .= "<td align=right>" . @number_format($gsttlrp[$jabatan]) . "</td>";
					}
				}
				$tab .= "<tr class=rowcontent style=background-color:skyblue>";
				$tab .= "<td colspan=5 align=center><b>Total</b></td>";
				foreach ($tpkary as $tipekary) {
					$tab .= "<td align=right><b>" . @number_format($gtstttk[$tipekary]) . "</b></td>";
					$tab .= "<td align=right><b>" . @number_format($gtsttlhk[$tipekary]) . "</b></td>";
					$tab .= "<td align=right><b>" . @number_format($gtsttlrp[$tipekary]) . "</b></td>";

					@$gtttk += $gtstttk[$tipekary];
					@$gtthk += $gtsttlhk[$tipekary];
					@$gttrp += $gtsttlrp[$tipekary];
				}
				$tab .= "<td align=right><b>" . @number_format($gtttk) . "</b></td>";
				$tab .= "<td align=right><b>" . @number_format($gtthk) . "</b></td>";
				$tab .= "<td align=right><b>" . @number_format($gttrp) . "</b></td>";
				$gttttttt += $gttrp;
			}



			$tab .= "</tbody>";
			$tab .= "</table>";
		}
		$tab .= "<br>";
		if ($proses == 'preview') {
			$tab .= "<table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>";
		} else {
			$tab .= "<table border=1 cellpadding=1 cellspacing=1 class=sortable>";
		}
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=center><b>G R A N D&nbsp;&nbsp;&nbsp;&nbsp;T O T A L</b></td>";
		$tab .= "<td width=200px align=right><b>" . number_format($gttttttt) . "</b></td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		echo $tab;
		break;
}
