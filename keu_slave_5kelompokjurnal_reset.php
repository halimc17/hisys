<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

if($_POST['proses']=="resetData")
{
    $b=0;
    $kodePt=$_POST['kodePt'];
    $sCek="select tutupbuku from ".$dbname.".setup_periodeakuntansi where 
        kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodePt."')";
    $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
    $qCek->setFetchMode(PDO::FETCH_ASSOC);
   $brs=owlBaris($qCek);
   for($a=0;$a<$brs;$a++)
   {
                $rBrs=$qCek->fetch();
                if(empty($rBrs[$a]))
                {
                        $b+=1;
                }
   }
   if($b!=0)
   {
       echo"warning:Organisasi di Sub ".$kodePt.",belum tutup Buku ";
       exit();
   }
   elseif($b==0)
   {
       $sUp="update ".$dbname.".keu_5kelompokjurnal set nokounter=0 where kodeorg='".$kodePt."'";
       try{$owlPDO->exec($sUp); }
       catch (PDOException $e) {
           print " Gagal  !: " . $e->getMessage() . "\n"; 
           exit(); 
       }
           echo"1";
   }
}
?>
