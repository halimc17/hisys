<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<?php
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPt=$optPeriode;
$optStat="<option value=''>".$_SESSION['lang']['all']."</option>";
//semua pt
// $sPt="select distinct kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI'";

// $qPt=$owlPDO->query($sPt) or die(print " Gagal: ".PDOException::getMessage());
// $qPt->setFetchMode(PDO::FETCH_ASSOC);
// while($rPt=$qPt->fetch()){
    // $optPt.="<option value='".$rPt['kodeorganisasi']."'>".$rPt['namaorganisasi']."</option>";
// }


$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optKodeorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optKodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optKodeorg.="</optgroup>";
	}
}


//periode akuntansi
$sPeriode="select distinct substr(periode,1,4) as tahun from ".$dbname.".setup_periodeakuntansi order by periode desc";

$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch()){
	$optPeriode.="<option value=".$rPeriode['tahun'].">".$rPeriode['tahun']."</option>";
}


$arr="##thnId##unitId##bulanId";
//$arrKry="##kdeOrg##period##idKry##tgl_1##tgl_2";
?>

<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script>
function Clear1(){
    document.getElementById('thnId').options[0].selected=true;
    document.getElementById('unitId').options[0].selected=true;
    document.getElementById('bulanId').options[0].selected=true;
}

function resetBulan(){
    document.getElementById('bulanId').innerHTML="";
    document.getElementById('unitId').value="";
}

function getBulan() {
	thnId = document.getElementById('thnId').options[document.getElementById('thnId').selectedIndex].value;
	unitId = document.getElementById('unitId').options[document.getElementById('unitId').selectedIndex].value;

	param = 'proses=bulanapaaja&thnId=' + thnId + '&unitId=' + unitId;
	tujuan = 'traksi_slave_2biayaBengkel.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('bulanId').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
// function displayDetail(bulan,noakun,unit,ev){
   // param='noakun='+noakun+'&periode='+bulan+'&periode1='+bulan;
   // param+='&lmperiode='+bulan+'&gudang='+unit+'&revisi=0';
   // tujuan='keu_slave_getBBDetail.php'+"?"+param;  
   // width='800';
   // height='300';

   // content="<fieldset style=width:98%;height:97%><iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe></fieldset>"
   // showDialog1('Detail transaction'+noakun,content,width,height,ev); 
// }

function displayDetail(bulan,noakun,unit,ev){
	param='noakun='+noakun+'&periode='+bulan+'&periode1='+bulan;
	param+='&lmperiode='+bulan+'&gudang='+unit+'&revisi=0';	
	
	tujuan = 'keu_slave_getBBDetail.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
</script>
<link rel=stylesheet type='text/css' href='style/zTable.css'>
<?

OPEN_BOX('','<span class=judul>'.getMenu('traksi_2biayaBengkel').'</span>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']; ?></b></legend>
<table cellspacing="1" border="0" >

<tr>
	<td><label><?php echo $_SESSION['lang']['tahun']?></label></td>
	<td>:</td>
	<td><select class='select2' id="thnId" name="thnId" style="width:200px" onchange="resetBulan()">
		<?php echo $optPeriode?>
	</select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['traksi']?></label></td>
	<td>:</td>
	<td><select class='select2' id="unitId" name="unitId" style="width:200px" onchange="getBulan()">
		<?php echo $optKodeorg?>
	</select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['bulan']?></label></td>
	<td>:</td>
	<td><select class='select2' id="bulanId" name="bulanId" style="width:200px">
	</select></td>
</tr>

<tr><td></td></tr>
<tr><td><td><td>
	<button onclick="zPreview('traksi_slave_2biayaBengkel','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
	<button onclick="zExcel(event,'traksi_slave_2biayaBengkel.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
	<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button>
</td></tr>
</table>
</fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer'  style='overflow:auto;height:380px;width:100%'></div>
<?php
CLOSE_BOX();
echo close_body();
?>