<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
$param = $_POST;
$tgl = tanggalsystem(checkPostGet('tanggal', ''));
try {
	$owlPDO->beginTransaction();
	
#=== Get Data ===
# Header
$queryH = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".
    $param['notransaksi']."'");
$dataH = fetchData($queryH);

# cek periode akutansi
$str="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$dataH[0]['kodeorg']."' and periode='".substr(tanggalsystemn(tanggalnormal($tgl)),0,7)."'"; 
if(count(fetchData($str))>0){
	throw new PDOException('Periode akutansi sudah di tutup !!!');
}

# cek periode gaji
$str="select * from ".$dbname.".sdm_5periodegaji where sudahproses=1 and kodeorg='".$dataH[0]['kodeorg']."' and periode='".substr(tanggalsystemn(tanggalnormal($tgl)),0,7)."'"; 
if(count(fetchData($str))>0){
	throw new PDOException('Periode gaji sudah di tutup !!!');
}

#====cek periode===============================
$dataH[0]['tanggal']=$tgl;
#$tgl = str_replace("-","",$dataH[0]['tanggal']);
if($_SESSION['org']['period']['start']>$tgl)
    throw new PDOException('Tanggal diluar periode aktif');
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
if(count($dataD)>1) {
    $error1 .= " duplicate transaction\n";
}
if($error1!='') {
    throw new PDOException("Data :\n".$error1);
}
# Get Segment
$segment = $dataD[0]['kodesegment'];
#=== Hitung Cost dari Absensi (Perawatan) ===
$costRawat = 0;
$totalHk = 0;
if(!empty($dataAbs)) {
    foreach($dataAbs as $row) {
        $costRawat += $row['umr'] + $row['insentif'];
        $totalHk += $row['jhk'];
    }
}
#=== Cek if HK belum sama ===
$totalHk=round($totalHk,2);                             // diround hingga 2 desimal
$dataD[0]['jumlahhk']=round($dataD[0]['jumlahhk'],2);   // diround hingga 2 desimal
$qwe=$totalHk-$dataD[0]['jumlahhk'];
if($totalHk!=$dataD[0]['jumlahhk']) {
    throw new PDOException('HK Prestasi belum teralokasi dengan lengkap '.$qwe.'.');
}
#=== cek apakah di setup ada materialnya ===
# Ambil data dari  kebun_pakaimaterial
$queryM = selectQuery($dbname,'kebun_pakaimaterial',"*","notransaksi='".$param['notransaksi']."'");
$dataM = fetchData($queryM);
# Cek data di master kegiatan
$queryK = selectQuery($dbname,'setup_kegiatannorma',"*","kodekegiatan='".$dataD[0]['kodekegiatan']."'");
$dataK = fetchData($queryK);

#=== Cek if Upload Absensi ===
$countUpload = 0;
$countUpload = "";
$arrUpload = array();
#===================================================================
$lstUnit=array();
$lstUph=array();
$sDr="select lokasitugas,sum(umr+insentif) as uphtot,kodeorganisasi as kodept from ".$dbname.".kebun_kehadiran a  left join ".$dbname.".datakaryawan b on a.nik=b.karyawanid where notransaksi='".$param['notransaksi']."' group by lokasitugas";
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
$tanggalzx=explode("-",$dataH[0]['tanggal']);
$tanggalqq=$tanggalzx[0].$tanggalzx[1].$tanggalzx[2];


$tmpNoJurnal = explode('/',$param['notransaksi']);
$nojurnal = $tanggalqq."/".$tmpNoJurnal[1]."/".$kodeJurnal."/".$konter;

