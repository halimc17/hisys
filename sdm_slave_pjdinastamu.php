<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$notransaksi=$_POST['notransaksi'];
$karid=$_POST['karid'];
$karyawanid=$_POST['karyawanid'];
$kodeorg=$_POST['kodeorg'];
$persetujuan=$_POST['persetujuan'];
$per['persetujuan1']=$_POST['persetujuan1'];
$per['persetujuan2']=$_POST['persetujuan2'];
$per['persetujuan3']=$_POST['persetujuan3'];
$jenispersetujuan='PJDTAMU';

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
$kendaraandinas=$_POST['kendaraandinas'];
$kendaraanpribadi=$_POST['kendaraanpribadi'];
$kendaraanumum=$_POST['kendaraanumum'];
$tempatlain=$_POST['tempatlain'];

if($uangmuka=='')
  $uangmuka=0;

// if ($per['persetujuan3']=='') {
// 	exit('warning : Persetujuan HRD tidak boleh kosong.');
// }

if($method=='insert')
{
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
		  `notransaksi`,`karyawanid`,namatamu,`tanggalbuat`,
		  `tanggalperjalanan`,`kodeorg`,`tujuan1`,
		  `tugas1`,`tujuan2`,`tugas2`,`tujuan3`,
		  `tugas3`,`tugaslain`,`tujuanlain`,
		  `pesawat`,`darat`,`laut`,
		  `mess`,`hotel`,`tanggalkembali`,`uangmuka`,
		  `hrd`,`persetujuan`,`jenis`,
		  `kendaraandinas`,`kendaraanpribadi`,`kendaraanumum`,`tempatlain`,`createdby`
		  ) values(
				'".$notrx."','".$_SESSION['standard']['userid']."','".$karyawanid."','".date('Ymd')."',
				'".$tanggalperjalanan."','".$kodeorg."','".$tujuan1."',
				'".$tugas1."','".$tujuan2."','".$tugas2."','".$tujuan3."',
				'".$tugas3."','".$tugaslain."','".$tujuanlain."',
				'".$pesawat."','".$darat."','".$laut."',
				'".$mess."','".$hotel."','".$tanggalkembali."','".$uangmuka."',
				'".$hrd."','".$persetujuan."','".$jenis."',
				'".$kendaraandinas."','".$kendaraanpribadi."','".$kendaraanumum."','".$tempatlain."','".$_SESSION['standard']['userid']."'
		  )";

	for($i=0; $i<count($rencanatanggal); $i++){
	$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt2 (`notransaksi`,`tanggal`,`keterangan`) values 
				('".$notrx."','".tanggalsystem($rencanatanggal[$i])."','".$rencanakegiatan[$i]."')";
	}

	for($i=0; $i<count($rutedari); $i++){
	$datetime = explode('_',$rutewaktu[$i]);
	$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt_rute (`notransaksi`,`waktu`,`tujuan`,`dari`,`transportasi`) values 
				('".$notrx."','".tanggalsystemn($datetime[0])." ".$datetime[1]."','".$rutetujuan[$i]."','".$rutedari[$i]."','".$rutetrans[$i]."')";
	}

	try{
		for($i=0; $i<count($qstr); $i++){
			$owlPDO->exec($qstr[$i]); 
		}

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
		
		echo $notrx;

	}catch (PDOException $e){
		echo " Gagal:".addslashes($e->getMessage());
		die();
	}
}
else if($method=='delete')
{

	$notransaksi=$_POST['notransaksi'];
	$qstr[]="delete from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'"; 
		  
	$qstr[]="delete from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."'"; 	

	$qstr[]="delete from ".$dbname.".sdm_pjdinasdt_rute where notransaksi='".$notransaksi."'"; 

	$qstr[]="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenispersetujuan."'"; 

	try{
		for($i=0; $i<count($qstr); $i++){
			$owlPDO->exec($qstr[$i]); 
		}
	}catch (PDOException $e){
		echo " Gagal:".addslashes($e->getMessage());
		die();
	}
}
else if($method=='update')
{
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

  	$notransaksi=$_POST['notransaksi'];
	$qstr[]="update ".$dbname.".sdm_pjdinasht set
		  `tanggalperjalanan`='".$tanggalperjalanan."',
		  `namatamu`='".$karyawanid."',
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
		where notransaksi='".$notransaksi."'"; 	
	
	$qstr[]="delete from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."'"; 	
	$qstr[]="delete from ".$dbname.".sdm_pjdinasdt_rute where notransaksi='".$notransaksi."'"; 
		    
	for($i=0; $i<count($rencanatanggal); $i++){
	$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt2 (`notransaksi`,`tanggal`,`keterangan`) values 
				('".$notransaksi."','".tanggalsystem($rencanatanggal[$i])."','".$rencanakegiatan[$i]."')";
	}

	for($i=0; $i<count($rutedari); $i++){
	$datetime = explode('_',$rutewaktu[$i]);
	$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt_rute (`notransaksi`,`waktu`,`tujuan`,`dari`,`transportasi`) values 
				('".$notransaksi."','".tanggalsystemn($datetime[0])." ".$datetime[1]."','".$rutetujuan[$i]."','".$rutedari[$i]."','".$rutetrans[$i]."')";
	}

	for($i=1; $i<4; $i++){
		if($per['persetujuan'.$i]!=''){
			$qstr[]="update ".$dbname.".approval set karyawanid='".$per['persetujuan'.$i]."' where notransaksi='".$notransaksi."' 
			and jenispersetujuan='".$jenispersetujuan."' and level='".$i."'";
		}
	}

	try{
		for($i=0; $i<count($qstr); $i++){
			$owlPDO->exec($qstr[$i]); 
		}
		
		echo $notransaksi;

	}catch (PDOException $e){
		echo " Gagal:".addslashes($e->getMessage());
		die();
	}

}else if ($method=='loadList'){

	if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
		$whereKary = " and bagian = 'HHRS'";
	} else {
		$whereKary = " and bagian = 'HRA' and kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
	}

	#limit/page
	$limit=20;
	$page=0;
	#========================
	#ambil jumlah baris dalam tahun ini
	$notransaksi="";
	if(isset($_POST['tex']))
	{
		$notransaksi.=$_POST['tex'];
	}
	$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht where notransaksi like '%".$notransaksi."%'
		  and createdby=".$_SESSION['standard']['userid']." and jeniskaryawan=0 order by jlhbrs desc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		$jlhbrs=$bar->jlhbrs;
	}		
	#==================
		 
  	if(isset($_POST['page'])){
	 	$page=$_POST['page'];
	    if($page<0)
		  $page=0;
	} 
	$offset=$page*$limit;
  	$str="select * from ".$dbname.".sdm_pjdinasht where notransaksi like '%".$notransaksi."%' and createdby=".$_SESSION['standard']['userid']." 
  		  and jeniskaryawan=0 order by tanggalbuat desc limit ".$offset.",20";	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);		
	$no=$page*$limit;
	while($bar=$res->fetch())
	{
	  	$no+=1;
		$add='';
		if($bar->statuspersetujuan==0)
		{
			$add.="&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delPJD('".$bar->notransaksi."');\">
		 		   &nbsp <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editPJD('".$bar->notransaksi."');\">";
		}

	   	if($bar->statuspersetujuan==2)
	    	$stpersetujuan=$_SESSION['lang']['ditolak'];
		else if($bar->statuspersetujuan==1)
	    	$stpersetujuan=$_SESSION['lang']['disetujui'];
		else {
	    	$stpersetujuan=$_SESSION['lang']['wait_approve'];	
	   	}

	  	$stat = array('0' =>$_SESSION['lang']['wait_approve'],'1' =>$_SESSION['lang']['disetujui'],'3'=>$_SESSION['lang']['ditolak'] );
	  	$strap="select * from ".$dbname.".approval 
	        where notransaksi='".$bar->notransaksi."'
			order by level asc";	
		$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
		$resap->setFetchMode(PDO::FETCH_ASSOC);
		while($barap=$resap->fetch())
		{
			$ttl.="Persetujuan ".$barap['level']." : ".$stat[$barap['status']]."\n";
		}

		echo"<tr class=rowcontent>
		  <td align=center>".$no."</td>
		  <td>".$bar->notransaksi."</td>
		  <td>".$bar->namatamu."</td>
		  <td>".tanggalnormal($bar->tanggalbuat)."</td>
		  <td align=center title='".$ttl."'>".$stpersetujuan."</td>
		  <td align=center>
		     <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."','".$bar->jeniskaryawan."',event);\"> 
	       	 ".$add."
		  </td>
		  </tr>";
  	}
	
	echo"<tr><td colspan=11 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	   <br>
       <button class=mybutton onclick=loadList(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	   <button class=mybutton onclick=loadList(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	   </tr>";
}else if ($method=='getdata'){

	$str="select * from ".$dbname.".approval where notransaksi='".$notransaksi."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$per['persetujuan'.$bar['level']]=$bar['karyawanid'];
	}

	$str="select * from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	echo"<?xml version='1.0' ?><pjd>";
	while($bar=$res->fetch())
	{

		
		  echo" <karyawanid>".($bar->namatamu!=""?$bar->namatamu:"*")."</karyawanid>
				 <kodeorg>".($bar->kodeorg!=""?$bar->kodeorg:"*")."</kodeorg>
				 <persetujuan1>".($per['persetujuan1']!=""?$per['persetujuan1']:"*")."</persetujuan1>
				 <tujuan3>".($bar->tujuan3!=""?$bar->tujuan3:"*")."</tujuan3>
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
	}
	$str="select * from ".$dbname.".sdm_pjdinasdt_rute where notransaksi='".$notransaksi."'";
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
			</kump_tujuan>";
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
			</kump_rencana>";
	}
	echo "</pjd>";
}

// echo "<pre>";
//  print_r($qstr);
//  echo "</pre>";
//  exit('warning');

?>