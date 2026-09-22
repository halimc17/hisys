<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/HtmlExcel.php');

$zel = makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');

if (count($_POST) > 0) {
	$param = $_POST;
} else {
	$param = $_GET;
}

$method      = checkPostGet('method', '');
$tpKary      = checkPostGet('tpKary', '');
$optThn      = checkPostGet('optThn', '');
$pilInp      = checkPostGet('pilInp', '');
$karyawanId  = checkPostGet('karyawanId', '');
$idKomponen  = checkPostGet('idKomponen', '');
$jmlhDt      = checkPostGet('jmlhDt', '');
$thn         = checkPostGet('thn', '');
$golongan    = checkPostGet('golongan', '');
$kdUnitCr    = checkPostGet('kdUnitCr', '');
$kdUnit      = checkPostGet('kdUnit', '');
$namaKary    = checkPostGet('namaKary', '');
$tpKaryCr    = checkPostGet('tpKaryCr', '');
$idKomponenCr = checkPostGet('idKomponenCr', '');
$idjabatan   = checkPostGet('idjabatan', '');
$vpage       = checkPostGet('vpage', '');
$page        = checkPostGet('page', '');

$optGol      = makeOption($dbname, 'datakaryawan', 'karyawanid,kodegolongan');
$optUnit     = makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');
$optTip      = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$optNikKar   = makeOption($dbname, 'datakaryawan', 'karyawanid,nik'); #sudah diakomodasi dengan left join pada display
$optNmKar    = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "alokasi=0");
$optTipe     = makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan');
$optJbtn     = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$optKomponen = makeOption($dbname, 'sdm_ho_component', 'id,name');

