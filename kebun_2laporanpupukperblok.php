<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>

function getmark(id){
	dis = document.getElementById(id).style.backgroundColor;
	if(dis!=''){
		document.getElementById(id).style.backgroundColor="";		
	}else{		
		document.getElementById(id).style.backgroundColor="cyan";
	}
}


</script>

<?
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2laporanpupukperblok').'</span><br>');

$optOrg="";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where = "";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
} else {
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optOrg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorgsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$optorgsch.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
		$optorgsch.="</optgroup>";
	}
}

$optDiv="";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) {
	$i="";
	if($bar['kodeorganisasi']==$_SESSION['empl']['subbagian']){
		$i="selected";
	}
    $optDiv.="<option value=" . $bar['kodeorganisasi'] . " ".$i.">".$bar['namaorganisasi']."</option>";
}

$optper = $optPT = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct(periode) as periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 25";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optper .= "<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$intiplasma="<option value=''>".$_SESSION['lang']['all']."</option>";
$intiplasma.="<option value='I'>Inti</option>";
$intiplasma.="<option value='P'>Plasma</option>";
?>
<script>
// Override getValue to support multiple selects
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

function laporanPreview() {
    if(getValue('kdorg') == '') {
        alert('Unit harus dipilih');
    } else if(getValue('periode') == '') {
        alert('Periode harus dipilih');
    } else {
        zPreview('kebun_slave_2laporanpupukperblok', '##kdorg##periode##intiplasma', 'printContainer');
    }
}

function laporanExcel(ev) {
    if(getValue('kdorg') == '') {
        alert('Unit harus dipilih');
    } else if(getValue('periode') == '') {
        alert('Periode harus dipilih');
    } else {
        zExcel(ev, 'kebun_slave_2laporanpupukperblok.php', '##kdorg##periode##intiplasma');
    }
}
</script>
<?php
$arr = "##kdorg##periode##intiplasma";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg style=\"width:175px;\" multiple>" . $optOrg . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=periode class=select2 style=\"width:175px;\">" . $optper . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['intiplasma'] . "</td>
                    <td>:</td>
                    <td><select id=intiplasma class=select2 style=\"width:175px;\">" . $intiplasma . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=laporanPreview() class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=laporanExcel(event) class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<div id='printContainer' class='table-scroll' style='min-height:400px;max-width:100%'; ></div>";
CLOSE_BOX();
echo close_body();
?>