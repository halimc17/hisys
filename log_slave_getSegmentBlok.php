<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
//=============================================

if(isTransactionPeriod())//check if transaction period is normal
{
    $blok=$_POST['kodeblok'];
	$defaultSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
	$query = "select distinct a.kodesegment,a.namasegment from ".$dbname.".keu_5segment a
		left join ".$dbname.".keu_5proporsisegment b on a.kodesegment=b.kodesegment
		where b.kodeblok='".$blok."' or a.kodesegment = '".$defaultSegment."'";
	$res = fetchData($query);
	$opt = "";
	foreach($res as $row) {
		$opt .= "<option value='".$row['kodesegment']."'>".$row['namasegment'].
			"</option>";
	}
	echo $opt;
} else {
	echo " Error: Transaction Period missing";
}