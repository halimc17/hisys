<?
#ini_set('display_errors',0);
#error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src='js/pad_pembebasan.js?v=<?php echo time(); ?>'></script>

<?

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>STATUS LAHAN AWAL</span>');
$optdesa = $optkecamatan = $optkabupaten = $optpad = $optPemilik = $optstatus = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select b.desa from " . $dbname . ".pad_5desa a 
        left join ".$dbname.".desa b on a.desa=b.iddes and a.kecamatan=b.id_kec group by a.desa order by b.desa";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optdesa.="<option value='" . $bar->desa . "'>" . $bar->desa . "</option>";
}
$str = "select b.kecamatan from " . $dbname . ".pad_5desa a 
        left join ".$dbname.".kecamatan b on a.kecamatan=b.idkec group by a.kecamatan order by b.kecamatan";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optkecamatan.="<option value='" . $bar->kecamatan . "'>" . $bar->kecamatan . "</option>";
}
$str = "select b.kabupaten from " . $dbname . ".pad_5desa a 
        left join ".$dbname.".kabupaten b on a.kabupaten=b.id group by a.kabupaten order by b.kabupaten";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optkabupaten.="<option value='" . $bar->kabupaten . "'>" . $bar->kabupaten . "</option>";
}
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe in ('KEBUN','PABRIK') order by namaorganisasi";
$optpad = "<option value=''>" . $_SESSION['lang']['pilih'] . "</option>";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()){
    $optpad.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->kodeorganisasi . " - " . $bar->namaorganisasi . "</option>";
}

$str = "select * from " . $dbname . ".lgl_5kawasan";
$optstatus = "<option value=''>" . $_SESSION['lang']['pilih'] . "</option>";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()){
    $optstatus.="<option value='" . $bar->jenis . "'>" . $bar->jenis . " </option>";
}

echo"
    <table border=0><tbody><tr><td valign=top>
    <fieldset><legend>1." . $_SESSION['lang']['namapemilik'] . " " . $_SESSION['lang']['lahan'] . " Awal</legend>
            <table><tbody>
                <tr>
					<td>" . $_SESSION['lang']['id'] . "</td>
					<td>
                         <input type=text  id=mid class=myinputtext  style='width:145px' disabled>
					</td>
					
					<td>Luas Lahan Inti</td><td>
                    <input type=text id=luasinti size=5 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber onblur='hitungtotalahan()' value=0>Ha.</td>
					
					
				</tr>
				<tr><td>" . $_SESSION['lang']['kebun'] . "</td><td>
                         <select id='unit' style='width:150px' onchange=updatePemilik(this.options[this.selectedIndex].value)>" . $optpad . "</select></td>
					<td>Luas Lahan Plasma</td><td>
                    <input type=text id=luasplasma size=5 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber onblur='hitungtotalahan()' value=0>Ha.</td>
				</tr>                    
                <tr>
					<td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['namapemilik'] . " " . $_SESSION['lang']['lahan'] . "</td>
					<td>
                         <select  style='width:150px' id=pemilik>" . $optPemilik . "</select>
					</td>
					
					<td>Total Luas Lahan</td><td>
                    <input type=text id=luaslahan size=5 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber value=0 disabled>Ha.</td>
					
				</tr>
                <tr><td>(No.Persil)</td><td>
                         <input type=text id=lokasi   style='width:145px' maxlength=45 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
					<td>" . $_SESSION['lang']['luas'] . " " . $_SESSION['lang']['bisaditanam'] . "</td><td>
                         <input type=text id=bisaditanam size=5 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber value=0>Ha.</td>
						 
				</tr>
				<tr>
					<td>No SHM / No SPPT</td><td>
                         <input type=text id=shm style='width:145px' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
					<td>Jumlah SPPT</td><td>
                         <input type=text id=jmlsppt size=5 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber value=0></td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['lokasi'] . " " . $_SESSION['lang']['kodeblok'] . "</td><td>
                         <select id=blok style=width:150px><option value=''></option></select></td>
				</tr>
				<tr>
					<td>Status Kawasan</td>
                    <td>
                         <select  style='width:150px' id=statuskawasan>" . $optstatus . "</select>
					</td>
				</tr>
				</tbody></table>
				</fieldset>
         </td>
         <td style='vertical-align:top;'>
			<fieldset style=height:143px><legend>2." . $_SESSION['lang']['batas'] . "-" . $_SESSION['lang']['lokasi'] . "</legend>
			<table>   
				<tr><td>" . $_SESSION['lang']['batastimur'] . "</td><td>
                         <input type=text id=batastimur  size=30 maxlength=45 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
					<td>" . @$_SESSION['lang']['koordinat'] . " UL_X</td><td>
                         <input type=text id=koordinatulx  size=30 maxlength=45 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>	 
				</tr>
                <tr><td>" . $_SESSION['lang']['batasbarat'] . "</td><td>
                         <input type=text id=batasbarat  size=30 maxlength=45 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
					 <td>" . @$_SESSION['lang']['koordinat'] . " UL_Y</td><td>
                         <input type=text id=koordinatuly  size=30 maxlength=45 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
				</tr>
                <tr><td>" . $_SESSION['lang']['batasutara'] . "</td><td>
                         <input type=text id=batasutara  size=30 maxlength=45 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
					<td>" . @$_SESSION['lang']['koordinat'] . " LR_X</td><td>
                         <input type=text id=koordinatlrx  size=30 maxlength=45 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>	 
				</tr>
                <tr><td>" . $_SESSION['lang']['batasselatan'] . "</td><td>
                         <input type=text id=batasselatan  size=30 maxlength=45 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
					<td>" . @$_SESSION['lang']['koordinat'] . " LR_Y</td><td>
                         <input type=text id=koordinatlry  size=30 maxlength=45 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['alamat'] . "</td>
					<td>
					<input type=text id=alamat  size=30 maxlength=100 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
					
					<td>" . $_SESSION['lang']['keterangan'] . "</td>
					<td colspan='3'>
					<input type=text id=ket  size=30 maxlength=100 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
				</tr>
            </table>
            </fieldset>         
         </td>
         </tr>
        <tr><td colspan=2 align=center>
            <input type=hidden id=method value='insert'>
            <button class=mybutton onclick=simpanJabatan()>" . $_SESSION['lang']['save'] . "</button>
            <button class=mybutton onclick=cancelJabatan()>" . $_SESSION['lang']['cancel'] . "</button>
        </td></tr>  
       </tbody>
         </table>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style=float:left><legend>Find</legend><table>";
echo"<tr>";
echo"<td>Nomor ID</td><td>:</td><td><input onkeypress='enterkey(event,loaddata)' id=idcari width=50px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>";
echo"<td>Pemilik</td><td>:</td><td><input onkeypress='enterkey(event,loaddata)' id=pemilikcari width=50px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>";
echo"<td>Persil</td><td>:</td><td><input onkeypress='enterkey(event,loaddata)' id=persilcari width=50px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>";
echo"<td>No SPPT</td><td>:</td><td><input onkeypress='enterkey(event,loaddata)' id=spptcari width=50px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>";
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