<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script languange=javascript1.2 src='js/pad_survey.js'></script>
<style>
    ul.listjawaban{list-style: none;}
    input[type="text"].optjawaban{
        background:none;
        border:none;
        border-bottom :0.5px solid #393939;
        /*width:-webkit-fill-available;*/
        margin-top:5px;
        margin-bottom:5px;
        width:200px;
    }
    .optexample{width:300px;}
    li{margin-top:5px;margin-bottom:5px;}
    input[type="text"].optjawaban:focus{
        background:#FFF;
        box-shadow:none;
        outline-offset: 0px;
        outline: none;
    }
    .colmn_jawaban_right{
        float:right;
    }
    .colmn_jawaban_left{
        float:left;
    }
    .clearfix{
        clear:both;
    }
    .w-400{width:400px;}
    ol.alphabet{list-style-type:lower-latin;}
    .optjawabanboth{float:left;}
    ol.pertanyaan>li{font-weight:bold;}
    ol.pertanyaan>li li{font-weight:normal;}
    textarea{width:400px;margin-top:5px;margin-bottom:5px;}
</style>
<?


OPEN_BOX('','<span class=judul>'.strtoupper("SURVEY GIS").'</span>');
#== Prep Option & Query

$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","tipe='PT'",'',true);
$optTipe = makeOption($dbname,"pad_5typesurvey","kodesurvey,namasurvey",'','',true);

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
    makeElement('kodeorgcr','label','Nama Perusahaan'),
    makeElement(':','label',':'),
    makeElement('kodeorgcr','select','',array('style'=>'width:190px'),$optOrg),
    makeElement('carijdwl','button',$_SESSION['lang']['find'],array('onclick'=>'caridata()'))
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
# Add Form

echo "<div id=containerForm style='display:none'>";
OPEN_BOX();
echo "<div id=surveyforms>";

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