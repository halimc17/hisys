<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
include_once('lib/zJournal.php');
    

$param = $_POST;
$tmpPeriod = explode('-',$param['periode']);
$tahunbulan = implode("",$tmpPeriod);
$tahun = $tmpPeriod[0];
$bulan = $tmpPeriod[1];
$proses = $_GET['proses'];

//cek apakah ada data

//ambil akun laba tahun berjalan;
$stl="select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLM'";
$akunCLM='';
$res=$owlPDO->query($stl) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bal=$res->fetch())
{
    $akunCLM=$bal->noakundebet;
}
//ambil akun laba ditahan
$stl="select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='CLY'";
$akunCLY='';
$res=$owlPDO->query($stl) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bal=$res->fetch())
{
    $akunCLY=$bal->noakundebet;
}
//ambil batas bawah akun laba/rugi
$stl="select noakundebet from ".$dbname.".keu_5parameterjurnal where jurnalid='RAT'";
$akunRAT='';
$res=$owlPDO->query($stl) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bal=$res->fetch())
{
    $akunRAT=$bal->noakundebet;
}
if($akunCLM=='' or $akunCLY=='' or $akunRAT=='')
{
    if($_SESSION['language']=='EN'){
        exit(' Error: Annual income account data, account  retained earnings and account limits profits / losses not yet listed on the parameters of the journal');
    }else{
       exit(' Error: data akun laba tahunan, akun laba ditahan dan batas akun laba/rugi belum terdaftar pada parameter jurnal');
    }
}

#periksa apakah sudah diposting semua transaksi kas dan bappp
$str="select tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      periode='".$param['periode']."' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
//echo $str."____";
$currstart='';
$currend='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $currstart=$bar->tanggalmulai;
    $currend=$bar->tanggalsampai;
}
    
if($currstart=='' or $currend=='')
{
    exit('Error: Accounting period is not normal to '.$_SESSION['empl']['lokasitugas']);
}
else{   
    
    #periksa kas
    $str="select notransaksi,tanggal,jumlah from ".$dbname.".keu_kasbankht where kodeorg='".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0)
    {
        echo " There are Cash/Bank transaction that has not been posted:\n";
        $no=0;
        while($bar=$res->fetch())
        {
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlah,0)."\n"; 
        }
        exit('Error');
    }

    #periksa bapp
    $str="select notransaksi,tanggal,jumlahrealisasi from ".$dbname.".log_baspk where kodeblok like '".$_SESSION['empl']['lokasitugas']."%'
          and tanggal between '".$currstart."' and '".$currend."' and statusjurnal=0";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0)
    {
        echo "There are Contract Realization transaction that has not been posted:\n";
        $no=0;
        while($bar=$res->fetch())
        {
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlahrealisasi,0)."\n"; 
        }
        exit('Error');
    }
    #periksa jurnal tidak balance
    $str="select nojurnal,tanggal,debet,kredit from ".$dbname.".keu_jurnal_tidak_balance_vw where kodeorg = '".$_SESSION['empl']['lokasitugas']."'
          and tanggal between '".$currstart."' and '".$currend."'
          and nojurnal not like '%/CLSM/%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0)
    {
        echo "There is still yet balanced Journal:\n";
        $no=0;
        while($bar=$res->fetch())
        {
           $no+=1;
            echo $no.". No ".$bar->nojurnal.":".tanggalnormal($bar->tanggal)."->(D)Rp. ".number_format($bar->debet,0).":(K)Rp. ".number_format($bar->kredit,0)."\n"; 
        }
        exit('Error');
    }    
    #periksa gudang
    $str="select notransaksi,tanggal, kodegudang from ".$dbname.".log_transaksiht where post=0 and kodegudang like '".$_SESSION['empl']['lokasitugas']."%'
            and tanggal between '".$currstart."' and '".$currend."'";
    $stm='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
        while($bar=$res->fetch())
        {
             $stm.="Gudang:".$bar->kodegudang."->No.>".$bar->notransaksi."->".$bar->tanggal."<br>";
         }
       echo "Error: Warehouse transaction that has not been posted\r<br>".$stm; 
       exit();
    }
   // #Periksa TRAKSI
    // $str="select notransaksi,tanggal from ".$dbname.".vhc_runht where kodeorg='".$_SESSION['empl']['lokasitugas']."'
          // and tanggal between '".$currstart."' and '".$currend."' and posting=0";
    // $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    // $res->setFetchMode(PDO::FETCH_OBJ);
    // $numrows=owlBaris($res);
    // if($numrows>0)
    // {
        // echo " There still Vehicle Runn transaction that has not been posted:\n";
        // $no=0;
        // while($bar=$res->fetch())
        // {
           // $no+=1;
            // echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."\n";
        // }
        // exit('Error');
    // }    
}   

