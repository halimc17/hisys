<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('dompdf/autoload.inc.php');

use Dompdf\Dompdf;

$method            = checkPostGet('method', '');
$pages             = checkPostGet('page', '');
$notransaksicr     = checkPostGet('notransaksicr', '');
$unitcr            = checkPostGet('unitcr', '');
$koderekanancr     = checkPostGet('koderekanancr', '');
$tglcr             = checkPostGet('tglcr', '');
$nopengajuan       = checkPostGet('nopengajuan', '');

$nourut            = checkPostGet('nourut', '');
$notransaksi       = checkPostGet('notransaksi', '');
$divisi            = checkPostGet('divisi', '');
$kebun             = checkPostGet('kebun', '');
$kodeorg           = checkPostGet('kodeorg', '');
$nopengajuanx      = checkPostGet('nopengajuan', '');
$tanggalx          = checkPostGet('tanggal', '');
$terminx           = checkPostGet('termin', '');
$tipex             = checkPostGet('tipe', '');
$pengajuanspk      = checkPostGet('pengajuanspk', '');
$kodeblokdt        = checkPostGet('kodeblok', '');
$kodebloktextdt    = checkPostGet('kodebloktext', '');
$kodekegdt         = checkPostGet('kodekegiatan', '');
$kodekegtextdt     = checkPostGet('kodekegtext', '');
$matauangdt        = checkPostGet('matauang', '');
$satuandt          = checkPostGet('satuan', '');
$hkdt              = checkPostGet('hk', '');
$hasilkerjajumlahdt = checkPostGet('hasilkerjajumlah', '');
$dttermin          = checkPostGet('dttermin', '');
$nobatermin        = checkPostGet('nobatermin', '');
$kodeblokdt2       = checkPostGet('kodeblokdt2', '');
$nobapp            = checkPostGet('nobapp', '');

$tgldt2            = checkPostGet('tgldt2', '');
$keterangandt2     = checkPostGet('keterangandt2', '');
$hkdt2             = checkPostGet('hkdt2', '0');
$hasilhkdt2        = checkPostGet('hasilhkdt2', '0');
$jumlahrpdt2       = checkPostGet('jumlahrpdt2', '0');
$tipeview          = checkPostGet('tipeview', '0');
$nobaspk           = checkPostGet('baspk', '');
$param             = $_POST;
$jumlahrpdt        = checkPostGet('jumlahrp', '');
$namafile          = checkPostGet('namafile', '');
$path              = "fileupload/lgl_pengajuanspk/";
$kriteriaefil      = checkPostGet('kriteriaefil', '');
$emodul            = 'BAP';

