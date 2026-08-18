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
				$url = $_SERVER['HTTP_ORIGIN'] . '/' . $expri[1] . '/mobile/index.php/api/module/mluas/erpHeader/send';
			} else {
				$url = $_SERVER['HTTP_ORIGIN'] . '/mobile/index.php/api/module/mluas/erpHeader/send';
			}
		} else {
			// Jika Server local / localhost maka munculkan URL localhost
			$url = $urlocal . 'mobile/index.php/api/module/mluas/erpHeader/send';
		}

		$dataParam = array(
			// 'tanggal' =>   $tglsch != '--' ? $tglsch : "",
			'periode' => $periodesch != "" ? $periodesch : "",
			'kodeorg' => getOrgDetail(28),
		);

		$nok = 0;
		$data = $getApi->post($url, $dataParam);
		// echo "<pre>";
		// print_r($data->response['result']['data']);
		// echo "</pre>";
		if (count($data->response['result']['data']) > 0) {
			foreach ($data->response['result']['data'] as $key => $val) {
				$sCek = "SELECT * FROM $dbname.kebun_rekaphancakpanen_vw 
                WHERE kodeorg='" . $val['blok'] . "' AND tanggal='" . $val['tanggal'] . "' AND nikmandor='" . $val['mandor'] . "'";
				$rCek = fetchData($sCek);
				$countData = count($rCek);
				if ($countData == 0) {
					$nok++;
					$tab .= "<tr class=rowcontent style='background-color:#4cdf26;'>";
					$tab .= "<td align=center>" . $nok . "</td>";
					$tab .= "<td align=center>" . $val['notransaksi'] . "</td>";
					$tab .= "<td align=center>" . $val['tanggal'] . "</td>";
					$tab .= "<td>" . substr($val['blok'], 0, 6) . " - " . getNamaOrg(substr($val['blok'], 0, 6)) . "</td>";
					$tab .= "<td align=center>" . getNamaKaryawan($val['mandor']) . "</td>";
					$tab .= "<td align=center>" . $nmindk[$val['blok']] . "</td>";
					$tab .= "<td colspan=1 align=center>
                    <img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30' title='Preview Data' onclick=\"previewData('" . $val['blok'] . "','" . $val['tanggal'] . "','" . $val['mandor'] . "')\">
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
				$url = $_SERVER['HTTP_ORIGIN'] . '/' . $expri[1] . '/mobile/index.php/api/module/mluas/erpDetail/send';
			} else {
				$url = $_SERVER['HTTP_ORIGIN'] . '/mobile/index.php/api/module/mluas/erpDetail/send';
			}
		} else {
			// Jika Server local / localhost maka munculkan URL localhost
			$url = $urlocal . 'mobile/index.php/api/module/mluas/erpDetail/send';
		}

		$dataParam = array(
			'tanggal'   =>   $tgl2 != "" ? $tgl2 : "",
			'periode'   =>   substr($tgl2, 0, 7) != "" ? substr($tgl2, 0, 7) : "",
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
            <td align=center rowspan='2'>" . $_SESSION['lang']['divisikary'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['hasilkerja2'] . " <br> Panen (HA)</td>
        </tr>";
		$tab .= "</thead>";
		$no = 0;
		foreach ($data->response['result']['data'] as $key => $bar) {
			$no += 1;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=left>" . getKary($bar['pemanen'], 'nik') . " - " . getNamaKaryawan($bar['pemanen']) . "</td>";
			$tab .= "<td align=center>" . getNamaOrg(getKary($bar['pemanen'], 'subbagian')) . "</td>";
			$tab .= "<td align=center>" . $nmindk[$bar['blok']] . "</td>";
			$tab .= "<td align=right>" . $bar['luasaktual'] . "</td>";
			@$tluaspanen += $bar['luasaktual'];
			$tab .= "</tr>";
		}
		$tab .= "<tr class=rowcontent style='font-weight:bold;'>";
		$tab .= "<td align=center colspan=4>" . $_SESSION['lang']['total'] . "</td>";
		$tab .= "<td align=right>" . $tluaspanen . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<br/>";
		$tab .= "<button onclick=\"postingMobileERP('" . $kodeorg . "','" . $tgl2 . "','" . $nikmandor . "')\" class=mybutton>Download Seluruhnya</button>";
		echo $tab;
		break;
}
