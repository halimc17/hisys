<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');

if (stripos($_SESSION['standard']['username'], "tim.owl") !== false) {
	// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
}
use Dompdf\Dompdf;

$method = checkPostGet('method', '');

function hitungRumusPakHadi($thn_lalu, $thn_ini)
{
	if ($thn_lalu == 0 && $thn_ini != 0) {
		return 100;
	} else if ($thn_lalu == 0 && $thn_ini == 0) {
		return 0;
	} else {
		return ($thn_ini - $thn_lalu) / $thn_lalu * 100;
	}
}
$param = $_POST;
$cparam = count($param);
if ($cparam == 0) {
	$param = $_GET;
}

$tampilData0 = checkPostGet('tampilData0', '');

if ($param['periode'] == '' || $param['kodept'] == '') {
	exit("Warning:Periode / PT masih kosong");
}

$dgt = $param['digit'];

#= ambil jumlah
#= ambil jumlah
$str = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $param['kodept'] . "' or induk='" . $param['kodept'] . "'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$namaorg[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
}


if ($param['kodeunit'] != '') {
	$whereunit = " and kodeorganisasi='" . $param['kodeunit'] . "'";
	$judulunit = "<br>" . $namaorg[$param['kodeunit']] . "";
}


#= daftar unit dalam 1 pt
@$where = " and substr(kodeorg,1,4) in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $param['kodept'] . "'  " . $whereunit . ")";


$kodelaporan = 'MUTASIBANK';

$tahuncutoff = 2024;

#= untuk judul laporan
$str = "select * from " . $dbname . ".keu_5mesinlaporanht where namalaporan='" . $kodelaporan . "'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$judullaporan = $bar['ket1'];
}
/*
data tahun lalu adalah januari bulan param, misal, param maret 2021, 
maka periodelalu untuk desember 2020, tapi data yang akan diambil januari 2021,
karna konsep neraca adalah saldo akhir bulan dipilih atau saldo awal bulan depan
contoh maret 2021

tampilan untuk periode lalu adalah desember 2020, namun datanya adalah januari 2021
tampilan untuk periode lalu adalah januari 2021, namun datanya adalah februari 2021
tampilan untuk periode lalu adalah februari 2021, namun datanya adalah maret 2021
*/

$qwe = explode('-', $param['periode']);
$tahun = $qwe[0];

if ($tahun > ($tahuncutoff + 1)) {
	$tahunlalu = $tahun - 1;
	$perlalu = $tahunlalu . '-' . $qwe[1];
}
#= bentuk array bulan
// array_push($arrper,month_inbetween($tahunlalu.'-01',$perlalu));


// echo $qwe[1];

$dataper = (float) $qwe[1];
// echo $dataper;
$cspan = 0;
$start = 0;

for ($i = 1; $i <= 1; $i++) { ## INI UNTUK PERIODE BULAN 01 DAN AMBIL SALDO AWAL BUKAN KREDIT - DEBET

	if ($tahun == 2025) { //khusus 2025 dari 7
		$start = 6;
		break;
	}
	if (strlen($i) < 2) {
		$i = '0' . $i;
	}
	$arrper[$tahun . '-' . $i] = $tahun . '-' . $i;
	// if ($tahunlalu > $tahuncutoff) {
	// 	$arrper[$tahunlalu . '-' . $i] = $tahunlalu . '-' . $i;
	// }
	// $arrper[$i] = $i;
	$cspan++;
}
$start++;
for ($i = $start; $i <= 12; $i++) { ## INI DIBUAT UNTUK BUAT PERIODE HANYA LEBIH BESAR DARI PERIODE BULAN 01 BUAT DIAMBIL DEBET - KREDIT BUKAN SALDO AWAL
	if (strlen($i) < 2) {
		$i = '0' . $i;
	}
	$arrper[$tahun . '-' . $i] = $tahun . '-' . $i;
	// if ($tahunlalu > $tahuncutoff) {
	// 	$arrper[$tahunlalu . '-' . $i] = $tahunlalu . '-' . $i;
	// }
	// $arrper[$i] = $i;
	$cspan++;
}
$arrperjudul = month_inbetween($tahun . '-0' . $start, $tahun . '-12');