switch ($method) {
	case 'showEdit':
		$where = "notransaksi='" . $notransaksi . "' and kodeorg='" . $kodeorg . "'";
		$query = selectQuery($dbname, 'log_spkht', "*", $where);
		$tmpData = fetchData($query);
		$data = $tmpData[0];
		$data['tanggal'] = tanggalnormal($data['tanggal']);
		echo formHeader('edit', $data);
		echo "<div id='detailField' style='clear:both'></div>";
		break;

	case 'showDetail':
		$param = $_POST;
		# Options
		#khusus jika project
		$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $notransaksi . "'";
		$res = fetchdata($str);
		$jenis = $res[0]['jenis'];

		if (empty($param['divisi']) or $param['divisi'] == 'PROJECT') {
			$str = "select * from " . $dbname . ".log_spkht where notransaksi='" . $param['notransaksi'] . "'";
			$res = fetchData($str);
			$nopengajuan = $res[0]['nopengajuan'];

			$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $nopengajuan . "'";
			$res = fetchData($str);
			$pengajuandivisi = $res[0]['divisi'];

			$optCapex = makeOption($dbname, 'project', 'kode,kodecapex', "kode='" . $pengajuandivisi . "'");
			// $optProject = makeOption($dbname,'project','kode,nama',"kode='".$row['kodeblok']."'");
			$kodecapex = $optCapex[$pengajuandivisi];

			if ($kodecapex == '') {
				$optAct = makeOption($dbname, 'project_dt', 'kegiatan,namakegiatan', "kodeproject='" . $pengajuandivisi . "'");
			} else {
				$optAct = makeOption($dbname, 'spl_capexbangunandt', 'kegiatan,namakegiatan', "kodeproject='" . $kodecapex . "'");
			}

			$optBlok = makeOption($dbname, 'project', 'kode,nama', "kodeorg='" . $param['kebun'] . "' and posting=0");
			// $optPrj = array();
			// foreach($optBlok as $key=>$row) {
			// $optPrj[] = $key;
			// }
			// $str="select kegiatan,namakegiatan from ".$dbname.".project_dt
			// where kodeproject in ('".implode("','",$optPrj)."')";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_OBJ);
			// while($bar=$res->fetch()){
			// $optAct[$bar->kegiatan]=$bar->namakegiatan;
			// }               
		} else if ($param['divisi'] == 'S') {
			$optRegOrg = makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional', "kodeunit='" . substr($param['kebun'], 0, 4) . "'");
			$optBlok = makeOption($dbname, 'sdm_perumahanht', 'norumah,keterangan', "kodeorg in (select kodeunit from " . $dbname . ".bgt_regional_assignment where regional = '" . $optRegOrg[substr($param['kebun'], 0, 4)] . "')");
			$optAct = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kelompok = 'SPL'");
		} else if ($param['divisi'] == 'P') {
			//pabrikasi
			$optBlok = makeOption($dbname, 'pabrikasi_5masterht', 'kodepabrikasi,namapabrikasi', '', 2);
			$optAct = makeOption($dbname, 'pabrikasi_5masterdt', 'tahapan,tahapan', '', 2);
		} else if ($jenis == 'SEWA.HM') {
			$str = "select * from " . $dbname . ".organisasi";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$optBlok[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
			}
			$str = "select * from " . $dbname . ".vhc_5master";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$optBlok[$bar['kodevhc']] = $bar['nopol'];
			}
			$optAct = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
		} else {
			$optBlok = makeOption(
				$dbname,
				'organisasi',
				'kodeorganisasi,namaorganisasi',
				"kodeorganisasi like '" . substr($param['divisi'], 0, 4) . "%'"
			);
			$optAct = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
		}


		$whr = "";
		if ($param['divisi'] == 'S') {
			$whr = " ";
		} else {
			$whr = " and kodeblok like '" . substr($param['divisi'], 0, 4) . "%'";
		}
		# Get Data
		$where = "notransaksi='" . $param['notransaksi'] . "' " . $whr . "";
		$cols = "kodeblok,kodekegiatan,hk,hasilkerjajumlah,satuan,jumlahrp";
		$query = selectQuery($dbname, 'log_spkdt', $cols, $where);
		$data = fetchData($query);
		$dataShow = array();
		// echo"<pre>";print_r($query);echo"</pre>";exit;
		foreach ($data as $key => $row) {
			$dataShow[$key]['kodeblok'] = $optBlok[$row['kodeblok']];
			@$dataShow[$key]['kodekegiatan'] = $optAct[$row['kodekegiatan']];
			$dataShow[$key]['hk'] = $row['hk'];
			$dataShow[$key]['hasilkerjajumlah'] = $row['hasilkerjajumlah'];
			$dataShow[$key]['satuan'] = $row['satuan'];
			$dataShow[$key]['jumlahrp'] = $row['jumlahrp'];
		}

		# Grid Header
		$grid = "<style type=text/css>
		.freezetbl {
			position: relative;
			max-height: 350px;
			background-color:#56B5E7;
		}
		.freezetbl thead {
			position: -webkit-sticky;
			position: sticky;
			top: 0;
			z-index: 2;
		}
		</style>";
		$grid .= "<div class=table-scroll style='height:400px'>
			<table class='freezetbl' cellpadding=5 cellspacing=1>
			<thead><tr class='rowheader' style='text-align:center'>
				<td>" . $_SESSION['lang']['subunit'] . "</td>
				<td>" . $_SESSION['lang']['kodekegiatan'] . "</td>
				<td>" . $_SESSION['lang']['hk'] . "</td>
				<td>" . $_SESSION['lang']['hasilkerjajumlah'] . "</td>
				<td>" . $_SESSION['lang']['satuan'] . "</td>
				<td>" . $_SESSION['lang']['jumlahrp'] . "</td>
				<td>" . $_SESSION['lang']['action'] . "</td>
			</tr></thead>";

		# Grid Content
		$grid .= "<tbody>";
		if (empty($data)) {
			$grid .= "<tr class='rowcontent'><td colspan='10'>Data Empty</td></tr>";
		} else {
			foreach ($dataShow as $key => $row) {
				// $grid .= "<tr class='rowcontent' onclick=\"manageDetail(".$key.")\" style='cursor:pointer'>";
				$grid .= "<tr class='rowcontent'>";
				foreach ($row as $head => $cont) {
					$grid .= "<td id='" . $head . "_" . $key . "' ";
					if (isset($data[$key][$head])) {
						$grid .= "value='" . $data[$key][$head] . "' ";
					} else {
						$grid .= "value='' ";
					}
					if ($head == 'kodeblok' or $head == 'kodekegiatan' or $head == 'satuan') {
						$grid .= "align='left'";
					} else {
						$grid .= "align='right'";
					}
					if ($head == 'jumlahrp' or $head == 'hk' or $head == 'hasilkerjajumlah') {
						$grid .= ">" . number_format($cont) . "</td>";
					} elseif ($head == 'satuan') {
						$grid .= ">" . $cont . "</td>";
					} else {
						$grid .= ">" . $data[$key][$head] . "-" . $cont . "</td>";
					}
				}
				$grid .= "<td style='text-align:center'>
					<img class='zImgBtn' src='images/skyblue/zoom.png' onclick=\"previewdt('" . $key . "',event)\">
					</td>
				</tr>";
				// $grid .= "<tr><td colspan='6'><div id='detail_".$key."'></div></td></tr>";
			}
		}
		$grid .= "</tbody>";
		$grid .= "</table></div>";

		#== Display View
		# Draw Tab
		echo "<fieldset><legend><b>Detail</b></legend>";
		echo $grid;
		echo "</fieldset>";
		break;

	case 'previewdt':
		$tab = "";

		@$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $notransaksi . "' and kodeblok='" . $kodeblok . "' and kodekegiatan='" . $kodekeg . "'";
		$res = fetchdata($str);


		$tab = "<table>
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>
				<td>" . $notransaksi . "</td>
				
				<td style='padding-left:20px'>" . $_SESSION['lang']['hk'] . "</td>
				<td>:</td>
				<td>" . $hkdt . "</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['subunit'] . "</td>
				<td>:</td>
				<td>" . $kodebloktextdt . "</td>
				
				<td style='padding-left:20px'>" . $_SESSION['lang']['hasilkerjajumlah'] . "</td>
				<td>:</td>
				<td>" . $hasilkerjajumlahdt . "</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['kegiatan'] . "</td>
				<td>:</td>
				<td>" . $kodekegtextdt . "</td>
				
				<td style='padding-left:20px'>" . $_SESSION['lang']['jumlahrp'] . "</td>
				<td>:</td>
				<td>" . number_format($jumlahrpdt) . "</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['satuan'] . "</td>
				<td>:</td>
				<td>" . $satuandt . "
					<input type='hidden' id='tothk' value='" . $hkdt . "'>
					<input type='hidden' id='tothkjumlah' value='" . $hasilkerjajumlahdt . "'>
					<input type='hidden' id='totjumlahrp' value='" . $jumlahrpdt . "'>
					<input type='hidden' id='matauangdt' value='" . $matauangdt . "'>
				</td>
				
				<td style='padding-left:20px'>" . $_SESSION['lang']['hargasatuan'] . "</td>
				<td>:</td>
				<td>" . number_format(($jumlahrpdt / $hasilkerjajumlahdt)) . "</td>
			</tr>
			<tr>
				<td colspan=3></td>
				
				<td style='padding-left:20px'>" . $_SESSION['lang']['matauang'] . "</td>
				<td>:</td>
				<td>" . $matauangdt . "</td>
			</tr>
		</table>";

		##create termin
		// for($i=1;$i<=10;$i++){
		// if($i==1){
		// $opttermin.="<option value='".$i."' selected>".$i."</option>";				
		// }else{
		// $opttermin.="<option value='".$i."'>".$i."</option>";				
		// }
		// }

		if (strlen($divisi) == 4) {
			$optBlok = "<option value='" . $divisi . "'>" . $divisi . "</option>";
		}
		if (strlen($kodeblokdt) == 6) {
			$optBlok = "<option value='" . $kodeblokdt . "'>" . $kodeblokdt . "</option>";
		}

		if ($_SESSION['empl']['tipelokasitugas'] != 'KEBUN') {
			$str_blok = "SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM " . $dbname . ".organisasi b 
				WHERE b.kodeorganisasi like '" . $kodeblokdt . "%'";
			$res_blok = $owlPDO->query($str_blok) or die(print " Gagal: " . PDOException::getMessage());
			$res_blok->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res_blok->fetch()) {
				if ($kodeblokdt == $bar->kodeorg) {
					$optBlok .= "<option value='" . $bar->kodeorg . "' selected>" . $bar->namaorg . "</option>";
				} else {
					$optBlok .= "<option value='" . $bar->kodeorg . "'>" . $bar->namaorg . "</option>";
				}
			}
		} else {
			$sAlokasi = selectQuery($dbname, 'setup_kegiatan', 'kelompok', "kodekegiatan='" . substr($kodekegtextdt, 0, 9) . "' and status='1'");
			$rAlokasi = fetchData($sAlokasi)[0];

			#== Ambil statusblok berdasarkan kelompok kegiatan traksi
			// if ($_SESSION['standard']['userid'] == '0000000005') {
			// 	echo "<pre>";
			// 	print_r($rAlokasi);
			// 	// print_r($bar);
			// 	exit("Warning");
			// }
			
			if ($rAlokasi['kelompok'] == 'PNN') {
				$statusblok = " and statusblok = 'TM'";
			} else if ($rAlokasi['kelompok'] == 'LC') {
				$statusblok = " and statusblok IN ('LC','TBM')";
			} else if ($rAlokasi['kelompok'] == 'TB') {
				$statusblok = " and statusblok IN ('TB')";
			} else {
				$statusblok = " and statusblok = '" . $rAlokasi['kelompok'] . "'";
			}

			##cek jika kegiatanya termasuk yg perhitungan berbeda
			$strpa = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='KLPLUS'and nilai like '%" . $kodekegdt . "%' ";
			$barpa = fetchData($strpa);
			$jmlkgplus = count($barpa);

			// $str_blok="SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg,b.indukblok FROM ".$dbname.".setup_blok a LEFT JOIN ".$dbname.".organisasi b 
			// 	ON a.kodeorg = b.kodeorganisasi 
			// 	WHERE a.luasareaproduktif >0 and b.kodeorganisasi like '".substr($kodeblokdt,0,6)."%' 
			// 	and length(b.kodeorganisasi)>6 and status='A' ".$statusblok." ";
			#== New
			$subunit = explode('-', $kodebloktextdt);
			if (strlen($subunit[0]) > 6) {
				$indukblok = substr($subunit[0], 0, 9);
			} else {
				$indukblok = $subunit[0];
			}

			## Tambah kondisi jika TB gak perlu cek luas produktif > 0 , karena HA nya belum ada sesuai kondisi setup blok yang ada
			if($rAlokasi['kelompok'] == 'TB'){
				$str_blok = selectQuery($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi,indukblok,namaindukblok', "indukblok like '" . $indukblok . "%' and (tipe='BLOK' OR tipe='BIBITAN') and kodeorganisasi in (select distinct kodeorg from $dbname.setup_blok where indukblok like '" . $indukblok . "%' $statusblok and status='A') group by indukblok", 'tipe desc,kodeorganisasi');
			}else{
				$str_blok = selectQuery($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi,indukblok,namaindukblok', "indukblok like '" . $indukblok . "%' and (tipe='BLOK' OR tipe='BIBITAN') and kodeorganisasi in (select distinct kodeorg from $dbname.setup_blok where indukblok like '" . $indukblok . "%' and luasareaproduktif>0 $statusblok and status='A') group by indukblok", 'tipe desc,kodeorganisasi');
			}

			$res_blok = $owlPDO->query($str_blok) or die(print " Gagal: " . PDOException::getMessage());
			$res_blok->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res_blok->fetch()) {
				$nminduk = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok', "indukblok='" . $bar->indukblok . "'");
				if ($jmlkgplus == '0') {
					if (substr($kodeblokdt, 0, 9) == $bar->indukblok) {
						$sel = 'selected';
					}
					$optBlok .= "<option value='" . $bar->indukblok . "' " . $sel . ">" . $bar->indukblok . " - " . $nminduk[$bar->indukblok] . "</option>";
					// else { #= Tambahkan kondisi else, karena ada case yang tidak muncul
					// 	$optBlok.="<option value='".$bar->indukblok."'>".$nminduk[$bar->indukblok]."</option>";					
					// }
				} else {
					if (substr($kodeblokdt, 0, 9) == $bar->indukblok) {
						$optBlok .= "<option value='" . $bar->indukblok . "' selected>" . $bar->indukblok . " - " . $nminduk[$bar->indukblok] . "</option>";
					} else {
						$optBlok .= "<option value='" . $bar->indukblok . "'>" . $bar->indukblok . " - " . $nminduk[$bar->indukblok] . "</option>";
					}
				}
			}
		}

		#khusus jika project
		if (empty($divisi)) {
			$optBlokx = makeOption($dbname, 'project', 'kode,nama', "kodeorg='" . $kebun . "' and kode='" . $kodeblokdt . "' and posting=0");
			foreach ($optBlokx as $key => $val) {
				$optBlok = "<option value='" . $key . "'>" . $val . "</option>";
			}
		}

		#khusus jika Perumahan
		if (@$param['divisi'] == 'S') {
			$optRegOrg = makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional', "kodeunit='" . substr($kebun, 0, 4) . "'");
			$optBlokx = makeOption($dbname, 'sdm_perumahanht', 'norumah,keterangan', "kodeorg in (select kodeunit from " . $dbname . ".bgt_regional_assignment where regional = '" . $optRegOrg[substr($kebun, 0, 4)] . "')");
			foreach ($optBlokx as $key => $val) {
				$optBlok = "<option value='" . $key . "'>" . $val . "</option>";
			}
		}

		if (@$param['divisi'] == 'P') {
			//pabrikasi
			$optBlokx = makeOption($dbname, 'pabrikasi_5masterht', 'kodepabrikasi,namapabrikasi', '', 2);
			foreach ($optBlokx as $key => $val) {
				$optBlok = "<option value='" . $key . "'>" . $val . "</option>";
			}
		}
		$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $notransaksi . "'";
		$res = fetchdata($str);
		$jenis = $res[0]['jenis'];
		if ($jenis == 'SEWA.HM') {
			$str = "select * from " . $dbname . ".organisasi";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$nmblok[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
			}
			$str = "select * from " . $dbname . ".vhc_5master";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$nmblok[$bar['kodevhc']] = $bar['nopol'];
			}

			$optBlok = "<option value='" . $param['kodeblok'] . "'>" . $param['kodeblok'] . " - " . $nmblok[$param['kodeblok']] . "</option>";
		}
		// echo "<pre>";
		// print_r($_POST);
		// exit('Error');

		#buat nomor BAPP
		$str = "select max(substr(keterangan,1,3)) as bapp from " . $dbname . ".log_baspk where notransaksi = '" . $notransaksi . "' and statuspengajuan>0 limit 1"; #exit("error");
		$res = fetchdata($str);
		$noba = intval($res[0]['bapp']);
		if ($noba == 0) {
			$nobap = "001";
			$notermin = 1;
		} else {
			$nobap = addZero($noba + 1, 3);
			$notermin = intval($noba) + 1;
		}
		$nobapp = $nobap . "/" . $notransaksi;

		$tab .= "<fieldset style=float:left>
			<legend>Form Realisasi</legend>
			<table>
				<tr>
					<td>" . $_SESSION['lang']['termin'] . "</td>
					<td>:</td>
					<td>
						<input id='dttermin' onkeyup=getnobapp('" . $notransaksi . "'); class='myinputtextnumber' onkeypress='return isNumber(this,event)' type='text' style='width:70px' value=" . $notermin . ">
					</td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['subunit'] . "</td>
					<td>:</td>
					<td>
						<select style=min-width:75px id='kodeblokdt2'>" . @$optBlok . "</select>
					</td>
				</tr>
				<tr>
					<td>No. BAPP</td>
					<td>:</td>
					<td colspan=5>
						<input id='nobatermin' class='myinputtext' type='text' style='width:210px' value=" . $nobapp . ">
						<input type='hidden' id='prosestermin' value='inserttermin'>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td colspan=5>
						<button class='mybutton' onclick=\"simpantermin('" . $notransaksi . "','" . $kodeblokdt . "','" . $kodekegdt . "')\">" . $_SESSION['lang']['save'] . "</button>
						<button class='mybutton' onclick=\"bataltermin()\">" . $_SESSION['lang']['cancel'] . "</button>
					</td>
				</tr>
			</table>
		</fieldset>";

		$tab .= "<div style=clear:both></div><fieldset style=float:left>
			<legend>List Realisasi</legend>
			<table class='sortable' cellpadding=5 cellspacing=1>
				<thead>
				<tr class='rowheader' style='text-align:center'>
					<th>" . $_SESSION['lang']['termin'] . "</th>
					<th>No. BAPP</th>
					<th>" . $_SESSION['lang']['subunit'] . "</th>
					<th>" . $_SESSION['lang']['tanggal'] . "</th>
					<th>" . $_SESSION['lang']['keterangan'] . "</th>
					<th>" . $_SESSION['lang']['hk'] . "</th>
					<th>" . $_SESSION['lang']['hasilkerjajumlah'] . "</th>
					<th>" . $_SESSION['lang']['jumlahrp'] . "</th>
					<th>" . $_SESSION['lang']['action'] . "</th>
				</tr>
				</thead>
				<tbody id='containerdt'>
					<script>loaddatadt('" . $notransaksi . "','" . $kodeblokdt . "','" . $kodekegdt . "')</script>
				</tbody>
			</table>
		</fieldset>";

		echo $tab;
		break;

	case 'loaddatadt':
		$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $notransaksi . "'";
		$res = fetchdata($str);
		$jenis = $res[0]['jenis'];

		if (empty($divisi) or $divisi == 'PROJECT') {
			$optBlok = makeOption($dbname, 'project', 'kode,nama', "kodeorg='" . $kebun . "' and posting=0");
		} else if ($divisi == 'S') {
			$optRegOrg = makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional', "kodeunit='" . substr($kebun, 0, 4) . "'");
			$optBlok = makeOption($dbname, 'sdm_perumahanht', 'norumah,keterangan', "kodeorg in (select kodeunit from " . $dbname . ".bgt_regional_assignment where regional = '" . $optRegOrg[substr($kebun, 0, 4)] . "')");
			$optAct = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kelompok = 'SPL'");
		} else if ($divisi == 'P') {
			//pabrikasi
			$optBlok = makeOption($dbname, 'pabrikasi_5masterht', 'kodepabrikasi,namapabrikasi', '', 2);
		} else if ($jenis == 'SEWA.HM') {
			$str = "select * from " . $dbname . ".organisasi";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$optBlok[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
			}
			$str = "select * from " . $dbname . ".vhc_5master";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$optBlok[$bar['kodevhc']] = $bar['nopol'];
			}
		} else {
			$optBlok = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi like '" . substr($divisi, 0, 4) . "%'");
		}

		$tab = "";

		$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $notransaksi . "' and kodekegiatan='" . $kodekegdt . "' and kodeblok like '" . substr($kodeblokdt, 0, 6) . "%'  order by termin asc";
		$res = fetchdata($str);
		if (count($res) <= 0) {
			$tab .= "<tr class='rowcontent'><td colspan=9 style='text-align:center'>" . $_SESSION['lang']['datanotfound'] . "</td></tr>";
		} else {
			$no = 0;
			foreach ($res as $val) {
				$no++;
				$tab .= "<tr class='rowcontent' style='font-weight:bold'>
					<td style='text-align:center' id='tdtermin_" . $no . "'>" . $val['termin'] . "</td>
					<td id='tdbapp_" . $no . "'>" . $val['keterangan'] . "</td>
					<td>" . $optBlok[$val['kodeblok']] . "</td>
					<td style='min-width:80px;text-align:center'>" . ($val['tanggal'] == '0000-00-00' ? '' : tanggalnormal($val['tanggal'])) . "</td>
					<td style='text-align:right'></td>
					<td style='text-align:right'>" . $val['hkrealisasi'] . "</td>
					<td style='text-align:right'>" . $val['hasilkerjarealisasi'] . "</td>
					<td style='text-align:right'>" . number_format($val['jumlahrealisasi']) . "</td>";
				if ($val['statuspengajuan'] == '0' || $val['statuspengajuan'] == '3') {
					$tab .= "<td style='text-align:center' nowrap>
							<img class='zImgBtn' src='images/skyblue/plus.png' onclick=\"adddt2('" . $notransaksi . "','" . $val['kodeblok'] . "','" . $kodekegdt . "','" . $val['termin'] . "','" . $val['keterangan'] . "',event)\">&nbsp;&nbsp;
							<img class='zImgBtn' src='images/skyblue/delete.png' onclick=\"deletedt2('" . $notransaksi . "','" . $val['kodeblok'] . "','" . $kodekegdt . "','" . $val['termin'] . "','" . $val['keterangan'] . "')\">
						</td>";
				} else if ($val['statuspengajuan'] == '1') {
					if ($val['statusjurnal'] == '1') {
						$tab .= "<td style='text-align:center'>
								<img class='zImgBtn' src='images/skyblue/posted.png'>
							</td>";
					} else {
						$tab .= "<td style='text-align:center'>
								<img class='zImgBtn' src='images/skyblue/posting.png' onclick=\"postingData('" . $kodeblokdt . "','" . $val['kodeblok'] . "','" . $kodekegdt . "','" . $val['hasilkerjarealisasi'] . "','" . $val['hkrealisasi'] . "','" . $val['jumlahrealisasi'] . "','" . tanggalnormal($val['tanggal']) . "')\">
							</td>";
					}
				} else {
					$tab .= "<td></td>";
				}
				$tab .= "</tr>";

				$strx = "select * from " . $dbname . ".log_baspkdt where notransaksi='" . $notransaksi . "' and kodeblok='" . $val['kodeblok'] . "' and kodekegiatan='" . $kodekegdt . "' and termin='" . $val['termin'] . "' and keterangan='" . $val['keterangan'] . "'";
				$resx = fetchdata($strx);
				if (count($resx) > 0) {
					foreach ($resx as $valx) {
						$tab .= "<tr class='rowcontent'>
							<td style='text-align:center'></td>
							<td></td>
							<td>" . $optBlok[$valx['kodeblok']] . "</td>
							<td style='min-width:80px;text-align:center;background-color:#50edd2'>" . tanggalnormal($valx['tanggal']) . "</td>
							<td style='text-align:left;background-color:#50edd2'>" . $valx['keterangan2'] . "</td>
							<td style='text-align:right;background-color:#50edd2'>" . $valx['hkrealisasi'] . "</td>
							<td style='text-align:right;background-color:#50edd2'>" . $valx['hasilkerjarealisasi'] . "</td>
							<td style='text-align:right;background-color:#50edd2'>" . number_format($valx['jumlahrealisasi']) . "</td>";

						if ($val['statuspengajuan'] == '0') {
							$tab .= "<td style='text-align:center;background-color:#50edd2'>
								<img class='zImgBtn' src='images/skyblue/delete.png' onclick=\"deletedt('" . $valx['nourut'] . "','" . $notransaksi . "','" . $kodeblokdt . "','" . $kodekegdt . "')\">
							</td>";
						} else {
							$tab .= "<td style='text-align:center;background-color:#50edd2'></td>";
						}
						$tab .= "</tr>";
					}
				}
			}
		}

		echo $tab;
		break;

	case 'deletedt':
		$str = "select * from " . $dbname . ".log_baspkdt where nourut='" . $nourut . "'";
		$res = fetchdata($str);
		$notransaksi = $res[0]['notransaksi'];
		$kodeblok = $res[0]['kodeblok'];
		$kodekegiatan = $res[0]['kodekegiatan'];
		$hasilkerjarealisasi2  = $res[0]['hasilkerjarealisasi'];
		$hkrealisasi2  = $res[0]['hkrealisasi'];
		$jumlahrealisasi2  = $res[0]['jumlahrealisasi'];
		$termin  = $res[0]['termin'];
		$keterangan  = $res[0]['keterangan'];

		$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $notransaksi . "' and kodeblok='" . $kodeblok . "' and kodekegiatan='" . $kodekegiatan . "' and termin='" . $termin . "' and keterangan='" . $keterangan . "'";
		$res = fetchdata($str);
		$hasilkerjarealisasi1 = $res[0]['hasilkerjarealisasi'];
		$hkrealisasi1 = $res[0]['hkrealisasi'];
		$jumlahrealisasi1 = $res[0]['jumlahrealisasi'];

		$hasilkerjarealisasi3 = $hasilkerjarealisasi1 - $hasilkerjarealisasi2;
		$hkrealisasi3 = $hkrealisasi1 - $hkrealisasi2;
		$jumlahrealisasi3 = $jumlahrealisasi1 - $jumlahrealisasi2;


		$str = "update " . $dbname . ".log_baspk set hasilkerjarealisasi='" . $hasilkerjarealisasi3 . "',hkrealisasi='" . $hkrealisasi3 . "', jumlahrealisasi='" . $jumlahrealisasi3 . "' where notransaksi='" . $notransaksi . "' and kodeblok='" . $kodeblok . "' and kodekegiatan='" . $kodekegiatan . "' and termin='" . $termin . "' and keterangan='" . $keterangan . "'";
		echo $str;
		try {
			$owlPDO->exec($str);
			$str = "delete from " . $dbname . ".log_baspkdt where nourut='" . $nourut . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'adddt2':
		$tab = "";

		if (strlen($divisi) == 4) {
			$optBlok[$divisi] = $divisi;
		}

		if ($_SESSION['empl']['tipelokasitugas'] != 'KEBUN') {
			$str_blok = "SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM " . $dbname . ".organisasi b 
				WHERE b.kodeorganisasi like '" . $kodeblokdt . "%'";
			$res_blok = $owlPDO->query($str_blok) or die(print " Gagal: " . PDOException::getMessage());
			$res_blok->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res_blok->fetch()) {
				$optBlok[$bar->kodeorg] = $bar->namaorg;
			}
		} else {
			$str_blok = "SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM " . $dbname . ".setup_blok a LEFT JOIN " . $dbname . ".organisasi b 
				ON a.kodeorg = b.kodeorganisasi 
				WHERE a.luasareaproduktif >0 and b.kodeorganisasi like '" . substr($divisi, 0, 6) . "%' 
				and length(b.kodeorganisasi)>6";
			$res_blok = $owlPDO->query($str_blok) or die(print " Gagal: " . PDOException::getMessage());
			$res_blok->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res_blok->fetch()) {
				$optBlok[$bar->kodeorg] = $bar->namaorg;
			}
		}

		#khusus jika project
		if (empty($divisi)) {
			$optBlokx = makeOption($dbname, 'project', 'kode,nama', "kodeorg='" . $kebun . "' and kode='" . $kodeblokdt . "' and posting=0");
			foreach ($optBlokx as $key => $val) {
				$optBlok[$key] = $val;
			}
		}

		#khusus jika Perumahan
		if (@$param['divisi'] == 'S') {
			$optRegOrg = makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional', "kodeunit='" . substr($kebun, 0, 4) . "'");
			$optBlokx = makeOption($dbname, 'sdm_perumahanht', 'norumah,keterangan', "kodeorg in (select kodeunit from " . $dbname . ".bgt_regional_assignment where regional = '" . $optRegOrg[substr($kebun, 0, 4)] . "')");
			foreach ($optBlokx as $key => $val) {
				$optBlok[$key] = $val;
			}
		}

		if (@$param['divisi'] == 'P') {
			//pabrikasi
			$optBlokx = makeOption($dbname, 'pabrikasi_5masterht', 'kodepabrikasi,namapabrikasi', '', 2);
			foreach ($optBlokx as $key => $val) {
				$optBlok = "<option value='" . $key . "'>" . $val . "</option>";
			}
		}


		$tab .= "<table>
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>
				<td>" . $notransaksi . "</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['termin'] . "</td>
				<td>:</td>
				<td>" . $dttermin . "</td>
			</tr>
			<tr>
				<td>No. BAPP</td>
				<td>:</td>
				<td>" . $nobatermin . "</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['matauang'] . "</td>
				<td>:</td>
				<td>" . $matauangdt . "</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['subunit'] . "</td>
				<td>:</td>
				<td>" . $optBlok[$kodeblokdt] . "</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['tanggal'] . "</td>
				<td>:</td>
				<td>
					<input id='tgldt2' onchange=getsewahm('" . $notransaksi . "','" . $kodeblokdt . "','" . $kodekegdt . "','" . $dttermin . "','" . $nobatermin . "'); class='myinputtext' type='text' onmousemove='setCalendar(this.id)' readonly='readonly' style='width:80px'>
				</td>
			</tr>
			<tr>
				<td style='vertical-align:top'>" . $_SESSION['lang']['keterangan'] . "</td>
				<td style='vertical-align:top'>:</td>
				<td>
					<textarea id='keterangandt2'></textarea>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['hk'] . "</td>
				<td>:</td>
				<td>
					<input id='hkdt2' class='myinputtextnumber' onkeypress='return angka_doang(event)' type='text' placeholder=0 style='width:80px'>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['hasilkerjajumlah'] . "</td>
				<td>:</td>
				<td>
					<input id='hasilhkdt2' class='myinputtextnumber' onkeypress='return angka_doang(event)' type='text' placeholder=0 style='width:80px' onkeyup=\"calJumlah()\">
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['jumlahrp'] . "</td>
				<td>:</td>
				<td>
					<input id='jumlahrpdt2' class='myinputtextnumber' onkeypress='return angka_doang(event)' type='text' placeholder=0 style='width:80px'>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class='mybutton' onclick=\"simpandt2('" . $notransaksi . "','" . $kodeblokdt . "','" . $kodekegdt . "','" . $dttermin . "','" . $nobatermin . "')\">" . $_SESSION['lang']['save'] . "</button>
				</td>
			</td>
		</table>";

		echo $tab;
		break;

	case 'getsewahm':
		$hasilkerja = "";
		$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $notransaksi . "'";
		$res = fetchdata($str);
		$jenis = $res[0]['jenis'];

		if ($jenis == 'SEWA.HM') {
			$param['tgldt2'] = tanggalsystemn($param['tgldt2']);
			$param['bulan'] = substr($param['tgldt2'], 0, 7);

			$str = "select sum(jumlah) as jumlah from " . $dbname . ".vhc_rundt_vw where kodevhc = '" . $param['kodeblok'] . "' and tanggal like '" . $param['bulan'] . "%'"; #exit("error".$str);
			$res = fetchdata($str);
			$hasilkerja = $res[0]['jumlah'];
		}

		echo $hasilkerja;
		break;
	case 'deletedt2':
		$str = "delete from " . $dbname . ".log_baspkdt where notransaksi='" . $notransaksi . "' and kodeblok='" . $kodeblokdt . "' and kodekegiatan='" . $kodekegdt . "' and termin='" . $dttermin . "'";
		try {
			$owlPDO->exec($str);
			$str = "delete from " . $dbname . ".log_baspk where notransaksi='" . $notransaksi . "' and kodeblok='" . $kodeblokdt . "' and kodekegiatan='" . $kodekegdt . "' and termin='" . $dttermin . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'simpandt2':
		if ($tgldt2 == '') {
			exit("Gagal, Tanggal harus diisi");
		}
		if ($hkdt2 == '') {
			$hkdt2 = 0;
		}
		if ($hasilhkdt2 == '' || $hasilhkdt2 == '0') {
			exit("Gagal, " . $_SESSION['lang']['hasilkerja'] . " = 0.");
		}
		if ($jumlahrpdt2 == '' || $jumlahrpdt2 == '0') {
			exit("Gagal, " . $_SESSION['lang']['jumlahrp'] . " = 0");
		}

		if ($matauangdt != 'IDR') {
			$str = "select * from " . $dbname . ".setup_matauangrate where daritanggal='" . tanggalsystem($tgldt2) . "'";
			$res = fetchdata($str);
			if (count($res) <= 0) {
				echo "Gagal : Data kurs untuk mata uang " . $matauangdt . " pada tanggal " . $tgldt2 . " belum ada";
			}
		}

		# Options
		$optBlok = array();
		if (strlen($divisi) == 4) {
			$optBlok[$divisi] = $divisi;
		}

		$str = "SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM " . $dbname . ".setup_blok a LEFT JOIN " . $dbname . ".organisasi b 
			ON a.kodeorg = b.kodeorganisasi 
			WHERE a.luasareaproduktif >0 and b.kodeorganisasi like '" . substr($divisi, 0, 4) . "%' 
			and length(b.kodeorganisasi)>6";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$optBlok[$val['kodeorg']] = $val['namaorg'];
		}

		#khusus jika project
		if (empty($divisi)) {
			$optBlok = makeOption($dbname, 'project', 'kode,nama', "kodeorg='" . $kebun . "' and kode='" . $kodeblokdt . "' and posting=0");
		}

		if ($divisi == 'P') {
			//pabrikasi
			$optBlok = makeOption($dbname, 'pabrikasi_5masterht', 'kodepabrikasi,namapabrikasi', '', 2);
		}

		#khusus jika Perumahan
		if ($divisi == 'S') {
			$optRegOrg = makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional', "kodeunit='" . substr($kebun, 0, 4) . "'");
			$optBlok = makeOption($dbname, 'sdm_perumahanht', 'norumah,keterangan', "kodeorg in (select kodeunit from " . $dbname . ".bgt_regional_assignment where regional = '" . $optRegOrg[substr($kebun, 0, 4)] . "')");
		}

		//cek tanam: april 4, 2014
		//dicopy dari file: kebun_slave_operasional_detail: cegatKegiatan
		$kegiatan = $kodekegdt;
		$kodeorg = $kodeblokdt;
		$hasilkerja = $jumlahrpdt2;
		$qwe = explode('-', $tgldt2);
		$tanggal = $qwe[2] . '-' . $qwe[1] . '-' . $qwe[0];

		// ambil kode parameter kegiatan
		$where = "nilai = '" . $kegiatan . "'";
		$cols = "kodeparameter";
		$query = selectQuery($dbname, 'setup_parameterappl', $cols, $where);
		$res = $owlPDO->query($query) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$kodeparameter = '';
		while ($bar = $res->fetch()) {
			$kodeparameter = $bar->kodeparameter;
		}

		$luasareanonproduktif = 0;
		$jumlahpokok = 0;
		$luasareaproduktif = 0;
		// kalo kegiatan tanam, cek. kalo luas blok = luas kerangka tidak bisa.
		$where = "kodeorg = '" . $kodeorg . "'";
		$cols = "luasareanonproduktif,jumlahpokok,luasareaproduktif";
		$query = selectQuery($dbname, 'setup_blok', $cols, $where);
		$res = $owlPDO->query($query) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$luasareanonproduktif = $bar->luasareanonproduktif;
			$jumlahpokok = $bar->jumlahpokok;
			$luasareaproduktif = $bar->luasareaproduktif;
		}
		@$sph = ($jumlahpokok + $hasilkerja) / $luasareaproduktif;
		$maxtanam = $luasareanonproduktif * 150;

		// kalo kegiatan sisip, cek. kalo sisa rencanasisip-udahsisip<=0 tidak bisa.
		// ambil rencana sisip s/d pada tahun berjalan
		#update indra : karena table rencana sisip untuk periode format m-Y (05-2017)
		#maka pembentukan where periode mesti diganti
		$perrencana = substr($tanggal, 5, 2) . '-' . substr($tanggal, 0, 4);
		$where = "blok = '" . $kodeorg . "' and periode <= '" . $perrencana . "' and 
				substr(periode,4,4) = '" . substr($tanggal, 0, 4) . "' and posting ='1'";
		$cols = "sum(rencanasisip) as rencanasisip";
		$query = selectQuery($dbname, 'kebun_rencanasisip', $cols, $where);
		$res = $owlPDO->query($query) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$rencanasisip = 0;
		while ($bar = $res->fetch()) {
			@$rencanasisip += $bar->rencanasisip;
		}

		// ambil jumlah sisip
		// BKM
		$query = "select kodeorg,sum(hasilkerja)as telahsisip from " . $dbname . ".kebun_perawatan_vw 
            where kodekegiatan in (select nilai from " . $dbname . ".setup_parameterappl where kodeparameter like 'SISIP%')
            and kodeorg = '" . $kodeorg . "' and tanggal >= '" . $tanggal . "' and tanggal like '" . substr($tanggal, 0, 4) . "%'";
		$res = $owlPDO->query($query) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$sudahsisip = 0;
		while ($bar = $res->fetch()) {
			$sudahsisip += $bar->telahsisip;
		}

		// PERAWATAN
		$query = "select kodeblok,sum(hasilkerjarealisasi)as telahsisip from " . $dbname . ".log_baspk 
            where kodekegiatan in (select nilai from " . $dbname . ".setup_parameterappl where kodeparameter like 'SISIP%')
            and kodeblok = '" . $kodeorg . "' and tanggal >= '" . $tanggal . "' and tanggal like '" . substr($tanggal, 0, 4) . "%'";
		$res = $owlPDO->query($query) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$sudahsisip += $bar->telahsisip;
		}
		$sisasisip = $rencanasisip - ($sudahsisip + $hasilkerja);

		if (substr($kodeparameter, 0, 5) == 'TANAM') {
			if ($hasilkerja > $maxtanam) {
				echo "error: Tidak bisa tanam baru, luas yang belum ditanam: " . number_format($luasareanonproduktif, 2) . " Ha, pokok bisa ditanam: " . number_format($maxtanam) . ". Jumlah ditanam: " . number_format($hasilkerja) . ".";
				exit();
			}
		}
		if (substr($kodeparameter, 0, 5) == 'COMPL') {
			if ($sph > 150) {
				echo "error: SPH setelah transaksi lebih dari 150: " . number_format($sph, 2) . ".";
				exit();
			}
		}
		if (substr($kodeparameter, 0, 5) == 'SISIP') {
			if ($sisasisip < 0) {
				echo "error: Harap diinput data pokok mati dan rencana sisipan, rencana sisip: " . $rencanasisip . ", sudah sisip: " . $sudahsisip . " + " . $hasilkerja . ", sisa rencana sisip: " . $sisasisip . ".";
				exit();
			}
		}

		$tbaspk = $tspk = 0;
		#buat validasi tidak boleh lebih dari spk
		#ambil dispk
		$str = " select jumlahrp as tspk,kodeblok from " . $dbname . ".log_spkdt where 
			notransaksi='" . $notransaksi . "' and kodeblok='" . $kodeblokdt . "'
			and kodekegiatan='" . $kodekegdt . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$tspk = $bar['tspk'];
		$blk = $bar['kodeblok'];



		#ambil data di ba
		$str = " select sum(jumlahrealisasi) as tbaspk from " . $dbname . ".log_baspk where 
			notransaksi='" . $notransaksi . "' and kodeblok='" . $kodeblokdt . "'
			and kodekegiatan='" . $kodekegdt . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$tbaspk = $bar['tbaspk'];


		$optnopengajuan = makeOption($dbname, 'log_spkht', 'notransaksi,nopengajuan', "notransaksi='" . $notransaksi . "'");
		$tipekontrak   = makeOption($dbname, 'lgl_pengajuanspkht', 'notransaksi,jenis', "notransaksi='" . $optnopengajuan[$notransaksi] . "'");
		$jenisspk      = $tipekontrak[$optnopengajuan[$notransaksi]];

		#kalau project validasi vs total spk
		$str = "select sum(nilaikontrak) as jlh from " . $dbname . ".log_spkht where notransaksi = '" . $notransaksi . "'";
		$bar = fetchData($str)[0];
		if ($jenisspk == 'PROJECT') {
			$tbaspk = $tspk = 0;

			$tspk = $bar['jlh'];

			$str = " select sum(jumlahrealisasi) as tbaspk from " . $dbname . ".log_baspk where notransaksi='" . $notransaksi . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$tbaspk = $bar['tbaspk'];
		}


		$strpa = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='KLPLUS'and nilai like '%" . $kodekegdt . "%' ";
		$barpa = fetchData($strpa);
		$jmlkgplus = count($barpa);

		// if ($jmlkgplus == 0) {
		// 	if ($kodeblokdt != $blk) {
		// 		// exit("Gagal, blok salah atau tidak ditemukan");
		// 	} else if (($jumlahrpdt2 + $tbaspk) > $tspk) {
		// 		exit("Gagal, Data melebihi SPK, nilai BA : " . ($jumlahrpdt2 + $tbaspk) . " nilai SPK : " . $tspk);
		// 	}
		// }

		$str = "insert into " . $dbname . ".log_baspkdt (notransaksi,kodeblok,kodekegiatan,tanggal,hasilkerjarealisasi,hkrealisasi,jumlahrealisasi,termin,keterangan,keterangan2) values ('" . $notransaksi . "','" . $kodeblokdt . "','" . $kodekegdt . "','" . tanggalsystem($tgldt2) . "','" . $hasilhkdt2 . "','" . $hkdt2 . "','" . $jumlahrpdt2 . "','" . $dttermin . "','" . $nobatermin . "','" . $keterangandt2 . "')";
		try {
			$owlPDO->exec($str);
			$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $notransaksi . "' and kodeblok='" . $kodeblokdt . "' and kodekegiatan='" . $kodekegdt . "' and termin='" . $dttermin . "' and keterangan='" . $nobatermin . "'";
			$res = fetchdata($str);
			$hasilhkdt25 = $res[0]['hasilkerjarealisasi'] + $hasilhkdt2;
			$hkdt25 = $res[0]['hkrealisasi'] + $hkdt2;
			$jumlahrpdt25 = $res[0]['jumlahrealisasi'] + $jumlahrpdt2;

			$str = "update " . $dbname . ".log_baspk set hasilkerjarealisasi='" . $hasilhkdt25 . "',hkrealisasi='" . $hkdt25 . "',jumlahrealisasi='" . $jumlahrpdt25 . "' where notransaksi='" . $notransaksi . "' and kodeblok='" . $kodeblokdt . "' and kodekegiatan='" . $kodekegdt . "' and termin='" . $dttermin . "' and keterangan='" . $nobatermin . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}


		break;

	case 'inserttermin':

		$nobatermin = trim($nobatermin);
		if ($dttermin == '') {
			exit("Gagal, Termin harus diisi");
		}
		if ($nobatermin == '') {
			exit("Gagal, No. BAPP harus diisi");
		}

		$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $notransaksi . "'";
		$res = fetchdata($str);
		$jenis = $res[0]['jenis'];


		$str = "select * from " . $dbname . ".log_baspk where notransaksi!='" . $notransaksi . "' and keterangan='" . $nobatermin . "'";
		$res = fetchdata($str);
		if (count($res) > 0) {
			exit("Gagal, No BAPP " . $nobatermin . " sudah pernah diinput pada nomor SPK lain.");
		}

		$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $notransaksi . "' and keterangan='" . $nobatermin . "' and termin!='" . $dttermin . "'";
		$res = fetchdata($str);
		if (count($res) > 0) {
			exit("Gagal, No BAPP " . $nobatermin . " sudah pernah diinput pada termin lain.");
		}

		$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $notransaksi . "' and kodekegiatan='" . $kodekegdt . "' and kodeblok='" . $kodeblokdt . "' and termin='" . $dttermin . "'";
		$res = fetchdata($str);
		if (count($res) > 0) {
			exit("Gagal, Termin " . $dttermin . " sudah pernah diinput sebelumnya.");
		}

		$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $notransaksi . "' and termin='" . $dttermin . "'";
		$res = fetchdata($str);
		if (count($res) > 0) {
			if ($res[0]['statuspengajuan'] != '0' and $res[0]['statuspengajuan'] != '3') {
				exit("Gagal, Termin " . $dttermin . " sudah atau dalam tahap persetujuan.");
			}
		}

		$str = "select indukblok,statusblok,kodeorg from " . $dbname . ".setup_blok where indukblok like '" . $kodeblokdt2 . "%'";
		$getBlokk = fetchdata($str);
		foreach ($getBlokk as $v) {
			if ($v['statusblok'] == 'TB' || $v['statusblok'] == 'TBM') {
				$getBlok['126'] = $v['statusblok'];
			}
			if ($v['statusblok'] == 'BBT') {
				$getBlok['128'] = $v['statusblok'];
			}
			if ($v['statusblok'] == 'TM') {
				$getBlok['611'] = $v['statusblok'];
			}
			if ($v['statusblok'] == 'TM') {
				$getBlok['621'] = $v['statusblok'];
			}
		}

		if (substr($kodekegdt, 0, 3) == '128' and $getBlok[substr($kodekegdt, 0, 3)] != 'BBT') {
			exit("Gagal, Kegiatan " . getNamaKeg($kodekegdt) . " harus menggunakan blok Bibitan.");
		}
		if (substr($kodekegdt, 0, 3) == '621' and $getBlok[substr($kodekegdt, 0, 3)] != 'TM') {
			exit("Gagal, Kegiatan " . getNamaKeg($kodekegdt) . " harus menggunakan blok TM.");
		}
		if (substr($kodekegdt, 0, 3) == '611' and $getBlok[substr($kodekegdt, 0, 3)] != 'TM') {
			exit("Gagal, Kegiatan " . getNamaKeg($kodekegdt) . " harus menggunakan blok TM.");
		}
		if (substr($kodekegdt, 0, 3) == '126' and ($getBlok[substr($kodekegdt, 0, 3)] != 'TBM' and $getBlok[substr($kodekegdt, 0, 3)] != 'TB')) {
			exit("Gagal, Kegiatan " . getNamaKeg($kodekegdt) . " harus menggunakan blok TBM atau TB.\nStatus Blok " . getNamaOrg($kodeblokdt2) . " = " . $getBlok[substr($kodekegdt, 0, 3)]);
		}

		#buat validasi tidak boleh lebih dari spk
		#ambil dispk
		$str = " select jumlahrp as tspk,kodeblok from " . $dbname . ".log_spkdt where 
			notransaksi='" . $notransaksi . "' and kodeblok='" . $kodeblokdt2 . "'
			and kodekegiatan='" . $kodekegdt . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$blk = $bar['kodeblok'];

		##cek jika kegiatanya termasuk yg perhitungan berbeda
		$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='KLPLUS'and nilai like '%" . $kodekegdt . "%' ";
		$barpa = fetchData($str);
		$jmlkgplus = count($barpa);
		if ($jmlkgplus == 0) {
			if ($kodeblokdt2 != $blk and $jenis != 'SEWA.HM') {
				// exit("Gagal, blok salah atau tidak ditemukan");
			}
		}

		$str = "insert into " . $dbname . ".log_baspk (notransaksi,kodeblok,kodekegiatan,posting,statusjurnal,statuspengajuan,blokspkdt,termin,keterangan) values ('" . $notransaksi . "','" . $kodeblokdt2 . "','" . $kodekegdt . "','0','0','0','" . $kodeblokdt2 . "','" . $dttermin . "','" . $nobatermin . "')";

		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'loaddata':

		$where = "";
		$kdorg = $_SESSION['empl']['kodeorganisasi'];
		if ($notransaksicr != '') {
			$where .= " and notransaksi like '%" . $notransaksicr . "%' ";
		}
		if ($unitcr != '') {
			$where .= " and kodeorg = '" . $unitcr . "' ";
		}
		if ($koderekanancr != '') {
			$where .= " and koderekanan in (select supplierid from " . $dbname . ".log_5supplier where namasupplier like '%" . $koderekanancr . "%')";
		}
		if ($tglcr != '') {
			$where .= " and tanggal = '" . tanggalsystem($tglcr) . "'";
		}

		// if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
		// 	$where .= " and length(kodeorg)=4";
		// } else if ($_SESSION['empl']['tipelokasitugas'] == 'TRAKSI' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
		// 	$where .= " and length(kodeorg)=4 and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk = '" . $kdOrganisasi . "') ";
		// } else {
			$where .= " and kodeorg IN (" . getOrgDetail(2) . ")";
		// }

		$where .= " and nopengajuan in (select notransaksi from " . $dbname . ".lgl_pengajuanspkht where jenis not in ('PO/SO','BELITBS','JUALTBS'))";

		$limit = 15;
		$page = 0;
		if (isset($pages)) {
			$page = $pages;
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;

		$str = "select * from " . $dbname . ".log_spkht where 1=1 " . $where;
		$res = fetchdata($str);
		$jlhbrs = count($res);
		if ($jlhbrs == 0) {
			$tab .= "<tr class=rowcontent>
				<td colspan=13>" . $_SESSION['lang']['dataempty'] . "</td>
			</tr>";
		} else {
			$tab = "";
			$no = (($page * $limit));
			$str = "select * from " . $dbname . ".log_spkht where 1=1 " . $where . " order by tanggal desc limit " . $offset . "," . $limit . "";
			// exit('warning; ' . $str);
			$res = fetchdata($str);
			foreach ($res as $val) {
				$no++;

				$optrekanan = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $val['koderekanan'] . "'");

				##Jumlah Realisasi
				$realisasi = 0;
				$strx = "select sum(jumlahrealisasi) as jumlahrealisasi from " . $dbname . ".log_baspk where notransaksi='" . $val['notransaksi'] . "' and statusjurnal = '1'";
				$resx = fetchdata($strx);
				$realisasi = ($resx[0]['jumlahrealisasi'] == '' ? 0 : $resx[0]['jumlahrealisasi']);

				##Status Posting
				$statusposting = "";
				$strx = "select statusjurnal from " . $dbname . ".log_baspk where notransaksi='" . $val['notransaksi'] . "' and statusjurnal=0";
				$resx = fetchdata($strx);
				if (count($resx) > 0) {
					$statusposting = "?";
				} else if ($realisasi == 0 and $statusposting == '') {
					$statusposting = "?";
				} else {
					$statusposting = "Posted";
				}

				##Status Tagihan & Lunas
				$strTagihan = "select sum(nilaiinvoice) as totaltagihan from ".$dbname.".keu_tagihanht where nopo='".$val['notransaksi']."'";
				$resTagihan = fetchdata($strTagihan);
				$totalTagihan = ($resTagihan[0]['totaltagihan'] == '' ? 0 : $resTagihan[0]['totaltagihan']);

				$strBayar = "select sum(jumlah) as dibayar from ".$dbname.".keu_kasbankdtht_vw where nodok='".$val['notransaksi']."' and jumlah > 0";
				$resBayar = fetchdata($strBayar);
				$totalBayar = ($resBayar[0]['dibayar'] == '' ? 0 : $resBayar[0]['dibayar']);
				
				$statusLunas = "";
				if ($totalTagihan == 0) {

						$statusLunas = "<span style='color:gray;'>Belum Tagih</span>";

					} else {

							$persentase = ($totalBayar / $totalTagihan) * 100;
							$persentase = min($persentase, 100);

							if ($totalBayar >= $totalTagihan && $totalBayar >= $val['nilaikontrak']) {

									$statusLunas = "<span style='color:green; font-weight:bold;'>
											Lunas (100%)
									</span>";

							} else if ($totalBayar > 0) {

									$statusLunas = "<span style='color:orange; font-weight:bold;'>
											Dibayar " . number_format($persentase, 0) . "%
									</span>";

							} else {

									$statusLunas = "<span style='color:red; font-weight:bold;'>
											Belum Lunas (0%)
									</span>";
							}
					}

				$tab .= "<tr class=rowcontent style='text-align:center'>
					<td style='vertical-align:top'>" . $no . "</td>
					<td style='vertical-align:top'>" . $val['kodeorg'] . "</td>
					<td style='vertical-align:top'>" . $val['notransaksi'] . "</td>
					<td style='vertical-align:top'>" . tanggalnormal($val['tanggal']) . "</td>
					<td style='vertical-align:top'>" . $val['divisi'] . "</td>
					<td style='vertical-align:top'>" . $optrekanan[$val['koderekanan']] . "</td>
					<td style='vertical-align:top'>" . number_format($val['nilaikontrak']) . "</td>
					<td style='vertical-align:top'>" . $val['matauang'] . "</td>
					<td style='vertical-align:top'>" . number_format($realisasi) . "</td>
					<td style='vertical-align:top'>" . $statusposting . "</td>
					<td style='vertical-align:top'>" . $statusLunas . "</td>
					
					<td style='vertical-align:top'>
						<img src='images/skyblue/edit.png' class='zImgBtn' onclick=\"showEdit('" . $val['notransaksi'] . "','" . $val['kodeorg'] . "')\" title='Edit'>
					</td>
					<td style='vertical-align:top'>
						<img src='images/skyblue/pdf.jpg' class='zImgBtn' onclick=\"detailPDF2('" . $val['notransaksi'] . "','" . $val['kodeorg'] . "','viewpdf',event)\" title='Print Data Detail'>
					</td>
					<td style='vertical-align:top'>
						<img src='images/skyblue/zoom.png' class='zImgBtn' onclick=\"viewdetail('" . $val['notransaksi'] . "','" . $val['kodeorg'] . "','viewhtml',event)\" title='View'>
					</td>
				</tr>";
			}
		}

		$totrows = ceil($jlhbrs / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}

		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
		}

		$frompage = (($page * $limit) + 1);
		if ((($page + 1) * $limit) > $jlhbrs) {
			$topage = $jlhbrs;
		} else {
			$topage = (($page + 1) * $limit);
		}
		$tab .= "</tr>
		<tr>
			<td colspan=13 align=center>
				" . $frompage . " to " . $topage . " Of " .  $jlhbrs . "
			</td>
		</tr>
		<tr>
			<td colspan=13 align=center>";

		if ($page == '0') {
			$tab .= "";
		} else {
			$tab .= "<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
		}

		$tab .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getpage()\">" . $isiRow . "</select>";

		if (($page + 1) == $totrows) {
			$tab .= "";
		} else {
			$tab .= "<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
		}
		$tab .= "</td></tr>";

		echo $tab;
		break;

	case 'rekapbapp':
		// echo"<pre>";
		// print_r($param);

		// exit("error cccc");
		$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $notransaksi . "'";
		$cekpost = count(fetchData($str));
		if ($cekpost == 0) {
			$tab = "Data Kosong!";
		} else {
			if ($tipeview == 'viewhtml') {
				$border = " border=0 cellpadding='5' cellspacing='1' ";
				$colspan = "8";
			} else {
				$border = " border=1 cellpadding='5' cellspacing='0' ";
				$colspan = "5";
				$tab .= "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
				<table width='100%' cellpadding=3>
					<tr>
						<td style='font-weight:bold;font-size:40px;text-align:center'>Realisasi BAPP</td>
					</tr>
				</table><br>";
			}
			$tab .= "<table border=0 cellpadding=0 style=width:100%>
				<tr>
					<td align=center style=background-color:gray;height:25px><b>SPK</b></td>
				</tr>
				<tr>
					<td>";

			#=====================================================
			$tab .= "<table " . $border . " class=sortable style=width:100%>
					<thead><tr class=rowheader>";

			$tab .= "<td align=center width=20px>No</td>
						<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
						<td align=center>" . $_SESSION['lang']['nomor'] . "</td>
						<td align=center>" . $_SESSION['lang']['koderekanan'] . "</td>
						<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
						<td align=center>" . $_SESSION['lang']['dari'] . "</td>
						<td align=center>" . $_SESSION['lang']['sampai'] . "</td>
						<td align=center>" . $_SESSION['lang']['jumlah'] . " (Rp)</td>
					</tr>
					</thead>";

			$where = "notransaksi='" . $notransaksi . "'";
			$str = "select * from " . $dbname . ".log_spkht where " . $where . "";
			$res = fetchdata($str);
			$no = '0';
			foreach ($res as $val) {
				$no += 1;
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td align=center>" . $no . "</td>";
				$tab .= "<td align=center>" . tanggalnormal($val['tanggal']) . "</td>";
				$tab .= "<td align=center style=color:blue;cursor:pointer; onclick=htmlspk('" . $val['notransaksi'] . "','html')>" . $val['notransaksi'] . "</td>";
				$tab .= "<td align=center>" . getNamaSupplier($val['koderekanan']) . "</td>";
				$tab .= "<td align=left>" . $val['keterangan'] . "</td>";
				$tab .= "<td align=center>" . tanggalnormal($val['dari']) . "</td>";
				$tab .= "<td align=center>" . tanggalnormal($val['sampai']) . "</td>";
				$tab .= "<td align=right>" . number_format($val['nilaikontrak']) . "</td>";
				$tab .= "</tr>";
				@$totalspk += $val['nilaikontrak'];
			}

			$tab .= "<tr class=rowcontent>
						<td align=center colspan=7>T O T A L</td>
						<td align=right>" . number_format($totalspk) . "</td>
					</tr>
					</table>
					</td>";
			#=====================================================
			$tab .= "</tr>
			</table><br>";

			$tab .= "<table border=0 cellpadding=0 style=width:100%>
				<tr>
					<td align=center style=background-color:gray;height:25px><b>Tagihan dan Kas Bank</b></td>
				</tr>
				<tr>
					<td valign=top>";
			#=====================================================
			$tab .= "<table " . $border . " class=sortable style=width:100%>
						<thead><tr class=rowheader>
							<td align=center width=20px>No</td>
							<td align=center>Tanggal</td>
							<td align=center>No Invoice</td>
							<td align=center>Tipe</td>
							<td align=center>Jumlah</td>
							<td style=background-color:gray;cellpadding:0;width:1px></td>
							<td align=center>Tanggal</td>
							<td align=center>No Kas Bank</td>
							<td align=center>Jumlah</td>
						</tr>
						</thead>";
			$datatagihan = array();
			$str = "select * from " . $dbname . ".keu_tagihanht where nopo='" . $notransaksi . "' order by noinvoice asc";
			$res = fetchdata($str);
			foreach ($res as $val) {
				$datatagihan[$val['noinvoice']] = $val['noinvoice'];
				$tipeinvoice[$val['noinvoice']] = $val['tipeinvoice'];
				$tanggalinv[$val['noinvoice']] = $val['tanggal'];
				$nilaiinvoice[$val['noinvoice']] = $val['nilaiinvoice'];
				$nopo[$val['noinvoice']] = $val['nopo'];
			}

			$no = '0';
			if (count($datatagihan) > 0) {
				foreach ($datatagihan as $noinvoice) {
					$nmtipe = makeOption($dbname, 'keu_5jenistagihan', 'kode,namajenis', "kode='" . $tipeinvoice[$noinvoice] . "'");

					$no += 1;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td align=center>" . $no . "</td>";
					$tab .= "<td align=center>" . tanggalnormal($tanggalinv[$noinvoice]) . "</td>";
					$tab .= "<td align=center>" . $noinvoice . "</td>";
					$tab .= "<td align=left>" . $nmtipe[$tipeinvoice[$noinvoice]] . "</td>";
					$tab .= "<td align=right>" . number_format($nilaiinvoice[$noinvoice]) . "</td>";
					$tab .= "<td align=center  style=background-color:gray></td>";

					#kas bank
					$strKb = "select sum(jumlah) as jumlah, tanggal, notransaksi from " . $dbname . ".keu_kasbankdtht_vw where nodok='" . $nopo[$noinvoice] . "' and keterangan1='" . $noinvoice . "' and jumlah>'0'";
					$resKb = $owlPDO->query($strKb) or die(print " Gagal: " . PDOException::getMessage());
					$resKb->setFetchMode(PDO::FETCH_ASSOC);
					$barKb = $resKb->fetch();
					if ($barKb['tanggal'] == '0000-00-00' or $barKb['tanggal'] == '') {
						$tab .= "<td></td>";
					} else {
						$tab .= "<td align=center>" . tanggalnormal($barKb['tanggal']) . "</td>";
					}
					$tab .= "<td align=left>" . $barKb['notransaksi'] . "</td>";
					$tab .= "<td align=right>" . number_format($barKb['jumlah']) . "</td>";

					$tab .= "</tr>";
					@$totaltagihan += $nilaiinvoice[$noinvoice];
					@$totalkasbank += $barKb['jumlah'];
				}

				$tab .= "<tr class=rowcontent>
								<td align=center colspan=4>T O T A L</td>
								<td align=right>" . number_format($totaltagihan) . "</td>
								<td align=center  style=background-color:gray></td>
								<td align=center colspan=2></td>
								<td align=right>" . number_format($totalkasbank) . "</td>
							</tr>";
			}

			$tab .= "</table>";
			#=====================================================	
			$tab .= "</td>
				</tr>
			</table><br>";


			$tab .= "<table border=0 cellpadding=0 style='width:100%;'>
				<tr>
					<td align=center style=background-color:gray;height:25px;><b>BAPP</b></td>
				</tr>
				<tr>
					<td valign=top>";
			#=====================================================
			$style = '';
			if (@$param['sumber'] == 'approval') {
				$style = "style=display:none";
			}
			$header .= "<table " . $border . " class=sortable style='width:100%;page-break-before:always;'>
						<thead><tr class=rowheader>
						<td align=center width=20px>No</td>
						<td align=center>Termin</td>
						<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
						<td align=center>Hasil Kerja</td>
						<td align=center>" . $_SESSION['lang']['jumlah'] . " (Rp)</td>
						<td align=center>No BAPP</td>
						<td align=center>No Persetujuan</td>
						<td align=center colspan=2>Persetujuan</td>";
			if ($tipeview == 'viewhtml') {
				$header .= "<td align=center " . $style . " colspan=2>#</td>";
			}
			$header .= "</tr>
					</thead>";

			$tab .= "<table " . $border . " class=sortable style='width:100%'>
						<thead><tr class=rowheader>
						<td align=center width=20px>No</td>
						<td align=center>Termin</td>
						<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
						<td align=center>Hasil Kerja</td>
						<td align=center>" . $_SESSION['lang']['jumlah'] . " (Rp)</td>
						<td align=center>No BAPP</td>
						<td align=center>No Persetujuan</td>
						<td align=center colspan=2>Persetujuan</td>";
			if ($tipeview == 'viewhtml') {
				$tab .= "<td align=center " . $style . " colspan=3>#</td>";
			}
			$tab .= "</tr>
					</thead>";
			$datashow = $tanggal = $ket = $real = $hasilkerja = $notran = $statuspengajuan = $nopengajuan = $stsjurnal = array();
			$where = "";
			$where .= "notransaksi='" . $notransaksi . "'";
			if ($nobapp != '') {
				$where .= "and keterangan='" . $nobapp . "'";
			}
			$str = "select * from " . $dbname . ".log_baspk where " . $where . " order by keterangan desc, statusjurnal desc ";
			$res = fetchdata($str);
			foreach ($res as $val) {
				$val['termin'] = trim($val['termin']);
				$val['tanggal'] = trim($val['tanggal']);
				$val['keterangan'] = trim($val['keterangan']);

				$datashow[$val['termin']][$val['tanggal']][$val['keterangan']] = $val['keterangan'];
				$tanggal[$val['termin']][$val['tanggal']][$val['keterangan']] = $val['tanggal'];
				$ket[$val['termin']][$val['tanggal']][$val['keterangan']] = $val['keterangan'];
				@$real[$val['termin']][$val['tanggal']][$val['keterangan']] += $val['jumlahrealisasi'];
				@$hasilkerja[$val['termin']][$val['tanggal']][$val['keterangan']] += $val['hasilkerjarealisasi'];
				$notran[$val['termin']][$val['tanggal']][$val['keterangan']] = $val['notransaksi'];
				$statuspengajuan[$val['termin']][$val['tanggal']][$val['keterangan']] = $val['statuspengajuan'];
				$nopengajuan[$val['termin']][$val['tanggal']][$val['keterangan']] = $val['nopengajuan'];
				$stsjurnal[$val['termin']][$val['tanggal']][$val['keterangan']] = $val['statusjurnal'];
			}


			$no = '0';
			$nox = '0';
			$kodeorgspk = makeOption($dbname, 'log_spkht', 'notransaksi,kodeorg', "notransaksi='" . $notransaksi . "'");

			$optnmkary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
			if (count($datashow) > 0) {
				foreach ($datashow as $termin => $valtgl) {
					foreach ($valtgl as $tanggal => $nbapp) {
						foreach ($nbapp as $bapp) {
							$x = '';
							if (@$param['nopengajuan'] == $nopengajuan[$termin][$tanggal][$bapp] and @$param['sumber'] == 'approval') {
								$x = "style=background-color:green";
							}

							$no += 1;

							if ($tipeview == 'viewpdf') {
								if ($no == 3) {
									$tab .= $header;
								} else if ($no == 10) {
									$tab .= $header;
									$nox = 1;
								} else if ($nox >= 1) {
									$nox++;
									if ($nox % 7 == 0) {
										$tab .= $header;
									}
								}
							}

							$tab .= "<tr class=rowcontent " . $x . " id=rowdetail_" . $no . ">";
							$tab .= "<td align=center>" . $no . "</td>";
							$tab .= "<td align=center>" . $termin . "</td>";
							$tab .= "<td align=center>" . ($tanggal == '0000-00-00' ? '' : tanggalnormal($tanggal)) . "</td>";
							$tab .= "<td align=right>" . number_format($hasilkerja[$termin][$tanggal][$bapp],2) . "</td>";
							$tab .= "<td align=right>" . number_format($real[$termin][$tanggal][$bapp],2) . "</td>";
							$tab .= "<td align=left>" . ($ket[$termin][$tanggal][$bapp]) . "</td>";

							#persetujuan
							$warna = '';
							if ($statuspengajuan[$termin][$tanggal][$bapp] == '3') {
								$warna = " style=background-color:red";
							}
							$i = '';
							if ($nopengajuan[$termin][$tanggal][$bapp] != '') {
								$i = "onclick=getapprovaldetail('" . $nopengajuan[$termin][$tanggal][$bapp] . "','" . $kodeorgspk[$notran[$termin][$tanggal][$bapp]] . "',event)";
							}
							$tab .= "<td align=center style=cursor:pointer " . $i . "><font color=blue>" . $nopengajuan[$termin][$tanggal][$bapp] . "</font></td>";

							#$tab.="<td ".$warna." align=center>".$optstatus[$statuspengajuan[$termin][$tanggal][$bapp]]."</td>";

							# approval		
							$arrHsl = array("0" => $_SESSION['lang']['wait_approval'], "1" => $_SESSION['lang']['disetujui'], "2" => $_SESSION['lang']['koreksi'], "3" => $_SESSION['lang']['ditolak']);

							$strX = "select * from " . $dbname . ".approval where notransaksi='" . $nopengajuan[$termin][$tanggal][$bapp] . "' and jenispersetujuan='BAPP' order by level desc limit 1";
							$resX = $owlPDO->query($strX) or die(print " Gagal: " . PDOException::getMessage());
							$resX->setFetchMode(PDO::FETCH_ASSOC);
							$barX = $resX->fetch();
							if ($barX['tanggal'] == '' || $barX['tanggal'] == '0000-00-00 00:00:00') {
								$tngl = '';
							} else {
								$tngl = tanggalnormal($barX['tanggal']);
							}

							$isi = @$optnmkary[$barX['karyawanid']] . "
											<br>" . @$arrHsl[$barX['status']];
							#."<br>".$tngl."
							#<br>".$barX['komentar'];
							if ($barX['karyawanid'] != '') {
								$tab .= "<td " . $warna . ">" . trim($isi) . "</td>";
							} else {
								$tab .= "<td " . $warna . "></td>";
							}
							# end approval

							if (($statuspengajuan[$termin][$tanggal][$bapp] == '0' or $statuspengajuan[$termin][$tanggal][$bapp] == '3') and $tipeview == 'viewhtml') {
								$tab .= "<td " . $style . " align=center width=25px><img src='images/skyblue/submit.jpg' class='resicon' height='30' title='Ajukan' onclick=\"form_ajukan('" . $kodeorgspk[$notransaksi] . "','" . $notransaksi . "','" . $tanggal . "','" . $termin . "','" . $no . "','" . $real[$termin][$tanggal][$bapp] . "','" . $ket[$termin][$tanggal][$bapp] . "');\"></td>";
							} else {
								$tab .= "<td " . $style . " align=center></td>";
							}

							if ($tipeview == 'viewhtml') {
								if ($statuspengajuan[$termin][$tanggal][$bapp] == 1 and $stsjurnal[$termin][$tanggal][$bapp] == 0) {
									$tab .= "<td style='text-align:center'>
												<img class='zImgBtn' src='images/skyblue/posting.png' onclick=\"formpostingDataAll('" . $nopengajuan[$termin][$tanggal][$bapp] . "','" . $notransaksi . "','" . $ket[$termin][$tanggal][$bapp] . "','" . $kodeorgspk[$notransaksi] . "','" . $tanggal . "','" . $termin . "','" . $no . "')\">
											</td>";
								} elseif ($statuspengajuan[$termin][$tanggal][$bapp] == 1 and $stsjurnal[$termin][$tanggal][$bapp] == 1) {
									$tab .= "<td align=center><img title='View Jurnal' class='zImgBtn' src='images/skyblue/posted.png' onclick=\"getdetailjurnal('" . $notransaksi . "','" . $ket[$termin][$tanggal][$bapp] . "','" . $kodeorgspk[$notransaksi] . "','" . $tanggal . "')\"></td>";
								} else {
									$tab .= "<td></td>";
								}

								$tab .= "<td " . $style . " align=center><img src='images/skyblue/zoom.png' class='resicon' height='30' title='View' onclick=\"view('" . $nopengajuan[$termin][$tanggal][$bapp] . "','" . $notransaksi . "','" . $kodeorgspk[$notransaksi] . "','" . $tanggal . "','" . $termin . "','" . $no . "',event,'html','" . $ket[$termin][$tanggal][$bapp] . "');\"></td>";


								$tab .= "<td " . $style . " align=center><img src='images/upload-2-xxl.png' class='resicon' height='30' title='Upload' onclick=\"UploadFile('" . $notransaksi . "','" . $tanggal . "','" . $termin . "','" . $no . "',event,'" . $nopengajuan[$termin][$tanggal][$bapp] . "');\"></td>";
							}

							@$totalbapp += $real[$termin][$tanggal][$bapp];

							$tab .= "</tr>";
						}
					}
				}

				$tab .= "<tr class=rowcontent>
							<td align=center colspan=4>T O T A L</td>
							<td align=right>" . number_format($totalbapp,2) . "</td>
							<td align=center colspan=" . $colspan . "></td>
						</tr>";
			}
			$tab .= "</table>
					</td>
				</tr>
			</table>";
		}

		if ($tipeview == 'viewhtml') {
			echo $tab;
		} else {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("Realisasi BAPP", array("Attachment" => 0));
		}
		break;
	case 'getdetailjurnal':
		$param = $_POST;

		$sql = "select * from " . $dbname . ".keu_jurnaldt_vw a
		where trim(a.noreferensi)='" . $param['notransaksi'] . "' and trim(a.nodok)='" . $param['nobapp'] . "' order by a.nojurnal, a.nourut";
		$res = fetchData($sql);
		$str = $owlPDO->query($sql);
		$str->setFetchMode(PDO::FETCH_OBJ);

		$tab .= "<table id=pvtTable cellpadding=5 cellspacing=1 border=0 class='sortable nowrap' width='100%'>
	 			<thead>
					<tr>
						
						<th align=center >" . $_SESSION['lang']['nojurnal'] . "</th>
						<th align=center >" . $_SESSION['lang']['tanggal'] . "</th>
						<th align=center >" . $_SESSION['lang']['unit'] . "</th>
						<th align=center >" . $_SESSION['lang']['noakun'] . "</th>
						<th align=center >" . $_SESSION['lang']['namaakun'] . "</th>
						<th align=center >" . $_SESSION['lang']['keterangan'] . "</th>
						<th align=center >" . $_SESSION['lang']['debet'] . "</th>
						<th align=center >" . $_SESSION['lang']['kredit'] . "</th>
						<th align=center >" . $_SESSION['lang']['noreferensi'] . "</th>    
						<th align=center >" . $_SESSION['lang']['nodok'] . "</th>    
						<th align=center >" . $_SESSION['lang']['kodeblok'] . "</th>
						<th align=center >" . $_SESSION['lang']['tahuntanam'] . "</th>
						<th align=center >" . $_SESSION['lang']['kodekegiatan'] . "</th>
						<th align=center >" . $_SESSION['lang']['namakegiatan'] . "</th>
					</tr>
					
				</thead>
				<tbody>";


		$tdebet = $tkredit = 0;
		$adark = 0;
		while ($bar = $str->fetch()) {
			if ($bar->kodejurnal == 'SPK1') {
				$no += 1;
				$debet = 0;
				$kredit = 0;
				if ($bar->jumlah > 0)
					$debet = $bar->jumlah;
				else
					$kredit = $bar->jumlah * -1;

				$tab .= "<tr class=rowcontent>
						<td>" . $bar->nojurnal . "</td>
						<td >" . tanggalnormal($bar->tanggal) . "</td>
						<td align=center >" . $bar->kodeorg . "</td>
						<td>" . $bar->noakun . "</td>
						<td>" . getNamaAkun($bar->noakun) . "</td>
						<td>" . $bar->keterangan . "</td>
						<td align=right  >" . number_format($debet, 2) . "</td>
						<td align=right  >" . number_format($kredit, 2) . "</td>
						<td align=center>" . $bar->noreferensi . "</td>    
						<td align=center>" . $bar->nodok . "</td>    
						<td align=center>" . getNamaOrg($bar->kodeblok) . "</td>
						<td align=center>" . getBlok($bar->kodeblok, 'tahuntanam') . "</td>
						<td align=center>" . $bar->kodekegiatan . "</td>
						<td align=center>" . getNamaKeg($bar->kodekegiatan) . "</td>
						</tr>";
				$tdebet += $debet;
				$tkredit += $kredit;
			} else {
				$adark += 1;
			}
		}
		$tab .= "<tr class=rowcontent>
					<td colspan=6 align=center>T O T A L</td>
					<td align=right>" . number_format($tdebet) . "</td>
					<td align=right>" . number_format($tkredit) . "</td>
					<td colspan=6></td>
					";
		$tab .= "</tr>";
		$tab .= "</tbody>";
		$tab .= "</table>";
		if ($adark > 0) {
			$tab .= "<br><br><table id=pvtTable cellpadding=5 cellspacing=1 border=0 class='sortable nowrap' width='100%'>
					 <thead>
						<tr>
							
							<th align=center >" . $_SESSION['lang']['nojurnal'] . "</th>
							<th align=center >" . $_SESSION['lang']['tanggal'] . "</th>
							<th align=center >" . $_SESSION['lang']['unit'] . "</th>
							<th align=center >" . $_SESSION['lang']['noakun'] . "</th>
							<th align=center >" . $_SESSION['lang']['namaakun'] . "</th>
							<th align=center >" . $_SESSION['lang']['keterangan'] . "</th>
							<th align=center >" . $_SESSION['lang']['debet'] . "</th>
							<th align=center >" . $_SESSION['lang']['kredit'] . "</th>
							<th align=center >" . $_SESSION['lang']['noreferensi'] . "</th>    
							<th align=center >" . $_SESSION['lang']['nodok'] . "</th>    
							<th align=center >" . $_SESSION['lang']['kodeblok'] . "</th>
							<th align=center >" . $_SESSION['lang']['tahuntanam'] . "</th>
							<th align=center >" . $_SESSION['lang']['kodekegiatan'] . "</th>
							<th align=center >" . $_SESSION['lang']['namakegiatan'] . "</th>
						</tr>
						
					</thead>
					<tbody>";


			$tdebetm = $tkreditm = 0;
			foreach ($res as $barm) {
				if ($barm['kodejurnal'] == 'M') {
					$no += 1;
					$debetm = 0;
					$kreditm = 0;
					if ($barm['jumlah'] > 0)
						$debetm = $barm['jumlah'];
					else
						$kreditm = $barm['jumlah'] * -1;

					$tab .= "<tr class=rowcontent>
							<td>" . $barm['nojurnal'] . "</td>
							<td >" . tanggalnormal($barm['tanggal']) . "</td>
							<td align=center >" . $barm['kodeorg'] . "</td>
							<td>" . $barm['noakun'] . "</td>
							<td>" . getNamaAkun($barm['noakun']) . "</td>
							<td>" . $barm['keterangan'] . "</td>
							<td align=right  >" . number_format($debetm, 2) . "</td>
							<td align=right  >" . number_format($kreditm, 2) . "</td>
							<td align=center>" . $barm['noreferensi'] . "</td>    
							<td align=center>" . $barm['nodok'] . "</td>    
							<td align=center>" . getNamaOrg($barm['kodeblok']) . "</td>
							<td align=center>" . getBlok($barm['kodeblok'], 'tahuntanam') . "</td>
							<td align=center>" . $barm['kodekegiatan'] . "</td>
							<td align=center>" . getNamaKeg($barm['kodekegiatan']) . "</td>
							</tr>";
					$tdebetm += $debetm;
					$tkreditm += $kreditm;
				}
			}
			$tab .= "<tr class=rowcontent>
						<td colspan=6 align=center>T O T A L</td>
						<td align=right>" . number_format($tdebetm) . "</td>
						<td align=right>" . number_format($tkreditm) . "</td>
						<td colspan=6></td>
						";
			$tab .= "</tr>";
			$tab .= "</tbody>";
			$tab .= "</table>";
		}
		echo $tab;
		break;
	case 'formpostingDataAll':
		$tab = "<table border=0 class=sortable cellpadding='5' cellspacing='1' style='width:100%'>
			<thead><tr class=rowheader>
			<td align=center width=20px>No</td>
			<td align=center>Termin</td>
			<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
			<td align=center>No BAPP</td>
			<td align=center>" . $_SESSION['lang']['blok'] . "</td>
			<td align=center>" . $_SESSION['lang']['kegiatan'] . "</td>
			<td align=center>Hari Kerja</td>
			<td align=center>Hasil Kerja</td>
			<td align=center>" . $_SESSION['lang']['jumlah'] . " (Rp)</td>
			";
		$tab .= "</tr>
		</thead>";

		$optkodeorg = makeOption($dbname, 'log_spkht', 'notransaksi,kodeorg', "notransaksi = '" . $notransaksi . "'");
		$optkoderek = makeOption($dbname, 'log_spkht', 'notransaksi,koderekanan', "notransaksi = '" . $notransaksi . "'");
		$optjenispk = makeOption($dbname, 'lgl_pengajuanspkht', 'notransaksi,jenis', "notransaksi = '" . $notransaksi . "'");
		$where = "notransaksi='" . $notransaksi . "' and nopengajuan='" . $nopengajuan . "' and termin='" . $terminx . "' and tanggal='" . $tanggalx . "' and statusjurnal='0' and statuspengajuan='1'";
		$where .= " and keterangan='" . $nobapp . "'";
		$jumlahblok = $nomor = 0;
		if ($optjenispk[$notransaksi] == 'ANGKUTTBS') {
			//ambil dari spb
			$str = "SELECT kgwbnetto,blok,nospb,indukblok FROM $dbname.kebun_spbdt_detail WHERE nospb IN (SELECT keterangan2 FROM $dbname.log_baspkdt WHERE keterangan='$nobapp') ";
			$res = fetchData($str);
			foreach ($res as $val) {
				$blokkecil[$val['indukblok']][$val['blok']] = $val['blok'];
				$ttlblokkecil[$val['indukblok']][$val['blok']] += $val['kgwbnetto'];
				$ttlblokbesar[$val['indukblok']] += $val['kgwbnetto'];
				$jumlahblok++;
			}
		}

		// if($optjenispk[$notransaksi] == 'ANGKUTTBS' || $optjenispk[$notransaksi] == 'SEWA.HM' ){
		// 	$str = "select notransaksi, kodeblok, kodekegiatan, tanggal, hasilkerjarealisasi, hkrealisasi, jumlahrealisasi, jjgkontanan, posting, statusjurnal, statuspengajuan, nopengajuan, blokspkdt, kodesegment, termin, keterangan, jenis from ".$dbname.".log_baspk where ".$where." group by notransaksi, kodeblok, kodekegiatan, tanggal order by termin asc";
		// 	// echo $str;

		// 	$res = fetchdata($str);
		// 	$no=0;$ttl='';
		// 	foreach($res as $val){
		// 		$arrtermin[$val['kodeblok']]=$val['termin'];
		// 		$arrtgl[$val['kodeblok']]=$val['tanggal'];
		// 		$arrkeg[$val['kodeblok']]=$val['kodekegiatan'];
		// 		$arrhk[$val['kodeblok']]=$val['hkrealisasi'];
		// 		$hasilkerjaindk[$val['kodeblok']]=$val['hasilkerjarealisasi'];
		// 		$jumlahrealisasiindk[$val['kodeblok']]=$val['jumlahrealisasi'];
		// 	}

		// 		foreach ($blokkecil as $indk => $arrkcl) {
		// 			foreach ($arrkcl as $kcl) {
		// 				$no++;
		// 				if($no<$jumlahblok){
		// 					$nilaihkproporsi[$indk][$kcl]=$ttlblokkecil[$indk][$kcl]/$ttlblokbesar[$indk]*$hasilkerjaindk[$indk];
		// 					$temphktotal[$indk][$kcl]+=$nilaihkproporsinilaihkproporsi[$indk][$kcl];
		// 					$nilairpproporsi[$indk][$kcl]=$ttlblokkecil[$indk][$kcl]/$ttlblokbesar[$indk]*$jumlahrealisasiindk[$indk];
		// 					$temprptotal[$indk][$kcl]+=$nilairpproporsinilaihkproporsi[$indk][$kcl];
		// 				}else{
		// 					$nilaihkproporsi[$indk][$kcl]=$hasilkerjaindk[$indk]-$temphktotal[$indk][$kcl];
		// 					$nilairpproporsi[$indk][$kcl]=$jumlahrealisasiindk[$indk]-$temprptotal[$indk][$kcl];
		// 				}
		// 				$tab.="<tr class=rowcontent id=tr_".$no.">";
		// 				$tab.="<td hidden>
		// 						<input id=notrpost".$no." value='".$notransaksi."'>
		// 						<input id=kdorgpost".$no." value=".$optkodeorg[$notransaksi].">
		// 						<input id=kdrekpost".$no." value=".$optkoderek[$notransaksi].">
		// 						</td>";
		// 				$tab.="<td align=center>" . $no . "</td>";
		// 				$tab.="<td align=center id=termin".$no.">".$arrtermin[$indk]."</td>";
		// 				$tab.="<td align=center id=tglpost".$no.">".tanggalnormal($arrtgl[$indk])."</td>";
		// 				$tab.="<td align=left id=nobapppost".$no.">".$nobapp."</td>";
		// 				$tab.="<td align=left hidden id=blokpost".$no.">".$kcl."</td>";
		// 				$tab.="<td align=left>".($kcl != '' ? " [".$kcl."]". getNamaOrg($kcl) : getNamaOrg($kcl))."</td>";
		// 				$tab.="<td align=left hidden id=kegpost".$no.">".$arrkeg[$indk]."</td>";
		// 				$tab.="<td align=left >".$arrkeg[$indk]." - ".getNamaKeg($arrkeg[$indk])."</td>";
		// 				$tab.="<td align=right id=hslkerjapost".$no.">".number_format($arrhk[$indk],2)."</td>";
		// 				$tab.="<td align=right id=hslkerjapost".$no.">".number_format($nilaihkproporsi[$indk][$kcl],2)."</td>";
		// 				$tab.="<td align=right  id=realpost".$no.">".number_format($nilairpproporsi[$indk][$kcl],2)."</td>";
		// 				$tab.="</tr>";
		// 				$ttl +=$nilairpproporsi[$indk][$kcl];
		// 			}
		// 		}
		// 		$tab.="<tr class=rowcontent>";
		// 		$tab.="<td align=center colspan=8 style='background-color:#ccc'><b>".strtoupper($_SESSION['lang']['total'])."</b></td>";
		// 		$tab.="<td align=right style='background-color:#ccc'><b>".number_format($ttl,2)."<b></td>";
		// 		$tab.="</tr>";
		// }else{
		$str = "select notransaksi, kodeblok, kodekegiatan, tanggal, sum(hasilkerjarealisasi) as hasilkerjarealisasi, sum(hkrealisasi) as hkrealisasi, sum(jumlahrealisasi) as jumlahrealisasi, sum(jjgkontanan) as jjgkontanan, posting, statusjurnal, statuspengajuan, nopengajuan, blokspkdt, kodesegment, termin, keterangan, jenis from " . $dbname . ".log_baspk where " . $where . " group by notransaksi, kodeblok, kodekegiatan, tanggal order by termin asc";
		// echo $str;

		$res = fetchdata($str);
		$no = 0;
		$ttl = '';
		foreach ($res as $val) {
			$no += 1;
			$optAct = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan = '" . $val['kodekegiatan'] . "'");
			$optActv = makeOption($dbname, 'vhc_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan = '" . $val['kodekegiatan'] . "'");

			$tab .= "<tr class=rowcontent id=tr_" . $no . ">";
			$tab .= "<td hidden>
						<input id=notrpost" . $no . " value='" . $notransaksi . "'>
						<input id=kdorgpost" . $no . " value=" . $optkodeorg[$notransaksi] . ">
						<input id=kdrekpost" . $no . " value=" . $optkoderek[$notransaksi] . ">
						</td>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=center id=termin" . $no . ">" . $val['termin'] . "</td>";
			$tab .= "<td align=center id=tglpost" . $no . ">" . tanggalnormal($val['tanggal']) . "</td>";
			$tab .= "<td align=left id=nobapppost" . $no . ">" . $val['keterangan'] . "</td>";
			$tab .= "<td align=left hidden id=blokpost" . $no . ">" . $val['kodeblok'] . "</td>";
			$tab .= "<td align=center>" . (getIndukBlok($val['kodeblok']) != '' ? getIndukBlok($val['kodeblok']) . " [" . getNamaOrg($val['kodeblok']) . "]" : getNamaOrg($val['kodeblok'])) . "</td>";
			$tab .= "<td align=left hidden id=kegpost" . $no . ">" . $val['kodekegiatan'] . "</td>";
			$tab .= "<td align=left >" . ($optAct[$val['kodekegiatan']] == '' ? $optActv[$val['kodekegiatan']] : $optAct[$val['kodekegiatan']]) . "</td>";
			$tab .= "<td align=right id=hkpost" . $no . ">" . number_format($val['hkrealisasi']) . "</td>";
			$tab .= "<td align=right id=hslkerjapost" . $no . ">" . number_format($val['hasilkerjarealisasi']) . "</td>";
			$tab .= "<td align=right  id=realpost" . $no . ">" . number_format($val['jumlahrealisasi'], 2) . "</td>";
			$tab .= "</tr>";
			@$ttl += $val['jumlahrealisasi'];
		}
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=center colspan=8 style='background-color:#ccc'><b>" . strtoupper($_SESSION['lang']['total']) . "</b></td>";
		$tab .= "<td align=right style='background-color:#ccc'><b>" . number_format($ttl) . "<b></td>";
		$tab .= "</tr>";
		// }

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=center colspan=9>";
		$tab .= "<button onclick=\"postingDataAll('" . $no . "')\" class='mybutton' id='Posting All'>Posting All</button>";
		$tab .= "</td>";
		$tab .= "</tr>";

		echo $tab;
		break;
	case 'getapprovaldetail':
		// $tab="";
		$notransaksi = checkPostGet('nopengajuan', '');
		// $nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		// $arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);

		// $tab.="<span><b>Approval</b></span>";
		// $tab.="<table  border=0 cellspacing=1 cellpadding=5 class=sortable>";
		// $countApprove = getCountApproval('BAPP',$kodeorg);
		// $tab.= "<thead>
		// <tr style='font-weight:bold'>";
		// for($i=1;$i<=$countApprove;$i++){
		// $tab.= "<th style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</th>";
		// }

		// $tab.= "</tr></thead><tbody>";
		// $tab.= "<tr class=rowcontent>";

		// for($i=1;$i<=$countApprove;$i++){
		// $arrApp = detailApprove($i,$notransaksi,'BAPP');
		// if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
		// $tngl='';
		// }else{
		// $tngl=tanggalnormal($arrApp['tanggal']);
		// }

		// if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
		// $tab.= "<td valign=top style='text-align:center'>".$arrApp['nama']."
		// <br>".$arrHsl[$arrApp['status']]."
		// <br>".$tngl."
		// <br>".$arrApp['komentar']."
		// </td>";
		// }else{
		// $tab.= "<td>&nbsp;</td>";
		// }
		// }
		// $tab.= "</tbody></table>";


		// #status tolak
		// $str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$notransaksi."' group by keterangan";
		// $res=fetchdata($str);
		// $row=count($res);
		// if($row>0){
		// $no=0;
		// foreach($res as $key=>$val){
		// $no++;
		// $tab.="<br><table border=0 cellspacing=1 class=sortable>
		// <thead>
		// <tr style='font-weight:bold'>
		// <td colspan='".($val['level'])."'>Return / Tolak - ".$no."</td>
		// </tr>
		// <tr style='font-weight:bold'>";
		// for($i=1;$i<=$val['level'];$i++) {
		// $tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
		// }
		// $tab.="</tr>
		// </thead>
		// <tbody>
		// <tr class=rowcontent>";
		// for($i=1;$i<=$val['level'];$i++) {
		// $strx="select * from ".$dbname.".approval_return where notransaksi='".$notransaksi."' and level='".$i."' and keterangan='".$val['keterangan']."'";
		// $resx=fetchdata($strx);
		// $color='';
		// if($resx[0]['status']==3){
		// $color=" style=background-color:red ";
		// }
		// $tab.="<td ".$color.">".$nmkar[$resx[0]['karyawanid']]."
		// <br>	
		// ".$arrHsl[$resx[0]['status']]."
		// <br>	
		// ".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
		// <br>	
		// ".$resx[0]['komentar']."
		// </td>";
		// }
		// $tab.="</tr>
		// </tbody>
		// </table>";
		// }
		// }
		//echo $tab;
		echo gethistoryapp($notransaksi);
		break;

	case 'preview':
		$param = $_POST;
		$theme = $_SESSION['theme'];
		if ($theme == 'skyblue' || $theme == '') {
			$gen = 'generic.css';
		} else if ($theme == 'red') {
			$gen = 'genericRed.css';
		} else {
			$gen = 'genericGray.css';
		}
		echo "<link rel=stylesheet type='text/css' href='style/" . $gen . "'>";

		if ($param['sumber'] == 'popupdet') {
			$param['baspk'] = $param['notransaksi'];
			$str = "select * from " . $dbname . ".log_baspk where keterangan='" . $param['notransaksi'] . "' and kodeblok='" . $param['kodeblok'] . "' and tanggal='" . $param['tanggal'] . "' and kodekegiatan='" . $param['kodekegiatan'] . "'";
			$req = fetchdata($str);
			foreach ($req as $val) {
				$param['termin'] = $val['termin'];
				$param['nopengajuan'] = $val['nopengajuan'];
				$param['notransaksi'] = $val['notransaksi'];
			}
		}

		$koderek = makeOption($dbname, 'log_spkht', 'notransaksi,koderekanan', "notransaksi='" . $param['notransaksi'] . "'");
		$tipeSPK = makeOption($dbname, 'lgl_pengajuanspkht', 'notransaksi,jenis', "notransaksi='" . $param['notransaksi'] . "'");
		$nmpek = makeOption($dbname, 'log_spkht', 'notransaksi,keterangan', "notransaksi='" . $param['notransaksi'] . "'");
		$nmsupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $koderek[$param['notransaksi']] . "'");
		$str = "select sum(jumlahrp) as jumlahrp from " . $dbname . ".log_spkdt where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchData($str);
		$click = '';

		if (@$param['sumber'] == 'approval') {
			$kodeorgspk = makeOption($dbname, 'log_spkht', 'notransaksi,kodeorg', "notransaksi='" . $param['notransaksi'] . "'");
			$click = " style=cursor:pointer; onclick=\"viewdetailbapp('" . $param['notransaksi'] . "','" . $kodeorgspk[$param['notransaksi']] . "','','" . $param['nopengajuan'] . "');\"";
		}




		$tab = "<table border=0 cellpadding=5 cellspacing=1 class=sortable>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>No Pengajuan</td><td>:</td><td>" . $param['nopengajuan'] . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>No SPK</td><td>:</td><td " . $click . "><font color=blue>" . $param['notransaksi'] . "</font></td>";
		$tab .= "</tr>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>Pekerjaan</td><td>:</td><td>" . $nmpek[$param['notransaksi']] . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>Kode Rekanan</td><td>:</td><td>" . $nmsupp[$koderek[$param['notransaksi']]] . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td>Nilai SPK</td><td>:</td><td align=left>" . number_format($res[0]['jumlahrp']) . "</td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$tab .= "<div style=clear:both><br></div>";

		$tab .= "<table border=0 cellpadding=5 cellspacing=1 class=sortable>
			<thead><tr class=rowheader>";
		$tab .= "<th align=center width=20px>No</th>
				<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
				<th align=center>" . $_SESSION['lang']['kegiatan'] . "</th>
				<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
				<th align=center>" . $_SESSION['lang']['blok'] . "</th>
				<th align=center>Real Hasil Kerja</th>
				<th align=center>Harga Rp</th>
				<th align=center>" . $_SESSION['lang']['jumlah'] . " (Rp)</th>
				<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
			</tr>
			</thead>
			";

		if($tipeSPK[$param['notransaksi']] == 'SEWA.HM'){
			$str = "select * from " . $dbname . ".vhc_kegiatan";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$nmkeg[$bar['kodekegiatan']] = $bar['namakegiatan'];
				$nmsat[$bar['kodekegiatan']] = $bar['satuan'];
			}
		}else{
			$str = "select * from " . $dbname . ".setup_kegiatan";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$nmkeg[$bar['kodekegiatan']] = $bar['namakegiatan'];
				$nmsat[$bar['kodekegiatan']] = $bar['satuan'];
			}
		}

		$str = "select * from " . $dbname . ".project_dt";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nmkeg[$bar['kegiatan']] = $bar['namakegiatan'];
			$nmsat[$bar['kegiatan']] = $bar['satuan'];
		}
		$str = "select * from " . $dbname . ".organisasi";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nmblok[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
		}
		$str = "select * from " . $dbname . ".project";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nmblok[$bar['kode']] = $bar['nama'];
		}
		$str = "select * from " . $dbname . ".vhc_5master";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($bar['nopol'] != '') {
				$bar['nama'] = $bar['nopol'];
			} else {
				$bar['nama'] = $bar['detailvhc'];
			}
			$nmblok[$bar['kodevhc']] = $bar['nama'];
		}

		if ($param['sumber'] == 'popupdet') {
			$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $param['notransaksi'] . "' and nopengajuan='" . $param['nopengajuan'] . "' and kodeblok='" . $param['kodeblok'] . "' and tanggal='" . $param['tanggal'] . "' and kodekegiatan='" . $param['kodekegiatan'] . "' and keterangan='" . $param['baspk'] . "'";
		} else {
			$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $param['notransaksi'] . "' and nopengajuan='" . $param['nopengajuan'] . "' and termin='" . $terminx . "' and keterangan ='" . $nobaspk . "'order by tanggal desc";
		}

		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = '';
		while ($bar = $res->fetch()) {
			$no++;
			$strx = "select * from " . $dbname . ".log_baspkdt where notransaksi='" . $bar['notransaksi'] . "' and kodeblok='" . $bar['kodeblok'] . "' and kodekegiatan='" . $bar['kodekegiatan'] . "' and termin='" . $bar['termin'] . "' and keterangan='" . $bar['keterangan'] . "' and jenis='" . $bar['jenis'] . "'";
			$resx = fetchdata($strx);
			$nox = 0;

			$tab .= "<tr class=rowcontent style='font-weight:bold;cursor:pointer' onclick=\"showhidedetail('" . $no . "','" . count($resx) . "')\">";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td style='min-width:80px;text-align:center'>" . ($bar['tanggal'] == '0000-00-00' ? '' : tanggalnormal($bar['tanggal'])) . "</td>";
			$tab .= "<td>" . $bar['kodekegiatan'] . "-" . @$nmkeg[$bar['kodekegiatan']] . "</td>";
			$tab .= "<td>" . @$nmsat[$bar['kodekegiatan']] . "</td>";
			$tab .= "<td>" . @$nmblok[$bar['kodeblok']] . "</td>";
			$tab .= "<td align=right>" . number_format($bar['hasilkerjarealisasi']) . "</td>";
			$tab .= "<td align=right>" . @number_format($bar['jumlahrealisasi'] / $bar['hasilkerjarealisasi'], 1) . "</td>";
			$tab .= "<td align=right>" . number_format($bar['jumlahrealisasi']) . "</td>";
			$tab .= "<td>" . $bar['keterangan'] . "</td>";
			$tab .= "</tr>";
			@$ttl += $bar['jumlahrealisasi'];

			foreach ($resx as $valx) {
				$nox++;
				$tab .= "<tr class=rowcontent style='background-color:#50edd2;display:none' id='tr_dt2_" . $no . "_" . $nox . "'>
						<td></td>
						<td style='min-width:80px;text-align:center'>" . tanggalnormal($valx['tanggal']) . "</td>
						<td>" . @$nmkeg[$bar['kodekegiatan']] . "</td>
						<td>" . @$nmsat[$bar['kodekegiatan']] . "</td>
						<td>" . @$nmblok[$bar['kodeblok']] . "</td>
						<td align=right>" . number_format($valx['hasilkerjarealisasi']) . "</td>
						<td align=right>" . number_format($valx['jumlahrealisasi'] / $valx['hasilkerjarealisasi']) . "</td>
						<td align=right>" . number_format($valx['jumlahrealisasi']) . "</td>
						<td>" . $valx['keterangan2'] . "</td>
					</tr>";
			}
		}
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=center colspan=7>T O T A L</td>";
		$tab .= "<td align=right>" . number_format($ttl) . "</td>";
		$tab .= "<td></td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$tab .= "<div style=clear:both><br></div>";
		$tab .= "<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
					<thead>
					<tr class=rowheader>
						<th align='center' width=30px>No.</th>
						<th align='center' width=50px>File Type</th>
						<th align='center' width=30px>Termin</th>
						<th align='center'>Kriteria</th>
						<th align='center'>Filename</th>
						<th align='center' width=50px>Action</th>
					</tr>
					</thead>
					<tbody>";
		$where = '';
		if ($param['termin'] != 'undefined') {
			$where = " and (termin='" . $param['termin'] . "' or termin='')";
		}
		$path               = "fileupload/lgl_pengajuanspk/";
		$nopengajuan = makeOption($dbname, 'log_spkht', 'notransaksi,nopengajuan', "notransaksi='" . $param['notransaksi'] . "'");
		$str = "select * from " . $dbname . ".listfile_lgl_pengajuanspk where notransaksi = '" . $nopengajuan[$param['notransaksi']] . "' and status='1' " . $where . ""; #exit("error".$str);
		$res = fetchData($str);
		if (empty($res)) {
			$tab .= "<tr class=rowcontent><td colspan=6 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			$no = '';
			foreach ($res as $key => $val) {
				$no++;
				$tab .= "<tr class=rowcontent>
									<td style='text-align:center'>" . $no . "</td>";
				$icon = seticonfile($val['formaticon']);
				$tab .= "<td style='text-align:center'>
									<a href='" . $path . $val['namafile'] . "' download><img src=" . $icon . " class=resicon></a>
								</td>";
				$nfile = '';
				if (strlen($val['namafile']) > 10) {
					$nfile = $val['namafile'];
				} else {
					$nfile = $val['namafile'];
				}
				$tab .= "<td style='text-align:center'>" . ($val['termin']) . "</td>
									<td style='text-align:left'>" . getcriterianame($val['kriteriaefil']) . "</td>
							<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','" . $val['namafile'] . "')\">" . $nfile . "</td>
								<td align=center>
									<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon	 title='download'></a>";
				$tab . "	</td>
								</tr>";
			}
		}
		$tab .= "</tbody>
				</table>
			
			
			
			";

		echo $tab;
		break;

	case 'UploadFile':
		$arrmodul = getmodulefil($emodul);
		foreach ($arrmodul as $key => $val) {
			$optkriteria .= "<option value='" . $key . "'>" . $val['kriteria'] . "</option>";
		}

		$nopengajuan = makeOption($dbname, 'log_spkht', 'notransaksi,nopengajuan', "notransaksi='" . $notransaksi . "'");
		$tab = "";
		$tab .= "<fieldset style=width:96%><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>No SPK</td>
				<td>:</td>
				<td id=notransaksi>" . $notransaksi . "</td>
			</tr>
			<tr>
				<td>No Pengajuan SPK</td>
				<td>:</td>
				<td id=pengajuanspk>" . $nopengajuan[$notransaksi] . "</td>
			</tr>
			<tr style=display:none>
				<td>Tanggal BAPP</td>
				<td>:</td>
				<td id=tanggal>" . $tanggal . "</td>
			</tr>
			<tr>
				<td>Termin</td>
				<td>:</td>
				<td id=terminup>" . $terminx . "</td>
			</tr>
			<tr>
				<td>Kriteria</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>" . $optkriteria . "</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>";

		$tab .= "<fieldset>
			<legend>" . $_SESSION['lang']['list'] . "</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center' width=30px>Termin</td>
					<td align='center'>Kriteria</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";

		echo $tab;
		break;

	case 'loadfiles':
		$no = 0;
		$tab = "";
		$where = '';
		if ($terminx != 'undefined') {
			$where = " and (termin='" . $terminx . "' or termin='')";
		}
		$nopengajuan = makeOption($dbname, 'log_spkht', 'notransaksi,nopengajuan', "notransaksi='" . $notransaksi . "'");
		$stssetuju = makeOption($dbname, 'log_baspk', 'nopengajuan,statuspengajuan', "nopengajuan='" . $nopengajuanx . "'");
		$str = "select * from " . $dbname . ".listfile_lgl_pengajuanspk where notransaksi = '" . $nopengajuan[$notransaksi] . "' and status='1' " . $where . ""; #exit("error".$str);
		$res = fetchData($str);
		if (empty($res)) {
			$tab .= "<tr class=rowcontent><td colspan=6 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			foreach ($res as $key => $val) {
				$no++;
				$tab .= "<tr class=rowcontent>
						<td style='text-align:center'>" . $no . "</td>";
				$icon = seticonfile($val['formaticon']);
				$tab .= "<td style='text-align:center'>
						<a href='" . $path . $val['namafile'] . "' download><img src=" . $icon . " class=resicon></a>
					</td>";
				$nfile = '';
				/* if(strlen($val['namafile'])>10){
					$nfile = potongtext($val['namafile'],10).$val['formaticon'];
				}else{
					$nfile = $val['namafile'];
				} */
				$nfile = $val['namafile'];
				$tab .= "<td style='text-align:center'>" . ($val['termin']) . "</td>
						<td style='text-align:left'>" . getcriterianame($val['kriteriaefil']) . "</td>
				<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','" . $val['namafile'] . "')\">" . $nfile . "</td>
					<td align=center>
						<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon	 title='download'></a>&nbsp";
				$tgl = date("Y-m-d");

				if ($val['createdby'] == $_SESSION['standard']['userid'] and substr($val['createdtime'], 0, 10) == $tgl) {
					$tab .= "<img src=images/application/application_delete.png class=resicon	 title='Delete' onclick=\"deletefile('" . $val['notransaksi'] . "','" . $val['namafile'] . "','" . $val['termin'] . "','" . $nopengajuanx . "');\" >";
				}
				if ($val['createdby'] == $_SESSION['standard']['userid'] and $stssetuju[$nopengajuanx] == 3 and $val['termin'] == $terminx) {
					$tab .= "<img src=images/application/application_delete.png class=resicon	 title='Delete' onclick=\"deletefile('" . $val['notransaksi'] . "','" . $val['namafile'] . "','" . $val['termin'] . "','" . $nopengajuanx . "');\" >";
				}

				$tab . "	</td>
					</tr>";
			}
		}
		echo $tab;
		break;
	case 'viewfile':
		$tab = "";
		$tab .= "<img src='" . $path . $namafile . "' style='width:600px;height:400px;'>";
		echo $tab;
		break;

	case 'submitfile':
		if ($notransaksi == '' || $pengajuanspk == '') {
			exit("Warning : Nomor transaksi dan nomor pengajuan SPK di perlukan !");
		}
		$tgl = date("YmdHis");
		$his = date("His");
		$data = $_POST;
		if ($data['fileupload'] != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$filename = $_FILES['file']['name'];
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
					/*if($_FILES['file']['size'] <= 250000){*/
					$str = "select * from " . $dbname . ".listfile_lgl_pengajuanspk where notransaksi = '" . $pengajuanspk . "' and status='1' and namafile='" . $filename . "'";
					$res = fetchData($str);
					if (count($res) > 0) {
						exit("Warning : Nama file sudah ada !!!");
					}
					$str = "insert into " . $dbname . ".listfile_lgl_pengajuanspk values ('','" . $pengajuanspk . "','" . $filename . "','" . $filetype . "','" . $kriteriaefil . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "','" . $terminx . "')"; #exit("error".$str);
					try {
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path . $filename, $file_tmpname);
					} catch (PDOException $e) {
						echo " Gagal," . addslashes($e->getMessage());
					}
					/*}else{
					exit("warning : Ukuran file upload maksimal 250kb");
				}*/
				} else {
					exit("Warning : Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
			}
		}
		break;

	case 'deletefile':
		$str = "delete from " . $dbname . ".listfile_lgl_pengajuanspk where notransaksi='" . $notransaksi . "' and namafile='" . $namafile . "'";
		try {
			$owlPDO->exec($str);
			$pathx = $path . $namafile;
			unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		break;

	case 'form_ajukan';
		$param = $_POST;
		$str = "select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from " . $dbname . ".setup_approval a 
		  left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where 
		  a.karyawanid!='" . $_SESSION['standard']['userid'] . "' and a.jenispersetujuan='BAPP' and a.level='1' and a.kodeunit='" . $param['kodeorg'] . "'  order by b.namakaryawan asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry = "";
		while ($rkry = $res->fetch()) {
			$optKry .= "<option value='" . $rkry['karyawanid'] . "'>" . $rkry['namakaryawan'] . " [" . $rkry['lokasitugas'] . "]</option>";
		}

		$strPos = "select * from " . $dbname . ".log_baspk where notransaksi='" . $param['notransaksi'] . "' and tanggal='" . $param['tanggal'] . "' and termin='" . $param['termin'] . "' and keterangan='" . $param['nobapp'] . "'";
		$res = $owlPDO->query($strPos) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($bar['nopengajuan'] != '') {
				$nopengajuan = $bar['nopengajuan'];
			} else {
				$nopengajuan = $param['kodeorg'] . date("Ymdhms");
			}
		}

		$str = "select * from " . $dbname . ".log_baspk where notransaksi!='" . $param['notransaksi'] . "' and tanggal!='" . $param['tanggal'] . "' and termin!='" . $param['termin'] . "' and keterangan!='" . $param['nobapp'] . "' and nopengajuan='" . $nopengajuan . "'";
		$res = fetchdata($str);
		if (count($res) > 0) {
			$nopengajuan = $param['kodeorg'] . date("Ymdhms");
		}

		if ($nopengajuan == '') {
			$nopengajuan = $param['kodeorg'] . date("Ymdhms");
		}


		$str = "select unit from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $param['notransaksi'] . "'";
		$res = fetchdata($str);
		$unit = $res[0]['unit'];

		$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>" . $param['notransaksi'] . "</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['termin'] . " Ke</td>
					<td width=5px>:</td>
					<td id=termin_aju>" . $param['termin'] . "</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>No Pengajuan</td>
					<td width=5px>:</td>
					<td id=nopengajuan_aju>" . $nopengajuan . "</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['tanggal'] . " Jurnal</td>
					<td width=5px>:</td>
					<td>
						<input type='hidden' id='unitdt2' value='" . $unit . "'>
						<input type='hidden' id='bappdt2' value='" . $param['nobapp'] . "'>
						<input id='tgljurnal' class='myinputtext' type='text' onmousemove='setCalendar(this.id)' readonly='readonly' style='width:80px'>
					</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='min-width:175px;'>" . $optKry . "</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=" . $param['numrow'] . "></td>
					<td align=left><button id=tomboldetail class=mybutton onclick=ajukan(event)>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";

		echo $tab;
		break;

	case 'ajukan':
		$param = $_POST;
		try {
			$owlPDO->beginTransaction();
			if ($param['kepada'] == '' or $param['notransaksi'] == '') {
				throw new PDOException('Isikan nama penyetuju.');
			}
			if ($param['nopengajuan'] == '') {
				throw new PDOException('No Pengajuan tidak boleh kosong.');
			}
			if ($param['tanggal'] == '') {
				throw new PDOException('Tanggal tidak boleh kosong.');
			}

			# cek dulu nomor pengajuannya karena sering sama
			$strcek = "select * from " . $dbname . ".log_baspk where nopengajuan='" . $param['nopengajuan'] . "'";
			$cekno = count(fetchData($strcek));
			if ($cekno > 0) {
				$param['nopengajuan'] = substr($param['nopengajuan'], 0, 4) . date("Ymdhms");
			}

			$strPos = "select * from " . $dbname . ".log_baspk where notransaksi='" . $param['notransaksi'] . "' and tanggal='" . tanggalsystem($param['tanggal']) . "' and termin='" . $param['termin'] . "' and posting='0'";
			$cekpost = count(fetchData($strPos));

			if ($cekpost > 0) {
				#throw new PDOException('Ada detail transaksi yang belum di posting.');
			}

			$str = "select max(nourut) as nourut from " . $dbname . ".approval_return where jenispersetujuan='BAPP' and notransaksi='" . $param['nopengajuan'] . "' limit 1";
			$res = fetchdata($str);
			if ($res[0]['nourut'] != '') {
				$urut = $res[0]['nourut'] + 1;
			} else {
				$urut = 1;
			}

			//cari dulu apakah sudah pernah di ajukan sebelumnya
			$tglhi = date("Ymd");
			$str = "select * from " . $dbname . ".approval where jenispersetujuan='BAPP' and notransaksi='" . $param['nopengajuan'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if ($bar['notransaksi'] != '') {
					# jika ada pindahkan ke table ini
					$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`, `nourut`)
					values ('" . $bar['notransaksi'] . "','" . $bar['jenispersetujuan'] . "','" . $bar['level'] . "','" . $bar['karyawanid'] . "','" . $bar['status'] . "','" . $bar['komentar'] . "','" . $tglhi . "','" . $bar['tanggal'] . "','" . $urut . "')";
					$owlPDO->exec($str);
				}
			}

			#kemudian setelah di pindah, hapus persetujuan lama
			$str = "delete from " . $dbname . ".approval where jenispersetujuan='BAPP' and notransaksi='" . $param['nopengajuan'] . "'";
			$owlPDO->exec($str);


			# update flag menjadi 1
			$str = "update " . $dbname . ".log_baspk set statuspengajuan='9', nopengajuan='" . $param['nopengajuan'] . "', tanggal='" . tanggalsystem($param['tanggal']) . "' where notransaksi='" . $param['notransaksi'] . "' and keterangan='" . $param['nobapp'] . "' and termin='" . $param['termin'] . "'";
			$owlPDO->exec($str);

			# insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('','" . $param['nopengajuan'] . "','BAPP','1','" . $param['kepada'] . "','0','','','')";
			$owlPDO->exec($str);
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		echo $param['nopengajuan'];
		break;
}

function formHeader($mode, $data)
{
	global $dbname;
	//print_r($data);
	//exit("Error");

	# Default Value
	if (empty($data)) {
		$data['kodeorg'] = '';
		$data['notransaksi'] = '0';
		$data['tanggal'] = '';
		$data['divisi'] = '';
		$data['koderekanan'] = '';
		$data['matauang'] = '';
	}

	# Disabled Primary
	if ($mode == 'edit') {
		$disabled = 'disabled';
	} else {
		$disabled = '';
	}

	# Options
	$whereOrg = "kodeorganisasi='" . $data['kodeorg'] . "'";
	$optOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', $whereOrg);
	$whereDiv = "kodeorganisasi='" . $data['divisi'] . "'";
	$optDiv = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', $whereDiv, '0', true);
	$optDiv[''] = 'Project';
	$optDiv['S'] = 'Perumahan';
	$optDiv['P'] = 'Pabrikasi';
	$optSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $data['koderekanan'] . "'");

	#khusus jika project
	if (substr($data['divisi'], 0, 2) == 'AK' or substr($data['divisi'], 0, 2) == 'PB') {
		$optDiv = makeOption($dbname, 'project', 'kode,nama', "kode='" . $data['divisi'] . "' and posting=0");
	}

	$els = array();
	$els[] = array(
		makeElement('kodeorg', 'label', $_SESSION['lang']['kebun']),
		makeElement(
			'kodeorg',
			'select',
			$data['kodeorg'],
			array('style' => 'width:150px', 'disabled' => 'disabled'),
			$optOrg
		)
	);
	$els[] = array(
		makeElement('notransaksi', 'label', $_SESSION['lang']['notransaksi']),
		makeElement(
			'notransaksi',
			'text',
			$data['notransaksi'],
			array('style' => 'width:150px', 'disabled' => 'disabled')
		)
	);
	$els[] = array(
		makeElement('tanggal', 'label', $_SESSION['lang']['tanggal']),
		makeElement('tanggal', 'text', $data['tanggal'], array(
			'style' => 'width:150px',
			'disabled' => 'disabled'
		))
	);
	$els[] = array(
		makeElement('divisi', 'label', $_SESSION['lang']['subunit']),
		makeElement(
			'divisi',
			'select',
			$data['divisi'],
			array('style' => 'width:150px', 'disabled' => 'disabled'),
			$optDiv
		)
	);
	$els[] = array(
		makeElement('koderekanan', 'label', $_SESSION['lang']['koderekanan']),
		makeElement(
			'koderekanan',
			'select',
			$data['koderekanan'],
			array('style' => 'width:150px', 'disabled' => 'disabled'),
			$optSup
		)
	);

	$els[] = array(
		makeElement('matauang', 'label', ''),
		makeElement(
			'matauang',
			'hidden',
			$data['matauang'],
			array('style' => 'width:150px', 'disabled' => 'disabled')
		)
	);

	return genElementMultiDim($_SESSION['lang']['header'], $els, 2);
}
