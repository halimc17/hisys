<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$method	= "";
$id	= "";
$jumlahpotongan=0; 
$listkoperasi=''; 
$jenispotongan=''; 
$tahunpotongan=''; 			
$karyawanid='';
$fileupload='';
$rename	=	'';

if(isset($_GET['method'])){
	$method = $_GET['method'];
}
if(isset($_POST['id'])){
	$id = $_POST['id'];
}
if(isset($_POST['jumlahpotongan'])){
	$jumlahpotongan = $_POST['jumlahpotongan'];
}
if(isset($_POST['listkoperasi'])){
	$listkoperasi = $_POST['listkoperasi'];
}
if(isset($_POST['jenispotongan'])){
	$jenispotongan = $_POST['jenispotongan'];
}
if(isset($_POST['tahunpotongan'])){
	$tahunpotongan = $_POST['tahunpotongan'];
}
if(isset($_POST['karyawanid'])){
	$karyawanid = $_POST['karyawanid'];
}
if(isset($_POST['fileupload'])){
	$fileupload = $_POST['fileupload'];
}
function upload_file($pathlocation,$data,$rename){
	$result = "";
	$ext ="";
	if($data != ""){
		$fileTrans	=$data;
		if($fileTrans != ""){
			$path = $pathlocation;
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
			$newFile=$fileTrans;
			$file =explode(',',$newFile);
			//$file =preg_replace('#^data:application/\w+;base64,#i', '', $newFile);
			
			$file =str_replace(' ', '+', $file[1]);
			$stream = base64_decode($file);
			$f = finfo_open();
			$mime_type = finfo_buffer($f, $stream, FILEINFO_MIME_TYPE);

			if ($mime_type=="image/jpeg" ){
				$ext = ".jpg";
			}elseif ($mime_type=="image/png" ){
				$ext = ".png";
			}elseif ($mime_type=="application/pdf" ){
				$ext = ".pdf";
			}
			$filename= $path.$rename.$ext;	
			file_put_contents($filename, $stream);			
		}
		$result = $rename.$ext;
	}
	return $result;
}
$str = array();
switch($method){
	case 'insert':
		$path = "./fileupload/karyawan/".$karyawanid."/";
		$rename = "potongan_".$listkoperasi;
		$namefile = upload_file($path,$fileupload,$rename);
		$find = selectQuery($dbname,"sdm_karyawankeanggotaan","karyawanid","karyawanid=".$karyawanid." and supplierid ='".$listkoperasi."'");
		$sdm_karyawankeanggotaan = fetchData($find);
		if(count($sdm_karyawankeanggotaan) == 0){ 
			$str[] ="insert into ".$dbname.".sdm_karyawankeanggotaan
			 (`karyawanid`,
			  `jenispotongan`,
			  `supplierid`,
			  `suratpernyataan`
			  )
			  values(".$karyawanid.",
			  '".$jenispotongan."',
			  '".$listkoperasi."',
			  '".$namefile."'
			  )";
		}else{
			$str[] ="update ".$dbname.".sdm_karyawankeanggotaan set
			 `jenispotongan` ='$jenispotongan',
			 `suratpernyataan` ='$namefile'
			 where karyawanid=".$karyawanid." and supplierid ='".$listkoperasi."'";
		}
		$find = selectQuery($dbname,"sdm_5gajipokok","karyawanid","karyawanid=".$karyawanid." and tahun ='".$tahunpotongan."' and idkomponen ='".$jenispotongan."'");
		$sdm_5gajipokok = fetchData($find);
		if(count($sdm_5gajipokok) == 0){ 
			$str[] ="insert into ".$dbname.".sdm_5gajipokok
			 (`tahun`,
			  `karyawanid`,
			  `idkomponen`,
			  `jumlah`
			  )
			  values(".$tahunpotongan.",
			  '".$karyawanid."',
			  '".$jenispotongan."',
			  '".$jumlahpotongan."'
			)";	
		}else{
			$str[] ="update ".$dbname.".sdm_5gajipokok set
			 `jumlah` ='$jumlahpotongan' where karyawanid=".$karyawanid." and tahun ='".$tahunpotongan."' and idkomponen ='".$jenispotongan."'";	
		}

	break;
	
	//DELETE
	case 'delete':
		$str[] ="delete from ".$dbname.".sdm_karyawankeanggotaan where id='".$id."'";
	break;
}

if(count($str)>0){
	for($i=0; $i<count($str); $i++){
		try{$owlPDO->exec($str[$i]); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";}
	}
}
//View*
 $str="select sdm_karyawankeanggotaan.*,sdm_5gajipokok.tahun,sdm_5gajipokok.jumlah,
 log_5supplier.namasupplier, log_5supplier.badanusaha,
 keu_5komponenbiaya.keteranganbiaya
 from ".$dbname.".sdm_karyawankeanggotaan 
 left join sdm_5gajipokok on sdm_karyawankeanggotaan.karyawanid = sdm_5gajipokok.karyawanid
 and sdm_karyawankeanggotaan.jenispotongan = sdm_5gajipokok.idkomponen
 left join log_5supplier on
 sdm_karyawankeanggotaan.supplierid = log_5supplier.supplierid
 left join keu_5komponenbiaya on
 sdm_karyawankeanggotaan.jenispotongan = keu_5komponenbiaya.kodebiaya
 where sdm_karyawankeanggotaan.karyawanid=".$karyawanid." and sdm_5gajipokok.tahun = '".date('Y')."' order by jenispotongan";

	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
 while($bar=$res->fetch())
 {
	$no+=1;	
	echo"<tr class=rowcontent>
	<td class=firsttd>".$no."</td>
	<td>".$bar->namasupplier.", ".$bar->badanusaha."</td>
	<td>".$bar->keteranganbiaya."</td>			  
	<td>".$bar->tahun."</td>			  
	<td>".$bar->jumlah."</td>
	<td><a href='fileupload/karyawan/".$karyawanid."/".$bar->suratpernyataan."' download><img src='images/download-file.png' width='10'>&nbsp;&nbsp;".$bar->suratpernyataan."</a></td>	
	<td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delKeanggotaan('".$bar->id."');\"></td>
	</tr>";	 	
	}
?>