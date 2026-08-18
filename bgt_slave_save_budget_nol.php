<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kodeorg=$_POST['kodeorg'];
$kodebudget=$_POST['kodebudget'];
$tahunbudget=$_POST['tahunbudget'];
if($kodebudget=='KAPITAL')
{
    $str="select kunci,hargatotal as rupiah,jumlah from ".$dbname.".bgt_kapital
          where kodeunit like '".$kodeorg."%' and tahunbudget='".$tahunbudget."'";
}
else
{
if($kodebudget!='')
    $where=" where kodeorg like '".$kodeorg."%' and tahunbudget='".$tahunbudget."' and kodebudget='".$kodebudget."'";
else
    $where=" where kodeorg like '".$kodeorg."%' and tahunbudget='".$tahunbudget."'";

$str="select kunci,rupiah,jumlah from ".$dbname.".bgt_budget ".$where;
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
     $rupiah=$bar->rupiah;
    $fisik=$bar->jumlah;
    if($kodebudget=='KAPITAL')
    {
        $dcek="select distinct sum(k01+k02+k03+k04+k05+k06+k07+k08+k09+k10+k11+k12) as total from ".$dbname.".bgt_kapital where kunci='".$bar->kunci."'";
        $qdcek=$owlPDO->query($dcek) or die(print " Gagal: ".PDOException::getMessage());
        $qdcek->setFetchMode(PDO::FETCH_ASSOC);
        $rcek=$qdcek->fetch();
        if($rcek['total']==0)
        {
             $strins="update ".$dbname.".bgt_kapital set
                      k01=".@($rupiah/12).",k02=".@($rupiah/12).",k03=".@($rupiah/12).",k04=".@($rupiah/12).",k05=".@($rupiah/12).",k06=".@($rupiah/12).",
                      k07=".@($rupiah/12).",k08=".@($rupiah/12).",k09=".@($rupiah/12).",k10=".@($rupiah/12).",k11=".@($rupiah/12).",k12=".@($rupiah/12).",
                      updateby='".$_SESSION['standard']['userid']."'
                      where kunci='".$bar->kunci."'";
            try{$owlPDO->exec($strins); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        }
    }
    else
    {
    $strdel="select distinct kunci from ".$dbname.".bgt_distribusi where kunci=".$bar->kunci;
    $res5=$owlPDO->query($strdel) or die(print " Gagal: ".PDOException::getMessage());
    $res5->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res5);
    $row=$numrows;
    if($row>0)
    {
        continue;
    }
    else
    {      
            $strins="insert into ".$dbname.".bgt_distribusi (kunci, rp01, fis01, rp02, fis02, rp03, fis03, rp04, fis04,
                  rp05, fis05, rp06, fis06, rp07, fis07, rp08, fis08, rp09, fis09, rp10, fis10, rp11, fis11, rp12,
                  fis12, updateby)
                  values(".$bar->kunci.",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".@($rupiah/12).",".@($fisik/12).",
                      ".$_SESSION['standard']['userid']."
                  )";
            try{$owlPDO->exec($strins); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        }
    }
}
?>