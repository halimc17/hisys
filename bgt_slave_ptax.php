<?php
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');

require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

use Dompdf\Dompdf;

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
$method          = checkPostGet('method','');
$unit            = checkPostGet('unit','');
$tahun           = checkPostGet('tahun','');
$notransaksi     = checkPostGet('notransaksi','');
$tipepta         = checkPostGet('tipepta','');
$jenis           = checkPostGet('jenis','');
$tipe            = checkPostGet('tipe','');
$kodeapproval    = checkPostGet('kodeapproval','');
$kepada          = checkPostGet('kepada','');
$path            = "fileupload/pta/";
$param['tanggal']= tanggalsystemn($param['tanggal']);
$param['rupiah'] = str_replace(",","",$param['rupiah']);
$param['jhk']    = str_replace(",","",$param['jhk']);
$param['jumlah'] = str_replace(",","",$param['jumlah']);

// echo"<pre>";
// print_r($param);
// echo"</pre>";
// exit("error");
$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$arrHsl=array("9"=>$_SESSION['lang']['wait_approval'],"0"=>$_SESSION['lang']['belumdiajukan'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['reconfirm']);

switch($method){
	case'gettipepta':
		if($param['tipebudget']=='ESTATE'){
			$data=array('ESTATE','UMUM','KAPITAL');
		}elseif($param['tipebudget']=='MILL'){
			$data=array('MILL','UMUM','KAPITAL');
		}elseif($param['tipebudget']=='KANWIL'){
			$data=array('UMUM','KAPITAL');
		}elseif($param['tipebudget']=='TC'){
			$data=array('UMUM','KAPITAL');
		}elseif($param['tipebudget']=='RND'){
			$data=array('UMUM','KAPITAL');
		}elseif($param['tipebudget']=='BULKING'){
			$data=array('UMUM','KAPITAL');
		}elseif($param['tipebudget']=='HOLDING'){
			$data=array('UMUM','KAPITAL');
		}elseif($param['tipebudget']=='TRK'){
			$data=array('TRK');
		}elseif($param['tipebudget']=='WS'){
			$data=array('WS');
		}
		$opttipebgt="<option value=''></option>";
		foreach($data as $bar){
			$opttipebgt.="<option value='".$bar."'>".$bar."</option>";
		}
		
	echo $opttipebgt;
	break;
	case'getnotrans':
		if($param['tipepta']=='KAPITAL'){
			$str="select max(left(notransaksi,3)) as nomor from ".$dbname.".bgt_kapital where kodeunit like '".$param['unit']."%' and tahunbudget = '".$param['tahun']."' and pta='PTA'";
		}else{
			$wh="";
			if($param['tipepta']=='UMUM'){			
				$wh="and kodebudget='".$param['tipepta']."'";
			}
		
			$str="select max(left(notransaksi,3)) as nomor from ".$dbname.".bgt_budget where kodeorg like '".$param['unit']."%' and tahunbudget = '".$param['tahun']."' and tipebudget = '".$param['tipebudget']."' ".$wh."  and pta='PTA'";
		}

		$res=fetchdata($str);
		foreach($res as $bar){
			$no=$bar['nomor'];
		}			
	
		if($no==0){
			$notran='001/'.$param['unit'].'/PTA/'.$param['tipepta'].'/'.$param['tahun'];
		}else{
			$notran=addZero($no+1,3).'/'.$param['unit'].'/PTA/'.$param['tipepta'].'/'.$param['tahun'];
		}
		
		
	echo $notran;
	break;
	
	case'postingconfirm':
		$str = "update " . $dbname . ".sdm_pjdinasht set statusconfirm='1' where notransaksi = '".$notransaksi."'"; 
		#exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'insertheader':
		if($param['notransaksi']==''){
			exit("Error : Notransaksi wajib diisi.");
		}
		if($param['tahun']==''){
			exit("Error : Tahun wajib diisi.");
		}
		if($param['unit']==''){
			exit("Error : Unit wajib dipilih.");
		}
		if($param['tipebudget']==''){
			exit("Error : Tipe budget wajib dipilih.");
		}
		if($param['tipepta']==''){
			exit("Error : Tipe PTA wajib diisi.");
		}
		if($param['ket']==''){
			exit("Error : Keterangan wajib diisi.");
		}
		
		if($param['tahun']<='2022'){
			exit("Error : PTA sudah ditutup.");
		}
	break;
	case'updateheader':
	
		try {
		$owlPDO->beginTransaction();
		
		if($param['notransaksi']==''){
			throw new PDOException("Notransaksi wajib diisi.");
		}
		if($param['tahun']==''){
			throw new PDOException("Tahun wajib diisi.");
		}
		if($param['unit']==''){
			throw new PDOException("Unit wajib dipilih.");
		}
		if($param['tipebudget']==''){
			throw new PDOException("Tipe budget wajib dipilih.");
		}
		if($param['tipepta']==''){
			throw new PDOException("Tipe PTA wajib diisi.");
		}
		if($param['ket']==''){
			throw new PDOException("Keterangan wajib diisi.");
		}
		
		if($param['tipepta']=='KAPITAL'){
			$table='bgt_kapital';
		}else{			
			$table='bgt_budget';
		}
			$data = array();
			$data = array(
				'keterangan2'=> $param['ket'],
				'updateby'   => $_SESSION['standard']['userid'],
				'lastupdate' => date('Y-m-d H:i:s')
			);
			$where = "notransaksi='".$notransaksi."' and pta='PTA'";
			$str = updateQuery($dbname,$table,$data,$where);
			$owlPDO->exec($str);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	case'getthntnm':
		$str="select distinct tahuntanam from ".$dbname.".setup_blok where kodeorg like  '".$param['divisi']."%' order by tahuntanam";
		$res=fetchdata($str);
		$optunit="<option value=''></option>";
		foreach($res as $bar){
			$optunit.="<option value=".$bar['tahuntanam'].">".$bar['tahuntanam']."</option>";
		}
	echo $optunit;
	break;
	case'getluas':
		$str="select sum(luasareaproduktif) as luas, statusblok from ".$dbname.".setup_blok where kodeorg like  '".$param['divisi']."%' and tahuntanam='".$param['tahuntanam']."'";
		$res=fetchdata($str);
		$luas = $res[0]['luas'];
		
		if($res[0]['statusblok']=='TM'){
			$sts="and kelompok in ('TM','PNN')";
		}else{
			$sts="and kelompok = '".$res[0]['statusblok']."'";
		}
		
		$str="select * from ".$dbname.".setup_kegiatan where 1=1 ".$sts."";
		$res=fetchdata($str);
		$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($res as $bar){
			$optunit.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
		}
		
		echo $luas."##".$optunit;
		
	break;
	case'getkodebarang':
		$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang like  '".str_replace("-","",str_replace("M","",$param['kelbrg']))."%' and inactive ='0' order by namabarang";
		#exit("error".$str);
		$res=fetchdata($str);
		$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($res as $bar){
			$optunit.="<option value=".$bar['kodebarang'].">".$bar['namabarang']." - (".$bar['satuan'].")</option>";
		}
	echo $optunit;
	break;
	case'getharga':
		$optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$param['unit']."'");
	
		$str="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['unit'],0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];
		
		$hargasatuan=0;
		$str = "select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$param['tahun']."' and kodebarang =  '".$param['namabarang']."'";
		$res = fetchdata($str);
		$hargasatuan = $res[0]['hargasatuan'];

		$str="select * from ".$dbname.".log_5saldobulanan where kodebarang =  '".$param['namabarang']."' and kodeorg ='".$optpt[$param['unit']]."' and hargarata>0 order by periode desc limit 1";
		$res=fetchdata($str);
		if($hargasatuan==0){			
			$hargasatuan = $res[0]['hargarata'];
		}
		
		
	echo $hargasatuan; #exit("error");
	break;
	case'simpandetail':
		try {
		$owlPDO->beginTransaction();
		
			if($param['tipepta']=='KAPITAL'){
				$table='bgt_kapital';
				$tabledist='bgt_kapital';
			}else{			
				$table='bgt_budget';
				$tabledist='bgt_distribusi';
			}

			$data = array();
			switch($param['tipepta']){
				case'MILL':
					if($param['noakun']==''){						
						$str = "select * from ".$dbname.".bgt_kode where kodebudget = '".$param['kodebudget']."'";
						$res = fetchdata($str)[0];
						$param['noakun'] = $res['noakun'];
					}
					
					if($param['noakun']==''){
						throw new PDOException("Noakun ".$param['kodebudget']." belum disetting. Silahkan disetting terlebih dahulu melalui menu : Anggaran - Setup - Kode Budget.");
					}
					$whr="and `kodeorg` like '".$param['station']."%'";
					$wh="and `induk` = '".$param['station']."'";
					
					$str = "delete from " . $dbname . ".".$table." where notransaksi='".$param['notransaksi']."' and pta='PTA' and tahunbudget='".$param['tahun']."' and kodebudget='".$param['kodebudget']."' ".$whr.""; 
					$owlPDO->exec($str);
					
					$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where 1=1 ".$wh." and tipe = 'STENGINE'";
					$res = fetchdata($str);
					$jlh = count($res);
					if($jlh>0){
						$no=0;$tjlh=$trp=0;
						foreach($res as $bar){
							$no++;
							if($no<$jlh){
								$jumlah = round(($param['jumlah']/$jlh),5);
								$totalrp= round(($param['rupiah']/$jlh),0);
								
								$tjlh+=$jumlah;
								$trp+=$totalrp;
							}else{
								$jumlah = $param['jumlah']-$tjlh;
								$totalrp= $param['rupiah']-$trp;
							}
							
							if($param['jenis']=='matmill'){
								$optsat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$param['kodebarang']."'");
								$satuanj= $optsat[$param['kodebarang']];
							}else{								
								if($param['kodebudget']=='EXPL-LEMBUR'){
									$satuanj='JAM';
								}else{
									$satuanj='HK';
								}
							}
							$str = "select max(kunci) as kunci from " . $dbname . ".".$table.""; 
							$res = fetchData($str);
							$kunci = $res[0]['kunci']+1;
							
							$data = array(
								'kunci'      => $kunci,
								'tahunbudget'=> $param['tahun'],
								'kodeorg'    => $bar['kodeorganisasi'],
								'tipebudget' => $param['tipebudget'],
								'kodebudget' => $param['kodebudget'],
								'noakun'     => $param['noakun'],
								'rupiah'     => $totalrp,
								'updateby'   => $_SESSION['standard']['userid'],
								'lastupdate' => date('Y-m-d H:i:s'),
								'jumlah'     => $jumlah,
								'satuanj'    => $satuanj,
								'kodebarang' => $param['kodebarang'],
								'keterangan' => $param['keterangan'],
								'keterangan2'=> $param['keterangan2'],
								'tutup'      => '0',
								'pta'        => 'PTA',
								'notransaksi'=> $param['notransaksi'],
								'statuspta'  => '0',
								'tanggal'    => $param['tanggal']
							);
							
							$cols = array();
							foreach($data as $key=>$row) {
								$cols[] = $key;
							}

							$query = insertQuery($dbname,$table,$data,$cols);
							$owlPDO->exec($query);
							
							#sebaran
							$exptgl= explode("-",$param['tanggal']);
							$date  = $exptgl[2];
							$bulan = $exptgl[1];
							
							$data = array();
							$data = array(
								'kunci'     => $kunci,
								'fis'.$bulan=> $jumlah,
								'rp'.$bulan => $totalrp,
								'updateby'  => $_SESSION['standard']['userid'],
								'lastupdate'=> date('Y-m-d H:i:s')
							);

							$cols = array();
							foreach($data as $key=>$row) {
									$cols[] = $key;
							}
							$str = insertQuery($dbname,$tabledist,$data,$cols);
							$owlPDO->exec($str);
						}
					}
				
				break;
				case'KAPITAL':
					$str = "select * from ".$dbname.".bgt_5capex where kodecapex='".$param['jnskapital']."' ";
					$res = fetchdata($str);
					$jlhbrs = count($res);
					
					if($param['kodebarang']=='' and $jlhbrs>0){
						throw new PDOException("Kode barang harus diisi.");
					}
				
					$str = "delete from " . $dbname . ".".$table." where notransaksi='".$param['notransaksi']."' and pta='PTA' and tahunbudget='".$param['tahun']."' and kodeunit='".$param['unit']."' and jeniskapital='".$param['jnskapital']."' and lokasi='".$param['lokasi']."' and aruskas='".$param['aruskas']."' and kodebarang='".$param['kodebarang']."'"; 
					// exit("error".$str);
					$owlPDO->exec($str);
					
					$exptgl= explode("-",$param['tanggal']);
					$date  = $exptgl[2];
					$bulan = $exptgl[1];
					$data = array(
						'tahunbudget' => $param['tahun'],
						'kodeunit'    => $param['unit'],
						'jeniskapital'=> $param['jnskapital'],
						'keterangan'  => $param['keterangan'],
						'aruskas'     => $param['aruskas'],
						'kodebarang'  => $param['kodebarang'],
						'keterangan2' => $param['keterangan2'],
						'jumlah'      => $param['jumlah'],
						'hargasatuan' => $param['rppersat'],
						'hargatotal'  => $param['rupiah'],
						'lokasi'      => $param['lokasi'],
						'updateby'    => $_SESSION['standard']['userid'],
						'lastupdate'  => date('Y-m-d H:i:s'),
						'pta'         => 'PTA',
						'notransaksi' => $param['notransaksi'],
						'statuspta'   => '0',
						'k'.$bulan    => $param['rupiah'],
						'tanggal'     => $param['tanggal']
					);

					$cols = array();
					foreach($data as $key=>$row) {
							$cols[] = $key;
					}
					$str = insertQuery($dbname,$table,$data,$cols);
					$owlPDO->exec($str);
					
					
				break;
				case'ESTATE':
				$str="select * from ".$dbname.".setup_blok where 1=1 and kodeorg like '".$param['divisi']."%' and tahuntanam ='".$param['tahuntanam']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$wh="";
					if($param['kodebarang']==''){$wh.=" and kodebarang is null";}else{$wh.=" and kodebarang ='".$param['kodebarang']."'";}
					if($param['kodevhc']==''){$wh.=" and kodevhc is null";}else{$wh.=" and kodevhc='".$param['kodevhc']."'";}
					
					$str = "delete from " . $dbname . ".".$table." where notransaksi='".$param['notransaksi']."' and pta='PTA' and tahunbudget='".$param['tahun']."' and kodeorg='".$bar['kodeorg']."' and kodebudget='".$param['kodebudget']."' and kegiatan='".$param['kegiatan']."' ".$wh.""; 
					#exit("error".$str);
					$owlPDO->exec($str);
					
					$optakun= makeOption($dbname,'setup_kegiatan','kodekegiatan,noakun');
					
					$vol    = $bar['luasareaproduktif'] / $param['luasareal'] * $param['volume'];
					$rupiah = round($vol / $param['volume'] * $param['rupiah'],2);
					$jlh    = $vol / $param['volume'] * $param['jumlah'];
					
					if($param['jenis']=='sdm'){
						$satuanv = 'HA';
						$satuanj = 'HK';
					}
					if($param['jenis']=='mat'){
						$satuanv = 'HA';
						$optsat  = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$param['kodebarang']."'");
						$satuanj = $optsat[$param['kodebarang']];
					}
					$str = "select max(kunci) as kunci from " . $dbname . ".".$table.""; 
					$res = fetchData($str);
					$kunci = $res[0]['kunci']+1;
					
					$data = array(
						'kunci'      => $kunci,
						'tahunbudget'=> $param['tahun'],
						'kodeorg'    => $bar['kodeorg'],
						'tipebudget' => $param['tipebudget'],
						'kodebudget' => $param['kodebudget'],
						'kegiatan'   => $param['kegiatan'],
						'noakun'     => $optakun[$param['kegiatan']],
						'volume'     => $vol,
						'satuanv'    => $satuanv,
						'rupiah'     => $rupiah,
						'rotasi'     => $param['rotasi'],
						'updateby'   => $_SESSION['standard']['userid'],
						'lastupdate' => date('Y-m-d H:i:s'),
						'jumlah'     => $jlh,
						'satuanj'    => $satuanj,
						'kodebarang' => $param['kodebarang'],
						'keterangan' => $param['keterangan'],
						'keterangan2'=> $param['keterangan2'],
						'tutup'      => '0',
						'pta'        => 'PTA',
						'notransaksi'=> $param['notransaksi'],
						'statuspta'  => '0',
						'tanggal'    => $param['tanggal']
					);

					$cols = array();
					foreach($data as $key=>$row) {
							$cols[] = $key;
					}
					$str = insertQuery($dbname,$table,$data,$cols);
					$owlPDO->exec($str);
					
					#sebaran
					$exptgl= explode("-",$param['tanggal']);
					$date  = $exptgl[2];
					$bulan = $exptgl[1];
					
					$data = array();
					$data = array(
						'kunci'     => $kunci,
						'fis'.$bulan=> $jlh,
						'rp'.$bulan => $rupiah,
						'updateby'  => $_SESSION['standard']['userid'],
						'lastupdate'=> date('Y-m-d H:i:s')
					);

					$cols = array();
					foreach($data as $key=>$row) {
							$cols[] = $key;
					}
					$str = insertQuery($dbname,$tabledist,$data,$cols);
					$owlPDO->exec($str);
				}
				
				break;
				case'TRK':
					$wh="";
					if($param['kodebarang']==''){$wh.=" and kodebarang is null";}else{$wh.=" and kodebarang ='".$param['kodebarang']."'";}
					if($param['kodevhc']==''){$wh.=" and kodevhc is null";}else{$wh.=" and kodevhc='".$param['kodevhc']."'";}
					if($param['kegiatan']==''){$wh.=" and kegiatan is null";}else{$wh.=" and kegiatan='".$param['kegiatan']."'";}
					
					$str = "delete from " . $dbname . ".".$table." where notransaksi='".$param['notransaksi']."' and pta='PTA' and tahunbudget='".$param['tahun']."' and kodeorg='".$param['divisi']."' and kodebudget='".$param['kodebudget']."' ".$wh.""; 
					#exit("error".$str);
					$owlPDO->exec($str);
					
					$optakun= makeOption($dbname,'setup_kegiatan','kodekegiatan,noakun');
					
					if($param['jenis']=='sdmtrk'){
						$satuanv='HK';
						$jlh='0';
						$volume=$param['jumlah'];
					}
					if($param['jenis']=='mattrk'){
						$jlh=$param['jumlah'];
						$volume='0';
						
						$satuanv='';
						$optsat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$param['kodebarang']."'");
						$satuanj=$optsat[$param['kodebarang']];
					}
					
					$str = "select max(kunci) as kunci from " . $dbname . ".".$table.""; 
					$res = fetchData($str);
					$kunci = $res[0]['kunci']+1;
					
					$data = array(
						'kunci'      => $kunci,
						'tahunbudget'=> $param['tahun'],
						'kodeorg'    => $param['divisi'],
						'tipebudget' => $param['tipebudget'],
						'kodebudget' => $param['kodebudget'],
						'kegiatan'   => $param['kegiatan'],
						'noakun'     => $optakun[$param['kegiatan']],
						'volume'     => $volume,
						'satuanv'    => $satuanv,
						'rupiah'     => $param['rupiah'],
						'rotasi'     => $param['rotasi'],
						'kodevhc'    => $param['kodevhc'],
						'updateby'   => $_SESSION['standard']['userid'],
						'lastupdate' => date('Y-m-d H:i:s'),
						'jumlah'     => $jlh,
						'satuanj'    => $satuanj,
						'kodebarang' => $param['kodebarang'],
						'keterangan' => $param['keterangan'],
						'keterangan2'=> $param['keterangan2'],
						'tutup'      => '0',
						'pta'        => 'PTA',
						'notransaksi'=> $param['notransaksi'],
						'statuspta'  => '0',
						'tanggal'    => $param['tanggal']
					);

					$cols = array();
					foreach($data as $key=>$row) {
							$cols[] = $key;
					}
					$str = insertQuery($dbname,$table,$data,$cols);#exit("error".$str.$param['jenis']);
					$owlPDO->exec($str);
					
					#sebaran
					$exptgl= explode("-",$param['tanggal']);
					$date  = $exptgl[2];
					$bulan = $exptgl[1];
					
					$data = array();
					$data = array(
						'kunci'     => $kunci,
						'fis'.$bulan=> $jlh,
						'rp'.$bulan => $param['rupiah'],
						'updateby'  => $_SESSION['standard']['userid'],
						'lastupdate'=> date('Y-m-d H:i:s')
					);

					$cols = array();
					foreach($data as $key=>$row) {
							$cols[] = $key;
					}
					$str = insertQuery($dbname,$tabledist,$data,$cols);
					$owlPDO->exec($str);
				
				break;
				case'UMUM':
					$wh="";
					$param['kodebudget']=$param['tipepta'];
					
					$str = "delete from " . $dbname . ".".$table." where notransaksi='".$param['notransaksi']."' and pta='PTA' and tahunbudget='".$param['tahun']."' and kodeorg='".$param['unit']."' and kodebudget='".$param['kodebudget']."' and noakun='".$param['noakun']."' ".$wh." and aruskas='".$param['aruskas']."' and kodebarang='".$param['kodebarang']."'"; 
					// exit("error".$str);
					$owlPDO->exec($str);
					
					$optakun= makeOption($dbname,'setup_kegiatan','kodekegiatan,noakun');
					
					$jlh=$param['kuantitas'];
					$satuanv='';
					$volume='0';
					
					$optsat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$param['kodebarang']."'");
					$satuanj= $optsat[$param['kodebarang']];
					
					$str = "select max(kunci) as kunci from " . $dbname . ".".$table.""; 
					$res = fetchData($str);
					$kunci = $res[0]['kunci']+1;
					$data = array(
						'kunci'      => $kunci,
						'tahunbudget'=> $param['tahun'],
						'kodeorg'    => $param['unit'],
						'tipebudget' => $param['tipebudget'],
						'kodebudget' => $param['kodebudget'],
						'kegiatan'   => $param['kegiatan'],
						'aruskas'    => $param['aruskas'],
						'noakun'     => $param['noakun'],
						'volume'     => $volume,
						'satuanv'    => $satuanv,
						'rupiah'     => $param['rupiah'],
						'rotasi'     => $param['rotasi'],
						'kodevhc'    => $param['kodevhc'],
						'updateby'   => $_SESSION['standard']['userid'],
						'lastupdate' => date('Y-m-d H:i:s'),
						'jumlah'     => $jlh,
						'satuanj'    => $satuanj,
						'kodebarang' => $param['kodebarang'],
						'keterangan' => $param['keterangan'],
						'keterangan2'=> $param['keterangan2'],
						'tutup'      => '0',
						'pta'        => 'PTA',
						'notransaksi'=> $param['notransaksi'],
						'statuspta'  => '0',
						'tanggal'    => $param['tanggal']
					);

					$cols = array();
					foreach($data as $key=>$row) {
							$cols[] = $key;
					}
					$str = insertQuery($dbname,$table,$data,$cols);#exit("error".$str.$param['jenis']);
					$owlPDO->exec($str);
					
					#sebaran
					$exptgl= explode("-",$param['tanggal']);
					$date  = $exptgl[2];
					$bulan = $exptgl[1];
					
					$data = array();
					$data = array(
						'kunci'     => $kunci,
						'fis'.$bulan=> $jlh,
						'rp'.$bulan => $param['rupiah'],
						'updateby'  => $_SESSION['standard']['userid'],
						'lastupdate'=> date('Y-m-d H:i:s')
					);

					$cols = array();
					foreach($data as $key=>$row) {
							$cols[] = $key;
					}
					$str = insertQuery($dbname,$tabledist,$data,$cols);
					$owlPDO->exec($str);
				
				break;
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	case'gethargabarang':
		$str="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['unit'],0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];
		
		$whr="";
		$whr.=" and kodebarang = '".$param['kodebarang']."'";
		
		
		$harga=0;
		$str="select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$param['tahun']."' and closed=1 ".$whr." ";
		$res=fetchData($str);
		foreach($res as $bar){
			$harga=$bar['hargasatuan'];
		}

		echo $harga;
	break;
	case'loadinputdetail':
		OPEN_BOX();
		switch($tipepta){
			#ESTATE, UMUM, KAPITAL, MILL, TRK
			case'MILL':
				$str="select * from ".$dbname.".organisasi where induk = '".$param['unit']."' and tipe in ('STATION')";
				$res=fetchdata($str);
				$optunit="<option value=''></option>";
				foreach($res as $bar){
					$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
				}

				echo"<fieldset style=float:left><legend><b>".$tipepta."</b></legend>";
				echo"<table border=0 style='display: inline-block;vertical-align:top'>
					<tr>
						<td>".$_SESSION['lang']['station']."</td>
						<td>:</td>
						<td colspan=3><select class=select2 id=station style='width:150px;'>".$optunit."</select></td>
					</tr>
					";
				echo"</table>";
				echo"</fieldset><div style=clear:both></div><hr>";
				$frm[0]="";
				$frm[0].="<fieldset><legend>".$_SESSION['lang']['sdm']."</legend>
						<table border=0 cellpadding=2 cellspacing=1 class=\"sortable tableforinput\">
						<thead><tr class=rowheader>";
					
					$rows="rowspan=2";	
					$frm[0].="<th align=center ".$rows." width=25px>No</th>
						<th align=center ".$rows.">".$_SESSION['lang']['tipekaryawan']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['jumlah']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['rupiahsatuan']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['rupiah']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['keterangan']."</th>
						<th align=center ".$rows.">" . $_SESSION['lang']['action'] . "</th>
					</tr>
					</thead>";
				#==== Form Judul Detail ====
				$optjnsbyy="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
				if($tipeorg[$param['unit']]=='BULKING'){
					$wh=" and kodebudget like 'EXPLBULK%'";
				}else{
					$wh=" and kodebudget like 'EXPL%' and kodebudget not like 'EXPLBULK%'";
				}
				
				$str="select * from ".$dbname.".bgt_kode where 1=1 ".$wh."";
				$res=fetchdata($str);
				foreach($res as $bar){
					$optjnsbyy.="<option value=".$bar['kodebudget'].">".$bar['nama']."</option>";
				}
				#=== Isi input detail ===
				$frm[0].="<tbody id=inputbiaya>
				<tr class=rowcontent>
					<td valign=top align=center>1</td>
					<td valign=top><select class=select2 id=tipekary style='width:150px;'>".$optjnsbyy."</select></td>
					<td valign=top><input id=jhk onkeyup=getrupiah('sdm'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=rpperhk onkeyup=getrupiah('sdm'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=ttlrupiahhk disabled class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=ketsdm class=myinputtext style='width:175px;'></td>
					";
				$frm[0].="<td align=center><img title='Simpan' class='zImgBtn' onclick=simpandetail('sdmmill'); src='images/save.png'></td>";
				$frm[0].="</tr>";
				$frm[0].="</tbody></table>";
				$frm[0].="</fieldset>";
				$frm[0].="<hr>";
				
				
				
				#=== List data tersimpan input detail ===	
				$frm[0].="<div style=clear:both></div>";
				$frm[0].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
							<div id=loaddatasdmpks></div>
						</fieldset>";
						
				#=== MATERIAL ===	
				$frm[1].="<fieldset><legend>".$_SESSION['lang']['material']."</legend>
					<table border=0 cellpadding=1 cellspacing=1 class=sortable>
					<thead><tr class=rowheader style=height:25px>";
				
				$rows="rowspan=2";	
				$frm[1].="<th align=center ".$rows." width=20px>No</th>
					<th align=center ".$rows." colspan=2>".$_SESSION['lang']['akun']."</th>
					<th align=center ".$rows.">".$_SESSION['lang']['kelompokbarang']."</th>
					<th align=center ".$rows." colspan=2>".$_SESSION['lang']['namabarang']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['volume']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['rupiahsatuan']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['jumlah']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['keterangan']."</th>
					<th align=center ".$rows.">" . $_SESSION['lang']['action'] . "</th>
				</tr>
				</thead>";
				$optkelbrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$str="select * from ".$dbname.".bgt_kode where kodebudget like 'M-%'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$optkelbrg.="<option value=".$bar['kodebudget'].">".$bar['kodebudget']." - ".$bar['nama']."</option>";
				}
				$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$str="select * from ".$dbname.".keu_5akun where noakun like '63%' and aktif=1";
				$res=fetchdata($str);
				foreach($res as $bar){
					if(strlen($bar['noakun'])=='3'){
						$d=$bar['noakun'];
						if($d!=$n){			
							$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
							$optakun.="<optgroup label='".$bar['namaakun']."'>";
						}
					}
					if(strlen($bar['noakun'])=='7'){						
						$optakun.="<option value=".$bar['noakun'].">".$bar['noakun']." - ".$bar['namaakun']."</option>";
					}
					if(strlen($bar['noakun'])=='3'){						
						$n=$d;
					}
				}
				
				$frm[1].="<tbody id=inputbiaya><tr class=rowcontent>
					<td valign=top align=center>1</td>
					<td valign=top colspan=2><select class=select2 id=akunmill style='width:150px;'>".$optakun."</select></td>
					<td valign=top><select class=select2 id=kelbrg onchange=getkodebarang(); style='width:150px;'>".$optkelbrg."</select></td>
					<td valign=top colspan=2><select class=select2 id=namabarang onchange=getharga(); style='width:150px;'></select></td>
					<td valign=top><input id=volume onkeyup=getrupiah('mat'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:70px;'></td>
					<td valign=top><input id=rppermat onkeyup=getrupiah('mat'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:90px;'></td>
					<td valign=top><input id=ttlrupiahmat disabled class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:90px;'></td>
					<td valign=top><input id=ketmat class=myinputtext style='width:175px;'></td>
					";
				$frm[1].="<td align=center><img title='Simpan' class='zImgBtn' onclick=simpandetail('matmill'); src='images/save.png'></td>";
				$frm[1].="</tr>";
				$frm[1].="</tbody></table>";
				$frm[1].="</fieldset>";
				$frm[1].="<hr>";
				
				
				#=== List data tersimpan ===	
				$frm[1].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
					<div id=loaddatamatpks style=height:40vh;overflow:auto>
					</div></fieldset>";
				
				#=== List data rekap ===	
				$frm[2].="<fieldset><legend>" . $_SESSION['lang']['rekap'] . "</legend>
					<div id=loaddatarekappks style=height:40vh;overflow:auto>
					</div></fieldset>";
					
				$hfrm[0]=$_SESSION['lang']['sdm'];
				$hfrm[1]=$_SESSION['lang']['material'];
				$hfrm[2]=$_SESSION['lang']['rekap'];
				drawTab('FRM',$hfrm,$frm,175,'100%');
			break;
			case'ESTATE':
				$str="select * from ".$dbname.".organisasi where induk = '".$param['unit']."' and tipe in ('AFDELING','BIBITAN')";
				$res=fetchdata($str);
				$optunit="<option value=''></option>";
				foreach($res as $bar){
					$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
				}

				echo"<fieldset style=float:left><legend><b>".$tipepta."</b></legend>";
				echo"<table border=0 style='display: inline-block;vertical-align:top'>
					<tr>
						<td>".$_SESSION['lang']['divisi']."</td>
						<td>:</td>
						<td colspan=3><select class=select2 id=divisi onchange=getthntnm(); style='width:150px;'>".$optunit."</select></td>
						<td></td>
						
						<td>".$_SESSION['lang']['tahuntanam']."</td>
						<td>:</td>
						<td colspan=3><select class=select2 id=tahuntanam onchange=getluas(); style='width:70px;'>".$opttipebgt."</select></td>

						<td>".$_SESSION['lang']['luasareal']."</td>
						<td>:</td>
						<td colspan=3><input disabled class=myinputtextnumber style='width:70px' id=luasareal onkeypress='return angka_doang(event)'></td>
					</tr>
					<tr>		
						<td>".$_SESSION['lang']['kegiatan']."</td>
						<td>:</td>
						<td colspan=4><select class=select2 id=kegiatan style='width:150px;'>".$opttipepta."</select></td>
						
						
						<td>".$_SESSION['lang']['rotasi']."</td>
						<td>:</td>
						<td colspan=3><input class=myinputtextnumber onkeyup=getluaspta(); style='width:65px' id=rotasi onkeypress='return angka_doang(event)'></td>
						
						<td>".$_SESSION['lang']['luas']." PTA (Ha)</td>
						<td>:</td>
						<td colspan=3><input disabled class=myinputtextnumber style='width:70px' id=luaspta onkeypress='return angka_doang(event)'></td>
					</tr> 
					
					";
				echo"</table>";
				echo"</fieldset><div style=clear:both></div><br>";
				$frm[0]="";
				$frm[0].="<fieldset><legend>".$_SESSION['lang']['sdm']."</legend>
						<table border=0 cellpadding=1 cellspacing=1 class=sortable>
						<thead><tr class=rowheader style=height:25px>";
					
					$rows="rowspan=2";	
					$frm[0].="<th align=center ".$rows." width=20px>No</th>
						<th align=center ".$rows.">".$_SESSION['lang']['tipekaryawan']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['jumlah']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['rupiahsatuan']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['rupiah']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['keterangan']."</th>
						<th align=center ".$rows.">" . $_SESSION['lang']['action'] . "</th>
					</tr>
					</thead>";
				#==== Form Judul Detail ====
				$optjnsbyy="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$str="select * from ".$dbname.".bgt_kode where kodebudget like 'SDM%' and nama like 'SDM%'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$optjnsbyy.="<option value=".$bar['kodebudget'].">".$bar['nama']."</option>";
				}
				#=== Isi input detail ===
				$frm[0].="<tbody id=inputbiaya>
				<tr class=rowcontent>
					<td valign=top align=center>1</td>
					<td valign=top><select class=select2 id=tipekary style='width:150px;'>".$optjnsbyy."</select></td>
					<td valign=top><input id=jhk onkeyup=getrupiah('sdm'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=rpperhk onkeyup=getrupiah('sdm'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=ttlrupiahhk disabled class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=ketsdm class=myinputtext style='width:175px;'></td>
					";
				$frm[0].="<td align=center><img title='Simpan' class='zImgBtn' onclick=simpandetail('sdm'); src='images/save.png'></td>";
				$frm[0].="</tr>";
				$frm[0].="</tbody></table>";
				$frm[0].="</fieldset>";
				$frm[0].="<hr>";
				
				
				
				#=== List data tersimpan input detail ===	
				$frm[0].="<div style=clear:both></div>";
				$frm[0].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
							<div id=loaddatasdm style=height:40vh;overflow:auto></div>
						</fieldset>";
						
				#=== MATERIAL ===	
				$frm[1].="<fieldset><legend>".$_SESSION['lang']['material']."</legend>
					<table border=0 cellpadding=1 cellspacing=1 class=sortable>
					<thead><tr class=rowheader style=height:25px>";
				
				$rows="rowspan=2";	
				$frm[1].="<th align=center ".$rows." width=20px>No</th>
					<th align=center ".$rows.">".$_SESSION['lang']['kelompokbarang']."</th>
					<th align=center ".$rows." colspan=2>".$_SESSION['lang']['namabarang']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['volume']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['rupiahsatuan']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['jumlah']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['keterangan']."</th>
					<th align=center ".$rows.">" . $_SESSION['lang']['action'] . "</th>
				</tr>
				</thead>";
				$optkelbrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$str="select * from ".$dbname.".bgt_kode where kodebudget like 'M-%'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$optkelbrg.="<option value=".$bar['kodebudget'].">".$bar['kodebudget']." - ".$bar['nama']."</option>";
				}
				
				$frm[1].="<tbody id=inputbiaya><tr class=rowcontent>
					<td valign=top align=center>1</td>
					<td valign=top><select class=select2 id=kelbrg onchange=getkodebarang(); style='width:150px;'>".$optkelbrg."</select></td>
					<td colspan=2 valign=top><select class=select2 id=namabarang onchange=getharga(); style='width:150px;'></select></td>
					
					<td valign=top><input id=volume onkeyup=getrupiah('mat'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=rppermat onkeyup=getrupiah('mat'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=ttlrupiahmat disabled class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=ketmat class=myinputtext style='width:175px;'></td>
					";
				$frm[1].="<td align=center><img title='Simpan' class='zImgBtn' onclick=simpandetail('mat'); src='images/save.png'></td>";
				$frm[1].="</tr>";
				$frm[1].="</tbody></table>";
				$frm[1].="</fieldset>";
				$frm[1].="<hr>";
				
				
				#=== List data tersimpan ===	
				$frm[1].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
					<div id=loaddatamat style=height:40vh;overflow:auto>
					</div></fieldset>";
				
				#=== List data rekap ===	
				$frm[2].="<fieldset><legend>" . $_SESSION['lang']['rekap'] . "</legend>
					<div id=loaddatarekap style=height:40vh;overflow:auto>
					</div></fieldset>";
					
				$hfrm[0]=$_SESSION['lang']['sdm'];
				$hfrm[1]=$_SESSION['lang']['material'];
				$hfrm[2]=$_SESSION['lang']['rekap'];
				drawTab('FRM',$hfrm,$frm,175,'100%');
			break;
			case'TRK':
				$str="select * from ".$dbname.".organisasi where induk = '".$param['unit']."' and tipe in ('TRAKSI')";
				$res=fetchdata($str);
				$optunit="<option value=''></option>";
				foreach($res as $bar){
					$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
				}
				
				$str="select * from ".$dbname.".vhc_5master where kodetraksi like '".$param['unit']."%' and status='1'";
				$res=fetchdata($str);
				$optvhc="<option value=''></option>";
				foreach($res as $bar){
					$n="";
					if($bar['nopol']!=''){
						$n=" - ".$bar['nopol']."";
					}
					if($bar['detailvhc']!=''){
						$n.=" - ".$bar['detailvhc']."";
					}
					$optvhc.="<option value=".$bar['kodevhc'].">".$bar['kodevhc'].$n."</option>";
				}
				
				echo"<fieldset style=float:left><legend><b>".$tipepta."</b></legend>";
				echo"<table border=0 style='display: inline-block;vertical-align:top'>
					<tr>
						<td>".$_SESSION['lang']['kodetraksi']."</td>
						<td>:</td>
						<td colspan=3><select class=select2 id=kodetraksi style='width:150px;'>".$optunit."</select></td>
						<td></td>
						
						<td>".$_SESSION['lang']['kodevhc']."</td>
						<td>:</td>
						<td colspan=4><select class=select2 id=kodevhc style='width:150px;'>".$optvhc."</select></td>
						
					</tr>
					
					
					";
				echo"</table>";
				
				echo"</fieldset><div style=clear:both></div><br>";
				
				$frm[0]="";
				$frm[0].="<fieldset><legend>".$_SESSION['lang']['sdm']."</legend>
						<table border=0 cellpadding=1 cellspacing=1 class=sortable>
						<thead><tr class=rowheader style=height:25px>";
					
					$rows="rowspan=2";	
					$frm[0].="<th align=center ".$rows." width=20px>No</th>
						<th align=center ".$rows.">".$_SESSION['lang']['tipekaryawan']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['jhk']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['rupiahsatuan']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['jumlah']."</th>
						<th align=center ".$rows." >".$_SESSION['lang']['keterangan']."</th>
						<th align=center ".$rows.">" . $_SESSION['lang']['action'] . "</th>
					</tr>
					</thead>";
				#==== Form Judul Detail ====
				$optjnsbyy="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$str="select * from ".$dbname.".bgt_kode where kodebudget like 'SDM%' and nama like 'SDM%'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$optjnsbyy.="<option value=".$bar['kodebudget'].">".$bar['kodebudget']." - ".$bar['nama']."</option>";
				}
				#=== Isi input detail ===
				$frm[0].="<tbody id=inputbiaya>
				<tr class=rowcontent>
					<td valign=top align=center>1</td>
					<td valign=top><select class=select2 id=tipekary style='width:150px;'>".$optjnsbyy."</select></td>
					<td valign=top><input id=jhk onkeyup=getrupiah('sdm'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=rpperhk onkeyup=getrupiah('sdm'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=ttlrupiahhk disabled class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=ketsdm class=myinputtext style='width:175px;'></td>
					";
				$frm[0].="<td align=center><img title='Simpan' class='zImgBtn' onclick=simpandetail('sdmtrk'); src='images/save.png'></td>";
				$frm[0].="</tr>";
				$frm[0].="</tbody></table>";
				$frm[0].="</fieldset>";
				$frm[0].="<hr>";
				
				
				
				#=== List data tersimpan input detail ===	
				$frm[0].="<div style=clear:both></div>";
				$frm[0].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
							<div id=loaddatasdm style=height:40vh;overflow:auto></div>
						</fieldset>";
						
				#=== MATERIAL ===	
				$frm[1].="<fieldset><legend>".$_SESSION['lang']['material']."</legend>
					<table border=0 cellpadding=1 cellspacing=1 class=sortable>
					<thead><tr class=rowheader style=height:25px>";
				
				$rows="rowspan=2";	
				$frm[1].="<th align=center ".$rows." width=20px>No</th>
					<th align=center ".$rows.">".$_SESSION['lang']['kelompokbarang']."</th>
					<th align=center ".$rows." colspan=2>".$_SESSION['lang']['namabarang']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['volume']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['rupiahsatuan']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['jumlah']."</th>
					<th align=center ".$rows." >".$_SESSION['lang']['keterangan']."</th>
					<th align=center ".$rows.">" . $_SESSION['lang']['action'] . "</th>
				</tr>
				</thead>";
				$optkelbrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$str="select * from ".$dbname.".bgt_kode where kodebudget like 'M-%'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$optkelbrg.="<option value=".$bar['kodebudget'].">".$bar['kodebudget']." - ".$bar['nama']."</option>";
				}
				
				$frm[1].="<tbody id=inputbiaya><tr class=rowcontent>
					<td valign=top align=center>1</td>
					<td valign=top><select class=select2 id=kelbrg onchange=getkodebarang(); style='width:150px;'>".$optkelbrg."</select></td>
					<td valign=top colspan=2><select class=select2 id=namabarang onchange=getharga(); style='width:150px;'></select></td>
					
					<td valign=top><input id=volume onkeyup=getrupiah('mat'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=rppermat onkeyup=getrupiah('mat'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=ttlrupiahmat disabled class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:100px;'></td>
					<td valign=top><input id=ketmat class=myinputtext style='width:175px;'></td>
					";
				$frm[1].="<td align=center><img title='Simpan' class='zImgBtn' onclick=simpandetail('mattrk'); src='images/save.png'></td>";
				$frm[1].="</tr>";
				$frm[1].="</tbody></table>";
				$frm[1].="</fieldset>";
				$frm[1].="<hr>";
				
				
				#=== List data tersimpan ===	
				$frm[1].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
					<div id=loaddatamat style=height:40vh;overflow:auto>
					</div></fieldset>";
				
				#=== List data rekap ===	
				$frm[2].="<fieldset><legend>" . $_SESSION['lang']['rekap'] . "</legend>
					<div id=loaddatarekap style=height:40vh;overflow:auto>
					</div></fieldset>";
					
				$hfrm[0]=$_SESSION['lang']['sdm'];
				$hfrm[1]=$_SESSION['lang']['material'];
				$hfrm[2]=$_SESSION['lang']['rekap'];
				drawTab('FRM',$hfrm,$frm,175,'100%');
			break;
			case'KAPITAL':
				$str="select * from ".$dbname.".sdm_5tipeasset where 1=1 and kodetipe in (select kodecapex from ".$dbname.".bgt_5capex)";
				$res=fetchdata($str);
				$optunit="<option value=''></option>";
				foreach($res as $bar){
					$optunit.="<option value=".$bar['kodetipe'].">".$bar['kodetipe']." - ".$bar['namatipe']."</option>";
				}
				$str="select * from ".$dbname.".organisasi where 1=1 and (induk ='".$param['unit']."' or kodeorganisasi='".$param['unit']."') and tipe !='GUDANGTEMP'";
				$res=fetchdata($str);
				$lokasi="<option value=''></option>";
				foreach($res as $bar){
					$lokasi.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
				}
				
				echo"<fieldset ><legend><b>".$tipepta."</b></legend>";
				echo"<table border=0 style='display: inline-block;vertical-align:top'>
					<tr>
						<td>".$_SESSION['lang']['jnsKapital']."</td>
						<td>:</td>
						<td colspan=2><select class=select2 id=jnskapital onchange=getaruskas('jnskapital','aruskas'); style='width:150px;'>".$optunit."</select></td>
						
						
						<td>".$_SESSION['lang']['lokasi']."</td>
						<td>:</td>
						<td colspan=2><select class=select2 id=lokasi style='width:150px;'>".$lokasi."</select></td>
						
						<td>".$_SESSION['lang']['keterangan']."</td>
						<td>:</td>
						<td><input id=ketkapital class=myinputtext style='width:175px;'></td>
					</tr>
					<tr>	
						<td>".$_SESSION['lang']['aruskas']."</td>
						<td>:</td>
						<td colspan=2><select class=select2 id=aruskas style='width:150px;'>".$optaruskas."</select></td>
						
						
						<td>".$_SESSION['lang']['kodebarang']."</td>
						<td>:</td>
						<td colspan=6><select class=select2 id=kodebarang onchange=gethargabarang(); style='width:405px;'></select>
										<input id=flagbarang hidden>
						</td>
						
						
					</tr>
					<tr>	
						<td>".$_SESSION['lang']['jumlah']."</td>
						<td>:</td>
						<td valign=top colspan=2><input id=jlhkap onkeyup=getrupiah('kapital'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:145px;'></td>
						
						<td>".$_SESSION['lang']['rupiahsatuan']."</td>
						<td>:</td>
						<td valign=top colspan=2><input id=rppersat onkeyup=getrupiah('kapital'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:145px;'></td>
						
						<td>".$_SESSION['lang']['rupiah']."</td>
						<td>:</td>
						<td valign=top><input id=ttlrupiahkap disabled class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:175px;'></td>
						
					</tr>
					<tr><td colspan=2></td>
						<td align=left><button title='Simpan' class='mybutton' onclick=simpandetail('kapital');>Simpan</button></td>
					</tr>
					
					
					";
				echo"</table>";
				
				echo"</fieldset><div style=clear:both></div>";
				
				#=== List data tersimpan input detail ===	
				echo"<div style=clear:both></div>";
				echo"<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
							<div id=loaddatakapital style=height:40vh;overflow:auto></div>
						</fieldset>";
						
			break;
			default:
				#tampilan umum
				if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
					$wh=" and noakun like '8%'";
				}else{
					$wh=" and noakun like '7%'";
				}
				
				
				$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
				
				$wh="";
				if($tipeorg[$param['unit']]=='KEBUN'){
					$wh.=" and noakun like '7%'";
				}

				if($tipeorg[$param['unit']]=='PABRIK'){
					$wh.=" and noakun like '7%'";
				}

				if($tipeorg[$param['unit']]=='TC'){
					$wh.=" and noakun like '82%'";
				}

				if($tipeorg[$param['unit']]=='RND'){
					$wh.=" and noakun like '82%'";
				}
				if($tipeorg[$param['unit']]=='TC'){
					$wh.=" and noakun like '82%'";
				}

				if($tipeorg[$param['unit']]=='KANWIL'){
					$wh.=" and noakun like '82%'";
				}

				if($tipeorg[$param['unit']]=='BULKING'){
					$wh.=" and noakun like '81%'";
				}

				$wh.=" or noakun like '9%' and aktif='1' and level='5'";

			
				$str="select * from ".$dbname.".keu_5akun where 1=1 and aktif='1' and level='5' ".$wh."";
				$res=fetchdata($str);
				$optunit="<option value=''></option>";
				foreach($res as $val){
					$d=substr($val['noakun'],0,3);
					if($d!=$n){			
						$optunit.="<optgroup label='".getNamaAkun($d)."'>";
					}
					$optunit.="<option value=".$val['noakun']." ".$b.">".$val['noakun']." - ".$val['namaakun']."</option>";
					$n=$d;
					if($d!=$n){			
						$optunit.="</optgroup>";
					}
				}
				
				echo"<fieldset><legend><b>".$tipepta."</b></legend>";
				echo"<table border=0 style='display: inline-block;vertical-align:top'>
					<tr>
						<td>".$_SESSION['lang']['noakun']."</td>
						<td>:</td>
						<td><select onchange=getaruskas('noakun','aruskasumum',this.value,'','','umum'); class=select2 id=noakun style='width:200px;'>".$optunit."</select></td>
						
						
						<td>".$_SESSION['lang']['aruskas']."</td>
						<td>:</td>
						<td colspan=4><select class=select2 id=aruskasumum style='width:200px;'></select></td>
						
						<td>".$_SESSION['lang']['keterangan']."</td>
						<td>:</td>
						<td><input id=ketumum class=myinputtext style='width:195px;'></td>
					</tr>	
					<tr>	
						<td>".$_SESSION['lang']['kodebarang']."</td>
						<td>:</td>
						<td>
							<input id=kodebarang class=myinputtext hidden onclick=getpopupbarang(); style='width:195px;'>
							<input id=namabarang class=myinputtext readonly onclick=getpopupbarang(); style='width:195px;'>
						</td>
						
						<td>".$_SESSION['lang']['satuan']."</td>
						<td>:</td>
						<td><input disabled id=satuanbrg class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:65px;'></td>
						
						<td>Qty</td>
						<td>:</td>
						<td><input id=kuantitas onkeyup=hitungbarang(); disabled class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:90px;'></td>
						
						<td>".$_SESSION['lang']['rupiah']."</td>
						<td>:</td>
						<td><input id=rupiahbarang disabled class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:195px;'></td>
						<input id=hargasatuan hidden>
						
					</tr>	
					<tr>	
						<td>".$_SESSION['lang']['jumlah']."</td>
						<td>:</td>
						<td><input id=jlhumum class=myinputtextnumber onkeypress=\"return angka_doang(event)\" style='width:195px;'></td>
						
					</tr>
					<tr><td colspan=2></td>
						<td align=left><button title='Simpan' class='mybutton' onclick=simpandetail('umum');>Simpan</button></td>
					</tr>
					
					
					";
				echo"</table>";
				
				echo"</fieldset><div style=clear:both></div>";
				
				#=== List data tersimpan input detail ===	
				echo"<div style=clear:both></div>";
				echo"<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
							<div id=loaddataumum style=height:40vh;overflow:auto></div>
						</fieldset>";
						
			break;
		}
		
		CLOSE_BOX();
	break;
	case'getpopupbarang':
		$tab="<label>".$_SESSION['lang']['findmaterial']."&nbsp;</label>";
		$tab.="<input id=kodebarangcari class=myinputtext style='width:250px;height:25px'>&nbsp;";
		$tab.="<button title='Cari' class='mybutton' style='width:100px;height:28px' onclick=getbarang();>Cari</button>";
		$tab.="<div style=clear:both></div>";
		$tab.="<div id=getpopupbarang></div>";
		
		echo $tab;
	break;
	case'getbarang':
		$tab="
		<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$tab.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['kodebarang']."</th>
			<th align=center >".$_SESSION['lang']['namabarang']."</th>
			<th align=center >".$_SESSION['lang']['satuan']."</th>
			<th align=center >".$_SESSION['lang']['harga']."</th>
		</tr>
		</thead><tbody>";
		
		$str = "select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['kodeorg'],0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];
		
		if($param['kodebarang']!=''){
			$whr.=" and kodebarang in (select kodebarang from ".$dbname.".log_5masterbarang where (kodebarang like '%".$param['kodebarang']."%' or namabarang like '%".$param['kodebarang']."%') and inactive='0')";
		}
		
		$whr.=" and (kodebarang like '3%' or kodebarang like '8%') ";
		$no=0;
		$str="select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$param['tahun']."' ".$whr." ";
		$res=fetchData($str);
		if(count($res)==0){
			$str="select * from ".$dbname.".log_5masterbarang where 1=1 ".$whr." ";
			$res=fetchData($str);
		}
		foreach($res as $bar){
			$s="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$bar['kodebarang']."'";
			$nm=fetchData($s)[0];
			
			$no+=1;
			if($bar['hargasatuan']>0){
				$set="style=cursor:pointer onclick=\"setdata('".$bar['kodebarang']."','".trim($nm['namabarang'])."','".trim($nm['satuan'])."','".trim($bar['hargasatuan'])."')\"";
			}else{
				$set="style=background-color:#FEE0B9; title=\"Harga barang belum ada.\"";
			}
			$tab.="<tr class=rowcontent ".$set.">";				
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['kodebarang']."</td>";
			$tab.="<td>".$nm['namabarang']."</td>";
			$tab.="<td align=center>".$nm['satuan']."</td>";
			$tab.="<td align=right>".@number_format($bar['hargasatuan'])."</td>";
			$tab.="</tr>";
		}			
		
		
		echo $tab;
	break;
	case'loaddatakapital':
		$tab="<div >
		<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$tab.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['tahun']."</th>
			<th align=center >".$_SESSION['lang']['unit']."</th>
			<th align=center >".$_SESSION['lang']['lokasi']."</th>
			<th align=center >".$_SESSION['lang']['jnsKapital']."</th>
			<th align=center >".$_SESSION['lang']['keterangan']."</th>
			<th align=center >".$_SESSION['lang']['aruskas']."</th>
			<th align=center >".$_SESSION['lang']['barang']."</th>
			<th align=center >".$_SESSION['lang']['jumlah']."</th>
			<th align=center >".$_SESSION['lang']['harga']."</th>
			<th align=center >".$_SESSION['lang']['rupiah']."</th>
			<th align=center >" . $_SESSION['lang']['action'] . "</th>
		</tr>
		</thead><tbody>";
		$str="select * from ".$dbname.".bgt_kapital where tahunbudget = '".$param['tahun']."' and notransaksi = '".$param['notransaksi']."' and pta='PTA' and kodeunit like '".$param['unit']."%' order by  kunci";
		$res=fetchdata($str);$no=0;
		foreach($res as $bar){
			$no++;
			$opttipekar = makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$bar['kodebudget']."'");
			$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
			$optakun = makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe',"kodetipe ='".$bar['jeniskapital']."'");
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['tahunbudget']."</td>";
			$tab.="<td align=left>".$bar['kodeunit']."</td>";
			$tab.="<td align=left>".getNamaOrg($bar['lokasi'])."</td>";
			$tab.="<td align=left>".$bar['jeniskapital']." - ".$optakun[$bar['jeniskapital']]."</td>";
			$tab.="<td align=left>".$bar['keterangan']."</td>";
			$tab.="<td align=left>".$bar['aruskas']." - ".getNamaAruskas($bar['aruskas'])."</td>";
			$tab.="<td align=left>".$bar['kodebarang']." - ".getNamaBrg($bar['kodebarang'])."</td>";
			$tab.="<td align=right>".numb_format($bar['jumlah'])."</td>";
			$tab.="<td align=right>".numb_format($bar['hargasatuan'])."</td>";
			$tab.="<td align=right>".numb_format($bar['hargatotal'])."</td>";
			$tab.="<td align=center style='width:25px'><img title='delete' class=zImgBtn onclick=deletedetail('".$bar['kunci']."') src=images/application/application_delete.png></td>";
			$ttlrp+=$bar['hargatotal'];	
			$tab.="</tr>";
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=10>T O T A L</td>";
		$tab.="<td align=right>".numb_format($ttlrp)."</td>";
		$tab.="<td align=center></td>";
		$tab.="</tr>";
		$tab.="</tbody></table></div>";
		
		echo $tab;
	break;
	case'loaddataumum':
		$tab="<div >
		<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$tab.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['tahun']."</th>";
			//$tab.="<th align=center >".$_SESSION['lang']['unit']."</th>";
			$tab.="<th align=center >".$_SESSION['lang']['aruskas']."</th>
			<th align=center >".$_SESSION['lang']['noakun']."</th>
			<th align=center >".$_SESSION['lang']['keterangan']."</th>
			<th align=center >".$_SESSION['lang']['kodebarang']."</th>
			<th align=center >".$_SESSION['lang']['namabarang']."</th>
			<th align=center >".$_SESSION['lang']['satuan']."</th>
			<th align=center >".$_SESSION['lang']['jumlah']."</th>
			<th align=center >".$_SESSION['lang']['rupiah']."</th>
			<th align=center >" . $_SESSION['lang']['action'] . "</th>
		</tr>
		</thead><tbody>";
		$str="select * from ".$dbname.".bgt_budget where tahunbudget = '".$param['tahun']."' and notransaksi = '".$param['notransaksi']."' and pta='PTA' and kodeorg like '".$param['unit']."%' and tipebudget = '".$param['tipebudget']."' and kodebudget like 'UMUM%' order by  kunci";
		$res=fetchdata($str);$no=0;
		foreach($res as $bar){
			$no++;
			$optthntnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
			$opttipekar = makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$bar['kodebudget']."'");
			$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
			$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['tahunbudget']."</td>";
			//$tab.="<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td align=left>".$bar['aruskas']." - ".getNamaAruskas($bar['aruskas'])."</td>";
			$tab.="<td align=left>".$bar['noakun']." - ".$optakun[$bar['noakun']]."</td>";
			$tab.="<td align=left>".$bar['keterangan']."</td>";
			$tab.="<td align=center>".$bar['kodebarang']."</td>";
			$tab.="<td align=left>".getNamaBrg($bar['kodebarang'])."</td>";
			$tab.="<td align=left>".$bar['satuanj']."</td>";
			$tab.="<td align=right>".numb_format($bar['jumlah'])."</td>";
			$tab.="<td align=right>".numb_format($bar['rupiah'])."</td>";
			$tab.="<td align=center style='width:25px'><img title='delete' class=zImgBtn onclick=deletedetail('".$bar['kunci']."') src=images/application/application_delete.png></td>";
			$ttlvol+=$bar['volume'];	
			$ttlrp+=$bar['rupiah'];	
			$ttljlh+=$bar['jumlah'];	
			$tab.="</tr>";
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=9>T O T A L</td>";
		$tab.="<td align=right>".numb_format($ttlrp)."</td>";
		$tab.="<td align=center></td>";
		$tab.="</tr>";
		$tab.="</tbody></table></div>";
		
		echo $tab;
	break;
	case'loaddatasdmpks':
	
		$tab="<div >
		<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$tab.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['tahun']."</th>
			<th align=center >".$_SESSION['lang']['station']."</th>
			<th align=center >".$_SESSION['lang']['mesin']."</th>
			<th align=center >".$_SESSION['lang']['kodeanggaran']."</th>
			<th align=center >".$_SESSION['lang']['noakun']."</th>
			<th align=center >".$_SESSION['lang']['rupiah']."</th>
			<th align=center >".$_SESSION['lang']['jumlah']."</th>
			<th align=center >".$_SESSION['lang']['satuan']."</th>
			<th align=center >" . $_SESSION['lang']['action'] . "</th>
		</tr>
		</thead><tbody>";
		$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where tahunbudget = '".$param['tahun']."' and notransaksi = '".$param['notransaksi']."' and pta='PTA' and kodeorg like '".$param['unit']."%' and tipebudget = '".$param['tipebudget']."' and kodebudget like 'EXPL%' order by divisi, kunci";
		$res=fetchdata($str);$no=0;
		foreach($res as $bar){
			$no++;
			$opttipekar= makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$bar['kodebudget']."'");
			$optakun   = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['tahunbudget']."</td>";
			$tab.="<td align=left>".$nmorg[$bar['divisi']]."</td>";
			$tab.="<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td align=left>".$bar['kodebudget']." - ".$opttipekar[$bar['kodebudget']]."</td>";
			$tab.="<td align=left>".$bar['noakun']." - ".$optakun[$bar['noakun']]."</td>";
			$tab.="<td align=right>".numb_format($bar['rupiah'])."</td>";
			$tab.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
			$tab.="<td align=center>".$bar['satuanj']."</td>";
			$tab.="<td align=center style='width:25px'><img title='delete' class=zImgBtn onclick=deletedetail('".$bar['kunci']."') src=images/application/application_delete.png></td>";
			$ttlvol+=$bar['volume'];	
			$ttlrp+=$bar['rupiah'];	
			$ttljlh+=$bar['jumlah'];	
			$tab.="</tr>";
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=6>T O T A L</td>";
		$tab.="<td align=right>".numb_format($ttlrp)."</td>";
		$tab.="<td align=right>".numb_format($ttljlh,2)."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=center></td>";
		
		
		$tab.="</tr>";
		$tab.="</tbody></table></div>";
		echo $tab;
		
	break;
	case'loaddatamatpks':
		$tab="<div >
		<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$tab.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['tahun']."</th>
			<th align=center >".$_SESSION['lang']['divisi']."</th>
			<th align=center >".$_SESSION['lang']['station']."</th>
			<th align=center >".$_SESSION['lang']['namabarang']."</th>
			<th align=center >".$_SESSION['lang']['noakun']."</th>
			<th align=center >".$_SESSION['lang']['rupiah']."</th>
			<th align=center >".$_SESSION['lang']['jumlah']."</th>
			<th align=center >".$_SESSION['lang']['satuan']."</th>
			<th align=center >" . $_SESSION['lang']['action'] . "</th>
		</tr>
		</thead><tbody>";
		$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where tahunbudget = '".$param['tahun']."' and notransaksi = '".$param['notransaksi']."' and pta='PTA' and kodeorg like '".$param['unit']."%' and tipebudget = '".$param['tipebudget']."' and kodebudget like 'M-%' order by divisi, kunci";
		$res=fetchdata($str);$no=0;
		foreach($res as $bar){
			$no++;
			$optthntnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
			$opttipekar = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
			$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['tahunbudget']."</td>";
			$tab.="<td align=left>".$nmorg[$bar['divisi']]."</td>";
			$tab.="<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td align=left>".$bar['kodebarang']." - ".$opttipekar[$bar['kodebarang']]."</td>";
			$tab.="<td align=left>".$bar['noakun']." - ".$optakun[$bar['noakun']]."</td>";
			$tab.="<td align=right>".numb_format($bar['rupiah'])."</td>";
			$tab.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
			$tab.="<td align=center>".$bar['satuanj']."</td>";
			$tab.="<td align=center style='width:25px'><img title='delete' class=zImgBtn onclick=deletedetail('".$bar['kunci']."') src=images/application/application_delete.png></td>";
			$ttlvol+=$bar['volume'];	
			$ttlrp+=$bar['rupiah'];	
			$ttljlh+=$bar['jumlah'];	
			$tab.="</tr>";
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=6>T O T A L</td>";
		$tab.="<td align=right>".numb_format($ttlrp)."</td>";
		$tab.="<td align=right>".numb_format($ttljlh,2)."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=center></td>";
		
		
		$tab.="</tr>";
		$tab.="</tbody></table></div>";
		
		echo $tab;
	break;
	case'loaddatasdm':
		$tab="<div >
		<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$tab.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['tahun']."</th>
			<th align=center >".$_SESSION['lang']['divisi']."</th>
			<th align=center >".$_SESSION['lang']['blok']."</th>
			<th align=center width=50px>".$_SESSION['lang']['tahuntanam']."</th>
			<th align=center >".$_SESSION['lang']['tipekaryawan']."</th>
			<th align=center >".$_SESSION['lang']['kegiatan']."</th>
			<th align=center >".$_SESSION['lang']['noakun']."</th>
			<th align=center >".$_SESSION['lang']['rotasi']."</th>
			<th align=center >".$_SESSION['lang']['volume']."</th>
			<th align=center >".$_SESSION['lang']['satuan']."</th>
			<th align=center >".$_SESSION['lang']['rupiah']."</th>
			<th align=center >".$_SESSION['lang']['jumlah']."</th>
			<th align=center >".$_SESSION['lang']['satuan']."</th>
			<th align=center >" . $_SESSION['lang']['action'] . "</th>
		</tr>
		</thead><tbody>";
		$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where tahunbudget = '".$param['tahun']."' and notransaksi = '".$param['notransaksi']."' and pta='PTA' and kodeorg like '".$param['unit']."%' and tipebudget = '".$param['tipebudget']."' and kodebudget like 'SDM%' order by divisi, kunci";
		$res=fetchdata($str);$no=0;
		foreach($res as $bar){
			$no++;
			$optthntnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
			$opttipekar = makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$bar['kodebudget']."'");
			$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
			$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['tahunbudget']."</td>";
			$tab.="<td align=left>".$nmorg[$bar['divisi']]."</td>";
			$tab.="<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td align=center>".$optthntnm[$bar['kodeorg']]."</td>";
			$tab.="<td align=left>".$bar['kodebudget']." - ".$opttipekar[$bar['kodebudget']]."</td>";
			$tab.="<td align=left>".$bar['kegiatan']." - ".$optkeg[$bar['kegiatan']]."</td>";
			$tab.="<td align=left>".$bar['noakun']." - ".$optakun[$bar['noakun']]."</td>";
			$tab.="<td align=center>".$bar['rotasi']."</td>";
			$tab.="<td align=right>".numb_format($bar['volume'],2)."</td>";
			$tab.="<td align=center>".$bar['satuanv']."</td>";
			$tab.="<td align=right>".numb_format($bar['rupiah'])."</td>";
			$tab.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
			$tab.="<td align=center>".$bar['satuanj']."</td>";
			$tab.="<td align=center style='width:25px'><img title='delete' class=zImgBtn onclick=deletedetail('".$bar['kunci']."') src=images/application/application_delete.png></td>";
			$ttlvol+=$bar['volume'];	
			$ttlrp+=$bar['rupiah'];	
			$ttljlh+=$bar['jumlah'];	
			$tab.="</tr>";
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=9>T O T A L</td>";
		$tab.="<td align=right>".numb_format($ttlvol,2)."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=right>".numb_format($ttlrp)."</td>";
		$tab.="<td align=right>".numb_format($ttljlh,2)."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=center></td>";
		
		
		$tab.="</tr>";
		$tab.="</tbody></table></div>";
		
		echo $tab;
	break;
	case'loaddatamat':
		$tab="
		<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$tab.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['tahun']."</th>
			<th align=center >".$_SESSION['lang']['divisi']."</th>
			<th align=center >".$_SESSION['lang']['blok']."</th>
			<th align=center width=50px>".$_SESSION['lang']['tahuntanam']."</th>
			<th align=center >".$_SESSION['lang']['namabarang']."</th>
			<th align=center >".$_SESSION['lang']['kegiatan']."</th>
			<th align=center >".$_SESSION['lang']['noakun']."</th>
			<th align=center >".$_SESSION['lang']['rotasi']."</th>
			<th align=center >".$_SESSION['lang']['volume']."</th>
			<th align=center >".$_SESSION['lang']['satuan']."</th>
			<th align=center >".$_SESSION['lang']['rupiah']."</th>
			<th align=center >".$_SESSION['lang']['jumlah']."</th>
			<th align=center >".$_SESSION['lang']['satuan']."</th>
			<th align=center >" . $_SESSION['lang']['action'] . "</th>
		</tr>
		</thead><tbody>";
		$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where tahunbudget = '".$param['tahun']."' and notransaksi = '".$param['notransaksi']."' and pta='PTA' and kodeorg like '".$param['unit']."%' and tipebudget = '".$param['tipebudget']."' and kodebudget like 'M-%' order by divisi, kunci";
		$res=fetchdata($str);$no=0;
		foreach($res as $bar){
			$no++;
			$optthntnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
			$opttipekar = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
			$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['tahunbudget']."</td>";
			$tab.="<td align=left>".$nmorg[$bar['divisi']]."</td>";
			$tab.="<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td align=center>".$optthntnm[$bar['kodeorg']]."</td>";
			$tab.="<td align=left>".$bar['kodebarang']." - ".$opttipekar[$bar['kodebarang']]."</td>";
			$tab.="<td align=left>".$bar['kegiatan']." - ".$optkeg[$bar['kegiatan']]."</td>";
			$tab.="<td align=left>".$bar['noakun']." - ".$optakun[$bar['noakun']]."</td>";
			$tab.="<td align=center>".$bar['rotasi']."</td>";
			$tab.="<td align=right>".numb_format($bar['volume'],2)."</td>";
			$tab.="<td align=center>".$bar['satuanv']."</td>";
			$tab.="<td align=right>".numb_format($bar['rupiah'])."</td>";
			$tab.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
			$tab.="<td align=center>".$bar['satuanj']."</td>";
			$tab.="<td align=center style='width:25px'><img title='delete' class=zImgBtn onclick=deletedetail('".$bar['kunci']."') src=images/application/application_delete.png></td>";
			$ttlvol+=$bar['volume'];	
			$ttlrp+=$bar['rupiah'];	
			$ttljlh+=$bar['jumlah'];	
			$tab.="</tr>";
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=9>T O T A L</td>";
		$tab.="<td align=right>".numb_format($ttlvol,2)."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=right>".numb_format($ttlrp)."</td>";
		$tab.="<td align=right>".numb_format($ttljlh,2)."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=center></td>";
		
		
		$tab.="</tr>";
		$tab.="</tbody></table>";
		
		echo $tab;
	break;
	
	case'loaddatarekap':
		$tab="<div >
		<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$tab.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['tahun']."</th>
			<th align=center >".$_SESSION['lang']['divisi']."</th>
			<th align=center >".$_SESSION['lang']['kegiatan']."</th>
			<th align=center >".$_SESSION['lang']['kodebudget']."</th>
			<th align=center >".$_SESSION['lang']['jumlah']."</th>
			<th align=center >".$_SESSION['lang']['rupiah']."</th>
			<th align=center >" . $_SESSION['lang']['action'] . "</th>
		</tr>
		</thead><tbody>";
		$data=array();
		$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where tahunbudget = '".$param['tahun']."' and notransaksi = '".$param['notransaksi']."' and pta='PTA' and kodeorg like '".$param['unit']."%' and tipebudget = '".$param['tipebudget']."' and (kodebudget like 'M-%' or kodebudget like 'SDM%' ) order by divisi, kunci";
		$res=fetchdata($str);$no=0;
		foreach($res as $bar){
			$data[$bar['divisi']][$bar['kegiatan']][$bar['kodebudget']]=$bar['kodebudget'];
			$rp[$bar['divisi']][$bar['kegiatan']][$bar['kodebudget']]+=$bar['rupiah'];
			$jlh[$bar['divisi']][$bar['kegiatan']][$bar['kodebudget']]+=$bar['jumlah'];
		}
		if(count($data)>0){			
			foreach($data as $divisi => $vkeg){
				foreach($vkeg as $keg => $vkdbgt){
					foreach($vkdbgt as $kdbgt){
						$no++;
						$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$divisi."'");
						$optkeg= makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$keg."'");
						$nmbgt = makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$kdbgt."'");

						$tab.="<tr class=rowcontent>";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td align=center>".$param['tahun']."</td>";
						$tab.="<td align=left>".$divisi." - ".$nmorg[$divisi]."</td>";
						$tab.="<td align=left>".$keg." - ".$optkeg[$keg]."</td>";					
						$tab.="<td align=left>".$kdbgt." - ".$nmbgt[$kdbgt]."</td>";					
						$tab.="<td align=right>".numb_format($jlh[$divisi][$keg][$kdbgt])."</td>";
						$tab.="<td align=right>".numb_format($rp[$divisi][$keg][$kdbgt])."</td>";
						$tab.="<td align=center style='width:25px'><img title='delete' class=zImgBtn onclick=\"deleterekap('".$divisi."','".$keg."','".$kdbgt."','".$param['tahun']."','".$param['notransaksi']."')\" src=images/application/application_delete.png></td>";
						
						$ttlrp+=$rp[$divisi][$keg][$kdbgt];	
						$tab.="</tr>";
					}
				}			
			}
		}
			
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=6>T O T A L</td>";
		$tab.="<td align=right>".numb_format($ttlrp)."</td>";
		$tab.="<td align=center></td>";
		$tab.="</tr>";
		$tab.="</tbody></table></div>";
		
		echo $tab;
	break;
	case'loaddatarekappks':
		$tab="<div >
		<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$tab.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['tahun']."</th>
			<th align=center >".$_SESSION['lang']['station']."</th>
			<th align=center >".$_SESSION['lang']['noakun']."</th>
			<th align=center >".$_SESSION['lang']['kodebudget']."</th>
			<th align=center >".$_SESSION['lang']['jumlah']."</th>
			<th align=center >".$_SESSION['lang']['rupiah']."</th>
			<th align=center >" . $_SESSION['lang']['action'] . "</th>
		</tr>
		</thead><tbody>";
		$data=array();
		$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where tahunbudget = '".$param['tahun']."' and notransaksi = '".$param['notransaksi']."' and pta='PTA' and kodeorg like '".$param['unit']."%' and tipebudget = '".$param['tipebudget']."' and (kodebudget like 'M-%' or kodebudget like 'EXPL%' ) order by divisi, kunci";
		$res=fetchdata($str);$no=0;
		foreach($res as $bar){
			$data[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]=$bar['kodebudget'];
			$rp[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]+=$bar['rupiah'];
			$jlh[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]+=$bar['jumlah'];
		}
		if(count($data)>0){			
			foreach($data as $divisi => $vkeg){
				foreach($vkeg as $keg => $vkdbgt){
					foreach($vkdbgt as $kdbgt){
						$no++;
						$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$divisi."'");
						$optkeg= makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$keg."'");
						$nmbgt = makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$kdbgt."'");

						$tab.="<tr class=rowcontent>";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td align=center>".$param['tahun']."</td>";
						$tab.="<td align=left>".$divisi." - ".$nmorg[$divisi]."</td>";
						$tab.="<td align=left>".$keg." - ".$optkeg[$keg]."</td>";					
						$tab.="<td align=left>".$nmbgt[$kdbgt]."</td>";					
						$tab.="<td align=right>".numb_format($jlh[$divisi][$keg][$kdbgt])."</td>";
						$tab.="<td align=right>".numb_format($rp[$divisi][$keg][$kdbgt])."</td>";
						$tab.="<td align=center style='width:25px'><img title='delete' class=zImgBtn onclick=\"deleterekap('".$divisi."','".$keg."','".$kdbgt."','".$param['tahun']."','".$param['notransaksi']."')\" src=images/application/application_delete.png></td>";
						
						$ttlrp+=$rp[$divisi][$keg][$kdbgt];	
						$tab.="</tr>";
					}
				}			
			}
		}
			
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=6>T O T A L</td>";
		$tab.="<td align=right>".numb_format($ttlrp)."</td>";
		$tab.="<td align=center></td>";
		$tab.="</tr>";
		$tab.="</tbody></table></div>";
		
		echo $tab;
	break;
	case'delete':
	if($param['tipepta']=='KAPITAL'){
		$str = "SELECT * FROM " . $dbname . ".bgt_kapital where notransaksi = '".$param['notransaksi']."' and tahunbudget = '".$param['tahun']."' and pta='PTA' and tutup!='0' and statuspta!='0'";
		$res = fetchdata($str);
		if(count($res)>0){
			exit("Error : Data sudah ditutup atau dalam proses persetujuan.");
		}
		
		$str = "delete from " . $dbname . ".bgt_kapital where notransaksi = '".$param['notransaksi']."' and tahunbudget = '".$param['tahun']."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	}else{
		$str = "SELECT * FROM " . $dbname . ".bgt_budget where notransaksi = '".$param['notransaksi']."' and tahunbudget = '".$param['tahun']."' and pta='PTA' and tutup!='0' and statuspta!='0'";
		$res = fetchdata($str);
		if(count($res)>0){
			exit("Error : Data sudah ditutup atau dalam proses persetujuan.");
		}
		
		$str = "delete from " . $dbname . ".bgt_budget where notransaksi = '".$param['notransaksi']."' and tahunbudget = '".$param['tahun']."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	}
	break;
	
	case'deletedetail':
		if($param['tipepta']=='KAPITAL'){
			$str = "SELECT * FROM " . $dbname . ".bgt_kapital where kunci='".$param['kunci']."' and pta='PTA' and tutup!='0' and statuspta!='0'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Error : Data sudah ditutup atau dalam proses persetujuan.");
			}
			
			$str = "delete from " . $dbname . ".bgt_kapital where kunci='".$param['kunci']."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}else{		
			$str = "SELECT * FROM " . $dbname . ".bgt_budget where kunci='".$param['kunci']."' and pta='PTA' and tutup!='0' and statuspta!='0'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Error : Data sudah ditutup atau dalam proses persetujuan.");
			}
			
			$str = "delete from " . $dbname . ".bgt_budget where kunci='".$param['kunci']."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
	break;
	case'deleterekap':
		$str = "SELECT * FROM " . $dbname . ".bgt_budget where kodeorg like '".$param['divisi']."%' and notransaksi = '".$param['notransaksi']."' and tahunbudget = '".$param['tahun']."' and kodebudget = '".$param['kodebudget']."' and kegiatan = '".$param['kegiatan']."' and pta='PTA' and tutup!='0' and statuspta!='0'";
		$res = fetchdata($str);
		if(count($res)>0){
			exit("Error : Data sudah ditutup atau dalam proses persetujuan.");
		}
		
		$str = "delete from " . $dbname . ".bgt_budget where kodeorg like '".$param['divisi']."%' and tahunbudget = '".$param['tahun']."' and kodebudget = '".$param['kodebudget']."' and kegiatan = '".$param['kegiatan']."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'loaddata':
        $where=$wh="";
        $where.=" and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
        $wh.=" and kodeunit like '".$_SESSION['empl']['lokasitugas']."%'";

		if($notransaksi!=''){
			$where.=" and notransaksi like '%".$notransaksi."%'";
			$wh.=" and notransaksi like '%".$notransaksi."%'";
		}
		
        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
		
		

        $offset = floatval($page) * floatval($limit);
        $maxdisplay = (floatval($page) * floatval($limit));
        $no = 0;
		$tab = "";
        $no = $maxdisplay;
		
		$sql = "select count(distinct notransaksi) as notr from " . $dbname . ".bgt_budget where 1=1 and pta='PTA' " . $where . "";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];
		
		$sql = "select count(distinct notransaksi) as notr from " . $dbname . ".bgt_kapital where 1=1 and pta='PTA' " . $wh . "";
        $res = fetchdata($sql);
        $jlhbrsk = $res[0]['notr'];
		
		$data = $file = array();
		$str = "SELECT distinct notransaksi,tahunbudget,substr(kodeorg,1,4) as unit, sum(rupiah) as rupiah,tanggal,statuspta FROM " . $dbname . ".bgt_budget where 1=1 and pta='PTA' " . $where . " group by notransaksi order by lastupdate desc, notransaksi desc limit " . $offset . "," . $limit . "";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$data[$bar['notransaksi']]=$bar['notransaksi'];
			$thn[$bar['notransaksi']]=$bar['tahunbudget'];
			$tgl[$bar['notransaksi']]=$bar['tanggal'];
			$kdorg[$bar['notransaksi']]=$bar['unit'];
			$rupiah[$bar['notransaksi']]+=$bar['rupiah'];
			$tipex[$bar['notransaksi']]='NONKAPITAL';
			$stspta[$bar['notransaksi']]=$bar['statuspta'];
		}
		
		#HIST diTOLAK
		// $str = "SELECT distinct notransaksi,tahunbudget,substr(kodeorg,1,4) as unit, sum(rupiah) as rupiah,tanggal,statuspta FROM " . $dbname . ".bgt_budget_hist where 1=1 and pta='PTA' " . $where . " group by notransaksi order by lastupdate desc, notransaksi desc";
		// $res = fetchdata($str);
		// foreach ($res as $bar){
			// $data[$bar['notransaksi']]=$bar['notransaksi'];
			// $thn[$bar['notransaksi']]=$bar['tahunbudget'];
			// $tgl[$bar['notransaksi']]=$bar['tanggal'];
			// $kdorg[$bar['notransaksi']]=$bar['unit'];
			// $rupiah[$bar['notransaksi']]+=$bar['rupiah'];
			// $tipex[$bar['notransaksi']]='NONKAPITAL';
			// $stspta[$bar['notransaksi']]=$bar['statuspta'];
		// }
		
		
		$str = "SELECT distinct notransaksi,tahunbudget,substr(kodeunit,1,4) as unit, sum(hargatotal) as rupiah,tanggal,statuspta FROM " . $dbname . ".bgt_kapital where 1=1 and pta='PTA' " . $wh . " group by notransaksi order by lastupdate desc, notransaksi desc limit " . $offset . "," . $limit . "";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$data[$bar['notransaksi']]=$bar['notransaksi'];
			$thn[$bar['notransaksi']]=$bar['tahunbudget'];
			$tgl[$bar['notransaksi']]=$bar['tanggal'];
			$kdorg[$bar['notransaksi']]=$bar['unit'];
			$rupiah[$bar['notransaksi']]+=$bar['rupiah'];
			$tipex[$bar['notransaksi']]='KAPITAL';
			$stspta[$bar['notransaksi']]=$bar['statuspta'];
		}
		
		#HIST diTOLAK
		// $str = "SELECT distinct notransaksi,tahunbudget,substr(kodeunit,1,4) as unit, sum(hargatotal) as rupiah,tanggal,statuspta FROM " . $dbname . ".bgt_kapital_hist where 1=1 and pta='PTA' " . $wh . " group by notransaksi order by lastupdate desc, notransaksi desc";
		// $res = fetchdata($str);
		// foreach ($res as $bar){
			// $data[$bar['notransaksi']]=$bar['notransaksi'];
			// $thn[$bar['notransaksi']]=$bar['tahunbudget'];
			// $tgl[$bar['notransaksi']]=$bar['tanggal'];
			// $kdorg[$bar['notransaksi']]=$bar['unit'];
			// $rupiah[$bar['notransaksi']]+=$bar['rupiah'];
			// $tipex[$bar['notransaksi']]='KAPITAL';
			// $stspta[$bar['notransaksi']]=$bar['statuspta'];
		// }
		
		if(count($data)>0){
			$str = "SELECT * FROM " . $dbname . ".listfileupload where 1=1 and kriteriaefil='PTA' and notransaksi in ('".implode("','",$data)."')";
			// echo $str;
			$res = fetchdata($str);
			foreach ($res as $bar){
				$file[$bar['notransaksi']]+=1;
			}
		}
		
		// echo"<pre>";
		// print_r($data);
		
		
		if(($jlhbrs+$jlhbrsk)==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=13 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}else{
			$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
			rsort($data);
			foreach ($data as $notr){
				$no+=1;
				$tab.="<tr height=25px class=rowcontent  id=tr_$no>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=center>".$notr."</td>";
				$tab.="<td align=center>".$thn[$notr]."</td>";
				$tab.="<td align=center>".tanggalnormal($tgl[$notr])."</td>";
				$tab.="<td align=center>".$kdorg[$notr]." - ".$nmorg[$kdorg[$notr]]."</td>";
				$tab.="<td align=right>".numb_format($rupiah[$notr])."</td>";
				
				
				$strx = "select * from ".$dbname.".approval where notransaksi ='".$notr."' and jenispersetujuan = 'PTA' order by level asc";
				$resx = fetchdata($strx);
				foreach ($resx as $barx){
					$kary=$barx['karyawanid'];
					$koment=$barx['komentar'];
				}
				
				$wr="";
				if($stspta[$notr]=='9' or $stspta[$notr]=='3'){
					$wr="style=background-color:yellow";
				}elseif($stspta[$notr]=='1'){
					$wr="style=background-color:green";
				}elseif($stspta[$notr]=='2'){
					$wr="style=background-color:red";
				}
				
				
				$nmkary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$kary."'");
				if($stspta[$notr]==0){
					$nmkary[$kary]="";
				}
				$tab.="<td align=center ".$wr."><i>".$arrHsl[$stspta[$notr]]."<br>".$nmkary[$kary]."<br>".$koment."</i></td>";
				
				
				
				if($stspta[$notr]=='1' or $stspta[$notr]=='9' or $stspta[$notr]=='2'){
					$tab.="<td align=center style=width:20px></td>";
					$tab.="<td align=center style=width:20px></td>";
					$tab.="<td align=center style=width:20px></td>";
				}else{					
					$tab.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillfield('".$notr."','".$tipex[$notr]."');\" ></td>";
					
					$tab.="<td align=center style=width:20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$notr."','".$thn[$notr]."','".$tipex[$notr]."');\" ></td>";				

					$tab.="<td align=center style=width:20px><img src=images/skyblue/submit.jpg class=zImgBtn  title='Ajukan ?' onclick=\"form_ajukan('".$notr."','".$tipex[$notr]."','".$file[$notr]."');\" ></td>";
				}
				
				
				$tab.="<td align=center style=width:20px><img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30'  title='Upload' onclick=\"showupload('".$notr."');\" ></td>";
			
				$tab.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detailPDF('".$notr."','".$tipex[$notr]."','event','pdf');\" ></td>";
				
				$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"detailData('".$notr."','".$tipex[$notr]."','event','html');\" ></td>";
				
				$tab.="<td align=center style=width:20px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Excel' onclick=\"detailExcel('".$notr."','".$tipex[$notr]."','event','excel');\" ></td>";

				$tab.="</tr>";
			}
		}

        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {$totrows = 1;}
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {$sel = ($page == $er - 1) ? 'selected' : '';$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";}
        $footd = "";
        $footd.="</tr><tr><td colspan=14 align=center>";
        if ($page == '0') {$footd.="<button class=mybutton disabled=true>Prev</button>";} else {$footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";}
        $footd.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {$footd.="<button class=mybutton disabled=true>Next</button>";} else {$footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";}
        $footd.="</td></tr>";

        echo $tab . "####" . $footd;

	break;
	case 'showupload':
		$tab="";
		$tab.="
		<table border=0 >
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>
				<td id='notranupload'>". $param['notransaksi']."</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td style=vertical-align:top>Status</td>
				<td style=vertical-align:top>:</td>
				<td>
					<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
					<p id='status'></p>
					<p id='loaded_n_total'></p>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('".$param['notransaksi']."')\">Submit</button>
				</td>
			</tr>
		</table>
		";

		$tab.="
			<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=30px colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		";

		echo $tab;
	break;
	
	case 'submitfile':
		try {
		$owlPDO->beginTransaction();
		$data = $_POST;
		if(count($data)==0){
			$data = $_GET;			
		}
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				if (!file_exists($path)){
					mkdir($path, 0777, true);
				}
				
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $_FILES['file']['name'];
				#cek duplikasi nama file
				$str="select * from ".$dbname.".listfileupload where namafile = '".$filename."'";
				$res=fetchData($str);
				if(count($res)>0){
					throw new PDOException("Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.");
				}
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
					values ('".$param['notransaksi']."','".$filename."','".$filetype."','PTA','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					$owlPDO->exec($str);
					file_put_contents($path.$filename,$file_tmpname);
				}else{
					throw new PDOException("Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
				if (!file_exists($path.$filename)) {
					throw new PDOException("Upload file gagal.");
				}
			}
		}else{
			throw new PDOException("Upload file gagal.");
		}
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();
	}
	break;
	case 'loadfiles':
		$str= "select * from ".$dbname.".bgt_budget where notransaksi = '".$param['notransaksi']."' and pta='PTA'";
		$res= fetchData($str);
		$jurnal = $res[0]['statuspta'];
		
		$no = 0;
		$tab= "";
		$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
		$res= fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
					</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
				if($jurnal!='1' or $jurnal!='9'){
					$tab.="<td align=center width=30px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
					
					$tab.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" ></td>";
				}else{
					$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
				}
				$tab.="</tr>";
			}
		}
		echo $tab;
	break;
	case 'deletefile':
		$str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$param['namafile']."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$param['namafile'];
			#sementara tidak boleh ada unlink
			//unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'viewfile':
		$tab="";
		$str= "select * from ".$dbname.".listfileupload where id = '".$param['idfile']."'";
		$res= fetchData($str);
		if($res[0]['formaticon']=='.xls' or $res[0]['formaticon']=='.xlsx' or $res[0]['formaticon']=='.doc' or $res[0]['formaticon']=='.docx'){
			exit("Warning: Tidak bisa ditampilkan, silahkan download.");
		}
		
		if($res[0]['formaticon']=='.pdf'){
			$tab.="<embed src='".$path.$res[0]['namafile']."' style='width:100%;height:97%;' type='application/pdf'>";
		}else{			
			$tab.="<img src='".$path.$res[0]['namafile']."'>";
		}
		
		echo $tab;
	break;	
	
	case'fillfield':
		$n="";
		if($param['sumber']=='NONKAPITAL'){			
			$str="select * from ".$dbname.".bgt_budget where notransaksi='".$param['notransaksi']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$tipebudget = $bar['tipebudget'];
				$kodebudget = $bar['kodebudget'];
				$notransaksi = $bar['notransaksi'];
				$tahun = $bar['tahunbudget'];
				$unit = substr($bar['kodeorg'],0,4);
				$ket = $bar['keterangan2'];
				$tanggal = tanggalnormal($bar['tanggal']);
			}
			if($tipebudget=='ESTATE' and $kodebudget!='UMUM'){
				$tipepta='ESTATE';
			}elseif($tipebudget=='ESTATE' and $kodebudget=='UMUM'){
				$tipepta='UMUM';
			}elseif($tipebudget=='MILL' and $kodebudget!='UMUM'){
				$tipepta='MILL';
			}elseif($tipebudget=='MILL' and $kodebudget=='UMUM'){
				$tipepta='UMUM';
			}elseif($tipebudget=='TRK'){
				$tipepta='TRK';
			}elseif($tipebudget=='WS'){
				$tipepta='WS';
			}else{
				$tipepta='UMUM';
			}
			$n = $notransaksi."##".$tahun."##".$unit."##".$tipebudget."##".$tipepta."##".$ket."##".$tanggal;
		}else{
			$str="select * from ".$dbname.".bgt_kapital where notransaksi='".$param['notransaksi']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$kodebudget = $bar['kodebudget'];
				$notransaksi = $bar['notransaksi'];
				$tahun = $bar['tahunbudget'];
				$unit = substr($bar['kodeunit'],0,4);
				$ket = $bar['keterangan2'];
				$tanggal = tanggalnormal($bar['tanggal']);
			}
			
			if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
				$tipebudget='ESTATE';
			}else if($_SESSION['empl']['tipelokasitugas']=='PABRIK'){
				$tipebudget='MILL';
			}else{
				$tipebudget=$_SESSION['empl']['tipelokasitugas'];
			}
			$tipepta='KAPITAL';
			$n = $notransaksi."##".$tahun."##".$unit."##".$tipebudget."##".$tipepta."##".$ket."##".$tanggal;
		}
		
		echo $n;
	break;
	
	
	case 'showupload':
		$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
		$optjns['realkeg']='Realisasi Kegiatan';
		
		$tab="";
		$tab.="<fieldset><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>".$_SESSION['lang']['jenisbiaya']."</td>
				<td>:</td>
				<td>
					<select id='jenisupload'>
						<option value=".$param['jenisupload'].">".$optjns[$param['jenisupload']]."</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td></td>
				<td>
					<input type='file' onclick=enabletombol(); name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('".$notransaksi."','".$param['jenisupload']."')\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>
			<p />";

		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=20px>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>".$_SESSION['lang']['jenisbiaya']."</td>
					<td align='center'>Filename</td>
					<td align='center' colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";

		echo $tab;
	break;
	case 'submitfile':
	
	$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
	$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' and namafile='".$_FILES['file']['name']."'";
	$res=fetchData($str);
	if(!empty($res)){
		foreach($res as $bar){
			$jn=".";
			if($bar['jenisbiaya']!=$param['jenisupload']){
				$jn=" dengan jenis biaya :\n";
				$jn.=$optjns[$bar['jenisbiaya']]."\n";
			}
		}
		exit("Warning : Nama file sudah ada".$jn."");
	}
	
	$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' and namafile='".$_FILES['file']['name']."' and jenisbiaya='".$param['jenisupload']."'";
	$res=fetchData($str);
	if(!empty($res)){
		exit("Warning : Nama file sudah ada.");
	}
	
	$tgl = date("YmdHis");
	$his = date("His");
	$data = $_POST;
	if($data['fileupload']!=''){
		if($_FILES['file']['error']==0){
			if(!preg_match("/^[a-zA-Z0-9 .]*$/",$_FILES['file']['name'])){
				//exit("Warning : Nama file hanya boleh ngandung Huruf, angka, spasi dan titik.");
			}
			if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
				$jns="real";
			}else{
				$jns="klaim";
			}
			
			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$filename = $_FILES['file']['name'];
			$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
			if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
				/*if($_FILES['file']['size'] <= 250000){*/
					$str = "insert into ".$dbname.".file_pjdinas values ('','".$notransaksi."','".$param['jenisupload']."','".$jns."','".$filename."','".$filetype."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
					try{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				/*}else{
					exit("warning : Ukuran file upload maksimal 250kb");
				}*/
			}else{
				exit("Warning : Format file upload harus *.jpg, *.jpeg, *.png, *.pdf, *.xls, *.xlsx, *.doc, *.docx");
			}
		}
	}
	break;
	case 'loadfiles':
	$no = 0; $wh="";
	if($param['jenisupload']!=''){
		$wh="and jenisbiaya='".$param['jenisupload']."'";
		$tab.="<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=20px>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>".$_SESSION['lang']['jenisbiaya']."</td>
					<td align='center'>Filename</td>
					<td align='center' colspan=2>Action</td>
				</tr>
				</thead>";
	}
	if($param['jenis']!=''){
		$jns="and jenis ='".$param['jenis']."'";
	}else{		
		if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
			$jns="and jenis ='real'";
		}else{
			$jns="and jenis ='klaim'";
		}
	}
	
	
	$statuspjd=makeOption($dbname,'sdm_pjdinasht','notransaksi,statusrealisasi',"notransaksi = '".$notransaksi."'");
	
	$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
	$optjns['realkeg']='Realisasi Kegiatan';
	
	$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' ".$wh." ".$jns."";
	$res=fetchData($str);
	if(empty($res)){
		$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		foreach($res as $key=>$val){
			$no++;
			$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
			$icon=seticonfile($val['formaticon']);
			$tab.="<td style='text-align:center'>
					<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
				</td>";
			$nfile = $val['namafile'];
			$tab.="<td style='text-align:left'>".$optjns[$val['jenisbiaya']]."</td>";
			$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>";
			
			$tab.="<td align=center width=20px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a></td>";
			
			if($val['updateby'] == $_SESSION['standard']['userid'] and $statuspjd[$notransaksi]!='1'){				
				$tab.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."','".$val['jenisbiaya']."');\" ></td>";
			}else{
				$tab.="<td></td>";
			}
			$tab."</tr>";
		}
	}
	echo $tab;
	break;
	case'viewfile':
		$tab="";
		$tab.="<img src='".$path.$param['namafile']."' style='width:100%;height:100%;'>";
	echo $tab;
	break;
	
	case 'deletefile':
		$str="delete from ".$dbname.".file_pjdinas where notransaksi='".$notransaksi."' and namafile='".$param['namafile']."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$param['namafile'];
			unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'previewdata':
		if($jenis=='pdf'){				
			$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:8\"";
		}elseif($jenis=='excel'){
			$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
		}else{
			$style="cellpadding=5 cellspacing=1 border=0 class=sortable";
			$hr="<hr>";
		}
		
		$tabx.="<br><table ".$style.">
		<thead><tr class=rowheader>";
		$tabx.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['nama']."</th>
			<th align=center >".$_SESSION['lang']['jabatan']."</th>
			<th align=center >".$_SESSION['lang']['status']."</th>
			<th align=center >".$_SESSION['lang']['tanggal']."</th>
			<th align=center >".$_SESSION['lang']['catatan']."</th>
		</tr>
		</thead><tbody>";
		$tabx.="<tr class=rowcontent>";
		$tabx.="<td align=left colspan=6 style=color:blue;>Persetujuan</td>";
		$tabx.="</tr>";
		
		$str="select distinct * from ".$dbname.".approval where  notransaksi = '".$notransaksi."' and jenispersetujuan='PTA' order by level";
		$res=fetchdata($str);$no=0;
		$namajab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		foreach($res as $bar){
			$nmkary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid ='".$bar['karyawanid']."'");
			$kodejab = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid ='".$bar['karyawanid']."'");

			$no++;
			$tabx.="<tr class=rowcontent>";
			$tabx.="<td align=center>".$no."</td>";
			$tabx.="<td align=left>".$nmkary[$bar['karyawanid']]."</td>";
			$tabx.="<td align=left>".$namajab[$kodejab[$bar['karyawanid']]]."</td>";
			if($bar['status']==0){
				$tabx.="<td align=left>".$_SESSION['lang']['wait_approval']."</td>";				
			}else{				
				$tabx.="<td align=left>".$arrHsl[$bar['status']]."</td>";
			}
			if($bar['tanggal']=='0000-00-00 00:00:00'){
				$tabx.="<td align=left></td>";
			}else{				
				$tabx.="<td align=left>".$bar['tanggal']."</td>";
			}
			$tabx.="<td align=left>".$bar['komentar']."</td>";
		}
		
		$str="select distinct * from ".$dbname.".approval_return where  notransaksi = '".$notransaksi."' and jenispersetujuan='PTA' order by tanggal desc, level";
		$res=fetchdata($str);$no=0;
		if(count($res)>0){
			$tabx.="</tr>";
			$tabx.="<tr class=rowcontent>";
			$tabx.="<td align=left colspan=6 style=color:blue;>History Approval</td>";
			$tabx.="</tr>";
			
			$namajab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
			foreach($res as $bar){
				$nmkary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid ='".$bar['karyawanid']."'");
				$kodejab = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid ='".$bar['karyawanid']."'");

				$no++;
				$tabx.="<tr class=rowcontent>";
				$tabx.="<td align=center>".$no."</td>";
				$tabx.="<td align=left>".$nmkary[$bar['karyawanid']]."</td>";
				$tabx.="<td align=left>".$namajab[$kodejab[$bar['karyawanid']]]."</td>";
				if($bar['status']==0){
					$tabx.="<td align=left>".$_SESSION['lang']['wait_approval']."</td>";				
				}else{				
					$tabx.="<td align=left>".$arrHsl[$bar['status']]."</td>";
				}
				if($bar['tanggal']=='0000-00-00 00:00:00'){
					$tabx.="<td align=left></td>";
				}else{				
					$tabx.="<td align=left>".$bar['tanggal']."</td>";
				}
				$tabx.="<td align=left>".$bar['komentar']."</td>";
			}
		}
		// echo $tipe;exit("Error:Aszzz");
		$tabx.="</tbody></table>".$hr."<br>"; 
		$tabx.="<div style='page-break-before: always;'></div>";
		switch($tipe){
			case'KAPITAL':
				if($jenis=='pdf'){				
					$style="cellpadding=1 cellspacing=0 border=0 class=sortable style=\"font-family:sans-serif;font-size:10\"";
				}elseif($jenis=='excel'){
					$style="cellpadding=1 cellspacing=0 border=0 class=sortable";
				}else{
					$style="cellpadding=5 cellspacing=1 border=0  width=100%";
				}
				$str="select * from ".$dbname.".bgt_kapital where notransaksi = '".$notransaksi."' and pta='PTA'";
				$res=fetchdata($str)[0];
				$catat=$res['keterangan2'];
				$optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".substr($res['kodeunit'],0,4)."'");
				$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
				$tab.="
				<table ".$style." width=100%>
					<tr>
						<td style=font-weight:bold;font-family:sans-serif;text-decoration:underline;text-align:center; colspan=7>PERMINTAAN TAMBAHAN ANGGARAN (PTA)</td>
					</tr>
					<tr>
						<td width=10%>".$_SESSION['lang']['pt']."</td>
						<td width=1%>:</td>
						<td width=25%>".$nmorg[$optpt[substr($res['kodeunit'],0,4)]]."</td>
						<td width=30%></td>
						<td width=10%>".$_SESSION['lang']['nomor']."</td>
						<td width=1%>:</td>
						<td width=25%>".$res['notransaksi']."</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['unit']."</td>
						<td>:</td>
						<td>".$nmorg[substr($res['kodeunit'],0,4)]."</td>
						<td></td>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>".tanggalnormal($res['tanggal'])."</td>
					</tr>
				</table>";
				
				if($jenis=='pdf'){				
					$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:8\"";
				}elseif($jenis=='excel'){
					$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
				}else{
					$style="cellpadding=5 cellspacing=1 border=0 class=sortable";
				}
				
				
				$tab.="<table ".$style." width=100%>
				<thead><tr class=rowheader>";
				$tab.="<th align=center width=20px>No</th>
					<th align=center >".$_SESSION['lang']['tahun']."</th>
					<th align=center >".$_SESSION['lang']['unit']."</th>
					<th align=center >".$_SESSION['lang']['lokasi']."</th>
					<th align=center >".$_SESSION['lang']['jnsKapital']."</th>
					<th align=center >".$_SESSION['lang']['keterangan']."</th>
					<th align=center >".$_SESSION['lang']['aruskas']."</th>
					<th align=center >".$_SESSION['lang']['barang']."</th>
					<th align=center >".$_SESSION['lang']['jumlah']."</th>
					<th align=center >".$_SESSION['lang']['harga']."</th>
					<th align=center >".$_SESSION['lang']['rupiah']."</th>
				</tr>
				</thead><tbody>";
				$str="select * from ".$dbname.".bgt_kapital where  notransaksi = '".$notransaksi."' and pta='PTA' order by  kunci";
				
				$res=fetchdata($str);$no=0;
				foreach($res as $bar){
					$no++;
					$opttipekar = makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$bar['kodebudget']."'");
					$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
					$optakun = makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe',"kodetipe ='".$bar['jeniskapital']."'");
					
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td align=center>".$bar['tahunbudget']."</td>";
					$tab.="<td align=left>".$bar['kodeunit']."</td>";
					$tab.="<td align=left>".getNamaOrg($bar['lokasi'])."</td>";
					$tab.="<td align=left>".$optakun[$bar['jeniskapital']]."</td>";
					$tab.="<td align=left>".$bar['keterangan']."</td>";
					$tab.="<td align=left>".getNamaAruskas($bar['aruskas'])."</td>";
					$tab.="<td align=left>".getNamaBrg($bar['kodebarang'])."</td>";
					$tab.="<td align=right>".numb_format($bar['jumlah'])."</td>";
					$tab.="<td align=right>".numb_format($bar['hargasatuan'])."</td>";
					$tab.="<td align=right>".numb_format($bar['hargatotal'])."</td>";
					$ttlrp+=$bar['hargatotal'];	
					$tab.="</tr>";
					$catat=$bar['keterangan2'];
				}
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center colspan=10>T O T A L</td>";
				$tab.="<td align=right>".numb_format($ttlrp)."</td>";
				$tab.="</tbody></table>"; 
				
				$tab.="<br><label style=font-family:sans-serif;font-size:10;>Catatan :</label>";
				$tab.="<br><label style=font-family:sans-serif;font-size:10;>".nl2br($catat)."</label>";
				
				
				$tab4.="<br><br><label style=font-family:sans-serif;font-size:10;>File Pendukung</label></br>
						<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
							<thead>
							<tr class=rowheader>
								<td align='center' width=30px>No.</td>
								<td align='center' width=50px>File Type</td>
								<td align='center'>Filename</td>
								<td align='center' width=30px>Action</td>
							</tr>
							</thead>
							<tbody>
					";
					
					$no=0;
					$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
					$res= fetchData($str);
					if(empty($res)){
						$tab4.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
					}else{
						foreach($res as $key=>$val){
							$no++;
							$tab4.="<tr class=rowcontent>
									<td style='text-align:center'>".$no."</td>";
							$icon=seticonfile($val['formaticon']);
							$tab4.="<td style='text-align:center'>
									<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
								</td>";
							$tab4.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
							$tab4.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
							$tab4.="</tr>";
						}
					}
					$tab4.="</tbody>
						</table>";
				
				if($jenis=='pdf'){		
					$dompdf = new Dompdf();
					$dompdf->load_html($tabx.$tab.$tab1.$tab2);
					$dompdf->setPaper('A4', 'landscape');
					$dompdf->render();
					$canvas = $dompdf->get_canvas();
					#$font = Font_Metrics::get_font("helvetica", "bold");
					$canvas->page_text(16, 800, "Page: {PAGE_NUM} of {PAGE_COUNT}",'', 8, array(0,0,0));
					$dompdf->stream("pta",array("Attachment"=>0));
				}elseif($jenis=='excel'){
					$nop = "pta.xls";
					$xls = new HtmlExcel();
					$xls->setCss($css);
					$xls->addSheet("approval", $tabx);
					$xls->addSheet("pta_kapital", $tab);
					$xls->headers($nop);
					echo $xls->buildFile();
				}else{
					echo $tabx.$tab.$tab1.$tab2.$tab4;
				}
			break;
			case'NONKAPITAL':
				if($jenis=='pdf'){				
					$style="cellpadding=1 cellspacing=0 border=0 class=sortable style=\"font-family:sans-serif;font-size:10\"";
				}elseif($jenis=='excel'){
					$style="cellpadding=1 cellspacing=0 border=0 class=sortable";
				}else{
					$style="cellpadding=5 cellspacing=1 border=0  width=100%";
				}
				$str="select * from ".$dbname.".bgt_budget where notransaksi = '".$notransaksi."' and pta='PTA'";
				$res=fetchdata($str)[0];
				$catat=$res['keterangan2'];
				$optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".substr($res['kodeorg'],0,4)."'");
				$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
				$tab.="
				<table ".$style." width=100%>
					<tr>
						<td style=font-weight:bold;font-family:sans-serif;text-decoration:underline;text-align:center; colspan=7>PERMINTAAN TAMBAHAN ANGGARAN (PTA)</td>
					</tr>
					<tr>
						<td width=10%>".$_SESSION['lang']['pt']."</td>
						<td width=1%>:</td>
						<td width=25%>".$nmorg[$optpt[substr($res['kodeorg'],0,4)]]."</td>
						<td width=30%></td>
						<td width=10%>".$_SESSION['lang']['nomor']."</td>
						<td width=1%>:</td>
						<td width=25%>".$res['notransaksi']."</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['unit']."</td>
						<td>:</td>
						<td>".$nmorg[substr($res['kodeorg'],0,4)]."</td>
						<td></td>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>".tanggalnormal($res['tanggal'])."</td>
					</tr>
				</table>";
				
				if($jenis=='pdf'){				
					$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:8\"";
				}elseif($jenis=='excel'){
					$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
				}else{
					$style="cellpadding=5 cellspacing=1 border=0 class=sortable";
				}
				
				
				$tab.="<table ".$style." width=100%>
				<thead><tr class=rowheader>";
				$tab.="<th align=center width=20px>No</th>
					<th align=center >".$_SESSION['lang']['tahun']."</th>
					<th align=center >".$_SESSION['lang']['divisi']." / ".$_SESSION['lang']['station']."</th>
					<th align=center >".$_SESSION['lang']['noakun']."</th>
					<th align=center >".$_SESSION['lang']['kodebudget']."</th>
					<th align=center >".$_SESSION['lang']['jumlah']."</th>
					<th align=center >".$_SESSION['lang']['rupiah']."</th>
					<th align=center >".$_SESSION['lang']['keterangan']."</th>
				</tr>
				</thead><tbody>";
				$data=array();
				$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where notransaksi = '".$notransaksi."' and pta='PTA' order by divisi, kunci";
				$res=fetchdata($str);$no=0;
				foreach($res as $bar){
					$data[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]=$bar['tahunbudget'];
					$rp[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]+=$bar['rupiah'];
					$jlh[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]+=$bar['jumlah'];
					$ket[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]=$bar['keterangan'];
				}

				$nmakun= makeOption($dbname,'keu_5akun','noakun,namaakun');
				foreach($data as $divisi => $vkeg){
					foreach($vkeg as $keg => $vkdbgt){
						foreach($vkdbgt as $kdbgt => $thn){
							$no++;
							$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$divisi."'");
							$optkeg= makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$keg."'");
							$nmbgt = makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$kdbgt."'");

							$tab.="<tr class=rowcontent style=vertical-align:top;>";
							$tab.="<td align=center>".$no."</td>";
							$tab.="<td align=center width=50px>".$thn."</td>";
							$tab.="<td align=left>".$divisi." - ".$nmorg[$divisi]."</td>";
							$tab.="<td align=left>".$keg." - ".$nmakun[$keg]."</td>";					
							$tab.="<td align=left>".$nmbgt[$kdbgt]."</td>";					
							$tab.="<td align=right width=100px>".numb_format($jlh[$divisi][$keg][$kdbgt])."</td>";
							$tab.="<td align=right width=150px>".numb_format($rp[$divisi][$keg][$kdbgt])."</td>";
							$tab.="<td align=left>".$ket[$divisi][$keg][$kdbgt]."</td>";
							
							$ttlrp+=$rp[$divisi][$keg][$kdbgt];	
							$tab.="</tr>";
						}
					}			
				}
					
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center colspan=6>T O T A L</td>";
				$tab.="<td align=right>".numb_format($ttlrp)."</td><td></td>";
				$tab.="</tr>";
				$tab.="</tbody></table>"; 
				
				$tab.="<br><label style=font-family:sans-serif;font-size:10;>Catatan :</label>";
				$tab.="<br><label style=font-family:sans-serif;font-size:10;>".nl2br($catat)."</label>";
				
				$tab.="<div style='page-break-before: always;'></div>";
				
				
				$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where notransaksi = '".$notransaksi."' and pta='PTA' and (kodebudget like 'SDM%' or kodebudget like 'EXPL%') order by divisi, kunci";
				$res=fetchdata($str);$no=0;
				if(count($res)>0){
					$tab1.="<br><label style=font-family:sans-serif;font-size:10;>SDM</label></br>";
					$tab1.="<table ".$style." width=100%>
					<thead><tr class=rowheader>";
					$tab1.="<th align=center width=20px>No</th>
						<th align=center >".$_SESSION['lang']['tahun']."</th>
						<th align=center >".$_SESSION['lang']['divisi']." / ".$_SESSION['lang']['station']."</th>
						<th align=center >".$_SESSION['lang']['blok']." / ".$_SESSION['lang']['mesin']."</th>
						<th align=center width=50px>".$_SESSION['lang']['tahuntanam']."</th>
						<th align=center >".$_SESSION['lang']['tipekaryawan']."</th>
						<th align=center >".$_SESSION['lang']['kegiatan']."</th>
						<th align=center >".$_SESSION['lang']['noakun']."</th>
						<th align=center >".$_SESSION['lang']['rotasi']."</th>
						<th align=center >".$_SESSION['lang']['volume']."</th>
						<th align=center >".$_SESSION['lang']['satuan']."</th>
						<th align=center >".$_SESSION['lang']['rupiah']."</th>
						<th align=center >".$_SESSION['lang']['jumlah']."</th>
						<th align=center >".$_SESSION['lang']['satuan']."</th>
					</tr>
					</thead><tbody>";
					$ttlvol=$ttlrp=$ttljlh=0;	
					foreach($res as $bar){
						$no++;
						$optthntnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
						$opttipekar = makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$bar['kodebudget']."'");
						$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
						$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
						
						$tab1.="<tr class=rowcontent>";
						$tab1.="<td align=center>".$no."</td>";
						$tab1.="<td align=center>".$bar['tahunbudget']."</td>";
						$tab1.="<td align=left>".getNamaOrg($bar['divisi'])."</td>";
						$tab1.="<td align=left>".getNamaOrg($bar['kodeorg'])."</td>";
						$tab1.="<td align=center>".$optthntnm[$bar['kodeorg']]."</td>";
						$tab1.="<td align=left>".$bar['kodebudget']." - ".$opttipekar[$bar['kodebudget']]."</td>";
						$tab1.="<td align=left>".$bar['kegiatan']." - ".$optkeg[$bar['kegiatan']]."</td>";
						$tab1.="<td align=left>".$bar['noakun']." - ".$optakun[$bar['noakun']]."</td>";
						$tab1.="<td align=center>".$bar['rotasi']."</td>";
						$tab1.="<td align=right>".numb_format($bar['volume'],2)."</td>";
						$tab1.="<td align=center>".$bar['satuanv']."</td>";
						$tab1.="<td align=right>".numb_format($bar['rupiah'])."</td>";
						$tab1.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
						$tab1.="<td align=center>".$bar['satuanj']."</td>";
						
						$ttlvol+=$bar['volume'];	
						$ttlrp+=$bar['rupiah'];	
						$ttljlh+=$bar['jumlah'];	
						$tab.="</tr>";
					}
					$tab1.="<tr class=rowcontent>";
					$tab1.="<td align=center colspan=9>T O T A L</td>";
					$tab1.="<td align=right>".numb_format($ttlvol,2)."</td>";
					$tab1.="<td align=center></td>";
					$tab1.="<td align=right>".numb_format($ttlrp)."</td>";
					$tab1.="<td align=right>".numb_format($ttljlh,2)."</td>";
					$tab1.="<td align=center></td>";
					
					$tab1.="</tr>";
					$tab1.="</tbody></table>";
					
					$tab1.="<div style='page-break-before: always;'></div>";
				}
				
				
				$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where  notransaksi = '".$notransaksi."' and pta='PTA' and kodebudget like 'M-%' order by divisi, kunci";
				$res=fetchdata($str);$no=0;
				if(count($res)>0){
					$tab2.="<br><label style=font-family:sans-serif;font-size:10;>Material</label></br>";
					$tab2.="<table ".$style." width=100%>
					<thead><tr class=rowheader>";
					$tab2.="<th align=center width=20px>No</th>
						<th align=center >".$_SESSION['lang']['tahun']."</th>
						<th align=center >".$_SESSION['lang']['divisi']." / ".$_SESSION['lang']['station']."</th>
						<th align=center >".$_SESSION['lang']['blok']." / ".$_SESSION['lang']['mesin']."</th>
						<th align=center width=50px>".$_SESSION['lang']['tahuntanam']."</th>
						<th align=center >".$_SESSION['lang']['namabarang']."</th>
						<th align=center >".$_SESSION['lang']['kegiatan']."</th>
						<th align=center >".$_SESSION['lang']['noakun']."</th>
						<th align=center >".$_SESSION['lang']['rotasi']."</th>
						<th align=center >".$_SESSION['lang']['volume']."</th>
						<th align=center >".$_SESSION['lang']['satuan']."</th>
						<th align=center >".$_SESSION['lang']['rupiah']."</th>
						<th align=center >".$_SESSION['lang']['jumlah']."</th>
						<th align=center >".$_SESSION['lang']['satuan']."</th>
						
					</tr>
					</thead><tbody>";
					$ttlvol=$ttlrp=$ttljlh=0;	
					foreach($res as $bar){
						$no++;
						$optthntnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
						$opttipekar = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
						$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
						$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
						
						$tab2.="<tr class=rowcontent>";
						$tab2.="<td align=center>".$no."</td>";
						$tab2.="<td align=center>".$bar['tahunbudget']."</td>";
						$tab2.="<td align=left>".getNamaOrg($bar['divisi'])."</td>";
						$tab2.="<td align=left>".getNamaOrg($bar['kodeorg'])."</td>";
						$tab2.="<td align=center>".$optthntnm[$bar['kodeorg']]."</td>";
						$tab2.="<td align=left>".$bar['kodebarang']." - ".$opttipekar[$bar['kodebarang']]."</td>";
						$tab2.="<td align=left>".$bar['kegiatan']." - ".$optkeg[$bar['kegiatan']]."</td>";
						$tab2.="<td align=left>".$bar['noakun']." - ".$optakun[$bar['noakun']]."</td>";
						$tab2.="<td align=center>".$bar['rotasi']."</td>";
						$tab2.="<td align=right>".numb_format($bar['volume'],2)."</td>";
						$tab2.="<td align=center>".$bar['satuanv']."</td>";
						$tab2.="<td align=right>".numb_format($bar['rupiah'])."</td>";
						$tab2.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
						$tab2.="<td align=center>".$bar['satuanj']."</td>";

						$ttlvol+=$bar['volume'];	
						$ttlrp+=$bar['rupiah'];	
						$ttljlh+=$bar['jumlah'];	
						$tab2.="</tr>";
					}
					$tab2.="<tr class=rowcontent>";
					$tab2.="<td align=center colspan=9>T O T A L</td>";
					$tab2.="<td align=right>".numb_format($ttlvol,2)."</td>";
					$tab2.="<td align=center></td>";
					$tab2.="<td align=right>".numb_format($ttlrp)."</td>";
					$tab2.="<td align=right>".numb_format($ttljlh,2)."</td>";
					$tab2.="<td align=center></td>";
					
					$tab2.="</tr>";
					$tab2.="</tbody></table>";
					
					$tab2.="<div style='page-break-before: always;'></div>";
				}
				
				$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where  notransaksi = '".$notransaksi."' and pta='PTA' and kodebudget = 'UMUM' order by divisi, kunci";
				$res=fetchdata($str);$no=0;
				if(count($res)>0){
					$tab3.="<br><label style=font-family:sans-serif;font-size:10;>UMUM</label></br>";
					$tab3.="<table ".$style." width=100%>
					<thead><tr class=rowheader>";
					$tab3.="<th align=center width=20px>No</th>
						<th align=center >".$_SESSION['lang']['noakun']."</th>
						<th align=center >".$_SESSION['lang']['namaakun']."</th>
						<th align=center >".$_SESSION['lang']['noaruskas']."</th>
						<th align=center >".$_SESSION['lang']['namaaruskas']."</th>
						<th align=center >".$_SESSION['lang']['kodebarang']."</th>
						<th align=center >".$_SESSION['lang']['namabarang']."</th>
						<th align=center >".$_SESSION['lang']['satuan']."</th>
						<th align=center >".$_SESSION['lang']['jumlah']."</th>
						<th align=center >".$_SESSION['lang']['rupiah']."</th>
					</tr>
					</thead><tbody>";
					$ttlvol=$ttlrp=$ttljlh=0;	
					foreach($res as $bar){
						$no++;
						$optthntnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
						$opttipekar = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
						$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
						$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
						
						$tab3.="<tr class=rowcontent>";
						$tab3.="<td align=center>".$no."</td>";
						$tab3.="<td align=center>".$bar['noakun']."</td>";
						$tab3.="<td align=left>".$optakun[$bar['noakun']]."</td>";
						$tab3.="<td align=center>".$bar['aruskas']."</td>";
						$tab3.="<td align=left>".getNamaAruskas($bar['aruskas'])."</td>";
						$tab3.="<td align=center>".$bar['kodebarang']."</td>";
						$tab3.="<td align=left>".getNamaBrg($bar['kodebarang'])."</td>";
						$tab3.="<td align=center>".$bar['satuanj']."</td>";
						$tab3.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
						$tab3.="<td align=right>".numb_format($bar['rupiah'])."</td>";

						$ttlvol+=$bar['volume'];	
						$ttlrp+=$bar['rupiah'];	
						$ttljlh+=$bar['jumlah'];	
						$tab3.="</tr>";
					}
					$tab3.="<tr class=rowcontent>";
					$tab3.="<td align=center colspan=9>T O T A L</td>";
					
					$tab3.="<td align=right>".numb_format($ttlrp)."</td>";
					
					
					$tab3.="</tr>";
					$tab3.="</tbody></table>";
					
					
				}
				$tab4.="<br><label style=font-family:sans-serif;font-size:10;>File Pendukung</label></br>
					<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
						<thead>
						<tr class=rowheader>
							<td align='center' width=30px>No.</td>
							<td align='center' width=50px>File Type</td>
							<td align='center'>Filename</td>
							<td align='center' width=30px>Action</td>
						</tr>
						</thead>
						<tbody>
				";
				$no=0;
				$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
				$res= fetchData($str);
				if(empty($res)){
					$tab4.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
				}else{
					foreach($res as $key=>$val){
						$no++;
						$tab4.="<tr class=rowcontent>
								<td style='text-align:center'>".$no."</td>";
						$icon=seticonfile($val['formaticon']);
						$tab4.="<td style='text-align:center'>
								<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
							</td>";
						$tab4.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
						$tab4.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
						$tab4.="</tr>";
					}
				}
				$tab4.="</tbody>
					</table>";
				
				
				
				if($jenis=='pdf'){		
					$dompdf = new Dompdf();
					$dompdf->load_html($tabx.$tab.$tab1.$tab2.$tab3);
					$dompdf->setPaper('A4', 'landscape');
					$dompdf->render();
					$canvas = $dompdf->get_canvas();
					#$font = Font_Metrics::get_font("helvetica", "bold");
					$canvas->page_text(16, 800, "Page: {PAGE_NUM} of {PAGE_COUNT}",'', 8, array(0,0,0));
					$dompdf->stream("pta",array("Attachment"=>0));
				}elseif($jenis=='excel'){
					$nop = "pta.xls";
					$xls = new HtmlExcel();
					$xls->setCss($css);
					$xls->addSheet("approval", $tabx);
					$xls->addSheet("rekap_pta", $tab);
					$xls->addSheet("detail_sdm", $tab1);
					$xls->addSheet("detail_mat", $tab2);
					$xls->addSheet("detail_umm", $tab3);
					$xls->headers($nop);
					echo $xls->buildFile();
				}else{
					
					echo $tabx.$tab.$tab1.$tab2.$tab3.$tab4;
				}
			break;
			
			default:
				if($jenis=='pdf'){				
					$style="cellpadding=1 cellspacing=0 border=0 class=sortable style=\"font-family:sans-serif;font-size:10\"";
				}elseif($jenis=='excel'){
					$style="cellpadding=1 cellspacing=0 border=0 class=sortable";
				}else{
					$style="cellpadding=5 cellspacing=1 border=0  width=100%";
				}
				$str="select * from ".$dbname.".bgt_budget where notransaksi = '".$notransaksi."' and pta='PTA'";
				$res=fetchdata($str)[0];
				$catat=$res['keterangan2'];
				$optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".substr($res['kodeorg'],0,4)."'");
				$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
				$tab.="
				<table ".$style." width=100%>
					<tr>
						<td style=font-weight:bold;font-family:sans-serif;text-decoration:underline;text-align:center; colspan=7>PERMINTAAN TAMBAHAN ANGGARAN (PTA)</td>
					</tr>
					<tr>
						<td width=10%>".$_SESSION['lang']['pt']."</td>
						<td width=1%>:</td>
						<td width=25%>".$nmorg[$optpt[substr($res['kodeorg'],0,4)]]."</td>
						<td width=30%></td>
						<td width=10%>".$_SESSION['lang']['nomor']."</td>
						<td width=1%>:</td>
						<td width=25%>".$res['notransaksi']."</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['unit']."</td>
						<td>:</td>
						<td>".$nmorg[substr($res['kodeorg'],0,4)]."</td>
						<td></td>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>".tanggalnormal($res['tanggal'])."</td>
					</tr>
				</table>";
				
				if($jenis=='pdf'){				
					$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:8\"";
				}elseif($jenis=='excel'){
					$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
				}else{
					$style="cellpadding=5 cellspacing=1 border=0 class=sortable";
				}
				
				
				$tab.="<table ".$style." width=100%>
				<thead><tr class=rowheader>";
				$tab.="<th align=center width=20px>No</th>
					<th align=center >".$_SESSION['lang']['tahun']."</th>
					<th align=center >".$_SESSION['lang']['divisi']." / ".$_SESSION['lang']['station']."</th>
					<th align=center >".$_SESSION['lang']['noakun']."</th>
					<th align=center >".$_SESSION['lang']['kodebudget']."</th>
					<th align=center >".$_SESSION['lang']['jumlah']."</th>
					<th align=center >".$_SESSION['lang']['rupiah']."</th>
					<th align=center >".$_SESSION['lang']['keterangan']."</th>
				</tr>
				</thead><tbody>";
				$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where notransaksi = '".$notransaksi."' and pta='PTA' order by divisi, kunci";
				$res=fetchdata($str);$no=0;
				foreach($res as $bar){
					$data[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]=$bar['tahunbudget'];
					$rp[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]+=$bar['rupiah'];
					$jlh[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]+=$bar['jumlah'];
					$ket[$bar['divisi']][$bar['noakun']][$bar['kodebudget']]=$bar['keterangan'];
				}

				$nmakun= makeOption($dbname,'keu_5akun','noakun,namaakun');
				foreach($data as $divisi => $vkeg){
					foreach($vkeg as $keg => $vkdbgt){
						foreach($vkdbgt as $kdbgt => $thn){
							$no++;
							$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$divisi."'");
							$optkeg= makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$keg."'");
							$nmbgt = makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$kdbgt."'");

							$tab.="<tr class=rowcontent style=vertical-align:top;>";
							$tab.="<td align=center>".$no."</td>";
							$tab.="<td align=center width=50px>".$thn."</td>";
							$tab.="<td align=left>".$divisi." - ".$nmorg[$divisi]."</td>";
							$tab.="<td align=left>".$keg." - ".$nmakun[$keg]."</td>";					
							$tab.="<td align=left>".$nmbgt[$kdbgt]."</td>";					
							$tab.="<td align=right width=100px>".numb_format($jlh[$divisi][$keg][$kdbgt])."</td>";
							$tab.="<td align=right width=150px>".numb_format($rp[$divisi][$keg][$kdbgt])."</td>";
							$tab.="<td align=left>".$ket[$divisi][$keg][$kdbgt]."</td>";
							
							$ttlrp+=$rp[$divisi][$keg][$kdbgt];	
							$tab.="</tr>";
						}
					}			
				}
					
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center colspan=6>T O T A L</td>";
				$tab.="<td align=right>".numb_format($ttlrp)."</td><td></td>";
				$tab.="</tr>";
				$tab.="</tbody></table>"; 
				
				$tab.="<br><label style=font-family:sans-serif;font-size:10;>Catatan :</label>";
				$tab.="<br><label style=font-family:sans-serif;font-size:10;>".nl2br($catat)."</label>";
				
				$tab.="<div style='page-break-before: always;'></div>";
				
				
				$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where notransaksi = '".$notransaksi."' and pta='PTA' and (kodebudget like 'SDM%' or kodebudget like 'EXPL%') order by divisi, kunci";
				$res=fetchdata($str);$no=0;
				if(count($res)>0){
					$tab1.="<br><label style=font-family:sans-serif;font-size:10;>SDM</label></br>";
					$tab1.="<table ".$style." width=100%>
					<thead><tr class=rowheader>";
					$tab1.="<th align=center width=20px>No</th>
						<th align=center >".$_SESSION['lang']['tahun']."</th>
						<th align=center >".$_SESSION['lang']['divisi']." / ".$_SESSION['lang']['station']."</th>
						<th align=center >".$_SESSION['lang']['blok']." / ".$_SESSION['lang']['mesin']."</th>
						<th align=center width=50px>".$_SESSION['lang']['tahuntanam']."</th>
						<th align=center >".$_SESSION['lang']['tipekaryawan']."</th>
						<th align=center >".$_SESSION['lang']['kegiatan']."</th>
						<th align=center >".$_SESSION['lang']['noakun']."</th>
						<th align=center >".$_SESSION['lang']['rotasi']."</th>
						<th align=center >".$_SESSION['lang']['volume']."</th>
						<th align=center >".$_SESSION['lang']['satuan']."</th>
						<th align=center >".$_SESSION['lang']['rupiah']."</th>
						<th align=center >".$_SESSION['lang']['jumlah']."</th>
						<th align=center >".$_SESSION['lang']['satuan']."</th>
					</tr>
					</thead><tbody>";
					$ttlvol=$ttlrp=$ttljlh=0;	
					foreach($res as $bar){
						$no++;
						$optthntnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
						$opttipekar = makeOption($dbname,'bgt_kode','kodebudget,nama',"kodebudget='".$bar['kodebudget']."'");
						$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
						$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
						
						$tab1.="<tr class=rowcontent>";
						$tab1.="<td align=center>".$no."</td>";
						$tab1.="<td align=center>".$bar['tahunbudget']."</td>";
						$tab1.="<td align=left>".getNamaOrg($bar['divisi'])."</td>";
						$tab1.="<td align=left>".getNamaOrg($bar['kodeorg'])."</td>";
						$tab1.="<td align=center>".$optthntnm[$bar['kodeorg']]."</td>";
						$tab1.="<td align=left>".$bar['kodebudget']." - ".$opttipekar[$bar['kodebudget']]."</td>";
						$tab1.="<td align=left>".$bar['kegiatan']." - ".$optkeg[$bar['kegiatan']]."</td>";
						$tab1.="<td align=left>".$bar['noakun']." - ".$optakun[$bar['noakun']]."</td>";
						$tab1.="<td align=center>".$bar['rotasi']."</td>";
						$tab1.="<td align=right>".numb_format($bar['volume'],2)."</td>";
						$tab1.="<td align=center>".$bar['satuanv']."</td>";
						$tab1.="<td align=right>".numb_format($bar['rupiah'])."</td>";
						$tab1.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
						$tab1.="<td align=center>".$bar['satuanj']."</td>";
						
						$ttlvol+=$bar['volume'];	
						$ttlrp+=$bar['rupiah'];	
						$ttljlh+=$bar['jumlah'];	
						$tab.="</tr>";
					}
					$tab1.="<tr class=rowcontent>";
					$tab1.="<td align=center colspan=9>T O T A L</td>";
					$tab1.="<td align=right>".numb_format($ttlvol,2)."</td>";
					$tab1.="<td align=center></td>";
					$tab1.="<td align=right>".numb_format($ttlrp)."</td>";
					$tab1.="<td align=right>".numb_format($ttljlh,2)."</td>";
					$tab1.="<td align=center></td>";
					
					$tab1.="</tr>";
					$tab1.="</tbody></table>";
					
					$tab1.="<div style='page-break-before: always;'></div>";
				}
				
				
				$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where  notransaksi = '".$notransaksi."' and pta='PTA' and kodebudget like 'M-%' order by divisi, kunci";
				$res=fetchdata($str);$no=0;
				if(count($res)>0){
					$tab2.="<br><label style=font-family:sans-serif;font-size:10;>Material</label></br>";
					$tab2.="<table ".$style." width=100%>
					<thead><tr class=rowheader>";
					$tab2.="<th align=center width=20px>No</th>
						<th align=center >".$_SESSION['lang']['tahun']."</th>
						<th align=center >".$_SESSION['lang']['divisi']." / ".$_SESSION['lang']['station']."</th>
						<th align=center >".$_SESSION['lang']['blok']." / ".$_SESSION['lang']['mesin']."</th>
						<th align=center width=50px>".$_SESSION['lang']['tahuntanam']."</th>
						<th align=center >".$_SESSION['lang']['namabarang']."</th>
						<th align=center >".$_SESSION['lang']['kegiatan']."</th>
						<th align=center >".$_SESSION['lang']['noakun']."</th>
						<th align=center >".$_SESSION['lang']['rotasi']."</th>
						<th align=center >".$_SESSION['lang']['volume']."</th>
						<th align=center >".$_SESSION['lang']['satuan']."</th>
						<th align=center >".$_SESSION['lang']['rupiah']."</th>
						<th align=center >".$_SESSION['lang']['jumlah']."</th>
						<th align=center >".$_SESSION['lang']['satuan']."</th>
						
					</tr>
					</thead><tbody>";
					$ttlvol=$ttlrp=$ttljlh=0;	
					foreach($res as $bar){
						$no++;
						$optthntnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
						$opttipekar = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
						$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
						$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
						
						$tab2.="<tr class=rowcontent>";
						$tab2.="<td align=center>".$no."</td>";
						$tab2.="<td align=center>".$bar['tahunbudget']."</td>";
						$tab2.="<td align=left>".getNamaOrg($bar['divisi'])."</td>";
						$tab2.="<td align=left>".getNamaOrg($bar['kodeorg'])."".$bar['kodevhc']."</td>";
						$tab2.="<td align=center>".$optthntnm[$bar['kodeorg']]."</td>";
						$tab2.="<td align=left>".$bar['kodebarang']." - ".$opttipekar[$bar['kodebarang']]."</td>";
						$tab2.="<td align=left>".$bar['kegiatan']." - ".$optkeg[$bar['kegiatan']]."</td>";
						$tab2.="<td align=left>".$bar['noakun']." - ".$optakun[$bar['noakun']]."</td>";
						$tab2.="<td align=center>".$bar['rotasi']."</td>";
						$tab2.="<td align=right>".numb_format($bar['volume'],2)."</td>";
						$tab2.="<td align=center>".$bar['satuanv']."</td>";
						$tab2.="<td align=right>".numb_format($bar['rupiah'])."</td>";
						$tab2.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
						$tab2.="<td align=center>".$bar['satuanj']."</td>";

						$ttlvol+=$bar['volume'];	
						$ttlrp+=$bar['rupiah'];	
						$ttljlh+=$bar['jumlah'];	
						$tab2.="</tr>";
					}
					$tab2.="<tr class=rowcontent>";
					$tab2.="<td align=center colspan=9>T O T A L</td>";
					$tab2.="<td align=right>".numb_format($ttlvol,2)."</td>";
					$tab2.="<td align=center></td>";
					$tab2.="<td align=right>".numb_format($ttlrp)."</td>";
					$tab2.="<td align=right>".numb_format($ttljlh,2)."</td>";
					$tab2.="<td align=center></td>";
					
					$tab2.="</tr>";
					$tab2.="</tbody></table>";
					
					$tab2.="<div style='page-break-before: always;'></div>";
				}
				
				$str="select substr(kodeorg,1,6) as divisi,bgt_budget.* from ".$dbname.".bgt_budget where  notransaksi = '".$notransaksi."' and pta='PTA' and kodebudget = 'UMUM' order by divisi, kunci";
				$res=fetchdata($str);$no=0;
				if(count($res)>0){
					$tab3.="<br><label style=font-family:sans-serif;font-size:10;>UMUM</label></br>";
					$tab3.="<table ".$style." width=100%>
					<thead><tr class=rowheader>";
					$tab3.="<th align=center width=20px>No</th>
						<th align=center >".$_SESSION['lang']['noakun']."</th>
						<th align=center >".$_SESSION['lang']['namaakun']."</th>
						<th align=center >".$_SESSION['lang']['noaruskas']."</th>
						<th align=center >".$_SESSION['lang']['namaaruskas']."</th>
						<th align=center >".$_SESSION['lang']['kodebarang']."</th>
						<th align=center >".$_SESSION['lang']['namabarang']."</th>
						<th align=center >".$_SESSION['lang']['satuan']."</th>
						<th align=center >".$_SESSION['lang']['jumlah']."</th>
						<th align=center >".$_SESSION['lang']['rupiah']."</th>
					</tr>
					</thead><tbody>";
					$ttlvol=$ttlrp=$ttljlh=0;	
					foreach($res as $bar){
						$no++;
						$optthntnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeorg']."'");
						$opttipekar = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
						$optkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan ='".$bar['kegiatan']."'");
						$optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun ='".$bar['noakun']."'");
						
						$tab3.="<tr class=rowcontent>";
						$tab3.="<td align=center>".$no."</td>";
						$tab3.="<td align=center>".$bar['noakun']."</td>";
						$tab3.="<td align=left>".$optakun[$bar['noakun']]."</td>";
						$tab3.="<td align=center>".$bar['aruskas']."</td>";
						$tab3.="<td align=left>".getNamaAruskas($bar['aruskas'])."</td>";
						$tab3.="<td align=center>".$bar['kodebarang']."</td>";
						$tab3.="<td align=left>".getNamaBrg($bar['kodebarang'])."</td>";
						$tab3.="<td align=center>".$bar['satuanj']."</td>";
						$tab3.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
						$tab3.="<td align=right>".numb_format($bar['rupiah'])."</td>";

						$ttlvol+=$bar['volume'];	
						$ttlrp+=$bar['rupiah'];	
						$ttljlh+=$bar['jumlah'];	
						$tab3.="</tr>";
					}
					$tab3.="<tr class=rowcontent>";
					$tab3.="<td align=center colspan=9>T O T A L</td>";
					
					$tab3.="<td align=right>".numb_format($ttlrp)."</td>";
					
					
					$tab3.="</tr>";
					$tab3.="</tbody></table>";
					
					$tab4.="<br><label style=font-family:sans-serif;font-size:10;>File Pendukung</label></br>
						<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
							<thead>
							<tr class=rowheader>
								<td align='center' width=30px>No.</td>
								<td align='center' width=50px>File Type</td>
								<td align='center'>Filename</td>
								<td align='center' width=30px>Action</td>
							</tr>
							</thead>
							<tbody>
					";
					
					$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
					$res= fetchData($str);
					if(empty($res)){
						$tab4.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
					}else{
						foreach($res as $key=>$val){
							$no++;
							$tab4.="<tr class=rowcontent>
									<td style='text-align:center'>".$no."</td>";
							$icon=seticonfile($val['formaticon']);
							$tab4.="<td style='text-align:center'>
									<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
								</td>";
							$tab4.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
							$tab4.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
							$tab4.="</tr>";
						}
					}
					$tab4.="</tbody>
						</table>";
					
				}
				
				
				
				if($jenis=='pdf'){		
					$dompdf = new Dompdf();
					$dompdf->load_html($tabx.$tab.$tab1.$tab2.$tab3);
					$dompdf->setPaper('A4', 'landscape');
					$dompdf->render();
					$canvas = $dompdf->get_canvas();
					#$font = Font_Metrics::get_font("helvetica", "bold");
					$canvas->page_text(16, 800, "Page: {PAGE_NUM} of {PAGE_COUNT}",'', 8, array(0,0,0));
					$dompdf->stream("pta",array("Attachment"=>0));
				}elseif($jenis=='excel'){
					$nop = "pta.xls";
					$xls = new HtmlExcel();
					$xls->setCss($css);
					$xls->addSheet("approval", $tabx);
					$xls->addSheet("rekap_pta", $tab);
					$xls->addSheet("detail_sdm", $tab1);
					$xls->addSheet("detail_mat", $tab2);
					$xls->addSheet("detail_umm", $tab3);
					$xls->headers($nop);
					echo $xls->buildFile();
				}else{
					
					echo $tabx.$tab.$tab1.$tab2.$tab3.$tab4;
				}
			break;
		}
		
		$str="select updateby,lastupdate from ".$dbname.".bgt_budget where  notransaksi = '".$notransaksi."' and pta='PTA' order by lastupdate desc limit 1";
		$res=fetchdata($str);
		if(empty($res)){
			$str="select updateby,lastupdate from ".$dbname.".bgt_kapital where  notransaksi = '".$notransaksi."' and pta='PTA' order by lastupdate desc limit 1";
			$res=fetchdata($str);
		}
		
		echo "<br><i>Dibuat Oleh, ".getKary($res[0]['updateby']).", ".$res[0]['lastupdate']."</i>";
	break;
	case'form_ajukan';
		$str = "SELECT * FROM " . $dbname . ".listfileupload where 1=1 and kriteriaefil='PTA' and notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		if(count($res)==0){
			exit("Warning : Silahkan upload file pendukung terlebih dahulu.");
		}
		
	
		$wh="a.karyawanid!='".$_SESSION['standard']['userid']."'";
		if($jenis=='NONKAPITAL'){
			$str="select substr(kodeorg,1,4) as kodeorg from ".$dbname.".bgt_budget where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			foreach($res as $bar){				
				$kodeorg=$bar['kodeorg'];
			}
		}else{
			$str="select substr(kodeunit,1,4) as kodeorg from ".$dbname.".bgt_kapital where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			foreach($res as $bar){				
				$kodeorg=$bar['kodeorg'];
			}
		}
		
		$kodeapproval='PTA';
		#$countApp = getCountApproval($kodeapproval, $kodeorg);
		
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
			left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 1=1 
			and a.jenispersetujuan='".$kodeapproval."' and level='1' and a.kodeunit='".$kodeorg."' order by b.namakaryawan asc";
		$res=fetchdata($str);
		$optKry="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($res as $rkry){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}

		$optjns=makeOption($dbname,'setup_jenisapproval','jenis,nama');
		$tab = "<table cellspacing=1 border=0 width=100%>
					<input hidden id=kodeapprovalaju value='".$kodeapproval."'>
					<input hidden id=jenisaju value='".$jenis."'>
		
					<tr class=rowcontent>
						<td width=100px>".$_SESSION['lang']['notransaksi']."</td>
						<td width=5px>:</td>
						<td id=notran_aju>".$notransaksi."</td>
					</tr>
					
					<tr class=rowcontent>
						<td width=100px>".$_SESSION['lang']['jenis']."</td>
						<td width=5px>:</td>
						<td>".$optjns[$kodeapproval]."</td>
					</tr>
					<tr class=rowcontent ".$hide.">
						<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
						<td width=5px>:</td>
						<td><select id=kepada style='width:100%;'>".$optKry."</select></td>
					</tr>
					<tr class=rowcontent>
						<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
						<td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
					</tr>				
					</table>";
		
        echo $tab;
	break;
	case'ajukan':
		try {
		$owlPDO->beginTransaction();
		
		if($jenis=='NONKAPITAL'){
			$str="select substr(kodeorg,1,4) as kodeorg from ".$dbname.".bgt_budget where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			foreach($res as $bar){				
				$kodeorg=$bar['kodeorg'];
			}
			
			$table='bgt_budget';
		}else{
			$str="select substr(kodeunit,1,4) as kodeorg from ".$dbname.".bgt_kapital where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			foreach($res as $bar){				
				$kodeorg=$bar['kodeorg'];
			}
			
			$table='bgt_kapital';
		}
		
		if($kepada=='' or $notransaksi==''){
			throw new PDOException('Isikan nama penyetuju.');
		}
		
		# cari dulu apakah sudah pernah di ajukan sebelumnya
		$tglhi = date("Ymd");
		$str="select * from ".$dbname.".approval where jenispersetujuan='".$kodeapproval."' and notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['notransaksi']!=''){
				# jika ada pindahkan ke table ini
				$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."')";
				$owlPDO->exec($str);
			}
		}
		
		#kemudian setelah di pindah, hapus persetujuan lama
		$str="delete from ".$dbname.".approval where jenispersetujuan='".$kodeapproval."' and notransaksi='".$notransaksi."'";
		$owlPDO->exec($str);
		
		# update flag menjadi 9
		$str = "update " . $dbname . ".".$table." set statuspta='9' where notransaksi = '" . $notransaksi . "'"; 
		#exit("error".$str);
		$owlPDO->exec($str);

		# insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
				`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				 values ('','".$notransaksi."','".$kodeapproval."','1','" . $kepada."','0','','".$jenis."','')";
		$owlPDO->exec($str);
		
	
		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	case'getaruskasglobal':
		$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '".$param['akun']."' order by a.noaruskas asc"; #exit("error".$str);
		$res=fetchdata($str);
		if(count($res)=='0'){
			exit("Warning : Nomor aruskas untuk akun ".$param['akun']." belum ada.");
		}
		
		$optaruskas="<option value=''>Pilih Data</option>";
		foreach($res as $bar){
			$a="";
			if($param['aruskas']==$bar['noaruskas']){
				$a="selected";
			}
			$optaruskas.="<option value=".$bar['noaruskas']." ".$a.">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
		}
		echo $optaruskas;
	break;
	case'getaruskas':
		$optakun=makeOption($dbname,'sdm_5tipeasset','kodetipe,akunak');
		$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '".$optakun[$param['kodebgt']]."' order by a.noaruskas asc"; #exit("error".$str);
		$res=fetchdata($str);
		if(count($res)=='0'){
			exit("Warning : Nomor aruskas untuk akun ".$optakun[$param['kodebgt']]." belum ada.");
		}
		
		$optaruskas="<option value=''></option>";
		foreach($res as $bar){
			$a="";
			if($param['aruskas']==$bar['noaruskas']){
				$a="selected";
			}
			$optaruskas.="<option value=".$bar['noaruskas']." ".$a.">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
		}
		
		$str = "select * from ".$dbname.".bgt_5capex where kodecapex = '".$param['kodebgt']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$klbarang=$bar['kelbarang'];
		}
		
		$optbarang="<option value=''></option>";
		if($klbarang!=''){
			$str = "select * from ".$dbname.".log_5masterbarang where substr(kodebarang,1,5) in (select kelbarang from ".$dbname.".bgt_5capex where kodecapex = '".$param['kodebgt']."') and inactive='0' order by kodebarang";
			$res = fetchdata($str);
			if(count($res)>0){
				$optbarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$ada='1';
			}else{
				$ada="";
			}
			foreach($res as $val){
				$d=substr($val['kodebarang'],0,5);
				if($d!=$n){			
					$nmkel = makeOption($dbname, 'log_5subklbarang', 'kode,namasubkelompok',"kode='".$d."'");
					// $optbarang.="<optgroup label='".$nmkel[$d]."'>";
				}
				$b="";
				if($param['kodebarang']==$val['kodebarang']){
					$b="selected";
				}
				$optbarang.="<option value=".$val['kodebarang']." ".$b.">".$val['kodebarang']." - ".$val['namabarang']."</option>";
				$n=$d;
				if($d!=$n){			
					// $optbarang.="</optgroup>";
				}
			}
		}
		
		echo $optaruskas."####".$optbarang."####".$ada;
		
	break;		
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
?>