<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$arr = "##periode##judul";
$_POST['judul'] == '' ? $judul = $_GET['judul'] : $judul = $_POST['judul'];

$arrTipe = array("1" => "Kapital", "2" => "Non Kapital");
$optTipe = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
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


echo"
<table cellspacing=\"1\" border=\"0\" >
    <tr><td colspan=2>" . $judul . "</td></tr>
    <tr><td><label>" . $_SESSION['lang']['periode'] . "</label></td><td><select id='periode' style=\"width:200px;\">" . $optperiode . "</select></td></tr>";

echo"<tr><td colspan=\"2\"><input type=hidden id=judul name=judul value='" . $judul . "'></td></tr>
    <tr><td colspan=\"2\"> 
    <button onclick=\"zPreview('lbm_slave_proc_supplier','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['preview'] . "</button>
    <button onclick=\"zExcel(event,'lbm_slave_proc_supplier.php','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"excel\" id=\"excel\">" . $_SESSION['lang']['excel'] . "</button>    
   <!--<button onclick=\"zPdf('lbm_slave_proc_supplier','" . $arr . "','reportcontainer')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">" . $_SESSION['lang']['pdf'] . "</button>
    <button onclick=\"batal()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">" . $_SESSION['lang']['cancel'] . "</button>--></td></tr>
</table>
";
?>