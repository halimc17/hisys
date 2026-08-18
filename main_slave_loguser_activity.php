<?php
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;

$param = $_POST;
$pt = checkPostGet('pt','');
$unit = checkPostGet('unit','');
$jabatan = checkPostGet('jabatan','');
$departemen = checkPostGet('departemen','');
$karyawan = checkPostGet('karyawan','');
$tgl1 = checkPostGet('tgl1','');
$tgl2 = checkPostGet('tgl2','');
$method = checkPostGet('method','');
$tipeprint = checkPostGet('tipeprint','');

switch ($method) {
	case 'getunit':
		$optunit = "<option value=>".$_SESSION['lang']['all']."</option>";

		if ($pt != '') {
			$str = "SELECT kodeorganisasi, namaorganisasi FROM ".$dbname.".organisasi WHERE induk = '".$pt."'";
			$res = fetchdata($str);

			foreach ($res as $key => $val) {
				$optunit .= "<option value=".$val['kodeorganisasi'].">".$val['namaorganisasi']."</option>";
			}
		}

		echo $optunit;
	break;

	case 'preview':

		$wherept = '';
		if ($unit != '') {
			$wherept = "AND b.lokasitugas = '".$unit."'";
		} else if ($pt != '') {
			$wherept = "AND b.lokasitugas in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE induk = '".$pt."')";
		} 

		$where = '';
		// if ($jabatan != '') {
		// 	$where .= "AND b.kodejabatan = '".$jabatan."'";
		// } else if ($departemen != '') {
		// 	$where .= "AND b.bagian = '".$departemen."'";
		// } else if ($karyawan != '') {
		// 	$where .= "AND b.karyawanid = '".$karyawan."'";
		// }
		if ($jabatan != '') {
			$where .= " AND b.kodejabatan = '".$jabatan."'";
		}
		if ($departemen != '') {
			$where .= " AND b.bagian = '".$departemen."'";
		}
		if ($karyawan != '') {
			$where .= " AND b.karyawanid = '".$karyawan."'";
		}


		$tab = '<div class=table-scroll style=max-height:320px>';

		if ($tipeprint == 'html') {
			$tab .= '<table border=0 class=sortable cellpading=0 cellspacing=1>';
		} else {
			$tab .= '<table border=1 class=sortable cellpading=0 cellspacing=1>';
		}

		$tab .= "<thead>
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['nourut']."</th>
						<th align=center>".$_SESSION['lang']['username']."</th>
						<th align=center>".$_SESSION['lang']['unit']."</th>
						<th align=center>".$_SESSION['lang']['tanggal']."</th>
						<th align=center>".$_SESSION['lang']['waktu']."</th>
						<th align=center>".$_SESSION['lang']['menu']."</th>
						 
					</tr>
				</thead>
				<tbody>";

		// $str = "SELECT a.username,
		// 		left(a.waktu,10) as tanggal, 
		// 		right(a.waktu,8) as waktu, 
		// 		a.file, 
		// 		a.karyawanid, 
		// 		b.namakaryawan, 
		// 		c.namaorganisasi, 
		// 		d.namajabatan, 
		// 		e.nama
		// 		FROM ".$dbname.".user_activity a
		// 		JOIN ".$dbname.".datakaryawan b ON a.karyawanid = b.karyawanid
		// 		JOIN ".$dbname.".organisasi c ON b.lokasitugas = c.kodeorganisasi
		// 		JOIN ".$dbname.".sdm_5jabatan d ON b.kodejabatan = d.kodejabatan
		// 		JOIN ".$dbname.".sdm_5departemen e ON b.bagian = e.kode
		// 		WHERE left(a.waktu,10) BETWEEN '".tanggalsystemn($tgl1)."' AND '".tanggalsystemn($tgl2)."'
		// 		".$wherept.$where."
		// 		ORDER BY a.username ASC";
		$str = "SELECT a.username,
				left(a.waktu,10) as tanggal, 
				right(a.waktu,8) as waktu, 
				a.file, 
				a.karyawanid, 
				b.namakaryawan, 
				c.namaorganisasi, 
				d.namajabatan,a.post,a.get 

				FROM ".$dbname.".user_activity a
				JOIN ".$dbname.".datakaryawan b ON a.karyawanid = b.karyawanid
				JOIN ".$dbname.".organisasi c ON b.lokasitugas = c.kodeorganisasi
				JOIN ".$dbname.".sdm_5jabatan d ON b.kodejabatan = d.kodejabatan
				WHERE left(a.waktu,10) BETWEEN '".tanggalsystemn($tgl1)."' AND '".tanggalsystemn($tgl2)."'
				".$wherept.$where."
				ORDER BY a.username DESC";
		$res = fetchdata($str);

		// echo"<pre>";
		// print_r($res);
		// echo"</pre>";

		foreach ($res as $key => $val) {
			$file = str_replace("/owl/", "", $val['file']);
			$file = str_replace(".php", "", $file);
			try{
				$menu = getMenu($file,'x');
			}catch(PDOException $e){
				$menu="";
			}
			if($menu!=''){
				@$no+=1;
				$tab .= "<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=left>".$val['username']."</td>
						<td align=left>".$val['namaorganisasi']."</td>
						<td align=right>".tanggalnormal($val['tanggal'])."</td>
						<td align=right>".$val['waktu']."</td>
						<td align=left>".trim($menu)."</td>
						 
					</tr>";
			}
		}

		$tab .= "</tbody></table></div>";


		if ($tipeprint == 'html') {
			echo $tab;
		} else if ($tipeprint == 'excel'){
			$nop = "Laporan Log User Activity.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("Sheet 1", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		} 
	break;
}

?>