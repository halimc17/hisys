<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/log_2monitoringposo.js?v=1.4'></script>
<script type="text/javascript" src="js/log_link.js"></script>
<script language=javascript1.2 src="js/zSelect2.js?ver=1"></script>
<?php
OPEN_BOX('','<span class=judul>'.getMenu('log_2monitoringposo').'</span>');

## FILTER SEARCH

## GET PT
$optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
// $listpt = getOrgDetail(3);
// foreach($listpt as $key=>$val){
// 	$optpt.="<option value='".$key."'>".$val."</option>";
// }



$listOrg = getOrgDetail(3);
foreach($listOrg as $key => $value){
	$optpt .= '<option value="'.$key.'">'.$value.'</option>';
}

## GET UNIT
$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";

## STRATEGIES
$optstrategis.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optstrategis.="<option value='1'>Ya</option>";
$optstrategis.="<option value='0'>Tidak</option>";

## GET PURCHASER
$arrpuchaser=array();
$optpurchaser="<option value=''>".$_SESSION['lang']['all']."</option>";

$str="select distinct purchaserid as purchaser FROM ".$dbname.".log_5list_purchaser where managerid='".$_SESSION['standard']['userid']."'";
$res=fetchdata($str);
if(count($res)>0){
	foreach($res as $val){
		if(getNamaKaryawan($val['purchaser'])!=''){
			$arrpuchaser[$val['purchaser']]=getNamaKaryawan($val['purchaser']);
		}
	}
}else{	
	$str="select distinct(karyawanid) as purchaser from ".$dbname.".log_listverifikasi";
	$res=fetchdata($str);
	foreach($res as $val){
		if(getNamaKaryawan($val['purchaser'])!=''){
			$arrpuchaser[$val['purchaser']]=getNamaKaryawan($val['purchaser']);
		}
	}
}


asort($arrpuchaser);
foreach($arrpuchaser as $key=>$val){
	$optpurchaser.="<option value='".$key."'>".$val."</option>";			
}

echo"<div>
	<fieldset style='float: left;'>
	<legend><b>Form</b></legend>
	<table cellspacing='1' border='0' >
		<tr>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='pt' style=width:173px onchange=\"getunit();\">".$optpt."</select>
			</td>
			
			<td>".$_SESSION['lang']['nopp']."</td>
			<td>:</td>
			<td>
				 <td><input type=text id=nopp  style=width:169px  placeholder='Seluruhnya' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
			</td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='unit' style=width:173px>".$optunit."</select>
			</td>
			
			<td>".$_SESSION['lang']['nopo']."</td>
			<td>:</td>
			<td>
				<td><input type=text id=nopo style=width:169px  placeholder='Seluruhnya'  onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']." ".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<select class=select2 style=width:173px id='tipeperiode'>
					<option value='pr'>PR</option>
					<option value='po'>PO</option>
				</select>
			</td>
			
			<td>Strategies</td>
			<td>:</td>
			<td>
				<td>
					<select class=select2 style=width:173px  id='strategis'>".$optstrategis."</select>
				</td>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tgl1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('01-m-Y')."' readonly />
				<input type=text class=myinputtext id=tgl2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('d-m-Y')."' readonly />
			</td>
			
			<td>Purchaser</td>
			<td>:</td>
			<td>
				<td>
					<select class=select2 style=width:173px  id='purchaser'>".$optpurchaser."</select>
				</td>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button onclick=\"getlaporan(event,'html')\" class='mybutton' name='preview' id='preview'>Preview</button>
				<!--<button onclick=\"getlaporan(event,'pdf')\" class='mybutton' name='preview' id='preview'>PDF</button>-->
				<button onclick=\"getlaporan(event,'excel')\" class='mybutton' name='preview' id='preview'>Excel</button>
				<button onclick=\"batal()\" class='mybutton' name='preview' id='preview'>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
	</fieldset>
</div>";

CLOSE_BOX();

OPEN_BOX();
echo"<div class='table-scroll' id='printContainer' style='overflow:none;height:400px;'></div>";
CLOSE_BOX();

echo close_body();
?>