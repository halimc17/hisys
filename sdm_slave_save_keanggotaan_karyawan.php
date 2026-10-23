<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/sdm_karyawan_helper.php');
require_once('lib/zLib.php');
$method	= "";
$id	= 0;
$jumlahpotongan=0;
$listkoperasi='';
$jenispotongan='';
$tahunpotongan='';
$karyawanid=0;
$fileupload='';
$rename	=	'';

if(isset($_GET['method'])){
	$method = $_GET['method'];
}
if(isset($_POST['id'])){
	$id = sdmId($_POST['id']);
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
	$karyawanid = sdmId($_POST['karyawanid']);
}
if(isset($_POST['fileupload'])){
	$fileupload = $_POST['fileupload'];
}
# simpan berkas surat pernyataan; hanya jpg, png, pdf dengan nama dari nilai yang sudah divalidasi. Hasil false bila berkas ditolak
function upload_file($pathlocation,$data,$rename){
	if($data == ""){
		return "";
	}
	$file = explode(',',$data);
	if(count($file) < 2){
		return false;
	}
	$stream = base64_decode(str_replace(' ', '+', $file[1]), true);
	if($stream === false || $stream === ""){
		return false;
	}
	$f = finfo_open();
	$mime_type = finfo_buffer($f, $stream, FILEINFO_MIME_TYPE);
	$allowed = array("image/jpeg" => ".jpg", "image/png" => ".png", "application/pdf" => ".pdf");
	if(!isset($allowed[$mime_type])){
		return false;
	}
	if (!file_exists($pathlocation)) {
		mkdir($pathlocation, 0755, true);
	}
	$filename = $rename.$allowed[$mime_type];
	file_put_contents($pathlocation.$filename, $stream);
	return $filename;
}
$str = array();
$galat = '';
switch($method){
	case 'insert':
		if($karyawanid == 0 || !preg_match('/^[A-Za-z0-9_-]{1,10}$/', $listkoperasi) || !preg_match('/^[A-Za-z0-9]{1,3}$/', $jenispotongan) || !preg_match('/^[0-9]{4}$/', $tahunpotongan) || !is_numeric($jumlahpotongan)){
			$galat = " Gagal  !: Data tidak lengkap atau tidak valid.\n";
			break;
		}
		$path = "./fileupload/karyawan/".$karyawanid."/";
		$rename = "potongan_".$listkoperasi;
		$namefile = upload_file($path,$fileupload,$rename);
		if($namefile === false){
			$galat = " Gagal  !: Berkas harus berupa JPG, PNG, atau PDF yang valid.\n";
			break;
		}
		$find = $owlPDO->prepare("select karyawanid from ".$dbname.".sdm_karyawankeanggotaan where karyawanid=? and supplierid=?");
		$find->execute(array($karyawanid,$listkoperasi));
		if(count($find->fetchAll()) == 0){
			$str[] = array("insert into ".$dbname.".sdm_karyawankeanggotaan (`karyawanid`,`jenispotongan`,`supplierid`,`suratpernyataan`) values(?,?,?,?)",
				array($karyawanid,$jenispotongan,$listkoperasi,$namefile));
		}else{
			$str[] = array("update ".$dbname.".sdm_karyawankeanggotaan set `jenispotongan`=?,`suratpernyataan`=? where karyawanid=? and supplierid=?",
				array($jenispotongan,$namefile,$karyawanid,$listkoperasi));
		}
		# jumlah potongan tidak lagi ditulis otomatis ke sdm_5gajipokok (dinonaktifkan)
		/*
		$find = $owlPDO->prepare("select karyawanid from ".$dbname.".sdm_5gajipokok where karyawanid=? and tahun=? and idkomponen=?");
		$find->execute(array($karyawanid,$tahunpotongan,$jenispotongan));
		if(count($find->fetchAll()) == 0){
			$str[] = array("insert into ".$dbname.".sdm_5gajipokok (`tahun`,`karyawanid`,`idkomponen`,`jumlah`) values(?,?,?,?)",
				array($tahunpotongan,$karyawanid,$jenispotongan,$jumlahpotongan));
		}else{
			$str[] = array("update ".$dbname.".sdm_5gajipokok set `jumlah`=? where karyawanid=? and tahun=? and idkomponen=?",
				array($jumlahpotongan,$karyawanid,$tahunpotongan,$jenispotongan));
		}
		*/
	break;

	//DELETE
	case 'delete':
		$str[] = array("delete from ".$dbname.".sdm_karyawankeanggotaan where id=? and karyawanid=?", array($id,$karyawanid));
	break;
}

for($i=0; $i<count($str); $i++){
	try{
		$st = $owlPDO->prepare($str[$i][0]);
		$st->execute($str[$i][1]);
	}catch (PDOException $e) {
		$galat = sdmGalat($e);
	}
}
if($galat != ''){
	print $galat;
}
//View*
$st = $owlPDO->prepare("select sdm_karyawankeanggotaan.*,sdm_5gajipokok.tahun,sdm_5gajipokok.jumlah,
 log_5supplier.namasupplier, log_5supplier.badanusaha,
 keu_5komponenbiaya.keteranganbiaya
 from ".$dbname.".sdm_karyawankeanggotaan
 left join ".$dbname.".sdm_5gajipokok on sdm_karyawankeanggotaan.karyawanid = sdm_5gajipokok.karyawanid
 and sdm_karyawankeanggotaan.jenispotongan = sdm_5gajipokok.idkomponen
 left join ".$dbname.".log_5supplier on
 sdm_karyawankeanggotaan.supplierid = log_5supplier.supplierid
 left join ".$dbname.".keu_5komponenbiaya on
 sdm_karyawankeanggotaan.jenispotongan = keu_5komponenbiaya.kodebiaya
 where sdm_karyawankeanggotaan.karyawanid=? and (sdm_5gajipokok.tahun = ? or sdm_5gajipokok.tahun is null) order by jenispotongan");
$st->execute(array($karyawanid,date('Y')));
$st->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($bar=$st->fetch())
{
	$no+=1;
	echo"<tr class=rowcontent>
	<td class=firsttd>".$no."</td>
	<td>".sdmH($bar->namasupplier).", ".sdmH($bar->badanusaha)."</td>
	<td>".sdmH($bar->keteranganbiaya)."</td>
	<td>".sdmH($bar->tahun)."</td>
	<td align=right>".sdmUang($bar->jumlah)."</td>
	<td><a href='fileupload/karyawan/".$karyawanid."/".rawurlencode($bar->suratpernyataan)."' download><img src='images/download-file.png' width='10'>&nbsp;&nbsp;".sdmH($bar->suratpernyataan)."</a></td>
	<td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delKeanggotaan('".sdmJs($bar->id)."');\"></td>
	</tr>";
	}
?>
