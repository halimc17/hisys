<?
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;

try {
	$owlPDO->beginTransaction();
	
#=== Get Data ===
# Header
$queryH= selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".$param['notransaksi']."'");
$dataH = fetchData($queryH);

$str = "select * from ".$dbname.".sdm_5periodegaji where kodeorg = '".$dataH[0]['kodeorg']."' and periode='".substr($dataH[0]['tanggal'],0,7)."' and sudahproses='1'";
$res = fetchData($str);
if(count($res)>0){
	 throw new PDOException("Periode gaji sudah ditutup, proses dibatalkan.");
}

#validasi posting
validasiInput($dataH[0]['kodeorg'],$dataH[0]['divisi'],'PNNPOST',$dataH[0]['tanggal'],$exit='0');


$prdbln=substr($dataH[0]['tanggal'],0,7);
#====cek periode===============================
$tgl = str_replace("-","",$dataH[0]['tanggal']);
if($_SESSION['org']['period']['start']>$tgl)
    throw new PDOException('Tanggal diluar periode aktif');

# Detail
#$queryD = selectQuery($dbname,'kebun_prestasi',"*","notransaksi='".$param['notransaksi']."'");	
$queryD = "select notransaksi,nobkm,nik,nikpemel,kodekegiatan,kodeorg,tahuntanam,sum(hasilkerja) as hasilkerja,sum(hasilkerjakg) as hasilkerjakg,sum(jumlahhk) as jumlahhk,bjr,sum(norma) as norma,sum(outputminimal) as outputminimal,round(sum(upahkerja)) as upahkerja, round(sum(upahpenalty)) as upahpenalty, round(sum(upahpremi)) as upahpremi, round(sum(premibasis)) as premibasis, round(sum(premibasis2)) as premibasis2, round(sum(upahpremilebihbasis)) as upahpremilebihbasis,round(sum(upahpremilebihbasis2)) as upahpremilebihbasis2,round(sum(premibrondol)) as premibrondol,round(sum(umr)) as umr,statusblok,round(sum(pekerjaanpremi)) as pekerjaanpremi,round(sum(rupiahpenalty)) as rupiahpenalty,sum(luaspanen) as luaspanen,kodesegment,noreferensi,sum(brondolan) as brondolan,sum(jjgpenalty) as jjgpenalty,keterangan from ".$dbname.".kebun_prestasi where notransaksi='".$param['notransaksi']."' group by nik, kodeorg";	
$dataD = fetchData($queryD);

#=== Cek if posted ===
$error0 = "";
if($dataH[0]['jurnal']==1) {
    $error0 .= $_SESSION['lang']['errisposted'];
}
if($error0!='') {
    throw new PDOException("Data Error :\n".$error0);
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
    throw new PDOException("Data Error :\n".$error1);
}


$str = "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and kriteriaefil='PNN'";
$res = fetchData($str);
if(count($res)==0){
	throw new PDOException("Silahkan upload file pendukung terlebih dahulu sebelum melakukan posting.");
}



#=== Cek if Upload Absensi ===
$countUpload = 0;
$countUpload = "";
$arrUpload = array();
if(!empty($dataH[0]['nikmandor'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor'];
if(!empty($dataH[0]['nikmandor1'])) $arrUpload[]['nik'] = $dataH[0]['nikmandor1'];
if(!empty($dataH[0]['nikasisten'])) $arrUpload[]['nik'] = $dataH[0]['nikasisten'];
// if(!empty($dataH[0]['keranimuat'])) $arrUpload[]['nik'] = $dataH[0]['keranimuat'];

$unit=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas');
$namakary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

foreach($dataD as $row){
	$arrUpload[]['nik'] = $row['nik'];
	$whkary=" karyawanid='".$row['nik']."'";	
	
	$unitkary=$unit[$row['nik']];
}

$pt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


$statusfp=0;
#query pengecekan apakah FP aktif / tidak
$strfp = "select * from ".$dbname.".sdm_5aktivasifp where kodeorg='".$dataH[0]['kodeorg']."'  and tanggal <= '".$dataH[0]['tanggal']."'";
$res = fetchData($strfp);
$statusfp    = $res[0]['status'];//1 aktif,0 tidak
$tipevalidasi= $res[0]['tipevalidasi'];
$detailexp   = explode(",",$res[0]['detailvalidasi']);
foreach($detailexp as $vald){
	$detval[$vald]=$vald;
}

if($statusfp==1){
	validasifp($tipevalidasi,$detval,'PNN',$arrUpload,$dataH[0]['tanggal'],'0');
}	

/* 
$kodeBlok=array();
$query808 = "select notransaksi,nobkm,nik,nikpemel,kodekegiatan,kodeorg,tahuntanam,sum(hasilkerja) as hasilkerja,sum(hasilkerjakg) as hasilkerjakg,sum(satpremi) as satpremi,sum(hasilkerjapremi) as hasilkerjapremi,sum(jumlahhk) as jumlahhk,bjr,sum(norma) as norma,sum(outputminimal) as outputminimal,sum(upahkerja) as upahkerja,sum(upahpenalty) as upahpenalty,sum(upahpremi) as upahpremi, sum(premibasis) as premibasis, sum(premibasis2) as premibasis2,sum(upahpremilebihbasis) as upahpremilebihbasis,sum(upahpremilebihbasis2) as upahpremilebihbasis2,sum(premibrondol) as premibrondol,sum(umr) as umr,statusblok,sum(pekerjaanpremi) as pekerjaanpremi,sum(rupiahpenalty) as rupiahpenalty,sum(luaspanen) as luaspanen,kodesegment,noreferensi,sum(brondolan) as brondolan,sum(jjgpenalty) as jjgpenalty,keterangan from ".$dbname.".kebun_prestasi where notransaksi='".$param['notransaksi']."' group by kodeorg";
$res808 = fetchData($query808);
foreach($res808 as $row){
	$kodeBlok[]['kodeorg'] = $row['kodeorg'];
	
	#cek apakah sudah dikirim
	$jjgkirim=$jjgpanen=$jjgafkir=0;
	$tersedia=$terkirim=0;
	$s = "select sum(jjg) as jjgkirim from ".$dbname.".kebun_spb_vw where  blok='".$row['kodeorg']."'"; 
	$b = fetchdata($s);
	$jjgkirim =$b[0]['jjgkirim'];
	
	$q = "select sum(jjgpanen) as jjgpanen, sum(jjgafkir) as jjgafkir from ".$dbname.".kebun_rekappnn where tanggal<='".$dataH[0]['tanggal']."' and blok='".$row['kodeorg']."'";
	$e = fetchData($q);
	$jjgpanen =$e[0]['jjgpanen'];
	$jjgafkir =$e[0]['jjgafkir'];
	
	#cek data di rekappnn
	$sql = "select * from ".$dbname.".kebun_rekappnn where tanggal='".$dataH[0]['tanggal']."' and blok='".$row['kodeorg']."'";
	$req = fetchData($sql);
	if(count($req)>0){
		$tersedia = $jjgpanen+$row['hasilkerja'];
		$terkirim = $jjgafkir+$jjgkirim;
		
		if(($tersedia-$terkirim)<0){
			throw new PDOException("Jumlah janjang SPB lebih besar dari jumlah janjang panen sehingga mengakibatkan Restan minus, jumlah janjang restan blok ".$nmorg[$row['kodeorg']]." : ".($tersedia-$terkirim)."");
		}
		
		$data = array(
			'luaspanen'  => $req[0]['luaspanen']+$row['luaspanen'],
			'tenagakerja'=> $req[0]['tenagakerja']+$row['jumlahhk'],
			'jjgpanen'   => $req[0]['jjgpanen']+$row['hasilkerja'],
			'brondolan'  => $req[0]['brondolan']+$row['brondolan'],
			'bjr'        => $row['bjr'],
			'kgkebun'    => $req[0]['kgkebun']+(round($row['bjr']*$row['hasilkerja'],0)),
			'posting'    => '1',
			'updateby'   => $_SESSION['standard']['userid'],
			'updatetime' => date("Y-m-d H:i:s"),
			'postingby'  => $_SESSION['standard']['userid'],
			'postingdate'=> date("Y-m-d H:i:s")
		);

		$where = "tanggal='".$dataH[0]['tanggal']."' and blok='".$row['kodeorg']."'";
			
		$query = updateQuery($dbname,'kebun_rekappnn',$data,$where);
		$owlPDO->exec($query);
	}else{
		$tersedia = $jjgpanen;
		$terkirim = $jjgafkir+$jjgkirim;
		
		if(($tersedia-$terkirim)<0){
			throw new PDOException("Jumlah janjang SPB lebih besar dari jumlah janjang panen sehingga mengakibatkan Restan minus, jumlah janjang restan blok ".$nmorg[$row['kodeorg']]." : ".($tersedia-$terkirim)."");
		}
		
		$str = "select * from ".$dbname.".setup_blok where kodeorg='".$row['kodeorg']."'"; 
		$res = fetchdata($str);
		$luasblok = $res[0]['luasareaproduktif'];
		
		$data = array(
			'divisi'      => substr($row['kodeorg'],0,6),
			'tanggal'     => $dataH[0]['tanggal'],
			'blok'        => $row['kodeorg'],
			'tahuntanam'  => $row['tahuntanam'],
			'luasproduksi'=> $luasblok,
			'luaspanen'   => $row['luaspanen'],
			'tenagakerja' => $row['jumlahhk'],
			'jjgpanen'    => $row['hasilkerja'],
			'brondolan'   => $row['brondolan'],
			'bjr'         => $row['bjr'],
			'kgkebun'     => round($row['bjr']*$row['hasilkerja'],0),
			'jjgafkir'    => 0,
			'keterangan'  => '',
			'norefffile'  => '',
			'posting'     => '1',
			'updateby'    => $_SESSION['standard']['userid'],
			'updatetime'  => date("Y-m-d H:i:s"),
			'postingby'   => $_SESSION['standard']['userid'],
			'postingdate' => date("Y-m-d H:i:s")
		);
		
		$query = insertQuery($dbname,'kebun_rekappnn',$data,array_keys($data));
		$owlPDO->exec($query);
	}
}
 */

#validasi ke rekappnn di lepas diganti insert dari kebun_prestasi ke kebun_rekappnn
#ambil jumlah jjg di kegiatan panen
$kodeBlok=array();
foreach($dataD as $row){
	$kodeBlok[]['kodeorg'] = $row['kodeorg'];
}
foreach($kodeBlok as $row){
	$str = "select kodeorg,tanggal, sum(hasilkerja) as hasilkerja, sum(hkpanenperhari) as hk from ".$dbname.".kebun_prestasi_vs_hk where kodeorg='".$row['kodeorg']."' and tanggal='".($dataH[0]['tanggal'])."' group by kodeorg";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
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
	while($bar2=$res2->fetch()){
		$kdblokpnn[$bar2['blok']]=$bar2['blok'];
		$jjgrkppnn[$bar2['blok']]=$bar2['jjgpanen'];
		$hkrkppnn[$bar2['blok']]=$bar2['hk'];
	}	
}

$nmblok=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$selisih=$selisihHk=array();
foreach($kdblokpnn as $blok){
	$selisih[$blok]=($jjgkegpnn[$blok] - $jjgrkppnn[$blok]);
	$selisihHk[$blok]=($hkkegpnn[$blok] - $hkrkppnn[$blok]);

	if($selisih[$blok] > 0){
		$errjjg="Jlh Jjg di Keg Panen lebih besar dari Jjg di Rekap Panen :\n";
		$errjjg.="".$nmblok[$kdblokpnn[$blok]]." Rkp Pnn => ".$jjgrkppnn[$blok]." Jjg, Keg Pnn => ".$jjgkegpnn[$blok]." Jjg, Var => ".($jjgkegpnn[$blok]-$jjgrkppnn[$blok])." Jjg\n";
	}

	if($selisihHk[$blok] > 0.1){
		$errhk="Jlh HK di Keg Panen lebih besar dari HK di Rekap Panen :\n";
		$errhk.="".$nmblok[$kdblokpnn[$blok]]." Rkp Pnn => ".$hkrkppnn[$blok]." HK, Keg Pnn => ".number_format($hkkegpnn[$blok],2)." HK, Var => ".number_format(($hkkegpnn[$blok]-$hkrkppnn[$blok]),2)." HK\n";
	}
}

if($errjjg!=''){
	throw new PDOException($errjjg);	
}
if($errhk!=''){
	throw new PDOException($errhk);
}

#======================== Kegiatan Panen ===========================
$kodeJurnal  = 'PNN01';
$queryParam  = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet'," jurnalid='".$kodeJurnal."'");
$resParam    = fetchData($queryParam);
$akunkredit  = $resParam[0]['noakunkredit']; 
$akundebet   = $resParam[0]['noakundebet'];
$kodekegiatan= $akundebet."01";     
if(count($resParam)==0){
	throw new PDOException("Nomor akun belum ada, silahkan cek Keuangan - Setup - Parameter Jurnal dengan kode jurnal = ".$kodeJurnal."");
}

# untuk premi
$kodeJurnalpremi  = 'PNN02';
$queryParampremi  = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet'," jurnalid='".$kodeJurnalpremi."'");
$resParampremi    = fetchData($queryParampremi);
$akunkreditpremi  = $resParampremi[0]['noakunkredit']; 
$akundebetpremi   = $resParampremi[0]['noakundebet'];
$kodekegiatanpremi= $akundebetpremi."02";  

if(count($resParampremi)==0){
	throw new PDOException("Nomor akun belum ada, silahkan cek Keuangan - Setup - Parameter Jurnal dengan kode jurnal = ".$kodeJurnalpremi."");
}
# untuk premi kutib brondol
$kodeJurnalpremibrd  = 'PNN03';
$queryParampremibrd  = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet'," jurnalid='".$kodeJurnalpremibrd."'");
$resParampremibrd    = fetchData($queryParampremibrd);
$akunkreditpremibrd  = $resParampremibrd[0]['noakunkredit']; 
$akundebetpremibrd   = $resParampremibrd[0]['noakundebet'];
$kodekegiatanpremibrd= $akundebetpremibrd."06";  
if(count($resParampremibrd)==0){
	throw new PDOException("Nomor akun belum ada, silahkan cek Keuangan - Setup - Parameter Jurnal dengan kode jurnal = ".$kodeJurnalpremibrd."");
}
# upah penalty
$kodeJurnalUpen  = 'PNN04';
$queryParamUpen  = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet'," jurnalid='".$kodeJurnalUpen."'");
$resParamUpen    = fetchData($queryParamUpen);
$akunkreditUpen  = $resParamUpen[0]['noakunkredit']; 
$akundebetUpen   = $resParamUpen[0]['noakundebet'];
$kodekegiatanUpen= $akunkreditUpen."01";     
if(count($resParamUpen)==0){
	throw new PDOException("Nomor akun belum ada, silahkan cek Keuangan - Setup - Parameter Jurnal dengan kode jurnal = ".$kodeJurnalUpen."");
}

# untuk denda premi
$kodeJurnalprednd  = 'PNN05';
$queryParamprednd  = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet'," jurnalid='".$kodeJurnalprednd."'");
$resParamprednd    = fetchData($queryParamprednd);
$akunkreditprednd  = $resParamprednd[0]['noakunkredit']; 
$akundebetprednd   = $resParamprednd[0]['noakundebet'];
$kodekegiatanprednd= $akunkreditprednd."02";  
if(count($resParamprednd)==0){
	throw new PDOException("Nomor akun belum ada, silahkan cek Keuangan - Setup - Parameter Jurnal dengan kode jurnal = ".$kodeJurnalprednd."");
}

#==========================================================================================
$cekkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"noakun like '611%'");

if($cekkeg[$kodekegiatan]==''){
	throw new PDOException("Kode kegiatan untuk upah panen tidak terdaftar di menu : Setup - Kegiatan.");
}
if($cekkeg[$kodekegiatanpremi]==''){
	throw new PDOException("Kode kegiatan untuk premi panen tidak terdaftar di menu : Setup - Kegiatan.");
}
if($cekkeg[$kodekegiatanpremibrd]==''){
	throw new PDOException("Kode kegiatan untuk kutib brondolan tidak terdaftar di menu : Setup - Kegiatan.");
}

if($cekkeg[$kodekegiatanUpen]==''){
	throw new PDOException("Kode kegiatan untuk penalty upah tidak terdaftar di menu : Setup - Kegiatan.");
}
if($cekkeg[$kodekegiatanprednd]==''){
	throw new PDOException("Kode kegiatan untuk denda potong buah tidak terdaftar di menu : Setup - Kegiatan.");
}

$kodept  =$pt[$dataH[0]['kodeorg']];
$kodeunit=$dataH[0]['kodeorg'];

#==========================================================================================
# Get Journal Counter
$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$kodeunit."' and periode='".$prdbln."'");
$tmpKonter = fetchData($queryJ);
if(count($tmpKonter)==0){throw new PDOException("Kelompok Jurnal ".$kodeJurnal." untuk kodeorg ".$kodept.", kodeunit : ".$kodeunit.", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}

$konter = @addZero($tmpKonter[0]['nokounter']+1,3);
# Transform No Jurnal dari No Transaksi
$tmpNoJurnal = explode('/',$param['notransaksi']);
$nojurnal = $tmpNoJurnal[0]."/".$tmpNoJurnal[1]."/".$kodeJurnal."/".$konter;
#==========================================================================================

# Get Journal Counter Premi
$queryJpremi = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnalpremi."' and kodeunit='".$kodeunit."' and periode='".$prdbln."'");
$tmpKonterpremi = fetchData($queryJpremi);
if(count($tmpKonterpremi)==0){throw new PDOException("Kelompok Jurnal ".$kodeJurnalpremi." untuk kodeorg ".$kodept.", kodeunit : ".$kodeunit.", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}

$konterpremi = @addZero($tmpKonterpremi[0]['nokounter']+1,3);
# Transform No Jurnal dari No Transaksi
$tmpNoJurnalpremi = explode('/',$param['notransaksi']);
$nojurnalpremi = $tmpNoJurnalpremi[0]."/".$tmpNoJurnalpremi[1]."/".$kodeJurnalpremi."/".$konterpremi;

#==========================================================================================

# Get Journal Counter Brondolan
$querybrd = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnalpremibrd."' and kodeunit='".$kodeunit."' and periode='".$prdbln."'");
$tmpKonterBrd = fetchData($querybrd);
if(count($tmpKonterBrd)==0){throw new PDOException("Kelompok Jurnal ".$kodeJurnalpremibrd." untuk kodeorg ".$kodept.", kodeunit : ".$kodeunit.", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}
$konterbrd = @addZero($tmpKonterBrd[0]['nokounter']+1,3);
# Transform No Jurnal dari No Transaksi
$tmpNoJurnalBrd = explode('/',$param['notransaksi']);
$nojurnalbrd = $tmpNoJurnalBrd[0]."/".$tmpNoJurnalBrd[1]."/".$kodeJurnalpremibrd."/".$konterbrd;
#==========================================================================================
# Get Journal Counter Upah Penalty
$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnalUpen."' and kodeunit='".$kodeunit."' and periode='".$prdbln."'");
$tmpKonterUpen = fetchData($queryJ);
if(count($tmpKonterUpen)==0){throw new PDOException("Kelompok Jurnal ".$kodeJurnalUpen." untuk kodeorg ".$kodept.", kodeunit : ".$kodeunit.", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}

$konterUpen = @addZero($tmpKonterUpen[0]['nokounter']+1,3);
# Transform No Jurnal dari No Transaksi
$tmpNoJurnalUpen = explode('/',$param['notransaksi']);
$nojurnalUpen = $tmpNoJurnalUpen[0]."/".$tmpNoJurnalUpen[1]."/".$kodeJurnalUpen."/".$konterUpen;
#==========================================================================================
# Get Journal Counter Denda Premi
$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnalprednd."' and kodeunit='".$kodeunit."' and periode='".$prdbln."'");
$tmpKonterprednd = fetchData($queryJ);
if(count($tmpKonterprednd)==0){throw new PDOException("Kelompok Jurnal ".$kodeJurnalprednd." untuk kodeorg ".$kodept.", kodeunit : ".$kodeunit.", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}

$konterprednd = @addZero($tmpKonterprednd[0]['nokounter']+1,3);
# Transform No Jurnal dari No Transaksi
$tmpNoJurnalprednd = explode('/',$param['notransaksi']);
$nojurnalprednd = $tmpNoJurnalprednd[0]."/".$tmpNoJurnalprednd[1]."/".$kodeJurnalprednd."/".$konterprednd;
#==========================================================================================
# untuk rk upah terima rkut
$queryJrkut = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$pt[$unitkary]."' and kodekelompok='M' and kodeunit='".$unitkary."' and periode='".$prdbln."'");
$tmpKonterrkut = fetchData($queryJrkut);
if(count($tmpKonterrkut)==0){throw new PDOException("Kelompok Jurnal M untuk kodeorg ".$pt[$unitkary].", kodeunit : ".$unitkary.", periode : ".$prdbln." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}
$konterrkut = @addZero($tmpKonterrkut[0]['nokounter']+1,3);
# Transform No Jurnal dari No Transaksi
$tmpNoJurnalrkut = explode('/',$param['notransaksi']);
$nojurnalrkut = $tmpNoJurnalrkut[0]."/".$unitkary."/M/".$konterrkut;

#======================== Nomor Jurnal =============================

# cari hari
$jenishari = getjenisharikerja($kodeunit,$dataH[0]['tanggal']);
if($jenishari=='LIBUR'){
	$libur=true;
}else{
	$libur=false;
}

$tulisanpanen='Potong Buah';
if($libur)$tulisanpanen.=' HM/HB';


#=== Transform Data ===
$dataRes['header'] = array();
$dataRes['detail'] = array();

$dataRespremi['header'] = array();
$dataRespremi['detail'] = array();

$dataResBrd['header'] = array();
$dataResBrd['detail'] = array();

$dataResUpen['header'] = array();
$dataResUpen['detail'] = array();

$dataResPrednd['header'] = array();
$dataResPrednd['detail'] = array();

$dataResrkub['header']=array();
$dataResrkut['header']=array();

$comment='';
$str="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$kodeunit."' and periode='".substr($dataH[0]['tanggal'],0,7)."'"; 
$res=fetchData($str);
foreach($res as $bar){
	$comment.="Unit ".$bar['kodeorg']." periode ".$bar['periode']." has been closed\n";
}
#Cek apakah unit sudah tutup buku
if($comment!=''){
	throw new PDOException($comment);
}

$premiint=$premiext=0;
$nokaryint=$nokaryext=0;
#1. Data Header
foreach($dataD as $row) {
	$whkary=" karyawanid='".$row['nik']."'";
	$unitkary=$unit[$row['nik']];
	
	if($unitkary==$_SESSION['empl']['lokasitugas']){
		$nokaryint+=1;
		$premiint+=($row['upahpremi']+$row['premibasis']+$row['premibasis2']+$row['upahpremilebihbasis']+$row['upahpremilebihbasis2']);
	}else{
		$nokaryext+=1;
		$premiext+=($row['upahpremi']+$row['premibasis']+$row['premibasis2']+$row['upahpremilebihbasis']+$row['upahpremilebihbasis2']);
		
		$totext+=($row['upahkerja']+$row['upahpremi']);
		#-($row['upahpenalty']+$row['rupiahpenalty'])
	}
}

# header lokasi bekerja
$dataRes['header'] = array(
	'nojurnal'     =>$nojurnal,
	'kodejurnal'   =>$kodeJurnal,
	'tanggal'      =>$dataH[0]['tanggal'],
	'tanggalentry' =>date('Ymd'),
	'posting'      =>'0',
	'totaldebet'   =>'0',
	'totalkredit'  =>'0',
	'amountkoreksi'=>'0',
	'noreferensi'  =>$dataH[0]['notransaksi'],
	'autojurnal'   =>'1',
	'matauang'     =>'IDR',
	'kurs'         =>'1',
	'revisi'       =>'0'
	// 'createby'     =>$_SESSION['standard']['userid'],
	// 'createtime'   =>date('Y-m-d H:i:s'),
	// 'updateby'     =>$_SESSION['standard']['userid'],
	// 'updatetime'   =>date('Y-m-d H:i:s')
);

$dataResUpen['header'] = array(
	'nojurnal'     =>$nojurnalUpen,
	'kodejurnal'   =>$kodeJurnalUpen,
	'tanggal'      =>$dataH[0]['tanggal'],
	'tanggalentry' =>date('Ymd'),
	'posting'      =>'0',
	'totaldebet'   =>'0',
	'totalkredit'  =>'0',
	'amountkoreksi'=>'0',
	'noreferensi'  =>$dataH[0]['notransaksi'],
	'autojurnal'   =>'1',
	'matauang'     =>'IDR',
	'kurs'         =>'1',
	'revisi'       =>'0'
	// 'createby'     =>$_SESSION['standard']['userid'],
	// 'createtime'   =>date('Y-m-d H:i:s'),
	// 'updateby'     =>$_SESSION['standard']['userid'],
	// 'updatetime'   =>date('Y-m-d H:i:s')
);

$dataRespremi['header'] = array(
	'nojurnal'     =>$nojurnalpremi,
	'kodejurnal'   =>$kodeJurnalpremi,
	'tanggal'      =>$dataH[0]['tanggal'],
	'tanggalentry' =>date('Ymd'),
	'posting'      =>'0',
	'totaldebet'   =>'0',
	'totalkredit'  =>'0',
	'amountkoreksi'=>'0',
	'noreferensi'  =>$dataH[0]['notransaksi'],
	'autojurnal'   =>'1',
	'matauang'     =>'IDR',
	'kurs'         =>'1',
	'revisi'       =>'0'
	// 'createby'     =>$_SESSION['standard']['userid'],
	// 'createtime'   =>date('Y-m-d H:i:s'),
	// 'updateby'     =>$_SESSION['standard']['userid'],
	// 'updatetime'   =>date('Y-m-d H:i:s')
);
$dataResBrd['header'] = array(
	'nojurnal'     =>$nojurnalbrd,
	'kodejurnal'   =>$kodeJurnalpremibrd,
	'tanggal'      =>$dataH[0]['tanggal'],
	'tanggalentry' =>date('Ymd'),
	'posting'      =>'0',
	'totaldebet'   =>'0',
	'totalkredit'  =>'0',
	'amountkoreksi'=>'0',
	'noreferensi'  =>$dataH[0]['notransaksi'],
	'autojurnal'   =>'1',
	'matauang'     =>'IDR',
	'kurs'         =>'1',
	'revisi'       =>'0'
	// 'createby'     =>$_SESSION['standard']['userid'],
	// 'createtime'   =>date('Y-m-d H:i:s'),
	// 'updateby'     =>$_SESSION['standard']['userid'],
	// 'updatetime'   =>date('Y-m-d H:i:s')
);

$dataResPrednd['header'] = array(
	'nojurnal'     =>$nojurnalprednd,
	'kodejurnal'   =>$kodeJurnalprednd,
	'tanggal'      =>$dataH[0]['tanggal'],
	'tanggalentry' =>date('Ymd'),
	'posting'      =>'0',
	'totaldebet'   =>'0',
	'totalkredit'  =>'0',
	'amountkoreksi'=>'0',
	'noreferensi'  =>$dataH[0]['notransaksi'],
	'autojurnal'   =>'1',
	'matauang'     =>'IDR',
	'kurs'         =>'1',
	'revisi'       =>'0'
	// 'createby'     =>$_SESSION['standard']['userid'],
	// 'createtime'   =>date('Y-m-d H:i:s'),
	// 'updateby'     =>$_SESSION['standard']['userid'],
	// 'updatetime'   =>date('Y-m-d H:i:s')
);


#header pemilik karyawan
if($nokaryext>0){
	$dataResrkut['header'] = array(
		'nojurnal'     =>$nojurnalrkut,
		'kodejurnal'   =>'M',
		'tanggal'      =>$dataH[0]['tanggal'],
		'tanggalentry' =>date('Ymd'),
		'posting'      =>'0',
		'totaldebet'   =>'0',
		'totalkredit'  =>'0',
		'amountkoreksi'=>'0',
		'noreferensi'  =>$dataH[0]['notransaksi'],
		'autojurnal'   =>'1',
		'matauang'     =>'IDR',
		'kurs'         =>'1',
		'revisi'       =>'0'
		// 'createby'     =>$_SESSION['standard']['userid'],
		// 'createtime'   =>date('Y-m-d H:i:s'),
		// 'updateby'     =>$_SESSION['standard']['userid'],
		// 'updatetime'   =>date('Y-m-d H:i:s')
	);
	$comment='';
	$str="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$unitkary."' and periode='".substr($dataH[0]['tanggal'],0,7)."'"; 
	$res=fetchData($str);
	foreach($res as $bar){
		$comment.="Unit ".$bar['kodeorg']." periode ".$bar['periode']." has been closed\n";
	}
	#Cek apakah unit penerima RK sudah tutup buku
	if($comment!=''){
		throw new PDOException($comment);
	}
}


#2. Data Detail
$i = 0; $noUrut = 0; $totalJumlah = 0;
foreach($dataD as $row) {
	$upahrk=$upahpenrk=$premirk=$brdrk=$premipenrk=0;
	if($unit[$row['nik']]!=$kodeunit){
		$totextdetail = ($row['upahkerja'])+($row['upahpremi']+$row['premibasis']+$row['premibasis2']+$row['upahpremilebihbasis']+$row['upahpremilebihbasis2']+$row['premibrondol'])-($row['upahpenalty']+$row['rupiahpenalty']);
		
		if($totextdetail<0){
			throw new PDOException("Ada pendapatan karyawan yang nilai minus, an. ".getNamaKaryawan($row['nik'])."");
		}
		
		$upahrk    =$row['upahkerja'];
		$upahpenrk =$row['upahpenalty'];
		$premirk   =$row['upahpremi']+$row['premibasis']+$row['premibasis2']+$row['upahpremilebihbasis']+$row['upahpremilebihbasis2'];
		$brdrk     =$row['premibrondol'];
		$premipenrk=$row['rupiahpenalty'];
		
		
		
		$totpenupah+=$row['upahpenalty'];
		$totpenpremi+=$row['rupiahpenalty'];
		
		if($pt[$unit[$row['nik']]]==$kodept){
			#intra dalam 1pt
			$jenis="intra"; 
		}else{
			$jenis="inter"; 
		}
		
		#$aknPt=makeOption($dbname,'keu_5caco','kodeorgtujuan,akunpiutang',"kodeorg='".$kodeunit."' and kodeorgtujuan='".$unit[$row['nik']]."' and jenis='lainnya'");
		
		$akunhutang=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$kodeunit."' and jenis='".$jenis."'");    		
		$akunpiutang=makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',"kodeorg='".$unit[$row['nik']]."' and jenis='".$jenis."'");
		
		
		#jika pakai caco ASP
		//$akunhutang=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$kodeunit."' and kodeorgtujuan='".$unit[$row['nik']]."' and jenis='lainnya'");
		//$akunpiutang=makeOption($dbname,'keu_5caco','kodeorgtujuan,akunpiutang',"kodeorg='".$kodeunit."' and kodeorgtujuan='".$unit[$row['nik']]."' and jenis='lainnya'");
		
		$noakunkreditrk=$akunhutang[$kodeunit];

		if ($akunhutang[$kodeunit]=='') {
			throw new PDOException("Account intraco or interco not available for ".$kodeunit.". Please setting on menu Finance > setup > COA for Intra/Interco.");
		}
		if ($akunpiutang[$unit[$row['nik']]]=='') {
			throw new PDOException("Account intraco or interco not available for ".$unit[$row['nik']].". Please setting on menu Finance > setup > COA for Intra/Interco.");
		}
		
		# Upah Detail debet sisi pemilik karyawan
		$noUrut++;
		$dataResrkut['detail'][] = array(
			'nojurnal'    =>$nojurnalrkut,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrut,
			'noakun'      =>$akunpiutang[$unit[$row['nik']]],
			'keterangan'  =>$tulisanpanen.' Hub RK '.$namakary[$row['nik']],
			'jumlah'      =>$row['upahkerja'],
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$unit[$row['nik']],
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$row['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);
		
		# Upah Detail debet sisi pemilik karyawan
		if($row['upahpenalty']!='0'){
			$noUrut++;
			$dataResrkut['detail'][] = array(
				'nojurnal'    =>$nojurnalrkut,
				'tanggal'     =>$dataH[0]['tanggal'],
				'nourut'      =>$noUrut,
				'noakun'      =>$akunpiutang[$unit[$row['nik']]],
				'keterangan'  =>'Upah Penalty Hub RK '.$namakary[$row['nik']],
				'jumlah'      =>$row['upahpenalty']*(-1),
				'matauang'    =>'IDR',
				'kurs'        =>'1',
				'kodeorg'     =>$unit[$row['nik']],
				'kodekegiatan'=>'',
				'kodeasset'   =>'',
				'kodebarang'  =>'',
				'nik'         =>$row['nik'],
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi' =>$row['notransaksi'],
				'noaruskas'   =>'',
				'kodevhc'     =>'',
				'nodok'       =>'',
				'kodeblok'    =>'',
				'revisi'      =>'0',
				'kodesegment' => $row['kodesegment']
				// 'createby'    =>$_SESSION['standard']['userid'],
				// 'createtime'  =>date('Y-m-d H:i:s'),
				// 'updateby'    =>$_SESSION['standard']['userid'],
				// 'updatetime'  =>date('Y-m-d H:i:s')
			);
		}
		
		if($premiext>0){
			$noUrut++;
			# Premi Detail debet sisi pemilik karyawan
			$dataResrkut['detail'][] = array(
				'nojurnal'    =>$nojurnalrkut,
				'tanggal'     =>$dataH[0]['tanggal'],
				'nourut'      =>$noUrut,
				'noakun'      =>$akunpiutang[$unit[$row['nik']]],
				'keterangan'  =>'Premi Potong Buah Hub RK '.$namakary[$row['nik']],
				'jumlah'      =>($row['upahpremi']+$row['premibasis']+$row['premibasis2']+$row['upahpremilebihbasis']+$row['upahpremilebihbasis2']),
				'matauang'    =>'IDR',
				'kurs'        =>'1',
				'kodeorg'     =>$unit[$row['nik']],
				'kodekegiatan'=>'',
				'kodeasset'   =>'',
				'kodebarang'  =>'',
				'nik'         =>$row['nik'],
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi' =>$row['notransaksi'],
				'noaruskas'   =>'',
				'kodevhc'     =>'',
				'nodok'       =>'',
				'kodeblok'    =>'',
				'revisi'      =>'0',
				'kodesegment' => $row['kodesegment']
				// 'createby'    =>$_SESSION['standard']['userid'],
				// 'createtime'  =>date('Y-m-d H:i:s'),
				// 'updateby'    =>$_SESSION['standard']['userid'],
				// 'updatetime'  =>date('Y-m-d H:i:s')
			);
		}
		
		if($row['premibrondol']>0){
			$noUrut++;
			# Premi Brondolan Detail debet sisi pemilik karyawan
			$dataResrkut['detail'][] = array(
				'nojurnal'    =>$nojurnalrkut,
				'tanggal'     =>$dataH[0]['tanggal'],
				'nourut'      =>$noUrut,
				'noakun'      =>$akunpiutang[$unit[$row['nik']]],
				'keterangan'  =>'Premi Kutib Brondolan Hub RK '.$namakary[$row['nik']],
				'jumlah'      =>$row['premibrondol'],
				'matauang'    =>'IDR',
				'kurs'        =>'1',
				'kodeorg'     =>$unit[$row['nik']],
				'kodekegiatan'=>'',
				'kodeasset'   =>'',
				'kodebarang'  =>'',
				'nik'         =>$row['nik'],
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi' =>$row['notransaksi'],
				'noaruskas'   =>'',
				'kodevhc'     =>'',
				'nodok'       =>'',
				'kodeblok'    =>'',
				'revisi'      =>'0',
				'kodesegment' => $row['kodesegment']
				// 'createby'    =>$_SESSION['standard']['userid'],
				// 'createtime'  =>date('Y-m-d H:i:s'),
				// 'updateby'    =>$_SESSION['standard']['userid'],
				// 'updatetime'  =>date('Y-m-d H:i:s')
			);
		}
		
		if($row['rupiahpenalty']>0){
			$noUrut++;
			# Penalty Premi Detail debet sisi pemilik karyawan
			$dataResrkut['detail'][] = array(
				'nojurnal'    =>$nojurnalrkut,
				'tanggal'     =>$dataH[0]['tanggal'],
				'nourut'      =>$noUrut,
				'noakun'      =>$akunpiutang[$unit[$row['nik']]],
				'keterangan'  =>'Penalty Premi Potong Buah Hub RK '.$namakary[$row['nik']],
				'jumlah'      =>$row['rupiahpenalty']*(-1),
				'matauang'    =>'IDR',
				'kurs'        =>'1',
				'kodeorg'     =>$unit[$row['nik']],
				'kodekegiatan'=>'',
				'kodeasset'   =>'',
				'kodebarang'  =>'',
				'nik'         =>$row['nik'],
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi' =>$row['notransaksi'],
				'noaruskas'   =>'',
				'kodevhc'     =>'',
				'nodok'       =>'',
				'kodeblok'    =>'',
				'revisi'      =>'0',
				'kodesegment' => $row['kodesegment']
				// 'createby'    =>$_SESSION['standard']['userid'],
				// 'createtime'  =>date('Y-m-d H:i:s'),
				// 'updateby'    =>$_SESSION['standard']['userid'],
				// 'updatetime'  =>date('Y-m-d H:i:s')
			);
		}
		$kodeorgrk = $unit[$row['nik']];
		
		$totalJumlah += ($row['upahkerja'])+($row['upahpremi']+$row['premibasis']+$row['premibasis2']+$row['upahpremilebihbasis']+$row['upahpremilebihbasis2']+$row['premibrondol'])-($row['upahpenalty']+$row['rupiahpenalty']);
	}
	
	
	$totalJumlahDetail = ($row['upahkerja'])+($row['upahpremi']+$row['premibasis']+$row['premibasis2']+$row['upahpremilebihbasis']+$row['upahpremilebihbasis2']+$row['premibrondol'])-($row['upahpenalty']+$row['rupiahpenalty']);
	
	
	# Detail (Kredit)
	# Upah Detail Kredit sisi pemilik karyawan
	if($totalJumlah>0){
		$noUrut++;
		$dataResrkut['detail'][] = array(
			'nojurnal'    =>$nojurnalrkut,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrut,
			'noakun'      =>$akunkredit,
			'keterangan'  =>$tulisanpanen.' Hub RK',
			'jumlah'      =>$totalJumlah*(-1),
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$kodeorgrk,
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$dataH[0]['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);
	}
	
	
	$noUrut++;
	# upah detail debet sisi lokasi bekerja
	# DEBET
	$dataRes['detail'][] = array(
		'nojurnal'    =>$nojurnal,
		'tanggal'     =>$dataH[0]['tanggal'],
		'nourut'      =>$noUrut,
		'noakun'      =>$akundebet,
		'keterangan'  =>$tulisanpanen.' '.$namakary[$row['nik']],
		'jumlah'      =>$row['upahkerja'],
		'matauang'    =>'IDR',
		'kurs'        =>'1',
		'kodeorg'     =>substr($row['kodeorg'],0,4),
		'kodekegiatan'=>$kodekegiatan,
		'kodeasset'   =>'',
		'kodebarang'  =>'',
		'nik'         =>$row['nik'],
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi' =>$row['notransaksi'],
		'noaruskas'   =>'',
		'kodevhc'     =>'',
		'nodok'       =>'',
		'kodeblok'    =>$row['kodeorg'],
		'revisi'      =>'0',
		'kodesegment' => $row['kodesegment']
		// 'createby'    =>$_SESSION['standard']['userid'],
		// 'createtime'  =>date('Y-m-d H:i:s'),
		// 'updateby'    =>$_SESSION['standard']['userid'],
		// 'updatetime'  =>date('Y-m-d H:i:s')
	);
		
	if($row['upahkerja']-$upahrk!='0'){
		$noUrut++;
		# KREDIT NON RK
		# upah detail debet sisi lokasi bekerja (NON RK)
		$dataRes['detail'][] = array(
			'nojurnal'    =>$nojurnal,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrut,
			'noakun'      =>$akunkredit,
			'keterangan'  =>$tulisanpanen.' '.$namakary[$row['nik']],
			'jumlah'      =>$row['upahkerja']*(-1),
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$dataH[0]['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$dataH[0]['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);	
	}
	if($totext>0){
		# Total Premi dan upah Kredit di sisi lokasi bekerja (RK)
		$noUrut++;
		$dataRes['detail'][] = array(
			'nojurnal'    =>$nojurnal,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrut,
			'noakun'      =>$noakunkreditrk,
			'keterangan'  =>$tulisanpanen.' Hub RK',
			'jumlah'      =>($totext)*(-1),
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$dataH[0]['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$dataH[0]['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);	
	}
	
	
	if($row['upahpenalty']-$upahpenrk!='0'){
		# DEBET NON RK
		$noUrutUpen++;
		$dataResUpen['detail'][] = array(
			'nojurnal'    =>$nojurnalUpen,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrutUpen,
			'noakun'      =>$akundebetUpen,
			'keterangan'  =>'Upah Penalty '.$tulisanpanen.' '.$namakary[$row['nik']],
			'jumlah'      =>$row['upahpenalty'],
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$dataH[0]['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$dataH[0]['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);
	}
	if($upahpenrk!='0'){
		# DEBET RK
		$noUrutUpen++;
		$dataResUpen['detail'][] = array(
			'nojurnal'    =>$nojurnalUpen,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrutUpen,
			'noakun'      =>$noakunkreditrk,
			'keterangan'  =>'Premi Potong Buah Hub RK',
			'jumlah'      =>$upahpenrk,
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$dataH[0]['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$dataH[0]['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);
	}
	
	# upahpenalty detail kredit sisi lokasi bekerja
	$noUrutUpen++;
	# KREDIT NON RK
	$dataResUpen['detail'][] = array(
		'nojurnal'    =>$nojurnalUpen,
		'tanggal'     =>$dataH[0]['tanggal'],
		'nourut'      =>$noUrutUpen,
		'noakun'      =>$akunkreditUpen,
		'keterangan'  =>'Upah Penalty '.$tulisanpanen.' '.$namakary[$row['nik']],
		'jumlah'      =>$row['upahpenalty']*(-1),
		'matauang'    =>'IDR',
		'kurs'        =>'1',
		'kodeorg'     =>substr($row['kodeorg'],0,4),
		'kodekegiatan'=>$kodekegiatanUpen,
		'kodeasset'   =>'',
		'kodebarang'  =>'',
		'nik'         =>$row['nik'],
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi' =>$row['notransaksi'],
		'noaruskas'   =>'',
		'kodevhc'     =>'',
		'nodok'       =>'',
		'kodeblok'    =>$row['kodeorg'],
		'revisi'      =>'0',
		'kodesegment' => $row['kodesegment']
		// 'createby'    =>$_SESSION['standard']['userid'],
		// 'createtime'  =>date('Y-m-d H:i:s'),
		// 'updateby'    =>$_SESSION['standard']['userid'],
		// 'updatetime'  =>date('Y-m-d H:i:s')
	);
	
	
	# Premi detail debet sisi lokasi bekerja
	$totpremipnn = ($row['upahpremi']+$row['premibasis']+$row['premibasis2']+$row['upahpremilebihbasis']+$row['upahpremilebihbasis2']);
	$noUrutPre++;
	$dataRespremi['detail'][] = array(
		'nojurnal'    =>$nojurnalpremi,
		'tanggal'     =>$dataH[0]['tanggal'],
		'nourut'      =>$noUrutPre,
		'noakun'      =>$akundebetpremi,
		'keterangan'  =>'Premi Potong Buah '.$namakary[$row['nik']],
		'jumlah'      =>$totpremipnn,
		'matauang'    =>'IDR',
		'kurs'        =>'1',
		'kodeorg'     =>substr($row['kodeorg'],0,4),
		'kodekegiatan'=>$kodekegiatanpremi,
		'kodeasset'   =>'',
		'kodebarang'  =>'',
		'nik'         =>$row['nik'],
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi' =>$row['notransaksi'],
		'noaruskas'   =>'',
		'kodevhc'     =>'',
		'nodok'       =>'',
		'kodeblok'    =>$row['kodeorg'],
		'revisi'      =>'0',
		'kodesegment' => $row['kodesegment']
		// 'createby'    =>$_SESSION['standard']['userid'],
		// 'createtime'  =>date('Y-m-d H:i:s'),
		// 'updateby'    =>$_SESSION['standard']['userid'],
		// 'updatetime'  =>date('Y-m-d H:i:s')
	);
		
	if($totpremipnn-$premirk!=0){
		# KREDIT NON RK
		$noUrutPre++;
		$dataRespremi['detail'][] = array(
			'nojurnal'    =>$nojurnalpremi,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrutPre,
			'noakun'      =>$akunkreditpremi,
			'keterangan'  =>'Premi Potong Buah '.$namakary[$row['nik']],
			'jumlah'      =>($totpremipnn-$premirk)*(-1),
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$dataH[0]['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$dataH[0]['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);
	}
	if($premirk!='0'){
		# DEBET RK
		$noUrutPre++;
		$dataRespremi['detail'][] = array(
			'nojurnal'    =>$nojurnalpremi,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrutPre,
			'noakun'      =>$noakunkreditrk,
			'keterangan'  =>'Premi Potong Buah Hub RK',
			'jumlah'      =>($premirk)*(-1),
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$dataH[0]['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$dataH[0]['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);	
	}
	
	
	# Premi brondol debet sisi lokasi bekerja
	$noUrutBrd++;
	$dataResBrd['detail'][] = array(
		'nojurnal'    =>$nojurnalbrd,
		'tanggal'     =>$dataH[0]['tanggal'],
		'nourut'      =>$noUrutBrd,
		'noakun'      =>$akundebetpremibrd,
		'keterangan'  =>'Premi Kutib Brondolan '.$namakary[$row['nik']],
		'jumlah'      =>$row['premibrondol'],
		'matauang'    =>'IDR',
		'kurs'        =>'1',
		'kodeorg'     =>substr($row['kodeorg'],0,4),
		'kodekegiatan'=>$kodekegiatanpremibrd,
		'kodeasset'   =>'',
		'kodebarang'  =>'',
		'nik'         =>$row['nik'],
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi' =>$row['notransaksi'],
		'noaruskas'   =>'',
		'kodevhc'     =>'',
		'nodok'       =>'',
		'kodeblok'    =>$row['kodeorg'],
		'revisi'      =>'0',
		'kodesegment' => $row['kodesegment']
		// 'createby'    =>$_SESSION['standard']['userid'],
		// 'createtime'  =>date('Y-m-d H:i:s'),
		// 'updateby'    =>$_SESSION['standard']['userid'],
		// 'updatetime'  =>date('Y-m-d H:i:s')
	);
	
	if($row['premibrondol']-$brdrk!=0){
		# KREDIT NON RK
		$noUrutBrd++;
		$dataResBrd['detail'][] = array(
			'nojurnal'    =>$nojurnalbrd,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrutBrd,
			'noakun'      =>$akunkreditpremibrd,
			'keterangan'  =>'Premi Kutib Brondolan '.$namakary[$row['nik']],
			'jumlah'      =>($row['premibrondol']-$brdrk)*(-1),
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$dataH[0]['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$dataH[0]['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);
	}
	if($brdrk!=0){
		# DEBET RK
		$noUrutBrd++;
		$dataResBrd['detail'][] = array(
			'nojurnal'    =>$nojurnalbrd,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrutBrd,
			'noakun'      =>$noakunkreditrk,
			'keterangan'  =>'Premi Kutib Brondolan Hub RK',
			'jumlah'      =>($brdrk)*(-1),
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$dataH[0]['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$dataH[0]['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);	
	}
	
	# KREDIT NON RK
	$noUrutPrednd++;
	$dataResPrednd['detail'][] = array(
		'nojurnal'    =>$nojurnalprednd,
		'tanggal'     =>$dataH[0]['tanggal'],
		'nourut'      =>$noUrutPrednd,
		'noakun'      =>$akunkreditprednd,
		'keterangan'  =>'Penalty Premi Potong Buah '.$namakary[$row['nik']],
		'jumlah'      =>$row['rupiahpenalty']*(-1),
		'matauang'    =>'IDR',
		'kurs'        =>'1',
		'kodeorg'     =>$dataH[0]['kodeorg'],
		'kodekegiatan'=>$kodekegiatanprednd,
		'kodeasset'   =>'',
		'kodebarang'  =>'',
		'nik'         =>$row['nik'],
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi' =>$dataH[0]['notransaksi'],
		'noaruskas'   =>'',
		'kodevhc'     =>'',
		'nodok'       =>'',
		'kodeblok'    =>$row['kodeorg'],
		'revisi'      =>'0',
		'kodesegment' => $row['kodesegment']
		// 'createby'    =>$_SESSION['standard']['userid'],
		// 'createtime'  =>date('Y-m-d H:i:s'),
		// 'updateby'    =>$_SESSION['standard']['userid'],
		// 'updatetime'  =>date('Y-m-d H:i:s')
	);
	
	# Penalty Premi detail debet sisi lokasi bekerja
	if($row['rupiahpenalty']-$premipenrk>0){
		#DEBET NON RK
		$noUrutPrednd++;
		$dataResPrednd['detail'][] = array(
			'nojurnal'    =>$nojurnalprednd,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrutPrednd,
			'noakun'      =>$akundebetprednd,
			'keterangan'  =>'Penalty Premi Potong Buah '.$namakary[$row['nik']],
			'jumlah'      =>$row['rupiahpenalty']-$premipenrk,
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>substr($row['kodeorg'],0,4),
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$row['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);
	}
	if($premipenrk!='0'){
		# DEBET RK
		$noUrutPrednd++;
		$dataResPrednd['detail'][] = array(
			'nojurnal'    =>$nojurnalprednd,
			'tanggal'     =>$dataH[0]['tanggal'],
			'nourut'      =>$noUrutPrednd,
			'noakun'      =>$noakunkreditrk,
			'keterangan'  =>'Penalty Premi Potong Buah Hub RK',
			'jumlah'      =>$premipenrk,
			'matauang'    =>'IDR',
			'kurs'        =>'1',
			'kodeorg'     =>$dataH[0]['kodeorg'],
			'kodekegiatan'=>'',
			'kodeasset'   =>'',
			'kodebarang'  =>'',
			'nik'         =>$row['nik'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi' =>$dataH[0]['notransaksi'],
			'noaruskas'   =>'',
			'kodevhc'     =>'',
			'nodok'       =>'',
			'kodeblok'    =>'',
			'revisi'      =>'0',
			'kodesegment' => $row['kodesegment']
			// 'createby'    =>$_SESSION['standard']['userid'],
			// 'createtime'  =>date('Y-m-d H:i:s'),
			// 'updateby'    =>$_SESSION['standard']['userid'],
			// 'updatetime'  =>date('Y-m-d H:i:s')
		);
	}
	
}

#=== Insert Data ===
# Update flag absensi
foreach($arrUpload as $row){
	$strAbs = "update ".$dbname.".upload_absensi set flag = 1 where karyawanid='".$row['nik']."' and tanggalabsen='".($dataH[0]['tanggal'])."'";
	$owlPDO->exec($strAbs); 
}

// echo"<pre>";
// print_r($dataRes);
// print_r($dataResUpen);
// print_r($dataRespremi);
// print_r($dataResPrednd);
// print_r($dataResrkut);
// echo"</pre>";
// exit("error");


$cek="";
# Header
if(count($dataRes)>0){
	$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
	$owlPDO->exec($queryH); 
	$cek.=$queryH."<br>";
	
	# Detail
	foreach($dataRes['detail'] as $key=>$dataDet) {
		$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
		$owlPDO->exec($queryD); 
	}
}

if(count($dataResUpen)>0){
	$queryH1 = insertQuery($dbname,'keu_jurnalht',$dataResUpen['header']);
	$owlPDO->exec($queryH1); 
	$cek.=$queryH1."<br>";
	
	# Detail
	foreach($dataResUpen['detail'] as $key=>$dataDet) {
		$queryD1 = insertQuery($dbname,'keu_jurnaldt',$dataDet);
		$owlPDO->exec($queryD1);
	}
}

if(count($dataRespremi)>0){
	$queryH2 = insertQuery($dbname,'keu_jurnalht',$dataRespremi['header']);
	$owlPDO->exec($queryH2); 
	$cek.=$queryH2."<br>";
	
	foreach($dataRespremi['detail'] as $key=>$dataDet) {
		$queryD2 = insertQuery($dbname,'keu_jurnaldt',$dataDet);
		$owlPDO->exec($queryD2); 
	}
}

if(count($dataResPrednd)>0){
	$queryH3 = insertQuery($dbname,'keu_jurnalht',$dataResPrednd['header']);
	$owlPDO->exec($queryH3); 
	$cek.=$queryH3."<br>";
	
	foreach($dataResPrednd['detail'] as $key=>$dataDet) {
		$queryD3 = insertQuery($dbname,'keu_jurnaldt',$dataDet);
		$owlPDO->exec($queryD3); 
	}
}
if(count($dataResBrd)>0){
	$queryH4 = insertQuery($dbname,'keu_jurnalht',$dataResBrd['header']);
	$owlPDO->exec($queryH4); 
	$cek.=$queryH4."<br>";
	
	foreach($dataResBrd['detail'] as $key=>$dataDet) {
		$queryD4 = insertQuery($dbname,'keu_jurnaldt',$dataDet);
		$owlPDO->exec($queryD4); 
	}
}

if(count($dataResrkut['header'])>0){
	$queryHrkut = insertQuery($dbname,'keu_jurnalht',$dataResrkut['header']);
	$owlPDO->exec($queryHrkut); 
	$cekx.=$queryHrkut."<br>";
	
	if($dataResrkut['detail']!=''){		
		foreach($dataResrkut['detail'] as $key=>$dataDet) {
			$queryDrkut = insertQuery($dbname,'keu_jurnaldt',$dataDet);
			$owlPDO->exec($queryDrkut); 
		}
	}
}

// echo $cek."<br>";
// echo $cekx."<br>";
// echo count($dataRes)."<br>";
// echo count($dataResUpen)."<br>";
// echo count($dataRespremi)."<br>";
// echo count($dataResPrednd)."<br>";
// echo count($dataResrkut)."<br>";
// exit("error");


// echo"<pre>";
// print_r($dataRes);
// print_r($dataResUpen);
// print_r($dataRespremi);
// print_r($dataResPrednd);
// print_r($dataResrkut);
// echo"</pre>";


    
#=== Switch Jurnal to 1 ===
# Cek if already posted
$queryJ= selectQuery($dbname,'kebun_aktifitas',"jurnal","notransaksi='".$param['notransaksi']."'");
$isJ   = fetchData($queryJ);

if($isJ[0]['jurnal']==1) {
	throw new PDOException("Data posted by another user");
} else {
	$queryToJ = updateQuery($dbname,'kebun_aktifitas',array('jurnal'=>1),"notransaksi='".$dataH[0]['notransaksi']."'");
	$owlPDO->exec($queryToJ); 
	
	$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$kodeunit."' and periode='".$prdbln."'");
	$owlPDO->exec($queryKonter); 
	
	$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonterpremi[0]['nokounter']+1),"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnalpremi."' and kodeunit='".$kodeunit."' and periode='".$prdbln."'");
	$owlPDO->exec($queryKonter); 
	
	$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonterprednd[0]['nokounter']+1),"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnalprednd."' and kodeunit='".$kodeunit."' and periode='".$prdbln."'");
	$owlPDO->exec($queryKonter); 

	$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonterUpen[0]['nokounter']+1),"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnalUpen."' and kodeunit='".$kodeunit."' and periode='".$prdbln."'");
	$owlPDO->exec($queryKonter); 
	
	$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonterBrd[0]['nokounter']+1),"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnalpremibrd."' and kodeunit='".$kodeunit."' and periode='".$prdbln."'");
	$owlPDO->exec($queryKonter); 
	
	$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonterrkut[0]['nokounter']+1),"kodeorg='".$pt[$unitkary]."' and kodekelompok='M' and kodeunit='".$unitkary."' and periode='".$prdbln."'");
	$owlPDO->exec($queryKonter); 
}
    
# exekusi query di atas
	$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
}

#hapus header yg tidak ada detailnya
$str="delete from ".$dbname.".keu_jurnalht where noreferensi='".$dataH[0]['notransaksi']."' and totaldebet='0' and totalkredit='0' and nojurnal not in (select nojurnal from ".$dbname.".keu_jurnaldt)";
try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

#ada jurnal yg isinya kosong
$str="delete from ".$dbname.".keu_jurnaldt where noreferensi='".$dataH[0]['notransaksi']."' and jumlah=0";
try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

?>