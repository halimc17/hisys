<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/zAlokasiGajiPPP.php');

#Setup Alokasi Komponen Gaji per PT, dua bagian:
#  1. Komponen Upah (Setup Upah, tipe basic): satu akun debet per jabatan untuk semua komponen upah jabatan itu (idkomponen 0, tanpa jenis biaya)
#  2. Pendapatan Lain (komponen tambahan): jenis biaya + akun debet (opsional) per komponen, untuk semua jabatan (kodejabatan 0) atau jabatan tertentu
$method = checkPostGet('method', '');
$kodejabatan = checkPostGet('kodejabatan', '0');
$idkomponen = checkPostGet('idkomponen', '');
$jenisbiaya = checkPostGet('jenisbiaya', '');
$noakundebet = trim((string)checkPostGet('noakundebet', ''));
$aktif = checkPostGet('aktif', '1');
$kodept = $_SESSION['empl']['kodeorganisasi'];
$arrJenis = jenisBiayaPPP();

if (!in_array($method, array('loadUpah', 'loadLain', 'saveUpah', 'saveLain', 'delete'), true)) {
    exit("Warning : Method tidak dikenal.");
}
if (!preg_match('/^[A-Za-z0-9]{1,10}$/', (string)$kodept)) {
    exit("Warning : PT pengguna tidak valid.");
}

