<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// exit('error');
$dataorg = array();
$dtstr="select * from ".$dbname.".organisasi where  kodeorganisasi = '".$param['kodeorg']."'";
$str=$owlPDO->query($dtstr);
$str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
    $dataorg[$bar->kodeorganisasi] = $bar;
	$orgpt=$bar->induk;
 }
$tanggal=$param['sampai'];
$param['karyawanid']=str_replace("#","'",$param['karyawanid']);

#parameter
#   namakaryawan  
#   karyawanid   
#   subbagian         
#   jumlah         
#   tipeorganisasi 
#   dari
#   sampai


  #karyawan kebun
  #output pada jurnal kolom noreferensi ALK_GAJI_LBR  

#periksa di perawatan
$str="select distinct b.kodekegiatan,b.kodeorg,c.noakun,b.kodesegment,a.tanggal from ".$dbname.".kebun_kehadiran_vw a 
      left join ".$dbname.".kebun_perawatan_vw b on a.notransaksi=b.notransaksi 
      left join ".$dbname.".setup_kegiatan c on b.kodekegiatan=c.kodekegiatan    
      where a.tanggal between '".$param['dari']."' and '".$param['sampai']."'
      and a.karyawanid in(".$param['karyawanid'].") and a.unit='".$param['kodeorg']."' 
      having noakun!='' order by b.kodeorg asc, b.kodekegiatan";
//exit('warning'.$str);
$tempBlok="";
$tmpKegiatan="";
$jmlhKeg=array();
$dtSegment=array();
$dtNoakun=array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$res1=$res;
while($rData=$res1->fetch()){
        if(($tempBlok!=$rData['kodeorg'])&&($tmpKegiatan!=$rData['kodekegiatan'])){
                $rData['kodesegment']="";
                $tempBlok=$rData['kodeorg'];
                $tmpKegiatan=$rData['kodekegiatan'];
                $jmlhKeg[$rData['kodeorg'].$rData['kodekegiatan']]=1;
                $dtSegment[$rData['kodeorg'].$rData['kodekegiatan']]=$rData['kodekegiatan'];
                $dtNoakun[$rData['kodeorg'].$rData['kodekegiatan']]=$rData['noakun'];
        }else{
                $jmlhKeg[$rData['kodeorg'].$rData['kodekegiatan']]+=1;
                $dtSegment[$rData['kodeorg'].$rData['kodekegiatan']]=$rData['kodekegiatan'];
				$tempBlok=$rData['kodeorg'];
                $tmpKegiatan=$rData['kodekegiatan'];
        }
        $lstBlok[$rData['kodeorg']]=$rData['kodeorg'];
        $lstKegiatan[$rData['kodekegiatan']]=$rData['kodekegiatan'];
        $totBrsKeg+=1;
}

#pabrikasi
$kdPabrikasi=0;
// $str3="select kodepabrikasi from ".$dbname.".pabrikasi_absensidt a left join ".$dbname.".pabrikasi_absensiht b
       // on a.notransaksi=b.notransaksi where tanggal between '".$param['dari']."' and '".$param['sampai']."'
       // and kodeorg like '".$param['kodeorg']."%' and karyawanid in (".$param['karyawanid'].")";
// $res3=fetchdata($str3);
// foreach($res3 as $rwData){
    // $lstPabrikasi[$rData['kodepabrikasi']]+=1;
    // $dafPabrikasi[$rData['kodepabrikasi']]=$rData['kodepabrikasi'];
    // $kdPabrikasi+=1;
// }


#panen
$str2="select distinct kodeorg,kodesegment,tanggal from ".$dbname.".kebun_prestasi_vw a   
      where tanggal between '".$param['dari']."' and '".$param['sampai']."'
      and karyawanid in (".$param['karyawanid'].") and unit='".$param['kodeorg']."' and notransaksi like '%PNN%'";
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_ASSOC);
$numrows=$res2->rowCount();
$jum=$numrows;
//$param['jumlah']=$param['jumlah']/($jum+$totBrsKeg);
if($totBrsKeg!=0){
#porsi perawatan
$porsiPerawatan=$totBrsKeg/($totBrsKeg+$jum+$kdPabrikasi)*$param['jumlah'];
}

