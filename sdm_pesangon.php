<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include('lib/zLib.php');
echo open_body();
// include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/sdm_pesangon.js?v=<?php echo time(); ?>"'></script>

<?php
$arrUnit = getOrgDetail(2);

$valduabulan=date('Y-m',strtotime('-2 month',strtotime(date('Y-m-01'))));

$sKary="select * from ".$dbname.".datakaryawan
        where tipekaryawan in(1,2,3,4,5,6) and lokasitugas in (".$arrUnit.") and (tanggalkeluar = '0000-00-00' or tanggalkeluar >= '".$valduabulan."-01') order by lokasitugas,nik,namakaryawan";
$qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
$qKary->setFetchMode(PDO::FETCH_ASSOC);
$optKary = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optpihakpertama = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$n = '';

while($rKary = $qKary->fetch()){
	$lok = $rKary['lokasitugas'];
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='".$lok."'");
	$d = isset($induk[$lok]) ? $induk[$lok] : '';

	if($d != $n){
		if($n != ''){
			$optKary .= "</optgroup>";
			$optpihakpertama .= "</optgroup>";
		}
		$optKary .= "<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		$optpihakpertama .= "<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		$n = $d;
	}

	$optKary .= "<option value='".$rKary['karyawanid']."'><b>".$lok."</b> - ".$rKary['nik']." - ".$rKary['namakaryawan']."</option>";

	if($rKary['tipekaryawan'] != '1'){
		$optpihakpertama .= "<option value='".$rKary['karyawanid']."'><b>".$lok."</b> - ".$rKary['nik']." - ".$rKary['namakaryawan']."</option>";
	}
}

if($n != ''){
	$optKary .= "</optgroup>";
	$optpihakpertama .= "</optgroup>";
}

$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";	
$sPeriode="select distinct left(tanggalberhenti,7) as periode from ".$dbname.".sdm_pesangon where left(tanggalberhenti,7)<>'0000-00'  order by left(tanggalberhenti,7) desc";
$rPeriode=fetchData($sPeriode);
foreach($rPeriode as $key=>$val){
	$optPeriode.="<option value=".$val['periode'].">".$val['periode']."</option>";	
}

$sJenis="select * from ".$dbname.".sdm_5jenispesangon where status='1' order by jenis asc";
$rJenis=fetchData($sJenis);
$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";                
foreach($rJenis as $key=>$val){
        $optjenis.="<option value='".$val['kode']."'>".nl2br($val['jenis'])."</option>";
}

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_pesangon').'</span>');

echo"<fieldset style='width:600px;margin-top:10px'>
    <legend><b>Form</b></legend>
	<table>
        <tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td><input type=text class=myinputtext id=tanggal style='width:200px;' onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/></td>
        </tr>
		<tr>
			<td>".$_SESSION['lang']['namakaryawan']."</td>
			<td><select id=karyawanid style=width:200px; onchange=getkodeunit(this.options[this.selectedIndex].value);>".$optKary."</select>
				<img id=\"karyawanid_find\" onclick=\"z.elSearch('karyawanid',event)\" class=\"resicon\" src=\"images/onebit_02.png\" style=\"position:relative;top:3px;left:3px;\">
			</td>
		</tr>
		<tr style='display:none'>
			<td>".$_SESSION['lang']['pihakpertama']."</td>
			<td>
				<select id=pihakpertama style=width:200px; >".$optpihakpertama."</select>
				<img id=\"pihakpertama_find\" onclick=\"z.elSearch('pihakpertama',event)\" class=\"resicon\" src=\"images/onebit_02.png\" style=\"position:relative;top:3px;left:3px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unitkerja']."</td>
			<td>
				<input type=text disabled class=myinputtext style='width:200px;' id=kodeunit size=10 maxlength=10>
				<input type=hidden  class=myinputtextnumber id=tglmasuk>
			</td>
		</tr>
		<tr>
            <td>".$_SESSION['lang']['tglberhenti']."</td>
            <td><input type=text class=myinputtext id=tglberhenti style='width:200px;' onmousemove=setCalendar(this.id) onkeypress=return false; onchange=getmasakerja(); size=10 maxlength=10 readonly/></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['masakerja']."</td>
			<td><input type=text disabled class=myinputtextnumber id=masakerjatahun style=width:30px; onkeypress='return angka_doang(event)' onblur=calculatePesangon(this);> ".$_SESSION['lang']['tahun']."
				<input type=text disabled class=myinputtextnumber id=masakerjabulan style=width:30px; onkeypress='return angka_doang(event)' /> ".$_SESSION['lang']['bulan']."
				<input type=text disabled class=myinputtextnumber id=masakerjahari style=width:30px; onkeypress='return angka_doang(event)' /> ".$_SESSION['lang']['hari']."</td>
		</tr>	
		<tr>
			<td>".$_SESSION['lang']['upah']."</td>
			<td><input type=text class=myinputtextnumber id=gajipokok  value=0  style='width:200px;' onkeypress='return angka_doang(event)' onchange='calculatePesangon()' onblur=\"change_number(this);\" disabled></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jenis']."</td>
			<td><input id='noid' hidden>
				<select id=jenis style=width:200px; onchange=getdetail();>".$optjenis."</select>
			</td>
		</tr>
	</table>
    </fieldset>";

	echo "<br /><fieldset style='width:1000px;'>
	<legend><b>".$_SESSION['lang']['detail']."</b></legend>
		<div id=detailTable style=display:block;>
		</div>
</fieldset>";



	echo "<br /><fieldset style='width:800px;'>
	<legend><b>".$_SESSION['lang']['list']."</b></legend>";
	echo "<fieldset style='width:800px;'><legend><b>".$_SESSION['lang']['find']."</b></legend>"; 
echo "<table cellpadding=1 cellspacing=1 border=0>
<tr>
<td>".$_SESSION['lang']['namakaryawan']."</td><td><input type=text class=myinputtext id=nmCar style=width:150px onkeypress='return tanpa_kutip(event)' /></td>
<td>".$_SESSION['lang']['jenis']."</td><td><select id=jnsSkCar style=width:150px>".$optjenis."</select></td>
<td>".$_SESSION['lang']['periode']."</td><td><select id=BlnCr style=width:150px>".$optPeriode."</select></td>
</tr> 
<tr>
<td colspan=6 align=left><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>&nbsp;<button class=mybutton onclick=clearPil()>Reset</button></td>
</tr> 

</table>";
echo"</fieldset><br>";
echo "<div id=isi><script>loadData(0)</script></div>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>