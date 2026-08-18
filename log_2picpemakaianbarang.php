<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>

<script language="javascript" src="js/zMaster.js?v=<?php echo time(); ?>" /></script>
<script language="javascript" src="js/zSelect2.js?v=<?php echo time(); ?>" /></script>
<script type="text/javascript" src="js/log_2picpemakaianbarang.js?v=<?php echo time(); ?>" /></script>
<script language="javascript" src="js/zSelect2.js?v=<?php echo time(); ?>" /></script>
<?
echo"<div id=action_list'>";


$optBrg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optunit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

## Pengambilan barang dari table barang
$optkelompok = "<option value=''></option>";
$optsearch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select distinct(kodebarang) as kodebarang, namabarang, satuan from ".$dbname.".log_5masterbarang order by namabarang";
$res=fetchdata($str);
foreach($res as $val){
	$optBrg.="<option value='" . $val['kodebarang'] . "'>" . $val['namabarang'] . " (" . $val['kodebarang'] . ")</option>";
}

## Pengambilan unit dari table unit
$optkelompok = "<option value=''></option>";
$optsearch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$unitDetailAkses = getOrgDetail(2);
$str = "select distinct kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi where tipe like 'GUDANG%' and induk in (".$unitDetailAkses.") and kodeorganisasi in (select kodegudang  from " . $dbname . ".log_transaksiht) order by kodeorganisasi";
$res=fetchdata($str);
foreach($res as $val){
	$d=substr($val['kodeorganisasi'],0,4);
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optunit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optunit.="<option value='" . $val['kodeorganisasi'] . "'>" . $val['kodeorganisasi'] . " (" . $val['namaorganisasi'] . ")</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}


$optbarang = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

$datefirst = date("01-m-Y");
$datenow = date("d-m-Y");

OPEN_BOX('','<span class=judul>'.getMenu('log_2picpemakaianbarang').'</span><br>');

echo "<fieldset style='float:left'>
	<legend><b>".$_SESSION['lang']['find']."</b></legend>
	<table>
	<tr>
	   <td>" . $_SESSION['lang']['unit'] . "</td><td>:</td>
	   <td>
	     <select class='select2' id=unit style='width:170px;'>" . $optunit . "</select></td>
	 	</tr>
	 <tr>
	 <tr>
	   <td>" . $_SESSION['lang']['periode'] . "</td><td>:</td>
	   <td>
	   	<input type=text class=myinputtext id=periode onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".$datefirst."' readonly/> S/D
    	<input type=text class=myinputtext id=periode2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".$datenow."' readonly/></td>
	 </tr>
	 <tr>
	   <td>" . $_SESSION['lang']['kodebarang'] . "</td><td>:</td>
       <td>
			<select  class='select2' style='width:170px;' id=kodebarang>" . $optBrg . "</select>
			<img id=kodebarang_find onclick=z.elSearch('kodebarang',event) class=resicon src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
	   </td>
	 </tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=\"preview('html',event)\">" . $_SESSION['lang']['preview'] . "</button>
				<button class=mybutton onclick=\"preview('excel',event)\">" . $_SESSION['lang']['excel'] . "</button>
			</td>
		</tr>
	</table>
</fieldset>";

echo"<fieldset style=float:left>
<div>
<legend>Note</legend>
<hr>
<table  cellpadding=5 cellspacing=1 border=0 style='font-weight:bold'>
	<tr>
		<td style='width:20px;background:orange'>&nbsp;</td>
		<td style='background-color:#d1e3fa !important; color:black;border:unset !important'>:</td>
		<td style='background-color:#d1e3fa !important; color:black;border:unset !important'>Transaksi belum terposting dan nilai rupiah masih estimasi</td>
	</tr>
</table>
</div>
</fieldset> ";

CLOSE_BOX();

OPEN_BOX();
echo"<div  class='table-scroll' style='height:500px;overflow:auto;' id=printContainer></div>";
CLOSE_BOX();
echo close_body();
?>