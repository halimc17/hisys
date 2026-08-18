<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses = $_GET['proses'];
$param = $_POST;
$namakar = Array();

$optTipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$tipeOrg = $optTipe[$param['kodeorg']];


$str = "select * from " . $dbname . ".bgt_regional_assignment where kodeunit LIKE '" . $param['kodeorg'] . "%'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $regional = $bar->regional;
    $unit = $bar->kodeunit;
}

#cek tutup atau belum periode gaji
$sCekPeriode = "select distinct * from " . $dbname . ".sdm_5periodegaji where periode='" . $param['periodegaji'] . "' 
              and kodeorg='" . $param['kodeorg'] . "' and sudahproses=1 and jenisgaji='H'";
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
             kodeorg='" . $param['kodeorg'] . "' and tutupbuku=1";
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




# Get Period Range
$qPeriod = selectQuery($dbname, 'sdm_5periodegaji', 'tanggalmulai,tanggalsampai', "periode='" . $param['periodegaji'] . "' and kodeorg='" .
        $param['kodeorg'] . "' and jenisgaji='H'");
$resPeriod = fetchData($qPeriod);
@$tanggal1 = $resPeriod[0]['tanggalmulai'];
@$tanggal2 = $resPeriod[0]['tanggalsampai'];

# Hapus tranasksi yang nomor BKM-nya salah
$str = "delete from " . $dbname . ".kebun_aktifitas where notransaksi like '%//%'";
$owlPDO->exec($str);


##indra harus cek ulang di tipekaryawan
#2. Get Karyawan harian yang penggajian=Harian dan alokasi=0
$query1 = selectQuery($dbname, 'datakaryawan', 'statuspajak,karyawanid,tipekaryawan,namakaryawan,jms,bpjs', "tipekaryawan in(2,3,4,6) and " .
        "lokasitugas='" . $param['kodeorg'] . "' and " .
        "(tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and alokasi=0 and sistemgaji='Harian'" .
        " and ( tanggalmasuk<='" . $tanggal2 . "' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)");


$absRes = fetchData($query1);

# Error empty karyawan
if (empty($absRes)) {
    echo "Error : There is no presence(kehadiran) on this period";
    exit();
} else {
    $id = Array();
    foreach ($absRes as $row => $kar) {
        $id[$kar['karyawanid']][] = $kar['karyawanid'];
        $statuskar[$kar['karyawanid']] = $kar['statuspajak'];
        $namakar[$kar['karyawanid']] = $kar['namakaryawan'];
        #mengambil no Jamsostek
        #bpjstenaga
        $bpjstenaga[$kar['karyawanid']] = trim($kar['jms']);

        #bpjskes
        $bpjskes[$kar['karyawanid']] = trim($kar['bpjs']);


        if ($kar['tipekaryawan'] == 2)
            $tipekaryawan[$kar['karyawanid']] = 'Kontrak';
        else if ($kar['tipekaryawan'] == 3)
            $tipekaryawan[$kar['karyawanid']] = 'KHT';
        else if ($kar['tipekaryawan'] == 4)
            $tipekaryawan[$kar['karyawanid']] = 'PHL';
        else if ($kar['tipekaryawan'] == 6)
            $tipekaryawan[$kar['karyawanid']] = 'Kontrak Karya';
        else
            $tipekaryawan[$kar['karyawanid']] = 'Magang';
    }
}

# ambil gaji pokok per hari untuk KHT
$strgjh = "select a.karyawanid,sum(jumlah)/25 as gjperhari,sum(jumlah) as umrbulanan,b.tipekaryawan from " . $dbname . ".sdm_5gajipokok a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where a.tahun=" . substr($tanggal1, 0, 4) . " and b.tipekaryawan in(2,3,6,4) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.idkomponen in(1,2,31) and sistemgaji='Harian'
               group by a.karyawanid";

$resgjh = fetchData($strgjh);
foreach ($resgjh as $idx => $val) {
    $gajiperhari[$val['karyawanid']] = $val['gjperhari'];
    $umrbulanan[$val['karyawanid']] = $val['umrbulanan'];
}
//Jika periode gaji lebih dari sebulan, maka kelebihannya ditambah sesuai dengan gaji  harian            
//==========hitung selisih hari
$t1 = $tanggal1 . " 00:00:01"; //awal
$t2 = $tanggal2 . " 23:59:59"; //sampai
$endd = strtotime($t2);
$startd = strtotime($t1);
$jumlahh = round(abs($endd - $startd) / 60 / 60 / 24);
//ambil jumlah hari periode gaji ( jumlah hari satu bulan)
$pengurang = date('t', $startd);
//=======================================================================  
#ambil jumlah hk tidak dibayar untuk KHT dan total tidak dibayar
$strgjh = "select  count(*) as jlh,b.karyawanid from " . $dbname . ".sdm_hktdkdibayar_vw a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(2,3,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
               and sistemgaji='Harian'
               group by a.karyawanid";

