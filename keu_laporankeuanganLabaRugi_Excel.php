<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=$_GET['pt'];
$unit=$_GET['gudang'];
$periode=$_GET['periode'];

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];

if($bulan=='01' or $bulan=='1'){
  $bulanlalu=12;
 }else{ 
  $bulanlalu=$bulan-1;
} 

if($bulanlalu<10)$bulanlalu='0'.$bulanlalu; // bulan lalu dia digit
if($bulanlalu=='00')$bulanlalu='12';
$periodelalu=$tahun.'-'.$bulanlalu; // periode lalu
if($bulan==1)$periodelalu=$tahunlalu.'-12';

$desemberlalu=$tahunlalu.'-12'; // periode desember tahun lalu

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $namapt=strtoupper($bar->namaorganisasi);
}

$kodelaporan='LK - LABA RUGI';

//title table
for ($i = $bulan; $i >= 1; $i--) {
    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
    $t=mktime(0,0,0,$i,15,$tahun);
    $kolom[$ii]=date('M-Y',$t);
}
$t=mktime(0,0,0,$bulan,15,$tahun);
$kolom['sd']='to '.date('M-Y',$t);

//involving units
if($unit=='')$where=" in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
else $where=" ='".$unit."'";

//report format
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'
    order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;}
    else{
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    
    // dari total
    $qwe=explode(",",$bar->noakundisplay);
    if(!empty($qwe))foreach($qwe as $rty){
        if((intval($rty)!=0)){
            $emaknya[$rty]=$bar->nourut;
            $adaemaknya[$rty]=$rty;
        }
    }
    $whrakun="noakun between '".$bar->noakundari."'  and '".$bar->noakunsampai."'";
    switch ($bar->nourut) {
        case '211102':
          $whrakun=" noakun in (".$bar->noakundisplay.")";
        break;
    }

    $semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';
    $st12="select noakun,sum(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan
        from ".$dbname.".keu_saldobulanan where ".$whrakun." and periode like'".$tahun."%' and kodeorg ".$where." group by noakun,periode order by periode";
    $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res12->setFetchMode(PDO::FETCH_OBJ);
    while($ba12=$res12->fetch()){
        $daftar[$ba12->noakun]=$ba12->noakun;
        $emaknya[$ba12->noakun]=$bar->nourut;
        if(!isset($dzArr[$bar->nourut][$ba12->bulan])) $dzArr[$bar->nourut][$ba12->bulan]=0;
        if(!isset($dzArr2[$ba12->noakun][$ba12->bulan])) $dzArr2[$ba12->noakun][$ba12->bulan]=0;
        $dzArr[$bar->nourut][$ba12->bulan]+=$ba12->jumlah;
        $dzArr2[$ba12->noakun][$ba12->bulan]+=$ba12->jumlah;
        if(!isset($dzArr[$bar->nourut]['sd'])) $dzArr[$bar->nourut]['sd']=0;
        if(!isset($dzArr2[$ba12->noakun]['sd'])) $dzArr2[$ba12->noakun]['sd']=0;
        if($bulan>=$ba12->bulan){
            $dzArr[$bar->nourut]['sd']+=$ba12->jumlah+$ba12->awal;
            $dzArr2[$ba12->noakun]['sd']+=$ba12->jumlah+$ba12->awal;
        }
    }  
    if(!empty($daftar))sort($daftar);
    switch ($bar->nourut){
            case'100009':
            case'213102':
            case'213301':
            case'211109':
            case'213501':
            case'213103':
            case'215999':
            case'216999':
                $dt=explode(",",$bar->noakundisplay);
                $coundAja=count($dt);
                for ($i = 1; $i <= $bulan; $i++) {
                    $totalDt=0;
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                    for($ard=0;$ard<$coundAja;$ard++){
                        $totalDt+=$dzArr[$dt[$ard]][$ii];
                    }
                    $dzArr[$bar->nourut][$ii]=$totalDt;
                    $dzArr[$bar->nourut]['sd']+=$totalDt;
                }
                $excepUrut[$bar->nourut]=$bar->nourut;//array untuk menghindari mensubtotal kembali di bawah
            break;
            
            case'213302':
                $excepUrut[$bar->nourut]=$bar->nourut;
                $dt=explode(",",$bar->noakundisplay);
                $coundAja=count($dt);
                for ($i = 1; $i <= $bulan; $i++) {
                    $totalDt=0;
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                    $totalDt+=$dzArr[$dt[0]][$ii];
                    for($ard=1;$ard<$coundAja;$ard++){
                        $totalDt-=$dzArr[$dt[$ard]][$ii];
                    }
                    $dzArr[$bar->nourut][$ii]=$totalDt;
                    $dzArr[$bar->nourut]['sd']+=$totalDt;
                }
            break;
            
    } 
}

