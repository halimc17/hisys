<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script type="text/javascript" src="js/vhc_2breakdownunit.js?v=<?php echo time(); ?>" /></script>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
</script>
<div id="action_list">
<?php

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

$sOrg="select distinct substr(alokasibiaya,1,4) as lokasi from ".$dbname.".vhc_rundt 
    where alokasibiaya not like 'AK-%' order by substr(alokasibiaya,1,4) asc";
$qOrg=fetchData($sOrg);
$optLokasi="";
foreach($qOrg as $brsOrg){
    if(trim($brsOrg['lokasi'])!='')$optLokasi.="<option value=".$brsOrg['lokasi'].">".$optOrg[$brsOrg['lokasi']]."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('vhc_2breakdownunit').'</span><br>');

echo"<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>
	<table>
     <tr valign=moiddle>
		<td>".$_SESSION['lang']['unit']."</td><td>:</td>
		<td><select class='select2' id=company_id name=company_id onChange=get_jnsVhc() style=width:200px>".$optKodeorg."</select></td>
	</tr>	
	<tr>	
		<td>".$_SESSION['lang']['jenisvch']."</td><td>:</td>
		<td><select class='select2' id=jnsVhc name=jnsVhc onchange=\"getKdVhc()\" style=width:200px><option  value=''>".$_SESSION['lang']['all']."</option></select></td>
	</tr>	
	<tr>		
		<td>".$_SESSION['lang']['kodevhc']."</td><td>:</td>
		<td><select class='select2' id=kdVhc name=kdVhc style=width:200px><option  value=''>".$_SESSION['lang']['all']."</option></select></td>
	</tr>	
	<tr>		
		<td>".$_SESSION['lang']['tgldari']."</td><td>:</td>
		<td><input type=\"text\" class=\"myinputtext\" id=\"tglAwal\" name=\"tglAwal\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:195px;\" readonly/></td>
	</tr>	
	<tr>		
		<td>".$_SESSION['lang']['tglsmp']."</td><td>:</td>
		<td><input type=\"text\" class=\"myinputtext\" id=\"tglAkhir\" name=\"tglAkhir\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:195px;\" readonly/></td>
	</tr>	
	<tr>		
		<td></td>
		<td></td>
		<td>
			<button class=mybutton onclick=save_pil()>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=dataKeExcel(event,'vhc_slave_2breakdownunit.php')>".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=ganti_pil()>".$_SESSION['lang']['cancel']."</button>
		</td>
     </tr>
         </table>"; 
echo"</fieldset>"; 
?>
</div>
<?php 
CLOSE_BOX();
OPEN_BOX();

?>
<div id="cari_barang" name="cari_barang"></div>
<div id="hasil_cari" name="hasil_cari"></div>
<div id="contain" class='table-scroll' style='height:420px;overflow:auto;' ></div>
<?php
CLOSE_BOX();
?>
<?php
echo close_body();
?>