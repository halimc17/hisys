<?php
error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once ('lib/zLib.php');

# Get POST Data
$afdeling = $_POST['afdeling'];

#========Get Blok Ids============
# Create Condition
$where1 = "(tipe='BLOK' or tipe='BIBITAN') and induk='".$afdeling."'";

# Get Org Data
$query = selectQuery($dbname,'organisasi',"kodeorganisasi",$where1);
$data = fetchData($query);
# Create Condition for Table
$where2 = array();
foreach($data as $key=>$row) {
    $where2[] = array('kodeorg'=>$row['kodeorganisasi']);
}
if(count($where2)<1)
{
    exit("Error:Tidak ada data");
}
$where2['sep'] = 'OR';

#========Start Make Table
# Prep
$fieldStr = '##kodeorg##indukblok##tahuntanam##luasareaproduktif##luasareanonproduktif'.
    '##jumlahpokok##statusblok##bulanmulaipanen##tahunmulaipanen##kodetanah'.
    '##klasifikasitanah##topografi##intiplasma##jenisbibit##buahkecil'.
'##cadangan##okupasi##rendahan##sungai##rumah##kantor##pabrik##jalan##kolam##umum##arealberbatu##konservasi##enclave##lc##luasbloking';
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

# Get Data
#$query = selectQuery($dbname,'setup_blok',"*",$where2);
#$data = fetchData($query);

# Set Header Name
$head = array();
$head[0]['name'] = $_SESSION['lang']['blok'];
$head[1]['name'] = $_SESSION['lang']['blok'].' '.$_SESSION['lang']['induk'];
$head[2]['name'] = $_SESSION['lang']['tahuntanam'];
// $head[2]['name'] = $_SESSION['lang']['kelaspohon'];
$head[3]['name'] = $_SESSION['lang']['luasareaproduktif'];
$head[4]['name'] = $_SESSION['lang']['luasareanonproduktif'];
$head[5]['name'] = $_SESSION['lang']['jumlahpokok'];
$head[6]['name'] = $_SESSION['lang']['statusblok'];
$head[7]['name'] = $_SESSION['lang']['mulaipanen'];
$head[8]['name'] = $_SESSION['lang']['kodetanah'];
$head[9]['name'] = $_SESSION['lang']['klasifikasitanah'];
$head[10]['name'] = $_SESSION['lang']['topografi'];
$head[11]['name'] = $_SESSION['lang']['intiplasma'];
$head[12]['name'] = $_SESSION['lang']['jenisbibit'];
$head[13]['name'] = $_SESSION['lang']['basisbuah'];
//$head[13]['name'] = $_SESSION['lang']['tanggal'];
$head[14]['name'] = $_SESSION['lang']['cadangan'];
$head[15]['name'] = $_SESSION['lang']['okupasi'];
$head[16]['name'] = $_SESSION['lang']['rendahan'];
$head[17]['name'] = $_SESSION['lang']['sungai'];
$head[18]['name'] = $_SESSION['lang']['rumah'];
$head[19]['name'] = $_SESSION['lang']['kantor'];
$head[20]['name'] = $_SESSION['lang']['pabrik'];
$head[21]['name'] = $_SESSION['lang']['jalan'];
$head[22]['name'] = $_SESSION['lang']['kolam'];
$head[23]['name'] = $_SESSION['lang']['umum'];

$head[24]['name'] = $_SESSION['lang']['arealberbatu'];
$head[25]['name'] = $_SESSION['lang']['konservasi'];
$head[26]['name'] = $_SESSION['lang']['enclave'];
$head[27]['name'] = $_SESSION['lang']['lc'];
$head[28]['name'] = $_SESSION['lang']['luasbloking'];




$head[0]['align'] = 'center';
$head[1]['align'] = 'center';
$head[2]['align'] = 'center';
$head[3]['align'] = 'center';
$head[4]['align'] = 'center';
$head[5]['align'] = 'center';
$head[6]['align'] = 'center';
$head[7]['align'] = 'center';
$head[8]['align'] = 'center';
$head[9]['align'] = 'center';
$head[10]['align'] = 'center';
$head[11]['align'] = 'center';
$head[12]['align'] = 'center';
$head[13]['align'] = 'center';
$head[14]['align'] = 'center';
$head[15]['align'] = 'center';
$head[16]['align'] = 'center';
$head[17]['align'] = 'center';
$head[18]['align'] = 'center';
$head[19]['align'] = 'center';
$head[20]['align'] = 'center';
$head[21]['align'] = 'center';
$head[22]['align'] = 'center';

$head[23]['align'] = 'center';
$head[24]['align'] = 'center';
$head[25]['align'] = 'center';
$head[26]['align'] = 'center';
$head[27]['align'] = 'center';
$head[28]['align'] = 'center';
// $head[25]['align'] = 'center';
//$head[23]['align'] = 'center';

# Set Span
$head[7]['span'] = '2';

# Set Display Type
$conSetting = array();
$conSetting['luasareaproduktif']['type'] = 'currency';
$conSetting['luasareanonproduktif']['type'] = 'currency';
$conSetting['jumlahpokok']['type'] = 'numeric';
$conSetting['bulanmulaipanen']['type'] = 'month';
$conSetting['cadangan']['type'] = 'numeric';
$conSetting['okupasi']['type'] = 'numeric';
$conSetting['rendahan']['type'] = 'numeric';
$conSetting['sungai']['type'] = 'numeric';
$conSetting['rumah']['type'] = 'numeric';
$conSetting['kantor']['type'] = 'numeric';
$conSetting['pabrik']['type'] = 'numeric';
$conSetting['jalan']['type'] = 'numeric';
$conSetting['kolam']['type'] = 'numeric';
$conSetting['umum']['type'] = 'numeric';

$conSetting['arealberbatu']['type'] = 'numeric';
$conSetting['konservasi']['type'] = 'numeric';
$conSetting['enclave']['type'] = 'numeric';
$conSetting['lc']['type'] = 'numeric';
$conSetting['luasbloking']['type'] = 'numeric';



# Display Table
$master = masterTableBlok($dbname,'setup_blok',1,$fieldArr,$head,$conSetting,$where2,
    array(),'setup_slave_blok_pdf');
try {
	
    echo $master;
} catch(Exception $e) {
    echo "Create Table Error";
}
?>