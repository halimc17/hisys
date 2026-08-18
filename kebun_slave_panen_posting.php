<?
//@Copy nangkoelframework
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;

#=== Get Data ===
# Header
$queryH = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".
    $param['notransaksi']."'");
	
$dataH = fetchData($queryH);

#====cek periode===============================
$tgl = str_replace("-","",$dataH[0]['tanggal']);
if($_SESSION['org']['period']['start']>$tgl)
    exit('Error:Tanggal diluar periode aktif');

# Detail
$queryD = selectQuery($dbname,'kebun_prestasi',"*","notransaksi='".
    $param['notransaksi']."'");
$dataD = fetchData($queryD);

#=== Cek if posted ===
$error0 = "";
if($dataH[0]['jurnal']==1) {
    $error0 .= $_SESSION['lang']['errisposted'];
}
if($error0!='') {
    echo "Data Error :\n".$error0;
    exit;
}

#=== Cek if data not exist ===
$error1 = "";
if(count($dataH)==0) {
    $error1 .= $_SESSION['lang']['errheadernotexist']."\n";
}
if(count($dataD)==0) {
    $error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
}
if($error1!='') {
    echo "Data Error :\n".$error1;
    exit;
}

#=== Cek if Upload Absensi ===
$countUpload = 0;
$countUpload = "";
$arrUpload = array();
if(!empty($dataH[0]['nikmandor'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor'];
if(!empty($dataH[0]['nikmandor1'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor1'];
if(!empty($dataH[0]['nikasisten'])) $arrUpload[]['nik'] = $dataH[0]['nikasisten'];
if(!empty($dataH[0]['keranimuat'])) $arrUpload[]['nik'] = $dataH[0]['keranimuat'];


$unit=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',$whkary);
$namakary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whkary);
$pt=makeOption($dbname,'organisasi','kodeorganisasi,induk');

foreach($dataD as $row){
	$arrUpload[]['nik'] = $row['nik'];
	$whkary=" karyawanid='".$row['nik']."'";
	$unitkary=$unit[$row['nik']];
}


foreach($arrUpload as $row){
	$str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$row['nik']."' 
	and tanggalabsen='".($dataH[0]['tanggal'])."' limit 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($row['nik'] != $bar['karyawanid']){
		$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$row['nik']."'");
		$nikkary = makeOption($dbname,"datakaryawan",'karyawanid,nik',"karyawanid='".$row['nik']."'");
		
		// $errorUpload .= $row['nik']." = ".$optNamaKaryawan[$row['nik']]."\n";
		$errorUpload .= $nikkary[$row['nik']]." = ".$optNamaKaryawan[$row['nik']]."\n";
		$countUpload = $countUpload + 1;
	}
}
// if($countUpload > 0){
	// echo "Warning : Absen fingerprint untuk karyawan dg NIK : \n".$errorUpload."belum diupload";
    // exit;
// }

$kodeBlok=array();
foreach($dataD as $row){
	$kodeBlok[]['kodeorg'] = $row['kodeorg'];
}
#ambil jumlah jjg di kegiatan panen
foreach($kodeBlok as $row){
	$str = "select kodeorg,tanggal, sum(hasilkerja) as hasilkerja, sum(hkpanenperhari) as hk from ".$dbname.".kebun_prestasi_vs_hk where kodeorg='".$row['kodeorg']."' and tanggal='".($dataH[0]['tanggal'])."' group by kodeorg";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kdblokpnn[$bar['kodeorg']]=$bar['kodeorg'];
			$jjgkegpnn[$bar['kodeorg']]=$bar['hasilkerja'];
			$hkkegpnn[$bar['kodeorg']]=$bar['hk'];
		}

}

#ambil jumlah jjg di rekap panen
foreach($kodeBlok as $row){
	$str2 = "select blok,tanggal, sum(jjgpanen) as jjgpanen, sum(tenagakerja) as hk from ".$dbname.".kebun_rekappnn_vw where blok='".$row['kodeorg']."' and tanggal='".($dataH[0]['tanggal'])."' group by blok";
	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_ASSOC);
		while($bar2=$res2->fetch())
		{
			$kdblokpnn[$bar2['blok']]=$bar2['blok'];
			$jjgrkppnn[$bar2['blok']]=$bar2['jjgpanen'];
			$hkrkppnn[$bar2['blok']]=$bar2['hk'];
		}	
}

foreach($kdblokpnn as $blok){
	$selisih=($jjgkegpnn[$blok] - $jjgrkppnn[$blok]);
	$selisihHk=($hkkegpnn[$blok] - $hkrkppnn[$blok]);

	if($selisih > 0){
		echo "Warning : Jlh Jjg di Keg Panen lebih besar dari Jjg di Rekap Panen :\n";
		foreach($kdblokpnn as $blok){
			echo "".$kdblokpnn[$blok]." Rkp Pnn => ".$jjgrkppnn[$blok]." Jjg, Keg Pnn => ".$jjgkegpnn[$blok]." Jjg, Var => ".($jjgkegpnn[$blok]-$jjgrkppnn[$blok])." Jjg\n";
		}
		exit;
	}

	if($selisihHk > 0.25){
		echo "Warning : Jlh HK di Keg Panen lebih besar dari HK di Rekap Panen :\n";
		foreach($kdblokpnn as $blok){
			echo "".$kdblokpnn[$blok]." Rkp Pnn => ".$hkrkppnn[$blok]." HK, Keg Pnn => ".number_format($hkkegpnn[$blok],2)." HK, Var => ".number_format(($hkkegpnn[$blok]-$hkrkppnn[$blok]),2)." HK\n";
		}
		exit;
	}
}

#======================== Kegiatan Panen ===========================
$kodeJurnal = 'PNN01';
$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',
    " jurnalid='".$kodeJurnal."'");
$resParam = fetchData($queryParam);

      $akunkredit=$resParam[0]['noakunkredit']; 
      $akundebet =$resParam[0]['noakundebet'];
//default kodekegiatan panen/potong buah      
$kodekegiatan= $akundebet."01";     

###untuk premi###
$kodeJurnalpremi = 'PNN02';
$queryParampremi = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',
    " jurnalid='".$kodeJurnalpremi."'");
