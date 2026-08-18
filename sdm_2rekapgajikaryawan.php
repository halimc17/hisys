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

<?
$optOrg="<option value=''></option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$vOrg = "";
}
else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    $vOrg = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
}

else{
	$vOrg = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$vOrg." order by namaorganisasi asc ";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}
$optPer="";
$iPer="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc limit 24";
$nPer=$owlPDO->query($iPer) or die(print " Gagal: ".PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while($dPer=$nPer->fetch()){
	$optPer.="<option value=".$dPer['periode'].">".$dPer['periode']."</option>";
}

$optTp="<option value=''>".$_SESSION['lang']['all']."</option>";
$iTp="select * from ".$dbname.".sdm_ho_component order by name asc";
$nTp=$owlPDO->query($iTp) or die(print " Gagal: ".PDOException::getMessage());
$nTp->setFetchMode(PDO::FETCH_ASSOC);
while($dTp=$nTp->fetch()){
	$optTp.="<option value=".$dTp['id'].">".$dTp['name']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_2rekapgajikaryawan').'</span>');


echo"<br>";
$arr="##unit##per";	
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:168px;\">".$optOrg."</select></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per style=\"width:168px;\">".$optPer."</select></td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=zPreview('sdm_slave_2rekapgajikaryawan','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'sdm_slave_2rekapgajikaryawan.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();








?>