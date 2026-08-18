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
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/bgt_alokasi_supervisi.js?v=<?php echo time(); ?>"></script>
<script>
dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";
</script>
<?php

$optOrg2="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('AFDELING','BIBITAN') and induk='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc";
$qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
$qOrg2->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg2=$qOrg2->fetch()){
	$optOrg2.="<option value=".$rOrg2['kodeorganisasi'].">".$rOrg2['kodeorganisasi']." - ".$rOrg2['namaorganisasi']."</option>";
}

$arrjenis=array('BBT'=>'Bibitan',
				'TB'=>'TB / LC',
				'TBM'=>'Tanaman Belum Menghasilkan',
				'TM'=>'Tanaman Menghasilkan'
				);
$optjenis="<option value=''>".$_SESSION['lang']['all']."</option>";
$optjenis2="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrjenis as $key => $val){
	$optjenis.="<option value=".$key.">".$key." - ".$val."</option>";
	$optjenis2.="<option value=".$key.">".$key." - ".$val."</option>";
}
$optjenis2.="<option value='PNN'>PNN - Panen</option>";



$arrsebar=array('0'=>'Sebar ke akun kegiatan',
				'1'=>'Alokasikan ke akun khusus supervisi'
				);
foreach($arrsebar as $key => $val){
	@$optalk.="<option value=".$key.">".$val."</option>";
}


OPEN_BOX('','<span class=judul>'.strtoupper('BUDGET '.$_SESSION['lang']['alokasisupervisi']).'</span>');

$frm[0].= OPEN_THEME($_SESSION['lang']['keterangan'].":");
$frm[0].="<fieldset  style='text-align:left;'><legend><b><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>";
$frm[0].="<div align=justify>".$_SESSION['lang']['infoSupervisi']."</div>";
$frm[0].="</fieldset>";
$frm[0].= CLOSE_THEME();

$frm[1].="<fieldset style=float:left><legend>".$_SESSION['lang']['hksupervisi']."</legend>";
$frm[1].="<table cellspacing=1 border=0>

<tr>
	<td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
	<input type=text class=myinputtextnumber id=thnAnggran  name=thnAnggran maxlength=4 onkeypress=\"return angka_doang(event);\" style=width:150px; onblur=getHk() /></td>

	<td>".$_SESSION['lang']['kodebudget']."</td><td>:</td><td><input type=text disabled class=myinputtext  id=kdBudget name=kdBudget  onkeypress=\"return tanpa_kutip(event);\" style=width:150px; value='SUPERVISI' /></td>
</tr>	
<tr>
	<td>".$_SESSION['lang']['upahsupervisi']."</td><td>:</td><td><input type=text  class=myinputtextnumber  id=uphSupervisi name=uphSupervisi  onkeypress=\"return angka_doang(event);\" style=width:150px; onblur=kalikan() onkeyup=\"z.numberFormat('uphSupervisi',2)\"></td>

	<td>".$_SESSION['lang']['jmlhPersonel']."</td><td>:</td><td><input type=text  class=myinputtextnumber  id=jmlhPersonel name=jmlhPersonel  onkeypress=\"return angka_doang(event);\" style=width:150px;  onblur=kalikan() /></td>
</tr>

<tr>
	<td>".$_SESSION['lang']['hk']."</td><td>:</td><td><input type=text  class=myinputtextnumber  id=hkEfektif name=hkEfektif  onkeypress=\"return angka_doang(event);\" style=width:150px;  disabled></td>
	
	<td>".$_SESSION['lang']['totalUpahSpr']."</td><td>:</td><td><input type=text disabled class=myinputtextnumber  id=totUpah name=totUpah  onkeypress=\"return angka_doang(event);\" style=width:150px; /></td>
</tr>

<tr>
	<td>".$_SESSION['lang']['divisi']."</td><td>:</td><td><select id=divisi style=\"width:155px\">" . $optOrg2 . "</select></td>
	
	<td>".$_SESSION['lang']['jenis']."</td><td>:</td><td><select id=jenis style=\"width:155px\">" . $optjenis . "</select></td>
</tr>
<tr>
	<td>Akun Alokasi</td><td>:</td><td><select id=alokasi style=\"width:155px\">" . $optalk . "</select></td>
</tr>


<tr><td></td><td></td><td colspan=3>
<button class=mybutton id=save_kepalaBr name=save_kepalaBr onclick=tampilKan()>".$_SESSION['lang']['preview']."</button>
<button class=mybutton id=btlTmbl name=btlTmbl onclick=batalBr()  >".$_SESSION['lang']['cancel']."</button></td></tr></table>
";

