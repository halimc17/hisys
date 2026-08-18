<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;

// Validasi harus pengurangan
if($param['revHK']==$param['hkrealisasi']
   and $param['revHasil']==$param['hasilkerjarealisasi']
   and $param['revJumlah']==$param['jumlahrealisasi'])
	exit("Warning: Tidak ada perubahan pada data");

if($param['revHK']>$param['hkrealisasi'])
	exit("Warning: Revisi hanya jika kelebihan.\nHK Revisi lebih besar dari HK Realisasi");
if($param['revHasil']>$param['hasilkerjarealisasi'])
	exit("Warning: Revisi hanya jika kelebihan.\nHasil Kerja Revisi lebih besar dari Hasil Kerja Realisasi");
if($param['revJumlah']>$param['jumlahrealisasi'])
	exit("Warning: Revisi hanya jika kelebihan.\nJumlah Revisi lebih besar dari Jumlah Realisasi");

#=== Get Data ===
# Get PT
$pt = getPT($dbname,$param['kodeorg']);
if($pt==false) {
    $pt = getHolding($dbname,$param['kodeorg']);
}

# Convert Tanggal
$tgl = tanggalsystem($param['tanggal']);
$tglSys = date('Y-m-d');

// SPK
$queryH = selectQuery($dbname,'log_spkht',"*","notransaksi='".$param['notransaksi']."'");
$resH = fetchData($queryH);

// Insert ke BASPK Revisi
$dataRev = array(
	'notransaksi' => $param['notransaksi'],
	'kodeblok' => $param['blokalokasi'],
	'kodekegiatan' => $param['kodekegiatan'],
	'tanggal' => $tgl,
	'blokspkdt' => $param['kodeblok'],
	'kodesegment' => $param['kodesegment'],
	'revisihasilkerja' => $param['revHasil'],
	'revisihk' => $param['revHK'],
	'revisijumlah' => $param['revJumlah'],
);
$qInsRev = insertQuery($dbname,'log_baspk_rev',$dataRev);
try{
	$owlPDO->exec($qInsRev); 
}catch(PDOException $e){
    exit("DB Error: ".$e->getMessage());
}

# periksa tanggal periode akuntansi===============
if($_SESSION['org']['period']['start']>$tgl)
    exit('Error:Tanggal diluar periode aktif');

# Get Akun
$kodeJurnal = 'SPK1';
$optKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,noakun',
    "kodekegiatan='".$param['kodekegiatan']."'");
$optSupp = makeOption($dbname,'log_5klsupplier','kode,noakun',
    "kode='".substr($param['koderekanan'],0,4)."'");

#======================== Nomor Jurnal =============================
# Get Journal Counter
$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
$tmpKonter = fetchData($queryJ);
$konter = addZero($tmpKonter[0]['nokounter']+1,3);

# Transform No Jurnal dari No Transaksi
$nojurnal = str_replace('-','',$tglSys)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
#======================== /Nomor Jurnal ============================
# Alokasi Blok
if(strlen($param['blokalokasi'])>5) {//di edit ama ginting dari 10 jadi 5
    $blok = $param['blokalokasi'];
} else {
    $blok = '';
}

#kusus jika project
$kodeasset='';
 if(substr($param['blokalokasi'],0,2)=='AK' or substr($param['blokalokasi'],0,2)=='PB')
 {
     #ambil akun aktiva dalam konstruksi
     $tipeasset=substr($param['blokalokasi'],3,3);
     $tipeasset=  str_replace("0","",$tipeasset);
     $str="select akunak from ".$dbname.".sdm_5tipeasset where kodetipe='".$tipeasset."'";
	 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	 $res->setFetchMode(PDO::FETCH_OBJ);
	 $numrows=owlBaris($res);
     if($numrows<1)
     {
         exit(" Error: Akun aktiva dalam konstruksi untuk ".$tipeasset." beum disetting dari keuangan->setup->tipeasset");
     }
     else
     {
         while($bar=$res->fetch())
         {
            if($bar->akunak==''){
                exit(" Error: Akun aktiva dalam konstruksi untuk ".$tipeasset." beum disetting dari keuangan->setup->tipeasset");
            }
            else{
//                $param['kodekegiatan']='';
                $kodeasset=$param['blokalokasi'];    
                $blok='';
                $optKeg[$param['kodekegiatan']]=$bar->akunak;
            }
         }
     } 
 }

$jumlahJurnal = $param['jumlahrealisasi'] - $param['revJumlah'];
if($jumlahJurnal==0) exit;

/** [PPn] *********************************************************************/
// Get Akun Ppn
$qAkun = selectQuery($dbname,'setup_parameterappl',"nilai",
					 "kodeaplikasi='TX' and kodeparameter='PPNINV'");
$resAkun = fetchData($qAkun);
$akunPpn = empty($resAkun)? "": $resAkun[0]['nilai'];

// Get Nilai PPn & Pph
$optPajak = makeOption($dbname,'log_spk_tax',"noakun,nilai","notransaksi='".
					   $param['notransaksi']."' and kodeorg='".$param['kodeorg']."'");

// Proporsi Rupiah
$proporsi = $jumlahJurnal / $resH[0]['nilaikontrak'];

// Pisah Ppn dan Pph
$ppn = $pph = 0;
foreach($optPajak as $noakun=>$nilai) {
	if($noakun==$akunPpn) {
		$ppn += $nilai * $proporsi;
	} else {
		$pph += $nilai * $proporsi;
	}
}
$hutang = $jumlahJurnal + $ppn - $pph;
/** [/PPn] *********************************************************************/

