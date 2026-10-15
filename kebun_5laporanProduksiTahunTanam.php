<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');

?>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$arrunit = getOrgDetail(23);
foreach ($arrunit as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optOrg.="<option value='".$key."'>".$key." - ".$val."</option>";			
	$n=$d;
	if($d!=$n){
		$optOrg.="</optgroup>";
	}
}

$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sTgl="select distinct substr(tanggal,1,7) as periode from ".$dbname.".pabrik_timbangan where tanggal!='' order by tanggal desc";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while($rTgl=$qTgl->fetch())
{
     $thn=explode("-", $rTgl['periode']);
   if($thn[1]=='12')
   {
   $optper.="<option value='".substr($rTgl['periode'],0,4)."'>".substr($rTgl['periode'],0,4)."</option>";
   }
   $optper.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}

$intiplasma="<option value=''>".$_SESSION['lang']['all']."</option>";
$intiplasma.="<option value='I'>Inti</option>";
$intiplasma.="<option value='P'>Plasma</option>";

$optThn="<option value=''>".$_SESSION['lang']['all']."</option>";

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language="javascript" src="js/kebun_5laporanProduksiTahunTanam.js?ver=1"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php

OPEN_BOX('','<span class=judul>Laporan Produksi Per Tahun Tanam<br></span>');
$arr="##periodeId##unitId##intiplasma##tahuntanam";

$frm[0].="<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>";
$frm[0].="<table cellspacing=1 border=0>";
$frm[0].="<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td><select class=select2 id=periodeId style=width:200px>".$optper."</select></td></tr>";
$frm[0].="<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 id=unitId onchange=\"getThnTanam()\" style=width:200px>".$optOrg."</select></td></tr>";
$frm[0].="<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>:</td><td><select class=select2 id=intiplasma onchange=\"getThnTanam()\" style=width:200px>".$intiplasma."</select></td></tr>";
$frm[0].="<tr><td>".$_SESSION['lang']['tahuntanam']."</td><td>:</td><td><select class=select2 id=tahuntanam style=width:200px>".$optThn."</select></td></tr>";

$frm[0].="<tr><td></td><td></td><td colspan=3>
<button onclick=\"cekPreview('preview');showheader();\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
<button onclick=\"cekPreview('excel');\" class=\"mybutton\" name=\"excel\" id=\"excel\">".$_SESSION['lang']['excel']."</button>
<button onclick=batal() class=\"mybutton\" name=\"batal\" id=\"batal\">".$_SESSION['lang']['cancel']."</button></td></tr>";
$frm[0].="</table></fieldset>";
$frm[0].="<div style='clear:both'></div>";

$hfrm[0]='Rincian';
echo"<div id=tableheader>";
drawTab('FRM',$hfrm,$frm,"",'300px');
echo"</div>";
CLOSE_BOX();

OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo"<div id='printContainer2' class='table-scroll' style='overflow:auto;height:73vh;'></div>";
CLOSE_BOX();
echo close_body();
?>
