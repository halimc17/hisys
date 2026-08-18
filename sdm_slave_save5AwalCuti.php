<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$karyawanid=$_POST['karyawanid'];
$lokasitugas=$_POST['lokasitugas'];
$periode=$_POST['periode'];
$hak=0+$_POST['hak'];
$keterangan=$_POST['keterangan'];
$cekdata=$_POST['cekdata'];

$hakcuti_ht=0;
$cutitambahan_ht=0;
$adjs_hakcuti_ht=0;
$diambil_ht=0;
$sisa = 0;

try {
    $owlPDO->beginTransaction();

$sCek="select * from ".$dbname.".sdm_cutiht where kodeorg= '".$lokasitugas."' and karyawanid='".$karyawanid."' and periodecuti='".$periode."'";
$rCek=fetchData($sCek);
if($cekdata==1){
	if(count($rCek) != 0){
		$hakcuti_ht = $rCek[0]['hakcuti'];
		$cutitambahan_ht = $rCek[0]['cutitambahan'];
		$adjs_hakcuti_ht = $rCek[0]['adjs_hakcuti'] + $hak;
		$diambil_ht = $rCek[0]['diambil'];

		$sisa = ($hakcuti_ht + $cutitambahan_ht + $adjs_hakcuti_ht) - $diambil_ht  ;
		$str="update ".$dbname.".sdm_cutiht 
			    set adjs_hakcuti ='".$adjs_hakcuti_ht."',
				sisa='".$sisa."'
				where kodeorg='".$lokasitugas."' and karyawanid='".$karyawanid."' and periodecuti='".$periode."'";  
		$owlPDO->exec($str); 
		
	}else{
		$str1 = "insert into ".$dbname.".sdm_cutiht (kodeorg,karyawanid,periodecuti,adjs_hakcuti,sisa) values ('".$lokasitugas."','".$karyawanid."','".$periode."','".$hak."','".$hak."')";
		$owlPDO->exec($str1);
	}	
}

	
$cekAdj="select * from ".$dbname.".sdm_5cutiadjsment where kodeorg= '".$lokasitugas."' and karyawanid='".$karyawanid."' and periodecuti='".$periode."'";
$rCekAdj=fetchData($cekAdj);
if($cekdata==1){
	$str1 = "insert into ".$dbname.".sdm_5cutiadjsment (kodeorg,karyawanid,periodecuti,adjs_hakcuti,keterangan,createdby,updateby) values ('".$lokasitugas."','".$karyawanid."','".$periode."','".$hak."','".$keterangan."','" . $_SESSION['standard']['userid'] . "','" . $_SESSION['standard']['userid'] . "')";
	$owlPDO->exec($str1);
}

$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
}

?>