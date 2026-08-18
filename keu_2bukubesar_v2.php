<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');
?>
	<script language=javascript src='js/keu_laporanxx.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>	
<?
OPEN_BOX('','<span class=judul>'.getMenu('keu_2bukubesar_v2').'</span><br>');

$optper="";
$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str=$owlPDO->query("select distinct periode as periode from ".$dbname.".setup_periodeakuntansi order by periode desc");
$str->setFetchMode(PDO::FETCH_OBJ);	  
while($bar=$str->fetch()){
	$optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optReg=$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";

//=================ambil PT;  
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi");
    $str->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$str->fetch()){
		$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

	}
}else {
    $optpt="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    $optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";  
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";  
}
 
 
$str="select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi = 'CLM'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$clm=$bar->noakundebet;
}

$str=$owlPDO->query("select noakun,namaakun from ".$dbname.".keu_5akun where level = '5' and noakun!='".$clm."' order by noakun");
$str->setFetchMode(PDO::FETCH_OBJ);
$optakun="<option value=''></option>";
while($bar=$str->fetch()){
	$optakun.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";
}

$qwe="01-".date("m-Y");


?>
<fieldset style='float:left' id=formfilter style=display:block>
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><?php echo $_SESSION['lang']['pt']?></td><td>:</td>
	<td><select class='select2' id=pt style='width:200px;'  onchange=getReg()><?php echo $optpt; ?></select>

	<td><?php echo $_SESSION['lang']['regional']?></td><td>:</td>
	<td><select class='select2' id=regional style='width:200px;' onchange=getUnit()><?php echo $optReg; ?></select>

	<td><?php echo $_SESSION['lang']['unit']?></td><td>:</td>
	<td><select class='select2' id=gudang style='width:200px;'><?php echo $optgudang; ?></select></td>
</tr>

<tr>
	<td><?php echo $_SESSION['lang']['periode']?></td><td>:</td>
	<td><select class='select2' id=periode style='width:94px;' onchange=ambilPer2()><?php echo $optper; ?></select> - 
		<select class='select2' id=periode2 style='width:94px;' ><?php echo $optper; ?></select>
	<td><?php echo $_SESSION['lang']['noakundari']?></td><td>:</td>
	<td><select class='select2' id=akundari style='width:200px;' onchange=ambilAkun2(this.options[this.selectedIndex].value)><?php echo $optakun; ?></select></td>

	<td><?php echo $_SESSION['lang']['noakunsampai']?></td><td>:</td>
	<td><select class='select2' id=akunsampai style='width:200px;'><option value=""></option></select>
	</td>
</tr>

<?
echo"
<tr>
	<td></td><td></td>
	<td>
		<button class=mybutton onclick=getLaporanBukuBesarv2('html')>".$_SESSION['lang']['preview']."</button>
		<!--button class=mybutton onclick=getLaporanBukuBesarv2('excel')>".$_SESSION['lang']['excel']."</button-->
	</td>
</tr>";
?>

</table>
</fieldset>
<?

CLOSE_BOX();
OPEN_BOX('','');
echo"<div id=container></div>";
CLOSE_BOX();
close_body();
?>
