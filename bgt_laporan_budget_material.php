<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<?php

$optKelompok=makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";

$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);

while($rOrg=$qOrg->fetch()){
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$optSup="<option value=''>".$_SESSION['lang']['all']."</option>";
$sSup="select distinct substr(kodebudget,3,3) as kelompokbarang from ".$dbname.".bgt_budget_detail where kodebudget like 'M%' order by substr(kodebudget,3,3) asc";
$qSup=$owlPDO->query($sSup) or die(print " Gagal: ".PDOException::getMessage());
$qSup->setFetchMode(PDO::FETCH_ASSOC);
while($rSup=$qSup->fetch())
{ 
	$optSup.="<option value=".$rSup['kelompokbarang'].">".$rSup['kelompokbarang']."-".$optKelompok[$rSup['kelompokbarang']]."</option>";
}
$optLokal="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrPo=array("0"=>"Pusat","1"=>"Lokal");
foreach($arrPo as $brsLokal =>$isiLokal)
{
    $optLokal.="<option value=".$brsLokal.">".$isiLokal."</option>";
}
$optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sThnBUdget="select distinct tahunbudget from ".$dbname.".bgt_budget order by tahunbudget desc";
$qThnBudget=$owlPDO->query($sThnBUdget) or die(print " Gagal: ".PDOException::getMessage());
$qThnBudget->setFetchMode(PDO::FETCH_ASSOC);

while($rThnBudget=$qThnBudget->fetch())
{
    $optThn.="<option value=".$rThnBudget['tahunbudget'].">".$rThnBudget['tahunbudget']."</option>";
}
$arrPilMode=array("0"=>$_SESSION['lang']['fisik'],"1"=>$_SESSION['lang']['rp']);
foreach($arrPilMode as $pilihan=>$lstData)
{
    $optPilMode.="<option value=".$pilihan.">".$lstData."</option>";
}
$arr="##kdPt##kdUnit##thnBudget##kdBudget##pilMode";

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/keu_2laporanAnggaranKebun.js></script>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});

$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
	$(this).closest(".select2-container").siblings('select:enabled').select2('open');
});
function getKdorg() {
    kdPt = document.getElementById('kdPt').options[document.getElementById('kdPt').selectedIndex].value;
    param = 'kdPt=' + kdPt + '&proses=getKdorg';
    tujuan = "log_slave_2detail_pembelian.php";
    //alert(param);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('kdUnit').innerHTML = con.responseText;
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
function searchSupplier(title, content, ev) {
    width = '500';
    height = '400';
    showDialog1(title, content, width, height, ev);
    //alert('asdasd');
}
function findSupplier() {
    nmSupplier = document.getElementById('nmSupplier').value;
    param = 'proses=getSupplierNm' + '&nmSupplier=' + nmSupplier;
    tujuan = 'log_slave_save_po.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('containerSupplier').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function setData(kdSupp) {
    l = document.getElementById('kdSup');

    for (a = 0; a < l.length; a++) {
        if (l.options[a].value == kdSupp) {
            l.options[a].selected = true;
        }
    }

    closeDialog();
    get_supplier();
}
</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_budget_material').'</span>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['budgetyear']?></label></td><td>:</td><td><select class='select2' id="thnBudget" name="thnBudget" style="width:150px"><? echo $optThn?></select></td></tr>
    <tr><td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td><td><select class='select2' id="kdPt" name="kdPt" style="width:150px" onchange="getKdorg()"><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td><select class='select2' id="kdUnit" name="kdUnit" style="width:150px"><option value=''><? echo $_SESSION['lang']['all']?></option></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['kodebudget']?></label></td><td>:</td><td><select class='select2' id="kdBudget" name="kdBudget" style="width:150px"><? echo $optSup?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['pilih']?></label></td><td>:</td><td><select class='select2' id="pilMode" name="pilMode" style="width:150px"><? echo $optPilMode?></select></td></tr>

<tr><td colspan="3"></td></tr>
<tr><td colspan=2></td><td colspan="2"><button onclick="zPreview('bgt_slave_laporan_budget_material','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event,'bgt_slave_laporan_budget_material.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>

<div id='printContainer' class='table-scroll' style='overflow:auto;height:350px;'></div>

<?php
CLOSE_BOX();
echo close_body();
?>