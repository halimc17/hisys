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

/*
$proses=$_POST['proses'];
$jenissave=$_POST['jenissave'];
$per=$_POST['per'];
$karyawanidsave=$_POST['karyawanidsave'];
$jumlahsave=$_POST['jumlahsave'];
$kdorgsave=$_POST['kdorgsave'];
*/


/*
$unit=$_POST['unit'];
$jenis=$_POST['jenis'];
*/
	

$unit=checkPostGet('unit','');
$jeniscuti=checkPostGet('jeniscuti','');
$tahun=checkPostGet('tahun','');
$tipekar=checkPostGet('tipekar','');
$golkar=checkPostGet('golkar','');	

$dari=checkPostGet('dari','');	
$sampai=checkPostGet('sampai','');	

$karidsave=checkPostGet('karidsave','');
$hakcuti=checkPostGet('hakcuti','');
switch($proses)
{
    
    case'del':
        
        #delete dlo semua 
        $str="delete from ".$dbname.".sdm_5cutilainht";
        //exit("Error:$iDel");
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
    break;
    
    
    case'savedata':
	
		#delete 1st
		$str="delete from ".$dbname.".sdm_5cutilainht where `kodeorg`='".$unit."' and `karyawanid`='".$karidsave."' and `periodecuti`='".$tahun."' and `jeniscuti`='".$jeniscuti."' ";
		try{
			$owlPDO->exec($str); 
			}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		
		$str="insert into ".$dbname.".sdm_5cutilainht (`kodeorg`,`karyawanid`,`periodecuti`,`jeniscuti`,`dari`,`sampai`,`hakcuti`,`sisa`,`updateby`)
				values ('".$unit."','".$karidsave."','".$tahun."','".$jeniscuti."','".$dari."','".$sampai."','".$hakcuti."','".$hakcuti."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str); 
			}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
    break; 
    default:
}

?>