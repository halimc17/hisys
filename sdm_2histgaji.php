<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formReport.php');

/** Controller **/
# Options
$optCmp = makeOption($dbname,'sdm_ho_component','id,name',"type='basic'",'0',true);
$optCmp[''] = $_SESSION['lang']['all'];

$fReport = new formReport('histgaji','sdm_slave_2histgaji',$_SESSION['lang']['form']);
$fReport->addPrime('tahun',$_SESSION['lang']['tahun'],date('Y'),'textnum','L',10);
$fReport->addPrime('karyawan',$_SESSION['lang']['namakaryawan'],'','text','L',24);
$fReport->addPrime('komponen',$_SESSION['lang']['idkomponen'],'','select','L',25,$optCmp);
// $fReport->addPrime('periode',$_SESSION['lang']['periode'],date('d-m-Y'),'period','L',10);
$fReport->_detailHeight = 60;

/** View **/
echo open_body();
?>
<script language="JavaScript1.2" src="js/formReport.js"></script>
<script language="JavaScript1.2" src="js/biReport.js"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['histgaji']).'</span>');
$fReport->render();
CLOSE_BOX();

echo close_body();
?>