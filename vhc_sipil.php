<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/vhc_sipil.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
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
$jenisSearch = array(
	'noinvoice' => $_SESSION['lang']['noinvoice'],
	'noinvoicesupplier' => $_SESSION['lang']['noinvoice']." Supplier",
	'namasupplier' => $_SESSION['lang']['supplier'],
	'nopo' => $_SESSION['lang']['nopo'],
);
$ctl[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td><input id=sNoTrans class=myinputtext value='' type=text style='width:150px'></td>
			<td><button class='mybutton' onclick=\"searchTrans()\">".$_SESSION['lang']['find']."</button></td>
		</tr>
	</table>";


#=== Table Aktivitas
# Header
$header = array(
   $_SESSION['lang']['nomor'],$_SESSION['lang']['organisasi'],
   $_SESSION['lang']['tanggal'],$_SESSION['lang']['mandor'],$_SESSION['lang']['nikmandor1'],
   $_SESSION['lang']['asisten'],$_SESSION['lang']['keraniafdeling'],
   $_SESSION['lang']['updateby']
);

//cari nama orang
$str="select karyawanid, namakaryawan from ".$dbname.".datakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar = $res->fetch())
{
   $nama[$bar->karyawanid]=$bar->namakaryawan;
}    

# Content
$cols = "a.notransaksi,a.kodeorg,a.tanggal,a.mandor,a.mandor1,a.assisten,
	a.krani,a.updateby,a.posting,a.postingby";
$order="a.tanggal desc";
if($_SESSION['empl']['subbagian']!=''){
	$where = "a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and updateby='".$_SESSION['standard']['userid']."'";
}else{
	$where = "a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
}
if($_SESSION['empl']['kodejabatan']==21)$where = "a.kodeorg like '%' and updateby like '%'";

$queryRow = "select count(*) ";
$query = " from ".$dbname.".vhc_splht a 
	where ".$where." order by ".$order." limit 0,10";
$queryRow .= $query;
$query = "select ".$cols.$query;
$tmpTotal = fetchData($queryRow);
$data = fetchData($query);
$totalRow = count($tmpTotal);

foreach($data as $key=>$row) {
	if($row['posting']==1) {
		$data[$key]['switched']=true;
	}
	unset($data[$key]['posting']);            
	unset($data[$key]['postingby']);            
	$data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
	$data[$key]['mandor'] = isset($nama[$row['mandor']])? $nama[$row['mandor']]: '';
	$data[$key]['mandor1'] = isset($nama[$row['mandor1']])? $nama[$row['mandor1']]: '';
	$data[$key]['assisten'] = isset($nama[$row['assisten']])? $nama[$row['assisten']]: '';
	$data[$key]['krani'] = isset($nama[$row['krani']])? $nama[$row['krani']]: '';
	$data[$key]['updateby'] = $nama[$row['updateby']];
}

# Posting --> Jabatan
$postJabatan = getPostingJabatan('sipil');

# Make Table
$tHeader = new rTable('headTable','headTableBody',$header,$data);
$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL' or $_SESSION['empl']['kodejabatan']==117 or $_SESSION['empl']['kodejabatan']==119){
	// $tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
// } else {//hanya HO dan region yang boleh menghapus
	// $tHeader->addAction('','Delete','images/'.$_SESSION['theme']."/delete.png");
// }
// print_r($_SESSION['standard']);
$tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
$tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
    $tHeader->_actions[2]->_name='';
}
$tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
$tHeader->_actions[3]->addAttr('event');
$tHeader->pageSetting(1,$totalRow,10);
$tHeader->_switchException = array('detailPDF');

#=== Display View
# Title & Control
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['sipil']).'</span>');
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

echo "<div id='detailField' style='display:none'>";
echo "</div>";
echo close_body();
?>