#porsi pabrikasi
if($kdPabrikasi!=0){
 $porsiPabrikasi=$kdPabrikasi/($totBrsKeg+$jum+$kdPabrikasi)*$param['jumlah'];   
}

if($jum!=0){
#porsi panen
$porsiPanen=$jum/($totBrsKeg+$jum+$kdPabrikasi)*$param['jumlah'];
}

#================HAPUS DULU YANG LAMA 
if($param['row']=='1'){ //dilakukan hanya pada loop baris pertama
        $nr=str_replace("-", "", $tanggal)."/".$param['kodeorg']."/M0/";
        $stw="delete from ".$dbname.".keu_jurnalht where nojurnal like '".$nr."%' and noreferensi='ALK_GAJI_LBR'";
        $owlPDO->exec($stw);
        $nr=str_replace("-", "", $tanggal)."/".$param['kodeorg']."/PNN01/";
        $stw="delete from ".$dbname.".keu_jurnalht where nojurnal like '".$nr."%' and noreferensi='ALK_GAJI_LBR'";
        $owlPDO->exec($stw);
        $nr=str_replace("-", "", $tanggal)."/".$param['kodeorg']."/PBK00/";
        $stw="delete from ".$dbname.".keu_jurnalht where nojurnal like '".$nr."%' and noreferensi='ALK_GAJI_LBR'";
        $owlPDO->exec($stw);
}
#===================================
#==========================================
#perawatan
$numrows=$res->rowCount();
if($numrows>0){
    #======================== Nomor Jurnal =============================
    $kodeJurnal = 'M0';
    $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit',
    " jurnalid='".$kodeJurnal."'");
        $resParam = fetchData($queryParam);
      $akunkredit=$resParam[0]['noakunkredit'];  

    # Get Journal Counter
	$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
            "kodeorg='".$orgpt."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' ");
        $tmpKonter = fetchData($queryJ);
        $konter = addZero($tmpKonter[0]['nokounter']+1,3);
	// exit("Error:".$konter);

    # Transform No Jurnal dari No Transaksi
    $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
    #======================== Nomor Jurnal =============================

 #prep header
        # Prep Header
        $dataResPerawatan['header'] = array(
            'nojurnal'=>$nojurnal,
            'kodejurnal'=>$kodeJurnal,
            'tanggal'=>$tanggal,
            'tanggalentry'=>date('Ymd'),
            'posting'=>1,
            'totaldebet'=>$porsiPerawatan,
            'totalkredit'=>-1*$porsiPerawatan,
            'amountkoreksi'=>'0',
            'noreferensi'=>'ALK_GAJI_LBR',
            'autojurnal'=>'1',
            'matauang'=>'IDR',
            'kurs'=>'1',
            'revisi'=>'0'
        );
