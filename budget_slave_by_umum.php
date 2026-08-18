<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$cekapa    = checkPostGet('cekapa', '');
$akunsrc   = checkPostGet('akun', '');
$ketsrc    = checkPostGet('ket', '');
$sebaransrc = checkPostGet('sebaran', '');
$method    = checkPostGet('method', '');
$aruskas   = checkPostGet('aruskas', '');
$tipebudget = checkPostGet('tipebudget', '');
$id        = checkPostGet('id', '');
$param     = $_POST;
switch ($method) {
	case 'getnoakun':
		$tipebgtmapping = array(
			'BULKING' => 'BULKING',
			'ESTATE' => 'KEBUN',
			'HOLDING' => 'HOLDING',
			'KANWIL' => 'KANWIL',
			'MILL' => 'PABRIK',
			'RND' => 'RND',
			'TC' => 'TC',
			'TRK' => 'TRAKSI',
			'WS' => 'WORKSHOP'
		);

		$str = "select * from " . $dbname . ".sdm_5departemen";
		$res = fetchData($str);
		foreach ($res as $bar) {
			$datadept[$bar['kode']] = $bar['nama'];
		}


		$where = "";
		if ($_SESSION['empl']['tipelokasitugas'] == 'KEBUN') {
			$where .= " and (noakun like '7%' or noakun like '9%')";
		}
		if ($_SESSION['empl']['tipelokasitugas'] == 'PABRIK') {
			$where .= " and (noakun like '7%' or noakun like '9%')";
		}
		if ($_SESSION['empl']['tipelokasitugas'] == 'TC') {
			$where .= " and (noakun like '82%' or noakun like '9%')";
		}
		if ($_SESSION['empl']['tipelokasitugas'] == 'RND') {
			$where .= " and (noakun like '82%' or noakun like '9%')";
		}
		if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
			$where .= " and (noakun like '82%' or noakun like '9%')";
		}
		if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
			$where .= " and (noakun like '82%' or noakun like '9%')";
		}
		if ($_SESSION['empl']['tipelokasitugas'] == 'BULKING') {
			$where .= " and (noakun like '81%' or noakun like '9%')";
		}
		$where .= " and aktif='1' and level='5'";
		$where .= " and noakun in (select noakun from " . $dbname . ".keu_5akun_detail where tipeorg='" . $_SESSION['empl']['tipelokasitugas'] . "' and dept='" . $param['dept'] . "')";

		$optakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select * from " . $dbname . ".keu_5akun where 1=1 and aktif='1' and level='5' " . $where . " order by noakun";
		$res = fetchdata($str);
		if (count($res) == 0) {
			exit("error: Nomor akun untuk departemen " . $datadept[$param['dept']] . " dan tipe organisasi " . $_SESSION['empl']['tipelokasitugas'] . " belum disetting.");
		}
		foreach ($res as $val) {
			// $d=substr($val['noakun'],0,3);
			// if($d!=$n){			
			// 	$optakun.="<optgroup label='".getNamaAkun($d)."'>";
			// }
			$optakun .= "<option value=" . $val['noakun'] . " " . $b . ">" . $val['noakun'] . " - " . $val['namaakun'] . "</option>";
			// $n=$d;
			// if($d!=$n){			
			// 	$optakun.="</optgroup>";
			// }
		}

		echo $optakun;
		break;
	case 'posting':
		try {
			$owlPDO->beginTransaction();

			$where = " and tipebudget = '" . $tipebudget . "' and kodebudget ='UMUM' and pta='BGT'";

			$str = "select * from " . $dbname . ".bgt_budget where 1=1 " . $where . " and tahunbudget='" . $param['tahun'] . "' and kodeorg like '" . $param['kodeorg'] . "%' and kunci not in (select kunci from " . $dbname . ".bgt_distribusi)";
			$res = fetchdata($str);
			if (count($res) > 0) {
				throw new PDOException("Masih ada data yang belum di sebarkan.");
			}

			$str = "update " . $dbname . ".bgt_budget set tutup='1' where 1=1 " . $where . " and tahunbudget='" . $param['tahun'] . "' and kodeorg like '" . $param['kodeorg'] . "%'"; #exit("error".$str);
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

			$where = " and tipebudget = '" . $tipebudget . "' and kodebudget ='UMUM' and pta='BGT'";
			$str = "update " . $dbname . ".bgt_budget set tutup='0' where 1=1 " . $where . " and tahunbudget='" . $param['tahun'] . "' and kodeorg like '" . $param['kodeorg'] . "%'"; #exit("error".$str);
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;

	case 'showposting':
		$jab = getPostingJabatan('budget');
		$where = $wh = $whr = "";
		if ($param['tahun'] != '') {
			$where .= " and a.tahunbudget = '" . $param['tahun'] . "'";
			$wh .= " and tahunbudget = '" . $param['tahun'] . "'";
			$whr .= " and tahunbudget = '" . $param['tahun'] . "'";
		}
		if ($param['kodeorg'] != '') {
			$where .= " and a.kodeorg like '" . $param['kodeorg'] . "%'";
			$wh .= " and kodeunit = '" . $param['kodeorg'] . "'";
			$whr .= " and millcode = '" . $param['kodeorg'] . "'";
		}
		$where .= " and a.kodebudget='UMUM' and a.pta='BGT'";
		$tab = "<table id=pvtTable cellpadding=8 cellspacing=1 border=0 class='sortable'>
			<thead>
				<tr class=rowheader style=height:25px>
					<th align=center width=30px>No.</th>
					<th align=centers style='width:50px'>" . $_SESSION['lang']['budgetyear'] . "</th>
					<th align=center>" . $_SESSION['lang']['kodeorg'] . "</th>
					<th align=center>Tipe Budget</th>
					<th align=center>" . $_SESSION['lang']['luas'] . "</th>
					<th align=center>" . $_SESSION['lang']['produksi'] . "<br>(Kg)</th>
					<th align=center>" . $_SESSION['lang']['total'] . "</th>
					<th align=center>Rp / Ha</th>
					<th align=center>Rp / Kg</th>
					<th width=30px align=center>Action</th>
				</tr>
			</thead>
			<tbody>";
		$colspan = 11;

		$str = "select tahunbudget, substr(kodeblok,1,4) as kodeorg, sum(hathnini) as hathnini from " . $dbname . ".bgt_blok group by tahunbudget, substr(kodeblok,1,4)";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$luas[$bar['tahunbudget']][$bar['kodeorg']]['ESTATE'] += $bar['hathnini'];
		}

		$str = "select sum(totalkg) as totalkg, tahunbudget, kodeunit from " . $dbname . ".bgt_produksi_kebun where substr(kodeunit,1,4) in (" . getOrgDetail(2) . ") " . $wh . " group by tahunbudget, substr(kodeunit,1,4)";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$produksi[$bar['tahunbudget']][$bar['kodeunit']] += $bar['totalkg'];
		}

		$str = "select sum(kgolah) as totalkg, tahunbudget, millcode from " . $dbname . ".bgt_produksi_pks where substr(millcode,1,4) in (" . getOrgDetail(2) . ") " . $whr . " group by tahunbudget, substr(millcode,1,4)";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$produksi[$bar['tahunbudget']][$bar['millcode']] += $bar['totalkg'];
		}

		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$str = "select a.*,substr(kodeorg,1,4) as unit, sum(a.rupiah) as rupiah from " . $dbname . ".bgt_budget a where substr(a.kodeorg,1,4) in (" . getOrgDetail(2) . ") " . $where . " group by a.tahunbudget, substr(kodeorg,1,4), tipebudget order by a.tahunbudget desc,a.kodeorg asc";
		$res = fetchdata($str);
		if (count($res) > 0) {
			foreach ($res as $bar) {
				$no++;
				$tab .= "<tr class='rowcontent'  style=height:35px>";
				$tab .= "<td style='text-align:center'>" . $no . "</td>";
				$tab .= "<td align=center>" . $bar['tahunbudget'] . "</td>";
				$tab .= "<td align=left>" . $bar['unit'] . " - " . $nmorg[$bar['unit']] . "</td>";
				$tab .= "<td align=center>" . $bar['tipebudget'] . "</td>";
				$tab .= "<td style='text-align:right;vertical-align:top;'>" . @hidezerodecimal($luas[$bar['tahunbudget']][$bar['unit']][$bar['tipebudget']]) . "</td>";
				$tab .= "<td style='text-align:right;vertical-align:top;'>" . @hidezerodecimal($produksi[$bar['tahunbudget']][$bar['unit']]) . "</td>";
				$tab .= "<td style='text-align:right;vertical-align:top;'>" . @hidezerodecimal($bar['rupiah']) . "</td>";
				$tab .= "<td style='text-align:right;vertical-align:top;'>" . @hidezerodecimal(fixnan($bar['rupiah'] / $luas[$bar['tahunbudget']][$bar['unit']][$bar['tipebudget']]), 2) . "</td>";
				$tab .= "<td style='text-align:right;vertical-align:top;'>" . @hidezerodecimal(fixnan($bar['rupiah'] / $produksi[$bar['tahunbudget']][$bar['unit']]), 2) . "</td>";

				if ($bar['tutup'] == 0) {
					$tab .= "<td align=center width=25px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('" . $bar['tahunbudget'] . "','" . $bar['unit'] . "','" . $bar['tipebudget'] . "');\" title='Posting'></td>";
				} else {
					if (in_array($_SESSION['empl']['jabatan'], $jab)) {
						$icon = "images/icons/04/16/04.png";
						$title = "Unclose / Unposting";
						$unpost = " onclick=\"unposting('" . $bar['tahunbudget'] . "','" . $bar['unit'] . "','" . $bar['tipebudget'] . "');\" ";
					} else {
						$icon = "images/icons/04/16/02.png";
						$title = "Closed / Posted";
						$unpost = '';
					}
					$tab .= "<td align=center width=25px><img src=" . $icon . " class=zImgBtn class=zImgBtn title='" . $title . "' " . $unpost . " ></td>";
				}

				$tab .= "</tr>";
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
		if ($param['dept'] != '') {
			$where .= " and a.dept like '" . $param['dept'] . "%'";
		}
		if ($param['aruskas'] != '') {
			$where .= " and a.aruskas like  '%" . $param['aruskas'] . "%'";
		}

		if ($param['ket'] != '') {
			$where .= " and a.keterangan like  '%" . $param['ket'] . "%'";
		}
		if ($param['sebaran'] == '1') {
			$where .= " and b.kunci IS NOT NULL";
		}
		if ($param['sebaran'] == '2') {
			$where .= " and b.kunci IS NULL";
		}

		if ($param['noakunsch'] != '') {
			$where .= " and noakun in (select noakun from " . $dbname . ".keu_5akun where noakun like '%" . $param['noakunsch'] . "%' or namaakun like '%" . $param['noakunsch'] . "%')";
		}

		$where .= " and a.kodebudget='UMUM' and a.pta='BGT'";
		$where .= " and substr(a.kodeorg,1,4)='" . $_SESSION['empl']['lokasitugas'] . "'";

		if ($param['jlhbaris'] > '5000') {
			exit("Warning : Jumlah baris maksimal 5000");
		}

		if ($param['jlhbaris'] == '' or $param['jlhbaris'] == '0') {
			$limit = 50;
		} else {
			$limit = $param['jlhbaris'];
		}

		if ($param['tampilkan'] == '1') {
			$group = "group by a.tahunbudget, a.aruskas";
		} elseif ($param['tampilkan'] == '2') {
			$group = "group by a.tahunbudget,dept";
		} else {
			$group = "group by a.kunci";
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
			$colspan   = 20;
		} else {
			$colspan   = 20;
		}

		$sql = "select count(*) from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_distribusi b on a.kunci=b.kunci  where 1=1 " . $where . " " . $group . "";
		$res = fetchdata($sql);
		$jlhbrs = count($res);

		$rowspan = "";
		$lmt = "limit " . $offset . "," . $limit . "";

		$str = "select a.keterangan, a.kunci, a.noakun,a.tahunbudget, b.kunci as kuncisebar,sum(b.rp01) as rp01,sum(b.rp02) as rp02,sum(b.rp03) as rp03,sum(b.rp04) as rp04,sum(b.rp05) as rp05,sum(b.rp06) as rp06,sum(b.rp07) as rp07,sum(b.rp08) as rp08,sum(b.rp09) as rp09,sum(b.rp10) as rp10,sum(b.rp11) as rp11,sum(b.rp12) as rp12, substr(a.kodeorg,1,4) as kodeunit, dept, aruskas, sum(a.rupiah) as rupiah 
		from " . $dbname . ".bgt_budget a 
		left join " . $dbname . ".bgt_distribusi b on a.kunci=b.kunci  
		where 1=1 " . $where . " " . $group . "
		order by a.tahunbudget desc,a.kodeorg asc,a.noakun asc " . $lmt . "";
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

				$nmdept = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $val['dept'] . "'");
				$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $val['noakun'] . "'");
				$nmarus = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $val['aruskas'] . "'");
				$check = "";

				$tab .= "<tr class='rowcontent' style='height:25px' id=rowsebar" . $no . ">";
				if ($param['tampilkan'] == '1') {
					$tab .= "<td width=25px align=center>
							<input id=chkboxsebar" . $no . " type=checkbox " . $check . " onclick=sebararuskas('" . $no . "'); title=\"Sebarkan sesuai proporsi diatas\">
						</td>";
				} elseif ($param['tampilkan'] == '2') {
					$tab .= "<td width=25px align=center>
							<input id=chkboxsebar" . $no . " type=checkbox " . $check . " onclick=sebardept('" . $no . "','" . $no . "'); title=\"Sebarkan sesuai proporsi diatas\">
						</td>";
				} elseif ($param['tampilkan'] == '3') {
					$tab .= "<td width=25px align=center>
							<input id=chkboxsebar" . $no . " type=checkbox " . $check . " onclick=sebardetail('" . $no . "','" . $no . "'); title=\"Sebarkan sesuai proporsi diatas\">
						</td>";
				}
				$tab .= "<td hidden id=index" . $no . ">" . $val['kunci'] . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:middle;'>" . $no . "</td>";
				$tab .= "<td style='text-align:center;vertical-align:middle;' id=tahun" . $no . ">" . $val['tahunbudget'] . "</td>";
				if ($param['tampilkan'] == '2') {
					$tab .= "<td style='text-align:center;vertical-align:middle;' id=dept" . $no . ">" . $val['dept'] . "</td>";
					$tab .= "<td style='text-align:left;vertical-align:middle;'  colspan=2>" . $nmdept[$val['dept']] . "</td>";
				}
				if ($param['tampilkan'] == '1') {
					$tab .= "<td style='text-align:center;vertical-align:middle;' id=aruskas" . $no . ">" . $val['aruskas'] . "</td>";
					$tab .= "<td style='text-align:left;vertical-align:middle;' colspan=2>" . $nmarus[$val['aruskas']] . "</td>";
				}
				if ($param['tampilkan'] == '3') {
					$tab .= "<td style='text-align:center;vertical-align:middle;' id=noakun" . $no . ">" . $val['noakun'] . "<x/td>";
					$tab .= "<td style='text-align:left;vertical-align:middle;'>" . $nmakun[$val['noakun']] . "</td>";
					$tab .= "<td style='text-align:left;vertical-align:middle;'>" . $val['keterangan'] . "</td>";
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
	case 'sebardept':
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

			$str = "select * from " . $dbname . ".bgt_budget where `tahunbudget` = '" . $param['tahun'] . "' and kodebudget = 'UMUM' and pta='BGT' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' and `dept` = '" . $param['dept'] . "'";
			//exit("error".$str);
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
	case 'sebararuskas':
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

			$str = "select * from " . $dbname . ".bgt_budget where `tahunbudget` = '" . $param['tahun'] . "' and kodebudget = 'UMUM' and pta='BGT' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' and `aruskas` = '" . $param['aruskas'] . "'";
			//exit("error".$str);
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
	case 'fillfield':
		$str = "select * from " . $dbname . ".bgt_budget where kunci='" . $id . "'"; #exit("error".$str);
		$res = fetchdata($str);
		foreach ($res as $bar) {
			echo $bar['tahunbudget'] . "##" . $bar['kodebudget'] . "##" . $bar['kodeorg'] . "##" . $bar['dept'] . "##" . $bar['aruskas'] . "##" . $bar['noakun'] . "##" . $bar['kodevhc'] . "##" . $bar['jumlah'] . "##" . $bar['rupiah'] . "##" . $bar['keterangan'] . "##" . $bar['kodebarang'] . "##" . getNamaBrg($bar['kodebarang']);
		}

		$str = "select distinct tipetransaksi from " . $dbname . ".keu_5aruskas where noaruskas = '" . $bar['aruskas'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			echo "##" . $bar['tipetransaksi'];
		}

		break;
	case 'getaruskas':
		$akuntambah = "";
		if ($tipebudget == 'MILL') {
			$akuntambah = "b.noakun like '7%' or b.noakun like '9%'";
		} else if ($tipebudget == 'ESTATE' or $tipebudget == 'RND') {
			$akuntambah = "b.noakun like'7%' or b.noakun like '9%'";
		} else if ($tipebudget == 'HOLDING') {
			$akuntambah = "b.noakun like'8%' or b.noakun like '9%' or b.noakun like '5%'";
		} else {
			$akuntambah = "b.noakun like'8%' or b.noakun like '9%' or b.noakun like '5%'";
		}

		$akuntambah = "b.noakun ='" . $param['noakun'] . "'";

		//$option="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select distinct a.noaruskas, a.nama_aruskas from " . $dbname . ".keu_5aruskas a left join " . $dbname . ".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='" . $param['keluarmasuk'] . "' and a.level='3' and a.status='1' and (" . $akuntambah . ") order by a.noaruskas asc";
		// exit("error".$str);
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$b = "";
			if ($param['aruskas'] == $bar['aruskas']) {
				$b = "selected";
			}
			$option .= "<option value=" . $bar['noaruskas'] . " " . $b . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
		}
		echo $option;
		break;

	case 'getakun':

		if ($_SESSION['language'] == 'ID') {
			$dd = 'namaakun as namaakun';
		} else {
			$dd = 'namaakun1 as namaakun';
		}
		if ($tipebudget == 'MILL') {
			$akuntambah = "b.noakun like '7%' or b.noakun like '9%'";
		} else if ($tipebudget == 'ESTATE' or $tipebudget == 'RND') {
			$akuntambah = "b.noakun like'7%' or b.noakun like '9%'";
		} else if ($tipebudget == 'HOLDING') {
			$akuntambah = "b.noakun like'8%' or b.noakun like '9%' or b.noakun like '5%'";
		} else {
			$akuntambah = "b.noakun like'8%' or b.noakun like '9%' or b.noakun like '5%'";
		}


		$option = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select a.noakun," . $dd . " from " . $dbname . ".keu_5akun a left join " . $dbname . ".keu_5aruskas_detail b on a.noakun=b.noakun where a.detail=1 and a.tipeakun in ('Biaya','Penjualan') and b.noaruskas='" . $param['aruskas'] . "' and (" . $akuntambah . ") order by a.noakun"; #exit("error".$str);
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$option .= "<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
		}
		echo $option;
		break;
	case 'getkodevhc':
		$str = "select distinct regional from " . $dbname . ".bgt_regional_assignment where kodeunit='" . substr($param['kodeorg'], 0, 4) . "' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];

		$optbarang = "<option value=''></option>";
		$str = "select * from " . $dbname . ".bgt_masterbarang where regional='" . $region . "' and tahunbudget='" . $param['tahunbudget'] . "' and closed=1 and kodebarang like '3%'";
		$res = fetchData($str);
		foreach ($res as $bar) {
			$s = "select namabarang,satuan from " . $dbname . ".log_5masterbarang where kodebarang='" . $bar['kodebarang'] . "'";
			$nm = fetchData($s)[0];
			if ($bar['hargasatuan'] > 0) {
				$optbarang .= "<option value='" . $bar['kodebarang'] . "'>" . $bar['kodebarang'] . " - " . $nm['namabarang'] . "</option>";
			} else {
				$optbarang .= "<option value='" . $bar['kodebarang'] . "' disabled>" . $bar['kodebarang'] . " - " . $nm['namabarang'] . "</option>";
			}
		}



		// $str="select sum(rupiah) as rupiah, kodevhc from ".$dbname.".bgt_budget where kodeorg like '".$param['kodeorg']."%' and tahunbudget ='".$param['tahunbudget']."' and tipebudget ='TRK' group by kodevhc"; #exit("error".$str);
		$str = "select sum(rupiah) as rupiah, kodevhc from " . $dbname . ".bgt_budget where tahunbudget ='" . $param['tahunbudget'] . "' and tipebudget ='TRK' group by kodevhc"; #exit("error".$str);
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$data[$bar['kodevhc']] += $bar['rupiah'];
		}

		$optVhc = "<option value=''></option>";
		$optnopol = makeOption($dbname, 'vhc_5master', 'kodevhc,nopol');
		$str = "select distinct(kodevhc) as kodevhc, jumlahjam from " . $dbname . ".bgt_vhc_jam where unitalokasi like '" . $param['kodeorg'] . "%' and tahunbudget='" . $param['tahunbudget'] . "' order by kodevhc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($data[$bar['kodevhc']] > 0) {
				$optVhc .= "<option value='" . $bar['kodevhc'] . "'>" . $bar['kodevhc'] . " - " . $optnopol[$bar['kodevhc']] . "</option>";
			} else {
				$optVhc .= "<option value='" . $bar['kodevhc'] . "' disabled>" . $bar['kodevhc'] . " - " . $optnopol[$bar['kodevhc']] . "</option>";
			}
		}

		echo $optVhc . "####" . $optbarang;
		break;
}

