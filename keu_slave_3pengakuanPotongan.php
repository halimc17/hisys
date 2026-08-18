<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
#cek proses gaji tidak langsung sudah diproses atau belum





#= bentuk tipeorganisasi	
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)=4";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
   $tipeorg[$bar['kodeorganisasi']]=$bar['tipe'];
}




if($tipeorg[$_POST['kodeorg']]!="HOLDING" and $tipeorg[$_POST['kodeorg']]!="BULKING"){
	if($tipeorg[$_POST['kodeorg']]=="RND" or $tipeorg[$_POST['kodeorg']]=="TC"){
		$wh=" and nojurnal like '%RNDB%'";
	}else{
		$wh=" and (nojurnal like '%KBNB%' or nojurnal like '%GJH%')";
	}
	
	$sCek="select * from ".$dbname.".keu_jurnalht where 1=1 ".$wh." and tanggal like '".$_POST['periode']."%' and nojurnal like '%".$_POST['kodeorg']."%'";
	$rCek=fetchdata($sCek);
	if(count($rCek)==0){
		exit('warning: Jalankan Proses Gaji Karyawan Tidak Langsung');
	}
}
if($tipeorg[$_POST['kodeorg']]=="KEBUN"){
    $sCek="select * from ".$dbname.".keu_jurnalht where 
           (nojurnal like '%M0%' or nojurnal like '%M1%' or nojurnal like '%PKS01%' or nojurnal like '%PKS02%') 
		   and tanggal like '".$_POST['periode']."%' and nojurnal like '%".$_POST['kodeorg']."%'";
  $rCek=fetchdata($sCek);
  if(count($rCek)==0){
    exit('warning: Jalankan Proses Gaji Karyawan Langsung');
  }
}
#kamus akun
if($_SESSION['language']=='EN'){
    $zz='namaakun1 as namaakun';
}else{
    $zz='namaakun';
}
$str="select noakun,".$zz." from ".$dbname.".keu_5akun where length(noakun)=7 order by namaakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $arrAkun[$bar->noakun]=$bar->namaakun;
}
#kamus komponen
$sAkun="select  id,name from ".$dbname.".sdm_ho_component where plus=0 order by name";
$res=$owlPDO->query($sAkun) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rAkun=$res->fetch())
{
    $namakomponen[$rAkun['id']]=$rAkun['name'];
}
$nodata=0;
#ambil  noakun debet dan kredit dari setup keu_5pegakuanpotongan
$str="select * from ".$dbname.".keu_5pengakuanpotongan where tipeorganisasi='".$tipeorg[$_POST['kodeorg']]."' order by idkomponen";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	@$nodata++;
    $debet[$bar->idkomponen]=$bar->noakundebet;
    $kredit[$bar->idkomponen]=$bar->noakunkredit;
    $kelompok[$bar->idkomponen]=substr($bar->noakunkredit,0,3);#ini juga tambahan untuk blok

    if($bar->noakundebet=='' or $bar->noakundebet=='')
    {
        // exit(' Error: Setup account number debet/kredit for component '.$bar->idkomponen.' not defined');
		exit("Warning: Maping potongan untuk tipe organisasi ".$tipeorg[$_POST['kodeorg']]." belum disetting, hubungi akunting RO
		untuk melakukan setting di menu : keuangan->setup->maping potongan gaji");
    }
	
}
if($nodata==0){
	exit("Warning: Maping potongan untuk tipe organisasi ".$tipeorg[$_POST['kodeorg']]." belum disetting, hubungi akunting RO
		untuk melakukan setting di menu : keuangan->setup->maping potongan gaji");
}

#==========bahan dasar pengambilan blok TBM,TM,PNN
#perawatan:
$str="select distinct karyawanid,kodeorg,left(kodekegiatan,3) as kelompok from ".$dbname.".kebun_kehadiran_vw
      where kodekegiatan like '126%' or kodekegiatan like '128%' or kodekegiatan like '621%'
      and tanggal like '".$_POST['periode']."%' and kodeorg like '".$_POST['kodeorg']."%'";
	  // exit("Error:$str");
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $blok[$bar->karyawanid][$bar->kelompok]=$bar->kodeorg;
}

$str="select distinct karyawanid,kodeorg from ".$dbname.".kebun_prestasi_vw
      where tanggal like '".$_POST['periode']."%' and kodeorg like '".$_POST['kodeorg']."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $blok[$bar->karyawanid]['611']=$bar->kodeorg;
}

