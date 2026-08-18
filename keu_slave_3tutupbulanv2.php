<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_POST;
$tmpPeriod = explode('-',$param['periode']);
$tahunbulan = implode("",$tmpPeriod);
$tahun = $tmpPeriod[0];
$bulan = $tmpPeriod[1];
$proses = $_GET['proses'];


//cek apakah catu sudah diposting atau belum
$str="select * from ".$dbname.".sdm_catu where periodegaji='".$param['periode']."' 
		and kodeorg='".$_SESSION['empl']['lokasitugas']."' and posting=0";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
if($row>0){
	exit("Error:Catu belum di posting");
}
#cek apakah ada transaksi kas bank yang hutangunit1=0 dan noakunnya R/K
$itungTransKb="";
$rCekKasBk=array();
$sCekKasBk="select b.notransaksi from ".$dbname.".keu_kasbankdt a left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
       where b.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='HOLDING')
       and b.tanggal like '".$param['periode']."%' and a.noakun like '121%' and hutangunit1=0 ";
$rCekKasBk=fetchData($sCekKasBk);
if(!empty($rCekKasBk)){
    foreach($rCekKasBk as $row=>$lstData){
        $sCk="select * from ".$dbname.".keu_jurnalht where noreferensi='".$lstData['notransaksi']."' and nojurnal like '%".$_SESSION['empl']['lokasitugas']."%' and nojurnal like '%/M/%'";
        $rCk=fetchData($sCk);
        if(count($rCk)==0){
            if($row==0){
                $itungTransKb.="List Notransaksi Kas Bank HO :\n";
                $itungTransKb.=$lstData['notransaksi']."\n";    
            }else{
                $itungTransKb.=$lstData['notransaksi']."\n";    
            }
            
        }
    }
}
if($itungTransKb!=""){
    echo $itungTransKb;
    exit('warning: Transaksi Kas Bank HO berisi RK belum teralokasi');
}
//ambil akun laba tahun berjalan;
$stl=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLM'");
$akunCLM='';
$stl->setFetchMode(PDO::FETCH_OBJ);
while($bal=$stl->fetch()){
    $akunCLM=$bal->noakundebet;
}
//ambil akun laba ditahan
$stl=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLY'");
$akunCLY='';
$stl->setFetchMode(PDO::FETCH_OBJ);
while($bal=$stl->fetch()){
    $akunCLY=$bal->noakundebet;
}
//ambil batas bawah akun laba/rugi
$stl=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='RAT'");
$akunRAT='';
$stl->setFetchMode(PDO::FETCH_OBJ);
while($bal=$stl->fetch()){
    $akunRAT=$bal->noakundebet;
}
if($akunCLM=='' or $akunCLY=='' or $akunRAT=='')
{
    if($_SESSION['language']=='EN'){
        exit('Warning: Annual income account data, account  retained earnings and account limits profits / losses not yet listed on the parameters of the journal');
    }else{
        exit('Warning: data akun laba tahunan, akun laba ditahan dan batas akun laba/rugi belum terdaftar pada parameter jurnal');
    }
}

#periksa apakah sudah diposting semua transaksi kas dan bappp
$str=$owlPDO->query("select tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$param['periode']."' and kodeorg='".$_SESSION['empl']['lokasitugas']."'");
$currstart='';
$currend='';
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $currstart=$bar->tanggalmulai;
    $currend=$bar->tanggalsampai;
}
    
if($currstart=='' or $currend=='')
{

    exit('Warning: '.$_SESSION['empl']['lokasitugas'].' '.$_SESSION['lang']['periodetidaknormal']);//periodetidaknormal
}
else
{
	
	##periksa apakah ada kas hutang unit
	$str="select notransaksi,tanggal,jumlah from ".$dbname.".keu_kasbankht where pemilikhutang='".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
		  
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=$res->rowCount();
	if($numrows>0){
        echo "Ada transaksi kas/bank HO yang belum diposting terkait hutang unit \n";
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."\n"; 
        }
        exit('Warning');
    }
	
	
	
    #periksa kas
    $str=$owlPDO->query("select notransaksi,tanggal,jumlah from ".$dbname.".keu_kasbankht where kodeorg='".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($str);
    if($numrows>0){
        echo $_SESSION['lang']['cekkasbank']." :\n";
        $no=0;
        while($bar=$str->fetch()){
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlah,0)."\n"; 
        }
        exit('Warning');
    }

    #periksa bapp
    $str="select notransaksi,tanggal,jumlahrealisasi from ".$dbname.".log_baspk where kodeblok like '".$_SESSION['empl']['lokasitugas']."%'
          and tanggal between '".$currstart."' and '".$currend."' and statusjurnal=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0)
    {
        echo $_SESSION['lang']['cekspk']." :\n";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlahrealisasi,0)."\n"; 
        }
        exit('Warning');
    }
    #periksa jurnal tidak balance
    $str="select nojurnal,tanggal,debet,kredit from ".$dbname.".keu_jurnal_tidak_balance_vw where kodeorg = '".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."'
          and nojurnal not like '%/CLSM/%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        echo $_SESSION['lang']['notifjurnaltidakbalance']." :\n";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->nojurnal.":".tanggalnormal($bar->tanggal)."->(D)Rp. ".number_format($bar->debet,0).":(K)Rp. ".number_format($bar->kredit,0)."\n"; 
        }
        exit('Warning');
    }    
    #periksa gudang
    $str="select notransaksi,tanggal, kodegudang from ".$dbname.".log_transaksiht where post=0 and kodegudang like '".$_SESSION['empl']['lokasitugas']."%'
            and tanggal between '".$currstart."' and '".$currend."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    $stm='';
    if($numrows>0){
        while($bar=$res->fetch()){
             $stm.="Gudang : ".$bar->kodegudang." No -> ".$bar->notransaksi." -> ".$bar->tanggal."\n";
         }
       echo "Warning: ".$_SESSION['lang']['notifgudang']." \n".$stm; 
       exit();
    }
     
    #Periksa BKM
    /*
    $str="select notransaksi,tanggal from ".$dbname.".kebun_aktifitas where kodeorg='".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."' and jurnal=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0)
    {
        echo $_SESSION['lang']['notifbkm']." :\n";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."\n";
        }
        exit('Warning');
    }
    
   #Periksa TRAKSI
    $str="select notransaksi,tanggal from ".$dbname.".vhc_runht where kodeorg='".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        echo  $_SESSION['lang']['notiftraksi']."  :\n";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."\n";
        }
        exit('Warning');
    }
        */
}   

