<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/pabrikasi_verso.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php

$optpt=$optcus=$optsatsch=$optsales="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optpt.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

$str="select kodecustomer,namacustomer from ".$dbname.".pmn_4customer ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optcus.="<option value=" . $bar['kodecustomer'] . ">" . $bar['namacustomer'] . "</option>";
}

$str="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan where tanggalkeluar!='0000-00-00'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optsales.="<option value=" . $bar['karyawanid'] . ">" . $bar['namakaryawan'] . " - " . $bar['nik'] . "</option>";
}

$optsatsch.="<option value='0'>Waiting</option>";
$optsatsch.="<option value='1'>Open</option>";
$optsatsch.="<option value='2'>Cancel</option>";
$optsatsch.="<option value='3'>Close</option>"; 


if($_SESSION['language']=='ID'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Verifikasi Sales Order').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('Verifikasi Sales Order').'</span>');
}



echo"<br /><fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['find']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['kodept']."</td>
					<td>:</td>
					<td><select id=kdptsch style=\"width:150px;\">'".$optpt."'</select></td>
					
					<td>".$_SESSION['lang']['nmcust']."</td>
					<td>:</td>
					<td><select id=kdcussch style=\"width:150px;\">'".$optcus."'</select>
					<img id='kdcussch' onclick=z.elSearch('kdcussch',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr> 
				<tr>
					<td>".$_SESSION['lang']['kodesalesorder']."</td>
					<td>:</td>
					<td><input type=text id=kdsosch size=4 class=myinputtext maxlength=4 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
					
					<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['order1']."</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext id=tglsch readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kodebarang']."</td>
					<td>:</td>
					<td><input type=text disabled id=kdbrgsch size=10 class=myinputtext maxlength=50 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\">
					<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=tambahBarang('".$_SESSION['lang']['find']."',event)>
					</td>
					<td>".$_SESSION['lang']['nopo']." ".$_SESSION['lang']['customer']."</td>
					<td>:</td>
					<td><input type=text id=noposch onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['namabarang']."</td>
					<td>:</td>
					<td><input type=text id=nmbrgsch disabled size=10 class=myinputtext maxlength=50 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
					<td>Sales</td>
					<td>:</td>
					<td><select id=salesidsch style=\"width:150px;\">'".$optsales."'</select>
					<img id='salesidsch' onclick=z.elSearch('salesidsch',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']."</td>
					<td>:</td>
					<td><select id=statussch style=\"width:150px;\">'".$optsatsch."'</select></td>
				</tr>
			
				
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=loaddata()>".$_SESSION['lang']['find']."</button>
					<button class=mybutton onclick=hapus()>".$_SESSION['lang']['cancel']."</button></td>
				</tr>
			</table></fieldset>";
					
//	<input type=hidden id=method value='insert'>	
					
CLOSE_BOX();
?>

<?php
OPEN_BOX();
echo "<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
            <thead>
               <tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['pt']."</td>    
					<td align=center>".$_SESSION['lang']['kodesalesorder']."</td>
					<td align=center>".$_SESSION['lang']['kodebarang']."</td>    
					<td align=center>".$_SESSION['lang']['namabarang']."</td> 
					<td align=center>Qty</td> 
					<td align=center>".$_SESSION['lang']['keterangan']."</td> 
					<td align=center>".$_SESSION['lang']['kodecustomer']."</td>
					<td align=center>".$_SESSION['lang']['nopo']."</td>
					<td align=center> ".$_SESSION['lang']['tanggal']."  ".$_SESSION['lang']['order1']."</td>
					<td align=center>Sales</td>
					
					<td align=center width=100px>".$_SESSION['lang']['action']."</td>
				 </tr>
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
                
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>

