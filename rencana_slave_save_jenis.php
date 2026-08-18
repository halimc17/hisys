<?
require_once('master_validation.php');
require_once('config/connection.php');

$kode=$_POST['kode'];
$nama=$_POST['nama'];
$method=$_POST['method'];

switch($method)
{
case 'update':	
	$str="update ".$dbname.".rencana_gis_jenis set namajenis='".$nama."'
	       where kode='".$kode."'";
    try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}

	break;
case 'insert':
	$str="insert into ".$dbname.".rencana_gis_jenis (kode,namajenis)
	      values('".$kode."','".$nama."')";
    try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}

	break;
case 'delete':
	$str="delete from ".$dbname.".rencana_gis_jenis 
	where kode='".$kode."'";
    try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}

	break;
default:
   break;					
}
$str1="select * from ".$dbname.".rencana_gis_jenis order by kode";

$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res1->fetch())
{
		echo"<tr class=rowcontent><td align=center>".$bar1->kode."</td><td>".$bar1->namajenis."</td><td><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->namajenis."');\"></td></tr>";
}	 
?>