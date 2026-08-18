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
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi asc";

$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);	

while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}
$optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sThn="select distinct  tahunbudget from ".$dbname.". bgt_budget order by tahunbudget desc";
$qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
$qThn->setFetchMode(PDO::FETCH_ASSOC);

while($rThn=$qThn->fetch())
{
    $optThn.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
}
$arr="##thnBudget";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>

function summForm()
{
	//closeDialog();
	width='350';
	height='200';
	content="<div id=container style='overflow:auto;width:100%;height:190px;'></div>";
	ev='event';
	title="Detail Alokasi";
	showDialog1(title,content,width,height,ev);
}
//function getAlokasi(kdWS,kdkend,thnbdget)
function summForm2()
{
	//closeDialog();
	width='650';
	height='350';
	content="<div id=container2 style='overflow:auto;width:100%;height:330px;'></div>";
	ev='event';
	title="Detail Alokasi";
	showDialog2(title,content,width,height,ev);
}
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='200';
   height='150';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
function Clear1()
{
    document.getElementById('thnBudget').value='';
    document.getElementById('kdWS').value='';
    document.getElementById('printContainer').innerHTML='';
}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('bgt_2pt').'</span>');

?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['budgetyear']?></label></td><td><select class=select2 id='thnBudget' style="width:150px;"><?php echo $optThn?></select></td></tr>

<tr><td></td><td colspan="2"><!--<button onclick="zPreview('bgt_slave_laporan_detail','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
                    <button onclick="zPdf('bgt_slave_laporan_detail','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
                    --><button onclick="zExcel(event,'bgt_slave_2pt.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
                    <!--<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

--></table>
</fieldset>
</div>

<?
CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer' style='overflow:auto;height:450px;'>

</div>
<?php

CLOSE_BOX();
echo close_body();
?>