<?
//@Copy nangkoelframework
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

# Prestasi
$queryD = selectQuery($dbname,'kebun_prestasi',"*","notransaksi='".
    $param['notransaksi']."'");
$dataD = fetchData($queryD);

# Absensi
$queryAbs = selectQuery($dbname,'kebun_kehadiran','jhk,umr,insentif,nik',"notransaksi='".$param['notransaksi']."'");
$dataAbs = fetchData($queryAbs);

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
if(count($dataD)>1) {
    $error1 .= " Error: duplicate transaction\n";
}
if($error1!='') {
    echo "Data Error :\n".$error1;
    exit;
}

// Get Segment
$segment = $dataD[0]['kodesegment'];

#=== Hitung Cost dari Absensi (Perawatan) ===
$costRawat = 0;
$totalHk = 0;
if(!empty($dataAbs)) {
    foreach($dataAbs as $row) {
       // $costRawat += ($row['jhk']*$row['umr']) + $row['insentif'];
         $costRawat += $row['umr'] + $row['insentif'];
        $totalHk += $row['jhk'];
    }
}

#=== Cek if HK belum sama ===
//$qwe=$totalHk-$dataD[0]['jumlahhk']; buat ngecek pengurangan, bisa koma2 sampe e-16. dz april 27, 2012 10:13 kpw samarinda
$totalHk=round($totalHk,2);                             // diround hingga 2 desimal
$dataD[0]['jumlahhk']=round($dataD[0]['jumlahhk'],2);   // diround hingga 2 desimal
$qwe=$totalHk-$dataD[0]['jumlahhk'];
// if(empty($dataAbs) or ($totalHk!=$dataD[0]['jumlahhk'])) {
if($totalHk!=$dataD[0]['jumlahhk']) {
    echo 'Warning : HK Prestasi belum teralokasi dengan lengkap '.$qwe.'.';
    exit;
}
#=== cek apakah di setup ada materialnya ===
# Ambil data dari  kebun_pakaimaterial
$queryM = selectQuery($dbname,'kebun_pakaimaterial',"*","notransaksi='".$param['notransaksi']."'");
$dataM = fetchData($queryM);

# Cek data di master kegiatan
$queryK = selectQuery($dbname,'setup_kegiatannorma',"*","kodekegiatan='".$dataD[0]['kodekegiatan']."'");
$dataK = fetchData($queryK);

if(empty($dataM) and !empty($dataK)){
	exit('Error : Kegiatan ini harus menggunakan material');
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
			
			// $errorUpload .= $row['nik']." = ".$optNamaKaryawan[$row['nik']]."\n";
			$errorUpload .= $nikkary[$row['nik']]." = ".$optNamaKaryawan[$row['nik']]."\n";
			$countUpload = $countUpload + 1;
		}
	}
	if($countUpload > 0){
		echo "Warning : Absen fingerprint untuk karyawan dg NIK : \n".$errorUpload."belum diupload.";
		exit;
	}
}



#===================================================================
$lstUnit=array();
$lstUph=array();
$sDr="select lokasitugas,sum(umr+insentif) as uphtot,kodeorganisasi as kodept from ".$dbname.".kebun_kehadiran a 
      left join ".$dbname.".datakaryawan b on a.nik=b.karyawanid
      where notransaksi='".$param['notransaksi']."'
      group by lokasitugas";
$rDr=fetchdata($sDr);
if(count($rDr)!=0){
    foreach($rDr as $row=>$lstData){
        $lstUnit[$lstData['lokasitugas']]=$lstData['lokasitugas'];
        $lstUph[$lstData['lokasitugas']]=$lstData['uphtot'];
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
#======================== Nomor Jurnal =============================

//cek apakah BKM material, jika BKM material pastikan gudangnya aktif pada periode yang sama
$tanggalzx=explode("-",$dataH[0]['tanggal']);
$tanggalqq=$tanggalzx[0].$tanggalzx[1].$tanggalzx[2];
$kodenyagudang='';
$strt="SELECT a.kodegudang FROM ".$dbname.".`kebun_pakaimaterial` a WHERE a.notransaksi='".$dataH[0]['notransaksi']."'";
$res=$owlPDO->query($strt) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($prm=$res->fetch())
{
        $kodenyagudang=$prm->kodegudang;
        
    }
	if($kodenyagudang!=''){
		if(($_SESSION['gudang'][$kodenyagudang]['start']>$tanggalqq) or ($_SESSION['gudang'][$kodenyagudang]['end']<$tanggalqq)){
			exit("error: periode aktif gudang tidak sama dengan kebun.");
		}
	}

#=== Transform Data ===
$dataRes['header'] = array();
$dataRes['detail'] = array();

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
                'keterangan'=>'Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
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
                'keterangan'=>'Pemeliharaan '.$dataH[0]['tipetransaksi'],
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
                'keterangan'=>'Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
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
                'keterangan'=>'Pemeliharaan '.$dataH[0]['tipetransaksi'],
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
        exit("Warning : ".$comment);
    }
}
if(count($lstUnit[$_SESSION['empl']['lokasitugas']])!=0){
    foreach($dataD as $row) {
        $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$dataH[0]['tanggal'],
            'nourut'=>$noUrut,
            'noakun'=>$resKeg[$row['kodekegiatan']]['akun'],
            'keterangan'=>'Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'],
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
    //  $totalJumlah += ($row['jumlahhk'] * $row['umr']) + $row['upahpremi'] + $row['upahkerja'];
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
        'keterangan'=>'Pemeliharaan '.$dataH[0]['tipetransaksi'],
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



$dataRes['header']['totaldebet'] = ($totalJumlah+$totRpRK);
$dataRes['header']['totalkredit'] = ($totalJumlah+$totRpRK);
?>