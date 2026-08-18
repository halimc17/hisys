<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>

<?php
if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
{
    $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";   
}
else
{
    $sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK'";   

}
$optOrg="";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch())
{
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
        
        
}	

$optBrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iSup="SELECT kodebarang,namabarang FROM ".$dbname.".log_5masterbarang WHERE inactive='0' and kelompokbarang='400'"
        . " and kodebarang not in ('40000001','40000002','40000003') order by namabarang asc";
$nSup=$owlPDO->query($iSup) or die(print " Gagal: ".PDOException::getMessage());
$nSup->setFetchMode(PDO::FETCH_ASSOC);
while($dSup=$nSup->fetch())
{
    $optBrg.="<option value=".$dSup['kodebarang'].">".$dSup['namabarang']."</option>";
}

$arr="##kdOrgRep##kdBrgRep##tgl1Rep##tgl2Rep";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<script language=javascript>


	function batal()
	{
		document.getElementById('kdOrgRep').value='';
		document.getElementById('kdBrgRep').value='';
		document.getElementById('tgl1Rep').value='';	
		document.getElementById('tgl2Rep').value='';
		document.getElementById('printContainer').innerHTML='';	
	}


</script>
<?
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.strtoupper('OTHER PRODUCT STOCK').'</span><br>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('STOK PRODUK SAMPINGAN').'</span><br>');
}
echo "<fieldset style='float:left;'><legend><b>Form</b></legend>
	<table>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>
            <td><select id=kdOrgRep style=\"width:162px;\" >".$optOrg."</select></td>
        </tr>
	<tr>
		<td>".$_SESSION['lang']['namabarang']."</td>
		<td>:</td>
		<td><select id=kdBrgRep style='width:162px;'>".$optBrg."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
		s/d
		<input type='text' class='myinputtext' id='tgl2Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly></td>
	</tr>
	<tr>
		<td colspan=100></td>
	</tr>
	<tr>
		<td><td><td>
		<button onclick=zPreview('pabrik_slave_2stokProduk','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_slave_2stokProduk.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();

echo"
<div id='printContainer' >
</div>";

CLOSE_BOX();
echo close_body();
?>