if ($cekapa == '') $cekapa = $_GET['cekapa'];

if ($cekapa == 'sebarDoong') {
	$var1 = $_POST['var1'];
	$var2 = $_POST['var2'];
	$var3 = $_POST['var3'];
	$var4 = $_POST['var4'];
	$var5 = $_POST['var5'];
	$var6 = $_POST['var6'];
	$var7 = $_POST['var7'];
	$var8 = $_POST['var8'];
	$var9 = $_POST['var9'];
	$var10 = $_POST['var10'];
	$var11 = $_POST['var11'];
	$var12 = $_POST['var12'];
	$rupiah = $_POST['rupe'];
	$fis = $_POST['fis'];
	$kunci = $_POST['kunci'];
	$str = "delete from " . $dbname . ".bgt_distribusi where kunci=" . $kunci;
	$owlPDO->exec($str);
	$str = "insert into " . $dbname . ".bgt_distribusi (kunci, rp01, fis01, rp02, fis02, rp03, fis03, rp04, fis04, rp05, fis05, rp06, fis06, rp07, fis07, rp08, fis08, rp09, fis09, rp10, fis10, rp11, fis11, rp12, fis12, updateby)
        values(" . $kunci . ",  
           " . $var1 * $rupiah . ",
           " . $var1 * $fis . ",
           " . $var2 * $rupiah . ",
           " . $var2 * $fis . ",
           " . $var3 * $rupiah . ",
           " . $var3 * $fis . ",
           " . $var4 * $rupiah . ",
           " . $var4 * $fis . ",
           " . $var5 * $rupiah . ",
           " . $var5 * $fis . ",
           " . $var6 * $rupiah . ",
           " . $var6 * $fis . ",
           " . $var7 * $rupiah . ",
           " . $var7 * $fis . ",
           " . $var8 * $rupiah . ",
           " . $var8 * $fis . ",
           " . $var9 * $rupiah . ",
           " . $var9 * $fis . ",
           " . $var10 * $rupiah . ",
           " . $var10 * $fis . ",
           " . $var11 * $rupiah . ",
           " . $var11 * $fis . ",
           " . $var12 * $rupiah . ",
           " . $var12 * $fis . ",
           " . $_SESSION['standard']['userid'] . ");"; #exit("error".$str);
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "\n";
		die();
	}
}

