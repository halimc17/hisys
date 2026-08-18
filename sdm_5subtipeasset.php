<?php
require_once('master_validation.php');
require_once('lib/zMysql.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language="JavaScript1.2" src="js/zTools.js?v=<?php echo time(); ?>'"></script>
<script language=javascript1.2 src="js/subtipeasset.js?v=<?php echo time(); ?>'"></script>
<?
include('master_mainMenu.php');
### BEGIN GET TYPE ASSET ###
$str = "select kodetipe, namatipe from " . $dbname . ".sdm_5tipeasset";
$optTypeAsset = $optionm = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$namatipe[$bar->kodetipe] = $bar->namatipe;
	$optTypeAsset .= "<option value='" . $bar->kodetipe . "'>" . $bar->kodetipe . " - " . $bar->namatipe . "</option>";
}
$str2 = "select id_namaharta, namaharta from " . $dbname . ".keu_5asset_namaharta";
$optNamaAset = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$res = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$namaharta[$bar->id_namaharta] = $bar->namaharta;
	$optNamaAset .= "<option value='" . $bar->id_namaharta . "'>" . $bar->id_namaharta . " - " . $bar->namaharta . "</option>";
}

$optkodeorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach (getOrgDetail(50) as $key => $val) {
	// if($key==$_SESSION['empl']['lokasitugas']){		
	// }
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
	$d = $induk[$key];
	if ($d != $n) {
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $d . "'");
		$optkodeorg .= "<optgroup label='" . $d . " - " . $nmorg[$d] . "'>";
	}
	$optkodeorg .= "<option value=" . $key . ">" . $key . " - " . $val . "</option>";
	$n = $d;
	if ($d != $n) {
		$optkodeorg .= "</optgroup>";
	}
}

$arrmetode = getEnum($dbname, 'sdm_5subtipeasset', 'metodepenyusutan');

foreach ($arrmetode as $key => $valm) {
	$optionm .= "<option value='" . $key . "'>" . $key . "</option>";
}

##  # END GET TYPE ASSET ##  #
OPEN_BOX('', '<span class=judul>' . getMenu('sdm_5subtipeasset') . '</span>');
echo "<fieldset style='width:800px;'><table>
<tr><td>" . $_SESSION['lang']['unit'] . "</td><td>:</td><td><select style='width:250px;' id=unit>" . $optkodeorg . "</select></td></tr>
<tr><td>" . $_SESSION['lang']['tipeasset'] . "</td><td>:</td><td><select style='width:250px;' id=tipeasset>" . $optTypeAsset . "</select></td></tr>
<tr><td>" . $_SESSION['lang']['kodesubasset'] . "</td><td>:</td><td><input type=text id=kodesubasset size=4 maxlength=2 onkeypress='return angka_doang(event)' \" class=myinputtext disabled></td></tr>
<tr><td>" . $_SESSION['lang']['namasubasset'] . "</td><td>:</td><td><input type=text id=namasubasset size=37 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
<tr><td>" . $_SESSION['lang']['namaharta'] . "</td><td>:</td><td><select style='width:250px;' id=nama>" . $optNamaAset . "</select></td></tr>
<tr><td>Metode Penyusutan</td><td>:</td><td><select style='width:250px;' onchange='getMP()' id=metodepenyusutan>" . $optionm . "</select></td></tr>
<tr><td>" . $_SESSION['lang']['umurpenyusutan'] . "</td><td>:</td><td style='vertical-align:bottom'><input type=text id=umurpenyusutan size=4 maxlength=5 onkeypress='return angka_doang(event)' \" class=myinputtext>&nbsp;" . $_SESSION['lang']['bulan'] . "</td></tr>
<tr><td>Tarif Penyusutan (%)</td><td>:</td><td style='vertical-align:bottom'><input type=text id=tarifpenyusutan size=4 maxlength=5 onkeypress='return angka_doang(event)' \" class=myinputtext>&nbsp;" . $_SESSION['lang']['persen'] . "(%)</td></tr>

<tr><td><td><td>
<input type=hidden id=save value=simpan>
<button class=mybutton onclick=simpanSubTipeAset()>" . $_SESSION['lang']['save'] . "</button>
<button class=mybutton onclick=cancelSubTipeAsset()>" . $_SESSION['lang']['cancel'] . "</button>
</table></fieldset><p />";
echo open_theme($_SESSION['lang']['availvhc']);
echo "<div class='table-scroll' style=height:55vh>";
echo "<table class=sortable cellspacing=1 cellpadding=5 border=0 style='min-width:800px;'>
<thead>
<tr class=rowheader>
<th>" . $_SESSION['lang']['kodeorg'] . "</th>
<th>" . $_SESSION['lang']['tipeasset'] . "</th>
<th>" . $_SESSION['lang']['kode'] . "</th>
<th>" . $_SESSION['lang']['namasubasset'] . "</th>
<th>" . $_SESSION['lang']['namaharta'] . "</th>
<th>Metode Penyusutan</th>
<th style='width:70px;'>" . $_SESSION['lang']['umurpenyusutan'] . " (" . $_SESSION['lang']['bulan'] . ")</th>
<th style='width:70px;'>Tarif Penyusutan (" . $_SESSION['lang']['persen'] . ")</th>
<th>" . $_SESSION['lang']['action'] . "</th></tr>
</thead>
<tbody id=container>";
$str1 = "select * from " . $dbname . ".sdm_5subtipeasset
	order by kodetipe, kodesub";
$res = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res->fetch()) {
	echo "<tr class=rowcontent>
	<td style='text-align:center'>" . $bar1->kodeorg . "</td>
	<td>" . $bar1->kodetipe . " - " . $namatipe[$bar1->kodetipe] . "</td>
	<td style='text-align:center'>" . $bar1->kodesub . "</td>
	<td>" . $bar1->namasub . "</td>
	<td>" . $bar1->id_namaharta . " - " . @$namaharta[$bar1->id_namaharta] . "</td>
	<td align=center>" . $bar1->metodepenyusutan . "</td>
	<td align=center>" . $bar1->umurpenyusutan . "</td>
	<td align=center>" . $bar1->tarifpenyusutan . "</td>
	<td style='text-align:center'>
	<img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"editSubTipeAset('" . $bar1->kodesub . "','" . $bar1->namasub . "','" . $bar1->id_namaharta . "','" . $bar1->umurpenyusutan . "','" . $bar1->kodetipe . "');\">
	</td></tr>";
}
echo "
</tbody>
<tfoot>
</tfoot>
</table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>