<?//@Copy nangkoelframework//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();



?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

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



$optsup="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql="SELECT namasupplier,`kodetimbangan` FROM ".$dbname.".log_5supplier WHERE kodetimbangan!='' order by namasupplier asc";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch())
			{				$optsup.="<option value=".$data['kodetimbangan'].">".$data['namasupplier']."</option>";
			}
                        
                        
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK' ORDER BY kodeorganisasi";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while($data=$qry->fetch()){
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}                        
			
?>


<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('Harga TBS(FFB Price)').'</span><br>');
$arr="##kdorg##kdsup##tgl1##tgl2";	

echo "<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>
            <td><select id=kdorg style=\"width:165px;\" >".$optOrg."</select></td>
        </tr>
	<tr>
		<td>Suplier</td>
		<td>:</td>
		<td><select id=kdsup style='width:165px;'>".$optsup."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
		s/d
		<input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
	</tr>	

	<tr>
		<td></td>
	</tr>
	<tr>
		<td><td><td>
		<button onclick=zPreview('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_slave_2hargatbs.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>
CLOSE_BOX();
OPEN_BOX();
echo "

<div id='printContainer'>
</div>";

CLOSE_BOX();
echo close_body();
?>