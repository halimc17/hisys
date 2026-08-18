<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/log_5supnpwp.js'></script>

<?php

// make option untuk menampilkan nama supplier di form
$nmOrg1=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');

$str2=$owlPDO->query("select supplierid,namasupplier from ".$dbname.".log_5supplier 
      order by namasupplier");
$str2->setFetchMode(PDO::FETCH_OBJ);
$optkeg='';
while($bar=$str2->fetch()){
    $optkeg.="<option value='".$bar->supplierid."'>".$bar->namasupplier."</option>";
}

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['npwp']).'</span>');
//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='width:400px;'>";
    echo"<legend>".$_SESSION['lang']['formnpwp']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>

                <tr>
                    <td>".$_SESSION['lang']['supplier']."</td>
                    <td>:</td>
                    <td><select id=supplierid style=\"width:200px;\">".$optkeg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['npwp']."</td> 
                    <td>:</td>
                    <td><input type=text  id=npwp nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['jalan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=jalan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['blok']."</td> 
                    <td>:</td>
                    <td><input type=text  id=blok nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['nomor']."</td> 
                    <td>:</td>
                    <td><input type=text  id=nomor nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['rt']."</td> 
                    <td>:</td>
                    <td><input type=text  id=rt nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['rw']."</td> 
                    <td>:</td>
                    <td><input type=text  id=rw nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kecamatan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=kecamatan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['keluarahan']."</td> 
                    <td>:</td>
                    <td><input type=text  id=keluarahan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kabupaten']."</td> 
                    <td>:</td>
                    <td><input type=text  id=kabupaten nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['propinsi']."</td> 
                    <td>:</td>
                    <td><input type=text  id=propinsi nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kodepos']."</td> 
                    <td>:</td>
                    <td><input type=text  id=kodepos nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['telp_no']."</td> 
                    <td>:</td>
                    <td><input type=text  id=telp_no nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>
            
                 <tr><td>".$_SESSION['lang']['aktif']."</td>
                 <td>:</td>
                 <td><input type=checkbox id=aktif>".$_SESSION['lang']['yes']."/".$_SESSION['lang']['no']."</td></tr>
                

                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>Simpan</button>
                                <button class=mybutton onclick=cancel()>Reset</button>
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