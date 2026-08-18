<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;
$dataorg = array();
$dtstr="select * from ".$dbname.".organisasi where  kodeorganisasi = '".$param['kodeorg']."'";
$str=$owlPDO->query($dtstr);
$str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
    $dataorg[$bar->kodeorganisasi] = $bar;
 }
$tanggal=$param['periode']."-28";

#periode akutansi
$qGaji = selectQuery($dbname,'setup_periodeakuntansi','tanggalmulai,tanggalsampai',
                     "kodeorg='".substr($param['kdsipil'],0,4)."' and periode='".$param['periode']."'");
$resGaji = fetchData($qGaji);
foreach($resGaji as $row) {
  $tglMulai                   = $row['tanggalmulai'];
  $tglSampai                   = $row['tanggalsampai'];
}

// Default Segment
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
if($param['jenis']=='ALK_SIPIL'){
    #hapus dulu alokasi  untuk kendaraan yang sama pada periode yang sama jika sudah pernah di proses:
    $str="select distinct nojurnal from ".$dbname.".keu_jurnaldt where noreferensi='ALK_SIPIL'
              and nodok='".$param['kdrumah']."' and tanggal='".$tanggal."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$bar->nojurnal."'";
        $owlPDO->exec($str);        
    }
    #============================================================================
    prosesBySipil();
}
function prosesBySipil(){
    global $conn;
    global $tanggal;
    global $param;
    global $dbname;
    global $defSegment;
    global $tglMulai;
    global $tglSampai;
    $tipe="";
    #cek apakah rumah/blok
    $whrDt="norumah='".$param['kdrumah']."'";
    $optCek=makeOption($dbname,'sdm_perumahanht','norumah,norumah',$whrDt);
    if(isset($optCek[$param['kdrumah']])){
        $tipe="A";#rumah
    }else{
         $tipe="B";#BLOK
    }

    #cek apakah ada kegiatan di bulan periode akutansi aktif
    $sData="select kodekegiatan,total_hk,alokasi from ".$dbname.".vhc_spl_prestasi a 
                left join ".$dbname.".vhc_splht b on a.notransaksi=b.notransaksi
                where alokasi='".$param['kdrumah']."' and tanggal between '".$tglMulai."' and '".$tglSampai."'";
    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
    $rRowData=owlBaris($qData);
    if($rRowData==0){
        $tgmulaid=$tanggal;  
        #======================== Nomor Jurnal =============================
        # Get Journal Counter
        $kodeJurnal='SIPA1';
        $dtKbn=$param['kodeorg'];
        $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
            "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
        $tmpKonter = fetchData($queryJ);
        $konter = addZero($tmpKonter[0]['nokounter']+1,3);
        $str="select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal
              where jurnalid='".$kodeJurnal."' limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        if(owlBaris($res)<1){
            exit("Error: No.Akun pada parameterjurnal belum ada untuk ".$kodeJurnal);
        }else{
            $akundebet='';
            $akunkredit='';
            $bar=$res->fetch();
            $akundebet=$bar->noakundebet;
            $akunkredit=$bar->noakunkredit;
        } 

        # Transform No Jurnal dari No Transaksi
        $nojurnal = str_replace("-","",$tgmulaid)."/".$dtKbn."/".$kodeJurnal."/".$konter;
        #======================== /Nomor Jurnal ============================
        # Prep Header
        unset($dataRes['header']);//ganti header
            $dataRes['header'] = array(
                    'nojurnal'=>$nojurnal,
                    'kodejurnal'=>$kodeJurnal,
                    'tanggal'=>$tgmulaid,
                    'tanggalentry'=>date('Ymd'),
                    'posting'=>1,
                    'totaldebet'=>$param['jumlah'],
                    'totalkredit'=>-1*$param['jumlah'],
                    'amountkoreksi'=>'0',
                    'noreferensi'=>'ALK_SIPIL',
                    'autojurnal'=>'1',
                    'matauang'=>'IDR',
                    'kurs'=>'1',
                    'revisi'=>'0'                  
                    );   
         # Debet 1
        $noUrut=1;
        $isidt=1;
        $totalRupiah=0;
        unset($dataRes['detail']);//ganti detail 
            # Debet
            $dataRes['detail'][] = array(
                'nojurnal'=>$nojurnal,
                'tanggal'=>$tgmulaid,
                'nourut'=>$noUrut,
                'noakun'=>$akundebet,
                'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                'jumlah'=>$paran['jumlah'],
                'matauang'=>'IDR',
                'kurs'=>'1',
                'kodeorg'=>$dtKbn,
                'kodekegiatan'=>$lstKeg,
                'kodeasset'=>'',
                'kodebarang'=>'',
                'nik'=>'0',
                'kodecustomer'=>'',
                'kodesupplier'=>'',
                'noreferensi'=>'ALK_SIPIL',
                'noaruskas'=>'',
                'kodevhc'=>'',
                'nodok'=>$param['kdrumah'],
                'kodeblok'=>'',
                'revisi'=>'0',
                'kodesegment'=>$defSegment
            );
            $noUrut++;
            # Kredit
            $dataRes['detail'][] = array(
                'nojurnal'=>$nojurnal,
                'tanggal'=>$tgmulaid,
                'nourut'=>$noUrut,
                'noakun'=>$akunkredit,
                'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                'jumlah'=>-1*$param['jumlah'],
                'matauang'=>'IDR',
                'kurs'=>'1',
                'kodeorg'=>$dtKbn,
                'kodekegiatan'=>'',
                'kodeasset'=>'',
                'kodebarang'=>'',
                'nik'=>'',
                'kodecustomer'=>'',
                'kodesupplier'=>'',
                'noreferensi'=>'ALK_SIPIL',
                'noaruskas'=>'',
                'kodevhc'=>'',
                'nodok'=>$param['kdrumah'],
                'kodeblok'=>'',
                'revisi'=>'0',
                'kodesegment'=>$defSegment);
                $noUrut++;
            
            
        
        $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
        try{
            $owlPDO->exec($insHead); 
        }catch (PDOException $e){
            $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
        }


        if(empty($headErr)) {
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
            $detailErr = '';
            foreach($dataRes['detail'] as $row) {
                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                try{
                    $owlPDO->exec($insDet); 
                }catch (PDOException $e){
                    $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                    break;
                }
            }

            if($detailErr=='') {
                # Header and Detail inserted
                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                    "kodeorg='".$ptpengguna.
                    "' and kodekelompok='".$kodeJurnal."'");
                try{
                    $owlPDO->exec($updJurnal); 
                }catch (PDOException $e){
                    echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                    # Rollback if Update Failed
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    try{
                        $owlPDO->exec($RBDet); 
                    }catch (PDOException $e){
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
                }catch (PDOException $e){
                    echo "Rollback Delete Header Error : ".$e->getMessage();
                    exit;
                }
            }
        } else {
            echo $headErr;
            exit;
        }
    }else{
            if($tipe=='A'){
                #cek penghuni rumah
                #1 pengecekan penghuni bekerja pada perawatan
                $totOrgKebun=array();
                $str="select distinct karyawanid from ".$dbname.".kebun_kehadiran_vw 
                      where tanggal between '".$tglMulai."' and '".$tglSampai."' 
                      and karyawanid in (select penghuni from ".$dbname.".sdm_penghuni3_vw where norumah='".$param['kdrumah']."' and tanggalkeluar='0000-00-00')";
                $qstr=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $qstr->setFetchMode(PDO::FETCH_ASSOC);
                while($rstr=$qstr->fetch()){
                    $totOrgKebun[$rstr['karyawanid']]=$rstr['karyawanid'];    
                    $optLksi=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$rstr['karyawanid']."'");
                    $dtPerKebun1[$optLksi[$rstr['karyawanid']]]=$optLksi[$rstr['karyawanid']];
                    $jmlhOrgKbn[$optLksi[$rstr['karyawanid']]][$rstr['karyawanid']]=$rstr['karyawanid'];
                }
               
                #2 pengecekan penghuni bekerja pada panen
                $str2="select distinct karyawanid as karyawanid from ".$dbname.".kebun_prestasi_vw a   
                       where tanggal between '".$tglMulai."' and '".$tglSampai."'
                       and karyawanid in (select penghuni from ".$dbname.".sdm_penghuni3_vw where norumah='".$param['kdrumah']."' and tanggalkeluar='0000-00-00')
                       and notransaksi like '%PNN%'";
                $qstr2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
                $qstr2->setFetchMode(PDO::FETCH_ASSOC);
                while($rstr2=$qstr2->fetch()){
                    $totOrgKebun[$rstr2['karyawanid']]=$rstr2['karyawanid'];     
                    $optLksi=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$rstr2['karyawanid']."'");
                    $dtPerKebun1[$optLksi[$rstr2['karyawanid']]]=$optLksi[$rstr2['karyawanid']];
                    $jmlhOrgKbn[$optLksi[$rstr2['karyawanid']]][$rstr2['karyawanid']]=$rstr2['karyawanid'];     
                }

                #3 pengecekan mandor/krani pada kegiatan bkm operasional
                $totPengawasan=array();
                $lstTipe=array();
                $lstBlok=array();
                $blokAws=array();
                $str3="select distinct nikmandor,nikmandor1,keranimuat,b.kodeorg,a.tipetransaksi  from
                      ".$dbname.".kebun_aktifitas a left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
                      where nikmandor in (select penghuni from ".$dbname.".sdm_penghuni3_vw where norumah='".$param['kdrumah']."' and tanggalkeluar='0000-00-00')
                      or nikmandor1 in (select penghuni from ".$dbname.".sdm_penghuni3_vw where norumah='".$param['kdrumah']."' and tanggalkeluar='0000-00-00')
                      or keranimuat  in (select penghuni from ".$dbname.".sdm_penghuni3_vw where norumah='".$param['kdrumah']."' and tanggalkeluar='0000-00-00')
                      order by a.tipetransaksi asc";
                $qstr3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
                $qstr3->setFetchMode(PDO::FETCH_ASSOC);
                while($rstr3=$qstr3->fetch()){
                    $lstBlok[substr($rstr3['kodeorg'],0,4)][$rstr3['tipetransaksi']][$rstr3['kodeorg']]=$rstr3['kodeorg'];
                    $lstTipe[$rstr3['tipetransaksi']]=$rstr3['tipetransaksi'];
                    $blokAws[substr($rstr3['kodeorg'],0,4)][$rstr3['kodeorg']]=$rstr3['kodeorg'];
                    if($rstr3['kodeorg']!=''){
                        $totBlok+=1;    
                    }
                    
                    if($totOrgKebun[$rstr3['nikmandor']]!=''){
                        continue;
                    }else{
                        if($rstr3['nikmandor']!=''){
                            $totPengawasan[$rstr3['nikmandor']]=$rstr3['nikmandor'];
                            $optLksi=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$rstr3['nikmandor']."'");
                            $dtPerKebun3[$optLksi[$rstr3['nikmandor']]]=$optLksi[$rstr3['nikmandor']];
                            //$jmlhOrgPeng[$optLksi[$rstr3['nikmandor']]]+=1;  
                            $jmlhOrgPeng[$optLksi[$rstr3['nikmandor']]][$rstr3['nikmandor']]=$rstr3['nikmandor'];      
                        }
                    }
                    if($totOrgKebun[$rstr3['nikmandor1']]!=''){
                        continue;
                    }else{
                        if($rstr3['nikmandor1']!=''){
                            $totPengawasan[$rstr3['nikmandor1']]=$rstr3['nikmandor1'];
                            $optLksi=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$rstr3['nikmandor1']."'");
                            $dtPerKebun3[$optLksi[$rstr3['nikmandor1']]]=$optLksi[$rstr3['nikmandor1']];
                            //$jmlhOrgPeng[$optLksi[$rstr3['nikmandor1']]]+=1;   
                            $jmlhOrgPeng[$optLksi[$rstr3['keranimuat']]][$rstr3['nikmandor1']]=$rstr3['nikmandor1'];      
                        }
                    }
                    if($totOrgKebun[$rstr3['keranimuat']]!=''){
                        continue;
                    }else{
                        if($rstr3['keranimuat']!=''){
                            $totPengawasan[$rstr3['keranimuat']]=$rstr3['keranimuat'];
                            $optLksi=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$rstr3['keranimuat']."'");
                            $dtPerKebun3[$optLksi[$rstr3['keranimuat']]]=$optLksi[$rstr3['keranimuat']];
                            $jmlhOrgPeng[$optLksi[$rstr3['keranimuat']]][$rstr3['keranimuat']]=$rstr3['keranimuat'];  
                        }
                    }
                }
                
                #4 pengecekan operator 
                $totOrgOperator=array();
                $str4="select karyawanid,vhc from ".$dbname.".vhc_5operator where karyawanid in (select penghuni from ".$dbname.".sdm_penghuni3_vw where norumah='".$param['kdrumah']."' and tanggalkeluar='0000-00-00')";
                $qstr4=$owlPDO->query($str4) or die(print " Gagal: ".PDOException::getMessage());
                $qstr4->setFetchMode(PDO::FETCH_ASSOC);
                while($rstr4=$qstr4->fetch()){
                    if(!empty($totOrgKebun[$rstr4['karyawanid']])){
                        continue;
                    }else if(!empty($totPengawasan[$rstr4['karyawanid']])){
                     continue;   
                    }else{
                        $totOrgOperator[$rstr4['karyawanid']]=$rstr4['karyawanid']; 
                        $optLksi=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$rstr4['karyawanid']."'");
                        $dtPerKebun4[$optLksi[$rstr4['karyawanid']]]=$optLksi[$rstr4['karyawanid']];
                        $jmlhOrgOprtr[$optLksi[$rstr4['karyawanid']]][$rstr4['karyawanid']]=$rstr4['karyawanid'];  
                        $lstVhc[$optLksi[$rstr4['karyawanid']]][$rstr4['vhc']]=$rstr4['vhc'];  
                    }  
                }
                        

                #5 pengecekan subbagian=stengine/maintenance
                $totOrgkntr=array();
                $str5="select distinct karyawanid,subbagian from ".$dbname.".sdm_gaji a left join ".$dbname.".sdm_penghuni3_vw  b 
                       on a.karyawanid=b.penghuni where norumah='".$param['kdrumah']."' and periodegaji='".$param['periode']."'";
                $qstr5=$owlPDO->query($str5) or die(print " Gagal: ".PDOException::getMessage());
                $qstr5->setFetchMode(PDO::FETCH_ASSOC);
                while($rstr5=$qstr5->fetch()){
                    if(!empty($totOrgKebun[$rstr5['karyawanid']])){
                        continue;
                    }else if(!empty($totPengawasan[$rstr5['karyawanid']])){
                     continue;   
                    }else if(!empty($totOrgOperator[$rstr5['karyawanid']])){
                       continue;
                    }else{
                        $whrdt="kodeorganisasi='".$rstr5['subbagian']."'";
                        $optdt=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whrdt);
                        $optLksi=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$rstr5['karyawanid']."'");
                        $dtPerKebun5[$optLksi[$rstr5['karyawanid']]]=$optLksi[$rstr5['karyawanid']];
                        $dafSubbagian[$optLksi[$rstr5['karyawanid']]][$rstr5['subbagian']]=$rstr5['subbagian'];
                        if($optdt[$rstr5['subbagian']]=='STENGINE'){
                            $totOrgEngine[$rstr5['karyawanid']]=$rstr5['karyawanid'];
                            $jmlhOrgEngn[$optLksi[$rstr5['karyawanid']]][$rstr5['karyawanid']]=$rstr5['karyawanid'];
                        }else if($optdt[$rstr5['subbagian']]=='MAINTENANCE'){
                            $totOrgMaintenance[$rstr5['karyawanid']]=$rstr5['karyawanid'];
                            $jmlhOrgMain[$optLksi[$rstr5['karyawanid']]][$rstr5['karyawanid']]=$rstr5['karyawanid'];
                        }else{
                            $totOrgkntr[$rstr5['karyawanid']]=$rstr5['karyawanid'];
                            $jmlhOrgKntr[$optLksi[$rstr5['karyawanid']]][$rstr5['karyawanid']]=$rstr5['karyawanid'];
                        }
                    } 
                }
                #total penghuni
                $totPenghuni=count($totOrgKebun)+count($totPengawasan)+count($totOrgOperator)+count($totOrgEngine)+count($totOrgMaintenance)+count($totOrgkntr);

                #porporsi penghuni di sesuaikan dengan aktifitasnya
                if(count($totOrgKebun)!=0){
                    @$rpKebun=(count($totOrgKebun)/$totPenghuni)*($param['jumlah']*$param['jmlhhk']);
                    foreach($dtPerKebun1 as $dtKbn){
                        $rpPerKebun[$dtKbn]=(count($jmlhOrgKbn[$dtKbn])/count($totOrgKebun))*$rpKebun;
                    }
                }
                if(count($totPengawasan)!=0){
                    @$rpPengawasan=(count($totPengawasan)/$totPenghuni)*($param['jumlah']*$param['jmlhhk']);
                    foreach($dtPerKebun3 as $dtKbn){
                        @$rpPerPengawasan[$dtKbn]=(count($jmlhOrgPeng[$dtKbn])/count($totPengawasan))*$rpPengawasan;
                    }
                }
                if(count($totOrgOperator)!=0){
                    @$rpOperator=(count($totOrgOperator)/$totPenghuni)*($param['jumlah']*$param['jmlhhk']);
                    foreach($dtPerKebun4 as $dtKbn){
                        @$rpPerOperator[$dtKbn]=(count($jmlhOrgOprtr[$dtKbn])/count($totOrgOperator))*$rpOperator;
                    }
                }
                if(count($totOrgEngine)!=0){
                    @$rpEngine=(count($totOrgEngine)/$totPenghuni)*($param['jumlah']*$param['jmlhhk']);
                    foreach($dtPerKebun5 as $dtKbn){
                        @$rpPerEngine[$dtKbn]=(count($jmlhOrgEngn[$dtKbn])/count($totOrgEngine))*$rpEngine;
                    }
                }
                if(count($totOrgMaintenance)!=0){
                    @$rpMainten=(count($totOrgMaintenance)/$totPenghuni)*($param['jumlah']*$param['jmlhhk']);
                    foreach($dtPerKebun5 as $dtKbn){
                        @$rpPerMainten[$dtKbn]=(count($jmlhOrgMain[$dtKbn])/count($totOrgMaintenance))*$rpMainten;
                    }
                }
                if(count($totOrgkntr)!=0){
                    @$rpKantor=(count($totOrgkntr)/$totPenghuni)*($param['jumlah']*$param['jmlhhk']);
                    foreach($dtPerKebun5 as $dtKbn){
                        @$rpPerKantor[$dtKbn]=(count($jmlhOrgKntr[$dtKbn])/count($totOrgkntr))*$rpKantor;
                    }
                }
                
                //exit('warning :'.$rpKebun."=1____2=".$rpPengawasan."___3=".$rpOperator."___4=".$rpEngine."___5=".$rpMainten."__6=".$rpKantor);
                if($rpKebun!=0){
                    #periksa di perawatan
                    $str="select distinct b.kodekegiatan,b.kodeorg,c.noakun,b.kodesegment,a.tanggal,a.unit from ".$dbname.".kebun_kehadiran_vw a 
                          left join ".$dbname.".kebun_perawatan_vw b on a.notransaksi=b.notransaksi 
                          left join ".$dbname.".setup_kegiatan c on b.kodekegiatan=c.kodekegiatan    
                          where a.tanggal between '".$tglMulai."' and '".$tglSampai."'
                          and a.karyawanid in (select penghuni from ".$dbname.".sdm_penghuni3_vw where norumah='".$param['kdrumah']."' and tanggalkeluar='0000-00-00')
                          having noakun!=''";
                    $res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res1->setFetchMode(PDO::FETCH_ASSOC);
                    $res=$res1;
                    $tempBlok="";
                    $tmpKegiatan="";
                    $jmlhKeg=array();
                    $dtSegment=array();
                    $dtNoakun=array();
                    while($rData=$res1->fetch()){
                        if(($tempBlok!=$rData['kodeorg'])&&($tmpKegiatan!=$rData['kodekegiatan'])){
                            $rData['kodesegment']="";
                            $tempBlok=$rData['kodeorg'];
                            $tmpKegiatan=$rData['kodekegiatan'];
                            $jmlhKeg[$rData['unit']][$rData['kodeorg'].$rData['kodekegiatan']]=1;
                            $dtSegment[$rData['unit']][$rData['kodeorg'].$rData['kodekegiatan']]=$rData['kodekegiatan'];
                            $dtNoakun[$rData['unit']][$rData['kodeorg'].$rData['kodekegiatan']]=$rData['noakun'];
                        }else{
                            $jmlhKeg[$rData['unit']][$rData['kodeorg'].$rData['kodekegiatan']]+=1;
                            $dtSegment[$rData['unit']][$rData['kodeorg'].$rData['kodekegiatan']]=$rData['kodekegiatan'];
                        }
                        $lstBlok[$rData['unit']][$rData['kodeorg']]=$rData['kodeorg'];
                        $lstKegiatan[$rData['unit']][$rData['kodekegiatan']]=$rData['kodekegiatan'];
                        $totBrsKeg[$rData['unit']]+=1;
                    }
                    
                    
                    #panen
                    $str2="select distinct unit,kodeorg,kodesegment,tanggal,unit from ".$dbname.".kebun_prestasi_vw 
                           where tanggal between '".$tglMulai."' and '".$tglSampai."'
                           and karyawanid in(select penghuni from ".$dbname.".sdm_penghuni3_vw where norumah='".$param['kdrumah']."' and tanggalkeluar='0000-00-00')
                           and notransaksi like '%PNN%'";
                    $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
                    $res2->setFetchMode(PDO::FETCH_ASSOC);
                    while($rjum=$res2->fetch()){
                        $jum[$rjum['unit']]+=1;
                        $blkPanen[$rjum['unit']][$rjum['kodeorg']]=$rjum['kodeorg'];
                    }

                    #BUAT JURNAL
                    foreach($dtPerKebun1 as $dtKbn){
                            $whrPt="kodeorganisasi='".$dtKbn."'";
                            $optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',$whrPt);
                            if($optPt[$dtKbn]!=$_SESSION['org']['kodeorganisasi']){
                                $stat="inter";//beda pt
                            }else if($param['kodeorg']!=$dtKbn){#cek jika satu lokasi tugas
                                $stat="intra";//satu pt beda lokasi
                            }else{
                                $stat="satu";//satu lokasi
                            }
                            #rupiah proporsi
                            if($totBrsKeg[$dtKbn]!=0){
                                #porsi perawatan
                                @$porsiPerawatan[$dtKbn]=$totBrsKeg[$dtKbn]/($totBrsKeg[$dtKbn]+count($blkPanen[$dtKbn]))*$rpPerKebun[$dtKbn];
                            }

                            if(count($blkPanen[$dtKbn])!=0){
                                #porsi panen
                                @$porsiPanen[$dtKbn]=count($blkPanen[$dtKbn])/($totBrsKeg[$dtKbn]+count($blkPanen[$dtKbn]))*$rpPerKebun[$dtKbn];
                            }
                            #Periksa apakah unit tujuan sudah tutup buku:
                            $str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$param['periode']."' 
                                       and kodeorg='".$dtKbn."'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $close='0';
                            while($bar=$res->fetch()){
                                $close=$bar->tutupbuku;
                            }
                            if($close=='1'){
                                exit(" Error: Unit ".$dtKbn.' has been closed');
                            }
                             
                            switch($stat){
                                case'satu':
                                    if($porsiPerawatan[$dtKbn]!=0){
                                        #======================== Nomor Jurnal =============================
                                        $kodeJurnal = 'SIPL9';
                                        $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakundebet',
                                        " jurnalid='".$kodeJurnal."'");
                                            $resParam = fetchData($queryParam);
                                        $akunkredit=$resParam[0]['noakundebet']; 
                                            
                                        # Get Journal Counter
                                        $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                            "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
                                        $tmpKonter = fetchData($queryJ);
                                        $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                                        # Transform No Jurnal dari No Transaksi
                                        $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
                                        #======================== Nomor Jurnal =============================
                                        # Prep Header
                                        unset($dataRes['header']);//ganti header
                                        $dataRes['header'] = array(
                                                'nojurnal'=>$nojurnal,
                                                'kodejurnal'=>$kodeJurnal,
                                                'tanggal'=>$tgmulaid,
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>$porsiPerawatan[$dtKbn],
                                                'totalkredit'=>-1*$porsiPerawatan[$dtKbn],
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>'ALK_SIPIL',
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'revisi'=>'0'                  
                                                );   
                                         # Debet 1
                                        $noUrut=1;
                                        $totalRupiah=0;
                                        $jmlhDt=0;
                                        foreach($lstBlok[$dtKbn] as $brsBlok=>$kodeBlok){
                                            foreach($lstKegiatan[$dtKbn] as $brsKeg=>$kodeKegiatan){
                                                if(count($jmlhKeg[$dtKbn][$kodeBlok.$kodeKegiatan])!=0){
                                                    $param['jumlahdet']=($jmlhKeg[$dtKbn][$kodeBlok.$kodeKegiatan]/$totBrsKeg[$dtKbn]*$porsiPerawatan[$dtKbn]);                                
                                                    $totalRupiah+=$param['jumlahdet'];
                                                    $jmlhDt+=$jmlhKeg[$dtKbn][$kodeBlok.$kodeKegiatan];
                                                    # Debet
                                                    $dataRes['detail'][] = array(
                                                        'nojurnal'=>$nojurnal,
                                                        'tanggal'=>$tgmulaid,
                                                        'nourut'=>$noUrut,
                                                        'noakun'=>substr($kodeKegiatan,0,7),
                                                        'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                                        'jumlah'=>$param['jumlahdet'],
                                                        'matauang'=>'IDR',
                                                        'kurs'=>'1',
                                                        'kodeorg'=>$dtKbn,
                                                        'kodekegiatan'=>$kodeKegiatan,
                                                        'kodeasset'=>'',
                                                        'kodebarang'=>'',
                                                        'nik'=>'0',
                                                        'kodecustomer'=>'',
                                                        'kodesupplier'=>'',
                                                        'noreferensi'=>'ALK_SIPIL',
                                                        'noaruskas'=>'',
                                                        'kodevhc'=>'',
                                                        'nodok'=>$param['kdrumah'],
                                                        'kodeblok'=>$kodeBlok,
                                                        'revisi'=>'0',
                                                        'kodesegment'=>$defSegment
                                                    );
                                                    $noUrut++;
                                                    if($totBrsKeg[$dtKbn]==$jmlhDt){
                                                        # Kredit
                                                        $dataRes['detail'][] = array(
                                                        'nojurnal'=>$nojurnal,
                                                        'tanggal'=>$tgmulaid,
                                                        'nourut'=>$noUrut,
                                                        'noakun'=>$akunkredit,
                                                        'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                                                        'jumlah'=>-1*$totalRupiah,
                                                        'matauang'=>'IDR',
                                                        'kurs'=>'1',
                                                        'kodeorg'=>$dtKbn,
                                                        'kodekegiatan'=>'',
                                                        'kodeasset'=>'',
                                                        'kodebarang'=>'',
                                                        'nik'=>'',
                                                        'kodecustomer'=>'',
                                                        'kodesupplier'=>'',
                                                        'noreferensi'=>'ALK_SIPIL',
                                                        'noaruskas'=>'',
                                                        'kodevhc'=>'',
                                                        'nodok'=>$param['kdrumah'],
                                                        'kodeblok'=>'',
                                                        'revisi'=>'0',
                                                        'kodesegment'=>$defSegment);
                                                        $noUrut++;
                                                    }

                                                }
                                            }
                                        }//end foreach $lstBlok
                                        $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                        try{
                                            $owlPDO->exec($insHead); 
                                        }catch (PDOException $e){
                                            $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
                                        }

                                        if(empty($headErr)) {
                                            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                            $detailErr = '';
                                            foreach($dataRes['detail'] as $row) {
                                                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                                try{
                                                    $owlPDO->exec($insDet); 
                                                }catch (PDOException $e){
                                                    $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                                                    break;
                                                }
                                            }

                                            if($detailErr=='') {
                                                # Header and Detail inserted
                                                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                                                $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                                    "kodeorg='".$ptpengguna.
                                                    "' and kodekelompok='".$kodeJurnal."'");
                                                try{
                                                    $owlPDO->exec($updJurnal); 
                                                }catch (PDOException $e){
                                                    echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                                    # Rollback if Update Failed
                                                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                                    try{
                                                        $owlPDO->exec($RBDet); 
                                                    }catch (PDOException $e){
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
                                                }catch (PDOException $e){
                                                    echo "Rollback Delete Header Error : ".$e->getMessage();
                                                    exit;
                                                }
                                            }
                                        } else {
                                            echo $headErr;
                                            exit;
                                        }
                                        
                                    }//end porsi perawatan case:'satu'
                                    if($porsiPanen[$dtKbn]!=0){
                                            #======================== Nomor Jurnal =============================
                                            $kodejurnal = 'PNN01';#panen
                                            $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',
                                            " jurnalid='".$kodejurnal."'");
                                                $resParam = fetchData($queryParam);
                                                
                                            $kegpanen=$resParam[0]['noakundebet']."01";

                                            $kodeJurnal = 'SIPL9';
                                            $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakundebet',
                                            " jurnalid='".$kodeJurnal."'");
                                                $resParam = fetchData($queryParam);
                                            $akunkredit=$resParam[0]['noakundebet']; 
                                            # Get Journal Counter
                                            $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                                        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
                                            $tmpKonter = fetchData($queryJ);
                                            $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                                            # Transform No Jurnal dari No Transaksi
                                            $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
                                            #======================== Nomor Jurnal =============================
                                            # Prep Header
                                            unset($dataRes['header']);//ganti header
                                            $dataRes['header'] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'kodejurnal'=>$kodeJurnal,
                                                    'tanggal'=>$tgmulaid,
                                                    'tanggalentry'=>date('Ymd'),
                                                    'posting'=>1,
                                                    'totaldebet'=>$porsiPanen[$dtKbn],
                                                    'totalkredit'=>-1*$porsiPanen[$dtKbn],
                                                    'amountkoreksi'=>'0',
                                                    'noreferensi'=>'ALK_SIPIL',
                                                    'autojurnal'=>'1',
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'revisi'=>'0'                  
                                                    );   
                                            # Debet 1
                                            $noUrut=1;
                                            unset($dataRes['detail']);//ganti header 
                                            #$blkPanen ada di line 253
                                            $isidt=1;
                                            $totalRupiah=0;
                                            foreach($blkPanen[$dtKbn] as $lstBlokPanen){
                                                $param['jumlahdet']=($porsiPanen[$dtKbn]/count($blkPanen[$dtKbn]));
                                                $totalRupiah+=$param['jumlahdet'];
                                                # Debet
                                                $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>$tgmulaid,
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>substr($kegpanen,0,7),
                                                    'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                                    'jumlah'=>$param['jumlahdet'],
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>$dtKbn,
                                                    'kodekegiatan'=>$kegpanen,
                                                    'kodeasset'=>'',
                                                    'kodebarang'=>'',
                                                    'nik'=>'0',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>'ALK_SIPIL',
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>'',
                                                    'nodok'=>$param['kdrumah'],
                                                    'kodeblok'=>$lstBlokPanen,
                                                    'revisi'=>'0',
                                                    'kodesegment'=>$defSegment
                                                );
                                                $noUrut++;
                                                if(count($blkPanen[$dtKbn])==$isidt){
                                                    # Kredit
                                                    $dataRes['detail'][] = array(
                                                    'nojurnal'=>$nojurnal,
                                                    'tanggal'=>$tgmulaid,
                                                    'nourut'=>$noUrut,
                                                    'noakun'=>$akunkredit,
                                                    'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                                                    'jumlah'=>-1*$totalRupiah,
                                                    'matauang'=>'IDR',
                                                    'kurs'=>'1',
                                                    'kodeorg'=>$dtKbn,
                                                    'kodekegiatan'=>'',
                                                    'kodeasset'=>'',
                                                    'kodebarang'=>'',
                                                    'nik'=>'',
                                                    'kodecustomer'=>'',
                                                    'kodesupplier'=>'',
                                                    'noreferensi'=>'ALK_SIPIL',
                                                    'noaruskas'=>'',
                                                    'kodevhc'=>'',
                                                    'nodok'=>$param['kdrumah'],
                                                    'kodeblok'=>'',
                                                    'revisi'=>'0',
                                                    'kodesegment'=>$defSegment);
                                                    $noUrut++;
                                                }
                                                $isidt+=1;
                                            }//end foreach list blok panen
                                    }//end porsi panen case:'satu'
                                break;
                                case 'intra':
                                case 'inter':
                                    $noUrut = 1;
                                    $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$dtKbn."' and jenis='".$stat."'";
                                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                                    $res->setFetchMode(PDO::FETCH_OBJ);
                                    $bar=$res->fetch();
                                    $akunRk=$bar->akunhutang;//pengguna

                                    if ($akunRk=='') {
                                        exit("Warning : Account intraco or interco not available for ".$dtKbn.". Please setting on menu Finance > setup > COA for Intra/Interco.");
                                    } 

                                    $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$param['kodeorg']."' and jenis='".$stat."'";
                                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                                    $res->setFetchMode(PDO::FETCH_OBJ);
                                    $bar=$res->fetch();
                                    $akunDt=$bar->akunhutang;//pemilik anggota sipil

                                    if ($akunDt=='') {
                                        exit("Warning : Account intraco or interco not available for ".$param['kodeorg'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
                                    } 
                                    
                                #jika tidak dalam satu unit kerja/beda pt maka akan ada hubungan RK
                                #======================== Nomor Jurnal =============================
                                $kodeJurnal = 'SIPL9';
                                $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakundebet',
                                " jurnalid='".$kodeJurnal."'");
                                    $resParam = fetchData($queryParam);
                                  $akunkredit=$resParam[0]['noakundebet']; 
                                    
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
                                $tmpKonter = fetchData($queryJ);
                                $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                                if($porsiPerawatan[$dtKbn]!=0){
                                        # Transform No Jurnal dari No Transaksi
                                        $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
                                        #======================== Nomor Jurnal =============================
                                        # Prep Header
                                        $dataRes['header'] = array(
                                            'nojurnal'=>$nojurnal,
                                            'kodejurnal'=>$kodeJurnal,
                                            'tanggal'=>$tanggal,
                                            'tanggalentry'=>date('Ymd'),
                                            'posting'=>1,
                                            'totaldebet'=>$porsiPerawatan[$dtKbn],
                                            'totalkredit'=>-1*$porsiPerawatan[$dtKbn],
                                            'amountkoreksi'=>'0',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'autojurnal'=>'1',
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'revisi'=>'0'                    
                                            );
                                        # Debet
                                        $dataRes['detail'][] = array(
                                            'nojurnal'=>$nojurnal,
                                            'tanggal'=>$tanggal,
                                            'nourut'=>$noUrut,
                                            'noakun'=>$akunRk,
                                            'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                            'jumlah'=>$porsiPerawatan[$dtKbn],
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'kodeorg'=>$param['kodeorg'],
                                            'kodekegiatan'=>'',
                                            'kodeasset'=>'',
                                            'kodebarang'=>'',
                                            'nik'=>'0',
                                            'kodecustomer'=>'',
                                            'kodesupplier'=>'',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'noaruskas'=>'',
                                            'kodevhc'=>'',
                                            'nodok'=>$param['kdrumah'],
                                            'kodeblok'=>'',
                                            'revisi'=>'0',
                                            'kodesegment'=>$defSegment
                                        );
                                        $noUrut++;

                                        # Kredit
                                        $dataRes['detail'][] = array(
                                            'nojurnal'=>$nojurnal,
                                            'tanggal'=>$tanggal,
                                            'nourut'=>$noUrut,
                                            'noakun'=>$akunkredit,
                                            'keterangan'=>$param['periode'].':Alokasi biaya rumah'.$param['kdrumah'],
                                            'jumlah'=>-1*$porsiPerawatan[$dtKbn],
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'kodeorg'=>$param['kodeorg'],
                                            'kodekegiatan'=>'',
                                            'kodeasset'=>'',
                                            'kodebarang'=>'',
                                            'nik'=>'0',
                                            'kodecustomer'=>'',
                                            'kodesupplier'=>'',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'noaruskas'=>'',
                                            'kodevhc'=>'',
                                            'nodok'=>$param['kdrumah'],
                                            'kodeblok'=>'',
                                            'revisi'=>'0',
                                            'kodesegment'=>$defSegment
                                        );
                                       $noUrut++;
                                       $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                        try{
                                            $owlPDO->exec($insHead); 
                                        }catch (PDOException $e){
                                            $headErr .= 'Insert Header Ex.Self Error : '.$e->getMessage()."\n";
                                        }

                                        if(empty($headErr)) {
                                            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                            $detailErr = '';
                                            foreach($dataRes['detail'] as $row) {
                                                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                                try{
                                                    $owlPDO->exec($insDet); 
                                                }catch (PDOException $e){
                                                    $detailErr .= "Insert Detail Ex.Self Error : ".$e->getMessage()."\n";
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
                                                }catch (PDOException $e){
                                                    echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                                    # Rollback if Update Failed
                                                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                                    try{
                                                        $owlPDO->exec($RBDet); 
                                                    }catch (PDOException $e){
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
                                                }catch (PDOException $e){
                                                    echo "Rollback Delete Header Error : ".$e->getMessage();
                                                    exit;
                                                }
                                            }
                                        } else {
                                            echo $headErr;
                                            exit;
                                        }  
                                        #+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
                                        #sisi Pengguna
                                        $tgmulaid=$tanggal;  

                                        #======================== Nomor Jurnal =============================
                                        # Get Journal Counter
                                        $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                            "kodeorg='".$optPt[$dtKbn]."' and kodekelompok='".$kodeJurnal."' ");
                                        $tmpKonter = fetchData($queryJ);
                                        $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                                        # Transform No Jurnal dari No Transaksi
                                        $nojurnal = str_replace("-","",$tgmulaid)."/".$dtKbn."/".$kodeJurnal."/".$konter;
                                        #======================== /Nomor Jurnal ============================
                                        # Prep Header
                                        unset($dataRes['header']);//ganti header
                                        $dataRes['header'] = array(
                                                'nojurnal'=>$nojurnal,
                                                'kodejurnal'=>$kodeJurnal,
                                                'tanggal'=>$tgmulaid,
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>$porsiPerawatan[$dtKbn],
                                                'totalkredit'=>-1*$porsiPerawatan[$dtKbn],
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>'ALK_SIPIL',
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'revisi'=>'0'                  
                                                );   
                                         # Debet 1
                                        $noUrut=1;
                                        unset($dataRes['detail']);//ganti header 
                                        $totalRupiah=0;
                                        $jmlhDt=0;
                                        foreach($lstBlok[$dtKbn] as $brsBlok=>$kodeBlok){
                                            foreach($lstKegiatan[$dtKbn] as $brsKeg=>$kodeKegiatan){
                                                if(count($jmlhKeg[$dtKbn][$kodeBlok.$kodeKegiatan])!=0){
                                                    $param['jumlahdet']=($jmlhKeg[$dtKbn][$kodeBlok.$kodeKegiatan]/$totBrsKeg[$dtKbn]*$porsiPerawatan[$dtKbn]);                                
                                                    $totalRupiah+=$param['jumlahdet'];
                                                    $jmlhDt+=$jmlhKeg[$dtKbn][$kodeBlok.$kodeKegiatan];
                                                    # Debet
                                                    $dataRes['detail'][] = array(
                                                        'nojurnal'=>$nojurnal,
                                                        'tanggal'=>$tgmulaid,
                                                        'nourut'=>$noUrut,
                                                        'noakun'=>substr($kodeKegiatan,0,7),
                                                        'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                                        'jumlah'=>$param['jumlahdet'],
                                                        'matauang'=>'IDR',
                                                        'kurs'=>'1',
                                                        'kodeorg'=>$dtKbn,
                                                        'kodekegiatan'=>$kodeKegiatan,
                                                        'kodeasset'=>'',
                                                        'kodebarang'=>'',
                                                        'nik'=>'0',
                                                        'kodecustomer'=>'',
                                                        'kodesupplier'=>'',
                                                        'noreferensi'=>'ALK_SIPIL',
                                                        'noaruskas'=>'',
                                                        'kodevhc'=>'',
                                                        'nodok'=>$param['kdrumah'],
                                                        'kodeblok'=>$kodeBlok,
                                                        'revisi'=>'0',
                                                        'kodesegment'=>$defSegment
                                                    );
                                                    $noUrut++;
                                                    if($totBrsKeg[$dtKbn]==$jmlhDt){
                                                        # Kredit
                                                        $dataRes['detail'][] = array(
                                                        'nojurnal'=>$nojurnal,
                                                        'tanggal'=>$tgmulaid,
                                                        'nourut'=>$noUrut,
                                                        'noakun'=>$akunDt,
                                                        'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                                                        'jumlah'=>-1*$totalRupiah,
                                                        'matauang'=>'IDR',
                                                        'kurs'=>'1',
                                                        'kodeorg'=>$dtKbn,
                                                        'kodekegiatan'=>'',
                                                        'kodeasset'=>'',
                                                        'kodebarang'=>'',
                                                        'nik'=>'',
                                                        'kodecustomer'=>'',
                                                        'kodesupplier'=>'',
                                                        'noreferensi'=>'ALK_SIPIL',
                                                        'noaruskas'=>'',
                                                        'kodevhc'=>'',
                                                        'nodok'=>$param['kdrumah'],
                                                        'kodeblok'=>'',
                                                        'revisi'=>'0',
                                                        'kodesegment'=>$defSegment);
                                                        $noUrut++;
                                                    }

                                                }
                                            }
                                        }
                                        $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                        try{
                                            $owlPDO->exec($insHead); 
                                        }catch (PDOException $e){
                                            $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
                                        }

                                        if(empty($headErr)) {
                                            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                            $detailErr = '';
                                            foreach($dataRes['detail'] as $row) {
                                                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                                try{
                                                    $owlPDO->exec($insDet); 
                                                }catch (PDOException $e){
                                                    $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                                                    break;
                                                }
                                            }

                                            if($detailErr=='') {
                                                # Header and Detail inserted
                                                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                                                $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                                    "kodeorg='".$ptpengguna.
                                                    "' and kodekelompok='".$kodeJurnal."'");
                                                try{
                                                    $owlPDO->exec($updJurnal); 
                                                }catch (PDOException $e){
                                                    echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                                    # Rollback if Update Failed
                                                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                                    try{
                                                        $owlPDO->exec($RBDet); 
                                                    }catch (PDOException $e){
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
                                                }catch (PDOException $e){
                                                    echo "Rollback Delete Header Error : ".$e->getMessage();
                                                    exit;
                                                }
                                            }
                                        } else {
                                            echo $headErr;
                                            exit;
                                        }
                                }//end if prosi perawatan case:'inter'
                                if($porsiPanen[$dtKbn]!=0){
                                    
                                    #======================== Nomor Jurnal =============================
                                    $kodejurnal = 'PNN01';#panen
                                    $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',
                                    " jurnalid='".$kodejurnal."'");
                                        $resParam = fetchData($queryParam);
                                        
                                    $kegpanen=$resParam[0]['noakundebet']."01";

                                    $kodeJurnal = 'SIPL9';
                                    # Get Journal Counter
                                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                                "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
                                    $tmpKonter = fetchData($queryJ);
                                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                                    # Transform No Jurnal dari No Transaksi
                                    $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
                                    #======================== Nomor Jurnal =============================
                                    # Prep Header
                                    unset($dataRes['header']);//ganti header
                                    $dataRes['header'] = array(
                                        'nojurnal'=>$nojurnal,
                                        'kodejurnal'=>$kodeJurnal,
                                        'tanggal'=>$tanggal,
                                        'tanggalentry'=>date('Ymd'),
                                        'posting'=>1,
                                        'totaldebet'=>$porsiPanen[$dtKbn],
                                        'totalkredit'=>-1*$porsiPanen[$dtKbn],
                                        'amountkoreksi'=>'0',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'autojurnal'=>'1',
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'revisi'=>'0'                    
                                        );
                                    $noUrut=1;
                                    unset($dataRes['detail']);//ganti detail
                                    # Debet
                                    $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$tanggal,
                                        'nourut'=>$noUrut,
                                        'noakun'=>$akunRk,
                                        'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                        'jumlah'=>$porsiPanen[$dtKbn],
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$param['kodeorg'],
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>'',
                                        'nik'=>'0',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'noaruskas'=>'',
                                        'kodevhc'=>'',
                                        'nodok'=>$param['kdrumah'],
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment'=>$defSegment
                                    );
                                    $noUrut++;

                                    # Kredit
                                    $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$tanggal,
                                        'nourut'=>$noUrut,
                                        'noakun'=>$akunkredit,
                                        'keterangan'=>$param['periode'].':Alokasi biaya rumah'.$param['kdrumah']." panen",
                                        'jumlah'=>-1*$porsiPanen[$dtKbn],
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$param['kodeorg'],
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>'',
                                        'nik'=>'0',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'noaruskas'=>'',
                                        'kodevhc'=>'',
                                        'nodok'=>$param['kdrumah'],
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment'=>$defSegment
                                    );
                                   $noUrut++;
                                   $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                    try{
                                        $owlPDO->exec($insHead); 
                                    }catch (PDOException $e){
                                        $headErr .= 'Insert Header Ex.Self Error : '.$e->getMessage()."\n";
                                    }

                                    if(empty($headErr)) {
                                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                        $detailErr = '';
                                        foreach($dataRes['detail'] as $row) {
                                            $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                            try{
                                                $owlPDO->exec($insDet); 
                                            }catch (PDOException $e){
                                                $detailErr .= "Insert Detail Ex.Self Error : ".$e->getMessage()."\n";
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
                                            }catch (PDOException $e){
                                                echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                                # Rollback if Update Failed
                                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                                try{
                                                    $owlPDO->exec($RBDet); 
                                                }catch (PDOException $e){
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
                                            }catch (PDOException $e){
                                                echo "Rollback Delete Header Error : ".$e->getMessage();
                                                exit;
                                            }
                                        }
                                    } else {
                                        echo $headErr;
                                        exit;
                                    }  
                                    #+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
                                    #sisi Pengguna
                                    $tgmulaid=$tanggal;  

                                    #======================== Nomor Jurnal =============================
                                    # Get Journal Counter
                                    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                        "kodeorg='".$optPt[$dtKbn]."' and kodekelompok='".$kodeJurnal."' ");
                                    $tmpKonter = fetchData($queryJ);
                                    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                                    # Transform No Jurnal dari No Transaksi
                                    $nojurnal = str_replace("-","",$tgmulaid)."/".$dtKbn."/".$kodeJurnal."/".$konter;
                                    #======================== /Nomor Jurnal ============================
                                    # Prep Header
                                    unset($dataRes['header']);//ganti header
                                    
                                        $dataRes['header'] = array(
                                                'nojurnal'=>$nojurnal,
                                                'kodejurnal'=>$kodeJurnal,
                                                'tanggal'=>$tgmulaid,
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>$porsiPanen[$dtKbn],
                                                'totalkredit'=>-1*$porsiPanen[$dtKbn],
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>'ALK_SIPIL',
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'revisi'=>'0'                  
                                                );   
                                     # Debet 1
                                    $noUrut=1;
                                    unset($dataRes['detail']);//ganti header 
                                    #$blkPanen ada di line 253
                                    $isidt=1;
                                    $totalRupiah=0;
                                    foreach($blkPanen[$dtKbn] as $lstBlokPanen){
                                        $param['jumlahdet']=($porsiPanen[$dtKbn]/count($blkPanen[$dtKbn]));
                                        $totalRupiah+=$param['jumlahdet'];
                                        # Debet
                                        $dataRes['detail'][] = array(
                                            'nojurnal'=>$nojurnal,
                                            'tanggal'=>$tgmulaid,
                                            'nourut'=>$noUrut,
                                            'noakun'=>substr($kegpanen,0,7),
                                            'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                            'jumlah'=>$param['jumlahdet'],
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'kodeorg'=>$dtKbn,
                                            'kodekegiatan'=>$kegpanen,
                                            'kodeasset'=>'',
                                            'kodebarang'=>'',
                                            'nik'=>'0',
                                            'kodecustomer'=>'',
                                            'kodesupplier'=>'',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'noaruskas'=>'',
                                            'kodevhc'=>'',
                                            'nodok'=>$param['kdrumah'],
                                            'kodeblok'=>$lstBlokPanen,
                                            'revisi'=>'0',
                                            'kodesegment'=>$defSegment
                                        );
                                        $noUrut++;
                                        if(count($blkPanen[$dtKbn])==$isidt){
                                            # Kredit
                                            $dataRes['detail'][] = array(
                                            'nojurnal'=>$nojurnal,
                                            'tanggal'=>$tgmulaid,
                                            'nourut'=>$noUrut,
                                            'noakun'=>$akunDt,
                                            'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']."__Panen ".$tanggal,
                                            'jumlah'=>-1*$totalRupiah,
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'kodeorg'=>$dtKbn,
                                            'kodekegiatan'=>'',
                                            'kodeasset'=>'',
                                            'kodebarang'=>'',
                                            'nik'=>'',
                                            'kodecustomer'=>'',
                                            'kodesupplier'=>'',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'noaruskas'=>'',
                                            'kodevhc'=>'',
                                            'nodok'=>$param['kdrumah'],
                                            'kodeblok'=>'',
                                            'revisi'=>'0',
                                            'kodesegment'=>$defSegment);
                                            $noUrut++;
                                        }
                                        $isidt+=1;
                                    }
                                    $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                    try{
                                        $owlPDO->exec($insHead); 
                                    }catch (PDOException $e){
                                        $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
                                    }

                                    if(empty($headErr)) {
                                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                        $detailErr = '';
                                        foreach($dataRes['detail'] as $row) {
                                            $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                            try{
                                                $owlPDO->exec($insDet); 
                                            }catch (PDOException $e){
                                                $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                                                break;
                                            }
                                        }

                                        if($detailErr=='') {
                                            # Header and Detail inserted
                                            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                                            $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                                "kodeorg='".$ptpengguna.
                                                "' and kodekelompok='".$kodeJurnal."'");
                                            try{
                                                $owlPDO->exec($updJurnal); 
                                            }catch (PDOException $e){
                                                echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                                # Rollback if Update Failed
                                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                                try{
                                                    $owlPDO->exec($RBDet); 
                                                }catch (PDOException $e){
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
                                            }catch (PDOException $e){
                                                echo "Rollback Delete Header Error : ".$e->getMessage();
                                                exit;
                                            }
                                        }
                                    } else {
                                        echo $headErr;
                                        exit;
                                    }

                                }//end if porsi panen  case:'inter'
                                break;
                            }//end dari switch case

                    }//end foreach array dtKbn
                }//end if dari rpKebun

                if($rpPengawasan!=0){
                    foreach($dtPerKebun3 as $dtKbn){
                        $whrPt="kodeorganisasi='".$dtKbn."'";
                        $optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',$whrPt);
                        if($optPt[$dtKbn]!=$_SESSION['org']['kodeorganisasi']){
                            $stat="inter";//beda pt
                        }else if($param['kodeorg']!=$dtKbn){#cek jika satu lokasi tugas
                            $stat="intra";//satu pt beda lokasi
                        }else{
                            $stat="satu";//satu lokasi
                        }
                        #Periksa apakah unit tujuan sudah tutup buku:
                        $str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$param['periode']."' 
                                   and kodeorg='".$dtKbn."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $close='0';
                        //while($bar=$res->getMessage()){
						while($bar=$res->fetch()){	
                            $close=$bar->tutupbuku;
                        }
                        if($close=='1'){
                            exit(" Error: Unit ".$dtKbn.' has been closed');
                        }
                        switch($stat){
                            case'satu':
                                $kodeJurnal = 'SIPL9';
                                $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakundebet',
                                " jurnalid='".$kodeJurnal."'");
                                    $resParam = fetchData($queryParam);
                                $akunkredit=$resParam[0]['noakundebet'];
                                #======================== Nomor Jurnal =============================
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                    "kodeorg='".$optPt[$dtKbn]."' and kodekelompok='".$kodeJurnal."' ");
                                $tmpKonter = fetchData($queryJ);
                                $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-","",$tgmulaid)."/".$dtKbn."/".$kodeJurnal."/".$konter;
                                #======================== /Nomor Jurnal ============================
                                # Prep Header
                                unset($dataRes['header']);//ganti header
                                    $dataRes['header'] = array(
                                            'nojurnal'=>$nojurnal,
                                            'kodejurnal'=>$kodeJurnal,
                                            'tanggal'=>$tgmulaid,
                                            'tanggalentry'=>date('Ymd'),
                                            'posting'=>1,
                                            'totaldebet'=>$rpPerPengawasan[$dtKbn],
                                            'totalkredit'=>-1*$rpPerPengawasan[$dtKbn],
                                            'amountkoreksi'=>'0',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'autojurnal'=>'1',
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'revisi'=>'0'                  
                                            );   
                                 # Debet 1
                                $noUrut=1;
                                unset($dataRes['detail']);//ganti header 
                                foreach($lstTipe as $dtTipe){
                                    switch($dtTipe){
                                        case'TBM':
                                        case'TB':
                                            $group='KBNL1';
                                        break;
                                        case'TM':
                                            $group='KBNL2';
                                        break;
                                        case'PNN':
                                            $group='KBNL3';
                                        break;
                                        case'BBT':
                                            $group='KBNL0';
                                        break;
                                    }
                                    $str="select noakundebet from ".$dbname.".keu_5parameterjurnal
                                          where jurnalid='".$group."' limit 1";
                                    $res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                                    $res1->setFetchMode(PDO::FETCH_OBJ);
                                    if(owlBaris($res1)<1){
                                        exit("Error: No.Akun pada parameterjurnal belum ada untuk ".$group);
                                    }
                                    else{
                                        $akundebet='';
                                        $bar1=$res1->fetch();
                                        $akundebet=$bar1->noakundebet;
                                    }
                                    $isidt=1;
                                    $totalRupiah=0;
                                    foreach($blokAws[$dtKbn] as $kdBlok){
                                        if($lstBlok[$dtKbn][$dtTipe][$kdBlok]!=''){
                                            $param['jumlahdet']=((count($lstBlok[$dtKbn][$dtTipe])/$totBlok)*$rpPerPengawasan[$dtKbn])/count($lstBlok[$dtKbn][$dtTipe]);
                                            $totalRupiah+=$param['jumlahdet'];
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>$tgmulaid,
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akundebet,
                                                'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                                'jumlah'=>$param['jumlahdet'],
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$dtKbn,
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>'',
                                                'nik'=>'0',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>'ALK_SIPIL',
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>$param['kdrumah'],
                                                'kodeblok'=>$kdBlok,
                                                'revisi'=>'0',
                                                'kodesegment'=>$defSegment
                                            );
                                            $noUrut++;
                                            if(count($lstBlok[$dtKbn][$dtTipe])==$isidt){
                                                # Kredit
                                                $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>$tgmulaid,
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunkredit,
                                                'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                                                'jumlah'=>-1*$totalRupiah,
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$dtKbn,
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>'',
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>'ALK_SIPIL',
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>$param['kdrumah'],
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                                'kodesegment'=>$defSegment);
                                                $noUrut++;
                                            }
                                            $isidt+=1;
                                        }//end dari if
                                    }//end buat foreach blok
                                }//end buat foreach tipe
                                $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                try{
                                    $owlPDO->exec($insHead); 
                                }catch (PDOException $e){
                                    $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
                                }

                                if(empty($headErr)) {
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                    $detailErr = '';
                                    foreach($dataRes['detail'] as $row) {
                                        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                        try{
                                            $owlPDO->exec($insDet); 
                                        }catch (PDOException $e){
                                            $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                                            break;
                                        }
                                    }

                                    if($detailErr=='') {
                                        # Header and Detail inserted
                                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                                        $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                            "kodeorg='".$ptpengguna.
                                            "' and kodekelompok='".$kodeJurnal."'");
                                        try{
                                            $owlPDO->exec($updJurnal); 
                                        }catch (PDOException $e){
                                            echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                            # Rollback if Update Failed
                                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                            try{
                                                $owlPDO->exec($RBDet); 
                                            }catch (PDOException $e){
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
                                        }catch (PDOException $e){
                                            echo "Rollback Delete Header Error : ".$e->getMessage();
                                            exit;
                                        }
                                    }
                                } else {
                                    echo $headErr;
                                    exit;
                                }
                            break;
                            case'inter':
                            case'intra':
                                $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$dtKbn."' and jenis='".$stat."'";
                                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                                $res->setFetchMode(PDO::FETCH_OBJ);
                                $bar=$res->fetch();
                                $akunRk=$bar->akunhutang;//pengguna

                                if ($akunRk=='') {
                                    exit("Warning : Account intraco or interco not available for ".$dtKbn.". Please setting on menu Finance > setup > COA for Intra/Interco.");
                                } 

                                #dari sisi pemilik anggota sipil
                                $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$param['kodeorg']."' and jenis='".$stat."'";
                                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                                $res->setFetchMode(PDO::FETCH_OBJ);
                                $bar=$res->fetch();
                                $akunDt=$bar->akunhutang;

                                if ($akunDt=='') {
                                    exit("Warning : Account intraco or interco not available for ".$param['kodeorg'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
                                } 
                                    
                                #jika tidak dalam satu unit kerja/beda pt maka akan ada hubungan RK
                                #======================== Nomor Jurnal =============================
                                $kodeJurnal = 'SIPL9';
                                $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakundebet',
                                " jurnalid='".$kodeJurnal."'");
                                    $resParam = fetchData($queryParam);
                                  $akunkredit=$resParam[0]['noakundebet']; 
                                    
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
                                $tmpKonter = fetchData($queryJ);
                                $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                                unset($dataRes['header']);//ganti header
                                unset($dataRes['detail']);//ganti detail
                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
                                #======================== Nomor Jurnal =============================
                                $noUrut=1;
                                # Prep Header
                                $dataRes['header'] = array(
                                    'nojurnal'=>$nojurnal,
                                    'kodejurnal'=>$kodeJurnal,
                                    'tanggal'=>$tanggal,
                                    'tanggalentry'=>date('Ymd'),
                                    'posting'=>1,
                                    'totaldebet'=>$rpPerPengawasan[$dtKbn],
                                    'totalkredit'=>-1*$rpPerPengawasan[$dtKbn],
                                    'amountkoreksi'=>'0',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'autojurnal'=>'1',
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'revisi'=>'0'                    
                                    );
                                # Debet
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>$tanggal,
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunRk,
                                    'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                    'jumlah'=>$rpPerPengawasan[$dtKbn],
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>$param['kodeorg'],
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>'',
                                    'nik'=>'0',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>$param['kdrumah'],
                                    'kodeblok'=>'',
                                    'revisi'=>'0',
                                    'kodesegment'=>$defSegment
                                );
                                $noUrut++;

                                # Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>$tanggal,
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunkredit,
                                    'keterangan'=>$param['periode'].':Alokasi biaya rumah'.$param['kdrumah'],
                                    'jumlah'=>-1*$rpPerPengawasan[$dtKbn],
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>$param['kodeorg'],
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>'',
                                    'nik'=>'0',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>$param['kdrumah'],
                                    'kodeblok'=>'',
                                    'revisi'=>'0',
                                    'kodesegment'=>$defSegment
                                );
                               $noUrut++;
                               $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                try{
                                    $owlPDO->exec($insHead); 
                                }catch (PDOException $e){
                                    $headErr .= 'Insert Header Ex.Self Error : '.$e->getMessage()."\n";
                                }

                                if(empty($headErr)) {
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                    $detailErr = '';
                                    foreach($dataRes['detail'] as $row) {
                                         $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                        try{
                                            $owlPDO->exec($insDet); 
                                        }catch (PDOException $e){
                                            $detailErr .= "Insert Detail Ex.Self Error : ".$e->getMessage()."\n";
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
                                        }catch (PDOException $e){
                                            echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                            # Rollback if Update Failed
                                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                            try{
                                                $owlPDO->exec($RBDet); 
                                            }catch (PDOException $e){
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
                                        }catch (PDOException $e){
                                            echo "Rollback Delete Header Error : ".$e->getMessage();
                                            exit;
                                        }
                                    }
                                } else {
                                    echo $headErr;
                                    exit;
                                }
                                #+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
                                #sisi Pengguna
                                $tgmulaid=$tanggal;  
                                #======================== Nomor Jurnal =============================
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                    "kodeorg='".$optPt[$dtKbn]."' and kodekelompok='".$kodeJurnal."' ");
                                $tmpKonter = fetchData($queryJ);
                                $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-","",$tgmulaid)."/".$dtKbn."/".$kodeJurnal."/".$konter;
                                #======================== /Nomor Jurnal ============================
                                # Prep Header
                                unset($dataRes['header']);//ganti header
                                    $dataRes['header'] = array(
                                            'nojurnal'=>$nojurnal,
                                            'kodejurnal'=>$kodeJurnal,
                                            'tanggal'=>$tgmulaid,
                                            'tanggalentry'=>date('Ymd'),
                                            'posting'=>1,
                                            'totaldebet'=>$rpPerPengawasan[$dtKbn],
                                            'totalkredit'=>-1*$rpPerPengawasan[$dtKbn],
                                            'amountkoreksi'=>'0',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'autojurnal'=>'1',
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'revisi'=>'0'                  
                                            );   
                                 # Debet 1
                                $noUrut=1;
                                unset($dataRes['detail']);//ganti header 
                                foreach($lstTipe as $dtTipe){
                                    switch($dtTipe){
                                        case'TBM':
                                        case'TB':
                                            $group='KBNL1';
                                        break;
                                        case'TM':
                                            $group='KBNL2';
                                        break;
                                        case'PNN':
                                            $group='KBNL3';
                                        break;
                                        case'BBT':
                                            $group='KBNL0';
                                        break;
                                    }
                                    $str="select noakundebet from ".$dbname.".keu_5parameterjurnal
                                          where jurnalid='".$group."' limit 1";
                                    $res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                                    $res1->setFetchMode(PDO::FETCH_OBJ);
                                    if(owlBaris($res1)<1)
                                        exit("Error: No.Akun pada parameterjurnal belum ada untuk ".$group);
                                    else
                                    {
                                        $akundebet='';
                                        $bar1=$res1->fetch();
                                        $akundebet=$bar1->noakundebet;
                                    }
                                    $isidt=1;
                                    $totalRupiah=0;
                                    foreach($blokAws[$dtKbn] as $kdBlok){
                                        if($lstBlok[$dtKbn][$dtTipe][$kdBlok]!=''){
                                            $param['jumlahdet']=((count($lstBlok[$dtKbn][$dtTipe])/$totBlok)*$rpPerPengawasan[$dtKbn])/count($lstBlok[$dtKbn][$dtTipe]);
                                            $totalRupiah+=$param['jumlahdet'];
                                            # Debet
                                            $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>$tgmulaid,
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akundebet,
                                                'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                                'jumlah'=>$param['jumlahdet'],
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$dtKbn,
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>'',
                                                'nik'=>'0',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>'ALK_SIPIL',
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>$param['kdrumah'],
                                                'kodeblok'=>$kdBlok,
                                                'revisi'=>'0',
                                                'kodesegment'=>$defSegment
                                            );
                                            $noUrut++;
                                            if(count($lstBlok[$dtKbn][$dtTipe])==$isidt){
                                                # Kredit
                                                $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal,
                                                'tanggal'=>$tgmulaid,
                                                'nourut'=>$noUrut,
                                                'noakun'=>$akunDt,
                                                'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                                                'jumlah'=>-1*$totalRupiah,
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$dtKbn,
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>'',
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>'ALK_SIPIL',
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>$param['kdrumah'],
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                                'kodesegment'=>$defSegment);
                                                $noUrut++;
                                            }
                                            $isidt+=1;
                                        }//end dari if
                                    }//end buat foreach blok
                                }//end buat foreach tipe
                                $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                try{
                                    $owlPDO->exec($insHead); 
                                }catch (PDOException $e){
                                    $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
                                }

                                if(empty($headErr)) {
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                    $detailErr = '';
                                    foreach($dataRes['detail'] as $row) {
                                        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                        try{
                                            $owlPDO->exec($insDet); 
                                        }catch (PDOException $e){
                                            $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                                            break;
                                        }
                                    }

                                    if($detailErr=='') {
                                        # Header and Detail inserted
                                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                                        $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                            "kodeorg='".$ptpengguna.
                                            "' and kodekelompok='".$kodeJurnal."'");
                                        try{
                                            $owlPDO->exec($updJurnal); 
                                        }catch (PDOException $e){
                                            echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                            # Rollback if Update Failed
                                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                            try{
                                                $owlPDO->exec($RBDet); 
                                            }catch (PDOException $e){
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
                                        }catch (PDOException $e){
                                            echo "Rollback Delete Header Error : ".$e->getMessage();
                                            exit;
                                        }
                                    }
                                } else {
                                    echo $headErr;
                                    exit;
                                }
          
                            break;
                        }
                    }//end dari foreach $dtPerKebun3
                }//end if dari rpPerPengawasan  lstVhc

                #rupiah operator di proporsi
                if($rpOperator!=0){
                     foreach($dtPerKebun4 as $dtKbn){
                        $whrPt="kodeorganisasi='".$dtKbn."'";
                        $optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',$whrPt);
                        if($optPt[$dtKbn]!=$_SESSION['org']['kodeorganisasi']){
                            $stat="inter";//beda pt
                        }else if($param['kodeorg']!=$dtKbn){#cek jika satu lokasi tugas
                            $stat="intra";//satu pt beda lokasi
                        }else{
                            $stat="satu";//satu lokasi
                        }
                        #Periksa apakah unit tujuan sudah tutup buku:
                        $str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$param['periode']."' 
                                   and kodeorg='".$dtKbn."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $close='0';
                        while($bar=$res->getMessage()){
                            $close=$bar->tutupbuku;
                        }
                        if($close=='1'){
                            exit(" Error: Unit ".$dtKbn.' has been closed');
                        }
                        $kodeJurnal = 'SIPA4';
                        $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',
                        " jurnalid='".$kodeJurnal."'");
                            $resParam = fetchData($queryParam);
                        $akunkredit=$resParam[0]['noakunkredit'];
                        $akundebet=$resParam[0]['noakundebet']; 

                        switch($stat){
                            case'satu':
                                $tgmulaid=$tanggal;  
                                #======================== Nomor Jurnal =============================
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                    "kodeorg='".$optPt[$dtKbn]."' and kodekelompok='".$kodeJurnal."' ");
                                $tmpKonter = fetchData($queryJ);
                                $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-","",$tgmulaid)."/".$dtKbn."/".$kodeJurnal."/".$konter;
                                #======================== /Nomor Jurnal ============================
                                # Prep Header
                                unset($dataRes['header']);//ganti header
                                    $dataRes['header'] = array(
                                            'nojurnal'=>$nojurnal,
                                            'kodejurnal'=>$kodeJurnal,
                                            'tanggal'=>$tgmulaid,
                                            'tanggalentry'=>date('Ymd'),
                                            'posting'=>1,
                                            'totaldebet'=>$rpPerOperator[$dtKbn],
                                            'totalkredit'=>-1*$rpPerOperator[$dtKbn],
                                            'amountkoreksi'=>'0',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'autojurnal'=>'1',
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'revisi'=>'0'                  
                                            );   
                                 # Debet 1
                                $noUrut=1;
                                $isidt=1;
                                $totalRupiah=0;
                                unset($dataRes['detail']);//ganti header 
                                foreach($lstVhc[$dtKbn] as $dtVhc){
                                    $param['jumlahdet']=$rpPerOperator[$dtKbn]/count($lstVhc[$dtKbn]);
                                    # Debet
                                    $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$tgmulaid,
                                        'nourut'=>$noUrut,
                                        'noakun'=>$akundebet,
                                        'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                        'jumlah'=>$param['jumlahdet'],
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$dtKbn,
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>'',
                                        'nik'=>'0',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'noaruskas'=>'',
                                        'kodevhc'=>$dtVhc,
                                        'nodok'=>$param['kdrumah'],
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment'=>$defSegment
                                    );
                                    $noUrut++;
                                    if(count($lstVhc[$dtKbn])==$isidt){
                                        # Kredit
                                        $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$tgmulaid,
                                        'nourut'=>$noUrut,
                                        'noakun'=>$akunkredit,
                                        'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                                        'jumlah'=>-1*$rpPerOperator[$dtKbn],
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$dtKbn,
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>'',
                                        'nik'=>'',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'noaruskas'=>'',
                                        'kodevhc'=>'',
                                        'nodok'=>$param['kdrumah'],
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment'=>$defSegment);
                                        $noUrut++;
                                    }
                                    $isidt+=1;
                                }//end buat foreach vhc
                                $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                try{
                                    $owlPDO->exec($insHead); 
                                }catch (PDOException $e){
                                    $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
                                }

                                if(empty($headErr)) {
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                    $detailErr = '';
                                    foreach($dataRes['detail'] as $row) {
                                        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                        try{
                                            $owlPDO->exec($insDet); 
                                        }catch (PDOException $e){
                                            $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                                            break;
                                        }
                                    }

                                    if($detailErr=='') {
                                        # Header and Detail inserted
                                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                                        $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                            "kodeorg='".$ptpengguna.
                                            "' and kodekelompok='".$kodeJurnal."'");
                                        try{
                                            $owlPDO->exec($updJurnal); 
                                        }catch (PDOException $e){
                                            echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                            # Rollback if Update Failed
                                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                            try{
                                                $owlPDO->exec($RBDet); 
                                            }catch (PDOException $e){
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
                                        }catch (PDOException $e){
                                            echo "Rollback Delete Header Error : ".$e->getMessage();
                                            exit;
                                        }
                                    }
                                } else {
                                    echo $headErr;
                                    exit;
                                }
                            break;
                            case'intra':
                            case'inter':
                                $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$dtKbn."' and jenis='".$stat."'";
                                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                                $res->setFetchMode(PDO::FETCH_OBJ);
                                $bar=$res->fetch();
                                $akunRk=$bar->akunhutang;//pengguna

                                if ($akunRk=='') {
                                    exit("Warning : Account intraco or interco not available for ".$dtKbn.". Please setting on menu Finance > setup > COA for Intra/Interco.");
                                } 

                                #dari sisi pemilik anggota sipil
                                $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$param['kodeorg']."' and jenis='".$stat."'";
                                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                                $res->setFetchMode(PDO::FETCH_OBJ);
                                $bar=$res->fetch();
                                $akunDt=$bar->akunhutang;

                                if ($akunDt=='') {
                                    exit("Warning : Account intraco or interco not available for ".$param['kodeorg'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
                                } 
                                    
                                #jika tidak dalam satu unit kerja/beda pt maka akan ada hubungan RK
                                #======================== Nomor Jurnal =============================
                                    
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
                                $tmpKonter = fetchData($queryJ);
                                $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                                unset($dataRes['header']);//ganti header
                                unset($dataRes['detail']);//ganti detail
                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
                                #======================== Nomor Jurnal =============================
                                $noUrut=1;
                                # Prep Header
                                $dataRes['header'] = array(
                                    'nojurnal'=>$nojurnal,
                                    'kodejurnal'=>$kodeJurnal,
                                    'tanggal'=>$tanggal,
                                    'tanggalentry'=>date('Ymd'),
                                    'posting'=>1,
                                    'totaldebet'=>$rpPerOperator[$dtKbn],
                                    'totalkredit'=>-1*$rpPerOperator[$dtKbn],
                                    'amountkoreksi'=>'0',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'autojurnal'=>'1',
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'revisi'=>'0'                    
                                    );
                                # Debet
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>$tanggal,
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunRk,
                                    'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                    'jumlah'=>$rpPerOperator[$dtKbn],
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>$param['kodeorg'],
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>'',
                                    'nik'=>'0',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>$param['kdrumah'],
                                    'kodeblok'=>'',
                                    'revisi'=>'0',
                                    'kodesegment'=>$defSegment
                                );
                                $noUrut++;

                                # Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>$tanggal,
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunkredit,
                                    'keterangan'=>$param['periode'].':Alokasi biaya rumah'.$param['kdrumah'],
                                    'jumlah'=>-1*$rpPerOperator[$dtKbn],
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>$param['kodeorg'],
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>'',
                                    'nik'=>'0',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>$param['kdrumah'],
                                    'kodeblok'=>'',
                                    'revisi'=>'0',
                                    'kodesegment'=>$defSegment
                                );
                               $noUrut++;
                               $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                try{
                                    $owlPDO->exec($insHead); 
                                }catch (PDOException $e){
                                    $headErr .= 'Insert Header Ex.Self Error : '.$e->getMessage()."\n";
                                }

                                if(empty($headErr)) {
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                    $detailErr = '';
                                    foreach($dataRes['detail'] as $row) {
                                        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                        try{
                                            $owlPDO->exec($insDet); 
                                        }catch (PDOException $e){
                                            $detailErr .= "Insert Detail Ex.Self Error : ".$e->getMessage()."\n";
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
                                        }catch (PDOException $e){
                                            echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                            # Rollback if Update Failed
                                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                            try{
                                                $owlPDO->exec($RBDet); 
                                            }catch (PDOException $e){
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
                                        }catch (PDOException $e){
                                            echo "Rollback Delete Header Error : ".$e->getMessage();
                                            exit;
                                        }
                                    }
                                } else {
                                    echo $headErr;
                                    exit;
                                }
                                #+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
                                #sisi Pengguna
                                $tgmulaid=$tanggal;  
                                #======================== Nomor Jurnal =============================
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                    "kodeorg='".$optPt[$dtKbn]."' and kodekelompok='".$kodeJurnal."' ");
                                $tmpKonter = fetchData($queryJ);
                                $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-","",$tgmulaid)."/".$dtKbn."/".$kodeJurnal."/".$konter;
                                #======================== /Nomor Jurnal ============================
                                # Prep Header
                                unset($dataRes['header']);//ganti header
                                    $dataRes['header'] = array(
                                            'nojurnal'=>$nojurnal,
                                            'kodejurnal'=>$kodeJurnal,
                                            'tanggal'=>$tgmulaid,
                                            'tanggalentry'=>date('Ymd'),
                                            'posting'=>1,
                                            'totaldebet'=>$rpPerOperator[$dtKbn],
                                            'totalkredit'=>-1*$rpPerOperator[$dtKbn],
                                            'amountkoreksi'=>'0',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'autojurnal'=>'1',
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'revisi'=>'0'                  
                                            );   
                                 # Debet 1
                                $noUrut=1;
                                $isidt=1;
                                $totalRupiah=0;
                                unset($dataRes['detail']);//ganti header 
                                foreach($lstVhc[$dtKbn] as $dtVhc){
                                    $param['jumlahdet']=$rpPerOperator[$dtKbn]/count($lstVhc[$dtKbn]);
                                    # Debet
                                    $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$tgmulaid,
                                        'nourut'=>$noUrut,
                                        'noakun'=>$akundebet,
                                        'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                        'jumlah'=>$param['jumlahdet'],
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$dtKbn,
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>'',
                                        'nik'=>'0',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'noaruskas'=>'',
                                        'kodevhc'=>$dtVhc,
                                        'nodok'=>$param['kdrumah'],
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment'=>$defSegment
                                    );
                                    $noUrut++;
                                    if(count($lstVhc[$dtKbn])==$isidt){
                                        # Kredit
                                        $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$tgmulaid,
                                        'nourut'=>$noUrut,
                                        'noakun'=>$akunDt,
                                        'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                                        'jumlah'=>-1*$rpPerOperator[$dtKbn],
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$dtKbn,
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>'',
                                        'nik'=>'',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'noaruskas'=>'',
                                        'kodevhc'=>'',
                                        'nodok'=>$param['kdrumah'],
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment'=>$defSegment);
                                        $noUrut++;
                                    }
                                    $isidt+=1;
                                }//end buat foreach vhc
                                $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                try{
                                    $owlPDO->exec($insHead); 
                                }catch (PDOException $e){
                                    $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
                                }

                                if(empty($headErr)) {
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                    $detailErr = '';
                                    foreach($dataRes['detail'] as $row) {
                                        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                        try{
                                            $owlPDO->exec($insDet); 
                                        }catch (PDOException $e){
                                            $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                                            break;
                                        }
                                    }

                                    if($detailErr=='') {
                                        # Header and Detail inserted
                                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                                        $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                            "kodeorg='".$ptpengguna.
                                            "' and kodekelompok='".$kodeJurnal."'");
                                        try{
                                            $owlPDO->exec($updJurnal); 
                                        }catch (PDOException $e){
                                            echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                            # Rollback if Update Failed
                                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                            try{
                                                $owlPDO->exec($RBDet); 
                                            }catch (PDOException $e){
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
                                        }catch (PDOException $e){
                                            echo "Rollback Delete Header Error : ".$e->getMessage();
                                            exit;
                                        }
                                    }
                                } else {
                                    echo $headErr;
                                    exit;
                                }
                            break;
                        }

                    }//end id dari foreach dtPerKebun4
                }//end if dari rpOperator 

                if(($rpMainten!=0)||($rpEngine!=0)||($rpKantor!=0)){
                    foreach($dtPerKebun5 as $dtKbn){
                        $whrPt="kodeorganisasi='".$dtKbn."'";
                        $optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',$whrPt);
                        if($optPt[$dtKbn]!=$_SESSION['org']['kodeorganisasi']){
                            $stat="inter";//beda pt
                        }else if($param['kodeorg']!=$dtKbn){#cek jika satu lokasi tugas
                            $stat="intra";//satu pt beda lokasi
                        }else{
                            $stat="satu";//satu lokasi
                        }
                        #Periksa apakah unit tujuan sudah tutup buku:
                        $str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$param['periode']."' 
                                   and kodeorg='".$dtKbn."'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $close='0';
                        while($bar=$res->fetch()){
                            $close=$bar->tutupbuku;
                        }
                        if($close=='1'){
                            exit(" Error: Unit ".$dtKbn.' has been closed');
                        }
                        if($rpPerMainten[$dtKbn]!=0){

                        }
                        $kodeJurnal = '';
                        foreach($dafSubbagian[$dtKbn] as $lstSubbagian){
                            $whrdt="kodeorganisasi='".$lstSubbagian."'";
                            $rpDisini=0;
                            $optdt=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whrdt);
                            if($optdt[$rstr5['subbagian']]=='STENGINE'){
                                $kodeJurnal = 'SIPA2';        
                                $rpDisini=$rpPerEngine[$dtKbn];
                            } else if($optdt[$rstr5['subbagian']]=='MAINTENANCE'){
                                $kodeJurnal = 'SIPA3';        
                                $rpDisini=$rpPerMainten[$dtKbn];
                            } else {
                                $kodeJurnal = 'SIPA1';        
                                $rpDisini=$rpPerKantor[$dtKbn];
                            }
                            $whrPt="kodeorganisasi='".$dtKbn."'";
                            $optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',$whrPt);
                            if($optPt[$dtKbn]!=$_SESSION['org']['kodeorganisasi']){
                                $stat="inter";//beda pt
                            }else if($param['kodeorg']!=$dtKbn){#cek jika satu lokasi tugas
                                $stat="intra";//satu pt beda lokasi
                            }else{
                                $stat="satu";//satu lokasi
                            }
                            #Periksa apakah unit tujuan sudah tutup buku:
                            $str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$param['periode']."' 
                                       and kodeorg='".$dtKbn."'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);        
                            $close='0';
                            while($bar=$res->fetch()){
                                $close=$bar->tutupbuku;
                            }
                            if($close=='1'){
                                exit(" Error: Unit ".$dtKbn.' has been closed');
                            }
                            $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',
                            " jurnalid='".$kodeJurnal."'");
                                $resParam = fetchData($queryParam);
                            $akunkredit=$resParam[0]['noakunkredit'];
                            $akundebet=$resParam[0]['noakundebet'];
                            switch($stat){
                            case'satu':
                                $tgmulaid=$tanggal;  
                                #======================== Nomor Jurnal =============================
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                    "kodeorg='".$optPt[$dtKbn]."' and kodekelompok='".$kodeJurnal."' ");
                                $tmpKonter = fetchData($queryJ);
                                $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-","",$tgmulaid)."/".$dtKbn."/".$kodeJurnal."/".$konter;
                                #======================== /Nomor Jurnal ============================
                                # Prep Header
                                unset($dataRes['header']);//ganti header
                                    $dataRes['header'] = array(
                                            'nojurnal'=>$nojurnal,
                                            'kodejurnal'=>$kodeJurnal,
                                            'tanggal'=>$tgmulaid,
                                            'tanggalentry'=>date('Ymd'),
                                            'posting'=>1,
                                            'totaldebet'=>$rpDisini,
                                            'totalkredit'=>-1*$rpDisini,
                                            'amountkoreksi'=>'0',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'autojurnal'=>'1',
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'revisi'=>'0'                  
                                            );   
                                 # Debet 1
                                $noUrut=1;
                                $isidt=1;
                                $totalRupiah=0;
                                unset($dataRes['detail']);//ganti detail 
                                    # Debet
                                    $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$tgmulaid,
                                        'nourut'=>$noUrut,
                                        'noakun'=>$akundebet,
                                        'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                        'jumlah'=>$rpDisini,
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$dtKbn,
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>'',
                                        'nik'=>'0',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'noaruskas'=>'',
                                        'kodevhc'=>'',
                                        'nodok'=>$param['kdrumah'],
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment'=>$defSegment
                                    );
                                    $noUrut++;
                                    # Kredit
                                    $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$tgmulaid,
                                        'nourut'=>$noUrut,
                                        'noakun'=>$akunkredit,
                                        'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                                        'jumlah'=>-1*$rpDisini,
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$dtKbn,
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>'',
                                        'nik'=>'',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'noaruskas'=>'',
                                        'kodevhc'=>'',
                                        'nodok'=>$param['kdrumah'],
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment'=>$defSegment);
                                        $noUrut++;
                                $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                try{
                                    $owlPDO->exec($insHead); 
                                }catch (PDOException $e){
                                    $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
                                }

                                if(empty($headErr)) {
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                    $detailErr = '';
                                    foreach($dataRes['detail'] as $row) {
                                        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                        try{
                                            $owlPDO->exec($insDet); 
                                        }catch (PDOException $e){
                                            $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                                            break;
                                        }
                                    }

                                    if($detailErr=='') {
                                        # Header and Detail inserted
                                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                                        $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                            "kodeorg='".$ptpengguna.
                                            "' and kodekelompok='".$kodeJurnal."'");
                                        try{
                                            $owlPDO->exec($updJurnal); 
                                        }catch (PDOException $e){
                                            echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                            # Rollback if Update Failed
                                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                            try{
                                                $owlPDO->exec($RBDet); 
                                            }catch (PDOException $e){
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
                                        }catch (PDOException $e){
                                            echo "Rollback Delete Header Error : ".$e->getMessage();
                                            exit;
                                        }
                                    }
                                } else {
                                    echo $headErr;
                                    exit;
                                }
                            break;
                            case'intra':
                            case'inter':
                                $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$dtKbn."' and jenis='".$stat."'";
                                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                                $res->setFetchMode(PDO::FETCH_OBJ);
                                $bar=$res->fetch();
                                $akunRk=$bar->akunhutang;//pengguna

                                if ($akunRk=='') {
                                    exit("Warning : Account intraco or interco not available for ".$dtKbn.". Please setting on menu Finance > setup > COA for Intra/Interco.");
                                } 

                                #dari sisi pemilik anggota sipil
                                $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$param['kodeorg']."' and jenis='".$stat."'";
                                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                                $res->setFetchMode(PDO::FETCH_OBJ);
                                $bar=$res->fetch();
                                $akunDt=$bar->akunhutang;

                                if ($akunDt=='') {
                                    exit("Warning : Account intraco or interco not available for ".$param['kodeorg'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
                                } 
                                    
                                #jika tidak dalam satu unit kerja/beda pt maka akan ada hubungan RK
                                #======================== Nomor Jurnal =============================
                                    
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
                                $tmpKonter = fetchData($queryJ);
                                $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                                unset($dataRes['header']);//ganti header
                                unset($dataRes['detail']);//ganti detail
                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
                                #======================== Nomor Jurnal =============================
                                $noUrut=1;
                                # Prep Header
                                $dataRes['header'] = array(
                                    'nojurnal'=>$nojurnal,
                                    'kodejurnal'=>$kodeJurnal,
                                    'tanggal'=>$tanggal,
                                    'tanggalentry'=>date('Ymd'),
                                    'posting'=>1,
                                    'totaldebet'=>$rpDisini,
                                    'totalkredit'=>-1*$rpDisini,
                                    'amountkoreksi'=>'0',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'autojurnal'=>'1',
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'revisi'=>'0'                    
                                    );
                                # Debet
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>$tanggal,
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunRk,
                                    'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                    'jumlah'=>$rpDisini,
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>$param['kodeorg'],
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>'',
                                    'nik'=>'0',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>$param['kdrumah'],
                                    'kodeblok'=>'',
                                    'revisi'=>'0',
                                    'kodesegment'=>$defSegment
                                );
                                $noUrut++;

                                # Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>$tanggal,
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunkredit,
                                    'keterangan'=>$param['periode'].':Alokasi biaya rumah'.$param['kdrumah'],
                                    'jumlah'=>-1*$rpDisini,
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>$param['kodeorg'],
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>'',
                                    'nik'=>'0',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>$param['kdrumah'],
                                    'kodeblok'=>'',
                                    'revisi'=>'0',
                                    'kodesegment'=>$defSegment
                                );
                               $noUrut++;
                               $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                try{
                                    $owlPDO->exec($insHead); 
                                }catch (PDOException $e){
                                    $headErr .= 'Insert Header Ex.Self Error : '.$e->getMessage()."\n";
                                }

                                if(empty($headErr)) {
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                    $detailErr = '';
                                    foreach($dataRes['detail'] as $row) {
                                        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                        try{
                                            $owlPDO->exec($insDet); 
                                        }catch (PDOException $e){
                                            $detailErr .= "Insert Detail Ex.Self Error : ".$e->getMessage()."\n";
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
                                        }catch (PDOException $e){
                                            echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                            # Rollback if Update Failed
                                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                            try{
                                                $owlPDO->exec($RBDet); 
                                            }catch (PDOException $e){
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
                                        }catch (PDOException $e){
                                            echo "Rollback Delete Header Error : ".$e->getMessage()."\n";
                                            exit;
                                        }
                                    }
                                } else {
                                    echo $headErr;
                                    exit;
                                }
                                #+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
                                #sisi Pengguna
                                $tgmulaid=$tanggal;  
                                #======================== Nomor Jurnal =============================
                                # Get Journal Counter
                                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                    "kodeorg='".$optPt[$dtKbn]."' and kodekelompok='".$kodeJurnal."' ");
                                $tmpKonter = fetchData($queryJ);
                                $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                                # Transform No Jurnal dari No Transaksi
                                $nojurnal = str_replace("-","",$tgmulaid)."/".$dtKbn."/".$kodeJurnal."/".$konter;
                                #======================== /Nomor Jurnal ============================
                                # Prep Header
                                unset($dataRes['header']);//ganti header
                                    $dataRes['header'] = array(
                                            'nojurnal'=>$nojurnal,
                                            'kodejurnal'=>$kodeJurnal,
                                            'tanggal'=>$tgmulaid,
                                            'tanggalentry'=>date('Ymd'),
                                            'posting'=>1,
                                            'totaldebet'=>$rpDisini,
                                            'totalkredit'=>-1*$rpDisini,
                                            'amountkoreksi'=>'0',
                                            'noreferensi'=>'ALK_SIPIL',
                                            'autojurnal'=>'1',
                                            'matauang'=>'IDR',
                                            'kurs'=>'1',
                                            'revisi'=>'0'                  
                                            );   
                                 # Debet 1
                                $noUrut=1;
                                $isidt=1;
                                $totalRupiah=0;
                                unset($dataRes['detail']);//ganti detail 
                                    # Debet
                                    $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$tgmulaid,
                                        'nourut'=>$noUrut,
                                        'noakun'=>$akundebet,
                                        'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                        'jumlah'=>$rpDisini,
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$dtKbn,
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>'',
                                        'nik'=>'0',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'noaruskas'=>'',
                                        'kodevhc'=>'',
                                        'nodok'=>$param['kdrumah'],
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment'=>$defSegment
                                    );
                                    $noUrut++;
                                    # Kredit
                                    $dataRes['detail'][] = array(
                                        'nojurnal'=>$nojurnal,
                                        'tanggal'=>$tgmulaid,
                                        'nourut'=>$noUrut,
                                        'noakun'=>$akunDt,
                                        'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                                        'jumlah'=>-1*$rpDisini,
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$dtKbn,
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>'',
                                        'nik'=>'',
                                        'kodecustomer'=>'',
                                        'kodesupplier'=>'',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'noaruskas'=>'',
                                        'kodevhc'=>'',
                                        'nodok'=>$param['kdrumah'],
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment'=>$defSegment);
                                        $noUrut++;
                                $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                try{
                                    $owlPDO->exec($insHead); 
                                }catch (PDOException $e){
                                    $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
                                }

                                if(empty($headErr)) {
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                    $detailErr = '';
                                    foreach($dataRes['detail'] as $row) {
                                        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                        try{
                                            $owlPDO->exec($insDet); 
                                        }catch (PDOException $e){
                                            $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                                            break;
                                        }
                                    }

                                    if($detailErr=='') {
                                        # Header and Detail inserted
                                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                                        $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                            "kodeorg='".$ptpengguna.
                                            "' and kodekelompok='".$kodeJurnal."'");
                                        try{
                                            $owlPDO->exec($updJurnal); 
                                        }catch (PDOException $e){
                                            echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                            # Rollback if Update Failed
                                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                            try{
                                                $owlPDO->exec($RBDet); 
                                            }catch (PDOException $e){
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
                                        }catch (PDOException $e){
                                            echo "Rollback Delete Header Error : ".$e->getMessage();
                                            exit;
                                        }
                                    }
                                } else {
                                    echo $headErr;
                                    exit;
                                }
                                break;
                            }//end of switch case
                        }//end of foreach subbagian
                    }//end foreach dari dtPerKebun5
                }//end if rpOperator,rpPerEngine,rpKantor
            }//end if tipe A (RUMAH)
            if($tipe=='B'){
                $rpDisini=0;
                $sData="select kodekegiatan,total_hk,alokasi from ".$dbname.".vhc_spl_prestasi a 
                        left join ".$dbname.".vhc_splht b on a.notransaksi=b.notransaksi
                        where alokasi='".$param['kdrumah']."' and tanggal between '".$tglMulai."' and '".$tglSampai."'";
                $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
                $qData->setFetchMode(PDO::FETCH_ASSOC);
                while($rData=$qData->fetch()){
                    $dafKeg[$rData['kodekegiatan']]=$rData['kodekegiatan'];
                    $lstHk[$rData['kodekegiatan']]+=$rData['total_hk'];
                    $dtKbn=substr($rData['alokasi'],0,4);
                }
                #cek apakah intraco,interco atau satu lokasi
                $whrPt="kodeorganisasi='".$dtKbn."'";
                $optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',$whrPt);
                if($optPt[$dtKbn]!=$_SESSION['org']['kodeorganisasi']){
                    $stat="inter";//beda pt
                }else if($param['kodeorg']!=$dtKbn){#cek jika satu lokasi tugas
                    $stat="intra";//satu pt beda lokasi
                }else{
                    $stat="satu";//satu lokasi
                }
                #Periksa apakah unit tujuan sudah tutup buku:
                $str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$param['periode']."' 
                           and kodeorg='".$dtKbn."'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                $close='0';
                while($bar=$res->fetch()){
                    $close=$bar->tutupbuku;
                }
                if($close=='1'){
                    exit(" Error: Unit ".$dtKbn.' has been closed');
                }
                $kodeJurnal="SIPL9";
                $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakundebet',
                " jurnalid='".$kodeJurnal."'");
                    $resParam = fetchData($queryParam);
                $akunkredit=$resParam[0]['noakundebet'];
                    switch($stat){
                        case'inter':
                        case'intra':
                            $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$dtKbn."' and jenis='".$stat."'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $akunRk=$bar->akunhutang;//pengguna

                            if ($akunRk=='') {
                                exit("Warning : Account intraco or interco not available for ".$dtKbn.". Please setting on menu Finance > setup > COA for Intra/Interco.");
                            }

                            #dari sisi pemilik anggota sipil
                            $str="select akunhutang,jenis from ".$dbname.".keu_5caco where  kodeorg='".$param['kodeorg']."' and jenis='".$stat."'";
                            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            $bar=$res->fetch();
                            $akunDt=$bar->akunhutang;

                            if ($akunDt=='') {
                                exit("Warning : Account intraco or interco not available for ".$param['kodeorg'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
                            } 
                                
                            #jika tidak dalam satu unit kerja/beda pt maka akan ada hubungan RK
                            #======================== Nomor Jurnal =============================
                                
                            # Get Journal Counter
                            $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."'");
                            $tmpKonter = fetchData($queryJ);
                            $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                            unset($dataRes['header']);//ganti header
                            unset($dataRes['detail']);//ganti detail
                            # Transform No Jurnal dari No Transaksi
                            $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
                            #======================== Nomor Jurnal =============================
                            $noUrut=1;
                            $rpDisini=$param['jumlah']*$param['jmlhhk'];
                            # Prep Header
                            $dataRes['header'] = array(
                                'nojurnal'=>$nojurnal,
                                'kodejurnal'=>$kodeJurnal,
                                'tanggal'=>$tanggal,
                                'tanggalentry'=>date('Ymd'),
                                'posting'=>1,
                                'totaldebet'=>$rpDisini,
                                'totalkredit'=>-1*$rpDisini,
                                'amountkoreksi'=>'0',
                                'noreferensi'=>'ALK_SIPIL',
                                'autojurnal'=>'1',
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'revisi'=>'0'                    
                                );
                            # Debet
                            $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>$tanggal,
                                'nourut'=>$noUrut,
                                'noakun'=>$akunRk,
                                'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                'jumlah'=>$rpDisini,
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$param['kodeorg'],
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>'',
                                'nik'=>'0',
                                'kodecustomer'=>'',
                                'kodesupplier'=>'',
                                'noreferensi'=>'ALK_SIPIL',
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$param['kdrumah'],
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>$defSegment
                            );
                            $noUrut++;

                            # Kredit
                            $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>$tanggal,
                                'nourut'=>$noUrut,
                                'noakun'=>$akunkredit,
                                'keterangan'=>$param['periode'].':Alokasi biaya rumah'.$param['kdrumah'],
                                'jumlah'=>-1*$rpDisini,
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$param['kodeorg'],
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>'',
                                'nik'=>'0',
                                'kodecustomer'=>'',
                                'kodesupplier'=>'',
                                'noreferensi'=>'ALK_SIPIL',
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$param['kdrumah'],
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment'=>$defSegment
                            );
                           $noUrut++;
                           $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                            try{
                                $owlPDO->exec($insHead); 
                            }catch (PDOException $e){
                                $headErr .= 'Insert Header Ex.Self Error : '.$e->getMessage()."\n";
                            }

                            if(empty($headErr)) {
                                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                $detailErr = '';
                                foreach($dataRes['detail'] as $row) {
                                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                    try{
                                        $owlPDO->exec($insDet); 
                                    }catch (PDOException $e){
                                        $detailErr .= "Insert Detail Ex.Self Error : ".$e->getMessage()."\n";
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
                                    }catch (PDOException $e){
                                        echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                        # Rollback if Update Failed
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        try{
                                            $owlPDO->exec($RBDet); 
                                        }catch (PDOException $e){
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
                                    }catch (PDOException $e){
                                        echo "Rollback Delete Header Error : ".$e->getMessage();
                                        exit;
                                    }
                                }
                            } else {
                                echo $headErr;
                                exit;
                            }
                            #+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
                            #sisi Pengguna
                            $tgmulaid=$tanggal;  
                            #======================== Nomor Jurnal =============================
                            # Get Journal Counter
                            $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                                "kodeorg='".$optPt[$dtKbn]."' and kodekelompok='".$kodeJurnal."' ");
                            $tmpKonter = fetchData($queryJ);
                            $konter = addZero($tmpKonter[0]['nokounter']+1,3);

                            # Transform No Jurnal dari No Transaksi
                            $nojurnal = str_replace("-","",$tgmulaid)."/".$dtKbn."/".$kodeJurnal."/".$konter;
                            #======================== /Nomor Jurnal ============================
                            # Prep Header
                            unset($dataRes['header']);//ganti header
                                $dataRes['header'] = array(
                                        'nojurnal'=>$nojurnal,
                                        'kodejurnal'=>$kodeJurnal,
                                        'tanggal'=>$tgmulaid,
                                        'tanggalentry'=>date('Ymd'),
                                        'posting'=>1,
                                        'totaldebet'=>$rpDisini,
                                        'totalkredit'=>-1*$rpDisini,
                                        'amountkoreksi'=>'0',
                                        'noreferensi'=>'ALK_SIPIL',
                                        'autojurnal'=>'1',
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'revisi'=>'0'                  
                                        );   
                             # Debet 1
                            $noUrut=1;
                            $isidt=1;
                            $totalRupiah=0;
                            unset($dataRes['detail']);//ganti detail 
                            foreach($dafKeg as $lstKeg){
                                # Debet
                                $rpDisini=($param['jumlah']*$lstHk[$lstKeg]);
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>$tgmulaid,
                                    'nourut'=>$noUrut,
                                    'noakun'=>substr($lstKeg,0,7),
                                    'keterangan'=>$param['periode'].':Biaya Rumah '.$param['kdrumah'],
                                    'jumlah'=>$rpDisini,
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>$dtKbn,
                                    'kodekegiatan'=>$lstKeg,
                                    'kodeasset'=>'',
                                    'kodebarang'=>'',
                                    'nik'=>'0',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>$param['kdrumah'],
                                    'kodeblok'=>$param['kdrumah'],
                                    'revisi'=>'0',
                                    'kodesegment'=>$defSegment
                                );
                                $noUrut++;
                                if(count($dafKeg)==$isidt){
                                # Kredit
                                $dataRes['detail'][] = array(
                                    'nojurnal'=>$nojurnal,
                                    'tanggal'=>$tgmulaid,
                                    'nourut'=>$noUrut,
                                    'noakun'=>$akunDt,
                                    'keterangan'=> "Alokasi Biaya Rumah ".$param['kdrumah']." ".$tanggal,
                                    'jumlah'=>-1*($param['jumlah']*$param['jmlhhk']),
                                    'matauang'=>'IDR',
                                    'kurs'=>'1',
                                    'kodeorg'=>$dtKbn,
                                    'kodekegiatan'=>'',
                                    'kodeasset'=>'',
                                    'kodebarang'=>'',
                                    'nik'=>'',
                                    'kodecustomer'=>'',
                                    'kodesupplier'=>'',
                                    'noreferensi'=>'ALK_SIPIL',
                                    'noaruskas'=>'',
                                    'kodevhc'=>'',
                                    'nodok'=>$param['kdrumah'],
                                    'kodeblok'=>'',
                                    'revisi'=>'0',
                                    'kodesegment'=>$defSegment);
                                    $noUrut++;
                                }//end dari cek row dafKeg
                                $isidt+=1;
                            }//end dari foreach dafKeg
                            $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                            try{
                                $owlPDO->exec($insHead); 
                            }catch (PDOException $e){
                                $headErr .= 'Insert Header OSIDE Error : '.$e->getMessage()."\n";
                            }

                            if(empty($headErr)) {
                                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                                $detailErr = '';
                                foreach($dataRes['detail'] as $row) {
                                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                    try{
                                        $owlPDO->exec($insDet); 
                                    }catch (PDOException $e){
                                        $detailErr .= "Insert Detail OSIDE Error : ".$e->getMessage()."\n".$insDet;
                                        break;
                                    }
                                }

                                if($detailErr=='') {
                                    # Header and Detail inserted
                                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                                    $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                                        "kodeorg='".$ptpengguna.
                                        "' and kodekelompok='".$kodeJurnal."'");
                                    try{
                                        $owlPDO->exec($updJurnal); 
                                    }catch (PDOException $e){
                                        echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                        # Rollback if Update Failed
                                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        try{
                                            $owlPDO->exec($RBDet); 
                                        }catch (PDOException $e){
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
                                    }catch (PDOException $e){
                                        echo "Rollback Delete Header Error : ".$e->getMessage();
                                        exit;
                                    }
                                }
                            } else {
                                echo $headErr;
                                exit;
                            }
                        break;
                    }//end dari switch
            }//end if tipe B (BLOK)
    }//end dari else cek jika row kegiatan bkm sipil ada
}//end dari function prosesSipil
?>