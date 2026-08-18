<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$comId = checkPostGet('comId', '');
$kdVhc = checkPostGet('kdVhc', '');
$akun = checkPostGet('akun', '');
$jnsVhc = checkPostGet('jnsVhc', '');
$alokasi = checkPostGet('alokasi', '');
$tipeReport = checkPostGet('tipeReport', 'rekap');
$tglAwalInput = checkPostGet('tglAwal', '');
$tglAkhirInput = checkPostGet('tglAkhir', '');
$tglAwal = $tglAwalInput == '' ? '' : tanggalsystemn($tglAwalInput);
$tglAkhir = $tglAkhirInput == '' ? '' : tanggalsystemn($tglAkhirInput);

$nmJenis = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
$arrPos = array('Operator', 'Helper');

switch ($proses) {
    case 'getJnsVhc':
        $optOrg = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
        $optJnsvhc = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sjnsVhc = "select distinct jenisvhc from " . $dbname . ".vhc_runht where kodeorg='" . substr($comId, 0, 4) . "' group by jenisvhc order by jenisvhc";
        $qjnsVhc = $owlPDO->query($sjnsVhc) or die(print " Gagal: " . PDOException::getMessage());
        $qjnsVhc->setFetchMode(PDO::FETCH_ASSOC);
        while ($rjnsVhc = $qjnsVhc->fetch()) {
            $optJnsvhc .= "<option value='" . $rjnsVhc['jenisvhc'] . "'>" . $rjnsVhc['jenisvhc'] . " - " . $optOrg[$rjnsVhc['jenisvhc']] . "</option>";
        }
        echo $optJnsvhc;
        break;

    case 'getKdvhc':
        $optKvhc = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $whereUnit = '';
        if ($comId != '') {
            $whereUnit = " and kodeorg='" . substr($comId, 0, 4) . "'";
        }
        $skdVhc = "select kodevhc from " . $dbname . ".vhc_runht where jenisvhc='" . $jnsVhc . "' " . $whereUnit . " group by kodevhc order by kodevhc";
        $qkdVhc = $owlPDO->query($skdVhc) or die(print " Gagal: " . PDOException::getMessage());
        $qkdVhc->setFetchMode(PDO::FETCH_ASSOC);
        while ($rkdVhc = $qkdVhc->fetch()) {
            $optKvhc .= "<option value='" . $rkdVhc['kodevhc'] . "'>" . $rkdVhc['kodevhc'] . " [" . getVhc($rkdVhc['kodevhc'], 'detailvhc') . "]</option>";
        }
        echo $optKvhc;
        break;

    case 'get_result':
    case 'excel':
        if ($comId == '') {
            echo "warning:Unit Tidak Boleh Kosong";
            exit();
        }
        if ($tglAkhir == '' || $tglAwal == '') {
            echo "warning:Tanggal Tidak Boleh Kosong";
            exit();
        }
        if (strtotime($tglAwal) > strtotime($tglAkhir)) {
            echo "warning:Tanggal Awal Tidak Boleh Lebih Besar Dari Tanggal Akhir";
            exit();
        }
        if (substr($tglAwal, 0, 7) != substr($tglAkhir, 0, 7)) {
            echo "warning:Range Tanggal Tidak Boleh Melewati Bulan";
            exit();
        }

        if ($tipeReport != 'detail') {
            $tipeReport = 'rekap';
        }

        $periodeGaji = substr($tglAwal, 0, 7);
        $strdkar = "select karyawanid from " . $dbname . ".datakaryawan_hist
            where approval_status='8' and version_type='B' and periodegaji='" . $periodeGaji . "' AND tanggalkeluar = '0000-00-00' limit 1";
        $resdkar = fetchData($strdkar);
        if (count($resdkar) > 0) {
            $tabelKaryawan = 'datakaryawan_hist';
            $whereKaryawan = " and d.approval_status='8' and d.version_type='B'
                and d.tipekaryawan in ('1','2','3','4') and d.periodegaji='" . $periodeGaji . "' AND d.tanggalkeluar = '0000-00-00'";
        } else {
            $tabelKaryawan = 'datakaryawan';
            $whereKaryawan = " AND d.tanggalkeluar = '0000-00-00'";
        }

        $whereHeader = " and b.tanggal between '" . $tglAwal . "' and '" . $tglAkhir . "'";
        if ($alokasi == '') {
            $whereHeader .= " and b.kodeorg='" . substr($comId, 0, 4) . "'";
        }
        if ($jnsVhc != '') {
            $whereHeader .= " and b.jenisvhc='" . $jnsVhc . "'";
        }
        if ($kdVhc != '') {
            $whereHeader .= " and b.kodevhc='" . $kdVhc . "'";
        }

        $whereDetail = '';
        if ($alokasi != '') {
            $whereDetail .= " and c.alokasibiaya like '" . $alokasi . "%'";
        }
        if ($akun != '') {
            $whereDetail .= " and (c.jenispekerjaan='" . $akun . "' or c.jenispekerjaan in
                (select kodekegiatan from " . $dbname . ".vhc_kegiatan where noakun='" . $akun . "'))";
        }

        $sqlSummary = "select
                count(distinct concat(a.idkaryawan,'-',a.posisi)) as jumlahkaryawan,
                count(distinct case when a.posisi=0 then a.idkaryawan end) as jumlahoperator,
                count(distinct case when a.posisi=1 then a.idkaryawan end) as jumlahhelper,
                count(distinct a.notransaksi) as jumlahtransaksi,
                count(distinct b.kodevhc) as jumlahkendaraan,
                sum(ifnull(a.upah,0)) as totalupah,
                sum(ifnull(a.premi,0)) as totalpremi,
                sum(ifnull(a.penalty,0)) as totalpenalty,
                sum(ifnull(a.upah,0)+ifnull(a.premi,0)-ifnull(a.penalty,0)) as totalditerima
            from " . $dbname . ".vhc_runhk a
            inner join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
            left join " . $dbname . "." . $tabelKaryawan . " d on a.idkaryawan=d.karyawanid
            where 1=1 " . $whereHeader . $whereKaryawan . "
            and exists (
                select 1 from " . $dbname . ".vhc_rundt c
                where c.notransaksi=b.notransaksi " . $whereDetail . "
            )";
        $qSummary = $owlPDO->query($sqlSummary) or die(print " Gagal: " . PDOException::getMessage());
        $qSummary->setFetchMode(PDO::FETCH_ASSOC);
        $rSummary = $qSummary->fetch();

        if ($rSummary['jumlahkaryawan'] == 0) {
            echo "warning:Tidak Ada Data Untuk Filter Yang Dipilih";
            exit();
        }

        $border = $proses == 'excel' ? 1 : 0;
        $summaryFreezeAwal = '';
        $summaryFreezeAkhir = '';
        if ($proses != 'excel') {
            $summaryFreezeAwal = "<div id=vhc_summary_freeze class=vhc-summary-freeze>";
            $summaryFreezeAkhir = "</div>";
        }

        $tab = $summaryFreezeAwal . "<table cellspacing=1 cellpadding=5 border=" . $border . " class=sortable style='margin-bottom:10px;'>
            <thead>
                <tr class=rowheader>
                    <th align=center>Jumlah Karyawan</th>
                    <th align=center>Operator</th>
                    <th align=center>Helper</th>
                    <th align=center>Transaksi</th>
                    <th align=center>Kendaraan</th>
                    <th align=center>Total Upah</th>
                    <th align=center>Total Premi</th>
                    <th align=center>Total Penalty</th>
                    <th align=center>Total Diterima</th>
                </tr>
            </thead>
            <tbody>
                <tr class=rowcontent>
                    <td align=right>" . number_format($rSummary['jumlahkaryawan']) . "</td>
                    <td align=right>" . number_format($rSummary['jumlahoperator']) . "</td>
                    <td align=right>" . number_format($rSummary['jumlahhelper']) . "</td>
                    <td align=right>" . number_format($rSummary['jumlahtransaksi']) . "</td>
                    <td align=right>" . number_format($rSummary['jumlahkendaraan']) . "</td>
                    <td align=right>" . number_format($rSummary['totalupah'], 2) . "</td>
                    <td align=right>" . number_format($rSummary['totalpremi'], 2) . "</td>
                    <td align=right>" . number_format($rSummary['totalpenalty'], 2) . "</td>
                    <td align=right><b>" . number_format($rSummary['totalditerima'], 2) . "</b></td>
                </tr>
            </tbody>
        </table>" . $summaryFreezeAkhir;

        if ($tipeReport == 'rekap') {
            $sql = "select
                    a.idkaryawan,
                    ifnull(max(d.nik),'') as nik2,
                    ifnull(max(d.namakaryawan),'') as namakaryawan,
                    a.posisi,
                    group_concat(distinct if(ifnull(a.statuskaryawan,'')='','-',a.statuskaryawan) order by a.statuskaryawan separator ', ') as statuskaryawan,
                    count(distinct b.tanggal) as harikerja,
                    count(distinct a.notransaksi) as jumlahtransaksi,
                    sum(ifnull(a.upah,0)) as totalupah,
                    sum(ifnull(a.premi,0)) as totalpremi,
                    sum(ifnull(a.penalty,0)) as totalpenalty,
                    sum(ifnull(a.upah,0)+ifnull(a.premi,0)-ifnull(a.penalty,0)) as totalditerima
                from " . $dbname . ".vhc_runhk a
                inner join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
                left join " . $dbname . "." . $tabelKaryawan . " d on a.idkaryawan=d.karyawanid
                where 1=1 " . $whereHeader . $whereKaryawan . "
                and exists (
                    select 1 from " . $dbname . ".vhc_rundt c
                    where c.notransaksi=b.notransaksi " . $whereDetail . "
                )
                group by a.idkaryawan,a.posisi
                order by a.posisi,namakaryawan,a.idkaryawan";

            $tab .= "<table cellspacing=1 cellpadding=5 border=" . $border . " class='sortable vhc-data-table' style='width:100%'>
                <thead>
                    <tr class=rowheader>
                        <th align=center>No.</th>
                        <th align=center>NIK</th>
                        <th align=center>Nama Karyawan</th>
                        <th align=center>Posisi</th>
                        <th align=center>Hari Kerja</th>
                        <th align=center>Jumlah Transaksi</th>
                        <th align=center>Total Upah</th>
                        <th align=center>Total Premi</th>
                        <th align=center>Total Penalty</th>
                        <th align=center>Total Diterima</th>
                    </tr>
                </thead>
                <tbody>";

            $no = 0;
            $oldPosisi = '';
            $subtotalUpah = 0;
            $subtotalPremi = 0;
            $subtotalPenalty = 0;
            $subtotalDiterima = 0;
            $grandUpah = 0;
            $grandPremi = 0;
            $grandPenalty = 0;
            $grandDiterima = 0;

            $qData = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
            $qData->setFetchMode(PDO::FETCH_ASSOC);
            while ($res = $qData->fetch()) {
                $posisi = $arrPos[$res['posisi']];

                if ($oldPosisi != '' && $oldPosisi != $posisi) {
                    $tab .= "<tr class=rowcontent>
                        <td colspan=6 align=right><b>SUBTOTAL " . strtoupper($oldPosisi) . "</b></td>
                        <td align=right><b>" . number_format($subtotalUpah, 2) . "</b></td>
                        <td align=right><b>" . number_format($subtotalPremi, 2) . "</b></td>
                        <td align=right><b>" . number_format($subtotalPenalty, 2) . "</b></td>
                        <td align=right><b>" . number_format($subtotalDiterima, 2) . "</b></td>
                    </tr>";
                    $subtotalUpah = 0;
                    $subtotalPremi = 0;
                    $subtotalPenalty = 0;
                    $subtotalDiterima = 0;
                }

                $no += 1;
                $tab .= "<tr class=rowcontent>
                    <td align=center>" . $no . "</td>
                    <td>" . $res['nik2'] . "</td>
                    <td>" . $res['namakaryawan'] . "</td>
                    <td>" . $posisi . "</td>
                    <td align=right>" . number_format($res['harikerja']) . "</td>
                    <td align=right>" . number_format($res['jumlahtransaksi']) . "</td>
                    <td align=right>" . number_format($res['totalupah'], 2) . "</td>
                    <td align=right>" . number_format($res['totalpremi'], 2) . "</td>
                    <td align=right>" . number_format($res['totalpenalty'], 2) . "</td>
                    <td align=right>" . number_format($res['totalditerima'], 2) . "</td>
                </tr>";

                $subtotalUpah += $res['totalupah'];
                $subtotalPremi += $res['totalpremi'];
                $subtotalPenalty += $res['totalpenalty'];
                $subtotalDiterima += $res['totalditerima'];
                $grandUpah += $res['totalupah'];
                $grandPremi += $res['totalpremi'];
                $grandPenalty += $res['totalpenalty'];
                $grandDiterima += $res['totalditerima'];
                $oldPosisi = $posisi;
            }

            $tab .= "<tr class=rowcontent>
                <td colspan=6 align=right><b>SUBTOTAL " . strtoupper($oldPosisi) . "</b></td>
                <td align=right><b>" . number_format($subtotalUpah, 2) . "</b></td>
                <td align=right><b>" . number_format($subtotalPremi, 2) . "</b></td>
                <td align=right><b>" . number_format($subtotalPenalty, 2) . "</b></td>
                <td align=right><b>" . number_format($subtotalDiterima, 2) . "</b></td>
            </tr>
            <tr class=rowcontent>
                <td colspan=6 align=right><b>GRAND TOTAL</b></td>
                <td align=right><b>" . number_format($grandUpah, 2) . "</b></td>
                <td align=right><b>" . number_format($grandPremi, 2) . "</b></td>
                <td align=right><b>" . number_format($grandPenalty, 2) . "</b></td>
                <td align=right><b>" . number_format($grandDiterima, 2) . "</b></td>
            </tr>";
            $tab .= "</tbody></table>";
            $colspan = 10;
            $judul = 'LAPORAN REKAP UPAH DAN PREMI OPERATOR / HELPER';
        } else {
            $sql = "select
                    a.idkaryawan,a.posisi,a.statuskaryawan,a.upah,a.premi,a.penalty,a.keterangan as keteranganupah,
                    b.notransaksi,b.tanggal,b.jenisvhc,b.kodevhc,
                    c.jenispekerjaan,c.alokasibiaya,c.muatan,c.kmhmawal,c.kmhmakhir,c.jumlah,c.beratmuatan,c.jumlahrit,c.satuan,c.keterangan as keteranganpekerjaan,
                    ifnull(d.nik,'') as nik2,ifnull(d.namakaryawan,'') as namakaryawan,
                    ifnull(e.noakun,'') as noakun,ifnull(e.namakegiatan,'') as namakegiatan,ifnull(e.satuan,'') as satuankegiatan
                from " . $dbname . ".vhc_runhk a
                inner join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
                inner join " . $dbname . ".vhc_rundt c on a.notransaksi=c.notransaksi
                left join " . $dbname . "." . $tabelKaryawan . " d on a.idkaryawan=d.karyawanid
                left join " . $dbname . ".vhc_kegiatan e on c.jenispekerjaan=e.kodekegiatan
                where 1=1 " . $whereHeader . $whereDetail . $whereKaryawan . "
                order by a.posisi,namakaryawan,a.idkaryawan,b.tanggal,b.notransaksi,c.jenispekerjaan,c.alokasibiaya,c.kmhmawal,c.beratmuatan";

            $tab .= "<table cellspacing=1 cellpadding=5 border=" . $border . " class='sortable vhc-data-table' style='width:100%'>
                <thead>
                    <tr class=rowheader>
                        <th align=center>No.</th>
                        <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
                        <th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
                        <th align=center>NIK</th>
                        <th align=center>Nama Karyawan</th>
                        <th align=center>Posisi</th>
                        <th align=center>" . $_SESSION['lang']['jenisvch'] . "</th>
                        <th align=center>" . $_SESSION['lang']['kodevhc'] . "</th>
                        <th align=center>" . $_SESSION['lang']['nopol'] . "</th>
                        <th align=center>Detail Kendaraan</th>
                        <th align=center>" . $_SESSION['lang']['noakun'] . "</th>
                        <th align=center>" . $_SESSION['lang']['kodekegiatan'] . "</th>
                        <th align=center>" . $_SESSION['lang']['vhc_jenis_pekerjaan'] . "</th>
                        <th align=center>" . $_SESSION['lang']['alokasibiaya'] . "</th>
                        <th align=center>Muatan</th>
                        <th align=center>HM/KM Awal</th>
                        <th align=center>HM/KM Akhir</th>
                        <th align=center>HM/KM</th>
                        <th align=center>" . $_SESSION['lang']['vhc_berat_muatan'] . "</th>
                        <th align=center>" . $_SESSION['lang']['jumlahrit'] . "</th>
                        <th align=center>" . $_SESSION['lang']['satuan'] . "</th>
                        <th align=center>Upah</th>
                        <th align=center>Premi</th>
                        <th align=center>Penalty</th>
                        <th align=center>Total Diterima</th>
                        <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
                    </tr>
                </thead>
                <tbody>";

            $no = 0;
            $oldKaryawan = '';
            $oldTransaksi = '';
            $namaKaryawan = '';
            $subtotalUpah = 0;
            $subtotalPremi = 0;
            $subtotalPenalty = 0;
            $subtotalDiterima = 0;
            $grandUpah = 0;
            $grandPremi = 0;
            $grandPenalty = 0;
            $grandDiterima = 0;

            $qData = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
            $qData->setFetchMode(PDO::FETCH_ASSOC);
            while ($res = $qData->fetch()) {
                $keyKaryawan = $res['idkaryawan'] . '-' . $res['posisi'];
                $keyTransaksi = $keyKaryawan . '-' . $res['notransaksi'];

                if ($oldKaryawan != $keyKaryawan) {
                    if ($oldKaryawan != '') {
                        $tab .= "<tr class=rowcontent>
                            <td colspan=21 align=right><b>TOTAL " . strtoupper($namaKaryawan) . "</b></td>
                            <td align=right><b>" . number_format($subtotalUpah, 2) . "</b></td>
                            <td align=right><b>" . number_format($subtotalPremi, 2) . "</b></td>
                            <td align=right><b>" . number_format($subtotalPenalty, 2) . "</b></td>
                            <td align=right><b>" . number_format($subtotalDiterima, 2) . "</b></td>
                            <td></td>
                        </tr>";
                    }

                    $namaKaryawan = $res['namakaryawan'];
                    $subtotalUpah = 0;
                    $subtotalPremi = 0;
                    $subtotalPenalty = 0;
                    $subtotalDiterima = 0;
                    $oldTransaksi = '';

                    $tab .= "<tr class=rowcontent>
                        <td colspan=27><b>" . strtoupper($arrPos[$res['posisi']]) . " : " . $res['nik2'] . " - " . strtoupper($res['namakaryawan']) . "</b></td>
                    </tr>";
                }

                $upah = '';
                $premi = '';
                $penalty = '';
                $totalDiterima = '';
                if ($oldTransaksi != $keyTransaksi) {
                    $nilaiDiterima = $res['upah'] + $res['premi'] - $res['penalty'];
                    $upah = number_format($res['upah'], 2);
                    $premi = number_format($res['premi'], 2);
                    $penalty = number_format($res['penalty'], 2);
                    $totalDiterima = number_format($nilaiDiterima, 2);

                    $subtotalUpah += $res['upah'];
                    $subtotalPremi += $res['premi'];
                    $subtotalPenalty += $res['penalty'];
                    $subtotalDiterima += $nilaiDiterima;
                    $grandUpah += $res['upah'];
                    $grandPremi += $res['premi'];
                    $grandPenalty += $res['penalty'];
                    $grandDiterima += $nilaiDiterima;
                }

                $satuan = $res['satuan'] == '' ? $res['satuankegiatan'] : $res['satuan'];
                $namaAlokasi = $res['alokasibiaya'];
                if ($res['alokasibiaya'] != '') {
                    $namaAlokasi .= ' - ' . getNamaOrg($res['alokasibiaya']);
                }
                $keterangan = $res['keteranganpekerjaan'];
                if ($oldTransaksi != $keyTransaksi && $res['keteranganupah'] != '') {
                    if ($keterangan != '') {
                        $keterangan .= ' | ';
                    }
                    $keterangan .= 'UPAH: ' . $res['keteranganupah'];
                }

                $no += 1;
                $tab .= "<tr class=rowcontent>
                    <td align=center>" . $no . "</td>
                    <td>" . tanggalnormal($res['tanggal']) . "</td>
                    <td>" . $res['notransaksi'] . "</td>
                    <td>" . $res['nik2'] . "</td>
                    <td>" . $res['namakaryawan'] . "</td>
                    <td>" . $arrPos[$res['posisi']] . "</td>
                    <td>" . $res['jenisvhc'] . " - " . $nmJenis[$res['jenisvhc']] . "</td>
                    <td>" . $res['kodevhc'] . "</td>
                    <td>" . getNOpol($res['kodevhc']) . "</td>
                    <td>" . getNOpol($res['kodevhc'], 'd') . "</td>
                    <td>" . $res['noakun'] . "</td>
                    <td>" . $res['jenispekerjaan'] . "</td>
                    <td>" . $res['namakegiatan'] . "</td>
                    <td>" . $namaAlokasi . "</td>
                    <td>" . $res['muatan'] . "</td>
                    <td align=right>" . number_format($res['kmhmawal'], 2) . "</td>
                    <td align=right>" . number_format($res['kmhmakhir'], 2) . "</td>
                    <td align=right>" . number_format($res['jumlah'], 2) . "</td>
                    <td align=right>" . number_format($res['beratmuatan'], 2) . "</td>
                    <td align=right>" . number_format($res['jumlahrit'], 2) . "</td>
                    <td>" . $satuan . "</td>
                    <td align=right>" . $upah . "</td>
                    <td align=right>" . $premi . "</td>
                    <td align=right>" . $penalty . "</td>
                    <td align=right>" . $totalDiterima . "</td>
                    <td>" . $keterangan . "</td>
                </tr>";

                $oldKaryawan = $keyKaryawan;
                $oldTransaksi = $keyTransaksi;
            }

            $tab .= "<tr class=rowcontent>
                <td colspan=21 align=right><b>TOTAL " . strtoupper($namaKaryawan) . "</b></td>
                <td align=right><b>" . number_format($subtotalUpah, 2) . "</b></td>
                <td align=right><b>" . number_format($subtotalPremi, 2) . "</b></td>
                <td align=right><b>" . number_format($subtotalPenalty, 2) . "</b></td>
                <td align=right><b>" . number_format($subtotalDiterima, 2) . "</b></td>
                <td></td>
            </tr>
            <tr class=rowcontent>
                <td colspan=21 align=right><b>GRAND TOTAL</b></td>
                <td align=right><b>" . number_format($grandUpah, 2) . "</b></td>
                <td align=right><b>" . number_format($grandPremi, 2) . "</b></td>
                <td align=right><b>" . number_format($grandPenalty, 2) . "</b></td>
                <td align=right><b>" . number_format($grandDiterima, 2) . "</b></td>
                <td></td>
            </tr>";
            $tab .= "</tbody></table>";
            $colspan = 27;
            $judul = 'LAPORAN DETAIL UPAH DAN PREMI OPERATOR / HELPER';
        }

        if ($proses == 'get_result') {
            echo $tab;
            exit();
        }

        $str = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . substr($comId, 0, 4) . "'";
        $namapt = 'COMPANY NAME';
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $namapt = strtoupper($bar->namaorganisasi);
        }

        $stream = "
            <table>
                <tr><td colspan=" . $colspan . " align=center><b>" . $judul . "</b></td></tr>
                <tr><td colspan=" . $colspan . ">" . $_SESSION['lang']['unit'] . " : " . $namapt . "</td></tr>
                <tr><td colspan=" . $colspan . ">" . $_SESSION['lang']['periode'] . " : " . $tglAwalInput . " - " . $tglAkhirInput . "</td></tr>
                <tr><td colspan=" . $colspan . ">Tipe Laporan : " . strtoupper($tipeReport) . "</td></tr>
                <tr><td colspan=" . $colspan . ">&nbsp;</td></tr>
            </table>";
        $stream .= $tab;
        $stream .= "<br>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];

        $dte = date('Hms');
        $nop_ = 'ReportUpahPremiOperatorHelper_' . $tipeReport . '_' . $dte;
        $gztralala = gzopen('tempExcel/' . $nop_ . '.xls.gz', 'w9');
        gzwrite($gztralala, $stream);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls.gz';
        </script>";
        break;

    default:
        break;
}
