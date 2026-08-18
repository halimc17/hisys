<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<?php
$lokasi=substr($_SESSION['empl']['lokasitugas'],0,4);

// if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')or($_SESSION['empl']['tipelokasitugas']=='KANWIL')){
//     $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN') order by namaorganisasi asc ";	
// }else{
//     $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by induk, kodeorganisasi asc";
// }
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// while($rOrg=$qOrg->fetch()){
// 	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$rOrg['kodeorganisasi']."'");
// 	$d=$induk[$rOrg['kodeorganisasi']];
// 	if($d!=$n){			
// 		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
// 	}
//     $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
// 	$n=$d;
// 	if($d!=$n){			
// 		$optOrg.="</optgroup>";
// 	}
// }

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optOrg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

$optDiv="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$optMandor="<option value=\"all\">".$_SESSION['lang']['all']."</option>";
$sMan="select a.nikmandor, b.namakaryawan from ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid
    where a.kodeorg = '".$lokasi."'
    group by a.nikmandor
    order by b.namakaryawan";
$qMan=$owlPDO->query($sMan) or die(print " Gagal: ".PDOException::getMessage());
$qMan->setFetchMode(PDO::FETCH_ASSOC);
while($rMan=$qMan->fetch()){
    $optMandor.="<option value=".$rMan['nikmandor'].">".$rMan['namakaryawan']." [".$rMan['nikmandor']."]</option>";
}
$arr="##kebun##divisi##mandor##tanggal##tanggal2";

?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/kebun_2kehadiranpermandor.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
if($_SESSION['language']=='EN'){
    $title= "Foreman Daily Absence"; 
}else{
    $title= "Laporan Kehadiran per Mandor"; 
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2kehadiranpermandor').'</span>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php 
  echo 'Form';
?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['kebun']?></label></td><td>:</td><td><select class='select2' id="kebun" name="kebun" style="width:200px" onchange =mandor()><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['divisi']?></label></td><td>:</td><td><select class='select2' id="divisi" name="kebun" style="width:200px" onchange =getMandor()><option value=""></option></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['mandor']?></label></td><td>:</td><td><select class='select2' id="mandor" name="mandor" style="width:200px"><option value=""></option></select>
</td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td>
<input type="text" class="myinputtext" id="tanggal" name="tanggal" onmousemove="setCalendar(this.id);" onkeypress="return false;"  maxlength="10" style="width:85px;"  readonly/> s.d 
<input type="text" class="myinputtext" id="tanggal2" name="tanggal2" onmousemove="setCalendar(this.id);" onkeypress="return false;"  maxlength="10" style="width:80px;"  readonly/>
</td></tr>

<tr><td></td><td><td>
    <button onclick="zPreview('kebun_slave_2kehadiranpermandor','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
    <button onclick="zExcel(event,'kebun_slave_2kehadiranpermandor.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
    <button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button></td></tr>
</table>
</fieldset>
</div>
<?php

CLOSE_BOX();
OPEN_BOX();
?>
<div id='both_report'>
    <div id='head_tableboth' align=right>
        <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
            <img title='Full Screen' class='resicon' src='images/full-screen.png'>
        </a>
    </div>
<div id='printContainer' style='overflow:auto; height:350px;'></div></div>
<?php

CLOSE_BOX();
echo close_body();
?>