if ($cekapa == 'saveatas') {

	$tipebudget = $_POST['tipebudget'];
	$tahunbudget = $_POST['tahunbudget'];
	$jenisbiaya = $_POST['jenisbiaya'];
	$kodebudget = $_POST['kodebudget'];
	$jumlahbiaya = $_POST['jumlahbiaya'];
	$ktrngan    = $_POST['ktrngan'];
	$jumlah     = $_POST['jamperthn'];
	$kodevhc    = $_POST['kodevhc'];
	#$lokasi    =substr($_SESSION['empl']['lokasitugas'],0,4);
	$lokasi     = checkPostGet('kodeorg', '');
	$dept       = checkPostGet('dept', '');

	if ($lokasi == '') {
		exit("Error : Kode organisasi tidak boleh kosong.");
	}
	if ($dept == '') {
		exit("Error : Departemen tidak boleh kosong.");
	}
	if ($jenisbiaya == '') {
		exit("Error : Nomor akun tidak boleh kosong.");
	}

	if ($jumlah == '')
		$jumlah = 0;

	$sCekJam = "select * from " . $dbname . ".bgt_biaya_jam_ken_vs_alokasi where tahunbudget='" . $tahunbudget . "'  and kodevhc='" . $kodevhc . "'";
	$qCekJam = $owlPDO->query($sCekJam) or die(print " Gagal: " . PDOException::getMessage());
	$qCekJam->setFetchMode(PDO::FETCH_ASSOC);
	$rCekJam = $qCekJam->fetch();
	$sisa = $rCekJam['jamsetahun'] - $rCekJam['teralokasi'];
	if ($jumlah > $sisa) {
		exit("Error: Vehicle " . $kodevhc . " has been allocated: " . $rCekJam['teralokasi'] . " from total hours :" . $rCekJam['jamsetahun'] . " can only allocate as remains:" . $sisa . "");
	}

	if (substr($jenisbiaya, 0, 1) != '7' and substr($jenisbiaya, 0, 1) != '8' and substr($jenisbiaya, 0, 1) != '9') {
		exit("Error : Akun tidak diperbolehkan.");
	}

	$str = "select distinct regional from " . $dbname . ".bgt_regional_assignment where kodeunit='" . substr($param['kodeorg'], 0, 4) . "' ";
	$res = fetchdata($str);
	$region = $res[0]['regional'];


	$str = "select * from " . $dbname . ".log_5masterbarang where kodebarang = '" . $param['kodebarang'] . "'"; #exit("error".$str);
	$res = fetchData($str);
	foreach ($res as $bar) {
		$satuan = $bar['satuan'];
	}

	if ($jumlah > 0) {
		$jumlah = $jumlah;
		$satuan = "JAM";
	} else {
		$jumlah = $param['jlhbarang'];
		$satuan = $satuan;
	}

	$str = "INSERT INTO " . $dbname . ".`bgt_budget` (`tipebudget` ,`tahunbudget` ,`kodeorg` ,`kodebudget` ,`noakun` ,`aruskas` ,`rupiah` ,`updateby` ,`keterangan`,`kodevhc`,`jumlah`,`satuanj`,`dept`, `kodebarang`,`regional`)VALUES (
	'" . $tipebudget . "', '" . $tahunbudget . "', '" . $lokasi . "', '" . $kodebudget . "', '" . $jenisbiaya . "','" . $aruskas . "', '" . $jumlahbiaya . "', '" . $_SESSION['standard']['userid'] . "','" . $ktrngan . "','" . $kodevhc . "','" . $jumlah . "','" . $satuan . "','" . $dept . "','" . $param['kodebarang'] . "','" . $region . "')"; #exit("error".$str);
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "\n";
		die();
	}
}
if ($cekapa == 'editatas') {

	$tipebudget  = $_POST['tipebudget'];
	$tahunbudget = $_POST['tahunbudget'];
	$jenisbiaya  = $_POST['jenisbiaya'];
	$kodebudget  = $_POST['kodebudget'];
	$jumlahbiaya = $_POST['jumlahbiaya'];
	$ktrngan     = $_POST['ktrngan'];
	$jumlah      = $_POST['jamperthn'];
	$kodevhc     = $_POST['kodevhc'];
	#$lokasi     =substr($_SESSION['empl']['lokasitugas'],0,4);
	$lokasi      = checkPostGet('kodeorg', '');
	$dept        = checkPostGet('dept', '');
	$jamperthnold = checkPostGet('jamperthnold', '');


	if ($lokasi == '') {
		exit("Error : Kode organisasi tidak boleh kosong.");
	}
	if ($dept == '') {
		exit("Error : Departemen tidak boleh kosong.");
	}
	if ($jenisbiaya == '') {
		exit("Error : Nomor akun tidak boleh kosong.");
	}

	if (substr($jenisbiaya, 0, 1) != '7' and substr($jenisbiaya, 0, 1) != '8' and substr($jenisbiaya, 0, 1) != '9') {
		exit("Error : Akun tidak diperbolehkan.");
	}
	if ($jumlah == '')
		$jumlah = 0;

	$sCekJam = "select * from " . $dbname . ".bgt_biaya_jam_ken_vs_alokasi where tahunbudget='" . $tahunbudget . "'  and kodevhc='" . $kodevhc . "'";
	$rCekJam = fetchdata($sCekJam)[0];
	$sisa = $rCekJam['jamsetahun'] - $rCekJam['teralokasi'];

	if ($jumlah > ($sisa + $jamperthnold)) {
		exit("Error: Vehicle " . $kodevhc . " has been allocated: " . $rCekJam['teralokasi'] . " from total hours :" . $rCekJam['jamsetahun'] . " can only allocate as remains:" . $sisa . "");
	}


	$str = "select distinct regional from " . $dbname . ".bgt_regional_assignment where kodeunit='" . substr($param['kodeorg'], 0, 4) . "' ";
	$res = fetchdata($str);
	$region = $res[0]['regional'];

	$satuan = "";
	$str = "select * from " . $dbname . ".log_5masterbarang where kodebarang = '" . $param['kodebarang'] . "'"; #exit("error".$str);
	$res = fetchData($str);
	foreach ($res as $bar) {
		$satuan = $bar['satuan'];
	}

	if ($jumlah > 0) {
		$jumlah = $jumlah;
		$satuan = "JAM";
	} else {
		$jumlah = $param['jlhbarang'];
		$satuan = $satuan;
	}

	$data = array();
	$data = array(
		'tipebudget' => $tipebudget,
		'tahunbudget' => $tahunbudget,
		'kodeorg'    => $lokasi,
		'kodebudget' => $kodebudget,
		'dept'       => $dept,
		'aruskas'    => $aruskas,
		'noakun'     => $jenisbiaya,
		'kodevhc'    => $kodevhc,
		'jumlah'     => $jumlah,
		'rupiah'     => $jumlahbiaya,
		'keterangan' => $ktrngan,
		'kodebarang' => $param['kodebarang'],
		'satuanj'    => $satuan,
		'updateby'   => $_SESSION['standard']['userid']
	);

	$where = "kunci='" . $id . "'";
	$str = updateQuery($dbname, 'bgt_budget', $data, $where);
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "\n";
		die();
	}
}

