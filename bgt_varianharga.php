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
$arr="##thnBudget##regional##kdBudget";
//ambil regional 
$optreg=$optOrg;
$str="select regional, nama from ".$dbname.".bgt_regional  order by regional";
 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
 $res->setFetchMode(PDO::FETCH_OBJ);

while($bar=$res->fetch())
{
    $optreg.="<option value='".$bar->regional."'>".$bar->regional." - ".$bar->nama."</option>";	
}
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/keu_2laporanAnggaranKebun.js></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('bgt_varianharga').'</span><br>');

?>

<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['budgetyear']?></label></td><td><select class=select2 id="thnBudget" name="thnBudget" style="width:150px"><? echo $optThn?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['regional']?></label></td><td><select class=select2 id="regional" name="regional" style="width:150px"><?php echo $optreg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['kodebudget']?></label></td><td><select class=select2 id="kdBudget" name="kdBudget" style="width:150px"><? echo $optSup?></select></td></tr>

<tr><td></td><td colspan="2"><button onclick="zPreview('bgt_slave_varianharga','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event,'bgt_slave_varianharga.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>

<?
CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer' style='overflow:auto;height:450px;'></div>
<?php
CLOSE_BOX();
echo close_body();
?>