<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<script language=javascript src='js/kebun_2byrangkuttbs.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2byrangkuttbs').'</span>');

## FILTER SEARCH
## AKSES DETAIL


$optkebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrunit=array();
$arrunit=getOrgDetail(23);
foreach($arrunit as $val=>$nama){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$val."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$val."'");
	$d=$induk[$val];
	if($d!=$n){			
		$optkebun.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optkebun.="<option value='".$val."' ".$s.">".$val." - ".$nama."</option>";
	$n=$d;
	if($d!=$n){			
		$optkebun.="</optgroup>";
	}
} 

## GET PERIODE
$str="select distinct(substr(tanggal,1,7)) as periode from ".$dbname.".kebun_spbht order by periode desc limit 12";
$res=fetchdata($str);
foreach($res as $val){
    $optperiode.="<option value=".$val['periode'].">".$val['periode']."</option>"; 
}


$res=array('0'=>'Sebulan (Tanggal : 1 s/d 30)','1'=>'Pertama (Tanggal : 1 s/d 15)','2'=>'Kedua (Tanggal : 16 s/d 30)');
$optbyr="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach($res as $key => $val){
	$optbyr.="<option value=".$key.">".$val."</option>";
}


echo"<div>
	<fieldset style='float: left;'>
	<legend><b>Form</b></legend>
	<table cellspacing='1' border='0' >
		<tr>
			<td>".$_SESSION['lang']['kebun']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='kebun'  style=width:173px>".$optkebun."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class=select2 id=periode  style=width:173px>".$optperiode."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']." ".$_SESSION['lang']['pembayaran']."</td>
			<td>:</td>
			<td>
				<select class=select2 id=periodebayar  style=width:173px>".$optbyr."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button onclick=\"getlaporan(event,'html')\" class='mybutton' name='preview' id='preview'>Preview</button>
			</td>
		</tr>
	</table>
	</fieldset>
</div>";

CLOSE_BOX();

OPEN_BOX();
echo"<div id='both_report'>
    <div id='head_tableboth' align=right>
        <a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
            <img title='Full Screen' class='resicon' src='images/full-screen.png'>
        </a>
        <a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
            <img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
        </a>
    </div>
<div id='printContainer' style='overflow:auto; height:450px;width:100%;'>

</div></div>";
CLOSE_BOX();

echo close_body();
?>