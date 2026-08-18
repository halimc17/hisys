<?php
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/utilities.php');
require_once('lib/terbilang.php');
include_once('lib/zFunction.php');
require_once('dompdf/autoload.inc.php');

use Dompdf\Dompdf;

$method         = checkPostGet('method', '');
$karyawanid     = checkPostGet('karyawanid', '');
$tglmasuk       = checkPostGet('tglmasuk', '');
$tglberhenti    = tanggalsystem(checkPostGet('tglberhenti', ''));
$jenis        = checkPostGet('jenis', '');
$masakerjatahun      = checkPostGet('masakerjatahun', '');
$masakerjabulan      = checkPostGet('masakerjabulan', '');
$masakerjahari      = checkPostGet('masakerjahari', '');
$gapok          = checkPostGet('gajipokok', '');
$tunjangan      = checkPostGet('tunjanganjabatan', '');
$tot_sblm_pajak = checkPostGet('tot_sblm_pajak', '');
$kodeunit       = checkPostGet('kodeunit', '');
$table          = "";
$notransaksi = checkPostGet('notransaksi', '');
$jlh = checkPostGet('jlh', '');
$jab      = getPostingJabatan('pesangon');
$stsapprv = array('0' => $_SESSION['lang']['wait_approval'], '1' => $_SESSION['lang']['disetujui'], '2' => $_SESSION['lang']['ditolak'], '3' => 'Dikoreksi', '9' => $_SESSION['lang']['pengajuan']);
$jenispersetujuan = 'PSG';
$notransaksi = checkPostGet('notransaksi', '');
$noid = checkPostGet('noid', '');
//Umar
$param = $_POST;
if (count($param) == 0) {
    $param = $_GET;
}
//End Umar

