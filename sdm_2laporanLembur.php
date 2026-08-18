<?php
error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<?php
if($_SESSION['empl']['tipelokasitugas']!='HOLDING' or $_SESSION['empl']['tipelokasitugas']!='KANWIL'){	
	$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
	
	$wh=" and kodeorg='".$lksiTugas."'";
}

$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where 1=1 ".$wh." order by periode desc";
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
	$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
}
$sBagian="select distinct * from ".$dbname.".sdm_5departemen order by nama asc";
$qBagian=$owlPDO->query($sBagian) or die(print " Gagal: ".PDOException::getMessage());
$qBagian->setFetchMode(PDO::FETCH_ASSOC);
$optBagian="";
while($rBagian=$qBagian->fetch())
{
	$optBagian.="<option value=".$rBagian['kode'].">".$rBagian['kode']." - ".$rBagian['nama']."</option>";
}

// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	// $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)<=6 and tipe not like 'GUDANGTEMP%' order by induk, tipe, kodeorganisasi asc ";	
// }elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
// 	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi 
//     where length(kodeorganisasi)<=6 or induk='".$_SESSION['empl']['lokasitugas']."' and tipe not like 'GUDANGTEMP%' order by induk, kodeorganisasi asc ";	
// }else{
// 	$whr='';
// 	if($_SESSION['empl']['subbagian']!='' and $_SESSION['empl']['lokasitugas']=='KSBW'){
// 		$whr='';
// 	}elseif($_SESSION['empl']['subbagian']!=''){
// 		$whr=" and kodeorganisasi='".$_SESSION['empl']['subbagian']."'";
// 	}
// 	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') and 1=1 ".$whr." and tipe not like 'GUDANGTEMP%' order by kodeorganisasi asc";
// }