$tdkdibayar = Array();
$resgjh = fetchData($strgjh);
foreach ($resgjh as $idx => $val) {
    $tdkdibayar[$val['karyawanid']] = $gajiperhari[$val['karyawanid']] * $val['jlh']; #jumlah tidak dibayar
    //koreksi untuk memindahkan potongan hk dari gaji pokok ke komponen potongan hk
    //seperti yang diterangkan pada escape dibawah  
    $readyData[] = array(
        'kodeorg' => $param['kodeorg'],
        'periodegaji' => $param['periodegaji'],
        'karyawanid' => $val['karyawanid'],
        'idkomponen' => 37, //potongan hk
        'jumlah' => $tdkdibayar[$val['karyawanid']],
        'pengali' => 1);
}


#tambahan absensi yang tidak dicatat sebagai penambah hk tidak dibayar==========added by ginting
#ambil hari-hari dapam periode berjalan  
if ($proses == 'list') {
    #ambil absensi dari bkm=================
    $strux = "select a.karyawanid,tanggal from " . $dbname . ".kebun_kehadiran_vw a left join 
                     " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
                      where b.tipekaryawan in(1,2,3,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
                      and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
                      and a.unit like '" . $param['kodeorg'] . "%' 
                      and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
                       order by tanggal";
    $resux = $owlPDO->query($strux) or die(print " Gagal: " . PDOException::getMessage());
    $resux->setFetchMode(PDO::FETCH_OBJ);
    while ($baux = $resux->fetch()) {
        $periksa[$baux->karyawanid][$baux->tanggal] = $baux->tanggal;
    }
    #ambil absensi dari panen=================
    $strux = "select a.karyawanid,tanggal from " . $dbname . ".kebun_prestasi_vw a left join 
                     " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
                      where b.tipekaryawan in(1,2,3,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
                      and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
                      and a.unit like '" . $param['kodeorg'] . "%' 
                      and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
                      order by tanggal";
    $resux = $owlPDO->query($strux) or die(print " Gagal: " . PDOException::getMessage());
    $resux->setFetchMode(PDO::FETCH_OBJ);
    while ($baux = $resux->fetch()) {
        $periksa[$baux->karyawanid][$baux->tanggal] = $baux->tanggal;
    }
    #ambil absensi dari traksi=================
    $strux = "select a.idkaryawan as karyawanid,tanggal
                  from " . $dbname . ".vhc_runhk_vw a left join 
                 " . $dbname . ".datakaryawan b on a.idkaryawan=b.karyawanid
                  where b.tipekaryawan in(1,2,3,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
                  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
                  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
                  order by tanggal";
    $resux = $owlPDO->query($strux) or die(print " Gagal: " . PDOException::getMessage());
    $resux->setFetchMode(PDO::FETCH_OBJ);
    while ($baux = $resux->fetch()) {
        $periksa[$baux->karyawanid][$baux->tanggal] = $baux->tanggal;
    }
    #absensi dari absen
    $strux = "select a.karyawanid,tanggal
                  from " . $dbname . ".sdm_absensidt a left join 
                 " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
                  where b.tipekaryawan in(1,2,3,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
                  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
                  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
                  order by tanggal";
    $resux = $owlPDO->query($strux) or die(print " Gagal: " . PDOException::getMessage());
    $resux->setFetchMode(PDO::FETCH_OBJ);
    while ($baux = $resux->fetch()) {
        $periksa[$baux->karyawanid][$baux->tanggal] = $baux->tanggal;
    }
	
	#ambil absen krani,mandor dari header BKM sipil
          $strux="select tanggal,mandor,mandor1,krani 
                  from ".$dbname.".vhc_splht where kodeorg='".$param['kodeorg']."'
                  and  tanggal>='".$tanggal1."' and tanggal<='".$tanggal2."'     
                  order by tanggal"; 
			$resux = $owlPDO->query($strux) or die(print " Gagal: " . PDOException::getMessage());
			$resux->setFetchMode(PDO::FETCH_OBJ);
			while ($baux = $resux->fetch()) {
              $periksa[$baux->mandor][$baux->tanggal]=$baux->tanggal;
              $periksa[$baux->mandor1][$baux->tanggal]=$baux->tanggal;
              $periksa[$baux->krani][$baux->tanggal]=$baux->tanggal;
          }
	
	
    #ambil absen krani,mandir dari header BKM dan Panen
    $strux = "select tipetransaksi,tanggal,nikmandor,nikmandor1,nikasisten,keranimuat
                  from " . $dbname . ".kebun_aktifitas where kodeorg='" . $param['kodeorg'] . "'
                  and  tanggal>='" . $tanggal1 . "' and tanggal<='" . $tanggal2 . "'     
                  order by tanggal";
    $resux = $owlPDO->query($strux) or die(print " Gagal: " . PDOException::getMessage());
    $resux->setFetchMode(PDO::FETCH_OBJ);
    while ($baux = $resux->fetch()) {

        $periksa[$baux->nikmandor][$baux->tanggal] = $baux->tanggal;
        $periksa[$baux->nikmandor1][$baux->tanggal] = $baux->tanggal;
        $periksa[$baux->nikasisten][$baux->tanggal] = $baux->tanggal;
        $periksa[$baux->keranimuat][$baux->tanggal] = $baux->tanggal;
    }
    #ambil data karyawan KBL,KHT dan Kontrak yang masih aktif
	#update kht dihilangkan sesuai analisa
    $queryq = selectQuery($dbname, 'datakaryawan', 'karyawanid,namakaryawan,subbagian', "tipekaryawan in(2,6) and 
           lokasitugas='" . $param['kodeorg'] . "' and 
           (tanggalkeluar>='" . $tanggal2 . "' or tanggalkeluar='0000-00-00') and alokasi=0  
           and ( tanggalmasuk<='" . $tanggal1 . "' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)");
    $absResq = fetchData($queryq);
    #periksa apakah masih ada absensi yang belum diinput
    $kotak = "";
    $nx = 0;
    // foreach ($absResq as $rowq => $karq) {
    //     if (@count($periksa[$karq['karyawanid']]) < $jumlahh) {
    //         $nx++;
    //         $kotak.=$nx . "." . $karq['namakaryawan'] . "-" . $karq['subbagian'] . "\n";
    //     }
    // }
    // if ($kotak != '') {
    //     exit("Error:[ENG] There are absences that have not been recorded in the name of the following employees:"
    //             . "\n[IND] Masih ada karyawan yang belum memiliki absen (selain PHL)\n\n " . $kotak);
    // }
}
#end periksa absensi===========================================================================  
#2 Get Jamsostek porsi==========================
#ambil semua komponen dari gajipokok khusus KHT dan Kontrak Harian=====================
$str1 = "select a.*,b.namakaryawan from " . $dbname . ".sdm_5gajipokok a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where a.tahun=" . substr($tanggal1, 0, 4) . " and b.tipekaryawan in(2,3,6,4) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0    
               and sistemgaji='Harian'";
$res1 = fetchData($str1);
$query6 = selectQuery($dbname, 'sdm_ho_hr_jms_porsi', 'value', "id='karyawan'");
$jmsRes = fetchData($query6);
$persenJms = $jmsRes[0]['value'] / 100;
$tjms = Array();
foreach ($res1 as $idx => $val) {
    #KHT dan Kontrak Harian
    if ($id[$val['karyawanid']][0] == $val['karyawanid'] and ( $tipekaryawan[$val['karyawanid']] == 'KHT' or $tipekaryawan[$val['karyawanid']] == 'Kontrak' or $tipekaryawan[$val['karyawanid']] == 'Kontrak Karya')) {

        // #kurangkan pemotongan HK tidak dibayar
//            if (array_key_exists($val['karyawanid'], $tdkdibayar)){ 
//                 if($val['idkomponen']=='1')#kurangkan hanya pada gaji pokok
//                 $val['jumlah']=$val['jumlah']-$tdkdibayar[$val['karyawanid']];#pengurangan HK tidak dibayar
//             }
        //Khusus KHT dan Kontrak harian tidak lagi dipotong pada gaji pokok, tetapi ditambah pada potongan HK diatas 
        //==============================================================================================================================
        //filter jumlah hari                
        if ($val['idkomponen'] == 1 and $jumlahh > $pengurang) {
            $selisih = $jumlahh - $pengurang;
            $pengurangkelebihanminggu = floor($selisih / 7);
            $bersih = $selisih - $pengurangkelebihanminggu;
            $val['jumlah']+=$gajiperhari[$val['karyawanid']] * $bersih; //nilai gajipokok diubah ditambah kelebihan hari
        }
        if ($val['idkomponen'] == 1 and $jumlahh < $pengurang) {
            $selisih = $pengurang - $jumlahh;
            $pengurangkelebihanminggu = floor($selisih / 7);
            $bersih = $selisih - $pengurangkelebihanminggu;
            $val['jumlah']-=$gajiperhari[$val['karyawanid']] * $bersih; //nilai gajipokok diubah dikurang kekurangan hari                   
        }
        //============================== 
        #add to ready data================================================
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $val['karyawanid'],
            'idkomponen' => $val['idkomponen'],
            'jumlah' => $val['jumlah'],
            'pengali' => 1);
        if ($val['idkomponen'] == 1 or $val['idkomponen'] == 2 or $val['idkomponen'] == 30 or $val['idkomponen'] == 31) { #ambil,
            #tunjangan jabatan
            #tunjangan masakerja
            #tunjangan Provesi
            #gaji pokok
            if (@$nojms[$val['karyawanid']] != '') {#jika No. JMS diisi maka ada potongan jamsostek
                setIt($tjms[$val['karyawanid']], 0);
                $tjms[$val['karyawanid']]+=$val['jumlah'];
            }
        }
    } else {
        #BHL
        //diabaikan yang dari gaji pokok 
    }


    #bentuk BPJS di sini
    if ($val['idkomponen'] == 1) {
        if (@$nobpjs[$val['karyawanid']] != '') {
            setIt($bpjs[$val['karyawanid']], 0);
            $bpjs[$val['karyawanid']] = $val['jumlah'];
        }
    }
}


#ambil komponen pengurang dari sdm_5gajipokok untuk PHL
$strphl = "select a.*,b.namakaryawan from " . $dbname . ".sdm_5gajipokok a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where a.tahun=" . substr($tanggal1, 0, 4) . " and b.tipekaryawan='4' and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0    
               and sistemgaji='Harian' and idkomponen in (select id from " . $dbname . ".sdm_ho_component where plus=0 and type='basic')";
$resphl = fetchData($strphl);
foreach ($resphl as $idx => $val) {
    $readyData[] = array(
        'kodeorg' => $param['kodeorg'],
        'periodegaji' => $param['periodegaji'],
        'karyawanid' => $val['karyawanid'],
        'idkomponen' => $val['idkomponen'], //potongan hk
        'jumlah' => $val['jumlah'],
        'pengali' => 1);
}





###################update indra ini sudah tidak dipakai lagi, pembentukan di bawah semua
#### 8 januari 2015  
#masukin BPJS ke sini
/* foreach($bpjs as $key=>$nilai){
  #add bpjs to ready data====================================
  $readyData[] = array(
  'kodeorg'=>$param['kodeorg'],
  'periodegaji'=>$param['periodegaji'],
  'karyawanid'=>$key,
  'idkomponen'=>44,
  'jumlah'=>($nilai/100),
  'pengali'=>1);

  } */
/* foreach($tjms as $key=>$nilai){
  #add jamsostek to ready data====================================
  if($tipekaryawan[$key]=='KHT' or $tipekaryawan[$key]=='Kontrak'){
  $readyData[] = array(
  'kodeorg'=>$param['kodeorg'],
  'periodegaji'=>$param['periodegaji'],
  'karyawanid'=>$key,
  'idkomponen'=>3,
  'jumlah'=>($nilai* $persenJms),//'jumlah'=>(($nilai+$tdkdibayar[$key])* $persenJms),
  'pengali'=>1);
  }
  } */


#3. Get Lembur Data
$where2 = " a.kodeorg like '" . $param['kodeorg'] . "%' and (tanggal>='" .
        $tanggal1 . "' and tanggal<='" . $tanggal2 . "')";
$query2 = "select a.karyawanid,sum(a.uangkelebihanjam) as lembur from " . $dbname . ".sdm_lemburdt a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and sistemgaji='Harian'                  
               and " . $where2 . " group by a.karyawanid";
$lbrRes = fetchData($query2);
foreach ($lbrRes as $idx => $row) {
    if (isset($id[$row['karyawanid']])) {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $row['karyawanid'],
            'idkomponen' => 33,
            'jumlah' => $row['lembur'],
            'pengali' => 1);
    } else {
        //abaikan jika tidak terdaftar pada karyawanid  
    }
}



#4. Get Potongan Data============================================================
$where3 = " kodeorg='" . $param['kodeorg'] . "' and periodegaji='" .
        $param['periodegaji'] . "'";
//$query3 = selectQuery($dbname,'sdm_potongandt','nik,sum(jumlahpotongan) as potongan',$where3)." group by nik";
$query3 = "select a.nik as karyawanid,sum(jumlahpotongan) as potongan,tipepotongan from " . $dbname . ".sdm_potongandt a left join 
              " . $dbname . ".datakaryawan b on a.nik=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and sistemgaji='Harian'                   
               and " . $where3 . " group by a.nik,a.tipepotongan";
$potRes = fetchData($query3);
foreach ($potRes as $idx => $row) {
    if (isset($id[$row['karyawanid']])) {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $row['karyawanid'],
            'idkomponen' => $row['tipepotongan'],
            'jumlah' => $row['potongan'],
            'pengali' => 1);
    } else {
        //abaikan jika tidak terdaftar pada karyawanid  
    }
}


#5. Get Angsuran Data==========================================================
$where4 = " start<='" . $param['periodegaji'] . "' and end>='" . $param['periodegaji'] . "'";
//$query4 = selectQuery($dbname,'sdm_angsuran','karyawanid,bulanan,jenis',$where4)." group by karyawanid";
$query4 = "select a.karyawanid,a.bulanan,a.jenis from " . $dbname . ".sdm_angsuran a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.active=1    
               and sistemgaji='Harian'                    
               and " . $where4;
$angRes = fetchData($query4);
foreach ($angRes as $idx => $row) {
    if ($id[$row['karyawanid']][0] == $row['karyawanid']) {
        #add to ready data================================================
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $row['karyawanid'],
            'idkomponen' => $row['jenis'],
            'jumlah' => $row['bulanan'],
            'pengali' => 1);
    }
}

#6 Premi dan penalty =======================================================================
#6.0 periksa posting transaksi
#posting perawatan
$stru1 = "select distinct(a.tanggal), a.notransaksi from " . $dbname . ".kebun_kehadiran_vw a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.unit like '" . $param['kodeorg'] . "%' and a.jurnal=0
               and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
               and sistemgaji='Harian' order by tanggal";

#posting panen
$stru2 = "select distinct(a.tanggal), a.notransaksi from " . $dbname . ".kebun_prestasi_vw a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.unit like '" . $param['kodeorg'] . "%' and a.jurnal=0
               and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
               and sistemgaji='Harian' order by tanggal";

#posting traksi
$stru3 = "select distinct(a.tanggal), a.notransaksi
           from " . $dbname . ".vhc_runhk_vw a left join 
          " . $dbname . ".datakaryawan b on a.idkaryawan=b.karyawanid
           where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
           and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
           and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
           and posting=0 and sistemgaji='Harian' order by tanggal";

$resu1 = $owlPDO->query($stru1) or die(print " Gagal: " . PDOException::getMessage());
$resu1->setFetchMode(PDO::FETCH_OBJ);
$numrows1 = owlBaris($resu1);

$resu2 = $owlPDO->query($stru2) or die(print " Gagal: " . PDOException::getMessage());
$resu2->setFetchMode(PDO::FETCH_OBJ);
$numrows2 = owlBaris($resu2);

$resu3 = $owlPDO->query($stru3) or die(print " Gagal: " . PDOException::getMessage());
$resu3->setFetchMode(PDO::FETCH_OBJ);
$numrows3 = owlBaris($resu3);

if ($numrows1 > 0 or $numrows2 > 0 or $numrows3 > 0) {
    echo"Masih ada data yang belum di posting/There still unconfirmed transaction:";
    echo"<table class=sortable border=0 cellspacing=1>
            <thead><tr class=rowheader>
            <td>" . $_SESSION['lang']['jenis'] . "</td>
            <td>" . $_SESSION['lang']['notransaksi'] . "</td>
            <td>" . $_SESSION['lang']['tanggal'] . "</td>
            </tr></thead><tbody>";
    while ($bar = $resu1->fetch()) {
        echo"<tr class=rowcontent><td>Perawatan Kebun</td><td>" . $bar->notransaksi . "</td><td>" . tanggalnormal($bar->tanggal) . "</td></tr>";
    }
    while ($bar = $resu2->fetch()) {
        echo"<tr class=rowcontent><td>Panen</td><td>" . $bar->notransaksi . "</td><td>" . tanggalnormal($bar->tanggal) . "</td></tr>";
    }
    while ($bar = $resu3->fetch()) {
        echo"<tr class=rowcontent><td>Traksi Pekerjaan</td><td>" . $bar->notransaksi . "</td><td>" . tanggalnormal($bar->tanggal) . "</td></tr>";
    }
    echo "</tbody><tfoot></tfoot></table>";
    exit(); //keluar dari proses
}


#6.3.1 Get Premi Kegiatan Perawatan dan gaji pokok BHL
$premi = Array();
$penalty = Array();
$gapokbhl = Array();
$penaltykehadiran = Array();
$query5 = "select sum(a.umr) as gaji,a.karyawanid,sum(a.insentif) as premi from " . $dbname . ".kebun_kehadiran_vw a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.unit like '" . $param['kodeorg'] . "%'
               and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
               and sistemgaji='Harian' group by a.karyawanid";

$premRes = fetchData($query5);
foreach ($premRes as $idx => $val) {
    if ($val['premi'] > 0)
        $premi[$val['karyawanid']] = $val['premi'];
    #gapok KHL
    if ($tipekaryawan[$val['karyawanid']] == 'PHL') {
        if (empty($gapokbhl[$val['karyawanid']]))
            $gapokbhl[$val['karyawanid']] = $val['gaji'];
        else
            $gapokbhl[$val['karyawanid']]+=$val['gaji'];
    }
}

//echo"</pre>";         
#6.3.2 Get Premi Kegiatan Panen    dan gaji pokok BHL
$query6 = "select a.tanggal, sum(a.upahkerja) as upahkerja,a.karyawanid,sum(a.upahpremi+a.premibasis) as premi,sum(a.rupiahpenalty+a.upahpenalty) as penalty 
               from " . $dbname . ".kebun_prestasi_vw a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.unit like '" . $param['kodeorg'] . "%' 
               and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
               and sistemgaji='Harian' group by a.tanggal, a.karyawanid";

//exit("Error:$query6");

$premRes1 = fetchData($query6);
foreach ($premRes1 as $idx => $val) {
    // cari hari
    $day = date('D', strtotime($val['tanggal']));
    if ($day == 'Sun')
        $libur = true;
    else
        $libur = false;
    // kamus hari libur
    $strorg = "select * from " . $dbname . ".sdm_5harilibur where tanggal = '" . $val['tanggal'] . "'";
    $queorg = $owlPDO->query($strorg) or die(print " Gagal: " . PDOException::getMessage());
    $queorg->setFetchMode(PDO::FETCH_ASSOC);
    while ($roworg = $queorg->fetch()) {
//                $libur=true;
        if ($roworg['keterangan'] == 'libur')
            $libur = true;
        if ($roworg['keterangan'] == 'masuk')
            $libur = false;
    }

    if ($val['premi'] > 0) {
        if (isset($premi[$val['karyawanid']]))
            $premi[$val['karyawanid']]+=$val['premi'];
        else
            $premi[$val['karyawanid']] = $val['premi'];
    }
    if ($val['penalty'] > 0) {
        if (isset($penalty[$val['karyawanid']]))
            $penalty[$val['karyawanid']]+=$val['penalty'];
        else
            $penalty[$val['karyawanid']] = $val['penalty'];
    }
    //$penalty[$val['karyawanid']]=$val['penalty'];
    #gapok KHL
    if ($tipekaryawan[$val['karyawanid']] == 'PHL') {
        if (empty($gapokbhl[$val['karyawanid']]))
            $gapokbhl[$val['karyawanid']] = $val['upahkerja'];
        else
            $gapokbhl[$val['karyawanid']]+=$val['upahkerja'];
    }
    //indrakhl
    // kalo hari kerja itung biasa
    if ($libur == false) {
        
    } else {// kalo hari libur dianggap kontanan? (masuk ke pengurang)
        if ($val['premi'] > 0) {
            if (isset($premikontanan[$val['karyawanid']]))
                $premikontanan[$val['karyawanid']]+=$val['premi'];
            else
                $premikontanan[$val['karyawanid']] = $val['premi'];
        }
        if ($val['penalty'] > 0) {//$penalty[$val['karyawanid']]=$val['penalty'];
            if (isset($premikontanan[$val['karyawanid']]))
                $premikontanan[$val['karyawanid']]-=$val['penalty'];
            else
                $premikontanan[$val['karyawanid']] = $val['penalty'];
        }
    }
}
#6.3.3 Get Premi Transport dan gaji pokok BHL
$query7 = "select sum(a.upah) as upah,a.idkaryawan as karyawanid,sum(a.premi) as premi,sum(a.penalty) as penalty 
               from " . $dbname . ".vhc_runhk_vw a left join 
              " . $dbname . ".datakaryawan b on a.idkaryawan=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and substr(a.notransaksi,1,4)='" . $param['kodeorg'] . "' 
               and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
               and sistemgaji='Harian' group by a.idkaryawan";
$premRes2 = fetchData($query7);
foreach ($premRes2 as $idx => $val) {
    if ($val['premi'] > 0) {
        if (isset($premi[$val['karyawanid']]))
            $premi[$val['karyawanid']]+=$val['premi'];
        else
            $premi[$val['karyawanid']] = $val['premi'];
    }
    if ($val['penalty'] > 0) {
        if (isset($penalty[$val['karyawanid']]))
            $penalty[$val['karyawanid']]+=$val['penalty'];
        else
            $penalty[$val['karyawanid']] = $val['penalty'];
    }
    #gapok KHL
    /*   BHL sudah dapat gaji pokok dari absensi, karena vhc_runhk sudah otomatis masuk ke sdm_absensi
      if($tipekaryawan[$val['karyawanid']]=='BHL')
      {
      if(empty ($gapokbhl[$val['karyawanid']]))
      $gapokbhl[$val['karyawanid']]=$val['upah'];
      else
      $gapokbhl[$val['karyawanid']]+=$val['upah'];
      }
     * 
     */
}
#6.3.4 Get Premi Kemandoran
$query8 = "select sum(a.premiinput) as premi,a.karyawanid
               from " . $dbname . ".kebun_premikemandoran a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.kodeorg='" . $param['kodeorg'] . "'  
               and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
               and b.sistemgaji='Harian'  and a.posting=1                   
               group by a.karyawanid";

$premRes2 = fetchData($query8);
foreach ($premRes2 as $idx => $val) {
    if ($val['premi'] > 0) {
        if (isset($premi[$val['karyawanid']]))
            $premi[$val['karyawanid']]+=$val['premi'];
        else
            $premi[$val['karyawanid']] = $val['premi'];
    }
}



		$query7="select sum(a.umr) as upah,a.nik as karyawanid,sum(a.premi) as premi from ".$dbname.".vhc_spl_kehadiran_vw a left join 
                ".$dbname.".datakaryawan b on a.nik=b.karyawanid
                where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='".$param['kodeorg']."' and 
                (b.tanggalkeluar>='".$tanggal1."' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
                and substr(a.notransaksi,1,4)='".$param['kodeorg']."' 
                and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'     
                and sistemgaji='Harian' group by a.nik";
        $premRes2 = fetchData($query7); 
        foreach($premRes2 as $idx => $val){
             if($val['premi']>0){   
                 if(isset ($premi[$val['karyawanid']]))
                     $premi[$val['karyawanid']]+=$val['premi'];
                 else
                     $premi[$val['karyawanid']]=$val['premi'];
             }
             if($tipekaryawan[$val['karyawanid']]=='PHL'){   
                if(empty ($gapokbhl[$val['karyawanid']]))
                    $gapokbhl[$val['karyawanid']]=$val['upah'];
                else
                    $gapokbhl[$val['karyawanid']]+=$val['upah'];  
              } 
        }


#gapok BHL dari absensi===================================
$strup = "select a.karyawanid,(b.jumlah/25) as upahabsen,absensi FROM " . $dbname . ".sdm_absensidt_vw a 
                left join " . $dbname . ".sdm_5gajipokok b on a.karyawanid=b.karyawanid and nilaihk=1
                and b.idkomponen=1 
               where b.tahun=" . substr($tanggal1, 0, 4) . " and substr(a.kodeorg,1,4)='" . $param['kodeorg'] . "' 
               and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' 
               and a.tipekaryawan=4 and a.absensi='H'";


$resup = fetchData($strup);
foreach ($resup as $idx => $val) {
    if (empty($gapokbhl[$val['karyawanid']]))
        $gapokbhl[$val['karyawanid']] = $val['upahabsen'];
    else
        $gapokbhl[$val['karyawanid']]+=$val['upahabsen'];
}
#add gapok BHL to ready data
foreach ($gapokbhl as $key => $val) {
    if ($val > 0) {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $key,
            'idkomponen' => 1, #kode komponen gapok
            'jumlah' => $val,
            'pengali' => 1);
    }
}

#premi tetap dari absensi==========================================
$stkh = "select a.karyawanid,sum(a.premi+a.insentif) as premi from " . $dbname . ".sdm_absensidt a 
                left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
                          where b.tipekaryawan in(2,3,4,6)  and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.kodeorg like '" . $param['kodeorg'] . "%' and sistemgaji='Harian'  
               and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' group by a.karyawanid";
$reskh = $owlPDO->query($stkh) or die(print " Gagal: " . PDOException::getMessage());
$reskh->setFetchMode(PDO::FETCH_OBJ);
while ($barky = $reskh->fetch()) {
    if (isset($premi[$barky->karyawanid]))
        $premi[$barky->karyawanid]+=$barky->premi;
    else
        $premi[$barky->karyawanid] = $barky->premi;
}
#end premi tetap dari absensi==========================================

foreach ($premi as $idx => $row) {
    #add to ready data================================================
    if ($row > 0) {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $idx,
            'idkomponen' => 32,
            'jumlah' => $row,
            'pengali' => 1);
    }
}



