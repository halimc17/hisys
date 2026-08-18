<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript src='js/keu_5proporsisegment.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<h2>Setup Proporsi Segmen</h2>
<?php
#=======Search==============
# Get Options
if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
  $tmpOpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN'");
} elseif($_SESSION['empl']['tipelokasitugas']=='KEBUN') {
  $tmpOpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
    "kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
} else {
  $tmpOpt = getOrgBelow($dbname,$_SESSION['empl']['lokasitugas'],false,'kebunonly');
}
$sKebun = array(''=>'');
foreach($tmpOpt as $key=>$row) {
  $sKebun[$key] = $row;
}

#=======Form============
# Create Elements
$searchEls = $_SESSION['lang']['kebun']." ";
$searchEls .= makeElement('sKebun','select','',
  array('onchange'=>"getAfdeling(this,'sAfdeling')",'style'=>'width:150px'),$sKebun)." ";
$searchEls .= $_SESSION['lang']['afdeling']." ";
$searchEls .= makeElement('sAfdeling','select','',array('style'=>'width:150px','onchange'=>'clearContainer()'),array())." ";
$searchEls .= makeElement('searchIt','button',$_SESSION['lang']['find'],array('onclick'=>'showData()'))." ";

# Render Search Element
echo "<fieldset id='search' style='margin-bottom:10px;float:left;clear:both'>";
echo "<legend><b>".$_SESSION['lang']['searchdata']."</b></legend>";
echo $searchEls;
echo "</fieldset><div style='clear:both'></div>";

echo "<div id='setupProporsi'></div>";

CLOSE_BOX();
echo close_body();
?>