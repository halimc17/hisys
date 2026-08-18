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





$optOrg="";
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
$iPer="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc limit 12";
$nPer=$owlPDO->query($iPer) or die(print " Gagal: ".PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while($dPer=$nPer->fetch())
{
	$optPer.="<option value=".$dPer['periode'].">".$dPer['periode']."</option>";
}

$optTp="<option value=''>".$_SESSION['lang']['all']."</option>";
$iTp="select * from ".$dbname.".sdm_ho_component order by name asc";
$nTp=$owlPDO->query($iTp) or die(print " Gagal: ".PDOException::getMessage());
$nTp->setFetchMode(PDO::FETCH_ASSOC);
while($dTp=$nTp->fetch())
{
	$optTp.="<option value=".$dTp['id'].">".$dTp['name']."</option>";
}

$optTipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$iTipe="select * from ".$dbname.".sdm_5tipekaryawan where id between 1 and 6 ";
$nTipe=$owlPDO->query($iTipe) or die(print " Gagal: ".PDOException::getMessage());
$nTipe->setFetchMode(PDO::FETCH_ASSOC);
while($dTipe=$nTipe->fetch())
{
    $optTipe.="<option value=".$dTipe['id'].">".$dTipe['tipe']."</option>";
}

$optJab="<option value=''>".$_SESSION['lang']['all']."</option>";
$iJab="select * from ".$dbname.".sdm_5jabatan order by namajabatan asc";
$nJab=$owlPDO->query($iJab) or die(print " Gagal: ".PDOException::getMessage());
$nJab->setFetchMode(PDO::FETCH_ASSOC);
while($dJab=$nJab->fetch())
{
    $optJab.="<option value=".$dJab['kodejabatan'].">".$dJab['namajabatan']."</option>";
}

$frm[0]='';
$frm[1]='';

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['summary'] ." ".$_SESSION['lang']['slipGaji']).'</span>');
$arr="##kdorg##per1##per2##kom##tipekar";	
$frm[0].="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:168px;\">".$optOrg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per1 style=\"width:70px;\">".$optPer."</select> S/D 
                        <select id=per2 style=\"width:70px;\">".$optPer."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['komponenpayroll']."</td>
                    <td>:</td>
                    <td><select id=kom style=\"width:168px;\">".$optTp."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tipekaryawan']."</td>
                    <td>:</td>
                    <td><select id=tipekar style=\"width:168px;\">".$optTipe."</select></td>
                </tr>

                <tr>
                    <td><td><td>
                    <button onclick=zPreview('sdm_slave_2totalkomponengaji','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'sdm_slave_2totalkomponengaji.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";

$frm[0].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1220px'; >
</div></fieldset>";//<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>

$arrv="##kdorgv##per1v##per2v##komv##tipekarv##jabv";
$frm[1].="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=kdorgv style=\"width:168px;\">".$optOrg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per1v style=\"width:70px;\">".$optPer."</select> S/D 
                        <select id=per2v style=\"width:70px;\">".$optPer."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['komponenpayroll']."</td>
                    <td>:</td>
                    <td><select id=komv style=\"width:168px;\">".$optTp."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tipekaryawan']."</td>
                    <td>:</td>
                    <td><select id=tipekarv style=\"width:168px;\">".$optTipe."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['jabatan']."</td>
                    <td>:</td>
                    <td><select id=jabv style=\"width:168px;\">".$optJab."</select></td>
                </tr>

                <tr>
                    <td><td><td>
                    <button onclick=zPreview('sdm_slave_2totalkomponengajiv','".$arrv."','printContainerv') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'sdm_slave_2totalkomponengajiv.php','".$arrv."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
$frm[1].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainerv' style='overflow:auto;height:350px;max-width:1230px'; >
</div></fieldset>";


$hfrm[0]=$_SESSION['lang']['detail'].' / '.$_SESSION['lang']['karyawan'];
$hfrm[1]=$_SESSION['lang']['rekap'].' / '.$_SESSION['lang']['jabatan'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1250);	

CLOSE_BOX();
echo close_body();








?>