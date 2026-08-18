<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/log_5docassign.js'></script>

<?php

//pengambilan kelompok barang dari table kelompok barang
$nmOrg1=  makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$str2=$owlPDO->query("select kode,kelompok from ".$dbname.".log_5klbarang 
      order by kode");
$str2->setFetchMode(PDO::FETCH_OBJ);
$optkeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$str2->fetch()){
    $optkeg.="<option value='".$bar->kode."'>[".$bar->kode."] ".$bar->kelompok."</option>";
    $optsearch.="<option value='" . $bar->kode . "'>[ " . $bar->kode . " ] " . $bar->kelompok . "</option>";
}

//pengambilan sub kelompok barang dari table sub kelompok barang
$nmOrg2=  makeOption($dbname, 'log_5subklbarang', 'kode,namasubkelompok');
$optkel="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$nmOrg3=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optkode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

OPEN_BOX('','<span class=judul>'.getMenu('log_5docassign').'</span></br>');
//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='float:left;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>

                <tr>
                    
                    <td><input type=hidden id=idbar></td></tr>
                <tr>
                    <td>".$_SESSION['lang']['materialgroupcode']."</td>
                    <td>:</td>
                    <td><select id=kelompok onchange='getkelompok()' style=\"width:255px;\">".$optkeg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kodesubkelompokbarang']."</td> 
                    <td>:</td>
                    <td><select id=subkelompok onchange='getkode()' style=\"width:255px;\">".$optkel."</select></td>
                </tr>
          
                <tr>
                    <td>".$_SESSION['lang']['kodebarang']."</td> 
                    <td>:</td>
                    <td><select id=kodebarang style=\"width:255px;\">".$optkode."</select>
						<img id='kodebarang' onclick=z.elSearch('kodebarang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
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
echo "<fieldset style=float:left>
        <legend>".$_SESSION['lang']['list']."</legend>
        <div id=container> 
            <script>loadData(0)</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>