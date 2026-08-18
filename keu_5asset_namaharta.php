<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_5asset_namaharta.js'></script>

<?php

$nmOrg1=  makeOption($dbname, 'keu_5asset_jenisharta', 'id_jnsharta,nama_jenisharta');
$str2=$owlPDO->query("select id_jnsharta,nama_jenisharta from ".$dbname.".keu_5asset_jenisharta 
      order by id_jnsharta");
$str2->setFetchMode(PDO::FETCH_OBJ);
$optkeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$str2->fetch()){
    $optkeg.="<option value='".$bar->id_jnsharta."'>[".$bar->id_jnsharta."] ".$bar->nama_jenisharta."</option>";
}
$nmOrg2=  makeOption($dbname, 'keu_5asset_kelompokharta', 'id_klmpkharta,nama_kelompokharta');
$optkel="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$nmOrg3=  makeOption($dbname, 'keu_5asset_jenis_usaha', 'id_jns_usaha,nama_jenis_usaha');
$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

OPEN_BOX('','<span class=judul>'.getMenu('keu_5asset_namaharta').'</span></br>');
//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='width:400px;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>

                <tr>
                    <td>".$_SESSION['lang']['jenisharta']."</td>
                    <td>:</td>
                    <td><select id=jenis onchange='getkelompok()' style=\"width:205px;\">".$optkeg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kelompokharta']."</td> 
                    <td>:</td>
                    <td><select id=kelompok onchange='getjenis()' style=\"width:205px;\">".$optkel."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['jenisusaha']."</td> 
                    <td>:</td>
                    <td><select id=jenisusaha onchange='getnama()' style=\"width:205px;\">".$optjenis."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['id']." ".$_SESSION['lang']['namaharta']."</td>
                    <td>:</td>
                    <td><input type=text placeholder='auto generate' id=namaid nkeypress=\"return_tanpa_kutip(event);\" disabled class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['namaharta']."</td> 
                    <td>:</td>
                    <td><input type=text  id=namaharta nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['jumlahbulanpenyusutan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=jumlah nkeypress=\"return_tanpa_kutip(event);\" onkeypress=\"return angka_doang(event)\"  class=myinputtextnumber style=\"width:50px;\"></td>
                </tr>
            
                 <tr><td>".$_SESSION['lang']['status']."</td>
                 <td>:</td>
                 <td><input type=checkbox id=status1 checked>".$_SESSION['lang']['aktif']."/".$_SESSION['lang']['tidakaktif']."</td></tr>
                

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
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
        <div id=container> 
            <script>loadData(0)</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>