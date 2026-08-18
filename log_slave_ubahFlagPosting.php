<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$notransaksi = $_POST['notransaksi'];
$status = $_POST['status'];
$gudang = $_POST['gudang'];
$user = $_SESSION['standard']['userid'];
$perbaiki = $_POST['perbaiki'];

if ($perbaiki == '1') {
	$barang = $_POST['barang'];
	$tanggal = $_POST['tanggal'];

	#ambil tanggal aktif
	$mulai = '';
	$sampai = '';
	$periode = substr($tanggal, 0, 7);
	$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $gudang . "' and periode='" . $periode . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$mulai = $bar->tanggalmulai;
		$sampai = $bar->tanggalsampai;
	}
	if ($mulai == '' or $sampai == '') {
		exit(" Error: tanggal mulai dan tanggal sampai periode aktif belum ada");
	}
	#ambil saldo awal
	$str = "select a.kodebarang,a.saldoawalqty,a.saldoakhirqty,a.hargarata,a.nilaisaldoawal,b.namabarang,b.satuan from " . $dbname . ".log_5saldobulanan a 
              left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang where a.kodebarang = '" . $barang . "' and a.kodegudang='" . $gudang . "' and a.periode='" . $periode . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$Dt['saldoawalqty'][$bar->kodebarang] = $bar->saldoawalqty;
		$Dt['nilaisaldoawal'][$bar->kodebarang] = $bar->nilaisaldoawal;
		$Dt['saldoakhirqty'][$bar->kodebarang] = $bar->saldoakhirqty;
		$Dt['hargarata'][$bar->kodebarang] = $bar->hargarata;
		$Dt['namabarang'][$bar->kodebarang] = $bar->namabarang;
		$Dt['satuan'][$bar->kodebarang] = $bar->satuan;
	}
	#ambil data masuk
	$str = "select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargasatuan) as rpmasuk from " . $dbname . ".log_transaksi_vw where kodebarang = '" . $barang . "' and kodegudang='" . $gudang . "' and tanggal>='" . $mulai . "' and tanggal <='" . $sampai . "'
              and tipetransaksi<5 and statussaldo=1 group by kodebarang";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		// sum(0.5;0.2;0.1)=0.7999999999999999? fix pake round,5
		$masuk[$bar->kodebarang] = round($bar->jumlah, 5);
		@$rpmasuk[$bar->kodebarang] += $bar->rpmasuk;
	}

	#ambil data keluar tidak proporsi ke blok kecil
	$str = "select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargarata) as rpkeluar from " . $dbname . ".log_transaksi_vw where  
	kodebarang='" . $barang . "' and kodegudang='" . $gudang . "' and tanggal>='" . $mulai . "' and tanggal <='" . $sampai . "'
    and tipetransaksi>4 and statussaldo=1 
	and notransaksi not in (select notransaksi from " . $dbname . ".log_transaksi_vw_detail where kodebarang = '" . $barang . "' and kodegudang='" . $gudang . "' and tanggal>='" . $mulai . "' and tanggal <='" . $sampai . "' and tipetransaksi>4 and statussaldo=1 group by notransaksi)
	group by kodebarang";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		// ini tidak di round kenapa? karena ada blok kecil di palma
		$keluar[$bar->kodebarang] = $bar->jumlah;
		@$rpkeluar[$bar->kodebarang] += $bar->rpkeluar;
	}

	#ambil data keluar
	$str = "select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargarata) as rpkeluar from " . $dbname . ".log_transaksi_vw_detail where  kodebarang='" . $barang . "' and kodegudang='" . $gudang . "' and tanggal>='" . $mulai . "' and tanggal <='" . $sampai . "'
              and tipetransaksi>4 and statussaldo=1 group by kodebarang";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		// ini tidak di round kenapa? karena ada blok kecil di palma
		$keluar[$bar->kodebarang] += $bar->jumlah;
		@$rpkeluar[$bar->kodebarang] += $bar->rpkeluar;
	}

	#cek keluar blm posting
	$str = "select * from " . $dbname . ".log_transaksi_vw_detail where  kodebarang='" . $barang . "' and kodegudang='" . $gudang . "' and tanggal>='" . $mulai . "' and tanggal <='" . $sampai . "'
              and tipetransaksi>4 and statussaldo=0";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$notransaksiCounter = 0;
	while ($bar = $res->fetch()) {
		$notransaksiCounter++;
		// ini tidak di round kenapa? karena ada blok kecil di palma
		$keluarblmposting[$bar->kodebarang] += $bar->jumlah;
	}

	if ($notransaksiCounter > 1) {
		exit("warning: Transaksi Belum Posting Lebih Dari Satu, silahkan lakukan rekalkulasi stock, di menu PENGADAAN > PROSES > 2. REKALKULASI STOCK.");
	}

	#hilangkan blank
	$fixdata = array();
	foreach ($Dt['saldoawalqty'] as $key => $val) {
		if (!isset($masuk[$key])) {
			$masuk[$key] = 0;
		}
		if (!isset($keluar[$key])) {
			$keluar[$key] = 0;
		}


		$seharusnya = $Dt['saldoawalqty'][$key] + $masuk[$key] - $keluar[$key];

		// // CEK JUMLAH KELUAR SEHARUSNYA VS outstanding
		// // exit("warning: ".$seharusnya." - ".$keluarblmposting[$key]." ");
		// if ($keluarblmposting[$key] > $seharusnya) {
		// 	// kasih toleransi kalau minus nya 0.00000xxxxx
		// 	if (round($keluarblmposting[$key] - $seharusnya, 5) == 0) {
		// 		$seharusnya = $keluarblmposting[$key];
		// 	} else {
		// 		exit("warning: Saldo Minus (".$seharusnya." ||| ".$keluarblmposting[$key].") , Silahkan Hubungi Tim IT ");
		// 	}
		// }
		$selisih = $keluarblmposting[$key] - $seharusnya;
		$toleransi = 0.0001;

		if ($keluarblmposting[$key] > $seharusnya) {

			if (abs($selisih) <= $toleransi) {
				$seharusnya = $keluarblmposting[$key];
			} else {
				exit("warning: Saldo Minus (".$seharusnya." ||| ".$keluarblmposting[$key].") , Silahkan Hubungi Tim IT ");
			}
		}

		// exit("warning: ".$seharusnya." = ".$Dt['saldoawalqty'][$key]." + ".$masuk[$key]." - ".$keluar[$key]." ");
		if ($seharusnya != $Dt['saldoakhirqty'][$key]) {
			$fixdata['saldoawal'][$key] = $Dt['saldoawalqty'][$key];
			$fixdata['saldoakhir'][$key] = $Dt['saldoakhirqty'][$key];
			$fixdata['masuk'][$key] = $masuk[$key];
			$fixdata['keluar'][$key] = $keluar[$key];
			$fixdata['seharusnya'][$key] = $seharusnya;

			$fixdatarp['masuk'][$key] = $rpmasuk[$key] > 0 ? $rpmasuk[$key] : 0;
			$fixdatarp['keluar'][$key] = $rpkeluar[$key] > 0 ? $rpkeluar[$key] : 0;
			$fixdatarp['saldoakhir'][$key] = $Dt['nilaisaldoawal'][$key] + $fixdatarp['masuk'][$key] - $fixdatarp['keluar'][$key];
			$fixdatarp['hargarata'][$key] = $fixdata['seharusnya'][$key] > 0 ? $fixdatarp['saldoakhir'][$key] / $fixdata['seharusnya'][$key] : 0;
		}
	}
	$no = 0;
	if (count($fixdata) > 0) {
		#update log_5saldobulanan
		foreach ($fixdata['saldoawal'] as $key => $val) {
			$no++;

			if ($fixdata['seharusnya'][$key] == 0) {
				$fixdatarp['saldoakhir'][$key] = 0;
			}

			$str = "update " . $dbname . ".log_5saldobulanan set saldoakhirqty=" . $fixdata['seharusnya'][$key] . ",qtymasuk=" . $fixdata['masuk'][$key] . ",qtykeluar=" . $fixdata['keluar'][$key] . ",
                   hargarata=" . $fixdatarp['hargarata'][$key] . ", qtymasukxharga=" . $fixdatarp['masuk'][$key] . ",qtykeluarxharga=" . $fixdatarp['keluar'][$key] . ",
                   nilaisaldoakhir=" . $fixdatarp['saldoakhir'][$key] . " where kodebarang='" . $key . "' and kodegudang='" . $gudang . "'
                   and periode='" . $periode . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo $e->getMessage();
			}
		}
	}
} else {
	#periksa apakah semua detail sudah status saldo 1
	$str = "select kodebarang from " . $dbname . ".log_transaksidt where notransaksi='" . $notransaksi . "'  and statussaldo=0";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	if (owlBaris($res) > 0) { #jika belum maka jangan ubah flag
		echo "Error : There is still unsucceed transaction, please re-process";
	} else {  #jika sudah, maka ubah flag      
		$str = "update " . $dbname . ".log_transaksiht set post=" . $status . ", postedby=" . $user . ",statusjurnal=1
					 where notransaksi='" . $notransaksi . "'  and kodegudang='" . $gudang . "'";
		try {
			$affected_rows = $owlPDO->exec($str);
			if ($affected_rows < 1) {
				echo "Error : post status update nothing";
			}
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	}
}
