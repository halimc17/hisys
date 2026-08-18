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
//////////////////////////////////////////////////////////////////////////////// LABA RUGI BEGIN
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

//_____________________________________________________________ nilai awal tahun
$st12="select sum(kuantitas) as cpo, sum(kernelquantity) as ker
    from ".$dbname.".pabrik_masukkeluartangki where tanggal = '".$tahun."-01-01' and kodeorg ".$where."";
$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res12->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res12->fetch()){
    $fisikawal['CPO']['01']=$ba12->cpo;
    $fisikawal['KER']['01']=$ba12->ker;
}

//$hargaawal['CPO']['01']=8233;
//$hargaawal['KER']['01']=5060;

// ambil persediaan awal dari 1150201 dan 1150202
$st12="select noakun,awal01 as awal, substr(periode,5,2) as bulan
    from ".$dbname.".keu_saldobulanan where noakun in('1150201','1150202') and periode like'".$tahun."%' and kodeorg ".$where." order by periode";
$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res12->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res12->fetch()){
    if($ba12->noakun=='1150201')$nilaiawal['CPO']['01']+=$ba12->awal;
    if($ba12->noakun=='1150202')$nilaiawal['KER']['01']+=$ba12->awal;
}  

@$hargaawal['CPO']['01']=$nilaiawal['CPO']['01']/$fisikawal['CPO']['01'];
@$hargaawal['KER']['01']=$nilaiawal['KER']['01']/$fisikawal['KER']['01'];

//_____________________________________________________ambil produksi CPO dan PK
$st12="select sum(oer) as cpo, sum(oerpk) as ker, substr(tanggal,6,2) as bulan
    from ".$dbname.".pabrik_produksi where tanggal like '".$tahun."%' and kodeorg ".$where." group by substr(tanggal,6,2) order by tanggal";
$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res12->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res12->fetch()){
    $fisikprod['CPO'][$ba12->bulan]=$ba12->cpo;
    $fisikprod['KER'][$ba12->bulan]=$ba12->ker;
    if($bulan>=$ba12->bulan){
        $fisikprod['CPO']['sd']+=$ba12->cpo;
        $fisikprod['KER']['sd']+=$ba12->ker;
    }
}

//_______________________________________________________ ambil harga CPO dan PK
$st12="select a.kodebarang, a.nokontrak, a.nodo, (b.hargasatuan*a.beratbersih) as nilai, (a.beratbersih) as fisik, b.catatanlain, substr(a.tanggal,6,2) as bulan
    from ".$dbname.".pabrik_timbangan a
    left join ".$dbname.".pmn_kontrakjual b on a.nokontrak = b.nokontrak 
        where a.kodebarang in ('40000001', '40000002') and a.tanggal like '".$tahun."%' and a.millcode ".$where." order by a.tanggal";
$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res12->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res12->fetch()){
    if($ba12->kodebarang == '40000001')$komoditi='CPO';
    if($ba12->kodebarang == '40000002')$komoditi='KER';
    $fisikjual[$komoditi][$ba12->bulan]+=$ba12->fisik;
//    if($ba12->catatanlain=='exclude')$nilai[$komoditi][$ba12->bulan]+=$ba12->nilai; else $nilai[$komoditi][$ba12->bulan]+=($ba12->nilai/1.1);
//    @$hargajual[$komoditi][$ba12->bulan]=$nilai[$komoditi][$ba12->bulan]/$fisik[$komoditi][$ba12->bulan];
    if($bulan>=$ba12->bulan){
        $fisikjual[$komoditi]['sd']+=$ba12->fisik;
//        if($ba12->catatanlain=='exclude')$nilai[$komoditi]['sd']+=$ba12->nilai; else $nilai[$komoditi]['sd']+=($ba12->nilai/1.1);
//        @$hargajual[$komoditi]['sd']=$nilai[$komoditi]['sd']/$fisik[$komoditi]['sd'];
    }    
}