## GET UNIT
$optOrg='';
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	if($key==$_SESSION['empl']['lokasitugas']){
		$optOrg.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unit=$key;
	}else{
		$optOrg.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

// $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg=$qOrg->fetch()){
// 	$key  = substr($rOrg['kodeorganisasi'],0,4);
// 	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
// 	$induk= makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
// 	$d=$induk[$key];
// 	if($d!=$n){			
// 		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
// 	}
// 	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
// 	$n=$d;
// 	if($d!=$n){			
// 		$optOrg.="</optgroup>";
// 	}
// }

$arr="##kdOrg##periode##tgl1##tgl2##pilihan##pilihan2##pilihan3";
$arrDat="##kdeOrg##period##pilihan_2##pilihan_3##tgl_1##tgl_2";

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/sdm_2laporanLembur.js?v=<?php echo time();?>'></script>
<script language="javascript" src="js/sdm_lembur.js?v=<?php echo time(); ?>"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2laporanLembur').'</span>');

?>
<div>
<fieldset style="float: left;min-height:160px;">
<legend><b><?php echo $_SESSION['lang']['laporanLembur']." / ".$_SESSION['lang']['tanggal'];?></b></legend>
<table cellspacing="1" border="0" >
        <?php
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
        {           
        ?>
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td colspan=3><select class='select2' id="kdOrg" name="kdOrg" style="width:160px" onchange="getPeriode()"><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td  colspan=3><select class='select2'id="periode" name="periode" style="width:160px" onchange="getTgl()"><?php echo $optPeriode?></select></td></tr>    
<?php
        }
        else
        {
?>
    <tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td  colspan="3"><select class='select2'id="kdOrg" name="kdOrg" style="width:160px"><?php echo $optOrg?></select></td></tr>
    <tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td  colspan="3"><select class='select2'id="periode" name="periode" style="width:160px" onchange="getTgl()"><?php echo $optPeriode?></select></td></tr>    
<?php }?>

<tr><td><label><?php echo $_SESSION['lang']['sistemgaji']?></label></td><td>:</td><td colspan="3"><select class='select2'id="pilihan2" name="pilihan2" style="width:160px" onchange="getTgl()"><option value="semua"><?echo $_SESSION['lang']['all'];?></option><option value="bulanan"><?echo $_SESSION['lang']['bulanan'];?></option><option value="harian"><?echo $_SESSION['lang']['harian'];?></option></select></td></tr>

<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td><input disabled type="text" class="myinputtext" id="tgl1" name="tgl1" onmousemove="setCalendar(this.id);" onkeypress="return false;"  maxlength="10" style="width:60px;" /></td>
<td><?php echo $_SESSION['lang']['sd']?></td><td><input disabled type="text" class="myinputtext" id="tgl2" name="tgl2" onmousemove="setCalendar(this.id);" onkeypress="return false;"  maxlength="10" style="width:60px;" /></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['options']?></label></td><td>:</td><td  colspan="3"><select class='select2'id="pilihan" name="pilihan" style="width:160px" onchange=getTombol()><!--<option value="rupiah">Dalam Rupiah / In Rupiahs</option>--><option value="jam_aktual">Dalam Jam Aktual / Actual Hour</option><option value="jam_lembur">Dalam Jam Lembur / Beyond Actual Hour</option><option value="detail">Detail</option></select></td></tr>

<tr><td><label><?php echo $_SESSION['lang']['bagian']?></label></td><td>:</td><td colspan="3"><select class='select2'id="pilihan3" name="pilihan3" style="width:160px"><option value="semua"><?echo $_SESSION['lang']['all'];?></option><?php echo $optBagian?></select></td></tr>


<tr><td colspan="5" align="right" id=tombolId><button onclick="zPreview('sdm_slave_2laporanLembur','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="printpdf('sdm_slave_2laporanLembur','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2laporanLembur.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

</table>
</fieldset>
</div>
      <div>
<fieldset style="float: left;min-height:160px">
<legend><b><?php echo $_SESSION['lang']['laporanLembur']." / ".$_SESSION['lang']['karyawan'];?></b></legend>
<table cellspacing="1" border="0" >
<?php
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{           
?>
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td colspan="3"><select class='select2'id="kdeOrg" name="kdeOrg" style="width:160px" onchange="getPeriode2()"><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td colspan="3"><select class='select2'id="period" name="period" style="width:160px" onchange="getTgl2()"><?php echo $optPeriode?></select></td></tr>    
<?php
        }
        else
        {
?>
    <tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td colspan="3"><select class='select2'id="kdeOrg" name="kdeOrg" style="width:160px"><?php echo $optOrg?></select></td></tr>
    <tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td colspan="3"><select class='select2'id="period" name="period" style="width:160px" onchange="getTgl2()"><?php echo $optPeriode?></select></td></tr>    
<?php }?>

<tr><td><label><?php echo $_SESSION['lang']['sistemgaji']?></label></td><td>:</td><td colspan="3"><select class='select2'id="pilihan_2" name="pilihan_2" style="width:160px" onchange="getTgl2()"><option value="semua"><?echo $_SESSION['lang']['all'];?></option><option value="bulanan"><?echo $_SESSION['lang']['bulanan'];?></option><option value="harian"><?echo $_SESSION['lang']['harian'];?></option></select></td></tr>

<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td><input disabled type="text" class="myinputtext" id="tgl_1" name="tgl_1" onmousemove="setCalendar(this.id);" onkeypress="return false;"  maxlength="10" style="width:60px;" /></td>
<td><?php echo $_SESSION['lang']['sd']?></td>
<td><input disabled type="text" class="myinputtext" id="tgl_2" name="tgl_2" onmousemove="setCalendar(this.id);" onkeypress="return false;"  maxlength="10" style="width:60px;" /></td></tr>


<tr><td><label><?php echo $_SESSION['lang']['bagian']?></label></td><td>:</td><td colspan="3"><select class='select2'id="pilihan_3" name="pilihan_3" style="width:160px"><option value="semua"><?echo $_SESSION['lang']['all'];?></option><?php echo $optBagian?></select></td></tr>
<tr style='height:20px'></tr>
<tr><td colspan="5" align="right"><button onclick="zPreview('sdm_slave_2laporanLembur_rekap','<?php echo $arrDat?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('sdm_slave_2laporanLembur_rekap','<?php echo $arrDat?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2laporanLembur_rekap.php','<?php echo $arrDat?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

</table>
</fieldset>
</div>

<?php

CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer'></div>





<?php

CLOSE_BOX();
echo close_body();
?>