if ($cekapa == 'cekclose') {

	$tipebudget = $_POST['tipebudget'];
	$tahunbudget = $_POST['tahunbudget'];
	$jenisbiaya = $_POST['jenisbiaya'];
	$kodebudget = $_POST['kodebudget'];
	$jumlahbiaya = $_POST['jumlahbiaya'];
	$lokasi = substr($_SESSION['empl']['lokasitugas'], 0, 4);

	$str = "select * from " . $dbname . ".bgt_budget
        where tutup = '1' and kodeorg = '" . $lokasi . "' and kodebudget = 'UMUM' and tahunbudget ='" . $tahunbudget . "' and pta='BGT' limit 0, 1";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$hkef = '';
	while ($bar = $res->fetch()) {
		$hkef .= "Data has been closed";
	}
	if ($hkef != '') echo $hkef;
}

if ($cekapa == 'updatetahun') {
	//pilihan tipebudget
	$tipebudget = substr($_SESSION['empl']['lokasitugas'], 3, 1);
	if ($tipebudget == 'M') $tipebudget = 'MILL';
	else
    if ($tipebudget == 'E') $tipebudget = 'ESTATE';
	else $tipebudget = $_SESSION['empl']['tipelokasitugas'];
	$kodeorg = substr($_SESSION['empl']['lokasitugas'], 0, 4);

	$str = "select distinct tahunbudget from " . $dbname . ".bgt_budget
        where tipebudget='" . $tipebudget . "' and kodeorg like '" . $kodeorg . "%' and kodebudget like 'UMUM%'
        order by tahunbudget desc";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$hkef = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
	while ($bar = $res->fetch()) {
		$hkef .= "<option value='" . $bar->tahunbudget . "'>" . $bar->tahunbudget . "</option>";
	}
	echo $hkef;
}

