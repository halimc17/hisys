<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_2daftarbarang.js?v=1.2"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
echo"<div id=action_list'>";
if($_SESSION['language'] == 'EN'){
	$zz = 'kelompok1 as kelompok';
}else{
	$zz = 'kelompok';
}

## Pengambilan kelompok barang dari table kelompok barang
$optkelompok = "<option value=''></option>";
$optsearch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select kode,".$zz." from ".$dbname.".log_5klbarang order by kelompok asc";
$res=fetchdata($str);
foreach($res as $val){
	$optkelompok.="<option value='".$val['kode']."'>".$val['kelompok']. " [ ".$val['kode']." ] </option>";
	$optsearch.="<option value='".$val['kode']."'>".$val['kelompok']. " [ ".$val['kode']." ] </option>";
}

## Pengambilan sub kelompok barang dari table sub kelompok barang
$optsubkelompok = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select kode, namasubkelompok from ".$dbname.".log_5subklbarang order by namasubkelompok asc";
$res=fetchdata($str);
foreach($res as $val){
	$optsubkelompok.="<option value='".$val['kode']."'>".$val['namasubkelompok']. " [ ".$val['kode']." ] </option>";
}

## Pengambilan kelompok gudang dari table organisasi
$optsearchgdg = "<option value=All>" . $_SESSION['lang']['all'] . "</option>";
$optgudang = '';
$unitDetailAkses = getOrgDetail(2);
$str="select * from ".$dbname.".organisasi where tipe='GUDANG' and induk in (".$unitDetailAkses.") order by kodeorganisasi";
$res=fetchdata($str);
foreach($res as $val){
	$optgudang.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	$optsearchgdg.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('log_2daftarbarang').'</span><br>');

echo "<fieldset style='float:left'>
	<legend><b>".$_SESSION['lang']['find']."</b></legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['namabarang']."</td>
			<td>:</td>
			<td>
				<input placeholder='".$_SESSION['lang']['all']."' type=text id=txtcari class=myinputtext style=width:245px onkeypress=\"return tanpa_kutip(event);\" maxlength=30>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['on']."   ".$_SESSION['lang']['kelompokbarang']."</td>
			<td>:</td>
			<td>
				<select class=select2 id=kelbrg style='width:250px;' onchange=\"getsubkelompok()\">" . $optsearch . "</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['on']."   ".$_SESSION['lang']['subkelompokbarang']."</td>
			<td>:</td>
			<td>
				<select class=select2 id=subklbarang  style='width:250px;'>" . $optsubkelompok . "</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['gudang']."</td>
			<td>:</td>
			<td>
				<select class=select2 id=gdg style='width:250px;'>".$optgudang."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=cariBarang()>" . $_SESSION['lang']['preview'] . "</button>
				<button class=mybutton onclick=\"fisikKeExcel(event,'log_laporanDaftarBarang_Excel.php')\">" . $_SESSION['lang']['excel'] . "</button>
			</td>
		</tr>
	</table>
</fieldset>";
    ?>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>
<div id='list_daftarbrg' class='table-scroll' style='height:400px;overflow:auto;width:100%;'></div>
<input type="hidden" name="method" id="method"  /> 
<input type="hidden" id="no_po" name="no_po" />
<input type="hidden" name="user_login" id="user_login" value="<?php echo $_SESSION['standard']['userid'] ?>" />

<?php
CLOSE_BOX();
echo close_body();
?>