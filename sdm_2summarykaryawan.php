<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
// OPEN_BOX();
?>

<?php 

$arr0="##tanggal"; 
?>
<script language=javascript src='js/sdm_2summarykaryawan.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script>


</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php

$title[0]=$_SESSION['lang']['summary']." ".$_SESSION['lang']['karyawan'];
$title[1]=$_SESSION['lang']['summary']." ".$_SESSION['lang']['karyawan']." ".$_SESSION['lang']['dari']." ".$_SESSION['lang']['gaji'];

//=================ambil PT;  
$optpt=$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$whrpt= getOrgDetail(4);
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' and kodeorganisasi in (".$whrpt.") order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
$optPil="<option value='default'>Default</option>";
$optPil.="<option value='bulanan'>Bulanan</option>";
$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";

$frm[0]="";
$frm[0].="<fieldset style=\"float: left;\">
<legend><b>".$title[0]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
    <td>
		<label>".$_SESSION['lang']['pt']."</label>
	</td>
    <td>:</td>
	<td>
		<select id=pt style='width:170px;' onchange=getunit()>".$optpt."</select>
	</td>
</tr>
<tr>
    <td>
		<label>".$_SESSION['lang']['unit']."</label>
	</td>
    <td>:</td>
	<td>
		<select id=unit style='width:170px;' onchange=getperiode()>".$optPeriode."</select>
	</td>
</tr>
<tr hidden>
    <td>
		<label>".$_SESSION['lang']['tipekaryawan']."</label>
	</td>
    <td>:</td>
	<td>
		<table width='80%'>
			<tr>
				<td>";
					$str = "select id,tipe from " . $dbname . ".sdm_5tipekaryawan";
					$qry = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$qry->setFetchMode(PDO::FETCH_ASSOC);
					while ($res = $qry->fetch()) {
						$frm[0].="<li style='float:left;width:250px;list-style-type:none'>
							<input type='checkbox' id='chktipe' name='chktipe[]' value='".$res['id']."' />".$res['tipe']."</li>";
					}
		$frm[0].="</td></tr></table>
	</td>
</tr>
<tr hidden>
    <td>
		<label>".$_SESSION['lang']['tanggal']."</label>
	</td>
    <td>:</td>
	<td>
		<input id=\"tanggal\" name=\"tanggal\" class=\"myinputtext\" onkeypress=\"return tanpa_kutip(event)\"  style=\"width:100px\" readonly=\"readonly\" onmousemove=\"setCalendar(this.id)\" type=\"text\" value='".date('d-m-Y')."'>
	</td>
</tr>
<tr>
    <td>
		<label>".$_SESSION['lang']['periode']."</label>
	</td>
    <td>:</td>
	<td>
		<select id=periode style='width:170px;'>".$optPeriode."</select>
	</td>
</tr>
<tr hidden>
    <td>
		<label>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['laporan']."</label>
	</td>
    <td>:</td>
	<td>
		<select id=pilrep style='width:170px;'>".$optPil."</select>
	</td>
</tr>
<tr>
    <td colspan=2></td>
	<td>
        <button onclick=\"getlevel0('preview')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"getlevel0('excel')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
        
    </td>    
</tr>    
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id=showHeader>
<div id='printContainer0' style='min-height:65vh;' class='table-scroll'>
</div>
<div id='printContainer1' style='min-height:auto;' class='table-scroll'>
</div>
</div>

</fieldset>";
/*<button onclick=\"zExcel(event,'sdm_slave_2summarykaryawan.php','".$arr0."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>*/
$optPt="";
$optPt="<option value=''>".$_SESSION['lang']['all']."</option>";
$spt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$qpt=$owlPDO->query($spt) or die(print " Gagal: ".PDOException::getMessage());
$qpt->setFetchMode(PDO::FETCH_ASSOC);
while($rpt=  $qpt->fetch()){
    $optPt.="<option value='".$rpt['kodeorganisasi']."'>".$rpt['namaorganisasi']."</option>";
}
$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
$sdr="select distinct periodegaji as periode from ".$dbname.".sdm_gaji order by periodegaji desc";
$qdr=$owlPDO->query($sdr) or die(print " Gagal: ".PDOException::getMessage());
$qdr->setFetchMode(PDO::FETCH_ASSOC);
$optPrdDr="";
while($rdr=  $qdr->fetch()){
    $optPrdDr.="<option value='".$rdr['periode']."'>".$rdr['periode']."</option>";
}
 $arr="##prdIdDr2";
 OPEN_BOX('','<span class=judul>'.getMenu('sdm_2summarykaryawan').'</span>');
//========================
$hfrm[0]=$title[0];
//$hfrm[1]=$title[1];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,'100%');
//===============================================


CLOSE_BOX();
echo close_body();
?>