<?php
 // ini_set('display_errors',0);
 // error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
	
$method = checkPostGet('method', '');
$tipe = checkPostGet('tipe', '');
$jenisupload = checkPostGet('jenisupload', '');
$pt = checkPostGet('pt', '');
$jenis = checkPostGet('jenis', '');
$jenisakta = checkPostGet('jenisakta', '');
$nomorakta = checkPostGet('nomorakta', '');
$tglakta = tanggalsystem(checkPostGet('tglakta', ''));
$tglaktax = checkPostGet('tgl', '');
$tanggalakta = checkPostGet('tanggalakta', '');
$tglsaham = checkPostGet('tglsaham', '');
$tglsahamlama = checkPostGet('tglsahamlama', '');
$tglkom = checkPostGet('tglkom', '');
$tglkomlama = checkPostGet('tglkomlama', '');
$notaris = checkPostGet('notaris', '');
$noakun = checkPostGet('noakun', '');
$noskhakim = checkPostGet('noskhakim', '');
$tglskhakim = tanggalsystem(checkPostGet('tglskhakim', ''));
$kedudukan = checkPostGet('kedudukan', '');
$alamat = checkPostGet('alamat', '');
$tahun = checkPostGet('tahun', '');
$namasaham = checkPostGet('namasaham', '');
$modaldasar = checkPostGet('modaldasar', '');
$modalsetor = checkPostGet('modalsetor', '');
$saham = checkPostGet('saham', '');
$lembarsaham = checkPostGet('lembarsaham', '');
$nilaisaham = checkPostGet('nilaisaham', '');
$kegusaha = checkPostGet('kegusaha', '');
$bnri = checkPostGet('bnri', '');
$tbnri = checkPostGet('tbnri', '');
$tglbnri = tanggalsystem(checkPostGet('tglbnri', ''));
$keterangan = checkPostGet('keterangan', '');
$noakta = checkPostGet('noakta', '');
$kary = checkPostGet('usr_id', '');
$notransaksi = checkPostGet('notransaksi', '');
$kepada = checkPostGet('kepada', '');
$numrow = checkPostGet('numrow', '');
$sumber = checkPostGet('sumber', '');


@$modaldasar = str_replace(",", "", $modaldasar);
@$modalsetor = str_replace(",", "", $modalsetor);
@$saham = str_replace(",", "", $saham);
@$nilaisaham = str_replace(",", "", $nilaisaham);
@$lembarsaham = str_replace(",", "", $lembarsaham);
$namakom = checkPostGet('namakom', '');
$jabatankom = checkPostGet('jabatankom', '');
$keterangankom = checkPostGet('keterangankom', '');
$xxx = checkPostGet('xxx', '');
$yyy = checkPostGet('yyy', '');
$iii = checkPostGet('iii', '');
$namafile = checkPostGet('namafile', '');
if ($iii == 'undefined') {
	$iii = '';
}
$divsch = checkPostGet('divsch', '');
$arrmilik = array("0" => "sewa/kontrak", "1" => "milik sendiri");
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
 @$tmpTgl = explode('-', $tgl);
$path = "fileupload/lgl_anggarandasar/";
$today = date('Y-m-d');
$todayhis = date('Y-m-d h:i:s');

$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);

switch ($method) {
case 'html':
	

	$kdpt=makeOption($dbname,'lgl_anggarandasardt_akta','nopengajuan,kodept');
	if($sumber=='approval'){
		$ptx=$kdpt[$notransaksi];
		$wh=" and nopengajuan='".$notransaksi."'";
	}else{
		$wh="";
		$ptx=$pt;
	}

	$tab = "<img src=images/excel.jpg class=resicon  title='Excel' onclick=\"viewexcel('".$ptx."','excel');\">";
	$tab.="<table>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td>".$_SESSION['lang']['pt']."</td>";
	$tab.="<td>:</td>";
	$tab.="<td>".$ptx." ".$nmorg[$ptx]."</td>";
	$tab.="</tr></table><hr>";
	$tab.="<fieldset>
		<legend>".$_SESSION['lang']['akta']."</legend>";
	if ($tipe == 'html') {
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
	} else {
		$tab.="<table cellpadding=1 cellspacing=1 border=1>";
	}
	$tab.="<thead><tr class=rowheader>
		<td align=center style=\"width:30px;\">".$_SESSION['lang']['nourut']."</td>
		<td align=center>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['akta']."</td>
		<td align=center>".$_SESSION['lang']['nomor']."</td>
		<td align=center>".$_SESSION['lang']['tanggalakta']."</td>
		<td align=center>".$_SESSION['lang']['namanotaris']."</td>
		<td align=center>".$_SESSION['lang']['noskhakim']."</td>
		<td align=center>".$_SESSION['lang']['tglskhakim']."</td>
		<td align=center>".$_SESSION['lang']['kedudukan']."</td>
		<td align=center>".$_SESSION['lang']['alamat']."</td>
		<td align=center>".$_SESSION['lang']['modaldasar']."</td>
		<td align=center>".$_SESSION['lang']['modalsetor']."</td>
		<td align=center>Kegiatan Usaha</td>
		<td align=center >BNRI</td>
		<td align=center >TBNRI</td>
		<td align=center >".$_SESSION['lang']['tanggal']."</td>
		<td align=center >".$_SESSION['lang']['keterangan']."</td>
		</tr>
		</thead>";
	$no = 0;
	$str = "select distinct * from ".$dbname.".lgl_anggarandasardt_akta where kodept='".$ptx."' ".$wh." order by tanggalakta asc"; //exit("error".$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$row = $res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if ($row == 0) {
		$tab.="<tr class=rowcontent><td colspan=9 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	} else {
		$lf = '';
		while ($bar = $res->fetch()) {
			$lf = " onclick=\"viewlistfile('akta','".$bar['kodept']."','".$bar['jenisakta']."','".$bar['noakta']."')\" valign=top";
			$no += 1;
			$tab.="<tr class=rowcontent style=cursor:pointer>";
			$tab.="<td ".$lf." align=center>".$no."</td>";
			$tab.="<td ".$lf." align=left>".$bar['jenisakta']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['noakta']."</td>";
			$tab.="<td ".$lf." align=center>".$bar['tanggalakta']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['namanotaris']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['noskkehakiman']."</td>";
			$tab.="<td ".$lf." align=center>".$bar['tanggalsk']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['kedudukan']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['alamat']."</td>";
			$tab.="<td ".$lf." align=right>".number_format($bar['modaldasar'])."</td>";
			$tab.="<td ".$lf." align=right>".number_format($bar['modalsetor'])."</td>";
			$tab.="<td ".$lf." align=justify>".$bar['kegusaha']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['bnri']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['tbnri']."</td>";
			$tab.="<td ".$lf." align=left>".tanggalnormal($bar['tglbnri'])."</td>";
			$tab.="<td ".$lf." align=left>".$bar['keterangan']."</td>";
			$noakta=$bar['noakta'];
			
			$whakta[$bar['noakta']]=$bar['noakta'];
			$whtgl[$bar['tanggalakta']]=$bar['tanggalakta'];
		}
	}
	$tab.="</tr>";
	$tab.="</table><hr>";
	$tab.="
		<table style=width:100%><td valign=top style=width:50%>
		<fieldset>
		<legend>".$_SESSION['lang']['saham']."</legend>";
	if ($tipe == 'html') {
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
	} else {
		$tab.="<table cellpadding=1 cellspacing=1 border=1>";
	}
	$tab.="<thead><tr class=rowheader>
		<td align=center style=\"width:30px;\">".$_SESSION['lang']['nourut']."</td>
		<td align=center>".$_SESSION['lang']['nomor']." Akta</td>
		<td align=center>".$_SESSION['lang']['tanggal']." Akta</td>
		<td align=center>".$_SESSION['lang']['nama']."</td>
		
		<td align=center style=\"width:80px;\">Lembar ".$_SESSION['lang']['saham']."</td>
		<td align=center style=\"width:80px;\">Nilai Saham / per Lembar</td>
		<td align=center style=\"width:80px;\">".$_SESSION['lang']['saham']."</td>
		</tr>
		</thead>";
	
	$wheredetail="";
	$wheredetail2="";
	if($sumber=='approval'){
		$wheredetail=" and noakta in ('".implode("','",$whakta)."') and tanggal_akta in ('".implode("','",$whtgl)."')";
		$wheredetail2=" and tahun in ('".implode("','",$whakta)."') and tanggal_akta in ('".implode("','",$whtgl)."')";
	}
	$str = " select distinct * from ".$dbname.".lgl_anggarandasardt_saham where kodept='".$ptx."' ".$wheredetail." order by tanggal_akta asc ";
	
	// $str = "select distinct * from ".$dbname.".lgl_anggarandasardt_saham where kodept='".$pt."' order by tanggal_akta asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$row = $res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if ($row == 0) {
		$tab.="<tr class=rowcontent><td colspan=7 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	} else {
		$no = '';
		while ($bar = $res->fetch()) {
			$lf = '';
			$lf = " onclick=\"viewlistfile('saham','".$bar['kodept']."','".$bar['tahun']."','".$bar['nama']."')\" valign=top";
			$no += 1;
			$tab.="<tr class=rowcontent style=cursor:pointer ".$title.">";
			$tab.="<td ".$lf." align=center>".$no."</td>";
			//$tab.="<td ".$lf." align=center>".$bar['tahun']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['noakta']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['tanggal_akta']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['nama']."</td>";
			
			$tab.="<td ".$lf." align=right>".@hidezerodecimal($bar['lembarsaham'])."</td>";
			$tab.="<td ".$lf." align=right>". @ hidezerodecimal($bar['nilaisaham'])."</td>";
			$tab.="<td ".$lf." align=right>". @ hidezerodecimal($bar['saham'])."</td>";
		}
	}
	$tab.="</tr>";
	$tab.="</table></fieldset>";
	$tab.="
		</td><td></td>
		<td valign=top  style=width:50%>
		<fieldset>
		<legend>".$_SESSION['lang']['susunanpengurusdankomisaris']."</legend>";
	if ($tipe == 'html') {
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
	} else {
		$tab.="<table cellpadding=1 cellspacing=1 border=1>";
	}
	$tab.="
		<thead><tr class=rowheader>
		<td align=center style=\"width:30px;\">".$_SESSION['lang']['nourut']."</td>
		<td align=center>".$_SESSION['lang']['nomor']." Akta</td>
		<td align=center>".$_SESSION['lang']['tanggal']." Akta</td>
		<td align=center>".$_SESSION['lang']['nama']."</td>
		<td align=center>".$_SESSION['lang']['jabatan']."</td>
		<td align=center >".$_SESSION['lang']['keterangan']."</td>
		</tr>
		</thead>";
	$str = "select * from ".$dbname.".lgl_anggarandasardt_komisaris  where kodept='".$ptx."' ".$wheredetail2." order by tanggal_akta asc";

	// $str = "select a.*, a.keterangan as ket, b.tanggalakta as tgl, b.* from ".$dbname.".lgl_anggarandasardt_komisaris a left join ".$dbname.".lgl_anggarandasardt_akta b on a.kodept=b.kodept  
	// where a.kodept='".$pt."' and a.tahun='".$noakta."' 
	// order by a.noakta, b.tanggalakta asc";

	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$row = $res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if ($row == 0) {
		$tab.="<tr class=rowcontent><td colspan=6 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	} else {
		$no = '';
		while ($bar = $res->fetch()) {
			$lf = '';
			$lf = " onclick=\"viewlistfile('kom','".$bar['kodept']."','".$bar['tahun']."','".$bar['nama']."','".$bar['jabatan']."')\" valign=top";
			$no += 1;
			$tab.="<tr class=rowcontent style=cursor:pointer ".$title.">";
			$tab.="<td ".$lf." align=center>".$no."</td>";
			$tab.="<td ".$lf." align=center>".$bar['tahun']."</td>";
			$tab.="<td ".$lf." align=center>".$bar['tanggal_akta']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['nama']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['jabatan']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['keterangan']."</td>";
		}
	}
	$tab.="</tr>";
	$tab.="</table>";
	$tab.="</fieldset>
		</td></table>";
	if ($tipe == 'html') {
		echo $tab;
	} else {
		
		$tempnm = explode("/",$_SERVER['PHP_SELF']);
		$nop = substr($tempnm[2],0,strripos($tempnm[2],'.')).".xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("sheet1", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
	}
	break;
