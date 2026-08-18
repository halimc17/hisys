<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

$optbatch="<option value=''>".$_SESSION['lang']['all']."</option>";

$optkodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

// $kodeorg="select distinct kodeorganisasi,namaorganisasi 
//           from ".$dbname.".bibitan_mutasi c inner join ".$dbname.".organisasi a on left(c.kodeorg,4)=a.kodeorganisasi
//           where tipe='KEBUN' order by namaorganisasi asc";
// $query=$owlPDO->query($kodeorg) or die(print " Gagal: ".PDOException::getMessage());
// $query->setFetchMode(PDO::FETCH_ASSOC);
// while($result=$query->fetch()){
//     $optkodeorg.="<option value='".$result['kodeorganisasi']."'>".$result['kodeorganisasi']." - ".$result['namaorganisasi']."</option>";
// }

$optkodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(30) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optkodeorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optkodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optkodeorg.="</optgroup>";
	}
}

$arr="##kodeunit##kodebatch";
?>
<script language='javascript' src='js/zTools.js'></script>
<script language='javascript' src='js/zReport.js'></script>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<script language='javascript1.2' src='js/bibit_2kartu.js'></script>      
<?php

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2antarBibit').'</span>');
echo'
<div style="margin-bottom: 30px;">
    <fieldset style="float: left;">
    <legend><b>'.$_SESSION['lang']['form'].'</b></legend>
    <table cellspacing="1" border="0" >
    <tr>
        <td><label>'.$_SESSION['lang']['unit'].'</label></td><td>:</td>
        <td><select id="kodeunit" name="kodeunit" onchange="ambilbatch(this.value);" style="width:150px">'.$optkodeorg.'</select>
        </td>
    </tr>
    <tr>
        <td><label>'.$_SESSION['lang']['batch'].'</label></td><td>:</td>
        <td><select id="kodebatch" name="kodebatch" style="width:150px">'.$optbatch.'</select></td>
    </tr>
    <tr>
        <td></td><td><td>
        <button onclick="zPreview(\'bibit_slave_2kartu\',\''.$arr.'\',\'printContainer\')" class="mybutton" name="preview" id="preview">Preview</button>
        <button onclick="zExcel(event,\'bibit_slave_2kartu.php\',\''.$arr.'\')" class="mybutton" name="preview" id="preview">Excel</button>
        </td>
    </tr>
    </table>
    </fieldset>
</div>';


CLOSE_BOX();
OPEN_BOX();
echo '<div id=\'printContainer\' style=\'overflow:auto;height:400px;\'></div>';

CLOSE_BOX();
echo close_body();
?>