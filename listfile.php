<?php
// $files = glob('imgbot/*.pdf'); 
// foreach($files as $file) {
	// if(is_file($file)){
		// unlink($file);
	// } 
// }

// $folder = "imgbot/";
// $files = glob($folder.'*'); 
// foreach($files as $file) {
	// echo $file."<br>";
	// // echo number_format((filesize($file)/1024))."<br>";
// }

// $folder = "fileupload/keu_tagihan/";
// $files = glob($folder.'*'); 
// foreach($files as $file) {
	// echo $file." = ";
	// echo number_format((filesize($file)/1024))."<br>";
// }

$folder = "fileupload/bkm/";
$files = glob($folder.'*'); 
foreach($files as $file){
	$dt[$file]=$file;
}

require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$str = "SELECT * FROM " . $dbname . ".listfileupload where kriteriaefil='BKM'";
$res = fetchData($str);
foreach($res as $bar){
	$data[$folder.$bar['namafile']]=$folder.$bar['namafile'];
	$notr[$folder.$bar['namafile']]=$bar['notransaksi'];
}

foreach($data as $fl){
	if($dt[$fl]==''){
		$no++;
		echo $no.". ".$notr[$fl]." - ".$fl."<br>";
	}
}


?>