case 'getjenispt':
	$str = " select * from ".$dbname.".lgl_anggarandasarht where  kodept='".$pt."'"; //exit('error'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	echo $bar['jenispt'];

	break;
case 'detailakta':
	 # cek data ht sudah ada atau belum
	$sql = "select count(*) as jmlhrow from ".$dbname.".lgl_anggarandasarht where kodept='".$pt."'";
	$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$jlhbrs = $bar['jmlhrow'];
	if ($jlhbrs == 0) {
		 # Jika data kosong maka Insert HT dulu
		$str = "insert into ".$dbname.".lgl_anggarandasarht (`kodept`,`jenispt`,`createby`,`createtime`,`updateby`)
			values ('".$pt."','".$jenis."','".$_SESSION['standard']['userid']."','".$todayhis."','".$_SESSION['standard']['userid']."')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	} else {
		 # Jika data ada maka Update
		$str = "update ".$dbname.".lgl_anggarandasarht set jenispt='".$jenis."', updateby='".$_SESSION['standard']['userid']."' where kodept='".$pt."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	}
	 #  === end insert ht ===
	OPEN_BOX();
	 @$optjenis.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	 @$arrjenis = getEnum($dbname, 'lgl_anggarandasardt_akta', 'jenisakta');
	foreach($arrjenis as $key => $val) {
		$optjenis.="<option value='".$key."'>".strtoupper($key)."</option>";
	}

	

	$optnotaris = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$sql = "SELECT distinct(a.supplierid) as supplierid, namasupplier FROM " . $dbname . ".log_5supkelompok a left join " . $dbname . ".log_5supplier b on a.supplierid=b.supplierid where tipe='NOTARIS'";
	$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $qry->fetch()) {
		$optnotaris.="<option value=" . $bar['supplierid'] . ">" . $bar['namasupplier'] . "</option>";
	}

		$optnoakta = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	// $sql = "select * FROM " . $dbname . ".lgl_anggarandasardt_akta where kodept='".$pt."' and jenisakta='pendirian' order by updatetime desc";
	$sql = "select distinct noakta FROM " . $dbname . ".lgl_anggarandasardt_akta where kodept='".$pt."' order by updatetime desc";
	$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $qry->fetch()) {
		$optnoakta.="<option value=" . $bar['noakta'] . ">" . $bar['noakta'] . "</option>";
	}


	//lgl_anggarandasardt_akta where where kodept='".$pt."' and jenisakta='pendirian' order by updatetime desc
	 #  === input dan list akta ===

