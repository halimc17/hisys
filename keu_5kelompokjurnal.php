<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/keu_5kelompokjurnal_reset.js'></script>
<script language=javascript src='js/keu_5kelompokjurnal_copy.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
#======Select Prep======
# Get Data
$optPt=$optPer="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$where = "`tipe`='PT'";
$whereunit = "`tipe` IN ('KEBUN','PABRIK')";
$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$where,'0');
$optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereunit,'0');
$optPer = makeOption($dbname,'setup_periodeakuntansi','periode,periode','1=1 GROUP BY kodeorg,periode','0');
$optKel= makeOption($dbname,'keu_5parameterjurnal','jurnalid,jurnalid');

$sOPt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where ".$where."";
$qOpt=$owlPDO->query($sOPt) or die(print " Gagal: ".PDOException::getMessage());
$qOpt->setFetchMode(PDO::FETCH_ASSOC);
while($rOpt=  $qOpt->fetch())
{
    $optPt.="<option value=".$rOpt['kodeorganisasi'].">".$rOpt['kodeorganisasi']." - ".$rOpt['namaorganisasi']."</option>";
}

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
  makeElement('kodeunit','label',"Kode ".$_SESSION['lang']['unit']),
  makeElement('kodeunit','select','',array('style'=>'width:250px'),$optUnit)
);
$els[] = array(
  makeElement('kodekelompok','label',$_SESSION['lang']['kodekelompok']),
  makeElement('kodekelompok','select','',array('style'=>'width:100px'),$optKel)
);
$els[] = array(
  makeElement('periode','label',$_SESSION['lang']['periode']),
  makeElement('periode','select','',array('style'=>'width:100px'),$optPer)
);
$els[] = array(
  makeElement('keterangan','label',$_SESSION['lang']['keterangan']),
  makeElement('keterangan','text','',array('style'=>'width:247px','maxlength'=>'45',
    'onkeypress'=>'return tanpa_kutip(event)'))
);
$els[] = array(
  makeElement('nokounter','label',$_SESSION['lang']['nokounter']),
  makeElement('nokounter','text','0',array('style'=>'width:100px','maxlength'=>'11',
    'onkeypress'=>'return angka_doang(event)'))
);

# Fields
$fieldStr = '##kodeorg##kodeunit##kodekelompok##periode##keterangan##nokounter';
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

# Button
$els['btn'] = array(
  genFormBtn($fieldStr,
    'keu_5kelompokjurnal',"##kodeorg##kodekelompok")
);
$els2 = array();
# Fields

$els2[] = array(
  makeElement('tipe','label',"Copy data dari kelompok jurnal Ya/Tidak"),
  makeElement('tipe','checkbox','',array('onchange'=>'checktipe()'))
);

$els2[] = array(
  makeElement('kodeorg1','label',$_SESSION['lang']['kodeorg']),
  makeElement('kodeorg1','select','',array('style'=>'width:250px'),$optOrg)
);
$els2[] = array(
  makeElement('kodeorg2','label',$_SESSION['lang']['copyfrom']." ".$_SESSION['lang']['kodeorg']),
  makeElement('kodeorg2','select','',array('style'=>'width:250px'),$optOrg)
);

$els2[] = array(

  makeElement('kodekelompokc','label',$_SESSION['lang']['kodekelompok']),
  makeElement('kodekelompokc','select','',array('style'=>'width:100px','style'=>'display:none'),$optKel)
);
# Button
$els2['btn'] = array(
    makeElement('prosesButton','button',$_SESSION['lang']['proses'],array('onclick'=>'copyData()')),
);

# Generate Field
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['kodekelompok']." ".$_SESSION['lang']['jurnal'].'<br>').'</span>');
echo genElTitle($_SESSION['lang']['form'],$els);
echo genElTitle($_SESSION['lang']['form']." Copy Data",$els2);
CLOSE_BOX();
OPEN_BOX();

#=======End Form============
echo"
<fieldset style='width:480px;>
<legend style='font-weight:bold'>Reset Counter</legend>
<table cellpading=1 border=0>
<tr><td><select id=kodePt name=kodePt style='width:250px;'>".$optPt."</select><button class=mybutton onclick=\"resetJurnal()\">".$_SESSION['lang']['save']."</button>
</table>
</fieldset>";

#=======Table===============
# Display Table
echo "<div style='clear:both;float:left'>";
echo masterTable($dbname,'keu_5kelompokjurnal');
echo "</div>";
#=======End Table============

CLOSE_BOX();
echo close_body();
?>