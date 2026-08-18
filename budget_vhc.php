<?
#ini_set('display_errors',0);error_reporting(0);
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
<script>pilh=" <? echo $_SESSION['lang']['pilihdata'] ?>";</script>
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
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/budget_vhc.js?v='<? echo time(); ?>"></script>
<script>dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";</script>
<?php

$optVhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".vhc_5master";
$res = fetchdata($str);
foreach($res as $rVhc){
	if($rVhc['nopol']!=''){
		$rVhc['nopol']=" - ".$rVhc['nopol'];
	}
	if($rVhc['detailvhc']!=''){
		$rVhc['detailvhc']=" - ".$rVhc['detailvhc'];
	}
    $optVhc.="<option value='".$rVhc['kodevhc']."'>".$rVhc['kodevhc']."".$rVhc['nopol']."".$rVhc['detailvhc']."</option>";
}

$optData="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct tahunbudget from ".$dbname.".bgt_budget where tipebudget='TRK'";
$res = fetchdata($str);
foreach($res as $rThn){
    $optData.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
}

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

$optKdtraksisch="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTraksi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optTraksi.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optKdtraksisch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	
	$optTraksi.="<option value=".$key.">".$key." - ".$val."</option>";
	$optKdtraksisch.="<option value=".$key.">".$key." - ".$val."</option>";
	
	$n=$d;
	if($d!=$n){			
		$optTraksi.="</optgroup>";
		$optKdtraksisch.="</optgroup>";
	}
}


OPEN_BOX('','<span class=judul>'.getMenu('budget_vhc').'</span>');
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
					<td><select class=select2 id=thnBudgetHead style='width:150px' onchange='loaddata()'>".$optData."</select></td>
					
					<td>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td>:</td>
					<td><select class=select2 id=kodeorgsch onchange=loaddata(); style=\"width:150px;\">".$optorgsch."</select></td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['traksi'] . "</td>
					<td>:</td>
					<td><select class=select2 id=kodewssch onchange=loaddata(0); style=\"width:150px;\">" . $optKdtraksisch . "</select></td>
					
					<td>" . $_SESSION['lang']['kodevhc'] . "</td>
					<td>:</td>
					<td><input type=text class=myinputtext id=kdVhcHead name=kdVhcHead  style=width:145px; onkeypress='enterkey(event,loaddata)' /></td>
					
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
OPEN_BOX();



#listdata
echo"<div id='listDatHeader' style='display:block' class='table-scroll' style=height:70vh>";
echo"<table cellspacing=1 cellpadding=5 class=sortable border=0 width=100%>
		<thead>
			<tr class=rowheader>
			<th align=center width=30px>No.</th>
			<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
			<th align=center>".$_SESSION['lang']['tipe']."</th>
			<th align=center>".$_SESSION['lang']['kodeorg']."</th>
			<th align=center>".$_SESSION['lang']['kodevhc']."</th>
			<th align=center>".$_SESSION['lang']['nopol']."</th>
			<th align=center>Total Jam</th>
			<th align=center>".$_SESSION['lang']['sdm']."</th>
			<th align=center>".$_SESSION['lang']['material']."</th>
			<th align=center>".$_SESSION['lang']['service']."</th>
			<th align=center>".$_SESSION['lang']['biayalain']."</th>

			<th align=center>".$_SESSION['lang']['totalbiaya']."</th>
			<th align=center>".$_SESSION['lang']['rpperjam']."</th>
			<th align=center colspan=5>Action</th>
			</tr>
		</thead>
		<tbody id=listDatHeader2><script>loaddata(0)</script></tbody>
		<tfoot id=footData></tfoot>
	</table>
	</div>
	";
// echo"</div>";
#tutup list data


echo"<div id='formIsian' style='display:none;'>";
echo"<fieldset style='float:left;'><legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1>
		<tr style=display:none>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td><input type='text' class='myinputtext' disabled value='TRK' id='tipeBudget' style=width:50px; /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['budgetyear']."</td>
			<td>:</td>
			<td><input type='text' class='myinputtextnumber' id='thnBudget' style='width:170px;' maxlength='4' onkeypress='return angka_doang(event)' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodetraksi']."</td>
			<td>:</td>
			<td><select class=select2 style='width:175px;' id='kdTraksi' onchange=getKdvhc(this.value,'');>".$optTraksi."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodevhc']."</td>
			<td>:</td>
			<td><select class=select2 style='width:175px;' id='kodeVhc'>".$optVhc."</select></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td colspan='8'>
				<button class=\"mybutton\"  id=\"saveData\" onclick='saveData()'>".$_SESSION['lang']['save']."</button>
				<button  class=\"mybutton\"  id=\"newData\" onclick='newData()'>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table></fieldset>
	<input hidden id=index>
	<input hidden id=proses>
	";

echo"</div>";
CLOSE_BOX();

