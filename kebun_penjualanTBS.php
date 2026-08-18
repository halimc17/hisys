<?php
//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');



?>

<script language=javascript1.2 src='js/kebun_penjualanTBS.js'></script>



<?php

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iOrg="select * from ".$dbname.".organisasi where tipe='KEBUN' order by namaorganisasi asc ";
$nOrg=$owlPDO->query($iOrg) or die(print " Gagal: ".PDOException::getMessage());
$nOrg->setFetchMode(PDO::FETCH_ASSOC);
while($dOrg=$nOrg->fetch())
{
    $optOrg.="<option value='".$dOrg['kodeorganisasi']."'>".$dOrg['kodeorganisasi']." - ".$dOrg['namaorganisasi']."</option>";
}

$optSup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iSup="select * from ".$dbname.".log_5supplier order by namasupplier asc";
$nSup=$owlPDO->query($iSup) or die(print " Gagal: ".PDOException::getMessage());
$nSup->setFetchMode(PDO::FETCH_ASSOC);
while($dSup=$nSup->fetch())
{
    $optSup.="<option value='".$dSup['supplierid']."'>".$dSup['namasupplier']."</option>";
}

$optPer="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iPer="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 12 ";
$nPer=$owlPDO->query($iPer) or die(print " Gagal: ".PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while($dPer=$nPer->fetch())
{
    $optPer.="<option value='".$dPer['periode']."'>".$dPer['periode']."</option>";
}



OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['penjualan']." ".$_SESSION['lang']['tbs']).'</span>');
//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='width:450px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unitkerja']."</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:175px;\">".$optOrg."</select></td>
                </tr>
                
                <tr>
                    <td>".$_SESSION['lang']['Pembeli']."</td>
                    <td>:</td>
                    <td><select id=sup style=\"width:175px;\">".$optSup."</select>
					<img id='sup' onclick=z.elSearch('sup',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
                </tr>
                
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per style=\"width:75px;\">".$optPer."</select></td>
                </tr>
                
                <tr>
                    <td>".$_SESSION['lang']['totalkg']."</td>
                    <td>:</td>
                    <td><input type=text id=kg  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=6 style=\"width:70px;\"> </td>
                </tr>
                    

                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                                <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
                        </td>
                </tr>

        </table></fieldset>
                        <input type=hidden id=method value='insert'>";
        


CLOSE_BOX();
?>



<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset style='width:450px;'>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>