/*
for($i=2;$i<=(float)$qwe[1];$i++){## INI DIBUAT UNTUK BUAT PERIODE HANYA LEBIH BESAR DARI PERIODE BULAN 01 BUAT DIAMBIL DEBET - KREDIT BUKAN SALDO AWAL
	if(strlen($i)<2){
		$i='0'.$i;
	}
	$arrpernext[$tahun.'-'.$i]=$tahun.'-'.$i;
	$arrpernext[$tahunlalu.'-'.$i]=$tahunlalu.'-'.$i;
	$arrpernext[$i]=$i;
	$cspan++;
}
*/
// echo"<pre>";
// print_r($arrpernext);
// exit();

$nouruttemp = '';
$daftarakun = array();
$daftartotal = array();
$jumlahdaftar = array();

#= ambil list laporan
$str = "select * from " . $dbname . ".keu_5mesinlaporandt where namalaporan='" . $kodelaporan . "'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$arrnourut[$bar['nourut']] = $bar['nourut'];
	$namanourut[$bar['nourut']] = $bar['keterangandisplay'];
	$noakuntotalnourut[$bar['nourut']] = $bar['noakundisplay'];
	$tipenourut[$bar['nourut']] = $bar['tipe'];
	$posisi[$bar['nourut']] = $bar['posisi'];
}

#= ambil jumlah
$str = "select count(*) as jumlah,nourut from " . $dbname . ".keu_5mesinlaporandt_akun where namalaporan='" . $kodelaporan . "' group by nourut";
$res = fetchdata($str);
foreach ($res as $bar) {
	$jumlahdaftar[$bar['nourut']] = $bar['jumlah'];
}


#= ambil daftar noakun
$str = "select * from " . $dbname . ".keu_5mesinlaporandt_akun where namalaporan='" . $kodelaporan . "' order by nourut asc";
$res = fetchdata($str);
foreach ($res as $bar) {
	if ($nouruttemp == $bar['nourut']) {
		$no++;
	} else {
		$no = 1;
	}

	if ($nouruttemp == $bar['nourut']) {
		if ($no < $jumlahdaftar[$bar['nourut']]) {
			$daftarakun[$bar['nourut']] .= $bar['noakun'] . ',';
		} else {
			$daftarakun[$bar['nourut']] .= $bar['noakun'];
		}
	} else {
		if ($jumlahdaftar[$bar['nourut']] == 1) { #= hanya 1 akun saja
			@$daftarakun[$bar['nourut']] .= $bar['noakun'];
		} else {
			@$daftarakun[$bar['nourut']] .= $bar['noakun'] . ',';
		}
	}
	$nouruttemp = $bar['nourut'];
}




// --- ADDED FOR OPTIMIZATION ---
$all_accounts_arr = array();
foreach ($arrnourut as $nourut) {
    if ($tipenourut[$nourut] == 'Detail' && $nourut != '10001' && (@$jumlahdaftar[$nourut] > 0)) {
        if (!empty($daftarakun[$nourut])) {
            $accs = explode(',', $daftarakun[$nourut]);
            foreach($accs as $acc) {
                $clean_acc = trim($acc, " '\"");
                if ($clean_acc != '') {
                    $all_accounts_arr[$clean_acc] = $clean_acc;
                }
            }
        }
    }
}
$all_accounts_str = implode("','", $all_accounts_arr);

$all_periods_arr = array();
foreach ($arrper as $per) {
    $all_periods_arr[] = $per;
}
$all_periods_str = implode("','", $all_periods_arr);

