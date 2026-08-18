<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
echo open_body();
require_once('master_mainMenu.php');

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src='js/kebun_2biaya.js?ver=1.3'></script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/option.js'></script>


<?
$optDiv="";
$optorg=$optper=$optnoakun=$optstatus="";
$optorg=$optDiv=$optTt=$optnoakun=$optstatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' order by namaorganisasi asc ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}


$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$str="select distinct(noakun) as noakun, namaakun from ".$dbname.".keu_5akun where noakun in (128,126,621,611,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optnoakun.="<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
}

$status = array('TM' => 'TM','TBM' => 'TBM','TB' => 'TB');
foreach ($status as $key) {
   $optstatus.="<option value=" . $key . ">" . $key . "</option>";
}

OPEN_BOX('','<span class=judul>Biaya Kebun</span><br>');

echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>Group Code</td>
                    <td>:</td>
                    <td><select id=noakun style=\"width:159px;\">" . $optnoakun . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') style=\"width:159px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi onchange=getThnTnm2(this,'tt','".$_SESSION['lang']['all']."') style=\"width:159px;\">" . $optDiv . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td>:</td>
                    <td><select id=tt style=\"width:159px;\">" . $optTt . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['status'] . "</td>
                    <td>:</td>
                    <td><select id=status style=\"width:159px;\">" . $optstatus . "</select></td>
                </tr>
                <tr>    
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td>
                        <select id=per1 style=\"width:67px;\">" . $optper . "</select>
                    s/d
                        <select id=per2 style=\"width:67px;\">" . $optper . "</select>
                    </td>
                </tr> 
                <tr>
				<td colspan=2></td>
                <td>
                    <button id=tomboldetail class=mybutton onclick=html1()>".$_SESSION['lang']['preview']."</button>
                    <button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
                </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "
<fieldset>
    <legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
	<div style='overflow:auto;height:400px;max-width:1235px';>
        <div id='html1'  style=display:none></div>
        <div id='html2'  style=display:none></div>
		<div id='html3'  style=display:none></div>
		<div id='html4'  style=display:none></div>
	</div>
</fieldset>"; // 
CLOSE_BOX();
echo close_body();
?>