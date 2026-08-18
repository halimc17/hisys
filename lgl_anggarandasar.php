<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/lgl_anggarandasar.js?v=<?php echo time(); ?>'></script>
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
$sql = "SELECT distinct(kodept) as kodeorganisasi FROM " . $dbname . ".lgl_anggarandasarht";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $nmorg[$bar['kodeorganisasi']] . "</option>";
}

$optsts=$optper = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(periode) as periode FROM " . $dbname . ".setup_periodeakuntansi order by periode desc limit 12 ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arrnama = array('PMA' => 'Penanaman Modal Asing' , 'PMDNFU' => 'PMDN Fasilitas Umum' , 'PMDNNFU' => 'PMDN Non Fasilitas Umum');
$arrsts=getEnum($dbname,'lgl_anggarandasarht','jenispt');
foreach($arrsts as $key => $val){
	$optsts.="<option value=" . $val . ">" . $arrnama[$val] . "</option>";
}

##HEADER UNTUK BUAT BARU SAMA LIST
OPEN_BOX('','<span class=judul>'.getMenu('lgl_anggarandasar').'</span>');
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
				<td><select id=divsch onchange='loaddata()' style=\"width:150px;\">" . $optunit . "</select></td>
				</tr>
				";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";

echo"</td></tr></table> ";
echo "</div>";
CLOSE_BOX();
##UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER

echo"<div id=listData style=display:block>"; # buka list data
OPEN_BOX();
echo "<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<div>    
		<table cellpading=1 cellspacing=1 border=0 class=sortable>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['pt'] . "</td>
				<td align=center>" . $_SESSION['lang']['status'] . "</td>
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
		<td><select onchange=getjenispt() style=\"width:200px;\" id=pt>" . $optorg . "</select></td>
    </tr>
	<tr>
		<td>Status</td> 
		<td>:</td>
		<td><select style=\"width:200px;\" id=jenis>" . $optsts . "</select>
		</td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td>
			<button id=tomboldetail class=mybutton onclick=detailakta()>" . $_SESSION['lang']['save'] . "</button>
			<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
			<!--<button id=ajukan class=mybutton onclick=frm_aju()>Ajukan</button>-->
		</td>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
echo"</div>";
echo"<div id=detailakta style=display:none>";
OPEN_BOX();
CLOSE_BOX();
echo"</div>";

	echo"<div id=persetujuan style=display:none>";

OPEN_BOX();

    echo "<div id=persetujuandata></div>";

CLOSE_BOX();
echo "</div>";

close_body();
