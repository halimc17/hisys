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


$param = $_GET;
$kodeorg  = $param['kodeorg'];
$dataorg = array();
$dtstr="select * from ".$dbname.".organisasi where  kodeorganisasi = '".$kodeorg."'";
$str=$owlPDO->query($dtstr);
$str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
  $dataorg[$bar->kodeorganisasi] = $bar;
 }


#= validasi jika tidak lokasitugas HO dan tipelokasitugas HOLDING
$optTipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$tipeOrg = $optTipe[$kodeorg];

if(@$dataorg[$kodeorg]->tipe=='HOLDING' and @$dataorg[$kodeorg]->tipe=='HOLDING' and @$dataorg[$kodeorg]->wilayahkota=='JAKARTA'){
}else{
	exit("Warning:Anda tidak dapat lakukan proses ini");
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
#indra
// if($tgmulai=='' || $tgsampai=='')
    // exit("Error: Accounting period is not registered");

/**
 * Validasi apakah proses gaji telah ditutup
 */
$qGaji = selectQuery($dbname,'sdm_5periodegaji','sudahproses,jenisgaji',"kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'");
$resGaji = fetchData($qGaji);
$optGaji = array();
foreach($resGaji as $row) {
	$optGaji[$row['jenisgaji']] = $row['sudahproses'];
}


// 1. Validasi Empty
if(empty($optGaji)) exit('Warning: Periode Gaji '.$param['periode']." belum ada");
if(!isset($optGaji['B'])) exit('Warning: Periode Gaji Bulanan '.$param['periode']." belum ada");

// 2. Validasi Proses Gaji
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
 
 $str="select sum(jumlah) as jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetailho_vw 
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
 $str="select jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetailho_vw 
       where kodeorg like '".$kodeorg."%' 
       and plus=1 and periodegaji='".$param['periode']."'";
 $gaji=array();
 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
     if($bar->idkomponen==1) 
        @$gaji[$bar->karyawanid][$bar->idkomponen]=$bar->jumlah-$potx[$bar->karyawanid];//dikurangkan dengan potongan HK
     else
		@$gaji[$bar->karyawanid][$bar->idkomponen]=$bar->jumlah;
 }
 #2 Ambil subunit setiap karyawan
 $str="select subbagian,karyawanid,namakaryawan from ".$dbname.".datakaryawan 
       where lokasitugas='".$kodeorg."'";
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
while($bar=$res->fetch())
{
     $tipe[$bar->kodeorganisasi]=$bar->tipe;
     
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
 #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

 #kurangkan gaji yang ada dengan yang sudah dialokasi
    $gajiblmalokasi=$gaji;
    foreach($gaji as $key=>$row)
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

  #=======================================================================================================  
    
   #echo $kekurangan; Buang escape ini untuk mengetahui selisih gaji yang belum teralokasi 
 if(empty($gaji))
     exit('Error: No Salary data found');
 else {
              echo"<button class=mybutton onclick=prosesGajiho(1) id=btnproses>Process</button>
                  <table class=sortable cellspacing=1 border=0>
                  <thead>
                    <tr class=rowheader>
                    <td>No</td>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>".$_SESSION['lang']['idkomponen']."</td>
                    <td>".$_SESSION['lang']['nama']."</td>
                    <td>".$_SESSION['lang']['tipe']."</td>
                    <td hidden>".$_SESSION['lang']['kendaraan']."</td>
                    <td>".$_SESSION['lang']['jumlah']."</td>
                    </tr>
                  </thead>
                  <tbody>";

            $no=0;
            $ttl=0;

            foreach($gaji as $key =>$baris) { 
                foreach ($baris as $val=>$jlh) { 
                    $dtKomp[$val]=$val;
                    if($subunit[$key]==''){
                        $subunit[$key]=$kodeorg;
                        $tipe[$subunit[$key]]='KANTOR';
                    }
                    $dtSubunit[$subunit[$key]]=$subunit[$key];
                    if(!isset($ken[$key])){
                        $ken[$key]='NOVHC';
                    }
                    @$dtVhc[$ken[$key]]=$ken[$key];
                     
                    @$dtTipe[$ken[$key]]=$tipe[$subunit[$key]];
                    @$dtRupGaji[$val][$tipe[$subunit[$key]]][$ken[$key]]+=$jlh;
                }
            }
             
            foreach ($dtRupGaji as $key=>$val){
                foreach($val as $dtRw=>$isDt){
                    foreach($isDt as $rwData=>$nil){
                            $no+=1;
                            echo"<tr class=rowcontent id='row".$no."'>
                               <td>".$no."</td>
                               <td id='periode".$no."'>".$_POST['periode']."</td>
                               <td id='komponen".$no."'>".$key."</td>
                               <td id='namakomponen".$no."'>".$namakomponen[$key]."</td>
                               <td id='tipeorganisasi".$no."'>".$dtRw."</td>                        
                               <td id='mesin".$no."' hidden></td>
                               <td align=right id='jumlah".$no."'>".$nil."</td>
                               </tr>";
                               $ttl+=$nil;
                    }
                }
            }
            echo"<tr class=rowcontent id='row".$no."'>
                    <td colspan=5>Total</td>
                    <td align=right>".number_format($ttl)."</td>
                    </tr>";
             echo"</tbody><tfoot></tfoot></table>";

}
#----------------------------------------------------------------
?>