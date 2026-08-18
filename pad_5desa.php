<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pad_desa.js?v=1.1'></script>
<?

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('pad_5desa').'</span>');
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe in ('KEBUN','PABRIK') order by namaorganisasi";
$optpad = "";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optpad.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}

$arrprov=makeOption($dbname, 'provinsi', 'id,provinsi');
$optprov="<option value=''>" . $_SESSION['lang']['pilih'] . "</option>";
foreach ($arrprov as $ky => $vle) {
	$optprov.="<option value='".$ky."'>".$vle."</option>";
}

$arrkabupaten=makeOption($dbname, 'kabupaten', 'id,kabupaten');
$optkabupaten="<option value=''>" . $_SESSION['lang']['pilih'] . "</option>";
foreach ($arrkabupaten as $ky => $vle) {
	$optkabupaten.="<option value='".$ky."'>".$vle."</option>";
}

$arrkecamatan=makeOption($dbname, 'kecamatan', 'idkec,kecamatan');
$optkecamatan="<option value=''>" . $_SESSION['lang']['pilih'] . "</option>";
foreach ($arrkecamatan as $ky => $vle) {
	$optkecamatan.="<option value='".$ky."'>".$vle."</option>";
}

$arrdesa=makeOption($dbname, 'desa', 'iddes,desa');
$optdesa="<option value=''>" . $_SESSION['lang']['pilih'] . "</option>";
foreach ($arrdesa as $ky => $vle) {
	$optdesa.="<option value='".$ky."'>".$vle."</option>";
}

echo"<fieldset style='width:1000px;'>
		<table>
			<tr>
				<td>" . $_SESSION['lang']['kebun'] . "</td>
				<td>
					 <select id='unit' style='width:150px;'>" . $optpad . "</select>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['handil'] . "</td>
				<td>
					 <input type=text id=handil  style='width:145px;' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['provinsi'] . "</td>
				<td>
					 <select id=provinsi onchange='getkabupaten()'  style='width:150px;' >".$optprov."</select>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['kabupaten'] . "</td>
				<td>
					 <select id=kabupaten onchange='getkecamatan()'  style='width:150px;' >".$optkabupaten."</select>
				</td>
			</tr> 
			<tr>
				<td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['kecamatan'] . "</td>
				<td>
					 <select id=kecamatan onchange='getdesa()' style='width:150px;' >".$optkecamatan."</select>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['desa'] . "</td>
				<td>
					 <select id=desa  style='width:150px;'>".$optdesa."</select>
				</td>
			</tr>            
		</table>
				 <input type=hidden id=method value='insert'>
				 <button class=mybutton onclick=simpanJabatan()>" . $_SESSION['lang']['save'] . "</button>
				 <button class=mybutton onclick=cancelJabatan()>" . $_SESSION['lang']['cancel'] . "</button>
     </fieldset>";
echo open_theme($_SESSION['lang']['list']);

echo"<fieldset style=float:left><legend>Find</legend><table>";
echo"<tr>";
echo"<td>Handil</td><td>:</td><td><input onkeypress='enterkey(event,loaddata)' id=handilcari width=50px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>";
echo"<td hidden>Desa</td><td hidden>:</td><td hidden><input onkeypress='enterkey(event,loaddata)' id=desacari width=50px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>";
echo"<td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td>";
echo"<td><button class=mybutton onclick=batalcari()>" . $_SESSION['lang']['cancel'] . "</button></td>";
echo"<td><button class=mybutton onclick=desaexcel(event,'pad_slave_save_desa.php')>" . $_SESSION['lang']['excel'] . "</button></td>";

echo"</tr>";
echo"</table></fieldset>";
echo"<div style=clear:both></div>";
echo "<fieldset><legend>List Data</legend><div id=container style='width:100%;overflow:auto;'>";

echo "<script>loaddata(0)</script>";
echo "</div></fieldset>";

echo close_theme();
CLOSE_BOX();
echo close_body();
?>