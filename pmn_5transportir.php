<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/pmn_5transportir.js'></script>



<?php

$optsup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$ha="SELECT namasupplier,`supplierid` FROM ".$dbname.".log_5supplier WHERE status='1' and left(kodekelompok,3)='T00' "
        . " order by namasupplier asc";
//$hi=mysql_query($ha) or die (mysql_error());
//while ($hu=mysql_fetch_assoc($hi))
$hi=$owlPDO->query($ha) or die(print " Gagal: ".PDOException::getMessage());
$hi->setFetchMode(PDO::FETCH_ASSOC);
while($hu=$hi->fetch())
{
	$optsup.="<option value=".$hu['supplierid'].">".$hu['namasupplier']."</option>";
}


OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['transporter']).'</span>');
//print_r($_SESSION['empl']['regional']);
echo"<br><fieldset style='float:left;'>";
    echo"<legend>".$_SESSION['lang']['entryForm']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
               
                <tr>
                    <td>".$_SESSION['lang']['transporter']."</td>
                    <td>:</td>
                    <td><select id=tran style=\"width:175px;\">".$optsup."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['nopol']." </td> 
                    <td>:</td>
                    <td><input type=text maxlength=10 id=nopol nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:171px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['supir']." </td> 
                    <td>:</td>
                    <td><input type=text maxlength=30 id=driv nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:171px;\"></td>
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