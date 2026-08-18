<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formReport.php');

/** Controller **/
# Options
$optPT = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
$optKebun = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN' and induk='".key($optPT)."'",null,1);
$optKebun[''] = $_SESSION['lang']['all'];
$optTipe = array(
	'RAWAT' => 'Perawatan Kebun',
	'BIBIT' => 'Perawatan Bibit',
);

$optTipeKebun = array(
	'' => $_SESSION['lang']['all'],
	'i' => 'Inti',
	'p' => 'Plasma',
);

$fReport = new formReport('cropstatistic','kebun_slave_2accreport',$_SESSION['lang']['form']);
$fReport->addPrime('pt',$_SESSION['lang']['pt'],'','select','L',20,$optPT);
$fReport->_primeEls[0]->_attr['onchange'] = "getKebun()";
$fReport->addPrime('kebun',$_SESSION['lang']['kebun'],'','select','L',20,$optKebun);
$fReport->addPrime('tipekebun',$_SESSION['lang']['intiplasma'],'','select','L',20,$optTipeKebun);
$fReport->addPrime('tanggal',$_SESSION['lang']['tanggal'],date('d-m-Y'),'date','L',11);
$fReport->addPrime('tipe',"Tipe",'','select','L',20,$optTipe);
$fReport->_detailHeight = 60;
$fReport->_noPdf = true;
$fReport->_noExcel = true;

/** View **/
echo open_body();
?>
<script src="js/formReport.js"></script>
<script src="js/biReport.js"></script>
<script src="js/kebun_2accreport.js"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['accreport']).'</span>');
$fReport->render();
CLOSE_BOX();

echo "<script>getById('workField').style.height='350px';getById('workField').style['max-width']='1235px';</script>";
echo close_body();
?>