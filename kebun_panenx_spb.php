<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
if ($_SESSION['org']['period']['start'] == '') {
	$val1 = "<span class=judul style=color:red;font-weight:bold;font-size:25px;>Warning : Silahkan buat periode akutansi untuk unit " . $_SESSION['empl']['lokasitugas'] . " terlebih dahulu</span>";
	exit($val1);
}
if ($_SESSION['empl']['tipelokasitugas'] != 'KEBUN') {
	$val2 = "<span class=judul style=color:red;font-weight:bold;font-size:25px;>Warning : Lokasi tugas anda di : " . $_SESSION['empl']['tipelokasitugas'] . ", silahkan pindah ke KEBUN terlebih dahulu.</span>";
	exit($val2);
}

?>
<script language=javascript1.2 src='js/kebun_panenx_spb.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<link rel="stylesheet" type="text/css" href="lib/MagnificPopup/magnific-popup.css">
<script type="text/javascript" src="lib/MagnificPopup/jquery.magnific-popup.js"></script>
<script>
	function popupimage() {
		alertify.closeAll();
		$('.popup-img').magnificPopup({
			type: 'image',
			removalDelay: 300,
			mainClass: 'mfp-fade',
			mainClass: 'mfp-fade',
			gallery: {
				enabled: true
			},
			zoom: {
				enabled: true,
				duration: 300,
				easing: 'ease-in-out',
				opener: function(openerElement) {
					return openerElement.is('img') ? openerElement : openerElement.find('img');
				}
			},
		});
	}
</script>

<?php
$where = $wh = "";
/* $str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
if(count($adm)==0){
} */
$where = " and induk = '" . $_SESSION['empl']['lokasitugas'] . "'";
$wh = " and kodeorganisasi = '" . $_SESSION['empl']['lokasitugas'] . "'";
# Organisasi
$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' " . $wh . "";
$res = fetchData($str);
foreach ($res as $key => $val) {
	if ($_SESSION['empl']['lokasitugas'] == $val['kodeorganisasi']) {
		$optorg .= "<option value=" . $val['kodeorganisasi'] . " selected >" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
	} else {
		$optorg .= "<option value=" . $val['kodeorganisasi'] . ">" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
	}
}
// $optorg = orgDetailuser($_SESSION['standard']['username'],'1');

// Get Kode Jabatan
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='JABKRN'";
$res = fetchdata($str);
@$arrjab = explode(',', $res[0]['nilai']);

# Divisi
$optdiv = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optdiv2 = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)=6 and tipe='AFDELING' " . $where . "";
$res = fetchData($str);
foreach ($res as $key => $val) {
	$n = "";
	if ($_SESSION['empl']['subbagian'] == $val['kodeorganisasi']) {
		$n = "selected";
	}
	$optdiv .= "<option value=" . $val['kodeorganisasi'] . " " . $n . ">" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
}

$arrdivsch = getOrgDetail(27);
foreach ($arrdivsch as $key => $val) {
	$s = "";
	if ($_SESSION['empl']['subbagian'] == $key) {
		$s = "selected";
	}
	$optdiv2 .= "<option value=" . $key . " " . $s . ">" . $key . " - " . getNamaOrg($val) . "</option>";
}

# Posting
$arrPos = array("0" => "Not Posted", "1" => "Posted");
$optPos = "<option value=''>&nbsp;</option>";
foreach ($arrPos as $key => $val) {
	@$optPos .= "<option value=" . $key . ">" . $val . "</option>";
}

# Periode
$optprd = "<option value=''>&nbsp;</option>";

# Periode
for ($x = -2; $x < 25; $x++) {
	$dt = mktime(0, 0, 0, date('m') - $x, 12, date('Y'));
	if (date("Y-m", $dt) == date("Y-m")) {
		$select = "selected";
	} else {
		$select = "";
	}

	$optprd .= "<option value=" . date("Y-m", $dt) . " " . $select . ">" . date("m-Y", $dt) . "</option>";
}

# === Option mandor dan kerani ===
$divisix = '';
// $whereKary=" and a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
if ($_SESSION['empl']['subbagian'] != '') {
	#$divisix=" and a.subbagian='".$_SESSION['empl']['subbagian']."'";
}

$optMandor = $optAsst = $optMandor1 = $optKerani = "<option value=''>&nbsp;</option>";

# === Option mandor dan kerani ===
$optAsst = $optMandor1 = $optKerani = "<option value=''>&nbsp;</option>";

