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
	while($barx=$resx->fetch())
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
	while($barx=$resx->fetch())
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
		   and plus=1 and periodegaji='".$param['periode']."' and name not like 'BPJS%'";
	 $gaji=array(); 
	 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		 if($bar->idkomponen==1) 
			@$gaji[$bar->karyawanid][$bar->idkomponen]=$bar->jumlah-$potx[$bar->karyawanid];//dikurangkan dengan potongan HK
		 else
			@$gaji[$bar->karyawanid][$bar->idkomponen]=$bar->jumlah;
	 }
	#2 Ambil subunit setiap karyawan
	$str="select subbagian,karyawanid,namakaryawan from ".$dbname.".datakaryawan 
	   where lokasitugas='".$kodeorg."'";
	$subunit=Array();$arrKaryId=array();
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		 $subunit[$bar->karyawanid]=$bar->subbagian;
		 $namakaryawan[$bar->karyawanid]=$bar->namakaryawan;
		 $arrKaryId[$bar->karyawanid]=$bar->karyawanid;		 
	}
 
$arrGapok=array();
#ambil gaji pokok
$sGjPokok="select karyawanid,(jumlah/25) as gjharian from ".$dbname.".sdm_5gajipokok 
		where tahun='".substr($param['periode'],0,4)."' and karyawanid in ('".implode("','",$arrKaryId)."')
		and idkomponen=1";
