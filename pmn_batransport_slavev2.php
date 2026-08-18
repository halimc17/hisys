<?php
require_once 'master_validation.php';
require_once 'config/connection.php';
require_once 'lib/nangkoelib.php';
require_once 'lib/zLib.php';
require_once 'lib/utilities.php';
include_once 'lib/terbilang.php';
require_once 'dompdf/autoload.inc.php';
include_once('lib/HtmlExcel.php');

use Dompdf\Dompdf;


$method = checkPostGet('method', '');
$table = 'pmn_batransport';
$param = $_POST;
if (count($param) == 0) {
    $param = $_GET;
}
$urlefil = checkPostGet('urlefil', '0');
$nmkomoditi = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kelompokbarang='400'");
$arrinisial = makeOption($dbname, 'log_5masterbarang', 'kodebarang,inisial', "kelompokbarang='400'");
$nmpt = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "tipe='PT'");
// $nmsupir = makeOption($dbname, 'pabrik_timbangan', 'notransaksi,supir');

$str = "select * from " . $dbname . ".organisasi  where length(kodeorganisasi)='4'";
$res = fetchdata($str);
foreach ($res as $bar) {
    $kodept[$bar['kodeorganisasi']] = $bar['induk'];
    if ($bar['tipe'] == 'KANWIL') {
        $kodero[$bar['induk']] = $bar['kodeorganisasi'];
    }
}

$str = "select * from " . $dbname . ".pmn_5kapalponton";
$res = fetchdata($str);
foreach ($res as $bar) {
    $namakapalponton[$bar['kode']] = $bar['nama'];
}
$arruskas = makeOption($dbname, 'keu_5aruskas_detail', 'noakun,noaruskas');

//Umar
$tab = '';
$namatransportir = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$namaorganisasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
// End Umar

#- Get PPH
$sql = selectQuery($dbname, "log_5supnpwp", "*", "supplierid='{$param['tipe']}'");
$res = fetchData($sql, "OBJECT");

// if (count($res) > 0) {
//     #= nilai default jika ada NPWP
//     $pph = 2;
// } else {
//     #= nilai default jika tidak ada NPWP
//     $pph = 4;
// }

