<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
// dz, sep 26 2011
?>
<script language=javascript1.2 src="js/bgt_laporan_produksi_pks.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
//OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['laporanbukubesar']).'</b>');


$str="select distinct tahunbudget from ".$dbname.".bgt_budget
	  order by tahunbudget desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$opttahun="";
while($bar=$res->fetch())
{
	$opttahun.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
}
$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' order by namaorganisasi desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optpabrik="";
while($bar=$res->fetch())
{
	$optpabrik.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

$optpabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(13) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".substr($key,0,4)."'");
	$d=$induk[substr($key,0,4)];
	if($d!=$n){			
		$optpabrik.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optpabrik.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optpabrik.="</optgroup>";
	}
}

OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_produksi_pks').'</span>');
?>
<br><fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['budgetyear']?></label></td><td>:</td><td><select class='select2' id=tahun style='width:175px;' ><?php echo $opttahun; ?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['kdpabrik']?></label></td><td>:</td><td><select class='select2' id=pabrik style='width:175px;' ><?php echo $optpabrik; ?></select></td></tr>
<tr><td colspan=2></td><td>
		<button class=mybutton onclick=getProduksi()><?php echo $_SESSION['lang']['preview'] ?></button>
		<button class=mybutton onclick=getProduksixls('excel')><?php echo $_SESSION['lang']['excel'] ?></button>
		</td></tr>

<!--<tr height="20"><td colspan="2">&nbsp;</td></tr>-->

<!--<tr><td colspan="2"><button onclick="zPreview('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2rekapabsen.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>-->

</table>
</fieldset>
<?
CLOSE_BOX();
OPEN_BOX('','');
echo"<!--<span id=printPanel style='display:none;'>
     <img onclick=produksiKeExcel(event,'bgt_slave_laporan_produksi_pks_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
     <img onclick=produksiKePDF(event,'bgt_slave_laporan_produksi_pks_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </span>-->
	 <div id=container style='width:100%;height:450px;overflow:auto;'>

     </div>";
CLOSE_BOX();
close_body();
?>