$stream=$kodelaporan." ".$pt." ".$unit." ".$periode;
$stream.="<table class=sortable border=1 cellspacing=0>
          <thead>
          <tr class=rowheader><td align=center colspan=3 rowspan=2>Description</td>";
          for ($i = $bulan; $i >= 1; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td align=center rowspan=2>".$kolom[$ii]."</td>";    
           }
$stream.="<td align=center rowspan=2>".$kolom['sd']."</td><td align=center colspan=2>Increase/Decrease</td></tr>
           <tr class=rowheader><td align=center>Rupiah</td><td align=center>%</td></tr></thead><tbody>";
$st12="select noakun, namaakun, namaakun1 from ".$dbname.".keu_5akun where level=5";
    $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res12->setFetchMode(PDO::FETCH_OBJ);
    while($ba12=$res12->fetch()){
    if($_SESSION['language']=='ID'){
        $akun[$ba12->noakun]=$ba12->namaakun;}
    else{
        $akun[$ba12->noakun]=$ba12->namaakun1;
    }
}  
$subtotal['sd']=0;
if(!empty($dzArr))foreach($dzArr as $data){ // level 0
    if($data['tipe']=='Header')
    {
        $totallagi=0;        
    }
    if($data['tipe']=='Detail'){
        // subtotal
        for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
            if(!isset($subtotal[$ii])) $subtotal[$ii]=0;
            $subtotal[$ii] += isset($data[$ii])? $data[$ii]: 0;
        }
        $subtotal['sd'] += isset($data['sd'])? $data['sd']: 0;
        $totallagi=0;
    }
    if($data['tipe']=='Total'){
        if($totallagi==1){
            // if(!empty($adaemaknya))foreach($adaemaknya as $ada){
            //     for ($i = 1; $i <= $bulan; $i++) {
            //         if($i<10)$ii='0'.$i; else $ii=$i;
            //         if(!isset($dzArr[$data['nourut']][$ii])) $dzArr[$data['nourut']][$ii]=0;
            //         if($emaknya[$ada]==$data['nourut'])$dzArr[$data['nourut']][$ii]+=isset($dzArr[$ada][$ii])? $dzArr[$ada][$ii]: 0;
            //     }
            //     if(!isset($dzArr[$data['nourut']]['sd'])) $dzArr[$data['nourut']]['sd']=0;
            //     if($emaknya[$ada]==$data['nourut'])$dzArr[$data['nourut']]['sd']+=isset($dzArr[$ada]['sd'])? $dzArr[$ada]['sd']: 0;
            // }
        }else{
            if(!empty($excepUrut[$data['nourut']])){
                continue;
            }
            /*for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                if(!isset($dzArr[$data['nourut']][$ii])) $dzArr[$data['nourut']][$ii]=0;
                $dzArr[$data['nourut']][$ii] += isset($subtotal[$ii])? $subtotal[$ii]: 0;
                $subtotal[$ii]=0;            
            } 
            if(!isset($dzArr[$data['nourut']]['sd'])) $dzArr[$data['nourut']]['sd']=0;
            $dzArr[$data['nourut']]['sd'] += isset($subtotal['sd'])? $subtotal['sd']: 0;*/
        }
        $subtotal['sd']=0;
        
        $totallagi=1;        
    }

}


