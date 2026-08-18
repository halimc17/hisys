<?
require_once('master_validation.php');
require_once('config/connection.php');

$str="delete from ".$dbname.".rencana_gis_file where namafile='".$_POST['namafile']."' and karyawanid=".$_SESSION['standard']['userid'];
try{$owlPDO->exec($str); 

echo"";
    unlink('filegis/'.$_POST['namafile']);

}
catch (PDOException $e) {
    
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}

?>