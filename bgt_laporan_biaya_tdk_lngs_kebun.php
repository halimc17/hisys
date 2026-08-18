<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/zTools.js'></script>
<script language=javascript1.2 src='js/bgt_btl_kebun.js?ver=1'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_biaya_tdk_lngs_kebun').'</span><br>');
#ambil tahun budget
$str="select distinct(tahunbudget) as tahunbudget from  ".$dbname.".bgt_budget order by tahunbudget desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$opttahun="<option value=''>Pilih..</option>";
$opttahun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
    $opttahun.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
}
#ambil kode kebun
$str="select kodeorganisasi as kodeorg,namaorganisasi from  ".$dbname.".organisasi where (tipe='KEBUN' or tipe='KANWIL') order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optunit="<option value=''>Pilih..</option>";
while($bar=$res->fetch())
{
    $optunit.="<option value='".$bar->kodeorg."'>".$bar->kodeorg." - ".$bar->namaorganisasi."</option>";
}

$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optunit.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}

echo"<fieldset style='float:left;'><table>
     <tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td><select class=select2 id=thnbudget style='width:175px'>".$opttahun."</select></td></tr>
	 <tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td>:</td><td><select class=select2 id=kodeunit style='width:175px'>".$optunit."</select></td></tr>
	 <tr><td></td><td></td><td>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=tampilkanBTLKebun()>".$_SESSION['lang']['preview']."</button>
	 <button class=mybutton onclick=\"fisikKeExcel(event,'bgt_laporan_biaya_tdk_lngs_kebun_excel.php')\">".$_SESSION['lang']['excel']."</button>
     </td></tr>
	 </table>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX();
	echo"
	    <span style=\"display:none;\">
			<span id=\"printPanel\" style=\"display:none;\"></span>
			<img onclick=\"fisikKePDF(event,'...')\" title=\"PDF\" class=\"resicon\" src=\"images/pdf.jpg\">
			<span style=display:none>Unit:<label id=unit></label> Tahun Budget:<label id=tahun></label></span>
		 </span>
		 <div id='container' style='overflow:auto;height:400px;max-width:100%'>
		";

CLOSE_BOX();
echo close_body();
?>