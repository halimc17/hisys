<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>


<?
OPEN_BOX('','<span class=judul>'.getMenu('tool_prosesupdate').'</span>');


#= ambil daftar tagihan

$frm[0]='';
$frm[1]='';


$frm[0].="<fieldset>";
$frm[0].="<legend>".$_SESSION['lang']['detail']."</legend>";
$frm[0].=" <table class=sortable cellspacing=1 cellspacing=1 border=0 style='width:100%;'>";
$frm[0].="<thead><tr class=rowheader>";
	 $frm[0].="<td rowspan=2>No. Invoice</td>";
	 $frm[0].="<td colspan=2>Data Lama</td>";
	 $frm[0].="<td colspan=2>Data Baru</td>";
$frm[0].="</tr>";
$frm[0].="<tr class=rowheader>";
	 $frm[0].="<td>Nilai Invoice (HT)</td>";
	 $frm[0].="<td>Niali Pajak (DT)</td>";
	 $frm[0].="<td>Nilai DPP</td>";
	 $frm[0].="<td>Nilai Invoice</td>";
$frm[0].="</tr></thead>";
$arrnoinv=$dtnilaipajak=$dtnilaiinv=array();
#= query vm
$str="select * from ".$dbname.".keu_tagihanht where tipeinvoice in (select kode from ".$dbname.".keu_5jenistagihan where jurnal=0)";
$res=fetchdata($str);
foreach($res as $bar){
	$arrnoinv[$bar['noinvoice']]=$bar['noinvoice'];
	$dtnilaiinv[$bar['noinvoice']]=$bar['nilaiinvoice'];
}

#= nilai pajak
$str="select * from ".$dbname.".keu_tagihandt where  noinvoice in ('".implode("','",$arrnoinv)."') and (noakun like '117%' or noakun like '213%')";
// echo $str;
$res=fetchdata($str);
foreach($res as $bar){
	// @$dtnilaippn[$bar['noinvoice']][$bar['noakun']]+=$bar['nilai'];
	@$dtnilaipajak[$bar['noinvoice']]+=$bar['nilai'];
}

foreach($arrnoinv as $dtnoinv){
	
	 $bgcolor="bgcolor=''";
	
	 $frm[0].="<tr class=rowcontent>";
	 $frm[0].="<td>".$dtnoinv."</td>";
	 $frm[0].="<td>".$dtnilaiinv[$dtnoinv]."</td>";
	 $frm[0].="<td>".$dtnilaipajak[$dtnoinv]."</td>";
	 $frm[0].="<td>".$dtnilaiinv[$dtnoinv]."</td>";
	 $frm[0].="<td>".($dtnilaiinv[$dtnoinv]+$dtnilaipajak[$dtnoinv])."</td>";
	 $frm[0].="</tr>";
	 
}

$frm[0].="</table>";
$frm[0].="</fieldset>";






/*
*
*
*
*
*
*
*
*/

$frm[1].="<fieldset>";
$frm[1].="<legend>".$_SESSION['lang']['detail']."</legend>";
$frm[1].=" <table class=sortable cellspacing=1 cellspacing=1 border=0 style='width:100%;'>";
$frm[1].="<thead><tr class=rowheader>";
	 $frm[1].="<td rowspan=2>No. Invoice</td>";
	 $frm[1].="<td colspan=2>Data Lama</td>";
	 $frm[1].="<td colspan=2>Data Baru</td>";
$frm[1].="</tr>";
$frm[1].="<tr class=rowheader>";
	 $frm[1].="<td>Nilai Invoice (HT)</td>";
	 $frm[1].="<td>Niali Pajak (DT)</td>";
	 $frm[1].="<td>Nilai DPP</td>";
	 $frm[1].="<td>Nilai Invoice</td>";
$frm[1].="</tr></thead>";

$arrnoinv=$dtnilaipajak=$dtnilaiinv=array();

#= query vm
$str="select * from ".$dbname.".keu_tagihanht where tipeinvoice in (select kode from ".$dbname.".keu_5jenistagihan where jurnal=1)";
$res=fetchdata($str);
foreach($res as $bar){
	$arrnoinv[$bar['noinvoice']]=$bar['noinvoice'];
	$dtnilaiinv[$bar['noinvoice']]=$bar['nilaiinvoice'];
}

#= nilai pajak
$str="select * from ".$dbname.".keu_tagihandt where  noinvoice in ('".implode("','",$arrnoinv)."') and (noakun like '117%' or noakun like '213%')";
// echo $str;
$res=fetchdata($str);
foreach($res as $bar){
	// @$dtnilaippn[$bar['noinvoice']][$bar['noakun']]+=$bar['nilai'];
	@$dtnilaipajak[$bar['noinvoice']]+=$bar['nilai'];
}

foreach($arrnoinv as $dtnoinv){
	$bgcolor="";
	$dtnilaidpp[$dtnoinv]=$dtnilaiinv[$dtnoinv]-$dtnilaipajak[$dtnoinv];
	if($dtnilaidpp[$dtnoinv]==0){
		$dtnilaidpp[$dtnoinv]=$dtnilaiinv[$dtnoinv];
		$bgcolor='bgcolor=orange';
	}
	$frm[1].="<tr class=rowcontent>";
	$frm[1].="<td ".$bgcolor.">".$dtnoinv."</td>";
	$frm[1].="<td ".$bgcolor.">".$dtnilaiinv[$dtnoinv]."</td>";
	$frm[1].="<td ".$bgcolor.">".$dtnilaipajak[$dtnoinv]."</td>";
	$frm[1].="<td ".$bgcolor.">".($dtnilaidpp[$dtnoinv])."</td>";
	$frm[1].="<td ".$bgcolor.">".$dtnilaiinv[$dtnoinv]."</td>";
	$frm[1].="</tr>";
}

$frm[1].="</table>";
$frm[1].="</fieldset>";




$hfrm[0]='VM';
$hfrm[1]='NVM';
drawTab('FRM',$hfrm,$frm,100,'auto');  

/*
echo "<fieldset style='width:450px;'><table>
	<tr><td colspan=2></td>
		<td colspan=4>
		<button onclick=zPreview('keu_slave_2daftarPerkiraan','".@$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'keu_slave_2daftarPerkiraan.php','".@$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		<button onclick=zPdf('keu_slave_2daftarPerkiraan','".@$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>
		</td>
	</tr>";

echo "</table>";
echo "</fieldset>";
*/
CLOSE_BOX();
?>


<?php
/*
OPEN_BOX();

echo "
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1220px'; >
</div></fieldset>";//<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>

CLOSE_BOX();
echo close_body();					
*/
?>