#PERIKSA akun transit yang belum nol=============================
$str="select sum(debet)-sum(kredit) as saldo FROM ".$dbname.".keu_jurnalsum_vw where  periode ='".$param['periode']."' 
          and kodeorg='".$_SESSION['empl']['lokasitugas']."' AND noakun like '4%'";
$transit=0;
 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
 $res->setFetchMode(PDO::FETCH_OBJ);
 $numrows=owlBaris($res);
 if($numrows>0){
        while($bar=$res->fetch())
        {
            $transit=$bar->saldo;
        }
}
if($transit>10 && $transit!='')#lebih dari  10 rupiah
{
    exit(" Error: Transit account has not been allocated correctly, remains:".$transit);
}
#---------------------------------------==================================

if(substr($_SESSION['empl']['lokasitugas'],2,2)!='HO' and substr($_SESSION['empl']['lokasitugas'],2,2)!='LO'){
    #PERIKSA apakah sudah ada gaji=============================
    $str="select nojurnal FROM ".$dbname.".keu_jurnalht where  tanggal like '".$param['periode']."%'
              and nojurnal like '%".$_SESSION['empl']['lokasitugas']."/KBN%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){

    }else{
        exit(" Error: Proses Gaji has not been processed. ");    
    }
    #---------------------------------------==================================
}

/**************************************************************
 * [START] Cek Pengakuan Penjualan ****************************
 **************************************************************/

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $listTiket="";
    $qJual = "SELECT notransaksi,a.nokontrak
            FROM ".$dbname.".pabrik_timbangan a
            INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
            WHERE date(a.tanggal) between '".$currstart."' and '".$currend.
                "'and a.nokontrak in (select nokontrak from ".$dbname.".pmn_kontrakjual where tanggalkontrak like '".substr($currstart,0,4)."%') 
                  and a.millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='PABRIK')";
    
    $resJual = fetchData($qJual);
        if(!empty($resJual)) {
            $listTiket = '';
            foreach($resJual as $row) {
                $scek2="select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual 
                        where notransaksi='".$row['notransaksi']."'";
                        //exit("error:".$scek2);
                $res=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                $numrows=owlBaris($res);
                $rcek2=$numrows;
                if($rcek2==0){
                    $listTiket .= "- ".$row['notransaksi']."\n";    
                }
            }
            if($listTiket!=''){
                exit("Warning: Ada Timbangan Pabrik ke Eksternal yang belum diakui\n".$listTiket);    
            }
            
        }
}

/**************************************************************
 * [END] Cek Pengakuan Penjualan ******************************
 **************************************************************/


/**************************************************************
 * [START] Buat HPP CPO,PK,TBS ********************************
 **************************************************************/
$tglPeriode="tanggal between '".$currstart."' and '".$currend."' ";
$tglPeriode2="left(tanggal,10) between '".$currstart."' and '".$currend."' ";
$ptUnit=$_SESSION['org']['kodeorganisasi'];
#cek apakah memiliki pabrik
#jika memiliki pabrik,cek apakah sudah akrtif mengolah jika iya,masuk ke dalam proses hpp. jika tidak, tidak masuk ke proses hpp
$sCekPabrikA="select count(kodeorganisasi) as jmlhPabrik from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$ptUnit."' ";
$qCekPabrikA=$owlPDO->query($sCekPabrikA) or die(print " Gagal: ".PDOException::getMessage());
$qCekPabrikA->setFetchMode(PDO::FETCH_ASSOC);
$rCekPabrikA=$qCekPabrikA->fetch();

#ambil porsi pembagi
$sPorsi="select sum(jumlah*(-1)) as jumlah,noakun from ".$dbname.".keu_jurnaldt_vw 
         where noakun in ('5110103','5110104') and kodeorg='".$_SESSION['empl']['lokasitugas']."' and tanggal like '".$param['periode']."%' group by noakun";
