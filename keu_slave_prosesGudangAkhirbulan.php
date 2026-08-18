<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;
//hilangkan koma
$param['hartot']=str_replace(",","",$param['hartot']);

if($_POST['hartot']<0.9)
{
    exit('Masih Ada harga barang yang belum ada harganya, mohon diperiksa transaksi anda\natau hubungi departement terkait');
}

#ambil nama barang
$str="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$_POST['kodebarang']."'";
$namabarang='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namabarang=$bar->namabarang;
}
if($namabarang=='')
    $namabaran=$_POST['kodebarang'];

#contoh format parameter
#Array
#(
#    [tipetransaksi] => 5 
#    [notransaksi] => 
#    [kodebarang] => 35100001
#    [jumlah] => 6
#    [satuan] => LTR
#    [idsupplier] => 
#    [gudangx] => 
#    [untukunit] => WKNE
#    [kodeblok] => WKNE03
#    [kodemesin] => 10KVAGNT06
#    [kodekegiatan] => 30101
#    [hartot] => 27937.23
#    [nopo] => -
#)
#
#tipe transaksi
//1=Masuk,
//3=penerimaan mutasi,
//5=Pengeluaran,
//7 pengeluaran mutasi
#barang masuk================================================================================
if($_POST['tipetransaksi']==1){
    #ambil noakun supplier
    $kodekl=substr($_POST['idsupplier'],0,4);
    $str="select noakun from ".$dbname.".log_5klsupplier where kode='".$kodekl."'";
    $akunspl='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $akunspl=$bar->noakun;
    }
   #ambil noakun barang
    $klbarang=substr($_POST['kodebarang'],0,3);
    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
    $akunbarang='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $akunbarang=$bar->noakun;
    }   
    if($akunbarang=='' or $akunspl=='')
        exit("Error: Noakun  Noakun barang atau supplier  belum ada untuk transaksi".$_POST['notransaksi']);
    else{
       #proses data
        $kodeJurnal = 'INVM1';
        #======================== Nomor Jurnal =============================
        # Get Journal Counter
        $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
            "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
        $tmpKonter = fetchData($queryJ);
        $konter = addZero($tmpKonter[0]['nokounter']+1,3);

        # Transform No Jurnal dari No Transaksi
        $nojurnal = str_replace("-","",$_POST['tanggal'])."/".substr($_POST['kodegudang'],0,4)."/".$kodeJurnal."/".$konter;
        #======================== /Nomor Jurnal ============================
        
        # Prep Header
            $dataRes['header'] = array(
                'nojurnal'=>$nojurnal,
                'kodejurnal'=>$kodeJurnal,
                'tanggal'=>$_POST['tanggal'],
                'tanggalentry'=>date('Ymd'),
                'posting'=>1,
                'totaldebet'=>$_POST['hartot'],
                'totalkredit'=>-1*$_POST['hartot'],
                'amountkoreksi'=>'0',
                'noreferensi'=>$_POST['notransaksi'],
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
                'tanggal'=>$_POST['tanggal'],
                'nourut'=>$noUrut,
                'noakun'=>$akunbarang,
                'keterangan'=>'Pembelian barang '.$namabarang.' '.$_POST['jumlah']." ".$_POST['satuan'],
                'jumlah'=>$_POST['hartot'],
                'matauang'=>'IDR',
                'kurs'=>'1',
                'kodeorg'=>substr($_POST['kodegudang'],0,4),
                'kodekegiatan'=>'',
                'kodeasset'=>'',
                'kodebarang'=>$_POST['kodebarang'],
                'nik'=>'',
                'kodecustomer'=>'',
                'kodesupplier'=>$_POST['idsupplier'],
                'noreferensi'=>$_POST['notransaksi'],
                'noaruskas'=>'',
                'kodevhc'=>'',
                'nodok'=>$_POST['nopo'],
                'kodeblok'=>'',
                'revisi'=>'0',
				'kodesegment'=>null
            );
            $noUrut++;

            # Kredit
            $dataRes['detail'][] = array(
                'nojurnal'=>$nojurnal,
                'tanggal'=>$_POST['tanggal'],
                'nourut'=>$noUrut,
                'noakun'=>$akunspl,
                'keterangan'=>'Pembelian barang '.$namabarang.' '.$_POST['jumlah']." ".$_POST['satuan'],
                'jumlah'=>-1*$_POST['hartot'],
                'matauang'=>'IDR',
                'kurs'=>'1',
                'kodeorg'=>substr($_POST['kodegudang'],0,4),
                'kodekegiatan'=>'',
                'kodeasset'=>'',
                'kodebarang'=>$_POST['kodebarang'],
                'nik'=>'',
                'kodecustomer'=>'',
                'kodesupplier'=>$_POST['idsupplier'],
                'noreferensi'=>$_POST['notransaksi'],
                'noaruskas'=>'',
                'kodevhc'=>'',
                'nodok'=>$_POST['nopo'],
                'kodeblok'=>'',
                'revisi'=>'0',
				'kodesegment'=>null
            );
            $noUrut++;      
    }   
             #===========EXECUTE
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            try{
                $owlPDO->exec($insHead); 
            }catch (PDOException $e) {
               $headErr .= 'Insert Header Error : '. $e->getMessage() . "<br/>"; 
            }

            if($headErr=='') {
                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    try{
                        $owlPDO->exec($insDet); 
                    }catch (PDOException $e) {
                       $detailErr .= "Insert Detail Error: ". $e->getMessage() . "<br/>"; 
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
                        $updTrans = updateQuery($dbname,'log_transaksiht',array('statusjurnal'=>1),
                            "notransaksi='".$_POST['notransaksi']."'");
                        try{
                            $owlPDO->exec($updTrans); 
                        }catch (PDOException $e) {
                            echo "Update Status Jurnal Error : ".$e->getMessage()."\n";
                            # Rollback if Update Failed
                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            try{
                                $owlPDO->exec($RBDet); 
                            }catch (PDOException $e) {
                                echo "Rollback Delete Header Error : ".$e->getMessage()."\n";
                            }

                            $RBJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter-1),
                                "kodeorg='".$_SESSION['org']['kodeorganisasi'].
                                "' and kodekelompok='".$kodeJurnal."'");
                            try{
                                $owlPDO->exec( $RBJurnal); 
                            }catch (PDOException $e) {
                                echo "Rollback Update Jurnal Error : ".$e->getMessage()."\n";
                            }
                            exit;
                        }
                      
                    }catch (PDOException $e) {
                        print " Update Kode Jurnal Error: " . $e->getMessage() . "<br/>";
                         $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                         try{
                                $owlPDO->exec($RBDet); 
                            }catch (PDOException $e) {
                                echo "Rollback Delete Header Error " . $e->getMessage() . "<br/>"; die();
                                exit;
                            }
                    }

                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                      try{
                            $owlPDO->exec($RBDet); 
                        }catch (PDOException $e) {
                            echo "Rollback Delete Header Error " . $e->getMessage() . "<br/>"; die();
                            exit;
                        }
                }
            } else {
                echo $headErr;
                exit;
            }     
    }
 else if($_POST['tipetransaksi']==3){
     #penerimaan mutasi================================================================================
    # periksa apakah dari satu PT
     $pengirim=substr($_POST['gudangx'],0,4);
     
     $ptPengirim='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengirim."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
     while($bar=$res->fetch())
     {
         $ptPengirim=$bar->induk;
     }
     
     $ptGudang='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($_POST['kodegudang'],0,4)."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
     while($bar=$res->fetch())
     {
         $ptGudang=$bar->induk;
     }
     #jika pt tidak sama maka pakai akun interco
     $akunspl='';
     if($ptGudang !=$ptPengirim)
     {
         #ambil akun interco
        $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".$pengirim."' and jenis='inter'";
        $akunspl='';
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $akunspl=$bar->akunhutang;
        }

        if ($akunspl=='') {
            exit("Warning : Account intraco or interco not available for ".$pengirim.". Please setting on menu Finance > setup > COA for Intra/Interco.");
        }
     }
     else
     {
        #ambil akun intraco
        $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".$pengirim."' and jenis='intra'";
        $akunspl='';
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $akunspl=$bar->akunhutang;
        } 

        if ($akunspl=='') {
            exit("Warning : Account intraco or interco not available for ".$pengirim.". Please setting on menu Finance > setup > COA for Intra/Interco.");
        }
     }