if(strlen($nojurnal)<20){
	throw new PDOException("Periksa nomor jurnal !!!\n".$nojurnal."");
}
#======================== Nomor Jurnal =============================
//cek apakah BKM material, jika BKM material pastikan gudangnya aktif pada periode yang sama
$kodenyagudang='';

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

            if ($aknPt[$rwUnit]=='') {
                exit("Warning : Account intraco or interco not available for ".$rwUnit.". Please setting on menu Finance > setup > COA for Intra/Interco.");
            }
            
            if ($aknHtg[$_SESSION['empl']['lokasitugas']]=='') {
                exit("Warning : Account intraco or interco not available for ".$_SESSION['empl']['lokasitugas'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
            }

            #debet disisi pengguna karyaawan
            $dataRes['detail'][] = array(
                'nojurnal'=>$nojurnal,
                'tanggal'=>$dataH[0]['tanggal'],
                'nourut'=>$noUrut,
                'noakun'=>$resKeg[$row['kodekegiatan']]['akun'],
                'keterangan'=>'Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'].$dataD[0]['kodeorg'],
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
                'keterangan'=>'Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'].' '.$dataD[0]['kodeorg'],
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
			$tanggalzx=explode("-",$dataH[0]['tanggal']);
			$tanggalqq=$tanggalzx[0].$tanggalzx[1].$tanggalzx[2];
            #$tmpNoJurnal = explode('/',$param['notransaksi']);
            $nojurnal2 = $tanggalqq."/".$rwUnit."/M/".$konter;
			if(strlen($nojurnal2)<20){
				throw new PDOException("Periksa nomor jurnal !!!\n".$nojurnal2."");
			}
			
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
                'keterangan'=>'Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'].' '.$dataD[0]['kodeorg'],
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
                'keterangan'=>'Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'].' '.$dataD[0]['kodeorg'],
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
        throw new PDOException("".$comment);
    }
}

if(count($lstUnit[$_SESSION['empl']['lokasitugas']])!=0){
    foreach($dataD as $row) {
        $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$dataH[0]['tanggal'],
            'nourut'=>$noUrut,
            'noakun'=>$resKeg[$row['kodekegiatan']]['akun'],
            'keterangan'=>'Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'].' '.$dataD[0]['kodeorg'],
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
        'keterangan'=>'Pemeliharaan '.$resKeg[$row['kodekegiatan']]['nama'].' '.$dataD[0]['kodeorg'],
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
#=== Insert Data ===
$errorDB = "";
# Header
$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
$owlPDO->exec($queryH);
# Detail
if($errorDB==''){
    foreach($dataRes['detail'] as $key=>$dataDet) {
        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
		$owlPDO->exec($queryD);
    }
	if(count($dataResRk['header'])!=0){
		foreach($dataResRk['header'] as $key=>$dataDet) {
			$queryD = insertQuery($dbname,'keu_jurnalht',$dataDet);
			$owlPDO->exec($queryD);
		}
		foreach($dataResRk['detail'] as $key=>$dataDet) {
			$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
			$owlPDO->exec($queryD);
		}
	}
    #=== Switch Jurnal to 1 ===
    # Cek if already posted
    $queryJ = selectQuery($dbname,'kebun_aktifitas',"jurnal","notransaksi='".
        $param['notransaksi']."'");
    $isJ = fetchData($queryJ);
    if($isJ[0]['jurnal']==1) {
        throw new PDOException("Data posted by another user");
    } else {
        $queryToJ = updateQuery($dbname,'kebun_aktifitas',array('jurnal'=>1,'tanggal'=>$tgl),"notransaksi='".$dataH[0]['notransaksi']."'");
        $owlPDO->exec($queryToJ);
        $queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
		$owlPDO->exec($queryKonter);
		if(count($lstUnit)!=0){
			foreach($lstUnit as $rw=>$rwUnit){
				if($rwUnit!=$_SESSION['empl']['lokasitugas']){
					$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counterDt[$rwUnit]),"kodeorg='".$lstPt[$rwUnit]."' and kodekelompok='M'");
					$owlPDO->exec($queryKonter);    
				}
			}    
		}
    }
}
	
# proses jurnal material
# periksa apakah transaksi ini pernah di unposting
$str="select * from ".$dbname.".log_transaksiht where notransaksireferensi='".$param['notransaksi']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
if($numrows>0){
	throw new PDOException("Posting ulang kegiatan berhasil !!!");
}

$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
}