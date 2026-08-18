<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>

<?php
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);

$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$opttipekaryawan="<option value=''>".$_SESSION['lang']['all']."</option>";
$optorg=$optOrg=$optPeriode;
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN','PABRIK','KANWIL') order by namaorganisasi asc ";	
$sPeriode="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
	$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
}
$str="select * from ".$dbname.".sdm_5tipekaryawan where id<>0 order by tipe";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
$opttipekaryawan.="<option value='".$bar->id."'>".$bar->tipe."</option>";	
}	
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

foreach(getOrgDetail(11) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}
$arr="##kdUnit##tpKary##periode";
//$arrKry="##kdeOrg##period##idKry##tgl_1##tgl_2";
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script>
function Clear1()
{
    document.getElementById('kdUnit').value='';
    document.getElementById('periode').value='';
    document.getElementById('tpKary').value='';
}
</script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['npwp']).'</span>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr>
	<td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td>
	<td><select id="kdUnit" name="kdUnit" style="width:150px"><?php echo $optorg?>
	</select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td>
	<td><select id="periode" name="periode" style="width:150px">
		<?php echo $optPeriode?>
	</select></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['tipekaryawan']?></label></td><td>:</td>
	<td><select id="tpKary" name="tpKary" style="width:150px">
		<?php echo $opttipekaryawan?>
	</select></td>
</tr>


<tr><td><td><td>
	<button onclick="zPreview('sdm_slave_2daftarKaryNpwp','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
	<!--<button onclick="zPdf('sdm_slave_2daftarKaryNpwp','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>-->
	<button onclick="zExcel(event,'sdm_slave_2daftarKaryNpwp.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
	<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button>
</td></tr>
</table>
</fieldset>
</div>


<?php
CLOSE_BOX();
OPEN_BOX();
?>

<fieldset style='width:100%'><legend><b>Print Area</b></legend>
<div class='table-scroll' id='printContainer'style='height:500px;overflow:auto;'>

</div></fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>