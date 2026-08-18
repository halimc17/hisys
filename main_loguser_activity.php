<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/main_loguser_activity.js?v=<?php echo time(); ?>></script>
<style type="text/css">
    .table-scroll table {
        min-width: 0px !important;
    }
</style>
<?

#= buat box input
OPEN_BOX('','<span class=judul>'.getMenu('main_loguser_activity').'</span>');

$optpt = "<option value=>".$_SESSION['lang']['all']."</option>";
$optunit = "<option value=>".$_SESSION['lang']['all']."</option>";
$optjbt = "<option value=>".$_SESSION['lang']['all']."</option>";
$optdpt = "<option value=>".$_SESSION['lang']['all']."</option>";
$optkry = "<option value=>".$_SESSION['lang']['all']."</option>";

$strpt = "SELECT kodeorganisasi, namaorganisasi FROM ".$dbname.".organisasi WHERE tipe = 'PT'";
$respt = fetchdata($strpt);
foreach ($respt as $key => $val) {
	$optpt .= "<option value=".$val['kodeorganisasi'].">".$val['namaorganisasi']."</option>";
}

$strjbt = "SELECT kodejabatan, namajabatan FROM ".$dbname.".sdm_5jabatan WHERE aktif = '1'";
$resjbt = fetchdata($strjbt);
foreach ($resjbt as $key => $val) {
	$optjbt .= "<option value=".$val['kodejabatan'].">".$val['namajabatan']."</option>";
}

$strdpt = "SELECT kode, nama FROM ".$dbname.".sdm_5departemen WHERE aktif = '1'";
$resdpt = fetchdata($strdpt);
foreach ($resdpt as $key => $val) {
	$optdpt .= "<option value=".$val['kode'].">".$val['nama']."</option>";
}

// $strkry = "SELECT DISTINCT karyawanid, namakaryawan, nik FROM ".$dbname.".datakaryawan WHERE tipekaryawan = '1' ORDER BY namakaryawan ASC";
// $reskry = fetchdata($strkry);
// foreach ($reskry as $key => $val) {
// 	$optkry .= "<option value=".$val['karyawanid'].">".$val['namakaryawan']." (".$val['nik'].")</option>";
// }

$strkry = "SELECT DISTINCT a.karyawanid, b.namakaryawan, b.nik FROM ".$dbname.".user a LEFT JOIN ".$dbname.".datakaryawan b ON a.karyawanid = b.karyawanid WHERE b.namakaryawan is not NULL ORDER BY b.namakaryawan ASC";
$reskry = fetchdata($strkry);
foreach ($reskry as $key => $val) {
	$optkry .= "<option value=".$val['karyawanid'].">".$val['namakaryawan']." (".$val['nik'].")</option>";
}

echo "<fieldset>";
echo "<legend>".$_SESSION['lang']['entryForm']."</legend>";
echo "<table>";
echo "<tr>
		<td>".$_SESSION['lang']['pt']."</td>
	  	<td>:</td>
	  	<td><select id=pt onchange=getunit(this.value) style=width:158px>".$optpt."</select></td>
	</tr>";
echo "<tr>
		<td>".$_SESSION['lang']['unit']."</td>
	  	<td>:</td>
	  	<td><select id=unit style=width:158px>".$optunit."</select></td>
	</tr>";
echo "<tr>
		<td>".$_SESSION['lang']['jabatan']."</td>
	  	<td>:</td>
	  	<td><select id=jabatan style=width:158px>".$optjbt."</select></td>
	</tr>";
echo "<tr>
		<td>".$_SESSION['lang']['departemen']."</td>
	  	<td>:</td>
	  	<td><select id=departemen style=width:158px>".$optdpt."</select></td>
	</tr>";
echo "<tr>
		<td>".$_SESSION['lang']['karyawan']."</td>
	  	<td>:</td>
	  	<td><select id=karyawan style=width:158px>".$optkry."</select><img id=karyawan onclick=z.elSearch('karyawan',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
	</tr>";
echo "<tr>
		<td>".$_SESSION['lang']['periode']."</td>
	  	<td>:</td>
	  	<td>
	  		<input type=text id=tgl1 class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:61px;>
	  		".$_SESSION['lang']['sd']."
	  		<input type=text id=tgl2 class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:61px;>
	  	</td>
	</tr>";
echo "<tr>
		<td colspan=2></td>
		<td>
			<button class=mybutton onclick=\"preview('html', event);\">".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=\"preview('excel', event);\">".$_SESSION['lang']['excel']."</button>
		</td>
	</tr>";
echo "</table>";
echo "</fieldset>";

CLOSE_BOX();
#= tutup 

#= buat data tersimpan
echo"<div id=loadpreview style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['list'].'</span>');

echo "<div id=listdata style=max-height:330px></div>";

echo"<br>";

CLOSE_BOX();
echo"</div>";
#= tutup data tersimpan

echo close_body();
?>