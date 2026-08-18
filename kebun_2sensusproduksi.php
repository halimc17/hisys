<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');


$sOrg="select distinct substr(kodeorg,1,4) as kodeorg from ".$dbname.".kebun_rencanapanen_vw order by kodeorg asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rOrg=$qOrg->fetch()){
	//$optOrg.="<option value=".$rOrg['kodeorg'].">".$rOrg['kodeorg']."</option>";
}

$sPeriode="select distinct substr(tanggal,1,4) as periode from ".$dbname.".kebun_rencanapanen_vw order by tanggal asc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_OBJ);
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rPeriode=$qPeriode->fetch()){
	$optPeriode.="<option value='".$rPeriode->periode."'>".$rPeriode->periode."</option>";       
}
$arr="##kodeorg##periode##pt##divisi";

?>
<script language=javascript src='js/zMaster.js'></script> 
<script language=javascript src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script languange=javascript1.2 src='js/formReport.js'></script>
<script languange=javascript1.2 src='js/zGrid.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<div>
<?
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2sensusproduksi').'</span><br>');

$optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrpt = getOrgDetail(3);
foreach ($arrpt as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optpt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optpt.="<option value='".$key."'>".$key." - ".$val."</option>";			
	$n=$d;
	if($d!=$n){
		$optpt.="</optgroup>";
	}
}

// $str="select * from ".$dbname.".organisasi where tipe='PT' and kodesejarah=''";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$s="";
// 	if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
// 		$s="selected";
// 	}
//     $optpt.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

// $str="select * from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['kodeorganisasi']."' and kodeorganisasi in (select substr(kodeorg,1,4) from ".$dbname.".kebun_rencanapanen_vw)";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$s="";
// 	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
// 		$s="selected";
// 	}
//     $optOrg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrunit = getOrgDetail(1);
foreach ($arrunit as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optOrg.="<option value='".$key."'>".$key." - ".$val."</option>";			
	$n=$d;
	if($d!=$n){
		$optOrg.="</optgroup>";
	}
}

$optdivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."' and kodeorganisasi in (select substr(kodeorg,1,6) from ".$dbname.".kebun_rencanapanen_vw)";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$s="";
	if($_SESSION['empl']['subbagian']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optdivisi.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}
?>
<?php 

echo"<fieldset style=float:left;>
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0 >


 <tr>
	<td>" . $_SESSION['lang']['pt'] . "</td>
	<td>:</td>
	<td><select id=pt onchange=getUnitThnTnm(this,'kodeorg,tt','divisi','".$_SESSION['lang']['all']."')  style=\"width:164px;\">" .$optpt . "</select></td>
</tr>
<tr>
	<td>" . $_SESSION['lang']['unit'] . "</td>
	<td>:</td>
	<td><select onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') id=kodeorg style=\"width:164px;\">" . $optOrg . "</select></td>
</tr>
<tr>
	<td>" . $_SESSION['lang']['divisi'] . "</td>
	<td>:</td>
	<td><select id=divisi style=\"width:164px;\">" . $optdivisi . "</select></td>
</tr>


<tr><td><label>".$_SESSION['lang']['tahun']."</label></td><td>:</td><td><select id=periode name=periode style=width:164px>".$optPeriode."</select></td></tr>

<tr><td colspan=2><td colspan=2>";
?>
    <?php 
    echo "<button onclick=\"zPreview('kebun_slave_2sensusproduksi','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>
          <button onclick=\"zExcel(event,'kebun_slave_2sensusproduksi.php','".$arr."','printContainer')\" class=\"mybutton\" name=\"excel\" id=\"excel\">".$_SESSION['lang']['excel']."</button>    
          "; 
    ?>
    </td>
</tr>
</table>
</fieldset>
<?php
CLOSE_BOX();
OPEN_BOX('','');

echo"<div id='printContainer' class='table-scroll'></div> "; 
CLOSE_BOX();
close_body();

?>