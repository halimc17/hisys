<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/pmn_5kontrakpanjang.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('pmn_5kontrakpanjang')."<br></span>");


$optunit=$optkodebarang="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";

$str="select * from ".$dbname.".log_5masterbarang where kodebarang in ('40000001','40000002')";
$res=fetchdata($str);
foreach($res as $bar){
  $optkodebarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
} 

echo"<fieldset style='float:left;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
      <tr>
        <td>".$_SESSION['lang']['komoditi']." </td>
        <td>:</td>
        <td><select id=kodebarang style='width:200px;'>".$optkodebarang."</select></td>
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