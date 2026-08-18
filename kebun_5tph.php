<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language=javascript1.2 src='js/kebun_5tph.js?v=<?php echo time(); ?>'></script>
<script>
function submitFile(){
    if(confirm('Are you sure..?')){
    document.getElementById('frm').submit();
    }
}


$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
</script>
<?

OPEN_BOX('','<span class=judul>'.getMenu('kebun_5tph').'</span><br>');	
$str="select distinct indukblok,namaindukblok from ".$dbname.".organisasi where tipe='BLOK'
      and indukblok like '".$_SESSION['empl']['lokasitugas']."%' order by indukblok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

$optorg="<option value=''></option>";
while($bar=$res->fetch()){
    $optorg.="<option value='".$bar->indukblok."'>".$bar->indukblok." - ".$bar->namaindukblok."</option>";
}
$frm[0] = '';
//$frm[1] = '';

$frm[0].="<fieldset style='float:left;'><legend>".$_SESSION['lang']['form']."</legend>
     <table>
     <tr>
       <td>".$_SESSION['lang']['kodeblok']."</td><td>:</td><td colspan=3><select class='select2' id='kodeorg' onchange=getList(0,this.options[this.selectedIndex].value) style='width:200px;'>".$optorg."</select></td>
     </tr>
     <tr>
       <td>".$_SESSION['lang']['notph']."</td><td>:</td><td colspan=3><input type=text class=myinputtext style='width:195px;' id=notph onkeypress=\"return tanpa_kutip(event);\"></td>
     </tr>
     <tr>
       <td>".$_SESSION['lang']['notph']." Besar</td><td>:</td><td colspan=3><select class='select2' id='notphbesar' onchange=getList(0,this.options[this.selectedIndex].value) style='width:200px;'></select></td>
     </tr>
     <tr>
       <td>".$_SESSION['lang']['keterangan']."</td><td>:</td><td colspan=3><input type=text class=myinputtext style='width:195px;' id=keterangan onkeypress=\"return tanpa_kutip(event);\"></td>
     </tr>
	 <tr>
       <td>Latitude</td><td>:</td><td colspan=3><input type=text style='width:195px;' id=lat nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber></td>
     </tr>
	 
	 <tr>
       <td>Longitude</td><td>:</td><td colspan=3><input type=text style='width:195px;' id=long nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber></td>
     </tr>
	 <tr>
       <td>Luas</td><td>:</td><td><input type=text style='width:70px;' id=luas nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"></td>
	   <td>Status</td><td><select id='status' style='width:80px;'>
			<option value='A'>Aktip</option>
			<option value='D'>Non Aktip</option>
	   </select></td>
	   
     </tr>
	 
	 <tr>
		<td colspan=2></td><td colspan=9>
			<button class=mybutton onclick=saveTph() state='save' id=tombol>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=cancelTph() state='save' id=tombol>".$_SESSION['lang']['cancel']."</button>
		</td>
	 </tr>
     </table>
     </fieldset>
	
		
	 ";

$frm[0].="<div style=clear:both></div><fieldset><legend>".$_SESSION['lang']['list']."</legend>
	<fieldset style=float:left><legend>".$_SESSION['lang']['find']."</legend>
	<table>
     <tr>
       <td>".$_SESSION['lang']['kodeblok']."</td><td>:</td><td><input id='kodeorgsrc' class=myinputtext style='width:100px;' onkeypress='enterkey(event,getList)'></td>
       <td>&nbsp;No TPH</td><td>:</td><td><input id='notphsrc' class=myinputtext style='width:100px;'  onkeypress='enterkey(event,getList)'></td>
	   <td><button class=mybutton onclick=getList(0)>".$_SESSION['lang']['find']."</button></td>
	 </tr>
     </table>
	</fieldset>
	<div style=clear:both></div>
	<hr>
      <div id=contain style='width:100%;height:350px;overflow:auto'>
		<script>getList(0)</script>
      </div>
      </fieldset>";
// $frm[1].="
// 	<fieldset><legend>".$_SESSION['lang']['upload']."</legend>
// 			<div id=uForm>
// 				<span id=sample>
// 				<table>
// 					<tr>
// 						<td>Catatan :</td>
// 					</tr>
// 					<tr>
// 						<td></td>
// 						<td>
// 							1. Template file upload dapat di download <a href=tool_slave_getExample.php?form=TPH target=frame>disini.</a>
// 						</td>
// 					</tr>
// 					<tr>
// 						<td></td>
// 						<td>
// 							2. File type hanya support CSV.
// 						</td>
// 					</tr>
// 				</table>
// 				</span>
// 				<br>
				
// 				<form id=frm name=frm enctype=multipart/form-data method=post action=tool_slave_uploadData.php target=frame>
// 					<input type=hidden name=jenisdata id=jenisdata value='TPH'>
// 					<input type=hidden name=MAX_FILE_SIZE value=1024000>
// 					File : <input name=filex type=file id=filex size=25 class=mybutton>
// 					Field separated by : 
// 					<select name=pemisah>
// 						<option value=','>, (comma)</option>
// 					</select>
// 					<input type=button class=mybutton  value=".$_SESSION['lang']['upload']." title='Submit this File' onclick=submitFile()>
// 				</form>
				
// 				<iframe frameborder=0 width=100% height=400px name=frame></iframe>
// 			</div>
			
// 		</fieldset>
// 		";
$hfrm[0] = "Form Input";
//$hfrm[1] = "Form Upload";
drawTab('FRM', $hfrm, $frm, 170, '100%');

CLOSE_BOX();
echo close_body();
?>