<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/lgl_pengajuanpembebasanlahan.js?v=1.7'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php

##deklarasi untuk option##
$arrorgdet = getOrgDetail(2);

$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
// 	$where = "";
// } else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
//     $where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
// } else {
// 	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
// }

$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi in (".$arrorgdet.") order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(kodeorg) as kodeorganisasi FROM " . $dbname . ".lgl_pembebasanlahan";

$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . "</option>";
}

$optper = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(periode) as periode FROM " . $dbname . ".setup_periodeakuntansi order by periode desc limit 12 ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

##HEADER UNTUK BUAT BARU SAMA LIST-->
OPEN_BOX('','<span class=judul>'.getMenu('lgl_pengajuanpembebasanlahan').'</span>');
echo"<div id=action_list>";
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
				<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td><input id=notrsch style=\"width:145px;\" class=myinputtext onkeypress='return tanpa_kutip(event)';></td>
				
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td><select id=divsch  style=\"width:100px;\">" . $optunit . "</select></td>
				
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select id=periodesch  style=\"width:100px;\">" . $optper . "</select>
				</td>
            </tr>";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
		<td>
		<fieldset style=display:none><legend>" . $_SESSION['lang']['print'] . "</legend> 
        <table>
			<tr>
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td><select id=unitexp  style=\"width:100px;\">" . $optunit . "</select></td>
				</tr>
				<tr>
				<td>" . $_SESSION['lang']['periode'] . "</td> 
				<td>:</td>
				<td><select id=perexp  style=\"width:100px;\">" . $optper . "</select></td>
			</tr>
			";

echo"<tr><td><td><td><button class=mybutton onclick=excel(event,'vhc_slave_byyijinops.php')>" . $_SESSION['lang']['excel'] . "</button></td></td></tr></table>";
echo"</fieldset></td>
     </tr>
	 </table> ";
echo "</div>";
CLOSE_BOX();
echo"<div id=listData style=display:block>";
OPEN_BOX();
echo "<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<div>    
		<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
				<td align=center>" . $_SESSION['lang']['periode'] . "</td>
				<td align=center>Luas Lahan (Ha)</td>
				<td align=center>" . $_SESSION['lang']['biaya'] . " Pemb Lahan</td>
				<td align=center>" . $_SESSION['lang']['biaya'] . " Total</td>
				<td align=center>" . $_SESSION['lang']['updateby'] . "</td>
				<td align=center>" . $_SESSION['lang']['status'] . "</td>
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
echo "</div>";
echo "<div id=header style=display:none>";
OPEN_BOX();
echo "
	<fieldset style=float:left>
	<legend>Header</legend>
	<table cellspacing=1 border=0>
		<tr>
			<td>" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
			<td>:</td>
			<td><select onchange=getnotransaksi() style=\"width:175px;\" id=kodeorg>" . $optorg . "</select></td>
		</tr> 
		<tr>
			<td>" . $_SESSION['lang']['periode'] . "</td> 
			<td>:</td>
			<td>
			<select id=periode onchange=getnotransaksi() style='width:175px;'/>".$optper."</select>
			</td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
			<td>:</td>
			<td>
			<input id=notransaksi style='width:170px;' class='myinputtext' disabled/>
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
echo"<div id=detail style=display:none>";
OPEN_BOX();
CLOSE_BOX();
echo"</div>";
echo close_body();
?>