#PERIKSA akun transit yang belum nol=============================
$str="select sum(debet)-sum(kredit) as saldo FROM ".$dbname.".keu_jurnalsum_vw where  periode ='".$param['periode']."' 
          and kodeorg='".$_SESSION['empl']['lokasitugas']."' AND noakun like '4%'";//exit('error'.$str);

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$transit=0;
if($numrows>0){
        while($bar=$res->fetch()){
            $transit=abs($bar->saldo);
        }
}
if($transit>10 && $transit!='')#lebih dari  10 rupiah
{
    exit(" Warning:  ".$_SESSION['lang']['notifakuntransit']." :".$transit);//
}
#---------------------------------------==================================

/**************************************************************
 * [START] Cek Nilai Material VS Jurnal ***********************
 **************************************************************/
// Get Kelompok Barang yang ada Akun
$optKel = makeOption($dbname,'log_5klbarang',"kode,noakun","noakun!='' and noakun like '115%'");
$listKel = $listAkun = array();
foreach($optKel as $kode=>$akun) {
    $listKel[] =  $kode;
    $listAkun[$akun] =  $akun;
}

// Get Nilai Material, log_5saldobulanan
// $qSaldoMat = $owlPDO->query("SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
//     FROM ".$dbname.".log_5saldobulanan 
//     WHERE left(kodebarang,3) in ('".implode("','",$listKel)."') and kodegudang like '".
//         $param['kodeorg']."%' and periode like '".$param['periode']."%'
//     GROUP BY left(kodebarang,3)");
$qSaldoMat="SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang FROM ".$dbname.".log_5saldobulanan 
            WHERE left(kodebarang,3) in ('".implode("','",$listKel)."') and kodegudang like '".$param['kodeorg']."%' and periode like '".$param['periode']."%'
            GROUP BY left(kodebarang,3)";
$resSaldoMat = fetchData($qSaldoMat); //error server busy disini
$optSaldoMat = array();
foreach($resSaldoMat as $row) {
    if(!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
        $optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
    } else {
        $optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
    }
}

// Get Nilai Jurnal, keu_saldobulanan
// $qSaldoJ = $owlPDO->query("SELECT awal".$bulan." as saldoawal,noakun
//     FROM ".$dbname.".keu_saldobulanan
//     WHERE kodeorg='".$param['kodeorg']."' and periode='".$tahunbulan."'
//         and noakun in ('".implode("','",$listAkun)."')");
$qSaldoJ="SELECT awal".$bulan." as saldoawal,noakun
    FROM ".$dbname.".keu_saldobulanan
    WHERE kodeorg='".$param['kodeorg']."' and periode='".$tahunbulan."'
        and noakun in ('".implode("','",$listAkun)."')";
$resSaldoJ = fetchData($qSaldoJ);
$optSaldoJ = array();
foreach($resSaldoJ as $row) {
    $optSaldoJ[$row['noakun']] = $row['saldoawal'];
}

// Get Transaksi Jurnal
// $qTrans = $owlPDO->query("SELECT sum(debet - kredit) as saldotrans, noakun
//     FROM ".$dbname.".keu_jurnaldt_vw
//     WHERE kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'
//         and noakun in ('".implode("','",$listAkun)."')
//     GROUP BY noakun");
$qTrans="SELECT sum(debet - kredit) as saldotrans, noakun
    FROM ".$dbname.".keu_jurnaldt_vw
    WHERE kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'
        and noakun in ('".implode("','",$listAkun)."')
    GROUP BY noakun";
$resTrans = fetchData($qTrans);
foreach($resTrans as $row) {
    if(!isset($optSaldoJ[$row['noakun']])) 
        $optSaldoJ[$row['noakun']] = 0;
    $optSaldoJ[$row['noakun']] += $row['saldotrans'];
}

