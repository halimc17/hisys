<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once ('lib/nangkoelib.php');
require_once ('lib/zLib.php');
$notransaksi=$_POST['notransaksi'];
$karyawanid=$_POST['karyawanid'];
$status=$_POST['status'];
$kolom=$_POST['kolom'];
$tanggal=date('Ymd');

$arrstatus=array("1"=>"disetujui","2"=>"ditolak");
$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$kolomstatus='status'.$kolom;
$kolomtanggal='tanggal'.$kolom;

/*
$dtl="select frekuensi,jumlah from ".$dbname.".sdm_pjdinasdt 
		where notransaksi='".$notransaksi."' and sumber='0'";

$resdtl=$owlPDO->query($dtl) or die(print " Gagal: ".PDOException::getMessage());
$resdtl->setFetchMode(PDO::FETCH_OBJ);
*/
/*
$uangmuka = 0;
while($r=$resdtl->fetch()){
	$uangmuka += $r->frekuensi * $r->jumlah;
}
  
$str[]="update ".$dbname.".sdm_pjdinasdt set jumlahhrd = jumlah  where notransaksi='".$notransaksi."' and sumber='0'";	 
*/

$str="update ".$dbname.".sdm_pjdinasht set ".$kolomstatus."=".$status.", 
      ".$kolomtanggal."=".$tanggal." where notransaksi='".$notransaksi."'";	 
try{
	// for($i=0; $i<count($str); $i++){
		$owlPDO->exec($str); 
	// }
	//ambil email notifikasi ke GA
    // $str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='X2' limit 1";
	// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// $res->setFetchMode(PDO::FETCH_OBJ);
    // while($bar=$res->fetch())
    // {
     // $to=$bar->nilai;
    // }
    
	
	
	
		$str="select a.hrd,b.email,a.tanggalperjalanan,a.kodeorg,a.tujuan1,a.tugas1,b.namakaryawan,b.bagian from ".$dbname.".sdm_pjdinasht a
              left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
              where a.notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$bar=$res->fetch();
            $nama=$bar->namakaryawan;
            $tanggal=tanggalnormal($bar->tanggalperjalanan);
            $tujuan=$bar->tujuan1;
            $bagian=$bar->bagian;
            $tugas=$bar->tugas1;
			$to=$bar->email;
			$hrd=$bar->hrd;
      if ($hrd==$_SESSION['standard']['userid']){
        $subject="[Notifikasi] Perjalanan Dinas";
        $body="<html>
                 <head>
                 <body>
                   <dd>Dengan Hormat,</dd><br>
                   <br>
                   Telah ".$arrstatus[$status]." oleh ".$nmkar[$hrd]." perjalanan dinas  A/n:".$nama." (".$bagian.")<br>
                   Tujuan:".$tujuan."<br>
                   Tugas :".$tugas."<br>
                   Tanggal:".$tanggal."
                   <br>
                   <br>
                   <br>
                   Regards,<br>
                   Owl-Plantation System.
                 </body>
                 </head>
               </html>
               ";
        $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;  
        #kirim email balik
      }
    
}catch (PDOException $e){
	echo addslashes($e->getMessage());
	die();
}
?>
