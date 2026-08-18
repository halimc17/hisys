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
$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTtm="<option value=''>".$_SESSION['lang']['all']."</option>";
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);

// kebun
// $sOrg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' order by induk asc";
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg=$qOrg->fetch()){
// 	$d=getNamaOrg($rOrg['kodeorganisasi'],'induk');
// 	if($d!=$n){			
// 		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
// 	}
//     $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
// 	$n=$d;
// 	if($d!=$n){			
// 		$optOrg.="</optgroup>";
// 	}
// }

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

// tahun
$tahun=date("Y");
$optTah="";

// $sOrg="select distinct left(tahun,4) as tahun from ".$dbname.".setup_blok_tahunan order by tahun asc limit 1";
$sOrg="select distinct left(tahun,4) as tahun from ".$dbname.".setup_blok_tahunan order by tahun desc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
    $tahunkecil=$rOrg['tahun'];
    if ($tahunkecil==$tahun){
        $optTah.="<option value=".$tahunkecil." selected>".$tahunkecil."</option>";
    } else {
        $optTah.="<option value=".$tahunkecil.">".$tahunkecil."</option>";
    }
}

// for ($i = $tahun; $i >= $tahunkecil+1; $i--) {
//     if ($i==$tahun) $optTah.="<option value=".$i." selected>".$i."</option>"; else
//     $optTah.="<option value=".$i.">".$i."</option>";
// }
 
$arr0="##tahun0##kebun0##afdeling0##tahuntanam0##tipe0"; 
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>
function getAfdeling(tab){
    if(tab==0){
        kebun0=document.getElementById('kebun0').options[document.getElementById('kebun0').selectedIndex].value;        
        param='kebun0='+kebun0+'&proses=getAfdeling0';
    }

    tujuan='kebun_slave_2aresta.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    cor=con.responseText.split("####");
                    if(tab==0){
                        document.getElementById('afdeling0').innerHTML=cor[0];                        
                        document.getElementById('tahuntanam0').innerHTML=cor[1];                        
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getTahuntanam(tab){
    if(tab==0){
        kebun0=document.getElementById('kebun0').options[document.getElementById('kebun0').selectedIndex].value;        
        afdeling0=document.getElementById('afdeling0').options[document.getElementById('afdeling0').selectedIndex].value;        
        param='kebun0='+kebun0+'&afdeling0='+afdeling0+'&proses=getTahuntanam0';
    }

    tujuan='kebun_slave_2aresta.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    cor=con.responseText.split("####");
                    if(tab==0){
                        document.getElementById('tahuntanam0').innerHTML=cor[0];                        
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2aresta').'</span><br>');
$title[0]=$_SESSION['lang']['form'];

echo "<fieldset style=\"float: left;\">
<legend><b>".$title[0]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
    <td><label>".$_SESSION['lang']['tahun']."</label></td><td>:</td>
    <td><select class=select2 id=\"tahun0\" name=\"tahun0\" style=\"width:80px\">".$optTah."</select></td>
	
	<td><label>".$_SESSION['lang']['kebun']."</label></td><td>:</td>
    <td><select class=select2 id=\"kebun0\" name=\"kebun0\" style=\"width:150px\" onchange=getAfdeling(0)>".$optOrg."</select></td>
	
</tr>
<tr>
	<td><label>".$_SESSION['lang']['tahuntanam']."</label></td><td>:</td>
    <td><select class=select2 id=\"tahuntanam0\" name=\"tahuntanam0\" style=\"width:80px\">".$optTtm."</select></td>
	
    <td><label>".$_SESSION['lang']['afdeling']."</label></td><td>:</td>
    <td><select class=select2 id=\"afdeling0\" name=\"afdeling0\" style=\"width:150px\" onchange=getTahuntanam(0)>".$optAfd."</select></td>
</tr>
<tr>
    <td><label>".$_SESSION['lang']['tipe']."</label></td><td>:</td>
    <td><select class=select2 style=\"width:80px\" id=\"tipe0\" name=\"tipe0\" >
			<option value='1'>Tipe 1</option>
			<option value='2'>Tipe 2</option>
			<option value='3'>Tipe 3</option>
		</select></td>
</tr>
</tr>
<tr>
    <td colspan=\"2\"><td  colspan=\"4\">
        <button onclick=\"zPreview('kebun_slave_2aresta','".$arr0."','printContainer0')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"zExcel(event,'kebun_slave_2aresta.php','".$arr0."')\" class=\"mybutton\" name=\"excel\" id=\"excel\">Excel</button>
        <button style='display:none' onclick=\"zPdf('kebun_slave_2aresta','".$arr0."','printContainer0')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">PDF</button>
    </td>    
</tr>    
</table>
</fieldset>";

// <div style=\"margin-bottom: 30px;\">
// </div>

CLOSE_BOX();
OPEN_BOX();
echo "<div id='both_report'>
    <div id='head_tableboth' align=right>
        <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer0' table='sortable' >
            <img title='Full Screen' class='resicon' src='images/full-screen.png'>
        </a>
        <a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer0' shown='0' >
            <img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
        </a>
    </div>
<div id='printContainer0' style='width:100%;'>
</div></div>";

//========================
// $hfrm[0]=$title[0];
// //draw tab, jangan ganti parameter pertama, krn dipakai di javascript
// drawTab('FRM',$hfrm,$frm,200,1100);
//===============================================


CLOSE_BOX();
echo close_body();
?>