$frm[1].="</fieldset>";
$frm[1].="<div style=clear:both></div>";
$frm[1].="<div id=listPrevData style='display:none;'><fieldset><legend>".$_SESSION['lang']['list']."</legend>";
$frm[1].="<button class=mybutton id=saveAwal name=saveAwal onclick=saveAll(1) >".$_SESSION['lang']['save']."</button>&nbsp;<button class=mybutton id=lnjutTmbl name=lnjutTmbl onclick=lanjutkan() style='display:none'>".$_SESSION['lang']['lanjut']."</button>";
$frm[1].="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td align=center rowspan=2>No</td>
            <td align=center rowspan=2 widht=50px>".$_SESSION['lang']['budgetyear']."</td>
            <td align=center rowspan=2>".$_SESSION['lang']['kodeorg']."</td>
            <td align=center rowspan=2>".$_SESSION['lang']['kegiatan']."</td>
            <td align=center rowspan=2>".$_SESSION['lang']['namakegiatan']."</td>
            <td align=center colspan=4>AlokasiBiaya Supervisi</td>
            <td style=display:none rowspan=2>".$_SESSION['lang']['volume']."</td>
            <td align=center rowspan=2>".$_SESSION['lang']['satuan']."</td>
            <td style=display:none rowspan=2>".$_SESSION['lang']['rotasi']."</td>
            <td align=center rowspan=2>".$_SESSION['lang']['jumlahhk']."</td>
            <td align=center rowspan=2>".$_SESSION['lang']['hksupervisi']."</td>
            <td align=center rowspan=2>".$_SESSION['lang']['supervisi']."</td>
            </tr>
            <tr>
			<td align=center>".$_SESSION['lang']['noakun']."</td>
			<td align=center>".$_SESSION['lang']['namaakun']."</td>
			<td align=center>".$_SESSION['lang']['kodekegiatan']."</td>
			<td align=center>".$_SESSION['lang']['namakegiatan']."</td>
            </tr>
			
            </thead><tbody id=containDetail>";
$frm[1].="</tbody></table></fieldset></div>";
$optThn="<option value=''>".$_SESSION['lang']['budgetyear']."</option>";
$frm[2].="<fieldset style='float:left;'><legend>".$_SESSION['lang']['sebaran']."</legend>";
$frm[2].="<table cellspacing=1 border=0>
<tr>
	<td>".$_SESSION['lang']['budgetyear']."</td><td>:</td>
	<td><input type=text class=myinputtextnumber id=thnBudget style='width:100px;' onkeypress='return angka_doang(event)' maxlength=4 /></td>
	
	<td>".$_SESSION['lang']['divisi']."</td><td>:</td><td><select id=divisisebar style=\"width:155px\">" . $optOrg2 . "</select></td>
	
	<td>".$_SESSION['lang']['jenis']."</td><td>:</td><td><select id=jenissebar style=\"width:155px\">" . $optjenis2 . "</select></td>

	<td><button class=mybutton onclick=prevSebaran() id=tmblPrev>".$_SESSION['lang']['preview']."</button></td>
	
</tr>
</table></fieldset><br />";

$frm[2].="<div id=contentSebaran style=width:100%; ></div>";
$frm[3]="<fieldset style='float:left;'><legend>".$_SESSION['lang']['delete']."</legend>";
$frm[3].="<table cellspacing=1 border=0>
<tr>
	<td>".$_SESSION['lang']['budgetyear']."</td><td>:</td>
	<td><input type=text class=myinputtextnumber id=thnBudgetUlg style='width:100px;' onkeypress='return angka_doang(event)' maxlength=4 /></td>
	
	<td>".$_SESSION['lang']['divisi']."</td><td>:</td><td><select id=divisidel style=\"width:155px\">" . $optOrg2 . "</select></td>
	
	<td>".$_SESSION['lang']['jenis']."</td><td>:</td><td><select id=jenisdel style=\"width:155px\">" . $optjenis2 . "</select></td>

	<td><button class=mybutton onclick=delAll()>".$_SESSION['lang']['delete']."</button></td>
	
</tr>
</table><br />";
$frm[3].="</fieldset>";
$frm[3].="<div id=contentSebaran></div>";

//========================
$hfrm[0]=$_SESSION['lang']['keterangan'];
$hfrm[1]=$_SESSION['lang']['hksupervisi'];
$hfrm[2]=$_SESSION['lang']['sebaran'];
$hfrm[3]=$_SESSION['lang']['delete'];
drawTab('FRM',$hfrm,$frm,150,'100%');
//===============================================	
CLOSE_BOX();
echo close_body();
?>