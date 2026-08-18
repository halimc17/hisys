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
$tanggal=checkPostGet('tanggal','');
$kodeorg=checkPostGet('kodeorg','');
$periode_tahun=$_GET['periode_tahun'];
$periode_bulan=$_GET['periode_bulan'];
$periode = $periode_tahun.'-'.addZero($periode_bulan,2);
setIt($_GET['type'],'');
//=================================================
echo"<fieldset><legend>Print Excel</legend>
     <img onclick=\"detailExcel(event,'pabrik_slave_2pengolahanmesin.php?type=excel&nopengolahan=".$nopengolahan."&kodeorg=".$kodeorg."&periode_tahun=".$periode_tahun."&periode_bulan=".$periode_bulan."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
     "; 
if($_GET['type']!='excel')$stream="<table class=sortable border=0 cellspacing=1 width=100%>"; else
$stream="<table class=sortable border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <td align=center>".$_SESSION['lang']['nourut']."</td>
          <td align=center>".$_SESSION['lang']['notransaksi']."</td>
         <td align=center>".$_SESSION['lang']['station']."</td>
          <td align=center>".$_SESSION['lang']['mesin']."</td>
          <td align=center>".$_SESSION['lang']['status']."</td>
          <td align=center>".$_SESSION['lang']['tipe']."</td>
          <td align=center>".$_SESSION['lang']['shift']."</td>
          <td align=center>".$_SESSION['lang']['jammulai']."</td>
          <td align=center>".$_SESSION['lang']['jamselesai']."</td>
          <td align=center>".$_SESSION['lang']['jamstagnasi']."<br>(Jam)</td>
          <td align=center>".$_SESSION['lang']['kegiatan']."</td>
          <td align=center>".$_SESSION['lang']['statusketuntasan']."</td>
        </tr>
      </thead>
      <tbody>";

	 $strJ="select * from ".$dbname.".organisasi";
  $resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
  $resJ->setFetchMode(PDO::FETCH_OBJ);
	while($barJ=$resJ->fetch())
	{
		$org[$barJ->kodeorganisasi]=$barJ->namaorganisasi;
	}
   // $str="select * from ".$dbname.".pabrik_pengolahanmesin
              // where nopengolahan = '".$nopengolahan."%'"; 
	$str="select * from ".$dbname.".pabrik_rawatmesinht
              where pabrik = '".$kodeorg."' and tanggal='".$tanggal."'"; 		  
			  
			  // echo $tanggal._.$kodeorg;
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $no=0;
    $tdebet=0;
    $tkredit=0;
    while($bar= $res->fetch())
    {
        $no+=1;	
    
    $stream.="<tr class=rowcontent>
           <td align=center>".$no."</td>
		    <td align=right>".$bar->notransaksi."</td>   
           <td>".$org[$bar->statasiun]."</td>               
           <td>".$org[$bar->mesin]."</td>    
           <td>".$bar->downstatus."</td>               
           <td>".$bar->tipeperbaikan."</td>               
           <td>".$bar->shift."</td>               
           <td align=right>".tanggalnormal($bar->jammulai)."  ".substr($bar->jammulai,10,6)."</td>               
           <td align=right>".tanggalnormal($bar->jamselesai)."  ".substr($bar->jamselesai,10,6)."</td>                         
           <td align=right>".$bar->jumlahjamperbaikan."</td>               
           <td>".$bar->kegiatan."</td>               
           <td>".$bar->statusketuntasan."</td>               
         </tr>";
    } 
   $stream.="</tbody></table></fieldset>";
   if($_GET['type']=='excel')
   {
$nop_="Detail_pengolahan_(Mesin)_".$kodeorg."_".$nopengolahan;
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