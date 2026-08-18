<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['slipGaji']."(".$_SESSION['lang']['harian'].")").'</span>');
?>
<?php
//ambil periode gaji sesuai dengan lokasi tugas
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$optPeriode="<option value''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where jenisgaji='H' order by periode desc limit 12";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
	$optPeriode.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
}
//ambil karyawand
$sKry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$lksiTugas."' and sistemgaji='Harian' order by namakaryawan asc";
$qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
$qKry->setFetchMode(PDO::FETCH_ASSOC);
$optKry="";
while($rKry=$qKry->fetch())
{
	$optKry.="<option value=".$rKry['karyawanid'].">".$rKry['namakaryawan']."</option>";
}
/*//ambil kodeorgannisasi dan organisasi dibawahnya
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
               where tipe in ('PABRIK','KANWIL','KEBUN','TRAKSI') and CHAR_LENGTH(kodeorganisasi)=4 order by namaorganisasi asc";	
}
else
{
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' or  kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}*/

// ambil kodeorganisasi
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
               where CHAR_LENGTH(kodeorganisasi)=4 order by namaorganisasi asc";	
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
               where CHAR_LENGTH(kodeorganisasi)=4 and tipe<>'HOLDING' order by namaorganisasi asc";	
}
else
{
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
	$sCek="select distinct * from ".$dbname.".sdm_gaji where kodeorg='".$rOrg['kodeorganisasi']."'";
	$rCek=fetchData($sCek);
	$sCek2="select distinct * from ".$dbname.".sdm_gajiho where kodeorg='".$rOrg['kodeorganisasi']."'";
	$rCek2=fetchData($sCek2);
	if((count($rCek)!=0)||(count($rCek2)!=0)){
		$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']."-".$rOrg['namaorganisasi']."</option>";	
	}
}

//ambil dept
$optDept="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTipe=$optDept;
$sDept="select * from ".$dbname.".sdm_5departemen order by nama asc";
$qDept=$owlPDO->query($sDept) or die(print " Gagal: ".PDOException::getMessage());
$qDept->setFetchMode(PDO::FETCH_ASSOC);
while($rDept=$qDept->fetch())
{
	$optDept.="<option value=".$rDept['kode'].">".$rDept['nama']."</option>";
}

//ambil tipekaryawan 
$sTipeKary="select distinct * from ".$dbname.".sdm_5tipekaryawan order by tipe asc";
$qTipeKary=$owlPDO->query($sTipeKary) or die(print " Gagal: ".PDOException::getMessage());
$qTipeKary->setFetchMode(PDO::FETCH_ASSOC);
while($rTipeKary=$qTipeKary->fetch())
{
    $optTipe.="<option value='".$rTipeKary['id']."'>".$rTipeKary['tipe']."</option>";
}

$arr="##periode##kdBag##tPkary";
$arrKry="##period##idKry";
$arrAfd="##perod##idAfd##kdBag2##tPkary2";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>

<script>
function  getPeriode()
{
    kdOrg=document.getElementById('idAfd').options[document.getElementById('idAfd').selectedIndex].value;
    tujuan='sdm_slave_2slipGajiHarianAfd';
    param='idAfd='+kdOrg;
    post_response_text(tujuan+'.php?proses=getPeriode', param, respog);
    function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}
						else {
							//alert(con.responseText);
							document.getElementById('perod').innerHTML=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  	
}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
      <?php 
           if($_SESSION['empl']['tipelokasitugas']!='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL')
           {
      ?>

<div>
<fieldset style="float: left;width:250px;height:140px;">
<legend><b><? echo $_SESSION['lang']['slipgajiharianper'];?> Periode</b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="periode" name="periode" style="width:150px"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['bagian']?></label></td><td><select id="kdBag" name="kdBag" style="width:150px"><?php echo $optDept?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tipekaryawan']?></label></td><td><select id="tPkary" name="tPkary" style="width:150px"><?php echo $optTipe?></select></td></tr>
<tr><td colspan="2" align="right">
<button onclick="zPreview('sdm_slave_2slipGajiHarian','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('sdm_slave_2slipGajiHarian','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2slipGajiHarian.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>
      
<div>
<fieldset style="float: left;width:250px;height:140px;">
<legend><b><? echo $_SESSION['lang']['slipgajiharianper']." / ".$_SESSION['lang']['karyawan'];?></b><?php //echo $_SESSION['lang']['']?></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="period" name="period" style="width:150px"><?php echo $optPeriode?></select></td></tr>

<tr><td><label><?php echo $_SESSION['lang']['namakaryawan']?></label></td><td><select id="idKry" name="idKry" style="width:150px"><?php echo $optKry?></select></td></tr>
<tr><td colspan="2"></td></tr>
<tr><td><td><button onclick="zPreview('sdm_slave_2slipGajiHarian','<?php echo $arrKry?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('sdm_slave_2slipGajiHarian','<?php echo $arrKry?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button></td></tr>
</table>
</fieldset>
</div>
<div style="margin-bottom: 30px;">
<fieldset style="float: left;width:250px;height:140px;">
<legend><b><? echo $_SESSION['lang']['slipgajiharianper']." / ";?>Station/Afdeling</b><?php //echo $_SESSION['lang']['']?></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="perod" name="perod" style="width:150px"><?php echo $optPeriode?></select></td></tr>

<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>
<select id="idAfd" name="idAfd" style="width:150px"><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['bagian']?></label></td><td><select id="kdBag2" name="kdBag2" style="width:150px"><?php echo $optDept?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tipekaryawan']?></label></td><td><select id="tPkary2" name="tPkary" style="width:150px"><?php echo $optTipe?></select></td></tr>
<tr><td colspan="2" align="right"><button onclick="zPreview('sdm_slave_2slipGajiHarianAfd','<?php echo $arrAfd?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('sdm_slave_2slipGajiHarianAfd','<?php echo $arrAfd?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2slipGajiHarianAfd.php','<?php echo $arrAfd?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
</table>
</fieldset>
</div>
<? } else { ?>
      <div>
<fieldset style="float: left;">
<legend><b><? echo $_SESSION['lang']['slipgajiharianper']." / ";?>Afdeling</b><?php //echo $_SESSION['lang']['']?></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>
<select id="idAfd" name="idAfd" style="width:150px"><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="perod" name="perod" style="width:150px"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['bagian']?></label></td><td><select id="kdBag2" name="kdBag2" style="width:150px"><?php echo $optDept?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tipekaryawan']?></label></td><td><select id="tPkary2" name="tPkary2" style="width:150px"><?php echo $optTipe?></select></td></tr>
<tr><td colspan="2" align="center"><button onclick="zPreview('sdm_slave_2slipGajiHarianAfd','<?php echo $arrAfd?>','printContainer')" class="mybutton" name="preview" id="preview" hidden>Preview</button><button onclick="zPdf('sdm_slave_2slipGajiHarianAfd','<?php echo $arrAfd?>','printContainer')" class="mybutton" name="preview" id="preview"  hidden>PDF</button><button onclick="zExcel(event,'sdm_slave_2slipGajiHarianAfd.php','<?php echo $arrAfd?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
</table>
</fieldset>
</div>
      <?php }?>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>

</div></fieldset>



<?php

CLOSE_BOX();
echo close_body();
?>