<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;
#cek apakah 1 no transaksi terdiri dari beberapa keg dan blok
$str = "SELECT * FROM " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."'"; #exit('error'.$str);
$ceckD = fetchData($str);
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$dataX[$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']]=$bar['kodeorg'];
}
$error1='';
if(count($ceckD)==0) {
	$error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
}
if($error1!='') {
	exit("warning : ".$error1);
}
		
try {
	$owlPDO->beginTransaction();
$arrkeg=array();
foreach($dataX as $notranX => $valKegX){
	foreach($valKegX as $kegiatanX => $valBlokX){
		foreach($valBlokX as $blokX){
		#=== Get Data ===
		# Header

		$qBlok = selectQuery($dbname,'setup_blok','statusblok',"kodeorg='".$blokX."'");
        $resBlok = fetchData($qBlok);

		$queryH = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".
			$param['notransaksi']."'");
		$dataH = fetchData($queryH);



		#====cek periode===============================
		$tgl = str_replace("-","",$dataH[0]['tanggal']);
		if($_SESSION['org']['period']['start']>$tgl)
			throw new PDOException('Tanggal diluar periode aktif');

		# Prestasi
		$queryD="select notransaksi,kodekegiatan,kodeorg,tahuntanam, sum(hasilkerja) as hasilkerja, sum(jumlahhk) as jumlahhk, sum(upahkerja) as upahkerja, sum(upahpenalty) as upahpenalty, sum(upahpremi) as upahpremi, sum(upahpremilebihbasis) as upahpremilebihbasis, sum(premibasis) as premibasis, sum(umr) as umr, sum(rupiahpenalty) as rupiahpenalty, kodesegment from ".$dbname.".kebun_prestasi where notransaksi='".$notranX."' and kodekegiatan='".$kegiatanX."' and kodeorg='".$blokX."' group by notransaksi, kodekegiatan, kodeorg";
		$dataD = fetchData($queryD);

		
		@$arrkeg[$resBlok[0]['statusblok']][$blokX][$kegiatanX]+=$dataD[0]['hasilkerja'];
		# Absensi
		$queryAbs = "SELECT a.jhk,a.umr,a.insentif,a.penalty,a.nik FROM " . $dbname . ".kebun_kehadiran a 
		left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi and a.nourut=b.nourut 
		where a.notransaksi='".$notranX."' and b.kodekegiatan='".$kegiatanX."' and b.kodeorg='".$blokX."'";
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
		$costRawat = 0;
		$costRawatPre = 0;
		$penaltyRawat=0;
		$totalHk = 0;
		if(!empty($dataAbs)) {
			foreach($dataAbs as $row) {
				$costRawat += $row['umr'];
				$costRawatPre += $row['insentif'];
				$penaltyRawat += $row['penalty'];
				$totalHk += $row['jhk'];
			}
		}

		#=== Cek if HK belum sama ===
		$totalHk=round($totalHk,2);                             // diround hingga 2 desimal
		$dataD[0]['jumlahhk']=round($dataD[0]['jumlahhk'],2);   // diround hingga 2 desimal
		$qwe=$totalHk-$dataD[0]['jumlahhk'];
		if($totalHk!=$dataD[0]['jumlahhk']) {
			throw new PDOException("HK Prestasi belum teralokasi dengan lengkap ".$qwe."");
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
		$str = "select status from ".$dbname.".sdm_5aktivasifp where kodeorg='".$dataH[0]['kodeorg']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$statusfp=$bar['status'];//1 aktif,0 tidak

		if($statusfp==1){
			foreach($arrUpload as $row){
				$str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$row['nik']."' and tanggalabsen='".($dataH[0]['tanggal'])."' limit 1";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				if($row['nik'] != $bar['karyawanid']){
					$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$row['nik']."'");
					$nikkary = makeOption($dbname,"datakaryawan",'karyawanid,nik',"karyawanid='".$row['nik']."'");
					$errorUpload .= $nikkary[$row['nik']]." = ".$optNamaKaryawan[$row['nik']]."\n";
					$countUpload = $countUpload + 1;
				}
			}
			if($countUpload > 0){
				throw new PDOException("Absen fingerprint untuk karyawan dg NIK : \n".$errorUpload."belum diupload.");
			}
		}

		#===================================================================
		$lstUnit=array();
		$lstUph=array();
		$lstPre=array();
		$statpen=1;
		$statPre=1;
		$sDr="select lokasitugas,sum(a.umr) as uphtot,sum(a.insentif) as premi, sum(a.penalty) as penalty,kodeorganisasi as kodept from ".$dbname.".kebun_kehadiran a 
			  left join ".$dbname.".datakaryawan b on a.nik=b.karyawanid
			  left join " . $dbname . ".kebun_prestasi c on a.notransaksi=c.notransaksi and a.nourut=c.nourut 
			  where a.notransaksi='".$notranX."' and c.kodekegiatan='".$kegiatanX."' and c.kodeorg='".$blokX."'
			  group by lokasitugas";
		$rDr=fetchdata($sDr);
		if(count($rDr)!=0){
			foreach($rDr as $row=>$lstData){
				if(abs($lstData['uphtot'])>0){
					$lstUph[$lstData['lokasitugas']]=$lstData['uphtot'];					
				}
				if(abs($lstData['premi'])>0){
					$lstPre[$lstData['lokasitugas']]=$lstData['premi'];					
				}
				if(abs($lstData['penalty'])>0){
					$lstPent[$lstData['lokasitugas']]=$lstData['penalty'];					
				}
				$lstUnit[$lstData['lokasitugas']]=$lstData['lokasitugas'];
				if($lstData['penalty']==0){
					$statpen=0;
				}
				if($lstData['premi']==0){
					$statPre=0;
				}
				$lstPt[$lstData['lokasitugas']]=$lstData['kodept'];
			}
		}

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
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
		$tmpKonter = fetchData($queryJ);
		$konter = addZero($tmpKonter[0]['nokounter']+1,3);

		# Transform No Jurnal dari No Transaksi
		$tmpNoJurnal = explode('/',$param['notransaksi']);
		$nojurnal = $tmpNoJurnal[0]."/".$tmpNoJurnal[1]."/".$kodeJurnal."/".$konter;

		#======================== Nomor Jurnal Penalty =============================
		$kodeJurnalpen = 'PPRWT';
		
		# Get Journal Counter
		$queryJpen = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnalpen."'");
		$tmpKonterpen = fetchData($queryJpen);
		$konterpen = addZero($tmpKonterpen[0]['nokounter']+1,3);

		# Transform No Jurnal dari No Transaksi
		$tmpNoJurnalpen = explode('/',$param['notransaksi']);
		$nojurnalpen = $tmpNoJurnalpen[0]."/".$tmpNoJurnalpen[1]."/".$kodeJurnalpen."/".$konterpen;
		#======================== Nomor Jurnal Premi =============================
		$kodeJurnalPre = 'M9';
		
		# Get Journal Counter
		$queryJpre = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnalPre."'");
		$tmpKonterPre = fetchData($queryJpre);
		$konterpre = addZero($tmpKonterPre[0]['nokounter']+1,3);

		# Transform No Jurnal dari No Transaksi
		$tmpNoJurnalpre = explode('/',$param['notransaksi']);
		$nojurnalpre = $tmpNoJurnalpre[0]."/".$tmpNoJurnalpre[1]."/".$kodeJurnalPre."/".$konterpre;
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
			'nojurnal'=>$nojurnal,
			'kodejurnal'=>'M0',
			'tanggal'=>$dataH[0]['tanggal'],
			'tanggalentry'=>date('Ymd'),
			'posting'=>'1',
			'totaldebet'=>'0',
			'totalkredit'=>'0',
			'amountkoreksi'=>'0',
			'noreferensi'=>$dataH[0]['notransaksi'],
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);

		#1. Data Header Penalti
		$dataResPen['header'] = array(
			'nojurnal'=>$nojurnalpen,
			'kodejurnal'=>'PPRWT',
			'tanggal'=>$dataH[0]['tanggal'],
			'tanggalentry'=>date('Ymd'),
			'posting'=>'1',
			'totaldebet'=>'0',
			'totalkredit'=>'0',
			'amountkoreksi'=>'0',
			'noreferensi'=>$dataH[0]['notransaksi'],
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);
		
		#1. Data Header Premi
		$dataResPre['header'] = array(
			'nojurnal'=>$nojurnalpre,
			'kodejurnal'=>'M9',
			'tanggal'=>$dataH[0]['tanggal'],
			'tanggalentry'=>date('Ymd'),
			'posting'=>'1',
			'totaldebet'=>'0',
			'totalkredit'=>'0',
			'amountkoreksi'=>'0',
			'noreferensi'=>$dataH[0]['notransaksi'],
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
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
		$noUrut = 1;
		$noUrut2 = 1;
		$totalJumlah = 0;
		$totRpRK=0;
		$hasilkerja = 0;
		$kodeblok='';
		$kodekegiatan='';
		$dataResRk=array();

		# Detail (Debet) Penalty
		$noUrutPen = 1;
		$noUrut2Pen = 1;
		$totalJumlahPen = 0;
		$totRpRKPen=0;
		$kodeblokPen='';
		$kodekegiatanPen='';
		$dataResRkPen=array();
		
		# Detail (Debet) Premi
		$noUrutPre = 1;
		$noUrut2Pre = 1;
		$totalJumlahPre = 0;
		$totRpRKPre=0;
		$kodeblokPre='';
		$kodekegiatanPre='';
		$dataResRkPre=array();

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
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut,
						'noakun'=>$resKeg[$row['kodekegiatan']]['akun'],
						'keterangan'=>'Upah Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'=>$lstUph[$rwUnit],
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>$dataD[0]['kodeorg'],
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$noUrut+=1;
					# Detail (Kredit)  disisi pengguna karyaawan
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut,
						'noakun'=>$aknPt[$rwUnit],
						'keterangan'=>'Upah Pemeliharaan '.$dataH[0]['tipetransaksi'],
						'jumlah'=>$lstUph[$rwUnit]*(-1),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$noUrut+=1;
				
					# Get Journal Counter
					$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
						"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M'");
					$tmpKonter2 = fetchData($queryJ);
					$konter = addZero($tmpKonter2[0]['nokounter']+1,3);
					$counterDt[$rwUnit]=intval($tmpKonter2[0]['nokounter'])+1;
					# Transform No Jurnal dari No Transaksi
					$tmpNoJurnal = explode('/',$param['notransaksi']);
					$nojurnal2 = $tmpNoJurnal[0]."/".$rwUnit."/M/".$konter;
					if($temp!=$rwUnit){
						$temp=$rwUnit;
						#1. Data Header
						$dataResRk['header'][] = array(
							'nojurnal'=>$nojurnal2,
							'kodejurnal'=>'M',
							'tanggal'=>$dataH[0]['tanggal'],
							'tanggalentry'=>date('Ymd'),
							'posting'=>'1',
							'totaldebet'=>'0',
							'totalkredit'=>'0',
							'amountkoreksi'=>'0',
							'noreferensi'=>$dataH[0]['notransaksi'],
							'autojurnal'=>'1',
							'matauang'=>'IDR',
							'kurs'=>'1',
							'revisi'=>'0'
						);
					}
					
					#debet disisi pemilik karyaawan
					$dataResRk['detail'][] = array(
						'nojurnal'=>$nojurnal2,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut2,
						'noakun'=>$aknHtg[$tmpNoJurnal[1]],
						'keterangan'=>'Upah Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'=>$lstUph[$rwUnit],
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$rwUnit,
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>$dataD[0]['kodeorg'],
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$noUrut2+=1;
					# Detail (Kredit)  disisi pemilik karyaawan
					$dataResRk['detail'][] = array(
						'nojurnal'=>$nojurnal2,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut2,
						'noakun'=>$resParam[0]['noakunkredit'],
						'keterangan'=>'Upah Pemeliharaan '.$dataH[0]['tipetransaksi'],
						'jumlah'=>$lstUph[$rwUnit]*(-1),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$rwUnit,
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$totRpRK+=$lstUph[$rwUnit];
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
						'nojurnal'=>$nojurnalpen,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrutPen,
						'noakun'=>$aknPt[$rwUnit],
						'keterangan'=>'Penalti Pemeliharaan '.$dataH[0]['tipetransaksi'],
						'jumlah'=>$lstPent[$rwUnit],
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$noUrutPen+=1;
					# Detail (Kredit)  disisi pengguna karyaawan
					$dataResPen['detail'][] = array(
						'nojurnal'=>$nojurnalpen,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrutPen,
						'noakun'=>$resKeg[$row['kodekegiatan']]['akun'],
						'keterangan'=>'Penalti Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'=>$lstPent[$rwUnit]*(-1),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>$dataD[0]['kodeorg'],
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$noUrutPen+=1;

					# Get Journal Counter
					$queryJpen = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
						"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M'");
					$tmpKonter2pen = fetchData($queryJpen);
					$konter2pen = addZero($tmpKonter2pen[0]['nokounter']+1,3);
					$counterDtpen[$rwUnit]=intval($tmpKonter2pen[0]['nokounter'])+2;
					# Transform No Jurnal dari No Transaksi
					$tmpNoJurnal2pen = explode('/',$param['notransaksi']);
					$nojurnal2pen = $tmpNoJurnal2pen[0]."/".$rwUnit."/M/".$konter2pen;
					if($temp!=$rwUnit){
						$temp=$rwUnit;
						#1. Data Header
						$dataResRkPen['header'][] = array(
							'nojurnal'=>$nojurnal2pen,
							'kodejurnal'=>'M',
							'tanggal'=>$dataH[0]['tanggal'],
							'tanggalentry'=>date('Ymd'),
							'posting'=>'1',
							'totaldebet'=>'0',
							'totalkredit'=>'0',
							'amountkoreksi'=>'0',
							'noreferensi'=>$dataH[0]['notransaksi'],
							'autojurnal'=>'1',
							'matauang'=>'IDR',
							'kurs'=>'1',
							'revisi'=>'0'
						);
					}
					
					#debet disisi pemilik karyaawan
					$dataResRkPen['detail'][] = array(
						'nojurnal'=>$nojurnal2pen,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut2Pen,
						'noakun'=>$resParam[0]['noakunkredit'],
						//'noakun'=>$aknHtg[$tmpNoJurnal[1]],
						'keterangan'=>'Penalti Pemeliharaan '.$dataH[0]['tipetransaksi'],
						'jumlah'=>$lstPent[$rwUnit],
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$rwUnit,
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$noUrut2Pen+=1;
					# Detail (Kredit)  disisi pemilik karyaawan
					$dataResRkPen['detail'][] = array(
						'nojurnal'=>$nojurnal2pen,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut2Pen,
						'noakun'=>$aknHtg[$tmpNoJurnal[1]],
						//'noakun'=>$resParam[0]['noakunkredit'],
						'keterangan'=>'Penalti Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'=>$lstPent[$rwUnit]*(-1),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$rwUnit,
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>$dataD[0]['kodeorg'],
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$totRpRKPen+=$lstPent[$rwUnit];
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
						'nojurnal'=>$nojurnalpre,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrutPre,
						'noakun'=>$resKeg[$row['kodekegiatan']]['akun'],
						'keterangan'=>'Premi Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'=>$lstPre[$rwUnit],
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>$dataD[0]['kodeorg'],
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$noUrutPre+=1;
					# Detail (Kredit)  disisi pengguna karyaawan
					$dataResPre['detail'][] = array(
						'nojurnal'=>$nojurnalpre,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrutPre,
						'noakun'=>$aknPt[$rwUnit],
						'keterangan'=>'Premi Pemeliharaan '.$dataH[0]['tipetransaksi'],
						'jumlah'=>$lstPre[$rwUnit]*(-1),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$_SESSION['empl']['lokasitugas'],
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$noUrutPre+=1;
					# Get Journal Counter
					$queryJpre = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
						"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M'");
					$tmpKonter2pre = fetchData($queryJpre);
					$konterpre = addZero($tmpKonter2pre[0]['nokounter']+1,3);
					$counterDtpre[$rwUnit]=intval($tmpKonter2pre[0]['nokounter'])+1;
					# Transform No Jurnal dari No Transaksi
					$tmpNoJurnalpre = explode('/',$param['notransaksi']);
					$nojurnal2pre = $tmpNoJurnalpre[0]."/".$rwUnit."/M/".$konterpre;
					if($temp!=$rwUnit){
						$temp=$rwUnit;
						#1. Data Header
						$dataResRkPre['header'][] = array(
							'nojurnal'=>$nojurnal2pre,
							'kodejurnal'=>'M',
							'tanggal'=>$dataH[0]['tanggal'],
							'tanggalentry'=>date('Ymd'),
							'posting'=>'1',
							'totaldebet'=>'0',
							'totalkredit'=>'0',
							'amountkoreksi'=>'0',
							'noreferensi'=>$dataH[0]['notransaksi'],
							'autojurnal'=>'1',
							'matauang'=>'IDR',
							'kurs'=>'1',
							'revisi'=>'0'
						);
					}
					
					#debet disisi pemilik karyaawan
					$dataResRkPre['detail'][] = array(
						'nojurnal'=>$nojurnal2pre,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut2Pre,
						'noakun'=>$aknHtg[$tmpNoJurnal[1]],
						'keterangan'=>'Premi Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
						'jumlah'=>$lstPre[$rwUnit],
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$rwUnit,
						'kodekegiatan'=>$row['kodekegiatan'],
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>$dataD[0]['kodeorg'],
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$noUrut2Pre+=1;
					# Detail (Kredit)  disisi pemilik karyaawan
					$dataResRkPre['detail'][] = array(
						'nojurnal'=>$nojurnal2pre,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut2Pre,
						'noakun'=>$resParam[0]['noakunkredit'],
						'keterangan'=>'Premi Pemeliharaan '.$dataH[0]['tipetransaksi'],
						'jumlah'=>$lstPre[$rwUnit]*(-1),
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$rwUnit,
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => $segment
					);
					$totRpRKPre+=$lstPre[$rwUnit];
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
		if(count($lstUnit[$_SESSION['empl']['lokasitugas']])!=0){
			foreach($dataD as $row) {
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$dataH[0]['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>$resKeg[$row['kodekegiatan']]['akun'],
					'keterangan'=>'Upah Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
					'jumlah'=>($costRawat-$totRpRK),
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>substr($row['kodeorg'],0,4),
					'kodekegiatan'=>$row['kodekegiatan'],
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$dataH[0]['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>'',
					'kodeblok'=>$row['kodeorg'],
					'revisi'=>'0',
					'kodesegment' => $segment
				);
				$totalJumlah +=($costRawat-$totRpRK);
				$noUrut++;
				$kodeblok=$row['kodeorg'];
				$kodekegiatan=$row['kodekegiatan'];
				$hasilkerja = $row['hasilkerja'];
			}

			# Detail (Kredit)
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$dataH[0]['tanggal'],
				'nourut'=>$noUrut,
				'noakun'=>$resParam[0]['noakunkredit'],
				'keterangan'=>'Upah Pemeliharaan '.$dataH[0]['tipetransaksi'],
				'jumlah'=>$totalJumlah*(-1),
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$_SESSION['empl']['lokasitugas'],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$dataH[0]['notransaksi'],
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => $segment
			);
			# Total D/K
		}
		#Penalty
		if(count($lstUnit[$_SESSION['empl']['lokasitugas']])!=0){
			foreach($dataD as $row) {
				$dataResPen['detail'][] = array(
					'nojurnal'=>$nojurnalpen,
					'tanggal'=>$dataH[0]['tanggal'],
					'nourut'=>$noUrutPen,
					'noakun'=>$resParam[0]['noakunkredit'],
					'keterangan'=>'Penalti Pemeliharaan '.$dataH[0]['tipetransaksi'],
					'jumlah'=>($penaltyRawat-$totRpRKPen),
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>substr($row['kodeorg'],0,4),
					'kodekegiatan'=>$row['kodekegiatan'],
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$dataH[0]['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>'',
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => $segment
				);
				$totalJumlahPen +=($penaltyRawat-$totRpRKPen);
				$noUrutPen++;
				$kodeblokPen=$row['kodeorg'];
				$kodekegiatanPen=$row['kodekegiatan'];
			}

			# Detail (Kredit)
			$dataResPen['detail'][] = array(
				'nojurnal'=>$nojurnalpen,
				'tanggal'=>$dataH[0]['tanggal'],
				'nourut'=>$noUrutPen,
				'noakun'=>$resKeg[$row['kodekegiatan']]['akun'],
				'keterangan'=>'Penalti Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
				'jumlah'=>$totalJumlahPen*(-1),
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$_SESSION['empl']['lokasitugas'],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$dataH[0]['notransaksi'],
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>$row['kodeorg'],
				'revisi'=>'0',
				'kodesegment' => $segment
			);
			# Total D/K
		}
		
		#Premi
		if(count($lstUnit[$_SESSION['empl']['lokasitugas']])!=0){
			foreach($dataD as $row) {
				$dataResPre['detail'][] = array(
					'nojurnal'=>$nojurnalpre,
					'tanggal'=>$dataH[0]['tanggal'],
					'nourut'=>$noUrutPre,
					'noakun'=>$resKeg[$row['kodekegiatan']]['akun'],
					'keterangan'=>'Premi Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
					'jumlah'=>($costRawatPre-$totRpRKPre),
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>substr($row['kodeorg'],0,4),
					'kodekegiatan'=>$row['kodekegiatan'],
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$dataH[0]['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>'',
					'kodeblok'=>$row['kodeorg'],
					'revisi'=>'0',
					'kodesegment' => $segment
				);
				$totalJumlahPre +=($costRawatPre-$totRpRKPre);
				$noUrutPre++;
				$kodeblokpre=$row['kodeorg'];
				$kodekegiatanpre=$row['kodekegiatan'];
				$hasilkerjapre = $row['hasilkerja'];
			}

			# Detail (Kredit)
			$dataResPre['detail'][] = array(
				'nojurnal'=>$nojurnalpre,
				'tanggal'=>$dataH[0]['tanggal'],
				'nourut'=>$noUrutPre,
				'noakun'=>$resParam[0]['noakunkredit'],
				'keterangan'=>'Premi Pemeliharaan '.$dataH[0]['tipetransaksi'],
				'jumlah'=>$totalJumlahPre*(-1),
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$_SESSION['empl']['lokasitugas'],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$dataH[0]['notransaksi'],
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => $segment
			);
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
		$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
		$owlPDO->exec($queryH);
		
		if($statpen==1){
			# Header Penalti
			$queryx = insertQuery($dbname,'keu_jurnalht',$dataResPen['header']);
			$owlPDO->exec($queryx);
		}
		if($statPre==1){
			# Header Premi
			$queryn = insertQuery($dbname,'keu_jurnalht',$dataResPre['header']);
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
		if(count(@$dataResRk['header'])!=0){
			foreach($dataResRk['header'] as $key=>$dataDet) {
				$queryD = insertQuery($dbname,'keu_jurnalht',$dataDet);
				$owlPDO->exec($queryD);
			}
			
			foreach($dataResRk['detail'] as $key=>$dataDet) {
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
			if(count(@$dataResRkPen['header'])!=0){
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
			if(count(@$dataResRkPre['header'])!=0){
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
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
		$owlPDO->exec($queryKonter);
		
		#Penalty
		if(count($lstPent)!=0){
			$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonterpen[0]['nokounter']+1),
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnalpen."'");
			$owlPDO->exec($queryKonter);
		}
		#Premi
		if(count($lstPre)!=0){
			$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonterPre[0]['nokounter']+1),
			"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnalPre."'");
			$owlPDO->exec($queryKonter);
		}

			if(count($lstUnit)!=0){
				foreach($lstUnit as $rw=>$rwUnit){
					if($rwUnit!=$_SESSION['empl']['lokasitugas']){
						$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counterDt[$rwUnit]),"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M'");
						$owlPDO->exec($queryKonter);
						
						if(count($lstPent[$rwUnit])!=0){
							$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counterDtpen[$rwUnit]),"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M'");
							$owlPDO->exec($queryKonter);
						}
						if(count($lstPre[$rwUnit])!=0){
							$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counterDtpre[$rwUnit]),"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M'");
							$owlPDO->exec($queryKonter);
						}
					}
				}    
			}
		}
	}
} #tutup foreach


#Jurnal material
#=== Get Data ===
# Header
$queryH = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".
	$param['notransaksi']."'");