foreach ($penalty as $idx => $row) {
    #add to ready data================================================
    if ($row > 0) {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $idx,
            'idkomponen' => 34,
            'jumlah' => $row,
            'pengali' => 1);
    }
}

#penalty kehadiran dari absensi
$stkh = "select a.karyawanid,sum(a.penaltykehadiran) as penaltykehadiran from " . $dbname . ".sdm_absensidt a 
                left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
                          where b.tipekaryawan in(2,3,4,6)  and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and a.kodeorg like '" . $param['kodeorg'] . "%' and sistemgaji='Harian'  
               and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' group by a.karyawanid";
$reskh = $owlPDO->query($stkh) or die(print " Gagal: " . PDOException::getMessage());
$reskh->setFetchMode(PDO::FETCH_OBJ);
while ($barky = $reskh->fetch()) {
    if ($barkh->penaltykehadiran > 0)
        $penaltykehadiran[$barkh->karyawanid] = $barkh->penaltykehadiran;
}

foreach ($penaltykehadiran as $idx => $row) {
    #add to ready data================================================
    if ($row > 0) {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $idx,
            'idkomponen' => 41,
            'jumlah' => $row,
            'pengali' => 1);
    }
}



#7. Uang Makan   : sdm_premi
$iMakan = "select a.karyawanid,a.premi from " . $dbname . ".sdm_premi a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where  b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
               and sistemgaji='Harian' and   jenis='UANGMAKAN' and periode='" . $param['periodegaji'] . "' group by a.karyawanid";
