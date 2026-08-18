<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript1.2 src='js/keu_5transaksipajak.js'></script>
<?php

OPEN_BOX('','<span class=judul>'.getMenu('keu_5tranksaksifakturpajak').'</span>');
echo"<fieldset style='width:450px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['id']."</td> 
                    <td>:</td>
                    <td><input type=text maxlength=2 id=id onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\" disabled></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['jenistransaksi']."</td> 
                    <td>:</td>
                    <td><input type=text  id=jenis onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:300px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['status']." Aktif / Non-Aktif</td> 
                    <td>:</td>
                    <td>
                        <input type=checkbox id=status checked>
                    </td>
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