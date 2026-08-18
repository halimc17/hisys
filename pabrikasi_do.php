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
<script language=javascript1.2 src='js/pabrikasi_do.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php

if($_SESSION['language']=='ID'){
	OPEN_BOX('','<span class=judul>'.strtoupper('delivery order').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('delivery order').'</span>');
}


$optdivisi=$optbuyer=$optso=$optoutlet="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select kode,nama from ".$dbname.".pabrikasi_5outlet";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optoutlet.="<option value='".$bar['kode']."'>".$bar['nama']."</option>";
}
$str="select kodecustomer,namacustomer from ".$dbname.".pmn_4customer";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbuyer.="<option value='".$bar['kodecustomer']."'>".$bar['namacustomer']."</option>";
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
        <td>".$_SESSION['lang']['nodo']."</td>
        <td>:</td>
        <td>
			<input type=text id=nodosch   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">
		</td>
    
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td>
			<input type='text' class='myinputtext' id='tglsch' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:80px;\">
		</td>
    
	
	
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
    <fieldset style=width:750px>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
            <thead>
                <tr class=rowheader>
					<td  align=center>".$_SESSION['lang']['nourut']."</td>
					<td  align=center>".$_SESSION['lang']['nodo']."</td>
					<td  align=center>".$_SESSION['lang']['tanggal']."</td>
					<td  align=center>".$_SESSION['lang']['nmcust']."</td>
                    <td  align=center>Outlet</td>
                    <td  align=center>".$_SESSION['lang']['salesorder']."</td>
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
<fieldset style=float:left>
<legend>Header</legend>
<table cellspacing=1 border=0>
	<tr>
        <td>".$_SESSION['lang']['nodo']."</td>
        <td>:</td>
        <td>
			<input type=text id=nodo disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:145px;\">
		</td>
    
        <td>".$_SESSION['lang']['tanggal']." DO</td>
        <td>:</td>
        <td>
			<input type='text' class='myinputtext' id='tgldo' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:145px;\">
		</td>
    </tr>
	
	<tr>
        <td>".$_SESSION['lang']['Pembeli']."</td>
        <td>:</td>
        <td>
			<select id=kdbuyer onchange=getso() style='width:150px;'>".$optbuyer."</select>
		</td>
    
        <td>Outlet</td>
        <td>:</td>
        <td>
			<select id=kdout style='width:150px;'>".$optoutlet."</select>
		</td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['kodesalesorder']."</td>
        <td>:</td>
        <td>
			<select id=kdso style='width:150px;'>".$optso."</select>
		</td>
   
        <td>".$_SESSION['lang']['keterangan']."</td>
        <td>:</td>
        <td>
			<input type=text id=ket   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:145px;\">
		</td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['tandatangan']." 1</td>
        <td>:</td>
        <td>
			<input type=text id=ttd1   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:145px;\">
		</td>
    
        <td>".$_SESSION['lang']['tandatangan']." 2</td>
        <td>:</td>
        <td>
			<input type=text id=ttd2   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:145px;\">
		</td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['tandatangan']." 3</td>
        <td>:</td>
        <td>
			<input type=text id=ttd3   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:145px;\">
		</td>
    </tr>
	
	
	<tr><td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=saveht()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
		</td>
		
	</tr>
</table>
</fieldset><input type=hidden id=methodht value='savehead'>";
CLOSE_BOX();
echo"</div>";
?>



<?php
echo "<div id=detail style=display:none>";//buka diff
OPEN_BOX();

echo"<fieldset style=width:750px><legend>Detail</legend>
<table cellspacing=1 border=0 class=sortable><thead>
	<tr>
        <td align=center>".$_SESSION['lang']['docnum']."</td>
        <td align=center>".$_SESSION['lang']['kodebarang']."</td>
		<td align=center>".$_SESSION['lang']['namabarang']."</td>
		<td align=center>".$_SESSION['lang']['jumlah']."</td>
		<td align=center>".$_SESSION['lang']['noseri']."</td>
		<td align=center width=80px>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kadaluwarsa']."</td>
		<td align=center>".$_SESSION['lang']['action']."</td>
    </tr></thead>
	<tr class=rowcontent>
        <td>
			<input type=text id=nodok disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:130px;\">
			<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblnodok class=resicon onclick=crnodok('".$_SESSION['lang']['find']."',event)>
		</td>
        <td><input type=text id=kdbrg disabled onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:70px;\"></td>
		<td><input type=text id=nmbrg disabled onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:225px;\"></td>
		<td><input type=text id=qty onkeypress='return angka_doang(event)' class=myinputtextnumber  style=\"width:60px;\"></td>
		<td><input type=text id=noseri onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\"></td>
		<td><input type='text' class='myinputtext' id='tglkad' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:80px;\"></td>
		<td align=center><img src=images/save.png title='".$_SESSION['lang']['save']."' class=resicon onclick=savedt()></td>
    </tr>
</table></fieldset><input type=hidden id=methoddt value='savedetail'>
";

echo"
<div id='listdetail'></div>";
CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>