echo"<fieldset>
	<legend>".$_SESSION['lang']['form']." Akta</legend>
	<table>
	
	<tr>
		<td>Jenis Akta</td>
		<td>:</td>
		<td><select id=jenisakta style=\"width:200px;\">".$optjenis."</select></td>
		
		<td>Nomor Akta</td>
		<td>:</td>
		<td><input id=nomorakta class=myinputtext onfocus='autodif(this)' nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:195px;\"></td>
		
		<td>Tanggal Akta</td>
		<td>:</td>
		<td><input type='text' style='width:195px;' class='myinputtext' id='tglakta' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
	</tr>
	<tr>
		<td>Nama Notaris [<input type='checkbox' id='cek' onclick='changetype()'>Input Text]</td>
		<td>:</td>
		<td><select id=notaris style=\"width:200px;\">".$optnotaris."</select>
		<input type='hidden' style='width:195px;' class='myinputtext' id='notarisx' onkeypress=\"return_tanpa_kutip(event);\" />
		</td>
	
		<td>".$_SESSION['lang']['noskhakim']."</td>
		<td>:</td>
		<td><input id=noskhakim class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:195px;\"></td>
		
		<td>".$_SESSION['lang']['tglskhakim']."</td>
		<td>:</td>
		<td><input type='text' style='width:195px;' class='myinputtext' id='tglskhakim' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
	</tr>
	<tr>	
		<td>".$_SESSION['lang']['kedudukan']."</td>
		<td>:</td>
		<td><input id=kedudukan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:195px;\"></td>
		
		<td>".$_SESSION['lang']['alamat']."</td>
		<td>:</td>
		<td><input id=alamat class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:195px;\"></td>
		
		<td>".$_SESSION['lang']['modaldasar']."</td>
		<td>:</td>
		<td><input id=modaldasar nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:195px;\" onkeyup=\"z.numberFormat('modaldasar',2)\"></td>
	
	</tr>
	<tr>
		
		<td>".$_SESSION['lang']['modalsetor']."</td>
		<td>:</td>
		<td><input id=modalsetor nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:195px;\" onkeyup=\"z.numberFormat('modalsetor',2)\"></td>
		
		<td>Kegiatan Usaha</td>
		<td>:</td>
		<td><input id=kegusaha class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:195px;\"></td>
		
		<td>BNRI</td>
		<td>:</td>
		<td><input id=bnri class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:195px;\"></td>
	
	</tr>
	<tr>
		<td>TBNRI</td>
		<td>:</td>
		<td><input id=tbnri class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:195px;\"></td>
		
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' style='width:195px;' class='myinputtext' id='tglbnri' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
		
		<td>".$_SESSION['lang']['keterangan']."</td>
		<td>:</td>
		<td><input id=keterangan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:195px;\"></td>
	
	</tr>
	<tr>
		<td colspan=2></td>
		<td  colspan=5><input type=hidden id=method value='insertakta'>
		<button title='".$_SESSION['lang']['save']."' class=mybutton onclick=\"savedetailakta()\" src='images/save.png'/>".$_SESSION['lang']['save']."</button>
		<button title='".$_SESSION['lang']['clear']."' class=mybutton onclick=\"cleardetailakta()\" src='images/clear.png'/>".$_SESSION['lang']['cancel']."</button>
		<button title='".$_SESSION['lang']['selesai']."' class=mybutton onclick=displayList() src=\"images/foldoq.png\"/>".$_SESSION['lang']['selesai']."</button>
		<button title='Refresh List Data' class=mybutton onclick=\"loaddatadetailakta()\" src='images/refresh2.png'/>Refresh</button>
		</td>
	</tr>
	</table>
	</fieldset>
	";	 


	
echo "
	<fieldset>
	<legend>".$_SESSION['lang']['akta']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 class=sortable >
	<thead><tr class=rowheader>
	<td align=center style=\"width:30px;\">".$_SESSION['lang']['nourut']."</td>
	<td align=center>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['akta']."</td>
	<td align=center>".$_SESSION['lang']['nomor']."</td>
	<td align=center>".$_SESSION['lang']['tanggalakta']."</td>
	<td align=center>".$_SESSION['lang']['namanotaris']."</td>
	<td align=center>".$_SESSION['lang']['noskhakim']."</td>
	<td align=center>".$_SESSION['lang']['tglskhakim']."</td>
	<td align=center>".$_SESSION['lang']['kedudukan']."</td>
	<td align=center>".$_SESSION['lang']['alamat']."</td>
	<td align=center>Σ ".$_SESSION['lang']['modaldasar']."</td>
	<td align=center>Σ ".$_SESSION['lang']['modalsetor']."</td>
	<td align=center>Kegiatan Usaha</td>
	<td align=center >BNRI</td>
	<td align=center >TBNRI</td>
	<td align=center >".$_SESSION['lang']['tanggal']."</td>
	<td align=center >".$_SESSION['lang']['keterangan']."</td>
	<td align=center width=50px>".$_SESSION['lang']['action']."</td>
	</tr>
	</thead>
	
	
	
	<tbody id=loaddatadetailakta>
	</tbody>
	
	</table>
	</fieldset>";
	 #  ===  ===  = end list akta ===  ===
		 
		 
		 
	
echo"<hr><fieldset>
	<legend>".$_SESSION['lang']['form']." Saham</legend>
	<table>	
	<tr>
		<td align=left>".$_SESSION['lang']['nomor']." Akta</td>
		<td>:</td>
		<td><select id=noktasaham style=\"width:152px;\" onchange=\"gettglakta('saham')\"></select></td>
		
		
		<td align=left>".$_SESSION['lang']['tanggal']." Akta</td>
		<td>:</td>
		<td><select id=tglsaham style=\"width:152px;\"></select></td>
		<td hidden><input type='text' style='width:152px;' class='myinputtext' id='tglsahamlama' /></td>
		
		
		<td align=left>".$_SESSION['lang']['nama']."</td>
		<td>:</td>
		<td><input id=namasaham class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:150px;\"></td>
		
	</tr>
	<tr>
		
		<td align=left>Σ Lembar ".$_SESSION['lang']['saham']."</td>
		<td>:</td>
		<td><input id=lembarsaham onblur=getnilaisaham() nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" onkeyup=\"z.numberFormat('lembarsaham',2)\"></td>
		
		
		
		<td align=left >Nilai Saham / per Lembar</td>
		<td>:</td>
		<td><input id=nilaisaham onblur=getnilaisaham() nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" onkeyup=\"z.numberFormat('nilaisaham',2)\"></td>
		
		
		<td align=left >Σ ".$_SESSION['lang']['saham']."</td>
		<td>:</td>
		<td><input id=saham disabled nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" onkeyup=\"z.numberFormat('saham',2)\"></td>
	
	</tr>
	<tr>
		<td colspan=2></td>
		<td align=left><input type=hidden id=methodsaham value='insertsaham'>
		<button title='".$_SESSION['lang']['save']."' class=mybutton onclick=\"savedetailsaham()\" src='images/save.png'/>".$_SESSION['lang']['save']."</button>
		<button title='".$_SESSION['lang']['clear']."' class=mybutton onclick=\"cleardetailsaham()\" src='images/clear.png'/>".$_SESSION['lang']['cancel']."</button>
		<button title='".$_SESSION['lang']['selesai']."' class=mybutton onclick=displayList() src=\"images/foldoq.png\"/>".$_SESSION['lang']['selesai']."</button>
		<button title='Refresh List Data' class=mybutton onclick=\"loaddatadetailsaham()\" src='images/refresh2.png'/>Refresh</button>
		
		</td>

	</tr>
	</table>
	</fieldset>
	";		 
		 
		 
		 
		 #  === input dan list saham dan komisaris ===
		echo "
		<fieldset>
		<legend>".$_SESSION['lang']['saham']."</legend>
		<table border=0 cellpadding=1 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>
		<td align=center style=\"width:30px;\">".$_SESSION['lang']['nourut']."</td>
		<td align=center>".$_SESSION['lang']['nomor']." Akta</td>
		<td align=center>".$_SESSION['lang']['tanggal']." Akta</td>
		<td align=center>".$_SESSION['lang']['nama']."</td>
		<td align=center style=\"width:80px;\">Σ Lembar ".$_SESSION['lang']['saham']."</td>
		<td align=center style=\"width:80px;\">Nilai Saham / per Lembar</td>
		<td align=center style=\"width:80px;\">Σ ".$_SESSION['lang']['saham']."</td>
		<td align=center width=50px>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id=loaddatadetailsaham>
		</tbody>
		</tr></table></fieldset>";

	

