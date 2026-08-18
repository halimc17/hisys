<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formReport.php');
/** Controller **/
# Options
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
//     $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
//         "tipe='KEBUN'");
// } else {
//     $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
//         "kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
// }


$arrunit = getOrgDetail(1);
foreach ($arrunit as $key => $val) {
	$optOrg[$key] =$val;			
}

$optTahunTanam = makeOption($dbname,'setup_blok','tahuntanam,tahuntanam',
    "left(kodeorg,4)='".$_SESSION['empl']['lokasitugas']."'",'0',true);
$optTahunTanam[''] = $_SESSION['lang']['all'];

if($_SESSION['language']=='EN'){
$optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan1,kelompok',
    "kelompok in ('BBT', 'TB', 'TBM', 'TM')",'2',true);
}else{
$optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,kelompok',
    "kelompok in ('BBT', 'TB', 'TBM', 'TM')",'2',true);
}        
$optKegiatan[''] = $_SESSION['lang']['all'];

$optBarang[''] = $_SESSION['lang']['all'];
$str="select distinct a.kodebarang, b.namabarang from ".$dbname.".kebun_pakaimaterial a 
    left join ".$dbname.".log_5masterbarang b on a.kodebarang = b.kodebarang 
    where a.kodebarang like '3%'
    order by a.kodebarang asc";
$que=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$que->setFetchMode(PDO::FETCH_ASSOC);
while($row=$que->fetch())
{
    $optBarang[$row['kodebarang']]=$row['kodebarang'].' '.$row['namabarang'];
}

//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`, `TH`) VALUES ('lapmaterial', 'Laporan Pakai Material', 'kebun', NULL, 'Laporan Pakai Material', 'Material Usage Report', 'Material Usage Report');

if(!isset($_SESSION['lang']['lapmaterial'])) {
    $_SESSION['lang']['lapmaterial'] = ucfirst('pakaimaterial');
}
$fReport = new formReport('pakaimaterial','kebun_slave_2pakaimaterial',$_SESSION['lang']['form']);
$fReport->addPrime('kodeorg',$_SESSION['lang']['kodeorg'],'','select','L',20,$optOrg);
$fReport->addPrime('periode',$_SESSION['lang']['periode'],'','bulantahun','L',27);
#$fReport->addPrime('tahuntanam',$_SESSION['lang']['tahuntanam'],'','select','L',20,$optTahunTanam);
$fReport->addPrime('kegiatan',$_SESSION['lang']['kegiatan'],'','selectsearch','L',20,$optKegiatan);
$fReport->addPrime('barang',$_SESSION['lang']['kodebarang'],'','select','L',20,$optBarang);




/** View **/
echo open_body();
?>
<script language="JavaScript1.2" src="js/formReport.js"></script>
<script language="JavaScript1.2" src="js/biReport.js"></script>
<link rel="stylesheet" type="text/css" href="style/zTable.css">
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2pakaimaterial').'</span>');
$fReport->render();
CLOSE_BOX();

echo close_body();
?>