<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('', '<span class=judul>' . getMenu('pabrik_2analisabyypks') . '</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<!-- <script language=javascript src='js/kebun_2analisabyytbm.js?v=<?php echo time(); ?>'></script> -->
<!-- Ganti pengambilannya by Abdul -->
<script language=javascript src='js/pabrik_2analisabyypks.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language="javascript" src="js/zComment.js?ver=<?php echo time(); ?>"></script>
<link rel=stylesheet type=text/css href=style/zComment.css>
<?
$optorg = $optper = '';
$optorg .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optPT = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optDiv = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optDiv2 = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optTt = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

$arrunit = array();
$arrunit = getOrgDetail(13);
foreach ($arrunit as $val => $nama) {
    $optorg .= "<option value='" . $val . "'>" . $val . " - " . $nama . "</option>";
}


// $str="select * from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$_SESSION['empl']['kodeorganisasi']."'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// $s="";
// if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
// $s="selected";
// }
// $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

$str = "select distinct(periode) as periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 25";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optper .= "<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

// $str="select * from ".$dbname.".organisasi where tipe='STATION' and induk='".$_SESSION['empl']['lokasitugas']."'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// $s="";
// if($_SESSION['empl']['subbagian']==$bar['kodeorganisasi']){
// $s="selected";
// }
// $optDiv.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

$arrtipe = array("code" => "Per Job Code", "group" => "Per Job Group");
foreach ($arrtipe as $res => $bar) {
    @$opttipe .= "<option value=" . $res . ">" . $bar . "</option>";
}

$arr1 = "##kdorg##prd##tipe##divisi";
echo "<fieldset style='float:left;' id=tableheader>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg onchange=getdivisi_x(); style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['station'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=divisi style=\"width:164px;\">" . $optDiv . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:164px;\">" . $optper . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tipe'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=tipe style=\"width:164px;\">" . $opttipe . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('pabrik_slave_2analisabyypks','" . $arr1 . "','printContainer');showheader(); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'pabrik_slave_2analisabyypks.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
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