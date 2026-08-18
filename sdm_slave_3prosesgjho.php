<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

/*
#untuk yang ikut proses penggajian harian
1) KHL [4] 
2) PKWT [2] => perlakuan KHT
3) PKWTT / KHT [3] => perlakuan KHT
#tambahan ambil premi kemandoran dari table kebun_premikemandoran
*/


$proses = $_GET['proses'];
$param = $_POST;
$namakar = array();
$premi = array();
$penalty = array();
$gapokbhl = array();
$penaltykehadiran = array();


// exit("Error:".$param['kodeorg']);

#= ambil HOnya saja
$str="select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodeorg']."' and tipe='HOLDING'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch(); 
  $kodeho=$bar['kodeorganisasi'];
  
  // exit("Error:$kodeho");


$optTipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$nmtpkar = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$tipeOrg = $optTipe[$param['kodeorg']];


#cek tutup atau belum periode gaji
$sCekPeriode = "select * from " . $dbname . ".sdm_5periodegaji where periode='" . $param['periodegaji'] . "' 
              and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $param['kodeorg'] . "' and tipe='HOLDING')
        and sudahproses=1 and jenisgaji='B'";
$res = $owlPDO->query($sCekPeriode) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);
if ($numrows > 0)
    $aktif2 = false;
else
    $aktif2 = true;
if (!$aktif2) {
    exit(" Payroll period has been closed");
}



#periksa tutupbuku
$str = "select * from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periodegaji'] . "' and 
            kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $param['kodeorg'] . "') and tutupbuku=1";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);
if ($numrows > 0)
    $aktif = false;
else
    $aktif = true;
if (!$aktif) {
    exit("Accounting perid has been closed");
}




#= Get Period Range
$qPeriod = selectQuery($dbname, 'sdm_5periodegaji', 'tanggalmulai,tanggalsampai', "periode='" . $param['periodegaji'] . "' 
    and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $param['kodeorg'] . "') and jenisgaji='B'");
$resPeriod = fetchData($qPeriod);
@$tanggal1 = $resPeriod[0]['tanggalmulai'];
@$tanggal2 = $resPeriod[0]['tanggalsampai'];

$totalhari=count(rangeTanggal($tanggal1,$tanggal2));


#= Get Karyawan
$str = selectQuery($dbname, 'datakaryawan', 'lokasitugas,statuspajak,karyawanid,tipekaryawan,namakaryawan,jms,bpjs,pensiun',
    "alokasi=1 and kodeorganisasi='".$param['kodeorg']."' and
    (tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and
    (tanggalmasuk<='" . $tanggal2 . "' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)");
$res = fetchData($str);

# Error empty karyawan
if (empty($res)) {
    echo "Error : There is no presence(kehadiran) on this period";
    exit();
} else {
    $id = array();
    foreach ($res as $row => $kar) {
        $id[$kar['karyawanid']][] = $kar['karyawanid'];
        $statuskar[$kar['karyawanid']] = $kar['statuspajak'];
        $namakar[$kar['karyawanid']] = $kar['namakaryawan'];
        $lokasitugas[$kar['karyawanid']] = $kar['lokasitugas'];
        #mengambil no Jamsostek
        #bpjstenaga
        $bpjstenaga[$kar['karyawanid']] = trim($kar['jms']);
        #bpjs pensiun
        $bpjspensiun[$kar['karyawanid']] = trim($kar['pensiun']);
        #bpjskes
        $bpjskes[$kar['karyawanid']] = trim($kar['bpjs']);
        $tipekaryawan[$kar['karyawanid']] = trim($kar['tipekaryawan']);
    }
}



#= ambil pengali gaji dari proses rekap absen bulanan
$str = "select * from " . $dbname . ".sdm_rekapabsenhobulanan where kodept='" . $param['kodeorg'] . "' and periode='".$param['periodegaji'] ."'";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
  $pengali[$bar['karyawanid']]=$bar['pengali'];
}



#= ambil semua komponen dari gajipokok
#= table gapokho
// $addExcp=" and idkomponen  in (1,2,40,56,57) ";
$addkomponen=" and idkomponen in (select id from ".$dbname.".sdm_ho_component where plus=1 and type='basic')";
$str1 = "select a.*,b.namakaryawan,b.lokasitugas from " . $dbname . ".sdm_5gajipokokho a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where a.tahun=" . substr($tanggal1, 0, 4) . " and b.kodeorganisasi='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') ".$addkomponen." and idkomponen not in ('69','45')  ";
$res1 = fetchData($str1);
$tjms = array();
foreach ($res1 as $idx => $val) { 
        #= add to ready data
    if(@$hk[$val['karyawanid']]==''){
      @$hk[$val['karyawanid']]=0;
    }
        $readyData[] = array(
            'kodeorg' => $kodeho,
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $val['karyawanid'],
            'idkomponen' => $val['idkomponen'],
            'jumlah' => $val['jumlah']*$pengali[$val['karyawanid']],
            'pengali' => 1,
            'hk'=>$hk[$val['karyawanid']]);
}

// echo"<pre>";
// print_r($readyData);
// echo"</pre>";


#= pendapatan lain ho
$str = "select a.karyawanid,a.rpjumlah,a.idkomponen,b.lokasitugas from " . $dbname . ".sdm_pendapatanho a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.alokasi=1
        and b.kodeorganisasi='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00')
               and  periode='" . $param['periodegaji'] . "'";
