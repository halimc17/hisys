<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript src='js/sdm_2perkomponengaji.js'></script>

<?php
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2perkomponengaji').'</span>');

## GET UNIT
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where = "";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
} else {
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc ";
$res=fetchdata($str);
foreach($res as $val){
	if($_SESSION['empl']['lokasitugas']==$val['kodeorganisasi']){
		$optunit.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	}else{
		$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	}
}

## GET DIVISION
$optdivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
$optdivisi.="<option value='office'>".$_SESSION['lang']['kantor']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc";
$res=fetchdata($str);
foreach($res as $val){
	$optdivisi.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
}

## GET SISTEM GAJI
$optsistemgaji.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optsistemgaji.="<option value='Bulanan'>".$_SESSION['lang']['bulanan']."</option>";
$optsistemgaji.="<option value='Harian'>".$_SESSION['lang']['harian']."</option>";

## GET KOMPONEN
$str="select * from ".$dbname.".sdm_ho_component order by name";
$res=fetchdata($str);
foreach($res as $val){
	if($val['id']=='64'){
		$otpkomponen.="<option value='".$val['id']."'>".$val['name']."</option>";			
	}
}

echo"<div>
	<fieldset style='float: left;'>
	<legend><b>Form</b></legend>
	<table cellspacing='1' border='0' >
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select id='unit'>".$optunit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['divisi']."</td>
			<td>:</td>
			<td>
				<select id='divisi'>".$optdivisi."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tgl1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".date('01-m-Y')."' readonly /> s/d 	
				<input type=text class=myinputtext id=tgl2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".date('d-m-Y')."' readonly />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['sistemgaji']."</td>
			<td>:</td>
			<td>
				<select id='sistemgaji'>".$optsistemgaji."</slect>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['namakomponen']."</td>
			<td>:</td>
			<td>
				<select id='idkomponen'>".$otpkomponen."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button onclick=\"getlaporan(event,'html')\" class='mybutton' name='preview' id='preview'>Preview</button>
				<!--<button onclick=\"getlaporan(event,'pdf')\" class='mybutton' name='preview' id='preview'>PDF</button>-->
				<button onclick=\"getlaporan(event,'excel')\" class='mybutton' name='preview' id='preview'>Excel</button>
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

<div id='printContainer' style='overflow:auto; height:390px; max-width:100%;'>

</div></div>";
CLOSE_BOX();

echo close_body();
?>