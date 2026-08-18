<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<?php

$optPabrik=$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sTgl="select distinct substr(tanggal,1,7) as periode from ".$dbname.".kebun_spbht order by tanggal desc";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while($rTgl=$qTgl->fetch())
{
   $optPeriode.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}


// $sPabrik="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
// $qPabrik=$owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
// $qPabrik->setFetchMode(PDO::FETCH_ASSOC);
// $optPabrik="";
// while($rPabrik=$qPabrik->fetch())
// {
// 	$optPabrik.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['namaorganisasi']."</option>";
// }

$arrunit = getOrgDetail(1);
foreach ($arrunit as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optPabrik.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optPabrik.="<option value='".$key."'>".$key." - ".$val."</option>";			
	$n=$d;
	if($d!=$n){
		$optPabrik.="</optgroup>";
	}
}

$arrOptIP = getEnum($dbname,'setup_blok','intiplasma');
$optIP = '';
$optIP .= "<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrOptIP as $val){
	if($val=="I"){
		$optIP .= "<option value='".$val."'>Inti</option>";
	}else{
		$optIP .= "<option value='".$val."'>Plasma</option>";
	}
}

$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
$optBrg="";
while($rBrg=$qBrg->fetch())
{
	$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}
$arr="##periode##idKebun##intiplasma";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<!--<script language=javascript src=js/keu_2laporanAnggaranKebun.js></script>-->

<script language=javascript>
	function batal()
	{
		document.getElementById('periode').value='';	
		document.getElementById('idKebun').value='';
		document.getElementById('intiplasma').value='';
		document.getElementById('printContainer').innerHTML='';
	}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<div style="margin-bottom: 30px;">
<fieldset style="float: left;">
<legend><b>Produksi per Blok</b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="periode" name="periode" style="width:150px"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['kebun']?></label></td><td><select id="idKebun" name="idKebun" style="width:150px"><?php echo $optPabrik?></select></td></tr>

<?php
echo"<tr>
	<td>".$_SESSION['lang']['intiplasma']."</td>
	
	<td><select id=intiplasma style=width:150px>".$optIP."</select></td>
</tr>";
?>

<tr><td></td><td><button onclick="zPreview('kebun_slave_2produksiBlok','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['preview']?></button>



<button onclick="zExcel(event,'kebun_slave_2produksiBlok.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['excel']?></button>

<button onclick="batal()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button></td></tr>
</table>
</fieldset>
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>

</div></fieldset>
<?
CLOSE_BOX();
echo close_body();
?>