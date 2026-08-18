<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
#==========================================konfigurasi database
# M0	Perawatan Kebun
# M1	Biaya Panen

#============================================konfigurasi database

#==Komfigurasi komponen gaji
# 1	Gaji Pokok
# 2	Tunjangan Jabatan
# 14	Rapel
# 16	Premi Pengawasan
# 21	Klaim Pengobatan
# 26	Bonus
# 27	Tunjangan Fasilitas
# 28	THR
# 30	Tunjangan Profesi
# 31	Tunjangan Masa Kerja
# 32	Premi
# 33	Lembur
# 34	Penalti
#
#cek proses gaji tidak langsung sudah diproses atau belum
$param = $_POST;
$kodeorg  = $param['kodeorg'];
$dataorg = array();
$dtstr="select * from ".$dbname.".organisasi where  kodeorganisasi = '".$kodeorg."'";
$str=$owlPDO->query($dtstr);
$str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
  $dataorg[$bar->kodeorganisasi] = $bar;
 }

if(@$dataorg[$kodeorg]->tipe!="HOLDING"){
  $varKdjrn="KBNB";
  $sCek="select * from ".$dbname.".keu_jurnalht where 
         nojurnal like '%KBNB%' and tanggal like '".$param['periode']."%' and nojurnal like '%".$kodeorg."%'";
  $rCek=fetchdata($sCek);
  /*if(count($rCek)==0){
    exit('warning: Jalankan Proses Gaji Karyawan Tidak Langsung');
  }*/
}


$tahunbulan = implode("",explode('-',$param['periode']));
#ambil periode akuntansi
$str="select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji 
    where kodeorg='".$kodeorg."'
    and periode='".$param['periode']."'";

$tgmulai='';
$tgsampai='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $tgsampai   = $bar->tanggalsampai;
    $tgmulai    = $bar->tanggalmulai;
}
if($tgmulai=='' || $tgsampai=='')
    exit("Error: Accounting period is not registered");

/**
 * Validasi apakah proses gaji telah ditutup
 */
