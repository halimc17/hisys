<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
include_once('lib/utilities.php');
?>

<script type="text/javascript" src="js/kebun_2panenspb.js?v=<?php echo time(); ?>" /></script>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
</script>

<?php
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2panenspb').'</span>');
## GET UNIT
$optunit = "<option value='' hidden>".$_SESSION['lang']['pilihdata']."</option>";
// $str="select kodeorganisasi as a, namaorganisasi as b from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi) = 4 AND tipe = 'KEBUN'";
// $optunit = createOption('Organization', $str, false, true);

foreach(getOrgDetail(23) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optunit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optunit.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}


$tanggalawal=date('01-m-Y');
$tanggalakhir=date('d-m-Y');

echo"<fieldset style='margin-top:10px'>
	<legend>".$_SESSION['lang']['header']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select class='select2' style='width:192px' id='unit' onchange='getDivisi(this.value)'>".$optunit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['divisi']."</td>
			<td>:</td>
			<td>
				<select class='select2' style='width:192px' id='divisi'></select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<input id='tanggalawal' class='myinputtext' onkeypress=\"return tanpa_kutip(event)\" style=\"width:80px\" readonly=\"readonly\" onmousemove=\"setCalendar(this.id)\" type=\"text\" readonly value='".$tanggalawal."'> s/d 
				<input id='tanggalakhir' class='myinputtext' onkeypress=\"return tanpa_kutip(event)\" style=\"width:80px\" readonly=\"readonly\" onmousemove=\"setCalendar(this.id)\" type=\"text\" readonly value='".$tanggalakhir."'>
			</td>
		</tr>
		<tr>
			<td colspan='2'></td>
			<td>
				<button class='mybutton' id='previewButton'>".$_SESSION['lang']['preview']."</button>
				<button class='mybutton' id='excelButton'>".$_SESSION['lang']['excel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo"<div id='listForm'></div>";
CLOSE_BOX();

echo close_body();


function createOption($tipe, $query, $seluruhnya = false, $grouping = false){
	global $utilities;

	$a 		= "";
	$b 		= "";
	$label 	= "";

	$opt = "<option value='' hidden>".$_SESSION['lang']['pilihdata']."</option>";
	if ($seluruhnya == true) {
		$opt = "<option value=''>".$_SESSION['lang']['all']."</option>";
	}
	$result  = fetchData($query, 'OBJECT');
	foreach ($result as $key => $value) {
		if ($tipe == 'Organization') {
			$a 		= $utilities['organization']['Parent'][$value->a];
			$label 	= $utilities['organization']['Name'][$a];
			
			if ($a != $b && $grouping == true) {
				$opt .= "<optgroup label='".$label."'>";
			}
		}

		
		$opt .= "<option value='".$value->a."'>".$value->b."</option>";

		
		if ($tipe == 'Organization') {
			$b = $utilities['organization']['Parent'][$value->a];

			if ($a != $b && $grouping == true) {
				$opt .= "</optgroup>";
			}
		}
	}

	return $opt;
}

?>