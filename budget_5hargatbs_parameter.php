<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/budget_5hargatbs_parameter.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>

<?php
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgx="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".organisasi where tipe = 'KEBUN' order by induk asc ";
$res=fetchdata($str);

foreach($res as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$val['kodeorganisasi']."'");
	$d=$induk[$val['kodeorganisasi']];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorgx.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	$optorgx.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
		$optorgx.="</optgroup>";
	}
}

$optorg.="<optgroup label='EXTM - EXTERNAL / SWADAYA'>";
$optorgx.="<optgroup label='EXTM - EXTERNAL / SWADAYA'>";
$optorg.="<option value=EXTM>EXTM - EXTERNAL / SWADAYA</option>";
$optorgx.="<option value=EXTM>EXTM - EXTERNAL / SWADAYA</option>";
$optorg.="</optgroup>";
$optorgx.="</optgroup>";

$where="";
$optgol="<option value=''>&nbsp;</option>";
$str="select distinct tahuntanam from ".$dbname.".setup_blok where 1=1 ".$where." order by tahuntanam asc ";
$res=fetchdata($str);
foreach($res as $bar){
	$optgol.="<option value=".$bar['tahuntanam'].">".$bar['tahuntanam']."</option>";
}

$optbulan=$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrbulan = array(
	"01" => "Januari",
	"02" => "Februari",
	"03" => "Maret",
	"04" => "April",
	"05" => "Mei",
	"06" => "Juni",
	"07" => "Juli",
	"08" => "Agustus",
	"09" => "September",
	"10" => "Oktober",
	"11" => "November",
	"12" => "Desember",
);

foreach($arrbulan as $keybulan => $valbulan) {
	$optbulan.="<option value='".$keybulan."'>".$valbulan."</option>";
}

$arrJenis=array("moderat"=>"Moderat", "optimis"=>"Optimis", "pesimis"=>"Pesimis");
foreach($arrJenis as $key => $val) {
	$optjenis .= "<option value='".$key."'>".$val."</option>";
}

$optTipe="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrTipe=getEnum($dbname,'bgt_hargatbs','kodebarang');
# Hidden sementara
unset($arrTipe['CPO']);
unset($arrTipe['KER']);
# End Hide
foreach($arrTipe as $kei=>$fal){
	$optTipe.="<option hidden value='".$kei."'>".$fal."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('budget_5hargatbs_parameter').'</span>');
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
			<th align=center>" . $_SESSION['lang']['namabarang'] . "</th>
			<th align=center>" . $_SESSION['lang']['pabrik'] . "</th>
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
			<td><font style=font-size:10px;color:blue;>(1)</font>&nbsp;:</td>
			<td><input style=\"width:145px;\" maxlength='4' type=text id=tahun class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress='return angka_doang(event)'/></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['bulan'] . "</td>
			<td><font style=font-size:10px;color:blue;>(2)</font>&nbsp;:</td>
			<td><select class='select2' style=\"width:150px;\" id=bulan>" . $optbulan . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['jenis'] . "</td>
            <td><font style=font-size:10px;color:blue;>(3)</font>&nbsp;:</td>
            <td><select class=select2 id=jenisbudget style=\"width:150px;\">".$optjenis."</select>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['namabarang'] . "</td>
			<td><font style=font-size:10px;color:blue;>(4)</font>&nbsp;:</td>
			<td><select class='select2' onchange=getbarang(this.value); style=\"width:150px;\" id=namabarang>" . $optTipe . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['sumber'] . "</td>
			<td><font style=font-size:10px;color:blue;>(5)</font>&nbsp;:</td>
			<td><select class='select2' style=\"width:150px;\" id=kodeorg onchange=getbgtkode();>" . $optorg . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tahuntanam'] . "</td>
			<td><font style=font-size:10px;color:blue;>(6)</font>&nbsp;:</td>
			<td><select class='select2' style=\"width:150px;\" id=tahuntanam>" . $optgol . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['harga'] . " (Rp)</td>
			<td><font style=font-size:10px;color:blue;>(7)</font>&nbsp;:</td>
			<td><input style=\"width:145px;\" type=text id=nilai class=myinputtextnumber onblur=\"z.numberFormat('nilai',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress='return angka_doang(event)'/></td>
		</tr>
		<tr>
			<td colspan=2><input style=display:none id=pabrik value=".$_SESSION['empl']['lokasitugas']."></td>
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

#============================================#
# DETAIL
#============================================#
// $bulan = 12;
// echo "<div id=detail style=display:none>";
// OPEN_BOX();
// echo "<fieldset style=float:left>
// 	<legend>Input</legend>
// 	<table cellspacing=1 border=0>
// 		<tr>
// 			<td>" . $_SESSION['lang']['tahun'] . "</td>
// 			<td>:</td>
// 			<td><input style=\"width:145px;\" maxlength='4' type=text id=tahun class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress='return angka_doang(event)'/></td>
// 		</tr>
// 		<tr>
// 			<td colspan=2><input style=display:none id=pabrik value=".$_SESSION['empl']['lokasitugas']."></td>
// 			<td>
// 				<button id=tomboldetail class=mybutton onclick=insertdetail()>" . $_SESSION['lang']['save'] . "</button>
// 				<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
// 			</td>
// 			<input type=hidden id=method value='insert'>
// 		</tr>
// 	</table>
// 	</fieldset>";
// CLOSE_BOX();
// echo"</div>";



echo"<div id=contdetail style=display:none>";
OPEN_BOX();
echo"<div id=detail></div>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>