if ($cekapa == 'vhc') {
	$harga = 0;
	$kodevhc = $_POST['kodevhc'];
	$jamperthn = $_POST['jamperthn'];
	$str = "select rpperjam from " . $dbname . ".bgt_biaya_ken_per_jam where kodevhc='" . $kodevhc . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$rpperjam = $bar->rpperjam;
		$harga = $bar->rpperjam;
	}
	$total = floatval($jamperthn) * floatval($rpperjam);


	$str = "select distinct regional from " . $dbname . ".bgt_regional_assignment where kodeunit='" . substr($param['kodeorg'], 0, 4) . "' ";
	$res = fetchdata($str);
	$region = $res[0]['regional'];

	$str = "select * from " . $dbname . ".bgt_masterbarang where regional='" . $region . "' and tahunbudget='" . $param['tahunbudget'] . "' and closed=1 and kodebarang = '" . $param['kodebarang'] . "'"; #exit("error".$str);
	$res = fetchData($str);
	foreach ($res as $bar) {
		$harga = $bar['hargasatuan'];
	}

	if ($param['jlhbarang'] == '') {
		$param['jlhbarang'] = 0;
	}
	$rpbrg = $harga * $param['jlhbarang'];

	echo (round($total, 2)) . "####" . round(floatval($rpperjam), 2) . "####" . round($rpbrg) . "####" . round($harga);
}