$str = "select * from " . $dbname . ".kebun_5pejabatbkm where kodeorg ='" . $_SESSION['empl']['lokasitugas'] . "' and tipe='PNN'";
$res = fetchdata($str);
foreach ($res as $bar) {
	if ($bar['kolom'] == 'mandor') {
		$mdr = $bar['jabatan'];
	}
	if ($bar['kolom'] == 'mandor1') {
		$mdr1 = $bar['jabatan'];
	}
	if ($bar['kolom'] == 'kerani') {
		$krn = $bar['jabatan'];
	}
	if ($bar['kolom'] == 'asst') {
		$asst = $bar['jabatan'];
	}
}

$d = $n = "";
if ($mdr != '') {
	$whr = " and a.kodejabatan in (" . $mdr . ")";
} else {
	$whr = " and b.namajabatan like '%mandor%' and b.namajabatan not like '%mandor%1%'";
}

if (getindukPT($_SESSION['empl']['lokasitugas']) == 'PPP') {
	$tipeOrg = makeOption($dbname, "organisasi", "kodeorganisasi,inti");
	if ($tipeOrg[$_SESSION['empl']['lokasitugas']] == '0') {
		if ($mdr1 != '') {
			$getAllPlamaPerPt = makeOption($dbname, "organisasi", "kodeorganisasi,induk");

			$sqlPlasma = selectQuery($dbname, "organisasi", "kodeorganisasi", "induk='" . $getAllPlamaPerPt[$_SESSION['empl']['lokasitugas']] . "' and tipe='KEBUN'");
			$resPlasma = fetchData($sqlPlasma);

			foreach ($resPlasma as $v) {
				$datakaryawanSupervisiPlasma[$v['kodeorganisasi']] = $v['kodeorganisasi'];
			}


			$whereKary = " and a.lokasitugas IN ('" . implode("','", $datakaryawanSupervisiPlasma) . "') and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > " . $_SESSION['org']['period']['start'] . ")";
		}
	} else {
		$whereKary = " and a.lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "' and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > " . $_SESSION['org']['period']['start'] . ")";
	}

	## UNTUK GROUP DMA
} else {
	## Gini dulu urgent di kebun/ kalau sempat nanti diganti parameter aplikasi
	if (getindukPT($_SESSION['empl']['lokasitugas']) == 'CAR' or getindukPT($_SESSION['empl']['lokasitugas']) == 'LAN') {
		$dataunitx = '';
		$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='CAR' and tipe in ('KEBUN')";
		$res = fetchdata($str);
		foreach ($res as $val) {
			if ($dataunitx == "") {
				$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
			} else {
				$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
			}
		}

		$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='LAN' and tipe in ('KEBUN')";
		$res = fetchdata($str);
		foreach ($res as $val) {
			if ($dataunitx == "") {
				$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
			} else {
				$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
			}
		}

		$whereKary = " and a.lokasitugas IN (" . $dataunitx . ") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > " . $_SESSION['org']['period']['start'] . ")";
	}

	if (getindukPT($_SESSION['empl']['lokasitugas']) == 'DMA' or getindukPT($_SESSION['empl']['lokasitugas']) == 'MHA') {
		$dataunitx = '';
		$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='DMA' and tipe in ('KEBUN')";
		$res = fetchdata($str);
		foreach ($res as $val) {
			if ($dataunitx == "") {
				$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
			} else {
				$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
			}
		}

		$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='MHA' and tipe in ('KEBUN')";
		$res = fetchdata($str);
		foreach ($res as $val) {
			if ($dataunitx == "") {
				$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
			} else {
				$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
			}
		}

		$whereKary = " and a.lokasitugas IN (" . $dataunitx . ") and statuskaryawan != 'Keluar' and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > " . $_SESSION['org']['period']['start'] . ")";
	}
}

## Mandor
$qMandor = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from " . $dbname . ".datakaryawan a left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 " . $whr . " " . $whereKary . " order by  b.namajabatan,a.namakaryawan asc";
$res = fetchdata($qMandor);
foreach ($res as $row) {
	$dkary = "";
	if ($row['subbagian'] != $param['divisi']) {
		$dkary = " [ " . $row['subbagian'] . " ]";
	}
	$d = $row['namajabatan'];
	if ($d != $n) {
		$optMandor .= "<optgroup label='" . $d . "'>";
	}

	if ($param['nikmandor'] == $row['karyawanid']) {
		$optMandor .= "<option value=" . $row['karyawanid'] . " selected>" . $row['namakaryawan'] . " [" . $row['nik'] . "]" . $dkary . "</option>";
	} else {
		$optMandor .= "<option value=" . $row['karyawanid'] . ">" . $row['namakaryawan'] . " [" . $row['nik'] . "]" . $dkary . "</option>";
	}

	$n = $d;
	if ($d != $n) {
		$optMandor .= "</optgroup>";
	}
}

