<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$method = checkPostGet('method', '');
$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

switch ($method) {
	case 'preview':
		$str = "select *  from " . $dbname . ".sdm_ho_component";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$idcomp[$bar['id']] = $bar['name'];
			$plus[$bar['id']] = $bar['plus'];
		}

		$str = "select *  from " . $dbname . ".bgt_regional_assignment";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmreg[$bar['kodeunit']] = $bar['subregional'];
		}

		$str = "select *  from " . $dbname . ".sdm_5jabatan";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmjab[$bar['kodejabatan']] = $bar['namajabatan'];
		}
		$str = "select *  from " . $dbname . ".sdm_5tipekaryawan";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tipekar[$bar['id']] = $bar['tipe'];
		}
		$str = "select *  from " . $dbname . ".sdm_5departemen";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmdept[$bar['kode']] = $bar['nama'];
		}
		$str = "select *  from " . $dbname . ".sdm_5golongan";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmgol[$bar['kodegolongan']] = $bar['namagolongan'];
		}

		$str = "select *  from " . $dbname . ".sdm_5pendidikan";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmpdd[$bar['levelpendidikan']] = $bar['pendidikan'];
		}
		$str = "select *  from " . $dbname . ".sdm_5suku";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmsuku[$bar['idsuku']] = $bar['namasuku'];
		}
		$str = "select *  from " . $dbname . ".provinsi";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$prov[$bar['id']] = $bar['provinsi'];
		}
		$str = "select *  from " . $dbname . ".kabupaten";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kab[$bar['id']] = $bar['kabupaten'];
		}
		$str = "select *  from " . $dbname . ".keu_5daftarbank";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nmbank[$bar['kodebank']] = $bar['namabank'];
		}
		$kab["LOKAL"] = "LOKAL";


		$datatipe = array();
		$strx = "select * from " . $dbname . ".sdm_5tipekaryawan where aktif='1'";
		//echo $strx;
		$resn = fetchdata($strx);
		foreach ($resn as $bar) {
			$datatipe[$bar['id']] = $bar['tipe'];
		}

		$datacountmax = intval(count($datatipe));
		$datacount = intval(count($datatipe) / 2);



		$tab .= "<table id=mytable class='sortable nowrap' cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['nourut']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['nik']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['namakaryawan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['jabatan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['kodegolongan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tipekaryawan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['status'] . " " . $_SESSION['lang']['karyawan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['regional']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['pt']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['lokasitugas']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['divisi']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['departemen']) . "</th>
				<th style='text-align:center;'>SUB " . strtoupper($_SESSION['lang']['departemen']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['poh']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['noktp']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['nokk']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['pendidikan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['alokasi']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['statuspajak']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['statusperkawinan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tanggalmenikah']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['jumlahanak']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tanggalmasuk']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tanggalpengangkatan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tanggalselesai']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tempatlahir']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['tanggallahir']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['umur']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['umur']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['warganegara']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['jeniskelamin']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['agama']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['golongandarah']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['provinsi']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['kabupaten'] . " / " . $_SESSION['lang']['kota']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['kecamatan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['desa'] . " / " . $_SESSION['lang']['kelurahan']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['kodepos']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['alamat']) . " KTP</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['telp']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['nohp']) . " (1)</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['nohp']) . " (2)</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['notelepondarurat']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['email']) . " PRIBADI</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['email']) . " KANTOR</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['rekening']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['norekeningbank']) . "</th>
				<th style='text-align:center;'>A/N REKENING BANK</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['sistemgaji']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['npwp']) . "</th>
				<th style='text-align:center;'>BPJS KES</th>
				<th style='text-align:center;'>BPJS TK</th>
				<th style='text-align:center;'>BPJS JP</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['jumlahtanggunganbpjs']) . "</th>
				<th style='text-align:center;'>" . strtoupper($_SESSION['lang']['suku']) . "</th>
				<th style='text-align:center;'>SIM</th>
				<th style='text-align:center;'>KODE CATU</th>
				<th style='text-align:center;'>NIK SEBELUMNYA</th>
			</tr>
		</thead>
		<tbody >";



		if ($param['periode'] == '') {
			exit("Warning : Periode harus dipilih.");
		}




		$where = "";
		if ($param['tipekaryawan'] != '') {
			$where .= " and tipekaryawan='" . $param['tipekaryawan'] . "'";
		}

		if ($param['kodeorg'] != '') {
			$where .= " and lokasitugas='" . $param['kodeorg'] . "'";
		} else {
			$where .= "and lokasitugas in (" . getOrgDetail(2) . ")";
		}


		## Cek datakaryawan bulanan
		$str = "select * from " . $dbname . ".datakaryawan_hist where 1=1 and version_type='B' and periodegaji='" . $param['periode'] . "' " . $where . " order by namakaryawan";
		$res = fetchdata($str);
		if (count($res) > 0) {
			$table = "datakaryawan_hist";
			$where .= " and version_type='B' and periodegaji='" . $param['periode'] . "'";
		} else {
			$table = "datakaryawan";
		}

		$str = "select *  from " . $dbname . ".kecamatan where idkec in (select kecamatan from " . $dbname . "." . $table . " where 1=1 " . $where . ")";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kec[$bar['idkec']] = $bar['kecamatan'];
		}

		$str = "select *  from " . $dbname . ".desa where iddes in (select desa from " . $dbname . "." . $table . " where 1=1 " . $where . ")";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$des[$bar['iddes']] = $bar['desa'];
		}

		$optjk = array("L" => "Laki - Laki", "P" => "Perempuan");
		$nmalokasi = array("0" => "UNIT", "1" => "UMUM");
		$country   = readCountry("./config/country.lst");
		for ($x = 0; $x < count($country); $x++) {
			$nmnegara[$country[$x][2]] = $country[$x][0];
		}

		$periodeLalu = periodelalu($param['periode']);
		$optNIKLAMA = makeOption($dbname, 'datakaryawan_hist', 'karyawanid,nik', " version_type='B' and periodegaji='" . $periodeLalu . "' ");

		$data = array();
		$str = "select * from " . $dbname . "." . $table . " where 1=1 " . $where . " and namakaryawan not like '%ADMINISTRATOR%' order by namakaryawan";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$no++;
			if ($bar['tanggalmasuk'] == "0000-00-00") {
				$bar['tanggalmasuk'] = "";
			}
			if ($bar['tanggalpengangkatan'] == "0000-00-00") {
				$bar['tanggalpengangkatan'] = "";
			}
			if ($bar['tanggalkeluar'] == "0000-00-00") {
				$bar['tanggalkeluar'] = "";
			}
			if ($bar['tanggallahir'] == "0000-00-00") {
				$bar['tanggallahir'] = "";
			}
			if ($bar['tanggalmenikah'] == "0000-00-00") {
				$bar['tanggalmenikah'] = "";
			}

			if ($bar['noktp'] != '') {
				$bar['noktp'] = "'" . $bar['noktp'];
			}
			if ($bar['no_keluarga'] != '') {
				$bar['no_keluarga'] = "'" . $bar['no_keluarga'];
			}
			if ($bar['subbagian'] != '') {
				$subbagian = $bar['subbagian'] . " - " . getNamaOrg($bar['subbagian']);
			} else {
				$subbagian = "";
			}

			$days = $months = $years = $hari = $jam = $menit = 0;
			$waktuawal = $bar['tanggallahir'];
			$waktuakhir = date("Y-m-d");
			$diff      = (strtotime($waktuakhir) - strtotime($waktuawal));
			$years     = floor($diff / (365 * 60 * 60 * 24));
			$months    = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
			$days      = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

			$hari      = floor($diff / (60 * 60 * 24));
			$jam       = floor(($diff - ($hari * (60 * 60 * 24))) / (60 * 60));
			$menit     = floor(($diff - (($hari * (60 * 60 * 24)) + ($jam * (60 * 60)))) / 60);

			$data[] = array(
				$no,
				strtoupper($bar['nik']),
				strtoupper($bar['namakaryawan']),
				strtoupper($nmjab[$bar['kodejabatan']]),
				strtoupper($nmgol[$bar['kodegolongan']]),
				strtoupper($tipekar[$bar['tipekaryawan']]),
				strtoupper($bar['statuskaryawan']),
				strtoupper($nmreg[$bar['lokasitugas']]),
				strtoupper(getNamaOrg($bar['kodeorganisasi'])),
				strtoupper(getNamaOrg($bar['lokasitugas'])),
				strtoupper($subbagian),
				strtoupper($nmdept[$bar['bagian']]),
				strtoupper($nmdept[$bar['subdept']]),
				strtoupper($kab[$bar['lokasipenerimaan']]),
				strtoupper(strval($bar['noktp'])),
				strtoupper($bar['no_keluarga']),
				strtoupper($nmpdd[$bar['levelpendidikan']]),
				strtoupper($nmalokasi[$bar['alokasi']]),
				strtoupper($bar['statuspajak']),
				strtoupper($bar['statusperkawinan']),
				strtoupper($bar['tanggalmenikah']),
				strtoupper($bar['jumlahanak']),
				strtoupper($bar['tanggalmasuk']),
				strtoupper($bar['tanggalpengangkatan']),
				strtoupper($bar['tanggalkeluar']),
				strtoupper($bar['tempatlahir']),
				strtoupper($bar['tanggallahir']),
				$years,
				$years . " tahun, " . $months . " bulan, " . $days . " hari",
				strtoupper($nmnegara[$bar['warganegara']]),
				strtoupper($optjk[$bar['jeniskelamin']]),
				strtoupper($bar['agama']),
				strtoupper($bar['golongandarah']),
				strtoupper($prov[$bar['provinsi']]),
				strtoupper($kab[$bar['kabupaten']]),
				strtoupper($kec[$bar['kecamatan']]),
				strtoupper($des[$bar['desa']]),
				strtoupper($bar['kodepos']),
				strtoupper($bar['alamataktif']),
				strtoupper($bar['noteleponrumah']),
				strtoupper($bar['nohp']),
				strtoupper($bar['nohp2']),
				strtoupper($bar['notelepondarurat']),
				$bar['email'],
				$bar['emailkantor'],
				strtoupper($nmbank[$bar['namabank']]),
				strtoupper($bar['norekeningbank']),
				$bar['pemilikrekening'],
				strtoupper($bar['sistemgaji']),
				strtoupper($bar['npwp']),
				strtoupper($bar['bpjs']),
				strtoupper($bar['jms']),
				strtoupper($bar['pensiun']),
				strtoupper($bar['jumlahtanggungan']),
				strtoupper($nmsuku[$bar['suku']]),
				strtoupper($bar['sim']),
				strtoupper($bar['kodecatu']),
				strtoupper($optNIKLAMA[$bar['karyawanid']])
			);
		}

		$tab .= "</tbody>
		<tfoot>
		</tfoot>
		</table>";

		echo "<fieldset style='float:left;'>
        <legend>Info</legend>";
		$datatab = '';
		$jumlahaktif = array();
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows = owlBaris($res);
		// exit("warning : ".$numrows." ");
		if ($numrows < 1) {
			// $datatab= "###";
			$nox = 0;
			foreach ($datatipe as $tipekar => $textdata) {
				if (!isset($jumlahaktif[$tipekar])) {
					$jumlahaktif[$tipekar] = 0;
				}
				if ($nox == $datacount) {
					$datacountmax = $datacountmax - $datacount;
					$datacount = $datacountmax;
					$datatab .= ", ";
					$nox = 0;
				}

				if ($nox == 0) {
					$datatab .= "<b> " . $textdata . " </b> Aktif : " . $jumlahaktif[$tipekar] . " Orang";
				} else {
					$datatab .= ",  " . $textdata . " Aktif : " . $jumlahaktif[$tipekar] . " Orang";
				}
				$nox++;
			}
		} else {
			while ($bar = $res->fetch()) {
				if ($param['tgl1'] != "") {
					if ($bar->tanggalkeluar == '0000-00-00' || $bar->tanggalkeluar > tanggalsystemn($param['tgl1'])) {
						$valueTglKeluar = '-';
						if (!isset($jumlahaktif[$bar->tipekaryawan])) {
							$jumlahaktif[$bar->tipekaryawan] = 0;
						}
						$jumlahaktif[$bar->tipekaryawan] += 1;
					}
				} else {
					if ($bar->tanggalkeluar == '0000-00-00') {
						$valueTglKeluar = '-';
						if (!isset($jumlahaktif[$bar->tipekaryawan])) {
							$jumlahaktif[$bar->tipekaryawan] = 0;
						}
						$jumlahaktif[$bar->tipekaryawan] += 1;
					}
				}
			}
			// $datatab= "###";
			$nox = 0;
			foreach ($datatipe as $tipekar => $textdata) {
				if (!isset($jumlahaktif[$tipekar])) {
					$jumlahaktif[$tipekar] = 0;
				}
				if ($nox == $datacount) {
					$datacountmax = $datacountmax - $datacount;
					$datacount = $datacountmax;
					$datatab .= ", ";
					$nox = 0;
				}

				if ($nox == 0) {
					$datatab .= "<b> " . $textdata . " </b> Aktif : " . $jumlahaktif[$tipekar] . " Orang";
				} else {
					$datatab .= ",  <b> " . $textdata . " </b> Aktif : " . $jumlahaktif[$tipekar] . " Orang";
				}
				$nox++;
			}
		}

		$datatab1 = '';
		$jumlahaktif = array();
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows = owlBaris($res);
		if ($numrows < 1) {
			// $datatab1= "###";
			$nox = 0;
			foreach ($datatipe as $tipekar => $textdata) {
				if (!isset($jumlahaktif[$tipekar])) {
					$jumlahaktif[$tipekar] = 0;
				}
				if ($nox == $datacount) {
					$datacountmax = $datacountmax - $datacount;
					$datacount = $datacountmax;
					$datatab1 .= ", ";
					$nox = 0;
				}

				if ($nox == 0) {
					$datatab1 .= "<b> " . $textdata . " </b> Tidak Aktif : " . $jumlahaktif[$tipekar] . " Orang";
				} else {
					$datatab1 .= ", <b> " . $textdata . " </b> Tidak Aktif : " . $jumlahaktif[$tipekar] . " Orang";
				}
				$nox++;
			}
		} else {
			while ($bar = $res->fetch()) {
				if ($param['tgl1'] != "") {
					if ($bar->tanggalkeluar != '0000-00-00') {
						if ($bar->tanggalkeluar <= tanggalsystemn($param['tgl1'])) {
							$valueTglKeluar = '-';
							if (!isset($jumlahaktif[$bar->tipekaryawan])) {
								$jumlahaktif[$bar->tipekaryawan] = 0;
							}
							$jumlahaktif[$bar->tipekaryawan] += 1;
						}
					}
				} else {
					if ($bar->tanggalkeluar != '0000-00-00') {
						$valueTglKeluar = '-';
						if (!isset($jumlahaktif[$bar->tipekaryawan])) {
							$jumlahaktif[$bar->tipekaryawan] = 0;
						}
						$jumlahaktif[$bar->tipekaryawan] += 1;
					}
				}
			}
			// $datatab1= "###";
			$nox = 0;
			foreach ($datatipe as $tipekar => $textdata) {
				if (!isset($jumlahaktif[$tipekar])) {
					$jumlahaktif[$tipekar] = 0;
				}
				if ($nox == $datacount) {
					$datacountmax = $datacountmax - $datacount;
					$datacount = $datacountmax;
					$datatab1 .= ", ";
					$nox = 0;
				}

				if ($nox == 0) {
					$datatab1 .= "<b> " . $textdata . " </b> Tidak Aktif : " . $jumlahaktif[$tipekar] . " Orang";
				} else {
					$datatab1 .= ",  <b> " . $textdata . " </b> Tidak Aktif : " . $jumlahaktif[$tipekar] . " Orang";
				}
				$nox++;
			}
		}


		// echo $datatab;
		// echo "</br>";
		// echo $datatab1;
		// echo "</fieldset>";
		echo $tab . "####" . json_encode($data);
		break;

	case 'excel':
		$nop = "csbm.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet('csbm', $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
		break;
}

function nantozero($e, $i = 0)
{
	if (is_nan($e)) {
		$e = 0;
	} else {
		$e = $e;
	}
	return number_format($e, $i);
}

function numb_format($a, $d = 0)
{
	$n = hidezerodecimal($a, $d);
	#$n = number_format($a,$d);
	if ($n == '0' or $n == '') {
		$n = "";
	} else {
		$n = $n;
	}
	return $n;
}
function bagi($e, $i)
{
	if ($i != '' and $i != '0') {
		$n = $e / $i;
	} else {
		$n = 0;
	}
	return $n;
}
