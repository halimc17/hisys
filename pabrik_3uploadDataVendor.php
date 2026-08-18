<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script>
dtAll='<?php echo"##dbnm##prt##pswrd##ipAdd##usrName##lksiServer##nmTable"; ?>';
</script>
<?php
$optd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optLksi=$optd;
$sLokasi="select id,lokasi from ".$dbname.".setup_remotetimbangan order by lokasi asc";
$qLokasi=$owlPDO->query($sLokasi) or die(print " Gagal: ".PDOException::getMessage());
$qLokasi->setFetchMode(PDO::FETCH_ASSOC);
while($rLokasi=$qLokasi->fetch())
{
	$optLksi.="<option value=".$rLokasi['id'].">".$rLokasi['lokasi']."</option>";
}

$lokasiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$arr="##dbnm##prt##pswrd##ipAdd##usrName##lksiServer##nmTable";
$tbl=array("Customer","Supplier","SIPB","Transportir");
foreach($tbl as $lsttbl)
{
    $optd.="<option value='".$lsttbl."'>".$lsttbl."</option>";
}
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/pabrik_3uploadDataVendor.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_3uploadDataVendor').'</span><br>');

echo"<div style='margin-bottom: 30px;'>
<fieldset style='float: left;'>
	<legend><b>".$_SESSION['lang']['form']."</b></legend>
	<table cellspacing='1' border='0' >
		<tr style='display:none'>
			<td>
				<label>".$_SESSION['lang']['lokasi']."</label>
			</td>
			<td> : </td>
			<td>
				<select id='lksiServer' name='lksiServer' style='width:150px' onchange='getDt()'>".$optLksi."</select>
			</td>
		</tr>
		<tr>
			<td>
				<label>".$_SESSION['lang']['tipe']."</label>
			</td>
			<td> : </td>
			<td>
				<select id='nmTable' name='nmTable' style='width:150px'>".$optd."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan='2'>
				<button onclick=\"zPreview('pabrik_slave_3uploadDataVendor','".$arr."','printContainer')\" class='mybutton' name='preview' id='preview'>Preview</button>
				<button onclick='unLockForm()' class='mybutton' name='cancel' id='cancel'>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
	<input type='hidden' name='dbnm' id='dbnm' />
	<input type='hidden' name='prt' id='prt' />
	<input type='hidden' name='pswrd' id='pswrd' />
	<input type='hidden' name='ipAdd' id='ipAdd' />
	<input type='hidden' name='usrName' id='usrName' />
</fieldset>
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>

<div id='printContainer'>
</div></fieldset>";

CLOSE_BOX();
echo close_body();
?>