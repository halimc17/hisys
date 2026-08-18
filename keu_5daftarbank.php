<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_5daftarbank.js'></script>
<?php
include('master_mainMenu.php');
$optOrg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where length(kodeorganisasi)=4 ORDER BY namaorganisasi";
$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($data = $res->fetch()) {
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['kodeorganisasi']." - ".$data['namaorganisasi']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_5daftarbank').'</span><br>');
echo"<fieldset>
    <legend>".$_SESSION['lang']['entryForm']."</legend> 
            <table border=0 cellpadding=1 cellspacing=1>
                    <tr>
                        <td>Kode ".$_SESSION['lang']['bank']."</td>
                        <td>:</td>
                        <td><input disabled type=text class=myinputtext id=kodebank onkeydown=\"upperCaseF(this)\" onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
                    </tr>
					<tr>
                       <td>".$_SESSION['lang']['namabank']."</td>
                        <td>:</td>
                        <td><input onkeyup=getkodebank() type=text class=myinputtext id=bank onkeydown=\"upperCaseF(this)\" onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
                    </tr>
                    <tr hidden>
                        <td>Jlh ".$_SESSION['lang']['hari']." Loan</td>
                        <td>:</td>
                        <td><input type=text class=myinputtextnumber id=jumlah_hari   onkeypress=\"return angka_doang(event)\" style=\"width:145px;\"></td>
                    </tr>
                    <tr hidden>
                        <td>Jlh ".$_SESSION['lang']['hari']." Deposito</td>
                        <td>:</td>
                        <td><input type=text class=myinputtextnumber id=jumlah_hari2   onkeypress=\"return angka_doang(event)\" style=\"width:145px;\"></td>
                    </tr>
					<tr>
                        <td>".$_SESSION['lang']['inisial']."</td>
                        <td>:</td>
                        <td><input type=text class=myinputtext id=inisial onkeydown=\"upperCaseF(this)\" onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
                    </tr>
                    <tr>
                        <td></td><td></td>
                        <td><button class=mybutton onclick=simpan()>Simpan</button>
                        <button class=mybutton onclick=hapus()>Batal</button></td>
                    </tr>
            </table></fieldset>
			<input type=hidden id=method value='insert'>";
CLOSE_BOX();
OPEN_BOX();
#ISI UNTUK DAFTAR 
echo "<fieldset >
		<legend>".$_SESSION['lang']['list']."</legend>
		<table border=0 style='display: inline-block;vertical-align:top'>
		<td>
			<fieldset style=float:left>
			<legend>".$_SESSION['lang']['find']."</legend>
			<table>
				<td>".$_SESSION['lang']['namabank']."</td>
				<td>:</td>
				<td><input type=text class=myinputtext id=banksch  onkeypress='enterkey(event,loadData)' style=\"width:145px;\"></td>
				<td><button class=mybutton onclick=loadData()>Search</button></td>
				<td><button class=mybutton onclick=hapussch()>Cancel</button></td>
			</table>
			</fieldset>
		</td></table>
			<div id=container style='overflow:auto;height:350px;max-width:auto';> 
				<script>loadData()</script>
			</div>
	 </fieldset>";
CLOSE_BOX();
echo close_body();					
?>