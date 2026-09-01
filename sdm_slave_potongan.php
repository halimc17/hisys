<?php
//session_start();
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

require_once('dompdf/autoload.inc.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';

use Dompdf\Dompdf;

$method = checkPostGet('method', '');
$per = checkPostGet('per', '');
$kom = checkPostGet('kom', '');
$org = checkPostGet('org', '');
$namafile 		= checkPostGet('namafile', '');

$path	= "fileupload/sdm_potongan/";


$param = $_POST;
setIt($param['periode'], '');
setIt($param['kdOrg'], '');
setIt($param['tipePot'], '');

$optLokasiTugas =  makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');
$periodeAkutansi = $_SESSION['org']['period']['tahun'] . "-" . $_SESSION['org']['period']['bulan'];
$whrPot = "(name like 'pot.%' or name like 'potongan%') or id ='59'";

if (($_SESSION['empl']['bagian'] == 'FIN') || ($_SESSION['empl']['bagian'] == 'IT')) {
	$whrPrdData = "kodeorg='" . $param['kdOrg'] . "' and periodegaji='" . $param['periode'] . "' and tipepotongan='" . $param['tipePot'] . "'  ";
} else {
	$whrPrdData = "kodeorg='" . $param['kdOrg'] . "' and updateby='" . $_SESSION['standard']['userid'] . "' and periodegaji='" . $param['periode'] . "' and tipepotongan='" . $param['tipePot'] . "' ";
}

if (($_SESSION['empl']['bagian'] == 'FIN') || ($_SESSION['empl']['bagian'] == 'IT')) {
	$whrPrdDataDetail = "periodegaji='" . $param['periode'] . "' and tipepotongan='" . $param['tipePot'] . "'  ";
} else {
	$whrPrdDataDetail = "updateby='" . $_SESSION['standard']['userid'] . "' and periodegaji='" . $param['periode'] . "' and tipepotongan='" . $param['tipePot'] . "' ";
}

$optNmPotongan = makeOption($dbname, 'sdm_ho_component', 'id,name');
$optNmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNikKar = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$tgl = date("Y-m-d");
$optTipe =  makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

switch ($method) {
	case 'submitfile':
		$tgl = date("YmdHis");
		$his = date("His");
		$data = $_POST;

		if ($data['fileupload'] != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$filename = $pt . "_" . $his . "" . $filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);

				if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
					if ($_FILES['file']['size'] <= 2500000) {
						$str = "insert into " . $dbname . ".listfile_sdm_potongan values ('','" . $org . "','" . $per . "','" . $kom . "','" . $filename . "','" . $filetype . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
						try {
							$owlPDO->exec($str);
							if (!file_exists($path)) {
								mkdir($path, 0777, true);
							}
							file_put_contents($path . $filename, $file_tmpname);
						} catch (PDOException $e) {
							echo " Gagal," . addslashes($e->getMessage());
						}
					} else {
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				} else {
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		}
		break;


	case 'getkaryawanid':

		if ($org == '') {
			exit("Warning : Unit Kerja Wajib Diisi ");
		}
		if ($per == '') {
			exit("Warning : Periode Wajib Diisi ");
		}
		if ($kom == '') {
			exit("Warning : Potongan Wajib Diisi ");
		}

		$strdkar = "select karyawanid from " . $dbname . ".datakaryawan_hist a where approval_status='8' and version_type='B' and periodegaji='" . $per . "' and  lokasitugas = '" . $org . "'";
		$resdkar = fetchdata($strdkar);
		if (count($resdkar) > 0) {
			$str = "select namakaryawan,nik,karyawanid,lokasitugas,tipekaryawan from " . $dbname . ".datakaryawan_hist where approval_status='8' and lokasitugas = '" . $org . "' and version_type='B' and periodegaji='" . $per . "' and (tanggalkeluar >= '" . $per . "-01' or tanggalkeluar = '0000-00-00') order by namakaryawan";
		} else {
			$str = "select * from " . $dbname . ".datakaryawan
			where (tanggalkeluar='0000-00-00' or tanggalkeluar>'" . date('Y-m-d') . "') and lokasitugas = '" . $org . "' and tipekaryawan != 0 order by namakaryawan";
		}

		$res = fetchData($str);

		$objPHPExcel = new PHPExcel();
		$sheet = $objPHPExcel->getActiveSheet();
		$sheet->setCellValue('A1', $_SESSION['lang']['nik']);
		$sheet->setCellValue('B1', $_SESSION['lang']['nama']);
		$sheet->setCellValue('C1', $_SESSION['lang']['jabatan']);
		$sheet->setCellValue('D1', $_SESSION['lang']['lokasitugas']);
		$sheet->setCellValue('E1', $_SESSION['lang']['divisi']);
		$sheet->setCellValue('F1', $_SESSION['lang']['jumlah']);
		$sheet->setCellValue('G1', $_SESSION['lang']['keterangan']);

		$row = 2;
		foreach ($res as $bar) {

			if ($bar['subbagian'] == '') {
				$text = 'KANTOR';
			} else {
				$text = $bar['subbagian'];
			}

			$sheet->setCellValueExplicit('A' . $row, $bar['nik'], PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('B' . $row, $bar['namakaryawan']);
			$sheet->setCellValue('C' . $row, getJabatanKaryawan($bar['karyawanid']));
			$sheet->setCellValueExplicit('D' . $row, $bar['lokasitugas'], PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('E' . $row, $text);
			$row++;
		}

		$nop_ = "Daftar_Karyawan_" . $org;
		if ($handle = opendir('tempExcel')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "index.html") {
					@unlink('tempExcel/' . $file);
				}
			}
			closedir($handle);
		}

		try {
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save("tempExcel/" . $nop_ . ".xls");
			echo "<script language=javascript>
					window.location='tempExcel/" . $nop_ . ".xls';
					</script>";
		} catch (Exception $e) {
			echo "<script language=javascript>
					parent.window.alert('Can't convert to excel format');
					</script>";
			exit;
		}
		break;
	case 'insertfile':

		$previewOnly = (checkPostGet('previewonly', '') == '1');

		if ($org == '') {
			exit("Warning : Unit Kerja Wajib Diisi ");
		}
		if ($per == '') {
			exit("Warning : Periode Kerja Wajib Diisi ");
		}
		if ($kom == '') {
			exit("Warning : Potongan Wajib Diisi ");
		}

		if ($_FILES['file']['error'] == 0) {

			$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
			$file = $_FILES['file']['tmp_name'];
			if (in_array($filetype, array('.xls', '.xlsx'))) {
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null, true, false, true);
				$karyawan_id = makeOption($dbname, 'datakaryawan', 'nik,karyawanid');

				$firsturut1 = null;
				$maxScanHeader = min(10, count($sheets));
				for ($r = 1; $r <= $maxScanHeader; $r++) {
					$nikCandidate = isset($sheets[$r]['A']) ? trim((string)$sheets[$r]['A']) : '';
					if ($nikCandidate !== '' && isset($karyawan_id[$nikCandidate])) {
						$firsturut1 = $r - 1;
						break;
					}
				}

				if ($firsturut1 === null) {
					exit("Warning : Format file tidak valid atau NIK pada file tidak ditemukan di data karyawan. Pastikan file sesuai template.");
				}

				$i = 1;

				try {
					$owlPDO->beginTransaction();

					foreach ($sheets as $sheet) {
						if ($i <= $firsturut1) {
							$i++;
							continue;
						}

						if ($sheet['F'] == null || trim((string)$sheet['F']) == '') {
							$i++;
							continue;
						}

						$jumlahPotongan = normalizeNumberExcel($sheet['F']);
						if ($jumlahPotongan == null) {
							throw new Exception("Format jumlah potongan tidak valid pada baris " . $i . ". Nilai: " . $sheet['F']);
						}

						$nikExcel = trim((string)$sheet['A']);
						if (!isset($karyawan_id[$nikExcel]) || $karyawan_id[$nikExcel] == '') {
							throw new Exception("NIK '" . $nikExcel . "' pada baris Excel " . $i . " tidak ditemukan di master karyawan.");
						}

						$kodeorgExcel = trim((string)$sheet['D']);

						if ($kodeorgExcel != $org) {
							$i++;
							continue;
						}

						$karyawanid = $karyawan_id[$nikExcel];
						$keterangan = isset($sheet['G']) ? addslashes($sheet['G']) : '';

						if ((float)$jumlahPotongan == 0) {
							$str = "delete from " . $dbname . ".sdm_potongandt
								where kodeorg='" . $kodeorgExcel . "'
								and periodegaji='" . $per . "'
								and tipepotongan='" . $kom . "'
								and nik='" . $karyawanid . "'";
							$owlPDO->exec($str);

							$i++;
							continue;
						}

						$jumlahPotonganSql = number_format($jumlahPotongan, 10, '.', '');
						$jumlahPotonganSql = rtrim(rtrim($jumlahPotonganSql, '0'), '.');

						$strc = "select * from " . $dbname . ".sdm_potonganht
							where kodeorg='" . $kodeorgExcel . "'
							and periodegaji='" . $per . "'
							and tipepotongan='" . $kom . "'";
						$resc = fetchdata($strc);

						if (count($resc) == 0) {
							$str = "insert into " . $dbname . ".sdm_potonganht
								(kodeorg,periodegaji,tipepotongan,updateby)
								values ('" . $kodeorgExcel . "','" . $per . "','" . $kom . "','" . $_SESSION['standard']['userid'] . "')";
							$owlPDO->exec($str);
						}

						$strx = "select * from " . $dbname . ".sdm_potongandt
							where kodeorg='" . $kodeorgExcel . "'
							and periodegaji='" . $per . "'
							and tipepotongan='" . $kom . "'
							and nik='" . $karyawanid . "'";
						$resx = fetchdata($strx);

						if (count($resx) > 0) {
							$str = "update " . $dbname . ".sdm_potongandt set
								jumlahpotongan='" . $jumlahPotonganSql . "',
								keterangan='" . $keterangan . "',
								updateby='" . $_SESSION['standard']['userid'] . "'
								where kodeorg='" . $kodeorgExcel . "'
								and periodegaji='" . $per . "'
								and tipepotongan='" . $kom . "'
								and nik='" . $karyawanid . "'";
							$owlPDO->exec($str);
						} else {
							$str = "insert into " . $dbname . ".sdm_potongandt
								(kodeorg,tipepotongan,periodegaji,nik,jumlahpotongan,keterangan,updateby)
								values ('" . $kodeorgExcel . "','" . $kom . "','" . $per . "','" . $karyawanid . "','" . $jumlahPotonganSql . "','" . $keterangan . "','" . $_SESSION['standard']['userid'] . "')";
							$owlPDO->exec($str);
						}

						$i++;
					}

					if ($previewOnly) {
						$strPrev = "select nik, jumlahpotongan, keterangan from " . $dbname . ".sdm_potongandt
							where kodeorg='" . $org . "' and periodegaji='" . $per . "' and tipepotongan='" . $kom . "'
							order by nik";
						$resPrev = fetchdata($strPrev);
						$owlPDO->rollback();

						$infoPrev = "<table cellpadding=2 cellspacing=0 border=0 style='margin-bottom:8px'>
							<tr><td>" . $_SESSION['lang']['unitkerja'] . "</td><td>:</td><td><b>" . $org . " - " . $nmOrg[$org] . "</b></td></tr>
							<tr><td>" . $_SESSION['lang']['periode'] . "</td><td>:</td><td><b>" . $per . "</b></td></tr>
							<tr><td>" . $_SESSION['lang']['potongan'] . "</td><td>:</td><td><b>" . $optNmPotongan[$kom] . "</b></td></tr>
						</table>";

						if (empty($resPrev)) {
							echo $infoPrev . "<i>Tidak ada data potongan yang akan disimpan untuk unit ini.</i>";
							break;
						}

						$totPrev = 0;
						$streamPrev = $infoPrev . "<table cellpadding=6 cellspacing=0 border=1 class=sortable style='width:100%;border-collapse:collapse;'>
							<thead><tr class=rowheader>
								<th align=center>No</th>
								<th align=center>NIK</th>
								<th align=center>Nama Karyawan</th>
								<th align=center>Jumlah Potongan</th>
								<th align=center>Keterangan</th>
							</tr></thead><tbody>";
						$noPrev = 0;
						foreach ($resPrev as $pr) {
							$noPrev++;
							$totPrev += $pr['jumlahpotongan'];
							$streamPrev .= "<tr class=rowcontent>
								<td align=center>" . $noPrev . "</td>
								<td align=center>" . $optNikKar[$pr['nik']] . "</td>
								<td>" . $optNmKar[$pr['nik']] . "</td>
								<td align=right>" . number_format($pr['jumlahpotongan'], 0) . "</td>
								<td>" . $pr['keterangan'] . "</td>
							</tr>";
						}
						$streamPrev .= "<tr class=rowcontent style='font-weight:bold;background-color:#F5F5F5;'>
							<td colspan=3 align=center>Total</td>
							<td align=right>" . number_format($totPrev, 0) . "</td>
							<td></td>
						</tr>";
						$streamPrev .= "</tbody></table>";
						echo $streamPrev;
						break;
					}

					$owlPDO->commit();
				} catch (Exception $e) {
					if ($owlPDO->inTransaction()) {
						$owlPDO->rollback();
					}
					echo "Error, " . addslashes($e->getMessage());
					die();
				}
			} else {
				exit("Warning : Format file upload harus .xls atau .xlsx");
			}
		}
		break;
}


switch ($param['proses']) {
	case 'loadNewData':
		echo "<table cellspacing='1' cellpadding=5 border='0' class='sortable' style='width:100%'>
			 <thead>
			 <tr class=rowheader>
			 <th align=center>No</th>
			 <th align=center>" . $_SESSION['lang']['kodeorg'] . "</th>
			 <th align=center>" . $_SESSION['lang']['namaorganisasi'] . "</th>
			 <th align=center>" . $_SESSION['lang']['periodegaji'] . "</th>
			 <th align=center>" . $_SESSION['lang']['potongan'] . "</th>
			 <th align=center>" . $_SESSION['lang']['total'] . "</th>
			 <th align=center>" . $_SESSION['lang']['updatetime'] . "</th>
			 <th align=center>" . $_SESSION['lang']['updateby'] . "</th>
			 <th hidden align=center>Posting By</th>
			 <th align=center>Action</th>
			 </tr>
			 </thead><tbody>";
		$whrCr = "";
		if ($param['periodecr'] != '') {
			$whrCr .= " and periodegaji like '%" . $param['periodecr'] . "%'";
		}
		if ($param['tipePotCr'] != '') {
			$whrCr .= " and tipepotongan= '" . $param['tipePotCr'] . "'";
		}
		if ($param['kdOrgCr'] != '') {
			$whrCr .= " and kodeorg= '" . $param['kdOrgCr'] . "'";
		}
		$limit = 15;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = intval($_POST['page']);
			if ($page < 0) $page = 0;
		}
		$offset = $page * $limit;
		$optOrg2 = getOrgDetail(1);

		$dtisi = 1;
		foreach ($optOrg2 as $key => $nmorg) {
			if ($dtisi == 1) {
				$lstorg = "'" . $key . "'";
				$dtisi = 2;
			} else {

				$lstorg .= ",'" . $key . "'";
			}
		}
		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_potonganht 
			  where substring(kodeorg,1,4) in (" . $lstorg . ")  " . $whrCr . " order by `periodegaji` desc,`kodeorg` asc"; // echo $ql2;
		$slvhc = "select * from " . $dbname . ".sdm_potonganht 
				where substring(kodeorg,1,4) in (" . $lstorg . ") " . $whrCr . "
				order by `periodegaji` desc,`kodeorg` asc limit " . $offset . "," . $limit . "";

		$query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$jlhbrs = $jsl->jmlhrow;
		}
		//echo $slvhc;
		$qlvhc = $owlPDO->query($slvhc) or die(print " Gagal: " . PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
		$user_online = $_SESSION['standard']['userid'];
		$no = 0;

		while ($rlvhc = $qlvhc->fetch()) {
			$ttlpot = 0;
			$thnPeriod = substr($rlvhc['periodegaji'], 0, 7);
			$whrd = "kodeorganisasi='" . $rlvhc['kodeorg'] . "'";
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', $whrd);

			$sql22 = "select sum(jumlahpotongan) as pot from " . $dbname . ".sdm_potongandt 
				where periodegaji='" . $rlvhc['periodegaji'] . "' and tipepotongan='" . $rlvhc['tipepotongan'] . "' and kodeorg='" . $rlvhc['kodeorg'] . "'  " . $whrCr . " group by tipepotongan, periodegaji, kodeorg";

			$aaa = $owlPDO->query($sql22) or die(print " Gagal: " . PDOException::getMessage());
			$aaa->setFetchMode(PDO::FETCH_ASSOC);
			while ($sss = $aaa->fetch()) {
				@$ttlpot = $sss['pot'];
			}
			$no += 1;
			echo "
			<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td>" . $rlvhc['kodeorg'] . " - " . getNamaOrg($rlvhc['kodeorg'], 'namaorganisasi') . "</td>
				<td>" . $optNmOrg[$rlvhc['kodeorg']] . "</td>
				<td align=center>" . $rlvhc['periodegaji'] . "</td>
				<td>" . $optNmPotongan[$rlvhc['tipepotongan']] . "</td>
				<td align=right>" . number_format($ttlpot) . "</td>
				<td align=center>" . $rlvhc['updatetime'] . "</td>
				<td align=center>" . getNamaKaryawan($rlvhc['updateby']) . "</td>
				<td hidden align=center>" . getNamaKaryawan($rlvhc['postingby']) . "</td>

				<td align=center>";

			$arr = "##kdorg##per";
			$sGp = "select DISTINCT sudahproses from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $rlvhc['kodeorg'] . "' and periode='" . $rlvhc['periodegaji'] . "' ";
			$qGp = $owlPDO->query($sGp) or die(print " Gagal: " . PDOException::getMessage());
			$qGp->setFetchMode(PDO::FETCH_ASSOC);
			$rGp = $qGp->fetch();

			if ($rGp['sudahproses'] == 0) {
				if ($_SESSION['standard']['userid'] == $rlvhc['updateby']) {
					echo "<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $rlvhc['kodeorg'] . "','" . $rlvhc['periodegaji'] . "','" . $rlvhc['tipepotongan'] . "');\">";
					echo "
						 <img class=zImgBtn onclick=\"delData('" . $rlvhc['kodeorg'] . "','" . $rlvhc['periodegaji'] . "','" . $rlvhc['tipepotongan'] . "')\" src=images/application/application_delete.png class=zImgBtn  title='Delete' >
						 <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_potonganht','" . $rlvhc['kodeorg'] . "," . $rlvhc['periodegaji'] . "," . $rlvhc['tipepotongan'] . "','','sdm_slave_potonganPdf',event)\">
						 <img onclick=excel(event,'" . $rlvhc['kodeorg'] . "','" . $rlvhc['periodegaji'] . "','" . $rlvhc['tipepotongan'] . "') src=images/excel.jpg class=resicon title='MS.Excel'>
						 <img title='" . $_SESSION['lang']['upload'] . "' class=zImgBtn onclick=\"showupload(event,'" . $rlvhc['kodeorg'] . "','" . $rlvhc['periodegaji'] . "','" . $rlvhc['tipepotongan'] . "')\" src='images/upload-2-xxl.png'/>";
				} else {
					echo "<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_potonganht','" . $rlvhc['kodeorg'] . "," . $rlvhc['periodegaji'] . "," . $rlvhc['tipepotongan'] . "','','sdm_slave_potonganPdf',event)\">
						 <img onclick=excel(event,'" . $rlvhc['kodeorg'] . "','" . $rlvhc['periodegaji'] . "','" . $rlvhc['tipepotongan'] . "') src=images/excel.jpg class=resicon title='MS.Excel'>
						 <img title='" . $_SESSION['lang']['upload'] . "' class=zImgBtn onclick=\"showupload(event,'" . $rlvhc['kodeorg'] . "','" . $rlvhc['periodegaji'] . "','" . $rlvhc['tipepotongan'] . "')\" src='images/upload-2-xxl.png'/>";
				}
			} else {
				echo "<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_potonganht','" . $rlvhc['kodeorg'] . "," . $rlvhc['periodegaji'] . "," . $rlvhc['tipepotongan'] . "','','sdm_slave_potonganPdf',event)\">
				<img onclick=excel(event,'" . $rlvhc['kodeorg'] . "','" . $rlvhc['periodegaji'] . "','" . $rlvhc['tipepotongan'] . "') src=images/excel.jpg class=resicon title='MS.Excel'>
				<img title='" . $_SESSION['lang']['upload'] . "' class=zImgBtn onclick=\"showupload(event,'" . $rlvhc['kodeorg'] . "','" . $rlvhc['periodegaji'] . "','" . $rlvhc['tipepotongan'] . "')\" src='images/upload-2-xxl.png'/>";
			}
			echo "</td></tr>";
		}
		echo "</tbody><tfoot>
		<tr><td colspan=10 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " .  $jlhbrs . "<br />
		<button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>
		</tr>";
		echo "</tfoot></table>";
		break;

	case 'getPrd':
		$optPrd .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sGet = "select distinct periode from " . $dbname . ".sdm_5periodegaji 
			   where kodeorg='" . $param['kdOrg'] . "' and sudahproses=0 and jenisgaji='H' order by periode desc";
		$qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
		$qGet->setFetchMode(PDO::FETCH_ASSOC);
		while ($rGet = $qGet->fetch()) {
			$optPrd .= "<option value=" . $rGet['periode'] . ">" . $rGet['periode'] . "</option>";
		}
		echo $optPrd;
		break;

	case 'getPrd2':
		$optPrd .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sGet = "select distinct periode from " . $dbname . ".sdm_5periodegaji 
			   where kodeorg='" . $param['kdOrg'] . "' and sudahproses=0 and jenisgaji='H' order by periode desc";
		$qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
		$qGet->setFetchMode(PDO::FETCH_ASSOC);
		while ($rGet = $qGet->fetch()) {
			$optPrd .= "<option value=" . $rGet['periode'] . ">" . $rGet['periode'] . "</option>";
		}
		echo $optPrd;
		break;

	case 'saveData':
		if (($param['rupPot'] == 0) || ($param['rupPot'] == '')) {
			exit("error: " . $_SESSION['lang']['potongan'] . " can't empty");
		}
		if ($param['krywnId'] == '') {
			exit("error: " . $_SESSION['lang']['namakaryawan'] . " can't empty");
		}

		$optData = makeOption($dbname, 'sdm_potonganht', 'periodegaji,tipepotongan', $whrPrdData);
		$scek = "select distinct * from " . $dbname . ".sdm_potonganht where periodegaji='" . $param['periode'] . "' "
			. " and tipepotongan='" . $param['tipePot'] . "' and kodeorg='" . $param['kdOrg'] . "'";
		$qcek = $owlPDO->query($scek) or die(print " Gagal: " . PDOException::getMessage());
		$rcek = owlBaris($qcek);
		$sInsHt = "insert into " . $dbname . ".sdm_potonganht (`kodeorg`,`periodegaji`,`tipepotongan`,`updateby`) values ";
		$sDet = "insert into " . $dbname . ".sdm_potongandt (`kodeorg`,`periodegaji`,`keterangan`,`nik`,`jumlahpotongan`,`tipepotongan`,`updateby`) values";
		$whr = "nik='" . $param['krywnId'] . "' and tipepotongan='" . $param['tipePot'] . "' and periodegaji='" . $param['periode'] . "'";
		$optAdKgk = makeOption($dbname, 'sdm_potongandt', 'nik,tipepotongan', $whr);

		if ($optAdKgk[$param['krywnId']] != '') {
			exit('warning :' . $_SESSION['lang']['exist']);
		}
		if ($rcek < 1) {

			$sInsHt .= "('" . $param['kdOrg'] . "','" . $param['periode'] . "','" . $param['tipePot'] . "','" . $_SESSION['standard']['userid'] . "')";
			try {
				$owlPDO->exec($sInsHt);

				$sDet .= "('" . $param['kdOrg'] . "','" . $param['periode'] . "','" . $param['ketPot'] . "','" . $param['krywnId'] . "','" . $param['rupPot'] . "'
						,'" . $param['tipePot'] . "','" . $_SESSION['standard']['userid'] . "')";
				try {
					$owlPDO->exec($sDet);
				} catch (PDOException $e) {
					exit("error: DB Error " . $e->getMessage() . "___" . $sDet);
				}
			} catch (PDOException $e) {
				exit("error: DB Error " . $e->getMessage() . "___" . $sInsHt);
				die();
			}
		} else {
			$sDet .= "('" . $param['kdOrg'] . "','" . $param['periode'] . "','" . $param['ketPot'] . "','" . $param['krywnId'] . "','" . $param['rupPot'] . "'
					,'" . $param['tipePot'] . "','" . $_SESSION['standard']['userid'] . "')";
			try {
				$owlPDO->exec($sDet);
			} catch (PDOException $e) {
				exit("error: DB Error " . $e->getMessage() . "___" . $sDet);
				die();
			}
		}
		break;

	case 'updateDetail':
		if (($param['rupPot'] == '') || (intval($param['rupPot']) == '0')) {
			exit("error: " . $_SESSION['lang']['potongan'] . " can't empty");
		}
		$sUpd = "update " . $dbname . ".sdm_potongandt set";
		$sUpd .= " jumlahpotongan='" . $param['rupPot'] . "',keterangan='" . $param['ketPot'] . "',updateby='" . $_SESSION['standard']['userid'] . "'";
		$sUpd .= " where tipepotongan='" . $param['tipePot'] . "' and nik='" . $param['krywnId'] . "' 
				 and kodeorg='" . $optLokasiTugas[$param['krywnId']] . "' and periodegaji='" . $param['periode'] . "'";
		try {
			$owlPDO->exec($sUpd);
		} catch (PDOException $e) {
			exit("error: db error" . $e->getMessage() . "___" . $sUpd);
			die();
		}
		break;

	case 'delData':
		$sDel = "delete from " . $dbname . ".sdm_potonganht where " . $whrPrdData . ""; // echo "___".$sDel;exit();
		try {
			$owlPDO->exec($sDel);

			$sDelDetail = "delete from " . $dbname . ".sdm_potongandt where " . $whrPrdData . "";
			try {
				$owlPDO->exec($sDelDetail);
			} catch (PDOException $e) {
				echo "DB Error : " . $e->getMessage();
			}


			$sDelDetailGambar = "delete from " . $dbname . ".listfile_sdm_potongan where kodeorg='" . $param['kdOrg'] . "' and periode='" . $param['periode'] . "' and idkomponen='" . $param['tipePot'] . "' ";
			try {
				$owlPDO->exec($sDelDetailGambar);
			} catch (PDOException $e) {
				echo "DB Error : " . $e->getMessage();
			}
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
			die();
		}
		break;

	case 'delDetail':
		$sDel = "delete from " . $dbname . ".sdm_potongandt where " . $whrPrdDataDetail . " and nik='" . $param['krywnId'] . "'";
		try {
			$owlPDO->exec($sDel);
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
			die();
		}
		break;

	case 'createTable':

		if (isset($param['statUpdate']) and $param['statUpdate'] != 1) {
			if ($param['kdOrg'] == '') {
				exit('warning : ' . $_SESSION['lang']['unitkerja'] . ' ' . $_SESSION['lang']['kosong']);
			}
			if ($param['periode'] == '') {
				exit('warning : ' . $_SESSION['lang']['periode'] . ' ' . $_SESSION['lang']['kosong']);
			}
			if ($param['tipePot'] == '') {
				exit('warning : ' . $_SESSION['lang']['potongan'] . ' ' . $_SESSION['lang']['kosong']);
			}
			#cek dah ada atau belum sm masih di dalam periode akutansi
			$whrPrd = "kodeorg='" . $param['kdOrg'] . "' and periode='" . $param['periode'] . "'";
			$optPeriodeAkn = makeOption($dbname, 'setup_periodeakuntansi', 'periode,tutupbuku', $whrPrd);
			$optData = makeOption($dbname, 'sdm_potonganht', 'periodegaji,tipepotongan', $whrPrdData);
			if ($optPeriodeAkn[$param['periode']] == 1) {
				exit("Error: Accounting period has been closed");
			}
			if (!empty($optData[$param['periode']])) {
				exit("error: This date and Organization Name already exist");
			}
		}

		$where = " lokasitugas='" . $param['kdOrg'] . "' and (tanggalkeluar='0000-00-00' or  tanggalkeluar>= '" . $tgl . "') and tipekaryawan != 0 ";

		$optTipeKar =  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
		$optKry = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		$strdkar = "select karyawanid from " . $dbname . ".datakaryawan_hist a where " . $where . "  and approval_status='8' and version_type='B' and periodegaji='" . substr($tgl, 0, 6) . "' ";
		$resdkar = fetchdata($strdkar);
		if (count($resdkar) > 0) {

			$sKry = "select namakaryawan,nik,karyawanid,lokasitugas,tipekaryawan from " . $dbname . ".datakaryawan_hist where  " . $where . " and approval_status='8' and version_type='B' and periodegaji='" . substr($tgl, 0, 6) . "' order by namakaryawan";
		} else {
			$sKry = "select namakaryawan,nik,karyawanid,lokasitugas,tipekaryawan from " . $dbname . ".datakaryawan where  " . $where . " order by namakaryawan asc";
		}
		//$sKry="select namakaryawan,nik,karyawanid,lokasitugas,tipekaryawan from ".$dbname.".datakaryawan where ".$where." order by namakaryawan asc";
		$qKry = $owlPDO->query($sKry) or die(print " Gagal: " . PDOException::getMessage());
		$qKry->setFetchMode(PDO::FETCH_ASSOC);
		while ($rKry = $qKry->fetch()) {
			$optKry .= "<option value=" . $rKry['karyawanid'] . "> [" . $rKry['nik'] . "] - " . $rKry['namakaryawan'] . "</option>";
		}
		$table = "<table id='ppDetailTable' cellspacing='1' border='0' class='sortable' width='600px'>
		<thead>
		<tr class=rowheader>
		<td align=center style='width:260px'>" . $_SESSION['lang']['namakaryawan'] . "</td>
		<td align=center style=width:100px>" . $_SESSION['lang']['potongan'] . " (Rp.)</td>
		<td align=center style=width:200px>" . $_SESSION['lang']['keterangan'] . "</td>
		<td style=width:40px>Action</td>
		</tr></thead>
		<tbody id='detailBody'>";
		$table .= "<tr class=rowcontent>
		<td><select id=krywnId name=krywnId style='width:220px'>" . $optKry . "</select>
		<img class='zImgBtn' style='position:relative;top:5px' src='images/onebit_02.png' onclick=\"getKary('Search : ','1',event);\"  />
		</td>
		<td><input type=text class='myinputtextnumber' id=rpPot style=width:100px onkeypress='return angka_doang(event)' /></td>
		<td><input type=text class=myinputtext id=ketPot style=width:200px onkeypress='return tanpa_kutip(event)' /></td>
		<td align=center><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/></td>
		</tr>
		";
		$table .= "</tbody></table>";
		echo $table;
		break;

	case 'loadDetail':
		// $optOrg2 = getOrgDetail(1);

		// $dtisi=1;
		// foreach ($optOrg2 as $key => $nmorg) {
		// 	if($dtisi==1){
		// 			$dtisi=2;
		// 			$lstorg="'".$key."'";
		// 		}else{
		// 			$lstorg.=",'".$key."'";
		// 		}
		// }

		$sDet = "select * from " . $dbname . ".sdm_potongandt where periodegaji='" . $param['periode'] . "' "
			. "and kodeorg = '" . $param['kdOrg'] . "'
			  and tipepotongan='" . $param['tipePot'] . "' order by nik asc";

		$qDet = $owlPDO->query($sDet) or die(print " Gagal: " . PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		$tot = 0;
		while ($rDet = $qDet->fetch()) {
			$no += 1;
			setIt($optNmKar[$rDet['updateby']], '');
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=center>" . $optNikKar[$rDet['nik']] . "</td>";
			$tab .= "<td>" . $optNmKar[$rDet['nik']] . "</td>";
			$tab .= "<td align=right>" . number_format($rDet['jumlahpotongan'], 0) . "</td>";
			$tab .= "<td>" . $rDet['keterangan'] . "</td>";
			$tab .= "<td>" . $optNmKar[$rDet['updateby']] . "</td>";
			$tab .= "<td align=center>
				<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editDetail('" . $rDet['nik'] . "','" . $rDet['jumlahpotongan'] . "','" . $rDet['keterangan'] . "');\">
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDetail('" . $rDet['kodeorg'] . "','" . $rDet['periodegaji'] . "','" . $rDet['nik'] . "','" . $rDet['tipepotongan'] . "');\" >	</td>";
			$tab .= "</tr>";
			$tot += $rDet['jumlahpotongan'];
		}
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td colspan=3 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>";
		$tab .= "<td align=right><b>" . number_format($tot, 0) . "</b></td><td  colspan=3>&nbsp;</td></tr>";
		echo $tab;
		break;

	case 'getKary':
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab .= "<thead>";
		$tab .= "<tr><td align=center>" . $_SESSION['lang']['nik'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>";
		$tab .= "<td align=center>" . $_SESSION['lang']['lokasitugas'] . "</td>";
		$tab .= "</tr></thead><tbody>";
		$where = " lokasitugas='" . $param['unit'] . "' and (tanggalkeluar='0000-00-00' or tanggalkeluar<'" . $tgl . "')  and tipekaryawan!=0 ";
		if ($optTipe[$param['unit']] == 'KANWIL') {
			$where = " lokasitugas in (select distinct kodeunit from " . $dbname . ".bgt_regional_assignment where regional='" . $_SESSION['empl']['regional'] . "')"
				. " and (tanggalkeluar='0000-00-00' or tanggalkeluar>= '" . $tgl . "') and tipekaryawan!=0 ";
		}
		if ($param['nmkary'] != '') {
			$where .= "and (namakaryawan like '%" . $param['nmkary'] . "%' or nik like '%" . $param['nmkary'] . "%')";
		}
		$sKry = "select namakaryawan,nik,karyawanid,lokasitugas from " . $dbname . ".datakaryawan where " . $where . " order by namakaryawan asc";
		$qDt = $owlPDO->query($sKry) or die(print " Gagal: " . PDOException::getMessage());
		$qDt->setFetchMode(PDO::FETCH_ASSOC);
		while ($rDt = $qDt->fetch()) {
			$clid = "onclick=setKary('" . $rDt['karyawanid'] . "') style=cursor:pointer;";
			$tab .= "<tr " . $clid . " class=rowcontent><td align=center>" . $rDt['nik'] . "</td>";
			$tab .= "<td>" . $rDt['namakaryawan'] . "</td>";
			$tab .= "<td>" . $rDt['lokasitugas'] . "</td>";
			$tab .= "</tr>";
		}
		$tab .= "</tbody></table>";
		echo $tab;
		break;

	case 'showupload':
		$tab = "";
		$tab .= "<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
		$tab .= "<tr>
					<td>" . $_SESSION['lang']['unit'] . "</td>
					<td>:</td>
					<td>
						<label id='kodeorg' style='display:none'>" . $org . "</label>
						<label style='font-weight:bold'>" . $nmOrg[$org] . "</label>
					</td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td>
					<td>:</td>
					<td>
						<label id='periode' style='font-weight:bold'>" . $per . "</label>
					</td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['jenis'] . "</td>
					<td>:</td>
					<td>
						<label id='komponen' style='display:none'>" . $kom . "</label>
						<label style='font-weight:bold'>" . $optNmPotongan[$kom] . "</label>
					</td>
				</tr>";
		$tab .= "<tr><td colspan=4><hr></td></tr>
					<tr>
						<td>Filename</td>
						<td>:</td>
						<td>
							<input type='file' name='upload' id='upload' >
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
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='listfiles'>
					</tbody>
				</table>
			</fieldset> ";

		echo $tab;
		break;
	case 'loadfiles':
		$no = 0;
		$tab = "";
		$str = "select * from " . $dbname . ".listfile_sdm_potongan where kodeorg = '" . $org . "' and status='1' and periode='" . $per . "' and idkomponen='" . $kom . "'";
		$res = fetchData($str);
		if (empty($res)) {
			$tab .= "<tr class=rowcontent><td colspan=4 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			foreach ($res as $key => $val) {
				$no++;
				$tab .= "<tr class=rowcontent>
						<td style='text-align:center'>" . $no . "</td>";

				if ($val['formaticon'] == '.jpeg' || $val['formaticon'] == '.jpg') {
					$tab .= "<td style='text-align:center'>
							<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
						</td>";
				} elseif ($val['formaticon'] == '.png') {
					$tab .= "<td style='text-align:center'>
							<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
						</td>";
				} elseif ($val['formaticon'] == '.pdf') {
					$tab .= "<td style='text-align:center'>
							<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
						</td>";
				} elseif ($val['formaticon'] == '.xls' || $val['formaticon'] == '.xlsx') {
					$tab .= "<td style='text-align:center'>
							<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
						</td>";
				} elseif ($val['formaticon'] == '.doc' || $val['formaticon'] == '.docx') {
					$tab .= "<td style='text-align:center'>
							<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
						</td>";
				} else {
					$tab .= "<td style='text-align:center'>
							<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
						</td>";
				}

				$tab .= "<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','" . $val['namafile'] . "')\">" . $val['namafile'] . "</td>
						<td align=center>
							<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";

				$tab .= "<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $val['kodeorg'] . "','" . $val['periode'] . "','" . $val['idkomponen'] . "','" . $val['namafile'] . "');\" >";

				$tab . "	</td>
					</tr>";
			}
		}

		echo $tab;
		break;
	case 'deletefile':
		$str = "delete from " . $dbname . ".listfile_sdm_potongan where kodeorg='" . $org . "' and periode='" . $per . "' and idkomponen='" . $kom . "' and namafile='" . $namafile . "'";
		try {
			$owlPDO->exec($str);
			$pathx = $path . $namafile;
			unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;
	default:
		break;
}