switch ($method) {
    case 'getgapoktunjangan':
        //   $hkefektif=array();
        //   $hkefektifop=array();
        //   $str = "select a.periode,a.hkefektif,b.idkomponen,b.operator  from ".$dbname.".sdm_hk_efektif a  
        //   left join ".$dbname.".sdm_hk_efektifdt b on a.periode=b.periode 
        //   where a.periode ='".substr(tanggalsystem($param['tglberhenti']), 0,6)."'"; 
        //   $res = fetchdata($str);
        //   foreach ($res as $bar) {
        //         $hkefektif[$bar['idkomponen']]=$bar['hkefektif'];
        //         $hkefektifop[$bar['idkomponen']]=$bar['operator'];
        //   }
        //print_r($hkefektifop);
        //exit('WARNING : SEDANG DALAM PERBAIKAN');
        $sgapok = "select jumlah ,idkomponen from " . $dbname . ".sdm_5gajipokok where karyawanid='" . $karyawanid . "' and tahun='" . substr(tanggalsystemn($param['tglberhenti']), 0, 7) . "' and idkomponen in (1) group by idkomponen";
        $r = fetchData($sgapok);
        $upah = 0;
        foreach ($r as $key => $val) {
            if (isset($hkefektifop[$val['idkomponen']])) {
                if ($hkefektifop[$val['idkomponen']] == 1) {
                    $upah += ($val['jumlah'] * 25);
                } else {
                    $upah += $val['jumlah'];
                }
            } else {
                $upah += $val['jumlah'];
            }
        }


        $upah        = number_format($upah, 0);

        echo $upah;
        break;
    case 'getkodeunit':
        $upah      = 0;

        $str = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $karyawanid . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
        $unit = $bar->lokasitugas;
        $tglmasuk = $bar->tanggalmasuk;
        $tglberhenti = $bar->tanggalkeluar;

        //   $hkefektif=array();
        //   $hkefektifop=array();
        //   $str = "select a.periode,a.hkefektif,b.idkomponen,b.operator  from ".$dbname.".sdm_hk_efektif a  
        //   left join ".$dbname.".sdm_hk_efektifdt b on a.periode=b.periode 
        //   where a.periode ='".substr(str_replace('-', '',$tglberhenti), 0,6)."'"; 
        //   $res = fetchdata($str);
        //   foreach ($res as $bar) {
        //         $hkefektif[$bar['idkomponen']]=$bar['hkefektif'];
        //         $hkefektifop[$bar['idkomponen']]=$bar['operator'];
        //   }

        $sgapok = "select jumlah,idkomponen from " . $dbname . ".sdm_5gajipokok where karyawanid='" . $karyawanid . "' and tahun='" . substr($tglberhenti, 0, 7) . "' and idkomponen in (1) group by idkomponen";
        $r = fetchData($sgapok);
        $upah = 0;
        foreach ($r as $key => $val) {
            if (isset($hkefektifop[$val['idkomponen']])) {
                if ($hkefektifop[$val['idkomponen']] == 1) {
                    $upah += ($val['jumlah'] * 25);
                } else {
                    $upah += $val['jumlah'];
                }
            } else {
                $upah += $val['jumlah'];
            }
        }


        $upah = number_format($upah, 0);

        echo $unit . "###" . $tglmasuk . "###" . $upah;
        break;

    //Umar
    case 'insertNew':
        $owlPDO->beginTransaction();

        if ($param['nosurat'] == '') {
            /** Auto Numbering :
             * 1. Number
             * 2. "PB"
             * 3. Initial PT
             * 4. "HRD"
             * 5. Bulan bentuk Romawi IIV
             * 6. Tahun
             * Ex: 001/PB/GAL/HRD/VII/2017
             **/

            $num = 1;
            $PT  = "PT";
            $sPT = "SELECT kodeorganisasi FROM " . $dbname . ".datakaryawan WHERE karyawanid = '" . $param['karyawanid'] . "'";
            $rPT = fetchData($sPT);

            if (count($rPT) > 0) {
                $PT = $rPT[0]['kodeorganisasi'];
            }

            $bulan  = romawi(date('m', strtotime($param['tanggal'])));
            $tahun  = date('Y', strtotime($param['tanggal']));
            $snum   = "select MAX(SUBSTRING(nosurat,1,3)) as num from " . $dbname . ".sdm_pesangon where nosurat like '%/PB/" . $PT . "/" . $bulan . "/" . $tahun . "'";
            $rnum   = fetchData($snum);
            if (count($rnum) > 0) {
                $num = intval($rnum[0]['num']) + $num;
            }

            $lastNumber         = str_pad($num, 3, "0", STR_PAD_LEFT);
            $param['nosurat']   = $lastNumber . "/PB/" . $PT . "/" . $bulan . "/" . $tahun;
        }

        $data = array(
            'nosurat' => $param['nosurat'],
            'karyawanid' => $param['karyawanid'],
            'kodeunit' => $param['kodeunit'],
            'tanggal' => tanggalsystem($param['tanggal']),
            'tanggalmasuk' => $param['tglmasuk'],
            'tanggalberhenti' => tanggalsystem($param['tglberhenti']),
            'masakerjatahun' => $param['masakerjatahun'],
            'masakerjabulan' => $param['masakerjabulan'],
            'masakerjahari' => $param['masakerjahari'],
            'upahterakhir' => $param['gajipokok'],
            'tunjanganjabatan' => $param['tunjanganjabatan'],
            'pupah1' => $param['hasilKaliGaji1'],
            'pupah2' => $param['hasilKaliGaji2'],
            'xpp' => $param['xPP'],
            'jenispesangon' => $param['jenissk'],
            'jenisphk' => $param['jenisPHK'],
            'judulphk' => $param['judulPHK'],
            'p1562' => $param['pengaliPesangon'],
            'jumlahp1562' => $param['totalPesangon'],
            'p1563' => $param['pengaliPMK'],
            'jumlahp1563' => $param['totalPMK'],
            'p1564a' => $param['pengaliCuti'],
            'jumlahp1564a' => $param['totalCuti'],
            'p1564b' => $param['pengaliPisah'],
            'jumlahp1564b' => $param['totalPisah'],
            'p1564c' => $param['pengaliPP'],
            'jumlahp1564c' => $param['totalPP'],
            'jumlahsebelumpajak' => $param['totalPHK'],
            'totalterima' => ($param['totalPHK'] - $param['totalPajak']),
            'posting' => 0,
            'statuspersetujuan' => 1,
            'createby' => $user,
            'createtime' => $datetime
        );

        $cols = array();
        foreach ($data as $key => $row) {
            $cols[] = $key;
        }

        $str = insertQuery($dbname, 'sdm_pesangon', $data, $cols);

        try {
            $owlPDO->exec($str);
            $owlPDO->commit();
        } catch (\Throwable $th) {
            $owlPDO->rollback();
        }
        break;

    case 'updateNew':
        $owlPDO->beginTransaction();

        $data = array(
            'karyawanid' => $param['karyawanid'],
            'kodeunit' => $param['kodeunit'],
            'tanggal' => tanggalsystem($param['tanggal']),
            'tanggalberhenti' => tanggalsystem($param['tglberhenti']),
            'masakerjatahun' => $param['masakerjatahun'],
            'masakerjabulan' => $param['masakerjabulan'],
            'masakerjahari' => $param['masakerjahari'],
            'upahterakhir' => $param['gajipokok'],
            'tunjanganjabatan' => $param['tunjanganjabatan'],
            'jenispesangon' => $param['jenissk'],
            'jenisphk' => $param['jenisPHK'],
            'judulphk' => $param['judulPHK'],
            'xpp' => $param['xPP'],
            'p1562' => (($param['pengaliPesangon'] != '') ? $param['pengaliPesangon'] : 0),
            'jumlahp1562' => (($param['totalPesangon'] != '') ? $param['totalPesangon'] : 0),
            'p1563' => (($param['pengaliPMK'] != '') ? $param['pengaliPMK'] : 0),
            'jumlahp1563' => (($param['totalPMK'] != '') ? $param['totalPMK'] : 0),
            'p1564a' => (($param['pengaliCuti'] != '') ? $param['pengaliCuti'] : 0),
            'jumlahp1564a' => (($param['totalCuti'] != '') ? $param['totalCuti'] : 0),
            'p1564b' => (($param['pengaliPisah'] != '') ? $param['pengaliPisah'] : 0),
            'jumlahp1564b' => (($param['totalPisah'] != '') ? $param['totalPisah'] : 0),
            'p1564c' => $param['pengaliPP'],
            'jumlahp1564c' => $param['totalPP'],
            'jumlahsebelumpajak' => $param['totalPHK'],
            'totalterima' => ($param['totalPHK'] - $param['totalPajak'])
        );

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";

        $where  = "nosurat='" . $param['nosurat'] . "'";
        $str    = updateQuery($dbname, 'sdm_pesangon', $data, $where);


        try {
            $owlPDO->exec($str);
            $owlPDO->commit();
        } catch (\Throwable $th) {
            $owlPDO->rollback();
        }
        break;

    case 'form_ajukan':
        /* Lokasi */
        //  $sLok = selectQuery($dbname, $table, "lokasikerja,jeniskebutuhan", "notransaksi = '".$notransaksi."'");
        //  $qLok = fetchData($sLok);
        $lokasi = " AND kodeunit = '" . $_SESSION['empl']['lokasitugas'] . "'";

        $where = '';

        /* Cek Perdepartemen */
        $sStr = selectQuery($dbname, "setup_approval", "COUNT(departemen) AS perdepartemen", "jenispersetujuan = '" . $jenispersetujuan . "' AND departemen = '" . $_SESSION['empl']['bagian'] . "' " . $lokasi . "");
        $qStr = fetchData($sStr);
        $perdepartemen = $qStr[0]['perdepartemen'];
        if ($perdepartemen > 0) {
            $where .= " AND departemen = '" . $_SESSION['empl']['bagian'] . "'";
        }

        /* Cek Pergolongan */
        $sStr = selectQuery($dbname, "setup_approval", "COUNT(golongan) AS pergolongan", "jenispersetujuan = '" . $jenispersetujuan . "' AND golongan = '" . $_SESSION['empl']['kodegolongan'] . "' " . $lokasi . "");
        $qStr = fetchData($sStr);
        $pergolongan = $qStr[0]['pergolongan'];
        if ($pergolongan > 0) {
            $where .= " AND golongan = '" . $_SESSION['empl']['kodegolongan'] . "'";
        }

        /* Cek Per Karyawanid */
        $sStr = selectQuery($dbname, "setup_approval", "COUNT(karyawanid) AS perkaryawanid", "jenispersetujuan = '" . $jenispersetujuan . "' AND karyawanid = '" . $_SESSION['standard']['userid'] . "' " . $lokasi . "");
        $qStr = fetchData($sStr);
        $perkaryawanid = $qStr[0]['perkaryawanid'];
        if ($perkaryawanid > 0) {
            $where .= " AND karyawanid = '" . $_SESSION['standard']['userid'] . "'";
        }

        // Setup Approval
        $sApp = selectQuery($dbname, "setup_approval", "*", "jenispersetujuan = '" . $jenispersetujuan . "' " . $lokasi . " " . $where . "", "level");
        $qApp = fetchData($sApp);

        if (count($qApp) <= 0) {
            exit("warning : Silahkan tambahkan nama penyetuju melalui menu : Setup - Persetujuan");
        }

        // Input Data Approval
        $optApp = array();
        foreach ($qApp as $apv) {
            $optApp[$apv['level']][] = $apv['karyawanid'];
        }

        // Membuat Select Option
        $nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
        $tab = '';
        $jlh = 0;
        foreach ($optApp as $level => $user) {
            /* Kode ini apabila menampilkan hanya 1 approval */
            if ($jlh > 0) {
                break;
            }
            /* akhir kode */

            $opt = '';
            foreach ($user as $karyawanid) {
                $opt .= "<option value='" . $karyawanid . "'>" . $nmkar[$karyawanid] . "</option>";
            }
            if ($opt != '') {
                $jlh++;
                $tab .= "<tr class='rowcontent'>
                             <td>Approval ke-" . $level . "</td>
                             <td width='5px'>:</td>
                             <td><select id='kepada" . $level . "' style='width: 99%';>" . $opt . "</select></td>
                         </tr>";
            }
        }

        $tab .= "<input hidden id=jlh value='" . $jlh . "'>";
        $tab .= "<input hidden id=notransaksi_ajukan value='" . $notransaksi . "'>";
        $tab .= "<tr>
                     <td></td>
                     <td></td>
                     <td><button id='tomboldetail' class='mybutton' onclick=\"ajukan()\">" . $_SESSION['lang']['diajukan'] . "</button></td>
                 </tr>";
        echo $tab;
        break;

    case 'ajukan':
        /* cek apabila user membuka 2 tab */
        $sAppr = selectQuery($dbname, "sdm_pesangon", "statuspersetujuan", "noid = '" . $notransaksi . "'");
        $qAppr = fetchData($sAppr);
        $stts = [1, 2, 9];
        if (in_array($qAppr[0]['statuspersetujuan'], $stts)) {
            exit("warning : Transaksi sudah diposting!");
        }

        /* Ambil dari lokasi tugas transaksi */
        $sKar = selectQuery($dbname, "sdm_pesangon", "karyawanid", "noid = '" . $notransaksi . "'");
        $qKar = fetchData($sKar);
        $karyawanid = $qKar[0]['karyawanid'];

        $sDat = selectQuery($dbname, "datakaryawan", "lokasitugas", "karyawanid = '" . $karyawanid . "'");
        $qDat = fetchData($sDat);
        $karTugas = $qDat[0]['lokasitugas'];

        /* Commment aja kalau ga butuh lokasi tugas dari pengajuan */
        $lokTugasPengaju = " AND kodeunit = '" . $karTugas . "'";

        /* Error jika Penyetuju tidak diinput */
        if ($jlh == 0) {
            exit("Warning : Isikan nama penyetuju");
        }

        /* Dapatkan Username Persetujuan */
        $appr = array();
        for ($lev = 1; $lev <= $jlh; $lev++) {
            $appr[$lev] = checkPostGet("kepada" . $lev . "", '');/* Ambil per masing-masing user approval */
            $sApp = selectQuery($dbname, "setup_approval", "*", "jenispersetujuan='" . $jenispersetujuan . "' AND level='" . $lev . "' " . $lokTugasPengaju . "");
            $qApp = fetchData($sApp);

            if (count($qApp) > 0) {
                $tipeApp = $qApp[0]['tipe'];
                $departemenApp = $qApp[0]['departemen'];
                $tipekaryawanApp = $qApp[0]['tipekaryawan'];
                $jabatanApp = $qApp[0]['jabatan'];

                $data = array(
                    'notransaksi' => $notransaksi,
                    'jenispersetujuan' => $jenispersetujuan,
                    'level' => $lev,
                    'status' => '0',
                );

                if ($tipeApp == '1') {
                    if ($departemenApp != '') {
                        $sDep = selectQuery($dbname, "datakaryawan", "*", "bagian = '" . $departemenApp . "'");
                        $qDep = fetchData($sDep);

                        foreach ($qDep as $kar) {
                            $data['karyawanid'] = $kar['karyawanid'];
                            $sIns = insertQuery($dbname, 'approval', $data, array_keys($data));
                            $owlPDO->exec($sIns);
                        }
                    }
                    if ($tipekaryawanApp != '') {
                        $sTKR = selectQuery($dbname, "datakaryawan", "*", "bagian = '" . $tipekaryawanApp . "'");
                        $qTKR = fetchData($sTKR);

                        foreach ($qTKR as $kar) {
                            $data['karyawanid'] = $kar['karyawanid'];
                            $sIns = insertQuery($dbname, 'approval', $data, array_keys($data));
                            $owlPDO->exec($sIns);
                        }
                    }
                    if ($jabatanApp != '') {
                        $sJab = selectQuery($dbname, "datakaryawan", "*", "bagian = '" . $jabatanApp . "'");
                        $qJab = fetchData($sJab);

                        foreach ($qJab as $kar) {
                            $data['karyawanid'] = $kar['karyawanid'];
                            $sIns = insertQuery($dbname, 'approval', $data, array_keys($data));
                            $owlPDO->exec($sIns);
                        }
                    }
                } else {
                    if ($appr[$lev] != '') {
                        $mokarid = makeOption($dbname, 'user', 'namauser,karyawanid');
                        $data['karyawanid'] = $mokarid[$appr[$lev]];
                        $sIns = insertQuery($dbname, 'approval', $data, array_keys($data));
                        try {
                            $owlPDO->exec($sIns);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                }
            }
        }

        /* Update Status persetujuan di transaksi */
        $data = array(
            'statuspersetujuan' => 9,
        );
        $sUpt = updateQuery($dbname, 'sdm_pesangon', $data, "noid = '" . $notransaksi . "'");
        try {
            $owlPDO->exec($sUpt);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    //End Umar

    case 'getmasakerja':

        $tgl1   = new DateTime($tglmasuk);
        $tgl2   = new DateTime($tglberhenti);
        $jarak  = $tgl2->diff($tgl1);

        $masakerjatahun = addZero($jarak->y, 2);
        $masakerjabulan = addZero($jarak->m, 2);
        $masakerjahari  = addZero($jarak->d, 2);

        echo $masakerjatahun . "###" . $masakerjabulan . "###" . $masakerjahari . "###" . "$tglmasuk" . "###" . $tglberhenti;
        break;

    case 'editTable':
        $skodept = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $karyawanid . "'";
        $rkodept = $owlPDO->query($skodept) or die(print " Gagal: " . PDOException::getMessage());
        $rkodept->setFetchMode(PDO::FETCH_OBJ);
        $bkodept = $rkodept->fetch();
        if (!$bkodept->kodeorganisasi) {
            exit("ERROR: Data Karyawan not Found!");
        }
        if (!$masakerjatahun) {
            exit("ERROR: Date not Found!");
        }

        $param['tglmasuk'] = $bkodept->tanggalmasuk;



        $query  = "SELECT * FROM $dbname.sdm_pesangon WHERE noid = '" . $param['noid'] . "'";
        $result = fetchData($query, 'OBJECT');
        foreach ($result as $key => $value) {
            $noid = $value->noid;
            $karyawanid = $value->karyawanid;
            $pihakpertama = $value->pihakpertama;
            $kodeunit = $value->kodeunit;
            $tanggal = $value->tanggal;
            $tanggalberhenti = $value->tanggalberhenti;
            $tanggalmasuk = $value->tanggalmasuk;
            $masakerjatahun = $value->masakerjatahun;
            $masakerjabulan = $value->masakerjabulan;
            $masakerjahari = $value->masakerjahari;
            $upahterakhir = $value->upahterakhir;
            $jenis = $value->jenis;
            $textuangpisah = $value->textuangpisah;
            $uangpisah = $value->uangpisah;
            $textupmk = $value->textupmk;
            $upmk = $value->upmk;
            $textcuti = $value->textcuti;
            $cutitahunan = $value->cutitahunan;
            $pembagigajicuti = $value->pembagigajicuti;
            $rupiahcutitahunan = $value->rupiahcutitahunan;
            $textkesehatan = $value->textkesehatan;
            $proporsikesehatan = $value->proporsikesehatan;
            $pengalikesehatan = $value->pengalikesehatan;
            $rupiahkesehatan = $value->rupiahkesehatan;
            $tambahan1 = $value->tambahan1;
            $tambahan2 = $value->tambahan2;
            $tambahan3 = $value->tambahan3;
            $tambahan4 = $value->tambahan4;
            $nilaitambahan1 = $value->nilaitambahan1;
            $nilaitambahan2 = $value->nilaitambahan2;
            $nilaitambahan3 = $value->nilaitambahan3;
            $nilaitambahan4 = $value->nilaitambahan4;
            $rupiahtotal1 = $value->rupiahtotal1;
            $jenispengembalian1 = $value->jenispengembalian1;
            $jenispengembalian2 = $value->jenispengembalian2;
            $jenispengembalian3 = $value->jenispengembalian3;
            $jenispengembalian4 = $value->jenispengembalian4;
            $jenispengembalian5 = $value->jenispengembalian5;
            $jenispengembalian6 = $value->jenispengembalian6;
            $nilaijenispengembalian1 = $value->nilaijenispengembalian1;
            $nilaijenispengembalian2 = $value->nilaijenispengembalian2;
            $nilaijenispengembalian3 = $value->nilaijenispengembalian3;
            $nilaijenispengembalian4 = $value->nilaijenispengembalian4;
            $nilaijenispengembalian5 = $value->nilaijenispengembalian5;
            $nilaijenispengembalian6 = $value->nilaijenispengembalian6;
            $nilaipajak = $value->nilaipajak;
            $rupiahtotalpotongan = $value->rupiahtotalpotongan;
            $rupiahditerima = $value->rupiahditerima;
            $posting = $value->posting;
            $createby = $value->createby;
            $createtime = $value->createtime;
            $updatetime = $value->updatetime;
            $updateby = $value->updateby;
        }


        //Ambil detail dari jenis
        $query  = "SELECT * FROM $dbname.sdm_5jenispesangon WHERE jenis = '" . $jenis . "'";
        $result = fetchData($query, 'OBJECT');
        $statuspajak = $result[0]->statuspajak;
        $maxpajak = $result[0]->maxpajak;


        $table .= "<table border='0' style='width:80%;margin-top:20px'>";
        $table .= "<tr>";
        $table .= "<td colspan=4><b>A.Identitas</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>1.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbsp" . $_SESSION['lang']['nama'] . "</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $bkodept->namakaryawan . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>2.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbsp" . $_SESSION['lang']['nik'] . "</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $bkodept->nik . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>3.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspJabatan</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . getNamaJabatan($bkodept->kodejabatan) . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>5.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspDivisi</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . getNamaOrg($bkodept->lokasitugas) . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>6.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspTempat/Tanggal Lahir</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $bkodept->tempatlahir . "/" . tanggalnormal($bkodept->tanggallahir) . "</td>";
        $table .= "</tr>";
        $tgl1   = new DateTime($bkodept->tanggallahir);
        $tgl2   = new DateTime($tanggalberhenti);
        $jarak  = $tgl2->diff($tgl1);

        $usiatahun = addZero($jarak->y, 2);
        $usiabulan = addZero($jarak->m, 2);
        $usiahari  = addZero($jarak->d, 2);
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>7.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspUsia</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $usiatahun . " Tahun " . $usiabulan . " Bulan " . $usiahari . " Hari</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>8.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspTanggal Masuk Kerja</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . tanggalnormal($bkodept->tanggalmasuk) . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>9.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspTanggal Keluar Kerja</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . tanggalnormal($tanggalberhenti) . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>10.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspMasa Kerja</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $masakerjatahun . " Tahun " . $masakerjabulan . " Bulan " . $masakerjahari . " Hari</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>11.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspUpah Terakhir</td>";
        $table .= "<td style='width:5px;vertical-align:top'>:</td>";
        $table .= "<td>&nbspRp." . number_format(($upahterakhir), 0) . "</td>";
        $table .= "</tr>";
        $arrjenis = makeOption($dbname, 'sdm_5jenispesangon', 'kode,jenis');
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>10.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspAlasan</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $arrjenis[$jenis] . "</td>";
        $table .= "</tr>";
        $table .= "</table>";

        $table .= "<table border='0' style='margin-top:15px;width:80%'>";
        $table .= "<tr>";
        $table .= "<td colspan=3><b>B.Kompensasi</b></td>";
        $table .= "</tr>";
        $norutx = 1;
        $total1 = 0;
        $totalupajak = 0;
        if ($textuangpisah != '') {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Uang Pisah</b></td>";
            $table .= "</tr>";
            $table .= "<tr>";

            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' id='textuangpisah'>" . $textuangpisah . "</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input style='width:190px;' type=text class=myinputtextnumber id=uangpisah value='" . number_format($uangpisah, 0) . "' disabled></td>";
            $table .= "</tr>";
            $total1 += $uangpisah;
            $totalupajak += $uangpisah;
            $norutx++;
        }

        if ($textupmk != '') {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Uang Penghargaan Masa Kerja</b></td>";
            $table .= "</tr>";
            $table .= "<tr>";

            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' id='textupmk'>" . $textupmk . "</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input style='width:190px;' type=text class=myinputtextnumber id=upmk value='" . number_format($upmk, 0) . "' disabled></td>";
            $table .= "</tr>";
            $total1 += $upmk;
            $totalupajak += $upmk;
            $norutx++;
        }


        $table .= "<tr>";
        $table .= "<td colspan=3><b>C.Penggantian Hak</b></td>";
        $table .= "</tr>";
        $norutx = 1;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Cuti Tahunan yang belum gugur</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top'>Rp." . number_format(($upahterakhir), 0) . " x <input id='cutitahunan' type='text' class='myinputtextnumber' style='width:20px;' value='" . $cutitahunan . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalcuti('" . $bkodept->kodeorganisasi . "')\"/> / <input id='pembagigajicuti' type='text' class='myinputtextnumber' style='width:20px;' value='" . $pembagigajicuti . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalcuti('" . $bkodept->kodeorganisasi . "')\"/></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input style='width:190px;' type=text class=myinputtextnumber id=rupiahcutitahunan value='" . number_format((($upahterakhir) * $cutitahunan / $pembagigajicuti), 0) . "' disabled><input id=gajicuti value='" . ($upahterakhir) . "' hidden></td>";
        $table .= "</tr>";
        $total1 += (($upahterakhir) * $cutitahunan / $pembagigajicuti);
        $norutx++;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Tunjangan Kesehatan</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top'>Rp." . number_format(($upahterakhir), 0) . " x <input id='proporsikesehatan' type='text' class='myinputtextnumber' style='width:20px;' value='" . $proporsikesehatan . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalkesehatan('" . $bkodept->kodeorganisasi . "')\"/> / 12 x <input id='pengalikesehatan' type='text' class='myinputtextnumber' style='width:20px;' value='" . $pengalikesehatan . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalkesehatan('" . $bkodept->kodeorganisasi . "')\"/></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input style='width:190px;' type=text class=myinputtextnumber id=rupiahkesehatan value='" . number_format((($upahterakhir) * $proporsikesehatan / 12 * $pengalikesehatan), 0) . "' disabled><input id=gajikesehatan value='" . ($upahterakhir) . "' hidden></td>";
        $table .= "</tr>";
        $total1 += (($upahterakhir) * $proporsikesehatan / 12 * $pengalikesehatan);

        $norutx++;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='tambahan1' type='text' class='myinputtext' style='width:200px;' value='" . $tambahan1 . "' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaitambahan1' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format(($nilaitambahan1), 2) . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalnilaitambahan();z.numberFormat('nilaitambahan1',2);\"/></td>";
        $table .= "</tr>";
        $total1 += $nilaitambahan1;

        $norutx++;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='tambahan2' type='text' class='myinputtext' style='width:200px;' value='" . $tambahan2 . "' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaitambahan2' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format(($nilaitambahan2), 2) . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalnilaitambahan();z.numberFormat('nilaitambahan2',2);\"/></td>";

        $total1 += $nilaitambahan2;
        $table .= "</tr>";

        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx++ . ".</b><input id='tambahan3' type='text' class='myinputtext' style='width:200px;' value='" . $tambahan3 . "' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaitambahan3' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format(($nilaitambahan3), 2) . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalnilaitambahan();z.numberFormat('nilaitambahan2',2);\"/></td>";

        $total1 += $nilaitambahan3;
        $table .= "</tr>";

        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx++ . ".</b><input id='tambahan4' type='text' class='myinputtext' style='width:200px;' value='" . $tambahan4 . "' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaitambahan4' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format(($nilaitambahan4), 2) . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalnilaitambahan();z.numberFormat('nilaitambahan2',2);\"/></td>";

        $total1 += $nilaitambahan4;
        $table .= "</tr>";

        $table .= "<tr>";
        $table .= "<td colspan=2 align=right></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'><b>____________________________</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td colspan=2 align=right></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'><b>TOTAL</b>&nbsp&nbspRp.<input style='width:190px;' type=text class=myinputtextnumber id=rupiahtotal1 value='" . number_format($rupiahtotal1, 0) . "' disabled></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td colspan=3><b>D.Kewajiban/Pengembalian</b></td>";
        $table .= "</tr>";
        $norutx = 1;
        $totalnilaijenispengembalian = 0;
        if ($jenispengembalian1 == '') {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian1' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian1' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian1',2);\"/></td>";
            $table .= "</tr>";
            $norutx++;
        } else {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian1' type='text' class='myinputtext' style='width:200px;' value='" . $jenispengembalian1 . "' onkeypress='return tanpa_kutip(event);'/></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian1' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format($nilaijenispengembalian1) . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian1',2);\"/></td>";
            $table .= "</tr>";
            $totalnilaijenispengembalian += $nilaijenispengembalian1;
            $norutx++;
        }

        if ($jenispengembalian2 == '') {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian2' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
            $table .= "</tr>";
            $table .= "<tr>";

            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian2' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian2',2);\"/></td>";
            $table .= "</tr>";
            $norutx++;
        } else {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian2' type='text' class='myinputtext' style='width:200px;' value='" . $jenispengembalian2 . "' onkeypress='return tanpa_kutip(event);'/></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian2' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format($nilaijenispengembalian2) . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian2',2);\"/></td>";
            $table .= "</tr>";
            $totalnilaijenispengembalian += $nilaijenispengembalian2;
            $norutx++;
        }

        if ($jenispengembalian3 == '') {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian3' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
            $table .= "</tr>";
            $table .= "<tr>";

            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian3' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian3',2);\"/></td>";
            $table .= "</tr>";
            $norutx++;
        } else {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian3' type='text' class='myinputtext' style='width:200px;' value='" . $jenispengembalian3 . "' onkeypress='return tanpa_kutip(event);'/></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian3' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format($nilaijenispengembalian3) . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian3',2);\"/></td>";
            $table .= "</tr>";
            $totalnilaijenispengembalian += $nilaijenispengembalian3;
            $norutx++;
        }


        if ($jenispengembalian4 == '') {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian4' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
            $table .= "</tr>";
            $table .= "<tr>";

            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian4' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian4',2);\"/></td>";
            $table .= "</tr>";
            $norutx++;
        } else {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian4' type='text' class='myinputtext' style='width:200px;' value='" . $jenispengembalian4 . "' onkeypress='return tanpa_kutip(event);'/></td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian4' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format($nilaijenispengembalian4) . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian4',2);\"/></td>";
            $table .= "</tr>";
            $totalnilaijenispengembalian += $nilaijenispengembalian4;
            $norutx++;
        }

        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian5' type='text' class='myinputtext' style='width:200px;' value='" . $jenispengembalian5 . "' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian5' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format($nilaijenispengembalian5) . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian5',2);\"/></td>";
        $table .= "</tr>";
        $totalnilaijenispengembalian += $nilaijenispengembalian5;

        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian6' type='text' class='myinputtext' style='width:200px;' value='" . $jenispengembalian6 . "' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian6' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format($nilaijenispengembalian6) . "' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian5',2);\"/></td>";
        $table .= "</tr>";
        $totalnilaijenispengembalian += $nilaijenispengembalian6;




        $table .= "<tr>";
        $table .= "<td colspan=3><b>E.Pajak</b></td>";
        $table .= "</tr>";
        $norutx = 1;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Pajak</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $wherexxx = '';
        if ($maxpajak != '0' and $maxpajak != '') {
            $wherexxx = " and persentase<'" . $maxpajak . "'";
        }

        $query       = "SELECT * FROM $dbname.sdm_5pajakpesangon WHERE kodept = '" . $bkodept->kodeorganisasi . "' and  penghasilan<'" . $totalupajak . "'" . $wherexxx . " order by penghasilan asc";
        //echo $query;
        $result      = fetchData($query);
        $jumlahres = count($result);
        $tambahan = 0;
        $nilaipajak = '';
        $noxzx = 1;
        if (count($result) > 0) {
            foreach ($result as $value) {
                if ($nilaipajak == '') {
                    $nilaipajak = $value['penghasilan'];
                    $tambahan += $nilaipajak * $value['persentase'];
                    //echo $value['penghasilan']." ke =".$noxzx.' =>'.($nilaipajak*$value['persentase']).'<br>';
                } else {
                    $tambahan += ($value['penghasilan'] - $nilaipajak) * $value['persentase'];
                    //echo $value['penghasilan'].'-'.$nilaipajak." ke =".$noxzx.' =>'.(($value['penghasilan']-$nilaipajak)*$value['persentase']).'<br>';
                    if ($noxzx <= $jumlahres) {
                        $nilaipajak = $nilaipajak + ($value['penghasilan'] - $nilaipajak);
                    }
                }
                $noxzx++;
            }
        }

        $wherexxx = '';
        if ($maxpajak != '0' and $maxpajak != '') {
            $wherexxx = " and persentase='" . $maxpajak . "'";
        } else {
            $wherexxx = " and  penghasilan>='" . $totalupajak . "'";
        }

        $query       = "SELECT * FROM $dbname.sdm_5pajakpesangon WHERE kodept = '" . $bkodept->kodeorganisasi . "' " . $wherexxx . " order by penghasilan asc limit 1";
        $result      = fetchData($query);
        $pajak = 0;
        if (count($result) > 0) {
            if ($nilaipajak == '') {
                $nilaipajak = 0;
            }
            foreach ($result as $value) {
                $pajak = (($totalupajak - $nilaipajak) * $value['persentase']) + $tambahan;
                // echo $value['persentase'].' ke =terakhir =>'.(($totalupajak-$nilaipajak)*$value['persentase']).'<br>';

            }
        }
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaipajak' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format($pajak, 0) . "' onkeypress='return angka_doang(event)' disabled/></td>";
        $table .= "</tr>";
        $norutx++;
        $table .= "<tr>";
        $table .= "<td colspan=2 align=right></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'><b>____________________________</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td colspan=2 align=right></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'><b>TOTAL POTONGAN</b>&nbsp&nbspRp.<input style='width:190px;' type=text class=myinputtextnumber id=rupiahtotalpotongan value='" . ($pajak + $totalnilaijenispengembalian) . "' disabled></td>";
        $table .= "</tr>";

        $table .= "<tr>";
        $table .= "<td colspan=2><b>F.DITERIMA</b></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='rupiahditerima' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format(($total1 - ($pajak + $totalnilaijenispengembalian))) . "' onkeypress='return angka_doang(event)' disabled/></td>";
        $table .= "</tr>";
        $table .= "</table>";



        $urutPerhitungan = 0;


        $table .= "<table border='0' style='margin-top:20px;width:95%;margin-bottom:20px'>";
        $table .= "<tr style='font-weight:bold;text-align:center'>";
        $table .= "<td>";
        $table .= "<button class='mybutton' onclick=\"savePesangonNew('update')\" >" . $_SESSION['lang']['save'] . "</button>";
        $table .= "<button class='mybutton' onclick='cancelIsiNew()'>" . $_SESSION['lang']['cancel'] . "</button>";
        $table .= "</td>";
        $table .= "</tr>";
        $table .= "</table>";



        echo $table;
        break;

    case 'createTable':
        $skodept = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $karyawanid . "'";
        $rkodept = $owlPDO->query($skodept) or die(print " Gagal: " . PDOException::getMessage());
        $rkodept->setFetchMode(PDO::FETCH_OBJ);
        $bkodept = $rkodept->fetch();
        if (!$bkodept->kodeorganisasi) {
            exit("ERROR: Data Karyawan not Found!");
        }
        if (!$masakerjatahun) {
            exit("ERROR: Date not Found!");
        }

        $masakerja = intval($masakerjatahun) . '.' . intval($masakerjabulan);
        $masakerja = doubleval($masakerja);
        $str = "select * from " . $dbname . ".sdm_5uangpisahpesangon where kodept='" . $bkodept->kodeorganisasi . "' and jenispesangon='" . $jenis . "' and masakerjadari<=" . $masakerja . " and masakerjasampai>=" . $masakerja . " ";
        $banyaknya = 0;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
        @$banyaknya = $bar->pengali;
        if ($banyaknya == '') {
            @$banyaknya = 0;
        }

        $sPenghargaan = "select * from " . $dbname . ".sdm_5uangpmkpesangon where kodept='" . $bkodept->kodeorganisasi . "' and jenispesangon='" . $jenis . "' and masakerjadari<=" . $masakerja . " and masakerjasampai>=" . $masakerja . " ";
        $penghargaan = 0;
        $rPenghargaan = $owlPDO->query($sPenghargaan) or die(print " Gagal: " . PDOException::getMessage());
        $rPenghargaan->setFetchMode(PDO::FETCH_OBJ);
        $hasil = $rPenghargaan->fetch();
        @$penghargaan = $hasil->pengali;
        if ($penghargaan == '') {
            @$penghargaan = 0;
        }



        //Penambah pengali pesangon
        $a = 0;
        $c = 0;

        $query  = "SELECT * FROM $dbname.sdm_5pengalitambahanpesangon WHERE jenispesangon = '" . $jenis . "'";
        $result = fetchData($query, 'OBJECT');
        foreach ($result as $key => $value) {
            $a = $value->uangpisah;
            $c = $value->upmk;
        }



        $gaji       = (($param['gajipokok'] != 'undefined') ? $param['gajipokok'] : (getGapok($karyawanid, '') * 25));
        $ttlgaji    = $gaji;


        //Ambil Tanggal Masuk
        $query  = "SELECT tanggalmasuk FROM $dbname.datakaryawan WHERE karyawanid = '" . $karyawanid . "'";
        $result = fetchData($query, 'OBJECT');
        $param['tglmasuk'] = $result[0]->tanggalmasuk;

        //Ambil detail dari jenis
        $query  = "SELECT * FROM $dbname.sdm_5jenispesangon WHERE kode = '" . $jenis . "'";
        $result = fetchData($query, 'OBJECT');
        $statuspajak = $result[0]->statuspajak;
        $maxpajak = $result[0]->maxpajak;

        // echo '<pre>';
        // print_r($taxPesangon);
        // echo '</pre>';

        $table .= "<table border='0' style='width:80%;margin-top:20px'>";
        $table .= "<tr>";
        $table .= "<td colspan=4><b>A.Identitas</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>1.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbsp" . $_SESSION['lang']['nama'] . "</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $bkodept->namakaryawan . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>2.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbsp" . $_SESSION['lang']['nik'] . "</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $bkodept->nik . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>3.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspJabatan</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . getNamaJabatan($bkodept->kodejabatan) . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>5.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspDivisi</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . getNamaOrg($bkodept->lokasitugas) . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>6.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspTempat/Tanggal Lahir</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $bkodept->tempatlahir . "/" . tanggalnormal($bkodept->tanggallahir) . "</td>";
        $table .= "</tr>";
        $tgl1   = new DateTime($bkodept->tanggallahir);
        $tgl2   = new DateTime($tglberhenti);
        $jarak  = $tgl2->diff($tgl1);

        $usiatahun = addZero($jarak->y, 2);
        $usiabulan = addZero($jarak->m, 2);
        $usiahari  = addZero($jarak->d, 2);
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>7.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspUsia</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $usiatahun . " Tahun " . $usiabulan . " Bulan " . $usiahari . " Hari</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>8.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspTanggal Masuk Kerja</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . tanggalnormal($bkodept->tanggalmasuk) . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>9.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspTanggal Keluar Kerja</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . tanggalnormal($tglberhenti) . "</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>10.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspMasa Kerja</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $masakerjatahun . " Tahun " . $masakerjabulan . " Bulan " . $masakerjahari . " Hari</td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>11.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspUpah Terakhir</td>";
        $table .= "<td style='width:5px;vertical-align:top'>:</td>";
        $table .= "<td>&nbspRp." . number_format(($gaji), 0) . "</td>";
        $table .= "</tr>";
        $arrjenis = makeOption($dbname, 'sdm_5jenispesangon', 'kode,jenis');
        $table .= "<tr>";
        $table .= "<td style='width:5px;text-align:center;vertical-align:top'>10.</td>";
        $table .= "<td style='width:80px;vertical-align:top'>&nbspAlasan</td>";
        $table .= "<td style='width:5px'>:</td>";
        $table .= "<td>&nbsp" . $arrjenis[$jenis] . "</td>";
        $table .= "</tr>";
        $table .= "</table>";

        $table .= "<table border='0' style='margin-top:15px;width:80%'>";
        $table .= "<tr>";
        $table .= "<td colspan=3><b>B.Kompensasi</b></td>";
        $table .= "</tr>";
        $norutx = 1;
        $total1 = 0;
        $totalupajak = 0;
        if ($banyaknya > 0 and $a > 0) {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Uang Pisah</b></td>";
            $table .= "</tr>";
            $table .= "<tr>";

            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' id='textuangpisah'>Rp." . number_format(($gaji), 0) . " x " . $a . " x " . $banyaknya . "</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input style='width:190px;' type=text class=myinputtextnumber id=uangpisah value='" . number_format((($gaji) * $a * $banyaknya), 0) . "' disabled></td>";
            $table .= "</tr>";
            $total1 += (($gaji) * $a * $banyaknya);
            $totalupajak += (($gaji) * $a * $banyaknya);
            $norutx++;
        } elseif ($banyaknya > 0) {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Uang Pisah</b></td>";
            $table .= "</tr>";
            $table .= "<tr>";

            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' id='textuangpisah'>Rp." . number_format(($gaji), 0) . " x " . $banyaknya . "</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input style='width:190px;' type=text class=myinputtextnumber id=uangpisah value='" . number_format((($gaji) * $banyaknya), 0) . "' disabled></td>";
            $table .= "</tr>";
            $total1 += (($gaji) * $banyaknya);
            $totalupajak += (($gaji) * $banyaknya);
            $norutx++;
        }

        if ($penghargaan > 0 and $c > 0) {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Uang Penghargaan Masa Kerja</b></td>";
            $table .= "</tr>";
            $table .= "<tr>";

            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' id='textupmk'>Rp." . number_format(($gaji), 0) . " x " . $c . " x " . $penghargaan . "</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input style='width:190px;' type=text class=myinputtextnumber id=upmk value='" . number_format((($gaji) * $c * $penghargaan), 0) . "' disabled></td>";
            $table .= "</tr>";
            $total1 += (($gaji) * $c * $penghargaan);
            $totalupajak += (($gaji) * $c * $penghargaan);
            $norutx++;
        } elseif ($penghargaan > 0) {
            $table .= "<tr>";
            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Uang Penghargaan Masa Kerja</b></td>";
            $table .= "</tr>";
            $table .= "<tr>";

            $table .= "<td style='width:20px;'>&nbsp</td>";
            $table .= "<td style='width:5px;text-align:left;vertical-align:top' id='textupmk'>Rp." . number_format(($gaji), 0) . " x " . $penghargaan . "</td>";
            $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input style='width:190px;' type=text class=myinputtextnumber id=upmk value='" . number_format((($gaji) * $penghargaan), 0) . "' disabled></td>";
            $table .= "</tr>";
            $total1 += (($gaji) * $penghargaan);
            $totalupajak += (($gaji) * $penghargaan);
            $norutx++;
        }


        $table .= "<tr>";
        $table .= "<td colspan=3><b>C.Penggantian Hak</b></td>";
        $table .= "</tr>";
        $norutx = 1;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Cuti Tahunan yang belum gugur</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top'>Rp." . number_format(($gaji), 0) . " x <input id='cutitahunan' type='text' class='myinputtextnumber' style='width:20px;' value='0' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalcuti('" . $bkodept->kodeorganisasi . "')\"/> / <input id='pembagigajicuti' type='text' class='myinputtextnumber' style='width:20px;' value='30' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalcuti('" . $bkodept->kodeorganisasi . "')\"/></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input style='width:190px;' type=text class=myinputtextnumber id=rupiahcutitahunan value='" . number_format((($gaji) * 0 / 30), 0) . "' disabled><input id=gajicuti value='" . ($gaji) . "' hidden></td>";
        $table .= "</tr>";
        $total1 += (($gaji) * 0 / 30);
        $norutx++;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Tunjangan Kesehatan</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:35px;text-align:left;vertical-align:top'>Rp." . number_format(($gaji), 0) . " x <input id='proporsikesehatan' type='text' class='myinputtextnumber' style='width:20px;' value='12' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalkesehatan('" . $bkodept->kodeorganisasi . "')\"/> / 12 x <input id='pengalikesehatan' type='text' class='myinputtextnumber' style='width:20px;' value='1.5' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalkesehatan('" . $bkodept->kodeorganisasi . "')\"/></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input style='width:190px;' type=text class=myinputtextnumber id=rupiahkesehatan value='" . number_format((($gaji) * 12 / 12 * 1.5), 0) . "' disabled><input id=gajikesehatan value='" . ($gaji) . "' hidden></td>";
        $table .= "</tr>";

        $norutx++;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='tambahan1' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaitambahan1' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalnilaitambahan();z.numberFormat('nilaitambahan1',2);\"/></td>";
        $table .= "</tr>";


        $norutx++;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx++ . ".</b><input id='tambahan2' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaitambahan2' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalnilaitambahan();z.numberFormat('nilaitambahan2',2);\"/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx++ . ".</b><input id='tambahan3' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaitambahan3' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalnilaitambahan();z.numberFormat('nilaitambahan3',2);\"/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx++ . ".</b><input id='tambahan4' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaitambahan4' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotalnilaitambahan();z.numberFormat('nilaitambahan4',2);\"/></td>";
        $table .= "</tr>";

        $total1 += (($gaji) * 12 / 12 * 1.5);
        $table .= "<tr>";
        $table .= "<td colspan=2 align=right></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'><b>____________________________</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td colspan=2 align=right></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'><b>TOTAL</b>&nbsp&nbspRp.<input style='width:190px;' type=text class=myinputtextnumber id=rupiahtotal1 value='" . number_format($total1, 0) . "' disabled></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td colspan=3><b>D.Kewajiban/Pengembalian</b></td>";
        $table .= "</tr>";
        $norutx = 1;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian1' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian1' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian1',2);\"/></td>";
        $table .= "</tr>";
        $norutx++;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian2' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian2' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian2',2);\"/></td>";
        $table .= "</tr>";
        $norutx++;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".</b><input id='jenispengembalian3' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian3' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian3',2);\"/></td>";
        $table .= "</tr>";

        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx++ . ".</b><input id='jenispengembalian4' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian4' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian4',2);\"/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx++ . ".</b><input id='jenispengembalian5' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian5' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian5',2);\"/></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx++ . ".</b><input id='jenispengembalian6' type='text' class='myinputtext' style='width:200px;' value='' onkeypress='return tanpa_kutip(event);'/></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaijenispengembalian6' type='text' class='myinputtextnumber' style='width:190px;' value='' onkeypress='return angka_doang(event)' onkeyup=\"hitungtotaljenispengembalian();z.numberFormat('nilaijenispengembalian6',2);\"/></td>";
        $table .= "</tr>";
        $norutx++;
        $table .= "<tr>";
        $table .= "<td colspan=3><b>E.Pajak</b></td>";
        $table .= "</tr>";
        $norutx = 1;
        $table .= "<tr>";
        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:5px;text-align:left;vertical-align:top' colspan=2><b>" . $norutx . ".Pajak</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";

        $table .= "<td style='width:20px;'>&nbsp</td>";
        $table .= "<td style='width:20px;'>&nbsp</td>";


        $wherexxx = '';
        if ($maxpajak != '0' and $maxpajak != '') {
            $wherexxx = " and persentase<'" . $maxpajak . "'";
        }

        $query       = "SELECT * FROM $dbname.sdm_5pajakpesangon WHERE kodept = '" . $bkodept->kodeorganisasi . "' and  penghasilan<'" . $totalupajak . "'" . $wherexxx . " order by penghasilan asc";
        //echo $query;
        $result      = fetchData($query);
        $jumlahres = count($result);
        $tambahan = 0;
        $nilaipajak = '';
        $noxzx = 1;
        if (count($result) > 0) {
            foreach ($result as $value) {
                if ($nilaipajak == '') {
                    $nilaipajak = $value['penghasilan'];
                    $tambahan += $nilaipajak * $value['persentase'];
                    //echo $value['penghasilan']." ke =".$noxzx.' =>'.($nilaipajak*$value['persentase']).'<br>';
                } else {
                    $tambahan += ($value['penghasilan'] - $nilaipajak) * $value['persentase'];
                    //echo $value['penghasilan'].'-'.$nilaipajak." ke =".$noxzx.' =>'.(($value['penghasilan']-$nilaipajak)*$value['persentase']).'<br>';
                    if ($noxzx <= $jumlahres) {
                        $nilaipajak = $nilaipajak + ($value['penghasilan'] - $nilaipajak);
                    }
                }
                $noxzx++;
            }
        }

        $wherexxx = '';
        if ($maxpajak != '0' and $maxpajak != '') {
            $wherexxx = " and persentase='" . $maxpajak . "'";
        } else {
            $wherexxx = " and  penghasilan>='" . $totalupajak . "'";
        }

        $query       = "SELECT * FROM $dbname.sdm_5pajakpesangon WHERE kodept = '" . $bkodept->kodeorganisasi . "' " . $wherexxx . " order by penghasilan asc limit 1";
        $result      = fetchData($query);
        $pajak = 0;
        if (count($result) > 0) {
            if ($nilaipajak == '') {
                $nilaipajak = 0;
            }
            foreach ($result as $value) {
                $pajak = (($totalupajak - $nilaipajak) * $value['persentase']) + $tambahan;
                // echo $value['persentase'].' ke =terakhir =>'.(($totalupajak-$nilaipajak)*$value['persentase']).'<br>';

            }
        }
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='nilaipajak' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format($pajak, 0) . "' onkeypress='return angka_doang(event)' disabled/></td>";
        $table .= "</tr>";
        $norutx++;
        $table .= "<tr>";
        $table .= "<td colspan=2 align=right></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'><b>____________________________</b></td>";
        $table .= "</tr>";
        $table .= "<tr>";
        $table .= "<td colspan=2 align=right></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'><b>TOTAL POTONGAN</b>&nbsp&nbspRp.<input style='width:190px;' type=text class=myinputtextnumber id=rupiahtotalpotongan value='" . $pajak . "' disabled></td>";
        $table .= "</tr>";

        $table .= "<tr>";
        $table .= "<td colspan=2><b>F.DITERIMA</b></td>";
        $table .= "<td style='width:5px;text-align:right;vertical-align:top'>Rp.<input id='rupiahditerima' type='text' class='myinputtextnumber' style='width:190px;' value='" . number_format(($total1 - $pajak)) . "' onkeypress='return angka_doang(event)' disabled/></td>";
        $table .= "</tr>";
        $table .= "</table>";



        $urutPerhitungan = 0;


        $table .= "<table border='0' style='margin-top:20px;width:95%;margin-bottom:20px'>";
        $table .= "<tr style='font-weight:bold;text-align:center'>";
        $table .= "<td>";
        $table .= "<button class='mybutton' onclick=\"savePesangonNew('insert')\" >" . $_SESSION['lang']['save'] . "</button>";
        $table .= "<button class='mybutton' onclick='cancelIsiNew()'>" . $_SESSION['lang']['cancel'] . "</button>";
        $table .= "</td>";
        $table .= "</tr>";
        $table .= "</table>";



        echo $table;
        break;

    case "hitungPPH21";
        $query       = "SELECT * FROM $dbname.sdm_5pajakpesangon WHERE kodept = '" . $param['pt'] . "' and  penghasilan<='" . $param['totaldapat'] . "'";
        $result      = fetchData($query);
        $tambahan = 0;
        $pajak = 0;
        if (count($result) > 0) {
            foreach ($result as $value) {
                $pajak = (($param['totaldapat'] - $value['penghasilan']) * $value['persentase']) + $tambahan;
                $tambahan = $value['penghasilan'] * $value['persentase'];
            }
        }

        echo $pajak;
        break;

    case 'insert':
        try {
            $owlPDO->beginTransaction();

            $noid = tanggalsystem($param['tglberhenti']) . '/' . $karyawanid;
            $str = "delete from " . $dbname . ".sdm_pesangon WHERE noid='" . $noid . "'";
            $owlPDO->exec($str);
            $param['textcuti'] = number_format($param['gajipokok']) . "x" . number_format($param['cutitahunan']) . "/" . number_format($param['pembagigajicuti']);
            $param['textkesehatan'] = number_format($param['gajipokok']) . "x" . number_format($param['proporsikesehatan']) . "/12 x " . number_format($param['pengalikesehatan']);

            $datainsert = "(
                '" . $noid . "',
                '" . $param['karyawanid'] . "',
                '',
                '" . $param['kodeunit'] . "',
                '" . tanggalsystem($param['tanggal']) . "',
                '" . tanggalsystem($param['tglberhenti']) . "',
                '" . tanggalsystem($param['tglmasuk']) . "',
                '" . $param['masakerjatahun'] . "',
                '" . $param['masakerjabulan'] . "',
                '" . $param['masakerjahari'] . "',
                '" . $param['gajipokok'] . "',
                '" . $param['jenis'] . "',
                '" . $param['textuangpisah'] . "',
                '" . $param['uangpisah'] . "',
                '" . $param['textupmk'] . "',
                '" . $param['upmk'] . "',
                '" . $param['textcuti'] . "',
                '" . $param['cutitahunan'] . "',
                '" . $param['pembagigajicuti'] . "',
                '" . $param['rupiahcutitahunan'] . "',
                '" . $param['textkesehatan'] . "',
                '" . $param['proporsikesehatan'] . "',
                '" . $param['pengalikesehatan'] . "',
                '" . $param['rupiahkesehatan'] . "',
                '" . $param['tambahan1'] . "',
                '" . $param['nilaitambahan1'] . "',
                '" . $param['tambahan2'] . "',
                '" . $param['nilaitambahan2'] . "',
                '" . $param['tambahan3'] . "',
                '" . $param['nilaitambahan3'] . "',
                '" . $param['tambahan4'] . "',
                '" . $param['nilaitambahan4'] . "',
                '" . $param['rupiahtotal1'] . "',
                '" . $param['jenispengembalian1'] . "',
                '" . $param['jenispengembalian2'] . "',
                '" . $param['jenispengembalian3'] . "',
                '" . $param['jenispengembalian4'] . "',
                '" . $param['jenispengembalian5'] . "',
                '" . $param['jenispengembalian6'] . "',
                '" . $param['nilaijenispengembalian1'] . "',
                '" . $param['nilaijenispengembalian2'] . "',
                '" . $param['nilaijenispengembalian3'] . "',
                '" . $param['nilaijenispengembalian4'] . "',
                '" . $param['nilaijenispengembalian5'] . "',
                '" . $param['nilaijenispengembalian6'] . "',
                '" . $param['nilaipajak'] . "',
                '" . $param['rupiahtotalpotongan'] . "',
                '" . $param['rupiahditerima'] . "',
                '0',
                '1',
                '" . $_SESSION['standard']['userid'] . "',
                '" . date('Y-m-d H:i:s') . "',
                '" . date('Y-m-d H:i:s') . "',
                '" . $_SESSION['standard']['userid'] . "'
            )";

            // exit("Error: ".$param['tambahan3']." ".$param['tambahan4']." ".$param['tambahan5']." ".$param['tambahan6']);


            $query = "INSERT INTO " . $dbname . ".sdm_pesangon (
                noid, karyawanid, pihakpertama, kodeunit, tanggal, tanggalberhenti, tanggalmasuk,
                masakerjatahun, masakerjabulan, masakerjahari, upahterakhir, jenis,
                textuangpisah, uangpisah, textupmk, upmk, textcuti, cutitahunan, pembagigajicuti,
                rupiahcutitahunan, textkesehatan, proporsikesehatan, pengalikesehatan, rupiahkesehatan,
                tambahan1, nilaitambahan1, tambahan2, nilaitambahan2, tambahan3, nilaitambahan3,
                tambahan4, nilaitambahan4, rupiahtotal1,
                jenispengembalian1, jenispengembalian2, jenispengembalian3, jenispengembalian4,
                jenispengembalian5, jenispengembalian6,
                nilaijenispengembalian1, nilaijenispengembalian2, nilaijenispengembalian3, nilaijenispengembalian4,
                nilaijenispengembalian5, nilaijenispengembalian6,
                nilaipajak, rupiahtotalpotongan, rupiahditerima,
                posting, statuspersetujuan, createby, createtime, updatetime, updateby
            ) VALUES " . $datainsert;
            // echo $query;
            $owlPDO->exec($query);



            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Errorcode, " . addslashes($e->getMessage());
            die();
        }
        break;

    case 'insert2':
        $nosurat        = $_POST['nosurat'];
        $tanggal        = $_POST['tanggal'];
        $karyawanid     = $_POST['karyawanid'];
        $pihakpertama   = $_POST['pihakpertama'];
        $kodeunit       = $_POST['kodeunit'];
        $tglberhenti    = $_POST['tglberhenti'];
        $masakerjatahun = $_POST['masakerjatahun'];
        $masakerjabulan = $_POST['masakerjabulan'];
        $masakerjahari  = $_POST['masakerjahari'];
        $gajipokok      = $_POST['gajipokok'];
        $tunjanganjabatan = $_POST['tunjanganjabatan'];
        $jenissk        = $_POST['jenissk'];
        $p1562          = $_POST['p1562'];
        $jml_pesangon   = $_POST['jml_pesangon'];
        $p1563          = $_POST['p1563'];
        $tot_penghargaan = $_POST['tot_penghargaan'];
        $p1564a         = $_POST['p1564a'];
        $jmlh_p1564a    = $_POST['jmlh_p1564a'];
        $p1564b         = $_POST['p1564b'];
        $jmlh_p1564b    = $_POST['jmlh_p1564b'];
        $p1564c         = $_POST['p1564c'];
        $jmlh_p1564c    = $_POST['jmlh_p1564c'];
        $tot_sblm_pajak = $_POST['tot_sblm_pajak'];
        $pajakprogresif1_ = $_POST['pajakprogresif1_'];
        $pajakprogresif2_ = $_POST['pajakprogresif2_'];
        $pajakprogresif3_ = $_POST['pajakprogresif3_'];
        $tot_pajak_      = $_POST['tot_pajak_'];
        $tot_pesangon    = $_POST['tot_pesangon'];

        if ($nosurat == '') {
            //exit("error: ".$_SESSION['lang']['nosurat']." tidak boleh kosong");
            /** Auto Numbering :
        1. Number
        2. "PB"
        3. Initial PT
        4. "HRD"
        5. Bulan bentuk Romawi IIV
        6. Tahun
        Ex: 001/PB/GAL/HRD/VII/2017
             **/
            $num = 1;
            $PT             = "PT";
            $sPT = "select kodeorganisasi from " . $dbname . ".datakaryawan where karyawanid = '" . $karyawanid . "'";
            $rPT = fetchData($sPT);
            if (count($rPT) > 0) {
                $PT = $rPT[0]['kodeorganisasi'];
            }
            $bulan          = romawi(date('m', strtotime($tanggal)));
            $tahun          = date('Y', strtotime($tanggal));
            $snum = "select MAX(SUBSTRING(nosurat,1,3)) as num from " . $dbname . ".sdm_pesangon where nosurat like '%/PB/" . $PT . "/" . $bulan . "/" . $tahun . "'";
            $rnum = fetchData($snum);
            if (count($rnum) > 0) {
                $num = intval($rnum[0]['num']) + $num;
            }
            $lastNumber     = str_pad($num, 3, "0", STR_PAD_LEFT); //r: 001
            $createFormat   = $lastNumber . "/PB/" . $PT . "/" . $bulan . "/" . $tahun;
            //result//
            $nosurat = $createFormat;
        }
        if ($tanggal == '') {
            exit("error: " . $_SESSION['lang']['tanggal'] . " tidak boleh kosong");
        }
        if ($karyawanid == '') {
            exit("error: " . $_SESSION['lang']['namakaryawan'] . " tidak boleh kosong");
        }
        if ($tglberhenti == '') {
            exit("error: " . $_SESSION['lang']['tglberhenti'] . " tidak boleh kosong");
        }
        $scek = "select * from " . $dbname . ".sdm_pesangon where nosurat='" . $nosurat . "' and karyawanid='" . $karyawanid . "' ";
        $qcek = $owlPDO->query($scek) or die(print " Gagal: " . PDOException::getMessage());
        $rcek = owlBaris($qcek);
        if ($rcek < 1) {
            $sIns = "insert into " . $dbname . ".sdm_pesangon(nosurat,karyawanid,pihakpertama,kodeunit,tanggal,tanggalberhenti,masakerjatahun,masakerjabulan,masakerjahari,
           upahterakhir,tunjanganjabatan,jenispesangon,p1562,jumlahp1562,p1563,jumlahp1563,p1564a,jumlahp1564a,p1564b,jumlahp1564b,
           p1564c,jumlahp1564c,jumlahsebelumpajak,totalterima,createby,createtime) 
           values ('" . $nosurat . "','" . $karyawanid . "','" . $pihakpertama . "','" . $kodeunit . "','" . tanggalsystem($tanggal) . "','" . tanggalsystem($tglberhenti) . "','" . $masakerjatahun . "','" . $masakerjabulan . "','" . $masakerjahari . "',
           '" . $gajipokok . "','" . $tunjanganjabatan . "','" . $jenissk . "','" . $p1562 . "','" . $jml_pesangon . "','" . $p1563 . "','" . $tot_penghargaan . "','" . $p1564a . "','" . $jmlh_p1564a . "',
           '" . $p1564b . "','" . $jmlh_p1564b . "','" . $p1564c . "','" . $jmlh_p1564c . "',
           '" . $tot_sblm_pajak . "','" . $tot_pesangon . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "')";
            try {
                $owlPDO->exec($sIns);
                echo 'Done.';
            } catch (PDOException $e) {
                echo "Gagal" . $e->getMessage();
                die();
            }
        } else {
            $sUpd = "update " . $dbname . ".sdm_pesangon set kodeunit='" . $kodeunit . "',pihakpertama='" . $pihakpertama . "',
               tanggal='" . $tanggal . "',tanggalberhenti='" . $tglberhenti . "',masakerjatahun='" . $masakerjatahun . "',
               masakerjabulan='" . $masakerjabulan . "',masakerjahari='" . $masakerjahari . "',upahterakhir='" . $gajipokok . "',
               tunjanganjabatan='" . $tunjanganjabatan . "',jenispesangon='" . $jenissk . "',p1562='" . $p1562 . "',jumlahp1562='" . $jml_pesangon . "',
               p1563='" . $p1563 . "',jumlahp1563='" . $tot_penghargaan . "',p1564a='" . $p1564a . "',jumlahp1564a='" . $jmlh_p1564a . "',
               p1564b='" . $p1564b . "',jumlahp1564b='" . $jmlh_p1564b . "',p1564c='" . $p1564c . "',jumlahp1564c='" . $jmlh_p1564c . "',
               jumlahsebelumpajak='" . $tot_sblm_pajak . "',totalterima='" . $tot_pesangon . "',
               updateby='" . $_SESSION['standard']['userid'] . "'
               where nosurat='" . $nosurat . "' and karyawanid='" . $karyawanid . "'";
            try {
                $owlPDO->exec($sUpd);
                echo 'Done.';
            } catch (PDOException $e) {
                echo "DB Error : " . $e->getMessage();
                die();
            }
        }
        break;

    case 'loadData':
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        $sCount = "select count(*) as jmlhrow from " . $dbname . ".sdm_pesangon order by karyawanid asc";
        $qCount = $owlPDO->query($sCount) or die(print " Gagal: " . PDOException::getMessage());
        $qCount->setFetchMode(PDO::FETCH_OBJ);
        while ($rCount = $qCount->fetch()) {
            $jlhbrs = $rCount->jmlhrow;
        }

        $offset = $page * $limit;
        if ($jlhbrs < ($offset)) $page -= 1;
        $offset = $page * $limit;
        $no = $offset;

        echo "<table class=sortable cellspacing=1 cellpadding=7 border=0 style='width:100%;'  ><thead>
      <tr class=rowheader>
           <th align=center>No.</th>
           <th align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
           <th align=center>" . $_SESSION['lang']['tglberhenti'] . "</th>
           <th align=center>" . $_SESSION['lang']['masakerja'] . "</th>
           <th align=center>Jenis</th>
           <th align=center>Total Diterima</th>
           <th align=center colspan=4>" . $_SESSION['lang']['action'] . "</th>
      </tr>
     </thead>
     <tbody id=container>";
        $hwd = "";
        if ($_POST['BlnCr'] != '') {
            $hwd .= " and left(a.tanggalberhenti,7)='" . $_POST['BlnCr'] . "'";
        }
        if ($_POST['nmCar'] != '') {
            $hwd .= " and b.namakaryawan like '%" . $_POST['nmCar'] . "%'";
        }
        if ($_POST['jnsSkCar'] != '') {
            $hwd .= " and a.jenis='" . $_POST['jnsSkCar'] . "'";
        }
        $str = "select a.*,b.namakaryawan as namakaryawan
           from " . $dbname . ".sdm_pesangon a 
           left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where 1=1 " . $hwd . "
           order by a.tanggalberhenti desc  limit " . $offset . "," . $limit . "";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $baris = owlBaris($res);
        $no = 0;
        if ($baris == 0) {
            echo "<tr class=rowcontent><td colspan=9 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
        } else {
            $arrjenis = makeOption($dbname, 'sdm_5jenispesangon', 'kode,jenis');
            while ($bar = $res->fetch()) {
                $no += 1;
                echo "<tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td>" . $bar['namakaryawan'] . "</td>
                <td align=center>" . $bar['tanggalberhenti'] . "</td>
                <td align=center>" . $bar['masakerjatahun'] . " Tahun " . $bar['masakerjabulan'] . " Bulan " . $bar['masakerjahari'] . " Hari</td>
                <td>" . $arrjenis[$bar['jenis']] . "</td>
                <td align=right>" . number_format($bar['rupiahditerima']) . "</td>";
                // if($bar['statuspersetujuan'] == 0 || $bar['statuspersetujuan'] == 3){

                //     echo "<td align=center><img src=images/skyblue/submit.jpg class=zImgBtn title='Ajukan' caption='Ajukan' onclick=\"formajukan('".$bar['noid']."');\"></td>";
                // }elseif($bar['statuspersetujuan'] == 9){
                //     echo "<td colspan='3' align=center style='color: blue;'><b>".$stsapprv[$bar['statuspersetujuan']]."</b></td>";
                // }elseif($bar['statuspersetujuan'] == 2){
                //     echo "<td colspan='3' align=center style='color: red;'><b>".$stsapprv[$bar['statuspersetujuan']]."</b></td>";
                // }elseif($bar['statuspersetujuan'] == 1){
                //     echo "<td colspan='3' align=center style='color: green;'><b>".$stsapprv[$bar['statuspersetujuan']]."</b></td>";
                // }
                //     // echo "<td align=center><img src=images/pdf.jpg class=resicon  title='HR' onclick=\"showPDFNew('".$bar['noid']."','HR',event)\"></td>"; 
                //     echo "<td align=center><img src=images/pdf.jpg class=resicon  title='KANTOR' onclick=\"showPDFNew('".$bar['noid']."','KANTOR',event)\"></td>"; 
                //     // echo "<td align=center><img src=images/pdf.jpg class=resicon  title='DPLK' onclick=\"showPDFNew('".$bar['noid']."','DPLK',event)\"></td>"; 

                if ($bar['posting'] == 1) {
                    echo "<td colspan=3 align=center><img src=images/skyblue/posted.png class=resicon  title='Posting'></td>";
                    echo "<td align=center><img src=images/pdf.jpg class=resicon  title='KANTOR' onclick=\"showPDFNew('" . $bar['noid'] . "','KANTOR',event)\"></td>";
                } else {
                    echo "<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"gedit('" . $bar['noid'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['karyawanid'] . "','" . $bar['kodeunit'] . "','" . tanggalnormal($bar['tanggalmasuk']) . "','" . tanggalnormal($bar['tanggalberhenti']) . "','" . $bar['masakerjatahun'] . "','" . $bar['masakerjabulan'] . "','" . $bar['masakerjahari'] . "','" . $bar['upahterakhir'] . "','" . $bar['jenis'] . "');\"></td>";
                    echo "<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('" . $bar['noid'] . "');\"></td>";
                    echo "<td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting' onclick=\"posting('" . $bar['noid'] . "');\"></td>";
                    echo "<td align=center><img src=images/pdf.jpg class=resicon  title='KANTOR' onclick=\"showPDFNew('" . $bar['noid'] . "','KANTOR',event)\"></td>";
                }



                echo "</tr>";
            }
        }

        echo "<tr class=rowheader><td colspan=10 align=center>
        " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " .  $jlhbrs . "<br />
        <button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
        <button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
        </td>
        </tr></table>";
        break;

    case 'update':
        try {
            $owlPDO->beginTransaction();


            $param['textcuti'] = number_format($param['gajipokok']) . "x" . number_format($param['cutitahunan']) . "/" . number_format($param['pembagigajicuti']);
            $param['textkesehatan'] = number_format($param['gajipokok']) . "x" . number_format($param['proporsikesehatan']) . "/12 x " . number_format($param['pengalikesehatan']);



            $data = array(
                'karyawanid'          =>   $param['karyawanid'],
                'kodeunit'          =>   $param['kodeunit'],
                'tanggal'          =>   tanggalsystem($param['tanggal']),
                'tanggalberhenti'          =>   tanggalsystem($param['tglberhenti']),
                'tanggalmasuk'          =>   tanggalsystem($param['tglmasuk']),
                'masakerjatahun'          =>   $param['masakerjatahun'],
                'masakerjabulan'          =>   $param['masakerjabulan'],
                'masakerjahari'          =>   $param['masakerjahari'],
                'upahterakhir'          =>   $param['gajipokok'],
                'jenis'          =>   $param['jenis'],
                'textuangpisah'          =>   $param['textuangpisah'],
                'uangpisah'          =>   $param['uangpisah'],
                'textupmk'          =>   $param['textupmk'],
                'upmk'          =>   $param['upmk'],
                'textcuti'          =>   $param['textcuti'],
                'cutitahunan'          =>   $param['cutitahunan'],
                'pembagigajicuti'          =>   $param['pembagigajicuti'],
                'rupiahcutitahunan'          =>   $param['rupiahcutitahunan'],
                'textkesehatan'          =>   $param['textkesehatan'],
                'proporsikesehatan'          =>   $param['proporsikesehatan'],
                'pengalikesehatan'          =>   $param['pengalikesehatan'],
                'rupiahkesehatan'          =>   $param['rupiahkesehatan'],
                'tambahan1'          =>   $param['tambahan1'],
                'tambahan2'          =>   $param['tambahan2'],
                'tambahan3'          =>   $param['tambahan3'],
                'tambahan4'          =>   $param['tambahan4'],
                'nilaitambahan1'          =>   $param['nilaitambahan1'],
                'nilaitambahan2'          =>   $param['nilaitambahan2'],
                'nilaitambahan3'          =>   $param['nilaitambahan3'],
                'nilaitambahan4'          =>   $param['nilaitambahan4'],
                'rupiahtotal1'          =>   $param['rupiahtotal1'],
                'jenispengembalian1'          =>   $param['jenispengembalian1'],
                'jenispengembalian2'          =>   $param['jenispengembalian2'],
                'jenispengembalian3'          =>   $param['jenispengembalian3'],
                'jenispengembalian4'          =>   $param['jenispengembalian4'],
                'jenispengembalian5'          =>   $param['jenispengembalian5'],
                'jenispengembalian6'          =>   $param['jenispengembalian6'],
                'nilaijenispengembalian1'          =>   $param['nilaijenispengembalian1'],
                'nilaijenispengembalian2'          =>   $param['nilaijenispengembalian2'],
                'nilaijenispengembalian3'          =>   $param['nilaijenispengembalian3'],
                'nilaijenispengembalian4'          =>   $param['nilaijenispengembalian4'],
                'nilaijenispengembalian5'          =>   $param['nilaijenispengembalian5'],
                'nilaijenispengembalian6'          =>   $param['nilaijenispengembalian6'],
                'nilaipajak'          =>   $param['nilaipajak'],
                'rupiahtotalpotongan'          =>   $param['rupiahtotalpotongan'],
                'rupiahditerima'          =>   $param['rupiahditerima'],
                'posting'          =>   '0',
                'updatetime'          =>   $_SESSION['standard']['userid'],
                'updateby'          =>   date('Y-m-d H:i:s')
            );
            $where = "noid = '" . $param['noid'] . "'";
            $query = updateQuery($dbname, 'sdm_pesangon', $data, $where);
            echo $query;
            $owlPDO->exec($query);


            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Errorcode, " . addslashes($e->getMessage());
            die();
        }
        break;

    case 'deletedata':
        $sDel = "delete from " . $dbname . ".sdm_pesangon 
           where noid='" . $param['noid'] . "'";
        try {
            $owlPDO->exec($sDel);
        } catch (PDOException $e) {
            echo "DB Error : " . $e->getMessage();
            die();
        }

        break;
    case 'getsisahakcuti':
        $periodecuti = date('Y', strtotime($tglberhenti));
        //Perhitungan sisa hak cuti = sisa cuti / hak cuti x (gaji pokok + tunjangan tetap)
        $scuti = "select * from " . $dbname . ".sdm_cutiht where karyawanid = '" . $karyawanid . "' and periodecuti='" . $periodecuti . "' limit 1";
        $rcuti = fetchData($scuti);
        if (count($rtr) > 0) {
            $hakcuti = $rcuti[0]['hakcuti'];
            $sisacuti = $rcuti[0]['sisa'];
            $persentage = $sisacuti / $hakcuti;
        }

        break;
    case 'posting':
        $str = "update " . $dbname . ".sdm_pesangon set posting='1' where noid ='" . $param['noid'] . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal:" . addslashes($e->getMessage());
        }
        break;
    case 'unposting':
        $str = "update " . $dbname . ".sdm_pesangon set posting='0' where noid ='" . $param['noid'] . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal:" . addslashes($e->getMessage());
        }
        break;

    case 'showPDFNew':
        $query  = "SELECT * FROM $dbname.sdm_pesangon WHERE noid = '" . $param['noid'] . "'";
        $result = fetchData($query, 'OBJECT');
        foreach ($result as $key => $value) {
            $noid = $value->noid;
            $karyawanid = $value->karyawanid;
            $pihakpertama = $value->pihakpertama;
            $kodeunit = $value->kodeunit;
            $tanggal = $value->tanggal;
            $tanggalberhenti = $value->tanggalberhenti;
            $tanggalmasuk = $value->tanggalmasuk;
            $masakerjatahun = $value->masakerjatahun;
            $masakerjabulan = $value->masakerjabulan;
            $masakerjahari = $value->masakerjahari;
            $upahterakhir = $value->upahterakhir;
            $jenis = $value->jenis;
            $textuangpisah = $value->textuangpisah;
            $uangpisah = $value->uangpisah;
            $textupmk = $value->textupmk;
            $upmk = $value->upmk;
            $textcuti = $value->textcuti;
            $cutitahunan = $value->cutitahunan;
            $pembagigajicuti = $value->pembagigajicuti;
            $rupiahcutitahunan = $value->rupiahcutitahunan;
            $textkesehatan = $value->textkesehatan;
            $proporsikesehatan = $value->proporsikesehatan;
            $pengalikesehatan = $value->pengalikesehatan;
            $rupiahkesehatan = $value->rupiahkesehatan;
            $tambahan1 = $value->tambahan1;
            $tambahan2 = $value->tambahan2;
            $nilaitambahan1 = $value->nilaitambahan1;
            $nilaitambahan2 = $value->nilaitambahan2;
            $rupiahtotal1 = $value->rupiahtotal1;
            $jenispengembalian1 = $value->jenispengembalian1;
            $jenispengembalian2 = $value->jenispengembalian2;
            $jenispengembalian3 = $value->jenispengembalian3;
            $jenispengembalian4 = $value->jenispengembalian4;
            $nilaijenispengembalian1 = $value->nilaijenispengembalian1;
            $nilaijenispengembalian2 = $value->nilaijenispengembalian2;
            $nilaijenispengembalian3 = $value->nilaijenispengembalian3;
            $nilaijenispengembalian4 = $value->nilaijenispengembalian4;
            $nilaipajak = $value->nilaipajak;
            $rupiahtotalpotongan = $value->rupiahtotalpotongan;
            $rupiahditerima = $value->rupiahditerima;
            $posting = $value->posting;
            $createby = $value->createby;
            $createtime = $value->createtime;
            $updatetime = $value->updatetime;
            $updateby = $value->updateby;
        }

        $query  = "SELECT * FROM $dbname.sdm_5jenispesangon WHERE jenis = '" . $jenis . "'";
        $result = fetchData($query, 'OBJECT');
        $statuspajak = $result[0]->statuspajak;
        $maxpajak = $result[0]->maxpajak;

        $skodept = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $karyawanid . "'";
        $rkodept = $owlPDO->query($skodept) or die(print " Gagal: " . PDOException::getMessage());
        $rkodept->setFetchMode(PDO::FETCH_OBJ);
        $bkodept = $rkodept->fetch();
        if (!$bkodept->kodeorganisasi) {
            exit("ERROR: Data Karyawan not Found!");
        }
        if (!$masakerjatahun) {
            exit("ERROR: Date not Found!");
        }

        $param['tglmasuk'] = $bkodept->tanggalmasuk;
        $table = '';
        $str = "select id, caption, caption2, caption3 from " . $dbname . ".menu where action='sdm_pesangon'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $menuid = $bar['id'];
        }
        $cspanjudul = array();
        $arrkaryawanid = array();
        $ket = array();
        // $str="select * from ".$dbname.".setup_2ttd where menuid='".$menuid."' and kodeunit='".$kodeunit."'  order by judul,id asc";
        $str = "select * from " . $dbname . ".sdm_5ttdpesangon where unit = '" . $kodeunit . "' ORDER by unit, level";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            @$cspanjudul[$bar['tipe']] += 1;
            @$arrkaryawanid[$bar['tipe']][] = $bar['karyawan'];
            @$ket[$bar['tipe']][] = $bar['keterangan'];
        }

        switch ($param['jenisx']) {
            case 'HR':
                $table .= "<table border='0' align='center'>";
                $table .= "<tr><td style='text-align:center;font-size:14px;'><b>Rincian Biaya</b></td></tr>";
                $table .= "<tr><td style='text-align:center;font-size:14px;'><b>Pemutusan Hubungan Kerja</b></td></tr>";
                $table .= "</table>";

                $table .= "<table border='0' style='width:100%;margin-top:20px'>";
                $table .= "<tr>";
                $table .= "<td colspan=4 style='font-size:14px;'><b>A.IDENTITA PEGAWAI</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>1.</td>";
                $table .= "<td style='width:450px;vertical-align:top;font-size:12px;'>&nbsp;<b>" . $_SESSION['lang']['nama'] . "</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $bkodept->namakaryawan . "</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>2.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;" . $_SESSION['lang']['nik'] . "</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . $bkodept->nik . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top'>3.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Jabatan</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . getNamaJabatan($bkodept->kodejabatan) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top'>5.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Divisi</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . getNamaOrg($bkodept->lokasitugas) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>6.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Tempat/Tanggal Lahir</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . $bkodept->tempatlahir . "/" . tanggalnormal($bkodept->tanggallahir) . "</td>";
                $table .= "</tr>";
                $tgl1   = new DateTime($bkodept->tanggallahir);
                $tgl2   = new DateTime($tanggalberhenti);
                $jarak  = $tgl2->diff($tgl1);

                $usiatahun = addZero($jarak->y, 2);
                $usiabulan = addZero($jarak->m, 2);
                $usiahari  = addZero($jarak->d, 2);
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>7.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Usia</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $usiatahun . " Tahun " . $usiabulan . " Bulan " . $usiahari . " Hari</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>8.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Tanggal Masuk Kerja</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . tanggalnormal($bkodept->tanggalmasuk) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>9.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Tanggal Keluar Kerja</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . tanggalnormal($tanggalberhenti) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>10.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Masa Kerja</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $masakerjatahun . " Tahun " . $masakerjabulan . " Bulan " . $masakerjahari . " Hari</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>11.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Upah Terakhir</b></td>";
                $table .= "<td style='width:5px;vertical-align:top;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>Rp." . number_format(($upahterakhir), 0) . "</b></td>";
                $table .= "</tr>";
                $arrjenis = makeOption($dbname, 'sdm_5jenispesangon', 'kode,jenis');
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>12.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Alasan</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $arrjenis[$jenis] . "</b></td>";
                $table .= "</tr>";
                $table .= "</table>";

                $table .= "<table border='0' style='margin-top:15px;width:100%'>";
                $table .= "<tr>";
                $table .= "<td colspan=3><b>B.Kompensasi</b></td>";
                $table .= "</tr>";
                $norutx = 1;
                $total1 = 0;
                $totalupajak = 0;
                if ($textuangpisah != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . ".Uang Pisah</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";

                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;' id='textuangpisah'>" . $textuangpisah . "</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($uangpisah, 0) . "</td>";
                    $table .= "</tr>";
                    $total1 += $uangpisah;
                    $totalupajak += $uangpisah;
                    $norutx++;
                }

                if ($textupmk != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . ".Uang Penghargaan Masa Kerja</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";

                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;' id='textupmk'>" . $textupmk . "</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($upmk, 0) . "</td>";
                    $table .= "</tr>";
                    $total1 += $upmk;
                    $totalupajak += $upmk;
                    $norutx++;
                }


                $table .= "<tr>";
                $table .= "<td colspan=3><b>C.Penggantian Hak</b></td>";
                $table .= "</tr>";
                $norutx = 1;
                $table .= "<tr>";
                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . ".Cuti Tahunan yang belum gugur</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";

                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;'>Rp." . number_format(($upahterakhir), 0) . " x " . $cutitahunan . " / " . $pembagigajicuti . "</td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format((($upahterakhir) * $cutitahunan / $pembagigajicuti), 0) . "</td>";
                $table .= "</tr>";
                $total1 += (($upahterakhir) * $cutitahunan / $pembagigajicuti);
                $norutx++;
                $table .= "<tr>";
                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . ".Tunjangan Kesehatan</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";

                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;'>Rp." . number_format(($upahterakhir), 0) . " x " . $proporsikesehatan . " / 12 x " . $pengalikesehatan . "</td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format((($upahterakhir) * $proporsikesehatan / 12 * $pengalikesehatan), 0) . "</td>";
                $table .= "</tr>";
                $total1 += (($upahterakhir) * $proporsikesehatan / 12 * $pengalikesehatan);
                if ($tambahan1 != '') {
                    $norutx++;
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $tambahan1 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";

                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaitambahan1, 0) . "</td>";
                    $table .= "</tr>";
                    $total1 += $nilaitambahan1;
                }
                if ($tambahan2 != '') {
                    $norutx++;
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $tambahan2 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";

                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaitambahan2, 0) . "</td>";
                    $table .= "</tr>";

                    $total1 += $nilaitambahan2;
                }
                $table .= "<tr>";
                $table .= "<td colspan=2 align=right></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>____________________________</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td colspan=2 align=right></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp." . number_format($total1, 0) . "</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td colspan=3><b>D.Kewajiban/Pengembalian</b></td>";
                $table .= "</tr>";
                $norutx = 1;
                $totalnilaijenispengembalian = 0;
                if ($jenispengembalian1 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian1 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian1) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian1;
                    $norutx++;
                }

                if ($jenispengembalian2 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian2 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian2) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian2;
                    $norutx++;
                }

                if ($jenispengembalian3 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian3 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian3) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian3;
                    $norutx++;
                }

                if ($jenispengembalian4 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian4 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian4) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian4;
                    $norutx++;
                }


                $table .= "<tr>";
                $table .= "<td colspan=3><b>E.Pajak</b></td>";
                $table .= "</tr>";
                $norutx = 1;
                $table .= "<tr>";
                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;text-align:left;vertical-align:top;;font-size:14px;' colspan=2><b>" . $norutx . ".Pajak</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";

                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:20px;'>&nbsp;</td>";

                $wherexxx = '';
                if ($maxpajak != '0' and $maxpajak != '') {
                    $wherexxx = " and persentase<'" . $maxpajak . "'";
                }

                $query       = "SELECT * FROM $dbname.sdm_5pajakpesangon WHERE kodept = '" . $bkodept->kodeorganisasi . "' and  penghasilan<'" . $totalupajak . "'" . $wherexxx . " order by penghasilan asc";
                //echo $query;
                $result      = fetchData($query);
                $jumlahres = count($result);
                $tambahan = 0;
                $nilaipajak = '';
                $noxzx = 1;
                if (count($result) > 0) {
                    foreach ($result as $value) {
                        if ($nilaipajak == '') {
                            $nilaipajak = $value['penghasilan'];
                            $tambahan += $nilaipajak * $value['persentase'];
                            //echo $value['penghasilan']." ke =".$noxzx.' =>'.($nilaipajak*$value['persentase']).'<br>';
                        } else {
                            $tambahan += ($value['penghasilan'] - $nilaipajak) * $value['persentase'];
                            //echo $value['penghasilan'].'-'.$nilaipajak." ke =".$noxzx.' =>'.(($value['penghasilan']-$nilaipajak)*$value['persentase']).'<br>';
                            if ($noxzx <= $jumlahres) {
                                $nilaipajak = $nilaipajak + ($value['penghasilan'] - $nilaipajak);
                            }
                        }
                        $noxzx++;
                    }
                }

                $wherexxx = '';
                if ($maxpajak != '0' and $maxpajak != '') {
                    $wherexxx = " and persentase='" . $maxpajak . "'";
                } else {
                    $wherexxx = " and  penghasilan>='" . $totalupajak . "'";
                }

                $query       = "SELECT * FROM $dbname.sdm_5pajakpesangon WHERE kodept = '" . $bkodept->kodeorganisasi . "' " . $wherexxx . " order by penghasilan asc limit 1";
                $result      = fetchData($query);
                $pajak = 0;
                if (count($result) > 0) {
                    if ($nilaipajak == '') {
                        $nilaipajak = 0;
                    }
                    foreach ($result as $value) {
                        $pajak = (($totalupajak - $nilaipajak) * $value['persentase']) + $tambahan;
                        // echo $value['persentase'].' ke =terakhir =>'.(($totalupajak-$nilaipajak)*$value['persentase']).'<br>';

                    }
                }
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($pajak, 0) . "</td>";
                $table .= "</tr>";
                $norutx++;
                $table .= "<tr>";
                $table .= "<td colspan=2 align=right></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>____________________________</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td colspan=2 align=right></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>TOTAL POTONGAN&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp." . number_format(($pajak + $totalnilaijenispengembalian)) . "</b></td>";
                $table .= "</tr>";

                $table .= "<tr>";
                $table .= "<td colspan=2 style='font-size:14px;'><b>F.DITERIMA</b></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:14px;'>Rp." . number_format(($total1 - ($pajak + $totalnilaijenispengembalian))) . "</td>";
                $table .= "</tr>";
                $table .= "</table><p></p><p></p>";

                $table .= "<table style='width:500px;'";
                $table .= "<tr>";
                $ttot = ($total1 - ($pajak + $totalnilaijenispengembalian));
                $table .= "<td colspan=2 style='width:400px;font-size:12px;'><b>Terbilang : " . terbilang($ttot, '') . " rupiah</b></td><td></td>";
                $table .= "</tr>";
                $table .= "</table>";

                $table .= "<p></p><table style='width:500px;'";
                $table .= "<tr>";
                $table .= "<td colspan=2 style='width:400px;font-size:12px;'>Jakarta,&nbsp;&nbsp;" . tanggalnormal($tanggal) . "</td><td></td>";
                $table .= "</tr>";
                $table .= "</table>";

                $table .= "<p></p><table style='width:100%;'>";
                if (isset($cspanjudul['HR'])) {
                    $table .= "<tr>";

                    for ($i = 0; $i < $cspanjudul['HR']; $i++) {
                        // if($i=='0'){
                        //     $table .= "<td style='font-size:12px;'>Dibuat Oleh :</td>";
                        // }elseif($i=='1' and $i==$cspanjudul['HR']){
                        //     $table .= "<td style='font-size:12px;'>Disetujui Oleh :</td>";
                        // }elseif($i=='1' and $i!=$cspanjudul['HR']){
                        //     $table .= "<td style='font-size:12px;'>Diperiksa Oleh :</td>";
                        // }elseif($i>'1' and $i!=$cspanjudul['HR']){
                        //     $table .= "<td style='font-size:12px;'>Diperiksa Oleh :</td>";
                        // }else{
                        //     $table .= "<td style='font-size:12px;'>Disetujui Oleh :</td>";
                        // }
                        $table .= "<td style='font-size:12px;'>" . $ket['HR'][$i] . "</td>";
                    }




                    $table .= "</tr>";
                    $table .= "<tr>";

                    for ($i = 0; $i < $cspanjudul['HR']; $i++) {

                        $table .= "<td style='font-size:12px;height:35px;'></td>";
                    }




                    $table .= "</tr>";
                    $table .= "<tr>";

                    for ($i = 0; $i < $cspanjudul['HR']; $i++) {
                        $table .= "<td style='font-size:12px;'>" . $arrkaryawanid['HR'][$i] . "</td>";
                    }




                    $table .= "</tr>";

                    // $table .= "<tr>";

                    //     for ($i=0; $i < $cspanjudul['HR']; $i++) { 
                    //         $table .= "<td style='font-size:12px;'>".getNamaJabatan(getKary($arrkaryawanid['HR'][$i],'kodejabatan'))."</td>";
                    //     }




                    // $table .= "</tr>"; 
                }
                $table .= "</table>";
                break;
            case 'KANTOR':
                $table .= "<table border='0' align='center'>";
                $table .= "<tr><td style='text-align:center;font-size:14px;'><b>Rincian Biaya</b></td></tr>";
                $table .= "<tr><td style='text-align:center;font-size:14px;'><b>Pemutusan Hubungan Kerja</b></td></tr>";
                $table .= "</table>";

                $table .= "<table border='0' style='width:100%;margin-top:20px'>";
                $table .= "<tr>";
                $table .= "<td colspan=4 style='font-size:14px;'><b>A.IDENTITA PEGAWAI</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>1.</td>";
                $table .= "<td style='width:450px;vertical-align:top;font-size:12px;'>&nbsp;<b>" . $_SESSION['lang']['nama'] . "</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $bkodept->namakaryawan . "</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>2.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;" . $_SESSION['lang']['nik'] . "</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . $bkodept->nik . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top'>3.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Jabatan</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . getNamaJabatan($bkodept->kodejabatan) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top'>5.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Divisi</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . getNamaOrg($bkodept->lokasitugas) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>6.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Tempat/Tanggal Lahir</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . $bkodept->tempatlahir . "/" . tanggalnormal($bkodept->tanggallahir) . "</td>";
                $table .= "</tr>";
                $tgl1   = new DateTime($bkodept->tanggallahir);
                $tgl2   = new DateTime($tanggalberhenti);
                $jarak  = $tgl2->diff($tgl1);

                $usiatahun = addZero($jarak->y, 2);
                $usiabulan = addZero($jarak->m, 2);
                $usiahari  = addZero($jarak->d, 2);
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>7.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Usia</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $usiatahun . " Tahun " . $usiabulan . " Bulan " . $usiahari . " Hari</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>8.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Tanggal Masuk Kerja</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . tanggalnormal($bkodept->tanggalmasuk) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>9.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Tanggal Keluar Kerja</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . tanggalnormal($tanggalberhenti) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>10.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Masa Kerja</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $masakerjatahun . " Tahun " . $masakerjabulan . " Bulan " . $masakerjahari . " Hari</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>11.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Upah Terakhir</b></td>";
                $table .= "<td style='width:5px;vertical-align:top;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>Rp." . number_format(($upahterakhir), 0) . "</b></td>";
                $table .= "</tr>";
                $arrjenis = makeOption($dbname, 'sdm_5jenispesangon', 'kode,jenis');
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>12.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Alasan</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $arrjenis[$jenis] . "</b></td>";
                $table .= "</tr>";
                $table .= "</table>";

                $table .= "<table border='0' style='margin-top:15px;width:100%'>";
                $norutx = 1;
                $total1 = 0;
                $totalupajak = 0;



                $table .= "<tr>";
                $table .= "<td colspan=3><b>B.Penggantian Hak</b></td>";
                $table .= "</tr>";
                $norutx = 1;
                $table .= "<tr>";
                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . ".Cuti Tahunan yang belum gugur</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";

                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;'>Rp." . number_format(($upahterakhir), 0) . " x " . $cutitahunan . " / " . $pembagigajicuti . "</td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format((($upahterakhir) * $cutitahunan / $pembagigajicuti), 0) . "</td>";
                $table .= "</tr>";
                $total1 += (($upahterakhir) * $cutitahunan / $pembagigajicuti);
                $norutx++;
                $table .= "<tr>";
                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . ".Tunjangan Kesehatan</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";

                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;'>Rp." . number_format(($upahterakhir), 0) . " x " . $proporsikesehatan . " / 12 x " . $pengalikesehatan . "</td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format((($upahterakhir) * $proporsikesehatan / 12 * $pengalikesehatan), 0) . "</td>";
                $table .= "</tr>";
                $total1 += (($upahterakhir) * $proporsikesehatan / 12 * $pengalikesehatan);
                if ($tambahan1 != '') {
                    $norutx++;
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $tambahan1 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";

                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaitambahan1, 0) . "</td>";
                    $table .= "</tr>";
                    $total1 += $nilaitambahan1;
                }
                if ($tambahan2 != '') {
                    $norutx++;
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $tambahan2 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";

                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaitambahan2, 0) . "</td>";
                    $table .= "</tr>";

                    $total1 += $nilaitambahan2;
                }
                $table .= "<tr>";
                $table .= "<td colspan=2 align=right></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>____________________________</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td colspan=2 align=right></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp." . number_format($total1, 0) . "</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td colspan=3><b>C.Kewajiban/Pengembalian</b></td>";
                $table .= "</tr>";
                $norutx = 1;
                $totalnilaijenispengembalian = 0;
                if ($jenispengembalian1 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian1 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian1) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian1;
                    $norutx++;
                }

                if ($jenispengembalian2 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian2 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian2) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian2;
                    $norutx++;
                }

                if ($jenispengembalian3 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian3 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian3) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian3;
                    $norutx++;
                }

                if ($jenispengembalian4 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian4 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian4) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian4;
                    $norutx++;
                }

                $table .= "<tr>";
                $table .= "<td colspan=2 align=right></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>____________________________</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td colspan=2 align=right></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>TOTAL POTONGAN&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp." . number_format(($totalnilaijenispengembalian)) . "</b></td>";
                $table .= "</tr>";

                $table .= "<tr>";
                $table .= "<td colspan=2 style='font-size:14px;'><b>D.DITERIMA</b></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:14px;'>Rp." . number_format(($total1 - ($totalnilaijenispengembalian))) . "</td>";
                $table .= "</tr>";
                $table .= "</table><p></p><p></p>";

                $table .= "<table style='width:500px;'";
                $table .= "<tr>";
                $ttot = ($total1 - ($totalnilaijenispengembalian));
                $table .= "<td colspan=2 style='width:400px;font-size:12px;'><b>Terbilang : " . terbilang($ttot, '') . " rupiah</b></td><td></td>";
                $table .= "</tr>";
                $table .= "</table>";

                $table .= "<p></p><table style='width:500px;'";
                $table .= "<tr>";
                $table .= "<td colspan=2 style='width:400px;font-size:12px;'>Jakarta,&nbsp;&nbsp;" . tanggalnormal($tanggal) . "</td><td></td>";
                $table .= "</tr>";
                $table .= "</table>";

                $table .= "<p></p><table style='width:100%;'>";
                if (isset($cspanjudul['KANTOR'])) {
                    $table .= "<tr>";

                    for ($i = 0; $i < $cspanjudul['KANTOR']; $i++) {
                        // if($i=='0'){
                        //     $table .= "<td style='font-size:12px;'>Dibuat Oleh :</td>";
                        // }elseif($i=='1' and $i==$cspanjudul['KANTOR']){
                        //     $table .= "<td style='font-size:12px;'>Disetujui Oleh :</td>";
                        // }elseif($i=='1' and $i!=$cspanjudul['KANTOR']){
                        //     $table .= "<td style='font-size:12px;'>Diperiksa Oleh :</td>";
                        // }elseif($i>'1' and $i!=$cspanjudul['KANTOR']){
                        //     $table .= "<td style='font-size:12px;'>Diperiksa Oleh :</td>";
                        // }else{
                        //     $table .= "<td style='font-size:12px;'>Disetujui Oleh :</td>";
                        // }
                        $table .= "<td style='font-size:12px;'>" . $ket['KANTOR'][$i] . "</td>";
                    }

                    $table .= "</tr>";
                    $table .= "<tr>";

                    for ($i = 0; $i < $cspanjudul['KANTOR']; $i++) {

                        $table .= "<td style='font-size:12px;height:35px;'></td>";
                    }




                    $table .= "</tr>";
                    $table .= "<tr>";

                    for ($i = 0; $i < $cspanjudul['KANTOR']; $i++) {
                        $table .= "<td style='font-size:12px;'>" . $arrkaryawanid['KANTOR'][$i] . "</td>";
                    }




                    $table .= "</tr>";

                    // $table .= "<tr>";

                    //     for ($i=0; $i < $cspanjudul['KANTOR']; $i++) { 
                    //         $table .= "<td style='font-size:12px;'>".getNamaJabatan(getKary($arrkaryawanid['KANTOR'][$i],'kodejabatan'))."</td>";
                    //     }




                    // $table .= "</tr>"; 
                }
                $table .= "</table>";
                break;
            case 'DPLK':
                $table .= "<table border='0' align='center'>";
                $table .= "<tr><td style='text-align:center;font-size:14px;'><b>Rincian Biaya</b></td></tr>";
                $table .= "<tr><td style='text-align:center;font-size:14px;'><b>Pemutusan Hubungan Kerja</b></td></tr>";
                $table .= "</table>";

                $table .= "<table border='0' style='width:100%;margin-top:20px'>";
                $table .= "<tr>";
                $table .= "<td colspan=4 style='font-size:14px;'><b>A.IDENTITA PEGAWAI</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>1.</td>";
                $table .= "<td style='width:450px;vertical-align:top;font-size:12px;'>&nbsp;<b>" . $_SESSION['lang']['nama'] . "</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $bkodept->namakaryawan . "</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>2.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;" . $_SESSION['lang']['nik'] . "</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . $bkodept->nik . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top'>3.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Jabatan</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . getNamaJabatan($bkodept->kodejabatan) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top'>5.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Divisi</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . getNamaOrg($bkodept->lokasitugas) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>6.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Tempat/Tanggal Lahir</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . $bkodept->tempatlahir . "/" . tanggalnormal($bkodept->tanggallahir) . "</td>";
                $table .= "</tr>";
                $tgl1   = new DateTime($bkodept->tanggallahir);
                $tgl2   = new DateTime($tanggalberhenti);
                $jarak  = $tgl2->diff($tgl1);

                $usiatahun = addZero($jarak->y, 2);
                $usiabulan = addZero($jarak->m, 2);
                $usiahari  = addZero($jarak->d, 2);
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>7.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Usia</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $usiatahun . " Tahun " . $usiabulan . " Bulan " . $usiahari . " Hari</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>8.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Tanggal Masuk Kerja</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . tanggalnormal($bkodept->tanggalmasuk) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'>9.</td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;Tanggal Keluar Kerja</td>";
                $table .= "<td style='width:5px;font-size:12px;'>:</td>";
                $table .= "<td style='font-size:12px;'>&nbsp;" . tanggalnormal($tanggalberhenti) . "</td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>10.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Masa Kerja</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $masakerjatahun . " Tahun " . $masakerjabulan . " Bulan " . $masakerjahari . " Hari</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>11.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Upah Terakhir</b></td>";
                $table .= "<td style='width:5px;vertical-align:top;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>Rp." . number_format(($upahterakhir), 0) . "</b></td>";
                $table .= "</tr>";
                $arrjenis = makeOption($dbname, 'sdm_5jenispesangon', 'kode,jenis');
                $table .= "<tr>";
                $table .= "<td style='width:5px;text-align:center;vertical-align:top;font-size:12px;'><b>12.</b></td>";
                $table .= "<td style='width:80px;vertical-align:top;font-size:12px;'>&nbsp;<b>Alasan</b></td>";
                $table .= "<td style='width:5px;font-size:12px;'><b>:</b></td>";
                $table .= "<td style='font-size:12px;'>&nbsp;<b>" . $arrjenis[$jenis] . "</b></td>";
                $table .= "</tr>";
                $table .= "</table>";

                $table .= "<table border='0' style='margin-top:15px;width:550px'>";
                $table .= "<tr>";
                $table .= "<td colspan=3><b>B.Kompensasi</b></td>";
                $table .= "</tr>";
                $norutx = 1;
                $total1 = 0;
                $totalupajak = 0;
                if ($textuangpisah != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . ".Uang Pisah</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";

                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;' id='textuangpisah'>" . $textuangpisah . "</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($uangpisah, 0) . "</td>";
                    $table .= "</tr>";
                    $total1 += $uangpisah;
                    $totalupajak += $uangpisah;
                    $norutx++;
                } else {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2>&nbsp;</td>";
                    $table .= "</tr>";
                    $table .= "<tr>";

                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>&nbsp;</td>";
                    $table .= "</tr>";
                    $norutx++;
                }

                if ($textupmk != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . ".Uang Penghargaan Masa Kerja</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";

                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;' id='textupmk'>" . $textupmk . "</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($upmk, 0) . "</td>";
                    $table .= "</tr>";
                    $total1 += $upmk;
                    $totalupajak += $upmk;
                    $norutx++;
                } else {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2>&nbsp;</td>";
                    $table .= "</tr>";
                    $table .= "<tr>";

                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:12px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>&nbsp;</td>";
                    $table .= "</tr>";
                    $norutx++;
                }


                if ($textupmk == '' and $textuangpisah == '') {
                    $table .= "<tr>";
                    $table .= "<td colspan=2 align=right>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>&nbsp;</td>";
                    $table .= "</tr>";
                } else {
                    $table .= "<tr>";
                    $table .= "<td colspan=2 align=right></td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp." . number_format($total1, 0) . "</b></td>";
                    $table .= "</tr>";
                }
                $table .= "<tr>";
                $table .= "<td colspan=3><b>C.Kewajiban/Pengembalian</b></td>";
                $table .= "</tr>";
                $norutx = 1;
                $totalnilaijenispengembalian = 0;
                if ($jenispengembalian1 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian1 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian1) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian1;
                    $norutx++;
                }

                if ($jenispengembalian2 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian2 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian2) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian2;
                    $norutx++;
                }

                if ($jenispengembalian3 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian3 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian3) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian3;
                    $norutx++;
                }

                if ($jenispengembalian4 != '') {
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:left;vertical-align:top;font-size:14px;' colspan=2><b>" . $norutx . "." . $jenispengembalian4 . "</b></td>";
                    $table .= "</tr>";
                    $table .= "<tr>";
                    $table .= "<td style='width:20px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;'>&nbsp;</td>";
                    $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($nilaijenispengembalian4) . "</td>";
                    $table .= "</tr>";
                    $totalnilaijenispengembalian += $nilaijenispengembalian4;
                    $norutx++;
                }


                $table .= "<tr>";
                $table .= "<td colspan=3><b>D.Pajak</b></td>";
                $table .= "</tr>";
                $norutx = 1;
                $table .= "<tr>";
                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;text-align:left;vertical-align:top;;font-size:14px;' colspan=2><b>" . $norutx . ".Pajak</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";

                $table .= "<td style='width:20px;'>&nbsp;</td>";
                $table .= "<td style='width:5px;'>&nbsp;</td>";

                $wherexxx = '';
                if ($maxpajak != '0' and $maxpajak != '') {
                    $wherexxx = " and persentase<'" . $maxpajak . "'";
                }

                $query       = "SELECT * FROM $dbname.sdm_5pajakpesangon WHERE kodept = '" . $bkodept->kodeorganisasi . "' and  penghasilan<'" . $totalupajak . "'" . $wherexxx . " order by penghasilan asc";
                //echo $query;
                $result      = fetchData($query);
                $jumlahres = count($result);
                $tambahan = 0;
                $nilaipajak = '';
                $noxzx = 1;
                if (count($result) > 0) {
                    foreach ($result as $value) {
                        if ($nilaipajak == '') {
                            $nilaipajak = $value['penghasilan'];
                            $tambahan += $nilaipajak * $value['persentase'];
                            //echo $value['penghasilan']." ke =".$noxzx.' =>'.($nilaipajak*$value['persentase']).'<br>';
                        } else {
                            $tambahan += ($value['penghasilan'] - $nilaipajak) * $value['persentase'];
                            //echo $value['penghasilan'].'-'.$nilaipajak." ke =".$noxzx.' =>'.(($value['penghasilan']-$nilaipajak)*$value['persentase']).'<br>';
                            if ($noxzx <= $jumlahres) {
                                $nilaipajak = $nilaipajak + ($value['penghasilan'] - $nilaipajak);
                            }
                        }
                        $noxzx++;
                    }
                }

                $wherexxx = '';
                if ($maxpajak != '0' and $maxpajak != '') {
                    $wherexxx = " and persentase='" . $maxpajak . "'";
                } else {
                    $wherexxx = " and  penghasilan>='" . $totalupajak . "'";
                }

                $query       = "SELECT * FROM $dbname.sdm_5pajakpesangon WHERE kodept = '" . $bkodept->kodeorganisasi . "' " . $wherexxx . " order by penghasilan asc limit 1";
                $result      = fetchData($query);
                $pajak = 0;
                if (count($result) > 0) {
                    if ($nilaipajak == '') {
                        $nilaipajak = 0;
                    }
                    foreach ($result as $value) {
                        $pajak = (($totalupajak - $nilaipajak) * $value['persentase']) + $tambahan;
                        // echo $value['persentase'].' ke =terakhir =>'.(($totalupajak-$nilaipajak)*$value['persentase']).'<br>';

                    }
                }
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'>Rp." . number_format($pajak, 0) . "</td>";
                $table .= "</tr>";
                $norutx++;
                $table .= "<tr>";
                $table .= "<td colspan=2 align=right></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>____________________________</b></td>";
                $table .= "</tr>";
                $table .= "<tr>";
                $table .= "<td colspan=2 align=right></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:12px;'><b>TOTAL POTONGAN&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp." . number_format(($pajak + $totalnilaijenispengembalian)) . "</b></td>";
                $table .= "</tr>";

                $table .= "<tr>";
                $table .= "<td colspan=2 style='font-size:14px;'><b>E.DITERIMA</b></td>";
                $table .= "<td style='width:5px;text-align:right;vertical-align:top;font-size:14px;'>Rp." . number_format(($total1 - ($pajak + $totalnilaijenispengembalian))) . "</td>";
                $table .= "</tr>";
                $table .= "</table><p></p><p></p>";

                $table .= "<table style='width:500px;'";
                $table .= "<tr>";
                $ttot = ($total1 - ($pajak + $totalnilaijenispengembalian));
                $table .= "<td colspan=2 style='width:400px;font-size:12px;'><b>Terbilang : " . terbilang($ttot, '') . " rupiah</b></td><td></td>";
                $table .= "</tr>";
                $table .= "</table>";

                $table .= "<p></p><table style='width:500px;'";
                $table .= "<tr>";
                $table .= "<td colspan=2 style='width:400px;font-size:12px;'>Jakarta,&nbsp;&nbsp;" . tanggalnormal($tanggal) . "</td><td></td>";
                $table .= "</tr>";
                $table .= "</table>";

                $table .= "<p></p><table style='width:100%;'>";
                if (isset($cspanjudul['DPLK'])) {
                    $table .= "<tr>";

                    for ($i = 0; $i < $cspanjudul['DPLK']; $i++) {
                        // if($i=='0'){
                        //     $table .= "<td style='font-size:12px;'>Dibuat Oleh :</td>";
                        // }elseif($i=='1' and $i==$cspanjudul['DPLK']){
                        //     $table .= "<td style='font-size:12px;'>Disetujui Oleh :</td>";
                        // }elseif($i=='1' and $i!=$cspanjudul['DPLK']){
                        //     $table .= "<td style='font-size:12px;'>Diperiksa Oleh :</td>";
                        // }elseif($i>'1' and $i!=$cspanjudul['DPLK']){
                        //     $table .= "<td style='font-size:12px;'>Diperiksa Oleh :</td>";
                        // }else{
                        //     $table .= "<td style='font-size:12px;'>Disetujui Oleh :</td>";
                        // }
                        $table .= "<td style='font-size:12px;'>" . $ket['DPLK'][$i] . "</td>";
                    }




                    $table .= "</tr>";
                    $table .= "<tr>";

                    for ($i = 0; $i < $cspanjudul['DPLK']; $i++) {

                        $table .= "<td style='font-size:12px;height:35px;'></td>";
                    }




                    $table .= "</tr>";
                    $table .= "<tr>";

                    for ($i = 0; $i < $cspanjudul['DPLK']; $i++) {
                        $table .= "<td style='font-size:12px;'>" . $arrkaryawanid['DPLK'][$i] . "</td>";
                    }




                    $table .= "</tr>";

                    // $table .= "<tr>";

                    //     for ($i=0; $i < $cspanjudul['DPLK']; $i++) { 
                    //         $table .= "<td style='font-size:12px;'>".getNamaJabatan(getKary($arrkaryawanid['DPLK'][$i],'kodejabatan'))."</td>";
                    //     }




                    // $table .= "</tr>"; 
                }
                $table .= "</table>";
                break;
        }


        //echo $table;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($table);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();


        // $canvas = $dompdf->get_canvas();
        // $canvas->page_text(16, 800, "Page: {PAGE_NUM} of {PAGE_COUNT}",'', 8, array(0,0,0));

        $dompdf->stream('', array("Attachment" => 0));
        break;

    case 'showPDFNewv2':
        $query  = "SELECT * FROM $dbname.sdm_pesangon WHERE noid = '" . $param['noid'] . "'";
        $result = fetchData($query, 'OBJECT');
        foreach ($result as $key => $value) {
            $noid = $value->noid;
            $karyawanid = $value->karyawanid;
            $pihakpertama = $value->pihakpertama;
            $kodeunit = $value->kodeunit;
            $tanggal = $value->tanggal;
            $tanggalberhenti = $value->tanggalberhenti;
            $tanggalmasuk = $value->tanggalmasuk;
            $masakerjatahun = $value->masakerjatahun;
            $masakerjabulan = $value->masakerjabulan;
            $masakerjahari = $value->masakerjahari;
            $upahterakhir = $value->upahterakhir;
            $jenis = $value->jenis;
            $textuangpisah = $value->textuangpisah;
            $uangpisah = $value->uangpisah;
            $textupmk = $value->textupmk;
            $upmk = $value->upmk;
            $textcuti = $value->textcuti;
            $cutitahunan = $value->cutitahunan;
            $pembagigajicuti = $value->pembagigajicuti;
            $rupiahcutitahunan = $value->rupiahcutitahunan;
            $textkesehatan = $value->textkesehatan;
            $proporsikesehatan = $value->proporsikesehatan;
            $pengalikesehatan = $value->pengalikesehatan;
            $rupiahkesehatan = $value->rupiahkesehatan;
            $tambahan1 = $value->tambahan1;
            $tambahan2 = $value->tambahan2;
            $nilaitambahan1 = $value->nilaitambahan1;
            $nilaitambahan2 = $value->nilaitambahan2;
            $rupiahtotal1 = $value->rupiahtotal1;
            $jenispengembalian1 = $value->jenispengembalian1;
            $jenispengembalian2 = $value->jenispengembalian2;
            $jenispengembalian3 = $value->jenispengembalian3;
            $jenispengembalian4 = $value->jenispengembalian4;
            $nilaijenispengembalian1 = $value->nilaijenispengembalian1;
            $nilaijenispengembalian2 = $value->nilaijenispengembalian2;
            $nilaijenispengembalian3 = $value->nilaijenispengembalian3;
            $nilaijenispengembalian4 = $value->nilaijenispengembalian4;
            $nilaipajak = $value->nilaipajak;
            $rupiahtotalpotongan = $value->rupiahtotalpotongan;
            $rupiahditerima = $value->rupiahditerima;
            $posting = $value->posting;
            $createby = $value->createby;
            $createtime = $value->createtime;
            $updatetime = $value->updatetime;
            $updateby = $value->updateby;
        }

        $query  = "SELECT * FROM $dbname.sdm_5jenispesangon WHERE jenis = '" . $jenis . "'";
        $result = fetchData($query, 'OBJECT');
        $statuspajak = $result[0]->statuspajak;
        $maxpajak = $result[0]->maxpajak;

        $skodept = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $karyawanid . "'";
        $rkodept = $owlPDO->query($skodept) or die(print " Gagal: " . PDOException::getMessage());
        $rkodept->setFetchMode(PDO::FETCH_OBJ);
        $bkodept = $rkodept->fetch();
        if (!$bkodept->kodeorganisasi) {
            exit("ERROR: Data Karyawan not Found!");
        }
        if (!$masakerjatahun) {
            exit("ERROR: Date not Found!");
        }

        $param['tglmasuk'] = $bkodept->tanggalmasuk;
        $table = '';
        $str = "select id, caption, caption2, caption3 from " . $dbname . ".menu where action='sdm_pesangon'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $menuid = $bar['id'];
        }
        $cspanjudul = array();
        $arrkaryawanid = array();
        $ket = array();
        $str = "select * from " . $dbname . ".sdm_5ttdpesangon where unit = '" . $kodeunit . "' ORDER by unit, level";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            @$cspanjudul[$bar['tipe']] += 1;
            @$arrkaryawanid[$bar['tipe']][] = $bar['karyawan'];
            @$ket[$bar['tipe']][] = $bar['keterangan'];
        }

        $table .= "
            <style>
                @page {margin: 0;}
                body {margin-top: 20px;margin-left:40px;margin-right:40px;padding: 0;font-family: Arial, sans-serif;font-size: 13px;color: #000;}
                .kop-table {width: 100%;}
                .kop-table td {vertical-align: top;}
                .kop-logo {width: 450px;}
                .kop-info {magin-left:200px;font-size: 12.5px;line-height: 1.5;}
                .label {display: inline-block;width: 100px;font-weight: normal;}
                .divider-wrapper {margin-top: 12px;}
                .divider-thin {border-top: 1px solid black;}  
                .divider-thick {border-top: 3px solid black;margin-bottom: 2px;}
            </style>";

        $ptKary = getNamaOrg(makeOption($dbname, "organisasi", "kodeorganisasi,induk", "kodeorganisasi='{$kodeunit}'")[$kodeunit]);
        $pt = $_SESSION['org']['kodeorganisasi'];

        $logo = getLogoImage($pt);
        $qHORO = selectQuery($dbname, "organisasi", "tipe, alamat, telepon, email, fax", "induk='{$pt}' AND tipe IN ('HOLDING', 'KANWIL')");
        $rHORO = fetchData($qHORO);
        $alamatHO = "";
        $alamatRO = "";
        foreach ($rHORO as $row) {
            if ($row['tipe'] == 'HOLDING') {
                $alamatHO .= $row['alamat'];
            }
            if ($row['tipe'] == 'KANWIL') {
                $alamatRO .= $row['alamat'];
            }
        }

        $qOrg = selectQuery($dbname, "organisasi", "tipe, alamat, telepon, email, fax", "induk='{$pt}' AND kodeorganisasi='{$kodeunit}' AND tipe NOT IN ('HOLDING', 'KANWIL')");
        $rOrg = fetchData($qOrg);
        if (!empty($rOrg)) {
            $divOrg = "<div style='margin-top: 6px;'>";
            foreach ($rOrg as $row) {
                $divOrg .= "<span class='label'>" . ucwords($row['tipe']) . "</span>: " . $row['alamat'];
            }
            $divOrg .= "</div>";
        }


        $table .= "
            <table class='kop-table'>
                <tr>
                    <td class='kop-logo'>
                        <img src='{$logo}' width='200' height='100'>
                    </td>
                    <td class='kop-info'>
                        <div>
                            <span class='label'>Kantor Pusat</span>: {$alamatHO}
                        </div>
                        {$divOrg}
                        <div style='margin-top: 6px;'>
                            <span class='label'>Kantor Cabang</span>: {$alamatRO}
                        </div>
                    </td>
                </tr>
            </table>

            <div class='divider-wrapper'>
                <div class='divider-thick'></div>
                <div class='divider-thin'></div>
            </div><br/>";

        $expTanggallahir = explode("-", $bkodept->tanggallahir);
        $tglLahir = (
            (strlen($expTanggallahir[2]) == 1 ? "0" . $expTanggallahir[2] : $expTanggallahir[2]) . " " .
            numToMonth($expTanggallahir[1], "I", "long") . " " .
            $expTanggallahir[0]
        );

        $expTanggalmasuk = explode("-", $bkodept->tanggalmasuk);
        $tglMasuk = (
            (strlen($expTanggalmasuk[2]) == 1 ? "0" . $expTanggalmasuk[2] : $expTanggalmasuk[2]) . " " .
            numToMonth($expTanggalmasuk[1], "I", "long") . " " .
            $expTanggalmasuk[0]
        );

        $expTanggalberhenti = explode("-", $tanggalberhenti);
        $tglBerhenti = (
            (strlen($expTanggalberhenti[2]) == 1 ? "0" . $expTanggalberhenti[2] : $expTanggalberhenti[2]) . " " .
            numToMonth($expTanggalberhenti[1], "I", "long") . " " .
            $expTanggalberhenti[0]
        );

        $lahir = new DateTime($bkodept->tanggallahir);
        $tglpesangon = new DateTime($tanggal);
        $diff = $lahir->diff($tglpesangon);
        $usia = "{$diff->y} Tahun {$diff->m} Bulan {$diff->d} Hari";

        $tanggalmasuk = new DateTime($bkodept->tanggalmasuk);
        $tanggalberhenti = new DateTime($tanggalberhenti);
        $diff = $tanggalmasuk->diff($tanggalberhenti);
        $masakerja = "{$diff->y} Tahun {$diff->m} Bulan {$diff->d} Hari";

        $total = 75257150;

        $table .= "
            <table border='0' align='center' style='width:100%'>
                <tr>
                    <th style='text-align:center;text-decoration:underline;text-transform:uppercase'>PERHITUNGAN KOMPENSASI PHK PENSIUN</th>
                </tr>
            </table>
            <table border='0' cellpadding='1' cellspacing='1' style='width:100%'>
                <tr>
                    <td style='width:150px'>Nama</td>
                    <td style='width:10px'>:</td>
                    <td>{$bkodept->namakaryawan}</td>
                </tr>
                <tr>
                    <td style='width:150px'>Tempat/Tanggal Lahir</td>
                    <td style='width:10px'>:</td>
                    <td>{$bkodept->tempatlahir}, {$tglLahir}</td>
                </tr>
                <tr>
                    <td style='width:150px'>Usia</td>
                    <td style='width:10px'>:</td>
                    <td>{$usia}</td>
                </tr>
                <tr>
                    <td style='width:150px'>Jenis Kelamin</td>
                    <td style='width:10px'>:</td>
                    <td>" . ($bkodept->jeniskelamin == "P" ? "Pria"  : "Wanita") . "</td>
                </tr>
                <tr>
                    <td style='width:150px'>Tanggal Masuk Kerja</td>
                    <td style='width:10px'>:</td>
                    <td>{$tglMasuk}</td>
                </tr>
                <tr>
                    <td style='width:150px'>Tanggal PHK</td>
                    <td style='width:10px'>:</td>
                    <td>{$tglBerhenti}</td>
                </tr>
                <tr>
                    <td style='width:150px'>Masa Kerja</td>
                    <td style='width:10px'>:</td>
                    <td>{$masakerja}</td>
                </tr>
                <tr>
                    <td style='width:150px'>Jabatan/Divisi</td>
                    <td style='width:10px'>:</td>
                    <td>" . getNamaJabatan($bkodept->kodejabatan) . "</td>
                </tr>
                <tr>
                    <td style='width:150px'>Status/Golongan</td>
                    <td style='width:10px'>:</td>
                    <td>" . getNamaTipekaryawan($bkodept->karyawanid) . "</td>
                </tr>
                <tr>
                    <td style='width:150px'>Gaji Pokok</td>
                    <td style='width:10px'>:</td>
                    <td>Rp. " . number_format($upahterakhir) . "</td>
                </tr>
            </table>";

        $table .= "<br/>";

        $table .= "
            <table border='0' cellpadding='1' cellspacing='3' style='width:100%'>
                <tr>
                    <th style='text-transform:uppercase'>DASAR KETENTUAN :</th>
                </tr>
                <tr>
                    <td>
                        <span>1. Peraturan Pemerintah Republik Indonesia No. 35 Tahun 2021 Pasal 56</span>
                        <br/>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;Tentang Hubungan Kerja Berakhir Karena Pensiun</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>2. Peraturan Pemerintah Republik Indonesia No. 35 Tahun 2021 Pasal 40</span>
                        <br/>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;Tentang Perhitungan Kompensasi PHK Pensiun</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>3. Peraturan Perusahaan PT. Dwi Mitra Adhiusaha Tahun 2024 - 2026 Pasal</span>
                        <br/>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;Tentang Usia Pensiun di perusahaan {$ptKary}</span>
                    </td>
                </tr>
            </table>";

        $table .= "<br/>";

        $table .= "
            <table border='0' cellpadding='1' cellspacing='3' style='width:100%'>
                <tr>
                    <th style='text-transform:uppercase' colspan='2'>PERHITUNGAN KOMPENSASI</th>
                </tr>

                <tr><td colspan='2'>1. Peraturan Pemerintah Republik Indonesia No. 35 Tahun 2021 Pasal 56 a</td></tr>
                <tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;Pesangon PHK Pensiun</td></tr>
                <tr style='font-weight:bold'>    
                    <td style='width:500px'>&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp; 1,75 x 9 Bulan Upah (Masa Kerja 12 - 13 Tahun) x Rp. 3.555,OOO,-</td>
                    <td> = &nbsp;&nbsp;&nbsp;RP &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 55,148,750</td>
                </tr>

                <tr><td colspan='2'>2. Peraturan Pemerintah Republik Indonesia No. 35 Tahun 2021 Pasal 56 b</td></tr>
                <tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;Uang Penghargaan Masa Kerja</td></tr>
                <tr style='font-weight:bold'>    
                    <td style='width:500px'>&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp; 1x 5 Bulan Upah (Masa Kerja 12 - 15 Tahun ) x Rp.3.565.000,-</td>
                    <td> = &nbsp;&nbsp;&nbsp;RP &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 17.825.OOO</td>
                </tr>

                <tr><td colspan='2'>3. Peraturan Pemerintah Republik Indonesia No. 35 Tahun 2021 Pasal 56 c</td></tr>
                <tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;Uang Penggantian Hak</td></tr>
                
                <tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp; Cuti yang belum gugur</td></tr>
                <tr style='font-weight:bold'>    
                    <td style='width:500px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 9/25 x Rp.3.565.000,-</td>
                    <td> = &nbsp;&nbsp;&nbsp;RP &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1.283.4OO</td>
                </tr>

                <tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp; Biaya / ongkos pulang Pekerja/Buruh dan Keluarganya ke tempat dimana Pekerja/Buruh diterima bekerja</td></tr>
                <tr style='font-weight:bold'>    
                    <td style='width:500px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Penerimaan di lokasi perusahaan {$ptKary}</td>
                    <td> = &nbsp;&nbsp;&nbsp;RP &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -</td>
                </tr>

                <tr><td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp; Upah Terakhir</td></tr>
                <tr style='font-weight:bold'>    
                    <td style='width:500px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Dibayar melalui Payroll Mei 2O25</td>
                    <td> = &nbsp;&nbsp;&nbsp;RP &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -</td>
                </tr>
            </table>";

        $table .= "<br/>";

        $table .= "
            <table border='0' cellpadding='1' cellspacing='3' style='width:100%'>
                <tr style='font-weight:bold'>
                    <td style='width:500px'>Total</td>
                    <td> = &nbsp;&nbsp;&nbsp;RP &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 75.257.15O</td>
                </tr>
            </table>";

        $table .= "<br/>";

        $table .= "
            <table border='0' cellpadding='1' cellspacing='3' style='width:100%'>
                <tr style='font-weight:bold;font-style:italic;'>
                    <td>Terbilang : " . terbilang($total, 3) . "</td>
                </tr>
            </table>";

        # Approve
        $sql = "SELECT penyetuju, pemeriksa, createby FROM {$dbname}.sdm_pesangon WHERE noid = '{$param['noid']}' LIMIT 1";
        $dt = fetchData($sql);
        if (empty($dt)) return '';

        $penyetuju = $dt[0]['penyetuju'];
        $pemeriksa = $dt[0]['pemeriksa'];
        $dibuatoleh = $dt[0]['createby'];

        list($namaCreate, $jabatanCreate) = getNamaDanJabatan($dibuatoleh);
        list($namaPemeriksa, $jabatanPemeriksa) = getNamaDanJabatan($pemeriksa);
        list($namaPenyetuju, $jabatanPenyetuju) = getNamaDanJabatan($penyetuju);

        $tableApprv = "<table width='100%' border='0'>";
        $tableApprv .= "<tr><td colspan='3' height='20'>&nbsp;</td></tr>";
        $tableApprv .= "
                <tr class='rowcontent'>
                    <td align='center'>Disetujui oleh,</td>
                    <td align='center'>Diperiksa oleh,</td>
                    <td align='center'>Dibuat oleh,</td>
               </tr>";
        $tableApprv .= "<tr><td colspan='3' height='40'>&nbsp;</td></tr>";
        $tableApprv .= "
                <tr class='rowcontent'>
                    <td align='center'><u>{$namaPenyetuju}</u></td>
                    <td align='center'><u>{$namaPemeriksa}</u></td>
                    <td align='center'><u>{$namaCreate}</u></td>
               </tr>";
        $tableApprv .= "
                <tr class='rowcontent'>
                    <td align='center'>{$jabatanPenyetuju}</td>
                    <td align='center'>{$jabatanPemeriksa}</td>
                    <td align='center'>{$jabatanCreate}</td>
               </tr>";
        $tableApprv .= "</table>";

        $table .= $tableApprv;

        $dompdf = new Dompdf();
        $dompdf->loadHtml($table);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $dompdf->stream('', array("Attachment" => 0));
        break;

    default:
        break;
}

function getNamaDanJabatan($karyawanid)
{
    global $dbname;
    $q = "SELECT namakaryawan, kodejabatan FROM {$dbname}.datakaryawan WHERE karyawanid = '{$karyawanid}' LIMIT 1";
    $res = fetchData($q);
    if (!empty($res)) {
        $nama = ucwords(strtolower($res[0]['namakaryawan']));
        $jabatanid = $res[0]['kodejabatan'];
        $qj = "SELECT namajabatan as nama FROM {$dbname}.sdm_5jabatan WHERE kodejabatan = '{$jabatanid}' LIMIT 1";
        $resj = fetchData($qj);
        $jabatan = !empty($resj) ? ucwords(strtolower($resj[0]['nama'])) : '';
        return [$nama, $jabatan];
    }
    return ['___________', ''];
}
