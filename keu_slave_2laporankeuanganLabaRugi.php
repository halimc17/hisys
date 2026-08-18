<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');

$pt=$_POST['pt'];
$unit=$_POST['unit']; //kebun
$periode=$_POST['periode'];

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

$dzArr=array();
$dzArr2=array();
$nilaiawal=array();
$hargaawal=array();
$fisikawal=array();

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

$noakunjualcpo='5110103';
$noakunjualker='5110104';

//report format
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
    // $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
    // $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    
    // dari total
    $qwe=explode(",",$bar->noakundisplay);
    if(!empty($qwe))foreach($qwe as $rty){
         if($rty!=0){    
            //if((number_format($rty)!=0)){
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
           where  ".$whrakun." and periode like'".$tahun."%' and kodeorg ".$where." group by periode  order by periode";
    $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res12->setFetchMode(PDO::FETCH_OBJ);
    while($ba12=$res12->fetch())
    {
        if(!isset($dzArr[$bar->nourut][$ba12->bulan])) $dzArr[$bar->nourut][$ba12->bulan]=0;
         
        $dzArr[$bar->nourut][$ba12->bulan]+=$ba12->jumlah;
        //$dzArr2[$ba12->noakun][$ba12->bulan]=$ba12->jumlah;
        if(!isset($dzArr[$bar->nourut]['sd'])) $dzArr[$bar->nourut]['sd']=0;
        if(!isset($dzArr2[$ba12->noakun]['sd'])) $dzArr2[$ba12->noakun]['sd']=0;
        if($bulan>=$ba12->bulan){
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

$stream="<table class=sortable border=0 cellspacing=0>
    <thead>
        <tr class=rowheader>
            <td style='width:520px' align=center colspan=3 rowspan=2>Description</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td style='width:120px' align=center rowspan=2>".$kolom[$ii]."</td>";    
            }
            $stream.="<td style='width:120px' align=center rowspan=2>".$kolom['sd']."</td>
                <td align=center colspan=2>Increase/Decrease</td>    
        </tr>
        <tr class=rowheader>
            <td style='width:120px' align=center>Rupiah</td>
            <td style='width:50px' align=center>%</td>
        </tr>
    </thead><tbody>";

//ambil format mesinlaporan
if(!empty($dzArr))foreach($dzArr as $data){ // level 0
    if($data['tipe']=='Header')
    {
        $stream.="<tr class=rowcontent title='Click untuk melihat detail' style=cursor:pointer; onclick=\"getLaporanKeuanganDetail('".$data['nourut']."','".$data['tipe']."')\">
            <td colspan=".(2+6)."><b>".$data['keterangan']."</b></td>
        </tr>"; 
        $stream.="<tr><td colspan=8><div style=\"display:none;\" id=".$data['nourut'].">";

        $stream.="</div></td></tr>";
    }
    else
    if($data['tipe']=='Detail'){
        @$dataPER=($data[$bulan]-$data[$bulanlalu])/$data[$bulanlalu]*100;
        $stream.="
        <tr class=rowcontent title='Click untuk melihat detail' style=cursor:pointer; onclick=\"getLaporanKeuanganDetail('".$data['nourut']."','".$data['tipe']."')\">
            <td style='width:10px'></td>
            <td colspan=2 style='width:510px'>".$data['keterangan']."</td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td style='width:120px' align=right>".number_format(isset($data[$ii])? $data[$ii]: 0,2)."</td>";
            }
            if(!isset($data['sd'])) $data['sd']=0;
            if(!isset($data[$bulan])) $data[$bulan]=0;
            if(!isset($data[$bulanlalu])) $data[$bulanlalu]=0;
            $stream.="<td style='width:120px' align=right>".number_format($data['sd'],2)."</td>
                <td style='width:120px' align=right>".number_format($data[$bulan]-$data[$bulanlalu],2)."</td>    
            <td style='width:50px' align=right>".number_format($dataPER,2)."</td>    
        </tr>";          
        $stream.="<tr><td colspan=".(2+6)."><div style=\"display:none;\" id=".$data['nourut'].">";

        $stream.="</div></td></tr>";
    }
    else
    if($data['tipe']=='Total'){
        $addRo="";
        @$subtotalPER=($dzArr[$data['nourut']][$bulan]-$dzArr[$data['nourut']][$bulanlalu])/$dzArr[$data['nourut']][$bulanlalu]*100;
        if($data['nourut']=='212999'){
            $addRo=" title='Total Nilai HPP Penjualan TBS' style='cursor:pointer' onclick=getLaporanKeuanganDetail('".$data['nourut']."','Detail')";
        }
        $stream.="<tr class=rowcontent ".$addRo.">
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'><b>".$data['keterangan']."</b></td>
            ";
            for ($i = $bulan; $i >= $bulanlalu; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td style='width:120px' align=right><b>".number_format($dzArr[$data['nourut']][$ii],2)."</b></td>";                
            }
            $stream.="<td style='width:120px' align=right><b>".number_format($dzArr[$data['nourut']]['sd'],2)."</b></td>
                <td style='width:120px' align=right><b>".number_format($dzArr[$data['nourut']][$bulan]-$dzArr[$data['nourut']][$bulanlalu],2)."</b></td>    
            <td style='width:50px' align=right><b>".number_format($subtotalPER,2)."</b></td>    
        </tr>
        <tr class=rowcontent><td colspan=".(2+8)."></td></tr>
        ";
        if($data['nourut']=='212999'){
            $stream.="<tr><td colspan=".(2+6)."><div style=\"display:none;\" id=".$data['nourut'].">";
            $stream.="</div></td></tr>";
        }
    }        
}

$stream.= "</tbody></tfoot></tfoot></table>";
echo $stream;
?>