#====================================
#execute
        # Data Detail
        $noUrut = 1; 
                foreach($lstBlok as $brsBlok=>$kodeBlok){
                        foreach($lstKegiatan as $brsKeg=>$kodeKegiatan){
                                if(!empty($jmlhKeg[$kodeBlok.$kodeKegiatan])){
                            // if($jmlhKeg[$kodeBlok.$kodeKegiatan] && count($jmlhKeg[$kodeBlok.$kodeKegiatan])!=0){
                                        if($porsiPerawatan<0)
                                        {
                                           $akundebet=$akunkredit;
                                           //$param['jumlah']=$param['jumlah']*-1;
                                                $param['jumlah']=($jmlhKeg[$kodeBlok.$kodeKegiatan]/$totBrsKeg*$porsiPerawatan)*-1;
                                        }
                                        else{
                                                $param['jumlah']=($jmlhKeg[$kodeBlok.$kodeKegiatan]/$totBrsKeg*$porsiPerawatan);
												
                                                $akundebet=substr($kodeKegiatan,0,7);
                                        }
                                                  # Debet
                                                        $dataResPerawatan['detail'][] = array(
                                                                'nojurnal'=>$nojurnal,
                                                                'tanggal'=>$tanggal,
                                                                'nourut'=>$noUrut,
                                                                'noakun'=>$akundebet,
                                                                'keterangan'=> 'Alokasi Gaji(Unalocated) '.$tanggal,
                                                                'jumlah'=>$param['jumlah'],
                                                                'matauang'=>'IDR',
                                                                'kurs'=>'1',
                                                                'kodeorg'=>$param['kodeorg'],
                                                                'kodekegiatan'=>$kodeKegiatan,
                                                                'kodeasset'=>'',
                                                                'kodebarang'=>'',
                                                                'nik'=>'',
                                                                'kodecustomer'=>'',
                                                                'kodesupplier'=>'',
                                                                'noreferensi'=>'ALK_GAJI_LBR',
                                                                'noaruskas'=>'',
                                                                'kodevhc'=>'',
                                                                'nodok'=>'',
                                                                'kodeblok'=>$kodeBlok,
                                                                'revisi'=>'0',
                                                                'kodesegment'=>$dtSegment[$kodeBlok.$kodeKegiatan]
                                                        );
                                                        $noUrut++;
                                                        //exit("error:".$dtSegment[$kodeBlok.$kodeKegiatan]."__".$kodeBlok."__".$kodeKegiatan);
                                                        # Kredit
                                                        $dataResPerawatan['detail'][] = array(
                                                                'nojurnal'=>$nojurnal,
                                                                'tanggal'=>$tanggal,
                                                                'nourut'=>$noUrut,
                                                                'noakun'=>$akunkredit,
                                                                'keterangan'=> 'Alokasi Gaji(Unalocated) '.$tanggal,
                                                                'jumlah'=>-1*$param['jumlah'],
                                                                'matauang'=>'IDR',
                                                                'kurs'=>'1',
                                                                'kodeorg'=>$param['kodeorg'],
                                                                'kodekegiatan'=>$kodeKegiatan,
                                                                'kodeasset'=>'',
                                                                'kodebarang'=>'',
                                                                'nik'=>'',
                                                                'kodecustomer'=>'',
                                                                'kodesupplier'=>'',
                                                                'noreferensi'=>'ALK_GAJI_LBR',
                                                                'noaruskas'=>'',
                                                                'kodevhc'=>'',
                                                                'nodok'=>'',
                                                                'kodeblok'=>$kodeBlok,
                                                                'revisi'=>'0',
                                                                'kodesegment'=>$dtSegment[$kodeBlok.$kodeKegiatan]
                                                        );
                                                        $noUrut++;           
                                }
                        }
        }

                    $insHead = insertQuery($dbname,'keu_jurnalht',$dataResPerawatan['header']);
                    try{$owlPDO->exec($insHead); }
                    catch (PDOException $e) {
                        $headErr .= 'Insert Header  Error : '.$e->getMessage()."\n";
                    }                             

                    if(empty($headErr)) {
                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                        $detailErr = '';
                        foreach($dataResPerawatan['detail'] as $row) {
                            $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                            try{$owlPDO->exec($insDet); }
                            catch (PDOException $e) {
                                $detailErr .= "Insert Detail Perawatan Error : ".$e->getMessage();
                                break;
                            }                                 
                        }

                        if($detailErr=='') {
                            # Header and Detail inserted
                            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
								$updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
									"kodeorg='".$orgpt.
									"' and kodekelompok='".$kodeJurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' ");
								
                                try{$owlPDO->exec($updJurnal); }
                                catch (PDOException $e) {
                                    echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        try{$owlPDO->exec($RBDet); }
                                        catch (PDOException $e) {
                                            echo "Rollback Delete Header BTL Error : ".$e->getMessage()."\n";
                                            exit;
                                        }                                     
                                    exit;                                    
                                }                                 
                        } else {
                            echo $detailErr;
                            # Rollback, Delete Header
                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            try{$owlPDO->exec($RBDet); }
                            catch (PDOException $e) {
                                echo "Rollback Delete Header BTL Error : ".$e->getMessage()."\n";
                                exit;
                            }    
                        }
                    } else {
                        echo $headErr;
                        exit;
                    }                 
       // }         
}
#+=========================pabrikasi
if($kdPabrikasi>0){
    #======================== Nomor Jurnal =============================
    $kodeJurnal = 'PBK00';
    $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',
    " jurnalid='".$kodeJurnal."'");
        $resParam = fetchData($queryParam);
      $akunkredit=$resParam[0]['noakunkredit'];
      $akundebet=$resParam[0]['noakundebet'];  

    # Get Journal Counter
	$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
            "kodeorg='".$orgpt."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' ");
        $tmpKonter = fetchData($queryJ);
        $konter = addZero($tmpKonter[0]['nokounter']+1,3);

    # Transform No Jurnal dari No Transaksi
    $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
    #======================== Nomor Jurnal =============================

 #prep header
        # Prep Header
        $dataResPerawatan['header'] = array(
            'nojurnal'=>$nojurnal,
            'kodejurnal'=>$kodeJurnal,
            'tanggal'=>$tanggal,
            'tanggalentry'=>date('Ymd'),
            'posting'=>1,
            'totaldebet'=>$porsiPabrikasi,
            'totalkredit'=>-1*$porsiPabrikasi,
            'amountkoreksi'=>'0',
            'noreferensi'=>'ALK_GAJI_LBR',
            'autojurnal'=>'1',
            'matauang'=>'IDR',
            'kurs'=>'1',
            'revisi'=>'0'
        );
