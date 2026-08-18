<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }  
echo"
<link rel=stylesheet type='text/css' href='style/".$gen."'>
";
   
$type=$_GET['type'];
$what=$_GET['what'];
$tahun=$_GET['tahun'];
$kebun=$_GET['kebun'];
$statBlok=$_GET['statBlok'];
$tt=$_GET['tt'];

        $str="select * from ".$dbname.".setup_topografi";
        $opttahun="";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch()){
                $topo[$bar->topografi]=$bar->keterangan;
        }
        if($statBlok=='TBM')
        {
            $statBlok="'TBM','TB'";
        }
        else
        {
            $statBlok="'TM'";
        }
        $str="select * from ".$dbname.".bgt_blok
             where tahunbudget ='".$tahun."' and statusblok in (".$statBlok.")
             and kodeblok like '".$kebun."%' and thntnm ='".$tt."'";
//exit("Error:".$str);
if($type=='pdf'){
    echo "PDF";
}


//        echo $str;
//=================================================
//     <img onclick=\"parent.detailKePDF(event,'bgt_slave_laporan_arealstatement_detail.php?type=pdf&what=".$what."&tahun=".$tahun."&kebun=".$kebun."&tt=".$tt."')\" src=images/pdf.jpg class=resicon title='PDF'>
echo"<fieldset style=float:left>
     <img onclick=\"parent.detailKeExcel(event,'bgt_slave_laporan_arealstatement_detail.php?type=excel&what=".$what."&tahun=".$tahun."&kebun=".$kebun."&tt=".$tt."')\" src=images/excel.jpg class=resicon title='MS.Excel'></fieldset>
     ";
if($what=='A')$stream=$_SESSION['lang']['detail'].' '.$_SESSION['lang']['luas'].' '.$kebun.'<br>'.$_SESSION['lang']['tahuntanam'].' '.$tt;
if($what=='B')$stream=$_SESSION['lang']['detail'].' '.$_SESSION['lang']['populasi'].' '.$kebun.'<br>'.$_SESSION['lang']['tahuntanam'].' '.$tt;
if($_GET['type']=='excel')$stream.="<table class=sortable border=1 cellspacing=1>"; else
    $stream.="<table class=sortable cellpadding=5 border=0 cellspacing=1>";
$stream.="<thead>
        <tr class=rowcontent>
          <th>".substr($_SESSION['lang']['nomor'],0,2)."</th>
          <th>".$_SESSION['lang']['kodeblok']."</th>";
if($what=='A')
          $stream.="<th>".$_SESSION['lang']['luas']." ".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['lalu']."</th>
          <th>".$_SESSION['lang']['Mutasi1']."</th>
          <th>".$_SESSION['lang']['luas']." ".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['ini']."</th>";
if($what=='B')
          $stream.="<th>".$_SESSION['lang']['pokok']." ".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['lalu']."</th>
          <th>".$_SESSION['lang']['Mutasi1']."</th>
          <th>".$_SESSION['lang']['pokok']." ".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['ini']."</th>";
          $stream.="<th>".$_SESSION['lang']['status']." ".$_SESSION['lang']['blok']."</th>
          <th>".$_SESSION['lang']['topografi']."</th>
          <th>".$_SESSION['lang']['lama']."/".$_SESSION['lang']['baru']."</th>
        </tr>
      </thead>
      <tbody>";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    
    $no=0;
    $totallalu=0;
    $totalmutasi=0;
    $totalini=0;
    while($bar=$res->fetch()){
        $no+=1;
        if($what=='A'){
        $totallalu+=$bar->hathnlalu;
        $totalmutasi+=$bar->hamutasi;
        $totalini+=$bar->hathnini;
        }
        if($what=='B'){
        $totallalu+=$bar->pokokthnlalu;
        $totalmutasi+=$bar->pokokmutasi;
        $totalini+=$bar->pokokthnini;
        }
    $stream.="<tr class=rowcontent>
           <td>".$no."</td>
           <td>".getNamaOrg($bar->kodeblok)."</td>";             
        if($what=='A'){
           $stream.="<td align=right>".number_format($bar->hathnlalu,2)."</td>    
           <td align=right>".number_format($bar->hamutasi,2)."</td>    
           <td align=right>".number_format($bar->hathnini,2)."</td>";
        }
        if($what=='B'){
           $stream.="<td align=right>".number_format($bar->pokokthnlalu)."</td>    
           <td align=right>".number_format($bar->pokokmutasi)."</td>    
           <td align=right>".number_format($bar->pokokthnini)."</td>";
        }
           $stream.="<td>".$bar->statusblok."</td>
           <td>".$topo[$bar->topografi]."</td>
           <td>".$bar->sumber."</td>
         </tr>";
    $tdebet+=$debet;
    $tkredit+=$kredit;    
    } 
      $stream.="<tr class=rowcontent>
           <td colspan=2>".$_SESSION['lang']['total']."</td>";
        if($what=='A'){
           $stream.="<td align=right>".number_format($totallalu,2)."</td>
           <td align=right>".number_format($totalmutasi,2)."</td>
           <td align=right>".number_format($totalini,2)."</td>";
        }
        if($what=='B'){
           $stream.="<td align=right>".number_format($totallalu)."</td>
           <td align=right>".number_format($totalmutasi)."</td>
           <td align=right>".number_format($totalini)."</td>";
        }
           $stream.="<td colspan=3>&nbsp;</td>
         </tr>";  
   $stream.="</tbody><tfoot></tfoot></table>";
   if($_GET['type']=='excel')
   {
$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
$nop_="Detail_arealstatement_".$_GET['what']."_".$_GET['kebun']."_".$_GET['tt'];
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
        //closedir($handle);
        }       
   }
   else
   {
       echo $stream;
   }    
       
?>