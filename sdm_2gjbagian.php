<?//@Copy nangkoelframework//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/zReport.js'></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/option.js'></script>

<script language=javascript>


function batal(){
	document.getElementById('unit').value='';	
	document.getElementById('bag').value='';
	document.getElementById('per').value='';
	document.getElementById('printContainer').innerHTML='';	
}


</script>


<?
include('master_mainMenu.php');

if($_SESSION['language']=='ID'){
	OPEN_BOX('','<span class=judul>'.getMenu('sdm_2gjbagian').'</span><br>');
}else{
	OPEN_BOX('','<span class=judul>'.getMenu('sdm_2gjbagian').'</span><br>');
}

$optper=$optmill=$Div="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where = "";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
} else {
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optmill.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$arrGj=array("Harian"=>"Harian","Bulanan"=>"Bulanan");
$optGj="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrGj as $brs1 => $isi1)
{
	$optGj.="<option value=".$brs1.">".$isi1."</option>";
}


$arr="##per##unit##bag##gaji";	

echo "<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td ><select class=select2 style=width:200px onchange=getDiv() id=unit>".$optmill."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['divisi']."</td>
		<td>:</td>
		<td><select class=select2 style=width:200px id=bag>".$Div."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['metodepanggajian']."</td>
		<td>:</td>
		<td><select class=select2 style=width:200px id=gaji>".$optGj."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select class=select2 style=width:200px id=per>".$optper."</select></td>
	</tr>	
	
	

	
	<tr>
		<td><td><td>
		<button onclick=zPreview('sdm_slave_2gjbagian','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'sdm_slave_2gjbagian.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo "
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%';></div>";

CLOSE_BOX();
echo close_body();
?>