<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/lgl_dokumenlegal.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
##deklarasi untuk option##
$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(kodept) as kodeorganisasi FROM " . $dbname . ".lgl_dokumenlegal";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $nmorg[$bar['kodeorganisasi']] . "</option>";
}
$nmijin = makeOption($dbname, 'legal_5nama', 'kodeijin,namaijin');
$optnmijin = "<option value=''></option>";
$sql = "SELECT distinct(kodeijin) as kodeijin FROM " . $dbname . ".lgl_dokumenlegal";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optnmijin.="<option value=" . $bar['kodeijin'] . ">" . $nmijin[$bar['kodeijin']] . "</option>";
}

##HEADER UNTUK BUAT BARU SAMA LIST
OPEN_BOX('','<span class=judul>'.getMenu('lgl_dokumenlegal').'</span>');
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
				<td>" . $_SESSION['lang']['pt'] . "</td> 
				<td>:</td>
				<td><select id=divsch onchange='loaddata()' style=\"width:105px;\">" . $optunit . "</select></td>
				
				<td>" . $_SESSION['lang']['namaperijinan'] . "</td> 
				<td>:</td>
				<td><select id=namaijinsrc onchange='loaddata()' style=\"width:105px;\">" . $optnmijin . "</select>
				</td>
				
				<td>" . $_SESSION['lang']['nomorperijinan'] . "</td> 
				<td>:</td>
				<td><input id=noijinsrc onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:100px;\"></td>

				<td>".$_SESSION['lang']['tglditerbitkan']."</td>
				<td>:</td>
				<td><input style=\"width:100px;\" onkeypress='enterkey(event,loaddata)' class='myinputtext' id='tglsdsrc' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
				
				<td>".$_SESSION['lang']['tglberakhir']."</td>
				<td>:</td>
				<td><input style=\"width:100px;\" onkeypress='enterkey(event,loaddata)' class='myinputtext' id='tglakhirsrc' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
			</tr><tr>
			
				<td>".$_SESSION['lang']['dikeluarkan']."</td>
				<td>:</td>
				<td><input id=dikeluarkansrc onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:100px;\"></td>
				
				<td>".$_SESSION['lang']['kedudukan']."</td>
				<td>:</td>
				<td><input id=kedudukansrc onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:100px;\"></td>
				
				<td>".$_SESSION['lang']['kegiatanusaha']."</td>
				<td>:</td>
				<td><input id=kegusahasrc onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:100px;\"></td>
				
				<td>".$_SESSION['lang']['penanggungjawab']."</td>
				<td>:</td>
				<td><input id=tggjwbsrc onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:100px;\"></td>
				
				<td>".$_SESSION['lang']['keterangan']."</td>
				<td>:</td>
				<td><input id=ketsrc onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:100px;\"></td>
			</tr>
			";

echo"<tr><td><td><td colspan=5>
		<button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
		<button class=mybutton onclick=batal()>" . $_SESSION['lang']['cancel'] . "</button>
	</td></td></tr></table>";

echo"</td></tr></table> ";
echo "</div>";
CLOSE_BOX();
##UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER

echo"<div id=listData style=display:block>"; # buka list data
OPEN_BOX();
echo "<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<div>    
		<table cellpading=7 cellspacing=1 border=0 class=sortable style=min-width:100%>
		<thead>
			<tr class=rowheader>
			
			<td align=center style=\"width:30px;\">".$_SESSION['lang']['nourut']."</td>
			<td align=center>" . $_SESSION['lang']['pt'] . "</td>
            <td align=center style=\"width:100px;\">".$_SESSION['lang']['jenis']."</td>
			<td align=center style=\"width:100px;\">".$_SESSION['lang']['namaperijinan'] . "</td>
			<td align=center style=\"width:150px;\">".$_SESSION['lang']['nomorperijinan']."</td>
			<td align=center style=\"width:75px;\">".$_SESSION['lang']['tglditerbitkan']."</td>
			<td align=center style=\"width:75px;\">".$_SESSION['lang']['tglberakhir']."</td>
			<td align=center width=100px>".$_SESSION['lang']['dikeluarkan']."</td>
			<td align=center style=\"width:130px;\">".$_SESSION['lang']['kedudukan']."</td>
			<td align=center width=150px>".$_SESSION['lang']['kegiatanusaha']."</td>
			<td align=center width=120px>".$_SESSION['lang']['penanggungjawab']."</td>
			<td align=center width=150px>".$_SESSION['lang']['keterangan']."</td>
			<td align=center width=75px>".$_SESSION['lang']['tgldaftarulang']."</td>
			<td align=center width=75px>".$_SESSION['lang']['tgljatuhtempo']."</td>
			<td align=center>" . $_SESSION['lang']['updateby'] . "</td>

				<td align=center colspan='3'>" . $_SESSION['lang']['action'] . "</td>
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
##UNTUK BUAT FORM INPUT HEADER

echo "<div id=header style=display:none>";
OPEN_BOX();
echo "
<fieldset style=float:left>
<legend>Header</legend>
<table cellspacing=1 border=0>
    <tr>
		<td>" . $_SESSION['lang']['pt'] . "</td> 
		<td>:</td>
		<td><select style=\"width:200px;\" id=pt>" . $optorg . "</select></td>
    </tr>
	<tr>
		<td colspan=2></td>
		<td>
			<button id=tomboldetail class=mybutton onclick=detail()>" . $_SESSION['lang']['save'] . "</button>
			<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";
echo"<div id=detail style=display:none>";
OPEN_BOX();
CLOSE_BOX();
echo"</div>";
echo close_body();
?>