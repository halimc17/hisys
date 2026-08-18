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
$tanggal=$param['periode']."-28";

if($param['row']=='1'){
	#periksa dan hapus transaksi untuk data yang sudah di proses pada periode yang sama    
    $str="delete from ".$dbname.".keu_jurnalht where kodejurnal in ('GJHO0','GJHO1','GJHO2','GJHO3','GJHO4','GJHO5','GJHO6') and tanggal='".$tanggal."' 
    and nojurnal like '%/".$param['kodeorg']."/%'";
	$owlPDO->exec($str);
   // exit ("Error".$str);
}

#==========================================konfigurasi database
/*
'GJHO0',	'GAJI STAFF HEAD OFFICE'
'GJHO1',	'GAJI NON-STAFF HEAD OFFICE'
'GJHO2',	'PREMI / LEBUR HEAD OFFICE'
'GJHO3',	'TUNJANGAN LAIN HEAD OFFICE'
'GJHO4',	'THR HEAD OFFIC'
'GJHO5',	'BONUS HEAD OFFICE'
'GJHO6',	'PENGOBATAN HEAD OFFICE'
*/

#============================================konfigurasi database

#==Komfigurasi komponen gaji
# 1	Gaji Pokok
# 2	Tunjangan Jabatan
# 14	Rapel
# 16	Premi Pengawasan
# 21	Klaim Pengobatan
# 26	Bonus
# 27	Tunjangan Fasilitas
# 28	THR
# 30	Tunjangan Profesi
# 31	Tunjangan Masa Kerja
# 32	Premi
# 33	Lembur
# 34	Penalti
#
#=======================================================
#parameter
#   namakaryawan  
#   karyawanid   
#   komponen     
#   namakomponen  
#   subbagian      
#   mesin       
#   jumlah         
#   tipeorganisasi 
#   periode

// Default Segment
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
prosesGajiKebun();   
//=====================================


function prosesGajiKebun(){ 

// exit("Error:A");

global $conn;
global $tanggal;
global $param;
global $dbname;
global $defSegment;
global $owlPDO;
#karyawan kebun
#output pada jurnal kolom noreferensi ALK_GAJI  
if($param['komponen']==1 or $param['komponen']==14)
  $group='GJHO0';
elseif($param['komponen']==16 or $param['komponen']==32 or $param['komponen']==33)
  $group='GJHO2';
elseif($param['komponen']==28)
  $group='GJHO4';  
elseif($param['komponen']==26)
  $group='GJHO5';  
elseif($param['komponen']==21)
  $group='GJHO6';
else
  $group='GJHO3';  //defaultnya tunjangan

$str="select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='".$group."' limit 1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
if($numrows<1)
    exit("Error: No.Akun pada parameterjurnal belum ada untuk ".$param['namakomponen']);
else
{
    $akundebet='';
    $akunkredit='';
    $bar=$res->fetch();
    $akundebet=$bar->noakundebet;
    $akunkredit=$bar->noakunkredit;
}

   #proses data
    $kodeJurnal = $group;
    #======================== Nomor Jurnal =============================
    # Get Journal Counter
    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
        "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' ");
    $tmpKonter = fetchData($queryJ);
    $konter = addZero($tmpKonter[0]['nokounter']+1,3);

    # Transform No Jurnal dari No Transaksi
    $nojurnal = str_replace("-","",$tanggal)."/".$param['kodeorg']."/".$kodeJurnal."/".$konter;
    #======================== /Nomor Jurnal ============================


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
            'noreferensi'=>'ALK_GAJI',
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
            'tanggal'=>$tanggal,
            'nourut'=>$noUrut,
            'noakun'=>$akundebet,
            'keterangan'=> $param['namakomponen'].' '.$param['periode'],
            'jumlah'=>$param['jumlah'],
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$param['kodeorg'],
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>'',
            'nik'=>$param['karyawanid'],
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>'ALK_GAJI',
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
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
            'keterangan'=> $param['namakomponen'].' '.$param['periode'],
            'jumlah'=>-1*$param['jumlah'],
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$param['kodeorg'],
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>'',
            'nik'=>$param['karyawanid'],
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>'ALK_GAJI',
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>'',
            'revisi'=>'0',
            'kodesegment'=>$defSegment
        );
        $noUrut++;      

        $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
        try{$owlPDO->exec($insHead); }
        catch (PDOException $e) {
            $headErr .= 'Insert Header HO Error : '.$e->getMessage()."\n";
        }           

        if(empty($headErr)) {
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
            $detailErr = '';
            foreach($dataRes['detail'] as $row) {
                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                try{$owlPDO->exec($insDet); }
                catch (PDOException $e) {
                    $detailErr .= "Insert Detail HO Error : ".$e->getMessage()."\n";
                    break;
                }                 
            }
            if($detailErr=='') {
                # Header and Detail inserted
                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                    "kodeorg='".$_SESSION['org']['kodeorganisasi'].
                    "' and kodekelompok='".$kodeJurnal."'");
                    try{$owlPDO->exec($updJurnal); }
                    catch (PDOException $e) {
                        echo "Update Kode Jurnal HO Error : ".$e->getMessage()."\n";
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            try{$owlPDO->exec($insHead); }
                            catch (PDOException $e) {
                            echo "Rollback Delete Header HO Error : ".$e->getMessage()."\n";
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
                    echo "Rollback Delete Header HO Error : ".$e->getMessage();
                    exit;
                }                
            }
        } else {
            echo $headErr;
            exit;
        }                 
}