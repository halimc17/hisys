<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/fpdf.php');

    $gudang=$_POST['gudang'];
    $unit=$_POST['unit'];

    if($gudang){
    $str="select kodeorg, periode from ".$dbname.".setup_periodeakuntansi 
          where kodeorg='".$gudang."' order by periode desc";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
            $hasil.="<option value='".$bar->periode."'>".$bar->periode."</option>";
    }
    echo $hasil;
    }

    if($unit){
    $str="select induk from ".$dbname.".organisasi
          where kodeorganisasi ='".$unit."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
            $induk=$bar->induk;
            $hasil.="<option value='".$bar->periode."'>".$bar->periode."</option>";
    }
    $str="select periode from ".$dbname.".setup_periodeakuntansi
          where kodeorg ='".$unit."' order by periode desc";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
            $hasil.="<option value='".$bar->periode."'>".$bar->periode."</option>";
    }
    echo $hasil;
    }
?>