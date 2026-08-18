<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script type="text/javascript" src="js/sdm_pph21det.js?v=<?php echo time(); ?>"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>PPH 21 Regular dan Iregular</span>');
$optjenis="<option value='regular'>Regular</option>";
$optjenis.="<option value='irregular'>Iregular</option>";
$optjenis.="<option value='PPH21'>PPH21</option>";


$optper=$optunit=$optkary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4  order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

?>
<fieldset style='width:500px;'>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo 'Unit' ?></td>
			<td>:</td>
			<td>
				<select id="unit" onchange=gantix()><?php echo $optunit ?></select>
			</td>
		</tr>
		<tr>
			<td><?php echo 'Periode' ?></td>
			<td>:</td>
			<td>
				<select id="periode"><?php echo $optper ?></select>
			</td>
		</tr>
		<tr>
			<td><?php echo 'Nama Karyawan' ?></td>
			<td>:</td>
			<td>
				<select id="karyawanid"><?php echo $optkary ?></select>
				<img id="karyawanid" onclick="z.elSearch('karyawanid',event)" class="zImgBtn" src="images/skyblue/zoom.png" style="position:relative;top:3px;left:3px;">
			</td>
		</tr>
		<tr>
			<td><?php echo 'Jenis' ?></td>
			<td>:</td>
			<td>
				<select id="jenis"><?php echo $optjenis ?></select>
			</td>
		</tr>
		<tr>
			<td><?php echo "Nilai" ?></td>
			<td>:</td>
			<td>
				<input type="text" id="nilai" class="myinputtextnumber" size="50" maxlength="40" />
			</td>
		</tr>
		<tr>
			<td colspan="2"></td>
			<td>
			<input type="hidden" value="insert" id="method"  />
			<button class=mybutton onclick=simpatpph21det()><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick=batalpph21det()><?php echo $_SESSION['lang']['cancel']?></button>
			</td>
		</tr>
	</table>
</fieldset>
<?php 
	CLOSE_BOX();
	OPEN_BOX();
?>
<fieldset>
	<legend><?php echo $_SESSION['lang']['list']." ".$_SESSION['lang']['traininginternal'] ?></legend>
	<table class="sortable" cellspacing="1" cellpadding="3" border="0">
		<thead>
			<tr class=rowheader>
				<td><?php echo $_SESSION['lang']['nourut']?></td>
				<td><?php echo 'Unit'?></td>
				<td><?php echo 'Periode'?></td>
				<td><?php echo 'Nama Karyawan'?></td>
				<td><?php echo 'Jenis'?></td>
				<td><?php echo 'Nilai'?></td>
				<td colspan="2" style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></td>
			</tr>
		</thead>
		<tbody id="container">
		<script>loadData()</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</fieldset>
<?
CLOSE_BOX();
echo close_body();
?>