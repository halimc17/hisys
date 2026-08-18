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

$nopengolahan=$_GET['nopengolahan'];
$tanggal=checkPostGet('tanggal','');
$kodeorg=$_GET['kodeorg'];
$periode_tahun=$_GET['periode_tahun'];
$periode_bulan=$_GET['periode_bulan'];
$periode = $periode_tahun.'-'.addZero($periode_bulan,2);
setIt($_GET['type'],'');
//=================================================
echo"<fieldset><legend>Print Excel</legend>
     <img onclick=\"detailExcel(event,'pabrik_slave_2pengolahanbarang.php?type=excel&nopengolahan=".$nopengolahan."&kodeorg=".$kodeorg."&periode_tahun=".$periode_tahun."&periode_bulan=".$periode_bulan."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
     "; 
if($_GET['type']!='excel')$stream="<table class=sortable border=0 cellspacing=1 width=100%>"; else
$stream="<table class=sortable border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <td align=center>No</td>
          <td align=center>Station</td>
          <td align=center>".$_SESSION['lang']['mesin']."</td>
          <td align=center>".$_SESSION['lang']['namabarang']."</td>
          <td align=center>".$_SESSION['lang']['jumlah']."</td>
          <td align=center>".$_SESSION['lang']['satuan']."</td>
          <td align=center>".$_SESSION['lang']['hargasatuan']."</td>
          <td align=center>".$_SESSION['lang']['total']."</td>
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
	 $strJ="select * from ".$dbname.".log_5saldobulanan";
    $resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
    $resJ->setFetchMode(PDO::FETCH_OBJ);
	while($barJ=$resJ->fetch())
	{
		$harga[$barJ->kodebarang]=$barJ->hargarata;
	}
	 $strJ="select * from ".$dbname.".log_5masterbarang";
    $resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
    $resJ->setFetchMode(PDO::FETCH_OBJ);
  while($barJ=$resJ->fetch())
	{
		$namabar[$barJ->kodebarang]=$barJ->namabarang;
		$satuan[$barJ->kodebarang]=$barJ->satuan;
	}
    $str="select * from ".$dbname.".pabrik_pengolahan_barang
              where nopengolahan = '".$nopengolahan."'";
    $no=0;
    $total=0;
    $totalall=0;
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
  while($bar=$res->fetch())
    {
        $no+=1;
		$total=($bar->jumlah)*($harga[$bar->kodebarang]);
    	$totalall+=$total;
    $stream.="<tr class=rowcontent>
           <td align=center>".$no."</td>
           <td>".$org[$bar->kodeorg]."</td>               
           <td>".$org[$bar->tahuntanam]."</td>               
           <td>".$namabar[$bar->kodebarang]."</td>               
           <td align=right>".$bar->jumlah."</td>               
           <td>".$satuan[$bar->kodebarang]."</td>               
           <td align=right>".number_format($harga[$bar->kodebarang],0)."</td>               
           <td align=right>".number_format($total,0)."</td>               
         </tr>";
    }
    $stream.="<tr class=rowheader>
           <td colspan=7>TOTAL</td>
           <td align=right>".number_format($totalall,0)."</td>               
         </tr>";
   $stream.="</tbody></table></fieldset>";
   if($_GET['type']=='excel')
   {
$nop_="Detail_pengolahan_(Barang)_".$kodeorg."_".$nopengolahan;
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