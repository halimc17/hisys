<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<!-- <link rel=stylesheet type=text/css href=style/zTable.css> 
<script language=javascript1.2 src=js/formTable.js></script>
-->
<script language=javascript src=js/keu_2alokasigaji.js?v=<?php echo time(); ?>></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<style type="text/css">
	.td {
		cursor: pointer;
	}
	.fix {
		max-height: 40vh;
		overflow-y: auto;
	}
	.table-scroll table {
		min-width: 0px !important;
	}
</style>

<?

#= buat box input
OPEN_BOX('','<span class=judul>'.getMenu('keu_2alokasigaji').'</span><br>');

$optpt = '<option value="" hidden>'.$_SESSION['lang']['pilihdata'].'</option>';
$optunit = '<option value="" hidden>'.$_SESSION['lang']['all'].'</option>';
$optperiode = '<option value="" hidden>'.$_SESSION['lang']['pilihdata'].'</option>';

## GET Nama PT
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d[$induk[$key]]=$induk[$key];
}
foreach($d as $val){
	$optpt.="<option value=".$val.">".$val." - ".getNamaOrg($val)."</option>";
}

## GET Periode
$str = "SELECT DISTINCT periode FROM ".$dbname.".sdm_5periodegaji ORDER BY periode DESC";
$res = fetchdata($str);
foreach ($res as $val) {
	$optperiode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
}
$optjenis="<option value='detail'>Detail per karyawan</option>";
$optjenis.="<option value='rekap'>Rekap per unit</option>";

echo "<fieldset style=float:left>";
echo "<legend>".$_SESSION['lang']['form']."</legend>";
echo "<div>";
echo "<table>";
echo "<tr>
		<td>".$_SESSION['lang']['pt']."</td>
	  	<td>:</td>
	  	<td>
	  		<select class=select2 id='pt' onchange=\"loadunit();\" style='width:177px'>
	  			".$optpt."
	  		</select>
	  	</td>
	</tr>";
echo "<tr>
		<td>".$_SESSION['lang']['unit']."</td>
	  	<td>:</td>
	  	<td>
	  		<select class=select2 id='unit' style='width:177px'>
	  			".$optunit."
	  		</select>
	  	</td>
	</tr>";
echo "<tr>
		<td>".$_SESSION['lang']['periode']."</td>
	  	<td>:</td>
	  	<td>
	  		<select class=select2 id='periode' style='width:177px'>
	  			".$optperiode."
	  		</select>
	  	</td>
	</tr>";
echo "<tr>
		<td>".$_SESSION['lang']['jenis']."</td>
	  	<td>:</td>
	  	<td>
	  		<select class=select2 id='jenis' style='width:177px'>
	  			".$optjenis."
	  		</select>
	  	</td>
	</tr>";
echo "<tr>
		<td colspan=2></td>
		<td>
			<button class=mybutton onclick=\"preview('html', event);\">".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=\"preview('excel', event);\">".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=\"preview('pdf', event);\">".$_SESSION['lang']['pdf']."</button>
		</td>
	</tr>";
echo "</table>";
echo "</div>";
echo "</fieldset>";

CLOSE_BOX();
#= tutup box input

#= buat data tersimpan
OPEN_BOX();
// echo "<div id=formlist style='display:none;margin-bottom: none !important'>";

// echo "<fieldset><legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['preview']."</legend>";
echo "<div id=listdata style=max-height:55vh class='table-scroll'></div>";
// echo "</fieldset>";

// echo "</div>";
CLOSE_BOX();
#= tutup data tersimpan

echo close_body();
?>