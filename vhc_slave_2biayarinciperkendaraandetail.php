<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<script language=javascript1.2 src="js/vhc_2biayatotalperkendaraan.js"></script>
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

   
$kodevhc=$_GET['kodevhc'];
$tanggalmulai=$_GET['tanggalmulai'];
$tanggalsampai=$_GET['tanggalsampai'];
$unit=$_GET['unit'];

$periode=checkPostGet('periode','');
$periode1=checkPostGet('periode1','');
$noakunawal=$_GET['noakunawal'];
$noakunakhir=$_GET['noakunakhir'];

$type=checkPostGet('type','');

//=================================================

$stream="";
if($type!='excel')
{
    echo"<fieldset><legend>Vehicle Cost  : ".$kodevhc." ".$_SESSION['lang']['tanggal']." : ".$tanggalmulai." s.d ".$tanggalsampai."</legend>
     <img onclick=\"detailExcel(event)\" src=images/excel.jpg class=resicon title='MS.Excel'>
     <input type=hidden id=kodevhc value='".$kodevhc."' />
    <input type=hidden id=tanggalmulai value='".$tanggalmulai."' />
    <input type=hidden id=tanggalsampai value='".$tanggalsampai."' />
    <input type=hidden id=unit value='".$unit."' />
    <input type=hidden id=noakunawal value='".$noakunawal."' />
    <input type=hidden id=noakunakhir value='".$noakunakhir."' />";
    $stream.="<table class=sortable border=0 cellspacing=1>"; 
    
}
else
{
 echo"<fieldset><legend>Print Excel</legend>
    
     </fieldset>";
    $stream.="<table class=sortable border=1 cellspacing=1>";
}
$stream.="
      <thead>
        <tr class=rowcontent>
          <td bgcolor=#DEDEDE align=center>No.</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>    
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodekegiatan']."</td>    
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namakegiatan']."</td>    
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['satuan']."</td>      
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['keterangan']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodeblok']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
          ";
        $stream.="</tr>
      </thead>
      <tbody>";
	$str="select c.satuan,a.tanggal, a.noakun, a.keterangan, a.debet as jumlah, a.kodevhc,a.kodeblok,a.noreferensi,b.namaakun,c.kodekegiatan,c.namakegiatan 
              from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".keu_5akun b
              on a.noakun=b.noakun
			  left join ".$dbname.".setup_kegiatan c
			  on a.kodekegiatan=c.kodekegiatan
              where kodevhc = '".$kodevhc."'
              and tanggal>='".$tanggalmulai."' and tanggal<='".$tanggalsampai."' 
              and (a.noakun between '".$noakunawal."' and '".$noakunakhir."')
              and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL)";
	
	
    $no=0;
    $total=0;
    
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $no+=1;
        if($bar->jumlah>0){
              $stream.="<tr class=rowcontent>
              <td align=center>".$no."</td>
              <td>".$bar->tanggal."</td>
              <td align=right>".$bar->noakun."</td>
              <td>".$bar->namaakun."</td>    
              <td>".$bar->kodekegiatan."</td>
              <td>".$bar->namakegiatan."</td>
                  <td>".$bar->satuan."</td>
              <td>".$bar->keterangan."</td>
              <td align=right>".number_format($bar->jumlah)."</td>
              <td>".$bar->kodeblok."</td>
              <td>".$bar->noreferensi."</td>";
             $stream.="</tr>";
         $total+=$bar->jumlah;
        }          
    } 
    $stream.="<tr class=rowtitle>
              <td colspan=7 align=right>TOTAL :</td><td></td>
              <td align=right>".number_format($total)."</td>
              <td></td><td></td>";
         $stream.="</tr>";

   $stream.="</tbody></table></fieldset>";
   if($type=='excel')
   {
$nop_="Detail_BiayaPerKendaraan_".$kodevhc."_";
        if(strlen($stream)>0)
        {
        if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != "index.html") {
                    @unlink('tempExcel/'.$file);
                }
            }	
           closedir($handle);
        }
         $handle=fopen("tempExcel/".$nop_.".xls",'w');
         if(!fwrite($handle,$stream))
         {
          echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
           exit;
         }
         else
         {
          echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls';
                </script>";
         }
        fclose($handle);
        }       
   }
   else
   {
       echo $stream;
   }    
       
?>