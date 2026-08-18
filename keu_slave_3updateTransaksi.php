<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
include_once('lib/zJournal.php');

$param = $_POST;
$kodeorg  = $param['kodeorg'];
$dataorg = array();
$dtstr="select * from ".$dbname.".organisasi where  kodeorganisasi = '".$kodeorg."'";
$str=$owlPDO->query($dtstr);
$str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
  $dataorg[$bar->kodeorganisasi] = $bar;
 }

        $sPrdGdng="select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' and kodeorganisasi like '".$_POST['kodeorg']."%') and tutupbuku=0";
        $qPrdGdng=$owlPDO->query($sPrdGdng) or die(print " Gagal: ".PDOException::getMessage());
        $qPrdGdng->setFetchMode(PDO::FETCH_ASSOC);
        $rPrdGdng=$qPrdGdng->fetch();
        if($rPrdGdng['periode']!=$_POST['periode']){
                // exit("warning: Gudang Sudah Berhasil Tutup Buku Periode ".$_POST['periode']);
        }
        // 1. Get Range Periode Akuntansi
        $sDt="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi 
          where kodeorg='".$_POST['kodeorg']."' and periode='".$_POST['periode']."'";
        $qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
        $qDt->setFetchMode(PDO::FETCH_ASSOC);
        $rDt=$qDt->fetch();
        $wrtgl=" and tanggal between '".$rDt['tanggalmulai']."' and '".$rDt['tanggalsampai']."'";
        $nobkm=0;
        ##cek disini coi untuk transaksi BKM
        $iBkm="select * from ".$dbname.".kebun_pakai_material_vw where jurnal=0 ".$wrtgl." and left(kodegudang,4)='".$_POST['kodeorg']."' ";
        $nBkm=$owlPDO->query($iBkm) or die(print " Gagal: ".PDOException::getMessage());
        $nBkm->setFetchMode(PDO::FETCH_ASSOC);
        while($dBkm=  $nBkm->fetch())
        {
            $nobkm++;
            $adabkm.=$dBkm['notransaksi']."____";
        }


        if($nobkm>0){
            exit("Error:Ada transaksi bkm yang memakai material belum terposting ".$adabkm." ");
        }
        if(@$dataorg[$kodeorg]->tipe=='KEBUN'){
          #pengecekan apakah user sudah melakukan intergrity cek atau belum
          $sCek="select count(kodebarang) as jmlBrg from ".$dbname.".kebun_pakai_material_vw where 
                 tanggal between '".$rDt['tanggalmulai']."' and '".$rDt['tanggalsampai']."' and kodegudang like '".$_POST['kodeorg']."%' and jurnal=1 ";
          $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
          $qCek->setFetchMode(PDO::FETCH_ASSOC);
          $rCek=$qCek->fetch();

          $sCek2="select count(kodebarang) as jmlBrg from ".$dbname.".log_transaksi_vw where 
                 tanggal between '".$rDt['tanggalmulai']."' and '".$rDt['tanggalsampai']."' and kodegudang like '".$_POST['kodeorg']."%'
                 and notransaksireferensi!='' and tipetransaksi=5";
          $qCek2=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
          $qCek2->setFetchMode(PDO::FETCH_ASSOC);
          $rCek2=$qCek2->fetch();
          if($rCek['jmlBrg']!=$rCek2['jmlBrg']){
            exit("warning: Silakan jalankan Proses pada menu Pengadaan>Proses>Intergrity Check BKM");
          }
        }

        $zJ = new zJournal();

        /***************************************************************************
         ** PROSES INTEGRITY CHECK TRANSAKSI GUDANG ********************************
         ***************************************************************************/
        //exit("Notice: Mohon maaf. Masih dalam tahap debugging.");



        // Build Query Condition

    $whrd=" and left(kodebarang,3) in (select kode from ".$dbname.".log_5klbarang where left(noakun,3)='115') ";
        $lstDataTrns=array();
        $ader=0;

        // 2. Get Transaksi Gudang yang sudah di posting
        $sData="select * from ".$dbname.".log_transaksi_vw where kodegudang like '".$_POST['kodeorg']."%' "
                        . "and (kodebarang<>'' and left(kodebarang,1)!=8)
                        and (notransaksireferensi is null or 
                        (notransaksireferensi not like '%/TB/%'
                        and notransaksireferensi not like '%/TBM/%'
                        and notransaksireferensi not like '%/TM/%'
                        and notransaksireferensi not like '%/BBT/%'))
                        ".$whrd." and post='1' ".$wrtgl." 
                        order by notransaksi,kodebarang asc";
        $resGudang = fetchData($sData);
        foreach($resGudang as $rData) {


                if($rData['tipetransaksi']>4) {
                        if($rData['tipetransaksi']==7) {
                                if(substr($rData['kodegudang'],0,4)==substr($rData['gudangx'],0,4)){
                                        continue;
                                }
                        }
                }else{
                        if($rData['tipetransaksi']==2){
                                $rData['kodemesin']="";
                        }
                        if($rData['tipetransaksi']==3){
                                if(substr($rData['kodegudang'],0,4)==substr($rData['gudangx'],0,4)){
                                        continue;
                                }
                        }
                }

                if($rData['tipetransaksi']=='2'){
                        $rData['hargasatuan']=$rData['hargarata'];
                }
                $str="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rData['kodebarang']."'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                $bar=$res->fetch();
                $lstDataTrns[]=$rData;//SUMBER DARI TRANSAKSI
                if($rData['kodemesin']=="") {
                        $tipetransaksi[$rData['notransaksi'].$rData['kodebarang']]=$rData['tipetransaksi'];
                        $tanggal[$rData['notransaksi'].$rData['kodebarang']]    =tanggalnormal($rData['tanggal']);
                        $kodebarang[$rData['notransaksi'].$rData['kodebarang']] =$rData['kodebarang'];
                        $namabarang[$rData['notransaksi'].$rData['kodebarang']] =$bar->namabarang;
                        $satuan[$rData['notransaksi'].$rData['kodebarang']] =$rData['satuan'];
                        $jumlah[$rData['notransaksi'].$rData['kodebarang']] =$rData['jumlah'];
                        $kodept[$rData['notransaksi'].$rData['kodebarang']] =$rData['kodept'];
                        $nilaitotal[$rData['notransaksi'].$rData['kodebarang']] =$rData['jumlah']*$rData['hargasatuan'];
                        if($nilaitotal[$rData['notransaksi'].$rData['kodebarang']]=='0')
                           $nilaitotal[$rData['notransaksi'].$rData['kodebarang']]=$rData['jumlah']*$rData['hargarata'];

                        $nilaitotalKlr[$rData['notransaksi'].$rData['kodebarang']] =$rData['jumlah']*$rData['hargarata'];
                        $gudangx[$rData['notransaksi'].$rData['kodebarang']]    =$rData['gudangx'];
                        $untukpt[$rData['notransaksi'].$rData['kodebarang']]    =$rData['untukpt'];
                        $gudang[$rData['notransaksi'].$rData['kodebarang']] =$rData['kodegudang'];
                        $blok[$rData['notransaksi'].$rData['kodebarang']]       =$rData['kodeblok'];
                        $notransaksi[$rData['notransaksi'].$rData['kodebarang']]=$rData['notransaksi'];
                        $user[$rData['notransaksi'].$rData['kodebarang']]   =$_SESSION['standard']['userid'];
                        $hargasatuan[$rData['notransaksi'].$rData['kodebarang']]=$rData['hargasatuan'];
                        $nopo[$rData['notransaksi'].$rData['kodebarang']]   =$rData['nopo'];
                        $supplier[$rData['notransaksi'].$rData['kodebarang']]   =$rData['idsupplier'];
                        $kodekegiatan[$rData['notransaksi'].$rData['kodebarang']]   =$rData['kodekegiatan'];
                        $namapenerima[$rData['notransaksi'].$rData['kodebarang']]   =isset($rData['namapenerima'])? $rData['namapenerima']: ''; 
                        $untukunit[$rData['notransaksi'].$rData['kodebarang']]=$rData['untukunit']; 
                        $hargarata[$rData['notransaksi'].$rData['kodebarang']]=$rData['hargarata'];
                        $lsJrnlKsng[$rData['notransaksi'].$rData['kodebarang']]=$rData['notransaksi'];//sumber dari transaksi
                        $kodemesin[$rData['notransaksi'].$rData['kodebarang']]=$rData['kodemesin'];
                        $notransaksireferensi[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=$rData['notransaksireferensi'];
                }else{
                        $tipetransaksi[$rData['notransaksi'].$rData['kodebarang']]=$rData['tipetransaksi'];
                        $tanggal[$rData['notransaksi'].$rData['kodebarang']]    =tanggalnormal($rData['tanggal']);
                        $kodebarang[$rData['notransaksi'].$rData['kodebarang']] =$rData['kodebarang'];
                        $namabarang[$rData['notransaksi'].$rData['kodebarang']] =$bar->namabarang;
                        $satuan[$rData['notransaksi'].$rData['kodebarang']] =$rData['satuan'];
                        $jumlah[$rData['notransaksi'].$rData['kodebarang']] =$rData['jumlah'];
                        $kodept[$rData['notransaksi'].$rData['kodebarang']] =$rData['kodept'];
                        $nilaitotal[$rData['notransaksi'].$rData['kodebarang']] =$rData['jumlah']*$rData['hargasatuan'];
                        if($nilaitotal[$rData['notransaksi'].$rData['kodebarang']]=='0')
                           $nilaitotal[$rData['notransaksi'].$rData['kodebarang']]=$rData['jumlah']*$rData['hargarata'];

                        $nilaitotalKlr[$rData['notransaksi'].$rData['kodebarang']] =$rData['jumlah']*$rData['hargarata'];
                        $gudangx[$rData['notransaksi'].$rData['kodebarang']]    =$rData['gudangx'];
                        $untukpt[$rData['notransaksi'].$rData['kodebarang']]    =$rData['untukpt'];
                        $gudang[$rData['notransaksi'].$rData['kodebarang']] =$rData['kodegudang'];
                        $blok[$rData['notransaksi'].$rData['kodebarang']]       =$rData['kodeblok'];
                        $notransaksi[$rData['notransaksi'].$rData['kodebarang']]=$rData['notransaksi'];
                        $user[$rData['notransaksi'].$rData['kodebarang']]   =$_SESSION['standard']['userid'];
                        $hargasatuan[$rData['notransaksi'].$rData['kodebarang']]=$rData['hargasatuan'];
                        $nopo[$rData['notransaksi'].$rData['kodebarang']]   =$rData['nopo'];
                        $supplier[$rData['notransaksi'].$rData['kodebarang']]   =$rData['idsupplier'];
                        $kodekegiatan[$rData['notransaksi'].$rData['kodebarang']]   =$rData['kodekegiatan'];
                        $namapenerima[$rData['notransaksi'].$rData['kodebarang']]   =isset($rData['namapenerima'])? $rData['namapenerima']: '';
                        $untukunit[$rData['notransaksi'].$rData['kodebarang']]=$rData['untukunit']; 
                        $hargarata[$rData['notransaksi'].$rData['kodebarang']]=$rData['hargarata'];
                        $lsJrnlKsng3[$rData['notransaksi'].$rData['kodebarang']]=$rData['notransaksi'];
                        //$tipetransaksi[$rData['notransaksi'].$rData['kodebarang']]=$rData['tipetransaksi'];
                        $kodemesin[$rData['notransaksi'].$rData['kodebarang']]=$rData['kodemesin'];

                        $tanggal[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]    =tanggalnormal($rData['tanggal']);
                        $kodebarang[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']] =$rData['kodebarang'];
                        $namabarang[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']] =$bar->namabarang;
                        $satuan[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']] =$rData['satuan'];
                        $jumlah[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']] =$rData['jumlah'];
                        $kodept[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']] =$rData['kodept'];
                        $nilaitotal[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']] =$rData['jumlah']*$rData['hargasatuan'];
                        if($nilaitotal[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=='0')
                                $nilaitotal[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=$rData['jumlah']*$rData['hargarata'];

                         $nilaitotalKlr[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']] =$rData['jumlah']*$rData['hargarata'];
                        $gudangx[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]    =$rData['gudangx'];
                        $untukpt[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]    =$rData['untukpt'];
                        $gudang[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']] =$rData['kodegudang'];
                        $blok[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]       =$rData['kodeblok'];
                        $notransaksi[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=$rData['notransaksi'];
                        $user[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]   =$_SESSION['standard']['userid'];
                        $hargasatuan[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=$rData['hargasatuan'];
                        $nopo[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]   =$rData['nopo'];
                        $supplier[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]   =$rData['idsupplier'];
                        $kodekegiatan[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]   =$rData['kodekegiatan'];
                        $namapenerima[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]   =isset($rData['namapenerima'])? $rData['namapenerima']: '';
                        $untukunit[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=$rData['untukunit']; 
                        $hargarata[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=$rData['hargarata'];
                        $lsJrnlKsng2[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=$rData['notransaksi'];
                        $tipetransaksi[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=$rData['tipetransaksi'];
                        $kodemesin[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=$rData['kodemesin'];
                        $notransaksireferensi[$rData['notransaksi'].$rData['kodebarang'].$rData['kodemesin']]=$rData['notransaksireferensi'];
                        
                }
        }

        // 3. Get Jurnal Barang, list
        $sJurnal="select distinct noreferensi,kodebarang,nojurnal,kodevhc from ".$dbname.".keu_jurnaldt_vw 
                          where right(noreferensi,6) like '".$_POST['kodeorg']."%'
                          and (noreferensi like '%-GI-%' or noreferensi  like '%-GR-%')
                          ".$whrd." ".$wrtgl." 
                          and (kodebarang<>'' and left(kodebarang,1)!=8) and nojurnal not like '%/M/%' order by noreferensi,kodebarang asc";
        $qJurnal=$owlPDO->query($sJurnal) or die(print " Gagal: ".PDOException::getMessage());
        $qJurnal->setFetchMode(PDO::FETCH_ASSOC);
        while($rJurnal=$qJurnal->fetch()){
                $rJurnal['kodevhc']=trim($rJurnal['kodevhc']);
                $lstDataJrn[]=$rJurnal;
                if($rJurnal['kodevhc']!=''){
                        $lsTransKsng2[$rJurnal['noreferensi'].$rJurnal['kodebarang'].$rJurnal['kodevhc']]=$rJurnal['noreferensi'];
                        $dtJrnTransKsng2[$rJurnal['noreferensi'].$rJurnal['kodebarang'].$rJurnal['kodevhc']]=$rJurnal['nojurnal'];
                }else{

                        $lsTransKsng[$rJurnal['noreferensi'].$rJurnal['kodebarang']]=$rJurnal['noreferensi'];
                        $dtJrnTransKsng[$rJurnal['noreferensi'].$rJurnal['kodebarang']]=$rJurnal['nojurnal'];
                }
        }

        // 4. Hapus semua jurnal kosong (tidak ada transaksinya)
    if(!empty($lsTransKsng)){
        foreach($lsTransKsng as $key =>$val){
            if(isset($lsJrnlKsng[$key])){
                continue;
            }
            else{
                if(isset($lsJrnlKsng3[$key])){
                    continue;
                }
                else{
                    $sdel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$dtJrnTransKsng[$key]."'";
                    try{$owlPDO->exec($sdel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
                }
            }
        }
    }

        if(!empty($lsTransKsng2)){
                foreach($lsTransKsng2 as $key =>$val){
                        if(isset($lsJrnlKsng2[$key]))
                        {
                                continue;
                        }
                        else{
                                $sdel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$dtJrnTransKsng2[$key]."'";
                                try{$owlPDO->exec($sdel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
                        }
                }
        }

        // 5. Get transaksi gudang yng belum terbentuk jurnal
        $pt = '';
        $konterPt = array();
    $awal = 0;
        if(!empty($lsJrnlKsng)){
        foreach($lsJrnlKsng as $key =>$val) {
                        if($awal==0) {
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                        "kodeorg='".$kodept[$key]."' and kodekelompok='INVM1' ");
                                $tmpKonter = fetchData($queryJ);
                                $invmCurr = $nilAwlMsk = $tmpKonter[0]['nokounter'];
                                //$konter = addZero($tmpKonter[0]['nokounter']+1,3);
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                        "kodeorg='".$kodept[$key]."' and kodekelompok='INVK1' ");
                                $tmpKonter = fetchData($queryJ);
                                $invkCurr = $nilAwlKlr = $tmpKonter[0]['nokounter'];
                                $pt = $kodept[$key];
                                $awal = 1;
                        }
                        if(isset($lsTransKsng[$key])) {
                continue;
            } else {    
                                switch ($tipetransaksi[$key]) {
                                        /** Tipe Transaksi 1 **************************************/
                    case'1':
                        #ambil noakun supplier
                        $kodekl=substr($supplier[$key],0,4);
                        $str="select noakun from ".$dbname.".log_5klsupplier where kode='".$kodekl."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $akunspl='';
                        $bar=$res->fetch();
                        $akunspl=$bar->noakun;
                        #ambil noakun barang
                        $klbarang=substr($kodebarang[$key],0,3);
                        $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $akunbarang='';
                        $bar=$res->fetch();
                        $akunbarang=$bar->noakun;
                        if(($akunbarang=='' or $akunspl=='') and (intval($klbarang)<'400' or substr($kodebarang[$key],0,1)=='9')){    
                            continue;
                        }
                        #proses data
                        $kodeJurnal = 'INVM1';
                        $nilAwlMsk++;
                        $konter = addZero($nilAwlMsk,3);

                                                # Transform No Jurnal dari No Transaksi
                        $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                        #======================== /Nomor Jurnal ============================
                        if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){ 
                            # Prep Header
                            $dataRes['header'][] = array(
                                'nojurnal'=>$nojurnal,
                                'kodejurnal'=>$kodeJurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'tanggalentry'=>date('Ymd'),
                                'posting'=>1,
                                'totaldebet'=>$nilaitotal[$key],
                                'totalkredit'=>-1*$nilaitotal[$key],
                                'amountkoreksi'=>'0',
                                'noreferensi'=>$notransaksi[$key],
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
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunbarang,
                                'keterangan'=>'Pembelian barang '.$namabarang[$key].' '.$jumlah[$key]." ".$satuan[$key],
                                'jumlah'=>$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($gudang[$key],0,4),
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>$supplier[$key],
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nopo[$key],
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>''
                            );
                            $noUrut++;

                            # Kredit
                            $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunspl,
                                'keterangan'=>'Pembelian barang '.$namabarang[$key].' '.$jumlah[$key]." ".$satuan[$key],
                                'jumlah'=>-1*$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($gudang[$key],0,4),
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>$supplier[$key],
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nopo[$key],
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>''                
                            );
                            $noUrut++;  
                        }
                                                break;

                                        /** Tipe Transaksi 6 **************************************/
                    case'6':
                         if(intval($hargasatuan[$key])==0 or $nopo=='' or $supplier==''){
                            continue;
                            //exit(" Error: ".$_SESSION['lang']['priceposuppnotfound']);
                        }
                        #ambil noakun supplier
                        $kodekl=substr($supplier[$key],0,4);
                        $str="select noakun from ".$dbname.".log_5klsupplier where kode='".$kodekl."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $akunspl='';
                        $bar=$res->fetch();
                        $akunspl=$bar->noakun;
                        #ambil noakun barang
                        $klbarang=substr($kodebarang[$key],0,3);
                        $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $akunbarang='';
                        $bar=$res->fetch();
                        $akunbarang=$bar->noakun;

                        if(($akunbarang=='' or $akunspl=='') and ($klbarang<'400' or substr($kodebarang[$key],0,1)=='9')){    
                            continue;
                        }

                        #proses data
                        $kodeJurnal = 'INVK1';
                        $nilAwlKlr++;
                        $konter = addZero($nilAwlKlr,3);

                        # Transform No Jurnal dari No Transaksi
                        $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                        #======================== /Nomor Jurnal ============================
                        # Prep Header
                        if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){        
                            $dataRes['header'][] = array(
                                'nojurnal'=>$nojurnal,
                                'kodejurnal'=>$kodeJurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'tanggalentry'=>date('Ymd'),
                                'posting'=>1,
                                'totaldebet'=>$nilaitotal[$key],
                                'totalkredit'=>-1*$nilaitotal[$key],
                                'amountkoreksi'=>'0',
                                'noreferensi'=>$notransaksi[$key],
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
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunspl,
                                'keterangan'=>'ReturSupplier '.$namabarang[$key].' '.$jumlah[$key]." ".$satuan[$key],
                                'jumlah'=>$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($gudang[$key],0,4),
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>$supplier[$key],
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nopo[$key],
                                'kodeblok'=>'',
                                'revisi'=>'0' ,
                                'kodesegment'=>''               
                            );
                            $noUrut++;

                            # Kredit
                            $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunbarang,
                                'keterangan'=>'ReturSupplier '.$namabarang[$key].' '.$jumlah[$key]." ".$satuan[$key],
                                'jumlah'=>-1*$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($gudang[$key],0,4),
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>$supplier[$key],
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nopo[$key],
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>''                
                            );
                            $noUrut++;      
                            #============================
                        }
                                                break;

                                        /** Tipe Transaksi 2 **************************************/
                    case'2':
                            #ambil harga satuan dan saldo
                            $pengguna=substr($untukunit[$key],0,4);
                            $ptpengguna='';
                            $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $ptpengguna=$bar->induk;
                            $str="select akunhutang,jenis from ".$dbname.".keu_5caco where 
                                              kodeorg='".$pengguna."'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $intraco='';
                            $interco='';
                            if($bar->jenis=='intra')
                                    $intraco=$bar->akunhutang;
                            else
                                    $interco=$bar->akunhutang; 
                            $ptGudang='';
                            $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang[$key],0,4)."'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $ptGudang=$bar->induk;
                            #jika pt tidak sama maka pakai akun interco
                            $akunspl='';
                            if($ptGudang !=$ptpengguna) {
                                    # INTERCO
                            $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='inter'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $akunspl='';
                            $akunspl=$bar->akunpiutang;
                            $inter=$interco;   
                                if($akunspl==''){
                                    continue;
                                }
                            } else if($pengguna!=substr($gudang[$key],0,4)) { #jika satu pt beda kebun
                                                        # INTRACO
                            $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='intra'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $akunspl='';
                            $akunspl=$bar->akunpiutang;
                            $inter=$intraco;  
                                    if($akunspl==''){
                                       continue;
                                    }
                            }

                    #ambil akun pekerjaan atau kendaraan atau ab
                    #periksa ke table setup blok
                    $statustm='';
                    $str="select statusblok from ".$dbname.".setup_blok where kodeorg='".$blok[$key]."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();
                    $statustm=$bar->statusblok;
                    $str="select noakun from ".$dbname.".setup_kegiatan where  kodekegiatan='".$kodekegiatan[$key]."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();                    
                    $akunpekerjaan='';
                    $kodeasset='';
                    $akunpekerjaan=$bar->noakun;
                    #jika akun kegiatan tidak ada maka exit
                    if($akunpekerjaan=='') {
                            // Cek akun untuk yang Project
                            $str="select noakun from ".$dbname.".keu_5akun where noakun='".$kodekegiatan[$key]."'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);                          
                            while($bar=$res->fetch())
                            {
                                    $akunpekerjaan=$bar->noakun;
                            }
                            if ($akunpekerjaan=='') {

                            } else {
                                    $kodeasset=$blok[$key];
                            }
                    }

                    #ambil noakun barang
                    $klbarang=substr($kodebarang[$key],0,3);
                    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                    $akunbarang='';
                    $akunbarang=$bar->noakun;
                    if($akunbarang==''){
                            continue;
                    }else{
    if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){
    if($pengguna==substr($gudang[$key],0,4)) {
                                            $kodeJurnal = 'INVM1';
                                            #======================== Nomor Jurnal =============================
                                            # Get Journal Counter
                                            $nilAwlMsk++;
                                            $konter = addZero($nilAwlMsk,3);

                                            # Transform No Jurnal dari No Transaksi
                                            $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                                            #======================== /Nomor Jurnal ============================
                                            # Prep Header
                                            $dataRes['header'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'kodejurnal'=>$kodeJurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'tanggalentry'=>date('Ymd'),
                                                    'posting'=>1,
                                                    'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                    'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                    'amountkoreksi'=>'0',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'autojurnal'=>'1',
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'revisi'=>'0'                            
                                            );

                                            # Data Detail
                                            $noUrut = 1;
                                            $keterangan="ReturGudang barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key];
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$akunbarang,
                                                    'keterangan'=> $keterangan,
                                                    'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>substr($gudang[$key],0,4),
                                                    'kodekegiatan'=>'',
                                                    'kodeasset'=>'',
                                                    'kodebarang'=>$kodebarang[$key],
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>$kodemesin[$key],
                                                    'nodok'=>'',
                                                    'kodeblok'=>$blok[$key],
                                                    'revisi'=>'0',
                                                    'kodesegment'=>''                            
                                            );
                                            $noUrut++;

                                            # Kredit
                                            $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$akunpekerjaan,
                                                    'keterangan'=>$keterangan,
                                                    'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>substr($gudang[$key],0,4),
                                                    'kodekegiatan'=>$kodekegiatan[$key],
                                                    'kodeasset'=>$kodeasset,
                                                    'kodebarang'=>$kodebarang[$key],
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>$kodemesin[$key],
                                                    'nodok'=>'',
                                                    'kodeblok'=>$blok[$key],
                                                     'revisi'=>'0',
                                                    'kodesegment'=>''                            
                                            );
                                            $noUrut++; 
                                            #========================================= 
                                    } else {
                                            #jika inter atau intraco 
        #proses data sisi pemilik====================================================
                                            $kodeJurnal = 'INVM1';
                                            #======================== Nomor Jurnal =============================
                                            # Get Journal Counter
                                            $nilAwlMsk++;
                                            $konter = addZero($nilAwlMsk,3);

                                            # Transform No Jurnal dari No Transaksi
                                            $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                                            #======================== /Nomor Jurnal ============================
                                            $header1pemilik=$nojurnal;   //no header pemilik    
                                            # Prep Header
                                            $dataRes['header'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'kodejurnal'=>$kodeJurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'tanggalentry'=>date('Ymd'),
                                                    'posting'=>1,
                                                    'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                    'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                    'amountkoreksi'=>'0',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'autojurnal'=>'1',
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'revisi'=>'0'                            
                                            );

                                            # Data Detail
                                            $noUrut = 1;
                                            $keterangan="ReturGudang barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key];
                                            $keterangan=substr($keterangan,0,150);
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$akunbarang,
                                                    'keterangan'=>$keterangan,
                                                    'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>substr($gudang[$key],0,4),
                                                    'kodekegiatan'=>'',
                                                    'kodeasset'=>'',
                                                    'kodebarang'=>$kodebarang[$key],
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>$kodemesin[$key],
                                                    'nodok'=>'',
                                                    'kodeblok'=>'',
                                                    'revisi'=>'0',
                                                    'kodesegment'=>''                            
                                            );
                                            $noUrut++;

                                            # Kredit
                                            $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$inter,
                                                    'keterangan'=>$keterangan,
                                                    'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>substr($gudang[$key],0,4),
                                                    'kodekegiatan'=>'',
                                                    'kodeasset'=>'',
                                                    'kodebarang'=>$kodebarang[$key],
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>$kodemesin[$key],
                                                    'nodok'=>'',
                                                    'kodeblok'=>'',
                                                     'revisi'=>'0',
                                                    'kodesegment'=>''                            
                                            );

                                            #proses data sisi pengguna====================================================
                                            $kodeJurnal = 'INVM1';
                                            #======================== Nomor Jurnal =============================
                                            # Get Journal Counter
                                            $nilAwlMsk++;
                                            $konter = addZero($nilAwlMsk,3);
                                            $tanggalsana=tanggalsystem($tanggal[$key]);
                                            # Transform No Jurnal dari No Transaksi
                                            $nojurnal = str_replace("-","",$tanggalsana)."/".$pengguna."/".$kodeJurnal."/".$konter;
                                            #======================== /Nomor Jurnal ============================
                                            # Prep Header
        $dataRes['header'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'kodejurnal'=>$kodeJurnal,
                                                    'tanggal'=>$tanggalsana,
                                                    'tanggalentry'=>date('Ymd'),
                                                    'posting'=>1,
                                                    'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                    'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                    'amountkoreksi'=>'0',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'autojurnal'=>'1',
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'revisi'=>'0'                        
                                            );

                                            # Data Detail
                                            $keterangan="ReturGudang barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]." ".substr($tanggal[$key],0,7)."__by system";
                                            $keterangan=substr($keterangan,0,150);
                                            $noUrut = 1;
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>$tanggalsana,
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$akunspl,
                                                    'keterangan'=>$keterangan,
                                                    'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>$pengguna,
                                                    'kodekegiatan'=>'',
                                                    'kodeasset'=>'',
                                                    'kodebarang'=>$kodebarang[$key],
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>$kodemesin[$key],
                                                    'nodok'=>'',
                                                    'kodeblok'=>$blok[$key],
                                                    'revisi'=>'0',
                                                     'kodesegment'=>''                            
                                            );
                                            $noUrut++;

                                            # Kredit
                                            $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>$tanggalsana,
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$akunpekerjaan,
                                                    'keterangan'=>$keterangan,
                                                    'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>$pengguna,
                                                    'kodekegiatan'=>$kodekegiatan[$key],
                                                    'kodeasset'=>'',
                                                    'kodebarang'=>$kodebarang[$key],
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>$kodemesin[$key],
                                                    'nodok'=>'',
                                                    'kodeblok'=>$blok[$key],
                                                    'revisi'=>'0',
                                                    'kodesegment'=>''                       
                                            );
                                            $noUrut++; 
        #===========EXECUTE
                                    }
                            }
                    }
                    break;

            /** Tipe Transaksi 3 ***************************************/
    case'3':
                    #=======================================================
                    #periksa apakah dari satu PT
                    $pengguna=substr($gudang[$key],0,4);//ini sebenarnya pemilik
                    $ptpengguna='';
                    $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";//ini sebenarnya pemilik
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                    $ptpengguna=$bar->induk;
                    $ptGudang='';
                    $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudangx[$key],0,4)."'";//ini yang pengguna
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                    $ptGudang=$bar->induk;
                    #jika pt tidak sama maka pakai akun interco
                    $akunspl='';
                    if($ptGudang !=$ptpengguna){
                            #ambil akun interco
                            $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudangx[$key],0,4)."' and jenis='inter'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();   
                            $akunspl='';
                            $akunspl=$bar->akunpiutang;  
                            if($akunspl==''){
                                    continue;
                            }
                    } else if($pengguna!=substr($gudangx[$key],0,4)){ #jika satu pt beda kebun
                            #ambil akun intraco
                            $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudangx[$key],0,4)."' and jenis='intra'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();   
                            $akunspl='';
                            $akunspl=$bar->akunpiutang;
                            if($akunspl==''){
                                    continue;
                            }
                    }
                    #ambil noakun barang
                    $klbarang=substr($kodebarang[$key],0,3);
                    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                    $akunbarang='';
                    $akunbarang=$bar->noakun;
                    if($akunbarang==''){
                            continue;
                    }else{
                            #proses data sisi pengguna====================================================
                            $kodeJurnal = 'INVM1';
                            $nilAwlMsk++;
                            $konter = addZero($nilAwlMsk,3);
                            # Transform No Jurnal dari No Transaksi
                            $nojurnal = tanggalsystem($tanggal[$key])."/".$pengguna."/".$kodeJurnal."/".$konter;
                            if((substr($kodebarang[$key],0,3)<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!='' and (substr($pengguna,0,4)!=substr($gudangx[$key],0,4))){  #hanya barang stok yang dijurnal      dan mutasi keluar kebun
                                    $dataRes['header'][] = array(
                                            'nojurnal'=>$nojurnal,
                                            'kodejurnal'=>$kodeJurnal,
                                            'tanggal'=>tanggalsystem($tanggal[$key]),
                                            'tanggalentry'=>date('Ymd'),
                                            'posting'=>1,
                                            'totaldebet'=>$nilaitotal[$key],
                                            'totalkredit'=>(-1*$nilaitotal[$key]),
                                            'amountkoreksi'=>'0',
                                            'noreferensi'=>$notransaksi[$key],
                                            'autojurnal'=>'1',
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'revisi'=>'0'
                                    );

                                    # Data Detail
                                    $keterangan="Terima Mutasi barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]." ".substr($tanggal[$key],0,7)."___ by system";
                                    $keterangan=substr($keterangan,0,150);
                                    $noUrut = 1;
                                    # Debet
                                    $dataRes['detail'][] = array(
                                            'nojurnal'=>$nojurnal,
                                            'tanggal'=>tanggalsystem($tanggal[$key]),
                                            'nourut'=>$noUrut,
                                            'noakun'=>$akunbarang,
                                            'keterangan'=>$keterangan,
                                            'jumlah'=>$nilaitotal[$key],
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'kodeorg'=>$pengguna,
                                            'kodekegiatan'=>'',
                                            'kodeasset'=>'',
                                            'kodebarang'=>$kodebarang[$key],
                                            'nik'=>'',
                                            'kodecustomer'=>'',
                                            'kodesupplier'=>'',
                                            'noreferensi'=>$notransaksi[$key],
                                            'noaruskas'=>'',
                                            'kodevhc'=>'',
                                            'nodok'=>'',
                                            'kodeblok'=>'',
                                            'revisi'=>'0',
                                            'kodesegment'=>''
                                    );
                                    $noUrut++;

                                    # Kredit
                                    $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunspl,
                                    'keterangan'=>$keterangan,
                                    'jumlah'=>(-1*$nilaitotal[$key]),
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>$pengguna,
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>$kodebarang[$key],
                                    'nik'=>'',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>$notransaksi[$key],
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>'',
                                    'kodeblok'=>'',
                                    'revisi'=>'0',
                                    'kodesegment'=>''                       
                                    );
                                    $noUrut++; 
                            }
                    }   
                    break;

                            /** Tipe Transaksi 7 ***************************************/
        case'7':
            #periksa apakah dari satu PT
            $pengguna=substr($gudangx[$key],0,4);//gudang tujuan
            $ptpengguna='';
            $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            $bar=$res->fetch();   
            $ptpengguna=$bar->induk;
            $str="select akunpiutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);        
            $intraco='';
            $interco='';
            while($bar=$res->fetch()){
                if($bar->jenis=='intra'){
                   $intraco=$bar->akunpiutang;
                }else{
                   $interco=$bar->akunpiutang; 
                }
            }
            $ptGudang='';
            $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang[$key],0,4)."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            $bar=$res->fetch();   
            $ptGudang=$bar->induk;
            $akunspl='';
            if($ptGudang !=$ptpengguna){
                #ambil akun interco
                $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='inter'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                $bar=$res->fetch();   
                $akunspl='';
                $akunspl=$bar->akunhutang;   
                $inter=$interco;   
                    if($akunspl==''){
                            continue;
                    }
            }
            else if($pengguna!=substr($gudang[$key],0,4)){ #jika satu pt beda kebun
                 #ambil akun intraco
                $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='intra'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                $bar=$res->fetch();   
                $akunspl='';
                $akunspl=$bar->akunhutang;
                $inter=$intraco;  
                    if($akunspl==''){
                        continue;
                    }
            }
            #ambil noakun barang
            $klbarang=substr($kodebarang[$key],0,3);
            $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            $bar=$res->fetch();   
            $akunbarang='';
            $akunbarang=$bar->noakun;
            if($akunbarang==''){
                continue;
            }else{
                if($pengguna==substr($gudang[$key],0,4)){
                    continue;
                }else{
                    if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){    
                                            #jika inter atau intraco 
                                            #proses data sisi pemilik====================================================
                                            $kodeJurnal = 'INVK1';
                                            #======================== Nomor Jurnal =============================
                                            # Get Journal Counter
                                            $nilAwlKlr++;
                                            $konter = addZero($nilAwlKlr,3);

                                            # Transform No Jurnal dari No Transaksi
                                            $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                                            #======================== /Nomor Jurnal ============================
                                            $header1pemilik=$nojurnal;   //no header pemilik    
                                            # Prep Header
                                            $dataRes['header'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'kodejurnal'=>$kodeJurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'tanggalentry'=>date('Ymd'),
                                                    'posting'=>1,
                                                    'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                    'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                    'amountkoreksi'=>'0',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'autojurnal'=>'1',
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'revisi'=>'0'                            
                                            );

                                            # Data Detail
                                            $noUrut = 1;
                                            $keterangan="Mutasi barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]."___by system";
                                            $keterangan=substr($keterangan,0,150);
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$inter,
                                                    'keterangan'=>$keterangan,
                                                    'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>substr($gudang[$key],0,4),
                                                    'kodekegiatan'=>'',
                                                    'kodeasset'=>'',
                                                    'kodebarang'=>$kodebarang[$key],
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>'',
                                                    'nodok'=>'',
                                                    'kodeblok'=>'',
                                                    'revisi'=>'0',
                                                     'kodesegment'=>''                            
                                            );
                                            $noUrut++;

                                            # Kredit
                                            $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$akunbarang,
                                                    'keterangan'=>$keterangan,
                                                    'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>substr($gudang[$key],0,4),
                                                    'kodekegiatan'=>'',
                                                    'kodeasset'=>'',
                                                    'kodebarang'=>$kodebarang[$key],
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>'',
                                                    'nodok'=>'',
                                                    'kodeblok'=>'',
                                                    'revisi'=>'0',
                                                    'kodesegment'=>''                            
                                            );
                                    }       
                            }
                    }
                    break;

                        /** Tipe Transaksi 5 ***************************************/
                    case'5':
                    #=======================================================
                    #periksa apakah dari satu PT
                    $pengguna=substr($untukunit[$key],0,4);
                    $ptpengguna='';
                    $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                    $ptpengguna=$bar->induk;
                    $str="select akunpiutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);  
                    $intraco='';
                    $interco='';
                    while($bar=$res->fetch() ){
                        if($bar->jenis=='intra')
                           $intraco=$bar->akunpiutang;
                        else
                           $interco=$bar->akunpiutang; 
                    }
                    $ptGudang='';
                    $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang[$key],0,4)."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                    $ptGudang=$bar->induk;
                    #jika pt tidak sama maka pakai akun interco
                    $akunspl='';
                    if($ptGudang !=$ptpengguna){
                        #ambil akun interco
                        $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='inter'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();   
                           $akunspl='';
                           $akunspl=$bar->akunhutang;
                           $inter=$interco;   
                       if($akunspl==''){
                           continue;
                       }
                    }
                    else if($pengguna!=substr($gudang[$key],0,4)){ #jika satu pt beda kebun
                         #ambil akun intraco
                        $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='intra'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();   
                           $akunspl='';
                           $akunspl=$bar->akunhutang;

                          $inter=$intraco;  
                        if($akunspl==''){
                            continue;
                        }
                    }
                    #ambil akun pekerjaan atau kendaraan atau ab
                    #periksa ke table setup blok
                    $statustm='';
                    $str="select statusblok from ".$dbname.".setup_blok where kodeorg='".$blok[$key]."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);  
                    while($bar=$res->fetch()){
                        $statustm=$bar->statusblok;
                    }
                        $str="select noakun from ".$dbname.".setup_kegiatan where 
                               kodekegiatan='".$kodekegiatan[$key]."'";
                    $akunpekerjaan='';
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();   
                    $akunpekerjaan=$bar->noakun;
                    #untuk project aktiva dalam konstruksi maka akun diambil dari kolom kodekegiatan
                    $kodeasset='';
                    if(substr($blok[$key],0,2)=='AK' or substr($blok[$key],0,2)=='PB'){
                           $akunpekerjaan=substr($kodekegiatan[$key],0,7);
                           $kodeasset=$blok[$key];
                           $blok[$key]="";#pemindahan kodeblok ke kode asset
                    }
                    if($akunpekerjaan==''){
                        continue;
                    }
                    $klbarang=substr($kodebarang[$key],0,3);
                    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                    $akunbarang='';
                    $akunbarang=$bar->noakun;
                    if($akunbarang==''){
                        continue;
                    }else{
                        if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){    
                            if($pengguna==substr($gudang[$key],0,4)){
                            $kodeJurnal = 'INVK1';
                            #======================== Nomor Jurnal =============================
                            # Get Journal Counter
                            $nilAwlKlr++;
                            $konter = addZero($nilAwlKlr,3);

                            # Transform No Jurnal dari No Transaksi
                            $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                            #======================== /Nomor Jurnal ============================
                            # Prep Header
                                $dataRes['header'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'kodejurnal'=>$kodeJurnal,
                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                    'tanggalentry'=>date('Ymd'),
                                    'posting'=>1,
                                    'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                    'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                    'amountkoreksi'=>'0',
                                    'noreferensi'=>$notransaksi[$key],
                                    'autojurnal'=>'1',
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'revisi'=>'0'                            
                                );

                                # Data Detail
                                $noUrut = 1;
                                 $keterangan="Pemakaian barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]."___by system";
                                # Debet
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunpekerjaan,
                                    'keterangan'=> $keterangan,
                                    'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>substr($gudang[$key],0,4),
                                    'kodekegiatan'=>$kodekegiatan[$key],
                                    'kodeasset'=>$kodeasset,
                                    'kodebarang'=>$kodebarang[$key],
                                    'nik'=>isset($namapenerima[$key])? $namapenerima[$key]: '',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>$notransaksi[$key],
                                    'noaruskas'=>'',
                                    'kodevhc'=>$kodemesin[$key],
                                    'nodok'=>'',
                                    'kodeblok'=>$blok[$key],
                                    'revisi'=>'0',
                                     'kodesegment'=>''                            
                                );
                                $noUrut++;

                                # Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunbarang,
                                    'keterangan'=>$keterangan,
                                    'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>substr($gudang[$key],0,4),
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>$kodebarang[$key],
                                    'nik'=>'',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>$notransaksi[$key],
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>'',
                                    'kodeblok'=>$blok[$key],
                                     'revisi'=>'0',
                                     'kodesegment'=>''                            
                                );
                                $noUrut++; 
                        #=========================================                                
                        }else{
                               #jika inter atau intraco 
                               #proses data sisi pemilik====================================================
                               $kodeJurnal = 'INVK1';
                               $nilAwlKlr++;
                               $konter = addZero($nilAwlKlr,3);

                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                                #======================== /Nomor Jurnal ============================
                                $header1pemilik=$nojurnal;   //no header pemilik    
                                        # Prep Header
                                            $dataRes['header'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'kodejurnal'=>$kodeJurnal,
                                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>$notransaksi[$key],
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'revisi'=>'0'                            
                                            );

                                            # Data Detail
                                            $noUrut = 1;
                                             $keterangan="Pemakaian barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]."___ by system";
                                             //$keterangan=substr($keterangan,0,150);
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                                'nourut'=>$noUrut,
                                                'noakun'=>$inter,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>substr($gudang[$key],0,4),
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>$kodemesin[$key],
                                                'nodok'=>'',
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                                'kodesegment'=>''                           
                                            );
                                            $noUrut++;

                                            # Kredit
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunbarang,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>substr($gudang[$key],0,4),
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>'',
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                                'kodesegment'=>''                            
                                            );                      

                            #proses data sisi pengguna====================================================
                            $kodeJurnal = 'INVK1';

                            # Get Journal Counter
                            if($pt != $ptpengguna) {
                                        $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                                "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."' ");
                                        $tmpKonter = fetchData($queryJ);
                                        $konterPt[$ptpengguna] = $tmpKonter[0]['nokounter']+1;
                                        $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                                } else {
                                        $nilAwlKlr++;
                                        $konter = addZero($nilAwlKlr,3);
                                }
                            $tanggalsana=tanggalsystem($tanggal[$key]);
                            # Transform No Jurnal dari No Transaksi
                            $nojurnal = str_replace("-","",$tanggalsana)."/".$pengguna."/".$kodeJurnal."/".$konter;
                                        #======================== /Nomor Jurnal ============================
                                        # Prep Header  
                                        $dataRes['header'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'kodejurnal'=>$kodeJurnal,
                                                'tanggal'=>$tanggalsana,
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>$notransaksi[$key],
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'revisi'=>'0'                        
                                            );

                                            # Data Detail
                                             $keterangan="Pemakaian barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]." ".substr($tanggal,0,7)."___ by system";

                                            $noUrut = 1;
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>$tanggalsana,
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunpekerjaan,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$pengguna,
                                                'kodekegiatan'=>$kodekegiatan[$key],
                                                'kodeasset'=>$kodeasset,
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>isset($namapenerima[$key])? $namapenerima[$key]: '',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>$kodemesin[$key],
                                                'nodok'=>'',
                                                'kodeblok'=>$blok[$key],
                                                'revisi'=>'0',
                                                'kodesegment'=>''                            
                                            );
                                            $noUrut++;

                                            # Kredit
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>$tanggalsana,
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunspl,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$pengguna,
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>'',
                                                'kodeblok'=>$blok[$key],
                                                'revisi'=>'0',
                                                'kodesegment'=>''                       
                                            );
                                            $noUrut++; 
                                        #===========EXECUTE                      

                        }
                      }
                    }//end else dari akun barang 
                    break;
                }
            }
        }        
        }
        if(!empty($lsJrnlKsng2)){
        foreach($lsJrnlKsng2 as $key =>$val){
                        if($awal==0) {
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                        "kodeorg='".$kodept[$key]."' and kodekelompok='INVM1' ");
                                $tmpKonter = fetchData($queryJ);
                                $invmCurr = $nilAwlMsk = $tmpKonter[0]['nokounter'];
                                //$konter = addZero($tmpKonter[0]['nokounter']+1,3);
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                        "kodeorg='".$kodept[$key]."' and kodekelompok='INVK1' ");
                                $tmpKonter = fetchData($queryJ);
                                $invkCurr = $nilAwlKlr = $tmpKonter[0]['nokounter'];
                                $pt = $kodept[$key];
                                $awal = 1;
                        }

            if(isset($lsTransKsng2[$key]))
            {
                continue;
            }
            else{
                switch ($tipetransaksi[$key]) {
                    case'1':
                        #ambil noakun supplier
                        $kodekl=substr($supplier[$key],0,4);
                        $str="select noakun from ".$dbname.".log_5klsupplier where kode='".$kodekl."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();   
                        $akunspl='';
                        $akunspl=$bar->noakun;
                        #ambil noakun barang
                        $klbarang=substr($kodebarang[$key],0,3);
                        $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();   
                        $akunbarang='';
                        $akunbarang=$bar->noakun;
                        if(($akunbarang=='' or $akunspl=='') and (intval($klbarang)<'400' or substr($kodebarang[$key],0,1)=='9')){    
                             continue;
                        }
                        #proses data
                        $kodeJurnal = 'INVM1';
                        $nilAwlMsk++;
                        $konter = addZero($nilAwlMsk,3);

                        # Transform No Jurnal dari No Transaksi
                        $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                        #======================== /Nomor Jurnal ============================
                        if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){ 
                            # Prep Header
                            $dataRes['header'][] = array(
                                'nojurnal'=>$nojurnal,
                                'kodejurnal'=>$kodeJurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'tanggalentry'=>date('Ymd'),
                                'posting'=>1,
                                'totaldebet'=>$nilaitotal[$key],
                                'totalkredit'=>-1*$nilaitotal[$key],
                                'amountkoreksi'=>'0',
                                'noreferensi'=>$notransaksi[$key],
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
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunbarang,
                                'keterangan'=>'Pembelian barang '.$namabarang[$key].' '.$jumlah[$key]." ".$satuan[$key],
                                'jumlah'=>$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($gudang[$key],0,4),
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>$supplier[$key],
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nopo[$key],
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>''                
                            );
                            $noUrut++;

                            # Kredit
                            $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunspl,
                                'keterangan'=>'Pembelian barang '.$namabarang[$key].' '.$jumlah[$key]." ".$satuan[$key],
                                'jumlah'=>-1*$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($gudang[$key],0,4),
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>$supplier[$key],
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nopo[$key],
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>''                
                            );
                            $noUrut++;  
                        }  
                    break;
                    case'6':
                         if(intval($hargasatuan[$key])==0 or $nopo=='' or $supplier==''){
                            continue;
                            //exit(" Error: ".$_SESSION['lang']['priceposuppnotfound']);
                        }
                        #ambil noakun supplier
                        $kodekl=substr($supplier[$key],0,4);
                        $str="select noakun from ".$dbname.".log_5klsupplier where kode='".$kodekl."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();   
                        $akunspl='';
                        $akunspl=$bar->noakun;
                        #ambil noakun barang
                        $klbarang=substr($kodebarang[$key],0,3);
                        $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();   
                        $akunbarang='';
                        $akunbarang=$bar->noakun;

                        if(($akunbarang=='' or $akunspl=='') and ($klbarang<'400' or substr($kodebarang[$key],0,1)=='9')){    
                            continue;
                        }

                        #proses data
                        $kodeJurnal = 'INVK1';
                        $nilAwlKlr++;
                        $konter = addZero($nilAwlKlr,3);

                        # Transform No Jurnal dari No Transaksi
                        $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                        #======================== /Nomor Jurnal ============================
                        # Prep Header
                        if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){        
                            $dataRes['header'][] = array(
                                'nojurnal'=>$nojurnal,
                                'kodejurnal'=>$kodeJurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'tanggalentry'=>date('Ymd'),
                                'posting'=>1,
                                'totaldebet'=>$nilaitotal[$key],
                                'totalkredit'=>-1*$nilaitotal[$key],
                                'amountkoreksi'=>'0',
                                'noreferensi'=>$notransaksi[$key],
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
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunspl,
                                'keterangan'=>'ReturSupplier '.$namabarang[$key].' '.$jumlah[$key]." ".$satuan[$key],
                                'jumlah'=>$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($gudang[$key],0,4),
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>$supplier[$key],
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nopo[$key],
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>''                
                            );
                            $noUrut++;

                            # Kredit
                            $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunbarang,
                                'keterangan'=>'ReturSupplier '.$namabarang[$key].' '.$jumlah[$key]." ".$satuan[$key],
                                'jumlah'=>-1*$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($gudang[$key],0,4),
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>$supplier[$key],
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nopo[$key],
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>''               
                            );
                            $noUrut++;      
                            #============================
                        }
                    break;
                    case'2':
                     #ambil harga satuan dan saldo
                     $pengguna=substr($untukunit[$key],0,4);
                     $ptpengguna='';
                     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                     $ptpengguna=$bar->induk;
                     $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$pengguna."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                     $intraco='';
                     $interco='';
                     if($bar->jenis=='intra')
                        $intraco=$bar->akunhutang;
                     else
                        $interco=$bar->akunhutang; 
                     $ptGudang='';
                     $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang[$key],0,4)."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                     $ptGudang=$bar->induk;
                     #jika pt tidak sama maka pakai akun interco
                     $akunspl='';
                     if($ptGudang !=$ptpengguna){
                         #ambil akun interco
                         $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='inter'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();   
                            $akunspl='';
                            $akunspl=$bar->akunpiutang;
                            $inter=$interco;   
                            if($akunspl==''){
                                continue;
                            }

                     }
                     else if($pengguna!=substr($gudang[$key],0,4)){ #jika satu pt beda kebun
                          #ambil akun intraco
                         $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='intra'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();   
                            $akunspl='';
                            $akunspl=$bar->akunpiutang;
                         $inter=$intraco;  
                         if($akunspl==''){
                            continue;
                         }
                     }
                     #ambil akun pekerjaan atau kendaraan atau ab
                     #periksa ke table setup blok
                     $statustm='';
                     $str="select statusblok from ".$dbname.".setup_blok where kodeorg='".$blok[$key]."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                    $statustm=$bar->statusblok;
                    $str="select noakun from ".$dbname.".setup_kegiatan where  kodekegiatan='".$kodekegiatan[$key]."'";
                     $akunpekerjaan='';
                     $kodeasset='';
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();   
                     $akunpekerjaan=$bar->noakun;
                     #jika akun kegiatan tidak ada maka exit
                     if($akunpekerjaan==''){
                        // Cek akun untuk yang Project
                        $str="select noakun from ".$dbname.".keu_5akun where noakun='".$kodekegiatan[$key]."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        while($bar=$res->fetch())
                        {
                            $akunpekerjaan=$bar->noakun;
                        }
                        if ($akunpekerjaan==''){

                        } else {
                            $kodeasset=$blok[$key];
                        }
                     }
                    #ambil noakun barang
                    $klbarang=substr($kodebarang[$key],0,3);
                    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();
                    $akunbarang='';
                    $akunbarang=$bar->noakun;
                    if($akunbarang==''){
                        continue;
                    }else{
                            if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){
                                if($pengguna==substr($gudang[$key],0,4)){
                                            $kodeJurnal = 'INVM1';
                                            #======================== Nomor Jurnal =============================
                                            # Get Journal Counter
                                            $nilAwlMsk++;
                                            $konter = addZero($nilAwlMsk,3);

                                            # Transform No Jurnal dari No Transaksi
                                            $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                                            #======================== /Nomor Jurnal ============================
                                            # Prep Header
                                                $dataRes['header'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'kodejurnal'=>$kodeJurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'tanggalentry'=>date('Ymd'),
                                                    'posting'=>1,
                                                    'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                    'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                    'amountkoreksi'=>'0',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'autojurnal'=>'1',
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'revisi'=>'0'                            
                                                );

                                                # Data Detail
                                                $noUrut = 1;
                                                 $keterangan="ReturGudang barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key];
                                                # Debet
                                                $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$akunbarang,
                                                    'keterangan'=> $keterangan,
                                                    'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>substr($gudang[$key],0,4),
                                                    'kodekegiatan'=>'',
                                                    'kodeasset'=>'',
                                                    'kodebarang'=>$kodebarang[$key],
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>$kodemesin[$key],
                                                    'nodok'=>'',
                                                    'kodeblok'=>$blok[$key],
                                                    'revisi'=>'0',
                                                    'kodesegment'=>''                            
                                                );
                                                $noUrut++;

                                                # Kredit
                                                $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$akunpekerjaan,
                                                    'keterangan'=>$keterangan,
                                                    'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>substr($gudang[$key],0,4),
                                                    'kodekegiatan'=>$kodekegiatan[$key],
                                                    'kodeasset'=>$kodeasset,
                                                    'kodebarang'=>$kodebarang[$key],
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>$notransaksi[$key],
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>$kodemesin[$key],
                                                    'nodok'=>'',
                                                    'kodeblok'=>$blok[$key],
                                                     'revisi'=>'0',
                                                     'kodesegment'=>''                            
                                                );
                                                $noUrut++; 
                        #========================================= 
                             }else{
                                 #jika inter atau intraco 
                                      #proses data sisi pemilik====================================================
                                        $kodeJurnal = 'INVM1';
                                        #======================== Nomor Jurnal =============================
                                        # Get Journal Counter
                                       $nilAwlMsk++;
                                       $konter = addZero($nilAwlMsk,3);

                                        # Transform No Jurnal dari No Transaksi
                                        $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                                        #======================== /Nomor Jurnal ============================
                                          $header1pemilik=$nojurnal;   //no header pemilik    
                                        # Prep Header
                                            $dataRes['header'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'kodejurnal'=>$kodeJurnal,
                                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>$notransaksi[$key],
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                    'revisi'=>'0'                            
                                            );

                                            # Data Detail
                                            $noUrut = 1;
                                             $keterangan="ReturGudang barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key];
                                             $keterangan=substr($keterangan,0,150);
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunbarang,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>substr($gudang[$key],0,4),
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>$kodemesin[$key],
                                                'nodok'=>'',
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                                'kodesegment'=>''                            
                                            );
                                            $noUrut++;

                                            # Kredit
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                                'nourut'=>$noUrut,
                                                'noakun'=>$inter,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>substr($gudang[$key],0,4),
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>$kodemesin[$key],
                                                'nodok'=>'',
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                                'kodesegment'=>''                            
                                            );

                                        #proses data sisi pengguna====================================================
                                        $kodeJurnal = 'INVM1';
                                        #======================== Nomor Jurnal =============================

                                        # Get Journal Counter
                                        $nilAwlMsk++;
                                        $konter = addZero($nilAwlMsk,3);
                                        $tanggalsana=tanggalsystem($tanggal[$key]);
                                        # Transform No Jurnal dari No Transaksi
                                        $nojurnal = str_replace("-","",$tanggalsana)."/".$pengguna."/".$kodeJurnal."/".$konter;
                                        #======================== /Nomor Jurnal ============================
                                        # Prep Header

                                        $dataRes['header'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'kodejurnal'=>$kodeJurnal,
                                                'tanggal'=>$tanggalsana,
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>$notransaksi[$key],
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                    'revisi'=>'0'                        
                                            );

                                            # Data Detail
                                             $keterangan="ReturGudang barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]." ".substr($tanggal[$key],0,7)."__by system";
                                             $keterangan=substr($keterangan,0,150);
                                            $noUrut = 1;
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>$tanggalsana,
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunspl,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$pengguna,
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>$kodemesin[$key],
                                                'nodok'=>'',
                                                'kodeblok'=>$blok[$key],
                                                'revisi'=>'0',
                                                'kodesegment'=>''                            
                                            );
                                            $noUrut++;

                                            # Kredit
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>$tanggalsana,
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunpekerjaan,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$pengguna,
                                                'kodekegiatan'=>$kodekegiatan[$key],
                                                'kodeasset'=>'',
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>$kodemesin[$key],
                                                'nodok'=>'',
                                                'kodeblok'=>$blok[$key],
                                                'revisi'=>'0',
                                                'kodesegment'=>''                       
                                            );
                                            $noUrut++; 
                             }

                        }
                    }
                    break;
                    case'3':
                        #=======================================================
                        #periksa apakah dari satu PT
                        $pengguna=substr($gudang[$key],0,4);//ini sebenarnya pemilik
                        $ptpengguna='';
                        $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";//ini sebenarnya pemilik
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();
                        $ptpengguna=$bar->induk;
                        $ptGudang='';
                        $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudangx[$key],0,4)."'";//ini yang pengguna
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();
                        $ptGudang=$bar->induk;
                        #jika pt tidak sama maka pakai akun interco
                        $akunspl='';
                        if($ptGudang !=$ptpengguna){
                            #ambil akun interco
                            $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudangx[$key],0,4)."' and jenis='inter'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $akunspl='';
                            $akunspl=$bar->akunpiutang;  
                            if($akunspl==''){
                                continue;
                            }
                        }
                        else if($pengguna!=substr($gudangx[$key],0,4)){ #jika satu pt beda kebun
                            #ambil akun intraco
                            $str="select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudangx[$key],0,4)."' and jenis='intra'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $akunspl='';
                            $akunspl=$bar->akunpiutang;
                            if($akunspl==''){
                                continue;
                            }
                        }
                        #ambil noakun barang
                        $klbarang=substr($kodebarang[$key],0,3);
                        $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();
                        $akunbarang='';
                        $akunbarang=$bar->noakun;
                        if($akunbarang==''){
                            continue;
                        }else{
                             #proses data sisi pengguna====================================================
                             $kodeJurnal = 'INVM1';
                             $nilAwlMsk++;
                             $konter = addZero($nilAwlMsk,3);
                             # Transform No Jurnal dari No Transaksi
                            $nojurnal = tanggalsystem($tanggal[$key])."/".$pengguna."/".$kodeJurnal."/".$konter;
                            if((substr($kodebarang[$key],0,3)<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!='' and (substr($pengguna,0,4)!=substr($gudangx[$key],0,4))){  #hanya barang stok yang dijurnal      dan mutasi keluar kebun
                                $dataRes['header'][] = array(
                                'nojurnal'=>$nojurnal,
                                'kodejurnal'=>$kodeJurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'tanggalentry'=>date('Ymd'),
                                'posting'=>1,
                                'totaldebet'=>$nilaitotal[$key],
                                'totalkredit'=>(-1*$nilaitotal[$key]),
                                'amountkoreksi'=>'0',
                                'noreferensi'=>$notransaksi[$key],
                                'autojurnal'=>'1',
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'revisi'=>'0'                        
                                );
                                # Data Detail
                                $keterangan="Terima Mutasi barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]." ".substr($tanggal[$key],0,7)."___ by system";
                                $keterangan=substr($keterangan,0,150);
                                $noUrut = 1;
                                # Debet
                                $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunbarang,
                                'keterangan'=>$keterangan,
                                'jumlah'=>$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$pengguna,
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>'',
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>'',
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>''                            
                                );
                                $noUrut++;

                                # Kredit
                                $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunspl,
                                'keterangan'=>$keterangan,
                                'jumlah'=>(-1*$nilaitotal[$key]),
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$pengguna,
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>'',
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>'',
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>''                       
                                );
                                $noUrut++; 
                            }
                        }   
                    break;
                    case'7':
                        #periksa apakah dari satu PT
                        $pengguna=substr($gudangx[$key],0,4);//gudang tujuan
                        $ptpengguna='';
                        $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();
                        $ptpengguna=$bar->induk;
                        $str="select akunpiutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $intraco='';
                        $interco='';
                        while($bar=$res->fetch()){
                            if($bar->jenis=='intra'){
                               $intraco=$bar->akunpiutang;
                            }else{
                               $interco=$bar->akunpiutang; 
                            }
                        }
                        $ptGudang='';
                        $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang[$key],0,4)."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();
                        $ptGudang=$bar->induk;
                        $akunspl='';
                        if($ptGudang !=$ptpengguna){
                            #ambil akun interco
                            $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang,0,4)."' and jenis='inter'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $akunspl='';
                            $akunspl=$bar->akunhutang;   
                            $inter=$interco;   
                           if($akunspl==''){
                               continue;
                           }
                        }
                        else if($pengguna!=substr($gudang[$key],0,4)){ #jika satu pt beda kebun
                             #ambil akun intraco
                            $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='intra'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $akunspl='';
                            $akunspl=$bar->akunhutang;
                            $inter=$intraco;  
                            if($akunspl==''){
                                continue;
                            }
                        }
                        #ambil noakun barang
                        $klbarang=substr($kodebarang[$key],0,3);
                        $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();
                        $akunbarang='';
                        $akunbarang=$bar->noakun;
                        if($akunbarang==''){
                            continue;
                        }else{
                            if($pengguna==substr($gudang[$key],0,4)){
                                continue;
                            }else{
                                if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){    
                                #jika inter atau intraco 
                                #proses data sisi pemilik====================================================
                                $kodeJurnal = 'INVK1';
                                #======================== Nomor Jurnal =============================
                                # Get Journal Counter
                                $nilAwlKlr++;
                                $konter = addZero($nilAwlKlr,3);

                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                                #======================== /Nomor Jurnal ============================
                                  $header1pemilik=$nojurnal;   //no header pemilik    
                                # Prep Header
                                    $dataRes['header'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'kodejurnal'=>$kodeJurnal,
                                        'tanggal'=>tanggalsystem($tanggal[$key]),
                                        'tanggalentry'=>date('Ymd'),
                                        'posting'=>1,
                                        'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                        'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                        'amountkoreksi'=>'0',
                                        'noreferensi'=>$notransaksi[$key],
                                        'autojurnal'=>'1',
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'revisi'=>'0'                            
                                    );

                                # Data Detail
                                $noUrut = 1;
                                 $keterangan="Mutasi barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]."___by system";
                                 $keterangan=substr($keterangan,0,150);
                                # Debet
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                    'nourut'=>$noUrut,
                                    'noakun'=>$inter,
                                    'keterangan'=>$keterangan,
                                    'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>substr($gudang[$key],0,4),
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>$kodebarang[$key],
                                    'nik'=>'',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>$notransaksi[$key],
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>'',
                                    'kodeblok'=>'',
                                    'revisi'=>'0',
                                    'kodesegment'=>''                            
                                );
                                $noUrut++;

                                # Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunbarang,
                                    'keterangan'=>$keterangan,
                                    'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>substr($gudang[$key],0,4),
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>$kodebarang[$key],
                                    'nik'=>'',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>$notransaksi[$key],
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>'',
                                    'kodeblok'=>'',
                                    'revisi'=>'0',
                                    'kodesegment'=>''                            
                                );
                            }       

                        }
                    }


                    break;
                    case'5':
                    #=======================================================
                    #periksa apakah dari satu PT
                    $pengguna=substr($untukunit[$key],0,4);
                    $ptpengguna='';
                    $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();
                    $ptpengguna=$bar->induk;
                    $str="select akunpiutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $intraco='';
                    $interco='';
                    while($bar=$bar=$res->fetch()){
                        if($bar->jenis=='intra')
                           $intraco=$bar->akunpiutang;
                        else
                           $interco=$bar->akunpiutang; 
                    }
                    $ptGudang='';
                    $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang[$key],0,4)."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();
                    $ptGudang=$bar->induk;
                    #jika pt tidak sama maka pakai akun interco
                    $akunspl='';
                    if($ptGudang !=$ptpengguna){
                        #ambil akun interco
                        $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='inter'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                           $akunspl='';
                           $akunspl=$bar->akunhutang;
                           $inter=$interco;   
                       if($akunspl==''){
                           continue;
                       }
                    }
                    else if($pengguna!=substr($gudang[$key],0,4)){ #jika satu pt beda kebun
                         #ambil akun intraco
                        $str="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang[$key],0,4)."' and jenis='intra'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $bar=$res->fetch();
                           $akunspl='';
                           $akunspl=$bar->akunhutang;

                          $inter=$intraco;  
                        if($akunspl==''){
                            continue;
                        }
                    }
                    #ambil akun pekerjaan atau kendaraan atau ab
                    #periksa ke table setup blok
                    $statustm='';
                    $str="select statusblok from ".$dbname.".setup_blok where kodeorg='".$blok[$key]."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    while($bar=$res->fetch()){
                        $statustm=$bar->statusblok;
                    }
                        $str="select noakun from ".$dbname.".setup_kegiatan where  kodekegiatan='".$kodekegiatan[$key]."'";
                    $akunpekerjaan='';
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();
                    $akunpekerjaan=$bar->noakun;
                    #untuk project aktiva dalam konstruksi maka akun diambil dari kolom kodekegiatan
                    $kodeasset='';
                    if(substr($blok[$key],0,2)=='AK' or substr($blok[$key],0,2)=='PB'){
                           $akunpekerjaan=substr($kodekegiatan[$key],0,7);
                           $kodeasset=$blok[$key];
                           $blok[$key]="";#pemindahan kodeblok ke kode asset
                    }
                    if($akunpekerjaan==''){
                        continue;
                    }
                    $klbarang=substr($kodebarang[$key],0,3);
                    $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar=$res->fetch();
                    $akunbarang='';
                    $akunbarang=$bar->noakun;
                    if($akunbarang==''){
                        continue;
                    }else{
                        if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){    
                            if($pengguna==substr($gudang[$key],0,4)){
                            $kodeJurnal = 'INVK1';
                            #======================== Nomor Jurnal =============================
                            # Get Journal Counter
                            $nilAwlKlr++;
                            $konter = addZero($nilAwlKlr,3);

                            # Transform No Jurnal dari No Transaksi
                            $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                            #======================== /Nomor Jurnal ============================
                            # Prep Header
                                $dataRes['header'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'kodejurnal'=>$kodeJurnal,
                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                    'tanggalentry'=>date('Ymd'),
                                    'posting'=>1,
                                    'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                    'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                    'amountkoreksi'=>'0',
                                    'noreferensi'=>$notransaksi[$key],
                                    'autojurnal'=>'1',
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'revisi'=>'0'                            
                                );

                                # Data Detail
                                $noUrut = 1;
                                 $keterangan="Pemakaian barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]."___by system";
                                # Debet
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunpekerjaan,
                                    'keterangan'=> $keterangan,
                                    'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>substr($gudang[$key],0,4),
                                    'kodekegiatan'=>$kodekegiatan[$key],
                                    'kodeasset'=>$kodeasset,
                                    'kodebarang'=>$kodebarang[$key],
                                    'nik'=>isset($namapenerima[$key])? $namapenerima[$key]: '',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>$notransaksi[$key],
                                    'noaruskas'=>'',
                                    'kodevhc'=>$kodemesin[$key],
                                    'nodok'=>'',
                                    'kodeblok'=>$blok[$key],
                                    'revisi'=>'0',
                                    'kodesegment'=>''                            
                                );
                                $noUrut++;

                                # Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>tanggalsystem($tanggal[$key]),
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunbarang,
                                    'keterangan'=>$keterangan,
                                    'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>substr($gudang[$key],0,4),
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>$kodebarang[$key],
                                    'nik'=>'',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>$notransaksi[$key],
                                    'noaruskas'=>'',
                                    'kodevhc'=>$kodemesin[$key],
                                    'nodok'=>'',
                                    'kodeblok'=>$blok[$key],
                                     'revisi'=>'0',
                                    'kodesegment'=>''                            
                                );
                                $noUrut++; 
                        #=========================================                                
                        }else{
                               #jika inter atau intraco 
                               #proses data sisi pemilik====================================================
                               $kodeJurnal = 'INVK1';
                               $nilAwlKlr++;
                               $konter = addZero($nilAwlKlr,3);

                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                                #======================== /Nomor Jurnal ============================
                                $header1pemilik=$nojurnal;   //no header pemilik    
                                        # Prep Header
                                            $dataRes['header'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'kodejurnal'=>$kodeJurnal,
                                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>$notransaksi[$key],
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                    'revisi'=>'0'                            
                                            );

                                            # Data Detail
                                            $noUrut = 1;
                                             $keterangan="Pemakaian barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]."___ by system";
                                             //$keterangan=substr($keterangan,0,150);
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                                'nourut'=>$noUrut,
                                                'noakun'=>$inter,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>substr($gudang[$key],0,4),
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>'',
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                                'kodesegment'=>''                            
                                            );
                                            $noUrut++;

                                            # Kredit
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunbarang,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>substr($gudang[$key],0,4),
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>'',
                                                'kodeblok'=>'',
                                                 'revisi'=>'0',
                                                'kodesegment'=>''                            
                                            );                      

                            #proses data sisi pengguna====================================================
                            $kodeJurnal = 'INVK1';
                            #======================== Nomor Jurnal =============================

                            # Get Journal Counter
                            if($pt != $ptpengguna) {
                                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                            "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."' ");
                                    $tmpKonter = fetchData($queryJ);
                                    $konterPt[$ptpengguna] = $tmpKonter[0]['nokounter']+1;
                                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                            } else {
                                    $nilAwlKlr++;
                                    $konter = addZero($nilAwlKlr,3);
                            }
                            $tanggalsana=tanggalsystem($tanggal[$key]);
                            # Transform No Jurnal dari No Transaksi
                            $nojurnal = str_replace("-","",$tanggalsana)."/".$pengguna."/".$kodeJurnal."/".$konter;
                                        #======================== /Nomor Jurnal ============================
                                        # Prep Header  
                                        $dataRes['header'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'kodejurnal'=>$kodeJurnal,
                                                'tanggal'=>$tanggalsana,
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>($jumlah[$key]*$hargarata[$key]),
                                                'totalkredit'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>$notransaksi[$key],
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                    'revisi'=>'0'                        
                                            );

                                            # Data Detail
                                             $keterangan="Pemakaian barang ".$namabarang[$key]." ".$jumlah[$key]." ".$satuan[$key]." ".substr($tanggal,0,7)."___ by system";

                                            $noUrut = 1;
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>$tanggalsana,
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunpekerjaan,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>($jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$pengguna,
                                                'kodekegiatan'=>$kodekegiatan[$key],
                                                'kodeasset'=>$kodeasset,
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>isset($namapenerima[$key])? $namapenerima[$key]: '',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>$kodemesin[$key],
                                                'nodok'=>'',
                                                'kodeblok'=>$blok[$key],
                                                'revisi'=>'0',
                                                'kodesegment'=>''                            
                                            );
                                            $noUrut++;

                                            # Kredit
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>$tanggalsana,
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunspl,
                                                'keterangan'=>$keterangan,
                                                'jumlah'=>(-1*$jumlah[$key]*$hargarata[$key]),
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$pengguna,
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>$kodebarang[$key],
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>$notransaksi[$key],
                                                'noaruskas'=>'',
                                                'kodevhc'=>$kodemesin[$key],
                                                'nodok'=>'',
                                                'kodeblok'=>$blok[$key],
                                                'revisi'=>'0',
                                                'kodesegment'=>''                       
                                            );
                                            $noUrut++; 
                         }
                      }
                    }//end else dari akun barang 
                    break;
                    case'0':
                        #ambil noakun supplier
                        $kodekl=substr($supplier[$key],0,4);
                        $str = "select noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='INVM0'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $akunspl='';
                        $bar=$res->fetch();
                        $akunspl=$bar->noakun;
                        #ambil noakun barang
                        $klbarang=substr($kodebarang[$key],0,3);
                        $str="select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $akunbarang='';
                        $bar=$res->fetch();
                        $akunbarang=$bar->noakun;
                        if(($akunbarang=='' or $akunspl=='') and (intval($klbarang)<'400' or substr($kodebarang[$key],0,1)=='9')){    
                            continue;
                        }
                        #proses data
                        $kodeJurnal = 'INVM0';
                        $nilAwlMsk++;
                        $konter = addZero($nilAwlMsk,3);

                                                # Transform No Jurnal dari No Transaksi
                        $nojurnal = str_replace("-","",tanggalsystem($tanggal[$key]))."/".substr($gudang[$key],0,4)."/".$kodeJurnal."/".$konter;
                        #======================== /Nomor Jurnal ============================
                        if(intval((substr($kodebarang[$key],0,3))<'400' or substr($kodebarang[$key],0,1)=='9') and trim($akunbarang)!=''){ 
                            # Prep Header
                            $dataRes['header'][] = array(
                                'nojurnal'=>$nojurnal,
                                'kodejurnal'=>$kodeJurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'tanggalentry'=>date('Ymd'),
                                'posting'=>1,
                                'totaldebet'=>$nilaitotal[$key],
                                'totalkredit'=>-1*$nilaitotal[$key],
                                'amountkoreksi'=>'0',
                                'noreferensi'=>$notransaksi[$key],
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
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunbarang,
                                'keterangan'=>'Penerimaan Barang Pabrikasi '.$namabarang[$key].' '.$jumlah[$key]." ".$satuan[$key],
                                'jumlah'=>$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($gudang[$key],0,4),
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>'',
                                'noreferensi'=>$notransaksi[$key],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>'',
                                'kodeblok'=>$notransaksireferensi[$key],
                                'revisi'=>'0',
                                'kodesegment'=>''
                            );
                            $noUrut++;

                            # Kredit
                            $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>tanggalsystem($tanggal[$key]),
                                'nourut'=>$noUrut,
                                'noakun'=>$akunspl,
                                'keterangan'=>'Penerimaan Barang Pabrikasi '.$namabarang[$key].' '.$jumlah[$key]." ".$satuan[$key],
                                'jumlah'=>-1*$nilaitotal[$key],
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($gudang[$key],0,4),
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$kodebarang[$key],
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>'',
                                'noreferensi'=>'',
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>'',
                                'kodeblok'=>$notransaksireferensi[$key],
                                'revisi'=>'0',
                                'kodesegment'=>''                
                            );
                            $noUrut++;  
                        }
                    break;
                }
                //$bentukjurnal2[]=$val;
            }//else dari pengecekan
        }//end dari perulangan    
        }

        $detailErr='';
        if(!empty($dataRes['header'])){
                foreach($dataRes['header'] as $row) {
                        $insDet = insertQuery($dbname,'keu_jurnalht',$row);
                        try{$owlPDO->exec($insDet); }
                        catch (PDOException $e) {
                            $detailErr .= "Insert Header Error : " . $e->getMessage() . "<br/>";
                            break;
                        }
                }
                if($detailErr=='')
                {
                        foreach($dataRes['detail'] as $row) {
                                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                try{$owlPDO->exec($insDet); }
                                catch (PDOException $e) {
                                    $detailErr .= "Insert Detail Error : " . $e->getMessage();
                                    $str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$row['nojurnal']."'";
                                    $owlPDO->exec($str);
                                    break;
                                }                                
                        }

                        if($detailErr=='')
                        {
                                if(!empty($pt)) {
                                        // Update COUNTER JURNAL INVM
                                        $dataUpdM = array('nokounter'=>$nilAwlMsk);
                                        $qUpdM = updateQuery($dbname,'keu_5kelompokjurnal',$dataUpdM,"kodeorg='".$pt."' and kodekelompok='INVM1' ");
                                        try{$owlPDO->exec($qUpdM); }
                                        catch (PDOException $e) {
                                            $detailErr .= "Update Kounter INVM1 Error :" . $e->getMessage();
                                        }
                                        // Update COUNTER JURNAL INVK
                                        $dataUpdK = array('nokounter'=>$nilAwlKlr);
                                        $qUpdK = updateQuery($dbname,'keu_5kelompokjurnal',$dataUpdK,"kodeorg='".$pt."' and kodekelompok='INVK1' ");
                                        try{$owlPDO->exec($qUpdK); }
                                        catch (PDOException $e) {
                                            $detailErr .= "Update Kounter INVK1 Error :" . $e->getMessage();
                                        }                                        

                                        // Update COUNTER JURNAL PT LAIN
                                        foreach($konterPt as $ptX=>$cnt) {
                                                $dataUpd = array('nokounter'=>$cnt);
                                                $qUpd = updateQuery($dbname,'keu_5kelompokjurnal',$dataUpd,"kodeorg='".$ptX."' and kodekelompok='INVK1' ");
                                                try{$owlPDO->exec($qUpd); }
                                                catch (PDOException $e) {
                                                    $detailErr .= "Update Kounter ".$ptX." Error :" . $e->getMessage();
                                                }                                                    
                                        }
                                }
                        } else {
                                echo $detailErr;
                        }
                } else {
                        echo $detailErr;
                }
        }


        /***************************************************************************
         ** [CHECK MATERIAL BKM] ***************************************************
         ***************************************************************************/
        // Condition
        $wrtgl=" and tanggal between '".$rDt['tanggalmulai']."' and '".$rDt['tanggalsampai']."'";

        // Akun Barang & Prestasi
        $optAkunMat = makeOption($dbname,'log_5klbarang','kode,noakun');
        $optAkunKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,noakun');

        // Get Kebun Aktifitas
        $qBkm = "SELECT DISTINCT a.notransaksi as notransMat, b.*, c.nojurnal, d.tanggal
                FROM ".$dbname.".`log_transaksi_vw` a
                LEFT JOIN ".$dbname.".kebun_prestasi b ON a.notransaksireferensi = b.notransaksi
                LEFT JOIN ".$dbname.".keu_jurnalht c ON b.notransaksi = c.noreferensi and nojurnal like '%/INVK%'
                LEFT JOIN ".$dbname.".kebun_aktifitas d ON a.notransaksireferensi = d.notransaksi
                WHERE substr(a.`notransaksi`,7,1) = 'M'
                AND a.tanggal LIKE '".$_POST['periode']."%'
                AND a.`kodegudang` LIKE '".$_POST['kodeorg']."%' AND c.nojurnal is NULL
                ORDER BY b.notransaksi";
        $resBkm = fetchData($qBkm);

        // List No Transaksi
        $listNoTrans = array();
        foreach($resBkm as $r) {
                $listNoTrans[$r['notransaksi']] = $r['notransaksi'];
        }

        if(!empty($resBkm)) {
                // Get Pakai Material
                $qMat = "SELECT a.*,b.namabarang, c.hargarata
                        FROM ".$dbname.".`kebun_pakaimaterial` a
                        LEFT JOIN ".$dbname.".log_5masterbarang b ON a.kodebarang = b.kodebarang
                        LEFT JOIN ".$dbname.".log_transaksi_vw c ON a.notransaksi = c.notransaksireferensi and a.kodebarang = c.kodebarang
                        WHERE a.notransaksi in ('".implode("','",$listNoTrans)."')";
                $resMat = fetchData($qMat);
                $optMat = array();
                foreach($resMat as $r) {
                        $optMat[$r['notransaksi']][] = $r;
                }

                // Iterasi tiap transaksi yang belum ada jurnal
                $kodeJurnal = 'INVK1';
                foreach($resBkm as $row) {
                        // Get Counter
                        $counter = $zJ->getCounter($pt, $kodeJurnal) + 1;

                        // Generate No Jurnal
                        $nojurnal = $zJ->genNoJournal($row['tanggal'],$_POST['kodeorg'],$kodeJurnal, $counter);

                        $dataRes = array();
                        # Prep Header
                        $dataRes['header'] = array(
                                'nojurnal'=>$nojurnal,
                                'kodejurnal'=>$kodeJurnal,
                                'tanggal'=>$row['tanggal'],
                                'tanggalentry'=>date('Ymd'),
                                'posting'=>'1',
                                'totaldebet'=>'0',
                                'totalkredit'=>'0',
                                'amountkoreksi'=>'0',
                                'noreferensi'=>$row['notransaksi'],
                                'autojurnal'=>'1',
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'revisi'=>'0'
                        );

                        // Iterasi di Kebun Pakai Material
                        $noUrut = 1;
                        $totalJumlah = 0;
                        foreach($optMat[$row['notransaksi']] as $rMat) {
                                // Detail (Kredit)
                                $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$row['tanggal'],
                                        'nourut'=>$noUrut,
                                        'noakun'=>$optAkunMat[substr($rMat['kodebarang'],0,3)],
                                        'keterangan'=>'Material BKM '. $row['notransaksi']." ".$rMat['namabarang'],
                                        'jumlah'=>$rMat['hargarata'] * $rMat['kwantitas'] * (-1),
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>'',
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>$rMat['kodebarang'],
                                        'nik'=>'',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>$row['notransaksi'],
                                        'noaruskas'=>'',
                                        'kodevhc'=>'',
                                        'nodok'=>'',
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment' => $row['kodesegment']
                                );
                                $totalJumlah += $rMat['hargarata'] * $rMat['kwantitas'];
                                $noUrut++;
                        }

                        // Detail (Debet)
                        $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>$row['tanggal'],
                                'nourut'=>$noUrut,
                                'noakun'=>$optAkunKeg[$row['kodekegiatan']],
                                'keterangan'=>'Material BKM '.$row['notransaksi'],
                                'jumlah'=>$totalJumlah,
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>substr($row['kodeorg'],0,4),
                                'kodekegiatan'=>$row['kodekegiatan'],
                                'kodeasset'=>'',
                                'kodebarang'=>'',
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>'',
                                'noreferensi'=>$row['notransaksi'],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>'',
                                'kodeblok'=>$row['kodeorg'],
                                'revisi'=>'0',
                                'kodesegment' => $row['kodesegment']
                        );

                        $zJ->doJournal($pt, $kodeJurnal, $dataRes, $counter, "Pengeluaran Barang");
                }
        }

        // Report BKM Check
        //echo '<pre>';
        //print_r($optMat);
        //print_r($resBkm);

