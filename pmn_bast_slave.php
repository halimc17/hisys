<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/cekakun.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/terbilang.php');
require_once('pmn_spk_nospk_slave.php');

use Dompdf\Dompdf;

$method = checkPostGet('method', '');
$param = array();
$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}
$where = '';
$arrkomoditi = array("40000001" => "CPO", "40000002" => "KER");
$str = "select * from " . $dbname . ".pabrik_5tangki";
$res = fetchdata($str);
foreach ($res as $bar) {
	$namatangki[$bar['kodetangki']] = $bar['keterangan'];
}

$path   = "fileupload/billofloading/";
$urlefil = checkPostGet('urlefil', '0');
$str = "select * from " . $dbname . ".log_5masterbarang where kelompokbarang='400' ";
$res = fetchdata($str);
foreach ($res as $bar) {
	$arrinisial[$bar['kodebarang']] = $bar['inisial'];
	$namabarang[$bar['kodebarang']] = $bar['namabarang'];
}
$str = "select * from " . $dbname . ".pmn_5kapalponton";
$res = fetchdata($str);
foreach ($res as $bar) {
	$namakapalponton[$bar['kode']] = $bar['nama'];
}
$str = "select * from " . $dbname . ".organisasi where tipe='PT'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$namapt[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
}
$str = "select * from " . $dbname . ".pmn_4customer";
$res = fetchdata($str);
foreach ($res as $bar) {
	$namacustomer[$bar['kodecustomer']] = $bar['namacustomer'];
}

$str = "select * from " . $dbname . ".setup_matauang";
$res = fetchdata($str);
foreach ($res as $bar) {
	$dtsimbol[$bar['kode']] = $bar['simbol'];
}

$str = "select * from " . $dbname . ".setup_filesize where transaksi='pmn_bast'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$filesize = $bar['filesize'];
}

