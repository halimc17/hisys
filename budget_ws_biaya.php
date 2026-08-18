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

?>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/budget_ws_biaya.js?v=<? echo time(); ?>"></script>
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

//pilihan kodebudget tab0
if($_SESSION['empl']['tipelokasitugas']=='PABRIK'){
	$where=" and kodebudget like '%EXPL%'";
}else{
	$where=" and kodebudget like '%SDM%'";
}

$str="select kodebudget,nama from ".$dbname.".bgt_kode
	where 1=1 ".$where."";
$optkodebudget0="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$optkodebudget0.="<option value='".$bar->kodebudget."'>".$bar->nama."</option>";
}

//pilihan kodebudget tab1
$str="select kodebudget,nama from ".$dbname.".bgt_kode
	where kodebudget like 'M%'";
$optmaterial1="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$optmaterial1.="<option value='".$bar->kodebudget."'>".$bar->kodebudget." - ".$bar->nama."</option>";
}
    
//pilihan kodebudget tab2    
$str="select kodebudget,nama from ".$dbname.".bgt_kode
	where kodebudget like 'TOOL%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$opttool2.="<option value='".$bar->kodebudget."'>".$bar->nama."</option>";
}
    
//pilihan kodebudget tab3    
$str="select kodebudget,nama from ".$dbname.".bgt_kode where kodebudget like 'TRANSIT%'";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
		$opttransit3.="<option value='".$bar->kodebudget."'>".$bar->nama."</option>";
}

//pilihan kodeakun tab3    
#and length(noakun)=7 and (substr(noakun,1,2) in ('71') or
$str="select noakun,namaakun from ".$dbname.".keu_5akun where detail=1 and tipeakun = 'Biaya' and substr(noakun,1,5) in ('41101') and noakun!='4110199' order by noakun";
$optakun3="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$optakun3.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";
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


$optwssch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(17) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		#$optwssch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optwssch.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		#$optwssch.="</optgroup>";
	}	
}


foreach(getOrgDetail(17) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		#$optwssch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optws.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		#$optwssch.="</optgroup>";
	}	
}
OPEN_BOX('','<span class=judul>'.getMenu('budget_ws_biaya').'</span>');
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
					<td><select class='select2'id=kodeorgsch onchange=loaddata(); style=\"width:150px;\">".$optorgsch."</select></td>
					
					<td>" . $_SESSION['lang']['workshop'] . "</td>
					<td>:</td>
					<td><select class='select2'id=kodewssch onchange=loaddata(0); style=\"width:150px;\">" . $optwssch . "</select></td>
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
echo"<fieldset style='float:left'><legend>".$_SESSION['lang']['form']."</legend><table cellspacing=1 border=0>
    <tr><td>".$_SESSION['lang']['tipeanggaran']." </td><td>:</td><td>
        <input type=text class=myinputtext id=tipebudget name=tipebudget onkeypress=\"return angka_doang(event);\" maxlength=2 disabled=true style=width:150px; value=\"WS\"/></td></tr>
    <tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
        <input type=text class=myinputtext id=tahunbudget name=tahunbudget onkeypress=\"return angka_doang(event);\" maxlength=4 style=width:150px; /></td></tr>
    <tr><td>".$_SESSION['lang']['workshop']."</td><td>:</td><td>
        <select class='select2'name=kodews id=kodews style='width:155px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optws."</select></td></tr>
    <tr><td></td><td></td><td colspan=3>
        <button class=mybutton id=simpan name=simpan onclick=prosesSimpan()>".$_SESSION['lang']['save']."</button>
        <button class=mybutton id=baru name=baru onclick=displayList()>".$_SESSION['lang']['done']."</button>
        <input type=hidden id=tersembunyi name=tersembunyi value=tersembunyi >
    </td></tr></table></fieldset><div style=clear:both></div>
	<input type=hidden id=index>
	<input type=hidden id=proses>
	";
