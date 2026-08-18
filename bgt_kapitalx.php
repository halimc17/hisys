<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/bgt_kapitalx.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<style>
	.freezetbl {
		position: relative;
		max-height: 350px;
	}
	.freezetbl thead {
	  position: -webkit-sticky;
	  position: sticky;
	  top: 0;
	  z-index: 2;
	}

	.freezetblload {
		position: relative;
		//max-height: 550px;
	}
	.freezetblload thead {
	  position: -webkit-sticky;
	  position: sticky;
	  top: 0;
	  z-index: 2;
	}

	.detailfix {
		position: relative;
		max-height: 550px;
	}
	.detailfix thead {
	  position: -webkit-sticky;
	  position: sticky;
	  top: 0;
	  z-index: 1;
	}

	.select {
		color: red !important;
	}

	.unselect {
		color: black !important;
	}
</style>

<?php
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(1) as $key => $val){
	//if($key==$_SESSION['empl']['lokasitugas']){		
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
		$d=$induk[$key];
		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			$optorgsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		}
		$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
		$optorgsch.="<option value=".$key.">".$key." - ".$val."</option>";
		$n=$d;
		if($d!=$n){			
			$optorg.="</optgroup>";
			$optorgsch.="</optgroup>";
		}
	//}
}

$optthnpost="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct tahunbudget from ".$dbname.".bgt_kapital order by tahunbudget desc";
$res = fetchdata($str);
$tahuncsh = $res[0]['tahunbudget'];
foreach($res as $bar){
    @$optthn.="<option value='".$bar['tahunbudget']."'>".$bar['tahunbudget']."</option>";
    $optthnpost.="<option value='".$bar['tahunbudget']."'>".$bar['tahunbudget']."</option>";
}

$optgol="<option value=''>".$_SESSION['lang']['all']."</option>";
$optJns="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".sdm_5tipeasset order by namatipe  asc";
$res = fetchdata($str);
foreach($res as $bar){
    $optgol.="<option value='".$bar['kodetipe']."'>".$bar['namatipe']."</option>";
    $optJns.="<option value='".$bar['kodetipe']."'>".$bar['namatipe']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('bgt_kapitalx').'</span>');
echo"<div id=action_list>";
echo"<table border=0>
     <tr valign=middle>	 
		<td align=center style='width:75px;cursor:pointer;' onclick=add_new_data()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:75px;cursor:pointer;' onclick=displayList()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
		</td>
		<td align=center style='width:75px;cursor:pointer;' onclick=add_sebaran()>
			<img class=delliconBig src=images/archive.png title='".$_SESSION['lang']['posting']."'><br>".$_SESSION['lang']['posting']."
		</td>
		<td valign=middle>
			<div id=formcari>
				<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
					<table>
						<tr>
							<td>" . $_SESSION['lang']['budgetyear'] . "</td>
							<td>:</td>
							<td><input id=tahunsch class=myinputtextnumber style=\"width:75px;\" value=".$tahuncsh."></td>
							
							<td>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td>:</td>
							<td><select class=select2 id=kodeorgsch onchange=loaddata(0); style=\"width:150px;\">".$optorgsch."</select></td>
							
							<td>" . $_SESSION['lang']['jnsKapital'] . "</td>
							<td>:</td>
							<td><select class=select2 id=sebaransch onchange=loaddata(0); style=\"width:150px;\">" . $optgol . "</select></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td colspan=3>
								<button class=mybutton id=btnprev onclick=loaddata(0)>" . $_SESSION['lang']['preview'] . "</button>
								<button class=mybutton id=btnexcel onclick=loadexcel(0)>" . $_SESSION['lang']['excel'] . "</button>
								<button class=mybutton id=btncari onclick=batalcari()>" . $_SESSION['lang']['cancel'] . "</button>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			
			<div id=formcariposting style=display:none>
				<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
					<table>
						<tr>
							<td>" . $_SESSION['lang']['budgetyear'] . "</td>
							<td>:</td>
							<td><select class=select2 id=tahunpostsch onchange=showposting(0); style=\"width:150px;\">" . $optthnpost . "</select></td>
							
							<td>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td>:</td>
							<td><select class=select2 id=kodeorgpostsch onchange=showposting(0); style=\"width:150px;\">".$optorgsch."</select></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td colspan=3>
								<button class=mybutton onclick=showposting(0)>" . $_SESSION['lang']['preview'] . "</button>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			
		</td>
		</tr></table>";

echo "</div>";
CLOSE_BOX();

$optlokasi="<option value=''></option>";
$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)>=4 order by induk";
$res = fetchdata($str);
foreach($res as $val){
	$d=$val['induk'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optlokasi.="<optgroup label='".$nmorg[$d]."'>";
	}
	$optlokasi.="<option value=".$val['kodeorganisasi'].">".$val['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optlokasi.="</optgroup>";
	}
}