$resParampremi = fetchData($queryParampremi);

      $akunkreditpremi=$resParampremi[0]['noakunkredit']; 
      $akundebetpremi =$resParampremi[0]['noakundebet'];
//default kodekegiatan panen/potong buah      
$kodekegiatanpremi= $akundebetpremi."01";  

      
# Get Journal Counter
$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
$tmpKonter = fetchData($queryJ);
$konter = addZero($tmpKonter[0]['nokounter']+1,3);

# Transform No Jurnal dari No Transaksi
$tmpNoJurnal = explode('/',$param['notransaksi']);
$nojurnal = $tmpNoJurnal[0]."/".$tmpNoJurnal[1]."/".$kodeJurnal."/".$konter;


/***************/
/***************/
/***************/

###untuk premi###
# Get Journal Counter
$queryJpremi = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnalpremi."'");
$tmpKonterpremi = fetchData($queryJpremi);
$konterpremi = addZero($tmpKonterpremi[0]['nokounter']+1,3);
# Transform No Jurnal dari No Transaksi
$tmpNoJurnalpremi = explode('/',$param['notransaksi']);
$nojurnalpremi = $tmpNoJurnalpremi[0]."/".$tmpNoJurnalpremi[1]."/".$kodeJurnalpremi."/".$konterpremi;



###untuk rk upah terima### rkut
$queryJrkut = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
	"kodeorg='".$pt[$unitkary]."' and kodekelompok='M'");
$tmpKonterrkut = fetchData($queryJrkut);
$konterrkut = addZero($tmpKonterrkut[0]['nokounter']+1,3);
# Transform No Jurnal dari No Transaksi
$tmpNoJurnalrkut = explode('/',$param['notransaksi']);
$nojurnalrkut = $tmpNoJurnalrkut[0]."/".$unitkary."/M/".$konterrkut;


/***************/
/***************/
/***************/

#======================== Nomor Jurnal =============================

    // cari hari
    $day = date('D', strtotime($dataH[0]['tanggal']));
    if($day=='Sun')$libur=true; else $libur=false;
    // kamus hari libur
    $strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$dataH[0]['tanggal']."'";
    $queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
    $queorg->setFetchMode(PDO::FETCH_ASSOC);
    while($roworg=$queorg->fetch())
    {
		
        if($roworg['keterangan']=='libur')$libur=true;
        if($roworg['keterangan']=='masuk')$libur=false;
    }
	
    $tulisanpanen='Potong Buah';
    if($libur)$tulisanpanen.=' HM/HB';

	//exit("Error:$tulisanpanen");

#=== Transform Data ===
$dataRes['header'] = array();
$dataRes['detail'] = array();

