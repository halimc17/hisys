<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/pabrik_analisaproduksi.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	

$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK'"; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";  
}


OPEN_BOX('','<span class=judul>'.getMenu('pabrik_analisaproduksi').'</span><br>');
echo"<div id=formhead style=display:none>";
echo "<fieldset style='float:left;width:350px'><legend><b>Form</b></legend>
		<table border=0 cellpadding=1 cellspacing=1 width:550px>
				<tr>
		     <td>
			    ".$_SESSION['lang']['unit']."
			 </td>
			 <td>:</td>
		     <td>
			    <select id=kodeorg style=\"width:125px;\">".$optorg."</select>
			 </td>
		   </tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type='text' class='myinputtext' id='tanggal' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:125px; /></td>
				</tr>
				
				<tr>
					<td colspan=3>
						<fieldset><legend>".$_SESSION['lang']['cpo']."</legend>
						 
						 <table>
						 
						 <tr>
							 <td>
								FFa
							 </td>
							 <td>:</td>
							 <td>
								<input type=text id=ffacpo value=0 onblur=periksaCPO(this)   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
							 </td>			 
						 </tr>
						 <tr>
							 <td>
								".$_SESSION['lang']['kadarair']."
							 </td>
							 <td>:</td>
							 <td>
								<input type=text id=kadaraircpo value=0 onblur=periksaCPO(this)   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
							 </td>
						 </tr>
						<tr>
							 <td>
								".$_SESSION['lang']['kotoran']."
							 </td>
							 <td>:</td>
							 <td>
								<input type=text id=dirtcpo value=0 onblur=periksaCPO(this)   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
							 </td>
						 </tr>	
						 <tr>
							 <td>
								Dobi
							 </td>
							 <td>:</td>
							 <td>
								<input type=text id=usbcpo  value=0   class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\">%. 
							 </td>			 
						 </tr>		   	   
						</table>
						</fieldset>
					</td>
				
				
				
					<td valign=top>
						<fieldset style=width:175px;><legend>".$_SESSION['lang']['kernel']."</legend>
							<table>
							  <tr>
								 <td>".$_SESSION['lang']['kadarair']."</td>
								 <td>:</td>
								 <td>
									<input type=text id=kadarairpk  value=0 class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
								 </td>
							 </tr>
							 <tr>
								 <td>
									".$_SESSION['lang']['kotoran']."
								 </td>
								 <td>:</td>
								 <td>
									<input type=text id=dirtpk  value=0  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
								 </td>
							 </tr>		
							 <tr style='display:none'>
								 <td>
									Broken
								 </td>
								 <td>:</td>
								 <td>
									<input type=text id=ffapk  value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
								 </td>			 
							 </tr>		 
							</table>
						</fieldset>
					</td>
				</tr>
				
				<tr>
					<td></td><td></td><br />
					<td><br /><button class=mybutton onclick=simpan()>Simpan</button>
					<button class=mybutton onclick=hapus()>Batal</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>
	</fieldset>";
	echo"</div>";
	echo"<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['list']."</b></legend>
		<table border=0 cellpadding=1 cellspacing=1>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td><input type='text' class='myinputtext' id='tglSch' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:125px; /></td>
				<td><button class=mybutton onclick=cari()>".$_SESSION['lang']['find']."</button>&nbsp;<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button></td>
			</tr>
		</table>
		<div id=container> 
			<script>loadData()</script>
		</div>
		</fieldset>";

	CLOSE_BOX();					
?>