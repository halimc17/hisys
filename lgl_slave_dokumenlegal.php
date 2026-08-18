<?php
//ini_set('display_errors',0);
//error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method 		= checkPostGet('method', '');
$pt 			= checkPostGet('pt', '');
$jenis 			= checkPostGet('jenis', '');
$kodeijin 		= checkPostGet('kodeijin', '');
$noijin 		= checkPostGet('noijin', '');
$tanggal 		= tanggalsystem(checkPostGet('tanggal', ''));
$tanggalsampai 	= tanggalsystem(checkPostGet('tanggalsampai', ''));
$dikeluarkan   	= checkPostGet('dikeluarkan', '');
$kedudukan 	   	= checkPostGet('kedudukan', '');
$jenisusaha    	= checkPostGet('jenisusaha', '');
$penanggungjawab = checkPostGet('penanggungjawab', '');
$tgldaftarulang  = tanggalsystem(checkPostGet('tgldaftarulang', ''));
$tgljatuhtempo  = tanggalsystem(checkPostGet('tgljatuhtempo', ''));
$keterangan 	= checkPostGet('keterangan', '');
$namafile 		= checkPostGet('namafile', '');
$tipe 			= checkPostGet('tipe', '');
$divsch 		= checkPostGet('divsch', '');
$namaijinsrc 	= checkPostGet('namaijinsrc', '');
$noijinsrc 		= checkPostGet('noijinsrc', '');
$tglsdsrc	 	= tanggalsystem(checkPostGet('tglsdsrc', ''));
$tglakhirsrc 	= tanggalsystem(checkPostGet('tglakhirsrc', ''));
$dikeluarkansrc = checkPostGet('dikeluarkansrc', '');
$kedudukansrc 	= checkPostGet('kedudukansrc', '');
$kegusahasrc 	= checkPostGet('kegusahasrc', '');
$tggjwbsrc 		= checkPostGet('tggjwbsrc', '');
$ketsrc 		= checkPostGet('ketsrc', '');

$arrmilik = array("0" => "sewa/kontrak", "1" => "milik sendiri");
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmijin = makeOption($dbname, 'legal_5nama', 'kodeijin,namaijin');

$path	= "fileupload/lgl_dokumenlegal/";
$today = date('Y-m-d');
$todayhis = date('Y-m-d h:i:s');

