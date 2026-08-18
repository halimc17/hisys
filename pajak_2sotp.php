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
<script language=javascript src='js/pajak_2sotp.js'></script>

<?
include('master_mainMenu.php');		
OPEN_BOX('','<span class=judul><b>'.getMenu('pajak_2sotp').'</b><br><br></span>');
$optNmPt=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","tipe='PT'");
$optpt=$optnpwp=$optthn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="SELECT distinct induk FROM ".$dbname.".organisasi where kodeorganisasi in (select distinct kodeorg from ".$dbname.".keu_kasbankht) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optpt.="<option value='".$bar['induk']."'>".$bar['induk']."-".$optNmPt[$bar['induk']]."</option>";
}
$str="SELECT distinct(left(tanggal,4)) as thn FROM ".$dbname.".keu_kasbankht where left(tanggal,4)>2016 order by left(tanggal,4) desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optthn.="<option value='".$bar['thn']."'>".$bar['thn']."</option>";
}

?>

<?
echo"<fieldset style=width:300px;float:left><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['pt']."</td>
		<td>:</td>
		<td><select id=pt style=\"width:130px;\" onchange=getnpwp()>".$optpt."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['npwp']."</td>
		<td>:</td>
		<td><select id=npwp style=\"width:130px;\" >".$optnpwp."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tahun']."</td>
		<td>:</td>
		<td><select id=thn style=\"width:130px;\" >".$optthn."</select></td>
	</tr>
	";
echo"<tr>
		<td colspan=3 align=right>
		<button onclick=preview() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=excel() class=mybutton name=preview id=excel>".$_SESSION['lang']['excel']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";


echo"
<fieldset style=width:99%><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 

CLOSE_BOX();
echo close_body();

?>