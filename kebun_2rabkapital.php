<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include ('master_mainMenu.php');
?>

<link rel="stylesheet" type="text/css" href="style/zTable.css">
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/kebun_2rabkapital.js?v=<?php echo time(); ?>></script>

<?

// BOX ATAS

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";


$str="select * from ".$dbname.".organisasi where tipe='pt' ";
$res=fetchdata($str);
foreach ($res as $key => $val) {
	$optpt.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

$str="select distinct tahunbudget from ".$dbname.".bgt_budget order by tahunbudget desc";
$res=fetchdata($str);
$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach ($res as $key => $val) {
	$optperiode.="<option value='".$val['tahunbudget']."'>".$val['tahunbudget']."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu ('kebun_2rabkapital').'</span><br>');
$arrht= "###pt###unit###tahunprd";
// $arrht= "###pt###tahunprd";
echo "<fieldset style='float: left;'>";
echo "<legend><b>Form</b></legend>";
echo "<table>";
echo"<tr>	
		<td>".$_SESSION['lang']['pt']."</td>
		<td>:</td>
		<td><select id='pt' style='width:170px' onchange=getunit()>".$optpt."</select></td>
</tr>";
echo"<tr>	
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select id='unit' style='width:170px'></select></td>
</tr>";

echo"<tr>	
		<td>".$_SESSION['lang']['tahun']."</td>
		<td>:</td>
		<td><select id='tahunprd' style='width:170px'>".$optperiode."</select></td>
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

OPEN_BOX('','<span class=judul>List Data</span>');
// echo "<fieldset style='float: left;'>";
// echo "<legend> List Data</legend>";
echo "<div id=container class='table-scroll'></div>";
// echo "</fieldset>";


CLOSE_BOX('','');


CLOSE_BOX('','');
echo close_body();

?>