#====================================
#execute
        # Data Detail
        $noUrut = 1; 
        //$akundebet=$akunkredit;
                        foreach($dafPabrikasi as $brsBlok=>$kodeBlok){
                       
                                if(count($lstPabrikasi[$kodeBlok])!=0){
                                        if($porsiPabrikasi<0){
                                           $param['jumlah']=($lstPabrikasi[$kodeBlok]/$kdPabrikasi*$porsiPabrikasi)*-1;
                                        }
                                        else{
                                            $param['jumlah']=($lstPabrikasi[$kodeBlok]/$kdPabrikasi*$porsiPabrikasi);
                                        }
                                                  # Debet
                                                        $dataResPerawatan['detail'][] = array(
                                                                'nojurnal'=>$nojurnal,
                                                                'tanggal'=>$tanggal,
                                                                'nourut'=>$noUrut,
                                                                'noakun'=>$akundebet,
                                                                'keterangan'=> 'Alokasi Gaji(Unalocated) '.$tanggal,
                                                                'jumlah'=>$param['jumlah'],
                                                                'matauang'=>'IDR',
                                                                'kurs'=>'1',
                                                                'kodeorg'=>$param['kodeorg'],
                                                                'kodekegiatan'=>'',
                                                                'kodeasset'=>'',
                                                                'kodebarang'=>'',
                                                                'nik'=>'',
                                                                'kodecustomer'=>'',
                                                                'kodesupplier'=>'',
                                                                'noreferensi'=>'ALK_GAJI_LBR',
                                                                'noaruskas'=>'',
                                                                'kodevhc'=>'',
                                                                'nodok'=>'',
                                                                'kodeblok'=>$kodeBlok,
                                                                'revisi'=>'0',
                                                                'kodesegment'=>''
                                                        );
                                                        $noUrut++;
                                                        //exit("error:".$dtSegment[$kodeBlok.$kodeKegiatan]."__".$kodeBlok."__".$kodeKegiatan);
                                                        # Kredit
                                                        $dataResPerawatan['detail'][] = array(
                                                                'nojurnal'=>$nojurnal,
                                                                'tanggal'=>$tanggal,
                                                                'nourut'=>$noUrut,
                                                                'noakun'=>$akunkredit,
                                                                'keterangan'=> 'Alokasi Gaji(Unalocated) '.$tanggal,
                                                                'jumlah'=>-1*$param['jumlah'],
                                                                'matauang'=>'IDR',
                                                                'kurs'=>'1',
                                                                'kodeorg'=>$param['kodeorg'],
                                                                'kodekegiatan'=>'',
                                                                'kodeasset'=>'',
                                                                'kodebarang'=>'',
                                                                'nik'=>'',
                                                                'kodecustomer'=>'',
                                                                'kodesupplier'=>'',
                                                                'noreferensi'=>'ALK_GAJI_LBR',
                                                                'noaruskas'=>'',
                                                                'kodevhc'=>'',
                                                                'nodok'=>'',
                                                                'kodeblok'=>$kodeBlok,
                                                                'revisi'=>'0',
                                                                'kodesegment'=>''
                                                        );
                                                        $noUrut++;           
                                }
                        }

                    $insHead = insertQuery($dbname,'keu_jurnalht',$dataResPerawatan['header']);
                    try{$owlPDO->exec($insHead); }
                    catch (PDOException $e) {
                        $headErr .= 'Insert Header  Error : '.$e->getMessage()."\n";
                    }                             

                    if(empty($headErr)) {
                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                        $detailErr = '';
                        foreach($dataResPerawatan['detail'] as $row) {
                            $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                            try{$owlPDO->exec($insDet); }
                            catch (PDOException $e) {
                                $detailErr .= "Insert Detail Perawatan Error : ".$e->getMessage();
                                break;
                            }                                 
                        }

                        if($detailErr=='') {
                            # Header and Detail inserted
                            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                            $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
									"kodeorg='".$orgpt.
									"' and kodekelompok='".$kodeJurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' ");
                                try{$owlPDO->exec($updJurnal); }
                                catch (PDOException $e) {
                                    echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        try{$owlPDO->exec($RBDet); }
                                        catch (PDOException $e) {
                                            echo "Rollback Delete Header BTL Error : ".$e->getMessage()."\n";
                                            exit;
                                        }                                     
                                    exit;                                    
                                }                                 
                        } else {
                            echo $detailErr;
                            # Rollback, Delete Header
                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            try{$owlPDO->exec($RBDet); }
                            catch (PDOException $e) {
                                echo "Rollback Delete Header BTL Error : ".$e->getMessage()."\n";
                                exit;
                            }    
                        }
                    } else {
                        echo $headErr;
                        exit;
                    }
}