# Mandor 1
if ($mdr1 != '') {
	$whr = " and a.kodejabatan in (" . $mdr1 . ")";
} else {
	$whr = " and b.namajabatan like '%mandor%I%' ";
}
$qMandor1 = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from " . $dbname . ".datakaryawan a " .
	"left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 " . $whr . " " . $whereKary . " order by  b.namajabatan,a.namakaryawan asc";
$d = $n = "";
$res = fetchdata($qMandor1);
foreach ($res as $row) {
	$dkary = "";
	if ($row['subbagian'] != $param['divisi']) {
		$dkary = " [ " . $row['subbagian'] . " ]";
	}
	$d = $row['namajabatan'];
	if ($d != $n) {
		$optMandor1 .= "<optgroup label='" . $d . "'>";
	}

	if ($param['nikmandor1'] == $row['karyawanid']) {
		$optMandor1 .= "<option value=" . $row['karyawanid'] . " selected>" . $row['namakaryawan'] . " [" . $row['nik'] . "]" . $dkary . "</option>";
	} else {
		$optMandor1 .= "<option value=" . $row['karyawanid'] . ">" . $row['namakaryawan'] . " [" . $row['nik'] . "]" . $dkary . "</option>";
	}
	$n = $d;
	if ($d != $n) {
		$optMandor1 .= "</optgroup>";
	}
}

# Asst
if ($asst != '') {
	$whr = " and a.kodejabatan in (" . $asst . ")";
} else {
	$whr = " and (b.namajabatan like '%asst%' or " . " b.namajabatan like '%asist%'  or namajabatan like '%assist%') and (namajabatan like '%div%'  or namajabatan like '%afd%' or namajabatan like '%kebun%' or namajabatan like '%rawat%' or namajabatan like '%pemel%' or namajabatan like '%panen%')";
}
$qAsst = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from " . $dbname . ".datakaryawan a " .
	"left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 " . $whr . " " . $whereKary . " " . $divisix . " order by b.namajabatan,a.namakaryawan asc";
$d = $n = "";
$res = fetchdata($qAsst);
foreach ($res as $row) {
	if ($row['subbagian'] != '') {
		$row['subbagian'] = $row['subbagian'];
	} else {
		$row['subbagian'] = $row['lokasitugas'];
	}
	$d = $row['namajabatan'];
	if ($d != $n) {
		$optAsst .= "<optgroup label='" . $d . "'>";
	}
	$optAsst .= "<option value=" . $row['karyawanid'] . ">" . $row['namakaryawan'] . " [" . $row['nik'] . "]</option>";
	$n = $d;
	if ($d != $n) {
		$optAsst .= "</optgroup>";
	}
}

# Kerani
if ($krn != '') {
	$whr = " and a.kodejabatan in (" . $krn . ")";
} else {
	$whr = " and (b.namajabatan like '%krani%panen%' or " . " b.namajabatan like '%kerani%panen%' or b.namajabatan like '%harves%clerk%') and (b.namajabatan not like '%account%' and b.namajabatan not like '%akunt%' and b.namajabatan not like '%Store%' and b.namajabatan not like '%gudang%' and b.namajabatan not like '%civil%') and a.lokasitugas not like '%M' ";
}
$qKerani = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from " . $dbname . ".datakaryawan a " .
	"left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 " . $whr . " " . $whereKary . " order by b.namajabatan,  a.namakaryawan asc";
$d = $n = "";
$res = fetchdata($qKerani);
foreach ($res as $row) {
	if ($row['subbagian'] != '') {
		$row['subbagian'] = $row['subbagian'];
	} else {
		$row['subbagian'] = $row['lokasitugas'];
	}
	$d = $row['namajabatan'];
	if ($d != $n) {
		$optKerani .= "<optgroup label='" . $d . "'>";
	}
	if ($param['kerani'] == $row['karyawanid']) {
		$optKerani .= "<option value=" . $row['karyawanid'] . " selected>" . $row['namakaryawan'] . " [" . $row['nik'] . "]</option>";
	} else {
		$optKerani .= "<option value=" . $row['karyawanid'] . ">" . $row['namakaryawan'] . " [" . $row['nik'] . "]</option>";
	}
	$n = $d;
	if ($d != $n) {
		$optKerani .= "</optgroup>";
	}
}

