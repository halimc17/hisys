<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
echo open_body();
include('master_mainMenu.php');

?>

<script languange=javascript1.2 src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script languange=javascript1.2 src='js/formReport.js'></script>
<script languange=javascript1.2 src='js/zGrid.js'></script>
<script language="javascript" src="js/zMaster.js"></script>
<script languange=javascript1.2 src='js/gis_survey.js'></script>

<?

OPEN_BOX('','<span class=judul>'.strtoupper("Survey").'</span>');
#== Prep Option & Query

$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);
$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi"," length(kodeorganisasi)='4'",2,true);





#== Prep List


$tblCr="<table cellspacing=1 border=0>";
$tblCr.="<tr valign=moiddle>";
$tblCr.="<td align=center style='width:100px;cursor:pointer;' onclick=addDataForm()>";
$tblCr.="<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>";
$tblCr.="<td align=center style='width:100px;cursor:pointer;' onclick=loadData()>";
$tblCr.="<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>";



#== Prep Form 
# Elements
$elscari = array();
$elscari[] = array(
    makeElement('unitcr','label','Kode Unit'),
    makeElement(':','label',':'),
    makeElement('unitcr','select','',array('style'=>'width:190px'),$optOrg),
    makeElement('caridata','button',$_SESSION['lang']['find'],array('onclick'=>'caridata()'))
);



#===== Show =======
echo "<div id=headerjudul>";
echo $tblCr;
echo "<td><fieldset id='formcari' style='width:500px; clear:right;min-height:auto;'>";
echo "<legend>".$_SESSION['lang']['find']."</legend>";
echo genElement($elscari);
echo "</fieldset></td></table>";
echo "</div>";

CLOSE_BOX();


OPEN_BOX();
echo "<div id=container>";
echo "<script>loadData()</script>";
echo "</div>";
CLOSE_BOX();

echo "<div id=formdata style='display:none'>";
OPEN_BOX();
echo "<div id=dataform >";
echo "</div>";
CLOSE_BOX();
echo "</div>";


echo "<div id=listtable style='display:none'>";
OPEN_BOX();
echo "<div id=tables >";
echo "</div>";
CLOSE_BOX();
echo "</div>";



echo close_body();
?>