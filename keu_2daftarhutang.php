<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<?php
$optNamaOrganisasi=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$sPeriode="select distinct substring(tanggal,1,7) as periode from ".$dbname.".keu_tagihanht order by substring(tanggal,1,7) desc";
$res=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$res->fetch())
{
    if(substr($rPeriode['periode'],5,2)=='12')
    {
//        $optPeriode.="<option value=".substr($rPeriode['periode'],0,4).">".substr($rPeriode['periode'],0,4)."</option>";
        $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
    }
    else
    {
        $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
    }
}

$optOrg="<select id=kdOrg name=kdOrg style=\"width:200px;\" ><option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select distinct kodeorg from ".$dbname.".keu_tagihanht order by kodeorg asc ";	
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$res->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorg'].">".$optNamaOrganisasi[$rOrg['kodeorg']]."</option>";
}
$optOrg.="</select>";

// Query Supplier Filter
$optSup="<select id=kdsup name=kdsup style=\"width:200px;\" ><option value='' selected>".$_SESSION['lang']['all']."</option>";
$qSup="SELECT distinct(a.kodesupplier) as supplierid FROM ".$dbname.".`keu_tagihanht` a where a.kodesupplier!=''";	
$rSup=$owlPDO->query($qSup) or die(print " Gagal: ".PDOException::getMessage());
$rSup->setFetchMode(PDO::FETCH_ASSOC);
while($r=$rSup->fetch())
{
	$optNmSupp=makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier',"supplierid='".$r['supplierid']."'");
	$optRam = makeOption($dbname,'log_5klsupplier','kode,kelompok',"kode='".$r['supplierid']."'");
	$arrSupplier[$r['supplierid']] = ($optNmSupp[$r['supplierid']]==''?$optRam[$r['supplierid']]:$optNmSupp[$r['supplierid']]);
}

asort($arrSupplier);

foreach($arrSupplier as $key=>$val)
{
	$optSup.="<option value=".$key.">".$val."</option>";
}

$optSup.="</select>";
// END

//$arr="##kdOrg##tgl1##tgl2##statTagihan";
$arr="##kdOrg##kdsup##periode##statTagihan##periode2";

$arrOpt=array("0"=>"Belum Terbayar","1"=>"Sudah Terbayar");
$optStatus="<option value='2'>".$_SESSION['lang']['all']."</option>";
foreach($arrOpt as $listBrs =>$dtStat)
{
    $optStatus.="<option value='".$listBrs."'>".$dtStat."</option>";
}
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script>
function Clear1()
{
    document.getElementById('kdOrg').value='';
    document.getElementById('periode').value='';
    document.getElementById('periode2').value='';
    document.getElementById('statTagihan').value='';
    document.getElementById('printContainer').innerHTML='';
}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['daftarHutang']).'</span><br>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td><td><?php echo $optOrg; ?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['supplier']?></label></td><td>:</td><td><?php echo $optSup; ?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['dari']?></label></td><td>:</td><td><select id='periode' style="width:85px"><?php echo $optPeriode?> </select> <?php echo $_SESSION['lang']['sd']?> 
<select id='periode2' style="width:86px"><?php echo $optPeriode?></select></td></tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['status']?></label></td><td>:</td>
	<td><select id="statTagihan" name="statTagihan" style="width:200px"><?php echo $optStatus?></select></td>
</tr>

<!--<tr><td><label><?php echo $_SESSION['lang']['tanggal']." ".$_SESSION['lang']['tagihan']." ".$_SESSION['lang']['dari']?></label></td><td><input type="text" class="myinputtext" id="tgl1" name="tgl1" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:150px;" /></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggalsampai']?></label></td><td><input type="text" class="myinputtext" id="tgl2" name="tgl2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:150px;" /></td></tr>-->


<tr><td><td><td><button onclick="zPreview('keu_slave_2daftarhutang','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('keu_slave_2daftarhutang','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'keu_slave_2daftarhutang.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

</table>
</fieldset>
</div>

<div style="margin-bottom: 30px;">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'>

</div></fieldset>

<?php

CLOSE_BOX();
echo close_body();
?>