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
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_biaya_langsung_pks').'</span><br>');
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
$str="select * from  ".$dbname.".keu_5akun where noakun like '63%' and length(noakun)='3' order by noakun";
$res=fetchdata($str);
$optkel="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($res as $bar){
    $optkel.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
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

echo"<fieldset style=float:left><table>
     <tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td><select class=select2 id=thnbudget style='width:150px'>".$opttahun."</select></td></tr>
	 <tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td>:</td><td><select class=select2 id=kodeunit style='width:150px'>".$optunit."</select></td></tr>
	 <tr><td>".$_SESSION['lang']['kelompokbiaya']."</td><td>:</td><td><select class=select2 id=kelompokbiaya style='width:150px'>".$optkel."</select></td></tr>
	 <input type=hidden id=method value='insert'>
	 <tr>
		<td colspan=2></td>
		<td>
		 <button class=mybutton onclick=tampilkanBLPks()>".$_SESSION['lang']['preview']."</button>
		 <button class=mybutton onclick=\"tampilkanBLPksExcel()\">".$_SESSION['lang']['excel']."</button>
		</td> 
	 </tr>
     </table>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo "<div id=container style='overflow:auto;height:450px;max-width:100%'>
</div>";


CLOSE_BOX();
echo close_body();
?>