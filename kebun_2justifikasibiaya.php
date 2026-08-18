<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2justifikasibiaya').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$wh='';
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){ 
	$wh='';
} elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
    $wh=" and kodeorganisasi in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";
} else {
	$wh=" and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}

$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' ".$wh." order by kodeorganisasi asc ";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optOrg="<option value=''></option>";
while ($rOrg = $qOrg->fetch()) {
    $optOrg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['kodeorganisasi'] . " - " . $rOrg['namaorganisasi'] . "</option>";
}

$sOrg = "select distinct periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 12";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optper="<option value=''></option>";
while ($rOrg = $qOrg->fetch()) {
    $optper.="<option value=" . $rOrg['periode'] . ">" . $rOrg['periode'] . "</option>";
}

$sOrg = "select distinct noakun, namaakun from " . $dbname . ".keu_5akun where length(noakun) = 5 and substr(noakun,1,3) in ('126','621','611') and namaakun not like '%NON AKTIF%'";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$klpbyy="<option value=''></option>";
while ($rOrg = $qOrg->fetch()) {
    $klpbyy.="<option value=" . $rOrg['noakun'] . ">" . $rOrg['noakun'] . " - " . $rOrg['namaakun'] . "</option>";
}

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$sOrg = "select distinct substr(noakun,1,5) as noakun from " . $dbname . ".keu_jurnaldt_vw where  substr(noakun,1,3) in ('126','621','611') order by noakun";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $klpbyy.="<option value=" . $rOrg['noakun'] . ">" . $rOrg['noakun'] . " - " . @$nmakun[$rOrg['noakun']] . "</option>";
}

$arr = "##kdorg##divisi##periode##kdklpakun##kdakun##keg";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg onchange=getDivisiX(this.value,'divisi','all') style=\"width:160px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi style=\"width:160px;\"></select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=periode style=\"width:160px;\">" . $optper . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['kelompokbiaya'] . "</td>
                    <td>:</td>
                    <td><select id=kdklpakun onchange=getakun(this.value,'kdakun','all') style=\"width:160px;\">" . $klpbyy . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['akun'] . "</td>
                    <td>:</td>
                    <td><select id=kdakun onchange=getkegiatan(this.value,'keg','all') style=\"width:160px;\"><option value=''></option></select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['kegiatan'] . "</td>
                    <td>:</td>
                    <td><select id=keg style=\"width:160px;\"><option value=''></option></select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2justifikasibiaya','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2justifikasibiaya.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo "<div style=clear:both></div>
		<div id='both_report'>
			<div id='head_tableboth' align=right>
				<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
					<img title='Full Screen' class='resicon' src='images/full-screen.png'>
				</a>
				<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
					<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
				</a>
			</div>
		<div class='table-scroll'><div id='printContainer' style='height:450px; ></div>
		</div></div>";
CLOSE_BOX();
echo close_body();
?>