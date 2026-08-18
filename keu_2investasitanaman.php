<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/keu_2investasitanaman.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul><b>'.getMenu('keu_2investasitanaman').'</b></span><br>');

$str="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$optper='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
        <table>
            <tr>
                <td>".$_SESSION['lang']['pt']."</td>
                <td>:</td>
                <td><select id=pt style='width:220px;' class='select2' onchange=getUnitInvestasiTanaman();>".$optpt."</select></td>
                <td>".$_SESSION['lang']['periode']."</td>
                <td>:</td>
                <td><select id=periode style='width:160px;' class='select2'>".$optper."</select></td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['unit']."</td>
                <td>:</td>
                <td><select id=unit style='width:220px;' class='select2'>".$optunit."</select></td>
                <td></td>
                <td></td>
                <td><button class=mybutton onclick=getLaporanInvestasiTanaman('html')>".$_SESSION['lang']['preview']."</button>
                    <button class=mybutton onclick=getLaporanInvestasiTanaman('excel')>".$_SESSION['lang']['excel']."</button></td>
            </tr>
        </table>
    </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
echo"<div id=container style='overflow:auto;height:73vh;'></div>";
CLOSE_BOX();
close_body();
?>
