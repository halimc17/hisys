<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<script type="text/javascript" src="js/vhc_daftaroperator.js" /></script>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
</script>
<div id="action_list">
<?php

$optOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$sOrg="select distinct kodetraksi from ".$dbname.".vhc_5master order by kodetraksi asc";
$qOrg=fetchData($sOrg);
$optPt="";
foreach($qOrg as $brsOrg)
{
    $optPt.="<option value=".$brsOrg['kodetraksi'].">".$optOrg[$brsOrg['kodetraksi']]."</option>";
}

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


$optJns="<option value=>".$_SESSION['lang']['all']."</option>";
$sJvhc="select distinct jenisvhc,namajenisvhc from ".$dbname.".vhc_5jenisvhc order by namajenisvhc asc";
$qJvhc=$owlPDO->query($sJvhc) or die(print " Gagal: ".PDOException::getMessage());
$qJvhc->setFetchMode(PDO::FETCH_ASSOC);
while($rJvhc=$qJvhc->fetch())
{
    $optJns.="<option value=".$rJvhc['jenisvhc'].">".$rJvhc['namajenisvhc']."</option>";
}

$optper="<option value=''>".$_SESSION['lang']['all']."</option>";
$sTgl="select distinct substr(tanggal,1,7) as periode from ".$dbname.".vhc_penggantianht order by tanggal desc";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while($rTgl=$qTgl->fetch())
{
   $optper.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('vhc_daftaroperator').'</span><br>');

echo"<fieldset style=float:left><legend>".$_SESSION['lang']['form']."</legend>
	<table>
     <tr valign=moiddle>
		<td>".$_SESSION['lang']['unit']."</td><td>:</td>
		<td><select class='select2' id=company_id name=company_id style=width:200px>".$optKodeorg."</select></td>
	</tr>	
	<tr>	
		<td>".$_SESSION['lang']['jenisvch']."</td><td>:</td>
		<td><select class='select2' id=jnsVhc name=jnsVhc  style=width:200px>".$optJns."</select></td>
	</tr>	
<tr>		
		<td></td>
		<td></td>
		<td>
			<button class=mybutton onclick=cari()>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=dataKeExcel(event,'vhc_slave_daftaroperator.php')>".$_SESSION['lang']['excel']."</button>
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
<div id="contain" class='table-scroll' style='height:420px;overflow:auto;' ></div>
<?php
CLOSE_BOX();
?>
<?php
echo close_body();
?>