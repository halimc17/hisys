<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/pabrik_2dailyproduction.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('pabrik_2dailyproduction')."<br></span>");


$optunit="<option value=''>". $_SESSION['lang']['all']."</option>";
$optperiode=$optsatuan="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";

#= untuk unit ht
$str = "SELECT distinct(kodeorg) as kodeorg FROM ".$dbname.".pabrik_produksi";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optunit.="<option value='".$bar['kodeorg']."'>".$bar['kodeorg']."</option>";
}

#= untuk unit ht
$str = "SELECT distinct(substr(tanggal,1,7)) as periode FROM ".$dbname.".pabrik_produksi ORDER BY periode desc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optperiode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

$optsatuan.="<option value='1000'>MT</option>";
$optsatuan.="<option value='1'>KG</option>";

echo"<fieldset style='float:left;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
		<td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select class='select2' id=kodeunit  style='width:150px;'>".$optunit."</select></td>
		
		<td>".$_SESSION['lang']['periode']."</td>
	  	<td>:</td>
		<td><select class='select2' id=periode  style='width:150px;'>".$optperiode."</select></td>
		
		<td>".$_SESSION['lang']['satuan']."</td>
	  	<td>:</td>
		<td><select class='select2' id=satuan  style='width:150px;'>".$optsatuan."</select></td>
      </tr>
      <tr>
        <td colspan=2></td>
        <td colspan=4>
			<button class=mybutton onclick=preview('html')>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=preview('excel')>".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=preview('pdf')>".$_SESSION['lang']['pdf']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
      <tr>
     </table>
   
   </fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo"<div  class='table-scroll' style='width:100%;height:400px;overflow:auto;' id=container></div>";
CLOSE_BOX();
close_body();
?>