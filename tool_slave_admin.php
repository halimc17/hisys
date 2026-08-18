<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/zFunction.php');

$method = $_POST['method'];

if (isset($_POST['method3'])) {
	$method = $_POST['method3'];
}

if (isset($_POST['methodpenjualan'])) {
	$method = $_POST['methodpenjualan'];
}

$tanggalpenjualan = checkPostGet('tanggalpenjualan', '');
$nokontrak = checkPostGet('nokontrak', '');
$nodo = checkPostGet('nodo', '');

$listTransaksi =     isset($_POST['listTransaksi']) ? explode(",", $_POST['listTransaksi']) : array();
$listTransaksi2 =    isset($_POST['listTransaksi2']) ? explode(",", $_POST['listTransaksi2']) : array();
$pilUn_1 =	isset($_POST['pilUn_1']) ? $_POST['pilUn_1'] : '';
$pilUn_5 =	isset($_POST['pilUn_5']) ? $_POST['pilUn_5'] : '';
$unitId =	isset($_POST['unitId']) ? $_POST['unitId'] : '';
$periodeId =	isset($_POST['periodeId']) ? $_POST['periodeId'] : '';
$bloklama =	isset($_POST['bloklama']) ? $_POST['bloklama'] : '';
$blokbaru =	isset($_POST['blokbaru']) ? $_POST['blokbaru'] : '';
$no = 0;
if ($pilUn_1 == '') {
	// exit('warning: Jenis Tidak Boleh Kosong');
}
foreach ($listTransaksi as $dtr => $lst) {
	$no++;
	// if($no==1 || $no==2)
	if ($no == 1) {
		$notrans = "'" . $lst . "'";
	} else {
		$notrans .= ",'" . $lst . "'";
	}
}
$no = 0;
foreach ($listTransaksi2 as $dtr => $lst) {
	$no++;
	if ($no == 1) {
		$notrans2 = "'" . $lst . "'";
	} else {
		$notrans2 .= ",'" . $lst . "'";
	}
}
if ($method == 'unposting') {
	if (count($_POST) > 0) {
		$param = $_POST;
	} else {
		$param = $_GET;
	}

	$no = 0;
	foreach ($param['notransaksi'] as $notran) {
		$no++;
		if ($no == 1) {
			$notranx = $notran;
		} else {
			$notranx .= "#" . $notran;
		}
	}

	$pil = array(
		"1" => $_SESSION['lang']['kasbank'] . " Menghapus nomor voucher (Keuangan)",
		"2" => 'Kasir  Menghapus nomor voucher (Keuangan)',
		"3" => "BAPP (Kontrak SPK)",
		"4" => "BKM Rawat / " . $_SESSION['lang']['panen'] . " (Kebun)",
		"5" => $_SESSION['lang']['traksi'] . "",
		"6" => "Tagihan / Invoice Pembelian (Keuangan)",
		"10" => "Penagihan / Invoice Penjualan (Keuangan)",
		"14" => "Project (Traksi/Keuangan)",
		"15" => "Pembayaran TBS (Sales)",
		"17" => "Fee TBS (Sales)",
		"18" => $_SESSION['lang']['kasbank'] . " Tidak menghapus nomor voucher (Keuangan)",
		"19" => "Kasir Tidak menghapus nomor voucher (Keuangan)",
		"20" => "Ganti Dokumen Finance (Keuangan)",
		"21" => "BA Mutasi Stok (Pabrik/Bulking)",
		"22" => "BA Transport (Bulking/Sales)",
		"23" => "BA Pengiriman (Sales)",
		"24" => "BAST Kontrak Penjualan (Sales)",
		// "25" => "Penerimaan Non Inventory (Procurment/Gudang)",
		"26" => "Harga Beli TBS (Sales)",
		"27" => "Harga Jual TBS (Sales)",
		"28" => "Transfer Produk (Pabrik/Bulking)",
		"29" => $_SESSION['lang']['jurnalmemo'] . " (Keuangan)",
		"30" => "BAPP (Per Termin)",
		"31" => "Upload Absensi HO (SDM)",
		"32" => "Surat Peringatan (SDM)",
		"33" => "Pengajuan Service (Traksi)",
		"34" => "Service (Traksi)",
		"35" => "Pekerjaan (Traksi)",
		"36" => "BA Beli TBS (Sales)",
		"37" => "BKM Sipil (Traksi)"
	);

	$data = array(
		'jenis'      => $pil[$param['pilUn_1']],
		'notransaksi' => $notranx,
		'tanggal'    => date("Y-m-d H:i:s"),
		'update'     => $_SESSION['standard']['userid']
	);
	$str = insertQuery($dbname, 'log_unposting', $data, array_keys($data));
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "<br/>";
		die();
	}
}