/**************************************************************
 * [START] Cek Nilai Material VS Jurnal ***********************
 **************************************************************/
// Get Kelompok Barang yang ada Akun
$optKel = makeOption($dbname,'log_5klbarang',"kode,noakun","noakun!=''");
$listKel = $listAkun = array();
foreach($optKel as $kode=>$akun) {
  $listKel[] =  $kode;
  $listAkun[$akun] =  $akun;
}
unset($dataRes);
$dataRes=Array();
// Get Nilai Material, log_5saldobulanan
$qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
  FROM ".$dbname.".log_5saldobulanan 
  WHERE left(kodebarang,3) in ('".implode("','",$listKel)."') and kodegudang like '".$_POST['kodeorg']."%' and periode='".$_POST['periode']."' GROUP BY left(kodebarang,3)";
  //echo $qSaldoMat."<p>";
$resSaldoMat = fetchData($qSaldoMat);
$optSaldoMat = array();
foreach($resSaldoMat as $row) {
  if(!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
    $optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
  } else {
        $optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
    }
}
$dtPeriode=explode("-",$_POST['periode']);
$bulan=$dtPeriode[1];
// Get Nilai Jurnal, keu_saldobulanan
$qSaldoJ = "SELECT awal".$bulan." as saldoawal,noakun
  FROM ".$dbname.".keu_saldobulanan
  WHERE kodeorg='".$_POST['kodeorg']."' and periode='".$dtPeriode[0].$dtPeriode[1]."'
    and noakun in ('".implode("','",$listAkun)."')";
        //echo $qSaldoJ."<p>";
