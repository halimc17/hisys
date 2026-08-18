<?//@Copy nangkoelframework
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

$namaorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
                        
 ////ind                       
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT distinct(kodeorg) as kodeorg FROM ".$dbname.".sdm_gajidetail_vw ORDER BY kodeorg";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch())
{
	$optOrg.="<option value=".$data['kodeorg'].">".$namaorg[$data['kodeorg']]."</option>";
}  

$optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iTahun="select distinct(substr(periodegaji,1,4)) as tahun from ".$dbname.".sdm_gajidetail_vw ";
$nTahun=$owlPDO->query($iTahun) or die(print " Gagal: ".PDOException::getMessage());
$nTahun->setFetchMode(PDO::FETCH_ASSOC);
while ($dTahun=  $nTahun->fetch())
{
    $optThn.="<option value=".$dTahun['tahun'].">".$dTahun['tahun']."</option>";
}

$optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iTahun="select distinct(substr(periodegaji,1,4)) as tahun from ".$dbname.".sdm_gajidetail_vw ";
$nTahun=$owlPDO->query($iTahun) or die(print " Gagal: ".PDOException::getMessage());
$nTahun->setFetchMode(PDO::FETCH_ASSOC);
while ($dTahun=  $nTahun->fetch())
{
    $optThn.="<option value=".$dTahun['tahun'].">".$dTahun['tahun']."</option>";
}
			
?>


<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('rekap potongan BPJS').'</span><br>');
//$arr="##tgl1##tgl2";	
$arr="##kdorg##thn";
echo "
<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select id=kdorg style=\"width:155px;\" >".$optOrg."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['tahun']."</td>
            <td>:</td>
            <td><select id=thn style=\"width:155px;\" >".$optThn."</select></td>
        </tr>
	<tr>
		<td><td><td>
		<button onclick=zPreview('sdm_slave_2bpjs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'sdm_slave_2bpjs.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>

echo "
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();




?>