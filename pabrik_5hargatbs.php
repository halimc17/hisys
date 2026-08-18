<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/pabrik_5hargatbs.js'></script>



<?php
include('master_mainMenu.php');			
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK' ORDER BY kodeorganisasi";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch()){
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}	

$optsup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$ha="SELECT namasupplier,`supplierid`,`kodetimbangan` FROM ".$dbname.".log_5supplier WHERE status='1' and kodetimbangan!='' order by namasupplier asc";
$hi=$owlPDO->query($ha) or die(print " Gagal: ".PDOException::getMessage());
$hi->setFetchMode(PDO::FETCH_ASSOC);
while($hu=$hi->fetch())
{
	$optsup.="<option value=".$hu['kodetimbangan'].">".$hu['namasupplier']."</option>";
}

$optbjr="<option value=1>3 - 5</option>";
$optbjr.="<option value=2>5 - 7</option>";		
$optbjr.="<option value=3>> 7</option>";	

$optorgsort=$optpersort=$optsupsort="";

?>


<?php
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.strtoupper('FFB PRICE').'<br></span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('HARGA TBS').'<br></span>');
}
echo"<fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
                                <tr>
                                    <td>".$_SESSION['lang']['pabrik']."</td>
                                    <td>:</td>
                                    <td colspan=4><select id=kdorg style=\"width:135px;\" >".$optOrg."</select></td>
				</tr>
				<tr>
                                    <td>".$_SESSION['lang']['supplier']." TBS</td>
                                    <td>:</td>
                                    <td colspan=4><select id=kodesupplier style=\"width:135px;\">".$optsup."</select></td>
				</tr>
				<tr>
                                    <td>".$_SESSION['lang']['tanggal']."</td>
                                    <td>:</td>
                                    <td colspan=4><input type='text' class='myinputtext' id='tgl' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:70px; /></td>
				</tr>
				<tr>
                                    <td width=100>".$_SESSION['lang']['tahuntanam']."</td>
                                    <td>:</td>
                                    <td><input type=text id=thntnm size=4 class=myinputtextnumber maxlength=4 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:30px;\"></td>
				
                                    <td>".$_SESSION['lang']['harga']."</td>
                                    <td>:</td>
                                    <td><input type=text id=harga size=10 class=myinputtextnumber maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
				</tr>
				<tr>
                                    <td></td><td></td>
                                    <td colspan=5><button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                                    <button class=mybutton onclick=hapus()>".$_SESSION['lang']['cancel']."</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>
					<input type=hidden id=oldtgl value='insert'>
					<input type=hidden id=oldbjr value='insert'>
					<input type=hidden id=oldkdorg value='insert'>";
					
	echo"<fieldset style='float:left;'>
			<legend>Filter</legend> 
				<table border=0 cellpadding=1 cellspacing=1>
					<tr>
						<td>".$_SESSION['lang']['pabrik']."</td>
						<td>:</td><td> <select id='kdorgsort' style='width:150px;' onchange='ubah_list()'>".$optorgsort."</select></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['periode']."</td>
						<td>:</td><td> <select id='periodesort' style='width:150px;' onchange='ubah_list()'>".$optpersort."</select></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['supplier']."</td>
						<td>:</td><td> <select id='suppsort' style='width:150px;' onchange='ubah_list()'>".$optsupsort."</select></td>
					</tr>
					<tr><td><td>
					<td colspan=5><button class=mybutton onclick=loadData()>".$_SESSION['lang']['all']."</button></td></tr>
					
				</table></fieldset>";
					
CLOSE_BOX();
?>

<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>