#===================end pengambilan blok

$tanggal=  str_replace("-", "",$_POST['periode'])."28";

#$str=" select a.idkomponen,a.karyawanid,a.jumlah,b.namakaryawan from ".$dbname.".sdm_gaji a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.kodeorg='".$_POST['kodeorg']."' and a.periodegaji='".$_POST['periode']."' and a.idkomponen in (select idkomponen from ".$dbname.".keu_5pengakuanpotongan)";

$str=" select a.idkomponen,a.karyawanid,a.jumlah,b.namakaryawan from ".$dbname.".sdm_gaji a
   left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.periodegaji='".$_POST['periode']."' where b.version_type = 'B' and a.kodeorg='".$_POST['kodeorg']."' and a.periodegaji='".$_POST['periode']."' and a.idkomponen in (select idkomponen from ".$dbname.".keu_5pengakuanpotongan)";
   
// echo $str;
		   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    if(!isset($total[$bar->idkomponen])) $total[$bar->idkomponen]=0;
    $total[$bar->idkomponen]+=$bar->jumlah;
    $nama[$bar->karyawanid]=$bar->namakaryawan;
    $rinci[$bar->idkomponen][$bar->karyawanid]=$bar->jumlah;
}
#penambahan gapok untuk perhitungan jamsostek porsi perusahaan
$strGapok=" select idkomponen,karyawanid,jumlah from ".$dbname.".sdm_gaji where kodeorg='".$_POST['kodeorg']."' 
           and periodegaji='".$_POST['periode']."' and idkomponen=1";
$res=$owlPDO->query($strGapok) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $dtGapok[$bar->karyawanid]=$bar->jumlah;
}
#pengambilan persen porsi perusahaan
#jamhari 10-april-2015 penambahan jurnal porsi perusahaan
$persenJamsostek=array();
$loksi='PABRIK';
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
        $loksi='KEBUN';
}
$sPersn="select jenisbpjs,bebanperusahaan from ".$dbname.".sdm_5bpjs where lokasibpjs='".$loksi."'";
$res=$owlPDO->query($sPersn) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rPersn=$res->fetch()){
        if($rPersn['jenisbpjs']=='44'){
                $persenJamsostek[44]=$rPersn['bebanperusahaan'];		
                $awlJrn[44]='002';
                $ketPersn[44]=strtoupper($rPersn['jenisbpjs']);
        }
        if($rPersn['jenisbpjs']=='3'){
                $persenJamsostek[3]=$rPersn['bebanperusahaan'];
                $awlJrn[3]='001';
                $ketPersn[3]=strtoupper($rPersn['jenisbpjs']);
        }
        if($rPersn['jenisbpjs']=='81'){
                $persenJamsostek[81]=$rPersn['bebanperusahaan'];
                $awlJrn[81]='003';
                $ketPersn[81]=strtoupper($rPersn['jenisbpjs']);
        }

}

$jamPrhsn=0;



// Default Segment
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

if(empty($total))
    exit('Error: No salary data found');
