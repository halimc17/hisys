<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$comId = checkPostGet('comId', '');
$kdVhc = checkPostGet('kdVhc', '');
$akun = checkPostGet('akun', '');
$jnsVhc = checkPostGet('jnsVhc', '');
$alokasi = checkPostGet('alokasi', '');
$tglAwal = tanggalsystem(checkPostGet('tglAwal', ''));
$tglAkhir = tanggalsystem(checkPostGet('tglAkhir', ''));
$where2 = ' kelompokbarang=351';
$optBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', $where2);

$nmJenis =  makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');

switch ($proses) {
	case 'getJnsVhc':
		$optOrg = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
		$optJnsvhc = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$sjnsVhc = "select distinct jenisvhc from " . $dbname . ".vhc_runht where kodeorg='" . substr($comId, 0, 4) . "' group by jenisvhc"; //echo "warning:".$sjnsVhc;
		$qjnsVhc = $owlPDO->query($sjnsVhc) or die(print " Gagal: " . PDOException::getMessage());
		$qjnsVhc->setFetchMode(PDO::FETCH_ASSOC);
		while ($rjnsVhc = $qjnsVhc->fetch()) {
			$optJnsvhc .= "<option value='" . $rjnsVhc['jenisvhc'] . "'>" . $optOrg[$rjnsVhc['jenisvhc']] . "</option>";
		}
		echo $optJnsvhc;
		break;

	case 'getKdvhc':
		$optKvhc = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$skdVhc = "select kodevhc from " . $dbname . ".vhc_runht where jenisvhc='" . $jnsVhc . "' group by kodevhc"; //echo "warning:".$skdVhc;
		$qkdVhc = $owlPDO->query($skdVhc) or die(print " Gagal: " . PDOException::getMessage());
		$qkdVhc->setFetchMode(PDO::FETCH_ASSOC);
		while ($rkdVhc = $qkdVhc->fetch()) {
			$optKvhc .= "<option value='" . $rkdVhc['kodevhc'] . "'>" . $rkdVhc['kodevhc'] . " [" . getVhc($rkdVhc['kodevhc'], 'detailvhc') . "]</option>";
		}
		echo $optKvhc;
		break;

	case 'get_result':
		if ($comId == '') {
			echo "warning:Unit Tidak Boleh Kosong";
			exit();
		}

		if ($tglAkhir == '' || $tglAwal = '') {
			echo "warning:Tanggal Tidak Boleh Kosong";
			exit();
		}

		$where = "";
		if ($jnsVhc != '') {
			$where .= " and jenisvhc='" . $jnsVhc . "'";
		}

		if ($kdVhc != '') {
			$where .= " and kodevhc='" . $kdVhc . "'";
		}

		if ($alokasi != '') {
			$where .= " and alokasibiaya like '" . $alokasi . "%'";
		}

		if ($akun != '') {
			$where .= " and a.jenispekerjaan in (select kodekegiatan from " . $dbname . ".vhc_kegiatan where noakun='" . $akun . "' ) ";
		}

		$sql = "select distinct a.notransaksi,a.jenispekerjaan,a.alokasibiaya,a.jumlah,c.upah,c.premi,b.kodevhc,b.jenisvhc,
				  b.tanggal,a.jumlahrit,a.beratmuatan,a.biaya,a.keterangan,a.satuan,b.jenisbbm,b.jlhbbm
				  from " . $dbname . ".vhc_rundt a left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
				  left join " . $dbname . ".vhc_runhk c on a.notransaksi=c.notransaksi where kodeorg='" . substr($comId, 0, 4) . "' and 
				  b.tanggal between  '" . tanggalsystem($_POST['tglAwal']) . "' and '" . $tglAkhir . "' " . $where . " group by a.notransaksi,a.alokasibiaya,a.jenispekerjaan, a.beratmuatan
				  order by b.tanggal,a.notransaksi asc";

		if ($alokasi != '') {
			$sql = "select distinct a.notransaksi,a.jenispekerjaan,a.alokasibiaya,a.jumlah,c.upah,c.premi,b.kodevhc,b.jenisvhc,
                      b.tanggal,a.jumlahrit,a.beratmuatan,a.biaya,a.keterangan,a.satuan,b.jenisbbm,b.jlhbbm
                      from " . $dbname . ".vhc_rundt a left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
                      left join " . $dbname . ".vhc_runhk c on a.notransaksi=c.notransaksi where
                      b.tanggal between  '" . tanggalsystem($_POST['tglAwal']) . "' and '" . $tglAkhir . "' " . $where . " group by a.notransaksi,a.alokasibiaya,a.jenispekerjaan, a.beratmuatan
                      order by b.tanggal,a.notransaksi asc";
		}


		#query upah dipisah
		$str = "select sum(upah) as upah,sum(premi) as premi,notransaksi from " . $dbname . ".vhc_runhk_vw where  
                      tanggal between '" . tanggalsystem($_POST['tglAwal']) . "' and '" . $tglAkhir . "' group by notransaksi ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$upah[$bar['notransaksi']] = $bar['upah'];
			$premi[$bar['notransaksi']] = $bar['premi'];
		}

		$tab .= "<table cellspacing=1 cellpadding=5 border=0 class=sortable>
			<thead>
        	<tr class=rowheader>
				<th align=center>No.</th>
				<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
				<th align=center>" . $_SESSION['lang']['tanggal'] . " </th>
				<th align=center>" . $_SESSION['lang']['jenisvch'] . " - " . $_SESSION['lang']['namajenisvhc'] . "</th>
				<th align=center>" . $_SESSION['lang']['kodevhc'] . "</th>
				<th align=center>" . $_SESSION['lang']['nopol'] . "</th>
				<th align=center>" . $_SESSION['lang']['detail'] . "</th>
				<th align=center>HM/KM</th>
				<th align=center>" . $_SESSION['lang']['vhc_jenis_bbm'] . "</th>
				<th align=center>" . $_SESSION['lang']['vhc_jumlah_bbm'] . "</th>
				<th align=center>" . $_SESSION['lang']['noakun'] . "</th>
				<th align=center>" . $_SESSION['lang']['kodekegiatan'] . "</th>
				<th align=center>" . $_SESSION['lang']['vhc_jenis_pekerjaan'] . "</th>
				<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
				<th align=center>" . $_SESSION['lang']['alokasibiaya'] . "</th>
				<th align=center>" . $_SESSION['lang']['vhc_berat_muatan'] . "</th>
				<th align=center>" . $_SESSION['lang']['jumlahrit'] . "</th>
				<th align=center>" . $_SESSION['lang']['biaya'] . "</th>			
				<th align=center>" . $_SESSION['lang']['upahpremi'] . "</th>
				<th align=center>" . $_SESSION['lang']['upahkerja'] . "</th>
				<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>    
            </tr>
        </thead>
        <tbody>";

		$arrPos = array("Operator", "Helper");
		$old = '';

		$qRvhc = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qRvhc->setFetchMode(PDO::FETCH_ASSOC);
		while ($res = $qRvhc->fetch()) {
			$sJns = "select *  from " . $dbname . ".vhc_kegiatan where kodekegiatan='" . $res['jenispekerjaan'] . "'";
			$qJns = $owlPDO->query($sJns) or die(print " Gagal: " . PDOException::getMessage());
			$qJns->setFetchMode(PDO::FETCH_ASSOC);
			$rJns = $qJns->fetch();
			$no += 1;

			if ($res['notransaksi'] == $old) {
				$res['biaya'] = 0;
				$res['premi'] = 0;
				$res['upah'] = 0;
			} else {
				$res['premi'] = $premi[$res['notransaksi']];
				$res['upah'] = $upah[$res['notransaksi']];
			}

			$sBn = "select sum(jumlah) as totalhm from " . $dbname . ".vhc_rundt where notransaksi='" . $res['notransaksi'] . "'";
			$qBn = $owlPDO->query($sBn) or die(print " Gagal: " . PDOException::getMessage());
			$qBn->setFetchMode(PDO::FETCH_ASSOC);
			$rBn = $qBn->fetch();
			@$jmlhBbm = $res['jlhbbm'] * ($res['jumlah'] / $rBn['totalhm']);


			$tab .= "<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td>" . $res['notransaksi'] . "</td>
				<td>" . tanggalnormal($res['tanggal']) . "</td>
				<td>" . $res['jenisvhc'] . " - " . $nmJenis[$res['jenisvhc']] . "</td>
				<td>" . $res['kodevhc'] . "</td>
				<td>" . getNOpol($res['kodevhc']) . "</td>
				<td>" . getNOpol($res['kodevhc'], 'd') . "</td>
				<td align=right>" . number_format($res['jumlah'], 2) . "</td>
				<td>" . $optBrg[$res['jenisbbm']] . "</td>
				<td align=right>" . number_format($jmlhBbm, 2) . "</td>
				<td>" . $rJns['noakun'] . "</td>
				<td>" . $rJns['kodekegiatan'] . "</td>
				<td>" . $rJns['namakegiatan'] . "</td>
				<td>" . $rJns['satuan'] . "</td>
				<td>" . getNamaOrg($res['alokasibiaya']) . "</td>
				<td align=right>" . number_format($res['beratmuatan'], 2) . "</td>
				<td align=right>" . number_format($res['jumlahrit'], 2) . "</td>
				<td align=right>" . number_format($res['biaya'], 2) . "</td>
				<td align=right>" . number_format($res['premi'], 2) . "</td>
				<td align=right>" . number_format($res['upah'], 2) . "</td>
				<td>" . $res['keterangan'] . "</td>
			</tr>";
			$old = $res['notransaksi'];
			$jmlhk += $res['jumlah'];
			$jmlbbm += $jmlhBbm;
			$jmlbrtmuatan += $res['beratmuatan'];
			$jmlrit += $res['jumlahrit'];
			$jmlbiaya += $res['biaya'];
			$jmlpremi += $res['premi'];
			$jmlupah += $res['upah'];
		}
		$tab .= "
		<tr class=rowcontent>
		<td colspan=7 align=center>TOTAL</td>
		<td align=right>" . number_format($jmlhk, 2) . "</td>
		<td></td>
		<td align=right>" . number_format($jmlbbm, 2) . "</td>
		<td colspan=5></td>
		<td align=right>" . number_format($jmlbrtmuatan, 2) . "</td>
		<td align=right>" . number_format($jmlrit, 2) . "</td>
		<td align=right>" . number_format($jmlbiaya, 2) . "</td>
		<td align=right>" . number_format($jmlpremi, 2) . "</td>
		<td align=right>" . number_format($jmlupah, 2) . "</td>
		<td></td>
		</tr>
		";
		$tab .= "</tbody></table>";

		echo $tab;

		break;
	case 'getResultKry':
		$sRvhc = "select a.*,b.jenispekerjaan,b.jumlahrit,b.keterangan from " . $dbname . ".vhc_runht 
		a inner join " . $dbname . ".vhc_rundt b on a.notransaksi=b.notransaksi 
		inner join " . $dbname . ".vhc_runhk c on b.notransaksi=c.notransaksi 
		where c.idkaryawan='" . $kryId . "' order by a.tanggal asc";
		$qRvhc = $owlPDO->query($sRvhc) or die(print " Gagal: " . PDOException::getMessage());
		$qRvhc->setFetchMode(PDO::FETCH_ASSOC);
		while ($rRvhc = $qRvhc->fetch()) {
			$no += 1;
			echo "
		<tr class=rowcontent>
		<td>" . $no . "</td>
		<td align=center>" . $rRvhc['notransaksi'] . "</td>
		<td align=center>" . tanggalnormal($rRvhc['tanggal']) . "</td>
		<td align=center>" . $rRvhc['kmhmawal'] . "</td>
		<td align=center>" . $rRvhc['kmhmakhir'] . "</td>
		<td align=center>" . $rRvhc['jumlah'] . "</td>
		<td align=center>" . $rRvhc['jenispekerjaan'] . "</td>
		<td align=center>" . $rRvhc['keterangan'] . "</td>
		<td align=center>" . $rRvhc['jumlahrit'] . "</td>
		<td align=center>" . $rRvhc['jlhbbm'] . "</td>
		</tr>
		";
		}
		break;





	case 'excel':
		if ($comId == '') {
			echo "warning:Unit Tidak Boleh Kosong";
			exit();
		}
		if ($tglAkhir == '' || $tglAwal = '') {
			echo "warning:Tanggal Tidak Boleh Kosong";
			exit();
		}
		$str = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . substr($comId, 0, 4) . "'";
		$namapt = 'COMPANY NAME';
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$namapt = strtoupper($bar->namaorganisasi);
		}

		$stream = "
			<table>
			<tr><td colspan=15 align=center>" . strtoupper($_SESSION['lang']['laporanPekerjaan']) . "</td></tr>";
		if ($comId != '') {
			$stream .= "
			<tr><td colspan=6>" . $_SESSION['lang']['unit'] . ":" . $namapt . "</td></tr>";
		}

		$stream .= "
			<tr><td colspan=6>" . $_SESSION['lang']['periode'] . ":" . $_GET['tglAwal'] . "-" . $_GET['tglAkhir'] . "</td></tr>";

		$stream .= "
			<tr><td colspan=6>&nbsp;</td></tr>
			</table>
			<table border=1 bgcolor=#DEDEDE >
                            <tr>
                            <td align=center valign=middle>No.</td>
                            <td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
                            <td align=center>" . $_SESSION['lang']['tanggal'] . " </td>
                            <td align=center>" . $_SESSION['lang']['jenisvch'] . " " . $_SESSION['lang']['namajenisvhc'] . "</td>
                                
                            <td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>
                            <td align=center>HM/KM</td>
                            <td align=center>" . $_SESSION['lang']['vhc_jenis_bbm'] . "</td>
                            <td align=center>" . $_SESSION['lang']['vhc_jumlah_bbm'] . "</td>
                            <td align=center>" . $_SESSION['lang']['kodekegiatan'] . "</td>
                            <td align=center>" . $_SESSION['lang']['noakun'] . "</td>
                            <td align=center>" . $_SESSION['lang']['vhc_jenis_pekerjaan'] . "</td>
                                <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
                            <td align=center>" . $_SESSION['lang']['alokasibiaya'] . "</td>
                            <td align=center>" . $_SESSION['lang']['vhc_berat_muatan'] . "</td>
                            <td align=center>" . $_SESSION['lang']['jumlahrit'] . "</td>
                            <td align=center>" . $_SESSION['lang']['biaya'] . "</td>			
                            <td align=center>" . $_SESSION['lang']['upahpremi'] . "</td>
                            <td align=center>" . $_SESSION['lang']['upahkerja'] . "</td>
                            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>   
                            </tr>
                            </table>						
                            ";

		$stream .= "<table border='1'>";
		$where = "";
		if ($jnsVhc != '') {
			$where .= " and jenisvhc='" . $jnsVhc . "'";
		}
		if ($kdVhc != '') {
			$where .= " and kodevhc='" . $kdVhc . "'";
		}
		if ($alokasi != '') {
			$where .= " and alokasibiaya like'" . $alokasi . "%'";
		}

		if ($akun != '') {
			$where .= " and a.jenispekerjaan in (select kodekegiatan from " . $dbname . ".vhc_kegiatan where noakun='" . $akun . "' ) ";
		}

		$sql = "select distinct a.notransaksi,a.jenispekerjaan,a.alokasibiaya,a.jumlah,c.upah,c.premi,b.kodevhc,b.jenisvhc,
				  b.tanggal,a.jumlahrit,a.beratmuatan,a.biaya,a.keterangan,a.satuan,b.jenisbbm,b.jlhbbm
				  from " . $dbname . ".vhc_rundt a left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
				  left join " . $dbname . ".vhc_runhk c on a.notransaksi=c.notransaksi where kodeorg='" . substr($comId, 0, 4) . "' and 
				  b.tanggal between  '" . tanggalsystem($_GET['tglAwal']) . "' and '" . $tglAkhir . "' " . $where . " group by a.notransaksi,a.alokasibiaya,a.jenispekerjaan, a.beratmuatan
				  order by b.tanggal,a.notransaksi asc";

		if ($alokasi != '') {
			$sql = "select distinct a.notransaksi,a.jenispekerjaan,a.alokasibiaya,a.jumlah,c.upah,c.premi,b.kodevhc,b.jenisvhc,
                      b.tanggal,a.jumlahrit,a.beratmuatan,a.biaya,a.keterangan,a.satuan,b.jenisbbm,b.jlhbbm
                      from " . $dbname . ".vhc_rundt a left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
                      left join " . $dbname . ".vhc_runhk c on a.notransaksi=c.notransaksi where
                      b.tanggal between  '" . tanggalsystem($_GET['tglAwal']) . "' and '" . $tglAkhir . "' " . $where . " group by a.notransaksi,a.alokasibiaya,a.jenispekerjaan, a.beratmuatan
                      order by b.tanggal,a.notransaksi asc";
		}
		$old = '';



		#query upah dipisah
		$str = "select sum(upah) as upah,sum(premi) as premi,notransaksi from " . $dbname . ".vhc_runhk_vw where  
                      tanggal between '" . tanggalsystem($_POST['tglAwal']) . "' and '" . $tglAkhir . "' group by notransaksi ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$upah[$bar['notransaksi']] = $bar['upah'];
			$premi[$bar['notransaksi']] = $bar['premi'];
		}
		$no = 0;
		$arrPos = array("Operator", "Helper");
		$resx = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		while ($res = $resx->fetch()) {
			$sbrg = "select namabarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $res['jenisbbm'] . "'";
			$qbrg = $owlPDO->query($sbrg) or die(print " Gagal: " . PDOException::getMessage());
			$qbrg->setFetchMode(PDO::FETCH_ASSOC);
			$rbrg = $qbrg->fetch();


			$sJns = "select *  from " . $dbname . ".vhc_kegiatan where kodekegiatan='" . $res['jenispekerjaan'] . "'";
			$qJns = $owlPDO->query($sJns) or die(print " Gagal: " . PDOException::getMessage());
			$qJns->setFetchMode(PDO::FETCH_ASSOC);
			$rJns = $qJns->fetch();

			$no += 1;
			if (!isset($old) or $res['notransaksi'] == $old) {
				$res['biaya'] = 0;
				$res['premi'] = 0;
				$res['upah'] = 0;
			} else {
				$res['premi'] = $premi[$res['notransaksi']];
				$res['upah'] = $upah[$res['notransaksi']];
			}
			$sBn = "select sum(jumlah) as totalhm from " . $dbname . ".vhc_rundt where notransaksi='" . $res['notransaksi'] . "'";
			$qBn = $owlPDO->query($sBn) or die(print " Gagal: " . PDOException::getMessage());
			$qBn->setFetchMode(PDO::FETCH_ASSOC);
			$rBn = $qBn->fetch();
			@$jmlhBbm = $res['jlhbbm'] * ($res['jumlah'] / $rBn['totalhm']);
			$stream .= "
                    <tr class=rowcontent>
                    <td>" . $no . "</td>
			<td>" . $res['notransaksi'] . "</td>
			<td>" . $res['tanggal'] . "</td>
			<td>" . $res['jenisvhc'] . " - " . $nmJenis[$res['jenisvhc']] . "</td>
			<td>" . $res['kodevhc'] . "</td>
			<td align=right>" . number_format($res['jumlah'], 2) . "</td>
			<td>" . $optBrg[$res['jenisbbm']] . "</td>
			<td align=right>" . number_format($jmlhBbm, 2) . "</td>
                        <td>" . $rJns['kodekegiatan'] . "</td>
                        <td>" . $rJns['noakun'] . "</td>
			<td>" . $rJns['namakegiatan'] . "</td>
                            <td>" . $rJns['satuan'] . "</td>
			<td>" . $res['alokasibiaya'] . "</td>
			<td align=right>" . number_format($res['beratmuatan'], 2) . "</td>
			<td align=right>" . number_format($res['jumlahrit'], 2) . "</td>
			<td align=right>" . number_format($res['biaya'], 2) . "</td>
			<td align=right>" . number_format($res['premi'], 2) . "</td>
			<td align=right>" . number_format($res['upah'], 2) . "</td>
                        <td>" . $res['keterangan'] . "</td>    
                    </tr>";
			$old = $res['notransaksi'];
			$jmlhk += $res['jumlah'];
			$jmlbbm += $jmlhBbm;
			$jmlbrtmuatan += $res['beratmuatan'];
			$jmlrit += $res['jumlahrit'];
			$jmlbiaya += $res['biaya'];
			$jmlpremi += $res['premi'];
			$jmlupah += $res['upah'];
		}
		$stream .= "
		<tr class=rowcontent>
		<td colspan=5 align=center>TOTAL</td>
		<td align=right>" . number_format($jmlhk, 2) . "</td>
		<td></td>
		<td align=right>" . number_format($jmlbbm, 2) . "</td>
		<td colspan=5></td>
		<td align=right>" . number_format($jmlbrtmuatan, 2) . "</td>
		<td align=right>" . number_format($jmlrit, 2) . "</td>
		<td align=right>" . number_format($jmlbiaya, 2) . "</td>
		<td align=right>" . number_format($jmlpremi, 2) . "</td>
		<td align=right>" . number_format($jmlupah, 2) . "</td>
		<td></td>
		</tr>
		";

		//echo "warning:".$strx;
		//=================================================

		$stream .= "</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
		$dte = date("Hms");
		$nop_ = "ReportVehicleUsage__" . $dte;
		$gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
		gzwrite($gztralala, $stream);
		gzclose($gztralala);
		echo "<script language=javascript1.2>
	window.location='tempExcel/" . $nop_ . ".xls.gz';
	</script>";


		break;
	default:
		break;
}