CLOSE_BOX();	
echo"</div>";	
OPEN_BOX();	
echo"<div id=datainputdetail style=display:none>";
$frm[0].="<fieldset id=tab0 disabled=true ><legend>".$_SESSION['lang']['sdm']."</legend>";
$frm[0].="<table cellspacing=1 border=0>
    <tr><td>".$_SESSION['lang']['kodeanggaran']."</td><td>:</td><td>
        <select class='select2'id=kodebudget0 onchange=\"jumlahkan0();\" name=kodebudget0 style='width:155px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optkodebudget0."</select></td></tr>
    <tr><td>".$_SESSION['lang']['hkefektif']." </td><td>:</td><td>
        <input type=text class=myinputtextnumber id=hkefektif0 name=hkefektif0 onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:150px; disabled=true /></td></tr>
    <tr><td>Jlh TK Setahun</td><td>:</td><td>
        <input type=text class=myinputtextnumber onkeyup=\"jumlahkan0();\" id=jumlahpersonel0 name=jumlahpersonel0 onkeypress=\"return angka_doang(event);\" maxlength=8 style=width:150px; /></td></tr>
	<tr><td>Jlh HK Setahun</td><td>:</td><td>
        <input type=text class=myinputtextnumber disabled id=jlhhksdm onkeypress=\"return angka_doang(event);\" maxlength=8 style=width:150px; /></td></tr>
    <tr><td>".$_SESSION['lang']['totalbiaya']."</td><td>:</td><td>
        <input type=text class=myinputtextnumber id=totalbiaya0 name=totalbiaya0 onkeypress=\"return false;\" maxlength=15 style=width:150px; /></td></tr>
    <tr><td></td><td></td><td colspan=3>
        <button class=mybutton id=simpan0 name=simpan0 onclick=simpan0()>".$_SESSION['lang']['save']."</button>
        <input type=hidden id=tersembunyi0 name=tersembunyi0 value=tersembunyi >
    </td></tr></table>";
$frm[0].="</fieldset>";
//box dalam tab0, daftar table
#$frm[0].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
$frm[0].="<div style=clear:both></div><div id=container0 style=max-height:300px;overflow:auto;></div>";
#$frm[0].="</fieldset>";


//tab 1
$frm[1].="<fieldset id=tab1 disabled=true ><legend>".$_SESSION['lang']['material']."</legend>";
$frm[1].="<table cellspacing=1 border=0><thead>
    </thead>
    <tr><td>".$_SESSION['lang']['kodeanggaran']."</td><td>:</td><td>
        <select class='select2'id=kodebudget1 onchange=\"bersihkan(1);\" name=kodebudget1 style='width:155px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optmaterial1."</select></td></tr>
    <tr><td>".$_SESSION['lang']['kodebarang']."</td><td>:</td><td>
        <input type=text readonly class=myinputtext id=kodebarang1 name=kodebarang1 onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:150px; onclick=\"searchBrg(1,'".$_SESSION['lang']['findBrg']."','<center>Find : <input type=text class=myinputtext id=no_brg value=".@$kodebarang1."><button class=mybutton onclick=findBrg(1)>Find</button></center><div id=container></div><input type=hidden id=nomor name=nomor value=".@$key.">',event)\">
		
        <input type=\"image\" id=search1   class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;' title=".$_SESSION['lang']['find']." onclick=\"searchBrg(1,'".$_SESSION['lang']['findBrg']."','<center>Find : <input type=text class=myinputtext id=no_brg value=".@$kodebarang1."><button class=mybutton onclick=findBrg(1)>Find</button><center><div id=container></div><input type=hidden id=nomor name=nomor value=".@$key.">',event)\";>    
        <label id=namabarang1></label></td></tr>
	<tr><td>".$_SESSION['lang']['harga']." ".$_SESSION['lang']['satuan']."</td><td>:</td><td>
		<input type=text class=myinputtextnumber id=hargasatuan1 name=hargasatuan1 onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:150px; disabled=true/>
		<label id=hargasatuan1></td></tr>
    <tr><td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['setahun']."</td><td>:</td><td>
        <input type=text class=myinputtextnumber onkeyup=\"jumlahkan1();\" id=jumlah1 name=jumlah1 onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:150px; disabled=true/>
        <label id=satuan1></td></tr>
    <tr><td>".$_SESSION['lang']['totalharga']."</td><td>:</td><td>
        <input type=text  class=myinputtextnumber id=totalharga1 name=totalharga1 onkeypress=\"return false;\" maxlength=10 style=width:150px; /></td></tr>
    <tr><td></td><td></td><td colspan=3>
        <button class=mybutton id=simpan1 name=simpan1 onclick=simpan1()>".$_SESSION['lang']['save']."</button>
		<button class=mybutton onclick='showupload()'   >".$_SESSION['lang']['upload']."</button>
        <input type=hidden id=regional1 name=regional1 value=>
    </td></tr></table>";