//report format
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'
    order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $dzArrLR[$bar->nourut]['nourut']=$bar->nourut;
    $dzArrLR[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dzArrLR[$bar->nourut]['keterangan']=$bar->keterangandisplay;}
    else{
        $dzArrLR[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dzArrLR[$bar->nourut]['noakundari']=$bar->noakundari;
    $dzArrLR[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    
    // dari total
    $qwe=explode(",",$bar->noakundisplay);
    if(!empty($qwe))foreach($qwe as $rty){
	  if($rty==''){
	  }else{
        if((number_format($rty)!=0)){
            $emaknya[$rty]=$bar->nourut;
            $adaemaknya[$rty]=$rty;
        }
		}
    }

    $semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';
    $st12="select noakun,(".$semuakolomdb.") as jumlah, awal01 as awal, substr(periode,5,2) as bulan
        from ".$dbname.".keu_saldobulanan where noakun between '".$bar->noakundari."' 
        and '".$bar->noakunsampai."' and periode like'".$tahun."%' and kodeorg ".$where." order by periode";
    $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res12->setFetchMode(PDO::FETCH_OBJ);
    while($ba12=$res12->fetch()){
//        $emaknya[$ba12->noakun]=$bar->nourut;
        $dzArrLR[$bar->nourut][$ba12->bulan]+=$ba12->jumlah;
//        $dzArrLR2[$ba12->noakun]['noakun']=$ba12->noakun;
        $dzArrLR2[$ba12->noakun][$ba12->bulan]=$ba12->jumlah;
        if($bulan>=$ba12->bulan){
            $dzArrLR[$bar->nourut]['sd']+=$ba12->jumlah+$ba12->awal;
            $dzArrLR2[$ba12->noakun]['sd']+=$ba12->jumlah+$ba12->awal;
        }
    }  
}

for ($i = 1; $i <= $bulan; $i++) {
    if($i<10)$ii='0'.$i; else $ii=$i;
    @$hargajual['CPO'][$ii]=$dzArrLR2[$noakunjualcpo][$ii]/$fisikjual['CPO'][$ii];
    @$hargajual['KER'][$ii]=$dzArrLR2[$noakunjualker][$ii]/$fisikjual['KER'][$ii];
}
@$hargajual['CPO']['sd']=$dzArrLR2[$noakunjualcpo]['sd']/$fisikjual['CPO']['sd'];
@$hargajual['KER']['sd']=$dzArrLR2[$noakunjualker]['sd']/$fisikjual['KER']['sd'];

for ($i = 1; $i <= $bulan; $i++) {
    if($i<10)$ii='0'.$i; else $ii=$i;
    @$persenjual['CPO'][$ii]=$fisikprod['CPO'][$ii]*$hargajual['CPO'][$ii]/(($fisikprod['CPO'][$ii]*$hargajual['CPO'][$ii])+($fisikprod['KER'][$ii]*$hargajual['KER'][$ii]));
    @$persenjual['KER'][$ii]=$fisikprod['KER'][$ii]*$hargajual['KER'][$ii]/(($fisikprod['CPO'][$ii]*$hargajual['CPO'][$ii])+($fisikprod['KER'][$ii]*$hargajual['KER'][$ii]));
}
@$persenjual['CPO']['sd']=$fisikprod['CPO']['sd']*$hargajual['CPO']['sd']/(($fisikprod['CPO']['sd']*$hargajual['CPO']['sd'])+($fisikprod['KER']['sd']*$hargajual['KER']['sd']));
@$persenjual['KER']['sd']=$fisikprod['KER']['sd']*$hargajual['KER']['sd']/(($fisikprod['CPO']['sd']*$hargajual['CPO']['sd'])+($fisikprod['KER']['sd']*$hargajual['KER']['sd']));
    
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

$st12="select noakun, namaakun, namaakun1
    from ".$dbname.".keu_5akun where level=5";
$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res12->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res12->fetch()){
    if($_SESSION['language']=='ID'){
        $akun[$ba12->noakun]=$ba12->namaakun;}
    else{
        $akun[$ba12->noakun]=$ba12->namaakun1;
    }
}      

$noakunproduksicpo='213101';
$noakunproduksiker='213102';

for ($i = 1; $i <= $bulan; $i++) {
    if($i<10)$ii='0'.$i; else $ii=$i;
    $j=$i-1;
    if($j<10)$jj='0'.$j; else $jj=$j;
    if($i>1){
        $fisikawal['CPO'][$ii]=$fisikakhir['CPO'][$jj];
        $fisikawal['KER'][$ii]=$fisikakhir['KER'][$jj];
    }else{ // bulan1, harga dan fisik udah ada
        
    }   
    $fisikstok['CPO'][$ii]=$fisikawal['CPO'][$ii]+$fisikprod['CPO'][$ii];
    $fisikakhir['CPO'][$ii]=$fisikstok['CPO'][$ii]-$fisikjual['CPO'][$ii];
    $fisikstok['KER'][$ii]=$fisikawal['KER'][$ii]+$fisikprod['KER'][$ii];
    $fisikakhir['KER'][$ii]=$fisikstok['KER'][$ii]-$fisikjual['KER'][$ii];
}
$fisikstok['CPO']['sd']=$fisikawal['CPO']['sd']+$fisikprod['CPO']['sd'];
$fisikakhir['CPO']['sd']=$fisikakhir['CPO'][$bulan];
$fisikstok['KER']['sd']=$fisikawal['KER']['sd']+$fisikprod['KER']['sd'];
$fisikakhir['KER']['sd']=$fisikakhir['KER'][$bulan];

if(!empty($dzArrLR))foreach($dzArrLR as $data){ // level 0
    if($data['tipe']=='Header')
    {
        $totallagi=0;        
    }
    if($data['tipe']=='Detail'){
        if($data['nourut']=='213101'){ // biaya produksi CPO
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $nilaiprod['CPO'][$ii]=$dzArrLR['212999'][$ii]*$persenjual['CPO'][$ii];
                $dzArrLR['213101'][$ii]=$nilaiprod['CPO'][$ii];
            }
            $nilaiprod['CPO']['sd']=$dzArrLR['212999']['sd']*$persenjual['CPO']['sd'];
            $dzArrLR['213101']['sd']=$nilaiprod['CPO']['sd'];                
        }
        if($data['nourut']=='213102'){ // biaya produksi KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $nilaiprod['KER'][$ii]=$dzArrLR['212999'][$ii]*$persenjual['KER'][$ii];
                $dzArrLR['213102'][$ii]=$nilaiprod['KER'][$ii];
            }
            $nilaiprod['KER']['sd']=$dzArrLR['212999']['sd']*$persenjual['KER']['sd'];
            $dzArrLR['213102']['sd']=$nilaiprod['KER']['sd'];               
        }
        if($data['nourut']=='213201'){ // persediaan awal CPO
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $j=$i+1;
                if($j<10)$jj='0'.$j; else $jj=$j;
                if($i==1){
                    $nilaiawal['CPO'][$ii]=$fisikawal['CPO'][$ii]*$hargaawal['CPO'][$ii];
                }else{
                    
                }
                $nilaistok['CPO'][$ii]=$nilaiawal['CPO'][$ii]+$nilaiprod['CPO'][$ii];                
                @$hargastok['CPO'][$ii]=$nilaistok['CPO'][$ii]/$fisikstok['CPO'][$ii];                                                
                $nilaiakhir['CPO'][$ii]=$hargastok['CPO'][$ii]*$fisikakhir['CPO'][$ii];
                $nilaijual['CPO'][$ii]=$hargastok['CPO'][$ii]*$fisikjual['CPO'][$ii];
                $nilaiawal['CPO'][$jj]=$nilaiakhir['CPO'][$ii];
                $dzArrLR['213201'][$ii]=$nilaiawal['CPO'][$ii];
                
                $nilaistok['CPO']['sd']+=$nilaistok['CPO'][$ii];
                $nilaiprod['CPO']['sd']+=$nilaiprod['CPO'][$ii];
                
                $nilaijual['CPO']['sd']+=$nilaijual['CPO'][$ii];
            }
            $nilaiawal['CPO']['sd']=$nilaiawal['CPO']['01'];            
            @$hargastok['CPO']['sd']=$nilaistok['CPO']['sd']/$fisikstok['CPO']['sd'];                
            $nilaiakhir['CPO']['sd']=$nilaiakhir['CPO'][$bulan];   
//            $nilaijual['CPO']['sd']=$nilaistok['CPO']['sd']-$nilaiakhir['CPO']['sd'];
            
            $dzArrLR['213201']['sd']=$nilaiawal['CPO']['sd'];
        }
        if($data['nourut']=='213202'){ // persediaan awal KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $j=$i+1;
                if($j<10)$jj='0'.$j; else $jj=$j;
                if($i==1){
                    $nilaiawal['KER'][$ii]=$fisikawal['KER'][$ii]*$hargaawal['KER'][$ii];
                }else{
                    
                }
                $nilaistok['KER'][$ii]=$nilaiawal['KER'][$ii]+$nilaiprod['KER'][$ii];                
                @$hargastok['KER'][$ii]=$nilaistok['KER'][$ii]/$fisikstok['KER'][$ii];                                                
                $nilaiakhir['KER'][$ii]=$hargastok['KER'][$ii]*$fisikakhir['KER'][$ii];
                $nilaijual['KER'][$ii]=$hargastok['KER'][$ii]*$fisikjual['KER'][$ii];
                $nilaiawal['KER'][$jj]=$nilaiakhir['KER'][$ii];
                $dzArrLR['213202'][$ii]=$nilaiawal['KER'][$ii];
                
                $nilaistok['KER']['sd']+=$nilaistok['KER'][$ii];
                $nilaiprod['KER']['sd']+=$nilaiprod['KER'][$ii];
                
                $nilaijual['KER']['sd']+=$nilaijual['KER'][$ii];
            }
            $nilaiawal['KER']['sd']=$nilaiawal['KER']['01'];            
            @$hargastok['KER']['sd']=$nilaistok['KER']['sd']/$fisikstok['KER']['sd'];                
            $nilaiakhir['KER']['sd']=$nilaiakhir['KER'][$bulan];   
           
            $dzArrLR['213202']['sd']=$nilaiawal['KER']['sd'];                       
        }        

        if($data['nourut']=='213401'){ // persediaan akhir CPO
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;                
                $dzArrLR['213401'][$ii]=$nilaiakhir['CPO'][$ii];                    
                
            }
            $dzArrLR['213401']['sd']=$nilaiakhir['CPO'][$bulan];             
        }
        
        if($data['nourut']=='213402'){ // persediaan akhir KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;                
                $dzArrLR['213402'][$ii]=$nilaiakhir['KER'][$ii];                    
                
            }
            $dzArrLR['213402']['sd']=$nilaiakhir['KER'][$bulan];             
        }
        
        if($data['nourut']=='213301'){ // barang siap dijual CPO
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArrLR['213301'][$ii]=$nilaistok['CPO'][$ii];
            }
            $dzArrLR['213301']['sd']=$nilaistok['CPO']['sd'];             
        }
        
        if($data['nourut']=='213302'){ // barang siap dijual KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArrLR['213302'][$ii]=$nilaistok['KER'][$ii];
            }
            $dzArrLR['213302']['sd']=$nilaistok['KER']['sd'];             
        }
        
        if($data['nourut']=='213501'){ // penjualan CPO
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArrLR['213501'][$ii]=$nilaijual['CPO'][$ii];
            }
            $dzArrLR['213501']['sd']=$nilaijual['CPO']['sd'];             
        }
        
        if($data['nourut']=='213502'){ // penjualan KER
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArrLR['213502'][$ii]=$nilaijual['KER'][$ii];
            }
            $dzArrLR['213502']['sd']=$nilaijual['KER']['sd'];             
        }
        
        // subtotal
        for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
            $subtotal[$ii]+=$data[$ii];
        }
        $subtotal['sd']+=$data['sd'];
        
        $totallagi=0;                
    }
    if($data['tipe']=='Total'){
        if($data['nourut']=='212999'){ // BIAYA PRODUKSI SETELAH ELIMINASI
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArrLR['212999'][$ii]=$dzArrLR['211999'][$ii]+$dzArrLR['212001'][$ii];
            }
            $dzArrLR['212999']['sd']=$dzArrLR['211999']['sd']+$dzArrLR['212001']['sd'];
        }
        if($data['nourut']=='213509'){ // JUMLAH BEBAN POKOK PENJUALAN
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArrLR['213509'][$ii]=$dzArrLR['213501'][$ii]+$dzArrLR['213502'][$ii];
            }
            $dzArrLR['213509']['sd']=$dzArrLR['213501']['sd']+$dzArrLR['213502']['sd'];
        }
