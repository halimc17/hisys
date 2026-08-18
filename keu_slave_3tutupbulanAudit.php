<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_POST;
$tmpPeriod = explode('-',$param['periode']);
$tahunbulan = implode("",$tmpPeriod);
$proses = $_GET['proses'];
$change = checkPostGet('change', '');

$dataorg = array();
$dtstr="select * from ".$dbname.".organisasi where  kodeorganisasi = '".$param['kodeorg']."'";
$str=$owlPDO->query($dtstr);
$str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
  $dataorg[$bar->kodeorganisasi] = $bar;
 }

if($change=='changeperiode'){
    $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $str = "select * FROM ".$dbname.".setup_periodeakuntansi where kodeorg='".$param['kodeorg']."' and tutupbuku=0 order by periode asc
    LIMIT 1 ";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while ($bar = $res->fetch()) {
        $optPeriode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
    }
    echo $optPeriode;
}else{
 
//ambil akun laba tahun berjalan;
$stl="select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLM'";
$akunCLM='';
$res=$owlPDO->query($stl) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bal=$res->fetch()){
    $akunCLM=$bal->noakundebet;
}
//ambil akun laba ditahan
$stl="select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLY'";
$akunCLY='';
$res=$owlPDO->query($stl) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bal=$res->fetch()){
    $akunCLY=$bal->noakundebet;
}

//ambil batas bawah akun laba/rugi
$stl="select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='RAT'";
$akunRAT='';
$res=$owlPDO->query($stl) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bal=$res->fetch()){
    $akunRAT=$bal->noakundebet;
}

if($akunCLM=='' or $akunCLY=='' or $akunRAT=='')
{
    exit(' Error: data akun laba tahunan, akun laba ditahan dan batas akun laba/rugi belum terdaftar pada parameter jurnal');
}

#periksa apakah sudah diposting semua transaksi kas dan bappp
$str="select tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$param['periode']."' and kodeorg='".$param['kodeorg']."'";
$currstart='';
$currend='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $currstart=$bar->tanggalmulai;
    $currend=$bar->tanggalsampai;
}
    
if($currstart=='' or $currend=='')
{
    exit('Error: Accounting period is not normal on '.$param['kodeorg']);
}
else
{
    #periksa kas
    $str="select notransaksi,tanggal,jumlah from ".$dbname.".keu_kasbankht where kodeorg='".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        echo "There are Cash/Bank transaction that has not been posted:\n";
        $no=0;
        while($bar=$res->fetch())
        {
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlah,0)."\n"; 
        }
        exit('Error');
    }

    #periksa bapp
    $str="select notransaksi,tanggal,jumlahrealisasi from ".$dbname.".log_baspk where kodeblok like '".$param['kodeorg']."%'
          and tanggal between '".$currstart."' and '".$currend."' and statusjurnal=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        echo "There are Contract Realization transaction that has not been posted:\n";
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlahrealisasi,0)."\n"; 
        }
        exit('Error');
    }

    #periksa jurnal tidak balance
    $str="select nojurnal,tanggal,debet,kredit from ".$dbname.".keu_jurnal_tidak_balance_vw where kodeorg = '".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."'
          and nojurnal not like '%/CLSM/%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        echo "There is still yet balanced Journal:\n";
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". No ".$bar->nojurnal.":".tanggalnormal($bar->tanggal)."->(D)Rp. ".number_format($bar->debet,0).":(K)Rp. ".number_format($bar->kredit,0)."\n"; 
        }
        exit('Error');
    }

    #periksa gudang
    $str="select notransaksi,tanggal, kodegudang from ".$dbname.".log_transaksiht where post=0 and kodegudang like '".$param['kodeorg']."%'
            and tanggal between '".$currstart."' and '".$currend."'";
    $stm='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0)
    {
        while($bar=$res->fetch()){
             $stm.="Gudang:".$bar->kodegudang."->No.>".$bat->notransaksi."->".$bar->tanggal."<br>";
        }
       echo "Error: Warehouse transaction that has not been posted\r<br>".$stm;  
    }
}

// Default Segment
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

