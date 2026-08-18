<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
$frm[0]='';
$frm[1]='';
$frm[2]='';

?>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/budget_5blok.js?v='<? echo time(); ?>"></script>
<script>dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";</script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
	
	$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});

</script>
<?php
#kode org
$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorgsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorgsch.="<option value=".$key.">".$key." - ".$val."</option>";
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorgsch.="</optgroup>";
		$optorg.="</optgroup>";
	}	
}
#Divisi
$optdivisi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optdivsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(19) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optdivisi.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optdivsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optdivisi.="<option value=".$key.">".$key." - ".$val."</option>";
	$optdivsch.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optdivisi.="</optgroup>";
		$optdivsch.="</optgroup>";
	}	
}


$optData="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct tahunbudget from ".$dbname.".bgt_blok order by tahunbudget desc";
$res = fetchdata($str);
foreach($res as $rThn){
    $optData.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('budget_5blok').'</span>');
#form pencarian
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
					<td><select class=select2 id=thnbgtsch style='width:150px' onchange='loadDataLama()'>".$optData."</select></td>
					
					<td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>:</td>
					<td><select class=select2 id=kodeorgsch onblur=loadDataLama(); onchange=getdivisi('header'); style=\"width:150px;\">".$optorgsch."</select></td>
				
					<td>" . $_SESSION['lang']['divisi'] . "</td>
					<td>:</td>
					<td><select class=select2 id=divisisch onchange=loadDataLama(0); style=\"width:150px;\">" . $optdivsch . "</select></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton id=tombolcari onclick=loadDataLama(0)>" . $_SESSION['lang']['find'] . "</button>
					<!--<button class=mybutton id=tombolbatalcari onclick=batalcari()>" . $_SESSION['lang']['cancel'] . "</button>--></td>
				</tr>
			</table>
		</fieldset>
     </tr></table>";
echo "</div>";
CLOSE_BOX();
OPEN_BOX();
echo"<div  id=contlistdata>
	<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
		<thead>
            <tr class=rowheader>
            <th align=center width=30px>No</th>
            <th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
            <th align=center>".$_SESSION['lang']['kebun']."</th>
            <th align=center>".$_SESSION['lang']['divisi']."</th>
            <th align=center>".$_SESSION['lang']['hathnini']."</th>
            <th align=center>".$_SESSION['lang']['pokokthnini']."</th>
            <th align=center>".$_SESSION['lang']['lcthnini']."</th>
            <th align=center>".$_SESSION['lang']['hanonproduktif']."</th>
            <th align=center>".$_SESSION['lang']['pkkproduktif']."</th>
            <th align=center>".$_SESSION['lang']['status']."</th>
            <th align=center colspan=5>Action</th>
            </tr>
		</thead><tbody id=containData><script>loadDataLama()</script></tbody>
		<tfoot id=footData></tfoot>
	</table>
	</div>";

echo"<div id=formjudulIsian style=display:none>";

$jenis=array('lama'=>$_SESSION['lang']['bloklm'],'baru'=>$_SESSION['lang']['blokbr']);
foreach($jenis as $res => $bar){
    @$optjns.="<option value='".$res."'>".$bar."</option>";
}

echo"<fieldset style=float:left>
			<legend>".$_SESSION['lang']['form']."</legend>
				<table cellspacing=1 border=0>
					<tr>
						<td>".$_SESSION['lang']['budgetyear']."</td>
						<td>:</td>
						<td><input type=text class=myinputtextnumber id=thnAnggran name=thnAnggran maxlength=4 onkeypress=\"return angka_doang(event);\" style=width:145px; /></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['jenis']."</td>
						<td>:</td>
						<td><select id=jenis name=jenis style=width:150px;>".$optjns."</select></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['kebun']."</td>
						<td>:</td>
						<td><select class=select2 id=kodeorg name=kodeorg onchange=getdivisi('trans'); style=width:150px;>".$optorg."</select></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['divisi']."</td>
						<td>:</td>
						<td><select class=select2 id=idAfd name=idAfd style=width:150px;>".$optdivisi."</select></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td colspan=3>
							<button class=mybutton id=save_kepala name=save_kepala onclick=tampilkan()>Preview</button>
							<button class=mybutton id=btlTmbl name=btlTmbl onclick=batal()  >".$_SESSION['lang']['cancel']."</button></td>
					</tr>
				</table>
		</fieldset>
";

echo"</div>";
CLOSE_BOX();
echo"<div id=formIsian style=display:none>";
OPEN_BOX();