//        if($data['nourut']=='213999'){ // LABA(RUGI) KOTOR
//            for ($i = 1; $i <= 12; $i++) {
//                if($i<10)$ii='0'.$i; else $ii=$i;
//                $dzArrLR['213999'][$ii]=$dzArrLR['100009'][$ii]+$dzArrLR['213509'][$ii];
//            }
//            $dzArrLR['213999']['sd']=$dzArrLR['100009']['sd']+$dzArrLR['213509']['sd'];
//        }
        
        
        
        
        
        if($data['nourut']=='214999'){ // LABA(RUGI) USAHA
            $totallagi=0;
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArrLR['214999'][$ii]=$dzArrLR['213999'][$ii]+$dzArrLR['214009'][$ii];
            }
            $dzArrLR['214999']['sd']=$dzArrLR['213999']['sd']+$dzArrLR['214009']['sd'];
        }
        if($data['nourut']=='215999'){ // LABA (RUGI) SEBELUM PAJAK
            $totallagi=1;
//            for ($i = 1; $i <= 12; $i++) {
//                if($i<10)$ii='0'.$i; else $ii=$i;
//                $dzArrLR['215999'][$ii]=$dzArrLR['214999'][$ii]+$dzArrLR['215001'][$ii];
//            }
//            $dzArrLR['215999']['sd']=$dzArrLR['214999']['sd']+$dzArrLR['215001']['sd'];
        }
        if($data['nourut']=='216999'){ // LABA (RUGI) BERSIH
            $totallagi=1;
//            for ($i = 1; $i <= 12; $i++) {
//                if($i<10)$ii='0'.$i; else $ii=$i;
//                $dzArrLR['216999'][$ii]=$dzArrLR['215999'][$ii]+$dzArrLR['216001'][$ii];
//            }
//            $dzArrLR['216999']['sd']=$dzArrLR['215999']['sd']+$dzArrLR['216001']['sd'];
        }
        if($totallagi==1){
            if(!empty($adaemaknya))foreach($adaemaknya as $ada){
                for ($i = 1; $i <= $bulan; $i++) {
                    if($i<10)$ii='0'.$i; else $ii=$i;
                    if($emaknya[$ada]==$data['nourut'])$dzArrLR[$data['nourut']][$ii]+=$dzArrLR[$ada][$ii];
                }
                if($emaknya[$ada]==$data['nourut'])$dzArrLR[$data['nourut']]['sd']+=$dzArrLR[$ada]['sd'];
            }
        }else{
            for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                $dzArrLR[$data['nourut']][$ii]+=$subtotal[$ii];
                $subtotal[$ii]=0;            
            } 
            $dzArrLR[$data['nourut']]['sd']+=$subtotal['sd'];                        
        }
        $subtotal['sd']=0;
        
        $totallagi=1;        
    }

}

