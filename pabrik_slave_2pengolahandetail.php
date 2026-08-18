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
$nopengolahan=checkPostGet('nopengolahan','');
$tanggal=$_GET['tanggal'];
$kodeorg=$_GET['kodeorg'];
$periode_tahun=$_GET['periode_tahun'];
$periode_bulan=$_GET['periode_bulan'];
$periode = $periode_tahun.'-'.addZero($periode_bulan,2);
setIt($_GET['type'],'');
//=================================================
echo"<fieldset><legend>Detail</legend>
     <img onclick=\"detailExcel(event,'pabrik_slave_2pengolahandetail.php?type=excel&tanggal=".$tanggal."&kodeorg=".$kodeorg."&periode_tahun=".$periode_tahun."&periode_bulan=".$periode_bulan."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
     "; 
if(!isset($_GET['type']) or $_GET['type']!='excel')$stream="<table class=sortable border=0 cellspacing=1 width=100%>"; else
$stream="<table class=sortable border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <td>No</td>
          <td>".$_SESSION['lang']['tanggal']."</td>
          <td>".$_SESSION['lang']['nopengolahan']."</td>
          <td>Shift</td>
          <td>".$_SESSION['lang']['jammulai']."</td>
          <td>".$_SESSION['lang']['jamselesai']."</td>
          <td>".$_SESSION['lang']['jamdinasbruto']."</td>
          <td>".$_SESSION['lang']['jamstagnasi']."</td>
          <td>".$_SESSION['lang']['jumlahlori']."</td>
          <td>".$_SESSION['lang']['tbsdiolah']."</td>";
if(!isset($_GET['type']) or $_GET['type']!='excel')$stream.="<td>Browse</td>";
        $stream.="</tr>
      </thead>
      <tbody>";
        $no=0;
    $tdebet=0;
    $tkredit=0;
    $str="select * from ".$dbname.".pabrik_pengolahan
              where tanggal = '".$tanggal."' and kodeorg='".$kodeorg."'";   	  
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $no+=1;
	    $stream.="<tr class=rowcontent>
           <td align=right>".$no."</td>
           <td>".tanggalnormal($bar->tanggal)."</td>    
           <td align=right>".$bar->nopengolahan."</td>               
           <td align=right>".$bar->shift."</td>               
           <td align=right>".substr($bar->jammulai,0,5)."</td>               
           <td align=right>".substr($bar->jamselesai,0,5)."</td>               
           <td align=right>".$bar->jamdinasbruto."</td>               
           <td align=right>".$bar->jamstagnasi."</td>               
           <td align=right>".number_format($bar->jumlahlori)."</td>               
           <td align=right>".number_format($bar->tbsdiolah)."</td>";
   		  if($_GET['type']!='excel')$stream.="
           <td><img onclick=\"parent.browsemesin(".$bar->nopengolahan.",'".$tanggal."','".$kodeorg."','".$periode_tahun."','".$periode_bulan."',event);\" title=".$_SESSION['lang']['mesin']." class=\"resicon\" src=\"images/icons/joystick.png\">
		      </td>";               
			  /* <img onclick=\"parent.browsebarang(".$bar->nopengolahan.",'".$tanggal."','".$kodeorg."','".$periode_tahun."','".$periode_bulan."',event);\" title=".$_SESSION['lang']['material']." class=\"resicon\" src=\"images/icons/box.png\">*/
         $stream.="</tr>";
    } 
   $stream.="</tbody></table></fieldset>";
   if($_GET['type']=='excel')
   {
$nop_="Detail_pengolahan_".$kodeorg."_".$tanggal;
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