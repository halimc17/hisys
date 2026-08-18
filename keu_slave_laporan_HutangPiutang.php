<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

?>
	<link rel=stylesheet type=text/css href=style/generic.css>	
<?
$tanggalmulai=$_GET['mulai'];
$tanggalsampai=$_GET['sampai'];
$noakun=$_GET['noakun'];
$kodesupplier=$_GET['kodesupplier'];
$kodeorg=$_GET['kodeorg'];

$tipe=$_GET['tipe'];

$dat=array();

if($tanggalmulai==''){ echo "warning: silakan mengisi tanggal"; exit; }
if($tanggalsampai==''){ echo "warning: silakan mengisi tanggal"; exit; }
if($noakun==''){ echo "warning: silakan memilih no akun"; exit; }

$whereawal = " and a.tanggal >= '2020-12-31'";

#ambil nama karyawan
$str="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$kodesupplier."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $supplier=$bar->namakaryawan;
}

$str="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$kodesupplier."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $supplier=$bar->namasupplier;
}
if(substr($noakun,0,3)=='211'){
  #ambil saldo awal supplier
  $str="select sum(a.debet-a.kredit) as sawal,a.noakun from ".$dbname.".keu_jurnaldt_vw a
        where left(a.tanggal,7)<'".substr($tanggalmulai,0,7)."' ".$whereawal." and a.noakun = '".$noakun."' and (a.kodesupplier='".$kodesupplier."' or a.nik='".$kodesupplier."')
        and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."')";
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  while($bar=$res->fetch())
  {
        $sawal[$kodesupplier]=$bar->sawal;
  } 
}else{
  #ambil saldo awal customer
  $str="select sum(a.debet-a.kredit) as sawal,a.noakun from ".$dbname.".keu_jurnaldt_vw a
        where left(a.tanggal,7)<'".substr($tanggalmulai,0,7)."'  ".$whereawal." and a.noakun = '".$noakun."' and (a.kodecustomer='".$kodesupplier."' or a.nik='".$kodesupplier."')
        and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
      $sawal[$kodesupplier]=$bar->sawal;
  }
}
$fild="a.kodecustomer";
if(substr($noakun,0,3)=='211'){
  $fild="a.kodesupplier";
}


#ambil  transaksi dalam periode supplier
$str="select a.debet  as debet, a.kredit as kredit,a.nojurnal,a.noreferensi,a.tanggal,a.noakun,a.keterangan,".$fild."  from ".$dbname.".keu_jurnaldt_vw a
      where left(a.tanggal,7) between '".substr($tanggalmulai,0,7)."' and '".substr($tanggalsampai,0,7)."' 
      and a.noakun = '".$noakun."' and ".$fild."='".$kodesupplier."'
	  and a.nik = ''
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') order by tanggal";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $dat[$bar->nojurnal]=$bar->tanggal;
    $ket[$bar->nojurnal]=$bar->nojurnal;
    $ref[$bar->nojurnal]=$bar->noreferensi;
    $debet[$bar->nojurnal]+=$bar->debet;
    $kredit[$bar->nojurnal]+=$bar->kredit;
}

#ambil saldo transaksi  karyawan
$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.nojurnal,a.noreferensi,a.tanggal,a.keterangan,a.noakun,a.nik from ".$dbname.".keu_jurnaldt_vw a
      where left(a.tanggal,7) between '".substr($tanggalmulai,0,7)."' and '".substr($tanggalsampai,0,7)."'  
      and a.noakun = '".$noakun."' and a.nik='".$kodesupplier."'
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') order by tanggal";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $dat[$bar->nojurnal]=$bar->tanggal;
    $ket[$bar->nojurnal]=$bar->nojurnal;
    $ref[$bar->nojurnal]=$bar->noreferensi;
    $debet[$bar->nojurnal]+=$bar->debet;
    $kredit[$bar->nojurnal]+=$bar->kredit;
}

