<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script>

andayakin="<?php echo $_SESSION['lang']['notifandayakin'];?>";
</script>
<script type="text/javascript" src="js/pabrikasi_absensi.js" /></script>

<?php
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['absensi']).'</span>');

echo"<table>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
	   <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=loadData(0)>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo $_SESSION['lang']['notransaksi']." : <input type=text id=txtsearch size=25 maxlength=50 class=myinputtext>";			
			echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
			echo"<button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
	 </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
echo"<fieldset style=width:850px><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>";
echo"<thead>";
echo"<tr><td align=center>".$_SESSION['lang']['nourut']."</td>";
echo"<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td align=center>".$_SESSION['lang']['tanggal']."</td>";
echo"<td align=center>".$_SESSION['lang']['kodeorg']."</td>";
echo"<td align=center>".$_SESSION['lang']['dibuatoleh']."</td>";
echo"<td colspan=4 style='text-align:center;'>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
$skeupenagih="select count(*) as rowd from ".$dbname.".keu_penagihanht where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
$qkeupenagih=$owlPDO->query($skeupenagih) or die(print " Gagal: ".PDOException::getMessage());
$rkeupenagih=owlBaris($qkeupenagih);

$totrows=ceil($rkeupenagih/10);
if($totrows==0){
    $totrows=1;
}
$isiRow='';
for($er=1;$er<=$totrows;$er++){
    $isiRow.="<option value='".$er."'>".$er."</option>";
}
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div><input type=hidden id=proses value=insert />";
$optData="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPrd = "";
$sData="select distinct(periode) from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' and sudahproses=0 order by periode desc";
$rData=fetchdata($sData);
foreach ($rData as $isiPabrikasi) {
    $optPrd.="<option value=" . $isiPabrikasi['periode'] . ">" . $isiPabrikasi['periode'] . "</option>";
}


	

$optCust='';
$sakun="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."'
        and tipe='AFDELING' order by namaorganisasi asc";
$qakun=$owlPDO->query($sakun) or die(print " Gagal: ".PDOException::getMessage());
$qakun->setFetchMode(PDO::FETCH_ASSOC);
while($rakun=$qakun->fetch()){
    $optCust.="<option value='".$rakun['kodeorganisasi']."'>".$rakun['kodeorganisasi']."-".$rakun['namaorganisasi']."</option>";
}
$optPabrikasi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sData="select kodepabrikasi,namapabrikasi from ".$dbname.".pabrikasi_5masterht where kodeorg='".$_SESSION['empl']['lokasitugas']."' and status=1 order by namapabrikasi asc";
$rData=fetchdata($sData);
foreach ($rData as $isiPabrikasi) {
    $optPabrikasi.="<option value=" . $isiPabrikasi['kodepabrikasi'] . ">" . $isiPabrikasi['kodepabrikasi'] . " - " . $isiPabrikasi['namapabrikasi'] . "</option>";
}

$sData="select kodeabsen,keterangan from ".$dbname.".sdm_5absensi where kodeabsen='H'";
$rData=fetchdata($sData);
foreach ($rData as $isiPabrikasi) {
    $optAbs.="<option value=" . $isiPabrikasi['kodeabsen'] . ">" . $isiPabrikasi['kodeabsen'] . " - " . $isiPabrikasi['keterangan'] . "</option>";
}




echo"<div id=formInput style=display:none>";
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
    <table style=width:100%;>";

echo"<tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td><td><input type=text id=notransaksi disabled=disabled class=myinputtext style=width:150px; readonly onclick=\"searchKontrak('".$_SESSION['lang']['find']." ".$_SESSION['lang']['NoKontrak']." ".$_SESSION['lang']['internal']."','Internal','<div id=formPencariandata></div>',event)\" /></td>";
echo"<td>&nbsp;</td>"
    . "<td>&nbsp;</td>"
    . "</tr>";//

echo"<tr><td>".$_SESSION['lang']['kodeorg']."</td><td>:</td><td><select id=kdorg style=width:155px>".$optCust."</select></td>";
echo"<td>&nbsp;</td><td>&nbsp;</td></tr>";

