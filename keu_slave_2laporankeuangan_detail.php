<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');

$pt=$_POST['pt'];
$unit=$_POST['unit']; //kebun
$periode=$_POST['periode'];
$nourut=$_POST['nourut'];
$tipe=$_POST['tipe'];

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
$periodelalu=$tahun.'-'.$bulanlalu; // periode lalu
if($bulan==1)$periodelalu=$tahunlalu.'-12';

$desemberlalu=$tahunlalu.'-12'; // periode desember tahun lalu

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namapt=strtoupper($bar->namaorganisasi);
}

$kodelaporan='LK - LABA RUGI';

$periodesaldo=str_replace("-", "", $periode);

//periode db
$periodeCUR=str_replace("-", "", $periode);
$periodePRF=str_replace("-", "", $periodelalu);
//$periodeLSD=str_replace("-", "", $desemberlalu);

//kolom db
$kolomCUR='debet'.$bulan.'-kredit'.$bulan;
$kolomPRF='debet'.$bulanlalu.'-kredit'.$bulanlalu;
$kolomLSD='awal'.$bulan.'+debet'.$bulan.'-kredit'.$bulan;

//title table
$t=mktime(0,0,0,substr($periodeCUR,4,2),15,substr($periodeCUR,0,4));
$captionCUR=date('M-Y',$t);
$t=mktime(0,0,0,substr($periodePRF,4,2),15,substr($periodePRF,0,4));
$captionPRF=date('M-Y',$t);
//$t=mktime(0,0,0,substr($periodeLSD,4,2),15,substr($periodeLSD,0,4));
//$captionLSD=date('M-Y',$t);

//involving units
if($unit=='')$where=" in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
else $where=" ='".$unit."'";

$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'
    order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
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
    foreach($qwe as $rty){
        //if((number_format($rty)!=0)){
        if($rty!=0){
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
    $st12="select noakun,sum(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan from ".$dbname.".keu_saldobulanan 
           where  ".$whrakun." and periode like'".$tahun."%' and kodeorg ".$where." group by noakun,periode  order by periode";
    $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res12->setFetchMode(PDO::FETCH_OBJ);
    while($ba12=$res12->fetch())
    {
        if(!isset($dzArr[$bar->nourut][$ba12->bulan])) $dzArr[$bar->nourut][$ba12->bulan]=0;
        if(!isset($dzArr2[$ba12->noakun][$ba12->bulan])) $dzArr2[$ba12->noakun][$ba12->bulan]=0;
        $dzArr[$bar->nourut][$ba12->bulan]+=$ba12->jumlah;
        $dzArr2[$ba12->noakun][$ba12->bulan]+=$ba12->jumlah;
        if($bulan>=$ba12->bulan){
            if(!isset($dzArr[$bar->nourut]['sd'])) $dzArr[$bar->nourut]['sd']=0;
            if(!isset($dzArr2[$ba12->noakun]['sd'])) $dzArr2[$ba12->noakun]['sd']=0;
            $dzArr[$bar->nourut]['sd']+=$ba12->jumlah+$ba12->awal;
            $dzArr2[$ba12->noakun]['sd']+=$ba12->jumlah+$ba12->awal;
        }
    }
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
            break;
            
            case'213302':

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


if($tipe=='Detail'){

        //report format
        $str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' and nourut = '".$nourut."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
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
            $st12="select noakun,(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan
                from ".$dbname.".keu_saldobulanan where ".$whrakun." and periode like'".$tahun."%' and kodeorg ".$where." group by noakun,periode order by periode";
            $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
            $res12->setFetchMode(PDO::FETCH_OBJ);

            while($ba12=$res12->fetch())
            {
                $daftar[$ba12->noakun]=$ba12->noakun;
            }              

        }
        if(isset($daftar) and !is_null($daftar)) sort($daftar);

        $st12="select noakun, namaakun, namaakun1
            from ".$dbname.".keu_5akun where level=5";
        $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
        $res12->setFetchMode(PDO::FETCH_OBJ);
        while($ba12=$res12->fetch())
        {
            if($_SESSION['language']=='ID'){
                $akun[$ba12->noakun]=$ba12->namaakun;}
            else{
                $akun[$ba12->noakun]=$ba12->namaakun1;
            }
        }      

        $stream="<table class=sortable border=0 cellspacing=0>";
        if(!empty($daftar))foreach($daftar as $akunnya){
            @$dataPER=($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu])/$dzArr2[$akunnya][$bulanlalu]*100;
            $stream.="
            <tr class=rowcontent>
                <td style='width:10px'></td>
                <td style='width:10px'></td>
                <td style='width:510px'>".$akun[$akunnya]."</td>
                ";
                for ($i = $bulan; $i >= $bulanlalu; $i--) {
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                    $stream.="<td style='width:120px' align=right>".number_format($dzArr2[$akunnya][$ii],2)."</td>";
                }            
                $stream.="<td style='width:120px' align=right>".number_format($dzArr2[$akunnya]['sd'],2)."</td>
                    <td style='width:120px' align=right>".number_format($dzArr2[$akunnya][$bulan]-$dzArr2[$akunnya][$bulanlalu],2)."</td>    
                <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
            </tr>";              
        }
        $stream.="</table>";            
    

}

//////////////////////////////////////////////////////////////////////////HEADER

echo $stream;
?>