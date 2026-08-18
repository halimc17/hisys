<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript src='js/pmn_5kepada.js'></script>
<?php
OPEN_BOX('','<span class=judul>'.getMenu('pmn_5kepada').'</span>');
//print_r($_SESSION['empl']['regional']);
echo"<br><fieldset style='float:left;'>";
    echo"<legend>form</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                 
                <tr>
                    <td>".$_SESSION['lang']['kepada']."</td> 
                    <td>:</td>
                    <td>
						<input type=text  id=kepada nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:100px;\">
						<input type=hidden id=method value='insert'>
						<input type=hidden id=id value=''>
					</td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['alamat']."</td> 
                    <td>:</td>
                    <td><input class=myinputtext id=alamat style=width:300px; rows=5 onkeypress=return tanpa_kutip(event);></td>
                </tr>
                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
								<button class=mybutton onclick=clearForm()>".$_SESSION['lang']['cancel']."</button>
                              
                        </td>
                </tr>

        </table></fieldset>";
       

CLOSE_BOX();//                        <input type=hidden id=method value='insert'>
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