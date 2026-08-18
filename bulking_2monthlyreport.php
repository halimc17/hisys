<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/bulking_2monthlyreport.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('bulking_2monthlyreport')."<br></span>");


$optunit=$optperiode="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";


$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('BULKING')";
$res=fetchdata($str);
foreach($res as $bar){
  $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
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
        <td><select id=unit style='width:150px;'>".$optunit."</select></td>
      </tr>
      <tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>:</td>
       <td><select id=periode style='width:150px;'>".$optperiode."</select></td>
      </tr>
      <tr>
        <td colspan=2></td>
        <td colspan=4>
			<button class=mybutton  onclick=preview('html')>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=preview('excel')>".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=preview('pdf') hidden>".$_SESSION['lang']['pdf']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
      <tr>
     </table>
   </fieldset>";
CLOSE_BOX();
OPEN_BOX();

// echo"<div class='table-scroll' style='width:100%;height:350px;overflow:scroll;' id=container></div>";
echo"<div style='width:100%;height:450px;overflow:scroll;' id=container></div>";
CLOSE_BOX();
close_body();
?>