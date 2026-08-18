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
<script language=javascript1.2 src='js/pabrikasi_penagihan.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php

if($_SESSION['language']=='ID'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Penagihan pabrikasi').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('Fabrication Invoice').'</span>');
}



$optdivisi=$optthnsch=$optper=$optkeg=$optsat=$opttk=$optblok="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' and induk='".$_SESSION['empl']['lokasitugas']."'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optgdg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

$str="select kodecustomer,namacustomer from ".$dbname.".pmn_4customer";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbuyer.="<option value='".$bar['kodecustomer']."'>".$bar['namacustomer']."</option>";
}


$str="select kodeso from ".$dbname.".pabrikasi_salesht ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optso.="<option value='".$bar['kodeso']."'>".$bar['kodeso']."</option>";
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
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td>
			<input type=text id=notransch   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">
		</td>
    
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td>
			<input type='text' class='myinputtext' id='tglsch' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:80px;\">
		</td>
    
	
	
            <td colspan=3>
			<button hidden id=x class=mybutton onclick=location.reload()>reload biar gk cape</button>
			<button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
        </tr>
	";
        echo "</table>";
		
	
echo"</fieldset></td>";

echo"
     </tr>
	 </table> "; 

echo "</div>";//tutup div
CLOSE_BOX();
?>



<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php 


echo"
<div id=listdata style=display:block;>";//buka list data
OPEN_BOX();
    echo "
    <fieldset style=width:750px>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:750px>
            <thead>
                <tr class=rowheader>
					<td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['notransaksi']."</td>
					<td  align=center>".$_SESSION['lang']['tanggal']."</td>
					<td  align=center>".$_SESSION['lang']['Pembeli']."</td>
                    <td  align=center>".$_SESSION['lang']['salesorder']."</td>
                    <td width=70px align=center>".$_SESSION['lang']['action']."</td>    
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
<fieldset style=width:600px>
<legend>Header</legend>
<table cellspacing=1 border=0>
	<tr>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td>
			<input type=text id=notran disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">
		</td>
    </tr> 
	<tr>
        <td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['invoice']."</td>
        <td>:</td>
        <td>
			<input type='text' class='myinputtext' id='tglpen' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:150px;\">
		</td>
    
        <td>".$_SESSION['lang']['Pembeli']."</td>
        <td>:</td>
        <td>
			<select id=kdbuyer onchange=getso() style='width:155px;'>".$optbuyer."</select>
		</td>
    </tr>
	
	<tr>
        <td>".$_SESSION['lang']['tgljatuhtempo']."</td>
        <td>:</td>
        <td>
			<input type='text' class='myinputtext' id='tgljth' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:150px;\">
		</td>
   
        <td>".$_SESSION['lang']['kodesalesorder']."</td>
        <td>:</td>
        <td>
			<select id=kdso style='width:155px;'>".$optso."</select>
		</td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['tandatangan']." 1</td>
        <td>:</td>
        <td>
			<input type=text id=ttd1   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">
		</td>
    
        <td>".$_SESSION['lang']['tandatangan']." 2</td>
        <td>:</td>
        <td>
			<input type=text id=ttd2   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">
		</td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['tandatangan']." 3</td>
        <td>:</td>
        <td>
			<input type=text id=ttd3   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">
		</td>
    </tr>

	
  
	
	<tr><td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=saveht()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=newdata()>".$_SESSION['lang']['cancel']."</button>
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
echo"<fieldset style=width:600px>
<legend>Find</legend>
".$_SESSION['lang']['notransaksi']." DO : 

<input type=text id=nodo disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:130px;\">
<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblnodok class=resicon onclick=crnodok('".$_SESSION['lang']['find']."',event)>

 
<div id='detaildata'></div></fieldset>
<div id='listdetail'></div>";
CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>