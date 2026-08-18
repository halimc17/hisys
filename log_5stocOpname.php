<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script type=text/javascript src='js/zTools.js'></script>
<script type=text/javascript src='js/stockOpneme.js?v=<?= time(); ?>'></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('', '<span class=judul>' . getMenu('log_5stocOpname') . '</span>');

$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe = 'GUDANG'
	and kodeorganisasi like '" . $_SESSION['empl']['lokasitugas'] . "%'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optGudang = '';
while ($bar = $res->fetch()) {
	$optGudang .= "<option value='" . $bar->kodeorganisasi . "'>" . $bar->kodeorganisasi . " - " . $bar->namaorganisasi . "</option>";
}
echo "<fieldset>
	
	<table>
		<tr><td>" . $_SESSION['lang']['kodeorg'] . "</td><td>
		<select id=kodegudang style='width:300px'>
		" . $optGudang . "
		</select></td></tr>
		<tr>
			<td class=bintang>" . $_SESSION['lang']['materialname'] . "</td><td><span id=kodebarang></span><input type=text id=namadisabled style='width:295px' class=myinputtext disabled>
			<img src=images/search.png class=dellicon title='" . $_SESSION['lang']['find'] . "' onclick=\"searchBarang('" . $_SESSION['lang']['findmaterial'] . "','<fieldset><legend>" . $_SESSION['lang']['findmaterial'] . "</legend>Find<input type=text class=myinputtext id=namabrg><button class=mybutton onclick=findBarang()>Find</button></fieldset><div id=container style=\'overflow:auto;height:352px\'></div>',event);\">
			</td>
		</tr>
		<tr>
			<td class=bintang>" . $_SESSION['lang']['jenis'] . "</td>
			<td>
				<select id='jenisAdjust' onchange='changeJenis()' style='width:300px'>
					<option value='in'>" . $_SESSION['lang']['masuk'] . "</option>
					<option value='out'>" . $_SESSION['lang']['keluar'] . "</option>
				</select>
			</td>
		</tr>
		<tr><td class=bintang>" . $_SESSION['lang']['jumlah'] . "</td><td><input type=text id=jumlah value=0 class=myinputtextnumber onkeypress=\"return angka_doang(event);\" size=5 style='width:295px'><span id=sat></span></td></tr>
		<tr><td class=bintang>" . $_SESSION['lang']['hargasatuan'] . "(Rp)</td><td><input type=text id=harga class=myinputtextnumber value=0 onkeypress=\"return angka_doang(event);\" style='width:295px' size=12><div id='divChkNol' style='display:none'><input type=checkbox id=chkNol onchange='checkChkNol();'>* Beri tanda checklist untuk untuk membuat harga satuan menjadi 0 (nol)</div></td></tr>
		<tr><td class=bintang>" . $_SESSION['lang']['tanggal'] . "</td><td><input type=text class=myinputtext id=tgladj onmousemove=setCalendar(this.id) onkeypress=return false; style='width:295px'  size=10 maxlength=10 readonly/></td></tr>
		<tr>
			<td>No Transaksi Referensi</td>
			<td><input type=text id=notransreferensi maxlength=25 size=25 style='width:295px' class=myinputtext></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['keterangan'] . "</td>
			<td><input type=text id=keterangan maxlength=80 size=80 style='width:295px' class=myinputtext></td>
		</tr>
		<tr>
			<td class=bintang>" . $_SESSION['lang']['upload'] . "</td>
			<td>
				<input style='width:291px;' type='file' name='upload' id='upload' class=mybutton>
			</td>
		</tr>
	</table>
    <!--<button class=mybutton onclick=saveAdjustment()>" . $_SESSION['lang']['save'] . "</button>-->
    <button class=mybutton onclick=savelistAdjustment()>" . $_SESSION['lang']['save'] . "</button>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset>
<legend>" . $_SESSION['lang']['list'] . "</legend>
			<table class='sortable' cellspacing='1' style='width:100%' cellpadding='3' border='0'>
				<thead>
				<tr class=rowheader>
					<th align='center'>No.</th>
					<th align='center'>" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align='center'>" . $_SESSION['lang']['kodeorg'] . "</th>
					<th align='center'>" . $_SESSION['lang']['kodebarang'] . "</th> 
					<th align='center'>" . $_SESSION['lang']['namabarang'] . "</th> 
					<th align='center'>" . $_SESSION['lang']['jenis'] . "</th> 
					<th align='center'>" . $_SESSION['lang']['jumlah'] . "</th>
					<th align='center'>" . $_SESSION['lang']['hargasatuan'] . " (Rp)</th>
					<th align='center'>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align='center'>" . $_SESSION['lang']['notransaksi'] . " Referensi</th>
					<th align='center'>" . $_SESSION['lang']['keterangan'] . "</th>
					<th align='center'>" . $_SESSION['lang']['dibuat'] . "</th>
					<th align='center' colspan=3>Action</th>
				</tr>
				</thead>
				<tbody id='liststocOpname'>
				<script>loadData(0)</script>
				</tbody>
				<tfoot id='containft'></tfoot>
			</table>
</fieldset> ";

?>