<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

?>

<script language=javascript1.2 src='js/keu_2pdo.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>


<?
$arrnmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$optjenis=$optorg=$optvhc=$optper="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
	$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)=4 
			order by namaorganisasi asc ";
}
else if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
	$str = "select * from " . $dbname . ".bgt_regional_assignment where regional = '".$_SESSION['empl']['regional']."' ";
				// and (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL')  and induk!='' order by namaorganisasi asc ";
}
else
{
	$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)=4 
			and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'"." order by namaorganisasi asc ";
}

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
		@$optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}


$str = "select distinct(periode) as periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 12 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$str = "select distinct(tipepdo) as tipepdo from " . $dbname . ".keu_pdoht";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $optjenis.="<option value=" . $bar['tipepdo'] . ">" . $bar['tipepdo'] . "</option>";
}
$optjenis.="<option value='REKAP'>" . strtoupper($_SESSION['lang']['rekap'])  . "</option>";

OPEN_BOX('','<span class=judul>'.getMenu('keu_2pdo').'</span>');
echo"<fieldset style='width:450px;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:159px;\">" . $optorg . "</select></td>
                </tr>
                
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=per style=\"width:159px;\">" . $optper . "</select></td>
                </tr>   

				<tr>
                    <td>" . $_SESSION['lang']['jenis'] . "</td>
                    <td>:</td>
                    <td><select id=jenis style=\"width:159px;\">" . $optjenis . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
						<button class=mybutton onclick=html('html')>".$_SESSION['lang']['html']."</button>
						<button class=mybutton onclick=excel('excel',event)>".$_SESSION['lang']['excel']."</button>
						<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
					</td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'; >
</div></fieldset>"; //<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>
CLOSE_BOX();
echo close_body();
?>