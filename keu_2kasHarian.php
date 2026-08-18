<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formReport.php');

/** Controller **/
# Options
$fReport = new formReport('kasharian','keu_slave_2kasHarian',$_SESSION['lang']['form']);
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
						 "length(kodeorganisasi)=4");
    $fReport->addPrime('kodeorg',$_SESSION['lang']['kodeorg'],'','select','L',40,$optOrg);
}  
// update Oct 17, 2011 begin
// $as=Array('0'=>'Seluruhnya');
$as=Array();
// update Oct 17, 2011 end
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',"kasbank=1",'2');
$optbank = makeOption($dbname,'keu_5akunbank','noakun,noakun','','2');

$optAkun=$as+$optAkun;

$optbank['']=$_SESSION['lang']['pilihdata'];
$str="select * from ".$dbname.".keu_5akunbank  order by pemilik asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optNamaBank = makeOption($dbname,"keu_5daftarbank",'kodebank,namabank',"kodebank='".$bar['namabank']."'");
	$optbank[$bar['noakun']]=$bar['pemilik'].":".$optNamaBank[$bar['namabank']]." ".$bar['rekening'];
}

// echo"<pre>";
// print_r($optbank);
// echo"</pre>";
$fReport->addPrime('noakun',$_SESSION['lang']['noakun'],'','select','L',40,$optAkun);
$fReport->addPrime('bank',$_SESSION['lang']['bank'],'','select','L',40,$optbank);
$fReport->addPrime('periode',$_SESSION['lang']['periode'],date('d-m-Y'),'period','L',10);
$fReport->_detailHeight = 60;

/** View **/
echo open_body();
?>
<script language="JavaScript1.2" src="js/formReport.js"></script>
<script language="JavaScript1.2" src="js/biReport.js"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['kasharian']).'</span>');
$fReport->render('sadasdas');
CLOSE_BOX();

echo close_body();
?>