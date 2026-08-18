<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$pt=$_GET['pt'];
$unit=$_GET['unit'];
$periode=$_GET['periode'];

$qwe=explode('-',$periode);
$tahun=$periode;
// $bulan=$qwe[1];

// 
$kodelaporan='LABARUGI V2';

$st12="select noakun, namaakun, namaakun1  from ".$dbname.".keu_5akun where level=5";
$res1=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res1->fetch())
{
    $namaakun[$ba12->noakun]=$ba12->namaakun;
}    


//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $namapt=strtoupper($bar->namaorganisasi);
}

// ambil urut
$str="select nourut, keterangandisplay, tipe, noakundisplay from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'
    order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $listurut[$bar->nourut]=$bar->nourut;

    $namaurut[$bar->nourut]=$bar->keterangandisplay;
    $tipeurut[$bar->nourut]=$bar->tipe;
    $anakurut[$bar->nourut]=$bar->noakundisplay;
}

// ambil akun
$str="select nourut, noakun from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."'
    order by nourut,noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $keurut[$bar->noakun]=$bar->nourut;
    $listanak[$bar->nourut][$bar->noakun]=$bar->noakun;
}

$str="select awal01, noakun from ".$dbname.".keu_saldobulanan where left(periode,4)='".$tahun."01' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') and noakun in ('1150201','1150203')";
$res=fetchdata($str);
foreach($res as $bar){
    $perx=$tahun.'-01';
        $data['2101'][$perx]+=$bar['awal01'];
    // $data['2101']['sd']+=$bar['awal01'];
}

// ambil transaksi
$str="select tanggal, noakun, jumlah, kodeorg from ".$dbname.".keu_jurnaldt_vw where left(tanggal,4)='".$tahun."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') order by tanggal asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $kali=1;
    if(substr($bar['noakun'],0,1)=='5'){
        $kali=(-1);
    }

    $perx=substr($bar['tanggal'],0,7);
    $masukkeurut=$keurut[$bar['noakun']];
    // $unit=$bar['tanggal'];

    $data[$masukkeurut][$perx]+=($kali*$bar['jumlah']);
    $data[$bar['noakun']][$perx]+=($kali*$bar['jumlah']);

    for ($x = 1; $x <= 12; $x++) {
        $xx = sprintf("%02d", $x);
        $perxxxx=$tahun.'-'.$xx;
        if(($perx<=$perxxxx)and($masukkeurut!='')){
            $datasd[$masukkeurut][$perxxxx]+=($kali*$bar['jumlah']);
            $datasd[$bar['noakun']][$perxxxx]+=($kali*$bar['jumlah']);
        }

        // if($x>1){
        //  $y=$x-1;
        //  $yy = sprintf("%02d", $y);
        //  $peryyyy=$tahun.'-'.$yy;
            if($masukkeurut=='2103'){ // closing stock ambil 
                $data['2103'][$perxxxx]=$datasd['2103'][$perxxxx];
                $data[$bar['noakun']][$perxxxx]=$datasd[$bar['noakun']][$perxxxx];
            }
        // }
    } 
    
    // if($masukkeurut=='2103'){
    //  $arrtemp['2101'][$perx]=$data['2103']['sd'];
    //  $arrtemp[$bar['noakun']][$perx]=$data[$bar['noakun']]['sd'];
    // }
    
    // $data['2101'][$periode]=abs($arrtemp['2101'][periodelalu($periode)]);
    // // $data['2101'][$periode]=($data['2103'][periodelalu($periode)]*-1);
    // $data['2103'][$periode]=$data['2103']['sd'];

    // opening n closing stock
    // if(substr($bar['noakun'],0,5)=='11502'){
    //  $data['2103'][$perx]+=$bar['jumlah'];
    //  $data['2103']['sd']+=$bar['jumlah'];
    // }
}

// if(substr($periode,5,2)!='01'){
//  $data['6610101'][$periode]=abs($arrtemp['6610301'][periodelalu($periode)]);
//  $data['6610102'][$periode]=abs($arrtemp['6610302'][periodelalu($periode)]);
// }

// susun total
foreach($listurut as $urut){
    // if($urut=='2103'){ // Closing Stock
    //  $data[$urut][$periode]=(-1)*$data[$urut][$periode];
    //  $data[$urut]['sd']=(-1)*$data[$urut]['sd'];
    // }
    for ($x = 1; $x <= 12; $x++) {
        $xx = sprintf("%02d", $x);
        $perxxxx=$tahun.'-'.$xx;

        if($urut=='3011' || $urut=='4001' || $urut=='4002'){ // sabinus: ini tanda nya harus nya minus pak
            $data[$urut][$perxxxx]=(-1)*$data[$urut][$perxxxx];
            $datasd[$urut][$perxxxx]=(-1)*$datasd[$urut][$perxxxx];
        }
        $qwe=explode(',', $anakurut[$urut]);
        foreach($qwe as $anak){
            if($anak!=''){
                $amin=substr($anak,0,1);
                if($amin=='-'){ // -1234
                    $anak2=substr($anak,1,4);
                    $data[$urut][$perxxxx]-=$data[$anak2][$perxxxx];
                    $datasd[$urut][$perxxxx]-=$data[$anak2][$perxxxx];
                }else{ // 1234
                    $data[$urut][$perxxxx]+=$data[$anak][$perxxxx];
                    $datasd[$urut][$perxxxx]+=$datasd[$anak][$perxxxx];
                }
            }
        }

        if($x==1){
            $datapers[$urut][$perxxxx]=100;
        }else{
            $y=$x-1;
            $yy = sprintf("%02d", $y);
            $peryyyy=$tahun.'-'.$yy;

            if($urut=='2101'){ // opening stock ambil 
                $data['2101'][$perxxxx]=abs($data['2103'][$peryyyy]);
                $data['6610101'][$perxxxx]=abs($data['6610301'][$peryyyy]);
                $data['6610102'][$perxxxx]=abs($data['6610302'][$peryyyy]);
            }

            $datapers[$urut][$perxxxx]=fixnan(($data[$urut][$perxxxx]-$data[$urut][$peryyyy])/$data[$urut][$peryyyy]*100);

            foreach($listanak[$urut] as $anak){
            $datapers[$anak][$perxxxx]=fixnan(($data[$anak][$perxxxx]-$data[$anak][$peryyyy])/$data[$anak][$peryyyy]*100);
            }    
        }


    }    
}

