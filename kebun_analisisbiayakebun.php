<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('', '<span class=judul>' . getMenu('kebun_analisisbiayakebun') . '</span><br>');

$arr="##kodeorg##divisi##periode##intiplasma";

$optOrg = "<option value = ''>".$_SESSION['lang']['pilihdata']."</option>";

foreach(getOrgDetail(23) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optOrg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

$optDivisi = "<option value = ''>".$_SESSION['lang']['all']."</option>";

$str="select * from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$s="";
	if($_SESSION['empl']['subbagian']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optDivisi.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str = "select distinct(periode) as periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 25";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optPeriode .= "<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$intiplasma="<option value=''>".$_SESSION['lang']['all']."</option>";
$intiplasma.="<option value='I'>Inti</option>";
$intiplasma.="<option value='P'>Plasma</option>";

// exit('warning: '	.$optPeriode);

// $str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
// exit('warning: '	.$str);
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
//     $optPeriode.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
// }   

// $optPt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

// foreach(getOrgDetail(3) as $key => $val){
//     $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
// 	$d=$induk[$key];
// 	if($d!=$n){			
// 		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
// 		$optPt.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
// 	}
// 	$optPt.="<option value=".$key.">".$key." - ".$val."</option>";
// 	$n=$d;
// 	if($d!=$n){			
// 		$optPt.="</optgroup>";
// 	}
// }

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<script language=javascript src=js/kebun_analisisbiayakebun.js></script>
<script language=javascript src=js/option.js></script>
<script language=javascript src=js/Chart.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
echo"<div id=tableheader>";
echo "<fieldset style=float:left><legend>Form</legend>";
echo "<table cellspacing=1 border=0>";
echo "<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 multiple onchange=getDivisi() id=kodeorg style=width:200px>".$optOrg."</select></td></tr>";
echo "<tr><td>".$_SESSION['lang']['divisi']."</td><td>:</td><td><select class=select2 multiple id=divisi style=width:200px>".$optDivisi."</select></td></tr>";
echo "<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td><select class=select2 id=periode style=width:200px>".$optPeriode."</select></td></tr>";
echo "<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>:</td><td><select class=select2 id=intiplasma style=width:200px>".$intiplasma."</select></td></tr>";
echo "<tr><td></td><td></td><td colspan=3>

<button onclick=\"if(getValue('kodeorg')==''){alert('Unit harus dipilih');}else if(getValue('periode')==''){alert('Periode harus dipilih');}else{zPreview('kebun_slave_analisisbiayakebun','".$arr."','printContainer2');showheader();}\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>

<button onclick=\"if(getValue('kodeorg')==''){alert('Unit harus dipilih');}else if(getValue('periode')==''){alert('Periode harus dipilih');}else{zExcel(event,'kebun_slave_analisisbiayakebun.php','".$arr."')}\" class=\"mybutton\" name=\"excel2\" id=\"excel2\">".$_SESSION['lang']['excel']."</button>

<button onclick=batal2() class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['cancel']."</button></td></tr>";
?>
<script>
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

function getDivisi() {
    var unit = getValue('kodeorg');
    var param = 'proses=getDivisi&kodeorg=' + unit;
    var tujuan = 'kebun_slave_analisisbiayakebun.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('divisi').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function batal2() {
    $('#kodeorg').val(null).trigger('change');
    $('#divisi').val(null).trigger('change');
    $('#periode').val(null).trigger('change');
    $('#intiplasma').val(null).trigger('change');
    document.getElementById('printContainer2').innerHTML = '';
    var chartCont = document.getElementById('chartContainer');
    if (chartCont) chartCont.style.display = 'none';
}
</script>
<?php
echo "</table></fieldset>";
echo "<div style='clear:both'></div>";

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
echo"<div id='chartContainer' style='width:100%; height:400px; display:none; margin-bottom:20px; background-color: #fff;'>
    <canvas id='prodChart'></canvas>
</div>";
CLOSE_BOX();
echo close_body();
?>