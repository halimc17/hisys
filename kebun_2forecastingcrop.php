<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include ('master_mainMenu.php');
?>

<link rel="stylesheet" type="text/css" href="style/zTable.css">
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/kebun_2forecastingcrop.js?v=<?php echo time(); ?>></script>

<?

// BOX ATAS

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optregional="<option value=''>".$_SESSION['lang']['all']."</option>";


$str="select distinct subregional from ".$dbname.".bgt_regional_assignment where kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where tipe='kebun') order by subregional desc";
$res=fetchdata($str);
foreach ($res as $key => $val) {
	$optregional.="<option value='".$val['subregional']."'>".$val['subregional']."</option>";
}

$str="select distinct left(tanggal,7) as tanggal from ".$dbname.".pabrik_timbangan where left(tanggal,7)!='0000-00' order by tanggal desc limit 24";
$res=fetchdata($str);
$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach ($res as $key => $val) {
	$optperiode.="<option value='".$val['tanggal']."'>".$val['tanggal']."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu ('kebun_2forecastingcrop').'</span><br>');
$arrht= "###regional###periode";
echo "<fieldset style='float: left;'>";
echo "<legend><b>Form</b></legend>";
echo "<table>";
echo"<tr>	
		<td style='width:40px'>".$_SESSION['lang']['regional']."</td>
		<td>:</td>
		<td>
			<select id='regional' style='width:170px'>
			".$optregional."
			</select>
		</td>
</tr>";

echo"<tr>	
		<td style='width:40px'>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td>
			<select id='periode' style='width:170px'>
			".$optperiode."
			</select>
		</td>
</tr>";

echo"<tr>
		<td align=center colspan=2></td>
		<td>
		<button class=mybutton onclick=\"preview('html',event)\">".$_SESSION['lang']['preview']."</button>
		<button class=mybutton onclick=\"preview('excel',event)\">".$_SESSION['lang']['excel']."</button>
		
		</td>
</tr>";


echo "</table></fieldset>";
// echo "</table>";

// BOX ATAS

CLOSE_BOX('','');

// PREVIEW

OPEN_BOX('','List Data');
// echo "<fieldset>";
// echo "<legend> Form Output</legend>";
echo "<div id=container style='display:none;height:400px'; class='table-scroll'></div>";
// echo "</fieldset>";


CLOSE_BOX('','');


CLOSE_BOX('','');
echo close_body();

?>