<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
$frm[0] = '';
$frm[1] = '';
$frm[2] = '';
?>
<script type="text/javascript" src="js/sdm_perumahan.js"></script>
<?php
$soptOrg = '';
$sorg = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where
      tipe in('KEBUN','KANWIL','PABRIK')
      order by kodeorganisasi";
$qorg = $owlPDO->query($sorg) or die(print " Gagal: " . PDOException::getMessage());
$qorg->setFetchMode(PDO::FETCH_ASSOC);
global $kd_org;
while ($rorg = $qorg->fetch()) {
	// '".($rorg['kodeorganisasi']==$rest['kodeorganisasi']?'selected=selected':'')."'
	$kd_org = $rorg['kodeorganisasi'];
	$soptOrg .= "<option  value=" . $rorg['kodeorganisasi'] . " >" . $rorg['namaorganisasi'] . "</option>";
}


$optKondisi = "<option value='B-BD'>B-BD:Baik bisa dipakai</option>";
$optKondisi .= "<option value='B-TD'>B-TD:Baik tida dipakai</option>";
$optKondisi .= "<option value='R-BD'>R-BD:Rusak Bisa dipakai</option>";
$optKondisi .= "<option value='R-TD'>R-TD:Rusak tidak dipakai</option>";

$optPintuRumah = "<option value='PERMANEN'>PERMANEN</option>";
$optPintuRumah .= "<option value='SEMIPERMANEN'>SEMI-PERMANEN</option>";

$optThn = '';
$thn_skrng = intval(date("Y"));
for ($i = $thn_skrng; $i >= ($thn_skrng - 30); $i--) {
	//echo "warning".$i;exit();
	$optThn .= "<option value='" . $i . "'>" . $i . "</option>";
}

$opt_tipe_rmh = '';
$str = "select jenis,nama from " . $dbname . ".sdm_5jenis_prasarana where jenis like 'R%' order by nama";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$opt_tipe_rmh .= "<option value='" . $bar->jenis . "'>" . $bar->jenis . ": " . $bar->nama . "</option>";
}
$opt_kompleks = '';
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe in('KEBUN','AFDELING','PABRIK', 'TRAKSI') order by kodeorganisasi";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$opt_kompleks .= "<option value='" . $bar->kodeorganisasi . "'>{$bar->kodeorganisasi} - " . $bar->namaorganisasi . "</option>";
}

