<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript src='js/sdm_5suku.js?v=<?php echo time(); ?>'></script>



<?php
	OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['suku']).'</span>');

	$arrstatus=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);
	foreach($arrstatus as $kei=>$fal){
		$optstatus.="<option value='".$kei."'>".$fal."</option>";
	}  
?>


<fieldset style='width:450px;'>
	<legend><?php echo $_SESSION['lang']['form'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['id']?></td>
			<td>:</td>
			<td>
				<input style="width:70px" class="myinputtext" id='idsuku' name='idsuku' disabled onkeypress='return tanpa_kutip(event);' maxlength=10> *otomatis
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['namasuku']?></td>
			<td>:</td>
			<td>
				<input style="width:200px" class="myinputtext" id='namasuku' name='namasuku' onkeypress='return tanpa_kutip(event);' maxlength=200>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['status']?></td>
			<td>:</td>
			<td>
				<select id=aktif style="width:205px" ><?php echo ".$optstatus." ?></select>			
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
	<legend><?php echo $_SESSION['lang']['list']." ".$_SESSION['lang']['suku'] ?></legend>
	<div class='table-scroll'>
	<table class="sortable" cellspacing="1" cellpadding="5" style="width:100%;"  border="0">
		<thead>
			<tr class=rowcontent>
				<th><?php echo $_SESSION['lang']['id']?></th>
				<th><?php echo $_SESSION['lang']['namasuku'];?></th> 
				<th><?php echo $_SESSION['lang']['status'];?></th> 
				<th colspan="2" style="text-align:center;z-index:99"><?php echo $_SESSION['lang']['action']; ?></th>
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