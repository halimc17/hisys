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
    document.getElementById('periodeId').value = '';
    document.getElementById('pt').value = '';
    // document.getElementById('intiplasma').value = '';
    document.getElementById('printContianer').innerHTML = '';
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
$sTgl="select distinct substr(tanggal,1,7) as periode from ".$dbname.".log_transaksi_vw where tanggal!='' order by tanggal desc";
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

$optPtDt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sqlPT = "
    SELECT DISTINCT 
        LEFT(sbt.indukblok,3) AS kodept,
        org.namaorganisasi
    FROM {$dbname}.setup_blok_tahunan sbt
    LEFT JOIN {$dbname}.organisasi org 
        ON org.kodeorganisasi = LEFT(sbt.indukblok,3)
    WHERE org.tipe = 'PT'
    ORDER BY kodept
";
$resPT = fetchData($sqlPT);

foreach ($resPT as $r) {
    $optPtDt .= "<option value='{$r['kodept']}'>
                    {$r['kodept']} - {$r['namaorganisasi']}
               </option>";
}

// $intiplasma="<option value=''>".$_SESSION['lang']['all']."</option>";
// $intiplasma.="<option value='I'>Inti</option>";
// $intiplasma.="<option value='P'>Plasma</option>";

OPEN_BOX('','<span class=judul>Laporan Kinerja Alat Berat<br></span>');

// $arr6="##periodeId##pt##intiplasma";
$arr="##periodeId##pt";

echo"<div id=tableheader>";
echo"<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>";
echo"<table cellspacing=1 border=0>";
echo"<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td><select class=select2 id=periodeId style=width:200px>".$optper."</select></td></tr>";
echo"<tr><td>".$_SESSION['lang']['pt']."</td><td>:</td><td><select class=select2 id=pt multiple style=width:200px>".$optPtDt."</select></td></tr>";
?>
<script>
// Override getValue locally to handle multiselect for the PT field
// since the global zTools.js version doesn't support it.
var originalGetValue = getValue;
getValue = function(id) {
    var tmp = document.getElementById(id);
    if(tmp && tmp.multiple) {
        var vals = [];
        for(var i=0; i<tmp.options.length; i++) {
            if(tmp.options[i].selected) vals.push(tmp.options[i].value);
        }
        return vals.join(',');
    }
    return originalGetValue(id);
}
</script>
<?php

// echo"<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>:</td><td><select class=select2 id=intiplasma style=width:200px>".$intiplasma."</select></td></tr>";

echo"<tr><td></td><td></td><td colspan=3>
<button onclick=\"zPreview('vhc_slave_2kinerjaAlatBerat','".$arr."','printContianer');showheader();\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
<button onclick=\"zExcel(event,'vhc_slave_2kinerjaAlatBerat.php','".$arr."')\" class=\"mybutton\" name=\"excel2\" id=\"excel2\">".$_SESSION['lang']['excel']."</button>
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
echo"<div id='printContianer' class='table-scroll' style='overflow:auto;height:73vh;'></div>";
CLOSE_BOX();
echo close_body();
?>