OPEN_BOX('', '<span class=judul>' . strtoupper($_SESSION['lang']['manajemenperumahan']) . '</span>');
$thn_skrg = "";
$frm[0] .= "<fieldset><legend>" . $_SESSION['lang']['data_rmh'] . "" . $thn_skrg . "</legend>";
$frm[0] .= "<table cellspacing=1 border=0>
<tr><td>" . $_SESSION['lang']['unitkerja'] . "</td><td>:</td><td>
<select id=kode_org name=kode_org onChange=load_data() style=width:200px;><option value=></option>" . $soptOrg . "</select></td></tr>
<tr><td>" . $_SESSION['lang']['komplek_rmh'] . "</td><td>:</td><td>
<select id=nm_kompleks style='width:200px;'>" . $opt_kompleks . "</select>    
</td></tr>
<tr><td>Blok Rumah</td><td>:</td><td>
<input type=text class=myinputtext id=blok_rmh name=blok_rmh maxlength=2 onkeypress=\"return tanpa_kutip(event);\" style=width:200px; /></td></tr>
<tr><td>" . $_SESSION['lang']['no_rmh'] . "</td><td>:</td><td>
<input type=text class=myinputtext id=no_rmh name=no_rmh maxlength=4 onkeypress=\"return angka_doang(event);\" style=width:200px; /></td></tr>
<tr><td>" . $_SESSION['lang']['tipe_rmh'] . "</td><td>:</td><td>
<select id=tipe_rmh style='width:200px;'>" . $opt_tipe_rmh . "</select>    
</td></tr>
<tr><td>" . $_SESSION['lang']['thn_bgn_rmh'] . "</td><td>:</td><td><select id=thn_buat_rmh name=thn_buat_rmh style='width:200px;'>" . $optThn . "</select>
<!--<input type=text class=myinputtext id=thn_buat_rmh name=thn_buat_rmh maxlength=45  style='width:200px;'onkeypress=\"return angka_doang(event);\" />--></td></tr>
<tr><td>" . $_SESSION['lang']['knds_rmh'] . "</td><td>:</td><td><select id=kndsi_rmh name=kndsi_rmh style=width:200px;>" . $optKondisi . "</select></td></tr>
<tr><td>Jumlah Pintu</td><td>:</td><td><input type='text' class='myinputtextnumber' id=pintu_rmh name=pintu_rmh maxlength=4 onkeypress=\"return angka_doang(event);\" style=width:200px; /></td></tr>
<tr><td>" . $_SESSION['lang']['note'] . "</td><td>:</td><td>
<input type=text class=myinputtext id=ket_rmh name=ket_rmh maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style=width:200px; /></td></tr>
<tr><td>" . $_SESSION['lang']['alamat'] . "</td><td>:</td><td>
<input type=text class=myinputtext id=almt_rmh name=almt_rmh maxlength=60 onkeypress=\"return tanpa_kutip(event);\" style=width:200px; /></td></tr>
<tr><td colspan=3>
<button class=mybutton id=save_kepala name=save_kepala onclick=save_header() >" . $_SESSION['lang']['save'] . "</button>
<button class=mybutton id=cancel_kepala name=cancel_kepala onclick=clear_save_form() >" . $_SESSION['lang']['cancel'] . "</button>
</table>";
$frm[0] .= "</fieldset>";
$frm[0] .= "<fieldset><legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
         <table class=sortable cellspacing=1 border=0>
		<thead>
		<tr class=\"rowheader\">
		<td>No.</td>
		<td>" . $_SESSION['lang']['kodeorg'] . "</td>
		<td>" . $_SESSION['lang']['lokasi'] . "</td>                    
		<td>" . $_SESSION['lang']['blok'] . "</td>
		<td>" . $_SESSION['lang']['no_rmh'] . "</td>
		<td>" . $_SESSION['lang']['tipe_rmh'] . "</td>
		<td>" . $_SESSION['lang']['thn_bgn_rmh'] . "</td>
		<td>" . $_SESSION['lang']['knds_rmh'] . "</td>
		<td>Jumlah Pintu</td>
		<td>" . $_SESSION['lang']['note'] . "</td>
		<td>Action</td>
		</tr></thead><tbody id=contain>
		
		";
$frm[0] .= "</tbody></table></fieldset>";

//assseettt
$optAset = '';


$frm[1] .= "<fieldset><legend>" . $_SESSION['lang']['data_rmh'] . "</legend>";
$frm[1] .= "<table cellspacing=1 border=0>
<tr><td>" . $_SESSION['lang']['unitkerja'] . "</td><td>:</td><td><select id=kode_org_asset name=kode_org_asset onchange='get_blok();' style=width:235px><option value=></option>" . $soptOrg . "</select></td></tr>
<tr><td>" . $_SESSION['lang']['blok'] . "</td><td>:</td><td>
<select id=blok_rmh_asset name=blok_rmh_asset onchange=get_normh('0','0') style=width:235px></select>
<!--<input type=text class=myinputtext id=blok_rmh_asset name=blok_rmh_asset maxlength=4 onkeypress=\"return tanpa_kutip(event);\"/>--></td></tr>
<tr><td>" . $_SESSION['lang']['no_rmh'] . "</td><td>:</td><td>
<select id=no_rmh_asset name=no_rmh_asset style=width:235px></select>
<!--<input type=text class=myinputtext id=no_rmh_asset name=no_rmh_asset maxlength=4 onkeypress=\"return angka_doang(event);\" />--></td></tr>
<tr><td>" . $_SESSION['lang']['namaaset'] . "</td><td>:</td><td><select id=kode_asset name=kode_asset style=width:235px><option value=></option>" . $optAset . "</select></td></tr>
<input type=hidden id=oldasset value='' />
<tr><td colspan=3><button class=mybutton onclick=save_asset() >" . $_SESSION['lang']['save'] . "</button><button class=mybutton onclick=clear_save_form_asset() >" . $_SESSION['lang']['cancel'] . "</button>
</table>";

