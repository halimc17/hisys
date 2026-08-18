<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
require_once('lib/zPivot.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/kebun_2premipemanenv3.js?v=<?php echo time(); ?>'></script>
<link href="lib/select2/css/select2.css" rel="stylesheet" />
<script src="lib/select2/js/select2.js"></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

$optunit=$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunit2=$optafd2=$optprd2="<option value=''>".$_SESSION['lang']['all']."</option>";
$optafd="<option value='%%'>".$_SESSION['lang']['pilihdata']."</option>";


$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
	$optunit2.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunit2="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optunit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optunit2.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optunit.="<option value=".$key.">".$key." - ".$val."</option>";
	$optunit2.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
		$optunit2.="</optgroup>";
	}
}


$str="select distinct(substr(tanggal,1,7)) as periode from ".$dbname.".kebun_aktifitas order by periode desc limit 13";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optprd.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
	$no++;
	if($no=='1'){
		$optprd2.="<option value=".$bar['periode']." selected>".$bar['periode']."</option>";
	}else{		
		$optprd2.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
	}
}
$whr='';
if($_SESSION['empl']['subbagian']!=''){
	#$whr=" and kodeorganisasi='".$_SESSION['empl']['subbagian']."'";
}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' and tipe='AFDELING' ".$whr."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optafd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$optafd2.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

#$opttahap="<option value='0'>Sebulan</option>";
$opttahap="<option value='1'>Pertama</option>";
$opttahap.="<option value='2'>Kedua</option>";

$optbrd="<option value='1'>Kg Sebelum Potong Brondolan</option>";
$optbrd.="<option value='2'>Kg Setelah Potong Brondolan</option>";


$optpot="<option value='1'>Pot Kg dengan biaya brondolan</option>";
$optpot.="<option value='3'>Pot Kg dengan tanpa brondolan</option>";
$optpot.="<option value='2'>Pot Rp Denda</option>";


OPEN_BOX('','<span class=judul>'.getMenu('kebun_2premipemanenv3').'</span><br>');
$arrlist="##tgl1list##tgl2list##unitlist##afdlist";

$arr="##prd##unit##afd##tahap##tgl1##tgl2##kgbrondol##perpot";
echo"<fieldset style=float:left;><legend><b>Form</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td colspan=3><select class=select2 id=unit onchange=\"getDivisiX(this.value,'afd','".$_SESSION['lang']['pilihdata']."')\"; style='width:173px;'>".$optunit."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td colspan=3><select class=select2 id=afd  style='width:173px;'>".$optafd."</select></td>
    </tr>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td colspan=3><select class=select2 id=prd onchange=gettanggal(); style='width:173px;'>".$optprd."</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tahap']."</td>
		<td>:</td>
		<td colspan=3><select class=select2 id=tahap onchange=gettanggal(); style='width:173px;'>".$opttahap."</select>
		</td>
    </tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10' readonly></td><td>s/d</td><td>
			<input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10' readonly>
		</td>
    </tr>
	
	";
echo"<tr>
		<td></td>
		<td></td>
		<td colspan=6>
		<button onclick=viewdetail2() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=previewexceldetail2() class=mybutton name=excel id=excel>".$_SESSION['lang']['excel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();

$_SESSION['temppnn']=array();
//echo"<fieldset  style=min-height:400px ><legend><b>".$_SESSION['lang']['list']."</b></legend>
echo"<div class='table-scroll' id='printContainer'></div>";
//echo"</fieldset>";


CLOSE_BOX();
echo"</div>";
echo close_body();
?>