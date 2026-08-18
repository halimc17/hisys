<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/pabrikasi_verrab.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php

$optpt=$optcus=$optsales=$optsatsch="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

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
	OPEN_BOX('','<span class=judul>'.strtoupper('Verifikasi RAB').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('Verifikasi RAB').'</span>');
}



echo"<br /><fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['find']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				
				<tr>
					<td>".$_SESSION['lang']['kodepabrikasi']."</td>
					<td>:</td>
					<td><input type=text id=kdpabsch size=4 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:125px;\"></td>	
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kodesalesorder']."</td>
					<td>:</td>
					<td><input type=text id=kdsosch size=4 class=myinputtext onkeypress=\"return tanpa_kutip(event);\"  style=\"width:125px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']."</td>
					<td>:</td>
					<td><select id=statussch style=\"width:130px;\">'".$optsatsch."'</select></td>
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
echo "<fieldset style=float:left>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable >
            <thead>
               <tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['kodepabrikasi']."</td>    
					<td align=center>".$_SESSION['lang']['namapabrikasi']."</td>    
					<td align=center>".$_SESSION['lang']['kodesalesorder']."</td>
					<td align=center>".$_SESSION['lang']['print']."</td>
					<td width=150px align=center>".$_SESSION['lang']['action']."</td>
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

