<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/zTools.js'></script>
<script language=javascript1.2 src='js/bgt_btl_kebun.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_rp_kg_pks').'</span><br>');
#ambil tahun budget
$str="select distinct(tahunbudget) as tahunbudget from  ".$dbname.".bgt_budget order by tahunbudget desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$opttahun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=$res->fetch())
{
    $opttahun.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
}
#ambil kode kebun
$str="select kodeorganisasi as kodeorg from  ".$dbname.".organisasi where tipe='PABRIK' order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optunit="<option value=''>Pilih..</option>";
while($bar=$res->fetch())
{
    $optunit.="<option value='".$bar->kodeorg."'>".$bar->kodeorg."</option>";
}

$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(13) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".substr($key,0,4)."'");
	$d=$induk[substr($key,0,4)];
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
     <tr><td>".$_SESSION['lang']['tahunanggaran']."</td><td><select class=select2 id=thnbudget style='width:175px'>".$opttahun."</select></td></tr>
     <tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td><select class=select2 id=kodeunit style='width:175px'>".$optunit."</select></td></tr>
     <tr><td>".$_SESSION['lang']['jenisbiaya']."</td><td><select class=select2 id=jenis style='width:175px'>
     <option value=''>".$_SESSION['lang']['pilihdata']."</option>
     <option value='LANGSUNG'>LANGSUNG</option>
     <option value='UMUM'>UMUM</option>
     <option value='LANGSUNG DAN UMUM'>LANGSUNG DAN UMUM</option>
     </select></td></tr>
	 
	 <tr>
		<td></td>
		<td>	
			 <button class=mybutton onclick=tampilkanRPKGPks()>".$_SESSION['lang']['preview']."</button>
			 <button class=mybutton onclick=\"fisikKeExcelRPKG(event,'bgt_laporan_RPKG_pks_excel.php')\">".$_SESSION['lang']['excel']."</button>
		</td> 
	 </tr>		 
     </table>
	 <input type=hidden id=method value='insert'>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo "<div id=container style='overflow:auto;height:450px;max-width:100%'>
</div>";

CLOSE_BOX();
echo close_body();
?>