$nmjenis = makeOption($dbname, 'legal_5kategoriijin', 'kodekategori,namakategori');
switch ($method) {
	case 'html':
		$tab = "<img src=images/excel.jpg class=resicon  title='Excel' onclick=\"viewexcel('" . $pt . "','excel');\">";
		$tab .= "<table>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>" . $_SESSION['lang']['pt'] . "</td>";
		$tab .= "<td>:</td>";
		$tab .= "<td>" . $pt . " " . $nmorg[$pt] . "</td>";
		$tab .= "</tr></table>";

		$tab .= "";
		if ($tipe == 'html') {
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
		} else {
			$tab .= "<table cellpadding=1 cellspacing=1 border=1>";
		}
		$tab .= "<thead><tr class=rowheader>";
		$tab .= "<td align=center style=\"width:30px;\">" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center style=\"width:100px;\">" . $_SESSION['lang']['jenis'] . "</td>
			<td align=center style=\"width:100px;\">" . $_SESSION['lang']['namaperijinan'] . "</td>
			<td align=center style=\"width:150px;\">" . $_SESSION['lang']['nomorperijinan'] . "</td>
			<td align=center style=\"width:75px;\">" . $_SESSION['lang']['tglditerbitkan'] . "</td>
			<td align=center style=\"width:75px;\">" . $_SESSION['lang']['tglberakhir'] . "</td>
			<td align=center width=100px>" . $_SESSION['lang']['dikeluarkan'] . "</td>
			<td align=center style=\"width:130px;\">" . $_SESSION['lang']['kedudukan'] . "</td>
			<td align=center width=150px>" . $_SESSION['lang']['kegiatanusaha'] . "</td>
			<td align=center width=120px>" . $_SESSION['lang']['penanggungjawab'] . "</td>
			<td align=center width=150px>" . $_SESSION['lang']['keterangan'] . "</td>
			<td align=center width=75px>" . $_SESSION['lang']['tgldaftarulang'] . "</td>
			<td align=center width=75px>" . $_SESSION['lang']['tgljatuhtempo'] . "</td>

        </tr>
		</thead>";
		$no = 0;
		$str = "select * from " . $dbname . ".lgl_dokumenlegal where kodept='" . $pt . "' order by jenis asc, kodeijin asc, noijin asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row = $res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$lf = $xx = '';
		while ($bar = $res->fetch()) {
			$lf = " onclick=\"viewlistfile('" . $bar['kodept'] . "','" . $bar['jenis'] . "','" . $bar['kodeijin'] . "','" . $bar['noijin'] . "')\" valign=top style=cursor:pointer";
			$no += 1;
			$a = $no % 2;
			$xx = '';
			if ($a == 1) {
				$xx .= " style=background-color:#F5EEF8 ";
			}
			$tab .= "<tr " . $xx . " class=rowcontent style=cursor:pointer>";
			$tab .= "<td " . $lf . " align=center>" . $no . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $nmjenis[$bar['jenis']] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $nmijin[$bar['kodeijin']] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['noijin'] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td " . $lf . " align=left>" . tanggalnormal($bar['tanggalsampai']) . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['dikeluarkan'] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['kedudukan'] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['jenisusaha'] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['penanggungjawab'] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['keterangan'] . "</td>";
			$tab .= "<td " . $lf . " align=center>" . tanggalnormal($bar['tgldaftarulang']) . "</td>";
			$tab .= "<td " . $lf . " align=center>" . tanggalnormal($bar['tgljatuhtempo']) . "</td>";
		}
		$tab .= "</tr>";

		$tab .= "</table>";


		if ($tipe == 'html') {
			echo $tab;
		} else {
			$stream = $tab;
			$nop_ = $pt;
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
								parent.window.alert('Cant convert to excel format');
								</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
				}
				closedir($handle);
			}
		}
		break;

	case 'getjenispt':
		$str = " select * from " . $dbname . ".lgl_anggarandasarht where  kodept='" . $pt . "'"; //exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();

		echo $bar['jenispt'];

		break;

	case 'getnama':
		$optijin = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "SELECT * FROM " . $dbname . ".legal_5nama where kodekategori='" . $jenis . "' order by namaijin";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optijin .= "<option value=" . $bar['kodeijin'] . ">" . $bar['namaijin'] . "</option>";
		}

		echo $optijin;
		break;


	case 'detail':
		OPEN_BOX();
		$optjenis = $optijin = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

		$str = "SELECT * FROM " . $dbname . ".legal_5kategoriijin order by kodekategori";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optjenis .= "<option value=" . $bar['kodekategori'] . ">" . $bar['namakategori'] . "</option>";
		}

		$str = "SELECT * FROM " . $dbname . ".legal_5nama order by namaijin";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optijin .= "<option value=" . $bar['kodeijin'] . ">" . $bar['namaijin'] . "</option>";
		}

		# === input dan list ===
		echo "<fieldset>
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
        <table border=0 cellpadding=5 cellspacing=1 class=sortable width=100%>
        <thead><tr class=rowheader>
			<td align=center style=\"width:30px;\">" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center style=\"width:100px;\">" . $_SESSION['lang']['jenis'] . "</td>
			<td align=center style=\"width:100px;\">" . $_SESSION['lang']['namaperijinan'] . "</td>
			<td align=center style=\"width:150px;\">" . $_SESSION['lang']['nomorperijinan'] . "</td>
			<td align=center style=\"width:75px;\">" . $_SESSION['lang']['tglditerbitkan'] . "</td>
			<td align=center style=\"width:75px;\">" . $_SESSION['lang']['tglberakhir'] . "</td>
			<td align=center width=100px>" . $_SESSION['lang']['dikeluarkan'] . "</td>
			<td align=center style=\"width:130px;\">" . $_SESSION['lang']['kedudukan'] . "</td>
			<td align=center width=150px>" . $_SESSION['lang']['kegiatanusaha'] . "</td>
			<td align=center width=120px>" . $_SESSION['lang']['penanggungjawab'] . "</td>
			<td align=center width=150px>" . $_SESSION['lang']['keterangan'] . "</td>
			<td align=center width=75px>" . $_SESSION['lang']['tgldaftarulang'] . "</td>
			<td align=center width=75px>" . $_SESSION['lang']['tgljatuhtempo'] . "</td>
			<td align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
        </tr>
		</thead>
		
        <tr class=rowcontent>
            <td></td>
            <td><select id=jenis style=\"width:100%;\" onchange='getnama()'>" . $optjenis . "</select></td>
            <td><select id=kodeijin style=\"width:100%;\">" . $optijin . "</select></td>
            <td><input id=noijin class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:97%;\"></td>
            <td><input type='text' style='width:97%;' class='myinputtext' id='tanggal' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
            <td><input type='text' style='width:97%;' class='myinputtext' id='tanggalsampai' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
			<td><input id=dikeluarkan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:97%;\"></td>
			<td><input id=kedudukan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:97%;\"></td>
			<td><input id=jenisusaha class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:97%;\"></td>
			<td><input id=penanggungjawab class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:97%;\"></td>
			<td><input id=keterangan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:97%;\"></td>
			<td><input type='text' style='width:97%;' class='myinputtext' id='tgldaftarulang' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
			<td><input type='text' style='width:97%;' class='myinputtext' id='tgljatuhtempo' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>

			<td align=center><input type=hidden id=method value='insert'>
			<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
			<img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetail()\" src='images/clear.png'/>
			<img title='Refresh List Data' class=zImgBtn onclick=\"loaddatadetail()\" src='images/refresh2.png'/>
            </td>
        </tr>
		<tbody id=loaddatadetail>
		</tbody>
		</table>
        </fieldset>";
		# ======= end list ======
		CLOSE_BOX();
		break;

	case 'loaddatadetail':
		$tab = "";
		$no = 0;
		$str = "select * from " . $dbname . ".lgl_dokumenlegal where kodept='" . $pt . "' order by jenis asc, kodeijin asc, noijin asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row = $res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$lf = $xx = '';
		while ($bar = $res->fetch()) {
			$lf = " onclick=\"viewlistfile('" . $bar['kodept'] . "','" . $bar['jenis'] . "','" . $bar['kodeijin'] . "','" . $bar['noijin'] . "')\" valign=top style=cursor:pointer";
			$no += 1;
			$a = $no % 2;
			$xx = '';
			if ($a == 1) {
				$xx .= " style=background-color:#F5EEF8 ";
			}

			$whr1 = "kodekategori='" . $bar['jenis'] . "'";
			$nmKat = makeOption($dbname, 'legal_5kategoriijin', 'kodekategori,namakategori', $whr1);
			$tab .= "<tr " . $xx . " class=rowcontent style=cursor:pointer>";
			$tab .= "<td " . $lf . " align=center>" . $no . "</td>";
			$tab .= "<td " . $lf . " align=left>" . @$nmKat[$bar['jenis']] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . @$nmijin[$bar['kodeijin']] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['noijin'] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td " . $lf . " align=left>" . tanggalnormal($bar['tanggalsampai']) . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['dikeluarkan'] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['kedudukan'] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['jenisusaha'] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['penanggungjawab'] . "</td>";
			$tab .= "<td " . $lf . " align=left>" . $bar['keterangan'] . "</td>";
			$tab .= "<td " . $lf . " align=center>" . tanggalnormal($bar['tgldaftarulang']) . "</td>";
			$tab .= "<td " . $lf . " align=center>" . tanggalnormal($bar['tgljatuhtempo']) . "</td>";

			$tab .= "<td valign=top align=center width=50px>";
			$tab .= "<img src=images/application/application_edit.png class=resicon  title='Edit' 
				onclick=\"editdetail('" . $pt . "','" . $bar['jenis'] . "','" . $bar['kodeijin'] . "','" . $bar['noijin'] . "','" . tanggalnormal($bar['tanggal']) . "','" . tanggalnormal($bar['tanggalsampai']) . "','" . $bar['dikeluarkan'] . "','" . $bar['kedudukan'] . "','" . $bar['jenisusaha'] . "','" . $bar['penanggungjawab'] . "','" . $bar['keterangan'] . "','" . tanggalnormal($bar['tgldaftarulang']) . "','" . tanggalnormal($bar['tgljatuhtempo']) . "');\">&nbsp;";

			$tab .= "<img src=images/application/application_delete.png class=resicon  title='Delete' 
				onclick=\"deletedetail('" . $pt . "','" . $bar['jenis'] . "','" . $bar['kodeijin'] . "','" . $bar['noijin'] . "');\">&nbsp;";

			$tab .= "<img title='" . $_SESSION['lang']['upload'] . "' class=zImgBtn onclick=\"showupload(event,'" . $pt . "','" . $bar['jenis'] . "','" . $bar['kodeijin'] . "','" . $bar['noijin'] . "')\" src='images/upload-2-xxl.png'/>";
			$tab .= "</td>";
		}
		$tab .= "</tr>";
		$tab .= "</table>";


		echo $tab;
		break;
	case 'viewlistfile':
		$tab .= "<fieldset>
				<legend>" . $_SESSION['lang']['list'] . "</legend>
				<table class='sortable' cellspacing='1' border='0' style=min-width:350px>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='loadfilesdetail'>
					</tbody>
				</table>
			</fieldset> ";
		echo $tab;
		break;

	case 'insert':
		#cek data
		$sql = "select * from " . $dbname . ".lgl_dokumenlegal where kodept='" . $pt . "' and jenis='" . $jenis . "' and kodeijin='" . $kodeijin . "' and noijin='" . $noijin . "'";
		$res = fetchData($sql);
		if (count($res) > 0) {
			exit('Error : Data sudah ada !');
		}

		# Jika data sudah ada maka langsung Insert
		$str = "insert into " . $dbname . ".lgl_dokumenlegal (`kodept`,`jenis`, `kodeijin`,`noijin`,`tanggal`,`tanggalsampai`,`tgldaftarulang`,`tgljatuhtempo`,`dikeluarkan`,`kedudukan`,`penanggungjawab`,`jenisusaha`,`keterangan`,`createby`,`createtime`,`updateby`)
        values ('" . $pt . "','" . $jenis . "','" . $kodeijin . "','" . $noijin . "','" . $tanggal . "','" . $tanggalsampai . "','" . $tgldaftarulang . "','" . $tgljatuhtempo . "','" . $dikeluarkan . "','" . $kedudukan . "','" . $penanggungjawab . "','" . $jenisusaha . "','" . $keterangan . "','" . $_SESSION['standard']['userid'] . "','" . $todayhis . "','" . $_SESSION['standard']['userid'] . "')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'update':
		$str = "update " . $dbname . ".lgl_dokumenlegal set tanggal='" . $tanggal . "', tanggalsampai='" . $tanggalsampai . "', tgldaftarulang='" . $tgldaftarulang . "',tgljatuhtempo='" . $tgljatuhtempo . "' ,  dikeluarkan='" . $dikeluarkan . "', kedudukan='" . $kedudukan . "', penanggungjawab='" . $penanggungjawab . "',jenisusaha='" . $jenisusaha . "',keterangan='" . $keterangan . "', updateby='" . $_SESSION['standard']['userid'] . "' where kodept='" . $pt . "' and jenis='" . $jenis . "' and kodeijin='" . $kodeijin . "' and noijin='" . $noijin . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'deletedetail':
		$str = "delete from " . $dbname . ".lgl_dokumenlegal where kodept='" . $pt . "' and jenis='" . $jenis . "' and kodeijin='" . $kodeijin . "' and noijin='" . $noijin . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}


		# delete file
		$sql = "select * from " . $dbname . ".listfile_lgl_dokumenlegal where kodept='" . $pt . "' and jenis='" . $jenis . "' and kodeijin='" . $kodeijin . "' and noijin='" . $noijin . "'"; //exit('error'.$sql);
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$str = "delete from " . $dbname . ".listfile_lgl_dokumenlegal where kodept='" . $pt . "' and jenis='" . $jenis . "' and kodeijin='" . $kodeijin . "' and noijin='" . $noijin . "' and namafile='" . $bar['namafile'] . "'";
			try {
				$owlPDO->exec($str);
				$pathx = $path . $bar['namafile'];
				unlink($pathx);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		break;

	case 'delete':
		$str = "delete from " . $dbname . ".lgl_anggarandasarht where kodept='" . $pt . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'loaddata':
		$where = "";
		if ($divsch != '') {
			$where .= " and kodept='" . $divsch . "' ";
		}
		if ($namaijinsrc != '') {
			$where .= " and kodeijin = '" . $namaijinsrc . "'";
		}
		if ($noijinsrc != '') {
			$where .= " and noijin like '%" . $noijinsrc . "%' ";
		}
		if ($tglsdsrc != '') {
			$where .= " and tanggal like '%" . $tglsdsrc . "%' ";
		}
		if ($tglakhirsrc != '') {
			$where .= " and tanggalsampai like '%" . $tglakhirsrc . "%' ";
		}
		if ($dikeluarkansrc != '') {
			$where .= " and dikeluarkan like '%" . $dikeluarkansrc . "%' ";
		}
		if ($kedudukansrc != '') {
			$where .= " and kedudukan like '%" . $kedudukansrc . "%' ";
		}
		if ($kegusahasrc != '') {
			$where .= " and jenisusaha like '%" . $kegusahasrc . "%' ";
		}
		if ($tggjwbsrc != '') {
			$where .= " and penanggungjawab like '%" . $tggjwbsrc . "%' ";
		}
		if ($ketsrc != '') {
			$where .= " and keterangan like '%" . $ketsrc . "%' ";
		}

		$limit = 20;
		$page = 0;
		$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}

		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);

		$sql = "SELECT *
		FROM " . $dbname . ".lgl_dokumenlegal
		where 1=1 " . $where . " order by kodept asc";
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$jlhbrs = owlBaris($res);
		$no = 0;

		$str = "SELECT *
		FROM " . $dbname . ".lgl_dokumenlegal 
		where 1=1 " . $where . " order by kodept asc limit " . $offset . "," . $limit . ""; //exit('error'.$str);
		$tab = "";
		$no = $maxdisplay;

		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row = $res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if (empty($row)) {
			$tab .= "<tr class=rowcontent><td colspan=17 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			while ($bar = $res->fetch()) {
				$isi = '';
				$no += 1;
				$tab .= "<tr class=rowcontent  id=tr_$no>";
				$tab .= "<td align=center>" . $no . "</td>";
				$tab .= "<td>" . $bar['kodept'] . " - " . $nmorg[$bar['kodept']] . "</td>";
				$tab .= "<td align=left>" . $nmjenis[$bar['jenis']] . "</td>";
				$tab .= "<td align=left>" . $nmijin[$bar['kodeijin']] . "</td>";
				$tab .= "<td align=left>" . $bar['noijin'] . "</td>";
				$tab .= "<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>";
				$tab .= "<td align=left>" . tanggalnormal($bar['tanggalsampai']) . "</td>";
				$tab .= "<td align=left>" . $bar['dikeluarkan'] . "</td>";
				$tab .= "<td align=left>" . $bar['kedudukan'] . "</td>";
				$tab .= "<td align=left>" . $bar['jenisusaha'] . "</td>";
				$tab .= "<td align=left>" . $bar['penanggungjawab'] . "</td>";
				$tab .= "<td align=left>" . $bar['keterangan'] . "</td>";
				$tab .= "<td align=center>" . tanggalnormal($bar['tgldaftarulang']) . "</td>";
				$tab .= "<td align=center>" . tanggalnormal($bar['tgljatuhtempo']) . "</td>";
				$tab .= "<td align=left>" . $nmkar[$bar['updateby']] . "</td>";

				$isi .= "<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
					onclick=\"edit('" . $bar['kodept'] . "');\" ></td>";

				$isi .= "<td align=center><img class=resicon src=images/application/application_delete.png onclick=\"deletedetail('" . $bar['kodept'] . "','" . $bar['jenis'] . "','" . $bar['kodeijin'] . "','" . $bar['noijin'] . "');\" title='Delete'></td>";
				$isi .= "<td align=center><img src=images/zoom.png class=resicon  title='View' onclick=\"html('" . $bar['kodept'] . "','html');\"></td>";
				$tab .= $isi;
				$tab .= "</tr>";
			}
		}
		$totrows = ceil($jlhbrs / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}
		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
		}
		$footd = "";
		$footd .= "</tr>
                     <tr><td colspan=18 align=center>";

		if ($page == '0') {
			$footd .= "<button class=mybutton disabled=true>Prev</button>";
		} else {
			$footd .= "<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
		}

		$footd .= "<select id=\"pages\" name=\"pages\" onchange=\"getPage()\">" . $isiRow . "</select>";

		if (($page + 1) == $totrows) {
			$footd .= "<button class=mybutton disabled=true>Next</button>";
		} else {
			$footd .= "<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
		}
		$footd .= "</td>
            </tr>";



		echo $tab . "####" . $footd;

		break;

	case 'showupload':
		$tab = "";
		$tab .= "<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
		$tab .= "<tr>
				<td>" . $_SESSION['lang']['pt'] . "</td>
				<td>:</td>
				<td>
					<label id='ptupload' style='display:none'>" . $pt . "</label>
					<label style='font-weight:bold'>" . $nmorg[$pt] . "</label>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['jenis'] . "</td>
				<td>:</td>
				<td>
					<label id='xxx' style='font-weight:bold'>" . $jenis . "</label>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['namaperijinan'] . "</td>
				<td>:</td>
				<td>
					<label id='yyy' style='display:none'>" . $kodeijin . "</label>
					<label style='font-weight:bold'>" . $nmijin[$kodeijin] . "</label>
				</td>
			</tr>";
		$tab .= "<tr>
				<td>" . $_SESSION['lang']['nomorperijinan'] . "</td>
				<td>:</td>
				<td>
					<label id='iii' style='font-weight:bold'>" . $noijin . "</label>
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
					$maxBytes = 10 * 1024 * 1024;
					if ($_FILES['file']['size'] <= $maxBytes) {
						$str = "insert into " . $dbname . ".listfile_lgl_dokumenlegal values ('','" . $pt . "','" . $jenis . "','" . $kodeijin . "','" . $noijin . "','" . $filename . "','" . $filetype . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
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
						exit("warning : Ukuran file upload maksimal 10 MB ");
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
		$str = "select * from " . $dbname . ".listfile_lgl_dokumenlegal where kodept = '" . $pt . "' and status='1' and jenis='" . $jenis . "' and kodeijin='" . $kodeijin . "' and noijin='" . $noijin . "'";
		//exit('error'.$str);
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

				$tab .= "<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $val['jenis'] . "','" . $val['kodept'] . "','" . $val['kodeijin'] . "','" . $val['noijin'] . "','" . $val['namafile'] . "');\" >";

				$tab . "	</td>
				</tr>";
			}
		}

		echo $tab;
		break;
	case 'viewfile':
		$tab = "";
		$tab .= "<img src='" . $path . $namafile . "' style='width:600px;height:400px;'>";

		echo $tab;
		break;

	case 'deletefile':
		$str = "delete from " . $dbname . ".listfile_lgl_dokumenlegal where kodept='" . $pt . "' and jenis='" . $jenis . "' and kodeijin='" . $kodeijin . "' and noijin='" . $noijin . "'and namafile='" . $namafile . "'"; //exit('error'.$str);
		try {
			$owlPDO->exec($str);
			$pathx = $path . $namafile;
			unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;
}