$data_jurnal = array();
if (!empty($all_accounts_arr) && !empty($all_periods_arr)) {
    $str_jurnal = "select sum(jumlah) as dtthnini, noakun, periode from " . $dbname . ".keu_jurnaldt_vw where 1=1 " . $where . " and periode in ('" . $all_periods_str . "') and noakun in ('" . $all_accounts_str . "') group by noakun, periode";
    $res_jurnal = fetchdata($str_jurnal);
    foreach ($res_jurnal as $bar) {
        // Trim just in case the database has trailing spaces on the varchar
        $data_jurnal[trim($bar['noakun'])][$bar['periode']] = $bar['dtthnini'];
    }
}
// --- END OPTIMIZATION ---

foreach ($arrnourut as $nourut) {

	if ($tipenourut[$nourut] == 'Detail' and (@$jumlahdaftar[$nourut] > 0 || @$jumlahdaftar[$nourut] != '')) {
		$listakun = '';
		$is_sales = false;
		if (@$jumlahdaftar[$nourut] > 0) {
			$listakun = " and noakun in (" . $daftarakun[$nourut] . ")";

			//buat sales 
			if ($jumlahdaftar[$nourut] == 1 && substr($daftarakun[$nourut], 0, 1) == '5') {
				$is_sales = true;
			}
		}


		foreach ($arrper as $per) {
			$bulanperdata = '';

			$perdata = $per;
			$explodeperdata = explode('-', $perdata);
			$bulanperdata = $explodeperdata[1];

			if ($bulanperdata == '') {
				$bulanperdata = $explodeperdata[0];
			}
			// echo $bulanperdata;
			// $kolomthnini = ($is_sales?"kredit" . $bulanperdata:"awal" . $bulanperdata);
			if($nourut=='10001'){
			    $bla = "sum(awal" . $bulanperdata . ")";
				$perdata = str_replace("-", "", $perdata);	//periode depan karna diambil dari saldo akhir berjalan, misal data periode 3, maka ambil sawal periode 4
				$str = "select " . $bla . " as dtthnini from " . $dbname . ".keu_saldobulanan where 1=1  " . $where . "  " . $listakun . "  and periode='" . $perdata . "'";

			    $res = fetchdata($str);
			    foreach ($res as $bar) {
				    // @$dtthnini[$nourut][$per]+=$bar['dtthnini'];
				    @$dtthnini[$nourut][$per] += ($bar['dtthnini'] * $posisi[$nourut]);
				    // @$ytdthn[$nourut][substr($per,0,4)]+=$bar['dtthnini'];
				    @$ytdthn[$nourut][substr($per, 0, 4)] += ($bar['dtthnini'] * $posisi[$nourut]);
			    }

			} else {
				$dtthnini_val = 0;
				if (!empty($daftarakun[$nourut])) {
					$accs = explode(',', $daftarakun[$nourut]);
					foreach($accs as $acc) {
						$clean_acc = trim($acc, " '\"");
						if (isset($data_jurnal[$clean_acc][$per])) {
							$dtthnini_val += $data_jurnal[$clean_acc][$per];
						}
					}
				}
				
				// Always add, even if 0, so that the array structure exactly mimics the original code.
				@$dtthnini[$nourut][$per] += ($dtthnini_val * $posisi[$nourut]);
				@$ytdthn[$nourut][substr($per, 0, 4)] += ($dtthnini_val * $posisi[$nourut]);
			}
			
		}


		// if($nourut=='10004'){
		// 	print_r($dtthnini);
		// 	// exit();
		// 	exit("WARNING 10004:");
			
		// }

		
		//tahunini-tahunlalu/tahunlalu
		### Rumus Pak Hadi
		// $adaini[$nourut][$i[1]] = hitungRumusPakHadi(@$dtthnini[$nourut][$tahunlalu . '-' . $i[1]], @$dtthnini[$nourut][$tahun . '-' . $i[1]]);
		// @$changeini[$nourut][$i[1]] = $adaini[$nourut][$i[1]];
		### Rumus Pak Hadi
		// $ada[$nourut] = hitungRumusPakHadi(@$ytdthn[$nourut][$tahunlalu], @$ytdthn[$nourut][$tahun]);

		// @$change[$nourut] = $ada[$nourut];

		// }
	}
}
// echo"<pre>";
// print_r($dtthnini);

