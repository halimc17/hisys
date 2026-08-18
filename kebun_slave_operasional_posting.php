<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
$param = $_POST;
try {
	$owlPDO->beginTransaction();
	
#=== Get Data ===
# Header
$queryH = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".
    $param['notransaksi']."'");
$dataH = fetchData($queryH);
#====cek periode===============================
$tgl = str_replace("-","",$dataH[0]['tanggal']);
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
if(empty($dataM) and !empty($dataK)){
	throw new PDOException('Kegiatan ini harus menggunakan material');
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
# Update flag absensi
foreach($arrUpload as $row){
	$strAbs = "update ".$dbname.".upload_absensi set flag = 1 where karyawanid='".$row['nik']."' and tanggalabsen='".($dataH[0]['tanggal'])."'";
	$owlPDO->exec($strAbs);
}
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
        $queryToJ = updateQuery($dbname,'kebun_aktifitas',array('jurnal'=>1),"notransaksi='".$dataH[0]['notransaksi']."'");
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
	throw new PDOException("Posting ulang kegiatan berhasil, namun untuk material pada kegiatan tsb tidak dapat di posting ulang");
}
#ambil notransaksi gudang
$nomor=Array();    
$str="select distinct kodegudang from ".$dbname.".kebun_pakaimaterial where notransaksi='".$param['notransaksi']."' and kodegudang!=''";
$resc=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$resc->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$resc->fetch()){
	$gudang =$bar1->kodegudang;
	$num=1; #default value 
	$str="select max(notransaksi) as notransaksi from ".$dbname.".log_transaksiht where tipetransaksi=5 and kodegudang='".$gudang."' 
	and tanggal like '".$_SESSION['gudang'][$gudang]['tahun'].'-'.$_SESSION['gudang'][$gudang]['bulan']."%' and notransaksireferensi!='' order by notransaksi desc limit 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);        
	while($bar=$res->fetch()){
		$num=$bar->notransaksi;
		if($num!=''){
			$num=substr($num,7,4);
			$num=1+intval($num);
			$num=str_pad($num,4,"0",STR_PAD_LEFT);
		}else{
			$num="0001";
		}
	}    
	// $nomor[$bar1->kodegudang]=$_SESSION['org']['period']['tahun'].$_SESSION['org']['period']['bulan']."M".$num;
	$nomor[$bar1->kodegudang]=$_SESSION['gudang'][$gudang]['tahun'].$_SESSION['gudang'][$gudang]['bulan']."M".$num;
	
	
	
	#ambil periode akintansi masing-masing gudang
	$strd="select periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$bar1->kodegudang."' and tutupbuku=0";
	$resd=$owlPDO->query($strd) or die(print " Gagal: ".PDOException::getMessage());
	$resd->setFetchMode(PDO::FETCH_OBJ);
	while($bard=$resd->fetch()){
		$periode[$bar1->kodegudang]=$bard->periode;
	}
}
$brg=Array();
$gud=Array();
$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
	  left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
	  where a.notransaksi='".$param['notransaksi']."' and a.kodegudang!=''";