$nMakan = fetchData($iMakan);
foreach ($nMakan as $idx => $row) {
    if (isset($id[$row['karyawanid']])) {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $row['karyawanid'],
            'idkomponen' => 45,
            'jumlah' => $row['premi'],
            'pengali' => 1);
    } else {
        //abaikan jika tidak terdaftar pada karyawanid  
    }
}

### tj absensi  : sdm_premi
$iTjabsen = "select a.karyawanid,a.premi from " . $dbname . ".sdm_premi a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
                and sistemgaji='Harian' and   jenis='TJABSEN' and periode='" . $param['periodegaji'] . "' group by a.karyawanid";
$nTjabsen = fetchData($iTjabsen);
foreach ($nTjabsen as $idx => $row) {
    if (isset($id[$row['karyawanid']])) {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $row['karyawanid'],
            'idkomponen' => 56,
            'jumlah' => $row['premi'],
            'pengali' => 1);
    } else {
        //abaikan jika tidak terdaftar pada karyawanid  
    }
}



## premi tetap : sdm_premi
$iPremitetap = "select a.karyawanid,a.premi from " . $dbname . ".sdm_premi a left join 
              " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
               where b.tipekaryawan in(2,3,4,6) and b.lokasitugas='" . $param['kodeorg'] . "' 
               and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi=0
                and sistemgaji='Harian' and   jenis='PREMITETAP' and periode='" . $param['periodegaji'] . "' group by a.karyawanid";
