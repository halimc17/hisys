<?//^@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script languange=javascript1.2 src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script languange=javascript1.2 src='js/formReport.js'></script>
<script languange=javascript1.2 src='js/zGrid.js'></script>
<script languange=javascript1.2 src='js/keu_2dividen.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>

<?

$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".organisasi where tipe='PT'" ;//where : noakun pajak (117 dan 213) dan detail=5
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optakun.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}


$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct tipetransaksi from ".$dbname.".keu_dividen" ;//where : noakun pajak (117 dan 213) dan detail=5
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $opttipe.="<option value='".$bar['tipetransaksi']."'>".$bar['tipetransaksi']."</option>";
}

$optno="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct notransaksi from ".$dbname.".keu_dividen" ;//where : noakun pajak (117 dan 213) dan detail=5
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optno.="<option value='".$bar['notransaksi']."'>".$bar['notransaksi']."</option>";
}




OPEN_BOX('','<span class=judul>'.getMenu('keu_2dividen').'</span>');
echo"<fieldset style='width:350px;'>
	<legend>Form</legend>
	<table>
	<tr>
			<td>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td><select id=pt style=width:250px onchange=getUnit(this)>".$optakun."</select></td>
	</tr>

		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>	
			<td><select id=unit style=width:250px><option> ".$_SESSION['lang']['pilihdata']."</option></select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td><select id=tipe style=width:250px>".$opttipe."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td><select id=notransaksi style=width:250px>".$optno."</select></td>
		</tr>


	 </table>
	 <input type=hidden id=method value='preview'>
	 <button class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
	<button class=mybutton onclick=excel(event,'keu_slave_2dividen.php')>Excel</button>
	 <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
	 </fieldset>";
	 
echo"<fieldset style='clear:both;max-width:1235px;'><legend><b>Print Area</b></legend>
    <div id='awal'>
        <div id='container' style='overflow:auto;height:350px;max-width:1235px;'>

        </div>
    </div>
    <div id='detailData' style='display:none'>
        <div id='isiData'>
        </div>
    </div>
</fieldset>";
	 


CLOSE_BOX();
echo close_body();
?>