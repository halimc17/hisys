<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once ('config/connection.php');
echo open_body();
?>
<script language=javascript>isidata="<?php echo"<tr class=rowcontent><td colspan=10>".$_SESSION['lang']['dataempty']."</td></tr>"?>";</script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src='js/kebun_5bjr.js?v=<?php echo time(); ?>'></script>
<?
$optKebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optKlsPohon="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
$sKebun="select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' ";
else
$sKebun="select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
$qBlok=$owlPDO->query($sKebun) or die(print " Gagal: ".PDOException::getMessage());
$qBlok->setFetchMode(PDO::FETCH_ASSOC);
while($rBlok=$qBlok->fetch())
{
    $optKebun.="<option value='".$rBlok['kodeorganisasi']."'>".$optNmOrg[$rBlok['kodeorganisasi']]."</option>";
}

$strKlsPhn="select kelas,nama from ".$dbname.".kebun_5kelaspohon";
$qryKlsPhn=$owlPDO->query($strKlsPhn) or die(print " Gagal: ".PDOException::getMessage());
$qryKlsPhn->setFetchMode(PDO::FETCH_ASSOC);
while($rowKlsPhn=$qryKlsPhn->fetch())
{
    $optKlsPohon.="<option value='".$rowKlsPhn['kelas']."'>".$rowKlsPhn['kelas']." - ".$rowKlsPhn['nama']."</option>";
}

$optPeriode="";
// $bln=array("02"=>"02","05"=>"05","08"=>"08","11"=>"11");
// for($x=-1;$x<2;$x++){
	// foreach($bln as $res => $bar){
			// $dt=mktime(0,0,0,$res,12,date('Y')-$x);
			// $optPeriode.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";		
	// }
// }

for($x=0;$x<15;$x++){
	$dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
	$optPeriode.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
}

$arr="##thnProd##kdKebun##kdBlok##kelaspohon##jmBjr##proses";

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_5bjr').'</span><br>');

@$frm[0].="<fieldset style=width:500px;float:left;>
     <legend>".$_SESSION['lang']['form']."</legend> 
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['unit']."</td><td>:</td>
	   <td><select id='kdKebun' onchange=loadBlok() style=\"width:150px;\">".$optKebun."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['periode']."</td><td>:</td>
	   <td>	<select id='thnProd' style=\"width:80px;\">".$optPeriode."</select>
           <button class=mybutton onclick=loadData() title='clik untuk get data'>".$_SESSION['lang']['preview']."</button></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['kodeblok']."</td><td>:</td>
	   <td><select id='kdBlok'  style=\"width:150px;\" disabled>".$optBlok."</select></td>
	 </tr>
	 <tr>
	   <td style='display:none;'>".$_SESSION['lang']['kelaspohon']."</td>
	   <td style='display:none;'><select id='kelaspohon'  style=\"width:150px;\" disabled>".$optKlsPohon."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['bjr']."</td><td>:</td>
	   <td><input type=text class=myinputtextnumber id=jmBjr name=jmBjr onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=7  disabled /> </td>
	 </tr>	
	
	 <tr><td><td><td>
	 <input type=hidden value=insert id=proses>
	 <button class=mybutton onclick=saveFranco('kebun_slave_5bjr','".$arr."')>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['done']."</button>
     </td></td></td></tr></table></fieldset>";
	
/*@$frm[0].="<fieldset style=height:125px;>
     <legend>".$_SESSION['lang']['info']."</legend>
	 <table border=0><tr>
	 <td>1.</td><td>Panen bulan <b>Nov, Des, Jan</b> menggunakan periode BJR <b>Ags</b> => rata2 BJR bulan <b>Ags, Sep, Okt</b></td></tr><tr>
		<td>2.</td><td>Panen bulan <b>Feb, Mar, Apr</b> menggunakan periode BJR <b>Nov</b> => rata2 BJR bulan <b>Nov, Des, Jan</b></td></tr><tr>
		<td>3.</td><td>Panen bulan <b>Mei, Jun, Jul</b> menggunakan periode BJR <b>Feb</b> => rata2 BJR bulan <b>Feb, Mar, Apr</b></td></tr><tr>
		<td>4.</td><td>Panen bulan <b>Ags, Sep, Okt</b> menggunakan periode BJR <b>Mei</b> => rata2 BJR bulan <b>Mei, Jun, Jul</b></td></tr>
		

	 </table>
	 </fieldset>";*/

	 @$frm[0].="<div style=clear:both></div>";

