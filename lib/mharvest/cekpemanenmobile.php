<?php
include_once('lib/mharvest/getContentAPI.php');

# Pemanen mutu hancak tanpa janjang dan tanpa denda tidak berpengaruh ke premi, jadi tidak dihitung sebagai "kurang"
function pemanenMutuKosong($row)
{
    $jjg = (float)$row['jjgbuahbesar'] + (float)$row['jjgbuahkecil'];
    $denda = isset($row['penalti']) && is_array($row['penalti']) ? array_sum(array_map('floatval', $row['penalti'])) : 0;
    return $jjg == 0 && $denda == 0;
}

# Cek kelengkapan pemanen sebelum posting: bandingkan pemanen di mobile dengan yang sudah ada di ERP
# $tipe: 'mutu' (Mutu Hancak Panen) atau 'ha' (HA Panen), $tanggal: Y-m-d, $nikmandor: karyawanid mandor
# Hasil: array('gagal' => bool, 'kurang' => array(array('kodeorg' =>, 'nik' =>)))
function cekPemanenMobile($tipe, $tanggal, $nikmandor)
{
    global $dbname, $owlPDO;

    $hasil = array('gagal' => false, 'kurang' => array());

    $expri = explode("/", $_SERVER['REQUEST_URI']);
    $svr = parse_url($_SERVER['HTTP_REFERER']);
    $pat = explode('/', $svr['path']);
    $arr = array_filter($pat, function ($value) {
        return !is_null($value) && $value !== '';
    });
    $data = array();
    foreach ($arr as $key => $value) {
        if (!strpos($value, ".php")) {
            $data[] = $value;
        }
    }
    $urlocal = $_SERVER['HTTP_ORIGIN'] . '/' . implode("/", $data);

    $options = array(
        'client_id' => 'USERSYSTEM',
        'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
        'username' => $_SESSION['standard']['username']
    );

    $endpoint = $tipe == 'mutu' ? 'mharvest/hancakdetails/send' : 'mluas/erpDetail/send';

    if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
        if (strlen($expri[1]) <= 7) {
            $urlKey = $_SERVER['HTTP_ORIGIN'] . "/" . $expri[1] . "/mobile/index.php/api/access_token/api_key";
            $urlDetail = $_SERVER['HTTP_ORIGIN'] . '/' . $expri[1] . '/mobile/index.php/api/module/' . $endpoint;
        } else {
            $urlKey = $_SERVER['HTTP_ORIGIN'] . "/mobile/index.php/api/access_token/api_key";
            $urlDetail = $_SERVER['HTTP_ORIGIN'] . '/mobile/index.php/api/module/' . $endpoint;
        }
    } else {
        $urlKey = $urlocal . "mobile/index.php/api/access_token/api_key";
        $urlDetail = $urlocal . 'mobile/index.php/api/module/' . $endpoint;
    }

    $getApi = new getContentAPI;
    $getApi->init($urlKey, $options);
    $getApi->post($urlDetail, array(
        'tanggal' => $tanggal,
        'nikmandor' => $nikmandor,
        'kodeorg' => getOrgDetail(28),
    ));

    // Hanya dua respons yang dianggap valid: status 200 (ada data) atau 404 dengan pesan "tidak ada ..." (mobile memang kosong).
    // Selain itu (respons kosong, error otorisasi/server, gagal terhubung, modul tidak ditemukan) = gagal dicek.
    $resp = $getApi->response;
    $status = is_array($resp) && isset($resp['status']) ? (int)$resp['status'] : 0;
    $pesan = is_array($resp) && isset($resp['message']) ? strtolower((string)$resp['message']) : '';
    if ($status == 404 && strpos($pesan, 'tidak ada') !== false) {
        return $hasil;
    }
    if ($status != 200) {
        $hasil['gagal'] = true;
        return $hasil;
    }
    $rowsMobile = isset($resp['result']['data']) ? $resp['result']['data'] : null;
    if (!is_array($rowsMobile) || count($rowsMobile) == 0) {
        return $hasil;
    }

    $tbl = $tipe == 'mutu' ? 'kebun_rekapmutuhancakpanen' : 'kebun_rekaphancakpanen';
    $sudahAda = array();
    $sql = "SELECT DISTINCT kodeorg, nik FROM " . $dbname . "." . $tbl . " WHERE tanggal='" . $tanggal . "' AND nikmandor='" . $nikmandor . "'";
    foreach ($owlPDO->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $sudahAda[strtoupper($r['kodeorg']) . '|' . (int)$r['nik']] = 1;
    }

    $sudahDicatat = array();
    foreach ($rowsMobile as $row) {
        $kodeorg = $tipe == 'mutu' ? $row['kodeorg'] : $row['blok'];
        $nik = $tipe == 'mutu' ? $row['nik'] : $row['pemanen'];
        if ($kodeorg == '' || $nik == '') {
            continue;
        }
        if ($tipe == 'mutu' && pemanenMutuKosong($row)) {
            continue;
        }
        $k = strtoupper($kodeorg) . '|' . (int)$nik;
        if (!isset($sudahAda[$k]) && !isset($sudahDicatat[$k])) {
            $sudahDicatat[$k] = 1;
            $hasil['kurang'][] = array('kodeorg' => $kodeorg, 'nik' => $nik);
        }
    }
    return $hasil;
}

