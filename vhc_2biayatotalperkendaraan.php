<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/vhc_2biayatotalperkendaraan.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('vhc_2biayatotalperkendaraan').'</span><br>');




$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optKodeorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optKodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optKodeorg.="</optgroup>";
	}
}
echo"<fieldset style=float:left>
     <legend>".$_SESSION['lang']['form']."</legend>
	 <table>
	 <tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select class=select2 id=unit style='width:200px;'>".$optKodeorg."</select></td>
		
	 </tr>
	 <tr>
		
		<td>".$_SESSION['lang']['tgldari']." </td>
		<td>:</td>
		<td><input type=\"text\" class=\"myinputtext\" id=\"tglAwal\" name=\"tglAwal\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\" readonly=readonly  maxlength=\"10\" style=\"width:195px;\" /></td>
    </tr>
	 <tr>    
		<td> ".$_SESSION['lang']['tglsmp']." </td>
		<td>:</td>
		<td><input type=\"text\" class=\"myinputtext\" id=\"tglAkhir\" name=\"tglAkhir\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\" readonly=readonly maxlength=\"10\" style=\"width:195px;\" /></td>
	</tr>
	 <tr>	
		<td></td>
		<td></td>
		<td><button class=mybutton onclick=getBiayaTotalPerKendaraan()>".$_SESSION['lang']['preview']."</button>
		<button class=mybutton onclick=getBiayaTotalPerKendaraanexcel()>".$_SESSION['lang']['excel']."</button></td>
	</tr>
	</table>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
//	 <img onclick=hutangSupplierKePDF(event,'log_laporanhutangsupplier_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>

echo"
	 <div class='table-scroll'>
       <table class=sortable cellpadding=5 cellspacing=1 border=0>
	     <thead>
		    <tr>
			  <th align=center>No.</th>
			  <th align=center style='width:50px;'>".$_SESSION['lang']['jenisvch']."</th>
			  <th align=center>".$_SESSION['lang']['kodevhc']."</th>
			  <th align=center>".$_SESSION['lang']['nopol']."</th>
			  <th align=center>".$_SESSION['lang']['detail']."</th>
			  <th align=center style='width:50px;'>".$_SESSION['lang']['tahunperolehan']."</th>   
			  <th align=center style='width:100px;'>".$_SESSION['lang']['jumlah']."</th>
			  <th align=center style='width:100px;'>".$_SESSION['lang']['jmljamkerja']."</th>  
			  <th align=center style='width:100px;'>Price / Unit</th>    
			  <th align=center>".$_SESSION['lang']['alokasirp']."</th>
			  <th align=center>".$_SESSION['lang']['blmAlokasi']."<br>(Rp)</th>
			</tr>  
		 </thead>
		 <tbody id=container>
		 </tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>
     </div>";
CLOSE_BOX();
close_body();
  //<td align=center>".$_SESSION['lang']['periode']."</td>
?>