$res = fetchData($str);
if(!empty($res)){
  foreach ($res as $idx => $row) {
    if (isset($id[$row['karyawanid']])) {
        $readyData[] = array(
            'kodeorg' => $kodeho,
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $row['karyawanid'],
            'idkomponen' => $row['idkomponen'],
            'jumlah' => $row['rpjumlah'],
            'pengali' => 1,
            'hk'=>0);
    }
  }
}


/*
#= pendapatan lain
$str = "select a.karyawanid,a.jumlah,a.idkomponen,b.lokasitugas from " . $dbname . ".sdm_pendapatanlaindt a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.alokasi=1
        and b.kodeorganisasi='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00')
               and  periodegaji='" . $param['periodegaji'] . "'";   
    
$res = fetchData($str);
foreach ($res as $idx => $val) {
    $readyData[] = array(
        'kodeorg' => $kodeho,
        'periodegaji' => $param['periodegaji'],
        'karyawanid' => $val['karyawanid'],
        'idkomponen' => $val['idkomponen'],
        'jumlah' => $val['jumlah'],
        'pengali' => 1,
        'hk'=>0);
}
*/
###################################################################################################################################################
###################################################################################################################################################
###################################################################################################################################################
$umrbulanan=array();
$sGapo="select * from ".$dbname.".sdm_5gajipokokho where tahun='".substr($param['periodegaji'],0,4)."' and idkomponen=1";
$rGapo=fetchData($sGapo);
foreach ($rGapo as $key => $val) {
  $sData="select tipe,lokasitugas from ".$dbname.".datakaryawan a left join ".$dbname.".organisasi b on a.lokasitugas=b.kodeorganisasi where karyawanid='".$val['karyawanid']."'";
  $rData=fetchData($sData);
  if($rData[0]['tipe']=='HOLDING'){
      $rData[0]['tipe']='HO';
  }
  $tipeOrg[$val['karyawanid']]=$rData[0]['tipe'];
  $umrbulanan[$val['karyawanid']]=$val['jumlah'];
}
if(empty($umrbulanan)){
  exit("warning: Gaji Pokok Tahun ".substr($param['periodegaji'],0,4)." Belum Disetting");
}
###tambahan indra disini, memasukan bpjs kesehatan (jms) dan bpjs kesehatan
##algoritma : jika kolom jms dan bpjs di datakaryawan terisi maka akan memotong
##jika tidak maka di kosongkan
@$bpjsorg = 'HO';

#= parameter aplikasi 

#= kerja
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSKER'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch(); 
  $arrbpjs=explode(',',$bar['nilai']);
  foreach($arrbpjs as $key){
    $arrker[]=$key;
  }

#= kesehatan
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSKES'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch(); 
  $arrbpjs=explode(',',$bar['nilai']);
  foreach($arrbpjs as $key){
    $arrkes[]=$key;
  }
  
  
#= pensiun
$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSPEN'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch(); 
  $arrbpjs=explode(',',$bar['nilai']);
  foreach($arrbpjs as $key){
    $arrpen[]=$key;
  }