switch($proses) {
    case 'tutupBuku':
        #=====insert data mulai=====#
        $sCek="select * from ".$dbname.".keu_saldobulanan_ori 
               where left(periode,4)='".$tmpPeriod[0]."' and kodeorg='".$param['kodeorg']."'";
        $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
        $qCek->setFetchMode(PDO::FETCH_OBJ);
        $rCek=owlBaris($res);
        if($rCek==0){
            $sInser="insert into ".$dbname.".keu_saldobulanan_ori select * from ".$dbname.".keu_saldobulanan where
                     left(periode,4)='".$tmpPeriod[0]."' and kodeorg='".$param['kodeorg']."' ";
            try{$owlPDO->exec($sInser); }catch (PDOException $e) {print "Error  !: ".$sInser."___". $e->getMessage() . "\n"; die(); }
        }
        #=====insert data end=====#
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
        $nojurnal = $tgl."/".$param['kodeorg']."/".$kodejurnal."/999";
        #==================== Journal Counter ==================
        
        # Cek apakah tahun sudah ditutup
//        $qCek = selectQuery($dbname,'keu_jurnalht','*',
//            "nojurnal='".$nojurnal."'");
//        $resCek = fetchData($qCek);
//        if(!empty($resCek)) {
//            echo 'Warning : Unit ini sudah melakukan tutup bulan.';
//            exit;
//        }
        #delete clm yang sudah terbentuk
        $str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal ."'";
        $owlPDO->exec($str);
        
        $query = "select count(*) as x from ".$dbname.".keu_jurnaldt_vw where 
                   tanggal between '".$currstart."' and '".$currend."' and kodeorg='".$param['kodeorg']."'";
//         exit("error: ".$query);
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($res);
        if($numrows==0) {
            echo 'Warning : No data found for this unit';
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
            $headErr .= "Insert Header Error : " . $e->getMessage() . "\n".$insHead; 
        }
        
        if($headErr=='') {
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
            $detailErr = '';
            foreach($dataRes['detail'] as $row) {
                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                try{$owlPDO->exec($insDet); }
                catch (PDOException $e) {
                    $detailErr .= "Insert Detail Error : " . $e->getMessage() . "\n"; 
                    break;
                }    
            }
            
            if($detailErr=='') {
                    #==================== /Prep Jurnal ====================================
                    createSaldoAwal($param['periode'],$tahunLanjut.'-'.addZero($bulanLanjut,2),$param['kodeorg']);
                    #========================== Proses Insert dan Update ==========================
                  #delete semua periodeakuntansi untuk tahun depan
                   $str="delete from ".$dbname.".setup_periodeakuntansi where kodeorg='".$param['kodeorg']."' 
                             and periode like '".$tahunLanjut ."%'";
                   $owlPDO->exec($str);
                # Header and Detail inserted
                # Update Status Tutup Buku
                $queryUpd = updateQuery($dbname,'setup_periodeakuntansi',array('tutupbuku'=>1),
                    "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
                try{$owlPDO->exec($queryUpd); }
                catch (PDOException $e) {
                    echo "Error Update :  " . $e->getMessage() . "\n";
                    exit();
                }
                    # Insert periode baru
                    $dataIns = array(
                        'kodeorg'=>$param['kodeorg'],
                        'periode'=>$tahunLanjut.'-'.addZero($bulanLanjut,2),
                        'tanggalmulai'=>$tglAwal,
                        'tanggalsampai'=>$tglAkhir,
                        'tutupbuku'=>0
                    );
                    $test=false;
                    $queryIns = insertQuery($dbname,'setup_periodeakuntansi',$dataIns);
                    $test=$owlPDO->exec($queryIns);
                    echo '1';
                    if(!$test) {
                        # Rollback
                        echo 'Error Insert periode akuntansi';
                        $queryRB = updateQuery($dbname,'setup_periodeakuntansi',array('tutupbuku'=>0),
                            "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
                        try{$owlPDO->exec($queryRB); }
                        catch (PDOException $e) {
                            echo "Error Rollback Update : " . $e->getMessage() . "\n";
                            exit();
                        }                        
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
                try{$owlPDO->exec($RBDet); }
                catch (PDOException $e) {
                    echo "Rollback Delete Header Error : " . $e->getMessage() . "\n";
                    exit();
                }                 
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
    global $conn;
    global $dbname;
    global $akunRAT;
    global $akunCLM;
    global $akunCLY;
    $sawal=Array();
    $mtdebet=Array();
    $mtkredit=Array();
    $salak=Array();
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
        $mtdebet[$bar->noakun]=$bar->debet;
        $mtkredit[$bar->noakun]=$bar->kredit;
        $salak[$bar->noakun]=$mtdebet[$bar->noakun]+$sawal[$bar->noakun]-$mtkredit[$bar->noakun];
    }
    #ambil semu nomor akun
    $str="select noakun from ".$dbname.".keu_5akun where length(noakun)=7";
    $temp='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        #create string update current
       
        if($sawal[$bar->noakun]!='')
        {  
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
           try{$owlPDO->exec($temp); }catch (PDOException $e) {print " Error update mutasi bulanan  !: " . $e->getMessage() . "\n"; die(); }   
        }
        else
        {
           #jika belum ada maka insert
         if($sawal[$bar->noakun]!='' or $mtdebet[$bar->noakun]!='' or  $mtkredit[$bar->noakun]!=''){
            if($mtdebet[$bar->noakun]=='')
                $mtdebet[$bar->noakun]=0;
           if($mtkredit[$bar->noakun]=='')
                $mtkredit[$bar->noakun]=0;
           $temp="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                  awal".substr($dariperiode,5,2).",debet".substr($dariperiode,5,2).",
                  kredit".substr($dariperiode,5,2).")values('". 
                   $kodeorg."','".str_replace("-", "", $dariperiode)."','".$bar->noakun."',0,".
                   $mtdebet[$bar->noakun].",".$mtkredit[$bar->noakun].");";
            try{$owlPDO->exec($temp); }catch (PDOException $e) {print " Error update mutasi bulanan  !: " . $e->getMessage() . "\n"; die(); }  
         }
        }   
    } 
    #delete saldo awal bulan selanjutnya;
    $str="delete from ".$dbname.".keu_saldobulanan where periode='".str_replace("-", "", $keperiode)."'
          and kodeorg='".$kodeorg."';";
    $test=false;
    $test=$owlPDO->exec($str);
    if($test);
    {
        $saldoditahan=0;
        foreach($salak as $key=>$val){
            if($salak[$key]!=''){
              
                $temp="insert into  ".$dbname.".keu_saldobulanan (kodeorg,periode,noakun,
                      awal".substr($keperiode,5,2).")values('". 
                       $kodeorg."','".str_replace("-", "", $keperiode)."','".$key."',".$salak[$key].")";
               if(substr($keperiode,5,2)!='01')#jika bukan awal tahun
               {      
                   try{$owlPDO->exec($temp); }catch (PDOException $e) {print " Error insert saldo awal  !: " . $e->getMessage() . "\n"; die(); }  
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
                        try{$owlPDO->exec($temp1); }catch (PDOException $e) {print " Error insert saldo awal  !: " . $e->getMessage() . "\n"; die(); } 
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
        try{$owlPDO->exec($temp2); }catch (PDOException $e) {print " Error insert laba ditahan pada saldo awal   !: " . $e->getMessage() . "\n"; die(); }  
     }
    }   
} 
}  
?>