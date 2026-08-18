<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src='js/keu_5akunbankv2.js?v=<?php echo time(); ?>'></script>
<?php
include('master_mainMenu.php');
$optOrg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where length(kodeorganisasi)=4 ORDER BY namaorganisasi";
$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($data = $res->fetch()) {
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['kodeorganisasi']." - ".$data['namaorganisasi']."</option>";
}
$optJenis = $optAkun =$optbank=$optmatauang=$optakuncoa= "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT * FROM ".$dbname.".keu_5akun where "
	." namaakun like '%bank%' and length(noakun)=7";
$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($data = $res->fetch()) {
	$optAkun.="<option value=".$data['noakun'].">".$data['namaakun']."</option>";
}

$sql = "SELECT * FROM ".$dbname.".setup_matauang";
$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($data = $res->fetch()) {
	$optmatauang.="<option value=".$data['kode'].">".$data['kode']." - ".$data['matauang']."</option>";
}

$sql = "SELECT * FROM ".$dbname.".keu_5daftarbank where "
	." status ='1'";
$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($data = $res->fetch()) {
	$optbank.="<option value=".$data['kodebank'].">".$data['namabank']."</option>";
}
$arrJenis = getEnum($dbname, 'keu_5akunbank', 'fungsi');
foreach($arrJenis as $kei => $fal) {
	$optJenis.="<option value='".$kei."'>".$fal."</option>";
}

$str = "SELECT * FROM ".$dbname.".keu_5akun where noakun like '111%' and namaakun like '%BANK%' and detail=1 and aktif=1";
$res=fetchdata($str);
foreach($res as $bar){
	$optakuncoa.="<option value=".$bar['noakun'].">".$bar['noakun']." - ".$bar['namaakun']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_5akunbankv2').'</span><br>');
echo"<fieldset style='float:left;'>
    <legend>".$_SESSION['lang']['entryForm']."</legend> 
            <table border=0 cellpadding=1 cellspacing=1>
                    <tr hidden>
                        <td>".$_SESSION['lang']['noakun']."</td>
                        <td>:</td>
                        <td><input type=text id=noakun></td>
                    </tr>
                    <tr>
                        <td>".$_SESSION['lang']['unit']."</td>
                        <td>:</td>
                        <td><select onchange=getbank(this.value) id=pt style=\"width:150px;\" >".$optOrg."</select></td>
                    
                    
                        <td>".$_SESSION['lang']['namabank']."</td>
                        <td>:</td>
                        <td><select id=bank style=\"width:150px;\" onchange=getinisialurut()>".$optbank."</select></td>
                    </tr>
					<tr>
                        <td>".$_SESSION['lang']['cabang']."</td>
                        <td>:</td>
                        <td><input type=text class=myinputtext id=cabang onkeydown=\"upperCaseF(this)\" onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
                    
                        <td>".$_SESSION['lang']['norek']."</td>
                        <td>:</td>
                        <td><input type=text class=myinputtext id=rek   onkeypress=\"return angka_doang(event);\" style=\"width:145px;\"></td>
                    </tr>
					<tr>
                        <td>".$_SESSION['lang']['atasnama']."</td>
                        <td>:</td>
                        <td><input type=text class=myinputtext id=atasnama   onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
                   
                        <td>".$_SESSION['lang']['matauang']."</td>
                        <td>:</td>
                        <td><select id=matauang style=\"width:150px;\" >".$optmatauang."</select></td>
                    </tr>
					<tr>
                        <td>Swift Code</td>
                        <td>:</td>
                        <td><input type=text class=myinputtext id=swift_code onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
                    
                        <td>".$_SESSION['lang']['fungsirekening']."</td>
                        <td>:</td>
                        <td><select id=fungsi style=\"width:150px;\" >".$optJenis."</select></td>
                    </tr>
					<tr>
                        <td>Email</td>
                        <td>:</td>
                        <td><input type=text class=myinputtext onblur=emailCheck(this.value) id=email onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
						
						<td>".$_SESSION['lang']['inisial']." ".$_SESSION['lang']['nourut']."</td>
                        <td>:</td>
                        <td><input type=text class=myinputtext id=inisialurut onkeypress=\"return tanpa_kutip(event);\" style=\"width:145px;\"></td>
                    
						
					</tr>
                    <tr>
                        <td>".$_SESSION['lang']['noakun']."</td>
                        <td>:</td>
                        <td><select  id=noakuncoa style=\"width:150px;\" >".$optakuncoa."</select></td>
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
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td><select onchange='enterkey(event,loadData)' id=unitsch style=\"width:150px;\" >".$optOrg."</select></td>
				<td>".$_SESSION['lang']['namabank']."</td>
				<td>:</td>
				<td><select id=banksch onchange='enterkey(event,loadData)' style=\"width:150px;\" >".$optbank."</select></td>
				<td>".$_SESSION['lang']['norek']."</td>
				<td>:</td>
				<td><input type=text class=myinputtext id=reksch  onkeypress='enterkey(event,loadData)' style=\"width:145px;\"></td>
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