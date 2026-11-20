<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/zAlokasiGajiPPP.php');

#Setup Alokasi Komponen Gaji per PT: satu daftar aturan untuk komponen (semua jabatan) dan komponen + jabatan tertentu
$method = checkPostGet('method', '');
$kodejabatan = checkPostGet('kodejabatan', '0');
$idkomponen = checkPostGet('idkomponen', '');
$jenisbiaya = checkPostGet('jenisbiaya', '');
$noakundebet = trim((string)checkPostGet('noakundebet', ''));
$aktif = checkPostGet('aktif', '1');
$kodept = $_SESSION['empl']['kodeorganisasi'];
$arrJenis = jenisBiayaPPP();

if (!in_array($method, array('loadData', 'save', 'delete'), true)) {
    exit("Warning : Method tidak dikenal.");
}
if (!preg_match('/^[A-Za-z0-9]{1,10}$/', (string)$kodept)) {
    exit("Warning : PT pengguna tidak valid.");
}

#validasi input simpan/hapus (selalu di sisi server walau JS sudah memeriksa)
function val5alokasiKomponen($metode, $kodejabatan, $idkomponen, $jenisbiaya, $noakundebet, $aktif)
{
    global $dbname, $owlPDO, $arrJenis;
    if (!is_string($kodejabatan) || !preg_match('/^[0-9]{1,10}$/', $kodejabatan)) {
        exit("Warning : Jabatan tidak valid.");
    }
    if (!is_string($idkomponen) || !preg_match('/^[0-9]{1,10}$/', $idkomponen)) {
        exit("Warning : Komponen wajib dipilih.");
    }
    if ($metode == 'delete') {
        return;
    }
    if ((int)$kodejabatan > 0) {
        $stm = $owlPDO->prepare("select kodejabatan from " . $dbname . ".sdm_5jabatan where kodejabatan=?");
        $stm->execute(array($kodejabatan));
        if (!$stm->fetchColumn()) {
            exit("Warning : Jabatan tidak ditemukan.");
        }
    }
    if ((int)$idkomponen == 0) {
        #aturan "semua komponen": hanya untuk jabatan tertentu, hanya akun debet (wajib), tidak berlaku untuk BPJS
        if ((int)$kodejabatan == 0) {
            exit("Warning : Aturan Semua komponen harus dipilihkan jabatan tertentu.");
        }
        if ($jenisbiaya != '') {
            exit("Warning : Aturan Semua komponen hanya mengatur Akun Debet; kosongkan Jenis Biaya.");
        }
        if ($noakundebet == '') {
            exit("Warning : Aturan Semua komponen wajib mengisi Akun Debet.");
        }
    } else {
        $stm = $owlPDO->prepare("select id from " . $dbname . ".sdm_ho_component where id=? and plus=1");
        $stm->execute(array($idkomponen));
        if (!$stm->fetchColumn()) {
            exit("Warning : Komponen tidak ditemukan atau bukan komponen pendapatan.");
        }
    }
    if (!is_string($jenisbiaya) || ($jenisbiaya != '' && !isset($arrJenis[$jenisbiaya]))) {
        exit("Warning : Jenis Biaya tidak valid.");
    }
    if ((int)$idkomponen > 0 && (int)$kodejabatan == 0 && $jenisbiaya == '') {
        exit("Warning : Jenis Biaya wajib dipilih untuk aturan semua jabatan.");
    }
    if ((int)$idkomponen > 0 && (int)$kodejabatan > 0 && $jenisbiaya == '' && $noakundebet == '') {
        exit("Warning : Aturan per jabatan harus mengisi Jenis Biaya atau Akun Debet.");
    }
    if (!in_array($aktif, array('0', '1'), true)) {
        exit("Warning : Status aktif tidak valid.");
    }
    if ($noakundebet != '') {
        if (substr($noakundebet, 0, 1) != '7') {
            exit("Warning : Akun Debet " . htmlspecialchars($noakundebet) . " tidak valid. Akun Debet harus berawalan 7.");
        }
        $stm = $owlPDO->prepare("select noakun,detail from " . $dbname . ".keu_5akun where noakun=?");
        $stm->execute(array($noakundebet));
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            exit("Warning : Akun Debet " . htmlspecialchars($noakundebet) . " tidak ditemukan di master akun.");
        }
        if ($row['detail'] != 1) {
            exit("Warning : Akun Debet " . htmlspecialchars($noakundebet) . " bukan akun detail.");
        }
    }
}