echo"<hr><fieldset>
	<legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['susunanpengurusdankomisaris']."</legend>
	<table>	
	<tr>
		<td align=left>".$_SESSION['lang']['nomor']." Akta</td>
		<td>:</td>
		<td><select id=noakta style=width:152px; onchange=\"gettglakta('pengurus')\">".$optnoakta."</select></td>
		
		
		<td align=left>Tanggal Akta</td>
		<td>:</td>
		<td><select id=tglkom style=\"width:152px;\"></select></td>
		<td hidden><input type='text' style='width:150px;' class='myinputtext' id='tglkomlama' /></td>
		
		
		<td align=left>".$_SESSION['lang']['nama']."</td>
		<td>:</td>
		<td><input id=namakom class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:150px;\"></td>
	
	</tr>
	<tr>
		
		<td align=left>".$_SESSION['lang']['jabatan']."</td>
		<td>:</td>
		<td><input id=jabatankom class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:150px;\"></td>
		
		
		<td align=left >".$_SESSION['lang']['keterangan']."</td>
		<td>:</td>
		<td><input id=keterangankom class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:150px;\"></td>
	
	</tr>
	<tr>	
		<td colspan=2></td>
		<td align=left><input type=hidden id=methodkom value='insertkom'>
		<button title='".$_SESSION['lang']['save']."' class=mybutton onclick=\"savedetailkom()\" src='images/save.png'/>".$_SESSION['lang']['save']."</button>
		<button title='".$_SESSION['lang']['clear']."' class=mybutton onclick=\"cleardetailkom()\" src='images/clear.png'/>".$_SESSION['lang']['cancel']."</button>
		<button title='".$_SESSION['lang']['selesai']."' class=mybutton onclick=displayList() src=\"images/foldoq.png\"/>".$_SESSION['lang']['selesai']."</button>
		<button title='Refresh List Data' class=mybutton onclick=\"loaddatadetailkom()\" src='images/refresh2.png'/>Refresh</button>
		</td>
		
	</tr>
	</table>
	</fieldset>
	";	
	
echo"<fieldset>
		<legend>".$_SESSION['lang']['susunanpengurusdankomisaris']."</legend>
		<table border=0 cellpadding=1 cellspacing=1 class=sortable >
		<thead><tr class=rowheader>
		<td align=center style=\"width:30px;\">".$_SESSION['lang']['nourut']."</td>
		<td align=center>".$_SESSION['lang']['nomor']." Akta</td>
		<td align=center>Tanggal Akta</td>
		<td align=center style=\"width:150px;\">".$_SESSION['lang']['nama']."</td>
		<td align=center style=\"width:150px;\">".$_SESSION['lang']['jabatan']."</td>
		<td align=center >".$_SESSION['lang']['keterangan']."</td>
		<td align=center width=50px>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody id=loaddatadetailkom>
		</tbody>
		
		</tr></table>
		</fieldset>";
	 #=  == end input saham ===
	CLOSE_BOX();
	break;
