<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
include_once('lib/zJournal.php');

$param = $_POST;
$tmpPeriod = explode('-', $param['periode']);
$tahunbulan = implode("", $tmpPeriod);
$tahun = $tmpPeriod[0];
$bulan = $tmpPeriod[1];
$proses = $_GET['proses'];
// echo"<pre>";
// print_r($_GET);
// echo"</pre>";
//  exit('warning');



#== cek akun bank tidak boleh ada <0 ===============================================================

$daftarbank = makeOption($dbname, 'keu_5akunbank', 'noakun,namabank');
$namabank = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');

$periodeberikut = periodeberikut($param['periode']);
$tmpPeriodberikut = explode('-', $periodeberikut);
$awalberikut = "awal" . $tmpPeriodberikut[1];
$str = "select " . $awalberikut . " as awal,norek FROM " . $dbname . ".keu_saldobank where  periode ='" . str_replace("-", "", $periodeberikut) . "' 
          and kodeorg='" . $param['kodeorg'] . "' and " . $awalberikut . "<0 ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);
$dataminus = '';
if ($numrows > 0) {
    while ($bar = $res->fetch()) {
        $dataminus .= "Bank/Norek : " . $namabank[$daftarbank[$bar->norek]] . " " . $bar->norek . "; Jumlah : " . number_format($bar->awal, 2) . "\n";
    }
}

if ($dataminus != '') {
    exit(" Warning:  Masih ada saldo rekening dibawah 0\n" . $dataminus . ""); //
}
#=======================================================================================================

#= cek apakah akun intraco interco sudah balance =======================================================

$str = "select sum(jumlah) from " . $dbname . ".keu_jurnaldt_vw where 
		noakun in (select akunpiutang from " . $dbname . ".keu_5caco) and periode='" . $param['periode'] . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$bar = $res->fetch();
// $bar->jumlah=10000.24252;
$jumlahpiutang = number_format(abs($bar->jumlah));
$jumlahpiutang = str_replace(",", "", $jumlahpiutang);

$str = "select sum(jumlah) from " . $dbname . ".keu_jurnaldt_vw where 
		noakun in (select akunhutang from " . $dbname . ".keu_5caco) and periode='" . $param['periode'] . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$bar = $res->fetch();
// $bar->jumlah=20000.5343;
$jumlahhutang = number_format(abs($bar->jumlah));
$jumlahhutang = str_replace(",", "", $jumlahhutang);

$selisihhutangpiutang = $jumlahhutang - $jumlahpiutang;
// exit("Error:".$selisihhutangpiutang._.$jumlahhutang._.$jumlahpiutang);

if ($selisihhutangpiutang > 1) {
    exit("Warning : Akun Intraco/Interco masih ada selisih, selisih : " . number_format($selisihhutangpiutang) . " ");
}
#=======================================================================================================



//ambil akun laba tahun berjalan;
$stl = "select noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='CLM'";
$akunCLM = '';
$res = $owlPDO->query($stl) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bal = $res->fetch()) {
    $akunCLM = $bal->noakundebet;
}
//ambil akun laba ditahan
$stl = "select noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='CLY'";
$akunCLY = '';
$res = $owlPDO->query($stl) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bal = $res->fetch()) {
    $akunCLY = $bal->noakundebet;
}
//ambil batas bawah akun laba/rugi
$stl = "select noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='RAT'";
$akunRAT = '';
$res = $owlPDO->query($stl) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bal = $res->fetch()) {
    $akunRAT = $bal->noakundebet;
}
if ($akunCLM == '' or $akunCLY == '' or $akunRAT == '') {
    if ($_SESSION['language'] == 'EN') {
        exit(' Error: Annual income account data, account  retained earnings and account limits profits / losses not yet listed on the parameters of the journal');
    } else {
        exit(' Error: data akun laba tahunan, akun laba ditahan dan batas akun laba/rugi belum terdaftar pada parameter jurnal');
    }
}

#periksa apakah sudah diposting semua transaksi kas dan bappp
$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi where 
      periode='" . $param['periode'] . "' and kodeorg='" . $param['kodeorg'] . "'";
//echo $str."____";
$currstart = '';
$currend = '';
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $currstart = $bar->tanggalmulai;
    $currend = $bar->tanggalsampai;
}

