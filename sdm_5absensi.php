<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/sdm_5absensi.js?v=<?php echo time(); ?>></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5absensi').'</span><br>');

echo"<fieldset style='float:left;'><table>
     <tr><td>" . $_SESSION['lang']['kodeabs'] . "</td><td><input type=text id=kode size=3 style='width:200px;' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=3></td></tr>
	 <tr><td>" . $_SESSION['lang']['keterangan'] . "</td><td><input type=text id=keterangan  style='width:200px;' size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=50></td></tr>
	 <tr><td>" . $_SESSION['lang']['grup'] . "</td><td><select id=grup style='width:204px;' ><option value=0>" . $_SESSION['lang']['tidakdibayar'] . "</option><option value=1>" . $_SESSION['lang']['dibayar'] . "</option></select></td></tr>
     <tr><td>" . $_SESSION['lang']['jumlahhk'] . "</td><td><input type=text id=jumlahhk  style='width:200px;' size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=3 value=0 onblur=change_number(this)></td></tr>
     <tr><td>Pengali Uang Makan dan Transport</td><td><input type=text id=pengali  style='width:200px;' size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=3 value=0 onblur=change_number(this)></td></tr>
     <tr><td>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['premi'] . "</td><td><input type=checkbox id=status  title='Aktif/Tidak Aktif' checked> Aktif/Tidak Aktif</td></tr>
     <tr><td>" . $_SESSION['lang']['potongan'] . "</td><td><input type=checkbox id=potongan  title='Aktif/Tidak Aktif'> Potongan/Tidak Potongan</td></tr>
     <tr><td>Validasi Dokumen</td><td><input type=checkbox id=validasiDokumen  title='Aktif/Tidak Aktif'> <label for=validasiDokumen>Aktif/Tidak Aktif</label></td></tr>
	 </table>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=simpanJ()>" . $_SESSION['lang']['save'] . "</button>
	 <button class=mybutton onclick=cancelJ()>" . $_SESSION['lang']['cancel'] . "</button>
	 </fieldset><br>";
CLOSE_BOX();
OPEN_BOX();	 
echo open_theme($_SESSION['lang']['availvhc']);
echo "<div>";
$str1 = "select *,
	     case kelompok when 1 then '" . $_SESSION['lang']['dibayar'] . "'
		 when 0 then '" . $_SESSION['lang']['tidakdibayar'] . "'
		 end as ketgroup 
	     from " . $dbname . ".sdm_5absensi order by kodeabsen";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
$arrayStat = array('1'=> 'Aktif', '0'=> 'Tidak Aktif');
$arraypot = array('1'=> 'Ada Potongan', '0'=> 'Tidak Ada Potongan');
$arrayValDok = array('1'=> 'Aktif', '0'=> 'Tidak Aktif');
echo"<table class=sortable cellspacing=1 border=0 cellpadding=5>
	     <thead>
		 <tr class=rowheader>
		    <th>" . $_SESSION['lang']['kodeabs'] . "</th>
			<th>" . $_SESSION['lang']['keterangan'] . "</th>
			<th>" . $_SESSION['lang']['grup'] . "</th>
			<th>" . $_SESSION['lang']['jumlahhk'] . "</th>
			<th>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['premi'] . "</th>
			<th>" . $_SESSION['lang']['potongan'] . "</th>
			<th>Validasi Dokumen</th>
			<th style='width:30px;'>*</th></tr>
		 </thead>
		 <tbody id=container>";
while ($bar1 = $res1->fetch()) {
    echo"<tr class=rowcontent>
		           <td align=center>" . $bar1->kodeabsen . "</td>
				   <td>" . $bar1->keterangan . "</td>
				   <td>" . $bar1->ketgroup . "</td>
				   <td>" . $bar1->nilaihk . "</td>
				   <td>" . $arrayStat[$bar1->status] . "</td>
				   <td>" . $arraypot[$bar1->potongan] . "</td>
				   <td>" . $arrayValDok[$bar1->validasidokumen] . "</td>
				   <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->kodeabsen . "','" . $bar1->keterangan . "','" . $bar1->kelompok . "','" . $bar1->nilaihk . "','" . $bar1->pengali . "','" . $bar1->status . "','" . $bar1->potongan . "','" . $bar1->validasidokumen . "');\"></td></tr>";
}
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