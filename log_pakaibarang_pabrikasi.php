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
<script language=javascript1.2 src='js/log_pakaibarang_pabrikasi.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php
OPEN_BOX('','<span class=judul>Transaksi Gudang Pabrikasi</span>');
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
        <td>Notransaksi</td>
        <td>:</td>
        <td>
			<input type=text id=notransch   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">
		</td>
    </tr> 
	<tr>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td>
			<input type='text' class='myinputtext' id='tglsch' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:150px;\">
		</td>
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
                    <td  align=center>".$_SESSION['lang']['notransaksi']."</td>
                    <td  align=center>Kode Pabrikasi</td>
					<td  align=center>".$_SESSION['lang']['tanggal']."</td>
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
<fieldset>
<legend>Header</legend>
<table cellspacing=1 border=0>
	<tr>
        <td>Notransaksi</td>
        <td>:</td>
        <td>
			<input type=text id=notran disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\">
		</td>
    </tr> 
	<tr>
        <td>".$_SESSION['lang']['gudang']."</td>
        <td>:</td>
        <td>
			<select id=gudang style='width:150px;'>".$optgdg."</select>
		</td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td>
			<input type='text' class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style=\"width:150px;\">
		</td>
    </tr>
    <tr>
        <td>Kode Pabrikasi</td>
        <td>:</td>
        <td colspan=2><input type=text id=kdpab disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\">
			<input type=text id=nmpab disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:200px;\">
			<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblkdso class=resicon onclick=kdso('".$_SESSION['lang']['find']."',event)>
		</td>
    </tr> 
	
	<tr><td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=list()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=batalht()>".$_SESSION['lang']['cancel']."</button>
		</td>
		
	</tr>
</table>
</fieldset>";
CLOSE_BOX();//<input type=hidden id=method value='insert'>
echo"</div>";
?>



<?php
echo "<div id=detail style=display:none>";//buka diff
OPEN_BOX();
echo"
<div id='listdetail'></div>";
CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>