$nPremitetap = fetchData($iPremitetap);
foreach ($nPremitetap as $idx => $row) {
    if (isset($id[$row['karyawanid']])) {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $row['karyawanid'],
            'idkomponen' => 40,
            'jumlah' => $row['premi'],
            'pengali' => 1);
    } else {
        //abaikan jika tidak terdaftar pada karyawanid  
    }
}

##pendapatan lainnya
$i = "select * from " . $dbname . ".sdm_pendapatanlaindt where kodeorg='" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' "
        . " and karyawanid in (select karyawanid from " . $dbname . ".datakaryawan where tipekaryawan in(2,3,4,6) and"
        . " (tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and alokasi=0 and sistemgaji='Harian' and"
        . " (tanggalmasuk<='" . $tanggal2 . "' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) ) ";
$n = fetchData($i);
foreach ($n as $idx => $val) {
    $readyData[] = array(
        'kodeorg' => $param['kodeorg'],
        'periodegaji' => $param['periodegaji'],
        'karyawanid' => $val['karyawanid'],
        'idkomponen' => $val['idkomponen'],
        'jumlah' => $val['jumlah'],
        'pengali' => 1);
}


###tambahan indra disini, memasukan bpjs kesehatan (jms) dan bpjs kesehatan
##algoritma : jika kolom jms dan bpjs di datakaryawan terisi maka akan memotong
##jika tidak maka di kosongkan
if ($tipeOrg == 'PABRIK') {
    $bpjsOrg = 'PABRIK';
} else {
    $bpjsOrg = 'KEBUN';
}