if ($currstart == '' or $currend == '') {
    if ($proses != 'changeperiode') {
        exit('Error: Accounting period is not normal to ' . $param['kodeorg']);
    }
} else {

    #periksa periode kas kecil sudah tutup atau belum
    $str = $owlPDO->query("select close from " . $dbname . ".keu_5kaskecil where periode='" . $param['periode'] . "' and unit='" . $param['kodeorg'] . "' and close=0 ");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $numrows = owlBaris($str);
    if ($numrows > 0) {
        echo "Kas Kecil unit " . $param['kodeorg'] . " pada periode " . $param['periode'] . " belum tutup. \n";
        exit('Warning');
    }

    #periksa kas
    /*
    $str="select notransaksi,tanggal,jumlah from ".$dbname.".keu_kasbankht where kodeorg='".$param['kodeorg']."'
          and tanggal between '".$currstart."' and '".$currend."' and posting<>1";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0)
    {
        echo " There are Cash/Bank transaction that has not been posted:\n";
        $no=0;
        while($bar=$res->fetch())
        {
           $no+=1;
            echo $no.". No ".$bar->notransaksi.":".tanggalnormal($bar->tanggal)."->Rp. ".number_format($bar->jumlah,0)."\n"; 
        }
        exit('Error');
    }
	*/

    #periksa bapp
    $str = "select notransaksi,tanggal,jumlahrealisasi from " . $dbname . ".log_baspk where kodeblok like '" . $param['kodeorg'] . "%'
          and tanggal between '" . $currstart . "' and '" . $currend . "' and statusjurnal=0";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows = owlBaris($res);
    if ($numrows > 0) {
        echo "There are Contract Realization transaction that has not been posted:\n";
        $no = 0;
        while ($bar = $res->fetch()) {
            $no += 1;
            echo $no . ". No " . $bar->notransaksi . ":" . tanggalnormal($bar->tanggal) . "->Rp. " . number_format($bar->jumlahrealisasi, 0) . "\n";
        }
        exit('Error');
    }
    #periksa jurnal tidak balance
    $str = "select nojurnal,tanggal,debet,kredit from " . $dbname . ".keu_jurnal_tidak_balance_vw where kodeorg = '" . $param['kodeorg'] . "'
          and tanggal between '" . $currstart . "' and '" . $currend . "'
          and nojurnal not like '%/CLSM/%'";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows = owlBaris($res);
    if ($numrows > 0) {
        echo "There is still yet balanced Journal:\n";
        $no = 0;
        while ($bar = $res->fetch()) {
            $no += 1;
            echo $no . ". No " . $bar->nojurnal . ":" . tanggalnormal($bar->tanggal) . "->(D)Rp. " . number_format($bar->debet, 0) . ":(K)Rp. " . number_format($bar->kredit, 0) . "\n";
        }
        exit('Error');
    }
    #periksa gudang
    $str = "select notransaksi,tanggal, kodegudang from " . $dbname . ".log_transaksiht where post=0 and hasilpersetujuan1!=2 and kodegudang like '" . $param['kodeorg'] . "%'
            and tanggal between '" . $currstart . "' and '" . $currend . "'";
    $stm = '';
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows = owlBaris($res);
    if ($numrows > 0) {
        while ($bar = $res->fetch()) {
            $stm .= "Gudang:" . $bar->kodegudang . "->No.>" . $bar->notransaksi . "->" . $bar->tanggal . "<br>";
        }
        echo "Error: Warehouse transaction that has not been posted\r<br>" . $stm;
        exit();
    }

    #periksa apakah ada gudang yg belum tutup
    $str = "select kodeorg,periode from " . $dbname . ".setup_periodeakuntansi where kodeorg like '" . $param['kodeorg'] . "%' and periode='" . $param['periode'] . "' and tutupbuku=0 and char_length(kodeorg)=6 ";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows = owlBaris($res);
    $sgudang = "";
    if ($numrows > 0) {
        while ($bar = $res->fetch()) {
            $sgudang .= "- " . $bar->kodeorg . "\n";
        }
        echo "Warning : \n\n" . $_SESSION['lang']['notiftutupgudang'] . " : \n" . $sgudang;
        exit();
    }


    #Periksa TRAKSI
    $str = "select notransaksi,tanggal from " . $dbname . ".vhc_runht where kodeorg='" . $param['kodeorg'] . "'
          and tanggal between '" . $currstart . "' and '" . $currend . "' and posting=0";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows = owlBaris($res);
    if ($numrows > 0) {
        echo " There still Vehicle Runn transaction that has not been posted:\n";
        $no = 0;
        while ($bar = $res->fetch()) {
            $no += 1;
            echo $no . ". No " . $bar->notransaksi . ":" . tanggalnormal($bar->tanggal) . "\n";
        }
        exit('Error');
    }
}

