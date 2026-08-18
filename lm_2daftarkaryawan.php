<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('', '<span class=judul>' . getMenu('lm_2daftarkaryawan') . '</span><br>');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/lm_2daftarkaryawan.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
// Deklarasi
$optPT = $optper = $optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optDiv = $optgank = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

$str = "select * from " . $dbname . ".organisasi where tipe='PT' and kodesejarah=''";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $s = "";
    if ($_SESSION['empl']['kodeorganisasi'] == $bar['kodeorganisasi']) {
        $s = "selected";
    }
    $optPT .= "<option value=" . $bar['kodeorganisasi'] . " " . $s . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str = "select * from " . $dbname . ".organisasi where tipe='KEBUN' and induk='" . $_SESSION['empl']['kodeorganisasi'] . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $s = "";
    if ($_SESSION['empl']['lokasitugas'] == $bar['kodeorganisasi']) {
        $s = "selected";
    }
    $optorg .= "<option value=" . $bar['kodeorganisasi'] . " " . $s . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}
$str = "select * from " . $dbname . ".organisasi where tipe='AFDELING' and induk='" . $_SESSION['empl']['lokasitugas'] . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $s = "";
    if ($_SESSION['empl']['subbagian'] == $bar['kodeorganisasi']) {
        $s = "selected";
    }
    $optDiv .= "<option value=" . $bar['kodeorganisasi'] . " " . $s . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str = "select distinct(left(periode,7)) as periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 25";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optper .= "<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arr1 = "##pt##kdorg##prd##divisi";
echo "<fieldset style='float:left;' id=tableheader>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=pt onchange=getUnitThnTnm(this,'kdorg,tt','divisi','" . $_SESSION['lang']['all'] . "')  style=\"width:164px;\">" . $optPT . "</select></td>
                    <td hidden>" . $_SESSION['lang']['unit'] . "</td>
                    <td hidden>:</td>
                    <td hidden><select class=select2 onchange=getAfdelingThnTnm(this,'divisi,tt','divisi,tt','" . $_SESSION['lang']['all'] . "') id=kdorg style=\"width:104px;\">" . $optorg . "</select></td>
                    
                    <td hidden>" . $_SESSION['lang']['divisi'] . "</td>
                    <td hidden>:</td>
                    <td hidden><select class=select2 id=divisi  style=\"width:164px;\">" . $optDiv . "</select></td>

                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:84px;\">" . $optper . "</select></td>
                </tr>
                <tr>
                    <td colspan=16 align=center>
                        <hr>
                        <button onclick=zPreview('lm_slave_2daftarkaryawan','" . $arr1 . "','printContainer');showheader();cekpt(); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                        <button onclick=zExcel(event,'lm_slave_2daftarkaryawan.php','" . $arr1 . "');cekpt(); class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo "<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo "<div id='printContainer' class='table-scroll' style=height:73vh></div>";

CLOSE_BOX();
echo close_body();
?>