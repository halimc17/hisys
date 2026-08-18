<?
//require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

if($_GET['telid']!=''){
	require_once('master_validation_tel.php');
}else{
	require_once('master_validation.php');
}

if(count($_POST)>0){	
	$param = $_POST;
}else{
	$param = $_GET;
}

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

#cek apakah 1 no transaksi terdiri dari beberapa keg dan blok
$str = "SELECT * FROM " . $dbname . ".vhc_spl_prestasi where notransaksi='".$param['notransaksi']."'"; #exit('error'.$str);
$ceckD = fetchData($str);
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$dataX[$bar['notransaksi']][$bar['kodekegiatan']][$bar['alokasi']]=$bar['alokasi'];
}
$error1='';
if(count($ceckD)==0) {
	$error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
}
if($error1!='') {
	exit("warning : ".$error1);
}

$strx = selectQuery($dbname,'vhc_spl_aktifitas',"*","notransaksi='".$param['notransaksi']."'");
$dtx = fetchData($strx);
$kdorgx = $dtx[0]['kodeorg'];
$prdgj = substr($dtx[0]['tanggal'],0,7);

// $str = "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and kriteriaefil='BKM'";
// $res = fetchData($str);
// if(count($res)==0){
// 	exit("Warning: Silahkan upload file pendukung terlebih dahulu sebelum melakukan posting.");
// }

$str = "select * from ".$dbname.".sdm_5periodegaji where kodeorg = '".$kdorgx."' and periode='".$prdgj."' and sudahproses='1'";
$res = fetchData($str);
if(count($res)>0){
	exit("Warning: Periode gaji sudah ditutup, proses dibatalkan.");
}