switch ($method) {
	case 'uploadData':
		if ($_FILES['file']['error'] == 0) {
			$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
			$file = $_FILES['file']['tmp_name'];
			if ($filetype == '.xlsx') {
				require_once 'dompdf/PHPExcel.php';
				require_once 'dompdf/PHPExcel/IOFactory.php';
				
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null, true, true, true);
				$firsturut1 = 1;
				$i = 1;
				$berhasil = 0;
				$gagal = 0;
				
				try {
					$owlPDO->beginTransaction();
					
					foreach ($sheets as $sheet) {
						if ($i > $firsturut1) {
							// A: Periode, B: Unit Kerja, C: NIK, D: Nama, E: Tipe, F: Gol, G: Jabatan, H: ID Komp, I: Jumlah Upah
							$periode = trim($sheet['A']);
							$kdOrg = trim($sheet['B']);
							$nik = trim($sheet['C']);
							// Extract ID from format "ID - Name"
							$idKompRaw = trim($sheet['H']);
							$idKompArr = explode(' - ', $idKompRaw);
							$idKomp = $idKompArr[0];
							$jumlah = trim($sheet['I']);
							
							if ($periode != '' && $nik != '') {
								$strc = "select karyawanid from " . $dbname . ".datakaryawan where nik='" . $nik . "' and lokasitugas='".$kdOrg."'";
								$resc = fetchdata($strc);
								if (count($resc) > 0) {
									$karyawanid = $resc[0]['karyawanid'];
									// Delete existing if any
									$id = "delete from " . $dbname . ".sdm_5gajipokok where tahun='" . $periode . "' and karyawanid='" . $karyawanid . "' and idkomponen='" . $idKomp . "'";
									$owlPDO->exec($id);
									
									if ($jumlah > 0) {
										$in = "insert into " . $dbname . ".sdm_5gajipokok (`tahun`, `karyawanid`, `idkomponen`, `jumlah`, `kodeorg`, `updateby`) 
										values ('" . $periode . "','" . $karyawanid . "','" . $idKomp . "','" . $jumlah . "','" . $kdOrg . "','" . $_SESSION['standard']['userid'] . "')";
										$owlPDO->exec($in);
									}
									$berhasil++;
								} else {
									$gagal++;
								}
							} else {
								break;
							}
						}
						$i++;
					}
					$owlPDO->commit();
					if($gagal > 0){
						exit("Warning : Berhasil diupload: ".$berhasil." data. Gagal: ".$gagal." data (NIK tidak ditemukan di unit tsb).");
					}
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Gagal, " . addslashes($e->getMessage());
				}
			} else {
				exit("Warning : Format file upload harus .xlsx");
			}
		} else {
			exit("Warning : Terjadi error pada file upload.");
		}
	break;

	case 'downloadTemplate':
		require_once 'dompdf/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', 'Periode (YYYY-MM)')
					->setCellValue('B1', 'Unit Kerja')
					->setCellValue('C1', 'NIK')
					->setCellValue('D1', 'Nama Karyawan')
					->setCellValue('E1', 'Tipe Karyawan')
					->setCellValue('F1', 'Golongan')
					->setCellValue('G1', 'Jabatan')
					->setCellValue('H1', 'ID Komponen')
					->setCellValue('I1', 'Jumlah Upah');

		$thn = checkPostGet('thn', '');
		$kdUnit = checkPostGet('kdUnit', '');
		$tpKary = checkPostGet('tpKary', '');
		$golongan = checkPostGet('golongan', '');
		$jabatan = checkPostGet('jabatan', '');
		$idKomponen = checkPostGet('idKomponen', '');
		$karyawanId = checkPostGet('karyawanId', '');

		$whrd = " and lokasitugas='" . $kdUnit . "'";
		if ($tpKary != '') $whrd .= " and tipekaryawan='" . $tpKary . "'";
		if ($golongan != '') $whrd .= " and kodegolongan='" . $golongan . "'";
		if ($jabatan != '') $whrd .= " and kodejabatan='" . $jabatan . "'";
		if ($karyawanId != '') $whrd .= " and karyawanid='" . $karyawanId . "'";
		
		// Query active employees
		$s = "select nik, namakaryawan, tipekaryawan, kodegolongan, kodejabatan, karyawanid from " . $dbname . ".datakaryawan 
			  where tanggalkeluar='0000-00-00' " . $whrd . " order by namakaryawan asc";
		$r = fetchdata($s);
		
		$optGolName = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
		
		$row = 2;
		foreach ($r as $baris) {
			// Find existing upah
			$upah = 0;
			if ($idKomponen != '') {
				$sUpah = "select jumlah from " . $dbname . ".sdm_5gajipokok where tahun='" . $thn . "' and karyawanid='" . $baris['karyawanid'] . "' and idkomponen='" . $idKomponen . "'";
				$rUpah = fetchdata($sUpah);
				if (count($rUpah) > 0) $upah = $rUpah[0]['jumlah'];
			}
			
			$tipeKaryawan = isset($optTip[$baris['tipekaryawan']]) ? $baris['tipekaryawan'] . ' - ' . $optTip[$baris['tipekaryawan']] : $baris['tipekaryawan'];
			$jabatanName = isset($optJbtn[$baris['kodejabatan']]) ? $baris['kodejabatan'] . ' - ' . $optJbtn[$baris['kodejabatan']] : $baris['kodejabatan'];
			$komponenName = isset($optKomponen[$idKomponen]) ? $idKomponen . ' - ' . $optKomponen[$idKomponen] : $idKomponen;
			$golonganName = isset($optGolName[$baris['kodegolongan']]) ? $baris['kodegolongan'] . ' - ' . $optGolName[$baris['kodegolongan']] : $baris['kodegolongan'];
			
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $row, $thn, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row, $kdUnit, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $row, $baris['nik'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $baris['namakaryawan'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $row, $tipeKaryawan, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $row, $golonganName, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $row, $jabatanName, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('H' . $row, $komponenName, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('I' . $row, $upah, PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$row++;
		}
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="template_gajipokok.xlsx"');
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;

	case 'getKar':
		$karyPdf = "karyawanid in (";
		$optTipe2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		if ($kdUnit != '') {
			$whr .= "and lokasitugas='" . $kdUnit . "'";
		}
		if ($tpKary != '') {
			$whr .= "and tipekaryawan='" . $tpKary . "'";
		}
		if ($golongan != '') {
			$whr .= " and kodegolongan='" . $golongan . "'";
		}
		if ($_POST['jabatan'] != '') {
			$whr .= " and kodejabatan='" . $_POST['jabatan'] . "'";
		}
		$i = "select * from " . $dbname . ".datakaryawan where lokasitugas !='' " . $whr . "";
		$n = $owlPDO->query($i) or die(print " Gagal: " . PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
		while ($d = $n->fetch()) {
			$ader += 1;
			$optTipe2 .= "<option value='" . $d['karyawanid'] . "'>" . $d['namakaryawan'] . " - " . $d['subbagian'] . " - " . $optJbtn[$d['kodejabatan']] . "</option>";
			if ($ader == 1) {
				$karyPdf .= $d['karyawanid'];
			} else {
				$karyPdf .= "," . $d['karyawanid'];
			}
		}

		$karyPdf .= ") and tahun=" . date('Y') . "";
		//echo $optTipe2 . "###" . $karyPdf;
		echo $optTipe2;
		break;

	case 'insert':
		if ($kdUnit == '') {
			echo "Warning: Unit is obligatory";
			exit;
		}
		if ($tpKary == '') {
			echo "Warning: silakan pilih tipe karyawan";
			exit;
		}
		if ($idKomponen == '') {
			echo "Warning: Component is obligatory";
			exit;
		}
		if (intval($jmlhDt) == '0') {
			echo "Warning: Please fill amount(jumlah)" . $jmlhDt;
			exit;
		}

		if ($karyawanId == '' && $pilInp == '0') {
			exit("Warning:Bila pilihan perorang, maka namakaryawan harus diisi \n if you choose the option per person, the employee's name can not be blank ");
		}

		try {
			$owlPDO->beginTransaction();


			if ($pilInp == 0) { #jika per orangan 
				$str = "select * from " . $dbname . ".sdm_5gajipokok where tahun='" . $thn . "' and kodeorg='" . $kdUnit . "' and idkomponen='87'";
				$res = fetchdata($str);
				if (count($res) > 0 and $jmlhDt > $res[0]['jumlah']) {
					throw new PDOException("Nilai tidak boleh lebih besar dari UMP.");
				}

				$i = "delete from " . $dbname . ".sdm_5gajipokok where tahun='" . $thn . "' and karyawanid='" . $karyawanId . "' and idkomponen='" . $idKomponen . "'";

				$owlPDO->exec($i);
				$n = "insert into " . $dbname . ".sdm_5gajipokok  (`tahun`, `karyawanid`, `idkomponen`, `jumlah`, `kodeorg`,`updateby`)
			 values ('" . $thn . "','" . $karyawanId . "','" . $idKomponen . "','" . $jmlhDt . "','" . $kdUnit . "','" . $_SESSION['standard']['userid'] . "')";
				$owlPDO->exec($n);
			}
			if ($pilInp == 1) {
				$whrDt = "";
				if ($_POST['jabatan'] != '') {
					$whrDt .= " and kodejabatan='" . $_POST['jabatan'] . "'";
				}
				if ($golongan != '') {
					$whrDt .= " and kodegolongan='" . $golongan . "'";
				}
				$x = "select distinct karyawanid from " . $dbname . ".datakaryawan where lokasitugas='" . $kdUnit . "'
                  and tipekaryawan='" . $tpKary . "' and (tanggalkeluar='0000-00-00' or tanggalkeluar>='" . $thn . "-01-01')
                  " . $whrDt . "";
				$y = fetchData($x);
				$jumlah = count($y);
				foreach ($y as $row => $z) {
					$str = "select * from " . $dbname . ".sdm_5gajipokok where tahun='" . $thn . "' and kodeorg='" . $kdUnit . "' and idkomponen='87'";
					$res = fetchdata($str);
					if (count($res) > 0 and $jmlhDt > $res[0]['jumlah']) {
						throw new PDOException("Nilai tidak boleh lebih besar dari UMP.");
					}

					$i = "delete from " . $dbname . ".sdm_5gajipokok where tahun='" . $thn . "' and karyawanid='" . $z['karyawanid'] . "' and idkomponen='" . $idKomponen . "'";

					$owlPDO->exec($i);
					$n = "insert into " . $dbname . ".sdm_5gajipokok (`tahun`, `karyawanid`, `idkomponen`, `jumlah`, `kodeorg`, `updateby`)
				 values ('" . $thn . "','" . $z['karyawanid'] . "','" . $idKomponen . "','" . $jmlhDt . "','" . $kdUnit . "','" . $_SESSION['standard']['userid'] . "')";
					$owlPDO->exec($n);
				}
			}
			if ($pilInp == 2) {
				$whrDt = "";
				if ($_POST['jabatan'] != '') {
					$whrDt .= " and kodejabatan='" . $_POST['jabatan'] . "'";
				}
				if ($golongan != '') {
					$whrDt .= " and kodegolongan='" . $golongan . "'";
				}
				$x = "select distinct karyawanid from " . $dbname . ".datakaryawan where lokasitugas='" . $kdUnit . "'
                  and tipekaryawan='" . $tpKary . "' and (tanggalkeluar='0000-00-00' or tanggalkeluar>='" . $thn . "-01-01')
                  " . $whrDt . " and karyawanid not in (select karyawanid from " . $dbname . ".sdm_5gajipokok where tahun='" . $thn . "' and idkomponen='" . $idKomponen . "')";
				$y = fetchData($x);
				$jumlah = count($y);
				if ($jumlah > 0) {
					foreach ($y as $row => $z) {
						$str = "select * from " . $dbname . ".sdm_5gajipokok where tahun='" . $thn . "' and kodeorg='" . $kdUnit . "' and idkomponen='87'";
						$res = fetchdata($str);
						if (count($res) > 0 and $jmlhDt > $res[0]['jumlah']) {
							throw new PDOException("Nilai tidak boleh lebih besar dari UMP.");
						}

						$n = "insert into " . $dbname . ".sdm_5gajipokok (`tahun`, `karyawanid`, `idkomponen`, `jumlah`, `kodeorg`,`updateby`)
					 values ('" . $thn . "','" . $z['karyawanid'] . "','" . $idKomponen . "','" . $jmlhDt . "','" . $kdUnit . "','" . $_SESSION['standard']['userid'] . "')";
						$owlPDO->exec($n);
					}
				}
			}

			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}

		if ($jumlah != '') {
			echo "Sukses sebanyak : " . $jumlah . " Karyawan.";
		}
		break;
	case 'prevupdate':
		try {
			$owlPDO->beginTransaction();

			if ($param['kodeorg'] == '') {
				throw new PDOException("Kodeorg wajib diisi.");
			}
			if ($param['periode'] == '') {
				throw new PDOException("Periode transaksi wajib diisi.");
			}
			if ($param['tahun'] == '') {
				throw new PDOException("Gaji tahun wajib diisi.");
			}
			if ($param['tipekary'] == '') {
				throw new PDOException("Tipekaryawan wajib diisi.");
			}
			if ($param['idkomponen'] == '') {
				throw new PDOException("Komponen gaji wajib diisi.");
			}

			// echo"<pre>";
			// print_r($param);
			// echo"</pre>";
			// exit("error");
			$s = "select * from " . $dbname . ".sdm_5periodegaji where periode = '" . $param['periode'] . "' and kodeorg = '" . $param['kodeorg'] . "'";
			$r = fetchdata($s);
			if ($r[0]['sudahproses'] == '1') {
				throw new PDOException("Periode gaji sudah ditutup.");
			}

			#== ambil table transaksi ==#
			#== kebun_prestasi ==#
			#== kebun_kehadiran ==#
			#== kebun_3premibmtbs ==#
			#== sdm_absensidt ==#
			#== vhc_runhk ==#
			$tab .= "<fieldset>
		<legend><b>List</b></legend>
		<div class='table-scroll'>
		<table class='sortable' cellspacing=1 cellpadding=5 border=0 width=100%>
			<thead>
			<tr class=rowheader>
				<th align=center width=30px>No.</th>
				<th align=center>" . $_SESSION['lang']['sumber'] . "</th>
				<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
				<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
				<th align=center>" . $_SESSION['lang']['nama'] . "</th>
				<th align=center>" . $_SESSION['lang']['hk2'] . "</th>
				<th align=center>" . $_SESSION['lang']['upah'] . "<br>" . $_SESSION['lang']['lama'] . "</th>
				<th align=center>" . $_SESSION['lang']['upah'] . "<br>" . $_SESSION['lang']['baru'] . "</th>
			</tr>
		</thead><tbody>";

			$wh = "";
			if ($param['namakaryawan'] != '') {
				$wh = " and karyawanid='" . $param['namakaryawan'] . "'";
			}
			#BKM
			$str = "select * from " . $dbname . ".kebun_kehadiran a left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
		where b.kodeorg = '" . $param['kodeorg'] . "' and b.tanggal like '" . $param['periode'] . "%' and b.jurnal='0' and b.tipetransaksi!='PNN' and umr>0 and nik in (select karyawanid from " . $dbname . ".datakaryawan where tipekaryawan='" . $param['tipekary'] . "' " . $wh . ")";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$color = "";
				$s = "select jumlah from " . $dbname . ".sdm_5gajipokok where tahun = '" . $param['tahun'] . "' and idkomponen = '" . $param['idkomponen'] . "' and karyawanid='" . $bar['nik'] . "' and kodeorg='" . $param['kodeorg'] . "'";
				$r = fetchdata($s);
				$upahbaru = $r[0]['jumlah'] / 25;
				if ($upahbaru == '0') {
					if ($param['jenis'] == 'update') {
						throw new PDOException("Gaji pokok tahun " . $param['tahun'] . " an. " . getNamaKaryawan($bar['nik']) . " belum ada.");
					} else {
						$color = "style=background-color:red;";
					}
				}
				$no++;
				if ($param['jenis'] == 'update') {
					$str = "update " . $dbname . ".kebun_kehadiran set `umr`='" . $upahbaru * $bar['jhk'] . "' where `notransaksi`='" . $bar['notransaksi'] . "' and nik='" . $bar['nik'] . "' and nourut='" . $bar['nourut'] . "'";
					$owlPDO->exec($str);
				} else {
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td align=center>" . $no . "</td>";
					$tab .= "<td align=center>BKM RAWAT</td>";
					$tab .= "<td align=center>" . $bar['notransaksi'] . "</td>";
					$tab .= "<td align=center>" . $bar['tanggal'] . "</td>";
					$tab .= "<td align=left>" . getNamaKaryawan($bar['nik']) . "</td>";
					$tab .= "<td align=right>" . $bar['jhk'] . "</td>";
					$tab .= "<td align=right>" . $bar['umr'] . "</td>";
					$tab .= "<td align=right " . $color . ">" . $upahbaru * $bar['jhk'] . "</td>";
					$tab .= "</tr>";
				}
			}

			#PNN
			// $str = "select * from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
			// where b.kodeorg = '".$param['kodeorg']."' and b.tanggal like '".$param['periode']."%' and b.jurnal='0' and b.tipetransaksi='PNN' and upahkerja>0 and nik in (select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='".$param['tipekary']."'  ".$wh.")";
			// $res = fetchdata($str);
			// foreach($res as $bar){
			// 	$color="";
			// 	$s = "select jumlah from ".$dbname.".sdm_5gajipokok where tahun = '".$param['tahun']."' and idkomponen = '".$param['idkomponen']."' and karyawanid='".$bar['nik']."' and kodeorg='".$param['kodeorg']."'";
			// 	$r = fetchdata($s);
			// 	$upahbaru = $r[0]['jumlah']/25;
			// 	if($upahbaru=='0'){
			// 		if($param['jenis']=='update'){					
			// 			throw new PDOException("Gaji pokok tahun ".$param['tahun']." an. ".getNamaKaryawan($bar['nik'])." belum ada.");
			// 		}else{
			// 			$color="style=background-color:red;";
			// 		}
			// 	}

			// 	$no++;
			// 	if($param['jenis']=='update'){					
			// 		$str = "update " . $dbname . ".kebun_prestasi set `upahkerja`='".$upahbaru*$bar['jumlahhk']."' where `notransaksi`='".$bar['notransaksi']."' and nik='".$bar['nik']."' and kodekegiatan='".$bar['kodekegiatan']."' and kodeorg='".$bar['kodeorg']."' and nikpemel='".$bar['nikpemel']."' and kodesegment='".$bar['kodesegment']."'";
			// 		$owlPDO->exec($str);
			// 	}else{				
			// 		$tab.="<tr class=rowcontent>";
			// 		$tab.="<td align=center>".$no."</td>";
			// 		$tab.="<td align=center>PNN</td>";
			// 		$tab.="<td align=center>".$bar['notransaksi']."</td>";
			// 		$tab.="<td align=center>".$bar['tanggal']."</td>";
			// 		$tab.="<td align=left>".getNamaKaryawan($bar['nik'])."</td>";
			// 		$tab.="<td align=right>".$bar['jumlahhk']."</td>";
			// 		$tab.="<td align=right>".$bar['upahkerja']."</td>";
			// 		$tab.="<td align=right ".$color.">".$upahbaru*$bar['jumlahhk']."</td>";
			// 		$tab.="</tr>";
			// 	}
			// }

			#SDM
			$str = "select * from " . $dbname . ".sdm_absensidt where kodeorg = '" . $param['kodeorg'] . "' and tanggal like '" . $param['periode'] . "%' and umr>0 and karyawanid in (select karyawanid from " . $dbname . ".datakaryawan where tipekaryawan='" . $param['tipekary'] . "' " . $wh . ")";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$color = "";
				$s = "select jumlah from " . $dbname . ".sdm_5gajipokok where tahun = '" . $param['tahun'] . "' and idkomponen = '" . $param['idkomponen'] . "' and karyawanid='" . $bar['karyawanid'] . "' and kodeorg='" . $param['kodeorg'] . "'";
				$r = fetchdata($s);
				$upahbaru = $r[0]['jumlah'] / 25;
				if ($upahbaru == '0') {
					if ($param['jenis'] == 'update') {
						throw new PDOException("Gaji pokok tahun " . $param['tahun'] . " an. " . getNamaKaryawan($bar['karyawanid']) . " belum ada.");
					} else {
						$color = "style=background-color:red;";
					}
				}

				$no++;
				if ($param['jenis'] == 'update') {
					$str = "update " . $dbname . ".sdm_absensidt set `umr`='" . $upahbaru * $bar['hk'] . "', penaltykehadiran='" . (($bar['penaltykehadiran'] / $bar['umr']) * $upahbaru) . "' where `tanggal`='" . $bar['tanggal'] . "' and karyawanid='" . $bar['karyawanid'] . "' and kodeorg='" . $bar['kodeorg'] . "'";
					$owlPDO->exec($str);
				} else {
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td align=center>" . $no . "</td>";
					$tab .= "<td align=center>SDM</td>";
					$tab .= "<td align=center></td>";
					$tab .= "<td align=center>" . $bar['tanggal'] . "</td>";
					$tab .= "<td align=left>" . getNamaKaryawan($bar['karyawanid']) . "</td>";
					$tab .= "<td align=right>" . $bar['hk'] . "</td>";
					$tab .= "<td align=right>" . $bar['umr'] . "</td>";
					$tab .= "<td align=right " . $color . ">" . $upahbaru * $bar['hk'] . "</td>";
					$tab .= "</tr>";
				}
			}

			#VHC
			$str = "select * from " . $dbname . ".vhc_runhk where notransaksi like '" . $param['kodeorg'] . "%' and tanggal like '" . $param['periode'] . "%' and upah>0 and idkaryawan in (select karyawanid from " . $dbname . ".datakaryawan where tipekaryawan='" . $param['tipekary'] . "' " . $wh . ")";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$color = "";
				$n = "select jumlah from " . $dbname . ".sdm_5gajipokok where tahun = '" . $param['periode'] . "' and idkomponen = '" . $param['idkomponen'] . "' and karyawanid='" . $bar['idkaryawan'] . "' and kodeorg='" . $param['kodeorg'] . "'";
				$q = fetchdata($n);
				$upahlama = $q[0]['jumlah'] / 25;


				$s = "select jumlah from " . $dbname . ".sdm_5gajipokok where tahun = '" . $param['tahun'] . "' and idkomponen = '" . $param['idkomponen'] . "' and karyawanid='" . $bar['idkaryawan'] . "' and kodeorg='" . $param['kodeorg'] . "'";
				$r = fetchdata($s);
				$upahbaru = $r[0]['jumlah'] / 25;
				if ($upahbaru == '0') {
					if ($param['jenis'] == 'update') {
						throw new PDOException("Gaji pokok tahun " . $param['tahun'] . " an. " . getNamaKaryawan($bar['idkaryawan']) . " belum ada.");
					} else {
						$color = "style=background-color:red;";
					}
				}

				$no++;
				if ($param['jenis'] == 'update') {
					$str = "update " . $dbname . ".vhc_runhk set `upah`='" . $upahbaru * ($bar['upah'] / $upahlama) . "' where `notransaksi`='" . $bar['notransaksi'] . "' and idkaryawan='" . $bar['idkaryawan'] . "'";
					$owlPDO->exec($str);
				} else {
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td align=center>" . $no . "</td>";
					$tab .= "<td align=center>TRAKSI</td>";
					$tab .= "<td align=center>" . $bar['notransaksi'] . "</td>";
					$tab .= "<td align=center>" . $bar['tanggal'] . "</td>";
					$tab .= "<td align=left>" . getNamaKaryawan($bar['idkaryawan']) . "</td>";
					$tab .= "<td align=right>" . ($bar['upah'] / $upahlama) . "</td>";
					$tab .= "<td align=right>" . $bar['upah'] . "</td>";
					$tab .= "<td align=right " . $color . ">" . $upahbaru * ($bar['upah'] / $upahlama) . "</td>";
					$tab .= "</tr>";
				}
			}

			$tab .= "</tbody></table>
				<button class=mybutton onclick=prevupdate('update')>Update</button>
			</fieldset>";

			if ($param['jenis'] == 'update') {
				#execute
				$owlPDO->commit();
			} else {
				echo $tab;
			}
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		break;
	case 'getperiode':
		$str = "select distinct periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $param['kodeorg'] . "' and sudahproses='0' order by periode desc";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$optkar .= "<option value='" . $val['periode'] . "'>" . $val['periode'] . "</option>";
		}

		$opttpkar = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$tporg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='" . $param['kodeorg'] . "'");

		$whrd = $whr = "";
		if (trim($tporg[$param['kodeorg']]) == 'HOLDING' || trim($tporg[$param['kodeorg']]) == 'KANWIL') {
			$whrd = " and id not in ('0')";
			$whr = " and tipekaryawan not in ('0')";
		} else {
			$whrd = " and id not in ('0')";
			$whr = " and tipekaryawan not in ('0')";
		}

		$str = "select distinct id,tipe from " . $dbname . ".sdm_5tipekaryawan where 1=1 " . $whrd . " order by no asc";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$opttpkar .= "<option value='" . $val['id'] . "'>" . $val['tipe'] . "</option>";
		}

		$str = "select * from " . $dbname . ".datakaryawan where 1=1 " . $whr . " and lokasitugas='" . $param['kodeorg'] . "' and tanggalkeluar='0000-00-00' order by namakaryawan asc";
		$res = fetchdata($str);
		$optkary = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		foreach ($res as $val) {
			$optkary .= "<option value='" . $val['karyawanid'] . "'>" . $val['nik'] . " - " . $val['namakaryawan'] . "</option>";
		}



		echo $optkar . "####" . $opttpkar . "####" . $optkary;
		break;
	case 'loadData':
		$whrd = '';

		if ($kdUnitCr != '') {
			$whrd .= " and a.karyawanid in (select karyawanid from " . $dbname . ".datakaryawan"
				. " where lokasitugas='" . $kdUnitCr . "') ";
		} else {
			$whrd .= " and a.karyawanid in (select karyawanid from " . $dbname . ".datakaryawan"
				. " where lokasitugas in (" . getOrgDetail('2') . ")) ";
		}

		if ($optThn != '') {
			$whrd .= " and a.tahun='" . $optThn . "'";
		}

		if ($namaKary != '') {
			$whrd .= " and b.namakaryawan like '%" . $namaKary . "%'";
		}

		if ($tpKaryCr != '') {
			$whrd .= " and b.tipekaryawan = '" . $tpKaryCr . "'";
		}

		if ($idKomponenCr != '') {
			$whrd .= " and a.idkomponen='" . $idKomponenCr . "'";
		}
		if ($idjabatan != '') {
			$whrd .= " and b.kodejabatan='" . $idjabatan . "'";
		}


		$limit = 30;
		if (isset($page)) {
			if ($page < 0)
				$page = 0;
		}
		$offset = floatval($page) * $limit;
		$maxdisplay = (floatval($page) * $limit);

		$ql2 = "select count(*) as jmlhrow,b.namakaryawan,b.tipekaryawan from " . $dbname . ".sdm_5gajipokok a "
			. " left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where 1=1 " . $whrd . " ";
		$query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage() . "___" . $ql2);
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}

		echo "<div class='table-scroll'>
		<table class=sortable cellspacing=1 border=0 cellpadding=7 style='width:100%;'>
			<thead>
			<tr class=rowheader>
				<th align=center>No</th>
				<th align=center>" . $_SESSION['lang']['periode'] . "</th>
				<th align=center>" . $_SESSION['lang']['unitkerja'] . "</th>
				<th align=center>" . $_SESSION['lang']['nik'] . "</th>
				<th align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
				<th align=center>" . $_SESSION['lang']['tipekaryawan'] . "</th>
				<th align=center>" . $_SESSION['lang']['kodegolongan'] . "</th>
				<th align=center>" . $_SESSION['lang']['kodejabatan'] . "</th>
				<th align=center>" . $_SESSION['lang']['idkomponen'] . "</th>
				<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>
				<th align=center colspan=2>" . $_SESSION['lang']['action'] . "</th>   
			</tr>
			</thead>
			<tbody>";

		$str = "select a.*,b.namakaryawan,b.tipekaryawan,b.kodegolongan,b.nik,b.lokasitugas,b.kodejabatan, d.namagolongan from " . $dbname . ".sdm_5gajipokok a "
			. " left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
					left join " . $dbname . ".sdm_5golongan d on b.kodegolongan=d.kodegolongan 
					left join " . $dbname . ".sdm_5tipekaryawan c on b.tipekaryawan=c.id where 1=1 " . $whrd . " "
			. " limit " . $offset . "," . $limit . " ";
		//exit("error".$str);
		$no = $maxdisplay;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$oow = owlBaris($res);
		if ($oow == 0) {
			echo "<tr class=rowcontent><td colspan=11 style='text-align:center'>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
		} else {
			while ($bar = $res->fetch()) {
				$no += 1;
				$optJab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $bar['kodejabatan'] . "'");
				echo "<tr class=rowcontent>
					<td align=center>" . $no . "</td>   
                    <td align=center>" . $bar['tahun'] . "</td>   
					<td>" . $bar['lokasitugas'] . "</td>
					<td>" . $bar['nik'] . "</td>
                    <td>" . $bar['namakaryawan'] . "</td>
                    <td>" . $optTip[$bar['tipekaryawan']] . "</td>
                    <td>" . $bar['namagolongan'] . "</td>
                    <td>" . $optJab[$bar['kodejabatan']] . "</td>
                    <td>" . $optKomponen[$bar['idkomponen']] . "</td>  
                    <td align=right>" . number_format($bar['jumlah'], 0) . "</td>  
                    <td align=center width=25px>
						<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar['tahun'] . "','" . $bar['karyawanid'] . "','" . $optTipe[$bar['karyawanid']] . "','" . $bar['idkomponen'] . "','" . $bar['jumlah'] . "','" . $zel[$bar['karyawanid']] . "','" . $optNmKar[$bar['karyawanid']] . "','" . $optGol[$bar['karyawanid']] . "');\"></td>
					<td align=center width=25px>	
						<img src=images/application/application_delete.png class=resicon  title='Delete Data' onclick=\"delData('" . $bar['tahun'] . "','" . $bar['karyawanid'] . "','" . $bar['idkomponen'] . "');\">
					</td>
				</tr>";
			}

			echo "<tr class=rowheader><td colspan=12 align=center>
                " . ((floatval($page) * $limit) + 1) . " to " . ((floatval($page) + 1) * $limit) . " Of " . $jlhbrs . "<br />
                <button class=mybutton onclick=cariBast(" . (floatval($page) - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <button class=mybutton onclick=cariBast(" . (floatval($page) + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
			echo "</tbody>
			<tfoot>
			</tfoot>
			</table></div>";
		}
		break;
	case 'excel':
		$whrExcel = '';

		if ($kdUnitCr != '') {
			$whrExcel .= " and b.lokasitugas='" . $kdUnitCr . "'";
		} else {
			$whrExcel .= " and b.lokasitugas in (" . getOrgDetail('2') . ")";
		}

		if ($optThn != '') {
			$whrExcel .= " and a.tahun='" . $optThn . "'";
		}
		if ($namaKary != '') {
			$whrExcel .= " and b.namakaryawan like '%" . $namaKary . "%'";
		}
		if ($tpKaryCr != '') {
			$whrExcel .= " and b.tipekaryawan='" . $tpKaryCr . "'";
		}
		if ($idjabatan != '') {
			$whrExcel .= " and b.kodejabatan='" . $idjabatan . "'";
		}

		$optNamaKomponen = makeOption($dbname, 'sdm_ho_component', 'id,name');
		$whrKomponenExcel = '';
		if ($idKomponenCr != '') {
			$whrKomponenExcel = " and a.idkomponen='" . $idKomponenCr . "'";
		}

		$strKomponen = "select distinct a.idkomponen
			from " . $dbname . ".sdm_5gajipokok a
			left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
			where 1=1 " . $whrExcel . $whrKomponenExcel . "
			order by a.idkomponen asc";
		$resKomponen = fetchData($strKomponen);

		$komponenExcel = array();
		foreach ($resKomponen as $rowKomponen) {
			$idKomp = $rowKomponen['idkomponen'];
			$komponenExcel[$idKomp] = isset($optNamaKomponen[$idKomp]) && $optNamaKomponen[$idKomp] != ''
				? $optNamaKomponen[$idKomp]
				: $idKomp;
		}

		$whrDataKomponen = $whrKomponenExcel;

		$str = "select a.tahun,a.karyawanid,a.idkomponen,a.jumlah,
			b.namakaryawan,b.tipekaryawan,b.kodegolongan,b.nik,b.lokasitugas,b.kodejabatan,
			d.namagolongan
			from " . $dbname . ".sdm_5gajipokok a
			left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
			left join " . $dbname . ".sdm_5golongan d on b.kodegolongan=d.kodegolongan
			where 1=1 " . $whrExcel . $whrDataKomponen . "
			order by b.lokasitugas,b.namakaryawan,a.tahun,a.idkomponen";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);

		$dataExcel = array();
		while ($bar = $res->fetch()) {
			$key = $bar['tahun'] . '|' . $bar['karyawanid'];
			if (!isset($dataExcel[$key])) {
				$dataExcel[$key] = array(
					'tahun' => $bar['tahun'],
					'karyawanid' => $bar['karyawanid'],
					'lokasitugas' => $bar['lokasitugas'],
					'nik' => $bar['nik'],
					'namakaryawan' => $bar['namakaryawan'],
					'tipekaryawan' => $bar['tipekaryawan'],
					'namagolongan' => $bar['namagolongan'],
					'kodejabatan' => $bar['kodejabatan'],
					'komponen' => array()
				);
			}
			$dataExcel[$key]['komponen'][$bar['idkomponen']] = $bar['jumlah'];
		}

		$jumlahKolomIdentitas = 8;
		$jumlahKolomKomponen = count($komponenExcel);
		$totalKolom = $jumlahKolomIdentitas + $jumlahKolomKomponen + 1; // +1 kolom TOTAL

		$view = "<table cellspacing=0 cellpadding=5 border=1>";
		$view .= "<thead>";
		$view .= "<tr>";
		$view .= "<th colspan='" . $jumlahKolomIdentitas . "' style='font-weight:bold;text-align:center;'>DATA KARYAWAN</th>";
		if ($jumlahKolomKomponen > 0) {
			$view .= "<th colspan='" . $jumlahKolomKomponen . "' style='font-weight:bold;text-align:center;'>KOMPONEN GAJI</th>";
		}
		$view .= "<th rowspan='2' style='font-weight:bold;text-align:center;'>TOTAL</th>";
		$view .= "</tr>";
		$view .= "<tr>";
		$view .= "<th style='font-weight:bold;text-align:center;'>No</th>";
		$view .= "<th style='font-weight:bold;text-align:center;'>" . $_SESSION['lang']['periode'] . "</th>";
		$view .= "<th style='font-weight:bold;text-align:center;'>" . $_SESSION['lang']['unitkerja'] . "</th>";
		$view .= "<th style='font-weight:bold;text-align:center;'>" . $_SESSION['lang']['nik'] . "</th>";
		$view .= "<th style='font-weight:bold;text-align:center;'>" . $_SESSION['lang']['namakaryawan'] . "</th>";
		$view .= "<th style='font-weight:bold;text-align:center;'>" . $_SESSION['lang']['tipekaryawan'] . "</th>";
		$view .= "<th style='font-weight:bold;text-align:center;'>" . $_SESSION['lang']['kodegolongan'] . "</th>";
		$view .= "<th style='font-weight:bold;text-align:center;'>" . $_SESSION['lang']['kodejabatan'] . "</th>";
		foreach ($komponenExcel as $idKomp => $namaKomp) {
			$view .= "<th style='font-weight:bold;text-align:center;'>" . $namaKomp . "</th>";
		}
		$view .= "</tr></thead><tbody>";

		$no = 0;
		$subTotal = array();
		$grandTotal = 0;
		foreach ($dataExcel as $bar) {
			$no++;
			$total = 0; // reset total untuk setiap karyawan
			$view .= "<tr>";
			$view .= "<td align=center>" . $no . "</td>";
			$view .= "<td align=center>" . $bar['tahun'] . "</td>";
			$view .= "<td>" . $bar['lokasitugas'] . "</td>";
			$view .= "<td>" . $bar['nik'] . "</td>";
			$view .= "<td>" . $bar['namakaryawan'] . "</td>";
			$view .= "<td>" . $optTip[$bar['tipekaryawan']] . "</td>";
			$view .= "<td>" . $bar['namagolongan'] . "</td>";
			$view .= "<td>" . $optJbtn[$bar['kodejabatan']] . "</td>";
			foreach ($komponenExcel as $idKomp => $namaKomp) {
				$nilai = isset($bar['komponen'][$idKomp]) ? $bar['komponen'][$idKomp] : 0;
				$view .= "<td align=right>" . number_format($nilai, 2) . "</td>";

				$total += $nilai;

				if (!isset($subTotal[$idKomp])) {
					$subTotal[$idKomp] = 0;
				}
				$subTotal[$idKomp] += $nilai;
			}
			$grandTotal += $total;
			$view .= "<td align=right>" . number_format($total, 2) . "</td>";
			$view .= "</tr>";
		}

		if ($no > 0) {
			$view .= "<tr>";
			$view .= "<th colspan='" . $jumlahKolomIdentitas . "' style='font-weight:bold;text-align:right;'>GRAND TOTAL</th>";
			foreach ($komponenExcel as $idKomp => $namaKomp) {
				$nilaiSubTotal = isset($subTotal[$idKomp]) ? $subTotal[$idKomp] : 0;
				$view .= "<th style='font-weight:bold;text-align:right;'>" . number_format($nilaiSubTotal, 2) . "</th>";
			}
			$view .= "<th style='font-weight:bold;text-align:right;'>" . number_format($grandTotal, 2) . "</th>";
			$view .= "</tr>";
		} else {
			$view .= "<tr><td colspan='" . $totalKolom . "' align=center>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
		}
		$view .= "</tbody></table>";

		$nop = "Data Gaji Pokok.xls";
		$xls = new HtmlExcel();
		if (isset($css)) {
			$xls->setCss($css);
		}
		$xls->addSheet('Data Gaji Pokok', $view);
		$xls->headers($nop);
		echo $xls->buildFile();
		break;
	case 'updateData':
		if ($pilInp == 0) {
			$sdel = "delete from " . $dbname . ".sdm_5gajipokok where karyawanid='" . $karyawanId . "'
                                   and idkomponen='" . $idKomponen . "' and tahun='" . $thn . "'";
			try {
				$owlPDO->exec($sdel);

				$sIns = "insert into " . $dbname . ".sdm_5gajipokok (`tahun`, `karyawanid`, `idkomponen`, `jumlah`, `kodeorg`, `updateby`)
                         values ('" . $thn . "','" . $karyawanId . "','" . $idKomponen . "','" . $jmlhDt . "','" . $kdUnit . "','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($sIns);
				} catch (PDOException $e) {
					echo "Gagal" . $e->getMessage();
				}
			} catch (PDOException $e) {
			}
		} else {
			$sdata = "select distinct karyawanid from " . $dbname . ".datakaryawan where lokasitugas='" . $kdUnit . "'
                                and tipekaryawan='" . $tpKary . "' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . $thn . "-01-01')";
			$qData = $owlPDO->query($sdata) or die(print " Gagal : " . PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while ($rdata = $qData->fetch()) {
				$sdel = "delete from " . $dbname . ".sdm_5gajipokok where karyawanid='" . $rdata['karyawanid'] . "'
                                   and idkomponen='" . $idKomponen . "' and tahun='" . $thn . "'";
				try {
					$owlPDO->exec($sdel);
					$sIns = "insert into " . $dbname . ".sdm_5gajipokok (`tahun`, `karyawanid`, `idkomponen`, `jumlah`, `kodeorg`, `updateby`)
							values ('" . $thn . "','" . $rdata['karyawanid'] . "','" . $idKomponen . "','" . $jmlhDt . "','" . $kdUnit . "','" . $_SESSION['standard']['userid'] . "')";
					try {
						$owlPDO->exec($sIns);
					} catch (PDOException $e) {
						echo "Gagal" . $sIns . "____" . $e->getMessage();
					}
				} catch (PDOException $e) {
					echo "Gagal" . $sdel . "____" . $e->getMessage();
				}
			}
		}
		break;
	case 'delData':
		$sdel = "delete from " . $dbname . ".sdm_5gajipokok where karyawanid='" . $_POST['karyawanId'] . "'
                                   and idkomponen='" . $_POST['idKomponen'] . "' and tahun='" . $_POST['optThn'] . "'";
		try {
			$owlPDO->exec($sdel);
		} catch (PDOException $e) {
			echo "Gagal" . $sdel . "____" . $e->getMessage();
		}
		break;
}
