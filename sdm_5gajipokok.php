<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
require_once('master_mainMenu.php');

?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/sdm_5gajipokok.js?v=<?php echo time(); ?>'></script>
<?



$optTipe = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optkmpn = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optkary = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sTp = "select id,name from " . $dbname . ".sdm_ho_component where type='basic' order by id asc";
$qTp = $owlPDO->query($sTp) or die(print " Gagal: " . PDOException::getMessage());
$qTp->setFetchMode(PDO::FETCH_ASSOC);
while ($rTp = $qTp->fetch()) {
	$optTipe .= "<option value='" . $rTp['id'] . "'>" . $rTp['name'] . "</option>";
	$optkmpn .= "<option value='" . $rTp['id'] . "'>" . $rTp['name'] . "</option>";
}

$optTipe2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

#jabatan
$optJbtn = $optGol = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sjbtn = "select * from " . $dbname . ".sdm_5jabatan where 
        lower(namajabatan) not like '%manager%'  and lower(namajabatan) not like '%head%' and lower(namajabatan) not like '%komisaris%'
        and lower(namajabatan) not like '%asisten%' and lower(namajabatan) not like '%Direktur%' and lower(namajabatan) not like '%senior%'
        order by namajabatan asc";
$rjbtn = fetchData($sjbtn);
foreach ($rjbtn as $row => $lstJbtn) {
	$optJbtn .= "<option value='" . $lstJbtn['kodejabatan'] . "'>" . $lstJbtn['namajabatan'] . "</option>";
}

##golongan
$i = "select * from " . $dbname . ".sdm_5golongan where kodegolongan not in ('BOD')";
$n = $owlPDO->query($i) or die(print " Gagal: " . PDOException::getMessage());
$n->setFetchMode(PDO::FETCH_ASSOC);
while ($d = $n->fetch()) {
	$optGol .= "<option value='" . $d['kodegolongan'] . "'>" . $d['kodegolongan'] . " => " . $d['namagolongan'] . "</option>";
}


