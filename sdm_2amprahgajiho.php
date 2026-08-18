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
<script languange=javascript1.2 src='js/sdm_2amprahgajiho.js'></script>

<?
$optpt="<option value=''></option>";

$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}


$optPer="";
$str="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc limit 12";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optPer.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_2amprahgajiho').'</span><br>');


echo"<br>";
$arr="##pt##per";	
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['pt']."</td>
                    <td>:</td>
                    <td><select id=pt style=\"width:168px;\">".$optpt."</select></td>
                </tr>
				
				<tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per style=\"width:168px;\">".$optPer."</select></td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=preview('previewawal') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=excel() class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1150px'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();


?>