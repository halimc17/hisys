<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/kebun_2laporansebaranbloktt.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('kebun_2laporansebaranbloktt')."<br></span>");


$optpt=$optnoakun=$optperiode="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";
$optunit=$optcustomer="<option value=''>". $_SESSION['lang']['all']."</option>";


$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('PT')";
$res=fetchdata($str);
foreach($res as $bar){
  $optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
} 

$str="select * from ".$dbname.".keu_5akun where noakun like '211%' and detail=1";
$res=fetchdata($str);
foreach($res as $bar){
  $optnoakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
} 


$str="select * from ".$dbname.".pmn_4customer order by namacustomer asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optcustomer.="<option value='".$bar['kodecustomer']."'>".$bar['kodecustomer']." - ".$bar['namacustomer']."</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=fetchdata($str);
foreach($res as $bar){
	$optperiode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

echo"<fieldset style='float:left;'>
     <legend>".$_SESSION['lang']['form']."</legend>
     <table>
        <td>".$_SESSION['lang']['pt']."</td>
        <td>:</td>
        <td><select class='select2' id=kodept onchange=getunit(); style='width:150px;'>".$optpt."</select></td>
		
		
		<td hidden>".$_SESSION['lang']['kodecustomer']." </td>
        <td hidden>:</td>
        <td hidden><select class='select2' id=kodecustomer style='width:150px;'>".$optcustomer."</select></td>
		
		
      </tr>
      <tr>     
		<td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select class='select2' id=kodeunit style='width:150px;'>".$optunit."</select></td>	
	  </tr>
      <tr>
        <td>".$_SESSION['lang']['tanggal']." Panen</td>
		<td>:</td>		
		<td>
			<input type=text class=myinputtext id=tanggal1 name=tanggal1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:60px;/>
			s/d
			<input type=text class=myinputtext id=tanggal2 name=tanggal2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:60px;/>			
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