case 'loaddatadetailakta':
	$tab = "";
	$no = 0;
	$str = "select * from ".$dbname.".lgl_anggarandasardt_akta where kodept='".$pt."' order by tanggalakta asc";
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$row = $res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if ($row == 0) {
		$tab.="<tr class=rowcontent><td colspan=17 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	} else {
		$lf =$nmnotaris= '';
		while ($bar = $res->fetch()) {
			$nmnotaris = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['namanotaris']."'");
			$lf = " onclick=\"viewlistfile('akta','".$bar['kodept']."','".$bar['jenisakta']."','".$bar['noakta']."')\" valign=top";
			$no += 1;
			
			$title="style=cursor:pointer";
			if($bar['posting']==1 and $bar['statuspersetujuan']==3){
				$judul = makeOption($dbname,'approval','notransaksi,komentar',"notransaksi='".$bar['nopengajuan']."'");
				$title=" title=\"".$judul[$bar['nopengajuan']]."\" style=color:red;cursor:pointer";
			}
			
			$tab.="<tr class=rowcontent id=tr_".$no."  ". @$title.">";
			$tab.="<td ".$lf." align=center>".$no."</td>";
			$tab.="<td ".$lf." align=left>".strtoupper($bar['jenisakta'])."</td>";
			$tab.="<td ".$lf." align=left>".$bar['noakta']."</td>";
			$tab.="<td ".$lf." align=center align=center>".$bar['tanggalakta']."</td>";
			if(@$nmnotaris[$bar['namanotaris']]==''){
			$tab.="<td ".$lf." align=left>".$bar['namanotaris']."</td>";
			}else{
			$tab.="<td ".$lf." align=left>".$nmnotaris[$bar['namanotaris']]."</td>";	
			}
			$tab.="<td ".$lf." align=left>".$bar['noskkehakiman']."</td>";
			$tab.="<td ".$lf." align=center>".$bar['tanggalsk']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['kedudukan']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['alamat']."</td>";
			$tab.="<td ".$lf." align=right>". @ number_format($bar['modaldasar'])."</td>";
			$tab.="<td ".$lf." align=right>". @ number_format($bar['modalsetor'])."</td>";
			$tab.="<td ".$lf." align=left>".$bar['kegusaha']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['bnri']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['tbnri']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['tglbnri']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['keterangan']."</td>";
			if($bar['posting']==0 or ($bar['posting']==1 and $bar['statuspersetujuan']==3)){
			$tab.="<td align=center width=75px>";
				if(@$nmnotaris[$bar['namanotaris']]==''){
				$tab.="<img src=images/application/application_edit.png class=resicon  title='Edit'
					onclick=\"editdetailakta('".$pt."','".$bar['jenisakta']."','".$bar['noakta']."','".tanggalnormal($bar['tanggalakta'])."','".$bar['namanotaris']."','".$bar['noskkehakiman']."','".tanggalnormal($bar['tanggalsk'])."','".$bar['kedudukan']."','".$bar['alamat']."','".$bar['modaldasar']."','".$bar['modalsetor']."','".$bar['kegusaha']."','".$bar['bnri']."','".$bar['tbnri']."','".$bar['tglbnri']."','".$bar['keterangan']."','1');\">&nbsp;";
				}else{
				$tab.="<img src=images/application/application_edit.png class=resicon  title='Edit'
					onclick=\"editdetailakta('".$pt."','".$bar['jenisakta']."','".$bar['noakta']."','".tanggalnormal($bar['tanggalakta'])."','".$bar['namanotaris']."','".$bar['noskkehakiman']."','".tanggalnormal($bar['tanggalsk'])."','".$bar['kedudukan']."','".$bar['alamat']."','".$bar['modaldasar']."','".$bar['modalsetor']."','".$bar['kegusaha']."','".$bar['bnri']."','".$bar['tbnri']."','".$bar['tglbnri']."','".$bar['keterangan']."','0');\">&nbsp;";	
				}
				$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete'
					onclick=\"deletedetail('akta','".$pt."','".$bar['jenisakta']."','".$bar['noakta']."','','".$bar['tanggalakta']."');\">&nbsp;";
				
				$tab.="<img src=images/skyblue/submit.jpg class=resicon class=zImgBtn height='30'  title='Ajukan ???' 
                    onclick=\"form_ajukan('".$pt."','".$bar['jenisakta']."','".$bar['noakta']."','".$bar['nopengajuan']."','" . $no . "','".$bar['tanggalakta']."');\" >&nbsp;";
					
				$tab.="<img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'akta','".$pt."','".$bar['jenisakta']."','".$bar['noakta']."')\" src='images/upload-2-xxl.png'/>";
			
			$tab.="</td>";
			}else{
				$tab.="<td align=center width=75px onclick=getstatuspersetujuan('".$bar['nopengajuan']."')><font color=blue>";				
				$tab.="" . $arrHsl[$bar['statuspersetujuan']] . "";				
				$tab.="</font></td>";
			}
		}
	}
	$tab.="</tr>";
	$tab.="</table>";
	echo $tab;
	break;
	
	case'getstatuspersetujuan':
	
	@$countApprove = getCountApproval('ANGGARAN');
		
		
		$str=" select * from ".$dbname.".lgl_anggarandasardt_akta where  nopengajuan='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		$tab.= "<table border=0 cellspacing=1 class=sortable>
				<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['updateby']."</td>";
				for($i=1;$i<=$countApprove;$i++){
					$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				}
					
		$tab.= "</tr></thead><tbody>";
		$tab.= "<tr class=rowcontent>
				<td>".$nmkar[$bar['updateby']]."<br>
					".$bar['updatetime']."</td>";
			for($i=1;$i<=$countApprove;$i++){
				@$arrApp = detailApprove($i,$notransaksi);
				
				if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
					$tngl='';
				}else{
					$tngl=($arrApp['tanggal']);
				}
				if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
					$tab.= "<td>".$arrApp['nama']."
						<br />".$arrHsl[$arrApp['status']].", ".$tngl."
						<br />".$arrApp['komentar']."
					</td>";
				}else{
					$tab.= "<td>&nbsp;</td>";
				}
			}
	$tab.= "</tbody></table>";
	
	#status tolak
	$str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$notransaksi."' group by keterangan";
	$res=fetchdata($str);
	$row=count($res);
	if($row>0){
		$no=0;
		foreach($res as $key=>$val){
			$no++;
			$tab.="<br><table border=0 cellspacing=1 class=sortable>
					<thead>
					<tr style='font-weight:bold'>
						<td colspan='".($val['level'])."'>Return / Tolak - ".$no."</td>
					</tr>
					<tr style='font-weight:bold'>";
						for($i=1;$i<=$val['level'];$i++) {
							$tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
						}
					$tab.="</tr>
				</thead>
				<tbody>
					<tr class=rowcontent>";
					for($i=1;$i<=$val['level'];$i++) {
						$strx="select * from ".$dbname.".approval_return where notransaksi='".$notransaksi."' and level='".$i."' and keterangan='".$val['keterangan']."'";
						$resx=fetchdata($strx);
						$color='';
						if($resx[0]['status']==3){
							$color=" style=background-color:red ";
						}
						$tab.="<td ".$color.">".$nmkar[$resx[0]['karyawanid']]."
							<br>	
							".$arrHsl[$resx[0]['status']]."
							<br>	
							".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
							<br>	
							".$resx[0]['komentar']."
						</td>";
					}
					$tab.="</tr>
				</tbody>
				</table>";
		}
	}
	$tab.="<hr>";
	
	echo $tab;
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
case 'loaddatadetailsaham':
	$tab = "";
	$no = 0;
	$str = "select * from ".$dbname.".lgl_anggarandasardt_saham where kodept='".$pt."' 
			order by tanggal_akta asc";

	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$row = $res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if ($row == 0) {
		$tab.="<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	} else {
		while ($bar = $res->fetch()) {
			$lf = '';
			$lf = " onclick=\"viewlistfile('saham','".$bar['kodept']."','".$bar['tahun']."','".$bar['nama']."')\" valign=top";
			$no += 1;
			$tab.="<tr class=rowcontent style=cursor:pointer ". @$title.">";
			$tab.="<td ".$lf." align=center>".$no."</td>";
			//$tab.="<td ".$lf." align=center>".$bar['tahun']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['noakta']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['tanggal_akta']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['nama']."</td>";
			
			$tab.="<td ".$lf." align=right>". @ hidezerodecimal($bar['lembarsaham'])."</td>";
			$tab.="<td ".$lf." align=right>". @ hidezerodecimal($bar['nilaisaham'])."</td>";
			$tab.="<td ".$lf." align=right>". @ hidezerodecimal($bar['saham'])."</td>";
			$tab.="<td align=center width=75px>";
			$stsposting=makeOption($dbname,'lgl_anggarandasardt_akta','noakta,posting',"noakta='".$bar['noakta']."' and tanggalakta='".$bar['tanggal_akta']."'");
			$stssetujui=makeOption($dbname,'lgl_anggarandasardt_akta','noakta,statuspersetujuan',"noakta='".$bar['noakta']."' and tanggalakta='".$bar['tanggal_akta']."'");
			
			if($stsposting[$bar['noakta']]==0 or ($stsposting[$bar['noakta']]==1 and $stssetujui[$bar['noakta']]==3) ){
				$tab.="<img src=images/application/application_edit.png class=resicon  title='Edit'
					onclick=\"editdetailsaham('".$bar['kodept']."','".$bar['tahun']."','".$bar['nama']."','".$bar['lembarsaham']."','".$bar['nilaisaham']."','".$bar['saham']."','".$bar['noakta']."','".$bar['tanggal_akta']."');\">&nbsp;";
				$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete'
					onclick=\"deletedetail('saham','".$pt."','".$bar['noakta']."','".$bar['nama']."','','".$bar['tanggal_akta']."');\">&nbsp;";
				$tab.="<img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'saham','".$pt."','".$bar['tahun']."','".$bar['nama']."')\" src='images/upload-2-xxl.png'/>";
			}else{
				$stssetuju=makeOption($dbname,'lgl_anggarandasardt_akta','noakta,statuspersetujuan',"noakta='".$bar['noakta']."'");
				$tab.="" . $arrHsl[$stssetuju[$bar['noakta']]] . "";
				
			}
			$tab.="</td>";
		}
	}
	$tab.="</tr>";
	$tab.="</table>";
	echo $tab;
	break;
case 'loaddatadetailkom':
	$tab = "";
	$no = 0;
	$str = "select * from ".$dbname.".lgl_anggarandasardt_komisaris  where kodept='".$pt."'  order by tanggal_akta asc";
	//exit('error'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$row = $res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if ($row == 0) {
		$tab.="<tr class=rowcontent><td colspan=6 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	} else {
		while ($bar = $res->fetch()) {
			$lf = '';
			$lf = " onclick=\"viewlistfile('kom','".$bar['kodept']."','".$bar['tahun']."','".$bar['nama']."','".$bar['jabatan']."')\" valign=top";
			$no += 1;
			$tab.="<tr class=rowcontent style=cursor:pointer ".@$title.">";
			$tab.="<td ".$lf." align=center>".$no."</td>";
			$tab.="<td ".$lf." align=center>".$bar['tahun']."</td>";
			$tab.="<td ".$lf." align=center>".$bar['tanggal_akta']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['nama']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['jabatan']."</td>";
			$tab.="<td ".$lf." align=left>".$bar['keterangan']."</td>";
			$tab.="<td align=center width=75px>";
			$stsposting=makeOption($dbname,'lgl_anggarandasardt_akta','noakta,posting',"noakta='".$bar['tahun']."' and tanggalakta='".$bar['tanggal_akta']."'");
			$stssetujui=makeOption($dbname,'lgl_anggarandasardt_akta','noakta,statuspersetujuan',"noakta='".$bar['tahun']."' and tanggalakta='".$bar['tanggal_akta']."'");
			
			if($stsposting[$bar['tahun']]==0 or ($stsposting[$bar['tahun']]==1 and $stssetujui[$bar['tahun']]==3) ){
				$tab.="<img src=images/application/application_edit.png class=resicon  title='Edit'
					onclick=\"editdetailkom('".$bar['kodept']."','".$bar['tahun']."','".$bar['nama']."','".$bar['jabatan']."','".$bar['keterangan']."','".$bar['tanggal_akta']."');\">&nbsp;";
				$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete'
					onclick=\"deletedetail('kom','".$pt."','".$bar['tahun']."','".$bar['nama']."','".$bar['jabatan']."','".$bar['tanggal_akta']."');\">&nbsp;";
				$tab.="<img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'kom','".$pt."','".$bar['tahun']."','".$bar['nama']."','".$bar['jabatan']."')\" src='images/upload-2-xxl.png'/>";
			}else{
				
				$stssetuju=makeOption($dbname,'lgl_anggarandasardt_akta','noakta,statuspersetujuan',"noakta='".$bar['tahun']."'");
				$tab.="" . $arrHsl[$stssetuju[$bar['tahun']]] . "";
				
			}
			$tab.="</td>";
		}
	}
	$tab.="</tr>";
	$tab.="</table>";
	echo $tab;
	break;
