<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script languange=javascript1.2 src='js/sdm_jadwalsecurity.js'></script>

<?


OPEN_BOX('','<span class=judul>'.getMenu('sdm_jadwalsecurity').'</span>');
#== Prep Option & Query

$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);

$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","NOT (tipe='PT' or tipe='HOLDING') and kodeorganisasi='".$idOrg."'",'2',true);
$optPos = makeOption($dbname,"sdm_5possecurity", "nopos,namapos","unit='".$idOrg."'",'',true);
$optPeriode = makeOption($dbname,"sdm_5periodegaji","periode,periode","kodeorg='".$idOrg."' and sudahproses='0' and jenisgaji='B'",'',true);
//$optMinggu = array(''=>'','1'=>'Ke-1','2'=>'Ke-2','3'=>'Ke-3','4'=>'Ke-4');

#== Prep List


$tblCr="<table cellspacing=1 border=0>";
$tblCr.="<tr valign=moiddle>";
$tblCr.="<td align=center style='width:100px;cursor:pointer;' onclick=adddataform()>";
$tblCr.="<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>";
$tblCr.="<td align=center style='width:100px;cursor:pointer;' onclick=loadData()>";
$tblCr.="<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>";



#== Prep Form 

# Elements
$elscari = array();
$elscari[] = array(
    makeElement('kodeorgcr','label','Kode Organisasi'),
    makeElement(':','label',':'),
    makeElement('kodeorgcr','select','',array('style'=>'width:190px'),$optOrg),
    makeElement('periodecr','label','Periode'),
    makeElement(':','label',':'),
    makeElement('periodecr','select','',array('style'=>'width:100px'),$optPeriode),
    makeElement('carijdwl','button',$_SESSION['lang']['find'],array('onclick'=>'caridata()'))
);

$elshead = array();
$elshead[] = array(
    makeElement('kodeorg','label','Kode Organisasi'),
    makeElement(':','label',':'),
    makeElement('kodeorg','select','',array('style'=>'width:190px'),$optOrg)
);
$elshead[] = array(
    makeElement('periode','label','Periode'),
    makeElement(':','label',':'),
    makeElement('periode','select','',array('style'=>'width:100px','onchange'=>'getWeek()'),$optPeriode)
);
$elshead[] = array(
    makeElement('pos','label','Pos'),
    makeElement(':','label',':'),
    makeElement('pos','select','',array('style'=>'width:100px'),$optPos)
);
$elshead[] = array(
    makeElement('minggu','label','Minggu'),
    makeElement(':','label',':'),
    makeElement('minggu','select','',array('style'=>'width:100px'))
);
$elsheadbutton['btn'] = array(
    makeElement('saveButton','button',$_SESSION['lang']['save'],array('onclick'=>'checkHeader()')),
    makeElement('clearButton','button',$_SESSION['lang']['clear'],array('onclick'=>'clearDetail()'))
);

#===== Show =======
echo "<div id=headerjudul>";
echo $tblCr;
echo "<td><fieldset id='formcari' clear:right;min-height:auto;'>";
echo "<legend>".$_SESSION['lang']['find']."</legend>";
echo genElement($elscari);
echo "</fieldset></td></table>";
echo "</div>";

CLOSE_BOX();
# Active Form


echo "<div id=header style='display:none'>";
OPEN_BOX();
echo "<fieldset id='formheader' style='float:left'>";
echo "<legend>".$_SESSION['lang']['header']."</legend>";
echo genElement($elshead);
echo "<div id=hbutton>";
echo genElement($elsheadbutton);
echo "</div>";
echo "</fieldset>";
CLOSE_BOX();
echo "</div>";


echo "<div id=Detail style='display:none'>";
OPEN_BOX();
echo "<div id=detailform >";
echo "</div>";
CLOSE_BOX();
echo "</div>";

echo "<div id=listtabledetail style='display:none'>";
OPEN_BOX();
echo "<div id=tabledetail >";
echo "</div>";
CLOSE_BOX();
echo "</div>";

# Table
echo "<div id=container>";
OPEN_BOX();
echo "<script>loadData()</script>";
CLOSE_BOX();
echo "</div>";

echo close_body();
?>