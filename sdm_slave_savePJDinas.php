<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$karyawanid=$_POST['karyawanid'];
$kodeorg=$_POST['kodeorg'];
$persetujuan=$_POST['persetujuan'];
$per['persetujuan1']=$_POST['persetujuan1'];
$per['persetujuan2']=$_POST['persetujuan2'];
$per['persetujuan3']=$_POST['persetujuan3'];
$jenispersetujuan='PJDINAS';

//author - atwal
//Param Array
$rutedari = $_POST['rutedari'];	
$rutetujuan = $_POST['rutetujuan'];	
$rutewaktu = $_POST['rutewaktu'];	
$rutetrans = $_POST['rutetrans'];	

$rencanatanggal = $_POST['rencanatanggal'];	
$rencanakegiatan = $_POST['rencanakegiatan'];	
// END:

$hrd=$_POST['hrd']; 
$tujuan3=$_POST['tujuan3'];
$unit=$_POST['unit'];
$tujuan2=$_POST['tujuan2'];	
$tujuan1=$_POST['tujuan1'];
$tanggalperjalanan=tanggalsystem($_POST['tanggalperjalanan']);
$tanggalkembali=tanggalsystem($_POST['tanggalkembali']);
$uangmuka=$_POST['uangmuka'];
$tugas1=checkPostGet($_POST['tugas1'],'');
$tugas2=$_POST['tugas2'];
$tugas3=$_POST['tugas3'];
$tujuanlain=$_POST['tujuanlain'];
$tugaslain=$_POST['tugaslain'];
$pesawat=$_POST['pesawat'];
$darat=$_POST['darat'];
$laut=$_POST['laut'];
$mess=$_POST['mess'];
$hotel=$_POST['hotel'];		
$method=$_POST['method'];
$jenis=$_POST['jenis'];

if($tugas1 == ""){
	$tugas1 = "-"; //karena Not NULL
}
if($tujuan1 == ""){
	$tujuan1 = "-";//karena Not NULL
}
//tambahan
// $persetujuan2=$_POST['persetujuan2'];
$kendaraandinas=$_POST['kendaraandinas'];
$kendaraanpribadi=$_POST['kendaraanpribadi'];
$kendaraanumum=$_POST['kendaraanumum'];
$tempatlain=$_POST['tempatlain'];

if($uangmuka=='')
  $uangmuka=0;

// if ($per['persetujuan3']=='') {
// 	exit('warning : Persetujuan HRD tidak boleh kosong.');
// }

if($method=='update' or $method=='insert'){

	//bagian karyawan
	$str="select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$tipekaryawan=$bar['tipekaryawan'];

	if($tipekaryawan!=7){
		if ($tanggalperjalanan<date('Ymd')) {
			exit('warning : Tanggal dinas tidak boleh lebih kecil dari tanggal hari ini.');
		}
	}
}