$dataRespremi['header'] = array();
$dataRespremi['detail'] = array();


$dataResrkub['header']=array();
$dataResrkut['header']=array();

	$comment='';
	$str="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".substr($dataH[0]['tanggal'],0,7)."'"; 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
	
		$comment.="Unit ".$bar->kodeorg." periode ".$bar->periode." has been closed\n";
	}

#Cek apakah unit sudah tutup buku
	if($comment!=''){
		exit("Warning : $comment");
	}

#1. Data Header
foreach($dataD as $row) {
	
	$whkary=" karyawanid='".$row['nik']."'";
	$unitkary=$unit[$row['nik']];
	
	if($unitkary==$_SESSION['empl']['lokasitugas']){
			$nokaryint+=1;
			// $premiint+=($row['upahpremi']+$row['upahpremilebihbasis']-$row['rupiahpenalty']);
			$premiint+=($row['upahpremi']+$row['upahpremilebihbasis']);
		}else{
			$nokaryext+=1;
			// $premiext+=($row['upahpremi']+$row['upahpremilebihbasis']-$row['rupiahpenalty']);
			$premiext+=($row['upahpremi']+$row['upahpremilebihbasis']);
	}
}


	
//	exit("Error:$nokaryext");

	$dataRes['header'] = array(
		'nojurnal'=>$nojurnal,
		'kodejurnal'=>$kodeJurnal,
		'tanggal'=>$dataH[0]['tanggal'],
		'tanggalentry'=>date('Ymd'),
		'posting'=>'0',
		'totaldebet'=>'0',
		'totalkredit'=>'0',
		'amountkoreksi'=>'0',
		'noreferensi'=>$dataH[0]['notransaksi'],
		'autojurnal'=>'1',
		'matauang'=>'IDR',
		'kurs'=>'1',
		'revisi'=>'0'
	);
	
if($nokaryext>0){
	$dataResrkut['header'] = array(
		'nojurnal'=>$nojurnalrkut,
		'kodejurnal'=>'M',
		'tanggal'=>$dataH[0]['tanggal'],
		'tanggalentry'=>date('Ymd'),
		'posting'=>'0',
		'totaldebet'=>'0',
		'totalkredit'=>'0',
		'amountkoreksi'=>'0',
		'noreferensi'=>$dataH[0]['notransaksi'],
		'autojurnal'=>'1',
		'matauang'=>'IDR',
		'kurs'=>'1',
		'revisi'=>'0'
	);
	$comment='';
	$str="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$unitkary."' and periode='".substr($dataH[0]['tanggal'],0,7)."'"; 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
	
		$comment.="Unit ".$bar->kodeorg." periode ".$bar->periode." has been closed\n";
	}
}
	#Cek apakah unit penerima RK sudah tutup buku
	if($comment!=''){
		exit("Warning : $comment");
	}

