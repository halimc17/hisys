<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$method       = checkPostGet('method', '');
$picpic       = checkPostGet('picpic', '');
$kodebarang   = checkPostGet('kodebarang', '');
$qty          = checkPostGet('qty', '');
$qtypic          = checkPostGet('qtypic', '');
$nodok        = checkPostGet('nodok', '');
$norequest    = checkPostGet('norequest', '');
$gudang       = checkPostGet('gudang', '');
$pemilikbarang = checkPostGet('pemilikbarang', '');
$urut         = checkPostGet('urut', '');
$untukunit    = checkPostGet('untukunit', '');
$subbagian    = checkPostGet('subunit', '');
$crnorequest  = checkPostGet('crnorequest', '');
$qtypresentase  = checkPostGet('qtypresentase', '');
$today        = date("Y-m-d");

switch ($method) {
	case 'getpicform':
		$tab = "";

		$tab .= "<table class=sortable border=0 cellspacing=1 cellpadding=5>
		<thead> 
		<tr>
			<th align=center>#</th>
			<th align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
			<th align=center>Presentase Harga (%)</th>
			<th align=center>Jumlah Pemakaian</th>
			<th align=center style='display:none'>#</th>
		</tr>
		</thead>
		<tbody id='trpic2'>";
		$no = 0;

		foreach ($_SESSION['pic2'] as $key => $row) {
			if ($row['kodebarang'] == $kodebarang) {
				$str = "select karyawanid, namakaryawan, nik, subbagian, lokasitugas from " . $dbname . ".datakaryawan where (tanggalkeluar>= '" . $today . "' or tanggalkeluar = '0000-00-00') and karyawanid='" . $row['picpic'] . "' order by namakaryawan";
				$res = fetchData($str);
				if ($res[0]['subbagian'] == '') {
					$res[0]['subbagian'] = $res[0]['lokasitugas'];
				}
				$nmkaryawan = ($res[0]['karyawanid'] == '' ? '' : $res[0]['namakaryawan'] . " [" . $res[0]['subbagian'] . "]");

				$no++;
				$tab .= "<tr class='rowcontent'>";
				$tab .= "<td style='text-align:right'>" . $no . "</td>";
				$tab .= "<td>" . $nmkaryawan . " - " . $res[0]['nik'] . "</td>";
				$tab .= "<td>" . $row['qtypresentase'] . "</td>";
				$tab .= "<td>" . $row['jumlah'] . "</td>";
				$tab .= "<td style='text-align:center;display:none'>
					<img title='Delete' class=resicon onclick=\"deletepic2('" . $kodebarang . "','" . $qty . "','" . $row['picpic'] . "')\" src='images/delete_32.png'/>
				</td>";
				$tab .= "</tr>";
			}
		}
		$tab .= "</tbody>";

		##LIST KARYAWAN##
		$optKaryawan = "<option value=''>&nbsp;</option>";

		// cek inti atau plasma
		$str = "select inti from " . $dbname . ".organisasi where kodeorganisasi = '" . $untukunit . "' ";
		$res = fetchData($str);
		$tipeeee = $res[0]['inti'];

		if ($tipeeee == '0') {
			$str = "select karyawanid, namakaryawan, nik, subbagian, lokasitugas from " . $dbname . ".datakaryawan where (tanggalkeluar >= '" . $today . "' or tanggalkeluar = '0000-00-00') and lokasitugas IN (select kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)='4' and induk='" . getindukPT($untukunit) . "' ) order by subbagian, namakaryawan";
		} else {
			$str = "select karyawanid, namakaryawan, nik, subbagian, lokasitugas from " . $dbname . ".datakaryawan where (tanggalkeluar >= '" . $today . "' or tanggalkeluar = '0000-00-00') and lokasitugas = '" . $untukunit . "' and subbagian = '" . $subbagian . "' order by subbagian, namakaryawan";
		}
		$res = fetchData($str);
		$no_p = 0;
		$jlhkolom = count($res);

		foreach ($res as $key => $val) {
			$is_checked = "";
			$is_disabled = "disabled";
			$nilai_x = 0;
			$nilai_x_pemakaian = 0;

			// Cek apakah karyawanid ada di dalam $_SESSION['pic2'] dan dapatkan nilai qtypresentase jika ada
			if (isset($_SESSION['pic2'])) {
				foreach ($_SESSION['pic2'] as $pic2) {
					if ($pic2['picpic'] == $val['karyawanid']) {
						$is_checked = "checked";
						$is_disabled = "";
						$nilai_x = $pic2['qtypresentase'];
						$nilai_x_pemakaian = $pic2['jumlah'];
						break;
					}
				}
			}

			$no_p++;
			$tab .= "<tr class='rowcontent'>
				<td>
					<input type='checkbox' size='5' maxlength='3' id='pic_p_" . $no_p . "' onclick=\"ceklis_P('" . $no_p . "', '" . $jlhkolom . "')\" " . $is_checked . "> 
				</td>
				<td>
					<select id='picpic_" . $no_p . "' class='select2' style='width:150px;'>
						<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . " - " . $val['nik'] . "</option>
					</select>
				</td>
				<td>
					<input type='text' size='5' maxlength='3' id='qtypresentase_" . $no_p . "' value='" . $nilai_x . "' class='myinputtextnumber' " . $is_disabled . " oninput='validateQty(this, " . $no_p . ", " . $jlhkolom . ");' onkeypress='return angka_doang(event);'>
				</td>
				<td>
					<input type='text' size='5' maxlength='3' id='qtypemakaian_" . $no_p . "' value='" . $nilai_x_pemakaian . "' class='myinputtextnumber' " . $is_disabled . " oninput='validateQty(this, " . $no_p . ", " . $jlhkolom . ");' onkeypress='return angka_doang(event);'>
				</td>
				<td style='display:none'></td>
			</tr>";
		}

		$tab .= "
	<tr class=rowcontent>
		<td style='text-align:center' colspan=5>
		<button class=mybutton onclick=\"addpic2(" . $no_p . ");\">Simpan</button
		</td>
	</tr>
	";
		$tab .= "</table>";
		// $optKaryawan = "<option value=''>&nbsp;</option>";
		// $str="select karyawanid, namakaryawan, subbagian,lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar>= '".$today."' or tanggalkeluar = '0000-00-00') and lokasitugas = '".$untukunit."' order by subbagian, namakaryawan";
		// $res=fetchData($str);
		// foreach($res as $key=>$val){
		// 	if($val['subbagian']==''){
		// 		$val['subbagian']=$val['lokasitugas'];
		// 	}
		// 	$d=$val['subbagian'];
		// 	if($d!=$n){			
		// 		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		// 		$optKaryawan.="<optgroup label='".$nmorg[$d]."'>";
		// 	}

		// 	$optKaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['subbagian']."]</option>";
		// 	$n=$d;
		// 	if($d!=$n){
		// 		$optKaryawan.="</optgroup>";
		// 	}
		// }

		// $tab.="<tr class=rowcontent>
		// 	<td></td>
		// 	<td>
		// 		<select id=picpic class=select2 style='width:150px;'>".$optKaryawan."</select>
		// 	</td>
		// 	<td>
		// 		<input type=text size=5 maxlength=3 id=qtypresentase value=0 class=myinputtextnumber onkeypress=\"return angka_doang(event);\">
		// 	</td>
		// 	<td></td>
		// 	<td style='text-align:center'>
		// 		<img src='images/plus.png' class='resicon' title='Add PIC/Departement' onclick=\"addpic2();\">
		// 	</td>
		// </tr>
		// </table>";

		echo $tab;
		break;

	case 'addpic2':
		$picpic       = checkPostGet('picpic', '');
		$qtypresentase  = checkPostGet('qtypresentase', '');
		$qtypic          = checkPostGet('qtypic', '');
		$t_dataa = count($picpic) - 1;

		if ($_SESSION['pic2'] != array()) {
			unset($_SESSION['pic2']);
			$_SESSION['pic2'] = array();
		} else {
			$_SESSION['pic2'] = array();
		}

		for ($i = 0; $i <= $t_dataa; $i++) {
			$newdata = array(
				'kodebarang' => $kodebarang,
				'qty' => $qty,
				'picpic' => $picpic[$i],
				'qtypresentase' => $qtypresentase[$i],
				'jumlah' => $qtypic[$i]
			);

			if ($_SESSION['pic2'] != array()) {
				foreach ($_SESSION['pic2'] as $key => $row) {
					if ($row['kodebarang'] == $kodebarang && $row['picpic'] == $picpic[$i]) {
						// unset($_SESSION['pic2'][$key]);
						// exit("Warning : Item ini sudah pernah diinput sebelumnya.");
					}
				}

				$totalqty = 0;
				foreach ($_SESSION['pic2'] as $key => $row) {
					if ($row['kodebarang'] == $kodebarang) {
						$totalqty = $totalqty + $row['qtypresentase'];
					}
				}

				if ($qtypresentase[$i] <= 0) {
					exit("Warning : Presentase tidak boleh 0");
				}

				if (($totalqty + $qtypresentase[$i]) > 100) {
					exit("Warning : Jumlah presentase max 100");
				}

				array_push($_SESSION['pic2'], $newdata);
			} else {
				array_push($_SESSION['pic2'], $newdata);
			}
		}


		$no = 0;
		foreach ($_SESSION['pic2'] as $key => $row) {
			if ($row['kodebarang'] == $kodebarang) {
				$str = "select karyawanid, namakaryawan, subbagian, lokasitugas from " . $dbname . ".datakaryawan where (tanggalkeluar>= '" . $today . "' or tanggalkeluar = '0000-00-00') and statuskaryawan != 'Keluar' and karyawanid='" . $row['picpic'] . "' order by namakaryawan";
				$res = fetchData($str);
				if ($res[0]['subbagian'] == '') {
					$res[0]['subbagian'] = $res[0]['lokasitugas'];
				}
				$nmkaryawan = ($res[0]['karyawanid'] == '' ? '' : $res[0]['namakaryawan'] . " [" . $res[0]['subbagian'] . "]");
				$nmkaryawan2 = ($res[0]['karyawanid'] == '' ? '' : $res[0]['namakaryawan']);

				$no++;
				$tab .= "<tr class='rowcontent'>";
				$tab .= "<td style='text-align:right'>" . $no . "</td>";
				$tab .= "<td>" . $nmkaryawan . "</td>";
				$tab .= "<td>" . $row['qtypresentase'] . "</td>";
				$tab .= "<td>" . $row['jumlah'] . "</td>";
				$tab .= "<td style='text-align:center;display:none'>
					<img title='Delete' class=resicon onclick=\"deletepic2('" . $kodebarang . "','" . $qty . "','" . $row['picpic'] . "')\" src='images/delete_32.png'/
				</td>";
				$tab .= "</tr>";

				$tab2 .= "<tr class='rowcontent'>";
				$tab2 .= "<td>" . $no . ". " . ($nmkaryawan2) . "</td>";
				$tab2 .= "</tr>";
			}
		}

		echo $tab . "####" . $tab2;
		break;

	case 'deletepic2':
		foreach ($_SESSION['pic2'] as $key => $row) {
			if ($row['kodebarang'] == $kodebarang && $row['picpic'] == $picpic) {
				unset($_SESSION['pic2'][$key]);
			}
		}

		$no = 0;
		foreach ($_SESSION['pic2'] as $key => $row) {
			if ($row['kodebarang'] == $kodebarang) {
				$str = "select karyawanid, namakaryawan, subbagian, lokasitugas from " . $dbname . ".datakaryawan where (tanggalkeluar>= '" . $today . "' or tanggalkeluar = '0000-00-00') and statuskaryawan != 'Keluar' and karyawanid='" . $row['picpic'] . "' order by namakaryawan";
				$res = fetchData($str);
				if ($res[0]['subbagian'] == '') {
					$res[0]['subbagian'] = $res[0]['lokasitugas'];
				}
				$nmkaryawan = ($res[0]['karyawanid'] == '' ? '' : $res[0]['namakaryawan'] . " [" . $res[0]['subbagian'] . "]");
				$nmkaryawan2 = ($res[0]['karyawanid'] == '' ? '' : $res[0]['namakaryawan']);


				$no++;
				$tab .= "<tr class='rowcontent'>";
				$tab .= "<td style='text-align:right'>" . $no . "</td>";
				$tab .= "<td>" . $nmkaryawan . "</td>";
				$tab .= "<td>" . $row['qtypresentase'] . "</td>";
				$tab .= "<td>" . $row['jumlah'] . "</td>";
				$tab .= "<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepic2('" . $kodebarang . "','" . $qty . "','" . $row['picpic'] . "')\" src='images/delete_32.png'/
				</td>";
				$tab .= "</tr>";

				$tab2 .= "<tr class='rowcontent'>";
				$tab2 .= "<td>" . $no . ". " . ($nmkaryawan2) . "</td>";
				$tab2 .= "</tr>";
			}
		}

		echo $tab . "####" . $tab2;
		break;

	case 'nextItem':
		$_SESSION['pic2'] = array();
		break;

	case 'editBast':
		$_SESSION['pic2'] = array();
		if ($norequest == '') {
			$where = " notransaksi='" . $nodok . "' and kodebarang='" . $kodebarang . "'";
		} else {
			$where = " notransaksi='" . $norequest . "' and kodebarang='" . $kodebarang . "' and realisasi!='0'";
		}
		$str = "select * from " . $dbname . ".log_pemakaianpresentase where " . $where . "";
		$res = fetchData($str);
		$no = 0;
		foreach ($res as $key => $val) {
			$no++;
			$optNmKary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $val['karyawanid'] . "'");

			$tab .= "<tr class='rowcontent'>
				<td>" . $no . ". " . ($optNmKary[$val['karyawanid']]) . "</td>
			</tr>";

			$newdata = array(
				'kodebarang' => $kodebarang,
				'qty' => $qty,
				'picpic' => $val['karyawanid'],
				'qtypresentase' => $val['presentase']
			);
			array_push($_SESSION['pic2'], $newdata);
		}

		echo $tab;
		break;

	case 'searchrequest':
		$tab = "<table>
			<tr>
				<td>Cari No. Request</td>
				<td>:</td>
				<td>
					<input type=text id=crnorequest size=25 style=width:100px class=myinputtext>
				</td>
				<td>
					<button onclick=carinorequest() class=mybutton>" . $_SESSION['lang']['find'] . "</button>
				</td>
			</tr>
		</table><hr>";

		$tab .= "<div id='listnorequest'><table class=sortable border=0 cellspacing=1 cellpadding=5>
		<thead> 
		<tr>
			<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
			<td align=center>No. Request</td>
			<td align=center>" . $_SESSION['lang']['unit'] . "</td>
		</tr>
		</thead>
		<tbody>";

		$str = "select * from " . $dbname . ".log_permintaanht where notransaksi not in (select notransaksi from " . $dbname . ".log_pemakaianpresentase where realisasi!='0') order by notransaksi";
		$res = fetchData($str);
		$no = 0;
		foreach ($res as $key => $val) {
			$no++;
			$tab .= "<tr class='rowcontent' style='cursor:pointer;' title='Show Detail' onclick=\"showdetail('" . $val['notransaksi'] . "')\">";
			$tab .= "<td style='text-align:right'>" . $no . "</td>";
			$tab .= "<td>" . $val['notransaksi'] . "</td>";
			$tab .= "<td>" . $val['untukunit'] . "</td>";
			$tab .= "</tr>";
		}
		$tab .= "</tbody>
		</table>
		</div>";
		echo $tab;
		break;

	case 'carinorequest':
		$tab .= "<table class=sortable border=0 cellspacing=1 cellpadding=5>
		<thead> 
		<tr>
			<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
			<td align=center>No. Request</td>
			<td align=center>" . $_SESSION['lang']['unit'] . "</td>
		</tr>
		</thead>
		<tbody>";

		$str = "select * from " . $dbname . ".log_permintaanht where notransaksi not in (select notransaksi from " . $dbname . ".log_pemakaianpresentase where realisasi!='0') and notransaksi like '%" . $crnorequest . "%' order by notransaksi";
		$res = fetchData($str);
		$no = 0;
		if (count($res) <= 0) {
			$tab .= "<tr class='rowcontent'>";
			$tab .= "<td colspan=3 style='text-align:center'>" . $_SESSION['lang']['datanotfound'] . "</td>";
			$tab .= "</tr>";
		} else {
			foreach ($res as $key => $val) {
				$no++;
				$tab .= "<tr class='rowcontent' style='cursor:pointer;' title='Show Detail' onclick=\"showdetail('" . $val['notransaksi'] . "')\">";
				$tab .= "<td style='text-align:right'>" . $no . "</td>";
				$tab .= "<td>" . $val['notransaksi'] . "</td>";
				$tab .= "<td>" . $val['untukunit'] . "</td>";
				$tab .= "</tr>";
			}
		}
		$tab .= "</tbody>
		</table>";
		echo $tab;
		break;

	case 'showdetail':
		$tab = "No. Request : " . $norequest;
		$_SESSION['pic2'] = array();

		$tab .= "<table class=sortable border=0 cellspacing=1>
		<thead> 
		<tr>
			<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
			<td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
			<td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
			<td align=center>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['stok'] . "</td>
			<td align=center>" . $_SESSION['lang']['jumlah'] . " Permintaan</td>
		</tr>
		</thead>
		<tbody>";

		$str = "select * from " . $dbname . ".log_permintaandt where notransaksi = '" . $norequest . "' order by kodebarang";
		$res = fetchData($str);
		$no = 0;
		foreach ($res as $key => $val) {
			$optNmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $val['kodebarang'] . "'");

			##ambil saldo barang##
			$saldoqty = 0;
			$str1 = "select saldoqty from " . $dbname . ".log_5masterbarangdt where kodebarang='" . $val['kodebarang'] . "' and kodegudang='" . $gudang . "'";
			$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while ($bar1 = $res1->fetch()) {
				$saldoqty = $bar1->saldoqty;
			}

			##ambil pengeluaran barang yang belum di posting##
			$qtynotposted = 0;
			$str2 = "select sum(b.jumlah) as jumlah,b.kodebarang FROM " . $dbname . ".log_transaksiht a left join " . $dbname . ".log_transaksidt
				   b on a.notransaksi=b.notransaksi where kodept='" . $pemilikbarang . "' and b.kodebarang='" . $val['kodebarang'] . "' 
				   and a.tipetransaksi>4
				   and a.kodegudang='" . $gudang . "'
				   and a.post=0
				   group by kodebarang";
			$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_OBJ);
			while ($bar2 = $res2->fetch()) {
				$qtynotposted = $bar2->jumlah;
			}
			if ($qtynotposted == '')
				$qtynotposted = 0;

			$saldoqty = $saldoqty - $qtynotposted;

			$no++;
			$tab .= "<tr class='rowcontent'>";
			$tab .= "<td style='text-align:right;vertical-align:top'>" . $no . "</td>";
			$tab .= "<td id='trkodebarang_" . $no . "' style='vertical-align:top'>" . $val['kodebarang'] . "</td>";
			$tab .= "<td style='vertical-align:top'>" . $optNmBarang[$val['kodebarang']] . "</td>";
			$tab .= "<td style='text-align:right;vertical-align:top'>
				<input type=text size=5 maxlength=10 id='jumlahstok_" . $no . "' value='" . $saldoqty . "' class=myinputtextnumber onkeypress=\"return angka_doang(event);\" disabled>
			</td>";
			$tab .= "<td style='text-align:right'>
				Total : <input type=text size=5 maxlength=10 id='jumlahpermintaan_" . $no . "' value='" . $val['jumlah'] . "' class=myinputtextnumber onkeypress=\"return angka_doang(event);\" disabled>";
			$tab .= "<div id='tddetailpic'>";
			$tab .= savesession($norequest, $val['kodebarang']);
			$tab .= getdetailpic($norequest, $val['kodebarang'], $no);
			$tab .= "</div>";
			$tab .= "</td>";
			$tab .= "</tr>";
		}
		$tab .= "<tr>
			<td colspan=5 class='rowcontent' style='text-align:center'>
				<button onclick=\"insertnorequest('" . $norequest . "','" . $no . "')\" class=mybutton>" . $_SESSION['lang']['save'] . "</button>
			</td>
		</tr></tbody>
		</table>";
		echo $tab;
		break;

	case 'deletepicrequest':
		foreach ($_SESSION['pic2'] as $key => $row) {
			if ($row['kodebarang'] == $kodebarang && $row['picpic'] == $picpic) {
				unset($_SESSION['pic2'][$key]);
			}
		}

		echo getdetailpic($norequest, $kodebarang, $urut);
		break;

	case 'insertnorequest':
		$msgError = "";
		$no = 0;
		$data = array();
		$data['head'] = array();
		$data['detail'] = array();

		$str = "select * from " . $dbname . ".log_permintaanht where notransaksi='" . $norequest . "'";
		$res = fetchData($str);
		$data['head']['keterangan'] = $res[0]['keterangan'];

		$no2 = 0;
		foreach ($_POST['kodebarang'] as $key => $val) {
			if ($_POST['jumlahstok'][$key] < $_POST['jumlahpermintaan'][$key] && $_POST['jumlahstok'][$key] > 0) {
				$no++;
				$optNmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $val . "'");
				if ($no == 1) {
					$msgError .= "\n* Jumlah Stok tidak mencukupi untuk jumlah permintaan\n";
				}
				$msgError .= "  - " . $optNmBarang[$val] . " : Stok = " . $_POST['jumlahstok'][$key] . " ; Permintaan : " . $_POST['jumlahpermintaan'][$key] . "\n";
			} else {
				if ($_POST['jumlahstok'][$key] > 0) {
					$str = "select * from " . $dbname . ".log_permintaandt where notransaksi='" . $norequest . "' and kodebarang='" . $val . "'";
					$res = fetchData($str);

					foreach ($res as $key2 => $val2) {
						$no2++;
						$d['kodebarang'] = $val2['kodebarang'];
						$d['satuan'] = $val2['satuan'];
						$d['jumlah'] = $_POST['jumlahpermintaan'][$key];
						$d['subunit'] = $val2['subunit'];
						$d['kodeblok'] = $val2['kodeblok'];
						$d['kodemesin'] = $val2['kodemesin'];
						$d['kodekegiatan'] = $val2['kodekegiatan'];
						$data['detail'][] = $d;
					}
				}
			}
		}

		//$datagroup[] = $data; 
		if ($msgError != '') {
			exit("Gagal : " . $msgError);
		}

		echo json_encode($data);
		break;
}

