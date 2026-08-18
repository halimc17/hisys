<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/keu_2monitoringuangmuka.js'></script>

<?php
include('master_mainMenu.php');

#GET UNIT
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN','PABRIK','KANWIL','TRAKSI') and CHAR_LENGTH(kodeorganisasi)='4' order by namaorganisasi asc ";
}
else
{
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optUnit="";
$optUnit.="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=$res->fetch())
{
	$optUnit.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

#PERIODE
$str = "select distinct(left(tanggalperjalanan,7)) as periode from ".$dbname.".sdm_pjdinasht order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optPeriode = "<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) 
{
    $optPeriode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

#DATA KARYAWAN
$str = "select distinct(a.karyawanid) as nik, b.namakaryawan from ".$dbname.".sdm_pjdinasht a
	left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	where a.karyawanid!='' order by b.namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optKaryawan = "";
$optKaryawan = "<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) 
{
	$optKaryawan .= "<option value='".$bar['nik']."'>".$bar['namakaryawan']." (".$bar['nik'].")</option>";
}

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['monitoruangmuka']).'</span>');
//Form Pencarian
echo "<fieldset style='width:450px;'><legend><b>".$_SESSION['lang']['formcari']."</b></legend>
<table>
	<tr>
		<td>" . $_SESSION['lang']['unit'] . "</td>
		<td>:</td>
		<td><select id=unit style='width:200px;'>".$optUnit."</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['periode'] . "</td>
		<td>:</td>
		<td><select id=periode style='width:200px;'>".$optPeriode."</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
		<td>:</td>
		<td><select id=idkaryawan style='width:200px;'>".$optKaryawan."</select></td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td>
			<button class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

CLOSE_BOX();

OPEN_BOX();
echo "<fieldset>
	<legend><b>".$_SESSION['lang']['result']."</b></legend>
	<div id='showGraphic'>
	</div>
	<div style=clear:both;>&nbsp;</div>
	<div id='showTable' style='min-height:200px;'>
		
	</div>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>