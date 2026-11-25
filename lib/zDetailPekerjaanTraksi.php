<?php
# Pembentukan vhc_rundt_detail (jam, muatan, premi operator per blok kecil) dari baris Pekerjaan Kendaraan (vhc_rundt),
# untuk menu Traksi > Proses > Bentuk Ulang Detail Pekerjaan (vhc_slave_3bentukulangdetail.php).
# Isinya salinan logika posting di vhc_slave_pekerjaan_v2.php (case postingdata) dengan nama fungsi berbeda
# (akhiran BU), supaya script transaksi tidak disentuh. Bila logika posting berubah, file ini ikut disesuaikan.
#
# Aturan:
#   - baris ke blok induk: dibagi ke blok kecil aktif menurut luas (porsiBlokKecilBU), sisa pembulatan ke blok terakhir
#   - baris ke unit sendiri, unit lain, project (AK), pabrikasi (PB), atau kode lain: disalin apa adanya
#   - jumlah jam detail per kegiatan + blok induk selalu sama dengan jam di vhc_rundt
# Hasil dipakai proses Alokasi Traksi (ALKJAM) dan alokasi gaji traksi.

#susun baris detail satu transaksi (tanpa menulis database); kunci array = kegiatan|alokasi|segment|km/hm awal
function susunDetailPekerjaanTraksi($noTrans)
{
    global $dbname;
    $noTransAman = addslashes($noTrans);

    #== Blok kecil aktif per blok induk
    $blokkecil = array();
    $sql = "select kodeorg,indukblok,statusblok,luasareaproduktif,luasbloking from " . $dbname . ".setup_blok
            where status='A' and indukblok in (select alokasibiaya from " . $dbname . ".vhc_rundt where notransaksi='" . $noTransAman . "')
            order by kodeorg";
    foreach (fetchData($sql) as $val) {
        # Luas acuan: blok TB memakai luas bloking (luas produktifnya umumnya kosong), selain itu luas produktif
        $luasAcuan = ($val['statusblok'] == 'TB' and $val['luasbloking'] > 0) ? $val['luasbloking'] : $val['luasareaproduktif'];
        $blokkecil[$val['indukblok']][$val['kodeorg']] = array('status' => $val['statusblok'], 'luas' => floatval($luasAcuan));
    }

    #== Kelompok kegiatan (TB/TBM) untuk kegiatan TB kode 126
    $kelompokKeg = array();
    $sql = "select kodekegiatan,kelompok from " . $dbname . ".vhc_kegiatan
            where tipe='traksi' and kodekegiatan in (select jenispekerjaan from " . $dbname . ".vhc_rundt where notransaksi='" . $noTransAman . "')";
    foreach (fetchData($sql) as $val) {
        $kelompokKeg[trim($val['kodekegiatan'])] = $val['kelompok'];
    }

    #== Susun detail; baris yang kuncinya sama (kegiatan, blok, segment, km/hm awal) digabung agar tidak bentrok kunci primer
    $detailBaru = array();
    $rsx = fetchData("select * from " . $dbname . ".vhc_rundt where notransaksi='" . $noTransAman . "'");
    foreach ($rsx as $v) {
        $jp = trim($v['jenispekerjaan']);
        if (!isset($blokkecil[$v['alokasibiaya']])) {
            tambahDetailBU($detailBaru, $v, $jp, $v['alokasibiaya'], $v['jumlah'], $v['beratmuatan'], $v['premiopt']);
            continue;
        }
        $porsi = porsiBlokKecilBU($blokkecil[$v['alokasibiaya']], $jp, isset($kelompokKeg[$jp]) ? $kelompokKeg[$jp] : '');
        $sisaJam = $v['jumlah'];
        $sisaMuatan = $v['beratmuatan'];
        $sisaPremi = $v['premiopt'];
        $jmlPorsi = count($porsi);
        $urut = 0;
        foreach ($porsi as $kodeKecil => $p) {
            $urut++;
            if ($urut == $jmlPorsi) {
                # blok terakhir menerima sisa, sehingga jumlah per blok kecil persis sama dengan baris induknya
                $jamKecil = $sisaJam;
                $muatanKecil = $sisaMuatan;
                $premiKecil = $sisaPremi;
            } else {
                $jamKecil = $v['jumlah'] * $p;
                $muatanKecil = $v['beratmuatan'] * $p;
                $premiKecil = $v['premiopt'] * $p;
                $sisaJam -= $jamKecil;
                $sisaMuatan -= $muatanKecil;
                $sisaPremi -= $premiKecil;
            }
            tambahDetailBU($detailBaru, $v, $jp, $kodeKecil, $jamKecil, $muatanKecil, $premiKecil);
        }
    }
    return $detailBaru;
}