# Prep Header
$dataRes['header'] = array(
    'nojurnal'=>$nojurnal,
    'kodejurnal'=>$kodeJurnal,
    'tanggal'=>$tglSys,
    'tanggalentry'=>date('Ymd'),
    'posting'=>0,
    'totaldebet'=>$jumlahJurnal,
    'totalkredit'=>$jumlahJurnal,
    'amountkoreksi'=>'0',
    'noreferensi'=>$param['notransaksi'],
    'autojurnal'=>'1',
    'matauang'=>'IDR',
    'kurs'=>'1',
    'revisi'=>'0'
);

# Data Detail
$noUrut = 1;

# Kredit
$dataRes['detail'][] = array(
    'nojurnal'=>$nojurnal,
    'tanggal'=>$tglSys,
    'nourut'=>$noUrut,
    'noakun'=>$optKeg[$param['kodekegiatan']],
    'keterangan'=>'Revisi BASPK '.$param['kodeorg'].'/'.$param['notransaksi'],
    'jumlah'=>$jumlahJurnal * (-1),
    'matauang'=>'IDR',
    'kurs'=>'1',
    'kodeorg'=>$param['kodeorg'],
    'kodekegiatan'=>$param['kodekegiatan'],
    'kodeasset'=>$kodeasset,
    'kodebarang'=>'',
    'nik'=>'',
    'kodecustomer'=>'',
    'kodesupplier'=>'',
    'noreferensi'=>$param['notransaksi'],
    'noaruskas'=>'',
    'kodevhc'=>'',
    'nodok'=>'',
    'kodeblok'=>$blok,
    'revisi'=>'0',
	'kodesegment' => $param['kodesegment']
);
$noUrut++;

# Debet
$dataRes['detail'][] = array(
    'nojurnal'=>$nojurnal,
    'tanggal'=>$tglSys,
    'nourut'=>$noUrut,
    'noakun'=>$optSupp[substr($param['koderekanan'],0,4)],
    'keterangan'=>'Revisi BASPK '.$param['kodeorg'].'/'.$param['notransaksi'],
    'jumlah'=>$hutang,
    'matauang'=>'IDR',
    'kurs'=>'1',
    'kodeorg'=>$param['kodeorg'],
    'kodekegiatan'=>$param['kodekegiatan'],
    'kodeasset'=>'',
    'kodebarang'=>'',
    'nik'=>'',
    'kodecustomer'=>'',
    'kodesupplier'=>$param['koderekanan'],
    'noreferensi'=>$param['notransaksi'],
    'noaruskas'=>'',
    'kodevhc'=>'',
    'nodok'=>'',
    'kodeblok'=>$blok,
    'revisi'=>'0',
	'kodesegment' => $param['kodesegment']
);
$noUrut++;

foreach($optPajak as $noakun=>$nilai) { // Pajak
	if($noakun!=$akunPpn) {
		$strPajak = 'Pph';
	} else {
		$nilai = $nilai * (-1);
		$strPajak = 'PPn';
	}
	$nilai = $nilai * $proporsi;
	
	$dataRes['detail'][] = array(
		'nojurnal'=>$nojurnal,
		'tanggal'=>$tgl,
		'nourut'=>$noUrut,
		'noakun'=>$noakun,
		'keterangan'=>'Revisi '.$strPajak.' BASPK '.$param['kodeorg'].'/'.$param['notransaksi'],
		'jumlah'=>$nilai,
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$param['kodeorg'],
		'kodekegiatan'=>$param['kodekegiatan'],
		'kodeasset'=>'',
		'kodebarang'=>'',
		'nik'=>'',
		'kodecustomer'=>'',
		'kodesupplier'=>$param['koderekanan'],
		'noreferensi'=>$param['notransaksi'],
		'noaruskas'=>'',
		'kodevhc'=>'',
		'nodok'=>'',
		'kodeblok'=>$blok,
		'revisi'=>'0',
		'kodesegment' => $param['kodesegment']
	);
	$noUrut++;
}

#========================== Proses Insert dan Update ==========================
#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
$headErr = '';


$insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
try{
	$owlPDO->exec($insHead); 
}catch(PDOException $e){
    $headErr .= 'Insert Header Error : '.$e->getMessage()."\n";
}

if($headErr=='') {
    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
    $detailErr = '';
    foreach($dataRes['detail'] as $row) {
        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
		try{
			$owlPDO->exec($insDet); 
		}catch(PDOException $e){
			$detailErr .= "Insert Detail Error : ".$e->getMessage()."\n".$insDet;
            break;
		}
    }
    
    if($detailErr=='') {
        # Header and Detail inserted
        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
        $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
            "kodeorg='".$_SESSION['org']['kodeorganisasi'].
            "' and kodekelompok='".$kodeJurnal."'");
		try{
			$owlPDO->exec($updJurnal); 
		}catch(PDOException $e){
			echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
            
			# Rollback if Update Failed
            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
            try{
				$owlPDO->exec($RBDet); 
			}catch(PDOException $e){
				echo "Rollback Delete Header Error : ".$e->getMessage()."\n";
                exit;
			}
            exit;
		}
    } else {
        echo $detailErr;
        
		# Rollback, Delete Header
        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
        try{
			$owlPDO->exec($RBDet); 
		}catch(PDOException $e){
			echo "Rollback Delete Header Error : ".$e->getMessage();
            exit;
		}
    }
} else {
    echo $headErr;
    exit;
}
?>