//echo "<pre>";
//print_r($dzArrLR);
//echo "</pre>";

//////////////////////////////////////////////////////////////////////////////// LABA RUGI END

$kodelaporan='LK - NERACA';

$periodesaldo=str_replace("-", "", $periode);

//periode db
$periodeCUR=str_replace("-", "", $periode);
$periodePRF=str_replace("-", "", $periodelalu);
$periodeLSD=str_replace("-", "", $desemberlalu);

//kolom db
//$kolomCUR='awal'.$bulan;
//$kolomPRF='awal'.$bulanlalu;
//$kolomLSD='awal12';
$kolomCUR='awal'.$bulan.'+debet'.$bulan.'-kredit'.$bulan;
$kolomPRF='awal'.$bulanlalu.'+debet'.$bulanlalu.'-kredit'.$bulanlalu;
$kolomLSD='awal12+debet12-kredit12';

//title table
$t=mktime(0,0,0,substr($periodeCUR,4,2),15,substr($periodeCUR,0,4));
$captionCUR=date('M-Y',$t);
$t=mktime(0,0,0,substr($periodePRF,4,2),15,substr($periodePRF,0,4));
$captionPRF=date('M-Y',$t);
$t=mktime(0,0,0,substr($periodeLSD,4,2),15,substr($periodeLSD,0,4));
$captionLSD=date('M-Y',$t);