$sUmpDaerah="select distinct jumlah from ".$dbname.".sdm_5gajipokok where tahun='".substr($param['periodegaji'],0,4)."' and idkomponen='87' and kodeorg  in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $param['kodeorg'] . "' and tipe='HOLDING')";
$rUmpDaerah=fetchData($sUmpDaerah);
$umpDaerah=$rUmpDaerah[0]['jumlah'];#bpjs kesehatan
$str = "select * from " . $dbname . ".sdm_5bpjs where lokasibpjs='".$bpjsorg."' or lokasibpjs like 'STAFF%'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
      if($bar['lokasibpjs']=='HO'){
            $arrPrsn['HO'][$bar['jenisbpjs']]['karyawan']=$bar['bebankaryawan'];
            $arrPrsn['HO'][$bar['jenisbpjs']]['perusahaan']=$bar['bebanperusahaan'];
            $arrPrsn['HO'][$bar['jenisbpjs']]['plus']=$bar['jenisbpjsplus'];
            $arrPrsn['HO'][$bar['jenisbpjs']]['maxgaji']=$bar['maxgaji'];
      }
      if(substr($bar['lokasibpjs'],5,5)=='KEBUN'){
            $arrPrsn['KEBUN'][$bar['jenisbpjs']]['karyawan']=$bar['bebankaryawan'];
            $arrPrsn['KEBUN'][$bar['jenisbpjs']]['perusahaan']=$bar['bebanperusahaan'];
            $arrPrsn['KEBUN'][$bar['jenisbpjs']]['plus']=$bar['jenisbpjsplus'];
            $arrPrsn['KEBUN'][$bar['jenisbpjs']]['maxgaji']=$bar['maxgaji'];
      }
      if(substr($bar['lokasibpjs'],5,6)=='PABRIK'){
            $arrPrsn['PABRIK'][$bar['jenisbpjs']]['karyawan']=$bar['bebankaryawan'];
            $arrPrsn['PABRIK'][$bar['jenisbpjs']]['perusahaan']=$bar['bebanperusahaan'];
            $arrPrsn['PABRIK'][$bar['jenisbpjs']]['plus']=$bar['jenisbpjsplus'];
            $arrPrsn['PABRIK'][$bar['jenisbpjs']]['maxgaji']=$bar['maxgaji'];
      }
}

