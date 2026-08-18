<? 
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$arr="##unit##periode##judul"; 
$_POST['judul']==''?$judul=$_GET['judul']:$judul=$_POST['judul'];



$optunit="<option value=''></option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
    where CHAR_LENGTH(kodeorganisasi)='4' and tipe = 'PABRIK'
    order by namaorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
    $optunit.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}
$optperiode="<option value=''></option>";
$sOrg="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
    $optperiode.="<option value=".$rOrg['periode'].">".$rOrg['periode']."</option>";
}

echo"
<table cellspacing=\"1\" border=\"0\" >
    <tr><td colspan=2>".$judul."</td></tr>
    <tr><td><label>".$_SESSION['lang']['unit']."</label></td><td><select id='unit' style=\"width:200px;\">".$optunit."</select></td></tr>
    <tr><td><label>".$_SESSION['lang']['periode']."</label></td><td><select id='periode' style=\"width:200px;\">".$optperiode."</select></td></tr>
    <tr height=\"20\"><td colspan=\"2\"><input type=hidden id=judul name=judul value='".$judul."'></td></tr>
    <tr><td colspan=\"2\"> 
    <button onclick=\"zPreview('lbm_slave_pks_kimiadanbbm','".$arr."','reportcontainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
    <button onclick=\"zExcel(event,'lbm_slave_pks_kimiadanbbm.php','".$arr."','excel')\" class=\"mybutton\" name=\"excel\" id=\"excel\">".$_SESSION['lang']['excel']."</button>    
    <button onclick=\"zPdf('lbm_slave_pks_kimiadanbbm','".$arr."','reportcontainer')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">". $_SESSION['lang']['pdf']."</button>
    <!--<button onclick=\"batal()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">".$_SESSION['lang']['cancel']."</button>--></td></tr>
</table>
";
?>