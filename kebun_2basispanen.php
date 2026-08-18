<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');

echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('', '<span class=judul>'.getMenu('kebun_2basispanen').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>


<?
$optPeriode = "";
for ($x = 0; $x < 13; $x++) {
    $dt = mktime(0, 0, 0, date('m') - $x, 15, date('Y'));
    $optPeriode.="<option value=" . date("Y-m", $dt) . ">" . date("m-Y", $dt) . "</option>";
}

$optOrg = "<option value=''></option>";
$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' order by kodeorganisasi asc ";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optOrg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['kodeorganisasi'] . " - " . $rOrg['namaorganisasi'] . "</option>";
}
$optDiv='';
$arr = "##kdUnit##tgl1##divisi";
echo"<fieldset style='float:left'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdUnit onchange=getdivisi() style=\"width:174px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi style=\"width:174px;\">" . $optDiv . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=tgl1 style=\"width:75px;\">" . $optPeriode . "</select>
                    </td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2basispanen','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2basispanen.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo "<fieldset style=width:500px;><legend>".$_SESSION['lang']['info']."</legend>
	<table border=0 cellpadding=1 cellspacing=1>
    <tr>
		<td>1.</td><td>Penentuan basis panen menggunakan BJR dari Setup - BJR</td></tr><tr>
		<td>2.</td><td>Panen bulan <b>Nov, Des, Jan</b> menggunakan rata2 BJR bulan <b>Ags, Sep, Okt</b></td></tr><tr>
		<td>3.</td><td>Panen bulan <b>Feb, Mar, Apr</b> menggunakan rata2 BJR bulan <b>Nov, Des, Jan</b></td></tr><tr>
		<td>4.</td><td>Panen bulan <b>Mei, Jun, Jul</b> menggunakan rata2 BJR bulan <b>Feb, Mar, Apr</b></td></tr><tr>
		<td>5.</td><td>Panen bulan <b>Ags, Sep, Okt</b> menggunakan rata2 BJR bulan <b>Mei, Jun, Jul</b></td></tr>
		
    </tr></table>
</fieldset>";

CLOSE_BOX();

OPEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'; >
</div></fieldset>";
CLOSE_BOX();
echo close_body();
?>