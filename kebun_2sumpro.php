<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<link rel="stylesheet" type="text/css" href="style/zTable.css">
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/kebun_2sumpro.js?v=<?php echo time(); ?>></script>

<?

// $optRegion = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
// $str = "SELECT * FROM " . $dbname . ".bgt_regional_assignment  GROUP BY subregional asc";
// $res = fetchdata($str);
// foreach ($res as $key => $val) {
	//     @$optRegion .= "<option value='" . $val['subregional'] . "'>" . $val['subregional'] . "</option>";
	// }
	
// $optRegion = "<option value=''>" . $_SESSION['lang']['all'] . "</option>"; ###option
$str = "select distinct subregional from " . $dbname . ".bgt_regional_assignment where kodeunit in (select kodeorganisasi from " . $dbname . ".organisasi where tipe='kebun') order by subregional desc";
$res = fetchdata($str);
foreach ($res as $key => $val) {
	@$optRegion .= "<option value='" . $val['subregional'] . "'>" . $val['subregional'] . "</option>";
}


// $optperiode = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "SELECT tahunbudget FROM " . $dbname . ".bgt_produksi_kebun GROUP BY tahunbudget asc";
$res = fetchdata($str);
foreach ($res as $key => $val) {
    @$optperiode .= "<option value='" . $val['tahunbudget'] . "'>" . $val['tahunbudget'] . " </option>";
}


// for ($i = date('n'); $i >= date('n') - date('n') + 1; $i--) {
//     if (strlen($i) == 1) {
//         $ii = "0" . $i;
//     }
//     @$optperiode .= "<option value=" . date('Y') . "-" . $ii . ">" . date('Y') . "-" . $ii . "</option>";
// }
OPEN_BOX('', '<span class=judul>' . getMenu('kebun_2sumpro') . '</span><br>');
$arrht = "###unit###periode";
echo "<fieldset style='float: left;'>";
echo "<legend><b>Form</b></legend>";
echo "<table>";
echo "<tr>	
		<td style='width:40px'>Regional</td>
		<td>:</td>
		<td>
			<select id=region onchange=getkebun(); style='width:200px'>" . $optRegion . "</select>
		</td>
</tr>";

echo "<tr>	
		<td hidden style='width:40px'>" . $_SESSION['lang']['kebun'] . "</td>
		<td hidden>:</td>
		<td>
			<select id='kebun'  hidden style='width:170px'>
			</select>
			</td>
</tr>";

echo "<tr>	
		<td>" . $_SESSION['lang']['tahun'] . "</td>
		<td>:</td>
		<td>
			<select id='tanggal' style='width:80px'>
		" . $optperiode . "
		</select>  
		</td>		
</tr>";


echo "<tr>
		<td align=center colspan=2></td>
		<td>
		<button class=mybutton onclick=\"preview('html',event)\">" . $_SESSION['lang']['preview'] . "</button>
		<button class=mybutton onclick=\"preview('excel',event)\">" . $_SESSION['lang']['excel'] . "</button>
		
		</td>
</tr>";


echo "</table></fieldset>";
// echo "</table>";

// BOX ATAS

CLOSE_BOX('', '');

// PREVIEW

OPEN_BOX('', 'List Data');
// echo "<fieldset>";
// echo "<legend> Form Output</legend>";
echo "<div id=container style='display:none;height:350px'; class='table-scroll'></div>";
// echo "</fieldset>";
CLOSE_BOX('', '');

echo close_body();

?>