<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

if($_POST['proses']=="copyData")
{
    $kodePt1=$_POST['kodePt1'];
    $kodePt2=$_POST['kodePt2'];
    $kodekelompok=$_POST['kodekelompok'];
    $tipe=$_POST['tipe'];
    
    if($tipe==0)
    {
      $Pt2Cek="select kodekelompok,keterangan,nokounter from ".$dbname.".keu_5kelompokjurnal where 
      kodeorg = '".$kodePt2."'";
      $Pt1Cek="select kodekelompok,keterangan,nokounter from ".$dbname.".keu_5kelompokjurnal where 
      kodeorg = '".$kodePt1."'";
    }
    else
    {
      $Pt2Cek="select kodekelompok,keterangan,nokounter from ".$dbname.".keu_5kelompokjurnal where 
      kodeorg = '".$kodePt2."' and kodekelompok='".$kodekelompok."'";
      $Pt1Cek="select kodekelompok,keterangan,nokounter from ".$dbname.".keu_5kelompokjurnal where 
      kodeorg = '".$kodePt1."' adn kodekelompok='".$kodekelompok."'";
    }
    $qpt2Cek=$owlPDO->query($Pt2Cek) or die(print " Gagal: ".PDOException::getMessage());
    $qpt2Cek->setFetchMode(PDO::FETCH_ASSOC);

    
    $qPt1Cek=$owlPDO->query($Pt1Cek) or die(print " Gagal: ".PDOException::getMessage());
    $qPt1Cek->setFetchMode(PDO::FETCH_ASSOC);

   $brs=owlBaris($qpt2Cek);
   $brs2=owlBaris($qPt1Cek);

   if($brs==0)
   {
       echo"warning:Kelompok Jurnal ".$kodePt2.",data masih kosong";
       exit();
   }
   elseif($brs!=0)
   {  
      if($brs2 !=0)
      {
        $str="delete from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$kodePt1."'";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
      }

      while ($bar = $qPt1Cek->fetch()) {

        $str="insert into ".$dbname.".keu_5kelompokjurnal (kodeorg,kodekelompok,keterangan,nokounter)
        values('".$kodePt1."','".$bar['kodekelompok']."','".$bar['keterangan']."','".$bar['nokounter']."')";
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
        
      }
      echo 1;
  }
}
?>