$arrJab = array("0" => "Normal", "1" => "Banjir");
foreach ($arrJab as $brs1 => $isi1) {
	@$optStatus .= "<option value=" . $brs1 . ">" . $isi1 . "</option>";
}

$optupload = "<option value=''>&nbsp;</option>";
$arrJab = array("1" => "Sudah Upload", "0" => "Belum Upload");
foreach ($arrJab as $brs1 => $isi1) {
	$optupload .= "<option value=" . $brs1 . ">" . $isi1 . "</option>";
}

$arrJenisPanen = array("0" => "Normal", "1" => "Panen HA");
foreach ($arrJenisPanen as $brs1 => $isi1) {
	$optJenisPanen .= "<option value=" . $brs1 . ">" . $isi1 . "</option>";
}

## HIDE BUAT BARU KEGIATAN PANEN (PERMINTAAN PALMA SESUAI TIKET SUPPORT)
$str = "select nilai,kodeorg from " . $dbname . ".setup_parameterappl where kodeparameter='HDBMKP' and kodeorg = '" . getindukPT($_SESSION['empl']['lokasitugas']) . "'";
$res = fetchdata($str);
$get_jabatan = explode(',', $res[0]['nilai']);

$hidden_tombol = 'hidden';
if ($res[0]['kodeorg'] == getindukPT($_SESSION['empl']['lokasitugas'])) {
	if (in_array($_SESSION['empl']['kodejabatan'], $get_jabatan)) {
		$hidden_tombol = '';
	}
} else {
	$hidden_tombol = '';
}

if (getindukPT($_SESSION['empl']['lokasitugas']) == 'PPP') {
	$hidden_jenispanen = 'hidden';
}


OPEN_BOX('', '<span class=judul>' . getMenu('kebun_panenx_spb') . '</span>', 'judul_header');
# === Header dan Pencarian data ===
echo "<div id=action_list>";
echo "<table>
     <tr valign=middle>
	 <td align=center " . $hidden_tombol . " style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset id=formpencarianheader><legend><b>" . $_SESSION['lang']['find'] . "</b></legend> 
         <table>
			<tr>
			   <td>" . $_SESSION['lang']['notransaksi'] . "</td><td>:</td>
			   <td><input type=text class=myinputtext id=notransaksisch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\" maxlength=21 onkeypress='enterkey(event,loaddata)' /> </td>

				<td>" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=divsch onchange='loaddata()' style=\"width:130px;\">" . $optdiv2 . "</select></td>
			
				<td>" . $_SESSION['lang']['posting'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=postingsrc onchange='loaddata()' style=\"width:130px;\">" . $optPos . "</select>
				</td>
				
				<td>" . $_SESSION['lang']['nospb'] . "</td><td>:</td>
			   <td><input type=text class=myinputtext id=nospbsch nkeypress=\"return_tanpa_kutip(event);\" style=\"width:130px;\"  onkeypress='enterkey(event,loaddata)' /> </td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['tanggalmulai'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' style='width:130px;' class='myinputtext' id='tglmulai' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/>
				</td>
				
				<td>" . $_SESSION['lang']['tanggalselesai'] . "</td> 
				<td>:</td>
				<td><input type='text' onchange='loaddata()' style='width:125px;' class='myinputtext' id='tglselesai' onmousemove='setCalendar(this.id)' onkeypress='return false'; readonly/>
				</td>
				
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=periodesch onchange='loaddata()' style=\"width:130px;\">" . $optprd . "</select>
				</td>
				
				<td>" . $_SESSION['lang']['upload'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=uploadsch onchange='loaddata()' style=\"width:135px;\">" . $optupload . "</select>
				</td>
			</tr>";

echo "<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
<button class=mybutton onclick=displayList(0)>" . $_SESSION['lang']['cancel'] . "</button>
<button id=loaddataexcel class=mybutton onclick=loaddataexcel()>" . $_SESSION['lang']['excel'] . "</button>
</td></td></tr></table>";
echo "</fieldset></td></tr></table> ";
echo "</div>";
CLOSE_BOX();

# === List data yang sudah tersimpan ===
echo "<div id=listData style=display:block>";
OPEN_BOX();

