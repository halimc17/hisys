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
<script type="text/javascript" src="js/log_baservis.js?ver=10.5" /></script>

<?php

$opttermin="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";



OPEN_BOX('','<span class=judul>BA Servis</span>');

echo"<table>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
	   <img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=loadData(0)>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo $_SESSION['lang']['noso'].":<input type=text id=txtsearchkontrak size=25 maxlength=50 class=myinputtext>";			
                        echo "NO. BA:<input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>";
			echo $_SESSION['lang']['tanggal'].":<input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
			echo"<button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
	 </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";
echo"<thead>";
echo"<tr><td align=center>".$_SESSION['lang']['nourut']."</td>";
echo"<td align=center>".$_SESSION['lang']['noso']."</td>";
echo"<td align=center>".$_SESSION['lang']['tanggal']."</td>";
echo"<td align=center>No. BA</td>";
echo"<td align=center>".$_SESSION['lang']['keterangan']."</td>";
echo"<td align=center>Created By</td>";
echo"<td colspan=5 style='text-align:center;'>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
$skeupenagih="select count(*) as rowd from ".$dbname.".log_baservis ";
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


// $arr="##noso##noba##keterangan";
echo"<div id=formInput style=display:none;>";
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
    <table style=width:100%;>";
echo"<tr><td>".$_SESSION['lang']['noso']."</td>
<td>
<input type=hidden id=id>
<input type=text id=noso class=myinputtext style=width:150px; readonly onclick=\"searchKontrak('".$_SESSION['lang']['find']." No. SO','noso','<div id=formPencariandata></div>',event)\" /></td>";
echo"<tr><td>".$_SESSION['lang']['tanggal']."</td><td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  style='width:95px;' maxlength=10 readonly value='".date('d-m-Y')."' /></td></tr>";
echo"<tr><td>No. BA</td><td><input type=text id=noba class=myinputtext style=width:150px; /></td></tr>";
echo"<tr style='display:none'><td>Termin</td><td><select id=termin style='width:150px'>".$opttermin."</select></td></tr>";


echo"<tr><td style='vertical-align:top;'>Keterangan</td><td><textarea id='keterangan' style=width:150px; onkeypress='return tanpa_kutip(event);' maxlength=40></textarea></td>

";
echo"<tr><td colspan='3'></td></tr>";


echo"<tr><td></td><td colspan=3>
		 <input type=hidden id=proses value='insert'  />
		 <input type=hidden id=kdOrg value=''  />
		 <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>&nbsp;
         <button class=mybutton onclick=cancelData()>".$_SESSION['lang']['cancel']."</button></td></tr>";


echo"</table></fieldset>"; 


CLOSE_BOX();
echo close_body(); ?>