try {
$owlPDO->beginTransaction();

$arrkeg=array();
foreach($dataX as $notranX => $valKegX){
	foreach($valKegX as $kegiatanX => $valBlokX){
		foreach($valBlokX as $blokX){
		#=== Get Data ===
		# Header

		// $qBlok = selectQuery($dbname,'setup_blok','statusblok',"kodeorg='".$blokX."'");
        // $resBlok = fetchData($qBlok);

		$queryH = selectQuery($dbname,'vhc_spl_aktifitas',"*","notransaksi='".
			$param['notransaksi']."'");
		$dataH = fetchData($queryH);
		
		if($dataH[0]['kodeorg']!=$_SESSION['empl']['lokasitugas']){
			throw new PDOException("Silahkan pindah lokasitugas anda ke unit ".$dataH[0]['kodeorg']." terlebih dahulu.");
		}
		
		#validasi posting
		validasiInput($dataH[0]['kodeorg'],$dataH[0]['divisi'],'BKMPOST',$dataH[0]['tanggal'],$exit='0');
			
			
		$prdbln=substr($dataH[0]['tanggal'],0,7);
		$tahun=substr($dataH[0]['tanggal'],0,4);

		#====cek periode===============================
		$tgl = str_replace("-","",$dataH[0]['tanggal']);
		if($_SESSION['org']['period']['start']>$tgl)
			throw new PDOException('Tanggal diluar periode aktif');

		# Prestasi
		$queryD="select notransaksi,kodekegiatan,alokasi, sum(total_hasilkerja) as hasilkerja, sum(total_hk) as jumlahhk,  kodesegment from ".$dbname.".vhc_spl_prestasi where notransaksi='".$notranX."' and kodekegiatan='".$kegiatanX."' and alokasi='".$blokX."' group by notransaksi, kodekegiatan, alokasi";
		$dataD = fetchData($queryD);

		
		// @$arrkeg[$resBlok[0]['statusblok']][$blokX][$kegiatanX]+=$dataD[0]['hasilkerja'];
		# Absensi
		// this is (JOKI dan NITA)
		// sama sepertii kebun_slave_operasional_print_detailx.php(174) untuk ngambil PRESTASI KEHADIRAN ON KE nikpemel nik
		$queryAbs = "SELECT a.jhk,a.umr,a.insentif,a.penalty,a.nik FROM " . $dbname . ".vhc_spl_kehadiran a 
		left join " . $dbname . ".vhc_spl_prestasi b on a.notransaksi=b.notransaksi and a.nik=b.nik and a.nourut=b.nourut 
		where a.notransaksi='".$notranX."' and b.kodekegiatan='".$kegiatanX."' and b.alokasi='".$blokX."'";
		$dataAbs = fetchData($queryAbs);

		#=== Cek if posted ===
		$error0 = "";
		if($dataH[0]['jurnal']==1) {
			$error0 .= $_SESSION['lang']['errisposted'];
		}
		if($error0!='') {
			throw new PDOException($error0);
		}

		#=== Cek if data not exist ===
		$error1 = "";
		if(count($dataH)==0) {
			$error1 .= $_SESSION['lang']['errheadernotexist']."\n";
		}
		if(count($dataD)==0) {
			$error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
		}
		if(count($dataD)>1) {
			//$error1 .= " Error: duplicate transaction\n";
		}
		if($error1!='') {
			throw new PDOException($error1);
		}

		// Get Segment
		$segment = $dataD[0]['kodesegment'];

		#=== Hitung Cost dari Absensi (Perawatan) ===
		$costRawat=$costRawatPre=$penaltyRawat=$totalHk = 0;
		$costRawatNik=$costRawatPreNik=$penaltyRawatNik=$totalHkNik = array();
		if(!empty($dataAbs)) {
			foreach($dataAbs as $row) {
				$costRawat += $row['umr'];
				$costRawatPre += $row['insentif'];
				$penaltyRawat += $row['penalty'];
				$totalHk += $row['jhk'];
				
				@$costRawatNik[$row['nik']] += $row['umr'];
				@$costRawatPreNik[$row['nik']] += $row['insentif'];
				@$penaltyRawatNik[$row['nik']] += $row['penalty'];
				@$totalHkNik[$row['nik']] += $row['jhk'];
			}
		}

		#=== Cek if HK belum sama ===
		$totalHk=round($totalHk,2);                             // diround hingga 2 desimal
		$dataD[0]['jumlahhk']=round($dataD[0]['jumlahhk'],2);   // diround hingga 2 desimal
		$qwe=$totalHk-$dataD[0]['jumlahhk'];
		if($totalHk!=$dataD[0]['jumlahhk']) {
			// throw new PDOException("HK Prestasi belum teralokasi dengan lengkap ".$qwe."");
			throw new PDOException("HK Prestasi belum teralokasi dengan lengkap ".$qwe."xxx".$totalHk."xx".$dataD[0]['jumlahhk']."");
		}
		#=== cek apakah di setup ada materialnya ===
		# Ambil data dari  kebun_pakaimaterial
		$queryM = selectQuery($dbname,'kebun_pakaimaterial',"*","notransaksi='".$notranX."' and kodekegiatan='".$kegiatanX."' and kodeorg='".$blokX."'");
		$dataM = fetchData($queryM);

		# Cek data di master kegiatan
		$queryK = selectQuery($dbname,'setup_kegiatannorma',"*","kodekegiatan='".$dataD[0]['kodekegiatan']."'");
		$dataK = fetchData($queryK);
		if(empty($dataM) and !empty($dataK)){
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$dataD[0]['kodekegiatan']."'");
			throw new PDOException("Kegiatan ".$nmkeg[$dataD[0]['kodekegiatan']].", blok ".$blokX." harus menggunakan material.");
		}
		

		#=== Cek if Upload Absensi ===
		$countUpload = 0;
		$countUpload = "";
		$arrUpload = array();
		if(!empty($dataH[0]['nikmandor'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor'];
		if(!empty($dataH[0]['nikmandor1'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor1'];
		// if(!empty($dataH[0]['nikasisten'])) $arrUpload[]['nik'] = $dataH[0]['nikasisten'];
		if(!empty($dataH[0]['keranimuat'])) $arrUpload[]['nik'] = $dataH[0]['keranimuat'];
		foreach($dataAbs as $row){
			$arrUpload[]['nik'] = $row['nik'];
		}


		#query pengecekan apakah FP aktif / tidak
		$statusfp ='0'; $tipevalidasi = "";
		$str = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".$dataH[0]['kodeorg']."' and tanggal <= '".$dataH[0]['tanggal']."'";
		$res = fetchData($str);
		$statusfp    = $res[0]['status'];//1 aktif,0 tidak
		$tipevalidasi= $res[0]['tipevalidasi'];
		$detailexp   = explode(",",$res[0]['detailvalidasi']);
		foreach($detailexp as $vald){
			$detval[$vald]=$vald;
		}
		
		
		if($statusfp==1){
			validasifpfull($tipevalidasi,$detval,'BKM',$arrUpload,$dataH[0]['tanggal'],'0');
		} else {
			// exit("Warning: Aktivasi Fingerprint belum ada<br>
			// 		Silakan setup di menu SDM > SETUP > Aktivasi Fingerprint"
			// );
		}

		#===================================================================
		$lstUnit=array();
		$lstUph=array();
		$lstPre=array();
		$statpen=0;
		$statPre=0;
		
		$sDr="select a.nik, lokasitugas,sum(a.umr) as uphtot,sum(a.insentif) as premi, sum(a.penalty) as penalty,kodeorganisasi as kodept from ".$dbname.".vhc_spl_kehadiran a left join ".$dbname.".datakaryawan b on a.nik=b.karyawanid left join " . $dbname . ".vhc_spl_prestasi c on a.notransaksi=c.notransaksi and a.nourut=c.nourut where a.notransaksi='".$notranX."' and c.kodekegiatan='".$kegiatanX."' and c.alokasi='".$blokX."' group by lokasitugas, a.nik";
		$rDr=fetchdata($sDr);
		$lstUph=$lstPre=$lstPent=$lstPt=array();
		$lstUphNik=$lstPreNik=$lstPentNik=array();
		if(count($rDr)!=0){
			foreach($rDr as $row=>$lstData){
				if(abs($lstData['uphtot'])>0){
					@$lstUph[$lstData['lokasitugas']]+=($lstData['uphtot']);
					@$lstUphNik[$lstData['lokasitugas']][$lstData['nik']]+=($lstData['uphtot']);
				}
				if(abs($lstData['premi'])>0){
					@$lstPre[$lstData['lokasitugas']]+=($lstData['premi']);
					@$lstPreNik[$lstData['lokasitugas']][$lstData['nik']]+=($lstData['premi']);

					$statPre=1;
				}
				if(abs($lstData['penalty'])>0){
					@$lstPent[$lstData['lokasitugas']]+=($lstData['penalty']);
					@$lstPentNik[$lstData['lokasitugas']][$lstData['nik']]+=($lstData['penalty']);

					$statpen=1;
				}
				$lstUnit[$lstData['lokasitugas']]=$lstData['lokasitugas'];
				$listnik[$lstData['nik']]=$lstData['nik'];
				$datanik[$lstData['lokasitugas']][$lstData['nik']]=$lstData['nik'];
				$lstPt[$lstData['lokasitugas']]=$lstData['kodept'];
			}
		}
	
	// echo"<pre>";
	// print_r($lstUnit);
	// echo"</pre>";
	// exit("error");
	
		if(count($lstUnit)==0){
			$lstUnit[$_SESSION['empl']['lokasitugas']]=$_SESSION['empl']['lokasitugas'];
			$lstPt[$_SESSION['empl']['kodeorganisasi']]=$_SESSION['empl']['kodeorganisasi'];
			
		}

		#======================== Nomor Jurnal =============================
		$kodeJurnal = 'M0';
		$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit',
			"kodeaplikasi='KBN' and jurnalid='".$kodeJurnal."'");
		$resParam = fetchData($queryParam);

		# Get Journal Counter
		$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and periode='".$prdbln."'"); #exit("error".$queryJ);
		$tmpKonter = fetchData($queryJ);
		if(count($tmpKonter)==0){throw new PDOException("Kelompok Jurnal M0 untuk kodeorg ".$_SESSION['org']['kodeorganisasi'].", kodeunit : ".$_SESSION['empl']['lokasitugas'].", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}
		
		@$konter = addZero($tmpKonter[0]['nokounter']+1,3);

		# Transform No Jurnal dari No Transaksi
		$tmpNoJurnal = explode('/',$param['notransaksi']);
		$nojurnal = $tmpNoJurnal[0]."/".$tmpNoJurnal[1]."/".$kodeJurnal."/".$konter;
		
		#cek apakah nomor ini sudah ada di keu_jurnalht atau belum ???
		$str = "SELECT * FROM ".$dbname.".`keu_jurnalht` WHERE nojurnal='".$nojurnal."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$str = "select max(convert(substring_index(nojurnal,'/',-1),unsigned integer)) as nomor from keu_jurnalht where kodejurnal='".$kodeJurnal."' and nojurnal like '%".$_SESSION['empl']['lokasitugas']."%' and tanggal like '".$prdbln."%'";
			$res = fetchdata($str);
			$konter = addZero($res[0]['nomor']+1,3);
			$nojurnal = $tmpNoJurnal[0]."/".$tmpNoJurnal[1]."/".$kodeJurnal."/".$konter;			
		}
		
		#======================== Nomor Jurnal Penalty =============================
		$kodeJurnalpen = 'PPRWT';
		# Get Journal Counter
		$queryJpen = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnalpen."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and periode='".$prdbln."'"); #exit("error".$queryJpen);
		$tmpKonterpen = fetchData($queryJpen);
		if(count($tmpKonterpen)==0){throw new PDOException("Kelompok Jurnal PPRWT untuk kodeorg ".$_SESSION['org']['kodeorganisasi'].", kodeunit : ".$_SESSION['empl']['lokasitugas'].", periode : ".$prdbln."  silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}
		
		@$konterpen = addZero($tmpKonterpen[0]['nokounter']+1,3);

		# Transform No Jurnal dari No Transaksi
		$tmpNoJurnalpen = explode('/',$param['notransaksi']);
		$nojurnalpen = $tmpNoJurnalpen[0]."/".$tmpNoJurnalpen[1]."/".$kodeJurnalpen."/".$konterpen;
		
		#cek apakah nomor ini sudah ada di keu_jurnalht atau belum ???
		$str = "SELECT * FROM ".$dbname.".`keu_jurnalht` WHERE nojurnal='".$nojurnalpen."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$str = "select max(convert(substring_index(nojurnal,'/',-1),unsigned integer)) as nomor from keu_jurnalht where kodejurnal='".$kodeJurnalpen."' and nojurnal like '%".$_SESSION['empl']['lokasitugas']."%' and tanggal like '".$prdbln."%'";
			$res = fetchdata($str);
			$konterpen = addZero($res[0]['nomor']+1,3);
			$nojurnalpen = $tmpNoJurnalpen[0]."/".$tmpNoJurnalpen[1]."/".$kodeJurnalpen."/".$konterpen;
		}
		
		#======================== Nomor Jurnal Premi =============================
		$kodeJurnalPre = 'M9';
		# Get Journal Counter
		$queryJpre = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnalPre."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and periode='".$prdbln."'"); #exit("error".$queryJpre);
		$tmpKonterPre = fetchData($queryJpre);
		if(count($tmpKonterPre)==0){throw new PDOException("Kelompok Jurnal M9 untuk kodeorg ".$_SESSION['org']['kodeorganisasi'].", kodeunit : ".$_SESSION['empl']['lokasitugas'].", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}
		
		@$konterpre = addZero($tmpKonterPre[0]['nokounter']+1,3);

		# Transform No Jurnal dari No Transaksi
		$tmpNoJurnalpre = explode('/',$param['notransaksi']);
		$nojurnalpre = $tmpNoJurnalpre[0]."/".$tmpNoJurnalpre[1]."/".$kodeJurnalPre."/".$konterpre;
		
		#cek apakah nomor ini sudah ada di keu_jurnalht atau belum ???
		$str = "SELECT * FROM ".$dbname.".`keu_jurnalht` WHERE nojurnal='".$nojurnalpre."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$str = "select max(convert(substring_index(nojurnal,'/',-1),unsigned integer)) as nomor from keu_jurnalht where kodejurnal='".$kodeJurnalPre."' and nojurnal like '%".$_SESSION['empl']['lokasitugas']."%' and tanggal like '".$prdbln."%'";
			$res = fetchdata($str);
			$konterpre = addZero($res[0]['nomor']+1,3);
			$nojurnalpre = $tmpNoJurnalpre[0]."/".$tmpNoJurnalpre[1]."/".$kodeJurnalPre."/".$konterpre;
		}
		
		#======================== Nomor Jurnal =============================

		//cek apakah BKM material, jika BKM material pastikan gudangnya aktif pada periode yang sama
		$tanggalzx=explode("-",$dataH[0]['tanggal']);
		$tanggalqq=$tanggalzx[0].$tanggalzx[1].$tanggalzx[2];
		$kodenyagudang='';
		$strt="SELECT a.kodegudang FROM ".$dbname.".`kebun_pakaimaterial` a WHERE a.notransaksi='".$notranX."' and a.kodekegiatan='".$kegiatanX."' and a.kodeorg='".$blokX."'";
		$res=$owlPDO->query($strt) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($prm=$res->fetch()){
				$kodenyagudang=$prm->kodegudang;
		}
		if($kodenyagudang!=''){
			if(($_SESSION['gudang'][$kodenyagudang]['start']>$tanggalqq) or ($_SESSION['gudang'][$kodenyagudang]['end']<$tanggalqq)){
				throw new PDOException("Periode aktif gudang tidak sama dengan kebun.");
			}
		}

		#=== Transform Data ===
		$dataRes['header'] = array();
		$dataRes['detail'] = array();

		$dataResPen['header'] = array();
		$dataResPen['detail'] = array();
		
		$dataResPre['header'] = array();
		$dataResPre['detail'] = array();

		#1. Data Header
		$dataRes['header'] = array(
			'nojurnal'     =>$nojurnal,
			'kodejurnal'   =>'M0',
			'tanggal'      =>$dataH[0]['tanggal'],
			'tanggalentry' =>date('Ymd'),
			'posting'      =>'1',
			'totaldebet'   =>'0',
			'totalkredit'  =>'0',
			'amountkoreksi'=>'0',
			'noreferensi'  =>$dataH[0]['notransaksi'],
			'autojurnal'   =>'1',
			'matauang'     =>'IDR',
			'kurs'         =>'1',
			'revisi'       =>'0'
		);

		#1. Data Header Penalti
		$dataResPen['header'] = array(
			'nojurnal'     =>$nojurnalpen,
			'kodejurnal'   =>'PPRWT',
			'tanggal'      =>$dataH[0]['tanggal'],
			'tanggalentry' =>date('Ymd'),
			'posting'      =>'1',
			'totaldebet'   =>'0',
			'totalkredit'  =>'0',
			'amountkoreksi'=>'0',
			'noreferensi'  =>$dataH[0]['notransaksi'],
			'autojurnal'   =>'1',
			'matauang'     =>'IDR',
			'kurs'         =>'1',
			'revisi'       =>'0'
		);
		
		#1. Data Header Premi
		$dataResPre['header'] = array(
			'nojurnal'     =>$nojurnalpre,
			'kodejurnal'   =>'M9',
			'tanggal'      =>$dataH[0]['tanggal'],
			'tanggalentry' =>date('Ymd'),
			'posting'      =>'1',
			'totaldebet'   =>'0',
			'totalkredit'  =>'0',
			'amountkoreksi'=>'0',
			'noreferensi'  =>$dataH[0]['notransaksi'],
			'autojurnal'   =>'1',
			'matauang'     =>'IDR',
			'kurs'         =>'1',
			'revisi'       =>'0'
		);

		#2. Data Detail
		# Get Data from Kegiatan
		$i = 0;
		$whereKeg = "";
		foreach($dataD as $row) {
			if($i==0) {
				$whereKeg .= "kodekegiatan='".$row['kodekegiatan']."'";
			} else {
				$whereKeg .= " or kodekegiatan='".$row['kodekegiatan']."'";
			}
			$i++;
		}

		$queryKeg = selectQuery($dbname,'setup_kegiatan',"kodekegiatan,namakegiatan,noakun",$whereKeg);
		$tmpRes = fetchData($queryKeg);
		$resKeg = array();
		foreach($tmpRes as $row) {
			$resKeg[$row['kodekegiatan']]['nama'] = $row['namakegiatan'];
			$resKeg[$row['kodekegiatan']]['akun'] = $row['noakun'];
		}

		# Detail (Debet)
		$noUrut      = 1;
		$noUrut2     = 1;
		$totalJumlah = 0;
		$totRpRK     = 0;
		$hasilkerja  = 0;
		$kodeblok    = '';
		$kodekegiatan= '';
		$dataResRk   = array();

		# Detail (Debet) Penalty
		$noUrutPen      = 1;
		$noUrut2Pen     = 1;
		$totalJumlahPen = 0;
		$totRpRKPen     = 0;
		$kodeblokPen    = '';
		$kodekegiatanPen= '';
		$dataResRkPen   = array();
		
		# Detail (Debet) Premi
		$noUrutPre      = 1;
		$noUrut2Pre     = 1;
		$totalJumlahPre = 0;
		$totRpRKPre     = 0;
		$kodeblokPre    = '';
		$kodekegiatanPre= '';
		$dataResRkPre   = array();

		#jurnal intra/interco
		if(count($lstUnit)!=0){
			foreach($lstUnit as $rw=>$rwUnit){
				if($rwUnit!=$_SESSION['empl']['lokasitugas']){
					if($lstPt[$rwUnit]!=$_SESSION['empl']['kodeorganisasi']){
						$jenis="inter";
					}else if($lstPt[$rwUnit]==$_SESSION['empl']['kodeorganisasi']){
						if($rwUnit!=$_SESSION['empl']['lokasitugas']){
							$jenis="intra";    
						}
					}
					$aknPt=makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',"kodeorg='".$rwUnit."' and jenis='".$jenis."'");
					$aknHtg=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$_SESSION['empl']['lokasitugas']."' and jenis='".$jenis."'");    
					
					#debet disisi pengguna karyaawan
					if($lstUph[$rwUnit]!=0){
						$dataRes['detail'][] = array(
							'nojurnal'    =>$nojurnal,
							'tanggal'     =>$dataH[0]['tanggal'],
							'nourut'      =>$noUrut,
							'noakun'      =>$resKeg[$row['kodekegiatan']]['akun'],
							'keterangan'  =>'Upah Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
							'jumlah'      =>@$lstUph[$rwUnit],
							'matauang'    =>'IDR',
							'kurs'        =>'1',
							'kodeorg'     =>$_SESSION['empl']['lokasitugas'],
							'kodekegiatan'=>$row['kodekegiatan'],
							'kodeasset'   =>$dataD[0]['kodeorg'],
							'kodebarang'  =>'',
							'nik'         =>'',
							'kodecustomer'=>'',
							'kodesupplier'=>'',
							'noreferensi' =>$dataH[0]['notransaksi'],
							'noaruskas'   =>'',
							'kodevhc'     =>'',
							'nodok'       =>'',
							'kodeblok'    =>$dataD[0]['kodeorg'],
							'revisi'      =>'0',
							'kodesegment' => $segment
						);
						
						$noUrut+=1;
					
						# Detail (Kredit)  disisi pengguna karyaawan
						$dataRes['detail'][] = array(
							'nojurnal'    =>$nojurnal,
							'tanggal'     =>$dataH[0]['tanggal'],
							'nourut'      =>$noUrut,
							'noakun'      =>$aknPt[$rwUnit],
							'keterangan'  =>'Upah Pemeliharaan '.$dataH[0]['tipetransaksi'].' - '.$resKeg[$row['kodekegiatan']]['nama'],
							'jumlah'      =>$lstUph[$rwUnit]*(-1),
							'matauang'    =>'IDR',
							'kurs'        =>'1',
							'kodeorg'     =>$_SESSION['empl']['lokasitugas'],
							'kodekegiatan'=>'',
							'kodeasset'   =>$dataD[0]['kodeorg'],
							'kodebarang'  =>'',
							'nik'         =>'',
							'kodecustomer'=>'',
							'kodesupplier'=>'',
							'noreferensi' =>$dataH[0]['notransaksi'],
							'noaruskas'   =>'',
							'kodevhc'     =>'',
							'nodok'       =>'',
							'kodeblok'    =>'',
							'revisi'      =>'0',
							'kodesegment' => $segment
						);
					}		
					
					# Get Journal Counter
					$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
						"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M' and kodeunit='".$rwUnit."' and periode='".$prdbln."'");
					$tmpKonter2 = fetchData($queryJ);
					if(count($tmpKonter2)==0){throw new PDOException("Kelompok Jurnal M untuk kodeorg ".$lstPt[$rwUnit].", kodeunit : ".$rwUnit.", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}
					
					@$konter = addZero($tmpKonter2[0]['nokounter']+1,3);
					@$counterDt[$rwUnit]=intval($tmpKonter2[0]['nokounter'])+1;
					# Transform No Jurnal dari No Transaksi
					$tmpNoJurnal = explode('/',$param['notransaksi']);
					$nojurnal2 = $tmpNoJurnal[0]."/".$rwUnit."/M/".$konter;
					
					#cek apakah nomor ini sudah ada di keu_jurnalht atau belum ???
					$str = "SELECT * FROM ".$dbname.".`keu_jurnalht` WHERE nojurnal='".$nojurnal2."'";
					$res = fetchdata($str);
					if(count($res)>0){
						$str = "select max(convert(substring_index(nojurnal,'/',-1),unsigned integer)) as nomor from keu_jurnalht where kodejurnal='M' and nojurnal like '%".$rwUnit."%' and tanggal like '".$prdbln."%'";
						$res = fetchdata($str);
						$konter = addZero($res[0]['nomor']+1,3);
						$nojurnal2 = $tmpNoJurnal[0]."/".$rwUnit."/M/".$konter;
					}
					
					
					
					
					$temp="";
					if($temp!=$rwUnit){
						$temp=$rwUnit;
						#1. Data Header
						$dataResRk['header'][] = array(
							'nojurnal'     =>$nojurnal2,
							'kodejurnal'   =>'M',
							'tanggal'      =>$dataH[0]['tanggal'],
							'tanggalentry' =>date('Ymd'),
							'posting'      =>'1',
							'totaldebet'   =>'0',
							'totalkredit'  =>'0',
							'amountkoreksi'=>'0',
							'noreferensi'  =>$dataH[0]['notransaksi'],
							'autojurnal'   =>'1',
							'matauang'     =>'IDR',
							'kurs'         =>'1',
							'revisi'       =>'0'
						);
					}
					
					#debet disisi pemilik karyaawan
					if($lstUph[$rwUnit]!=0){
						$dataResRk['detail'][] = array(
							'nojurnal'    =>$nojurnal2,
							'tanggal'     =>$dataH[0]['tanggal'],
							'nourut'      =>$noUrut2,
							'noakun'      =>$aknHtg[$tmpNoJurnal[1]],
							'keterangan'  =>'Upah Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
							'jumlah'      =>@$lstUph[$rwUnit],
							'matauang'    =>'IDR',
							'kurs'        =>'1',
							'kodeorg'     =>$rwUnit,
							'kodekegiatan'=>$row['kodekegiatan'],
							'kodeasset'   =>$dataD[0]['kodeorg'],
							'kodebarang'  =>'',
							'nik'         =>'',
							'kodecustomer'=>'',
							'kodesupplier'=>'',
							'noreferensi' =>$dataH[0]['notransaksi'],
							'noaruskas'   =>'',
							'kodevhc'     =>'',
							'nodok'       =>'',
							'kodeblok'    =>$dataD[0]['kodeorg'],
							'revisi'      =>'0',
							'kodesegment' => $segment
						);
						$noUrut2+=1;
					}
					# Detail (Kredit)  disisi pemilik karyaawan
					foreach($listnik as $nik){
						if($datanik[$rwUnit][$nik]!='' and $lstUphNik[$rwUnit][$nik]!=0){
							$dataResRk['detail'][] = array(
								'nojurnal'    =>$nojurnal2,
								'tanggal'     =>$dataH[0]['tanggal'],
								'nourut'      =>$noUrut2,
								'noakun'      =>$resParam[0]['noakunkredit'],
								'keterangan'  =>'Upah Pemeliharaan '.$dataH[0]['tipetransaksi'].' - '.$resKeg[$row['kodekegiatan']]['nama'],
								'jumlah'      =>$lstUphNik[$rwUnit][$nik]*(-1),
								'matauang'    =>'IDR',
								'kurs'        =>'1',
								'kodeorg'     =>$rwUnit,
								'kodekegiatan'=>$row['kodekegiatan'],
								'kodeasset'   =>$dataD[0]['kodeorg'],
								'kodebarang'  =>'',
								'nik'         =>$nik,
								'kodecustomer'=>'',
								'kodesupplier'=>'',
								'noreferensi' =>$dataH[0]['notransaksi'],
								'noaruskas'   =>'',
								'kodevhc'     =>'',
								'nodok'       =>'',
								'kodeblok'    =>$rwUnit,
								'revisi'      =>'0',
								'kodesegment' => $segment
							);
							$totRpRK+=$lstUphNik[$rwUnit][$nik];
							$totRpRKNik[$nik]+=$lstUphNik[$rwUnit][$nik];
							$noUrut2+=1;
						}
					}
				}
				$comment='';
				$str="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$rwUnit."' and periode='".substr($dataH[0]['tanggal'],0,7)."'"; 
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch()){
					$comment.="Unit ".$bar->kodeorg." periode ".$bar->periode." has been closed\n";
				}
			}
			#Cek apakah unit penerima RK sudah tutup buku
			if($comment!=''){
				throw new PDOException($comment);
			}
		}
		#jurnal intra / interco Penalty
		if(count($lstUnit)!=0){
			foreach($lstUnit as $rw=>$rwUnit){
				if($rwUnit!=$_SESSION['empl']['lokasitugas']){
					if($lstPt[$rwUnit]!=$_SESSION['empl']['kodeorganisasi']){
						$jenis="inter";
					}else if($lstPt[$rwUnit]==$_SESSION['empl']['kodeorganisasi']){
						if($rwUnit!=$_SESSION['empl']['lokasitugas']){
							$jenis="intra";    
						}
					}
					$aknPt=makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',"kodeorg='".$rwUnit."' and jenis='".$jenis."'");
					$aknHtg=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$_SESSION['empl']['lokasitugas']."' and jenis='".$jenis."'");    
					
					#debet disisi pengguna karyaawan
					$dataResPen['detail'][] = array(
						'nojurnal'    =>$nojurnalpen,
						'tanggal'     =>$dataH[0]['tanggal'],
						'nourut'      =>$noUrutPen,
						'noakun'      =>$aknPt[$rwUnit],
						'keterangan'  =>'Penalti Pemeliharaan '.$dataH[0]['tipetransaksi'],
						'jumlah'      =>@$lstPent[$rwUnit],
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'   =>'',
						'kodebarang'  =>'',
						'nik'         =>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$dataH[0]['notransaksi'],
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>'',
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
					$noUrutPen+=1;
					
					# Detail (Kredit)  disisi pengguna karyaawan			
					$dataResPen['detail'][] = array(
						'nojurnal'    =>$nojurnalpen,
						'tanggal'     =>$dataH[0]['tanggal'],
						'nourut'      =>$noUrutPen,
						'noakun'      =>$resKeg[$row['kodekegiatan']]['akun'],
						'keterangan'  =>'Penalti Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'      =>@$lstPent[$rwUnit]*(-1),
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>'',
						'kodeasset'   =>$dataD[0]['kodeorg'],
						'kodebarang'  =>'',
						'nik'         =>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$dataH[0]['notransaksi'],
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>$dataD[0]['kodeorg'],
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
							

					# Get Journal Counter
					$queryJpen = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
						"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M' and kodeunit='".$rwUnit."' and periode='".$prdbln."'");
					$tmpKonter2pen = fetchData($queryJpen);
					if(count($tmpKonter2pen)==0){throw new PDOException("Kelompok Jurnal M untuk kodeorg ".$lstPt[$rwUnit].",  kodeunit : ".$rwUnit.", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}
					
					@$konter2pen = addZero($tmpKonter2pen[0]['nokounter']+1,3);
					@$counterDtpen[$rwUnit]=intval($tmpKonter2pen[0]['nokounter'])+2;
					# Transform No Jurnal dari No Transaksi
					$tmpNoJurnal2pen = explode('/',$param['notransaksi']);
					$nojurnal2pen = $tmpNoJurnal2pen[0]."/".$rwUnit."/M/".$konter2pen;
					
					#cek apakah nomor ini sudah ada di keu_jurnalht atau belum ???
					$str = "SELECT * FROM ".$dbname.".`keu_jurnalht` WHERE nojurnal='".$nojurnal2pen."'";
					$res = fetchdata($str);
					if(count($res)>0){
						$str = "select max(convert(substring_index(nojurnal,'/',-1),unsigned integer)) as nomor from keu_jurnalht where kodejurnal='M' and nojurnal like '%".$rwUnit."%' and tanggal like '".$prdbln."%'";
						$res = fetchdata($str);
						$konter2pen = addZero($res[0]['nomor']+1,3);
						$nojurnal2pen = $tmpNoJurnal2pen[0]."/".$rwUnit."/M/".$konter2pen;
					}
					
					if($temp!=$rwUnit){
						$temp=$rwUnit;
						#1. Data Header
						$dataResRkPen['header'][] = array(
							'nojurnal'     =>$nojurnal2pen,
							'kodejurnal'   =>'M',
							'tanggal'      =>$dataH[0]['tanggal'],
							'tanggalentry' =>date('Ymd'),
							'posting'      =>'1',
							'totaldebet'   =>'0',
							'totalkredit'  =>'0',
							'amountkoreksi'=>'0',
							'noreferensi'  =>$dataH[0]['notransaksi'],
							'autojurnal'   =>'1',
							'matauang'     =>'IDR',
							'kurs'         =>'1',
							'revisi'       =>'0'
						);
					}
					
					#debet disisi pemilik karyaawan
					$dataResRkPen['detail'][] = array(
						'nojurnal'    =>$nojurnal2pen,
						'tanggal'     =>$dataH[0]['tanggal'],
						'nourut'      =>$noUrut2Pen,
						'noakun'      =>$resParam[0]['noakunkredit'],
						'keterangan'  =>'Penalti Pemeliharaan '.$dataH[0]['tipetransaksi'],
						'jumlah'      =>@$lstPent[$rwUnit],
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$rwUnit,
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'   =>'',
						'kodebarang'  =>'',
						'nik'         =>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$dataH[0]['notransaksi'],
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>'',
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
					$noUrut2Pen+=1;
					# Detail (Kredit)  disisi pemilik karyaawan
					foreach($listnik as $nik){
						if($datanik[$rwUnit][$nik]!='' and $lstPentNik[$rwUnit][$nik]!=0){
							$dataResRkPen['detail'][] = array(
								'nojurnal'    =>$nojurnal2pen,
								'tanggal'     =>$dataH[0]['tanggal'],
								'nourut'      =>$noUrut2Pen,
								'noakun'      =>$aknHtg[$tmpNoJurnal[1]],
								'keterangan'  =>'Penalti Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
								'jumlah'      =>$lstPentNik[$rwUnit][$nik]*(-1),
								'matauang'    =>'IDR',
								'kurs'        =>'1',
								'kodeorg'     =>$rwUnit,
								'kodekegiatan'=>$row['kodekegiatan'],
								'kodeasset'   =>$dataD[0]['kodeorg'],
								'kodebarang'  =>'',
								'nik'         =>$nik,
								'kodecustomer'=>'',
								'kodesupplier'=>'',
								'noreferensi' =>$dataH[0]['notransaksi'],
								'noaruskas'   =>'',
								'kodevhc'     =>'',
								'nodok'       =>'',
								'kodeblok'    =>$dataD[0]['kodeorg'],
								'revisi'      =>'0',
								'kodesegment' => $segment
							);
							$totRpRKPen+=$lstPentNik[$rwUnit][$nik];
							$totRpRKPenNik[$nik]+=$lstPentNik[$rwUnit][$nik];
							$noUrut2Pen+=1;
						}
					}		
				}
				$comment='';
				$str="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$rwUnit."' and periode='".substr($dataH[0]['tanggal'],0,7)."'"; 
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch()){
					$comment.="Unit ".$bar->kodeorg." periode ".$bar->periode." has been closed\n";
				}
			}
			#Cek apakah unit penerima RK sudah tutup buku
			if($comment!=''){
				throw new PDOException($comment);
			}
		}
		#jurnal inter/interco premi
		if(count($lstUnit)!=0){
			foreach($lstUnit as $rw=>$rwUnit){
				if($rwUnit!=$_SESSION['empl']['lokasitugas']){
					if($lstPt[$rwUnit]!=$_SESSION['empl']['kodeorganisasi']){
						$jenis="inter";
					}else if($lstPt[$rwUnit]==$_SESSION['empl']['kodeorganisasi']){
						if($rwUnit!=$_SESSION['empl']['lokasitugas']){
							$jenis="intra";    
						}
					}
					$aknPt=makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',"kodeorg='".$rwUnit."' and jenis='".$jenis."'");
					$aknHtg=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$_SESSION['empl']['lokasitugas']."' and jenis='".$jenis."'");    
					
					#debet disisi pengguna karyaawan
					$dataResPre['detail'][] = array(
						'nojurnal'    =>$nojurnalpre,
						'tanggal'     =>$dataH[0]['tanggal'],
						'nourut'      =>$noUrutPre,
						'noakun'      =>$resKeg[$row['kodekegiatan']]['akun'],
						'keterangan'  =>'Premi Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'      =>$lstPre[$rwUnit],
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'   =>$dataD[0]['kodeorg'],
						'kodebarang'  =>'',
						'nik'         =>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$dataH[0]['notransaksi'],
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>$dataD[0]['kodeorg'],
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
					$noUrutPre+=1;
			
					# Detail (Kredit)  disisi pengguna karyaawan
					$dataResPre['detail'][] = array(
						'nojurnal'    =>$nojurnalpre,
						'tanggal'     =>$dataH[0]['tanggal'],
						'nourut'      =>$noUrutPre,
						'noakun'      =>$aknPt[$rwUnit],
						'keterangan'  =>'Premi Pemeliharaan '.$dataH[0]['tipetransaksi'].' - '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'      =>$lstPre[$rwUnit]*(-1),
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>'',
						'kodeasset'   =>'',
						'kodebarang'  =>'',
						'nik'         =>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$dataH[0]['notransaksi'],
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>'',
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
					
					# Get Journal Counter
					$queryJpre = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
						"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M' and kodeunit='".$rwUnit."' and periode='".$prdbln."'"); 
					$tmpKonter2pre = fetchData($queryJpre);
					if(count($tmpKonter2pre)==0){throw new PDOException("Kelompok Jurnal M untuk kodeorg ".$lstPt[$rwUnit].",   kodeunit : ".$rwUnit.", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}
					
					@$konterpre = addZero($tmpKonter2pre[0]['nokounter']+1,3);
					@$counterDtpre[$rwUnit]=intval($tmpKonter2pre[0]['nokounter'])+1;
					# Transform No Jurnal dari No Transaksi
					$tmpNoJurnalpre = explode('/',$param['notransaksi']);
					$nojurnal2pre = $tmpNoJurnalpre[0]."/".$rwUnit."/M/".$konterpre;
					
					#cek apakah nomor ini sudah ada di keu_jurnalht atau belum ???
					$str = "SELECT * FROM ".$dbname.".`keu_jurnalht` WHERE nojurnal='".$nojurnal2pre."'";
					$res = fetchdata($str);
					if(count($res)>0){
						$str = "select max(convert(substring_index(nojurnal,'/',-1),unsigned integer)) as nomor from keu_jurnalht where kodejurnal='M' and nojurnal like '%".$rwUnit."%' and tanggal like '".$prdbln."%'";
						$res = fetchdata($str);
						$konterpre = addZero($res[0]['nomor']+1,3);
						$nojurnal2pre = $tmpNoJurnalpre[0]."/".$rwUnit."/M/".$konterpre;
					}
					
					if($temp!=$rwUnit){
						$temp=$rwUnit;
						#1. Data Header
						$dataResRkPre['header'][] = array(
							'nojurnal'     =>$nojurnal2pre,
							'kodejurnal'   =>'M',
							'tanggal'      =>$dataH[0]['tanggal'],
							'tanggalentry' =>date('Ymd'),
							'posting'      =>'1',
							'totaldebet'   =>'0',
							'totalkredit'  =>'0',
							'amountkoreksi'=>'0',
							'noreferensi'  =>$dataH[0]['notransaksi'],
							'autojurnal'   =>'1',
							'matauang'     =>'IDR',
							'kurs'         =>'1',
							'revisi'       =>'0'
						);
					}
					
					
					#debet disisi pemilik karyaawan
					$dataResRkPre['detail'][] = array(
						'nojurnal'    =>$nojurnal2pre,
						'tanggal'     =>$dataH[0]['tanggal'],
						'nourut'      =>$noUrut2Pre,
						'noakun'      =>$aknHtg[$tmpNoJurnal[1]],
						'keterangan'  =>'Premi Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'      =>$lstPre[$rwUnit],
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$rwUnit,
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'   =>'',
						'kodebarang'  =>'',
						'nik'         =>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$dataH[0]['notransaksi'],
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>$dataD[0]['kodeorg'],
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
					
					$noUrut2Pre+=1;
					# Detail (Kredit)  disisi pemilik karyaawan
					foreach($listnik as $nik){
						if($datanik[$rwUnit][$nik]!='' and $lstPreNik[$rwUnit][$nik]!=0){
							$dataResRkPre['detail'][] = array(
								'nojurnal'    =>$nojurnal2pre,
								'tanggal'     =>$dataH[0]['tanggal'],
								'nourut'      =>$noUrut2Pre,
								'noakun'      =>$resParam[0]['noakunkredit'],
								'keterangan'  =>'Premi Pemeliharaan '.$dataH[0]['tipetransaksi'].' - '.$resKeg[$row['kodekegiatan']]['nama'],
								'jumlah'      =>$lstPreNik[$rwUnit][$nik]*(-1),
								'matauang'    =>'IDR',
								'kurs'        =>'1',
								'kodeorg'     =>$rwUnit,
								'kodekegiatan'=>$row['kodekegiatan'],
								'kodeasset'   =>'',
								'kodebarang'  =>'',
								'nik'         =>$nik,
								'kodecustomer'=>'',
								'kodesupplier'=>'',
								'noreferensi' =>$dataH[0]['notransaksi'],
								'noaruskas'   =>'',
								'kodevhc'     =>'',
								'nodok'       =>'',
								'kodeblok'    =>$rwUnit,
								'revisi'      =>'0',
								'kodesegment' => $segment
							);
							$totRpRKPre+=$lstPreNik[$rwUnit][$nik];
							$noUrut2Pre+=1;
						}
					}
				}
				$comment='';
				$str="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$rwUnit."' and periode='".substr($dataH[0]['tanggal'],0,7)."'"; 
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch()){
					$comment.="Unit ".$bar->kodeorg." periode ".$bar->periode." has been closed\n";
				}
			}
			#Cek apakah unit penerima RK sudah tutup buku
			if($comment!=''){
				throw new PDOException($comment);
			}
		}
		
		#Upah
		if(@count($lstUnit[$_SESSION['empl']['lokasitugas']])!=0 and ($costRawat-$totRpRK)!=0){
			foreach($dataD as $row) {
				if($nmorg[substr($row['kodeorg'],0,4)]!=''){
					$kodeorgx = substr($row['kodeorg'],0,4);
				}else{
					$kodeorgx = $dataH[0]['kodeorg'];
				}
				$dataRes['detail'][] = array(
					'nojurnal'    =>$nojurnal,
					'tanggal'     =>$dataH[0]['tanggal'],
					'nourut'      =>$noUrut,
					'noakun'      =>$resKeg[$row['kodekegiatan']]['akun'],
					'keterangan'  =>'Upah Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
					'jumlah'      =>($costRawat-$totRpRK),
					'matauang'    =>'IDR',
					'kurs'        =>'1',
					'kodeorg'     =>$kodeorgx,
					'kodekegiatan'=>$row['kodekegiatan'],
					'kodeasset'   =>$row['kodeorg'],
					'kodebarang'  =>'',
					'nik'         =>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi' =>$dataH[0]['notransaksi'],
					'noaruskas'   =>'',
					'kodevhc'     =>'',
					'nodok'       =>'',
					'kodeblok'    =>$row['kodeorg'],
					'revisi'      =>'0',
					'kodesegment' => $segment
				);
				$totalJumlah +=($costRawat-$totRpRK);
				$noUrut++;
				$kodeblok=$row['kodeorg'];
				$kodekegiatan=$row['kodekegiatan'];
				$hasilkerja = $row['hasilkerja'];
			}
			
			# Detail (Kredit)
			foreach($listnik as $nik){
				if($datanik[$_SESSION['empl']['lokasitugas']][$nik]!=''){
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal'    =>$nojurnal,
						'tanggal'     =>$dataH[0]['tanggal'],
						'nourut'      =>$noUrut,
						'noakun'      =>$resParam[0]['noakunkredit'],
						'keterangan'  =>'Upah Pemeliharaan '.$dataH[0]['tipetransaksi'].' - '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'      =>($costRawatNik[$nik]-$totRpRKNik[$nik])*(-1),
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'   =>$row['kodeorg'],
						'kodebarang'  =>'',
						'nik'         =>$nik,
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$dataH[0]['notransaksi'],
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>$row['kodeorg'],
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
					$noUrut++;
				}
			}
			# Total D/K
		}
		#Penalty
		if(@count($lstUnit[$_SESSION['empl']['lokasitugas']])!=0 and ($penaltyRawat-$totRpRKPen)!=0){
			foreach($dataD as $row) {
				if($nmorg[substr($row['kodeorg'],0,4)]!=''){
					$kodeorgx = substr($row['kodeorg'],0,4);
				}else{
					$kodeorgx = $dataH[0]['kodeorg'];
				}
				$noUrutPen++;
				$dataResPen['detail'][] = array(
					'nojurnal'    =>$nojurnalpen,
					'tanggal'     =>$dataH[0]['tanggal'],
					'nourut'      =>$noUrutPen,
					'noakun'      =>$resParam[0]['noakunkredit'],
					'keterangan'  =>'Penalti Pemeliharaan '.$dataH[0]['tipetransaksi'],
					'jumlah'      =>($penaltyRawat-$totRpRKPen),
					'matauang'    =>'IDR',
					'kurs'        =>'1',
					'kodeorg'     =>$kodeorgx,
					'kodekegiatan'=>$row['kodekegiatan'],
					'kodeasset'   =>'',
					'kodebarang'  =>'',
					'nik'         =>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi' =>$dataH[0]['notransaksi'],
					'noaruskas'   =>'',
					'kodevhc'     =>'',
					'nodok'       =>'',
					'kodeblok'    =>'',
					'revisi'      =>'0',
					'kodesegment' => $segment
				);
				$totalJumlahPen +=($penaltyRawat-$totRpRKPen);
				$noUrutPen++;
				$kodeblokPen=$row['kodeorg'];
				$kodekegiatanPen=$row['kodekegiatan'];
			}

			# Detail (Kredit)
			foreach($listnik as $nik){
				if($datanik[$_SESSION['empl']['lokasitugas']][$nik]!=''){
					$dataResPen['detail'][] = array(
						'nojurnal'    =>$nojurnalpen,
						'tanggal'     =>$dataH[0]['tanggal'],
						'nourut'      =>$noUrutPen,
						'noakun'      =>$resKeg[$row['kodekegiatan']]['akun'],
						'keterangan'  =>'Penalti Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'      =>($penaltyRawatNik[$nik]-$totRpRKPenNik[$nik])*(-1),
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'   =>$row['kodeorg'],
						'kodebarang'  =>'',
						'nik'         =>$nik,
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$dataH[0]['notransaksi'],
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>$row['kodeorg'],
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
					$noUrutPen++;
				}
			}
			# Total D/K
		}
		
		#Premi
		if(@count($lstUnit[$_SESSION['empl']['lokasitugas']])!=0 and ($costRawatPre-$totRpRKPre)!=0){
			foreach($dataD as $row) {
				if($nmorg[substr($row['kodeorg'],0,4)]!=''){
					$kodeorgx = substr($row['kodeorg'],0,4);
				}else{
					$kodeorgx = $dataH[0]['kodeorg'];
				}
				$noUrutPre++;
				$dataResPre['detail'][] = array(
					'nojurnal'    =>$nojurnalpre,
					'tanggal'     =>$dataH[0]['tanggal'],
					'nourut'      =>$noUrutPre,
					'noakun'      =>$resKeg[$row['kodekegiatan']]['akun'],
					'keterangan'  =>'Premi Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
					'jumlah'      =>($costRawatPre-$totRpRKPre),
					'matauang'    =>'IDR',
					'kurs'        =>'1',
					'kodeorg'     =>$kodeorgx,
					'kodekegiatan'=>$row['kodekegiatan'],
					'kodeasset'   =>$row['kodeorg'],
					'kodebarang'  =>'',
					'nik'         =>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi' =>$dataH[0]['notransaksi'],
					'noaruskas'   =>'',
					'kodevhc'     =>'',
					'nodok'       =>'',
					'kodeblok'    =>$row['kodeorg'],
					'revisi'      =>'0',
					'kodesegment' => $segment
				);
				$totalJumlahPre +=($costRawatPre-$totRpRKPre);
				$noUrutPre++;
				$kodeblokpre=$row['kodeorg'];
				$kodekegiatanpre=$row['kodekegiatan'];
				$hasilkerjapre = $row['hasilkerja'];
			}

			# Detail (Kredit)
			foreach($listnik as $nik){
				if($datanik[$_SESSION['empl']['lokasitugas']][$nik]!='' and ($costRawatPreNik[$nik]-$totRpRKPreNik[$nik])!=0){
					$dataResPre['detail'][] = array(
						'nojurnal'    =>$nojurnalpre,
						'tanggal'     =>$dataH[0]['tanggal'],
						'nourut'      =>$noUrutPre,
						'noakun'      =>$resParam[0]['noakunkredit'],
						'keterangan'  =>'Premi Pemeliharaan '.$dataH[0]['tipetransaksi'].' - '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'      =>@($costRawatPreNik[$nik]-$totRpRKPreNik[$nik])*(-1),
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'   =>$row['kodeorg'],
						'kodebarang'  =>'',
						'nik'         =>$nik,
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$dataH[0]['notransaksi'],
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>$row['kodeorg'],
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
					$noUrutPre++;
				}
			}
			# Total D/K
		}
		
		$dataRes['header']['totaldebet'] = ($totalJumlah+$totRpRK);
		$dataRes['header']['totalkredit'] = ($totalJumlah+$totRpRK);

		$dataResPen['header']['totaldebet'] = ($totalJumlahPen+$totRpRKPen);
		$dataResPen['header']['totalkredit'] = ($totalJumlahPen+$totRpRKPen);
		
		$dataResPre['header']['totaldebet'] = ($totalJumlahPre+$totRpRKPre);
		$dataResPre['header']['totalkredit'] = ($totalJumlahPre+$totRpRKPre);

		#=== Insert Data ===
		$errorDB = "";
		# Header
		$cols = array();
		foreach($dataRes['header'] as $key=>$row) {
			$cols[] = $key;
		}
		$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header'],$cols);
		$owlPDO->exec($queryH);
		
		if($statpen==1){
			# Header Penalti
			$cols = array();
			foreach($dataResPen['header'] as $key=>$row) {
				$cols[] = $key;
			}
			$queryx = insertQuery($dbname,'keu_jurnalht',$dataResPen['header'],$cols);
			$owlPDO->exec($queryx);
		}
		if($statPre==1){
			# Header Premi
			$cols = array();
			foreach($dataResPre['header'] as $key=>$row) {
				$cols[] = $key;
			}
			$queryn = insertQuery($dbname,'keu_jurnalht',$dataResPre['header'],$cols);#exit("error".$queryn);
			$owlPDO->exec($queryn);
		}	


		# Update flag absensi
		foreach($arrUpload as $row){
			$strAbs = "update ".$dbname.".upload_absensi set flag = 1 where karyawanid='".$row['nik']."' and tanggalabsen='".($dataH[0]['tanggal'])."'";
			$owlPDO->exec($strAbs);
		}


		# Detail
		foreach($dataRes['detail'] as $key=>$dataDet) {
			$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet); 
			$owlPDO->exec($queryD);
		}
		
		if(count($dataResRk)!=0){
			foreach($dataResRk['header'] as $key=>$dataDet) {
				$queryD = insertQuery($dbname,'keu_jurnalht',$dataDet);
				$owlPDO->exec($queryD);
			}
			
			foreach(@$dataResRk['detail'] as $key=>$dataDet) {
				$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				$owlPDO->exec($queryD);
			}
		}

		if($statpen==1){
			# Detail Penalty
			foreach($dataResPen['detail'] as $key=>$dataDet) {
				$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				$owlPDO->exec($queryD);
			}
			if(count($dataResRkPen)!=0){
				foreach($dataResRkPen['header'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnalht',$dataDet);
					$owlPDO->exec($queryD);
				}
				
				foreach($dataResRkPen['detail'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
					$owlPDO->exec($queryD);
				}
			}
		}
		
		if($statPre==1){
			# Detail Premi
			foreach($dataResPre['detail'] as $key=>$dataDet) {
				$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				$owlPDO->exec($queryD);
			}
		
			if(count($dataResRkPre)!=0){
				foreach($dataResRkPre['header'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnalht',$dataDet);
					$owlPDO->exec($queryD);
				}
				
				
				foreach($dataResRkPre['detail'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
					$owlPDO->exec($queryD);
				}
			}
		}
		
		
		
		$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and periode='".$prdbln."'");
		$owlPDO->exec($queryKonter);
		
		#Penalty
		if(count($lstPent)!=0){
			$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonterpen[0]['nokounter']+1),
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnalpen."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and periode='".$prdbln."'");
			$owlPDO->exec($queryKonter);
		}
		#Premi
		if(count($lstPre)!=0){
			$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonterPre[0]['nokounter']+1),
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnalPre."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and periode='".$prdbln."'");
			$owlPDO->exec($queryKonter);
		}

			if(count($lstUnit)!=0){
				foreach($lstUnit as $rw=>$rwUnit){
					if($rwUnit!=$_SESSION['empl']['lokasitugas']){
						$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counterDt[$rwUnit]),"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M' and kodeunit='".$rwUnit."' and periode='".$prdbln."'");
						$owlPDO->exec($queryKonter);
						
						if(count($lstPent[$rwUnit])!=0){
							$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counterDtpen[$rwUnit]),"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M' and kodeunit='".$rwUnit."' and periode='".$prdbln."'");
							$owlPDO->exec($queryKonter);
						}
						if(count($lstPre[$rwUnit])!=0){
							$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counterDtpre[$rwUnit]),"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M' and kodeunit='".$rwUnit."' and periode='".$prdbln."'");
							$owlPDO->exec($queryKonter);
						}
					}
				}    
			}
		}
	}
} #tutup foreach
	
	
	
		
// echo "<pre>";
// print_r($dataRes['detail']);
// print_r($dataResPen['detail']);
// print_r($dataResPre['detail']);

// throw new PDOException("TEST");



#Jurnal material
#=== Get Data ===
# Header
$queryH = selectQuery($dbname,'vhc_spl_aktifitas',"*","notransaksi='".
	$param['notransaksi']."'");
$dataH = fetchData($queryH);
		
#cek apakah 1 no transaksi terdiri dari beberapa keg dan blok
$str3 = "SELECT * FROM " . $dbname . ".kebun_pakaimaterial where notransaksi='".$param['notransaksi']."'"; #exit('error'.$str3);
$res3 = $owlPDO->query($str3) or die(print " Gagal: " . PDOException::getMessage());
$res3->setFetchMode(PDO::FETCH_ASSOC);
$adamat=0;
while ($bar3 = $res3->fetch()) {
	$dataXX[$bar3['notransaksi']][$bar3['kodekegiatan']][$bar3['kodeorg']][$bar3['kodebarang']]=$bar3['kodebarang'];
	$dataXJurnal[$bar3['notransaksi']][$bar3['kodekegiatan']][$bar3['kodeorg']]=$bar3['kodeorg'];
	@$adamat+=1;
}

#jalankan jika ada material
if($adamat!=''){
	$rpmatperbarang=array();
	#ini insert jurnal material
	foreach(@$dataXJurnal as $notranXXJ => $valKegXXJ){
		foreach($valKegXXJ as $kegiatanXXJ => $valBlokXXJ){
			foreach($valBlokXXJ as $blokXXJ){

				$queryKeg = selectQuery($dbname,'setup_kegiatan',"kodekegiatan,namakegiatan,noakun","kodekegiatan='".$kegiatanXXJ."'");
				$tmpRes = fetchData($queryKeg);
				$resKeg = array();
				foreach($tmpRes as $row) {
					$resKeg[$row['kodekegiatan']]['nama'] = $row['namakegiatan'];
					$resKeg[$row['kodekegiatan']]['akun'] = $row['noakun'];
				}

				#ambil akun barang
				$akunbarang=Array();
				$stk="select kode,noakun from ".$dbname.".log_5klbarang where noakun!=''";
				$rek=$owlPDO->query($stk) or die(print " Gagal: ".PDOException::getMessage());
				$rek->setFetchMode(PDO::FETCH_OBJ);
				while($bak=$rek->fetch()){
					  $akunbarang[$bak->kode]=$bak->noakun;
				}
				#======================== Nomor Jurnal material=============================
				$kodeJurnal1 = 'INVK1';
				# Get Journal Counter
				$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
					"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal1."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and periode='".$prdbln."'");#exit("error".$queryJ);
				$tmpKonter1 = fetchData($queryJ);
				if(count($tmpKonter1)==0){
					#INVK1 belum diseting di kelompok jurnal
					throw new PDOException("Kelompok jurnal untuk ".$kodeJurnal1." belum ada");
				}
				$konter1 = addZero($tmpKonter1[0]['nokounter']+1,3);

				# Transform No Jurnal dari No Transaksi
				$tmpNoJurnal = explode('/',$param['notransaksi']);
				$nojurnal1 = $tmpNoJurnal[0]."/".$tmpNoJurnal[1]."/".$kodeJurnal1."/".$konter1;
				
				#cek apakah nomor ini sudah ada di keu_jurnalht atau belum ???
				$str = "SELECT * FROM ".$dbname.".`keu_jurnalht` WHERE nojurnal='".$nojurnal1."'";
				$res = fetchdata($str);
				if(count($res)>0){
					$str = "select max(convert(substring_index(nojurnal,'/',-1),unsigned integer)) as nomor from keu_jurnalht where kodejurnal='".$kodeJurnal1."' and nojurnal like '%".$_SESSION['empl']['lokasitugas']."%' and tanggal like '".$prdbln."%'";
					$res = fetchdata($str);
					$konter1 = addZero($res[0]['nomor']+1,3);
					$nojurnal1 = $tmpNoJurnal[0]."/".$tmpNoJurnal[1]."/".$kodeJurnal1."/".$konter1;
				}
				#======================== Nomor Jurnal =============================
				

				#=== Transform Data ===
				$dataResMat['header'] = array();
				$dataResMat['detail'] = array();

				#1. Data Header
				$dataResMat['header'] = array(
					'nojurnal'     =>$nojurnal1,
					'kodejurnal'   =>$kodeJurnal1,
					'tanggal'      =>$dataH[0]['tanggal'],
					'tanggalentry' =>date('Ymd'),
					'posting'      =>'1',
					'totaldebet'   =>'0',
					'totalkredit'  =>'0',
					'amountkoreksi'=>'0',
					'noreferensi'  =>$dataH[0]['notransaksi'],
					'autojurnal'   =>'1',
					'matauang'     =>'IDR',
					'kurs'         =>'1',
					'revisi'       =>'0'
				);    
				
				# Detail (kredit)
				$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
				  left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
				  where a.notransaksi='".$notranXXJ."' and a.kodegudang!='' and a.kodekegiatan='".$kegiatanXXJ."' and a.kodeorg='".$blokXXJ."'"; 
				//exit("error".$str);
				$noUrut = 1;
				$totalJumlah = 0;
				$errAkunBarang='';
				$namabarang='';
				$resx=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_OBJ); 
				while($bab=$resx->fetch()) {
					if(($bab->kwantitas<=0)||($bab->kwantitas=='')){
						$pesanError="Kuantitas Barang Tidak Boleh Kosong atau nol\n:";
						throw new PDOException("Qty ".$pesanError."\n".$errorDB);
					}

					#kredit
					#ambil periode akuntansi masing-masing gudang
					$strd="select periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$bab->kodegudang."' and tutupbuku=0";
					$resd=$owlPDO->query($strd) or die(print " Gagal: ".PDOException::getMessage());
					$resd->setFetchMode(PDO::FETCH_OBJ);
					while($bard=$resd->fetch()){
						$periode[$bab->kodegudang]=$bard->periode;
					}
					#harga harus di ambil dari tabel yang sama dengan harga log_transaksiht
					$hargarata=$salakhirbrg=$nilaisalakbrg='0';
					$stru="select * from ".$dbname.".log_5saldobulanan where kodegudang='".$bab->kodegudang."' and kodebarang='".$bab->kodebarang."' and periode='".$periode[$bab->kodegudang]."'"; 
					$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
					$resu->setFetchMode(PDO::FETCH_OBJ);
					while($baru=$resu->fetch()){
						$hargarata=abs($baru->hargarata);
						$salakhirbrg=abs($baru->saldoakhirqty);
						$nilaisalakbrg=abs($baru->nilaisaldoakhir);
					}
					
					
					#$harga[$bab->kodegudang][$bab->kodebarang]=$bab->hargasatuan;
					$namabarang=substr($bab->namabarang,0,25)." ".$bab->kwantitas." ".$bab->satuan;
					#if($harga[$bab->kodegudang][$bab->kodebarang]=='' or $harga[$bab->kodegudang][$bab->kodebarang]==0){
					if($hargarata=='' or $hargarata==0){
						throw new PDOException("Belum ada harga rata-rata barang ".$namabarang);
					}
					if(isset($akunbarang[substr($bab->kodebarang,0,3)]) and $akunbarang[substr($bab->kodebarang,0,3)]!=''){
						// ROUND2021
						if($bab->kwantitas==$salakhirbrg){
							$nilairound=floor($nilaisalakbrg);
						}else{							
							$nilairound=floor($hargarata*$bab->kwantitas);
						}
						
						$hargarata=$nilairound/$bab->kwantitas;
						// 'jumlah'      =>$hargarata*$bab->kwantitas*(-1),
						$dataResMat['detail'][] = array(
							'nojurnal'    =>$nojurnal1,
							'tanggal'     =>$dataH[0]['tanggal'],
							'nourut'      =>$noUrut,
							'noakun'      =>$akunbarang[substr($bab->kodebarang,0,3)],
							'keterangan'  =>'Material BKM '. $dataH[0]['notransaksi']." ".$namabarang,
							'jumlah'      =>$nilairound*(-1),
							'matauang'    =>'IDR',
							'kurs'        =>'1',
							'kodeorg'     =>$_SESSION['empl']['lokasitugas'],
							'kodekegiatan'=>'',
							'kodeasset'   =>'',
							'kodebarang'  =>$bab->kodebarang,
							'nik'         =>'',
							'kodecustomer'=>'',
							'kodesupplier'=>'',
							'noreferensi' =>$dataH[0]['notransaksi'],
							'noaruskas'   =>'',
							'kodevhc'     =>'',
							'nodok'       =>'',
							'kodeblok'    =>'',
							'revisi'      =>'0',
							'kodesegment' => $segment
						);  
						$noUrut++;
						#$totalJumlah +=$harga[$bab->kodegudang][$bab->kodebarang]*$bab->kwantitas;
						// $totalJumlah+=$hargarata*$bab->kwantitas;
						// ROUND2021
						$rpmatperbarang[$bab->kodegudang][$bab->kodebarang]+=$nilairound;
						$totalJumlah+=$nilairound;
						@$ttlqtykeuangan+=$bab->kwantitas;
					
					}else{
						throw new PDOException("Belum ada akun untuk barang ".$namabarang);
					}
				}
				
				if($totalJumlah>0){
					#debet
					$dataResMat['detail'][] = array(
						'nojurnal'    =>$nojurnal1,
						'tanggal'     =>$dataH[0]['tanggal'],
						'nourut'      =>$noUrut,
						'noakun'      =>$resKeg[$kegiatanXXJ]['akun'],
						'keterangan'  =>'Material BKM '.$dataH[0]['notransaksi'],
						'jumlah'      =>$totalJumlah,
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>substr($blokXXJ,0,4),
						'kodekegiatan'=>$kegiatanXXJ,
						'kodeasset'   =>'',
						'kodebarang'  =>$bab->kodebarang,
						'nik'         =>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$dataH[0]['notransaksi'],
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>$blokXXJ,
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
				}

				if($namabarang!=''){ // kalo transaksi BKM tanpa material, ga usah eksekusi jurnal barangnya
						
					# Total D/K
					$dataResMat['header']['totaldebet'] = $totalJumlah;
					$dataResMat['header']['totalkredit'] = $totalJumlah;

					#=== Insert Data jurnal material ===
					$errorDBX = "";    
					# Header
					$queryH = insertQuery($dbname,'keu_jurnalht',$dataResMat['header']);
					$owlPDO->exec($queryH);
					
					# Detail
					foreach($dataResMat['detail'] as $key=>$dataDet) {
						$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
						$owlPDO->exec($queryD);
					}
					
					$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter1[0]['nokounter']+1),
					"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal1."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and periode='".$prdbln."'");
					$owlPDO->exec($queryKonter);
				}		
			}
		}
		$no='';
		# ini insert ke log transaksi gudang
		foreach($dataXX as $notranXX => $valKegXX){
			$noxz=0;
			foreach($valKegXX as $kegiatanXX => $valBlokXX){
				foreach($valBlokXX as $blokXX => $valBrgXX){
					foreach($valBrgXX as $barangXX){
						$noxz+=1;
						#ambil notransaksi gudang
						$nomor=Array();    
						$str="select distinct kodegudang from ".$dbname.".kebun_pakaimaterial where notransaksi='".$notranXX."' and kodegudang!='' and kodekegiatan='".$kegiatanXX."' and kodeorg='".$blokXX."' and kodebarang='".$barangXX."'";
						$resc=fetchData($str);
						foreach($resc as $bar1){
							$gudang =$bar1['kodegudang'];
							$num=1;//default value 
							
							$str="select max(substr(notransaksi,8,4)) as notransaksi from ".$dbname.".log_transaksiht where tipetransaksi=5 and kodegudang='".$gudang."' and tanggal>=".$_SESSION['gudang'][$bar1['kodegudang']]['start']." and tanggal<=".$_SESSION['gudang'][$bar1['kodegudang']]['end']." and notransaksireferensi!='' order by notransaksi desc limit 1";
							$res=fetchData($str);
							foreach($res as $bar){
								$num=$bar['notransaksi'];
								if($num!=''){
									$num=$noxz+intval($num);
									$num=str_pad($num,4,"0",STR_PAD_LEFT);
								}else{
									$num="000".$noxz;
								}
							}
							#ambil periode akuntansi masing-masing gudang
							$strd="select periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$bar1['kodegudang']."' and tutupbuku=0";
							$resd=fetchData($strd);
							foreach($resd as $bard){
								$periode[$bar1['kodegudang']]=$bard['periode'];
							}
							$nomor[$bar1['kodegudang']]=substr($_SESSION['gudang'][$bar1['kodegudang']]['start'],0,4).substr($_SESSION['gudang'][$bar1['kodegudang']]['start'],4,2)."M".$num;
						}
						$brg=Array();
						$gud=Array();
						$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
						left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
						where a.notransaksi='".$param['notransaksi']."' and a.kodegudang!='' 
						and a.kodekegiatan='".$kegiatanXX."' and a.kodeorg='".$blokXX."' and a.kodebarang='".$barangXX."'";
						$resa=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$resa->setFetchMode(PDO::FETCH_OBJ);
						#ambil saldo dan harga rata
						while($barf=$resa->fetch()){
							$stru="select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from ".$dbname.".log_5saldobulanan where kodegudang='".$barf->kodegudang."' and kodebarang='".$barf->kodebarang."' and periode='".$periode[$barf->kodegudang]."'"; 
							$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
							$resu->setFetchMode(PDO::FETCH_OBJ);
							$saldo[$barf->kodegudang][$barf->kodebarang]=0;
							$harga[$barf->kodegudang][$barf->kodebarang]=0;
							$kodegudangxz[$blokXX]=$barf->kodegudang;
							while($baru=$resu->fetch()){
								$saldo[$barf->kodegudang][$barf->kodebarang]=$baru->saldoakhirqty;
								// $harga[$barf->kodegudang][$barf->kodebarang]=$baru->hargarata;
								$xkeluar[$barf->kodegudang][$barf->kodebarang]=$baru->qtykeluarxharga;
								$qtykeluar[$barf->kodegudang][$barf->kodebarang]=$baru->qtykeluar;
								$nilaisaldoakhir[$barf->kodegudang][$barf->kodebarang]=$baru->nilaisaldoakhir;
								// ROUND2021
								$harga[$barf->kodegudang][$barf->kodebarang]=$baru->nilaisaldoakhir/$baru->saldoakhirqty;
							}
						}
						
						$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
						left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
						where a.notransaksi='".$param['notransaksi']."' and a.kodegudang!='' 
						and a.kodekegiatan='".$kegiatanXX."' and a.kodeorg='".$blokXX."'";
						$res = fetchdata($str);
						foreach($res as $bar){
							$brg[$bar['kodegudang']][$bar['kodebarang']]=$bar['kodebarang'];
							$gud[$bar['kodegudang']]=$bar['kodegudang'];     
						}
						
						#insert ke log transaksi gudang#
						$str="select induk from ".$dbname.".organisasi where kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
						$kodeorganisasi="";
						$res=fetchData($str);
						foreach($res as $bar){
							$kodeorganisasi=$bar['induk'];
						}

						$awlheader=1;
						$str="select a.*,b.namabarang,b.satuan,a.kodekegiatan from ".$dbname.".kebun_pakaimaterial a left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang where a.notransaksi='".$notranXX."' and a.kodegudang!='' and a.kodekegiatan='".$kegiatanXX."' and a.kodeorg='".$blokXX."' and a.kodebarang='".$barangXX."'";

						$dataMat['header']=array();
						$dataMat['detail']=array();
						$resy=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$resy->setFetchMode(PDO::FETCH_OBJ);
						while($bar=$resy->fetch()){
							$num=$nomor[$bar->kodegudang]."-GI-".$bar->kodegudang;
							$dataMat['header'][$bar->kodegudang] = array(
								'tipetransaksi'       =>'5',
								'notransaksi'         =>$num, 
								'tanggal'             =>$dataH[0]['tanggal'], 
								'kodept'              =>$kodeorganisasi, 
								'untukpt'             =>$kodeorganisasi, 
								'nopo'                =>'', 
								'nosj'                =>'', 
								'keterangan'          =>'Material BKM ', 
								'statusjurnal'        =>'1', 
								'kodegudang'          =>$bar->kodegudang, 
								'user'                =>$_SESSION['standard']['userid'], 
								'namapenerima'        =>'0', 
								'mengetahui'          =>$_SESSION['standard']['userid'], 
								'idsupplier'          =>'', 
								'nofaktur'            =>'', 
								'post'                =>'1', 
								'postedby'            =>$_SESSION['standard']['userid'], 
								'untukunit'           =>$_SESSION['empl']['lokasitugas'], 
								'subunit'             =>'', 
								'notransaksireferensi'=>$param['notransaksi'], 
								'gudangx'             =>'',
								'persetujuan1'        =>'0',
								'hasilpersetujuan1'   =>'0',
								'tanggalpersetujuan1' =>date('Y-m-d H:i:s'),
								'persetujuan2'        =>'0',
								'hasilpersetujuan2'   =>'0',
								'tanggalpersetujuan2' =>date('Y-m-d H:i:s'),
								'namafile'            =>'',
								'departemen'          =>'',
								'karyawanid'          =>'',
								'lastupdate'          =>date('Y-m-d H:i:s'),
								'norequest'           =>'',
								'driver'              =>'',
								'hpdriver'            =>'',
								'nopol'               =>'',
								'jeniskendaraan'      =>'',
								'expeditor'           =>''
							);
							$dataMat['detail'][]=array(
								'notransaksi'   =>$num, 
								'nopp'          =>'', 
								'kodebarang'    =>$bar->kodebarang, 
								'satuan'        =>$bar->satuan, 
								'jumlah'        =>($bar->kwantitas<0?0:$bar->kwantitas),
								'jumlahlalu'    =>($saldo[$bar->kodegudang][$bar->kodebarang]<0?0:$saldo[$bar->kodegudang][$bar->kodebarang]), 
								'hargasatuan'   =>'0', 
								'kodeblok'      =>$blokXX,
								'waktutransaksi'=>date('Y-m-d H:i:s'),
								'updateby'      =>$_SESSION['standard']['userid'], 
								'kodekegiatan'  =>$bar->kodekegiatan, 
								'kodemesin'     =>'', 
								'statussaldo'   =>1, 
								'hargarata'     =>($harga[$bar->kodegudang][$bar->kodebarang]<0?0:$harga[$bar->kodegudang][$bar->kodebarang]),
								'nopo'          =>'',
								'kodesegment'   => $segment,
								'catatan'       => null,
								'namafile'      => '',
								'kmhm'          => '',
								'kodedptrmn'	=> ''
							);
							$awlheader++;
						}

						#periksa apakah saldo mencukupi:
						$str="select a.notransaksi, a.kodebarang, a.kodegudang, sum(a.kwantitas) as kwantitas,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
						left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
						where a.notransaksi='".$notranXX."' and a.kodegudang='".$kodegudangxz[$blokXX]."' and a.kodebarang='".$barangXX."' group by a.kodebarang, a.notransaksi,a.kodegudang 
						";
						$errsaldo='';
						$resku=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$resku->setFetchMode(PDO::FETCH_OBJ);
						while($barf=$resku->fetch()){
							# cek saldo apakah cukup, di round desimal 2 untuk ngatasin mysql sum problem 0.88 => 0.8800000000000001 
							$jumlah[$barf->kodegudang][$barf->kodebarang]=round($barf->kwantitas,5);
							
							if($saldo[$barf->kodegudang][$barf->kodebarang] < @$jumlah[$barf->kodegudang][$barf->kodebarang] or $harga[$barf->kodegudang][$barf->kodebarang] <= 0){
								$nmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$barf->kodebarang."'");
								$nmsat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$barf->kodebarang."'");
								
								$errsaldo="Saldo untuk barang ".$nmbarang[$barf->kodebarang]." di gudang ".$barf->kodegudang." periode ".$periode[$barf->kodegudang]." tidak cukup.\n => Jumlah saldo tersedia : ".$saldo[$barf->kodegudang][$barf->kodebarang]." ".$nmsat[$barf->kodebarang]."\n => Pemakaian material : ".@$jumlah[$barf->kodegudang][$barf->kodebarang]." ".$nmsat[$barf->kodebarang];
								throw new PDOException($errsaldo);
							}
						}

						$errSal='';
						$ttlqtygudang=$ttlrpgudang='0';
						foreach($gud as $keygud=>$valgud){
							foreach($brg[$valgud] as $keybrg=>$valbrg){
								#ini buat cek qty & rp di Gudang vs Akutansi
								@$ttlqtygudang+=($jumlah[$valgud][$valbrg]);
								//$ttlrpgudang+=(($jumlah[$valgud][$valbrg])*$harga[$valgud][$valbrg]);
								$ttlrpgudang+=$rpmatperbarang[$valgud][$valbrg];
							}
						}
						
						$errorY='';
						$errorX='';
						#insert transaksi gudang ht
						foreach($dataMat['header'] as $key=>$dataX) {
							$queryD = insertQuery($dbname,'log_transaksiht',$dataX);
							$owlPDO->exec($queryD);
						}
						#insert transaksi gudang dt
						foreach($dataMat['detail'] as $key=>$dataY) {
							$queryD = insertQuery($dbname,'log_transaksidt',$dataY);
							$owlPDO->exec($queryD);              
						}
					}
				}	
			}
		}
	}

foreach($dataXX as $notranXX => $valKegXX){
	foreach($valKegXX as $kegiatanXX => $valBlokXX){
		foreach($valBlokXX as $blokXX => $valBrgXX){
			foreach($valBrgXX as $barangXX){
				$brg=Array();
				$gud=Array();
				$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
				left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
				where a.notransaksi='".$param['notransaksi']."' and a.kodegudang!='' 
				and a.kodekegiatan='".$kegiatanXX."' and a.kodeorg='".$blokXX."'";
				$res = fetchdata($str);
				foreach($res as $bar){
					$brg[$bar['kodegudang']][$bar['kodebarang']]=$bar['kodebarang'];
					$gud[$bar['kodegudang']]=$bar['kodegudang'];     
				}
				
				$errSal='';
				#$ttlqtygudang='';
				#$ttlrpgudang='';
				foreach($gud as $keygud=>$valgud){
					foreach($brg[$valgud] as $keybrg=>$valbrg){
						$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$valbrg."'");
						#pastikan tidak ada minus diantara kita
						if($saldo[$valgud][$valbrg]-$jumlah[$valgud][$valbrg]<0){
							throw new PDOException("Nilai kuantitas saldo tidak mencukupi.");
						}
						// ROUND2021
						// nilai saldo akhir = 			57001.48148148 // 20211123/KBPE/BKM/016
						// keluar 2 x 28500.87037037 = 	57002 (2*28501)
						//$xsisa=round($nilaisaldoakhir[$valgud][$valbrg]-($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg]));
						
						$xsisa=round($nilaisaldoakhir[$valgud][$valbrg]-$rpmatperbarang[$valgud][$valbrg]);
						// sebelumnya diginiin, hasilnya jadi -0 < 0
			// if(round($nilaisaldoakhir[$valgud][$valbrg]-($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg]))<0){
						if($xsisa<0){
							//throw new PDOException($xsisa." Nilai rupiah saldo tidak mencukupi.\nRupiah saldo : ".number_format($nilaisaldoakhir[$valgud][$valbrg])."\nRupiah pakai : ".number_format($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg])."\nNama barang : ".$optnmbrg[$valbrg]."");
							throw new PDOException($xsisa." Nilai rupiah saldo tidak mencukupi.\nRupiah saldo : ".number_format($nilaisaldoakhir[$valgud][$valbrg])."\nRupiah pakai : ".number_format($rpmatperbarang[$valgud][$valbrg])."");
						}
						
						// ROUND2021
						// $nilairound=round($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg]);
						// if($saldo[$valgud][$valbrg]==$jumlah[$valgud][$valbrg]){ // kalo barang terakhir pake floor
							// $nilairound=floor($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg]);
						// }
						
						#ambil rupiah dari rp jurnal
						$nilairound=$rpmatperbarang[$valgud][$valbrg];
						
						$harga[$valgud][$valbrg]=$nilairound/$jumlah[$valgud][$valbrg];

						$salqty       = $rpsalakhir=$qtyout=$rpout="0";      
						$salqty       =($saldo[$valgud][$valbrg]-$jumlah[$valgud][$valbrg]);
						// $rpsalakhir=($nilaisaldoakhir[$valgud][$valbrg]-($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg]));
						$rpsalakhir   =($nilaisaldoakhir[$valgud][$valbrg]-$nilairound);
						$qtyout       =($qtykeluar[$valgud][$valbrg]+$jumlah[$valgud][$valbrg]);
						// $rpout     =($xkeluar[$valgud][$valbrg]+($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg]));
						$rpout        =($xkeluar[$valgud][$valbrg]+$nilairound);
						
						
						if($salqty==0 and $rpsalakhir<0){$rpsalakhir=0;}
						
						if($salqty<0){throw new PDOException("Nilai saldo akhir salah, proses dibatalkan.\n\nNama barang : ".$optnmbrg[$valbrg]."\nNilai : ".$salqty."");}
						if($rpsalakhir<0){throw new PDOException("Nilai rupiah saldo akhir salah, proses dibatalkan.\n\nNama barang : ".$optnmbrg[$valbrg]."\nNilai : ".$rpsalakhir."");}
						if($qtyout<0){throw new PDOException("Nilai qty keluar salah, proses dibatalkan.\n\nNama barang : ".$optnmbrg[$valbrg]."\nNilai : ".$qtyout."");}
						if($rpout<0){throw new PDOException("Nilai rupiah keluar salah, proses dibatalkan.\n\nNama barang : ".$optnmbrg[$valbrg]."\nNilai : ".$rpout."");}
						#if($hargarata<0){throw new PDOException("Nilai harga rata - rata salah, proses dibatalkan.");}
						
						if(intval($salqty)<0){throw new PDOException("Nilai saldo akhir salah, proses dibatalkan.\n\nNama barang : ".$optnmbrg[$valbrg]."\nNilai : ".$salqty."");}
						
						$sth="update ".$dbname.".log_5saldobulanan 
						set saldoakhirqty=".$salqty.", 
						nilaisaldoakhir=".$rpsalakhir.",
						qtykeluar=".$qtyout.",
						qtykeluarxharga=".$rpout."
						where periode='".$periode[$valgud]."' and kodegudang='".$valgud."' and kodebarang='".$valbrg."'"; 
						$owlPDO->exec($sth);

						// ROUND2021 update harga di transaksidt
						$stcx="update ".$dbname.".log_transaksidt
						set hargarata='".$harga[$valgud][$valbrg]."' where kodebarang='".$valbrg."' and notransaksi in (
							SELECT notransaksi
							FROM ".$dbname.".log_transaksiht
							WHERE `notransaksireferensi` = '".$param['notransaksi']."' and kodegudang = '".$valgud."'
						)"; 
						$owlPDO->exec($stcx);
						
						#ini buat cek qty & rp di Gudang vs Akutansi
						#@$ttlqtygudang+=($jumlah[$valgud][$valbrg]);
						#@$ttlrpgudang+=(($jumlah[$valgud][$valbrg])*$harga[$valgud][$valbrg]);
						
						$stup="update ".$dbname.".kebun_pakaimaterial set hargasatuan=".$harga[$valgud][$valbrg]." where kodegudang='".$valgud."' and kodebarang='".$valbrg."' and notransaksi='".$param['notransaksi']."'";
						$owlPDO->exec($stup);
						
						
						#cek lagi saldo apakah ada minus di log_5saldobulanan
						// Nilai saldoawalqty salah.2021-05 KBPE56 312020010 -0.0000000000000017763568394003
						// diantisipasi dengan round($angka,5)
						$str="select * from ".$dbname.".log_5saldobulanan where periode='".$periode[$valgud]."' and kodegudang='".$valgud."' and kodebarang='".$valbrg."'";
						$res = fetchdata($str);
						foreach($res as $bar){
							if(round($bar['saldoakhirqty'],5)<0){
								throw new PDOException("Nilai saldoakhirqty salah. ".$periode[$valgud].' '.$valgud.' '.$valbrg.' '.round($bar['saldoakhirqty'],4));
							}
							if(round($bar['nilaisaldoakhir'],5)<0){
								throw new PDOException("Nilai nilaisaldoakhir salah. ".$periode[$valgud].' '.$valgud.' '.$valbrg.' '.round($bar['nilaisaldoakhir'],5));
							}
							if(round($bar['qtykeluar'],5)<0){
								throw new PDOException("Nilai qtykeluar salah. ".$periode[$valgud].' '.$valgud.' '.$valbrg.' '.round($bar['qtykeluar'],5));
							}
							if(round($bar['qtykeluarxharga'],5)<0){
								throw new PDOException("Nilai qtykeluarxharga salah. ".$periode[$valgud].' '.$valgud.' '.$valbrg.' '.round($bar['qtykeluarxharga'],5));
							}
							if(round($bar['saldoawalqty'],5)<0){
								throw new PDOException("Nilai saldoawalqty salah. ".$periode[$valgud].' '.$valgud.' '.$valbrg.' '.round($bar['saldoawalqty'],5));
							}
							if(round($bar['nilaisaldoawal'],5)<0){
								throw new PDOException("Nilai nilaisaldoawal salah. ".$periode[$valgud].' '.$valgud.' '.$valbrg.' '.round($bar['nilaisaldoawal'],5));
							}
						}
						
					}
				}
				#update log_5masterbarangdt
				$n='0';
				foreach($gud as $keygud=>$valgud){
					foreach($brg[$valgud] as $keybrg=>$valbrg){
						$n++;
						$strg="update ".$dbname.".log_5masterbarangdt set saldoqty=".($saldo[$valgud][$valbrg]-$jumlah[$valgud][$valbrg]).",hargalastout=".$harga[$valgud][$valbrg]." where kodegudang='".$valgud."' and kodebarang='".$valbrg."'";
						$owlPDO->exec($strg);                         
				   }  
				}
			}
		}
	}
}

	# cek qty gudang vs acct
	if(($ttlqtygudang-$ttlqtykeuangan)>1){
		throw new PDOException("Qty gudang vs Qty Accounting tidak sama, selisih : ".($ttlqtygudang-$ttlqtykeuangan));
	}

	# cek rupiah gudang vs acct
	if(($totalJumlah-$ttlrpgudang)>1){
		throw new PDOException("Rupiah gudang vs Rupiah Accounting tidak sama, selisih : ".($totalJumlah-$ttlrpgudang) ." Gudang : ".$ttlrpgudang." dan Accounting : ".$totalJumlah);
	}
} #tutup if $adamat


#=== Switch Jurnal to 1 ===
# Cek if already posted
$queryJ = selectQuery($dbname,'vhc_spl_aktifitas',"jurnal","notransaksi='".
	$param['notransaksi']."'");
$isJ = fetchData($queryJ);
if($isJ[0]['jurnal']==1) {
	throw new PDOException("Data posted by another user");
} else {
	$queryToJ = updateQuery($dbname,'vhc_spl_aktifitas',array('jurnal'=>1),"notransaksi='".$dataH[0]['notransaksi']."'");
	$owlPDO->exec($queryToJ);
}

# exekusi query di atas !!!
	$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error , " . addslashes($e->getMessage());
	die();
} 

#ada jurnal yg isinya kosong, kalau pakai if di atas banyak kali, nah solusinya adalah hapus saja jurnalnya
$str="delete from ".$dbname.".keu_jurnalht where noreferensi='".$dataH[0]['notransaksi']."' and totaldebet='0' and totalkredit='0' and nojurnal not in (select nojurnal from ".$dbname.".keu_jurnaldt)";
try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }


$str="delete from ".$dbname.".keu_jurnaldt where noreferensi='".$dataH[0]['notransaksi']."' and jumlah='0'";
try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

echo "Posting Sukses.";