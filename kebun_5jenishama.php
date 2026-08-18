<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>

<script type="text/javascript" src="js/kebun_5jenishama.js"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">

<?php
	//Get Data Satuan
	$optSatuan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select satuan from ".$dbname.".setup_satuan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar = $res->fetch()){
		$optSatuan .= "<option value='".$bar['satuan']."'>".$bar['satuan']."</option>";
	}
	
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['jenishama']).'</span>');
	echo "<fieldset style='width:450px;'>
		<legend>".$_SESSION['lang']['form']."</legend>
		<table cellpadding=1>
			<tr>
				<td>".$_SESSION['lang']['kodehama']."</td>
				<td>:</td>
				<td>
					<input type=text id=kodehama size=5 class=myinputtext maxlength='5' />
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['namahama']."</td>
				<td>:</td>
				<td>
					<input type=text id=namahama class=myinputtext maxlength='40' />
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>:</td>
				<td>
					<select id='satuan'>".$optSatuan."</select>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<input type=hidden id='method' value='insert' />
					<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
					<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
		</table>
	</fieldset>";
CLOSE_BOX();

OPEN_BOX();
	echo "<fieldset style='width:450px;'>
		<legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['jenishama']."</legend>
		<table class=sortable cellspacing=1 cellpadding=1 border=0>
			<thead>
				<tr class=rowheader>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['kodehama']."</td>
					<td>".$_SESSION['lang']['namahama']."</td>
					<td>".$_SESSION['lang']['satuan']."</td>
					<td colspan=2 style='text-align:center;'>".$_SESSION['lang']['action']."</td>
				</tr>
			</thead>
			<tbody id=container>
				<script>loadData()</script>
			</tbody>
			<tfoot>
			</tfoot>
		</table>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>