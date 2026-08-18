<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript1.2 src='js/pabrikasi_3cutoff.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php


if($_SESSION['language']=='ID'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Tutup Buku / Cut off').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('cut off').'</span>');
}


$optkel=$optsat="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str="select distinct(kode) as kode,nama from ".$dbname.".pabrikasi_5kelompok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optkel.="<option value=" . $bar['kode'] . ">" . $bar['kode'] . " - " . $bar['nama'] . "</option>";
}

$str="select * from ".$dbname.".sdm_5tipekaryawan where id between 1 and 6 "; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $opttk.="<option value=" . $bar['id'] . ">" . $bar['tipe'] . "</option>";
}

$str="select * from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'"
        . " and length(kodeorganisasi)<=6   order by kodeorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $optdivisi.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

$str="select * from ".$dbname.".setup_kegiatan order by kodekegiatan asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $optkeg.="<option value=" . $bar['kodekegiatan'] . ">" . $bar['kodekegiatan'] . " - " . $bar['namakegiatan'] . "</option>";
}
		
$optsat.="<option value='0'>Waiting</option>";
$optsat.="<option value='1'>Open</option>";
$optsat.="<option value='2'>Cancel</option>";
$optsat.="<option value='3'>Close</option>"; 


$str="select distinct(substr(periode,1,4)) as tahun from ".$dbname.".keu_pdoht where kodeorg='".$_SESSION['empl']['lokasitugas']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optthnsch.="<option value='".$bar['tahun']."'>".$bar['tahun']."</option>";
}


?>

<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php

echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:70px;cursor:pointer;' onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	 
	echo"<table>";
	echo"
	<tr>
            <td>".$_SESSION['lang']['kodepabrikasi']."</td>
            <td>:</td>
            <td><input type=text id=schkdpab   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
	<td><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
        </tr>
	";
        echo "</table>";
		
	
echo"</fieldset></td>";

echo"
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div
?>



<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php 

echo"
<div id=listdata style=display:block>";//buka list data
OPEN_BOX();
    echo "
    <fieldset  style=width:750px>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['kodepabrikasi']."</td>
					<td  align=center>".$_SESSION['lang']['namapabrikasi']."</td>
					<td  align=center>".$_SESSION['lang']['kodesalesorder']."</td>
					<td  align=center>".$_SESSION['lang']['tanggal']." Cut off</td>
					<td  align=center>".$_SESSION['lang']['total']."</td>
					<td  align=center>".$_SESSION['lang']['status']."</td>
                    <td  align=center>".$_SESSION['lang']['action']."</td>    
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
echo "</div>";//tutup list data
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php

echo "<div id=header style=display:none>";//buka diff
OPEN_BOX();// 
echo "
<fieldset style=width:750px>
<legend><b>Header</b></legend>
<table cellspacing=1 border=0>
    <tr>
        <td style=\"width:120px;\">".$_SESSION['lang']['kodepabrikasi']."</td>
        <td>:</td>
        <td><input type=text id=kdpab disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\">
		<img title='".$_SESSION['lang']['find']."' id=tmblkdso class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' onclick=crkdpab('".$_SESSION['lang']['find']."',event)>
		</td>
		<td>&nbsp;</td>
		<td>".$_SESSION['lang']['namapabrikasi']."</td>
        <td>:</td>
		<td>	<input type=text id=nmpab disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">
			
		</td>
    </tr> 
	<tr>
        
		<td>".$_SESSION['lang']['tanggal']." Cut Off</td>
		<td>:</td>
		<td>
			<input type=text onchange=tgl1 class=myinputtext id=tgl1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:100px;/>
		</td>
		
		<td>&nbsp;</td>
		<td>".$_SESSION['lang']['kodesalesorder']."</td>
        <td>:</td>
        <td><input type=text id=kdso disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
    
		
		
	</tr>
	<tr>
        <td>".$_SESSION['lang']['persen']." ".$_SESSION['lang']['biaya']."</td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber onchange=gethitunght() id=persen name=persen onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=5 />
			<input type=text class=myinputtextnumber  id=rptampung name=rptampung hidden onkeypress=\"return angka_doang(event);\" style=\"width:50px;\" />
		</td>
    
		<td>&nbsp;</td>
        <td>".$_SESSION['lang']['total']." ".$_SESSION['lang']['biaya']."</td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber id=total name=total disabled onkeypress=\"return angka_doang(event);\" style=\"width:100px;\"  /></td>
    </tr>
	
	
	<tr><td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=savehead()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
		
	</tr>
</table>
</fieldset><input type=hidden id=method value='savehead'>";
CLOSE_BOX();//<input type=hidden id=method value='insert'>
echo"</div>";
?>



<?php
echo "<div id=detail style=display:none>";//buka diff
OPEN_BOX();



// style='float:left;'
echo"<fieldset style=width:750px><legend><b>Form ".$_SESSION['lang']['detail']."</b></legend>
<table border=0>
    <tr>
		<td style=\"width:120px;\">".$_SESSION['lang']['kodebarang']."</td>
		<td>:</td>
		<td><input type=text  id=kdbrgdt disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\">
		<img class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang onclick=tambahBarang('".$_SESSION['lang']['find']."',event)></td>
	
		<td>&nbsp;</td>
		<td>".$_SESSION['lang']['namabarang']."</td>
		<td>:</td>
		<td><input type=text  id=nmbrgdt disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:150px;\"></td>
	</tr>
	<tr>
        <td>".$_SESSION['lang']['hargasatuan']."</td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber id=hargasatdt disabled name=hargadt onkeypress=\"return angka_doang(event);\" style=\"width:75px;\"  /></td>
    
		<td>&nbsp;</td>
		<td>".$_SESSION['lang']['kwantitas']."</td>
		<td>:</td>
		<td><input type=text  id=jumlahdt onchange=gethitungdt()  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:75px;\"></td>
	</tr>
	<tr>
		
        <td>".$_SESSION['lang']['persen']." ".$_SESSION['lang']['beban']."</td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber onchange=gethitungdt()  id=persendt name=persen onkeypress=\"return angka_doang(event);\" style=\"width:75px;\" maxlength=3 /></td>
		
		<td>&nbsp;</td>
        <td>".$_SESSION['lang']['total']."</td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber id=hargadt name=hargadt disabled onkeypress=\"return angka_doang(event);\" style=\"width:75px;\"  /></td>
    </tr>
	
	
	
	
		<td colspan=2></td>
		<td colspan=100>
		<button id=savedetail class=mybutton onclick=savedetail()>".$_SESSION['lang']['save']."</button>
		<button onclick=canceldt() class=mybutton name=canceldt id=canceldt>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset></fieldset><input type=hidden id=methoddetail value='savedetail'>";

echo"
<div id='listdetail'>
</div>";



CLOSE_BOX();
echo"</div>";

echo close_body();			
?>
    
