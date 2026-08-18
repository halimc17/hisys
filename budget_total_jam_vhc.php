<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
$bhs=$_SESSION['language'];
?>
<script>
	save="<? echo $_SESSION['lang']['save']; ?>";btl="<? echo $_SESSION['lang']['cancel']; ?>";pilih="<? echo $_SESSION['lang']['pilihdata']; ?>";

	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
	
	$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});

</script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src="js/budget_total_jam_vhc.js?v=<? echo time(); ?>"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)='4' order by induk asc, namaorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$rOrg['kodeorganisasi']."'");
	$d=$induk[$rOrg['kodeorganisasi']];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optUnit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
    $optUnit.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
	
	$n=$d;
	if($d!=$n){			
		$optUnit.="</optgroup>";
	}
}

$optSup="<option value=''>".$_SESSION['lang']['all']."</option>";
$sSup="select supplierid,namasupplier from ".$dbname.".log_5supplier where substring(supplierid,1,1)='S' order by namasupplier asc";
$qSup=$owlPDO->query($sSup) or die(print " Gagal: ".PDOException::getMessage());
$qSup->setFetchMode(PDO::FETCH_ASSOC);
while($rSup=$qSup->fetch()){
    $optSup.="<option value=".$rSup['supplierid'].">".$rSup['namasupplier']."</option>";
}
$optLokal="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sSup="select distinct kodevhc, nopol,detailvhc from ".$dbname.".vhc_5master order by kodevhc asc";
$res=fetchdata($sSup);
foreach($res as $rSup){	
	if($rSup['nopol']!=''){
		$rSup['nopol']=" - ".$rSup['nopol'];
	}
	if($rSup['detailvhc']!=''){
		$rSup['detailvhc']=" - ".$rSup['detailvhc'];
	}
	$optLokal.="<option value='".$rSup['kodevhc']."' >".$rSup['kodevhc']."".$rSup['nopol']."".$rSup['detailvhc']."</option>";
}

$optKdtraksisch="<option value=''>".$_SESSION['lang']['all']."</option>";
$optKdtraksi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optKdtraksi.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optKdtraksisch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	
	$optKdtraksi.="<option value=".$key.">".$key." - ".$val."</option>";
	$optKdtraksisch.="<option value=".$key.">".$key." - ".$val."</option>";
	
	$n=$d;
	if($d!=$n){			
		$optKdtraksi.="</optgroup>";
		$optKdtraksisch.="</optgroup>";
	}
}

$optwssch="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(9) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorgsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorgsch.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorgsch.="</optgroup>";
	}	
}

OPEN_BOX('','<span class=judul>'.getMenu('budget_total_jam_vhc').'</span>');
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
					<td>" . $_SESSION['lang']['budgetyear'] . "</td>
					<td>:</td>
					<td><input type=text class=myinputtext id=tahunbudgetsch name=tahunbudget onkeypress=\"return angka_doang(event);\" maxlength=4 style=width:70px; />
					</td>
					
					<td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>:</td>
					<td><select class=select2 id=kodeorgsch onchange=loaddata(); style=\"width:150px;\">".$optorgsch."</select></td>
					
					<td>" . $_SESSION['lang']['traksi'] . "</td>
					<td>:</td>
					<td><select class=select2 id=kodewssch onchange=loaddata(0); style=\"width:150px;\">" . $optKdtraksisch . "</select></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton id=tombolcari onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
					<!--<button class=mybutton id=tombolbatalcari onclick=batalcari()>" . $_SESSION['lang']['cancel'] . "</button>--></td>
				</tr>
			</table>
		</fieldset>
     </tr></table>";

echo "</div>";
CLOSE_BOX();

echo"<div id=inputdetail style=display:none>";
OPEN_BOX();
echo"<fieldset style=float:left><legend>Form</legend>
	<table cellspacing=1 border=0>
    <tr>
		<td>".$_SESSION['lang']['budgetyear']."</td>
		<td>:</td>
		<td><input type='text' id='thnBudget' class='myinputtextnumber' onkeypress='return angka_doang(event);' style='width:145px' maxlength='4' /></td>
	</tr>
    <tr>
		<td>".$_SESSION['lang']['kodetraksi']."</td>
		<td>:</td>
		<td><select class=select2 id='kdTraksi' name='kdTraksi' style='width:150px' onchange='getKdvhc(0,0);' onblur='loadDatadetail();'>".$optKdtraksi."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['kodevhc']."</td>
		<td>:</td>
		<td><select class=select2 id='kdVhc' name='kdVhc' onchange='loadDatadetail();' style='width:150px'>".$optLokal."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['alokasi']." ".$_SESSION['lang']['ke']." ".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select class=select2 id='kdUnit' style='width:150px;'>".$optUnit."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['totJamThn']."</td>
		<td>:</td>
		<td><input type='text' id='totJamThn' class='myinputtextnumber' onkeypress='return angka_doang(event);' style='width:145px'></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td>
		<div id='tmblSave'>
			<button onclick='saveHead()' class='mybutton' name='saveDt' id='saveDt'>".$_SESSION['lang']['save']."</button>
			<button onclick='batal()' class='mybutton' name='btl' id='btl'>".$_SESSION['lang']['cancel']."</button></div>
		</td>
	</tr>
	</table>
	<input type=hidden id=proses value=saveData>
	</fieldset>";
	
