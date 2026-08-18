<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script type="text/javascript" src="js/sdm_5reminder.js"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
	##Get List PT
	$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe = 'PT' ";
	$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())
	{
		$optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
	}
	
	##Get List Departement
	$optdep="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select kode,nama from ".$dbname.".sdm_5departemen order by nama asc";
	$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())
	{
		$optdep.="<option value='".$bar['kode']."'>".$bar['nama']."</option>";
	}
	
	##Get List PIC
	$optpic="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['peringatan'].' Email').'</span>');
?>
<fieldset style='width:450px;'>
	<legend><?php echo $_SESSION['lang']['form'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['pt']?></td>
			<td>:</td>
			<td>
				<select style="width:200px" id='pt' onchange='getpic()'>
					<?php echo $optpt ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['departemen']?></td>
			<td>:</td>
			<td>
				<!-- <input type="text" id="kd_denda" size="5" class="myinputtext" maxlength="4" /> -->
				<select style="width:200px" id='departemen' onchange='getpic()'>
					<?php echo $optdep ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>PIC</td>
			<td>:</td>
			<td>
				<select style="width:200px" id='pic' onchange='getemail()'>
					<?php echo $optpic ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['email'] ?></td>
			<td>:</td>
			<td>
				<input style="width:195px" type="text" id="email" class="myinputtext" onKeyPress="return tanpa_kutip(event);"  />
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
<?php 
	CLOSE_BOX();
	OPEN_BOX();
?>
<fieldset>
	<legend><?php echo $_SESSION['lang']['list']." ".$_SESSION['lang']['peringatan']." Email" ?></legend>
	<div style='height:350px;overflow:auto;'>
	<table class="sortable" cellspacing="1" cellpadding="3" border="0">
		<thead>
			<tr class=rowheader>
				<td><?php echo $_SESSION['lang']['nourut']?></td>
				<td><?php echo $_SESSION['lang']['pt']?></td>
				<td><?php echo $_SESSION['lang']['departemen'];?></td>
				<td>PIC</td> 
				<td><?php echo $_SESSION['lang']['email'];?></td>
				<td colspan="2" style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></td>
			</tr>
		</thead>
		<tbody id="container">
		<script>loaddata()</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table></div>
</fieldset>
<?
CLOSE_BOX();
echo close_body();
?>