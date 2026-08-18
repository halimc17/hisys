<?
//@Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/kebun_3pusingan.js'></script>


<?

$optOrg = "";
$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "' and tipe='KEBUN' order by namaorganisasi asc ";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optOrg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['namaorganisasi'] . "</option>";
}
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['pusingan']." ".$_SESSION['lang']['panen']).'</span><br>');
$arr = "##kdorg##tgl1##tgl2";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:159px;\">" . $optOrg . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
                    s/d
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_3pusingan','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'; >
</div></fieldset>"; //<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>
CLOSE_BOX();
echo close_body();
?>