echo "<div class='table-scroll'  style=height:60vh>
			<table cellpadding=5 cellspacing=1 border=0 class=sortable style=width:99.9%>
				<thead>
					<tr class=rowheader>
						<th align=center width=30px>" . $_SESSION['lang']['nourut'] . "</th>
						<th align=center >" . $_SESSION['lang']['notransaksi'] . "</th>
						<th align=center >No BKM</th>
						<th align=center >" . $_SESSION['lang']['sumber'] . "</th>
						<th align=center>" . $_SESSION['lang']['noreferensi'] . "</th>
						<th align=center >" . $_SESSION['lang']['kebun'] . "</th>
						<th align=center >" . $_SESSION['lang']['divisi'] . "</th>
						<th align=center >" . $_SESSION['lang']['hari'] . "</th>
						<th align=center >" . $_SESSION['lang']['tanggal'] . "</th>
						<th align=center >" . $_SESSION['lang']['jjg'] . "</th>
						<th align=center >" . $_SESSION['lang']['mandor'] . "</th>
						<th align=center >" . $_SESSION['lang']['mandor'] . " 1</th>
						<th align=center >" . $_SESSION['lang']['keranipanen'] . "</th>
						<th align=center >" . $_SESSION['lang']['kontanan'] . "</th>
						<th align=center >" . $_SESSION['lang']['updateby'] . "</th>
						<th align=center colspan='8'>" . $_SESSION['lang']['action'] . "</th>
				</thead>
				<tbody id=contain> 
					<script>loaddata(0)</script>
				</tbody>
				<tfoot id=footData>
				</tfoot>
			</table>
		</div>";
CLOSE_BOX();
echo "</div>";

# === Form header input data ===
echo "<div id=header style=display:none>";
OPEN_BOX('', '', 'header_trans');
echo "<fieldset style=float:left>
		<legend>Header</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td><input id=notransaksi style='width:145px;' class='myinputtext' disabled/></td>
				
				<td>&nbsp;" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=kodeorg>" . $optorg . "</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['divisi'] . "</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=divisi>" . $optdiv . "</select></td>
				
				<td>&nbsp;" . $_SESSION['lang']['mandor'] . "</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=mandor>" . $optMandor . "</select>
					<!--<img id='mandor' onclick=z.elSearch('mandor',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>--></td>
				
				<td " . $hidden_jenispanen . ">Jenis Panen </td> 
				<td " . $hidden_jenispanen . ">:</td>
				<td " . $hidden_jenispanen . "><select class=select2 style=\"width:150px;\" id=jenispanen>" . $optJenisPanen . "</select></td>
			</tr> 
			<tr>
				<td>Nomor BKM</td> 
				<td>:</td>
				<td><input disabled id=nobkm style='width:145px;' class='myinputtext'/></td>
				
				<td>&nbsp;" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text class=myinputtext style='width:145px;' id=tgl onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 readonly/></td>
				
				<td>&nbsp;" . $_SESSION['lang']['mandor'] . " 1</td> 
				<td>:</td>
				<td><select class=select2 style=\"width:150px;\" id=mandor1>" . $optMandor1 . "</select>
				</td>
				
				<td>&nbsp;" . $_SESSION['lang']['keranipanen'] . "</td> 
				<td>:</td>
				<td>
					<select class=select2 style=\"width:150px;\" id=kerani>" . $optKerani . "</select>
				</td>
				
				<td>&nbsp;" . $_SESSION['lang']['kontanan'] . "</td> 
				<td>:</td>
				<td>
					<input type='checkbox' id='kontanan' style='vertical-align:middle'/>
				</td>
									
				<td rowspan=3>
					<fieldset>
                            <b>" . $_SESSION['lang']['keterangan'] . " :</b><br>
                            &nbsp;- <input type='checkbox' checked disabled> : Kontanan<br>
                            &nbsp;- <input type='checkbox' disabled> : Tidak Kontanan 
					</fieldset>
				</td>

				<td style=display:none>&nbsp;" . $_SESSION['lang']['nikasisten'] . "</td> 
				<td style=display:none>:</td>
				<td style=display:none><select class=select2 style=\"width:150px;\" id=asst>" . $optAsst . "</select>
				</td>";

echo "</tr>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=tomboldetail class=mybutton onclick=addHeader()>" . $_SESSION['lang']['save'] . "</button>
					<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
				</td>
				<input type=hidden id=notphold>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=mode value='baru'>
				<input type=hidden id=modedt value='baru'>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();
echo "</div>";

# === Form Detail Input Data ===
echo "<div id=detailx style=display:none>";
OPEN_BOX();
echo "<div id=detail style=display:none>";
echo "</div>";
CLOSE_BOX();
echo "</div>";

echo close_body();
?>