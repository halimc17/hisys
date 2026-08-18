<?//@Copy nangkoelframework
//-----------------ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();


?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/sdm_5cutilain.js'></script>



<?
$optgol="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTipe=$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

if ($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') {
	$whereorg.=" and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}else{
	$whereorg.=" and length(kodeorganisasi)=4";
}
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 1=1 ".$whereorg."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
        $optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}
if ($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') {
	$wheretk.=" and id in (1,2,3,5,6)";
}else{
	$wheretk.=" and id in (7,8,0)";
}
$str="select * from ".$dbname.".sdm_5tipekaryawan  where 1=1  ".$wheretk." ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optTipe.="<option value=".$bar['id'].">".$bar['tipe']."</option>";
}

$str="select * from ".$dbname.".sdm_5golongan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optgol.="<option value=".$bar['kodegolongan'].">".$bar['namagolongan']."</option>";
}

$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optJenis.="<option value='CUTIPOTONGGAJI'>CUTI POTONG GAJI</option>";
$optJenis.="<option value='TERLAMBATPOTONGGAJI'>TERLAMBAT POTONG GAJI</option>";
$optJenis.="<option value='PULANGAWALPOTONGGAJI'>PULANG AWAL POTONG GAJI</option>";



?>



<?
include('master_mainMenu.php');
if($_SESSION['language']=='EN'){
   OPEN_BOX('','<span class=judul>'.strtoupper('Another Holiday Setup').'</span><br>');
}else{
    OPEN_BOX('','<span class=judul>'.strtoupper('Setup Cuti Lainnya').'</span><br>');
}
$arr="##unit##tahun##jeniscuti##tipekar##golkar";	

echo "<fieldset style=float:left><legend><b>Form</b></legend>
<table>
	<tr>
        <td>Tahun</td>
        <td>:</td>
        <td><input type=text id=tahun  size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=5 style=\"width:80px;\"></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td><select id=unit  style='width:140px;'>".$optOrg."</select></td>
    </tr>
   
    <tr>
        <td>".$_SESSION['lang']['jenis']."</td>
        <td>:</td>
        <td><select id=jeniscuti  style='width:140px;'>".$optJenis."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['tipekaryawan']."</td>
        <td>:</td>
        <td><select id=tipekar style='width:140px;'>".$optTipe."</select></td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['kodegolongan']."</td>
        <td>:</td>
        <td><select id=golkar style='width:140px;'>".$optgol."</select></td>
    </tr> ";
echo "	<tr>
		<td colspan=3 align=right>
		<button onclick=zPreview('sdm_slave_5cutilain','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'sdm_slave_5cutilain.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo "
<fieldset ><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 

CLOSE_BOX();
echo close_body();




?>