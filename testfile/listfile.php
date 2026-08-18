<?php
$folder = "imgbot/";
$files = glob($folder.'*'); 
foreach($files as $file) {
	echo $file."<br>";
}

$folder = "imgbot/pdf/";
$files = glob($folder.'*'); 
foreach($files as $file) {
	echo $file."<br>";
}
?>