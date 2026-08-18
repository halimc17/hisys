<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/log_2penerimaannoninv.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('log_2penerimaannoninv')."<br></span>");


$optunit=$optstatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optposting=$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";


#= untuk unit ht
$arrunit=array();
$arrunit=getOrgDetail(1);
foreach($arrunit as $val=>$nama){
    $optunit.="<option value='".$val."'>".$val." - ".$nama."</option>";
}

$str="select distinct(tipe) as tipe from ".$dbname.".log_noninventorydt_vw";
$res=fetchdata($str);
foreach($res as $bar){
  $opttipe.="<option value='".$bar['tipe']."'>".$bar['tipe']."</option>";
} 

 $optposting.="<option value='0'>".$_SESSION['lang']['belumposting']."</option>";
 $optposting.="<option value='1'>".$_SESSION['lang']['posting']."</option>";

echo"<fieldset style='float:left;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=unit style='width:200px;'>".$optunit."</select></td>
		
		<td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td>
            <input type=text class=myinputtext id=tanggal1 style='width:80px;' readonly onmousemove=setCalendar(this.id)  onkeypress=return false;  size=10 maxlength=10 /> S/D
            <input type=text class=myinputtext id=tanggal2 style='width:80px;' readonly onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />
        </td>
		
		<td>".$_SESSION['lang']['nopo']."</td>
		<td>:</td>		
		<td>
			<input type=text id=nopo size=50 class=myinputtext style=\"width:200px;\">
		</td>		
		
      </tr>
      <tr>
        <td>".$_SESSION['lang']['tipe']." </td>
        <td>:</td>
        <td><select id=tipe style='width:200px;'>".$opttipe."</select></td>
		
		 <td>".$_SESSION['lang']['posting']."</td>
        <td>:</td>
        <td><select id=posting style='width:200px;'>".$optposting."</select></td>
      </tr>
     
      <tr>
        <td colspan=2></td>
        <td colspan=4>
			<button class=mybutton onclick=preview('html')>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=preview('excel')>".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=preview('pdf') hidden>".$_SESSION['lang']['pdf']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
      <tr>
     </table>
   
   </fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo"<div  class='table-scroll' style='width:100%;height:350px;overflow:scroll;' id=container></div>";
CLOSE_BOX();
close_body();
?>