<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

echo open_body();
include('master_mainMenu.php');
if(empty(getOrgDetail(13))){
	$rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
	exit($rusak);
}
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
	$rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
	exit($rusak);
}
?>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zSearch.js></script>
<script type="text/javascript">var notif="<? echo $_SESSION['lang']['notifandayakin']; ?>";</script>
<script language=javascript1.2 src='js/pabrik_pengolahan.js?ver=1.2'></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?php
#=== Prep Control & Search
$ctl = array();

# Control
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/addbig.png title='".
    $_SESSION['lang']['new']."' onclick=\"showAdd()\"><br><span align='center'>".$_SESSION['lang']['new']."</span></div>";
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/list.png title='".
    $_SESSION['lang']['list']."' onclick=\"defaultList()\"><br><span align='center'>".$_SESSION['lang']['list']."</span></div>";

# Search
$ctl[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend>".
    // makeElement('sNoTrans','label',$_SESSION['lang']['nopengolahan']).
    // makeElement('sNoTrans','text','').
    makeElement('sTanggal','label',$_SESSION['lang']['tanggal'].' ').
    makeElement('sTanggal','text','',array('style'=>'width:150px','readonly'=>'','onmousemove'=>'setCalendar(this.id)')).
    makeElement('sFind','btn',$_SESSION['lang']['find'],array('onclick'=>"searchTrans()")).
    "</fieldset>";


#=== Table Aktivitas
# Header
$header = array(
    $_SESSION['lang']['nopengolahan'],
    $_SESSION['lang']['pabrik'],
    $_SESSION['lang']['tanggal'],
    $_SESSION['lang']['shift'],
    $_SESSION['lang']['status']
);

# Content
$cols = "nopengolahan,kodeorg,tanggal,shift,posting";
$query = selectQuery($dbname,'pabrik_pengolahan',$cols,"kodeorg='".$_SESSION['empl']['lokasitugas']."' order by nopengolahan desc","",false,10,1);
$data = fetchData($query);
$totalRow = getTotalRow($dbname,'pabrik_pengolahan');
// foreach($data as $key=>$row) {
    // $data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
// }

foreach($data as $key=>$row) {
    $data[$key]['kodeorg'] = getNamaOrg($row['kodeorg']);
    $data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
    $data[$key]['status'] = $row['posting'] == 1 ? "POSTED" : "NOT POSTED";
    if($row['posting']==1) {
		$data[$key]['switched']=true;
    }
    unset($data[$key]['posting']);
}




# Make Table
$tHeader = new rTable('headTable','headTableBody',$header,$data);
#$tHeader->addAction('showDetail','Detail','images/'.$_SESSION['theme']."/detail.png");
$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
#$tHeader->addAction('approveData','Approve','images/'.$_SESSION['theme']."/approve.png");

$tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
$tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");

// $tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
$tHeader->addAction('tampilDetail','Print Data Detail','images/'.$_SESSION['theme']."/zoom.png");
$tHeader->_actions[3]->addAttr('event');
$tHeader->_switchException = array('tampilDetail');
$tHeader->pageSetting(1,$totalRow,10);
#echo "<pre>";
#print_r($tHeader);
#=== Display View
# Title & Control
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_pengolahan').'</span>');
echo "<div><table><tr>";
foreach($ctl as $el) {
    echo "<td v-align='middle' style='min-width:100px'>".$el."</td>";
}
echo "</tr></table></div>";
CLOSE_BOX();

# List
OPEN_BOX();
echo "<div id='workField'>";
$tHeader->renderTable();
echo "</div>";
CLOSE_BOX();
echo close_body();
?>