$frm[1].="</fieldset>";
//box dalam tab1, daftar table
// $frm[1].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
$frm[1].="<div style=clear:both></div><div id=container1 style=max-height:300px;overflow:auto;></div>    ";
// $frm[1].="</fieldset>";

//tab2
$frm[2]="<fieldset id=tab2 disabled=true><legend>".$_SESSION['lang']['peralatan']."</legend>";
$frm[2].="<table cellspacing=1 border=0><thead>
    </thead>
    <tr><td>".$_SESSION['lang']['kodeanggaran']."</td><td>:</td><td>
        <select class='select2'id=kodebudget2 onchange=\"bersihkan(2);\" name=kodebudget2 style='width:155px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$opttool2."</select></td></tr>
    <tr><td>".$_SESSION['lang']['kodebarang']."</td><td>:</td><td>
        <input type=text class=myinputtext readonly id=kodebarang2 name=kodebarang2 onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:150px; disabled=true onclick=\"searchBrg(2,'".$_SESSION['lang']['findBrg']."','<center>Find : <input type=text class=myinputtext id=no_brg2 value=".@$kodebarang2."><button class=mybutton onclick=findBrg(2)> Find</button></center><div id=containerx></div><input type=hidden id=nomor name=nomor value=".@$key.">',event)\">
        <input type=\"image\" id=search2 class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;' title=".$_SESSION['lang']['find']." onclick=\"searchBrg(2,'".$_SESSION['lang']['findBrg']."','<center>Find : <input type=text class=myinputtext id=no_brg2 value=".@$kodebarang2."><button class=mybutton onclick=findBrg(2)> Find</button></center><div id=containerx></div><input type=hidden id=nomor name=nomor value=".@$key.">',event)\";>    
        <label id=namabarang2></label></td></tr>
	<tr><td>".$_SESSION['lang']['harga']." ".$_SESSION['lang']['satuan']."</td><td>:</td><td>
		<input type=text class=myinputtextnumber id=hargasatuan2 name=hargasatuan2 onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:150px; disabled=true/>
		<label id=hargasatuan2></td></tr>
    <tr><td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['setahun']."</td><td>:</td><td>
        <input type=text class=myinputtextnumber onkeyup=\"jumlahkan2();\" id=jumlah2 name=jumlah2 onkeypress=\"return angka_doang(event);\" maxlength=10 style=width:150px; disabled=true/>
         <label id=satuan2></td></tr>
    <tr><td>".$_SESSION['lang']['totalharga']."</td><td>:</td><td>
        <input type=text  class=myinputtextnumber id=totalharga2 name=totalharga2 onkeypress=\"return false;\" maxlength=10 style=width:150px; /></td></tr>
    <tr><td></td><td></td><td colspan=3>
        <button class=mybutton id=simpan2 name=simpan2 onclick=simpan2()>".$_SESSION['lang']['save']."</button>
		<button class=mybutton onclick=\"showupload('TOOL')\">".$_SESSION['lang']['upload']."</button>
        <input type=hidden id=regional2 name=regional2 value=>
    </td></tr></table>";
$frm[2].="</fieldset>";
//box dalam tab2, daftar table
// $frm[2].="<fieldset  style=float:left><legend>".$_SESSION['lang']['list']."</legend>";
$frm[2].="<div style=clear:both></div><div id=container2 style=max-height:300px;overflow:auto;></div>";
// $frm[2].="</fieldset>";

