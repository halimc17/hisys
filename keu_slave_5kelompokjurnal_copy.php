<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

if($_POST['proses']=="copyData")
{
    $kodePt1=$_POST['kodePt1'];
    $kodeUnit1=$_POST['kodeUnit1'];
    $periode1=$_POST['periode1'];
    $kodePt2=$_POST['kodePt2'];
    $kodeUnit2=$_POST['kodeUnit2'];
    $periode2=$_POST['periode2'];
    $kodekelompok=$_POST['kodekelompok'];
    $tipe=$_POST['tipe'];

    if($kodePt1=='' || $kodeUnit1=='' || $periode1==''){
        echo"warning:Kode Organisasi, Unit, dan Periode tujuan wajib dipilih";
        exit();
    }
    if($kodePt2=='' || $kodeUnit2=='' || $periode2==''){
        echo"warning:Kode Organisasi, Unit, dan Periode 'Duplikat dari' wajib dipilih";
        exit();
    }
    if($kodePt1==$kodePt2 && $kodeUnit1==$kodeUnit2 && $periode1==$periode2){
        echo"warning:Organisasi, Unit, dan Periode tujuan tidak boleh sama persis dengan sumber";
        exit();
    }
    if($tipe!='false' && $kodekelompok==''){
        echo"warning:Kode Kelompok wajib dipilih";
        exit();
    }

    $whrKelompok = ($tipe!='false') ? " and kodekelompok='".$kodekelompok."'" : "";

    $Pt2Cek="select kodekelompok,keterangan,nokounter from ".$dbname.".keu_5kelompokjurnal where
      kodeorg = '".$kodePt2."' and kodeunit='".$kodeUnit2."' and periode='".$periode2."'".$whrKelompok;
    $Pt1Cek="select kodekelompok,keterangan,nokounter from ".$dbname.".keu_5kelompokjurnal where
      kodeorg = '".$kodePt1."' and kodeunit='".$kodeUnit1."' and periode='".$periode1."'".$whrKelompok;

    $qpt2Cek=$owlPDO->query($Pt2Cek) or die(print " Gagal: ".PDOException::getMessage());
    $qpt2Cek->setFetchMode(PDO::FETCH_ASSOC);

    $qPt1Cek=$owlPDO->query($Pt1Cek) or die(print " Gagal: ".PDOException::getMessage());
    $qPt1Cek->setFetchMode(PDO::FETCH_ASSOC);

    $brs=owlBaris($qpt2Cek);
    $brs2=owlBaris($qPt1Cek);

    if($brs==0)
    {
        echo"warning:Kelompok Jurnal sumber (".$kodePt2." - ".$kodeUnit2." - ".$periode2.") masih kosong";
        exit();
    }

    if($brs2 !=0)
    {
        $str="delete from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$kodePt1."' and kodeunit='".$kodeUnit1."' and periode='".$periode1."'".$whrKelompok;
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    while ($bar = $qpt2Cek->fetch()) {

        $str="insert into ".$dbname.".keu_5kelompokjurnal (kodeorg,kodeunit,kodekelompok,periode,keterangan,nokounter)
        values('".$kodePt1."','".$kodeUnit1."','".$bar['kodekelompok']."','".$periode1."','".$bar['keterangan']."','".$bar['nokounter']."')";
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    }
    echo 1;
}
?>
