<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<script language=javascript1.2 src="js/sdm_2rekapabsen.js"></script>

<?
$theme=$_SESSION['theme'];
if($theme=='skyblue' || $theme==''){
  $gen='generic.css';
}else if($theme=='red'){
  $gen='genericRed.css';  
}else{
  $gen='genericGray.css';  
} 
echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>
";  

$karyawanid=$_GET['karyawanid'];
$tanggal=$_GET['tanggal'];

$str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'"; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $namakaryawan[$bar->karyawanid]=$bar->namakaryawan;
}

$strz="select notransaksi, tanggal, karyawanid,(upahpremi-rupiahpenalty) as upahpremi from ".$dbname.".kebun_prestasi_vw
     where tanggal like '".$tanggal."%' and karyawanid = '".$karyawanid."'
     order by notransaksi";   
$resz=$owlPDO->query($strz) or die(print " Gagal: ".PDOException::getMessage());
$resz->setFetchMode(PDO::FETCH_OBJ);
while($barz=$resz->fetch()){
    $notran['BKM:'.$barz->notransaksi].='BKM:'.$barz->notransaksi;
    $premi['BKM:'.$barz->notransaksi]=$barz->upahpremi;
}
//echo $strz.'<br>';

//ambil data di perawatan
$strx="select notransaksi,karyawanid,tanggal,(insentif) as upahpremi from ".$dbname.".kebun_kehadiran_vw
     where tanggal like '".$tanggal."%' and karyawanid = '".$karyawanid."'
     order by notransaksi";   
$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
$resx->setFetchMode(PDO::FETCH_OBJ);
while($barx=$resx->fetch()){
    $notran['BKM:'.$barx->notransaksi]='BKM:'.$barx->notransaksi;
    $premi['BKM:'.$barx->notransaksi]=$barx->upahpremi;
}
//echo $strx.'<br>';

//ambil data kemandoran
$stry="select karyawanid,tanggal,(premiinput) as upahpremi from ".$dbname.".kebun_premikemandoran 
     where tanggal like '".$tanggal."%' and karyawanid = '".$karyawanid."'
     order by tanggal";   
$resy=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
$resy->setFetchMode(PDO::FETCH_OBJ);
while($bary=$resy->fetch()){
    $notran['PREMI KEMANDORAN:'.$bary->tanggal]='PREMI KEMANDORAN:'.$bary->tanggal;
    $premi['PREMI KEMANDORAN:'.$bary->tanggal]=$bary->upahpremi;
}

//premi traksi
$strv="select notransaksi,idkaryawan as karyawanid,tanggal,(premi-penalty) as upahpremi from ".$dbname.".vhc_runhk 
     where tanggal like '".$tanggal."%' and idkaryawan = '".$karyawanid."'
     order by notransaksi";  
$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
$resv->setFetchMode(PDO::FETCH_OBJ);
while($barv=$resv->fetch()){
    $notran['TRAKSI:'.$barv->notransaksi]='TRAKSI:'.$barv->notransaksi;
    $premi['TRAKSI:'.$barv->notransaksi]=$barv->upahpremi;
}

//echo $strv;
//
//echo "<pre>";
//print_r($notran);
//print_r($premi);
//echo "</pre>";

//=================================================
//echo"<fieldset><legend>Print Excel</legend>
//     <img onclick=\"detailExcel(event,'pabrik_slave_2pengolahandetail.php?type=excel&tanggal=".$tanggal."&kodeorg=".$kodeorg."&periode_tahun=".$periode_tahun."&periode_bulan=".$periode_bulan."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
//     </fieldset>"; 
if($_GET['type']!='excel')$stream="<table class=sortable border=0 cellspacing=1>"; //else
//$stream="<table class=sortable border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <td>Karyawan</td>
          <td>No. Transaksi</td>
          <td>Tanggal</td>
          <td>Premi</td>";
//		  if($_GET['type']!='excel')$stream.="<td>Browse</td>";
        $stream.="</tr>
      </thead>
      <tbody>";
        if(empty($notran)){
            $stream.="<tr class=rowcontent>";
            $stream.="<td colspan=4>Abensce</td>";
            $stream.="</tr>";
        }else{
            foreach($notran as $kyu){
                $stream.="<tr class=rowcontent>";
                $stream.="<td align=left>".$namakaryawan[$karyawanid]."</td>";
                $stream.="<td align=left>".$kyu."</td>";
                $stream.="<td align=center>".$tanggal."</td>";
                $stream.="<td align=right>".number_format($premi[$kyu])."</td>";
                $stream.="</tr>";
            }        
        }

   $stream.="</tbody></table>";
//   if($_GET['type']=='excel')
//   {
//$nop_="Detail_pengolahan_".$kodeorg."_".$tanggal;
//        if(strlen($stream)>0)
//        {
//        if ($handle = opendir('tempExcel')) {
//            while (false !== ($file = readdir($handle))) {
//                if ($file != "." && $file != ".." && $file != "index.html") {
//                    @unlink('tempExcel/'.$file);
//                }
//            }	
//           closedir($handle);
//        }
//         $handle=fopen("tempExcel/".$nop_.".xls",'w');
//         if(!fwrite($handle,$stream))
//         {
//          echo "<script language=javascript1.2>
//                parent.window.alert('Can't convert to excel format');
//                </script>";
//           exit;
//         }
//         else
//         {
//          echo "<script language=javascript1.2>
//                window.location='tempExcel/".$nop_.".xls';
//                </script>";
//         }
//        fclose($handle);
//        }       
//   }
//   else
   {
       echo $stream;
   }    
       
?>