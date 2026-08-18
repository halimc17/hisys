<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

/** Controller **/
//# Options
//$optPeriod = makeOption($dbname,'sdm_5periodegaji','periode,periode',
//    "kodeorg='".$_SESSION['empl']['lokasitugas']."' and jenisgaji='H'");

$str="select periode from ".$dbname.".sdm_5periodegaji where jenisgaji='B' and sudahproses=0
          order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optPeriod[$bar->periode]=$bar->periode;
}

// $hrt=" tipe='HOLDING' and length(kodeorganisasi)=4";
$hrt=" tipe='PT'";
$optOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', $hrt);
$optOrg=array();
$str="SELECT * FROM ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $scek="select distinct * from ".$dbname.".sdm_5periodegaji where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$bar['kodeorganisasi']."' and tipe='HOLDING')";
    $rcek=fetchData($scek);
    if(count($rcek)>0){
        $optOrg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
    }
}

#========= Tipe Karyawan ====================
$optTipe = array('kht'=>'KHT','khl'=>'KHL');
#========= Tipe Karyawan ====================

# Element

$els = array();
$els[] = array(
    makeElement('unit','label',$_SESSION['lang']['unit']),
    makeElement('kodeorg','select','',
        array('style'=>'width:150px'),$optOrg)
);


$els[] = array(
    makeElement('periodegaji','label',$_SESSION['lang']['periodegaji']),
    makeElement('periodegaji','select','',
        array('style'=>'width:150px'),$optPeriod),
);
/*$els[] = array(
    makeElement('tipe','label',$_SESSION['lang']['tipekaryawan']),
    makeElement('tipe','select','',array('style'=>'width:150px'),$optTipe),
);*/
$els['btn'] = array(
    makeElement('listBtn','btn',$_SESSION['lang']['list'],
        array('onclick'=>"list()"))
   // makeElement('postBtn','btn',$_SESSION['lang']['proses'],
   //     array('onclick'=>"post()",'disabled'=>'disabled'))
);

$form = "";
$form .= genElementMultiDim($_SESSION['lang']['form'],$els,1);
$form .= "<fieldset style='float:left;clear:left'><legend><b>".$_SESSION['lang']['list']."</b>".
    "</legend><div id='listContainer'></div></fieldset>";

/** View **/
echo open_body();
?>
<script languange=javascript1.2 src='js/sdm_3prosesgjho.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?php
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_3prosesgjho').'</span><br>');
echo $form;
CLOSE_BOX();

echo close_body();
?>