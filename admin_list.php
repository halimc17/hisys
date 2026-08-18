<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zSearch.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
// Options
$optUser = makeOption($dbname,'user','namauser,namauser');
$optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

// Options for passing
$opt = array('username' => $optUser,'karyawanid'=> $optKary);

#=======Form============
echo "<div style='margin-bottom:30px'>";
$els = array();
# Fields
$els[] = array(
  makeElement('username','label',$_SESSION['lang']['user']),
  makeElement('username','select','',array('style'=>'width:250px'),$optUser)
);
$els[] = array(
  makeElement('karyawanid','label',$_SESSION['lang']['namakaryawan']),
  makeElement('karyawanid','select','',array('style'=>'width:250px'),$optKary)
);

# Fields
$fieldStr = '##username##karyawanid'; // Field yang akan diambil
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

$fieldId = '##username##karyawanid'; // Field yang menjadi ID (tidak boleh diubah)

# Button
$btnConfig = array(
	'opt' => json_encode($opt), // Untuk masking, misal tampilan kode ingin diubah jadi nama
	'barang' => array('kodebarang') // List field yang type = 'searchBarang'
);
$els['btn'] = array(
	genFormBtnConfig($fieldStr,'admin_list',$fieldId,$btnConfig)
);

# Generate Field
echo genElTitle('<span class=judul>'.strtoupper('Admin List').'</span>',$els);
echo "</div>";
#=======End Form============

#=======Table============
# Display Table
echo "<div style='clear:both;float:left'>";
$configTable = array(
	'column' 		=> $fieldArr, 						// Kolom yang akan ditampilkan, jika tidak diassign kolom pada tabel tidak ada
	//'freezeField' 	=> 'kodegudang##kodebarang', 		// Kolom yang freeze ketika edit
	'changeLang' 	=> array('username'=>'user'), 	// kayword lang yang diganti, contoh kodegudang diganti gudang
	'listName' 		=> "Admin List", // Custom String disebelah kanan 'List Data: '
	'opt'			=> $opt,							// Untuk Masking kode menjadi nama
);
echo masterTableConfig($dbname,'admin_list',$configTable);
echo "</div>";
#=======End Table============

CLOSE_BOX();
echo close_body();
?>