$qGaji = selectQuery($dbname,'sdm_5periodegaji','sudahproses,jenisgaji', "kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
$resGaji = fetchData($qGaji);
$optGaji = array();
foreach($resGaji as $row) {
        $optGaji[$row['jenisgaji']] = $row['sudahproses'];
}

// 1. Validasi Empty
if(empty($optGaji)) exit('Warning: Periode Gaji '.$param['periode']." belum ada");
if(!isset($optGaji['H'])) exit('Warning: Periode Gaji Harian '.$param['periode']." belum ada");
if(!isset($optGaji['B'])) exit('Warning: Periode Gaji Bulanan '.$param['periode']." belum ada");

// 2. Validasi Proses Gaji
if($optGaji['H']==0) exit('Warning: Proses Gaji Harian '.$param['periode']." belum dilakukan");
if($optGaji['B']==0) exit('Warning: Proses Gaji Bulanan '.$param['periode']." belum dilakukan");

#---------------------------------------------------------------
#ambil potongan HK
#---------------------------------------------------------------
 $str="select sum(jumlah) as jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
       where kodeorg like '".$kodeorg."%' 
       and idkomponen in(37,41) and periodegaji='".$param['periode']."' group by idkomponen,karyawanid";
 $potx=Array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($barx=$res->fetch())
{
     $potx[$barx->karyawanid]=$barx->jumlah;
 }
#---------------------------------------------------------------
#ambil kontanan sudah dibayar
#---------------------------------------------------------------
 $str="select sum(jumlah) as jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
       where kodeorg like '".$kodeorg."%' 
       and idkomponen in(43,48) and periodegaji='".$param['periode']."' group by idkomponen,karyawanid";
 $potkon=Array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($barx=$res->fetch())
{
     $potkon[$barx->karyawanid]=$barx->jumlah;
 }
 

	$str="select * from ".$dbname.".kebun_aktifitas where  
		 kodeorg='".$kodeorg."' and tanggal between '".$tgmulai."' 
        and '".$tgsampai."' and jurnal=1";
	$res=fetchdata($str);
	foreach($res as $bar){
		$pejabatbkm[$bar['nikmandor']]=$bar['nikmandor'];
		$pejabatbkm[$bar['nikmandor1']]=$bar['nikmandor1'];
		$pejabatbkm[$bar['nikasisten']]=$bar['nikasisten'];
		$pejabatbkm[$bar['keranimuat']]=$bar['keranimuat'];
	}
 
 
#---------------------------------------------------------------
#ambil semua gaji per karyawan
#---------------------------------------------------------------
#1. Ambil gaji total per karyawan yang plus pada unit bersangkutan
 $str="select sum(jumlah) as jumlah,karyawanid from ".$dbname.".sdm_gajidetail_vw 
       where kodeorg like '".$kodeorg."%' 
       and plus=1 and periodegaji='".$param['periode']."' group by karyawanid";
 $gaji=Array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        if(!isset($potx[$bar->karyawanid])) $potx[$bar->karyawanid]=0;
        if(!isset($potkon[$bar->karyawanid])) $potkon[$bar->karyawanid]=0;
    $gaji[$bar->karyawanid]=$bar->jumlah-$potx[$bar->karyawanid]-$potkon[$bar->karyawanid];//kurangi potongan hk
	#= jika dia terdaftar di pejabatbkm maka unset, karna sudah dialokasi dari proses tidak langsung ke pengawasan
	if(in_array($bar->karyawanid,$pejabatbkm)){
		unset($gaji[$bar->karyawanid]);
	}
 }
 
 #2 Ambil subunit setiap karyawan
 $str="select subbagian,karyawanid,namakaryawan,kodejabatan from ".$dbname.".datakaryawan 
       where lokasitugas='".$kodeorg."'";
 $subunit=Array();
 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
     $subunit[$bar->karyawanid]=$bar->subbagian;
     $namakaryawan[$bar->karyawanid]=$bar->namakaryawan;
     $kodejab[$bar->karyawanid]=$bar->kodejabatan;

 }
 $namaJabt=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
 
 #3 ambil semua organisasi yang traksi atau workshop
 $str="select distinct kodeorganisasi,tipe from ".$dbname.".organisasi 
       where kodeorganisasi like '".$kodeorg."%'";
 $tipe=Array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
     $tipe[$bar->kodeorganisasi]=$bar->tipe;

 } 

  #==========================================================================================  
  #ambil daftar karyawan yang masuk dalam perawatan dan panen
  $str="select karyawanid,(sum(umr)+sum(insentif)) as upah from ".$dbname.".kebun_kehadiran_vw
        where unit='".$kodeorg."' and tanggal between '".$tgmulai."' 
        and '".$tgsampai."' group by karyawanid";
$gjPerawatan=Array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
      $gjPerawatan[$bar->karyawanid]=$bar->upah;
  }
  #===================panen

  $str="select tanggal,karyawanid,((sum(upahkerja)+sum(upahpremi)+sum(premibasis)+sum(upahpremilebihbasis))-(sum(upahpenalty)+sum(rupiahpenalty))) as upah from ".$dbname.".kebun_prestasi_vw
        where unit='".$kodeorg."' and tanggal between '".$tgmulai."' 
        and '".$tgsampai."' group by tanggal,karyawanid";