# Keterangan perbedaan data ERP dengan data mobile, ditampilkan saat akan posting (tidak memblokir posting)
# Data mobile bisa direvisi setelah didownload, jadi perbedaan hanya diinformasikan
function pesanPemanenKurang($hasil, $tanggal, $nikmandor)
{
    if ($hasil['gagal']) {
        return "Perhatian: pengecekan ke data mobile tidak dapat dilakukan, perbedaan dengan data mobile belum bisa diperiksa.";
    }
    if (count($hasil['kurang']) == 0) {
        return "";
    }
    $nama = array();
    foreach ($hasil['kurang'] as $k) {
        $nama[] = getNamaKaryawan($k['nik']) . " (" . $k['kodeorg'] . ")";
    }
    return "Perhatian: data di ERP berbeda dengan data di mobile (tanggal " . tanggalnormal($tanggal) . ", mandor " . getNamaKaryawan($nikmandor) . ").<br>"
        . count($hasil['kurang']) . " pemanen ada di mobile tetapi tidak ada di ERP:<br>"
        . implode("<br>", array_slice($nama, 0, 15)) . (count($nama) > 15 ? "<br>dan " . (count($nama) - 15) . " lainnya" : "")
        . "<br><br>Data mobile dapat saja sudah direvisi setelah didownload.";
}

# Siapkan klien API mobile: kembalikan array(klien, url modul) untuk endpoint tertentu
function mobileApiSiap($endpoint)
{
    $expri = explode("/", $_SERVER['REQUEST_URI']);
    $svr = parse_url($_SERVER['HTTP_REFERER']);
    $pat = explode('/', $svr['path']);
    $arr = array_filter($pat, function ($value) {
        return !is_null($value) && $value !== '';
    });
    $data = array();
    foreach ($arr as $key => $value) {
        if (!strpos($value, ".php")) {
            $data[] = $value;
        }
    }
    $urlocal = $_SERVER['HTTP_ORIGIN'] . '/' . implode("/", $data);

    $options = array(
        'client_id' => 'USERSYSTEM',
        'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
        'username' => $_SESSION['standard']['username']
    );

    if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
        if (strlen($expri[1]) <= 7) {
            $urlKey = $_SERVER['HTTP_ORIGIN'] . "/" . $expri[1] . "/mobile/index.php/api/access_token/api_key";
            $urlModul = $_SERVER['HTTP_ORIGIN'] . '/' . $expri[1] . '/mobile/index.php/api/module/' . $endpoint;
        } else {
            $urlKey = $_SERVER['HTTP_ORIGIN'] . "/mobile/index.php/api/access_token/api_key";
            $urlModul = $_SERVER['HTTP_ORIGIN'] . '/mobile/index.php/api/module/' . $endpoint;
        }
    } else {
        $urlKey = $urlocal . "mobile/index.php/api/access_token/api_key";
        $urlModul = $urlocal . 'mobile/index.php/api/module/' . $endpoint;
    }

    $getApi = new getContentAPI;
    $getApi->init($urlKey, $options);
    return array($getApi, $urlModul);
}

