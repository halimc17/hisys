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

$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
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

$kodealokasi = 'HPP';

#= ambil daftar unit untuk hpp
$str = "select  * from " . $dbname . ".organisasi where induk='" . $pt . "'";
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


switch ($method) {

	case 'viewvar':
		$tab .= "<table border=0 cellspacing=1 class=sortable cellpadding=5 style=min-width:400px>
					<thead>
					<tr style='font-weight:bold'>
						<td align='center'></td>
						<td align='center'>" . $_SESSION['lang']['rupiah'] . " " . $_SESSION['lang']['penjualan'] . "</td>
						<td align='center'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['penjualan'] . "</td>
						<td align='center'>" . $_SESSION['lang']['rpkg'] . " " . $_SESSION['lang']['penjualan'] . "</td>
						<td align='center'>" . $_SESSION['lang']['produksi'] . "</td>
						<td align='center'>" . $_SESSION['lang']['produksi'] . " * " . $_SESSION['lang']['rpkg'] . "  " . $_SESSION['lang']['penjualan'] . "</td>
						<td align='center'>" . $_SESSION['lang']['varian'] . "</td>
					
					</tr>
					</thead>";


		#- data produksi
		$str = "SELECT sum(tbsmasuk) as tbsmasuk,sum(tbsdiolah) as tbsdiolah,sum(tbsdiolahnetto) as tbsdiolahnetto,sum(sisatbskemarin) as sisatbskemarin,
								sum(oer) as cpo,sum(oerpk) as pk
								from " . $dbname . ".pabrik_produksi  where tanggal like '" . $per . "%' and kodeorg in 
								(select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and tipe='PABRIK')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kgproduksicpo = $bar['cpo'];
			$kgproduksipk = $bar['pk'];
		}


		#= sumber data dari timbangan
		$str = "select sum(beratbersih) as kg,kodebarang from " . $dbname . ".pabrik_timbangan_vw where  tanggal like '" . $per . "%' and millcode in  (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "'  and tipe='PABRIK') group by kodebarang";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($bar['kodebarang'] == '40000001') {
				$kggcpo = $bar['kg'];
				$kgsalescpo = $bar['kg'];
			}
			if ($bar['kodebarang'] == '40000002') {
				$kggpk = $bar['kg'];
				$kgsalespk = $bar['kg'];
			}
		}




		#= varian pakai data 	

		#= data sales
		$str = "select sum(jumlah*-1) as rp,kodebarang from " . $dbname . ".keu_jurnaldt_vw where tanggal like '" . $per . "%' and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "') and noakun like '5%' group by kodebarang";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($bar['kodebarang'] == '40000001') {
				$rpsalescpo = $bar['rp'];
			}
			if ($bar['kodebarang'] == '40000002') {
				$rpsalespk = $bar['rp'];
			}
		}


		$rpkgsalescpo = $rpsalescpo / $kgsalescpo;
		$rpkgsalespk = $rpsalespk / $kgsalespk;

		#= nilai Nilai Penjualan dari produksi CPO/pk = rata2 sales * produksi
		$rpnilaisalesproduksicpo = $rpkgsalescpo * $kgproduksicpo;
		$rpnilaisalesproduksipk = $rpkgsalespk * $kgproduksipk;
		$rpnilaisalesproduksi = $rpnilaisalesproduksicpo + $rpnilaisalesproduksipk;

		#= var CPO/PK rpnilaisalesproduksicpo/rpnilaisalesproduksi

		$varcpo = hidezerodecimal(fixnan($rpnilaisalesproduksicpo / ($rpnilaisalesproduksi)), 4);
		$varpk = hidezerodecimal(fixnan($rpnilaisalesproduksipk / ($rpnilaisalesproduksi)), 4);




		$tab .= "<tr class=rowcontent>
					<td align='left'>" . $_SESSION['lang']['cpo'] . "</td>
					<td align='right'>" . hidezerodecimal($rpsalescpo) . "</td>
					<td align='right'>" . hidezerodecimal($kgsalescpo) . "</td>
					<td align='right'>" . hidezerodecimal($rpkgsalescpo) . "</td>
					<td align='right'>" . hidezerodecimal($kgproduksicpo) . "</td>
					<td align='right'>" . hidezerodecimal($rpnilaisalesproduksicpo) . "</td>
					<td align='right'>" . hidezerodecimal(fixnan($varcpo * 100), 2) . "</td>
					
				</tr>";
		$tab .= "<tr class=rowcontent>
					<td align='left'>" . $_SESSION['lang']['kernel'] . "</td>
					<td align='right'>" . hidezerodecimal($rpsalespk) . "</td>
					<td align='right'>" . hidezerodecimal($kgsalespk) . "</td>
					<td align='right'>" . hidezerodecimal($rpkgsalespk) . "</td>
					<td align='right'>" . hidezerodecimal($kgproduksipk) . "</td>
					<td align='right'>" . hidezerodecimal($rpnilaisalesproduksipk) . "</td>
					<td align='right'>" . hidezerodecimal(fixnan($varpk * 100), 2) . "</td>
					
				</tr>";
		$tab .= "<tr class=rowcontent>
					<td align='left'>" . $_SESSION['lang']['total'] . "</td>
					<td align='right'>" . hidezerodecimal($rpsalescpo + $rpkgsalespk) . "</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'>" . hidezerodecimal($rpnilaisalesproduksi) . "</td>
					<td align='right'>" . hidezerodecimal((fixnan($varpk * 100)) + (fixnan($varcpo * 100)), 2) . "</td>
				</tr>";
		$tab .= "</table>";






		echo $tab;
		break;



	case 'preview':

		#= ambil saldo awal
		$str = "SELECT * FROM " . $dbname . ".keu_5hppsaldo where periode='" . $per . "' and kodeorg='" . $unit . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kgawal[$bar['kodebarang']] = $bar['qtyawal'];
			$rpawal[$bar['kodebarang']] = $bar['rpawal'];
			$rpperkgawal[$bar['kodebarang']] = $bar['rpawal'] / $bar['qtyawal'];

			#= gitcpo
			#= gitpk
			if ($bar['kodebarang'] == '40000001') {
				$kge2cpo = $bar['qtyawalgit'];
				$rpe2cpo = $bar['rpawalgit'];
				$rpkge2cpo = $bar['rpawalgit'] / $bar['qtyawalgit'];
			}
			if ($bar['kodebarang'] == '40000002') {
				$kge2pk = $bar['qtyawalgit'];
				$rpe2pk = $bar['rpawalgit'];
				$rpkge2pk = $bar['rpawalgit'] / $bar['qtyawalgit'];
			}
			@$kge2 += $bar['qtyawalgit'];
			@$rpe2 += $bar['rpawalgit'];
		}

		@$rpkge2 = ($rpe2 / $kge2);

		#= di-0kan karna tidak mempengahuri
		$kge2cpo = 0;
		$rpe2cpo = 0;
		$rpkge2cpo = 0;

		$kge2pk = 0;
		$rpe2pk = 0;
		$rpkge2pk = 0;

		$kge2 = 0;
		$rpe2 = 0;
		$rpkge2 = 0;



		#= Terima Tbs Internal #= b1 b2 b3
		$str = "select sum(beratbersih) as bruto,sum(kgpotsortasi) as sortasi,sum(beratbersih)-sum(kgpotsortasi) as netto,intex,intiplasma 
				from " . $dbname . ".pabrik_timbangan_vw  where  kodebarang='40000003' and tanggal like '" . $per . "%' and millcode in 
				(select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and tipe='PABRIK') group by intex,intiplasma";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($bar['intex'] == 1) { //internal
				@$kgb1tbsbruto += $bar['bruto'];
				@$kgb1tbsnetto += $bar['netto'];
				@$kgb1tbsrestan += $bar['netto'];
			}
			if ($bar['intex'] == 3) { //plasma
				@$kgb3tbsbruto += $bar['bruto'];
				@$kgb3tbsnetto += $bar['netto'];
				@$kgb3tbsrestan += $bar['netto'];
			}
			if ($bar['intex'] == 2) { //afiliasi
				@$kgb2tbsbruto += $bar['bruto'];
				@$kgb2tbsnetto += $bar['netto'];
				@$kgb2tbsrestan += $bar['netto'];
			}
			if ($bar['intex'] == 0) {
				@$kgb4tbsbruto += $bar['bruto'];
				@$kgb4tbsnetto += $bar['netto'];
				@$kgb4tbsrestan += $bar['netto'];
			}
		}




		$str = "SELECT sum(tbsmasuk) as tbsmasuk,sum(tbsdiolah) as tbsdiolah,sum(tbsdiolahnetto) as tbsdiolahnetto,sum(sisatbskemarin) as sisatbskemarin,
				sum(oer) as cpo,sum(oerpk) as pk
				from " . $dbname . ".pabrik_produksi  where tanggal like '" . $per . "%' and kodeorg in 
				(select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and tipe='PABRIK')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$kge1cpo = $bar['cpo'];
			$kgproduksicpo = $bar['cpo'];
			@$kge1pk = $bar['pk'];
			$kgproduksipk = $bar['pk'];
			@$kgb5bruto = $bar['tbsdiolah'];
		}


		#= sumber data dari timbangan
		$str = "select sum(beratbersih) as kg,kodebarang from " . $dbname . ".pabrik_timbangan_vw where  tanggal like '" . $per . "%' and millcode in  (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "'  and tipe='PABRIK') group by kodebarang";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($bar['kodebarang'] == '40000001') {
				$kggcpo = $bar['kg'];
				$kgsalescpo = $bar['kg'];
			}
			if ($bar['kodebarang'] == '40000002') {
				$kggpk = $bar['kg'];
				$kgsalespk = $bar['kg'];
			}
		}



		#= query ambil proporsi
		$str = "SELECT * from " . $dbname . ".keu_5hppproporsi  where kodeunit='" . $unit . "'";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$varcpo = hidezerodecimal(fixnan($bar['cpo'] / ($bar['cpo'] + $bar['pk'])), 4);
		$varpk = hidezerodecimal(fixnan($bar['pk'] / ($bar['cpo'] + $bar['pk'])), 4);



		#= varian pakai data 	

		#= data sales
		$str = "select sum(jumlah*-1) as rp,kodebarang from " . $dbname . ".keu_jurnaldt_vw where tanggal like '" . $per . "%' and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "') and noakun like '5%' group by kodebarang";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($bar['kodebarang'] == '40000001') {
				$rpsalescpo = $bar['rp'];
			}
			if ($bar['kodebarang'] == '40000002') {
				$rpsalespk = $bar['rp'];
			}
		}


		$rpkgsalescpo = $rpsalescpo / $kgsalescpo;
		$rpkgsalespk = $rpsalespk / $kgsalespk;

		#= nilai Nilai Penjualan dari produksi CPO/pk = rata2 sales * produksi
		$rpnilaisalesproduksicpo = $rpkgsalescpo * $kgproduksicpo;
		$rpnilaisalesproduksipk = $rpkgsalespk * $kgproduksipk;
		$rpnilaisalesproduksi = $rpnilaisalesproduksicpo + $rpnilaisalesproduksipk;

		#= var CPO/PK rpnilaisalesproduksicpo/rpnilaisalesproduksi

		$varcpo = hidezerodecimal(fixnan($rpnilaisalesproduksicpo / ($rpnilaisalesproduksi)), 4);
		$varpk = hidezerodecimal(fixnan($rpnilaisalesproduksipk / ($rpnilaisalesproduksi)), 4);


		// echo $varcpo._.$varpk;





		// echo $varcpo._.$varpk;	

		// echo $toer._.$oercpo._.$oerpk._.$varcpo._.$varpk;




		// print_r($arrunitselainpabrik);

		#= b1 biaya tbs internal
		$nourutlaporan = 'B1';
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw 
                where periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "')
				and kodeorg in ('" . implode("','", $arrunitkebun) . "')";

		// if($_SESSION['standard']['username']=='tim.owl3' || $_SESSION['standard']['username']=='admin.ind'){
		// echo $str;
		// }

		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$rpb1 += $bar['jumlah'];
		}

		#= b2 biaya tbs afiliasi
		$nourutlaporan = 'B2';
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw 
                where periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "')
				and kodeorg in ('" . implode("','", $arrunit) . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$rpb2 += $bar['jumlah'];
		}

		#= b3 biaya pembelian tbs kud
		$nourutlaporan = 'B3';
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw  where periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "') and kodeorg in ('" . implode("','", $arrunit) . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$rpb3 += $bar['jumlah'];
		}

		#= b4 biaya pembelian tbs external
		$nourutlaporan = 'B4';
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw 
                where periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where  namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "')
				and kodeorg in ('" . implode("','", $arrunit) . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$rpb4 += $bar['jumlah'];
		}


		#= query BTL
		#= DI Biaya tidak langsung (OH) kebun + ho ; dijadikan kebun (ho ditanggung kebun)
		// $str = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where periode='".$per."' 
		// and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe in ('KANWIL','HOLDING','KEBUN'))
		// and noakun like '7%' and (nojurnal not like '%HPP%' or nojurnal not like '%ALKHO%') ";
		$nourutlaporan = 'D1';
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw  where periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where  namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "') and kodeorg in ('" . implode("','", $arrunitkebun) . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			// @$rpd1+=$bar['jumlah'];
			@$rpbtlkebun += $bar['jumlah'];
		}

		$rpd1 = 0;

		#= query BTL
		#= DII Biaya tidak langsung pks
		$nourutlaporan = 'D2';
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw 
                where periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where  namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "') and kodeorg in ('" . implode("','", $arrunitpabrik) . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$rpd2 += $bar['jumlah'];
		}




		if ($tipe == 'excel') {
			$border = 'border=1';
		} else {

			$border = '';
		}

		// $stream.= "<fieldset><legend>CPO & PK</legend>";
		$stream .= "<table class=sortable " . $border . " cellspacing=1 style=width:100%;>";

		/*
		$stream.="<thead>";	
		$stream.="<tr class=rowheader>";	
		$stream.="<td bgcolor=#CCCCCC align=center colspan=2 rowspan=2></td>";  
		
		$stream.="<td bgcolor=#CCCCCC align=center rowspan=2>TBS<br>bruto<br>Kg</td>";  
		$stream.="<td bgcolor=#CCCCCC align=center rowspan=2>TBS<br>netto<br>Kg</td>";  
		$stream.="<td bgcolor=#CCCCCC align=center rowspan=2>TBS<br>olah<br>Kg</td>";  
		$stream.="<td bgcolor=#CCCCCC align=center rowspan=2>TBS<br>restan<br>Kg</td>";  
		
		$stream.="<td bgcolor=#CCCCCC align=center colspan=2>".$_SESSION['lang']['total']."</td>";  
		$stream.="<td bgcolor=#CCCCCC align=center colspan=3>".$_SESSION['lang']['cpo']." (".hidezerodecimal(fixnan($varcpo*100),2).")</td>";
		$stream.="<td bgcolor=#CCCCCC align=center colspan=3>".$_SESSION['lang']['kernel']." (".hidezerodecimal(fixnan($varpk*100),2).")</td>";
		$stream.="</tr>";  
		
		$stream.="<tr class=rowheader>";	 
		$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rp']."</td>";
		$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rpperkg']."</td>";		
		$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kg']."</td>"; 
		
		$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rp']."</td>";
		$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rpperkg']."</td>";
		$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kg']."</td>"; 
		
		$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rp']."</td>";
		$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['rpperkg']."</td>";
		$stream.="</tr>";  
		$stream.="</thead>";
		*/

		$colspan = '14';

		$stream .= "<thead>";
		$stream .= "<tr class=rowheader>";
		$stream .= "<th bgcolor=#CCCCCC align=center colspan=2 rowspan=3 style=width:300px;></th>";
		$stream .= "<th bgcolor=#CCCCCC align=center colspan=4>TBS</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center colspan=2>Perhitungan By Pabrik</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center colspan=3>" . $_SESSION['lang']['cpo'] . " (" . hidezerodecimal(fixnan($varcpo * 100), 2) . ")</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center colspan=3>" . $_SESSION['lang']['kernel'] . " (" . hidezerodecimal(fixnan($varpk * 100), 2) . ")  <img src=images/skyblue/zoom.png style='cursor:pointer' class=zImgBtn height='30'  title='view detail variable porporsi'  onclick=\"viewvar();\"></th>";
		$stream .= "</tr>";
		$stream .= "<tr class=rowheader>";
		$stream .= "<th bgcolor=#CCCCCC align=center rowspan=2>Bruto<br>" . $_SESSION['lang']['kg'] . "</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center rowspan=2>Netto<br>" . $_SESSION['lang']['kg'] . "</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center colspan=2>Perhitungan Bahan Baku TBS</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center rowspan=2>" . $_SESSION['lang']['rp'] . "</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center rowspan=2>" . $_SESSION['lang']['rpperkg'] . "</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center rowspan=2>" . $_SESSION['lang']['kg'] . "</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center rowspan=2>" . $_SESSION['lang']['rp'] . "</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center rowspan=2>" . $_SESSION['lang']['rpperkg'] . "</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center rowspan=2>" . $_SESSION['lang']['kg'] . "</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center rowspan=2>" . $_SESSION['lang']['rp'] . "</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center rowspan=2>" . $_SESSION['lang']['rpperkg'] . "</th>";
		$stream .= "</tr>";
		$stream .= "<tr class=rowheader>";
		$stream .= "<th bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['kg'] . "</th>";
		$stream .= "<th bgcolor=#CCCCCC align=center>" . $_SESSION['lang']['rp'] . "</th>";
		$stream .= "</tr>";
		$stream .= "</thead>";



		$kgacpo = $kgawal['40000001'];
		$rpacpo = $rpawal['40000001'];
		$rpkgacpo = $rpperkgawal['40000001'];
		$kgapk = $kgawal['40000002'];
		$rpapk = $rpawal['40000002'];
		$rpkgapk = $rpperkgawal['40000002'];
		$rpa = $rpawal['40000001'] + $rpawal['40000002'];

		$kgatbsrestan = $kgawal['40000003'];
		$rpatbsrestan = $rpawal['40000003'];

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>A</b></td>";
		$stream .= "<td align=left><b>Nilai Saldo Awal Stok</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgatbsrestan), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpatbsrestan), 2) . "</td>";



		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpa), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgacpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpacpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgacpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgapk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpapk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgapk), 2) . "</td>";
		$stream .= "</tr>";

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>B</b></td>";
		$stream .= "<td align=left><b>Bahan Baku</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
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
		$rpb1 = $rpb1 + $rpbtlkebun;
		$rpkgb1 = $rpb1 / $kgb1tbsnetto;

		$rpb1cpo = $rpb1 * $varcpo;
		$rpb1pk = $rpb1 * $varpk;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>1</td>";
		$stream .= "<td align=left>TBS Internal (DC + GC)</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb1tbsbruto), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb1tbsnetto), 2) . "</td>";
		$stream .= "<td align=right id=qtytbsint>" . hidezerodecimal(fixnan($kgb1tbsnetto), 2) . "</td>";
		$stream .= "<td align=right id=rptbsint>" . hidezerodecimal(fixnan($rpb1), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgb1), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";

		$rpkgb2 = $rpb2 / $kgb2tbsnetto;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>2</td>";
		$stream .= "<td align=left>TBS Afiliasi</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb2tbsbruto), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb2tbsnetto), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb2tbsnetto), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpb2), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgb2), 2) . "</td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";


		$rpkgb3 = $rpb3 / $kgb3tbsnetto;

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>3</td>";
		$stream .= "<td align=left>TBS KUD</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb3tbsbruto), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb3tbsnetto), 2) . "</td>";
		$stream .= "<td align=right id=qtytbsext>" . hidezerodecimal(fixnan($kgb3tbsnetto), 2) . "</td>";
		$stream .= "<td align=right id=rptbsext>" . hidezerodecimal(fixnan($rpb3), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgb3), 2) . "</td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";

		$rpkgb4 = $rpb4 / $kgb4tbsnetto;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>4</td>";
		$stream .= "<td align=left>TBS Eksternal</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb4tbsbruto), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb4tbsnetto), 2) . "</td>";
		$stream .= "<td align=right id=qtytbsext>" . hidezerodecimal(fixnan($kgb4tbsnetto), 2) . "</td>";
		$stream .= "<td align=right  id=rptbsext>" . hidezerodecimal(fixnan($rpb4), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgb4), 2) . "</td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";




		$kgb5tbsbruto = $kgb3tbsbruto + $kgb2tbsbruto + $kgb1tbsbruto + $kgb4tbsbruto;
		$kgb5tbsnetto = $kgb3tbsnetto + $kgb2tbsnetto + $kgb1tbsnetto + $kgb4tbsnetto;
		$rpb5 = $rpb3 + $rpb2 + $rpb1 + $rpb4;
		$rpkgb5 = $rpb5 / $kgb5tbsnetto;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>5</td>";
		$stream .= "<td align=left>Total TBS</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb5tbsbruto), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb5tbsnetto), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb5tbsnetto), 2) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpb5), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgb5), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";


		// $kgb5=$kgatbsrestan+$kgb4tbsnetto;
		$kgb6 = $kgatbsrestan + $kgb5tbsnetto;
		$rpb6 = $rpatbsrestan + $rpb5;
		$rpkgb6 = $rpb6 / $kgb6;

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>6</td>";
		$stream .= "<td align=left>Total TBS Siap Olah (Rp/Kg TBS diolah)</td>";

		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb6), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpb6), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgb6), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";


		#= saldo akhir tbs ambil saldo awal di periode depannya
		$str = "select sisatbskemarin from " . $dbname . ".pabrik_produksi where  kodeorg in ('" . implode("','", $arrunit) . "') and tanggal='" . periodeberikut($per) . "-01'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kgjtbs = $bar['sisatbskemarin'];
		}

		@$kgb5netto = $kgb6 - $kgjtbs;

		$kgb7 = $kgb5netto;

		$rpb7 = $rpkgb6 * $kgb7;
		$rpkgb7 = $rpkgb6;
		$rpb7cpo = $rpb7 * $varcpo;
		$rpb7pk = $rpb7 * $varpk;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>7</td>";
		$stream .= "<td align=left>Jumlah TBS Diolah</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb5bruto), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgb5netto), 2) . "</td>";
		$stream .= "<td align=right id=qtytbsolah>" . hidezerodecimal(fixnan($kgb7), 2) . "</td>";
		$stream .= "<td align=right  id=rptbsolah>" . hidezerodecimal(fixnan($rpb7), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpb7), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgb7), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpb7cpo), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpb7pk), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";




		#= c1 Biaya Pengolahan
		// $str = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw 
		// where periode='".$per."' 
		// and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')
		// and noakun like '631%'  and nojurnal not like '%HPP%' ";
		$nourutlaporan = 'C1';
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw 
                where periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where  namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "')
				and kodeorg in ('" . implode("','", $arrunit) . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$rpc1 = $bar['jumlah'];
		}


		#= c2 Biaya maintenance
		$nourutlaporan = 'C2';
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw 
                where periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "')
				and kodeorg in ('" . implode("','", $arrunit) . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$rpc2 = $bar['jumlah'];
		}


		#= c3 beli cpo
		#= dipindah jadi e5
		$nourutlaporan = 'E51';
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw where periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "') and kodeorg in ('" . implode("','", $arrunit) . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			// @$rpc3=$rpc3cpo=$bar['jumlah'];
			@$rpe5cpo = $bar['jumlah'];
		}



		#= c4 beli pk
		$nourutlaporan = 'E52';
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw  where periode='" . $per . "' and noakun in (select noakun from " . $dbname . ".keu_5prosesalokasidt_akun where namalaporan='" . $kodealokasi . "' and nourut='" . $nourutlaporan . "') and kodeorg in ('" . implode("','", $arrunit) . "')";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			// @$rpc4=$rpc4pk=$bar['jumlah'];
			@$rpe5pk = $bar['jumlah'];
		}


		#= kg beli cpo/pk ambil dari bast
		/*
		$str="select sum(kuantitas) as jumlah,kodebarang from ".$dbname.".pmn_bastdt_vw where tanggal like '".$per."%' and tipejualbeli='BELI' and kodept='".$pt."' group by kodebarang";	  
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['kodebarang']=='40000001'){
				$kge5cpo=$bar['jumlah'];
			}
			if($bar['kodebarang']=='40000002'){
				$kge5pk=$bar['jumlah'];
			}
		}
		*/

		// $str="select sum(beratbersih) as beratbersih,kodebarang from ".$dbname.".keu_pembeliancpopk where periode='".$per."' and unit in ('".implode("','",$arrunit)."') group by kodebarang";	  
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// if($bar['kodebarang']=='40000001'){
		// $kge5cpo=$bar['beratbersih'];
		// }
		// if($bar['kodebarang']=='40000002'){
		// $kge5pk=$bar['beratbersih'];
		// }
		// }




		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>C</b></td>";
		$stream .= "<td align=left><b>Pabrik</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";


		$rpc1cpo = $rpc1 * $varcpo;
		$rpc1pk = $rpc1 * $varpk;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>1</td>";
		$stream .= "<td align=left>Proses</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpc1), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpc1cpo), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpc1pk), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";

		$rpc2cpo = $rpc2 * $varcpo;
		$rpc2pk = $rpc2 * $varpk;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>2</td>";
		$stream .= "<td align=left>Maintenance</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpc2), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpc2cpo), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpc2pk), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";

		/*
		
		$stream.="<tr class=rowcontent>";	
			$stream.="<td align=center style=width:25px;>3</td>";  
			$stream.="<td align=left>Pembelian CPO</td>";  
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right></td>";  	
			$stream.="<td align=right >".hidezerodecimal(fixnan($rpc3),2)."</td>"; 		
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right>".hidezerodecimal(fixnan($kgc3cpo),2)."</td>";
			$stream.="<td align=right>".hidezerodecimal(fixnan($rpc3cpo),2)."</td>"; 
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right></td>";  					
			$stream.="<td align=right></td>";  					
			$stream.="<td align=right></td>";  					
		$stream.="</tr>";  
		
		$stream.="<tr class=rowcontent>";	
			$stream.="<td align=center style=width:25px;>4</td>";  
			$stream.="<td align=left>Pembelian PK</td>";  
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right >".hidezerodecimal(fixnan($rpc4),2)."</td>"; 			
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right></td>";  		
			$stream.="<td align=right></td>";  			
			$stream.="<td align=right>".hidezerodecimal(fixnan($kgc4pk),2)."</td>"; 			
			$stream.="<td align=right>".hidezerodecimal(fixnan($rpc4pk),2)."</td>"; 			
			$stream.="<td align=right></td>";  					
		$stream.="</tr>";  
		*/

		$rpc5 = $rpc1 + $rpc2 + $rpc3 + $rpc4;
		$rpc5cpo = $rpc1cpo + $rpc2cpo + $rpc3cpo + $rpc4cpo;
		$rpc5pk = $rpc1pk + $rpc2pk + $rpc3pk + $rpc4pk;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>5</td>";
		$stream .= "<td align=left>Total</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpc5), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpc5cpo), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpc5pk), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";


		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";






		#= karna tertinggal maka variable tetap pakai C. dengan variable cbtl
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>D</b></td>";
		$stream .= "<td align=left><b>Biaya Tidak Langsung</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";


		#= kebun btlnya sudah dipindah menambah biaya langsung
		$rpd1cpo = $rpd1 * $varcpo;
		$rpd1pk = $rpd1 * $varpk;
		$stream .= "<tr  class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>1</td>";
		$stream .= "<td align=left>Kebun</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpd1), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpd1cpo), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpd1pk), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";

		$rpd2cpo = $rpd2 * $varcpo;
		$rpd2pk = $rpd2 * $varpk;
		$stream .= "<tr class=rowcontent>";
		// $stream.="<td align=center style=width:25px;>2</td>";  
		$stream .= "<td align=center style=width:25px;>2</td>";
		$stream .= "<td align=left>Pabrik</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpd2), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpd2cpo), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpd2pk), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";

		$rpd3 = $rpd1 + $rpd2;
		$rpd3cpo = $rpd1cpo + $rpd2cpo;
		$rpd3pk = $rpd1pk + $rpd2pk;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center style=width:25px;>3</td>";
		$stream .= "<td align=left>Total</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpd3), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpd3cpo), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpd3pk), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";












		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";



		$rpe1 = $rpb7 + $rpc5 + $rpd3;
		$kge1 = $kge1cpo + $kge1pk;
		$rpkge1 = $rpe1 / $kgb7;

		$rpe1cpo = $rpb7cpo + $rpc5cpo + $rpd3cpo;
		$rpkge1cpo = $rpe1cpo / $kge1cpo;
		$rpe1pk = $rpb7pk + $rpc5pk + $rpd3pk;
		$rpkge1pk = $rpe1pk / $kge1pk;

		$noe = 0;

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>E</b></td>";
		$stream .= "<td align=left><b>CPO dan PK Terproduksi</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "</tr>";

		$noe++;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center>" . $noe . "</td>";
		$stream .= "<td align=left>Pabrik</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpe1), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge1), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kge1cpo), 2) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpe1cpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge1cpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kge1pk), 2) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpe1pk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge1pk), 2) . "</td>";
		$stream .= "</tr>";

		$noe++;
		$stream .= "<tr class=rowcontent hidden>";
		$stream .= "<td align=center>2</td>";
		// $stream.="<td align=left>Beginning Goods In Transit</td>";  
		$stream .= "<td align=left>Selisih Mutasi Sounding</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpe2), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge2), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kge2cpo), 2) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpe2cpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge2cpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kge2pk), 2) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpe2pk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge2pk), 2) . "</td>";
		$stream .= "</tr>";


		// $str="SELECT * FROM ".$dbname.".keu_5hppselisihtimbang where periode='".$per."' and kodeorg='".$unit."' and keterangan='penjualan'";
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// if($bar['kodebarang']=='40000001'){
		// $kge3cpo=$bar['qtyawal'];
		// $rpe3cpo=$bar['rpawal'];
		// }
		// if($bar['kodebarang']=='40000002'){
		// $kge3pk=$bar['qtyawal'];
		// $rpe3pk=$bar['rpawal'];
		// }
		// }

		$kge3 = $kge3cpo + $kge3pk;
		$rpe3 = $rpe3cpo + $rpe3pk;
		$rpkge3 = $rpe3 / $kge3;


		$noe++;
		$stream .= "<tr class=rowcontent hidden>";
		$stream .= "<td align=center>" . $noe . "</td>";
		$stream .= "<td align=left>Selisih Timbangan Penjualan</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpe3), 3) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge3), 3) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kge3cpo), 3) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpe3cpo), 3) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge3cpo), 3) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kge3pk), 3) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpe3pk), 3) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge3pk), 3) . "</td>";
		$stream .= "</tr>";

		// $str="SELECT * FROM ".$dbname.".keu_5hppselisihtimbang where periode='".$per."' and kodeorg='".$unit."' and keterangan='loadingsounding'";
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// if($bar['kodebarang']=='40000001'){
		// $kge4cpo=$bar['qtyawal'];
		// $rpe4cpo=$bar['rpawal'];
		// }
		// if($bar['kodebarang']=='40000002'){
		// $kge4pk=$bar['qtyawal'];
		// $rpe4pk=$bar['rpawal'];
		// }
		// }

		$kge4 = $kge4cpo + $kge4pk;
		$rpe4 = $rpe4cpo + $rpe4pk;
		$rpkge4 = $rpe4 / $kge4;

		$noe++;
		$stream .= "<tr class=rowcontent hidden>";
		$stream .= "<td align=center>" . $noe . "</td>";
		$stream .= "<td align=left>Selisih Timbangan Loading dan Sounding</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpe4), 4) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge4), 4) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kge4cpo), 4) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpe4cpo), 4) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge4cpo), 4) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kge4pk), 4) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpe4pk), 4) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge4pk), 4) . "</td>";
		$stream .= "</tr>";

		$noe++;
		$kge5 = $kge5cpo + $kge5pk;
		$rpe5 = $rpe5cpo + $rpe5pk;
		$rpkge5 = $rpe5 / $kge5;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center>" . $noe . "</td>";
		$stream .= "<td align=left>Pembelian</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpe5), 4) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge5), 4) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kge5cpo), 4) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpe5cpo), 4) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge5cpo), 4) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kge5pk), 4) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpe5pk), 4) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge5pk), 4) . "</td>";
		$stream .= "</tr>";


		$rpe = $rpe1 + $rpe2 + $rpe3 + $rpe4 + $rpe5;
		$kge = $kge1 + $kge2 + $kge3 + $kge4 + $kge5;
		$rpkge = $rpe / $kge;

		$rpecpo = $rpe1cpo + $rpe2cpo + $rpe3cpo + $rpe4cpo + $rpe5cpo;
		$kgecpo = $kge1cpo + $kge2cpo + $kge3cpo + $kge4cpo + $kge5cpo;
		$rpkgecpo = $rpecpo / $kgecpo;

		$rpepk = $rpe1pk + $rpe2pk + $rpe3pk + $rpe4pk + $rpe5pk;
		$kgepk = $kge1pk + $kge2pk + $kge3pk + $kge4pk + $kge5pk;
		$rpkgepk = $rpepk / $kgepk;

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center>6</td>";
		$stream .= "<td align=left>Total</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpe), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkge), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgecpo), 2) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpecpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgecpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgepk), 2) . "</td>";
		$stream .= "<td align=right >" . hidezerodecimal(fixnan($rpepk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgepk), 2) . "</td>";
		$stream .= "</tr>";


		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";


		$rpf = $rpa + $rpe;
		$kgfcpo = $kgacpo + $kgecpo;
		$rpfcpo = $rpacpo + $rpecpo;
		$rpkgfcpo = $rpfcpo / $kgfcpo;
		$kgfpk = $kgapk + $kgepk;
		$rpfpk = $rpapk + $rpepk;
		$rpkgfpk = $rpfpk / $kgfpk;

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>F</b></td>";
		$stream .= "<td align=left><b>CPO dan PK Tersedia</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpf), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgfcpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpfcpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgfcpo), 2) . "</td>";

		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgfpk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpfpk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgfpk), 2) . "</td>";
		$stream .= "</tr>";



		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";



		/*
		#= saldo akhir pabrik
		$str="select * from ".$dbname.".pabrik_masukkeluartangki   
				where kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki where komoditi in ('CPO','KER')) 
				and tanggal = '".tglakhir($per)."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where
				induk='".$pt."' and tipe='PABRIK' and kodeorganisasi!='KSBW')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kggcpo+=$bar['kuantitas'];
			$kggpk+=$bar['kernelquantity'];	
		}
		
		#= stok akhir bulking
		$str="select * from ".$dbname.".pabrik_stokbulking   
				where kodept='".$pt."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kggcpo+=$bar['kuantitas'];
			$kggpk+=$bar['kernelquantity'];	
		}
		*/


		// $kgfcpo


		//pmn_bapengiriman

		#= terkirim indra
		/*
		$str="select sum(beratbersih) as kg,kodebarang
				from ".$dbname.".pabrik_timbangan_vw   where kodebarang='40000001' and tanggal like '".$per."%' and millcode in 
				(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' 
				and tipe='PABRIK' and kodeorganisasi!='KSBW') group by kodebarang";
				echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			
		}
		
		$str="select sum(jumlah) as kg,kodebarang
				from ".$dbname.".pmn_bapengiriman_vw where tanggal like '".$per."%' 
				and kodept='".$pt."' group by kodebarang";
				// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['kodebarang']=='40000001'){
				$kggcpo=$bar['kg'];
			}
			if($bar['kodebarang']=='40000002'){
				$kggpk=$bar['kg'];
			}
		}
		*/

		#= 
		/*
		$str="select sum(jumlah) as kg,kodebarang from ".$dbname.".pmn_bast where tanggalbl like '".$per."%' and kodept='".$pt."' group by kodebarang";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['kodebarang']=='40000001'){
				$kggcpo=$bar['kg'];
			}
			if($bar['kodebarang']=='40000002'){
				$kggpk=$bar['kg'];
			}
		}
		*/




		$rpkggcpo = $rpkgfcpo;
		$rpkggpk = $rpkgfpk;
		$rpgcpo = $rpkggcpo * $kggcpo;
		$rpgpk = $rpkggpk * $kggpk;
		$rpg = $rpgcpo + $rpgpk;
		$kgg = $kggcpo + $kggpk;


		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>G</b></td>";
		$stream .= "<td align=left><b>CPO dan PK Terkirim</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpg), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right id=qtyjualcpo>" . hidezerodecimal(fixnan($kggcpo), 2) . "</td>";
		$stream .= "<td align=right  id=rpjualcpo>" . hidezerodecimal(fixnan($rpgcpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkggcpo), 2) . "</td>";

		$stream .= "<td align=right id=qtyjualpk>" . hidezerodecimal(fixnan($kggpk), 2) . "</td>";
		$stream .= "<td align=right  id=rpjualpk >" . hidezerodecimal(fixnan($rpgpk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkggpk), 2) . "</td>";
		$stream .= "</tr>";


		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";

		$str = "select sum(jumlah) as kg,kodebarang from " . $dbname . ".pabrik_pembersihantangki where tanggal like '" . $per . "%' 
				and kodeorg in ('" . implode("','", $arrunitpabrik) . "')
				group by kodebarang";
		// echo $str;
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($bar['kodebarang'] == '40000001') {
				$kghcpo = $bar['kg'];
			}
			if ($bar['kodebarang'] == '40000002') {
				$kghpk = $bar['kg'];
			}
		}

		$rpkghcpo = $rpkgfcpo;
		$rphcpo = $kghcpo * $rpkghcpo;
		$rpkghpk = $rpkgfpk;
		$rphpk = $kghpk * $rpkghpk;
		$rph = $rphcpo + $rphpk;

		// echo $kgkirimcpo._.$kgterimacpo.'     '.$kgkirimpk._.$kgterimapk;

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>H</b></td>";
		$stream .= "<td align=left><b>Cuci Tangki/Write Off</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rph), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kghcpo), 2) . "</td>";
		$stream .= "<td align=right bgcolor=#87CEEB>" . hidezerodecimal(fixnan($rphcpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkghcpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kghpk), 2) . "</td>";
		$stream .= "<td align=right bgcolor=#87CEEB>" . hidezerodecimal(fixnan($rphpk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkghpk), 2) . "</td>";
		$stream .= "</tr>";

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";


		#= rumus saldo akhir cpo pk
		#= Rumus saldo akhir = saldoawal + produksi - sales-cuci tangki + lost in transit.

		#= rubah = rumus menjadi sounding
		#= munculkan GIT, rumus, saldo akhir - rumus saldo akhir mutasi



		#======================= saldo akhir  sumber sounding
		$str = "select * from " . $dbname . ".pabrik_masukkeluartangki where kodetangki in (select kodetangki from " . $dbname . ".pabrik_5tangki where komoditi in ('CPO','KER')) and tanggal = '" . tglakhir($per) . "' and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $pt . "' and tipe='PABRIK')";
		// echo $str;exit();
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$kgicpo += $bar['kuantitas'];
			$kgipk += $bar['kernelquantity'];
		}

		/*
		$rpicpo=$rpacpo+$rpecpo-$rpgcpo-$rphcpo;
		$rpipk=$rpapk+$rpepk-$rpgpk-$rphpk;
		
		$rpkgicpo=$rpicpo/$kgicpo;
		$rpkgipk=$rpipk/$kgipk;
		*/
		#==============================================

		#============================= mutasi



		// $kgjtbs=$kgb6-$kgb7;
		$rpjtbs = $rpb6 - $rpb7;
		$rpkgjtbs = $rpjtbs / $kgjtbs;

		$kgjcpo = $kgacpo + $kgecpo - $kggcpo - $kghcpo;
		$kgjpk = $kgapk + $kgepk - $kggpk - $kghpk;

		$rpkgicpo = $rpkgfcpo;
		$rpkgipk = $rpkgfpk;
		$rpicpo = $rpkgicpo * $kgicpo;
		$rpipk = $rpkgipk * $kgipk;
		$rpi = $rpicpo + $rpipk;

		#==============================================

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>I</b></td>";
		$stream .= "<td align=left><b>Saldo Akhir Sounding</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right id=kgtbssalak bgcolor=lightgray>" . hidezerodecimal(fixnan($kgjtbs), 2) . "</td>";
		$stream .= "<td align=right id=rptbssalak bgcolor=lightgray>" . hidezerodecimal(fixnan($rpjtbs), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpi), 2) . "</td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right id=kgcposalak bgcolor=lightgray>" . hidezerodecimal(fixnan($kgicpo), 2) . "</td>";
		$stream .= "<td align=right id=rpcposalak bgcolor=lightgray>" . hidezerodecimal(fixnan($rpicpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgicpo), 2) . "</td>";
		$stream .= "<td align=right id=kgpksalak bgcolor=lightgray>" . hidezerodecimal(fixnan($kgipk), 2) . "</td>";
		$stream .= "<td align=right id=rppksalak bgcolor=lightgray>" . hidezerodecimal(fixnan($rpipk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgipk), 2) . "</td>";
		$stream .= "</tr>";

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";


		$rpkgjcpo = $rpkgfcpo;
		$rpkgjpk = $rpkgfpk;
		$rpjcpo = $rpkgjcpo * $kgjcpo;
		$rpjpk = $rpkgjpk * $kgjpk;
		$rpj = $rpjcpo + $rpjpk;

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>J</b></td>";
		$stream .= "<td align=left><b>Saldo Akhir Mutasi</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgjtbs), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpjtbs), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpj), 2) . "</td>";
		$stream .= "<td align=right></td>";

		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgjcpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpjcpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgjcpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($kgjpk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpjpk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgjpk), 2) . "</td>";
		$stream .= "</tr>";


		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";



		#=================================================================================================================
		#=================================================================================================================
		#=================================================================================================================


		$kgkcpo = $kgicpo - $kgjcpo;
		$rpkgkcpo = $rpkgfcpo;
		$rpkcpo = $rpkgkcpo * $kgkcpo;

		$kgkpk = $kgipk - $kgjpk;
		$rpkgkpk = $rpkgfcpo;
		$rpkpk = $rpkgkpk * $kgkpk;

		$rpk = $rpkcpo + $rpkpk;

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left style=width:25px;><b>K</b></td>";
		$stream .= "<td align=left><b>Selisih Mutasi (Sounding-Mutasi)</b></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpk), 2) . "</td>";
		$stream .= "<td align=right></td>";
		$stream .= "<td align=right id=kgcpogit bgcolor=lightgray>" . hidezerodecimal(fixnan($kgkcpo), 2) . "</td>";
		$stream .= "<td align=right id=rpcpogit bgcolor=lightgray>" . hidezerodecimal(fixnan($rpkcpo), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgkcpo), 2) . "</td>";
		$stream .= "<td align=right id=kgpkgit>" . hidezerodecimal(fixnan($kgkpk), 2) . "</td>";
		$stream .= "<td align=right id=rppkgit bgcolor=lightgray>" . hidezerodecimal(fixnan($rpkpk), 2) . "</td>";
		$stream .= "<td align=right>" . hidezerodecimal(fixnan($rpkgkpk), 2) . "</td>";
		$stream .= "</tr>";

		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left colspan='" . $colspan . "'>&nbsp;</td>";
		$stream .= "</tr>";

		#===============================================================================================
		#= jurnal
		#===============================================================================================

		/*
		Jurnal HPP
		D/K : HPP CPO (651)
		K/K : HPP PK  (651)
		D/K : Persediaan CPO (11502)
		K/K : Persediaan PK  (11502)
		
		Jurnal Loss (coa sesuai dengan barang)
		D : Other exp (923)
		K : GAIN/(LOSS) IN TRANSIT (66102)

		Jurnal Cuci Tangki
		D : WRITE OFF - INVENTORY (9299902)
		K : WRITE OFF - INVENTORY (6610204/5 akun baru CPO/PK)	
		
		*/


		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=left><b>Z</b></td>";
		$stream .= "<td align=left><b>Jurnal</b></td>";
		$stream .= "<td align=center><b>" . $_SESSION['lang']['noakun'] . "</b></td>";
		$stream .= "<td align=center colspan=3><b>" . $_SESSION['lang']['namaakun'] . "</b></td>";
		$stream .= "<td align=center><b>" . $_SESSION['lang']['debet'] . "</b></td>";
		$stream .= "<td align=center><b>" . $_SESSION['lang']['kredit'] . "</b></td>";
		$stream .= "<td align=left></td>";
		$stream .= "<td align=left></td>";
		$stream .= "<td align=left></td>";
		$stream .= "<td align=left></td>";
		$stream .= "<td align=left></td>";
		$stream .= "<td align=left></td>";

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
		$rpdebet['1'] = $rpecpo;
		$rpdebet['2'] = $rpepk;
		$rpdebet['3'] = $rpb5;
		$rpkredit['4'] = $rpb1;
		$rpkredit['5'] = $rpb2 + $rpb3 + $rpb4;
		$rpkredit['6'] = $rpe5cpo;
		$rpkredit['7'] = $rpe5pk;
		$rpkredit['8'] = $rpc5;
		$rpkredit['9'] = $rpd3;

		$rpdebet['10'] = $rpgcpo;
		$rpdebet['11'] = $rpgpk;
		$rpkredit['12'] = $rpgcpo;
		$rpkredit['13'] = $rpgpk;
		$rpkredit['14'] = $rpb7;

		#= selisih stok
		#= jika selisih stok + 
		#= 115 + , hpp -

		if ($rpkcpo > 0) {
			$rpdebet['15'] = $rpkcpo;
			$rpkredit['15'] = '0';
			$rpdebet['16'] = '0';
			$rpkredit['16'] = $rpkcpo;
		} else {
			$rpdebet['15'] = '0';
			$rpkredit['15'] = ($rpkcpo * -1);
			$rpdebet['16'] = ($rpkcpo * -1);
			$rpkredit['16'] = '0';
		}

		if ($rpkpk > 0) {
			$rpdebet['17'] = $rpkpk;
			$rpkredit['17'] = '0';
			$rpdebet['18'] = '0';
			$rpkredit['18'] = $rpkpk;
		} else {
			$rpdebet['17'] = '0';
			$rpkredit['17'] = ($rpkpk * -1);
			$rpdebet['18'] = ($rpkpk * -1);
			$rpkredit['18'] = '0';
		}


		$rpdebet['19'] = '0';
		$rpkredit['19'] = $rphcpo;
		$rpdebet['20'] = $rphcpo;
		$rpkredit['20'] = '0';

		$rpdebet['21'] = '0';
		$rpkredit['21'] = $rphpk;
		$rpdebet['22'] = $rphpk;
		$rpkredit['22'] = '0';



		$arrht = "###kurs###jumlah###notransaksi###tipetransaksi###kodeorg###noakun###tanggal###bayarkepada###keterangan###matauang###autokb###noakun2###namapenerima###norekpenerima###rekening###norekap";

		##= alokasi
		$nodata = 0;
		$arrht = "";
		$str = "select * from " . $dbname . ".keu_5prosesalokasidt where namalaporan='" . $kodealokasi . "' and nourut like 'Z%' order by nourut asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nodata++;
			$stream .= "<tr class=rowcontent>";
			$stream .= "<td align=right>" . $bar['nourut'] . "</td>";
			$stream .= "<td align=left id=keterangan" . $nodata . ">" . $bar['keterangandisplay'] . "</td>";
			$stream .= "<td align=center id=akun" . $nodata . ">" . $bar['noakun'] . "</td>";
			$stream .= "<td align=left colspan=3>" . $nmakun[$bar['noakun']] . "</td>";
			$stream .= "<td align=right id=rpdebet" . $nodata . ">" . hidezerodecimal(fixnan($rpdebet[$nodata]), 2) . "</td>";
			$stream .= "<td align=right id=rpkredit" . $nodata . ">" . hidezerodecimal(fixnan($rpkredit[$nodata]), 2) . "</td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=left></td>";
			$stream .= "<td align=left></td>";
			$stream .= "</tr>";
			$arrht .= "###akun" . $nodata . "###rpdebet" . $nodata . "###rpkredit" . $nodata . "###keterangan" . $nodata . "";
		}
		$arrht .= "###kgpksalak###rppksalak###kgcposalak###rpcposalak###kgtbssalak###rptbssalak";
		$arrht .= "###kgcpogit###rpcpogit###kgpkgit###rppkgit";
		// $arrht.="###"
		// echo $arrht;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td colspan=14><button class=mybutton onclick=savehpp('" . $arrht . "','" . $nodata . "')>" . $_SESSION['lang']['proses'] . "</button><br></td>";
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
		$nojurnal = $tahunbulan . $lastDay . '/' . $unit . '/HPP/001';
		$kodeJurnal = 'HPP';
		$tanggalJurnal = $per . '-' . $lastDay;
		$noUrut = 1;
		$noRef = $kodeJurnal . '/' . $unit . '/' . $tahunbulan;

		$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

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
					'kodesegment' => $defSegment
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
					'kodesegment' => $defSegment
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

		#= CPO
		$dataHpp[] = array(
			'kodeorg' => $unit,
			'periode' => $nxtPeriod,
			'kodebarang' => $barang['cpo'],
			'qtyawal' => $param['kgcposalak'],
			'rpawal' => $param['rpcposalak'],
			'qtyawalgit' => $param['kgcpogit'],
			'rpawalgit' => $param['rpcpogit']
		);

		#= PK
		$dataHpp[] = array(
			'kodeorg' => $unit,
			'periode' => $nxtPeriod,
			'kodebarang' => $barang['pk'],
			'qtyawal' => $param['kgpksalak'],
			'rpawal' => $param['rppksalak'],
			'qtyawalgit' => $param['kgpkgit'],
			'rpawalgit' => $param['rppkgit']
		);



		#= Delete Saldo Awal HPP
		$qDelHPP = deleteQuery($dbname, 'keu_5hppsaldo', "kodeorg='" . $unit . "' and periode='" . $nxtPeriod . "'");

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
				print " Gagal !: " . $e->getMessage() . "\n";
				die();
			}
		}


		#= Insert data  HPP

		#= PK

		// exit("Error:".$qtyjualcpo);
		/*
		$datapendukung[] = array(
			'kodept'=>$pt,
			'kodeunit'=>$unit,
			'periode'=>$per,
			'qtyjualcpo'=>$qtyjualcpo,
			'rpjualcpo'=>$rpjualcpo,
			'qtyjualpk'=>$qtyjualpk,
			'rpjualpk'=>$rpjualpk,
			'qtytbsext'=>$qtytbsext,
			'rptbsext'=>$rptbsext,
			'qtytbsint'=>$qtytbsint,
			'rptbsint'=>$rptbsint,
			'qtytbsolah'=>$qtytbsolah,
			'rptbsolah'=>$rptbsolah
		);
		$str = deleteQuery($dbname,'keu_3hpp',"kodept='".$pt."' and kodeunit='".$unit."' and periode='".$per."'");
		try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		foreach($datapendukung as $key=>$dataDet) {
			$queryD = insertQuery($dbname,'keu_3hpp',$dataDet);
			try {
				$owlPDO->exec($queryD);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		*/


		#=cek jurnal apakah balance atau tidak
		$str = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw where kodejurnal='HPP' and kodeorg='" . $unit . "' and periode='" . $per . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nilaijurnal = abs($bar['jumlah']);
		}

		if ($jumlah > 1) {
			exit("Warning:Jurnal HPP selisih sejumlah " . hidezerodecimal($nilaijurnal, 2) . ", harap periksa kembali pembentukan jurnal saat proses. ");
		}


		break;






	default:
		break;
}