#2. Data Detail
# Get Data from Kegiatan
$i = 0;
//if($unitkary==$_SESSION['empl']['lokasitugas']){
# Detail (Debet)
$noUrut = 1;
$totalJumlah = 0;
foreach($dataD as $row) {
	//'jumlah'=>$row['upahkerja'] - $row['upahpenalty'],
	$whkary=" karyawanid='".$row['nik']."'";
	//if($unit[$row['nik']]==$_SESSION['empl']['lokasitugas']){	
		$dataRes['detail'][] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$dataH[0]['tanggal'],
			'nourut'=>$noUrut,
			'noakun'=>$akundebet,
			'keterangan'=>$tulisanpanen.' '.$namakary[$row['nik']],
			'jumlah'=>$row['upahkerja'],
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>substr($row['kodeorg'],0,4),
			'kodekegiatan'=>$kodekegiatan,
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi'=>$row['notransaksi'],
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>'',
			'kodeblok'=>$row['kodeorg'],
			'revisi'=>'0',
			'kodesegment' => $row['kodesegment']
		);
		
					
		
		//'jumlah'=>$row['upahpremi'] + $row['upahpremilebihbasis'] - $row['rupiahpenalty'],
		if($premiint>0){
			$noUrut++;
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$dataH[0]['tanggal'],
				'nourut'=>$noUrut,
				'noakun'=>$akundebetpremi,
				'keterangan'=>'Premi Potong Buah '.$namakary[$row['nik']],
				'jumlah'=>$row['upahpremi'] + $row['upahpremilebihbasis'],
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>substr($row['kodeorg'],0,4),
				'kodekegiatan'=>$kodekegiatanpremi,
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$row['notransaksi'],
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>$row['kodeorg'],
				'revisi'=>'0',
				'kodesegment' => $row['kodesegment']
			);
			
			
		}
		if($unit[$row['nik']]!=$_SESSION['empl']['lokasitugas']){
			// $totext+= ($row['upahkerja'] - $row['upahpenalty'])+($row['upahpremi'] + $row['upahpremilebihbasis'] - $row['rupiahpenalty']);
			$totext+= ($row['upahkerja'])+($row['upahpremi'] + $row['upahpremilebihbasis']);
			
			if($pt[$unit[$row['nik']]]==$_SESSION['empl']['kodeorganisasi']){
				#intra dalam 1pt
				 $jenis="intra"; 
			}else{
				 $jenis="inter"; 
			}
			
			$akunhutang=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$_SESSION['empl']['lokasitugas']."' and jenis='".$jenis."'");    
				$noakunkreditrk=$akunhutang[$_SESSION['empl']['lokasitugas']];

		    if ($akunhutang[$_SESSION['empl']['lokasitugas']]=='') {
		        exit("Warning : Account intraco or interco not available for ".$_SESSION['empl']['lokasitugas'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
		    }
			
		}		
		// $totalJumlah += ($row['upahkerja'] - $row['upahpenalty'])+($row['upahpremi'] + $row['upahpremilebihbasis'] - $row['rupiahpenalty']);
		$totalJumlah += ($row['upahkerja'])+($row['upahpremi'] + $row['upahpremilebihbasis']);
		$noUrut++;
	//}
}


$totint=$totalJumlah-$totext;

if($totext>0){
	$dataRes['detail'][] = array(
		'nojurnal'=>$nojurnal,
		'tanggal'=>$dataH[0]['tanggal'],
		'nourut'=>$noUrut,
		'noakun'=>$noakunkreditrk,
		'keterangan'=>$tulisanpanen.' dan Premi Potong Buah Hub RK ',
		'jumlah'=>$totext*(-1),
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$dataH[0]['kodeorg'],
		'kodekegiatan'=>$kodekegiatan,
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
		'kodesegment' => $row['kodesegment']
	);	
}


# Detail (Kredit)
if($totint>0){
	$dataRes['detail'][] = array(
		'nojurnal'=>$nojurnal,
		'tanggal'=>$dataH[0]['tanggal'],
		'nourut'=>$noUrut+1,
		'noakun'=>$akunkredit,
		'keterangan'=>$tulisanpanen.' dan Premi Potong Buah',
		'jumlah'=>$totint*(-1),
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$dataH[0]['kodeorg'],
		'kodekegiatan'=>$kodekegiatan,
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
		'kodesegment' => $row['kodesegment']
	);
}
# Total D/K
$dataRes['header']['totaldebet'] = $totalJumlah;
$dataRes['header']['totalkredit'] = $totalJumlah;



#####################################################################################
#####################################################################################
#####################################################################################
#####################################################################################

