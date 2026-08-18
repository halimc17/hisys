<?php
//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script languange=javascript1.2 src='js/sdm_2amprahgaji.js'></script>

<?
// $optunit="<option value=''></option>";

// $whereunit='';
// if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
// 	$whereunit=" and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
// }
// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$whereunit." and tipe!='HOLDING' and 
// 		length(kodeorganisasi)=4 order by namaorganisasi asc ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
// }

$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	if($key==$_SESSION['empl']['lokasitugas']){
		$optunit.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unit=$key;
	}else{
		$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}


$optPer="";
$str="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc limit 12";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optPer.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_2amprahgaji').'</span>');


echo"<br>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:168px;\" onchange=fillblank()>".$optunit."</select></td>
                </tr>
				
				<tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per style=\"width:168px;\" onchange=fillblank()>".$optPer."</select></td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=preview() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=excel() class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"
<!--<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>-->
<div id='both_report'>
<div id='head_tableboth' style='width:100%';>

<a title='Full Screen' class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' style='float:right;margin-right:10px'>
                <img title='Full Screen' class=resicon src='images/full-screen.png'>
            </a>
            <a title='Fixed Header Table' class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' style='float:right;margin-right:10px;' >
                <img title='Fixed Header Table' class=resicon src=images/fix-header.gif>
            </a>
<div id='printContainer' style='overflow:auto;width:100%'; >
</div></div></div><!--</fieldset>-->";

CLOSE_BOX();
echo close_body();


?>