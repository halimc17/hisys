<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

#==========================================konfigurasi database
# KBNB0	Gaji BTL Kebun/Pabrik
# KBNB1	Premi/Lebur BTL Kebun/Pabrik
# KBNB2	Tunjangan Lain
# KBNB3	THR BTL
# KBNB4	Bonus BTL
# KBNB5	Pengobatan BTL
# VHCG0	Gaji Kendaraan/A.Berat
# VHCG1	Biaya Lebur Kendaraan/A.Berat
# VHCG2	Biaya Tunjangan Lain Kend./A.Berat
# VHCG3	THR Kend./A.Berat
# VHCG4	Bonus Kend. A.Berat
# VHCG5	Pengobatan Kend./A.Berat
# WSG0	Biaya Gaji Bengkel
# WSG1	Biaya Premi/Lembur Bengkel
# WSG2	Tunjangan Lain Bengkel
# WSG3	THR Traksi
# WSG4	Bonus Traksi
# WSG5	Pengobatan Traksi
# KBNL0	Biaya pengawasan BBT
# KBNL1	Biaya pengawasan TBM
# KBNL2	Biaya pengawasan TM
# KBNL3	Biaya Pengawasan Panen
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

$param = $_POST;
$tahunbulan = implode("",explode('-',$param['periode']));
#ambil periode akuntansi
$kodeorg  = $param['kodeorg'];
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
$qGaji = selectQuery($dbname,'sdm_5periodegaji','sudahproses,jenisgaji',"kodeorg='".$kodeorg."' and periode='".$param['periode']."'");
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
if($optGaji['H']==0) exit('Warning: Periode Gaji Harian '.$param['periode']." belum ditutup");
if($optGaji['B']==0) exit('Warning: Periode Gaji Bulanan '.$param['periode']." belum ditutup");

#---------------------------------------------------------------
#ambil potongan HK
#---------------------------------------------------------------
#1. check component : Potongan HK(idkomponen:37) di mapping potongan karyawan
 $str="select count(idkomponen) as counts from ".$dbname.".keu_5pengakuanpotongan where idkomponen=37";
 $qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
 $qry->setFetchMode(PDO::FETCH_OBJ);
 $res=$qry->fetch();
 $count37 = $res->counts;
 
 #2. check component : Denda dibawah jam kerja(idkomponen:41) di mapping potongan karyawan
 $str="select count(idkomponen) as counts from ".$dbname.".keu_5pengakuanpotongan where idkomponen=41";
 $qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
 $qry->setFetchMode(PDO::FETCH_OBJ);
 $res=$qry->fetch();
 $count41 = $res->counts;
 
 $str="select sum(jumlah) as jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
       where kodeorg like '".$kodeorg."%' 
       and idkomponen in(37) and periodegaji='".$param['periode']."' group by idkomponen,karyawanid";
 $potx=Array();
 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
 $res->setFetchMode(PDO::FETCH_OBJ);
 if($count37 < 1){
	while($barx=$res->fetch())
	{
		$potx[$barx->karyawanid]=$barx->jumlah; 
	}
 }
 
 $str="select sum(jumlah) as jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
       where kodeorg like '".$kodeorg."%' 
       and idkomponen in(41) and periodegaji='".$param['periode']."' group by idkomponen,karyawanid";
 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
 $res->setFetchMode(PDO::FETCH_OBJ);
 if($count41 < 1){
	while($barx=$res->fetch())
	{	
		if(isset($potx[$barx->karyawanid])){
			$potx[$barx->karyawanid]+=$barx->jumlah;
		}else{
			$potx[$barx->karyawanid]=$barx->jumlah;
		}    
	}
 }