$rGjPokok=fetchData($sGjPokok);
if(count($rGjPokok)!=0){
	foreach($rGjPokok as $rw=>$val){
		$arrGapok[$val['karyawanid']]=$val['gjharian'];
	}
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
    $str="select vhc,karyawanid from ".$dbname.".vhc_5operator";
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar=$res->fetch())
       {
        $ken[$bar->karyawanid]=$bar->vhc;
    }
	
	$gajiOperator=array();
    $gajiOperator2=array();
    $sdt="select idkaryawan,kodevhc,sum(upah) as gaji,sum(premi) as premi  from ".$dbname.".vhc_runhk_vw 
          where tanggal between '".$tgmulai."' and '".$tgsampai."' and kodeorg='".$kodeorg."' group by idkaryawan,kodevhc";
    $rdt=fetchData($sdt);
    if(count($rdt)!=0){
        foreach($rdt as $rw=>$val){
            $key=$val['idkaryawan'];
            $ken[$val['idkaryawan']]=$val['kodevhc'];
            if($val['gaji']!=0){
                if($val['gaji']!=0){
                    #$val['gaji']=$arrGapok[$val['idkaryawan']];
                }
                $gajiOperator[$val['idkaryawan']]['gapok']+=$val['gaji'];
                $gajiOperator2[$val['idkaryawan']][$val['kodevhc']]['gapok']=$val['gaji'];
                $gajiOperator2[$val['idkaryawan']][$val['kodevhc']]['subunit']=$subunit[$key];
                 
            }
            if($val['premi']!=0){
                $gajiOperator[$val['idkaryawan']]['premi']+=$val['premi'];
                $gajiOperator2[$val['idkaryawan']][$val['kodevhc']]['premi']=$val['premi'];
                $gajiOperator2[$val['idkaryawan']][$val['kodevhc']]['subunit']=$subunit[$key];
                
            }
        }
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

    #a.kehadiran kebun
    $str="select sum(umr) as umr, sum(insentif) as insentif,karyawanid from ".$dbname.".kebun_kehadiran_vw
          where tanggal>='".$tgmulai."' and tanggal <='".$tgsampai."' 
          and unit='".$kodeorg."' and jurnal=1 group by karyawanid";
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar=$res->fetch())
       {
        if(!isset($potongan[$bar->karyawanid])) $potongan[$bar->karyawanid]=array(1=>0,32=>0);
        $potongan[$bar->karyawanid][1]+=$bar->umr;//potongan gaji pokok
        $potongan[$bar->karyawanid][32]+=$bar->insentif; //potongan premi    
    }
	#b. ambil prestasi kebun
    $str="select sum(upahkerja) as umr, sum(upahpremilebihbasis) as insentif,sum(rupiahpenalty) as penalty,
          karyawanid from ".$dbname.".kebun_prestasi_vw
          where tanggal>='".$tgmulai."' and tanggal <='".$tgsampai."' 
          and unit='".$kodeorg."' and jurnal=1 group by karyawanid";
       $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
       $res->setFetchMode(PDO::FETCH_OBJ);
       while($bar=$res->fetch())
       {
        if(!isset($potongan[$bar->karyawanid])) $potongan[$bar->karyawanid]=array(1=>0,32=>0);
        $potongan[$bar->karyawanid][1]+=$bar->umr-$bar->penalty;//potongan gaji pokok
        $potongan[$bar->karyawanid][32]+=$bar->insentif; //potongan premi 
    }    
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
	
	#cek ada  kerja di kendaraan atau tidak#
	foreach($gaji as $key =>$baris) { 
		foreach ($baris as $val=>$jlh) { 
			if($val==1){//dikurangi dengan upah bersumber dari inputan traksi upah
				$gaji[$key][$val]=$jlh-$gajiOperator[$key]['gapok'];
				if($tipe[$subunit[$key]]=='TRAKSI'){
					$subunit[$key]="";
				}
				
			}
			if($val==32){
				$gaji[$key][$val]=$jlh-$gajiOperator[$key]['premi'];
				if($tipe[$subunit[$key]]=='TRAKSI'){
					$subunit[$key]="";
				}
			}
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
             echo"<button class=mybutton onclick=prosesGaji(1) id=btnproses>Process</button>
                  <table class=sortable cellspacing=1 border=0>
                  <thead>
                    <tr class=rowheader>
                    <td>No</td>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>".$_SESSION['lang']['employeename']."</td>
                    <td>".$_SESSION['lang']['karyawanid']."</td>
                    <td>".$_SESSION['lang']['idkomponen']."</td>
                    <td>".$_SESSION['lang']['nama']."</td>
                    <td>".$_SESSION['lang']['subbagian']."</td>
                    <td>".$_SESSION['lang']['tipe']."</td>
                    <td>".$_SESSION['lang']['kendaraan']."</td>
                    <td>".$_SESSION['lang']['jumlah']."</td>
                    </tr>
                  </thead>
                  <tbody>";

            $no=0;
            $ttl=0;
            foreach($gaji as $key =>$baris) { 
                foreach ($baris as $val=>$jlh) { 
                    $no+=1;
                    echo"<tr class=rowcontent id='row".$no."'>
                       <td>".$no."</td>
                       <td id='periode".$no."'>".$_POST['periode']."</td>
                       <td id='namakaryawan".$no."'>".$namakaryawan[$key]."</td>
                       <td id='karyawanid".$no."'>".$key."</td>    
                       <td id='komponen".$no."'>".$val."</td>
                       <td id='namakomponen".$no."'>".$namakomponen[$val]."</td>
                       <td id='subbagian".$no."'>".(isset($subunit[$key])? $subunit[$key]: '')."</td>
                       <td id='tipeorganisasi".$no."'>".(isset($tipe[$subunit[$key]])? $tipe[$subunit[$key]]: '')."</td>                        
                       <td id='mesin".$no."'></td>
                       <td align=right id='jumlah".$no."'>".($jlh-$potongan[$key][$val])."</td>
                       </tr>";
                       $ttl+=($jlh-$potongan[$key][$val]);
                }
            }
			
			if(count($gajiOperator)!=0){
                foreach($gajiOperator2 as $karyid=>$arrDt){
                   foreach($arrDt as $kdvhc=>$val){
                        if($val['gapok']!=0){
                            $no+=1;
                            $komp=1;
                            echo"<tr class=rowcontent id='row".$no."'>
                            <td>".$no."</td>
                            <td id='periode".$no."'>".$_POST['periode']."</td>
                            <td id='namakaryawan".$no."'>".$namakaryawan[$karyid]."</td>
                            <td id='karyawanid".$no."'>".$karyid."</td>    
                            <td id='komponen".$no."'>".$komp."</td>
                            <td id='namakomponen".$no."'>".$namakomponen[$komp]."</td>
                            <td id='subbagian".$no."'>".$val['subunit']."</td>
                            <td id='tipeorganisasi".$no."'>TRAKSI</td>                        
                            <td id='mesin".$no."'>".(isset($kdvhc)? $kdvhc: '')."</td>
                            <td align=right id='jumlah".$no."'>".$val['gapok']."</td>
                            </tr>";
                            $ttl+=$val['gapok'];
                        }
                        if($val['premi']!=0){
                            $no+=1;
                            $komp=32;
                            echo"<tr class=rowcontent id='row".$no."'>
                            <td>".$no."</td>
                            <td id='periode".$no."'>".$_POST['periode']."</td>
                            <td id='namakaryawan".$no."'>".$namakaryawan[$karyid]."</td>
                            <td id='karyawanid".$no."'>".$karyid."</td>    
                            <td id='komponen".$no."'>".$komp."</td>
                            <td id='namakomponen".$no."'>".$namakomponen[$komp]."</td>
                            <td id='subbagian".$no."'>".$val['subunit']."</td>
                            <td id='tipeorganisasi".$no."'>TRAKSI</td>                  
                            <td id='mesin".$no."'>".(isset($kdvhc)? $kdvhc: '')."</td>
                            <td align=right id='jumlah".$no."'>".$val['premi']."</td>
                            </tr>";
                            $ttl+=$val['premi'];
                        }
                   }
                }
            }
            echo"<tr class=rowcontent id='row".$no."'>
                    <td colspan=9>Total</td>
                    <td align=right>".number_format($ttl)."</td>
                    </tr>";
             echo"</tbody><tfoot></tfoot></table>";

}
#----------------------------------------------------------------
?>