//tab3
$frm[3]="<fieldset id=tab3 disabled=true ><legend>".$_SESSION['lang']['lain']."</legend>";
$frm[3].="<table cellspacing=1 border=0><thead>
    </thead>
    <tr><td>".$_SESSION['lang']['kodeanggaran']."</td><td>:</td><td>
        <select class='select2'id=kodebudget3 name=kodebudget3 style='width:155px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$opttransit3."</select></td></tr>
    <tr><td>".$_SESSION['lang']['noakun']."</td><td>:</td><td>
        <select class='select2'id=kodeakun3 name=kodeakun3 style='width:155px;'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optakun3."</select> <img id='kodeakun3' onclick=z.elSearch('kodeakun3',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td></tr>
    <tr><td>".$_SESSION['lang']['totalbiaya']." ".$_SESSION['lang']['setahun']."</td><td>:</td><td>
        <input type=text class=myinputtextnumber id=totalbiaya3 name=totalbiaya3 onkeypress=\"return angka_doang(event);\" maxlength=15 style=width:150px; /></td></tr>
    <tr><td></td><td></td><td colspan=3>
        <button class=mybutton id=simpan3 name=simpan3 onclick=simpan3()>".$_SESSION['lang']['save']."</button>
        <input type=hidden id=regional3 name=regional3 value=>
    </td></tr></table>";
$frm[3].="</fieldset>";
//box dalam tab3, daftar table
// $frm[3].="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
$frm[3].="<div style=clear:both></div><div id=container3 style=max-height:300px;overflow:auto;></div>";
// $frm[3].="</fieldset>";

/* 
$frm[4]="<fieldset id=tab4 disabled=true style=float:left><legend>".$_SESSION['lang']['close']."</legend>";
$frm[4].="<table cellspacing=1 border=0><thead>
    </thead>
    <tr><td>
        <button class=mybutton id=display4 name=display4 onclick=persiapantutup4()>".$_SESSION['lang']['preview']."</button>
    </td><td>
        <button class=mybutton id=tutup4 name=tutup4 onclick=tutup4(1) disabled=true>".$_SESSION['lang']['close']."</button>
        <input type=hidden id=hidden4 name=hidden4 value=>
    </td><td>Sebelum menutup budget workshop pastikan semua nilai sudah sesuai, karena jika sudah di tutup tidak bisa dilakukan edit, click tombol Preview sebelum meng-click tombol tutup</td></tr></table>";
$frm[4].="</fieldset><div style=clear:both></div>";
$frm[4].="<div id=container4></div>    ";
$frm[4].="";
 */

$hfrm[0]=$_SESSION['lang']['sdm'];
$hfrm[1]=$_SESSION['lang']['material'];
$hfrm[2]=$_SESSION['lang']['peralatan'];
$hfrm[3]=$_SESSION['lang']['lain'];
//$hfrm[4]=$_SESSION['lang']['close'];
drawTab('FRM',$hfrm,$frm,150,'100%');


echo"</div>";

echo"<div id=listdata class='table-scroll' style=height:65vh>";
	echo"
		<table class='sortable' cellspacing=1 cellpadding=5 border=0>
		<thead>
			<tr class=rowheader>
			<th align=center width=30px>No.</th>
			<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
			<th align=center>".$_SESSION['lang']['tipe']."</th>
			<th align=center>".$_SESSION['lang']['kodeorg']."</th>
			<th align=center>".$_SESSION['lang']['workshop']."</th>
			<th align=center width=75px>".$_SESSION['lang']['totJamBengkel']."</th>
			<th align=center>".$_SESSION['lang']['sdm']."</th>
			<th align=center>".$_SESSION['lang']['material']."</th>
			<th align=center>".$_SESSION['lang']['peralatan']."</th>
			<th align=center>".$_SESSION['lang']['lain']."</th>
			<th align=center>".$_SESSION['lang']['total']."</th>
			<th align=center width=75px>Rupiah / Jam</th>
			<th align=center colspan=4>Action</th>
			</tr>
		</thead>
		<tbody id=contain><script>loaddata(0)</script></tbody>
		<tfoot id=footData></tfoot>
		</table>";
echo"</div>";
CLOSE_BOX();
echo close_body();
?>