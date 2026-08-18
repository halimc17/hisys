<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zSearch.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
// Where Condition for Main Query
$cond = "kodegudang like '".$_SESSION['empl']['lokasitugas']."%'";

# Options
$whereOrg = "tipe like '%GUDANG%' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
$optGudang = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg,'1');

// Masking Barang
$qData = selectQuery($dbname,'log_5kartubin','kodebarang',$cond);
$resData = fetchData($qData);
$strWhere = "";
foreach($resData as $row) {
	if(!empty($strWhere)) {
		$strWhere .= ",";
	}
	$strWhere .= "'".$row['kodebarang']."'";
}
if(!empty($strWhere)) {
	$whereBarang = "kodebarang in (".$strWhere.")";
	$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whereBarang,'1');
} else {
	$optBarang = array();
}

// Options for passing
$opt = array('kodegudang' => $optGudang,'kodebarang'=> $optBarang);

#=======Form============
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['nokartubin']).'</span>');
echo "<div style='margin-bottom:30px'>";
$els = array();
# Fields
$els[] = array(
  makeElement('kodegudang','label',$_SESSION['lang']['gudang']),
  makeElement('kodegudang','select','',array('style'=>'width:250px'),$optGudang)
);
$els[] = array(
  makeElement('kodebarang','label',$_SESSION['lang']['kodebarang']),
  makeElement('kodebarang','searchBarang','',array('style'=>'width:250px'))
);
$els[] = array(
  makeElement('nokartubin','label',$_SESSION['lang']['nokartubin']),
  makeElement('nokartubin','text','',array('style'=>'width:70px','maxlength'=>'45'))
);
$els[] = array(
  makeElement('maxstok','label',$_SESSION['lang']['maxstok']),
  makeElement('maxstok','textnum','',array('style'=>'width:100px'))
);
$els[] = array(
  makeElement('minstok','label',$_SESSION['lang']['minstok']),
  makeElement('minstok','textnum','',array('style'=>'width:100px'))
);

# Fields
$fieldStr = '##kodegudang##kodebarang##nokartubin##maxstok##minstok'; // Field yang akan diambil
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

$fieldId = '##kodegudang##kodebarang'; // Field yang menjadi ID (tidak boleh diubah)

# Button
$btnConfig = array(
	'disabled' => '##kodebarang', // Selalu disabled
	'opt' => json_encode($opt), // Untuk masking, misal tampilan kode ingin diubah jadi nama
	'barang' => array('kodebarang') // List field yang type = 'searchBarang'
);
$els['btn'] = array(
	genFormBtnConfig($fieldStr,'log_5kartubin',$fieldId,$btnConfig)
);

# Generate Field
echo genElTitle($_SESSION['lang']['nokartubin'],$els);
echo "</div>";
#=======End Form============

#=======Table============
# Display Table
echo "<div style='clear:both;float:left'>";
$configTable = array(
	'column' 		=> $fieldArr, 						// Kolom yang akan ditampilkan, jika tidak diassign kolom pada tabel tidak ada
	'freezeField' 	=> 'kodegudang##kodebarang', 		// Kolom yang freeze ketika edit
	'changeLang' 	=> array('kodegudang'=>'gudang'), 	// kayword lang yang diganti, contoh kodegudang diganti gudang
	'listName' 		=> $_SESSION['lang']['nokartubin'], // Custom String disebelah kanan 'List Data: '
	'opt'			=> $opt,							// Untuk Masking kode menjadi nama
	'cond'			=> $cond,							// Where Condition
);
echo masterTableConfig($dbname,'log_5kartubin',$configTable);
echo "</div>";
#=======End Table============

CLOSE_BOX();
echo close_body();
?>