#isi input preview blok baru
echo"<div id=formbloklama>";
echo"
	
	<table cellspacing=1 border=0>
		<tr>
			<td><h3>Info :</h3></td>
		</tr>
		<tr>
			<td><h4>Ha Non Produktif = Luas Cadangan + Umum + Kolam + Jalan + Pabrik + Kantor + Rumah + Sungai + Rendahan + Okupasi + LC</h4></td>
		</tr>
	</table>
	<table cellpadding=5 cellspacing=1 border=0 class=sortable>
		<thead>
		<tr class=rowheader>
		<th align=center width=30px>No</th>
		<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
		<th align=center>".$_SESSION['lang']['blok']."</th>
		<th align=center>".$_SESSION['lang']['topografi']."</th>
		<th align=center>".$_SESSION['lang']['thntnm']."</th>
		<th align=center>".$_SESSION['lang']['hathnlalu']."</th>
		<th align=center width=70px>".$_SESSION['lang']['hathnini']."</th>
		<th align=center width=70px>".$_SESSION['lang']['pokokthnlalu']."</th>
		<th align=center width=70px>".$_SESSION['lang']['pokokthnini']."</th>
		<th align=center width=70px>".$_SESSION['lang']['statusblok']."</th>
		<th align=center width=70px>".$_SESSION['lang']['lcthnini']." (Ha)</th>
		<th align=center width=70px>".$_SESSION['lang']['hanonproduktif']."</th>
		<th align=center width=70px>".$_SESSION['lang']['pkkproduktif']."</th>
		<th align=center width=40px>Plasma</th>
		</tr>
		</thead><tbody id=isiContainer></tbody>
	</table>


	";
echo"</div>";
echo"<div id=formblokbaru style=display:none>";
echo"<div id=dataListBr>
	<fieldset>
		<legend>".$_SESSION['lang']['form']."</legend>
			<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <th align=center>No</th>
            <th align=center width=50px>".$_SESSION['lang']['tahun']."</th>
            <th align=center width=50px>".$_SESSION['lang']['kebun']."</th>
            <th align=center width=50px>".$_SESSION['lang']['afdeling']."</th>
            <th align=center width=50px>".$_SESSION['lang']['blok']."</th>
            <th align=center width=50px>".$_SESSION['lang']['hathnini']."</th>
            <th align=center width=50px>".$_SESSION['lang']['pokokthnini']."</th>
            <th align=center width=50px>".$_SESSION['lang']['statusblok']."</th>
            <th align=center width=50px>".$_SESSION['lang']['topografi']."</th>
            <th align=center width=50px>".$_SESSION['lang']['thntnm']."</th>
            <th align=center width=50px>".$_SESSION['lang']['lcthnini']."</th>
            <th align=center width=50px>".$_SESSION['lang']['hanonproduktif']."</th>
            <th align=center width=50px>".$_SESSION['lang']['pkkproduktif']."</th>
            <th align=center>Plasma</th>
            <th align=center>Action</th>
            </tr>
            </thead><tbody id=isiContainerBr></tbody>
		</table>
	</fieldset>
	<input type=hidden id=prosesBr name=prosesBr value=insert_baru >";

echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <th align=center width=30px>No</th>
            <th align=center>".$_SESSION['lang']['tahun']."</th>
            <th align=center>".$_SESSION['lang']['blok']."</th>
            <th align=center>".$_SESSION['lang']['hathnini']."</th>
            <th align=center>".$_SESSION['lang']['pokokthnini']."</th>
            <th align=center>".$_SESSION['lang']['statusblok']."</th>
            <th align=center>".$_SESSION['lang']['topografi']."</th>
            <th align=center>".$_SESSION['lang']['thntnm']."</th>
            <th align=center>".$_SESSION['lang']['lcthnini']."</th>
            <th align=center>".$_SESSION['lang']['hanonproduktif']."</th>
            <th align=center>".$_SESSION['lang']['pkkproduktif']."</th>
            <th align=center colspan=2>Action</th>
            </tr>
            </thead><tbody id=containDetail>
		</tbody>
	</table>
</fieldset>
</div>
";
echo"</div>";


// $optThn="<option value=''>".$_SESSION['lang']['budgetyear']."</option>";
// $frm[2].="<fieldset><legend>".$_SESSION['lang']['blokcls']."</legend>
			// <table cellspacing=1 border=0>
				// <tr>
					// <td>".$_SESSION['lang']['ttpBudget']."</td>
					// <td>:</td>
					// <td><select id=thnBudget style='width:100px;'>".$optThn."</select></td>
				// </tr>
				// <tr>
					// <td colspan=3>
						// <button class=mybutton onclick=prosesClose() >".$_SESSION['lang']['proses']."</button>
						// <input type=hidden name=prosesOpt id=prosesOpt value=insert_operator />
					// </td>
				// </tr>
			// </table>";
// $frm[2].="</fieldset>";

// $hfrm[0]=$_SESSION['lang']['bloklm'];
// $hfrm[1]=$_SESSION['lang']['blokbr'];
// $hfrm[2]=$_SESSION['lang']['close'];
// drawTab('FRM',$hfrm,$frm,150,'100%');

echo"</div>";
CLOSE_BOX();
echo close_body();
?>