#= buat total
foreach ($arrnourut as $nourut) {
	if ($tipenourut[$nourut] == 'Total') {
		$daftartotal = explode(',', $noakuntotalnourut[$nourut]);
		foreach ($daftartotal as $key) {
			if ($key != '') {
				$amin = substr($key, 0, 1);
				if ($amin == '-') {
					$keydata = substr($key, 1, 5);
					foreach ($arrper as $per) {
						@$dtthnini[$nourut][$per] -= $dtthnini[$keydata][$per];
						@$ytdthn[$nourut][substr($per, 0, 4)] -= $dtthnini[$keydata][$per];
						$i = explode('-', $per);
						@$dtthnini[$nourut][$i[1]] = ($dtthnini[$nourut][$tahun . '-' . $i[1]] - $dtthnini[$nourut][$tahunlalu . '-' . $i[1]]) / $dtthnini[$nourut][$tahunlalu . '-' . $i[1]] * 100;
						### Rumus Pak Hadi
						// $adaini[$nourut][$i[1]] = hitungRumusPakHadi(@$dtthnini[$nourut][$tahunlalu . '-' . $i[1]], @$dtthnini[$nourut][$tahun . '-' . $i[1]]);
						// @$changeini[$nourut][$i[1]] = $adaini[$nourut][$i[1]];
						### Rumus Pak Hadi
						// $ada[$nourut] = hitungRumusPakHadi(@$ytdthn[$nourut][$tahunlalu], @$ytdthn[$nourut][$tahun]);
						// @$change[$nourut] = $ada[$nourut];
					}
				} else {
					foreach ($arrper as $per) {
						@$dtthnini[$nourut][$per] += $dtthnini[$key][$per];
						@$ytdthn[$nourut][substr($per, 0, 4)] += $dtthnini[$key][$per];
						$i = explode('-', $per);
						@$dtthnini[$nourut][$i[1]] = ($dtthnini[$nourut][$tahun . '-' . $i[1]] - $dtthnini[$nourut][$tahunlalu . '-' . $i[1]]) / $dtthnini[$nourut][$tahunlalu . '-' . $i[1]] * 100;
						### Rumus Pak Hadi
						// $adaini[$nourut][$i[1]] = hitungRumusPakHadi(@$dtthnini[$nourut][$tahunlalu . '-' . $i[1]], @$dtthnini[$nourut][$tahun . '-' . $i[1]]);
						// @$changeini[$nourut][$i[1]] = $adaini[$nourut][$i[1]];
						### Rumus Pak Hadi
						// $ada[$nourut] = hitungRumusPakHadi(@$ytdthn[$nourut][$tahunlalu], @$ytdthn[$nourut][$tahun]);
						// @$change[$nourut] = $ada[$nourut];
					}
				}
			}
		}
	}
}

