<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/sdm_5hkEfektif.js?v=<?php echo time(); ?>'></script>


<?
$arr="##periode##hariminggu##harilibur##hkefektif##catatan##kodept";

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['hkefektif']).'</span>');
// ambil periode -10 +3
$today = getdate();
$bulan = $today['mon'];
$tahun = $today['year'];
function tanggalan($minus){
    global $bulan;
    global $tahun;
    global $optperiode;
    $bulanan = $bulan+$minus;
    $tahunan = $tahun;
    if($bulanan<1){
        $bulanan=12+$bulanan; $tahunan=$tahun-1;
    }
    if($bulanan>24){
        $bulanan=$bulanan-24; $tahunan=$tahun+2;
    }else
    if($bulanan>12){
        $bulanan=$bulanan-12; $tahunan=$tahun+1;
    }
    if(strlen($bulanan)==1)$bulanan='0'.$bulanan;
    $optperiode.="<option value='".$tahunan."-".$bulanan."'>".$tahunan."-".$bulanan."</option>";
}
for ($i = -3; $i < 18; $i++)tanggalan($i);

$whr=getOrgDetail(4);
$optkodept= "<option selected value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (".$whr.") and tipe='PT' order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ); 
while($bar=$res->fetch()){
	$optkodept.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
}

echo"<fieldset>
     <legend>".$_SESSION['lang']['hkefektif']."</legend>
	 <table>

	 <tr>
	   <td>".$_SESSION['lang']['kodept']."</td>
	   <td><select id=kodept style='width:100px'><option value=''>".$optkodept."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['periode']."</td>
	   <td><select onchange=\"tambah();\" id=periode style='width:100px'><option value=''>".$optperiode."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['hariminggu']."</td>
	   <td><input onblur=\"tambah();\" type=text class=myinputtextnumber id=hariminggu name=hariminggu onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=3></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['harilibur']."</td>
	   <td><input onblur=\"tambah();\" type=text class=myinputtextnumber id=harilibur name=harilibur onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=3></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['hkefektif']."</td>
	   <td><input type=text class=myinputtextnumber id=hkefektif name=hkefektif style=\"width:100px;\" maxlength=3 disabled/></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['catatan']."</td>
	   <td><input type=text class=myinputtext id=catatan name=catatan onkeypress=\"return tanpa_kutip(event);\" style=\"width:100px;\" /></td>
	 </tr>
	 </table>
         <input type=hidden value=insert id=method>
         <button class=mybutton onclick=savehk('sdm_slave_5hkEfektif','".$arr."')>".$_SESSION['lang']['save']."</button>
         <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </fieldset><input type='hidden' id=oldtahunbudget name=oldtahunbudget />";
CLOSE_BOX();

OPEN_BOX();
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td align=center>No</td>
	   <td align=center>".$_SESSION['lang']['kodept']."</td>
	   <td align=center>".$_SESSION['lang']['periode']."</td>
	   <td align=center>".$_SESSION['lang']['hariminggu']."</td>
	   <td align=center>".$_SESSION['lang']['harilibur']."</td>
	   <td align=center>".$_SESSION['lang']['hkefektif']."</td>
	   <td align=center>".$_SESSION['lang']['catatan']."</td>
	   <td align=center>".$_SESSION['lang']['action']."</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
	 echo"<script>loadData()</script>";
echo"</tbody>
     <tfoot>
     </tfoot>
     </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>