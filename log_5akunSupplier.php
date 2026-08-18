<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language='JavaScript1.2' src='js/supplier.js'></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['find'] . ' ' . $_SESSION['lang']['supplier'] . '/' . $_SESSION['lang']['kontraktor']).'</span>');
echo "<br>
		<fieldset><table border=0><tr><td><fieldset style=float:left>
		" . $_SESSION['lang']['nama'] . " : <input type=text class=myinputtext id=cari size=30 maxlength=30 onkeypress=\"return tanpa_kutip(event)\">
	      <button class=mybutton onclick=findSupplier()>" . $_SESSION['lang']['find'] . "</button>
		  </fieldset></td><td>Note : Cari nama supplier yang akan ditambahkan Nomor Rekening Bank kemudian click edit pada hasil pencarian, <br>lalu isikan detail Nomor Rekening dan keterangan lainnya pada form dibawah.</td></tr></table>";
echo"<fieldset>
	     <legend>" . $_SESSION['lang']['pilih'] . " " . $_SESSION['lang']['supplier'] . "</legend>
		 <div style='width=100%; height:200px;overflow:auto'>
	     <table class=sortable cellspacing=1 border=0 width=100%>
	     <thead>
		 <tr class=header>
	     <td align=center>" . $_SESSION['lang']['kodekelompok'] . "</td>
		 <td align=center>ID." . $_SESSION['lang']['supplier'] . "</td>
		 <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
		 <td align=center>" . $_SESSION['lang']['alamat'] . "</td>
		 <td align=center>" . $_SESSION['lang']['cperson'] . "</td>
		 <td align=center>" . $_SESSION['lang']['kota'] . "</td>
		 <td align=center>" . $_SESSION['lang']['telp'] . "</td>		 
		 <td align=center>" . $_SESSION['lang']['fax'] . "</td>		 
		 <td align=center>" . $_SESSION['lang']['email'] . "</td>		 
		 <td align=center>" . $_SESSION['lang']['npwp'] . "</td>	 
		 <td align=center>" . $_SESSION['lang']['plafon'] . "</td>
		 <td align=center>" . $_SESSION['lang']['noakun'] . "</td>
		 <td align=center>" . $_SESSION['lang']['akunpajak'] . "</td>
		 <td align=center>" . $_SESSION['lang']['noseripajak'] . "</td>
		 <td align=center>" . $_SESSION['lang']['namabank'] . "</td>
		 <td align=center>" . $_SESSION['lang']['norekeningbank'] . "</td>
		 <td align=center>" . $_SESSION['lang']['atasnama'] . "</td>
		 <td align=center>" . $_SESSION['lang']['nilaihutang'] . "</td>
		 <td align=center>" . $_SESSION['lang']['action'] . "</td>
		 </tr>
		 <tbody id=container>
		 </tbody>
		 <tfoot></tfoot>
		 </table>
		 </div>
		 </fieldset></fieldset>
		 ";

CLOSE_BOX();
OPEN_BOX('','<span class=judul>'.strtoupper("SUPPLIER / CONTRACTOR BANK ACCOUNTs").'</span>');

//akun ini hanya dibutuhkan jika setiap supplier memiliki akun sendiri-sendiri
//jika akun hutang supplier digabungkan, akun ini tidak perlu
if ($_SESSION['language'] == 'EN') {
    $zz = 'namaakun1 as namaakun';
} else {
    $zz = 'namaakun';
}
$str = "select noakun," . $zz . " from " . $dbname . ".keu_5akun where detail=1 and (noakun like '211%')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$opt = "<option value=''></option>";
while ($bar = $res->fetch()) {
    $opt.="<option value='" . $bar->noakun . "'>" . $bar->noakun . " - " . $bar->namaakun . "</option>";
}

//ambil no akun hutang pajak
$str1 = "select noakun," . $zz . " from " . $dbname . ".keu_5akun where detail=1 and (noakun like '212%')";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
$opt1 = "<option value=''></option>";
while ($bar1 = $res1->fetch()) {
    $opt1.="<option value='" . $bar1->noakun . "'>" . $bar1->namaakun . "</option>";
}
echo"<fieldset>
      <legend>Form</legend>
	  <table border=0>
	  <tr>
	      <td>ID " . $_SESSION['lang']['supplier'] . "</td><td>:</td><td><input style=width:100px  type=text class=myinputtext disabled id=idsupplier></td>
	      <td>" . $_SESSION['lang']['namabank'] . "</td><td>:</td><td><input type=text class=myinputtext id=bank onkeypress=\"return tanpa_kutip(event);\" size=30 maxlength=30></td>
	  </tr>	  
	  <tr>    
	     <td>" . $_SESSION['lang']['noakun'] . "</td><td>:</td><td><select  style=width:200px  id=noakun>" . $opt . "</select></td> 
                          <td>Bank Acc.No (No Rek)</td><td>:</td><td><input type=text class=myinputtext id=rek onkeypress=\"return tanpa_kutip(event);\" size=30 maxlength=30></td>	  
 	  </tr>
	  <tr>
	      <td>" . $_SESSION['lang']['namasupplier'] . "</td><td>:</td><td><input  style=width:195px  type=text class=myinputtext id=namasupplier onkeypress=\return tanpa_kutip(event);\" size=30 maxlength=30 disabled></td>
	      <td>A/c on Bhf (Bank A/N)</td><td>:</td><td><input type=text class=myinputtext id=an onkeypress=\"return tanpa_kutip(event);\" size=30 maxlength=30></td>
	  </tr>
	  <tr>
	      <td>" . $_SESSION['lang']['noakun'] . " " . $_SESSION['lang']['pajak'] . "</td><td>:</td><td><select  style=width:200px  id=akunpajak>" . $opt1 . "</select></td>
	      <td>" . $_SESSION['lang']['noseripajak'] . "</td><td>:</td><td><input type=text class=myinputtext id=noseripajak onkeypress=\"return tanpa_kutip(event);\" size=30 maxlength=30></td>
	  </tr>
	  <tr>
	      <td>" . $_SESSION['lang']['nilaihutang'] . "</td><td>:</td><td  colspan=3><input  style=width:100px  type=text  onblur=\"change_number(this);\"class=myinputtextnumber id=nilaihutang onkeypress=\"return angka_doang(event);\" size=15 maxlength=15 value=0></td>
	  </tr>
	  <tr><td><td><td>
	<button class=mybutton onclick=saveAkunSupplier()>" . $_SESSION['lang']['save'] . "</button>
	<button class=mybutton onclick=cancelAkunSupplier()>" . $_SESSION['lang']['cancel'] . "</button>	  
	  </td></td></td></tr></table></fieldset>";
?>
<?php

CLOSE_BOX();
echo close_body();
?>