// Cek All Akun
$notBal = "";
foreach($listAkun as $akun) {
    if(!isset($optSaldoMat[$akun])) $optSaldoMat[$akun] = 0;
    if(!isset($optSaldoJ[$akun])) $optSaldoJ[$akun] = 0;
    
    $selisih = abs( abs($optSaldoMat[$akun]) - abs($optSaldoJ[$akun]) );
    if($selisih > 300) {
        $notBal .= $akun." = ".number_format($selisih)."\n";
    }
}

// Alert Jika ada yang belum balance
if(!empty($notBal)) {
    exit("Warning:  ".$_SESSION['lang']['notifmaterial']." \n".$notBal); //
}
/**************************************************************
 * [END] Cek Nilai Material VS Jurnal *************************
 **************************************************************/

if(substr($_SESSION['empl']['lokasitugas'],2,2)!='HO'){
    $str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
              and nojurnal like '%".$_SESSION['empl']['lokasitugas']."/KBN%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){

    }else{
        exit(" Warning : ".$_SESSION['lang']['notifprosesgaji']." ");
    }
    #---------------------------------------==================================
}
/*
// CEK SPB INPUT/POSTING diambil dari KEBUN_SLAVE_PANEN_DETAIL
// cek spb vs tiket
$spbbelumdiinput='';
$query = $owlPDO->query("SELECT a.nospb, b.tanggal
    FROM ".$dbname.".`pabrik_timbangan` a
    LEFT JOIN ".$dbname.".kebun_spbht b ON a.nospb = b.nospb
    WHERE a.`tanggal` LIKE '".$param['periode']."%' and a.`kodeorg` = '".$_SESSION['empl']['lokasitugas']."'
        AND b.`tanggal` is NULL");
$query->setFetchMode(PDO::FETCH_ASSOC);
while($rDetail=$query->fetch()){
    $spbbelumdiinput.=$rDetail['nospb'].', ';
}        
if($spbbelumdiinput!=''){
    $spbbelumdiinput=substr($spbbelumdiinput,0,-2);
    echo "WARNING: ".$_SESSION['lang']['notifspbinput']."  : ".$spbbelumdiinput;//
    exit();
}

$spbbelumdiposting='';
$query = $owlPDO->query("SELECT nospb, tanggal
    FROM ".$dbname.".`kebun_spb_vw`
    WHERE `tanggal` LIKE '".$param['periode']."%' and `blok` like '".$_SESSION['empl']['lokasitugas']."%'
        and posting = 0
        ");
$query->setFetchMode(PDO::FETCH_ASSOC);
while($rDetail=$query->fetch()){
    $spbbelumdiposting.=$rDetail['nospb'].', ';
}        
if($spbbelumdiposting!=''){
    $spbbelumdiposting=substr($spbbelumdiposting,0,-2);
    echo "WARNING: ".$_SESSION['lang']['notifspbposting']." : ".$spbbelumdiposting;//
    exit();
}
//============================================================================== END OF CEK SPB
*/
/**************************************************************
 * [START] Cek Pengakuan Penjualan ****************************
 **************************************************************/
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO'){
$listTiket="";
$qJual = $owlPDO->query("SELECT notransaksi,a.nokontrak
        FROM ".$dbname.".pabrik_timbangan a
        INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
        WHERE date(a.tanggal) between '".$currstart."' and '".$currend.
            "'and a.millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='PABRIK')");
//exit("error:".$qJual);
    $resJual = fetchData($qJual);
    if(!empty($resJual)) {
        $listTiket = '';
        foreach($resJual as $row) {
            $scek2="select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual 
                    where notransaksi='".$row['notransaksi']."'";
                    //exit("error:".$scek2);
            $qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
            $qcek2->setFetchMode(PDO::FETCH_OBJ); 
            $rcek2=owlBaris($qcek2);
            if($rcek2==0){
                $listTiket .= "- ".$row['notransaksi']."\n";    
            }
        }
        if($listTiket!=''){
            exit("Warning:  ".$_SESSION['lang']['notifpengakuanjual']." \n".$listTiket); //   
        }
        
    }
}

/**************************************************************
 * [END] Cek Pengakuan Penjualan ******************************
 **************************************************************/

// Default Segment
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

