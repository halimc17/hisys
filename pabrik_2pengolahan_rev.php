<?//@Copy nangkoelframework//@ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src='js/pabrik_2pengolahan.js'></script>

<script language=javascript>


	function batal()
	{
		document.getElementById('kdsup').value='';
		document.getElementById('tgl2').value='';	
		document.getElementById('tgl1').value='';
		document.getElementById('printContainer').innerHTML='';	
	}


</script>

<?  
$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK' ORDER BY kodeorganisasi";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch()){
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}                        
			
?>


<?
include('master_mainMenu.php');
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Mill Pcrocessing Report').'</span><br>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('Laporan Pengolahan').'</span><br>');
}
//$arr="##tgl1##tgl2";	
$arr="##kdpabrik##tgl1##tgl2";
echo "
<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select id=kdpabrik style=\"width:163px;\" >".$optOrg."</select></td>
        </tr>
	
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
		s/d
		<input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly></td>
	</tr>	

	<tr>
		<td></td>
	</tr>
	<tr>
		<td><td><td>
		<button onclick=zPreview('pabrik_slave_2pengolahan_rev','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_slave_2pengolahan_rev.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>
CLOSE_BOX();
OPEN_BOX();
echo "

<div id='printContainer' >
</div>";

CLOSE_BOX();
echo close_body();

?>