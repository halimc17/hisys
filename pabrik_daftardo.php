<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/pabrik_daftardo.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

$opttipe=$optkomoditi=$optvhc=$optbrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}	

$str = "SELECT kodebarang,namabarang FROM ".$dbname.".log_5masterbarang where kelompokbarang='400'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optkomoditi.="<option value='".$bar['kodebarang']."'>".$bar['kodebarang']." - ".$bar['namabarang']."</option>";
}

$str = "SELECT kodebarang,namabarang FROM ".$dbname.".log_5masterbarang where namabarang like 'Ban %' and kodebarang not like '9%' order by kodebarang asc";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbrg.="<option value='".$bar['kodebarang']."'>".$bar['kodebarang']." - ".$bar['namabarang']."</option>";
}
//$opttipe="<option value=''>Pemakaian</option>";
$opttipe.="<option value='1'>Pemasangan</option>";
$opttipe.="<option value='2'>Pelepasan</option>";

?>


<?php
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>List DO</span>');
}else{
	OPEN_BOX('','<span class=judul>Daftar DO</span>');
	
}
echo "<br><br>";

echo"<fieldset style=\"width:550px;float:left\" >
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr hidden>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=pabrik style=\"width:150px;\" >".$optOrg."</select></td>
				</tr>
				<tr>
					<td width=100>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td><input type=text id=nodaftar disabled placeholder='otomatis saat simpan' size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type=text class=myinputtext id=tgl name=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:60px;  />
						</td>
				</tr>
				<tr>
					<td width=100>".$_SESSION['lang']['customer']."</td>
					<td>:</td>
					<td><input type=text id=cust size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td width=100>".$_SESSION['lang']['NoKontrak']."</td>
					<td>:</td>
					<td><input type=text id=nokontrak  size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td width=100>".$_SESSION['lang']['nodo']."</td>
					<td>:</td>
					<td><input type=text id=nodo size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['komoditi']."</td>
					<td>:</td>
					<td><select id=komoditi style=\"width:150px;\" >".$optkomoditi."</select></td>
				</tr>
				<tr>
					<td width=100>".$_SESSION['lang']['volumekontrak']."</td>
					<td>:</td>
					<td><input type=text id=volkontrak size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:105px;\" /> Kg</td>
				</tr>
				<tr>
					<td width=100>".$_SESSION['lang']['toleransipenyusutan']."</td>
					<td>:</td>
					<td><input type=text id=toleransi size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:105px;\" /> %</td>
				</tr>
				<tr>
					<td width=100>Keterangan</td>
					<td>:</td>
					<td><input type=text id=ket size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
				</tr>				
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=simpan()>Simpan</button>
					<button class=mybutton onclick=hapus()>Batal</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>";
		




		
    echo"<fieldset  fieldset style=\"float:left\"  >
                <legend>Find</legend> 
                        <table border=0 cellpadding=1 cellspacing=1>
							<tr>
								<td width=100>".$_SESSION['lang']['notransaksi']."</td>
								<td>:</td>
								<td><input type=text id=nodaftarsch  size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['tanggal']."</td>
								<td>:</td>
								<td><input type=text class=myinputtext id=tglsch name=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:60px;  />
									</td>
							</tr>
							<tr>
								<td width=100>".$_SESSION['lang']['NoKontrak']."</td>
								<td>:</td>
								<td><input type=text id=nokontraksch  size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
							</tr>
							<tr>
								<td width=100>".$_SESSION['lang']['nodo']."</td>
								<td>:</td>
								<td><input type=text id=nodosch size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['komoditi']."</td>
								<td>:</td>
								<td><select id=komoditisch style=\"width:150px;\" >".$optkomoditi."</select></td>
							</tr>
							<tr>
								<td colspan=2></td>
                                <td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>
                                     <button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button></td>
                            </tr>
                        </table></fieldset>";




					


//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo"<fieldset fieldset style=\"width:1000px;\" >
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData(0)</script>
		</div>
	</fieldset>";


CLOSE_BOX();
echo close_body();

?>