//exit('Warning'.$sPorsi);
$rPorsi=fetchData($sPorsi);
foreach ($rPorsi as $key => $val) {
    $nilRp[$val['noakun']]=$val['jumlah'];
    $totaRupiah+=$val['jumlah'];
}
@$cpo="5110103";
@$pk="5110104";
@$persenCpo=($nilRp[$cpo]/$totaRupiah);
@$persenKer=($nilRp[$pk]/$totaRupiah);
//exit('warning'.$nilRp[$cpo]."___".$totaRupiah."__".$persenCpo."__".$persenKer."__".$nilRp[$pk]);
if($rCekPabrikA['jmlhPabrik']!=0){
$sCekPabrik="select count(kodeorg) as jmlhPabrik from ".$dbname.".pabrik_produksi 
             where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$ptUnit."')  and  ".$tglPeriode."";
$qCekPabrik=$owlPDO->query($sCekPabrik) or die(print " Gagal: ".PDOException::getMessage());
$qCekPabrik->setFetchMode(PDO::FETCH_ASSOC);
$rCekPabrik=$qCekPabrik->fetch();
if(intval($rCekPabrik['jmlhPabrik'])!=0){//start pengecekan ada pabrik atau tidak
$ptUnit = $_SESSION['org']['kodeorganisasi'];
$unit = $_SESSION['empl']['lokasitugas'];
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO'){
    // Kode Barang
    $barang = array(
        'cpo' => '40000001',
        'pk' => '40000002',
        'tbs' => '40000003',
    );
    
    // Cek Saldo Awal HPP
    $qHpp = selectQuery($dbname,'keu_4hpp',"*",
                        "kodeorg = '".$unit."' and periode = '".$param['periode']."'");
    $resHpp = fetchData($qHpp);
    $optHpp = array();
    foreach($resHpp as $row) {
        $optHpp[$row['kodebarang']] = array(
            'qty' => $row['qtyawal'],
            'rp' => $row['rpawal']
        );
    }
    
    // Init Saldo Awal
    $cpoAwal = (empty($resHpp))? 0: $optHpp[$barang['cpo']]['qty'];
    $cpoRpAwal = (empty($resHpp))? 0: $optHpp[$barang['cpo']]['rp'];
    
    $pkAwal = (empty($resHpp))? 0: $optHpp[$barang['pk']]['qty'];
    $pkRpAwal = (empty($resHpp))? 0: $optHpp[$barang['pk']]['rp'];
    //Get noakun Piutang PABRIK
    $optPabrik=makeOption($dbname,'organisasi','induk,kodeorganisasi',"induk='".$ptUnit."' and tipe='HOLDING'");
    $strprsdn=$owlPDO->query("select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".$optPabrik[$ptUnit]."' and jenis='intra'");
    $strprsdn->setFetchMode(PDO::FETCH_OBJ);
    $barprsdn=$strprsdn->fetch();

    if ($barprsdn->akunpiutang=='') {
        exit("Warning : Account intraco or interco not available for ".$optPabrik[$ptUnit].". Please setting on menu Finance > setup > COA for Intra/Interco.");
    } 

    #mengambil hpp rupiah tbs#
    $sTotalRp="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where ".$tglPeriode." and noakun='".$barprsdn->akunpiutang."'
               and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$ptUnit."' and tipe='PABRIK')
               and nojurnal like '%PRSDN%'";
    $resTotalRp=fetchData($sTotalRp);
    ///
    $totByTotalRpPabrik=$resTotalRp[0]['jumlah'];
    //exit('warning'.$totByTotalRpPabrik);
    // $distByTotalPabrikCpo=$totByTotalRpPabrik*$persenCpo;
    // $distByTotalPabrikKer=$totByTotalRpPabrik*$persenKer;
    //exit('warning'.$totByTotalRpPabrik."___".$distByTotalPabrikCpo."__".$persenCpo."__".$persenKer);
    #hpp rupiah tbs#
    // Price
    @$cpoPriceAwal = empty($cpoAwal)? 0: $cpoRpAwal / $cpoAwal;
    @$pkPriceAwal = empty($pkAwal)? 0: $pkRpAwal / $pkAwal;
    /**
     * CPO & PK - Penerimaan
     */
    // Fisik
    $qCpoOlah = "SELECT sum(oer) as cpoqty, sum(oerpk) as pkqty
        FROM ".$dbname.".pabrik_produksi WHERE ".$tglPeriode."
        AND kodeorg in (select kodeorganisasi from ".$dbname.".organisasi
        where tipe='PABRIK' and induk='".$ptUnit."')";
    $resCpoOlah = fetchData($qCpoOlah);
    $cpoIn = empty($resCpoOlah[0]['cpoqty'])? 0: $resCpoOlah[0]['cpoqty'];
    $pkIn = empty($resCpoOlah[0]['pkqty'])? 0: $resCpoOlah[0]['pkqty'];

    
    $sNoakun="select distinct akunhutang from ".$dbname.".keu_5caco where jenis='Intra' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$ptUnit."' and tipe='PABRIK')";
    $qNoakun=fetchData($sNoakun);

    $optUnitPabrik=makeOption($dbname,'organisasi','induk,kodeorganisasi',"induk='".$ptUnit."' and tipe='PABRIK'");
    if ($qNoakun[0]['akunhutang']=='') {
        exit("Warning : Account intraco or interco not available for ".$optUnitPabrik[$ptUnit].". Please setting on menu Finance > setup > COA for Intra/Interco.");
    }
     
    #over head ro dan ho
    $daftBtlAkn=array();
    $sOverHead="select noakun,sum(jumlah) as overhead from ".$dbname.".keu_jurnaldt 
                where ".$tglPeriode." and noakun like '7%' and noakun!='7150201' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL') and induk='".$ptUnit."')  and noakun!='7199999'";
    //exit('warning:'.$sOverHead);
    $qOverHead=$owlPDO->query($sOverHead) or die(print " Gagal: ".PDOException::getMessage());
    $qOverHead->setFetchMode(PDO::FETCH_ASSOC);
    $rOverHead=$qOverHead->fetch();
    $byOverHead=$rOverHead['overhead'];   
    $optCpoOut = array(); 
    #[Mulai] 
    #average rumusan untuk cari prosentasi distribusi 
    #step pertama cari average harga berdasarkan totalrupiah penjualan/kg penjualan (dari menu pengakuan penjualan)
    #kg kirim dimana pengakuan timbangan pabrik
    $sKgPabrik="select a.kodebarang,beratbersih from ".$dbname.".pabrik_timbangan a left join 
               ".$dbname.".pmn_kontrakjual b on a.nokontrak=b.nokontrak where ".$tglPeriode2." 
               and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$ptUnit."' and tipe='PABRIK')
               and b.franco<>9 and a.kodebarang in ('40000001','40000002') order by a.kodebarang asc";
    $rKgPabrik=fetchData($sKgPabrik);
    foreach($rKgPabrik as $row){
        $optCpoOut[$row['kodebarang']]+=$row['beratbersih'];
    }

    #kg kirim dimana pengakuan timbangan pabrik
    $sKgPabrik="select a.kodebarang,beratbersih as beratbersih from ".$dbname.".pabrik_timbangan a left join 
               ".$dbname.".pmn_kontrakjual b on a.nokontrak=b.nokontrak where ".$tglPeriode2." 
               and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$ptUnit."' and tipe='PABRIK')
               and b.franco=9 and a.kodebarang in ('40000001','40000002') order by a.kodebarang asc";
    $rKgPabrik=fetchData($sKgPabrik);
    foreach($rKgPabrik as $row){
        $optCpoOut[$row['kodebarang']]+=$row['beratbersih'];
    }
    #rupiah berdasarkan pengakuan penjualan
    $sRupKirim="select kodebarang,sum(kredit) as rupiahKrm,noakun from ".$dbname.".keu_jurnaldt_vw where ".$tglPeriode." and kodeorg='".$unit."' and noakun in (5110103,5110104) group by noakun";
    $rRupKirim=fetchData($sRupKirim);
    foreach ($rRupKirim as $key => $val) {
        $rupiahRata[$val['kodebarang']]=$val['rupiahKrm']/$optCpoOut[$val['kodebarang']];
    }
    #prosentasi
    @$rupProdCpo=$rupiahRata[$barang['cpo']]*$cpoIn;
    @$rupProdKer=$rupiahRata[$barang['pk']]*$pkIn;
    @$persenCpo=($rupProdCpo/($rupProdCpo+$rupProdKer));
    @$persenKer=($rupProdKer/($rupProdCpo+$rupProdKer));

    $cpoOut=$optCpoOut[$barang['cpo']];
    $pkOut=$optCpoOut[$barang['pk']];
    #distribusi biaya produksi
    @$distByTotalPabrikCpo=$totByTotalRpPabrik*$persenCpo;#rupiah untuk jurnal alokasi overhead untuk cpo
    @$distByTotalPabrikKer=$totByTotalRpPabrik*$persenKer;#rupiah untuk jurnal alokasi overhead untuk ker
    //exit('warning'.$distByTotalPabrikCpo."__".$distByTotalPabrikKer);
    @$cpoOverhead=$byOverHead*$persenCpo;#rupiah untuk jurnal alokasi overhead untuk cpo
    @$kerOverhead=$byOverHead*$persenKer;#rupiah untuk jurnal alokasi overhead untuk ker

     
    #hpp olah     
    @$cpoPriceMutasi=($cpoRpAwal+($distByTotalPabrikCpo+$cpoOverhead))/($cpoAwal+$cpoIn);
    @$pkPriceMutasi=($pkRpAwal+($distByTotalPabrikKer +$kerOverhead))/($pkAwal+$pkIn);
    @$rpCpoOut=$cpoOut*$cpoPriceMutasi;
    @$rpPkOut=$pkOut*$pkPriceMutasi;
     
    
    //exit("error jam:".$sisaCpoOut."___".$rpCpoOut."___".$rpPkOut."___".$sisaPkOut."__".$pkPriceMutasi);
    /**
     * CPO & PK - Saldo Akhir
     */
    @$cpoQtyAkhir = $cpoAwal + $cpoIn - $cpoOut;
    @$cpoRpAkhir = $cpoRpAwal+($distByTotalPabrikCpo+$cpoOverhead) - $rpCpoOut;
    @$pkQtyAkhir = $pkAwal + $pkIn - $pkOut;
    @$pkRpAkhir = $pkRpAwal+($distByTotalPabrikKer +$kerOverhead) - $rpPkOut;


    /***************************************************************************
     ** Jurnal *****************************************************************
     ***************************************************************************/
    // Init Param
    $zJ = new zJournal();
    $lastDay = cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
    $nojurnal = $tahunbulan.$lastDay.'/'.$unit.'/HPP/001';
    $kodeJurnal = 'HPP';
    $tanggalJurnal = $param['periode'].'-'.$lastDay;
    $noUrut = 1;
    $noRef = $kodeJurnal.'/'.$unit.'/'.$tahunbulan;
    
    
    // Default Segment
    $defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
    
    // Delete Jurnal
    $qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal = '".$nojurnal."'");
    try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Delete Error !: " . $e->getMessage() . "\n"; die(); }
    
    
    // Prepare Data Header
    $dataResHPP['header'] = array(
        'nojurnal'=>$nojurnal, 'kodejurnal'=>$kodeJurnal,
        'tanggal'=>$tanggalJurnal, 'tanggalentry'=>date('Ymd'),
        'posting'=>'0',
        'totaldebet'=>'0', 'totalkredit'=>'0',
        'amountkoreksi'=>'0',
        'noreferensi'=>$noRef,
        'autojurnal'=>'1',
        'matauang'=>'IDR', 'kurs'=>'1',
        'revisi'=>'0'
    );
    
    // Prepare Data Detail
    $dataResHPP['detail'] = array();
    /***************************************************************************
     ** Jurnal CPO *************************************************************
     ***************************************************************************/
    if($distByTotalPabrikCpo !='') {
        // Proporsi total biaya pabrik - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150201',
            'keterangan'=>'Proporsi Total Biaya Pabrik Untuk CPO '.$ptUnit.' '.$param['periode'],
            'jumlah'=>($distByTotalPabrikCpo+$cpoOverhead),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += ($distByTotalPabrikCpo+$cpoOverhead);
        $dataResHPP['header']['totalkredit'] += ($distByTotalPabrikCpo+$cpoOverhead);
        
        // Proporsi total biaya pabrik - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>$qNoakun[0]['akunhutang'],
            'keterangan'=>'Total Biaya Pabrik Proporsi (CPO ) '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$distByTotalPabrikCpo * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        if($cpoOverhead!=''){
            $dataResHPP['detail'][] = array(
                'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
                'nourut'=>$noUrut, 'noakun'=>'7199999',
                'keterangan'=>'Total Biaya Tidak Langsung Ke (CPO ) '.$ptUnit.' '.$param['periode'],
                'jumlah'=>$cpoOverhead * (-1),
                'matauang'=>'IDR', 'kurs'=>'1',
                'kodeorg'=>$unit, 'kodekegiatan'=>'',
                'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
                'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
                'noreferensi'=>$noRef, 'noaruskas'=>'',
                'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
                'revisi'=>'0', 'kodesegment' => $defSegment
            );
            $noUrut++;
        }
    }
    
    if($rpCpoOut !='') {
        // CPO Dikirim - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6410800',
            'keterangan'=>'Harga Pokok Penjualan CPO '.$ptUnit.' '.$param['periode'].'; Fisik: Awal:'.number_format($cpoAwal).';Produksi:'.number_format($cpoIn).';Kirim:'.number_format($cpoOut).';Akhir:'.number_format($cpoQtyAkhir),
            'jumlah'=>$rpCpoOut,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $rpCpoOut;
        $dataResHPP['header']['totalkredit'] += $rpCpoOut;
        
        // CPO Dikirim - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150201',
            'keterangan'=>'Harga Pokok Penjualan CPO '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpCpoOut * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['cpo'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }
    
    
    /***************************************************************************
     ** Jurnal PK (Kernel) *****************************************************
     ***************************************************************************/
    if($distByTotalPabrikKer !='') {
        //  Proporsi total biaya pabrik - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150202',
            'keterangan'=>'Proporsi total biaya pabrik (PK) '.$ptUnit.' '.$param['periode'],
            'jumlah'=>($distByTotalPabrikKer +$kerOverhead),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += ($distByTotalPabrikKer +$kerOverhead);
        $dataResHPP['header']['totalkredit'] += ($distByTotalPabrikKer +$kerOverhead);
        
        // Proporsi total biaya pabrik - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>$qNoakun[0]['akunhutang'],
            'keterangan'=>'Proporsi total biaya pabrik (PK) '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$distByTotalPabrikKer * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        if($kerOverhead!=''){
            $dataResHPP['detail'][] = array(
                'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
                'nourut'=>$noUrut, 'noakun'=>'7199999',
                'keterangan'=>'Biaya Tidak Langung Ke persediaan (PK) '.$ptUnit.' '.$param['periode'],
                'jumlah'=>$kerOverhead * (-1),
                'matauang'=>'IDR', 'kurs'=>'1',
                'kodeorg'=>$unit, 'kodekegiatan'=>'',
                'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
                'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
                'noreferensi'=>$noRef, 'noaruskas'=>'',
                'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
                'revisi'=>'0', 'kodesegment' => $defSegment
            );  
            $noUrut++;
        }
    }
    

    if($rpPkOut !='') {
        // PK Dikirim - Debet
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'6410900',
            'keterangan'=>'Harga Pokok Penjualan PK '.$ptUnit.' '.$param['periode'].'; Fisik: Awal:'.number_format($pkAwal).';Produksi:'.number_format($pkIn).';Kirim:'.number_format($pkOut).';Akhir:'.number_format($pkQtyAkhir),
            'jumlah'=>$rpPkOut,
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
        $dataResHPP['header']['totaldebet'] += $rpPkOut;
        $dataResHPP['header']['totalkredit'] += $rpPkOut;
        
        // PK Dikirim - Kredit
        $dataResHPP['detail'][] = array(
            'nojurnal'=>$nojurnal, 'tanggal'=>$tanggalJurnal,
            'nourut'=>$noUrut, 'noakun'=>'1150202',
            'keterangan'=>'Harga Pokok Penjualan PK '.$ptUnit.' '.$param['periode'],
            'jumlah'=>$rpPkOut * (-1),
            'matauang'=>'IDR', 'kurs'=>'1',
            'kodeorg'=>$unit, 'kodekegiatan'=>'',
            'kodeasset'=>'', 'kodebarang'=>$barang['pk'],
            'nik'=>'', 'kodecustomer'=>'', 'kodesupplier'=>'',
            'noreferensi'=>$noRef, 'noaruskas'=>'',
            'kodevhc'=>'', 'nodok'=>$noRef, 'kodeblok'=>'',
            'revisi'=>'0', 'kodesegment' => $defSegment
        );
        $noUrut++;
    }

    if(!empty($dataResHPP['detail'])){
        // Lakukan Jurnal
        $zJ->doJournal($ptUnit,$kodeJurnal,$dataResHPP,1,"",false);
    }
    
    // Insert ke Saldo Awal HPP
    $nxtBulan = ($bulan<12)? $bulan+1: 1;
    $nxtTahun = ($bulan<12)? $tahun: $tahun+1;
    $nxtPeriod = $nxtTahun.'-'.str_pad($nxtBulan,2,'0',STR_PAD_LEFT);
    
    $dataHpp = array();
    // // TBS
    // $dataHpp[] = array(
    //     'kodeorg' => $unit,
    //     'periode' => $nxtPeriod,
    //     'kodebarang' => $barang['tbs'],
    //     'qtyawal' => $tbsQtyAkhir,
    //     'rpawal' => $tbsRpAkhir,
    // );
    
    // CPO
    $dataHpp[] = array(
        'kodeorg' => $unit,
        'periode' => $nxtPeriod,
        'kodebarang' => $barang['cpo'],
        'qtyawal' => $cpoQtyAkhir,
        'rpawal' => $cpoRpAkhir,
    );
    
    // PK
    $dataHpp[] = array(
        'kodeorg' => $unit,
        'periode' => $nxtPeriod,
        'kodebarang' => $barang['pk'],
        'qtyawal' => $pkQtyAkhir,
        'rpawal' => $pkRpAkhir,
    );
    
    // Delete Saldo Awal HPP
    $qDelHPP = deleteQuery($dbname,'keu_4hpp',"kodeorg='".$unit."' and periode='".$nxtPeriod."'");
    try{$owlPDO->exec($qDelHPP); }catch (PDOException $e) {print " Delete HPP Error !: " . $e->getMessage() . "\n"; die(); }
    
    // Insert Saldo Awal HPP
    $qInsHPP = insertQuery($dbname,'keu_4hpp',$dataHpp);
    try{$owlPDO->exec($qInsHPP); }
    catch (PDOException $e) {
        echo "Insert HPP Error: " . $e->getMessage() . "\n"; 
          $zJ->rbJournal($nojurnal);
    }
}
/**************************************************************
 * [END] Buat HPP CPO,PK,TBS **********************************
 **************************************************************/
}//end pengecekan pabrik sudah mengolah atau belum
}//end pengecekan ada pabrik atau tidak

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
            $sPeriode="select periode from ".$dbname.".setup_periodeakuntansi 
                       where kodeorg='".$_SESSION['empl']['lokasitugas']."' order by periode desc limit 1";
            $qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
            $qPeriode->setFetchMode(PDO::FETCH_ASSOC);
            $rPeriode=$qPeriode->fetch();
            if($rPeriode['periode']==$param['periode']){
                $sDel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
                try{$owlPDO->exec($sDel); }catch (PDOException $e) {print " Delete HPP Error !: " . $e->getMessage() . "\n"; die(); }
            }else{
                echo ' Error : This period has been closed(Before).';
                exit;    
            }
        }
        
         $query = "select count(*) as x from ".$dbname.".keu_jurnaldt_vw where 
                   tanggal between '".$currstart."' and '".$currend."' and kodeorg='".$param['kodeorg']."'";