$str="select distinct substr(kodeorg,1,4) as kodeorg,tahunproduksi from ".$dbname.".kebun_5bjr where substr(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' order by tahunproduksi desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

$frm[0].="<div id=listThnProduksi><fieldset style='height:350px;'><legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['tahunproduksi']."</legend>";
$frm[0].="<table border=0 cellpadding=1 cellspacing=1><thead>";
$frm[0].="<tr class=rowheader><td>".$_SESSION['lang']['kodeorg']."</td><td>".$_SESSION['lang']['tahunproduksi']."</td></tr><tbody>";
while($rowData=$res->fetch())
{
    $frm[0].="<tr class=rowcontent><td>".$optNmOrg[$rowData['kodeorg']]."</td><td>".$rowData['tahunproduksi']."</td></tr>";
}
$frm[0].="</tbody></table></fieldset></div>";
$frm[0].="<div id=listDataBjr style=display:none >";
$frm[0].="<fieldset style='min-height:350px;'><legend>".$_SESSION['lang']['list']."</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td align=center>No</td>
	   <td align=center>".$_SESSION['lang']['kodeblok']."</td>
	   <td style='display:none;'>".$_SESSION['lang']['kelaspohon']."</td>
	   <td align=center>".$_SESSION['lang']['tahunproduksi']."</td>
	   <td align=center>".$_SESSION['lang']['periode']."</td>
	   <td align=center>".$_SESSION['lang']['tahuntanam']."</td>
	   <td align=center>".$_SESSION['lang']['jenisbibit']."</td>
	   <td align=center>".$_SESSION['lang']['bjr']."</td>
	   <td align=center>Action</td>
	  </tr>
	 </thead>
	 <tbody id=container >";
	 $frm[0].="<tr class=rowcontent><td colspan=10>".$_SESSION['lang']['dataempty']."</td></tr>";

$frm[0].="</tbody>
     <tfoot>
	 </tfoot>
	 </table></fieldset></div>";
	 
$arr2="##kdKebun2##bln##tahun##periode1##periode2"; 
@$frm[1].="<fieldset style=width:500px;height:110px;float:left;>
     <legend>".$_SESSION['lang']['form']."</legend> 
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['unit']."</td><td>:</td>
	   <td><select id='kdKebun2' onchange=getBln()  style=\"width:150px;\">".$optKebun."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['periode']." ".$_SESSION['lang']['bjr']."</td><td>:</td>
	   <td> <select id='bln' onchange=getThn() style=\"width:75px;\">".@$optPrd."</select>
	   <select id='tahun'  onchange=getPrd() style=\"width:70px;\">".@$thn."</select>
			
           </td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['rerata']." ".$_SESSION['lang']['produksi']."</td><td>:</td>
	   <td> <select id='periode1'  style=\"width:75px;\">".@$prd1."</select>
			<select id='periode2'  style=\"width:70px;\">".@$prd2."</select></td>
	 </tr>
	 	
	 <tr><td><td><td>
	 <button onclick=zPreview('kebun_slave_5bjrproses','" . $arr2 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
     </td></td></td></tr></table></fieldset>";
	 
/*@$frm[1].="<fieldset style=height:110px;>
     <legend>".$_SESSION['lang']['info']."</legend>
	 <table border=0><tr>
	 <td colspan=3>
		Jika BJR periode ini <b>lebih kecil</b> dari BJR periode lalu maka yang di ambil adalah <b>BJR periode lalu</b>.
	 </td></tr><tr>
	 <td>
		Febuary </td><td>:</td><td> Rata - rata BJR bulan <b>Feb, Mar, Apr.</b>
	 </td></tr><tr>
	 <td>
		Mei </td><td>:</td><td> Rata - rata BJR bulan <b>Mei, Jun, Jul.</b>
	 </td></tr><tr>
	 <td>
		Agustus </td><td>:</td><td> Rata - rata BJR bulan <b>Ags, Sep, Okt.</b>
	 </td></tr><tr>
	 <td>
		November </td><td>:</td><td> Rata - rata BJR bulan <b>Nov, Des, Jan.</b>
	 </td></tr>
	 </table>
	 </fieldset>";*/
@$frm[1].="<div style=clear:both></div>
	 
	<fieldset><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
	<div id='printContainer' style='overflow:auto;height:350px;max-width:100%'; >
	</div></fieldset>";

	 

$hfrm[1]=$_SESSION['lang']['proses'];
$hfrm[0]=$_SESSION['lang']['input'];

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,150,'100%');	

CLOSE_BOX();
echo close_body();
?>