echo"<div style=clear:both></div>
	<div id='printContainer' style=display:none>
		<fieldset style='clear:both;float: left;'><legend>".$_SESSION['lang']['sebaran']."</legend>
	";

	$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sept","10"=>"Okt","11"=>"Nov","12"=>"Des");
	$tot=count($arrBln);
	echo"<table class=sortable border=0 cellspacing=1 cellpadding=1><thead><tr class=rowheader>";
	foreach($arrBln as $brs=>$dtBln){
		echo"<td align=center>".$dtBln."</td>";
	}
	echo"<td align=center>action</td></tr></thead>";
	echo"<tbody><tr class=rowcontent>";
	foreach($arrBln as $brs2 =>$dtBln2){
		echo"<td align=center><input type='text' id=jam_x".$brs2." class=\"myinputtextnumber\" style=\"width:50px;\" /></td>";
	}
	echo"<td align=center style='cursor:pointer;'><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"saveJam(".$tot.")\" src='images/save.png'/></td></tr></tbody></table>";
	
	echo"</fieldset></div>";


// $optThnBudget="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $optKdvhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $sThnBudget="select distinct tahunbudget from ".$dbname.".bgt_vhc_jam where  kodetraksi like '%".$_SESSION['empl']['lokasitugas']."%' ";
// $qThnBudget=$owlPDO->query($sThnBudget) or die(print " Gagal: ".PDOException::getMessage());
// $qThnBudget->setFetchMode(PDO::FETCH_ASSOC);
// while($rThnBudget=$qThnBudget->fetch()){
    // $optThnBudget.="<option value='".$rThnBudget['tahunbudget']."'>".$rThnBudget['tahunbudget']."</option>";
// }
// $sThnBudget2="select distinct unitalokasi from ".$dbname.".bgt_vhc_jam where  kodetraksi like '%".$_SESSION['empl']['lokasitugas']."%' ";
// $qThnBudget2=$owlPDO->query($sThnBudget2) or die(print " Gagal: ".PDOException::getMessage());
// $qThnBudget2->setFetchMode(PDO::FETCH_ASSOC);
// while($rThnBudget2=$qThnBudget2->fetch()){
    // $optUnit.="<option value='".$rThnBudget2['unitalokasi']."'>".$rThnBudget2['unitalokasi']."</option>";
// }
// $sThnBudget3="select distinct kodevhc from ".$dbname.".bgt_vhc_jam where  kodetraksi like '%".$_SESSION['empl']['lokasitugas']."%' ";
// $qThnBudget3=$owlPDO->query($sThnBudget3) or die(print " Gagal: ".PDOException::getMessage());
// $qThnBudget3->setFetchMode(PDO::FETCH_ASSOC);
// while($rThnBudget3=$qThnBudget3->fetch()){
    // $optKdvhc.="<option value='".$rThnBudget3['kodevhc']."'>".$rThnBudget3['kodevhc']."</option>";
// }

CLOSE_BOX();
echo"</div>";

OPEN_BOX();
echo"<div id=listdatadetail style=display:none>";
echo"<div id=contain class='table-scroll' style=height:60vh><script>loadDatadetail()</script></div>";
echo"</div>";


echo"<div id=listdata class='table-scroll' style=height:65vh>
		<table class='sortable' cellspacing=1 cellpadding=3 border=0>
		<thead>
			<tr class=rowheader>
			<th align=center width=30px>No.</th>
			<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
			<th align=center>".$_SESSION['lang']['kodeorg']."</th>
			<th align=center>".$_SESSION['lang']['traksi']."</th>";
		$bulan = range(1,12);
		foreach($bulan as $bln){
			echo"<th align=center>".numToMonth($bln,$lang='E',$format='short')."</th>";
		}
		echo"<th align=center>".$_SESSION['lang']['total']."</th>
			<th align=center colspan=5>Action</th>
			</tr>
		</thead>
		<tbody id=containdata><script>loaddata(0)</script></tbody>
		<tfoot id=footData></tfoot>
		</table>
</div>";
CLOSE_BOX();
echo close_body();
?>