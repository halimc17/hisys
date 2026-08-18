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
<script language=javascript1.2 src='js/pabrikasi_sales.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php

if($_SESSION['language']=='ID'){
	OPEN_BOX('','<span class=judul>'.strtoupper('sales order').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('sales order').'</span>');
}



$optpt=$optcus=$optsales="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

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

$optstat="<option value='0'>Waiting</option>";
// $optstat.="<option value='1'>Open</option>";
// $optstat.="<option value='2'>Cancel</option>";
// $optstat.="<option value='3'>Close</option>";				


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
            <td>".$_SESSION['lang']['kodesalesorder']."</td>
            <td>:</td>
            <td><input type=text id=schkdso   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
	</tr>
            <td colspan=3><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
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
    <fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:50%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
					<td  align=center>".$_SESSION['lang']['pt']."</td>
                    <td  align=center>".$_SESSION['lang']['kodesalesorder']."</td>
					<td  align=center>".$_SESSION['lang']['kodecustomer']."</td>
					<td  align=center>".$_SESSION['lang']['tanggal']." Order</td>
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
<fieldset>
<legend><b>Header</b></legend>
<table cellspacing=1 border=0>
    <tr>
        <td>".$_SESSION['lang']['kodept']."</td>
        <td>:</td>
        <td><select id=kdpt style=\"width:163px;\">'".$optpt."'</select></td>
    </tr> 
	<tr>
        <td>".$_SESSION['lang']['kodesalesorder']."</td>
        <td>:</td>
        <td><input type=text id=kdso   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:158px;\"></td>
    </tr> 
	 <tr>
        <td>".$_SESSION['lang']['nmcust']."</td>
        <td>:</td>
        <td><select id=kdcus style=\"width:163px;\">'".$optcus."'</select>
		<img id='kdcus' onclick=z.elSearch('kdcus',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
    </tr> 
	
	<tr>
		<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['order1']."</td>
		<td>:</td>
		<td>
			<input type=text onchange=tglorder class=myinputtext id=tglorder readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
		</td>
	</tr>
	
	<tr>
        <td>".$_SESSION['lang']['nopo']." ".$_SESSION['lang']['customer']."</td>
        <td>:</td>
        <td><input type=text id=nopo onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:158px;\"></td>
    </tr> 
	
	<tr>
		<td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['pengiriman']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tglmulai readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
			S/D
			<input type=text class=myinputtext id=tglselesai readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
		</td>
	</tr>
	
	<tr>
        <td>Sales</td>
        <td>:</td>
        <td><select id=salesid style=\"width:163px;\">'".$optsales."'</select>
		<img id='salesid' onclick=z.elSearch('salesid',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
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
echo"<fieldset><legend><b>Form ".$_SESSION['lang']['detail']."</b></legend>
<table>
    <tr>
		<td>".$_SESSION['lang']['kodebarang']."</td>
		<td>:</td>
		<td><input type=text  id=kdbrg disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:65px;\" >
			<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=tambahBarang('".$_SESSION['lang']['find']."',event)>
		</td>
	
		<td><input type=text  id=nmbrg disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:275px;\"></td>
	</tr>
	
	<tr>
		<td>".$_SESSION['lang']['jumlah']."</td>
		<td>:</td>
		<td><input type=text  id=jum onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:65px;\"></td>
	</tr>
	
	<tr>
		<td valign=top>".$_SESSION['lang']['keterangan']."</td>
		<td valign=top>:</td>
		<td colspan=2><input onkeypress=\"return_tanpa_kutip(event);\" id=ket style=width:365px; rows=5 class=myinputtext></td>
	</tr>
	
	<tr hidden>
        <td>".$_SESSION['lang']['status']."</td>
        <td>:</td>
        <td><select id=stat style=\"width:150px;\">'".$optstat."'</select></td>
    </tr>
	
	
	
	<tr>
		<td colspan=2></td>
		<td colspan=100>
		<button id=savedetail class=mybutton onclick=savedetail()>".$_SESSION['lang']['save']."</button>
		<button onclick=canceldt() class=mybutton name=canceldt id=canceldt>".$_SESSION['lang']['cancel']."</button>
		<button onclick=displaylist() class=mybutton name=selesai id=selesai>".$_SESSION['lang']['selesai']."</button>
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
    
