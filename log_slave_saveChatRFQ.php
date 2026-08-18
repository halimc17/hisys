<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$karyawanid = $_SESSION['standard']['userid'];
$nomor = checkPostGet('nomor',''); 
$pesan = checkPostGet('pesan','');
$nmKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan'); 
// $nmpek = makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
   if ($pesan!="") {
   	# code...
	$str= "insert into ".$dbname.".log_rfq_chat (`nomor`,`karyawanid`,
          `pesan`)
		  values('".$nomor."',".$karyawanid.",'".$pesan."')"; 	
	try{
		$owlPDO->exec($str);

		##getpurchaser
		$str="select purchaser from ".$dbname.".log_perintaanhargaht where nomor='".$nomor."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$idkar[$bar['purchaser']]=$bar['purchaser'];
		}

		##getverificator
		$str="select verificator from ".$dbname.".log_permintaanhargadt where nomor='".$nomor."' and verificator!='0000000000' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$idkar[$bar['verificator']]=$bar['verificator'];
		}

		// ##getaproval
		// $str="select karyawanid from ".$dbname.".approval where notransaksi='".$nopo."'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$idkar[$bar['karyawanid']]=$bar['karyawanid'];
		// }

		// ##getkaryawan procurment
		// $str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where  bagian like '%PRO%' and tanggalkeluar='0000-00-00'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$idkar[$bar['karyawanid']]=$bar['karyawanid'];
		// }
			
		foreach ($idkar as $key => $val) {
			## CREATE NOTIFICATION
			$msgdt = "Ada pesan Chat terkait Riwayat Perbandingan Harga dengan No ".$nomor." dari ".$_SESSION['empl']['name'];
			createnotif($nomor,'CHAT',$msgdt,$val,date('Y-m-d H:i:s'));
		}
		
	}catch(PDOException $e){
		print " Gagal  !: " . $e->getMessage() . "\n"; 
		die(); 
	}
   }

echo "<table class=sortable cellspacing=1 border=0 width=100%>
	<tr>
		<td>From</td>
		<td>Time</td>
		<td>Messages</td>
	</tr>";


$str="select a.*,b.namauser from ".$dbname.".log_rfq_chat a left join ".$dbname.".user b
	on a.karyawanid=b.karyawanid
	where 1=1 and a.nomor='".$nomor."' order by tanggal asc";	 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

$no=0;
while($bar=$res->fetch())
{
	$no+=1;
	if($no%2==0)
	{
		$ct="style='background-color:#FFFFFF'";
	}
	else
	{
		$ct="style='background-color:#E8F2FE'";
	}
	echo"<tr>
		<td ".$ct.">".$bar->namauser."</td>
		<td ".$ct.">".$bar->tanggal."</td>
		<td ".$ct.">".$bar->pesan."</td>
	</tr>";
}
   
echo"</table>"; 
?>