foreach ($umrbulanan as $key => $nilai){
    if($bpjstenaga[$key] != ''){
        foreach ($arrker as $key => $jnspjs){
             #Untuk JKK,JHT, dan JKM jika gaji pokok dibawah UMP (Daerah) maka menggunakan angka UMP. 
            #Jika lebih menggunakan gaji pokok, berlaku untuk payroll HO&Site
            if($nilai<$umpDaerah){
              $nilai=$umpDaerah;
            }
            $gapokdt=$nilai;
            $bar['maxgaji']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['maxgaji'];
            $bar['jenisbpjs']=$jnspjs;
            $bar['jenisbpjsplus']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['plus'];
            $bar['bebanperusahaan']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['perusahaan'];
            $bar['bebankaryawan']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['karyawan'];

            if($bar['maxgaji']!=0){
              if($nilai>$bar['maxgaji']){
                $gapokdt=$bar['maxgaji'];
              }  
            }

           $readyData[] = array(
             'kodeorg' => $kodeho,
             'periodegaji' => $param['periodegaji'],
             'karyawanid' => $key,
             'idkomponen' => $bar['jenisbpjs'],
             'jumlah' => ($bar['bebankaryawan'] / 100 * $gapokdt),
             'pengali' => 1,
             'hk'=>0);

           $readyData[] = array(
             'kodeorg' => $kodeho,
             'periodegaji' => $param['periodegaji'],
             'karyawanid' => $key,
             'idkomponen' => $bar['jenisbpjsplus'],
             'jumlah' => ($bar['bebanperusahaan'] / 100 * $gapokdt),
             'pengali' => 1,
             'hk'=>0); 
        }
    }//end of if
    if($bpjskes[$key]!=''){
        foreach ($arrkes as $key => $jnspjs){
                $bar['maxgaji']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['maxgaji'];
                $bar['jenisbpjs']=$jnspjs;
                $bar['jenisbpjsplus']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['plus'];
                $bar['bebanperusahaan']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['perusahaan'];
                $bar['bebankaryawan']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['karyawan'];
                
                if($bar['maxgaji']!=0){
                    exit('warning: Max Gaji Untuk BPJS Kesehatan Belum tersetting, SDM>Setup>Administrasi ');
                }
                $gapokdt=$bar['maxgaji'];
               $readyData[] = array(
                 'kodeorg' => $kodeho,
                 'periodegaji' => $param['periodegaji'],
                 'karyawanid' => $key,
                 'idkomponen' => $bar['jenisbpjs'],
                 'jumlah' => ($bar['bebankaryawan'] / 100 * $gapokdt),
                 'pengali' => 1,
                 'hk'=>0);

               $readyData[] = array(
                 'kodeorg' => $kodeho,
                 'periodegaji' => $param['periodegaji'],
                 'karyawanid' => $key,
                 'idkomponen' => $bar['jenisbpjsplus'],
                 'jumlah' => ($bar['bebanperusahaan'] / 100 * $gapokdt),
                 'pengali' => 1,
                 'hk'=>0);
        }
    }//END OF IF
     if($bpjspensiun[$key] != ''){
                foreach ($arrpen as $key => $jnspjs){
                    $bar['maxgaji']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['maxgaji'];
                    $bar['jenisbpjs']=$jnspjs;
                    $bar['jenisbpjsplus']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['plus'];
                    $bar['bebanperusahaan']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['perusahaan'];
                    $bar['bebankaryawan']=$arrPrsn[$tipeOrg[$key]][$jnspjs]['karyawan'];
                    #Untuk JP jika gaji pokok dibawah UMP (daerah) maka menggunakan UMP. Jika kurang dari Maks gunakan gaji pokok. 
                    #Jika lebih dari maks maka gunakan maksimal gapok
                    if($nilai<$umpDaerah){
                        $nilai=$umpDaerah;
                    }
                    if($nilai>$bar['maxgaji']){
                        $nilai=$bar['maxgaji'];
                    }
                      $gapokdt=$nilai;
                      if($bar['maxgaji']!=0){
                        if($nilai>$bar['maxgaji']){
                          $gapokdt=$bar['maxgaji'];
                        }
                      }
                   $readyData[] = array(
                     'kodeorg' => $kodeho,
                     'periodegaji' => $param['periodegaji'],
                     'karyawanid' => $key,
                     'idkomponen' => $bar['jenisbpjs'],
                     'jumlah' => ($bar['bebankaryawan'] / 100 * $gapokdt),
                     'pengali' => 1,
                     'hk'=>0);

                   $readyData[] = array(
                     'kodeorg' => $kodeho,
                     'periodegaji' => $param['periodegaji'],
                     'karyawanid' => $key,
                     'idkomponen' => $bar['jenisbpjsplus'],
                     'jumlah' => ($bar['bebanperusahaan'] / 100 * $gapokdt),
                     'pengali' => 1,
                     'hk'=>0); 
             }
          }
}

// #= Ketenagakerjaan JKK
// $str = "select * from " . $dbname . ".sdm_5bpjs where lokasibpjs='".$bpjsorg."'";
// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar = $res->fetch()){
  
//  #= kerja
//  if(in_array($bar['jenisbpjs'],$arrker)){
//    foreach ($umrbulanan as $key => $nilai) {
//      if ($bpjstenaga[$key] != '') {
//         #Untuk JKK,JHT, dan JKM jika gaji pokok dibawah UMP (Daerah) maka menggunakan angka UMP. 
//         #Jika lebih menggunakan gaji pokok, berlaku untuk payroll HO&Site
//         if($nilai<$umpDaerah){
//           $nilai=$umpDaerah;
//         }
//         $gapokdt=$nilai;

//         if($bar['maxgaji']!=0){
//           if($nilai>$bar['maxgaji']){
//             $gapokdt=$bar['maxgaji'];
//           }  
//         }
        
//        $readyData[] = array(
//          'kodeorg' => $kodeho,
//          'periodegaji' => $param['periodegaji'],
//          'karyawanid' => $key,
//          'idkomponen' => $bar['jenisbpjs'],
//          'jumlah' => ($bar['bebankaryawan'] / 100 * $gapokdt),
//          'pengali' => 1,
//          'hk'=>0);
          