$iKerja = "select * from " . $dbname . ".sdm_5bpjs where lokasibpjs='" . $bpjsOrg . "' and jenisbpjs='ketanagakerjaan' ";
$nKerja = $owlPDO->query($iKerja) or die(print " Gagal: " . PDOException::getMessage());
$nKerja->setFetchMode(PDO::FETCH_ASSOC);
$dKerja = $nKerja->fetch();
$bpjsKerja = $dKerja['bebankaryawan'];

$iSehat = "select * from " . $dbname . ".sdm_5bpjs where lokasibpjs='" . $bpjsOrg . "' and jenisbpjs='kesehatan' ";
$nSehat = $owlPDO->query($iSehat) or die(print " Gagal: " . PDOException::getMessage());
$nSehat->setFetchMode(PDO::FETCH_ASSOC);
$dSehat = $nSehat->fetch();
$bpjsSehat = $dSehat['bebankaryawan'];

#masukin BPJS Tenaga Kerja ke sini
foreach ($umrbulanan as $key => $nilai) {
    if ($bpjstenaga[$key] != '') {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $key,
            'idkomponen' => 3,
            'jumlah' => ($bpjsKerja / 100 * $nilai),
            'pengali' => 1);
    }
}

#masukin BPJS kesehatan ke sini
foreach ($umrbulanan as $key => $nilai) {
    if ($bpjskes[$key] != '') {
        $readyData[] = array(
            'kodeorg' => $param['kodeorg'],
            'periodegaji' => $param['periodegaji'],
            'karyawanid' => $key,
            'idkomponen' => 44,
            'jumlah' => ($bpjsSehat / 100 * $nilai),
            'pengali' => 1);
    }
}

