<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/log_catatterimapo.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

#kodeorganisasi untuk klinik harus berakhiran PK
// if($_SESSION['empl']['tipelokasitugas']=='KANWIL' and substr($_SESSION['empl']['subbagian'],-2)!='PK'){
   // $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like '%GUDANG'
       // and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
       // order by namaorganisasi";
// }else{
      // $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (left(induk,4)='".$_SESSION['empl']['lokasitugas']."' 
       // or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') and tipe like 'GUDANG%' order by namaorganisasi";
// }
$arrorgdet=getOrgDetail(2);
 $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (kodeorganisasi like '%HO' or kodeorganisasi like '%RO' or kodeorganisasi like '%LO') and kodeorganisasi in (".$arrorgdet.") order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsloc="<option value=''></option>";
while($bar=$res->fetch()){
	$optsloc.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}
	
?>


<?php
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['catatterimapo']).'</span><br>');
$frm[0].="<fieldset><legend>".$_SESSION['lang']['header']."</legend>";//getBapbList
//$frm[0].=$_SESSION['lang']['peringatanretur']."
$frm[0].="<table cellspacing=1 border=0>
     <tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select id=gudang onchange=getnotransaksi(); style=\"width:200px;\">".$optsloc."</select></td>
		
		<td>".$_SESSION['lang']['momordok']."</td>
		<td>:</td>
		<td><input type=text id=notransaksi style=\"width:195px;\" size=25 disabled class=myinputtext></td>	 
		
		<tr>
		</tr>
	    <td>".$_SESSION['lang']['tanggal']."</td><td>:</td><td>
		     <input type=text class=myinputtext style=\"width:195px;\" id=tanggal size=25 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" value='".date('d-m-Y')."'>
		</td>
	 <td>".$_SESSION['lang']['supplier']."</td>
		 <td>:</td>
		 <td><input type=text id=idsupplier class=myinputtext size=25 style=\"width:195px;\" maxlength=25 onkeypress=\"return tanpa_kutip(event);\" disabled></td>";
	
		
			// <button class=mybutton onclick=getdatapo() id=btnheader>".$_SESSION['lang']['tampilkan']."</button>
$frm[0].="</td>
	 </tr>
	 <tr>
		 <td>".$_SESSION['lang']['nopo']."</td>
		<td>:</td>
		<td>
			<input type=text disabled id=nopo style=\"width:195px;\" class=myinputtext size=25 maxength=25 onkeypress=\"return tanpa_kutip(event);\">
			<img src=images/zoom.png title='".$_SESSION['lang']['find']."' class=resicon onclick=cariPO('".$_SESSION['lang']['find']."',event)>
	
		 <td>".$_SESSION['lang']['namasupplier']."</td>
		 <td>:</td>	 
		 <td><input type=text id=namasupplier class=myinputtext size=25 style=\"width:195px;\" maxlength=25 onkeypress=\"return tanpa_kutip(event);\" disabled></td>
	 </tr>
	 </table>";


$frm[0].="</fieldset>
    <fieldset>
	   <legend>".$_SESSION['lang']['detail']."</legend>
	   <div id=container></div>
	 </fieldset>
	 ";


 
$frm[1].="<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  <fieldset style=float:left>
	  ".$_SESSION['lang']['cari_transaksi']."
	  <input type=text id=schnotransaksi size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\">
	  <button class=mybutton onclick=cariBapb()>".$_SESSION['lang']['find']."</button>
	  </fieldset><br><br><br>
	  <table class=sortable cellspacing=1 border=0 width=100%>
      <thead>
	  <tr class=rowheader>
	  <td align=center>No.</td>
	  <td align=center>".$_SESSION['lang']['unit']."</td>
	  <td align=center>".$_SESSION['lang']['momordok']."</td>
	  <td align=center>".$_SESSION['lang']['tanggal']."</td>
	  <td align=center>".$_SESSION['lang']['pt']."</td>
	  <td align=center>".$_SESSION['lang']['nopo']."</td>	
	  <td align=center>".$_SESSION['lang']['supplier']."</td> 
	  <td align=center>".$_SESSION['lang']['dbuat_oleh']."</td>
	  <td align=center>".$_SESSION['lang']['action']."</td>
	  </tr>
	  </head>
	   <tbody id=containerlist>
	   </tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>
	<script>getlistdata()</script>
	 ";	 

$hfrm[0]=$_SESSION['lang']['penerimaanbarang'];
$hfrm[1]=$_SESSION['lang']['list'];
drawTab('FRM',$hfrm,$frm,200,1200);

CLOSE_BOX();
echo close_body();

?>