#PERIKSA akun transit yang belum nol=============================
$str = "select sum(debet)-sum(kredit) as saldo FROM " . $dbname . ".keu_jurnalsum_vw where  periode ='" . $param['periode'] . "' 
          and kodeorg='" . $param['kodeorg'] . "' AND noakun like '4%'";
$transit = 0;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);
if ($numrows > 0) {
    while ($bar = $res->fetch()) {
        $transit = $bar->saldo;
    }
}
if ($transit > 10 && $transit != '') #lebih dari  10 rupiah
{
    exit(" Error: Transit account  been allocated correctly, remains:" . $transit);
}
#---------------------------------------==================================

if (substr($param['kodeorg'], 2, 2) != 'HO' and substr($param['kodeorg'], 2, 2) != 'LO') {
    #PERIKSA apakah sudah ada gaji=============================
    $str = "select nojurnal FROM " . $dbname . ".keu_jurnalht where  tanggal like '" . $param['periode'] . "%'
              and nojurnal like '%" . $param['kodeorg'] . "/KBN%'";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows = owlBaris($res);
    if ($numrows > 0) {
    } else {
        exit(" Error: Proses Gaji has not been processed. ");
    }
    #---------------------------------------==================================
}


/**************************************************************
 * [START] Cek Pengakuan Penjualan ****************************
 **************************************************************/
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    // $listTiket="";
    // $qJual = "SELECT notransaksi,a.nokontrak
    //         FROM ".$dbname.".pabrik_timbangan a
    //         INNER JOIN ".$dbname.".pmn_kontrakjual d on a.nokontrak = d.nokontrak 
    //         WHERE date(a.tanggal) between '".$currstart."' and '".$currend.
    //             "'and a.nokontrak in (select nokontrak from ".$dbname.".pmn_kontrakjual where tanggalkontrak like '".substr($currstart,0,4)."%') 
    //               and a.millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='PABRIK')";
    //     $resJual = fetchData($qJual);
    //     if(!empty($resJual)) {
    //         $listTiket = '';
    //         foreach($resJual as $row) {
    //             $scek2="select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual 
    //                     where notransaksi='".$row['notransaksi']."'";
    //                     //exit("error:".$scek2);
    //             $res=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
    //             $res->setFetchMode(PDO::FETCH_OBJ);
    //             $numrows=owlBaris($res);
    //             $rcek2=$numrows;
    //             if($rcek2==0){
    //                 $listTiket .= "- ".$row['notransaksi']."\n";    
    //             }
    //         }
    //         if($listTiket!=''){
    //             exit("Warning: Ada Timbangan Pabrik ke Eksternal yang belum diakui\n".$listTiket);    
    //         }

    //     }


    /**************************************************************
     * [END] Cek Pengakuan Penjualan ******************************
     **************************************************************/
}
/**************************************************************
 * [START] Cek transaksi tagihan jenisnya jurnal **************
 **************************************************************/
$sCek = '';
$rCek = '';
$sCek = "select * from " . $dbname . ".keu_tagihanht where unit='" . $param['kodeorg'] . "' 
       and tanggal between '" . $currstart . "' and '" . $currend . "' and posting=0 
       and tipeinvoice in (select kode from " . $dbname . ".keu_5jenistagihan where jurnal=1)";
$rCek = fetchdata($sCek);
if (count($rCek) != 0) {
    $no = 0;
    foreach ($rCek as $row => $lstData) {
        $no += 1;
        echo "No " . $no . ". " . $lstData['noinvoice'] . "\n";
    }
    exit('warning: Data Tagihan Belum Terposting');
}

/**************************************************************
 * [END] Cek transaksi tagihan jenisnya jurnal ****************
 **************************************************************/