//involving units
if($unit=='')$where=" kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
else $where=" kodeorg='".$unit."'";

//report format
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut";
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
    
    $st12="select noakun,sum(".$kolomPRF.") as jumlah
        from ".$dbname.".keu_saldobulanan where noakun between '".$bar->noakundari."' 
        and '".$bar->noakunsampai."' and (periode='".$periodePRF."') and ".$where." group by noakun";  
    $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res12->setFetchMode(PDO::FETCH_OBJ);
    $jlhlalu=0;
    while($ba12=$res12->fetch())
    {
        $qwe=$ba12->noakun.$bar->nourut;
        $dzArr3[$qwe]=$bar->nourut;
        if($ba12->noakun=='3110700'){ // 3110700 Laba (Rugi) Tahun Berjalan
            $dzArr[$bar->nourut]['PRF']+=($dzArrLR['216999']['sd']-$dzArrLR['216999'][$bulanlalu]);
        }else if($ba12->noakun=='1150201'){ // 1150201 Persediaan: CPO
            $dzArr[$bar->nourut]['PRF']+=($dzArrLR['213401']['sd']-$dzArrLR['213401'][$bulanlalu]);
        }else if($ba12->noakun=='1150202'){ // 1150202 Persediaan: Kernel
            $dzArr[$bar->nourut]['PRF']+=($dzArrLR['213402']['sd']-$dzArrLR['213402'][$bulanlalu]);
        }else{
            if($bar->nourut=='110103'){ // 110103 PIUTANG USAHA
                if($ba12->jumlah>0){
                    $dzArr[$bar->nourut]['PRF']+=$ba12->jumlah;
                }else{
                    $dzArr['120102']['PRF']+=$ba12->jumlah;
                    $qwe=$ba12->noakun.'120102';
                    $dzArr3[$qwe]='120102';
                }
            }else
            $dzArr[$bar->nourut]['PRF']+=$ba12->jumlah;
        }
//        $dzArr3[$ba12->noakun]=$bar->nourut;
    }     
    
    $st12="select noakun,sum(".$kolomCUR.") as jumlah
        from ".$dbname.".keu_saldobulanan where noakun between '".$bar->noakundari."' 
        and '".$bar->noakunsampai."' and (periode='".$periodeCUR."') and ".$where." group by noakun";
    $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res12->setFetchMode(PDO::FETCH_OBJ);
    $jlhsekarang=0;
    while($ba12=$res12->fetch())
    {
        $qwe=$ba12->noakun.$bar->nourut;
        $dzArr3[$qwe]=$bar->nourut;
        if($ba12->noakun=='3110700'){ // 3110700 Laba (Rugi) Tahun Berjalan
            $dzArr[$bar->nourut]['CUR']+=($dzArrLR['216999']['sd']);
        }else if($ba12->noakun=='1150201'){ // 1150201 Persediaan: CPO
            $dzArr[$bar->nourut]['CUR']+=($dzArrLR['213401']['sd']);
        }else if($ba12->noakun=='1150202'){ // 1150202 Persediaan: Kernel
            $dzArr[$bar->nourut]['CUR']+=($dzArrLR['213402']['sd']);
        }else{
            if($bar->nourut=='110103'){ // 110103 PIUTANG USAHA
                if($ba12->jumlah>0){
                    $dzArr[$bar->nourut]['CUR']+=$ba12->jumlah;
                }else{
                    $dzArr['120102']['CUR']+=$ba12->jumlah;
                    $qwe=$ba12->noakun.'120102';
                    $dzArr3[$qwe]='120102';
                }
            }else
            $dzArr[$bar->nourut]['CUR']+=$ba12->jumlah;            
        }
//        $dzArr3[$ba12->noakun]=$bar->nourut;
    }      
    
    $st12="select noakun,sum(".$kolomLSD.") as jumlah
        from ".$dbname.".keu_saldobulanan where noakun between '".$bar->noakundari."' 
        and '".$bar->noakunsampai."' and (periode='".$periodeLSD."') and ".$where." group by noakun";
    $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res12->setFetchMode(PDO::FETCH_OBJ);
    $jlhsekarang=0;
    while($ba12=$res12->fetch())
    {
        $qwe=$ba12->noakun.$bar->nourut;
        $dzArr3[$qwe]=$bar->nourut;
        if($ba12->noakun=='3110700'){ // 3110700 Laba (Rugi) Tahun Berjalan
            $dzArr[$bar->nourut]['LSD']+=$ba12->jumlah;
        }else if($ba12->noakun=='1150201'){ // 1150201 Persediaan: CPO
            $dzArr[$bar->nourut]['LSD']+=$ba12->jumlah;
        }else if($ba12->noakun=='1150202'){ // 1150202 Persediaan: Kernel
            $dzArr[$bar->nourut]['LSD']+=$ba12->jumlah;
        }else{
            if($bar->nourut=='110103'){ // 110103 PIUTANG USAHA
                if($ba12->jumlah>0){
                    $dzArr[$bar->nourut]['LSD']+=$ba12->jumlah;
                }else{
                    $dzArr['120102']['LSD']+=$ba12->jumlah;
                    $qwe=$ba12->noakun.'120102';
                    $dzArr3[$qwe]='120102';
                }
            }else
            $dzArr[$bar->nourut]['LSD']+=$ba12->jumlah;
        }
//        $dzArr3[$ba12->noakun]=$bar->nourut;
    }    
}