#tampilkan data tab0
if ($cekapa == 'tab') {
	$tahunbudget = $_POST['tahunbudget'];
	$pilihtahun0 = $_POST['pilihtahun0'];
	$kodeorg    = checkPostGet('kodeorg', '');
	$dept       = checkPostGet('dept', '');
	$aruskas    = checkPostGet('aruskas', '');

	#pilihan tipebudget
	if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
		if ($kodeorg != '') {
			$where = " and kodeorg = '" . $kodeorg . "'";
		}
	} else {
		$tipebudget = substr($_SESSION['empl']['lokasitugas'], 3, 1);
		if ($tipebudget == 'M') $tipebudget = 'MILL';
		else if ($tipebudget == 'E') $tipebudget = 'ESTATE';
		else $tipebudget = $_SESSION['empl']['tipelokasitugas'];
		$kodeorg = substr($_SESSION['empl']['lokasitugas'], 0, 4);
		$where = " and tipebudget = '" . $tipebudget . "' and kodeorg = '" . $kodeorg . "'";
	}



	$hkef = '';
	$hkef .= "<input type=hidden id=hidden0 name=hidden0 value=\"\">";
	$hkef .= "<table id=container9 class=sortable cellspacing=1 border=0 cellpadding=5>
	 <thead>
		<tr>
			<th align=center>No</th>
			<th align=center width=50px>" . $_SESSION['lang']['budgetyear'] . "</th>
			<th align=center width=50px>" . $_SESSION['lang']['kodeorg'] . "</th>
			<th align=center>Dept</th>
			<!--<th align=center width=50px>" . $_SESSION['lang']['tipeanggaran'] . "</th>-->
			<!--<th align=center width=50px>" . $_SESSION['lang']['kodeanggaran'] . "</th>-->
			<th align=center>" . $_SESSION['lang']['aruskas'] . "</th>
			<th align=center>" . $_SESSION['lang']['noakun'] . "</th>
			<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>
			<th align=center>" . $_SESSION['lang']['kodevhc'] . "</th>
			<th align=center>" . $_SESSION['lang']['namabarang'] . "</th>
			<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
			<th align=center>" . $_SESSION['lang']['totalbiaya'] . "</th>
			<th align=center>" . $_SESSION['lang']['updateby'] . "</th>
			<th align=center colspan=2>" . $_SESSION['lang']['action'] . "</th>
	   </tr>  
	 </thead>
	 <tbody>";

	$str = "select noakun,namaakun from " . $dbname . ".keu_5akun where detail=1 order by noakun";
	$optakun = "";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$akun[$bar->noakun] = $bar->namaakun;
	}

	$wh = '';
	if ($akunsrc != '') {
		$wh .= " and (noakun like '%" . $akunsrc . "%' or noakun in (select noakun from " . $dbname . ".keu_5akun where namaakun like '%" . $akunsrc . "%'))";
	}
	if ($aruskas != '') {
		$wh .= " and (aruskas like '%" . $aruskas . "%' or aruskas in (select noaruskas from " . $dbname . ".keu_5aruskas where nama_aruskas like '%" . $aruskas . "%'))";
	}
	if ($ketsrc != '') {
		$wh .= " and keterangan like '%" . $ketsrc . "%'";
	}
	if ($dept != '') {
		$wh .= " and dept like '%" . $dept . "%'";
	}
	$nmbarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
	$nopol    = makeOption($dbname, 'vhc_5master', 'kodevhc,nopol');
	$no = 1;
	$optnmdept = makeOption($dbname, 'sdm_5departemen', 'kode,nama');
	$str = "select * from " . $dbname . ".bgt_budget where kodebudget like 'UMUM%' and pta='BGT' " . $where . " and tahunbudget like '%" . $pilihtahun0 . "%' " . $wh . "  order by tahunbudget desc,kunci desc, noakun";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$nmorg    = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $bar->kodeorg . "'");
		$nmaruskas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $bar->aruskas . "'");
		$nmkar    = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar->updateby . "'");
		if ($nopol[$bar->kodevhc] != '') {
			$kend = $bar->kodevhc . " - " . $nopol[$bar->kodevhc];
		} else {
			$kend = $bar->kodevhc;
		}

		$hkef .= "<tr class=rowcontent title=" . @$isiDta . ">
			<td align=center>" . $no . "</td>
			<td align=center>" . $bar->tahunbudget . "</td>
			<td align=center>" . $bar->kodeorg . "</td>
			<td align=left>" . $optnmdept[$bar->dept] . "</td>
			<!--<td align=center>" . $bar->tipebudget . "</td>
			<td align=center>" . $bar->kodebudget . "</td>-->
			<td align=left>" . $bar->aruskas . " - " . $nmaruskas[$bar->aruskas] . "</td>
			<td align=center>" . $bar->noakun . "</td>
			<td align=left>" . $akun[$bar->noakun] . "</td>
			<td align=left>" . $kend . "</td>
			<td align=left>" . $nmbarang[$bar->kodebarang] . "</td>
			<td align=left>" . UCFIRST($bar->keterangan) . "</td>
			<td align=right>" . number_format($bar->rupiah) . "</td>
			<td align=center>" . $nmkar[$bar->updateby] . "</td>
			";
		if ($bar->tutup == 0) {
			$hkef .= "<td align=center width=20px><img src='images/application/application_edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('" . $bar->kunci . "');\"></td>";
			$hkef .= "<td align=center  width=20px><img id=\"delRow\" class=\"zImgBtn\" src=\"images/application/application_delete.png\" onclick=\"deleteRow(" . $bar->kunci . ")\" title=\"Hapus\"></td>";
		} else {
			$hkef .= "<td align=center  width=20px>&nbsp;</td>";
			$hkef .= "<td align=center  width=20px>&nbsp;</td>";
		}
		$hkef .= "</tr>";
		$no += 1;
		@$total += $bar->rupiah;
	}
	$hkef .= "</tbody><tfoot>";
	$hkef .= "<tr class=rowcontent>
			<td align=center colspan=10><b>T O T A L</b></td>
			<td align=right><b>" . number_format($total) . "</b></td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			</tr>";
	$hkef .= "</tfoot>";


	echo $hkef;

	echo "</table>";
} #tutup if cekapa

//delete row, all tab berdasarkan kunci
if ($cekapa == 'delete') {
	$kunci = $_POST['kunci'];
	$str = "delete from " . $dbname . ".bgt_budget  where kunci='" . $kunci . "'";
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "\n";
		die();
	}
}

if ($cekapa == 'tutup') {
	$kunci = $_POST['kunci'];
	$str = "update " . $dbname . ".bgt_budget set tutup='1'  where kunci ='" . $kunci . "'";
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "\n";
		die();
	}
}