$dataH = fetchData($queryH);
		
#cek apakah 1 no transaksi terdiri dari beberapa keg dan blok
$str3 = "SELECT * FROM " . $dbname . ".kebun_pakaimaterial where notransaksi='".$param['notransaksi']."'"; #exit('error'.$str3);
$res3 = $owlPDO->query($str3) or die(print " Gagal: " . PDOException::getMessage());
$res3->setFetchMode(PDO::FETCH_ASSOC);
$adamat='';
while ($bar3 = $res3->fetch()) {
	$dataXX[$bar3['notransaksi']][$bar3['kodekegiatan']][$bar3['kodeorg']][$bar3['kodebarang']]=$bar3['kodebarang'];
	$dataXJurnal[$bar3['notransaksi']][$bar3['kodekegiatan']][$bar3['kodeorg']]=$bar3['kodeorg'];
	$adamat+=1;
}

#jalankan jika ada material
if($adamat!=''){
	
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
				"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal1."'");#exit("error".$queryJ);
			$tmpKonter1 = fetchData($queryJ);
			if(count($tmpKonter1)==0){
				#INVK1 belum diseting di kelompok jurnal
				throw new PDOException("Kelompok jurnal untuk ".$kodeJurnal1." belum ada");
			}
			$konter1 = addZero($tmpKonter1[0]['nokounter']+1,3);

			# Transform No Jurnal dari No Transaksi
			$tmpNoJurnal = explode('/',$param['notransaksi']);
			$nojurnal1 = $tmpNoJurnal[0]."/".$tmpNoJurnal[1]."/".$kodeJurnal1."/".$konter1;
			#======================== Nomor Jurnal =============================
			

			#=== Transform Data ===
			$dataResMat['header'] = array();
			$dataResMat['detail'] = array();

			#1. Data Header
			$dataResMat['header'] = array(
				'nojurnal'=>$nojurnal1,
				'kodejurnal'=>$kodeJurnal1,
				'tanggal'=>$dataH[0]['tanggal'],
				'tanggalentry'=>date('Ymd'),
				'posting'=>'1',
				'totaldebet'=>'0',
				'totalkredit'=>'0',
				'amountkoreksi'=>'0',
				'noreferensi'=>$dataH[0]['notransaksi'],
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'
			);    
			
			# Detail (kredit)
			$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
			  left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
			  where a.notransaksi='".$notranXXJ."' and a.kodegudang!='' and a.kodekegiatan='".$kegiatanXXJ."' and a.kodeorg='".$blokXXJ."'"; #exit("error".$str);
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
				$hargarata='';
				$stru="select * from ".$dbname.".log_5saldobulanan where kodegudang='".$bab->kodegudang."' and kodebarang='".$bab->kodebarang."' and periode='".$periode[$bab->kodegudang]."'"; 
				$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
				$resu->setFetchMode(PDO::FETCH_OBJ);
				while($baru=$resu->fetch()){
					$hargarata=$baru->hargarata;
				}
				#$harga[$bab->kodegudang][$bab->kodebarang]=$bab->hargasatuan;
				$namabarang=substr($bab->namabarang,0,25)." ".$bab->kwantitas." ".$bab->satuan;
				#if($harga[$bab->kodegudang][$bab->kodebarang]=='' or $harga[$bab->kodegudang][$bab->kodebarang]==0){
				if($hargarata=='' or $hargarata==0){
					throw new PDOException("Belum ada harga rata-rata barang ".$namabarang);
				}
				if(isset($akunbarang[substr($bab->kodebarang,0,3)]) and $akunbarang[substr($bab->kodebarang,0,3)]!=''){
				$dataResMat['detail'][] = array(
					'nojurnal'=>$nojurnal1,
					'tanggal'=>$dataH[0]['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>$akunbarang[substr($bab->kodebarang,0,3)],
					'keterangan'=>'Material BKM '. $dataH[0]['notransaksi']." ".$namabarang,
					'jumlah'=>$hargarata*$bab->kwantitas*(-1),
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$_SESSION['empl']['lokasitugas'],
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$bab->kodebarang,
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$dataH[0]['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>'',
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => $segment
				);  
				$noUrut++;
				#$totalJumlah +=$harga[$bab->kodegudang][$bab->kodebarang]*$bab->kwantitas;
				$totalJumlah+=$hargarata*$bab->kwantitas;
				@$ttlqtykeuangan+=$bab->kwantitas;
				
				}else{
					throw new PDOException("Belum ada akun untuk barang ".$namabarang);
				}
			}
			
			if($totalJumlah>0){
				#debet
				$dataResMat['detail'][] = array(
					'nojurnal'=>$nojurnal1,
					'tanggal'=>$dataH[0]['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>$resKeg[$kegiatanXXJ]['akun'],
					'keterangan'=>'Material BKM '.$dataH[0]['notransaksi'],
					'jumlah'=>$totalJumlah,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>substr($blokXXJ,0,4),
					'kodekegiatan'=>$kegiatanXXJ,
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$dataH[0]['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>'',
					'kodeblok'=>$blokXXJ,
					'revisi'=>'0',
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
				"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal1."'");
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
				$resc=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$resc->setFetchMode(PDO::FETCH_OBJ);
				while($bar1=$resc->fetch()){
					$gudang =$bar1->kodegudang;
					$num=1;//default value 
					
					$str="select max(substr(notransaksi,8,4)) as notransaksi from ".$dbname.".log_transaksiht where tipetransaksi=5 and kodegudang='".$gudang."' and tanggal>=".$_SESSION['gudang'][$bar1->kodegudang]['start']." and tanggal<=".$_SESSION['gudang'][$bar1->kodegudang]['end']." and notransaksireferensi!='' order by notransaksi desc limit 1";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_OBJ);        
					while($bar=$res->fetch()){
						$num=$bar->notransaksi;
						if($num!=''){
							$num=$noxz+intval($num);
							$num=str_pad($num,4,"0",STR_PAD_LEFT);
						}else{
							$num="000".$noxz;
						}
					}
					#ambil periode akuntansi masing-masing gudang
					$strd="select periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$bar1->kodegudang."' and tutupbuku=0";
					$resd=$owlPDO->query($strd) or die(print " Gagal: ".PDOException::getMessage());
					$resd->setFetchMode(PDO::FETCH_OBJ);
					while($bard=$resd->fetch()){
						$periode[$bar1->kodegudang]=$bard->periode;
					}
					$nomor[$bar1->kodegudang]=substr($_SESSION['gudang'][$bar1->kodegudang]['start'],0,4).substr($_SESSION['gudang'][$bar1->kodegudang]['start'],4,2)."M".$num;
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
						$harga[$barf->kodegudang][$barf->kodebarang]=$baru->hargarata;
						$xkeluar[$barf->kodegudang][$barf->kodebarang]=$baru->qtykeluarxharga;
						$qtykeluar[$barf->kodegudang][$barf->kodebarang]=$baru->qtykeluar;
						$nilaisaldoakhir[$barf->kodegudang][$barf->kodebarang]=$baru->nilaisaldoakhir;
					}
					$brg[$barf->kodegudang][$barf->kodebarang]=$barf->kodebarang;
					$gud[$barf->kodegudang]=$barf->kodegudang;     
				}
				
				#insert ke log transaksi gudang#
				$str="select induk from ".$dbname.".organisasi where kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
				$kodeorganisasi="";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch()){
					$kodeorganisasi=$bar->induk;
				}

				$awlheader=1;
				$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang where a.notransaksi='".$notranXX."' and a.kodegudang!='' and a.kodekegiatan='".$kegiatanXX."' and a.kodeorg='".$blokXX."' and a.kodebarang='".$barangXX."'";

				$dataMat['header']=array();
				$dataMat['detail']=array();
				$resy=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$resy->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$resy->fetch()){
					$num=$nomor[$bar->kodegudang]."-GI-".$bar->kodegudang;
					$dataMat['header'][$bar->kodegudang] = array(
						'tipetransaksi'=>'5',
						'notransaksi'=>$num, 
						'tanggal'=>$dataH[0]['tanggal'], 
						'kodept'=>$kodeorganisasi, 
						'untukpt'=>$kodeorganisasi, 
						'nopo'=>'', 
						'nosj'=>'', 
						'keterangan'=>'Material BKM ', 
						'statusjurnal'=>'1', 
						'kodegudang'=>$bar->kodegudang, 
						'user'=>$_SESSION['standard']['userid'], 
						'namapenerima'=>'0', 
						'mengetahui'=>$_SESSION['standard']['userid'], 
						'idsupplier'=>'', 
						'nofaktur'=>'', 
						'post'=>'1', 
						'postedby'=>$_SESSION['standard']['userid'], 
						'untukunit'=>$_SESSION['empl']['lokasitugas'], 
						'subunit'=>'', 
						'notransaksireferensi'=>$param['notransaksi'], 
						'gudangx'=>'',
						'persetujuan1'=>'0',
						'hasilpersetujuan1'=>'0',
						'tanggalpersetujuan1'=>date('Y-m-d H:i:s'),
						'persetujuan2'=>'0',
						'hasilpersetujuan2'=>'0',
						'tanggalpersetujuan2'=>date('Y-m-d H:i:s'),
						'namafile'=>'',
						'departemen'=>'',
						'karyawanid'=>'',
						'lastupdate'=>date('Y-m-d H:i:s'),
						'norequest'=>''
					);
					$dataMat['detail'][]=array(
						'notransaksi'=>$num, 
						'nopp'=>'', 
						'kodebarang'=>$bar->kodebarang, 
						'satuan'=>$bar->satuan, 
						'jumlah'=>$bar->kwantitas, 
						'jumlahlalu'=>$saldo[$bar->kodegudang][$bar->kodebarang], 
						'hargasatuan'=>'0', 
						'kodeblok'=>$blokXX,
						'waktutransaksi'=>date('Y-m-d H:i:s'),
						'updateby'=>$_SESSION['standard']['userid'], 
						'kodekegiatan'=>$kodekegiatan, 
						'kodemesin'=>'', 
						'statussaldo'=>1, 
						'hargarata'=>$harga[$bar->kodegudang][$bar->kodebarang],
						'nopo'=>'',
						'kodesegment' => $segment,
						'catatan' => null,
						'namafile' => '',
						'kmhm' => ''
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
					# cek saldo apakah cukup
					$jumlah[$barf->kodegudang][$barf->kodebarang]=$barf->kwantitas; 
					if($saldo[$barf->kodegudang][$barf->kodebarang] < @$jumlah[$barf->kodegudang][$barf->kodebarang] or $harga[$barf->kodegudang][$barf->kodebarang] <= 0){
						$nmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$barf->kodebarang."'");
						$nmsat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$barf->kodebarang."'");
						
						$errsaldo="Saldo untuk barang ".$nmbarang[$barf->kodebarang]." di gudang ".$barf->kodegudang." periode ".$periode[$barf->kodegudang]." tidak cukup !!!\n => Jumlah saldo tersedia : ".$saldo[$barf->kodegudang][$barf->kodebarang]." ".$nmsat[$barf->kodebarang]."\n => Pemakaian material : ".@$jumlah[$barf->kodegudang][$barf->kodebarang]." ".$nmsat[$barf->kodebarang];
						throw new PDOException($errsaldo);
					}
				}

				$errSal='';
				$ttlqtygudang='';
				foreach($gud as $keygud=>$valgud){
					foreach($brg[$valgud] as $keybrg=>$valbrg){
						#ini buat cek qty & rp di Gudang vs Akutansi
						@$ttlqtygudang+=($jumlah[$valgud][$valbrg]);
						@$ttlrpgudang+=(($jumlah[$valgud][$valbrg])*$harga[$valgud][$valbrg]);
						
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
							
						#bentuk saldo akhir
			
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
				  and a.kodekegiatan='".$kegiatanXX."' and a.kodeorg='".$blokXX."' and a.kodebarang='".$barangXX."'";
				$resa=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$resa->setFetchMode(PDO::FETCH_OBJ);
				#ambil saldo dan harga rata
				while($barf=$resa->fetch()){
					$brg[$barf->kodegudang][$barf->kodebarang]=$barf->kodebarang;
					$gud[$barf->kodegudang]=$barf->kodegudang;     
				}

				$errSal='';
				$ttlqtygudang=$ttlrpgudang='';
				foreach($gud as $keygud=>$valgud){
					foreach($brg[$valgud] as $keybrg=>$valbrg){
						$sth="update ".$dbname.".log_5saldobulanan 
						set saldoakhirqty=".($saldo[$valgud][$valbrg]-$jumlah[$valgud][$valbrg]).", 
						nilaisaldoakhir=".($nilaisaldoakhir[$valgud][$valbrg]-($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg])).",
						qtykeluar=".($qtykeluar[$valgud][$valbrg]+$jumlah[$valgud][$valbrg]).",
						qtykeluarxharga=".($xkeluar[$valgud][$valbrg]+($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg]))."
						where periode='".$periode[$valgud]."' and kodegudang='".$valgud."' and kodebarang='".$valbrg."'"; 
						$owlPDO->exec($sth);
						
						#ini buat cek qty & rp di Gudang vs Akutansi
						$ttlqtygudang+=($jumlah[$valgud][$valbrg]);
						$ttlrpgudang+=(($jumlah[$valgud][$valbrg])*$harga[$valgud][$valbrg]);
						
						$stup="update ".$dbname.".kebun_pakaimaterial set hargasatuan=".$harga[$valgud][$valbrg]." where kodegudang='".$valgud."' and kodebarang='".$valbrg."' and notransaksi='".$param['notransaksi']."'";
						$owlPDO->exec($stup);
					}
				}
				#update log_5masterbarangdt
				foreach($gud as $keygud=>$valgud){
					foreach($brg[$valgud] as $keybrg=>$valbrg){ 
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
$queryJ = selectQuery($dbname,'kebun_aktifitas',"jurnal","notransaksi='".
	$param['notransaksi']."'");
$isJ = fetchData($queryJ);
if($isJ[0]['jurnal']==1) {
	throw new PDOException("Data posted by another user");
} else {
	$queryToJ = updateQuery($dbname,'kebun_aktifitas',array('jurnal'=>1),"notransaksi='".$dataH[0]['notransaksi']."'");
	$owlPDO->exec($queryToJ);
}

# exekusi query di atas !!!
	$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
} 

#ada jurnal yg isinya kosong, kalau pakai if di atas banyak kali, nah solusinya adalah hapus saja jurnalnya
$str="delete from ".$dbname.".keu_jurnalht where noreferensi='".$dataH[0]['notransaksi']."' and totaldebet='0' and totalkredit='0' and nojurnal not in (select nojurnal from ".$dbname.".keu_jurnaldt)";
try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