switch ($method) {
    case 'save':
        val5alokasiKomponen('save', $kodejabatan, $idkomponen, $jenisbiaya, $noakundebet, $aktif);
        $stm = $owlPDO->prepare("insert into " . $dbname . ".keu_5alokasikomponen (kodept,kodejabatan,idkomponen,jenisbiaya,noakundebet,aktif,createdby,updateby)
            values (?,?,?,?,?,?,?,?)
            on duplicate key update jenisbiaya=values(jenisbiaya), noakundebet=values(noakundebet), aktif=values(aktif), updateby=values(updateby)");
        try {
            $stm->execute(array($kodept, $kodejabatan, $idkomponen, $jenisbiaya, $noakundebet, $aktif, $_SESSION['standard']['userid'], $_SESSION['standard']['userid']));
        } catch (PDOException $e) {
            exit("Gagal : Setup tidak bisa disimpan. Pastikan sql_alokasi_komponen_ppp.sql sudah dijalankan. " . $e->getMessage());
        }
        break;

    case 'delete':
        val5alokasiKomponen('delete', $kodejabatan, $idkomponen, '', '', '1');
        $stm = $owlPDO->prepare("delete from " . $dbname . ".keu_5alokasikomponen where kodept=? and kodejabatan=? and idkomponen=?");
        try {
            $stm->execute(array($kodept, $kodejabatan, $idkomponen));
        } catch (PDOException $e) {
            exit("Gagal : " . $e->getMessage());
        }
        break;

    case 'loadData':
        $nmKomponen = makeOption($dbname, 'sdm_ho_component', 'id,name');
        $nmJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
        $nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun like '7%' or noakun like '2%' or noakun like '4%' or noakun like '8%'");
        try {
            $stm = $owlPDO->prepare("select * from " . $dbname . ".keu_5alokasikomponen where kodept=? order by idkomponen, kodejabatan");
            $stm->execute(array($kodept));
            $res = $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            exit("Gagal : Tabel setup belum ada. Jalankan sql_alokasi_komponen_ppp.sql terlebih dahulu.");
        }
        #akun default dari parameter jurnal, supaya terlihat di sini tanpa membuka menu Parameter Jurnal
        $paramJurnal = array();
        foreach (fetchData("select jurnalid,noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal") as $barPj) {
            $paramJurnal[$barPj['jurnalid']] = $barPj;
        }
        $teksAkun = function ($noakun) use ($nmAkun) {
            return $noakun == '' ? '(belum diatur)' : $noakun . ' - ' . (isset($nmAkun[$noakun]) ? $nmAkun[$noakun] : '');
        };
        $jenisSemua = array();
        foreach ($res as $rr) {
            if ((int)$rr['kodejabatan'] == 0) {
                $jenisSemua[(int)$rr['idkomponen']] = $rr['jenisbiaya'];
            }
        }
        #nama user pembuat/pengubah (createdby/updateby berisi karyawanid)
        $idUser = array();
        foreach ($res as $rr) {
            $idUser[(int)$rr['createdby']] = (int)$rr['createdby'];
            $idUser[(int)$rr['updateby']] = (int)$rr['updateby'];
        }
        $nmUser = array();
        if (count($idUser) > 0) {
            foreach (fetchData("select karyawanid,namakaryawan from " . $dbname . ".datakaryawan where karyawanid in (" . implode(',', $idUser) . ")") as $barU) {
                $nmUser[(int)$barU['karyawanid']] = $barU['namakaryawan'];
            }
        }
        $namaBagian = array('KBNB' => 'Kebun', 'GJHO' => 'HO', 'WSG' => 'Bengkel', 'VHCU' => 'Traksi umum', 'VHCG' => 'Traksi');
        $no = 0;
        foreach ($res as $r) {
            $no++;
            $akun = trim($r['noakundebet']);
            $jab = (int)$r['kodejabatan'];
            #jenis biaya yang berlaku untuk baris ini (kosong = ikut aturan semua jabatan, lalu Setup Bawaan)
            $jenisEf = $r['jenisbiaya'];
            if ($jenisEf == '') {
                $jenisEf = isset($jenisSemua[(int)$r['idkomponen']]) && $jenisSemua[(int)$r['idkomponen']] != '' ? $jenisSemua[(int)$r['idkomponen']] : jenisBiayaBawaanPPP($r['idkomponen']);
            }
            $petaKebun = jurnalidBagianPPP('KBNB');
            $gidKebun = isset($petaKebun[$jenisEf]) ? $petaKebun[$jenisEf] : 'KBNB2';
            $defKebun = isset($paramJurnal[$gidKebun]) ? $paramJurnal[$gidKebun] : array('noakundebet' => '', 'noakunkredit' => '');
            #rincian default semua bagian (muncul sebagai tooltip dan teks kecil)
            $rincianBagian = array();
            foreach ($namaBagian as $kdBagian => $nmBagian) {
                $pt = jurnalidBagianPPP($kdBagian);
                $gid = isset($pt[$jenisEf]) ? $pt[$jenisEf] : '';
                $pj = isset($paramJurnal[$gid]) ? $paramJurnal[$gid] : array('noakundebet' => '', 'noakunkredit' => '');
                $rincianBagian[] = $nmBagian . ' ' . $gid . ': D ' . $teksAkun($pj['noakundebet']) . ' / K ' . $teksAkun($pj['noakunkredit']);
            }
            $selJurnal = "<td align=center title=\"" . htmlspecialchars(implode("\n", $rincianBagian), ENT_QUOTES) . "\">" . htmlspecialchars($gidKebun) . "</td>";
            $selDebet = ($akun != '')
                ? "<td><b>" . htmlspecialchars($teksAkun($akun)) . "</b><br><span style='font-size:10px;color:#444'>diganti dari default " . htmlspecialchars($defKebun['noakundebet']) . "</span></td>"
                : "<td title=\"" . htmlspecialchars(implode("\n", $rincianBagian), ENT_QUOTES) . "\">" . htmlspecialchars($teksAkun($defKebun['noakundebet'])) . "<br><span style='font-size:10px;color:#444'><i>default parameter jurnal " . htmlspecialchars($gidKebun) . "</i></span></td>";
            $selKredit = "<td>" . htmlspecialchars($teksAkun($defKebun['noakunkredit'])) . "</td>";            echo "<tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td align=center>" . (int)$r['idkomponen'] . "</td>
                <td>" . ((int)$r['idkomponen'] == 0 ? "<i>Semua komponen (kecuali BPJS)</i>" : htmlspecialchars((string)@$nmKomponen[$r['idkomponen']])) . "</td>
                <td>" . ($jab == 0 ? "<i>Semua jabatan</i>" : htmlspecialchars((string)@$nmJabatan[$jab])) . "</td>
                <td>" . ($r['jenisbiaya'] == '' ? "<i>ikut aturan semua jabatan</i> (" . htmlspecialchars((string)@$arrJenis[$jenisEf]) . ")" : htmlspecialchars((string)@$arrJenis[$r['jenisbiaya']])) . "</td>
                " . $selJurnal . $selDebet . $selKredit . "
                <td align=center>" . ($r['aktif'] == 1 ? 'Ya' : 'Tidak') . "</td>
                <td>" . htmlspecialchars((string)@$nmUser[(int)$r['createdby']]) . "</td>
                <td align=center nowrap>" . htmlspecialchars((string)$r['createdtime']) . "</td>
                <td>" . htmlspecialchars((string)@$nmUser[(int)$r['updateby']]) . "</td>
                <td align=center nowrap>" . htmlspecialchars((string)$r['updatetime']) . "</td>
                <td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn title='Edit' onclick=\"edit5alokasi('" . $jab . "','" . (int)$r['idkomponen'] . "','" . htmlspecialchars($r['jenisbiaya'], ENT_QUOTES) . "','" . htmlspecialchars(preg_replace('/[^0-9A-Za-z]/', '', $akun), ENT_QUOTES) . "','" . (int)$r['aktif'] . "');\"></td>
                <td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn title='Hapus' onclick=\"hapus5alokasi('" . $jab . "','" . (int)$r['idkomponen'] . "');\"></td>
            </tr>";
        }
        if ($no == 0) {
            echo "<tr class=rowcontent><td colspan=15 align=center>Belum ada pengecualian. Semua komponen memakai Setup Bawaan (lihat tabel Setup Bawaan di bawah).</td></tr>";
        }
        break;
}