function getdetailpic($norequest, $kodebarang, $urut)
{
	global $dbname;
	global $owlPDO;

	$tab = "";

	$str = "select * from " . $dbname . ".log_pemakaianpresentase where notransaksi='" . $norequest . "' and kodebarang='" . $kodebarang . "'";
	$res = fetchData($str);
	if (count($res) > 0) {
		$tab .= "<table id='tablepicrequest_" . $kodebarang . "' class=sortable border=0 cellspacing=1>
			<thead> 
			<tr>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['untukunit'] . "</td>
				<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
				<td align=center>" . $_SESSION['lang']['action'] . "</td>
			</tr>
			</thead>
			<tbody>";
		$no2 = 0;

		foreach ($_SESSION['pic2'] as $key3 => $val3) {
			if ($val3['kodebarang'] == $kodebarang) {
				$optNmKary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $val3['picpic'] . "'");
				$optDep = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $val3['departemenpic'] . "'");
				$no2++;
				$tab .= "<tr class='rowcontent'>
					<td>" . $no2 . "</td>
					<td style='text-align:left'>" . ($optNmKary[$val3['picpic']] == '' ? $optDep[$val3['departemenpic']] : $optNmKary[$val3['picpic']]) . "</td>
					<td style='text-align:center'>
						<input type=text size=5 maxlength=10 id='jumlahpermintaanpic' value='" . $val3['qtypic'] . "' class=myinputtextnumber onkeypress=\"return angka_doang(event);\" disabled>
					</td>
					<td style='text-align:center'>
						<img title='Delete' class=resicon onclick=\"deletepicrequest('" . $norequest . "','" . $kodebarang . "','" . $val3['picpic'] . "','" . $val3['departemenpic'] . "','" . $val3['qtypic'] . "','" . $urut . "')\" src='images/delete_32.png'/>
					</td>
				</tr>";
			}
		}
		$tab .= "</tbody>
			</table>";
	}

	return $tab;
}

function savesession($norequest, $kodebarang)
{
	global $dbname;
	global $owlPDO;

	$tab = "";

	$str = "select * from " . $dbname . ".log_pemakaianpresentase where notransaksi='" . $norequest . "' and kodebarang='" . $kodebarang . "'";
	$res = fetchData($str);
	if (count($res) > 0) {
		foreach ($res as $key => $val) {
			$newdata = array(
				'kodebarang' => $val['kodebarang'],
				'picpic' => $val['karyawanid']
			);

			array_push($_SESSION['pic2'], $newdata);
		}
	}
	return;
}
