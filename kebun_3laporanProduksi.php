<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
$frm[0]='';
$frm[0]='';
$frm[1]='';

?>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

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

// $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg=$qOrg->fetch()){
// 	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
// }
//for($x=0;$x<=6;$x++)
//{
//	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
//	$optper.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
//}

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

$intex=array('0'=>'External','1'=>'Internal','2'=>'Afiliasi');
$optTbs="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($intex as $dt => $rw)
{
	$optTbs.="<option value=".$dt.">".$rw."</option>";
}

$intiplasma="<option value=''>".$_SESSION['lang']['all']."</option>";
$intiplasma.="<option value='I'>Inti</option>";
$intiplasma.="<option value='P'>Plasma</option>";

$arr="##periode##tipeIntex##unit";

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>
function getKode() {
    tipeIntex = document.getElementById('tipeIntex').options[document.getElementById('tipeIntex').selectedIndex].value;
    param = 'tipeIntex=' + tipeIntex + '&proses=getKdorg';
    tujuan = "kebun_slave_3laporanProduksi.php";
    //alert(param);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('unit').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
    //  alert(fileTarget+'.php?proses=preview', param, respon);
    post_response_text(tujuan, param, respon);

}
function getAfd(id) {
    kdOrg = document.getElementById('kdOrg_' + id).getAttribute('value');
    tglAfd = document.getElementById('tanggal_' + id).getAttribute('value');
    param = "kodeOrg=" + kdOrg + "&proses=getAfdeling" + "&brsKe=" + id + "&tglAfd=" + tglAfd;
    tujuan = "kebun_slave_3laporanProduksi.php";
    //alert(param);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //	alert(con.responseText);
                    document.getElementById('detail_' + id).innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
    //  alert(fileTarget+'.php?proses=preview', param, respon);
    post_response_text(tujuan, param, respon);
}
function detailBlok(idAwal, id) {
    kdBlok = document.getElementById('kdBlok_' + idAwal + '_' + id).innerHTML;
    nospb = document.getElementById('nospb_' + idAwal + '_' + id).innerHTML;
    tgl = document.getElementById('tanggal_' + idAwal).innerHTML;

    param = 'kdBlok=' + kdBlok + '&proses=getPrestasi' + '&tgl=' + tgl + '&brsKe=' + idAwal + '&endKe=' + id + '&nospb=' + nospb;
    tujuan = "kebun_slave_3laporanProduksi.php";

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //	alert(con.responseText);
                    document.getElementById('detailBlok_' + idAwal + '_' + id).innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
    //  alert(fileTarget+'.php?proses=preview', param, respon);
    post_response_text(tujuan, param, respon);
}
function closeBlok(idAwal, id) {
    document.getElementById('detailBlok_' + idAwal + '_' + id).innerHTML = '';
}
function closeAfd(id) {
    document.getElementById('detail_' + id).innerHTML = '';
}
function batal() {
    document.getElementById('periode').value = '';
    document.getElementById('tipeIntex').value = '';
    document.getElementById('unit').value = '';
    document.getElementById('printContainer').innerHTML = '';

}
function batal2() {
    document.getElementById('periodeId').value = '';
    document.getElementById('unitId').value = '';
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


function getdetailprd(blok, periode){
	title  = "Detail";
	param  = 'proses=detail';
	param += '&blok=' + blok;
	param += '&periode=' + periode;
	tujuan = 'kebun_slave_3laporanProduksi_detail.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup(title,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getAfdCmb(unit) {
    param = "unit=" + unit + "&proses=getAfdCmb";
    tujuan = "kebun_slave_3laporanProduksi.php";
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('afdId2').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php

OPEN_BOX('','<span class=judul>'.getMenu('kebun_3laporanProduksi').'<br></span>');
$arr2="##periodeId##unitId##intiplasma";
$arr4="##periodeId2##unitId2##afdId2##intiplasma2";
$optUniDt="<option value=''>".$_SESSION['lang']['all']."</option>";
$sUnit="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' order by namaorganisasi asc";
$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
$qUnit->setFetchMode(PDO::FETCH_ASSOC);
while($rUnit=$qUnit->fetch()){
    $optUniDt.="<option value='".$rUnit['kodeorganisasi']."'>".$rUnit['kodeorganisasi']." - ".$rUnit['namaorganisasi']."</option>";
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

$optAfdDt="<option value=''>".$_SESSION['lang']['all']."</option>";


echo"<div id=tableheader>";
$frm[0].="<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>";
$frm[0].="<table cellspacing=1 border=0>";
$frm[0].="<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td><select class=select2 id=periodeId style=width:200px>".$optper."</select></td></tr>";
$frm[0].="<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 id=unitId style=width:200px>".$optUniDt."</select></td></tr>";
$frm[0].="<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>:</td><td><select class=select2 id=intiplasma style=width:200px>".$intiplasma."</select></td></tr>";

$frm[0].="<tr><td></td><td></td><td colspan=3>
<button onclick=\"zPreview('kebun_slave_3laporanProduksi2','".$arr2."','printContainer2');showheader();\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
<!--<button onclick=\"zPdf('kebun_slave_3laporanProduksi2','".$arr2."','printContainer2')\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['pdf']."</button>-->
<button onclick=\"zExcel(event,'kebun_slave_3laporanProduksi2.php','".$arr2."');\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['excel']."</button>
<button onclick=batal2() class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['cancel']."</button></td></tr>";
$frm[0].="</table></fieldset>";
$frm[0].="<div style='clear:both'></div>";
// $frm[0].="<div id='printContainer2' style='overflow:auto;height:400px;'></div>";

$arr3="##periodetahun##unittahun##intiplasmatahun";
$frm[1].="<fieldset style=float:left><legend>Form</legend>";
$frm[1].="<table cellspacing=1 border=0>";
$frm[1].="<tr><td><labe>".$_SESSION['lang']['periode']."</label></td><td>:</td><td><select class=select2 id=periodetahun style=width:200px>".$optper."</select></td></tr>";
$frm[1].="<tr><td><labe>".$_SESSION['lang']['unit']."</label></td><td>:</td><td><select class=select2 id=unittahun style=width:200px>".$optUniDt."</select></td></tr>";
$frm[1].="<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>:</td><td><select class=select2 id=intiplasmatahun style=width:200px>".$intiplasma."</select></td></tr>";

$frm[1].="<tr><td></td><td></td><td colspan=3>
<button onclick=\"zPreview('kebun_slave_3laporanProduksi3','".$arr3."','printContainer2');showheader();\" class=\"mybutton\" name=\"preview2\" id=\"preview2\">".$_SESSION['lang']['preview']."</button>
<button onclick=\"zExcel(event,'kebun_slave_3laporanProduksi3.php','".$arr3."')\" class=\"mybutton\" name=\"excel2\" id=\"excel2\">".$_SESSION['lang']['excel']."</button>
<button onclick=batal2() class=\"mybutton\" name=\"batal2\" id=\"batal2\">".$_SESSION['lang']['cancel']."</button></td></tr>";
$frm[1].="</table></fieldset>";
$frm[1].="<div style='clear:both'></div>";
//$frm[1].="<div id='printContainer3' style='overflow:auto;height:400px;'></div>";

$frm[2].="<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>";
$frm[2].="<table cellspacing=1 border=0>";
$frm[2].="<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td><select class=select2 id=periodeId2 style=width:200px>".$optper."</select></td></tr>";
$frm[2].="<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 id=unitId2 onchange=\"getAfdCmb(this.value)\" style=width:200px>".$optUniDt."</select></td></tr>";
$frm[2].="<tr><td>".$_SESSION['lang']['afdeling']."</td><td>:</td><td><select class=select2 id=afdId2 style=width:200px>".$optAfdDt."</select></td></tr>";
$frm[2].="<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>:</td><td><select class=select2 id=intiplasma2 style=width:200px>".$intiplasma."</select></td></tr>";

$frm[2].="<tr><td></td><td></td><td colspan=3>
<button onclick=\"zPreview('kebun_slave_3laporanProduksi4','".$arr4."','printContainer2');showheader();\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
<!--<button onclick=\"zPdf('kebun_slave_3laporanProduksi4','".$arr4."','printContainer2')\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['pdf']."</button>-->
<button onclick=\"zExcel(event,'kebun_slave_3laporanProduksi4.php','".$arr4."');\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['excel']."</button>
<button onclick=batal2() class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['cancel']."</button></td></tr>";
$frm[2].="</table></fieldset>";
$frm[2].="<div style='clear:both'></div>";
// $frm[0].="<div id='printContainer2' style='overflow:auto;height:400px;'></div>";

$arr5="##periodeId3##unitId3##intiplasma3";

$frm[3].="<fieldset style=float:left><legend>Form</legend>";
$frm[3].="<table cellspacing=1 border=0>";
$frm[3].="<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td><select class=select2 id=periodeId3 style=width:200px>".$optper."</select></td></tr>";
$frm[3].="<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 id=unitId3 style=width:200px>".$optUniDt."</select></td></tr>";
$frm[3].="<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>:</td><td><select class=select2 id=intiplasma3 style=width:200px>".$intiplasma."</select></td></tr>";

$frm[3].="<tr><td></td><td></td><td colspan=3>
<button onclick=\"zPreview('kebun_slave_3laporanProduksi5_3','".$arr5."','printContainer2');showheader();\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
<!--<button onclick=\"zPdf('kebun_slave_3laporanProduksi5_3','".$arr5."','printContainer2')\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['pdf']."</button>-->
<button onclick=\"zExcel(event,'kebun_slave_3laporanProduksi5_3.php','".$arr5."')\" class=\"mybutton\" name=\"excel2\" id=\"excel2\">".$_SESSION['lang']['excel']."</button>
<button onclick=batal2() class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['cancel']."</button></td></tr>";
$frm[3].="</table></fieldset>";
$frm[3].="<div style='clear:both'></div>";
//$frm[1].="<div id='printContainer3' style='overflow:auto;height:400px;'></div>";

$hfrm[0]=$_SESSION['lang']['detail'];
$hfrm[1]='Trend Prod';
$hfrm[2]='Filter Per Blok';
$hfrm[3]='Rotasi Panen';
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