#ambil noakun barang
    $klbarang=substr($_POST['kodebarang'],0,3);
    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
    $akunbarang='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
     while($bar=$res->fetch())
     {
        $akunbarang=$bar->noakun;
    }   
    if($akunbarang=='' or $akunspl=='')
        exit("Error: Noakun barang atau intra/interco belum ada untuk transaksi ".$_POST['notransaksi']);
    else{
       #proses data
        $kodeJurnal = 'INVM1';
        #======================== Nomor Jurnal =============================
        # Get Journal Counter
        $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
            "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
        $tmpKonter = fetchData($queryJ);
        $konter = addZero($tmpKonter[0]['nokounter']+1,3);

        # Transform No Jurnal dari No Transaksi
        $nojurnal = str_replace("-","",$_POST['tanggal'])."/".substr($_POST['kodegudang'],0,4)."/".$kodeJurnal."/".$konter;
        #======================== /Nomor Jurnal ============================
        
        # Prep Header
            $dataRes['header'] = array(
                'nojurnal'=>$nojurnal,
                'kodejurnal'=>$kodeJurnal,
                'tanggal'=>$_POST['tanggal'],
                'tanggalentry'=>date('Ymd'),
                'posting'=>1,
                'totaldebet'=>$_POST['hartot'],
                'totalkredit'=>-1*$_POST['hartot'],
                'amountkoreksi'=>'0',
                'noreferensi'=>$_POST['notransaksi'],
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
                'tanggal'=>$_POST['tanggal'],
                'nourut'=>$noUrut,
                'noakun'=>$akunbarang,
                'keterangan'=>'Mutasi barang '.$namabarang.' '.$_POST['jumlah']." ".$_POST['satuan'],
                'jumlah'=>$_POST['hartot'],
                'matauang'=>'IDR',
                'kurs'=>'1',
                'kodeorg'=>substr($_POST['kodegudang'],0,4),
                'kodekegiatan'=>'',
                'kodeasset'=>'',
                'kodebarang'=>$_POST['kodebarang'],
                'nik'=>'',
                'kodecustomer'=>'',
                'kodesupplier'=>$_POST['idsupplier'],
                'noreferensi'=>$_POST['notransaksi'],
                'noaruskas'=>'',
                'kodevhc'=>'',
                'nodok'=>$_POST['nopo'],
                'kodeblok'=>'',
                'revisi'=>'0',
				'kodesegment'=>null
            );
            $noUrut++;

            # Kredit
            $dataRes['detail'][] = array(
                'nojurnal'=>$nojurnal,
                'tanggal'=>$_POST['tanggal'],
                'nourut'=>$noUrut,
                'noakun'=>$akunspl,
                'keterangan'=>'Mutasi barang '.$namabarang.' '.$_POST['jumlah']." ".$_POST['satuan'],
                'jumlah'=>-1*$_POST['hartot'],
                'matauang'=>'IDR',
                'kurs'=>'1',
                'kodeorg'=>substr($_POST['kodegudang'],0,4),
                'kodekegiatan'=>'',
                'kodeasset'=>'',
                'kodebarang'=>$_POST['kodebarang'],
                'nik'=>'',
                'kodecustomer'=>'',
                'kodesupplier'=>$_POST['idsupplier'],
                'noreferensi'=>$_POST['notransaksi'],
                'noaruskas'=>'',
                'kodevhc'=>'',
                'nodok'=>$_POST['nopo'],
                'kodeblok'=>'',
                'revisi'=>'0',
				'kodesegment'=>null
            );
            $noUrut++;      
    }
             #===========EXECUTE
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            try{
                $owlPDO->exec($insHead); 
            }catch (PDOException $e) {
                $headErr .= "Insert Header Error :". $e->getMessage() . "<br/>";
            }

            if($headErr=='') {
                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    try{
                        $owlPDO->exec($insDet); 
                    }catch (PDOException $e) {
                        $detailErr .= "Insert Detail Error : ". $e->getMessage() . "<br/>"; 
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
                        $updTrans = updateQuery($dbname,'log_transaksiht',array('statusjurnal'=>1),
                            "notransaksi='".$_POST['notransaksi']."'");
                        try{
                            $owlPDO->exec($updTrans); 
                        }catch (PDOException $e) {
                            echo "Update Status Jurnal Error : ".$e->getMessage()."\n";
                            # Rollback if Update Failed
                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                try{
                                    $owlPDO->exec($RBDet); 
                                }catch (PDOException $e) {
                                    echo "Rollback Delete Header Error : ". $e->getMessage() . "<br/>"; 
                                } 

                            $RBJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter-1),
                                "kodeorg='".$_SESSION['org']['kodeorganisasi'].
                                "' and kodekelompok='".$kodeJurnal."'");
                                try{
                                    $owlPDO->exec($RBJurnal); 
                                }catch (PDOException $e) {
                                    echo "Rollback Counter Jurnal Error : ". $e->getMessage() . "<br/>"; 
                                } 
                            exit;
                        }                             
                    }catch (PDOException $e) {
                        echo "Update Counter Jurnal Error : ".$e->getMessage()."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        try{
                            $owlPDO->exec($RBDet); 
                        }catch (PDOException $e) {
                            echo "Rollback Delete Header Error : ". $e->getMessage() . "<br/>"; 
                        }  
                        exit;
                    }
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    try{
                        $owlPDO->exec($RBDet); 
                    }catch (PDOException $e) {
                        echo "Rollback Delete Header Error : ". $e->getMessage() . "<br/>"; 
                    }                    
                    exit;
                }
            } else {
                echo $headErr;
                exit;
            }     
 }  
 else if($_POST['tipetransaksi']==5){
     #pengeluaran barang normal================================================================================
	
     #periksa apakah dari satu PT
     $pengguna=substr($_POST['untukunit'],0,4);
    
     $ptpengguna='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
     while($bar=$res->fetch())
     {
         $ptpengguna=$bar->induk;
     }
      $str="select akunpiutang,jenis from ".$dbname.".keu_5caco where 
           kodeorg='".$pengguna."'";
     $intraco='';
     $interco='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
     while($bar=$res->fetch())
     {
         if($bar->jenis=='intra')
            $intraco=$bar->akunpiutang;
         else
            $interco=$bar->akunpiutang; 
     }

    if ($intraco=='' || $interco=='') {
        exit("Warning : Account intraco or interco not available for ".$pengguna.". Please setting on menu Finance > setup > COA for Intra/Interco.");
    }
     
     
     $ptGudang='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($_POST['kodegudang'],0,4)."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
     while($bar=$res->fetch())
     {
         $ptGudang=$bar->induk;
     }
     #jika pt tidak sama maka pakai akun interco
     $akunspl='';
     if($ptGudang !=$ptpengguna)
     {
         #ambil akun interco
            $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($_POST['kodegudang'],0,4)."' and jenis='inter'";
            $akunspl='';
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
             while($bar=$res->fetch())
             {
                $akunspl=$bar->akunhutang;
            }
         $inter=$interco;   
        if($akunspl=='')
           exit("Error: Akun intraco  atau interco belum ada untuk unit ".$pengguna); 
     }
     else if($pengguna!=substr($_POST['kodegudang'],0,4)){ #jika satu pt beda kebun
          #ambil akun intraco
            $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($_POST['kodegudang'],0,4)."' and jenis='intra'";
            $akunspl='';
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
             while($bar=$res->fetch())
             {
                $akunspl=$bar->akunhutang;
            } 
          $inter=$intraco;  
         if($akunspl=='')
            exit("Error: Akun intraco  atau interco belum ada untuk unit ".$pengguna);    
     }
     
    #ambil akun pekerjaan atau kendaraan atau ab
     #periksa ke table setup blok
     $statustm='';
     $str="select statusblok from ".$dbname.".setup_blok where kodeorg='".$_POST['kodeblok']."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
     while($bar=$res->fetch())
     {
         $statustm=$bar->statusblok;
     }
     #jika statustm ada maka ambil noakun berdasarkan kegiatan dan tipe blok
     if($statustm!='' and $_POST['kodemesin']==''){
          $str="select noakun from ".$dbname.".setup_kegiatan where 
                kodekegiatan='".$_POST['kodekegiatan']."'";
     }
     else{
         $str="select noakun from ".$dbname.".setup_kegiatan where 
                kodekegiatan='".$_POST['kodekegiatan']."'";
     }
     $akunpekerjaan='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
     while($bar=$res->fetch())
     {
         $akunpekerjaan=$bar->noakun;
     }
     #jika akun kegiatan tidak ada maka exit
     if($akunpekerjaan=='')
         exit("Error: Akun pekerjaan belum ada untuk kegiatan ".$_POST['kodekegiatan']);
     
    #ambil noakun barang
    $klbarang=substr($_POST['kodebarang'],0,3);
    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
    $akunbarang='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
     while($bar=$res->fetch())
     {
        $akunbarang=$bar->noakun;
    }   
    if($akunbarang=='')
        exit("Error: Noakun barang belum ada untuk transaksi".$_POST['notransaksi']);
    else{
        //penggunaan internal
        if($pengguna==substr($_POST['kodegudang'],0,4)){
                    $kodeJurnal = 'INVK1';
                    #======================== Nomor Jurnal =============================
                    # Get Journal Counter
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",$_POST['tanggal'])."/".substr($_POST['kodegudang'],0,4)."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================
                    # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>$_POST['tanggal'],
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>1*$_POST['hartot'],
                            'totalkredit'=>-1*$_POST['hartot'],
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$_POST['notransaksi'],
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                'revisi'=>'0'                            
                        );

                        # Data Detail
                        $noUrut = 1;
                         $keterangan="Pemakaian barang ".$namabarang." ".$_POST['jumlah']." ".$_POST['satuan']." ".$_POST['keterangan'];
                         $keterangan=substr($keterangan,0,150);
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$_POST['tanggal'],
                            'nourut'=>$noUrut,
                            'noakun'=>$akunpekerjaan,
                            'keterangan'=> $keterangan,
                            'jumlah'=>$_POST['hartot'],
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($_POST['kodegudang'],0,4),
                            'kodekegiatan'=>$_POST['kodekegiatan'],
                            'kodeasset'=>'',
                            'kodebarang'=>$_POST['kodebarang'],
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$_POST['notransaksi'],
                            'noaruskas'=>'',
                            'kodevhc'=>$_POST['kodemesin'],
                            'nodok'=>'',
                            'kodeblok'=>$_POST['kodeblok'],
							'revisi'=>'0',
							'kodesegment'=>null
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$_POST['tanggal'],
                            'nourut'=>$noUrut,
                            'noakun'=>$akunbarang,
                            'keterangan'=>$keterangan,
                            'jumlah'=>-1*$_POST['hartot'],
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($_POST['kodegudang'],0,4),
                            'kodekegiatan'=>$_POST['kodekegiatan'],
                            'kodeasset'=>'',
                            'kodebarang'=>$_POST['kodebarang'],
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$_POST['notransaksi'],
                            'noaruskas'=>'',
                            'kodevhc'=>$_POST['kodemesin'],
                            'nodok'=>'',
                            'kodeblok'=>$_POST['kodeblok'],
							'revisi'=>'0',
							'kodesegment'=>null
                        );
                        $noUrut++; 
                    #===========EXECUTE
                    $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                    try{
                        $owlPDO->exec($RBDet2); 
                    }catch (PDOException $e) {
                       $headErr .= "Insert Header Error : " . $e->getMessage() . "<br/>"; 
                    }

                    if($headErr=='') {
                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                        $detailErr = '';
                        foreach($dataRes['detail'] as $row) {
                            $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                            try{
                                $owlPDO->exec($insDet); 
                            }catch (PDOException $e) {
                               $detailErr .= "Insert Detail Error :" . $e->getMessage() . "<br/>";
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
                            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Jurnal di log_transaksiht
                                    $updTrans = updateQuery($dbname,'log_transaksiht',array('statusjurnal'=>1),
                                        "notransaksi='".$_POST['notransaksi']."'");
                                    try{
                                        $owlPDO->exec($updTrans); 
                                    }catch (PDOException $e) {
                                        echo "Update Status Jurnal Error : ".$e->getMessage()."\n";
                                        # Rollback if Update Failed
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        try{
                                            $owlPDO->exec($RBDet); 
                                        }catch (PDOException $e) {
                                           echo "Rollback Delete Header Error :" . $e->getMessage() . "<br/>";
                                        }
                                        //==
                                        $RBJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter-1),
                                            "kodeorg='".$_SESSION['org']['kodeorganisasi'].
                                            "' and kodekelompok='".$kodeJurnal."'");
                                        try{
                                            $owlPDO->exec($RBJurnal); 
                                        }catch (PDOException $e) {
                                           echo "Rollback Update Jurnal Counter Error :" . $e->getMessage() . "<br/>";
                                        }
                                        exit; 
                                    }                                                                       
                                }catch (PDOException $e) {
                                    echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                    # Rollback if Update Failed
                                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                    try{
                                        $owlPDO->exec($RBDet); 
                                    }catch (PDOException $e) {
                                       echo "Rollback Delete Header Error :" . $e->getMessage() . "<br/>";
                                    } 
                                    exit;
                                }
                        } else {
                            echo $detailErr;
                            # Rollback, Delete Header
                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            try{
                                $owlPDO->exec($RBDet); 
                            }catch (PDOException $e) {
                               echo "Rollback Delete Header Error :" . $e->getMessage() . "<br/>";
                                exit; 
                            }  
                        }                              
                        
                    } else {
                        echo $headErr;
                        exit;
                    }
        }
         else{
             #jika inter atau intraco 
                  #proses data sisi pemilik====================================================
                    $kodeJurnal = 'INVK1';
                    #======================== Nomor Jurnal =============================
                    # Get Journal Counter
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",$_POST['tanggal'])."/".substr($_POST['kodegudang'],0,4)."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================

                    # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>$_POST['tanggal'],
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>1*$_POST['hartot'],
                            'totalkredit'=>-1*$_POST['hartot'],
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$_POST['notransaksi'],
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                'revisi'=>'0'                            
                        );

                        # Data Detail
                        $noUrut = 1;
                         $keterangan="Pemakaian barang ".$namabarang." ".$_POST['jumlah']." ".$_POST['satuan']." ".$_POST['keterangan'];
                         $keterangan=substr($keterangan,0,150);
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$_POST['tanggal'],
                            'nourut'=>$noUrut,
                            'noakun'=>$inter,
                            'keterangan'=>$keterangan,
                            'jumlah'=>$_POST['hartot'],
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($_POST['kodegudang'],0,4),
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$_POST['kodebarang'],
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$_POST['notransaksi'],
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment'=>null
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$_POST['tanggal'],
                            'nourut'=>$noUrut,
                            'noakun'=>$akunbarang,
                            'keterangan'=>$keterangan,
                            'jumlah'=>-1*$_POST['hartot'],
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>substr($_POST['kodegudang'],0,4),
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$_POST['kodebarang'],
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$_POST['notransaksi'],
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment'=>null
                        );
                        $noUrut++; 
                    #===========EXECUTE
                    $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                    try{
                        $owlPDO->exec($insHead); 
                    }catch (PDOException $e) {
                       $headErr .= "Insert Header Error : " . $e->getMessage() . "<br/>"; 
                    }

                    if($headErr=='') {
                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                        $detailErr = '';
                        foreach($dataRes['detail'] as $row) {
                            $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                            try{
                                $owlPDO->exec($insDet); 
                            }catch (PDOException $e) {
                                $detailErr .= "Insert Detail Error sisi pemilik: " . $e->getMessage() . "<br/>"; 
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
                                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Jurnal di log_transaksiht
                                $updTrans = updateQuery($dbname,'log_transaksiht',array('statusjurnal'=>1),
                                    "notransaksi='".$_POST['notransaksi']."'");
                                try{
                                    $owlPDO->exec($updTrans); 
                                }catch (PDOException $e) {
                                    echo "Update Status Jurnal Error : ".$e->getMessage()."\n";
                                    # Rollback if Update Failed
                                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                    try{
                                        $owlPDO->exec($RBDet); 
                                    }catch (PDOException $e) {
                                        echo "Rollback Delete Header Error  :" . $e->getMessage() . "<br/>"; 
                                    }

                                    $RBJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter-1),
                                        "kodeorg='".$_SESSION['org']['kodeorganisasi'].
                                        "' and kodekelompok='".$kodeJurnal."'");
                                    try{
                                        $owlPDO->exec($RBJurnal); 
                                    }catch (PDOException $e) {
                                        echo "Rollback Update Jurnal counter Error :" . $e->getMessage() . "<br/>"; 
                                    }
                                    exit;
                                }                                
                            }catch (PDOException $e) {
                                echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                # Rollback if Update Failed
                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                 try{
                                        $owlPDO->exec($RBDet); 
                                    }catch (PDOException $e) {
                                        echo "Rollback Delete Header Error :" . $e->getMessage() . "<br/>"; 
                                    }                                
                                exit;
                            }
                        } else {
                            echo $detailErr;
                            # Rollback, Delete Header
                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            try{
                                $owlPDO->exec($RBDet); 
                            }catch (PDOException $e) {
                               echo "Rollback Delete Header Error : " . $e->getMessage() . "<br/>"; 
                               exit;
                            }
                        }
                    } else {
                        echo $headErr;
                        exit;
                    }
                   #proses data sisi pengguna====================================================
                    $kodeJurnal = 'INVK1';
                    #======================== Nomor Jurnal =============================
                    #ambil tanggal terkecil periode pengguna
                    $stri="select tanggalmulai from ".$dbname.".setup_periodeakuntansi
                           where kodeorg='".$pengguna."' and tutupbuku=0";
                    $tanggalsana='';
                    $resi=$owlPDO->query($stri) or die(print " Gagal: ".PDOException::getMessage());
                    $resi->setFetchMode(PDO::FETCH_OBJ);
                    while($bari=$resi->fetch())
                    {
                        $tanggalsana=$bari->tanggalmulai;
                    }
                    if($tanggalsana=='' or substr($tanggalsana,0,7)==substr($_POST['tanggal'],0,7))#jika periode sama maka biarkan
                        $tanggalsana=$_POST['tanggal'];
                    
                    # Get Journal Counter
                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."' ");
                    $tmpKonter = fetchData($queryJ);
                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                    # Transform No Jurnal dari No Transaksi
                    $nojurnal = str_replace("-","",$tanggalsana)."/".$pengguna."/".$kodeJurnal."/".$konter;
                    #======================== /Nomor Jurnal ============================
                    # Prep Header
                    unset($dataRes['header']);//ganti header    
                    $dataRes['header'] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>$kodeJurnal,
                            'tanggal'=>$tanggalsana,
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>$_POST['hartot'],
                            'totalkredit'=>-1*$_POST['hartot'],
                            'amountkoreksi'=>'0',
                            'noreferensi'=>$_POST['notransaksi'],
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                'revisi'=>'0'                        
                        );

                        # Data Detail
                         $keterangan="Pemakaian barang ".$namabarang." ".$_POST['jumlah']." ".$_POST['satuan']." ".substr($_POST['tanggal'],0,7)." ".$_POST['keterangan'];
                         $keterangan=substr($keterangan,0,150);
                        $noUrut = 1;
                        unset($dataRes['detail']);//ganti detail 
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$tanggalsana,
                            'nourut'=>$noUrut,
                            'noakun'=>$akunpekerjaan,
                            'keterangan'=>$keterangan,
                            'jumlah'=>$_POST['hartot'],
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$pengguna,
                            'kodekegiatan'=>$_POST['kodekegiatan'],
                            'kodeasset'=>'',
                            'kodebarang'=>$_POST['kodebarang'],
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$_POST['notransaksi'],
                            'noaruskas'=>'',
                            'kodevhc'=>$_POST['kodemesin'],
                            'nodok'=>'',
                            'kodeblok'=>$_POST['kodeblok'],
							'revisi'=>'0',
							'kodesegment'=>null
                        );
                        $noUrut++;

                        # Kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$tanggalsana,
                            'nourut'=>$noUrut,
                            'noakun'=>$akunspl,
                            'keterangan'=>$keterangan,
                            'jumlah'=>-1*$_POST['hartot'],
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$pengguna,
                            'kodekegiatan'=>$_POST['kodekegiatan'],
                            'kodeasset'=>'',
                            'kodebarang'=>$_POST['kodebarang'],
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$_POST['notransaksi'],
                            'noaruskas'=>'',
                            'kodevhc'=>$_POST['kodemesin'],
                            'nodok'=>'',
                            'kodeblok'=>$_POST['kodeblok'],
							'revisi'=>'0',
							'kodesegment'=>null
                        );
                        $noUrut++; 
                    #===========EXECUTE
                    $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                    try{
                        $owlPDO->exec($insHead); 
                    }catch (PDOException $e) {
                       $headErr .= "Insert Header Error : " . $e->getMessage() . "<br/>"; 
                    }

                    if($headErr=='') {
                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                        $detailErr = '';
                        foreach($dataRes['detail'] as $row) {
                            $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                            try{
                                $owlPDO->exec($insDet); 
                            }catch (PDOException $e) {
                                $detailErr .= "Insert Detail Error sisi pengguna: " . $e->getMessage() . "<br/>"; 
                                break;
                            }
                        }

                        if($detailErr=='') {
                            # Header and Detail inserted
                            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                            $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'");
                                try{
                                    $owlPDO->exec($updJurnal); 
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Jurnal di log_transaksiht
                                        $updTrans = updateQuery($dbname,'log_transaksiht',array('statusjurnal'=>1),
                                            "notransaksi='".$_POST['notransaksi']."'");
                                            try{
                                                $owlPDO->exec($updTrans); 
                                            }catch (PDOException $e) {
                                                echo "Update Status Jurnal Error : ".$e->getMessage()."\n";
                                                # Rollback if Update Failed
                                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                                try{
                                                    $owlPDO->exec($RBDet); 
                                                }catch (PDOException $e) {
                                                   echo "Rollback Delete Header Error : " . $e->getMessage() . "<br/>"; 
                                                }                                                

                                                $RBJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter-1),
                                                    "kodeorg='".$ptpengguna.
                                                    "' and kodekelompok='".$kodeJurnal."'");
                                                try{
                                                    $owlPDO->exec($RBJurnal); 
                                                }catch (PDOException $e) {
                                                   echo "Rollback Update Jurnal  Counter Error : " . $e->getMessage() . "<br/>"; 
                                                }  
                                                exit;                                                
                                            }
                                }catch (PDOException $e) {
                                    echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                    # Rollback if Update Failed
                                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                    try{
                                        $owlPDO->exec($RBDet); 
                                    }catch (PDOException $e) {
                                       echo "Rollback Delete Header Error : " . $e->getMessage() . "<br/>"; 
                                    }
                                    exit();
                                }

                        } else {
                            echo $detailErr;
                            # Rollback, Delete Header
                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            try{
                                $owlPDO->exec($RBDet); 
                            }catch (PDOException $e) {
                               echo "Rollback Delete Header Error : " . $e->getMessage() . "<br/>"; 
                            }
                            exit();
                        }
                    } else {
                        echo $headErr;
                        exit;
                    }   
        }//disini=====
    }      
 }  
