<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

/** Controller **/
//# Options
//$optPeriod = makeOption($dbname,'sdm_5periodegaji','periode,periode',
//    "kodeorg='".$_SESSION['empl']['lokasitugas']."' and jenisgaji='H'");

$str="select periode from ".$dbname.".sdm_5periodegaji_kecil where kodeorg='".$_SESSION['empl']['lokasitugas']."'  and sudahproses=0 order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optPeriod[$bar->periode]=$bar->periode;
}

// if(($_SESSION['empl']['tipelokasitugas']=='KANWIL')||($_SESSION['empl']['bagian']=='IT')){
    $hrt=" induk='".$_SESSION['org']['kodeorganisasi']."'";
// }else{
//     $hrt=" kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
// }
$optOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', $hrt);


#========= Tipe Karyawan ====================
#========= Tipe Karyawan ====================

# Element

$els = array();
$els[] = array(
    makeElement('unit','label',$_SESSION['lang']['unit']),
    makeElement('kodeorg','select','',
        array('style'=>'width:150px','class'=>'select2'),$optOrg)
);


$els[] = array(
    makeElement('periodegaji','label',$_SESSION['lang']['periodegaji']),
    makeElement('periodegaji','select','',
        array('style'=>'width:150px','class'=>'select2'),$optPeriod),
);
/*$els[] = array(
    makeElement('tipe','label',$_SESSION['lang']['tipekaryawan']),
    makeElement('tipe','select','',array('style'=>'width:150px','class'=>'select2'),$optTipe),
);*/
$els['btn'] = array(
    makeElement('listBtn','btn',$_SESSION['lang']['list'],
        array('onclick'=>"list()"))
   // makeElement('postBtn','btn',$_SESSION['lang']['proses'],
   //     array('onclick'=>"post()",'disabled'=>'disabled'))
);

$form = "";
$form .= genElementMultiDim($_SESSION['lang']['form'],$els,1);
$form .= "<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['list']."</b>".
    "</legend><div id='listContainer'></div></fieldset>";

/** View **/
echo open_body();
?>
<script languange=javascript1.2 src='js/sdm_3prosesgajikecil.js?v=<?php echo time();?>'></script>
<?php
#Cabang PPP Proses Gaji: unit milik PT PPP dan file sdm_slave_*_ppp.php yang sudah ada. Unit PPP dijalankan lewat file *_ppp.php (js/sdm_tujuanProsesGaji.js), unit lain tetap file biasa.
$orgPPPGj = array();
foreach ($optOrg as $kdOrgGj => $nmOrgGj) {
    if ($kdOrgGj != '' && getindukPT($kdOrgGj) == 'PPP') {
        $orgPPPGj[] = $kdOrgGj;
    }
}
$filePPPGj = array();
foreach (glob('sdm_slave_*_ppp.php') as $fGj) {
    $filePPPGj[] = basename($fGj);
}
echo "<script>var orgPPP = " . json_encode($orgPPPGj) . "; var filePPP = " . json_encode($filePPPGj) . ";</script>";
?>
<script language=javascript src='js/sdm_tujuanProsesGaji.js?v=<?php echo time();?>'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?php
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
echo "<script>jQuery(function(){ jQuery('select.select2').select2({dropdownAutoWidth:true,width:'style'}); jQuery('<style>.select2-container--open .select2-dropdown{z-index:100000;}</style>').appendTo('head'); });</script>";

//OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['prosesgjharian']).'</span><br>');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_3prosesgajikecil').'</span><br>');
echo $form;
CLOSE_BOX();

echo close_body();
?>