echo"<tr><td>".$_SESSION['lang']['periode']."</td><td>:</td><td><select id=periodedt style=width:85px>".$optPrd."</select></td>";
echo"<td>&nbsp;</td><td>&nbsp;</td></tr>";

echo"<tr><td>".$_SESSION['lang']['tanggal']."</td><td>:</td><td><input type=text class=myinputtext id=tgldata onmousemove=setCalendar(this.id) onkeypress=return false; style=width:80px size=12 maxlength=10 /></td>";
echo"<td>&nbsp;</td><td>&nbsp;</td></tr>";




echo"<tr><td></td><td colspan=3>
		 <input type=hidden id=proses value='insert'  />
		 <input type=hidden id=pernah value='0'  />
		 <button class=mybutton id=tomblSimpan onclick=saveData()>".$_SESSION['lang']['save']."</button>&nbsp;
         <button class=mybutton onclick=cancelData()>".$_SESSION['lang']['done']."</button></td></tr>";


echo"</table></fieldset></div><div style=clear:both>&nbsp;</div>";
echo"<div id=formDetail style=display:none>";
echo"<fieldset  style=width:768px>
<legend>".$_SESSION['lang']['detail']."</legend>
<table border=0 cellspacing=1 cellpading=1 class=sortable>
    <thead>
        <tr class=rowheader align=center>
        <td>".$_SESSION['lang']['namakaryawan']."</td>
        <td>".$_SESSION['lang']['kodepabrikasi']."</td>
        <td>".$_SESSION['lang']['absensi']."</td>
        <td>".$_SESSION['lang']['jhk']."</td>
        <td>".$_SESSION['lang']['umr']."</td>
        <td>".$_SESSION['lang']['premi']."</td>
        <td>".$_SESSION['lang']['action']."</td>
        </tr>
    </thead>
<tbody>
<tr class=rowcontent>
    <td><select id=karyId style=wdith:120px onchange=getGaji()>".$optData."</select>
	<img id='karyId' onclick=z.elSearch('karyId',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
	</td>
    <td><select id=pabrikasiId style=wdith:100px>".$optPabrikasi."</select></td>
    <td><select id=absensiId style=wdith:100px>".$optAbs."</select></td>
    <td><input type=text class=myinputtextnumber id=jhk  size=7 onkeypress='return angka_doang(event)' onkeyup=getGaji() /></td>
    <td><input type=text class=myinputtextnumber disabled id=umr  size=8 onkeypress='return angka_doang(event)' /></td>
    <td><input type=text class=myinputtextnumber id=premi  size=12 onkeypress='return angka_doang(event)' /></td>
    <td align=center><img id=\"detail_add\" title=\"Simpan\" class=\"zImgBtn\" onclick=\"addDetail()\" src=\"images/save.png\"></td>
</tr>
</tbody>
</table>
</fieldset>
<div style=clear:both>&nbsp;</div>
<fieldset style=width:768px><legend>".$_SESSION['lang']['data']."</legend>
<table border=0 cellspacing=1 cellpading=1 class=sortable width=100%>
    <thead>
        <tr class=rowheader align=center>
        <td>".$_SESSION['lang']['nourut']."</td>
        <td>".$_SESSION['lang']['nik']."</td>
        <td>".$_SESSION['lang']['namakaryawan']."</td>
        <td>".$_SESSION['lang']['kodepabrikasi']."</td>
        <td>".$_SESSION['lang']['absensi']."</td>
        <td>".$_SESSION['lang']['jhk']."</td>
        <td>".$_SESSION['lang']['umr']."</td>
        <td>".$_SESSION['lang']['premi']."</td>
        <td colspan=2>".$_SESSION['lang']['action']."</td>
        </tr>
    </thead>
<tbody id=detailData>
</tbody></table>
</fieldset>
";
echo"</div>"; 


CLOSE_BOX();
echo close_body(); ?>
