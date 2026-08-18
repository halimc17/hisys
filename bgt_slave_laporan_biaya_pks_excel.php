<?php
require_once('master_validation.php');
require_once('config/connection.php');

$kodeorg=$_GET['kodeorg'];
$thnbudget=$_GET['thnbudget'];
$kelompokbiaya=$_GET['kelompokbiaya'];
#ambil produksi pks
$tbs=0;
$cpo=0;
$pk=0;
$str="select sum(kgolah) as tbs,sum(kgcpo) as cpo,sum(kgkernel) as kernel from ".$dbname.".bgt_produksi_pks_vw 
      where tahunbudget=".$thnbudget." and millcode = '".$kodeorg."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $tbs=$bar->tbs;
    $cpo=$bar->cpo;
    $pk=$bar->kernel;
}
$stream= $_SESSION['lang']['produksipabrik']."  (".$_SESSION['lang']['Ton'].")
<table  border=1>
     <thead>
         <tr>
           <td align=center>".$_SESSION['lang']['tbsdiolah']."</td>
           <td align=center>Palm Product</td>    
           <td align=center>".$_SESSION['lang']['cpo']."</td>
           <td align=center>".$_SESSION['lang']['kernel']."</td>
         </tr>
         </thead>
         <tbody>
         <tr>
           <td align=right>".number_format($tbs,0,".",",")."</td>
           <td align=right>".number_format($cpo+$pk,0,".",",")."</td>    
           <td align=right>".number_format($cpo,0,".",",")."</td>
           <td align=right>".number_format($pk,0,".",",")."</td>
         </tr>     
     </tbody>
     <tfoot>
     </tfoot>
     </table>"; 

echo $str="select a.tahunbudget,a.station,a.kdbudget, sum(a.rupiah) as rupiah,
	  sum(a.rp01) as rp01,sum(a.rp02) as rp02,sum(a.rp03) as rp03,sum(a.rp04) as rp04,sum(a.rp05) as rp05,
	  sum(a.rp06) as rp06,sum(a.rp07) as rp07,sum(a.rp08) as rp08,sum(a.rp09) as rp09,sum(a.rp10) as rp10,
	  sum(a.rp11) as rp11,sum(a.rp12) as rp12,b.namaorganisasi,c.nama from ".$dbname.".bgt_pks_station_vw a left join
      ".$dbname.".organisasi b on a.station=b.kodeorganisasi left join ".$dbname.".bgt_kode c on a.kdbudget=c.kodebudget
      where tahunbudget=".$thnbudget." and a.station like '".$kodeorg."%' and a.noakun like '".$kelompokbiaya."%' group by a.tahunbudget,a.station,a.kdbudget
      ";	  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
$rpperha=0;
$stream.="<br>".strtoupper($_SESSION['lang']['anggaran']." ".$_SESSION['lang']['biaya']." ".$_SESSION['lang']['langsung'])."<br>
          ".$_SESSION['lang']['unit']." : ".$kodeorg." ".$_SESSION['lang']['budgetyear']." : ".$thnbudget."
             <table border=1>
	     <thead>
		 <tr class=rowheader>
                   <td align=center>".$_SESSION['lang']['nourut']."</td>
                   <td align=center>".$_SESSION['lang']['station']."</td>
                   <td align=center>".$_SESSION['lang']['kodeabs']."</td>
                   <td align=center>".$_SESSION['lang']['jumlahrp']."</td>
                   <td align=center>".$_SESSION['lang']['rpperkg']."-CPO</td>   
                   <td align=center>".$_SESSION['lang']['rpperkg']."-TBS</td> 
                   <td align=center>".$_SESSION['lang']['rpperkg']."-PP</td>
                   
                    
                 </tr>
		 </thead>
		 <tbody>"; 
$old='';
$jumlah=0;
$grandtt=0;
$awalan=0;
$mulai=0;
while($bar=$res->fetch())
{
    $no+=1;
    $new=$bar->station;
   
    $grandtt+=$bar->rupiah;
    if($bar->kdbudget=='M')
        $nama_komponen="Material";
    else
        $nama_komponen=$bar->nama;
    
    if($old!='' and $old!=$new)
    {
        #subtotal
        @$jumlahpercpo=$jumlah/($cpo+$pk);
        @$jumlahpertbs=$jumlah/$tbs;
        @$jmlhCpo=$jumlah/$cpo;
    $stream.="<tr>
           <td colspan=3 align=center>".$_SESSION['lang']['total']."</td>
           <td align=right>".number_format($jumlah,0,'.',',')."</td>
           <td align=right>".number_format($jmlhCpo,3,'.',',')."</td> 
           <td align=right>".number_format($jumlahpertbs,3,'.',',')."</td>               
           <td align=right>".number_format($jumlahpercpo,3,'.',',')."</td>
         </tr>";        
        $jumlah=0;
        $awalan=0;
        $jumlah+=$bar->rupiah;
    }
    else
    {
        $jumlah+=$bar->rupiah;
    }

      
       $mulai++;
//    }
    
    @$rupiahpercpo=$bar->rupiah/($cpo+$pk);
    @$rupiahpertbs=$bar->rupiah/$tbs;
    @$rupiahpercpo2=$bar->rupiah/$cpo;
    $stream.="<tr>
           <td>".$no."</td>
           <td>".$bar->namaorganisasi."</td>
           <td>".$nama_komponen."</td>
           <td align=right>".number_format($bar->rupiah,0,'.',',')."</td>
           <td align=right>".number_format($rupiahpercpo2,3,'.',',')."</td> 
           <td align=right>".number_format($rupiahpertbs,3,'.',',')."</td>  
           <td align=right>".number_format($rupiahpercpo,3,'.',',')."</td>
           
           
         </tr>";
   $old=$bar->station;
}
#subtotal terakhir
        @$jumlahpercpo=$jumlah/($cpo+$pk);
        @$jumlahpertbs=$jumlah/$tbs;
        @$jumlahpercpo2=$jumlah/$cpo;
    $stream.="<tr>
           <td colspan=3 align=center>".$_SESSION['lang']['total']."</td>
           <td align=right>".number_format($jumlah,0,'.',',')."</td>
           <td align=right>".number_format($jumlahpercpo2,3,'.',',')."</td>    
           <td align=right>".number_format($jumlahpertbs,3,'.',',')."</td> 
           <td align=right>".number_format($jumlahpercpo,3,'.',',')."</td>           
         </tr>"; 
    @$grandttpercpo=$grandtt/($cpo+$pk);
    @$grandttpertbs=$grandtt/$tbs;
    @$grandttpercpo2=$grandtt/$cpo;
    $stream.="<tr>
           <td colspan=3 align=center>".$_SESSION['lang']['grnd_total']."</td>
           <td align=right>".number_format($grandtt,0,'.',',')."</td>
           <td align=right>".number_format($grandttpercpo2,3,'.',',')."</td> 
           <td align=right>".number_format($grandttpertbs,3,'.',',')."</td>
           <td align=right>".number_format($grandttpercpo,3,'.',',')."</td>
           
         </tr>";     
$stream.="</tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
$qwe=date("YmdHms");
$nop_="Budget_".$kodeorg."_BYLANGSUNG_".$thnbudget."_".$qwe;
if(strlen($stream)>0)
{
     $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
     gzwrite($gztralala, $stream);
     gzclose($gztralala);
     echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
} 
?>