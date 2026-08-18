<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/keu_3jurnaloh.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');		
OPEN_BOX('','<span class=judul><b>'.getMenu('keu_3jurnalohtbm').'</b><br></span>');
	
$optper=$optunit=$optalokasi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optnmkom=  makeOption($dbname, 'sdm_ho_component', 'id,name');


#= data kebun yang ada tbmnya saja
$str="SELECT * FROM ".$dbname.".organisasi where length(kodeorganisasi)='4' and tipe='KEBUN' and inti=1";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$str="SELECT distinct(periode) as periode FROM ".$dbname.".setup_periodeakuntansi order by periode desc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$optper.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}

$sql = "SELECT DISTINCT statusblok FROM {$dbname}.setup_blok";
$res = fetchData($sql);

foreach($res as $val) {
    $optalokasi .= "<option value='".$val['statusblok']."'>".$val['statusblok']."</option>";
}

?>
<?
$optjenis="<option value='0'>TIDAK</option>";
$optjenis.="<option value='1'>YA</option>";
echo"<fieldset style=width:300px;float:left><legend><b>Form</b></legend>
<table>
    <tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select class=select2 id=per style=\"width:200px;\" >".$optper."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select class=select2 id=unit style=\"width:200px;\" >".$optunit."</select></td>
	</tr>
    <tr>
        <td>".$_SESSION['lang']['alokasi']."</td>
		<td>:</td>
		<td><select class=select2 id=alokasibiaya style=\"width:200px;\" >".$optalokasi."</select></td>
    </tr>
	<tr hidden>
		<td>".$_SESSION['lang']['detail']."</td>
		<td>:</td>
		<td><select class=select2 id=jenis style=\"width:200px;\" >".$optjenis."</select></td>
	</tr>";
echo"<tr>
		<td colspan=2 align=right></td>
		<td colspan=3>
			<button onclick=preview() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
			<button onclick=excel() class=mybutton name=preview id=excel>".$_SESSION['lang']['excel']."</button>
			<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>		
		</td>
	</tr>
</table>
</fieldset>";
echo"<fieldset style=width:800px><legend><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
			<li>Lakukan Proses ini setelah proses akhir bulan</li>
			<li>Lakukan Proses ini sebelum tutup buku Unit</li>
			<li>Proses ini hanya untuk Unit Kebun yang <b>masih memiliki blok TBM</b></li>
			
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo"
<div id='printContainer'  style='height:60vh;'></div>";

CLOSE_BOX();
echo close_body();

?>