else if($_POST['tipetransaksi']==7){
     #Pengeluaran mutasi================================================================================
    # periksa apakah dari satu PT
     $penerima=substr($_POST['gudangx'],0,4);
     
     $ptPenerima='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$penerima."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
         $ptPenerima=$bar->induk;
     }
     
     $ptGudang='';
     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($_POST['kodegudang'],0,4)."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
         $ptGudang=$bar->induk;
     }
     #jika pt tidak sama maka penerima akun interco
     $akunspl='';
     if($ptGudang !=$ptPenerima)
     {
         #ambil akun interco
        $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".$penerima."' and jenis='inter'";
        $akunspl='';
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $akunspl=$bar->akunpiutang;
        }

        if($akunspl==''){
            exit("Error: Akun intraco  atau interco belum ada untuk unit ".$penerima); 
        }
     }
     else
     {
        #ambil akun intraco
        $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".$penerima."' and jenis='intra'";
        $akunspl='';
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $akunspl=$bar->akunpiutang;
        }

        if($akunspl==''){
            exit("Error: Akun intraco  atau interco belum ada untuk unit ".$penerima); 
        }        
     }
#ambil noakun barang
    $klbarang=substr($_POST['kodebarang'],0,3);
    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
    $akunbarang='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $akunbarang=$bar->noakun;
    }   
    if($akunbarang=='' or $akunspl=='')
        exit("Error: Noakun barang atau intra/interco belum ada untuk transaksi ".$_POST['notransaksi']);
    else{
       #proses data
        $kodeJurnal = 'INVK1';
        #======================== Nomor Jurnal =============================
        # Get Journal Counter
        $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
            "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
        $tmpKonter = fetchData($queryJ);
        $konter = addZero($tmpKonter[0]['nokounter']+1,3);

        # Transform No Jurnal dari No Transaksi
        $nojurnal = str_replace("-","",$_POST['tanggal'])."/".substr($_POST['kodegudang'],0,4)."/".$kodeJurnal."/".$konter;
        #======================== /Nomor Jurnal ============================
        
        # Prep Header
            $dataRes['header'] = array(
                'nojurnal'=>$nojurnal,
                'kodejurnal'=>$kodeJurnal,
                'tanggal'=>$_POST['tanggal'],
                'tanggalentry'=>date('Ymd'),
                'posting'=>1,
                'totaldebet'=>$_POST['hartot'],
                'totalkredit'=>-1*$_POST['hartot'],
                'amountkoreksi'=>'0',
                'noreferensi'=>$_POST['notransaksi'],
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
                'tanggal'=>$_POST['tanggal'],
                'nourut'=>$noUrut,
                'noakun'=>$akunspl,
                'keterangan'=>'Mutasi barang '.$namabarang.' '.$_POST['jumlah']." ".$_POST['satuan'],
                'jumlah'=>$_POST['hartot'],
                'matauang'=>'IDR',
                'kurs'=>'1',
                'kodeorg'=>substr($_POST['kodegudang'],0,4),
                'kodekegiatan'=>'',
                'kodeasset'=>'',
                'kodebarang'=>$_POST['kodebarang'],
                'nik'=>'',
                'kodecustomer'=>'',
                'kodesupplier'=>$_POST['idsupplier'],
                'noreferensi'=>$_POST['notransaksi'],
                'noaruskas'=>'',
                'kodevhc'=>'',
                'nodok'=>$_POST['nopo'],
                'kodeblok'=>'',
                'revisi'=>'0',
				'kodesegment'=>null
            );
            $noUrut++;

            # Kredit
            $dataRes['detail'][] = array(
                'nojurnal'=>$nojurnal,
                'tanggal'=>$_POST['tanggal'],
                'nourut'=>$noUrut,
                'noakun'=>$akunbarang,
                'keterangan'=>'Mutasi barang '.$namabarang.' '.$_POST['jumlah']." ".$_POST['satuan'],
                'jumlah'=>-1*$_POST['hartot'],
                'matauang'=>'IDR',
                'kurs'=>'1',
                'kodeorg'=>substr($_POST['kodegudang'],0,4),
                'kodekegiatan'=>'',
                'kodeasset'=>'',
                'kodebarang'=>$_POST['kodebarang'],
                'nik'=>'',
                'kodecustomer'=>'',
                'kodesupplier'=>$_POST['idsupplier'],
                'noreferensi'=>$_POST['notransaksi'],
                'noaruskas'=>'',
                'kodevhc'=>'',
                'nodok'=>$_POST['nopo'],
                'kodeblok'=>'',
                'revisi'=>'0',
				'kodesegment'=>null
            );
            $noUrut++;      
    }
             #===========EXECUTE
            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            try{
                $owlPDO->exec($insHead); 
            }catch (PDOException $e) {
               $headErr .= "Insert Header Error : " . $e->getMessage() . "<br/>"; 
            }

            if($headErr=='') {
                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                    try{
                        $owlPDO->exec($insDet); 
                    }catch (PDOException $e) {
                       $detailErr .= "Insert Detail Error : " . $e->getMessage() . "<br/>"; 
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
                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Jurnal di log_transaksiht
                        $updTrans = updateQuery($dbname,'log_transaksiht',array('statusjurnal'=>1),
                            "notransaksi='".$_POST['notransaksi']."'");
                        try{
                            $owlPDO->exec( $updTrans); 
                        }catch (PDOException $e) {
                            echo "Update Status Jurnal Error : ".$e->getMessage()."\n";
                            # Rollback if Update Failed
                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            try{
                                $owlPDO->exec($RBDet); 
                            }catch (PDOException $e) {
                               echo "Rollback Delete Header Error :  " . $e->getMessage() . "<br/>"; 
                            }

                            $RBJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter-1),
                                "kodeorg='".$_SESSION['org']['kodeorganisasi'].
                                "' and kodekelompok='".$kodeJurnal."'");
                            try{
                                $owlPDO->exec($RBJurnal); 
                            }catch (PDOException $e) {
                               echo "Rollback Update Jurnal counter error :  " . $e->getMessage() . "<br/>"; 
                            }
                            exit;
                        }                      
                    }catch (PDOException $e) {
                        echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                        # Rollback if Update Failed
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        try{
                            $owlPDO->exec($RBDet); 
                        }catch (PDOException $e) {
                           echo "Rollback Delete Header Error :  " . $e->getMessage() . "<br/>"; 
                        }
                        exit;
                    }
                } else {
                    echo $detailErr;
                    # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                        try{
                            $owlPDO->exec($RBDet); 
                        }catch (PDOException $e) {
                           echo "Rollback Delete Header Error :  " . $e->getMessage() . "<br/>"; 
                        }
                    exit;
                }
            } else {
                echo $headErr;
                exit;
            }     
 }
else{//selain itu lewatkan saja
      $updTrans = updateQuery($dbname,'log_transaksiht',array('statusjurnal'=>1),
                        "notransaksi='".$_POST['notransaksi']."'");
        try{
            $owlPDO->exec($updTrans); 
        }catch (PDOException $e) {
           echo "Update Status Jurnal Error : " . $e->getMessage() . "<br/>"; 
        }      
}                        
?>