#---------------------------------------------------------------
#ambil semua gaji per karyawan
#---------------------------------------------------------------
#1. Ambil gaji total per karyawan pada unit bersangkutan
  $str="select jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
        where kodeorg like '".$kodeorg."%' 
        and plus=1 and periodegaji='".$param['periode']."' and name not like '%BPJS%'";
  $gaji=array();
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  while($bar=$res->fetch())
  {
    if($bar->idkomponen==1){
      @$gaji[$bar->karyawanid][$bar->idkomponen]=$bar->jumlah-$potx[$bar->karyawanid];//dikurangkan dengan potongan HK
    }
    else{
      @$gaji[$bar->karyawanid][$bar->idkomponen]=$bar->jumlah;
    }
    // if($bar->jumlah>0){
    //   @$gajix[$bar->idkomponen]=[$bar->idkomponen];
    // }
    
    @$info[$bar->karyawanid][$bar->idkomponen]='GAJI';
  }
 
 #= 1.1 bpjs disini
  # Kenapa di komen
  # karena KSP memakai BPJS dengan format tulisan :
  # BPJS JKK (-), BPJS Kesehatan (-), BPJS KESEHATAN (+) jadi like 'BPJS%' bisa
  // $str="select jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
  //       where kodeorg like '".$kodeorg."%' 
  //       and plus=1 and periodegaji='".$param['periode']."' and name like 'BPJS%'";

  # Palma Pakai yang like '%BPJS%'
  $str="select jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
        where kodeorg like '".$kodeorg."%' 
        and plus=1 and periodegaji='".$param['periode']."' and name like '%BPJS%'";

  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  while($bar=$res->fetch())
  {
    // if($bar->jumlah>0){
    //   @$gajix[$bar->idkomponen]=[$bar->idkomponen];
    // }
    @$gaji[$bar->karyawanid][$bar->idkomponen]=$bar->jumlah;
    @$info[$bar->karyawanid][$bar->idkomponen]='BPJS';
    
  } 
 
 #2 Ambil subunit setiap karyawan
 // $str="select subbagian,karyawanid,namakaryawan from ".$dbname.".datakaryawan 
       // where lokasitugas='".$kodeorg."'";
 $str="select subbagian,karyawanid,namakaryawan from ".$dbname.".datakaryawan_hist 
       where lokasitugas='".$kodeorg."' and periodegaji='".$param['periode']."' and version_type='B'";	   
 $subunit=Array();
 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
     $subunit[$bar->karyawanid]=$bar->subbagian;
     $namakaryawan[$bar->karyawanid]=$bar->namakaryawan;     
}
 #3 ambil semua organisasi yang traksi atau workshop
  $str="select distinct kodeorganisasi,tipe from ".$dbname.".organisasi 
        where kodeorganisasi like '".$kodeorg."%'";
  $tipe=Array();
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  while($bar=$res->fetch()){
      $tipe[$bar->kodeorganisasi]=$bar->tipe;     
  } 

  #==========================================================================================  
  
	#= ambil daftar karyawan yang terdaftar di kebun aktifitas
	$str="select * from ".$dbname.".kebun_aktifitas where  
		tanggal>='".$tgmulai."' and tanggal <='".$tgsampai."' and kodeorg='".$kodeorg."' and jurnal=1";
	$res=fetchdata($str);
	foreach($res as $bar){
		$pejabatbkm[$bar['nikmandor']]=$bar['nikmandor'];
		$pejabatbkm[$bar['nikmandor1']]=$bar['nikmandor1'];
		$pejabatbkm[$bar['nikasisten']]=$bar['nikasisten'];
		$pejabatbkm[$bar['keranimuat']]=$bar['keranimuat'];
	}
	
	// echo"<pre>";
	// print_r($pejabatbkm);
	// echo"</pre>";

	 
  
   $GJ=$gaji;
   #buang karyawan yang gajinya sudah teralokasi
 
    $str="select karyawanid from ".$dbname.".kebun_kehadiran_vw
          where tanggal>='".$tgmulai."' and tanggal <='".$tgsampai."' 
          and unit='".$kodeorg."' and jurnal=1";
		  // echo $str;
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar=$res->fetch()){
		    if(!in_array($bar->karyawanid,$pejabatbkm)){
				unset($gaji[$bar->karyawanid]);
			}
		    // if(in_array($bar->karyawanid],$pejabatbkm)){
			// }else{
				// unset($gaji[$bar->karyawanid]);
			// }
			// unset($gaji[$bar->karyawanid]);
	   }
    #buang karyawan yang tanggalmasuknya > dari tanggal akhir periode
    $str1="select karyawanid from ".$dbname.".datakaryawan where 
           lokasitugas='".$kodeorg."'
           and tanggalmasuk>'".$tgsampai."'";
       $res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar1=$res->fetch())
       {
        unset($gaji[$bar1->karyawanid]);
    }    
  #b. ambil prestasi kebun
    $str="select karyawanid from ".$dbname.".kebun_prestasi_vw
          where tanggal>='".$tgmulai."' and tanggal <='".$tgsampai."' 
          and unit='".$kodeorg."' and jurnal=1";
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar=$res->fetch())
       {
		    if(!in_array($bar->karyawanid,$pejabatbkm)){
				unset($gaji[$bar->karyawanid]);
			}
       // unset($gaji[$bar->karyawanid]);
    }

 #==========================================================================================
	#ambil kendaraan atau mesin yang menempel pada orang
	$str="select vhc,karyawanid from ".$dbname.".vhc_5operator where aktif='1'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$ken[$bar->karyawanid]=$bar->vhc;
	}
	#ambil komponen gaji
    $str="select id,name from ".$dbname.".sdm_ho_component";
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar=$res->fetch())
       {
        $komponen[$bar->id]=$bar->id;
        $namakomponen[$bar->id]=$bar->name;
    }   
    

    #ambil gaji yang sudah teralokasi per karyawan
    #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Init
    $potongan=array();
