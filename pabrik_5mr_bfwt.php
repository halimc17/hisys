<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/pabrik_5mr_bfwt.js'></script>

<?php

$optwt='';
$arrwt=getEnum($dbname,'pabrik_5mr_bfwt','kd_transaksi');
foreach($arrwt as $kei=>$fal){
    if ($kei=='DPWT') {
        $capt='Demin Water Treatment';
    }else if ($kei=='POWT'){
        $capt='Plant Operation Water Treatment';
    }
    $optwt.="<option value='".$kei."'>".$capt."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5mr_bfwt').'</span>');

//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='width:450px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['tipewt']."</td> 
                    <td>:</td>
                    <td><select id='tipewt' style='width:150px'>".$optwt."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['nama']."</td> 
                    <td>:</td>
                    <td><input type=text  id=nama nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:146px;\"></td>
                </tr>
				
                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>Simpan</button>
                                <button class=mybutton onclick=cancel()>Hapus</button>
                        </td>
                </tr>

        </table></fieldset>
                        <input type=hidden id=method value='insert'>
                        <input type=hidden id=kode value=''>";
        


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