case 'insertakta':
	 # cek data dt jenis pendirian ada atau belum(hanya sekali untuk ini)
	$sql = "select * from ".$dbname.".lgl_anggarandasardt_akta where kodept='".$pt."'";
	$res = fetchData($sql);
	if (($res[0]['jenisakta'] == 'pendirian') != '' and $jenisakta == 'pendirian') {
		exit('Error : Akta pendirian hanya boleh diinput sekali !');
	}
	if (($res[0]['jenisakta'] == 'pendirian') == '' and $jenisakta != 'pendirian') {
		exit('Error : Input akta pendirian terlebih dahulu !');
	}
	 # cek data dt sudah ada atau belum
	$sql = "select * from ".$dbname.".lgl_anggarandasardt_akta where kodept='".$pt."' and jenisakta='".$jenisakta."' and noakta='".$nomorakta."' and tanggalakta='".$tglakta."'";
	$res = fetchData($sql);
	if (count($res) > 0) {
		exit('Error : Data sudah ada !');
	}
	 # Jika data sudah ada maka langsung Insert Akta
	$str = "insert into ".$dbname.".lgl_anggarandasardt_akta (`kodept`,`jenisakta`, `noakta`,`tanggalakta`,`namanotaris`,`noskkehakiman`,`tanggalsk`,`kedudukan`,`modaldasar`,`modalsetor`,`alamat`,`updateby`,`kegusaha`,`bnri`,`tbnri`,`tglbnri`,`keterangan`)
		values ('".$pt."','".$jenisakta."','".$nomorakta."','".$tglakta."','".$notaris."','".$noskhakim."','".$tglskhakim."','".$kedudukan."','".$modaldasar."','".$modalsetor."','".$alamat."','".$_SESSION['standard']['userid']."','".$kegusaha."','".$bnri."','".$tbnri."','".$tglbnri."','".$keterangan."')";
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'insertsaham':
	 # cek data ht sudah ada atau belum
	$sql = "select * from ".$dbname.".lgl_anggarandasardt_akta where kodept='".$pt."'";
	$res = fetchData($sql);
	if (count($res) == 0) {
		exit('Error : Silahkan isi Akta pendirian terlebih dahulu !');
	}
	 # cek data dt sudah ada atau belum
	$sql = "select * from ".$dbname.".lgl_anggarandasardt_saham where kodept='".$pt."' and tahun='".$tahun."' and nama='".$namasaham."' and noakta='".$nomorakta."'";
	$res = fetchData($sql);
	if (count($res) > 0) {
		exit('Error : Data sudah ada !');
	}
	 # cek total modal dasar di lgl_anggarandasardt_akta
	$sql = "select * from ".$dbname.".lgl_anggarandasardt_akta where kodept='".$pt."' and noakta='".$nomorakta."' and tanggalakta='".$tglsaham."' ";
	$resx = fetchData($sql);
	 # cek total modal dasar di lgl_anggarandasardt_saham
	$str = "select * from ".$dbname.".lgl_anggarandasardt_saham where kodept='".$pt."' and noakta='".$nomorakta."' and tanggal_akta='".$tglsaham."' ";
	$resv = fetchData($str);
	if (( @$resv[0]['saham'] + $saham) >  @$resx[0]['modaldasar']) {
		exit('Warning : Nilai saham lebih besar dari Modal Dasar !');
	}

	$str = "select substr(tanggalakta,1,4) as tahun from ".$dbname.".lgl_anggarandasardt_akta where noakta='".$nomorakta."'";
	$res = fetchData($sql);

    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
     $res->setFetchMode(PDO::FETCH_ASSOC);
     while($bar1=$res->fetch()){
      $tahun=$bar1['tahun'];
                  }


	 # Jika data sudah ada maka langsung Insert Akta
	$str = "insert into ".$dbname.".lgl_anggarandasardt_saham (`kodept`,`tahun`, `nama`,`saham`,`updateby`,`noakta`,`lembarsaham`,`nilaisaham`,`tanggal_akta`)
		values ('".$pt."','".$tahun."','".$namasaham."','".$saham."','".$_SESSION['standard']['userid']."','".$nomorakta."','".$lembarsaham."','".$nilaisaham."','".$tglsaham."')";
		//exit('error'.$str);
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'insertkom':
	 # cek data ht sudah ada atau belum
	$sql = "select * from ".$dbname.".lgl_anggarandasardt_akta where kodept='".$pt."'";
	$res = fetchData($sql);
	if (count($res) == 0) {
		exit('Error : Silahkan isi Akta pendirian terlebih dahulu !');
	}
	 # cek data dt sudah ada atau belum
	$sql = "select * from ".$dbname.".lgl_anggarandasardt_komisaris where kodept='".$pt."' and tahun='".$tahun."' and nama='".$namakom."' and jabatan='".$jabatankom."'";
	$res = fetchData($sql);
	if (count($res) > 0) {
		exit('Error : Data sudah ada !');
	}

	$str = "select substr(tanggalakta,1,4) as tahun from ".$dbname.".lgl_anggarandasardt_akta where noakta='".$noakta."' and tanggalakta='".$tglkom."'";
	$res = fetchData($sql);

    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
     $res->setFetchMode(PDO::FETCH_ASSOC);
     while($bar1=$res->fetch()){
      $tahun1=$bar1['tahun'];
                  }
	 # Jika data sudah ada maka langsung Insert Akta
	$str = "insert into ".$dbname.".lgl_anggarandasardt_komisaris (`kodept`,`tahun`, `nama`,`jabatan`,`keterangan`,`updateby`,`tanggal_akta`)
		values ('".$pt."','".$noakta."','".$namakom."','".$jabatankom."','".$keterangankom."','".$_SESSION['standard']['userid']."','".$tglkom."')";
		
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'updateakta':
	$str = "update ".$dbname.".lgl_anggarandasardt_akta set  namanotaris='".$notaris."', noskkehakiman='".$noskhakim."', tanggalsk='".$tglskhakim."', kedudukan='".$kedudukan."', alamat='".$alamat."', updateby='".$_SESSION['standard']['userid']."', modaldasar='".$modaldasar."', modalsetor='".$modalsetor."',keterangan='".$keterangan."' where kodept='".$pt."' and jenisakta='".$jenisakta."' and noakta='".$nomorakta."' and tanggalakta='".$tglakta."'";
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'updatesaham':
	 # cek total modal dasar di lgl_anggarandasardt_akta
	$sql = "select * from ".$dbname.".lgl_anggarandasardt_akta where kodept='".$pt."' and noakta='".$nomorakta."' and tanggalakta ='".$tglsaham."' ";
	$resx = fetchData($sql);
	 # cek total modal dasar di lgl_anggarandasardt_saham
	$str = "select * from ".$dbname.".lgl_anggarandasardt_saham where kodept='".$pt."' and noakta='".$nomorakta."' and tanggal_akta ='".$tglsaham."'";
	$resv = fetchData($str);
	 # cek modal dasar di lgl_anggarandasardt_saham yg diedit
	$str = "select * from ".$dbname.".lgl_anggarandasardt_saham where kodept='".$pt."' and noakta='".$nomorakta."' and tahun='".$tahun."' and nama='".$namasaham."' and tanggal_akta ='".$tglsaham."'";
	$resq = fetchData($str);
	if (( @$resv[0]['saham'] -  @$resq[0]['saham'] + $saham) >  @$resx[0]['modaldasar']) {
		exit('Warning : Nilai saham lebih besar dari Modal Dasar !');
	}
	$str = "update ".$dbname.".lgl_anggarandasardt_saham set tanggal_akta='".$tglsaham."', lembarsaham='".$lembarsaham."', nilaisaham='".$nilaisaham."', saham='".$saham."', updateby='".$_SESSION['standard']['userid']."' where kodept='".$pt."' and nama='".$namasaham."' and noakta='".$nomorakta."'  and tanggal_akta='".$tglsahamlama."'"; //exit('error'.$str);
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'updatekom':
	if($tglkom=='')
	{
		exit('Error : Tanggal Akta Harus Dipilih');
	}
	if($tglkomlama=='')
	{
		$tglkomlama='0000-00-00';
	}
	$str = "update ".$dbname.".lgl_anggarandasardt_komisaris set tanggal_akta='".$tglkom."', keterangan='".$keterangankom."', updateby='".$_SESSION['standard']['userid']."' where kodept='".$pt."' and tahun='".$noakta."' and nama='".$namakom."'  and jabatan='".$jabatankom."' and tanggal_akta='".$tglkomlama."'"; //exit('error'.$str);
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'deletedetail':
	switch ($jenis) {
	case 'akta':
		$jenisakta = $xxx;
		$nomorakta = $yyy;
		$str = "delete from ".$dbname.".lgl_anggarandasardt_akta where kodept='".$pt."' and jenisakta='".$jenisakta."' and noakta='".$nomorakta."' and tanggalakta='".$tglaktax."'";
		// exit('error'.$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
		break;
	case 'saham':
		$tahun = $xxx;
		$nama = $yyy;
		$str = "delete from ".$dbname.".lgl_anggarandasardt_saham where kodept='".$pt."' and noakta='".$tahun."' and nama='".$nama."' and tanggal_akta='".$tglaktax."'"; 
		// exit('error'.$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
		break;
	case 'kom':
		$tahun = $xxx;
		$nama = $yyy;
		$str = "delete from ".$dbname.".lgl_anggarandasardt_komisaris where kodept='".$pt."' and tahun='".$tahun."' and nama='".$nama."' and jabatan='".$iii."' and tanggal_akta='".$tglaktax."'"; //exit('error'.$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
		break;
	}
	 # delete file
	$sql = "select * from ".$dbname.".listfile_lgl_anggarandasar where kodept='".$pt."' and jenis='".$jenis."' and field1='".$xxx."' and field2='".$yyy."'and field3='".$iii."'"; //exit('error'.$sql);
	$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$str = "delete from ".$dbname.".listfile_lgl_anggarandasar where kodept ='".$pt."' and jenis='".$jenis."' and field1='".$xxx."' and field2='".$yyy."'and field3='".$iii."' and namafile='".$bar['namafile']."'";
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
case 'delete':
	$str = "delete from ".$dbname.".lgl_anggarandasarht where kodept='".$pt."'";
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'loaddata':
	$arrnama = array('PMA' => 'Penanaman Modal Asing' , 'PMDNFU' => 'PMDN Fasilitas Umum' , 'PMDNNFU' => 'PMDN Non Fasilitas Umum');
	$arrstatus = array('0' => 'Belum Pengajuan' , '1' => 'Disetujui' , '2' => 'Ditolak','3' => 'Proses Pengajuan');
	$where = "";
	if ($divsch != '') {
		$where.=" and a.kodept='".$divsch."' ";
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

	$sql = "SELECT distinct a.*, b.kodept as ptakta, c.kodept as ptsaham, c.kodept as ptkom
		FROM ".$dbname.".lgl_anggarandasarht a
		left join ".$dbname.".lgl_anggarandasardt_akta b on a.kodept=b.kodept
		left join ".$dbname.".lgl_anggarandasardt_saham c on a.kodept=c.kodept
		left join ".$dbname.".lgl_anggarandasardt_komisaris d on a.kodept=d.kodept
		where 1=1 ".$where." order by a.kodept asc";
	$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$res=fetchData($sql);
	$jlhbrs = count($res);
	$no = 0;
	$str = "SELECT distinct a.*, b.kodept as ptakta, c.kodept as ptsaham, c.kodept as ptkom
		FROM ".$dbname.".lgl_anggarandasarht a
		left join ".$dbname.".lgl_anggarandasardt_akta b on a.kodept=b.kodept
		left join ".$dbname.".lgl_anggarandasardt_saham c on a.kodept=c.kodept
		left join ".$dbname.".lgl_anggarandasardt_komisaris d on a.kodept=d.kodept
		where 1=1 ".$where." order by a.kodept asc limit ".$offset.",".$limit."";


		 //exit('error'.$str);
	$tab = "";
	$no = $maxdisplay;
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$row = $res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if (empty($row)) {
		$tab.="<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	} 
	else 
	{

			foreach ($res as $key => $bar) {
			$isi = '';
			$no += 1;
			$tab.="<tr class=rowcontent  id=tr_$no>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$bar['kodept']." - ".$nmorg[$bar['kodept']]."</td>";
			$tab.="<td align=left>".$arrnama[$bar['jenispt']]."</td>";
			$tab.="<td align=left>".$nmkar[$bar['updateby']]."</td>";
			

			$isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit'
				onclick=\"edit('".$bar['kodept']."','".$bar['jenispt']."');\" ></td>";
			$del = '';
			if ($bar['ptakta'] != '' or $bar['ptsaham'] != '' or $bar['ptkom'] != '') {
				$del.=" src=images/application/application_delete_gray.png onclick=\"deldisabled();\" title='Delete'";
			} else {
				$del.=" src=images/application/application_delete.png onclick=\"del('".$bar['kodept']."');\" title='Delete'";
			}
			$isi.="<td align=center><img class=resicon ".$del."></td>";
			$isi.="<td align=center><img src=images/zoom.png class=resicon  title='View' onclick=\"html('".$bar['kodept']."','html');\"></td>";

			$tab.= $isi;
			$tab.="</tr>";
			}
		
		
	}
	$totrows = ceil($jlhbrs / $limit);
	// exit('warning : '.$totrows."==".$jlhbrs."==".$limit);
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
		<tr><td colspan=7 align=center>";
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
case 'showupload':
	$tab = "";
	$tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
	switch ($jenisupload) {
	case 'akta':
		 @$lxxx.="".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['akta']."";
		 @$lyyy.="".$_SESSION['lang']['nomor']."";
		break;
	case 'saham':
		 @$lxxx.="".$_SESSION['lang']['tahun']."";
		 @$lyyy.="".$_SESSION['lang']['nama']."";
		break;
	case 'kom':
		$lxxx.="".$_SESSION['lang']['tahun']."";
		$lyyy.="".$_SESSION['lang']['nama']."";
		$liii.="".$_SESSION['lang']['jabatan']."";
		break;
	}
	$tab.="<tr>
		<td>".$_SESSION['lang']['pt']."</td>
		<td>:</td>
		<td>
		<label id='ptupload' style='display:none'>".$pt."</label>
		<label style='font-weight:bold'>".$nmorg[$pt]."</label>
		</td>
		</tr>
		<tr>
		<td>".$lxxx."</td>
		<td>:</td>
		<td>
		<label id='xxx' style='font-weight:bold'>".$xxx."</label>
		</td>
		</tr>
		<tr>
		<td>".$lyyy."</td>
		<td>:</td>
		<td>
		<label id='yyy' style='font-weight:bold'>".$yyy."</label>
		</td>
		</tr>";
	if ($iii != '') {
		$tab.="<tr>
			<td>".$liii."</td>
			<td>:</td>
			<td>
			<label id='iii' style='font-weight:bold'>".$iii."</label>
			</td>
			</tr>";
	}
	$tab.="<tr><td colspan=4><hr></td></tr>
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
		<button class=mybutton onclick=\"submitfile('".$jenisupload."')\">Submit</button>
		</td>
		</tr>
		</table>
		<p />";
	$tab.="<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
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
			$filetype = strtolower('.'.substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
			$filename = $pt."_".$xxx."_".$his."".$filetype;
			$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
			if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
				if ($_FILES['file']['size'] <= 250000000) {
					$str = "insert into ".$dbname.".listfile_lgl_anggarandasar values ('','".$pt."','".$jenisupload."','".$xxx."','".$yyy."','".$iii."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
	$tab = $icon = "";
	$str = "select * from ".$dbname.".listfile_lgl_anggarandasar where kodept = '".$pt."' and status='1' and jenis='".$jenisupload."' and field1='".$xxx."' and field2='".$yyy."'";
	//exit('error'.$str);
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
				<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
				</td>";
			$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
				<td align=center>
				<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
			$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['jenis']."','".$val['kodept']."','".$val['field1']."','".$val['field2']."','".$val['field3']."','".$val['namafile']."');\" >";
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
	$str = "delete from ".$dbname.".listfile_lgl_anggarandasar where kodept='".$pt."' and jenis='".$jenisupload."' and field1='".$xxx."' and field2='".$yyy."' and field3='".$iii."' and namafile='".$namafile."'"; //exit('error'.$str);
	try {
		$owlPDO->exec($str);
		$pathx = $path.$namafile;
		unlink($pathx);
	} catch (PDOException $e) {
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
case 'deletefileall':
	$str = "select * from ".$dbname.".listfile_lgl_anggarandasar where kodept='".$pt."' and jenis='".$jenisupload."' and field1='".$xxx."' and field2='".$yyy."' and field3='".$iii."'";
	exit('error belom kelar scriptnya'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$path2 = $path.$bar['namafile'];
		unlink($path2);
	}
	$str = "delete from ".$dbname.".listfilebyyijinops where notransaksi='".$notransaksi."'";
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
case 'getoptakta':
	$optakta = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sql = "SELECT distinct(noakta) as noakta FROM ".$dbname.".lgl_anggarandasardt_akta where kodept='".$pt."' 
		order by updatetime desc";
	/*
	$sql = "SELECT distinct(noakta) as noakta FROM ".$dbname.".lgl_anggarandasardt_akta where kodept='".$pt."' 
		and jenisakta='pendirian' order by updatetime desc";
	*/	
	$qry = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $qry->fetch()) {
		$optakta.="<option value='".$bar['noakta']."'>".$bar['noakta']."</option>";
	}
	echo $optakta;
	break;

	case 'gettglakta':
//exit('Error : '. $tglaktax);
	if($tglaktax=='' or $tglaktax=='0000-00-00' or empty($tglaktax) or $tglaktax=='undefined'){
	$optnoakta="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	}
	else
	{
		$optnoakta="<option value='".$tglaktax."'>".$tglaktax."</option>";
	}
$str="select tanggalakta from ".$dbname.".lgl_anggarandasardt_akta  where kodept='".$pt."' and noakta='".$noakta."'  order by updatetime desc" ;//where : noakun pajak (117 dan 213) dan detail=5
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($tglaktax != $bar['tanggalakta'])
    $optnoakta.="<option value='".$bar['tanggalakta']."'>".$bar['tanggalakta']."</option>";
}
echo $optnoakta;
	break;

case'formPersetujuan':
		$arrnama = array('PMA' => 'Penanaman Modal Asing' , 'PMDNFU' => 'PMDN Fasilitas Umum' , 'PMDNNFU' => 'PMDN Non Fasilitas Umum');
	
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='ANGGARAN' order by b.namakaryawan asc";
				  //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
		
		$tab="<fieldset style=width:300px;>
			<legend>".$_SESSION['lang']['pengajuan']."</legend>";
		$tab.="<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><input class=myinputtext style=width:165px type=\"text\" id=\"fnopp\" name=\"fnopp\" disabled value='".$nmorg[$pt]."' /></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['status']."</td>
				<td>:</td>
				<td><input class=myinputtext style=width:165px type=\"text\" id=\"fnopp\" name=\"fnopp\" disabled value='".$arrnama[$jenis]."' /></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kepada']."</td>
				<td>:</td>
				<td><select style=width:170px  id=\"karywn_id\" name=\"karywn_id\">". $optKry."</select></td>
			</tr>
			<input type=\"hidden\" id=\"cls_stat\" name=\"cls_stat\" value=0 />
			<tr>
				<td><td><td>
					<button class=mybutton onclick=reset_data_setuju()>".$_SESSION['lang']['cancel']."</button>
					<button class=mybutton onclick=save_persetujuan() >".$_SESSION['lang']['diajukan']."</button>
				</td>
			</tr>
		</table>
		</fieldset>";
		echo $tab;
	break;
	
	case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='ANGGARAN' and a.level='1' order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
	if($notransaksi!=''){
		$notr=$notransaksi;
	}else{
		$notr=$pt.$noakta.date('YmdHis');
	}
	
	$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>Nomor Akta</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$notr."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>Nama PT</td>
					<td width=5px>:</td>
					<td hidden id=pt_aju>".$pt."</td>
					<td >".$nmorg[$pt]."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>Nomor Akta</td>
					<td width=5px>:</td>
					<td id=noakta_aju>".$noakta."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>Jenis Akta</td>
					<td width=5px>:</td>
					<td id=jenisakta_aju>".$jenisakta."</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>Tanggal Akta</td>
					<td width=5px>:</td>
					<td id=tanggalakta_aju>".$tanggalakta."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:100%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=LEFT><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	 case'ajukan':
		try {
		$owlPDO->beginTransaction();
		
		if($kepada=='' or $notransaksi==''){
			throw new PDOException('Isikan nama penyetuju.');
		}
		
		//cari dulu apakah sudah pernah di ajukan sebelumnya
		$tglhi = date("Ymd");
		$str="select * from ".$dbname.".approval where jenispersetujuan='ANGGARAN' and notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['notransaksi']!=''){
				# jika ada pindahkan ke table ini
				$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."')";
				$owlPDO->exec($str);
			}
		}
		
		#kemudian setelah di pindah, hapus persetujuan lama
		$str="delete from ".$dbname.".approval where jenispersetujuan='ANGGARAN' and notransaksi='".$notransaksi."'";
		$owlPDO->exec($str);
		
		
		# update flag menjadi 1
        $str = "update " . $dbname . ".lgl_anggarandasardt_akta set posting='1', statuspersetujuan='0', postingdate='" . date('Y-m-d') . "',"."postingby='" . $_SESSION['standard']['userid'] . "', nopengajuan='".$notransaksi."' where kodept = '" . $pt . "' and noakta = '" . $noakta . "' and jenisakta = '" . $jenisakta . "' and tanggalakta='".$tanggalakta."'"; //exit("error".$str);
		$owlPDO->exec($str);

		# insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
				`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				 values ('','".$notransaksi."','ANGGARAN','1','" . $kepada."','0','','','')";
		$owlPDO->exec($str);
		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
        
	break;

	case 'insert_persetujuan':
		$str = "update ".$dbname.".lgl_anggarandasarht set  persetujuan='".$kary."', hasilpersetujuan='3' where kodept='".$pt."' and jenispt='".$jenis."';

			insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`,`status`,`komentar`,`keterangan`,tanggal)
			values ('".$pt."','ANGGARAN','1','".$kary."','0','','','".date('Y-m-d H:i:s')."');
		";
		
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;

	case 'aprove':
		$str = "update ".$dbname.".lgl_anggarandasarht set hasilpersetujuan='1' where kodept='".$pt."' and jenispt='".$jenis."'";
		
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;

	case 'tolak':
		$str = "update ".$dbname.".lgl_anggarandasarht set hasilpersetujuan='2' where kodept='".$pt."' and jenispt='".$jenis."'";
		
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;


}

?>	