//and unit='".$kodeorg."'
    #a.kehadiran kebun
    $str="select sum(umr) as umr, sum(insentif) as insentif,karyawanid from ".$dbname.".kebun_kehadiran_vw
          where tanggal>='".$tgmulai."' and tanggal <='".$tgsampai."' 
           and jurnal=1 group by karyawanid";
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar=$res->fetch())
       {
        if(!isset($potongan[$bar->karyawanid])) $potongan[$bar->karyawanid]=array(1=>0,32=>0);
        $potongan[$bar->karyawanid][1]+=$bar->umr;//potongan gaji pokok
        $potongan[$bar->karyawanid][32]+=$bar->insentif; //potongan premi    
    }
    //and unit='".$kodeorg."'
	#b. ambil prestasi kebun
    $str="select sum(upahkerja) as umr, sum(upahpremilebihbasis) as insentif,sum(rupiahpenalty) as penalty,
          karyawanid from ".$dbname.".kebun_prestasi_vw
          where tanggal>='".$tgmulai."' and tanggal <='".$tgsampai."' 
           and jurnal=1 group by karyawanid";
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar=$res->fetch())
       {
        if(!isset($potongan[$bar->karyawanid])) $potongan[$bar->karyawanid]=array(1=>0,32=>0);
        $potongan[$bar->karyawanid][1]+=$bar->umr-$bar->penalty;//potongan gaji pokok
        $potongan[$bar->karyawanid][32]+=$bar->insentif; //potongan premi 
    }    

    ## Untuk Kutip Brondolan
    $str="select sum(rphk) as umr, sum(rppremi) as insentif,karyawanid from ".$dbname.".kebun_3premibmtbs
          where tanggal>='".$tgmulai."' and tanggal <='".$tgsampai."' 
           and posting=1 group by karyawanid";
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar=$res->fetch())
       {
        if(!isset($potongan[$bar->karyawanid])) $potongan[$bar->karyawanid]=array(1=>0,32=>0);
        $potongan[$bar->karyawanid][1]+=$bar->umr;//potongan gaji pokok
        $potongan[$bar->karyawanid][32]+=$bar->insentif; //potongan premi    
    }

    ## Untuk VHC SPL Kehadiran
    if($param['periode']>'2025-07'){
         $str="select sum(umr) as umr, sum(premi) as insentif,nik from ".$dbname.".vhc_spl_kehadiran_vw
            where tanggal>='".$tgmulai."' and tanggal <='".$tgsampai."' 
             and posting=1 group by nik";
         $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
         $res->setFetchMode(PDO::FETCH_OBJ);
         while($bar=$res->fetch())
         {
            if(!isset($potongan[$bar->nik])) $potongan[$bar->nik]=array(1=>0,32=>0);
            $potongan[$bar->nik][1]+=$bar->umr;//potongan gaji pokok
            $potongan[$bar->nik][32]+=$bar->insentif; //potongan premi    
         }
    }

    ## GET KEGIATAN YANG TIDAK DIALOKASI GAJI DLL
    $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='SNALOC' and kodeorg='".$param['kodeorg']."'";
    $res=fetchdata($str);
    $wherekegx="";
    if($res[0]['nilai']==''){
      $wherekegx="";		
    }else{
      $wherekegx=" and kodekegiatan in (".$res[0]['nilai'].")";
    }

    # Add NEW
    # Case : Ada Karyawan Plasma Supervisi, untuk Premi Brondolan di bentuk Jurnalnya saat Premi Pemanen / Kemandoran
    # Cek apakah sudah ada Jurnal Premi Brondolan yang sudah Terbentuk
    $sql = "select nik,sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 5=5 ".$wherekegx." and debet > 0 and periode='".$param['periode']."' and kodejurnal='PNN03' and noreferensi like '%PNN02%' GROUP BY nik";
    $res = fetchData($sql,"OBJECT");
    foreach($res as $val):
      // if(in_array($val->nik,$pejabatbkm)): # Cek apakah Pejabat BKM / Karyawan Supervisi
        if(!isset($potongan[$val->nik])) $potongan[$val->nik]=array(32=>0);
        $potongan[$val->nik][32]+=$val->jumlah;
      // endif;
    endforeach;
    # End
 #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

 #kurangkan gaji yang ada dengan yang sudah dialokasi
    $gajiblmalokasi=$GJ;
    foreach($GJ as $key=>$row)
    {
            if(!isset($potongan[$key])) $potongan[$key]=array(1=>0,32=>0);
            if(!isset($gajiblmalokasi[$key][1])) $gajiblmalokasi[$key][1]=0;
            if(!isset($gajiblmalokasi[$key][32])) $gajiblmalokasi[$key][32]=0;
            $gajiblmalokasi[$key][1]-= $potongan[$key][1];
            $gajiblmalokasi[$key][32]-= $potongan[$key][32];
    }
 #ambil selisih kekurangan 
    $kekurangan=0;
    foreach($gajiblmalokasi as $key)
    { 
      foreach($key as $row=>$cell)
      {
        if($cell<0)
            $kekurangan+=$cell;
      }
    }
	
	foreach ($gaji as $karid => $valx) {
     foreach ($valx as $komponen => $jlhx) {
          if(($tipe[$subunit[$karid]] == 'TRAKSI' or $tipe[$subunit[$karid]] == 'WORKSHOP')) {
            @$gajix[$komponen]=$komponen;
          }
     }
  }

  $komponenall='';
  foreach ($gajix as $komponen => $ponen) {
    if($komponenall==''){
              $komponenall=$komponen;
            }else{
              $komponenall.="###".$komponen;
            }
  }
	// echo"<pre>";
	// print_r($potongan);
	// echo"</pre>";

  #=======================================================================================================  
    
   #echo $kekurangan; Buang escape ini untuk mengetahui selisih gaji yang belum teralokasi 
 if(empty($gaji))
     exit('Error: No Salary data found');
 else {
  $tab="
				<button class=mybutton onclick=prosesGajiTrk(1) id=btnproses>Process</button>
				<button class=mybutton onclick=exportTableToExcel()>Excel</button>
                  <table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable>
                  <thead>
                    <tr class=rowheader>
                    <th rowspan='2'>No</th>
                    <th rowspan='2'>".$_SESSION['lang']['periode']."</th>
                    <th rowspan='2'>".$_SESSION['lang']['employeename']."</th>
                    <th rowspan='2'>".$_SESSION['lang']['karyawanid']."</th>
                    <th rowspan='2'>".$_SESSION['lang']['subbagian']."</th>
                    <th rowspan='2'>".$_SESSION['lang']['tipe']."</th>
                    <th rowspan='2'>".$_SESSION['lang']['kendaraan']."</th>
                    <th rowspan='1' colspan='".(count($gajix))."'>Nama ".$_SESSION['lang']['idkomponen']."</th>
                    <th rowspan='2'>".$_SESSION['lang']['total']."</th>
                    </tr><tr>";
                    foreach($gajix as $komponen =>$val) { 
                        $tab.="<th>".$namakomponen[$komponen]."</th> ";
                    
                    }
                    $tab.="</tr>
                  </thead>
                  <tbody>";

            $no=0;
            $ttl=0;
            foreach($gaji as $key =>$baris) { 
                if($tipe[$subunit[$key]] == 'TRAKSI' or $tipe[$subunit[$key]] == 'WORKSHOP') {
                  $no+=1;
                  $tab.="<tr class=rowcontent id='row".$no."'>
                  <td>".$no."</td>
                  <td id='periode".$no."'>".$_POST['periode']."</td>
                  <td id='namakaryawan".$no."'>".$namakaryawan[$key]."</td>
                  <td id='karyawanid".$no."'>".$key."</td>
                  <td id='subbagian".$no."'>".(isset($subunit[$key])? $subunit[$key]: '')."</td>
                  <td id='tipeorganisasi".$no."'>".(isset($tipe[$subunit[$key]])? $tipe[$subunit[$key]]: '')."</td>                        
                  <td id='mesin".$no."'>".(isset($ken[$key])? $ken[$key]: '')."</td>
                  <td id='komponenall".$no."' hidden>".$komponenall."</td>";
                  $totalxxxz=0;
                  foreach($gajix as $komponen =>$val) { 
                      $jlh=$gaji[$key][$val];
                        #====================================================================================================#
                        // UPDATE TRAKSI IDLE 
                        #====================================================================================================#
                        $dtIDLE = [];
                        $dtArrkdIDLE = [
                          1 => 'VHCG7', // Gaji Traksi Idle
                          32 => 'VHCG8', // PREMI Traksi Idle
                        ];

                        $kdIDLE = ($dtArrkdIDLE[$val] ?? '');
                        $whrIDLE = '';
                        if ($dtArrkdIDLE[$val] == 'VHCG7') {
                          $whrIDLE .= " AND noreferensi = 'ALK_UPAH_TRAKSI_IDLE'";
                        } else if ($dtArrkdIDLE[$val] == 'VHCG8') {
                          $whrIDLE .= " AND noreferensi = 'ALK_PREMI_TRAKSI_IDLE'";
                        }

                        if (in_array($val, array_keys($dtArrkdIDLE))) {
                          // cek dulu apakah ada gaji traksi idle nya 
                          $str = "SELECT DISTINCT nojurnal, periode, kodeorg, nik, sum(debet) as jumlah
                                  FROM {$dbname}.keu_jurnaldt_vw 
                                  WHERE 1=1 
                                    AND kodejurnal = '{$kdIDLE}'
                                    {$whrIDLE}
                                    AND nik = '{$key}'
                                    AND periode = '{$param['periode']}'
                                    AND kodeorg = '{$param['kodeorg']}'
                                  GROUP BY nojurnal
                                ";
                          $getDtIdle = fetchData($str);
                          foreach ($getDtIdle as $dt) {
                            $dtIDLE[$dt['nik']][$val] += $dt['jumlah'];
                          }
                        }

                        $tmpDtIDLE = 0;
                        $tmpJml = $jlh - $potongan[$key][$val];
                        if (!empty($dtIDLE)) {
                          $tmpDtIDLE = $dtIDLE[$key][$val];
                          $tmpJml = ($jlh - $potongan[$key][$val]) - $tmpDtIDLE;
                          // echo "<pre>" . var_export($tmpJml, true);
                        }

                        #====================================================================================================#
                        // END UPDATE TRAKSI IDLE 
                        #====================================================================================================#
                          $tab.="
                             <td align=right id='jumlah".$no."-".$val."'>".$tmpJml."</td>";
                        $totalxxxz+=$tmpJml;
      					       
                        if($info[$key][$val]=='BPJS'){
                          $ttlbpjs+=($jlh);
                        }else{
                          $ttlgaji+=$tmpJml; 
                        }

                        $ttl+=$tmpJml;     
                      
                    
                  }
                  $tab.="<td>".$totalxxxz."</td>
                  </tr>";
                }
            }
            $tab.="<tr class=rowcontent>
                    <td colspan=".(count($gajix)+7).">Total Gaji</td>
                    <td align=right>".number_format($ttlgaji)."</td>
                    </tr>";
					  $tab.="<tr class=rowcontent>
                    <td colspan=".(count($gajix)+7).">Total BPJS</td>
                    <td align=right>".number_format($ttlbpjs)."</td>
                    </tr>";
					      $tab.="<tr class=rowcontent id='row".$no."'>
                    <td colspan=".(count($gajix)+7).">Total</td>
                    <td align=right>".number_format($ttl)."</td>
                    </tr>";
             $tab.="</tbody><tfoot></tfoot></table>";
			
		echo $tab;
		
}
#----------------------------------------------------------------
?>