if($method=='insert')
{
//get number
$potSK=substr($_SESSION['empl']['lokasitugas'],0,4).date('Y');
$str="select notransaksi from ".$dbname.".sdm_pjdinasht
      where  notransaksi like '".$potSK."%'
	  order by notransaksi desc limit 1";
 
$notrx=0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$notrx=substr($bar->notransaksi,10,5);
}
$notrx=intval($notrx);
$notrx=$notrx+1;
$notrx=str_pad($notrx, 5, "0", STR_PAD_LEFT);
$notrx=$potSK.$notrx;
 
  $qstr[]="insert into ".$dbname.".sdm_pjdinasht (
		  `notransaksi`,`karyawanid`,`tanggalbuat`,
		  `tanggalperjalanan`,`kodeorg`,`tujuan1`,
		  `tugas1`,`tujuan2`,`tugas2`,`tujuan3`,unit,
		  `tugas3`,`tugaslain`,`tujuanlain`,
		  `pesawat`,`darat`,`laut`,
		  `mess`,`hotel`,`tanggalkembali`,`uangmuka`,
		  `hrd`,`persetujuan`,`jenis`,
		  `kendaraandinas`,`kendaraanpribadi`,`kendaraanumum`,`tempatlain`,`createdby`
		  ) values(
				'".$notrx."','".$karyawanid."','".date('Ymd')."',
				'".$tanggalperjalanan."','".$kodeorg."','".$tujuan1."',
				'".$tugas1."','".$tujuan2."','".$tugas2."','".$tujuan3."','".$unit."',
				'".$tugas3."','".$tugaslain."','".$tujuanlain."',
				'".$pesawat."','".$darat."','".$laut."',
				'".$mess."','".$hotel."','".$tanggalkembali."','".$uangmuka."',
				'".$hrd."','".$persetujuan."','".$jenis."',
				'".$kendaraandinas."','".$kendaraanpribadi."','".$kendaraanumum."','".$tempatlain."','".$_SESSION['standard']['userid']."'
		  )";

	for($i=0; $i<count($rencanatanggal); $i++){
	$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt2 (
				`notransaksi`,`tanggal`,`keterangan`
				) values (
					'".$notrx."','".tanggalsystem($rencanatanggal[$i])."','".$rencanakegiatan[$i]."'
				)";
	}

	for($i=0; $i<count($rutedari); $i++){
	$datetime = explode('_',$rutewaktu[$i]);
	$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt_rute (
				`notransaksi`,`waktu`,`tujuan`,`dari`,`transportasi`
				) values (
					'".$notrx."','".tanggalsystemn($datetime[0])." ".$datetime[1]."','".$rutetujuan[$i]."','".$rutedari[$i]."','".$rutetrans[$i]."'
				)";
	}
}
else if($method=='delete')
{
	$notransaksi=$_POST['notransaksi'];
	$qstr[]="delete from ".$dbname.".sdm_pjdinasht
	      where karyawanid='".$karyawanid."' and notransaksi='".$notransaksi."'"; 
		  
	$qstr[]="delete from ".$dbname.".sdm_pjdinasdt2
	      where notransaksi='".$notransaksi."'"; 	

	$qstr[]="delete from ".$dbname.".sdm_pjdinasdt_rute
	      where notransaksi='".$notransaksi."'"; 

	$qstr[]="delete from ".$dbname.".approval
	      where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenispersetujuan."'"; 

}
else if($method=='update')
{
  $notransaksi=$_POST['notransaksi'];
	$qstr[]="update ".$dbname.".sdm_pjdinasht set
		  `tanggalperjalanan`='".$tanggalperjalanan."',
		  `kodeorg`='".$kodeorg."',
		  `tujuan1`='".$tujuan1."',
		  `tugas1`='".$tugas1."',
		  `tujuan2`='".$tujuan2."',
		  `tugas2`='".$tugas2."',
		  `tujuan3`='".$tujuan3."',
		  `tugas3`='".$tugas3."',
		  `tugaslain`='".$tugaslain."',
		  `tujuanlain`='".$tujuanlain."',
		  `pesawat`='".$pesawat."',
		  `darat`='".$darat."',
		  `laut`='".$laut."',
		  `mess`='".$mess."',
		  `hotel`='".$hotel."',
		  `tanggalkembali`='".$tanggalkembali."',
		  `uangmuka`='".$uangmuka."',
		  `hrd`='".$hrd."',
		  `persetujuan`='".$persetujuan."',
		  `jenis`='".$jenis."',
			kendaraandinas='".$kendaraandinas."',
			kendaraanpribadi='".$kendaraanpribadi."',
			kendaraanumum='".$kendaraanumum."',
			tempatlain='".$tempatlain."'
		where karyawanid='".$karyawanid."' and notransaksi='".$notransaksi."'"; 	
	
	
	
	$qstr[]="delete from ".$dbname.".sdm_pjdinasdt2
	      where notransaksi='".$notransaksi."'"; 	

	$qstr[]="delete from ".$dbname.".sdm_pjdinasdt_rute
	      where notransaksi='".$notransaksi."'"; 
		  
	//exit("ERROR".count($rencanatanggal));	  
	for($i=0; $i<count($rencanatanggal); $i++){
	$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt2 (
				`notransaksi`,`tanggal`,`keterangan`
				) values (
					'".$notransaksi."','".tanggalsystem($rencanatanggal[$i])."','".$rencanakegiatan[$i]."'
				)";
	}

	for($i=0; $i<count($rutedari); $i++){
	$datetime = explode('_',$rutewaktu[$i]);
	$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt_rute (
				`notransaksi`,`waktu`,`tujuan`,`dari`,`transportasi`
				) values (
					'".$notransaksi."','".tanggalsystemn($datetime[0])." ".$datetime[1]."','".$rutetujuan[$i]."','".$rutedari[$i]."','".$rutetrans[$i]."'
				)";
	}

	for($i=1; $i<4; $i++){
		if($per['persetujuan'.$i]!=''){
			$qstr[]="update ".$dbname.".approval set karyawanid='".$per['persetujuan'.$i]."' where notransaksi='".$notransaksi."' 
			and jenispersetujuan='".$jenispersetujuan."' and level='".$i."'";
		}
	
	}
	
}else if ($method=='getpersetujuan'){
	//bagian karyawan
	$str="select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$tipekaryawan=$bar['tipekaryawan'];

	echo $tipekaryawan;
}