switch($proses) {
    case 'tutupBuku':
        #==================== Prep Periode ====================================
        # Prep Tahun Bulan untuk periode selanjutnya
        if($tmpPeriod[1]==12) {
            $bulanLanjut = 1;
            $tahunLanjut = $tmpPeriod[0]+1;
        } else {
            $bulanLanjut = $tmpPeriod[1]+1;
            $tahunLanjut = $tmpPeriod[0];
        }
        
        # Prep Hari untuk periode selanjutnya
        $jmlHari = cal_days_in_month(CAL_GREGORIAN,$bulanLanjut,$tahunLanjut);
        $tglAwal = $tahunLanjut.'-'.addZero($bulanLanjut,2).'-01';
        $tglAkhir = $tahunLanjut.'-'.addZero($bulanLanjut,2).'-'.addZero($jmlHari,2);
        #==================== /Prep Periode ===================================
        
        #==================== Prep Jurnal =====================================
        #=== Extract Data ====
        # Get PT
        $pt = getPT($dbname,$param['kodeorg']);
        if($pt==false) {
            $pt = getHolding($dbname,$param['kodeorg']);
        }
        
        # Tanggal dan Kode Jurnal
        $tgl = $tmpPeriod[0].$tmpPeriod[1].
            cal_days_in_month(CAL_GREGORIAN,$tmpPeriod[1],$tmpPeriod[0]);
        $kodejurnal = 'CLSM';
        
        
        #==================== Journal Counter ==================
        $nojurnal = $tgl."/".$param['kodeorg'].
            "/".$kodejurnal."/999";
        #==================== Journal Counter ==================
        
        # Cek apakah tahun sudah ditutup
        $qCek = selectQuery($dbname,'keu_jurnalht','*',
            "nojurnal='".$nojurnal."'");
//        echo "error:".$qCek;
//        exit;
        $resCek = fetchData($qCek);
        if(!empty($resCek)) {
            $sPeriode=$owlPDO->query("select periode from ".$dbname.".setup_periodeakuntansi 
                       where kodeorg='".$_SESSION['empl']['lokasitugas']."' order by periode desc limit 1");
            $sPeriode->setFetchMode(PDO::FETCH_ASSOC);
            $rPeriode=$sPeriode->fetch();
            if($rPeriode['periode']==$param['periode']){
                $sDel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
                try{
                    $owlPDO->exec($sDel); 
                }catch(PDOException $e){
                    echo "DB Error : " . $e->getMessage();
                    die();
                }
            }else{
                echo ' warning : '.$_SESSION['lang']['notifperiode'];//
                exit();    
            }
        }
        
        $query =  "select count(*) as x from ".$dbname.".keu_jurnaldt_vw where 
                   tanggal between '".$currstart."' and '".$currend."' and kodeorg='".$param['kodeorg']."'";
//         exit("error: ".$query);
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($res);
        if($numrows==0) {
            echo 'Warning : '.$_SESSION['lang']['datanotfound'];//
            exit;
        }
        
        # Get Sum dari Jurnal
        $query = selectQuery($dbname,'keu_jurnaldt_vw','kodeorg as kodeorg,sum(jumlah) as jumlah',
            "kodeorg='".$param['kodeorg']."' and tanggal between '".$currstart."' and '".$currend."'
             and noakun>='".$akunRAT."'").
            "group by kodeorg";
        $data = fetchData($query);

        
        # Get Akun
        #+++++++++++++++++++++++++
        //tambahan ginting
        $noakun=$akunCLM;//akun laba tahun berjalan
        #++++++++++++++++++++++++++
        if($data[0]['jumlah']>0) {
            # Rugi
            $debetH=$data[0]['jumlah'];
            $kreditH=0;
        } else {
            # Laba
            $debetH=0;
            $kreditH=$data[0]['jumlah'];            
        }
        
        # Prep Header
        $dataRes['header'] = array(
            'nojurnal'=>$nojurnal,
            'kodejurnal'=>$kodejurnal,
            'tanggal'=>$tgl,
            'tanggalentry'=>date('Ymd'),
            'posting'=>'0',
            'totaldebet'=>$debetH,
            'totalkredit'=>$kreditH,
            'amountkoreksi'=>'0',
            'noreferensi'=>'TUTUP/'.$param['kodeorg'].'/'.$tahunbulan,
            'autojurnal'=>'1',
            'matauang'=>'IDR',
            'kurs'=>'1',
            'revisi'=>'0'
        );
        
        # Data Detail
        $noUrut = 1;
        
        # Debet
        $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$tgl,
            'nourut'=>$noUrut,
            'noakun'=>$noakun,
            'keterangan'=>'Tutup Bulan '.$tahunbulan.' Unit '.$param['kodeorg'],
            'jumlah'=>$data[0]['jumlah'],
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$param['kodeorg'],
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>'',
            'nik'=>'',
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>'',
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>'',
            'revisi'=>'0',
            'kodesegment'=>$defSegment
        );
        $noUrut++;

       #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
        $headErr = '';
        $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
        try{$owlPDO->exec($insHead); }
        catch (PDOException $e) {
            $headErr .= "Insert Header Error : " . $e->getMessage() . "\n"; 
        }
        
        if($headErr=='') {
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
            $detailErr = '';
            foreach($dataRes['detail'] as $row) {
                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                try{$owlPDO->exec($insDet); }
                catch (PDOException $e) {
                    $detailErr .= "Insert Detail Error : " . $e->getMessage() . "\n".$insDet; 
                    break;
                }     
            }
            
            if($detailErr=='') {
                
                /**
                 * Cek Nilai Akumulasi Penyusutan (Dari Daftar Asset) dengan Nilai Akumulasi Penyusutan pada Saldo Bulanan
                 */
                // Ambil Nilai Akumulasi Penyusutan
                if($_SESSION['language']=='EN'){
                    $zz="b.namatipe1 as namatipe";
                } else {
                    $zz="b.namatipe";
                }
                
                $rinci = array();//indra
                $str=$owlPDO->query("select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,".$zz." 
                      from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
                      on a.tipeasset=b.kodetipe    
                      where a.kodeorg='".$_SESSION['empl']['lokasitugas']."' 
                      and status=1  and a.awalpenyusutan <= '".$param['periode']."' and persendecline=0");
                
                $arrAsset = array();
                $str->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$str->fetch()){
                    $x=mktime(0,0,0,  intval(substr($bar->awalpenyusutan,5,2)+($bar->jlhblnpenyusutan)),15,substr($bar->awalpenyusutan,0,4));
                    $maxperiod=date('Y-m',$x);
                    if($param['periode']<$maxperiod) {
                       if(!isset($arrAsset[$bar->tipeasset]['nilai'])) $arrAsset[$bar->tipeasset]['nilai']=0;
                       $arrAsset[$bar->tipeasset]['nilai']+=$bar->bulanan;
                    }
                    
                    $arrAsset[$bar->tipeasset]['nama']=$bar->namatipe;
                    $arrAsset[$bar->tipeasset]['kode']='DEP'.substr($bar->tipeasset,0,2);
                }
                
                //Ambil double declining
                $str=$owlPDO->query("select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,a.persendecline,a.hargaperolehan,".$zz." 
                     from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
                     on a.tipeasset=b.kodetipe    
                     where a.kodeorg='".$_SESSION['empl']['lokasitugas']."' 
                     and status=1 and a.awalpenyusutan <= '".$param['periode']."' and a.persendecline>'0'");
                $str->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$str->fetch()){
                    $thnawal=substr($bar->awalpenyusutan,0,4);
                    $blnawal=substr($bar->awalpenyusutan,5,2);
                    $total=($thnawal*12)+$blnawal;
                
                    $thnNow=substr($param['periode'],0,4);
                    $blnNow=substr($param['periode'],5,2);
                    
                    $totalBulanAwal = 12-$blnawal+1;
                    $totalTahun = $thnNow-$thnawal-1;
                    
                    $totalNow=($thnNow*12)+$blnNow+1;
                    $selisih=$totalNow-$total;
                    $out=0;
                    $akumNow = $sekarang = 0;
                    
                    // Depresiasi s/d akhir tahun
                    $before = $sekarang = $bar->hargaperolehan;
                    if($totalTahun>-1) {
                        $akumNow += $totalBulanAwal/12 * $bar->persendecline/100 * $sekarang;
                    }
                    $sekarang -= $akumNow;
                    
                    // Depresiasi per Tahun
                    if($totalTahun>0) {
                        for($i=0;$i<$totalTahun;$i++) {
                            $before = $sekarang;
                            $akumNow += $sekarang*$bar->persendecline/100;
                            $sekarang -= $sekarang*$bar->persendecline/100;
                        }
                    }
                    
                    // Depresiasi per Bulan
                    $out = $sekarang*($bar->persendecline/100)/12;
                    if($bar->jlhblnpenyusutan<$selisih) {
                        if($totalTahun>-1) {
                            $out = $sekarang - ($bulanNow*$sekarang);
                        } else {
                            $out = $sekarang - (($bulanNow-$bulanawal+1)*$sekarang);
                        }
                    }
                    
                    if(isset($arrAsset[$bar->tipeasset]['nilai'])) {
                        $arrAsset[$bar->tipeasset]['nilai']+=$out;
                    } else {
                        $arrAsset[$bar->tipeasset]['nilai']=$out;
                    }
                    $arrAsset[$bar->tipeasset]['nama']=$bar->namatipe;
                    $arrAsset[$bar->tipeasset]['kode']='DEP'.substr($bar->tipeasset,0,2);
                }
                
                $poolAsset = array();
                foreach($arrAsset as $row) {
                    $poolAsset[$row['kode']] = $row['nilai'];
                }
                
                // Get List Akun dari Parameter Jurnal = 'DEP'
                $optDep = makeOption($dbname,'keu_5parameterjurnal',"jurnalid,noakunkredit",
                                      "kodeaplikasi='DEP'");
                
                // Get Jurnal
                foreach($poolAsset as $kode=>$nilai) {
                    // No Jurnal
                    $konter ='001';
                    $tanggal=$param['periode']."-28";
                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",$tanggal)."/".substr($param['kodeorg'],0,4)."/".$kode."/".$konter;
                    
                    $qJurnal = selectQuery($dbname,'keu_jurnaldt',"jumlah",
                                           "nojurnal='".$nojurnal."' and noakun='".$optDep[$kode]."'");
                    $resJurnal = fetchData($qJurnal);
                    
                    if(empty($resJurnal)) {
                        exit("Warning: ".$kode." : ".$_SESSION['lang']['notifdepresiasi']);//
                    } else {
                        if($resJurnal[0]['jumlah']+round($nilai,2)>0.01) {
                            exit("Warning: ".$kode." : ".$_SESSION['lang']['notifdepresiasi']);
                        }
                    }
                }
				
				#==================== /Prep Jurnal ============================================
                createJurnalPRSDN($param['periode'],$param['kodeorg']);
                #========================== Proses Insert dan Update ==========================
				
                #==================== /Prep Jurnal ====================================
                createSaldoAwal($param['periode'],$tahunLanjut.'-'.addZero($bulanLanjut,2),$param['kodeorg']);
                #========================== Proses Insert dan Update ==========================
                
                # Header and Detail inserted
                # Update Status Tutup Buku
                $queryUpd = updateQuery($dbname,'setup_periodeakuntansi',array('tutupbuku'=>1),
                    "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
                try{$owlPDO->exec($queryUpd); }catch (PDOException $e) {print " Gagal  Update!: " . $e->getMessage() . "\n"; die(); }
                    # Insert periode baru
                    $dataIns = array(
                        'kodeorg'=>$param['kodeorg'],
                        'periode'=>$tahunLanjut.'-'.addZero($bulanLanjut,2),
                        'tanggalmulai'=>$tglAwal,
                        'tanggalsampai'=>$tglAkhir,
                        'tutupbuku'=>0
                    );
                    $queryIns = insertQuery($dbname,'setup_periodeakuntansi',$dataIns);
                    echo '1';
                    $test=false;
                    try{$test=$owlPDO->exec($queryIns); }
                    catch (PDOException $e) {
                        print " Gagal  Update!: " . $e->getMessage() . "\n"; 
                         $queryRB = updateQuery($dbname,'setup_periodeakuntansi',array('tutupbuku'=>0),
                            "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
                         try{$owlPDO->exec($queryRB); }
                         catch (PDOException $e) {
                             print " Error Rollback Update  !: " . $e->getMessage() . "\n"; 
                             die();                            
                         }                   
                    }
                    if($test){#berhasil
                            //update history tutup buku
                            $str="delete from ".$dbname.".keu_setup_watu_tutup where periode='".$param['periode']."' and kodeorg='".$param['kodeorg']."'";
                            $owlPDO->exec($str);
                            $str="insert into ".$dbname.".keu_setup_watu_tutup(kodeorg,periode,username) values(
                                  '".$param['kodeorg']."','".$param['periode']."','".$_SESSION['standard']['username']."')";
                            $owlPDO->exec($str);                             
                        }                    
            } else {
                echo $detailErr;
                # Rollback, Delete Header
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                try{$owlPDO->exec($RBDet); }catch (PDOException $e) {print " Rollback Delete Header Error  !: " . $e->getMessage() . "\n"; die(); }                
            }
        } else {
            echo $headErr;
            exit;
        }

        
        break;
    default:
}

function createSaldoAwal($dariperiode,$keperiode,$kodeorg)
{
    global $owlPDO;
    global $dbname;
    global $akunRAT;
    global $akunCLM;
    global $akunCLY;
    $sawal=array();
    $mtdebet=array();
    $mtkredit=array();
    $salak=array();
    #ambil saldoawal bulan berjalan
    $str="select awal".substr($dariperiode,5,2).",noakun from ".$dbname.".keu_saldobulanan
          where periode='".str_replace("-", "", $dariperiode)."' and kodeorg='".$kodeorg."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_NUM);
    while($bar=$res->fetch()){
        $sawal[$bar[1]]=$bar[0];
        $mtdebet[$bar[1]]=0;
        $mtkredit[$bar[1]]=0;
        $salak[$bar[1]]=$bar[0];
    }
    #ambil transaksi transaksi bln berjalan
    $str="select debet,kredit,noakun from ".$dbname.".keu_jurnalsum_vw 
          where periode='".$dariperiode."' and kodeorg='".$kodeorg."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        setIt($sawal[$bar->noakun],0);
        $mtdebet[$bar->noakun]=$bar->debet;
        $mtkredit[$bar->noakun]=$bar->kredit;
        $salak[$bar->noakun]=$mtdebet[$bar->noakun]+$sawal[$bar->noakun]-$mtkredit[$bar->noakun];
    }
    #ambil semu nomor akun
    $str="select noakun from ".$dbname.".keu_5akun where length(noakun)=7";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $temp='';
    while($bar=$res->fetch()){
        #create string update current
       
        if($sawal[$bar->noakun]!=''){
         #jika sudah ada di database maka update
            if($mtdebet[$bar->noakun]=='')
                $mtdebet[$bar->noakun]=0;
           if($mtkredit[$bar->noakun]=='')
                $mtkredit[$bar->noakun]=0;
           
           $temp="update ".$dbname.".keu_saldobulanan 
                set debet".substr($dariperiode,5,2)."=".$mtdebet[$bar->noakun].",
                kredit".substr($dariperiode,5,2)."=".$mtkredit[$bar->noakun]."
                where periode='".str_replace("-", "", $dariperiode)."'
                and kodeorg='".$kodeorg."' and noakun='".$bar->noakun."';";
          try{$owlPDO->exec($temp); }catch (PDOException $e) {print " Error update mutasi bulanan!: " . $e->getMessage() . "\n"; die(); }   
        }
        else
        {
           #jika belum ada maka insert
         if(isset($sawal[$bar->noakun]) and ($sawal[$bar->noakun]!='' or $mtdebet[$bar->noakun]!='' or  $mtkredit[$bar->noakun]!='')){
            if($mtdebet[$bar->noakun]=='')
                $mtdebet[$bar->noakun]=0;
           if($mtkredit[$bar->noakun]=='')
                $mtkredit[$bar->noakun]=0;
           $temp="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                  awal".substr($dariperiode,5,2).",debet".substr($dariperiode,5,2).",
                  kredit".substr($dariperiode,5,2).")values('". 
                   $kodeorg."','".str_replace("-", "", $dariperiode)."','".$bar->noakun."',0,".
                   $mtdebet[$bar->noakun].",".$mtkredit[$bar->noakun].");";
           try{$owlPDO->exec($temp); }catch (PDOException $e) {print " Error insert mutasi bulanan!: " . $e->getMessage() . "\n"; die(); }  
         }
        }   
    } 
    #delete saldo awal bulan selanjutnya;
    $str="delete from ".$dbname.".keu_saldobulanan where periode='".str_replace("-", "", $keperiode)."'
          and kodeorg='".$kodeorg."';";
	try{
		$owlPDO->exec($str); 
		
		$saldoditahan=0;
        foreach($salak as $key=>$val){
            if($salak[$key]!=''){
              
                $temp="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                      awal".substr($keperiode,5,2).")values('". 
                       $kodeorg."','".str_replace("-", "", $keperiode)."','".$key."',".$salak[$key].")";
               if(substr($keperiode,5,2)!='01')#jika bukan awal tahun
               {      
                   try{$owlPDO->exec($temp); }catch (PDOException $e) {print " Error insert saldo awal!: " . $e->getMessage() . "\n".$temp; die(); }   
               }
               else #jika bulan 12
               {                     
                   if($key<$akunRAT){#jika awal tahun maka hanya akan membawa aktiva saja ke bulan selanjutnya
                #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++                       
                   #deteksi jika saldo ditahan
                   #sudah mengakomodasi tutup akhir tahun    
                    if($key==$akunCLY)
                        $saldoditahan+=$salak[$key];
                    else{                    
                            if($key==$akunCLM){
                                $saldoditahan+=$salak[$key];#tampung laba tahun berjalan ke laba ditahan
                                $salak[$key]=0;
                            }
                            $temp1="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                                  awal".substr($keperiode,5,2).")values('". 
                                   $kodeorg."','".str_replace("-", "", $keperiode)."','".$key."',".$salak[$key].")";

                       #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++                       
                           try{$owlPDO->exec($temp1); }catch (PDOException $e) {print " Error insert saldo awal!: " . $e->getMessage() . "\n"; die(); } 
                    }                   
                  }
               }
            }   
        }
      //masukkan saldo laba ditahan
     if(substr($keperiode,5,2)=='01'){//hanya pada bulan 12                           
        $temp2="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
          awal".substr($keperiode,5,2).")values
           ('".$kodeorg."','".str_replace("-", "", $keperiode)."','".$akunCLY."',".$saldoditahan.")";
       try{$owlPDO->exec($temp2); }catch (PDOException $e) {print " Error insert laba ditahan pada saldo awal!: " . $e->getMessage() . "\n"; die(); }
     }
	}catch (PDOException $e){
		print " Error : " . $e->getMessage() . "\n"; die(); 
	}   
} 