$gjPanen=Array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
				/*
                // cari hari
                $day = date('D', strtotime($bar->tanggal));
                if($day=='Sun')$libur=true; else $libur=false;
                // kamus hari libur
                $strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$bar->tanggal."'";
                $strorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
                $strorg->setFetchMode(PDO::FETCH_ASSOC);
                while($roworg=$strorg->fetch())
                {
//                $libur=true;
                if($roworg['keterangan']=='libur')$libur=true;
                if($roworg['keterangan']=='masuk')$libur=false;
                }        
        if($libur==false) {
						*/
                        if(!isset($gjPanen[$bar->karyawanid])) $gjPanen[$bar->karyawanid]=0;
                        $gjPanen[$bar->karyawanid]+=$bar->upah;
                /*
				}else{// kalo hari libur dianggap kontanan? (masuk ke pengurang)

                }*/
  }

  #===========pabrikasi
  // $sPabrikasi="select karyawanid,sum(umr+premi) as gaji from ".$dbname.".pabrikasi_absensidt a 
               // left join ".$dbname.".pabrikasi_absensiht b on a.notransaksi=b.notransaksi
               // where tanggal between '".$tgmulai."' and '".$tgsampai."' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'
               // and posting='1' group by a.karyawanid";
  // $qPabrikasi=fetchdata($sPabrikasi);
  // foreach($qPabrikasi as $rPabrikasi){
      // $gjPabrikasi[$rPabrikasi['karyawanid']]=$rPabrikasi['gaji'];
  // }
  #=================================================================
  #hapus karyawan tidaklangsung
  $masukkotak=array();
  $gaji1=$gaji;
  foreach($gaji as $karid=>$g){
        if(!isset($gjPanen[$karid])) $gjPanen[$karid]=0;
        if(!isset($gjPerawatan[$karid])) $gjPerawatan[$karid]=0;
        $gajiyangsudahdialokasi[$karid]=$gjPanen[$karid]+$gjPerawatan[$karid]+@$gjPabrikasi[$karid];
        if(($gajiyangsudahdialokasi[$karid]!=0) && ($g>$gajiyangsudahdialokasi[$karid])){       
                if(($g-$gajiyangsudahdialokasi[$karid])!=0){
                  $masukkotak[$karid]=$g-$gajiyangsudahdialokasi[$karid];
                }
                
        }
  }
  $zzz=$masukkotak;
  #bersihkan memory
  //unset($gaji);
  #=======================================================================================================  

 if(empty($masukkotak))
     exit('Error: Salaries has been allocated correctly');
 else {
             echo"<button class=mybutton onclick=prosesGajiLangsung(1) id=btnproses>Process</button>
                  <table class=sortable cellspacing=1 border=0>
                  <thead>
                    <tr class=rowheader>
                    <td>No</td>
                    <td>".$_SESSION['lang']['dari']."</td>
                    <td>".$_SESSION['lang']['sampai']."</td>
                    <td>".$_SESSION['lang']['namakaryawan']."</td>
                    <td>".$_SESSION['lang']['karyawanid']."</td>
                    <td>".$_SESSION['lang']['jabatan']."</td>
                    <td>".$_SESSION['lang']['subbagian']."</td>
                    <td>".$_SESSION['lang']['tipe']."</td>
                    <td>".$_SESSION['lang']['blmAlokasi']."</td>
                    <td>".$_SESSION['lang']['gaji']."</td>
                    <td>Allocated</td>
                    </tr>
                  </thead>
                  <tbody>";
             $no=$ttl=0;
            foreach($masukkotak as $key =>$baris){
              
              if(number_format($baris)!=0){
                 if ($baris<1) {
                                  $baris=0;
                              }   
              if ($baris>0) {
            // if($gaji1[$key]>$gajiyangsudahdialokasi[$key]){
                $no+=1;
                   echo"<tr class=rowcontent>
                      <td>".$no."</td>
                      <td>".$tgmulai."</td>
                      <td>".$tgsampai."</td>    
                      <td>".$namakaryawan[$key]."</td>
                      <td>".$key."</td>    
                      <td>".$namaJabt[$kodejab[$key]]."</td>    
                      <td>".$subunit[$key]."</td>
                      <td>".$tipe[$subunit[$key]]."</td>                        
                      <td align=right>".number_format($baris)."</td>
                      <td align=right>".number_format($gaji1[$key])."</td>
                      <td align=right>".number_format($gajiyangsudahdialokasi[$key])."</td>       
                      </tr>";
                      }
              else
              {
                $kar[$key]=$key;
              }
                   $ttl+=$baris;
             }
           }
            echo"<tr class=rowcontent id='row".$no."'>
                    <td colspan=8>Total</td>
                    <td align=right>".number_format($ttl)."</td>
                    <td></td>
                    <td></td>
                    </tr>";
             echo"</tbody><tfoot></tfoot></table>";
                  $s=0;
                  foreach($zzz as $karid=>$val)
                  {
                     if (count(@$kar[$karid])!=0) {
                      continue;
                    }
                      if($s==0)
                         $arrkarid="#".$karid."#";
                      else
                         $arrkarid.=",#".$karid."#"; 
                      $s++;
                  }
             echo "<input type=hidden id=karyawanid value=\"".$arrkarid."\">";
             echo "<input type=hidden id=jumlah value='".$ttl."'>";
             echo "<input type=hidden id=dari value='".$tgmulai."'>";
             echo "<input type=hidden id=sampai value='".$tgsampai."'>";
}
#----------------------------------------------------------------
?>