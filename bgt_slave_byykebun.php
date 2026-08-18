<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

$method = checkPostGet('method', '');
if (count($_POST) > 0) {
	$param = $_POST;
} else {
	$param = $_GET;
}


$str = "select * from " . $dbname . ".bgt_blok";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmorg[$bar['kodeblok']] = $bar['kodeblok'];
}

$str = "select * from " . $dbname . ".organisasi";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmorg[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
}

$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmkode = makeOption($dbname, 'bgt_kode', 'kodebudget,nama');
$akun  = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,noakun', "kodekegiatan='" . $param['kegiatan'] . "'");

$param['noakun']     = $akun[$param['kegiatan']];
$param['rupiah']     = str_replace(",", "", $param['rupiah']);
$param['jhk']        = str_replace(",", "", $param['jhk']);
$param['norma']      = str_replace(",", "", $param['norma']);
$param['jumlah']     = str_replace(",", "", $param['jumlah']);
$param['totalvolume'] = str_replace(",", "", $param['totalvolume']);

$tipebudget = 'ESTATE';
$whr = " and kodeorg like '" . $param['kodeorg'] . "%' and tipebudget='ESTATE' and kodebudget != 'UMUM' and tahunbudget='" . $param['tahun'] . "' and pta='BGT'";

// echo"<pre>";
// print_r($param);
// echo"</pre>";
// exit("error");
switch ($method) {
	case 'formatupload':
		#1. Cek areal statement
		#2. Cek budget HK
		#3. Cek harga material
		#4. Cek Budget Traksi
		#5. 

		$tab = "<table width=100%>";
		$tab .= "<tr><td align=center>Form Utama</td></tr>";
		$tab .= "<tr><td align=center><button class=mybutton style=height:35px;width:250px>1. Form Upload Budget Kebun</button></td></tr>";
		$tab .= "<tr><td align=center>Form Pendukung</td></tr>";
		$tab .= "<tr><td align=center><button class=mybutton style=height:35px;width:250px onclick=getpendupload('1');>1. Master Kode Divisi</button></td></tr>";
		$tab .= "<tr><td align=center><button class=mybutton style=height:35px;width:250px onclick=getpendupload('2');>2. Master Kode Kegiatan</button></td></tr>";
		$tab .= "<tr><td align=center><button class=mybutton style=height:35px;width:250px onclick=getpendupload('3');>3. Master Kode Budget</button></td></tr>";
		$tab .= "<tr><td align=center><button class=mybutton style=height:35px;width:250px onclick=getpendupload('4');>4. Master Arus Kas</button></td></tr>";
		$tab .= "<tr><td align=center><button class=mybutton style=height:35px;width:250px onclick=getpendupload('5');>5. Master Harga Barang</button></td></tr>";
		$tab .= "<tr><td align=center><button class=mybutton style=height:35px;width:250px onclick=getpendupload('6');>6. Master Kendaraan</button></td></tr>";
		$tab .= "</table>";

		echo $tab;
		break;
	case 'sebartt':
		try {
			$owlPDO->beginTransaction();

			$ttlpersen = 0;
			for ($i == 1; $i <= 12; $i++) {
				if ($param['persen'][$i] == '') {
					$param['persen'][$i] = 0;
				}
				$ttlpersen += $param['persen'][$i];
			}
			if ($ttlpersen == 0) {
				throw new PDOException("Persen sebaran belum ada.");
			}

			$str = "select * from " . $dbname . ".bgt_budget where `tahunbudget` = '" . $param['tahun'] . "' and `tipebudget` = 'ESTATE' and kodebudget != 'UMUM' and pta='BGT' and kodeorg like '" . $param['divisi'] . "%' and `kegiatan` = '" . $param['kegiatan'] . "' and kodeorg in (select kodeblok from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and kodeblok like '" . $param['divisi'] . "%' and thntnm='" . $param['tt'] . "')";
			$res = fetchdata($str);
			if (count($res) > 0) {
				foreach ($res as $bar) {
					$str = "delete from " . $dbname . ".bgt_distribusi where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);

					if ($bar['tutup'] == '1') {
						throw new PDOException("Budget sudah ditutup.");
					}
					$str = "insert into " . $dbname . ".bgt_distribusi (`kunci`";
					for ($i = 1; $i <= 12; $i++) {
						$str .= ",`rp" . addZero($i, 2) . "`";
						$str .= ",`fis" . addZero($i, 2) . "`";
					}
					$str .= ") values('" . $bar['kunci'] . "'";
					for ($i = 1; $i <= 12; $i++) {
						$str .= ",'" . $param['persen'][$i] / $ttlpersen * $bar['rupiah'] . "'";
						$str .= ",'" . $param['persen'][$i] / $ttlpersen * $bar['jumlah'] . "'";
					}
					$str .= ");";
					$owlPDO->exec($str);
				}
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'sebardetail':
		try {
			$owlPDO->beginTransaction();

			$ttlpersen = 0;
			for ($i == 1; $i <= 12; $i++) {
				if ($param['persen'][$i] == '') {
					$param['persen'][$i] = 0;
				}
				$ttlpersen += $param['persen'][$i];
			}
			if ($ttlpersen == 0) {
				throw new PDOException("Persen sebaran belum ada.");
			}
			if (is_array($param['index'])) {
				foreach ($param['index'] as $key => $kunci) {
					$str = "delete from " . $dbname . ".bgt_distribusi where kunci='" . $kunci . "'";
					$owlPDO->exec($str);

					$str = "select * from " . $dbname . ".bgt_budget where kunci='" . $kunci . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						if ($bar['tutup'] == '1') {
							throw new PDOException("Budget sudah ditutup.");
						}
						$str = "insert into " . $dbname . ".bgt_distribusi (`kunci`";
						for ($i = 1; $i <= 12; $i++) {
							$str .= ",`rp" . addZero($i, 2) . "`";
							$str .= ",`fis" . addZero($i, 2) . "`";
						}
						$str .= ") values('" . $kunci . "'";
						for ($i = 1; $i <= 12; $i++) {
							$str .= ",'" . $param['persen'][$i] / $ttlpersen * $bar['rupiah'] . "'";
							$str .= ",'" . $param['persen'][$i] / $ttlpersen * $bar['jumlah'] . "'";
						}
						$str .= ");";
						$owlPDO->exec($str);
					}
				}
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'formcaribarang':
		echo "<table>
				<tr>
					<td>Find</td>
					<td><input type=text class=myinputtext id=kodebarangcari onkeypress='enterkey(event,caribarang)' style=width:145px;></td>
					<td><button class=mybutton onclick='caribarang()'>" . $_SESSION['lang']['find'] . "</button></td>
				</tr>
			</table>";
		echo "<table class='sortable' cellspacing=1 cellpadding=5 border=0 width=100%>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center width=70px>" . $_SESSION['lang']['kodebarang'] . "</th>
					<th align=center>" . $_SESSION['lang']['namabarang'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['harga'] . "</th>
				</tr>
			</thead><tbody id=contcaribarang></tbody>
			</table>
			<input hidden id=sumbermat value='" . $param['sumber'] . "'>
			";
		break;
	case 'caribarang':

		if ($nmBrg == '') {
			@$nmBrg = $kdBarang;
		}

		$str = "select distinct regional from " . $dbname . ".bgt_regional_assignment where kodeunit='" . substr($param['kodeorg'], 0, 4) . "' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];

		$whr = "";
		if ($param['klbarang'] != '') {
			$whr .= " and left(kodebarang,3)='" . $param['klbarang'] . "'";
		}
		if ($param['kodebarang'] != '') {
			$whr .= " and kodebarang in (select kodebarang from " . $dbname . ".log_5masterbarang where kodebarang like '%" . $param['kodebarang'] . "%' or namabarang like '%" . $param['kodebarang'] . "%')";
		}
		$no = 0;
		$str = "select * from " . $dbname . ".bgt_masterbarang where regional='" . $region . "' and tahunbudget='" . $param['tahun'] . "' " . $whr . " ";
		$res = fetchData($str);
		foreach ($res as $bar) {
			$s = "select namabarang,satuan from " . $dbname . ".log_5masterbarang where kodebarang='" . $bar['kodebarang'] . "'";
			$nm = fetchData($s)[0];

			$no += 1;
			if ($bar['hargasatuan'] > 0) {
				$set = "style=cursor:pointer onclick=\"setdata('" . $bar['kodebarang'] . "','" . $nm['namabarang'] . "','" . $nm['satuan'] . "','" . $bar['hargasatuan'] . "')\"";
			} else {
				$set = "style=background-color:#FEE0B9; title=\"Harga barang belum ada.\"";
			}
			$tab .= "<tr class=rowcontent " . $set . ">";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=center>" . $bar['kodebarang'] . "</td>";
			$tab .= "<td>" . $nm['namabarang'] . "</td>";
			$tab .= "<td align=center>" . $nm['satuan'] . "</td>";
			$tab .= "<td align=right>" . @number_format($bar['hargasatuan']) . "</td>";
			$tab .= "</tr>";
		}

		echo $tab;
		break;
	case 'simpanvhc':
		cekheader($param);

		try {
			$owlPDO->beginTransaction();

			if ($param['kdbudget'] == '') {
				throw new PDOException("Kode anggaran wajib diisi.");
			}
			if ($param['kodevhc'] == '') {
				throw new PDOException("Kode kendaraan wajib diisi.");
			}
			if ($param['jumlah'] == '' or $param['jumlah'] == '0') {
				throw new PDOException("Jumlah wajib diisi.");
			}
			if ($param['rupiah'] == '' or $param['rupiah'] == '0') {
				throw new PDOException("Rupiah setahun wajib diisi.");
			}

			#permintaan don bosco arus kas di vra di kosongkan
			$param['aruskas'] = "";

			$param['jumlah'] = round($param['jumlah'], 5);
			$param['totalvolume'] = round($param['totalvolume'], 5);

			if ($param['update'] == 'update') {
				$str = "select * from " . $dbname . ".bgt_vhc_jam where tahunbudget='" . $param['tahun'] . "' and kodevhc='" . $param['kodevhc'] . "' and unitalokasi='" . $param['kodeorg'] . "'";
				$res = fetchdata($str);
				$tersedia = $res[0]['jumlahjam'];

				$str = "select sum(jumlah) as jumlah from " . $dbname . ".bgt_budget where tahunbudget='" . $param['tahun'] . "' and kodevhc='" . $param['kodevhc'] . "' and tipebudget<>'TRK' and left(kodeorg,4)='" . $param['kodeorg'] . "' and kunci !='" . $param['index'] . "' group by left(kodeorg,4)";
				$res = fetchdata($str);
				$teralokasi = $res[0]['jumlah'];

				$sisa = $tersedia - $teralokasi;

				if (round($param['jumlah'], 5) > round($sisa, 5)) { // kalo ga diginiin, sisa 248, diinput 248 dibilang over?
					throw new PDOException("Total HM/KM Kendaraan " . $param['kodevhc'] . " :\nTersedia = " . number_format($tersedia, 2) . "\nSudah teralokasi = " . number_format($teralokasi, 2) . "\nSisa = " . number_format($sisa, 2) . "");
				}


				$data = array(
					'tahunbudget' => $param['tahun'],
					'kodeorg'    => $param['blok'],
					'tipebudget' => $tipebudget,
					'kodebudget' => $param['kdbudget'],
					'kegiatan'   => $param['kegiatan'],
					'noakun'     => $param['noakun'],
					'volume'     => $param['totalvolume'],
					'satuanv'    => $param['satuanv'],
					'rupiah'     => $param['rupiah'],
					'rotasi'     => $param['rotasi'],
					'updateby'   => $_SESSION['standard']['userid'],
					'jumlah'     => $param['jumlah'],
					'satuanj'    => $param['satuan'],
					'aruskas'    => $param['aruskas']
				);

				$where = "kunci='" . $param['index'] . "'";
				$query = updateQuery($dbname, 'bgt_budget', $data, $where); #exit("error".$query);
				$owlPDO->exec($query);
			} else {
				$wh = "";
				if ($param['blok'] != '') {
					$whr = "and `kodeorg` like '" . $param['blok'] . "%'";
					$wh .= "and `kodeblok` like '" . $param['blok'] . "%'";
				} else {
					$whr = "and `kodeorg` like '" . $param['divisi'] . "%'";
					$wh .= "and `kodeblok` like '" . $param['divisi'] . "%'";
				}
				if ($param['tt'] != '') {
					$wh .= " and thntnm='" . $param['tt'] . "'";
				}
				$whr .= " and kodevhc='" . $param['kodevhc'] . "'";

				$str = "select * from " . $dbname . ".bgt_budget where `tahunbudget` = '" . $param['tahun'] . "' and `tipebudget` = '" . $tipebudget . "' " . $whr . " and `kodebudget` = '" . $param['kdbudget'] . "' and `kegiatan` = '" . $param['kegiatan'] . "' and kodeorg in (select kodeblok from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "' " . $wh . ")";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$str = "delete from " . $dbname . ".bgt_distribusi where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);

					$str = "delete from " . $dbname . ".bgt_budget where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);
				}

				$str = "select * from " . $dbname . ".bgt_vhc_jam where tahunbudget='" . $param['tahun'] . "' and kodevhc='" . $param['kodevhc'] . "' and unitalokasi='" . $param['kodeorg'] . "'";
				$res = fetchdata($str);
				$tersedia = $res[0]['jumlahjam'];

				$str = "select sum(jumlah) as jumlah from " . $dbname . ".bgt_budget where tahunbudget='" . $param['tahun'] . "' and kodevhc='" . $param['kodevhc'] . "' and tipebudget<>'TRK' and left(kodeorg,4)='" . $param['kodeorg'] . "' group by left(kodeorg,4)";
				$res = fetchdata($str);
				$teralokasi = $res[0]['jumlah'];

				$sisa = $tersedia - $teralokasi;

				if (round($param['jumlah'], 5) > round($sisa, 5)) { // kalo ga diginiin, sisa 248, diinput 248 dibilang over?
					throw new PDOException("Total HM/KM Kendaraan " . $param['kodevhc'] . " :\nTersedia = " . number_format($tersedia, 2) . "\nSudah teralokasi = " . number_format($teralokasi, 2) . "\nSisa = " . number_format($sisa, 2) . "");
				}


				$luasttl = 0;
				$str = "select sum(hathnini) as luasttl from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "' " . $wh . "";
				$res = fetchdata($str)[0];
				$luasttl = $res['luasttl'];

				$str = "select * from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "'  " . $wh . "";
				$res = fetchdata($str);
				$jlh = count($res);
				if ($jlh > 0) {
					$no = 0;
					$tvol = $tjlh = $trp = 0;
					foreach ($res as $bar) {
						$no++;
						if ($no < $jlh) {
							$volume = round(($bar['hathnini'] / $luasttl * $param['totalvolume']), 5);
							$jumlah = round(($bar['hathnini'] / $luasttl * $param['jumlah']), 5);
							$totalrp = round(($bar['hathnini'] / $luasttl * $param['rupiah']), 0);

							$tvol += $volume;
							$tjlh += $jumlah;
							$trp += $totalrp;
						} else {
							$volume = $param['totalvolume'] - $tvol;
							$jumlah = $param['jumlah'] - $tjlh;
							$totalrp = $param['rupiah'] - $trp;
						}

						$data = array(
							'tahunbudget' => $param['tahun'],
							'kodeorg'    => $bar['kodeblok'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'kegiatan'   => $param['kegiatan'],
							'noakun'     => $param['noakun'],
							'volume'     => $volume,
							'satuanv'    => $param['satuanv'],
							'rupiah'     => $totalrp,
							'rotasi'     => $param['rotasi'],
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'satuanj'    => $param['satuan'],
							'aruskas'    => $param['aruskas'],
							'keterangan' => $param['keterangan'],
							'kodevhc'    => $param['kodevhc']
						);

						$cols = array();
						foreach ($data as $key => $row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname, 'bgt_budget', $data, $cols);
						$owlPDO->exec($query);
					}
				}
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'simpankont':
		cekheader($param);

		try {
			$owlPDO->beginTransaction();

			if ($param['kdbudget'] == '') {
				throw new PDOException("Kode anggaran wajib diisi.");
			}
			if ($param['jumlah'] == '' or $param['jumlah'] == '0') {
				throw new PDOException("Jumlah wajib diisi.");
			}
			if ($param['rupiah'] == '' or $param['rupiah'] == '0') {
				throw new PDOException("Rupiah setahun wajib diisi.");
			}

			$param['jumlah'] = round($param['jumlah'], 5);
			$param['totalvolume'] = round($param['totalvolume'], 5);

			if ($param['update'] == 'update') {
				$data = array(
					'tahunbudget' => $param['tahun'],
					'kodeorg'    => $param['blok'],
					'tipebudget' => $tipebudget,
					'kodebudget' => $param['kdbudget'],
					'kegiatan'   => $param['kegiatan'],
					'noakun'     => $param['noakun'],
					'volume'     => $param['totalvolume'],
					'satuanv'    => $param['satuanv'],
					'rupiah'     => $param['rupiah'],
					'rotasi'     => $param['rotasi'],
					'updateby'   => $_SESSION['standard']['userid'],
					'jumlah'     => $param['jumlah'],
					'satuanj'    => $param['satuan'],
					'aruskas'    => $param['aruskas']
				);

				$where = "kunci='" . $param['index'] . "'";
				$query = updateQuery($dbname, 'bgt_budget', $data, $where); #exit("error".$query);
				$owlPDO->exec($query);
			} else {
				$wh = "";
				if ($param['blok'] != '') {
					$whr = "and `kodeorg` like '" . $param['blok'] . "%'";
					$wh .= "and `kodeblok` like '" . $param['blok'] . "%'";
				} else {
					$whr = "and `kodeorg` like '" . $param['divisi'] . "%'";
					$wh .= "and `kodeblok` like '" . $param['divisi'] . "%'";
				}
				if ($param['tt'] != '') {
					$wh .= " and thntnm='" . $param['tt'] . "'";
				}

				$str = "select * from " . $dbname . ".bgt_budget where `tahunbudget` = '" . $param['tahun'] . "' and `tipebudget` = '" . $tipebudget . "' " . $whr . " and `kodebudget` = '" . $param['kdbudget'] . "' and `kegiatan` = '" . $param['kegiatan'] . "' and kodeorg in (select kodeblok from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "' " . $wh . ")";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$str = "delete from " . $dbname . ".bgt_distribusi where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);

					$str = "delete from " . $dbname . ".bgt_budget where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);
				}

				$luasttl = 0;
				$str = "select sum(hathnini) as luasttl from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "' " . $wh . "";
				$res = fetchdata($str)[0];
				$luasttl = $res['luasttl'];

				$str = "select * from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "'  " . $wh . "";
				$res = fetchdata($str);
				$jlh = count($res);
				if ($jlh > 0) {
					$no = 0;
					$tvol = $tjlh = $trp = 0;
					foreach ($res as $bar) {
						$no++;
						if ($no < $jlh) {
							$volume = round(($bar['hathnini'] / $luasttl * $param['totalvolume']), 5);
							$jumlah = round(($bar['hathnini'] / $luasttl * $param['jumlah']), 5);
							$totalrp = round(($bar['hathnini'] / $luasttl * $param['rupiah']), 0);

							$tvol += $volume;
							$tjlh += $jumlah;
							$trp += $totalrp;
						} else {
							$volume = $param['totalvolume'] - $tvol;
							$jumlah = $param['jumlah'] - $tjlh;
							$totalrp = $param['rupiah'] - $trp;
						}

						$data = array(
							'tahunbudget' => $param['tahun'],
							'kodeorg'    => $bar['kodeblok'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'kegiatan'   => $param['kegiatan'],
							'noakun'     => $param['noakun'],
							'volume'     => $volume,
							'satuanv'    => $param['satuanv'],
							'rupiah'     => $totalrp,
							'rotasi'     => $param['rotasi'],
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'satuanj'    => $param['satuan'],
							'keterangan' => $param['keterangan'],
							'aruskas'    => $param['aruskas']
						);

						$cols = array();
						foreach ($data as $key => $row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname, 'bgt_budget', $data, $cols); #exit("error".$query);
						$owlPDO->exec($query);
					}
				}
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'simpanalat':
		cekheader($param);

		try {
			$owlPDO->beginTransaction();

			if ($param['kdbudget'] == '') {
				throw new PDOException("Kode anggaran wajib diisi.");
			}
			if ($param['kodebarang'] == '') {
				throw new PDOException("Kode barang wajib diisi.");
			}
			if ($param['norma'] == '') {
				throw new PDOException("Norma wajib diisi.");
			}
			if ($param['jumlah'] == '' or $param['jumlah'] == '0') {
				throw new PDOException("Jumlah wajib diisi.");
			}
			if ($param['rupiah'] == '' or $param['rupiah'] == '0') {
				throw new PDOException("Rupiah setahun wajib diisi.");
			}

			$param['jumlah'] = round($param['jumlah'], 5);
			$param['totalvolume'] = round($param['totalvolume'], 5);

			$str = "select distinct regional from " . $dbname . ".bgt_regional_assignment where kodeunit='" . substr($param['kodeorg'], 0, 4) . "' ";
			$res = fetchdata($str);
			$region = $res[0]['regional'];

			if ($param['update'] == 'update') {
				$data = array(
					'tahunbudget' => $param['tahun'],
					'kodeorg'    => $param['blok'],
					'tipebudget' => $tipebudget,
					'kodebudget' => $param['kdbudget'],
					'kegiatan'   => $param['kegiatan'],
					'noakun'     => $param['noakun'],
					'volume'     => $param['totalvolume'],
					'satuanv'    => $param['satuanv'],
					'rupiah'     => $param['rupiah'],
					'rotasi'     => $param['rotasi'],
					'updateby'   => $_SESSION['standard']['userid'],
					'jumlah'     => $param['jumlah'],
					'satuanj'    => $param['satuan'],
					'aruskas'    => $param['aruskas'],
					'kodebarang' => $param['kodebarang'],
					'regional'   => $region
				);

				$where = "kunci='" . $param['index'] . "'";
				$query = updateQuery($dbname, 'bgt_budget', $data, $where); #exit("error".$query);
				$owlPDO->exec($query);
			} else {
				$wh = "";
				if ($param['blok'] != '') {
					$whr = "and `kodeorg` like '" . $param['blok'] . "%'";
					$wh .= "and `kodeblok` like '" . $param['blok'] . "%'";
				} else {
					$whr = "and `kodeorg` like '" . $param['divisi'] . "%'";
					$wh .= "and `kodeblok` like '" . $param['divisi'] . "%'";
				}
				if ($param['tt'] != '') {
					$wh .= " and thntnm='" . $param['tt'] . "'";
				}

				$str = "select * from " . $dbname . ".bgt_budget where `tahunbudget` = '" . $param['tahun'] . "' and `tipebudget` = '" . $tipebudget . "' " . $whr . " and `kodebudget` = '" . $param['kdbudget'] . "' and `kodebarang` = '" . $param['kodebarang'] . "' and `kegiatan` = '" . $param['kegiatan'] . "' and kodeorg in (select kodeblok from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "' " . $wh . ")";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$str = "delete from " . $dbname . ".bgt_distribusi where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);

					$str = "delete from " . $dbname . ".bgt_budget where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);
				}

				$luasttl = 0;
				$str = "select sum(hathnini) as luasttl from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "'  " . $wh . "";
				$res = fetchdata($str)[0];
				$luasttl = $res['luasttl'];

				$str = "select * from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "'  " . $wh . "";
				$res = fetchdata($str);
				$jlh = count($res);
				if ($jlh > 0) {
					$no = 0;
					$tvol = $tjlh = $trp = 0;
					foreach ($res as $bar) {
						$no++;
						if ($no < $jlh) {
							$volume = round(($bar['hathnini'] / $luasttl * $param['totalvolume']), 5);
							$jumlah = round(($bar['hathnini'] / $luasttl * $param['jumlah']), 5);
							$totalrp = round(($bar['hathnini'] / $luasttl * $param['rupiah']), 0);

							$tvol += $volume;
							$tjlh += $jumlah;
							$trp += $totalrp;
						} else {
							$volume = $param['totalvolume'] - $tvol;
							$jumlah = $param['jumlah'] - $tjlh;
							$totalrp = $param['rupiah'] - $trp;
						}

						$data = array(
							'tahunbudget' => $param['tahun'],
							'kodeorg'    => $bar['kodeblok'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'kegiatan'   => $param['kegiatan'],
							'noakun'     => $param['noakun'],
							'volume'     => $volume,
							'satuanv'    => $param['satuanv'],
							'rupiah'     => $totalrp,
							'rotasi'     => $param['rotasi'],
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'satuanj'    => $param['satuan'],
							'aruskas'    => $param['aruskas'],
							'kodebarang' => $param['kodebarang'],
							'keterangan' => $param['keterangan'],
							'regional'   => $region

						);

						$cols = array();
						foreach ($data as $key => $row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname, 'bgt_budget', $data, $cols);
						$owlPDO->exec($query);
					}
				}
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'simpanmat':
		cekheader($param);

		try {
			$owlPDO->beginTransaction();

			if ($param['kdbudget'] == '') {
				throw new PDOException("Kode anggaran wajib diisi.");
			}
			if ($param['kodebarang'] == '') {
				throw new PDOException("Kode barang wajib diisi.");
			}
			if ($param['norma'] == '') {
				throw new PDOException("Norma wajib diisi.");
			}
			if ($param['jumlah'] == '' or $param['jumlah'] == '0') {
				throw new PDOException("Jumlah wajib diisi.");
			}
			if ($param['rupiah'] == '' or $param['rupiah'] == '0') {
				throw new PDOException("Rupiah setahun wajib diisi.");
			}

			$param['jumlah'] = round($param['jumlah'], 5);
			$param['totalvolume'] = round($param['totalvolume'], 5);

			$str = "select distinct regional from " . $dbname . ".bgt_regional_assignment where kodeunit='" . substr($param['kodeorg'], 0, 4) . "' ";
			$res = fetchdata($str);
			$region = $res[0]['regional'];

			if ($param['update'] == 'update') {
				$data = array(
					'tahunbudget' => $param['tahun'],
					'kodeorg'    => $param['blok'],
					'tipebudget' => $tipebudget,
					'kodebudget' => $param['kdbudget'],
					'kegiatan'   => $param['kegiatan'],
					'noakun'     => $param['noakun'],
					'volume'     => $param['totalvolume'],
					'satuanv'    => $param['satuanv'],
					'rupiah'     => $param['rupiah'],
					'rotasi'     => $param['rotasi'],
					'updateby'   => $_SESSION['standard']['userid'],
					'jumlah'     => $param['jumlah'],
					'satuanj'    => $param['satuan'],
					'aruskas'    => $param['aruskas'],
					'kodebarang' => $param['kodebarang'],
					'regional'   => $region
				);

				$where = "kunci='" . $param['index'] . "'";
				$query = updateQuery($dbname, 'bgt_budget', $data, $where); #exit("error".$query);
				$owlPDO->exec($query);
			} else {
				$wh = "";
				if ($param['blok'] != '') {
					$whr = "and `kodeorg` like '" . $param['blok'] . "%'";
					$wh .= "and `kodeblok` like '" . $param['blok'] . "%'";
				} else {
					$whr = "and `kodeorg` like '" . $param['divisi'] . "%'";
					$wh .= "and `kodeblok` like '" . $param['divisi'] . "%'";
				}
				if ($param['tt'] != '') {
					$wh .= " and thntnm='" . $param['tt'] . "'";
				}

				$str = "select * from " . $dbname . ".bgt_budget where `tahunbudget` = '" . $param['tahun'] . "' and `tipebudget` = '" . $tipebudget . "' " . $whr . " and `kodebudget` = '" . $param['kdbudget'] . "' and `kodebarang` = '" . $param['kodebarang'] . "' and `kegiatan` = '" . $param['kegiatan'] . "' and kodeorg in (select kodeblok from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "' " . $wh . ")";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$str = "delete from " . $dbname . ".bgt_distribusi where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);

					$str = "delete from " . $dbname . ".bgt_budget where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);
				}

				$luasttl = 0;
				$str = "select sum(hathnini) as luasttl from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "'  " . $wh . "";
				$res = fetchdata($str)[0];
				$luasttl = $res['luasttl'];

				$str = "select * from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "' " . $wh . "";
				$res = fetchdata($str);
				$jlh = count($res);
				if ($jlh > 0) {
					$no = 0;
					$tvol = $tjlh = $trp = 0;
					foreach ($res as $bar) {
						$no++;
						if ($no < $jlh) {
							$volume = round(($bar['hathnini'] / $luasttl * $param['totalvolume']), 5);
							$jumlah = round(($bar['hathnini'] / $luasttl * $param['jumlah']), 5);
							$totalrp = round(($bar['hathnini'] / $luasttl * $param['rupiah']), 0);

							$tvol += $volume;
							$tjlh += $jumlah;
							$trp += $totalrp;
						} else {
							$volume = $param['totalvolume'] - $tvol;
							$jumlah = $param['jumlah'] - $tjlh;
							$totalrp = $param['rupiah'] - $trp;
						}

						$data = array(
							'tahunbudget' => $param['tahun'],
							'kodeorg'    => $bar['kodeblok'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'kegiatan'   => $param['kegiatan'],
							'noakun'     => $param['noakun'],
							'volume'     => $volume,
							'satuanv'    => $param['satuanv'],
							'rupiah'     => $totalrp,
							'rotasi'     => $param['rotasi'],
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'satuanj'    => $param['satuan'],
							'aruskas'    => $param['aruskas'],
							'kodebarang' => $param['kodebarang'],
							'keterangan' => $param['keterangan'],
							'regional'   => $region

						);

						$cols = array();
						foreach ($data as $key => $row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname, 'bgt_budget', $data, $cols);
						$owlPDO->exec($query);
					}
				}
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;

	case 'simpansdm':
		cekheader($param);

		try {
			$owlPDO->beginTransaction();

			if ($param['kdbudget'] == '') {
				throw new PDOException("Kode anggaran wajib diisi.");
			}
			if ($param['hke'] == '') {
				throw new PDOException("HKE wajib diisi.");
			}
			if ($param['norma'] == '') {
				throw new PDOException("Norma wajib diisi.");
			}
			if ($param['jhk'] == '') {
				throw new PDOException("Jumlah HK wajib diisi.");
			}
			if ($param['rupiah'] == '' or $param['rupiah'] == '0') {
				throw new PDOException("Rupiah setahun wajib diisi.");
			}

			$param['jumlah'] = round($param['jumlah'], 5);
			$param['totalvolume'] = round($param['totalvolume'], 5);

			if ($param['update'] == 'update') {
				$data = array(
					'tahunbudget' => $param['tahun'],
					'kodeorg'    => $param['blok'],
					'tipebudget' => $tipebudget,
					'kodebudget' => $param['kdbudget'],
					'kegiatan'   => $param['kegiatan'],
					'noakun'     => $param['noakun'],
					'volume'     => $param['totalvolume'],
					'satuanv'    => $param['satuanv'],
					'rupiah'     => $param['rupiah'],
					'rotasi'     => $param['rotasi'],
					'updateby'   => $_SESSION['standard']['userid'],
					'jumlah'     => $param['jhk'],
					'satuanj'    => 'HK',
					'aruskas'    => $param['aruskas']
				);

				$where = "kunci='" . $param['index'] . "'";
				$query = updateQuery($dbname, 'bgt_budget', $data, $where); #exit("error".$query);
				$owlPDO->exec($query);
			} else {
				$wh = "";
				if ($param['blok'] != '') {
					$whr = "and `kodeorg` like '" . $param['blok'] . "%'";
					$wh .= "and `kodeblok` like '" . $param['blok'] . "%'";
				} else {
					$whr = "and `kodeorg` like '" . $param['divisi'] . "%'";
					$wh .= "and `kodeblok` like '" . $param['divisi'] . "%'";
				}
				if ($param['tt'] != '') {
					$wh .= " and thntnm='" . $param['tt'] . "'";
				}

				$str = "select * from " . $dbname . ".bgt_budget where `tahunbudget` = '" . $param['tahun'] . "' and `tipebudget` = '" . $tipebudget . "' " . $whr . " and `kodebudget` = '" . $param['kdbudget'] . "' and `kegiatan` = '" . $param['kegiatan'] . "' and kodeorg in (select kodeblok from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "' " . $wh . ")";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$str = "delete from " . $dbname . ".bgt_distribusi where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);

					$str = "delete from " . $dbname . ".bgt_budget where kunci='" . $bar['kunci'] . "'";
					$owlPDO->exec($str);
				}

				$luasttl = 0;
				$str = "select sum(hathnini) as luasttl from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "'  " . $wh . "";
				$res = fetchdata($str)[0];
				$luasttl = $res['luasttl'];

				$str = "select * from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and statusblok='" . $param['jenis'] . "'  " . $wh . "";
				$res = fetchdata($str);
				$jlh = count($res);
				if ($jlh > 0) {
					$no = 0;
					$tvol = $tjlh = $trp = 0;
					foreach ($res as $bar) {
						$no++;
						if ($no < $jlh) {
							$volume = round(($bar['hathnini'] / $luasttl * $param['totalvolume']), 5);
							$jumlah = round(($bar['hathnini'] / $luasttl * $param['jhk']), 5);
							$totalrp = round(($bar['hathnini'] / $luasttl * $param['rupiah']), 0);

							$tvol += $volume;
							$tjlh += $jumlah;
							$trp += $totalrp;
						} else {
							$volume = $param['totalvolume'] - $tvol;
							$jumlah = $param['jhk'] - $tjlh;
							$totalrp = $param['rupiah'] - $trp;
						}

						$data = array(
							'tahunbudget' => $param['tahun'],
							'kodeorg'    => $bar['kodeblok'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'kegiatan'   => $param['kegiatan'],
							'noakun'     => $param['noakun'],
							'volume'     => $volume,
							'satuanv'    => $param['satuanv'],
							'rupiah'     => $totalrp,
							'rotasi'     => $param['rotasi'],
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'satuanj'    => 'HK',
							'keterangan' => $param['keterangan'],
							'aruskas'    => $param['aruskas']
						);

						$cols = array();
						foreach ($data as $key => $row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname, 'bgt_budget', $data, $cols);
						$owlPDO->exec($query);
					}
				}
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;

	case 'getupah':
		if ($param['kdbudget'] == '') {
			exit("Warning : Kode anggaran wajib diisi.");
		}
		$str = "select jumlah from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg = '" . $param['kodeorg'] . "' and golongan='" . $param['kdbudget'] . "' and closed=1";
		$res = fetchdata($str);
		if (count($res) > 0) {
			if ($res[0]['jumlah'] == '') {
				exit("Warning : Data upah belum ada, silahkan cek kembali");
			} else {
				$totalupah = (floatval($res[0]['jumlah']) * floatval($param['jhk']));
				echo number_format($totalupah);
			}
		} else {
			exit("Error : Budget upah rata - rata belum diinput atau ditutup.");
		}
		break;

	case 'gethargavhc':
		if ($param['kodevhc'] == '') {
			exit("Warning : Kode kendaraan wajib diisi.");
		}
		$str = "select distinct rpperjam from " . $dbname . ".bgt_biaya_ken_per_jam where tahunbudget='" . $param['tahun'] . "' and kodevhc='" . $param['kodevhc'] . "'";
		$res = fetchdata($str);
		if (count($res) > 0) {
			if ($res[0]['rpperjam'] == '') {
				exit("Warning : Data rupiah / jam kendaraan belum ada, silahkan cek kembali");
			} else {
				$rp = $res[0]['rpperjam'];
			}
		} else {
			exit("Error : Budget Kendaraan belum diinput.");
		}

		$jnsvhc = makeOption($dbname, 'vhc_5master', 'kodevhc,jenisvhc', "kodevhc='" . $param['kodevhc'] . "'");
		$kelvhc = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,kelompokvhc', "jenisvhc='" . $jnsvhc[$param['kodevhc']] . "'");

		if ($kelvhc[$jnsvhc[$param['kodevhc']]] == 'KD') {
			$sat = "KM";
		} else {
			$sat = "HM";
		}

		echo $rp . "####" . $sat;
		break;

	case 'simpanheader':
		cekheader($param);

		$str = "select distinct tutup from " . $dbname . ".bgt_budget where 1=1 " . $whr . "";
		$res = fetchdata($str);
		if ($res[0]['tutup'] > 0) {
			exit("Warning : Budget " . $param['tahun'] . " sudah ditutup.");
		}

		$str = "select distinct * from " . $dbname . ".bgt_hk where tahunbudget='" . $param['tahun'] . "' and unit = '" . substr($param['kodeorg'], 0, 4) . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$thrlb    = $bar['hrminggu'] + $bar['hrlibur'] - $bar['hrliburminggu'];
			$thke     = $bar['harisetahun'] - $thrlb;
			$tsim     = $bar['s1s2'] + $bar['h1h2'] + $bar['p1p3'] + $bar['mangkir'];
			$tothke   = $thke - ($bar['jlhcuti'] + $tsim);
			$hkefektip = $tothke;
		}

		$optvhc = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select distinct a.kodetraksi ,a.kodevhc from " . $dbname . ".bgt_biaya_jam_ken_vs_alokasi a left join " . $dbname . ".bgt_vhc_jam b on a.tahunbudget=b.tahunbudget and a.kodevhc=b.kodevhc where a.tahunbudget='" . $param['tahun'] . "' and b.unitalokasi='" . $param['kodeorg'] . "' order by kodevhc asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optnopol = makeOption($dbname, 'vhc_5master', 'kodevhc,nopol', "kodevhc='" . $bar['kodevhc'] . "'");
			$detvhc = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc', "kodevhc='" . $bar['kodevhc'] . "'");
			$nopol = "";
			if ($optnopol[$bar['kodevhc']] != '') {
				$nopol = " - " . $optnopol[$bar['kodevhc']];
			}
			$det = "";
			if ($detvhc[$bar['kodevhc']] != '') {
				$det = " - " . $detvhc[$bar['kodevhc']];
			}

			// $d = $bar['kodetraksi'];
			// if ($d != $n) {
			// 	$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $bar['kodetraksi'] . "'");
			// 	$optvhc .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
			// }

			$optvhc .= "<option value='" . $bar['kodevhc'] . "'>" . $bar['kodevhc'] . "" . $nopol . "" . $det . "</option>";
			// $n = $d;
			// if ($d != $n) {
			// 	$optvhc .= "</optgroup>";
			// }
		}

		$optupah = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select distinct golongan from " . $dbname . ".bgt_upah where kodeorg='" . $param['kodeorg'] . "' and tahunbudget='" . $param['tahun'] . "' and jumlah>0";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optupah .= "<option value='" . $bar['golongan'] . "'>" . strtoupper($nmkode[$bar['golongan']]) . "</option>";
		}

		$str = "select distinct a.noaruskas, a.nama_aruskas from " . $dbname . ".keu_5aruskas a left join " . $dbname . ".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '" . $param['noakun'] . "' order by a.noaruskas asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optaruskas .= "<option value=" . $bar['noaruskas'] . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
		}
		echo $hkefektip . "###" . $optvhc . "###" . $optupah . "###" . $optaruskas;

		break;

	case 'del':
		try {
			$owlPDO->beginTransaction();
			$wh = "";
			$wh .= " and tipebudget = 'ESTATE' and kodebudget!='UMUM' and pta='BGT'";
			$wh .= " and kegiatan = '" . $param['kegiatan'] . "'";
			$wh .= " and kodeorg in (select kodeblok from " . $dbname . ".bgt_blok where `tahunbudget` = '" . $param['tahun'] . "' and `kodeblok` like '" . $param['divisi'] . "%'  and thntnm='" . $param['tt'] . "')";


			$str = "delete from " . $dbname . ".bgt_budget  where tahunbudget='" . $param['tahun'] . "' and kodeorg like '" . $param['divisi'] . "%' " . $wh . "";
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'delrekapblok':
		try {
			$owlPDO->beginTransaction();
			$wh = "";
			$wh .= " and tipebudget = 'ESTATE' and kodebudget!='UMUM' and pta='BGT'";
			$wh .= " and kegiatan = '" . $param['kegiatan'] . "'";

			$str = "delete from " . $dbname . ".bgt_budget  where tahunbudget='" . $param['tahun'] . "' and kodeorg = '" . $param['blok'] . "' " . $wh . "";
			$owlPDO->exec($str);


			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}

		$tt = makeOption($dbname, 'bgt_blok', 'kodeblok,thntnm', "kodeblok='" . $param['blok'] . "' and tahunbudget='" . $param['tahun'] . "'");
		echo $tt[$param['blok']];
		break;
	case 'posting':
		try {
			$owlPDO->beginTransaction();

			$where = " and tipebudget = 'ESTATE' and kodebudget!='UMUM' and pta='BGT'";

			$str = "select * from " . $dbname . ".bgt_budget where 1=1 " . $where . " and tahunbudget='" . $param['tahun'] . "' and kodeorg like '" . $param['divisi'] . "%' and kunci not in (select kunci from " . $dbname . ".bgt_distribusi)";
			$res = fetchdata($str);
			if (count($res) > 0) {
				throw new PDOException("Masih ada data yang belum di sebarkan.");
			}

			$str = "update " . $dbname . ".bgt_budget set tutup='1' where 1=1 " . $where . " and tahunbudget='" . $param['tahun'] . "' and kodeorg like '" . $param['divisi'] . "%'"; #exit("error".$str);
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'unposting':
		try {
			$owlPDO->beginTransaction();

			$where = " and tipebudget = 'ESTATE' and kodebudget!='UMUM' and pta='BGT'";
			$str = "update " . $dbname . ".bgt_budget set tutup='0' where 1=1 " . $where . " and tahunbudget='" . $param['tahun'] . "' and kodeorg like '" . $param['divisi'] . "%'"; #exit("error".$str);
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;

	case 'getblok':
		$blok = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$where = "";
		if ($param['divisi'] != '') {
			$where .= " and kodeblok like '" . $param['divisi'] . "%'";
		}
		if ($param['blok'] != '') {
			$where .= " and kodeblok like '" . $param['blok'] . "%'";
		}
		if ($param['tt'] != '') {
			$where .= " and thntnm = '" . $param['tt'] . "'";
		}

		$luas = 0;
		$str = "select distinct kodeblok, hathnini,statusblok from " . $dbname . ".bgt_blok where 1=1 " . $where . " and tahunbudget='" . $param['tahun'] . "' and closed='1' order by kodeblok asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$blok .= "<option value='" . $bar['kodeblok'] . "'>" . $nmorg[$bar['kodeblok']] . "</option>";
			$luas += $bar['hathnini'];
			$stsblok[$bar['statusblok']] = $bar['statusblok'];
		}

		$stat = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($stsblok as $sts) {
			$nmkel = makeOption($dbname, 'setup_klpkegiatan', 'kodeklp,namakelompok', "kodeklp='" . $sts . "'");
			$stat .= "<option value='" . $sts . "'>" . $nmkel[$sts] . "</option>";
		}

		echo $blok . "####" . $luas . "####" . $stat;
		break;
	case 'getkegiatan':
		$optkeg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$wh = "";
		if ($param['jenis'] == 'TB' or $param['jenis'] == 'TBM') {
			$wh = "and substr(noakun,1,3) in ('126')";
		}
		if ($param['jenis'] == 'TM') {
			$wh = "and substr(noakun,1,3) in ('611','621')";
		}
		if ($param['jenis'] == 'BBT') {
			$wh = "and substr(noakun,1,3) in ('128')";
		}

		$kdkel = array('126' => 'TBM', '128' => 'BBT', '611' => 'PNN', '621' => 'TM');
		$str = "select * from " . $dbname . ".setup_kegiatan where status='1' and namakegiatan not like '%NON AKTIF%' and namakegiatan not like '%TIDAK DIPAKAI%' " . $wh . " order by  noakun asc,  kodekegiatan asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			// $d = $kdkel[substr($bar['noakun'], 0, 3)];
			// if ($d != $n) {
			// 	$nmkel = makeOption($dbname, 'setup_klpkegiatan', 'kodeklp,namakelompok', "kodeklp='" . $d . "'");
			// 	$optkeg .= "<optgroup label='" . $d . " - " . $nmkel[$d] . "'>";
			// }
			$optkeg .= "<option value='" . $bar['kodekegiatan'] . "'>" . $bar['kodekegiatan'] . " - " . $bar['namakegiatan'] . "</option>";
			// $n = $d;
			// if ($d != $n) {
			// 	$optkeg .= "</optgroup>";
			// }
		}

		$where = "";
		if ($param['divisi'] != '') {
			$where .= " and kodeblok like '" . $param['divisi'] . "%'";
		}
		if ($param['blok'] != '') {
			$where .= " and kodeblok like '" . $param['blok'] . "%'";
		}
		if ($param['tt'] != '') {
			$where .= " and thntnm = '" . $param['tt'] . "'";
		}
		$luas = 0;
		$str = "select hathnini from " . $dbname . ".bgt_blok where 1=1 " . $where . " and tahunbudget='" . $param['tahun'] . "' and closed='1' and statusblok='" . $param['jenis'] . "' order by kodeblok asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$luas += $bar['hathnini'];
		}

		echo $optkeg . "####" . $luas;
		break;
	case 'getnoakun':
		$akun  = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,noakun', "kodekegiatan='" . $param['kegiatan'] . "'");
		$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $akun[$param['kegiatan']] . "'");
		$satuan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan', "kodekegiatan='" . $param['kegiatan'] . "'");

		$pres = 0;
		if (strtolower($satuan[$param['kegiatan']]) == 'kg') {
			$where = "";
			$where .= " and kodeunit = '" . $param['kodeorg'] . "'";
			$where .= " and tahunbudget = '" . $param['tahun'] . "'";

			if ($param['divisi'] != '') {
				$where .= " and kodeblok  like '" . $param['divisi'] . "%'";
			}

			if ($param['tt'] != '') {
				$where .= " and tahuntanam = '" . $param['tt'] . "'";
			}

			$str = "select sum(totalkg) as totalkg from " . $dbname . ".bgt_produksi_kebun where 1=1 " . $where . "";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$pres += $bar['totalkg'];
			}
			// exit("error".$str);
		} elseif (strtolower($satuan[$param['kegiatan']]) == 'pkk' or strtolower($satuan[$param['kegiatan']]) == 'pokok') {
			$where = "";
			if ($param['divisi'] != '') {
				$where .= " and kodeblok like '" . $param['divisi'] . "%'";
			}
			if ($param['blok'] != '') {
				$where .= " and kodeblok like '" . $param['blok'] . "%'";
			}
			if ($param['tt'] != '') {
				$where .= " and thntnm = '" . $param['tt'] . "'";
			}
			$str = "select pokokthnini from " . $dbname . ".bgt_blok where 1=1 " . $where . " and tahunbudget='" . $param['tahun'] . "' and closed='1' and statusblok='" . $param['jenis'] . "' order by kodeblok asc";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$pres += $bar['pokokthnini'];
			}
		}





		echo $akun[$param['kegiatan']] . "####" . $satuan[$param['kegiatan']] . "####" . $pres;
		break;
	case 'getttblok':
		if ($param['tahun'] == '' or strlen($param['tahun']) < 4) {
			exit("Warning : Tahun budget wajib diisi.");
		}
		$tt = $blok = "<option value=''>" . $param['bahasa'] . "</option>";
		$str = "select distinct thntnm from " . $dbname . ".bgt_blok where kodeblok like '" . $param['kodeorg'] . "%' order by thntnm asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tt .= "<option value='" . $bar['thntnm'] . "'>" . $bar['thntnm'] . "</option>";
		}

		$stsblok = array();
		$str = "select distinct kodeblok, statusblok from " . $dbname . ".bgt_blok where kodeblok like '" . $param['kodeorg'] . "%' and tahunbudget='" . $param['tahun'] . "' and closed='1' order by kodeblok asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$blok .= "<option value='" . $bar['kodeblok'] . "'>" . $nmorg[$bar['kodeblok']] . "</option>";
			$stsblok[$bar['statusblok']] = $bar['statusblok'];
		}
		$stat = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($stsblok as $sts) {
			$nmkel = makeOption($dbname, 'setup_klpkegiatan', 'kodeklp,namakelompok', "kodeklp='" . $sts . "'");
			$stat .= "<option value='" . $sts . "'>" . $nmkel[$sts] . "</option>";
		}


		if (count($res) == 0) {
			exit("Warning : Master blok belum diinput atau diposting.");
		}

		$wh = "";
		if (count($stsblok) > 0) {
			if (in_array('TM', $stsblok)) {
				$wh .= " and kelompok in ('" . implode("','", $stsblok) . "','PNN')";
			} else {
				$wh .= " and kelompok in ('" . implode("','", $stsblok) . "')";
			}
		}

		$kdkel = array('126' => 'TBM', '128' => 'BBT', '611' => 'PNN', '621' => 'TM');

		$optkeg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select * from " . $dbname . ".setup_kegiatan where status='1' and namakegiatan not like '%NON AKTIF%' and namakegiatan not like '%TIDAK DIPAKAI%' and substr(noakun,1,3) in ('126','128','611','621') " . $wh . " order by kodekegiatan asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			// $d = $kdkel[substr($bar['noakun'], 0, 3)];
			// if ($d != $n) {
			// 	$nmkel = makeOption($dbname, 'setup_klpkegiatan', 'kodeklp,namakelompok', "kodeklp='" . $d . "'");
			// 	$optkeg .= "<optgroup label='" . $d . " - " . $nmkel[$d] . "'>";
			// }
			$optkeg .= "<option value='" . $bar['kodekegiatan'] . "'>" . $bar['kodekegiatan'] . " - " . $bar['namakegiatan'] . "</option>";
			// $n = $d;
			// if ($d != $n) {
			// 	$optkeg .= "</optgroup>";
			// }
			$kodeakun[$bar['noakun']] = $bar['noakun'];
		}

		$optakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($kodeakun as $akun) {
			// $d = $kdkel[substr($akun, 0, 3)];
			// if ($d != $n) {
			// 	$nmkel = makeOption($dbname, 'setup_klpkegiatan', 'kodeklp,namakelompok', "kodeklp='" . $d . "'");
			// 	$optakun .= "<optgroup label='" . $d . " - " . $nmkel[$d] . "'>";
			// }
			$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $akun . "'");
			$optakun .= "<option value='" . $akun . "'>" . $akun . " - " . $nmakun[$akun] . "</option>";
			// $n = $d;
			// if ($d != $n) {
			// 	$optakun .= "</optgroup>";
			// }
		}

		echo $tt . "####" . $blok . "####" . $optkeg . "####" . $optakun . "####" . $stat;
		break;
	case 'getdivttblok':
		if ($param['tahun'] == '' or strlen($param['tahun']) < 4) {
			exit("Warning : Tahun budget wajib diisi.");
		}

		$div = $tt = $blok = "<option value=''>" . $param['bahasa'] . "</option>";
		$div .= makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "tipe in ('AFDELING','BIBITAN') and induk ='" . $param['kodeorg'] . "' ", '2', '0', true);

		$whr = '';

		if ($param['kodeorg'] != '') {
			$whr = "and induk='" . $param['kodeorg'] . "'";
		}

		$str = "select distinct tahuntanam from " . $dbname . ".setup_blok a left join " . $dbname . ".organisasi b on left(a.kodeorg,6)=b.kodeorganisasi where b.tipe in ('AFDELING','BIBITAN') and tahuntanam!=0 " . $whr . " order by tahuntanam asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tt .= "<option value='" . $bar['tahuntanam'] . "'>" . $bar['tahuntanam'] . "</option>";
		}

		$str = "select distinct kodeblok from " . $dbname . ".bgt_blok where kodeblok like '" . $param['kodeorg'] . "%' and tahunbudget='" . $param['tahun'] . "' and closed='1' order by kodeblok asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$blok .= "<option value='" . $bar['kodeblok'] . "'>" . $nmorg[$bar['kodeblok']] . "</option>";
		}

		echo $div . "####" . $tt . "####" . $blok;
		break;
	case 'loaddataprd':
		$where = "";
		if ($param['tahun'] != '') {
			$where .= " and tahunbudget = '" . $param['tahun'] . "'";
		}
		if ($param['kodeorg'] != '') {
			$where .= " and kodeunit = '" . $param['kodeorg'] . "'";
		}
		if ($param['divisi'] != '') {
			$where .= " and kodeblok like '" . $param['divisi'] . "%'";
		}
		if ($param['tt'] != '') {
			$where .= " and tahuntanam = '" . $param['tt'] . "'";
		}
		if ($param['sebaran'] != '') {
			if ($param['sebaran'] == 0) {
				$where .= " and (kg01+kg02+kg03+kg04+kg05+kg06+kg07+kg08+kg09+kg10+kg11+kg12)=0";
			} else {
				$where .= " and (kg01+kg02+kg03+kg04+kg05+kg06+kg07+kg08+kg09+kg10+kg11+kg12)>0";
			}
		}
		if ($param['ip'] != '') {
			$where .= " and intiplasma = '" . $param['ip'] . "'";
		}
		$bulan = range(1, 12);

		$tab = "";

		$tab .= "<table class='sortable' cellspacing=1 cellpadding=5 border=0>
			<thead>
			<tr class=rowheader>
				<th align=center width=30px>No.</th>
				<th align=center style='width:70px'>" . $_SESSION['lang']['budgetyear'] . "</th>
				<th align=center>" . $_SESSION['lang']['divisi'] . "</th>
				<th align=centers style='width:70px'>" . $_SESSION['lang']['tahuntanam'] . "</th>
				<th align=center>" . $_SESSION['lang']['blok'] . "</th>
				<th align=center>" . $_SESSION['lang']['luas'] . "</th>
				<th align=center>" . $_SESSION['lang']['pokok'] . "</th>
				<th align=center>" . $_SESSION['lang']['sph'] . "</th>
				<th align=center>Ton / Ha</th>
				<th align=center>Jjg / Pkk</th>
				<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['jjg'] . "</th>
				<th align=center>BJR</th>
				<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['kg'] . "</th>
				<th align=center>" . $_SESSION['lang']['jenis'] . "</th>";
		foreach ($bulan as $bln) {
			$tab .= "<th align=center>" . numToMonth($bln, 'E', 'short') . "</th>";
		}
		$tab .= "
			</tr>
		</thead><tbody>";

		$no     = 0;
		$colspan = 28;

		$rowspan = "rowspan=2";
		$str = "select * from " . $dbname . ".bgt_produksi_kebun where substr(kodeunit,1,4) in (" . getOrgDetail(2) . ") " . $where . " order by tahunbudget desc,kodeunit asc,tahuntanam asc";
		$res = fetchdata($str);
		if (count($res) > 0) {
			foreach ($res as $key => $val) {
				$no++;

				$luas = makeOption($dbname, 'bgt_blok', 'kodeblok,hathnini', "kodeblok='" . $val['kodeblok'] . "' and tahunbudget='" . $val['tahunbudget'] . "'");
				$pokok = makeOption($dbname, 'bgt_blok', 'kodeblok,pokokthnini', "kodeblok='" . $val['kodeblok'] . "' and tahunbudget='" . $val['tahunbudget'] . "'");

				$tluas += $luas[$val['kodeblok']];
				$tpokok += $pokok[$val['kodeblok']];
				$tttljjg += $val['totaljjg'];
				$tttlkg += $val['totalkg'];

				$tab .= "<tr class='rowcontent'>";
				$tab .= "<td " . $rowspan . " style='text-align:center;vertical-align:top;'>" . $no . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:center;vertical-align:top;'>" . $val['tahunbudget'] . "</td>";
				#$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".$nmorg[$val['kodeunit']]."</td>";
				#$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".$val['kodeunit']."</td>";
				#$tab.="<td ".$rowspan." style='text-align:left;vertical-align:top;'>".$nmorg[substr($val['kodeblok'],0,6)]."</td>";
				$tab .= "<td " . $rowspan . " style='text-align:left;vertical-align:top;'>" . substr($val['kodeblok'], 0, 6) . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:center;vertical-align:top;'>" . $val['tahuntanam'] . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:center;vertical-align:top;'>" . $nmorg[$val['kodeblok']] . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:right;vertical-align:top;'>" . hidezerodecimal($luas[$val['kodeblok']], 2) . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:right;vertical-align:top;'>" . hidezerodecimal($pokok[$val['kodeblok']], 0) . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:right;vertical-align:top;'>" . hidezerodecimal($pokok[$val['kodeblok']] / $luas[$val['kodeblok']], 2) . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:right;vertical-align:top;'>" . hidezerodecimal(($val['totalkg'] / $luas[$val['kodeblok']]) / 1000, 2) . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:right;vertical-align:top;'>" . hidezerodecimal(($val['totaljjg'] / $pokok[$val['kodeblok']]), 2) . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:right;vertical-align:top;'>" . hidezerodecimal($val['totaljjg'], 0) . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:right;vertical-align:top;'>" . hidezerodecimal($val['totalkg'] / $val['totaljjg'], 2) . "</td>";
				$tab .= "<td " . $rowspan . " style='text-align:right;vertical-align:top;'>" . hidezerodecimal($val['totalkg'], 0) . "</td>";

				$tab .= "<td style='text-align:center'>Jjg</td>";
				foreach ($bulan as $bln) {
					$tab .= "<td style='text-align:right'>" . hidezerodecimal($val['jjg' . addZero($bln, 2)], 0) . "</td>";
					$tjjg[$bln] += $val['jjg' . addZero($bln, 2)];
					$tkg[$bln] += $val['kg' . addZero($bln, 2)];
				}

				$tab .= "</tr>";
				$tab .= "<tr class='rowcontent'>";
				$tab .= "<td style='text-align:center'>Kg</td>";
				foreach ($bulan as $bln) {
					$tab .= "<td style='text-align:right'>" . hidezerodecimal($val['kg' . addZero($bln, 2)], 0) . "</td>";
				}

				$tab .= "</tr>";
			}

			#== TOTAL ==
			$c = "vertical-align:top;background-color:#AED6F1;";
			$tab .= "<tr class='rowcontent'>";
			$tab .= "<td " . $rowspan . " style='text-align:center;" . $c . "' colspan=5>TOTAL</td>";
			$tab .= "<td " . $rowspan . " style='text-align:right;" . $c . "'>" . hidezerodecimal($tluas, 2) . "</td>";
			$tab .= "<td " . $rowspan . " style='text-align:right;" . $c . "'>" . hidezerodecimal($tpokok, 0) . "</td>";
			$tab .= "<td " . $rowspan . " style='text-align:right;" . $c . "'>" . @hidezerodecimal($tpokok / $tluas, 2) . "</td>";
			$tab .= "<td " . $rowspan . " style='text-align:right;" . $c . "'>" . @hidezerodecimal(($tttlkg / $tluas) / 1000, 2) . "</td>";
			$tab .= "<td " . $rowspan . " style='text-align:right;" . $c . "'>" . @hidezerodecimal(($tttljjg / $tpokok), 2) . "</td>";
			$tab .= "<td " . $rowspan . " style='text-align:right;" . $c . "'>" . @hidezerodecimal($tttljjg, 0) . "</td>";
			$tab .= "<td " . $rowspan . " style='text-align:right;" . $c . "'>" . @hidezerodecimal($tttlkg / $tttljjg, 2) . "</td>";
			$tab .= "<td " . $rowspan . " style='text-align:right;" . $c . "'>" . @hidezerodecimal($tttlkg, 0) . "</td>";
			$tab .= "<td style='text-align:center;" . $c . "'>Jjg</td>";
			foreach ($bulan as $bln) {
				$tab .= "<td style='text-align:right;" . $c . "'>" . hidezerodecimal($tjjg[$bln], 0) . "</td>";
			}

			$tab .= "</tr>";
			$tab .= "<tr class='rowcontent'>";
			$tab .= "<td style='text-align:center;" . $c . "'>Kg</td>";
			foreach ($bulan as $bln) {
				$tab .= "<td style='text-align:right;" . $c . "'>" . hidezerodecimal($tkg[$bln], 0) . "</td>";
			}

			$tab .= "</tr>";
		} else {
			$tab .= "<tr class='rowcontent'><td colspan=" . $colspan . " style='text-align:center'>" . $_SESSION['lang']['datanotfound'] . "</tr>";
		}

		echo $tab;
		break;
	case 'showposting':
		$jab = getPostingJabatan('budget');
		$where = "";
		if ($param['tahun'] != '') {
			$where .= " and a.tahunbudget = '" . $param['tahun'] . "'";
		}
		if ($param['kodeorg'] != '') {
			$where .= " and a.kodeorg like '" . $param['kodeorg'] . "%'";
		}
		$where .= " and a.tipebudget = 'ESTATE' and a.kodebudget!='UMUM' and a.pta='BGT'";
		$where .= " and substr(a.kodeorg,1,4) = '" . $_SESSION['empl']['lokasitugas'] . "'";
		$tab = "<table id=pvtTable cellpadding=10 cellspacing=1 border=0 class='sortable'>
			<thead>
				<tr class=rowheader style=height:25px>
					<th align=center width=30px>No.</th>
					<th align=centers style='width:50px'>" . $_SESSION['lang']['budgetyear'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodeorg'] . "</th>
					<th align=center>" . $_SESSION['lang']['divisi'] . "</th>
					<th align=center>" . $_SESSION['lang']['luas'] . "</th>
					<th align=center>" . $_SESSION['lang']['pokok'] . "</th>
					<th align=center>" . $_SESSION['lang']['sph'] . "</th>
					<th align=center>" . $_SESSION['lang']['produksi'] . "<br>(Kg)</th>
					<th align=center>" . $_SESSION['lang']['sdm'] . "</th>
					<th align=center>" . $_SESSION['lang']['material'] . "</th>
					<th align=center>" . $_SESSION['lang']['peralatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['kontrak'] . "</th>
					<th align=center>" . $_SESSION['lang']['kndran'] . "</th>
					<th align=center>" . $_SESSION['lang']['total'] . "</th>
					<th align=center>Rp/Ha</th>
					<th align=center>Rp/Pkk</th>
					<th align=center>Rp/Kg</th>
					<th width=30px align=center>Action</th>
				</tr>
			</thead>
			<tbody>";
		$colspan = 13;

		$str = "SELECT kodebudget, sum(rupiah) as jumlah, noakun, kegiatan, tipebudget, substr(kodeorg,1,6) as divisi, a.tahunbudget
		FROM " . $dbname . ".bgt_budget a 
		where 1=1 " . $where . " group by a.tahunbudget, substr(kodeorg,1,6), kodebudget";
		$res = fetchData($str);
		foreach ($res as $bar) {
			if (substr($bar['kodebudget'], 0, 3) == 'SDM' or substr($bar['kodebudget'], 0, 4) == 'EXPL') {
				$data[$bar['tahunbudget']][$bar['divisi']]['sdm'] += $bar['jumlah'];
				$data[$bar['tahunbudget']][$bar['divisi']]['vol'] += $bar['hathnini'];
			}
			if (substr($bar['kodebudget'], 0, 2) == 'M-') {
				$data[$bar['tahunbudget']][$bar['divisi']]['mat'] += $bar['jumlah'];
			}
			if ($bar['kodebudget'] == 'TOOL') {
				$data[$bar['tahunbudget']][$bar['divisi']]['tool'] += $bar['jumlah'];
			}
			if (substr($bar['kodebudget'], 0, 3) == 'VHC') {
				$data[$bar['tahunbudget']][$bar['divisi']]['vhc'] += $bar['jumlah'];
			}
			if (substr($bar['kodebudget'], 0, 7) == 'KONTRAK') {
				$data[$bar['tahunbudget']][$bar['divisi']]['kont'] += $bar['jumlah'];
			}
		}

		$str = "SELECT sum(hathnini) as jumlah,sum(pokokthnini) as pkk, tahunbudget, substr(kodeblok,1,6) as divisi FROM " . $dbname . ".bgt_blok group by tahunbudget, substr(kodeblok,1,6)";
		$res = fetchData($str);
		foreach ($res as $bar) {
			$data[$bar['tahunbudget']][$bar['divisi']]['luas'] += $bar['jumlah'];
			$data[$bar['tahunbudget']][$bar['divisi']]['pkk'] += $bar['pkk'];
		}

		$str = "SELECT sum(totalkg ) as jumlah, tahunbudget, substr(kodeblok,1,6) as divisi FROM " . $dbname . ".bgt_produksi_kebun group by tahunbudget, substr(kodeblok,1,6)";
		$res = fetchData($str);
		foreach ($res as $bar) {
			$data[$bar['tahunbudget']][$bar['divisi']]['kg'] += $bar['jumlah'];
		}
		//exit("error");

		$str = "select a.*,substr(kodeorg,1,6) as divisi, sum(a.rupiah) as rupiah from " . $dbname . ".bgt_budget a where 1=1 " . $where . " group by a.tahunbudget, substr(kodeorg,1,6) order by a.tahunbudget desc,a.kodeorg asc";
		$res = fetchdata($str);
		if (count($res) > 0) {
			foreach ($res as $bar) {
				$dt[$bar['tahunbudget']][substr($bar['kodeorg'], 0, 4)][$bar['divisi']] = $bar['divisi'];
				$ttp[$bar['tahunbudget']][$bar['divisi']] = $bar['tutup'];
			}
			$no = 0;
			foreach ($dt as $tahunbudget => $v1) {
				foreach ($v1 as $kodeorg => $v2) {
					foreach ($v2 as $divisi) {
						$no++;
						$tab .= "<tr class='rowcontent'>";
						$tab .= "<td style='text-align:center'>" . $no . "</td>";
						$tab .= "<td align=center>" . $tahunbudget . "</td>";
						$tab .= "<td align=left>" . $kodeorg . " - " . $nmorg[$kodeorg] . "</td>";
						$tab .= "<td align=left>" . $divisi . " - " . $nmorg[$divisi] . "</td>";

						$popupluas = "onclick=getdatadetail('luas','" . $tahunbudget . "','" . $kodeorg . "','" . $divisi . "','','','','')";
						$popupprd = "onclick=getdatadetail('prd','" . $tahunbudget . "','" . $kodeorg . "','" . $divisi . "','','','','')";


						$tab .= "<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' " . $popupluas . ">" . @hidezerodecimal($data[$tahunbudget][$divisi]['luas'], 2) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' " . $popupluas . ">" . @hidezerodecimal($data[$tahunbudget][$divisi]['pkk']) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' " . $popupluas . ">" . @hidezerodecimal($data[$tahunbudget][$divisi]['pkk'] / $data[$tahunbudget][$divisi]['luas'], 2) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' " . $popupprd . ">" . @hidezerodecimal($data[$tahunbudget][$divisi]['kg']) . "</td>";

						$popupsdm = "onclick=getdatadetail('sdm','" . $tahunbudget . "','" . $kodeorg . "','" . $divisi . "','','','','')";
						$popupmat = "onclick=getdatadetail('mat','" . $tahunbudget . "','" . $kodeorg . "','" . $divisi . "','','','','')";
						$popuptool = "onclick=getdatadetail('tool','" . $tahunbudget . "','" . $kodeorg . "','" . $divisi . "','','','','')";
						$popupkont = "onclick=getdatadetail('kont','" . $tahunbudget . "','" . $kodeorg . "','" . $divisi . "','','','','')";
						$popupvhc = "onclick=getdatadetail('vhc','" . $tahunbudget . "','" . $kodeorg . "','" . $divisi . "','','','','')";

						$tab .= "<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' " . $popupsdm . ">" . @hidezerodecimal($data[$tahunbudget][$divisi]['sdm']) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' " . $popupmat . ">" . @hidezerodecimal($data[$tahunbudget][$divisi]['mat']) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' " . $popuptool . ">" . @hidezerodecimal($data[$tahunbudget][$divisi]['tool']) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' " . $popupkont . ">" . @hidezerodecimal($data[$tahunbudget][$divisi]['kont']) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' " . $popupvhc . ">" . @hidezerodecimal($data[$tahunbudget][$divisi]['vhc']) . "</td>";

						$ttl = $data[$tahunbudget][$divisi]['sdm'] + $data[$tahunbudget][$divisi]['mat'] + $data[$tahunbudget][$divisi]['tool'] + $data[$tahunbudget][$divisi]['kont'] + $data[$tahunbudget][$divisi]['vhc'];
						$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttl) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttl / $data[$tahunbudget][$divisi]['luas']) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttl / $data[$tahunbudget][$divisi]['pkk']) . "</td>";
						$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttl / $data[$tahunbudget][$divisi]['kg'], 2) . "</td>";


						$subttl[$tahunbudget][$kodeorg]['sdm'] += $data[$tahunbudget][$divisi]['sdm'];
						$subttl[$tahunbudget][$kodeorg]['mat'] += $data[$tahunbudget][$divisi]['mat'];
						$subttl[$tahunbudget][$kodeorg]['tool'] += $data[$tahunbudget][$divisi]['tool'];
						$subttl[$tahunbudget][$kodeorg]['kont'] += $data[$tahunbudget][$divisi]['kont'];
						$subttl[$tahunbudget][$kodeorg]['vhc'] += $data[$tahunbudget][$divisi]['vhc'];
						$subttl[$tahunbudget][$kodeorg]['luas'] += $data[$tahunbudget][$divisi]['luas'];
						$subttl[$tahunbudget][$kodeorg]['pkk'] += $data[$tahunbudget][$divisi]['pkk'];
						$subttl[$tahunbudget][$kodeorg]['kg'] += $data[$tahunbudget][$divisi]['kg'];
						$subttl[$tahunbudget][$kodeorg]['ttl'] += $ttl;


						if ($ttp[$tahunbudget][$divisi] == 0) {
							$tab .= "<td align=center width=25px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('" . $tahunbudget . "','" . $divisi . "');\" title='Posting'></td>";
						} else {
							if (in_array($_SESSION['empl']['jabatan'], $jab)) {
								$icon = "images/icons/04/16/04.png";
								$title = "Unclose / Unposting";
								$unpost = " onclick=\"unposting('" . $tahunbudget . "','" . $divisi . "');\" ";
							} else {
								$icon = "images/icons/04/16/02.png";
								$title = "Closed / Posted";
								$unpost = '';
							}
							$tab .= "<td align=center width=25px><img src=" . $icon . " class=zImgBtn class=zImgBtn title='" . $title . "' " . $unpost . " ></td>";
						}

						$tab .= "</tr>";
					}
					$tab .= "<tr class='rowcontent'>";
					$tab .= "<td style='text-align:center;background-color:#d4d2d2' colspan=4>Total</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['luas'], 2) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['pkk']) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['pkk'] / $subttl[$tahunbudget][$kodeorg]['luas'], 2) . "</td>";

					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['kg']) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['sdm']) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['mat']) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['tool']) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['kont']) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['vhc']) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['ttl']) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['ttl'] / $subttl[$tahunbudget][$kodeorg]['luas']) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['ttl'] / $subttl[$tahunbudget][$kodeorg]['pkk']) . "</td>";
					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>" . @hidezerodecimal($subttl[$tahunbudget][$kodeorg]['ttl'] / $subttl[$tahunbudget][$kodeorg]['kg'], 2) . "</td>";

					$tab .= "<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'></td>";
					$tab .= "</tr>";
				}
			}
		} else {
			$tab .= "<tr class='rowcontent'><td colspan=" . $colspan . " style='text-align:center'>Data tidak ditemukan.</td></tr>";
		}
		$tab .= "</tbody>
			<tfoot>
			</tfoot>
			</table>
			";
		echo $tab;
		break;

	case 'loaddata':
		$tab = "";
		if ($param['jenis'] == 'excel') {
			$tab .= "<table class='sortable' cellspacing=1 cellpadding=5 border=1>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center style='width:50px'>" . $_SESSION['lang']['budgetyear'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodeorg'] . "</th>
					<th align=center>" . $_SESSION['lang']['divisi'] . "</th>
					<th align=centers style='width:50px'>" . $_SESSION['lang']['tahuntanam'] . "</th>
					<!--<th align=center>" . $_SESSION['lang']['luas'] . "</th>
					<th align=center>" . $_SESSION['lang']['pokok'] . "</th>
					<th align=center style='width:50px'>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['akun'] . "</th>-->
					<th align=center style='width:70px'>" . $_SESSION['lang']['kodekegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['namakegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['volume'] . "</th>
					<th align=center>" . $_SESSION['lang']['rotasi'] . "</th>
					<th align=center>" . $_SESSION['lang']['sdm'] . "</th>
					<th align=center>" . $_SESSION['lang']['material'] . "</th>
					<th align=center>" . $_SESSION['lang']['peralatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['kontrak'] . "</th>
					<th align=center>" . $_SESSION['lang']['kndran'] . "</th>
					<th align=center>" . $_SESSION['lang']['total'] . "</th>
					<th align=center>" . $_SESSION['lang']['rpsat'] . "</th>
				</tr>
			</thead><tbody>";
		}

		$where = "";
		if ($param['tahun'] != '') {
			$where .= " and a.tahunbudget = '" . $param['tahun'] . "'";
		}
		if ($param['kodeorg'] != '') {
			$where .= " and a.kodeorg like '" . $param['kodeorg'] . "%'";
		}
		if ($param['divisi'] != '') {
			$where .= " and a.kodeorg like '%" . $param['divisi'] . "%'";
		}
		if ($param['tt'] != '') {
			$where .= " and b.thntnm = '" . $param['tt'] . "'";
		}
		if ($param['noakunsch'] != '') {
			$where .= " and noakun in (select noakun from " . $dbname . ".keu_5akun where noakun like '%" . $param['noakunsch'] . "%' or namaakun like '%" . $param['noakunsch'] . "%')";
		}
		if ($param['kegiatan'] != '') {
			$where .= " and kegiatan in (select kodekegiatan from " . $dbname . ".setup_kegiatan where kodekegiatan like '%" . $param['kegiatan'] . "%' or namakegiatan like '%" . $param['kegiatan'] . "%')";
		}
		$where .= " and a.tipebudget = 'ESTATE' and a.kodebudget!='UMUM' and a.pta='BGT'";
		//$where.=" and substr(a.kodeorg,1,4) in (".getOrgDetail(2).")";
		$where .= " and substr(a.kodeorg,1,4) = '" . $_SESSION['empl']['lokasitugas'] . "'";


		$limit = 15;
		$page = 0;
		$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
		if (isset($_POST['page'])) {
			$page = intval($_POST['page']);
			if ($page < 0) {
				$page = 0;
			}
		}

		$offset    = floatval($page) * $limit;
		$maxdisplay = floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 22;




		$sql = "select count(*) from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $where . " group by substr(kodeorg,1,6), b.thntnm, a.kegiatan";
		$res = fetchdata($sql);
		$jlhbrs = count($res);

		$kdkel = array('126' => 'TBM', '128' => 'BBT', '611' => 'TM', '621' => 'TM');
		$rowspan = "";
		if ($param['jenis'] != 'excel') {
			$lmt = "limit " . $offset . "," . $limit . "";
		}



		$str = "SELECT thntnm, kodebudget, sum(rupiah) as jumlah, noakun, kegiatan, tipebudget, substr(kodeorg,1,6) as divisi, a.tahunbudget,sum(volume) as volume  
		FROM " . $dbname . ".bgt_budget a 
		left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget
		where 1=1 " . $where . " group by kodebudget,a.tahunbudget, substr(kodeorg,1,6), thntnm, noakun, kegiatan";

		// if(substr($_SESSION['standard']['username'],0,7)=='tim.owl') echo $str;
		
		$res = fetchData($str);
		foreach ($res as $bar) {
			if (substr($bar['kodebudget'], 0, 3) == 'SDM' or substr($bar['kodebudget'], 0, 4) == 'EXPL') {
				$data[$bar['tahunbudget']][$bar['divisi']][$bar['thntnm']][$bar['noakun']][$bar['kegiatan']]['sdm'] += $bar['jumlah'];
				$data[$bar['tahunbudget']][$bar['divisi']][$bar['thntnm']][$bar['noakun']][$bar['kegiatan']]['vol'] += $bar['volume'];
			}
			if (substr($bar['kodebudget'], 0, 2) == 'M-') {
				$data[$bar['tahunbudget']][$bar['divisi']][$bar['thntnm']][$bar['noakun']][$bar['kegiatan']]['mat'] += $bar['jumlah'];
			}
			if ($bar['kodebudget'] == 'TOOL') {
				$data[$bar['tahunbudget']][$bar['divisi']][$bar['thntnm']][$bar['noakun']][$bar['kegiatan']]['tool'] += $bar['jumlah'];
				$data[$bar['tahunbudget']][$bar['divisi']][$bar['thntnm']][$bar['noakun']][$bar['kegiatan']]['voltool'] += $bar['volume'];
			}
			if (substr($bar['kodebudget'], 0, 7) == 'KONTRAK') {
				$data[$bar['tahunbudget']][$bar['divisi']][$bar['thntnm']][$bar['noakun']][$bar['kegiatan']]['kont'] += $bar['jumlah'];
				$data[$bar['tahunbudget']][$bar['divisi']][$bar['thntnm']][$bar['noakun']][$bar['kegiatan']]['vol'] += $bar['volume'];
			}
			if (substr($bar['kodebudget'], 0, 3) == 'VHC') {
				$data[$bar['tahunbudget']][$bar['divisi']][$bar['thntnm']][$bar['noakun']][$bar['kegiatan']]['vhc'] += $bar['jumlah'];
				$data[$bar['tahunbudget']][$bar['divisi']][$bar['thntnm']][$bar['noakun']][$bar['kegiatan']]['volvhc'] += $bar['volume'];
			}
		}



		//tahunbudget='".$val['tahunbudget']."' and substr(kodeorg,1,6)='".$val['divisi']."' and tipebudget='".$val['tipebudget']."' and  kegiatan='".$val['kegiatan']."' and noakun='".$val['noakun']."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where tahunbudget='".$val['tahunbudget']."' and substr(kodeblok,1,6)='".$val['divisi']."' and thntnm='".$val['thntnm']."')





		$str = "select substr(a.kodeorg,1,4) as kodeunit,substr(a.kodeorg,1,6) as divisi,a.*,b.thntnm, sum(b.hathnini) as luas, sum(b.pokokthnini) as pokok  from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $where . " group by substr(kodeorg,1,6), b.thntnm, a.kegiatan order by a.tahunbudget desc,a.kodeorg asc,b.thntnm asc, a.noakun asc,a.kegiatan asc " . $lmt . "";
		$res = fetchdata($str);
		if (count($res) > 10000) {
			exit("Warning : Data terlalu banyak, silahkan filter terlebih dahulu.");
		}

		if (count($res) > 0) {
			foreach ($res as $val) {
				$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $val['noakun'] . "'");
				$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $val['kegiatan'] . "'");

				$no++;
				$tab .= "<tr class='rowcontent' style='height:25px'>";
				$tab .= "<td style='text-align:center;vertical-align:top;'>" . $no . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:top;'>" . $val['tahunbudget'] . "</td>";
				#$tab.="<td style='text-align:left;vertical-align:top;'>".$nmorg[$val['kodeunit']]."</td>";
				#$tab.="<td style='text-align:left;vertical-align:top;'>".$nmorg[$val['divisi']]."</td>";
				$tab .= "<td style='text-align:center;vertical-align:top;'>" . $val['kodeunit'] . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:top;'>" . $val['divisi'] . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:top;'>" . $val['thntnm'] . "</td>";
				#$tab.="<td style='text-align:center;vertical-align:top;'>".hidezerodecimal($val['luas'],2)."</td>";
				#$tab.="<td style='text-align:center;vertical-align:top;'>".hidezerodecimal($val['pokok'])."</td>";
				#$tab.="<td style='text-align:center;vertical-align:top;'>".$val['noakun']."</td>";
				#$tab.="<td style='text-align:left;vertical-align:top;'>".$nmakun[$val['noakun']]."</td>";
				$tab .= "<td style='text-align:center;vertical-align:top;'>" . $val['kegiatan'] . "</td>";
				$tab .= "<td style='text-align:left;vertical-align:top;'>" . $nmkeg[$val['kegiatan']] . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:top;'>" . $val['satuanv'] . "</td>";

				#volume
				/* $vol = "SELECT sum(volume) as volume  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and substr(kodeorg,1,6)='".$val['divisi']."' and tipebudget='".$val['tipebudget']."' and  kegiatan='".$val['kegiatan']."' and noakun='".$val['noakun']."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where tahunbudget='".$val['tahunbudget']."' and substr(kodeblok,1,6)='".$val['divisi']."' and thntnm='".$val['thntnm']."')  group by kodebudget order by volume desc";
				$resvol = fetchData($vol);
				$tab.="<td style='text-align:right;vertical-align:top;'>".@hidezerodecimal($resvol[0]['volume'],2)."</td>";
				 */

				if ($data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['vol'] == 0 and $data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['tool'] > 0) {
					$data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['vol'] = $data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['voltool'];
				} elseif ($data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['vol'] == 0 and $data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['vhc'] > 0) {
					$data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['vol'] = $data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['volvhc'];
				}




				$tab .= "<td style='text-align:right;vertical-align:top;'>" . @hidezerodecimal($data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['vol']) . "</td>";

				$tab .= "<td style='text-align:center;vertical-align:top;'>" . hidezerodecimal($val['rotasi'], 2) . "</td>";

				$popupsdm = "onclick=getdatadetail('sdm','" . $val['tahunbudget'] . "','" . $val['kodeunit'] . "','" . $val['divisi'] . "','','" . $val['thntnm'] . "','" . $val['noakun'] . "','" . $val['kegiatan'] . "')";

				$popupmat = "onclick=getdatadetail('mat','" . $val['tahunbudget'] . "','" . $val['kodeunit'] . "','" . $val['divisi'] . "','','" . $val['thntnm'] . "','" . $val['noakun'] . "','" . $val['kegiatan'] . "')";
				$popuptool = "onclick=getdatadetail('tool','" . $val['tahunbudget'] . "','" . $val['kodeunit'] . "','" . $val['divisi'] . "','','" . $val['thntnm'] . "','" . $val['noakun'] . "','" . $val['kegiatan'] . "')";
				$popupkont = "onclick=getdatadetail('kont','" . $val['tahunbudget'] . "','" . $val['kodeunit'] . "','" . $val['divisi'] . "','','" . $val['thntnm'] . "','" . $val['noakun'] . "','" . $val['kegiatan'] . "')";
				$popupvhc = "onclick=getdatadetail('vhc','" . $val['tahunbudget'] . "','" . $val['kodeunit'] . "','" . $val['divisi'] . "','','" . $val['thntnm'] . "','" . $val['noakun'] . "','" . $val['kegiatan'] . "')";

				$tab .= "<td style='text-align:right;vertical-align:top;color:blue;cursor:pointer;' " . $popupsdm . ">" . @hidezerodecimal($data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['sdm']) . "</td>";
				$tab .= "<td style='text-align:right;vertical-align:top;color:blue;cursor:pointer;' " . $popupmat . ">" . @hidezerodecimal($data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['mat']) . "</td>";
				$tab .= "<td style='text-align:right;vertical-align:top;color:blue;cursor:pointer;' " . $popuptool . ">" . @hidezerodecimal($data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['tool']) . "</td>";
				$tab .= "<td style='text-align:right;vertical-align:top;color:blue;cursor:pointer;' " . $popupkont . ">" . @hidezerodecimal($data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['kont']) . "</td>";
				$tab .= "<td style='text-align:right;vertical-align:top;color:blue;cursor:pointer;' " . $popupvhc . ">" . @hidezerodecimal($data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['vhc']) . "</td>";

				/* 
				#Material
				$mat = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and substr(kodeorg,1,6)='".$val['divisi']."' and tipebudget='".$val['tipebudget']."' and  kegiatan='".$val['kegiatan']."' and noakun='".$val['noakun']."' and kodebudget like 'M-%' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where tahunbudget='".$val['tahunbudget']."' and substr(kodeblok,1,6)='".$val['divisi']."' and thntnm='".$val['thntnm']."')";
				$resmat = fetchData($mat);
				$tab.="<td style='text-align:right;vertical-align:top;'>".@hidezerodecimal($resmat[0]['jumlah'])."</td>";
				#tool
				$tool = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and substr(kodeorg,1,6)='".$val['divisi']."' and tipebudget='".$val['tipebudget']."' and  kegiatan='".$val['kegiatan']."' and noakun='".$val['noakun']."' and kodebudget like 'TOOL%' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where tahunbudget='".$val['tahunbudget']."' and substr(kodeblok,1,6)='".$val['divisi']."' and thntnm='".$val['thntnm']."')";
				$restool = fetchData($tool);
				$tab.="<td style='text-align:right;vertical-align:top;'>".@hidezerodecimal($restool[0]['jumlah'])."</td>";
				#kont
				$kont = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and substr(kodeorg,1,6)='".$val['divisi']."' and tipebudget='".$val['tipebudget']."' and  kegiatan='".$val['kegiatan']."' and noakun='".$val['noakun']."' and kodebudget like 'KONTRAK%' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where tahunbudget='".$val['tahunbudget']."' and substr(kodeblok,1,6)='".$val['divisi']."' and thntnm='".$val['thntnm']."')";
				$reskont = fetchData($kont);
				$tab.="<td style='text-align:right;vertical-align:top;'>".@hidezerodecimal($reskont[0]['jumlah'])."</td>";
				#vhc
				$vhc = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and substr(kodeorg,1,6)='".$val['divisi']."' and tipebudget='".$val['tipebudget']."' and  kegiatan='".$val['kegiatan']."' and noakun='".$val['noakun']."' and kodebudget like 'VHC%' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where tahunbudget='".$val['tahunbudget']."' and substr(kodeblok,1,6)='".$val['divisi']."' and thntnm='".$val['thntnm']."')";
				$resvhc = fetchData($vhc);
				$tab.="<td style='text-align:right;vertical-align:top;'>".@hidezerodecimal($resvhc[0]['jumlah'])."</td>"; */

				$ttl = $data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['sdm'] + $data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['mat'] + $data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['tool'] + $data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['kont'] + $data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['vhc'];
				$tab .= "<td style='text-align:right;vertical-align:top;'>" . @hidezerodecimal($ttl) . "</td>";
				$tab .= "<td style='text-align:right;vertical-align:top;'>" . @hidezerodecimal($ttl / $data[$val['tahunbudget']][$val['divisi']][$val['thntnm']][$val['noakun']][$val['kegiatan']]['vol']) . "</td>";

				if ($val['tutup'] == '0') {
					$tab .= "<td align=center style='vertical-align:top;' width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editdetail('" . $val['tahunbudget'] . "','" . $val['kodeunit'] . "','" . $val['divisi'] . "','" . $val['thntnm'] . "','" . $val['kodeblok'] . "','" . $val['noakun'] . "','" . $val['kegiatan'] . "','" . $val['satuanv'] . "','" . $val['volume'] . "','" . $val['rotasi'] . "','" . hidezerodecimal($val['volume'] / $val['rotasi'], 3) . "','" . $kdkel[substr($val['noakun'], 0, 3)] . "');\" ></td>";

					$tab .= "<td align=center style='vertical-align:top;' width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('" . $val['tahunbudget'] . "','" . $val['divisi'] . "','" . $val['thntnm'] . "','" . $val['kegiatan'] . "');\" title='Delete'></td>";
				} else {
					$tab .= "<td align=center width=25px></td>";
					$tab .= "<td align=center width=25px></td>";
				}
				$tab .= "<td align=center style='vertical-align:top;' width=25px><img src=images/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview'  onclick=\"preview('" . $val['tahunbudget'] . "','" . $val['divisi'] . "','" . $val['thntnm'] . "','" . $val['kegiatan'] . "','html');\" ></td>";

				$tab .= "</tr>";
			}

			/* #== TOTAL ==
			$c="vertical-align:top;background-color:#AED6F1;";
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:center;".$c."' colspan=5>TOTAL</td>";
			$tab.="<td style='text-align:right;".$c."'>".hidezerodecimal($tluas,2)."</td>";
			$tab.="<td style='text-align:right;".$c."'>".hidezerodecimal($tpokok,0)."</td>";
			$tab.="<td style='text-align:right;".$c."'>".@hidezerodecimal($tpokok/$tluas,2)."</td>";
			$tab.="<td style='text-align:right;".$c."'>".@hidezerodecimal(($tttlkg/$tluas)/1000,2)."</td>";
			$tab.="<td style='text-align:right;".$c."'>".@hidezerodecimal(($tttljjg/$tpokok),2)."</td>";
			$tab.="<td style='text-align:right;".$c."'>".@hidezerodecimal($tttljjg,0)."</td>";
			$tab.="<td style='text-align:right;".$c."'>".@hidezerodecimal($tttlkg/$tttljjg,2)."</td>";
			$tab.="<td style='text-align:right;".$c."'>".@hidezerodecimal($tttlkg,0)."</td>";
			
			$tab.="</tr>"; */
		} else {
			$tab .= "<tr class='rowcontent'><td colspan=" . $colspan . " style='text-align:center'>" . $_SESSION['lang']['datanotfound'] . "</td></tr>";
		}

		## PAGING
		$foot = createpaging($jlhbrs, $limit, $page, $colspan, 'loaddata', 'getPage');

		if ($param['jenis'] == 'excel') {
			$tab .= "</tbody></table>";
			$nop = "bgt_prd_" . $param['tahun'] . ".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("bgt_prd_" . $param['tahun'], $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		} else {
			echo $tab . "####" . $foot;
		}
		break;
	case 'showsebaran':
		$tab = "";
		$bulan = range(1, 12);

		$where = "";
		if ($param['tahun'] != '') {
			$where .= " and a.tahunbudget = '" . $param['tahun'] . "'";
		}
		if ($param['kodeorg'] != '') {
			$where .= " and a.kodeorg like '" . $param['kodeorg'] . "%'";
		}
		if ($param['divisi'] != '') {
			$where .= " and a.kodeorg like '" . $param['divisi'] . "%'";
		}
		if ($param['tt'] != '') {
			$where .= " and b.thntnm = '" . $param['tt'] . "'";
		}
		if ($param['sebaran'] == '1') {
			$where .= " and c.kunci IS NOT NULL";
		}
		if ($param['sebaran'] == '2') {
			$where .= " and c.kunci IS NULL";
		}

		if ($param['noakunsch'] != '') {
			$where .= " and noakun in (select noakun from " . $dbname . ".keu_5akun where noakun like '%" . $param['noakunsch'] . "%' or namaakun like '%" . $param['noakunsch'] . "%')";
		}
		if ($param['kegiatan'] != '') {
			$where .= " and kegiatan in (select kodekegiatan from " . $dbname . ".setup_kegiatan where kodekegiatan like '%" . $param['kegiatan'] . "%' or namakegiatan like '%" . $param['kegiatan'] . "%')";
		}
		$where .= " and a.tipebudget = 'ESTATE' and a.kodebudget!='UMUM' and a.pta='BGT'";
		$where .= " and substr(a.kodeorg,1,4) = '" . $_SESSION['empl']['lokasitugas'] . "'";

		if ($param['jlhbaris'] > '5000') {
			exit("Warning : Jumlah baris maksimal 5000");
		}

		if ($param['jlhbaris'] == '' or $param['jlhbaris'] == '0') {
			$limit = 15;
		} else {
			$limit = $param['jlhbaris'];
		}

		if ($param['tampilkan'] == '1') {
			$group = "group by a.tahunbudget,substr(kodeorg,1,6), b.thntnm, a.kegiatan";
		} else {
			$group = "group by a.tahunbudget,kodeorg, kodebudget, kegiatan, noakun, kodebarang, kodevhc";
		}

		$page = 0;
		$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
		if (isset($_POST['page'])) {
			$page = intval($_POST['page']);
			if ($page < 0) {
				$page = 0;
			}
		}

		$offset    = floatval($page) * $limit;
		$maxdisplay = floatval($page * $limit);
		$no        = $maxdisplay;
		if ($param['tampilkan'] != '1') {
			$colspan   = 24;
		} else {
			$colspan   = 20;
		}

		$sql = "select count(*) from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget left join " . $dbname . ".bgt_distribusi c on a.kunci=c.kunci  where 1=1 " . $where . " " . $group . "";
		$res = fetchdata($sql);
		$jlhbrs = count($res);

		$kdkel = array('126' => 'TBM', '128' => 'BBT', '611' => 'TM', '621' => 'TM');
		$rowspan = "";
		$lmt = "limit " . $offset . "," . $limit . "";

		$str = "select c.kunci as kuncisebar,sum(c.rp01) as rp01,sum(c.rp02) as rp02,sum(c.rp03) as rp03,sum(c.rp04) as rp04,sum(c.rp05) as rp05,sum(c.rp06) as rp06,sum(c.rp07) as rp07,sum(c.rp08) as rp08,sum(c.rp09) as rp09,sum(c.rp10) as rp10,sum(c.rp11) as rp11,sum(c.rp12) as rp12, substr(a.kodeorg,1,4) as kodeunit,substr(a.kodeorg,1,6) as divisi,a.*,b.thntnm, sum(a.rupiah) as rupiah 
		from " . $dbname . ".bgt_budget a 
		left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget 
		left join " . $dbname . ".bgt_distribusi c on a.kunci=c.kunci  
		where 1=1 " . $where . " " . $group . "
		order by a.tahunbudget desc,a.kodeorg asc,b.thntnm asc, a.noakun asc,a.kegiatan asc " . $lmt . "";
		$res = fetchdata($str);
		if (count($res) > 10000) {
			exit("Warning : Data terlalu banyak, silahkan filter terlebih dahulu.");
		}
		$awal = 0;
		if (count($res) > 0) {
			$tab .= "<tr class='rowcontent'>
					<td colspan=" . $colspan . " style='text-align:left'>
						<button class=\"mybutton\" id=btnprev onclick=sebarkan('" . $awal . "','" . $no . "','" . $param['tampilkan'] . "')>" . $_SESSION['lang']['sebaran'] . " " . $_SESSION['lang']['all'] . "</button></td></tr>";
			foreach ($res as $val) {
				$no++;
				if ($awal == 0) {
					$awal = $no;
				}

				$nmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $val['kodebarang'] . "'");
				$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $val['noakun'] . "'");
				$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $val['kegiatan'] . "'");
				$check = "";

				$tab .= "<tr class='rowcontent' style='height:25px' id=rowsebar" . $no . ">";
				if ($param['tampilkan'] == '1') {
					$tab .= "<td width=25px align=center>
							<input id=chkboxsebar" . $no . " type=checkbox " . $check . " onclick=sebartt('" . $no . "'); title=\"Sebarkan sesuai proporsi diatas\">
						</td>";
				} elseif ($param['tampilkan'] == '2') {
					$tab .= "<td width=25px align=center>
							<input id=chkboxsebar" . $no . " type=checkbox " . $check . " onclick=sebardetail('" . $no . "','" . $no . "'); title=\"Sebarkan sesuai proporsi diatas\">
						</td>";
				}
				$tab .= "<td hidden id=index" . $no . ">" . $val['kunci'] . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:middle;'>" . $no . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:middle;' id=tahun" . $no . ">" . $val['tahunbudget'] . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:middle;' id=divisi" . $no . ">" . $val['divisi'] . "</td>";
				if ($param['tampilkan'] != '1') {
					$tab .= "<td style='text-align:center;vertical-align:middle;'>" . $val['kodeorg'] . "</td>";
				}
				$tab .= "<td style='text-align:center;vertical-align:middle;' id=tt" . $no . ">" . $val['thntnm'] . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:middle;' id=kegiatan" . $no . ">" . $val['kegiatan'] . "</td>";
				$tab .= "<td style='text-align:left;vertical-align:middle;'>" . $nmkeg[$val['kegiatan']] . "</td>";
				if ($param['tampilkan'] != '1') {
					$tab .= "<td style='text-align:left;vertical-align:middle;'>" . $val['kodebudget'] . "</td>";
					$tab .= "<td style='text-align:left;vertical-align:middle;'>" . $nmbrg[$val['kodebarang']] . "</td>";
					$tab .= "<td style='text-align:left;vertical-align:middle;'>" . $val['kodevhc'] . "</td>";
				}
				$tab .= "<td style='text-align:right;vertical-align:middle;'>" . hidezerodecimal($val['rupiah']) . "</td>";
				foreach ($bulan as $bln) {
					$tab .= "<td style='text-align:right;vertical-align:middle;'>" . hidezerodecimal($val['rp' . addZero($bln, 2)]) . "</td>";
				}

				$tab .= "</tr>";
			}
			$tab .= "<tr class='rowcontent' style=display:none>
						<td colspan=" . $colspan . " style='text-align:left'>
						<input id=awalsebar value='" . $awal . "'>
						<input id=akhirsebar value='" . $no . "'>
							<button class=mybutton id=btnprev onclick=sebarkan('" . $awal . "','" . $no . "','" . $param['tampilkan'] . "')>" . $_SESSION['lang']['sebaran'] . " " . $_SESSION['lang']['all'] . "</button>
						</tr>";
		} else {
			$tab .= "<tr class='rowcontent'><td colspan=" . $colspan . " style='text-align:center'>" . $_SESSION['lang']['datanotfound'] . "</td></tr>";
		}

		## PAGING
		$foot = createpagingsebar($jlhbrs, $limit, $page, $colspan, 'showsebaran', 'getPageSbr');


		echo $tab . "####" . $foot;
		// if($param['jenis']=='excel'){
		// $tab.="</tbody></table>";
		// $nop = "bgt_prd_".$param['tahun'].".xls";
		// $xls = new HtmlExcel();
		// $xls->setCss($css);
		// $xls->addSheet("bgt_prd_".$param['tahun'], $tab);
		// $xls->headers($nop);
		// echo $xls->buildFile();
		// }else{			
		// }
		break;
	case 'rekapperblok':
		$tab .= "
			<table class='sortable' cellspacing=1 cellpadding=5 border=0 width=100%>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center>" . $_SESSION['lang']['blok'] . "</th>
					<th align=center style='width:70px'>" . $_SESSION['lang']['kodekegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['namakegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['volume'] . "</th>
					<th align=center>" . $_SESSION['lang']['rotasi'] . "</th>
					<th align=center>" . $_SESSION['lang']['sdm'] . "</th>
					<th align=center>" . $_SESSION['lang']['material'] . "</th>
					<th align=center>" . $_SESSION['lang']['peralatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['kontrak'] . "</th>
					<th align=center>" . $_SESSION['lang']['kndran'] . "</th>
					<th align=center>" . $_SESSION['lang']['total'] . "</th>
					<th align=center>" . $_SESSION['lang']['rpsat'] . "</th>
					<th align=center>Action</th>
				</tr>
			</thead><tbody>";
		$wh .= " and substr(a.kodeorg,1,4) = '" . $_SESSION['empl']['lokasitugas'] . "'";
		$wh .= " and a.kodeorg like '" . $param['divisi'] . "%'";
		$wh .= " and a.kegiatan = '" . $param['kegiatan'] . "'";
		$wh .= " and b.thntnm = '" . $param['tt'] . "'";
		$wh .= " and a.tahunbudget = '" . $param['tahun'] . "'";
		$wh .= " and a.tipebudget = 'ESTATE' and a.kodebudget!='UMUM' and a.pta='BGT'";

		$str = "select substr(a.kodeorg,1,6) as divisi,a.*, b.*, sum(a.volume) as volume  from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " group by kodeorg order by kodeorg asc";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $val['noakun'] . "'");
			$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $val['kegiatan'] . "'");

			$no++;
			$tab .= "<tr class='rowcontent' style='height:25px'>";
			$tab .= "<td style='text-align:center;vertical-align:middle;'>" . $no . "</td>";
			$tab .= "<td style='text-align:center;vertical-align:middle;'>" . $nmorg[$val['kodeorg']] . "</td>";
			$tab .= "<td style='text-align:center;vertical-align:middle;'>" . $val['kegiatan'] . "</td>";
			$tab .= "<td style='text-align:left;vertical-align:middle;'>" . $nmkeg[$val['kegiatan']] . "</td>";
			$tab .= "<td style='text-align:center;vertical-align:middle;'>" . $val['satuanv'] . "</td>";
			$tab .= "<td style='text-align:right;vertical-align:middle;'>" . hidezerodecimal($val['volume'], 2) . "</td>";
			$tab .= "<td style='text-align:center;vertical-align:middle;'>" . $val['rotasi'] . "</td>";

			#SDM
			$sdm = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='" . $val['tahunbudget'] . "' and kodeorg='" . $val['kodeorg'] . "' and tipebudget='" . $val['tipebudget'] . "' and  kegiatan='" . $val['kegiatan'] . "' and noakun='" . $val['noakun'] . "' and (kodebudget like 'EXPL%' or kodebudget like 'SDM%')";
			$ressdm = fetchData($sdm);
			$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ressdm[0]['jumlah']) . "</td>";
			#Material
			$mat = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='" . $val['tahunbudget'] . "' and kodeorg='" . $val['kodeorg'] . "' and tipebudget='" . $val['tipebudget'] . "' and  kegiatan='" . $val['kegiatan'] . "' and noakun='" . $val['noakun'] . "' and kodebudget like 'M-%'";
			$resmat = fetchData($mat);
			$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($resmat[0]['jumlah']) . "</td>";
			#tool
			$tool = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='" . $val['tahunbudget'] . "' and kodeorg='" . $val['kodeorg'] . "' and tipebudget='" . $val['tipebudget'] . "' and  kegiatan='" . $val['kegiatan'] . "' and noakun='" . $val['noakun'] . "' and kodebudget like 'TOOL%'";
			$restool = fetchData($tool);
			$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($restool[0]['jumlah']) . "</td>";
			#kont
			$kont = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='" . $val['tahunbudget'] . "' and kodeorg='" . $val['kodeorg'] . "' and tipebudget='" . $val['tipebudget'] . "' and  kegiatan='" . $val['kegiatan'] . "' and noakun='" . $val['noakun'] . "' and kodebudget like 'KONTRAK%'";
			$reskont = fetchData($kont);
			$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($reskont[0]['jumlah']) . "</td>";
			#vhc
			$vhc = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='" . $val['tahunbudget'] . "' and kodeorg='" . $val['kodeorg'] . "' and tipebudget='" . $val['tipebudget'] . "' and  kegiatan='" . $val['kegiatan'] . "' and noakun='" . $val['noakun'] . "' and kodebudget like 'VHC%'";
			$resvhc = fetchData($vhc);
			$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($resvhc[0]['jumlah']) . "</td>";

			$ttl = $resvhc[0]['jumlah'] + $reskont[0]['jumlah'] + $restool[0]['jumlah'] + $resmat[0]['jumlah'] + $ressdm[0]['jumlah'];
			$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttl) . "</td>";
			$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttl / $val['volume']) . "</td>";

			if ($param['jenis'] != 'excel' and $val['tutup'] != '1') {
				$tab .= "<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delrekapblok('" . $val['tahunbudget'] . "','" . $val['kodeorg'] . "','" . $val['kegiatan'] . "');\" title='Delete'></td>";
			} else {
				$tab .= "<td align=center width=25px></td>";
			}
			$ttlvol += $val['volume'];
			$ttlsdm += $ressdm[0]['jumlah'];
			$ttlmat += $resmat[0]['jumlah'];
			$ttltool += $restool[0]['jumlah'];
			$ttlkont += $reskont[0]['jumlah'];
			$ttlvhc += $resvhc[0]['jumlah'];
			$gttl = $ttlvol + $ttlsdm + $ttlmat + $ttltool + $ttlkont + $ttlvhc;

			$tab .= "</tr>";
		}

		$tab .= "<tr class='rowcontent' style='height:25px'>";
		$tab .= "<td style='text-align:center;vertical-align:middle;' colspan=5>TOTAL</td>";
		$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttlvol, 2) . "</td>";
		$tab .= "<td style='text-align:center;vertical-align:middle;'></td>";
		$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttlsdm, 0) . "</td>";
		$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttlmat, 0) . "</td>";
		$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttltool, 0) . "</td>";
		$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttlkont, 0) . "</td>";
		$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($ttlvhc, 0) . "</td>";
		$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($gttl, 0) . "</td>";
		$tab .= "<td style='text-align:right;vertical-align:middle;'>" . @hidezerodecimal($gttl / $ttlvol, 0) . "</td>";
		$tab .= "<td style='text-align:center;vertical-align:middle;'></td>";
		$tab .= "</tr>";

		$tab .= "</tbody></table>";

		echo $tab;
		break;
	case 'loaddatasdm':

		if ($param['tipe'] != 'popup') {
			// $tab.="<div class='table-scroll'>";
		}
		if ($param['tipe'] == 'popup') {
			$tab .= "<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportTableToExcel('loaddatasdm');\" >";
		}
		//$tab.="<table id=loaddatasdm class='sortable' cellspacing=1 cellpadding=5 border=0>
		$tab .= "<table id=loaddatasdm cellpadding=3 cellspacing=1 border=0 class=sortable>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
					<th align=center>" . $_SESSION['lang']['blok'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodeanggaran'] . "</th>
					<th align=center style='width:55px'>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>
					<th align=center style='width:70px'>" . $_SESSION['lang']['kodekegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['namakegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['aruskas'] . "</th>
					<th align=center width=50px>" . $_SESSION['lang']['rotasi'] . "</th>
					<th align=center>" . $_SESSION['lang']['volume'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['rp'] . "</th>";
		if ($param['tipe'] != 'popup') {
			$tab .= "<th align=center colspan=2>Action</th>";
		}
		$tab .= "</tr>
			</thead><tbody>";

		$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
		$wh = "";
		if ($param['blok'] != '') {
			$style = "";
			$stytt = "style=cursor:pointer;height:25px;display:none";
			$wh .= " and substr(a.kodeorg,1,4) = '" . $param['blok'] . "'";
		} else {
			$stytt = "style=cursor:pointer;height:25px";
			$style = "style=display:none";
			$wh .= " and substr(a.kodeorg,1,6) = '" . $param['divisi'] . "'";
		}
		if ($param['tt'] != '') {
			$wh .= " and b.thntnm = '" . $param['tt'] . "'";
		}
		if ($param['jenis'] != '') {
			$wh .= " and b.statusblok = '" . $param['jenis'] . "'";
		}
		if ($param['kegiatan'] != '') {
			$wh .= " and a.kegiatan = '" . $param['kegiatan'] . "'";
		}
		if ($param['noakun'] != '') {
			$wh .= " and noakun='" . $param['noakun'] . "'";
		}
		$wh .= " and a.tahunbudget = '" . $param['tahun'] . "'";
		$wh .= " and a.tipebudget = 'ESTATE' and a.kodebudget!='UMUM' and a.pta='BGT'";
		$wh .= " and (kodebudget like 'EXPL%' or kodebudget like 'SDM%')";

		$wh .= " and substr(a.kodeorg,1,4) = '" . $_SESSION['empl']['lokasitugas'] . "'";
		$wh .= " and substr(a.kodeorg,1,6) = '" . $param['divisi'] . "'";

		$data = array();
		$str = "select a.*,b.thntnm from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " order by kodeorg asc, kodebudget asc";

		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $bar['noakun'] . "'");
			$data[$bar['thntnm']][$bar['kodebudget']] = $bar['kodebudget'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['kas'] = $bar['aruskas'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['satv'] = $bar['satuanv'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['satj'] = $bar['satuanj'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['rot'] = $bar['rotasi'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['vol'] += $bar['volume'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['jlh'] += $bar['jumlah'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['rp'] += $bar['rupiah'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['acc'] = $bar['noakun'];
		}

		if (count($res) > 0) {
			$no = 0;
			foreach ($data as $tt => $vkdbgt) {
				foreach ($vkdbgt as $kdbgt) {
					$str = "select a.*,b.thntnm from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " and kodebudget='" . $kdbgt . "'order by kodeorg asc";
					$res = fetchdata($str);
					$row = 0;
					foreach ($res as $bar) {
						$nmkas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $bar['aruskas'] . "'");
						$row++;
						$no++;

						$tab .= "<tr class='rowcontent' id=row_" . $no . " " . $style . ">";
						$tab .= "<td style='text-align:center;'></td>";
						$tab .= "<td style='text-align:center;'>" . $tt . "</td>";
						$tab .= "<td style='text-align:center;'>" . $nmorg[$bar['kodeorg']] . "</td>";
						$tab .= "<td style='text-align:left;'>" . $nmkode[$bar['kodebudget']] . "</td>";
						$tab .= "<td style='text-align:center;'>" . $bar['noakun'] . "</td>";
						$tab .= "<td style='text-align:left;'>" . getNamaAkun($bar['noakun']) . "</td>";
						$tab .= "<td style='text-align:center;'>" . $bar['kegiatan'] . "</td>";
						$tab .= "<td style='text-align:left;'>" . $nmkeg[$bar['kegiatan']] . "</td>";
						$tab .= "<td style='text-align:left;'>" . $bar['aruskas'] . " - " . $nmkas[$bar['aruskas']] . "</td>";
						$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['rotasi'], 2) . "</td>";
						$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['volume'], 2) . "</td>";
						$tab .= "<td style='text-align:center;'>" . $bar['satuanv'] . "</td>";
						$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['jumlah'], 2) . "</td>";
						$tab .= "<td style='text-align:center;'>" . $bar['satuanj'] . "</td>";
						$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['rupiah']) . "</td>";
						if ($param['jenis'] != 'excel' and $param['tipe'] != 'popup') {
							$tab .= "<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editsdm('" . $bar['kunci'] . "','" . $bar['kodeorg'] . "','" . $bar['aruskas'] . "','" . $bar['kodebudget'] . "','" . $bar['jumlah'] . "','" . $bar['rupiah'] . "','" . $bar['volume'] . "','" . $bar['rotasi'] . "','" . hidezerodecimal($bar['volume'] / $bar['rotasi'], 3) . "');\" ></td>";

							$tab .= "<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('" . $bar['kunci'] . "','sdm');\" title='Delete'></td>";
						}



						$tab .= "</tr>";
						$awal = ($no - $row) + 1;
					}

					$nott++;
					if ($param['blok'] != '') {
						$isi = "<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('" . $awal . "','" . $no . "','sdm');\">";
					} else {
						$isi = "<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('" . $awal . "','" . $no . "','sdm');\">";
					}
					$click = "onclick=\"showhide('" . $awal . "','" . $no . "','sdm');\"";
					$nmkas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $dtb[$tt][$kdbgt]['kas'] . "'");
					$tab .= "<tr class='rowcontent' " . $stytt . ">";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $nott . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $tt . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;' id=plussdm" . $awal . ">" . $isi . "</td>";
					$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmkode[$kdbgt] . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt]['acc'] . "</td>";
					$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . getNamaAkun($dtb[$tt][$kdbgt]['acc']) . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $bar['kegiatan'] . "</td>";
					$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmkeg[$bar['kegiatan']] . "</td>";
					$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt]['kas'] . " - " . $nmkas[$dtb[$tt][$kdbgt]['kas']] . "</td>";
					$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt]['rot'], 2) . "</td>";
					$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt]['vol'], 2) . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt]['satv'] . "</td>";
					$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt]['jlh'], 2) . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt]['satj'] . "</td>";
					$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt]['rp']) . "</td>";
					if ($param['jenis'] != 'excel' and $param['tipe'] != 'popup') {
						$tab .= "<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editsdm('','','" . $dtb[$tt][$kdbgt]['kas'] . "','" . $kdbgt . "','" . $dtb[$tt][$kdbgt]['jlh'] . "','" . $dtb[$tt][$kdbgt]['rp'] . "','" . $dtb[$tt][$kdbgt]['vol'] . "','" . $dtb[$tt][$kdbgt]['rot'] . "','" . hidezerodecimal($dtb[$tt][$kdbgt]['vol'] / $dtb[$tt][$kdbgt]['rot'], 3) . "');\" ></td>";

						$tab .= "<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('sdm','" . $param['tahun'] . "','" . $param['divisi'] . "','" . $tt . "','" . $kdbgt . "','" . $param['kegiatan'] . "','" . $dtb[$tt][$kdbgt]['acc'] . "','','');\" title='Delete'></td>";
					}

					$ttljlh += $dtb[$tt][$kdbgt]['jlh'];
					$ttlrp += $dtb[$tt][$kdbgt]['rp'];
					$tab .= "</tr>";
				}
			}

			$tab .= "<tr class='rowcontent' style='height:25px'>";
			$tab .= "<td style='text-align:center;background-color:#E5E8E8;' colspan=12>TOTAL</td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'>" . hidezerodecimal($ttljlh, 2) . "</td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'></td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'>" . hidezerodecimal($ttlrp, 0) . "</td>";
			if ($param['tipe'] != 'popup') {
				$tab .= "<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab .= "<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
			$tab .= "</tr>";
		}

		$tab .= "</tbody></table>";

		echo $tab;
		break;
	case 'loaddatamat':
		if ($param['tipe'] != 'popup') {
			// $tab.="<div class='table-scroll'>";
		}
		if ($param['tipe'] == 'popup') {
			$tab .= "<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportTableToExcel('loaddatamat');\" >";
		}
		$tab .= "<table id=loaddatamat class='sortable' cellspacing=1 cellpadding=5 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
					<th align=center>" . $_SESSION['lang']['blok'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodeanggaran'] . "</th>
					<th align=center style='width:55px'>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>
					<th align=center style='width:70px'>" . $_SESSION['lang']['kodekegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['namakegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['aruskas'] . "</th>
					<th align=center width=50px>" . $_SESSION['lang']['rotasi'] . "</th>
					<th align=center>" . $_SESSION['lang']['volume'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
					<th align=center>" . $_SESSION['lang']['namabarang'] . "</th>
					<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['rp'] . "</th>";
		if ($param['tipe'] != 'popup') {
			$tab .= "<th align=center colspan=2>Action</th>";
		}
		$tab .= "
				</tr>
			</thead><tbody>";

		$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');

		if ($param['blok'] != '') {
			$style = "";
			$stytt = "style=cursor:pointer;height:25px;display:none";
			$wh = " and a.kodeorg like '" . $param['blok'] . "%'";
		} else {
			$stytt = "style=cursor:pointer;height:25px";
			$style = "style=display:none";
			$wh = " and a.kodeorg like '" . $param['divisi'] . "%'";
		}
		if ($param['tt'] != '') {
			$wh .= " and b.thntnm = '" . $param['tt'] . "'";
		}
		if ($param['jenis'] != '') {
			$wh .= " and b.statusblok = '" . $param['jenis'] . "'";
		}
		if ($param['kegiatan'] != '') {
			$wh .= " and a.kegiatan = '" . $param['kegiatan'] . "'";
		}
		if ($param['noakun'] != '') {
			$wh .= " and noakun='" . $param['noakun'] . "'";
		}
		$wh .= " and a.tahunbudget = '" . $param['tahun'] . "'";
		$wh .= " and a.tipebudget = 'ESTATE' and a.kodebudget!='UMUM' and a.pta='BGT'";
		$wh .= " and substr(kodebudget,1,2)='M-'";
		$wh .= " and substr(a.kodeorg,1,4) = '" . $_SESSION['empl']['lokasitugas'] . "'";
		$wh .= " and substr(a.kodeorg,1,6) = '" . $param['divisi'] . "'";

		$nmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
		$data = array();
		$str = "select a.*,b.thntnm from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " order by kodeorg asc, kodebudget asc, kodebarang asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $bar['noakun'] . "'");
			$data[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']] = $bar['kodebarang'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['acc'] = $bar['noakun'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['kas'] = $bar['aruskas'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['satv'] = $bar['satuanv'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['satj'] = $bar['satuanj'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['rot'] = $bar['rotasi'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['vol'] += $bar['volume'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['jlh'] += $bar['jumlah'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['rp'] += $bar['rupiah'];
		}
		if (count($res) > 0) {
			$no = 0;
			foreach ($data as $tt => $vkdbgt) {
				foreach ($vkdbgt as $kdbgt => $vbrg) {
					foreach ($vbrg as $brg) {
						$str = "select a.*,b.thntnm from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " and kodebudget='" . $kdbgt . "' and kodebarang='" . $brg . "' order by kodeorg asc";
						$res = fetchdata($str);
						$row = 0;
						foreach ($res as $bar) {
							$nmkas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $bar['aruskas'] . "'");
							$row++;
							$no++;

							$tab .= "<tr class='rowcontent' id=mat_" . $no . " " . $style . ">";
							$tab .= "<td style='text-align:center;'></td>";
							$tab .= "<td style='text-align:center;'>" . $tt . "</td>";
							$tab .= "<td style='text-align:center;'>" . $nmorg[$bar['kodeorg']] . "</td>";
							$tab .= "<td style='text-align:left;'>" . $nmkode[$bar['kodebudget']] . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['noakun'] . "</td>";
							$tab .= "<td style='text-align:left;'>" . getNamaAkun($bar['noakun']) . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['kegiatan'] . "</td>";
							$tab .= "<td style='text-align:left;'>" . $nmkeg[$bar['kegiatan']] . "</td>";
							$tab .= "<td style='text-align:left;'>" . $bar['aruskas'] . " - " . $nmkas[$bar['aruskas']] . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['rotasi'], 2) . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['volume'], 2) . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['satuanv'] . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['kodebarang'] . "</td>";
							$tab .= "<td style='text-align:left;'>" . $nmbrg[$bar['kodebarang']] . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['jumlah'], 2) . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['satuanj'] . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['rupiah']) . "</td>";
							if ($param['jenis'] != 'excel'  and $param['tipe'] != 'popup') {
								$tab .= "<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editmat('" . $bar['kunci'] . "','" . $bar['kodeorg'] . "','" . $bar['aruskas'] . "','" . $bar['kodebudget'] . "','" . $bar['jumlah'] . "','" . $bar['rupiah'] . "','" . $bar['volume'] . "','" . $bar['rotasi'] . "','" . hidezerodecimal($bar['volume'] / $bar['rotasi'], 3) . "','" . $bar['kodebarang'] . "','" . $nmbrg[$bar['kodebarang']] . "','" . $bar['satuanj'] . "');\" ></td>";

								$tab .= "<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('" . $bar['kunci'] . "','mat');\" title='Delete'></td>";
							}



							$tab .= "</tr>";
							$awal = ($no - $row) + 1;
						}
						$nott++;
						if ($param['blok'] != '') {
							$isi = "<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('" . $awal . "','" . $no . "','mat');\">";
						} else {
							$isi = "<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('" . $awal . "','" . $no . "','mat');\">";
						}
						$click = "onclick=\"showhide('" . $awal . "','" . $no . "','mat');\"";
						$nmkas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $dtb[$tt][$kdbgt][$brg]['kas'] . "'");
						$tab .= "<tr class='rowcontent' " . $stytt . ">";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $nott . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $tt . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;' id=plusmat" . $awal . ">" . $isi . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmkode[$kdbgt] . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$brg]['acc'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . getNamaAkun($dtb[$tt][$kdbgt][$brg]['acc']) . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $bar['kegiatan'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmkeg[$bar['kegiatan']] . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$brg]['kas'] . " - " . $nmkas[$dtb[$tt][$kdbgt][$brg]['kas']] . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$brg]['rot'], 2) . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$brg]['vol'], 2) . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$brg]['satv'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $brg . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmbrg[$brg] . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$brg]['jlh'], 2) . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$brg]['satj'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$brg]['rp']) . "</td>";
						if ($param['jenis'] != 'excel' and $param['tipe'] != 'popup') {
							$tab .= "<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editmat('','','" . $dtb[$tt][$kdbgt][$brg]['kas'] . "','" . $kdbgt . "','" . $dtb[$tt][$kdbgt][$brg]['jlh'] . "','" . $dtb[$tt][$kdbgt][$brg]['rp'] . "','" . $dtb[$tt][$kdbgt][$brg]['vol'] . "','" . $dtb[$tt][$kdbgt][$brg]['rot'] . "','" . hidezerodecimal($dtb[$tt][$kdbgt][$brg]['vol'] / $dtb[$tt][$kdbgt][$brg]['rot'], 3) . "','" . $brg . "','" . $nmbrg[$brg] . "','" . $dtb[$tt][$kdbgt][$brg]['satj'] . "');\" ></td>";

							$tab .= "<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('mat','" . $param['tahun'] . "','" . $param['divisi'] . "','" . $tt . "','" . $kdbgt . "','" . $param['kegiatan'] . "','" . $dtb[$tt][$kdbgt][$brg]['acc'] . "','" . $brg . "','');\" title='Delete'></td>";
						}

						$ttljlh += $dtb[$tt][$kdbgt][$brg]['jlh'];
						$ttlrp += $dtb[$tt][$kdbgt][$brg]['rp'];
						$tab .= "</tr>";
					}
				}
			}

			$tab .= "<tr class='rowcontent' style='height:25px'>";
			$tab .= "<td style='text-align:center;background-color:#E5E8E8;' colspan=14>TOTAL</td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'>" . hidezerodecimal($ttljlh, 2) . "</td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'></td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'>" . hidezerodecimal($ttlrp, 0) . "</td>";
			if ($param['tipe'] != 'popup') {
				$tab .= "<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab .= "<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
		}

		$tab .= "</tbody></table></div>";

		echo $tab;
		break;
	case 'loaddataalat':
		if ($param['tipe'] != 'popup') {
			// $tab.="<div class='table-scroll'>";
		}
		if ($param['tipe'] == 'popup') {
			$tab .= "<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportTableToExcel('loaddataalt');\" >";
		}
		$tab .= "<table id=loaddataalt class='sortable' cellspacing=1 cellpadding=5 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
					<th align=center>" . $_SESSION['lang']['blok'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodeanggaran'] . "</th>
					<th align=center style='width:55px'>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>
					<th align=center style='width:70px'>" . $_SESSION['lang']['kodekegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['namakegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['aruskas'] . "</th>
					<th align=center width=50px>" . $_SESSION['lang']['rotasi'] . "</th>
					<th align=center>" . $_SESSION['lang']['volume'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
					<th align=center>" . $_SESSION['lang']['namabarang'] . "</th>
					<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['rp'] . "</th>";
		if ($param['tipe'] != 'popup') {
			$tab .= "<th align=center colspan=2>Action</th>";
		}
		$tab .= "</tr>
			</thead><tbody>";

		$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');

		if ($param['blok'] != '') {
			$style = "";
			$stytt = "style=cursor:pointer;height:25px;display:none";
			$wh = " and a.kodeorg like '" . $param['blok'] . "%'";
		} else {
			$stytt = "style=cursor:pointer;height:25px";
			$style = "style=display:none";
			$wh = " and a.kodeorg like '" . $param['divisi'] . "%'";
		}
		if ($param['tt'] != '') {
			$wh .= " and b.thntnm = '" . $param['tt'] . "'";
		}
		if ($param['jenis'] != '') {
			$wh .= " and b.statusblok = '" . $param['jenis'] . "'";
		}
		if ($param['kegiatan'] != '') {
			$wh .= " and a.kegiatan = '" . $param['kegiatan'] . "'";
		}
		if ($param['noakun'] != '') {
			$wh .= " and noakun='" . $param['noakun'] . "'";
		}
		$wh .= " and a.tahunbudget = '" . $param['tahun'] . "'";
		$wh .= " and a.tipebudget = 'ESTATE' and a.kodebudget!='UMUM' and a.pta='BGT'";
		$wh .= " and kodebudget='TOOL'";
		$wh .= " and substr(a.kodeorg,1,4) = '" . $_SESSION['empl']['lokasitugas'] . "'";
		$wh .= " and substr(a.kodeorg,1,6) = '" . $param['divisi'] . "'";

		$nmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
		$data = array();
		$str = "select a.*,b.thntnm from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " order by kodeorg asc, kodebarang asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $bar['noakun'] . "'");
			$data[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']] = $bar['kodebarang'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['acc'] = $bar['noakun'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['kas'] = $bar['aruskas'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['satv'] = $bar['satuanv'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['satj'] = $bar['satuanj'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['rot'] = $bar['rotasi'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['vol'] += $bar['volume'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['jlh'] += $bar['jumlah'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodebarang']]['rp'] += $bar['rupiah'];
		}
		if (count($res) > 0) {
			$no = 0;
			foreach ($data as $tt => $vkdbgt) {
				foreach ($vkdbgt as $kdbgt => $vbrg) {
					foreach ($vbrg as $brg) {
						$str = "select a.*,b.thntnm from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " and kodebudget='" . $kdbgt . "' and kodebarang='" . $brg . "' order by kodeorg asc";
						$res = fetchdata($str);
						$row = 0;
						foreach ($res as $bar) {
							$nmkas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $bar['aruskas'] . "'");
							$row++;
							$no++;

							$tab .= "<tr class='rowcontent' id=alat_" . $no . " " . $style . ">";
							$tab .= "<td style='text-align:center;'></td>";
							$tab .= "<td style='text-align:center;'>" . $tt . "</td>";
							$tab .= "<td style='text-align:center;'>" . $nmorg[$bar['kodeorg']] . "</td>";
							$tab .= "<td style='text-align:left;'>" . $nmkode[$bar['kodebudget']] . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['noakun'] . "</td>";
							$tab .= "<td style='text-align:left;'>" . getNamaAkun($bar['noakun']) . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['kegiatan'] . "</td>";
							$tab .= "<td style='text-align:left;'>" . $nmkeg[$bar['kegiatan']] . "</td>";
							$tab .= "<td style='text-align:left;'>" . $bar['aruskas'] . " - " . $nmkas[$bar['aruskas']] . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['rotasi'], 2) . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['volume'], 2) . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['satuanv'] . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['kodebarang'] . "</td>";
							$tab .= "<td style='text-align:left;'>" . $nmbrg[$bar['kodebarang']] . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['jumlah'], 2) . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['satuanj'] . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['rupiah']) . "</td>";
							if ($param['jenis'] != 'excel' and $param['tipe'] != 'popup') {
								$tab .= "<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editalat('" . $bar['kunci'] . "','" . $bar['kodeorg'] . "','" . $bar['aruskas'] . "','" . $bar['kodebudget'] . "','" . $bar['jumlah'] . "','" . $bar['rupiah'] . "','" . $bar['volume'] . "','" . $bar['rotasi'] . "','" . hidezerodecimal($bar['volume'] / $bar['rotasi'], 3) . "','" . $bar['kodebarang'] . "','" . $nmbrg[$bar['kodebarang']] . "','" . $bar['satuanj'] . "');\" ></td>";

								$tab .= "<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('" . $bar['kunci'] . "','alat');\" title='Delete'></td>";
							}



							$tab .= "</tr>";
							$awal = ($no - $row) + 1;
						}
						$nott++;
						if ($param['blok'] != '') {
							$isi = "<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('" . $awal . "','" . $no . "','alat');\">";
						} else {
							$isi = "<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('" . $awal . "','" . $no . "','alat');\">";
						}
						$click = "onclick=\"showhide('" . $awal . "','" . $no . "','alat');\"";
						$nmkas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $dtb[$tt][$kdbgt][$brg]['kas'] . "'");
						$tab .= "<tr class='rowcontent' " . $stytt . ">";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $nott . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $tt . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;' id=plusalat" . $awal . ">" . $isi . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmkode[$kdbgt] . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$brg]['acc'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . getNamaAkun($dtb[$tt][$kdbgt][$brg]['acc']) . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $bar['kegiatan'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmkeg[$bar['kegiatan']] . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$brg]['kas'] . " - " . $nmkas[$dtb[$tt][$kdbgt][$brg]['kas']] . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$brg]['rot'], 2) . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$brg]['vol'], 2) . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$brg]['satv'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $brg . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmbrg[$brg] . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$brg]['jlh'], 2) . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$brg]['satj'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$brg]['rp']) . "</td>";
						if ($param['jenis'] != 'excel' and $param['tipe'] != 'popup') {
							$tab .= "<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editalat('','','" . $dtb[$tt][$kdbgt][$brg]['kas'] . "','" . $kdbgt . "','" . $dtb[$tt][$kdbgt][$brg]['jlh'] . "','" . $dtb[$tt][$kdbgt][$brg]['rp'] . "','" . $dtb[$tt][$kdbgt][$brg]['vol'] . "','" . $dtb[$tt][$kdbgt][$brg]['rot'] . "','" . hidezerodecimal($dtb[$tt][$kdbgt][$brg]['vol'] / $dtb[$tt][$kdbgt][$brg]['rot'], 3) . "','" . $brg . "','" . $nmbrg[$brg] . "','" . $dtb[$tt][$kdbgt][$brg]['satj'] . "');\" ></td>";

							$tab .= "<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('alat','" . $param['tahun'] . "','" . $param['divisi'] . "','" . $tt . "','" . $kdbgt . "','" . $param['kegiatan'] . "','" . $dtb[$tt][$kdbgt][$brg]['acc'] . "','" . $brg . "','');\" title='Delete'></td>";
						}

						$ttljlh += $dtb[$tt][$kdbgt][$brg]['jlh'];
						$ttlrp += $dtb[$tt][$kdbgt][$brg]['rp'];
						$tab .= "</tr>";
					}
				}
			}

			$tab .= "<tr class='rowcontent' style='height:25px'>";
			$tab .= "<td style='text-align:center;background-color:#E5E8E8;' colspan=14>TOTAL</td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'>" . hidezerodecimal($ttljlh, 2) . "</td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'></td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'>" . hidezerodecimal($ttlrp, 0) . "</td>";
			if ($param['tipe'] != 'popup') {
				$tab .= "<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab .= "<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
		}

		$tab .= "</tbody></table></div>";

		echo $tab;
		break;
	case 'loaddatakont':
		if ($param['tipe'] != 'popup') {
			// $tab.="<div class='table-scroll'>";
		}
		if ($param['tipe'] == 'popup') {
			$tab .= "<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportTableToExcel('loaddatakon');\" >";
		}
		$tab .= "<table id=loaddatakon class='sortable' cellspacing=1 cellpadding=5 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
					<th align=center>" . $_SESSION['lang']['blok'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodeanggaran'] . "</th>
					<th align=center style='width:55px'>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>
					<th align=center style='width:70px'>" . $_SESSION['lang']['kodekegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['namakegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['aruskas'] . "</th>
					<th align=center width=50px>" . $_SESSION['lang']['rotasi'] . "</th>
					<th align=center>" . $_SESSION['lang']['volume'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['rp'] . "</th>";
		if ($param['tipe'] != 'popup') {
			$tab .= "<th align=center colspan=2>Action</th>";
		}
		$tab .= "</tr>
			</thead><tbody>";

		$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');

		if ($param['blok'] != '') {
			$style = "";
			$stytt = "style=cursor:pointer;height:25px;display:none";
			$wh = " and a.kodeorg like '" . $param['blok'] . "%'";
		} else {
			$stytt = "style=cursor:pointer;height:25px";
			$style = "style=display:none";
			$wh = " and a.kodeorg like '" . $param['divisi'] . "%'";
		}
		if ($param['tt'] != '') {
			$wh .= " and b.thntnm = '" . $param['tt'] . "'";
		}
		if ($param['jenis'] != '') {
			$wh .= " and b.statusblok = '" . $param['jenis'] . "'";
		}
		if ($param['kegiatan'] != '') {
			$wh .= " and a.kegiatan = '" . $param['kegiatan'] . "'";
		}
		if ($param['noakun'] != '') {
			$wh .= " and noakun='" . $param['noakun'] . "'";
		}
		$wh .= " and a.tahunbudget = '" . $param['tahun'] . "'";
		$wh .= " and a.tipebudget = 'ESTATE' and a.kodebudget!='UMUM' and a.pta='BGT'";
		$wh .= " and kodebudget = 'KONTRAK'";
		$wh .= " and substr(a.kodeorg,1,4) = '" . $_SESSION['empl']['lokasitugas'] . "'";
		$wh .= " and substr(a.kodeorg,1,6) = '" . $param['divisi'] . "'";

		$data = array();
		$str = "select a.*,b.thntnm from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " order by kodeorg asc, kodebudget asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $bar['noakun'] . "'");
			$data[$bar['thntnm']][$bar['kodebudget']] = $bar['kodebudget'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['kas'] = $bar['aruskas'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['satv'] = $bar['satuanv'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['satj'] = $bar['satuanj'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['rot'] = $bar['rotasi'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['vol'] += $bar['volume'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['jlh'] += $bar['jumlah'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['rp'] += $bar['rupiah'];
			$dtb[$bar['thntnm']][$bar['kodebudget']]['acc'] = $bar['noakun'];
		}
		if (count($res) > 0) {
			$no = 0;
			foreach ($data as $tt => $vkdbgt) {
				foreach ($vkdbgt as $kdbgt) {
					$str = "select a.*,b.thntnm from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " and kodebudget='" . $kdbgt . "'order by kodeorg asc";
					$res = fetchdata($str);
					$row = 0;
					foreach ($res as $bar) {
						$nmkas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $bar['aruskas'] . "'");
						$row++;
						$no++;

						$tab .= "<tr class='rowcontent' id=kont_" . $no . " " . $style . ">";
						$tab .= "<td style='text-align:center;'></td>";
						$tab .= "<td style='text-align:center;'>" . $tt . "</td>";
						$tab .= "<td style='text-align:center;'>" . $nmorg[$bar['kodeorg']] . "</td>";
						$tab .= "<td style='text-align:left;'>" . $nmkode[$bar['kodebudget']] . "</td>";
						$tab .= "<td style='text-align:center;'>" . $bar['noakun'] . "</td>";
						$tab .= "<td style='text-align:left;'>" . getNamaAkun($bar['noakun']) . "</td>";
						$tab .= "<td style='text-align:center;'>" . $bar['kegiatan'] . "</td>";
						$tab .= "<td style='text-align:left;'>" . $nmkeg[$bar['kegiatan']] . "</td>";
						$tab .= "<td style='text-align:left;'>" . $bar['aruskas'] . " - " . $nmkas[$bar['aruskas']] . "</td>";
						$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['rotasi'], 2) . "</td>";
						$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['volume'], 2) . "</td>";
						$tab .= "<td style='text-align:center;'>" . $bar['satuanv'] . "</td>";
						$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['jumlah'], 2) . "</td>";
						$tab .= "<td style='text-align:center;'>" . $bar['satuanj'] . "</td>";
						$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['rupiah']) . "</td>";
						if ($param['jenis'] != 'excel' and $param['tipe'] != 'popup') {
							$tab .= "<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editkont('" . $bar['kunci'] . "','" . $bar['kodeorg'] . "','" . $bar['aruskas'] . "','" . $bar['kodebudget'] . "','" . $bar['jumlah'] . "','" . $bar['rupiah'] . "','" . $bar['volume'] . "','" . $bar['rotasi'] . "','" . hidezerodecimal($bar['volume'] / $bar['rotasi'], 3) . "');\" ></td>";

							$tab .= "<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('" . $bar['kunci'] . "','kont');\" title='Delete'></td>";
						}



						$tab .= "</tr>";
						$awal = ($no - $row) + 1;
					}

					$nott++;
					if ($param['blok'] != '') {
						$isi = "<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('" . $awal . "','" . $no . "','kont');\">";
					} else {
						$isi = "<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('" . $awal . "','" . $no . "','kont');\">";
					}
					$click = "onclick=\"showhide('" . $awal . "','" . $no . "','kont');\"";
					$nmkas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $dtb[$tt][$kdbgt]['kas'] . "'");
					$tab .= "<tr class='rowcontent' " . $stytt . ">";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $nott . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $tt . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;' id=pluskont" . $awal . ">" . $isi . "</td>";
					$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmkode[$kdbgt] . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt]['acc'] . "</td>";
					$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . getNamaAkun($dtb[$tt][$kdbgt]['acc']) . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $bar['kegiatan'] . "</td>";
					$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmkeg[$bar['kegiatan']] . "</td>";
					$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt]['kas'] . " - " . $nmkas[$dtb[$tt][$kdbgt]['kas']] . "</td>";
					$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt]['rot'], 2) . "</td>";
					$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt]['vol'], 2) . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt]['satv'] . "</td>";
					$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt]['jlh'], 2) . "</td>";
					$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt]['satj'] . "</td>";
					$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt]['rp']) . "</td>";
					if ($param['jenis'] != 'excel' and $param['tipe'] != 'popup') {
						$tab .= "<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editkont('','','" . $dtb[$tt][$kdbgt]['kas'] . "','" . $kdbgt . "','" . $dtb[$tt][$kdbgt]['jlh'] . "','" . $dtb[$tt][$kdbgt]['rp'] . "','" . $dtb[$tt][$kdbgt]['vol'] . "','" . $dtb[$tt][$kdbgt]['rot'] . "','" . hidezerodecimal($dtb[$tt][$kdbgt]['vol'] / $dtb[$tt][$kdbgt]['rot'], 3) . "');\" ></td>";

						$tab .= "<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('kont','" . $param['tahun'] . "','" . $param['divisi'] . "','" . $tt . "','" . $kdbgt . "','" . $param['kegiatan'] . "','" . $dtb[$tt][$kdbgt]['acc'] . "','','');\" title='Delete'></td>";
					}

					$ttljlh += $dtb[$tt][$kdbgt]['jlh'];
					$ttlrp += $dtb[$tt][$kdbgt]['rp'];
					$tab .= "</tr>";
				}
			}

			$tab .= "<tr class='rowcontent' style='height:25px'>";
			$tab .= "<td style='text-align:center;background-color:#E5E8E8;' colspan=12>TOTAL</td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'>" . hidezerodecimal($ttljlh, 2) . "</td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'></td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'>" . hidezerodecimal($ttlrp, 0) . "</td>";
			if ($param['tipe'] != 'popup') {
				$tab .= "<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab .= "<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
		}

		$tab .= "</tbody></table></div>";

		echo $tab;
		break;
	case 'loaddatavhc':
		if ($param['tipe'] != 'popup') {
			// $tab.="<div class='table-scroll'>";
		}
		if ($param['tipe'] == 'popup') {
			$tab .= "<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportTableToExcel('loaddatavhc');\" >";
		}
		$tab .= "<table id=loaddatavhc class='sortable' cellspacing=1 cellpadding=5 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
					<th align=center>" . $_SESSION['lang']['blok'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodeanggaran'] . "</th>
					<th align=center style='width:55px'>" . $_SESSION['lang']['noakun'] . "</th>
					<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>
					<th align=center style='width:70px'>" . $_SESSION['lang']['kodekegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['namakegiatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['aruskas'] . "</th>
					<th align=center width=50px>" . $_SESSION['lang']['rotasi'] . "</th>
					<th align=center>" . $_SESSION['lang']['volume'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodevhc'] . "</th>
					<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['rp'] . "</th>";
		if ($param['tipe'] != 'popup') {
			$tab .= "<th align=center colspan=2>Action</th>";
		}
		$tab .= "</tr>
			</thead><tbody>";

		$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');

		if ($param['blok'] != '') {
			$style = "";
			$stytt = "style=cursor:pointer;height:25px;display:none";
			$wh = " and a.kodeorg like '" . $param['blok'] . "%'";
		} else {
			$stytt = "style=cursor:pointer;height:25px";
			$style = "style=display:none";
			$wh = " and a.kodeorg like '" . $param['divisi'] . "%'";
		}
		if ($param['tt'] != '') {
			$wh .= " and b.thntnm = '" . $param['tt'] . "'";
		}
		if ($param['jenis'] != '') {
			$wh .= " and b.statusblok = '" . $param['jenis'] . "'";
		}
		if ($param['kegiatan'] != '') {
			$wh .= " and a.kegiatan = '" . $param['kegiatan'] . "'";
		}
		if ($param['noakun'] != '') {
			$wh .= " and noakun='" . $param['noakun'] . "'";
		}

		$wh .= " and a.tahunbudget = '" . $param['tahun'] . "'";
		$wh .= " and a.tipebudget = 'ESTATE' and a.kodebudget!='UMUM' and a.pta='BGT'";
		$wh .= " and kodebudget='VHC'";
		$wh .= " and substr(a.kodeorg,1,4) = '" . $_SESSION['empl']['lokasitugas'] . "'";
		$wh .= " and substr(a.kodeorg,1,6) = '" . $param['divisi'] . "'";

		$optnopol = makeOption($dbname, 'vhc_5master', 'kodevhc,nopol');

		$data = array();
		$str = "select a.*,b.thntnm from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " order by kodeorg asc, kodebudget asc, kodevhc asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $bar['noakun'] . "'");
			$data[$bar['thntnm']][$bar['kodebudget']][$bar['kodevhc']] = $bar['kodevhc'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodevhc']]['acc'] = $bar['noakun'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodevhc']]['kas'] = $bar['aruskas'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodevhc']]['satv'] = $bar['satuanv'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodevhc']]['satj'] = $bar['satuanj'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodevhc']]['rot'] = $bar['rotasi'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodevhc']]['vol'] += $bar['volume'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodevhc']]['jlh'] += $bar['jumlah'];
			$dtb[$bar['thntnm']][$bar['kodebudget']][$bar['kodevhc']]['rp'] += $bar['rupiah'];
		}
		if (count($res) > 0) {
			$no = 0;
			foreach ($data as $tt => $vkdbgt) {
				foreach ($vkdbgt as $kdbgt => $vvhc) {
					foreach ($vvhc as $vhc) {
						$str = "select a.*,b.thntnm from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 " . $wh . " and kodebudget='" . $kdbgt . "' and kodevhc='" . $vhc . "' order by kodeorg asc";
						$res = fetchdata($str);
						$row = 0;
						foreach ($res as $bar) {
							$nmkas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $bar['aruskas'] . "'");
							$row++;
							$no++;

							$nopol = "";
							if ($optnopol[$bar['kodevhc']] != '') {
								$nopol = " - " . $optnopol[$bar['kodevhc']];
							}
							$tab .= "<tr class='rowcontent' id=vhc_" . $no . " " . $style . ">";
							$tab .= "<td style='text-align:center;'></td>";
							$tab .= "<td style='text-align:center;'>" . $tt . "</td>";
							$tab .= "<td style='text-align:center;'>" . $nmorg[$bar['kodeorg']] . "</td>";
							$tab .= "<td style='text-align:left;'>" . $nmkode[$bar['kodebudget']] . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['noakun'] . "</td>";
							$tab .= "<td style='text-align:left;'>" . getNamaAkun($bar['noakun']) . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['kegiatan'] . "</td>";
							$tab .= "<td style='text-align:left;'>" . getNamaKeg($bar['kegiatan']) . "</td>";
							$tab .= "<td style='text-align:left;'>" . $bar['aruskas'] . " - " . $nmkas[$bar['aruskas']] . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['rotasi'], 2) . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['volume'], 2) . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['satuanv'] . "</td>";
							$tab .= "<td style='text-align:left;'>" . $bar['kodevhc'] . "" . $nopol . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['jumlah'], 2) . "</td>";
							$tab .= "<td style='text-align:center;'>" . $bar['satuanj'] . "</td>";
							$tab .= "<td style='text-align:right;'>" . hidezerodecimal($bar['rupiah']) . "</td>";
							if ($param['jenis'] != 'excel' and $param['tipe'] != 'popup') {
								$tab .= "<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editvhc('" . $bar['kunci'] . "','" . $bar['kodeorg'] . "','" . $bar['aruskas'] . "','" . $bar['kodebudget'] . "','" . $bar['jumlah'] . "','" . $bar['rupiah'] . "','" . $bar['volume'] . "','" . $bar['rotasi'] . "','" . hidezerodecimal($bar['volume'] / $bar['rotasi'], 3) . "','" . $bar['kodevhc'] . "','" . $bar['satuanj'] . "');\" ></td>";

								$tab .= "<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('" . $bar['kunci'] . "','vhc');\" title='Delete'></td>";
							}
							$tab .= "</tr>";
							$awal = ($no - $row) + 1;
						}

						$nopol = "";
						if ($optnopol[$vhc] != '') {
							$nopol = " - " . $optnopol[$vhc];
						}
						$nott++;
						if ($param['blok'] != '') {
							$isi = "<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('" . $awal . "','" . $no . "','vhc');\">";
						} else {
							$isi = "<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('" . $awal . "','" . $no . "','vhc');\">";
						}
						$click = "onclick=\"showhide('" . $awal . "','" . $no . "','vhc');\"";
						$nmkas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $dtb[$tt][$kdbgt][$vhc]['kas'] . "'");
						$tab .= "<tr class='rowcontent' " . $stytt . ">";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $nott . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $tt . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;' id=plusvhc" . $awal . ">" . $isi . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmkode[$kdbgt] . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$vhc]['acc'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . getNamaAkun($dtb[$tt][$kdbgt][$vhc]['acc']) . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $bar['kegiatan'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $nmkeg[$bar['kegiatan']] . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$vhc]['kas'] . " - " . $nmkas[$dtb[$tt][$kdbgt][$vhc]['kas']] . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$vhc]['rot'], 2) . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$vhc]['vol'], 2) . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$vhc]['satv'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:left;background-color:#CAFFF4;'>" . $vhc . "" . $nopol . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$vhc]['jlh'], 2) . "</td>";
						$tab .= "<td " . $click . " style='text-align:center;background-color:#CAFFF4;'>" . $dtb[$tt][$kdbgt][$vhc]['satj'] . "</td>";
						$tab .= "<td " . $click . " style='text-align:right;background-color:#CAFFF4;'>" . hidezerodecimal($dtb[$tt][$kdbgt][$vhc]['rp']) . "</td>";
						if ($param['jenis'] != 'excel' and $param['tipe'] != 'popup') {
							$tab .= "<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editvhc('','','" . $dtb[$tt][$kdbgt][$vhc]['kas'] . "','" . $kdbgt . "','" . $dtb[$tt][$kdbgt][$vhc]['jlh'] . "','" . $dtb[$tt][$kdbgt][$vhc]['rp'] . "','" . $dtb[$tt][$kdbgt][$vhc]['vol'] . "','" . $dtb[$tt][$kdbgt][$vhc]['rot'] . "','" . hidezerodecimal($dtb[$tt][$kdbgt][$vhc]['vol'] / $dtb[$tt][$kdbgt][$vhc]['rot'], 3) . "','" . $vhc . "','" . $dtb[$tt][$kdbgt][$vhc]['satj'] . "');\" ></td>";

							$tab .= "<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('vhc','" . $param['tahun'] . "','" . $param['divisi'] . "','" . $tt . "','" . $kdbgt . "','" . $param['kegiatan'] . "','" . $dtb[$tt][$kdbgt][$vhc]['acc'] . "','','" . $vhc . "');\" title='Delete'></td>";
						}

						$ttljlh += $dtb[$tt][$kdbgt][$vhc]['jlh'];
						$ttlrp += $dtb[$tt][$kdbgt][$vhc]['rp'];
						$tab .= "</tr>";
					}
				}
			}

			$tab .= "<tr class='rowcontent' style='height:25px'>";
			$tab .= "<td style='text-align:center;background-color:#E5E8E8;' colspan=13>TOTAL</td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'>" . hidezerodecimal($ttljlh, 2) . "</td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'></td>";
			$tab .= "<td style='text-align:right;background-color:#E5E8E8;'>" . hidezerodecimal($ttlrp, 0) . "</td>";
			if ($param['tipe'] != 'popup') {
				$tab .= "<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab .= "<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
		}

		$tab .= "</tbody></table></div>";

		echo $tab;
		break;
	case 'delbyindex':
		try {
			$owlPDO->beginTransaction();

			$str = "delete from " . $dbname . ".bgt_budget  where kunci='" . $param['index'] . "'";
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'deldetail':
		try {
			$owlPDO->beginTransaction();

			$whr = $wh = "";
			$whr .= " and `tahunbudget` = '" . $param['tahun'] . "'";
			$whr .= " and `kodebudget` = '" . $param['kdbudget'] . "'";
			$whr .= " and `kodeorg` like '" . $param['divisi'] . "%'";
			$whr .= " and `kegiatan` = '" . $param['kegiatan'] . "'";
			if ($param['kodebarang'] != '') {
				$whr .= " and `kodebarang` = '" . $param['kodebarang'] . "'";
			}
			if ($param['kodevhc'] != '') {
				$whr .= " and `kodevhc` = '" . $param['kodevhc'] . "'";
			}
			if ($param['noakun'] != '') {
				$whr .= " and `noakun` = '" . $param['noakun'] . "'";
			}


			$wh .= " and `kodeblok` like '" . $param['divisi'] . "%'";
			$wh .= " and `tahunbudget` = '" . $param['tahun'] . "'";

			$str = "select * from " . $dbname . ".bgt_budget where 1=1 and `tipebudget` = '" . $tipebudget . "' " . $whr . "  and kodeorg in (select kodeblok from " . $dbname . ".bgt_blok where 1=1 and thntnm='" . $param['tt'] . "' " . $wh . ")";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$str = "delete from " . $dbname . ".bgt_distribusi where kunci='" . $bar['kunci'] . "'";
				$owlPDO->exec($str);

				$str = "delete from " . $dbname . ".bgt_budget where kunci='" . $bar['kunci'] . "'";
				$owlPDO->exec($str);
			}


			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
}
function cekheader($param)
{
	global $param;
	global $dbname;

	if ($param['tahun'] == '') {
		exit("Warning : Tahun budget wajib diisi.");
	}
	if (strlen($param['tahun']) < 4) {
		exit("Warning : Tahun budget salah.");
	}
	if ($param['kodeorg'] == '') {
		exit("Warning : Kode organisasi wajib diisi.");
	}
	if ($param['divisi'] == '') {
		exit("Warning : Divisi wajib diisi.");
	}
	if ($param['jenis'] == '') {
		exit("Warning : Jenis wajib diisi.");
	}
	if ($param['kegiatan'] == '') {
		exit("Warning : Kegiatan wajib diisi.");
	}
	if ($param['volume'] == '') {
		exit("Warning : Fisik wajib diisi.");
	}
	if ($param['rotasi'] == '') {
		exit("Warning : Rotasi wajib diisi.");
	}
	if ($param['totalvolume'] == '') {
		exit("Warning : Total wajib diisi.");
	}

	$whr = " and kodeorg like '" . $param['kodeorg'] . "%' and tipebudget='ESTATE' and tahunbudget='" . $param['tahun'] . "' and pta='BGT' and kodebudget!='UMUM'";
	$str = "select * from " . $dbname . ".bgt_budget where 1=1 " . $whr . " and tutup='1'"; #exit("error".$str);
	$res = fetchdata($str);
	if (count($res) > 0) {
		exit("Warning : Budget sudah ditutup.");
	}
}
function createpagingsebar($jlhbrs, $limit, $page, $colspan, $loaddata, $getpage)
{
	global $dbname;
	global $owlPDO;

	$tab = "";
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
	$tab .= "<tfoot><tr>
		<td colspan=" . $colspan . " align=center>
			" . $frompage . " to " . $topage . " Of " .  $jlhbrs . "
		</td>
	</tr>
	<tr>
		<td colspan=" . $colspan . " align=center>";
	if ($page == '0') {
		$tab .= "";
	} else {
		$tab .= "<button class=mybutton onclick=$loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
	}
	$tab .= "<select id=\"pagessbr\" name=\"pagessbr\" style=\"min-width:20px\" onchange=\"$getpage()\">" . $isiRow . "</select>";

	if (($page + 1) == $totrows) {
		$tab .= "";
	} else {
		$tab .= "<button class=mybutton onclick=$loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
	}
	$tab .= "</td>
	</tr>
	</tfoot>";

	return $tab;
}
