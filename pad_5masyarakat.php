<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pad_masyarakat.js?v=1.2'></script>
<?

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('pad_5masyarakat').'</span><br>');
$optdesa = $optkecamatan = $optkabupaten = $optpad = "<option value=''>" . $_SESSION['lang']['pilih'] . "</option>";
$str = "select b.iddes, b.desa from " . $dbname . ".pad_5desa a 
        left join ".$dbname.".desa b on a.desa=b.iddes and a.kecamatan=b.id_kec group by a.desa order by b.desa";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optdesa.="<option value='" . $bar->iddes . "'>" . $bar->desa . "</option>";
}
$str = "select b.idkec, b.kecamatan from " . $dbname . ".pad_5desa a 
        left join ".$dbname.".kecamatan b on a.kecamatan=b.idkec group by a.kecamatan order by b.kecamatan";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optkecamatan.="<option value='" . $bar->idkec . "'>" . $bar->kecamatan . "</option>";
}
$str = "select b.id, b.kabupaten from " . $dbname . ".pad_5desa a 
        left join ".$dbname.".kabupaten b on a.kabupaten=b.id group by a.kabupaten order by b.kabupaten";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optkabupaten.="<option value='" . $bar->id . "'>" . $bar->kabupaten . "</option>";
}
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe in ('KEBUN','PABRIK') order by namaorganisasi";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optpad.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}
$str = "select kodebank,namabank from " . $dbname . ".keu_5daftarbank  order by kodebank asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
@$optbank="<option value=''></option>";
while ($bar = $res->fetch()) {
    @$optbank.="<option value='" . $bar->kodebank . "'>" . $bar->namabank . "</option>";
}

echo"<fieldset style='float:left;'><table>
    <tr><td valign=top>" . $_SESSION['lang']['id'] . "</td>
		<td valign=top><input type=text id=mid class=myinputtext  style='width:250px;' sise=4 disabled></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['nama'] . "</td><td>
             <input type=text id=nama size=30  style='width:250px;' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
		<td valign=top>" . $_SESSION['lang']['alamat'] . "</td>
		<td valign=top><input class=myinputtext id=alamat style='width:245px;'></td>
	</tr>
    <tr>
		<td>" . $_SESSION['lang']['namabank'] . "</td><td>
             <select id=kodebank style='width:255px;' >" . @$optbank . "</select></td>
		<td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['desa'] . "</td><td>
             <select id=desa style='width:250px;' >" . $optdesa . "</select></td>
	</tr>
    <tr>
	</tr>
	<tr><td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['pemilik'] . " " . $_SESSION['lang']['rekening'] . "</td>
			<td><input type=text id=namapemilikrek size=30  style='width:250px;' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
			 <td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['kecamatan'] . "</td><td>
             <select id=kecamatan style='width:250px;' onchange='getdesa()'>" . $optkecamatan . "</select></td>
	</tr>
    <tr><td>" . $_SESSION['lang']['norek'] . "</td><td>
             <input type=text id=norek size=45 maxlength=45 style='width:250px;'  onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
		<td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['kabupaten'] . "</td><td>
             <select id=kabupaten style='width:250px;' onchange='getkecamatan()'>" . $optkabupaten . "</select></td>	 
			 
	</tr>
     <tr><td>" . $_SESSION['lang']['nohp'] . "</td><td>
             <input type=text id=hp size=45 maxlength=45 style='width:250px;' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
		<td>" . $_SESSION['lang']['noktp'] . "</td><td>
             <input type=text id=ktp size=45 maxlength=45 style='width:245px;'  onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
	 </tr>             
    <tr><td></td><td>
		<input type=hidden id=method value='insert'>
         <button class=mybutton onclick=simpanJabatan()>" . $_SESSION['lang']['save'] . "</button>
         <button class=mybutton onclick=cancelJabatan()>" . $_SESSION['lang']['cancel'] . "</button>
     </td></tr>
	 </table>
         </fieldset>";
CLOSE_BOX();
OPEN_BOX();

echo"<fieldset style=float:left><legend>Find</legend><table>";
echo"<tr>";
echo"<td>Nomor ID</td><td>:</td><td><input onkeypress='enterkey(event,loaddata)' id=idcari width=50px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>";
echo"<td>Nama</td><td>:</td><td><input onkeypress='enterkey(event,loaddata)' id=namacari width=50px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>";
echo"<td>Alamat</td><td>:</td><td><input onkeypress='enterkey(event,loaddata)' id=alamatcari width=50px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>";
echo"<td>No KTP</td><td>:</td><td><input onkeypress='enterkey(event,loaddata)' id=ktpcari width=50px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>";
echo"<td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td>";
echo"<td><button class=mybutton onclick=batalcari()>" . $_SESSION['lang']['cancel'] . "</button></td>";

echo"</tr>";
echo"</table></fieldset>";
echo"<div style=clear:both></div>";
echo "<fieldset><legend>List Data</legend><div id=container style='width:100%;overflow:auto;'>
		<script>loaddata(0)</script>";
echo "</div></fieldset>";


CLOSE_BOX();
echo close_body();
?>