<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/lgl_litigasi.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
##deklarasi untuk option##
$optorg =$optun=$optjns= "<option value=''></option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}
$optunit =$optunit2= "<option value=''></option>";
$sql = "SELECT distinct(kodept) as kodeorganisasi FROM " . $dbname . ".lgl_litigasi";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $nmorg[$bar['kodeorganisasi']] . "</option>";
}

$sql = "SELECT distinct(unit) as kodeorganisasi FROM " . $dbname . ".lgl_litigasi";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optunit2.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $nmorg[$bar['kodeorganisasi']] . "</option>";
}

$optun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optun.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$optjnsperkara="<option value=''></option>";
$arrtipe=getEnum($dbname,'lgl_litigasi','jenisperkara');
foreach( $arrtipe as $key => $val){
	$optjnsperkara.="<option value=".$val.">".$val."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('lgl_litigasi').'</span>');
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
				<td>" . $_SESSION['lang']['pt'] . "</td> 
				<td>:</td>
				<td><select id=divsch onchange='loaddata()' style=\"width:150px;\">" . $optunit . "</select></td>
				
				<td>" . $_SESSION['lang']['jenis'] . "</td> 
				<td>:</td>
				<td><select id=jenissch onchange='loaddata()' style=\"width:150px;\">" . $optjnsperkara . "</select></td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['unit'] . "</td> 
				<td>:</td>
				<td><select id=unitsch onchange='loaddata()' style=\"width:150px;\">" . $optunit2 . "</select></td>
				
				<td>" . $_SESSION['lang']['nomor'] . "</td> 
				<td>:</td>
				<td colspan=4><input id=nohaksch onkeypress='enterkey(event,loaddata)' class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:145px;\"></td>
			</tr>
			";

echo"<tr><td><td><td><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button></td></td></tr></table>";

echo"</td></tr></table> ";
echo "</div>";
CLOSE_BOX();
echo"<div id=listData style=display:block>";
OPEN_BOX();
echo "<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<div>    
		<table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['pt'] . "</td>
				<td align=center>" . $_SESSION['lang']['unit'] . "</td>
				<td align=center>" . $_SESSION['lang']['nomor'] . "</td>
				<td align=center>Lokasi</td>
				<td align=center>Pengadilan</td>
				<td align=center>Penggugat</td>
				<td align=center>Tergugat</td>
				<td align=center>Jenis Perkara</td>
				<td align=center>Advokat</td>
				<td align=center>Tanggal Daftar</td>
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
echo "</div>";

echo "<div id=header style=display:none>";
OPEN_BOX();
echo "
	<fieldset>
	<legend>Form</legend>
	<fieldset style='display: inline-block;vertical-align:top;min-height:310px'>
	<legend>Input</legend>
	<table cellspacing=1 border=0 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>" . $_SESSION['lang']['pt'] . "</td> 
			<td>:</td>
			<td colspan=4><select onchange=getunit() style=\"width:99%;\" id=pt>" . $optorg . "</select></td>
			
			<td>" . $_SESSION['lang']['unit'] . "</td> 
			<td>:</td>
			<td colspan=4><select style=\"width:99%;\" id=unit>" . $optun . "</select></td>
		</tr>
		<tr>
			<td>Nomor Perkara</td> 
			<td>:</td>
			<td colspan=4><input id=notransaksi class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:98%;\"></td>
			
			<td>Jenis Perkara</td> 
			<td>:</td>
			<td colspan=4><select style=\"width:99%;\" id=jenisperkara>" . $optjnsperkara . "</select></td>
			
		</tr>
		<tr>
			<td>Nama Penggugat</td> 
			<td>:</td>
			<td colspan=4><input id=penggugat class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:98%;\"></td>
			
			<td>Nama Tergugat</td> 
			<td>:</td>
			<td colspan=4><input id=tergugat class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:98%;\"></td>
		</tr>
		<tr>
			<td>Jenis Pengadilan</td> 
			<td>:</td>
			<td colspan=4><input id=jenispengadilan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:98%;\"></td>
			
			<td>Lokasi Pengadilan</td> 
			<td>:</td>
			<td colspan=4><input id=lokasipengadilan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:98%;\"></td>
		</tr>
		<tr>
			<td>Advokat</td> 
			<td>:</td>
			<td colspan=4><input id=advokat class=myinputtext nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:98%;\"></td>
			
			<td>Tanggal Daftar Perkara</td> 
			<td>:</td>
			<td style='width:75px;'><input id='tanggalterdaftar' type='text' style='width:97%;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>

			<td>Tanggal Putusan</td> 
			<td>:</td>
			<td style='width:100px;'><input id='tanggalputusan' type='text' style='width:94%;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  /></td>
		</tr>
		<tr>
			<td valign=top>Ringkasan Kasus</td> 
			<td valign=top>:</td>
			<td colspan=10><textarea rows='4' cols='50' maxlength=512 id=ringkasan type=text nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:97%;\"></textarea></td>
		</tr><tr>
			<td valign=top>Amar Putusan</td> 
			<td valign=top>:</td>
			<td colspan=10><textarea rows='4' cols='50' maxlength=512 id=amarputusan type=text nkeypress=\"return_tanpa_kutip(event);\"  style=\"width:97%;\"></textarea></td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td colspan=10><input type=hidden id=method value='insert'>
				<button id=tomboldetail class=mybutton onclick=save()>" . $_SESSION['lang']['save'] . "</button>
				<button id=batal class=mybutton onclick=cleardetail()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
		</tr>
	</table></fieldset>
	<fieldset style='display: inline-block;vertical-align:top;min-height:310px'><legend>Upload</legend>
	<table border=0 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>Filename</td>
			<td>:</td>
			<td>
				<input type='file' name='upload' id='upload' >
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button id=btnsubmit class=mybutton onclick=\"submitfile()\">Submit</button>
			</td>
		</tr>
	</table>
	<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table class='sortable' cellspacing='1' border='0' width=100%>
			<thead>
			<tr class=rowheader>
				<td align='center' width=50px>No.</td>
				<td align='center' width=50px>File Type</td>
				<td align='center'>Filename</td>
				<td align='center' width=50px>Action</td>
			</tr>
			</thead>
			<tbody id='listfiles'>
			</tbody>
		</table>
	</fieldset>
		
	</fieldset>
	</fieldset>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>