switch ($method) {

    case 'posting':

        try {
            $owlPDO->beginTransaction();

            cekperiodeakuntansi($param['unit'], $param['tanggalpost']);

            ## GET HS
            $str = "select sum(luasareaproduktif) as luasareaproduktif, left(kodeorg,6) as kodeorg, statusblok from " . $dbname . ".setup_blok where status='A' group by statusblok, left(kodeorg,6)";
            $res = fetchdata($str);
            foreach ($res as $val) {
                $arrhs[$val['kodeorg']][$val['statusblok']] = round($val['luasareaproduktif'], 2);
            }

            #=
            $kgkirim = $rpjumlah = $rpclaim = 0;
            $str = "select nettoreal, kgkirim, rpjumlah, rpclaim, notransaksi, transportir, tanggal, unit, rounit,noakundebet, nospk, kodebarang, tipe, transportir, nilaippn,nilaipph, persenppn, kodecustomer, kodesupplier,noinvoice, jenis from " . $dbname . ".pmn_batransport where  notransaksi='" . $param['notransaksi'] . "'";
            $res = fetchdata($str);
            foreach ($res as $bar) {
                $kgkirim += $bar['kgkirim'];
                // $rpjumlah += $bar['rpjumlah'];
                $rpclaim += $bar['rpclaim'];
                $transportir = $bar['transportir'];
                $tanggal = $bar['tanggal'];
                $periode = substr($bar['tanggal'], 0, 7);
                $unit = $bar['unit'];
                $nettoreal = $bar['nettoreal'];
                $rounit = $bar['rounit'];
                $nospk = $bar['nospk'];
                $jenisba = $bar['jenis'];
                $kodebarang = $bar['kodebarang'];
                $noinvoiceba = $bar['noinvoice'];
                $tipe = $bar['tipe'];
                $transportir = $bar['transportir'];
                $rppph = ($bar['nilaipph'] == 0 ? 0 : $bar['nilaipph']);
                $rppn = ($bar['nilaippn'] == 0 ? 0 : $bar['nilaippn']);
                $ppnPersen = ($bar['persenppn'] == 0 ? 0 : $bar['persenppn']);

            }   
            $rpjumlah=$nettoreal;


            # Make Option
            $tipelok = makeOption($dbname, "organisasi", "kodeorganisasi,tipe");

        
            if ($kodebarang == '40000001') {
                $str = "select * from " . $dbname . ".keu_5parameterjurnal where  kodeaplikasi='SLE' and jurnalid='STCPO' ";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $noakundebet=$bar['noakundebet'];
                    $noakunkredit = $bar['noakunkredit'];
                }
                $noakundebet = $noakundebet;
                $noakunkredit = $noakunkredit;
            }

            if ($kodebarang == '40000002') {
                $str = "select * from " . $dbname . ".keu_5parameterjurnal where  kodeaplikasi='SLE' and jurnalid='STKER'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $noakundebet=$bar['noakundebet'];
                    $noakunkredit = $bar['noakunkredit'];
                }
                $noakundebet = $noakundebet;
                $noakunkredit = $noakunkredit;
            }

            if ($kodebarang == '40000003') {

                $str = "select * from " . $dbname . ".keu_5parameterjurnal where  kodeaplikasi='SLE' and jurnalid='STTBS'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $noakundebet=$bar['noakundebet'];
                    $noakunkredit = $bar['noakunkredit'];
                }
                $noakundebet = $noakundebet;
                $noakunkredit = $noakunkredit;
            } 

            if ($kodebarang == '40000005') {

                $str = "select * from " . $dbname . ".keu_5parameterjurnal where  kodeaplikasi='SLE' and jurnalid='STCKG'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $noakundebet=$bar['noakundebet'];
                    $noakunkredit = $bar['noakunkredit'];
                }
                $noakundebet = $noakundebet;
                $noakunkredit = $noakunkredit;
            } 

            if ($noakundebet == '') {
                throw new PDOException("Warning:Noakun debet masih kosong");
            } 

            $noaruskas = $arruskas[$noakundebet];
            if ($noaruskas == '') {
                throw new PDOException("Warning:No Arus Kas untuk Noakun " . $noakundebet . " tidak ada");
            }
            $noakunppn= '1160101';
            $noakunpph= '2120201';

            $kodejurnal = 'BATR';
            $query = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodekelompok='" . $kodejurnal . "' and kodeunit='" . $rounit . "' and periode='" . $periode . "'");
            $tmpKonter = fetchData($query);
            $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
            # Prep No Jurnal
            $nojurnal = str_replace('-', '', $tanggal) . "/" . $rounit . "/" . $kodejurnal . "/" . $konter;
            $nilaippn= $rppn; 
            $nilaipph= $rppph; 
            $totalinvoice = $rpjumlah + $nilaippn-$nilaipph;
            // exit('error '.$nilaipph);
            // JurnalHT
            $dataRes['header'][] = array(
                'nojurnal' => $nojurnal,
                'kodejurnal' => $kodejurnal,
                'tanggal' => $tanggal,
                'tanggalentry' => date('Ymd'),
                'posting' => '0',
                'totaldebet' => $rpjumlah,
                'totalkredit' => $rpjumlah,
                'amountkoreksi' => '0',
                'noreferensi' => $param['notransaksi'],
                'autojurnal' => '1',
                'matauang' => 'IDR',
                'kurs' => '1',
                'revisi' => '0', 
            );

            ## BACA JENIS BA 
            ## 0 = BA NORMAL
            ## 1 = BA RETUR
            if ($jenisba == 0 || $jenisba == '0') {

                $noUrut = 1; 
                // JurnalDt
                #= debet
           
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tanggal,
                    'nourut' => $noUrut,
                    'noakun' => $noakundebet,
                    'keterangan' => 'Jurnal BA Transport : ' . $param['notransaksi'] ,
                    'jumlah' => $rpjumlah,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $rounit,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => $kodebarang,
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => $transportir,
                    'noreferensi' => $param['notransaksi'],
                    'noaruskas' => $noaruskas,
                    'kodevhc' => '',
                    'nodok' => $noinvoiceba,
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => '0000000001',
                );


                $noUrut++;
                
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tanggal,
                    'nourut' => $noUrut,
                    'noakun' => $noakunppn,
                    'keterangan' => 'Jurnal PPN : ' . $param['notransaksi'] ,
                    'jumlah' => $nilaippn,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $rounit,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => $kodebarang,
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => $transportir,
                    'noreferensi' => $param['notransaksi'],
                    'noaruskas' => $noaruskas,
                    'kodevhc' => '',
                    'nodok' => $noinvoiceba,
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => '0000000001',
                    
                );

                $noUrut++;
                
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tanggal,
                    'nourut' => $noUrut,
                    'noakun' => $noakunpph,
                    'keterangan' => 'Jurnal PPh : ' . $param['notransaksi'] ,
                    'jumlah' => $nilaipph*-1,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $rounit,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => $kodebarang,
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => $transportir,
                    'noreferensi' => $param['notransaksi'],
                    'noaruskas' => $noaruskas,
                    'kodevhc' => '',
                    'nodok' => $noinvoiceba,
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => '0000000001',
                    
                );
                $noUrut++;
                #= kredit

                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tanggal,
                    'nourut' => $noUrut,
                    'noakun' => $noakunkredit,
                    'keterangan' => 'Jurnal BA Transport : ' . $param['notransaksi'] ,
                    'jumlah' => $totalinvoice * -1,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $rounit,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => $kodebarang,
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => $transportir,
                    'noreferensi' => $param['notransaksi'],
                    'noaruskas' => $noaruskas,
                    'kodevhc' => '',
                    'nodok' => $noinvoiceba,
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => '0000000001',
                );
            }

            #= UNTUK JURNAL RETUR TRANSPORT
            if ($jenisba == 1 || $jenisba == '1') {

                $noUrut = 1;  
                #= debet
           
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tanggal,
                    'nourut' => $noUrut,
                    'noakun' => $noakundebet,
                    'keterangan' => 'Jurnal Retur BA Transport : ' . $param['notransaksi'] ,
                    'jumlah' => $rpjumlah,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $rounit,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => $kodebarang,
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => $transportir,
                    'noreferensi' => $param['notransaksi'],
                    'noaruskas' => $noaruskas,
                    'kodevhc' => '',
                    'nodok' => $noinvoiceba,
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => '0000000001',
                );


                $noUrut++;
                
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tanggal,
                    'nourut' => $noUrut,
                    'noakun' => $noakunppn,
                    'keterangan' => 'Jurnal Retur PPN : ' . $param['notransaksi'] ,
                    'jumlah' => $nilaippn,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $rounit,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => $kodebarang,
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => $transportir,
                    'noreferensi' => $param['notransaksi'],
                    'noaruskas' => $noaruskas,
                    'kodevhc' => '',
                    'nodok' => $noinvoiceba,
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => '0000000001',
                    
                );


                $noUrut++;
                
                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tanggal,
                    'nourut' => $noUrut,
                    'noakun' => $noakunpph,
                    'keterangan' => 'Jurnal Retur PPh : ' . $param['notransaksi'] ,
                    'jumlah' => $nilaipph,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $rounit,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => $kodebarang,
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => $transportir,
                    'noreferensi' => $param['notransaksi'],
                    'noaruskas' => $noaruskas,
                    'kodevhc' => '',
                    'nodok' => $noinvoiceba,
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => '0000000001',
                    
                );
                $noUrut++;
                #= kredit

                $dataRes['detail'][] = array(
                    'nojurnal' => $nojurnal,
                    'tanggal' => $tanggal,
                    'nourut' => $noUrut,
                    'noakun' => $noakunkredit,
                    'keterangan' => 'Jurnal Retur BA Transport : ' . $param['notransaksi'] ,
                    'jumlah' => $totalinvoice * -1,
                    'matauang' => 'IDR',
                    'kurs' => '1',
                    'kodeorg' => $rounit,
                    'kodekegiatan' => '',
                    'kodeasset' => '',
                    'kodebarang' => $kodebarang,
                    'nik' => '',
                    'kodecustomer' => '',
                    'kodesupplier' => $transportir,
                    'noreferensi' => $param['notransaksi'],
                    'noaruskas' => $noaruskas,
                    'kodevhc' => '',
                    'nodok' => $noinvoiceba,
                    'kodeblok' => '',
                    'revisi' => '0',
                    'kodesegment' => '0000000001',

                );
            }

            // echo "<pre>";
            // print_r($dataRes['detail']);
            // exit('warning');
            #= kredit

            #= update counter jurnal
            $str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where kodeunit='" . $rounit . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $periode . "' ";
            $owlPDO->exec($str);
            
            $str = "update " . $dbname . ".pmn_batransport set posting='1',postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi='" . $param['notransaksi'] . "'";
            $owlPDO->exec($str);

            #= jurnalht
            // if($dataRes['header']!=''){
            if (count($dataRes['header']) > 0) {
                foreach ($dataRes['header'] as $key => $dataHead) {
                    $queryH = insertQuery($dbname, 'keu_jurnalht', $dataHead, array_keys($dataHead));
                    $owlPDO->exec($queryH);
                }
            }

            #= jurnaldt
            // if($dataRes['detail']!=''){
            if (count($dataRes['detail']) > 0) {
                foreach ($dataRes['detail'] as $key => $dataDet) {
                    $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet, array_keys($dataDet));
                    $owlPDO->exec($queryD);
                }
            }
            // $owlPDO->commit();

            $tipeinv = 'batr';
            
            $arrinduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $rounit . "'");
            $akunpph = makeOption($dbname, 'log_5pphsup', 'supplierid,noakun', "supplierid='" . $transportir . "'");

            $arrnpwp = makeOption($dbname, 'log_5supnpwp', 'supplierid,npwp', "supplierid='" . $transportir . "'");
            $arrbank = makeOption($dbname, 'log_5rekbank', 'supplierid,rekening', "supplierid='" . $transportir . "'");
            $arrkelompok = makeOption($dbname, 'log_5supkelompok', 'supplierid,noakun', "supplierid='" . $transportir . "'");

            $query2 = selectQuery($dbname, 'keu_5parameterjurnal', 'noakundebet,noakunkredit', "jurnalid='" . $kodejurnal . "' and aktif=1");
            $dtnoakun = fetchData($query2);
 
            $query4 = selectQuery($dbname, 'setup_org_npwp', 'npwp,no_pkp', "kodeorg='" . $kodept2 . "' ");
            $datapt = fetchData($query4);
            
            $kelompokbarang = '400';
            #=== Transform Data ===
            $dataRes['header'] = array();
            $dataRes['detail'] = array();

            # Prep Header

            ##= JURNAL INVOICE

            $kodejurnalinvoice = 'TGH01';
            
            $query = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodekelompok='" . $kodejurnalinvoice . "' and kodeunit='" . $rounit . "' and periode='" . $periode . "' and kodeorg='".$arrinduk[$rounit]."'");
            $tmpKonter = fetchData($query);
            $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
            # Prep No Jurnal
            $nojurnal = str_replace('-', '', $tanggal) . "/" . $rounit . "/" . $kodejurnalinvoice . "/" . $konter;
        
            $tgl = $tanggal;
            $bln = substr($tgl, 5, 2);

            // $noinvoice = $arrinduk[$rounit] . "/INV/" . $bln . "/" . substr($tgl, 0, 4) . "/" . $konter;

            // exit('error '.$noinvoice);
            // $noinvoice= date('YmdHis');
            // JurnalHT
            // $dataRes['header2'][] = array(
            //     'nojurnal' => $nojurnal,
            //     'kodejurnal' => $kodejurnalinvoice,
            //     'tanggal' => $tanggal,
            //     'tanggalentry' => date('Ymd'),
            //     'posting' => '0',
            //     'totaldebet' => '0',
            //     'totalkredit' => '0',
            //     'amountkoreksi' => '0',
            //     'noreferensi' => $noinvoice,
            //     'autojurnal' => '1',
            //     'matauang' => 'IDR',
            //     'kurs' => '1',
            //     'revisi' => '0',
            //     // 'bagian' => '',
            //     // 'others' => '',
            //     // 'kuantitas' => $kgkirim,
            //     // 'createby' => $_SESSION['standard']['userid'],
            //     // 'createtime' => date('Y-m-d H:i:s'),
            //     // 'updateby' => $_SESSION['standard']['userid'],
            //     // 'updatetime' => date('Y-m-d H:i:s'),
            // );

            // $noUrut = 1;
            // // JurnalDT
            // #= debet
            // $dataRes['detail2'][] = array(
            //     'nojurnal' => $nojurnal,
            //     'tanggal' => $tanggal,
            //     'nourut' => $noUrut,
            //     'noakun' => $noakuninvoicedebet,
            //     'keterangan' => 'Jurnal BA Transport : ' . $param['notransaksi'] ,
            //     'jumlah' => $rpjumlah,
            //     'matauang' => 'IDR',
            //     'kurs' => '1',
            //     'kodeorg' => $rounit,
            //     'kodekegiatan' => '',
            //     'kodeasset' => '',
            //     'kodebarang' => $kodebarang,
            //     'nik' => '',
            //     'kodecustomer' => '',
            //     'kodesupplier' => $transportir,
            //     'noreferensi' => $noinvoice,
            //     'noaruskas' => $noaruskas,
            //     'kodevhc' => '',
            //     'nodok' => $nospk,
            //     'kodeblok' => '',
            //     'revisi' => '0',
            //     'kodesegment' => '0000000001',
            //     // 'bagian' => '',
            //     // 'others' => '',
            //     // 'kuantitas' => $kgkirim,
            //     // 'createby' => $_SESSION['standard']['userid'],
            //     // 'createtime' => date('Y-m-d H:i:s'),
            //     // 'updateby' => $_SESSION['standard']['userid'],
            //     // 'updatetime' => date('Y-m-d H:i:s'),
            // );
            // $noUrut++;

            // #= kredit
            // // JurnalDT
            // $dataRes['detail2'][] = array(
            //     'nojurnal' => $nojurnal,
            //     'tanggal' => $tanggal,
            //     'nourut' => $noUrut,
            //     'noakun' => $noakuninvoicekredit,
            //     'keterangan' => 'Jurnal BA Transport : ' . $param['notransaksi'] ,
            //     'jumlah' => (($rpjumlah - $rppph[$param['notransaksi']]) * -1),
            //     'matauang' => 'IDR',
            //     'kurs' => '1',
            //     'kodeorg' => $rounit,
            //     'kodekegiatan' => '',
            //     'kodeasset' => '',
            //     'kodebarang' => $kodebarang,
            //     'nik' => '',
            //     'kodecustomer' => '',
            //     'kodesupplier' => $transportir,
            //     'noreferensi' => $noinvoice,
            //     'noaruskas' => $noaruskas,
            //     'kodevhc' => '',
            //     'nodok' => $nospk,
            //     'kodeblok' => '',
            //     'revisi' => '0',
            //     'kodesegment' => '0000000001',
            //     // 'bagian' => '',
            //     // 'others' => '',
            //     // 'kuantitas' => $kgkirim,
            //     // 'createby' => $_SESSION['standard']['userid'],
            //     // 'createtime' => date('Y-m-d H:i:s'),
            //     // 'updateby' => $_SESSION['standard']['userid'],
            //     // 'updatetime' => date('Y-m-d H:i:s'),
            // );
            // $noUrut++;

            // // JurnalDT
            // if ($rppph > 0) {
            //     $dataRes['detail2'][] = array(
            //         'nojurnal' => $nojurnal,
            //         'tanggal' => $tanggal,
            //         'nourut' => $noUrut,
            //         'noakun' => '2130103',
            //         'keterangan' => 'Jurnal PPH 23 BA Transport : ' . $param['notransaksi'] ,
            //         'jumlah' => $rppph[$param['notransaksi']] * -1,
            //         'matauang' => 'IDR',
            //         'kurs' => '1',
            //         'kodeorg' => $rounit,
            //         'kodekegiatan' => '',
            //         'kodeasset' => '',
            //         'kodebarang' => $kodebarang,
            //         'nik' => '',
            //         'kodecustomer' => '',
            //         'kodesupplier' => $transportir,
            //         'noreferensi' => $noinvoice,
            //         'noaruskas' => $noaruskas,
            //         'kodevhc' => '',
            //         'nodok' => $nospk,
            //         'kodeblok' => '',
            //         'revisi' => '0',
            //         'kodesegment' => '0000000001',
            //         // 'bagian' => '',
            //         // 'others' => '',
            //         // 'kuantitas' => $kgkirim,
            //         // 'createby' => $_SESSION['standard']['userid'],
            //         // 'createtime' => date('Y-m-d H:i:s'),
            //         // 'updateby' => $_SESSION['standard']['userid'],
            //         // 'updatetime' => date('Y-m-d H:i:s'),
            //     );
            //     $noUrut++;
            // }
            $dataRes['header3'] = array(
                'noinvoice' => $noinvoiceba,
                'tipeinvoice' => $tipeinv,
                'tanggal' => $tanggal,
                'nopo' => $param['notransaksi'],
                'kodesupplier' => $transportir,
                'nilaidpp' => $rpjumlah,
                'nilaiinvoice' => $totalinvoice,
                'nilaippn' => $nilaippn,
                'jatuhtempo' => '',
                'tanggalinvoice' => $tanggal,
                'status_bayar' => 0,
                'nofp' => '',
                'keterangan' => 'BA SPK TRANSPORT an: ' . $transportir . ' ',
                'keterangan2' => 'BA SPK TRANSPORT an: ' . $transportir . ' ',
                'noakun' => $noakunkredit,
                'terbayar' => '',
                'matauang' => 'IDR',
                'kurs' => 1,
                'posting' => 1,
                'uangmuka' => '',
                'jurnalstatus' => 0,
                'kodeorg' => $arrinduk[$rounit],
                'unit' => $rounit,
                'updateby' => '',
                'postingby' => '',
                'postingdate' => '',
                'noinvoicesupplier' => '',
                'uploadinvoice' => '',
                'statusdoc' => '',
                'historynofp' => '',
                'tanggalnofp' => '',
                'historytanggalfp' => '',
                'npwp' => '',
                'npwppph' => '',
                'jenistransaksi' => 00,
                'reksupplier' => '',
                'jenisfp' => '',
                'notransaksi_gr' => '',
                'noinvoiceum' => '',
                'jenissupplier' => 'TRANSPORTIR',
                'termin' => '',
                // 'bagian' => '',
                // 'createby' => $_SESSION['standard']['userid'],
                // 'createtime' => date('Y-m-d H:i:s'),
                // 'lokasitugasuser' => '',
                // 'updatetime' => date('Y-m-d H:i:s'),
            );

            #=Prep Detail

            // $totalall=$grandtotal+$nilaipph;
            $nmsupplier = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
            $noUrut = 1;
            $dataRes['detail3'][] = array(

                'noinvoice' => $noinvoiceba,
                'noakun' => $noakundebet,
                'kodevhc' => '',
                'kodeasset' => '',
                'nilai' => $rpjumlah,
                'noaruskas' => $noaruskas,
                'keterangan' => 'BA SPK TRANSPORTIR an : ' . $nmsupplier[$transportir],
                'noinvoice_referensi' => '',
                'nourut' => $noUrut,
                'notransaksi' => '', 
                'kodeblok' => '',
                'pajak' => 0,
                'kelompokbarang' => 400,
                'nopo' => $param['notransaksi'],
                'termin' => '',
                'kodekegiatan' => '',
            );
            $dataRes['detail3'][] = array(

                'noinvoice' => $noinvoiceba,
                'noakun' => $noakunppn,
                'kodevhc' => '',
                'kodeasset' => '',
                'nilai' => $nilaippn,
                'noaruskas' => $noaruskas,
                'keterangan' => 'PPN BA TRANSPORTIR an : ' . $nmsupplier[$transportir],
                'noinvoice_referensi' => '',
                'nourut' => $noUrut++,
                'notransaksi' => '', 
                'kodeblok' => '',
                'pajak' => 0,
                'kelompokbarang' => 400,
                'nopo' => $param['notransaksi'],
                'termin' => '',
                'kodekegiatan' => '',
            );

            $dataRes['detail3'][] = array(

                'noinvoice' => $noinvoiceba,
                'noakun' => $noakunpph,
                'kodevhc' => '',
                'kodeasset' => '',
                'nilai' => $nilaipph*-1,
                'noaruskas' => $noaruskas,
                'keterangan' => 'PPH 23 BA TRANSPORTIR an : ' . $nmsupplier[$transportir],
                'noinvoice_referensi' => '',
                'nourut' => $noUrut++,
                'notransaksi' => '', 
                'kodeblok' => '',
                'pajak' => 0,
                'kelompokbarang' => 400,
                'nopo' => $param['notransaksi'],
                'termin' => '',
                'kodekegiatan' => '',
            );

            // echo"<pre>";
            // print_r($dataRes['detail3']);
            // exit('error');

            #= update counter jurnal

            $str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where kodeunit='" . $rounit . "' and kodekelompok='" . $kodejurnalinvoice . "' and periode='" . $periode . "' ";
            $owlPDO->exec($str);

            // $str = "update ".$dbname.".pmn_batransport set posting=1,postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$param['notransaksi']."'";
            // $owlPDO->exec($str);

            #= jurnalht
            // if (count($dataRes['header2']) > 0) {
            //     foreach ($dataRes['header2'] as $key => $dataHead2) {
            //         $queryH2 = insertQuery($dbname, 'keu_jurnalht', $dataHead2, array_keys($dataHead2));
            //         $owlPDO->exec($queryH2);
            //     }
            // }

            // #= jurnaldt
            // if (count($dataRes['detail2']) > 0) {
            //     foreach ($dataRes['detail2'] as $key => $dataDet2) {
            //         $queryD2 = insertQuery($dbname, 'keu_jurnaldt', $dataDet2, array_keys($dataDet2));
            //         $owlPDO->exec($queryD2);
            //     }
            // }


            #Insert Header
            if (count($dataRes['header3']) > 0) {
                $queryH3 = insertQuery($dbname, 'keu_tagihanht', $dataRes['header3'], array_keys($dataRes['header3']));
                // exit('error '.$queryH3);
                $owlPDO->exec($queryH3);
            }

            // $query = insertQuery($dbname, 'bgt_budget', $data, $cols);
            #Insert Detail
            
            if (count($dataRes['detail3']) > 0) {
                foreach ($dataRes['detail3'] as $key => $dataDet3) {
                    $queryD3 = insertQuery($dbname, 'keu_tagihandt', $dataDet3, array_keys($dataDet3));
                    $owlPDO->exec($queryD3);
                }
            }

            $owlPDO->commit();
        } catch (PDOException $e) {

            $owlPDO->rollback();
            echo "Warning Posting Gagal \n" . addslashes($e->getMessage());
        }

        break;

    case 'getnospk':
        // exit('warning:'.$param['unit']);
        $optspk = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        if ($param['tipe'] != '' and $param['unit'] != '') {
            $table = "pmn_batransport";
            $str = "select * from " . $dbname . "." . $table . " where notransaksi='" . $param['unit'] . "' and transportir='" . $param['tipe'] . "' order by tanggal desc";
            $res = fetchdata($str);
            foreach ($res as $val) {
                if ($param['nospk'] == $val['nospk']) {
                    $optspk .= "<option value='" . $val['nospk'] . "' selected>" . $val['nospk'] . "</option>";
                } else {
                    $optspk .= "<option value='" . $val['nospk'] . "'>" . $val['nospk'] . "</option>";
                }
            }
        }

        echo $optspk;
        break;

    case 'getpphpersen':

        $str = "select supplierid from " . $dbname . ".log_5supnpwp where supplierid='" . $param['transportir'] . "'";
        $res = fetchdata($str);

        if (count($res) > 0) {
            #= nilai default jika ada NPWP
            $perspph = 2;
        } else {
            #= nilai default jika tidak ada NPWP
            $perspph = 4;
        }
        echo $perspph;
        break;

    case 'getTransportir':
        $optTransportir = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        if ($param['komoditi'] != '') {
            $table = "pmn_5ongkosangkutht";
            $str = "select lokasi,kodeunit from " . $dbname . "." . $table . " where komoditi='" . $param['komoditi'] . "' ";
            // exit('warning:'.$str);
            $res = fetchdata($str);
            foreach ($res as $val) {
                $optTransportir .= "<option value='" . $val['lokasi'] . "'>" . $val['lokasi'] . " - " . $namatransportir[$val['lokasi']] . "</option>";
            }
        }

        echo $optTransportir;
        break;

    case 'saveht':
        #bentuk tanggal between
        $arrtanggal = rangeTanggalarr($param['tanggalkirim1'], $param['tanggalkirim2']);

        #= validasi
        $texterror = '';
        $whrjenis = "";
        
        $pt_lokasi = getindukPT($_SESSION['empl']['lokasitugas']);
        // exit('warning: '.$pt_lokasi);

        if ($pt_lokasi != 'PPP') {
            $whrjenis = " and jenis = '{$param['jenisba']}'";
        }

        $str = "select *
        from " . $dbname . ".pmn_batransport
        where unit='" . $param['unit'] . "'
        and ((tanggalkirim1 >= '" . tanggalsystemn($param['tanggalkirim1']) . "' and tanggalkirim2 <= '" . tanggalsystemn($param['tanggalkirim2']) . "') OR (tanggalkirim1 <= '" . tanggalsystemn($param['tanggalkirim1']) . "' AND tanggalkirim2 >= '" . tanggalsystemn($param['tanggalkirim2']) . "')) and nospk='' and transportir='" . $param['tipe'] . "' AND kodeunit = '{$_SESSION['empl']['lokasitugas']}' AND kodebarang = '{$param['komoditi']}' and nokontrak = '{$param['nokontrak']}' " . $whrjenis . " GROUP BY notransaksi";
        // exit("Error: " . $str);
        $res = fetchData($str);
        if (!empty($res)) {
            foreach ($res as $bar) {
                $texterror .= "&nbsp;&nbsp - " . $bar['notransaksi'] . " dengan tanggal kirim (" . tanggalnormal($bar['tanggalkirim1']) . " - " . tanggalnormal($bar['tanggalkirim2']) . "). <br/>";
            }
        }

        if ($texterror != '') {
            if ($pt_lokasi != 'PPP') {
                $arrJenis = [0 => 'Normal', 1 => 'Return'];
                $namaJenis = isset($arrJenis[$param['jenisba']]) ? $arrJenis[$param['jenisba']] : $param['jenisba'];
                exit("Warning: Gagal Proses <br/> Sudah ada data Tipe BA " . $namaJenis . " diantara tanggal " . $param['tanggalkirim1'] . " - " . $param['tanggalkirim2'] . " dengan Nomor Transaksi: <br/>" . $texterror);
            } else {
                exit("Warning: Gagal Proses <br/> Sudah ada data diantara tanggal " . $param['tanggalkirim1'] . " - " . $param['tanggalkirim2'] . " dengan Nomor Transaksi: <br/>" . $texterror);
            }
        }

        $unit = $param['unit'];
        $tipe = $param['tipe'];
        $komoditi = $param['komoditi'];
        $tanggal = tanggalsystemn($param['tanggal']);

        # Parameter Applikasi Kebun
        $sql = selectQuery($dbname, "setup_parameterappl", "*", "kodeaplikasi='BA' AND kodeparameter='BATS'");
        $res = fetchData($sql);

        foreach ($res as $val) {
            $dataKebun = $val['nilai'];
        }
 
        $datamentah = explode(",", $dataKebun); // Tarikan Unit Kebun

        if (in_array($param['komoditi'], $datamentah)) {
            $param['unit'] = $_SESSION['empl']['lokasitugas']; // Berdasarkan Lokasi Tugas User
        } else {
            $param['unit'] = $param['unit'];
        }

 
        $kdpt = getindukPT($param['unit']);
        $bln = substr($tanggal, 5, 2);
        $lokunituser = $unit;
 
        $expltgl = explode("-", $tanggal);
        $thnex = substr($expltgl[0], -2);
        $blnex = $expltgl[1];
        $kod = "BA-TRP";

        // Get last counter for this period
        $lastQuery = "SELECT SUBSTRING_INDEX(notransaksi, '/', -1) AS nourut 
            FROM " . $dbname . ".`pmn_batransport` 
            WHERE notransaksi LIKE '%" . $kdpt . "/".$lokunituser."/" . $kod . "/" . $thnex . "/" . $blnex . "/%' 
            ORDER BY nourut DESC LIMIT 1";
        $rLastQuery = fetchData($lastQuery);
        $last = 0;
        if (!empty($rLastQuery) && isset($rLastQuery[0]['nourut'])) {
            $last = intval($rLastQuery[0]['nourut']);
        }
        $counter = str_pad($last + 1, 4, "0", STR_PAD_LEFT);

        $notransaksi =  "" . $kdpt . "/".$lokunituser ."/" . $kod . "/" . $thnex . "/" . $blnex . "/" . $counter;
        $arrData = array(
            'notransaksi' => $notransaksi,
            'persenpph' => $pph,
        );

        // echo $notransaksi;
        echo json_encode($arrData);
        break;

    case 'loaddatadt':

        if ($param['print'] == 'pdf') {
            $tab .= "<table cellpading=1 cellspacing=1 border=1 class=sortable width=100% style='font-size:10px'>
  						<thead>
   							<tr class=rowheader>
								<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
								<th align=center>" . $_SESSION['lang']['nospk'] . "</th>
								<th align=center>" . $_SESSION['lang']['komoditi'] . "</th>
								<th align=center>" . $_SESSION['lang']['NoKontrak'] . "</th>
								<th align=center>" . $_SESSION['lang']['transportir'] . "</th>
								<th align=center>" . $_SESSION['lang']['noTiket'] . " " . $_SESSION['lang']['kirim'] . "</th>
								<th align=center>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['kirim'] . "</th>
								<th align=center>" . $_SESSION['lang']['nopol'] . "</th>
								<th align=center>" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['kirim'] . "</th>
								<th align=center>" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['kirim'] . " </br> (Buyer)</th>
								<th align=center>" . $_SESSION['lang']['noTiket'] . " " . $_SESSION['lang']['tujuan'] . "</th>
								<th align=center>" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['diterima'] . "</th>
								<th align=center>Tonbag</th>
								<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['diterima'] . "</th>
								<th align=center>" . $_SESSION['lang']['selisih'] . "<br>(" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['diterima'] . "-" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['kirim'] . ")</th>
								<th align=center>" . $_SESSION['lang']['rpperkg'] . "</th>

								<th align=center>" . $_SESSION['lang']['jumlahrp'] . "</th>
								<th align=center>" . $_SESSION['lang']['toleransi'] . " (%)</th>
								<th align=center>" . $_SESSION['lang']['toleransi'] . " (Kg)</th>
								<th align=center>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['klaim'] . "<br>(" . $_SESSION['lang']['selisih'] . "-" . $_SESSION['lang']['kg'] . "<br>" . $_SESSION['lang']['klaim'] . ")</th>
								<th align=center>" . $_SESSION['lang']['rpperkg'] . " " . $_SESSION['lang']['klaim'] . "</th>
								<th align=center>" . $_SESSION['lang']['jumlahrp'] . " " . $_SESSION['lang']['klaim'] . "</th> 
   							</tr>
 						</thead>
   					<tbody id=listdatadt>";
        }

        switch ($param['tipe']) {

            case 'sip':

                $str = "select * from " . $dbname . ".pabrik_bamutasi  where nosip='" . $param['nospk'] . "' and unit='" . $param['unit'] . "' and substr(tanggalbongkar1,1,10) >= '" . tanggalsystemn($param['tanggalkirim1']) . "'  and substr(tanggalbongkar2,1,10) <= '" . tanggalsystemn($param['tanggalkirim2']) . "'";
                // echo $str;
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $arrnotiketkirim[$bar['notransaksi']] = $bar['notransaksi'];
                    $dttanggalkirimpks[$bar['notransaksi']] = $bar['tanggal'];
                    @$dtkgkirim[$bar['notransaksi']] += $bar['jumlah'];
                }

                if (@count($arrnotiketkirim) < 1) {
                    exit("Warning:Nomor SIP untuk " . $param['nospk'] . " ditanggal " . tanggalsystemn($param['tanggalkirim1']) . " s/d " . tanggalsystemn($param['tanggalkirim2']) . " belum dibuatkan BA Pengirmannya atau salah pemilihan tanggal, cocokan data transaksi pengiriman dengan taggal pembuat ba transpor ini");
                }

                #= ambil data penerimaannya berasarkan nomor sip dan noreferensi= nomor ba pengirman
                $str = "select * from " . $dbname . ".pabrik_bamutasi  where nosip='" . $param['nospk'] . "' and   noreferensi in ('" . implode("','", $arrnotiketkirim) . "')";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtnotiketterima[$bar['noreferensi']] = $bar['notransaksi'];
                    @$dtkgterima[$bar['noreferensi']] += $bar['jumlah'];
                    @$dtkgterimaawal[$bar['noreferensi']] += $bar['jumlah'];
                }
                // if($_SESSION['standard']['username']=='tim.owl3'){
                // echo $str;
                // }

                #= ambil data BA untuk rpkg, toleransi, transportir
                $str = "select * from " . $dbname . ".pmn_suratperintahpengiriman  where nodo='" . $param['nospk'] . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtrpkg[$bar['nodo']] = $bar['harga'];
                    $dttransportir[$bar['nodo']] = $bar['transportir'];
                    $dtpersentoleransi[$bar['nodo']] = $bar['toleransi'];
                    $dtkgtoleransi[$bar['nodo']] = $bar['kgtoleransi'];
                    $arrnokontrak[$bar['nokontrak']] = $bar['nokontrak'];
                    $dtnokontrak[$bar['nodo']] = $bar['nokontrak'];
                    $dtnoakundebet[$bar['nodo']] = $bar['noakundebet'];
                    $dtkodebarang[$bar['nodo']] = $bar['kodebarang'];
                }

                #= data lama untuk ambil rp/kg claim
                $str = "select count(*) as jumrow from " . $dbname . ".pmn_batransport  where notransaksi='" . $param['notransaksi'] . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    @$datatransaksi = $bar['jumrow'];
                }

                $dtrpkgclaim = array();
                if ($datatransaksi == 0) {

                    #= harga claim ambil dari kontrak
                    if (@count($arrnokontrak) > 0) {
                        $str = "select * from " . $dbname . ".pmn_kontrakjual  where nokontrak in ('" . implode("','", $arrnokontrak) . "')";
                        // exit("Error:$str");
                        $res = fetchdata($str);
                        foreach ($res as $bar) {
                            $dtrpkgclaim[$bar['nokontrak']] = $bar['hargasatuan'];
                        }
                    }
                } else {
                    $str = "select * from " . $dbname . ".pmn_batransport  where notransaksi='" . $param['notransaksi'] . "'";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while ($bar = $res->fetch()) {
                        $dtrpkgclaim[$bar['notiket']] = $bar['rpkgclaim'];
                        @$dtkgtonbag[$bar['notiket']] = $bar['kgtonbag'];
                        if ($bar['kgterimaawal'] == 0) {
                            @$dtkgterimaawal[$bar['notiket']] = $bar['kgterima'];
                        } else {
                            @$dtkgterimaawal[$bar['notiket']] = $bar['kgterimaawal'];
                        }
                        @$dtkgterima[$bar['notiket']] = $bar['kgterima'];
                        @$dtkgclaim[$bar['notiket']] = $bar['kgclaim'];
                    }
                }

                if (@count($arrnotiketkirim) > 0) {
                    foreach ($arrnotiketkirim as $dtnotiketkirim) {
                        @$nouruttiket++;
                        if ($nouruttiket % 2 == 0) {
                            $bgcolor = "style=background-color:lightblue;";
                        } else {
                            $bgcolor = "";
                        }
                        @$no++;
                        $tab .= "<tr  " . $bgcolor . " class=rowcontent id=row" . $no . ">";
                        $tab .= "<td align=center>" . $no . "</td>";
                        $tab .= "<td align=center id=nospk" . $no . " align=left>" . $param['nospk'] . "</td>";
                        $tab .= "<td align=center id=kodebarang" . $no . " align=left>" . $dtkodebarang[$param['nospk']] . "</td>";
                        $tab .= "<td align=center id=nokontrak" . $no . " align=left>" . $dtnokontrak[$param['nospk']] . "</td>";
                        $tab .= "<td align=center id=transportir" . $no . ">" . $dttransportir[$param['nospk']] . "</td>";
                        $tab .= "<td align=center id=notiket" . $no . " align=left>" . $dtnotiketkirim . "</td>";
                        $tab .= "<td align=center id=tanggalkirimpks" . $no . " align=left>" . tanggalnormal($dttanggalkirimpks[$dtnotiketkirim]) . "</td>";
                        $tab .= "<td align=center id=nokendaraan" . $no . " align=left></td>";
                        $tab .= "<td align=center id=kgkirim" . $no . " align=right>" . number_format($dtkgkirim[$dtnotiketkirim], 2) . "</td>";
                        $tab .= "<td align=center>" . $dtnotiketterima[$dtnotiketkirim] . "</td>";
                        $tab .= "<td align=center id=kgterimaawal" . $no . ">" . $dtkgterimaawal[$dtnotiketkirim] . "</td>";
                        #= tonbag

                        $tab .= "<td align=center><input type=text id=kgtonbag" . $no . " size=20  onblur=getkgterima(" . $no . ")  class=myinputtextnumber onkeyup=z.numberFormat('kgtonbag" . $no . "',2); value='" . $dtkgtonbag[$dtnotiketkirim] . "' onkeypress='return_tanpa_kutip_dan_sepasi(event)' /></td>";

                        $tab .= "<td align=center id=kgterima" . $no . ">" . $dtkgterima[$dtnotiketkirim] . "</td>";
                        #= selisih
                        $dtkgselisih[$dtnotiketkirim] = ($dtkgterima[$dtnotiketkirim] - $dtkgkirim[$dtnotiketkirim]);
                        $tab .= "<td align=center id=kgselisih" . $no . ">" . number_format($dtkgselisih[$dtnotiketkirim], 2) . "</td>";
                        $tab .= "<td align=center id=rpkg" . $no . " align=right>" . number_format($dtrpkg[$param['nospk']], 2) . "</td>";
                        #= total rp
                        $dttotalrp[$dtnotiketkirim] = $dtkgkirim[$dtnotiketkirim] * $dtrpkg[$param['nospk']];
                        $tab .= "<td align=center id=rpjumlah" . $no . " align=right>" . number_format($dttotalrp[$dtnotiketkirim], 2) . "</td>";

                        #= toleransi, jika persen terisi maka hitung kg, jika kg terisi maka persen di 0-kan
                        if ($dtpersentoleransi[$param['nospk']] > 0) {
                            $tab .= "<td align=center id=persentoleransi" . $no . ">" . $dtpersentoleransi[$param['nospk']] . "</td>";
                            #= toleransi kg-nya
                            $dtkgtoleransi[$dtnotiketkirim] = round($dtpersentoleransi[$param['nospk']] / 100 * $dtkgkirim[$dtnotiketkirim] * -1);
                            $tab .= "<td align=center id=kgtoleransi" . $no . ">" . $dtkgtoleransi[$dtnotiketkirim] . "</td>";
                        } else {
                            $tab .= "<td align=center id=persentoleransi" . $no . ">0</td>";
                            #= toleransi kg
                            $dtkgtoleransi[$dtnotiketkirim] = $dtkgtoleransi[$param['nospk']] * -1;
                            $tab .= "<td align=center id=kgtoleransi" . $no . ">" . $dtkgtoleransi[$dtnotiketkirim] . "</td>";
                        }

                        #= kg claim (kg toleransi - kg selisih)
                        $disabledrpkgclaim = "";
                        $dtkgclaim[$dtnotiketkirim] = $dtkgselisih[$dtnotiketkirim] - $dtkgtoleransi[$dtnotiketkirim];
                        if ($dtkgclaim[$dtnotiketkirim] >= 0) {
                            $dtkgclaim[$dtnotiketkirim] = 0;
                            $disabledrpkgclaim = "disabled";
                        }
                        $tab .= "<td align=center id=kgclaim" . $no . ">" . $dtkgclaim[$dtnotiketkirim] . "</td>";

                        if ($datatransaksi == 0) {
                            $tab .= "<td align=center><input type=text  id=rpkgclaim" . $no . " " . $disabledrpkgclaim . " onblur=getrpclaim(" . $no . ") value='" . @$dtrpkgclaim[$dtnokontrak[$param['nospk']]] . "' id=rpkgclaim" . $no . " size=20  class=myinputtext></td>";
                            #= rpclaim = rpkgclaim * kgclaim
                            @$dtrpclaim[$dtnotiketkirim] = $dtkgclaim[$dtnotiketkirim] * $dtrpkgclaim[$dtnokontrak[$param['nospk']]];
                            $tab .= "<td align=center id=rpclaim" . $no . ">" . $dtrpclaim[$dtnotiketkirim] . "</td>";
                        } else {
                            $tab .= "<td align=center><input type=text  id=rpkgclaim" . $no . " " . $disabledrpkgclaim . " onblur=getrpclaim(" . $no . ") value='" . @$dtrpkgclaim[$dtnotiketkirim] . "' id=rpkgclaim" . $no . " size=20  class=myinputtext></td>";
                            #= rpclaim = rpkgclaim * kgclaim
                            @$dtrpclaim[$dtnotiketkirim] = $dtkgclaim[$dtnotiketkirim] * $dtrpkgclaim[$dtnotiketkirim];
                            $tab .= "<td align=center id=rpclaim" . $no . ">" . $dtrpclaim[$dtnotiketkirim] . "</td>";
                        }
                        $tab .= "<td align=center id=noakundebet" . $no . ">" . $dtnoakundebet[$param['nospk']] . "</td>";
                        $tab .= "</tr>";
                    }
                    if ($param['print'] != 'pdf') {
                        $tab .= "<tr>";
                        $tab .= "<td align=center colspan=22><button  id=save class=mybutton onclick=savedt(" . $no . ")>" . $_SESSION['lang']['save'] . "</button>";
                        $tab .= "</tr>";
                    }
                }

                break;

            case 'ipkd':

                $no = 0;
                $arrnotiketkirim = array();
                $str = "select * from " . $dbname . ".pabrik_timbangan_vw  where nosipb='" . $param['nospk'] . "' and millcode='" . $param['unit'] . "' and tanggal between '" . tanggalsystemn($param['tanggalkirim1']) . "' and '" . tanggalsystemn($param['tanggalkirim2']) . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $arrnotiketkirim[$bar['notiket']] = $bar['notiket'];
                    $dtnokendaraan[$bar['notiket']] = $bar['nokendaraan'];
                    $dtnokontrak[$bar['notiket']] = $bar['nokontrak'];
                    $dtkgkirim[$bar['notiket']] = $bar['beratbersih'];
                    $arrnokontrak[$bar['nokontrak']] = $bar['nokontrak'];
                    $dttanggalkirimpks[$bar['notiket']] = $bar['tanggal'];
                }

                $str = "select * from " . $dbname . ".pmn_spk_ipkd where nospk='" . $param['nospk'] . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtrpkg[$bar['nospk']] = $bar['rpkg'];
                    $dttransportir[$bar['nospk']] = $bar['transportirdarat'];
                    $dtnoakundebet[$bar['nospk']] = $bar['noakundebet'];
                    $dtkodebarang[$bar['nospk']] = $bar['kodebarang'];
                }

                #= harga claim ambil dari kontrak
                $rpkgclaim = array();
                $str = "select * from " . $dbname . ".pmn_kontrakjual  where nokontrak in ('" . implode("','", $arrnokontrak) . "')";
                // echo $str;
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtrpkgclaim[$bar['nokontrak']] = $bar['hargasatuan'];
                }

                foreach ($arrnotiketkirim as $dtnotiketkirim) {
                    @$nouruttiket++;
                    if ($nouruttiket % 2 == 0) {
                        $bgcolor = "style=background-color:lightblue;";
                    } else {
                        $bgcolor = "";
                    }
                    @$no++;
                    $tab .= "<tr  " . $bgcolor . " class=rowcontent id=row" . $no . ">";
                    $tab .= "<td align=center>" . $no . "</td>";
                    $tab .= "<td align=center id=nospk" . $no . " align=left>" . $param['nospk'] . "</td>";
                    $tab .= "<td align=center id=kodebarang" . $no . " align=left>" . $dtkodebarang[$param['nospk']] . "</td>";
                    $tab .= "<td align=center id=nokontrak" . $no . " align=left>" . $dtnokontrak[$dtnotiketkirim] . "</td>";
                    $tab .= "<td align=center id=transportir" . $no . ">" . $dttransportir[$param['nospk']] . "</td>";
                    $tab .= "<td align=center id=notiket" . $no . " align=left>" . $dtnotiketkirim . "</td>";
                    $tab .= "<td align=center id=tanggalkirimpks" . $no . " align=left>" . tanggalnormal($dttanggalkirimpks[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center id=nokendaraan" . $no . " align=left>" . $dtnokendaraan[$dtnotiketkirim] . "</td>";
                    $tab .= "<td align=center id=kgkirim" . $no . " align=right>" . number_format($dtkgkirim[$dtnotiketkirim], 2) . "</td>";
                    $tab .= "<td align=center></td>";
                    $tab .= "<td align=center id=kgterimaawal" . $no . ">" . $dtkgterimaawal[$dtnotiketkirim] . "</td>";
                    #= tonbag
                    $tab .= "<td align=center><input type=text id=kgtonbag" . $no . " size=20 disabled class=myinputtextnumber></td>";
                    $tab .= "<td align=center id=kgterima" . $no . "></td>";
                    $tab .= "<td align=center id=kgselisih" . $no . "></td>";
                    $tab .= "<td align=center id=rpkg" . $no . " align=right>" . number_format($dtrpkg[$param['nospk']], 2) . "</td>";
                    $dttotalrp[$dtnotiketkirim] = $dtkgkirim[$dtnotiketkirim] * $dtrpkg[$param['nospk']];
                    $tab .= "<td align=center id=rpjumlah" . $no . " align=right>" . number_format($dttotalrp[$dtnotiketkirim], 2) . "</td>";
                    $tab .= "<td align=center id=persentoleransi" . $no . "></td>";
                    $tab .= "<td align=center id=kgtoleransi" . $no . "></td>";
                    $tab .= "<td align=center id=kgclaim" . $no . "></td>";
                    $tab .= "<td align=center><input type=text  id=rpkgclaim" . $no . " disabled class=myinputtext></td>";
                    $tab .= "<td align=center id=rpclaim" . $no . "></td>";
                    $tab .= "<td align=center id=noakundebet" . $no . ">" . $dtnoakundebet[$param['nospk']] . "</td>";
                }
                if ($param['print'] != 'pdf' || $param['print'] != 'pdf') {
                    $tab .= "<tr>";
                    $tab .= "<td align=center colspan=22><button  id=save class=mybutton onclick=savedt(" . $no . ")>" . $_SESSION['lang']['save'] . "</button>";
                    $tab .= "</tr>";
                }
                break;

            case 'etc':
                $arrnotiketkirim = array();
                $str = "select * from " . $dbname . ".pabrik_timbangan_vw  where nosipb='" . $param['nospk'] . "' and millcode='" . $param['unit'] . "' and tanggal between '" . tanggalsystemn($param['tanggalkirim1']) . "' and '" . tanggalsystemn($param['tanggalkirim2']) . "'";
                // echo $str;
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $arrnotiketkirim[$bar['notiket']] = $bar['notiket'];
                    // $dtnosipb[$bar['notiket']]=$bar['nosipb'];
                    $dtnokontrak[$bar['notiket']] = $bar['nokontrak'];
                    $dtkgkirim[$bar['notiket']] = $bar['beratbersih'];
                    $arrnokontrak[$bar['nokontrak']] = $bar['nokontrak'];
                    $dttanggalkirimpks[$bar['notiket']] = $bar['tanggal'];
                    $dtnokendaraan[$bar['notiket']] = $bar['nokendaraan'];
                    $countnotiketterima[$bar['notiket']] = 0;
                }

                // print_r($arrnotiketkirim);
                // exit("Error:".count($arrnotiketkirim));
                if (count($arrnotiketkirim) > 0) {
                    $str = "select * from " . $dbname . ".pabrik_timbangan_vw  where nosipb='" . $param['nospk'] . "' and norefrensi in ('" . implode("','", $arrnotiketkirim) . "')";
                    // echo $str;exit();
                    $res = fetchdata($str);
                    foreach ($res as $bar) {
                        $arrnotiketterima[$bar['notiket']] = $bar['notiket'];
                        $listnotiketterima[$bar['norefrensi']][$bar['notiket']] = $bar['notiket'];
                        @$countnotiketterima[$bar['norefrensi']] += 1;
                        @$rowspan[$bar['norefrensi']] += 1;
                        $dtkgterimadt[$bar['norefrensi']][$bar['notiket']] = $bar['beratbersih'];
                        @$dtkgterima[$bar['norefrensi']] += $bar['beratbersih'];
                    }
                }

                $str = "select * from " . $dbname . ".pmn_spk_etc  where kodept='" . $kodept[$param['unit']] . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtrpkg[$bar['nospk']] = $bar['rpkg'];
                    $dtpersentoleransi[$bar['nospk']] = $bar['toleransi'];
                    $dtkgtoleransi[$bar['nospk']] = $bar['kgtoleransi'];
                    $dttransportir[$bar['nospk']] = $bar['transportirdarat'];
                    $dtnoakundebet[$bar['nospk']] = $bar['noakundebet'];
                    $dtkodebarang[$bar['nospk']] = $bar['kodebarang'];
                }

                #= data lama untuk ambil rp/kg claim
                $str = "select count(*) as jumrow from " . $dbname . ".pmn_batransport  where notransaksi='" . $param['notransaksi'] . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    @$datatransaksi = $bar['jumrow'];
                }

                $dtrpkgclaim = array();
                if ($datatransaksi == 0) {
                    #= harga claim ambil dari kontrak
                    if (@count($arrnokontrak) > 0) {
                        $str = "select * from " . $dbname . ".pmn_kontrakjual  where nokontrak in ('" . implode("','", $arrnokontrak) . "')";
                        // exit("Error:$str");
                        $res = fetchdata($str);
                        foreach ($res as $bar) {
                            $dtrpkgclaim[$bar['nokontrak']] = $bar['nokontrak'];
                            $dtvalidasikontrak[$bar['nokontrak']] = $bar['nokontrak'];
                        }
                    }
                } else {
                    $str = "select * from " . $dbname . ".pmn_batransport  where notransaksi='" . $param['notransaksi'] . "'";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while ($bar = $res->fetch()) {
                        $dtrpkgclaim[$bar['notiket']] = $bar['rpkgclaim'];
                    }
                }

                // echo"<pre>";
                // print_r($arrnotiketkirim);
                // echo"</pre>";
                // exit('Error');

                $counter = $nouruttiket = 0;
                foreach ($arrnotiketkirim as $dtnotiketkirim) {
                    @$nouruttiket++;
                    if ($nouruttiket % 2 == 0) {
                        $bgcolor = "style=background-color:lightblue;";
                    } else {
                        $bgcolor = "";
                    }
                    @$no++;
                    $tab .= "<tr  " . $bgcolor . " class=rowcontent id=row" . $no . ">";
                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "'>" . $no . "</td>";
                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=nospk" . $no . ">" . $param['nospk'] . "</td>";
                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kodebarang" . $no . ">" . $dtkodebarang[$param['nospk']] . "</td>";
                    if ($dtvalidasikontrak[$dtnokontrak[$dtnotiketkirim]] == $dtnokontrak[$dtnotiketkirim]) {
                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=nokontrak" . $no . ">" . $dtnokontrak[$dtnotiketkirim] . "</td>";
                    } else {
                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=nokontrak" . $no . "></td>";
                    }
                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=transportir" . $no . ">" . $dttransportir[$param['nospk']] . "</td>";
                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=notiket" . $no . ">" . $dtnotiketkirim . "</td>";
                    $tab .= "<td align=center  rowspan='" . $rowspan[$dtnotiketkirim] . "' id=tanggalkirimpks" . $no . ">" . tanggalnormal($dttanggalkirimpks[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center  rowspan='" . $rowspan[$dtnotiketkirim] . "' id=nokendaraan" . $no . ">" . $dtnokendaraan[$dtnotiketkirim] . "</td>";
                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgkirim" . $no . ">" . $dtkgkirim[$dtnotiketkirim] . "</td>";
                    $nokirim = 0;
                    if ($countnotiketterima[$dtnotiketkirim] > 0) {
                        foreach ($arrnotiketterima as $dtnotiketterima) {
                            if (@$listnotiketterima[$dtnotiketkirim][$dtnotiketterima] != '') {
                                $nokirim++;
                                if ($nokirim == 1) {
                                    $tab .= "<td align=center>" . $dtnotiketterima . "</td>";
                                    $tab .= "<td align=center  id=kgterimaawal" . $no . ">" . $dtkgterimadt[$dtnotiketkirim][$dtnotiketterima] . "</td>";

                                    #= tonbag
                                    $tab .= "<td align=center><input type=text id=kgtonbag" . $no . " size=20 disabled class=myinputtextnumber></td>";

                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgterima" . $no . ">" . $dtkgterima[$dtnotiketkirim] . "</td>";

                                    #= selisih
                                    $dtkgselisih[$dtnotiketkirim] = ($dtkgterima[$dtnotiketkirim] - $dtkgkirim[$dtnotiketkirim]);
                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgselisih" . $no . ">" . $dtkgselisih[$dtnotiketkirim] . "</td>";

                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=rpkg" . $no . ">" . $dtrpkg[$param['nospk']] . "</td>";
                                    #= totalrp
                                    $dttotalrp[$dtnotiketkirim] = $dtkgkirim[$dtnotiketkirim] * $dtrpkg[$param['nospk']];
                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=rpjumlah" . $no . ">" . $dttotalrp[$dtnotiketkirim] . "</td>";

                                    #= toleransi, jika persen terisi maka hitung kg, jika kg terisi maka persen di 0-kan
                                    if ($dtpersentoleransi[$param['nospk']] > 0) {
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=persentoleransi" . $no . ">" . $dtpersentoleransi[$param['nospk']] . "</td>";

                                        #= toleransi kg-nya
                                        $dtkgtoleransi[$dtnotiketkirim] = round($dtpersentoleransi[$param['nospk']] / 100 * $dtkgkirim[$dtnotiketkirim] * -1);
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgtoleransi" . $no . ">" . $dtkgtoleransi[$dtnotiketkirim] . "</td>";
                                    } else {
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=persentoleransi" . $no . ">0</td>";
                                        #= toleransi kg
                                        $dtkgtoleransi[$dtnotiketkirim] = $dtkgtoleransi[$param['nospk']] * -1;
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgtoleransi" . $no . ">" . $dtkgtoleransi[$dtnotiketkirim] . "</td>";
                                    }

                                    #= kg claim (kg toleransi - kg selisih)
                                    $disabledrpkgclaim = "";
                                    $dtkgclaim[$dtnotiketkirim] = $dtkgselisih[$dtnotiketkirim] - $dtkgtoleransi[$dtnotiketkirim];
                                    if ($dtkgclaim[$dtnotiketkirim] >= 0) {
                                        $dtkgclaim[$dtnotiketkirim] = 0;
                                        $disabledrpkgclaim = "disabled";
                                    }
                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgclaim" . $no . ">" . $dtkgclaim[$dtnotiketkirim] . "</td>";

                                    if ($datatransaksi == 0) {
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "'><input type=text  id=rpkgclaim" . $no . " " . $disabledrpkgclaim . " onblur=getrpclaim(" . $no . ") value='" . @$dtrpkgclaim[$dtnokontrak[$dtnotiketkirim]] . "' id=rpkgclaim" . $no . " size=20  class=myinputtext></td>";
                                        #= rpclaim = rpkgclaim * kgclaim
                                        @$dtrpclaim[$dtnotiketkirim] = $dtkgclaim[$dtnotiketkirim] * $dtrpkgclaim[$dtnokontrak[$dtnotiketkirim]];
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=rpclaim" . $no . ">" . $dtrpclaim[$dtnotiketkirim] . "</td>";
                                    } else {
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "'><input type=text  id=rpkgclaim" . $no . " " . $disabledrpkgclaim . " onblur=getrpclaim(" . $no . ") value='" . @$dtrpkgclaim[$dtnotiketkirim] . "' id=rpkgclaim" . $no . " size=20  class=myinputtext></td>";
                                        #= rpclaim = rpkgclaim * kgclaim
                                        @$dtrpclaim[$dtnotiketkirim] = $dtkgclaim[$dtnotiketkirim] * $dtrpkgclaim[$dtnotiketkirim];
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=rpclaim" . $no . ">" . $dtrpclaim[$dtnotiketkirim] . "</td>";
                                    }
                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=noakundebet" . $no . ">" . $dtnoakundebet[$param['nospk']] . "</td>";

                                    $tab .= "</tr>";
                                } else {
                                    $tab .= "<tr  " . $bgcolor . " class=rowcontent>";
                                    $tab .= "<td align=center>" . $dtnotiketterima . "</td>";
                                    $tab .= "<td align=center>" . $dtkgterimadt[$dtnotiketkirim][$dtnotiketterima] . "</td>";
                                    $tab .= "</tr>";
                                }
                            }
                        }
                    } else {
                        $tab .= "<td align=center></td>";
                        $tab .= "<td align=center id=kgterimaawal" . $no . "></td>";
                        $tab .= "<td align=center><input type=text id=kgtonbag" . $no . " size=20 disabled class=myinputtextnumber></td>";
                        $tab .= "<td align=center id=kgterima" . $no . "></td>";
                        $tab .= "<td align=center id=kgselisih" . $no . "></td>";
                        $tab .= "<td align=center id=rpkg" . $no . ">" . $dtrpkg[$param['nospk']] . "</td>";
                        $dttotalrp[$dtnotiketkirim] = $dtkgkirim[$dtnotiketkirim] * $dtrpkg[$param['nospk']];
                        $tab .= "<td align=center id=rpjumlah" . $no . ">" . $dttotalrp[$dtnotiketkirim] . "</td>";
                        $tab .= "<td align=center id=persentoleransi" . $no . "></td>";
                        $tab .= "<td align=center id=kgtoleransi" . $no . "></td>";
                        $tab .= "<td align=center id=kgclaim" . $no . "></td>";
                        $tab .= "<td align=center><input type=text  id=rpkgclaim" . $no . " disabled onblur=getrpclaim(" . $no . ") id=rpkgclaim" . $no . " size=20  class=myinputtext></td>";
                        $tab .= "<td align=center id=rpclaim" . $no . "></td>";
                        $tab .= "<td align=center id=noakundebet" . $no . ">" . $dtnoakundebet[$param['nospk']] . "</td>";
                    }
                }
                if ($param['print'] != 'pdf' || $param['print'] != 'pdf') {
                    $tab .= "<tr>";
                    $tab .= "<td align=center colspan=22><button  id=save class=mybutton onclick=savedt(" . $no . ")>" . $_SESSION['lang']['save'] . "</button>";
                    $tab .= "</tr>";
                }
                break;

            default:
                $no = 0;
                $err = 0;
                $persenpph = $param['persenpph'];
                $persenppn = $param['persenppn'];
                $arrnotiketkirim = array();
                $arrnokontrak = array();
            
                $toleransisusut= $param['persentlrsusut'] / 100;
                

                $whr = '';
                if ($param['nospk'] != '') {
                    $whr .= "and nodo='" . $param['nospk'] . "' ";
                }

                # Cek Customer
                $sql = selectQuery($dbname, "pmn_4customer", "*");
                $res = fetchData($sql, "OBJECT");

                foreach ($res as $val):
                    $unitCustomer[$val->kodecustomer] = $val->kodeunit;
                endforeach;
                # End

                # Parameter Applikasi Kebun
                $sql = selectQuery($dbname, "setup_parameterappl", "*", "kodeaplikasi='BA' AND kodeparameter='BATS'");
                $res = fetchData($sql);

                foreach ($res as $val) {
                    $dataKebun = $val['nilai'];
                }
                // echo "<pre>";
                // print_r($param);
                // if ($dataKebun == "") {
                //     exit("<label hidden>Error :</label> Setup Parameter Applikasi belum di setup dengan kodeaplikasi BA dan kodeparameter BATS");
                // }

                $datamentah = explode(",", $dataKebun);

                $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
                # Cek jika Unit di pilih SELURUHNYA
                # Maka cek Induk nya untuk IN
                if ($param['unit'] == '') {
                    $whrMillcode = " AND millcode IN (SELECT kodeorganisasi FROM {$dbname}.organisasi WHERE tipe='PABRIK' AND induk='{$induk[$_SESSION['empl']['lokasitugas']]}')";
                    $whrMillcodeUnit = " AND kodeunit IN (SELECT kodeorganisasi FROM {$dbname}.organisasi WHERE kodeorganisasi = '{$_SESSION['empl']['lokasitugas']}')";
                } else {
                    $whrMillcode = " AND millcode = '{$param['unit']}'";
                    $whrMillcodeUnit = " AND kodeunit = '{$_SESSION['empl']['lokasitugas']}'";
                }

                $whrjenis = "";
                if ($param['jenisba'] == '0') {
                    $whrjenis = " AND wbcond='Normal' AND notiket NOT IN (
                        SELECT DISTINCT tiketref
                        FROM ".$dbname.".pabrik_timbangan
                        WHERE tiketref IS NOT NULL AND tiketref != ''
                    ) ";
                } else {
                    $whrjenis = " AND wbcond='Return' and tiketref!='' ";
                }

                if (in_array($param['komoditi'], $datamentah)) {
                    $str = "select * from " . $dbname . ".pabrik_timbangan_vw
						where 1=1 " . $whr . "
						and kodebarang='" . $param['komoditi'] . "'
						{$whrMillcode}
						and trpcode='" . $param['tipe'] . "'
						and nokontrak='" . $param['nokontrak'] . "'
						and nodo='" . $param['nodo'] . "'
                        {$whrjenis}
						and tanggal between '" . tanggalsystemn($param['tanggalkirim1']) . "' and '" . tanggalsystemn($param['tanggalkirim2']) . "'
						AND (
								(kodesupplier != '' AND kodesupplier IN (SELECT supplierid FROM {$dbname}.log_5supplier WHERE 1=1 {$whrMillcodeUnit}))
								OR
								(kodecustomer IN (SELECT kodecustomer FROM {$dbname}.pmn_4customer WHERE 1=1 {$whrMillcodeUnit}))
							)";
                } else {
                    $str = "select * from " . $dbname . ".pabrik_timbangan_vw
						where 1=1 " . $whr . "
						and kodebarang='" . $param['komoditi'] . "'
						{$whrMillcode}
						and trpcode='" . $param['tipe'] . "'
                        and nokontrak='" . $param['nokontrak'] . "'
						and nodo='" . $param['nodo'] . "'
                        {$whrjenis}
						and tanggal between '" . tanggalsystemn($param['tanggalkirim1']) . "' and '" . tanggalsystemn($param['tanggalkirim2']) . "'";
                }
                // echo $str;
                $res = fetchdata($str);
                foreach ($res as $bar) {

                    # Jika Kirim Normal
                    if ($param['jenisba'] == '0'):
                        # Buat Cek, tampilkan tiket timbang berdasarkan unit
                        // if($_SESSION['empl']['lokasitugas'] == $unitCustomer[$bar['kodecustomer']]) {
                        $arrnotiketkirim[$bar['notiket']] = $bar['notiket'];
                        $dtnokendaraan[$bar['notiket']] = $bar['nokendaraan'];
                        $dtnokontrak[$bar['notiket']] = $bar['nokontrak'];
                        $dtkgkirim[$bar['notiket']] = $bar['kgpembeli'];
                        $dtkgkiriminternal[$bar['notiket']] = $bar['beratbersih'];
                        $dtkgmasuk[$bar['notiket']] = $bar['beratmasuk'];
                        $dtkgkeluar[$bar['notiket']] = $bar['beratkeluar'];
                        $arrnokontrak[$bar['nokontrak']] = $bar['nokontrak'];
                        $dttanggalkirimpks[$bar['notiket']] = $bar['tanggal'];
                        $dtkodebarang[$bar['notiket']] = $bar['kodebarang'];
                        $dttrpcode[$bar['notiket']] = $bar['trpcode'];
                        $dtkodecustomer[$bar['notiket']] = $bar['kodecustomer'];
                        $dtkodesupplier[$bar['notiket']] = $bar['kodesupplier'];
                        $dttiketref[$bar['notiket']] = $bar['tiketref'];
                    // }
                    endif;
                    

                    # Jika Timbang Return
                    if ($param['jenisba'] == '1'):
                        $arrnotiketkirim[$bar['notiket']] = $bar['notiket'];
                        $dtnokendaraan[$bar['notiket']] = $bar['nokendaraan'];
                        $dtnokontrak[$bar['notiket']] = $bar['nokontrak'];
                        $dtkgkirim[$bar['notiket']] = $bar['beratbersih'];
                        $dtkgkiriminternal[$bar['notiket']] = $bar['beratbersih'];
                        $dtkgmasuk[$bar['notiket']] = $bar['beratmasuk'];
                        $dtkgkeluar[$bar['notiket']] = $bar['beratkeluar'];
                        $arrnokontrak[$bar['nokontrak']] = $bar['nokontrak'];
                        $dttanggalkirimpks[$bar['notiket']] = $bar['tanggal'];
                        $dtkodebarang[$bar['notiket']] = $bar['kodebarang'];
                        $dttrpcode[$bar['notiket']] = $bar['trpcode'];
                        $dtkodecustomer[$bar['notiket']] = $bar['kodecustomer'];
                        $dtkodesupplier[$bar['notiket']] = $bar['kodesupplier'];
                        $dttiketref[$bar['notiket']] = $bar['tiketref'];

                        
                    endif;
                }
                
                if (!empty($dttiketref)) {
                    // echo"<pre>";
                    // print_r($dttiketref);
                    // echo"</pre>";
                    $inQuery = "'" . implode("','", $dttiketref) . "'";
                    $sql = "SELECT * FROM ".$dbname.".pabrik_timbangan_vw WHERE notiket IN ($inQuery)";
                } else { 
                    $sql = "SELECT * FROM ".$dbname.".pabrik_timbangan_vw WHERE 1=0";
                }
                
                $req = fetchdata($sql);
                foreach ($req as $key) {
                    $dttiket_awal[$key['notiket']] = $key['notiket'];
                    $dtnokendaraan_awal[$key['notiket']] = $key['nokendaraan'];
                    $dtnokontrak_awal[$key['notiket']] = $key['nokontrak'];
                    $dtkgkirim_awal[$key['notiket']] = $key['kgpembeli'];
                    $dtkgkiriminternal_awal[$key['notiket']] = $key['beratbersih'];
                    $dtkgmasuk_awal[$key['notiket']] = $key['beratmasuk'];
                    $dtkgkeluar_awal[$key['notiket']] = $key['beratkeluar'];
                    $arrnokontrak_awal[$key['nokontrak']] = $key['nokontrak'];
                    $dttanggalkirimpks_awal[$key['notiket']] = $key['tanggal'];
                    $dtkodebarang_awal[$key['notiket']] = $key['kodebarang'];
                    $dttrpcode_awal[$key['notiket']] = $key['trpcode'];
                    $dtkodecustomer_awal[$key['notiket']] = $key['kodecustomer'];
                    $dtkodesupplier_awal[$key['notiket']] = $key['kodesupplier']; 
                }
                
                // if (count($arrnotiketkirim) <= 0) {
                //     exit("Gagal, Tidak ada pengiriman pada periode " . $param['tanggalkirim1'] . " s/d " . $param['tanggalkirim2'] . "");
                // }
                
                #= harga claim ambil dari kontrak
                $rpkgclaim = array();
                $str = "select * from " . $dbname . ".pmn_kontrakjual  where nokontrak='".$param['nokontrak']."'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtrpkgclaim = $bar['hargasatuan'];
                }

                foreach ($arrnotiketkirim as $dtnotiketkirim) {

                    if ($dtkodecustomer[$dtnotiketkirim] != '') {
                        $whr .= "and tujuan = '" . $dtkodecustomer[$dtnotiketkirim] . "'  ";
                    } else if ($dtkodesupplier[$dtnotiketkirim] != '') {
                        $whr .= "and lokasi = '" . $dtkodesupplier[$dtnotiketkirim] . "'";
                    }


                    @$nouruttiket++;
                    if ($nouruttiket % 2 == 0) {
                        $bgcolor = "style=background-color:lightblue;";
                    } else {
                        $bgcolor = "";
                    }
                    @$no++;

               
                    // $rpongkos=gethargatransport($param['unit'],$dtnotiketkirim,$param['tanggalkirim1'],$param['tanggalkirim2'],$param['komoditi']);
                    $kodeTuj = $dtkodecustomer[$dtnotiketkirim] != '' ? " AND tujuan = '$dtkodecustomer[$dtnotiketkirim]' " : " AND lokasi = '$dtkodesupplier[$dtnotiketkirim]' ";
                    $rpongkos = getOngkosAngkut($param['unit'], $kodeTuj, $dttanggalkirimpks[$dtnotiketkirim], $dtkodebarang[$dtnotiketkirim], $dttrpcode[$dtnotiketkirim])[0];
                    // $rpPotongan = getRpPotongan($param['unit'], $kodeTuj, $dttanggalkirimpks[$dtnotiketkirim], $dtkodebarang[$dtnotiketkirim])[0];

                    if (isset($rpongkos['harga'])) {

                        $ongkosrpreal = $rpongkos['harga'] . "";
                        $ongkosrp = $rpongkos['harga'] . "";
                        $idharga = $rpongkos['nourut'];
                    } else {
                        $ongkosrp = 0;
                        $idharga = 0;
                    }

                    if (isset($rpongkos['hargapotongan'])) {
                        $hargapotongan = $rpongkos['hargapotongan'];
                    } else {
                        $hargapotongan = 0;
                    }

                    if (!empty($dttiketref[$dtnotiketkirim])) {
                        $ongkosrp = $ongkosrp * ($hargapotongan / 100);
                    }


                    $tab .= "<tr  " . $bgcolor . " {$txtakun} class=rowcontent id=row" . $no . ">";
                    $tab .= "<td align=center>" . $no . "</td>";
                    $tab .= "<td align=center id=notiket" . $no . " align=left>" . $dtnotiketkirim . "</td>";
                    $tab .= "<td align=center id=kodebarang" . $no . " align=left>" . getNamaBrg($dtkodebarang[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center id=tanggalkirimpks" . $no . " align=left>" . tanggalnormal($dttanggalkirimpks[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center id=nokendaraan" . $no . " align=left>" . $dtnokendaraan[$dtnotiketkirim] . "</td>";
                    $tab .= "<td align=right id=kgmasuk" . $no . " align=right>" . number_format($dtkgmasuk[$dtnotiketkirim], 2) . "</td>";
                    $tab .= "<td align=right id=kgkeluar" . $no . " align=right>" . number_format($dtkgkeluar[$dtnotiketkirim], 2) . "</td>";
                    $tab .= "<td align=right id=kgkiriminternal" . $no . " align=right>" . number_format($dtkgkiriminternal[$dtnotiketkirim], 2) . "  </td>";
                    $tab .= "<td align=right id=kgkirim" . $no . " align=right>" . number_format($dtkgkirim[$dtnotiketkirim], 2) . " </td>";

                    $dtselisih[$dtnotiketkirim] = $dtkgkiriminternal[$dtnotiketkirim] - $dtkgkirim[$dtnotiketkirim];
 
                    if ($dtselisih[$dtnotiketkirim] < 0) {
                        $dtselisih[$dtnotiketkirim] = 0;
                    }
                    $tab .= "<td align=right id=kgselisih" . $no . " align=right>" .number_format($dtselisih[$dtnotiketkirim],2) . " </td>";

                    $tab .= "<td align=right id=rpkgreal" . $no . " align=right> " . number_format($ongkosrpreal, 2) . " </td>";
                    $tab .= "<td hidden align=right id=idharga" . $no . " align=right> " . number_format($idharga) . " </td>";
                    // $tab.="<td align=center id=kodecustomer".$no." align=left>".$dtkodecustomer[$dtnotiketkirim]."</td>";
                    // $tab.="<td align=center id=kodesupplier".$no." align=left>".$dtkodesupplier[$dtnotiketkirim]."</td>";
                    $tab .= "<td hidden align=center id=kodecustomer" . $no . " align=left>" . $dtkodecustomer[$dtnotiketkirim] . "</td>"; # untuk variabel inputan
                    $tab .= "<td hidden align=center id=kodesupplier" . $no . " align=left>" . $dtkodesupplier[$dtnotiketkirim] . "</td>"; # untuk variabel inputan
                    $tab .= "<td align=center  align=left>" . getNamaCustomer($dtkodecustomer[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center  align=left>" . getNamaSupplier($dttrpcode[$dtnotiketkirim]) . "</td>";
                    $dttotalrp[$dtnotiketkirim] = $dtkgkirim[$dtnotiketkirim] * $ongkosrpreal;
                    $tab .= "<td align=right id=rpjumlahbefore" . $no . " align=right> " . number_format($dttotalrp[$dtnotiketkirim], 2) . "</td>";
                    $tab .= "<td align=center id=dttiketref" . $no . " align=left>" . $dttiketref[$dtnotiketkirim] . "</td>";
                    $tab .= "<td align=right id=rpkg" . $no . " align=right> " . number_format($ongkosrp, 2) . " </td>";

                    #Jika jenis BA adalah Timbang Normal, maka ongkos angkut yang digunakan adalah ongkos angkut real
                    if($param['jenisba'] == '0'){
                        $ongkosrp = $ongkosrpreal;
                    }

                    $dttotalrpdibayar[$dtnotiketkirim] = $dtkgkirim[$dtnotiketkirim] * $ongkosrp;
                    $tab .= "<td align=right id=rpjumlah" . $no . " align=right> " . number_format($dttotalrpdibayar[$dtnotiketkirim], 2) . "</td>";


                    #Hidden
                    $tab .= "<td hidden {$bgcolorakun} align=center id=noakundebet" . $no . ">" . $dtnoakundebet . "</td>";
                    $tab .= "<td hidden align=center id=nospk" . $no . " align=left>" . $param['nospk'] . "</td>";
                    $tab .= "<td hidden align=center id=nokontrak" . $no . " align=left>" . $dtnokontrak[$dtnotiketkirim] . "</td>";
                    $tab .= "<td hidden align=center align=left>" . getNamaBrg($dtkodebarang[$param['nospk']]) . "</td>";
                    $tab .= "<td hidden align=center id=transportir" . $no . ">" . $dttransportir[$param['nospk']] . "</td>";
                    $tab .= "<td hidden align=center>" . getNamaSupplier($dttransportir[$param['nospk']]) . "</td>";
                    $tab .= "<td hidden align=center></td>";
                    $tab .= "<td hidden align=center id=kgterimaawal" . $no . ">" . $dtkgterimaawal[$dtnotiketkirim] . "</td>";
                    #= tonbag
                    $tab .= "<td hidden align=center><input type=text id=kgtonbag" . $no . " size=20 disabled class=myinputtextnumber></td>";
                    $tab .= "<td hidden align=center id=kgterima" . $no . "></td>";
                    $tab .= "<td hidden align=center id=kgselisih" . $no . "></td>";
                    // $tab.="<td align=center id=rpkg".$no." align=right>".number_format($dtrpkg[$param['nospk']],2)."</td>";
                    $tab .= "<td hidden align=center id=persentoleransi" . $no . "></td>";
                    $tab .= "<td hidden align=center id=kgtoleransi" . $no . "></td>";
                    $tab .= "<td hidden align=center id=kgclaim" . $no . "></td>";
                    $tab .= "<td hidden align=center><input type=text  id=rpkgclaim" . $no . " disabled class=myinputtext></td>";
                    $tab .= "<td hidden align=center id=rpclaim" . $no . "></td>";

                    $tab .= "</tr>";

                    $ttlkgmasuk += $dtkgmasuk[$dtnotiketkirim];
                    $ttlkgkeluar += $dtkgkeluar[$dtnotiketkirim];
                    $ttlkgkiriminternal += $dtkgkiriminternal[$dtnotiketkirim];
                    $ttlkgkirim += $dtkgkirim[$dtnotiketkirim];
                    $ttglkgselisih += $dtselisih[$dtnotiketkirim];
                    $ttlawal += $dttotalrpdibayar[$dtnotiketkirim];
                }
                $ttlrupiahawal=0;
                if ($param['jenisba'] == '1'){
                    foreach ($dttiket_awal as $tiketawal){

                        $noq++;
                        $tab .= "<tr class=rowcontent style=background-color:lightgreen;>";
                        $tab .= "<td align=center>Tiket awal</td>";
                        $tab .= "<td id=id_tiketawal" . $noq . " align=center >" . $tiketawal . "</td>";
                        $tab .= "<td align=center align=left>" . getNamaBrg($dtkodebarang_awal[$tiketawal]) . "</td>";
                        $tab .= "<td align=center align=left>" . tanggalnormal($dttanggalkirimpks_awal[$tiketawal]) . "</td>";
                        $tab .= "<td id=dtnokendaraan_awal" . $noq . " align=center align=left>" . $dtnokendaraan_awal[$tiketawal] . "</td>";
                        $tab .= "<td id=dtkgmasuk_awal" . $noq . " align=right align=right>" . number_format($dtkgmasuk_awal[$tiketawal], 2) . "</td>";
                        $tab .= "<td id=dtkgkeluar_awal" . $noq . " align=right align=right>" . number_format($dtkgkeluar_awal[$tiketawal], 2) . "</td>";
                        $tab .= "<td id=dtkgkiriminternal_awal" . $noq . " align=right align=right>" . number_format($dtkgkiriminternal_awal[$tiketawal], 2) . "  </td>";
                        $tab .= "<td align=right align=right>" . number_format($dtkgkiriminternal_awal[$tiketawal], 2) . " </td>";

                        $dtselisih_awal[$tiketawal] = $dtkgkiriminternal_awal[$tiketawal] - $dtkgkirim_awal[$tiketawal];
    
                        // if ($dtselisih_awal[$tiketawal] < 0) {
                            $dtselisih_awal[$tiketawal] = 0;
                        // }
                        $tab .= "<td id=dtselisih_awal" . $noq . " align=right align=right>" .number_format($dtselisih_awal[$tiketawal],2) . " </td>";

                        $tab .= "<td id=ongkosrpreal" . $noq . " align=right align=right> " . number_format($ongkosrpreal, 2) . " </td>";
                       
                        $tab .= "<td align=center  align=left>" . getNamaCustomer($dtkodecustomer_awal[$tiketawal]) . "</td>";
                        $tab .= "<td align=center  align=left>" . getNamaSupplier($dttrpcode_awal[$tiketawal]) . "</td>";
                        $dttotalrp_awal[$tiketawal] = $dtkgkiriminternal_awal[$tiketawal] * $ongkosrpreal;
                        $tab .= "<td id=dttotalrp_awal" . $noq . " align=right align=right> " . number_format($dttotalrp_awal[$tiketawal], 2) . "</td>";
                        $tab .= "<td id=dttiketref_awal" . $noq . " align=center align=left>" . $dttiketref_awal[$tiketawal] . "</td>";
                        $tab .= "<td id=ongkosrp_awal" . $noq . " align=right align=right> " . number_format($ongkosrp_awal, 2) . " </td>";

                        $dttotalrpdibayar_awal[$tiketawal] = $dttotalrp_awal[$tiketawal];
                        $tab .= "<td id=dttotalrpdibayar_awal" . $noq . " align=right align=right> " . number_format($dttotalrpdibayar_awal[$tiketawal], 2) . "</td>";
                        $tab.="</tr>";

                        $ttlrupiahawal+=$dttotalrpdibayar_awal[$tiketawal];
                        
                    }
                        
                    $tab .= "<tr class=rowcontent style=background-color:#36a955;>";
                    $tab .= "<td align=center colspan=16></td>";
                    $tab .= "<td align=right>" . number_format($ttlrupiahawal, 2) . "</td>";
                    $tab.="</tr>";
                }





                $maxselisih_kg=0;
                $maxselisih_kg=round($ttlkgkiriminternal*$toleransisusut);
                $klaimkg=round($ttglkgselisih-$maxselisih_kg,2);
                if($klaimkg<0){
                    $klaimkg=0;
                }
                $ttldenda=round($dtrpkgclaim*$klaimkg,2);


                $tab .= "<tr class=rowheader bgcolor=#B0C4DE>";
                $tab .= "<td align=center colspan=5>" . $_SESSION['lang']['total'] . "</td>";
                $tab .= "<td align=right>" . @number_format($ttlkgmasuk,2) . "</td>";
                $tab .= "<td align=right>" . @number_format($ttlkgkeluar,2) . "</td>";
                $tab .= "<td align=right>" . @number_format($ttlkgkiriminternal,2) . "</td>";
                $tab .= "<td align=right>" . @number_format($ttlkgkirim,2) . "</td>";
                $tab .= "<td align=right>" . @number_format($ttglkgselisih,2) . "</td>";
                $ttlawal=$ttlawal+$ttlrupiahawal;
                $tab .= "<td align=right colspan=6></td>";
                $tab .= "<td align=right>
					<input class=myinputtext style='text-align:right;width:100px;' type=text id='ttlawal' value='" . number_format($ttlawal, 2) . "' placeholder=0 onkeypress=\"return isNumberKey(event);\" onkeyup=\"z.numberFormat('ttlawal',2)\" disabled>
					<input type=hidden id='dummyPph' value='{$persenpph}' >";
         
                $tab .= "</tr>";

                $tab .= "<tr class=rowheader bgcolor=#B0C4DE>";
                $tab .= "<td align=center colspan=5>" . $_SESSION['lang']['total'] . " Denda</td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=left style='font-size:10px;font-style:italic;color:blue;'>Toleransi Selisih :  " . $maxselisih_kg . "kg</td>";
                $tab .= "<td align=left colspan=6 style='font-size:10px;font-style:italic;color:blue;'> Denda = " . number_format($klaimkg) . " x " . number_format($dtrpkgclaim) . "</td>";
                $tab .= "<td align=right>
                    
					<input hidden class=myinputtext style='text-align:right;width:100px;' type=text id='persentlrsusut' value='" . $param['persentlrsusut'] . "' placeholder=0 onkeypress=\"return isNumberKey(event);\" onkeyup=\"z.numberFormat('persentlrsusut',2)\" disabled>
                    
					<p  style='font-size:10px;font-style:italic;color:blue;'>Kg  <input onchange=\"adjustdpp($dtrpkgclaim)\" class=myinputtext style='text-align:right;width:80px;' type=text id='kgdenda' value='" . round($klaimkg) . "' placeholder=0  > </p>

					<p  style='font-size:10px;font-style:italic;color:blue;'>Rp<input class=myinputtext style='text-align:right;width:80px;' type=text id='rpdenda' value='" . round($ttldenda) . "' placeholder=0  > </p>
 
                    ";
         
         
                $tab .= "</tr>"; 
                $ttldtrp = $ttlawal - $ttldenda;
                $tab .= "<tr class=rowheader bgcolor=#B0C4DE>";
                $tab .= "<td align=center colspan=5>Total Nilai Bersih (Setelah Denda)</td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right colspan=6></td>";
                $tab .= "<td align=right>
					<input class=myinputtext style='text-align:right;width:100px;' type=text id='ttotalrp' value='" . number_format($ttldtrp) . "' placeholder=0 onkeypress=\"return isNumberKey(event);\" onkeyup=\"z.numberFormat('ttotalrp',2)\" disabled>";
         
                $tab .= "</tr>";

  
                # TOTAL PPH
                $ttlpph = ($ttldtrp / 100) * $persenpph;
                $ttlppn = ($ttldtrp / 100) * $persenppn;
  

                $tab .= "<tr class=rowheader bgcolor=#B0C4DE>";
                $tab .= "<td align=center colspan=8>" . $_SESSION['lang']['pph'] . "</td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right colspan=5></td>";
                $tab .= "<td align=right>
                    <input class=myinputtext style='text-align:right;width:100px;' type=text id='ttlpph' value='" . number_format($ttlpph) . "' placeholder=0 onkeypress=\"return isNumberKey(event);\" onchange=\"htggrandtotal()\" onkeyup=\"z.numberFormat('ttlpph',2)\">
				</td>"; 
                $tab .= "</tr>";

                $tab .= "<tr class=rowheader bgcolor=#B0C4DE>";
                $tab .= "<td align=center colspan=8>" . $_SESSION['lang']['ppn'] . "</td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right colspan=5></td>";
                $tab .= "<td align=right>
					<input class=myinputtext style='text-align:right;width:100px;' type=text id='ttlppn' value='" . number_format($ttlppn, 2) . "' placeholder=0 onkeypress=\"return isNumberKey(event);\" onchange=\"htggrandtotal()\" onkeyup=\"z.numberFormat('ttlppn',2)\">
				</td>";
                $tab .= "</tr>";

                $ttldeducation = 0;
                if ($param['tp'] != '') {
                    $ttldeducation = fixnan($nilaipotongan);
                }

                $ttlinsentive = 0;
                if ($param['tp'] != '') {
                    $ttlinsentive = fixnan($resx[0]['nilaiinsentive']);
                }

                $grdttl = fixnan($ttldtrp + $ttlppn - $ttlpph);
                $tab .= "<tr class=rowheader bgcolor=#B0C4DE style='font-weight:bold'>";
                $tab .= "<td align=center colspan=8>Grand Total</td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right></td>";
                $tab .= "<td align=right colspan=5></td>";
                $tab .= "<td align=right>
					<input class=myinputtext style='text-align:right;width:100px;' type=text id='grdttl' placeholder=0 onkeypress=\"return isNumberKey(event);\" onkeyup=\"z.numberFormat('grdttl',2)\" value='" . number_format($grdttl, 2) . "' disabled>
				</td>";  
                $tab .= "</tr>";

                if ($param['print'] != 'pdf') {
 
                    $tab .= "<tr>";
                    $tab .= "<td align=center colspan=22><button  id=save class=mybutton onclick=savedt(" . $no . ")>" . $_SESSION['lang']['save'] . "</button>";
                    $tab .= "</tr>";
                
                }

                break;
        }

        if ($param['print'] == 'pdf') {
            $tab .= "</tbody></table>";
            $dompdf = new Dompdf();
            $dompdf->loadHtml($tab);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("BA TRANSPORT", array("Attachment" => false));
        } else {
            echo $tab;
        }

        break;

    case 'savedt':
        if ($param['currRow'] == '1') {
            #= delete 1st
            $str = "delete from " . $dbname . "." . $table . " where
				notransaksi='" . $param['notransaksi'] . "'";
            try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                echo " Gagal," . addslashes($e->getMessage());
            }
        }


        $param['kgkirim'] = str_replace(',', '', $param['kgkirim']);
        $param['kgkiriminternal'] = str_replace(',', '', $param['kgkiriminternal']);
        $param['kgtonbag'] = str_replace(',', '', $param['kgtonbag']);
        $param['kgterima'] = str_replace(',', '', $param['kgterima']);
        $param['kgselisih'] = str_replace(',', '', $param['kgselisih']);
        $param['rpkg'] = str_replace(',', '', $param['rpkg']);
        $param['rpjumlah'] = str_replace(',', '', $param['rpjumlah']);
        $param['persentoleransi'] = str_replace(',', '', $param['persentoleransi']);
        $param['kgtoleransi'] = str_replace(',', '', $param['kgtoleransi']);
        $param['kgclaim'] = str_replace(',', '', $param['kgclaim']);
        $param['rpkgclaim'] = str_replace(',', '', $param['rpkgclaim']);
        $param['rpclaim'] = str_replace(',', '', $param['rpclaim']);
        $param['kgterimaawal'] = str_replace(',', '', $param['kgterimaawal']);
        $param['kodecustomer'] = str_replace(',', '', $param['kodecustomer']);
        $param['kodesupplier'] = str_replace(',', '', $param['kodesupplier']);
        $param['idharga'] = str_replace(',', '', $param['idharga']);
        $param['idhargadtkgmasuk_awal'] = str_replace(',', '', $param['idhargadtkgmasuk_awal']);
        $param['dtkgkeluar_awal'] = str_replace(',', '', $param['dtkgkeluar_awal']);
        $param['dtkgkiriminternal_awal'] = str_replace(',', '', $param['dtkgkiriminternal_awal']);
        $param['dtselisih_awal'] = str_replace(',', '', $param['dtselisih_awal']);
        $param['ongkosrpreal'] = str_replace(',', '', $param['ongkosrpreal']);
        $param['dttotalrp_awal'] = str_replace(',', '', $param['dttotalrp_awal']);
        $param['dttiketref_awal'] = str_replace(',', '', $param['dttiketref_awal']);
        $param['dttotalrpdibayar_awal'] = str_replace(',', '', $param['dttotalrpdibayar_awal']);
        $param['dtkgmasuk_awal'] = str_replace(',', '', $param['dtkgmasuk_awal']);
        $param['rpdenda'] = str_replace(',', '', $param['rpdenda']);
        $param['kgdenda'] = str_replace(',', '', $param['kgdenda']);
        $param['ttotalrp'] = str_replace(',', '', $param['ttotalrp']);

        #= cari ROnya
         $kdebarng = makeOption($dbname, 'log_5masterbarang', 'namabarang,kodebarang', "namabarang='" . $param['kodebarang'] . "'");
        
        // exit('error');
         if (!empty($param['id_tiketawal'])) {
            $ids = is_array($param['id_tiketawal']) ? $param['id_tiketawal'] : array($param['id_tiketawal']);
            foreach ($ids as $idx => $idawal) {
                $notiketawal = $idawal;
                $kgmasuk_awal = $param['dtkgmasuk_awal'];
                $kgkeluar_awal = $param['dtkgkeluar_awal'];
                $kgkiriminternal_awal = $param['dtkgkiriminternal_awal'];
                $dtselisih_awal = $param['dtselisih_awal'];
                $ongkosrpreal_awal = $param['ongkosrpreal'];
                $dttotalrp_awal = $param['dttotalrp_awal'];
                $idharga_awal = $param['idhargadtkgmasuk_awal'];
                $dttiketref_awal = $param['dttiketref_awal'];
                
                // exit('error = ' . $kgkiriminternal_awal);

                 if ($dttotalrp_awal > 0) {
                    $ins = "INSERT INTO " . $dbname . "." . $table . "
                        (`notransaksi`,`unit`,`tanggal`,`tanggalkirim1`,`tanggalkirim2`,`keterangan`,`tipe`,
                         `nospk`,`nokontrak`,`notiket`,`kginternal`,`kgkirim`,`kgtonbag`,`kgterimaawal`,
                         `kgterima`,`kgselisih`,`rpkg`,`rpjumlah`,`persentoleransi`,`kgtoleransi`,
                         `kgclaim`,`rpkgclaim`,`rpclaim`,`createby`,`createtime`,`updateby`,`rounit`,
                         `nokendaraan`,`tanggalkirimpks`,`transportir`,`noakundebet`,`kodebarang`,
                         `kodesupplier`,`kodecustomer`,`idharga`,`persenppn`,`persenpph`,`nilaipph`,`nilaippn`,
                         `kodeunit`,`noinvoice`,`tiketref`,`jenis`)
                        VALUES (
                         :notransaksi, :unit, :tanggal, :tanggalkirim1, :tanggalkirim2, :keterangan, :tipe,
                         :nospk, :nokontrak, :notiket, :kginternal, :kgkirim, '', :kgterimaawal,
                         :kgterima, :kgselisih, :rpkg, :rpjumlah, :persentoleransi, :kgtoleransi,
                         0, 0, 0, :createby, :createtime, :updateby, :rounit,
                         '', :tanggalkirimpks, :transportir, :noakundebet, :kodebarang,
                         :kodesupplier, :kodecustomer, :idharga, :persenppn, :persenpph, :nilaipph, :nilaippn,
                         :kodeunit, '', :tiketref, :jenis
                        )";

                    try {
                        $stmt = $owlPDO->prepare($ins);
                        $stmt->execute([
                            ':notransaksi' => $param['notransaksi'],
                            ':unit' => $param['unit'],
                            ':tanggal' => tanggalsystemn($param['tanggal']),
                            ':tanggalkirim1' => tanggalsystemn($param['tanggalkirim1']),
                            ':tanggalkirim2' => tanggalsystemn($param['tanggalkirim2']),
                            ':keterangan' => $param['keterangan'],
                            ':tipe' => $param['tipe'],
                            ':nospk' => $param['nospk'],
                            ':nokontrak' => $param['nokontrak'],
                            ':notiket' => $notiketawal,
                            ':kginternal' => $kgkiriminternal_awal,
                            ':kgkirim' => $kgmasuk_awal,
                            ':kgterimaawal' => $kgmasuk_awal,
                            ':kgterima' => $kgkiriminternal_awal,
                            ':kgselisih' => $dtselisih_awal,
                            ':rpkg' => $ongkosrpreal_awal,
                            ':rpjumlah' => $dttotalrp_awal,
                            ':persentoleransi' => $param['persentlrsusut'],
                            ':kgtoleransi' => 0,
                            ':createby' => $_SESSION['standard']['userid'],
                            ':createtime' => date('Y-m-d H:i:s'),
                            ':updateby' => $_SESSION['standard']['userid'],
                            ':rounit' => (isset($kodero[$kodept[$param['unit']]]) ? $kodero[$kodept[$param['unit']]] : ''),
                            ':tanggalkirimpks' => isset($param['tanggalkirimpks']) ? tanggalsystemn($param['tanggalkirimpks']) : tanggalsystemn($param['tanggalkirim1']),
                            ':transportir' => $param['tipe'],
                            ':noakundebet' => isset($param['noakundebet']) ? $param['noakundebet'] : '',
                            ':kodebarang' => isset($kdebarng[$param['kodebarang']]) ? $kdebarng[$param['kodebarang']] : $param['kodebarang'],
                            ':kodesupplier' => $param['kodesupplier'],
                            ':kodecustomer' => $param['kodecustomer'],
                            ':idharga' => $idharga_awal,
                            ':persenppn' => $param['persenppn'],
                            ':persenpph' => $param['persenpph'],
                            ':nilaipph' => $param['nilaipph'],
                            ':nilaippn' => $param['nilaippn'],
                            ':kodeunit' => $_SESSION['empl']['lokasitugas'],
                            ':tiketref' => $dttiketref_awal,
                            ':noakundebet' => (isset($param['noakundebet']) ? $param['noakundebet'] : ''),
                            ':jenis' => isset($param['jenisba']) ? $param['jenisba'] : '0'
                        ]);
                    } catch (PDOException $e) {
                        continue;
                    }
                }
            }
        }

        if ($param['rpjumlah'] > 0) {
            $str = "insert into " . $dbname . "." . $table . "
            (`notransaksi`, `unit`, `tanggal`, `tanggalkirim1`, `tanggalkirim2`, `keterangan`, `tipe`, `nospk`, `nokontrak`, `notiket`,`kginternal` ,`kgkirim`, `kgtonbag`, `kgterimaawal`, `kgterima`, `kgselisih`, `rpkg`, `rpjumlah`, `persentoleransi`, `kgtoleransi`, `kgclaim`, `rpkgclaim`, `rpclaim`, `createby`, `createtime`, `updateby`,`rounit`,`nokendaraan`,`tanggalkirimpks`,`transportir`,`noakundebet`,`kodebarang`,`kodesupplier`,`kodecustomer`,`idharga`,`persenppn`,`persenpph`,`nilaipph`,`nilaippn`,`kodeunit`,`noinvoice`,`tiketref`,`jenis`, `rpdenda`, `dendakg`, `nettoreal`)
            values
            ('" . $param['notransaksi'] . "','" . $param['unit'] . "','" . tanggalsystemn($param['tanggal']) . "','" . tanggalsystemn($param['tanggalkirim1']) . "','" . tanggalsystemn($param['tanggalkirim2']) . "','" . $param['keterangan'] . "','" . $param['tipe'] . "','" . $param['nospk'] . "','" . $param['nokontrak'] . "','" . $param['notiket'] . "','" . $param['kgkiriminternal'] . "','" . $param['kgkirim'] . "','" . $param['kgtonbag'] . "','" . $param['kgterimaawal'] . "','" . $param['kgterima'] . "','" . $param['kgselisih'] . "','" . $param['rpkg'] . "','" . $param['rpjumlah'] . "','" . $param['persentlrsusut'] . "','" . $param['kgtoleransi'] . "','" . $param['kgclaim'] . "','" . $param['rpkgclaim'] . "','" . $param['rpclaim'] . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "','" . $kodero[$kodept[$param['unit']]] . "','" . $param['nokendaraan'] . "','" . tanggalsystemn($param['tanggalkirimpks']) . "','" . $param['tipe'] . "','" . $param['noakundebet'] . "','" . $kdebarng[$param['kodebarang']] . "','" . $param['kodesupplier'] . "','" . $param['kodecustomer'] . "','" . $param['idharga'] . "','" . $param['persenppn'] . "','" . $param['persenpph'] . "','" . $param['nilaipph'] . "','" . $param['nilaippn'] . "','{$_SESSION['empl']['lokasitugas']}','".$param['noinvoice']."','".$param['dttiketref']."','".$param['jenisba']."','".$param['rpdenda']."','".$param['kgdenda']."','".$param['ttotalrp']."')";
             try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        } else {
            continue;
         }

        break;

    case 'deleteht':
        $str = "delete from  " . $dbname . "." . $table . " where notransaksi='" . $param['notransaksi'] . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'loaddata':

        // $where=" notransaksi LIKE '%".$_SESSION['empl']['lokasitugas']."%' OR createby='{$_SESSION['standard']['userid']}'";
        // $where = "kodeunit IN (".orgDetailuser($_SESSION['standard']['username'], '2').")";
        $where = "1=1";
        // $where = "createby='{$_SESSION['standard']['userid']}'";

        if ($param['tanggalmulai'] != '' and $param['tanggalselesai'] != '') {
            $where .= " and tanggal between '" . tanggalsystemn($param['tanggalmulai']) . "' and '" . tanggalsystemn($param['tanggalselesai']) . "'";
        }

        if ($param['notransaksi'] != '') {
            $where .= " and notransaksi like '%" . $param['notransaksi'] . "%'";
        }

        if ($param['transportirsch'] != '') {
            $where .= " and transportir = '" . $param['transportirsch'] . "'";
        }

        if ($param['komoditisch'] != '') {
            $where .= " and kodebarang = '" . $param['komoditisch'] . "'";
        }
        // echo $where;

        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0) {
                $page = 0;
            }
        }
        $maxdisplay = ($page * $limit);
        $colspan = 18;

        $offset = $page * $limit;

        $str = "select count(DISTINCT(notransaksi)) as jumrow from " . $dbname . "." . $table . " where " . $where . "";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $jumrow = $bar['jumrow'];
        }

        $no = 0;
        $no = $maxdisplay;
        $statusapp = '';
        $str = "select noinvoice,rpdenda,sum(kgkirim) as kgkirim,sum(kgterima) as kgterima,sum(kgselisih) as kgselisih,sum(rpjumlah) as rpjumlah,sum(kgclaim) as kgclaim,sum(rpclaim) as rpclaim,createby,updateby,notransaksi,tanggal,unit,keterangan,tipe,posting,nokontrak,nospk,transportir,jenis from " . $dbname . "." . $table . " where " . $where . " group by notransaksi order by tanggal desc limit " . $offset . "," . $limit . " ";
        // exit($str);
        $res = fetchdata($str);
        foreach ($res as $bar) {

            #=datakaryawan
            $strdt = "select namakaryawan,karyawanid from " . $dbname . ".datakaryawan where karyawanid in ('" . $bar['createby'] . "','" . $bar['updateby'] . "') ";
            $resdt = fetchdata($strdt);
            foreach ($resdt as $bardt) {
                $namakaryawan[$bardt['karyawanid']] = $bardt['namakaryawan'];
            }

            #=supplier/transportir
            $strdt = "select * from " . $dbname . ".log_5supplier where supplierid='" . $bar['transportir'] . "' ";
            $resdt = fetchdata($strdt);
            foreach ($resdt as $bardt) {
                $namatransportir[$bardt['transportir']] = $bardt['namasupplier'];
            }
            $arrtipe   = array('1' => 'Return', '0' => 'Normal');
            $no++;
            $nettoreal=0;
            $nettoreal=$bar['rpjumlah']-$bar['rpdenda'];
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center valign=top>" . $no . "</td>";
            $tab .= "<td valign=top nowrap>" . $bar['notransaksi'] . "</td>";
            $tab .= "<td hidden align=center valign=top>" . $bar['tipe'] . "</td>";
            $tab .= "<td align=left valign=top nowrap>" . $namatransportir[$bar['transportir']] . "</td>";
            $tab .= "<td valign=top>" . $bar['noinvoice'] . "</td>";
            $tab .= "<td valign=top nowrap>" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab .= "<td valign=top>" . $bar['unit'] . "</td>";
            $tab .= "<td align=right valign=top>" . number_format($bar['kgkirim']) . "</td>"; 
            $tab .= "<td align=right valign=top><b>" . number_format($nettoreal) . "</b></td>";
            $tab .= "<td align='center' valign=top>" . nl2br($bar['keterangan']) . " <br> <i style=\"color:green;font-size:smaller;\">(" . $arrtipe[$bar['jenis']] . ")</i></td>";
            $tab .= "<td valign=top>" . $namakaryawan[$bar['createby']] . "</td>";
            $tab .= "<td style='text-align:center;vertical-align:middle'><label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('" . $bar['notransaksi'] . "',event)\">History Approval</label></td>";

            if ($bar['posting'] == 0 || $bar['posting'] == 3) {
                $tab .= "<td>
						<img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('" . $bar['notransaksi'] . "');\">
					</td>";
                $tab .= "<td>
						<img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('" . $bar['notransaksi'] . "');\">
					</td>";
             
                $tab .= "<td><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30' title='Posting' onclick=\"posting('" . $bar['notransaksi'] . "','" . $bar['tanggal'] . "','" . $bar['unit'] . "','" . $page . "');\"></td>";

                #komen sementara
                // $tab .= "<td>
				// 		<img src='images/skyblue/submit.jpg' class='zImgBtn' title='Ajukan' onclick='form_ajukan(`" . $bar['notransaksi'] . "`)'>
				// 	</td>";
            } else if ($bar['posting'] == 9) {
                $tab .= "<td></td>";
                $tab .= "<td></td>";
                $tab .= "<td><img src='images/icons/04/16/04.png' class='zImgBtn' height='30' title='On Progress Approval'></td>";
            } else if ($bar['posting'] == 2) {
                $tab .= "<td></td>";
                $tab .= "<td></td>";
                $tab .= "<td><img src='images/icons/04/16/01.png' class='zImgBtn' height='30' title='Approval Rejected'></td>";
            } else if ($bar['posting'] == 8) {
                $tab .= "<td></td>";
                $tab .= "<td></td>";
                $tab .= "<td><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30' title='Posting' onclick=\"posting('" . $bar['notransaksi'] . "','" . $bar['tanggal'] . "','" . $bar['unit'] . "','" . $page . "');\"></td>";
                #komen sementara
                // $tab .= "<td><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30' title='Posting' onclick=\"posting('" . $bar['notransaksi'] . "','" . $bar['tanggal'] . "','" . $bar['unit'] . "','" . $page . "');\"></td>";
            } else {
                $tab .= "<td></td>";
                $tab .= "<td></td>";
                $tab .= "<td><img src='images/icons/04/16/02.png' class='zImgBtn' height='30' title='Approved'></td>";
            }

            $tab .= "<td>
					<img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF " . $bar['notransaksi'] . "' onclick=\"pdf('" . $bar['notransaksi'] . "');\">
				</td>";

            $tab .= "<td>
					<img src=images/excel.jpg class=resicon  caption='Excel'  title='Print Excel " . $bar['notransaksi'] . "' onclick=\"excel('" . $bar['notransaksi'] . "');\">
				</td>";
            $tab .= "<td ><img src=images/pdf.jpg class=zImgBtn  caption='PDF Invoice'  title='Print PDF Invoice " . $bar['notransaksi'] . "' onclick=\"pdfinvoice('" . $bar['notransaksi'] . "');\"></td>";
            $tab .= "<td ><img src=images/pdf.jpg class=zImgBtn  caption='PDF BA'  title='Print PDF BA " . $bar['notransaksi'] . "' onclick=\"pdfba('" . $bar['notransaksi'] . "');\"></td>";

            if ($bar['kgclaim'] != 0) {
                $tab .= "<td hidden><img src=images/pdf.jpg class=zImgBtn  caption='PDF Claim'  title='Print PDF Claim " . $bar['notransaksi'] . "' onclick=\"pdfclaim('" . $bar['notransaksi'] . "');\"></td>";
            } else {
                $tab .= "<td hidden></td>";
            }

            $tab .= "</tr>";
        }

        ## PAGING
        $footd .= createpaging($jumrow, $limit, $page, $colspan, 'loaddata', 'getpage');

        echo $tab . "####" . $footd;
        break;

    case 'form_ajukan':
        // exit()
        //$countApp = getCountApproval($tipe, $unit, getKary($nmdept[$notransaksi],'bagian'));
        // $tab.="Persetujuan";
        $tab .= "<table cellpadding=2 cellspacing=1 border=0>";
        // $tab.="<table cellpadding=2 cellspacing=1 border=0>
        // <thead>
        // <tr style='font-weight:bold'>
        // <td align='center' colspan=2>".$_SESSION['lang']['keterangan']."</td>
        // <td align='center'>".$_SESSION['lang']['action']."</td>
        // </tr>
        // </thead>";
        $tab .= "<tbody id='listfile'>";
        // echo"<pre>";
        // print_r($param);
        // exit;
        $tipe = 'BATRANSPORT';
        // $countApp = getHitungApproval($kasbank,$param['kodeorg'],'','','',$resH[0]['jumlah']);
        // $countApp = getCountApproval($tipe,$unit);
        $countApp = getCountApproval($tipe, $_SESSION['empl']['lokasitugas']);

        for ($i = 1; $i <= $countApp; $i++) {
            //$arrList = listApprove($i, $tipe, $unit, getKary($nmdept[$notransaksi],'bagian'));
            // $arrList = listApprove($i,$tipe,$unit);
            $arrList = listApprove($i, $tipe, $_SESSION['empl']['lokasitugas']);
            // echo"<pre>";
            // print_r($arrList[0]['karyawanid']);
            // $optpersetujuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

            $arrDetail = detailApprove($i, $param['notransaksi'], $param['tipe']);
            $optpersetujuan = "";
            foreach ($arrList as $key => $val) {
                $optpersetujuan .= "<option value='" . $val['karyawanid'] . "'>" . $val['nama'] . " </option>";
            }
            $tab .= "<tr  class=rowcontent>
				<td>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</td>
				<td>:</td>
				<td colspan=1><select style=\"width:204px;\" id=persetujuan" . $i . ">" . $optpersetujuan . "</select></td>
				</tr>";
        }

        if ($countApp < 1) {
            exit("Warning : Setup approval BA TRANSPORT (BATRANSPORT) untuk unit " . strtoupper($_SESSION['empl']['lokasitugas']) . " belum disetting.");
        }

        $tab .= "
			<tr  class=rowcontent>
				<td>" . $_SESSION['lang']['tanggal'] . "</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext disabled value='" . date('d-m-Y') . "'  id=tanggalpengajuan onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:200px;\">
				</td>
			</tr>
			<tr class=rowcontent>
				<td colspan=2></td>
				<td style='text-align:left'>
					<button class=mybutton onclick=saveajukan('" . $param['notransaksi'] . "','" . $tipe . "','" . $countApp . "','" . $arrList[0]['karyawanid'] . "')>Simpan</button>
				</td>
			</tr>
		</table>
		";

        echo $tab;
        break;

    case 'saveajukan':

        // echo"<pre>";
        // print_r($param);
        // echo"</pre>";exit("Error:A");
        try {
            $owlPDO->beginTransaction();

            if ($param['tanggalpengajuan'] == '') {
                exit("Warning:Tanggal pengajuan masih kosong");
            }

            for ($i = 1; $i <= $param['maxaproval']; $i++) {
                if ($param['persetujuan'][$i] == '') {
                    exit("Warning: Persetujuan " . $i . " belum dipilih.");
                }
            }
            // echo"<pre>";
            // print_r($param['kasbank']);
            // echo"</pre>";
            #= delete 1st untuk aprovalnya
            $strd = "delete from " . $dbname . ".approval where notransaksi='" . $param['notransaksi'] . "' and jenispersetujuan = '" . $param['tipe'] . "'";
            // exit('error'.$strd);
            $owlPDO->exec($strd);

            $stru = "update " . $dbname . ".pmn_batransport set posting=9, postingby='" . $param['karyawanid'] . "' where notransaksi='" . $param['notransaksi'] . "'";
            $owlPDO->exec($stru);
            // exit('error '.$stru);

            // echo $param['persetujuan'][1];
            // exit("error");
            // exit("Error:MASUKKK");
            for ($i = 1; $i <= $param['maxaproval']; $i++) {
                #= insert
                $strq = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, username, status, komentar, keterangan, tanggal)
				values('" . $param['notransaksi'] . "','" . $param['tipe'] . "','" . $i . "','" . $param['karyawanid'] . "','" . $param['persetujuan'][$i] . "','0','','','0000-00-00 00:00:00')";

                $owlPDO->exec($strq);
            }
            // exit("Error:".$str);

            $owlPDO->commit();
        } catch (PDOException $e) {

            $owlPDO->rollback();
            echo "Warning: Gagal melakukan pengajuan \n" . addslashes($e->getMessage());
        }
        break;

    // case 'form_ajukan':
    //     $query  = "SELECT unit FROM $dbname.pmn_batransport WHERE notransaksi = '".$param['notransaksi']."'";
    //     $result = fetchData($query, 'OBJECT');
    //     $unit   = $result[0]->unit;

    //     $opt    = array();
    //     $query  = "SELECT * FROM $dbname.setup_approval WHERE jenispersetujuan = 'BATRANSPORT' AND kodeunit = '$unit' ORDER BY level";
    //     $result = fetchData($query, 'OBJECT');
    //     foreach ($result as $key => $value) {
    //         $opt['approver'][$value->level][$value->karyawanid] = "<option value='".$value->karyawanid."'>".$utilities['worker']['Name'][$value->karyawanid]."</option>";
    //         $opt['level'][$value->level] = $value->level;
    //     }

    //     $jumlahlevel = count($opt["level"]);
    //     $stream .= "<input type='hidden' id='notransaksi_ajukan' value='".$param['notransaksi']."'/>";
    //     $stream .= "<input type='hidden' id='jlh' value='".$jumlahlevel."'/>";
    //     $optShow = "";
    //     foreach ($opt['approver'][1] as $key => $value) {
    //         $optShow .= $value;
    //     }

    //     $stream .= "<tr class='rowcontent'>";
    //         $stream .= "<td> Approval ke - 1</td>";
    //         $stream .= "<td style='width:5px'> : </td>";
    //         $stream .= "<td>";
    //             $stream .= "<select id='kepada1' style='width:99%'>".$optShow."</select>";
    //         $strean .= "</td>";
    //     $stream .= "</tr>";

    //     $stream .= "<tr class='rowcontent'>";
    //         $stream .= "<td></td>";
    //         $stream .= "<td></td>";
    //         $stream .= "<td>";
    //             $stream .= "<button id='tomboldetail' class='mybutton' onclick='ajukan()'>" . $_SESSION['lang']['diajukan'] . "</button>";
    //         $strean .= "</td>";
    //     $stream .= "</tr>";

    //     echo $stream;
    // break;

    // case 'ajukan':
    //     for ($i = 1; $i <= $param['jlh'] ; $i++) {
    //         $per['persetujuan'.$i] = checkPostGet("kepada".$i, '');
    //         if($per['persetujuan'.$i] == '' or $param['notransaksi'] == ''){
    //             exit('Warning : Isikan nama penyetuju.');
    //         }
    //     }

    //     $query = "UPDATE $dbname.pmn_batransport SET posting = '9' WHERE notransaksi = '".$param['notransaksi']."'";

    //     try {
    //         $owlPDO->exec($query);

    //         $query  = "SELECT unit FROM $dbname.pmn_batransport WHERE notransaksi = '".$param['notransaksi']."'";
    //         $result = fetchData($query, 'OBJECT');
    //         $unit   = $result[0]->unit;

    //         $jenispersetujuan = 'BATRANSPORT';
    //         for($i = 1; $i <= $param['jlh']; $i++){
    //             $query  = "SELECT * FROM $dbname.setup_approval WHERE jenispersetujuan = '$jenispersetujuan' AND level = '$i' AND kodeunit = '$unit'";
    //             $result = fetchData($query, 'OBJECT');
    //             $tipeapp            = $result[0]->tipe;
    //             $departemenapp      = $result[0]->departemen;
    //             $tipekaryawanapp    = $result[0]->tipekaryawan;
    //             $jabatanapp         = $result[0]->jabatan;

    //             if ($tipeapp == 1) {
    //                 if ($departemenapp != "") {
    //                     $query = "SELECT * FROM $dbname.datakaryawan WHERE bagian = '".$departemenapp."'";
    //                 }

    //                 if ($tipekaryawanapp != "") {
    //                     $query = "SELECT * FROM $dbname.datakaryawan WHERE tipekaryawan = '".$tipekaryawanapp."'";
    //                 }

    //                 if ($jabatanapp != "") {
    //                     $query = "SELECT * FROM $dbname.datakaryawan WHERE kodejabatan = '".$jabatanapp."'";
    //                 }

    //                 $result = fetchData($query, 'OBJECT');
    //                 foreach($result as $key => $value){
    //                     $query = "INSERT INTO $dbname.approval (notransaksi,jenispersetujuan,level,karyawanid,status) VALUES ('".$param['notransaksi']."', '".$jenispersetujuan."', '".$i."', '".$valx['karyawanid']."', '0')";

    //                     $owlPDO->exec($query);
    //                 }

    //                 break;
    //             } else {
    //                 if($per['persetujuan'.$i] != ''){
    //                     $query  = "INSERT INTO $dbname.approval (notransaksi,jenispersetujuan,level,karyawanid,status) VALUES ('".$param['notransaksi']."', '".$jenispersetujuan."', '".$i."', '".$per['persetujuan'.$i]."', '0')";
    //                 }
    //             }

    //             try {
    //                 $owlPDO->exec($query);
    //             } catch (PDOException $e) {
    //                 print " Gagal  !: " . $e->getMessage() . "\n";
    //                 die();
    //             }
    //         }
    //     } catch (PDOException $e) {
    //         print " Gagal  !: " . $e->getMessage() . "\n";
    //         die();
    //     }
    // break;

    case 'geteditht':
        $str = "select * from " . $dbname . "." . $table . "  where notransaksi='" . $param['notransaksi'] . "'";
        $res = fetchdata($str);
        $res[0]['tanggal'] = tanggalnormal($res[0]['tanggal']);
        $res[0]['tanggalkirim1'] = tanggalnormal($res[0]['tanggalkirim1']);
        $res[0]['tanggalkirim2'] = tanggalnormal($res[0]['tanggalkirim2']);
        $res[0]['komoditi'] = $res[0]['kodebarang'];
        $res[0]['nokontrak'] = $res[0]['nokontrak'];
        $res[0]['persenpph'] = $res[0]['persenpph'];
        $res[0]['persenppn'] = $res[0]['persenppn'];
        $res[0]['noinvoice'] = $res[0]['noinvoice'];
        $res[0]['persentoleransi'] = $res[0]['persentoleransi'];

        $arrnodo = makeOption($dbname, "pmn_suratperintahpengiriman", "nokontrak,nodo");
        $res[0]['nodo'] = $arrnodo[$res[0]['nokontrak']];
        // $str = "select supplierid from " . $dbname . ".log_5supnpwp where supplierid='" . $res[0]['tipe'] . "'";
        // $respph = fetchdata($str);

        // if (count($respph) > 0) {
        //     #= nilai default jika ada NPWP
        //     $res[0]['persenpph'] = 2;
        // } else {
        //     #= nilai default jika tidak ada NPWP
        //     $res[0]['persenpph'] = 4;
        // }

        echo json_encode($res[0]);
        break;

    // Umar
    case 'export':
        $css = "<style>
			@page {
				margin-top: 30px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}

			body {
				font-family: Serif, Times-New-Roman;
			}

			footer {
				position: fixed;
				bottom: -10px;
				left: 0px;
				right: 0px;
				height: 50px;
			}
		</style>";

        $str = "select * from " . $dbname . ".pmn_batransport where notransaksi='" . $param['notransaksi'] . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $notransaksi = $bar['notransaksi'];
            $rounit = $bar['rounit'];
            $noakundebet = $bar['noakundebet'];
            $pabrik = $bar['unit'];
            $tanggal = $bar['tanggal'];
            $transportir = $bar['transportir'];
            $kodebarang = $bar['kodebarang'];
            $nospk = $bar['nospk'];
            $kgtoleransi = abs($bar['kgtoleransi']);
            $persentoleransi = abs($bar['persentoleransi']);
            $nokontrak = $bar['nokontrak'];
            $tipetransaksi = $bar['tipe'];
            $kodesupplierht = $bar['kodesupplier'];
            $kodecustomerht = $bar['kodecustomer'];

            if ($bar['kgclaim'] != 0) {
                $noclaim++;

                $kgselisih += abs($bar['kgselisih']);
                $kgclaim += abs($bar['kgclaim']);
                $rpkgclaim = abs($bar['rpkgclaim']);
                $rpclaim += abs($bar['rpclaim']);
            }
        }

        #=supplier/transportir
        $strdt = "select * from " . $dbname . ".log_5supplier where supplierid='" . $transportir . "' ";
        $resdt = fetchdata($strdt);
        foreach ($resdt as $bardt) {
            $namatransportir[$bardt['transportir']] = $bardt['namasupplier'];
        }

        $strdt = "select * from " . $dbname . ".log_5supplier";
        $resdt = fetchdata($strdt);
        foreach ($resdt as $bardt) {
            $namasupcus[$bardt['supplierid']] = $bardt['namasupplier'];
        }

        $strdt = "select * from " . $dbname . ".pmn_4customer";
        $resdt = fetchdata($strdt);
        foreach ($resdt as $bardt) {
            $namacus[$bardt['kodecustomer']] = $bardt['namacustomer'];
        }

        $str1 = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $pabrik . "'";
        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            @$alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
            @$telp = $bar1->telepon;
        }

        $arrkodept = setheadreport($rounit, $kodept[$rounit]);
        $cellpadding = 1;
        $cellspacing = 1;
        $sizefont = '14';

        $header = "<div style='page-break-after: always;'>";
        $header .= "<div style='position:absolute;right:0;bottom:0;text-align:right'>" . tglnmbln(date('Y-m-d'), 'i', 'l') . " " . date('H:i:s') . "<br><font style='font-size:7px'>Generated By OWL System</font></div>";

        $header .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>"; //logoheight logowidth
        $header .= "<tr>";
        $header .= "<td style='width:20%;' align=center><img src=" . $arrkodept['logo'] . " style='width:150px;height:150px'></td>";
        $header .= "<td style='width:80%;vertical-align:top;font-size:" . ($sizefont + 24) . "px;line-height: 80%'>";
        $header .= "<br><font>" . $arrkodept['nama'] . "</font><br>";
        $header .= "<i><font style='font-size:20px;font-weight:italic'>" . $alamatpt . "</font></i><br>";
        $header .= "<i><font style='font-size:20px;font-weight:italic'>" . $telp . "</font></i>";
        $header .= "</td>";
        $header .= "</tr>";
        $header .= "</table>";

        $header .= "<br>";
        $header .= "<hr>";

        $header .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;line-height:80%'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:center;vertical-align:center;font-weight:bold;font-size:24px'>BA TRANSPORT</td>";
        $tab .= "</tr>";
        // $tab.="<tr>";
        //         $tab.="<td style='text-align:center;vertical-align:bottom;font-size:12px'><b><i>".$param['notransaksi']."</i></b></td>";
        // $tab.="</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width='100%' class='sortable' style='font-size:" . $sizefont . "px;padding-left:50px'>";
        $tab .= "<tr>";
        $tab .= "<td style='width:20%;text-align:left;vertical-align:center;'>NO BA</td>";
        $tab .= "<td style='width:2%'>:</td>";
        $tab .= "<td style='text-align:left;vertical-align:center;'>" . $param['notransaksi'] . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='width:20%;text-align:left;vertical-align:center;'>TRANSPORTIR</td>";
        $tab .= "<td style='width:2%'>:</td>";
        $tab .= "<td style='text-align:left;vertical-align:center;'>" . $namatransportir[$transportir] . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='width:20%;text-align:left;vertical-align:center;'>TANGGAL</td>";
        $tab .= "<td style='width:2%'>:</td>";
        $tab .= "<td style='text-align:left;vertical-align:center;'>" . tglnmbln($tanggal, 'I', 'long') . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='width:20%;text-align:left;vertical-align:center;'>UNIT PABRIK</td>";
        $tab .= "<td style='width:2%'>:</td>";
        $tab .= "<td style='text-align:left;vertical-align:center;'>" . $namaorganisasi[$pabrik] . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $data = array();
        $str = "select sum(kgkirim) as kgkirim,sum(kgterima) as kgterima,sum(kgselisih) as kgselisih,sum(rpjumlah) as rpjumlah,sum(kgclaim) as kgclaim,sum(rpclaim) as rpclaim,createby,updateby,notransaksi,tanggal,unit,keterangan,tipe,posting,nokontrak,nospk,transportir from " . $dbname . "." . $table . " where notransaksi = '" . $param['notransaksi'] . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $data = $bar;
        }

        $tab .= "<table width='100%' class='sortable' cellspacing=0 style='font-size:" . $sizefont . "px;' border=1>";
        $tab .= "<tr class='rowheader'>";
        $tab .= "<th style='text-align:center;vertical-align:center;'>Berat Bersih Kirim</th>";
        $tab .= "<th style='text-align:center;vertical-align:center;'>Berat Bersih Diterima</th>";
        $tab .= "<th style='text-align:center;vertical-align:center;'>Selisih</th>";
        $tab .= "<th style='text-align:center;vertical-align:center;'>Jumlah (Rp)</th>";
        $tab .= "</tr>";
        $tab .= "<tr class='rowcontent'>";
        $tab .= "<td style='text-align:center;vertical-align:center;padding:5px'>" . hidezerodecimal($data['kgkirim'], 2) . "</td>";
        $tab .= "<td style='text-align:center;vertical-align:center;padding:5px'>" . hidezerodecimal($data['kgkirim'], 2) . "</td>";
        $tab .= "<td style='text-align:center;vertical-align:center;padding:5px'>" . hidezerodecimal(0, 2) . "</td>";
        $tab .= "<td style='text-align:center;vertical-align:center;padding:5px'>" . hidezerodecimal($data['rpjumlah'], 2) . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr class='rowheader'>";
        $tab .= "<th style='text-align:center;vertical-align:center;' colspan='4'>Keterangan</th>";
        $tab .= "</tr>";
        $tab .= "<tr class='rowcontent'>";
        $tab .= "<td style='text-align:center;vertical-align:center;padding:10px' colspan='4'>" . $data['keterangan'] . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<div style='page-break-after: always;'></div>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;line-height:80%'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:center;vertical-align:center;font-weight:bold;font-size:24px'>LIST DATA</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        if ($param['print'] == 'pdf') {
            $tab .= "<hr>";
        }

        $tab .= "<br>";

        $tab .= "<table cellpading=1 cellspacing=0 border=1 class=sortable width=100% style='font-size:10px'>
  						<thead>
   							<tr class=rowheader>
   								<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
								   <th align=center>" . $_SESSION['lang']['noTiket'] . " " . $_SESSION['lang']['kirim'] . "</th>
   								<th align=center>" . $_SESSION['lang']['supir'] . "</th>
								<th align=center>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['kirim'] . "</th>
								<th align=center>" . $_SESSION['lang']['nopol'] . "</th>";

        if ($kodesupplierht != '') {
            $tab .= "<th align=center>" . $_SESSION['lang']['divisi'] . "</th>";
        } else {
            $tab .= "<th align=center>Customer</th>";
        }

        $tab .= "
								<th align=center>" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['kirim'] . "</th>
								<!--<th align=center>" . $_SESSION['lang']['noTiket'] . " " . $_SESSION['lang']['tujuan'] . "</th>-->
								<!--<th align=center>" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['diterima'] . "</th>-->
								<!--<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['diterima'] . "</th>-->
								<!--<th align=center>" . $_SESSION['lang']['selisih'] . "<br>(" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['diterima'] . "-" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['kirim'] . ")</th>-->
								<th align=center>" . $_SESSION['lang']['rpperkg'] . "</th>
								<th align=center>" . $_SESSION['lang']['jumlahrp'] . "</th>
								<!--<th align=center>" . $_SESSION['lang']['toleransi'] . " (%)</th>-->
								<!--<th align=center>" . $_SESSION['lang']['toleransi'] . " (Kg)</th>-->
								<!--<th align=center>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['klaim'] . "<br>(" . $_SESSION['lang']['selisih'] . "-" . $_SESSION['lang']['kg'] . "<br>" . $_SESSION['lang']['klaim'] . ")</th>-->
								<!--<th align=center>" . $_SESSION['lang']['rpperkg'] . " " . $_SESSION['lang']['klaim'] . "</th>-->
								<!--<th align=center>" . $_SESSION['lang']['jumlahrp'] . " " . $_SESSION['lang']['klaim'] . "</th>-->
								<th align=center>" . $_SESSION['lang']['noakun'] . " " . $_SESSION['lang']['debet'] . "</th>
   							</tr>
 						</thead>
   					<tbody id=listdatadt>";

        switch ($param['tipe']) {
            case 'sip':
                // exit("Error:A");
                $str = "select * from " . $dbname . ".pabrik_bamutasi  where nosip='" . $param['nospk'] . "' and unit='" . $param['unit'] . "' and tanggal between '" . tanggalsystemn($param['tanggalkirim1']) . "' and '" . tanggalsystemn($param['tanggalkirim2']) . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $arrnotiketkirim[$bar['notransaksi']] = $bar['notransaksi'];
                    $dttanggalkirimpks[$bar['notransaksi']] = $bar['tanggal'];
                    @$dtkgkirim[$bar['notransaksi']] += $bar['jumlah'];
                }

                if (@count($arrnotiketkirim) < 1) {
                    exit("Warning:Nomor SIP untuk " . $param['nospk'] . " belum dibuatkan BA Pengirmannya");
                }

                #= ambil data penerimaannya berasarkan nomor sip dan noreferensi= nomor ba pengirman
                $str = "select * from " . $dbname . ".pabrik_bamutasi  where nosip='" . $param['nospk'] . "' and   noreferensi in ('" . implode("','", $arrnotiketkirim) . "')";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtnotiketterima[$bar['noreferensi']] = $bar['notransaksi'];
                    @$dtkgterima[$bar['noreferensi']] += $bar['jumlah'];
                }

                #= ambil data BA untuk rpkg, toleransi, transportir
                $str = "select * from " . $dbname . ".pmn_suratperintahpengiriman  where nodo='" . $param['nospk'] . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtrpkg[$bar['nodo']] = $bar['harga'];
                    $dttransportir[$bar['nodo']] = $bar['transportir'];
                    $dtpersentoleransi[$bar['nodo']] = $bar['toleransi'];
                    $dtkgtoleransi[$bar['nodo']] = $bar['kgtoleransi'];
                    $arrnokontrak[$bar['nokontrak']] = $bar['nokontrak'];
                    $dtnokontrak[$bar['nodo']] = $bar['nokontrak'];
                    $dtnoakundebet[$bar['nodo']] = $bar['noakundebet'];
                    $dtkodebarang[$bar['nodo']] = $bar['kodebarang'];
                }

                #= data lama untuk ambil rp/kg claim
                $str = "select count(*) as jumrow from " . $dbname . ".pmn_batransport  where notransaksi='" . $param['notransaksi'] . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    @$datatransaksi = $bar['jumrow'];
                    @$datatransaksi = $bar['jumrow'];
                }

                $dtrpkgclaim = array();
                if ($datatransaksi == 0) {
                    #= harga claim ambil dari kontrak
                    if (@count($arrnokontrak) > 0) {
                        $str = "select * from " . $dbname . ".pmn_kontrakjual  where nokontrak in ('" . implode("','", $arrnokontrak) . "')";
                        // exit("Error:$str");
                        $res = fetchdata($str);
                        foreach ($res as $bar) {
                            $dtrpkgclaim[$bar['nokontrak']] = $bar['hargasatuan'];
                        }
                    }
                } else {
                    $str = "select * from " . $dbname . ".pmn_batransport  where notransaksi='" . $param['notransaksi'] . "'";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while ($bar = $res->fetch()) {
                        $dtrpkgclaim[$bar['notiket']] = $bar['rpkgclaim'];
                    }
                }

                foreach ($arrnotiketkirim as $dtnotiketkirim) {
                    @$nouruttiket++;
                    if ($nouruttiket % 2 == 0) {
                        // $bgcolor="style=background-color:lightblue;";
                        $bgcolor = "";
                    } else {
                        $bgcolor = "";
                    }
                    @$no++;
                    $tab .= "<tr  " . $bgcolor . " class=rowcontent id=row" . $no . ">";
                    $tab .= "<td align=center>" . $no . "</td>";
                    // $tab.="<td align=center id=nospk".$no." align=left>".$param['nospk']."</td>";
                    // $tab.="<td align=center id=kodebarang".$no." align=left>".$dtkodebarang[$param['nospk']]."</td>";
                    // $tab.="<td align=center id=nokontrak".$no." align=left>".$dtnokontrak[$param['nospk']]."</td>";
                    // $tab.="<td align=center id=transportir".$no.">".$dttransportir[$param['nospk']]."</td>";
                    $tab .= "<td align=center id=notiket" . $no . " align=left>" . $dtnotiketkirim . "</td>";
                    $tab .= "<td align=center id=tanggalkirimpks" . $no . " align=left>" . tanggalnormal($dttanggalkirimpks[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center id=nokendaraan" . $no . " align=left></td>";
                    $tab .= "<td align=center id=kgkirim" . $no . " align=right>" . number_format($dtkgkirim[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center>" . $dtnotiketterima[$dtnotiketkirim] . "</td>";
                    $tab .= "<td align=center>" . $dtkgterima[$dtnotiketkirim] . "</td>";
                    $tab .= "<td align=center id=kgterima" . $no . ">" . $dtkgterima[$dtnotiketkirim] . "</td>";
                    #= selisih
                    $dtkgselisih[$dtnotiketkirim] = ($dtkgterima[$dtnotiketkirim] - $dtkgkirim[$dtnotiketkirim]);
                    $tab .= "<td align=center id=kgselisih" . $no . ">" . number_format($dtkgselisih[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center id=rpkg" . $no . " align=right>" . number_format($dtrpkg[$param['nospk']], 2) . "</td>";
                    #= total rp
                    $dttotalrp[$dtnotiketkirim] = $dtkgkirim[$dtnotiketkirim] * $dtrpkg[$param['nospk']];
                    $tab .= "<td align=center id=rpjumlah" . $no . " align=right>" . number_format($dttotalrp[$dtnotiketkirim]) . "</td>";

                    #= toleransi, jika persen terisi maka hitung kg, jika kg terisi maka persen di 0-kan
                    if ($dtpersentoleransi[$param['nospk']] > 0) {
                        $tab .= "<td align=center id=persentoleransi" . $no . ">" . $dtpersentoleransi[$param['nospk']] . "</td>";
                        #= toleransi kg-nya
                        $dtkgtoleransi[$dtnotiketkirim] = round($dtpersentoleransi[$param['nospk']] / 100 * $dtkgkirim[$dtnotiketkirim] * -1);
                        $tab .= "<td align=center id=kgtoleransi" . $no . ">" . $dtkgtoleransi[$dtnotiketkirim] . "</td>";
                    } else {
                        $tab .= "<td align=center id=persentoleransi" . $no . ">0</td>";
                        #= toleransi kg
                        $dtkgtoleransi[$dtnotiketkirim] = $dtkgtoleransi[$param['nospk']] * -1;
                        $tab .= "<td align=center id=kgtoleransi" . $no . ">" . $dtkgtoleransi[$dtnotiketkirim] . "</td>";
                    }

                    #= kg claim (kg toleransi - kg selisih)
                    $disabledrpkgclaim = "";
                    $dtkgclaim[$dtnotiketkirim] = $dtkgselisih[$dtnotiketkirim] - $dtkgtoleransi[$dtnotiketkirim];
                    if ($dtkgclaim[$dtnotiketkirim] >= 0) {
                        $dtkgclaim[$dtnotiketkirim] = 0;
                        $disabledrpkgclaim = "disabled";
                    }
                    $tab .= "<td align=center id=kgclaim" . $no . ">" . $dtkgclaim[$dtnotiketkirim] . "</td>";

                    if ($datatransaksi == 0) {
                        $tab .= "<td align=center><input type=text  id=rpkgclaim" . $no . " " . $disabledrpkgclaim . " onblur=getrpclaim(" . $no . ") value='" . @$dtrpkgclaim[$dtnokontrak[$param['nospk']]] . "' id=rpkgclaim" . $no . " size=20  class=myinputtext></td>";
                        #= rpclaim = rpkgclaim * kgclaim
                        @$dtrpclaim[$dtnotiketkirim] = $dtkgclaim[$dtnotiketkirim] * $dtrpkgclaim[$dtnokontrak[$param['nospk']]];
                        $tab .= "<td align=center id=rpclaim" . $no . ">" . $dtrpclaim[$dtnotiketkirim] . "</td>";
                    } else {
                        $tab .= "<td align=center><input type=text  id=rpkgclaim" . $no . " " . $disabledrpkgclaim . " onblur=getrpclaim(" . $no . ") value='" . @$dtrpkgclaim[$dtnotiketkirim] . "' id=rpkgclaim" . $no . " size=20  class=myinputtext></td>";
                        #= rpclaim = rpkgclaim * kgclaim
                        @$dtrpclaim[$dtnotiketkirim] = $dtkgclaim[$dtnotiketkirim] * $dtrpkgclaim[$dtnotiketkirim];
                        $tab .= "<td align=center id=rpclaim" . $no . ">" . $dtrpclaim[$dtnotiketkirim] . "</td>";
                    }
                    $tab .= "<td align=center id=noakundebet" . $no . ">" . $dtnoakundebet[$param['nospk']] . "</td>";
                    $tab .= "</tr>";
                }
                if ($param['print'] != 'pdf' && $param['print'] != 'excel') {
                    $tab .= "<tr>";
                    $tab .= "<td align=center colspan=21><button id=save class=mybutton onclick=savedt(" . $no . ")>" . $_SESSION['lang']['save'] . "</button>";
                    $tab .= "</tr>";
                }
                break;

            case 'ipkd':
                $no = 0;
                $arrnotiketkirim = array();
                $str = "select * from " . $dbname . ".pabrik_timbangan_vw  where nosipb='" . $param['nospk'] . "' and millcode='" . $param['unit'] . "' and tanggal between '" . tanggalsystemn($param['tanggalkirim1']) . "' and '" . tanggalsystemn($param['tanggalkirim2']) . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $arrnotiketkirim[$bar['notiket']] = $bar['notiket'];
                    $dtnokendaraan[$bar['notiket']] = $bar['nokendaraan'];
                    $dtnokontrak[$bar['notiket']] = $bar['nokontrak'];
                    $dtkgkirim[$bar['notiket']] = $bar['beratbersih'];
                    $arrnokontrak[$bar['nokontrak']] = $bar['nokontrak'];
                    $dttanggalkirimpks[$bar['notiket']] = $bar['tanggal'];
                }

                $str = "select * from " . $dbname . ".pmn_spk_ipkd  where nospk='" . $param['nospk'] . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtrpkg[$bar['nospk']] = $bar['rpkg'];
                    $dttransportir[$bar['nospk']] = $bar['transportirdarat'];
                    $dtnoakundebet[$bar['nospk']] = $bar['noakundebet'];
                    $dtkodebarang[$bar['nospk']] = $bar['kodebarang'];
                }

                #= harga claim ambil dari kontrak
                $rpkgclaim = array();
                $str = "select * from " . $dbname . ".pmn_kontrakjual  where nokontrak in ('" . implode("','", $arrnokontrak) . "')";
                // echo $str;
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtrpkgclaim[$bar['nokontrak']] = $bar['hargasatuan'];
                }

                foreach ($arrnotiketkirim as $dtnotiketkirim) {
                    @$nouruttiket++;
                    if ($nouruttiket % 2 == 0) {
                        $bgcolor = "";
                    } else {
                        $bgcolor = "";
                    }
                    @$no++;
                    $tab .= "<tr  " . $bgcolor . " class=rowcontent id=row" . $no . ">";
                    $tab .= "<td align=center>" . $no . "</td>";
                    $tab .= "<td align=center id=notiket" . $no . " align=left>" . $dtnotiketkirim . "</td>";
                    $tab .= "<td align=center id=tanggalkirimpks" . $no . " align=left>" . tanggalnormal($dttanggalkirimpks[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center id=nokendaraan" . $no . " align=left>" . $dtnokendaraan[$dtnotiketkirim] . "</td>";
                    $tab .= "<td align=center id=kgkirim" . $no . " align=right>" . number_format($dtkgkirim[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center></td>";
                    $tab .= "<td align=center></td>";
                    $tab .= "<td align=center id=kgterima" . $no . "></td>";
                    $tab .= "<td align=center id=kgselisih" . $no . "></td>";
                    $tab .= "<td align=center id=rpkg" . $no . " align=right>" . number_format($dtrpkg[$param['nospk']], 2) . "</td>";
                    $dttotalrp[$dtnotiketkirim] = $dtkgkirim[$dtnotiketkirim] * $dtrpkg[$param['nospk']];
                    $tab .= "<td align=center id=rpjumlah" . $no . " align=right>" . number_format($dttotalrp[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center id=persentoleransi" . $no . "></td>";
                    $tab .= "<td align=center id=kgtoleransi" . $no . "></td>";
                    $tab .= "<td align=center id=kgclaim" . $no . "></td>";
                    $tab .= "<td align=center><input type=text  id=rpkgclaim" . $no . " disabled class=myinputtext></td>";
                    $tab .= "<td align=center id=rpclaim" . $no . "></td>";
                    $tab .= "<td align=center id=noakundebet" . $no . ">" . $dtnoakundebet[$param['nospk']] . "</td>";
                }
                if ($param['print'] != 'pdf' && $param['print'] != 'excel') {
                    $tab .= "<tr>";
                    $tab .= "<td align=center colspan=21><button  id=save class=mybutton onclick=savedt(" . $no . ")>" . $_SESSION['lang']['save'] . "</button>";
                    $tab .= "</tr>";
                }
                break;

            case 'etc':
                $arrnotiketkirim = array();
                $str = "select * from " . $dbname . ".pabrik_timbangan_vw  where nosipb='" . $param['nospk'] . "' and millcode='" . $param['unit'] . "' and tanggal between '" . tanggalsystemn($param['tanggalkirim1']) . "' and '" . tanggalsystemn($param['tanggalkirim2']) . "'";
                // echo $str;
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $arrnotiketkirim[$bar['notiket']] = $bar['notiket'];
                    // $dtnosipb[$bar['notiket']]=$bar['nosipb'];
                    $dtnokontrak[$bar['notiket']] = $bar['nokontrak'];
                    $dtkgkirim[$bar['notiket']] = $bar['beratbersih'];
                    $arrnokontrak[$bar['nokontrak']] = $bar['nokontrak'];
                    $dttanggalkirimpks[$bar['notiket']] = $bar['tanggal'];
                    $dtnokendaraan[$bar['notiket']] = $bar['nokendaraan'];
                    $countnotiketterima[$bar['notiket']] = 0;
                }


                if (count($arrnotiketkirim) > 0) {
                    $str = "select * from " . $dbname . ".pabrik_timbangan_vw  where nosipb='" . $param['nospk'] . "' and norefrensi in ('" . implode("','", $arrnotiketkirim) . "')";

                    $res = fetchdata($str);
                    foreach ($res as $bar) {
                        $arrnotiketterima[$bar['notiket']] = $bar['notiket'];
                        $listnotiketterima[$bar['norefrensi']][$bar['notiket']] = $bar['notiket'];
                        @$countnotiketterima[$bar['norefrensi']] += 1;
                        @$rowspan[$bar['norefrensi']] += 1;
                        $dtkgterimadt[$bar['norefrensi']][$bar['notiket']] = $bar['beratbersih'];
                        @$dtkgterima[$bar['norefrensi']] += $bar['beratbersih'];
                    }
                }

                $str = "select * from " . $dbname . ".pmn_spk_etc  where kodept='" . $kodept[$param['unit']] . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $dtrpkg[$bar['nospk']] = $bar['rpkg'];
                    $dtpersentoleransi[$bar['nospk']] = $bar['toleransi'];
                    $dtkgtoleransi[$bar['nospk']] = $bar['kgtoleransi'];
                    $dttransportir[$bar['nospk']] = $bar['transportirdarat'];
                    $dtnoakundebet[$bar['nospk']] = $bar['noakundebet'];
                    $dtkodebarang[$bar['nospk']] = $bar['kodebarang'];
                }

                #= data lama untuk ambil rp/kg claim
                $str = "select count(*) as jumrow from " . $dbname . ".pmn_batransport  where notransaksi='" . $param['notransaksi'] . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    @$datatransaksi = $bar['jumrow'];
                }

                $dtrpkgclaim = array();
                if ($datatransaksi == 0) {
                    #= harga claim ambil dari kontrak
                    if (@count($arrnokontrak) > 0) {
                        $str = "select * from " . $dbname . ".pmn_kontrakjual  where nokontrak in ('" . implode("','", $arrnokontrak) . "')";
                        $res = fetchdata($str);
                        foreach ($res as $bar) {
                            $dtrpkgclaim[$bar['nokontrak']] = $bar['nokontrak'];
                            $dtvalidasikontrak[$bar['nokontrak']] = $bar['nokontrak'];
                        }
                    }
                } else {
                    $str = "select * from " . $dbname . ".pmn_batransport  where notransaksi='" . $param['notransaksi'] . "'";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while ($bar = $res->fetch()) {
                        $dtrpkgclaim[$bar['notiket']] = $bar['rpkgclaim'];
                    }
                }

                $counter = $nouruttiket = 0;

                foreach ($arrnotiketkirim as $dtnotiketkirim) {
                    @$nouruttiket++;
                    if ($nouruttiket % 2 == 0) {
                        // $bgcolor="style=background-color:lightblue;";
                        $bgcolor = "";
                    } else {
                        $bgcolor = "";
                    }
                    @$no++;
                    $tab .= "<tr  " . $bgcolor . " class=rowcontent id=row" . $no . ">";
                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "'>" . $no . "</td>";

                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=notiket" . $no . ">" . $dtnotiketkirim . "</td>";
                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=tanggalkirimpks" . $no . ">" . tanggalnormal($dttanggalkirimpks[$dtnotiketkirim]) . "</td>";
                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=nokendaraan" . $no . ">" . $dtnokendaraan[$dtnotiketkirim] . "</td>";
                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgkirim" . $no . ">" . $dtkgkirim[$dtnotiketkirim] . "</td>";
                    $nokirim = 0;
                    if ($countnotiketterima[$dtnotiketkirim] > 0) {
                        foreach ($arrnotiketterima as $dtnotiketterima) {
                            if (@$listnotiketterima[$dtnotiketkirim][$dtnotiketterima] != '') {
                                $nokirim++;
                                if ($nokirim == 1) {
                                    $tab .= "<td align=center>" . $dtnotiketterima . "</td>";
                                    $tab .= "<td align=center>" . $dtkgterimadt[$dtnotiketkirim][$dtnotiketterima] . "</td>";

                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgterima" . $no . ">" . $dtkgterima[$dtnotiketkirim] . "</td>";

                                    #= selisih
                                    $dtkgselisih[$dtnotiketkirim] = ($dtkgterima[$dtnotiketkirim] - $dtkgkirim[$dtnotiketkirim]);
                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgselisih" . $no . ">" . $dtkgselisih[$dtnotiketkirim] . "</td>";
                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=rpkg" . $no . ">" . $dtrpkg[$param['nospk']] . "</td>";
                                    #= totalrp
                                    $dttotalrp[$dtnotiketkirim] = $dtkgkirim[$dtnotiketkirim] * $dtrpkg[$param['nospk']];
                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=rpjumlah" . $no . ">" . $dttotalrp[$dtnotiketkirim] . "</td>";

                                    #= toleransi, jika persen terisi maka hitung kg, jika kg terisi maka persen di 0-kan
                                    if ($dtpersentoleransi[$param['nospk']] > 0) {
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=persentoleransi" . $no . ">" . $dtpersentoleransi[$param['nospk']] . "</td>";

                                        #= toleransi kg-nya
                                        $dtkgtoleransi[$dtnotiketkirim] = round($dtpersentoleransi[$param['nospk']] / 100 * $dtkgkirim[$dtnotiketkirim] * -1);
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgtoleransi" . $no . ">" . $dtkgtoleransi[$dtnotiketkirim] . "</td>";
                                    } else {
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=persentoleransi" . $no . ">0</td>";
                                        #= toleransi kg
                                        $dtkgtoleransi[$dtnotiketkirim] = $dtkgtoleransi[$param['nospk']] * -1;
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgtoleransi" . $no . ">" . $dtkgtoleransi[$dtnotiketkirim] . "</td>";
                                    }

                                    #= kg claim (kg toleransi - kg selisih)
                                    $disabledrpkgclaim = "";
                                    $dtkgclaim[$dtnotiketkirim] = $dtkgselisih[$dtnotiketkirim] - $dtkgtoleransi[$dtnotiketkirim];
                                    if ($dtkgclaim[$dtnotiketkirim] >= 0) {
                                        $dtkgclaim[$dtnotiketkirim] = 0;
                                        $disabledrpkgclaim = "disabled";
                                    }
                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=kgclaim" . $no . ">" . $dtkgclaim[$dtnotiketkirim] . "</td>";

                                    if ($datatransaksi == 0) {
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "'><input type=text  id=rpkgclaim" . $no . " " . $disabledrpkgclaim . " onblur=getrpclaim(" . $no . ") value='" . @$dtrpkgclaim[$dtnokontrak[$dtnotiketkirim]] . "' id=rpkgclaim" . $no . " size=20  class=myinputtext></td>";
                                        #= rpclaim = rpkgclaim * kgclaim
                                        @$dtrpclaim[$dtnotiketkirim] = $dtkgclaim[$dtnotiketkirim] * $dtrpkgclaim[$dtnokontrak[$dtnotiketkirim]];
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=rpclaim" . $no . ">" . $dtrpclaim[$dtnotiketkirim] . "</td>";
                                    } else {
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "'><input type=text  id=rpkgclaim" . $no . " " . $disabledrpkgclaim . " onblur=getrpclaim(" . $no . ") value='" . @$dtrpkgclaim[$dtnotiketkirim] . "' id=rpkgclaim" . $no . " size=20  class=myinputtext></td>";
                                        #= rpclaim = rpkgclaim * kgclaim
                                        @$dtrpclaim[$dtnotiketkirim] = $dtkgclaim[$dtnotiketkirim] * $dtrpkgclaim[$dtnotiketkirim];
                                        $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=rpclaim" . $no . ">" . $dtrpclaim[$dtnotiketkirim] . "</td>";
                                    }
                                    $tab .= "<td align=center rowspan='" . $rowspan[$dtnotiketkirim] . "' id=noakundebet" . $no . ">" . $dtnoakundebet[$param['nospk']] . "</td>";

                                    $tab .= "</tr>";
                                } else {
                                    $tab .= "<tr  " . $bgcolor . " class=rowcontent>";
                                    $tab .= "<td align=center>" . $dtnotiketterima . "</td>";
                                    $tab .= "<td align=center>" . $dtkgterimadt[$dtnotiketkirim][$dtnotiketterima] . "</td>";
                                    $tab .= "</tr>";
                                }
                            }
                        }
                    } else {
                        $tab .= "<td align=center></td>";
                        $tab .= "<td align=center></td>";
                        $tab .= "<td align=center id=kgterima" . $no . "></td>";
                        $tab .= "<td align=center id=kgselisih" . $no . "></td>";
                        $tab .= "<td align=center id=rpkg" . $no . ">" . $dtrpkg[$param['nospk']] . "</td>";
                        $dttotalrp[$dtnotiketkirim] = $dtkgkirim[$dtnotiketkirim] * $dtrpkg[$param['nospk']];
                        $tab .= "<td align=center id=rpjumlah" . $no . ">" . $dttotalrp[$dtnotiketkirim] . "</td>";
                        $tab .= "<td align=center id=persentoleransi" . $no . "></td>";
                        $tab .= "<td align=center id=kgtoleransi" . $no . "></td>";
                        $tab .= "<td align=center id=kgclaim" . $no . "></td>";
                        $tab .= "<td align=center><input type=text  id=rpkgclaim" . $no . " disabled onblur=getrpclaim(" . $no . ") id=rpkgclaim" . $no . " size=20  class=myinputtext></td>";
                        $tab .= "<td align=center id=rpclaim" . $no . "></td>";
                        $tab .= "<td align=center id=noakundebet" . $no . ">" . $dtnoakundebet[$param['nospk']] . "</td>";
                    }
                }
                if ($param['print'] != 'pdf' && $param['print'] != 'excel') {
                    $tab .= "<tr>";
                    $tab .= "<td align=center colspan=21><button  id=save class=mybutton onclick=savedt(" . $no . ")>" . $_SESSION['lang']['save'] . "</button>";
                    $tab .= "</tr>";
                }
                break;
            default:
                ### PDFASLI
                $no = 0;
                $arrnotiketkirim = array();
                $sql = "select a.*,b.supir from " . $dbname . ".pmn_batransport a left join pabrik_timbangan b on a.notiket=b.notransaksi where a.notransaksi='{$notransaksi}' order by a.kodesupplier, b.supir";
                $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);

                $resttl = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
                $resttl->setFetchMode(PDO::FETCH_OBJ);
                foreach ($resttl as $key) {
                    $notranstiketx[$key->kodecustomer != '' ? $key->kodecustomer : $key->kodesupplier][$key->supir][$key->nokendaraan][$key->notiket] = [
                        'tanggalkirimpks' => $key->tanggalkirimpks,
                        'kgkirim' => $key->kgkirim,
                        'rpkg' => $key->rpkg,
                        'rpjumlah' => $key->rpjumlah,
                        'nilaipph' => $key->nilaipph,
                        'suppcust' => ($key->kodecustomer != '') ? $key->kodecustomer : $key->kodesupplier,
                        'supir' => $key->supir,
                    ];
                    $persenPpn = $key->persenppn;
                    $ttlRpx += $key->rpjumlah;
                }
                $nilaiPpn = ($persenPpn / 100) * $ttlRpx;

                $firstRow = true;
                foreach ($notranstiketx as $suppcust => $supirData) {
                    // Tampilkan baris pemisah untuk setiap `suppcust`
                    if ($firstRow) {
                        $tab .= "<tr><td colspan=10>Divisi " . $suppcust . "</td></tr>";
                        $firstRow = false;
                    } else {
                        $tab .= "<tr><td colspan=10>Divisi " . $suppcust . "</td></tr>";
                    }

                    foreach ($supirData as $supir => $kendaraanData) {
                        foreach ($kendaraanData as $nopol => $bar) {
                            $subttlbrt[$nopol] = 0;
                            $subttlttlrp[$nopol] = 0;
                            $subttlttlpph[$nopol] = 0;

                            foreach ($bar as $notiket => $val) {
                                @$no++;
                                $tab .= "<tr class=rowcontent id=row" . $no . ">";
                                $tab .= "<td align=center>" . $no . "</td>";
                                $tab .= "<td align=center>" . $notiket . "</td>";
                                $tab .= "<td align=center>" . $supir . "</td>";
                                $tab .= "<td align=center>" . tanggalnormal($val['tanggalkirimpks']) . "</td>";
                                $tab .= "<td align=center>" . $nopol . "</td>";
                                $tab .= "<td align=center>" . ($namasupcus[$suppcust] != '' ? $namasupcus[$suppcust] : $namacus[$suppcust]) . "</td>";
                                $tab .= "<td align=right>" . number_format($val['kgkirim']) . "</td>";
                                $tab .= "<td align=right>" . number_format($val['rpkg'], 2) . "</td>";
                                $tab .= "<td align=right>" . number_format($val['rpjumlah']) . "</td>";
                                $tab .= "<td align=center>" . $noakundebet . "</td>";
                                $tab .= "</tr>";

                                // Subtotal dan update
                                $subttlbrt[$nopol] += $val['kgkirim'];
                                $subttlttlrp[$nopol] += $val['rpjumlah'];
                                $subttlttlpph[$nopol] = $val['nilaipph'];
                            }

                            // Subtotal per kendaraan
                            $tab .= "<tr>";
                            $tab .= "<td align=right colspan=6><b>SUB TOTAL KENDARAAN</b></td>";
                            $tab .= "<td align=right><b>" . number_format($subttlbrt[$nopol]) . "</b></td>";
                            $tab .= "<td align=right></td>";
                            $tab .= "<td align=right><b>" . number_format($subttlttlrp[$nopol]) . "</b></td>";
                            $tab .= "<td align=right></td>";
                            $tab .= "</tr>";

                            $ttlAllbrt += $subttlbrt[$nopol];
                            $ttlAllrp += $subttlttlrp[$nopol];
                            $ttlAllpph = $subttlttlpph[$nopol];
                        }
                    }
                }

                $grandtotal = $ttlAllrp + $nilaiPpn - $ttlAllpph;

                $tab .= "<tr>";
                $tab .= "<td align=right colspan=6><b>  TOTAL  </b></td>";
                $tab .= "<td align=right><b>" . number_format($ttlAllbrt) . "</b></td>";
                $tab .= "<td align=right><b></b></td>";
                $tab .= "<td align=right><b>" . number_format($ttlAllrp) . "</b></td>";
                $tab .= "<td align=right><b></b></td>";
                $tab .= "</tr>";

                $tab .= "<tr>";
                $tab .= "<td align=right colspan=8><b>  PPN  </b></td>";
                $tab .= "<td align=right><b>" . number_format($nilaiPpn) . "</b></td>";
                $tab .= "<td align=right><b></b></td>";
                $tab .= "</tr>";

                $tab .= "<tr>";
                $tab .= "<td align=right colspan=8><b>  PPH  </b></td>";
                $tab .= "<td align=right><b>" . number_format($ttlAllpph, 2) . "</b></td>";
                $tab .= "<td align=right><b></b></td>";
                $tab .= "</tr>";

                $tab .= "<tr>";
                $tab .= "<td align=right colspan=8><b>  GRAND TOTAL  </b></td>";
                $tab .= "<td align=right><b>" . number_format($grandtotal, 2) . "</b></td>";
                $tab .= "<td align=right><b></b></td>";
                $tab .= "</tr>";

                $tab .= "<tr>";
                $tab .= "<td align=left colspan=10><i>Terbilang : " . terbilang($grandtotal, 3) . "</i></td>";
                $tab .= "</tr>";

                if ($param['print'] != 'pdf' && $param['print'] != 'excel') {
                    $tab .= "<tr>";
                    $tab .= "<td align=center colspan=21><button  id=save class=mybutton onclick=savedt(" . $no . ")>" . $_SESSION['lang']['save'] . "</button>";
                    $tab .= "</tr>";
                }
                break;
        }

        $tab .= "</tbody>";
        $tab .= "</table>";

        // TTD
        $namaKaryawan = makeOption($dbname, "datakaryawan", "karyawanid,namakaryawan");

        $kodeUnit = $pabrik;
        $strQuery = selectQuery($dbname, "setup_2ttd", "*", "menuid = '2074' and kodeunit = '{$kodeUnit}' ORDER BY id ASC");
        $getTtd = fetchData($strQuery);

        $tbodyTTD = "";
        if (count($getTtd) > 0) {
            $counter = 0;
            $tbodyTTD .= "<tr>";
            foreach ($getTtd as $data) {
                $tbodyTTD .= "
                    <td>
                        <table align=center style='margin-top:20px;'>
                            <tr style='text-align:center; font-weight:bold;'>
                                <td>
                                    {$data['judul']}
                                    <br/>
                                    <br/>
                                    <br/>
                                    <br/>
                                </td>
                            </tr>
                            <tr style='text-align:center;'>
                                <td><u>{$namaKaryawan[$data['karyawanid']]}</u></td>
                            </tr>
                            <tr style='text-align:center;'>
                                <td>{$data['jabatan']}</td>
                            </tr>
                        </table>
                    </td>
                ";

                $counter++;
                if ($counter % 4 == 0) {
                    $tbodyTTD .= "</tr><tr>"; // Close current row and start a new one
                }
            }
            $tbodyTTD .= "</tr>";
        }

        $tab .= "
            <table style='width:100%;white-space: nowrap;'>
                {$tbodyTTD}
            </table>
        </main>";

        $footer = "<div style='position:absolute;right:0;bottom:0;text-align:right'>" . tglnmbln(date('Y-m-d'), 'i', 'l') . " " . date('H:i:s') . "<br><font style='font-size:7px'>Generated By OWL System</font></div>";
        $footer .= "<div style='position:absolute;left:0;bottom:0;text-align:right'><i>Total Record : " . $no . " Data</i></div>";

        switch ($param['print']) {
            case 'pdf':
                $stream = $css . $header . $tab . $footer;

                $dompdf = new Dompdf();
                
                $dompdf->loadHtml($stream);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $dompdf->stream("BA TRANSPORT", array("Attachment" => false));
                break;
            case 'excel':
                $stream = $tab;
                $nop_ = "batransport_" . date('Ymd_His');

                if (strlen($stream) > 0) {
                    if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                            if ($file != "." && $file != ".." && $file != "index.html") {
                                @unlink('tempExcel/' . $file);
                            }
                        }
                        closedir($handle);
                    }
                    $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
                    if (!fwrite($handle, $stream)) {
                        echo "<script language=javascript1.2>
                                parent.window.alert('Cant convert to excel format');
                                </script>";
                        exit;
                    } else {
                        echo "<script language=javascript1.2>
                                window.location='tempExcel/" . $nop_ . ".xls';
                                </script>";
                    }
                    closedir($handle);
                }

                break;
            default:
                echo $tab;
                break;
        }

        break;
    // End Umar

    case 'pdfclaim':
        $tab = "<style>
			@page {
				margin-top: 30px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			body {
				font-family: Serif, Times-Roman;
			}

			footer {
				position: fixed;
				bottom: -10px;
				left: 0px;
				right: 0px;
				height: 50px;
			}

		</style>";
        $noclaim = $kgselisih = $rpclaim = $kgclaim = 0;
        $str = "select * from " . $dbname . ".pmn_batransport where notransaksi='" . $param['notransaksi'] . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $rounit = $bar['rounit'];
            $tanggal = $bar['tanggal'];
            $notransaksi = $bar['notransaksi'];
            $transportir = $bar['transportir'];
            $kodebarang = $bar['kodebarang'];
            $nospk = $bar['nospk'];
            $kgtoleransi = abs($bar['kgtoleransi']);
            $persentoleransi = abs($bar['persentoleransi']);
            $nokontrak = $bar['nokontrak'];
            $tipe = $bar['tipe'];
            if ($bar['kgclaim'] != 0) {
                $noclaim++;
                $kgselisih += abs($bar['kgselisih']);
                $kgclaim += abs($bar['kgclaim']);
                $rpkgclaim = abs($bar['rpkgclaim']);
                $rpclaim += abs($bar['rpclaim']);
            }
        }
        // exit("Error".$kodebarang.___.$arrinisial[$kodebarang]);
        if ($tipe == 'sip') {
            $str = "select * from " . $dbname . ".pmn_suratperintahpengiriman where nodo='" . $nospk . "'";
            $res = fetchdata($str);
            foreach ($res as $bar) {
                // $namakapal=$bar['namakapal    namaponton']
                $texttransport = 'Kapal / Ponton ';
                $texttransport = "Kapal " . $namakapalponton[$bar['namakapal']] . " / Ponton " . $namakapalponton[$bar['namaponton']] . " ";
                $texttransportdua = '';
                $texttimbangansounding = 'Timbangan ';
                $pelabuhantujuan = $bar['pelabuhanbongkar'];
            }
        } else {
            $str = "select * from " . $dbname . ".pmn_spk_etc where nospk='" . $nospk . "'";
            $res = fetchdata($str);
            foreach ($res as $bar) {
                // if($bar['transportir']!=''){
                // $texttransport=$texttransportdua='Kapal / Ponton ';
                // $texttimbangansounding='Sounding ';
                // }
                // if($bar['transportirdarat']!=''){
                $texttransport = $texttransportdua = 'Truck ';
                $texttimbangansounding = 'Timbangan ';
                // }
                $pelabuhantujuan = $bar['pelabuhantujuan'];
            }
        }
        // exit("Error:".$texttransport);
        $str = "select * from " . $dbname . ".pmn_5franco";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $namafranco[$bar['id_franco']] = $bar['franco_name'];
        }

        if ($tipe == 'sip') {
            $texttoleransipersen = " " . hidezerodecimal($persentoleransi) . " % ";
            $texttoleransi = " " . hidezerodecimal($kgtoleransi) . " Kg per kapal / ponton dari angka timbangan";
        } else {
            if ($persentoleransi != 0) {
                $texttoleransi = " " . hidezerodecimal($persentoleransi) . " % dari angka sounding";
            }

            if ($kgtoleransi != 0) {
                $texttoleransi = " " . hidezerodecimal($kgtoleransi) . " Kg per truck dari angka timbangan";
            }
        }

        $str = "select * from " . $dbname . ".log_5supplier where supplierid='" . $transportir . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $namasupplier = $bar['namasupplier'];
            $namapemiliksupplier = $bar['namapenanggungjawab'];
        }

        $str = "select * from " . $dbname . ".log_5supalamat where supplierid='" . $transportir . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $alamatsupplier = $bar['alamat'];
        }

        $arrkodept = setheadreport($rounit, $kodept[$rounit]);
        $cellpadding = 1;
        $cellspacing = 1;
        $sizefont = '14';
        // print_r($arrkodept);exit();

        $tab .= "<div style='page-break-after: always;'>";
        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>"; //logoheight logowidth
        $tab .= "<tr>";
        $tab .= "<td style='width:50px;' align=center><img src=" . $arrkodept['logo'] . " style='width:" . $arrkodept['logowidth'] . ";height:" . $arrkodept['logoheight'] . "'></td>";
        $tab .= "<td style='width:350px;text-align:center;font-size:" . ($sizefont + 14) . "px'>" . $arrkodept['nama'] . "</td>";
        $tab .= "<td style='width:50px;'>&nbsp;</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $sizefont = '12';
        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Pontianak, " . tglnmbln($tanggal, 'i', 'l') . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Ref No : " . $notransaksi . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Kepada Yth :</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>" . $namasupplier . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>" . $alamatsupplier . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Atm : " . $namapemiliksupplier . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Perihal : Claim Atas Kekurangan Penerimaan " . $arrinisial[$kodebarang] . " " . $arrkodept['nama'] . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Sesuai Surat Perintah kerja No. " . $nospk . " khusus ketentuan angkutan " . $arrinisial[$kodebarang] . " via " . $texttransport . " bahwa toleransi susut /  penerimaan maksimum " . $texttoleransi . " Pabrik " . $arrkodept['nama'] . " ke " . $texttimbangansounding . " " . $namafranco[$pelabuhantujuan] . ".</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Berdasarkan Data Hasil Penerimaan " . $texttransport . " " . $arrinisial[$kodebarang] . " di " . $namafranco[$pelabuhantujuan] . ", ada beberapa " . $texttransportdua . " yang mengalami kekurangan muatan " . $texttransportdua . " " . $arrinisial[$kodebarang] . " yang diangkut sbb :</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";
        $no = 0;
        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $str = "select * from " . $dbname . ".pmn_batransport where notransaksi='" . $param['notransaksi'] . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            if ($bar['kgclaim'] < 0) {
                $no++;
                if ($tipe == 'sip') {
                    $tab .= "<tr>";
                    $tab .= "<td style='text-align:left' valign=top>" . $no . ".</td>";
                    $tab .= "<td style='text-align:left;'>Quantity PMKS sebanyak " . hidezerodecimal($bar['kgkirim']) . " Kg, Quantity di " . $namafranco[$pelabuhantujuan] . " sebanyak " . hidezerodecimal($bar['kgterima']) . " Kg, Kekurangan " . abs($bar['kgselisih']) . " Kg -  " . abs($bar['kgtoleransi']) . " Kg = <b>" . abs($bar['kgclaim']) . " Kg</b></td>";
                    $tab .= "<td style='text-align:left;'></td>";
                    $tab .= "</tr>";
                } else {
                    #= query ambil tanggal terima
                    $strterima = "select * from " . $dbname . ".pabrik_timbangan_vw where norefrensi='" . $bar['notiket'] . "'";
                    $resterima = fetchdata($strterima);
                    foreach ($resterima as $barterima) {
                        $tanggalterima = $barterima['tanggal'];
                        $notiketterima = $bar['notiket'];
                        $nokendaraanterima = $bar['nokendaraan'];
                        $beratbersihterima = $bar['beratbersih'];
                        if ($noclaim == $no) {
                            $texttanggalterima .= "tanggal  " . tglnmbln($tanggalterima, 'i', 'l') . " ";
                        } else {
                            $texttanggalterima .= "tanggal  " . tglnmbln($tanggalterima, 'i', 'l') . ", ";
                        }
                    }
                    $tab .= "<tr>";
                    $tab .= "<td style='text-align:left' valign=top>" . $no . ".</td>";
                    $tab .= "<td style='text-align:left;'>Tgl. " . tglnmbln($tanggalterima, 'i', 'l') . " " . $nokendaraanterima . " No. Tiket " . $notiketterima . " Quantity PMKS sebanyak " . hidezerodecimal($bar['kgkirim']) . " Kg, Quantity di " . $namafranco[$pelabuhantujuan] . " sebanyak " . hidezerodecimal($bar['kgterima']) . " Kg, Kekurangan " . abs($bar['kgselisih']) . " Kg -  " . abs($bar['kgtoleransi']) . " Kg = <b>" . abs($bar['kgclaim']) . " Kg</b></td>";
                    $tab .= "<td style='text-align:left;'></td>";
                    $tab .= "</tr>";
                }
            }
        }
        $tab .= "</table>";

        $tab .= "<br>";

        if ($nokontrak != '') {
            $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
            $tab .= "<tr>";
            $tab .= "<td style='text-align:left;'>" . $arrinisial[$kodebarang] . " tersebut diangkut dari PMKS " . $arrkodept['nama'] . " ke " . $namafranco[$pelabuhantujuan] . " untuk memenuhi kontrak No. : " . $nokontrak . "</td>";
            $tab .= "</tr>";
            $tab .= "</table>";
        }

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;' colspan=3>Atas kekurangan penerimaan " . $arrinisial[$kodebarang] . " tersebut akan kami claim ke transporter dengan perhitungan</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Total " . $arrinisial[$kodebarang] . " yang susut " . $texttanggalterima . "</td>";
        $tab .= "<td style='text-align:left;width:10px' >:</td>";
        $tab .= "<td style='text-align:left;'>" . hidezerodecimal($kgselisih) . " Kg</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        if ($tipe == 'sip') {
            $tab .= "<td style='text-align:left;'>Toleransi " . hidezerodecimal($texttoleransipersen) . " %</td>";
        } else {
            $tab .= "<td style='text-align:left;'>Toleransi " . hidezerodecimal($kgtoleransi) . " Kg ( " . $noclaim . " Unit)</td>";
        }
        $tab .= "<td style='text-align:left;width:10px' >:</td>";
        $tab .= "<td style='text-align:left;'>" . hidezerodecimal(($kgtoleransi) * ($noclaim)) . " Kg</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Total Claim</td>";
        $tab .= "<td style='text-align:left;width:10px' >:</td>";
        $tab .= "<td style='text-align:left;'>" . hidezerodecimal($kgclaim) . " Kg</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Harga " . $arrinisial[$kodebarang] . "</td>";
        $tab .= "<td style='text-align:left;width:10px' >:</td>";
        $tab .= "<td style='text-align:left;'>Rp. " . hidezerodecimal($rpkgclaim) . " / Kg</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Nilai Claim</td>";
        $tab .= "<td style='text-align:left;width:10px' >:</td>";
        $tab .= "<td style='text-align:left;'>Rp. " . hidezerodecimal($rpclaim) . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Nilai Claim sebesar <u><b>Rp. " . hidezerodecimal($rpclaim) . "</b></u> tersebut akan langsung kami potong dari tagihan " . $namasupplier . ".</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Demikian disampaikan.</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Terima Kasih,</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        for ($i = 0; $i <= 2; $i++) {
            $tab .= "<tr>";
            $tab .= "<td style='text-align:left;'>&nbsp;</td>";
            $tab .= "</tr>";
        }
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>______________________</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Manager " . $namafranco[$pelabuhantujuan] . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";
        // echo $tab;exit();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($tab);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();
        if ($urlefil == '0') {
            $dompdf->stream("Print_BA_" . $notransaksi, array("Attachment" => 0));
        } else {
            file_put_contents($urlefil, $dompdf->output());
        }
        break;

    case 'pdfba':
        $tab = "<style>
			@page {
				margin-top: 30px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			body {
				font-family: Serif, Times-Roman;
			}

			footer {
				position: fixed;
				bottom: -10px;
				left: 0px;
				right: 0px;
				height: 50px;
			}

		</style>";
        $noclaim = $kgselisih = $rpclaim = $kgclaim = 0;
        $str = "select * from " . $dbname . ".pmn_batransport where notransaksi='" . $param['notransaksi'] . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $rounit = $bar['rounit'];
            $tanggal = $bar['tanggal'];
            $notransaksi = $bar['notransaksi'];
            $transportir = $bar['transportir'];
            $kodebarang = $bar['kodebarang'];
            $nospk = $bar['nospk'];
            $kgtoleransi = abs($bar['kgtoleransi']);
            $persentoleransi = abs($bar['persentoleransi']);
            $nokontrak = $bar['nokontrak'];
            $tipe = $bar['tipe'];
            $kodecustomer = $bar['kodecustomer'];
            $kgselisih += $bar['kgselisih'];
            $kgkirim += $bar['kgkirim'];
    
        }

        $str = "select * from " . $dbname . ".log_5supplier where supplierid='" . $transportir . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $namasupplier = $bar['namasupplier'];
            $namapemiliksupplier = $bar['namapenanggungjawab'];
        }

        $str = "select * from " . $dbname . ".log_5supalamat where supplierid='" . $transportir . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $alamatsupplier = $bar['alamat'];
        }

        $str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $hargasatuan = $bar['hargasatuan'];
        }
 
        $cellpadding = 1;
        $cellspacing = 1; 
        $arrinduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $rounit . "'");
        $kodept = $arrinduk[$rounit];

        $arralamat = makeOption($dbname, 'organisasi', 'kodeorganisasi,alamat', "kodeorganisasi='" . $kodept . "'");
        
        $arr_npwp = makeOption($dbname, 'setup_org_npwp', 'kodeorg,npwp', "kodeorg='" . $kodept . "'");

        $alamatpt = $arralamat[$kodept];
        $ptnpwp = $arr_npwp[$kodept];

        $path = "images/logo/" . $kodept . ".jpg";
        // print_r($arrkodept);exit();

        $tab .= "<div style='page-break-after: always;'>";
        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>"; //logoheight logowidth
        $tab .= "<tr>";
        $tab .= "<td style='width:50px;' align=left><img src=" . $path . " style='width:100px;height: 100px;'></td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $sizefont = '15';
        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Surabaya, " . tglnmbln($tanggal, 'i', 'l') . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>No. $notransaksi</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
  
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Kepada Yth :</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>" . $namasupplier . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>UP. Ibu Rita Wati</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>" . $alamatsupplier . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";
        $tab .= "<br>";
        $tab .= "<br>";
        $tab .= "<br>";
        
          $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Hal : <u>Pengajuan Klaim Susut Lebih dari 0.25% </u></td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Dengan Hormat,</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'> Bersama dengan surat ini kami mengajukan klaim atas susut lebih dari 0.25% pada truk pengangkutan ".getNamaBrg($kodebarang)." ke PKS ".getNamaCustomer($kodecustomer)." kontrak No. $nokontrak.</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Adapun klaim atas susut lebih dari 0.25% sebesar " . number_format($kgselisih) . " Kg dengan perhitungan sbb: </td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'> = <b>" . number_format($kgkirim) . " Kg </b> (".getNamaBrg($kodebarang)." terkirim ke ".$kodecustomer.")</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'> = <b>" . number_format($kgselisih) . " Kg </b> </td>";
        $tab .= "</tr>";
        
        $tab .= "<tr>";
        $tab .= "<td height=50px style='text-align:left;'>Kami akan membebankan klaim kepada perusahaan ibu dengan perhitungan sebagai berikut :</td>";
        $tab .= "</tr>";
        $tab .= "</table>";
 

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";

        $tab .= "<tr>";
        $tab .= "<td width=200pxstyle='text-align:left;'>Klaim Susut Lebih dari 0.25%   </td>";
        $tab .= "<td style='text-align:left;'>: <b>" . number_format($kgselisih) . " Kg X Rp" . number_format($hargasatuan, 2) . "/ Kg </b>(Harga Jual SPJB Kontrak " . substr($nokontrak, 0, 3) . ")</td>";
        $tab .= "</tr>";
        $rupiahbayar  = $kgselisih * $hargasatuan;
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'> </td>";
        $tab .= "<td style='text-align:left;'>: <b>Rp. " . number_format($rupiahbayar, 2) . " </b>(" . terbilang($rupiahbayar,3) . ")</td>";
        $tab .= "</tr>";
        

        $tab .= "</table>";

        $tab .= "<br>";
        $tab .= "<br>";
        
        $tab .= "<table>";
        $tab .= "<tr>";
            $tab .= "<td style='text-align:left;'>Demikian pengajuan klaim ini kami sampaikan, Atas perhatian dan kerjasamanya kami ucapkan terima kasih.</td>"; 
        $tab .= "</tr>";
        $tab .= "</table>";
        
        $tab .= "<br>";
        $tab .= "<br>";
        $tab .= "<br>";
            
        $tab .= "<table>";
            $tab .= "<tr>";
            $tab .= "<td style='text-align:left;'>Hormat kami,</td>"; 
            $tab .= "</tr>";
            $tab .= "<tr>";
            $tab .= "<td style='text-align:left;'>".getNamaOrg($kodept)."</td>"; 
            $tab .= "</tr>";
            $tab .= "<tr>";
            $tab .= "<td height=100px style='text-align:left;'></td>"; 
            $tab .= "</tr>";
            $tab .= "<tr>";
            $tab .= "<td style='text-align:left;'><u>Ir. Gatot Setiyo Nuswantoro </u></td>"; 
            $tab .= "</tr>";
            $tab .= "<tr>";
            $tab .= "<td style='text-align:left;'>Kabag Trading & Purchasing</td>"; 
            $tab .= "</tr>";
        $tab .= "</table>";
 
 
 
  

     
 
        // echo $tab;exit();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($tab);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();
        if ($urlefil == '0') {
            $dompdf->stream("Print_BA_" . $notransaksi, array("Attachment" => 0));
        } else {
            file_put_contents($urlefil, $dompdf->output());
        }
        break;
 

    case 'pdfinvoice':
        $tab = "<style>
			@page {
				margin-top: 30px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			body {
				font-family: Serif, Times-Roman;
			}

			footer {
				position: fixed;
				bottom: -10px;
				left: 0px;
				right: 0px;
				height: 50px;
			}

		</style>";
        $noclaim = $kgselisih = $rpclaim = $kgclaim = 0;
        $str = "select * from " . $dbname . ".pmn_batransport where notransaksi='" . $param['notransaksi'] . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $noinvoice = $bar['noinvoice'];
            $rounit = $bar['rounit'];
            $tanggal = $bar['tanggal'];
            $unit = $bar['unit'];
            $notransaksi = $bar['notransaksi'];
            $transportir = $bar['transportir'];
            $kodebarang = $bar['kodebarang'];
            $nospk = $bar['nospk'];
            $kgtoleransi = abs($bar['kgtoleransi']);
            $persentoleransi = abs($bar['persentoleransi']);
            $nokontrak = $bar['nokontrak'];
            $tipe = $bar['tipe'];
            $kodecustomer = $bar['kodecustomer'];
            $tiketref = $bar['tiketref'];
            $jenisba = $bar['jenis'];
            $rpkg = $bar['rpkg'];
            $dendakg = $bar['dendakg'];
            $rpdenda = $bar['rpdenda'];
            $persenppn = $bar['persenppn'];
            $nilaipph = $bar['nilaipph'];
            $nettoreal = $bar['nettoreal'];
            $kgkirim += $bar['kgkirim'];
            $rpjumlah += $bar['rpjumlah'];
            $kgselisih += $bar['kgselisih'];
        }
        // $rpjumlah = $nettoreal;

        $str = "select * from " . $dbname . ".pmn_batransport where notransaksi='" . $param['notransaksi'] . "' and notiket='" . $tiketref . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $kginternal = $bar['kginternal'];
        }



        $str = "select hargasatuan from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $hargasatuan = $bar['hargasatuan'];
        }
        $str = "select * from " . $dbname . ".log_5supalamat where supplierid='" . $transportir . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $alamatsupplier = $bar['alamat'];
        }

        $str = "select * from " . $dbname . ".log_5supnpwp where supplierid='" . $transportir . "'"; 
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $npwpsupplier = $bar['npwp'];
        }

        $str = "select * from " . $dbname . ".log_5supplier where supplierid='" . $transportir . "'"; 
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $namasupplier = $bar['namasupplier'];
        }

        $str = "select * from " . $dbname . ".log_5supuser where id_supplier='" . $transportir . "'"; 
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $emailsupp = $bar['email'];
        }

        $str = "select * from " . $dbname . ".pabrik_timbangan where notransaksi='" . $tiketref . "'"; 
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $nopoltiket = $bar['nokendaraan'];
            $kgkebuyer = $bar['kgpembeli'];
        }

        #cek jika jenis BA 1 maka sama dengan retur 
        if ($jenisba == 1) {
            $kgkebuyer = $kginternal;
        }

        
        $arrinduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $rounit . "'");
        $kodept = $arrinduk[$rounit];

        $arralamat = makeOption($dbname, 'organisasi', 'kodeorganisasi,alamat', "kodeorganisasi='" . $kodept . "'");

        $arr_npwp = makeOption($dbname, 'setup_org_npwp', 'kodeorg,npwp', "kodeorg='" . $kodept . "'");

        $alamatpt = $arralamat[$kodept];
        $ptnpwp = $arr_npwp[$kodept];

        $arrkodept = setheadreport($rounit, $kodept[$rounit]);
        $cellpadding = 1;
        $cellspacing = 1;
        $sizefont = '14';
        // print_r($arrkodept);exit();

        $tab .= "<div style='page-break-after: always;'>";
        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " style='border:1px solid #000; border-collapse:collapse;'>";
        $tab .= "<tr>";
        $tab .= "<td style='width:350px;text-align:center;font-size:" . ($sizefont + 14) . "px'>" . $namasupplier . "</td>"; 
        $tab .= "</tr>";
        $tab .= "<tr>";
    
        $tab .= "<td style='width:350px;text-align:center;font-size:" . ($sizefont) . "px'>" . $alamatsupplier . "</td>"; 
        $tab .= "</tr>";
        $tab .= "<tr>";
    
        $tab .= "<td style='width:350px;text-align:center;font-size:" . ($sizefont) . "px'>" . $emailsupp . "</td>"; 
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $sizefont = '12';
        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Dari : </td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'> <b>$namasupplier </b></td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'> <b>$alamatsupplier </b></td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'> NPWP : $npwpsupplier </td>";
        $tab .= "</tr>";
        // $tab .= "<tr>";
        // $tab .= "<td style='text-align:left;'>Pontianak, " . tglnmbln($tanggal, 'i', 'l') . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
         
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Kepada Yth :</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'> <b>" . getNamaOrg($kodept) . "</b></td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>" . $alamatpt . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>NPWP : " . $ptnpwp . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table  width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:center;'><b>INVOICE </b></td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:center;'><b>No. $noinvoice </b></td>";
        $tab .= "</tr>"; 
        $tab .= "</table>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " style='font-size:" . $sizefont . "px;border:1px solid #000;border-collapse:collapse;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:center;border:1px solid #000;padding:5px;'><b>No.</b></td>";
        $tab .= "<td style='text-align:left;border:1px solid #000;padding:5px;'><b>Desciption</b></td>";
        $tab .= "<td style='text-align:left;border:1px solid #000;padding:5px;'><b>Qty</b></td>";
        $tab .= "<td style='text-align:left;border:1px solid #000;padding:5px;'><b>Unit Price</b></td>";
        $tab .= "<td style='text-align:left;border:1px solid #000;padding:5px;'><b>%</b></td>";
        $tab .= "<td style='text-align:left;border:1px solid #000;padding:5px;'><b>Amount</b></td>";
        $tab .= "</tr>";

        #hitung harga potongan
        $query = "SELECT hargapotongan,harga,id AS nourut FROM pmn_ongkosangkut_vw WHERE trpcode='{$tipe}' and  kodeunit = '{$unit}' and tujuan = '{$kodecustomer}' AND date_range = '{$tanggal}' AND komoditi = '{$kodebarang}' and posting = '1'";
        $bar = fetchdata($query);
        $hargasatuan = $bar[0]['harga'];
        $hargapotongan = $bar[0]['hargapotongan'];
        $rpdpp= $hargasatuan;
        $rupiahdpp=$kgkirim*$rpdpp;


        $rejectinfo="";
        // $newline="";
        $newline="".number_format($kgkirim)." Kg";
        $newline2="Rp ".number_format($rpkg)."";
        $newline3="Rp ".number_format($rpjumlah)."";



        $rpawal=$rpkg;
        if($tiketref!=''){
            $rejectinfo="<br> TIKET : ".$tiketref." (".$nopoltiket.") REJECT";
            $newline="".number_format($kgkebuyer)." Kg<br> <br> ".number_format($kgkirim)." Kg";
            $newline2="Rp ".number_format($rpdpp)."<br> <br> Rp ".number_format($rpkg)." ";
            $jlhdpp1=$kgkebuyer*$rpdpp;
            $jlhdpp2=$kgkirim*$rpkg;
            $newline3="Rp ".number_format($jlhdpp1)."<br> <br>Rp ".number_format($jlhdpp2)."";
        }

        $tab .= "<tr>";
        $tab .= "<td style='text-align:center;border:1px solid #000;padding:5px;'>1</td>";
        $tab .= "<td style='text-align:left;border:1px solid #000;padding:5px;'>Biaya Angkut ".getNamaBrg($kodebarang)." dari PKS ".getNamaOrg($kodept)." ke PKS ".getNamaCustomer($kodecustomer)." ".$rejectinfo." <br> <br>No Kontrak : $nokontrak </td>";
        $tab .= "<td style='text-align:right;border:1px solid #000;padding:5px;'>".$newline." </td>";
        $tab .= "<td style='text-align:right;border:1px solid #000;padding:5px;'>".$newline2."</td>";
        $tab .= "<td style='text-align:left;border:1px solid #000;padding:5px;'></td>";
        $tab .= "<td style='text-align:right;border:1px solid #000;padding:5px;'>".$newline3."</td>";
        $tab .= "</tr>";
        $toleransisusut=$persentoleransi/100;
        $maxselisih_kg=0;
        $maxselisih_kg=round($kgkirim*$toleransisusut,2);
        $klaimkg=round($ttglkgselisih-$maxselisih_kg,2);
        if($klaimkg<0){
            $klaimkg=0;
        }
        $ttldenda=round($dtrpkgclaim*$klaimkg,2);

        if ($dendakg>0){
            $tab .= "<tr>";
            $tab .= "<td style='text-align:center;border:1px solid #000;padding:5px;'>2</td>";
            $tab .= "<td style='text-align:left;border:1px solid #000;padding:5px;'>Klaim Kesusutan</td>";
            $tab .= "<td style='text-align:right;border:1px solid #000;padding:5px;'>".number_format($dendakg)." Kg</td>";
            $tab .= "<td style='text-align:right;border:1px solid #000;padding:5px;'>Rp ".number_format($hargasatuan)."</td>";
            $rpklaimsusut = $kgselisih * $hargasatuan;
            $tab .= "<td style='text-align:left;border:1px solid #000;padding:5px;'></td>";
            $tab .= "<td style='text-align:right;border:1px solid #000;padding:5px;'>Rp ".number_format($rpdenda)." </td>";
            $tab .= "</tr>";
        }


        $jumlah=$rpjumlah-$rpdenda;
        $nilaippn=$jumlah*($persenppn/100);


        $tab .= "<tr>";
        $tab .= "<td colspan='5' style='border:1px solid #000; text-align:left; padding:5px 5px 5px 43px;'>JUMLAH</td>";
        $tab .= "<td style='text-align:right;border:1px solid #000;padding:5px;'>Rp ".number_format($jumlah)."</td>";
        $tab .= "</tr>";
        
        $tab .= "<tr>";
        $tab .= "<td colspan='5' style='border:1px solid #000; text-align:left; padding:5px 5px 5px 43px;'>PPn 11%</td>";
        $tab .= "<td style='text-align:right;border:1px solid #000;padding:5px;'>Rp ".number_format($nilaippn)."</td>";
        $tab .= "</tr>";

        $showpph=0;
        if($nilaipph>0){
            $showpph="(Rp ".number_format($nilaipph).")";
        }
        $tab .= "<tr>";
        $tab .= "<td colspan='5' style='border:1px solid #000; text-align:left; padding:5px 5px 5px 43px;'>PPh 23</td>";
        $tab .= "<td style='text-align:right;border:1px solid #000;padding:5px;'>".$showpph."</td>";
        $tab .= "</tr>";

        $tab .= "<tr>";
        $tab .= "<td colspan='5' style='border:1px solid #000; text-align:left; padding:5px 5px 5px 43px;'>JUMLAH YANG HARUS DIBAYAR</td>";
         $tab .= "<td style='text-align:right;border:1px solid #000;padding:5px;'>Rp ".number_format($jumlah+$nilaippn-$nilaipph)."</td>";
        $tab .= "</tr>";

        $tab .= "</table>";

        $tab .= "<br>";
     
     
        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:" . $sizefont . "px;'>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Bank : BRI Cabang Surabaya</td>";
        $tab .= "<td style='text-align:center;'>Batulicin, ".tanggalbulan($tanggal,2)."</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Dengan Nomor : 0411-01-001179-302</td>";
        $tab .= "<td style='text-align:center;'>".$namatransportir[$transportir]."</td>";
        $tab .= "</tr>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'>Atas Nama : $namatransportir[$transportir]</td>";
        $tab .= "<td style='text-align:center;'></td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td style='text-align:left;'></td>";
        $tab .= "<td height=190px style='text-align:center;'>________________________</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

    

        $dompdf = new Dompdf();
        $dompdf->loadHtml($tab);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();
        if ($urlefil == '0') {
            $dompdf->stream("Print_BA_" . $notransaksi, array("Attachment" => 0));
        } else {
            file_put_contents($urlefil, $dompdf->output());
        }
        break;

    default:
        break;
}
function gethargatransport($kodeunit, $notiket, $tgl1, $tgl2, $komoditi)
{
    $whre = '';
    $str = "SELECT * from pabrik_timbangan where notransaksi='" . $notiket . "'";
    $res = fetchData($str);

    if ($res[0]['kodecustomer'] == '' && $res[0]['kodesupplier'] == '') {
        exit('Warning !! Terdapat tiket dengan Kode Supplier dan Kode Customer yang kosong ');
    }

    if ($res[0]['kodecustomer'] != '') {
        $whre .= "and a.tujuan='" . $res[0]['kodecustomer'] . "'";
    }
    if ($res[0]['kodesupplier'] != '') {
        $whre .= "and a.lokasi='" . $res[0]['kodesupplier'] . "'";
    }
    // echo $str1="SELECT * from pmn_5ongkosangkutht a left join pmn_5ongkosangkutdt b on a.id=b.id where 1=1 ".$whre."
    // and komoditi ='".$komoditi."'
    $whre .= "and tanggalawal >='" . tanggalsystemn($tgl1) . "'
	 and tanggalsampai <='" . tanggalsystemn($tgl2) . "'";
    $str1 = "SELECT * from pmn_5ongkosangkutht a left join pmn_5ongkosangkutdt b on a.id=b.id where 1=1 " . $whre . "
	and komoditi ='" . $komoditi . "' and kodeunit='" . $kodeunit . "' and a.posting='1' and b.posting='1'
  	ORDER BY tanggalawal DESC limit 1";

    $res1 = fetchData($str1);

    # Permintaan dan kebijakan WTL (Info dari Pak Ilham)
    # Cek Jika tidak ada data
    # Mungkin karena tanggal sampai berbeda tahun dan beda range tanggal
    # Maka lebihkan 3 tahun
    if (count($res1) <= 0) {
        $tgl2bsk = strtotime('+3 years', strtotime($tgl2));
        $tgl2bsk = date('Y-m-d', $tgl2bsk);

        $whre = '';

        if ($res[0]['kodecustomer'] != '') {
            $whre .= "and tujuan='" . $res[0]['kodecustomer'] . "'";
        }
        if ($res[0]['kodesupplier'] != '') {
            $whre .= "and lokasi='" . $res[0]['kodesupplier'] . "'";
        }

        $whre .= "and tanggalawal >='" . tanggalsystemn($tgl1) . "' and tanggalsampai <='" . $tgl2bsk . "'";

        $str1 = "SELECT * from pmn_ongkosangkut_vw a left join pmn_5ongkosangkutdt b on a.id=b.id where 1=1 " . $whre . "
		and komoditi ='" . $komoditi . "' and kodeunit='" . $kodeunit . "' and a.posting='1' and b.posting='1'
		ORDER BY tanggalawal DESC limit 1";

        $res1 = fetchData($str1);
    }
    // echo $str1."<br/>";

    $ongkosan = $res1[0]['harga'];
    $nourutharga = $res1[0]['nourut'];

    $dataharga = array('nourut' => $nourutharga, 'ongkosan' => $ongkosan);
    return $dataharga;
}