echo"<div id='detailformIsian' style='display:none;'>";
OPEN_BOX();

#========= SDM ========= 
$optKdbdgt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$frm[0].="<fieldset><legend>".$_SESSION['lang']['sdm']."</legend>";
$frm[0].="<table cellspacing=1 border=0>
		<tr>
			<td>".$_SESSION['lang']['jenis']."</td>
			<td>:</td>
			<td><select id='kdBudget' style='width:155px;' onchange='jumlahkan(1)'>".$optKdbdgt."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['hkefektif']."</td>
			<td>:</td>
			<td><input type='text' class='myinputtextnumber' disabled style='width:150px;' id='hkEfektif' /></td>
		</tr>
		<tr>
			<td>Jlh TK ".$_SESSION['lang']['setahun']."</td>
			<td>:</td>
			<td><input type='text' class='myinputtextnumber' style='width:150px;' id='jmlh_1' onkeyup='jumlahkan(1)' onkeypress='return angka_doang(event)' /></td>
		</tr>
		<tr>
			<td>Jlh HK ".$_SESSION['lang']['setahun']."</td>
			<td>:</td>
			<td><input type='text' class='myinputtextnumber' style='width:150px;' id='jmlhk_1' disabled onkeypress='return angka_doang(event)' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['totalbiaya']." (Rp)</td>
			<td>:</td>
			<td><input type='text' class='myinputtextnumber'  style='width:150px;' id='totBiaya' value='0' onkeypress='return false' /></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td colspan=3><button class=mybutton id=btlTmbl name=btlTmbl onclick=saveBudget(1)  >".$_SESSION['lang']['save']."</button></td>
		</tr>
	</table>";
	
$frm[0].="</fieldset>";

$frm[0].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<div id=containDataSDM></div>
	</fieldset>";

#========= MATERIAL ========= 
$optKdbdgtM="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodebudget,nama from ".$dbname.".bgt_kode where substr(kodebudget,1,1)='M' order by kodebudget asc";
$res = fetchdata($str);
foreach($res as $bar){
    $optKdbdgtM.="<option value='".$bar['kodebudget']."'>".$bar['kodebudget']." - ".$bar['nama']."</option>";
}

$frm[1].="<fieldset><legend>".$_SESSION['lang']['material']."</legend>";
$frm[1].="<table cellspacing=1 border=0>
		<tr>
			<td>".$_SESSION['lang']['kelompokbarang']."</td>
			<td>:</td>
			<td><select class=select2 id='kdBudgetM' style='width:153px;' onchange='getKlmpkbrg()'>".$optKdbdgtM."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodebarang']."</td>
			<td>:</td>
			<td><input type='text' readonly class='myinputtext' id='kdBarang' style='width:150px;' onkeypress='return angka_doang(event)' >
			
			<img class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;' title='".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."' onclick=\"searchBrg('".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."','<center>".$_SESSION['lang']['find']." : <input type=text style=width:250px class=myinputtext id=nmBrg><button class=mybutton onclick=findBrg()>".$_SESSION['lang']['find']."</button></center><div id=containerBarang style=overflow=auto;></div>',event);\">
			<span id='namaBrg'></span></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['harga']."  ".$_SESSION['lang']['satuan']."</td>
			<td>:</td>
			<td><input type='text' class='myinputtextnumber' id='hargasatuan_2' style='width:150px;' onkeypress='return angka_doang(event)' disabled />&nbsp;<span id='hargasatuan_2'></span></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jumlah']."  ".$_SESSION['lang']['setahun']."</td>
			<td>:</td>
			<td><input type='text' class='myinputtextnumber' id='jmlh_2' style='width:150px;' onkeypress='return angka_doang(event)' onkeyup='jumlahkan(2)' />&nbsp;<span id='satuan'></span></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['totalbiaya']." (Rp)</td>
			<td>:</td>
			<td><input type='text' class='myinputtextnumber' id='totHarga' style='width:150px;' onkeypress='return false'  value='0' /></td>
		</tr>        
		<tr>
			<td></td>
			<td></td>
			<td colspan=3>
				<button class=mybutton id=btlTmbl2 name=btlTmbl2 onclick='saveBudget(2)'   >".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick='showupload()'   >".$_SESSION['lang']['upload']."</button>
			</td>
		</tr>
	</table>	
	<input type=hidden id=prosesBr name=prosesBr value=insert_baru >";
	
	
$frm[1].="</fieldset>";
$frm[1].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<div id=containDataBrg class='table-scroll' style=height:60vh></div>
	</fieldset>
	";

#========= SERVICE ========= 
if(getNamaOrg($_SESSION['empl']['lokasitugas'],'tipe')=='BULKING'){
	$wh=" and kodebudget like '%SERVICEBULK%'";
}else{
	$wh=" and kodebudget like '%SERVICE%' and kodebudget not like '%SERVICEBULK%'";
}

