<?php //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;

// Options
$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
					  "induk='".$param['afdeling']."'");

// Masking Segment
$qData = selectQuery($dbname,'keu_5proporsisegment','kodesegment',
					 "kodeblok like '".$param['afdeling']."%'");
$resData = fetchData($qData);
$strWhere = "";
foreach($resData as $row) {
	if(!empty($strWhere)) {
		$strWhere .= ",";
	}
	$strWhere .= "'".$row['kodesegment']."'";
}
if(!empty($strWhere)) {
	$whereSegment = "kodesegment in (".$strWhere.")";
	$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',$whereSegment,'1');
} else {
	$optSegment = array();
}

// Options for passing
$opt = array('kodesegment' => $optSegment);

#=======Form============
$els = array();
# Fields
$els[] = array(
	makeElement('kodeblok','label',$_SESSION['lang']['kodeblok']),
	makeElement('kodeblok','select','',array('style'=>'width:200px'),$optBlok)
);
$els[] = array(
	makeElement('kodesegment','label',$_SESSION['lang']['kodesegment']),
	makeElement('kodesegment','searchSegment','',array('style'=>'width:200px'))
);
$els[] = array(
	makeElement('porsisegment','label',$_SESSION['lang']['porsisegment']),
	makeElement('porsisegment','textnum','',array('style'=>'width:200px'))
);

# Fields
$fieldStr = '##kodeblok##kodesegment##porsisegment'; // Field yang akan diambil
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

$fieldId = '##kodeblok##kodesegment'; // Field yang menjadi ID (tidak boleh diubah)

# Button
$btnConfig = array(
	'disabled' => '##kodesegment', // Selalu disabled
	'opt' => json_encode($opt), // Untuk masking, misal tampilan kode ingin diubah jadi nama
	//'barang' => array('kodebarang') // List field yang type = 'searchBarang'
);
$els['btn'] = array(
	genFormBtnConfig($fieldStr,'keu_5proporsisegment',$fieldId,$btnConfig)
);

# Generate Field
echo genElTitle('Form',$els);
#=======End Form============

#=======Table============
# Display Table
echo "<div style='clear:both;float:left'>";
$configTable = array(
	'column' 		=> $fieldArr, 					// Kolom yang akan ditampilkan, jika tidak diassign kolom pada tabel tidak ada
	'freezeField' 	=> 'kodeblok##kodesegment', 	// Kolom yang freeze ketika edit
	'listName' 		=> $_SESSION['lang']['segment'],	// Custom String disebelah kanan 'List Data: '
	'opt'			=> $opt,						// Untuk Masking kode menjadi nama
);
echo masterTableConfig($dbname,'keu_5proporsisegment',$configTable);
echo "</div>";
#=======End Table============