//tampilkan data tab4
if ($cekapa == 'sebaran') {
	$kunci = $_GET['kunci'];
	//kamus kodeakun    
	$str = "select noakun,namaakun from " . $dbname . ".keu_5akun
				where detail=1 and tipeakun = 'Biaya' order by noakun";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$akun[$bar->noakun] = $bar->namaakun;
	}

	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
	<div style="border: 1px solid orange; width: 150px; position: fixed; right: 20px; top: 65px; color: rgb(255, 0, 0); font-family: Tahoma; font-size: 13px; font-weight: bolder; text-align: center; background-color: rgb(255, 255, 255); z-index: 10000; display: none;" id="progress">
		Please wait.....! <br>
		<img src="images/progress.gif">
	</div>
	<script language=javascript1.2 src="js/generic.js"></script>
	<script language=javascript1.2 src="js/budget_by_umum.js"></script>
	<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
	$arrBln = array("1" => "Jan", "2" => "Feb", "3" => "Mar", "4" => "Apr", "5" => "Mei", "6" => "Jun", "7" => "Jul", "8" => "Aug", "9" => "Sep", "10" => "Okt", "11" => "Nov", "12" => "Des");
	$hkef = '';

	$sTot = "select * from " . $dbname . ".bgt_budget_detail where kunci = '" . $kunci . "'";
	$qTot = $owlPDO->query($sTot) or die(print " Gagal: " . PDOException::getMessage());
	$qTot->setFetchMode(PDO::FETCH_ASSOC);
	$rRes = $qTot->fetch();

	$hkef .= "<p align=center><fieldset><legend>" . $_SESSION['lang']['sebaran'] . "/" . $_SESSION['lang']['bulan'] . "</legend>";
	$hkef .= "<table cellspacing=1 cellpadding=1 border=0 class=sortable><thead>";
	$hkef .= "<tr class=rowheader><td align=center>" . $_SESSION['lang']['total'] . " (Rp.)</td><td align=center>%</td><td align=right id='hasilPerkalian'>" . number_format($rRes['rupiah']) . "</td></tr></thead><tbody>";
	for ($bre = 1; $bre <= 12; $bre++) {
		if (strlen($bre) < 2) {
			$abe = "0" . $bre;
		} else {
			$abe = $bre;
		}
		if (is_null($rRes['rp' . $abe])) {
			@$hslDr = (($rRes['rupiah'] / 12) / $rRes['rupiah']) * 100;
			$rRes['rp' . $abe] = $rRes['rupiah'] / 12;
		} else {
			@$hslDr = ($rRes['rp' . $abe] / $rRes['rupiah']) * 100;
		}
		$hkef .= "<tr class=rowcontent><td>" . $arrBln[$bre] . "</td>
			<td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=persenPrdksi" . $bre . " onblur=ubahNilai(this.value,'" . $rRes['rupiah'] . "','brt_x') value='" . $hslDr . "' /></td>";
		$hkef .= "<td><input type='text' id=brt_x" . $bre . " class=\"myinputtextnumber\" style=\"width:75px;\" value=" . $rRes['rp' . $abe] . " /></td>
			</tr>";
	}

	$hkef .= "<tr class=rowcontent><td  colspan=3 align=center style='cursor:pointer;'><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"simpansebaran('" . $rRes['kunci'] . "','" . $rRes['rupiah'] . "',event)\" src='images/save.png'/>&nbsp;&nbsp;<img id='detail_add' title='Clear Form' class=zImgBtn  width='16' height='16'  onclick=\"clearForm()\" src='images/clear.png'/></td>";
	$hkef .= "</tr></tbody></table></fieldset></p>";

	echo $hkef;

	echo "</tbody>
     <tfoot>
     </tfoot>		 
     </table>";
}

//tampilkan data tab1
if ($cekapa == 'tabs') {
	$tahunbudget = $_POST['tahunbudget'];
	$pilihtahun1 = $_POST['pilihtahun1'];
	$kodeorg     = checkPostGet('kodeorg', '');
	$dept       = checkPostGet('dept', '');
	$aruskas    = checkPostGet('aruskas', '');

	#pilihan tipebudget
	if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
		if ($kodeorg != '') {
			$where = " and a.kodeorg = '" . $kodeorg . "'";
		}
	} else {
		$tipebudget = substr($_SESSION['empl']['lokasitugas'], 3, 1);
		if ($tipebudget == 'M') $tipebudget = 'MILL';
		else if ($tipebudget == 'E') $tipebudget = 'ESTATE';
		else $tipebudget = $_SESSION['empl']['tipelokasitugas'];
		$kodeorg = substr($_SESSION['empl']['lokasitugas'], 0, 4);
		$where = " and a.tipebudget = '" . $tipebudget . "' and a.kodeorg = '" . $kodeorg . "'";
	}

	$hkef = '';
	$hkef .= "<input type=hidden id=hidden1 name=hidden1 value=\"\">";
	$hkef .= "<table id=container6 class=sortable cellspacing=1 border=0 cellpadding=5>
     <thead>
        <tr>
            <td align=center>#</td>
            <td align=center>No</td>
            <td align=center width=50px>" . $_SESSION['lang']['budgetyear'] . "</td>
            <td align=center width=50px>" . $_SESSION['lang']['kodeorg'] . "</td>
            <td align=center>Dept</td>
            <td align=center width=50px>" . $_SESSION['lang']['kodeanggaran'] . "</td>
            <td align=center width=50px>" . $_SESSION['lang']['tipeanggaran'] . "</td>
            <td align=center>" . $_SESSION['lang']['aruskas'] . "</td>
            <td align=center width=50px>" . $_SESSION['lang']['noakun'] . "</td>
            <td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            <td align=center>Jan</td>
            <td align=center>Feb</td>
            <td align=center>Mar</td>
            <td align=center>Apr</td>
            <td align=center>May</td>
            <td align=center>Jun</td>
            <td align=center>Jul</td>
            <td align=center>Aug</td>
            <td align=center>Sep</td>
            <td align=center>Oct</td>
            <td align=center>Nov</td>
            <td align=center>Dec</td>
            <td align=center>" . $_SESSION['lang']['totalbiaya'] . "</td>
            <td align=center>" . $_SESSION['lang']['action'] . "</td>
       </tr>  
     </thead>
     <tbody>";
	//pilihan kodeakun    
	$str = "select noakun,namaakun from " . $dbname . ".keu_5akun
                    where detail=1 and tipeakun = 'Biaya' order by noakun";
	$optakun = "";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$akun[$bar->noakun] = $bar->namaakun;
	}
	$wh = '';
	if ($akunsrc != '') {
		$wh .= " and (b.noakun like '%" . $akunsrc . "%' or b.noakun in (select noakun from " . $dbname . ".keu_5akun where namaakun like '%" . $akunsrc . "%'))";
	}
	if ($aruskas != '') {
		$wh .= " and (b.aruskas like '%" . $aruskas . "%' or b.aruskas in (select noaruskas from " . $dbname . ".keu_5aruskas where nama_aruskas like '%" . $aruskas . "%'))";
	}
	if ($ketsrc != '') {
		$wh .= " and b.keterangan like '%" . $ketsrc . "%'";
	}
	if ($dept != '') {
		$wh .= " and b.dept like '%" . $dept . "%'";
	}

	if ($sebaransrc == '1') {
		$wh .= " and b.kunci in (select kunci from " . $dbname . ".bgt_distribusi)";
	} else if ($sebaransrc == '2') {
		$wh .= " and b.kunci not in (select kunci from " . $dbname . ".bgt_distribusi)";
	}

	$str = "select a.*, b.tutup,b.keterangan, b.kunci,b.dept from " . $dbname . ".bgt_budget_detail a left join " . $dbname . ".bgt_budget b on a.kunci=b.kunci
	where a.kodebudget like 'UMUM%' " . $where . " and a.tahunbudget like '%" . $pilihtahun1 . "%' " . $wh . " and b.pta='BGT' order by a.tahunbudget desc, b.kunci desc, a.noakun";
	$ttlbrs = count(fetchdata($str));
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$no = 1;
	$hkef .= "<tr class=rowcontent><td colspan=25><button class=mybutton onclick=sebarkanall(" . $ttlbrs . ") title=\"Sebarkan Seluruhnya\">Sebarkan Seluruhnya</button></td></tr>
	";
	$optnmdept = makeOption($dbname, 'sdm_5departemen', 'kode,nama');
	while ($bar = $res->fetch()) {
		$nmaruskas = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas', "noaruskas='" . $bar->aruskas . "'");
		$bar->tutup == 0 ? $rpt = " onclick=\"sebaranumum(" . $bar->kunci . ",event)\" title='Sebaran " . $kodeorg . " " . $akun[$bar->noakun] . "' style='cursor:pointer;'" : $rpt = " ";
		$hkef .= "<tr class=rowcontent id=baris" . $no . ">
			<td><input id=chkboxsebar" . $no . " type=checkbox onclick=sebarkanBoo(" . $no . "); title='Sebarkan sesuai proporsi diatas'></td>
			<td style=display:none>
					<input id='kunci" . $no . "' value=" . $bar->kunci . " />
					<input id='rupiah" . $no . "' value=" . $bar->rupiah . " />
					<input id='jlh" . $no . "' value=" . $bar->jumlah . " />			
				</td>
            <td align=center " . $rpt . ">" . $no . "</td>
            <td align=center " . $rpt . ">" . $bar->tahunbudget . "</td>
            <td align=center " . $rpt . ">" . $bar->kodeorg . "</td>
            <td align=left " . $rpt . ">" . $optnmdept[$bar->dept] . "</td>
            <td align=center " . $rpt . ">" . $bar->kodebudget . "</td>
            <td align=center " . $rpt . ">" . $bar->tipebudget . "</td>
            <td align=left " . $rpt . ">" . $bar->aruskas . " - " . $nmaruskas[$bar->aruskas] . "</td>
            <td align=right " . $rpt . ">" . $bar->noakun . "</td>
            <td align=left " . $rpt . ">" . $akun[$bar->noakun] . "</td>
            <td align=left " . $rpt . ">" . $bar->keterangan . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp01) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp02) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp03) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp04) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp05) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp06) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp07) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp08) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp09) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp10) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp11) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rp12) . "</td>
            <td align=right " . $rpt . ">" . number_format($bar->rupiah) . "</td>";
		if ($bar->tutup == 0)
			$hkef .= "
            <td align=center>
                <input type=\"image\" id=search4 class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;' title=" . $_SESSION['lang']['sebaran'] . " onclick=\"sebaranumum(" . $bar->kunci . ",event)\";>
            </td>";
		else
			$hkef .= "<td align=center>&nbsp;</td>";
		$hkef .= "
       </tr>";
		$no += 1;
	}
	echo $hkef;


	echo "</tbody>
     <tfoot>
     </tfoot>		 
     </table>";
}