function getOngkosAngkut($kodeunit, $lokasi, $tanggal, $komoditi, $trpcode)
{
    $query = "SELECT hargapotongan,harga,id AS nourut FROM pmn_ongkosangkut_vw WHERE trpcode='{$trpcode}' and  kodeunit = '{$kodeunit}' {$lokasi} AND date_range = '{$tanggal}' AND komoditi = '{$komoditi}' and posting = '1' ORDER BY nourut_dt DESC";
    // exit('warning: '.$query);
    $harga = fetchdata($query);

    $sql = "SELECT namabarang FROM log_5masterbarang where kodebarang={$komoditi} ";
    $res = fetchData($sql);
    $nmbarang = $res[0]['namabarang'];
    if ($harga < 1) {
        exit("Warning, Harga ongkos angkut untuk kodeunit '{$kodeunit}' dengan komoditi '{$nmbarang}' pada tanggal '" . tanggalnormal($tanggal) . "' tidak ada atau belum di posting");
        return false;
    }
    return $harga;
}


// function getRpPotongan($kodeunit, $lokasi, $tanggal, $komoditi)
// {
//     $query = "SELECT hargapotongan,id AS nourut FROM pmn_ongkosangkut_vw WHERE kodeunit = '{$kodeunit}' {$lokasi} AND date_range = '{$tanggal}' AND komoditi = '{$komoditi}' and posting = '1'";
//     //    exit("Warning,$query");
//     $hargapotongan = fetchdata($query);

//     // $sql = "SELECT namabarang FROM log_5masterbarang where kodebarang={$komoditi} ";
//     // $res = fetchData($sql);
//     // $nmbarang = $res[0]['namabarang'];
//     // if (count($hargapotongan) < 1) {
//     //     exit("Warning, Harga ongkos angkut untuk kodeunit '{$kodeunit}' dengan komoditi '{$nmbarang}' pada tanggal '" . tanggalnormal($tanggal) . "' tidak ada atau belum di posting");
//     //     return false;
//     // }
//     return $hargapotongan;
// }
