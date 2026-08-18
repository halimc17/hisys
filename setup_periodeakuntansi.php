<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['periodeakuntansi'].'</span><br>'));
?>
<script language=javascript src=js/zMaster.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
  
<?php
#======Select Prep======
# Get Data
//$where = "`tipe`='HOLDING' or `tipe`='PT' or `tipe`='KANWIL'";
$where = "`tipe`='HOLDING' or `tipe`='PABRIK' or `tipe`='KANWIL' or `tipe`='GUDANG' or tipe='GUDANGTEMP' or `tipe`='KEBUN'
         or (tipe='TRAKSI' and length(kodeorganisasi)=4)";
$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$where,'0');
#======End Select Prep======
#=======Form============
echo "<div style='margin-bottom:30px'>";
$els = array();
# Fields
$els[] = array(
  makeElement('kodeorg','label',$_SESSION['lang']['kodeorg']),
  makeElement('kodeorg','select','',array('style'=>'width:172px'),$optOrg)
);
$els[] = array(
  makeElement('periode','label',$_SESSION['lang']['periode']),
  makeElement('periode','text','',array('style'=>'width:70px','placeholder'=>'yyyy-dd','maxlength'=>'80',))
);
$els[] = array(
  makeElement('tanggal','label',$_SESSION['lang']['tanggal']),
  makeElement('tanggalmulai','text','',array('style'=>'width:70px',
    'readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)','maxlength'=>'8'))." s/d ".
  makeElement('tanggalsampai','text','',array('style'=>'width:70px',
    'readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)','maxlength'=>'8'))
);
$els[] = array(
  makeElement('tutupbuku','label',$_SESSION['lang']['tutupbuku']),
  makeElement('tutupbuku','check','0',array())
);

# Fields
$fieldStr = '##kodeorg##periode##tanggalmulai##tanggalsampai##tutupbuku';
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

# Button
$els['btn'] = array(
  genFormBtn($fieldStr,'setup_periodeakuntansi',"##kodeorg##periode",null,null,
			 '##tutupbuku')
);

# Generate Field
echo genElTitle($_SESSION['lang']['form'],$els);
echo "</div>";
#=======End Form============
CLOSE_BOX();
OPEN_BOX();

#=======Table============
# Display Table
echo "<div style='clear:both;float:left'>";
echo masterTable($dbname,'setup_periodeakuntansi',"*",array(),array(),null,array(),null,'kodeorg##periode');
echo "</div>";
#=======End Table============

CLOSE_BOX();
echo close_body();
?>