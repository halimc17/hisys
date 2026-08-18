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

<script language=javascript src='js/pabrik_hasil.js?v=<?php echo time(); ?>'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?php
/*<script language=javascript1.2 src=js/pabrik_hasil.js></script>*/
#=== Prep Control & Search
$optKomoditi = array(""=>$_SESSION['lang']['pilihdata']);
$optTanki    = array(""=>$_SESSION['lang']['pilihdata']);
$arrKomoditi = array('KER' => 'Kernel', 'CPO' => 'CPO');
foreach($arrKomoditi as $key => $row) {
    $optKomoditi[$key] = $row;
}   
$ctl = array();

# Control
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/addbig.png title='".
    $_SESSION['lang']['new']."' onclick=\"showAdd()\"><br><span align='center'>".$_SESSION['lang']['new']."</span></div>";
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/list.png title='".
    $_SESSION['lang']['list']."' onclick=\"defaultList()\"><br><span align='center'>".$_SESSION['lang']['list']."</span></div>";

# Search
$ctl[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend>".
    // makeElement('sNoTrans','label',$_SESSION['lang']['notransaksi']).
    // makeElement('sNoTrans','text','').
    makeElement('sTanggal','label',$_SESSION['lang']['tanggal'].' ').
    makeElement('sTanggal','text','',array('style'=>'width:150px','readonly'=>'','onmousemove'=>'setCalendar(this.id)')).
    makeElement('sKomoditi','label',' '.$_SESSION['lang']['komoditi'].' ').
    makeElement('sKomoditi','select','',array('onchange'=>'getSearchTanki();'),$optKomoditi).
    makeElement('sTanki','label',' '.$_SESSION['lang']['tangki'].' ').
    makeElement('sTanki','select','',array(),$optTanki).
    makeElement('sFind','btn',$_SESSION['lang']['find'],array('onclick'=>"searchTrans()")).
    "</fieldset>";


#=== Table Aktivitas
# Header
$header = array(
    $_SESSION['lang']['nomor'],$_SESSION['lang']['tanggal'],$_SESSION['lang']['pabrik'],$_SESSION['lang']['kodetangki'], $_SESSION['lang']['jumlah'].' CPO','FFA CPO','Moist CPO','Dirt CPO','Dobi CPO',$_SESSION['lang']['jumlah'].' PK','Moist PK','Dirt PK','Broken PK'
);

$nmtangki = makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan',"kodeorg='".$_SESSION['empl']['lokasitugas']."'");

# Content
$cols = "notransaksi,tanggal,kodeorg,kodetangki,kuantitas,cpoffa,cpokdair,cpokdkot,dobi,kernelquantity,kernelkdair,kernelkdkot,kernelffa";
$query = selectQuery($dbname,'pabrik_masukkeluartangki',$cols,"kodeorg='".$_SESSION['empl']['lokasitugas']."'","notransaksi desc",false,10,1);
$data = fetchData($query);
$totalRow = getTotalRow($dbname,'pabrik_masukkeluartangki');
foreach($data as $key=>$row) {
	$data[$key]['kodetangki'] = $nmtangki[$row['kodetangki']];
	$data[$key]['kodeorg'] = getNamaOrg($row['kodeorg']);
	$data[$key]['kuantitas'] = number_format(floatval($row['kuantitas']),0);
	$data[$key]['kernelquantity'] = number_format(floatval($row['kernelquantity']),0);
    $data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
}

# Make Table
$tHeader = new rTable('headTable','headTableBody',$header,$data);
$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
//$tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
$tHeader->_actions[1]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
$tHeader->pageSetting(1,$totalRow,10);
#echo "<pre>";
#print_r($tHeader);
#=== Display View
# Title & Control
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_hasil').'</span>');
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