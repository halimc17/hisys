<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include("lib/mharvest/getContentAPI.php");

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$tgl       = tanggalsystemn(checkPostGet('tgl', ''));
$tgl2       = checkPostGet('tgl2', '');
$method    = checkPostGet('method', '');
$kodeorg      = checkPostGet('kodeorg', '');
$nikmandor    = checkPostGet('nikmandor', '');
$periodesch   = checkPostGet('periodesch', '');
$tglsch1      = checkPostGet('tglsch1', '');
$tglsch2      = checkPostGet('tglsch2', '');
$divisisch    = checkPostGet('divisisch', '');
$mandorsch    = checkPostGet('mandorsch', '');
$cekPemanen   = checkPostGet('cekpemanen', '') == '1';
$nmorg     = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmindk     = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');
$nmkar     = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

$getApi = new getContentAPI;

switch ($method) {
    case 'loaddata':
        $tab = "";
        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0) {
                $page = 0;
            }
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        // GET URI FOR PRODUCTION
        $expri = explode("/", $_SERVER['REQUEST_URI']);

        $svr = parse_url($_SERVER['HTTP_REFERER']);
        $pat = array();
        $pat = explode('/', $svr['path']);
        $arr = array_filter($pat, function ($value) {
            return !is_null($value) && $value !== '';
        });

        $data = [];
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
        /** GET API KEY */
        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
        if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
            if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN'] . "/" . $expri[1] . "/mobile/index.php/api/access_token/api_key";
            } else {
                // Jika tidak, maka tidak munculkan uri array[1]
                $url = $_SERVER['HTTP_ORIGIN'] . "/mobile/index.php/api/access_token/api_key";
            }
        } else {
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal . "mobile/index.php/api/access_token/api_key";
        }

        $getApi->init($url, $options);
        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
        if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
            if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN'] . '/' . $expri[1] . '/mobile/index.php/api/module/mharvest/hancakheaders/send';
            } else {
                $url = $_SERVER['HTTP_ORIGIN'] . '/mobile/index.php/api/module/mharvest/hancakheaders/send';
            }
        } else {
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal . 'mobile/index.php/api/module/mharvest/hancakheaders/send';
        }

        // Filter tanggal (dd-mm-YYYY), divisi dan mandor
        $tglDari = $tglsch1 != '' ? tanggalsystemn($tglsch1) : '';
        $tglSampai = $tglsch2 != '' ? tanggalsystemn($tglsch2) : '';
        if ($tglDari != '' && $tglSampai == '') {
            $tglSampai = $tglDari;
        }
        if ($tglSampai != '' && $tglDari == '') {
            $tglDari = $tglSampai;
        }
        $periodeApi = $periodesch != "" ? $periodesch : ($tglDari != '' ? substr($tglDari, 0, 7) : "");

        $dataParam = array(
            'periode' => $periodeApi,
            'kodeorg' => getOrgDetail(28),
        );
        if ($mandorsch != '') {
            $dataParam['nikmandor'] = $mandorsch;
        }
        if ($tglDari != '' && $tglDari == $tglSampai) {
            $dataParam['tanggal'] = $tglDari;
        }

        $nok = 0;
        $data = $getApi->post($url, $dataParam);
        $rowsApi = $data->response['result']['data'];

        if (is_array($rowsApi) && count($rowsApi) > 0) {
            $kandidat = array();
            $tglMin = '';
            $tglMax = '';
            foreach ($rowsApi as $val) {
                if ($tglDari != '' && ($val['tanggal'] < $tglDari || $val['tanggal'] > $tglSampai)) {
                    continue;
                }
                if ($divisisch != '' && substr($val['kodeorg'], 0, strlen($divisisch)) != $divisisch) {
                    continue;
                }
                if ($mandorsch != '' && (int)$val['nikmandor'] != (int)$mandorsch) {
                    continue;
                }
                $kandidat[] = $val;
                if ($tglMin == '' || $val['tanggal'] < $tglMin) {
                    $tglMin = $val['tanggal'];
                }
                if ($tglMax == '' || $val['tanggal'] > $tglMax) {
                    $tglMax = $val['tanggal'];
                }
            }

            if (count($kandidat) > 0) {
                // Satu query untuk semua data yang sudah ada di ERP (bukan satu query per baris)
                $sudahAda = array();
                $nikErp = array();
                $sCek = "SELECT DISTINCT kodeorg, tanggal, nikmandor" . ($cekPemanen ? ", nik" : "") . " FROM $dbname.kebun_rekapmutuhancakpanen_vw
                WHERE tanggal BETWEEN '" . $tglMin . "' AND '" . $tglMax . "'";
                foreach (fetchData($sCek) as $rc) {
                    $kg = strtoupper($rc['kodeorg']) . '|' . $rc['tanggal'] . '|' . (int)$rc['nikmandor'];
                    $sudahAda[$kg] = 1;
                    if ($cekPemanen) {
                        $nikErp[$kg][(int)$rc['nik']] = 1;
                    }
                }

                // Bandingkan pemanen di mobile dengan yang sudah ada di ERP, per tanggal (API detail dipanggil per tanggal)
                $pemanenKurang = array();
                if ($cekPemanen) {
                    $urlDetail = str_replace('hancakheaders/send', 'hancakdetails/send', $url);
                    $tglCek = array();
                    foreach ($kandidat as $val) {
                        if (isset($sudahAda[strtoupper($val['kodeorg']) . '|' . $val['tanggal'] . '|' . (int)$val['nikmandor']])) {
                            $tglCek[$val['tanggal']] = 1;
                        }
                    }
                    foreach (array_keys($tglCek) as $tgCek) {
                        $dpDetail = array('tanggal' => $tgCek, 'kodeorg' => getOrgDetail(28));
                        if ($mandorsch != '') {
                            $dpDetail['nikmandor'] = $mandorsch;
                        }
                        $resDetail = $getApi->post($urlDetail, $dpDetail);
                        $rowsDetail = $resDetail->response['result']['data'];
                        if (is_array($rowsDetail)) {
                            foreach ($rowsDetail as $dt) {
                                $kg = strtoupper($dt['kodeorg']) . '|' . $dt['tanggal'] . '|' . (int)$dt['nikmandor'];
                                if (isset($sudahAda[$kg]) && !isset($nikErp[$kg][(int)$dt['nik']])) {
                                    $pemanenKurang[$kg][(int)$dt['nik']] = 1;
                                }
                            }
                        }
                    }
                }

                foreach ($kandidat as $val) {
                    $kg = strtoupper($val['kodeorg']) . '|' . $val['tanggal'] . '|' . (int)$val['nikmandor'];
                    $ket = "Baru";
                    $warna = "#4cdf26";
                    if (isset($sudahAda[$kg])) {
                        if (!isset($pemanenKurang[$kg])) {
                            continue;
                        }
                        $ket = "Pemanen kurang " . count($pemanenKurang[$kg]);
                        $warna = "#ffb347";
                    }
                    $div = substr($val['kodeorg'], 0, 6);
                    $nok++;
                    $tab .= "<tr class=rowcontent style='background-color:" . $warna . ";'>";
                    $tab .= "<td align=center>" . $nok . "</td>";
                    $tab .= "<td align=center>" . $val['tanggal'] . "</td>";
                    $tab .= "<td>" . $div . " - " . (isset($nmorg[$div]) ? $nmorg[$div] : getNamaOrg($div)) . "</td>";
                    $tab .= "<td align=center>" . (isset($nmkar[$val['nikmandor']]) ? $nmkar[$val['nikmandor']] : getNamaKaryawan($val['nikmandor'])) . "</td>";
                    $tab .= "<td align=center>" . $nmindk[$val['kodeorg']] . "</td>";
                    $tab .= "<td align=center>" . $ket . "</td>";
                    $tab .= "<td colspan=1 align=center>
                    <img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30' title='Preview Data' onclick=\"previewData('" . $val['kodeorg'] . "','" . $val['tanggal'] . "','" . $val['nikmandor'] . "')\">
                    </td>";
                    $tab .= "</tr>";
                }
            }
        }

        $footd = "";

        echo $tab . "####" . $footd;
        break;

    case 'html':
        $tTotJjg = $tluaspanen = $tJjgBesar = $tJjgKecil = 0;
        $iddendapnn = $dendapanen = $kodedendaid = array();
        $totDenda = [];
        // Get Kode Denda Panen
        $sDenda = "SELECT id, kodedenda, deskripsi, status FROM " . $dbname . ".kebun_5kodedendapanen order by id asc";
        $rDenda = fetchData($sDenda);
        // Get Jumlah Kode Denda Panen
        $countDenda = count($rDenda);
        foreach ($rDenda as $key => $val) {
            $iddendapnn[$val['id']] = $val['id'];
            $dendapanen[$val['id']] = $val['kodedenda'];
            $kodedendaid[$val['kodedenda']] = $val['id'];
        }

        // GET URI FOR PRODUCTION
        $expri = explode("/", $_SERVER['REQUEST_URI']);

        $svr = parse_url($_SERVER['HTTP_REFERER']);

        $pat = array();
        $pat = explode('/', $svr['path']);
        $arr = array_filter($pat, function ($value) {
            return !is_null($value) && $value !== '';
        });

        $urlocal = $_SERVER['HTTP_ORIGIN'] . '/' . implode("/", $data);

        $options = array(
            'client_id' => 'USERSYSTEM',
            'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
            'username' => $_SESSION['standard']['username']
        );
        /** GET API KEY */
        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
        if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
            if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN'] . "/" . $expri[1] . "/mobile/index.php/api/access_token/api_key";
            } else {
                // Jika tidak, maka tidak munculkan uri array[1]
                $url = $_SERVER['HTTP_ORIGIN'] . "/mobile/index.php/api/access_token/api_key";
            }
        } else {
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal . "mobile/index.php/api/access_token/api_key";
        }

        $getApi->init($url, $options);
        // Jika Server Bukan local / localhost maka munculkan $_SERVER['ORIGIN']
        if ($_SERVER['SERVER_ADDR'] == $_SERVER['SERVER_NAME']) {
            // Jika URI yang array [1] panjang string <= 7, Maka munculkan
            if (strlen($expri[1]) <= 7) {
                $url = $_SERVER['HTTP_ORIGIN'] . '/' . $expri[1] . '/mobile/index.php/api/module/mharvest/hancakdetails/send';
            } else {
                $url = $_SERVER['HTTP_ORIGIN'] . '/mobile/index.php/api/module/mharvest/hancakdetails/send';
            }
        } else {
            // Jika Server local / localhost maka munculkan URL localhost
            $url = $urlocal . 'mobile/index.php/api/module/mharvest/hancakdetails/send';
        }

        $dataParam = array(
            'tanggal'   =>   $tgl2 != "" ? $tgl2 : "",
            'nikmandor' =>   $nikmandor != "" ? $nikmandor : "",
            'blok'      => $kodeorg != "" ? $kodeorg : "",
            'kodeorg'   => getOrgDetail(28),
        );

        $data = $getApi->post($url, $dataParam);

        // echo "<pre>";
        // print_r($data->response['result']);
        // echo "</pre>";

        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['pemanen'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['jjg'] . "</td>
            <td align=center colspan='" . ($countDenda * 2) . "'>" . $_SESSION['lang']['denda'] . "</td>
        </tr>";
        $tab .= "<tr>";
        $tab .= "<th align=center>Basis Besar</th>";
        $tab .= "<th align=center>Basis Kecil</th>";
        foreach ($rDenda as $dnd) {
            $tab .= "<td align=center title='" . $dnd['deskripsi'] . "'>" . $dnd['kodedenda'] . "</td>";
            $tab .= "<td align=center title='Foto " . $dnd['deskripsi'] . "'>" . $_SESSION['lang']['photo'] . "</td>";
        }
        $tab .= "</tr>";
        $tab .= "</thead>";
        $no = 0;
        foreach ($data->response['result']['data'] as $key => $bar) {
            $no += 1;
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center>" . $no . "</td>";
            $tab .= "<td align=left>" . getNamaKaryawan($bar['nik']) . "</td>";
            $tab .= "<td align=center>" . $nmindk[$bar['kodeorg']] . "</td>";
            $tab .= "<td align=right>" . $bar['jjgbuahbesar'] . "</td>";
            $tab .= "<td align=right>" . $bar['jjgbuahkecil'] . "</td>";
            foreach ($bar['penalti'] as $key2 => $pnlt) {
                $keydenda[$key2] = $key2;
                $nilaipenalty[$key2] = $pnlt;
            }
            foreach ($bar['photo'] as $key3 => $foto) {
                $photodenda[$key3] = $foto;
            }
            foreach ($rDenda as $dnd) {
                $tab .= "<td align=right>" . $nilaipenalty[$keydenda[$dnd['kodedenda']]] . "</td>";
                $tab .= "<td align=right>";
                if (empty($photodenda[$keydenda[$dnd['kodedenda']]])) {
                    $tab .= "<img src='images/noimages.png' width=50px height=50px>";
                } else {
                    $tab .= "<a href='" . $photodenda[$keydenda[$dnd['kodedenda']]] . "' class='popup-img'>";
                    $tab .= "<img onclick=\"popupimage()\" src='" . $photodenda[$keydenda[$dnd['kodedenda']]] . "' 
                            alt='Foto " . $dnd['deskripsi'] . "' width=50px height=50px>";
                    $tab .= "</a>";
                }
                $tab .= "</td>";
                $totDenda[$dnd['id']] += $nilaipenalty[$keydenda[$dnd['kodedenda']]];
            }
            @$tluaspanen += $bar['luaspanen'];
            @$tJjgBesar += $bar['jjgbuahbesar'];
            @$tJjgKecil += $bar['jjgbuahkecil'];
            $tab .= "</tr>";
        }
        $tab .= "</table>";

        $tab .= "<br/>";
        $tab .= "<button onclick=\"postingMobileERP('" . $kodeorg . "','" . $tgl2 . "','" . $nikmandor . "')\" class=mybutton>Download Seluruhnya</button>";
        echo $tab;
        break;
}
