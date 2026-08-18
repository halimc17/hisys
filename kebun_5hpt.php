<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>

<script type="text/javascript" src="js/kebun_5hpt.js"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">

<?php
	//Get Data Kegiatan
	$optKegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select * from ".$dbname.".setup_kegiatan order by kodekegiatan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optKegiatan.="<option value='".$bar['kodekegiatan']."'>".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
	}
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['hpt']).'</span><br>');
?>
<fieldset style="float:left">
	<legend><?php echo $_SESSION['lang']['form'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['kegiatan']?></td>
			<td>:</td>
			<td>
				<select id='kegiatan'>
					<?php echo $optKegiatan ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['tipe']?></td>
			<td>:</td>
			<td>
				<input type="radio" name="tipe" value="s" checked>Sensus
				<input type="radio" name="tipe" value="p">Penanggulangan
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=simpan()><?php echo $_SESSION['lang']['save']?></button>
			</td>
		</tr>
	</table>
</fieldset>
<?php 
	CLOSE_BOX();
	OPEN_BOX();
?>
<fieldset>
	<legend><?php echo $_SESSION['lang']['list'] ?></legend>
	<div style="height:400px;overflow:auto;">
	<table class="sortable" cellspacing="1" cellpadding="3" border="0">
		<thead>
			<tr class=rowheader>
				<td><?php echo $_SESSION['lang']['nourut']?></td>
				<td><?php echo $_SESSION['lang']['kodekegiatan']?></td>
				<td><?php echo $_SESSION['lang']['namakegiatan'];?></td> 
				<td><?php echo $_SESSION['lang']['tipe'];?></td>
				<td colspan="2" style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></td>
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