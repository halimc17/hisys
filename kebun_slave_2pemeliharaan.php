<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$lksiTgs = $_SESSION['empl']['lokasitugas'];
$kdOrg = checkPostGet('kdOrg', '');
$kdAfd = checkPostGet('kdAfd', '');
$tgl1_ = checkPostGet('tgl1', '');
$tgl2_ = checkPostGet('tgl2', '');
$kegiatan = checkPostGet('kegiatan', '');
$sumber = checkPostGet('sumber', '');
$kdbarang = checkPostGet('kdbarang', '');
$intiplasma = checkPostGet('intiplasma', '');
$tipereport = checkPostGet('tipereport', '');
$kegiatan = checkPostGet('kegiatan', '');


$namaOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

if (($proses == 'excel') or ($proses == 'pdf')) {
	$kdOrg = $_GET['kdOrg'];
	$kdAfd = $_GET['kdAfd'];
	$tgl1_ = $_GET['tgl1'];
	$tgl2_ = $_GET['tgl2'];
	$kegiatan = $_GET['kegiatan'];
	$sumber = $_GET['sumber'];
	$kdbarang = $_GET['kdbarang'];
}

$getNamaIndukBlok = makeOption($dbname, "organisasi", "indukblok,namaindukblok");


if ($kdbarang == '') {
	$brg = "";
} else {
	$brg = " and e.kodebarang='" . $kdbarang . "' ";
}


if ($kdAfd == '')
	$kdAfd = $kdOrg;

$tgl1_ = tanggalsystem($tgl1_);
$tgl1 = substr($tgl1_, 0, 4) . '-' . substr($tgl1_, 4, 2) . '-' . substr($tgl1_, 6, 2);
$tgl2_ = tanggalsystem($tgl2_);
$tgl2 = substr($tgl2_, 0, 4) . '-' . substr($tgl2_, 4, 2) . '-' . substr($tgl2_, 6, 2);

if ($_SESSION['language'] == 'EN') {
	$zz = 'namakegiatan1 as namakegiatan';
} else {
	$zz = 'namakegiatan';
}
$str = "select kodekegiatan, " . $zz . ", satuan
        from " . $dbname . ".setup_kegiatan
        ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$kamusKeg[$bar->kodekegiatan] = $bar->namakegiatan;
}

if (($proses == 'preview') or ($proses == 'excel') or ($proses == 'pdf')) {
	if ($kdOrg == '') {
		if (substr($lksiTgs, 2, 2) == 'HO') {
		} else {
			// echo"Error: Estate code and afdeling code required."; exit;
		}
	}

	if (($tgl1_ == '') or ($tgl2_ == '')) {
		echo "Error: Date required.";
		exit;
	}

	if ($tgl1 > $tgl2) {
		echo "Error: First date must lower than the second.";
		exit;
	}
}





