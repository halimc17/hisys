<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/zMaster.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
  
<?php
#======Select Prep======
# Get Data
$where = " level=5";
$optAkun = array(''=>'');
$tmpAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$where,'2');
foreach($tmpAkun as $key=>$row) {
    $optAkun[$key] = $row;
}
$sAkun="select noakun,namaakun from ".$dbname.".keu_5akun where left(noakun,3)='115' and char_length(noakun)>6";
$qAkun=$owlPDO->query($sAkun) or die(print " Gagal: ".PDOException::getMessage());
$qAkun->setFetchMode(PDO::FETCH_ASSOC);
while($rAkun=$qAkun->fetch()){
  $optAkun[$rAkun['noakun']] = $rAkun['noakun']."-".$rAkun['namaakun'];
}
asort($optAkun);

$whereOrg = "tipe='HOLDING' and length(kodeorganisasi)=3";
$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg,'1');
#======End Select Prep======
#=======Form============
echo "<div style='margin-bottom:30px'>";
$els = array();
# Fields
$els[] = array(
  makeElement('kodeorg','label',$_SESSION['lang']['kodeorg']),
  makeElement('kodeorg','select','',array('style'=>'width:250px'),$optOrg)
);
$els[] = array(
  makeElement('kodeaplikasi','label',$_SESSION['lang']['kodeaplikasi']),
  makeElement('kodeaplikasi','text','',array('style'=>'width:75px','maxlength'=>'5'))
);
$els[] = array(
  makeElement('jurnalid','label',$_SESSION['lang']['jurnalid']),
  makeElement('jurnalid','text','',array('style'=>'width:75px','maxlength'=>'5'))
);
$els[] = array(
  makeElement('keterangan','label',$_SESSION['lang']['keterangan']),
  makeElement('keterangan','text','',array('style'=>'width:245px','maxlength'=>'50'))
);
$els[] = array(
  makeElement('noakundebet','label',$_SESSION['lang']['noakundebet']),
  makeElement('noakundebet','select','',array('style'=>'width:250px'),$optAkun)
);
$els[] = array(
  makeElement('sampaidebet','label',$_SESSION['lang']['sampaidebet']),
  makeElement('sampaidebet','select','',array('style'=>'width:250px'),$optAkun)
);
$els[] = array(
  makeElement('noakunkredit','label',$_SESSION['lang']['noakunkredit']),
  makeElement('noakunkredit','select','',array('style'=>'width:250px'),$optAkun)
);
$els[] = array(
  makeElement('sampaikredit','label',$_SESSION['lang']['sampaikredit']),
  makeElement('sampaikredit','select','',array('style'=>'width:250px'),$optAkun)
);
$els[] = array(
  makeElement('auto','label',$_SESSION['lang']['auto']),
  makeElement('auto','check')
);
$els[] = array(
  makeElement('aktif','label',$_SESSION['lang']['aktif']),
  makeElement('aktif','check')
);


# Fields
$fieldStr = '##kodeorg##kodeaplikasi##jurnalid##keterangan##noakundebet##sampaidebet##noakunkredit##sampaikredit##auto##aktif';
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

# Button
$els['btn'] = array(
  genFormBtn($fieldStr,
    'keu_5parameterjurnal',"##kodeorg##kodeaplikasi##jurnalid",null,null,true)
);

OPEN_BOX('','<span class=judul>'.strtoupper('Journal Parameter').'</span><br>');
# Generate Field
echo genElTitle('Form',$els);
echo "</div>";
#=======End Form============

#=======Table============
# Display Table
#echo masterTable($dbname,'setup_kegiatan',$fieldArr);
echo "<div style='clear:both'>";
echo masterTable($dbname,'keu_5parameterjurnal',$fieldArr,array(),array(),null,array(),null,'kodeorg##kodeaplikasi##jurnalid');
echo "</div>";
#=======End Table============

CLOSE_BOX();
echo close_body();
?>