switch ($method) {
	case 'blokganti':
		$bloklama = $_POST['bloklama'];
		$blokbaru =  strtoupper($_POST['blokbaru']);

		if (substr($bloklama, 0, 4) != substr($blokbaru, 0, 4)) {
			exit("Error: Tidak boleh ganti kebun");
		}
		$udahada = "";
		$str = $owlPDO->query("select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $blokbaru . "' ");
		$str->setFetchMode(PDO::FETCH_OBJ);
		$optKebun = '';
		while ($bar = $str->fetch()) {
			$udahada = "Blok " . $bar->kodeorganisasi . " (" . $bar->namaorganisasi . ") sudah ada.";
		}

		if ($udahada == "") {
			$apdet = "UPDATE " . $dbname . ".`organisasi` SET `induk` = '" . substr($blokbaru, 0, 6) . "' WHERE `kodeorganisasi` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`organisasi` SET `namaorganisasi` = '" . $blokbaru . "' WHERE `kodeorganisasi` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`organisasi` SET `kodeorganisasi` = '" . $blokbaru . "' WHERE `kodeorganisasi` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`setup_blok` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			// 20140506, dz
			$apdet = "UPDATE " . $dbname . ".`setup_blok_tahunan` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}

			// 20140507, dz
			$apdet = "UPDATE " . $dbname . ".`bgt_blok` SET `kodeblok` = '" . $blokbaru . "' WHERE `kodeblok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`bgt_budget` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}

			$apdet = "UPDATE " . $dbname . ".`bibitan_mutasi` SET `afdeling` = '" . $blokbaru . "' WHERE `afdeling` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_5bjr` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			// $apdet="UPDATE ".$dbname.".`kebun_crossblock_ht` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
			// try{
			// $owlPDO->exec($apdet);          
			// }
			// catch (PDOException $e) {
			// print " Gagal  !: " . $e->getMessage() . "<br/>";
			// die();
			// }
			$apdet = "UPDATE " . $dbname . ".`kebun_pakaimaterial` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			//$apdet="UPDATE ".$dbname.".`kebun_peta` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
			//               try{
			//              $owlPDO->exec($apdet);          
			//                }
			//                catch (PDOException $e) {
			//                       print " Gagal  !: " . $e->getMessage() . "<br/>";
			//                       die();
			//                }
			$apdet = "UPDATE " . $dbname . ".`kebun_prestasi` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			//            $apdet="UPDATE ".$dbname.".`kebun_qc_ancakht` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
			//                            try{
			//             $owlPDO->exec($apdet);          
			//               }
			//               catch (PDOException $e) {
			//                      print " Gagal  !: " . $e->getMessage() . "<br/>";
			//                      die();
			//             }
			//            $apdet="UPDATE ".$dbname.".`kebun_qc_kondisitbdt` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
			//                try{
			//              $owlPDO->exec($apdet);          
			//                }
			//               catch (PDOException $e) {
			//                      print " Gagal  !: " . $e->getMessage() . "<br/>";
			//                      die();
			//                }
			//            $apdet="UPDATE ".$dbname.".`kebun_qc_kondisitbmdt` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
			//               try{
			//              $owlPDO->exec($apdet);          
			//               }
			//                catch (PDOException $e) {
			//                       print " Gagal  !: " . $e->getMessage() . "<br/>";
			//                       die();
			//                }
			//            $apdet="UPDATE ".$dbname.".`kebun_qc_panenht` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
			//                try{
			//              $owlPDO->exec($apdet);          
			//                }
			//                catch (PDOException $e) {
			//                       print " Gagal  !: " . $e->getMessage() . "<br/>";
			//                       die();
			//                }
			$apdet = "UPDATE " . $dbname . ".`kebun_rekomendasipupuk` SET `blok` = '" . $blokbaru . "' WHERE `blok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_rencanapanen` SET `kodeblok` = '" . $blokbaru . "' WHERE `kodeblok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_restan` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_sisip` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_spbdt` SET `blok` = '" . $blokbaru . "' WHERE `blok` = '" . $bloklama . "'"; // ga keupdate?
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			// 20140506, dz
			$apdet = "UPDATE " . $dbname . ".`kebun_taksasi` SET `blok` = '" . $blokbaru . "' WHERE `blok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`keu_jurnaldt` SET `kodeblok` = '" . $blokbaru . "' WHERE `kodeblok` = '" . $bloklama . "'"; // ga keupdate?
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`keu_kasbankdt` SET `orgalokasi` = '" . $blokbaru . "' WHERE `orgalokasi` = '" . $bloklama . "'"; // ga keupdate?
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`keu_penagihanht` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`log_baspk` SET `kodeblok` = '" . $blokbaru . "' WHERE `kodeblok` = '" . $bloklama . "'"; // ga keupdate?
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`log_baspk` SET `blokspkdt` = '" . $blokbaru . "' WHERE `blokspkdt` = '" . $bloklama . "'"; //  ga keupdate?
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`log_spkdt` SET `kodeblok` = '" . $blokbaru . "' WHERE `kodeblok` = '" . $bloklama . "'"; //  ga keupdate?
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`log_transaksidt` SET `kodeblok` = '" . $blokbaru . "' WHERE `kodeblok` = '" . $bloklama . "'"; //  ga keupdate?
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`qc_ht` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'"; //  ga keupdate?
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`vhc_rundt` SET `alokasibiaya` = '" . $blokbaru . "' WHERE `alokasibiaya` = '" . $bloklama . "'"; // ga keupdate?
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			//update 22 okt 2015
			$apdet = "UPDATE " . $dbname . ".`bgt_bjr` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`bgt_borong_panen` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`bgt_produksi_kebun` SET `kodeblok` = '" . $blokbaru . "' WHERE `kodeblok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_5kud` SET `kodeblok` = '" . $blokbaru . "' WHERE `kodeblok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_hpt_penanggulangan_ht` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_hpt_sensus_ht` SET `blok` = '" . $blokbaru . "' WHERE `blok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_pusingan` SET `blok` = '" . $blokbaru . "' WHERE `blok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_rekappnn` SET `blok` = '" . $blokbaru . "' WHERE `blok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_rencanasisip` SET `blok` = '" . $blokbaru . "' WHERE `blok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`kebun_rkb` SET `kodeorg` = '" . $blokbaru . "' WHERE `kodeorg` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`keu_pdodt` SET `kodeblok` = '" . $blokbaru . "' WHERE `kodeblok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`log_baspk_rev` SET `kodeblok` = '" . $blokbaru . "' WHERE `kodeblok` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
			$apdet = "UPDATE " . $dbname . ".`vhc_spl_prestasi` SET `alokasi` = '" . $blokbaru . "' WHERE `alokasi` = '" . $bloklama . "'";
			try {
				$owlPDO->exec($apdet);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
		} else {
			echo "error: " . $udahada;
		}
		break;

	case 'getDataPenjualan':
		if ($nokontrak == '') {
			exit("Warning : No. Kontrak harus diisi.");
		}
		$vnodo = "";
		if ($nodo != '') {
			$vnodo = $nodo;
		}

		$notransaksi = tanggalsystem($tanggalpenjualan) . "##" . $nokontrak . "##" . $vnodo;

		$str = "select * from " . $dbname . ".keu_pengakuanjual where notransaksi like '" . $notransaksi . "%' and posting='1'";
		$res = fetchData($str);

		if (count($res) <= 0) {
			exit("Warning : Data tidak ditemukan.");
		} else {
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable>
				<thead>
				<tr>
					<td>No Kontrak</td>
					<td>No. DO</td>
					<td>Unit</td>
					<td style='display:none'>No. PO</td>
					<td>" . $_SESSION['lang']['tanggal'] . "</td>
					<td><input type=checkbox id=allCheck onclick=checkAll() /></td>
				</tr>
				</thead>
				<tbody id=dataIsi>";

			$nor = 0;
			foreach ($res as $key => $val) {
				$expnodo = explode("##", $val['notransaksi']);
				$nor++;
				$tab .= "<tr class=rowcontent>
					<td id=notransaks_" . $nor . " style='display:none'>" . $val['notransaksi'] . "</td>
					<td>" . $nokontrak . "</td>
					<td>" . $expnodo[2] . "</td>
					<td>" . $val['millcode'] . "</td>
					<td id=tgl_" . $nor . ">" . tanggalnormal($val['tanggalpengakuan']) . "</td>
					<td><input type=checkbox id=act_" . $nor . " /></td>
				</tr>";
			}

			$tab .= "<tr>
				<td colspan=4 align=center>
					<button class=mybutton onclick=unpostingPenjualan()>unposting</button>
					<button class=mybutton onclick=unlockForm()>batal</button>
				</td>
			</tr>
			</tbody>
			</table>";
			// $qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			// if($qKasBank)
			// {
			// while($rData=$qKasBank->fetch())
			// {
			// $expnotran = explode('##',$rData['notransaksi']);
			// if($expnotran[1]==''){
			// $mynotrans = $rData['notransaksi'];
			// $str="select * from ".$dbname.".pabrik_timbangan where notransaksi='".$mynotrans."'";
			// $res=fetchData($str);
			// $nodo = $res[0]['nosipb'];
			// }else{
			// $mynotrans = $expnotran[1];
			// if($expnotran[2]==''){
			// $nodo = '';
			// }else{
			// $nodo = $expnotran[2];
			// }
			// }
			// if($expnotran[2])
			// $nor++;
			// $tab.="<tr class=rowcontent>";
			// $tab.="<td id=notransaks_".$nor." style='display:none'>".$rData['notransaksi']."</td>";
			// $tab.="<td>".$mynotrans."</td>";
			// $tab.="<td>".$nodo."</td>";
			// $tab.="<td id=blokspkdt_".$nor.">".$rData['millcode']."</td>";
			// $tab.="<td id=kodekegiatan_".$nor." style='display:none'>".$rData['kodeho']."</td>";            
			// $tab.="<td id=tgl_".$nor.">".tanggalnormal($rData['tanggal'])."</td>";
			// $tab.="<td><input type=checkbox id=act_".$nor." /></td>";
			// $tab.="</tr>";
			// }
			// }
		}
		echo $tab;
		break;

	case 'getData':

		switch ($pilUn_1) {
			case '33': ##TRAKSI PENGAJUAN SERVICE##
				if ($_POST['listTransaksi'] != '') {
					$whr = "nopengajuan in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= "and kodeorg='" . $unitId . "'";
				}
				if ($periodeId != '') {
					$whr .= "and tanggalpengajuan like '%" . $periodeId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct nopengajuan,tanggalpengajuan,kodevhc,kodeorg from " . $dbname . ".vhc_pengajuanservice where " . $whr . " and statuspersetujuan = '1'");
				break;
			case '34': ##TRAKSI SERVICE##
				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= "and kodeorg='" . $unitId . "'";
				}
				if ($periodeId != '') {
					$whr .= "and tanggal like '%" . $periodeId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct notransaksi,tanggal,kodevhc,kodeorg from " . $dbname . ".vhc_penggantianht where " . $whr . " and posting = '1'");
				break;

			case '35': ##TRAKSI PEKERJAAN##
				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= "and kodeorg='" . $unitId . "'";
				}
				if ($periodeId != '') {
					$whr .= "and tanggal like '%" . $periodeId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct notransaksi,tanggal,kodevhc,kodeorg from " . $dbname . ".vhc_runht where " . $whr . " and posting = '1'");
				break;

			case '36': ## BA BELI TBS
				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				// if ($unitId != '') {
				// 	$whr .= "and kodeorg='" . $unitId . "'";
				// }
				// if ($periodeId != '') {
				// 	$whr .= "and tanggal like '%" . $periodeId . "%'";
				// }
				$qKasBank = $owlPDO->query("select distinct notransaksi as notransaksi,tanggal,tanggaltbs1,tanggaltbs2,unit,supplier from " . $dbname . ".pmn_tbs where " . $whr . " and posting = '1'");
				break;

			case '37': ## BKM SIPIL
				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				// if ($unitId != '') {
				// 	$whr .= "and kodeorg='" . $unitId . "'";
				// }
				// if ($periodeId != '') {
				// 	$whr .= "and tanggal like '%" . $periodeId . "%'";
				// }
				$qKasBank = $owlPDO->query("select distinct notransaksi,tanggal,kodeorg from " . $dbname . ".vhc_spl_aktifitas where " . $whr . " and jurnal = '1'");
				break;

			case '1':
			case '2':
			case '18':
			case '19':
				if ($_POST['listTransaksi'] != '') {
					$whr = " noreferensi in (" . $notrans . ") ";
					$whrx = " notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				// exit("Error:".$whr._.$whrx);


				$str = "select distinct a.notransaksi as notransaksi,a.tanggal,b.nojurnal from " . $dbname . ".keu_kasbankht a 
			left join " . $dbname . ".keu_jurnalht b on a.notransaksi=b.noreferensi where " . $whrx . " and a.posting!='0'";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());

				break;

			case '20':
				if ($_POST['listTransaksi'] == '') {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}

				$str = "SELECT DISTINCT notransaksi, tanggal FROM " . $dbname . ".keu_gantidokumen
                    WHERE notransaksi=" . $notrans . " AND posting NOT IN ('0','3')";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());

				break;

			case '4':
				if ($_POST['listTransaksi'] != '') {
					$whr = " noreferensi in (" . $notrans . ") ";
					$whrx = " notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= "and nojurnal like '%" . $unitId . "%'";
					$whrx .= "and kodeorg like '%" . $unitId . "%'";
				}
				if ($periodeId != '') {
					$whr .= "and tanggal like '%" . $periodeId . "%'";
					$whrx .= "and tanggal like '%" . $periodeId . "%'";
				}

				// $kdtrans=explode("/",$notrans);

				// if($kdtrans[2]=='PNN'){

				// $qKasBank=$owlPDO->query("select distinct notransaksi as notransaksi,tanggal from ".$dbname.".kebun_aktifitas  where ".$whrx." ");
				// }else{
				// $qKasBank=$owlPDO->query("select distinct noreferensi as notransaksi,nojurnal,tanggal from ".$dbname.".keu_jurnalht  where ".$whr." ");
				// }

				$str = "select distinct a.notransaksi as notransaksi,a.tanggal,b.nojurnal from " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".keu_jurnalht b on a.notransaksi=b.noreferensi where " . $whrx . ""; #exit("error".$str);
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());


				break;
			case '5':
				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= "and kodeorg='" . $unitId . "'";
				}
				if ($periodeId != '') {
					$whr .= "and tanggal like '%" . $periodeId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct notransaksi,tanggal from " . $dbname . ".vhc_runht where " . $whr . " and posting=1");
				break;
			case '3': #BAPP
				$str = "select notransaksi from " . $dbname . ".log_baspk where keterangan=" . $notrans . " limit 1";
				$res = fetchdata($str);

				$strcek = "select noinvoice,tanggal,nilaiinvoice from " . $dbname . ".keu_tagihanht where nopo='" . $res[0]['notransaksi'] . "' ";
				$rescek = fetchdata($strcek);

				if ($_POST['listTransaksi'] != '') {
					$whr = "keterangan = " . $notrans . " ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and blokspkdt like '" . $unitId . "%'";
				}
				$qKasBank = "select distinct keterangan,notransaksi,tanggal,sum(jumlahrealisasi) as nilai from " . $dbname . ".log_baspk  where " . $whr . " and statusjurnal=1";
				break;
			case '6': ##TAGIHAN##
				if ($_POST['listTransaksi'] != '') {
					$whr = "noinvoice in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['noinvoice'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and unit like '" . $unitId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct noinvoice,tipeinvoice,tanggal,nopo from " . $dbname . ".keu_tagihanht  where " . $whr . " and posting=1");
				break;
			case '7': ##Penerimaan TBS PABRIK##
				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and kodeho like '" . $unitId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct notransaksi,kodeho,tanggal from " . $dbname . ".keu_persediaantbs_ht  where " . $whr . " and jurnal=1");
				break;
			case '8': ##PENERIMAAN TBS RAMP##
				if ($_POST['listTransaksi'] != '') {
					$whr = "notiket in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and unit like '" . $unitId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct notiket,unit,left(datein,10) as tanggal from " . $dbname . ".pmn_penerimaantbsramp  where " . $whr . " and posted=1");
				break;
			case '10': ##PENAGIHAN##
				if ($_POST['listTransaksi'] != '') {
					$whr = "noinvoice in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}

				if ($unitId != '') {
					$whr .= " and kodeorg like '" . $unitId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct noinvoice,kodeorg,tanggal as tanggal from " . $dbname . ".keu_penagihanht  where " . $whr . " and posting=1");
				break;
			case '11': ##KAS KECIL TOP UP##

				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and unit like '" . $unitId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct notransaksi,unit,tanggal as tanggal from " . $dbname . ".keu_kaskecilht  where " . $whr . " and posting=1");
				break;
			case '12': ##NOTA DEBET##
				if ($_POST['listTransaksi'] != '') {
					$whr = "notadebet in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and unit like '" . $unitId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct notadebet as notransaksi,tanggal,unit from " . $dbname . ".keu_notadebet_ht  where " . $whr . " and posting=1");
				break;
			case '13': ##KAS KECIL PENGELUARAN##

				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}

				if ($unitId != '') {
					$whr .= " and unit like '" . $unitId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct notransaksi,unit,tanggal as tanggal from " . $dbname . ".keu_kaskecilht  where " . $whr . " and posting=1");
				break;
			case '14': ##PROJECT##
				if ($_POST['listTransaksi'] != '') {
					$whr = "kodeasset in (" . $notrans . ") and nojurnal like '%PRJ%'";
				} else {
					exit('warning:Kode Project ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and unit like '" . $unitId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct noreferensi as notransaksi,tanggal,kodeorg as unit,nojurnal from " . $dbname . ".keu_jurnaldt_vw  where " . $whr . " and noakun like '129%'");


				break;

			case '15': ##PEMBAYARAN TBS##
				if ($_POST['listTransaksi'] != '') {
					$whr = "noreferensi in (" . $notrans . ") and nojurnal like '%INVTB%'";
				} else {
					exit('warning: No. Transaksi Pembayaran ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and kodeorg like '" . $unitId . "%'";
				}
				$qKasBank = $owlPDO->query("select distinct noreferensi as notransaksi,tanggal,kodeorg as unit,kodesupplier from " . $dbname . ".keu_jurnaldt_vw  where " . $whr . "");
				break;

			case '16': ##UNPOST PERGUDANGAN##
				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ")";
				} else {
					exit('warning: No. Transaksi ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and kodegudang like '" . $unitId . "%'";
				}
				$str = "select * from " . $dbname . ".log_transaksiht  where " . $whr . "";
				break;

			case '17': ##UNPOST FEETBS##
				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ")";
				} else {
					exit('warning: No. Transaksi ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and unit like '" . $unitId . "%'";
				}
				$str = "select * from " . $dbname . ".pmn_feetbs  where " . $whr . "";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				break;

			#= pabrik_bamutasi
			#= jurnal
			case '21':
				if ($_POST['listTransaksi'] != '') {
					$whr = " and notransaksi in (" . $notrans . ")";
				} else {
					exit('warning: No. Transaksi ' . $_SESSION['lang']['kosong']);
				}
				$str = "select * from " . $dbname . ".pabrik_bamutasi  where 1=1 " . $whr . " AND posting='1'";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());

				break;

			#= pmn_batransport
			#= jurnal
			case '22':
				if ($_POST['listTransaksi'] != '') {
					$whr = " and notransaksi in (" . $notrans . ")";
				} else {
					exit('warning: No. Transaksi ' . $_SESSION['lang']['kosong']);
				}
				$str = "select * from " . $dbname . ".pmn_batransport  where 1=1 " . $whr . " AND posting='1'";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());

				break;

			#= pmn_bapengiriman
			case '23':
				// exit("Error:$str");
				if ($_POST['listTransaksi'] != '') {
					$whr = " and notransaksi in (" . $notrans . ")";
				} else {
					exit('warning: No. Transaksi ' . $_SESSION['lang']['kosong']);
				}
				$str = "select * from " . $dbname . ".pmn_bapengiriman  where 1=1 " . $whr . " AND posting='1'";
				// exit("Error:$str");
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				break;


			#= pmn_bast
			#= jurnal
			case '24':
				if ($_POST['listTransaksi'] != '') {
					$whr = " and notransaksi in (" . $notrans . ")";
				} else {
					exit('warning: No. Transaksi ' . $_SESSION['lang']['kosong']);
				}
				$str = "select * from " . $dbname . ".pmn_bast  where 1=1 " . $whr . " AND posting='1'";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());

				break;


			#= noninventory
			#= jurnal
			case '25':
				if ($_POST['listTransaksi'] != '') {
					$whr = " and notransaksi in (" . $notrans . ")";
				} else {
					exit('warning: No. Transaksi ' . $_SESSION['lang']['kosong']);
				}
				$str = "select * from " . $dbname . ".log_noninventory  where 1=1 " . $whr . " AND posting='1'";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				break;


			#= tbs beli
			case '26':
				if ($_POST['listTransaksi'] != '') {
					$whr = " and notransaksi in (" . $notrans . ")";
				} else {
					exit('warning: No. Transaksi ' . $_SESSION['lang']['kosong']);
				}
				$str = "select * from " . $dbname . ".pmn_hargabelitbs  where 1=1 " . $whr . " AND posting='1'";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				break;

			#= tbs jual
			case '27':
				if ($_POST['listTransaksi'] != '') {
					$whr = " and notransaksi in (" . $notrans . ")";
				} else {
					exit('warning: No. Transaksi ' . $_SESSION['lang']['kosong']);
				}
				$str = "select * from " . $dbname . ".pmn_hargajualtbs  where 1=1 " . $whr . " AND posting='1'";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				break;

			// this is
			case '28':

				if ($_POST['listTransaksi'] != '') {
					$whr = " and notransaksi in (" . $notrans . ")";
				} else {
					exit('warning: No. Transaksi ' . $_SESSION['lang']['kosong']);
				}
				$str = "select * from " . $dbname . ".pabrik_transferproduk  where 1=1 " . $whr . " AND posting='1'";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				break;

			case '29':
				if ($_POST['listTransaksi'] != '') {
					$whr = " and nojurnal in (" . $notrans . ")";
				} else {
					exit('warning: No. Jurnal ' . $_SESSION['lang']['kosong']);
				}
				$str = "select nojurnal as notransaksi,nojurnal,tanggal from " . $dbname . ".keu_jurnalmemorial  where 1=1 " . $whr . " AND posting='1'";
				$qKasBank = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				break;
			case '30': #BAPP Per Termin
				$str = "select notransaksi from " . $dbname . ".log_baspk where keterangan=" . $notrans . " limit 1";
				$res = fetchdata($str);

				$strcek = "select noinvoice,tanggal,nilaiinvoice from " . $dbname . ".keu_tagihanht where nopo='" . $res[0]['notransaksi'] . "' and noinvoice in (select noinvoice from " . $dbname . ".keu_tagihandt where notransaksi=" . $notrans . ") ";
				$rescek = fetchdata($strcek);

				if ($_POST['listTransaksi'] != '') {
					$whr = "keterangan = " . $notrans . " ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}
				if ($unitId != '') {
					$whr .= " and blokspkdt like '" . $unitId . "%'";
				}
				$qKasBank = "select distinct keterangan,notransaksi,tanggal,sum(jumlahrealisasi) as nilai from " . $dbname . ".log_baspk  where " . $whr . " and statusjurnal=1";
				break;

			case '31': #
				if ($_POST['listTransaksi'] != '') {
					$whr = "notransaksi in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}

				if ($unitId != '') {
					$whr .= " and kodeorg like '" . $unitId . "%'";
				}
				$qKasBank = $owlPDO->query("select * from " . $dbname . ".sdm_uploadabsensihoht  where " . $whr . " and posting=1");
				break;

			case '32': #
				if ($_POST['listTransaksi'] != '') {
					$whr = "nomor in (" . $notrans . ") ";
				} else {
					exit('warning:' . $_SESSION['lang']['notransaksi'] . ' ' . $_SESSION['lang']['kosong']);
				}

				if ($unitId != '') {
					$whr .= " and kodeorg like '" . $unitId . "%'";
				}

				$qKasBank = $owlPDO->query("select * from " . $dbname . ".sdm_suratperingatan  where " . $whr . " and posting=1");
				break;
		}
		##LIST DATA##
		if ($pilUn_1 == 3) {
			// echo"<pre>";
			// print_r($rescek);

			// exit("error");
			##SPK##
			if (count($rescek) > 0) {
				$tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable>
				<thead>
				<tr style=align:center;>
				<td>No Tagihan</td>
				<td>Tanggal</td>
				<td>Nilai</td>
				</tr>
				</thead>
				";

				$err = "";
				foreach ($rescek as $val) {
					$tab .= "
					<tr class=rowcontent>
					<td>" . $val['noinvoice'] . "</td>
					<td>" . tanggalnormal($val['tanggal']) . "</td>
					<td>" . number_format($val['nilaiinvoice']) . "</td>
					</tr>
					";
				}

				$tab .= "<div style=color:red;><b>No BAPP tersebut sudah memiliki transaksi tagihan, Silahkan hapus No transaksi tagihan sesuai dengan BAPP yg di unposting!</b></div>";
				$err = "x";
			}

			if ($err == '') {
				$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
				$tab .= "<tr style=text-align:center><th>No SPK</th>";
				$tab .= "<th>No BAPP</th>";
				$tab .= "<th>" . $_SESSION['lang']['tanggal'] . "</th>";
				$tab .= "<th>Nilai</th>";
				$tab .= "<th><input type=checkbox id=allCheck onclick=checkAll() /></th></tr></thead><tbody id=dataIsi>";
				$rKasBank = fetchdata($qKasBank);
				if (count($rKasBank) > 0) {
					$nor = 0;
					foreach ($rKasBank as $value) {
						$nor++;
						$tab .= "<tr class=rowcontent>";
						$tab .= "<td id=notransaks_" . $nor . ">" . $value['notransaksi'] . "</td>";
						$tab .= "<td id=nobapp_" . $nor . ">" . $value['keterangan'] . "</td>";
						$tab .= "<td id=tgl_" . $nor . ">" . $value['tanggal'] . "</td>";
						$tab .= "<td id=nilai_" . $nor . ">" . number_format($value['nilai']) . "</td>";
						$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
						$tab .= "</tr>";
					}
				} else {
					exit('Warning, data tidak ditemukan!');
				}
			} else {
			}
		} else if ($pilUn_1 == 30) {
			// echo"<pre>";
			// print_r($rescek);

			// exit("error");
			##BAPP PER TERMIN##
			if (count($rescek) > 0) {
				$tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable>
				<thead>
				<tr style=align:center;>
				<td>No Tagihan</td>
				<td>Tanggal</td>
				<td>Nilai</td>
				</tr>
				</thead>
				";

				$err = "";
				foreach ($rescek as $val) {
					$tab .= "
					<tr class=rowcontent>
					<td>" . $val['noinvoice'] . "</td>
					<td>" . tanggalnormal($val['tanggal']) . "</td>
					<td>" . number_format($val['nilaiinvoice']) . "</td>
					</tr>
					";
				}
				$tab .= "<button class=mybutton onclick=unlockForm()>" . $_SESSION['lang']['cancel'] . "</button>";
				$tab .= "<div style=color:red;><b>No BAPP tersebut sudah memiliki transaksi tagihan, Silahkan hapus No transaksi tagihan sesuai dengan BAPP yg di unposting!</b></div>";
				$err = "x";
			}

			if ($err == '') {
				$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
				$tab .= "<tr style=text-align:center><th>No SPK</th>";
				$tab .= "<th>No BAPP</th>";
				$tab .= "<th>" . $_SESSION['lang']['tanggal'] . "</th>";
				$tab .= "<th>Nilai</th>";
				$tab .= "<th><input type=checkbox id=allCheck onclick=checkAll() /></th></tr></thead><tbody id=dataIsi>";
				$rKasBank = fetchdata($qKasBank);
				if (count($rKasBank) > 0) {
					$nor = 0;
					foreach ($rKasBank as $value) {
						$nor++;
						$tab .= "<tr class=rowcontent>";
						$tab .= "<td id=notransaks_" . $nor . ">" . $value['notransaksi'] . "</td>";
						$tab .= "<td id=nobapp_" . $nor . ">" . $value['keterangan'] . "</td>";
						$tab .= "<td id=tgl_" . $nor . ">" . $value['tanggal'] . "</td>";
						$tab .= "<td id=nilai_" . $nor . ">" . number_format($value['nilai']) . "</td>";
						$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
						$tab .= "</tr>";
					}
				} else {
					exit('Warning, data tidak ditemukan!');
				}
			} else {
			}
		} else if ($pilUn_1 == 6) {
			##TAGIHAN##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>" . $_SESSION['lang']['notransaksi'] . "</td>";
			$tab .= "<td>Jenis Tagihan</td>";
			$tab .= "<td>No. PO</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$optjnstgh = makeOption($dbname, "keu_5jenistagihan", 'kode,namajenis', "lower(kode)='" . strtolower($rData['tipeinvoice']) . "'");
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['noinvoice'] . "</td>";
					$tab .= "<td id=blokspkdt_" . $nor . ">" . $optjnstgh[strtolower($rData['tipeinvoice'])] . "</td>";
					$tab .= "<td id=kodekegiatan_" . $nor . ">" . $rData['nopo'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 7) {
			##PENERIMAAN TBS PABRIK##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>" . $_SESSION['lang']['notransaksi'] . "</td>";
			$tab .= "<td>Kode HO</td>";
			$tab .= "<td style='display:none'>No. PO</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=blokspkdt_" . $nor . ">" . $rData['kodeho'] . "</td>";
					$tab .= "<td id=kodekegiatan_" . $nor . " style='display:none'>" . $rData['kodeho'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 8) {
			##PENERIMAAN TBS PABRIK##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>No Tiket</td>";
			$tab .= "<td>Unit</td>";
			$tab .= "<td style='display:none'>No. PO</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notiket'] . "</td>";
					$tab .= "<td id=blokspkdt_" . $nor . ">" . $rData['unit'] . "</td>";
					$tab .= "<td id=kodekegiatan_" . $nor . " style='display:none'>" . $rData['kodeho'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 9) {
			##PENGAKUAN PENJUALAN##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>No Tiket</td>";
			$tab .= "<td>Unit</td>";
			$tab .= "<td style='display:none'>No. PO</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=blokspkdt_" . $nor . ">" . $rData['millcode'] . "</td>";
					$tab .= "<td id=kodekegiatan_" . $nor . " style='display:none'>" . $rData['kodeho'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 10) {
			##PENAGIHAN##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>No Invoice</td>";
			$tab .= "<td>Unit</td>";
			$tab .= "<td style='display:none'>No. PO</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['noinvoice'] . "</td>";
					$tab .= "<td id=blokspkdt_" . $nor . ">" . $rData['kodeorg'] . "</td>";
					$tab .= "<td id=kodekegiatan_" . $nor . " style='display:none'>" . $rData['kodeho'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 31) {
			##PENAGIHAN##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>No Transaksi</td>";
			$tab .= "<td>Unit</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=blokspkdt_" . $nor . ">" . $rData['kodeorg'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 33) {
			##TRAKSI PENGAJUAN SERVICE##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td align=center>" . $_SESSION['lang']['nopengajuan'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td align=center><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$optnmkend = makeOption($dbname, "vhc_5master", 'kodevhc,detailvhc', "kodevhc='" . $rData['kodevhc'] . "'");
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['nopengajuan'] . "</td>";
					$tab .= "<td id=kodeorg_" . $nor . ">" . $rData['kodeorg'] . " - " . getNamaOrg($rData['kodeorg']) . "</td>";
					$tab .= "<td id=kodevhc_" . $nor . ">" . $optnmkend[$rData['kodevhc']] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggalpengajuan']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 34) {
			##TRAKSI SERVICE##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td align=center><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$optnmkend = makeOption($dbname, "vhc_5master", 'kodevhc,detailvhc', "kodevhc='" . $rData['kodevhc'] . "'");
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=kodeorg_" . $nor . ">" . $rData['kodeorg'] . " - " . getNamaOrg($rData['kodeorg']) . "</td>";
					$tab .= "<td id=kodevhc_" . $nor . ">" . $optnmkend[$rData['kodevhc']] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 32) {
			##PENAGIHAN##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>No Transaksi</td>";
			$tab .= "<td>Unit</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['nomor'] . "</td>";
					$tab .= "<td id=blokspkdt_" . $nor . ">" . $rData['kodeorg'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 11) {
			##KAS KECIL##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>" . $_SESSION['lang']['notransaksi'] . "</td>";
			$tab .= "<td>Unit</td>";
			$tab .= "<td style='display:none'>No. PO</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=blokspkdt_" . $nor . ">" . $rData['unit'] . "</td>";
					$tab .= "<td id=kodekegiatan_" . $nor . " style='display:none'>" . $rData['kodeho'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 12) {
			##KAS KECIL##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>" . $_SESSION['lang']['notransaksi'] . "</td>";
			$tab .= "<td>Unit</td>";
			$tab .= "<td style='display:none'>No. PO</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=blokspkdt_" . $nor . ">" . $rData['unit'] . "</td>";
					$tab .= "<td id=kodekegiatan_" . $nor . " style='display:none'>" . $rData['kodeho'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 13) {
			##KAS KECIL##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>" . $_SESSION['lang']['notransaksi'] . "</td>";
			$tab .= "<td>Unit</td>";
			$tab .= "<td style='display:none'>No. PO</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent style='text-align:center'>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=blokspkdt_" . $nor . ">" . $rData['unit'] . "</td>";
					$tab .= "<td id=kodekegiatan_" . $nor . " style='display:none'>" . $rData['kodeho'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 15) {
			##PEMBAYARAN TBS##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr style='text-align:center'><td>No. Transaksi</td>";
			$tab .= "<td>Unit</td>";
			$tab .= "<td>" . $_SESSION['lang']['supplier'] . "</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$optsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $rData['kodesupplier'] . "'");
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=nojurnal_" . $nor . ">" . $rData['unit'] . "</td>";
					$tab .= "<td id=unitdt_" . $nor . ">" . $optsup[$rData['kodesupplier']] . "</td>";
					$tab .= "<td id=tgl_" . $nor . " style='min-width:70px;text-align:center'>" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 16) {
			##UNPOST GUDANG##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr style='text-align:center'><td>No. Transaksi</td>";
			$tab .= "<td>" . $_SESSION['lang']['tipetransaksi'] . "</td>";
			$tab .= "<td>" . $_SESSION['lang']['gudang'] . "</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$res = fetchdata($str);
			if (count($res) > 0) {
				foreach ($res as $bar) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $bar['notransaksi'] . "</td>";
					$tab .= "<td id=nojurnal_" . $nor . ">" . getDetailTipeMutasi($bar['tipetransaksi']) . "</td>";
					$tab .= "<td id=gudang_" . $nor . ">" . $bar['kodegudang'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . " style='min-width:70px;text-align:center'>" . tanggalnormal($bar['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 35) {
			##TRAKSI PEKERJAAN##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['unit'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td align=center><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$optnmkend = makeOption($dbname, "vhc_5master", 'kodevhc,detailvhc', "kodevhc='" . $rData['kodevhc'] . "'");
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=unit_" . $nor . ">" . $rData['kodeorg'] . " - " . getNamaOrg($rData['kodeorg']) . "</td>";
					$tab .= "<td id=kodevhc_" . $nor . ">" . $optnmkend[$rData['kodevhc']] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		}else if ($pilUn_1 == 36) {
			##BA BELI TBS##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['unit'] . "</td>";
 			$tab .= "<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
 			$tab .= "<td align=center>" . $_SESSION['lang']['supplier'] . "</td>";
 			$tab .= "<td align=center>" . $_SESSION['lang']['tanggal'] . " TBS</td>";
 			$tab .= "<td align=center><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
 					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=unit_" . $nor . ">" . $rData['unit'] . " - " . getNamaOrg($rData['unit']) . "</td>";
 					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td id=supplier_" . $nor . ">" . $rData['supplier'] . "</td>";
 					$tab .= "<td id=tgltbs_" . $nor . ">" . tanggalnormal($rData['tanggaltbs1']) . " s/d " . tanggalnormal($rData['tanggaltbs2']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		}else if ($pilUn_1 == 37) {
			##BKM SIPIL##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>";
			$tab .= "<td align=center>" . $_SESSION['lang']['unit'] . "</td>";
 			$tab .= "<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
 			$tab .= "<td align=center><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
 					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=unit_" . $nor . ">" . $rData['kodeorg'] . " - " . getNamaOrg($rData['kodeorg']) . "</td>";
 					$tab .= "<td id=tgl_" . $nor . ">" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 17) {
			##PEMBAYARAN TBS##
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr style='text-align:center'><td>No. Transaksi</td>";
			$tab .= "<td>Unit</td>";
			$tab .= "<td>" . $_SESSION['lang']['supplier'] . "</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$optsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $rData['kodesupplier'] . "'");
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=nojurnal_" . $nor . ">" . $rData['unit'] . "</td>";
					$tab .= "<td id=unitdt_" . $nor . ">" . $optsup[$rData['kodesupplier']] . "</td>";
					$tab .= "<td id=tgl_" . $nor . " style='min-width:70px;text-align:center'>" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 20) {
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr style='text-align:center'><td>No. Transaksi</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . " style='min-width:70px;text-align:center'>" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else if ($pilUn_1 == 21 || $pilUn_1 == 22 || $pilUn_1 == 23 || $pilUn_1 == 24) {
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr style='text-align:center'><td>No. Transaksi</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . " style='min-width:70px;text-align:center'>" . tanggalnormal($rData['tanggal']) . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		} else {


			$tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab .= "<tr><td>" . $_SESSION['lang']['notransaksi'] . "</td>";
			$tab .= "<td>" . $_SESSION['lang']['nojurnal'] . "</td>";
			$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";

			$nor = 0;
			$qKasBank->setFetchMode(PDO::FETCH_ASSOC);
			if ($qKasBank) {
				while ($rData = $qKasBank->fetch()) {
					$nor++;

					$tab .= "<tr class=rowcontent>";
					$tab .= "<td id=notransaks_" . $nor . ">" . $rData['notransaksi'] . "</td>";
					$tab .= "<td id=nojurnal_" . $nor . ">" . $rData['nojurnal'] . "</td>";
					$tab .= "<td id=tgl_" . $nor . ">" . $rData['tanggal'] . "</td>";
					$tab .= "<td><input type=checkbox id=act_" . $nor . " /></td>";
					$tab .= "</tr>";
				}
			}
		}
		if ($err == '') {
			$tab .= "<tr><td colspan=4 align=center><button class=mybutton onclick=unPosting()>unposting</button><button class=mybutton onclick=unlockForm()>batal</button></td></tr>";
		}
		$tab .= "</tbody></table>";
		echo $tab;
		break;

	case 'unposting':

		// exit("Error:A");

		switch ($pilUn_1) {
			case '33':
				#= traksi pengajuan service
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';
					$stry = $owlPDO->query("select kodeorg,tanggalpengajuan from " . $dbname . ".vhc_pengajuanservice where nopengajuan='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					while ($bat = $stry->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->kodeorg . "' and periode='" . substr($bat->tanggalpengajuan, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
							$comment2 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " sudah closing\n";
						}
					}
					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n proces dibatalkan");
					}
					$sUp = "update " . $dbname . ".vhc_pengajuanservice set statuspersetujuan=0  where nopengajuan='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sUp);

						movetoappreturn($bsdlis);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;
			case '34':
				#= traksi service
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';
					$stry = $owlPDO->query("select kodeorg,tanggal from " . $dbname . ".vhc_penggantianht where notransaksi='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					while ($bat = $stry->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->kodeorg . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
							$comment2 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " sudah closing\n";
						}
					}
					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n proces dibatalkan");
					}
					$sUp = "update " . $dbname . ".vhc_penggantianht set posting=0  where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sUp);

						movetoappreturn($bsdlis);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '35':
				#= traksi pekerjaan
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';
					$stry = $owlPDO->query("select kodeorg,tanggal from " . $dbname . ".vhc_runht where notransaksi='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					while ($bat = $stry->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->kodeorg . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
							$comment2 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " sudah closing\n";
						}
					}
					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n proces dibatalkan");
					}
					$sUp = "update " . $dbname . ".vhc_runht set posting=0  where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sUp);

						# Delete vhc_rundt_detail
						$sdel="delete from ".$dbname.".vhc_rundt_detail where notransaksi='".$bsdlis."'";
						try
						{
							$owlPDO->exec($sdel);       
						}
						catch (PDOException $e) 
						{
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}

						movetoappreturn($bsdlis);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '36':
				try {
					$owlPDO->beginTransaction();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
						#= hapus jurnal
						$str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
						$owlPDO->exec($str);

						#= unflag
						$str = "update " . $dbname . ".pmn_tbs set posting='0', postingby='' where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);
					}

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Warning: Gagal melakukan unposting data \n" . addslashes($e->getMessage());
				}
				break;

			case '37':
				try {
					$owlPDO->beginTransaction();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
						$tgl = substr(tanggaldb($_POST['tanggal'][$dtList]), 0, 7);
						$unit = explode("/", $bsdlis);
						$scek = "select distinct tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $tgl . "' and kodeorg='" . $unit[1] . "'";
						$rcek = fetchdata($scek);
						if ($rcek[0]['tutupbuku'] == 1) {
							exit("Warningsystem : Periode akuntansi bulan ".$tgl." sudah di tutup");
						}else{
							#= hapus jurnal kalau ada
							$query = selectQuery($dbname,'keu_jurnalht','*',"noreferensi='".$bsdlis."'");
							$hasil = fetchData($query);
							if(count($hasil)>0){
								$str="delete from ".$dbname.".keu_jurnalht where noreferensi='".$bsdlis."'";
								$owlPDO->exec($str);
							}
	
							#= hapus table _detail kalau ada
							$qry = selectQuery($dbname,'vhc_spl_prestasi_detail','*',"notransaksi='".$bsdlis."'");
							$hsl = fetchData($qry);
							if(count($hsl)>0){
								$str="delete from ".$dbname.".vhc_spl_prestasi_detail where notransaksi='".$bsdlis."'";
								$owlPDO->exec($str);
							}
							
							# Update flag transaksi
							$str="update ".$dbname.".vhc_spl_aktifitas set jurnal ='0' where notransaksi='".$bsdlis."'";
							$owlPDO->exec($str);
						}
					}

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Warning: Gagal melakukan unposting data \n" . addslashes($e->getMessage());
				}
				break;

			case '1': #= kasbank
				#=kasbank



				// foreach($_POST as $data){
				// exit("Error:".$data['notransaksi']);

				// }
				// echo"<pre>";
				// print_r($_POST);

				// echo"</pre>";
				// exit("Error:A");

				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					#periksa apakah unit RK lain sudah tutup buku
					$comment = '';
					// $strz="select nojurnal,tanggal from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."'";
					// exit("Error:$strz");
					$tempnotransaksi = $tempnojurnal = '';
					$str = $owlPDO->query("select nojurnal,tanggal from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "'");
					$str->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $str->fetch()) {
						$nojurnal = $bar->nojurnal;
						$unit = explode("/", $bar->nojurnal);
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $unit[1] . "' and periode='" . substr($bar->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							$comment .= "-" . $baru->kodeorg . " periode " . $baru->periode . " has been closed\n";
						}

						$strv = "select novoucher from " . $dbname . ".keu_kasbankht  where notransaksi='" . $bsdlis . "'"; #exit("error".$str);
						$resv = $owlPDO->query($strv) or die(print " Gagal: " . PDOException::getMessage());
						$resv->setFetchMode(PDO::FETCH_ASSOC);
						$barv = $resv->fetch();
						$novoucher = $barv['novoucher'];

						#= jika sukses maka insert ke table pembantu		
						#=cek jika sudah ada nojurnal dan notransaksi yang sama maka tidak perlu insert ketable log

						$strcek = "select count(*) as jumlah 
						from " . $dbname . ".tool_logunposting  where 
						notransaksi='" . $bsdlis . "' and nojurnal='" . $nojurnal . "'";
						$rescek = $owlPDO->query($strcek) or die(print " Gagal: " . PDOException::getMessage());
						$rescek->setFetchMode(PDO::FETCH_ASSOC);
						$barcek = $rescek->fetch();
						if ($barcek['jumlah'] < '1') {
							$strlog = "insert into " . $dbname . ".tool_logunposting
							(notransaksi, nojurnal, novoucher,unit,jenis,tanggal, user)
							values('" . $bsdlis . "','" . $nojurnal . "',
							'" . $novoucher . "','" . $unit[1] . "','KASBANK',
							'" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "');";
							try {
								$owlPDO->exec($strlog);
								$strlog = '';
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						}
					}

					if ($comment != '') {
						#delete table log 
						$sDel = "delete from " . $dbname . ".tool_logunposting where notransaksi='" . $bsdlis . "' and nojurnal='" . $nojurnal . "'";
						try {
							$owlPDO->exec($sDel);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
						// exit(" Error :".$comment."\n process canceled");
						exit(" Error :" . $comment . "\n proces dibatalkan");
					}




					#= proses unposting hapus jurnal dan unflag transaksi , approval
					$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "'";
					// saat unpost, tambahkan hapus novoucher. as requested by vienny via WA 2021-07-15
					$sUp = "update " . $dbname . ".keu_kasbankht set posting=0,pembayaran=0,novoucher='' where notransaksi='" . $bsdlis . "' ";
					$strdelapv = "delete from " . $dbname . ".approval where notransaksi='" . $bsdlis . "' ";
					try {
						$owlPDO->exec($sDel);
						$owlPDO->exec($sUp);
						$owlPDO->exec($strdelapv);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}


					#== periksa apakah dia termasuk auto kasbank
					$str = "select * from " . $dbname . ".keu_kasbankht  where notransaksi='" . $bsdlis . "'"; #exit("error".$str);
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar = $res->fetch();
					$autokb = $bar['autokb'];
					$norefautokb = $bar['noreferensi'];
					if ($autokb == 1) {

						#= insert ke table tool_logunposting
						#= cari nomor jurnal
						$strj = "select nojurnal from " . $dbname . ".keu_jurnalht  where noreferensi='" . $norefautokb . "'"; #exit("error".$str);
						$resj = $owlPDO->query($strj) or die(print " Gagal: " . PDOException::getMessage());
						$resj->setFetchMode(PDO::FETCH_ASSOC);
						$barj = $resj->fetch();
						$nojurnalautokb = $barj['nojurnal'];

						$strj = "select novoucher from " . $dbname . ".keu_kasbankht  where notransaksi='" . $norefautokb . "'"; #exit("error".$str);
						$resj = $owlPDO->query($strj) or die(print " Gagal: " . PDOException::getMessage());
						$resj->setFetchMode(PDO::FETCH_ASSOC);
						$barj = $resj->fetch();
						$novoucherautokb = $barj['novoucher'];
						$unitautokb = $barj['kodeorg'];

						$strlog .= "insert into " . $dbname . ".tool_logunposting
						(notransaksi, nojurnal, novoucher,unit,jenis,tanggal, user)
						values('" . $norefautokb . "','" . $nojurnalautokb . "',
								'" . $novoucherautokb . "','" . $unitautokb . "','KASBANK',
								'" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "');";
						if ($strlog != '') {
							try {
								$owlPDO->exec($strlog);
								$strlog = '';
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						}


						$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '" . $norefautokb . "'";
						#= auto kb wajib delet kasbanknya
						$sUp = "delete from " . $dbname . ".keu_kasbankht where notransaksi='" . $norefautokb . "' ";
						try {
							$owlPDO->exec($sDel);
							$owlPDO->exec($sUp);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					}
					#= tutup unpost auto kb


				}
				break;

			case '18': #= kasbank tidak hapus novoucher
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					#periksa apakah unit RK lain sudah tutup buku
					$comment = '';
					// $strz="select nojurnal,tanggal from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."'";
					// exit("Error:$strz");
					$tempnotransaksi = $tempnojurnal = '';
					$str = $owlPDO->query("select nojurnal,tanggal from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "'");
					$str->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $str->fetch()) {
						$nojurnal = $bar->nojurnal;
						$unit = explode("/", $bar->nojurnal);
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $unit[1] . "' and periode='" . substr($bar->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							$comment .= "-" . $baru->kodeorg . " periode " . $baru->periode . " has been closed\n";
						}

						$strv = "select novoucher from " . $dbname . ".keu_kasbankht  where notransaksi='" . $bsdlis . "'"; #exit("error".$str);
						$resv = $owlPDO->query($strv) or die(print " Gagal: " . PDOException::getMessage());
						$resv->setFetchMode(PDO::FETCH_ASSOC);
						$barv = $resv->fetch();
						$novoucher = $barv['novoucher'];

						#= jika sukses maka insert ke table pembantu		
						#=cek jika sudah ada nojurnal dan notransaksi yang sama maka tidak perlu insert ketable log

						$strcek = "select count(*) as jumlah 
						from " . $dbname . ".tool_logunposting  where 
						notransaksi='" . $bsdlis . "' and nojurnal='" . $nojurnal . "'";
						$rescek = $owlPDO->query($strcek) or die(print " Gagal: " . PDOException::getMessage());
						$rescek->setFetchMode(PDO::FETCH_ASSOC);
						$barcek = $rescek->fetch();
						if ($barcek['jumlah'] < '1') {
							$strlog = "insert into " . $dbname . ".tool_logunposting
							(notransaksi, nojurnal, novoucher,unit,jenis,tanggal, user)
							values('" . $bsdlis . "','" . $nojurnal . "',
							'" . $novoucher . "','" . $unit[1] . "','KASBANK',
							'" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "');";
							try {
								$owlPDO->exec($strlog);
								$strlog = '';
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						}
					}

					if ($comment != '') {
						#delete table log 
						$sDel = "delete from " . $dbname . ".tool_logunposting where notransaksi='" . $bsdlis . "' and nojurnal='" . $nojurnal . "'";
						try {
							$owlPDO->exec($sDel);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
						// exit(" Error :".$comment."\n process canceled");
						exit(" Error :" . $comment . "\n proces dibatalkan");
					}




					#= proses unposting hapus jurnal dan unflag transaksi , approval
					$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "'";
					// saat unpost, tambahkan hapus novoucher. as requested by vienny via WA 2021-07-15
					$sUp = "update " . $dbname . ".keu_kasbankht set posting=0,pembayaran=0 where notransaksi='" . $bsdlis . "' ";
					$strdelapv = "delete from " . $dbname . ".approval where notransaksi='" . $bsdlis . "' ";
					try {
						$owlPDO->exec($sDel);
						$owlPDO->exec($sUp);
						$owlPDO->exec($strdelapv);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}


					#== periksa apakah dia termasuk auto kasbank
					$str = "select * from " . $dbname . ".keu_kasbankht  where notransaksi='" . $bsdlis . "'"; #exit("error".$str);
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar = $res->fetch();
					$autokb = $bar['autokb'];
					$norefautokb = $bar['noreferensi'];
					if ($autokb == 1) {

						#= insert ke table tool_logunposting
						#= cari nomor jurnal
						$strj = "select nojurnal from " . $dbname . ".keu_jurnalht  where noreferensi='" . $norefautokb . "'"; #exit("error".$str);
						$resj = $owlPDO->query($strj) or die(print " Gagal: " . PDOException::getMessage());
						$resj->setFetchMode(PDO::FETCH_ASSOC);
						$barj = $resj->fetch();
						$nojurnalautokb = $barj['nojurnal'];

						$strj = "select novoucher from " . $dbname . ".keu_kasbankht  where notransaksi='" . $norefautokb . "'"; #exit("error".$str);
						$resj = $owlPDO->query($strj) or die(print " Gagal: " . PDOException::getMessage());
						$resj->setFetchMode(PDO::FETCH_ASSOC);
						$barj = $resj->fetch();
						$novoucherautokb = $barj['novoucher'];
						$unitautokb = $barj['kodeorg'];

						$strlog .= "insert into " . $dbname . ".tool_logunposting
						(notransaksi, nojurnal, novoucher,unit,jenis,tanggal, user)
						values('" . $norefautokb . "','" . $nojurnalautokb . "',
								'" . $novoucherautokb . "','" . $unitautokb . "','KASBANK',
								'" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "');";
						if ($strlog != '') {
							try {
								$owlPDO->exec($strlog);
								$strlog = '';
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						}


						$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '" . $norefautokb . "'";
						#= auto kb wajib delet kasbanknya
						$sUp = "delete from " . $dbname . ".keu_kasbankht where notransaksi='" . $norefautokb . "' ";
						try {
							$owlPDO->exec($sDel);
							$owlPDO->exec($sUp);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					}
					#= tutup unpost auto kb


				}
				break;




			#= case 2 baru : yaitu kasir
			case '2': #= kasbank


				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					#periksa apakah unit RK lain sudah tutup buku
					$comment = '';
					// $strz="select nojurnal,tanggal from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."'";
					// exit("Error:$strz");
					$tempnotransaksi = $tempnojurnal = '';
					$str = $owlPDO->query("select nojurnal,tanggal from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "'");
					$str->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $str->fetch()) {
						$nojurnal = $bar->nojurnal;
						$unit = explode("/", $bar->nojurnal);
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $unit[1] . "' and periode='" . substr($bar->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							$comment .= "-" . $baru->kodeorg . " periode " . $baru->periode . " has been closed\n";
						}

						$strv = "select novoucher from " . $dbname . ".keu_kasbankht  where notransaksi='" . $bsdlis . "'"; #exit("error".$str);
						$resv = $owlPDO->query($strv) or die(print " Gagal: " . PDOException::getMessage());
						$resv->setFetchMode(PDO::FETCH_ASSOC);
						$barv = $resv->fetch();
						$novoucher = $barv['novoucher'];

						#= jika sukses maka insert ke table pembantu		
						#=cek jika sudah ada nojurnal dan notransaksi yang sama maka tidak perlu insert ketable log

						$strcek = "select count(*) as jumlah 
							from " . $dbname . ".tool_logunposting  where 
							notransaksi='" . $bsdlis . "' and nojurnal='" . $nojurnal . "'";
						$rescek = $owlPDO->query($strcek) or die(print " Gagal: " . PDOException::getMessage());
						$rescek->setFetchMode(PDO::FETCH_ASSOC);
						$barcek = $rescek->fetch();
						if ($barcek['jumlah'] < '1') {
							$strlog = "insert into " . $dbname . ".tool_logunposting
								(notransaksi, nojurnal, novoucher,unit,jenis,tanggal, user)
								values('" . $bsdlis . "','" . $nojurnal . "',
								'" . $novoucher . "','" . $unit[1] . "','KASBANK',
								'" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "');";
							try {
								$owlPDO->exec($strlog);
								$strlog = '';
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						}
					}

					if ($comment != '') {
						#delete table log 
						$sDel = "delete from " . $dbname . ".tool_logunposting where notransaksi='" . $bsdlis . "' and nojurnal='" . $nojurnal . "'";
						try {
							$owlPDO->exec($sDel);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
						// exit(" Error :".$comment."\n process canceled");
						exit(" Error :" . $comment . "\n proces dibatalkan");
					}




					#= proses unposting hapus jurnal dan unflag transaksi
					$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "'";
					// saat unpost, tambahkan hapus novoucher. as requested by vienny via WA 2021-07-15
					$sUp = "update " . $dbname . ".keu_kasbankht set posting=1,pembayaran=0,novoucher='' where notransaksi='" . $bsdlis . "' ";
					try {
						$owlPDO->exec($sDel);
						$owlPDO->exec($sUp);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}

					#== periksa apakah dia termasuk auto kasbank
					$str = "select * from " . $dbname . ".keu_kasbankht  where notransaksi='" . $bsdlis . "'"; #exit("error".$str);
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar = $res->fetch();
					$autokb = $bar['autokb'];
					$norefautokb = $bar['noreferensi'];
					if ($autokb == 1) {

						#= insert ke table tool_logunposting
						#= cari nomor jurnal
						$strj = "select nojurnal from " . $dbname . ".keu_jurnalht  where noreferensi='" . $norefautokb . "'"; #exit("error".$str);
						$resj = $owlPDO->query($strj) or die(print " Gagal: " . PDOException::getMessage());
						$resj->setFetchMode(PDO::FETCH_ASSOC);
						$barj = $resj->fetch();
						$nojurnalautokb = $barj['nojurnal'];

						$strj = "select novoucher from " . $dbname . ".keu_kasbankht  where notransaksi='" . $norefautokb . "'"; #exit("error".$str);
						$resj = $owlPDO->query($strj) or die(print " Gagal: " . PDOException::getMessage());
						$resj->setFetchMode(PDO::FETCH_ASSOC);
						$barj = $resj->fetch();
						$novoucherautokb = $barj['novoucher'];
						$unitautokb = $barj['kodeorg'];

						$strlog .= "insert into " . $dbname . ".tool_logunposting
							(notransaksi, nojurnal, novoucher,unit,jenis,tanggal, user)
							values('" . $norefautokb . "','" . $nojurnalautokb . "',
									'" . $novoucherautokb . "','" . $unitautokb . "','KASBANK',
									'" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "');";
						if ($strlog != '') {
							try {
								$owlPDO->exec($strlog);
								$strlog = '';
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						}

						/*	
						$sDel="delete from ".$dbname.".keu_jurnalht where noreferensi like '".$norefautokb."'";
                        // saat unpost, tambahkan hapus novoucher. as requested by vienny via WA 2021-07-15
						$sUp="update ".$dbname.".keu_kasbankht set posting=0,pembayaran=0,nocek='',novoucher='' where notransaksi='".$norefautokb."' ";
						*/

						$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '" . $norefautokb . "'";
						#= auto kb wajib delet kasbanknya
						$sUp = "delete from " . $dbname . ".keu_kasbankht where notransaksi='" . $norefautokb . "' ";

						try {
							$owlPDO->exec($sDel);
							$owlPDO->exec($sUp);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					}
					#= tutup unpost auto kb


				}
				break;
			case '19': #= kasbank


				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					#periksa apakah unit RK lain sudah tutup buku
					$comment = '';
					// $strz="select nojurnal,tanggal from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."'";
					// exit("Error:$strz");
					$tempnotransaksi = $tempnojurnal = '';
					$str = $owlPDO->query("select nojurnal,tanggal from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "'");
					$str->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $str->fetch()) {
						$nojurnal = $bar->nojurnal;
						$unit = explode("/", $bar->nojurnal);
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $unit[1] . "' and periode='" . substr($bar->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							$comment .= "-" . $baru->kodeorg . " periode " . $baru->periode . " has been closed\n";
						}

						$strv = "select novoucher from " . $dbname . ".keu_kasbankht  where notransaksi='" . $bsdlis . "'"; #exit("error".$str);
						$resv = $owlPDO->query($strv) or die(print " Gagal: " . PDOException::getMessage());
						$resv->setFetchMode(PDO::FETCH_ASSOC);
						$barv = $resv->fetch();
						$novoucher = $barv['novoucher'];

						#= jika sukses maka insert ke table pembantu		
						#=cek jika sudah ada nojurnal dan notransaksi yang sama maka tidak perlu insert ketable log

						$strcek = "select count(*) as jumlah 
							from " . $dbname . ".tool_logunposting  where 
							notransaksi='" . $bsdlis . "' and nojurnal='" . $nojurnal . "'";
						$rescek = $owlPDO->query($strcek) or die(print " Gagal: " . PDOException::getMessage());
						$rescek->setFetchMode(PDO::FETCH_ASSOC);
						$barcek = $rescek->fetch();
						if ($barcek['jumlah'] < '1') {
							$strlog = "insert into " . $dbname . ".tool_logunposting
								(notransaksi, nojurnal, novoucher,unit,jenis,tanggal, user)
								values('" . $bsdlis . "','" . $nojurnal . "',
								'" . $novoucher . "','" . $unit[1] . "','KASBANK',
								'" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "');";
							try {
								$owlPDO->exec($strlog);
								$strlog = '';
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						}
					}

					if ($comment != '') {
						#delete table log 
						$sDel = "delete from " . $dbname . ".tool_logunposting where notransaksi='" . $bsdlis . "' and nojurnal='" . $nojurnal . "'";
						try {
							$owlPDO->exec($sDel);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
						// exit(" Error :".$comment."\n process canceled");
						exit(" Error :" . $comment . "\n proces dibatalkan");
					}




					#= proses unposting hapus jurnal dan unflag transaksi
					$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "'";
					$sUp = "update " . $dbname . ".keu_kasbankht set posting=1,pembayaran=0 where notransaksi='" . $bsdlis . "' ";
					try {
						$owlPDO->exec($sDel);
						$owlPDO->exec($sUp);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}

					#== periksa apakah dia termasuk auto kasbank
					$str = "select * from " . $dbname . ".keu_kasbankht  where notransaksi='" . $bsdlis . "'"; #exit("error".$str);
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar = $res->fetch();
					$autokb = $bar['autokb'];
					$norefautokb = $bar['noreferensi'];
					if ($autokb == 1) {

						#= insert ke table tool_logunposting
						#= cari nomor jurnal
						$strj = "select nojurnal from " . $dbname . ".keu_jurnalht  where noreferensi='" . $norefautokb . "'"; #exit("error".$str);
						$resj = $owlPDO->query($strj) or die(print " Gagal: " . PDOException::getMessage());
						$resj->setFetchMode(PDO::FETCH_ASSOC);
						$barj = $resj->fetch();
						$nojurnalautokb = $barj['nojurnal'];

						$strj = "select novoucher from " . $dbname . ".keu_kasbankht  where notransaksi='" . $norefautokb . "'"; #exit("error".$str);
						$resj = $owlPDO->query($strj) or die(print " Gagal: " . PDOException::getMessage());
						$resj->setFetchMode(PDO::FETCH_ASSOC);
						$barj = $resj->fetch();
						$novoucherautokb = $barj['novoucher'];
						$unitautokb = $barj['kodeorg'];

						$strlog .= "insert into " . $dbname . ".tool_logunposting
							(notransaksi, nojurnal, novoucher,unit,jenis,tanggal, user)
							values('" . $norefautokb . "','" . $nojurnalautokb . "',
									'" . $novoucherautokb . "','" . $unitautokb . "','KASBANK',
									'" . date('Y-m-d H:i') . "','" . $_SESSION['standard']['userid'] . "');";
						if ($strlog != '') {
							try {
								$owlPDO->exec($strlog);
								$strlog = '';
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						}


						/*	
						$sDel="delete from ".$dbname.".keu_jurnalht where noreferensi like '".$norefautokb."'";
                        // saat unpost, tambahkan hapus novoucher. as requested by vienny via WA 2021-07-15
						$sUp="update ".$dbname.".keu_kasbankht set posting=0,pembayaran=0,nocek='',novoucher='' where notransaksi='".$norefautokb."' ";
						*/

						$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '" . $norefautokb . "'";
						#= auto kb wajib delet kasbanknya
						$sUp = "delete from " . $dbname . ".keu_kasbankht where notransaksi='" . $norefautokb . "' ";
						try {
							$owlPDO->exec($sDel);
							$owlPDO->exec($sUp);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					}
					#= tutup unpost auto kb


				}
				break;


			case '21':
				try {
					$owlPDO->beginTransaction();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
						#= hapus jurnal
						$str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
						$owlPDO->exec($str);

						#= unflag
						$str = "update " . $dbname . ".pabrik_bamutasi set posting='0', postingby='' where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);
					}

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Warning: Gagal melakukan unposting data \n" . addslashes($e->getMessage());
				}
				break;

			case '22':
				try {
					$owlPDO->beginTransaction();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
						#= hapus jurnal
						$str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
						$owlPDO->exec($str);

						#= hapus tagihan
						$str = "delete from " . $dbname . ".keu_tagihanht where nopo='" . $bsdlis . "'";
						$owlPDO->exec($str);

						$str = "delete from " . $dbname . ".keu_tagihandt where nopo='" . $bsdlis . "'";
						$owlPDO->exec($str);


						#= unflag
						$str = "update " . $dbname . ".pmn_batransport set posting='0', postingby='' where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);
					}

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Warning: Gagal melakukan unposting data \n" . addslashes($e->getMessage());
				}
				break;

			case '23':
				try {
					$owlPDO->beginTransaction();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
						#= hapus jurnal
						$str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
						$owlPDO->exec($str);

						#= unflag
						$str = "update " . $dbname . ".pmn_bapengiriman set posting='0', postingby='' where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);
					}

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Warning: Gagal melakukan unposting data \n" . addslashes($e->getMessage());
				}
				break;


			case '24':
				try {
					$owlPDO->beginTransaction();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
						#= hapus jurnal
						$str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
						$owlPDO->exec($str);

						#= unflag
						$str = "update " . $dbname . ".pmn_bast set posting='0' where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);
					}

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Warning: Gagal melakukan unposting data \n" . addslashes($e->getMessage());
				}
				break;


			case '25':

				#= pengecekan : 
				#= 1. periode akuntansi unit
				#= 2. periode akuntansi ro jika ada jurnal interco ke ro
				#= 3. jika ada project cek project tersebut sudah close / belum




				$comment = '';
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {

					#= ambil unit yang terlibat jurnal dan tanggalnya
					$str = "select distinct(kodeorg) as kodeorg,periode from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $bsdlis . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						#= periode akuntansi 
						$strdt = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bar['kodeorg'] . "' and periode='" . $bar['periode'] . "' ";
						$resdt = fetchdata($strdt);
						foreach ($resdt as $bardt) {
							$comment .= "" . $bardt['kodeorg'] . " periode " . $bar['periode'] . " sudah closing\n";
						}
					}

					#= ambil unit yang terlibat jurnal dan tanggalnya
					$str = "select kodeasset from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $bsdlis . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						if ($bar['kodeasset'] != '') {
							$strdt = "select * from " . $dbname . ".project where posting=1 and kode='" . $bar['kodeasset'] . "'";
							$resdt = fetchdata($strdt);
							foreach ($resdt as $bardt) {
								$comment .= "Project : " . $bardt['kodeasset'] . "  sudah diposting\n";
							}
						}
					}

					if ($comment != '') {
						exit("Warning:Gagal melakukan unposting\n " . $comment . " ");
					}

					#= hapus jurnal
					$str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
					$owlPDO->exec($str);

					#= unflag
					$str = "update " . $dbname . ".log_noninventory set posting='0',persetujuan='0' where notransaksi='" . $bsdlis . "'";
					$owlPDO->exec($str);

					#= unflag
					$str = "delete from  " . $dbname . ".approval  where notransaksi='" . $bsdlis . "'";
					$owlPDO->exec($str);
				}


				break;


			case '26':
				try {
					$owlPDO->beginTransaction();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
						#= unflag
						$str = "update " . $dbname . ".pmn_hargabelitbs set posting='0' where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);

						#= delete approval
						$str = "delete from " . $dbname . ".approval where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);
					}
					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Warning: Gagal melakukan unposting data \n" . addslashes($e->getMessage());
				}
				break;

			case '27':
				try {
					$owlPDO->beginTransaction();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
						#= unflag
						$str = "update " . $dbname . ".pmn_hargajualtbs set posting='0' where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);

						#= delete approval
						$str = "delete from " . $dbname . ".approval where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);
					}
					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Warning: Gagal melakukan unposting data \n" . addslashes($e->getMessage());
				}
				break;

			// this is
			case '28':
				try {
					$owlPDO->beginTransaction();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {

						// cek dulu apakah dinotransaksi tersebut sudah masuk noreferensi di notransaksi lain
						$str = "select notransaksi from " . $dbname . ".pabrik_transferproduk where noreferensi='" . $bsdlis . "'";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$noreferensi = $bar['notransaksi'];
						}

						if (count($res) > 0) {
							exit("Warning : " . $bsdlis . " sudah masuk di notransaksi " . $noreferensi . "");
						}
						#= unflag
						$str = "update " . $dbname . ".pabrik_transferproduk set posting='0' where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);

						#= delete approval
						$str = "delete from " . $dbname . ".approval where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);
					}
					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Warning: Gagal melakukan unposting data \n" . addslashes($e->getMessage());
				}
				break;

			case '29':
				try {
					$owlPDO->beginTransaction();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {

						#= ambil data unit dari jurnal tersebut
						$str = "select * from " . $dbname . ".keu_jurnalmemorial where nojurnal='" . $bsdlis . "'";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$kodeorg = $bar['kodeorg'];
							$tanggal = $bar['tanggal'];
							$periode = substr($bar['tanggal'], 0, 7);
						}

						#= cek periode akuntansi
						$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $kodeorg . "' and periode='" . $periode . "'";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$tutupbuku = $bar['tutupbuku'];
						}

						if ($tutupbuku == '1') {
							throw new PDOException("Gagal unposting jurnal " . $bsdlis . ", Unit : " . $kodeorg . " Periode " . $periode . " sudah ditutup\n");
						}

						#= Move approval return (sebelum delete)
						movetohistory($bsdlis);

						#= delete approval
						$str = "delete from " . $dbname . ".approval where notransaksi='" . $bsdlis . "'";
						$owlPDO->exec($str);

						#= unflag
						$str = "update " . $dbname . ".keu_jurnalmemorial set posting='0' where nojurnal='" . $bsdlis . "'";
						$owlPDO->exec($str);

						#= delete keu_jurnalht
						$str = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $bsdlis . "'";
						$owlPDO->exec($str);
					}
					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Warning: Gagal melakukan unposting data \n" . addslashes($e->getMessage());
				}
				break;


			case '20':
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					try {
						$sdeljrn = "delete from " . $dbname . ".approval where notransaksi='" . $bsdlis . "' AND jenispersetujuan='GDOKFIN'";
						try {
							$owlPDO->exec($sdeljrn);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>" . $sdeljrn;
							die();
						}

						$supdateproj = "update " . $dbname . ".keu_gantidokumen set posting=0 where notransaksi='" . $bsdlis . "'";
						try {
							$owlPDO->exec($supdateproj);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;


			case '2xx': #= tidak dipakai ; karna diganti dengan case kasir
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {

					#periksa apakah unit RK lain sudah tutup buku
					$comment = '';
					$str = $owlPDO->query("select nojurnal,tanggal from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "%'");
					$str->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $str->fetch()) {
						$unit = explode("/", $bar->nojurnal);
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $unit[1] . "' and periode='" . substr($bar->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
							$comment .= "-" . $baru->kodeorg . " periode " . $baru->periode . " sudah closing\n";
						}
					}
					if ($comment != '') {
						// exit(" Error :".$comment."\n process canceled");
						exit(" Error :" . $comment . "\n proces dibatalkan");
					}
					$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "%'";
					$sUp = "update " . $dbname . ".kebun_aktifitas set jurnal=0 where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sDel);
						$owlPDO->exec($sUp);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '3':
				try {
					$owlPDO->beginTransaction();
					#noreff == nospk
					#nodok == nobapp

					$str = "select * from " . $dbname . ".keu_jurnaldt where noreferensi = '" . $_POST['notransaksi'][0] . "' and tanggal = '" . $_POST['tanggal'][0] . "' and nodok='" . $_POST['nobapp'][0] . "'"; #exit("error".$str);
					$res = fetchdata($str);
					foreach ($res as $val) {
						$unit = explode("/", $val['nojurnal']);
						#periksa apakah sudah tutup buku
						$stu = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $unit[1] . "' and periode='" . substr($val['tanggal'], 0, 7) . "'";
						$rus = fetchdata($stu);
						if (count($rus) > 0) {
							throw new PDOException($rus[0]['kodeorg'] . " periode " . $rus[0]['periode'] . " has been closed\n");
						}

						$sDel = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $val['nojurnal'] . "'";
						$owlPDO->exec($sDel);
					}

					$strx = "select nopengajuan from " . $dbname . ".log_baspk where keterangan ='" . $_POST['nobapp'][0] . "'";
					$resx = fetchdata($strx);
					movetohistory($resx[0]['nopengajuan']);


					$strx = "delete from " . $dbname . ".approval where notransaksi='" . $resx[0]['nopengajuan'] . "'";
					$owlPDO->exec($strx);

					$sUp = "update " . $dbname . ".log_baspk set statusjurnal=0,posting=0,statuspengajuan=0,nopengajuan='' where keterangan='" . $_POST['nobapp'][0] . "' and notransaksi='" . $_POST['notransaksi'][0] . "'";
					$owlPDO->exec($sUp);

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Error, " . addslashes($e->getMessage());
					die();
				}
				break;
			case '30':
				try {
					$owlPDO->beginTransaction();
					#noreff == nospk
					#nodok == nobapp

					$str = "select * from " . $dbname . ".keu_jurnaldt where noreferensi = '" . $_POST['notransaksi'][0] . "' and tanggal = '" . $_POST['tanggal'][0] . "' and nodok='" . $_POST['nobapp'][0] . "'"; #exit("error".$str);
					$res = fetchdata($str);
					foreach ($res as $val) {
						$unit = explode("/", $val['nojurnal']);
						#periksa apakah sudah tutup buku
						$stu = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $unit[1] . "' and periode='" . substr($val['tanggal'], 0, 7) . "'";
						$rus = fetchdata($stu);
						if (count($rus) > 0) {
							throw new PDOException($rus[0]['kodeorg'] . " periode " . $rus[0]['periode'] . " has been closed\n");
						}

						$sDel = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $val['nojurnal'] . "'";
						$owlPDO->exec($sDel);
					}

					$strx = "select nopengajuan from " . $dbname . ".log_baspk where keterangan ='" . $_POST['nobapp'][0] . "'";
					$resx = fetchdata($strx);
					movetohistory($resx[0]['nopengajuan']);


					$strx = "delete from " . $dbname . ".approval where notransaksi='" . $resx[0]['nopengajuan'] . "'";
					$owlPDO->exec($strx);

					$sUp = "update " . $dbname . ".log_baspk set statusjurnal=0,posting=0,statuspengajuan=0,nopengajuan='' where keterangan='" . $_POST['nobapp'][0] . "' and notransaksi='" . $_POST['notransaksi'][0] . "'";
					$owlPDO->exec($sUp);

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Error, " . addslashes($e->getMessage());
					die();
				}
				break;
			case '31':
				try {
					$owlPDO->beginTransaction();

					$strx = "select kodeorg,tanggal from " . $dbname . ".sdm_uploadabsensihoht where notransaksi ='" . $_POST['notransaksi'][0] . "'";
					$resx = fetchdata($strx);
					$unitHO = $resx[0]['kodeorg'];
					$tanggalHO = $resx[0]['tanggal'];

					$strdel = "delete from " . $dbname . ".sdm_absensiht where kodeorg='" . $unitHO . "' and tanggal='" . $tanggalHO . "' ";
					$owlPDO->exec($strdel);

					$strdeldt = "delete from " . $dbname . ".sdm_absensidt where norefrensi='" . $_POST['notransaksi'][0] . "' and kodeorg='" . $unitHO . "' and norefrensi!=''";
					$owlPDO->exec($strdeldt);

					$sUp = "update " . $dbname . ".sdm_uploadabsensihoht set posting=0 where notransaksi='" . $_POST['notransaksi'][0] . "'";
					$owlPDO->exec($sUp);

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Error, " . addslashes($e->getMessage());
					die();
				}
				break;
			case '32':
				try {
					$owlPDO->beginTransaction();

					$strx = "select kodeorg,tanggal from " . $dbname . ".sdm_suratperingatan where nomor ='" . $_POST['notransaksi'][0] . "'";
					$resx = fetchdata($strx);
					$kdunit = $resx[0]['kodeorg'];
					$tglunit = $resx[0]['tanggal'];

					$strdel = "delete from " . $dbname . ".sdm_absensiht where kodeorg='" . $kdunit . "' and tanggal='" . $tglunit . "' ";
					$owlPDO->exec($strdel);

					$sUp = "update " . $dbname . ".sdm_suratperingatan set posting=0 where nomor='" . $_POST['notransaksi'][0] . "'";
					$owlPDO->exec($sUp);

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Error, " . addslashes($e->getMessage());
					die();
				}
				break;

			/***********************************************************************
				 ** 4: Unposting BKM ***************************************************
				 ***********************************************************************/
			case '4':
				try {
					$owlPDO->beginTransaction();

					$notran = array();
					foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
						$notran[$bsdlis] = $bsdlis;
					}

					if (count($notran) == 0) {
						throw new PDOException("Notransaksi tidak ada.");
					}


					foreach ($notran as $bsdlis) {
						$dttgl = array();
						$str = "select a.tanggal,b.kodeorg from " . $dbname . ".kebun_aktifitas a left join " . $dbname . ".kebun_prestasi b on a.notransaksi=b.notransaksi where a.notransaksi = '" . $bsdlis . "' and a.tipetransaksi='PNN'";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$dttgl[substr($bar['kodeorg'], 0, 6)] = $bar['tanggal'];
							$perd[substr($bar['kodeorg'], 0, 6)] = substr($bar['tanggal'], 0, 7);
						}

						if (count($dttgl) > 0) {
							foreach ($dttgl as $div => $tanggal) {
								// $str = "select * from ".$dbname.".kebun_3premipemanen where divisi = '".$div."' and tanggalpanen<='".$tanggal."' and periode='".$perd[$div]."' and posting='1'";
								$str = "select * from " . $dbname . ".kebun_3premipemanen where divisi = '" . $div . "' and tanggalpanen='" . $tanggal . "' and periode='" . $perd[$div] . "' and posting='1'";
								$res = fetchdata($str);
								if (count($res) > 0) {
									throw new PDOException("Proses premi pemanen sudah diposting, silahkan unposting terlebih dahulu.");
								}
							}
						}

						# [VALIDASI] periksa apakah unit RK lain sudah tutup buku
						$comment = '';
						$str = "select nojurnal,tanggal from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "%'";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$unit = explode("/", $bar['nojurnal']);
							#periksa apakah sudah tutup buku
							$stu = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $unit[1] . "' and periode='" . substr($bar['tanggal'], 0, 7) . "'";
							$resu = fetchdata($stu);
							foreach ($resu as $baru) {
								$comment .= "\n-" . $baru['kodeorg'] . " periode " . $baru['periode'] . " has been closed\n";
							}
						}
						if ($comment != '') {
							// throw new PDOException($comment."\n process canceled");
							throw new PDOException($comment . "\n proces dibatalkan");
						}

						$comment = '';
						# Periksa periode akutansi apakah sudah tutup buku
						$strt = "select kodeorg,periode from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $bsdlis . "'";
						$res = fetchdata($strt);
						foreach ($res as $bat) {
							#periksa apakah sudah tutup buku
							$stu = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat['kodeorg'] . "' and periode='" . $bat['periode'] . "'";
							$resu = fetchdata($stu);
							foreach ($resu as $baru) {
								$comment .= "\n-" . $baru['kodeorg'] . " periode " . $baru['periode'] . " has been closed\n";
							}
						}

						if ($comment != '') {
							throw new PDOException($comment . "\n process canceled");
						}

						$comment = "";
						#periksa gudang apakah sudah tutup buku
						$strt = "select kodegudang,tanggal from " . $dbname . ".log_transaksiht where notransaksireferensi='" . $bsdlis . "'";
						$res = fetchdata($strt);
						// if(count($res)>0){
						// 	throw new PDOException("Unposting BKM dengan Material tidak di perbolehkan lagi.");
						// }
						foreach ($res as $bat) {
							#periksa apakah sudah tutup buku
							$stu = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat['kodegudang'] . "' and periode='" . substr($bat['tanggal'], 0, 7) . "'";
							$resu = fetchdata($stu);
							foreach ($resu as $baru) {
								// $comment.="\n-".$baru['kodeorg']." periode ".$baru['periode']." has been closed";
								$comment .= "\n-" . $baru['kodeorg'] . " periode " . $baru['periode'] . " sudah closing";
							}
						}
						if ($comment != '') {
							// throw new PDOException($comment."\n process canceled");
							throw new PDOException($comment . "\n proces dibatalkan");
						}

						# update log_5saldobulanan
						$stt = "select sum(jumlah) as jumlah, kodept,kodebarang,tanggal,kodegudang from " . $dbname . ".log_transaksi_vw where notransaksireferensi='" . $bsdlis . "' group by kodept,kodebarang,tanggal,kodegudang";
						//echo '1.'.$stt.'<br>';
						$ret = fetchdata($stt);
						$rupiahbarang = array();
						$jumlahbarang = array();
						if (count($ret) > 0) {
							foreach ($ret as $bat) {

								$qty = $bat['jumlah'];
								$rp = 0;
								# ambil nilai barang dari jurnal and noakun like '115%'
								$sx = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt where noreferensi ='" . $bsdlis . "' and kodebarang='" . $bat['kodebarang'] . "' ";
								//echo '2.'.$sx.'<br>';
								$rx = fetchdata($sx)[0];
								$rp = abs($rx['jumlah']);
								if (count($rx) == 0) {
									// throw new PDOException("Nilai barang tidak ditemukan process canceled");
									throw new PDOException("Nilai barang tidak ditemukan proces dibatalkan");
								}
								if ($rp == 0) {
									throw new PDOException("Nilai barang tidak ditemukan proces dibatalkan");
								}

								$query =  " update " . $dbname . ".log_5saldobulanan set qtykeluar=(qtykeluar-" . $qty . "), qtykeluarxharga=(qtykeluarxharga-" . $rp . "),  saldoakhirqty=(saldoakhirqty+" . $qty . "), nilaisaldoakhir=(nilaisaldoakhir+" . $rp . "), hargarata=((nilaisaldoakhir+" . $rp . ")/(saldoakhirqty+" . $qty . ")) where kodeorg ='" . $bat['kodept'] . "' and kodebarang='" . $bat['kodebarang'] . "' and periode='" . substr($bat['tanggal'], 0, 7) . "' and kodegudang='" . $bat['kodegudang'] . "'";

								//echo '3.'.$query.'<br>';
								$owlPDO->exec($query);
							}
						}

						# update log_5saldobulanan
						// $stt = "select * from ".$dbname.".log_transaksiht where notransaksireferensi='".$bsdlis."'";
						// $ret = fetchdata($stt);
						// if(count($ret)>0){
						// 	foreach($ret as $bat){
						// 		$str = "select * from ".$dbname.".log_transaksidt where notransaksi ='".$bat['notransaksi']."'";
						// 		$res = fetchdata($str); $qty=0;
						// 		foreach($res as $bar){
						// 			$qty = $bar['jumlah'];
						// 			$rp=0;
						// 			# ambil nilai barang dari jurnal
						// 			$sx = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt where noreferensi ='".$bsdlis."' and kodebarang='".$bar['kodebarang']."' and noakun like '115%'";
						// 			$rx = fetchdata($sx)[0];
						// 			$rp = abs($rx['jumlah']);
						// 			if(count($rx)==0){
						// 				// throw new PDOException("Nilai barang tidak ditemukan process canceled");
						//                             throw new PDOException("Nilai barang tidak ditemukan proces dibatalkan");
						// 			}
						// 			#pastikan tidak ada retur, kalau sudah ada retur maka batalkan
						// 			$ret="select * from ".$dbname.".log_transaksi_vw where tipetransaksi='2' and notransaksireferensi='".$bar['notransaksi']."'";
						// 			$Ref = fetchData($ret);
						// 			if(count($Ref)>0){
						// 				throw new PDOException("Sudah ada proses retur barang ke gudang dengan nomor transaksi ".$bar['notransaksi']." untuk melanjutkan silahkan unpost dan delete transaksi tersebut, process canceled.");
						// 			}

						// 			#ambil nilai saldo lama
						// 			$sn = "select * from ".$dbname.".log_5saldobulanan where kodeorg ='".$bat['kodept']."' and kodebarang='".$bar['kodebarang']."' and periode='".substr($bat['tanggal'],0,7)."' and kodegudang='".$bat['kodegudang']."'";
						// 			$rn = fetchdata($sn)[0];

						// 			$qtykeluarxharga=$qtykeluar=$qtykeluarxharga=$saldoakhirqty=$nilaisaldoakhir=$hargarata="0";
						// 			$qtykeluar      =round($rn['qtykeluar']-$qty,5);
						// 			$qtykeluarxharga=round($rn['qtykeluarxharga']-$rp,5);
						// 			$saldoakhirqty  =round($rn['saldoakhirqty']+$qty,5);
						// 			$nilaisaldoakhir=round($rn['nilaisaldoakhir']+$rp,5);
						// 			$hargarata      =round(($rn['nilaisaldoakhir']+$rp)/($rn['saldoakhirqty']+$qty),5);


						// 			if($qtykeluar<0){throw new PDOException("Nilai qtykeluar kodebarang ".$bar['kodebarang']." salah, Nilai = ".$qtykeluar.", proses dibatalkan.");}
						// 			if($qtykeluarxharga<0){throw new PDOException("Nilai rupiah keluar kodebarang ".$bar['kodebarang']." salah, Nilai = ".$qtykeluarxharga.", proses dibatalkan.");}
						// 			if($saldoakhirqty<0){throw new PDOException("Nilai saldo akhir kodebarang ".$bar['kodebarang']." salah, Nilai = ".$saldoakhirqty.", proses dibatalkan.");}
						// 			if($nilaisaldoakhir<0){throw new PDOException("Nilai rupiah saldo akhir kodebarang ".$bar['kodebarang']." salah, Nilai = ".$nilaisaldoakhir.", proses dibatalkan.");}
						// 			if($hargarata<0){throw new PDOException("Nilai harga rata - rata kodebarang ".$bar['kodebarang']." salah, Nilai = ".$hargarata.", proses dibatalkan.");}

						// 			$data = array(
						// 				'qtykeluar'      =>$qtykeluar,
						// 				'qtykeluarxharga'=>$qtykeluarxharga,
						// 				'saldoakhirqty'  =>$saldoakhirqty,
						// 				'nilaisaldoakhir'=>$nilaisaldoakhir,
						// 				'hargarata'      =>$hargarata
						// 			);


						// 			$where = "kodeorg ='".$bat['kodept']."' and kodebarang='".$bar['kodebarang']."' and periode='".substr($bat['tanggal'],0,7)."' and kodegudang='".$bat['kodegudang']."'";
						// 			#update log_5saldobulanan
						// 			$query = updateQuery($dbname,'log_5saldobulanan',$data,$where);
						// 			$owlPDO->exec($query);
						// 		}
						// 	}
						// }

						# del jurnal
						$query = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
						$owlPDO->exec($query);

						# del transaksi
						$query = "delete from " . $dbname . ".log_transaksiht where notransaksireferensi='" . $bsdlis . "'";
						$owlPDO->exec($query);

						# del kebun_prestasi_detail
						$query = "delete from " . $dbname . ".kebun_prestasi_detail where notransaksi	='" . $bsdlis . "'";
						$owlPDO->exec($query);

						# del kebun_pakaimaterial_detail
						$query = "delete from " . $dbname . ".kebun_pakaimaterial_detail where notransaksi ='" . $bsdlis . "'";
						$owlPDO->exec($query);

						# del kebun_statuskegiatan
						$query = "delete from " . $dbname . ".kebun_statuskegiatan where notransaksi ='" . $bsdlis . "'";
						$owlPDO->exec($query);

						# del kebun_verifikasibkm
						$query = "delete from " . $dbname . ".kebun_verifikasibkm where notransaksi ='" . $bsdlis . "'";
						$owlPDO->exec($query);

						#update kebun_aktivitas
						$query = "update " . $dbname . ".kebun_aktifitas set jurnal=0 where notransaksi ='" . $bsdlis . "'";
						$owlPDO->exec($query);
					}

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Error, " . addslashes($e->getMessage());
					die();
				}
				break;

			case '5': #traksi
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';
					$stry = $owlPDO->query("select kodeorg,tanggal from " . $dbname . ".vhc_runht where notransaksi='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					while ($bat = $stry->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->kodeorg . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
							$comment2 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " sudah closing\n";
						}
					}
					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n proces dibatalkan");
					}
					$sUp = "update " . $dbname . ".vhc_runht set posting=0  where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sUp);
						$sdel = "delete from " . $dbname . ".vhc_rundt_detail where notransaksi='" . $bsdlis . "'";
						try {
							$owlPDO->exec($sdel);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '6':
				##Tagihan##
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';

					$stry = $owlPDO->query("select unit,tanggal,tanggalinvoice from " . $dbname . ".keu_tagihanht where noinvoice='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					while ($bat = $stry->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->unit . "' and periode='" . substr($bat->tanggalinvoice, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// ganti bahasa: danny 2021-09-09
							// $comment2.="-".$baru->unit." periode ".$baru->periode." has been closed\n";
							$comment2 .= "-" . $baru->unit . " periode " . $baru->periode . " sudah closing\n";
						}

						$stu = $owlPDO->query("select a.notransaksi from " . $dbname . ".keu_kasbankdt a left join " . $dbname . ".keu_kasbankht b on a.notransaksi=b.notransaksi where  a.keterangan1='" . $bsdlis . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="\n- Invoice : ".$bsdlis." has been paid, please cancel/unpost cash/bank transaction : ".$baru->notransaksi." to unpost this item.";
							$comment2 .= "\n- Invoice : " . $bsdlis . " sudah ada di transaksi kas/bank, silakan hapus/unpost transaksi : " . $baru->notransaksi . " terlebih dahulu untuk dapat meng-unpost tagihan ini.";
						}
					}

					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n proces dibatalkan");
					}

					$sUp = "update " . $dbname . ".keu_tagihanht set posting=0 where noinvoice='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sUp);
						$sdel = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "' and kodejurnal='TGH01'";
						try {
							$owlPDO->exec($sdel);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '7':
				##Penerimaan TBS Pabrik##
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';

					$stry = $owlPDO->query("select kodeho,tanggal from " . $dbname . ".keu_persediaantbs_ht where notransaksi='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					while ($bat = $stry->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->kodeho . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							$comment2 .= "-" . $baru->kodeho . " periode " . $baru->periode . " has been closed\n";
						}

						$stu = $owlPDO->query("select noinvoice from " . $dbname . ".keu_tagihanht where nopo='" . $bsdlis . "' and posting='1'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="\n- Invoice of PO : ".$bsdlis." has been created and posted, please unpost Invoice transaction : ".$baru->noinvoice." to unpost this item.";
							$comment2 .= "\n- Invoice PO : " . $bsdlis . " sudah terbentuk dan diposting, silakan unpost transaksi Invoice : " . $baru->noinvoice . " untuk dapat meng-unpost transaksi ini.";
						}
					}

					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n proces dibatalkan");
					}

					$sUp = "update " . $dbname . ".keu_persediaantbs_ht set jurnal=0,jurnalbalik=0 where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sUp);
						$sdel = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "' and kodejurnal='INVTB'";
						try {
							$owlPDO->exec($sdel);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '8':
				##Penerimaan TBS Ramp##
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';

					$stry = $owlPDO->query("select unit,left(datein,10) as tanggal, kodesupplier from " . $dbname . ".pmn_penerimaantbsramp where notiket='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					while ($bat = $stry->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="-".$baru->unit." periode ".$baru->periode." has been closed\n";
							$comment2 .= "-" . $baru->unit . " periode " . $baru->periode . " sudah closing\n";
						}

						$vnopo = ($bat->tanggal) . "/" . $bat->kodesupplier;

						$stu = $owlPDO->query("select noinvoice from " . $dbname . ".keu_tagihanht where nopo='" . $vnopo . "' and posting='1'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="\n- Invoice of PO : ".$bsdlis." has been created and posted, please unpost Invoice transaction : ".$baru->noinvoice." to unpost this item.";
							$comment2 .= "\n- Invoice PO : " . $bsdlis . " sudah terbentuk dan diposting, silakan unpost transaksi Invoice: " . $baru->noinvoice . " untuk dapat meng-unpost transaksi ini.";
						}
					}

					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n proces dibatalkan");
					}

					$sUp = "update " . $dbname . ".pmn_penerimaantbsramp set posted=0 where notiket='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sUp);
						$sdel = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "' and kodejurnal='INVTR'";
						try {
							$owlPDO->exec($sdel);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '9':
				##Pengakuan Penjualan##
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';

					$stry = $owlPDO->query("select millcode as unit,tanggalpengakuan as tanggal from " . $dbname . ".keu_pengakuanjual where notransaksi='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					$vnojurnal = "";
					while ($bat = $stry->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="-".$baru->unit." periode ".$baru->periode." has been closed\n";
							$comment2 .= "-" . $baru->unit . " periode " . $baru->periode . " sudah closing\n";
						}
					}

					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n proces dibatalkan");
					}

					$sUp = "update " . $dbname . ".keu_pengakuanjual set posting=0 where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sUp);
						$sdel = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
						try {
							$owlPDO->exec($sdel);

							$stu = $owlPDO->query("select nojurnal from " . $dbname . ".keu_jurnaldt where keterangan like '%" . $bsdlis . "%'");
							$stu->setFetchMode(PDO::FETCH_OBJ);
							while ($baru = $stu->fetch()) {
								$vnojurnal = $baru->nojurnal;
							}

							$sdel2 = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $vnojurnal . "'";
							try {
								$owlPDO->exec($sdel2);
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '10':
				##Penagihan##
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';

					$stry = $owlPDO->query("select kodeorg as unit,tanggal as tanggal from " . $dbname . ".keu_penagihanht where noinvoice='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					$vnojurnal = "";
					while ($bat = $stry->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="-".$baru->unit." periode ".$baru->periode." has been closed\n";
							$comment2 .= "-" . $baru->unit . " periode " . $baru->periode . " sudah closing\n";
						}
					}

					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n proces dibatalkan");
					}

					$sdel2 = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sdel2);

						$sUp = "update " . $dbname . ".keu_penagihanht set posting=0 where noinvoice='" . $bsdlis . "'";
						try {
							$owlPDO->exec($sUp);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '11':
				##Penerimaan TBS Ramp##
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';

					#select data kas kecil
					$stry = $owlPDO->query("select unit,tanggal,jumlah from " . $dbname . ".keu_kaskecilht a left join " . $dbname . ".keu_kaskecildt b on a.notransaksi=b.notransaksi where a.notransaksi='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					$bat = $stry->fetch();
					$saldotopup = $bat->jumlah;

					#select setup kas kecil
					$stu = $owlPDO->query("select saldoberjalan from " . $dbname . ".keu_5kaskecil where unit='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
					$stu->setFetchMode(PDO::FETCH_OBJ);
					$baru = $stu->fetch();
					$saldoberjalan = $baru->saldoberjalan;

					#periksa apakah sudah tutup buku
					$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
					$stu->setFetchMode(PDO::FETCH_OBJ);
					$baru1 = $stu->fetch();
					if ($baru1->unit != '') {
						// $comment2.="-".$baru1->unit." periode ".$baru1->periode." has been closed\n";
						$comment2 .= "-" . $baru1->unit . " periode " . $baru1->periode . " sudah closing\n";
					}

					#periksa apakah sudah close kas kecil
					$stu = $owlPDO->query("select * from " . $dbname . ".keu_5kaskecil where close=1 and unit='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
					$stu->setFetchMode(PDO::FETCH_OBJ);
					$baru2 = $stu->fetch();
					if ($baru2->unit != '') {
						$comment2 .= "- Petty Cash " . $baru2->unit . " periode " . $baru2->periode . " has been closed\n";
						$comment2 .= "- Petty Cash " . $baru2->unit . " periode " . $baru2->periode . " sudah closing\n";
					}

					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n process dibatalkan");
					}

					$sdel = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sdel);

						$al = 0;
						$al2 = 0;
						$lstTrans = "";
						$notransaksiRef = "";
						$stu = $owlPDO->query("select distinct notransaksi,noakun,tipetransaksi from " . $dbname . ".keu_kasbankdt where nodok='" . $bsdlis . "'");
						$stu->setFetchMode(PDO::FETCH_ASSOC);
						while ($baru = $stu->fetch()) {
							if ($al == 0) {
								$al = 1;
								$lstTrans = "'" . $baru['notransaksi'] . "'";
							} else {
								$lstTrans .= ",'" . $baru['notransaksi'] . "'";
							}

							if ((substr($baru['noakun'], 0, 1) != '1') && ($baru['tipetransaksi'] == 'K')) {

								if ($al1 == 0) {
									$al1 = 1;
									$notransaksiRef = "'" . $baru['notransaksi'] . "'";
								} else {
									$notransaksiRef .= ",'" . $baru['notransaksi'] . "'";
								}
							}
						}

						$sdel = "delete from " . $dbname . ".keu_kasbankht where notransaksi in (" . $lstTrans . ")";
						try {
							$owlPDO->exec($sdel);

							$sdel = "delete from " . $dbname . ".keu_kaskecilht where notransaksi='" . $bsdlis . "'";
							try {
								$owlPDO->exec($sdel);

								$str = "update " . $dbname . ".keu_kaskecilht set noreferensi='' where noreferensi in (" . $notransaksiRef . ") ";
								try {
									$owlPDO->exec($str);

									$saldo = 0;
									$saldo = $saldoberjalan - $saldotopup;
									$str = "update " . $dbname . ".keu_5kaskecil set saldoberjalan='" . $saldo . "' where unit='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'";
									try {
										$owlPDO->exec($str);
									} catch (PDOException $e) {
										echo " Gagal : " . addslashes($e->getMessage());
										die();
									}
								} catch (PDOException $e) {
									echo " Gagal : " . addslashes($e->getMessage());
									die();
								}
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '12':
				##Tagihan##
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';

					$stry = $owlPDO->query("select unit,tanggal from " . $dbname . ".keu_notadebet_ht where notadebet='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					while ($bat = $stry->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment2.="-".$baru->unit." periode ".$baru->periode." has been closed\n";
							$comment2 .= "-" . $baru->unit . " periode " . $baru->periode . " sudah closing\n";
						}
					}

					if ($comment2 != '') {
						exit(" Error :" . $comment2 . "\n process canceled");
					}

					$sUp = "update " . $dbname . ".keu_notadebet_ht set posting=0 where notadebet='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sUp);
						$sdel = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "' and kodejurnal='NOTAD'";
						try {
							$owlPDO->exec($sdel);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '13':
				##Penerimaan TBS Ramp##
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment2 = '';


					#select data kas kecil
					$stry = $owlPDO->query("select unit,tanggal,jumlah from " . $dbname . ".keu_kaskecilht a left join " . $dbname . ".keu_kaskecildt b on a.notransaksi=b.notransaksi where a.notransaksi='" . $bsdlis . "'");
					$stry->setFetchMode(PDO::FETCH_OBJ);
					$bat = $stry->fetch();
					$jumlahkk = $bat->jumlah;

					#select setup kas kecil
					$stu = $owlPDO->query("select saldoberjalan from " . $dbname . ".keu_5kaskecil where unit='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
					$stu->setFetchMode(PDO::FETCH_OBJ);
					$baru = $stu->fetch();
					$saldoberjalan = $baru->saldoberjalan;

					#periksa apakah sudah tutup buku
					$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
					$stu->setFetchMode(PDO::FETCH_OBJ);
					$baru1 = $stu->fetch();
					if ($baru1->unit != '') {
						$comment2 .= "-" . $baru1->unit . " periode " . $baru1->periode . " has been closed\n";
					}

					#periksa apakah sudah close kas kecil
					$stu = $owlPDO->query("select * from " . $dbname . ".keu_5kaskecil where close=1 and unit='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
					$stu->setFetchMode(PDO::FETCH_OBJ);
					$baru2 = $stu->fetch();
					if ($baru2->unit != '') {
						$comment2 .= "- Petty Cash " . $baru2->unit . " periode " . $baru2->periode . " has been closed\n";
					}

					$str = "select noreferensi " . $dbname . ".keu_kaskecilht where notransaksi='" . $bsdlis . "' ";
					$stu->setFetchMode(PDO::FETCH_OBJ);
					$baru3 = $stu->fetch();
					if ($baru3->noreferensi != '') {
						$comment2 .= "- Transaksi ini sudah dilakukan pertanggungjawaban. Silahkan lakukan unpost top up transaksi ini terlebih dahulu. \n";
					}

					if ($comment2 != '') {
						// exit(" Error :".$comment2."\n process canceled");
						exit(" Error :" . $comment2 . "\n process dibatalkan");
					}

					$saldo = 0;
					$saldo = $saldoberjalan + $jumlahkk;
					$str = "update " . $dbname . ".keu_5kaskecil set saldoberjalan='" . $saldo . "' where unit='" . $bat->unit . "' and periode='" . substr($bat->tanggal, 0, 7) . "'";

					try {
						$owlPDO->exec($str);

						$str = "update " . $dbname . ".keu_kaskecilht set posting='0' where notransaksi='" . $bsdlis . "' ";
						try {
							$owlPDO->exec($str);
						} catch (PDOException $e) {
							echo " Gagal : " . addslashes($e->getMessage());
							die();
						}
					} catch (PDOException $e) {
						echo " Gagal : " . addslashes($e->getMessage());
						die();
					}
				}
				break;
			case '14':
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$sunit = "select distinct kodeorg,tanggal from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $bsdlis . "' and noakun like '128%'";
					$runit = fetchData($sunit);
					#cek periode akutansi
					$sCek = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $runit[0]['kodeorg'] . "' and tanggalmulai>='" . $runit[0]['tanggal'] . "' and tanggalsampai<='" . $runit[0]['tanggal'] . "' and tutupbuku=1";
					$rCek = fetchData($sCek);
					if (count($rCek) == 1) {
						exit('warning: ' . $_SESSION['lang']['unit'] . ' ' . $_SESSION['lang']['tutup']);
					}
					// $sDelAsset="delete from ".$dbname.".sdm_daftarasset where kodeproject='".$bsdlis."' and tanggalperolehan='".$_POST['tanggal'][$dtList]."'";
					$sDelAsset = "delete from " . $dbname . ".sdm_daftarasset where kodeproject='" . $bsdlis . "'";
					try {
						$owlPDO->exec($sDelAsset);
						$sdeljrn = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "' and nojurnal like '%PRJ%'";
						try {
							$owlPDO->exec($sdeljrn);
							$supdateproj = "update " . $dbname . ".project set posting=0 where kode='" . $bsdlis . "'";
							try {
								$owlPDO->exec($supdateproj);
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "<br/>";
								die();
							}
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>" . $sdeljrn;
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;

			case '15':
				$wrng = "";
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					try {
						$owlPDO->beginTransaction();

						$noafi = "";
						$nokud = "";
						## CEK TIPE TRANSAKSI
						if (strpos($bsdlis, 'TBSKUD') == true) {
							## CEK ADA AFLIASI ATAU TIDAK
							$str = "select notransaksi from " . $dbname . ".kebun_tbsafiliasi where noreferensi='" . $bsdlis . "' limit 1";
							$res = fetchdata($str);
							if (count($res) > 0) {
								$noafi = $res[0]['notransaksi'];
							}

							## CEK JURNAL
							$str = "select distinct kodeorg,tanggal,nojurnal from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $bsdlis . "'";
							$res = fetchData($str);
							$unit = $res[0]['kodeorg'];
							$tanggal = $res[0]['tanggal'];
							$nojurnal = $res[0]['nojurnal'];

							## CEK PERIODE AKUTANSI
							$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and tanggalmulai>='" . $tanggal . "' and tanggalsampai<='" . $tanggal . "' and tutupbuku=1";
							$res = fetchdata($str);
							if (count($res) > 0) {
								throw new PDOException($_SESSION['lang']['unit'] . ' ' . $_SESSION['lang']['tutup']);
							}

							## CEK TAGIHAN
							$str = "select noinvoice from " . $dbname . ".keu_tagihandt where notransaksi='" . $bsdlis . "' limit 1";
							$res = fetchdata($str);
							if (count($res) > 0) {
								throw new PDOException("Sudah ada tagihan dengan No. : " . $res[0]['noinvoice']);
							}

							if ($noafi != "") {
								## CEK JURNAL
								$str = "select distinct kodeorg,tanggal,nojurnal from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $noafi . "'";
								$res = fetchData($str);
								$unitafi = $res[0]['kodeorg'];
								$tanggalafi = $res[0]['tanggal'];
								$nojurnalafi = $res[0]['nojurnal'];

								## CEK PERIODE AKUTANSI
								$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unitafi . "' and tanggalmulai>='" . $tanggalafi . "' and tanggalsampai<='" . $tanggalafi . "' and tutupbuku=1";
								$res = fetchdata($str);
								if (count($res) > 0) {
									throw new PDOException($_SESSION['lang']['unit'] . ' ' . $_SESSION['lang']['tutup']);
								}

								## CEK PENAGIHAN
								$str = "select noinvoice from " . $dbname . ".keu_penagihandt where notransaksi='" . $noafi . "' limit 1";
								$res = fetchdata($str);
								if (count($res) > 0) {
									throw new PDOException("Sudah ada Penagihan dengan No. : " . $res[0]['noinvoice']);
								}

								# Get data dari approval untuk diinsert ke approval_return
								$getapv = "SELECT notransaksi, jenispersetujuan, 
                        level, karyawanid, 
                        status, komentar, 
                        keterangan, tanggal
                        FROM " . $dbname . ".approval
                        WHERE jenispersetujuan = 'PTBS'
                        AND notransaksi='" . $bsdlis . "'";
								$resapv = fetchData($getapv);

								# Insert ke approval_return
								foreach ($resapv as $key => $val) {
									$insapv = "INSERT INTO " . $dbname . ".approval_return VALUES (
                      '" . $val['notransaksi'] . "',
                      '" . $val['jenispersetujuan'] . "',
                      '" . $val['level'] . "',
                      '" . $val['karyawanid'] . "',
                      '" . $val['status'] . "',
                      '" . $val['komentar'] . "',
                      '" . $val['keterangan'] . "',
                      '" . $val['tanggal'] . "',
                      '1'
                    )";
									$owlPDO->exec($insapv);
								}

								#= delete persetujuan 
								$del = "delete from " . $dbname . ".approval where jenispersetujuan = 'PTBS' and notransaksi='" . $bsdlis . "'";
								try {
									$owlPDO->exec($del);
								} catch (PDOException $e) {
									echo " Gagal," . addslashes($e->getMessage());
								}

								## HAPUS JURNAL
								$str = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $nojurnalafi . "'";
								$owlPDO->exec($str);

								## UBAH FLAG Transaksi
								$str = "update " . $dbname . ".kebun_tbsafiliasi set posting='0',postingby='' where notransaksi='" . $noafi . "'";
								$owlPDO->exec($str);
							}

							# Get data dari approval untuk diinsert ke approval_return
							$getapv = "SELECT notransaksi, jenispersetujuan, 
                      level, karyawanid, 
                      status, komentar, 
                      keterangan, tanggal
                      FROM " . $dbname . ".approval
                      WHERE jenispersetujuan = 'PTBS'
                      AND notransaksi='" . $bsdlis . "'";
							$resapv = fetchData($getapv);

							# Insert ke approval_return
							foreach ($resapv as $key => $val) {
								$insapv = "INSERT INTO " . $dbname . ".approval_return VALUES (
                    '" . $val['notransaksi'] . "',
                    '" . $val['jenispersetujuan'] . "',
                    '" . $val['level'] . "',
                    '" . $val['karyawanid'] . "',
                    '" . $val['status'] . "',
                    '" . $val['komentar'] . "',
                    '" . $val['keterangan'] . "',
                    '" . $val['tanggal'] . "',
                    '1'
                  )";
								$owlPDO->exec($insapv);
							}

							#= delete persetujuan 
							$del = "delete from " . $dbname . ".approval where jenispersetujuan = 'PTBS' and notransaksi='" . $bsdlis . "'";
							try {
								$owlPDO->exec($del);
							} catch (PDOException $e) {
								echo " Gagal," . addslashes($e->getMessage());
							}

							## HAPUS JURNAL
							$str = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $nojurnal . "'";
							$owlPDO->exec($str);

							## UBAH FLAG Transaksi
							$str = "update " . $dbname . ".kebun_tbskud set posting='0',postingby='' where notransaksi='" . $bsdlis . "'";
							$owlPDO->exec($str);
						} else if (strpos($bsdlis, 'TBSAFI') == true) {
							## CEK ADA KUD ATAU TIDAK
							$str = "select noreferensi from " . $dbname . ".kebun_tbsafiliasi where notransaksi='" . $bsdlis . "' limit 1";
							$res = fetchdata($str);
							if (count($res) > 0) {
								$nokud = $res[0]['notransaksi'];
							}

							## CEK JURNAL
							$str = "select distinct kodeorg,tanggal,nojurnal from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $bsdlis . "'";
							$res = fetchData($str);
							$unit = $res[0]['kodeorg'];
							$tanggal = $res[0]['tanggal'];
							$nojurnal = $res[0]['nojurnal'];

							## CEK PERIODE AKUTANSI
							$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and tanggalmulai>='" . $tanggal . "' and tanggalsampai<='" . $tanggal . "' and tutupbuku=1";
							$res = fetchdata($str);
							if (count($res) > 0) {
								throw new PDOException($_SESSION['lang']['unit'] . ' ' . $_SESSION['lang']['tutup']);
							}

							## CEK PENAGIHAN
							$str = "select noinvoice from " . $dbname . ".keu_penagihandt where notransaksi='" . $bsdlis . "' limit 1";
							$res = fetchdata($str);
							if (count($res) > 0) {
								throw new PDOException("Sudah ada penagihan dengan No. : " . $res[0]['noinvoice']);
							}

							if ($nokud != "") {
								## CEK JURNAL
								$str = "select distinct kodeorg,tanggal,nojurnal from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $nokud . "'";
								$res = fetchData($str);
								$unitkud = $res[0]['kodeorg'];
								$tanggalkud = $res[0]['tanggal'];
								$nojurnalkud = $res[0]['nojurnal'];

								## CEK PERIODE AKUTANSI
								$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unitkud . "' and tanggalmulai>='" . $tanggalkud . "' and tanggalsampai<='" . $tanggalkud . "' and tutupbuku=1";
								$res = fetchdata($str);
								if (count($res) > 0) {
									throw new PDOException($_SESSION['lang']['unit'] . ' ' . $_SESSION['lang']['tutup']);
								}

								## CEK TAGIHAN
								$str = "select noinvoice from " . $dbname . ".keu_tagihandt where notransaksi='" . $nokud . "' limit 1";
								$res = fetchdata($str);
								if (count($res) > 0) {
									throw new PDOException("Sudah ada Tagihan dengan No. : " . $res[0]['noinvoice']);
								}

								# Get data dari approval untuk diinsert ke approval_return
								$getapv = "SELECT notransaksi, jenispersetujuan, 
                        level, karyawanid, 
                        status, komentar, 
                        keterangan, tanggal
                        FROM " . $dbname . ".approval
                        WHERE jenispersetujuan = 'PTBS'
                        AND notransaksi='" . $bsdlis . "'";
								$resapv = fetchData($getapv);

								# Insert ke approval_return
								foreach ($resapv as $key => $val) {
									$insapv = "INSERT INTO " . $dbname . ".approval_return VALUES (
                      '" . $val['notransaksi'] . "',
                      '" . $val['jenispersetujuan'] . "',
                      '" . $val['level'] . "',
                      '" . $val['karyawanid'] . "',
                      '" . $val['status'] . "',
                      '" . $val['komentar'] . "',
                      '" . $val['keterangan'] . "',
                      '" . $val['tanggal'] . "',
                      '1'
                    )";
									$owlPDO->exec($insapv);
								}

								#= delete persetujuan 
								$del = "delete from " . $dbname . ".approval where jenispersetujuan = 'PTBS' and notransaksi='" . $bsdlis . "'";
								$owlPDO->exec($del);

								## HAPUS JURNAL
								$str = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $nojurnalkud . "'";
								$owlPDO->exec($str);

								## UBAH FLAG Transaksi
								$str = "update " . $dbname . ".kebun_tbsafiliasi set posting='0',postingby='' where notransaksi='" . $nokud . "'";
								$owlPDO->exec($str);
							}

							# Get data dari approval untuk diinsert ke approval_return
							$getapv = "SELECT notransaksi, jenispersetujuan, 
                      level, karyawanid, 
                      status, komentar, 
                      keterangan, tanggal
                      FROM " . $dbname . ".approval
                      WHERE jenispersetujuan = 'PTBS'
                      AND notransaksi='" . $bsdlis . "'";
							$resapv = fetchData($getapv);

							# Insert ke approval_return
							foreach ($resapv as $key => $val) {
								$insapv = "INSERT INTO " . $dbname . ".approval_return VALUES (
                    '" . $val['notransaksi'] . "',
                    '" . $val['jenispersetujuan'] . "',
                    '" . $val['level'] . "',
                    '" . $val['karyawanid'] . "',
                    '" . $val['status'] . "',
                    '" . $val['komentar'] . "',
                    '" . $val['keterangan'] . "',
                    '" . $val['tanggal'] . "',
                    '1'
                  )";
								$owlPDO->exec($insapv);
							}

							#= delete persetujuan 
							$del = "delete from " . $dbname . ".approval where jenispersetujuan = 'PTBS' and notransaksi='" . $bsdlis . "'";
							$owlPDO->exec($del);

							## HAPUS JURNAL
							$str = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $nojurnal . "'";
							$owlPDO->exec($str);

							## UBAH FLAG Transaksi
							$str = "update " . $dbname . ".kebun_tbsafiliasi set posting='0',postingby='' where notransaksi='" . $bsdlis . "'";
							$owlPDO->exec($str);
						} else if (strpos($bsdlis, 'TBSEXT') == true) {
							## CEK JURNAL
							$str = "select distinct kodeorg,tanggal,nojurnal from " . $dbname . ".keu_jurnaldt_vw where noreferensi='" . $bsdlis . "'";
							$res = fetchData($str);
							$unit = $res[0]['kodeorg'];
							$tanggal = $res[0]['tanggal'];
							$nojurnal = $res[0]['nojurnal'];

							## CEK PERIODE AKUTANSI
							$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and tanggalmulai>='" . $tanggal . "' and tanggalsampai<='" . $tanggal . "' and tutupbuku=1";
							$res = fetchdata($str);
							if (count($res) > 0) {
								throw new PDOException($_SESSION['lang']['unit'] . ' ' . $_SESSION['lang']['tutup']);
							}

							## CEK TAGIHAN
							$str = "select noinvoice from " . $dbname . ".keu_tagihandt where notransaksi='" . $bsdlis . "' limit 1";
							$res = fetchdata($str);
							if (count($res) > 0) {
								throw new PDOException("Sudah ada tagihan dengan No. : " . $res[0]['noinvoice']);
							}

							# Get data dari approval untuk diinsert ke approval_return
							$getapv = "SELECT notransaksi, jenispersetujuan, 
                      level, karyawanid, 
                      status, komentar, 
                      keterangan, tanggal
                      FROM " . $dbname . ".approval
                      WHERE jenispersetujuan = 'PTBS'
                      AND notransaksi='" . $bsdlis . "'";
							$resapv = fetchData($getapv);

							# Insert ke approval_return
							foreach ($resapv as $key => $val) {
								$insapv = "INSERT INTO " . $dbname . ".approval_return VALUES (
                    '" . $val['notransaksi'] . "',
                    '" . $val['jenispersetujuan'] . "',
                    '" . $val['level'] . "',
                    '" . $val['karyawanid'] . "',
                    '" . $val['status'] . "',
                    '" . $val['komentar'] . "',
                    '" . $val['keterangan'] . "',
                    '" . $val['tanggal'] . "',
                    '1'
                  )";
								$owlPDO->exec($insapv);
							}

							#= delete persetujuan 
							$del = "delete from " . $dbname . ".approval where jenispersetujuan = 'PTBS' and notransaksi='" . $bsdlis . "'";
							$owlPDO->exec($del);


							## HAPUS JURNAL
							$str = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $nojurnal . "'";
							$owlPDO->exec($str);

							## UBAH FLAG Transaksi
							$str = "update " . $dbname . ".kebun_tbsexternal set posting='0',postingby='' where notransaksi='" . $bsdlis . "'";
							$owlPDO->exec($str);
						}

						// throw new PDOException("Next");
						$owlPDO->commit();
					} catch (PDOException $e) {
						$owlPDO->rollback();
						$wrng .= "\n" . $e;
						continue;
					}
				}
				if ($wrng != '') {
					exit("Warningsitem : " . $wrng);
				}
				break;
			/***********************************************************************
				 ** 16: Unposting PERGUDANGAN *******************************************
				 ***********************************************************************/
			case '16':
				try {
					$owlPDO->beginTransaction();
					# 1. Cek periode gudang
					# 2. Cek periode acct (jika RK cek juga lawannya)
					# 3. Cek sumber transaksi (BKM)
					# 4. Cek tipe transaksi
					# 1. Masuk : cek transaksi tanggal > dan kodebarang sama sudah post atau belum
					# 2. Pengembalian pengeluaran(retur) == exit
					# 3. Penerimaan mutasi
					# 4. Koreksi == exit
					# 5. Pengeluaran
					# 6. Pengembalian penerimaan == exit
					# 7. pengeluaran mutasi (cek tipe 3 apakah sudah diposting input dan di posting atau belum)

					# 5. Cek nomor jurnal (ambil rupiah jurnal per barang)
					# 6. Update log_5saldobulanan
					# 7. Update log_transaksidt
					# 8. Update log_transaksiht

					if (count($_POST['notransaksi']) == 0) {
						throw new PDOException("Data tidak ditemukan.\n process canceled");
					}
					foreach ($_POST['notransaksi'] as $key => $notransaksi) {
						$comment = '';
						$str = "select * from " . $dbname . ".log_transaksiht where notransaksi = '" . $notransaksi . "'";
						$res = fetchdata($str)[0];
						$tipetransaksi = $res['tipetransaksi'];
						$tanggal      = $res['tanggal'];
						$kodegudang   = $res['kodegudang'];
						$noref        = $res['notransaksireferensi'];
						$untukgudang  = $res['gudangx'];
						$kodeorg      = substr($kodegudang, 0, 4);
						$untukunit    = substr($untukgudang, 0, 4);
						$periode      = substr($tanggal, 0, 7);

						if ($tipetransaksi == '2' or $tipetransaksi == '6' or $tipetransaksi == '4') {
							throw new PDOException("Tipe transaksi ini tidak bisa di unposting.\n process canceled");
						}

						if ($tipetransaksi == '5' and $noref != '') {
							throw new PDOException("Silahkan unpost dari menu BKM.\n process canceled");
						}

						#periode gudang sendiri
						$str = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $kodegudang . "' and periode='" . $periode . "'";
						$res = fetchdata($str);
						if (count($res) > 0) {
							throw new PDOException("Periode gudang sudah ditutup.\n process canceled");
						}

						#periode gudang lawan
						$str = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $untukgudang . "' and periode='" . $periode . "'";
						$res = fetchdata($str);
						if ($tipetransaksi == '7' and count($res) > 0) {
							throw new PDOException("Periode gudang penerima sudah ditutup.\n process canceled");
						}

						#periode acct sendiri
						$str = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $kodeorg . "' and periode='" . $periode . "'";
						$res = fetchdata($str);
						if (count($res) > 0) {
							throw new PDOException("Periode accounting sudah ditutup.\n process canceled");
						}

						#periode acct lawan
						$str = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $untukunit . "' and periode='" . $periode . "'";
						$res = fetchdata($str);
						if ($tipetransaksi == '7' and count($res) > 0) {
							throw new PDOException("Periode accounting penerima sudah ditutup.\n process canceled");
						}
						switch ($tipetransaksi) {
							case '1':
								$qty = 0;
								$str = "select * from " . $dbname . ".log_transaksi_vw where notransaksi = '" . $notransaksi . "'";
								$res = fetchdata($str);
								foreach ($res as $bar) {
									$s = "select * from " . $dbname . ".log_transaksi_vw where kodebarang = '" . $bar['kodebarang'] . "' and tanggal>='" . $bar['tanggal'] . "' and kodegudang='" . $bar['kodegudang'] . "' and notransaksi!='" . $bar['notransaksi'] . "' and statusjurnal='1' and post='1'";
									$r = fetchdata($s);
									if (count($r) > 0) {
										throw new PDOException("Sudah ada transaksi untuk kode barang " . $r['kodebarang'] . " pada tanggal lebih besar dari " . $bar['tanggal'] . ".\n process canceled");
									}

									#jumlah barang
									$qty = $bar['jumlah'];

									$rp = 0;
									# ambil nilai barang dari jurnal
									$sx = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw where noreferensi ='" . $bar['notransaksi'] . "' and kodebarang='" . $bar['kodebarang'] . "' and noakun like '115%'";
									$rx = fetchdata($sx)[0];
									$rp = abs($rx['jumlah']);
									if (count($rx) == 0) {
										throw new PDOException("Nilai barang tidak ditemukan process canceled");
									}

									#ambil nilai saldo lama
									$sn = "select * from " . $dbname . ".log_5saldobulanan where kodeorg ='" . $bar['kodept'] . "' and kodebarang='" . $bar['kodebarang'] . "' and periode='" . substr($bar['tanggal'], 0, 7) . "' and kodegudang='" . $bar['kodegudang'] . "'";
									$rn = fetchdata($sn)[0];

									#$qtykeluar      =round($rn['qtykeluar']-$qty,5);
									#$qtykeluarxharga=round($rn['qtykeluarxharga']-$rp,5);
									$qtymasuk        = round($rn['qtymasuk'] - $qty, 5);
									$qtymasukxharga  = round($rn['qtymasukxharga'] - $rp, 5);
									$saldoakhirqty   = round($rn['saldoakhirqty'] - $qty, 5);
									$nilaisaldoakhir = round($rn['nilaisaldoakhir'] - $rp, 5);
									$hargarata       = round($nilaisaldoakhir / $saldoakhirqty, 5);

									#if($qtykeluar<0){throw new PDOException("Nilai qtykeluar salah, proses dibatalkan.");}
									#if($qtykeluarxharga<0){throw new PDOException("Nilai rupiah keluar salah, proses dibatalkan.");}
									if ($qtymasuk < 0) {
										throw new PDOException("Nilai qtymasuk salah, proses dibatalkan.");
									}
									if ($qtymasukxharga < 0) {
										throw new PDOException("Nilai rupiah masuk salah, proses dibatalkan.");
									}
									if ($saldoakhirqty < 0) {
										throw new PDOException("Nilai saldo akhir salah, proses dibatalkan.");
									}
									if ($nilaisaldoakhir < 0) {
										throw new PDOException("Nilai rupiah saldo akhir salah, proses dibatalkan.");
									}
									if ($hargarata < 0) {
										throw new PDOException("Nilai harga rata - rata salah, proses dibatalkan.");
									}
									if ($nilaisaldoakhir > 0 and $hargarata == 0) {
										throw new PDOException("Nilai harga rata - rata salah, proses dibatalkan (2).");
									}


									$data = array(
										'qtymasuk'       => $qtymasuk,
										'qtymasukxharga' => $qtymasukxharga,
										'saldoakhirqty'  => $saldoakhirqty,
										'nilaisaldoakhir' => $nilaisaldoakhir,
										'hargarata'      => $hargarata
									);
									$where = "kodeorg ='" . $bar['kodept'] . "' and kodebarang='" . $bar['kodebarang'] . "' and periode='" . substr($bar['tanggal'], 0, 7) . "' and kodegudang='" . $bar['kodegudang'] . "'";
									#update log_5saldobulanan
									$query = updateQuery($dbname, 'log_5saldobulanan', $data, $where);
									$owlPDO->exec($query);
								}
								# del jurnal
								$query = "delete from " . $dbname . ".keu_jurnalht where noreferensi ='" . $notransaksi . "'";
								$owlPDO->exec($query);

								# update transaksi dt
								$query = "update " . $dbname . ".log_transaksidt set statussaldo='0' where notransaksi='" . $notransaksi . "'";
								$owlPDO->exec($query);

								# update transaksi ht
								$query = "update " . $dbname . ".log_transaksiht set statusjurnal='0', post='0', postedby='' where notransaksi='" . $notransaksi . "'";
								$owlPDO->exec($query);

								break;
							case '3':
								$r = "select * from " . $dbname . ".log_transaksi_vw where notransaksi = '" . $notransaksi . "'";
								$n = fetchdata($r);
								foreach ($n as $e) {
									$qty = 0;
									$str = "select * from " . $dbname . ".log_transaksi_vw where notransaksireferensi = '" . $e['notransaksireferensi'] . "'";
									$res = fetchdata($str);
									foreach ($res as $bar) {
										$s = "select * from " . $dbname . ".log_transaksi_vw where kodebarang = '" . $bar['kodebarang'] . "' and tanggal>='" . $bar['tanggal'] . "' and kodegudang='" . $bar['kodegudang'] . "' and notransaksi!='" . $bar['notransaksi'] . "' and statusjurnal='1' and post='1'";
										$r = fetchdata($s);
										if (count($r) > 0) {
											throw new PDOException("Sudah ada transaksi untuk kode barang " . $r['kodebarang'] . " pada tanggal lebih besar dari " . $bar['tanggal'] . ".\n process canceled");
										}

										#jumlah barang
										$qty = $bar['jumlah'];

										$rp = 0;
										# ambil nilai barang dari jurnal
										$sx = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw where noreferensi ='" . $bar['notransaksi'] . "' and kodebarang='" . $bar['kodebarang'] . "' and noakun like '115%'";
										$rx = fetchdata($sx)[0];
										$rp = abs($rx['jumlah']);
										if ($rp > 0) {
											#ada jurnal
											$adajurnal = true;
										} else {
											#tidak ada jurnal
											$rp = $bar['jumlah'] * $bar['hargarata'];
											$adajurnal = false;
										}

										#ambil nilai saldo lama
										$sn = "select * from " . $dbname . ".log_5saldobulanan where kodeorg ='" . $bar['kodept'] . "' and kodebarang='" . $bar['kodebarang'] . "' and periode='" . substr($bar['tanggal'], 0, 7) . "' and kodegudang='" . $bar['kodegudang'] . "'";
										$rn = fetchdata($sn)[0];

										#$qtykeluar      =round($rn['qtykeluar']-$qty,5);
										#$qtykeluarxharga=round($rn['qtykeluarxharga']-$rp,5);
										$qtymasuk        = round($rn['qtymasuk'] - $qty, 5);
										$qtymasukxharga  = round($rn['qtymasukxharga'] - $rp, 5);
										$saldoakhirqty   = round($rn['saldoakhirqty'] - $qty, 5);
										$nilaisaldoakhir = round($rn['nilaisaldoakhir'] - $rp, 5);
										$hargarata       = round($nilaisaldoakhir / $saldoakhirqty, 5);
										if ($hargarata == 0) {
											$hargarata = $rn['hargarata'];
										}

										#if($qtykeluar<0){throw new PDOException("Nilai qtykeluar salah, proses dibatalkan.");}
										#if($qtykeluarxharga<0){throw new PDOException("Nilai rupiah keluar salah, proses dibatalkan.");}
										if ($qtymasuk < 0) {
											throw new PDOException("Nilai qtymasuk salah, proses dibatalkan.");
										}
										if ($qtymasukxharga < 0) {
											throw new PDOException("Nilai rupiah masuk salah, proses dibatalkan.");
										}
										if ($saldoakhirqty < 0) {
											throw new PDOException("Nilai saldo akhir salah, proses dibatalkan.");
										}
										if ($nilaisaldoakhir < 0) {
											throw new PDOException("Nilai rupiah saldo akhir salah, proses dibatalkan.");
										}
										if ($hargarata < 0) {
											throw new PDOException("Nilai harga rata - rata salah, proses dibatalkan.");
										}
										if ($nilaisaldoakhir > 0 and $hargarata == 0) {
											throw new PDOException("Nilai harga rata - rata salah, proses dibatalkan (2).");
										}

										$data = array(
											'qtymasuk'       => $qtymasuk,
											'qtymasukxharga' => $qtymasukxharga,
											'saldoakhirqty'  => $saldoakhirqty,
											'nilaisaldoakhir' => $nilaisaldoakhir,
											'hargarata'      => $hargarata
										);
										$where = "kodeorg ='" . $bar['kodept'] . "' and kodebarang='" . $bar['kodebarang'] . "' and periode='" . substr($bar['tanggal'], 0, 7) . "' and kodegudang='" . $bar['kodegudang'] . "'";
										#update log_5saldobulanan
										$query = updateQuery($dbname, 'log_5saldobulanan', $data, $where);
										$owlPDO->exec($query);


										# del jurnal
										if ($adajurnal == true) {
											$query = "delete from " . $dbname . ".keu_jurnalht where noreferensi ='" . $bar['notransaksi'] . "'";
											$owlPDO->exec($query);
										}

										# update transaksi dt
										$query = "update " . $dbname . ".log_transaksidt set statussaldo='0' where notransaksi='" . $bar['notransaksi'] . "'";
										$owlPDO->exec($query);

										# update transaksi ht
										$query = "update " . $dbname . ".log_transaksiht set statusjurnal='0', post='0', postedby='' where notransaksi='" . $bar['notransaksi'] . "'";
										$owlPDO->exec($query);
									}
								}
								break;
							case '5':
								$qty = 0;
								$str = "select * from " . $dbname . ".log_transaksi_vw where notransaksi = '" . $notransaksi . "'";
								$res = fetchdata($str);
								foreach ($res as $bar) {
									$s = "select * from " . $dbname . ".log_transaksi_vw where kodebarang = '" . $bar['kodebarang'] . "' and tanggal>='" . $bar['tanggal'] . "' and kodegudang='" . $bar['kodegudang'] . "' and notransaksi!='" . $bar['notransaksi'] . "' and statusjurnal='1' and post='1'";
									$r = fetchdata($s);
									if (count($r) > 0) {
										throw new PDOException("Sudah ada transaksi untuk kode barang " . $r['kodebarang'] . " pada tanggal lebih besar dari " . $bar['tanggal'] . ".\n process canceled");
									}

									#jumlah barang
									$qty = $bar['jumlah'];

									$rp = 0;
									# ambil nilai barang dari jurnal
									$sx = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw where noreferensi ='" . $bar['notransaksi'] . "' and kodebarang='" . $bar['kodebarang'] . "' and noakun like '115%'";
									$rx = fetchdata($sx)[0];
									if (count($rx) == 0) {
										throw new PDOException("Nilai barang tidak ditemukan process canceled");
									}
									$rp = abs($rx['jumlah']);

									#ambil nilai saldo lama
									$sn = "select * from " . $dbname . ".log_5saldobulanan where kodeorg ='" . $bar['kodept'] . "' and kodebarang='" . $bar['kodebarang'] . "' and periode='" . substr($bar['tanggal'], 0, 7) . "' and kodegudang='" . $bar['kodegudang'] . "'";
									$rn = fetchdata($sn)[0];

									$qtykeluar      = round($rn['qtykeluar'] - $qty, 5);
									$qtykeluarxharga = round($rn['qtykeluarxharga'] - $rp, 5);
									#$qtymasuk      =round($rn['qtymasuk']-$qty,5);
									#$qtymasukxharga=round($rn['qtymasukxharga']-$rp,5);
									$saldoakhirqty  = round($rn['saldoakhirqty'] + $qty, 5);
									$nilaisaldoakhir = round($rn['nilaisaldoakhir'] + $rp, 5);
									$hargarata      = round($nilaisaldoakhir / $saldoakhirqty, 5);

									if ($qtykeluar < 0) {
										throw new PDOException("Nilai qtykeluar salah, proses dibatalkan.");
									}
									if ($qtykeluarxharga < 0) {
										throw new PDOException("Nilai rupiah keluar salah, proses dibatalkan.");
									}
									#if($qtymasuk<0){throw new PDOException("Nilai qtymasuk salah, proses dibatalkan.");}
									#if($qtymasukxharga<0){throw new PDOException("Nilai rupiah masuk salah, proses dibatalkan.");}
									if ($saldoakhirqty < 0) {
										throw new PDOException("Nilai saldo akhir salah, proses dibatalkan.");
									}
									if ($nilaisaldoakhir < 0) {
										throw new PDOException("Nilai rupiah saldo akhir salah, proses dibatalkan.");
									}
									if ($hargarata < 0) {
										throw new PDOException("Nilai harga rata - rata salah, proses dibatalkan.");
									}
									if ($nilaisaldoakhir > 0 and $hargarata == 0) {
										throw new PDOException("Nilai harga rata - rata salah, proses dibatalkan (2).");
									}

									$data = array(
										'qtykeluar'      => $qtykeluar,
										'qtykeluarxharga' => $qtykeluarxharga,
										'saldoakhirqty'  => $saldoakhirqty,
										'nilaisaldoakhir' => $nilaisaldoakhir,
										'hargarata'      => $hargarata
									);
									$where = "kodeorg ='" . $bar['kodept'] . "' and kodebarang='" . $bar['kodebarang'] . "' and periode='" . substr($bar['tanggal'], 0, 7) . "' and kodegudang='" . $bar['kodegudang'] . "'";
									#update log_5saldobulanan
									$query = updateQuery($dbname, 'log_5saldobulanan', $data, $where);
									$owlPDO->exec($query);
								}
								# del jurnal
								$query = "delete from " . $dbname . ".keu_jurnalht where noreferensi ='" . $notransaksi . "'";
								$owlPDO->exec($query);

								# update transaksi dt
								$query = "update " . $dbname . ".log_transaksidt set statussaldo='0' where notransaksi='" . $notransaksi . "'";
								$owlPDO->exec($query);

								# update transaksi ht
								$query = "update " . $dbname . ".log_transaksiht set statusjurnal='0', post='0' where notransaksi='" . $notransaksi . "'";
								$owlPDO->exec($query);

								break;
							case '7':
								$str = "select * from " . $dbname . ".log_transaksi_vw where notransaksireferensi = '" . $notransaksi . "'";
								$res = fetchdata($str);
								$comment = "";
								foreach ($res as $bar) {
									$no++;
									$comment .= "\n " . $no . ". " . $bar['notransaksi'];
								}
								if ($comment != '') {
									throw new PDOException("Sudah ada transaksi penerimaan, silahkan unposting dan hapus penerimaan terlebih dahulu : " . $comment . "\n process canceled");
								}

								$qty = 0;
								$str = "select * from " . $dbname . ".log_transaksi_vw where notransaksi = '" . $notransaksi . "'";
								$res = fetchdata($str);
								foreach ($res as $bar) {
									$s = "select * from " . $dbname . ".log_transaksi_vw where kodebarang = '" . $bar['kodebarang'] . "' and tanggal>='" . $bar['tanggal'] . "' and kodegudang='" . $bar['kodegudang'] . "' and notransaksi!='" . $bar['notransaksi'] . "' and statusjurnal='1' and post='1'";
									$r = fetchdata($s);
									if (count($r) > 0) {
										throw new PDOException("Sudah ada transaksi untuk kode barang " . $r['kodebarang'] . " pada tanggal lebih besar dari " . $bar['tanggal'] . ".\n process canceled");
									}

									#jumlah barang
									$qty = $bar['jumlah'];

									$rp = 0;
									# ambil nilai barang dari jurnal
									$sx = "select sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw where noreferensi ='" . $bar['notransaksi'] . "' and kodebarang='" . $bar['kodebarang'] . "' and noakun like '115%'";
									$rx = fetchdata($sx)[0];
									$rp = abs($rx['jumlah']);
									if ($rp > 0) {
										#ada jurnal
										$adajurnal = true;
									} else {
										#tidak ada jurnal
										$rp = $bar['jumlah'] * $bar['hargarata'];
										$adajurnal = false;
									}

									#ambil nilai saldo lama
									$sn = "select * from " . $dbname . ".log_5saldobulanan where kodeorg ='" . $bar['kodept'] . "' and kodebarang='" . $bar['kodebarang'] . "' and periode='" . substr($bar['tanggal'], 0, 7) . "' and kodegudang='" . $bar['kodegudang'] . "'";
									$rn = fetchdata($sn)[0];

									$qtykeluar      = round($rn['qtykeluar'] - $qty, 5);
									$qtykeluarxharga = round($rn['qtykeluarxharga'] - $rp, 5);
									#$qtymasuk      =round($rn['qtymasuk']-$qty,5);
									#$qtymasukxharga=round($rn['qtymasukxharga']-$rp,5);
									$saldoakhirqty  = round($rn['saldoakhirqty'] + $qty, 5);
									$nilaisaldoakhir = round($rn['nilaisaldoakhir'] + $rp, 5);
									$hargarata      = round($nilaisaldoakhir / $saldoakhirqty, 5);

									if ($qtykeluar < 0) {
										throw new PDOException("Nilai qtykeluar salah, proses dibatalkan.");
									}
									if ($qtykeluarxharga < 0) {
										throw new PDOException("Nilai rupiah keluar salah, proses dibatalkan.");
									}
									#if($qtymasuk<0){throw new PDOException("Nilai qtymasuk salah, proses dibatalkan.");}
									#if($qtymasukxharga<0){throw new PDOException("Nilai rupiah masuk salah, proses dibatalkan.");}
									if ($saldoakhirqty < 0) {
										throw new PDOException("Nilai saldo akhir salah, proses dibatalkan.");
									}
									if ($nilaisaldoakhir < 0) {
										throw new PDOException("Nilai rupiah saldo akhir salah, proses dibatalkan.");
									}
									if ($hargarata < 0) {
										throw new PDOException("Nilai harga rata - rata salah, proses dibatalkan.");
									}
									if ($nilaisaldoakhir > 0 and $hargarata == 0) {
										throw new PDOException("Nilai harga rata - rata salah, proses dibatalkan (2).");
									}

									$data = array(
										'qtykeluar'      => $qtykeluar,
										'qtykeluarxharga' => $qtykeluarxharga,
										'saldoakhirqty'  => $saldoakhirqty,
										'nilaisaldoakhir' => $nilaisaldoakhir,
										'hargarata'      => $hargarata
									);
									$where = "kodeorg ='" . $bar['kodept'] . "' and kodebarang='" . $bar['kodebarang'] . "' and periode='" . substr($bar['tanggal'], 0, 7) . "' and kodegudang='" . $bar['kodegudang'] . "'";
									#update log_5saldobulanan
									$query = updateQuery($dbname, 'log_5saldobulanan', $data, $where);
									$owlPDO->exec($query);
								}
								# del jurnal
								if ($adajurnal == true) {
									$query = "delete from " . $dbname . ".keu_jurnalht where noreferensi ='" . $notransaksi . "'";
									$owlPDO->exec($query);
								}

								# update transaksi dt
								$query = "update " . $dbname . ".log_transaksidt set statussaldo='0' where notransaksi='" . $notransaksi . "'";
								$owlPDO->exec($query);

								# update transaksi ht
								$query = "update " . $dbname . ".log_transaksiht set statusjurnal='0', post='0' where notransaksi='" . $notransaksi . "'";
								$owlPDO->exec($query);

								break;

							default;
								throw new PDOException("Process Canceled");
								break;
						}
					}

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Error, " . addslashes($e->getMessage());
					die();
				}
				break;

			default:
				break;
		}

	case '17':
		$wrng = "";
		foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
			try {
				$owlPDO->beginTransaction();

				# Get data dari approval untuk diinsert ke approval_return
				$getapv = "SELECT notransaksi, jenispersetujuan, 
                            level, karyawanid, 
                            status, komentar, 
                            keterangan, tanggal
                            FROM " . $dbname . ".approval
                            WHERE jenispersetujuan = 'FTBS'
                            AND notransaksi='" . $bsdlis . "'";
				$resapv = fetchData($getapv);

				# Insert ke approval_return
				foreach ($resapv as $key => $val) {
					$insapv = "INSERT INTO " . $dbname . ".approval_return VALUES (
                                  '" . $val['notransaksi'] . "',
                                  '" . $val['jenispersetujuan'] . "',
                                  '" . $val['level'] . "',
                                  '" . $val['karyawanid'] . "',
                                  '" . $val['status'] . "',
                                  '" . $val['komentar'] . "',
                                  '" . $val['keterangan'] . "',
                                  '" . $val['tanggal'] . "',
                                  '1')";
					try {
						$owlPDO->exec($insapv);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						die();
					}
				}

				#= delete persetujuan 
				$del = "delete from " . $dbname . ".approval where jenispersetujuan = 'FTBS' and notransaksi='" . $bsdlis . "'";
				try {
					$owlPDO->exec($del);
				} catch (PDOException $e) {
					echo " Gagal," . addslashes($e->getMessage());
				}

				## HAPUS JURNAL
				$str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $bsdlis . "'";
				$owlPDO->exec($str);

				$str = "delete from " . $dbname . ".keu_jurnaldt where noreferensi='" . $bsdlis . "'";
				$owlPDO->exec($str);

				## UBAH FLAG Transaksi
				$str = "update " . $dbname . ".pmn_feetbs set posting='0', postingby='' where notransaksi='" . $bsdlis . "'";
				$owlPDO->exec($str);

				$owlPDO->commit();
			} catch (PDOException $e) {
				$owlPDO->rollback();
				$wrng .= "\n" . $e;
				continue;
			}
		}
		break;
		break;

	case 'delData':
		$sDel = "delete from " . $dbname . ".setup_franco where id_franco='" . $idFranco . "'";
		try {
			$owlPDO->exec($sDel);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
	// case'getData':
	// $sDt=$owlPDO->query("select * from ".$dbname.".setup_franco where id_franco='".$idFranco."'");
	// $sDt->setFetchMode(PDO::FETCH_ASSOC);
	// $rDet=$sDt->fetch();
	// echo $rDet['id_franco']."###".$rDet['franco_name']."###".$rDet['alamat']."###".$rDet['contact']."###".$rDet['handphone']."###".$rDet['status'];
	// break;
	case 'getBlok':
		//           $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unitId."%' and length(kodeorganisasi)=10 order by kodeorganisasi";
		$str = $owlPDO->query("select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi like '" . $unitId . "%' and tipe = 'BLOK' order by kodeorganisasi");
		$str->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $str->fetch()) {
			$opt .= "<option value='" . $bar->kodeorganisasi . "'>" . $bar->kodeorganisasi . " " . $bar->namaorganisasi . "</option>";
		}
		echo $opt;
		break;
	case 'getPeriodeOClose':
		$str = $owlPDO->query("select periode from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $_POST['unit'] . "' order by periode desc");
		$str->setFetchMode(PDO::FETCH_OBJ);
		$z = 0;
		$last = '';
		while ($bar = $str->fetch()) {
			if ($z == 0) {
				#penambah periode             
				$last = $bar->periode;
				for ($u = 10; $u >= 1; $u--) {
					$st = mktime(0, 0, 0, intval(substr($last, 5, 2)) + $u, 15, intval(substr($last, 0, 4)));
					$stream .= "<option value='" . date('Y-m', $st) . "'>" . date('Y-m', $st) . "</option>";
				}
			}
			$stream .= "<option value='" . $bar->periode . "'>" . $bar->periode . "</option>";
			$z++;
		}

		echo "<select id=dariperiode>" . $stream . "</select> to <select id=sampaiperiode>" . $stream . "</select>";
		break;

	case 'getPeriodebank':
		$z = 0;
		$last = '';
		$str = "select distinct periode from " . $dbname . ".keu_saldobank where kodeorg='" . $_POST['unit'] . "' order by periode desc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$thn = substr($bar['periode'], 0, 4);
			$bln = substr($bar['periode'], 4, 2);
			$stream .= "<option value='" . $thn . "-" . $bln . "'>" . $thn . "-" . $bln . "</option>";
		}
		echo "<select id=dariperiodebank>" . $stream . "</select> to <select id=sampaiperiodebank>" . $stream . "</select>";
		break;

	case 'openCloseMethodBank':

		#periksa apakah ada periode terkecil
		$str = $owlPDO->query("select periode from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $_POST['unitopenclose'] . "' and tutupbuku=0 order by periode desc limit 1");
		$str->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $str->fetch();
		$numrows = owlBaris($str);
		if ($_POST['dariperiode'] < $bar['periode']) {
			echo "Error : Periode aktif akuntansi yaitu " . $bar['periode'] . ". Silahkan unclose periode akuntansi " . $_POST['dariperiode'] . " terlebih dahulu.";
		} else {

			$_POST['dariperiode'] = str_replace('-', '', $_POST['dariperiode']);
			$_POST['sampaiperiode'] = str_replace('-', '', $_POST['sampaiperiode']);
			$str = " delete from  " . $dbname . ".keu_saldobank where kodeorg='" . $_POST['unitopenclose'] . "' and periode>'" . $_POST['dariperiode'] . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
		}

		break;






















	case 'openCloseMethod':
		if ($_POST['tipe'] == 'OPEN') {
			#periksa apakah ada periode terkecil
			$str = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where periode='" . $_POST['dariperiode'] . "'
                      and kodeorg='" . $_POST['unitopenclose'] . "'");
			$str->setFetchMode(PDO::FETCH_OBJ);
			$numrows = owlBaris($str);
			if ($numrows < 1) {
				echo "Error : Periode terkecil tersebut belum terdaftar pada periode akuntansi";
			} else {
				#1 task hapus jurnal CLS
				$str = "delete from " . $dbname . ".keu_jurnalht where nojurnal like '%/" . $_POST['unitopenclose'] . "/CLS%'
                          and left(tanggal,7)>='" . $_POST['dariperiode'] . "' and  left(tanggal,7)<='" . $_POST['sampaiperiode'] . "'";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "<br/>";
					die();
				}
				$str = " update " . $dbname . ".setup_periodeakuntansi set tutupbuku=0 where kodeorg='" . $_POST['unitopenclose'] . "'
                           and periode='" . $_POST['dariperiode'] . "'";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "<br/>";
					die();
				}
				$str = " delete from  " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $_POST['unitopenclose'] . "'
                           and periode>'" . $_POST['dariperiode'] . "'";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "<br/>";
					die();
				}
			}
		}
		if ($_POST['tipe'] == 'CLOSE') {
			#periksa periode terakhir dari unit ybs
			$curperiode = '';
			$str = $owlPDO->query("select periode from " . $dbname . ".setup_periodeakuntansi where tutupbuku=0 
                     and kodeorg='" . $_POST['unitopenclose'] . "' order by periode desc limit 1");
			$str->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $str->fetch()) {
				$curperiode = $bar->periode;
			}
			if ($curperiode == $_POST['dariperiode']) {
				#mengubah session, kemudian pada response terakhir akan dibawa logout
				$_SESSION['empl']['lokasitugas'] = $_POST['unitopenclose'];
				#================================================== 
				$zz = $_POST['dariperiode'];
				$list = $_POST['dariperiode'];
				while ($zz < $_POST['sampaiperiode']) {
					$st = mktime(0, 0, 0, intval(substr($zz, 5, 2)) + 1, 15, intval(substr($_POST['dariperiode'], 0, 4)));
					$zz = date('Y-m', $st);
					$list .= "#" . $zz;
				}
				echo $list;
			} else {
				echo " Error: Periode terakhir tidak sama dengan periode awal yang dipilih, mohon diperiksa kembali";
			}
		}
		break;
	case 'getData2':
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
		$tab .= "<tr><td>" . $_SESSION['lang']['notransaksi'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['nojurnal'] . "</td>";
		$tab .= "<td>" . $_SESSION['lang']['tanggal'] . "</td>";
		$tab .= "<td><input type=checkbox id=allCheck2 onclick=checkAll2() /></td></tr></thead><tbody id=dataIsi2>";
		$sData = $owlPDO->query("select distinct * from " . $dbname . ".keu_jurnalht where noreferensi in (" . $notrans2 . ")");
		$sData->setFetchMode(PDO::FETCH_ASSOC);
		while ($rData = $sData->fetch()) {
			$nor++;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td id=notransaks_" . $nor . ">" . $rData['noreferensi'] . "</td>";
			$tab .= "<td id=nojurnal_" . $nor . ">" . $rData['nojurnal'] . "</td>";
			$tab .= "<td id=tgl_" . $nor . ">" . $rData['tanggal'] . "</td>";
			$tab .= "<td><input type=checkbox id=trans_" . $nor . " /></td>";
			$tab .= "</tr>";
		}
		$tab .= "</tbody></table>";
		$tab .= "<button class=mybutton id=tmblDt onclick=unpostingGudang()>Unposting</button>";
		echo $tab;
		break;
	case 'unpostingGudang':
		#================================
		//unposting gudang dilarang
		echo "Gagal: Maaf, unposting gudang tidak dapat digunakan, menjaga konsistensi harga rata2";
		exit();
		#================================
		switch ($pilUn_5) {
			case '1':
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {

					$tgl = substr($_POST['tanggal'][$dtList], 0, 7);
					$unit = substr($bsdlis, -6, 6);
					//exit("error:".$unit);
					$scek = $owlPDO->query("select distinct tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $tgl . "' and kodeorg='" . $unit . "'");
					$scek->setFetchMode(PDO::FETCH_ASSOC);
					$rcek = $scek->fetch();
					if ($rcek['tutupbuku'] == 1) {
						exit("error:periode akuntansi sudah di tutup");
					}

					$sBrg = $owlPDO->query("select distinct kodebarang,notransaksi,jumlah from " . $dbname . ".log_transaksidt where notransaksi='" . $bsdlis . "' and statussaldo=1");
					$sBrg->setFetchMode(PDO::FETCH_ASSOC);
					while ($rBrg =  $sBrg->fetch()) {

						$supd = "update " . $dbname . ".log_5saldobulanan set saldoakhirqty=(saldoakhirqty-" . $rBrg['jumlah'] . "),
                                   nilaisaldoakhir=(saldoakhirqty-" . $rBrg['jumlah'] . ")*hargarata,qtymasuk=(qtymasuk-" . $rBrg['jumlah'] . "),
                                   qtymasukxharga=(qtymasuk-" . $rBrg['jumlah'] . ")*hargarata where periode='" . $tgl . "' and kodegudang='" . $unit . "'
                                   and kodebarang='" . $rBrg['kodebarang'] . "'";
						try {
							$owlPDO->exec($supd);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}

						$supd = "update " . $dbname . ".log_5masterbarangdt set saldoqty=saldoqty-" . $rBrg['jumlah'] . "
                                   where kodegudang='" . $unit . "' and kodebarang='" . $rBrg['kodebarang'] . "'";
						try {
							$owlPDO->exec($supd);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					}
					$supd = "update " . $dbname . ".log_transaksidt set statussaldo=0
                               where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($supd);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
					$supd2 = "update " . $dbname . ".log_transaksiht set post=0,statusjurnal=0
                               where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($supd2);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}

					$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "%'";
					try {
						$owlPDO->exec($sDel);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;
			case '5':
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment1 = '';
					#periksa gudang apakah sudah tutup buku
					$strt = $owlPDO->query("select kodegudang,tanggal from " . $dbname . ".log_transaksiht where notransaksireferensi='" . $bsdlis . "'");
					$strt->setFetchMode(PDO::FETC_OBJ);
					while ($bat = $strt->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='" . $bat->kodegudang . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETC_OBJ);
						while ($baru = $stu->fetch()) {
							$comment1 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " has been closed\n";
						}
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='" . substr($bat->kodegudang, 0, 4) . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETC_OBJ);
						while ($baru = $stu->fetch()) {
							$comment1 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " has been closed\n";
						}
					}
					$str = $owlPDO->query("select nojurnal,tanggal from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "'");
					$str->setFetchMode(PDO::FETC_OBJ);
					while ($bar = $str->fetch()) {
						$unit = explode("/", $bar->nojurnal);
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $unit[1] . "' and periode='" . substr($bar->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETC_OBJ);
						while ($baru = $stu->fetch()) {
							$comment1 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " has been closed\n";
						}
					}
					if ($comment1 != '') {
						exit(" Error :" . $comment1 . "\n process canceled");
					}

					$tgl = substr($_POST['tanggal'][$dtList], 0, 7);
					$unit = substr($bsdlis, -6, 6);
					//exit("error:".$unit);
					$scek = $owlPDO->query("select distinct tutupbuku from " . $dbname . ".setup_periodeakuntansi  where periode='" . $tgl . "' and kodeorg='" . $unit . "'");
					$scek->setFetchMode(PDO::FETC_ASSOC);
					$rcek = $scek->fetch();
					if ($rcek['tutupbuku'] == 1) {
						exit("error:periode akuntansi sudah di tutup");
					}

					$sBrg = $owlPDO->query("select distinct kodebarang,notransaksi,jumlah from " . $dbname . ".log_transaksidt where notransaksi='" . $bsdlis . "' and statussaldo=1");
					$sBrg->setFetchMode(PDO::FETC_ASSOC);
					while ($rBrg = $sBrg->fetch()) {

						$supd = "update " . $dbname . ".log_5saldobulanan set saldoakhirqty=(saldoakhirqty+" . $rBrg['jumlah'] . "),
                                   nilaisaldoakhir=(saldoakhirqty+" . $rBrg['jumlah'] . ")*hargarata,qtykeluar=(qtykeluar-" . $rBrg['jumlah'] . "),
                                   qtykeluarxharga=(qtykeluar-" . $rBrg['jumlah'] . ")*hargarata where periode='" . $tgl . "' and kodegudang='" . $unit . "'
                                   and kodebarang='" . $rBrg['kodebarang'] . "'";
						try {
							$owlPDO->exec($supd);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
						$supd = "update " . $dbname . ".log_5masterbarangdt set saldoqty=saldoqty+" . $rBrg['jumlah'] . "
                                   where kodegudang='" . $unit . "' and kodebarang='" . $rBrg['kodebarang'] . "'";
						try {
							$owlPDO->exec($supd);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					}
					$supd = "update " . $dbname . ".log_transaksidt set statussaldo=0
                               where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($supd);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
					$supd2 = "update " . $dbname . ".log_transaksiht set post=0,statusjurnal=0
                               where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($supd2);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}

					$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "%'";
					try {
						$owlPDO->exec($sDel);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;
			case '3':
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment1 = '';
					#periksa gudang apakah sudah tutup buku
					$strt = $owlPDO->query("select kodegudang,tanggal from " . $dbname . ".log_transaksiht where notransaksi='" . $bsdlis . "'");
					$strt->setFetchMode(PDO::FETCH_OBJ);
					while ($bat = $strt->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='" . $bat->kodegudang . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
							$comment1 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " sudah closing\n";
						}
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='" . substr($bat->kodegudang, 0, 4) . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							$comment1 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " has been closed\n";
							$comment1 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " sudah closing\n";
						}
					}
					if ($comment1 != '') {
						exit(" Error :" . $comment1 . "\n process canceled");
						exit(" Error :" . $comment1 . "\n proces dibatalkan");
					}

					$tgl = substr($_POST['tanggal'][$dtList], 0, 7);
					$unit = substr($bsdlis, -6, 6);
					//exit("error:".$unit);
					$scek = $owlPDO->query("select distinct tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $tgl . "' and kodeorg='" . $unit . "'");
					$stu->setFetchMode(PDO::FETCH_ASSOC);
					$rcek = $stu->fetch();
					if ($rcek['tutupbuku'] == 1) {
						exit("error:periode akuntansi sudah di tutup");
					}

					$sBrg = $owlPDO->query("select distinct kodebarang,notransaksi,jumlah from " . $dbname . ".log_transaksidt where notransaksi='" . $bsdlis . "' and statussaldo=1");
					$sBrg->setFetchMode(PDO::FETCH_ASSOC);
					while ($rBrg = $sBrg->fetch()) {

						$supd = "update " . $dbname . ".log_5saldobulanan set saldoakhirqty=(saldoakhirqty-" . $rBrg['jumlah'] . "),
                                   nilaisaldoakhir=(saldoakhirqty-" . $rBrg['jumlah'] . ")*hargarata,qtymasuk=(qtymasuk-" . $rBrg['jumlah'] . "),
                                   qtymasukxharga=(qtymasuk-" . $rBrg['jumlah'] . ")*hargarata where periode='" . $tgl . "' and kodegudang='" . $unit . "'
                                   and kodebarang='" . $rBrg['kodebarang'] . "'";
						try {
							$owlPDO->exec($supd);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}

						$supd = "update " . $dbname . ".log_5masterbarangdt set saldoqty=saldoqty-" . $rBrg['jumlah'] . "
                                   where kodegudang='" . $unit . "' and kodebarang='" . $rBrg['kodebarang'] . "'";
						try {
							$owlPDO->exec($supd);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					}

					$supd = "update " . $dbname . ".log_transaksidt set statussaldo=0
                               where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($supd);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}

					$supd2 = "update " . $dbname . ".log_transaksiht set post=0,statusjurnal=0
                               where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($supd);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}

					$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "%'";
					try {
						$owlPDO->exec($sDel);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}

				break;
			case '7':
				foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {
					$comment1 = '';
					#periksa gudang apakah sudah tutup buku
					$strt = $owlPDO->query("select kodegudang,tanggal from " . $dbname . ".log_transaksiht where notransaksi='" . $bsdlis . "'");
					$strt->setFetchMode(PDO::FETCH_OBJ);
					while ($bat = $strt->fetch()) {
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='" . $bat->kodegudang . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							$comment1 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " has been closed\n";
						}
						#periksa apakah sudah tutup buku
						$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='" . substr($bat->kodegudang, 0, 4) . "' and periode='" . substr($bat->tanggal, 0, 7) . "'");
						$stu->setFetchMode(PDO::FETCH_OBJ);
						while ($baru = $stu->fetch()) {
							// $comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
							$comment1 .= "-" . $baru->kodeorg . " periode " . $baru->periode . " sudah closing\n";
						}
					}
					if ($comment1 != '') {
						// exit(" Error :".$comment1."\n process canceled");
						exit(" Error :" . $comment1 . "\n proces dibatalkan");
					}

					$tgl = substr($_POST['tanggal'][$dtList], 0, 7);
					$unit = substr($bsdlis, -6, 6);
					//exit("error:".$unit);
					$scek = $owlPDO->query("select distinct tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $tgl . "' and kodeorg='" . $unit . "'");
					$scek->setFetchMode(PDO::FETCH_ASSOC);
					$rcek = $scek->fetch();
					if ($rcek['tutupbuku'] == 1) {
						exit("error:periode akuntansi sudah di tutup");
					}
					$scekdua = $owlPDO->query("select distinct notransaksireferensi from " . $dbname . ".log_transaksi_vw where notransaksi='" . $bsdlis . "'");
					$scekdua->setFetchMode(PDO::FETCH_ASSOC);
					$rcekdua = $scekdua->fetch();

					$sdt = $owlPDO->query("select distinct post,statusjurnal from " . $dbname . ".log_transaksiht where notransaksi='" . $rcekdua['notransaksireferensi'] . "'");
					$sdt->setFetchMode(PDO::FETCH_ASSOC);
					$rdt = $sdt->fetch();
					if ($rdt['post'] == 1 && $rdt['statusjurnal'] == 1) {
						exit("error:Penerimaan notransaksi : " . $rdt['notransaksireferensi'] . ", sudah terposting, silakan lakukan unposting penerimaan terlebih dahulu");
					}
					$sBrg = $owlPDO->query("select distinct kodebarang,notransaksi,jumlah from " . $dbname . ".log_transaksidt where notransaksi='" . $bsdlis . "' and statussaldo=1");
					$sBrg->setFetchMode(PDO::FETCH_ASSOC);
					while ($rBrg =  $sBrg->fetch()) {

						$supd = "update " . $dbname . ".log_5saldobulanan set saldoakhirqty=(saldoakhirqty+" . $rBrg['jumlah'] . "),
                                   nilaisaldoakhir=(saldoakhirqty+" . $rBrg['jumlah'] . ")*hargarata,qtykeluar=(qtykeluar-" . $rBrg['jumlah'] . "),
                                   qtykeluarxharga=(qtykeluar-" . $rBrg['jumlah'] . ")*hargarata where periode='" . $tgl . "' and kodegudang='" . $unit . "'
                                   and kodebarang='" . $rBrg['kodebarang'] . "'";
						try {
							$owlPDO->exec($supd);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}

						$supd = "update " . $dbname . ".log_5masterbarangdt set saldoqty=saldoqty+" . $rBrg['jumlah'] . "
                                   where kodegudang='" . $unit . "' and kodebarang='" . $rBrg['kodebarang'] . "'";
						try {
							$owlPDO->exec($supd);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "<br/>";
							die();
						}
					}
					$supd = "update " . $dbname . ".log_transaksidt set statussaldo=0
                               where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($supd);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}

					$supd2 = "update " . $dbname . ".log_transaksiht set post=0,statusjurnal=0,notransaksireferensi=NULL
                               where notransaksi='" . $bsdlis . "'";
					try {
						$owlPDO->exec($supd2);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}

					$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi like '%" . $bsdlis . "%'";
					try {
						$owlPDO->exec($sDel);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>";
						die();
					}
				}
				break;
		}

		break;
	case 'unpostingPenjualan':
		try {
			$owlPDO->beginTransaction();

			if (count($_POST['notransaksi']) <= 0) {
				throw new PDOException("Data unposting belum dipilih.");
			}

			foreach ($_POST['notransaksi'] as $dtList => $bsdlis) {

				#periksa apakah unit sudah tutup buku
				$comment = '';
				$str = $owlPDO->query("select nojurnal,tanggal from " . $dbname . ".keu_jurnalht where noreferensi = '" . $bsdlis . "'");
				$str->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $str->fetch()) {
					$unit = explode("/", $bar->nojurnal);
					#periksa apakah sudah tutup buku
					$stu = $owlPDO->query("select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . $unit[1] . "' and periode='" . substr($bar->tanggal, 0, 7) . "'");
					$stu->setFetchMode(PDO::FETCH_OBJ);
					while ($baru = $stu->fetch()) {
						$comment .= "-" . $baru->kodeorg . " periode " . $baru->periode . " has been closed\n";
					}
				}

				if ($comment != '') {
					// throw new PDOException($comment."\n process canceled");
					throw new PDOException($comment . "\n proces dibatalkan");
				}

				$expnotran = explode('##', $bsdlis);
				$whr = "";
				if ($expnotran[2] != '') {
					$str = "select * from " . $dbname . ".keu_jurnaldt where noreferensi='" . $expnotran[1] . "' and tanggal='" . $expnotran[0] . "' and keterangan like '%" . $expnotran[2] . "%'";
					$res = fetchdata($str);
					foreach ($res as $key => $val) {
						$sDel2 = "delete from " . $dbname . ".keu_jurnalht where noreferensi = '" . $expnotran[1] . "' and tanggal='" . $expnotran[0] . "' and (kodejurnal='STCPO' or kodejurnal='STKER') and nojurnal ='" . $val['nojurnal'] . "'";
						$owlPDO->exec($sDel2);
					}
				}

				$sDel = "delete from " . $dbname . ".keu_jurnalht where noreferensi = '" . $bsdlis . "'";
				$sUp = "delete from " . $dbname . ".keu_pengakuanjual where notransaksi = '" . $bsdlis . "'";

				$owlPDO->exec($sDel);
				$owlPDO->exec($sUp);
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
		}
		break;
}


function movetohistory($notransaksi)
{
	global $dbname;
	global $owlPDO;

	$str = "select * from " . $dbname . ".approval where notransaksi='" . $notransaksi . "'"; #exit("error".$str);
	$res = fetchdata($str);
	foreach ($res as $val) {
		$str = "insert into " . $dbname . ".approval_return (notransaksi,jenispersetujuan,level,karyawanid,status,komentar,tanggal,nourut) values ('" . $val['notransaksi'] . "','" . $val['jenispersetujuan'] . "','" . $val['level'] . "','" . $val['karyawanid'] . "','" . $val['status'] . "','" . $val['komentar'] . "','" . $val['tanggal'] . "','1')";
		$owlPDO->exec($str);
	}

	return true;
}
