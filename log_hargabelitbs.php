<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/log_hargabelitbs.js'></script>
<script language="javascript" src='js/zTools.js'></script>

<?php
$optsupp=$optmill= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
		left join log_5supkelompok b on a.supplierid=b.supplierid
		where a.status=1 and b.tipe='SUPPLIERTBS' order by a.namasupplier asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optsupp.="<option value=" . $bar['supplierid'] . ">" . $bar['namasupplier'] . "</option>";
}
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi
		where tipe='PABRIK' order by kodeorganisasi asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optmill.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}



OPEN_BOX('','<span class=judul>'.getMenu('log_hargabelitbs').'</span>');
echo"<div><fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td> 
			<td>:</td>
			<td><input type=text  id=notransaksi nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" placeholder=\"auto generate\" disabled></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td> 
			<td>:</td>
			<td><input type='text' onchange=getNotransaksi() class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:200px;' value='".date('d-m-Y')."' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['pabrik']."</td> 
			<td>:</td>
			<td><select id=pabrik  onchange=getNotransaksi() style=\"width:205px;\">" . $optmill . "</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['supplier']."</td> 
			<td>:</td>
			<td><select id=supplier  style=\"width:205px;\">" . $optsupp . "</select>
				<img id='supplier' onclick=z.elSearch('supplier',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['harga']."</td> 
			<td>:</td>
			<td><input type=text  id=harga nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:200px;\" maxlength=100 ></td>
		</tr>
		<tbody id='trapproval'>
		</tbody>
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<input type='hidden' id='countapproval' value=0>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
				<input type=hidden id=method value='insert'>
			</td>
		</tr>
	</table>

</fieldset><div>";

CLOSE_BOX();
?>

<?php
OPEN_BOX();
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['find']."</legend>
							".$_SESSION['lang']['notransaksi']." : 
							<input type=text id=find_notransaksi nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:110px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
							 &nbsp".$_SESSION['lang']['tanggal']." : 
							<input type=text placeholder=yyyy-mm-dd id=find_tgl nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
							 &nbsp".$_SESSION['lang']['supplier']." : 
							<input type=text  id=find_supplier nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:150px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
							
							<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
							<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
							
						</fieldset>
					</td> 
				</tr>
			</table>
		
        <div id=container> 
            <script>loaddata(0)</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>