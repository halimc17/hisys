<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_POST;

/** Posting Bengkel **/
#=== Extract Data ====
# Get PT
$pt = getPT($dbname,$param['kodeorg']);
if($pt==false) {
    $pt = getHolding($dbname,$param['kodeorg']);
}

# Get Tanggal Akhir Periode
$qPeriod = selectQuery($dbname,'setup_periodeakuntansi','tanggalsampai',
    "kodeorg='".$param['kodeorg']."' and periode='".
    $param['param_periode']."'");
$resPeriod = fetchData($qPeriod);
$tanggalSampai = $resPeriod[0]['tanggalsampai'];

# Get Akun
$kodejurnal = 'WS1';
$qParam = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',
    "kodeaplikasi='WS' and jurnalid='".$kodejurnal."'");
$tmpParam = fetchData($qParam);
$akunDebet = $tmpParam[0]['noakundebet'];
$akunKredit = $tmpParam[0]['noakunkredit'];

#==================== Journal Counter ==================
$queryC = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
    "kodeorg='".$_SESSION['org']['kodeorganisasi'].
    "' and kodekelompok='".$kodejurnal."'");
$tmpC = fetchData($queryC);
if(empty($tmpC)) {
    echo "Warning : Kode Jurnal belum disetting untuk PT anda";
    exit;
}
$konter = addZero($tmpC[0]['nokounter']+1,3);
$tanggalJ = tanggalsystem(tanggalnormal($tanggalSampai));
$nojurnal = $tanggalJ."/".substr($param['kodeorg'],0,4).
    "/".$kodejurnal."/".$konter;
#==================== Journal Counter ==================

# Prep Header
$dataRes['header'] = array(
    'nojurnal'=>$nojurnal,
    'kodejurnal'=>$kodejurnal,
    'tanggal'=>$tanggalSampai,
    'tanggalentry'=>date('Ymd'),
    'posting'=>'0',
    'totaldebet'=>'0',
    'totalkredit'=>'0',
    'amountkoreksi'=>'0',
    'noreferensi'=>$param['kodevhc'],
    'autojurnal'=>'1',
    'matauang'=>'IDR',
    'kurs'=>'1'
);

# Data Detail
$noUrut = 1;

# Debet
$dataRes['detail'][] = array(
    'nojurnal'=>$nojurnal,
    'tanggal'=>$tanggalSampai,
    'nourut'=>$noUrut,
    'noakun'=>$akunDebet,
    'keterangan'=>'Biaya Bengkel '.$param['kodevhc'],
    'jumlah'=>$param['jumlah'],
    'matauang'=>'IDR',
    'kurs'=>'1',
    'kodeorg'=>$pt['kode'],
    'kodekegiatan'=>'',
    'kodeasset'=>'',
    'kodebarang'=>'',
    'nik'=>'',
    'kodecustomer'=>'',
    'kodesupplier'=>'',
    'noreferensi'=>'',
    'noaruskas'=>'',
    'kodevhc'=>$param['kodevhc'],
    'nodok'=>'',
    'kodeblok'=>$param['kodeorg'],
	'revisi'=>'0',
	'kodesegment'=>null
);
$noUrut++;

# Kredit
$dataRes['detail'][] = array(
    'nojurnal'=>$nojurnal,
    'tanggal'=>$tanggalSampai,
    'nourut'=>$noUrut,
    'noakun'=>$akunKredit,
    'keterangan'=>'Biaya Bengkel '.$param['kodevhc'],
    'jumlah'=>-1*$param['jumlah'],
    'matauang'=>'IDR',
    'kurs'=>'1',
    'kodeorg'=>$pt['kode'],
    'kodekegiatan'=>'',
    'kodeasset'=>'',
    'kodebarang'=>'',
    'nik'=>'',
    'kodecustomer'=>'',
    'kodesupplier'=>'',
    'noreferensi'=>'',
    'noaruskas'=>'',
    'kodevhc'=>$param['kodevhc'],
    'nodok'=>'',
    'kodeblok'=>$param['kodeorg'],
	'revisi'=>'0',
	'kodesegment'=>null
);
$noUrut++;

#========================== Proses Insert dan Update ==========================
#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
$headErr = '';
$insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
try{
    $owlPDO->exec($insHead);
}catch (PDOException $e){
    $headErr="Insert Header Error: ".$e->getMessage();
}

if($headErr=='') {
    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
    $detailErr = '';
    foreach($dataRes['detail'] as $row) {
        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
        try{
                $owlPDO->exec($insDet);
        }catch (PDOException $e){
            $detailErr= "Insert Detail Error: ".$e->getMessage();
        }
    }
    
    if($detailErr=='') {
        # Header and Detail inserted
        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
        $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
            "kodeorg='".$_SESSION['org']['kodeorganisasi'].
            "' and kodekelompok='".$kodejurnal."'");

        try{
                $owlPDO->exec($updJurnal);
               echo '1';
        }catch (PDOException $e){
            echo "Update Kode Jurnal Error : ".$e->getMessage();
            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                try{
                    $owlPDO->exec($RBDet);
                       echo "Error: Failed update journal counter";
                }catch (PDOException $e){
                    echo "Rollback Error : ".$e->getMessage();
                }

        }
    } else {
        echo $detailErr;
        # Rollback, Delete Header
        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
        try{
            $owlPDO->exec($RBDet);
        }catch (PDOException $e){
            echo "Rollback Error : ".$e->getMessage();
        }
    }
} else {
    echo $headErr;
    exit;
}
?>