if ($proses == 'excel' or $proses == 'preview') {

	if ($kdOrg == '') {
		exit("Warning:Kebun masih kosong");
	}
	$where = "";
	if ($kegiatan != "") {
		$where = " and a.kodekegiatan='" . $kegiatan . "' ";
	}
	if ($tipereport == 'perkaryawan') {
		$where .= " order by z.namakaryawan, c.tanggal,a.kodekegiatan ";
	}

	$border = 0;
	if ($proses == 'excel') $border = 1;

	if ($sumber == 'BKM') {

		if (substr($tgl1_, 0, 6) != substr($tgl2_, 0, 6)) {
			exit("Warning : Periode tanggal harus dibulan yang sama.");
		}

		$str2 = "select a.*,z.namakaryawan, a.kodeorg as kodeblok, b.umr, b.insentif, c.* 
            from " . $dbname . ".kebun_prestasi a
            left join " . $dbname . ".datakaryawan z on a.nikpemel=z.karyawanid
            left join " . $dbname . ".kebun_kehadiran b on a.notransaksi=b.notransaksi and a.nikpemel=b.nik and a.nourut=b.nourut
            left join " . $dbname . ".kebun_aktifitas c on c.notransaksi=a.notransaksi
            where c.tipetransaksi !='PNN' and c.tanggal between '" . $tgl1_ . "' and '" . $tgl2_ . "' and a.kodeorg like '" . $kdAfd . "%' " . $where . " ";
		$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
		$res2->setFetchMode(PDO::FETCH_OBJ);
		while ($bar2 = $res2->fetch()) {
			$no = $bar2->notransaksi;
			$notrans[$bar2->notransaksi] = $bar2->notransaksi;
			$tglxx[$bar2->notransaksi] = $bar2->tanggal;
			$nourut[$bar2->kodekegiatan] = $bar2->kodekegiatan;
			$kdblok[$bar2->notransaksi][$bar2->kodekegiatan] = $bar2->kodeblok;
			$kdkegiatan[$bar2->notransaksi][$bar2->kodekegiatan] = $bar2->kodekegiatan;

			$data[$bar2->notransaksi][$bar2->kodekegiatan][$bar2->kodeblok] = $bar2->kodeblok;

			@$upah[$bar2->notransaksi][$bar2->kodekegiatan][$bar2->kodeblok] += $bar2->umr;
			@$premi[$bar2->notransaksi][$bar2->kodekegiatan][$bar2->kodeblok] += $bar2->insentif;
			@$jhasilkerja[$bar2->notransaksi][$bar2->kodekegiatan][$bar2->kodeblok] += $bar2->hasilkerja;
			@$jhk[$bar2->notransaksi][$bar2->kodekegiatan][$bar2->kodeblok] += $bar2->jumlahhk;

			//perkaryawan
			@$namakaryawan[$bar2->nikpemel]['nama'] = $bar2->namakaryawan;
			@$dataPerkaryawan[$bar2->nikpemel][$bar2->tanggal][$bar2->kodekegiatan]['tanggal'] = $bar2->tanggal;
			@$dataPerkaryawan[$bar2->nikpemel][$bar2->tanggal][$bar2->kodekegiatan]['upah'] += $bar2->umr;
			@$dataPerkaryawan[$bar2->nikpemel][$bar2->tanggal][$bar2->kodekegiatan]['insentif'] += $bar2->insentif;
			@$dataPerkaryawan[$bar2->nikpemel][$bar2->tanggal][$bar2->kodekegiatan]['hasilkerja'] += $bar2->hasilkerja;
			@$dataPerkaryawan[$bar2->nikpemel][$bar2->tanggal][$bar2->kodekegiatan]['jumlahhk'] += $bar2->jumlahhk;
		}

		$str4 = "select * from " . $dbname . ".kebun_pakaimaterial 
          where notransaksi like '" . substr($tgl1_, 0, 6) . "%' and kodeorg like '" . $kdAfd . "%' ";
		$res4 = $owlPDO->query($str4) or die(print " Gagal: " . PDOException::getMessage());
		$res4->setFetchMode(PDO::FETCH_OBJ);
		while ($bar4 = $res4->fetch()) {
			@$arrkdbrg[$bar4->kodebarang] = $bar4->kodebarang;
			@$kdbrg[$bar4->notransaksi][$bar4->kodekegiatan][$bar4->kodebarang] = $bar4->kodebarang;
			@$jmlhbrg[$bar4->notransaksi][$bar4->kodekegiatan][$bar4->kodebarang] += $bar4->kwantitas;
		}
	} else if ($sumber == 'SPK') {
		if ($kdOrg == '') {
			$str1 = "select a.notransaksi as notransaksi,a.tanggal as tanggal,a.kodeblok as kodeorg,
                  a.kodekegiatan as kodekegiatan,a.hasilkerjarealisasi as hasilkerja,a.hkrealisasi as jumlahhk,
                  a.jumlahrealisasi as upah,b.namakegiatan as namakegiatan,
                  b.satuan as satuan,b.kelompok as kelompok
                  from " . $dbname . ".log_baspk a 
                  left join " . $dbname . ".setup_kegiatan b on a.kodekegiatan = b.kodekegiatan
				  left join " . $dbname . ".setup_blok c on a.kodeblok = c.kodeorg
                  where a.kodeblok like '" . $kdAfd . "%' and a.tanggal between '" . $tgl1_ . "' and '" . $tgl2_ . "'
                  and a.kodekegiatan like '%" . $kegiatan . "%' and a.posting=1 and c.intiplasma like '%" . $intiplasma . "%'
                  group by a.notransaksi,a.kodekegiatan,a.kodeblok,a.tanggal
                  order by a.kodekegiatan,a.kodeblok asc";
		} else {
			$where = '';
			if ($kdOrg != $_SESSION['empl']['lokasitugas']) {
				$where = " and a.posting=1";
			}
			$str1 = "select a.notransaksi as notransaksi,a.tanggal as tanggal,a.kodeblok as kodeorg,
                  a.kodekegiatan as kodekegiatan,a.hasilkerjarealisasi as hasilkerja,a.hkrealisasi as jumlahhk,
                  a.jumlahrealisasi as upah,b.namakegiatan as namakegiatan,
                  b.satuan as satuan,b.kelompok as kelompok
                  from " . $dbname . ".log_baspk a 
                  left join " . $dbname . ".setup_kegiatan b on a.kodekegiatan = b.kodekegiatan
				  left join " . $dbname . ".setup_blok c on a.kodeblok = c.kodeorg
                                      
                  where a.kodeblok like '" . $kdAfd . "%' and a.tanggal between '" . $tgl1_ . "' and '" . $tgl2_ . "'
                  and a.kodekegiatan like '%" . $kegiatan . "%' " . $where . " and c.intiplasma like '%" . $intiplasma . "%'
                   and b.kelompok in ('BBT','TM','TB','TBM','PNN')    
                  group by a.notransaksi,a.kodekegiatan,a.kodeblok,a.tanggal
                  order by a.kodekegiatan,a.kodeblok asc";
		}
	} else {
	}


	if ($proses == 'excel') {
		$stream = "<table>
					<tr>
						<td colspan=16 style='text-align:left; font-weight:bold'>" . $_SESSION['lang']['laporanPemeliharaan'] . "</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
				</table>";
	} else {
		$stream = "";
	}
	//$stream.="<div class='table-scroll' style='height:350px;'>";
	if ($tipereport == 'perkaryawan') {
		$stream .= "<p style='display:none;'>" . $str1 . "</p><table style=font-size:12px; cellspacing='1' cellpadding=5 border='" . $border . "' class='sortable'>
		<thead>
		<tr class=rowheader>
		<th align=center>" . $_SESSION['lang']['nomor'] . "</th>
		<th  align=center>" . $_SESSION['lang']['nama'] . "</th>
		<th  align=center>" . $_SESSION['lang']['tanggal'] . "</th>
		<th  align=center>" . $_SESSION['lang']['kodekegiatan'] . "</th>            
		<th  align=center>" . $_SESSION['lang']['kegiatan'] . "</th>
		<th  align=center>" . $_SESSION['lang']['satuan'] . "</th>
		<th  align=center>" . $_SESSION['lang']['realisasi'] . "</th>

		<th align=center>" . $_SESSION['lang']['jumlahhk'] . "</th>
		<th  align=center>" . $_SESSION['lang']['upahkerja'] . "</th>
		<th align=center>" . $_SESSION['lang']['insentif'] . "</th>
		<th align=center>" . $_SESSION['lang']['jumlahditerima'] . "</th>
		<th align=center>" . $_SESSION['lang']['tandatangan'] . "</th>
		</tr></thead>
		<tbody>";
	} else {
		$stream .= "<p style='display:none;'>" . @$str1 . "</p><table style=font-size:12px; cellpadding=5 cellspacing='1' border='" . $border . "' class='sortable'>
	<thead>
	<tr class=rowheader>
	<th align=center>" . $_SESSION['lang']['nomor'] . "</th>
	<th  align=center>" . $_SESSION['lang']['notransaksi'] . "</th>    
	<th  align=center>" . $_SESSION['lang']['sumber'] . "</th>
	<th  align=center>" . $_SESSION['lang']['tanggal'] . "</th>
	<th  align=center>" . $_SESSION['lang']['kodeblok'] . "</th>
	<th  align=center>" . $_SESSION['lang']['kodekegiatan'] . "</th>       
	<th  align=center>" . $_SESSION['lang']['kegiatan'] . "</th>
	<th  align=center>" . $_SESSION['lang']['satuan'] . "</th>
	<th  align=center>" . $_SESSION['lang']['realisasi'] . "</th>
	
	<th align=center>" . $_SESSION['lang']['jumlahhk'] . "</th>
	<th  align=center>" . $_SESSION['lang']['upahkerja'] . "</th>
	<th align=center>" . $_SESSION['lang']['insentif'] . "</th>
	<th align=center>Grand Total</th>
	<th align=center>" . $_SESSION['lang']['kodebarang'] . "</th> 
	<th align=center>" . $_SESSION['lang']['namabarang'] . "</th>
	<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>  
	<th align=center>" . $_SESSION['lang']['satuan'] . "</th>     
	</tr></thead>
	<tbody>";
	}
	$nmkegiatan =  makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
	$satuan =  makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
	$nmbrg =  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
	$satuanbrg =  makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');


	if ($tipereport == 'perkaryawan') {

		/* //perkaryawan
	$namakaryawan[$bar2->nikpemel]['nama'] = $bar2->namakaryawan;
	$dataPerkaryawan[$bar2->nikpemel][$bar2->tanggal][$bar2->kodekegiatan]['tanggal'] = $bar2->tanggal;
	$dataPerkaryawan[$bar2->nikpemel][$bar2->tanggal][$bar2->kodekegiatan]['upah']+=$bar2->umr;
	$dataPerkaryawan[$bar2->nikpemel][$bar2->tanggal][$bar2->kodekegiatan]['insentif']+=$bar2->insentif;
	$dataPerkaryawan[$bar2->nikpemel][$bar2->tanggal][$bar2->kodekegiatan]['hasilkerja']+=$bar2->hasilkerja;
	$dataPerkaryawan[$bar2->nikpemel][$bar2->tanggal][$bar2->kodekegiatan]['jumlahhk']+=$bar2->jumlahhk;
	*/
		foreach ($dataPerkaryawan as $nikpemel => $listtanggal) {
			$thk = 0;
			$tupah = 0;
			$tpremi = 0;
			foreach ($listtanggal as $tgl => $listKegiatan) {
				foreach ($listKegiatan as $kodekeg => $data) {
					$noo += 1;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td align='center'>" . $noo . "</td>
				<td rowspan='" . $rowspannik . "'>" . @$namakaryawan[$nikpemel]['nama'] . "</td>";
					$stream .= "<td>" . tanggalnormal($tgl) . "</td>";
					$stream .= "<td>" . $kodekeg . "</td>";
					$stream .= "<td>" . $nmkegiatan[$kodekeg] . "</td>";
					$stream .= "<td align=center>" . $satuan[$kodekeg] . "</td>";
					$stream .= "<td align=center>" . $data['hasilkerja'] . "</td>";
					$stream .= "<td align=right>" . number_format($data['jumlahhk'], 2) . "</td>";
					$stream .= "<td align=right>" . number_format($data['upah']) . "</td>";
					$stream .= "<td align=right>" . number_format($data['insentif']) . "</td>";
					$ttdterima = $data['upah'] + $data['insentif'];
					$stream .= "<td align=right>" . number_format($ttdterima) . "</td>";
					$stream .= "<td align=right></td>";
					$stream .= "</tr>";

					$thslkrj += $data['hasilkerja'];
					$thk += $data['jumlahhk'];
					$tupah += $data['upah'];
					$tpremi += $data['insentif'];

					$tthk += $data['jumlahhk'];
					$ttupah += $data['upah'];
					$ttpremi += $data['insentif'];
				}
				// $thslkrj +=$data['hasilkerja'];
			}
			$stream .= "
		<tr class=rowcontent style='background-color:#E8DAEF;'>
			<td align=center colspan=7><b>S U B  T O T A L</b></td>			
			<td align=right>" . number_format($thk, 2) . "</td>
			<td align=right>" . number_format($tupah) . "</td>
			<td align=right>" . number_format($tpremi) . "</td>
			<td align=right>" . number_format($tupah + $tpremi) . "</td>
			<td align=right></td>
		</tr>";
		}

		$stream .= "
		<tr class=rowcontent style='background-color:#27ED1C;'>
			<td align=center colspan=7><b>T O T A L</b></td>
			<td align=right>" . number_format($tthk, 2) . "</td>
			<td align=right>" . number_format($ttupah) . "</td>
			<td align=right>" . number_format($ttpremi) . "</td>
			<td align=right>" . number_format($ttupah + $ttpremi) . "</td>
			<td align=right></td>
		</tr> 
		</tbody></table>";
	} else {

		$noo = 0;
		foreach ($data as $notrans => $valkeg) {
			foreach ($valkeg as $kodekeg => $valblok) {
				foreach ($valblok as $kodeblok) {
					// echo $kodeblok."<br>";
					$noo++;
					$optnmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $kodekeg . "'");
					$optsat = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan', "kodekegiatan='" . $kodekeg . "'");

					$stream .= "
				<tr class=rowcontent>
				<td align=center>" . $noo . "</td>
				<td>" . $notrans . "</td>    
				<td>" . $sumber . "</td>
				<td>" . tanggalnormal($tglxx[$notrans]) . "</td>
				<td>" . $getNamaIndukBlok[$kodeblok] . "</td>    
				<td>" . $kodekeg . "</td>    
				<td>" . @$optnmkeg[$kodekeg] . "</td>    
				<td>" . @$optsat[$kodekeg] . "</td>    
				<td align=right>" . number_format($jhasilkerja[$notrans][$kodekeg][$kodeblok], 2) . "</td>    
				<td align=right>" . number_format($jhk[$notrans][$kodekeg][$kodeblok], 2) . "</td>    
				<td align=right>" . number_format($upah[$notrans][$kodekeg][$kodeblok], 2) . "</td>    
				<td align=right>" . number_format($premi[$notrans][$kodekeg][$kodeblok], 2) . "</td>    
				<td align=right>" . number_format(($upah[$notrans][$kodekeg][$kodeblok] + $premi[$notrans][$kodekeg][$kodeblok]), 2) . "</td>    
				";
					@$thk += $jhk[$notrans][$kodekeg][$kodeblok];
					@$tjhasilkerja += $jhasilkerja[$notrans][$kodekeg][$kodeblok];
					@$tupah += $upah[$notrans][$kodekeg][$kodeblok];
					@$tgrandtotal += $upah[$notrans][$kodekeg][$kodeblok] + $premi[$notrans][$kodekeg][$kodeblok];
					@$tpremi += $premi[$notrans][$kodekeg][$kodeblok];


					$str = "select sum(kwantitas) as kwantitas, kodebarang from " . $dbname . ".kebun_pakaimaterial where 1=1 and notransaksi='" . $notrans . "' and kodeorg='" . $kodeblok . "' and kodekegiatan='" . $kodekeg . "' group by kodebarang";
					$res = fetchdata($str);
					$stream .= "<td>";
					foreach ($res as $bar) {
						$stream .= "" . $bar['kodebarang'] . " <br>";
					}
					$stream .= "</td>";
					$stream .= "<td>";
					foreach ($res as $bar) {
						$optnmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $bar['kodebarang'] . "'");
						$stream .= "" . @$optnmbrg[$bar['kodebarang']] . "<br>";
					}
					$stream .= "</td>";
					$stream .= "<td align=right>";
					foreach ($res as $bar) {
						$stream .= "" . hidezerodecimal($bar['kwantitas'], 2) . "<br>";
						$ttlbar += $bar['kwantitas'];
					}

					$stream .= "</td>";
					$stream .= "<td>";
					foreach ($res as $bar) {
						$optsatbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan', "kodebarang='" . $bar['kodebarang'] . "'");
						$stream .= "" . @$optsatbrg[$bar['kodebarang']] . "<br>";
					}
					$stream .= "</td>";
				}
			}
		}
		$stream .= "
	<tr class=rowcontent>
	<td align=center colspan=8><b>T O T A L</b></td>
	<td align=right>" . number_format($tjhasilkerja, 2) . "</td>
	<td align=right>" . number_format($thk, 2) . "</td>
	<td align=right>" . number_format($tupah) . "</td>
	<td align=right>" . number_format($tpremi) . "</td>
	<td align=right>" . number_format($tgrandtotal) . "</td>
        <td></td> 
        <td></td>
    <td align=right>" . number_format(@$ttlbar) . "</td>  
        <td></td>  
        </tbody></table>";
	}
}
switch ($proses) {
	case 'getAfdAll':
		$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi 
			where kodeorganisasi like '" . $kdAfd . "%' and length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') order by namaorganisasi
			";

		$op = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$op .= "<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
		}
		echo $op;
		exit();
		break;
	case 'getbarang':

		$str = "select * from " . $dbname . ".setup_kegiatannorma where kodekegiatan = '" . $kegiatan . "' and tipeanggaran='Material'";
		$res = fetchdata($str);
		$optDivisi = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		foreach ($res as $bar) {
			$nmbarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $bar['kodebarang'] . "'");
			$optDivisi .= "<option value=" . $bar['kodebarang'] . ">" . $bar['kodebarang'] . " - " . $nmbarang[$bar['kodebarang']] . "</option>";
		}
		echo $optDivisi;
		break;
	case 'preview':
		echo $stream;
		break;
	case 'excel':
		$stream .= "</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
		$dte = date("YmdHms");
		$nop_ = "Laporan_perawatan" . $kdAfd . $tgl1_ . "-" . $tgl2_ . "_" . date('YmdHis');
		if ($handle = opendir('tempExcel')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "index.html") {
					@unlink('tempExcel/' . $file);
				}
			}
			closedir($handle);
		}
		$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
		if (!fwrite($handle, $stream)) {
			echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
					</script>";
			exit;
		} else {
			echo "<script language=javascript1.2>
				window.location='tempExcel/" . $nop_ . ".xls';
				</script>";
		}
		closedir($handle);
		// $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		// gzwrite($gztralala, $stream);
		// gzclose($gztralala);
		// echo "<script language=javascript1.2>
		// window.location='tempExcel/".$nop_.".xls.gz';
		// </script>";            
		break;
}
