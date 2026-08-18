<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');

$max_id=0;
$str=$owlPDO->query("select max(id) as id from ".$dbname.".menu");
$str->setFetchMode(PDO::FETCH_NUM);
while($bar=$str->fetch()){
	$max_id=$bar[0];
}
echo"<script language=javascript1.2>max_id=".$max_id."</script>";
?>
<script type="text/javascript" src="js/main_rolesmenu.js?v=<?php echo date("ymdhis"); ?>"></script>
<link rel=stylesheet type=text/css HREF=style/privillages.css>
<?php


$arrtipe=array('1'=>'Aktip','0'=>'Non Aktip');
foreach($arrtipe as $val => $key){
	$opttipe.="<option value='".$val."'>".$key."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('main_rolesmenu').'</span><br>');
?>
<fieldset style='float:left;'>
	<legend><?php echo $_SESSION['lang']['form'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td style="vertical-align:top;"><?php echo $_SESSION['lang']['nama']?> Role</td>
			<td style="vertical-align:top;">:</td>
			<td><input class=myinputtext style="width:195px" id='nama' name='nama'></td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['status']?></td>
			<td>:</td>
			<td>
				<select style="width:200px" id='status' name='status'>
					<?php echo $opttipe ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><td><td>
			<input type='hidden' id="id">
			<input type='hidden' value="insert" id="method">
			<button class=mybutton onclick=simpan()><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick=batal()><?php echo $_SESSION['lang']['cancel']?></button>
			</td>
		</tr>
	</table>
</fieldset>
<?php 
CLOSE_BOX();
OPEN_BOX();
echo"<div id=ctr style='position:absolute;display:none;'></div>";
echo"<div id=ctrmenu style='position:absolute;display:none;'>";
	echo OPEN_THEME('Menu Mapping:');
		echo"<div id=contentmenu></div>";
	echo CLOSE_THEME();
echo"</div>";
?>
<fieldset style='float:left'>
	<legend><?php echo $_SESSION['lang']['find']?></legend>
		<table><tr>
				<td style="vertical-align:top;"><?php echo $_SESSION['lang']['nama']?> Role</td>
				<td>:</td>
				<td><input onkeyup=loaddata(); class='myinputtext' id='namacari' style='width:150px'></td>
				
				<td><button class=mybutton onclick=loaddata()><?php echo $_SESSION['lang']['preview']?></button></td>
			</tr></table>
	</fieldset>
	<div style='clear:both'></div>
	<table class="sortable" cellspacing="1" cellpadding="5" border="0">
		<thead>
			<tr class=rowheader>
				<td style="text-align:center;"><?php echo $_SESSION['lang']['nourut']?></td>
				<td style="text-align:center;">Nama Role</td>
				<td style="text-align:center;"><?php echo $_SESSION['lang']['status']?></td>
				<td style="text-align:center;"><?php echo $_SESSION['lang']['updateby'];?></td>
				<td style="text-align:center;"><?php echo $_SESSION['lang']['tanggal'];?></td>
				<td colspan="5" style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></td>
			</tr>
		</thead>
		<tbody id="container">
		<script>loaddata()</script>
		</tbody>
		<tfoot id='footer'>
		</tfoot>
	</table>
<?

CLOSE_BOX();
echo close_body();
?>