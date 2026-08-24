<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//====================================
//default setting on database 
//1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi 
$tipetransaksi = 1;
//=============================================
// exit('warning : masukkk');
if (isTransactionPeriod()) //check if transaction period is normal
{
	$nodok = $_POST['nodok'];
	$idsupplier = $_POST['idsupplier'];
	$tanggal = tanggalsystem($_POST['tanggal']);
	$nopo = $_POST['nopo'];
	$nofaktur = $_POST['nofaktur'];
	$nosj = $_POST['nosj'];
	$qty = $_POST['qty'];
	$kodebarang = $_POST['kodebarang'];
	$kodegudang = $_POST['kodegudang'];
	$post = 0;
	$user = $_SESSION['standard']['userid'];
	$satuan = $_POST['satuan']; //satuan pada master barang
	$nopp = $_POST['nopp'];
	$catatan = $_POST['catatan'];
	$fileupload = $_POST['fileupload'];

	$status = 0;
	$user1 = $_SESSION['standard']['userid'];
	$str = "select * from " . $dbname . ".log_transaksiht where notransaksi='" . $nodok . "'";
	$res1 = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	if (owlBaris($res1) == 1) {
		$str = "select distinct nopo from " . $dbname . ".log_transaksiht where notransaksi='" . $nodok . "' and nopo='" . $nopo . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		if (owlBaris($res) == 0) {
			$status = 8;
		} else {
			while ($bar1 = $res1->fetch()) {
				$user1 = $bar1->user;
			}

			if ($_SESSION['standard']['userid'] == $user1) {
				$status = 1;
			} else {
				exit('Error: This transaction belongs to other user, please reload and start over');
			}
		}
	}

	//=======================================================

	$str = "select * from " . $dbname . ".log_transaksidt where notransaksi='" . $nodok . "' and kodebarang='" . $kodebarang . "' and nopp='" . $nopp . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	if (owlBaris($res) > 0) {
		$status = 2;
	}

	$str = "select * from " . $dbname . ".log_transaksiht where notransaksi='" . $nodok . "' and post=1";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	if (owlBaris($res) > 0) {
		$status = 3;
	}

	//==========================================

	if ($status == 5 or $status == 2 or $status == 1) {
		$str = "select * from " . $dbname . ".log_transaksidt where notransaksi='" . $nodok . "' and statussaldo=1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		if (owlBaris($res) > 0) {
			$status = 3;
			exit(" Error, transaksi sudah dalam proses posting");
		}
	}

	//==========================================            

	//kode pt dan kurs===================================

	$kurs = 1; // default untuk kurs sebagai pengali
	$kodept = '';
	$kodeunit = '';
	$str = "select kodeorg,kurs,matauang,addcost as ongkosangkutan,pbbkb,kodeunit from " . $dbname . ".log_poht where nopo='" . $nopo . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$matauang = '';
	while ($bar = $res->fetch()) {
		$kodept = $bar->kodeorg;
		$kurs = $bar->kurs;
		$matauang = str_replace(" ", "", $bar->matauang);
		$ongAngkut = $bar->ongkosangkutan * $bar->kurs;
		$pbbkb = $bar->pbbkb * $bar->kurs;
		$kodeunit = $bar->kodeunit;
	}

	/*$str2="select hargasatuan,jumlahpesan,satuan,matauang,kodebarang from ".$dbname.".log_podt where 
          nopo='".$nopo."'";
    $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_ASSOC);
	while($rsd=$res2->fetch())
	{
        $hrgBar[$rsd['kodebarang']]=$rsd['hargasatuan']*$kurs;
        $jmlhBar[$rsd['kodebarang']]=$rsd['jumlahpesan'];
        $ttHarga+=$rsd['hargasatuan'];
    }
    if($ongAngkut!=0){
        foreach($hrgBar as $rw=>$lst){
            $persen[$rw]=(($lst/$ttHarga)*$ongAngkut);
            $hrgAdd[$rw]=$persen[$rw]/$jmlhBar[$rw];
        }
    }*/

	$str2 = "select hargasatuan,jumlahpesan,satuan,matauang,kodebarang,nopp from " . $dbname . ".log_podt where 
          nopo='" . $nopo . "'";
	$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_ASSOC);
	while ($rsd = $res2->fetch()) {
		$hrgBar[$rsd['kodebarang']][$rsd['nopp']] = $rsd['hargasatuan'] * $kurs;
		$jmlhBar[$rsd['kodebarang']][$rsd['nopp']] = $rsd['jumlahpesan'];
		$ttHarga += ($rsd['hargasatuan'] * $kurs) * $rsd['jumlahpesan'];
	}
	if ($ongAngkut != 0) {
		foreach ($hrgBar as $rw => $lstnopp) {
			foreach ($lstnopp as $pp => $lst) {
				$hrgAdd[$rw][$pp] = (($lst / $ttHarga) * $ongAngkut);
			}
		}
	}
	if ($pbbkb != 0) {
		foreach ($hrgBar as $rw => $lstnopp) {
			foreach ($lstnopp as $pp => $lst) {
				$hrgAddpbbkb[$rw][$pp] = (($lst / $ttHarga) * $pbbkb);
			}
		}
	}

	//harga satuan base on conversion==============================
	$str = "select nopp,hargasatuan,ongkangkut,jumlahpesan,satuan,matauang,kodebarang from " . $dbname . ".log_podt where 
	      nopo='" . $nopo . "' and kodebarang='" . $kodebarang . "'";
	// exit('error:'.$str);
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$jumlahpesan = '';
	$hargasatuan = 0;

	while ($bar = $res->fetch()) {

		$jumlahpesan = $bar->jumlahpesan;
		if ($bar->ongkangkut > 0) {

			// cek status ongkir
			$str_b_o = "SELECT ongkir FROM " . $dbname . ".log_5masterbarang WHERE kodebarang = '" . $bar->kodebarang . "' ";
			$res_b_o = $owlPDO->query($str_b_o) or die(print " Gagal: " . PDOException::getMessage());
			$res_b_o->setFetchMode(PDO::FETCH_OBJ);
			while ($bar_b_o = $res_b_o->fetch()) {
				$statusongkir = $bar_b_o->ongkir;
			}
			if ($statusongkir == '2') {
				$hargasatuan = ($bar->hargasatuan + $bar->ongkangkut);
			} else {
				$hargasatuan = ($bar->hargasatuan + $bar->ongkangkut);
				//$hargasatuan=$bar->hargasatuan;	
			}
		} else {
			$hargasatuan = $bar->hargasatuan;
		}
		//konversi satuan jika satuan default kodebarang tidak sama dengan satuan po
		if ($satuan != $bar->satuan) {
			$jlhkonversi = 1; //tidak nol untuk menhindari devide by zero
			$str1 = "select jumlah from " . $dbname . ".log_5stkonversi 
			       where darisatuan='" . $satuan . "' and satuankonversi='" . $bar->satuan . "'
                                                        and kodebarang='" . $bar->kodebarang . "'";
			$res3 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
			$res3->setFetchMode(PDO::FETCH_OBJ);
			if (owlBaris($res3) > 0) {
				while ($bar2 = $res3->fetch()) {
					$jlhkonversi = $bar2->jumlah;
				}
			}
			if ($jlhkonversi != 0) {
				$hargasatuan = $bar->hargasatuan * $jlhkonversi;
			}
		}

		if ($ongAngkut != 0 || $pbbkb != 0) {
			$hargasatuan = $hargasatuan + $hrgAdd[$bar->kodebarang] + $hrgAddpbbkb[$bar->kodebarang][$bar->nopp];
		}



		// cek apakah SO tersebut sudah release where barang, agar barang 1 bisa barang 2 g bisa
		$str3 = "select * from " . $dbname . ".log_sorefrensi where 
         	nopo ='" . $nopo . "' ";
		$res3 = $owlPDO->query($str3) or die(print " Gagal: " . PDOException::getMessage());
		$res3->setFetchMode(PDO::FETCH_ASSOC);
		while ($rsd = $res3->fetch()) {
			// cek apakah SO tersebut sudah release
			$str33 = "select * from " . $dbname . ".log_poht where 
         	    nopo ='" . $rsd['noso'] . "' ";
			$res33 = $owlPDO->query($str33) or die(print " Gagal: " . PDOException::getMessage());
			$res33->setFetchMode(PDO::FETCH_ASSOC);
			while ($rsd0 = $res33->fetch()) {
				if ($rsd0['stat_release'] != '1') {
					exit("warning : PO = " . $nopo . " tersebut masuk SO referensi, SO " . $rsd['noso'] . " belum release...");
				}
			}
		}
		$str3 = "select * from " . $dbname . ".log_sorefrensi where 
			nopo ='" . $nopo . "' and nopp = '" . $bar->nopp . "' and kodebarang='" . $bar->kodebarang . "'";
		$res3 = $owlPDO->query($str3) or die(print " Gagal: " . PDOException::getMessage());
		$res3->setFetchMode(PDO::FETCH_ASSOC);
		while ($rsd = $res3->fetch()) {

			// cek apakah SO tersebut sudah release
			$str33 = "select * from " . $dbname . ".log_poht where 
				nopo ='" . $rsd['noso'] . "' ";
			$res33 = $owlPDO->query($str33) or die(print " Gagal: " . PDOException::getMessage());
			$res33->setFetchMode(PDO::FETCH_ASSOC);
			while ($rsd0 = $res33->fetch()) {
				if ($rsd0['stat_release'] != '1') {
					exit("warning : PO = " . $nopo . " atas PR = " . $bar->nopp . " dan Kodebarang = " . $bar->kodebarang . " tersebut masuk SO referensi, SO " . $rsd['noso'] . " belum release...");
				}
			}

			$hargaongkos = $rsd['nilai_proporsi'] / $rsd['jumlah'];
			// bulat 2
			$hargaongkos = round($hargaongkos, 2);
		}
	}

	if ($kurs == 0 or $matauang == 'IDR' or $matauang == '') {
		$kurs = 1;
	}

	$hargasatuan = $hargasatuan * $kurs;

	//==================ambil jumlah lalu====================
	$jumlahlalu = 0;
	$str = "select a.jumlah as jumlah,b.nopo as nopo,a.notransaksi as notransaksi 
	    from " . $dbname . ".log_transaksidt a,
	         " . $dbname . ".log_transaksiht b
		   where a.notransaksi=b.notransaksi and  
		   b.nopo='" . $nopo . "' 
	       and a.kodebarang='" . $kodebarang . "'
	       and a.nopp='" . $nopp . "'
		   and a.notransaksi<'" . $nodok . "'
		   order by notransaksi desc limit 1";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$jumlahlalu = $bar->jumlah;
	}
	//===============================================================		 		  
	//periksa apakah sudah ada status 7
	if ($status == 0 or $status == 1 or $status == 2) {
		$stro = "select a.post from " . $dbname . ".log_transaksiht a
	       left join " . $dbname . ".log_transaksidt b
		   on a.notransaksi=b.notransaksi
	       where a.tanggal>" . $tanggal . " and a.kodept='" . $kodept . "'
		   and b.kodebarang='" . $kodebarang . "' and kodegudang='" . $kodegudang . "'
		   and a.post=1";
		$res = $owlPDO->query($stro) or die(print " Gagal: " . PDOException::getMessage());
		if (owlBaris($res) > 0) {
			$status = 7;
			echo " Error :" . $_SESSION['lang']['tanggaltutup'];
			exit(0);
		}
	}
	//periksa apakah harga barang sudah diisi dalam PO
	if ($hargasatuan == 0) {
		exit('Error: belum ada harga pada PO:' . $nopo);
	}
	//=============================start input/update	
	//status=0



	if ($status == 0) {


		if ($fileupload != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$newfilename = str_replace($filetype, '', $_FILES['file']['name']);
				$filename = $newfilename . "_" . date("Ymd") . "" . $filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];
				$path = "fileupload/penerimaanbarang/" . $filename;
				// $path="fileupload/penerimaanbarang/".basename($_FILES['file']['name']);
				//          $path = "fileupload/penerimaanbarang/".$nodok."/".basename($_FILES['file']['name']);
				// if (!file_exists($path)) {
				//   mkdir($path, 0777, true);
				// } 
				// exit('error:'.$path);


				if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
					if ($_FILES['file']['size'] <= 512000) {
						move_uploaded_file($file_tmpname, $path);
					} else {
						exit("warning : Ukuran file upload maksimal 512 kb");
					}
				} else {
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		} else if ($fileupload == '') {
			$str = "select * from " . $dbname . ".log_transaksiht where notransaksi='" . $notransaksi . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$bar = $res->fetch();
			$path = $bar->namafile;
		}

		#= cek apakah ada file upload, jika tidak ada maka tidak bisa save
		$str = "select count(*) as jumlah from " . $dbname . ".listfile_log_penerimaan where notransaksi='" . $nodok . "' and namafile like '%" . $kodebarang . "%' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$bar = $res->fetch();
		$jumlahfile = $bar->jumlah;

		if ($jumlahfile == 0) {
			// exit("Warning:File upload untuk dokumen ini belum ada, silahkan upload file dahulu");
		}

		$str = "insert into " . $dbname . ".log_transaksiht (
				`tipetransaksi`,`notransaksi`,`tanggal`,
				`kodept`,`nopo`,`nosj`,`kodegudang`,`user`,
				`idsupplier`,`nofaktur`,`post`,`namafile`)
		values(" . $tipetransaksi . ",'" . $nodok . "'," . $tanggal . ",
				'" . $kodept . "','" . $nopo . "','" . $nosj . "','" . $kodegudang . "'," . $user . ",
					'" . $idsupplier . "','" . $nofaktur . "'," . $post . ",'" . $filename . "'
		)";
		// exit('error:'.$str);

		try {
			$owlPDO->exec($str); //insert hedaer

			// $subgdg = explode('/', $nopo);
			// $countApp = getCountApproval('GR', $subgdg[4]);

			$subgdg = $kodeunit;
			$countApp = getCountApproval('GR', $kodeunit);

			// exit("Error:".$countApp);
			for ($i = 1; $i <= $countApp; $i++) {
				$str = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('" . $nodok . "','GR','" . $i . "','" . $_POST['persetujuan' . $i] . "','0')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
			// exit("Error:A");
			if (substr($kodebarang, 0, 1) == '9') {
				$hargasatuan = '0';
			} else {
				$hargasatuan = $hargasatuan;
			}

			// $str="insert into ".$dbname.".log_transaksidt (
			//                     `notransaksi`,`kodebarang`,
			//                     `satuan`,`jumlah`,`jumlahlalu`,
			//                     `hargasatuan`,`kodeblok`,`nopp`,`nopo`,`catatan`,`namafile`)
			//                     values('".$nodok."','".$kodebarang."',
			//                     '".$satuan."',".$qty.",".$jumlahlalu.",
			//                     ".$hargasatuan.",'','".$nopp."','".$nopo."','".$catatan."','".$filename."')";
			$str = "insert into " . $dbname . ".log_transaksidt (
				`notransaksi`,`kodebarang`,
				`satuan`,`jumlah`,`jumlahlalu`,
				`hargasatuan`,`kodeblok`,`nopp`,`nopo`,`catatan`,`namafile`,`ongkir`)
				values('" . $nodok . "','" . $kodebarang . "',
				'" . $satuan . "'," . $qty . "," . $jumlahlalu . ",
				" . $hargasatuan . ",'','" . $nopp . "','" . $nopo . "','" . $catatan . "','" . $filename . "','" . $hargaongkos . "')";
			// exit('error:'.$str);
			try {
				$owlPDO->exec($str); //insert detail

				//update PO jumlah masuk pada posting
				//update statuspo pada table po
				$str = "update " . $dbname . ".log_poht set statuspo=3 where nopo='" . $nopo . "'";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	}
	//============================
	//status=1
	else if ($status == 1) {

		if ($fileupload != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$newfilename = str_replace($filetype, '', $_FILES['file']['name']);
				$filename = $newfilename . "_" . date("Ymd") . "" . $filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];
				$path = "fileupload/penerimaanbarang/" . $filename;

				if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
					if ($_FILES['file']['size'] <= 512000) {
						move_uploaded_file($file_tmpname, $path);
					} else {
						exit("warning : Ukuran file upload maksimal 512 kb");
					}
				} else {
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		}

		if (substr($kodebarang, 0, 1) == '9') {
			$hargasatuan = '0';
		} else {
			$hargasatuan = $hargasatuan;
		}

		// $str="insert into ".$dbname.".log_transaksidt (
		// 	`notransaksi`,`kodebarang`,
		// 	`satuan`,`jumlah`,`jumlahlalu`,
		// 	`hargasatuan`,`kodeblok`,`nopp`,`nopo`,`catatan`,`namafile`)
		// 	values('".$nodok."','".$kodebarang."',
		// 	'".$satuan."',".$qty.",".$jumlahlalu.",
		// 	".$hargasatuan.",'','".$nopp."','".$nopo."','".$catatan."','".$filename."')";
		$str = "insert into " . $dbname . ".log_transaksidt (
			`notransaksi`,`kodebarang`,
			`satuan`,`jumlah`,`jumlahlalu`,
			`hargasatuan`,`kodeblok`,`nopp`,`nopo`,`catatan`,`namafile`,`ongkir`)
			values('" . $nodok . "','" . $kodebarang . "',
			'" . $satuan . "'," . $qty . "," . $jumlahlalu . ",
			" . $hargasatuan . ",'','" . $nopp . "','" . $nopo . "','" . $catatan . "','" . $filename . "','" . $hargaongkos . "')";
		// exit('error:'.$str);
		try {
			$owlPDO->exec($str); //insert detail

			//update table po statuspo
			$str = "update " . $dbname . ".log_poht set statuspo=3 where nopo='" . $nopo . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	}
	//============================update detail
	//status=2
	else if ($status == 2) {
		$editfile = "";
		if ($fileupload != '') {
			if ($_FILES['file']['error'] == 0) {
				$filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
				$newfilename = str_replace($filetype, '', $_FILES['file']['name']);
				$filename = $newfilename . "_" . date("Ymd") . "" . $filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];
				$path = "fileupload/penerimaanbarang/" . $filename;

				if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
					if ($_FILES['file']['size'] <= 512000) {
						$editfile = ",namafile='" . $filename . "'";
						move_uploaded_file($file_tmpname, $path);
					} else {
						exit("warning : Ukuran file upload maksimal 512 kb");
					}
				} else {
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		} else if ($fileupload == '') {
		}

		$str = "update " . $dbname . ".log_transaksidt set
				`jumlah`=" . $qty . ",catatan='" . $catatan . "',
					`updateby`=" . $user . " 
					" . $editfile . " 
					where `notransaksi`='" . $nodok . "' 
					and `kodebarang`='" . $kodebarang . "'  and `nopp`='" . $nopp . "'";
		try {
			$affected_rows = $owlPDO->exec($str); //insert detail
			if ($affected_rows < 1) {
				echo " Warning, (tidak ada perubahan data)";
			} else {
				//update jumlah lalu pada transaksi berikutnya jika ada
				//ambil no trx yg berikutnya
				$notrxnext = '';
				$strc = "select a.notransaksi as notrx from " . $dbname . ".log_transaksidt a, " . $dbname . ".log_transaksiht b
						where a.notransaksi= b.notransaksi 
							and b.nopo='" . $nopo . "'
							and a.notransaksi>'" . $nodok . "'
							and a.kodebarang='" . $kodebarang . "'
							and a.nopp='" . $nopp . "'
							order by notrx asc limit 1";
				$resc = $owlPDO->query($strc) or die(print " Gagal: " . PDOException::getMessage());
				$resc->setFetchMode(PDO::FETCH_OBJ);
				while ($barc = $resc->fetch()) {
					$notrxnext = $barc->notrx;
				}

				if ($notrxnext != '') {
					$str = "update " . $dbname . ".log_transaksidt set
					`jumlahlalu`=" . $qty . ",catatan='" . $catatan . "',
						`updateby`=" . $user . " 
						where `notransaksi`='" . $notrxnext . "'
						and `kodebarang`='" . $kodebarang . "' and `nopp`='" . $nopp . "'";
					try {
						$affected_rows = $owlPDO->exec($str);
						if ($affected_rows < 1) {
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						die();
					}
				}
			}
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	}
	//============================return message
	//status=3
	if ($status == 3) {
		echo " Gagal: Data has been posted";
	}
	if ($status == 8) {
		echo " Gagal: Material not registred on PO : " . $nodok;
	}
} else {
	echo " Error: Transaction Period missing";
}