if($unit==''){
    $unitx=$pt;
}else{
    $unitx=$unit;
}

$nmorg  = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unitx."'");
$stream ="Laporan Keuangan - Laba Rugi<br>";
$stream.="".$unitx." - ".$nmorg[$unitx]."<br>";
$stream.="Periode ".$periode."<br><br>";
$stream.="<table class=sortable border=1 cellspacing=0>
    <thead>
        <tr class=rowheader>
            <td style='width:520px' align=center colspan=3 rowspan=2>Description</td>
            ";
    for ($x = 1; $x <= 12; $x++) {
        $xx = sprintf("%02d", $x);
        $perxxxx=$tahun.'-'.$xx;
            $stream.="<td style='width:120px' align=center colspan=2>".$perxxxx." </td>";    
    }            
$stream.="</tr>
        <tr class=rowheader>";
    for ($x = 1; $x <= 12; $x++) {
        $xx = sprintf("%02d", $x);
        $perxxxx=$tahun.'-'.$xx;
        $stream.="<td align=center>Rp.</td>";    
        $stream.="<td align=center>%</td>";
    }            
    $stream.="</tr></thead><tbody>";

if(!empty($listurut))foreach($listurut as $urut){ // level 0
    if($tipeurut[$urut]=='Header'){
        $stream.="<tr class=rowcontent title='".$namaurut[$urut]."' >
            <td colspan=27><b>".$namaurut[$urut]." </b></td>
        </tr>"; 
        $stream.="<tr><td colspan=27><div style=\"display:none;\" id=".$urut.">";
        $stream.="</div></td></tr>";
    }else if($tipeurut[$urut]=='Detail'){
        // tampilin anak2nya
    //     foreach($listanak[$urut] as $anak){
    //         $stream.="<tr>
    //             <td style='width:10px'></td>
    //             <td style='width:10px'>".$anak."</td>
    //             <td style='width:500px'>".$namaakun[$anak]."</td>
    //             ";
    // for ($x = 1; $x <= 12; $x++) {
    //     $xx = sprintf("%02d", $x);
    //     $perxxxx=$tahun.'-'.$xx;

    //         $stream.="<td style='width:120px' align=right>".number_format($data[$anak][$perxxxx])."</td>";
    //         $stream.="<td style='width:120px' align=right>".number_format($datapers[$anak][$perxxxx],2)."</td>";
    // }        
    //         $stream.="</tr>";            
    //     }

        $stream.="<tr class=rowcontent title='Click untuk melihat detail' style=cursor:pointer; onclick=\"getLaporanKeuanganDetailv2('".$urut."','".$tipeurut[$urut]."')\">
            <td colspan=2 style='width:10px'></td>
            <td style='width:510px'>".$namaurut[$urut]." </td>
            ";
    for ($x = 1; $x <= 12; $x++) {
        $xx = sprintf("%02d", $x);
        $perxxxx=$tahun.'-'.$xx;

        $stream.="<td style='width:120px' align=right>".number_format($data[$urut][$perxxxx])."</td>";
        $stream.="<td style='width:120px' align=right>".number_format($datapers[$urut][$perxxxx],2)."</td>";
    }    
        $stream.="</tr>";

        $stream.="<tr><td colspan=27><div style=\"display:none;\" id=".$urut.">";
        $stream.="</div></td></tr>";
    }else if($tipeurut[$urut]=='Total'){
        $stream.="<tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td style='width:500px'><b>".$namaurut[$urut]."</b></td>
            ";
    for ($x = 1; $x <= 12; $x++) {
        $xx = sprintf("%02d", $x);
        $perxxxx=$tahun.'-'.$xx;

        $stream.="<td style='width:120px' align=right><b>".number_format($data[$urut][$perxxxx])."</b></td>";                
        $stream.="<td style='width:120px' align=right><b>".number_format($datapers[$urut][$perxxxx],2)."</b></td>";
    }    
        $stream.="</tr>
        <tr class=rowcontent><td colspan=5></td></tr>
        ";
    }
}

$stream.= "</tbody></tfoot></tfoot></table>";

$nop_="Laporan Keuangan - Laba Rugi ".$pt."-".$unit."-".$periode;
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