switch ($method) {

	case 'geteditht':

		$str = "select * from " . $dbname . ".pmn_bast  where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		echo
		$res[0]['notransaksi'] . "###" .
			$res[0]['kodept'] . "###" .
			$res[0]['kodecustomer'] . "###" .
			$res[0]['kodebarang'] . "###" .
			$res[0]['nokontrak'] . "###" .
			tanggalnormal($res[0]['tanggal']) . "###" .
			$res[0]['kota'];
		break;

	case 'loaddata':
		#= untuk unit ht
		$arrunit = array();
		$arrunit = getOrgDetail(1);
		foreach ($arrunit as $val => $nama) {
			$dtunit[$val] = $val;
		}

		$where = "1=1 ";

		if ($param['notransaksi'] != '') {
			$where .= " and notransaksi like '%" . $param['notransaksi'] . "%'";
		}
		if ($param['nokontrak'] != '') {
			$where .= " and nokontrak like '%" . $param['nokontrak'] . "%'";
		}
		if ($param['tanggal1'] != '' and $param['tanggal2'] != '') {
			$where .= " and tanggal between '" . tanggalsystemn($param['tanggal1']) . "' and  '" . tanggalsystemn($param['tanggal2']) . "'";
		}
		if ($param['tanggalbl1'] != '' and $param['tanggalbl2'] != '') {
			$where .= " and tanggalbl between '" . tanggalsystemn($param['tanggalbl1']) . "' and  '" . tanggalsystemn($param['tanggalbl2']) . "'";
		}
		if ($param['kodebarang'] != '') {
			$where .= " and kodebarang ='" . $param['kodebarang'] . "'";
		}
		if ($param['kodecustomer'] != '') {
			$where .= " and kodecustomer ='" . $param['kodecustomer'] . "'";
		}

		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay = ($page * $limit);
		$colspan = 17;

		$offset = $page * $limit;

		// $str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."  group by notransaksi  ";
		$str = "select count(*) as jumrow from " . $dbname . ".pmn_bast where " . $where . " group by notransaksi";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			@$jumrow += $bar['jumrow'];
		}



		$no = 0;
		$no = $maxdisplay;
		$statusapp = '';
		$str = "select notransaksi,sum(jumlah) as jumlah,nokontrak,tanggal,kodept,kodecustomer,kodebarang,updateby,createby,posting,close,sum(kgpembeli) as kgpembeli from " . $dbname . ".pmn_bast where " . $where . " group by notransaksi order by tanggal desc limit " . $offset . "," . $limit . " ";
		$res = fetchdata($str);
		foreach ($res as $bar) {

			#=datakaryawan
			$strdt = "select namakaryawan,karyawanid from " . $dbname . ".datakaryawan where karyawanid in ('" . $bar['createby'] . "','" . $bar['updateby'] . "') ";
			$resdt = fetchdata($strdt);
			foreach ($resdt as $bardt) {
				$namakaryawan[$bardt['karyawanid']] = $bardt['namakaryawan'];
			}

			$tanggalbl = $temptanggalbl = '';
			$strbl = "select tanggalbl from " . $dbname . ".pmn_bast where notransaksi='" . $bar['notransaksi'] . "'";
			$resbl = fetchdata($strbl);
			foreach ($resbl as $barbl) {
				if ($temptanggalbl != $barbl['tanggalbl']) {
					$tanggalbl .= tanggalnormal($barbl['tanggalbl']) . '<br>';
				}
				$temptanggalbl = $barbl['tanggalbl'];
			}
			$no++;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center valign=top>" . $no . "</td>";
			$tab .= "<td valign=top>" . $bar['notransaksi'] . "</td>";
			$tab .= "<td valign=top>" . $bar['nokontrak'] . "</td>";
			$tab .= "<td valign=top>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td valign=top>" . $tanggalbl . "</td>";
			$tab .= "<td valign=top>" . $bar['kodept'] . "</td>";
			$tab .= "<td valign=top>" . $namacustomer[$bar['kodecustomer']] . "</td>";
			$tab .= "<td valign=top>" . $arrinisial[$bar['kodebarang']] . "</td>";
			$tab .= "<td valign=top align=right>" . number_format($bar['kgpembeli']) . "</td>";
			$tab .= "<td valign=top>" . $namakaryawan[$bar['createby']] . "</td>";
			// $tab.="<td valign=top>".$namakaryawan[$bar['updateby']]."</td>";

			// $tab.="<td valign=top align=center>".$statusapp."</td>";
			// $tab.="<td valign=top>".$namakaryawan[$bar['updateby']]."</td>";
			$tab .= "<td style='text-align:center;vertical-align:middle'><label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('" . $bar['notransaksi'] . "',event)\">History Approval</label></td>";
			if ($bar['posting'] == 0) {
				$tab .= "<td align=center valign=top  style=\"width:20px;\"><img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('" . $bar['notransaksi'] . "');\"></td>";
				$tab .= "<td align=center valign=top  style=\"width:20px;\"><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('" . $bar['notransaksi'] . "');\"></td>";
				$tab .= "<td align=center> <img src='images/skyblue/submit.jpg' class='zImgBtn' title='Ajukan' onclick='form_ajukan(`" . $bar['notransaksi'] . "`)'> </td>";

			} else if ($bar['posting'] == 9) {
				$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
				$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
				$tab .= "<td align=center> <img src='images/icons/04/16/04.png' class='zImgBtn' height='30' title='On Progress Approval'> </td>";
				
			} else if ($bar['posting']==8){
				$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
				$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
				$tab .= "<td align=center valign=top  style=\"width:20px;\"><img src=images/" . $_SESSION['theme'] . "/posting.png class=resicon  title='posting' onclick=\"posting('" . $bar['notransaksi'] . "','" . $page . "');\"></td>";

			}else {
				$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
				$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
				if ($bar['close'] == 0) {
					$tab .= "<td align=center><img src=images/icons/04/16/02.png  class=zImgBtn height='30'  title='Disetujui/Tolak' ></td>";
				} else {
					$tab .= "<td align=center><img src=images/icons/04/16/02.png  class=zImgBtn height='30'  title='Disetujui/Tolak' > Close</td>";
				}
			}
			$tab .= "<td align=center valign=top  style=\"width:20px;\"><img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF Nomor BAST : " . $bar['notransaksi'] . "' onclick=\"pdf('" . $bar['notransaksi'] . "');\"></td>";
			$tab .= "<td align=center valign=top  style=\"width:20px;\"><img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print Rincian Tiket : " . $bar['nokontrak'] . "' onclick=\"pdf2('" . $bar['notransaksi'] . "');\"></td>";
			$tab .= "<td align=center valign=top  style=\"width:20px;\"><img title='" . $_SESSION['lang']['upload'] . "' class=zImgBtn onclick=\"viewlistfile('" . $bar['notransaksi'] . "')\" src='images/upload-2-xxl.png'/></td>";
			$tab .= "</tr>";
		}
		$tab2 = createpaging($jumrow, $limit, $page, $colspan, 'loaddata', 'getpage');
		//$tab.="</table>";
		echo $tab . "####" . $tab2;
		break;
	
	
	case 'form_ajukan':
		$tab = "";

		$optKrylevel = array();
		$jenispersetujuanx = "BAST";
		$lokasitugas = $_SESSION['empl']['lokasitugas'];

		$optper4 = $optper3 = $optper2 = $optper1 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $jenispersetujuanx . "' and kodeunit='" . $lokasitugas . "'  order by level asc";
		$res = fetchData($str);
		foreach ($res as $key => $bar) {
			$whr		= " karyawanid='" . $bar['karyawanid'] . "'";
			$optnama 	= makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whr);

			$optKryx[$bar['level']][$bar['karyawanid']] = "<option value=" . $bar['karyawanid'] . ">" . $optnama[$bar['karyawanid']] . "</option>";
			$optKrylevel[$bar['level']] = $bar['level'];
		}
		$tab .= "<div><b>Nomor : " . $param['notransaksi'] . "</b></div><br>";
		$tab .= "<table cellspacing=1 border=0>
		<tr class=rowcontent hidden > 
			<td id=notran_aju>" . $param['notransaksi'] . "</td>
		</tr>";

		$jumlahlevel = count($optKrylevel);
		if ($jumlahlevel > 0) {
			for ($i = 1; $i <= $jumlahlevel; $i++) {
				$optKry = '';
				foreach ($optKryx[$i] as $key2 => $val) {
					$optKry .= $val;
				}
				$tab .= "<tr class=rowcontent>
						<td>Approval ke-" . $i . "</td>
						<td width=5px>:</td>
						<td><select id=kepada" . $i . " style='width:200px;'>" . $optKry . "</select></td>
					</tr>";
			}
		} else {
			$jumlahlevel = 1;
			$tab .= "<tr class=rowcontent>
					<td>Approval ke-1</td>
					<td width=5px>:</td>
					<td><select id=kepada1 style='width:200px;'></select></td>
				</tr>";
		}
		$tab .= "<tr class=rowcontent>
					<td hidden><input id=jenispersetujuanx style=display:none value=" . $jenispersetujuanx . "></td><td><input id=numrow style=display:none value=" . $jumlahlevel . "></td>
					<td align=left></td>
					</tr>
				<tr>
					<td align=left></td>
					<td align=left></td>
					<td align=center><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>
				</table>";

		echo $tab;
		break;

	case 'ajukan':
		$kepada = checkPostGet('kepada', '');
		$jenispersetujuanx = checkPostGet('jenispersetujuanx', '');
		$notransaksi = checkPostGet('notransaksi', '');
 		if ($kepada == '') {
			throw new PDOException('Isikan nama penyetuju.');
		}

		try {
			// Update status kontrak menjadi 'diajukan'
			$str2 = "update " . $dbname . ".pmn_bast set posting='9' where notransaksi = '" . $notransaksi . "'";
			$owlPDO->exec($str2);

			// Insert ke tabel approval untuk setiap level
			$arrkepada = explode('###', $kepada);
			foreach ($arrkepada as $i => $karyawanid) {
				if (trim($karyawanid) != '') {
					$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
						`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
						values ('','" . $notransaksi . "','" . $jenispersetujuanx . "','" . ($i + 1) . "','" . $karyawanid . "','0','','','')";
					$owlPDO->exec($str);
				}
			}
			echo "OK";
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
		}

		break;



	case 'deleteht':
		if ($param['notransaksi'] == '') {
			exit("Warningsistem:Nomor Transaksi Kosong");
		}
		$str = "delete from " . $dbname . ".pmn_bast where notransaksi='" . $param['notransaksi'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
		}

		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "'"; //exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			// $pathx = $path.$bar['namafile'];
			// unlink($pathx);
		}

		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}

		break;

	case 'deletedt':
		$str = "delete from " . $dbname . ".pmn_bast where notransaksi='" . $param['notransaksi'] . "' and nourut='" . $param['nourut'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'deletedtnotransaksireferensi':
		$str = "delete from " . $dbname . ".pmn_bastdt_notransaksireferensi where notransaksi='" . $param['notransaksi'] . "' and notransaksireferensi='" . $param['notransaksireferensi'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'loaddatadt':
		$str = "select * from " . $dbname . ".pmn_bast  where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=left>" . tanggalnormal($bar['tanggalbl']) . "</td>";
			$tab .= "<td align=left>" . tanggalnormal($bar['tanggalsampai']) . "</td>";
			$tab .= "<td align=right>" . number_format($bar['jumlahrit']) . "</td>";
			$tab .= "<td align=right>" . number_format($bar['jumlah']) . "</td>";
			$tab .= "<td align=right>" . number_format($bar['kgpembeli']) . "</td>";

			$tab .= "<td align=center  valign=top width=20px>";
			$tab .= "<img src=images/application/application_edit.png class=zImgBtn caption='Edit' onclick=\"editdt('" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\"></td>";
			$tab .= "<td align=center  valign=top width=20px>";
			$tab .= "<img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' 
					onclick=\"deletedt('" . $bar['notransaksi'] . "','" . $bar['nourut'] . "');\">";

			$tab .= "</td>";
			$tab .= "</tr>";
		}
		echo $tab;
		break;



	case 'geteditdt':
		$str = "select * from " . $dbname . ".pmn_bast  where notransaksi='" . $param['notransaksi'] . "' and nourut='" . $param['nourut'] . "'";
		$res = fetchdata($str);
		echo  tanggalnormal($res[0]['tanggalbl']) . "###" . $res[0]['kodetangki'] . "###" . hidezerodecimal($res[0]['jumlah']) . "###" . $res[0]['namakapal'] . "###" . $res[0]['namaponton'] . "###" .
			$res[0]['ffa'] . "###" . $res[0]['moisture'] . "###" . $res[0]['impurities'] . "###" . $res[0]['mdani'] . "###" . $res[0]['dirt'] . "###" . $res[0]['dobi'] . "###" .
			$res[0]['broken'] . "###" . $res[0]['nourut'] . "###" . hidezerodecimal($res[0]['rpkgclaimffa']) . "###" . hidezerodecimal($res[0]['rpkgclaimmdani']) . "###" . hidezerodecimal($res[0]['rpkgclaimdirt']) . "###" . hidezerodecimal($res[0]['rpkgclaimmoisture']) . "###" . hidezerodecimal($res[0]['rpkgclaimimpurities']) . "###" . hidezerodecimal($res[0]['rpkgclaimbroken']) . "###" . hidezerodecimal($res[0]['rpkgclaimdobi']) . "###" . hidezerodecimal($res[0]['rpclaimffa']) . "###" . hidezerodecimal($res[0]['rpclaimmdani']) . "###" . hidezerodecimal($res[0]['rpclaimdirt']) . "###" . hidezerodecimal($res[0]['rpclaimmoisture']) . "###" . hidezerodecimal($res[0]['rpclaimimpurities']) . "###" . hidezerodecimal($res[0]['rpclaimbroken']) . "###" . hidezerodecimal($res[0]['rpclaimdobi']) . "###" . hidezerodecimal($res[0]['lain']) . "###" . hidezerodecimal($res[0]['rpkgclaimlain']) . "###" . hidezerodecimal($res[0]['rpclaimlain']) . "###" . tanggalnormal($res[0]['tanggalsampai']). "###" . hidezerodecimal($res[0]['kgpembeli']). "###" . hidezerodecimal($res[0]['jumlahrit']);
 		break;

	case 'getnilaiclaim':

		#= data mutu kontrak
		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $param['nokontrak'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$ffa = $bar['ffa'];
			$dobi = $bar['dobi'];
			$mdani = $bar['mdani'];
			$impurities = $bar['grading'];
			$moisture = $bar['moist'];
			$dirt = $bar['dirt'];
		}


		$param['jumlah'] = str_replace(',', '', $param['jumlah']);

		$param['rpkgclaimffa'] = str_replace(',', '', $param['rpkgclaimffa']);
		$param['rpkgclaimmdani'] = str_replace(',', '', $param['rpkgclaimmdani']);
		$param['rpkgclaimdirt'] = str_replace(',', '', $param['rpkgclaimdirt']);
		$param['rpkgclaimmoisture'] = str_replace(',', '', $param['rpkgclaimmoisture']);
		$param['rpkgclaimimpurities'] = str_replace(',', '', $param['rpkgclaimimpurities']);
		$param['rpkgclaimbroken'] = str_replace(',', '', $param['rpkgclaimbroken']);
		$param['rpkgclaimdobi'] = str_replace(',', '', $param['rpkgclaimdobi']);
		$param['rpkgclaimlain'] = str_replace(',', '', $param['rpkgclaimlain']);

		$param['ffa'] = str_replace(',', '', $param['ffa']);
		$param['mdani'] = str_replace(',', '', $param['mdani']);
		$param['dirt'] = str_replace(',', '', $param['dirt']);
		$param['moisture'] = str_replace(',', '', $param['moisture']);
		$param['impurities'] = str_replace(',', '', $param['impurities']);
		$param['broken'] = str_replace(',', '', $param['broken']);
		$param['dobi'] = str_replace(',', '', $param['dobi']);
		$param['lain'] = str_replace(',', '', $param['lain']);

		#= ffa
		$param['rpclaimffa'] = $param['jumlah'] * $param['rpkgclaimffa'];

		#= mandi
		$param['rpclaimmdani'] = ($param['mdani'] - $mdani) / 100 * $param['jumlah'] * $param['rpkgclaimmdani'];

		#= dirt
		$param['rpclaimdirt'] = $param['jumlah'] * $param['rpkgclaimdirt'];

		#= moisture
		$param['rpclaimmoisture'] = ($param['moisture'] - $moisture) / 100 * $param['jumlah'] * $param['rpkgclaimmoisture'];

		#= impurities
		$param['rpclaimimpurities'] = $param['jumlah'] * $param['rpkgclaimimpurities'];

		#= broken
		$param['rpclaimbroken'] = $param['jumlah'] * $param['rpkgclaimbroken'];

		#= dobi
		$param['rpclaimdobi'] = $param['jumlah'] * $param['rpkgclaimdobi'];

		#= lain
		$param['rpclaimlain'] = $param['lain'] * $param['rpkgclaimlain'];

		echo hidezerodecimal($param['rpclaimffa']) . "###" . hidezerodecimal($param['rpclaimmdani']) . "###" . hidezerodecimal($param['rpclaimdirt']) . "###" . hidezerodecimal($param['rpclaimmoisture']) . "###" . hidezerodecimal($param['rpclaimimpurities']) . "###" . hidezerodecimal($param['rpclaimbroken']) . "###" . hidezerodecimal($param['rpclaimdobi']) . "###" . hidezerodecimal($param['rpclaimlain']);

		break;

	case 'savedt':

		$param['jumlah'] = str_replace(',', '', $param['jumlah']);
		$param['kgpembeli'] = str_replace(',', '', $param['kgpembeli']);

		$param['rpkgclaimffa'] = str_replace(',', '', $param['rpkgclaimffa']);
		$param['rpkgclaimmdani'] = str_replace(',', '', $param['rpkgclaimmdani']);
		$param['rpkgclaimdirt'] = str_replace(',', '', $param['rpkgclaimdirt']);
		$param['rpkgclaimmoisture'] = str_replace(',', '', $param['rpkgclaimmoisture']);
		$param['rpkgclaimimpurities'] = str_replace(',', '', $param['rpkgclaimimpurities']);
		$param['rpkgclaimbroken'] = str_replace(',', '', $param['rpkgclaimbroken']);
		$param['rpkgclaimdobi'] = str_replace(',', '', $param['rpkgclaimdobi']);
		$param['rpkgclaimlain'] = str_replace(',', '', $param['rpkgclaimlain']);

		$param['rpclaimffa'] = str_replace(',', '', $param['rpclaimffa']);
		$param['rpclaimmdani'] = str_replace(',', '', $param['rpclaimmdani']);
		$param['rpclaimdirt'] = str_replace(',', '', $param['rpclaimdirt']);
		$param['rpclaimmoisture'] = str_replace(',', '', $param['rpclaimmoisture']);
		$param['rpclaimimpurities'] = str_replace(',', '', $param['rpclaimimpurities']);
		$param['rpclaimbroken'] = str_replace(',', '', $param['rpclaimbroken']);
		$param['rpclaimdobi'] = str_replace(',', '', $param['rpclaimdobi']);
		$param['rpclaimlain'] = str_replace(',', '', $param['rpclaimlain']);

		#= rpkg dan rpbast , rpkg dari nomor kontrak, dan rpbast rpkg * qty bast

		#= cari nokontrak
		$str = "select * from " . $dbname . ".pmn_kontrakjual  where nokontrak='" . $param['nokontrak'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$param['rpkg'] = $bar['hargasatuan'];
		}

		$param['rpbast'] = $param['rpkg'] * $param['jumlah'];
		// exit('error' . $param['kgpembeli']);
		if ($param['methoddt'] == 'insert') { // insert
			$str = "INSERT INTO " . $dbname . ".`pmn_bast` (`notransaksi`, `nokontrak`, `kodept`, `kodecustomer`,`kodebarang`,`tanggal`, `tanggalbl`,`tanggalsampai`, `jenis`, `jumlah`, `posting`, `close`, `createby`, `createtime`, `ffa`, `moisture`, `dirt`, `dobi`, `broken`, `mdani`, `impurities`, `kota`, `kodetangki`,namakapal,namaponton,`rpkgclaimffa`,`rpkgclaimmdani`,`rpkgclaimdirt`,`rpkgclaimmoisture`,`rpkgclaimimpurities`,`rpkgclaimbroken`,`rpkgclaimdobi`,`rpclaimffa`,`rpclaimmdani`,`rpclaimdirt`,`rpclaimmoisture`,`rpclaimimpurities`,`rpclaimbroken`,`rpclaimdobi`,`rpkg`,`rpbast`,`lain`,`rpkgclaimlain`,`rpclaimlain`,`jumlahrit`,`kgpembeli`,`catatan`) 
			values 
			('" . $param['notransaksi'] . "','" . $param['nokontrak'] . "','" . $param['kodept'] . "','" . $param['kodecustomer'] . "','" . $param['kodebarang'] . "','" . tanggalsystemn($param['tanggal']) . "','" . tanggalsystemn($param['tanggalbl']) . "','" . tanggalsystemn($param['tanggalsmp']) . "','BAST','" . $param['jumlah'] . "','0','0','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $param['ffa'] . "','" . $param['moisture'] . "','" . $param['dirt'] . "','" . $param['dobi'] . "','" . $param['broken'] . "','" . $param['mdani'] . "','" . $param['impurities'] . "','" . $param['kota'] . "','" . $param['kodetangki'] . "','" . $param['namakapal'] . "','" . $param['namaponton'] . "','" . $param['rpkgclaimffa'] . "','" . $param['rpkgclaimmdani'] . "','" . $param['rpkgclaimdirt'] . "','" . $param['rpkgclaimmoisture'] . "','" . $param['rpkgclaimimpurities'] . "','" . $param['rpkgclaimbroken'] . "','" . $param['rpkgclaimdobi'] . "','" . $param['rpclaimffa'] . "','" . $param['rpclaimmdani'] . "','" . $param['rpclaimdirt'] . "','" . $param['rpclaimmoisture'] . "','" . $param['rpclaimimpurities'] . "','" . $param['rpclaimbroken'] . "','" . $param['rpclaimdobi'] . "','" . $param['rpkg'] . "','" . $param['rpbast'] . "','" . $param['lain'] . "','" . $param['rpkgclaimlain'] . "','" . $param['rpclaimlain'] . "'," . $param['jlhrit'] . ",'" . $param['kgpembeli'] . "','" . $param['catatan'] . "')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$str = "update " . $dbname . ".`pmn_bast` set  `tanggalbl`='" . tanggalsystemn($param['tanggalbl']) . "', `jenis`='" . $param['jenis'] . "', `jumlah`='" . $param['jumlah'] . "', `ffa`='" . $param['ffa'] . "',`moisture`='" . $param['moisture'] . "',`dirt`='" . $param['dirt'] . "',`dobi`='" . $param['dobi'] . "',`broken`='" . $param['broken'] . "',`mdani`='" . $param['mdani'] . "',`impurities`='" . $param['impurities'] . "',`kodetangki`='" . $param['kodetangki'] . "',`namakapal`='" . $param['namakapal'] . "',`namaponton`='" . $param['namaponton'] . "',`rpkgclaimffa`='" . $param['rpkgclaimffa'] . "',`rpkgclaimmdani`='" . $param['rpkgclaimmdani'] . "',`rpkgclaimdirt`='" . $param['rpkgclaimdirt'] . "',`rpkgclaimmoisture`='" . $param['rpkgclaimmoisture'] . "',`rpkgclaimimpurities`='" . $param['rpkgclaimimpurities'] . "',`rpkgclaimbroken`='" . $param['rpkgclaimbroken'] . "',`rpkgclaimdobi`='" . $param['rpkgclaimdobi'] . "',`rpclaimffa`='" . $param['rpclaimffa'] . "',`rpclaimmdani`='" . $param['rpclaimmdani'] . "',`rpclaimdirt`='" . $param['rpclaimdirt'] . "',`rpclaimmoisture`='" . $param['rpclaimmoisture'] . "',`rpclaimimpurities`='" . $param['rpclaimimpurities'] . "',`rpclaimbroken`='" . $param['rpclaimbroken'] . "',`rpclaimdobi`='" . $param['rpclaimdobi'] . "',`rpkg`='" . $param['rpkg'] . "',`rpbast`='" . $param['rpbast'] . "',`lain`='" . $param['lain'] . "',`rpkgclaimlain`='" . $param['rpkgclaimlain'] . "',`rpclaimlain`='" . $param['rpclaimlain'] . "',`tanggalsampai`='" . tanggalsystemn($param['tanggalsmp']) . "', jumlahrit='" . $param['jlhrit'] . "', kgpembeli='" . $param['kgpembeli'] . "', catatan='" . $param['catatan'] . "' where notransaksi='" . $param['notransaksi'] . "' and nourut='" . $param['nourut'] . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;
	case 'getkodetangki':
		$no = 0;
		$optkodetangki = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select kodetangki from " . $dbname . ".pmn_bapengiriman where nokontrak='" . $param['nokontrak'] . "' and tanggal='" . tanggalsystemn($param['tanggalbl']) . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$no++;
			if ($param['kodetangki'] == $bar['kodetangki']) {
				$optkodetangki .= "<option selected value='" . $bar['kodetangki'] . "'>" . $bar['kodetangki'] . " " . $namatangki[$bar['kodetangki']] . "</option>";
			} else {
				$optkodetangki .= "<option value='" . $bar['kodetangki'] . "'>" . $bar['kodetangki'] . " " . $namatangki[$bar['kodetangki']] . "</option>";
			}
		}

		#= jika tidak ada data dari BA pengiriman maka ambil daftar semua tangki dari unit yang ada dalam 1 pt tersebut
		if ($no == 0) {
			$str = "select * from " . $dbname . ".pabrik_5tangki where komoditi='" . $arrkomoditi[$param['kodebarang']] . "' and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $param['kodept'] . "')";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($param['kodetangki'] == $bar['kodetangki']) {
					$optkodetangki .= "<option value='" . $bar['kodetangki'] . "' selected>" . $bar['kodetangki'] . " " . $bar['keterangan'] . "</option>";
				} else {
					$optkodetangki .= "<option value='" . $bar['kodetangki'] . "'>" . $bar['kodetangki'] . " " . $bar['keterangan'] . "</option>";
				}
			}
		}


		$str = "select tipepenjualan from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $param['nokontrak'] . "'";
		// exit('error '.$str);
		$res = fetchdata($str);
		$tipepenjualan = $res[0]['tipepenjualan'];

 
		## GET DATA FROM WB
		$jlhitem = 0;
		$strx = "select sum(beratbersih) as jlhitem, sum(kgpembeli) as jlhkgpembeli from " . $dbname . ".pabrik_timbangan where nodo in (select distinct nodo from " . $dbname . ".pmn_suratperintahpengiriman where (nokontrak='" . $param['nokontrak'] . "') and posting=1) and (left(timbang2,10) between '" . tanggalsystemn($param['tanggalbl']) . "' and '" . tanggalsystemn($param['tanggalsmp']) . "')"; 
		$resx = fetchdata($strx);
		$jlhitem = $resx[0]['jlhitem'];
		$jlhkgpembeli = $resx[0]['jlhkgpembeli'];
		// } else {
		## GET DATA FROM WB
		//     $jlhitem = 0;
		//     $strx = "select sum(kgpembeli) as jlhitem from " . $dbname . ".pabrik_timbangan where nodo in (select distinct nodo from " . $dbname . ".pmn_suratperintahpengiriman where (nokontrak='" . $param['nokontrak'] . "') and posting=1) and (left(timbang2,10) between '" . tanggalsystemn($param['tanggalbl']) . "' and '" . tanggalsystemn($param['tanggalsmp']) . "')";
		//     $resx = fetchdata($strx);
		//     $jlhitem = $resx[0]['jlhitem'];
		// }

		$slhtgl = selisitgl($param['tanggalsmp'], $param['tanggalbl']);
		if ($slhtgl < 0) {
			$jlhitem = 0;
		}

		echo $optkodetangki . "###" . $jlhitem . "###" . hidezerodecimal($jlhitem, 0) . "###" . hidezerodecimal($jlhkgpembeli, 0);


		break;

	case 'loaddatadtnotransaksireferensi':
		#= cari nokontrak
		$str = "select * from " . $dbname . ".pmn_bast  where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nokontrak = $bar['nokontrak'];
		}

		$arrnoref[$param['notransaksi']] = $param['notransaksi'];
		$str = "select * from " . $dbname . ".pmn_bastdt_notransaksireferensi  where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrnoref[$bar['notransaksireferensi']] = $bar['notransaksireferensi'];
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=left>" . $bar['notransaksireferensi'] . "</td>";
			$tab .= "<td align=center  valign=top width=20px>";
			$tab .= "<img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deletedtnotransaksireferensi('" . $bar['notransaksi'] . "','" . $bar['notransaksireferensi'] . "');\">";
			$tab .= "</td>";
			$tab .= "</tr>";
		}

		$str = "select distinct(notransaksi) as notransaksi from " . $dbname . ".pmn_bast  where nokontrak='" . $nokontrak . "' and notransaksi not in ('" . implode("','", $arrnoref) . "')";
		$optnotransaksi = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$optnotransaksi .= "<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . "</option>";
		}


		echo $tab . "###" . $optnotransaksi;
		break;

	case 'savedtnotransaksireferensi':
		$str = "INSERT INTO `pmn_bastdt_notransaksireferensi` (`notransaksi`, `notransaksireferensi`) 
			values 
			('" . $param['notransaksi'] . "','" . $param['notransaksireferensi'] . "')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'getnokontrak':
		$tab .= "<fieldset><legend>" . $_SESSION['lang']['form'] . "</legend>";
		$tab .= "<table>";
		$tab .= "<tr>
					<td>" . $_SESSION['lang']['NoKontrak'] . "</td>
					<td>:</td>
					<td><input type=text id=nokontrakfind value='" . date('y') . "' size=50 class=myinputtext style=\"width:150px;\"></td>	
					</tr>";
		$tab .= "<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=findnokontrak()>" . $_SESSION['lang']['find'] . "</button></td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</fieldset>";
		$tab .= "<br>";
		$tab .= " <div class=table-scroll style='height:280px'>";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		$tab .= "<thead><tr class=rowheader>
					<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center>" . $_SESSION['lang']['NoKontrak'] . "</th>
					<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
					<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
					<th align=center>" . $_SESSION['lang']['tipe'] . "</th>
					";
		$tab .= "</tr>";
		$tab .= "</thead>";
		$tab .= "<tbody id=formnokontrak></tbody>";
		$tab .= "</table>";
		$tab .= "</div>";
		echo $tab;
		break;

	case 'findnokontrak':
		// echo $param['noinvoiceap']._.$param['kodeorg'];

		$param['nilaiinvoiceap'] = str_replace(',', '', $param['nilaiinvoiceap']);
		$where .= "  and kodept	 = '" . $param['kodept'] . "' ";
		$where .= "  and koderekanan	 = '" . $param['kodecustomer'] . "' ";
		$where .= "  and kodebarang	 = '" . $param['kodebarang'] . "' ";
		if ($param['nokontrakfind'] != '') {
			$where .= "  and nokontrak like '%" . $param['nokontrakfind'] . "%' ";
		}

		#= pmn_kontrakjual
		$no = 0;
		$str = "select * from " . $dbname . ".pmn_kontrakjual where 1=1 " . $where . " order by tanggalkontrak desc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$no++;
			$tab .= "<tr class=rowcontent style='cursor:pointer' title='pindah data' onclick=\"movenokontrak('" . $bar['nokontrak'] . "')\">";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td>" . $bar['nokontrak'] . "</td>";
			$tab .= "<td>" . tanggalnormal($bar['tanggalkontrak']) . "</td>";
			$tab .= "<td>" . hidezerodecimal($bar['kuantitaskontrak']) . "</td>";
			$tab .= "<td>" . $bar['satuan'] . "</td>";
			$tab .= "<td>" . $bar['tipepenjualan'] . "</td>";
			$tab .= "</tr>";
		}
		echo $tab;

		break;

	case 'getnobast':
		echo $param['notransaksi'];
		if ($param['notransaksi'] == '') {
			$nokontrak = $param['nokontrak'];
			$kodept = $param['kodept'];
			$tanggal = tanggalsystemn($param['tanggal']);
			$table = 'pmn_bast';
			$jenis = 'BAST';
			$arrinisial = makeOption($dbname, 'log_5masterbarang', 'kodebarang,inisial', "kelompokbarang='400'");
			$kodebarang = $param['kodebarang'];

			#generet notransaksi  
			$param['notransaksi'] = generatenobast();
			echo $param['notransaksi'];
		}
		break;

	case 'submitfile':
		$tgl = date("YmdHis");
		$his = date("His");
		$nmTemp = str_replace('-', '', str_replace('/', '', $param['notransaksi']));

		if ($_FILES['file']['size'] > $filesize) {
			exit("Warning : Ukuran File melebihi " . number_format($filezie / 1024) . " KB; ukuran file ini " . number_format($_FILES['file']['size'] / 1024, 2) . " Kb");
		}

		if ($param['fileupload'] != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$filename = $param['kriteriaefil'] . "_" . $nmTemp . "_" . $his . "" . $filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.rar') || ($filetype == '.gz') || ($filetype == '.zip') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
					$str = "insert into " . $dbname . ".listfileupload values ('','" . $param['notransaksi'] . "','" . $filename . "','" . $filetype . "','" . $param['kriteriaefil'] . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
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
					exit("Warning : Format file upload tidak boleh " . $filetype);
				}
			}
		}
		break;


	case 'deletefile':
		$param = $_POST;
		$str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "' and namafile='" . $param['namafile'] . "'";
		// exit('error'.$str);
		try {
			$owlPDO->exec($str);
			// $pathx = $path.str_replace('/','',$param['namafile']);
			// unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'loadfiles':
		$param = $_POST;
		$form = '';
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "' ";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form .= "<tr class=rowcontent >";
			$form .= "<td style='text-align:center'>" . $no . "</td>";
			$form .= "<td align='center'><img src=" . $icon . " class=zImgBtn></a></td>";
			$form .= "<td>" . getcriterianame($bar['kriteriaefil']) . "</td>";
			$form .= "<td><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download>" . $bar['namafile'] . "</td>";
			$form .= "<td><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a>&nbsp<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('" . $bar['notransaksi'] . "','" . $bar['namafile'] . "');\" ></td>";
			$form .= "</tr>";
		}
		echo $form;
		break;

	case 'viewlistfile':
		$form = "
		<table class='sortable' cellspacing='1' border='0' cellpadding=5>
			<thead>
			<tr class=rowheader>
				<th align='center'>" . $_SESSION['lang']['nourut'] . "</th>
				<th align='center'>File Type</th>
				<th align='center'>Kriteria</th>
				<th align='center'>Filename</th>
				<th align='center'>Action</th>
			</tr>
			</thead>
			
		";
		$str = "select * from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form .= "<tr class=rowcontent >";
			$form .= "<td style='text-align:center'>" . $no . "</td>";
			$form .= "<td align='center'><img src=" . $icon . " class=zImgBtn></a></td>";
			$form .= "<td>" . getcriterianame($bar['kriteriaefil']) . "</td>";
			$form .= "<td><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download>" . $bar['namafile'] . "</td>";
			$form .= "<td align=center><a href='" . $path . str_replace('/', '', $bar['namafile']) . "' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a></td>";
			$form .= "</tr>";
		}
		$form .= "</table>
		</fieldset>";
		echo $form;
		break;



	case 'posting':
		$theme = $_SESSION['theme'];
		if ($theme == 'skyblue' || $theme == '') {
			$men = 'menu.css';
			$gen = 'generic.css';
		} else if ($theme == 'red') {
			$men = 'menuRed.css';
			$gen = 'genericRed.css';
		} else {
			$men = 'menuGray.css';
			$gen = 'genericGray.css';
		}

		$status = array('' => $_SESSION['lang']['pilihdata'], '0' => 'Posting', '1' => 'Posting & Close Contract');
		foreach ($status as $sts => $val) {
			@$optstatus .= "<option value='" . $sts . "'>" . $val . "</option>";
		}

		$tab .= "<link rel=stylesheet type=text/css href=style/" . $gen . ">";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>" . $_SESSION['lang']['bast'] . "</td>";
		$tab .= "<td>:</td>";
		$tab .= "<td><input disabled type=text class=myinputtext  style='width:200px;' id=notransaksiposting name=notransaksi value='" . $param['notransaksi'] . "' onkeypress=return tanpa_kutip(event) style=width:145px; maxlength=100 /></td>";
		$tab .= "</tr>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>" . $_SESSION['lang']['tipetransaksi'] . "</td>";
		$tab .= "<td>:</td>";
		$tab .= "<td><select id='tipe' name='tipe' style='width:200px;'>" . $optstatus . "</select></td>";
		$tab .= "</tr>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td colspan=3><button class=mybutton onclick=saveposting('" . $param['page'] . "')>" . $_SESSION['lang']['save'] . "</button></td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		// exit("Error:$tab");
		echo $tab;
		break;

	case 'savepostinglama':
		if ($param['tipe'] == '') {
			exit("Warningsistem:Tipe Posting tidak boleh kosong");
		}
		// exit("Error:A");
		try {
			$owlPDO->beginTransaction();
			switch ($param['tipe']) {
				case '0':
					$str = "update " . $dbname . ".pmn_bast set  posting='1',close='0' where  notransaksi='" . $param['notransaksi'] . "'";
					$owlPDO->exec($str);
					break;
				case '1':
					$str = "update " . $dbname . ".pmn_bast set  posting='1',close='1' where  notransaksi='" . $param['notransaksi'] . "'";
					$owlPDO->exec($str);
					break;
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan posting data \n" . addslashes($e->getMessage());
		}
		break;

	case 'saveposting':
		#= nanti akan jadi jurnal disini

		if ($param['tipe'] == '') {
			exit("Warningsistem:Tipe Posting tidak boleh kosong");
		}


		$str = "select * from " . $dbname . ".pmn_bast where notransaksi='" . $param['notransaksi'] . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nokontrak = $bar['nokontrak'];
			$kodept = $bar['kodept'];
			$kodecustomer = $bar['kodecustomer'];
			$tanggal = $bar['tanggalbl'];
			$tanggalbast = $bar['tanggal'];
			$periodejurnal = substr($bar['tanggalbl'], 0, 7);
			@$kgbast += $bar['jumlah'];
			$closebast = $bar['close'];
			// $rpkgclaim+=$bar['rpkgclaim'];
			$nilaiclaim += $bar['rpclaimffa'] + $bar['rpclaimmoisture'] + $bar['rpclaimdirt'] + $bar['rpclaimdobi'] + $bar['rpclaimbroken'] + $bar['rpclaimmdani'] + $bar['rpclaimimpurities'];
		}

		#= ambil unit RO, karna jurnal sales di ro
		$str = "select * from " . $dbname . ".organisasi where induk='" . $kodept . "' and tipe='KANWIL' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodeunit = $bar['kodeorganisasi'];
		}

		#= cek periode posting
		$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $kodeunit . "' and periode='" . $periodejurnal . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tutupbuku = $bar['tutupbuku'];
		}
		if ($tutupbuku == 1) {
			exit("Warningsistem:Periode " . $periodejurnal . " unit " . $namaorganisasi[$kodeunit] . " Sudah ditutup");
		}

		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kdtermin=$bar['kdtermin'];
		}


		#= ambil data dari kontrakjual
		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodebarang = $bar['kodebarang'];

			$hargasatuan = $bar['hargasatuan'];
			if ($bar['ppn']=='1') {
				$hargasatuan = $bar['hargasatuan'] / 1.11;
			}

			if ($tanggalbast < '2022-04-01') {
				$persenppn = $bar['persenppn']; // sudah mewakili berikat / tidak , jika ppn 0 => berikat; jika 10 maka tidak berikat
			} else {
				if ($bar['persenppn'] >= 10) {
					$persenppn = '11';
				} else {
					$persenppn = $bar['persenppn'];
				}
			}
		}



		#= ambil COA
		$str = "select * from " . $dbname . ".keu_5jenispenagihandt where kodebarang='" . $kodebarang . "' and kodejenis='CIPP'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$noakunsales = $bar['noakunsales'];
			$noakunuangmuka = $bar['noakunuangmuka'];
			$noakunppn = $bar['noakunppn'];
			$noakunpiutang = $bar['noakunpiutang'];
			$noakunclaim = $bar['noakunklaim'];
		}

		$kodejurnal = 'SLE';

		#= bentuk nilai
		#= uang muka / sales = kg * hargasatuan
		#= untuk tipe posting


		// exit("Error:$tipe");



		#= bentuk jurnal disini

		try {
			$owlPDO->beginTransaction();

			#= code disini
			$query = selectQuery(
				$dbname,
				'keu_5kelompokjurnal',
				'nokounter',
				"kodekelompok='" . $kodejurnal . "' and kodeunit='" . $kodeunit . "' and periode='" . $periodejurnal . "'"
			);
			$tmpKonter = fetchData($query);
			$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
			# Prep No Jurnal
			$nojurnal = str_replace('-', '', $tanggal) . "/" . $kodeunit . "/" . $kodejurnal . "/" . $konter;
			$noUrut = 0;

			
			#kondisi jika ada uang muka maka terbentuk jurnal
			if ($kdtermin!=100||$kdtermin!='100') { 
				$dataRes['header'][] = array(
					'nojurnal' => $nojurnal,
					'kodejurnal' => $kodejurnal,
					'tanggal' => $tanggal,
					'tanggalentry' => date('Ymd'),
					'posting' => '0',
					'totaldebet' => '0',
					'totalkredit' => '0',
					'amountkoreksi' => '0',
					'noreferensi' => $param['notransaksi'],
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				);

			} else {

				$param['tipe']='3';
			}

			switch ($param['tipe']) {

				case '0':
					$str = "update " . $dbname . ".pmn_bast set  posting='1',close='0' where  notransaksi='" . $param['notransaksi'] . "'";
					$owlPDO->exec($str);

					$nilaiuangmuka = $nilaisales = $kgbast * $hargasatuan;

					 
	
						#= debet uang muka
						$noUrut++;
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $noakunuangmuka,
							'keterangan' => 'Berita Acara Serah Terima ' . $param['notransaksi'] . ', No. Kontrak : ' . $nokontrak,
							'jumlah' => $nilaiuangmuka,
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $kodeunit,
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => $kodecustomer,
							'kodesupplier' => '',
							'noreferensi' => $param['notransaksi'],
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => $nokontrak,
							'kodeblok' => '',
							'revisi' => '0',
							'kodesegment' => ''
						);

						#= kredit sales
						#= debet uang muka
						$noUrut++;
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $noakunsales,
							'keterangan' => 'Berita Acara Serah Terima ' . $param['notransaksi'] . ', No. Kontrak : ' . $nokontrak,
							'jumlah' => $nilaisales * -1,
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $kodeunit,
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => $kodecustomer,
							'kodesupplier' => '',
							'noreferensi' => $param['notransaksi'],
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => $nokontrak,
							'kodeblok' => '',
							'revisi' => '0',
							'kodesegment' => ''
						);


						if ($nilaiclaim > 0) {
							$noUrut++;
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $tanggal,
								'nourut' => $noUrut,
								'noakun' => $noakunclaim,
								'keterangan' => 'Nilai Claim Berita Acara Serah Terima ' . $param['notransaksi'] . ', No. Kontrak : ' . $nokontrak,
								'jumlah' => $nilaiclaim,
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $kodeunit,
								'kodekegiatan' => '',
								'kodeasset' => '',
								'kodebarang' => $kodebarang,
								'nik' => '',
								'kodecustomer' => $kodecustomer,
								'kodesupplier' => '',
								'noreferensi' => $param['notransaksi'],
								'noaruskas' => '',
								'kodevhc' => '',
								'nodok' => $nokontrak,
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => ''
							);

							$noUrut++;
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $tanggal,
								'nourut' => $noUrut,
								'noakun' => $noakunuangmuka,
								'keterangan' => 'Berita Acara Serah Terima ' . $param['notransaksi'] . ', No. Kontrak : ' . $nokontrak,
								'jumlah' => $nilaiclaim * -1,
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $kodeunit,
								'kodekegiatan' => '',
								'kodeasset' => '',
								'kodebarang' => $kodebarang,
								'nik' => '',
								'kodecustomer' => $kodecustomer,
								'kodesupplier' => '',
								'noreferensi' => $param['notransaksi'],
								'noaruskas' => '',
								'kodevhc' => '',
								'nodok' => $nokontrak,
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => ''
							);
						}
					
					break;

				case '1':

					$str = "update " . $dbname . ".pmn_bast set  posting='1',close='1' where  notransaksi='" . $param['notransaksi'] . "'";
					$owlPDO->exec($str);



					#= cari nilai penjualan
					$nilaisales = $kgbast * $hargasatuan;
					// exit('error '.$kgbast.' x '.$hargasatuan);
					// $nilaiclaim=$kgbast*$rpkgclaim;

					#= cari sisa uang muka, dengan cara sum debet-kredit  jurnal where nodok=param nomor kontrak
					$str = "select  sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw where nodok='" . $nokontrak . "' and noakun='" . $noakunuangmuka . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$nilaiuangmuka = abs($bar['jumlah']); //karna nilai minus
					}

					$nilaisisa = $nilaisales - $nilaiuangmuka - $nilaiclaim;
					$nilaippn = 0;
					if ($persenppn > 0) {
						$nilaippn = floor($nilaisisa * 0.11);
					}
					$nilaipiutang = $nilaisisa + $nilaippn;
					// exit("Error:".$nilaipiutang._.$nilaippn._.$nilaisales._.$nilaiuangmuka);

					#= debet piutang
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $noakunpiutang,
						'keterangan' => 'Berita Acara Serah Terima ' . $param['notransaksi'] . ', No. Kontrak : ' . $nokontrak,
						'jumlah' => $nilaipiutang,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $kodeunit,
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => $kodebarang,
						'nik' => '',
						'kodecustomer' => $kodecustomer,
						'kodesupplier' => '',
						'noreferensi' => $param['notransaksi'],
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => $nokontrak,
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => ''
					);

					#= debet uangmuka
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $noakunuangmuka,
						'keterangan' => 'Berita Acara Serah Terima ' . $param['notransaksi'] . ', No. Kontrak : ' . $nokontrak,
						'jumlah' => $nilaiuangmuka,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $kodeunit,
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => $kodebarang,
						'nik' => '',
						'kodecustomer' => $kodecustomer,
						'kodesupplier' => '',
						'noreferensi' => $param['notransaksi'],
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => $nokontrak,
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => ''
					);

					#= jika ada claim
					#= posisi debet
					if ($nilaiclaim > 0) {
						$noUrut++;
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $noakunclaim,
							'keterangan' => 'Berita Acara Serah Terima ' . $param['notransaksi'] . ', No. Kontrak : ' . $nokontrak,
							'jumlah' => $nilaiclaim,
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $kodeunit,
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => $kodecustomer,
							'kodesupplier' => '',
							'noreferensi' => $param['notransaksi'],
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => $nokontrak,
							'kodeblok' => '',
							'revisi' => '0',
							'kodesegment' => ''
						);
					}

					#= kredit sales
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $noakunsales,
						'keterangan' => 'Berita Acara Serah Terima ' . $param['notransaksi'] . ', No. Kontrak : ' . $nokontrak,
						'jumlah' => $nilaisales * -1,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $kodeunit,
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => $kodebarang,
						'nik' => '',
						'kodecustomer' => $kodecustomer,
						'kodesupplier' => '',
						'noreferensi' => $param['notransaksi'],
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => $nokontrak,
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => ''
					);

					#= kredit ppn keluaran
					if ($nilaippn > 0) {
						$noUrut++;
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $noakunppn,
							'keterangan' => 'PPN Keluaran Berita Acara Serah Terima ' . $param['notransaksi'] . ', No. Kontrak : ' . $nokontrak,
							'jumlah' => $nilaippn * -1,
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $kodeunit,
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => $kodecustomer,
							'kodesupplier' => '',
							'noreferensi' => $param['notransaksi'],
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => $nokontrak,
							'kodeblok' => '',
							'revisi' => '0',
							'kodesegment' => ''
						);
					}




				break;

				case '3':
				
					continue;
				break;
			}
			
			#= update counter jurnal
			$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
				kodeunit='" . $kodeunit . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $periodejurnal . "' ";
			$owlPDO->exec($str);

			
			if ($kdtermin!=100||$kdtermin!='100') {

				$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
				$owlPDO->exec($queryH);

				$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
				$owlPDO->exec($queryD);
			}


			$msgdt = "Pemberitahuan bahwa Berita Acara Serah Terima (BAST) penjualan sudah dibuat dengan nomor " . $param['notransaksi'] . " dengan nomor kontrak : " . $nokontrak . ", invoice AR sudah dapat dibuat.";
			$str = "select * from " . $dbname . ".setup_notification_dt where kodejenis='PMNBAST'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				createnotif($param['notransaksi'], 'PMNBAST', $msgdt, $bar['karyawanid'], date('Y-m-d H:i:s'));
			}

			$owlPDO->commit();
		} catch (PDOException $e) {

			$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan posting data \n" . addslashes($e->getMessage());
		}


		break;

	case 'pdf':
		$tab = "<style>
			@page {
				margin-top: 30px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			body {
				font-family: Serif, Times-Roman;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>";



		$str = "select * from " . $dbname . ".pmn_bastdt_notransaksireferensi where notransaksi='" . $param['notransaksi'] . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrnotransaksi[$bar['notransaksireferensi']] = $bar['notransaksireferensi'];
			$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
		}
		$arrnotransaksi[$param['notransaksi']] = $param['notransaksi'];
		$jumlahbast = 0;

		$str = "select * from " . $dbname . ".pmn_bast where notransaksi in ('" . implode("','", $arrnotransaksi) . "') and tanggalsampai='0000-00-00'";
		$res = fetchdata($str);
		if (!empty($res)) {
			$orderby = "order by notransaksi asc";
		} else {
			$orderby = "order by tanggalsampai asc,notransaksi asc";
		}

		$str = "select * from " . $dbname . ".pmn_bast where notransaksi in ('" . implode("','", $arrnotransaksi) . "') " . $orderby;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jumlahbast++;
			$nokontrak = $bar['nokontrak'];
			$kodept = $bar['kodept'];
			$kodecustomer = $bar['kodecustomer'];
			$tanggal = $bar['tanggal']; //tanggal BL
			$tanggalbast = $bar['tanggalbast'];
			$periodejurnal = substr($bar['tanggal'], 0, 7);
			$jumlah = $bar['jumlah'];
			$kgpembeli = $bar['kgpembeli'];
			$closebast = $bar['close'];
			$tanggalbl = $bar['tanggalbl'];
			$tanggalsampai = $bar['tanggalsampai'];
			$jumlahrit = $bar['jumlahrit'];
			$cttbast = $bar['catatan'];


		 
			$kotabast = $bar['kota'];
			// $rpkgclaim=$bar['rpkgclaim'];

			$nilaiclaim += $bar['rpclaimffa'] + $bar['rpclaimmoisture'] + $bar['rpclaimdirt'] + $bar['rpclaimdobi'] + $bar['rpclaimbroken'] + $bar['rpclaimmdani'] + $bar['rpclaimimpurities'];

			$rpclaimffa += $bar['rpclaimffa'];
			$rpclaimmoisture += $bar['rpclaimmoisture'];
			$rpclaimdirt += $bar['rpclaimdirt'];
			$rpclaimdobi += $bar['rpclaimdobi'];
			$rpclaimbroken += $bar['rpclaimbroken'];
			$rpclaimmdani += $bar['rpclaimmdani'];
			$rpclaimimpurities += $bar['rpclaimimpurities'];

			$rpkgclaimffa += $bar['rpkgclaimffa'];
			$rpkgclaimmoisture += $bar['rpkgclaimmoisture'];
			$rpkgclaimdirt += $bar['rpkgclaimdirt'];
			$rpkgclaimdobi += $bar['rpkgclaimdobi'];
			$rpkgclaimbroken += $bar['rpkgclaimbroken'];
			$rpkgclaimmdani += $bar['rpkgclaimmdani'];
			$rpkgclaimimpurities += $bar['rpkgclaimimpurities'];

			$kodebarang = $bar['kodebarang'];

			#= jika gabungan
			if ($bar['ffa'] != 0) {
				$ffabast = $bar['ffa'];
			}
			if ($bar['moisture'] != 0) {
				$moistbast = $bar['moisture'];
			}
			if ($bar['mdani'] != 0) {
				$mdanibast = $bar['mdani'];
			}
			if ($bar['dirt'] != 0) {
				$dirtbast = $bar['dirt'];
			}
			if ($bar['broken'] != 0) {
				$brokenbast = $bar['broken'];
			}
			if ($bar['impurities'] != 0) {
				$impuritiesbast = $bar['impurities'];
			}
			if ($bar['dobi'] != 0) {
				$dobibast = $bar['dobi'];
			}
			$arrnamakapalponton[$bar['namakapal']] = $bar['namakapal'];
			$arrnamakapalponton[$bar['namaponton']] = $bar['namaponton'];

			if ($bar['namakapal'] != '') {
				$arrdtketeranganjudul[$bar['tanggalbl']][$bar['namakapal']] = $namakapalponton[$bar['namakapal']] . " Tanggal " . tglnmbln($bar['tanggalbl'], 'i', 'l') . ", ";
			} else {
				$arrdtketeranganjudul[$bar['tanggalbl']][$bar['namaponton']] = $namakapalponton[$bar['namaponton']] . " Tanggal " . tglnmbln($bar['tanggalbl'], 'i', 'l') . ", ";
			}


			#= arr tanggal bl diganti jadi tanggal data, dikarenakan adanya 2 bast berbeda, disatukan, nomor kontrak sama, tanggal bl sama
			#= jadi array tanggal bl diganti tanggal data
			$arrkodetangki[$bar['kodetangki']] = $bar['kodetangki'];
			$arrtanggalbl[$bar['nourut']] = $bar['nourut'];
			$arrtanggalblori[$bar['tanggalbl']] = $bar['tanggalbl'];

			$dtjumlah[$bar['nourut']][$bar['kodetangki']] += $bar['jumlah'];
			$listdata[$bar['nourut']][$bar['kodetangki']] = $bar['kodetangki'];
			$arrmutu[$bar['nourut']][$bar['kodetangki']] = $bar['kodetangki'];
			$dtnamakapal[$bar['nourut']][$bar['kodetangki']] = $bar['namakapal'];
			$dtnamakapaltanggal[$bar['nourut']][$bar['kodetangki']] = $bar['tanggalbl'];
			$dtnamaponton[$bar['nourut']][$bar['kodetangki']] = $bar['namaponton'];
			if ($bar['nourut'] == '0000-00-00') {
				$sortdata = SORT_DESC;
			} else {
				$sortdata = SORT_ASC;
			}

			$nomutubast = 1;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['ffa'];
			$dttextmutu[$nomutubast] = 'Free Fatty Acid (FFA)';
			//$arrmutubast[$nomutubast]=$nomutubast;


			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['moisture'];
			$dttextmutu[$nomutubast] = 'Moisture Content';

			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['mdani'];
			$dttextmutu[$nomutubast] = 'Moisture & Impurities (M&I)';
			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['dirt'];
			//$arrmutubast[$nomutubast]=$nomutubast;
			$dttextmutu[$nomutubast] = 'Dirt Content';

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['broken'];
			$dttextmutu[$nomutubast] = 'Broken Content';
			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['impurities'];
			$dttextmutu[$nomutubast] = 'Impurities Content';
			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['dobi'];
			$dttextmutu[$nomutubast] = 'Dobi Content';
			//$arrmutubast[$nomutubast]=$nomutubast;
		}

		$dttextglobalkapalponton = '';

		#ada customer yang beberapa kata tidak ditampilkan
		$excludekodecustomer = array('SAL' => 'SAL');
 
		foreach ($arrtanggalblori as $dttanggalbl) {
			foreach ($arrnamakapalponton as $dtkapalponton) {
				if ($arrdtketeranganjudul[$dttanggalbl][$dtkapalponton] != '') {
					$dtketeranganjudul .= $namakapalponton[$dtkapalponton] . " Tanggal " . tglnmbln($dttanggalbl, 'i', 'l') . ", ";
				}
			}
		} 


		#= jika global
		$nobast = 0;
		if ($ffabast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($ffabast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Free Fatty Acid (FFA)';
		}
		if ($moistbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($moistbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Moisture Content';
		}
		if ($mdanibast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($mdanibast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Moisture & Impurities (M&I)';
		}
		if ($brokenbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($brokenbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Broken Content';
		}
		if ($dobibast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($dobibast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Dobi Content';
		}


		if ($dirtbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($dirtbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Dirt Content';
		}
		if ($impuritiesbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($impuritiesbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Impurities Content';
		}
		#= tutup jika global



		#= ambil data dari kontrakjual
		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			//indra
			$hargasatuan = $bar['hargasatuan'];
			$ffakontrak = $bar['ffa'];
			$dobikontrak = $bar['dobi'];
			$mdanikontrak = $bar['mdani'];
			$moistkontrak = $bar['moist'];
			$dirtkontrak = $bar['dirt'];
			$impuritieskontrak = $bar['grading'];
			$penandatangan = $bar['penandatangan'];
			$satuanbarang = $bar['satuan'];
			$matauang = $dtsimbol[$bar['matauang']];
			$persenppn = $bar['persenppn'];
			$kuantitaskontrak = $bar['kuantitaskontrak'];

		}


		$str = "select * from " . $dbname . ".pmn_suratperintahpengiriman where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
		
			$nodo = $bar['nodo'];
			$transportir = $bar['transportir'];

		}

		 

		#= jabatan ttd
		$str = "select * from " . $dbname . ".pmn_5ttd where nama='" . $penandatangan . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jabatanpenandatangan = $bar['jabatan'];
		}



		$no = 0;
		if ($ffakontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($ffakontrak, 3) . ' %';
			$textkualitas[$no] = 'Free Fatty Acid (FFA)';
		}
		if ($dobikontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($dobikontrak, 3) . ' %';
			$textkualitas[$no] = 'Dobi Content';
		}
		if ($mdanikontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($mdanikontrak, 3) . ' %';
			$textkualitas[$no] = 'Moisture & Impurities (M&I)';
		}
		if ($moistkontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($moistkontrak, 3) . ' %';
			$textkualitas[$no] = 'Moisture Content';
		}
		if ($dirtkontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($dirtkontrak, 3) . ' %';
			$textkualitas[$no] = 'Dirt Content';
		}
		if ($impuritieskontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($impuritieskontrak, 3) . ' %';
			$textkualitas[$no] = 'Impurities Content';
		}


		$str = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $kodecustomer . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$namacustomer = $bar['namacustomer'];
			$penandatangancustomer = $bar['penandatangan'];
			$jabatancustomer = $bar['jabatan'];
		}

		$datattdcustomer = explode('/', $penandatangancustomer);
		if ($datattdcustomer[1] != '') {
			$penandatangancustomer = ucwords(strtolower($datattdcustomer[0])) . '/' . ucwords(strtolower($datattdcustomer[1]));
		} else {
			$penandatangancustomer = ucwords(strtolower($datattdcustomer[0]));
		}



		$arrkodept = setheadreport('', $kodept);
		$path = "images/logo/" . $kodept . ".jpg";
		$cellpadding = 1;
		$cellspacing = 1;
		$sizefont = '14px';
		// print_r($arrkodept);exit();

		$tab .= "<div style='page-break-after: always;'>";
		$tab  = "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>";
		$tab .= "<tr>";
		// Logo
		$tab .= "<td style='width:80px;' align='center'>
						<img src=" . $path . " style='width:" . $arrkodept['logowidth'] . ";height:" . $arrkodept['logoheight'] . "'>
					</td>";
		// Nama perusahaan dan alamat
		$tab .= "<td style='text-align:left;'>
						<div style='font-weight:bold; color:green; font-size:" . ($sizefont + 6) . "px;'>
							PERUSAHAAN PERKEBUNAN & PABRIK MINYAK KELAPA SAWIT
						</div>
						<div style='font-weight:bold; color:green; font-size:" . ($sizefont + 4) . "px;'>
							PKS PT. CANDI ARTHA
						</div>
						<div style='font-size:" . ($sizefont - 1) . "px;'>
							Desa Taju Pecah Kec. Batu Ampar Kab. Tanah Laut, Kotak Pos 106 Pelaihari 
							e-mail : <span style='color:blue;'>candiarthaplh@gmail.com</span><br>
							<b>Kalimantan Selatan </b><br>
							Kantor Pusat : Jl. Rungkut YKP Blok PS IH 14 No. 6 Telp. (031) 8713 546 Kode Pos 60297
						</div>
					</td>";
		// Spacer
		$tab .= "<td style='width:50px;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		// garis tipis + garis ganda
		$tab .= "<div style='border-bottom:1px solid #000; margin-top:3px;'></div>";
		$tab .= "<div style='border-bottom:3px double #000; margin-bottom:10px;'></div>";

		$tab .= "<br>";

		$tab .= "<div style='text-align:center; font-weight:bold; font-size:" . $sizefont . "px;'><u> BERITA ACARA SERAH TERIMA " . $arrinisial[$kodebarang] . " </u> </div>";

		$tab .= "<br>";
		$tab .= "<div style='text-align:center; font-weight:bold; font-size:" . $sizefont . "px; margin-bottom:15px;'>
					NO. " . $param['notransaksi'] . "
				</div>";

		$lebihkg=$kgpembeli-$jumlah;

		$tab .= "<div style='text-align:justify; font-size:" . $sizefont . "px;'>
					Pada hari ini, " . tglnmblnhr($tanggal, 'I', 'long') . " (" . tanggalnormal($tanggal) . ") 
					telah selesai diangkut " . $arrinisial[$kodebarang] . " milik " . getNamaOrg($kodept) . " pada tanggal " . tanggalnormal($tanggalbl) . " s/d " . tanggalnormal($tanggalsampai) . "
					dengan rincian sebagai berikut :
				</div>";
		$tab .= "<br><table width=100% style='font-size:" . $sizefont . "px;' cellpadding=2 cellspacing=0 border=0>
            <tr><td width=200>1. Nama Barang / Commodity</td><td>: " . $arrinisial[$kodebarang] . "</td></tr>
            <tr><td>2. No Kontrak</td><td>: {$nokontrak}</td></tr>
            <tr><td>3. No. DO</td><td>: {$nodo}</td></tr>
            <tr><td>4. Nilai Kontrak</td><td>: ".number_format($kuantitaskontrak, 0, ',', '.')." Kg</td></tr>
            <tr><td>5.Total Dikirim</td><td><b>: ".number_format($jumlah, 0, ',', '.')." Kg</b></td></tr>
            <tr><td>6.Total Diterima</td><td><b>: ".number_format($kgpembeli, 0, ',', '.')." Kg</b></td></tr>
            <tr><td>7. Selisih Timbang</td><td>: ".number_format($kgpembeli, 0, ',', '.')." + ".number_format($lebihkg, 0, ',', '.')." (%)</td></tr>
            <tr><td>8. Jumlah Unit Pengangkut</td><td>: ".$jumlahrit." Rit</td></tr>
            <tr><td>9. Pemilik Barang</td><td>: " . getNamaOrg($kodept) . "</td></tr>
            <tr><td>10. Pembeli</td><td>: ".getNamaCustomer($kodecustomer)."</td></tr>
            <tr><td>11. Transportir</td><td>: ".getNamaSupplier($transportir)."</td></tr>
            <tr><td>12. Tujuan</td><td>: ".getNamaCustomer($kodecustomer)."</td></tr>
        </table>";

		$tab .= "<br><div style='font-size:" . $sizefont . "px; font-style:italic;'>
					Catatan : Kontrak di anggap telah selesai.
				</div>";

		$tab .= "<br><div style='font-size:" . $sizefont . "px; text-align:justify;'>
					Demikian Berita Acara Serah terima ini di buat, agar dipergunakan sebagaimana mestinya.
				</div>";

		// Bagian tanda tangan
		$tab .= "<br><br><table width=100% style='font-size:" . $sizefont . "px;' border=0>
            <tr>
                <td>".$cttbast."</td> 

            </tr>
        </table>";
		// $tab .= "<br><br><table width=100% style='font-size:" . $sizefont . "px;' border=1>
        //     <tr>
        //         <td style='width:60%;'>&nbsp;</td>
        //         <td style='text-align:left;'>
        //             Dibuat di : Taju Pecah, PKS PT CA<br>
        //             Tanggal &nbsp; : " . tglnmblnhr($tanggal, 'I', 'long') . " <br><br><br><br>
        //             <div style='text-align:center;'>
        //                 Disetujui<br><br><br><br>
        //                 <u>Rinarti Adiati</u><br>
        //                 Direktur Utama
        //             </div>
        //         </td>
        //     </tr>
        // </table>";

		$tab .= "</div>";


		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');

		$dompdf->render();
		if ($urlefil == '0') {
			$dompdf->stream("Print_BAST_" . $nobast, array("Attachment" => 0));
		} else {
			file_put_contents($urlefil, $dompdf->output());
		}
		break;
	case 'pdf2':
		$tab = "
			<style>
			@page {
				margin-top: 10px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			@page {
				font-family: Arial, sans-serif, Tahoma;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>
		<title>Rekap Pengiriman CPO</title>";


		$str = "select * from " . $dbname . ".pmn_bastdt_notransaksireferensi where notransaksi='" . $param['notransaksi'] . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrnotransaksi[$bar['notransaksireferensi']] = $bar['notransaksireferensi'];
			$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
		}
		$arrnotransaksi[$param['notransaksi']] = $param['notransaksi'];
		$jumlahbast = 0;

		$str = "select * from " . $dbname . ".pmn_bast where notransaksi in ('" . implode("','", $arrnotransaksi) . "') and tanggalsampai='0000-00-00'";
		$res = fetchdata($str);
		if (!empty($res)) {
			$orderby = "order by notransaksi asc";
		} else {
			$orderby = "order by tanggalsampai asc,notransaksi asc";
		}

		$str = "select * from " . $dbname . ".pmn_bast where notransaksi in ('" . implode("','", $arrnotransaksi) . "') " . $orderby;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jumlahbast++;
			$nokontrak = $bar['nokontrak'];
			$kodept = $bar['kodept'];
			$kodecustomer = $bar['kodecustomer'];
			$tanggal = $bar['tanggal']; //tanggal BL
			$tanggalbast = $bar['tanggalbast'];
			$periodejurnal = substr($bar['tanggal'], 0, 7);
			$jumlah += $bar['jumlah'];
			$closebast = $bar['close'];
			$tanggalbl = $bar['tanggalbl'];
			$tanggalsampai = $bar['tanggalsampai'];
			$jumlahrit = $bar['jumlahrit'];


		 
			$kotabast = $bar['kota'];
			// $rpkgclaim=$bar['rpkgclaim'];

			$nilaiclaim += $bar['rpclaimffa'] + $bar['rpclaimmoisture'] + $bar['rpclaimdirt'] + $bar['rpclaimdobi'] + $bar['rpclaimbroken'] + $bar['rpclaimmdani'] + $bar['rpclaimimpurities'];

			$rpclaimffa += $bar['rpclaimffa'];
			$rpclaimmoisture += $bar['rpclaimmoisture'];
			$rpclaimdirt += $bar['rpclaimdirt'];
			$rpclaimdobi += $bar['rpclaimdobi'];
			$rpclaimbroken += $bar['rpclaimbroken'];
			$rpclaimmdani += $bar['rpclaimmdani'];
			$rpclaimimpurities += $bar['rpclaimimpurities'];

			$rpkgclaimffa += $bar['rpkgclaimffa'];
			$rpkgclaimmoisture += $bar['rpkgclaimmoisture'];
			$rpkgclaimdirt += $bar['rpkgclaimdirt'];
			$rpkgclaimdobi += $bar['rpkgclaimdobi'];
			$rpkgclaimbroken += $bar['rpkgclaimbroken'];
			$rpkgclaimmdani += $bar['rpkgclaimmdani'];
			$rpkgclaimimpurities += $bar['rpkgclaimimpurities'];

			$kodebarang = $bar['kodebarang'];

			#= jika gabungan
			if ($bar['ffa'] != 0) {
				$ffabast = $bar['ffa'];
			}
			if ($bar['moisture'] != 0) {
				$moistbast = $bar['moisture'];
			}
			if ($bar['mdani'] != 0) {
				$mdanibast = $bar['mdani'];
			}
			if ($bar['dirt'] != 0) {
				$dirtbast = $bar['dirt'];
			}
			if ($bar['broken'] != 0) {
				$brokenbast = $bar['broken'];
			}
			if ($bar['impurities'] != 0) {
				$impuritiesbast = $bar['impurities'];
			}
			if ($bar['dobi'] != 0) {
				$dobibast = $bar['dobi'];
			}
			$arrnamakapalponton[$bar['namakapal']] = $bar['namakapal'];
			$arrnamakapalponton[$bar['namaponton']] = $bar['namaponton'];

			if ($bar['namakapal'] != '') {
				$arrdtketeranganjudul[$bar['tanggalbl']][$bar['namakapal']] = $namakapalponton[$bar['namakapal']] . " Tanggal " . tglnmbln($bar['tanggalbl'], 'i', 'l') . ", ";
			} else {
				$arrdtketeranganjudul[$bar['tanggalbl']][$bar['namaponton']] = $namakapalponton[$bar['namaponton']] . " Tanggal " . tglnmbln($bar['tanggalbl'], 'i', 'l') . ", ";
			}


			#= arr tanggal bl diganti jadi tanggal data, dikarenakan adanya 2 bast berbeda, disatukan, nomor kontrak sama, tanggal bl sama
			#= jadi array tanggal bl diganti tanggal data
			$arrkodetangki[$bar['kodetangki']] = $bar['kodetangki'];
			$arrtanggalbl[$bar['nourut']] = $bar['nourut'];
			$arrtanggalblori[$bar['tanggalbl']] = $bar['tanggalbl'];

			$dtjumlah[$bar['nourut']][$bar['kodetangki']] += $bar['jumlah'];
			$listdata[$bar['nourut']][$bar['kodetangki']] = $bar['kodetangki'];
			$arrmutu[$bar['nourut']][$bar['kodetangki']] = $bar['kodetangki'];
			$dtnamakapal[$bar['nourut']][$bar['kodetangki']] = $bar['namakapal'];
			$dtnamakapaltanggal[$bar['nourut']][$bar['kodetangki']] = $bar['tanggalbl'];
			$dtnamaponton[$bar['nourut']][$bar['kodetangki']] = $bar['namaponton'];
			if ($bar['nourut'] == '0000-00-00') {
				$sortdata = SORT_DESC;
			} else {
				$sortdata = SORT_ASC;
			}

			$nomutubast = 1;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['ffa'];
			$dttextmutu[$nomutubast] = 'Free Fatty Acid (FFA)';
			//$arrmutubast[$nomutubast]=$nomutubast;


			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['moisture'];
			$dttextmutu[$nomutubast] = 'Moisture Content';

			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['mdani'];
			$dttextmutu[$nomutubast] = 'Moisture & Impurities (M&I)';
			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['dirt'];
			//$arrmutubast[$nomutubast]=$nomutubast;
			$dttextmutu[$nomutubast] = 'Dirt Content';

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['broken'];
			$dttextmutu[$nomutubast] = 'Broken Content';
			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['impurities'];
			$dttextmutu[$nomutubast] = 'Impurities Content';
			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['dobi'];
			$dttextmutu[$nomutubast] = 'Dobi Content';
			//$arrmutubast[$nomutubast]=$nomutubast;
		}

		$dttextglobalkapalponton = '';

		#ada customer yang beberapa kata tidak ditampilkan
		$excludekodecustomer = array('SAL' => 'SAL');

		//array_multisort($arrtanggalbl,$sortdata);
		// array_multisort($arrnamakapalponton,SORT_ASC);
		foreach ($arrtanggalblori as $dttanggalbl) {
			foreach ($arrnamakapalponton as $dtkapalponton) {
				if ($arrdtketeranganjudul[$dttanggalbl][$dtkapalponton] != '') {
					$dtketeranganjudul .= $namakapalponton[$dtkapalponton] . " Tanggal " . tglnmbln($dttanggalbl, 'i', 'l') . ", ";
				}
			}
		}

		#= jika global
		$nobast = 0;
		if ($ffabast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($ffabast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Free Fatty Acid (FFA)';
		}
		if ($moistbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($moistbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Moisture Content';
		}
		if ($mdanibast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($mdanibast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Moisture & Impurities (M&I)';
		}
		if ($brokenbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($brokenbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Broken Content';
		}
		if ($dobibast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($dobibast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Dobi Content';
		}

		if ($dirtbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($dirtbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Dirt Content';
		}
		if ($impuritiesbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($impuritiesbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Impurities Content';
		}
		#= tutup jika global

		#= ambil data dari kontrakjual
		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			//indra
			$hargasatuan = $bar['hargasatuan'];
			$ffakontrak = $bar['ffa'];
			$dobikontrak = $bar['dobi'];
			$mdanikontrak = $bar['mdani'];
			$moistkontrak = $bar['moist'];
			$dirtkontrak = $bar['dirt'];
			$impuritieskontrak = $bar['grading'];
			$penandatangan = $bar['penandatangan'];
			$satuanbarang = $bar['satuan'];
			$matauang = $dtsimbol[$bar['matauang']];
			$persenppn = $bar['persenppn'];
			$kuantitaskontrak = $bar['kuantitaskontrak'];

		}

		$str = "select * from " . $dbname . ".pmn_suratperintahpengiriman where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
	
			$nodo = $bar['nodo'];
			$transportir = $bar['transportir'];
			$prdkirim = $bar['waktupenyerahan'];
		}

		#= jabatan ttd
		$str = "select * from " . $dbname . ".pmn_5ttd where nama='" . $penandatangan . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jabatanpenandatangan = $bar['jabatan'];
		}

		#= Data Timbangan
		$str = "select substring(tanggal,1,10) as tanggal,supir,nokendaraan,notransaksi,jammasuk,jamkeluar,beratmasuk,beratkeluar,beratbersih,nosegel,bps,moist,dirt from " . $dbname . ".pabrik_timbangan where nokontrak='" . $nokontrak . "' order by tanggal";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$master[$bar['tanggal']][$bar['notransaksi']] = $bar['notransaksi'];
			$supir[$bar['notransaksi']] = $bar['supir'];
			$nokendaraan[$bar['notransaksi']] = $bar['nokendaraan'];
			$jammasuk[$bar['notransaksi']] = substr($bar['jammasuk'],0,5);
			$jamkeluar[$bar['notransaksi']] = substr($bar['jamkeluar'],0,5);
			$beratkeluar[$bar['notransaksi']] = $bar['beratkeluar'];
			$beratmasuk[$bar['notransaksi']] = $bar['beratmasuk'];
			$beratbersih[$bar['notransaksi']] = $bar['beratbersih'];
			$nosegel[$bar['notransaksi']] = $bar['nosegel'];
			$ffa[$bar['notransaksi']] = $bar['bps'];
			$moist[$bar['notransaksi']] = $bar['moist'];
			$dirt[$bar['notransaksi']] = $bar['dirt'];
			$ttltimbang+=$bar['beratbersih'];
		}

		$no = 0;
		if ($ffakontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($ffakontrak, 3) . ' %';
			$textkualitas[$no] = 'Free Fatty Acid (FFA)';
		}
		if ($dobikontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($dobikontrak, 3) . ' %';
			$textkualitas[$no] = 'Dobi Content';
		}
		if ($mdanikontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($mdanikontrak, 3) . ' %';
			$textkualitas[$no] = 'Moisture & Impurities (M&I)';
		}
		if ($moistkontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($moistkontrak, 3) . ' %';
			$textkualitas[$no] = 'Moisture Content';
		}
		if ($dirtkontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($dirtkontrak, 3) . ' %';
			$textkualitas[$no] = 'Dirt Content';
		}
		if ($impuritieskontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($impuritieskontrak, 3) . ' %';
			$textkualitas[$no] = 'Impurities Content';
		}


		$str = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $kodecustomer . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$namacustomer = $bar['namacustomer'];
			$penandatangancustomer = $bar['penandatangan'];
			$jabatancustomer = $bar['jabatan'];
		}

		$datattdcustomer = explode('/', $penandatangancustomer);
		if ($datattdcustomer[1] != '') {
			$penandatangancustomer = ucwords(strtolower($datattdcustomer[0])) . '/' . ucwords(strtolower($datattdcustomer[1]));
		} else {
			$penandatangancustomer = ucwords(strtolower($datattdcustomer[0]));
		}

		$arrkodept = setheadreport('', $kodept);
		$path = "images/logo/" . $kodept . ".jpg";
		$cellpadding = 1;
		$cellspacing = 0;
		$sizefont = '12.5px';

		$tab .= "<div style='page-break-after: always;'>";
			$tab  = "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>";
				$tab .= "<tr>";
					$tab .= "<td style='width:62%' valign='top'>";
						$tab  .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>";
							$tab .= "<tr>";
								// Logo
								$tab .= "<td align='center' valign='top' style='padding-left:-65px;width:17%'>
												<img src=" . $path . " style='width:" . ($arrkodept['logowidth']) . ";height:" . $arrkodept['logoheight'] . "; transform: scale(1.3);'>
										</td>";
								// Nama perusahaan dan alamat
								$tab .= "<td style='text-align:left;width:45%' valign='top'>
											<div style=' font-size:" . ($sizefont + 1) . "px;padding-bottom:5px'>PERUSAHAAN PERKEBUNAN DAN PABRIK KELAPA SAWIT</div>
											<div style=' font-size:" . ($sizefont + 2) . "px;padding-bottom:5px'><b>PKS ".getNamaOrg($kodept)."</b></div>
											<div style=' font-size:" . ($sizefont) . "px;padding-bottom:5px'>Kebun : Desa Taju Pecah Kec. Batu Ampar Kab. Tanah Laut</div>
										</td>";
							$tab .= "</tr>";
						$tab .= "</table>";
					$tab .= "</td>";
					$tab .= "<td style='width:38%;font-size:11.6px'>";
						$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>";
							$tab .= "<tr>
										<td><b>Pembeli</b></td>
										<td align=center>:</td>
										<td><b>".getNamaCustomer($kodecustomer)."</b></td>";
							$tab .= "</tr>";
							$tab .= "<tr>
										<td><b>Penjual</b></td>
										<td align=center>:</td>
										<td><b>PKS PT. CANDI ARTHA</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>No. Kontrak</b></td>
										<td align=center>:</td>
										<td><b>".$nokontrak."</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>Pengangkut</b></td>
										<td align=center>:</td>
										<td><b>".getNamaSupplier($transportir)."</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>No. DO</b></td>
										<td align=center>:</td>
										<td><b>".$nodo."</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>Periode Pengangkutan</b></td>
										<td align=center>:</td>
										<td><b>".$prdkirim."</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>Nilai Kontrak</b></td>
										<td align=center>:</td>
										<td style='padding-left:76px'><b>".number_format($kuantitaskontrak)."&nbsp;Kg</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>Total Pengiriman SD Hi</b></td>
										<td align=center>=</td>
										<td style='padding-left:76px'><b>".number_format($ttltimbang)."&nbsp;Kg</b></td>
									</tr>";
						$tab .= "</table>";
					$tab .= "</td>";
				$tab .= "</tr>";
			$tab .= "</table>";
			// garis tipis + garis ganda
			$tab .= "<h4 align=center style='padding-bottom:-20px;padding-top:-20px'>REKAP LOADING  ".$arrinisial[$kodebarang]."</h4>";
			$ttlsdhi=0;$ttlpertgl=array();
			foreach ($master as $tgl => $arrtkt) {
				//Loop Per Tanggal
				$tab .="
				<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:9.2px;padding-bottom:6px'>
					<thead>
						<tr>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>No</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>Tanggal</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>Nama Supir</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>No Polisi</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>No Tiket</td>
							<td style='text-transform: uppercase;border:1px solid black;' colspan=2 align=center>Jam</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>No segel</td>
							<td style='text-transform: uppercase;border:1px solid black;' colspan=3 align=center>Mutu ".$arrinisial[$kodebarang]."</td>
							<td style='text-transform: uppercase;border:1px solid black;' colspan=3 align=center>Timbangan</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>s/d HI</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>Keterangan</td>
						</tr>
						<tr>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Masuk</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Keluar</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>FFA</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Moist</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Dirty</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Brutto</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Tarra</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Netto</td>
						</tr>
					</thead>
					<tbody>";$nox=0;
					foreach ($arrtkt as $tkt) {
						# Loop Tiket
						$nox++;$ttlsdhi+=$beratbersih[$tkt];
						$tab .="
							<tr>
								<td style='border:1px solid black;' align=center>".$nox."</td>
								<td style='border:1px solid black;' align=center>".($nox == 1 ? tglnmbln($tgl,'I','long') : '')."</td>
								<td style='border:1px solid black;' align=center>".$supir[$tkt]."</td>
								<td style='border:1px solid black;' align=center>".$nokendaraan[$tkt]."</td>
								<td style='border:1px solid black;' align=center>".$tkt."</td>
								<td style='border:1px solid black;' align=center>".$jammasuk[$tkt]."</td>
								<td style='border:1px solid black;' align=center>".$jamkeluar[$tkt]."</td>
								<td style='border:1px solid black;' align=center>".$nosegel[$tkt]."</td>
								<td style='border:1px solid black;' align=center>".$ffa[$tkt]."%</td>
								<td style='border:1px solid black;' align=center>".$moist[$tkt]."%</td>
								<td style='border:1px solid black;' align=center>".$dirt[$tkt]."%</td>
								<td style='border:1px solid black;' align=right>".number_format($beratkeluar[$tkt])."&nbsp;</td>
								<td style='border:1px solid black;' align=right>".number_format($beratmasuk[$tkt])."&nbsp;</td>
								<td style='border:1px solid black;' align=right>".number_format($beratbersih[$tkt])."&nbsp;</td>
								<td style='border:1px solid black;' align=right><b>".number_format($ttlsdhi)."&nbsp;</b></td>
								<td style='border:1px solid black;' align=right></td>
							</tr>";
						$ttlpertgl[$tgl]+=$beratbersih[$tkt];
					}
					$tab .="
						<tr>
							<td style='border:1px solid black;' align=center colspan=13><b>SUB TOTAL TANGGAL ".tglnmbln($tgl,'I','long')."</b></td>
							<td style='border:1px solid black;' align=right><b>".number_format($ttlpertgl[$tgl])."&nbsp;</b></td>
							<td style='border:1px solid black;' align=right></td>
							<td style='border:1px solid black;' align=right></td>
						</tr>";
					$sisakontrak = ($kuantitaskontrak-$ttlsdhi);
					if($sisakontrak > 0){
						$ket = "KEKURANGAN";
					}elseif($sisakontrak == 0){
						$ket = "";
					}else{
						$ket = "KELEBIHAN";
					}
					$tab .="
						<tr>
							<td style='border:1px solid black;' align=center colspan=13><b>".$ket." MUATAN</b></td>
							<td style='border:1px solid black;' align=right><b>".($sisakontrak < 0 ? "(".number_format($sisakontrak*-1).")" : number_format($sisakontrak)) ."&nbsp;</b></td>
							<td style='border:1px solid black;' align=right></td>
							<td style='border:1px solid black;' align=right></td>
						</tr>";
					$tab .="
					</tbody>
				</table>";
			}
			$tab .="
				<div style='border:2px solid black;width:227px;padding:7px;font-size:11.4px;transform:uppercase'><b>Catatan: KONTRAK SELESAI !</b></div>
				<div style=\"display:flex; justify-content:space-around; margin-top:40px; text-align:center;\">
					<table width=100% style='text-align:center'>
						<tr>
							<td></td>
							<td></td>
							<td>PKS ".getNamaOrg($kodept).", ".tglnmbln($tanggalbast,'I','long')."</td>
						</tr>
						<tr>
							<td>Diketahui Oleh:</td>
							<td>Di Periksa Oleh:</td>
							<td>Dibuat Oleh:</td>
						</tr>
						<tr><td style='height:40px'>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
						<tr>
							<td><b><u>Irsan Nurdiansyah Jenie</u></b></td>
							<td><b><u>Kristelli</u></b></td>
							<td><b><u>Syaiful Qiram</u></b></td>
						</tr>
						<tr>
							<td>Manager PKS</td>
							<td>KTU</td>
							<td>Plk. Produksi</td>
						</tr>
					</table>
				</div>";

		$tab .= "</div>";


		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'landscape');

		$dompdf->render();
		if ($urlefil == '0') {
			$dompdf->stream("RincianTiket REKAP LOADING " . $arrinisial[$kodebarang]." ".substr($nokontrak,0,3)." ".$kodecustomer, array("Attachment" => 0));
		} else {
			file_put_contents($urlefil, $dompdf->output());
		}
		break;



	case 'pdf_lama':
		$tab = "<style>
			@page {
				margin-top: 30px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			body {
				font-family: Serif, Times-Roman;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>";



		$str = "select * from " . $dbname . ".pmn_bastdt_notransaksireferensi where notransaksi='" . $param['notransaksi'] . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrnotransaksi[$bar['notransaksireferensi']] = $bar['notransaksireferensi'];
			$arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
		}
		$arrnotransaksi[$param['notransaksi']] = $param['notransaksi'];
		$jumlahbast = 0;

		$str = "select * from " . $dbname . ".pmn_bast where notransaksi in ('" . implode("','", $arrnotransaksi) . "') and tanggalsampai='0000-00-00'";
		$res = fetchdata($str);
		if (!empty($res)) {
			$orderby = "order by notransaksi asc";
		} else {
			$orderby = "order by tanggalsampai asc,notransaksi asc";
		}

		$str = "select * from " . $dbname . ".pmn_bast where notransaksi in ('" . implode("','", $arrnotransaksi) . "') " . $orderby;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jumlahbast++;
			$nokontrak = $bar['nokontrak'];
			$kodept = $bar['kodept'];
			$kodecustomer = $bar['kodecustomer'];
			$tanggal = $bar['tanggal']; //tanggal BL
			$tanggalbast = $bar['tanggalbast'];
			$periodejurnal = substr($bar['tanggal'], 0, 7);
			$jumlah += $bar['jumlah'];
			$closebast = $bar['close'];
			$tanggalbl = $bar['tanggalbl'];


			$kotabast = $bar['kota'];
			// $rpkgclaim=$bar['rpkgclaim'];

			$nilaiclaim += $bar['rpclaimffa'] + $bar['rpclaimmoisture'] + $bar['rpclaimdirt'] + $bar['rpclaimdobi'] + $bar['rpclaimbroken'] + $bar['rpclaimmdani'] + $bar['rpclaimimpurities'];

			$rpclaimffa += $bar['rpclaimffa'];
			$rpclaimmoisture += $bar['rpclaimmoisture'];
			$rpclaimdirt += $bar['rpclaimdirt'];
			$rpclaimdobi += $bar['rpclaimdobi'];
			$rpclaimbroken += $bar['rpclaimbroken'];
			$rpclaimmdani += $bar['rpclaimmdani'];
			$rpclaimimpurities += $bar['rpclaimimpurities'];

			$rpkgclaimffa += $bar['rpkgclaimffa'];
			$rpkgclaimmoisture += $bar['rpkgclaimmoisture'];
			$rpkgclaimdirt += $bar['rpkgclaimdirt'];
			$rpkgclaimdobi += $bar['rpkgclaimdobi'];
			$rpkgclaimbroken += $bar['rpkgclaimbroken'];
			$rpkgclaimmdani += $bar['rpkgclaimmdani'];
			$rpkgclaimimpurities += $bar['rpkgclaimimpurities'];

			$kodebarang = $bar['kodebarang'];

			#= jika gabungan
			if ($bar['ffa'] != 0) {
				$ffabast = $bar['ffa'];
			}
			if ($bar['moisture'] != 0) {
				$moistbast = $bar['moisture'];
			}
			if ($bar['mdani'] != 0) {
				$mdanibast = $bar['mdani'];
			}
			if ($bar['dirt'] != 0) {
				$dirtbast = $bar['dirt'];
			}
			if ($bar['broken'] != 0) {
				$brokenbast = $bar['broken'];
			}
			if ($bar['impurities'] != 0) {
				$impuritiesbast = $bar['impurities'];
			}
			if ($bar['dobi'] != 0) {
				$dobibast = $bar['dobi'];
			}
			$arrnamakapalponton[$bar['namakapal']] = $bar['namakapal'];
			$arrnamakapalponton[$bar['namaponton']] = $bar['namaponton'];

			if ($bar['namakapal'] != '') {
				$arrdtketeranganjudul[$bar['tanggalbl']][$bar['namakapal']] = $namakapalponton[$bar['namakapal']] . " Tanggal " . tglnmbln($bar['tanggalbl'], 'i', 'l') . ", ";
			} else {
				$arrdtketeranganjudul[$bar['tanggalbl']][$bar['namaponton']] = $namakapalponton[$bar['namaponton']] . " Tanggal " . tglnmbln($bar['tanggalbl'], 'i', 'l') . ", ";
			}


			#= arr tanggal bl diganti jadi tanggal data, dikarenakan adanya 2 bast berbeda, disatukan, nomor kontrak sama, tanggal bl sama
			#= jadi array tanggal bl diganti tanggal data
			$arrkodetangki[$bar['kodetangki']] = $bar['kodetangki'];
			$arrtanggalbl[$bar['nourut']] = $bar['nourut'];
			$arrtanggalblori[$bar['tanggalbl']] = $bar['tanggalbl'];

			$dtjumlah[$bar['nourut']][$bar['kodetangki']] += $bar['jumlah'];
			$listdata[$bar['nourut']][$bar['kodetangki']] = $bar['kodetangki'];
			$arrmutu[$bar['nourut']][$bar['kodetangki']] = $bar['kodetangki'];
			$dtnamakapal[$bar['nourut']][$bar['kodetangki']] = $bar['namakapal'];
			$dtnamakapaltanggal[$bar['nourut']][$bar['kodetangki']] = $bar['tanggalbl'];
			$dtnamaponton[$bar['nourut']][$bar['kodetangki']] = $bar['namaponton'];
			if ($bar['nourut'] == '0000-00-00') {
				$sortdata = SORT_DESC;
			} else {
				$sortdata = SORT_ASC;
			}

			$nomutubast = 1;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['ffa'];
			$dttextmutu[$nomutubast] = 'Free Fatty Acid (FFA)';
			//$arrmutubast[$nomutubast]=$nomutubast;


			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['moisture'];
			$dttextmutu[$nomutubast] = 'Moisture Content';

			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['mdani'];
			$dttextmutu[$nomutubast] = 'Moisture & Impurities (M&I)';
			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['dirt'];
			//$arrmutubast[$nomutubast]=$nomutubast;
			$dttextmutu[$nomutubast] = 'Dirt Content';

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['broken'];
			$dttextmutu[$nomutubast] = 'Broken Content';
			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['impurities'];
			$dttextmutu[$nomutubast] = 'Impurities Content';
			//$arrmutubast[$nomutubast]=$nomutubast;

			$nomutubast++;
			$dtmutu[$bar['nourut']][$bar['kodetangki']][$nomutubast] = $bar['dobi'];
			$dttextmutu[$nomutubast] = 'Dobi Content';
			//$arrmutubast[$nomutubast]=$nomutubast;
		}

		$dttextglobalkapalponton = '';

		#ada customer yang beberapa kata tidak ditampilkan
		$excludekodecustomer = array('SAL' => 'SAL');


		// echo"<br><br><br><br>";
		// echo"<pre>";
		// print_r($arrdtketeranganjudul);exit("Error:A");


		//array_multisort($arrtanggalbl,$sortdata);
		// array_multisort($arrnamakapalponton,SORT_ASC);
		foreach ($arrtanggalblori as $dttanggalbl) {
			foreach ($arrnamakapalponton as $dtkapalponton) {
				if ($arrdtketeranganjudul[$dttanggalbl][$dtkapalponton] != '') {
					$dtketeranganjudul .= $namakapalponton[$dtkapalponton] . " Tanggal " . tglnmbln($dttanggalbl, 'i', 'l') . ", ";
				}
			}
		}

		// echo"<pre>";
		// echo"<br><br><br><br>";
		// echo $dtketeranganjudul;
		// print_r($arrtanggalbl);
		// print_r($listdata);
		// echo"</pre>";exit();


		#= jika global
		$nobast = 0;
		if ($ffabast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($ffabast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Free Fatty Acid (FFA)';
		}
		if ($moistbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($moistbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Moisture Content';
		}
		if ($mdanibast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($mdanibast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Moisture & Impurities (M&I)';
		}
		if ($brokenbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($brokenbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Broken Content';
		}
		if ($dobibast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($dobibast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Dobi Content';
		}


		if ($dirtbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($dirtbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Dirt Content';
		}
		if ($impuritiesbast != 0) {
			$nobast++;
			$nilaikualitasbast[$nobast] = hidezerodecimal($impuritiesbast, 3) . ' %';
			$textkualitasbast[$nobast] = 'Impurities Content';
		}
		#= tutup jika global



		#= ambil data dari kontrakjual
		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			//indra
			$hargasatuan = $bar['hargasatuan'];
			// sudah mewakili berikat / tidak , jika ppn 0 => berikat; jika 10 maka tidak berikat
			$ffakontrak = $bar['ffa'];
			$dobikontrak = $bar['dobi'];
			$mdanikontrak = $bar['mdani'];
			$moistkontrak = $bar['moist'];
			$dirtkontrak = $bar['dirt'];
			$impuritieskontrak = $bar['grading'];
			$penandatangan = $bar['penandatangan'];
			$satuanbarang = $bar['satuan'];
			$matauang = $dtsimbol[$bar['matauang']];


			if ($tanggal < '2022-04-01') {

				$persenppn = $bar['persenppn'];
				$defaultpersenppn = $bar['defaultpersenppn']; // secara default 10
			} else {
				if ($bar['persenppn'] >= 10) {
					$persenppn = 11;
				} else {
					$persenppn = $bar['persenppn'];
				}
				$defaultpersenppn = 11;
			}

			// $kontrak=$bar[''];
		}

		#= jabatan ttd
		$str = "select * from " . $dbname . ".pmn_5ttd where nama='" . $penandatangan . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jabatanpenandatangan = $bar['jabatan'];
		}



		$no = 0;
		if ($ffakontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($ffakontrak, 3) . ' %';
			$textkualitas[$no] = 'Free Fatty Acid (FFA)';
		}
		if ($dobikontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($dobikontrak, 3) . ' %';
			$textkualitas[$no] = 'Dobi Content';
		}
		if ($mdanikontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($mdanikontrak, 3) . ' %';
			$textkualitas[$no] = 'Moisture & Impurities (M&I)';
		}
		if ($moistkontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($moistkontrak, 3) . ' %';
			$textkualitas[$no] = 'Moisture Content';
		}
		if ($dirtkontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($dirtkontrak, 3) . ' %';
			$textkualitas[$no] = 'Dirt Content';
		}
		if ($impuritieskontrak != 0) {
			$no++;
			$nilaikualitas[$no] = 'max. ' . hidezerodecimal($impuritieskontrak, 3) . ' %';
			$textkualitas[$no] = 'Impurities Content';
		}


		$str = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $kodecustomer . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$namacustomer = $bar['namacustomer'];
			$penandatangancustomer = $bar['penandatangan'];
			$jabatancustomer = $bar['jabatan'];
		}

		$datattdcustomer = explode('/', $penandatangancustomer);
		if ($datattdcustomer[1] != '') {
			$penandatangancustomer = ucwords(strtolower($datattdcustomer[0])) . '/' . ucwords(strtolower($datattdcustomer[1]));
		} else {
			$penandatangancustomer = ucwords(strtolower($datattdcustomer[0]));
		}

		$arrkodept = setheadreport('', $kodept);
		$cellpadding = 1;
		$cellspacing = 1;
		$sizefont = '14';
		// print_r($arrkodept);exit();

		$tab .= "<div style='page-break-after: always;'>";
		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>"; //logoheight logowidth
		$tab .= "<tr>";
		$tab .= "<td style='width:50px;' align=center><img src=" . $arrkodept['logo'] . " style='width:" . $arrkodept['logowidth'] . ";height:" . $arrkodept['logoheight'] . "'></td>";
		$tab .= "<td style='width:350px;text-align:center;font-size:" . ($sizefont + 10) . "px'>" . $arrkodept['nama'] . "</td>";
		$tab .= "<td style='width:50px;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:center;font-size:" . $sizefont . "px' colspan=3>BERITA ACARA PENERIMAAN KOMODITI</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:center;font-size:" . $sizefont . "px' colspan=3>" . $namabarang[$kodebarang] . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:center;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;font-size:" . $sizefont . "px'  colspan=3>KONTRAK NOMOR " . $nokontrak . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br>";

		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'  colspan=3>Telah diterima oleh " . $namacustomer . " dari " . $dtketeranganjudul . " muatan " . $namabarang[$kodebarang] . " dengan perincian sebagai berikut :</td>";
		$tab .= "</tr>";

		$tab .= "</table>";

		$tab .= "<br>";


		if ($excludekodecustomer[$kodecustomer] == '') {
			$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
			for ($i = 1; $i <= $no; $i++) {
				$tab .= "<tr>";
				if ($i == 1) {
					$tab .= "<td rowspan='" . $no . "' valign=top style='text-align:left;width:100px;'>Mutu Standar Komoditi :</td>";
					$tab .= "<td style='text-align:left;width:150px;'>" . $textkualitas[$i] . "</td>";
					$tab .= "<td style='text-align:left;width:150px;'>" . $nilaikualitas[$i] . "</td>";
				} else {
					$tab .= "<td style='text-align:left;width:150px;'>" . $textkualitas[$i] . "</td>";
					$tab .= "<td style='text-align:left;width:150px;'>" . $nilaikualitas[$i] . "</td>";
				}

				$tab .= "</tr>";
			}
			$tab .= "</table>";
		}

		$tab .= "<br>";

		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;width:250px;'>Jumlah komoditi yang diterima adalah</td>";
		$tab .= "<td style='text-align:left;width:150px;'>" . number_format($jumlah) . " " . $satuanbarang . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br>";
		// echo $nomutubast;exit("error:A");
		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";

		if ($excludekodecustomer[$kodecustomer] == '') {
			$tab .= "<tr>";
			$tab .= "<td style='text-align:left;' colspan=7>Mutu barang yang diterima :</td>";
			$tab .= "</tr>";
		}

		$notampil = 0;

		foreach ($arrtanggalbl as $dttanggalbl) {
			foreach ($arrkodetangki as $dtkodetangki) {
				if ($listdata[$dttanggalbl][$dtkodetangki] != '') {

					$dttextkapalponton[$dttanggalbl][$dtkodetangki] = '';
					if ($namakapalponton[$dtnamakapal[$dttanggalbl][$dtkodetangki]] != '' and $namakapalponton[$dtnamaponton[$dttanggalbl][$dtkodetangki]] != '') {
						$dttextkapalponton[$dttanggalbl][$dtkodetangki] = $namakapalponton[$dtnamakapal[$dttanggalbl][$dtkodetangki]] . " - " . $namakapalponton[$dtnamaponton[$dttanggalbl][$dtkodetangki]];
					}
					if ($namakapalponton[$dtnamakapal[$dttanggalbl][$dtkodetangki]] != '' and $namakapalponton[$dtnamaponton[$dttanggalbl][$dtkodetangki]] == '') {
						$dttextkapalponton[$dttanggalbl][$dtkodetangki] = $namakapalponton[$dtnamakapal[$dttanggalbl][$dtkodetangki]];
					}
					if ($namakapalponton[$dtnamakapal[$dttanggalbl][$dtkodetangki]] == '' and $namakapalponton[$dtnamaponton[$dttanggalbl][$dtkodetangki]] != '') {
						$dttextkapalponton[$dttanggalbl][$dtkodetangki] = $namakapalponton[$dtnamaponton[$dttanggalbl][$dtkodetangki]];
					}
					$notampil++;

					$tab .= "<tr>";
					if ($jumlahbast == '1' and $notampil == '1') {
						$tab .= "<td style='text-align:left'></td>";
					} else {
						$tab .= "<td style='text-align:left'>" . $notampil . ".</td>";
					}
					// $tab.="<td style='text-align:left' colspan=3>".$dttextkapalponton[$dttanggalbl][$dtkodetangki].", ".$dtkodetangki." ( ".tglnmbln($dttanggalbl,'i','l')." )</td>"; 
					$tab .= "<td style='text-align:left' colspan=3>" . $dttextkapalponton[$dttanggalbl][$dtkodetangki] . ", " . $dtkodetangki . " ( " . tglnmbln($dtnamakapaltanggal[$dttanggalbl][$dtkodetangki], 'i', 'l') . " )</td>";
					$tab .= "<td style='text-align:right'></td>";
					$tab .= "<td style='text-align:right'>" . hidezerodecimal($dtjumlah[$dttanggalbl][$dtkodetangki]) . "</td>";
					$tab .= "<td style='text-align:left'>" . $satuanbarang . "</td>";
					$tab .= "</tr>";
					for ($i = 1; $i < $nomutubast; $i++) {
						if ($dtmutu[$dttanggalbl][$dtkodetangki][$i] != 0 and $excludekodecustomer[$kodecustomer] == '') {
							$tab .= "<tr>";
							$tab .= "<td style='text-align:left;width:50px;'></td>";
							$tab .= "<td style='text-align:left;width:150px;'>" . $dttextmutu[$i] . "</td>";
							$tab .= "<td style='text-align:left;width:50px;'>" . $dtmutu[$dttanggalbl][$dtkodetangki][$i] . " %</td>";
							$tab .= "<td style='text-align:left;width:100px;'>&nbsp;</td>";
							$tab .= "<td style='text-align:right;width:1px;'>&nbsp;</td>";
							$tab .= "<td style='text-align:right;width:50px;'>&nbsp;</td>";
							$tab .= "<td style='text-align:left;width:50px;'>&nbsp;</td>";
							$tab .= "</tr>";
						}
					}
				}
			}
		}



		$tab .= "</table>";

		/*
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;' colspan=3>Mutu barang yang diterima :</td>"; 
			$tab.="</tr>";
			for($i=1;$i<=$nobast;$i++){
			$tab.="<tr>";
				if($i==1){
					$tab.="<td rowspan='".$nobast."' valign=top style='text-align:left;width:100px;'>Mutu Komoditi :</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$textkualitasbast[$i]."</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$nilaikualitasbast[$i]."</td>"; 
				}else{
					$tab.="<td style='text-align:left;width:150px;'>".$textkualitasbast[$i]."</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$nilaikualitasbast[$i]."</td>";
				}
				
			$tab.="</tr>";
		}
		$tab.="</table>";
		*/

		$tab .= "<br>";

		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'>Perhitungan Penalti akibat penyimpangan mutu dari standar akan diperhitungkan dalam pembayaran.</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br>";

		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'>Demikian Berita Acara ini dibuat.</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br>";

		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;' colspan=3>" . $kotabast . ", " . tglnmbln($tanggal, 'i', 'l') . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'>" . $_SESSION['lang']['dibuat'] . "</td>";
		$tab .= "<td style='text-align:left;'>&nbsp;</td>";
		$tab .= "<td style='text-align:left;'>" . $_SESSION['lang']['diterimaoleh'] . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'>" . $namapt[$kodept] . "</td>";
		$tab .= "<td style='text-align:left;'>&nbsp;</td>";
		$tab .= "<td style='text-align:left;'>" . $namacustomer . "</td>";
		$tab .= "</tr>";
		for ($i = 0; $i <= 4; $i++) {
			$tab .= "<tr>";
			$tab .= "<td style='text-align:left;'>&nbsp;</td>";
			$tab .= "<td style='text-align:left;'>&nbsp;</td>";
			$tab .= "<td style='text-align:left;'>&nbsp;</td>";
			$tab .= "</tr>";
		}
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'>" . ucwords(strtolower(getKary($penandatangan))) . "</td>";
		$tab .= "<td style='text-align:left;'>&nbsp;</td>";
		$tab .= "<td style='text-align:left;'>" . $penandatangancustomer . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'>" . ucwords(strtolower($jabatanpenandatangan)) . "</td>";
		$tab .= "<td style='text-align:left;'>&nbsp;</td>";
		$tab .= "<td style='text-align:left;'>" . ucwords(strtolower($jabatancustomer)) . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";



		$tab .= "<footer>";
		$cellpadding = 1;
		$tab .= "<table style='font-size:10px' border=0 cellpadding=" . $cellpadding . " width=100%>";
		$tab .= "<tr>";
		$tab .= "<td align=center><b>Alamat Korespondensi :</b></td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td align=center><b>" . $arrkodept['alamat'] . "</b></td>";

		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td align=center><b>Telp. " . $arrkodept['telepon'] . " Fax. " . $arrkodept['fax'] . "</b></td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</footer>";
		$tab .= "</div>";


		#================================= page 2
		#================================= page 2



		$tab .= "<div style='page-break-after: always;'>";
		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>"; //logoheight logowidth
		$tab .= "<tr>";
		$tab .= "<td style='width:50px;' align=center>&nbsp;</td>";
		$tab .= "<td style='width:350px;text-align:center;font-size:" . ($sizefont + 14) . "px'>&nbsp;</td>";
		$tab .= "<td style='width:50px;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:center;font-size:" . $sizefont . "px' colspan=3>SURAT KETERANGAN PEMBAYARAN</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:center;font-size:" . $sizefont . "px' colspan=3>" . $namabarang[$kodebarang] . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:center;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;font-size:" . $sizefont . "px'  colspan=3>KONTRAK NOMOR " . $nokontrak . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br>";

		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
		// $tab.="<tr>";
		// $tab.="<td style='text-align:left;'  colspan=3>Telah diterima oleh ".$namacustomer." dari ".$dttextglobalkapalponton." tanggal ".tglnmbln($tanggal,'i','l')." muatan ".$namabarang[$kodebarang]." dengan perincian sebagai berikut :</td>"; 
		// $tab.="</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'  colspan=3>Telah diterima oleh " . $namacustomer . " dari " . $dtketeranganjudul . " muatan " . $namabarang[$kodebarang] . " dengan perincian sebagai berikut :</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br>";

		if ($excludekodecustomer[$kodecustomer] == '') {
			$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
			for ($i = 1; $i <= $no; $i++) {
				$tab .= "<tr>";
				if ($i == 1) {
					$tab .= "<td rowspan='" . $no . "' valign=top style='text-align:left;width:100px;'>Mutu Standar Komoditi :</td>";
					$tab .= "<td style='text-align:left;width:150px;'>" . $textkualitas[$i] . "</td>";
					$tab .= "<td style='text-align:left;width:150px;'>" . $nilaikualitas[$i] . "</td>";
				} else {
					$tab .= "<td style='text-align:left;width:150px;'>" . $textkualitas[$i] . "</td>";
					$tab .= "<td style='text-align:left;width:150px;'>" . $nilaikualitas[$i] . "</td>";
				}

				$tab .= "</tr>";
			}
			$tab .= "</table>";
		}

		$tab .= "<br>";

		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;width:250px;'>Jumlah komoditi yang diterima adalah</td>";
		$tab .= "<td style='text-align:left;width:150px;'>" . number_format($jumlah) . " " . $satuanbarang . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br>";
		// echo $nomutubast;exit("error:A");
		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
		if ($excludekodecustomer[$kodecustomer] == '') {
			$tab .= "<tr>";
			$tab .= "<td style='text-align:left;' colspan=7>Mutu barang yang diterima :</td>";
			$tab .= "</tr>";
		}
		$notampil = 0;

		foreach ($arrtanggalbl as $dttanggalbl) {
			foreach ($arrkodetangki as $dtkodetangki) {
				if ($listdata[$dttanggalbl][$dtkodetangki] != '') {

					$dttextkapalponton[$dttanggalbl][$dtkodetangki] = '';
					if ($namakapalponton[$dtnamakapal[$dttanggalbl][$dtkodetangki]] != '' and $namakapalponton[$dtnamaponton[$dttanggalbl][$dtkodetangki]] != '') {
						$dttextkapalponton[$dttanggalbl][$dtkodetangki] = $namakapalponton[$dtnamakapal[$dttanggalbl][$dtkodetangki]] . " - " . $namakapalponton[$dtnamaponton[$dttanggalbl][$dtkodetangki]];
					}
					if ($namakapalponton[$dtnamakapal[$dttanggalbl][$dtkodetangki]] != '' and $namakapalponton[$dtnamaponton[$dttanggalbl][$dtkodetangki]] == '') {
						$dttextkapalponton[$dttanggalbl][$dtkodetangki] = $namakapalponton[$dtnamakapal[$dttanggalbl][$dtkodetangki]];
					}
					if ($namakapalponton[$dtnamakapal[$dttanggalbl][$dtkodetangki]] == '' and $namakapalponton[$dtnamaponton[$dttanggalbl][$dtkodetangki]] != '') {
						$dttextkapalponton[$dttanggalbl][$dtkodetangki] = $namakapalponton[$dtnamaponton[$dttanggalbl][$dtkodetangki]];
					}

					$notampil++;
					$tab .= "<tr>";

					if ($jumlahbast == '1' and $notampil == '1') {
						$tab .= "<td style='text-align:left'></td>";
					} else {
						$tab .= "<td style='text-align:left'>" . $notampil . ".</td>";
					}

					// $tab.="<td style='text-align:left'>".$notampil.".</td>"; 
					// $tab.="<td style='text-align:left' colspan=3>".$dttextkapalponton[$dttanggalbl][$dtkodetangki].", ".$dtkodetangki." ( ".tglnmbln($dttanggalbl,'i','l')." )</td>"; 
					$tab .= "<td style='text-align:left' colspan=3>" . $dttextkapalponton[$dttanggalbl][$dtkodetangki] . ", " . $dtkodetangki . " ( " . tglnmbln($dtnamakapaltanggal[$dttanggalbl][$dtkodetangki], 'i', 'l') . " )</td>";
					$tab .= "<td style='text-align:right'></td>";
					$tab .= "<td style='text-align:right'>" . hidezerodecimal($dtjumlah[$dttanggalbl][$dtkodetangki]) . "</td>";
					$tab .= "<td style='text-align:left'>" . $satuanbarang . "</td>";
					$tab .= "</tr>";
					for ($i = 1; $i < $nomutubast; $i++) {
						if ($dtmutu[$dttanggalbl][$dtkodetangki][$i] != 0 and $excludekodecustomer[$kodecustomer] == '') {
							$tab .= "<tr>";
							$tab .= "<td style='text-align:left;width:50px;'></td>";
							$tab .= "<td style='text-align:left;width:150px;'>" . $dttextmutu[$i] . "</td>";
							$tab .= "<td style='text-align:left;width:50px;'>" . $dtmutu[$dttanggalbl][$dtkodetangki][$i] . " %</td>";
							$tab .= "<td style='text-align:left;width:100px;'>&nbsp;</td>";
							$tab .= "<td style='text-align:right;width:1px;'>&nbsp;</td>";
							$tab .= "<td style='text-align:right;width:50px;'>&nbsp;</td>";
							$tab .= "<td style='text-align:left;width:50px;'>&nbsp;</td>";
							$tab .= "</tr>";
						}
					}
				}
			}
		}



		$tab .= "</table>";
		$tab .= "<br>";


		#= pembayaran uang muka
		#= ambil COA
		/*
		
			$str="select * from ".$dbname.".keu_5jenispenagihandt where kodebarang='".$kodebarang."' and kodejenis='CIPP'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$noakunsales=$bar['noakunsales'];
				$noakunuangmuka=$bar['noakunuangmuka'];
				$noakunppn=$bar['noakunppn'];
				$noakunpiutang=$bar['noakunpiutang'];
			}
		
			$nodata=0;
			#= cari sisa uang muka, dengan cara sum debet-kredit  jurnal where nodok=param nomor kontrak
			$str="select  sum(jumlah) as jumlah,noreferensi from ".$dbname.".keu_jurnaldt_vw where nodok='".$nokontrak."' and noakun='".$noakunuangmuka."' and tanggal<='".$tanggalbl."' group by noreferensi";
			// echo $str;exit();
			$res=fetchdata($str);
			foreach($res as $bar){
				$nodata++;
				$nilaiuangmuka[$nodata]=$bar['jumlah'];
				$tnilaiuangmuka+=$bar['jumlah'];
			}
			
			// $nilaiclaim=$rpkgclaim*$jumlah;
			
			if($persenppn==0){
				$persenppn=$defaultpersenppn;
			}
			$nilaipenjualan=$jumlah*$hargasatuan;
			$nilaisisa=$nilaipenjualan+$tnilaiuangmuka-$nilaiclaim;;
			$nilaippn=floor($persenppn/100*$nilaisisa);
			$nilaitagihan=$nilaisisa+$nilaippn;
		*/

		$str = "select * from " . $dbname . ".keu_5jenispenagihandt where kodebarang='" . $kodebarang . "' and kodejenis='CIPP'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$noakunsales = $bar['noakunsales'];
			$noakunuangmuka = $bar['noakunuangmuka'];
			$noakunppn = $bar['noakunppn'];
			$noakunpiutang = $bar['noakunpiutang'];
		}

		$nodata = $nourutinv = $nourutbast = 0;
		$str = "select * from " . $dbname . ".keu_penagihanht where nokontrak='" . $nokontrak . "' and tanggal<='" . $tanggalbl . "' and jenisinvoice='UM'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nodata++;
			$nourutinv++;
			$nilaiuangmuka[$nodata] = ($bar['nilaiinvoice'] * -1);
			$tnilaiuangmuka += ($bar['nilaiinvoice'] * -1);
			$dtrpketerangan[$nodata] = 'Dikurangi Pembayaran ke ' . $nourutinv . ' ';
		}

		// $tnilaiuangmuka=0;
		// exit("Error:".$tnilaiuangmuka);
		#= ambil data bast
		$str = "select *,rpclaimffa+rpclaimmoisture+rpclaimdirt+rpclaimdobi+rpclaimbroken+rpclaimmdani+rpclaimimpurities+rpclaimlain as rpclaim from " . $dbname . ".pmn_bast where nokontrak='" . $nokontrak . "'  and tanggal<'" . $tanggalbl . "'";
		// echo $str;exit();
		$res = fetchdata($str);
		foreach ($res as $bar) {
			// $nodata++;
			$nourutbast++;
			// $dtrpketerangan[$nodata]='Pengiriman ke '.$nourutbast.' ';
			// $nilaiuangmuka[$nodata]=$bar['rpbast'];
			// $tnilaiuangmuka+=($bar['rpbast']*-1);
			if ($bar['rpclaim'] > 0) {
				$nodata++;
				$dtrpketerangan[$nodata] = 'Penalty Mutu Pengiriman ke ' . $nourutbast . ' ';
				$nilaiuangmuka[$nodata] = ($bar['rpclaim'] * -1);
				$tnilaiuangmuka += ($bar['rpclaim'] * -1);
			}
		}
		// exit("Error:".$tnilaiuangmuka);
		// echo"<pre>";
		// print_r($dtrpketerangan);
		// echo"</pre>";
		// exit("Error:A");
		if ($persenppn == 0) {
			$persenppn = $defaultpersenppn;
		}

		#= query ulang untuk penalti
		$str = "select * from " . $dbname . ".pmn_bast where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		$nilaiimpurities = $res[0]['rpclaimimpurities'];
		$rpclaimffa = $res[0]['rpclaimffa'];

		$nilaipenjualan = $jumlah * $hargasatuan;
		// exit("Error:".$nilaipenjualan);
		// $nilaisisa=$nilaipenjualan+$tnilaiuangmuka-$nilaiclaim;
		$nilaisisa = $nilaipenjualan + $tnilaiuangmuka - $nilaiimpurities - $rpclaimffa;

		$nilaippn = floor($persenppn / 100 * $nilaisisa);
		$nilaitagihan = $nilaisisa + $nilaippn;

		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;' colspan=4>Jumlah tagihan kami kepada perusahaan Saudara adalah sebagai berikut :</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;width:325px;'>Nilai penjualan " . hidezerodecimal($jumlah) . " " . $satuanbarang . " x " . $matauang . " " . hidezerodecimal($hargasatuan, 2) . " / " . $satuanbarang . "</td>";
		$tab .= "<td style='text-align:right;width:20px;'>" . $matauang . "</td>";

		$tab .= "<td style='text-align:right;width:100px;'>" . hidezerodecimal($nilaipenjualan, 2) . "</td>";
		$tab .= "<td style='text-align:left;width:75px;'>&nbsp;</td>";
		$tab .= "</tr>";
		if ($nilaiclaim > 0) {
			#= query ulang untuk penalti
			$str = "select * from " . $dbname . ".pmn_bast where notransaksi='" . $param['notransaksi'] . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				if ($bar['rpclaimffa'] > 0) {
					$tab .= "<tr>";
					$tab .= "<td style='text-align:left;'>Penalty Mutu FFA " . hidezerodecimal($bar['jumlah']) . " " . $satuanbarang . " x " . $matauang . " " . hidezerodecimal($bar['rpkgclaimffa'], 2) . " / " . $satuanbarang . " ( " . $matauang . " " . hidezerodecimal($bar['rpclaimffa'], 2) . " )</td>";
					$tab .= "<td style='text-align:right;'></td>";
					$tab .= "<td style='text-align:right'></td>";
					$tab .= "<td>&nbsp;</td>";
					$tab .= "</tr>";
				}
				if ($bar['rpclaimmoisture'] > 0) {
					$tab .= "<tr>";
					$tab .= "<td style='text-align:left;'>Penalty Mutu Moisture ( " . hidezerodecimal($bar['moisture'], 3) . " % - " . hidezerodecimal($moistkontrak, 3) . " %) x " . hidezerodecimal($bar['jumlah']) . " " . $satuanbarang . " x " . $matauang . " " . hidezerodecimal($bar['rpkgclaimmoisture'], 2) . " / " . $satuanbarang . " ( " . $matauang . " " . hidezerodecimal($bar['rpclaimmoisture'], 2) . " )</td>";
					$tab .= "<td style='text-align:right;'></td>";
					$tab .= "<td style='text-align:right'></td>";
					$tab .= "<td>&nbsp;</td>";
					$tab .= "</tr>";
				}
				if ($bar['rpclaimimpurities'] > 0) {
					$tab .= "<tr>";
					$tab .= "<td style='text-align:left;' colspan=4>Penalty Mutu Impurities " . hidezerodecimal($bar['jumlah']) . " " . $satuanbarang . " x " . $matauang . " " . hidezerodecimal($bar['rpkgclaimimpurities'], 2) . " / " . $satuanbarang . " ( " . $matauang . " " . hidezerodecimal($bar['rpclaimimpurities'], 2) . " )</td>";
					$tab .= "<td style='text-align:right;'></td>";
					$tab .= "<td style='text-align:right'></td>";
					$tab .= "<td>&nbsp;</td>";
					$tab .= "</tr>";
				}

				if ($bar['rpclaimmdani'] > 0) {
					$tab .= "<tr>";
					$tab .= "<td style='text-align:left;'>Penalty Mutu M&I ( " . hidezerodecimal($bar['mdani'], 3) . " % - " . hidezerodecimal($mdanikontrak, 3) . " % ) x " . hidezerodecimal($bar['jumlah']) . " " . $satuanbarang . " x " . $matauang . " " . hidezerodecimal($bar['rpkgclaimmdani'], 2) . " / " . $satuanbarang . " ( " . $matauang . " " . hidezerodecimal($bar['rpclaimmdani'], 2) . " )</td>";
					$tab .= "<td style='text-align:right;'></td>";
					$tab .= "<td style='text-align:right'></td>";
					$tab .= "<td>&nbsp;</td>";
					$tab .= "</tr>";
				}
				if ($bar['rpclaimdirt'] > 0) {
					$tab .= "<tr>";
					$tab .= "<td style='text-align:left;'>Penalty Mutu Dirt " . hidezerodecimal($bar['jumlah']) . " " . $satuanbarang . " x " . $matauang . " " . hidezerodecimal($bar['rpkgclaimdirt'], 2) . " / " . $satuanbarang . " ( " . $matauang . " " . hidezerodecimal($bar['rpclaimdirt'], 2) . " )</td>";
					$tab .= "<td style='text-align:right;'></td>";
					$tab .= "<td style='text-align:right'></td>";
					$tab .= "<td>&nbsp;</td>";
					$tab .= "</tr>";
				}
				if ($bar['rpclaimdobi'] > 0) {
					$tab .= "<tr>";
					$tab .= "<td style='text-align:left;'>Penalty Mutu Dobi " . hidezerodecimal($bar['jumlah']) . " " . $satuanbarang . " x " . $matauang . " " . hidezerodecimal($bar['rpkgclaimdobi'], 2) . " / " . $satuanbarang . " ( " . $matauang . " " . hidezerodecimal($bar['rpclaimdobi'], 2) . " )</td>";
					$tab .= "<td style='text-align:right;'></td>";
					$tab .= "<td style='text-align:right'></td>";
					$tab .= "<td>&nbsp;</td>";
					$tab .= "</tr>";
				}
				if ($bar['rpclaimbroken'] > 0) {
					$tab .= "<tr>";
					$tab .= "<td style='text-align:left;'>Penalty Mutu Broken " . hidezerodecimal($bar['jumlah']) . " " . $satuanbarang . " x " . $matauang . " " . hidezerodecimal($bar['rpkgclaimbroken'], 2) . " / " . $satuanbarang . " ( " . $matauang . " " . hidezerodecimal($bar['rpclaimbroken'], 2) . " )</td>";
					$tab .= "<td style='text-align:right;'></td>";
					$tab .= "<td style='text-align:right'></td>";
					$tab .= "<td>&nbsp;</td>";
					$tab .= "</tr>";
				}
			}
		}
		for ($i = 1; $i <= $nodata; $i++) {
			$tab .= "<tr>";
			$tab .= "<td style='text-align:left'>" . $dtrpketerangan[$i] . " ( " . $matauang . " " . hidezerodecimal(abs($nilaiuangmuka[$i]), 2) . " )</td>";
			$tab .= "<td style='text-align:right'>&nbsp;</td>";

			$tab .= "<td style='text-align:right;'>&nbsp;</td>";
			$tab .= "<td style='text-align:left;'>&nbsp;</td>";
			$tab .= "</tr>";
		}
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left'>&nbsp;</td>";
		$tab .= "<td style='text-align:right'>&nbsp;</td>";
		$tab .= "<td  style='text-align:center;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000'>&nbsp;</td>";
		$tab .= "<td style='text-align:left;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left'>Sisa tagihan menjadi</td>";
		$tab .= "<td style='text-align:right'>" . $matauang . "</td>";
		$tab .= "<td style='text-align:right;'>" . hidezerodecimal($nilaisisa, 2) . "</td>";
		$tab .= "<td style='text-align:left;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left'>PPN " . $persenppn . " % yang harus dibayar</td>";
		$tab .= "<td style='text-align:right'>" . $matauang . "</td>";
		$tab .= "<td style='text-align:right;'>" . hidezerodecimal($nilaippn, 2) . "</td>";
		$tab .= "<td style='text-align:left;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left'>&nbsp;</td>";
		$tab .= "<td style='text-align:right'>&nbsp;</td>";
		$tab .= "<td  style='text-align:center;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000'>&nbsp;</td>";
		$tab .= "<td style='text-align:left;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left'>Tagihan yang harus dibayar</td>";
		$tab .= "<td style='text-align:right'>" . $matauang . "</td>";
		$tab .= "<td style='text-align:right;'>" . hidezerodecimal($nilaitagihan, 2) . "</td>";
		$tab .= "<td style='text-align:left;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left'>&nbsp;</td>";
		$tab .= "<td style='text-align:right'>&nbsp;</td>";
		$tab .= "<td style='text-align:right'>&nbsp;</td>";
		$tab .= "<td style='text-align:left;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left'>Demikian keterangan ini dibuat.</td>";
		$tab .= "<td style='text-align:right'></td>";
		$tab .= "<td style='text-align:right;'></td>";
		$tab .= "<td style='text-align:left;'></td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br>";

		$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;' colspan=3>" . $kotabast . ", " . tglnmbln($tanggal, 'i', 'l') . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'>" . $_SESSION['lang']['dibuat'] . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'>" . $namapt[$kodept] . "</td>";
		$tab .= "</tr>";
		for ($i = 0; $i <= 4; $i++) {
			$tab .= "<tr>";
			$tab .= "<td style='text-align:left;'>&nbsp;</td>";
			$tab .= "</tr>";
		}
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'>" . ucwords(strtolower(getKary($penandatangan))) . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr>";
		$tab .= "<td style='text-align:left;'>" . ucwords(strtolower($jabatanpenandatangan)) . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "</div>";


		$tab .= "<br>";



		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');

		$dompdf->render();
		if ($urlefil == '0') {
			$dompdf->stream("Print_BAST_" . $nobast, array("Attachment" => 0));
		} else {
			file_put_contents($urlefil, $dompdf->output());
		}
		break;

		function terbilang($x)
		{
			$angka = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
			if ($x < 12)
				return " " . $angka[$x];
			elseif ($x < 20)
				return terbilang($x - 10) . " belas";
			elseif ($x < 100)
				return terbilang(intval($x / 10)) . " puluh" . terbilang($x % 10);
			elseif ($x < 200)
				return " seratus" . terbilang($x - 100);
			elseif ($x < 1000)
				return terbilang(intval($x / 100)) . " ratus" . terbilang($x % 100);
			elseif ($x < 2000)
				return " seribu" . terbilang($x - 1000);
			elseif ($x < 1000000)
				return terbilang(intval($x / 1000)) . " ribu" . terbilang($x % 1000);
			elseif ($x < 1000000000)
				return terbilang(intval($x / 1000000)) . " juta" . terbilang($x % 1000000);
		}

		// Fungsi ubah tanggal ke format terbilang lengkap dengan hari
		function tanggalHariTerbilang($tanggal)
		{
			// Nama bulan
			$bulanIndo = array(
				1 => "Januari",
				"Februari",
				"Maret",
				"April",
				"Mei",
				"Juni",
				"Juli",
				"Agustus",
				"September",
				"Oktober",
				"November",
				"Desember"
			);
			// Nama hari
			$hariIndo = array(
				"Sunday"    => "Minggu",
				"Monday"    => "Senin",
				"Tuesday"   => "Selasa",
				"Wednesday" => "Rabu",
				"Thursday"  => "Kamis",
				"Friday"    => "Jumat",
				"Saturday"  => "Sabtu"
			);

			// Pecah tanggal
			$pecah = explode("-", $tanggal); // [0]=YYYY, [1]=MM, [2]=DD
			$tahun  = (int)$pecah[0];
			$bulan  = (int)$pecah[1];
			$hari   = (int)$pecah[2];

			// Dapatkan nama hari dari strtotime
			$dayName = date("l", strtotime($tanggal));
			$hariNama = $hariIndo[$dayName];

			// Konversi ke terbilang
			$hariTerbilang   = trim(terbilang($hari));
			$tahunTerbilang  = trim(terbilang($tahun));

			// Susun kalimat
			return $hariNama . " tanggal " . $hariTerbilang . " bulan " . $bulanIndo[$bulan] . " tahun " . $tahunTerbilang;
		}




	default:
		break;
}
