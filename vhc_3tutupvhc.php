<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/vhc_3tutupvhc.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('vhc_3tutupvhc')."<br></span>");

$opttraksi=$optperiode="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";


$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 24";
$res=fetchdata($str);
foreach($res as $bar){
	$optperiode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

$arrunit=array();
$arrunit=getOrgDetail(18);
foreach($arrunit as $val=>$nama){
    $opttraksi.="<option value='".$val."'>".$val." - ".$nama."</option>";
} 

// echo $opttraksi;
echo"<fieldset style='float:left;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
      </tr>
      <tr>     
		<td>".$_SESSION['lang']['traksi']."</td>
        <td>:</td>
        <td><select class='select2' id=kodetraksi style='width:150px;'>".$opttraksi."</select></td>
	  
		<td>".$_SESSION['lang']['periode']." </td>
        <td>:</td>
        <td><select class='select2' id=periode style='width:150px;'>".$optperiode."</select></td>
	  </tr>
    
      <tr>
        <td colspan=2></td>
        <td colspan=4>
			<button class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
      <tr>
     </table>
   
   </fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo"<div  class='table-scroll' style='height:65vh;overflow:auto;' id=container></div>";
CLOSE_BOX();
close_body();
?>