<?php

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/lm_rekapsengketatanah.js?v=<?php echo time(); ?>'></script>

<?php

OPEN_BOX('','<span class=judul>'.getMenu('lm_rekapsengketatanah').'</span><br>');

## GET PT
$optPT="<option value='all'>".$_SESSION['lang']['pilihdata']."</option>";
$unit='';
$arrPT = getOrgDetail(3);
foreach($arrPT as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optPT.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
    $optPT.="<option value='".$key."'>".$key." - ".$val."</option>";			
    
    $n=$d;
    if($d!=$n){			
        $optPT.="</optgroup>";
    }
	
}

## GET UNIT
$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$unit='';
$arrUnit = getOrgDetail(23);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	$opttipeorg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$key."'");

	$optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			
	
	$n=$d;
	if($d!=$n){			
		$optUnit.="</optgroup>";
	}	
}

## GET PERIODE
$optPeriode="";
$str="select DATE_FORMAT(tanggal, '%Y-%m')  as periode from ".$dbname.".kebun_curahhujan group by periode order by periode desc";
$res=fetchdata($str);
foreach($res as $val){
	$optPeriode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
}

## FILTER REPORT ##
echo"
<fieldset style=float:left>
	<table cellspacing=1 cellpadding=2>
		<tr>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='pt' style='width:246px' onchange=\"getUnit()\">".$optPT."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='unit' style='width:246px' >".$optUnit."</select>
			</td>
		</tr>
		<tr hidden>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='periode' style='width:246px'>".$optPeriode."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button onclick=\"preview('html',event)\" class='mybutton'>".$_SESSION['lang']['preview']."</button>
				<button onclick=\"preview('excel',event)\" class='mybutton'>".$_SESSION['lang']['excel']."</button>
			</td>
		</tr>
	</table></fieldset>";
echo"</div>";
CLOSE_BOX();


OPEN_BOX();
echo"<div  class='table-scroll' style='height:500px;overflow:auto;' id=printContainer></div>";
CLOSE_BOX();
echo close_body();
?>