####RK penerima###
#################
$noUrut = 1;
$totalJumlah = 0;
foreach($dataD as $row) {
	if($unit[$row['nik']]!=$_SESSION['empl']['lokasitugas']){

	
	
	#get akun caco
	if($pt[$unit[$row['nik']]]==$_SESSION['empl']['kodeorganisasi']){
		#intra dalam 1pt
		 $jenis="intra"; 
	}else{
		 $jenis="inter"; 
	}
	
		$akunpiutang=makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',"kodeorg='".$unit[$row['nik']]."' and jenis='".$jenis."'");
        $akunhutang=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$_SESSION['empl']['lokasitugas']."' and jenis='".$jenis."'");    
        
	    if ($akunpiutang[$unit[$row['nik']]]=='') {
	        exit("Warning : Account intraco or interco not available for ".$unit[$row['nik']].". Please setting on menu Finance > setup > COA for Intra/Interco.");
	    }
	    
	    if ($akunhutang[$_SESSION['empl']['lokasitugas']]=='') {
	        exit("Warning : Account intraco or interco not available for ".$_SESSION['empl']['lokasitugas'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
	    }
	
		//'jumlah'=>$row['upahkerja'] - $row['upahpenalty'],
		$dataResrkut['detail'][] = array(
			'nojurnal'=>$nojurnalrkut,
			'tanggal'=>$dataH[0]['tanggal'],
			'nourut'=>$noUrut,
			'noakun'=>$akunpiutang[$unit[$row['nik']]],
			'keterangan'=>$tulisanpanen.' Hub RK '.$namakary[$row['nik']],
			'jumlah'=>$row['upahkerja'],
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$unit[$row['nik']],
			'kodekegiatan'=>$kodekegiatan,
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi'=>$row['notransaksi'],
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>'',
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => $row['kodesegment']
		);
		//'jumlah'=>$row['upahpremi'] + $row['upahpremilebihbasis'] - $row['rupiahpenalty'],
		$noUrut++;
		if($premiext>0){
			$dataResrkut['detail'][] = array(
				'nojurnal'=>$nojurnalrkut,
				'tanggal'=>$dataH[0]['tanggal'],
				'nourut'=>$noUrut,
				'noakun'=>$akunpiutang[$unit[$row['nik']]],
				'keterangan'=>'Premi Potong Buah Hub RK '.$namakary[$row['nik']],
				'jumlah'=>$row['upahpremi'] + $row['upahpremilebihbasis'],
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit[$row['nik']],
				'kodekegiatan'=>$kodekegiatanpremi,
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$row['notransaksi'],
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => $row['kodesegment']
			);
		}
			
		// $totalJumlah += ($row['upahkerja'] - $row['upahpenalty'])+($row['upahpremi'] + $row['upahpremilebihbasis'] - $row['rupiahpenalty']);
		$totalJumlah += ($row['upahkerja'])+($row['upahpremi'] + $row['upahpremilebihbasis']);
		$noUrut++;
	}
}

# Detail (Kredit)
$dataResrkut['detail'][] = array(
    'nojurnal'=>$nojurnalrkut,
    'tanggal'=>$dataH[0]['tanggal'],
    'nourut'=>$noUrut,
    'noakun'=>$akunkredit,
    'keterangan'=>$tulisanpanen.' dan Premi Potong Buah Hub RK',
    'jumlah'=>$totalJumlah*(-1),
    'matauang'=>'IDR',
    'kurs'=>'1',
    'kodeorg'=>$dataH[0]['kodeorg'],
    'kodekegiatan'=>$kodekegiatan,
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
    'kodesegment' => $row['kodesegment']
);

# Total D/K
$dataResrkut['header']['totaldebet'] = $totalJumlah;
$dataResrkut['header']['totalkredit'] = $totalJumlah;




#=== Insert Data ===
$errorDB = "";

# Update flag absensi
foreach($arrUpload as $row){
	$strAbs = "update ".$dbname.".upload_absensi set flag = 1 where karyawanid='".$row['nik']."' and tanggalabsen='".($dataH[0]['tanggal'])."'";
	try{
		$owlPDO->exec($strAbs); 
	}
    catch (PDOException $e) 
	{
         $errorDB .= " Error update flag absensi gagal:" . $e->getMessage() . "\n".$strAbs;
    }
}

# Header

if($dataRes['header']['totaldebet']>0){
	$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
	try{$owlPDO->exec($queryH); }
	catch (PDOException $e) {
		 $errorDB .= "Header Upah :" . $e->getMessage() . "\n";
	}
}
// echo"<pre>";
// print_r($dataRes);
// echo"</pre>";
// exit("Error:A");

if($dataResrkut['header']['totaldebet']>0){
	$queryHrkut = insertQuery($dbname,'keu_jurnalht',$dataResrkut['header']);
	try{$owlPDO->exec($queryHrkut); }
	catch (PDOException $e) {
		 $errorDB .= "Header RK Upah Penerima :" . $e->getMessage() . "\n";
	}
}

# Detail
if($errorDB=='') {
	if($dataRes['header']['totaldebet']>0){
		foreach($dataRes['detail'] as $key=>$dataDet) {
			$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
			try{$owlPDO->exec($queryD); }
			catch (PDOException $e) {
				 $errorDB .= "Detail Error ".$key." :" . $e->getMessage() . "\n";
			}
		}
	}
	
	if($dataResrkut['header']['totaldebet']>0){	
		foreach($dataResrkut['detail'] as $key=>$dataDet) {
			$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
			try{$owlPDO->exec($queryD); }
			catch (PDOException $e) {
				 $errorDB .= "Detail Error RK Penerima ".$key." :" . $e->getMessage() . "\n";
			}
		}
	}
	
	/*
	##premi
	 foreach($dataRespremi['detail'] as $key=>$dataDet) {
        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
        try{$owlPDO->exec($queryD); }
        catch (PDOException $e) {
             $errorDB .= "Detail Error Premi ".$key." :" . $e->getMessage() . "\n";
        }
    }
	*/
	
    if($errorDB=='') {
            #=== Switch Jurnal to 1 ===
            # Cek if already posted
            $queryJ = selectQuery($dbname,'kebun_aktifitas',"jurnal","notransaksi='".
                    $param['notransaksi']."'");
            $isJ = fetchData($queryJ);
            if($isJ[0]['jurnal']==1) {
                    $errorDB .= "Data posted by another user";
            } else {
                    $queryToJ = updateQuery($dbname,'kebun_aktifitas',array('jurnal'=>1),"notransaksi='".$dataH[0]['notransaksi']."'");
                    try{$owlPDO->exec($queryToJ); }
                    catch (PDOException $e) {
                        $errorDB .= "Posting Mark Error :" . $e->getMessage() . "\n";
                    }
					
                    $queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),
                            "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
                    try{$owlPDO->exec($queryKonter); }
                    catch (PDOException $e) {
                        $errorDB .= "Update Counter Error :" . $e->getMessage() . "\n";
                    }

					$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonterrkut[0]['nokounter']+1),
							"kodeorg='".$pt[$unitkary]."' and kodekelompok='M'");
					try{$owlPDO->exec($queryKonter); }
					catch (PDOException $e) {
						$errorDB .= "Update Counter Error :" . $e->getMessage() . "\n";
					}					
					
					
					
					
            }
    }
}


