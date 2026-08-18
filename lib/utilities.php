<?php 
//Declare Global Variables
$stream = "";
$where  = "";
$user   = $_SESSION['standard']['userid'];
$work   = $_SESSION['empl']['lokasitugas'];

//Table
$border 	 = 0;
$cellpadding = 3;

//Global Dinamyc Array
$data 	= array();
$temp 	= array();
$cols 	= array();

//Utilities - Stuff
$utilities['stuff'] = array(
	"Name" => makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang'),
	"Unit" => makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan')
);

//Utilities - Organization
$utilities['organization'] = array(
	"Name" => makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi'),
	"Parent" => makeOption($dbname, 'organisasi', 'kodeorganisasi,induk')
);

//Utilities - Worker
$utilities['worker'] = array(
	"Name" => makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan'),
	"Nik" => makeOption($dbname, 'datakaryawan', 'karyawanid,nik')
);

//Utilities - Supplier
$utilities['supplier'] = array(
	"Name" => makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier')
);

//Utilities - Others
$utilities['others'] = array(
	"Tangki" => makeOption($dbname, 'pabrik_5tangki', 'kodetangki,keterangan')
);

$limit 	 	= 10;
$page 	 	= 0;
$offset  	= $page * $limit;
$date    	= date("Y-m-d");
$time    	= date("H:i:s");
$datetime  	= date("Y-m-d H:i:s");

$arrBulan = array("01" => "JANUARI", "02" => "PEBRUARI", "03" => "MARET", "04" => "APRIL", "05" => "MEI"
    , "06" => "JUNI", "07" => "JULI", "08" => "AGUSTUS", "09" => "SEPTEMBER", "10" => "OKTOBER"
    , "11" => "NOVEMBER", "12" => "DESEMBER");

function createNoTrans($kodeorg, $key, $table, $zero = 4){
	global $dbname;

	$notransaksi = date('Ymd')."/".$kodeorg."/".$key."/";
	$counter     = 1;

	$query  = "SELECT COUNT(notransaksi) AS notransaksi FROM $dbname.$table WHERE notransaksi LIKE '".$notransaksi."%' ORDER BY notransaksi DESC LIMIT 1";
	$result = fetchData($query, 'OBJECT');
	if (count($result) < 1) {
		return $notransaksi .= (string) addZero($counter, $zero);
	}

	return $notransaksi .= addZero($counter + $result[0]->notransaksi, $zero);
}

// Button
/**
* 1. Ajukan <img src='images/skyblue/submit.jpg' class='zImgBtn' title='Ajukan' onclick='form_ajukan()'>
* 2. Edit <img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick='updateData()'>
* 3. Posted <img src='images/skyblue/posted.png' class='zImgBtn' title='Posted'>
* 4. Delete <img src='images/skyblue/delete.png' class='zImgBtn' title='Delete' onclick='deleteData()'>
* 5. Detail <img src='images/skyblue/detail.png' class='zImgBtn' title='Detail' onclick='detailData()'>
* 6. Insert <img src='images/skyblue/plus.png' class='zImgBtn' title='Insert' onclick='insertData()'>
* 7. On Progress Approval <img style='margin:2px' src='images/skyblue/progress1.gif' class='zImgBtn' title='On Progress Approval'>
*/

//Input
/**
 * 1. Number Only <input id='' type='text' class='myinputtextnumber' value='0' size='15' maxlength='15' onkeypress=\"return angka_doang(event);\" onblur='change_number(this)'>
 */
?>

