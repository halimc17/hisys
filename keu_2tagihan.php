<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');

?>
<?php
$arrExcep="'upd','pjd','p22','p21','p23'";

$optNamaOrganisasi=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optnmjenis=makeOption($dbname, 'keu_5jenistagihan', 'kode,namajenis',"kode not in (".$arrExcep.")");	
$optPeriode='';
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$sPeriode="select distinct substring(tanggal,1,7) as periode from ".$dbname.".keu_tagihanht order by substring(tanggal,1,7) desc";
$res=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$res->fetch()){
    if(substr($rPeriode['periode'],5,2)=='12'){
//        $optPeriode.="<option value=".substr($rPeriode['periode'],0,4).">".substr($rPeriode['periode'],0,4)."</option>";
        $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
    }
    else{
        $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
    }
}

$optOrg="<select class=select2 id=kdOrg name=kdOrg style=\"width:200px;\" ><option value=''>".$_SESSION['lang']['all']."</option>";
$optNik="<select class=select2 id=updateby name=updateby style=\"width:200px;\" ><option value=''>".$_SESSION['lang']['all']."</option>";
$optSupplier="<select class=select2 id=kodesupplier name=kodesupplier style=\"width:200px;\" ><option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select distinct kodeorg from ".$dbname.".keu_tagihanht order by kodeorg asc ";	
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$res->fetch()){
 	$optOrg.="<option value=".$rOrg['kodeorg'].">".$optNamaOrganisasi[$rOrg['kodeorg']]."</option>";
}
$optOrg.="</select>";
$sOrg="select distinct updateby from ".$dbname.".keu_tagihanht order by kodeorg asc ";  
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$res->fetch()){
    $whrby="karyawanid='".$rOrg['updateby']."'";
    $optNm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrby);
    $optNik.="<option value=".$rOrg['updateby'].">".@$optNm[$rOrg['updateby']]."</option>";
}
$optNik.="</select>";
$sOrg="select distinct kodesupplier from ".$dbname.".keu_tagihanht order by kodeorg asc ";  
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$res->fetch()){
    $whrby="supplierid='".$rOrg['kodesupplier']."'";
    $optSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrby);
    $optSupplier.="<option value=".$rOrg['kodesupplier'].">".@$optSup[$rOrg['kodesupplier']]."</option>";
}
$optSupplier.="</select>";

//$arr="##kdOrg##tgl1##tgl2##statTagihan";
$arr="##kdOrg##updateby##statTagihan##periode2##periode##noinv##noinvsupp##nopodt##jenis##kodesupplier";

$arrOpt=array("0"=>"Belum Posting","1"=>"Sudah Posting","2"=>"Sudah Terbayar","3"=>"Outstanding");
$optStatus="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrOpt as $listBrs =>$dtStat)
{
    $optStatus.="<option value='".$listBrs."'>".$dtStat."</option>";
}


$optjenis="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct(tipeinvoice) as tipeinvoice  from ".$dbname.".keu_tagihanht where tipeinvoice!=''";
// echo $str;	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$nmjenis=$optnmjenis[$bar['tipeinvoice']];
	if($nmjenis==''){
		$nmjenis=$bar['tipeinvoice'];
	}
 	$optjenis.="<option value='".$bar['tipeinvoice']."'>".$nmjenis."</option>";
}


?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/keu_tagihanv2.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/keu_2tagihan.js?v=<?php echo time(); ?>'></script>


<script language=javascript>
notifpopilih="<?php echo $_SESSION['lang']['notifpopilih']; ?>";
notiftagihtanggal="<?php echo $_SESSION['lang']['notiftagihtanggal']; ?>";
notifpostingpenagihan="<?php echo $_SESSION['lang']['notifpostingpenagihan']; ?>";
</script>
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('keu_2tagihan').'</span>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >


<?php
$arr="##kdOrg##updateby##statTagihan##periode2##periode##noinv##noinvsupp##nopodt##jenis##kodesupplier";
echo"
<tr>
	<td><label>".$_SESSION['lang']['pt']."</label></td><td>:</td><td>".$optOrg."</td>
	<td><label>".$_SESSION['lang']['periode']."</label></td><td>:</td><td><select class='select2' id='periode' style=width:85px>".$optPeriode."</select> ".$_SESSION['lang']['sd']." <select class='select2' id='periode2' style=width:86px>".$optPeriode."</select></td>
	<td><label>".$_SESSION['lang']['noinvoice']."</label></td><td>:</td><td><input type=text id=noinv class=myinputtext onkeypress='return tanpa_kutip(event)' style='width:200px' placeholder='No. invoice boleh kosong' /></td>
</tr>
<tr>
	<td><label>".$_SESSION['lang']['namakaryawan']."</label></td><td>:</td><td>".$optNik."</td>
	<td>".$_SESSION['lang']['jenis']."</td>
	<td>:</td>
	<td><select class=select2 style=width:200px  id='jenis' name='jenis'>".$optjenis."</select></td>
	<td><label>".$_SESSION['lang']['noinvoicesupplier']."</label></td><td>:</td><td><input type=text id=noinvsupp class=myinputtext onkeypress='return tanpa_kutip(event)' style='width:200px' placeholder='No. invoice supplier boleh kosong'  /></td>
</tr>
<tr>
	<td><label>".$_SESSION['lang']['namasupplier']."</label></td><td>:</td><td>".$optSupplier."</td>
	<td><label>".$_SESSION['lang']['status']."</label></td><td>:</td><td><select class=select2 id='statTagihan' name='statTagihan' style='width:200px'>".$optStatus."</select></td>
	<td><label>".$_SESSION['lang']['nopo']."</label></td><td>:</td><td><input type=text id=nopodt class=myinputtext onkeypress='return tanpa_kutip(event)' style='width:200px' placeholder='No. PO boleh kosong'  /></td>
</tr>
<tr>
        <td colspan=2></td>
        <td colspan=4>
			<button class=mybutton onclick=previewlaporan('".$arr."','html')>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=previewlaporan('".$arr."','excel')>".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=previewlaporan('pdf') hidden>".$_SESSION['lang']['pdf']."</button>
			<button class=mybutton onclick=cancellaporan()>".$_SESSION['lang']['cancel']."</button>
		</td>
      <tr>


  </table>
   </fieldset>";
CLOSE_BOX();
OPEN_BOX();

echo"<div class='table-scroll' style='width:100%;height:380px;' id=container></div>";
CLOSE_BOX();
close_body();
?>