//        $readyData[] = array(
//          'kodeorg' => $kodeho,
//          'periodegaji' => $param['periodegaji'],
//          'karyawanid' => $key,
//          'idkomponen' => $bar['jenisbpjsplus'],
//          'jumlah' => ($bar['bebanperusahaan'] / 100 * $gapokdt),
//          'pengali' => 1,
//          'hk'=>0); 
          
//      }
//    }
//  }
  
//  #= kesehatan
//  if(in_array($bar['jenisbpjs'],$arrkes)){
//  #= jika diparameter aplikasi diset 0 maka akan ambil dari gapok
//      foreach ($umrbulanan as $key => $nilai) {
//        if ($bpjskes[$key] != '') {
//           $gapokdt=$nilai;
//           if($bar['maxgaji']!=0){
//               exit('warning: Max Gaji Untuk BPJS Kesehatan Belum tersetting, SDM>Setup>Administrasi ')
//           }
//          $readyData[] = array(
//            'kodeorg' => $kodeho,
//            'periodegaji' => $param['periodegaji'],
//            'karyawanid' => $key,
//            'idkomponen' => $bar['jenisbpjs'],
//            'jumlah' => ($bar['bebankaryawan'] / 100 * $gapokdt),
//            'pengali' => 1,
//            'hk'=>0);
        
//          $readyData[] = array(
//            'kodeorg' => $kodeho,
//            'periodegaji' => $param['periodegaji'],
//            'karyawanid' => $key,
//            'idkomponen' => $bar['jenisbpjsplus'],
//            'jumlah' => ($bar['bebanperusahaan'] / 100 * $gapokdt),
//            'pengali' => 1,
//            'hk'=>0);
//        }
        
//      }
    
//  }
  
//  #= pensiun
//  if(in_array($bar['jenisbpjs'],$arrpen)){
//    foreach ($umrbulanan as $key => $nilai) {
//      if ($bpjspensiun[$key] != '') {
//         #Untuk JP jika gaji pokok dibawah UMP (daerah) maka menggunakan UMP. Jika kurang dari Maks gunakan gaji pokok. 
//         #Jika lebih dari maks maka gunakan maksimal gapok
//         if($nilai<$umpDaerah){
//             $nilai=$umpDaerah;
//         }
//         if($nilai>$bar['maxgaji']){
//             $nilai=$bar['maxgaji'];
//         }
//           $gapokdt=$nilai;
//           if($bar['maxgaji']!=0){
//             if($nilai>$bar['maxgaji']){
//               $gapokdt=$bar['maxgaji'];
//             }
//           }
//        $readyData[] = array(
//          'kodeorg' => $kodeho,
//          'periodegaji' => $param['periodegaji'],
//          'karyawanid' => $key,
//          'idkomponen' => $bar['jenisbpjs'],
//          'jumlah' => ($bar['bebankaryawan'] / 100 * $gapokdt),
//          'pengali' => 1,
//          'hk'=>0);
          
//        $readyData[] = array(
//          'kodeorg' => $kodeho,
//          'periodegaji' => $param['periodegaji'],
//          'karyawanid' => $key,
//          'idkomponen' => $bar['jenisbpjsplus'],
//          'jumlah' => ($bar['bebanperusahaan'] / 100 * $gapokdt),
//          'pengali' => 1,
//          'hk'=>0); 
//      }
//    }
//  }
// }


###################################################################################################################################################
###################################################################################################################################################
###################################################################################################################################################


// echo"<pre>";
// print_r($umrbulanan);
// echo"</pre>";
//calculate to component
$strx = "select id as komponen, case plus when 0 then -1 else plus end as pengali,name as nakomp 
              FROM " . $dbname . ".sdm_ho_component";
$comRes = fetchData($strx);
$comp = array();
$nakomp = array();
foreach ($comRes as $idx => $row) {
    $comp[$row['komponen']] = $row['pengali'];
    $nakomp[$row['komponen']] = $row['nakomp'];
}

//=tampilan  ============================
$listbutton = "<button class=mybuttton name=postBtn id=postBtn onclick=post()>Proses</button>";
$list0 = "<table class=sortable border=0 cellspacing=1>
                     <thead>
                     <tr class=rowheader>";