//         exit("error: ".$query);
        $res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
       if(owlbaris($res)==0) {
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
 /*    kredit tidak perlu untuk laba rugi tahun berjalan   
        # Kredit
        $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$tgl,
            'nourut'=>$noUrut,
            'noakun'=>$akunKredit,
            'keterangan'=>'Tutup Bulan '.$tahunbulan.' Unit '.$param['kodeorg'],
            'jumlah'=>-1*$data[0]['jumlah'],
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
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>''
            
        );
  *        $noUrut++; 
  * 
  */

       #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
        $headErr = '';
        $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
        try{$owlPDO->exec($insHead); }
        catch (PDOException $e) {
            $headErr .= "Insert Header Error :  " . $e->getMessage() . "\n".$insHead;          
        }
        
        if($headErr=='') {
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
            $detailErr = '';
            foreach($dataRes['detail'] as $row) {
                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                try{$owlPDO->exec($insDet); }
                catch (PDOException $e) {
                    $detailErr .= "Insert Detail Error :  " . $e->getMessage() . "\n".$insDet;      
                    break;
                }   
            }
            
            if($detailErr=='') {
                    #==================== /Prep Jurnal ====================================
                    createSaldoAwal($param['periode'],$tahunLanjut.'-'.addZero($bulanLanjut,2),$param['kodeorg']);
                    
                    
                    //exit("Error:ASDASD");
                    
                    #========================== Proses Insert dan Update ==========================
 
                # Header and Detail inserted
                # Update Status Tutup Buku
                $queryUpd = updateQuery($dbname,'setup_periodeakuntansi',array('tutupbuku'=>1),
                    "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
                try{$owlPDO->exec($queryUpd); }
                catch (PDOException $e) {
                    echo "Error Update :  " . $e->getMessage() . "\n".$queryUpd;      
                    exit;
                }

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
                    try{$owlPDO->exec($queryIns); }
                    catch (PDOException $e) {
                        echo "Error Insert :" . $e->getMessage() . "\n".$queryUpd;      
                        $queryRB = updateQuery($dbname,'setup_periodeakuntansi',array('tutupbuku'=>0),
                            "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
                        try{$owlPDO->exec($queryRB); }
                        catch (PDOException $e) {
                            echo "Error Rollback Update :  " . $e->getMessage() . "\n".$queryRB;      
                            exit;
                        }
                         exit;
                    }                    
                            //update history tutup buku
                            $str="delete from ".$dbname.".keu_setup_watu_tutup where periode='".$param['periode']."'  and kodeorg='".$param['kodeorg']."'";
                            $owlPDO->exec($str);
                            $str="insert into ".$dbname.".keu_setup_watu_tutup(kodeorg,periode,username) values(
                                  '".$param['kodeorg']."','".$param['periode']."','".$_SESSION['standard']['username']."')";
                            $owlPDO->exec($str);                                          
            } else {
                echo $detailErr;
                # Rollback, Delete Header
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                try{$owlPDO->exec($RBDet); }
                catch (PDOException $e) {
                    echo "Error Rollback Update :  " . $e->getMessage() . "\n".$RBDet;      
                    exit;
                }                
            }
        } else {
            echo $headErr;
            exit;
        }
        
        
 #email notifikasi bjr       
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
    global $owlPDO;
    $sawal=Array();
    $mtdebet=Array();
    $mtkredit=Array();
    $salak=Array();
        
    #ambil saldoawal bulan berjalan
    $str="select awal".substr($dariperiode,5,2).",noakun from ".$dbname.".keu_saldobulanan
          where periode='".str_replace("-", "", $dariperiode)."' and kodeorg='".$kodeorg."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_NUM);
    while($bar=$res->fetch())
    {
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
    while($bar=$res->fetch())
    {
        $mtdebet[$bar->noakun]=$bar->debet;
        $mtkredit[$bar->noakun]=$bar->kredit;
        $salak[$bar->noakun]=$mtdebet[$bar->noakun]+$sawal[$bar->noakun]-$mtkredit[$bar->noakun];
    }
    #ambil semu nomor akun
    $str="select noakun from ".$dbname.".keu_5akun where length(noakun)=7";
    $temp='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
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
           try{$owlPDO->exec($temp); }catch (PDOException $e) {print " Error insert mutasi bulanan !: " . $e->getMessage() . "\n"; die(); }  
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
                   try{$owlPDO->exec($temp); }catch (PDOException $e) {print "Error insert saldo awal !: " . $e->getMessage() . "\n"; die(); } 
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
                        try{$owlPDO->exec($temp1); }catch (PDOException $e) {print "Error insert saldo awal !: " . $e->getMessage() . "\n"; die(); }
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
        try{$owlPDO->exec($temp2); }catch (PDOException $e) {print "Error insert laba ditahan pada saldo awal  !: " . $e->getMessage() . "\n"; die(); }  
     }
    }catch (PDOException $e){
        print " Error : " . $e->getMessage() . "\n"; die(); 
    }   
}   
?>