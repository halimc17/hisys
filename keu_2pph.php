<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<span class=judul>'.getMenu('keu_2pph').'</span><br>');
?>
<script language=javascript src='js/keu_2pph.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?

$tglawal=date("01-m-Y");
$tglakhir=date("d-m-Y");

## GET LIST PT
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(3) as $key=>$val){
	if($_SESSION['empl']['kodeorganisasi']==$key){
		$optpt.="<option value='".$key."' selected>".$key." - ".$val."</option>";		
	}else{
		$optpt.="<option value='".$key."'>".$key." - ".$val."</option>";		
	}
}

## GET LIST UNIT
$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."'";
$res=fetchdata($str);
foreach($res as $val){
	$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

## GET LIST JENIS PPH
$optjenis="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select noakun,namaakun from ".$dbname.".keu_5akun where detail='1' and noakun like '213%' order by namaakun asc";
$res=fetchdata($str);
foreach($res as $val){
	$optjenis.="<option value='".$val['noakun']."'>".$val['noakun']." - ".$val['namaakun']."</option>";
}

echo"<fieldset style='min-width:100px;float:left'>
	<table border=0 cellpadding=1>
		<tr>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='pt' onchange='getunit()'>".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='unit'>".$optunit."</select>
			</td>
		</tr>
		<tr>
			<td>Periode</td>
			<td>:</td>
			<td>
				<input type=text id=tanggal class=myinputtext style='text-align:center' value='".$tglawal."' size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\" readonly> 
				s/d 
				<input type=text id=tanggal2 tabindex='3' class=myinputtext style='text-align:center;' value='".$tglakhir."' size=10 onmousemove=setCalendar(this.id) onkeypress=\"return false;\" readonly>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jenis']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='jenis'>".$optjenis."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button tabindex=111110 class=mybutton id=btnpreview style='height:25px' onclick='loaddata()'>Preview</button>
			</td>
		</tr>
	</table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id='output' style=min-height:400px></div>";
CLOSE_BOX();
echo close_body();
?>