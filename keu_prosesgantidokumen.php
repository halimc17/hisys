<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/keu_prosesgantidokumen.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('keu_prosesgantidokumen')."<br></span>");


$optunit=$optkodebarang="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";


$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK','BULKING')";
$res=fetchdata($str);
foreach($res as $bar){
  $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
} 

$str="select * from ".$dbname.".log_5masterbarang where kodebarang in ('40000001','40000002')";
$res=fetchdata($str);
foreach($res as $bar){
  $optkodebarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
} 

echo"<fieldset style='float:left;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
     <tr>
	  <td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>		
			<td>
				<input type=text id=notransaksi disabled class=myinputtext style=\"width:150px;\">
				<img id=buttonsearchnodok onclick=getnodok() class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>
			</td>
			
      </tr>
		<tr>
       <td>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['lama']."</td>
			<td>:</td>		
			<td>
				<input type=text id=nodokumenlama disabled    class=myinputtext style=\"width:150px;\">
				<img id=buttonsearchnodok onclick=getnodok() class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>
			</td>
			
      </tr>
      <tr>
	   <td>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['baru']."</td>
			<td>:</td>		
			<td>
				<input type=text id=nodokumenbaru disabled class=myinputtext style=\"width:150px;\">
				<img id=buttonsearchnodok onclick=getnodok() class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>
			</td>
      </tr>
     
      <tr>
        <td colspan=2></td>
        <td colspan=4>
			<button class=mybutton onclick=preview('preview')>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
      <tr>
     </table>
   
   </fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo"<div  class='table-scroll' style='width:100%;height:300px;overflow:scroll;' id=container></div>";
CLOSE_BOX();
close_body();
?>