<?php
//ini_set('display_errors',0);
//error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
$method      = checkPostGet('method', '');
$pt          = checkPostGet('pt', '');
$unit        = checkPostGet('unit', '');
$notransaksi = checkPostGet('notransaksi', '');
$tanggalsurat= tanggalsystemn(checkPostGet('tanggalsurat', ''));
$tanggalkirim= tanggalsystemn(checkPostGet('tanggalkirim', ''));
$intex       = checkPostGet('intex', '');
$departement = checkPostGet('departement', '');
$tujuan      = checkPostGet('tujuan', '');
$lokasi      = checkPostGet('lokasi', '');
$ringkasan   = trim(checkPostGet('ringkasan', ''));
$namafile    = checkPostGet('namafile', '');
$tipe        = checkPostGet('tipe', '');
$divsch      = checkPostGet('divsch', '');
$jenissch    = checkPostGet('jenissch', '');
$nohaksch    = checkPostGet('nohaksch', '');
$unitsch     = checkPostGet('unitsch', '');
$ringkasansch= checkPostGet('ringkasansch', '');
$nmorg       = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar       = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmdept      = makeOption($dbname, 'sdm_5departemen', 'kode,nama');
$arrintex    = array('INT'=> 'INTERNAL', 'EXT'=> 'EXTERNAL');
$arrtipe	 = array('1' => 'MASUK', '0' => 'KELUAR');
$ringkasan   = nl2br($ringkasan);
$eee         = explode('<br />', $ringkasan);
$no          = '';
foreach($eee as $ggg => $hhh) {
	$no += 1;
	if ($no < count($eee)) {
		 @ $ghjkl.= trim($hhh)."####";
	} else {
		 @ $ghjkl.=trim($hhh);
	}
}
$ringkasan= $ghjkl;
$path     = "fileupload/lgl_surat/";
$today    = date('Y-m-d');
$todayhis = date('Y-m-d H:i:s');
switch ($method) {
case 'getnotransaksi':
	 # 001 / INT / LGL / BOD / BJHO / IX / 2017
	 # 001 / EXT / LGL / BOD / BJHO / IX / 2017
	 # 001a / EXT / LGL / BOD / BJHO / IX / 2017
	 # 001b / EXT / LGL / BOD / BJHO / IX / 2017
	$tempPrd = explode('-', $tanggalsurat);
	$abj2num = array('a' => '1', 'b' => '2', 'c' => '3', 'd' => '4', 'e' => '5', 'f' => '6', 'g' => '7', 'h' => '8', 'i' => '9', 'j' => '10', 'k' => '11', 'l' => '12', 'm' => '13', 'n' => '14', 'o' => '15', 'p' => '16', 'q' => '17', 'r' => '18', 's' => '19', 't' => '20', 'u' => '21', 'v' => '22', 'w' => '23', 'x' => '24', 'y' => '25', 'z' => '26');
	$num2abj = array('1' => 'a', '2' => 'b', '3' => 'c', '4' => 'd', '5' => 'e', '6' => 'f', '7' => 'g', '8' => 'h', '9' => 'i', '10' => 'j', '11' => 'k', '12' => 'l', '13' => 'm', '14' => 'n', '15' => 'o', '16' => 'p', '17' => 'q', '18' => 'r', '19' => 's', '20' => 't', '21' => 'u', '22' => 'v', '23' => 'w', '24' => 'x', '25' => 'y', '26' => 'z');
	$str = " select notransaksi from ".$dbname.".lgl_surat where tipe='".$tipe."' and intex='".$intex."' and tanggalsurat like '".$tempPrd[0]."%' order by notransaksi desc limit 1 "; //exit('error'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$tempNo1 = explode('/', $bar['notransaksi']);
	if ($tanggalsurat < $today) {
		 # Jika tanggal surat lebih kecil dari tgl HI
		$strx = " select notransaksi from ".$dbname.".lgl_surat where tipe='".$tipe."' and intex='".$intex."' and tanggalsurat <= '".$tanggalsurat."' order by notransaksi desc limit 1 ";
		$resx = $owlPDO->query($strx)or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$barx = $resx->fetch();
		$tempNo = explode('/', $barx['notransaksi']);
		if (strlen($tempNo[0]) == 4) {
			 # Jika nomor 4 digit == 001a maka no baru menjadi 001b
			$abj = substr($tempNo[0], -1);
			$newabj = $abj2num[$abj] + 1;
			$nomorsurat = substr($tempNo[0], 0, 3).$num2abj[$newabj];
		} else if (intval(substr($tempNo[0], 0, 3)) == 0 and strlen($tempNo1[0]) == 3) {
			$nomorsurat = addZero(intval($tempNo1[0]), 3)."a";
		} else if (intval(substr($tempNo[0], 0, 3)) == 0 and strlen($tempNo1[0]) == 4) {
			$abj = substr($tempNo1[0], -1);
			$newabj = $abj2num[$abj] + 1;
			$nomorsurat = substr($tempNo1[0], 0, 3).$num2abj[$newabj];
		} else {
			if(substr($tempNo[0], 0, 3)==''){
				$nomorsurat = "001a";
			}else{
				$nomorsurat = substr($tempNo[0], 0, 3)."a";
			}
		}
	} else {
		if (intval($bar['notransaksi']) == 0 or intval($bar['notransaksi']) == 999) {
			$nomorsurat = "001";
		} else {
			$nomorsurat = addZero(intval($tempNo1[0]) + 1, 3);
		}
	}
	echo $nomorsurat."/".$intex."/".$departement."/".$pt."/".$unit."/".romawi($tempPrd[1])."/".$tempPrd[0];
	break;
case 'getunit':
	$optun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4 order by namaorganisasi asc ";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$optun.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}
	echo $optun;
	break;
case 'html':
	$tab = "<img src=images/excel.jpg class=resicon  title='Excel' onclick=\"viewexcel('".$pt."','".$unit."','".$notransaksi."','excel');\">";
	$no = 0;
	$str = "select * from ".$dbname.".lgl_surat where notransaksi='".$notransaksi."'";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	if ($tipe == 'html') {
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
	} else {
		$tab.="<table cellpadding=1 cellspacing=1 border=1>";
	}
	$tab.="<tr class=rowcontent>
		<td>Nomor Surat</td>
		<td>:</td>
		<td colspan=4>".$bar['notransaksi']."</td>
		<td>Jenis Surat</td>
		<td>:</td>
		<td>".$arrintex[$bar['intex']]."</td>
		<td>Tanggal Surat</td>
		<td>:</td>
		<td>".$bar['tanggalsurat']."</td>
		</tr>
		<tr class=rowcontent>
		<td>".$_SESSION['lang']['pt']."</td>
		<td>:</td>
		<td colspan=4>".$pt." ".$nmorg[$pt]."</td>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td>".$unit." ".$nmorg[$unit]."</td>
		<td>Tanggal Kirim</td>
		<td>:</td>
		<td>".$bar['tanggalkirim']."</td>
		</tr>
		<tr class=rowcontent>
		<td>".$_SESSION['lang']['lokasi']."</td>
		<td>:</td>
		<td colspan=4 >".$bar['lokasi']."</td>
		<td>".$_SESSION['lang']['departemen']."</td>
		<td>:</td>
		<td>".$nmdept[$bar['departement']]."</td>
		<td>Tujuan</td>
		<td>:</td>
		<td>".$bar['tujuan']."</td>
		</tr>
		<tr class=rowcontent>
		<td valign=top>Ringkasan</td>
		<td valign=top>:</td>
		<td colspan=10>".str_replace('####', '<br>', $bar['ringkasan'])."</td>
		</tr>
		</table>";
	if ($tipe == 'html') {
		echo $tab;
		echo @ $isi.="<hr><table class='sortable' cellspacing='1' border='0' style=min-width:100%>
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
			</table>";
	} else {
		$stream = $tab;
		$nop_ = "surat_menurat_";
		if (strlen($stream) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						 @ unlink('tempExcel/'.$file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/".$nop_.".xls", 'w');
			if (!fwrite($handle, $stream)) {
				echo "<script language=javascript1.2>
				parent.window.alert('Cant convert to excel format');
				</script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			closedir($handle);
		}
	}
	break;
case 'viewlistfile':
	$tab.="<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
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
	 # cek data
	$sql = "select * from ".$dbname.".lgl_surat where kodept='".$pt."' and unit='".$unit."' and tipe='".$tipe."' and notransaksi='".$notransaksi."'";
	$res = fetchData($sql);
	if (count($res) > 0) {
		exit('Warning : Data sudah ada !');
	}
	 # Jika data sudah ada maka langsung Insert
	$str = "insert into ".$dbname.".lgl_surat (`notransaksi`,`kodept`,`unit`,`tipe`,`intex`,`lokasi`,`departement`,`tujuan`,`ringkasan`,`tanggalsurat`,`tanggalkirim`,`createby`,`createtime`,`updateby`)
		values ('".$notransaksi."','".$pt."','".$unit."','".$tipe."','".$intex."','".$lokasi."','".$departement."','".$tujuan."','".$ringkasan."','".$tanggalsurat."','".$tanggalkirim."','".$_SESSION['standard']['userid']."','".$todayhis."','".$_SESSION['standard']['userid']."')"; //EXIT('error'.$str);
	try {
		$owlPDO->beginTransaction();
		$owlPDO->exec($str);
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'update':
	$str = "update ".$dbname.".lgl_surat set lokasi='".$lokasi."',departement='".$departement."',tujuan='".$tujuan."',ringkasan='".$ringkasan."',updateby='".$_SESSION['standard']['userid']."',updatetime='".$todayhis."'
		where kodept='".$pt."' and unit='".$unit."' and tipe='".$tipe."' and notransaksi='".$notransaksi."'";
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'delete':
	$str = "delete from ".$dbname.".lgl_surat where kodept='".$pt."' and notransaksi='".$notransaksi."' and unit='".$unit."' and tipe='".$tipe."'";
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	 # delete file
	$sql = "select * from ".$dbname.".listfile_lgl_surat where notransaksi='".$notransaksi."'";
	$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$str = "delete from ".$dbname.".listfile_lgl_surat where notransaksi='".$notransaksi."'and namafile='".$bar['namafile']."'";
		try {
			$owlPDO->exec($str);
			$pathx = $path.$bar['namafile'];
			unlink($pathx);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	}
	break;
case 'loaddata':
	$where = "";
	if ($divsch != '') {
		$where.=" and kodept='".$divsch."' ";
	}
	if ($jenissch != '') {
		$where.=" and intex='".$jenissch."' ";
	}
	if ($unitsch != '') {
		$where.=" and unit='".$unitsch."' ";
	}
	if ($nohaksch != '') {
		$where.=" and notransaksi like '%".$nohaksch."%' ";
	}
	if ($ringkasansch != '') {
		$where.=" and ringkasan like '%".$ringkasansch."%' ";
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
	$sql = "SELECT * FROM ".$dbname.".lgl_surat where 1=1 ".$where." order by kodept asc";
	$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$jlhbrs = $res->rowCount();

	$no = 0;
	$str = "SELECT * FROM ".$dbname.".lgl_surat  where 1=1 ".$where." order by updatetime desc limit ".$offset.",".$limit."";
	$tab = "";
	$no = $maxdisplay;
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$row = $res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if (empty($row)) {
		$tab.="<tr class=rowcontent><td colspan=15 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	} else {
		while ($bar = $res->fetch()) {
			$isi = '';
			$no += 1;
			$a = $no % 2;
			$xx = '';
			if ($a == 1) {
				$xx.=" style=background-color:#F5EEF8 ";
			}
			$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$bar['kodept']." - ".$nmorg[$bar['kodept']]."</td>";
			$tab.="<td>".$bar['unit']." - ".$nmorg[$bar['unit']]."</td>";
			$tab.="<td>".$bar['departement']."</td>";
			$tab.="<td>".$bar['notransaksi']."</td>";
			$tab.="<td>".$arrtipe[$bar['tipe']]."</td>";
			$tab.="<td>".$bar['lokasi']."</td>";
			$tab.="<td>".$bar['tujuan']."</td>";
			$tab.="<td>".$bar['tanggalsurat']."</td>";
			$tab.="<td>".$bar['tanggalkirim']."</td>";
			$tab.="<td align=left>".$nmkar[$bar['updateby']]."</td>";
			$isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit'
				onclick=\"edit('".$bar['notransaksi']."','".$bar['kodept']."','".$bar['unit']."','".$bar['tipe']."','".$bar['intex']."','".$bar['lokasi']."','".$bar['departement']."','".$bar['tujuan']."','".str_replace('####', '\n', $bar['ringkasan'])."','".tanggalnormal($bar['tanggalsurat'])."','".tanggalnormal($bar['tanggalkirim'])."');\" ></td>";
			$isi.="<td align=center><img class=resicon src=images/application/application_delete.png onclick=\"del('".$bar['kodept']."','".$bar['unit']."','".$bar['notransaksi']."');\" title='Delete'></td>";
			$isi.="<td align=center><img src=images/zoom.png class=resicon  title='View' onclick=\"html('".$bar['kodept']."','".$bar['unit']."','".$bar['notransaksi']."','html');\"></td>";
			$tab.=$isi;
			$tab.="</tr>";
		}
	}
	$totrows = ceil($jlhbrs / $limit);
	if ($totrows == 0) {
		$totrows = 1;
	}
	$isiRow = '';
	for ($er = 1; $er <= $totrows; $er++) {
		$sel = ($page == $er - 1) ? 'selected' : '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}
	$footd = "";
	$footd.="</tr>
		<tr><td colspan=14 align=center>";
	if ($page == '0') {
		$footd.="<button class=mybutton disabled=true>Prev</button>";
	} else {
		$footd.="<button class=mybutton onclick=loaddata(".($page - 1).");>Prev</button>";
	}
	$footd.="<select id=\"pages\" name=\"pages\" onchange=\"getPage()\">".$isiRow."</select>";
	if (($page + 1) == $totrows) {
		$footd.="<button class=mybutton disabled=true>Next</button>";
	} else {
		$footd.="<button class=mybutton onclick=loaddata(".($page + 1).");>Next</button>";
	}
	$footd.="</td>
		</tr>";
	echo $tab."####".$footd;
	break;
case 'submitfile':
	if ($pt == '' or $unit == '' or $notransaksi == '') {
		exit("Warning : Silahkan isikan detail transaksi terlebih dahulu !");
	}
	 # cek data
	$sql = "select * from ".$dbname.".lgl_surat where kodept='".$pt."' and unit='".$unit."' and notransaksi='".$notransaksi."'";
	$res = fetchData($sql);
	if (count($res) == 0) {
		exit('Warning : Silahkan isikan dan save detail transaksi terlebih dahulu !');
	}
	$str = "select * from ".$dbname.".listfile_lgl_surat where notransaksi = '".$notransaksi."'";
	$res = fetchData($str);
	if (count($res) >= 10) {
		exit("Warning : Limit upload hanya 10 file.");
	}
	$tgl = date("YmdHis");
	$his = date("His");
	$data = $_POST;
	if ($data['fileupload'] != '') {
		if ($_FILES['file']['error'] == 0) {
			$filetype = strtolower('.'.substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
			$filename = $_FILES['file']['name'];
			//$filename = $pt."_".$tgl."".$filetype;
			$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
			if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
				if ($_FILES['file']['size'] <= 250000) {
					$str = "insert into ".$dbname.".listfile_lgl_surat values ('','".$notransaksi."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
					try {
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename, $file_tmpname);
					} catch (PDOException $e) {
						echo " Gagal,".addslashes($e->getMessage());
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
case 'loadfiles':
	$no = 0;
	$tab = "";
	$str = "select * from ".$dbname.".listfile_lgl_surat where notransaksi = '".$notransaksi."' and status='1'";
	$res = fetchData($str);
	if (empty($res)) {
		$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	} else {
		foreach($res as $key => $val) {
			$no++;
			$tab.="<tr class=rowcontent>
				<td style='text-align:center'>".$no."</td>";
			$icon = seticonfile($val['formaticon']);
			$tab.="<td style='text-align:center'>
				<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon title='click to download'></a>
				</td>";
			$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
				<td align=center>
				<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
			$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
			$tab."	</td>
			</tr>";
		}
	}
	echo $tab;
	break;
case 'viewfile':
	$tab = "";
	$tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
	echo $tab;
	break;
case 'deletefile':
	$str = "delete from ".$dbname.".listfile_lgl_surat where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
	try {
		$owlPDO->exec($str);
		$pathx = $path.$namafile;
		unlink($pathx);
	} catch (PDOException $e) {
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
}
?>