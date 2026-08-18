<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

// error_reporting(E_ALL);
// ini_set('display_errors', 1);


$proses = isset($_POST['proses']) ? $_POST['proses'] : '';
$pt = isset($_POST['pt']) ? $_POST['pt'] : '';
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';

$pt_arr = explode(',', $pt);
$pt_list = [];
foreach($pt_arr as $p) if($p != '') $pt_list[] = "'".$p."'";
if(empty($pt_list)) {
    $in_pt = " = ''";
    $like_pt_clause = "induk = ''";
    $like_kodeorg_clause = "kodeorg = ''";
    $like_lokasitugas_clause = "lokasitugas = ''";
} else {
    $in_pt = " IN (".implode(',', $pt_list).")";
    $likes = []; $likes2 = []; $likes3 = [];
    foreach($pt_arr as $p) {
        if($p == '') continue;
        $likes[] = "induk LIKE '".$p."%'";
        $likes2[] = "kodeorg LIKE '".$p."%'";
        $likes3[] = "lokasitugas LIKE '".$p."%'";
    }
    $like_pt_clause = " (".implode(' OR ', $likes).")";
    $like_kodeorg_clause = " (".implode(' OR ', $likes2).")";
    $like_lokasitugas_clause = " (".implode(' OR ', $likes3).")";
}