function createJurnalPRSDN($periode,$kodeorg){
	global $owlPDO;
    global $dbname;
    global $tahunbulan;
    global $tmpPeriod;
	
	# Tanggal dan Kode Jurnal
	$tgl = $tmpPeriod[0].$tmpPeriod[1].cal_days_in_month(CAL_GREGORIAN,$tmpPeriod[1],$tmpPeriod[0]);
	$kodejurnalprsdn = 'PRSDN';
	
	#============================= Journal Counter ===========================
	$nojurnalprsdn = $tgl."/".$kodeorg."/".$kodejurnalprsdn."/999";
	#============================= Journal Counter ===========================
	
	$str=$owlPDO->query("select nojurnal from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnalprsdn."'");
	$str->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($str);
	
	if($numrows > 0){
		$sDel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnalprsdn."'";
		try{
			$owlPDO->exec($sDel); 
		}catch(PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
	}
	
	//Get noakun Piutang HO
    $optHo=makeOption($dbname,'organisasi','induk,kodeorganisasi',"induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='HOLDING'");
	$strprsdn=$owlPDO->query("select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".$optHo[$_SESSION['org']['kodeorganisasi']]."' and jenis='intra'");
	$strprsdn->setFetchMode(PDO::FETCH_OBJ);
	$barprsdn=$strprsdn->fetch();
	$noAkunprsdn = $barprsdn->akunpiutang;

    if ($barprsdn->akunpiutang=='') {
        exit("Warning : Account intraco or interco not available for ".$optHo[$_SESSION['org']['kodeorganisasi']].". Please setting on menu Finance > setup > COA for Intra/Interco.");
    } 
	
	$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
	
	$debetH=$kreditH=0;
	$noUrut = 1;
	
	$strprsdn="SELECT sum(jumlah) as jumlah, noakun, keterangan from ".$dbname.".keu_jurnaldt 
               where kodeorg='".$kodeorg."' and tanggal like '".$periode."%' and left(noakun,1) in ('6','7')  
               and nojurnal not like '%PRSDN%' group by noakun";
	$resprsdn = fetchdata($strprsdn);
	$arrTempprsdn = array();
	foreach($resprsdn as $row){
        if(substr($row['noakun'],0,1)=='7'){
            // if(substr($row['noakun'],0,5)=='71502'){
            //     continue;#jika noakun penyusutan asset abaikan
            // }
            $dtakun='7199999';
            $lstAkun['noakun'][$dtakun]=$dtakun;
            $dtRup[$dtakun]+=$row['jumlah'];
            $ketData[$dtakun]="Alokasi Biaya Umum Pabrik ke persediaan";
        }
        if(substr($row['noakun'],0,1)=='6'){
            $dtakun='6999999';
            $lstAkun['noakun'][$dtakun]=$dtakun;
            $dtRup[$dtakun]+=$row['jumlah'];
            $ketData[$dtakun]="Alokasi Biaya Langsung Pabrik ke persediaan";
        }
    }
    // $strprsdn="SELECT sum(jumlah) as jumlah, noakun, keterangan from ".$dbname.".keu_jurnaldt 
    //            where kodeorg='".$kodeorg."' and tanggal like '".$periode."%' and noakun='8220405'
    //            and nojurnal not like '%PRSDN%' group by noakun";
    // $resprsdn = fetchdata($strprsdn);
    // foreach($resprsdn as $row){
    //         $dtakun='8220499';
    //         $lstAkun['noakun'][$dtakun]=$dtakun;
    //         $dtRup[$dtakun]+=$row['jumlah'];
    //         $ketData[$dtakun]="Alokasi Biaya Beban Pajak 22 Pabrik ke persediaan";
    // }//tidak termasuk biaya yang di bebankan di dalam hpp tapi jadi pengurang pada laba rugi
             

        foreach($lstAkun['noakun'] as $row=>$noakunIsi){
		# Data Detail
		# Kredit
		$dataRes['detail'][] = array(
			'nojurnal'=>$nojurnalprsdn,
			'tanggal'=>$tgl,
			'nourut'=>$noUrut,
			'noakun'=>$noakunIsi,
			'keterangan'=>''.$ketData[$noakunIsi].' '.$tahunbulan.' Unit '.$kodeorg,
			'jumlah'=>(-($dtRup[$noakunIsi])),
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$kodeorg,
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi'=>'',
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>'',
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment'=>$defSegment
		);
		
		$arrTempprsdn[$noakunIsi] = $dtRup[$noakunIsi];
		$debetH = $debetH + $dtRup[$noakunIsi];
		$noUrut++;
	}
	// echo"<pre>".print_r($dataRes['detail'])."</pre>";
 //    exit('warning');
	# Data Detail
	# Debit
	$dataRes['detail'][] = array(
		'nojurnal'=>$nojurnalprsdn,
		'tanggal'=>$tgl,
		'nourut'=>$noUrut,
		'noakun'=>$noAkunprsdn,
		'keterangan'=>'Biaya Total Pabrik ke Persediaan Produk '.$tahunbulan.' Unit '.$kodeorg,
		'jumlah'=>$debetH,
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$kodeorg,
		'kodekegiatan'=>'',
		'kodeasset'=>'',
		'kodebarang'=>'',
		'nik'=>'',
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi'=>'',
		'noaruskas'=>'',
		'kodevhc'=>'',
		'nodok'=>'',
		'kodeblok'=>'',
		'revisi'=>'0',
		'kodesegment'=>$defSegment
	);

	# Prep Header
	$dataRes['header'] = array(
		'nojurnal'=>$nojurnalprsdn,
		'kodejurnal'=>$kodejurnalprsdn,
		'tanggal'=>$tgl,
		'tanggalentry'=>date('Ymd'),
		'posting'=>'0',
		'totaldebet'=>$debetH,
		'totalkredit'=>(-($debetH)),
		'amountkoreksi'=>'0',
		'noreferensi'=>'',
		'autojurnal'=>'1',
		'matauang'=>'IDR',
		'kurs'=>'1',
		'revisi'=>'0'
	);

	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
	$headErr = '';
	$insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
	try{$owlPDO->exec($insHead); }
	catch (PDOException $e) {
		$headErr .= "Insert Header Error : " . $e->getMessage() . "\n"; 
	}
	
	if($headErr=='') {
		#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
		$detailErr = '';
		foreach($dataRes['detail'] as $row) {
			$insDet = insertQuery($dbname,'keu_jurnaldt',$row);
			try{
                $owlPDO->exec($insDet); 
            }
			catch(PDOException $e){
				$detailErr .= "Insert Detail Error : " . $e->getMessage() . "\n".$insDet; 
			}
		}
        if($detailErr!=''){
            #rollback
            $sDel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnalprsdn."'";
            try{
                $owlPDO->exec($sDel); 
            }catch(PDOException $e){
                echo "DB Error : " . $e->getMessage();
                die();
            }
            exit('warning : Detail Error '.$detailErr);    
        }

	}else{
        exit('warning : Header Error '.$headErr);
    }
}

?>