//calculate to component
$strx = "select id as komponen, case plus when 0 then -1 else plus end as pengali,name as nakomp 
              FROM " . $dbname . ".sdm_ho_component";
$comRes = fetchData($strx);
$comp = Array();
$nakomp = Array();
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
        $list1 .= "<td>" . $tipekaryawan[$val[0]] . "</td>";
        $list1 .= "<td>" . number_format($sisa[$val[0]], 0, ',', '.') . "</td>";
        $list1 .= "</tr>";
        $negatif = true;
    } else {
        $no+=1;
        $list2 .="<tr class=rowcontent>";
        $list2 .= "<td>" . $no . "</td>";
        $list2 .= "<td>" . $param['periodegaji'] . "</td>";
        $list2 .= "<td>" . $namakar[$val[0]] . "</td>";
        $list2 .= "<td>" . $tipekaryawan[$val[0]] . "</td>";
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
        $sdel = "delete from " . $dbname . ".sdm_gaji "
                . " where idkomponen not in ('28','46','47') "
                . " and periodegaji='" . $param['periodegaji'] . "' and kodeorg='" . $param['kodeorg'] . "' "
                . " and karyawanid in (select distinct karyawanid from " . $dbname . ".datakaryawan "
                . " where sistemgaji='Harian' and lokasitugas='" . $param['kodeorg'] . "')";
        //exit("Error:$sdel");
        try {
            $owlPDO->exec($sdel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        # Insert All ready data
        $insError = "";
        foreach ($readyData as $row) {
            if ($row['jumlah'] == 0 or $row['jumlah'] == '') {
                continue;
            } else {
                $queryIns = insertQuery($dbname, 'sdm_gaji', $row);
                try{
                	$test = $owlPDO->exec($queryIns);
                }
                catch(PDOException $e){
                    $queryUpd = updateQuery($dbname, 'sdm_gaji', $row, "kodeorg='" . $row['kodeorg'] .
                            "' and periodegaji='" . $row['periodegaji'] .
                            "' and karyawanid='" . $row['karyawanid'] .
                            "' and idkomponen=" . $row['idkomponen']);
                    try {
                        $owlPDO->exec($queryUpd);
                    } catch (PDOException $e) {
                        echo "DB Update Error : " . $e->getMessage() . "\n";
                        die();
                    }
                }
            }
        }
        break;
    default:
        break;
}
?>