$str="select kodebudget,nama from ".$dbname.".bgt_kode where 1=1 ".$wh." order by nama asc";
$res=fetchdata($str);
foreach($res as $rOrgs){
    $optKdbdgt_S.="<option value='".$rOrgs['kodebudget']."'>".$rOrgs['nama']."</option>";
}

$frm[2].="<fieldset><legend>".$_SESSION['lang']['service']."</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['kodeanggaran']."</td>
				<td>:</td>
				<td><select id='kdBudgetS' style='width:155px;'>".$optKdbdgt_S."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kdWorks']."</td>
				<td>:</td>
				<td><select id='kdWorkshop' style='width:155px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['jam']." ".$_SESSION['lang']['setahun']."</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' id='jmlh_3' style='width:150px;' onkeypress='return angka_doang(event)' onkeyup='jumlahkan(3)' /></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['totalbiaya']." (Rp)</td>
				<td>:</td>
				<td><input  type='text' class='myinputtextnumber' id='totHargaJam' style='width:150px;' onkeypress='return false'  value='0' /></td>
			</tr>        
			<tr>
				<td></td>
				<td></td>
				<td colspan=3>
					<button class=mybutton onclick=saveBudget(3)>".$_SESSION['lang']['save']."</button>
					<input type=hidden name=prosesOpt id=prosesOpt value=insert_operator />
				</td>
			</tr>
	</table>
</fieldset>";

$frm[2].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<div id=containDataSrvc ></div>
	</fieldset>";

#========= OTHER ========= 		
#$optKdbdgt_B="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodebudget,nama from ".$dbname.".bgt_kode where kodebudget like '%TRANSIT%' order by nama asc";
$res = fetchdata($str);
foreach($res as $rOrgB){
    $optKdbdgt_B.="<option value='".$rOrgB['kodebudget']."'>".$rOrgB['nama']."</option>";
}

$optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
#and (substr(noakun,1,2) in ('71') or substr(noakun,1,5) in ('41101','41102'))
$str="select noakun,namaakun from ".$dbname.".keu_5akun where detail=1 and tipeakun='BIAYA' and substr(noakun,1,5) in ('41102') and noakun!='4110299' order by noakun asc";
$res = fetchdata($str);
foreach($res as $rJns){
    $optAkun.="<option value='".$rJns['noakun']."'>".$rJns['noakun']." - ".$rJns['namaakun']."</option>";
}

$optbarang="<option value=''></option>";

$frm[3]="<fieldset><legend>".$_SESSION['lang']['biayalain']."</legend>
		<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['kodeanggaran']."</td>
				<td>:</td>
				<td><select id='kdBudgetB' style='width:150px;'>".$optKdbdgt_B."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['jenisbiaya']."</td>
				<td>:</td>
				<td><select id='noAkun' style='width:150px;'>".$optAkun."</select> <img id='noAkun' onclick=z.elSearch('noAkun',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td>:</td>
				<td><select id='kodebaranglain' style='width:150px;' onchange=gethargabaranglain();>".$optbarang."</select> <img id='kodebaranglain' onclick=z.elSearch('kodebaranglain',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kuantitas']."</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' onkeyup=gethargabaranglain(); id='kuantitas' style='width:145px;' onkeypress='return angka_doang(event)' disabled></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['totalbiaya']." ".$_SESSION['lang']['setahun']."</td>
				<td>:</td>
				<td><input type='text' class='myinputtextnumber' id='totBiayaB' style='width:145px;' onkeypress='return angka_doang(event)' value='0' /></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td colspan=3>
					<button class=mybutton onclick=saveBudget(4) >".$_SESSION['lang']['save']."</button>
					<input type=hidden name=prosesOpt id=prosesOpt value=insert_operator />
				</td>
			</tr>
		</table>
	</fieldset>";
	
$frm[3].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>
	<div id=containDataLain></div>
</fieldset>";

$optThnTtp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
/* 
$frm[4]="<fieldset><legend>".$_SESSION['lang']['tutup']."</legend>
    <div><table><tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td><select id='thnBudgetTutup' style='width:150px'>".$optThnTtp."</select></td>";
$frm[4].="<td colspan=2 align=center><button class=\"mybutton\"  id=\"saveData\" onclick='closeBudget()'>".$_SESSION['lang']['tutup']."</button></td></tr></table>";
$frm[4].="</div></fieldset>";
 */
$frm[4].="<div id='containerttlbiaya' class='table-scroll' style=height:60vh></div>";

$hfrm[0]=$_SESSION['lang']['sdm'];
$hfrm[1]=$_SESSION['lang']['material'];
$hfrm[2]=$_SESSION['lang']['service'];
$hfrm[3]=$_SESSION['lang']['biayalain'];
$hfrm[4]=$_SESSION['lang']['rekap'];
drawTab('FRM',$hfrm,$frm,150,'100%');

CLOSE_BOX();
echo"</div>";
echo close_body();
?>