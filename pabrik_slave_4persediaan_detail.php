<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<script language=javascript1.2 src="js/pabrik_2pengolahan.js"></script>

<?
    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }  

  echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>"; 
$kodeorg=$_GET['kodeorg'];
$tanggal=$_GET['tanggal'];
$barang=$_GET['barang'];
//=================================================

if(isset($_GET['type'])?$_GET['type']:''!='excel')$stream="<fieldset><table class=sortable border=0 cellspacing=1 width=100%>"; else
$stream="<table class=sortable border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <td>No</td>
          <td>".$_SESSION['lang']['tanggal']."</td>
          <td>".$_SESSION['lang']['NoKontrak']."</td>
          <td>".$_SESSION['lang']['nodo']."</td>
          <td>".$_SESSION['lang']['komoditi']."</td>
          <td>".$_SESSION['lang']['kuantitas']."</td>
          <td>".$_SESSION['lang']['nokendaraan']."</td>
          <td>".$_SESSION['lang']['Pembeli']."</td>";
        $stream.="</tr>
      </thead>
      <tbody>";
    $str="select nokontrak, koderekanan from ".$dbname.".pmn_kontrakjual";   
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $kontrak[$bar->nokontrak]=$bar->koderekanan;
    }
    $str="select kodecustomer,namacustomer from ".$dbname.".pmn_4customer";   
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $kustom[$bar->kodecustomer]=$bar->namacustomer;
    }
    $str="select tanggal, nokontrak, nodo, kodebarang, beratbersih, nokendaraan from ".$dbname.".pabrik_timbangan
              where millcode = '".$kodeorg."' and tanggal like '".$tanggal."%' and kodebarang = '".$barang."'";   
    // echo $str;
	$no=0;
    $total=0;
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $no+=1;    
        if(($bar->kodebarang)=='40000001')$barang='CPO';
        if(($bar->kodebarang)=='40000002')$barang='Kernel';
        $total+=$bar->beratbersih;
        $stream.="<tr class=rowcontent>
           <td align=right>".$no."</td>
           <td align=left>".$bar->tanggal."</td>    
           <td align=left>".$bar->nokontrak."</td>               
           <td align=left>".$bar->nodo."</td>               
           <td align=left>".$barang."</td>               
           <td align=right>".number_format($bar->beratbersih,0)."</td>               
           <td align=left>".$bar->nokendaraan."</td>               
           <td align=left>".@$kustom[$kontrak[$bar->nokontrak]]."</td>";               
         $stream.="</tr>";
    } 
        $stream.="<tr class=rowcontent>
           <td align=center colspan=5><b>Total</b></td>               
           <td align=right><b>".number_format($total,0)."</b></td>               
           <td align=center colspan=2></td>";               
         $stream.="</tr>";
   $stream.="</tbody></table></fieldset>";

       echo $stream;
       
?>