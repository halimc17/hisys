<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

include_once('lib/zPosting.php');
include_once('lib/zJournal.php');


$method = checkPostGet('method', '');
$param = $_POST;
$cparam = count($param);
if ($cparam == 0) {
	$param = $_GET;
}

$unit = checkPostGet('unit', '');
$pt = getNamaOrg($unit, 'induk');
$per = checkPostGet('per', '');
$tipe = checkPostGet('tipe', '');


$tmpPeriod = explode('-', $per);
$tahunbulan = implode("", $tmpPeriod);
$tahun = $tmpPeriod[0];
$bulan = $tmpPeriod[1];


$karyawanid = checkPostGet('karyawanid', '');

$nmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$optpt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
$namaunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$pt = $optpt[$unit];


$barang = array(
	'cpo' => '40000001',
	'pk' => '40000002',
	'tbs' => '40000003',
);

#= buat saldo akhir yg insert ke keu_5hppsaldo
$kgtbssalak = checkPostGet('kgtbssalak', '');
$rptbssalak = checkPostGet('rptbssalak', '');

$kgcposalak = checkPostGet('kgcposalak', '');
$rpcposalak = checkPostGet('rpcposalak', '');

$kgpksalak = checkPostGet('kgpksalak', '');
$rppksalak = checkPostGet('rppksalak', '');

$qtyjualcpo = checkPostGet('qtyjualcpo', '');
$rpjualcpo = checkPostGet('rpjualcpo', '');
$qtyjualpk = checkPostGet('qtyjualpk', '');
$rpjualpk = checkPostGet('rpjualpk', '');
$qtytbsext = checkPostGet('qtytbsext', '');
$rptbsext = checkPostGet('rptbsext', '');
$qtytbsint = checkPostGet('qtytbsint', '');
$rptbsint = checkPostGet('rptbsint', '');
$qtytbsolah = checkPostGet('qtytbsolah', '');
$rptbsolah = checkPostGet('rptbsolah', '');

$kodealokasi = 'HPPTBS';

#= ambil daftar unit untuk hpp
$str = "select  * from " . $dbname . ".organisasi where induk='" . $pt . "' and inti='1'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$arrunit[$bar['kodeorganisasi']] = $bar['kodeorganisasi'];
	if ($bar['tipe'] == 'PABRIK') {
		$arrunitpabrik[$bar['kodeorganisasi']] = $bar['kodeorganisasi'];
	} else if ($bar['tipe'] == 'KEBUN') {
		$arrunitkebun[$bar['kodeorganisasi']] = $bar['kodeorganisasi'];
	}
}
// print_r($arrunitkebun);

$stream = "";

$kodesegmentsawit = '0000000001';
$kodesegmentkaret = '0000000002';

