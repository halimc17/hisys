<?php

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/kebun_2produksiharian.js?v=<?php echo time(); ?>'></script>

<?php

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2produksiharian').'</span><br>');


## GET INTI/PLASMA
$arrTipe=array("Seluruhnya"=>"Seluruhnya","Inti"=>"Inti","Plasma"=>"Plasma");
foreach($arrTipe as $val){
	$optTipe.="<option value='".$val."'>".$val."</option>";
}

## GET UNIT
$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
    $opttipeorg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	
	if($opttipeorg[$key] == 'KEBUN'){

		$optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			

		$n=$d;
		if($d!=$n){			
			$optUnit.="</optgroup>";
		}
	}
}

##GET SUBUNIT
$optSubUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe = 'AFDELING' order by kodeorganisasi";
$res=fetchdata($str);
foreach($res as $val){
	$optSubUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

## GET PERIODE
$optPeriode="";
$str="select CONCAT(YEAR(tanggalpanen), '-', LPAD(MONTH(tanggalpanen), 2, '0')) AS periode  from ".$dbname.".kebun_spbdt group by periode order by periode desc";
$res=fetchdata($str);
foreach($res as $val){
	$optPeriode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
}

## FILTER REPORT ##
echo"
<fieldset style=float:left>
<legend> FORM </legend>
	<table cellspacing=1 cellpadding=2 border=0>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td colspan=2>
				<select  class=select2 id='tipe' onchange=getorg() style='width:246px'>".$optTipe."</select>
			</td>
		</tr>
        <tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td colspan=2>
				<select  class=select2 id='unit' onchange=getdivisi() style='width:246px'>".$optUnit."</select>
			</td>
		</tr>
        <tr>
			<td>".$_SESSION['lang']['divisi']."</td>
			<td>:</td>
			<td colspan=2>
				<select class=select2 id='divisi' style='width:246px'>".$optSubUnit."</select>
			</td>
		</tr>
		<tr >
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td hidden>
				<select class=select2 id='periode' style='width:110px'>".$optPeriode."</select> S/D
			</td>
            <td>
                <select class=select2 id='periode2' style='width:246px'>".$optPeriode."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button onclick=\"preview('html',event)\" class='mybutton'>".$_SESSION['lang']['preview']."</button>
				<button onclick=\"preview('excel',event)\" class='mybutton'>".$_SESSION['lang']['excel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo"<div  class='table-scroll' style='height:500px;overflow:auto;' id=printContainer></div>";
CLOSE_BOX();
echo close_body();
?>