echo"<div id=inputdata style=display:none>";
OPEN_BOX();
echo"
	<fieldset style=float:left><legend>" . $_SESSION['lang']['form'] . "</legend>
		<table border=0>
			<tr>
				<td>".$_SESSION['lang']['budgetyear']."</td>
				<td><input type='text' class='myinputtextnumber' id='tahunbudget' style='width:195px;' maxlength='4' onkeypress='return angka_doang(event)' /></td>
				
				<td>".$_SESSION['lang']['unit']."</td>
				<td><select class=select2 style='width:200px;' id='kodeorg' onchange=getlokasi(); >".$optorg."</select></td>
				
				<td>".$_SESSION['lang']['jnsKapital']."</td>
				<td><select class=select2 style='width:200px;' onchange=getaruskas('jeniskapital','aruskas'); id='jeniskapital'>".$optJns."</select></td>
				
			</tr>
			<tr>
				<td>".$_SESSION['lang']['lokasi']."</td>
				<td><select class=select2 style='width:200px;' id=lokasi>".$optlokasi."</select>
				</td>

				<td>".$_SESSION['lang']['aruskas']."</td>
				<td><select class=select2 id='aruskas' style='width:200px;'></select></td>
				
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td><select class=select2 style='width:200px;' onchange=gethargabarang(this.value); id=kodebarang></select>
					<input hidden id=flagbarang value=''>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['jumlah']."</td>
				<td><input type='text' class='myinputtextnumber' id='jumlah' style='width:195px;'  onkeypress='return angka_doang(event)' onkeyup='kaliKan()' /></td>
				
				<td>".$_SESSION['lang']['hargasatuan']."</td>
				<td><input type='text' class='myinputtextnumber' id='harga' onkeyup=\"z.numberFormat('harga',2)\" style='width:195px;' onkeypress='return angka_doang(event)' onblur='kaliKan()' /></td>
				
				<td>".$_SESSION['lang']['total']."</td>
				<td><input type='text' class='myinputtextnumber' disabled id='totalrp' onkeyup=\"z.numberFormat('totalrp',2)\" style='width:195px;' readonly/></td>
			</tr>
			<tr>
				<td valign=top>".$_SESSION['lang']['keterangan']."</td>
				<td colspan=15>
					<textarea rows=3 maxlength=124 id=keterangan type=text onkeypress='return tanpa_kutip(event)' style='width:745px;'></textarea>
				</td>
			</tr>
			<tr hidden>
				<td><input hidden id=idbgt><input hidden id=method value='simpan'></td>
			</tr>
			<tr>
				<td></td>
				<td colspan='2'><button class=\"mybutton\"  id=\"saveData\" onclick='saveHeader()'>".$_SESSION['lang']['save']."</button></td>
			</tr>
		</table>
	</fieldset>
	";
CLOSE_BOX();
echo "</div>";

$bulan=range(1,12);
OPEN_BOX();
#cont posting
echo"
	<div class='table-scroll' id=contposting style=display:none;>
	<table class='sortable' cellspacing=1 cellpadding=5 border=0>
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center width=30px>No.</th>
			<th align=center style='width:50px'>".$_SESSION['lang']['budgetyear']."</th>
			<th align=center>".$_SESSION['lang']['kodeorg']."</th>
			<th align=center>".$_SESSION['lang']['jnsKapital']."</th>
			<th align=center>".$_SESSION['lang']['total']."</th>
			";
			foreach($bulan as $bln){				
				echo"<th align=center>".numToMonth($bln,'E','short')."</th>";
			}
		echo"<th width=30px align=center>Action</th>
		</tr>
	</thead>
	<tbody id=contpostingdata></tbody>
	</table></div>";
//echo"</div>";

#list data
//echo"<div >";
echo"<div  class=freezetblload id=listData style=display:block>
	<table class='sortable' cellspacing=1 cellpadding=5 border=0 >
	<thead>
		<tr class=rowheader style=height:25px>
			<th align=center width=30px>No.</th>
			<th align=center style='width:50px'>".$_SESSION['lang']['budgetyear']."</th>
			<th align=center>".$_SESSION['lang']['kodeorg']."</th>
			<th align=center>".$_SESSION['lang']['lokasi']."</th>
			<th align=center>".$_SESSION['lang']['jnsKapital']."</th>
			<th align=center>".$_SESSION['lang']['kodebarang']."</th>
			<th align=center>".$_SESSION['lang']['namabarang']."</th>
			<th align=center>".$_SESSION['lang']['keterangan']."</th>
			<th align=center>".$_SESSION['lang']['aruskas']."</th>
			<th align=center>".$_SESSION['lang']['namaaruskas']."</th>
			<th align=center>".$_SESSION['lang']['jumlah']."</th>
			<th align=center>".$_SESSION['lang']['rpsat']."</th>
			<th align=center>".$_SESSION['lang']['total']."</th>";
		echo"<th align=center colspan=3>Action</th>
		</tr>
	</thead>
	
	<tbody id=contain><script>loaddata(0)</script></tbody>
	<tfoot id=footData></tfoot>
	</table>";
	
echo "</div>";
//echo "</div>";

CLOSE_BOX();
echo close_body();
?>