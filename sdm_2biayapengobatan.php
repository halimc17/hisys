<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

$arr0="##tanggal"; 
?>
<script language=javascript src='js/zTools.js'></script>
<script type="text/javascript" src="js/sdm_2biayapengobatan.js"></script>
<script>


</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php
$title[1]=$_SESSION['lang']['form'];

$optPt="<option value=''>".$_SESSION['lang']['all']."</option>";
$spt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$qpt=$owlPDO->query($spt) or die(print " Gagal: ".PDOException::getMessage());
$qpt->setFetchMode(PDO::FETCH_ASSOC);
while($rpt=  $qpt->fetch()){
    $optPt.="<option value='".$rpt['kodeorganisasi']."'>".$rpt['namaorganisasi']."</option>";
}

$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
$sdr="select distinct left(periode,4) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$qdr=$owlPDO->query($sdr) or die(print " Gagal: ".PDOException::getMessage());
$qdr->setFetchMode(PDO::FETCH_ASSOC);
$optPrdSmp="";
while($rdr=  $qdr->fetch()){
    $optPrdSmp.="<option value='".$rdr['periode']."'>".$rdr['periode']."</option>";
}
$arrsmstr=array("0"=>"".$_SESSION['lang']['all']."","I"=>"Satu","II"=>"Dua");
$optsmstr="";
foreach($arrsmstr as $lstsmtr=>$nmsstr){
    $optsmstr.="<option value='".$lstsmtr."'>".$nmsstr."</option>";
}
$arrdata=array("0"=>"Default","1"=>"Rumah Sakit");
$optsmstr2="";
foreach($arrdata as $lstsmtr=>$nmsstr){
    $optsmstr2.="<option value='".$lstsmtr."'>".$nmsstr."</option>";
}
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['biayapengobatan']).'</span>');

 $arr="##ptId2##unitId2##thn##smstr";
echo"<br><fieldset style=\"float: left;\">
<legend><b>".$title[1]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >";
echo"<tr><td>".$_SESSION['lang']['pt']."</td><td>:</td>";
echo"<td><select id=ptId2  onchange='getUnit2()'  style=width:150px;>".$optPt."</select></td>";
echo"</tr>";
echo"<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td>
          <td><select id=unitId2 style=width:150px;>".$optUnit."</select></td>
          </tr>";
echo"<tr><td>".$_SESSION['lang']['tahun']."</td><td>:</td>
          <td><select id=thn style=width:150px;>".$optPrdSmp."</select></td>
          </tr>";
echo"<tr><td>".$_SESSION['lang']['semester']."</td><td>:</td>
          <td><select id=smstr style=width:150px;>".$optsmstr."</select></td>
          </tr>";
//echo"<tr><td>".$_SESSION['lang']['data']."</td>
//          <td><select id=smbrData style=width:150px; onchange=getTmbl()>".$optsmstr2."</select></td>
//          </tr>";
 
echo"<tr>
    <td><td><td>
        <button class=mybutton onclick=zPreview('sdm_slave_2biayapengobatan','".$arr."','printContainer2')>".$_SESSION['lang']['proses']."</button>
        <button onclick=\"zExcel(event,'sdm_slave_2biayapengobatan.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"
<fieldset style='clear:both;max-width:1235px;'><legend><b>Print Area</b></legend>

<div id='printContainer2' style='overflow:auto;height:350px;max-width:1235px;'>
</div>

<div id='printContainer5' style='overflow:auto;height:350px;max-width:1235px;display:none;'>
</div>

<div id='printContainer7' style='overflow:auto;height:350px;max-width:1235px;display:none;'>
</div>
 
<div id='printContainer8' style='overflow:auto;height:350px;max-width:1235px;display:none;'>
</div>

<div id='printContainer9' style='overflow:auto;height:350px;max-width:1235px;display:none;'>
</div>
</fieldset>";



CLOSE_BOX();
echo close_body();
?>