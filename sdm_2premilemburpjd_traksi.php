<?php

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/sdm_2premilemburpjd_traksi.js?v=<?php echo time(); ?>'></script>

<?php

OPEN_BOX('','<span class=judul>'.getMenu('sdm_2premilemburpjd_traksi').'</span><br>');

## GET UNIT
$optUnit='';
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
$d=$induk[$key];
if($d!=$n){			
    $optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
}

if($key==$_SESSION['empl']['lokasitugas']){
    $optUnit.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
    $unit=$key;
}else{
    $optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			
}
$n=$d;
if($d!=$n){			
    $optUnit.="</optgroup>";
}
}

##GET SUBUNIT
$optSubUnit="<option value='all'>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$unit."' and tipe IN ('TRAKSI','WORKSHOP') order by kodeorganisasi";
$res=fetchdata($str);
foreach($res as $val){
if($val['tipe'] == 'TRAKSI'){
    $sel ='selected';
}else{
    $sel ='';
}
$optSubUnit.="<option value='".$val['kodeorganisasi']."' ".$sel.">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

## GET PERIODE
$optPeriode="";
$str="select periode from ".$dbname.".sdm_5periodegaji group by periode order by periode desc";
$res=fetchdata($str);
foreach($res as $val){
$optPeriode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
}

## GET TiPEKARYAWaN
$optTipe="<option value='all'>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif=1 ";
$res=fetchdata($str);
foreach($res as $val){
$optTipe.="<option value='".$val['id']."'>".$val['tipe']."</option>";
}

## FILTER REPORT ##
echo"
<fieldset style=float:left>
<table cellspacing=1 cellpadding=2>
    <tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td>
            <select class=select2 id='unit' style='min-width:150px' onchange=\"getsubunit()\">".$optUnit."</select>
        </td>
    </tr>
    <tr >
        <td>Sub ".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td>
            <select class=select2 id='subunit' style='min-width:246px'>".$optSubUnit."</select>
        </td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['tipekaryawan']."</td>
        <td>:</td>
        <td>
            <select class=select2 id='tipekaryawan' style='min-width:246px'>".$optTipe."</select>
        </td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>:</td>
        <td>
            <select class=select2 id='periode' style='min-width:246px'>".$optPeriode."</select>
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

echo"<fieldset style=float:left>
<div>
<legend>Note</legend>
<hr>
<table  cellpadding=5 cellspacing=1 border=0 style='font-weight:bold'>
	<tr >
		<td style='width:20px;background:red'>&nbsp;</td>
		<td style='background-color:#d1e3fa !important; color:black;border:unset !important'>: Karyawan Memiliki Premi dan Lembur Dalam 1 Tanggal</td>
	</tr>
</table>
</div>
</fieldset> ";
CLOSE_BOX();

OPEN_BOX();
echo"<div  class='table-scroll' style='height:500px;overflow:auto;' id=printContainer></div>";
CLOSE_BOX();
echo close_body();
?>