if($errorDB!="") {
    // Rollback
	
	/*
    $where = "nojurnal='".$nojurnal."'";
    $queryRB = "delete from `".$dbname."`.`keu_jurnalht` where ".$where;
    $queryRB2 = updateQuery($dbname,'kebun_aktifitas',array('jurnal'=>0),
        "notransaksi='".$dataH[0]['notransaksi']."'");
		
	$whererkub= "nojurnal='".$nojurnalrkub."'";
    $queryRBrkub = "delete from `".$dbname."`.`keu_jurnalht` where ".$where;
		
    $queryRBKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']),
        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
    try{$owlPDO->exec($queryRB); }
        catch (PDOException $e) {
            $errorDB .= "Rollback 1 Error" . $e->getMessage() . "\n";
        } 
    try{$owlPDO->exec($queryRB2); }
        catch (PDOException $e) {
            $errorDB .= "Rollback 2 Error :" . $e->getMessage() . "\n";
        } 
		
	 try{$owlPDO->exec($queryRBrkub); }
        catch (PDOException $e) {
            $errorDB .= "Rollback 3 Error (RK) :" . $e->getMessage() . "\n";
        } 	
		
    try{$owlPDO->exec($queryRBKonter); }
        catch (PDOException $e) {
           $errorDB .= "Rollback Counter Error :" . $e->getMessage() . "\n";
        } 
	*/

	#delete jurnal dari noreferensi
	$wherenoref = "noreferensi='".$dataH[0]['notransaksi']."'";
	$queryjurnal = "delete from `".$dbname."`.`keu_jurnalht` where ".$wherenoref;

	#update kebun_aktifitas
	$querynopnn = updateQuery($dbname,'kebun_aktifitas',array('jurnal'=>0),
        "notransaksi='".$dataH[0]['notransaksi']."'");
		
	#exec query	
	try{$owlPDO->exec($queryjurnal); }
        catch (PDOException $e) {
           $errorDB .= "Rollback Detele Jurnal :" . $e->getMessage() . "\n";
        } 	
		
	try{$owlPDO->exec($querynopnn); }
        catch (PDOException $e) {
           $errorDB .= "Rollback Update BKM PNN :" . $e->getMessage() . "\n";
        } 	
    
	
	foreach($arrUpload as $row){
		$strAbs = "update ".$dbname.".upload_absensi set flag = 0 where karyawanid='".$row['nik']."' and tanggalabsen='".($dataH[0]['tanggal'])."'";
		try{
			$owlPDO->exec($strAbs); 
		}
		catch (PDOException $e) 
		{
			 $errorDB .= " Error update flag absensi gagal:" . $e->getMessage() . "\n".$strAbs;
		}
	}
    echo "DB Error :\n".$errorDB."___".$queryRB2;

    exit;
} else {
    // Posting Success
    
}
?>