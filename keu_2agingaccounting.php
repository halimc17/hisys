<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/keu_2agingaccounting.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('keu_2agingaccounting')."<br></span>");


$optpt=$optnoakun=$optperiode=$optsubledger="<option value=''>". $_SESSION['lang']['pilihdata']."</option>";
$optunit=$optsupplier="<option value=''>". $_SESSION['lang']['all']."</option>";



$optsubledger.="<option value='nik'>".$_SESSION['lang']['karyawan']."</option>";
$optsubledger.="<option value='kodesupplier'>".$_SESSION['lang']['supplier']."</option>";
$optsubledger.="<option value='kodecustomer'>".$_SESSION['lang']['customer']."</option>";

$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('PT')";
$res=fetchdata($str);
foreach($res as $bar){
  $optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
} 

/*
$str="select * from ".$dbname.".keu_5akun where noakun like '211%' and detail=1";
$res=fetchdata($str);
foreach($res as $bar){
  $optnoakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
} 
*/

$str="select * from ".$dbname.".keu_5akun where detail=1";
$res=fetchdata($str);
foreach($res as $bar){
  $namaakun[$bar['noakun']]=$bar['namaakun'];
} 

$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='AGINGACCOUNTING'";
$res=fetchdata($str);
foreach($res as $bar){
  $optnoakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$namaakun[$bar['noakun']]."</option>";
} 

$str="select * from ".$dbname.".log_5supplier where status=1";
$res=fetchdata($str);
foreach($res as $bar){
	$optsupplier.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
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
        <td><select class='select2' id=kodept onchange=getunit(); style='width:125px;'>".$optpt."</select></td>
		
		<td>".$_SESSION['lang']['noakun']." </td>
        <td>:</td>
        <td><select class='select2' id=noakun style='width:125px;'>".$optnoakun."</select></td>
		
		<td>".$_SESSION['lang']['subledger']." </td>
        <td>:</td>
        <td><select class='select2' id=subledger style='width:125px;'>".$optsubledger."</select></td>
		
		<td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['bulan']."</td>
		<td>:</td>		
		<td><input class=myinputtextnumber id=bulan value=4 style=\"width:125px;\" onkeypress='return angka_doang(event)' /></td>	
			
		<td hidden>".$_SESSION['lang']['kodesupplier']." </td>
        <td hidden>:</td>
        <td hidden><select class='select2' id=kodesupplier style='width:125px;'>".$optsupplier."</select></td>	
			
      </tr>
      <tr>     
		<td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select class='select2' id=kodeunit style='width:125px;'>".$optunit."</select></td>
	  
		<td>".$_SESSION['lang']['periode']." </td>
        <td>:</td>
        <td><select class='select2' id=periode style='width:125px;'>".$optperiode."</select></td>
		
		<td>".$_SESSION['lang']['nodok']."</td>
		<td>:</td>		
		<td>
			<input type=text id=nodok size=50 placeholder='Seluruhnya' class=myinputtext style=\"width:125px;\">
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