<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include('lib/zLib.php');


$id = $_POST['id'];
$kodeorg = $_POST['kodeorg'];
$karid = $_POST['karid'];
$periodecuti = $_POST['periodecuti'];
$hakcutiadj = $_POST['hakcutiadj'];

$hakcuti_ht=0;
$cutitambahan_ht=0;
$adjs_hakcuti_ht=0;
$diambil_ht=0;
$sisa = 0;

try {
    $owlPDO->beginTransaction();

        echo $sCek="select * from ".$dbname.".sdm_cutiht where kodeorg= '".$kodeorg."' and karyawanid='".$karid."' and periodecuti='".$periodecuti."'";
        $rCek=fetchData($sCek);
        if(count($rCek) != 0){
            $hakcuti_ht = $rCek[0]['hakcuti'];
            $cutitambahan_ht = $rCek[0]['cutitambahan'];
            $adjs_hakcuti_ht = $rCek[0]['adjs_hakcuti'] - $hakcutiadj;
            $diambil_ht = $rCek[0]['diambil'];

            $sisa = ($hakcuti_ht + $cutitambahan_ht + $adjs_hakcuti_ht) - $diambil_ht  ;

            $str="update ".$dbname.".sdm_cutiht 
                    set adjs_hakcuti ='".$adjs_hakcuti_ht."',
                    sisa='".$sisa."'
                    where kodeorg='".$kodeorg."' and karyawanid='".$karid."' and periodecuti='".$periodecuti."'";  
            $owlPDO->exec($str);

        }else{
            exit("Warning : Karyawan belum ada di cutiht :" . getKary($karid,'namakaryawan'));
        }	

        $str1 = "delete from " . $dbname . ".sdm_5cutiadjsment where id='".$id."' and kodeorg= '".$kodeorg."' and karyawanid='".$karid."' and periodecuti='".$periodecuti."' "; 
        $owlPDO->exec($str1);

    $owlPDO->commit();
    } catch (PDOException $e) {
        $owlPDO->rollback();
        echo "Error, " . addslashes($e->getMessage());
        die();
    }
?>