$i = "select * from " . $dbname . ".organisasi where 1=1 and kodeorganisasi in (" . getOrgDetail(2) . ") ";
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){

// }else{
//     $i="select * from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and  tipe!='HOLDING' ";
// }

$optUnit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$n = $owlPDO->query($i) or die(print " Gagal: " . PDOException::getMessage());
$n->setFetchMode(PDO::FETCH_ASSOC);
while ($d = $n->fetch()) {
	$optUnit .= "<option value='" . $d['kodeorganisasi'] . "'>" . $d['kodeorganisasi'] . " - " . $d['namaorganisasi'] . "</option>";
}

$optUnit2 = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$n = $owlPDO->query($i) or die(print " Gagal: " . PDOException::getMessage());
$n->setFetchMode(PDO::FETCH_ASSOC);
while ($d = $n->fetch()) {
	$optUnit2 .= "<option value='" . $d['kodeorganisasi'] . "'>" . $d['kodeorganisasi'] . " - " . $d['namaorganisasi'] . "</option>";
}


$optTipe3 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$opttpkar = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

$whrd = "";
if (trim($_SESSION['empl']['tipelokasitugas']) == 'HOLDING' || trim($_SESSION['empl']['tipelokasitugas']) == 'KANWIL') {
	$whrd = " and id not in ('0')";
} else {
	$whrd = " and id not in ('0')";
}

$sTp2 = "select distinct id,tipe from " . $dbname . ".sdm_5tipekaryawan where 1=1 " . $whrd . " and aktif='1' order by no asc";
$qTp2 = $owlPDO->query($sTp2) or die(print " Gagal: " . PDOException::getMessage());
$qTp2->setFetchMode(PDO::FETCH_ASSOC);
while ($rTp = $qTp2->fetch()) {
	$optTipe3 .= "<option value='" . $rTp['id'] . "'>" . $rTp['tipe'] . "</option>";
	$opttpkar .= "<option value='" . $rTp['id'] . "'>" . $rTp['tipe'] . "</option>";
}
$arrd = array("0" => "Per Orang/Per Person", "1" => $_SESSION['lang']['all'], "2" => "Hanya yg belum ada Gapok");
$optTipe5 = "";
foreach ($arrd as $rwdd => $lstarr) {

	$optTipe5 .= "<option value='" . $rwdd . "'>" . $lstarr . "</option>";
}

$arr = "##thn##pilInp##karyawanId##idKomponen##jmlhDt##method##tpKary##kdUnit##golongan##jabatan";


OPEN_BOX('', '<span class=judul>' . getMenu('sdm_5gajipokok') . '</span><br>');
$opttahun = "";
$currentDate = new DateTime();
$format = 'Y-m';

for ($x = 2; $x >= -10; $x--) {
	// Clone the current date to avoid modifying it
	$date = clone $currentDate;
	// Modify the date by adding/subtracting months
	$date->modify("{$x} month");

	$value = $date->format($format);

	if ($value == $currentDate->format($format)) {
		$opttahun .= "<option value='{$value}' selected>{$value}</option>";
	} else {
		$opttahun .= "<option value='{$value}'>{$value}</option>";
	}
}

#== FORM PENCARIAN ==
echo "<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
		<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
		<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td hidden align=center style='width:75px;cursor:pointer;' onclick=updatetrans()>
			<img class=delliconBig src=images/archive.png title='Update'><br>Update
	 </td>
	 <td>
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
			<table border=0>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td>
					<td>
						<select id=opttahun style='width:150px;''>" . $opttahun . "</select>
					</td>
					<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
					<td>
						<input type=text style='width:145px'; class=myinputtext  id=nmKar name=nmKar  onkeypress=\"return tanpa_kutip(event);\" onkeypress=\"enterkey(event,loadData);\" style=\"width:150px;\" />
					</td>
					<td>" . $_SESSION['lang']['unitkerja'] . "</td>
					<td>
						<select  id=kdUnitCr style='width:150px;'>" . $optUnit2 . "</select>
					</td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['tipekaryawan'] . "</td>
					<td>
						<select id=tpKaryCr style=width:150px;>" . $opttpkar . "</select>
					</td>
					<td>" . $_SESSION['lang']['idkomponen'] . "</td>
					<td>
						<select id=idKomponenCr style=width:150px;>" . $optkmpn . "</select>
					</td>
					<td>" . $_SESSION['lang']['kodejabatan'] . "</td>
					<td>
						<select id=idjabatan style=width:150px;>" . $optJbtn . "</select>
					</td>
				</tr>
				<tr>
					<td></td>
					<td colspan=6>
						<button onclick=loadData() class=mybutton >" . $_SESSION['lang']['find'] . "</button>
						<button onclick=dataKeExcel(event) class=mybutton>Excel</button>  
					</td>
				</tr>
			</table>
		</fieldset>
     </tr></table>
</fieldset>";
CLOSE_BOX();


echo "<div id=inputdata style=display:none;>";
OPEN_BOX();
echo "<table border=0><td><fieldset style='float:left;'>
     <legend><b>Form</b></legend>
	 <table>
	 <tr>
	   <td>" . $_SESSION['lang']['periode'] . "</td>
	   <td>:</td>
	   <td>
	   		<input type=text class=myinputtextnumber id=thn name=thn  style=width:145px; onkeypress=\"return angka_doang(event);\" style=\"width:50px;\" maxlength='7' value='" . date('Y-m') . "'>
	   </td>

	   <td>" . $_SESSION['lang']['unitkerja'] . "</td>
	   <td>:</td>
	   <td><select onchange=getKar() id=kdUnit style=width:150px;>" . $optUnit . "</select></td>
	 </tr>
	 
    <tr>
	   <td>" . $_SESSION['lang']['tipekaryawan'] . " </td>
	   <td>:</td>
	   <td><select id=tpKary onchange=getKar() style=width:150px;>" . $optTipe3 . "</select></td>

	   <td>" . $_SESSION['lang']['kodegolongan'] . " </td>
	   <td>:</td>
	   <td>
			<select id=golongan onchange=getKar() style=width:150px;>" . $optGol . "</select>
	   </td>
	 </tr>	
	 <tr>
	   	<td>" . $_SESSION['lang']['kodejabatan'] . " </td>
		<td>:</td>
	    <td>
			<select id=jabatan onchange=getKar() style=width:150px;>" . $optJbtn . "</select><img id='jabatan' onclick=z.elSearch('jabatan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
	   <td>" . $_SESSION['lang']['pilih'] . " </td>
	   <td>:</td>
	   <td><select id=pilInp style=width:150px;>" . $optTipe5 . "</select></td>
	 </tr>	
	 

	 <tr>
	   <td>" . $_SESSION['lang']['idkomponen'] . " </td>
	   <td>:</td>
	   <td><select id=idKomponen  style=width:150px;>" . $optTipe . "</select></td>

	   <td>" . $_SESSION['lang']['namakaryawan'] . " </td>
	   <td>:</td>
	   <td>
	   		<select id=karyawanId style=width:150px;>" . $optTipe2 . "</select><img id='karyawanId' onclick=z.elSearch('karyawanId',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
	</tr>	 
     
    <tr>
	   <td>" . $_SESSION['lang']['jumlah'] . "</td>
	   <td>:</td>
	   <td>
			<input type=text class=myinputtextnumber id=jmlhDt name=jmlhDt  onkeypress=\"return angka_doang(event);\" style=\"width:145px;\" maxlength='12' />
	   </td>
	 </tr>	
	<tr>
	   <td><input type=hidden class=myinputtext id=karyPdf name=karyPdf  style=\"width:150px;\"></td>
	</tr>	
	 
	 <tr><td colspan=2><td>
	 <input type=hidden value=insert id=method>
	 <button class=mybutton onclick=saveFranco('sdm_slave_5gajipokok','" . $arr . "')>" . $_SESSION['lang']['save'] . "</button>
	 <button class=mybutton onclick=cancelIsi()>" . $_SESSION['lang']['cancel'] . "</button>
     </td></td></tr>
	 </table>
	 </fieldset></td>";


$opttahun1 = "";
$currentDate = new DateTime();
$format = 'Y-m';

for ($x = -2; $x <= 8; $x++) {
	// Clone the current date to avoid modifying it
	$date = clone $currentDate;
	// Modify the date by adding/subtracting months
	$date->modify("{$x} month");

	$value = $date->format($format);

	if ($value == $currentDate->format($format)) {
		$opttahun1 .= "<option value='{$value}' selected>{$value}</option>";
	} else {
		$opttahun1 .= "<option value='{$value}'>{$value}</option>";
	}
}

echo "</td>";
if (trim($_SESSION['empl']['tipelokasitugas']) == 'HOLDING' || trim($_SESSION['empl']['tipelokasitugas']) == 'KANWIL') {
	echo "<td valign=top><fieldset style='float:left;height:150px'><legend>Copy</legend>";
	echo "<table border=0 style='display: inline-block;vertical-align:top'>
	<tr><td>" . $_SESSION['lang']['unitkerja'] . "</td><td>:</td>
		<td colspan=4><select id=kdUnit2 style=width:200px>" . $optUnit . "</select></td>
	</tr><tr>
		<td>" . $_SESSION['lang']['dari'] . " " . $_SESSION['lang']['periode'] . "</td>
		<td>:</td>
		<td><select id=tahun1>" . $opttahun1 . "</select>
			Ke " . $_SESSION['lang']['periode'] . " :
			<select id=tahun2>" . $opttahun1 . "</select></td>
	</tr><tr>
		<td  colspan=2></td><td colspan=4><button onclick=copyTahun() class=mybutton>" . $_SESSION['lang']['proses'] . "</button></center></td>
	</tr><tr>
		<td colspan=6><hr></td>
	</tr><tr>
		<td colspan=6>ID : Copy upah dari konfigurasi periode gaji tertentu ke periode tertentu</td>
    </tr></table></fieldset></td></table>";
} else {

	echo "<td  valign=top><fieldset style='float:left;height:150px'><legend>Copy</legend>";
	echo "<table border=0 style='display: inline-block;vertical-align:top'>
	<tr><td>" . $_SESSION['lang']['unitkerja'] . "</td><td>:</td>
		<td colspan=4><select id=kdUnit2 style=width:200px>" . $optUnit . "</select></td>
	</tr><tr>
		<td>" . $_SESSION['lang']['dari'] . " " . $_SESSION['lang']['periode'] . "</td><td>:</td>
		<td><select id=tahun1>" . $opttahun1 . "</select>
			Ke " . $_SESSION['lang']['periode'] . " :
			<select id=tahun2>" . $opttahun1 . "</select></td>
	</tr><tr>
		<td  colspan=2></td><td colspan=4><button onclick=copyTahun() class=mybutton>" . $_SESSION['lang']['proses'] . "</button></center></td>
	</tr><tr>
		<td colspan=6><hr></td>
	</tr><tr>
		<td colspan=6>Info : Copy gaji dari konfigurasi gaji periode tertentu ke periode tertentu dan data yang tidak tercopy merupakan data yang berbeda dengan data karyawan , silahkan input manual</td>
	</tr><tr>
    </tr></table></fieldset></td></table>";
}

CLOSE_BOX();
echo "</div>";

$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach (getOrgDetail(11) as $key => $val) {
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
	$d = $induk[$key];
	if ($d != $n) {
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $d . "'");
		$optorg .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
	}
	$optorg .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
	$n = $d;
	if ($d != $n) {
		$optorg .= "</optgroup>";
	}
}

$optidkmpn = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select id,name from " . $dbname . ".sdm_ho_component where type='basic' and id='1' order by name";
$res = fetchData($str);
foreach ($res as $bar) {
	$optidkmpn .= "<option value='" . $bar['id'] . "'>" . $bar['name'] . "</option>";
}

OPEN_BOX();
echo "<div id=updatedata style=display:none;>";
echo "<fieldset style='float:left;'>
     <legend><b>Update Transaksi</b></legend>
	 <table>
	 <tr>
	    <td>" . $_SESSION['lang']['kodeorg'] . "</td>
		<td>:</td>
		<td><select onchange=getperiode('kodeorg') id=kodeorg style=width:150px;>" . $optorg . "</select></td>
		
		<td>" . $_SESSION['lang']['periode'] . " " . $_SESSION['lang']['transaksi'] . "</td>
		<td>:</td>
		<td><select id=periode style=width:150px;></select></td>
	 </tr>
	 <tr>
		<td>Periode Upah</td>
		<td>:</td>
		<td><select id=tahun style=width:150px;>" . $opttahun . "</select></td>
	 
		<td>" . $_SESSION['lang']['tipekaryawan'] . " </td>
		<td>:</td>
	    <td><select id=tipekary onchange=getperiode('tipekary') style=width:150px;>" . $optTipe3 . "</select></td>
	</tr>	
	<tr>
	   <td>" . $_SESSION['lang']['idkomponen'] . " </td><td>:</td>
	    <td><select id=idkomponen  style=width:150px;>" . $optidkmpn . "</select></td>

	
	   <td>" . $_SESSION['lang']['namakaryawan'] . " </td><td>:</td>
	    <td><select id=namakaryawan  style=width:150px;>" . $optkary . "</select>
			<img id='namakaryawan' onclick=z.elSearch('namakaryawan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>

	</tr>	
	<tr>
		<td colspan=2></td>
		<td>
			<button class=mybutton onclick=prevupdate()>Preview</button>
		</td>
	</tr>
	 </table>
	 </fieldset>";
echo "<div style=clear:both;></div>";

echo "<div id=containerupdate></div>";
echo "</div>";



##LIST DATA
echo "<div id=listdata>";
#echo"<fieldset style=float:left;>
#	<legend>".$_SESSION['lang']['list']."</legend>";
echo "<div id=container><script>loadData()</script></div>";
#echo"</fieldset>";
echo "</div>";
CLOSE_BOX();

echo close_body();
?>