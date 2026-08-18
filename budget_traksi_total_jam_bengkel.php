<?php
ini_set('display_errors',0);error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript1.2 src="js/budget_traksi_total_jam_bengkel.js?v='<? echo time(); ?>"></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?php
include('master_mainMenu.php');
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '%".$_SESSION['empl']['lokasitugas']."%' and tipe='TRAKSI' order by namaorganisasi asc";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch()){
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}

$optws="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(9) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}	
}

$opttraksi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		#$opttraksi.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$opttraksi.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		#$opttraksi.="</optgroup>";
	}	
}


$optws="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(17) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		#$optws.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optws.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		#$optws.="</optgroup>";
	}
	
}


OPEN_BOX('','<span class=judul>'.getMenu('budget_traksi_total_jam_bengkel').'</span>');
echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
		<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'>
		<br>".$_SESSION['lang']['new']."
	</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
		<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
			<table>
				<tr>
					<td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>:</td>
					<td><select class='select2' id=kodeorgsch onchange=loadData(); style=\"width:150px;\">".$optorg."</select></td>
					
					<td>" . $_SESSION['lang']['workshop'] . "</td>
					<td>:</td>
					<td><select class='select2' id=kodewssch onchange=loadData(0); style=\"width:150px;\">" . $optws . "</select></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton id=tombolcari onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button>
					<!--<button class=mybutton id=tombolbatalcari onclick=batalcari()>" . $_SESSION['lang']['cancel'] . "</button>--></td>
				</tr>
			</table>
		</fieldset>
     </tr></table>";

echo "</div>";
CLOSE_BOX();

echo"<div id=inputdetail style=display:none>";
OPEN_BOX();
echo"<fieldset style='float:left;'>
     <legend><b>".$_SESSION['lang']['form']."</b></legend>
         <table>
                 <tr><td width=100>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td><input type=text class=myinputtextnumber id=thnbudget name=thnbudget onkeypress=\"return angka_doang(event);\" style=\"width:145px;\" maxlength=4 /></td></tr>
                 <tr><td>".$_SESSION['lang']['kodetraksi']." </td><td width=10>:</td><td><select class='select2' id=kdorg name=kdorg onchange=getws(0,0) style=\"width:150px;\">".$opttraksi."</select></td></tr>
                 <tr><td>".$_SESSION['lang']['workshop']."</td><td width=10>:</td><td><select class='select2' id=kdtrak name=kdtrak style=\"width:150px;\">".$optws."</select></td></tr>
                 <tr><td>".$_SESSION['lang']['totJamThn']."</td><td width=10>:</td><td><input type=text class=myinputtextnumber  id=totjamthn name=totjamthn onkeypress=\"return angka_doang(event);\" style=\"width:145px;\" /></td></tr>
         <tr>
         
         <tr>
         <td></td><td></td><td>
                 <div id=tmblSave>
                 <button onclick=saveHead() class=mybutton name=saveDt id=saveDt>".$_SESSION['lang']['save']."</button>	 
         <button class=mybutton onclick=batal() name=btl id=btl>".$_SESSION['lang']['cancel']."</button></div>
        </td></tr>
        </table>
     </fieldset><input type=hidden id=method value=saveData />";
echo"<div id='printContainer' style=display:none;>
      <fieldset style='clear:both;float: left;'>
	  <legend>".$_SESSION['lang']['sebaran']." ".$_SESSION['lang']['bulanan']."</legend>";
$arrBln=array(
	"1"=>substr($_SESSION['lang']['jan'],0,3),
	"2"=>substr($_SESSION['lang']['peb'],0,3),
	"3"=>substr($_SESSION['lang']['mar'],0,3),
	"4"=>substr($_SESSION['lang']['apr'],0,3),
	"5"=>substr($_SESSION['lang']['mei'],0,3),
	"6"=>substr($_SESSION['lang']['jun'],0,3),
	"7"=>substr($_SESSION['lang']['jul'],0,3),
	"8"=>substr($_SESSION['lang']['agt'],0,3),
	"9"=>substr($_SESSION['lang']['sep'],0,3),
	"10"=>substr($_SESSION['lang']['okt'],0,3),
	"11"=>substr($_SESSION['lang']['nov'],0,3),
	"12"=>substr($_SESSION['lang']['dec'],0,3),
	);
$tot=count($arrBln);
echo"<table class=sortable border=0 cellspacing=1 cellpadding=1><thead><tr class=rowheader >";
foreach($arrBln as $brs=>$dtBln){
	echo"<td align=center>".$dtBln."</td>";
}
echo"<td>".$_SESSION['lang']['save']."</td></tr></thead>";
echo"<tbody><tr class=rowcontent>";
foreach($arrBln as $brs2=>$dtBln2){
	echo"<td><input type='text' class='myinputtextnumber'  id=jam_x".$brs2." value=0 style='width:50px' onkeypress=\"return angka_doang(event);\" /></td>";
}
echo"<td align=center style='cursor:pointer;'><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"saveJam(".$tot.")\" src='images/save.png'/></td>";

echo "</tr></tbody></table></fieldset></div>";
CLOSE_BOX();
echo"</div>";

OPEN_BOX();
#echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
echo"<div id=contain class='table-scroll'><script>loadData()</script></div>";
#echo"</fieldset>";
CLOSE_BOX();
echo close_body();
?>