switch ($proses) {
    case 'tutupBuku':
        #==================== Prep Periode ====================================
        # Prep Tahun Bulan untuk periode selanjutnya
        if ($tmpPeriod[1] == 12) {
            $bulanLanjut = 1;
            $tahunLanjut = $tmpPeriod[0] + 1;
        } else {
            $bulanLanjut = $tmpPeriod[1] + 1;
            $tahunLanjut = $tmpPeriod[0];
        }

        # Prep Hari untuk periode selanjutnya
        $jmlHari = cal_days_in_month(CAL_GREGORIAN, $bulanLanjut, $tahunLanjut);
        $tglAwal = $tahunLanjut . '-' . addZero($bulanLanjut, 2) . '-01';
        $tglAkhir = $tahunLanjut . '-' . addZero($bulanLanjut, 2) . '-' . addZero($jmlHari, 2);
        #==================== /Prep Periode ===================================

        #==================== Prep Jurnal =====================================
        #=== Extract Data ====
        # Get PT
        $pt = getPT($dbname, $param['kodeorg']);
        if ($pt == false) {
            $pt = getHolding($dbname, $param['kodeorg']);
        }

        # Tanggal dan Kode Jurnal
        $tgl = $tmpPeriod[0] . $tmpPeriod[1] .
            cal_days_in_month(CAL_GREGORIAN, $tmpPeriod[1], $tmpPeriod[0]);
        $kodejurnal = 'CLSM';


        #==================== Journal Counter ==================
        $nojurnal = $tgl . "/" . $param['kodeorg'] .
            "/" . $kodejurnal . "/999";
        #==================== Journal Counter ==================

        # Cek apakah tahun sudah ditutup
        $qCek = selectQuery(
            $dbname,
            'keu_jurnalht',
            '*',
            "nojurnal='" . $nojurnal . "'"
        );
        //        echo "error:".$qCek;
        //        exit;
        $resCek = fetchData($qCek);
        if (!empty($resCek)) {
            $sPeriode = "select periode from " . $dbname . ".setup_periodeakuntansi 
                       where kodeorg='" . $param['kodeorg'] . "' order by periode desc limit 1";
            $qPeriode = $owlPDO->query($sPeriode) or die(print " Gagal: " . PDOException::getMessage());
            $qPeriode->setFetchMode(PDO::FETCH_ASSOC);
            $rPeriode = $qPeriode->fetch();
            if ($rPeriode['periode'] == $param['periode']) {
                $sDel = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $nojurnal . "'";
                try {
                    $owlPDO->exec($sDel);
                } catch (PDOException $e) {
                    print " Delete HPP Error !: " . $e->getMessage() . "\n";
                    die();
                }
            } else {
                echo ' Error : This period has been closed(Before).';
                exit;
            }
        }

        $query = "select count(*) as x from " . $dbname . ".keu_jurnaldt_vw where 
                   tanggal between '" . $currstart . "' and '" . $currend . "' and kodeorg='" . $param['kodeorg'] . "'";
        //         exit("error: ".$query);
        $res = $owlPDO->query($query) or die(print " Gagal: " . PDOException::getMessage());
        $excludeHO = ['CAHO', 'LAHO', 'MHHO'];
        if (owlbaris($res) == 0) {
            // 20211008 monica: Ini mau proses tutup buku SDPHO tp tdk bisa Krn memang tdk ad transaksi
            // 20211124 monica: tidak hanya 2021-03, tapi kemungkinan bulan2 setelahnya juga kosong
            // if(($param['kodeorg']=='SPHO')and($param['periode']=='2021-03')){
            if (!in_array($param['kodeorg'], $excludeHO)) {
                echo 'Warning : No data found for this unit';
                exit;
            }
        }

        # Get Sum dari Jurnal
        $query = selectQuery(
            $dbname,
            'keu_jurnaldt_vw',
            'kodeorg as kodeorg,sum(jumlah) as jumlah',
            "kodeorg='" . $param['kodeorg'] . "' and tanggal between '" . $currstart . "' and '" . $currend . "'
             and noakun>='" . $akunRAT . "'"
        ) .
            "group by kodeorg";
        $data = fetchData($query);


        # Get Akun
        #+++++++++++++++++++++++++
        //tambahan ginting
        $noakun = $akunCLM; //akun laba tahun berjalan
        #++++++++++++++++++++++++++
        if ($data[0]['jumlah'] > 0) {
            # Rugi
            $debetH = $data[0]['jumlah'];
            $kreditH = 0;
        } else {
            # Laba
            $debetH = 0;
            $kreditH = $data[0]['jumlah'];
        }

        # Prep Header
        $dataRes['header'] = array(
            'nojurnal' => $nojurnal,
            'kodejurnal' => $kodejurnal,
            'tanggal' => $tgl,
            'tanggalentry' => date('Ymd'),
            'posting' => '0',
            'totaldebet' => floatval($debetH),
            'totalkredit' => floatval($kreditH),
            'amountkoreksi' => '0',
            'noreferensi' => 'TUTUP/' . $param['kodeorg'] . '/' . $tahunbulan,
            'autojurnal' => '1',
            'matauang' => 'IDR',
            'kurs' => '1',
            'revisi' => '0'
        );

        # Data Detail
        $noUrut = 1;

        # Debet
        $dataRes['detail'][] = array(
            'nojurnal' => $nojurnal,
            'tanggal' => $tgl,
            'nourut' => $noUrut,
            'noakun' => $noakun,
            'keterangan' => 'Tutup Bulan ' . $tahunbulan . ' Unit ' . $param['kodeorg'],
            'jumlah' => floatval($data[0]['jumlah']),
            'matauang' => 'IDR',
            'kurs' => '1',
            'kodeorg' => $param['kodeorg'],
            'kodekegiatan' => '',
            'kodeasset' => '',
            'kodebarang' => '',
            'nik' => '',
            'kodecustomer' => '',
            'kodesupplier' => '',
            'noreferensi' => '',
            'noaruskas' => '',
            'kodevhc' => '',
            'nodok' => '',
            'kodeblok' => '',
            'revisi' => '0',
            'kodesegment' => $defSegment
        );
        $noUrut++;
        /*    kredit tidak perlu untuk laba rugi tahun berjalan   
        # Kredit
        $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$tgl,
            'nourut'=>$noUrut,
            'noakun'=>$akunKredit,
            'keterangan'=>'Tutup Bulan '.$tahunbulan.' Unit '.$param['kodeorg'],
            'jumlah'=>-1*$data[0]['jumlah'],
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$pt['kode'],
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>'',
            'nik'=>'',
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>'',
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>''
            
        );
  *        $noUrut++; 
  * 
  */

        #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
        $headErr = '';
        $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
        try {
            $owlPDO->exec($insHead);
        } catch (PDOException $e) {
            $headErr .= "Insert Header Error :  " . $e->getMessage() . "\n" . $insHead;
        }

        if ($headErr == '') {
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
            $detailErr = '';
            foreach ($dataRes['detail'] as $row) {
                $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
                try {
                    $owlPDO->exec($insDet);
                } catch (PDOException $e) {
                    $detailErr .= "Insert Detail Error :  " . $e->getMessage() . "\n" . $insDet;
                    break;
                }
            }

            if ($detailErr == '') {
                #==================== /Prep Jurnal ====================================
                createSaldoAwal($param['periode'], $tahunLanjut . '-' . addZero($bulanLanjut, 2), $param['kodeorg']);
                #========================== Proses Insert dan Update ==========================

                # Header and Detail inserted
                # Update Status Tutup Buku
                $queryUpd = updateQuery(
                    $dbname,
                    'setup_periodeakuntansi',
                    array('tutupbuku' => 1),
                    "kodeorg='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "'"
                );
                try {
                    $owlPDO->exec($queryUpd);
                } catch (PDOException $e) {
                    echo "Error Update :  " . $e->getMessage() . "\n" . $queryUpd;
                    exit;
                }

                # Insert periode baru
                $dataIns = array(
                    'kodeorg' => $param['kodeorg'],
                    'periode' => $tahunLanjut . '-' . addZero($bulanLanjut, 2),
                    'tanggalmulai' => $tglAwal,
                    'tanggalsampai' => $tglAkhir,
                    'tutupbuku' => 0
                );
                $queryIns = insertQuery($dbname, 'setup_periodeakuntansi', $dataIns);
                echo '1';
                try {
                    $owlPDO->exec($queryIns);
                } catch (PDOException $e) {
                    echo "Error Insert :" . $e->getMessage() . "\n" . $queryUpd;
                    $queryRB = updateQuery(
                        $dbname,
                        'setup_periodeakuntansi',
                        array('tutupbuku' => 0),
                        "kodeorg='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "'"
                    );
                    try {
                        $owlPDO->exec($queryRB);
                    } catch (PDOException $e) {
                        echo "Error Rollback Update :  " . $e->getMessage() . "\n" . $queryRB;
                        exit;
                    }
                    exit;
                }
                //update history tutup buku
                $str = "delete from " . $dbname . ".keu_setup_watu_tutup where periode='" . $param['periode'] . "'  and kodeorg='" . $param['kodeorg'] . "'";
                $owlPDO->exec($str);
                $str = "insert into " . $dbname . ".keu_setup_watu_tutup(kodeorg,periode,username) values(
                                  '" . $param['kodeorg'] . "','" . $param['periode'] . "','" . $_SESSION['standard']['username'] . "')";
                $owlPDO->exec($str);
            } else {
                echo $detailErr;
                # Rollback, Delete Header
                $RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal . "'");
                try {
                    $owlPDO->exec($RBDet);
                } catch (PDOException $e) {
                    echo "Error Rollback Update :  " . $e->getMessage() . "\n" . $RBDet;
                    exit;
                }
            }
        } else {
            echo $headErr;
            exit;
        }


        #email notifikasi bjr       
        break;

    default:
}