//tampilkan data tab2
if ($cekapa == 'tab2') {
	//pilihan tipebudget
	$tipebudget = substr($_SESSION['empl']['lokasitugas'], 3, 1);
//if($tipebudget=='M')$tipebudget='MILL'; else
//if($tipebudget=='E')$tipebudget='ESTATE'; else $tipebudget='';
	/******************8update by Ginting*******/
	if ($tipebudget == 'M') $tipebudget = 'MILL';
	else if ($tipebudget == 'E') $tipebudget = 'ESTATE';
	else $tipebudget = $_SESSION['empl']['tipelokasitugas'];
	/******************************************/
	//    $tipebudget=$_POST['tipebudget'];
	//    $tahunbudget=$_POST['tahunbudget'];
	$pilihtahun2 = $_POST['pilihtahun2'];
	$kodeorg = substr($_SESSION['empl']['lokasitugas'], 0, 4);
	$hkef = '';
	$hkef .= "<input type=hidden id=hidden2 name=hidden2 value=\"\">";
	$hkef .= "<table id=container5 class=sortable cellspacing=1 border=0 cellpadding=5>
     <thead>
        <tr id=baris_0 name=baris_0>
            <td align=center>No</td>
            <td align=center width=50px>" . $_SESSION['lang']['budgetyear'] . "</td>
            <td align=center>" . $_SESSION['lang']['kodeanggaran'] . "</td>
            <td align=center>" . $_SESSION['lang']['tipeanggaran'] . "</td>
            <td align=center>" . $_SESSION['lang']['noakun'] . "</td>
            <td align=center>" . $_SESSION['lang']['totalbiaya'] . "</td>
       </tr>  
     </thead>
     <tbody>";

	$str = "select kunci,tahunbudget,kodebudget,tipebudget,noakun,sum(rupiah) as rupiah from " . $dbname . ".bgt_budget
        where tutup=0 and kodebudget like 'UMUM%' and tipebudget = '" . $tipebudget . "' and kodeorg = '" . $kodeorg . "' and tahunbudget like '%" . $pilihtahun2 . "%' and pta='BGT' group by noakun order by tahunbudget desc,noakun";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$no = 1;
	while ($bar = $res->fetch()) {
		$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $bar->noakun . "'");
		$hkef .= "<tr id=baris_" . $no . " class=rowcontent>
            <td align=center><input type=hidden id=kunci_" . $no . " name=kunci_" . $no . " value=" . $bar->kunci . ">" . $no . "</td>
            <td align=center>" . $bar->tahunbudget . "</td>
            <td align=center>" . $bar->kodebudget . "</td>
            <td align=center>" . $bar->tipebudget . "</td>
            <td align=left>" . $bar->noakun . " - " . $nmakun[$bar->noakun] . "</td>
            <td align=right>" . number_format($bar->rupiah) . "</td>
       </tr>";

		$no += 1;
		@$total += $bar->rupiah;
	}


	$hkef .= "<tr class=rowcontent>
			<td align=center colspan=5><b>T O T A L</b></td>
			<td align=right><b>" . number_format($total) . "</b></td>
			</tr>";
	echo $hkef;


	echo "</tbody>
     <tfoot>
     </tfoot>		 
     </table>";
}
if ($cekapa == 'insertDistribusi') {
	for ($a = 1; $a <= 12; $a++) {
		if ($_POST['arrBrt'][$a] == '') {
			$_POST['arrBrt'][$a] = 0;
		}
		$totalSum += $_POST['arrBrt'][$a];
	}
	if ($totalSum > $_POST['totalSetahn']) {
		exit("Error : Total mothly (" . $totalSum . ") greater than annualy (" . $_POST['totalSetahn'] . ") ");
	}
	$sCek = "select distinct kunci from " . $dbname . ".bgt_distribusi  where kunci='" . $_POST['kunci'] . "'";
	$qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
	$numrows = owlBaris($qCek);
	$rCek = $numrows;
	if ($rCek > 0) {
		$sUpdate = "update " . $dbname . ".bgt_distribusi set updateby='" . $_SESSION['standard']['userid'] . "'";
		for ($art = 1; $art <= 12; $art++) {
			if (strlen($art) == '1') {
				$ccrt = "0" . $art;
			} else {
				$ccrt = $art;
			}

			$sUpdate .= " ,rp" . $ccrt . "='" . $_POST['arrBrt'][$art] . "'";
		}
		$sUpdate .= " where  kunci='" . $_POST['kunci'] . "'";
		try {
			$owlPDO->exec($sUpdate);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	} else {
		$sInsert = "insert into " . $dbname . ".bgt_distribusi (kunci, updateby,rp01, rp02, rp03, rp04, rp05, rp06, rp07, rp08, rp09,  rp10, rp11,  rp12 )";
		$sInsert .= " values ('" . $_POST['kunci'] . "','" . $_SESSION['standard']['userid'] . "'";
		for ($arb = 1; $arb <= 12; $arb++) {
			$sInsert .= ",'" . $_POST['arrBrt'][$arb] . "'";
		}
		$sInsert .= ")";
		try {
			$owlPDO->exec($sInsert);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
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
?>