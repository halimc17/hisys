<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zConfig.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/setup_kud.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php

# Lokasi Tugas
if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
  $tmpOpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN'");
} elseif($_SESSION['empl']['tipelokasitugas']=='KEBUN') {
  $tmpOpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
    "kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
} else {
  $tmpOpt = getOrgBelow($dbname,$_SESSION['empl']['lokasitugas'],false,'kebunonly');
}

# Kebun Berdasarkan Lokasi Tugas
$sKebun = array(''=>'');
foreach($tmpOpt as $key=>$row) {
  $sKebun[$key] = $row;
}

# Form Cari Data Kebun
$searchEls = $_SESSION['lang']['kebun']." ";
$searchEls .= makeElement('sKebun','select','',
  array('onchange'=>"getAfdeling(this,'sAfdeling')",'style'=>'width:150px'),$sKebun)." ";
$searchEls .= $_SESSION['lang']['afdeling']." ";
$searchEls .= makeElement('sAfdeling','select','',array('style'=>'width:150px'),array())." ";
$searchEls .= makeElement('searchIt','button',$_SESSION['lang']['find'],array('onclick'=>'showData()'))." ";


OPEN_BOX('','<span class=judul>'.strtoupper('Daftar Plasma / KUD').'</span><br>');


# Render Search Element
echo "<fieldset id='search' style='margin-bottom:10px;float:left;clear:both'>";
echo "<legend><b>".$_SESSION['lang']['searchdata']."</b></legend>";
echo $searchEls;
echo "</fieldset>";


# Begin Select Option Value Form KUD
$optBlok=array();

$str="select a.supplierid, namasupplier from ".$dbname.".log_5supplier a left join 
	  ".$dbname.".log_5supkelompok b on a.supplierid=b.supplierid
      where tipe='PLASMA' order by namasupplier ASC";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optSup='';
$optSup.="<option value=''></option>";
while($bar=$res->fetch())
{
	$optSup.="<option value='".$bar->supplierid."'>".$bar->namasupplier."</option>";
}
# End Select Option Value Form KUD
CLOSE_BOX();
OPEN_BOX();

#======= Begin Form KUD ============


echo"<div id='formKUD' style='display:none;margin-bottom:10px;clear:both'>
	<fieldset id='search' style='margin-bottom:10px;float:left;clear:both;width:430px'>
	<legend><b>KUD</b></legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['kodeblok']."</td><td>:</td>
			<td><select style='width:150px' id='kodeblok'>".$optBlok."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['namasupplier']." / KUD</td><td>:</td>
			<td><select style='width:150px' id='supplierid' onchange='getunitPlasma(0,0)'>".$optSup."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']." Plasma</td><td>:</td>
			<td><select style='width:150px' id='unitplasma' onchange='getblokplasma()'><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodeblok']." Plasma</td><td>:</td>
			<td><select style='width:150px' id='kodeblokplasma'><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nosertifikat']."</td><td>:</td>
			<td><input style='width:145px' class=myinputtext type='text' id='nosertifikat' maxlength='45'></td>
		</tr>
		<tr>
			<td></td>
			<td><td>
				<input type='hidden' id='hiddensupplierid' value=''>
				<input type='hidden' id='hiddenproses' value='simpan'>
				<button class=mybutton onclick=simpanData()>".$_SESSION['lang']['save']."</button>		
				<button class=mybutton onclick=clearData()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
	</fieldset>
	</div>";
#======= End Form KUD ============

#=======Table===============
# Display Table
echo "<div id='KUDTable' style='display:none;margin-bottom:10px;clear:both'>";
#echo masterTable($dbname,'setup_blok',"*",array(),array(),array(),array(),'setup_slave_blok_pdf');
echo "</div>";
#=======End Table============

CLOSE_BOX();
echo close_body();
?>