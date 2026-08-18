<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/pmn_2rekappembeliantbs.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('pmn_2rekappembeliantbs')."<br></span>");


$opttipe=$optunit=$opttipetbs="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";

#= untuk unit ht
$arrunit=array();
$arrunit=getOrgDetail(1);
foreach($arrunit as $val=>$nama){
    $optunit.="<option value='".$val."'>".$val." - ".$nama."</option>";
} 

$str = "SELECT DISTINCT tipe FROM ".$dbname.".pmn_hargabelitbs ORDER BY tipe ASC";
$res = fetchdata($str);
foreach ($res as $bar) {
	$opttipetbs .= "<option value='" . $bar['tipe'] . "'>" . $bar['tipe'] . "</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=fetchdata($str);
foreach($res as $bar){
	$optperiode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

echo"<fieldset style='float:left;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
		<td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select class='select2' id=kodeunit  style='width:150px;'>".$optunit."</select></td>
		
		<td>".$_SESSION['lang']['tipe']." TBS</td>
	  	<td>:</td>
		<td><select class='select2' id=tipetbs  style='width:150px;'>".$opttipetbs."</select></td>
		
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>		
		<td>
			<input type=text class=myinputtext id=tanggalmulai placeholder='Tanggal Mulai' name=tanggalmulai readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
			s/d
			<input type=text class=myinputtext id=tanggalsampai placeholder='Tanggal Sampai' name=tanggalsampai readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>			
		</td>
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

echo"<div  class='table-scroll' style='width:100%;height:350px;overflow:auto;' id=container></div>";
CLOSE_BOX();
close_body();
?>