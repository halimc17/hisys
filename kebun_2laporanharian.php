<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script languange=javascript1.2 src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script languange=javascript1.2 src='js/formReport.js'></script>
<script languange=javascript1.2 src='js/zGrid.js'></script>
<script languange=javascript1.2 src='js/kebun_2laporanharian.js?v=1.3'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>

<?
//KEBUN 
//$optkebun=$optdivisi=$frm[0] = $frm[1] = "";

if($_SESSION['empl']['lokasitugas']=="MRHO")
{ $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)=4 and tipe='KEBUN'"; }
else {
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)=4 and tipe='KEBUN' and kodeorganisasi ='".$_SESSION['empl']['lokasitugas']."'";
}

$optkebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)=4 and tipe='KEBUN' and kodeorganisasi ='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optkebun.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('kebun_2laporanharian').'</span>');
echo"<fieldset>
	<legend>".$_SESSION['lang']['form']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['kebun']."</td>
			<td>:</td>
			<td><select id=kebun style=width:120px onchange=getDivisi(this.value)>".$optkebun."</select></td>
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['afdeling']."</td>
			<td>:</td>
			<td><select id='divisi' name='divisi' onchange='getKonduktor();' style='width:120px'></select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td><input type='text'  class='myinputtext' id='tanggal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='15' maxlength='10' onchange='getKonduktor();' readonly></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['konduktor']."</td>
			<td>:</td>
			<td><select id=konduktor name=konduktor  style=width:120px></select>
			<img onclick=\"z.elSearch('konduktor',event);\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>Type of Report</td>
			<td>:</td>
			<td><select id=typereport name=typereport  style=width:120px>
				<option value='1'>By Activity</option>
				<option value='2'>By Block</option>
			</select>
			</td>
		</tr>

	 </table>
	 <input type=hidden id=method value='preview'>
	 <button class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
	 <button class=mybutton onclick=excel(event,'kebun_slave_2laporanharian.php')>".$_SESSION['lang']['excel']."</button>
	 <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
	 </fieldset>";
	 
CLOSE_BOX();

OPEN_BOX();

echo"<fieldset style='clear:both;max-width:100%;'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
		<div id='awal'>
			<div id='container' style='height:350px;'>
			</div>
		</div>
    
		<div id='detailData' style='display:none'>
			<div id='isiData'>
			</div>
		</div>		
	</fieldset>";
	 
CLOSE_BOX();
echo close_body();
?>