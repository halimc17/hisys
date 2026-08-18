<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$notransaksi=$_POST['notransaksi'];
$karid=$_POST['karid'];

$str="select * from ".$dbname.".approval where notransaksi='".$notransaksi."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$per['persetujuan'.$bar['level']]=$bar['karyawanid'];
}

 // echo "<pre>";
 // print_r($per);
 // echo "</pre>";
 // exit('warning');

$str="select * from ".$dbname.".sdm_pjdinasht
      where karyawanid=".$karid ." and notransaksi='".$notransaksi."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
echo"<?xml version='1.0' ?>
	     <pjd>";
while($bar=$res->fetch())
{

	
	  echo" <karyawanid>".($bar->karyawanid!=""?$bar->karyawanid:"*")."</karyawanid>
			 <kodeorg>".($bar->kodeorg!=""?$bar->kodeorg:"*")."</kodeorg>
			 <persetujuan1>".($per['persetujuan1']!=""?$per['persetujuan1']:"*")."</persetujuan1>
			 <persetujuan2>".($per['persetujuan2']!=""?$per['persetujuan2']:"*")."</persetujuan2>
			 <persetujuan3>".($per['persetujuan3']!=""?$per['persetujuan3']:"*")."</persetujuan3>
			 <persetujuan4>".($per['persetujuan4']!=""?$per['persetujuan4']:"*")."</persetujuan4>
			 <tujuan3>".($bar->tujuan3!=""?$bar->tujuan3:"*")."</tujuan3>
			 <unit>".($bar->unit!=""?$bar->unit:"*")."</unit>
			 <tujuan2>".($bar->tujuan2!=""?$bar->tujuan2:"*")."</tujuan2>
		     <tujuan1>".($bar->tujuan1!=""?$bar->tujuan1:"*")."</tujuan1>
			 <tanggalperjalanan>".($bar->tanggalperjalanan!=""?tanggalnormal($bar->tanggalperjalanan):"*")."</tanggalperjalanan>
			 <tanggalkembali>".($bar->tanggalkembali!=""?tanggalnormal($bar->tanggalkembali):"*")."</tanggalkembali>
			 <uangmuka>".($bar->uangmuka!=""?$bar->uangmuka:"*")."</uangmuka>
			 <tugas1>".($bar->tugas1!=""?$bar->tugas1:"*")."</tugas1>
			 <tugas2>".($bar->tugas2!=""?$bar->tugas2:"*")."</tugas2>
			 <tugas3>".($bar->tugas3!=""?$bar->tugas3:"*")."</tugas3>
			 <tugaslain>".($bar->tugaslain!=""?$bar->tugaslain:"*")."</tugaslain>
			 <tujuanlain>".($bar->tujuanlain!=""?$bar->tujuanlain:"*")."</tujuanlain>
			 <pesawat>".($bar->pesawat!=""?$bar->pesawat:"*")."</pesawat>
			 <darat>".($bar->darat!=""?$bar->darat:"*")."</darat>
			 <laut>".($bar->laut!=""?$bar->laut:"*")."</laut>
			 <mess>".($bar->mess!=""?$bar->mess:"*")."</mess>
			 <notransaksi>".($bar->notransaksi!=""?$bar->notransaksi:"*")."</notransaksi>
			 <jenis>".($bar->jenis!=""?$bar->jenis:"*")."</jenis>
			 <hotel>".($bar->hotel!=""?$bar->hotel:"*")."</hotel>
			 
			 <kendaraandinas>".($bar->kendaraandinas!=""?$bar->kendaraandinas:"*")."</kendaraandinas>
			 <kendaraanpribadi>".($bar->kendaraanpribadi!=""?$bar->kendaraanpribadi:"*")."</kendaraanpribadi>
			 <kendaraanumum>".($bar->kendaraanumum!=""?$bar->kendaraanumum:"*")."</kendaraanumum>
			 <tempatlain>".($bar->tempatlain!=""?$bar->tempatlain:"*")."</tempatlain>";
		/*
		 
		 //tambahan
			$kendaraandinas=$_POST['kendaraandinas'];
			$kendaraanpribadi=$_POST['kendaraanpribadi'];
			$kendaraanumum=$_POST['kendaraanumum'];
			$tempatlain=$_POST['tempatlain'];
		 */   		 	
}
$str="select * from ".$dbname.".sdm_pjdinasdt_rute
      where notransaksi='".$notransaksi."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{	
	$datetime = explode(' ',$bar->waktu);
	$date	  = tanggalnormal($datetime[0]);
	$time	  = $datetime[1];
	echo"
			<kump_tujuan>
				<tujuan for='dari' title='".$_SESSION['lang']['dari']."'>".$bar->dari."</tujuan>
				<tujuan for='tujuan' title='".$_SESSION['lang']['tujuan']."'>".$bar->tujuan."</tujuan>
				<tujuan for='waktu' title='".$_SESSION['lang']['waktu']."'>".$date."_".$time."</tujuan>
				<tujuan for='transportasi' title ='".$_SESSION['lang']['transportasi']."'>".$bar->transportasi."</tujuan>
			</kump_tujuan>
		";
}
$str="select * from ".$dbname.".sdm_pjdinasdt2
      where notransaksi='".$notransaksi."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{	
		echo"
			<kump_rencana>
				<rencana for='tanggal' title='".$_SESSION['lang']['tanggal']."'>".tanggalnormal($bar->tanggal)."</rencana>
				<rencana for='kegiatan' title='".$_SESSION['lang']['rencanakegiatan']."'>".$bar->keterangan."</rencana>
			</kump_rencana>
		";
}
echo "</pjd>";
?>