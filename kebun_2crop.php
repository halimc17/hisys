<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formReport.php');

/** Controller **/
# Options
$optKebun = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN'");
$optPeriod = array();
$optTp = array(""=>$_SESSION['lang']['all'],"I"=>"Inti","P"=>"Plasma");
$sData="select distinct left(tanggal,7) as periode from ".$dbname.".kebun_aktifitas order by tanggal desc";
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qData->fetch()){
	$optPeriod[$rData['periode']] = $rData['periode'];
}
// for($i=date('Y')-1; $i <= date('Y')+1; $i++) {
// 	for($j=1;$j<=12;$j++) {
// 		$val = $i.'-'.str_pad($j,2,'0',STR_PAD_LEFT);
// 		$optPeriod[$val] = $val;
// 	}
// }

$fReport = new formReport('cropstatistic','kebun_slave_2crop',$_SESSION['lang']['form']);
$fReport->addPrime('kebun',$_SESSION['lang']['kebun'],'','select','L',20,$optKebun);
$fReport->addPrime('periode',$_SESSION['lang']['periode'],date('Y-m'),'select','L',20,$optPeriod);
$fReport->addPrime('intiplasma',$_SESSION['lang']['intiplasma'],'','select','L',20,$optTp);
$fReport->_detailHeight = 60;
$fReport->_noPdf = true;

/** View **/
echo open_body();
?>
<script src="js/formReport.js"></script>
<script src="js/biReport.js"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['cropstatistic']).'</span><br>');
$fReport->render();
CLOSE_BOX();

echo close_body();
?>