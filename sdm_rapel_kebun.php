<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/sdm_rapel_kebun.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['rapel']).'</span>');
$optPeriode = '<option value=""></option>';
$sGp = "select DISTINCT periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and `sudahproses`=0 order by periode desc limit 0,6";
$qGp = $owlPDO->query($sGp) or die(print " Gagal: " . PDOException::getMessage());
$qGp->setFetchMode(PDO::FETCH_ASSOC);
while ($rGp = $qGp->fetch()) {
    $optPeriode.="<option value=" . $rGp['periode'] . ">" . substr(tanggalnormal($rGp['periode']), 1, 7) . "</option>";
}
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    $str1 = "select * from " . $dbname . ".datakaryawan
      where ((tanggalkeluar='0000-00-00') or tanggalkeluar>='" . date('Y') . "-01-01" . "')
	  and tipekaryawan!=0 and lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "'
	  order by namakaryawan";
} else {
    $str1 = "select * from " . $dbname . ".datakaryawan
      where ((tanggalkeluar='0000-00-00') or tanggalkeluar>='" . date('Y') . "-01-01" . "')
	  and tipekaryawan!=0 and LEFT(lokasitugas,4)='" . substr($_SESSION['empl']['lokasitugas'], 0, 4) . "'
	  order by namakaryawan";
}
$optIdKaryawan = '<option value=""></option>';
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res1->fetch()) {
    $optIdKaryawan.="<option value=" . $bar1->karyawanid . ">" . $bar1->namakaryawan . "</option>";
    $nama[$bar1->karyawanid] = $bar1->namakaryawan;
}
$strKom = "select * from " . $dbname . ".sdm_ho_component where id in('14','24')";
$optKomponen = '';
$resKom = $owlPDO->query($strKom) or die(print " Gagal: " . PDOException::getMessage());
$resKom->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $resKom->fetch()) {
    $optKomponen.="<option value=" . $bar1->id . ">" . $bar1->name . "</option>";
}

echo"<fieldset style='width:500px;'><table>
     <tr>
	 	<td>" . $_SESSION['lang']['periodegaji'] . "</td>
	 	<td><select id=\"periodegaji\" name=\"periodegaji\" style=\"width:150px;\" onchange=showPremi1(this.options[this.selectedIndex].value)>" . $optPeriode . "</select></td>
	 </tr>
	 <tr>
	 	<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
	 	<td><select id=\"idkaryawan\" name=\"idkaryawan\" style=\"width:150px\">" . $optIdKaryawan . "</select></td>
	 </tr>
	 <tr>
	 	<td>" . $_SESSION['lang']['rapel'] . "</td>
		<td><input type=text id=upahpremi size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=10 value=0 style=\"width:150px\"></td>
	 </tr>
     <tr>
	 	<td>" . $_SESSION['lang']['komponenpayroll'] . "</td>
	 	<td><select id=\"komponenpayroll\" name=\"komponenpayroll\" style=\"width:150px\">" . $optKomponen . "</select></td>
	 </tr>
	 </table>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=simpanJ()>" . $_SESSION['lang']['save'] . "</button>
	 <button class=mybutton onclick=cancelJ()>" . $_SESSION['lang']['cancel'] . "</button>
	 </fieldset>";


echo open_theme($_SESSION['lang']['list']);

$strJ = "select * from " . $dbname . ".sdm_5jabatan";
$resJ = $owlPDO->query($strJ) or die(print " Gagal: " . PDOException::getMessage());
$resJ->setFetchMode(PDO::FETCH_OBJ);
while ($barJ = $resJ->fetch()) {
    $jab[$barJ->kodejabatan] = $barJ->namajabatan;
}
echo "<div>";
$strRes = "select a.*, b.kodejabatan, b.lokasitugas from " . $dbname . ".sdm_gaji a 
	left join " . $dbname . ".datakaryawan b
	on a.karyawanid = b.karyawanid
	where a.idkomponen in ('14','24') and  b.lokasitugas = '" . $_SESSION['empl']['lokasitugas'] . "'
	order by a.karyawanid";
echo"" . $_SESSION['lang']['periode'] . " : " . "<select id=periodegaji2 style='width:200px;' onchange=showPremi2(this.options[this.selectedIndex].value)>" . $optPeriode . "</select>";
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader>
		    <td style='width:150px;'>" . $_SESSION['lang']['namakaryawan'] . "</td>
			<td>" . $_SESSION['lang']['jabatan'] . "</td>
			<td>" . $_SESSION['lang']['periode'] . "</td>
			<td>" . $_SESSION['lang']['upahpremi'] . "</td>
			<td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody id=container>";
		 echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>