<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<?php
$optBatch="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    $sBatch="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
    $sKodeorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' order by namaorganisasi asc";
}
else
{
    $sBatch="select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg like '%".$_SESSION['empl']['lokasitugas']."%' order by periode desc";
    $sKodeorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' and kodeorganisasi like '%".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi asc";
}
$qBatch=$owlPDO->query($sBatch) or die(print " Gagal: ".PDOException::getMessage());
$qBatch->setFetchMode(PDO::FETCH_ASSOC);
while($rBatch=$qBatch->fetch())
{
    if(substr($rBatch['periode'],4,2)=='12')
    {
        $optBatch.="<option value='".substr($rBatch['periode'],0,4)."'>".substr($rBatch['periode'],0,4)."</option>";   
    }
    $optBatch.="<option value='".$rBatch['periode']."'>".$rBatch['periode']."</option>";
}

$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qKodeOrg=$owlPDO->query($sKodeorg) or die(print " Gagal: ".PDOException::getMessage());
$qKodeOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rKodeorg=$qKodeOrg->fetch())
{
    $optKodeorg.="<option value='".$rKodeorg['kodeorganisasi']."'>".$rKodeorg['namaorganisasi']."</option>";
}

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




$arr="##kdUnit##periode##periode1";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language="javascript">
function previewDetail(kdvhc, periode, periode1, status, kdbrg, kdunit, ev,tipe) {
	// title = "Detail " + kdvhc;
	// content = "<fieldset style=width:98%;height:97%><legend>" + kdvhc + "</legend><div id=contDetail ></div></fieldset>";
	// width = '800';
	// height = '450';
	// showDialog1(title, content, width, height, ev);

	param = 'kodevhc=' + kdvhc + '&periode=' + periode + '&periode1=' + periode1 + '&status=' + status + '&proses=getDetail';
	param += '&kodebarang=' + kdbrg + '&kdUnit=' + kdunit;
	param += '&tipe=' + tipe;
	tujuan = 'vhc_2slave_penggunaanBahan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					// document.getElementById('contDetail').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getdetailexcel(kdvhc, periode, periode1, status, kdbrg, kdunit, ev,tipe) {
	param = 'kodevhc=' + kdvhc + '&periode=' + periode + '&periode1=' + periode1 + '&status=' + status + '&proses=getDetail';
	param += '&kodebarang=' + kdbrg + '&kdUnit=' + kdunit;
	param += '&tipe=' + tipe;
	
	showDialog1('Report Ms.Excel', "<iframe frameborder=0 style='width:895px;height:400px'" +
		" src='vhc_2slave_penggunaanBahan.php?" + param + "'></iframe>", '900', '400', ev);
}

$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});

</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('vhc_2penggunaanBahan').'</span>');
?>
<div style="margin-bottom: 30px;">
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unitkerja']?></label></td><td>:</td><td><select class='select2' id="kdUnit" name="kdUnit" style="width:175px">
<?php echo $optKodeorg?></select></td></tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['periode']?></label></td>
	<td>:</td>
	<td>
		<select class='select2' id="periode" name="periode" style="width:75px">
			<?php echo $optBatch?></select>
			s/d
		<select class='select2' id="periode1" name="periode1" style="width:75px">
			<?php echo $optBatch?></select>
	</td>
</tr>
<tr>
	<td>
	<td>
	<td>
		<button onclick="zPreview('vhc_2slave_penggunaanBahan','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
		<button onclick="zPdf('vhc_2slave_penggunaanBahan','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
		<button onclick="zExcel(event,'vhc_2slave_penggunaanBahan.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
	</td>
</tr>
</table>
</fieldset>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>

<div id='printContainer' class='table-scroll'  style='overflow:auto;height:400px;max-width:100%'>
</div>
<?php
CLOSE_BOX();
echo close_body();
?>