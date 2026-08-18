<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$proses = $_GET['proses'];
$param = $_POST;
$tmpPeriod = explode('-', $param['periode']);
$tahunbulan = implode("", $tmpPeriod);
$maxDay = cal_days_in_month(CAL_GREGORIAN, $tmpPeriod[1], $tmpPeriod[0]);

$tmpPeriodLalu = explode('-', periodelalu($param['periode']));
$tahunbulanLalu = implode("", $tmpPeriodLalu);
$maxDayLalu = cal_days_in_month(CAL_GREGORIAN, $tmpPeriodLalu[1], $tmpPeriodLalu[0]);

$tipeorganisasi = makeOption($dbname, "organisasi", "kodeorganisasi,tipe");

// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// exit('error');
/**
 * Proses Kurs
 */
// Get Kurs Mata Uang
$qKursLalu = selectQuery($dbname, 'setup_matauangrate', "*", "DAY(daritanggal) in ('1','" . $maxDayLalu . "')");
$resKursLalu = fetchData($qKursLalu);
$tmpKursLalu = array();
foreach ($resKursLalu as $row) {
        $tmpTglLalu = explode('-', $row['daritanggal']);
        $tmpKursLalu[$row['kode']][$tmpTglLalu[2]] = $row['kurs'];
}

$qKurs = selectQuery($dbname, 'setup_matauangrate', "*", "DAY(daritanggal) in ('1','" . $maxDay . "')");
$resKurs = fetchData($qKurs);
$tmpKurs = array();
foreach ($resKurs as $row) {
        $tmpTgl = explode('-', $row['daritanggal']);
        $tmpKurs[$row['kode']][$tmpTgl[2]] = $row['kurs'];
}

// Arrange Kurs yang memiliki selisih
$currSelisihLalu = array();
foreach ($tmpKursLalu as $curr => $row) {
        if (isset($row['01']) and isset($row[$maxDayLalu])) {
                // $currSelisihLalu[$curr] = $row[$maxDayLalu] - $row['01'];
                $currSelisihLalu[$curr] = $row[$maxDayLalu];
        }
}

$currSelisih = array();
foreach ($tmpKurs as $curr => $row) {
        if (isset($row['01']) and isset($row[$maxDay])) {
                // $currSelisih[$curr] = $row[$maxDay] - $row['01'];
                $currSelisih[$curr] = $row[$maxDay];
        }
}

/**
 * Proses Get Jurnal
 */
// Get Opt Akun Kas Bank
$optAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "kasbank=1");

// Get Sum Transaaksi Kas Bank per Currency
$qTrans = "SELECT noakun,tipetransaksi,matauang,sum(jumlah) as jumlah FROM " . $dbname . ".keu_kasbankht
        WHERE left(notransaksi,6) = '" . $tahunbulan . "' and matauang != 'IDR'
        and kodeorg='" . $param['kodeorg'] . "'
        GROUP BY noakun,tipetransaksi,matauang";
$resTrans = fetchData($qTrans);
// Rearrange Data
$data = array();
foreach ($resTrans as $row) {
        if (!isset($data[$row['matauang']][$row['noakun']])) {
                $data[$row['matauang']][$row['noakun']] = 0;
        }
        if ($row['tipetransaksi'] == 'M') {
                // $data[$row['matauang']][$row['noakun']] += $row['jumlah'] * $currSelisih[$row['matauang']];
                $data[$row['matauang']][$row['noakun']] += $row['jumlah'];
        } elseif ($row['tipetransaksi'] == 'K') {
                // $data[$row['matauang']][$row['noakun']] -= $row['jumlah'] * $currSelisih[$row['matauang']];
                $data[$row['matauang']][$row['noakun']] -= $row['jumlah'];
        }
}

$qTrans = "SELECT noakun,tipetransaksi,matauang,sum(jumlah*kurs) as jumlah FROM " . $dbname . ".keu_kasbankht
        WHERE left(notransaksi,6) = '" . $tahunbulan . "' and matauang != 'IDR'
        and kodeorg='" . $param['kodeorg'] . "'
        GROUP BY noakun,tipetransaksi,matauang";
$resTrans = fetchData($qTrans);
// Rearrange Data
$dataMutasi = array();
foreach ($resTrans as $row) {
        if (!isset($dataMutasi[$row['matauang']][$row['noakun']])) {
                $dataMutasi[$row['matauang']][$row['noakun']] = 0;
        }
        if ($row['tipetransaksi'] == 'M') {
                // $dataMutasi[$row['matauang']][$row['noakun']] += $row['jumlah'] * $currSelisih[$row['matauang']];
                $dataMutasi[$row['matauang']][$row['noakun']] += $row['jumlah'];
        } elseif ($row['tipetransaksi'] == 'K') {
                // $dataMutasi[$row['matauang']][$row['noakun']] -= $row['jumlah'] * $currSelisih[$row['matauang']];
                $dataMutasi[$row['matauang']][$row['noakun']] -= $row['jumlah'];
        }
}

