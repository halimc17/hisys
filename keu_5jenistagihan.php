<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/keu_5jenistagihan.js?v=<?php echo time(); ?>'></script>


<?php
	OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['tipeinvoice']).'</span>');
	$arrJrn=array("1"=>"Jurnal","0"=>"Tidak Jurnal");
	foreach($arrJrn as $rw=>$nmJrn){
		@$optJrn.="<option value='".$rw."'>".$nmJrn."</option>";
	}

	$str1=$owlPDO->query("select tipe from ".$dbname.".log_5klsupplier where sync='1' order by tipe asc");
	$str1->setFetchMode(PDO::FETCH_ASSOC);
	while($rtr1=$str1->fetch()){
	    @$opttipe.="<option value='".$rtr1['tipe']."'>".$rtr1['tipe']."</option>";
	}

?>
<div style='clear:both;'></div>
<fieldset style='width:450px;float:left; height:220px;'>
	<legend><?php echo $_SESSION['lang']['form'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['kode']?></td>
			<td>:</td>
			<td>
				<input style="width:70px" class="myinputtext" id='kode' name='kode' onkeypress='return tanpa_kutip(event);' maxlength=3>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['namajenisvhc']?></td>
			<td>:</td>
			<td>
				<input style="width:200px" class="myinputtext" id='namajenis' name='namajenis' onkeypress='return tanpa_kutip(event);' maxlength=200>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['sumber'] ?></td>
			<td>:</td>
			<td>
				<input style="width:200px" class="myinputtext" id='sumber' name='sumber' onkeypress='return tanpa_kutip(event);' maxlength=200>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['jurnal'] ?></td>
			<td>:</td>
			<td>
				<select id="statJrn" style="width:200px"><?php echo $optJrn; ?></select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['tipe'].' '.$_SESSION['lang']['supplier'] ?></td>
			<td>:</td>
			<td>
				<select id="tipesup" style="width:200px"><?php echo $opttipe; ?></select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['transaksi']." Rutin" ?></td>
			<td>:</td>
			<td>
				<input id="transaksirutin" type='checkbox' > Ya / Tidak
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['status'] ?></td>
			<td>:</td>
			<td>
				<input id="status" type='checkbox' checked> Aktif / Non-Aktif
			</td>
		</tr>
		<tr>
			<td><td><td>
			<input type="hidden" value="insert" id="method"  />
			<button class=mybutton onclick=simpan()><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick=batal()><?php echo $_SESSION['lang']['cancel']?></button>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset style='text-align:left; height:170px;width:450px;'>
<legend><b><img src=images/info.png align=left height=35px valign=asmiddle>[Info]</b></legend>
<br />
<ol type='a'>
<li>Kode Tipe Invoice Max. 3 Karakter.</li>
<li>Definisi sumber pada form ini adalah pencarian no dokumen yang muncul dari transaksi lain, Contoh : Pada jenis PO sumber terisi maka pencarian pada transaksi pembuatn PO</li>
<li>Jika sumber terisi maka harus mengupdate script pencarian PO, menambahkan case pada file keu_slave_getpotagihan.php</li>
</ol>
</fieldset>
<?php 
	CLOSE_BOX();
	OPEN_BOX();
?>
<fieldset>
	<legend><?php echo $_SESSION['lang']['list']." ".$_SESSION['lang']['tipeinvoice'] ?></legend>
	<div style='height:350px;overflow:auto;'>
	<table class="sortable" cellspacing="1" cellpadding="3" border="0">
		<thead>
			<tr class=rowheader>
				<td><?php echo $_SESSION['lang']['nourut']?></td>
				<td><?php echo $_SESSION['lang']['kode']?></td>
				<td><?php echo $_SESSION['lang']['namajenisvhc'];?></td> 
				<td><?php echo $_SESSION['lang']['sumber'];?></td>
				<td><?php echo $_SESSION['lang']['tipe'].' '.$_SESSION['lang']['supplier'];?></td>
				<td><?php echo $_SESSION['lang']['transaksi']." Rutin";?></td>
				<td><?php echo $_SESSION['lang']['jurnal'];?></td>
				<td><?php echo $_SESSION['lang']['status'];?></td>
				<td colspan="3" style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></td>
			</tr>
		</thead>
		<tbody id="container">
		<script>loadData()</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table></div>
</fieldset>
<?
CLOSE_BOX();
echo close_body();
?>