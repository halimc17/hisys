<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<!-- <script language=javascript1.2 src='js/log_postingGudang.js?v=1.2'></script> -->
<script language=javascript1.2 src="js/log_postingGudang.js?v=<?php echo time(); ?>" /></script>
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
OPEN_BOX('','<span class=judul>'.getMenu('log_postingGudang').'</span><br>');

$frm[0]='';
$frm[1]='';
echo "<fieldset><legend>";
echo" <b>".$_SESSION['lang']['periode'].":<span id=displayperiod>".tanggalnormal($_SESSION['org']['period']['start'])." - ".tanggalnormal($_SESSION['org']['period']['end'])."</pre></b>";
echo"</legend>";
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' and left(induk,4) in (select kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING') order by namaorganisasi";
	}elseif($_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' and left(induk,4) in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KANWIL' and kodeorganisasi in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')) order by namaorganisasi";
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

  // if(($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') and substr($_SESSION['empl']['subbagian'],-2)!='PK'){
  	// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%'
       // and left(induk,4)='".$_SESSION['empl']['lokasitugas']."'
       // order by namaorganisasi";// and kodeorganisasi not in ('SENE10', 'SKNE10', 'SOGE30') order by namaorganisasi";
   // /*$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANG'
       // and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')
       // order by namaorganisasi";// and kodeorganisasi not in ('SENE10', 'SKNE10', 'SOGE30') order by namaorganisasi";*/
// }else{
	// $gudangdivisi='';
	// $gudangxxx=makeOption($dbname,'kebun_5gudangtransaksi','afdeling,kodegudang',"status='1'");
	// if($_SESSION['empl']['subbagian']!='' and $gudangxxx[$_SESSION['empl']['subbagian']]!=''){
		// $gudangdivisi=" and kodeorganisasi ='".$gudangxxx[$_SESSION['empl']['subbagian']]."'";
	// }
   // $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (left(induk,4)='".$_SESSION['empl']['lokasitugas']."' 
       // or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') ".$gudangdivisi." and tipe like 'GUDANG%' order by kodeorganisasi";
    
// }
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);  
$optsloc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
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

echo"<fieldset style=float:left;>
     <legend>
	 ".$_SESSION['lang']['daftargudang']."
     </legend>
	  ".$_SESSION['lang']['pilihgudang'].": <select class=select2 id=sloc>".$optsloc."</select>
	   <button onclick=setSloc('simpan') class=mybutton id=btnsloc>".$_SESSION['lang']['save']."</button>
	   <button onclick=setSloc('ganti') class=mybutton>".$_SESSION['lang']['cancel']."</button>
 	 </fieldset><div style=clear:both></div>";
	 
$frm[0].="<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  <fieldset style=float:left;>
	  ".$_SESSION['lang']['notransaksi']."
	  <input type=text id=txtunpost size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=12>
	  <button class=mybutton onclick=cariUnconfirmed(0)>".$_SESSION['lang']['find']."</button>
	  </fieldset><div style=clear:both></div>
	  <table class=sortable cellspacing=1 border=0 cellpadding=5>
      <thead>
	  <tr class=rowheader>
	  <th align=center>No.</th>
	  <th align=center>".$_SESSION['lang']['sloc']."</th>
	  <th align=center>".$_SESSION['lang']['tipe']."</th>
	  <th align=center>".$_SESSION['lang']['momordok']."</th>
	  <th align=center>".$_SESSION['lang']['tanggal']."</th>
	  <th align=center>".$_SESSION['lang']['pt']."</th>
	  <th align=center>".$_SESSION['lang']['nopo']."</th>	
	  <th align=center>".$_SESSION['lang']['supplier']."</th> 
	  <th align=center>".$_SESSION['lang']['asaltujuan']."</th>
	  <th align=center>".$_SESSION['lang']['noreferensi']."</th>			  
	  <th align=center>".$_SESSION['lang']['dbuat_oleh']."</th>
	  <th align=center>".$_SESSION['lang']['action']."</th>
	  </tr>
	  </thead>
	   <tbody id=unconfirmaedlist>
	   </tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>	 
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
$frm[1].="<fieldset>
	   <legend>".$_SESSION['lang']['list']."</legend>
	  <fieldset style=float:left;>".$_SESSION['lang']['notransaksi']."
	  <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=12>
	  <button class=mybutton onclick=cariDokumen(0)>".$_SESSION['lang']['find']."</button>
	  </fieldset><div style=clear:both></div><br>
	  <table class=sortable cellspacing=1 border=0 cellpadding=5>
      <thead>
	  <tr class=rowheader>
	  <th align=center>No.</th>
	  <th align=center>".$_SESSION['lang']['sloc']."</th>
	  <th align=center>".$_SESSION['lang']['tipe']."</th>
	  <th align=center>".$_SESSION['lang']['momordok']."</th>
	  <th align=center>".$_SESSION['lang']['tanggal']."</th>
	  <th align=center>".$_SESSION['lang']['pt']."</th>
	  <th align=center>".$_SESSION['lang']['nopo']."</th>	
	  <th align=center>".$_SESSION['lang']['supplier']."</th> 
	  <th align=center>".$_SESSION['lang']['asaltujuan']."</th>
	  <th align=center>".$_SESSION['lang']['noreferensi']."</th>		  
	  <th align=center>".$_SESSION['lang']['dbuat_oleh']."</th>
	  <th align=center>".$_SESSION['lang']['posted']."</th>
	  <th align=center>".$_SESSION['lang']['action']."</th>
	  </tr>
	  </thead>
	   <tbody id=containerlist>
	   </tbody>
	   <tfoot>
	   </tfoot>
	   </table>
	 </fieldset>	 
	 ";	 
//========================
$hfrm[0]=$_SESSION['lang']['belumposting'];
$hfrm[1]=$_SESSION['lang']['daftartransaksi'];
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