function createSaldoAwal($dariperiode, $keperiode, $kodeorg)
{
    global $conn;
    global $dbname;
    global $akunRAT;
    global $akunCLM;
    global $akunCLY;
    global $owlPDO;
    $sawal = array();
    $mtdebet = array();
    $mtkredit = array();
    $salak = array();
    #ambil saldoawal bulan berjalan
    $str = "select awal" . substr($dariperiode, 5, 2) . ",noakun from " . $dbname . ".keu_saldobulanan
          where periode='" . str_replace("-", "", $dariperiode) . "' and kodeorg='" . $kodeorg . "'";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_NUM);
    while ($bar = $res->fetch()) {
        $sawal[$bar[1]] = $bar[0];
        $mtdebet[$bar[1]] = 0;
        $mtkredit[$bar[1]] = 0;
        $salak[$bar[1]] = $bar[0];
    }
    #ambil transaksi transaksi bln berjalan
    $str = "select debet,kredit,noakun from " . $dbname . ".keu_jurnalsum_vw 
          where periode='" . $dariperiode . "' and kodeorg='" . $kodeorg . "'";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        $mtdebet[$bar->noakun] = $bar->debet;
        $mtkredit[$bar->noakun] = $bar->kredit;
        $salak[$bar->noakun] = $mtdebet[$bar->noakun] + $sawal[$bar->noakun] - $mtkredit[$bar->noakun];
    }

    #ambil semu nomor akun
    $str = "select noakun from " . $dbname . ".keu_5akun where length(noakun)=7";
    $temp = '';
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        #create string update current
        if ($sawal[$bar->noakun] != '') {
            #jika sudah ada di database maka update
            if ($mtdebet[$bar->noakun] == '')
                $mtdebet[$bar->noakun] = 0;
            if ($mtkredit[$bar->noakun] == '')
                $mtkredit[$bar->noakun] = 0;

            $temp = "update " . $dbname . ".keu_saldobulanan 
                set debet" . substr($dariperiode, 5, 2) . "=" . $mtdebet[$bar->noakun] . ",
                kredit" . substr($dariperiode, 5, 2) . "=" . $mtkredit[$bar->noakun] . "
                where periode='" . str_replace("-", "", $dariperiode) . "'
                and kodeorg='" . $kodeorg . "' and noakun='" . $bar->noakun . "';";
            try {
                $owlPDO->exec($temp);
            } catch (PDOException $e) {
                print " Error update mutasi bulanan  !: " . $e->getMessage() . "\n";
                die();
            }
        } else {
            #jika belum ada maka insert
            if ($sawal[$bar->noakun] != '' or $mtdebet[$bar->noakun] != '' or  $mtkredit[$bar->noakun] != '') {
                if ($mtdebet[$bar->noakun] == '')
                    $mtdebet[$bar->noakun] = 0;
                if ($mtkredit[$bar->noakun] == '')
                    $mtkredit[$bar->noakun] = 0;
                $temp = "insert into  " . $dbname . ".keu_saldobulanan (kodeorg,periode,noakun
,                  awal" . substr($dariperiode, 5, 2) . ",debet" . substr($dariperiode, 5, 2) . ",
                  kredit" . substr($dariperiode, 5, 2) . ")values('" .
                    $kodeorg . "','" . str_replace("-", "", $dariperiode) . "','" . $bar->noakun . "',0," .
                    $mtdebet[$bar->noakun] . "," . $mtkredit[$bar->noakun] . ");";
                try {
                    $owlPDO->exec($temp);
                } catch (PDOException $e) {
                    print " Error insert mutasi bulanan !: " . $e->getMessage() . "\n";
                    die();
                }
            }
        }
    }
    #list akun
    $sAkn = "select noakun from " . $dbname . ".keu_5akun where char_length(noakun)=7 order by noakun asc";
    $rAkn = fetchdata($sAkn);
    foreach ($rAkn as $row => $lstAkn) {
        $dafAkun[$lstAkn['noakun']] = $lstAkn['noakun'];
    }
    $itung = 0;
    $tmpAknTrp = array();
    #cek ada akun enggak dikenali disystem
    foreach ($salak as $key => $val) {
        if ($salak[$key] != '') {
            if (count($dafAkun[$key]) == 0) {
                $itung += 1;
                $tmpAknTrp[$key] = $key;
            }
        }
    }
    if ($itung != 0) {
        echo "<pre>";
        print_r($tmpAknTrp);
        echo "</pre>";
        exit('warning: Akun di atas tidak terdaftar pada keuangan>setup>daftar perkiraan');
    }
    #delete saldo awal bulan selanjutnya;
    $str = "delete from " . $dbname . ".keu_saldobulanan where periode='" . str_replace("-", "", $keperiode) . "'
          and kodeorg='" . $kodeorg . "';";
    //$test=false;
    try {
        $owlPDO->exec($str);
    } catch (PDOException $e) {
        print " Error insert mutasi bulanan !: " . $e->getMessage() . "\n";
        die();
    }
    $saldoditahan = 0;
    foreach ($salak as $key => $val) {
        if ($salak[$key] != '') {
            if (count($dafAkun[$key]) == 0) {
                $itung += 1;
                $tmpAknTrp[$key] = $key;
            }
            $temp = "insert into  " . $dbname . ".keu_saldobulanan (kodeorg,periode,noakun,
                      awal" . substr($keperiode, 5, 2) . ")values('" .
                $kodeorg . "','" . str_replace("-", "", $keperiode) . "','" . $key . "'," . $salak[$key] . ")";
            if (substr($keperiode, 5, 2) != '01') #jika bukan awal tahun
            {
                try {
                    $owlPDO->exec($temp);
                } catch (PDOException $e) {
                    print "Error insert saldo awal !: " . $e->getMessage() . "\n";
                    die();
                }
            } else #jika bulan 12
            {
                if ($key < $akunRAT) { #jika awal tahun maka hanya akan membawa aktiva saja ke bulan selanjutnya
                    #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++                       
                    #deteksi jika saldo ditahan
                    #sudah mengakomodasi tutup akhir tahun    
                    if ($key == $akunCLY)
                        $saldoditahan += $salak[$key];
                    else {
                        if ($key == $akunCLM) {
                            $saldoditahan += $salak[$key]; #tampung laba tahun berjalan ke laba ditahan
                            $salak[$key] = 0;
                        }
                        $temp1 = "insert into  " . $dbname . ".keu_saldobulanan (kodeorg,periode,noakun,
                                  awal" . substr($keperiode, 5, 2) . ")values('" .
                            $kodeorg . "','" . str_replace("-", "", $keperiode) . "','" . $key . "'," . $salak[$key] . ")";

                        #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++                       
                        try {
                            $owlPDO->exec($temp1);
                        } catch (PDOException $e) {
                            print "Error insert saldo awal !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                }
            }
        }
    }
    //masukkan saldo laba ditahan
    if (substr($keperiode, 5, 2) == '01') { //hanya pada bulan 12                           
        $temp2 = "insert into  " . $dbname . ".keu_saldobulanan (kodeorg,periode,noakun,
          awal" . substr($keperiode, 5, 2) . ")values
           ('" . $kodeorg . "','" . str_replace("-", "", $keperiode) . "','" . $akunCLY . "'," . $saldoditahan . ")";
        try {
            $owlPDO->exec($temp2);
        } catch (PDOException $e) {
            print "Error insert laba ditahan pada saldo awal  !: " . $e->getMessage() . "\n";
            die();
        }
    }
}
