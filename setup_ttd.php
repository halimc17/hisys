<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/setup_ttd.js'></script>



<?php
include('master_mainMenu.php');			
$optkar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT karyawanid,namakaryawan FROM ".$dbname.".datakaryawan 
        where tipekaryawan in ('0','7','8') and tanggalkeluar='0000-00-00' order by namakaryawan asc";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch()){
	$optkar.="<option value=".$data['karyawanid'].">".$data['namakaryawan']."</option>";
}


	OPEN_BOX('','<span class=judul>'.getMenu('setup_ttd').'<br></span>');
echo"<fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['karyawan']."</td>
					<td>:</td>
					<td colspan=4><select id=kar style=\"width:135px;\" >".$optkar."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kode']."</td>
					<td>:</td>
					<td><input type=text id=kdpo class=myinputtext  maxlength=4 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:105px;\" /></td>
				
				</tr>
				<tr>
					<td>Signature</td>
					<td>:</td>
					<td><input name=fileupload type=file id=fileupload size=1 class=mybutton style=width:160px>
				</td>
				</tr>
				
				<tr>
					<td></td><td></td>
					<td colspan=5><button class=mybutton onclick=savefile()>".$_SESSION['lang']['save']."</button>
					<button class=mybutton onclick=hapus()>".$_SESSION['lang']['cancel']."</button></td>
				</tr>
			</table></fieldset>";
					
echo"<fieldset style='width:250px;height:100px'>
	 <legend>Note</legend>
	 <table>
	 <tr><td>Kode</td><td></td><td>Keterangan</td></tr>
	 <tr><td>PO1</td><td>:</td><td>Mengetahui</td></tr>
	 <tr><td>PO3</td><td>:</td><td>Menyetujui</td></tr>
	 </table>
	</fieldset>";
CLOSE_BOX();
?>

<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset style='width:520px;'>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loaddata()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>

