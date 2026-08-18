<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$code=$_POST['code'];
$sta=$owlPDO->query("select * from ".$dbname.".organisasi where kodeorganisasi='".$code."'");
$sta->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($sta);
if($numrows>0){
   while($be=$sta->fetch())
   {
	 echo $be->kodeorganisasi."|".$be->namaorganisasi."|".$be->tipe."|".$be->alamat."|".$be->telepon."|".$be->wilayahkota."|".$be->kodepos."|".$be->negara."|".$be->alokasi."|".$be->noakun."|".$be->identnik."|".$be->inisialisasiorganisasi."|".$be->tipepabrik."|".$be->sustainable."|".$be->sertifikat."|".$be->indukblok."|".$be->namaindukblok; 	
   }
 } else{
	echo "-1";
} 
?>