//$dzArr[$bar->nourut]['nourut']=$bar->nourut;
if(!empty($dzArr)) foreach($dzArr as $c=>$key) {
    $sort_nouru[] = $key['nourut'];
}

// sort
if(!empty($dzArr))array_multisort($sort_nouru, SORT_ASC, $dzArr);

$stream=$kodelaporan." ".$pt." ".$unit." ".$periode;
$stream.="<table class=sortable border=1 cellspacing=0>
    <thead>
        <tr class=rowheader>
            <td style='width:520px' align=center colspan=3 rowspan=2>Description</td>
            <td style='width:120px' align=center rowspan=2>".$captionCUR."</td>
            <td style='width:120px' align=center rowspan=2>".$captionPRF."</td>    
            <td style='width:120px' align=center rowspan=2>".$captionLSD."</td>    
            <td align=center colspan=2>Increase/Decrease</td>    
        </tr>
        <tr class=rowheader>
            <td style='width:120px' align=center>Rupiah</td>
            <td style='width:50px' align=center>%</td>
        </tr>
    </thead><tbody>";

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

$st12="select noakun, ".$kolomCUR." as jumlah
    from ".$dbname.".keu_saldobulanan where (periode='".$periodeCUR."') and ".$where;
$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res12->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res12->fetch())
{
    if($ba12->noakun=='3110700'){ // 3110700 Laba (Rugi) Tahun Berjalan
        $dzArr2[$ba12->noakun]['CUR']=($dzArrLR['216999']['sd']);
    }else if($ba12->noakun=='1150201'){ // 1150201 Persediaan: CPO
        $dzArr2[$ba12->noakun]['CUR']=($dzArrLR['213401']['sd']);
    }else if($ba12->noakun=='1150202'){ // 1150202 Persediaan: Kernel
        $dzArr2[$ba12->noakun]['CUR']=($dzArrLR['213402']['sd']);
    }else{
        $dzArr2[$ba12->noakun]['CUR']+=$ba12->jumlah;
    }        
    $dzArr2[$ba12->noakun]['noakun']=$ba12->noakun;
}      
$st12="select noakun, ".$kolomPRF." as jumlah
    from ".$dbname.".keu_saldobulanan where (periode='".$periodePRF."') and ".$where;
