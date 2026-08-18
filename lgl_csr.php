<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/lgl_csr.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php

##deklarasi untuk option##
$optorg =$optorgx=$optkat=$optkab=$optkec=$optdes= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

// $arr=getEnum($dbname,'lgl_pengajuanfee','tipe');
// foreach($arr as $kei=>$fal){
	// $opttipe.="<option value='".$kei."'>".$fal."</option>";
// }

$str="select * from ".$dbname.".kabupaten order by kabupaten asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optkab.="<option value=".$bar['id'].">".$bar['kabupaten']."</option>";
}

$str="select * from ".$dbname.".kecamatan order by kecamatan asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optkec.="<option value=".$bar['idkec'].">".$bar['kecamatan']."</option>";
}

$str="select * from ".$dbname.".desa order by desa asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optdes.="<option value=".$bar['iddes'].">".$bar['desa']."</option>";
}


$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optorgx.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$str="select * from ".$dbname.".lgl_5kegiatancsr order by no asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optkat.="<option value=".$bar['id'].">".$bar['tipe']."</option>";
}

$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(kodeorg) as kodeorganisasi FROM " . $dbname . ".lgl_csr";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . "</option>";
}

$optper = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT distinct(left(tanggal,7)) as periode FROM " . $dbname . ".lgl_csr order by periode desc limit 12 ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('lgl_csr').'</span>');
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
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td><select id=divsch  style=\"width:100px;\">" . $optunit . "</select></td>
				</tr>
				<tr>
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
echo "<fieldset  style=width:900px>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<div>    
		<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
				<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
				<td align=center>" . $_SESSION['lang']['kegiatan'] . "</td>
				<td align=center>" . $_SESSION['lang']['penerima'] . "</td>
				<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
				<td align=center>" . $_SESSION['lang']['biaya'] . "</td>
				<td align=center>" . $_SESSION['lang']['updateby'] . "</td>
				<td align=center colspan='4'>" . $_SESSION['lang']['action'] . "</td>
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
	<fieldset >
	<legend>Header</legend>
	<table cellspacing=1 border=0>
		<tr>
			<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
			<td>:</td>
			<td>
			<input id=notransaksi style='width:170px;' class='myinputtext' disabled/>
			</td>
			
			<td>Program Kegiatan</td> 
			<td>:</td>
			<td><select style=\"width:212px;\" id=kegiatan>" . $optkat . "</select></td>
			
			<td>Volume</td> 
			<td>:</td>
			<td><input id=volume class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style='width:207px;'></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
			<td>:</td>
			<td><select onchange=getnotransaksi() style=\"width:175px;\" id=kodeorg>" . $optorg . "</select></td>
			
			<td>Kabupaten</td> 
			<td>:</td>
			<td><select style=\"width:212px;\" onchange=getkec() id=kab>" . $optkab . "</select></td>
			
			<td>Dana</td> 
			<td>:</td>
			<td><input id=dana class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style='width:207px;'></td>
			
		</tr> 
		<tr>
			<td>" . $_SESSION['lang']['tanggal'] . "</td> 
			<td>:</td>
			<td><input type=text onchange=getnotransaksi() class=myinputtext style='width:170px;' id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 />
			</td>
			
			<td>Kecamatan</td> 
			<td>:</td>
			<td><select style=\"width:212px;\" onchange=getdes() id=kec>" . $optkec . "</select></td>
			
			<td>Nama Penerima</td> 
			<td>:</td>
			<td><input id=penerima class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style='width:207px;'></td>
		</tr>
		<tr>
			<td>Keterangan</td> 
			<td>:</td>
			<td><input id=keterangan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style='width:170px;'></td>
			
			<td>Desa</td> 
			<td>:</td>
			<td><select style=\"width:212px;\" id=des>" . $optdes . "</select></td>
			<!--
			<td>Nomor Rekening</td> 
			<td>:</td>
			<td><input id=bank class=myinputtext placeholder='BANK' onkeydown=\"upperCaseF(this)\" nkeypress=\"return_tanpa_kutip(event);\" style='width:50px;'>
				<input id=rekening class=myinputtext  placeholder='No Rekening' nkeypress=\"return_tanpa_kutip(event);\" style='width:150px;'></td> -->
		</tr> 
		<tr>
			<td colspan=2></td>
			<td>
				<button id=tomboldetail class=mybutton onclick=savedetail()>" . $_SESSION['lang']['save'] . "</button>
				<button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
			<input type=hidden id=method value='insert'>
			<input type=hidden id=status value='0'>
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