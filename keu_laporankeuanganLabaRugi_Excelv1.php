<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$pt=$_GET['pt'];
$unit=$_GET['unit'];
$periode=$_GET['periode'];

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];
$st12="select noakun, namaakun, namaakun1  from ".$dbname.".keu_5akun where level=5";
$res1=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while($ba12=$res1->fetch())
{
    if($_SESSION['language']=='ID'){
        $akun[$ba12->noakun]=$ba12->namaakun;}
    else{
        $akun[$ba12->noakun]=$ba12->namaakun1;
    }
}    

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
while($bar=$res->fetch())
{
    $namapt=strtoupper($bar->namaorganisasi);
}

$kodelaporan='LK - LABA RUGI V1';

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


#DATA CPO DAN KERNEL, PRODUKSI,SALDO END SCRIPT#

//report format
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $noakunsql=" noakun between '".$bar->noakundari."' and '".$bar->noakunsampai."'";
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
    if($bar->tipe=='Total'){
            $isiRange[$bar->nourut]=$bar->noakundisplay;
        }
        switch($bar->nourut){#update where untuk biaya tidak langsung
                case'200004':#biaya tidak langsung kebun
                $dtNoakun=array();
                $byOverHead2=array();
                $byOverHead3=array();
                $byOverHead=array();
                $byOverHeadDt=array();
            //     $sDeplesi="select sum(jumlah) as deplesi,substr(tanggal,6,2) as bln,noakun  from ".$dbname.".keu_jurnaldt 
            //     where left(tanggal,4)='".$tahun."'  and  noakun='7150201' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$pt."') group by left(tanggal,7)";
            // $qDeplesi=$owlPDO->query($sDeplesi) or die(print " Gagal: ".PDOException::getMessage());
            // $qDeplesi->setFetchMode(PDO::FETCH_ASSOC);
            // while($rDeplesi=$qDeplesi->fetch())
            // {
            //     $byDeplesi[$rDeplesi['bln']]=$rDeplesi['deplesi'];
            //     $lstData[$bar->nourut.$rDeplesi['noakun']][$rDeplesi['bln']]=$rDeplesi['deplesi'];
            //     $lstData[$bar->nourut.$rDeplesi['noakun']]['sd']+=$rDeplesi['deplesi'];
            //     $daftar[$rDeplesi['noakun']]=$rDeplesi['noakun'];
            // }
              
            $sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
                       where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')
                       group by left(tanggal,7) order by unit asc";
            $qUnitKbn=$owlPDO->query($sUnitKbn) or die(print " Gagal: ".PDOException::getMessage());
            $qUnitKbn->setFetchMode(PDO::FETCH_ASSOC);
            while($rowUnitKbn=$qUnitKbn->fetch()){
                $arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
            }

            #total unit pabrik
            $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
                      where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
            $qUnitPbrk=$owlPDO->query($sUnitPbrk) or die(print " Gagal: ".PDOException::getMessage());
            $qUnitPbrk->setFetchMode(PDO::FETCH_ASSOC);
            while($rowUnitPbrk=$qUnitPbrk->fetch()){   
                $arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
            }
            for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
                $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
                }
            #over head ro dan ho
            $sOverHead="select sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."'  and noakun like '7%' and left(noakun,5)!='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL') and induk='".$pt."')  
                        and noakun!='7199999'  group by left(tanggal,7)";
            $qOverHead=$owlPDO->query($sOverHead) or die(print " Gagal: ".PDOException::getMessage());
            $qOverHead->setFetchMode(PDO::FETCH_ASSOC);
            while($rOverHead=$qOverHead->fetch()){ 
                $byOverHead[$rOverHead['bln']]=$rOverHead['overhead'];
            }

            for ($i = 1; $i <= $bulan; $i++) {
                if($unit==''){
                    if($i<10)$ii='0'.$i; else $ii=$i;
                    @$byOverHead2[$ii]=$byOverHead[$ii]/$rowUnit[$ii];
                    $dzArr[$bar->nourut][$ii]=($arrRwKbn[$ii]*$byOverHead2[$ii]);
                    $dzArr[$bar->nourut]['sd']+=($arrRwKbn[$ii]*$byOverHead2[$ii]);
                }
            }
            $sOverHead2="select noakun,sum(jumlah) as overhead,substr(tanggal,6,2) as bln,noakun  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."'  and noakun like '7%' and left(noakun,5)!='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL') and induk='".$pt."')  and noakun!='7199999' 
                        group by noakun,left(tanggal,7)";
            $qOverHead2=$owlPDO->query($sOverHead2) or die(print " Gagal: ".PDOException::getMessage());
            $qOverHead2->setFetchMode(PDO::FETCH_ASSOC);
            while($rOverHead2=$qOverHead2->fetch()){                 
                $byOverHead3[$rOverHead2['noakun']][$rOverHead2['bln']]=$rOverHead2['overhead'];
                $dtNoakun[$rOverHead2['noakun']]=$rOverHead2['noakun'];
            }
            foreach($dtNoakun as $lstNoakun){
                if($unit==''){
                    for ($i = 1; $i <= $bulan; $i++) {
                    if($i<10)$ii='0'.$i; else $ii=$i;
                        @$byOverHeadDt[$lstNoakun][$ii]=$byOverHead3[$lstNoakun][$ii]/$rowUnit[$ii];
                        $lstData[$bar->nourut.$lstNoakun][$ii]=$byOverHeadDt[$lstNoakun][$ii]*$arrRwKbn[$ii];
                        $lstData[$bar->nourut.$lstNoakun]['sd']+=$byOverHeadDt[$lstNoakun][$ii]*$arrRwKbn[$ii];
                    }
                }
                $daftar[$lstNoakun]=$lstNoakun;
            }
                        $nomakun=explode(",",$bar->noakundisplay);
                        if($unit==''){
                                $where="";
                                $where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')  and left(noakun,5)!='71502'";
                        }else{
                                $whr="kodeorganisasi='".$unit."'";
                                $optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
                                if($optTipe[$unit]!='KEBUN'){
                                        $where="";
                                }else{
                                        $where=" ='".$unit."'  and left(noakun,5)!='71502'";
                                }
                        } 
                break;
                case'300003':#biaya tidak langsung pabrik
                $dtNoakun=array();
                $byOverHead2=array();
                $byOverHead3=array();
                $byOverHead=array();
                $byOverHeadDt=array();
                $sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
              where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
            $qUnitKbn=$owlPDO->query($sUnitKbn) or die(print " Gagal: ".PDOException::getMessage());
            $qUnitKbn->setFetchMode(PDO::FETCH_ASSOC);
            while($rowUnitKbn=$qUnitKbn->fetch())
            { 
                $arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
             }

            #total unit pabrik
            $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
                      where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
            $qUnitPbrk=$owlPDO->query($sUnitPbrk) or die(print " Gagal: ".PDOException::getMessage());
            $qUnitPbrk->setFetchMode(PDO::FETCH_ASSOC);
            while($rowUnitPbrk=$qUnitPbrk->fetch())
            {  
                $arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
            }
            for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
                $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
                }
            #over head ro dan ho
            $sOverHead="select sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."'  and noakun like '7%' and left(noakun,5)!='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL','TRAKSI') and induk='".$pt."')  
                        and noakun!='7199999' and noakun not in (".$rakunTnmn[0]['nilai'].") group by left(tanggal,7)";
            $qOverHead=$owlPDO->query($sOverHead) or die(print " Gagal: ".PDOException::getMessage());
            $qOverHead->setFetchMode(PDO::FETCH_ASSOC);
            while($rOverHead=$qOverHead->fetch()){ 
                $byOverHead[$rOverHead['bln']]=$rOverHead['overhead'];
            }
            for ($i = 1; $i <= $bulan; $i++) {
                if($unit==''){
                    if($i<10)$ii='0'.$i; else $ii=$i;
                    @$byOverHead2[$ii]=$byOverHead[$ii]/$rowUnit[$ii];
                    $dzArr[$bar->nourut][$ii]=($arrRwPbrk[$ii]*$byOverHead2[$ii]);
                    $dzArr[$bar->nourut]['sd']+=($arrRwPbrk[$ii]*$byOverHead2[$ii]);
                }
            }
             $sOverHead2="select noakun,sum(jumlah) as overhead,substr(tanggal,6,2) as bln,noakun  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."'  and noakun like '7%' and left(noakun,5)!='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL','TRAKSI') and induk='".$pt."')  and noakun!='7199999' 
                        group by noakun,left(tanggal,7)";
            $qOverHead2=$owlPDO->query($sOverHead2) or die(print " Gagal: ".PDOException::getMessage());
            $qOverHead2->setFetchMode(PDO::FETCH_ASSOC);
            while($rOverHead2=$qOverHead2->fetch())
            {                 
                $byOverHead3[$rOverHead2['noakun']][$rOverHead2['bln']]=$rOverHead2['overhead'];
                $dtNoakun[$rOverHead2['noakun']]=$rOverHead2['noakun'];
            }
            foreach($dtNoakun as $lstNoakun){
                if($unit==''){
                    for ($i = 1; $i <= $bulan; $i++) {
                    if($i<10)$ii='0'.$i; else $ii=$i;
                        @$byOverHeadDt[$lstNoakun][$ii]=$byOverHead3[$lstNoakun][$ii]/$rowUnit[$ii];
                        $lstData[$bar->nourut.$lstNoakun][$ii]=$byOverHeadDt[$lstNoakun][$ii]*$arrRwPbrk[$ii];
                        $lstData[$bar->nourut.$lstNoakun]['sd']+=$byOverHeadDt[$lstNoakun][$ii]*$arrRwPbrk[$ii];
                    }
                }
                $daftar[$lstNoakun]=$lstNoakun;
            }
                        if($unit==''){
                                $where="";
                                $where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')  and     left(noakun,5)!='71502'";
                        }else{
                                $whr="kodeorganisasi='".$unit."'";
                                $optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
                                if($optTipe[$unit]!='PABRIK'){
                                        $where="";
                                }else{
                                        $where=" ='".$unit."' and  left(noakun,5)!='71502'";
                                }
                        } 
                break;
                case'200005':#depresiasi kebun+traksi
                $dtNoakun=array();
                $byOverHead2=array();
                $byOverHead3=array();
                $byOverHead=array();
                $byOverHeadDt=array();
                $sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
                    where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
                $qUnitKbn=$owlPDO->query($sUnitKbn) or die(print " Gagal: ".PDOException::getMessage());
                $qUnitKbn->setFetchMode(PDO::FETCH_ASSOC);
                while($rowUnitKbn=$qUnitKbn->fetch())
                { 
                    $arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
                 }

            #total unit pabrik
            $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
                      where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
            $qUnitPbrk=$owlPDO->query($sUnitPbrk) or die(print " Gagal: ".PDOException::getMessage());
            $qUnitPbrk->setFetchMode(PDO::FETCH_ASSOC);
            while($rowUnitPbrk=$qUnitPbrk->fetch())
            { 
                $arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
            }
            for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
                $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
                }
            #akun tanaman tidak disebar merata hanya ke kebun
            $akunTnmn="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='TKAKUN'";
            $rakunTnmn=fetchdata($akunTnmn);
            
            #over head ro dan ho
            $sOverHead="select sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."'  and left(noakun,5)='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('KANWIL') and induk='".$pt."') 
                        and noakun not in (".$rakunTnmn[0]['nilai'].") group by left(tanggal,7)";
            $qOverHead=$owlPDO->query($sOverHead) or die(print " Gagal: ".PDOException::getMessage());
            $qOverHead->setFetchMode(PDO::FETCH_ASSOC);
            while($rOverHead=$qOverHead->fetch()){  
                $byOverHead[$rOverHead['bln']]=$rOverHead['overhead'];
            }
            
            for ($i = 1; $i <= $bulan; $i++) {
                if($unit==''){
                    if($i<10)$ii='0'.$i; else $ii=$i;
                    @$byOverHead2[$ii]=$byOverHead[$ii]/$rowUnit[$ii];
                    $dzArr[$bar->nourut][$ii]=($arrRwKbn[$ii]*$byOverHead2[$ii]);
                    $dzArr[$bar->nourut]['sd']+=($arrRwKbn[$ii]*$byOverHead2[$ii]);
                }
            }
            $sOverHead2="select noakun,sum(jumlah) as overhead,substr(tanggal,6,2) as bln,noakun  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."'  and left(noakun,5)='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('KANWIL','TRAKSI') and induk='".$pt."') 
                        group by noakun,left(tanggal,7)";
            $qOverHead2=$owlPDO->query($sOverHead2) or die(print " Gagal: ".PDOException::getMessage());
            $qOverHead2->setFetchMode(PDO::FETCH_ASSOC);
            while($rOverHead2=$qOverHead2->fetch())
            {                 
                $byOverHeadDt[$rOverHead2['noakun']][$rOverHead2['bln']]=$rOverHead2['overhead'];
                $dtNoakun[$rOverHead2['noakun']]=$rOverHead2['noakun'];
            }
            foreach($dtNoakun as $lstNoakun){
                if($unit==''){
                    for ($i = 1; $i <= $bulan; $i++) {
                    if($i<10)$ii='0'.$i; else $ii=$i;
                        @$byOverHeadDt[$lstNoakun][$ii]=$byOverHead3[$lstNoakun][$ii]/$rowUnit[$ii];
                        $lstData[$bar->nourut.$lstNoakun][$ii]=$byOverHeadDt[$lstNoakun][$ii]*$arrRwKbn[$ii];
                        $lstData[$bar->nourut.$lstNoakun]['sd']+=$byOverHeadDt[$lstNoakun][$ii]*$arrRwKbn[$ii];
                    }
                }
                $daftar[$lstNoakun]=$lstNoakun;
            }
            #depresiasi tanaman
            $sOverHead2="select sum(jumlah) as overhead,substr(tanggal,6,2) as bln,noakun  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL') and induk='".$pt."')  
                        and noakun in (".$rakunTnmn[0]['nilai'].") group by left(tanggal,7),noakun";
            $qOverHead2=$owlPDO->query($sOverHead2) or die(print " Gagal: ".PDOException::getMessage());
            $qOverHead2->setFetchMode(PDO::FETCH_ASSOC);
            while($rOverHead2=$qOverHead2->fetch()){ 
                $daftar[$rOverHead2['noakun']]=$rOverHead2['noakun'];
                $lstData[$bar->nourut.$rOverHead2['noakun']][$rOverHead2['bln']]=$rOverHead2['overhead'];
                $lstData[$bar->nourut.$rOverHead2['noakun']]['sd']+=$rOverHead2['overhead'];
                $dzArr[$bar->nourut][$rOverHead2['bln']]=$rOverHead2['overhead'];
                $dzArr[$bar->nourut]['sd']+=$rOverHead2['overhead'];
            }
                        if($unit==''){
                                $where="";
                                $where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe in ('KEBUN'))";
                        }else{
                                $whr="kodeorganisasi='".$unit."'";
                                $optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
                                if(($optTipe[$unit]!='KEBUN')||($optTipe[$unit]!='TRAKSI')){
                                        $where="";
                                }else{
                                        $where=" ='".$unit."'";
                                }
                        }
                break;
                case'300004':#depresiasi PABRIK
                $dtNoakun=array();
                $byOverHead2=array();
                $byOverHead3=array();
                $byOverHead=array();
                $byOverHeadDt=array();
                $sUnitKbn="select count(distinct unit) as jmlKbn,substr(tanggal,6,2) as bln  from ".$dbname.".kebun_prestasi_vw 
              where left(tanggal,4)='".$tahun."'  and unit in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$pt."')  group by left(tanggal,7) order by unit asc";
                $qUnitKbn=$owlPDO->query($sUnitKbn) or die(print " Gagal: ".PDOException::getMessage());
                $qUnitKbn->setFetchMode(PDO::FETCH_ASSOC);
                while($rowUnitKbn=$qUnitKbn->fetch())
                { 
                     $arrRwKbn[$rowUnitKbn['bln']]=$rowUnitKbn['jmlKbn'];
                 }
               

            #total unit pabrik
            $sUnitPbrk="select count(distinct kodeorg) as jmlPbrk,substr(tanggal,6,2) as bln  from ".$dbname.".pabrik_pengolahan 
                      where  left(tanggal,4)='".$tahun."' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where  tipe='PABRIK' and induk='".$pt."')  group by left(tanggal,7) order by kodeorg asc";
           $qUnitPbrk=$owlPDO->query($sUnitPbrk) or die(print " Gagal: ".PDOException::getMessage());
            $qUnitPbrk->setFetchMode(PDO::FETCH_ASSOC);
            while($rowUnitPbrk=$qUnitPbrk->fetch())
            {  
                $arrRwPbrk[$rowUnitPbrk['bln']]=$rowUnitPbrk['jmlPbrk'];
            }
            for ($i = 1; $i <= $bulan; $i++) {
            if($i<10)$ii='0'.$i; else $ii=$i;
                $rowUnit[$ii]=$arrRwKbn[$ii]+$arrRwPbrk[$ii];
                }

            #akun tanaman tidak disebar merata hanya ke kebun
            $akunTnmn="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='TKAKUN'";
            $rakunTnmn=fetchdata($akunTnmn);
            
            #over head ro dan ho
            $sOverHead="select sum(jumlah) as overhead,substr(tanggal,6,2) as bln  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."' and noakun not in (".$rakunTnmn[0]['nilai'].") and left(noakun,5)='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('KANWIL','TRAKSI') and induk='".$pt."') 
                        group by left(tanggal,7)";
            $qOverHead=$owlPDO->query($sOverHead) or die(print " Gagal: ".PDOException::getMessage());
            $qOverHead->setFetchMode(PDO::FETCH_ASSOC);
            while($rOverHead=$qOverHead->fetch())
            { 
                $byOverHead[$rOverHead['bln']]=$rOverHead['overhead'];
            }
            for ($i = 1; $i <= $bulan; $i++) {
                if($unit==''){
                    if($i<10)$ii='0'.$i; else $ii=$i;
                    @$byOverHead2[$ii]=$byOverHead[$ii]/$rowUnit[$ii];
                    $dzArr[$bar->nourut][$ii]=($arrRwPbrk[$ii]*$byOverHead2[$ii]);
                    $dzArr[$bar->nourut]['sd']+=($arrRwPbrk[$ii]*$byOverHead2[$ii]);	
                }
            }
             $sOverHead2="select noakun,sum(jumlah) as overhead,substr(tanggal,6,2) as bln,noakun  from ".$dbname.".keu_jurnaldt 
                        where left(tanggal,4)='".$tahun."'  and noakun not in (".$rakunTnmn[0]['nilai'].") and left(noakun,5)='71502' and kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe in ('KANWIL','TRAKSI') and induk='".$pt."') 
                        group by noakun,left(tanggal,7)";
            $qOverHead2=$owlPDO->query($sOverHead2) or die(print " Gagal: ".PDOException::getMessage());
            $qOverHead2->setFetchMode(PDO::FETCH_ASSOC);
            while($rOverHead2=$qOverHead2->fetch())
            {                 
                $byOverHead3[$rOverHead2['noakun']][$rOverHead2['bln']]=$rOverHead2['overhead'];
                $dtNoakun[$rOverHead2['noakun']]=$rOverHead2['noakun'];
            }
            foreach($dtNoakun as $lstNoakun){
                if($unit==''){
                    for ($i = 1; $i <= $bulan; $i++) {
                    if($i<10)$ii='0'.$i; else $ii=$i;
                        @$byOverHeadDt[$lstNoakun][$ii]=$byOverHead3[$lstNoakun][$ii]/$rowUnit[$ii];
                        $lstData[$bar->nourut.$lstNoakun][$ii]=$byOverHeadDt[$lstNoakun][$ii]*$arrRwPbrk[$ii];
                        $lstData[$bar->nourut.$lstNoakun]['sd']+=$byOverHeadDt[$lstNoakun][$ii]*$arrRwPbrk[$ii];
                    }
                }
                $daftar[$lstNoakun]=$lstNoakun;
            }
                        if($unit==''){
                                $where="";
                                $where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')";
                        }else{
                                $whr="kodeorganisasi='".$unit."'";
                                $optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
                                if($optTipe[$unit]!='PABRIK'){
                                        $where="";
                                }else{
                                        $where=" ='".$unit."'";
                                }
                        }
                break;
                case'200013':
                case'200011':
                        $nomakun=$bar->noakundisplay;
                        $noakunsql=" noakun in (".$nomakun.")";
                        if($unit==''){
                                $where="";
                                $where=" in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' ) ";
                        }else{
                                $whr="kodeorganisasi='".$unit."'";
                                $optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',$whr);
                                        $where=" ='".$unit."' ";
                        } 

                break;

                default:
                        if($unit=='')$where=" in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
                        else $where=" ='".$unit."'";
                break;
        }

     $semuakolomdb='debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';
     $st12="select noakun,(".$semuakolomdb.") as jumlah, awal01 as awal,awal02, awal03, awal04, awal05, awal06, awal07, awal08, awal09, awal10, awal11, awal12,substr(periode,5,2) as bulan
            from ".$dbname.".keu_saldobulanan where ".$noakunsql." and periode like'".$tahun."%' and kodeorg ".$where."  order by noakun,periode ";
    $res1=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while($ba12=$res1->fetch())
    {
                $daftar[$ba12->noakun]=$ba12->noakun;
                if($bar->nourut<100006){
                        $ba12->jumlah=abs($ba12->jumlah);
                        $ba12->awal=abs($ba12->awal);
                }


                                        if(!isset($lstData[$bar->nourut.$ba12->noakun][$ba12->bulan])) $lstData[$bar->nourut.$ba12->noakun][$ba12->bulan]=0;
                                        $lstData[$bar->nourut.$ba12->noakun][$ba12->bulan]+=($ba12->jumlah+$ba12->awal);

                                        //$emaknya[$ba12->noakun]=$bar->nourut;
                                        if(!isset($dzArr[$bar->nourut][$ba12->bulan])) $dzArr[$bar->nourut][$ba12->bulan]=0;
                                        //if(!isset($dzArr2[$ba12->noakun][$ba12->bulan])) $dzArr2[$ba12->noakun][$ba12->bulan]=0;
                                        $dzArr[$bar->nourut][$ba12->bulan]+=($ba12->jumlah+$ba12->awal);
                                        //$dzArr2[$ba12->noakun][$ba12->bulan]+=$ba12->jumlah;
                                        if(!isset($dzArr[$bar->nourut]['sd'])) $dzArr[$bar->nourut]['sd']=0;
                                        if(!isset($lstData[$bar->nourut.$ba12->noakun]['sd'])) $dzArr2[$ba12->noakun]['sd']=0;
                                        if(intval($bulan)>=intval($ba12->bulan)){
                                        $dzArr[$bar->nourut]['sd']+=($ba12->jumlah+$ba12->awal);
                                        $lstData[$bar->nourut.$ba12->noakun]['sd']+=($ba12->jumlah+$ba12->awal);
                                        //$dzArr2[$ba12->noakun]['sd']+=$ba12->jumlah+$ba12->awal;
                                        }

    }
        #NGUMPULIN DATA FISIK CPO,KERNEL DAN TBS AWAL#
        if($bar->nourut==200008){//TBS
                        $sData="select sum(kgwb) as jmlhKg,kodeorg,substr(tanggal,6,2) as bln from ".$dbname.".kebun_spb_vw where 
                                        kodeorg ".$where." and tanggal like '".substr($periode,0,4)."%' and intiplasma='I' and posting=1
                                        group by left(tanggal,7),kodeorg";
                        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
                        $qData->setFetchMode(PDO::FETCH_ASSOC);
                        while($rData=$qData->fetch())
                        {   
                                $dzArr[$bar->nourut][$rData['bln']]+=$rData['jmlhKg'];
                                if(intval($rData['bln'])<=intval(substr($periode,5,2))){
                                        $dzArr[$bar->nourut]['sd']+=$rData['jmlhKg'];	
                                }
                        }
        }
        #NGUMPULIN DATA FISIK AKHIR#	
       // if(!empty($daftar))sort($daftar);
}