$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res12->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res12->fetch())
{
    if($ba12->noakun=='3110700'){ // 3110700 Laba (Rugi) Tahun Berjalan
        $dzArr2[$ba12->noakun]['PRF']=($dzArrLR['216999']['sd']-$dzArrLR['216999'][$bulanlalu]);
    }else if($ba12->noakun=='1150201'){ // 1150201 Persediaan: CPO
        $dzArr2[$ba12->noakun]['PRF']=($dzArrLR['213401']['sd']-$dzArrLR['213401'][$bulanlalu]);
    }else if($ba12->noakun=='1150202'){ // 1150202 Persediaan: Kernel
        $dzArr2[$ba12->noakun]['PRF']=($dzArrLR['213402']['sd']-$dzArrLR['213402'][$bulanlalu]);
    }else{
        $dzArr2[$ba12->noakun]['PRF']+=$ba12->jumlah;        
    }
    $dzArr2[$ba12->noakun]['noakun']=$ba12->noakun;
}      
$st12="select noakun, ".$kolomLSD." as jumlah
    from ".$dbname.".keu_saldobulanan where (periode='".$periodeLSD."') and ".$where;
$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res12->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res12->fetch())
{
    if($ba12->noakun=='3110700'){ // 3110700 Laba (Rugi) Tahun Berjalan
        $dzArr2[$ba12->noakun]['LSD']+=$ba12->jumlah;
    }else if($ba12->noakun=='1150201'){ // 1150201 Persediaan: CPO
        $dzArr2[$ba12->noakun]['LSD']+=$ba12->jumlah;
    }else if($ba12->noakun=='1150202'){ // 1150202 Persediaan: Kernel
        $dzArr2[$ba12->noakun]['LSD']+=$ba12->jumlah;
    }else{
        $dzArr2[$ba12->noakun]['LSD']+=$ba12->jumlah;
    }
    $dzArr2[$ba12->noakun]['noakun']=$ba12->noakun;
}      

//echo "<pre>";
//print_r($dzArr);
//echo "</pre>";
//exit;