//ambil format mesinlaporan
if(!empty($dzArr))foreach($dzArr as $data){
    if($data['tipe']=='Header')
    {
        $stream.="<tr class=rowcontent>
            <td colspan=".($bulan+6)."><b>".$data['keterangan']."</b></td>
        </tr>"; 
    }
    else
    if($data['tipe']=='Total'){
        @$subtotalPER=($dzArr[$data['nourut']][$bulan]-$dzArr[$data['nourut']][$bulanlalu])/$dzArr[$data['nourut']][$bulanlalu]*100;
        $stream.="<tr class=rowcontent>
            <td></td>
            <td></td>
            <td><b>".$data['keterangan']."</b></td>
            </td>";
            for ($i = $bulan; $i >= 1; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td align=right><b>".number_format($dzArr[$data['nourut']][$ii],2)."</b></td>";                
            }
            $stream.="<td align=right><b>".number_format($dzArr[$data['nourut']]['sd'],2)."</b>
                <td align=right><b>".number_format($dzArr[$data['nourut']][$bulan]-$dzArr[$data['nourut']][$bulanlalu],2)."</b></td>    
            <td align=right><b>".number_format($subtotalPER,2)."</b></td>    
        </tr>
        ";
    }
    else
    if($data['tipe']=='Detail'){
        @$dataPER=($data[$bulan]-$data[$bulanlalu])/$data[$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent>
            <td></td>
            <td colspan=2>".$data['keterangan']."</td>
            ";
            for ($i = $bulan; $i >= 1; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td align=right>".number_format(isset($data[$ii])? $data[$ii]: 0,2)."</td>";
            }
            if(!isset($data['sd'])) $data['sd']=0;
            if(!isset($data[$bulan])) $data[$bulan]=0;
            if(!isset($data[$bulanlalu])) $data[$bulanlalu]=0;
            $stream.="<td align=right>".number_format($data['sd'],2)."</td>
                <td align=right>".number_format($data[$bulan]-$data[$bulanlalu],2)."</td>    
            <td align=right>".number_format($dataPER,2)."</td>    
        </tr>";          
//        $stream.="<tr><td colspan=".($bulan+6)."><div style=\"display:none;\" id=".$data['nourut'].">";
        if(!empty($daftar))foreach($daftar as $akunnya){
            
            if($emaknya[$akunnya]==$data['nourut']){
            @$dataPER=($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu])/$dzArr2[$akunnya][$bulanlalu]*100;
            $stream.="
            <tr class=rowcontent>
                <td></td>
                <td></td>
                <td>".$akun[$akunnya]."</td>
                ";
                for ($i = $bulan; $i >= 1; $i--) {
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                    $stream.="<td align=right>".number_format(isset($dzArr2[$akunnya][$ii])? $dzArr2[$akunnya][$ii]: 0,2)."</td>";
                }
                if(!isset($dzArr2[$akunnya][$bulan])) $dzArr2[$akunnya][$bulan]=0;
                if(!isset($dzArr2[$akunnya][$bulanlalu])) $dzArr2[$akunnya][$bulanlalu]=0;
                $stream.="<td align=right>".number_format(isset($dzArr2[$akunnya]['sd'])? $dzArr2[$akunnya]['sd']: 0,2)."</td><td align=right>".number_format($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu],2)."</td>    
                <td align=right>".number_format($dataPER,2)."</td>    
            </tr>";              
                
            }
            
        }
//        $stream.="</div></td></tr>";

//        $stream.="</table></div></td></tr>";
    }
}
$stream.= "</tbody></tfoot></tfoot></table>";   

$nop_="Laporan Keuangan-".$pt."-".$unit."-".$periode;
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

?>