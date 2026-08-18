<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optDiv="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select distinct kodeorganisasi,namaorganisasi 
       from ".$dbname.".bibitan_mutasi c inner join ".$dbname.".organisasi a on left(c.kodeorg,4)=a.kodeorganisasi
       where tipe='KEBUN' order by namaorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$Div="select distinct kodeorganisasi,namaorganisasi 
       from ".$dbname.".bibitan_mutasi c inner join ".$dbname.".organisasi a on substr(c.afdeling,1,6)=a.kodeorganisasi
       where tipe='AFDELING' order by namaorganisasi asc";
$qDiv=$owlPDO->query($Div) or die(print " Gagal: ".PDOException::getMessage());
$qDiv->setFetchMode(PDO::FETCH_ASSOC);
while($rDiv=$qDiv->fetch())
{
	$optDiv.="<option value=".$rDiv['kodeorganisasi'].">".$rDiv['namaorganisasi']."</option>";
}

$arr="##kdUnit##divisi##periodeData";
$optModel="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode="select distinct substr(tanggal,1,7) as periode from ".$dbname.".bibitan_mutasi order by tanggal desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
    $optModel.="<option value='".$rPeriode['periode']."'>".$rPeriode['periode']."</option>";
}
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script>
function Clear1()
{
    document.getElementById('thnBudget').value='';
    document.getElementById('kdUnit').value='';
    document.getElementById('printContainer').innerHTML='';
}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2antarBibit').'</span>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php 
if($_SESSION['language']=='EN'){
    echo "Form"; 
}else{
    echo "Form"; 
}
?></b></legend>
<table cellspacing="1" border="0" >
<?php

$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sUnit="select distinct kodeorganisasi,namaorganisasi 
       from ".$dbname.".bibitan_mutasi c inner join ".$dbname.".organisasi a on left(c.kodeorg,4)=a.kodeorganisasi
       where tipe='KEBUN' order by namaorganisasi asc";
$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
$qUnit->setFetchMode(PDO::FETCH_ASSOC);
while($rUnit=$qUnit->fetch())
{
    $optUnit.="<option value='".$rUnit['kodeorganisasi']."'>".$rUnit['kodeorganisasi']." - ".$rUnit['namaorganisasi']."</option>";
}
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode="select distinct substr(tanggal,1,7) as periode from ".$dbname.".bibitan_mutasi where kodetransaksi='PNB' order by tanggal desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
    $optPeriode.="<option value='".$rPeriode['periode']."'>".$rPeriode['periode']."</option>";
}

echo"<tr><td><label>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['bibit']."</label></td><td>:</td><td><select id=\"kdUnit\" name=\"kdUnit\" style=\"width:150px\">".$optUnit."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['divisi']." ".$_SESSION['lang']['tujuan']."</label></td><td>:</td><td><select id=\"divisi\" name=\"divisi\" style=\"width:150px\">".$optDiv."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['periode']."</label></td><td>:</td><td><select id=\"periodeData\" name=\"periodeData\" style=\"width:150px\">".$optPeriode."</select></td></tr>
";
?>


<tr><td></td><td><td><button onclick="zPreview('kebun_slave_2antarBibit','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
        
        <button onclick="zExcel(event,'kebun_slave_2antarBibit.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
</td></tr>

</table>
</fieldset>
</div>

<?php

CLOSE_BOX();
OPEN_BOX();
?>
<div id='both_report'>
  <div id='head_tableboth' align=right>
    <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
      <img title='Full Screen' class='resicon' src='images/full-screen.png'>
    </a>
  </div>
<div id='printContainer' style='overflow:auto;height:400px;'>

</div></div>

<?php

CLOSE_BOX();
echo close_body();
?>