// print_r($arrper);
// exit ("ERROR");
if ($param['tipe'] == 'pdf') {
	$fsize = '9px';
	$cell = '0.5';
	$cspc = '0';
	$brd = '0.1';
	$sty = 'border-top:0.1px solid black;border-bottom:0.1px solid black;font-weight:bold;';
} else {
	$cell = '0';
	$cspc = '1';
	$brd = '0';
	$sty = '';
}
$stream .= "<table class=freezetbl border=0 cellspacing=" . @$cspc . " cellpadding=1 style='width:100%;" . @$fsize . "'>";
$stream .= "<thead>";
$stream .= "<tr class=rowheader>";
// $stream.="<th align=center style='".$sty."' colspan='".(($cspan*3)+2)."'><b>".strtoupper($namaorg[$param['kodept']]."<br>".$judullaporan)."</b></th>";
$stream .= "<th align=center style='" . $sty . "' colspan=15><b>" . strtoupper($namaorg[$param['kodept']] . "" . @$judulunit . "<br>" . $judullaporan) . "</b></th>";
$stream .= "</tr>";
$stream .= "<tr class=rowheader>";
$stream .= "<th align=center style='" . $sty . "' colspan=2 rowspan=2><b>" . $_SESSION['lang']['keterangan'] . "</b></th>";
foreach ($arrperjudul as $per) {

	$stream .= "<th align=center style='" . $sty . "'><b>" . numToMonth(floatval(substr($per, 5, 2)), 'I', 'long') . "</b></th>";
}
$stream .= "<th align=center style='width:5%;" . $sty . "' rowspan=2><b>YTD " . $tahun . "</b></th>";

$stream .= "</tr>";

$stream .= "</thead><tbody>";
foreach ($arrnourut as $nourut) {
	$style = '';
	$stream .= "<tr class=rowcontent>";
	if ($tipenourut[$nourut] == 'Header') {
		// exit('warning: <pre>'. print_r($arrper,true));
		$stream .= "<td align=left colspan='" . (count($arrper) + 5) . "'><b>" . $namanourut[$nourut] . "</b></td>";
		continue;
	}

	if ($tipenourut[$nourut] == 'Detail') {
		$stream .= "<td align=left style='width:1%'></td>";
		$stream .= "<td align=left style='width:20%'>" . $namanourut[$nourut] . "</td>";
	}
	if ($tipenourut[$nourut] == 'Total') {
		@$style = "style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'";
		// $stream.="<td align=left>&nbsp;</td>"; 
		$stream .= "<td align=left " . $style . "  colspan=2><b>" . $namanourut[$nourut] . "</b></td>";
	}

	//
	#= data untuk nilainya
	#= tahun sekarang
	foreach ($arrper as $per) {
		$tahunjudul = substr($per, 0, 4);
		// if ($tipenourut[$nourut] == 'Header') {
		// 	$stream .= "<td></td>";
		// 	continue;
		// }
		if ($tipenourut[$nourut] == 'Detail') {
			$style = "style=cursor:pointer; title='Click untuk melihat detail' onclick=\"detail('" . @$nourut . "','" . @$per . "','" . @$param['kodept'] . "','" . @$regional . "','" . @$param['kodeunit'] . "','html','event','" . $param['digit'] . "');\"";
		}
		// if (strlen($tahunjudul) < '4') {
		// 	if (isset($tahunlalu)) {
				$stream .= "<td align=right " . $style . ">" . hidezerodecimal(fixnan(@$dtthnini[$nourut][$per]), $dgt) . "</td>";
			// } else {
			// 	continue;
			// }
		
	}

	if ($tipenourut[$nourut] == 'Total') {
		@$style = "style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'";
	}
	$stream .= "<td align=right " . $style . ">" . hidezerodecimal(fixnan(@$ytdthn[$nourut][$tahun]), $dgt) . "</td>";

	$stream .= "</tr>";
}


$stream .= "</tbody></table>";


if ($param['tipe'] == 'excel') {
	$nop = $kodelaporan . "_" . $param['kodept'] . "_" . $param['periode'] . ".xls";
	$xls = new HtmlExcel();
	$xls->setCss($css);
	$xls->addSheet("LABARUGIKONSOL", $stream);
	// $xls->addSheet("Report", $tab2);
	$xls->headers($nop);
	echo $xls->buildFile();
} else if ($param['tipe'] == 'pdf') {
	$dompdf = new Dompdf();
	$dompdf->loadHtml($stream);
	$dompdf->setPaper('A4', 'landscape');
	$dompdf->render();
	$dompdf->stream("LABARUGIKONSOL", array("Attachment" => 0));
} else {
	echo $stream;
}
