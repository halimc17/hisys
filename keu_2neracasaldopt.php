<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body(); 
?>
<script language=javascript src='js/keu_2neracasaldopt.js?v=<?php echo time(); ?>'></script>
<?
//<script language=javascript1.2 src="js/keu_laporan.js"></script>
include('master_mainMenu.php');
// OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['neracasaldo']).'</span><br>');
OPEN_BOX('','<span class=judul><b>'.getMenu('keu_2neracasaldopt').'</b></span><br>');

//get existing period
// $str=$owlPDO->query("select distinct periode as periode from ".$dbname.".setup_periodeakuntansi order by periode desc");	 
// $str->setFetchMode(PDO::FETCH_OBJ);
// $optper="";
// while($bar=$str->fetch())
// {
    // $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
// }	

//$str="SELECT distinct(substr(tanggal,1,7)) as periode FROM ".$dbname.".keu_jurnaldt_vw where tanggal!='0000-00-00' order by periode desc";
$str="SELECT distinct periode as periode FROM ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	@$optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}

    $optrev="<option value='0'>0</option>";
    $optrev.="<option value='1'>1</option>";
    $optrev.="<option value='2'>2</option>";
    $optrev.="<option value='3'>3</option>";
    $optrev.="<option value='4'>4</option>";    
    $optrev.="<option value='5'>5</option>";     
//}




$CLM='';
$str=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='CLM'");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=  $str->fetch()){
	$CLM=$bar->noakundebet;
}


$str=$owlPDO->query("select noakun,namaakun from ".$dbname.".keu_5akun
	where level = '5' and noakun!='".$CLM."' order by noakun");
$str->setFetchMode(PDO::FETCH_OBJ);
$optakun="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=$str->fetch()){
	$optakun.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";
}





$arrTampilan=array("0"=>"Tampilkan Nol","1"=>"Tidak Tampilkan Nol");
foreach ($arrTampilan as $key => $value) {
    @$optTampilan.="<option value='".$key."'>".$value."</option>";
}
echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
    
	<table border=0><tr>
		<tr>
		<td>".$_SESSION['lang']['noakun']."</td>
		<td>:</td>
		<td><select id=akundari style='width:200px;' onchange=hideById('printPanel')>".$optakun."</select>
		<img id='akundari' onclick=z.elSearch('akundari',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
		
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select style='width:85px' id=periode onchange=hideById('printPanel')>".$optper."</select>
		".$_SESSION['lang']['sd']."
		<select style='width:85px' id=periode1 onchange=hideById('printPanel')>".$optper."</select></td>
		</tr>
		<tr>
		<td align=center>".$_SESSION['lang']['noakunsampai']."</td>
		<td>:</td>
		<td><select id=akunsampai style='width:200px;' onchange=hideById('printPanel')>".$optakun."</select>
		<img id='akunsampai' onclick=z.elSearch('akunsampai',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
		
		<td >".$_SESSION['lang']['statussaldo']."</td>
			<td >:</td>
			<td ><select id=tampilanId style='width:200px'  onchange=hideById('printPanel') >".$optTampilan."</select></td>
		</tr>
		<tr>
			
		
	
			
			<td colspan=2></td>
			
			<td>
				<button onclick=preview() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
				<button onclick=excel() class=mybutton name=preview id=excel>".$_SESSION['lang']['excel']."</button>
				<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
			
			</td>
		</tr>
		</table>
	</fieldset>";
CLOSE_BOX();


OPEN_BOX();
// echo"<div id=printContainer class='table-scroll' style='min-height:365px;'></div>";
echo"<div class='table-scroll' style='width:100%;height:340px;overflow:scroll;' id=printContainer></div>";

CLOSE_BOX();
echo close_body();
?>