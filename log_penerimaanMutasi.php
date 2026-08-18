<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/log_penerimaanMutasi.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?
include('master_mainMenu.php');



if(isTransactionPeriod())//check if transaction period is normal
{
OPEN_BOX('','<span class=judul>'.getMenu('log_penerimaanMutasi').'</span><br>');

$frm[0]='';
$frm[1]='';
$frm[2]='';
echo "<fieldset><legend>";

echo" <b>".$_SESSION['lang']['periode'].": <span id=displayperiod>".tanggalnormal($_SESSION['org']['period']['start'])." - ".tanggalnormal($_SESSION['org']['period']['end'])."</span></b>";
echo"</legend>";
#kodeorganisasi untuk klinik harus berakhiran PK
if($_SESSION['empl']['tipelokasitugas']=='KANWIL' and substr($_SESSION['empl']['subbagian'],-2)!='PK'){
   $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%'
       and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
       order by kodeorganisasi";// and kodeorganisasi not in ('SENE10', 'SKNE10', 'SOGE30') order by namaorganisasi";
}else{
	$gudangdivisi='';
	$gudangxxx=makeOption($dbname,'kebun_5gudangtransaksi','afdeling,kodegudang',"status='1'");
	if($_SESSION['empl']['subbagian']!='' and $gudangxxx[$_SESSION['empl']['subbagian']]!=''){
		$gudangdivisi=" and kodeorganisasi ='".$gudangxxx[$_SESSION['empl']['subbagian']]."'";
	}

	$unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
	$gudang_detailAkses=" and induk IN (".$unitDetailAkses.") ";

	if(count($unitDetailAkses) > 0){
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$gudang_detailAkses." and tipe like 'GUDANG%' order by kodeorganisasi";
	}else{
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (left(induk,4)='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') ".$gudangdivisi." and tipe like 'GUDANG%' order by kodeorganisasi";
	}

    
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optsloc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
	// $optsloc.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	$d=substr($bar->kodeorganisasi,0,4);
	if($d!=$n){	
		$optsloc.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optsloc.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	$n=$d;

	if($d!=$n){		
		$optsloc.="</optgroup>";
	}
}

echo"<fieldset style=float:left>
     <legend>
	 ".$_SESSION['lang']['daftargudang']."
     </legend>
	  ".$_SESSION['lang']['pilihgudang']." : <select class=select2 id=sloc>".$optsloc."</select>
	   <button onclick=setSloc('simpan') class=mybutton id=btnsloc>".$_SESSION['lang']['save']."</button>
	   <button onclick=setSloc('ganti') class=mybutton>".$_SESSION['lang']['cancel']."</button>
	  
	 </fieldset>";
	 
echo"<fieldset style=float:left>
     <legend>
	 ".$_SESSION['lang']['info']."
     </legend>
	  Jika setelah simpan gudang <b>No.Dokumen</b> tidak muncul, coba cek periode akuntansi gudangnya
	 </fieldset><div style=clear:both></div>";	 



//===================================
$frm[1].="<fieldset><legend>".$_SESSION['lang']['header']."</legend>";

$frm[1].="<table cellspacing=1 border=0>
     <tr>
		<td>".$_SESSION['lang']['momordok']." : </td>
		<td><input type=text id=nodok size=25 disabled class=myinputtext></td>	 
	    <td>".$_SESSION['lang']['tanggal']." : </td><td>
		     <input type=text class=myinputtext id=tanggal size=10 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" value='' readonly>
	    </td>
	 </tr>

	 </table>
    </fieldset>
    <fieldset>
	   <legend>".$_SESSION['lang']['detail']."</legend>
	   <div id=containerReceipt>

	   </div>
	 </fieldset>	 	 
	 ";
//==================masukkan variable periode gudang
//$sess=$_SESSION['gudang'];
foreach($_SESSION['gudang'] as $key=>$val)
{
 //  echo	$sess[$key]['start'];

	$frm[1].="<input type=hidden id='".$key."_start' value='".$_SESSION['gudang'][$key]['start']."'>
	     <input type=hidden id='".$key."_end' value='".$_SESSION['gudang'][$key]['end']."'>
		";
}	 
$frm[0].="<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  <fieldset style=float:left>
	  ".$_SESSION['lang']['cari_transaksi']." : 
	  <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25>
	  <button class=mybutton onclick=cariBast()>".$_SESSION['lang']['find']."</button>
	  </fieldset><div style=clear:both></div><br>
	  <table class=sortable cellpadding=5 cellspacing=1 border=0 >
      <thead>
	  <tr class=rowheader>
	  <th align=center>No.</th>
	  <th align=center>".$_SESSION['lang']['sumber']."</th>
	  <th align=center>".$_SESSION['lang']['tipe']."</th>
	  <th align=center>".$_SESSION['lang']['suratjalan']."</th>
	  <th align=center>".$_SESSION['lang']['momordok']."</th>
	  <th align=center>".$_SESSION['lang']['tanggal']."</th>
	  <th align=center>".$_SESSION['lang']['pemilik']."</th>
	  <th align=center>".$_SESSION['lang']['tujuan']."</th>	  	 
	  <th align=center>".$_SESSION['lang']['dbuat_oleh']."</th>
	  <th align=center>".$_SESSION['lang']['rilis']."</th>
	  <th align=center>".$_SESSION['lang']['status']."</th>
	  <th align=center colspan=2>Action</th>
	  </tr>
	  </head>
	   <tbody id=containerlist>
	   </tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>	 
	 ";	 
$frm[2].="<fieldset>
	   <legend>".$_SESSION['lang']['sudahditerima']."</legend>
	  <fieldset style=float:left>
	  ".$_SESSION['lang']['notransaksi']."
	  <input type=text id=txtrece size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25>
	  <button class=mybutton onclick=cariBapbReceived(0)>".$_SESSION['lang']['find']."</button>
	  </fieldset><div style=clear:both></div><br>
	  <table class=sortable cellspacing=1 cellpadding=5 border=0 >
      <thead>
	  <tr class=rowheader>
	  <th align=center>No.</th>
	  <th align=center>".$_SESSION['lang']['sloc']."</th>
	  <th align=center>".$_SESSION['lang']['tipe']."</th>
	  <th align=center>".$_SESSION['lang']['suratjalan']."</th>
	  <th align=center>".$_SESSION['lang']['momordok']."</th>
	  <th align=center>".$_SESSION['lang']['tanggal']."</th>
	  <th align=center>".$_SESSION['lang']['pt']."</th>
	  <th align=center>".$_SESSION['lang']['sumber']."</th>	
	  <th align=center>".$_SESSION['lang']['noreferensi']."</th> 
	  <th align=center>".$_SESSION['lang']['dbuat_oleh']."</th>
	  <th align=center>".$_SESSION['lang']['posted']."</th>
	  <th align=center colspan=2>Action</th>
	  </tr>
	  </head>
	   <tbody id=containerlistreceived>
	   </tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>	 
	 ";	 	 
//========================
$hfrm[1]=$_SESSION['lang']['terimamutasi'];
$hfrm[2]=$_SESSION['lang']['sudahditerima'];
$hfrm[0]=$_SESSION['lang']['barangdatang'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,'100%');
//===============================================	 
}
else
{
	echo " Error: Transaction Period missing";
}
CLOSE_BOX();
close_body();
?>