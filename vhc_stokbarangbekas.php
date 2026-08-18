<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/vhc_stokbarangbekas.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
    $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and tipe='WORKSHOP'";   
}else{
    $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='WORKSHOP'";   
}
$optOrg="";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch()){
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}	

$optBrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iSup="SELECT distinct(a.kodebarang) as kodebarang, b.namabarang, b.satuan FROM ".$dbname.".vhc_stokbarangbekas a left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang where inactive='0' order by namabarang asc";
$nSup=$owlPDO->query($iSup) or die(print " Gagal: ".PDOException::getMessage());
$nSup->setFetchMode(PDO::FETCH_ASSOC);
while($dSup=$nSup->fetch()){
    $optBrg.="<option value=".$dSup['kodebarang'].">".$dSup['kodebarang']." - ".$dSup['namabarang']."</option>";
}
	
?>
<?php
OPEN_BOX('','<span class=judul>'.getMenu('vhc_stokbarangbekas').'</span><br>');
echo "<fieldset style=float:left><legend>Form</legend>
		<table border=0 cellpadding=1 cellspacing=1>
		<tr>
			<td>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td>:</td>
			<td colspan=4><select id=kdOrg style=\"width:205px;\" >".$optOrg."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['namabarang']."</td>
			<td>:</td>
			<td  width=250px colspan=4><select onchange=getstok() id=kdBrg style=\"width:205px;\">".$optBrg."</select>
				<img id='kdBrg' onclick=z.elSearch('kdBrg',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td width=75px><input type='text' class='myinputtext' id='tgl' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;' maxlength='10' onchange=getstok() style=width:75px; /></td>

			<td width=30px>".$_SESSION['lang']['stok']."</td>
			<td width=5px>:</td>
			<td><input type=text id=saldo disabled class=myinputtextnumber style=\"width:75px;\"></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['keluar']."</td>
			<td>:</td>
			<td><input type=text id=keluar size=10 class=myinputtextnumber onkeyup=\"z.numberFormat('keluar',2);getsisa()\" value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:75px;\"></td>
			
			<td width=30px>".$_SESSION['lang']['sisa']."</td>
			<td width=5px>:</td>
			<td><input type=text id=sisa disabled class=myinputtextnumber style=\"width:75px;\"></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td>:</td>
			<td colspan=4><input type=text id=ket size=10 class=myinputtext maxlength=124 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:202px;\"></td>
		</tr>
		<tr>
			<td></td><td><input type=text id=tgljam style=display:none class=myinputtext</td>
			<td colspan=4><button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=hapus()>".$_SESSION['lang']['cancel']."</button></td>
		</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>
	</fieldset>";
	CLOSE_BOX();
	OPEN_BOX();

	echo"<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['list']."</b></legend>
		<table border=0 cellpadding=1 cellspacing=1>
			<tr>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>:</td>
				<td><select id='kdBrgSch' style='width:150px;'>".$optBrg."</select></td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td><input type='text' class='myinputtext' id='tglSch' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style=width:75px; /></td>
				<td><button class=mybutton onclick=cari()>".$_SESSION['lang']['find']."</button>&nbsp;<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button></td>
			</tr>
		</table>
		<div id=container> 
			<script>loadData()</script>
		</div>
		</fieldset>";

	CLOSE_BOX();					
?>