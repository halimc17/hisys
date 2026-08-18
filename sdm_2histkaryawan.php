<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script src="js/zReport.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">
<?
include('master_mainMenu.php');

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";
$res=fetchData($sOrg);
foreach($res as $row=>$rOrg){
	$optperiode.="<option value=".$rOrg['periode'].">".$rOrg['periode']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('sdm_2histkaryawan').'</span><br>');

$par="##unit##periode";
echo "	
	<fieldset style=float:left>
	<legend>Form</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td>
					<select id='unit' class='select2' style='width:180px;'>
						".$optorg."
					</select>&nbsp;
				</td>
				
			</tr>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td>
					<select id='periode' class='select2' style='width:180px;'>
						".$optperiode."
					</select>&nbsp;
				</td>
				
			<tr>
				<td></td><td></td>
				<td colspan=20>
					<button class=mybutton onclick=zPreview('sdm_slave_2histkaryawan','".$par."','container')>".$_SESSION['lang']['preview']."</button>
					<button class=mybutton onclick=zExcel(event,'sdm_slave_2histkaryawan.php','".$par."')>".$_SESSION['lang']['excel']."</button>
				</td>
			</tr>
		</table>
	</fieldset>"; 
	 

CLOSE_BOX();
OPEN_BOX();
echo "<div id=container class='table-scroll'></div>";
CLOSE_BOX();
echo close_body();
?>