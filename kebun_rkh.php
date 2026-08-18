<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

echo open_body();
require_once('master_mainMenu.php');

$tipeVal = "rhk";
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/addbig.png title='".
    $_SESSION['lang']['new']."' onclick=\"getSlave('showadd')\"><br><span align='center'>".$_SESSION['lang']['new']."</span></div>";
$ctl[] = "<div align='center'><img class=delliconBig src=images/".$_SESSION['theme']."/list.png title='".
    $_SESSION['lang']['list']."' onclick=\"getSlave()\"><br><span align='center'>".$_SESSION['lang']['list']."</span></div>";

	
#posting
$optpost=array(''=>'','0'=>'Not Posted','1'=>'Posted');
//$optpost=array(''=>'','0'=>'Masuk','1'=>'Keluar');
#divisi
$whereorg="induk='".$_SESSION['empl']['lokasitugas']."' and tipe in ('AFDELING','BIBITAN')";
$optdivisi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereorg,null,true); ?>

<script language='javascript' src='js/zMaster.js'></script>
<script language='javascript' src='js/zSearch.js'></script>
<script language='javascript' src='js/zTools.js'></script>
<script language='javascript' src='js/kebun_rkh.js?v=1.4'></script>
<script languange='javascript' src='js/formTable.js'></script>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php	
# Search
$datenow = date('d-m-Y');
$ctl[] = "<fieldset><legend><b>".$_SESSION['lang']['find']."</b></legend><table border=0>".
			"<tr><td>".
			makeElement('carinorhk','label',$_SESSION['lang']['notransaksi'])."</td><td>".
			makeElement('carinorhk','text','',array('style'=>'width:105px'))."</td><td>".

			makeElement('cariDivisi','label',$_SESSION['lang']['divisi'])."</td><td>".
			makeElement('cariDivisi','select','',array('style'=>'width:105px'),$optdivisi)."</td><td>".

			makeElement('cariTanggal','label',$_SESSION['lang']['tanggal'])."</td><td>".
			makeElement('cariTanggal','date','',array('style'=>'width:100px'))."</td><td>".
			'<button onclick="getSlave();" class="mybutton"> '.$_SESSION['lang']['find'].'</button>'.
			"</td></table></fieldset>";

OPEN_BOX('','<span class=judul>'.getMenu('kebun_rkh').'</span>');
//echo "<div align='center'><h3>".$title."</h3></div>";
echo "<div><table><tr>";
foreach($ctl as $el) {
    echo "<td v-align='middle' style='min-width:100px'>".$el."</td>";
}
echo "</tr></table></div>";
CLOSE_BOX();
echo "<div id='workwarp'></div>";
echo "<script>getSlave();</script>";
echo close_body();
?>