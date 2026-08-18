<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('dompdfv2/autoload.inc.php');

use Dompdf\Dompdf;

$method = checkPostGet('method', '');
$tipeprint = checkPostGet('tipeprint', '');

$intiplasma = checkPostGet('intiplasma', '');
$unit = checkPostGet('unit', '');
$indukblok = checkPostGet('indukblok', '');
$blokkecil = checkPostGet('blokkecil', '');
$kodeorgnya = checkPostGet('kodeorgnya', '');
$subunit = checkPostGet('subunit', '');
$periode = checkPostGet('periode', '');
$periode2 = checkPostGet('periode2', '');

$tanggal = checkPostGet('tanggal', '');
$nik = checkPostGet('nik', '');
$kodemesin = checkPostGet('kodemesin', '');
$tanggal = checkPostGet('tanggal', '');
$sStr = selectQuery($dbname, "setup_parameterappl", "nilai", "kodeaplikasi = 'TX' AND kodeparameter = 'BRGOPRBBM'");
$qStr = fetchData($sStr);
$dftrkodebarang = explode(',', $qStr[0]['nilai']);

$optsubunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optindukblok = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');

switch ($method) {
	case 'getUnit':
		## GET UNIT

		if($intiplasma == '0'){
			$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
			$unit='';
			$arrUnit = getOrgDetail(1);

			// Mengambil index (key) dari array
			$indexArray = array_keys($arrUnit);
			
			$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi in ('" . implode("','", $indexArray) . "') and inti=1 order by kodeorganisasi";
			$res = fetchdata($str);
			

			foreach($res as $val){
				$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$val['kodeorganisasi']."'");
				$d=$induk[$val['kodeorganisasi']];
				if($d!=$n){			
					$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
				}
				
				if($val['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
					$optUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
					$unit=$val['kodeorganisasi'];
				}else{
					$optUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
				}
				$n=$d;
				if($d!=$n){			
					$optUnit.="</optgroup>";
				}
			}
		}elseif($intiplasma == '1'){
			$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
			$unit='';
			$arrUnit = getOrgDetail(1);

			// Mengambil index (key) dari array
			$indexArray = array_keys($arrUnit);
			
			$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi in ('" . implode("','", $indexArray) . "') and inti=0 order by kodeorganisasi";
			$res = fetchdata($str);
			

			foreach($res as $val){
				$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$val['kodeorganisasi']."'");
				$d=$induk[$val['kodeorganisasi']];
				if($d!=$n){			
					$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
				}
				
				if($val['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
					$optUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";	
					$unit=$val['kodeorganisasi'];
				}else{
					$optUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
				}
				$n=$d;
				if($d!=$n){			
					$optUnit.="</optgroup>";
				}
			}
		}else{
			$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
			$unit='';
			$arrUnit = getOrgDetail(1);
			foreach($arrUnit as $key=>$val){
				$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
				$d=$induk[$key];
				if($d!=$n){			
					$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
				}
				
				if($key==$_SESSION['empl']['lokasitugas']){
					$optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";	
					$unit=$key;
				}else{
					$optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			
				}
				$n=$d;
				if($d!=$n){			
					$optUnit.="</optgroup>";
				}
			}
		}

		echo $optUnit;
		break;
	case 'getsubunit':
		$optSubUnit = "<option value='all'>" . $_SESSION['lang']['all'] . "</option>";
		$optSubUnit .= "<option value=''>" . $unit . " - Kantor</option>";
		$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $unit . "' order by kodeorganisasi";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$optSubUnit .= "<option value='" . $val['kodeorganisasi'] . "'>" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
		}

		echo $optSubUnit;
		break;
	case 'getBlokKecil':
		$optOPT = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$str = "select kodeorg from " . $dbname . ".setup_blok where indukblok = '" . $indukblok . "' group by kodeorg";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$optOPT .= "<option value='" . $val['kodeorg'] . "'>" . $optsubunit[$val['kodeorg']] . " - " . $val['kodeorg'] . "</option>";
		}

		echo $optOPT;
		break;
	case 'getIndukBlok':
		$optOPT = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$str = "select indukblok from " . $dbname . ".setup_blok where indukblok like '%" . $unit . "%' group by indukblok";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$optOPT .= "<option value='" . $val['indukblok'] . "'>" . $optindukblok[$val['indukblok']] . " - " . $val['indukblok'] . "</option>";
		}

		echo $optOPT;
		break;

		case 'preview':
			$tab = "";
	
			// Array untuk penyimpanan data utama
			$data = [];
		
			// Konversi periode ke format tanggal
			$tanggalmulai = substr($periode, 6, 4) . "-" . substr($periode, 3, 2) . "-" . substr($periode, 0, 2);
			$tanggalsampai = substr($periode2, 6, 4) . "-" . substr($periode2, 3, 2) . "-" . substr($periode2, 0, 2);
		
			// Kondisi tambahan
			$where = "";
			$where .= "  AND a.tanggalpanen BETWEEN '" . $tanggalmulai . "' AND '" . $tanggalsampai . "'";
			
			if($unit != ''){
				$where .= "  AND a.blok LIKE '" . $unit . "%' ";				
			}else{

				$where .= " AND substr(blok,1,4) in (".getOrgDetail(2).")";				

			}
			
			if($intiplasma != ''){
				if($intiplasma == '0'){
					$nilaiIP='I';
				}else{
					$nilaiIP='P';
				}
				$where .= " AND a.blok in (select kodeorg FROM " . $dbname . ".setup_blok where intiplasma = '".$nilaiIP."') ";				
			}

			if($indukblok != ''){
				$where .= "  AND a.indukblok = '" . $indukblok . "' ";				
			}
			if($blokkecil != ''){
				$where .= "  AND a.blok = '" . $blokkecil . "' ";				
			}
		
			// Query pengambilan data
			$str0 = "SELECT a.* FROM " . $dbname . ".`kebun_spbdt_detail` a left join setup_blok b on a.blok=b.kodeorg
					 WHERE 1=1 " . $where . " 
					 ORDER BY a.nospb";
					//  echo $str0;
					//  exit;
			$queryResult = fetchData($str0);
		
			// Masukkan data ke dalam array yang dikelompokkan
			foreach ($queryResult as $row) {
				$nospb = $row['nospb'];
				if (!isset($data[$nospb])) {
					$data[$nospb] = [
						'details' => [],
						'subtotal' => [
							'kgwb' => 0,
							'kgwbnetto' => 0,
							'brondolan' => 0,
							'totalkg' => 0,
						],
					];
				}
		
				// Tambahkan detail ke grup `nospb`
				$data[$nospb]['details'][] = $row;
		
				// Akumulasi subtotal
				$data[$nospb]['subtotal']['kgwb'] += $row['kgwb'];
				$data[$nospb]['subtotal']['kgwbnetto'] += $row['kgwbnetto'];
				$data[$nospb]['subtotal']['brondolan'] += $row['brondolan'];
				$data[$nospb]['subtotal']['totalkg'] += $row['totalkg'];
			}
		
			// Variabel untuk grand total
			$grandTotal = [
				'kgwb' => 0,
				'kgwbnetto' => 0,
				'brondolan' => 0,
				'totalkg' => 0,
			];
		
			// Rendering tabel
			if ($tipeprint == 'html') {
				$border = "border=0";
			} else {
				$border = "border=1";
			}
		
			$tab = "<table cellpadding=5 cellspacing=1 " . $border . " class=sortable style='width:100%'>
				<thead>
				<tr class=rowheader style='text-align:center;font-weight:bold'>
					<th>No</th>
					<th>No SPB</th>
					<th>" . $_SESSION['lang']['divisi'] . "</th>
					<th>Induk " . $_SESSION['lang']['blok'] . "</th>
					<th>" . $_SESSION['lang']['blok'] . "</th>
					<th>Tanggal Panen</th>
					<th>" . $_SESSION['lang']['jjg'] . "</th>
					<th>KG WB</th>
					<th>KG WB Netto</th>
					<th>BJR</th>
					<th>Brondolan</th>
					<th>Total KG</th>
					<th>KG BJR</th>
				</tr>
				</thead><tbody>";
		
			$no = 1;
			foreach ($data as $nospb => $group) {
				foreach ($group['details'] as $detail) {
					$tab .= "<tr class='rowcontent'>
						<td align=center>" . $no++ . "</td>
						<td align=center>" . $nospb . "</td>
						<td align=center>" . $optsubunit[substr($detail['blok'], 0, 6)] . " - " . substr($detail['blok'], 0, 6) . "</td>
						<td align=center>" . $optindukblok[$detail['indukblok']] . " - " . $detail['indukblok'] . "</td>
						<td align=center>" . $optsubunit[$detail['blok']] . " - " . $detail['blok'] . "</td>
						<td align=center>" . $detail['tanggalpanen'] . "</td>
						<td align=right>" . $detail['jjg'] . "</td>
						<td align=right>" . $detail['kgwb'] . "</td>
						<td align=right>" . $detail['kgwbnetto'] . "</td>
						<td align=right>" . $detail['bjr'] . "</td>
						<td align=right>" . $detail['brondolan'] . "</td>
						<td align=right>" . $detail['totalkg'] . "</td>
						<td align=right>" . $detail['kgbjr'] . "</td>
					</tr>";
				}
		
				// Tambahkan subtotal
				$tab .= "<tr class='rowcontent' style='font-weight:bold;background-color:yellow'>
					<td colspan='7' align='center'>Subtotal SPB " . $nospb . "</td>
					<td align='right'>" . $group['subtotal']['kgwb'] . "</td>
					<td align='right'>" . $group['subtotal']['kgwbnetto'] . "</td>
					<td></td>
					<td align='right'>" . $group['subtotal']['brondolan'] . "</td>
					<td align='right'>" . $group['subtotal']['totalkg'] . "</td>
					<td colspan='3'></td>
				</tr>";
		
				// Tambahkan ke grand total
				foreach ($grandTotal as $key => $value) {
					$grandTotal[$key] += $group['subtotal'][$key];
				}
			}
		
			// Tambahkan grand total
			$tab .= "<tr class='rowcontent' style='font-weight:bold;background-color:orange'>
				<td colspan='7' align='center'>Grand Total</td>
				<td align='right'>" . $grandTotal['kgwb'] . "</td>
				<td align='right'>" . $grandTotal['kgwbnetto'] . "</td>
				<td></td>
				<td align='right'>" . $grandTotal['brondolan'] . "</td>
				<td align='right'>" . $grandTotal['totalkg'] . "</td>
				<td colspan='3'></td>
			</tr>";
		
			$tab .= "</tbody></table>";
				
		if ($tipeprint == 'html') {
			echo $tab;
		} else {
			$nop_ = "Laporan_RINCIANSPB_" . $unit . "_" . $periode;
			if (strlen($tab) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $tab)) {
					echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				} else {
					echo "<script language=javascript>
						window.location='tempExcel/" . $nop_ . ".xls';
						</script>";
				}
				fclose($handle);
			}
		}
		break;
}
