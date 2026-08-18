<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>

<script language=javascript1.2 src='js/vhc_rkh.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php

##deklarasi untuk option##

$optorg=$org = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$qry = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=4 and kodeorganisasi in (".getOrgDetail(2).") AND kodeorganisasi IN (SELECT DISTINCT induk FROM organisasi WHERE tipe='TRAKSI') ";
$ris = fetchData($qry);
foreach ($ris as $qr) {
    # kode berdasarkan detail akses
    $org.="<option value=" .$qr['kodeorganisasi']. ">" .$qr['kodeorganisasi']. " - ".$qr['namaorganisasi']."</option>";
}
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=6 and tipe='TRAKSI' and left(kodeorganisasi,4) in (".getOrgDetail(2).") ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . "-" . $bar['namaorganisasi'] . "</option>";
}


$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(kodeorg) as kodeorganisasi FROM $dbname.vhc_rkh WHERE kodeorg  IN (".getOrgDetail(2).") ";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - ".getNamaOrg($bar['kodeorganisasi'])."</option>";
}

$optper = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(substr(tanggal,1,7)) as periode FROM " . $dbname . ".vhc_rkh order by periode desc limit 12 ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}



##HEADER UNTUK BUAT BARU SAMA LIST-->
OPEN_BOX('','<span class=judul>'.getMenu('vhc_rkh').'</span>');
echo"<div id=action_list>"; //buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset><legend>" . $_SESSION['lang']['find'] . " Transaksi</legend> 
         <table>
			<tr>
				<td>" . $_SESSION['lang']['kodetraksi'] . "</td> 
				<td>:</td>
				<td><select id=divsch class=select2 style=\"width:100px;\">" . $optorg . "</select></td>
				</tr>
				<tr>
				<td>" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td><input type=text class=myinputtext  id=tglsch onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:95px;\" /></td>
            </tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
		<td>
		<fieldset><legend>" . $_SESSION['lang']['print'] . " Transaksi</legend> 
        <table>
			<tr>
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td><select id=unitexp class=select2 style=\"width:100px;\">" . $optunit . "</select></td>
				</tr>
				<tr>
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select id=perexp class=select2 style=\"width:100px;\">" . $optper . "</select></td>
			</tr>
			";

echo"<tr><td><td><td><button class=mybutton onclick=excel(event,'vhc_slave_rkh.php')>" . $_SESSION['lang']['excel'] . "</button></td></td></tr></table>";
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
	<fieldset>
            <legend>" . $_SESSION['lang']['list'] . "</legend>
            <div>    
		    <table cellpading=5 cellspacing=1 width=100% border=0 class=sortable >
            <thead>
                <tr class=rowheader>
                    <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
                    <td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td align=center>" . $_SESSION['lang']['kodetraksi'] . "</td>
					<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
					<td align=center>" . $_SESSION['lang']['updateby'] . "</td>
					<td align=center>Posting Date</td>
					<td hidden align=center>" . $_SESSION['lang']['posting'] . " By</td>
					<td align=center colspan='5'>" . $_SESSION['lang']['action'] . "</td>
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
             </div>
	</fieldset>";
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
            <td>" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
            <td>:</td>
            <td><select style=\"width:150px;\" id=kodeorg onchange=\"getsubunit()\" class=select2>" . $org . "</select></td>
    </tr> 
	<tr>
            <td>" . $_SESSION['lang']['kodetraksi'] . "</td> 
            <td>:</td>
            <td><select style=\"width:150px;\" id=div class=select2>" . $optorg . "</select></td>
    </tr> 
    <tr>
            <td>" . $_SESSION['lang']['tanggal'] . "</td> 
            <td>:</td>
            <td>
			<input type='text' style='width:145px;' class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false'; value='".date('d-m-Y')."' readonly/>
			</td>
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