switch ($proses) {
        case 'list':

                if ($tipeorganisasi[$_SESSION['empl']['lokasitugas']] != 'HOLDING' && $tipeorganisasi[$_SESSION['empl']['lokasitugas']] != 'KANWIL') {
                        exit('<label hidden>Warning</label> Untuk Selisih Kurs, hanya lokasi tugas Holding (HO) yang bisa lakukan Proses');
                }

                // Tampilan
                $tab = "<div>" . makeElement(
                        'postKursBtn',
                        'btn',
                        $_SESSION['lang']['proses'],
                        array('onclick' => 'postKurs()')
                ) . "</div>";
                $tab .= makeElement('kodeorgKurs', 'hid', $param['kodeorg']);
                $tab .= makeElement('periodeKurs', 'hid', $param['periode']);
                $tab .= "<table class=data><thead><tr class=rowheader>";
                $tab .= "<td>" . $_SESSION['lang']['matauang'] . "</td>";
                $tab .= "<td>" . $_SESSION['lang']['noakun'] . "</td>";
                $tab .= "<td>" . $_SESSION['lang']['namaakun'] . "</td>";
                $tab .= "<td>" . $_SESSION['lang']['jumlah'] . "</td>";
                $tab .= "</tr></thead><tbody>";
                foreach ($data as $mtUang => $row1) {
                        $tab .= "<tr class=rowcontent><td colspan=4>" . $mtUang . "</td></tr>";
                        foreach ($row1 as $akun => $jumlah) {
                                $bulan = substr(str_replace("-", "", $param['periode']), 4, 2);
                                $str = "select sum(awal" . $bulan . ") as sawal from " . $dbname . ".keu_saldobulanan where kodeorg='" . $param['kodeorg'] . "' and periode='" . str_replace("-", "", $param['periode']) . "' and noakun='" . $akun . "'";
                                $res = fetchData($str)[0];
                                $sawalAkun = $res['sawal'];

                                $sawalAkunKurs = $sawalAkun / $currSelisihLalu[$mtUang];

                                $jumlah = ($sawalAkunKurs + $jumlah) * $currSelisih[$mtUang] - ($sawalAkun + $dataMutasi[$mtUang][$akun]);

                                $tab .= "<tr class=rowcontent><td></td><td>";
                                $tab .= $akun . "</td>";
                                $tab .= "<td>" . $optAkun[$akun] . "</td>";
                                $tab .= "<td align=right>" . number_format($jumlah, 2) . "</td>";
                        }
                }
                $tab .= "</tbody></table>";
                echo $tab;
                break;
        case 'post':

                if ($tipeorganisasi[$_SESSION['empl']['lokasitugas']] != 'HOLDING' && $tipeorganisasi[$_SESSION['empl']['lokasitugas']] != 'KANWIL') {
                        exit('<label hidden>Warning</label> Untuk Selisih Kurs, hanya lokasi tugas Holding (HO) yang bisa lakukan Proses');
                }

                // Init
                $dataRes['header'] = array();
                $dataRes['detail'] = array();

                // Default Segment
                $defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

                // Kode Jurnal
                $kodeJurnal = "KRS01";

                // No Jurnal
                $nojurnal = $tahunbulan . $maxDay . "/" . $param['kodeorg'] . "/" . $kodeJurnal .
                        "/00001";

                // Get Kurs Parameter Jurnal
                $qAkun = selectQuery(
                        $dbname,
                        'keu_5parameterjurnal',
                        "noakundebet,noakunkredit",
                        "kodeaplikasi='KURS' and jurnalid='" . $kodeJurnal . "'"
                );
                $resAkun = fetchData($qAkun);
                if (empty($resAkun)) exit("Warning: Parameter Akun untuk jurnalid " . $kodeJurnal .
                        " belum ada\nSilahkan hubungi IT dengan melampirkan pesan error ini");

                // Data Header
                $dataRes['header'] = array(
                        'nojurnal' => $nojurnal,
                        'kodejurnal' => $kodeJurnal,
                        'tanggal' => $tahunbulan . $maxDay,
                        'tanggalentry' => date('Ymd'),
                        'posting' => '0',
                        'totaldebet' => '0',
                        'totalkredit' => '0',
                        'amountkoreksi' => '0',
                        'noreferensi' => $kodeJurnal,
                        'autojurnal' => '1',
                        'matauang' => 'IDR',
                        'kurs' => '1',
                        'revisi' => '0'
                );

                // Data Detail
                $noUrut = 1;
                foreach ($data as $mtUang => $row1) {
                        foreach ($row1 as $akun => $jumlah) {
                                $bulan = substr(str_replace("-", "", $param['periode']), 4, 2);
                                $str = "select sum(awal" . $bulan . ") as sawal from " . $dbname . ".keu_saldobulanan where kodeorg='" . $param['kodeorg'] . "' and periode='" . str_replace("-", "", $param['periode']) . "' and noakun='" . $akun . "'";
                                $res = fetchData($str)[0];
                                $sawalAkun = $res['sawal'];

                                $sawalAkunKurs = $sawalAkun / $currSelisihLalu[$mtUang];

                                $jumlah = ($sawalAkunKurs + $jumlah) * $currSelisih[$mtUang] - ($sawalAkun + $dataMutasi[$mtUang][$akun]);

                                $dataRes['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => $tahunbulan . $maxDay,
                                        'nourut' => $noUrut,
                                        'noakun' => $akun,
                                        'keterangan' => 'Selisih Kurs ' . $mtUang . ' untuk ' . $param['kodeorg'] .
                                                ' per ' . $param['periode'],
                                        'jumlah' => $jumlah,
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => $param['kodeorg'],
                                        'kodekegiatan' => '',
                                        'kodeasset' => '',
                                        'kodebarang' => '',
                                        'nik' => '',
                                        'kodecustomer' => '',
                                        'kodesupplier' => '',
                                        'noreferensi' => $kodeJurnal,
                                        'noaruskas' => '',
                                        'kodevhc' => '',
                                        'nodok' => '',
                                        'kodeblok' => '',
                                        'revisi' => '0',
                                        'kodesegment' => $defSegment
                                );
                                $noUrut++;

                                if ($jumlah < 0) {
                                        $tmpAkun = $resAkun[0]['noakundebet'];
                                } else {
                                        $tmpAkun = $resAkun[0]['noakunkredit'];
                                }

                                $dataRes['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => $tahunbulan . $maxDay,
                                        'nourut' => $noUrut,
                                        'noakun' => $tmpAkun,
                                        'keterangan' => 'Selisih Kurs ' . $mtUang . ' untuk ' . $param['kodeorg'] .
                                                ' per ' . $param['periode'],
                                        'jumlah' => $jumlah * (-1),
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => $param['kodeorg'],
                                        'kodekegiatan' => '',
                                        'kodeasset' => '',
                                        'kodebarang' => '',
                                        'nik' => '',
                                        'kodecustomer' => '',
                                        'kodesupplier' => '',
                                        'noreferensi' => $kodeJurnal,
                                        'noaruskas' => '',
                                        'kodevhc' => '',
                                        'nodok' => '',
                                        'kodeblok' => '',
                                        'revisi' => '0',
                                        'kodesegment' => $defSegment
                                );
                                $noUrut++;
                        }
                }

                // Delete Jurnal jika sudah ada
                $errorDB = "";
                $delJurnal = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal . "'");
                try {
                        $owlPDO->exec($delJurnal);
                } catch (PDOException $e) {
                        print " DB Error: " . $e->getMessage();
                        exit();
                }

                // Insert Jurnal Header
                $queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                try {
                        $owlPDO->exec($queryH);
                } catch (PDOException $e) {
                        $errorDB .= "Header : " . $e->getMessage();
                }

                // Insert Jurnal Detail
                if ($errorDB == '') {
                        foreach ($dataRes['detail'] as $key => $dataDet) {
                                $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
                                try {
                                        $owlPDO->exec($queryD);
                                } catch (PDOException $e) {
                                        $errorDB .= "Detail " . $key . " :" . $e->getMessage();
                                        break;
                                }
                        }
                }

                // Rollback
                if ($errorDB != "") {
                        $where = "nojurnal='" . $nojurnal . "'";
                        $queryRB = "delete from `" . $dbname . "`.`keu_jurnalht` where " . $where;
                        try {
                                $owlPDO->exec($queryRB);
                        } catch (PDOException $e) {
                                echo "Rollback 1 Error :" . $e->getMessage();
                        }
                        echo $errorDB;
                }
                break;
        default:
                echo 'Process Undefined\nProcess Undefined\nProcess Undefined';
                break;
}
