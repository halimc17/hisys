<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<script language=javascript1.2 src='js/kebun_rekappnn.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language="javascript" src="js/generic.js?v=<?php echo time(); ?>"></script>
<?php
##deklarasi untuk option##
$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=6 and tipe='AFDELING' and induk='" . $_SESSION['empl']['lokasitugas'] . "' ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}


$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=4 and tipe='KEBUN' order by kodeorganisasi";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

$optper = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(periode) as periode FROM " . $dbname . ".setup_periodeakuntansi order by periode desc limit 12 ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}



##HEADER UNTUK BUAT BARU SAMA LIST-->
OPEN_BOX('','<span class=judul>'.getMenu('kebun_rekappnn').'</span>');
echo"<div id=action_list>"; //buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
            <fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
	
         <table>
				<tr>
					<td>" . $_SESSION['lang']['divisi'] . "</td> 
					<td>:</td>
					<td><select class=select2 id=divsch  style=\"width:120px;\">" . $optorg . "</select></td>
				</tr>
				<tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td> 
                    <td>:</td>
                    <td><input type=text class=myinputtext  id=tglsch onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:115px;\" readonly/></td>
            </tr>
                ";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
    


<td>
	<fieldset><legend>" . $_SESSION['lang']['print'] . "</legend> 
         <table>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td> 
                    <td>:</td>
                    <td><select class=select2 id=unitexp  style=\"width:120px;\">" . $optunit . "</select></td>
                    </tr>
                    <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td> 
                    <td>:</td>
                    <td><select class=select2 id=perexp  style=\"width:120px;\">" . $optper . "</select></td>
                </tr>
                ";

echo"<tr><td><td><td><button class=mybutton onclick=excel(event,'kebun_slave_rekappnn.php')>" . $_SESSION['lang']['excel'] . "</button></td></td></tr></table>";
echo"</fieldset></td>




     </tr>
	 </table> ";
CLOSE_BOX();
echo "</div>"; //tutup div
##UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->


echo"
<div id=listData style=display:block>"; //buka list data
OPEN_BOX(); //<div style=overflow:scroll>
//<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%
	echo "
	
            <table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:900px>
            <thead>
                <tr class=rowheader>
					<td align=center  rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
					<td align=center rowspan='2'>" . $_SESSION['lang']['afdeling'] . "</td>
					<td align=center rowspan='2'>" . $_SESSION['lang']['namaorganisasi'] . "</td>
					<td align=center  rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</td>
					<td align=center  rowspan='2'>Ha " . $_SESSION['lang']['panen'] . "</td>
					<td align=center  rowspan='2'>".$_SESSION['lang']['hk2']."</td>
					<td align=center  colspan='2'>" . $_SESSION['lang']['jjg'] . "</td>
					<td align=center  rowspan='2'>Brondolan<br>(Kg)</td>
					<td align=center  rowspan='2'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['kebun'] . "</td>
					<td align=center  rowspan='2'>" . $_SESSION['lang']['dibuat'] . "</td>
					<td align=center rowspan='2' colspan='4'>" . $_SESSION['lang']['action'] . "</td>
                </tr> 
                <tr>
                     <td align=center>" . $_SESSION['lang']['panen'] . "</td>
                         <td align=center>Afkir</td>
                </tr>
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
             ";
CLOSE_BOX();
echo "</div>"; //tutup list data
##UNTUK BUAT FORM INPUT HEADER-->


echo "<div id=header style=display:none>"; //buka diff
OPEN_BOX();
echo "
<fieldset style=float:left>
<legend>Header</legend>
<table cellspacing=1 border=0>
    <tr>
            <td>" . $_SESSION['lang']['divisi'] . "</td> 
            <td>:</td>
            <td><select class=select2 style=\"width:150px;\" id=div>" . $optorg . "</select></td>
    </tr> 
    <tr>
            <td>" . $_SESSION['lang']['tanggal'] . "</td> 
            <td>:</td>
            <td><input type=text style=\"width:145px;\" class=myinputtext id=tgl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/></td>
    </tr>

	<tr>
            <td colspan=2></td>
            <td>
                <button id=tomboldetail class=mybutton onclick=detail()>" . $_SESSION['lang']['save'] . "</button>
                <button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
            </td>
            <input type=hidden id=method value='insert'>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";



echo"
<div id=detail style=display:none>"; //buka list data
OPEN_BOX();

/*
  echo"
  <fieldset style='float:left;'>
  <script>detail()</script>
  </fieldset>";
 */




CLOSE_BOX();
echo"</div>";




echo close_body();
?>