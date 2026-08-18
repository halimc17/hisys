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
    $('#periodeId').val(null).trigger('change');
    $('#pt').val(null).trigger('change');
    $('#intiplasma').val(null).trigger('change');
    document.getElementById('printContianer').innerHTML = '';
}

function laporanPreview() {
    if(getValue('periodeId') == '') {
        alert('Periode harus dipilih');
    } else if(getValue('pt') == '') {
        alert('PT harus dipilih');
    } else {
        zPreview('keu_slave_2analisisBiayaKantor', '##periodeId##pt##intiplasma', 'printContianer');
        showheader();
    }
}

function laporanExcel(ev) {
    if(getValue('periodeId') == '') {
        alert('Periode harus dipilih');
    } else if(getValue('pt') == '') {
        alert('PT harus dipilih');
    } else {
        zExcel(ev, 'keu_slave_2analisisBiayaKantor.php', '##periodeId##pt##intiplasma');
    }
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

var originalGetValue = getValue;
getValue = function(id) {
    var el = document.getElementById(id);
    if(el && el.multiple) {
        var result = [];
        for (var i = 0, l = el.options.length; i < l; i++) {
            if (el.options[i].selected) result.push(el.options[i].value);
        }
        return result.join(',');
    }
    return originalGetValue(id);
};
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

$intiplasma="<option value=''>".$_SESSION['lang']['all']."</option>";
$intiplasma.="<option value='1'>Inti</option>";
$intiplasma.="<option value='0'>Plasma</option>";

OPEN_BOX('','<span class=judul>Laporan Analisis Biaya Kantor<br></span>');

// $arr6="##periodeId##pt##intiplasma";
$arr="##periodeId##pt##intiplasma";

echo"<div id=tableheader>";
echo"<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>";
echo"<table cellspacing=1 border=0>";
echo"<tr><td>".$_SESSION['lang']['pt']."</td><td>:</td><td><select class=select2 id=pt style=width:200px multiple>".$optPtDt."</select></td></tr>";
echo"<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td><select class=select2 id=periodeId style=width:200px>".$optper."</select></td></tr>";
echo"<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>:</td><td><select class=select2 id=intiplasma style=width:200px>".$intiplasma."</select></td></tr>";

echo"<tr><td></td><td></td><td colspan=3>
<button onclick=\"laporanPreview()\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
<button onclick=\"laporanExcel(event)\" class=\"mybutton\" name=\"excel2\" id=\"excel2\">".$_SESSION['lang']['excel']."</button>
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