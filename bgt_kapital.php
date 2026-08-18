<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';
$frm[2]='';

?>
<script>
pilh=" <? echo $_SESSION['lang']['pilihdata'] ?>";
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script type="text/javascript" src="js/bgt_kapital.js?v=<?php echo time(); ?>"></script>
<script>
dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";
</script>
<?php
$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where = "";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $where = " and right(kodeorganisasi,2)='RO'";
} else {
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}

$sOrg2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 1=1 ".$where." and length(kodeorganisasi)='4' order by namaorganisasi asc";
$qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
$qOrg2->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg2=$qOrg2->fetch())
{
	$optBlok.="<option value=".$rOrg2['kodeorganisasi'].">".$rOrg2['kodeorganisasi']." - ".$rOrg2['namaorganisasi']."</option>";
}
$optJns="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['language']=='EN'){
    $dd='namatipe1 as namatipe';
}else{
    $dd='namatipe as namatipe';
}
$sJns="select kodetipe,".$dd." from ".$dbname.".sdm_5tipeasset order by namatipe";
$qJns=$owlPDO->query($sJns) or die(print " Gagal: ".PDOException::getMessage());
$qJns->setFetchMode(PDO::FETCH_ASSOC);
while($rJns=$qJns->fetch()){
    $optJns.="<option value='".$rJns['kodetipe']."'>".$rJns['namatipe']."</option>";
}
$optlokasi="<option value=''></option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optlokasi.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['anggaran']." ". $_SESSION['lang']['kapital']).'</span>');

echo"<br /><fieldset><legend>".$_SESSION['lang']['entryForm']."</legend> <table border=0 cellpadding=1 cellspacing=1>";
echo"<tr><td>".$_SESSION['lang']['budgetyear']."</td><td><input type='text' class='myinputtextnumber' id='tahunbudget' style='width:145px;' maxlength='4' onkeypress='return angka_doang(event)' /></td>";
echo"<td>".$_SESSION['lang']['unit']."</td><td><select style='width:150px;' id='kodeorg' onchange=getlokasi(); >".$optBlok."</select></td>";
echo"<td>".$_SESSION['lang']['jnsKapital']."</td><td><select style='width:150px;' onchange=getaruskas('jeniskapital','aruskas'); id='jeniskapital'>".$optJns."</select></td>";
echo"</tr><tr><td>".$_SESSION['lang']['lokasi']."</td><td><select style='width:150px;' id=lokasi>".$optlokasi."</select>
<img id='lokasi' onclick=z.elSearch('lokasi',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>


</td>";

echo"
		<td>".$_SESSION['lang']['aruskas']."</td>
		<td><select id='aruskas' style='width:150px;'></select></td></tr><tr>
		<td>".$_SESSION['lang']['jumlah']."</td><td><input type='text' class='myinputtextnumber' id='jumlah' style='width:145px;'  onkeypress='return angka_doang(event)' onblur='kaliKan()' /></td>
	";

echo"<td>".$_SESSION['lang']['hargasatuan']."</td><td><input type='text' class='myinputtextnumber' id='harga' onkeyup=\"z.numberFormat('harga',2)\" style='width:145px;' onkeypress='return angka_doang(event)' onblur='kaliKan()' /></td>";
echo"<td>".$_SESSION['lang']['total']."</td><td><input type='text' class='myinputtextnumber' id='totalrp' onkeyup=\"z.numberFormat('totalrp',2)\" style='width:145px;' readonly/></td></tr>";
echo"<tr><td valign=top>".$_SESSION['lang']['keterangan']."</td><td colspan=15>
<textarea rows=3 maxlength=124 id=keterangan type=text onkeypress='return tanpa_kutip(event)' style='width:590px;'></textarea>
</td></tr>";

echo"<input hidden id=idbgt>";
echo"<input hidden id=method value='simpanHeader'>";
echo"<tr><td></td><td colspan='2'><button class=\"mybutton\"  id=\"saveData\" onclick='saveHeader()'>".$_SESSION['lang']['save']."</button></td></tr>";
echo"</table></fieldset>";

CLOSE_BOX();

echo"<div id='formIsian' style='display:block;'>";
OPEN_BOX();
$frm[0].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend>
                    <div id=container> 
                            <script>loadData()</script>
                    </div>
            </fieldset>";
		
$frm[1].="<fieldset><legend>".$_SESSION['lang']['sebaran']."</legend>
    <div id='detailDataSebaran'>";
$frm[1].="</div></fieldset>";
$str="select distinct tahunbudget from ".$dbname.".bgt_kapital where kodeunit='".$_SESSION['empl']['lokasitugas']."'  and tutup=0 order by tahunbudget desc";
$optThnTtp="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optThnTtp.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
}

$frm[2].="<fieldset><legend>".$_SESSION['lang']['tutup']."</legend>
    <div><table><tr><td>".$_SESSION['lang']['budgetyear']."</td><td><select id='thnBudgetTutup' onchange=rekap() style='width:150px'>".$optThnTtp."</select></td></tr>";
$frm[2].="<tr><td colspan=2 align=center><button class=\"mybutton\"  id=\"saveData\" onclick='closeBudget()'>".$_SESSION['lang']['tutup']."</button></td></tr></table>";
$frm[2].="</div></fieldset>";

$frm[2].="<fieldset><legend>".$_SESSION['lang']['rekap']."</legend>
		<table class=sortable cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
				<td align=center rowspan=2>No</td>
				<td align=center rowspan=2 width=50px>".$_SESSION['lang']['budgetyear']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['unit']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['jnsKapital']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
			</tr>
		</thead>
		<tbody id=rekapkapital>
		</tbody>
";
$frm[2].="</table></fieldset>";

//========================
$hfrm[0]=$_SESSION['lang']['list'];
$hfrm[1]=$_SESSION['lang']['sebaran'];
$hfrm[2]=$_SESSION['lang']['tutup'];
drawTab('FRM',$hfrm,$frm,150,'100%');
//===============================================	

CLOSE_BOX();
echo"</div>";
echo close_body();
?>