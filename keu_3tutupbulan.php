<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

#=== Start ===
echo open_body();
require_once('lib/zSelect2.php');
?>
<!-- Includes -->
<script language=javascript1.2 src=js/zTools.js></script>
<script language=javascript1.2 src=js/keu_3tutupbulan.js?ver=2.1></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<script language=javascript src=js/zSelect2.js?ver=1></script>
<?php
#====== Controller ======
# Options
$optOrg = getOrgDetail(9);
$optOrg2=array();
$optPeriod= array(""=>$_SESSION['lang']['pilihdata']);
$optOrg2['']=$_SESSION['lang']['pilihdata'];
foreach ($optOrg as $key => $val) {
	$optTipe=makeOption($dbname,"organisasi","kodeorganisasi,tipe","kodeorganisasi='".$key."'");
	if($optTipe[$key]!='HOLDING'){
		$rPrdAkn=array();
		$sPrdAkn="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$key."' and tutupbuku=0 order by periode desc limit 1";
		$rPrdAkn=fetchData($sPrdAkn);
		if(!empty($rPrdAkn)){
			$optOrg2[$key]=$val;
		}
	}
}
#====== View ======
# Menu
include('master_mainMenu.php');

# Form (kode organisasi dan periode memakai select2)
$optSelOrg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach ($optOrg2 as $kodeOpt => $namaOpt) {
	if ($kodeOpt == '') { continue; }
	$optSelOrg .= "<option value='".$kodeOpt."'>".$namaOpt."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('keu_3tutupbulan').'</span><br>');
echo "<fieldset style=float:left>
		<legend>".$_SESSION['lang']['form']."</legend>
		<table cellspacing='1' border='0'>
			<tr>
				<td>".$_SESSION['lang']['kodeorg']."</td>
				<td>:</td>
				<td><select id='kodeorg' class=select2 onchange='changeperiode(this)' style='width:250px'>".$optSelOrg."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select id='periode' class=select2 style='width:250px'><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td><button class=mybutton onclick='tutupBuku()'>".$_SESSION['lang']['tutupbuku']."</button></td>
			</tr>
		</table></fieldset>";
CLOSE_BOX();
#=== End ===
close_body();
?>