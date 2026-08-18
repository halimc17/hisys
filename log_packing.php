<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include_once('lib/zLib.php');
?>

<script language=javascript1.2 src='js/log_packing.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
## GET UNIT
$optunit='';
$arrorgdet = getOrgDetail(1);
$no=0;
$optunit2="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrorgdet as $key=>$val){
	$subro = substr($key,2,2);
	if($subro=='RO'){
		$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";
		$optunit2.="<option value='".$key."'>".$key." - ".$val."</option>";
		if($no==0){
			$loktugas.= "'".$key."'";
		}else{
			$loktugas.= ",'".$key."'";
		}
		$no++;
	}
}

##GET KARYAWAN YG MENYERAHKAN
$optKar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan  where lokasitugas in (".$loktugas.")";
$res=fetchdata($str);
foreach($res as $val){
	$optKar.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']."</option>";
}

## PERIODE FOR SEARCHING 
$optPer="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct substr(tanggal,1,7) as periode from ".$dbname.".log_packinght order by periode desc limit 10";
$res=fetchdata($str);
foreach($res as $val){
	$optPer.="<option value='".$val['periode']."'>".$val['periode']."</option>";
}

$frm[0]='';
$frm[1]='';


#### BEGIN FORM INPUT ####
OPEN_BOX('','<span class=judul>'.strtoupper('PACKING LIST').'</span>');
$frm[0].="<fieldset id=header style=width:450px>
	<legend><b>".$_SESSION['lang']['header']."</b></legend>
	<table border=0 cellpadding=1 cellspacing=1>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td> 
            <td>:</td>
            <td>
				<input type=text id=notran onkeypress=\"return tanpa_kutip(event);\" class=myinputtext disabled style=\"width:150px;\"> <font color='red'>* otomatis</font>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td> 
            <td>:</td>
            <td>
				<select id=\"unit\">".$optunit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td> 
            <td>:</td>
            <td>
				<input type=text class=myinputtext readonly  id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:80px;text-align:center\" value='".date('d-m-Y')."'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['ukuranpeti']."</td> 
            <td>:</td>
            <td>
				<input type=text maxlength=20 id=peti  onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:150px;\">
			</td>
		</tr>
		<tr>
			<td>No. Koli</td> 
            <td>:</td>
            <td>
				<input type=text  id=ket onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:150px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['menyerahkan']."</td> 
            <td>:</td>
            <td>
				<select id=serah style=\"width:155px;\">".$optKar."</select>
				<img id='serah' onclick=z.elSearch('serah',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodeorgpenerima']."</td> 
            <td>:</td>
            <td>
				<input type=text  id=terima onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:150px;\">
			</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td colspan=2>
				<button class=mybutton onclick=saveHeader()>".$_SESSION['lang']['save']."</button>
                <button class=mybutton  onclick=cancel()>".$_SESSION['lang']['baru']."</button>	
			</td>
		</tr>			
	</table>
</fieldset><input type=hidden id=method value='insert'>";	

$tmbl="<tr>
	<td>".$_SESSION['lang']['find']." ".$_SESSION['lang']['nopo']." : 
    <img src=images/zoom.png title='".$_SESSION['lang']['find']."'  class=resicon onclick=cariNoPo('".$_SESSION['lang']['find']."',event)>
    </td>
</tr>
<tr style='display:none'>
	<td>".$_SESSION['lang']['find']." ".$_SESSION['lang']['kodebarang']." : 
    <img src=images/zoom.png title='".$_SESSION['lang']['find']."'  class=resicon onclick=inputBarang('".$_SESSION['lang']['find']."',event)>
    </td>
</tr>";

$frm[0].="<div id=detailForm  style='display:none'>";
$frm[0].="<fieldset style=float:left>";
$frm[0].="<legend ><b>".$_SESSION['lang']['detail']."</b></legend>";
$frm[0].="<table border=0 cellpadding=1 cellspacing=1>";
$frm[0].=$tmbl;	
$frm[0].="</table>";
$frm[0].="<div id=containList  style='display:none;'><script>loadDataDetail()</script></div></fieldset>";	


#### BEGIN LIST DATA ####
$frm[1].="<fieldset style=float:left>
	<legend>".$_SESSION['lang']['find']."</legend>
	".$_SESSION['lang']['unit']." : <select id=srcunit style=\"width:100px;\" onchange=loadData(0)>".$optunit2."</select>
    ".$_SESSION['lang']['periode']." : <select id=srcperiode style=\"width:100px;\" onchange=loadData(0)>".$optPer."</select>		
    ".$_SESSION['lang']['notransaksi']." : <input type=text style=width:100px class=myinputtext id=srcnotrans onkeypress='return tanpa_kutip(event)' onkeyup=loadData(0) />
    No. PR : <input type=text style=width:100px class=myinputtext id=srcnopr onkeypress='return tanpa_kutip(event)' onkeyup=loadData(0) />
    No. PO : <input type=text style=width:100px class=myinputtext id=srcnopo onkeypress='return tanpa_kutip(event)' onkeyup=loadData(0) />
	
	<button class=mybutton title='Cari' onclick=\"loadData(0)\">Cari</button>
</fieldset>
<div style=display:both></div>
<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id=container>
		<script>loadData(0)</script>
	</div>
</fieldset>";

$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['list'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,250,'100%');		

CLOSE_BOX();
echo close_body();			
?>