#hapus detail lama satu transaksi lalu isi dengan hasil susunDetailPekerjaanTraksi; transaksi database diatur pemanggil
function simpanDetailPekerjaanTraksi($noTrans)
{
    global $dbname, $owlPDO;
    $detailBaru = susunDetailPekerjaanTraksi($noTrans);
    $owlPDO->exec("delete from " . $dbname . ".vhc_rundt_detail where notransaksi='" . addslashes($noTrans) . "'");
    foreach ($detailBaru as $data) {
        $owlPDO->exec(insertQuery($dbname, 'vhc_rundt_detail', $data, array_keys($data)));
    }
    return count($detailBaru);
}

#porsi blok kecil untuk satu baris pekerjaan yang dialokasikan ke blok induk (jumlah porsi = 1)
#- kegiatan 128 -> blok BBT, 611/621 -> blok TM, 126 -> blok sesuai kelompok kegiatan (TB/TBM), bila tidak ada -> blok TB dan TBM
#- kegiatan lain, atau tidak ada blok kecil yang statusnya cocok -> semua blok kecil aktif di induk itu (jam tidak boleh hilang)
#- dibagi menurut luas; bila luas blok terpilih semuanya kosong dibagi rata; blok dengan porsi 0 tidak dibuatkan detail
function porsiBlokKecilBU($arrBlok, $jenispekerjaan, $kelompok)
{
    $jp3 = substr($jenispekerjaan, 0, 3);
    $sasaran = array();
    if ($jp3 == '128') {
        $sasaran = array('BBT');
    } else if ($jp3 == '611' || $jp3 == '621') {
        $sasaran = array('TM');
    } else if ($jp3 == '126') {
        $sasaran = array('TB', 'TBM');
        if ($kelompok == 'TB' || $kelompok == 'TBM') {
            foreach ($arrBlok as $b) {
                if ($b['status'] == $kelompok) {
                    $sasaran = array($kelompok);
                    break;
                }
            }
        }
    }

    $pilih = array();
    foreach ($arrBlok as $kode => $b) {
        if (empty($sasaran) || in_array($b['status'], $sasaran)) {
            $pilih[$kode] = $b['luas'];
        }
    }
    if (empty($pilih)) {
        foreach ($arrBlok as $kode => $b) {
            $pilih[$kode] = $b['luas'];
        }
    }

    $porsi = array();
    $ttlLuas = array_sum($pilih);
    foreach ($pilih as $kode => $luas) {
        $p = ($ttlLuas > 0) ? ($luas / $ttlLuas) : (1 / count($pilih));
        if ($p > 0) {
            $porsi[$kode] = $p;
        }
    }
    return $porsi;
}

#tambah satu baris vhc_rundt_detail ke daftar; kunci sama (kegiatan, alokasi, segment, km/hm awal) digabung jam, muatan, dan preminya
function tambahDetailBU(&$detail, $baris, $jenispekerjaan, $alokasi, $jam, $muatan, $premi)
{
    $kunci = $jenispekerjaan . '|' . $alokasi . '|' . $baris['kodesegment'] . '|' . floatval($baris['kmhmawal']);
    if (isset($detail[$kunci])) {
        $detail[$kunci]['jumlah'] += $jam;
        $detail[$kunci]['beratmuatan'] += $muatan;
        $detail[$kunci]['premiopt'] += $premi;
        return;
    }
    $detail[$kunci] = array(
        'notransaksi'   => $baris['notransaksi'],
        'jenispekerjaan' => $jenispekerjaan,
        'alokasibiaya'  => $alokasi,
        'beratmuatan'   => $muatan,
        'jumlahrit'     => $baris['jumlahrit'],
        'keterangan'    => is_null($baris['keterangan']) ? null : addslashes($baris['keterangan']),
        'biaya'         => $baris['biaya'],
        'kmhmawal'      => $baris['kmhmawal'],
        'kmhmakhir'     => $baris['kmhmakhir'],
        'jumlah'        => $jam,
        'satuan'        => $baris['satuan'],
        'kodesegment'   => $baris['kodesegment'],
        'operator'      => $baris['operator'],
        'helper'        => $baris['helper'],
        'helper2'       => $baris['helper2'],
        'helper3'       => $baris['helper3'],
        'denda'         => $baris['denda'],
        'premiopt'      => $premi
    );
}