$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
$stream ="Laporan Keuangan - Laba Rugi<br>";
$stream.="".$unit." - ".$nmorg[$unit]."<br>";
$stream.="Periode ".$periode."<br><br>";
$stream.="<table class=sortable border=1 cellspacing=0>
    <thead>
        <tr class=rowheader>
            <td align=center colspan=3 rowspan=2>Description</td>
            ";
            for ($i = $bulan; $i >= 1; $i--) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                $stream.="<td align=center rowspan=2>".$kolom[$ii]."</td>";    
            }
            $stream.="<td align=center rowspan=2>".$kolom['sd']."</td>
                <td align=center colspan=2>Increase/Decrease</td>    
        </tr>
        <tr class=rowheader>
            <td align=center>Rupiah</td>
            <td align=center>%</td>
        </tr>
    </thead><tbody>";

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
            $sbDt[$data['nourut']][$ii]+=isset($data[$ii])? $data[$ii]: 0;
        }
        $subtotal['sd'] += isset($data['sd'])? $data['sd']: 0;
        $totallagi=0; 
    }
        if($totallagi==0){
                $tipeTotal='standar';		
        }
    if($data['tipe']=='Total'){
                #DATA BERDASARKAN TIPE TOTAL
                switch($tipeTotal){
                        case'standar':
                                for ($i = 1; $i <= $bulan; $i++) {
                if($i<10)$ii='0'.$i; else $ii=$i;
                                //if(!isset($dzArr[$data['nourut']][$ii])) $dzArr[$data['nourut']][$ii]=0;
                $dzArr[$data['nourut']][$ii] += isset($subtotal[$ii])? $subtotal[$ii]: 0;
                $subtotal[$ii]=0;            
                                }
                                //if(!isset($dzArr[$data['nourut']]['sd'])) $dzArr[$data['nourut']]['sd']=0;
                                $dzArr[$data['nourut']]['sd'] += isset($subtotal['sd'])? $subtotal['sd']: 0;
                        break;
                }
                #DATA PER NOURUT
                $subtotal['sd']=0;
        $totallagi=1;       
                $rangedt=explode(",",$isiRange[$data['nourut']]);
                switch($data['nourut']){
                        case '200014':
                        #TOTALAN BERDASARKAN RANGE NO URUT 
                        $sdt=$rangedt[1]-$rangedt[0];
                        $totAwl=$sdt;
                        $sdt=$sdt+1;//tambah satu untuk ambil biaya sub total kebun
                                for($asa=$sdt;$asa>0;$asa--){
                                                if($asa==$sdt){
                                                        $ada=200006;//no urut sub total kebun
                                                }else if($asa==$totAwl){
                                                        $ada=$rangedt[0];
                                                        $datanya.=$ada;
                                                }else{
                                                        if($rangedt[1]!=$ada){
                                                                $ada+=1;
                                                                $datanya.=",".$ada;
                                                        }
                                                }
                                        for ($i = 1; $i <= $bulan; $i++){
                                                if($i<10)$ii='0'.$i; else $ii=$i;
                                                $dzArr[$data['nourut']][$ii]+=isset($dzArr[$ada][$ii])? $dzArr[$ada][$ii]: 0;
                                                $angkanya.=$dzArr[$ada][$ii]."__".$ii."__".$ada."\n";
                                                //if(!isset($dzArr[$data['nourut']]['sd'])){ $dzArr[$data['nourut']]['sd']=0;}else{

                                                //}
                                        }
                                        $dzArr[$data['nourut']]['sd']+=$dzArr[$ada]['sd'];
                                }
                        break;
                        case '400001':
                        #100004=Penjualan,(300007,300011,300012)=total biaya produksi,(300014,300015,300016)=saldo awal,(300018,300019,300020)=saldo akhir
                        #PENJUALAN-(TOTAL BIAYA PRODUKSI+SALDO AWAL+PEMBELIAN CPOKER-SALDO AKHIR)*0.9
                                for ($i = 1; $i <= $bulan; $i++) {
                                                if($i<10)$ii='0'.$i; else $ii=$i;
                                                @$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]-($dzArr[$rangedt[1]][$ii]+$dzArr[$rangedt[2]][$ii]+$dzArr[$rangedt[3]][$ii]+$dzArr[$rangedt[4]][$ii]+$dzArr[$rangedt[5]][$ii]+$dzArr[$rangedt[6]][$ii]-$dzArr[$rangedt[7]][$ii]-$dzArr[$rangedt[8]][$ii]-$dzArr[$rangedt[9]][$ii]))*0.9;
                                }
                                //exit("warning:".$dzArr[$rangedt[0]][$ii]."___".$dzArr[$rangedt[1]][$ii]."___".$dzArr[$rangedt[2]][$ii]."___".$dzArr[$rangedt[3]][$ii]."___".$dzArr[$rangedt[4]][$ii]."___".$dzArr[$rangedt[5]][$ii]."___".$dzArr[$rangedt[6]][$ii]."___".$dzArr[$rangedt[7]][$ii]."___".$dzArr[$rangedt[8]][$ii]);
                            @$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']-($dzArr[$rangedt[1]]['sd']+$dzArr[$rangedt[2]]['sd']+$dzArr[$rangedt[3]]['sd']+$dzArr[$rangedt[4]]['sd']+$dzArr[$rangedt[5]]['sd']+$dzArr[$rangedt[6]]['sd']-$dzArr[$rangedt[7]]['sd']-$dzArr[$rangedt[8]]['sd']-$dzArr[$rangedt[9]]['sd']))*0.9;
                                //$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']-$dzArr[$rangedt[1]]['sd']);
                        break;
                        case '400002':
                        #100004=Penjualan,(300007,300011,300012)=total biaya produksi,(300014,300015,300016)=saldo awal,(300018,300019,300020)=saldo akhir
                        #PENJUALAN-(TOTAL BIAYA PRODUKSI+SALDO AWAL+PEMBELIAN CPOKER-SALDO AKHIR)*0.1
                                for ($i = 1; $i <= $bulan; $i++) {
                                                if($i<10)$ii='0'.$i; else $ii=$i;
                                                @$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]-($dzArr[$rangedt[1]][$ii]+$dzArr[$rangedt[2]][$ii]+$dzArr[$rangedt[3]][$ii]+$dzArr[$rangedt[4]][$ii]+$dzArr[$rangedt[5]][$ii]+$dzArr[$rangedt[6]][$ii]-$dzArr[$rangedt[7]][$ii]-$dzArr[$rangedt[8]][$ii]-$dzArr[$rangedt[9]][$ii]))*0.1;
                                }
                                //exit("warning:".$dzArr[$rangedt[0]][$ii]."___".$dzArr[$rangedt[1]][$ii]."___".$dzArr[$rangedt[2]][$ii]."___".$dzArr[$rangedt[3]][$ii]."___".$dzArr[$rangedt[4]][$ii]."___".$dzArr[$rangedt[5]][$ii]."___".$dzArr[$rangedt[6]][$ii]."___".$dzArr[$rangedt[7]][$ii]."___".$dzArr[$rangedt[8]][$ii]);
                            @$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']-($dzArr[$rangedt[1]]['sd']+$dzArr[$rangedt[2]]['sd']+$dzArr[$rangedt[3]]['sd']+$dzArr[$rangedt[4]]['sd']+$dzArr[$rangedt[5]]['sd']+$dzArr[$rangedt[6]]['sd']-$dzArr[$rangedt[7]]['sd']-$dzArr[$rangedt[8]]['sd']-$dzArr[$rangedt[9]]['sd']))*0.1;
                                //$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']-$dzArr[$rangedt[1]]['sd']);
                        break;
                        case'200009':
                                for ($i = 1; $i <= $bulan; $i++) {
                                                if($i<10)$ii='0'.$i; else $ii=$i;
                                                @$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]/$dzArr[$rangedt[1]][$ii]);
                                }
                                @$dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']/$dzArr[$rangedt[1]]['sd']);
                        break;
                        case'300007':
                                for ($i = 1; $i <= $bulan; $i++) {
                                                if($i<10)$ii='0'.$i; else $ii=$i;
                                                @$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]+$dzArr[$rangedt[1]][$ii]);
                                }
                                $dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']+$dzArr[$rangedt[1]]['sd']);
                        break;

                        case'300014':
                                for($i = 1; $i <= $bulan; $i++){
                                        if($i<10)$ii='0'.$i; else $ii=$i;
                                        $sAwl="select sum(awal".$ii.") as awal from ".$dbname.".keu_saldobulanan where noakun='1150201' and left(periode,4)='".$tahun."' and kodeorg ".$where."";
                                        $qData=$owlPDO->query($sAwl) or die(print " Gagal: ".PDOException::getMessage());
                                        $qData->setFetchMode(PDO::FETCH_ASSOC);              
                                        $rAwl=$qData->fetch();
                                        $dzArr[$data['nourut']][$ii]=$rAwl['awal'];
                                        if($ii=='01'){
                                                if($rAwl['awal']!=''){
                                                $dzArr[$data['nourut']]['sd']=$rAwl['awal'];
                                                }
                                        }
                                }
                        break;
                        case'300015':
                                for($i = 1; $i <= $bulan; $i++){
                                        if($i<10)$ii='0'.$i; else $ii=$i;
                                        $sAwl="select sum(awal".$ii.") as awal from ".$dbname.".keu_saldobulanan where noakun='1150202' and left(periode,4)='".$tahun."' and kodeorg ".$where."";
                                        $qData=$owlPDO->query($sAwl) or die(print " Gagal: ".PDOException::getMessage());
                                        $qData->setFetchMode(PDO::FETCH_ASSOC);              
                                        $rAwl=$qData->fetch();
                                        $dzArr[$data['nourut']][$ii]=$rAwl['awal'];
                                        if($ii=='01'){
                                                if($rAwl['awal']!=''){
                                                $dzArr[$data['nourut']]['sd']=$rAwl['awal'];
                                                }
                                        }
                                }
                        break;
                        case'300016':
                                for($i = 1; $i <= $bulan; $i++){
                                        if($i<10)$ii='0'.$i; else $ii=$i;
                                        $sAwl="select sum(awal".$ii.") as awal from ".$dbname.".keu_saldobulanan where noakun='".$isiRange[$data['nourut']]."' and periode='".$tahun."".$ii."' and kodeorg ".$where."";
                                        $qData=$owlPDO->query($sAwl) or die(print " Gagal: ".PDOException::getMessage());
                                        $qData->setFetchMode(PDO::FETCH_ASSOC);              
                                        $rAwl=$qData->fetch();
                                        if($ii=='01'){
                                                if($rAwl['awal']!=''){
                                                        $dzArr[$data['nourut']]['sd']=$rAwl['awal'];
                                                }
                                        }
                                }
                        break;
                        case'300018':
                        case'300019':
                        case'300020':
                                for($i = 1; $i <= $bulan; $i++){
                                        if($i<10)$ii='0'.$i; else $ii=$i;
                                        $sAwl="select sum(awal".$ii.") as awal,sum(debet".$ii."-kredit".$ii.") as mutsi from ".$dbname.".keu_saldobulanan where noakun='".$isiRange[$data['nourut']]."' and periode='".$tahun."".$ii."' and kodeorg ".$where." ";
                                        $qData=$owlPDO->query($sAwl) or die(print " Gagal: ".PDOException::getMessage());
                                        $qData->setFetchMode(PDO::FETCH_ASSOC);              
                                        $rAwl=$qData->fetch();
                                        $dzArr[$data['nourut']][$ii]=$rAwl['awal']+$rAwl['mutsi'];
                                        $dzArr[$data['nourut']]['sd']=$rAwl['awal']+$rAwl['mutsi'];	
                                }
                        break;
                        case'400008':
                                for ($i = 1; $i <= $bulan; $i++) {
                                                if($i<10)$ii='0'.$i; else $ii=$i;
                                                $dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]+$dzArr[$rangedt[1]][$ii])-$dzArr[$rangedt[2]][$ii];
                                                $dzArr[$data['nourut']]['sd']+=($dzArr[$rangedt[0]][$ii]+$dzArr[$rangedt[1]][$ii])-$dzArr[$rangedt[2]][$ii];
                                }
                        break;
                        case'400010':
                        #400008,400009
                                for ($i = 1; $i <= $bulan; $i++) {
                                                if($i<10)$ii='0'.$i; else $ii=$i;
                                                $dzArr[$data['nourut']][$ii]=$dzArr[$rangedt[0]][$ii]-$dzArr[$rangedt[1]][$ii];
                                }
                                $dzArr[$data['nourut']]['sd']=($dzArr[$rangedt[0]]['sd']-$dzArr[$rangedt[1]]['sd']);
                        break;
						case'100006':
							$dzArr[$data['nourut']]['sd'] = 0;
							for ($i = 1; $i <= $bulan; $i++) {
									if($i<10)$ii='0'.$i; else $ii=$i;
									$dzArr[$data['nourut']][$ii]=($dzArr[$rangedt[0]][$ii]+$dzArr[$rangedt[1]][$ii]+$dzArr[$rangedt[2]][$ii]+$dzArr[$rangedt[3]][$ii])-$dzArr[$rangedt[4]][$ii];
									$dzArr[$data['nourut']]['sd']+=($dzArr[$rangedt[0]][$ii]+$dzArr[$rangedt[1]][$ii]+$dzArr[$rangedt[2]][$ii]+$dzArr[$rangedt[3]][$ii])-$dzArr[$rangedt[4]][$ii];
							}
						break;						
                }

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
        //if(!empty($daftar))
        array_multisort($daftar,SORT_ASC);
                foreach($daftar as $akunnya){
            //if($emaknya[$akunnya]==$data['nourut']){lstData
                        if(count($lstData[$data['nourut'].$akunnya])!=0){
                                @$dataPER=($lstData[$data['nourut'].$akunnya][$bulan]-$lstData[$data['nourut'].$akunnya][$bulanlalu])/$lstData[$data['nourut'].$akunnya][$bulanlalu]*100;
                                $stream.="
                                <tr class=rowcontent>
                                        <td></td>
                                        <td>".$akunnya."</td>
                                        <td>".$akun[$akunnya]."</td>
                                        ";
                                        for ($i = $bulan; $i >= 1; $i--) {
                                                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;  
                                                $stream.="<td align=right>".number_format(isset($lstData[$data['nourut'].$akunnya][$ii])? $lstData[$data['nourut'].$akunnya][$ii]: 0,2)."</td>";
                                        }
                                        if(!isset($lstData[$data['nourut'].$akunnya][$bulan])) $lstData[$data['nourut'].$akunnya][$bulan]=0;
                                        if(!isset($lstData[$data['nourut'].$akunnya][$bulanlalu])) $lstData[$data['nourut'].$akunnya][$bulanlalu]=0;
                                        $stream.="<td align=right>".number_format($lstData[$data['nourut'].$akunnya]['sd'],2)."</td><td align=right>".number_format($lstData[$data['nourut'].$akunnya][$bulan]-$lstData[$data['nourut'].$akunnya][$bulanlalu],2)."</td>    
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