// echo "<pre>";
//  print_r($qstr);
//  echo "</pre>";
//  exit('warning');

try{
	for($i=0; $i<count($qstr); $i++){
		$owlPDO->exec($qstr[$i]); 
	}

	if ($method=='insert') {//
		for($i=1; $i<4; $i++){
			if($per['persetujuan'.$i]!=''){
				$str="insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`) values 
					  ('".$notrx."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."')";
				try{
		            $owlPDO->exec($str); 
		        }catch(PDOException $e){
		            echo " Gagal," . addslashes($e->getMessage());
		        }
			}
		}

		// if($tipekaryawan==7){
		// 	$str="insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`,`status`) values 
		// 		  ('".$notrx."','".$jenispersetujuan."','3','".$per['persetujuan3']."','1')";
		// 	try{
	 	//          $owlPDO->exec($str); 
	 	//      }catch(PDOException $e){
	 	//          echo " Gagal," . addslashes($e->getMessage());
	 	//      }
		// }
	}

	if ($method=='getunit') {
		
		$sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$tujuan2."' and tipe='KEBUN'";
		$qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
		$qKbn->setFetchMode(PDO::FETCH_ASSOC);
		while($rKbn=$qKbn->fetch())
		{
			$optKebun.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
		}
		
		$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($tujuan2!='')
		{
			$sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$tujuan2."' and tipe='KEBUN') and tipe='AFDELING'";
			$qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
			$qKbn->setFetchMode(PDO::FETCH_ASSOC);
			while($rKbn=$qKbn->fetch())
			{
				$optAfd.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
			}
		}
		
		echo $optKebun."##".$optAfd;
	}
	
	if($method=='update' or $method=='insert')
    {
        // $to=getUserEmail($hrd);
        // $namakaryawan=getNamaKaryawan($_SESSION['standard']['userid']);
        // $subject="[Notifikasi]Persetujuan Perjalanan Dinas a/n ".$namakaryawan;
        // $body="<html>
        //          <head>
        //          <body>
        //            <dd>Dengan Hormat,</dd><br>
        //            <br>
        //            Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan surat perjalanan dinas
        //            kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
        //            <br>
        //            <br>
        //            <br>
        //            Regards,<br>
        //            Owl-Plantation System.
        //          </body>
        //          </head>
        //        </html>
        //        ";
        // $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;
        // $to = getUserEmail($persetujuan);
        // $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;
		
		if($method=='update'){
			echo $notransaksi;
		}
		if($method=='insert'){
			echo $notrx;
		}
		
    }
}catch (PDOException $e){
	echo " Gagal:".addslashes($e->getMessage());
	die();
}
?>