elseif(isset($_POST['method']) and $_POST['method']=='post'){
	
	
	#= delete semua query alk_pot
	$nojurnaldelete=$tanggal."/".$_POST['kodeorg']."/POT";
	$str="delete from ".$dbname.".keu_jurnalht where kodejurnal='POT' and tanggal='".$tanggal."' and nojurnal like '".$nojurnaldelete."%'  ";
	// exit("Error:$str"); 
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		echo " Gagal," . addslashes($e->getMessage());
	}	

	
	
        #periksa periode akuntansi
    $str="select * from ".$dbname.".setup_periodeakuntansi where 
                 kodeorg ='".$_POST['kodeorg']."' and tutupbuku=0 and periode='".$_POST['periode']."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $numrows=owlBaris($res);
    if($numrows<1)
    {
        exit("Error: Accounting has closed transaction of  ".$_POST['periode']);
    }   
        foreach($total as $komponen =>$ttl)
        {
                #bersihkan detail
                $dataRes['detail']=array();
                $dataRes['header']=array();
                $totalJamsostek=0;
                $nojurnal2="";//untuk jurnal jamsostek porsi PT
                #buat nourut
                $noUrut=0;                
                $noUrut++;

                #setup nojurnal
                $nojurnal=$tanggal."/".$_POST['kodeorg']."/POT/".$komponen;
                $ttl=str_replace(",","",$ttl);
                #======================== /Nomor Jurnal ============================
                # Prep Header
                $dataRes['header'][]  = array(
                        'nojurnal'=>$nojurnal,
                        'kodejurnal'=>'POT',
                        'tanggal'=>$tanggal,
                        'tanggalentry'=>date('Ymd'),
                        'posting'=>1,
                        'totaldebet'=>$ttl,
                        'totalkredit'=>-1*$ttl,
                        'amountkoreksi'=>'0',
                        'noreferensi'=>'ALK_POT:'.$komponen,
                        'autojurnal'=>'1',
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'revisi'=>'0'
                );
                # Data Detail
                # Debet
                $dataRes['detail'][] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$tanggal,
                        'nourut'=>$noUrut,
                        'noakun'=>$debet[$komponen],
                        'keterangan'=> $namakomponen[$komponen],
                        'jumlah'=>$ttl,
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$_POST['kodeorg'],
                        'kodekegiatan'=>'',
                        'kodeasset'=>'',
                        'kodebarang'=>'',
                        'nik'=>'',
                        'kodecustomer'=>'',
                        'kodesupplier'=>'',
                        'noreferensi'=>'ALK_POT:'.$komponen,
                        'noaruskas'=>'',
                        'kodevhc'=>'',
                        'nodok'=>$komponen,
                        'kodeblok'=>'',
                        'revisi'=>'0',
                   'kodesegment'=>$defSegment
                );

                foreach($rinci[$komponen] as $karid =>$jlhperorang) {
                        $noUrut++;
                #tambahan untuk kode blok------------	
                        if(isset($blok[$karid][$kelompok[$komponen]])){
                            $kodeblok=$blok[$karid][$kelompok[$komponen]];
                        }else{
                          $kodeblok=''; 
                        }	
                #========end tambahan kode blok	
                        # Kredit
                        $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>$tanggal,
                                'nourut'=>$noUrut,
                                'noakun'=>$kredit[$komponen],
                                'keterangan'=> $namakomponen[$komponen].": ".$nama[$karid],
                                'jumlah'=>-1*$jlhperorang,
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$_POST['kodeorg'],
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>'',
                                'nik'=>$karid,
                                'kodecustomer'=>'',
                                'kodesupplier'=>'',
                                'noreferensi'=>'ALK_POT:'.$komponen,
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$komponen,
                                'kodeblok'=>$kodeblok,
                                'revisi'=>'0',
                           'kodesegment'=>$defSegment
                        );

                        #jika potongan jamsoste ditambahkan perhitungan jamsostek perusahan per kary
                        if(($komponen==3)||($komponen==44)||($komponen==81)){
                                @$jamPrhsn=$dtGapok[$karid]*$persenJamsostek[$komponen]/100;
                                $totalJamsostek+=$jamPrhsn;
                        }
                }
				/*
                if(($komponen==3)||($komponen==44)||($komponen==81)){
                        $noUrut2=0;
                        $noUrut2++;
                        $sJrnid="select jurnalid,noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='SDMJP'";
                        $qJrnid=$owlPDO->query($sJrnid) or die(print " Gagal: ".PDOException::getMessage());
                        $qJrnid->setFetchMode(PDO::FETCH_ASSOC);
                        $rJrnid=$qJrnid->fetch();
                                        $nojurnal2=$tanggal."/".$_SESSION['empl']['lokasitugas']."/".$rJrnid['jurnalid']."/".$awlJrn[$komponen];

                                        #======================== /Nomor Jurnal ============================
                                        # Prep Header
                                        $dataRes['header'][] = array(
                                                'nojurnal'=>$nojurnal2,
                                                'kodejurnal'=>$rJrnid['jurnalid'],
                                                'tanggal'=>$tanggal,
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>$totalJamsostek,
                                                'totalkredit'=>-1*$totalJamsostek,
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>'ALK_POT:'.$komponen,
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'revisi'=>'0'
                                        ); 

                                        # Data Detail
                                        # Debet
                                        $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal2,
                                                'tanggal'=>$tanggal,
                                                'nourut'=>$noUrut2,
                                                'noakun'=>$rJrnid['noakundebet'],
                                                'keterangan'=> "BPJS ".$ketPersn[$komponen]." : PORSI PT UNIT ".$_SESSION['empl']['lokasitugas'],
                                                'jumlah'=>$totalJamsostek,
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$_SESSION['empl']['lokasitugas'],
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>'',
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>'ALK_POT:'.$komponen,
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>'',
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                           'kodesegment'=>$defSegment
                                        );
                        $noUrut2++;
                                        # Kredit
                                        $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal2,
                                                'tanggal'=>$tanggal,
                                                'nourut'=>$noUrut2,
                                                'noakun'=>$rJrnid['noakunkredit'],
                                                'keterangan'=> "BPJS ".$ketPersn[$komponen]."  : PORSI PT UNIT ".$_SESSION['empl']['lokasitugas'],
                                                'jumlah'=>-1*$totalJamsostek,
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$_SESSION['empl']['lokasitugas'],
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>'',
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>'ALK_POT:'.$komponen,
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>'',
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                           'kodesegment'=>$defSegment
                                        );
                }    
				*/
                #hapus dulu yang lama
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                try{$owlPDO->exec($RBDet); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
                if($nojurnal2!=''){
                        $RBDet2 = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal2."'");
                        try{$owlPDO->exec($RBDet2); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }     
                }
                #=====================execute
                foreach($dataRes['header'] as $row) {
                        $insHead = insertQuery($dbname,'keu_jurnalht',$row);
                        try{$owlPDO->exec($insHead); }catch (PDOException $e) {$headErr .= "Insert Header komponen:".$komponen." Error : " . $e->getMessage(); die(); }
                }

                if(empty($headErr)) {
                        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                        $detailErr = '';
                        foreach($dataRes['detail'] as $row) {
                                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                                try{$owlPDO->exec($insDet); }
                                catch (PDOException $e) {
                                    $detailErr .= "Insert Detail Komponen:".$komponen." Error : " . $e->getMessage() ; 
                                    break;
                                }
                        }

                        if($detailErr=='') {
                                #do nothing
                        } else {
                                echo $detailErr;
                                # Rollback, Delete Header
                                if($nojurnal2!=''){
                                        $RBDet2 = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal2."'");
                                        $owlPDO->exec($RBDet2);   
                                } 
                                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                                try{$owlPDO->exec($RBDet); }
                                catch (PDOException $e) {
                                        echo "Rollback Delete Header Error : ".$e->getMessage() ;
                                        exit;
                                }                               
                        }
                } else {
                        echo $headErr;
                        exit;
                }                   
                #====================end excute   
    }//end for total          
} else {
             echo"<button class=mybutton onclick=prosesPotongan('".$_POST['periode']."') id=btnproses>Process</button><button class=mybutton onclick=exportTableToExcel()>Excel</button>
                  <table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable>
                  <thead>
                    <tr class=rowheader>
                    <th>No</th>
                    <th>".$_SESSION['lang']['periode']."</th>
                    <th>".$_SESSION['lang']['noakun']."</th>
                    <th>".$_SESSION['lang']['namaakun']."</th>                    
                    <th>".$_SESSION['lang']['keterangan']."</th>
                    <th>".$_SESSION['lang']['debet']."</th>
                    <th>".$_SESSION['lang']['kredit']."</th>
                    </tr>
                  </thead>
                  <tbody>";

            foreach($total as $komponen =>$ttl)
             {
              $no=0;                
                $no++;
                echo"<tr class=rowcontent>
                          <td>".$no."</td>
                          <td>".$_POST['periode']."</td> 
                          <td>".$debet[$komponen]."</td>
                          <td>".$arrAkun[$debet[$komponen]]."</td> 
                          <td>".$namakomponen[$komponen]."</td>
                          <td align=right>".number_format($ttl)."</td> 
                          <td align=right>0</td>     
                          </tr>";
                #loop per orangnya:
                    foreach($rinci[$komponen] as $karid =>$jlhperorang){
                        $no++;
                        echo"<tr class=rowcontent>
                                 <td>".$no."</td>
                                 <td>".$_POST['periode']."</td> 
                                 <td>".$kredit[$komponen]."</td>
                                 <td>".$arrAkun[$kredit[$komponen]]."</td> 
                                 <td>".$namakomponen[$komponen].": ".$nama[$karid]."</td>
                                 <td align=right>0</td>                                      
                                 <td align=right>".number_format($jlhperorang)."</td>     
                                 </tr>";                    
                    }

             }
             echo"</tbody><tfoot></tfoot></table>";
}
#----------------------------------------------------------------
?>