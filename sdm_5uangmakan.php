<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/sdm_5uangmakan.js'></script>



<?php

$iReg="select * from ".$dbname.".bgt_regional order by nama asc";
$nReg=$owlPDO->query($iReg) or die(print " Gagal: ".PDOException::getMessage());
$nReg->setFetchMode(PDO::FETCH_ASSOC); 
while($dReg=  $nReg->fetch())
{
    $optReg.="<option value='".$dReg['regional']."'>".$dReg['nama']."</option>";
}

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['uangmakan']).'</span><br>');
//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='float:left;'>";
    echo"<legend>Form</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['regional']."</td> 
                    <td>:</td>
                    <td><select id=regional style=\"width:150px;\">".$optReg."</select></td>                
                </tr>
                <tr>
                    <td>Rupiah/Hari</td> 
                    <td>:</td>
                    <td><input type=text class=myinputtextnumber id=rupiah  onkeypress=\"return angka_doang(event);\" style=width:150px; /></td>
                </tr>
                

                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>Simpan</button>
                                <button class=mybutton onclick=cancel()>Hapus</button>
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
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>