$frm[1] .= "</fieldset>";
$frm[1] .= "<fieldset><legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
        <table class=sortable cellspacing=1 border=0>
		<thead>
		<tr class=\"rowheader\">
		<td>No.</td>
		<td>" . $_SESSION['lang']['kodeorg'] . "</td>
		<td>" . $_SESSION['lang']['blok'] . "</td>
		<td>" . $_SESSION['lang']['no_rmh'] . "</td>
		<td>" . $_SESSION['lang']['namaaset'] . "</td>
		<td>Action</td>
		</tr></thead><tbody id=containasset>
		";
$frm[1] .= "</tbody></table></fieldset>";

//karyawan
$lksiTugas = $_SESSION['empl']['lokasitugas'];

$frm[2] .= "<fieldset><legend>" . $_SESSION['lang']['data_rmh'] . "</legend>";
$frm[2] .= "<table cellspacing=1 border=0>
<tr><td>" . $_SESSION['lang']['kodeorg'] . "</td><td>:</td><td><select id=kode_org_penghuni name=kode_org_penghuni style=width:200px onchange=get_blok_penghuni()><option value=></option>" . $soptOrg . "</select></td></tr>
<tr><td>" . $_SESSION['lang']['blok'] . "</td><td>:</td><td>
<select id=blok_rmh_penghuni name=blok_rmh_penghuni style=width:200px onchange=get_normh_penghuni('0','0')></select>
<!--<input type=text class=myinputtext id=blok_rmh_penghuni name=blok_rmh_penghuni maxlength=4 onkeypress=\"return tanpa_kutip(event);\"/>--></td></tr>
<tr><td>" . $_SESSION['lang']['no_rmh'] . "</td><td>:</td><td>
<select id=no_rmh_penghuni name=no_rmh_penghuni style=width:200px></select>
<!--<input type=text class=myinputtext id=no_rmh_penghuni name=no_rmh_penghuni maxlength=4 onkeypress=\"return angka_doang(event);\" />--></td></tr>
<tr><td>" . $_SESSION['lang']['namakaryawan'] . "</td><td>:</td><td><select id=kode_karyawan name=kode_karyawan style=width:200px></select></td></tr>

<tr><td colspan=3><button class=mybutton onclick=save_penghuni() >" . $_SESSION['lang']['save'] . "</button><button class=mybutton onclick=clear_save_form_penghuni() >" . $_SESSION['lang']['cancel'] . "</button>
</table>";

$frm[2] .= "</fieldset>";
$frm[2] .= "<fieldset><legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
      <table class=sortable cellspacing=1 border=0>
		<thead>
		<tr class=\"rowheader\">
		<td>No.</td>
		<td>" . $_SESSION['lang']['kodeorg'] . "</td>
		<td>" . $_SESSION['lang']['blok'] . "</td>
		<td>" . $_SESSION['lang']['no_rmh'] . "</td>
		<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
		<td>Action</td>
		</tr></thead><tbody id=containpenghuni>
		";
$frm[2] .= "</tbody></table></fieldset>";

//========================
$hfrm[0] = $_SESSION['lang']['rumah'];
$hfrm[1] = $_SESSION['lang']['prabot'];
$hfrm[2] = $_SESSION['lang']['penghuni'];
//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 100, 900);
//===============================================	
?>


<?php
CLOSE_BOX();
echo close_body();
?>