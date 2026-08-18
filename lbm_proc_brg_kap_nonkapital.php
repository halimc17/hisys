<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$arr = "##periode##judul##kdPt##regDt##smbrData##statDt";
$_POST['judul'] == '' ? $judul = $_GET['judul'] : $judul = $_POST['judul'];

$optRegional = $optSmbr = $optstat = "";
$optRegional.="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sRegion = "select distinct regional from " . $dbname . ".bgt_regional where regional not in ('DKI','LAMPUNG') order by regional asc";

$qRegion = $owlPDO->query($sRegion) or die(print " Gagal: " . PDOException::getMessage());
$qRegion->setFetchMode(PDO::FETCH_ASSOC);
while ($rRegion = $qRegion->fetch()) {
    $optRegional.="<option value='" . $rRegion['regional'] . "'>" . $rRegion['regional'] . "</option>";
}
$arrTipe = array("1" => "Kapital", "2" => "Non Kapital");
$optPt = $optTipe = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach ($arrTipe as $lstTipe => $dtTipe) {
    $optTipe.="<option value='" . $lstTipe . "'>" . $dtTipe . "</option>";
}

$optperiode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sOrg = "select distinct periode from " . $dbname . ".setup_periodeakuntansi order by periode desc";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $optperiode.="<option value=" . $rOrg['periode'] . ">" . $rOrg['periode'] . "</option>";
}
$arrSmbr = array("3" => "Default", "2" => "Semua PO yang Di buat");

foreach ($arrSmbr as $lstSmbr => $dtSmbr) {
    $optSmbr.="<option value='" . $lstSmbr . "'>" . $dtSmbr . "</option>";
}

$derk = 1;

$optstat.="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$arrstatus = array("0" => "Pusat", "1" => "Lokal");
foreach ($arrstatus as $lstStatus => $statdt) {
    $optstat.="<option value='" . $lstStatus . "'>" . $statdt . "</option>";
}

echo"
<table cellspacing=\"1\" border=\"0\" >
    <tr><td colspan=4>" . $judul . "</td></tr>
    <tr><td><label>" . $_SESSION['lang']['periode'] . "</label></td><td><select id='periode' style=\"width:150px;\">" . $optperiode . "</select></td>";
echo"<td><label>" . $_SESSION['lang']['regional'] . "</label></td><td><select id='regDt' style=\"width:150px;\" onchange=getDtPt()>" . $optRegional . "</select></td>
    <td><label>" . $_SESSION['lang']['status'] . "</label></td><td><select id='statDt' style=\"width:150px;\">" . $optstat . "</select></td>
</tr>
    ";

echo"<tr><td><label>" . $_SESSION['lang']['pt'] . "</label></td><td><select id='kdPt' style=\"width:150px;\">" . $optPt . "</select></td>";

echo"<td><label>" . $_SESSION['lang']['data'] . "</label></td><td><select id='smbrData' style=\"width:150px;\">" . $optSmbr . "</select></td>
    <td colspan=2>&nbsp;</td>
    </tr>
    ";
echo"<tr><td colspan=\"2\"><input type=hidden id=judul name=judul value='" . $judul . "'></td></tr>
    <tr><td colspan=\"4\">
    <button onclick=\"zPreview('lbm_slave_proc_brg_kap_nonkapital','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['preview'] . "</button>
    <button onclick=\"zExcel(event,'lbm_slave_proc_brg_kap_nonkapital.php','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"excel\" id=\"excel\">" . $_SESSION['lang']['excel'] . "</button>    
   <!--<button onclick=\"zPdf('lbm_slave_proc_brg_kap_nonkapital','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">" . $_SESSION['lang']['pdf'] . "</button>
    <button onclick=\"batal()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">" . $_SESSION['lang']['cancel'] . "</button>--></td></tr>
</table>
";
?>