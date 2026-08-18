<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/log_returkegudang.js?v=<?php echo time()?>'></script>
<?
include('master_mainMenu.php');

if(isTransactionPeriod())//check if transaction period is normal
{
OPEN_BOX('','<span class=judul>'.getMenu('log_returKeGudang').'</span><br>');

$frm[0]='';
$frm[1]='';
echo "<fieldset ><legend>";
echo" <b>".$_SESSION['lang']['periode']." : <span id=displayperiod>".tanggalnormal($_SESSION['org']['period']['start'])." - ".tanggalnormal($_SESSION['org']['period']['end'])."</span></b>";
echo"</legend>";
#kodeorganisasi untuk klinik harus berakhiran PK
  if($_SESSION['empl']['tipelokasitugas']=='KANWIL' and substr($_SESSION['empl']['subbagian'],-2)!='PK'){
   $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANG'
       and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
       order by namaorganisasi";// and kodeorganisasi not in ('SENE10', 'SKNE10', 'SOGE30') order by namaorganisasi";
}else{
   $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (left(induk,4)='".$_SESSION['empl']['lokasitugas']."' 
       or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') and tipe like 'GUDANG%' order by kodeorganisasi";
    
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsloc="<option value=''></option>";
while($bar=$res->fetch())
{
	$optsloc.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

echo"<fieldset style=float:left>
     <legend>
	 ".$_SESSION['lang']['daftargudang']."
     </legend>
	  ".$_SESSION['lang']['pilihgudang'].": <select id=sloc>".$optsloc."</select>
	   <button onclick=setSlocreturgudang('simpan') class=mybutton id=btnsloc>".$_SESSION['lang']['save']."</button>
	   <button onclick=setSlocreturgudang('ganti') class=mybutton>".$_SESSION['lang']['ganti']."</button>
	  
	 </fieldset><div style=clear:both><br></div>";

$frm[0].="<fieldset><legend>".$_SESSION['lang']['header']."</legend>";

$frm[0].="<fieldset><table cellspacing=1 border=0>
     <tr>
		<td width=110px>".$_SESSION['lang']['momordok']."</td><td>:</td>
		<td><input type=text id=nodok size=25 disabled class=myinputtext></td>	 
	    <td>".$_SESSION['lang']['tanggalretur']."</td><td>:</td><td>
		     <input type=text class=myinputtext id=tanggal size=8 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" readonly>
		</td>
	 </tr>
	 </table></fieldset>
	 <fieldset><legend>".$_SESSION['lang']['dokumenlama']."</legend>
	 <table border=0>
	 <tr>
	 <td width=110px>".$_SESSION['lang']['nomorlama']."</td><td>:</td><td><input type=text id=nomorlama class=myinputtext size=20 maxength=25 onkeypress=\"return tanpa_kutip(event);\">
	 <img src='images/onebit_02.png' style='position:relative;top:3px;padding-right:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchNoLama('".$_SESSION['lang']['nomorlama']."','<fieldset>Find : &nbsp;<input type=text class=myinputtext id=no_nomorlama onkeypress=enterkey(event,nomorlama2)><button class=mybutton onclick=nomorlama2()>Find</button></fieldset><div id=container></div>',event)\";>
	 </td>
	 <td>".$_SESSION['lang']['kodebarang']."</td><td>:</td><td><input type=text id=kodebarang class=myinputtext size=7 maxength=11 onkeypress=\"return angka_doang(event);\">
	 <img src='images/onebit_02.png' style='position:relative;top:3px;padding-right:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchBARANGLama('".$_SESSION['lang']['kodebarang']."','<div id=container2></div>',event)\";>
     <td>".$_SESSION['lang']['kodeblok']."</td><td>:</td><td><input type=text id=kodeblok class=myinputtext size=10 maxength=11 onkeypress=\"return tanpa_kutip_dan_sepasi(event);\">    
     <input type=hidden id=mesin class=myinputtext size=10 maxength=11 onkeypress=\"return tanpa_kutip_dan_sepasi(event);\">    
	 <img src='images/onebit_02.png' style='position:relative;top:3px;padding-right:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchBLOKLama('".$_SESSION['lang']['kodeblok']."','<div id=container3></div>',event)\";>
	 <button class=mybutton onclick=Fverify()>".$_SESSION['lang']['cek']."</button>
	 </td>
	 </tr>
	 <tr>
	 <td>".$_SESSION['lang']['namabarang']."</td><td>:</td><td><input type=text id=namabarang class=myinputtext size=25 maxength=25 onkeypress=\"return tanpa_kutip(event);\" disabled></td>
	 <td>".$_SESSION['lang']['jumlah']."</td><td>:</td><td colspan=4><input type=text id=jlhlama class=myinputtextnumber size=7 maxength=25 onkeypress=\"return tanpa_kutip(event);\" disabled>
	 <input type=text id=satuan size=6 disabled class=myinputtext>
	 </td>
	 </tr>
	 </table>
	 </fieldset>
	 <fieldset><legend>".$_SESSION['lang']['jumlahkembali']."</legend>
	 <table border=0><tr><td width=110px>".$_SESSION['lang']['jumlahkembali']."</td><td>:</td><td><input type=text id=jlhretur disabled value=0 class=myinputtextnumber size=7 maxlength=6 onkeypress=\"return tanpa_kutip(event);\">
	 <input type=hidden id=hargasatuan value='0'>
	 <input type=hidden id=kodept value=''>
	 <input type=hidden id=untukunit value=''>
	 <input type=hidden id=untukpt value=''>
	 ".$_SESSION['lang']['keterangan']."</td><td>:</td><td>
	 <input type=text id=keterangan class=myinputtext onkeypress=\"return tanpa_kutip(event);\" size=40 maxlength=80>
	 <button id=savebutton class=mybutton onclick=simpanRetur() disabled>".$_SESSION['lang']['save']."</button>
	 <button id=savebutton class=mybutton onclick=window.location.reload()>".$_SESSION['lang']['cancel']."</button>
	 </td></tr></table></fieldset>
	 <!-- Umar -->
	 <fieldset>
	 	<legend>".$_SESSION['lang']['file']."</legend>
	 	<button class=mybutton onclick=showupload(event)>Upload Files</button>
	 </fieldset>
	 <!-- End Umar -->
	 ";
//==================masukkan variable periode gudang
//$sess=$_SESSION['gudang'];
foreach($_SESSION['gudang'] as $key=>$val)
{
 //  echo	$sess[$key]['start'];

	$frm[0].="<input type=hidden id='".$key."_start' value='".$_SESSION['gudang'][$key]['start']."'>
	     <input type=hidden id='".$key."_end' value='".$_SESSION['gudang'][$key]['end']."'>
		";
}	 
$frm[0].="</fieldset>
	 ";
	 
$frm[1].="<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  <fieldset style=float:left>
	  ".$_SESSION['lang']['cari_transaksi']."
	  <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=9>
	  <button class=mybutton onclick=cariBapb()>".$_SESSION['lang']['find']."</button>
	  </fieldset><br><br><br>
	  <table class=sortable cellspacing=1 border=0 width=100%>
      <thead>
	  <tr class=rowheader>
	  <td align=center>No.</td>
	  <td align=center>".$_SESSION['lang']['sloc']."</td>
	  <td align=center>".$_SESSION['lang']['tipe']."</td>
	  <td align=center>".$_SESSION['lang']['momordok']."</td>
	  <td align=center>".$_SESSION['lang']['tanggal']."</td>
	  <td align=center>".$_SESSION['lang']['pt']."</td>
	  <td align=center>".$_SESSION['lang']['nopo']."</td>	
	  <td align=center>".$_SESSION['lang']['dari']."</td> 
	  <td align=center>".$_SESSION['lang']['dbuat_oleh']."</td>
	  <td align=center>".$_SESSION['lang']['posted']."</td>
	  <td align=center>Action</td>
	  </tr>
	  </head>
	   <tbody id=containerlist>
	   </tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>	 
	 ";	 
//========================
$hfrm[0]=$_SESSION['lang']['retur'];
$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,150,'100%');
//===============================================	 
}
else
{
	echo " Error: Transaction Period missing";
}
CLOSE_BOX();
close_body();
?>