$list0 .= "<td>" . $_SESSION['lang']['nomor'] . "</td>";
$list0 .= "<td>" . $_SESSION['lang']['periodegaji'] . "</td>";
$list0 .= "<td>" . $_SESSION['lang']['karyawanid'] . "</td>";
$list0 .= "<td>" . $_SESSION['lang']['tipe'] . "</td>";
$list0.= "<td>" . $_SESSION['lang']['jumlah'] . "</td></tr></thead><tbody>";

//periksa gaji minus
$negatif = false;
$list1 = '';
$listx = "Masih ada gaji dibawah 0:";
$list2 = '';
$list3 = '';
$no = 0;

// echo"<pre>";
// print_r($readyData);
// echo"</pre>";
foreach ($id as $key => $val) {
    $sisa[$val[0]] = 0;

    foreach ($readyData as $dat => $bar) {
        if ($val[0] == $bar['karyawanid']) {
            $sisa[$val[0]]+=$bar['jumlah'] * $comp[$bar['idkomponen']];
            // tambahan pph21
            /* if (in_array($bar['idkomponen'], $komponenkenapajak)){
              setIt($gajikenapajak[$val[0]],0);
              $gajikenapajak[$val[0]]+=$bar['jumlah']*$comp[$bar['idkomponen']];
              }
              if($bar['idkomponen']=='1'){
              $gapok[$val[0]]=$bar['jumlah'];
              //$dJMS[$val[0]]=$bar['jumlah']*$plusJMS/100;
              } */
            // endof tambahan pph21  
        } else
            continue;
    }


    if ($sisa[$val[0]] < 0) {
        $list1 .="<tr class=rowcontent>";
        $list1 .= "<td>-</td>";
        $list1 .= "<td>" . $param['periodegaji'] . "</td>";
        $list1 .= "<td>" . $namakar[$val[0]] . "</td>";
        $list1 .= "<td>" . $nmtpkar[$tipekaryawan[$val[0]]] . "</td>";
        $list1 .= "<td>" . number_format($sisa[$val[0]], 0, ',', '.') . "</td>";
        $list1 .= "</tr>";
        $negatif = true;
    } else {
        $no+=1;
        $list2 .="<tr class=rowcontent>";
        $list2 .= "<td>" . $no . "</td>";
        $list2 .= "<td>" . $param['periodegaji'] . "</td>";
        $list2 .= "<td>" . $namakar[$val[0]] . "</td>";
        $list2 .= "<td>" . $nmtpkar[$tipekaryawan[$val[0]]] . "</td>";
        $list2 .= "<td align=right>" . number_format($sisa[$val[0]], 0, ',', '.') . "</td>";
        $list2 .= "</tr>";
    }
}
$list3 = "</tbody><table>";

switch ($proses) {
    case 'list':
        if ($negatif)
            echo $listx . $list0 . $list1 . $list3;
        else
            echo $listbutton . $list0 . $list2 . $list3;
        break;
    case 'post':
        #delete first
        $str = "delete from " . $dbname . ".sdm_gajiho 
        where idkomponen not in ('26','28') and periodegaji='" . $param['periodegaji'] . "' and 
        karyawanid in (select karyawanid from ".$dbname.".datakaryawan where kodeorganisasi='".$param['kodeorg']."')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n".$str;
            die();
        }
        # Insert All ready data
        $insError = "";
        foreach ($readyData as $row) {
      //exit("Error".$row['hk']);
            if ($row['jumlah'] == 0 or $row['jumlah'] == '' or $row['karyawanid']=='') {
                continue;
            } else {
        #= delete again
        $str="delete from ".$dbname.".sdm_gajiho where idkomponen='".$row['idkomponen']."' 
            and karyawanid='".$row['karyawanid']."' and periodegaji='".$row['periodegaji']."' ";
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
          print " Gagal  !: " . $e->getMessage() . "\n".$str; 
          die(); 
        }
                $str = insertQuery($dbname, 'sdm_gajiho', $row); 
                try{
                  $owlPDO->exec($str);
                }
                catch(PDOException $e){
                  echo "Gagal : " . $e->getMessage() . "\n".$str;
                  die();
                }
            }
        }
        break;
    default:
        break;
}

?>