#akun debet harus akun detail berawalan 7 yang ada di master akun ('' = tidak diisi)
function cekAkun5alokasi($noakundebet)
{
    global $dbname, $owlPDO;
    if ($noakundebet == '') {
        return;
    }
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

function cekAktif5alokasi($aktif)
{
    if (!in_array($aktif, array('0', '1'), true)) {
        exit("Warning : Status aktif tidak valid.");
    }
}

function cekJabatan5alokasi($kodejabatan)
{
    global $dbname, $owlPDO;
    if (!is_string($kodejabatan) || !preg_match('/^[0-9]{1,10}$/', $kodejabatan)) {
        exit("Warning : Jabatan tidak valid.");
    }
}

function simpan5alokasi($kodept, $kodejabatan, $idkomponen, $jenisbiaya, $noakundebet, $aktif)
{
    global $dbname, $owlPDO;
    $stm = $owlPDO->prepare("insert into " . $dbname . ".keu_5alokasikomponen (kodept,kodejabatan,idkomponen,jenisbiaya,noakundebet,aktif,createdby,updateby)
        values (?,?,?,?,?,?,?,?)
        on duplicate key update jenisbiaya=values(jenisbiaya), noakundebet=values(noakundebet), aktif=values(aktif), updateby=values(updateby)");
    try {
        $stm->execute(array($kodept, $kodejabatan, $idkomponen, $jenisbiaya, $noakundebet, $aktif, $_SESSION['standard']['userid'], $_SESSION['standard']['userid']));
    } catch (PDOException $e) {
        exit("Gagal : Setup tidak bisa disimpan. Pastikan tabel keu_5alokasikomponen sudah ada.");
    }
}

#teks akun: "noakun - nama" atau "(belum diatur)"
function teksAkun5alokasi($noakun, $nmAkun)
{
    return $noakun == '' ? '(belum diatur)' : $noakun . ' - ' . (isset($nmAkun[$noakun]) ? $nmAkun[$noakun] : '');
}

switch ($method) {
    case 'saveUpah':
        #Komponen Upah: jabatan tertentu wajib, akun debet wajib, tanpa jenis biaya
        cekJabatan5alokasi($kodejabatan);
        if ((int)$kodejabatan <= 0) {
            exit("Warning : Jabatan wajib dipilih.");
        }
        $stm = $owlPDO->prepare("select kodejabatan from " . $dbname . ".sdm_5jabatan where kodejabatan=?");
        $stm->execute(array($kodejabatan));
        if (!$stm->fetchColumn()) {
            exit("Warning : Jabatan tidak ditemukan.");
        }
        if ($noakundebet == '') {
            exit("Warning : Akun Debet wajib dipilih.");
        }
        cekAkun5alokasi($noakundebet);
        cekAktif5alokasi($aktif);
        simpan5alokasi($kodept, $kodejabatan, '0', '', $noakundebet, $aktif);
        break;

    case 'saveLain':
        #Pendapatan Lain: komponen tambahan pendapatan (bukan komponen upah); semua jabatan (jenis biaya wajib) atau jabatan tertentu (jenis biaya atau akun diisi)
        cekJabatan5alokasi($kodejabatan);
        if ((int)$kodejabatan > 0) {
            $stm = $owlPDO->prepare("select kodejabatan from " . $dbname . ".sdm_5jabatan where kodejabatan=?");
            $stm->execute(array($kodejabatan));
            if (!$stm->fetchColumn()) {
                exit("Warning : Jabatan tidak ditemukan.");
            }
        }
        if (!is_string($idkomponen) || !preg_match('/^[0-9]{1,10}$/', $idkomponen) || (int)$idkomponen <= 0) {
            exit("Warning : Komponen wajib dipilih.");
        }
        $stm = $owlPDO->prepare("select id from " . $dbname . ".sdm_ho_component where id=? and plus=1 and type<>'basic'");
        $stm->execute(array($idkomponen));
        if (!$stm->fetchColumn()) {
            exit("Warning : Komponen tidak ditemukan, bukan komponen pendapatan, atau termasuk komponen upah (diatur per jabatan di tab Komponen Upah).");
        }
        if (!is_string($jenisbiaya) || ($jenisbiaya != '' && !isset($arrJenis[$jenisbiaya]))) {
            exit("Warning : Jenis Biaya tidak valid.");
        }
        if ((int)$kodejabatan == 0 && $jenisbiaya == '') {
            exit("Warning : Jenis Biaya wajib dipilih untuk aturan semua jabatan.");
        }
        if ((int)$kodejabatan > 0 && $jenisbiaya == '' && $noakundebet == '') {
            exit("Warning : Aturan per jabatan harus mengisi Jenis Biaya atau Akun Debet.");
        }
        cekAkun5alokasi($noakundebet);
        cekAktif5alokasi($aktif);
        simpan5alokasi($kodept, $kodejabatan, $idkomponen, $jenisbiaya, $noakundebet, $aktif);
        break;

    case 'delete':
        cekJabatan5alokasi($kodejabatan);
        if (!is_string($idkomponen) || !preg_match('/^[0-9]{1,10}$/', $idkomponen)) {
            exit("Warning : Komponen tidak valid.");
        }
        $stm = $owlPDO->prepare("delete from " . $dbname . ".keu_5alokasikomponen where kodept=? and kodejabatan=? and idkomponen=?");
        try {
            $stm->execute(array($kodept, $kodejabatan, $idkomponen));
        } catch (PDOException $e) {
            exit("Gagal : Aturan tidak bisa dihapus.");
        }
        break;

    case 'loadUpah':
        $nmJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
        $nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun like '7%'");
        try {
            $stm = $owlPDO->prepare("select kodejabatan,noakundebet,aktif from " . $dbname . ".keu_5alokasikomponen where kodept=? and idkomponen=0 order by kodejabatan");
            $stm->execute(array($kodept));
            $res = $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            exit("Gagal : Tabel setup belum ada.");
        }
        $no = 0;
        foreach ($res as $r) {
            $no++;
            $jab = (int)$r['kodejabatan'];
            $akun = preg_replace('/[^0-9A-Za-z]/', '', trim($r['noakundebet']));
            echo "<tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td>" . htmlspecialchars((string)@$nmJabatan[$jab]) . "</td>
                <td>" . htmlspecialchars(teksAkun5alokasi($akun, $nmAkun)) . "</td>
                <td align=center>" . ($r['aktif'] == 1 ? 'Ya' : 'Tidak') . "</td>
                <td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn title='Edit' onclick=\"editUpah5('" . $jab . "','" . htmlspecialchars($akun, ENT_QUOTES) . "','" . (int)$r['aktif'] . "');\"></td>
                <td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn title='Hapus' onclick=\"hapus5('" . $jab . "','0','upah');\"></td>
            </tr>";
        }
        if ($no == 0) {
            echo "<tr class=rowcontent><td colspan=6 align=center>Belum ada aturan. Semua komponen upah memakai akun bawaan (lihat bagian Akun Bawaan di bawah).</td></tr>";
        }
        break;

    case 'loadLain':
        $nmKomponen = makeOption($dbname, 'sdm_ho_component', 'id,name');
        $tipeKomponen = makeOption($dbname, 'sdm_ho_component', 'id,type');
        $nmJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
        $nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun like '7%' or noakun like '2%' or noakun like '4%' or noakun like '8%'");
        try {
            $stm = $owlPDO->prepare("select kodejabatan,idkomponen,jenisbiaya,noakundebet,aktif from " . $dbname . ".keu_5alokasikomponen where kodept=? and idkomponen>0 order by idkomponen, kodejabatan");
            $stm->execute(array($kodept));
            $res = $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            exit("Gagal : Tabel setup belum ada.");
        }
        $paramJurnal = array();
        foreach (fetchData("select jurnalid,noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal") as $barPj) {
            $paramJurnal[$barPj['jurnalid']] = $barPj;
        }
        $namaBagian = array('KBNB' => 'Kebun', 'GJHO' => 'HO', 'WSG' => 'Bengkel', 'VHCU' => 'Traksi umum', 'VHCG' => 'Traksi');
        #jenis biaya aturan semua jabatan per komponen (dipakai aturan per jabatan yang jenis biayanya dikosongkan)
        $jenisSemua = array();
        foreach ($res as $rr) {
            if ((int)$rr['kodejabatan'] == 0 && $rr['jenisbiaya'] != '') {
                $jenisSemua[(int)$rr['idkomponen']] = $rr['jenisbiaya'];
            }
        }
        $no = 0;
        foreach ($res as $r) {
            $no++;
            $idk = (int)$r['idkomponen'];
            $jab = (int)$r['kodejabatan'];
            $akun = preg_replace('/[^0-9A-Za-z]/', '', trim($r['noakundebet']));
            $nama = htmlspecialchars(trim((string)@$nmKomponen[$idk])) . " (" . $idk . ")";
            $upah = (@$tipeKomponen[$idk] == 'basic');
            if ($upah) {
                #aturan lama komponen upah: sekarang komponen upah diatur per jabatan, jadi aturan ini tidak dipakai lagi
                echo "<tr class=rowcontent style='color:#777'>
                    <td align=center>" . $no . "</td>
                    <td>" . $nama . "</td>
                    <td colspan=4><i>Aturan lama komponen upah, tidak dipakai lagi (komponen upah diatur per jabatan di tab Komponen Upah). Silakan hapus.</i></td>
                    <td align=center>-</td>
                    <td align=center width=25px></td>
                    <td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn title='Hapus' onclick=\"hapus5('" . $jab . "','" . $idk . "','lain');\"></td>
                </tr>";
                continue;
            }
            $jenisEf = $r['jenisbiaya'] != '' ? $r['jenisbiaya'] : (isset($jenisSemua[$idk]) ? $jenisSemua[$idk] : jenisBiayaBawaanPPP($idk));
            $petaKebun = jurnalidBagianPPP('KBNB');
            $gidKebun = isset($petaKebun[$jenisEf]) ? $petaKebun[$jenisEf] : 'KBNB2';
            $defKebun = isset($paramJurnal[$gidKebun]) ? $paramJurnal[$gidKebun] : array('noakundebet' => '', 'noakunkredit' => '');
            $rincian = array();
            foreach ($namaBagian as $kdBagian => $nmBagian) {
                $pt = jurnalidBagianPPP($kdBagian);
                $gid = isset($pt[$jenisEf]) ? $pt[$jenisEf] : '';
                $pj = isset($paramJurnal[$gid]) ? $paramJurnal[$gid] : array('noakundebet' => '', 'noakunkredit' => '');
                $rincian[] = $nmBagian . ' ' . $gid . ': D ' . teksAkun5alokasi($pj['noakundebet'], $nmAkun) . ' / K ' . teksAkun5alokasi($pj['noakunkredit'], $nmAkun);
            }
            $selAkun = ($akun != '')
                ? "<td><b>" . htmlspecialchars(teksAkun5alokasi($akun, $nmAkun)) . "</b></td>"
                : "<td title=\"" . htmlspecialchars(implode("\n", $rincian), ENT_QUOTES) . "\">" . htmlspecialchars(teksAkun5alokasi($defKebun['noakundebet'], $nmAkun)) . "<br><span style='font-size:10px;color:#444'><i>akun bawaan</i></span></td>";
            $edit = "<img src=images/application/application_edit.png class=zImgBtn title='Edit' onclick=\"editLain5('" . $jab . "','" . $idk . "','" . htmlspecialchars($r['jenisbiaya'], ENT_QUOTES) . "','" . htmlspecialchars($akun, ENT_QUOTES) . "','" . (int)$r['aktif'] . "');\">";
            echo "<tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td>" . $nama . "</td>
                <td>" . ($jab == 0 ? "<i>Semua jabatan</i>" : htmlspecialchars((string)@$nmJabatan[$jab])) . "</td>
                <td>" . ($r['jenisbiaya'] == '' ? "<i>ikut semua jabatan</i> (" . htmlspecialchars((string)@$arrJenis[$jenisEf]) . ")" : htmlspecialchars((string)@$arrJenis[$r['jenisbiaya']])) . "</td>
                " . $selAkun . "
                <td align=center>" . ($r['aktif'] == 1 ? 'Ya' : 'Tidak') . "</td>
                <td align=center width=25px>" . $edit . "</td>
                <td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn title='Hapus' onclick=\"hapus5('" . $jab . "','" . $idk . "','lain');\"></td>
            </tr>";
        }
        if ($no == 0) {
            echo "<tr class=rowcontent><td colspan=8 align=center>Belum ada aturan. Semua komponen Pendapatan Lain memakai jenis biaya dan akun bawaan (lihat Akun Bawaan di bawah).</td></tr>";
        }
        break;
}