$resa=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$resa->setFetchMode(PDO::FETCH_OBJ);
#ambil saldo dan harga rata
while($barf=$resa->fetch()){
	$saldo[$barf->kodegudang][$barf->kodebarang]=0;
	$harga[$barf->kodegudang][$barf->kodebarang]=0;
	
	$stru="select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from ".$dbname.".log_5saldobulanan where 
	kodegudang='".$barf->kodegudang."' and kodebarang='".$barf->kodebarang."' and periode='".$periode[$barf->kodegudang]."'";
	$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
	$resu->setFetchMode(PDO::FETCH_OBJ);
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
	"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal1."'");
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
	  where a.notransaksi='".$param['notransaksi']."' and a.kodegudang!=''";
$noUrut = 1;
$totalJumlah = 0;
$errAkunBarang='';
$namabarang='';
$resx=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$resx->setFetchMode(PDO::FETCH_OBJ); 
while($bab=$resx->fetch()) {
if(($bab->kwantitas<=0)||($bab->kwantitas=='')){
	throw new PDOException("Kuantitas Barang Tidak Boleh Kosong atau nol");
}    
#kredit
$namabarang=substr($bab->namabarang,0,25)." ".$bab->kwantitas." ".$bab->satuan;
if($harga[$bab->kodegudang][$bab->kodebarang]=='' or $harga[$bab->kodegudang][$bab->kodebarang]==0){
	throw new PDOException("Belum ada harga rata-rata barang ".$bab->kodebarang);
}
if(isset($akunbarang[substr($bab->kodebarang,0,3)]) and $akunbarang[substr($bab->kodebarang,0,3)]!=''){
	$dataResMat['detail'][] = array(
		'nojurnal'=>$nojurnal1,
		'tanggal'=>$dataH[0]['tanggal'],
		'nourut'=>$noUrut,
		'noakun'=>$akunbarang[substr($bab->kodebarang,0,3)],
		'keterangan'=>'Material BKM '. $resKeg[$row['kodekegiatan']]['nama']." ".$namabarang,
		'jumlah'=>$harga[$bab->kodegudang][$bab->kodebarang]*$bab->kwantitas*(-1),
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
    $totalJumlah +=$harga[$bab->kodegudang][$bab->kodebarang]*$bab->kwantitas;
    }else{
        throw new PDOException("Error: Belum ada akun untuk barang ".$bab->kodebarang);
    }
}
if($totalJumlah>0){
#debet
    $dataResMat['detail'][] = array(
        'nojurnal'=>$nojurnal1,
        'tanggal'=>$dataH[0]['tanggal'],
        'nourut'=>$noUrut,
        'noakun'=>$resKeg[$kodekegiatan]['akun'],
        'keterangan'=>'Material BKM '.$resKeg[$row['kodekegiatan']]['nama'].$dataD[0]['kodeorg'],
        'jumlah'=>$totalJumlah,
        'matauang'=>'IDR',
        'kurs'=>'1',
        'kodeorg'=>substr($kodeblok,0,4),
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
        'kodeblok'=>$kodeblok,
        'revisi'=>'0',
        'kodesegment' => $segment
    );
} 
	if($namabarang!=''){ # kalo transaksi BKM tanpa material, ga usah eksekusi jurnal barangnya
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
			
			$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter1[0]['nokounter']+1),"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal1."'");
			$owlPDO->exec($queryKonter);            
		}

		# prosess material first
		# =====================
		# ============ ambil PT
		$str="select induk from ".$dbname.".organisasi where kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
		$kodeorganisasi="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$kodeorganisasi=$bar->induk;
		}
		$awlheader=1;
		$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
				left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
				where a.notransaksi='".$param['notransaksi']."' and a.kodegudang!=''";
		$dataMat['header']=Array();
		$dataMat['detail']=Array();
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
				'kodeblok'=>$kodeblok,
				'waktutransaksi'=>date('Y-m-d H:i:s'),
				'updateby'=>$_SESSION['standard']['userid'], 
				'kodekegiatan'=>$kodekegiatan, 
				'kodemesin'=>'', 
				'statussaldo'=>1, 
				'hargarata'=>$harga[$bar->kodegudang][$bar->kodebarang],
				'nopo'=>'',
				'kodesegment' => $segment,
				'catatan' => null,
                'namafile'=>'',
				'kmhm'=>''
			);
			$awlheader++;
		}
		#periksa apakah saldo mencukupi:
		$str="select a.*,b.namabarang,b.satuan from ".$dbname.".kebun_pakaimaterial a
			  left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
			  where a.notransaksi='".$param['notransaksi']."' and a.kodegudang!=''";  
		$errsaldo='';
		$resku=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$resku->setFetchMode(PDO::FETCH_OBJ);
		while($barf=$resku->fetch()){
			if($saldo[$barf->kodegudang][$barf->kodebarang]>=$barf->kwantitas){
			}else {
				throw new PDOException("Tidak cukup saldo untuk barang ".$barf->kodebarang." pada gudang ".$barf->kodegudang." pada periode ".$periode[$barf->kodegudang]);
			}
			if($harga[$barf->kodegudang][$barf->kodebarang]>0){
			}else{
				throw new PDOException("Tidak cukup saldo untuk barang ".$barf->kodebarang." pada gudang ".$barf->kodegudang." pada periode ".$periode[$barf->kodegudang]);
			}  
			$jumlah[$barf->kodegudang][$barf->kodebarang]=$barf->kwantitas;   
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
		$errSal='';
		foreach($gud as $keygud=>$valgud){
			foreach($brg[$valgud] as $keybrg=>$valbrg){
				$sth="update ".$dbname.".log_5saldobulanan set saldoakhirqty=".($saldo[$valgud][$valbrg]-$jumlah[$valgud][$valbrg]).",
				nilaisaldoakhir=".($nilaisaldoakhir[$valgud][$valbrg]-($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg])).",
				qtykeluar=".($qtykeluar[$valgud][$valbrg]+$jumlah[$valgud][$valbrg]).",
				qtykeluarxharga=".($xkeluar[$valgud][$valbrg]+($jumlah[$valgud][$valbrg]*$harga[$valgud][$valbrg]))."  
				where periode='".$periode[$valgud]."' and kodegudang='".$valgud."' and kodebarang='".$valbrg."'"; 
				$owlPDO->exec($sth); 
				
				$stup="update ".$dbname.".kebun_pakaimaterial set hargasatuan=".$harga[$valgud][$valbrg]." where kodegudang='".$valgud."'
						   and kodebarang='".$valbrg."' and notransaksi='".$param['notransaksi']."'";
				$owlPDO->exec($stup);                        
			}
		}

		#update log_5masterbarangdt
		foreach($gud as $keygud=>$valgud){
			foreach($brg[$valgud] as $keybrg=>$valbrg){ 
				$strg="update ".$dbname.".log_5masterbarangdt set saldoqty=".($saldo[$valgud][$valbrg]-$jumlah[$valgud][$valbrg]).",
					hargalastout=".$harga[$valgud][$valbrg]." where kodegudang='".$valgud."' and kodebarang='".$valbrg."'";
				$owlPDO->exec($strg);                          
			}  
		}


	} # if namabarang kalo transaksi BKM tanpa material, ga usah eksekusi jurnal barangnya

$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
}