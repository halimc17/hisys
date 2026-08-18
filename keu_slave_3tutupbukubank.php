<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$method = checkPostGet('method', '');
$kodeorg = checkPostGet('kodeorg', '');
$periode = checkPostGet('periode', '');
$tampil = checkPostGet('tampil', '');

$akunKasbank = "'11101','11102'";
$str = "select * from " . $dbname . ".keu_5akun  where  LEFT(noakun,5) IN ({$akunKasbank})";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$namaakun[$bar['noakun']] = $bar['namaakun'];
}


switch ($method) {
	case 'getperiode':
		$nodata = 0;
		$str = "select * from " . $dbname . ".keu_saldobank where kodeorg='" . $kodeorg . "' order by periode desc limit 1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nodata++;
			$thn = substr($bar['periode'], 0, 4);
			$bln = substr($bar['periode'], 4, 2);
			$optorg .= "<option value=" . $thn . "-" . $bln . ">" . $thn . "-" . $bln . "</option>";
		}
		#= jika tidak ada saldobank maka ambil dari periode akuntansi
		if ($nodata == '0') {
			$str = "select distinct(periode) as periode from " . $dbname . ".keu_saldobulanan where kodeorg='" . $kodeorg . "' and noakun like '11101%' order by periode desc limit 1";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$nodata++;
				$thn = substr($bar['periode'], 0, 4);
				$bln = substr($bar['periode'], 4, 2);
				$optorg .= "<option value=" . $thn . "-" . $bln . ">" . $thn . "-" . $bln . "</option>";
			}
		}
		/*$optorg.="<option value='2018-04'>2018-04</option>";
		$optorg.="<option value='2018-05'>2018-05</option>";
		$optorg.="<option value='2018-06'>2018-06</option>";
		$optorg.="<option value='2018-07'>2018-07</option>";
		$optorg.="<option value='2018-08'>2018-08</option>";
		$optorg.="<option value='2018-09'>2018-09</option>";
		$optorg.="<option value='2018-10'>2018-10</option>";*/

		echo $optorg;
		break;

	case 'preview':

		$belumsetuju = array();
		# cek transaksi yang sudah diajukan namun belum di setujui
		$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi like '%" . $kodeorg . "%' and tanggalpengajuan 
			like '" . $periode . "%' and posting='9'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$belumsetuju[$bar['notransaksi']] = $bar['notransaksi'];
		}

		$belumbayar = array();
		# cek transaksi yang sudah disetujui namun belum ada jurnal
		$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi not in (select noreferensi from " . $dbname . ".keu_jurnaldt_vw) 
		and notransaksi like '%" . $kodeorg . "%' and tanggalpengajuan like '" . $periode . "%' and posting='1'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$belumbayar[$bar['notransaksi']] = $bar['notransaksi'];
		}

		$rekeningbank = array();
		$akunrek = array();
		# ambil daftar nama bank
		$str = "select * from " . $dbname . ".keu_5akunbank where pemilik = '" . $kodeorg . "' and status='1'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$rekeningbank[$bar['namabank']][$bar['noakun']] = $bar['noakun'];
			$akunrek[$bar['noakuncoa']] = $bar['noakun'];
		}

		// if (count($rekeningbank)==0) {
		// exit('warning : Pada unit ini belum ada bank yang aktif. Silahkan cek di menu keuangan > setup > daftar rek bank perusahaan.');
		// }


		$tab = "";
		// if($belumsetuju!=''){
		// echo "<font color=red>Ada transaksi kas dan bank yang sudah diajukan namun belum di setujui, dengan nomor transaksi : <br>".implode(", ",$belumsetuju)."</font>";
		// }
		// if($belumbayar!=''){
		// echo "<font color=red>Ada transaksi kas dan bank yang sudah disetujui namun belum di bayarkan, dengan nomor transaksi : <br>".implode(", ",$belumbayar)."</font>";
		// }

		$tab .= "<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
			<thead>
			<tr class=rowheader>
				<td align=center rowspan=2>No</td>
				<td align=center rowspan=2>" . $_SESSION['lang']['namabank'] . "<br>/ " . $_SESSION['lang']['kas'] . "</td>
				<td align=center rowspan=2>" . $_SESSION['lang']['rekening'] . "</td>
				<td align=center colspan=4>" . $periode . "</td>";
		$tab .= "</tr>";
		$tab .= "<tr class=rowheader>";
		$tab .= "<td align=center>" . $_SESSION['lang']['saldoawal'] . "</td>
				<td align=center>" . $_SESSION['lang']['debet'] . "</td>
				<td align=center>" . $_SESSION['lang']['kredit'] . "</td>
				<td align=center>" . $_SESSION['lang']['saldoakhir'] . "</td>
				";
		$tab .= "</tr>";
		$tab .= "</thead>
			<tbody>";


		$sawal = array();
		# ambil saldo awal
		$str = "select kodeorg,periode,norek, awal" . substr($periode, 5, 2) . " as sawal from " . $dbname . ".keu_saldobank 
			where kodeorg = '" . $kodeorg . "' and periode='" . str_replace("-", "", $periode) . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$sawal[$bar['norek']] = $bar['sawal'];
		}

		$jlhbm = array();
		$jlhbmluar = array();
		# Get Total Bank Masuk
		$str = "select rekening, sum(jumlah * kurs) as jumlah,matauang,kurs from " . $dbname . ".keu_kasbankdtht_vw 
			where kode='BM' and posting='1' and pembayaran=1 and tanggal like '" . $periode . "%' and kodeorght='" . $kodeorg . "'  group by rekening";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$jlhbm[$bar['rekening']] = $bar['jumlah'];
			/*
			if ($bar['matauang']!='IDR') {
				$jlhbmluar[$bar['rekening']]=$bar['jumlah']*$bar['kurs'];
			}
			*/
		}

		$jlhbk = array();
		$jlhbkluar = array();
		# Get Total Bank Keluar
		$str = "select rekening, sum(jumlah * kurs) as jumlah,matauang,kurs from " . $dbname . ".keu_kasbankdtht_vw 
			where kode='BK' and posting='1' and pembayaran=1 and tanggal like '" . $periode . "%' and kodeorght='" . $kodeorg . "'  group by rekening";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$jlhbk[$bar['rekening']] = $bar['jumlah'];
			/*
			if ($bar['matauang']!='IDR') {
				$jlhbkluar[$bar['rekening']]=$bar['jumlah']*$bar['kurs'];
			}
			*/
		}


		$str = "select sum(jumlah) as jumlah, noakun from " . $dbname . ".keu_jurnaldt_vw 
			where kodejurnal='KRS01'  AND LEFT(noakun,5) IN ({$akunKasbank})  and noakun not like '11104%'and tanggal like '" . $periode . "%' and kodeorg='" . $kodeorg . "'  group by noakun";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($bar['jumlah'] > 0) {
				$jlhbm[$akunrek[$bar['noakun']]] += $bar['jumlah'];
			} else {
				$jlhbk[$akunrek[$bar['noakun']]] += ($bar['jumlah'] * -1);
			}
		}

		$nmbank = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
		$no = '';
		foreach ($rekeningbank as $namabank => $valrek) {
			foreach ($valrek as $norek => $rekbank) {

				$no++;
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td align=center>" . $no . "</td>";
				$tab .= "<td>" . $nmbank[$namabank] . "</td>";
				$tab .= "<td>" . $norek . "</td>";
				$tab .= "<td align=right>" . @number_format($sawal[$norek], 2) . "</td>";
				$tab .= "<td align=right>" . @number_format($jlhbm[$norek], 2) . "</td>";
				$tab .= "<td align=right>" . @number_format($jlhbk[$norek], 2) . "</td>";
				$tab .= "<td align=right>" . @number_format(($sawal[$norek] + $jlhbm[$norek]) - $jlhbk[$norek], 2) . "</td>";
				$tab .= "</tr>";
			}
		}




		#= pembatas
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td colspan=7>&nbsp</td>";
		$tab .= "</tr>";
		#= pembatas




		#==== dari COA ====#

		#= saldo awal
		$str = "select noakun,kodeorg,periode, awal" . substr($periode, 5, 2) . " as sawal from " . $dbname . ".keu_saldobulanan 
			where kodeorg = '" . $kodeorg . "' and periode='" . str_replace("-", "", $periode) . "'  AND LEFT(noakun,5) IN ({$akunKasbank})";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nilaisawal[$bar['noakun']] = $bar['sawal'];
			$arrnoakun[$bar['noakun']] = $bar['noakun'];
		}





		// echo $str="select debet,kredit,noakun from  ".$dbname.".keu_jurnaldt_vw where noakun like '111%' and tanggal like '".$periode."%' and kodeorg='".$kodeorg."'";
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// 	$arrnoakun[$bar['noakun']]=$bar['noakun'];
		// 	$nilaimasuk[$bar['noakun']]+=doubleval(round($bar['debet'],2));
		// 	$nilaikeluar[$bar['noakun']]+=doubleval(round($bar['kredit'],2));
		// }

		$str = "select sum(debet) as debet,sum(kredit) as kredit,noakun from  " . $dbname . ".keu_jurnaldt_vw where LEFT(noakun,5) IN ({$akunKasbank})  and noakun not like '11104%'and tanggal like '" . $periode . "%' and kodeorg='" . $kodeorg . "' group by noakun";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrnoakun[$bar['noakun']] = $bar['noakun'];
			//if ($bar['jumlah'] > 0) {
			$nilaimasuk[$bar['noakun']] = $bar['debet'];
			//}
			//if ($bar['jumlah'] < 0) {
			$nilaikeluar[$bar['noakun']] = $bar['kredit'];
			//}
		}

		// echo "<pre>";
		// print_r($nilaimasuk);

		/*
		$str="select sum(jumlah) as jumlah,noakun2a from ".$dbname.".keu_kasbankdtht_vw 
			where tipetransaksi='M' and posting='1' and pembayaran=1 and tanggal like '".$periode."%' and kodeorght='".$kodeorg."'  group by noakun2a";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrnoakun[$bar['noakun2a']]=$bar['noakun2a'];
			$nilaimasuk[$bar['noakun2a']]=$bar['jumlah'];
		}
		
		
		$str="select sum(jumlah) as jumlah,noakun2a from ".$dbname.".keu_kasbankdtht_vw 
			where tipetransaksi='K' and posting='1' and pembayaran=1 and tanggal like '".$periode."%' and kodeorght='".$kodeorg."'  group by noakun2a";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrnoakun[$bar['noakun2a']]=$bar['noakun2a'];
			$nilaikeluar[$bar['noakun2a']]=$bar['jumlah'];
		}
		*/



		$no = 0;
		foreach ($arrnoakun as $noakun) {
			$no++;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td>" . $namaakun[$noakun] . "</td>";
			$tab .= "<td></td>";
			$tab .= "<td align=right>" . @number_format($nilaisawal[$noakun], 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($nilaimasuk[$noakun], 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($nilaikeluar[$noakun], 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($nilaisawal[$noakun] + $nilaimasuk[$noakun] - $nilaikeluar[$noakun], 2) . "</td>";
			$tab .= "</tr>";
		}

		$tab .= "<button onclick=preview('simpan') class=mybutton name=preview id=preview>" . $_SESSION['lang']['proses'] . "</button>";
		$tab .= "</tbody></table>";

		if ($tampil == 'show') {
			echo $tab;
		} else {

			try {
				$owlPDO->beginTransaction();
				# insert atau update
				$arrprd = explode("-", $periode);
				$bulanbank = $arrprd[1];
				$thn = $arrprd[0];
				$bln = $arrprd[1];
				$noUrut = 0;
				if ($bln == 12) {
					$nextbln = '01';
					$nextthn = $thn + 1;
				} else {
					$nextbln = addZero(intval($bln + 1), 2);
					$nextthn = $thn;
				}
				foreach ($rekeningbank as $namabank => $valrek) {
					foreach ($valrek as $norek => $rekbank) {

						# cek dulu apakah sudah ada atau belum
						$str = "select * from " . $dbname . ".keu_saldobank where kodeorg='" . $kodeorg . "' and periode='" . str_replace("-", "", $periode) . "' and norek='" . $norek . "'";
						$res = fetchData($str);
						# jika sudah ada datanya
						if (count($res) > 0) {
							if ($jlhbm[$norek] != 0 && $jlhbk[$norek] != 0) {
								# debet dan kredit
								$str = "update " . $dbname . ".keu_saldobank set debet" . $bulanbank . "='" . $jlhbm[$norek] . "', kredit" . $bulanbank . "='" . $jlhbk[$norek] . "' where kodeorg='" . $kodeorg . "' and periode='" . str_replace("-", "", $periode) . "' and norek='" . $norek . "'";
								$owlPDO->exec($str);
							} else {
								if ($jlhbm[$norek] != 0) {
									# debet
									$str = "update " . $dbname . ".keu_saldobank set debet" . $bulanbank . "='" . $jlhbm[$norek] . "' where kodeorg='" . $kodeorg . "' and periode='" . str_replace("-", "", $periode) . "' and norek='" . $norek . "'";
									$owlPDO->exec($str);
								}
								if ($jlhbk[$norek] != 0) {
									# kredit
									$str = "update " . $dbname . ".keu_saldobank set kredit" . $bulanbank . "='" . $jlhbk[$norek] . "' where kodeorg='" . $kodeorg . "' and periode='" . str_replace("-", "", $periode) . "' and norek='" . $norek . "'";
									$owlPDO->exec($str);
								}
							}
						} else { # jika belum ada
							if ($jlhbm[$norek] != 0 && $jlhbk[$norek] != 0) {
								# debet dan kredit
								$str = "insert into " . $dbname . ".keu_saldobank (kodeorg,periode,norek,debet" . $bulanbank . ",kredit" . $bulanbank . ") values ('" . $kodeorg . "','" . str_replace("-", "", $periode) . "','" . $norek . "','" . $jlhbm[$norek] . "','" . $jlhbk[$norek] . "')";
								$owlPDO->exec($str);
							} else {
								if ($jlhbm[$norek] != 0) {
									# debet
									$str = "insert into " . $dbname . ".keu_saldobank (kodeorg,periode,norek,debet" . $bulanbank . ") values ('" . $kodeorg . "','" . str_replace("-", "", $periode) . "','" . $norek . "','" . $jlhbm[$norek] . "')";
									$owlPDO->exec($str);
								}
								if ($jlhbk[$norek] != 0) {
									# kredit
									$str = "insert into " . $dbname . ".keu_saldobank (kodeorg,periode,norek,kredit" . $bulanbank . ") values ('" . $kodeorg . "','" . str_replace("-", "", $periode) . "','" . $norek . "','" . $jlhbk[$norek] . "')";
									$owlPDO->exec($str);
								}
							}
						}
						# cek dulu sudah ada transaksi bulan depan
						$salak = (($sawal[$norek] + $jlhbm[$norek]) - $jlhbk[$norek]);
						$str = "select * from " . $dbname . ".keu_saldobank where kodeorg='" . $kodeorg . "' and periode='" . $nextthn . $nextbln . "' and norek='" . $norek . "'";
						$res = fetchData($str);
						if (count($res) > 0) {
							# update sawal bulan depan
							$str = "update " . $dbname . ".keu_saldobank set awal" . $nextbln . "='" . $salak . "' where kodeorg='" . $kodeorg . "' and periode='" . $nextthn . $nextbln . "' and norek='" . $norek . "'";
							$owlPDO->exec($str);
						} else {
							# insert sawal bulan depan
							$str = "insert into " . $dbname . ".keu_saldobank (kodeorg,periode,norek,awal" . $nextbln . ") values ('" . $kodeorg . "','" . $nextthn . $nextbln . "','" . $norek . "','" . $salak . "')";
							$owlPDO->exec($str);
						}

						# ambil mata uang bank
						$str = "select matauang from " . $dbname . ".keu_5akunbank where noakun = '" . $norek . "' and status='1'";
						$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$bar = $res->fetch();
						$matauangrek = $bar['matauang'];
						/*
					$selisih=0;
					$nilkonversi=0;
					$niluangasli=0;
                    $dt1 = strtotime($periode);
                    $periodesebelum=date('Y-m', strtotime('-1 month', $dt1));
					if ($matauangrek!='IDR') {
						# Ambil kurs tanggal terakhir bulan lalu
						$str="select * from ".$dbname.".setup_matauangrate where left(daritanggal,7)='".$periodesebelum."' and kode='".$matauangrek."' order by daritanggal desc limit 1";
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$bar=$res->fetch();
						$kursbulanlalu=$bar['kurs'];
						
						# Ambil kurs tanggal terakhir bulan ini
						$str="select * from ".$dbname.".setup_matauangrate where left(daritanggal,7)='".$periode."' and kode='".$matauangrek."' order by daritanggal desc limit 1";
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$bar=$res->fetch();
						$kursbulanini=$bar['kurs'];

						$nilkonversi=(($sawal[$norek]*$kursbulanlalu)+($jlhbmluar[$norek])-($jlhbkluar[$norek]));
						$niluangasli=((($sawal[$norek]+$jlhbm[$norek])-$jlhbk[$norek])*$kursbulanini);
						$selisih=$niluangasli-$nilkonversi;

						if ($selisih!='0') {
							// Init
			                $dataRes['header'] = array();
			                $dataRes['detail'] = array();
							$maxDay = cal_days_in_month(CAL_GREGORIAN,$bln,$thn);

			                // Default Segment
			                $defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

			                // Kode Jurnal
			                $kodeJurnal = "KRSKB";

			                // No Jurnal
			                $tahunbulan=str_replace('-', '', $periode);
			                $nojurnal = $tahunbulan.$maxDay."/".$kodeorg."/".$kodeJurnal."/00001";

			                // Get Kurs Parameter Jurnal
			                $qAkun = selectQuery($dbname,'keu_5parameterjurnal',"noakundebet,noakunkredit",
			                                                         "kodeaplikasi='KURS' and jurnalid='".$kodeJurnal."'");
			                $resAkun = fetchData($qAkun);
			                if(empty($resAkun)) exit("Warning: Parameter Akun untuk jurnalid ".$kodeJurnal.
	                                                                 " belum ada\nSilahkan hubungi IT dengan melampirkan pesan error ini");

	                        if($selisih<0) {
	                            $akundebet = $resAkun[0]['noakunkredit'];
	                            $akunkredit = $resAkun[0]['noakundebet'];
	                            $jumlahselisih=$selisih*(-1);
	                        }
	                        if($selisih>0) {
	                            $akundebet = $resAkun[0]['noakundebet'];
	                            $akunkredit = $resAkun[0]['noakunkredit'];
	                            $jumlahselisih=$selisih;
	                        }

			                // Data Detail
	                        $noUrut++;
	                        $dataRes['detail'][] = array(
	                            'nojurnal'=>$nojurnal,
	                            'tanggal'=>$tahunbulan.$maxDay,
	                            'nourut'=>$noUrut,
	                            'noakun'=>$akundebet,
	                            'keterangan'=>'Selisih Kurs '.$matauangrek.' rekening : '.$norek.' untuk '.$kodeorg.
	                                    ' per '.$periode,
	                            'jumlah'=>$jumlahselisih,
	                            'matauang'=>'IDR',
	                            'kurs'=>'1',
	                            'kodeorg'=>$kodeorg,
	                            'kodekegiatan'=>'',
	                            'kodeasset'=>'',
	                            'kodebarang'=>'',
	                            'nik'=>'',
	                            'kodecustomer'=>'',
	                            'kodesupplier'=>'',
	                            'noreferensi'=>$kodeJurnal,
	                            'noaruskas'=>'',
	                            'kodevhc'=>'',
	                            'nodok'=>'',
	                            'kodeblok'=>'',
	                            'revisi'=>'0',
	                            'kodesegment' => $defSegment
	                        );

	                        $noUrut++;
	                        $dataRes['detail'][] = array(
	                            'nojurnal'=>$nojurnal,
	                            'tanggal'=>$tahunbulan.$maxDay,
	                            'nourut'=>$noUrut,
	                            'noakun'=>$akunkredit,
	                            'keterangan'=>'Selisih Kurs '.$matauangrek.' rekening : '.$norek.' untuk '.$kodeorg.
	                                    ' per '.$periode,
	                            'jumlah'=>$jumlahselisih * (-1),
	                            'matauang'=>'IDR',
	                            'kurs'=>'1',
	                            'kodeorg'=>$kodeorg,
	                            'kodekegiatan'=>'',
	                            'kodeasset'=>'',
	                            'kodebarang'=>'',
	                            'nik'=>'',
	                            'kodecustomer'=>'',
	                            'kodesupplier'=>'',
	                            'noreferensi'=>$kodeJurnal,
	                            'noaruskas'=>'',
	                            'kodevhc'=>'',
	                            'nodok'=>'',
	                            'kodeblok'=>'',
	                            'revisi'=>'0',
	                            'kodesegment' => $defSegment
	                        );
						}
					}
					*/
					}
				}

				#= dasi noakun
				foreach ($arrnoakun as $noakun) {
					$nilaisalak[$noakun] = $nilaisawal[$noakun] + $nilaimasuk[$noakun] - $nilaikeluar[$noakun];
					#= delet 1st
					$str = "delete from " . $dbname . ".keu_saldobulanan where kodeorg='" . $kodeorg . "' and periode='" . $nextthn . $nextbln . "' and noakun='" . $noakun . "' ";
					$owlPDO->exec($str);

					#= insert 2nd
					$str = "insert into " . $dbname . ".keu_saldobulanan (kodeorg,periode,noakun,awal" . $nextbln . ") values ('" . $kodeorg . "','" . $nextthn . $nextbln . "','" . $noakun . "','" . $nilaisalak[$noakun] . "')";
					$owlPDO->exec($str);

					// exit("Error:".$str1.____.$str2);

				}





				// echo "<pre>";
				// print_r($dataRes['header']);
				// print_r($dataRes['detail']);
				// echo "</pre>";
				// exit('warning : '.count($dataRes));

				if (count($dataRes) > 0) {

					// Data Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => $tahunbulan . $maxDay,
						'tanggalentry' => date('Ymd'),
						'posting' => '0',
						'totaldebet' => '0',
						'totalkredit' => '0',
						'amountkoreksi' => '0',
						'noreferensi' => $kodeJurnal,
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					// Delete Jurnal jika sudah ada
					$errorDB = "";
					$delJurnal = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal . "'");
					try {
						$owlPDO->exec($delJurnal);
					} catch (PDOException $e) {
						print " DB Error: " . $e->getMessage();
						exit();
					}

					// Insert Jurnal Header
					$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					try {
						$owlPDO->exec($queryH);
					} catch (PDOException $e) {
						$errorDB .= "Header : " . $e->getMessage();
					}

					// Insert Jurnal Detail
					if ($errorDB == '') {
						foreach ($dataRes['detail'] as $key => $dataDet) {
							$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
							try {
								$owlPDO->exec($queryD);
							} catch (PDOException $e) {
								$errorDB .= "Detail " . $key . " :" . $e->getMessage();
								break;
							}
						}
					}

					// Rollback
					if ($errorDB != "") {
						$where = "nojurnal='" . $nojurnal . "'";
						$queryRB = "delete from `" . $dbname . "`.`keu_jurnalht` where " . $where;
						try {
							$owlPDO->exec($queryRB);
						} catch (PDOException $e) {
							echo "Rollback 1 Error :" . $e->getMessage();
						}
						echo $errorDB;
					}
				}

				# exec
				#exit("error".$str);
				$owlPDO->commit();
			} catch (PDOException $e) {
				$owlPDO->rollback();
				echo "Error, " . addslashes($e->getMessage());
				die();
			}
		}
		break;
}