#+=========================panen
$numrows=$res2->rowCount();
if($numrows>0)
{
   #======================== Nomor Jurnal =============================
    $kodeJurnal = 'PNN01';#panen
    $queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',
    " jurnalid='".$kodeJurnal."'");
        $resParam = fetchData($queryParam);

      $akunkredit=$resParam[0]['noakunkredit']; 
      $akundebet =$resParam[0]['noakundebet']; 
      $kegpanen=$akundebet."01";

    # Get Journal Counter
    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
		"kodeorg='".$orgpt."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' ");
	$tmpKonter = fetchData($queryJ);
	$konter = addZero($tmpKonter[0]['nokounter']+1,3);
    # Transform No Jurnal dari No Transaksi
    $nojurnal = str_replace("-", "", $tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
    #======================== Nomor Jurnal =============================

     #prep header
        # Prep Header
        $dataResPanen['header'] = array(
            'nojurnal'=>$nojurnal,
            'kodejurnal'=>$kodeJurnal,
            'tanggal'=>$tanggal,
            'tanggalentry'=>date('Ymd'),
            'posting'=>1,
            'totaldebet'=>$porsiPanen,
            'totalkredit'=>-1*$porsiPanen,
            'amountkoreksi'=>'0',
            'noreferensi'=>'ALK_GAJI_LBR',
            'autojurnal'=>'1',
            'matauang'=>'IDR',
            'kurs'=>'1',
            'revisi'=>'0'            
        );
#=================
        # Data Detail
        $noUrut = 1; 
        while($bar2=$res2->fetch())
        {
            if($porsiPanen<0)
            {
                $x=$akundebet;
                $akundebet=$akunkredit;
                $akunkredit=$x;
                //$param['jumlah']=$param['jumlah']*-1;
                                $param['jumlah']=($porsiPanen/$jum)*-1;
            }else{

                                $param['jumlah']=($porsiPanen/$jum);
                        }

                  # Debet
                    $dataResPanen['detail'][] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$tanggal,
                        'nourut'=>$noUrut,
                        'noakun'=>$akundebet,
                        'keterangan'=> 'Alokasi Gaji(Unalocated) '.$tanggal,
                        'jumlah'=>$param['jumlah'],
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$param['kodeorg'],
                        'kodekegiatan'=>$kegpanen,
                        'kodeasset'=>'',
                        'kodebarang'=>'',
                        'nik'=>'',
                        'kodecustomer'=>'',
                        'kodesupplier'=>'',
                        'noreferensi'=>'ALK_GAJI_LBR',
                        'noaruskas'=>'',
                        'kodevhc'=>'',
                        'nodok'=>'',
                        'kodeblok'=>$bar2['kodeorg'],
                        'revisi'=>'0',
                        'kodesegment'=>$bar2['kodesegment']
                    );
                    $noUrut++;

                    # Kredit
                    $dataResPanen['detail'][] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$tanggal,
                        'nourut'=>$noUrut,
                        'noakun'=>$akunkredit,
                        'keterangan'=> 'Alokasi Gaji(Unalocated) '.$tanggal,
                        'jumlah'=>-1*$param['jumlah'],
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$param['kodeorg'],
                        'kodekegiatan'=>$kegpanen,
                        'kodeasset'=>'',
                        'kodebarang'=>'',
                        'nik'=>'',
                        'kodecustomer'=>'',
                        'kodesupplier'=>'',
                        'noreferensi'=>'ALK_GAJI_LBR',
                        'noaruskas'=>'',
                        'kodevhc'=>'',
                        'nodok'=>'',
                        'kodeblok'=>$bar2['kodeorg'],
                        'revisi'=>'0',
                        'kodesegment'=>$bar2['kodesegment']
                    );
                    $noUrut++;           
        }
        #hantam=========================
                    $insHead = insertQuery($dbname,'keu_jurnalht',$dataResPanen['header']);
                    try{$owlPDO->exec($insHead); }
                    catch (PDOException $e) {
                        $headErr .= 'Insert Header  Error : '.$e->getMessage()."\n";
                    }      

                    if($headErr=='') {
                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                        $detailErr = '';
                        foreach($dataResPanen['detail'] as $row) {
                            $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                            try{$owlPDO->exec($insDet); }
                            catch (PDOException $e) {
                                $detailErr .= "Insert Detail panen Error : ".$e->getMessage()."\n";
                                break;                                
                            }                             
                        }

                        if($detailErr=='') {
                            # Header and Detail inserted
                            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                            $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
									"kodeorg='".$orgpt.
									"' and kodekelompok='".$kodeJurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' ");
                                try{$owlPDO->exec($updJurnal); }
                                catch (PDOException $e) {
                                    echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";    
                                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                        try{$owlPDO->exec($RBDet); }
                                        catch (PDOException $e) {
                                            echo "Rollback Delete Header  Error : ".$e->getMessage()."\n";
                                            exit;
                                        }            
                                     exit;                                        
                                }                            
                        } else {
                            echo $detailErr;
                            # Rollback, Delete Header
                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            try{$owlPDO->exec($RBDet); }
                            catch (PDOException $e) {
                                echo "Rollback Delete Header  Error : ".$e->getMessage()."\n";
                                exit;
                            }    
                        }
                    } else {
                        echo $headErr;
                        exit;
                    }                    
}
?>