//ambil format mesinlaporan
if(!empty($dzArr))foreach($dzArr as $data){
    if($data['tipe']=='Header')
    {
        $stream.="<tr class=rowcontent>
            <td colspan=8><b>".$data['keterangan']."</b></td>
        </tr>"; 
    }
    else
    if($data['tipe']=='Total'){
        if($totallagi==1){
            $subtotal['CUR']=$subtotal2['CUR'];
            $subtotal['PRF']=$subtotal2['PRF'];
            $subtotal['LSD']=$subtotal2['LSD'];
            $subtotal2['CUR']=0;
            $subtotal2['PRF']=0;
            $subtotal2['LSD']=0;  
        }
        @$subtotalPER=($subtotal['CUR']-$subtotal['PRF'])/$subtotal['PRF']*100;
        $stream.="
        <tr class=rowcontent>
            <td style='width:10px'></td>
            <td style='width:10px'></td>
            <td><b>".$data['keterangan']."</b></td>
            <td align=right><b>".number_format($subtotal['CUR'],2)."</b></td>
            <td align=right><b>".number_format($subtotal['PRF'],2)."</b></td>    
            <td align=right><b>".number_format($subtotal['LSD'],2)."</b></td>    
            <td align=right><b>".number_format($subtotal['CUR']-$subtotal['PRF'],2)."</b></td>    
            <td align=right><b>".number_format($subtotalPER,2)."</b></td>    
        </tr>
        <tr class=rowcontent><td colspan=8></td></tr>
        ";
        if($totallagi==0){
            $subtotal2['CUR']+=$subtotal['CUR'];
            $subtotal2['PRF']+=$subtotal['PRF'];
            $subtotal2['LSD']+=$subtotal['LSD'];        
        }
        $subtotal['CUR']=0;
        $subtotal['PRF']=0;
        $subtotal['LSD']=0;  
        $totallagi=1;
    }
    else
    if($data['tipe']=='Detail'){
        $totallagi=0;
        @$dataPER=($data['CUR']-$data['PRF'])/$data['PRF']*100;
        $stream.="
        <tr class=rowcontent title='Click untuk melihat detail' style=cursor:pointer; onclick=\"switchHidden(".$data['nourut'].")\">
            <td style='width:10px'></td>
            <td colspan=2>".$data['keterangan']."</td>
            <td align=right>".number_format($data['CUR'],2)."</td>
            <td align=right>".number_format($data['PRF'],2)."</td>    
            <td align=right>".number_format($data['LSD'],2)."</td>    
            <td align=right>".number_format($data['CUR']-$data['PRF'],2)."</td>    
            <td align=right>".number_format($dataPER,2)."</td>    
        </tr>";          
        $subtotal['CUR']+=$data['CUR'];
        $subtotal['PRF']+=$data['PRF'];
        $subtotal['LSD']+=$data['LSD'];
        $stream.="<tr><td colspan=8><div style=\"display:none;\" id=".$data['nourut']."><table class=sortable border=1 cellspacing=0>";
        if(!empty($dzArr2))foreach($dzArr2 as $data2){
            $datacur=0;
            $dataprf=0;
            $datalsd=0;
//            if(($data2['noakun']>=$data['noakundari'])and($data2['noakun']<=$data['noakunsampai']))
            $qwe=$data2['noakun'].$data['nourut'];
            if($data['nourut']=='110103'){
                if($data2['CUR']>0)$datacur=$data2['CUR'];
                if($data2['PRF']>0)$dataprf=$data2['PRF'];
                if($data2['LSD']>0)$datalsd=$data2['LSD'];
            }else if($data['nourut']=='120102'){
                if($data2['CUR']<0)$datacur=$data2['CUR'];
                if($data2['PRF']<0)$dataprf=$data2['PRF'];
                if($data2['LSD']<0)$datalsd=$data2['LSD'];
            }else{
                $datacur=$data2['CUR'];
                $dataprf=$data2['PRF'];
                $datalsd=$data2['LSD'];
            }
            @$data2PER=($datacur-$dataprf)/$dataprf*100;
            if($dzArr3[$qwe]==$data['nourut'])  
            $stream.="
            <tr class=rowcontent>
                <td style='width:10px'></td>
                <td style='width:10px'></td>
                <td style='width:500px'>".$akun[$data2['noakun']]."</td>
                <td style='width:120px' align=right>".number_format($datacur,2)."</td>
                <td style='width:120px' align=right>".number_format($dataprf,2)."</td>    
                <td style='width:120px' align=right>".number_format($datalsd,2)."</td>    
                <td style='width:120px' align=right>".number_format($datacur-$dataprf,2)."</td>    
                <td style='width:50px' align=right>".number_format($data2PER,2)."</td>    
            </tr>";          
        }
        $stream.="</table></div></td></tr>";
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