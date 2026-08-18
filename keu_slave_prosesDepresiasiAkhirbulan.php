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
 }

#contoh format parameter
#Array
#(
#       kodejurnal    
//      periode
//      keterangan
//      jumlah 
#)
// echo"<pre>";
// print_r($param);
// echo"</pre>";
// exit('warning');

$blm=str_replace("-","",$param['periode']);
// $qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal like '%".$blm."28/".substr($param['kodeorg'],0,4)."/DEP%'");
    // try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  ! Proses Delete Jurnal Error: " . $e->getMessage(); die(); }
// if(@$dataorg[$param['kodeorg']]->tipe=='HOLDING'){
    // $qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal like '%".$blm."28/".substr($param['kodeorg'],0,4)."/DPH%'");
    // try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  ! Proses Delete Jurnal Error: " . $e->getMessage(); die(); }
// }  

$qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal like '%".$blm."28/".substr($param['kodeorg'],0,4)."/DEP%'");
try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  ! Proses Delete Jurnal Error: " . $e->getMessage(); die(); }

$qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal like '%".$blm."28/".substr($param['kodeorg'],0,4)."/DPH%'");
try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  ! Proses Delete Jurnal Error: " . $e->getMessage(); die(); }

$qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal like '%".$blm."28/".substr($param['kodeorg'],0,4)."/DEB%'");
try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  ! Proses Delete Jurnal Error: " . $e->getMessage(); die(); }

 
foreach($param['kodejurnal'] as $rowdt=>$kodeJurnal){
    #ambil noakun pada table parameterjurnal
    $str="select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal  where jurnalid='".$kodeJurnal."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows<1) {
        // $qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal like '%".$blm."28/".substr($param['kodeorg'],0,4)."/DEP%'");
            // try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  ! Proses Delete Jurnal Error: " . $e->getMessage(); die(); }
        // if(@$dataorg[$param['kodeorg']]->tipe=='HOLDING'){
            // $qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal like '%".$blm."28/".substr($param['kodeorg'],0,4)."/DPH%'");
            // try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  ! Proses Delete Jurnal Error: " . $e->getMessage(); die(); }
        // } 
		
		$qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal like '%".$blm."28/".substr($param['kodeorg'],0,4)."/DEP%'");
		try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  ! Proses Delete Jurnal Error: " . $e->getMessage(); die(); }

		$qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal like '%".$blm."28/".substr($param['kodeorg'],0,4)."/DPH%'");
		try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  ! Proses Delete Jurnal Error: " . $e->getMessage(); die(); }

		$qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal like '%".$blm."28/".substr($param['kodeorg'],0,4)."/DEB%'");
		try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  ! Proses Delete Jurnal Error: " . $e->getMessage(); die(); }

		
        exit("Error: Tidak ada kode jurnal untuk ".$kodeJurnal.", Lakukan proses ulang ");
    }
    while($bar=$res->fetch()) {
            $debet=$bar->noakundebet;
            $kredit=$bar->noakunkredit;
    }
    #periksa jika sudah pernah dilakukan
    $str="select * from ".$dbname.".keu_jurnalht where nojurnal 
              like '%".$blm."28/".substr($param['kodeorg'],0,4)."/".$kodeJurnal."%'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0){
       exit("Error: Proses penarikan data Penyusutan sudah pernah dilakukan"); 
    }  
    #======================== Nomor Jurnal =============================
    # Get Journal Counter
    $nojurnal="";
    $konter ='001';
    $tanggal=$param['periode']."-28";
    # Transform No Jurnal dari No Transaksi
    $nojurnal = str_replace("-","",$tanggal)."/".substr($param['kodeorg'],0,4)."/".$kodeJurnal."/".$konter;
    #======================== /Nomor Jurnal ============================
    // Default Segment
    $defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
    $param['jumlah']=$param['rpjurnal'][$kodeJurnal];
    //exit('warning'.$param['rpjurnal'][$kodeJurnal]);
    # Prep Header
    $dataRes['header'] = array(
            'nojurnal'=>$nojurnal,
            'kodejurnal'=>$kodeJurnal,
            'tanggal'=>$tanggal,
            'tanggalentry'=>date('Ymd'),
            'posting'=>1,
            'totaldebet'=>$param['jumlah'],
            'totalkredit'=>-1*$param['jumlah'],
            'amountkoreksi'=>'0',
            'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
            'autojurnal'=>'1',
            'matauang'=>'IDR',
            'kurs'=>'1',
            'revisi'=>'0'
    );
    # Data Detail
    $noUrut = 1;
    $dataRes['detail']=array();
    $ketdt=$param['keterangan'][$rowdt]." Periode:".$_POST['periode'];
    $param['assetMesin']="";
    if($param['assetMesin']!=''){
        $param['jumlah']=$param['jumlah']-$param['assetMesin'];
        $ketdt=$param['keterangan'][$rowdt]." Biaya Langsung Periode:".$_POST['periode'];
        $sNoakun="select * from ".$dbname.".setup_parameterappl where kodeparameter='".$param['tipeasset'][$rowdt]."1'";
        $rNoakun=fetchData($sNoakun);
        if(count($rNoakun)==0){
            exit('warning: '.$_SESSION['lang']['noakun'].' '.$_SESSION['lang']['kosong']);
        }
        $dtNoakunAsst=$rNoakun[0]['nilai'];
    }
    if(intval($param['jumlah'])!=0){
            # Debet
            $dataRes['detail'][] = array(
                    'nojurnal'=>$nojurnal,
                    'tanggal'=>$tanggal,
                    'nourut'=>$noUrut,
                    'noakun'=>$debet,
                    'keterangan'=>$ketdt,
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
                    'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
                    'noaruskas'=>'',
                    'kodevhc'=>'',
                    'nodok'=>'',
                    'kodeblok'=>'',
                    'revisi'=>'0',
                    'kodesegment'=>$defSegment
            );
            $noUrut++;
    }
    if($param['assetMesin']!=''){
            # Debet Mesin-mesin
            $dataRes['detail'][] = array(
                    'nojurnal'=>$nojurnal,
                    'tanggal'=>$tanggal,
                    'nourut'=>$noUrut,
                    'noakun'=>$dtNoakunAsst,
                    'keterangan'=>$ketdt.":".$_POST['periode'],
                    'jumlah'=>$param['assetMesin'],
                    'matauang'=>'IDR',
                    'kurs'=>'1',
                    'kodeorg'=>$param['kodeorg'],
                    'kodekegiatan'=>'',
                    'kodeasset'=>'',
                    'kodebarang'=>'',
                    'nik'=>'',
                    'kodecustomer'=>'',
                    'kodesupplier'=>'',
                    'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
                    'noaruskas'=>'',
                    'kodevhc'=>'',
                    'nodok'=>'',
                    'kodeblok'=>'',
                    'revisi'=>'0',
                    'kodesegment'=>$defSegment
            );
            $noUrut++;
            $param['jumlah']=$param['jumlah']+$param['assetMesin'];
        }
        # Kredit
        $dataRes['detail'][] = array(
                'nojurnal'=>$nojurnal,
                'tanggal'=>$tanggal,
                'nourut'=>$noUrut,
                'noakun'=>$kredit,
                'keterangan'=>$param['keterangan'][$rowdt]." Periode:".$_POST['periode'],
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
                'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
                'noaruskas'=>'',
                'kodevhc'=>'',
                'nodok'=>'',
                'kodeblok'=>'',
                'revisi'=>'0',
                'kodesegment'=>$defSegment
        );
        $noUrut++;

        #===========EXECUTE
        $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
        try{$owlPDO->exec($insHead); }
        catch (PDOException $e) {
            $headErr .= "Insert Header Error :" . $e->getMessage()."\n".$insHead;
        }        
        if(empty($headErr)) {
        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
        $detailErr = '';
        foreach($dataRes['detail'] as $row) {
            $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                try{$owlPDO->exec($insDet); }
                catch (PDOException $e) {
                    $detailErr .= "Insert Detail Error : " . $e->getMessage()."\n".$insDet;
                    break;
                }                 
        }

        if($detailErr=='') {
            # Header and Detail inserted
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
            }
        else {
            echo $detailErr;
            # Rollback, Delete Header
            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                try{$owlPDO->exec($RBDet); }
                catch (PDOException $e) {
                    echo "Rollback Delete Header Error : " . $e->getMessage();
                     exit;
                }               
            }
        } else {
            echo $headErr;
            exit;
        }     
}
if($detailErr=='') {
        echo "1";
}