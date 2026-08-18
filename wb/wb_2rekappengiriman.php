<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('','<span class=judul>'.getMenu('wb_2rekappengiriman').'</span><br>');

$optproduk="<option value=''>Pilih Data</option>";
$optso="<option value=''>Pilih Data</option>";
$str="select * from ".$dbname.".msproduk where statusproduk='1'";
$res=fetchdata($str);
foreach($res as $val){
	$optproduk.="<option value='".$val['kodeproduk']."'>".$val['namaproduk']."</option>";	
}
date_default_timezone_set('Asia/Jakarta');
$tanggal=date("d-m-Y");

echo"<fieldset style='min-width:100px;float:left'>
	<table border=0 cellpadding=3>
		<tr>
			<td>Produk</td>
			<td>:</td>
			<td>
				<select class='select2' style='width:200px' id='produk' tabindex=2>".$optproduk."</select>
			</td>
		</tr>
		<tr>
			<td>No SO</td>
			<td>:</td>
			<td>
				<select class='select2' style='width:200px' id='noso' tabindex=2>".$optso."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button tabindex=111110 class=mybutton id=btnpreview style='height:25px'>Preview</button>
			</td>
		</tr>
	</table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id='output' style=min-height:400px></div>";
CLOSE_BOX();

?>
<script language=javascript src='js/wb_2rekappengiriman.js?v=<?php echo time(); ?>'></script>
<script>
	getSelect2();
</script>
<?
echo close_body();
?>