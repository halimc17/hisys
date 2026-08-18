<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');


$optOrg="";
// $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN'  order by kodeorganisasi";
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg=$qOrg->fetch())
// {
//     $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
// }
$arrunit = getOrgDetail(1);
foreach ($arrunit as $key => $val) {
	if($key==$_SESSION['empl']['lokasitugas']){
		$optOrg.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unit=$key;
	}else{
		$optOrg.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
}

$arr="##kodeorg##kegiatan##tgl1##tgl2##intiplasma";
if($_SESSION['language']=='EN'){
    $zz='namakegiatan1 as namaakun';
}else{
    $zz='namakegiatan as namaakun';
}
$kegiatan="";
$str="select kodekegiatan as noakun,".$zz." from ".$dbname.".setup_kegiatan
	  where (noakun LIKE '126%' or noakun LIKE '128%' or noakun LIKE '611%' or noakun LIKE '621%')
      order by kodekegiatan,namakegiatan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $kegiatan.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";
}

$arrOptIP = getEnum($dbname,'setup_blok','intiplasma');
$optIP = '';
$optIP .= "<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrOptIP as $val){
	if($val=="I"){
		$optIP .= "<option value='".$val."'>Inti</option>";
	}else{
		$optIP .= "<option value='".$val."'>Plasma</option>";
	}
}

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>


<link rel=stylesheet type=text/css href=style/zTable.css>
<?
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.strtoupper('ACTIVITY COST BY BLOCK').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('BIAYA KEGIATAN PER BLOK').'</span>');
}



?>
<div>
<fieldset style="float: left;">
<legend><b>Form</b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['kebun']?></label></td><td>:</td><td><select id="kodeorg" name="kdOrg" style="width:180px"><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['kegiatan']?></label></td><td>:</td><td><select id="kegiatan" name="kdAfd" style="width:180px"><?php echo $kegiatan?></select><img id='kegiatan' onclick=z.elSearch('kegiatan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td></tr>
<?php
echo"<tr>
	<td>".$_SESSION['lang']['intiplasma']."</td><td>:</td>
	
	<td><select style=width:180px id=intiplasma>".$optIP."</select></td>
</tr>";
?>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td>
<input type="text" class="myinputtext" id="tgl1" name="tgl1" onmousemove="setCalendar(this.id);" onkeypress="return false;"  maxlength="10" style="width:70px;"  readonly/> s.d.
<input type="text" class="myinputtext" id="tgl2" name="tgl2" onmousemove="setCalendar(this.id);" onkeypress="return false;"  maxlength="10" style="width:75px;"  readonly/></td></tr>

<tr><td colspan="2">
<td colspan="2">
    <button onclick="zPreview('kebun_slave_2kegiatanPerBlok','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
    <button onclick="zPdf('kebun_slave_2kegiatanPerBlok','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
    <button onclick="zExcel(event,'kebun_slave_2kegiatanPerBlok.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
</td>
</td></tr>

</table>
</fieldset>

<?php

CLOSE_BOX();
OPEN_BOX();
?>
<div class='table-scroll'><div id='printContainer' style=' height:350px; '></div>

</div>
<?php

CLOSE_BOX();
echo close_body();
?>