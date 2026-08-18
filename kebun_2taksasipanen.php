<?php
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
$optPer="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $add="";
}else{
    $add="and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}

// kebun

$arrunit = getOrgDetail(1);
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

// $sOrg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' ".$add." order by induk, kodeorganisasi";
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg=$qOrg->fetch()){
// 	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
// 	$d=$induk[$bar['kodeorganisasi']];
// 	if($d!=$n){			
// 		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
// 	}
//     $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
// 	$n=$d;
// 	if($d!=$n){			
// 		$optOrg.="</optgroup>";
// 	}
// }

// periode 
$sOrg="select distinct substr(tanggal,1,7) as periode from ".$dbname.".kebun_taksasi order by tanggal desc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
    $optPer.="<option value=".$rOrg['periode'].">".$rOrg['periode']."</option>";
}
 
//$arr0="##kebun0##afdeling0##mandor0##periode0"; 
$arr0="##kebun0##afdeling0##periode0"; 
$arr="##kebun##afdeling##tanggal"; 
$arr2="##kebun2##afdeling2##periode2"; 
$arr3="##kebun3##afdeling3##periode3"; 
$arr4="##kebun4##afdeling4##periode4"; 
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script>
function getPeriode(tab){
    if(tab==0){
        kebun=document.getElementById('kebun0').options[document.getElementById('kebun0').selectedIndex].value;        
        param='kebun='+kebun+'&proses=getAfdeling0';
    }
    if(tab==1){
        kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;        
        param='kebun='+kebun+'&proses=getAfdeling';
    }
    if(tab==2){
        kebun=document.getElementById('kebun2').options[document.getElementById('kebun2').selectedIndex].value;        
        param='kebun='+kebun+'&proses=getAfdeling';
    }
    if(tab==3){
        kebun=document.getElementById('kebun3').options[document.getElementById('kebun3').selectedIndex].value;        
        param='kebun='+kebun+'&proses=getAfdeling';
    }
    if(tab==4){
        kebun=document.getElementById('kebun4').options[document.getElementById('kebun4').selectedIndex].value;        
        param='kebun='+kebun+'&proses=getAfdeling';
    }

    tujuan='kebun_slave_2taksasipanen.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    cor=con.responseText.split("####");
                    if(tab==0){
                        document.getElementById('afdeling0').innerHTML=cor[0];                        
                        document.getElementById('mandor0').innerHTML=cor[1];                        
                    }
                    if(tab==1){
                        document.getElementById('afdeling').innerHTML=cor[0];                        
                    }
                    if(tab==2){
                        document.getElementById('afdeling2').innerHTML=cor[0];                        
                    }
                    if(tab==3){
                        document.getElementById('afdeling3').innerHTML=cor[0];                        
                    }
                    if(tab==4){
                        document.getElementById('afdeling4').innerHTML=cor[0];                        
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function pindahtanggal(kebun,afdeling,tanggal) {
    var workField = document.getElementById('printContainer');
    var param = "kebun="+kebun+"&afdeling="+afdeling+"&tanggal="+tanggal;

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                    document.getElementById('tanggal').value=tanggal;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    post_response_text('kebun_slave_2taksasipanen.php?proses=preview', param, respon);
}
</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php


$title[0]=$_SESSION['lang']['laporan']." ".$_SESSION['lang']['rencanapanen']." ".$_SESSION['lang']['harian'];
$title[1]=$_SESSION['lang']['laporan']." ".$_SESSION['lang']['rencanapanen'];
$title[2]=$_SESSION['lang']['laporan']." ".$_SESSION['lang']['rencanapanen']." ".$_SESSION['lang']['bulanan'];
$title[3]=$_SESSION['lang']['laporan']." ".$_SESSION['lang']['rencanapanen']." ".$_SESSION['lang']['harian']."/".$_SESSION['lang']['blok'];
$title[4]=$_SESSION['lang']['laporan']." ".$_SESSION['lang']['rencanapanen']." vs ".$_SESSION['lang']['realisasi'];

//<tr>
//    <td><label>".$_SESSION['lang']['mandor']."</label></td>
//    <td><select id=\"mandor0\" name=\"mandor0\"  style=\"width:150px\"><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
//</tr>
$frm[1]="<fieldset style=\"float: left;\">
<legend><b>Form</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
    <td><label>".$_SESSION['lang']['kebun']."</label></td><td>:</td>
    <td><select class=select2 id=\"kebun0\" name=\"kebun0\"  style=\"width:150px\" onchange=getPeriode(0)>".$optOrg."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['afdeling']."</label></td><td>:</td>
    <td><select class=select2 id=\"afdeling0\" name=\"afdeling0\"  style=\"width:150px\">".$optAfd."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['periode']."</label></td><td>:</td>
    <td><select class=select2 id=\"periode0\" name=\"periode0\"  style=\"width:150px\">".$optPer."</select></td>
</tr>


<tr>
    <td colspan=\"2\"><td colspan=\"2\">
        <button onclick=\"zPreview('kebun_slave_2taksasipanen0','".$arr0."','printContainer0')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"zExcel(event,'kebun_slave_2taksasipanen0.php','".$arr0."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\">
</div>
<fieldset style='clear:both'>
<div id='printContainer0' style='overflow:auto;height:400px;max-width:100%'>
</div></fieldset>";

$frm[0]="<fieldset style=\"float: left;\">
<legend><b>Form</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
    <td><label>".$_SESSION['lang']['kebun']."</label></td><td>:</td>
    <td><select class=select2 id=\"kebun\" name=\"kebun\"  style=\"width:150px\" onchange=getPeriode(1)>".$optOrg."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['afdeling']."</label></td><td>:</td>
    <td><select class=select2 id=\"afdeling\" name=\"afdeling\"  style=\"width:150px\">".$optAfd."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['tanggal']."</label></td><td>:</td>
    <td><input id=\"tanggal\" name=\"tanggal\" class=\"myinputtext\" onkeypress=\"return tanpa_kutip(event)\" style=\"width:145px\" readonly=\"readonly\" onmousemove=\"setCalendar(this.id)\" type=\"text\"></td>
</tr>

<tr>
    <td colspan=\"2\"> 
    <td colspan=\"2\"> 
        <button onclick=\"zPreview('kebun_slave_2taksasipanen','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"zExcel(event,'kebun_slave_2taksasipanen.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\">
</div>
<fieldset style='clear:both'>
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'>
</div></fieldset>";

$frm[2]="<fieldset style=\"float: left;\">
<legend><b>Form</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
    <td><label>".$_SESSION['lang']['kebun']."</label></td><td>:</td>
    <td><select class=select2 id=\"kebun2\" name=\"kebun2\"  style=\"width:150px\" onchange=getPeriode(2)>".$optOrg."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['afdeling']."</label></td><td>:</td>
    <td><select class=select2 id=\"afdeling2\" name=\"afdeling2\"  style=\"width:150px\">".$optAfd."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['periode']."</label></td><td>:</td>
    <td><select class=select2 id=\"periode2\" name=\"periode2\"  style=\"width:150px\">".$optPer."</select></td>
</tr>

<tr>
    <td colspan=\"2\">
    <td colspan=\"2\">
        <button onclick=\"zPreview('kebun_slave_2taksasipanen2','".$arr2."','printContainer2')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"zExcel(event,'kebun_slave_2taksasipanen2.php','".$arr2."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\">
</div>
<fieldset style='clear:both'>
<div id='printContainer2' style='overflow:auto;height:400px;max-width:100%'>
</div></fieldset>";

$frm[3]="<fieldset style=\"float: left;\">
<legend><b>Form</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
    <td><label>".$_SESSION['lang']['kebun']."</label></td><td>:</td>
    <td><select class=select2 id=\"kebun3\" name=\"kebun3\"  style=\"width:150px\" onchange=getPeriode(3)>".$optOrg."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['afdeling']."</label></td><td>:</td>
    <td><select class=select2 id=\"afdeling3\" name=\"afdeling3\"  style=\"width:150px\">".$optAfd."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['periode']."</label></td><td>:</td>
    <td><select class=select2 id=\"periode3\" name=\"periode3\"  style=\"width:150px\">".$optPer."</select></td>
</tr>


<tr>
    <td colspan=\"2\">
    <td colspan=\"2\">
        <button onclick=\"zPreview('kebun_slave_2taksasipanen3','".$arr3."','printContainer3')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"zExcel(event,'kebun_slave_2taksasipanen3.php','".$arr3."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\">
</div>
<fieldset style='clear:both'>
<div id='printContainer3' style='overflow:auto;height:400px;max-width:100%'>
</div></fieldset>";

$frm[4]="<fieldset style=\"float: left;\">
<legend><b>Form</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
    <td><label>".$_SESSION['lang']['kebun']."</label></td><td>:</td>
    <td><select class=select2 id=\"kebun4\" name=\"kebun4\"  style=\"width:150px\" onchange=getPeriode(4)>".$optOrg."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['afdeling']."</label></td><td>:</td>
    <td><select class=select2 id=\"afdeling4\" name=\"afdeling4\"  style=\"width:150px\">".$optAfd."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['periode']."</label></td><td>:</td>
    <td><select class=select2 id=\"periode4\" name=\"periode4\"  style=\"width:150px\">".$optPer."</select></td>
</tr>

<tr>
    <td colspan=\"2\">
    <td colspan=\"2\">
        <button onclick=\"zPreview('kebun_slave_2taksasipanen4','".$arr4."','printContainer4')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"zExcel(event,'kebun_slave_2taksasipanen4.php','".$arr4."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\"></div>
<fieldset style='clear:both'>
<div id='printContainer4' style='overflow:auto;height:400px;max-width:100%'>
</div></fieldset>";
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2taksasipanen').'</span><br>');
//========================
$hfrm[0]=$title[0];
$hfrm[1]=$title[1];
$hfrm[2]=$title[2];
$hfrm[3]=$title[3];
$hfrm[4]=$title[4];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,250,'100%');
//===============================================


CLOSE_BOX();
echo close_body();
?>