<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/log_5rekbank.js'></script>

<?php

	
OPEN_BOX('','<span class=judul>'.strtoupper('Rekening Bank').'</span>');
//print_r($_SESSION['empl']['regional']);

	$sql="select namasupplier,supplierid,kota,kodekelompok from ".$dbname.".log_5supplier where  status=1 order by kodekelompok,namasupplier asc";    
	
	$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);
    while($res=$query->fetch())
    {
       @$optSupplier.="<option value='".$res['supplierid']."'>".$res['kodekelompok']." - ".$res['namasupplier']."</option>";
    }

echo"<fieldset style='width:450px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['supplier']."</td>
                    <td>:</td>
                    <td><select id=sup style=\"width:300px;\">".$optSupplier."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['bank']."</td> 
                    <td>:</td>
                    <td><input type=text  id=bank nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:300px;\"></td>
                </tr>
				 <tr>
                    <td>Rekening</td> 
                    <td>:</td>
                    <td><input type=text  id=rek nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:300px;\"></td>
                </tr>
				 <tr>
                    <td>Atas Nama</td> 
                    <td>:</td>
                    <td><input type=text  id=an nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:300px;\"></td>
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