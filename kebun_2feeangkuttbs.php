<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript src='js/kebun_2feeangkuttbs.js?ver=1.0'></script>

<?php
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2feeangkuttbs').'</span>');

## FILTER SEARCH
## AKSES DETAIL

// $optkebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
// 	$where = "";
// } else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
//     $where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
// } else {
// 	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
// }
// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$s="";
// 	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
// 		$s="selected";
// 	}
//     $optkebun.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

$optkebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optkebun.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optkebun.="<option value=".$key.">".$key." - ".$val."</option>";
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

echo"<div>
	<fieldset style='float: left;'>
	<legend><b>Form</b></legend>
	<table cellspacing='1' border='0' >
		<tr>
			<td>".$_SESSION['lang']['kebun']."</td>
			<td>:</td>
			<td>
				<select id='kebun' style=width:173px>".$optkebun."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<!--<select id=periode>".$optperiode."</select>-->
				<input type=text class=myinputtext id=tgl1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('01-m-Y')."' readonly />
				<input type=text class=myinputtext id=tgl2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('d-m-Y')."' readonly />
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button onclick=\"getlaporan(event,'html')\" class='mybutton' name='preview' id='preview'>Preview</button>
				<button onclick=\"getlaporan(event,'pdf')\" class='mybutton' name='preview' id='preview'>PDF</button>
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

<div class='table-scroll'><div id='printContainer' style='height:450px;'>

</div></div></div>";
CLOSE_BOX();

echo close_body();
?>