$resSaldoJ = fetchData($qSaldoJ);
$optSaldoJ = array();
foreach($resSaldoJ as $row) {
  $optSaldoJ[$row['noakun']] = $row['saldoawal'];
}

// Get Transaksi Jurnal
$qTrans = "SELECT sum(debet - kredit) as saldotrans, noakun
  FROM ".$dbname.".keu_jurnaldt_vw
  WHERE kodeorg='".$_POST['kodeorg']."' and tanggal>='".$rDt['tanggalmulai']."' and tanggal <='".$rDt['tanggalsampai']."'
    and noakun in ('".implode("','",$listAkun)."')
  GROUP BY noakun";
  //echo $qTrans."<p>";
$resTrans = fetchData($qTrans);
foreach($resTrans as $row) {
  if(!isset($optSaldoJ[$row['noakun']])) 
    $optSaldoJ[$row['noakun']] = 0;
  $optSaldoJ[$row['noakun']] += $row['saldotrans'];
}
/************************************************
* Jurnal Data start *****************************
************************************************/
$kodePt = $_SESSION['org']['kodeorganisasi'];
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
$nmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun','left(noakun,3)=115');
// Cek All Akun
$notBal = "";
foreach($listAkun as $akun) {
  if(!isset($optSaldoMat[$akun])) $optSaldoMat[$akun] = 0;
  if(!isset($optSaldoJ[$akun])) $optSaldoJ[$akun] = 0;

  $selisih = abs( abs($optSaldoMat[$akun]) - abs($optSaldoJ[$akun]) );
    if($selisih > 300) {
        $notBal .= $akun." = ".number_format($selisih)."___".abs($optSaldoMat[$akun])."____".abs($optSaldoJ[$akun])."\n";
        if(($optSaldoMat[$akun])>($optSaldoJ[$akun])){
            $dafSal[$akun]=$selisih;
        }
        if(($optSaldoMat[$akun])<($optSaldoJ[$akun])){
            $dafSal[$akun]=$selisih*(-1);
        }
    }
}
        if(!empty($dafSal)){
                $kodePt=$_SESSION['org']['kodeorganisasi'];
                foreach($dafSal as $rwAkun=>$isiDt){
                        $currTgl=$_POST['periode']."-28";
                        if($isiDt<0){
                                $kodeJurnal = 'INVK4';
                        }elseif($isiDt>0){
                                $kodeJurnal = 'INVM4';

                        }
                        // Parameter Jurnal
                        $qParam = selectQuery($dbname,'keu_5parameterjurnal',"*",
                                                                  "kodeaplikasi='INV' and jurnalid='".$kodeJurnal."'");
                        $resParam = fetchData($qParam);
                        if(empty($resParam)) exit("Warning: Parameter Jurnal ".$kodeJurnal." belum ada.\n".
                                                                          "Silahkan hubungi IT dengan melampirkan pesan error ini");
                        // Get Journal Counter
                        $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                "kodeorg='".$kodePt."' and kodekelompok='".$kodeJurnal."'");
                        $tmpKonter = fetchData($queryJ);
                        if(empty($tmpKonter)) exit("Warning: Kelompok Jurnal ".$kodeJurnal." untuk PT ".$kodePt.
                                                                           " belum ada.\nSilahkan hubungi IT dengan melampirkan pesan error ini");
                        $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                        $nojurnal = str_replace('-','',$currTgl)."/".$_POST['kodeorg']."/".$kodeJurnal."/".$konter;
                                        if($isiDt<0){
                                                $akunKredit=$rwAkun;
                                                $akunDebet=$resParam[0]['noakundebet'];
                                        }elseif($isiDt>0){
                                                $akunDebet=$rwAkun;
                                                $akunKredit=$resParam[0]['sampaikredit'];
                                        }
                            $dataRes['header']= array(
                                        'nojurnal'=>$nojurnal,
                                        'kodejurnal'=>$kodeJurnal,
                                        'tanggal'=>$currTgl,
                                        'tanggalentry'=>date('Ymd'),
                                        'posting'=>'0',
                                        'totaldebet'=>$isiDt,
                                        'totalkredit'=>$isiDt,
                                        'amountkoreksi'=>'0',
                                        'noreferensi'=>'',
                                        'autojurnal'=>'1',
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'revisi'=>'0'
                                );
                            // Detail Debet
                                $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$currTgl,
                                        'nourut'=>1,
                                        'noakun'=>$akunDebet,
                                        'keterangan'=>"Adjusment harga rata-rata material gudang, Nama Kelompok Barangnya ".$nmAkun[$rwAkun],
                                        'jumlah'=>$isiDt<0?$isiDt*(-1):$isiDt,
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$_POST['kodeorg'],
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
                                        'kodesegment' => ''
                                );

                                // Detail Kredit
                                $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$currTgl,
                                        'nourut'=>2,
                                        'noakun'=>$akunKredit,
                                        'keterangan'=>"Adjusment harga rata-rata material gudang, Nama Kelompok Barangnya ".$nmAkun[$rwAkun],
                                        'jumlah'=>$isiDt<0?$isiDt:$isiDt*(-1),
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$_POST['kodeorg'],
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
                                        'kodesegment' => ''
                                );

                                
                        if(!empty($dataRes['header'])){
                                $queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                 try{$owlPDO->exec($queryH); }catch (PDOException $e) {
                                     print " DB Error 1: " . $e->getMessage();
                                     exit();
                                 }
                                if(!empty($dataRes['detail'])){
                                        foreach($dataRes['detail'] as $det) {
                                                $queryD = insertQuery($dbname,'keu_jurnaldt',$det);
                                                try{$owlPDO->exec($queryD); }catch (PDOException $e) {
                                                    print " DB Error 2: " . $e->getMessage();
                                                    rbJournal($dataRes['header']['nojurnal']);
                                                }                                                
                                        }
                                        $sUpdate="update ".$dbname.".keu_5kelompokjurnal set nokounter=".intval($konter)." where kodeorg='".$kodePt."' and kodekelompok='".$kodeJurnal."'";
                                        try{$owlPDO->exec($sUpdate); }
                                        catch (PDOException $e) {
                                                    rbJournal($dataRes['header']['nojurnal']);
                                                    print " Gagal update counter: " . $e->getMessage();
                                                } 
                                }else{
                                        rbJournal($dataRes['header']['nojurnal']);
                                        exit("warning: Detail jurnal Kosong");
                                }

                        }
                        $tmpKonter="";
                        $dataRes['header']=array();
                        $dataRes['detail']=array();
                }
        }


/**
 * rbJournal
 * Rollback Jurnal Transaksi Adjustment
 */
function rbJournal($nojurnal) {
        global $dbname;
        global $owlPDO;
        $qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
        try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Rollback Error: " . $e->getMessage();}
}
/************************************************
* Jurnal Data END *****************************
************************************************/

/**************************************************************
 * [END] Cek Nilai Material VS Jurnal *************************
 **************************************************************/
if(empty($detailErr)) echo "Selesai";