# Bandingkan data mobile dengan ERP untuk banyak grup (tanggal + mandor) sekaligus, untuk mewarnai baris list
# $grup: array(array('tanggal' => Y-m-d, 'nikmandor' => karyawanid)); $tipe: 'ha' atau 'mutu'
# Hasil: array('Y-m-d|idmandor' => jumlah pemanen di mobile yang tidak ada di ERP); kosong bila tidak ada selisih atau API tidak bisa dijangkau
function bandingkanGrupMobile($tipe, $grup)
{
    global $dbname, $owlPDO;
    $hasil = array();
    if (count($grup) == 0) {
        return $hasil;
    }
    $tanggal = array();
    $kunciGrup = array();
    foreach ($grup as $g) {
        $tanggal[$g['tanggal']] = $g['tanggal'];
        $kunciGrup[$g['tanggal'] . '|' . (int)$g['nikmandor']] = 1;
    }

    list($api, $url) = mobileApiSiap($tipe == 'mutu' ? 'mharvest/hancakdetails/send' : 'mluas/erpDetail/send');
    $panggilan = array();
    if ($tipe == 'mutu') {
        foreach (array_slice(array_values($tanggal), 0, 12) as $t) {
            $panggilan[] = array('tanggal' => $t, 'kodeorg' => getOrgDetail(28));
        }
    } else {
        $periode = array();
        foreach ($tanggal as $t) {
            $periode[substr($t, 0, 7)] = 1;
        }
        foreach (array_keys($periode) as $p) {
            $panggilan[] = array('periode' => $p, 'kodeorg' => getOrgDetail(28));
        }
    }
    $rowsMobile = array();
    foreach ($panggilan as $dp) {
        $api->post($url, $dp);
        $resp = $api->response;
        $rows = (is_array($resp) && isset($resp['status']) && (int)$resp['status'] == 200 && isset($resp['result']['data'])) ? $resp['result']['data'] : null;
        if (is_array($rows)) {
            foreach ($rows as $r) {
                $rowsMobile[] = $r;
            }
        }
    }
    if (count($rowsMobile) == 0) {
        return $hasil;
    }

    $tbl = $tipe == 'mutu' ? 'kebun_rekapmutuhancakpanen' : 'kebun_rekaphancakpanen';
    $ph = implode(',', array_fill(0, count($tanggal), '?'));
    $stmt = $owlPDO->prepare("SELECT DISTINCT tanggal, nikmandor, kodeorg, nik FROM " . $dbname . "." . $tbl . " WHERE tanggal IN (" . $ph . ")");
    $stmt->execute(array_values($tanggal));
    $erp = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $erp[$r['tanggal'] . '|' . (int)$r['nikmandor']][strtoupper($r['kodeorg']) . '|' . (int)$r['nik']] = 1;
    }

    $kurang = array();
    foreach ($rowsMobile as $row) {
        $mandor = $tipe == 'mutu' ? $row['nikmandor'] : $row['mandor'];
        $kodeorg = $tipe == 'mutu' ? $row['kodeorg'] : $row['blok'];
        $nik = $tipe == 'mutu' ? $row['nik'] : $row['pemanen'];
        $kg = $row['tanggal'] . '|' . (int)$mandor;
        if (!isset($kunciGrup[$kg]) || $kodeorg == '' || $nik == '') {
            continue;
        }
        if ($tipe == 'mutu' && pemanenMutuKosong($row)) {
            continue;
        }
        $k = strtoupper($kodeorg) . '|' . (int)$nik;
        if (!isset($erp[$kg][$k])) {
            $kurang[$kg][$k] = 1;
        }
    }
    foreach ($kurang as $kg => $set) {
        $hasil[$kg] = count($set);
    }
    return $hasil;
}
