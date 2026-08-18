<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get POST
$keyword = 	isset($_POST['keyword'])? $_POST['keyword']: '';
$target = 	isset($_POST['target'])? $_POST['target']: '';
$tipeBud = 	isset($_POST['tipeanggaran'])? $_POST['tipeanggaran']: '';

# Get Data



$where = "(namabarang like '%".$keyword."%' or kodebarang like '%".$keyword."%') and (kelompokbarang='311' or kelompokbarang='312' or kelompokbarang='313' or kelompokbarang='351' or kelompokbarang='386' or kelompokbarang='373' or kelompokbarang='347') ";
/*$query = "SELECT a.kodebarang,a.namabarang,a.satuan,IF(ISNULL(b.hargalastin),0,b.hargalastin) as harga ";
$query .= "FROM ".$dbname.".`log_5masterbarang` a ";
$query .= "LEFT JOIN (".$dbname.".log_5masterbarangdt b) ";
$query .= "ON a.kodebarang=b.kodebarang ";
$query .= "WHERE ".$where;*/
$query="select kodebarang,namabarang,satuan from ".$dbname.".log_5masterbarang where ".$where;
if($tipeBud=='SDM') {
    $query .= " and substr(a.kodebarang,1,3)='909'";
}
$data = fetchData($query);

# Make Table
//$headers = array('Kode','Nama','Satuan','Harga');
$headers = array('Kode','Nama','Satuan');
$table = "<table border=0 cellpadding=1 cellspacing=1 class=sorttable>";
$table .= "<thead><tr class='rowheader'>";
foreach($headers as $head) {
    $table .= "<td>".$head."</td>";
}
$table .= "</tr></thead>";
$table .= "<tbody>";
foreach($data as $key=>$row) {
    $table .= "<tr id='inv_tr_".$key."' style=cursor:pointer class='rowcontent' ";
    $table .= "onclick=\"passValue('".$row['kodebarang']."','".$row['namabarang']."','".$row['satuan']."');\">";
    #$table .= "passValue('".$row['harga']."','hargasatuan');\">";
    foreach($row as $head=>$con) {
	$table .= "<td id='".$head."_".$key."'>".$con."</td>";
    }
    $table .= "</tr>";
}
$table .= "</tbody>";
$table .= "<tfoot></tfoot></table>";

echo $table;
?>