<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>
function batal2() {
    document.getElementById('periodeId4').value = '';
    document.getElementById('unitId4').value = '';
    document.getElementById('intiplasma4').value = '';
    document.getElementById('printContainer2').innerHTML = '';
}
function showheader(){
	if(document.getElementById('tableheader').style.display=="none"){		
		document.getElementById('tableheader').style.display="block";
		document.getElementById('showhead').innerHTML="Hide Filter";
		document.getElementById('tombolexport').style.display="none";
	}else{
		document.getElementById('tableheader').style.display="none";
		document.getElementById('tombolexport').style.display="block";
		document.getElementById('showhead').innerHTML="Show Filter";
	}	
}
</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
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

$optUniDt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optUniDt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optUniDt.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optUniDt.="</optgroup>";
	}
}

$intiplasma="<option value=''>".$_SESSION['lang']['all']."</option>";
$intiplasma.="<option value='I'>Inti</option>";
$intiplasma.="<option value='P'>Plasma</option>";

OPEN_BOX('','<span class=judul>Detail Produksi Brondol<br></span>');

$arr6="##periodeId4##unitId4##intiplasma4";

echo"<div id=tableheader>";
echo"<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>";
echo"<table cellspacing=1 border=0>";
echo"<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td><select class=select2 id=periodeId4 style=width:200px>".$optper."</select></td></tr>";
echo"<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 id=unitId4 style=width:200px>".$optUniDt."</select></td></tr>";
echo"<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>:</td><td><select class=select2 id=intiplasma4 style=width:200px>".$intiplasma."</select></td></tr>";

echo"<tr><td></td><td></td><td colspan=3>
<button onclick=\"zPreview('kebun_slave_DetProduksiBrondol','".$arr6."','printContainer2');showheader();\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
<button onclick=\"zExcel(event,'kebun_slave_DetProduksiBrondol.php','".$arr6."')\" class=\"mybutton\" name=\"excel2\" id=\"excel2\">".$_SESSION['lang']['excel']."</button>
<button onclick=batal2() class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['cancel']."</button></td></tr>";
echo"</table></fieldset>";  
echo"<div style='clear:both'></div>";
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