if($tipe=='excel')
{
    $border="border=1";
}
else
{
    $border="border=0";
}

echo"<fieldset style=float:left>
     <img onclick=\"parent.lihatDetailHutang('".$kodesupplier."','".$noakun."','".$tanggalmulai."','".$tanggalsampai."','".$kodeorg."','excel',event)\" src=images/excel.jpg class=resicon title='MS.Excel'>
   </fieldset>";

            $stream="<table $border class=sortable cellspacing=1 cellpadding=5  width=100%>
             <thead>
                    <tr>
                          <td align=center width=50>".$_SESSION['lang']['nourut']."</td>
                          <td align=center>".$_SESSION['lang']['organisasi']."</td>
                          <td align=center>".$_SESSION['lang']['tanggal']."</td>    
                          <td align=center>".$_SESSION['lang']['notransaksi']."</td>
                          <td align=center>".$_SESSION['lang']['noreferensi']."</td>     
                          <td align=center>".$_SESSION['lang']['noakun']."</td>
                          <td align=center>Karyawan/Supplier</td>
                          <td align=center>".$_SESSION['lang']['saldoawal']."</td>                             
                          <td align=center>".$_SESSION['lang']['debet']."</td>
                          <td align=center>".$_SESSION['lang']['kredit']."</td>
                          <td align=center>".$_SESSION['lang']['saldoakhir']."</td>                               
                        </tr>  
                 </thead>
                 <tbody id=container>"; 
//=================================================
            
            
         
            
$no=0;
if(count($dat)<1)
{
        echo"<tr class=rowcontent><td colspan=9>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}
else
{
    $tsa=$sawal[$kodesupplier];
    $tsa2=$tsa;
    $totDebet=0;
    $totKredit=0;
    foreach($dat as $notran =>$val){
            $no+=1;
            if($no==1){
              $stream.="<tr class=rowcontent >
                      <td colspan=5>&nbsp;</td>
                      <td align=center>".$noakun."</td>
                      <td>".$supplier."</td>
                       <td align=right width=100>".number_format($tsa,2)."</td>   
                      <td align=right width=100>&nbsp;</td>
                      <td align=right width=100>&nbsp;</td>
                      <td align=right width=100>&nbsp;</td>
                     </tr>"; 
            }
            if(count($dat[$notran])!=0){
                $stream.="<tr class=rowcontent >
                      <td align=center width=20>".$no."</td>
                      <td align=center>".$kodeorg."</td>   
                      <td align=center>".tanggalnormal($val)."</td>                   
                      <td align=center>".$notran."</td>
                       <td align=center>".$ref[$notran]."</td>     
                      <td align=center>".$noakun."</td>
                      <td>".$supplier."</td>
                       <td align=right width=100>&nbsp;</td>   
                      <td align=right width=100>".number_format($debet[$notran],2)."</td>
                      <td align=right width=100>".number_format($kredit[$notran],2)."</td>
                      <td align=right width=100>".number_format($tsa2+$debet[$notran]-$kredit[$notran],2)."</td>
                     </tr>"; 
                     $totDebet+=$debet[$notran];
                     $totKredit+=$kredit[$notran];
                     $tsa2=$tsa2+$debet[$notran]-$kredit[$notran];   
            }
    }	
    $stream.="<tr class=rowcontent >
                <td colspan=7>&nbsp;</td>
                <td align=right width=100>".number_format($tsa,2)."</td>   
                <td align=right width=100>".number_format($totDebet,2)."</td>
                <td align=right width=100>".number_format($totKredit,2)."</td>
                <td align=right width=100>".number_format($tsa+$totDebet-$totKredit,2)."</td>
               </tr>"; 
}



if($tipe=='excel')
{
    echo $stream;
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop_="Detail_jurnal_".$kodeorg._.$noakun._.$tanggalmulai._.$tanggalsampai;
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