switch($proses) {
    case 'loadData1':
        $nop_="Laporan_Daftar_Asset_".str_replace(',','_',$pt);
        // [Existing LoadData1 logic...]
        $sDiv = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                 where (induk in (select kodeorganisasi from ".$dbname.".organisasi where ".$like_pt_clause.") 
                        or kodeorganisasi ".$in_pt.")
                 and tipe in ('DIVISI','AFDELING') order by kodeorganisasi asc";
        $qDiv = $owlPDO->query($sDiv) or die(print " Gagal: ".PDOException::getMessage());
        $qDiv->setFetchMode(PDO::FETCH_ASSOC);
        $divisions = array();
        while($rDiv = $qDiv->fetch()) $divisions[] = $rDiv;

        $sJenis = "select jenis, nama, status_bangunan from ".$dbname.".sdm_5jenis_prasarana 
                   where kelompok='BGN' order by nama asc";
        $qJenis = $owlPDO->query($sJenis) or die(print " Gagal: ".PDOException::getMessage());
        $qJenis->setFetchMode(PDO::FETCH_ASSOC);
        $assetTypes = array();
        while($rJenis = $qJenis->fetch()) $assetTypes[] = $rJenis;

        $sData = "select tipe, kodeorg, jumlahpintu, tahunpembuatan from ".$dbname.".sdm_perumahanht 
                  where ".$like_kodeorg_clause;
        $qData = $owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        
        $counts = array();
        $years = array();
        while($rData = $qData->fetch()) {
            $tp = $rData['tipe'];
            $org = $rData['kodeorg'];
            $col = (strlen($org) == 4) ? 'BASECAMP' : (strpos($org, 'TR') !== false ? 'TRAKSI' : (strpos($org, 'UM') !== false ? 'UMUM' : $org));
            if(!isset($counts[$tp][$col]['count'])) $counts[$tp][$col]['count'] = 0;
            if(!isset($counts[$tp][$col]['doors'])) $counts[$tp][$col]['doors'] = 0;
            $counts[$tp][$col]['count'] += 1;
            $counts[$tp][$col]['doors'] += $rData['jumlahpintu'];
            if($rData['tahunpembuatan'] != '' && $rData['tahunpembuatan'] != '0000') $years[$tp][] = $rData['tahunpembuatan'];
        }

        if ($mode == 'excel') $border = 1; else $border = 0;

        $tab = "<table class=sortable cellspacing=1 border=".$border."><thead><tr class=rowheader><th rowspan=2>No</th><th rowspan=2>Nama Bangunan</th><th rowspan=2>Tahun Pembuatan</th><th colspan=3>Base Camp</th>";
        foreach($divisions as $div) $tab .= "<th colspan=3>".$div['namaorganisasi']."</th>";
        $tab .= "<th colspan=3>Divisi Traksi</th><th colspan=3>Divisi Umum</th><th colspan=4>Summary</th></tr>
                <tr class=rowheader><th align=center>S-Perm</th><th align=center>Perm</th><th align=center>Total Door</th>";
        foreach($divisions as $div) $tab .= "<th align=center>S-Perm</th><th align=center>Perm</th><th align=center>Total Door</th>";
        $tab .= "<th>S-Perm</th><th>Perm</th><th>Door</th><th>S-Perm</th><th>Perm</th><th>Door</th><th>S-Perm</th><th>Perm</th><th>Total</th><th>Door</th></tr></thead><tbody>";

        $cols = array('BASECAMP');
        foreach($divisions as $d) $cols[] = $d['kodeorganisasi'];
        $cols[] = 'TRAKSI'; $cols[] = 'UMUM';

        $no = 0;
        foreach($assetTypes as $at) {
            $tp = $at['jenis'];
            if(!isset($counts[$tp])) continue;
            $no++; $isPerm = (strtoupper($at['status_bangunan']) == 'PERMANEN');
            $yearStr = '-'; if(isset($years[$tp])) { $minY = min($years[$tp]); $maxY = max($years[$tp]); $yearStr = ($minY == $maxY) ? $minY : $minY." - ".$maxY; }
            $tab .= "<tr class=rowcontent><td>".$no."</td><td>".$at['nama']."</td><td align=center>".$yearStr."</td>";
            
            $rowSumSemi = 0; $rowSumPerm = 0; $rowSumDoors = 0;
            foreach($cols as $c) {
                $count = isset($counts[$tp][$c]['count']) ? $counts[$tp][$c]['count'] : 0;
                $door = isset($counts[$tp][$c]['doors']) ? $counts[$tp][$c]['doors'] : 0;
                $semi = (!$isPerm) ? $count : 0; $perm = ($isPerm) ? $count : 0;
                $tab .= "<td align=right>".$semi."</td><td align=right>".$perm."</td><td align=right>".$door."</td>";
                $rowSumSemi += $semi; $rowSumPerm += $perm; $rowSumDoors += $door;
            }
            $tab .= "<td align=right><b>".$rowSumSemi."</b></td><td align=right><b>".$rowSumPerm."</b></td><td align=right><b>".($rowSumSemi + $rowSumPerm)."</b></td><td align=right><b>".$rowSumDoors."</b></td></tr>";
        }
        $tab .= "</tbody></table>";
        echo $tab;
        break;

    case 'loadData2':
        // [Existing LoadData2 logic...]
        $sPenghuni = "select norumah, count(*) as jlh from ".$dbname.".sdm_penghunirumah where ".$like_kodeorg_clause." group by norumah";
        $qPenghuni = $owlPDO->query($sPenghuni);
        $occupants = array();
        while($rPenghuni = $qPenghuni->fetch(PDO::FETCH_ASSOC)) $occupants[$rPenghuni['norumah']] = $rPenghuni['jlh'];

        $sData = "select H.*, T.nama as tipe_nama, T.status_bangunan from ".$dbname.".sdm_perumahanht H
                  left join ".$dbname.".sdm_5jenis_prasarana T on H.tipe = T.jenis where ".str_replace('kodeorg', 'H.kodeorg', $like_kodeorg_clause)." order by H.tipe asc, H.norumah asc";
        $qData = $owlPDO->query($sData);

        if ($mode == 'excel') $border = 1; else $border = 0;            
        
        $tab = "<table class=sortable cellspacing=1 border=".$border."><thead><tr class=rowheader><th>No</th><th>Mess</th><th>Divisi</th><th>Tahun</th><th>Bgn</th><th>Door</th><th>Kap</th><th>Huni</th><th>Sisa</th><th>P/S</th><th>Fungsi</th><th>Kondisi</th></tr></thead><tbody>";
        $no = 0; $totalBgn = 0; $totalDoor = 0; $totalKapasitas = 0; $totalPenghuni = 0; $totalSisa = 0;
        while($r = $qData->fetch(PDO::FETCH_ASSOC)) {
            $no++; $kap = 0; if (preg_match('/G(\d+)/', $r['tipe'], $m)) $kap = $m[1]; else if (preg_match('/G(\d+)/', $r['keterangan'], $m)) $kap = $m[1];
            $huni = isset($occupants[$r['norumah']]) ? $occupants[$r['norumah']] : 0; $sisa = ($kap > 0) ? ($kap - $huni) : 0;
            $tab .= "<tr class=rowcontent><td>".$no."</td><td>".$r['keterangan']."</td><td>".$r['kodeorg']."</td><td align=center>".$r['tahunpembuatan']."</td><td align=right>1</td><td align=right>".$r['jumlahpintu']."</td><td align=right>".$kap."</td><td align=right>".$huni."</td><td align=right>".$sisa."</td><td>".$r['status_bangunan']."</td><td>".$r['alamat']."</td><td>".$r['kondisi']."</td></tr>";
            $totalBgn += 1; $totalDoor += $r['jumlahpintu']; $totalKapasitas += $kap; $totalPenghuni += $huni; $totalSisa += $sisa;
        }
        $tab .= "<tr class=rowcontent><td colspan=4 align=center><b>Total</b></td><td align=right><b>".$totalBgn."</b></td><td align=right><b>".$totalDoor."</b></td><td align=right><b>".$totalKapasitas."</b></td><td align=right><b>".$totalPenghuni."</b></td><td align=right><b>".$totalSisa."</b></td><td colspan=3></td></tr>";
        $sKary = "select count(*) as jlh from ".$dbname.".datakaryawan where ".$like_lokasitugas_clause." and (tanggalkeluar='0000-00-00' or tanggalkeluar > curdate())";
        $qKary = $owlPDO->query($sKary)->fetch();
        $tab .= "<tr class=rowcontent><td colspan=4 align=center><b>Total Karyawan</b></td><td colspan=2></td><td align=right><b>".$qKary['jlh']."</b></td><td colspan=5></td></tr>";
        $tab .= "<tr class=rowcontent><td colspan=4 align=center><b>Selisih</b></td><td colspan=2></td><td align=right><b>".($totalKapasitas - $qKary['jlh'])."</b></td><td colspan=5></td></tr></tbody></table>";
        echo $tab;
        break;

    case 'loadData3':
        // [Existing LoadData3 logic...]
        $startOfYear = date('Y') . '-01-01';
        $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
        $sData = "select kodeasset,namasset, tanggalperolehan, tanggaldisposal, status, keterangan, posisiasset from ".$dbname.".sdm_daftarasset where ".$like_kodeorg_clause." and tipeasset = 'MM' order by namasset asc";
        $qData = $owlPDO->query($sData);

        if ($mode == 'excel') $border = 1; else $border = 0;

        $tab = "<table class=sortable cellspacing=1 border=".$border."><thead><tr class=rowheader><th rowspan=2>No</th><th rowspan=2>Barang</th><th rowspan=2>Tahun</th><th rowspan=2>Sat</th><th rowspan=2>Awal</th><th colspan=2>Mutasi</th><th rowspan=2>Akhir</th><th rowspan=2>Lokasi</th><th rowspan=2>Fungsi</th><th rowspan=2>PIC</th><th rowspan=2>Kondisi</th><th rowspan=2>Keterangan</th></tr><tr class=rowheader><th>Masuk</th><th>Keluar</th></tr></thead><tbody>";
        $no = 0;
        while($r = $qData->fetch(PDO::FETCH_ASSOC)) {
            ##cek di vhc_5master
            $strvhc = "select kodevhc from ".$dbname.".vhc_5master where kodeasset='".$r['kodeasset']."'";
            $resvhc = fetchData($strvhc)[0];
            $ket = "";

            if (count($resvhc)>0) {
                ##cek di service
                $strvhcs = "select nopengajuan from ".$dbname.".vhc_pengajuanservice where kodevhc='".$resvhc['kodevhc']."' and statuspersetujuan='0'";
                $resvhcs = fetchData($strvhcs);
                if (count($resvhcs)>0) {
                    $ket = "Perlu Perbaikan";
                }
            }

            $no++; $per = $r['tanggalperolehan']; $dis = $r['tanggaldisposal']; $st = $r['status'];
            $awal = ($per < $startOfYear && $per != '0000-00-00') ? 1 : 0; $masuk = ($per >= $startOfYear) ? 1 : 0; $keluar = ($dis >= $startOfYear && $dis != '0000-00-00') ? 1 : 0; $akhir = ($st == 1) ? 1 : 0;
            $pic = ''; $fung = $r['keterangan']; if(strpos($r['keterangan'], 'PIC:') !== false) { $parts = explode('PIC:', $r['keterangan']); $fung = trim($parts[0]); $pic = trim($parts[1]); }
            $tab .= "<tr class=rowcontent><td>".$no."</td><td>".$r['namasset']."</td><td align=center>".substr($per, 0, 4)."</td><td align=center>Unit</td><td align=right>".$awal."</td><td align=right>".$masuk."</td><td align=right>".$keluar."</td><td align=right>".$akhir."</td><td>".(isset($optNmOrg[$r['posisiasset']]) ? $optNmOrg[$r['posisiasset']] : $r['posisiasset'])."</td><td>".$fung."</td><td>".$pic."</td><td>".($st == 1 ? 'Baik' : 'Rusak')."</td><td>".$ket."</td></tr>";
        }
        $tab .= "</tbody></table>"; echo $tab;
        break;

    case 'loadData4':
        // [Existing LoadData4 logic...]
        $sData = "select V.*, A.namasset from ".$dbname.".vhc_5master V left join ".$dbname.".sdm_daftarasset A on V.kodeasset = A.kodeasset where ".str_replace('kodeorg', 'V.kodeorg', $like_kodeorg_clause)." and V.kelompokvhc='KD' order by V.kelompokvhc asc";
        // exit('WARNING: '.$sData);
        $qData = $owlPDO->query($sData);
        $sec1 = array(); $sec2 = array();
        $ket = [];
        while($r = $qData->fetch(PDO::FETCH_ASSOC)) {
            $is50 = (strpos(strtoupper($r['namasset']), '50%') !== false || strpos(strtoupper($r['detailvhc']), '50%') !== false || ($r['kelompokvhc'] == 'MS' && strpos(strtoupper($r['namasset']), 'MOTOR') !== false));
            if($is50) $sec2[] = $r; else $sec1[] = $r;

            ##cek di service
            $strvhcs = "select nopengajuan from ".$dbname.".vhc_pengajuanservice where kodevhc='".$r['kodevhc']."' and statuspersetujuan='0'";
            $resvhcs = fetchData($strvhcs);
            if (count($resvhcs)>0) {
                $ket[$r['kodeasset']] = "Perlu Perbaikan";
            }
        }

        if ($mode == 'excel') $border = 1; else $border = 0;

        $tab = "<table class=sortable cellspacing=1 border=".$border."><thead><tr class=rowheader><th>No</th><th>Tipe</th><th>Merk</th><th>Plat</th><th>Tahun</th><th>Pengguna</th><th>Tugas</th><th>Supir</th><th>Kon</th><th>Milik</th><th>Remaks</th><th>Keterangan</th></tr></thead><tbody>";
        $tab .= "<tr class=rowcontent><td colspan=12 bgcolor=#fdfd96><b>ASSET MH</b></td></tr>"; $no = 0;
        foreach($sec1 as $r) { $no++; $tipe = $r['detailvhc']; $merk = ''; if (preg_match('/\((.*?)\)/', $r['detailvhc'], $m)) $merk = $m[1]; $tab .= "<tr class=rowcontent><td>".$no."</td><td>".$tipe."</td><td>".$merk."</td><td align=center>".$r['nopol']."</td><td align=center>".$r['tahunperolehan']."</td><td>".$r['kodeorg']."</td><td></td><td></td><td align=center>".($r['status']==1?'Baik':'Rusak')."</td><td align=center>".($r['kepemilikan']==1?$pt:'Ext')."</td><td>".$r['namasset']."</td><td>".$ket[$r['kodeasset']]."</td></tr>"; }
        $tab .= "<tr class=rowcontent><td colspan=12><b>Total Asset: ".$no."</b></td></tr><tr class=rowcontent><td colspan=12 bgcolor=#fdf96><b>PROGRAM 50%</b></td></tr>"; $no2 = 0;
        foreach($sec2 as $r) { $no2++; $tab .= "<tr class=rowcontent><td>".$no2."</td><td>".$r['namasset']."</td><td>".$r['warna']."</td><td align=center>".$r['nopol']."</td><td align=center>".$r['tahunperolehan']."</td><td>".$r['kodeorg']."</td><td>Ops Lap</td><td></td><td align=center>".($r['status']==1?'Baik':'Rusak')."</td><td align=center>".($r['kepemilikan']==1?$pt:'P50')."</td><td>".$r['detailvhc']."</td><td>".$ket[$r['kodeasset']]."</td></tr>"; }
        $tab .= "<tr class=rowcontent><td colspan=12><b>Total Program 50%: ".$no2."</b></td></tr>";
        $qKary = $owlPDO->query("select count(*) as jlh from ".$dbname.".datakaryawan where ".$like_lokasitugas_clause." and (tanggalkeluar='0000-00-00' or tanggalkeluar > curdate())")->fetch();
        $tab .= "<tr class=rowcontent><td colspan=4 align=center><b>Total Karyawan</b></td><td colspan=2></td><td align=right><b>".$qKary['jlh']."</b></td><td colspan=5></td></tr><tr class=rowcontent><td colspan=4 align=center><b>Selisih</b></td><td colspan=2></td><td align=right><b>".($no2 - $qKary['jlh'])."</b></td><td colspan=6></td></tr></tbody></table>";
        echo $tab;
        break;

    case 'loadData5':
        $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
        $optJenis = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');

        $sData = "select V.*, A.namasset, A.keterangan as asset_ket, A.status as asset_status 
                  from ".$dbname.".vhc_5master V
                  left join ".$dbname.".sdm_daftarasset A on V.kodeasset = A.kodeasset
                  where ".str_replace('kodeorg', 'V.kodeorg', $like_kodeorg_clause)." and V.kelompokvhc = 'AB' 
                  order by V.kodeorg asc, V.jenisvhc asc";
        $qData = $owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);

        if ($mode == 'excel') $border = 1; else $border = 0;
        
        $tab = "<table class=sortable cellspacing=1 border=".$border.">
                <thead>
                <tr class=rowheader>
                    <th align=center>No</th>
                    <th align=center>Kode Organisasi</th>
                    <th align=center>Tipe Mesin,Kend & Alat Berat</th>
                    <th align=center>Kode Kendaraan</th>
                    <th align=center>No. Polisi</th>
                    <th align=center>Nama Barang</th>
                    <th align=center>Tahun Perolehan</th>
                    <th align=center>Nomor Akun</th>
                    <th align=center>Berat Kosong</th>
                    <th align=center>Nomor Rangka / Serial</th>
                    <th align=center>Nomor Mesin</th>
                    <th align=center>Rincian</th>
                    <th align=center>Kepemilikan</th>
                </tr>
                </thead>
                <tbody>";

        $no = 0;
        while($r = $qData->fetch()) {
            $no++;
            $tipe = isset($optJenis[$r['jenisvhc']]) ? $optJenis[$r['jenisvhc']] : $r['jenisvhc'];
            $milik = ($r['kepemilikan'] == 1) ? 'Milik Sendiri' : 'Sewa/External';
            
            $tab .= "<tr class=rowcontent>
                <td>".$no."</td>
                <td>".$r['kodeorg']."</td>
                <td>".$tipe."</td>
                <td>".$r['kodevhc']."</td>
                <td>".$r['nopol']."</td>
                <td>".$r['namasset']."</td>
                <td align=center>".$r['tahunperolehan']."</td>
                <td>".$r['noakun']."</td>
                <td align=right>".number_format($r['beratkosong'], 0)."</td>
                <td>".$r['nomorrangka']."</td>
                <td>".$r['nomormesin']."</td>
                <td>".$r['detailvhc']."</td>
                <td>".$milik."</td>
            </tr>";
        }

        if ($no == 0) $tab .= "<tr class=rowcontent><td colspan=13 align=center>No data found</td></tr>";

        $tab .= "</tbody></table>";
        echo $tab;
        break;

    case 'loadData6':
        $sData = "SELECT 
                    m.detailvhc as namasset, 
                    m.kodevhc,
                    h.tanggal, 
                    v.kmmasuk as hm,
                    b.namabarang,
                    d.jumlah,
                    d.satuan,
                    d.hargarata as cost,
                    v.kerusakan as ket
                FROM ".$dbname.".vhc_5master m
                INNER JOIN ".$dbname.".log_transaksidt d ON m.kodevhc = d.kodemesin
                INNER JOIN ".$dbname.".log_transaksiht h ON d.notransaksi = h.notransaksi
                INNER JOIN ".$dbname.".log_5masterbarang b ON d.kodebarang = b.kodebarang
                LEFT JOIN ".$dbname.".vhc_penggantianht v ON (m.kodevhc = v.kodevhc AND h.tanggal = v.tanggal)
                WHERE m.kelompokvhc = 'AB' AND b.kelompokbarang != '351' AND ".str_replace('kodeorg', 'm.kodeorg', $like_kodeorg_clause)."
                ORDER BY m.detailvhc, h.tanggal DESC, h.notransaksi ASC";
        
        $qData = $owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        
        $records = array();
        while($r = $qData->fetch()) {
            $records[$r['kodevhc']]['name'] = $r['namasset'];
            $records[$r['kodevhc']]['data'][] = $r;
        }

        // AMBIL DATA JASA SERVICE MEKANIK
        $sJasa = "SELECT 
                    a.kodevhc,
                    c.detailvhc as namasset,
                    a.tanggalpengajuan as tanggal,
                    0 as hm,
                    'JASA SERVICE' as namabarang,
                    1 as jumlah,
                    'LS' as satuan,
                    SUM(gaji.total_gaji / counts.jml_service) as cost,
                    'JASA SERVICE' as ket
                FROM ".$dbname.".vhc_pengajuanservice a
                INNER JOIN ".$dbname.".vhc_5master c ON a.kodevhc = c.kodevhc
                INNER JOIN ".$dbname.".vhc_pengajuanservicedt_karyawan b ON a.nopengajuan = b.nopengajuan
                INNER JOIN (
                    SELECT b2.karyawanid, SUBSTRING(a2.tanggalpengajuan, 1, 7) as periode, COUNT(a2.nopengajuan) as jml_service
                    FROM ".$dbname.".vhc_pengajuanservice a2
                    INNER JOIN ".$dbname.".vhc_pengajuanservicedt_karyawan b2 ON a2.nopengajuan = b2.nopengajuan
                    GROUP BY b2.karyawanid, SUBSTRING(a2.tanggalpengajuan, 1, 7)
                ) counts ON b.karyawanid = counts.karyawanid AND SUBSTRING(a.tanggalpengajuan, 1, 7) = counts.periode
                INNER JOIN (
                    SELECT sg.karyawanid, sg.periodegaji, SUM(sg.jumlah) as total_gaji
                    FROM ".$dbname.".sdm_gaji sg
                    INNER JOIN ".$dbname.".sdm_ho_component d ON sg.idkomponen = d.id
                    WHERE sg.pengali = '1' AND d.plus = '1' AND sg.idkomponen != 32
                    GROUP BY sg.karyawanid, sg.periodegaji
                ) gaji ON b.karyawanid = gaji.karyawanid AND SUBSTRING(a.tanggalpengajuan, 1, 7) = gaji.periodegaji
                WHERE c.kelompokvhc = 'AB' AND ".str_replace('kodeorg', 'c.kodeorg', $like_kodeorg_clause)."
                GROUP BY a.nopengajuan, a.kodevhc, c.detailvhc, a.tanggalpengajuan";

        $qJasa = $owlPDO->query($sJasa) or die(print " Gagal Jasa: ".PDOException::getMessage());
        $qJasa->setFetchMode(PDO::FETCH_ASSOC);

        while($r = $qJasa->fetch()) {
            if(!isset($records[$r['kodevhc']])) {
                $records[$r['kodevhc']]['name'] = $r['namasset'];
            }
            $records[$r['kodevhc']]['data'][] = $r;
        }

        // SORT DATA BERDASARKAN TANGGAL DESCENDING UNTUK SETIAP ALAT BERAT
        foreach($records as $k => $v) {
            usort($records[$k]['data'], function($a, $b) {
                return strtotime($b['tanggal']) - strtotime($a['tanggal']);
            });
        }

        if ($mode == 'excel') $border = 1; else $border = 0;
        
        $tab = "<table class=sortable cellspacing=1 border=".$border.">
                <thead>
                <tr class=rowheader>
                    <th align=center>No</th>
                    <th align=center>Nama Alat Berat</th>
                    <th align=center>Tanggal</th>
                    <th align=center>Hour Machine</th>
                    <th align=center>Part Diganti</th>
                    <th align=center>Biaya Part</th>
                    <th align=center>Ket.</th>
                </tr>
                </thead>
                <tbody>";
        
        $no = 0;
        foreach($records as $kodevhc => $machine) {
            $no++;
            $first = true;
            $rowspan = count($machine['data']);
            foreach($machine['data'] as $row) {
                $tab .= "<tr class=rowcontent>";
                if($first) {
                    $tab .= "<td align=center rowspan=".$rowspan.">".$no."</td>";
                    $tab .= "<td rowspan=".$rowspan.">".$machine['name']." (".$kodevhc.")</td>";
                }
                $tab .= "<td>".$row['tanggal']."</td>";
                $tab .= "<td align=right>".number_format($row['hm'], 0)."</td>";
                $tab .= "<td>".$row['namabarang']."</td>";
                $tab .= "<td align=right>".number_format($row['cost'], 2)."</td>";
                $tab .= "<td>".$row['ket']."</td>";
                $tab .= "</tr>";
                $first = false;
            }
        }
        
        if (empty($records)) $tab .= "<tr class=rowcontent><td colspan=7 align=center>No data found</td></tr>";
        
        $tab .= "</tbody></table>";
        echo $tab;
        break;

    case 'loadData7':
        $sData = "select namasset, kodeorg, posisiasset, keterangan 
                  from ".$dbname.".sdm_daftarasset 
                  where ".$like_kodeorg_clause." 
                  and (namasset like '%Jalan%' or namasset like '%Jembatan%' or namasset like '%Parit%' or namasset like '%Drain%' or namasset like '%Gorong%')
                  order by namasset asc";
        // exit('warning:'.$sData);
        $qData = $owlPDO->query($sData);
        $qData->setFetchMode(PDO::FETCH_ASSOC);

        $jalan = array(); $jembatan = array(); $parit = array();
        $divisions = array();

        while($r = $qData->fetch()) {
            $org = $r['kodeorg'];
            $div = (strlen($org) > 4) ? substr($org, -2) : 'HO';
            if(!in_array($div, $divisions)) $divisions[] = $div;

            $name = strtoupper($r['namasset']);
            if(strpos($name, 'JALAN') !== false) {
                $sub = (strpos($name, 'POROS')!==false) ? 'POROS' : ((strpos($name, 'UTAMA')!==false)?'UTAMA':((strpos($name, 'PRODUKSI')!==false)?'PRODUKSI':((strpos($name, 'PRINGGAN')!==false)?'PRINGGAN':'UMUM')));
                $jalan[$sub][$div] = (isset($jalan[$sub][$div])?$jalan[$sub][$div]:0) + 1;
            } else if(strpos($name, 'PARIT') !== false || strpos($name, 'DRAIN') !== false) {
                $sub = (strpos($name, 'MAIN')!==false) ? 'MAIN DRAIN' : ((strpos($name, 'COLLECTION')!==false)?'COLLECTION DRAIN':'SUBSIDARI DRAIN');
                $parit[$sub][$div] = (isset($parit[$sub][$div])?$parit[$sub][$div]:0) + 1;
            } else {
                $sub = (strpos($name, 'BOX')!==false) ? 'BOX CULVERT' : ((strpos($name, 'ULIN')!==false)?'JEMBATAN ULIN':((strpos($name, 'GORONG')!==false)?'GORONG-GORONG':'JEMBATAN LAYANG'));
                $jembatan[$sub][$div] = (isset($jembatan[$sub][$div])?$jembatan[$sub][$div]:0) + 1;
            }
        }
        sort($divisions);

        if ($mode == 'excel') $border = 1; else $border = 0;

        function makeInfraTable($title, $unit, $subs, $divs, $data, $border) {
            $tab = "<h3>".$title."</h3><table class=sortable cellspacing=1 border=".$border."><thead><tr class=rowheader><th>NO</th><th>SPESIFIKASI</th><th>SAT</th>";
            foreach($divs as $d) $tab .= "<th>Divisi ".$d."</th>";
            $tab .= "<th>JUMLAH</th><th>Keterangan</th></tr></thead><tbody>";
            $no = 0;
            foreach($subs as $s) {
                $no++; $tab .= "<tr class=rowcontent><td>".$no."</td><td>".$s."</td><td align=center>".$unit."</td>";
                $rowTot = 0;
                foreach($divs as $d) {
                    $val = isset($data[$s][$d]) ? $data[$s][$d] : 0;
                    $tab .= "<td align=right>".$val."</td>";
                    $rowTot += $val;
                }
                $tab .= "<td align=right><b>".$rowTot."</b></td><td></td></tr>";
            }
            $tab .= "</tbody></table><br>";
            return $tab;
        }

        $tab = makeInfraTable("Panjang Jalan", "Meter", array('POROS','UTAMA','PRODUKSI','PRINGGAN','UMUM'), $divisions, $jalan, $border);
        $tab .= makeInfraTable("Jumlah Jembatan", "Unit", array('BOX CULVERT','JEMBATAN ULIN','GORONG-GORONG','JEMBATAN LAYANG'), $divisions, $jembatan, $border);
        $tab .= makeInfraTable("Panjang Parit", "Meter", array('MAIN DRAIN','COLLECTION DRAIN','SUBSIDARI DRAIN'), $divisions, $parit, $border);

        echo $tab;
        break;
}
?>
