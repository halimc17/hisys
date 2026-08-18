<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/budget_upahx.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>

<?php
/* 
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where = "";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
} else {
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgx="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by induk asc ";
$res=fetchdata($str);
foreach($res as $bar){
	$d=$bar['induk'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorgx.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	
	$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$optorgx.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
		$optorgx.="</optgroup>";
	}
} */

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgx="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorgx.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$optorgx.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
		$optorgx.="</optgroup>";
	}
}

$optgol="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$where="";
/* if($_SESSION['empl']['tipelokasitugas']=='PABRIK'){	
	$where="and kodebudget like 'EXPL%'";
}else{
} */
$where="and (kodebudget like 'SDM%' or kodebudget like 'EXPL%')";

$str="select * from ".$dbname.".bgt_kode where 1=1 ".$where." order by kodebudget asc ";
$res=fetchdata($str);
foreach($res as $bar){
	$optgol.="<option value=".$bar['kodebudget'].">".$bar['nama']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('budget_upahx').'</span>');
echo"<div id=action_list>";
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
		<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
		<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
			<table>
				<tr>
					<td>" . $_SESSION['lang']['tahun'] . "</td>
					<td>:</td>
					<td><input style=\"width:146px;\" maxlength='4' type=text id=tahunsch class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress='return angka_doang(event)'/></td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>:</td>
					<td><select class='select2' id=kodeorgsch onchange=loaddata(0); style=\"width:150px;\">" . $optorgx . "</select></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td>
				</tr>
			</table>
		</fieldset>
     </tr></table>";

CLOSE_BOX();
echo "</div>";

echo"<div id=listData style=display:block>";
OPEN_BOX();
echo "
	<table cellpadding=5 cellspacing=1 border=0 class=sortable>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
			<th align=center>" . $_SESSION['lang']['tahun'] . "</th>
			<th align=center>" . $_SESSION['lang']['namaorganisasi'] . "</th>
			<th align=center>" . $_SESSION['lang']['updateby'] . "</th>
			<th align=center colspan='4'>" . $_SESSION['lang']['action'] . "</th>
		</tr>
	</thead>
	
	<tbody id=contain><script>loaddata(0)</script></tbody>
	<tfoot id=footData></tfoot>
	</table>
";
CLOSE_BOX();
echo "</div>";


echo "<div id=header style=display:none>";
OPEN_BOX();
echo "<fieldset style=float:left>
	<legend>Input</legend>
	<table cellspacing=1 border=0>
		<tr>
			<td>" . $_SESSION['lang']['tahun'] . "</td>
			<td>:</td>
			<td><input style=\"width:145px;\" maxlength='4' type=text id=tahun class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress='return angka_doang(event)'/></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
			<td>:</td>
			<td><select class='select2' style=\"width:150px;\" id=kodeorg onchange=getbgtkode();>" . $optorg . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tipe'] . " / " . $_SESSION['lang']['kodegolongan'] . "</td>
			<td>:</td>
			<td><select class='select2' style=\"width:150px;\" id=golongan>" . $optgol . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['nilai'] . " (Rp)</td>
			<td>:</td>
			<td><input style=\"width:145px;\" type=text id=nilai class=myinputtextnumber onblur=\"z.numberFormat('nilai',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress='return angka_doang(event)'/></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button id=tomboldetail class=mybutton onclick=insertdetail()>" . $_SESSION['lang']['save'] . "</button>
				<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
			<input type=hidden id=method value='insert'>
		</tr>
	</table>
	</fieldset>";
CLOSE_BOX();
echo"</div>";

echo"<div id=contdetail style=display:none>";
OPEN_BOX();
echo"<div id=detail></div>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>