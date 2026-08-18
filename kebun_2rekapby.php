<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/zTools.js></script>

<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper("Rekap Biaya Per Blok").'</span><br>');
//get existing periodbgt_regional_assignment
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$optOrg.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
	$str = "select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";	
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$optOrg.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'
	       and left(kodeorganisasi,4) in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regioinal']."')";
	$str = "select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";	
}else{
	$optOrg.="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
	$str = "select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."' order by periode desc";	
}

$rOrg=fetchdata($sOrg);
foreach($rOrg as $row=>$lstDt){
	$optOrg.="<option value='".$lstDt['kodeorganisasi']."'>".$lstDt['namaorganisasi']."</option>";
}
$res=fetchdata($str);
$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($res as $bar){
    $optper.="<option value='".$bar['periode']."'>".substr($bar['periode'],5,2)."-".substr($bar['periode'],0,4)."</option>";
}
$optInti="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrInti=array("I"=>"Inti","P"=>"Plasma");
foreach($arrInti as $row=>$isRw){
	$optInti.="<option value='".$row."'>".$isRw."</option>";
}
$arr = "##unitId##periode##intiPlasma";
$tab.="<fieldset style=float:left><legend>".$_SESSION['lang']['find']."</legend>";
$tab.="<table border=0 cellspacing=1 cellpadding=1>";
$tab.="<tr><td>".$_SESSION['lang']['unit']."/".$_SESSION['lang']['divisi']."</td><td>:</td>";
$tab.="<td><select id=unitId style=width:150px>".$optOrg."</select></td></tr>";
$tab.="<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td>";
$tab.="<td><select id=periode style=width:150px>".$optper."</select></td></tr>";
$tab.="<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>:</td>";
$tab.="<td><select id=intiPlasma style=width:150px>".$optInti."</select></td></tr>";
$tab.="<tr><td colspan=2>&nbsp;</td>";
$tab.="<td><button class=mybutton  onclick=\"zPreview('kebun_slave_2rekapby', '".$arr."', 'printContainer')\" class=\"mybutton\">".$_SESSION['lang']['preview']."</button>
              <button onclick=\"zExcel(event, 'kebun_slave_2rekapby.php', '".$arr."')\" class=\"mybutton\">".$_SESSION['lang']['excel']."</button>
          </td></tr>";
$tab.="</table>";
$tab.="</fieldset>";

$tab.="<div style=clear:both></div>

<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
    <div class='table-scroll'><div id='printContainer' style='height:350px'></div>

    </div></div>";

echo $tab;
//===============================================	
CLOSE_BOX();
close_body();
?>