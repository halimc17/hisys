<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/pmn_2summarydelivery.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('pmn_2summarydelivery')."<br></span>");


#= untuk unit ht
$optkodebarang=$opttahun="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";

$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res=fetchdata($str);
foreach($res as $bar){
  $optkodebarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
} 

$str="select distinct(substr(periode,1,4)) as tahun from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=fetchdata($str);
foreach($res as $bar){
  $opttahun.="<option value='".$bar['tahun']."'>".$bar['tahun']."</option>";
}



echo"<fieldset style='float:left;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
		<td>".$_SESSION['lang']['komoditi']."</td>
        <td>:</td>
        <td><select class='select2' id=kodebarang  style='width:150px;'>".$optkodebarang."</select></td>
		
		<td>".$_SESSION['lang']['tahun']."</td>
        <td>:</td>
        <td><select class='select2' id=tahun  style='width:150px;'>".$opttahun."</select></td>
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