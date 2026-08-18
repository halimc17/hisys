<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$jenissave=checkPostGet('jenissave','');
$per=checkPostGet('per','');
$karyawanidsave=checkPostGet('karyawanidsave','');
$jumlahsave=checkPostGet('jumlahsave','');
$kdorgsave=checkPostGet('kdorgsave','');
$unit=checkPostGet('unit','');
$jenis=checkPostGet('jenis','');
$pengalisave=checkPostGet('pengalisave','');

switch($proses)
{
    
    case'del':
        $sFil="select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='".$_POST['tipe']."' and lokasitugas='".$unit."'";
        #delete dlo semua 
        $iDel="delete from ".$dbname.".sdm_gaji where kodeorg='".$unit."' "
            . " and periodegaji='".$per."' and idkomponen='".$jenis."' and karyawanid in (".$sFil.") ";
        //exit("Error:$iDel");
        try{$owlPDO->exec($iDel); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
    break;
    
    
    case'savedata':
        if($jumlahsave=='0' or $jumlahsave==''){

        }else{   
            $str="insert into ".$dbname.".sdm_gaji (`kodeorg`,`periodegaji`,`karyawanid`,`idkomponen`,`jumlah`,`pengali`) values ('".$kdorgsave."','".$per."','".$karyawanidsave."','".$jenissave."','".$jumlahsave."','".$pengalisave."')";

            try{$owlPDO->exec($str); }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }
        }
        
        
    break; 
    default:
}

?>