switch ($method) {

	case 'preview':
		# Cek jika alokasi per PT sudah ada
		$cekAlokasiPt = getCountRows($dbname, "keu_5prosesalokasi", "namalaporan='{$kodealokasi}' AND kodeorg='{$pt}'");
		if ($cekAlokasiPt == 0) {
			exit("Warning: Setup Alokasi HPP untuk PT " . getNamaOrg($pt) . " belum ada.");
		}

		if ($pt == 'DMA' or $pt == 'MHA' or $pt == 'LAN') {
			$totalpanen = 0;
			$noakunjualtbs = array();
			$noakunbelitbs = array();
			#= Ambil Nilai Penjualan TBS
			$sql = "select sum(jumlah) as jumlah,noakun from " . $dbname . ".keu_jurnaldt_vw where 5=5 and noakun like '51101%' and periode='" . $per . "' and perusahaan = '" . $pt . "' group by noakun ";
			$res = fetchData($sql);

			foreach ($res as $val):
				$noakunjualtbs[$val['noakun']] = $val['jumlah'] * -1;
			endforeach;

			#= Ambil Nilai Pembelian TBS
			$sql = "select sum(jumlah) as jumlah,noakun from " . $dbname . ".keu_jurnaldt_vw where 5=5 and noakun like '641010%' and periode='" . $per . "' and perusahaan = '" . $pt . "' group by noakun ";
			$res = fetchData($sql);

			foreach ($res as $val):
				$noakunbelitbs[$val['noakun']] = $val['jumlah'];
			endforeach;

			#= Ambil Noakun persediaan TBS
			$sql = "select * from " . $dbname . ".keu_5akun where 5=5 and level='5' and namaakun LIKE '%TBS%' and noakun like '115%'";
			$res = fetchData($sql);

			foreach ($res as $val):
				$noakunpersediaantbs[$val['noakun']] = $val['noakun'];
			endforeach;

			#= Ambil Biaya Panen
			$nourutlaporan = 'B1';
			$whereAlokasi = "namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "' AND kodeorg='{$pt}'";
			$sql = "select sum(jumlah) as rupiah, kodeorg, noakun, left(noakun,5) as noakunhead from " . $dbname . ".keu_jurnaldt_vw where 5=5 and kodesegment='" . $kodesegmentsawit . "' and periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where {$whereAlokasi} AND kodeorg='{$pt}') and kodeorg in ('" . implode("','", $arrunit) . "') group by noakun";
			$res = fetchData($sql);

			foreach ($res as $val):
				$panendt[$val['noakunhead']][$val['noakun']] = $val['rupiah'];
				$totalpanen += $val['rupiah'];
			endforeach;

			#= Ambil Biaya Langsung
			$nourutlaporan = 'B2';
			$whereAlokasi = "namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "' AND kodeorg='{$pt}'";
			$sql = "select sum(jumlah) as rupiah, kodeorg, noakun, left(noakun,5) as noakunhead from " . $dbname . ".keu_jurnaldt_vw where 5=5 and kodesegment='" . $kodesegmentsawit . "' and periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where {$whereAlokasi} AND kodeorg='{$pt}') and kodeorg in ('" . implode("','", $arrunit) . "') group by noakun";
			$res = fetchData($sql);

			foreach ($res as $val):
				$rawatdt[$val['noakunhead']][$val['noakun']] = $val['rupiah'];
			endforeach;

			#= Ambil Biaya Tidak Langsung
			$nourutlaporan = 'B3';
			$whereAlokasi = "namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "' AND kodeorg='{$pt}'";
			$sql = "select sum(jumlah) as rupiah, kodeorg, noakun, left(noakun,5) as noakunhead from " . $dbname . ".keu_jurnaldt_vw where 5=5 and kodesegment='" . $kodesegmentsawit . "' and periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where {$whereAlokasi} AND kodeorg='{$pt}') and kodeorg in ('" . implode("','", $arrunit) . "') group by noakun";
			$res = fetchData($sql);

			foreach ($res as $val):
				$umumdt[$val['noakunhead']][$val['noakun']] = $val['rupiah'];
			endforeach;


			#= ambil saldo awal
			$str = "SELECT * FROM " . $dbname . ".keu_5hppsaldo where periode='" . $per . "' and kodeorg='" . $unit . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$kgawal[$bar['kodebarang']] = $bar['qtyawal'];
				$rpawal[$bar['kodebarang']] = $bar['rpawal'];
				$rpperkgawal[$bar['kodebarang']] = $bar['rpawal'] / $bar['qtyawal'];
			}

			#= Terima Tbs produksi
			$str = "select sum(jjgpanen) as jjgpanen from " . $dbname . ".kebun_rekappnn_vw where tanggal like '" . $per . "%' and substr(divisi,1,4) in  (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and tipe='KEBUN')";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				@$jjgb1 = $bar['jjgpanen'];
			}

			#= JUAL TBS
			$str = "select sum(jjg) as jjg,sum(kgnetto) as kgwb,sum(kgnetto)/sum(jjg) as bjr, sum(totalrp) as totalrp from " . $dbname . ".kebun_tbsjual where (tanggalpks like '" . $per . "%') and unit in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and tipe='KEBUN')";
			// echo $str;
			$res = fetchdata($str);
			foreach ($res as $bar) {
				@$kgwbjual += $bar['kgwb'];
				@$jjgdjual = $bar['jjg'];
				@$bjrjual = $bar['bjr'];
				@$rpjualtbs = $bar['totalrp'];
			}

			#= pengiriman kebun_spb_vw
			$str = "select left(divisi,4) as kodeorganisasi, sum(jjg) as jjg,sum(kgwb) as kgwb, sum(kgwbnetto) as kgwbnetto,sum(kgwb)/sum(jjg) as bjr, intiplasma from " . $dbname . ".kebun_spb_penjualan_vw where (tanggal like '" . $per . "%' or tanggal like '" . $per . "%') and substr(divisi,1,4) in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and tipe='KEBUN') group by left(divisi,4)";
			// echo $str;
			$res = fetchdata($str);
			foreach ($res as $bar) {
				@$jjgd = $bar['jjg'];
				@$bjr = $bar['bjr'];
				@$kgwb += $bar['kgwb'];
				@$kgwbnetto += $bar['kgwbnetto'];

				@$kgwbpembelian[$bar['inti']] += $bar['kgwb'];
				@$kgwbnettopembelian[$bar['inti']] += $bar['kgwbnetto'];
			}

			#= Get Potongan Pabrik
			$sql = "select sum(kgpotsortasi) as potongan from " . $dbname . ".pabrik_timbangan where 5=5 and millcode='EXTM' and kodebarang='40000003' and tanggal like '" . $per . "%'";
			$res = fetchData($sql);

			$res = fetchdata($sql);
			foreach ($res as $bar) {
				@$potongan = $bar['potongan'];
			}

			// #= b1 biaya tbs internal
			// $nourutlaporan='B1';
			// $str="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where kodesegment='".$kodesegmentsawit."' and periode='".$per."' and noakun in (select noakun from ".$dbname.".keu_5prosesalokasidt_akun where namalaporan='".$kodealokasi."' and nourut='".$nourutlaporan."') and kodeorg in ('".implode("','",$arrunit)."')";
			// $res=fetchdata($str);
			// foreach($res as $bar){
			// 	@$rpb1+=$bar['jumlah'];
			// }	

			// #= b2 biaya tbs afiliasi
			// $nourutlaporan='B2';
			// $str="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where kodesegment='".$kodesegmentsawit."' and periode='".$per."' and noakun in (select noakun from ".$dbname.".keu_5prosesalokasidt_akun where namalaporan='".$kodealokasi."' and nourut='".$nourutlaporan."') and kodeorg in ('".implode("','",$arrunit)."')";
			// $res=fetchdata($str);
			// foreach($res as $bar){
			// 	@$rpb2+=$bar['jumlah'];
			// }	

			// #= b3 query BTL
			// $nourutlaporan='B3';
			// $str="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw  where kodesegment='".$kodesegmentsawit."' and periode='".$per."' and noakun in (select noakun from ".$dbname.".keu_5prosesalokasidt_akun where namalaporan='".$kodealokasi."' and nourut='".$nourutlaporan."') and kodeorg in ('".implode("','",$arrunit)."')";
			// $res=fetchdata($str);
			// foreach($res as $bar){
			// 	@$rpb3+=$bar['jumlah'];
			// }	

			// $kgb1=$kgwb;
			// $kgb1=round($jjgb1*$bjr,0);

			// $rpb4=$rpb1+$rpb2+$rpb3;
			// $kgb4=$kgb1+$kgb2+$kgb3;


			if ($tipe == 'excel') {
				$border = 'border=1';
			} else {
				$border = '';
			}

			// $stream.= "<fieldset><legend>CPO & PK</legend>";
			$stream .= "<table class=sortable " . $border . " cellspacing=1 width=80%>";


			$colspan = '10';

			$stream .= "<thead>";
			$stream .= "<tr class=rowheader>";
			$stream .= "<th bgcolor=#CCCCCC align=center colspan=2 rowspan=3></th>";
			$stream .= "<th bgcolor=#CCCCCC align=center rowspan=3>" . $_SESSION['lang']['noakun'] . "</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center rowspan=3>" . $_SESSION['lang']['namaakun'] . "</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center colspan=6>" . $_SESSION['lang']['tbs'] . "</th>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowheader>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Bruto</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center colspan=2>Grading</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Netto</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center colspan=2>Perhitungan Harga Pokok TBS</th>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowheader>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Kg</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Kg</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>%</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Kg</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Rp/Kg</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Rupiah</th>";
			$stream .= "</tr>";

			$stream .= "</thead>";




			$kga = $kgawal['400000003'];
			$rpa = $rpawal['400000003'];

			foreach ($noakunpersediaantbs as $noakuntbs => $v):
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center style=width:25px;><b>A</b></td>";
				$stream .= "<td align=left><b>Nilai Saldo Awal Stok</b></td>";
				$stream .= "<td align=center>" . $noakuntbs . "</td>";
				$stream .= "<td align=left>" . $nmakun[$noakuntbs] . "</td>";
				$stream .= "<td align=center>" . hidezerodecimal(fixnan($kgwb), 2) . "</td>";
				$stream .= "<td align=center>" . hidezerodecimal(fixnan($potongan), 2) . "</td>";
				$stream .= "<td align=center>" . hidezerodecimal(fixnan($potongan / $kgwb), 2) . "</td>";
				$stream .= "<td align=center>" . hidezerodecimal(fixnan($kgwbnetto), 2) . "</td>";
				$stream .= "<td align=right>" . hidezerodecimal(fixnan($kga), 2) . "</td>";
				$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpa), 2) . "</td>";
				$stream .= "</tr>";
			endforeach;

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>B</b></td>";
			$stream .= "<td align=left><b>TBS Internal</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			#= Biaya Langsung
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>Biaya Langsung</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			#= btl kebun masukin ke tbs internal


			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>B.1</b></td>";
			$stream .= "<td align=left><b>Panen</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			// $stream.="<td align=right id=qtytbsint>".hidezerodecimal(fixnan($jjgb1),2)."</td>";  
			// $stream.="<td align=right id=qtytbsint>".hidezerodecimal(fixnan($bjr),2)."</td>";  
			// $stream.="<td align=right id=rptbsint>".hidezerodecimal(fixnan($kgb1),2)."</td>";  
			// $stream.="<td align=right id=rptbsint>".hidezerodecimal(fixnan($rpb1),2)."</td>";  
			$stream .= "<td align=right id=qtytbsint></td>";
			$stream .= "<td align=right id=qtytbsint></td>";
			$stream .= "<td align=right id=rptbsint></td>";
			$stream .= "<td align=right id=rptbsint><b>" . hidezerodecimal(fixnan($totalpanen), 2) . "</b></td>";
			$stream .= "</tr>";

			$nopnn = 0;
			foreach ($panendt as $noakunpnnhead => $rupiahpnnx):
				foreach ($rupiahpnnx as $noakunpnn => $rupiahpnn):
					$nopnn++;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td align=center style=width:25px;>" . $nopnn . "</td>";
					$stream .= "<td align=left></td>";
					$stream .= "<td align=center>" . $noakunpnn . "</td>";
					$stream .= "<td align=left>" . $nmakun[$noakunpnn] . "</td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right>" . hidezerodecimal(fixnan($rupiahpnn), 2) . "</td>";
					$stream .= "</tr>";

					# TOTAL RUPIAH PANEN PER 5 DIGIT
					$subtotalpnn[$noakunpnnhead] += $rupiahpnn;
				endforeach;
				# SUB TOTAL PER NOAKUN
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center style=width:25px;></td>";
				$stream .= "<td align=left></td>";
				$stream .= "<td align=center></td>";
				$stream .= "<td align=left><b>Sub Total " . $nmakun[$noakunpnnhead] . "</b></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalpnn[$noakunpnnhead]), 2) . "</b></td>";
				$stream .= "</tr>";

				$subtotalbypnn += $subtotalpnn[$noakunpnnhead];
			endforeach;

			#= SUBTOTAL BIAYA PANEN
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>Sub Total Biaya Panen</b></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalbypnn), 2) . "</b></td>";
			$stream .= "</tr>";


			$rpkgb2 = $rpb2 / $kgb2tbsbruto;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>B.2</b></td>";
			$stream .= "<td align=left><b>Perawatan</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpb2), 2) . "</td>";

			$stream .= "</tr>";

			$norwt = 0;
			foreach ($rawatdt as $noakunrwthead => $rupiahrwtx):
				foreach ($rupiahrwtx as $noakunrwt => $rupiahrwt):
					$norwt++;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td align=center style=width:25px;>" . $norwt . "</td>";
					$stream .= "<td align=left></td>";
					$stream .= "<td align=center>" . $noakunrwt . "</td>";
					$stream .= "<td align=left>" . $nmakun[$noakunrwt] . "</td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right>" . hidezerodecimal(fixnan($rupiahrwt), 2) . "</td>";
					$stream .= "</tr>";

					# TOTAL RUPIAH PANEN PER 5 DIGIT
					$subtotalrwt[$noakunrwthead] += $rupiahrwt;
				endforeach;
				# SUB TOTAL PER NOAKUN
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center style=width:25px;></td>";
				$stream .= "<td align=left></td>";
				$stream .= "<td align=center></td>";
				$stream .= "<td align=left><b>Sub Total " . $nmakun[$noakunrwthead] . "</b></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalrwt[$noakunrwthead]), 2) . "</b></td>";
				$stream .= "</tr>";

				$subtotalbyrwt += $subtotalrwt[$noakunrwthead];
			endforeach;

			#= SUBTOTAL BIAYA PANEN
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>Sub Total Biaya TM</b></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalbyrwt), 2) . "</b></td>";
			$stream .= "</tr>";

			#= TOTAL BIAYA LANGSUNG (RAWAT + PANEN)
			$totalbiayalangsung = ($subtotalbypnn + $subtotalbyrwt);
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>TOTAL BIAYA LANGSUNG</b></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($totalbiayalangsung), 2) . "</b></td>";
			$stream .= "</tr>";

			$rpkgb3 = $rpb3 / $kgb3tbsbruto;

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";

			#=========================#
			# BIAYA TIDAK LANGSUNG
			#=========================#
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>Biaya Tidak Langsung</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			$noumm = 0;
			foreach ($umumdt as $noakunummhead => $rupiahummx):
				foreach ($rupiahummx as $noakunumm => $rupiahumm):
					$noumm++;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td align=center style=width:25px;>" . $noumm . "</td>";
					$stream .= "<td align=left></td>";
					$stream .= "<td align=center>" . $noakunumm . "</td>";
					$stream .= "<td align=left>" . $nmakun[$noakunumm] . "</td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right>" . hidezerodecimal(fixnan($rupiahumm), 2) . "</td>";
					$stream .= "</tr>";

					# TOTAL RUPIAH PANEN PER 5 DIGIT
					$subtotalumm[$noakunummhead] += $rupiahumm;
				endforeach;
				# SUB TOTAL PER NOAKUN
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center style=width:25px;></td>";
				$stream .= "<td align=left></td>";
				$stream .= "<td align=center></td>";
				$stream .= "<td align=left><b>Sub Total " . $nmakun[$noakunummhead] . "</b></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalumm[$noakunummhead]), 2) . "</b></td>";
				$stream .= "</tr>";

				$subtotalbyumm += $subtotalumm[$noakunummhead];
			endforeach;

			#= SUBTOTAL BIAYA TIDAK LANGSUNG
			$totalbiayatidaklangsung = $subtotalbyumm;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>Sub Total Biaya Tidak Langsung</b></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalbyumm), 2) . "</b></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";

			# TOTAL BIAYA
			$totalbiaya = ($totalbiayalangsung + $totalbiayatidaklangsung);
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>TOTAL BIAYA TBS INTERNAL <br/> (Total Biaya Langsung + Tidak Langsung)</b></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($totalbiaya), 2) . "</b></td>";
			$stream .= "</tr>";


			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";


			$kgc = $kga + $kgb4;
			$rpc = $rpa + $rpb4;
			$rpkgc = $rpc / $kgc;

			// echo "<pre>";
			// print_r($);

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>C</b></td>";
			$stream .= "<td align=left><b>Pembelian TBS</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			// $stream.="<td align=right>".hidezerodecimal(fixnan($kgc),2)."</td>";  
			// $stream.="<td align=right>".hidezerodecimal(fixnan($rpc),2)."</td>";  
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			# TBS Afiliasi
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>1</b></td>";
			$stream .= "<td align=left><b>TBS Afiliasi</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center>6410102</td>";
			$stream .= "<td align=left>" . $nmakun['6410102'] . "</td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($noakunbelitbs['6410102']), 2) . "</td>";
			$stream .= "</tr>";

			# TBS KUD Plasma
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>2</b></td>";
			$stream .= "<td align=left><b>TBS KUD Plasma</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center>6410101</td>";
			$stream .= "<td align=left>" . $nmakun['6410101'] . "</td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($noakunbelitbs['6410101']), 2) . "</td>";
			$stream .= "</tr>";

			# TBS KUD Plasma
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>3</b></td>";
			$stream .= "<td align=left><b>TBS External</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center>6410103</td>";
			$stream .= "<td align=left>" . $nmakun['6410103'] . "</td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($noakunbelitbs['6410103']), 2) . "</td>";
			$stream .= "</tr>";


			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";

			$rpkgd = $rpkgc;
			if ($kgwbjual <= 0) {
				$kgd = $jjgd * $bjr;
				$rpd = $noakunjualtbs['5110101'] - $noakunjualtbs['5110102'];
			} else {
				$kgd = $jjgd * $bjr;
				$rpd = $noakunjualtbs['5110101'] - $noakunjualtbs['5110102'];
				// $jjgd=$jjgdjual;
				// $kgd=$kgwbjual;
				// $bjr=$bjrjual;
				// $rpd=$rpjualtbs;
			}

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>D</b></td>";
			$stream .= "<td align=left><b>TBS Dikirim</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($jjgd), 2) . "</td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($bjr), 2) . "</td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgd), 2) . "</td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpd), 2) . "</td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";


			$kge = $kgc - $kgd;
			$rpc =($noakunbelitbs['6410103'] + $noakunbelitbs['6410102'] + $noakunbelitbs['6410101'] + $totalbiaya);
			$rpe = $rpd-($noakunbelitbs['6410103'] + $noakunbelitbs['6410102'] + $noakunbelitbs['6410101'] + $totalbiaya);
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>E</b></td>";
			$stream .= "<td align=left><b>TBS Terkirim - Harga Pokok Produksi</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right id=kgtbssalak>" . hidezerodecimal(fixnan($kge), 2) . "</td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpe / $kge), 2) . "</td>";
			$stream .= "<td align=right id=rptbssalak>" . hidezerodecimal(fixnan($rpe), 2) . "</td>";
			// $stream.="<td align=right id=kgtbssalak></td>";  
			// $stream.="<td align=right id=rptbssalak></td>"; 

			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";


			#===============================================================================================
			#= jurnal
			#===============================================================================================



			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center><b>Z</b></td>";
			$stream .= "<td align=left><b>Jurnal</b></td>";
			$stream .= "<td align=center><b>" . $_SESSION['lang']['noakun'] . "</b></td>";
			$stream .= "<td align=center><b>" . $_SESSION['lang']['namaakun'] . "</b></td>";
			$stream .= "<td align=center><b>" . $_SESSION['lang']['debet'] . "</b></td>";
			$stream .= "<td align=center><b>" . $_SESSION['lang']['kredit'] . "</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";




			#============================================================================
			#= jurnal 1 hpp
			#============================================================================

			$nilairp = 0;
			for ($i = 1; $i <= 10; $i++) {
				$nilairp++;
				$rp[$i] = $nilairp;
			}



			#= masukin nilai, sementara manual dulu
			$rpdebet['1'] = $rpc;
			$rpkredit['2'] = ($rpc-$totalbiayatidaklangsung);
			$rpkredit['3'] = $totalbiayatidaklangsung;
			$rpdebet['4'] = $rpd;
			$rpkredit['5'] = $rpd;
			$rpdebet['6'] = $rpe;
			$rpkredit['7'] = $rpe;
			// $rpdebet['1']=$rpkredit['2']=$rpacpo;
			// $rpdebet['3']=$rpkredit['4']=$rpicpo;
			// $rpdebet['5']=$rpkredit['6']=$rpapk;
			// $rpdebet['7']=$rpkredit['8']=$rpipk;
			// $rpdebet['9']=$rpkredit['10']=$rpatbsrestan;
			// $rpdebet['11']=$rpkredit['12']=$rpjtbs;
			// $rpdebet['13']=$rpkredit['14']=$rpe2cpo;
			// $rpdebet['15']=$rpkredit['16']=$rpkcpo;
			// $rpdebet['17']=$rpkredit['18']=$rpe2pk;
			// $rpdebet['19']=$rpkredit['20']=$rpkpk;
		} else {
			$totalpanen = 0;

			#= Ambil Noakun persediaan TBS
			$sql = "select * from " . $dbname . ".keu_5akun where 5=5 and level='5' and namaakun LIKE '%TBS%' and noakun like '115%'";
			$res = fetchData($sql);

			foreach ($res as $val):
				$noakunpersediaantbs[$val['noakun']] = $val['noakun'];
			endforeach;

			#= Ambil Biaya Panen
			$nourutlaporan = 'B1';
			$whereAlokasi = "namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "' AND kodeorg='{$pt}'";
			$sql = "select sum(jumlah) as rupiah, kodeorg, noakun, left(noakun,5) as noakunhead from " . $dbname . ".keu_jurnaldt_vw where 5=5 and kodesegment='" . $kodesegmentsawit . "' and periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where {$whereAlokasi} AND kodeorg='{$pt}') and kodeorg in ('" . implode("','", $arrunit) . "') group by noakun";
			$res = fetchData($sql);

			foreach ($res as $val):
				$panendt[$val['noakunhead']][$val['noakun']] = $val['rupiah'];
				$totalpanen += $val['rupiah'];
			endforeach;

			#= Ambil Biaya Langsung
			$nourutlaporan = 'B2';
			$whereAlokasi = "namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "' AND kodeorg='{$pt}'";
			$sql = "select sum(jumlah) as rupiah, kodeorg, noakun, left(noakun,5) as noakunhead from " . $dbname . ".keu_jurnaldt_vw where 5=5 and kodesegment='" . $kodesegmentsawit . "' and periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where {$whereAlokasi} AND kodeorg='{$pt}') and kodeorg in ('" . implode("','", $arrunit) . "') group by noakun";
			$res = fetchData($sql);

			foreach ($res as $val):
				$rawatdt[$val['noakunhead']][$val['noakun']] = $val['rupiah'];
			endforeach;

			#= Ambil Biaya Tidak Langsung
			$nourutlaporan = 'B3';
			$whereAlokasi = "namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "' AND kodeorg='{$pt}'";
			$sql = "select sum(jumlah) as rupiah, kodeorg, noakun, left(noakun,5) as noakunhead from " . $dbname . ".keu_jurnaldt_vw where 5=5 and kodesegment='" . $kodesegmentsawit . "' and periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where {$whereAlokasi} AND kodeorg='{$pt}') and kodeorg in ('" . implode("','", $arrunit) . "') group by noakun";
			$res = fetchData($sql);

			foreach ($res as $val):
				$umumdt[$val['noakunhead']][$val['noakun']] = $val['rupiah'];
			endforeach;


			#= ambil saldo awal
			$str = "SELECT * FROM " . $dbname . ".keu_5hppsaldo where periode='" . $per . "' and kodeorg='" . $unit . "'";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				$kgawal[$bar['kodebarang']] = $bar['qtyawal'];
				$rpawal[$bar['kodebarang']] = $bar['rpawal'];
				$rpperkgawal[$bar['kodebarang']] = $bar['rpawal'] / $bar['qtyawal'];
			}

			#= Terima Tbs produksi
			$str = "select sum(jjgpanen) as jjgpanen from " . $dbname . ".kebun_rekappnn_vw where tanggal like '" . $per . "%' and substr(divisi,1,4) in  (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and tipe='KEBUN')";
			$res = fetchdata($str);
			foreach ($res as $bar) {
				@$jjgb1 = $bar['jjgpanen'];
			}

			#= JUAL TBS
			$str = "select sum(jjg) as jjg,sum(kgnetto) as kgwb,sum(kgnetto)/sum(jjg) as bjr, sum(totalrp) as totalrp from " . $dbname . ".kebun_tbsjual where (tanggalpks like '" . $per . "%') and unit in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and tipe='KEBUN')";
			// echo $str;
			$res = fetchdata($str);
			foreach ($res as $bar) {
				@$kgwbjual += $bar['kgwb'];
				@$jjgdjual = $bar['jjg'];
				@$bjrjual = $bar['bjr'];
				@$rpjualtbs = $bar['totalrp'];
			}

			#= pengiriman kebun_spb_vw
			$str = "select left(divisi,4) as kodeorganisasi, sum(jjg) as jjg,sum(kgwb) as kgwb, sum(kgwbnetto) as kgwbnetto,sum(kgwb)/sum(jjg) as bjr, intiplasma from " . $dbname . ".kebun_spb_penjualan_vw where (tanggal like '" . $per . "%' or tanggal like '" . $per . "%') and substr(divisi,1,4) in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and tipe='KEBUN') group by left(divisi,4)";
			// echo $str;
			$res = fetchdata($str);
			foreach ($res as $bar) {
				@$jjgd = $bar['jjg'];
				@$bjr = $bar['bjr'];
				@$kgwb += $bar['kgwb'];
				@$kgwbnetto += $bar['kgwbnetto'];

				@$kgwbpembelian[$bar['inti']] += $bar['kgwb'];
				@$kgwbnettopembelian[$bar['inti']] += $bar['kgwbnetto'];
			}

			#= Get Potongan Pabrik
			$sql = "select sum(kgpotsortasi) as potongan from " . $dbname . ".pabrik_timbangan where 5=5 and millcode='EXTM' and kodebarang='40000003' and tanggal like '" . $per . "%'";
			$res = fetchData($sql);

			$res = fetchdata($sql);
			foreach ($res as $bar) {
				@$potongan = $bar['potongan'];
			}

			// #= b1 biaya tbs internal
			// $nourutlaporan='B1';
			// $str="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where kodesegment='".$kodesegmentsawit."' and periode='".$per."' and noakun in (select noakun from ".$dbname.".keu_5prosesalokasidt_akun where namalaporan='".$kodealokasi."' and nourut='".$nourutlaporan."') and kodeorg in ('".implode("','",$arrunit)."')";
			// $res=fetchdata($str);
			// foreach($res as $bar){
			// 	@$rpb1+=$bar['jumlah'];
			// }	

			// #= b2 biaya tbs afiliasi
			// $nourutlaporan='B2';
			// $str="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where kodesegment='".$kodesegmentsawit."' and periode='".$per."' and noakun in (select noakun from ".$dbname.".keu_5prosesalokasidt_akun where namalaporan='".$kodealokasi."' and nourut='".$nourutlaporan."') and kodeorg in ('".implode("','",$arrunit)."')";
			// $res=fetchdata($str);
			// foreach($res as $bar){
			// 	@$rpb2+=$bar['jumlah'];
			// }	

			// #= b3 query BTL
			// $nourutlaporan='B3';
			// $str="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw  where kodesegment='".$kodesegmentsawit."' and periode='".$per."' and noakun in (select noakun from ".$dbname.".keu_5prosesalokasidt_akun where namalaporan='".$kodealokasi."' and nourut='".$nourutlaporan."') and kodeorg in ('".implode("','",$arrunit)."')";
			// $res=fetchdata($str);
			// foreach($res as $bar){
			// 	@$rpb3+=$bar['jumlah'];
			// }	

			// $kgb1=$kgwb;
			// $kgb1=round($jjgb1*$bjr,0);

			// $rpb4=$rpb1+$rpb2+$rpb3;
			// $kgb4=$kgb1+$kgb2+$kgb3;


			if ($tipe == 'excel') {
				$border = 'border=1';
			} else {
				$border = '';
			}

			// $stream.= "<fieldset><legend>CPO & PK</legend>";
			$stream .= "<table class=sortable " . $border . " cellspacing=1 width=80%>";


			$colspan = '10';

			$stream .= "<thead>";
			$stream .= "<tr class=rowheader>";
			$stream .= "<th bgcolor=#CCCCCC align=center colspan=2 rowspan=3></th>";
			$stream .= "<th bgcolor=#CCCCCC align=center rowspan=3>" . $_SESSION['lang']['noakun'] . "</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center rowspan=3>" . $_SESSION['lang']['namaakun'] . "</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center colspan=6>" . $_SESSION['lang']['tbs'] . "</th>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowheader>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Bruto</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center colspan=2>Grading</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Netto</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center colspan=2>Perhitungan Harga Pokok TBS</th>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowheader>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Kg</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Kg</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>%</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Kg</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Rp/Kg</th>";
			$stream .= "<th bgcolor=#CCCCCC align=center>Rupiah</th>";
			$stream .= "</tr>";

			$stream .= "</thead>";




			$kga = $kgawal['400000003'];
			$rpa = $rpawal['400000003'];

			foreach ($noakunpersediaantbs as $noakuntbs => $v):
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center style=width:25px;><b>A</b></td>";
				$stream .= "<td align=left><b>Nilai Saldo Awal Stok</b></td>";
				$stream .= "<td align=center>" . $noakuntbs . "</td>";
				$stream .= "<td align=left>" . $nmakun[$noakuntbs] . "</td>";
				$stream .= "<td align=center>" . hidezerodecimal(fixnan($kgwb), 2) . "</td>";
				$stream .= "<td align=center>" . hidezerodecimal(fixnan($potongan), 2) . "</td>";
				$stream .= "<td align=center>" . hidezerodecimal(fixnan($potongan / $kgwb), 2) . "</td>";
				$stream .= "<td align=center>" . hidezerodecimal(fixnan($kgwbnetto), 2) . "</td>";
				$stream .= "<td align=right>" . hidezerodecimal(fixnan($kga), 2) . "</td>";
				$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpa), 2) . "</td>";
				$stream .= "</tr>";
			endforeach;

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>B</b></td>";
			$stream .= "<td align=left><b>TBS Internal</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			#= Biaya Langsung
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>Biaya Langsung</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			#= btl kebun masukin ke tbs internal


			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>B.1</b></td>";
			$stream .= "<td align=left><b>Panen</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			// $stream.="<td align=right id=qtytbsint>".hidezerodecimal(fixnan($jjgb1),2)."</td>";  
			// $stream.="<td align=right id=qtytbsint>".hidezerodecimal(fixnan($bjr),2)."</td>";  
			// $stream.="<td align=right id=rptbsint>".hidezerodecimal(fixnan($kgb1),2)."</td>";  
			// $stream.="<td align=right id=rptbsint>".hidezerodecimal(fixnan($rpb1),2)."</td>";  
			$stream .= "<td align=right id=qtytbsint></td>";
			$stream .= "<td align=right id=qtytbsint></td>";
			$stream .= "<td align=right id=rptbsint></td>";
			$stream .= "<td align=right id=rptbsint><b>" . hidezerodecimal(fixnan($totalpanen), 2) . "</b></td>";
			$stream .= "</tr>";

			$nopnn = 0;
			foreach ($panendt as $noakunpnnhead => $rupiahpnnx):
				foreach ($rupiahpnnx as $noakunpnn => $rupiahpnn):
					$nopnn++;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td align=center style=width:25px;>" . $nopnn . "</td>";
					$stream .= "<td align=left></td>";
					$stream .= "<td align=center>" . $noakunpnn . "</td>";
					$stream .= "<td align=left>" . $nmakun[$noakunpnn] . "</td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right>" . hidezerodecimal(fixnan($rupiahpnn), 2) . "</td>";
					$stream .= "</tr>";

					# TOTAL RUPIAH PANEN PER 5 DIGIT
					$subtotalpnn[$noakunpnnhead] += $rupiahpnn;
				endforeach;
				# SUB TOTAL PER NOAKUN
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center style=width:25px;></td>";
				$stream .= "<td align=left></td>";
				$stream .= "<td align=center></td>";
				$stream .= "<td align=left><b>Sub Total " . $nmakun[$noakunpnnhead] . "</b></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalpnn[$noakunpnnhead]), 2) . "</b></td>";
				$stream .= "</tr>";

				$subtotalbypnn += $subtotalpnn[$noakunpnnhead];
			endforeach;

			#= SUBTOTAL BIAYA PANEN
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>Sub Total Biaya Panen</b></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalbypnn), 2) . "</b></td>";
			$stream .= "</tr>";


			$rpkgb2 = $rpb2 / $kgb2tbsbruto;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>B.2</b></td>";
			$stream .= "<td align=left><b>Perawatan</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpb2), 2) . "</td>";

			$stream .= "</tr>";

			$norwt = 0;
			foreach ($rawatdt as $noakunrwthead => $rupiahrwtx):
				foreach ($rupiahrwtx as $noakunrwt => $rupiahrwt):
					$norwt++;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td align=center style=width:25px;>" . $norwt . "</td>";
					$stream .= "<td align=left></td>";
					$stream .= "<td align=center>" . $noakunrwt . "</td>";
					$stream .= "<td align=left>" . $nmakun[$noakunrwt] . "</td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right>" . hidezerodecimal(fixnan($rupiahrwt), 2) . "</td>";
					$stream .= "</tr>";

					# TOTAL RUPIAH PANEN PER 5 DIGIT
					$subtotalrwt[$noakunrwthead] += $rupiahrwt;
				endforeach;
				# SUB TOTAL PER NOAKUN
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center style=width:25px;></td>";
				$stream .= "<td align=left></td>";
				$stream .= "<td align=center></td>";
				$stream .= "<td align=left><b>Sub Total " . $nmakun[$noakunrwthead] . "</b></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalrwt[$noakunrwthead]), 2) . "</b></td>";
				$stream .= "</tr>";

				$subtotalbyrwt += $subtotalrwt[$noakunrwthead];
			endforeach;

			#= SUBTOTAL BIAYA PANEN
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>Sub Total Biaya TM</b></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalbyrwt), 2) . "</b></td>";
			$stream .= "</tr>";

			#= TOTAL BIAYA LANGSUNG (RAWAT + PANEN)
			$totalbiayalangsung = ($subtotalbypnn + $subtotalbyrwt);
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>TOTAL BIAYA LANGSUNG</b></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($totalbiayalangsung), 2) . "</b></td>";
			$stream .= "</tr>";

			$rpkgb3 = $rpb3 / $kgb3tbsbruto;

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";

			#=========================#
			# BIAYA TIDAK LANGSUNG
			#=========================#
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>Biaya Tidak Langsung</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			$noumm = 0;
			foreach ($umumdt as $noakunummhead => $rupiahummx):
				foreach ($rupiahummx as $noakunumm => $rupiahumm):
					$noumm++;
					$stream .= "<tr class=rowcontent>";
					$stream .= "<td align=center style=width:25px;>" . $noumm . "</td>";
					$stream .= "<td align=left></td>";
					$stream .= "<td align=center>" . $noakunumm . "</td>";
					$stream .= "<td align=left>" . $nmakun[$noakunumm] . "</td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right></td>";
					$stream .= "<td align=right>" . hidezerodecimal(fixnan($rupiahumm), 2) . "</td>";
					$stream .= "</tr>";

					# TOTAL RUPIAH PANEN PER 5 DIGIT
					$subtotalumm[$noakunummhead] += $rupiahumm;
				endforeach;
				# SUB TOTAL PER NOAKUN
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center style=width:25px;></td>";
				$stream .= "<td align=left></td>";
				$stream .= "<td align=center></td>";
				$stream .= "<td align=left><b>Sub Total " . $nmakun[$noakunummhead] . "</b></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right></td>";
				$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalumm[$noakunummhead]), 2) . "</b></td>";
				$stream .= "</tr>";

				$subtotalbyumm += $subtotalumm[$noakunummhead];
			endforeach;

			#= SUBTOTAL BIAYA TIDAK LANGSUNG
			$totalbiayatidaklangsung = $subtotalbyumm;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>Sub Total Biaya Tidak Langsung</b></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($subtotalbyumm), 2) . "</b></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";

			# TOTAL BIAYA
			$totalbiaya = ($totalbiayalangsung + $totalbiayatidaklangsung);
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left><b>TOTAL BIAYA TBS INTERNAL <br/> (Total Biaya Langsung + Tidak Langsung)</b></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right><b>" . hidezerodecimal(fixnan($totalbiaya), 2) . "</b></td>";
			$stream .= "</tr>";


			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";


			$kgc = $kga + $kgb4;
			$rpc = $rpa + $rpb4;
			$rpkgc = $rpc / $kgc;

			// echo "<pre>";
			// print_r($);

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>C</b></td>";
			$stream .= "<td align=left><b>Pembelian TBS</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			// $stream.="<td align=right>".hidezerodecimal(fixnan($kgc),2)."</td>";  
			// $stream.="<td align=right>".hidezerodecimal(fixnan($rpc),2)."</td>";  
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			# TBS Afiliasi
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>1</b></td>";
			$stream .= "<td align=left><b>TBS Afiliasi</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center>6410102</td>";
			$stream .= "<td align=left>" . $nmakun['6410102'] . "</td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			# TBS KUD Plasma
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>2</b></td>";
			$stream .= "<td align=left><b>TBS KUD Plasma</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center>6410101</td>";
			$stream .= "<td align=left>" . $nmakun['6410101'] . "</td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			# TBS KUD Plasma
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>3</b></td>";
			$stream .= "<td align=left><b>TBS External</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=center>6410103</td>";
			$stream .= "<td align=left>" . $nmakun['6410103'] . "</td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";


			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";

			$rpkgd = $rpkgc;
			if ($kgwbjual <= 0) {
				$kgd = $jjgd * $bjr;
				$rpd = $kgd * $rpkgd;
			} else {
				$kgd = $jjgd * $bjr;
				$rpd = $kgd * $rpkgd;
				// $jjgd=$jjgdjual;
				// $kgd=$kgwbjual;
				// $bjr=$bjrjual;
				// $rpd=$rpjualtbs;
			}

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>D</b></td>";
			$stream .= "<td align=left><b>TBS Tersedia</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			// $stream.="<td align=right>".hidezerodecimal(fixnan($jjgd),2)."</td>";  
			// $stream.="<td align=right>".hidezerodecimal(fixnan($bjr),2)."</td>";  
			// $stream.="<td align=right>".hidezerodecimal(fixnan($kgd),2)."</td>";  
			// $stream.="<td align=right>".hidezerodecimal(fixnan($rpd),2)."</td>";  
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";


			$kge = $kgc - $kgd;
			$rpe = $rpc - $rpd;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center style=width:25px;><b>E</b></td>";
			$stream .= "<td align=left><b>TBS Terkirim - Harga Pokok Produksi</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgwb), 2) . "</td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right id=kgtbssalak>" . hidezerodecimal(fixnan($kgwbnetto), 2) . "</td>";
			$stream .= "<td align=right>" . hidezerodecimal(fixnan($totalbiaya / $kgwbnetto), 2) . "</td>";
			$stream .= "<td align=right id=rptbssalak>" . hidezerodecimal(fixnan($totalbiaya), 2) . "</td>";
			// $stream.="<td align=right id=kgtbssalak></td>";  
			// $stream.="<td align=right id=rptbssalak></td>"; 

			$stream .= "</tr>";

			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
			$stream .= "</tr>";


			#===============================================================================================
			#= jurnal
			#===============================================================================================



			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=center><b>Z</b></td>";
			$stream .= "<td align=left><b>Jurnal</b></td>";
			$stream .= "<td align=center><b>" . $_SESSION['lang']['noakun'] . "</b></td>";
			$stream .= "<td align=center><b>" . $_SESSION['lang']['namaakun'] . "</b></td>";
			$stream .= "<td align=center><b>" . $_SESSION['lang']['debet'] . "</b></td>";
			$stream .= "<td align=center><b>" . $_SESSION['lang']['kredit'] . "</b></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "<td align=right></td>";
			$stream .= "</tr>";




			#============================================================================
			#= jurnal 1 hpp
			#============================================================================

			$nilairp = 0;
			for ($i = 1; $i <= 10; $i++) {
				$nilairp++;
				$rp[$i] = $nilairp;
			}



			#= masukin nilai, sementara manual dulu
			$rpdebet['1'] = $totalbiaya;
			$rpkredit['2'] = $totalbiayalangsung;
			$rpkredit['3'] = $totalbiayatidaklangsung;
			$rpdebet['4'] = $totalbiaya;
			$rpkredit['5'] = $totalbiaya;
			// $rpdebet['1']=$rpkredit['2']=$rpacpo;
			// $rpdebet['3']=$rpkredit['4']=$rpicpo;
			// $rpdebet['5']=$rpkredit['6']=$rpapk;
			// $rpdebet['7']=$rpkredit['8']=$rpipk;
			// $rpdebet['9']=$rpkredit['10']=$rpatbsrestan;
			// $rpdebet['11']=$rpkredit['12']=$rpjtbs;
			// $rpdebet['13']=$rpkredit['14']=$rpe2cpo;
			// $rpdebet['15']=$rpkredit['16']=$rpkcpo;
			// $rpdebet['17']=$rpkredit['18']=$rpe2pk;
			// $rpdebet['19']=$rpkredit['20']=$rpkpk;	
		}


		$arrht = "###kurs###jumlah###notransaksi###tipetransaksi###kodeorg###noakun###tanggal###bayarkepada###keterangan###matauang###autokb###noakun2###namapenerima###norekpenerima###rekening###norekap";

		##= alokasi
		$nodata = 0;
		$arrht = "";
		$str = "select * from " . $dbname . ".keu_5prosesalokasidt where namalaporan='" . $kodealokasi . "' and nourut like 'Z%'  AND kodeorg='{$pt}' order by nourut asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nodata++;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=right>" . $bar['nourut'] . "</td>";
			$stream .= "<td align=left id=keterangan" . $nodata . ">" . $bar['keterangandisplay'] . "</td>";
			$stream .= "<td align=center id=akun" . $nodata . ">" . $bar['noakun'] . "</td>";
			$stream .= "<td align=left>" . $nmakun[$bar['noakun']] . "</td>";
			$stream .= "<td align=right id=rpdebet" . $nodata . ">" . hidezerodecimal(fixnan($rpdebet[$nodata]), 2) . "</td>";
			$stream .= "<td align=right id=rpkredit" . $nodata . ">" . hidezerodecimal(fixnan($rpkredit[$nodata]), 2) . "</td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=left></td>";

			$stream .= "</tr>";
			$arrht .= "###akun" . $nodata . "###rpdebet" . $nodata . "###rpkredit" . $nodata . "###keterangan" . $nodata . "";
		}
		$arrht .= "###kgtbssalak###rptbssalak";
		// $arrht.="###"
		// echo $arrht;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td colspan=" . $colspan . "><button class=mybutton onclick=savehpp('" . $arrht . "','" . $nodata . "')>" . $_SESSION['lang']['proses'] . "</button><br></td>";
		$stream .= "</tr>";
		$stream .= "</table>";



		if ($tipe == 'excel') {
			$tglSkrg = date("Ymd");
			$nop_ = "laporan_hpp_" . $unit . "_" . $per;
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/" . $nop_ . ".xls';
					</script>";
				}
				fclose($handle);
			}
		} else {
			echo $stream;
		}

		break;


	case 'savehpp':

		// echo"<pre>";
		// print_r($param);
		// echo"</pre>";
		// exit("Error:MASUK");

		#= cek unit dibawah PT sudah closing selain HO
		$unitbelumclose = '';
		$nounitbelumclose = 0;
		$str = "select * from " . $dbname . ".setup_periodeakuntansi where   periode='" . $per . "' and kodeorg in ('" . implode("','", $arrunit) . "') and kodeorg!='" . $unit . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($bar['tutupbuku'] == 0) {
				// exit("Warning:Unit ".$namaunit[$bar['kodeorg']]." belum closing ");
				$unitbelumclose .= " Unit " . $namaunit[$bar['kodeorg']] . " belum closing ";
				$nounitbelumclose++;
			}
		}

		// if($nounitbelumclose>0){
		// echo $unitbelumclose;
		// exit("Warning:");
		// }

		#= cek sudah tutup buku / belum
		$str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where 
			  periode='" . $per . "' and kodeorg='" . $unit . "'";
		//echo $str."____";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tutupbuku = $bar['tutupbuku'];
		}
		if ($tutupbuku == 1) {
			exit("Warning:Periode ini sudah ditutup");
		}

		$lastDay = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
		$kodeJurnal = 'HPPT';
		$nojurnal = $tahunbulan . $lastDay . '/' . $unit . '/' . $kodeJurnal . '/001';
		$tanggalJurnal = $per . '-' . $lastDay;
		$noUrut = 1;
		$noRef = $kodeJurnal . '/' . $unit . '/' . $tahunbulan;

		$kodesegmentsawit = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

		$str = deleteQuery($dbname, 'keu_jurnalht', "nojurnal = '" . $nojurnal . "'");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		// exit("Error:".$nojurnal._.$tanggalJurnal._.$noRef);

		#bentuk jurnal

		// Prepare Data Header
		$dataResHPP['header'] = $dataResHPP['detail'] = array();

		$dataResHPP['header'] = array(
			'nojurnal' => $nojurnal,
			'kodejurnal' => $kodeJurnal,
			'tanggal' => $tanggalJurnal,
			'tanggalentry' => date('Ymd'),
			'posting' => '0',
			'totaldebet' => '0',
			'totalkredit' => '0',
			'amountkoreksi' => '0',
			'noreferensi' => $noRef,
			'autojurnal' => '1',
			'matauang' => 'IDR',
			'kurs' => '1',
			'revisi' => '0'
		);

		// Prepare Data Detail
		$dataResHPP['detail'] = array();


		for ($i = 1; $i <= $param['nodata']; $i++) {
			#= Jurnal HPP

			#= cpo ganjil 
			#= pk genap
			// if($i%2=='0'){
			// $kodebarang=$barang['pk'];
			// }else{
			// $kodebarang=$barang['cpo'];
			// }

			$param['rpdebet' . $i] = str_replace(',', '', $param['rpdebet' . $i]);
			$param['rpkredit' . $i] = str_replace(',', '', $param['rpkredit' . $i]);
			if ($param['rpdebet' . $i] != '0') {
				$dataResHPP['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggalJurnal,
					'nourut' => $noUrut,
					'noakun' => $param['akun' . $i],
					'keterangan' => 'Jurnal ' . $param['keterangan' . $i] . ' ; HPP PT. ' . $namaunit[$pt] . '; Periode ' . $per,
					'jumlah' => $param['rpdebet' . $i],
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $unit,
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => $kodebarang,
					'nik' => '',
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => $noRef,
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $noRef,
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => $kodesegmentsawit
				);
				$noUrut++;
			}
			if ($param['rpkredit' . $i] != '0') {
				$dataResHPP['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggalJurnal,
					'nourut' => $noUrut,
					'noakun' => $param['akun' . $i],
					'keterangan' => 'Jurnal ' . $param['keterangan' . $i] . ' ; HPP PT. ' . $namaunit[$pt] . '; Periode ' . $per,
					'jumlah' => $param['rpkredit' . $i] * -1,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $unit,
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => $kodebarang,
					'nik' => '',
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => $noRef,
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => $noRef,
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => $kodesegmentsawit
				);
				$noUrut++;
			}
		}



		$queryH = insertQuery($dbname, 'keu_jurnalht', $dataResHPP['header']);

		try {
			$owlPDO->exec($queryH);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		foreach ($dataResHPP['detail'] as $key => $dataDet) {
			$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
			// exit("Error:".$queryD);
			try {
				$owlPDO->exec($queryD);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}





		#= Insert ke Saldo Awal HPP
		$nxtBulan = ($bulan < 12) ? $bulan + 1 : 1;
		$nxtTahun = ($bulan < 12) ? $tahun : $tahun + 1;
		$nxtPeriod = $nxtTahun . '-' . str_pad($nxtBulan, 2, '0', STR_PAD_LEFT);

		$dataHpp = array();

		#= TBS

		$param['kgtbssalak'] = str_replace(',', '', $param['kgtbssalak']);
		$param['rptbssalak'] = str_replace(',', '', $param['rptbssalak']);
		$param['kgcposalak'] = str_replace(',', '', $param['kgcposalak']);
		$param['rpcposalak'] = str_replace(',', '', $param['rpcposalak']);
		$param['kgcpogit'] = str_replace(',', '', $param['kgcpogit']);
		$param['rpcpogit'] = str_replace(',', '', $param['rpcpogit']);
		$param['kgpksalak'] = str_replace(',', '', $param['kgpksalak']);
		$param['rppksalak'] = str_replace(',', '', $param['rppksalak']);
		$param['kgpkgit'] = str_replace(',', '', $param['kgpkgit']);
		$param['rppkgit'] = str_replace(',', '', $param['rppkgit']);

		$dataHpp[] = array(
			'kodeorg' => $unit,
			'periode' => $nxtPeriod,
			'kodebarang' => $barang['tbs'],
			'qtyawal' => $param['kgtbssalak'],
			'rpawal' => $param['rptbssalak'],
			'qtyawalgit' => 0,
			'rpawalgit' => 0
		);

		// #= CPO
		// $dataHpp[] = array(
		// 'kodeorg' => $unit,
		// 'periode' => $nxtPeriod,
		// 'kodebarang' => $barang['cpo'],
		// 'qtyawal' => $param['kgcposalak'],
		// 'rpawal' => $param['rpcposalak'],
		// 'qtyawalgit' => $param['kgcpogit'],
		// 'rpawalgit' => $param['rpcpogit']
		// );

		// #= PK
		// $dataHpp[] = array(
		// 'kodeorg' => $unit,
		// 'periode' => $nxtPeriod,
		// 'kodebarang' => $barang['pk'],
		// 'qtyawal' => $param['kgpksalak'],
		// 'rpawal' => $param['rppksalak'],
		// 'qtyawalgit' => $param['kgpkgit'],
		// 'rpawalgit' => $param['rppkgit']
		// );



		#= Delete Saldo Awal HPP
		$qDelHPP = deleteQuery($dbname, 'keu_5hppsaldo', "kodeorg='" . $unit . "' and periode='" . $nxtPeriod . "' and kodebarang='" . $barang['tbs'] . "'");

		try {
			$owlPDO->exec($qDelHPP);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		#= Insert Saldo Awal HPP
		foreach ($dataHpp as $key => $dataDet) {
			$queryD = insertQuery($dbname, 'keu_5hppsaldo', $dataDet);
			// exit("Error:A".$queryD);
			try {
				$owlPDO->exec($queryD);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}


		#= Insert data  HPP

		#= PK

		// exit("Error:".$qtyjualcpo);
		$datapendukung[] = array(
			'kodept' => $pt,
			'kodeunit' => $unit,
			'periode' => $per,
			'qtyjualcpo' => $qtyjualcpo,
			'rpjualcpo' => $rpjualcpo,
			'qtyjualpk' => $qtyjualpk,
			'rpjualpk' => $rpjualpk,
			'qtytbsext' => $qtytbsext,
			'rptbsext' => $rptbsext,
			'qtytbsint' => $qtytbsint,
			'rptbsint' => $rptbsint,
			'qtytbsolah' => $qtytbsolah,
			'rptbsolah' => $rptbsolah
		);
		$str = deleteQuery($dbname, 'keu_3hpp', "kodept='" . $pt . "' and kodeunit='" . $unit . "' and periode='" . $per . "'");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		foreach ($datapendukung as $key => $dataDet) {
			$queryD = insertQuery($dbname, 'keu_3hpp', $dataDet);
			try {
				$owlPDO->exec($queryD);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}

		break;






	default:
		break;
}
