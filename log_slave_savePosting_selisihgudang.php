<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');



$gudang = checkPostGet('gudang','');
$kodeunit=substr($gudang, 0, 4);


$str="select * from ".$dbname.".setup_parameterappl where kodeaplikasi='GR' and kodeparameter='AKUNGRIR'"; 
$res=fetchdata($str);
foreach($res as $bar){
	$noakungrir=$bar['nilai'];
}

$str="select * from ".$dbname.".setup_parameterappl where kodeaplikasi='GR' and kodeparameter='PICPEMAKAI'"; 
$res=fetchdata($str);
foreach($res as $bar){
	$noakunPIC=$bar['nilai'];
}

$str="select * from ".$dbname.".setup_parameterappl where kodeaplikasi='GR' and kodeparameter='AKUNGRTR'"; 
$res=fetchdata($str);
foreach($res as $bar){
	$noakunPDP=$bar['nilai'];
}

#= tipe organisasi
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)='4'"; 
$res=fetchdata($str);
foreach($res as $bar){
	$tipeorganisasi[$bar['kodeorganisasi']]=$bar['tipe'];
}



error_reporting(0);
error_log(0);
## check if transaction period is normal
if (isTransactionPeriod()){
    $tipetransaksi = checkPostGet('tipetransaksi','');
    $tanggal = checkPostGet('tanggal','');
    $kodebarang = checkPostGet('kodebarang','');
    $satuan = checkPostGet('satuan','');
    $jumlah = checkPostGet('jumlah','');
    $kodept = checkPostGet('kodept','');
    $gudangx = checkPostGet('gudangx','');
    $untukpt = checkPostGet('untukpt','');
    $gudang = checkPostGet('gudang','');
    $blok = checkPostGet('kodeblok','');
    $kdpabrikasi = checkPostGet('kdpabrikasi','');
    $notransaksi = checkPostGet('notransaksi','');
    $hargasatuan = checkPostGet('hargasatuan','');
    $nopo = checkPostGet('nopo','');
    $nopp = checkPostGet('nopp','');
    $supplier = checkPostGet('supplier','');
    $kodekegiatan = checkPostGet('kodekegiatan','');
    $kodemesin = checkPostGet('kodemesin','');
	$currentRow = checkPostGet('currentRow', '');
	
	
	// cek apakah sudah di posting gudang
	$statussaldo = 0;
	if ($nopo != '') {
		$str = "select statussaldo from " . $dbname . ".log_transaksi_vw_detail where notransaksi='" . $notransaksi . "' and kodebarang='" . $kodebarang . "' and kodeblok='" . $blok . "' and nopp='" . $nopp . "' and kodekegiatan = '" . $kodekegiatan . "' ";
	} else {
		$str = "select statussaldo,notransaksireferensi from " . $dbname . ".log_transaksi_vw_detail where notransaksi='" . $notransaksi . "' and kodebarang='" . $kodebarang . "' and kodeblok='" . $blok . "' and kodekegiatan = '" . $kodekegiatan . "' ";
	}
	$res = fetchdata($str);
	$statussaldo = ($res[0]['statussaldo'] == '' ? '0' : $res[0]['statussaldo']);
	$notransaksireferensi = ($res[0]['notransaksireferensi'] == '' ? '0' : $res[0]['notransaksireferensi']);
	
	// cek apakah BKM
	if (strpos($notransaksireferensi, 'BKM') !== false) {
		$tipetransaksi = 'BKM';
		$param['notransaksi'] = $notransaksireferensi;
		$notransaksi = $notransaksi;
		if ($currentRow == 1) {
			$RBDet_s = deleteQuery(
				$dbname,
				'keu_jurnalht',
				"noreferensi='" . $notransaksireferensi . "' 
				AND (kodejurnal LIKE 'INVK%' OR kodejurnal LIKE 'INVM%')"
			);
			$owlPDO->exec($RBDet_s);
		}
	}

	if ($statussaldo == '0') {
		exit("Warning : Silahkan lakukan posting di menu Pengadaan-proses-posting ");
	} else {
		// hapus jurnal hanya yg kodejurnal INVK / INVM
		if ($currentRow == 1) {
			$RBDet_s = deleteQuery(
				$dbname,
				'keu_jurnalht',
				"noreferensi='" . $notransaksi . "' 
				AND (kodejurnal LIKE 'INVK%' OR kodejurnal LIKE 'INVM%')"
			);
			$owlPDO->exec($RBDet_s);
		}
	}
		
	
	
	
    $user = $_SESSION['standard']['userid'];
	$segment = !empty($_POST['kodesegment']) ? $_POST['kodesegment'] : colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

	## Validasi Kode barang
    // if (!preg_match('/^[0-9]{8}$/', $kodebarang)) {
        // exit("Warning: Kode Barang tidak standard");
    // }
	
	## Periksa apakah sudah pernah mempengaruhi saldo
    $statussaldo = 0;
    $str = "select statussaldo from ".$dbname.".log_transaksidt where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."' and nopp='".$nopp."'";
	$res=fetchdata($str);
	$statussaldo = ($res[0]['statussaldo']==''?'0':$res[0]['statussaldo']);

    if ($statussaldo > 0 and $statussaldo != ''){
		## dilewati karena sudah membentuk jurnal
        exit(0);
    }else{
		## statussaldo=1
        
		## periksa apakah sudah tutup buku:
        ## unit sendiri
        $periode = $_SESSION['gudang'][$gudang]['tahun']."-".$_SESSION['gudang'][$gudang]['bulan'];
        $str = "select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".substr($gudang, 0, 4)."'";
		$res=fetchdata($str);
        $close = $res[0]['tutupbuku'];
        if ($close == '1'){
			if($_SESSION['language']=='ID'){
				exit ("Gagal : Departement keuangan sudah tutup buku");
			}else{
				exit("Warning : Accounting Period has been closed.");				
			}
        }
		
        ## Unit tujuan
        if ($gudangx != '' and (substr($gudang,0,4) != substr($gudangx,0,4))){
			## jika mutasi dan gudang tujuan ada di unit berbeda
            $str = "select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".substr($gudangx,0,4)."'";
			$res=fetchdata($str);
            $close = $res[0]['tutupbuku'];
			
			if($close == '1' and $tipetransaksi != '3'){
				## Khusus penerimaan mutasi dikecualikan boleh di jurnal walau pengirim sudah tutup buku
				if($_SESSION['language']=='ID'){
					exit ("Gagal : Departement keuangan gudang tujuan sudah tutup buku");
				}else{
					exit("Warning : Receiver Accounting Period has been closed.");
				}
            }
        }
		
        ## Periksa transaksi yang belum diposting di tanggal sebelumnya:
        $str = "select * from ".$dbname.".log_transaksi_vw where kodebarang='".$kodebarang."' and kodegudang='".$gudang."' and tanggal < '".tanggalsystem($tanggal)."' and statussaldo='0' and notransaksi not in (select notransaksi from ".$dbname.".approval where status='2' and jenispersetujuan='GR')";
		$res=fetchdata($str);
		$numrows=count($res);
        if ($numrows > 0) {
			if($_SESSION['language']=='ID'){
				exit("Gagal : Masih ada barang yang sama belum di posting pada tanggal yang lebih kecil.");
			}else{
				exit("Warning : There is material has not been posted on previous date.");				
			}
        }
		
        ## Ambil nama barang
        $str = "select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$kodebarang."'";
		$res=fetchdata($str);
        $namabarang = $res[0]['namabarang'];
		
        if ($namabarang == ''){
            $namabarang = $kodebarang;			
		}

		########################################
		#### Begin Penerimaan dari Supplier ####
		########################################
        if($tipetransaksi == '1'){
			exit("warning: Belum di sesuaikan bentuk ulang jurnal, untuk PALMA DAN DMA baru pemakain barang saja");
        }
		######################################
		#### End Penerimaan dari Supplier ####
		######################################
		
		
		#################################
		#### Begin Retur ke Supplier ####
		#################################
        if($tipetransaksi == '6'){
			exit("warning: Belum di sesuaikan bentuk ulang jurnal, untuk PALMA DAN DMA baru pemakain barang saja");
        }
		###############################
		#### End Retur ke Supplier ####
		###############################
		
		
		######################################
		#### Begin Retur Barang ke Gudang ####
		######################################
        if ($tipetransaksi == '2') {
			exit("warning: Belum di sesuaikan bentuk ulang jurnal, untuk PALMA DAN DMA baru pemakain barang saja");
        }
		####################################
		#### End Retur Barang ke Gudang ####
		####################################
		
		
		########################################
		#### Begin Penerimaan Mutasi Gudang ####
		########################################
		// ROUND 2021 penerimaan mutasi tidak bisa diround karena ada hubungan hutang unit. seharusnya sudah round, tapi untuk transaksi lama masih mungkin koma
		if ($tipetransaksi == '3'){
			exit("warning: Belum di sesuaikan bentuk ulang jurnal, untuk PALMA DAN DMA baru pemakain barang saja");
        } 
		######################################
		#### End Penerimaan Mutasi Gudang ####
		######################################
		
		
		#########################################
		#### Begin Pengeluaran Mutasi Gudang ####
		#########################################
		if ($tipetransaksi == '7'){
			exit("warning: Belum di sesuaikan bentuk ulang jurnal, untuk PALMA DAN DMA baru pemakain barang saja");
        }
		#######################################
		#### End Pengeluaran Mutasi Gudang ####
		#######################################
		
		
		###################################################
		#### Begin Pengeluaran/Pemakaian Barang Gudang ####
		###################################################
		if ($tipetransaksi == '5') {
			try{
				$owlPDO->beginTransaction();

				$str = "select statusblok,hargarata from " . $dbname . ".log_transaksi_vw where notransaksi='" . $notransaksi . "' and kodebarang = '" . $kodebarang . "' and kodeblok = '" . substr($blok,0,6) . "' and kodemesin = '" . $kodemesin . "' and kodekegiatan = '" . $kodekegiatan . "' ";
				$res = fetchdata($str);
				$statusblokx = $res[0]['statusblok'];
				$hargaratax = $res[0]['hargarata'];

				if($statusblokx != '' || $statusblokx != NULL){
					// cek harganya bener ga
					$strxx = "select hartot,hargarata,kodeblok from " . $dbname . ".log_transaksi_vw_detail where notransaksi='" . $notransaksi . "' and hargarata != '".$hargaratax."'
					and kodebarang = '" . $kodebarang . "' and kodeblok like '" . substr($blok,0,6) . "%' and kodemesin = '" . $kodemesin . "' and kodekegiatan = '" . $kodekegiatan . "' ";
					$resxx = fetchdata($strxx);
					if(count($resxx) > 0){
						exit("warning: Ada hargarata senilai ".$resxx[0]['hargarata'].". tidak sesuai di proporsi detail untuk notransaksi ".$notransaksi." kodebarang ".$kodebarang." kodeblok ".$resxx[0]['kodeblok']." ");
					}
				}
				

				
				// ambil rupiah nya
				$str = "select hartot,hargarata from " . $dbname . ".log_transaksi_vw_detail where notransaksi='" . $notransaksi . "' and kodebarang = '" . $kodebarang . "' and kodeblok = '" . $blok . "' and kodemesin = '" . $kodemesin . "' and kodekegiatan = '" . $kodekegiatan . "' ";
				$res = fetchdata($str);
				$rpkeluar = $res[0]['hartot'];
				$hargarata = $res[0]['hargarata'];
				if($hargarata <= 0){
					exit("warning: Hargarata tidak ada ");
				}
				if($hargarata == ''){
					exit("warning: Hargarata tidak ada ");
				}

				$kelkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok');
				if($kelkeg[$kodekegiatan]=='TRK' and $kodemesin==''){
					throw new PDOException("Jika kegiatan traksi maka kendaraan tidak boleh kosong");
				}
				
				#= get nik karyawan
				$str = "select namapenerima from ".$dbname.".log_transaksiht where notransaksi = '".$notransaksi."'";
				$res=fetchdata($str);
				$karyawanid = $res[0]['namapenerima'];
				
				## Get Kelompok kegiatan
				$str = "select kodekegiatan from ".$dbname.".log_transaksidt where notransaksi = '".$notransaksi."' LIMIT 1";
				$res=fetchdata($str);
				$vKdKegiatan = $res[0]['kodekegiatan'];
				
				$str = "select kelompok from ".$dbname.".setup_kegiatan where kodekegiatan = '".$vKdKegiatan."'";
				$res=fetchdata($str);
				$vKlmpKegiatan = $res[0]['kelompok'];		


				## Periksa apakah dari satu PT
				$pengguna = substr($_POST['untukunit'], 0, 4);

				$ptpengguna = '';
				$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $pengguna . "'";
				$res=fetchdata($str);
				$ptpengguna = $res[0]['induk'];
				
				$intraco = '';
				$interco = '';
				$str = "select akunpiutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
				$res=fetchdata($str);
				foreach($res as $key=>$val){
					if ($val['jenis'] == 'intra'){
						$intraco = $val['akunpiutang'];
					}else{
						$interco = $val['akunpiutang'];
					}
				}

				if ($intraco=='' || $interco=='') {
					throw new PDOException("Account intraco or interco not available for " . $pengguna."\n\nAkun intraco  atau interco belum ada untuk unit ".$pengguna);
				}   
				
				$ptGudang = '';
				$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . substr($gudang, 0, 4) . "'";
				$res=fetchdata($str);
				$ptGudang = $res[0]['induk'];

				## Jika pt tidak sama maka pakai akun interco
				$akunspl = '';
				if ($ptGudang != $ptpengguna) {
					## Ambil akun interco
					$akunspl = '';
					$str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . substr($gudang, 0, 4) . "' and jenis='inter'";
					$res=fetchdata($str);
					$akunspl = $res[0]['akunhutang'];
					
					$inter = $interco;
					if ($akunspl == ''){
						throw new PDOException("Account intraco or interco not available for " . $pengguna."\n\nAkun intraco  atau interco belum ada untuk unit ".$pengguna);
					}
				}else if ($pengguna != substr($gudang, 0, 4)) { 
					## Jika satu pt beda kebun
					## Ambil akun intraco
					$akunspl = '';
					$str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . substr($gudang, 0, 4) . "' and jenis='intra'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_OBJ);
					$res=fetchdata($str);
					$akunspl = $res[0]['akunhutang'];
					
					$inter = $intraco;
					if ($akunspl == ''){
						throw new PDOException("Account intraco or interco not available for ".$pengguna."\n\nAkun intraco  atau interco belum ada untuk unit ".$pengguna);
					}
				}
				
				## Ambil akun pekerjaan atau kendaraan atau ab
				## Periksa ke table setup blok
				$statustm = '';
				$str = "select statusblok from " . $dbname . ".setup_blok where kodeorg='" . $blok . "'";
				$res=fetchdata($str);
				$statustm = $res[0]['statusblok'];
				
				$akunpekerjaan = '';
				$str = "select noakun from " . $dbname . ".setup_kegiatan where 
					kodekegiatan='" . $kodekegiatan . "'";
				$res=fetchdata($str);
				$akunpekerjaan = $res[0]['noakun'];
				
				## Untuk project aktiva dalam konstruksi maka akun diambil dari kolom kodekegiatan

				$cek_blok=explode('/',$blok);
				$kodeasset = '';
				if (substr($blok, 0, 2) == 'AK' or substr($blok, 0, 2) == 'PB' or $cek_blok[1] == 'SPK') {
					$akunpekerjaan = substr($kodekegiatan, 0, 7);
					$kodeasset = $blok;

					## Pemindahan kodeblok ke kode asset
					$blok = "";
				}
				
				## Jika akun kegiatan tidak ada maka exit
				if ($akunpekerjaan == ''){
					throw new PDOException("Account not available for activity " . $kodekegiatan."\n\nAkun pekerjaan belum ada untuk kegiatan ".$kodekegiatan);
				}
				
				## Ambil noakun barang
				$klbarang = substr($kodebarang, 0, 3);
				$str = "select noakun from " . $dbname . ".log_5klbarang where kode='" . $klbarang . "'";
				$res=fetchdata($str);
				$akunbarang = $res[0]['noakun'];
				
				if (($akunbarang == '') and ( $klbarang < '400' or substr($kodebarang, 0, 1) == '9')){
					throw new PDOException("Account for material not available for " . $notransaksi."\n\nNoakun barang belum ada untuk transaksi".$notransaksi);
				}else{
				//throw new PDOException("Error");
					$kdsupblok='';
					$kdsup='';
					$str = "select supplierid from " . $dbname . ".log_5supplier where supplierid='" . $blok . "'";
					$res=fetchdata($str);
					$kdsupblok = $res[0]['supplierid'];

					if($kdsupblok!=''){
						$kdsup=$blok;
					}else{
						$blok=$blok;
					}

					
				// cek apakah ada pic nya
				$ada_pic = 0;
				$str_pic = "select * from " . $dbname . ".log_pemakaianpresentase where notransaksi='" . $notransaksi . "' and kodebarang='".$kodebarang."' ";
				$res_pic=fetchdata($str_pic);
				$ada_pic=count($res_pic);
				// cek apakah udh ada jurnal PIC Karyawan
				$str_jurnalPIC = "select * from ".$dbname.".keu_jurnaldt where noreferensi='" . $notransaksi . "' and kodebarang='".$kodebarang."' and keterangan like '%Pemakaian PIC Karyawan%' ";
				$res_jurnalPIC=fetchdata($str_jurnalPIC);
				$total_jurnalPIC=count($res_jurnalPIC);

				$total_harga_pic_presentase=0;
				$sub_total_harga_pic_presentase=0;
				$jumlahpic_hargarata=0;
				$sub_jumlahpic_hargarata=0;
				$total_jumlah_pic=0;
				$total_pic_kary=0;
				$sub_total_pic_kary=0;
				if($ada_pic > 0){
					$optnamakaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
					$optnikkaryawan=makeOption($dbname,'datakaryawan','karyawanid,nik');
					// ambil data panen
					
					// $noUrut = 0;
					// ambil total qty
					$str = "select jumlah,hargarata from " . $dbname . ".log_transaksidt where notransaksi='" . $notransaksi . "' and kodebarang='".$kodebarang."' ";
					$res=fetchdata($str);
					$jumlahBarang_trxdetail = $res[0]['jumlah'];
					$hargarata_trxdetail = $res[0]['hargarata'];


					foreach($res_pic as $key=>$val){

						$total_jumlah_pic += $val['jumlah'];

						// hargarata pic presentase dan jumlah pakai presentase
						$total_harga_pic_presentase = ($hargarata * ($val['presentase']/100) * $val['jumlah']);
						$jumlahpic_hargarata =  ($hargarata * $val['jumlah']);
						// pakai pic dikali hargarata
						$sub_total_harga_pic_presentase += $total_harga_pic_presentase;
						$sub_jumlahpic_hargarata += $jumlahpic_hargarata;
						// total pic kary
						$total_pic_kary = ($jumlahpic_hargarata - $total_harga_pic_presentase);
						$sub_total_pic_kary += $total_pic_kary;

								if($total_jurnalPIC <= 0){
									// JURNAL utang karyawan

									## Ambil noakun barang
									$klbarang = substr($kodebarang, 0, 3);
									$str = "select noakun from " . $dbname . ".log_5klbarang where kode='" . $klbarang . "'";
									$res=fetchdata($str);
									$akunbarang = $res[0]['noakun'];
									
									if (($akunbarang == '') and ( $klbarang < '400' or substr($kodebarang, 0, 1) == '9')){
										throw new PDOException("Account for material not available for " . $notransaksi."\n\nNoakun barang belum ada untuk transaksi".$notransaksi);
									}


								$kodeJurnal = 'INVK1';
								##======================== Begin Nomor Jurnal =============================
								## Get Journal Counter
								$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'  and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'";
								$tmpKonter = fetchData($str);
								$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

								## Transform No Jurnal dari No Transaksi
								$nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
								##======================== End Nomor Jurnal ============================
								## Prep Header
								$dataResPIC['header'][] = array(
									'nojurnal' => $nojurnal,
									'kodejurnal' => $kodeJurnal,
									'tanggal' => tanggalsystem($tanggal),
									'tanggalentry' => date('Ymd'),
									'posting' => 1,
									'totaldebet' => ($total_harga_pic_presentase),
									'totalkredit' => (-1 * $total_harga_pic_presentase),
									'amountkoreksi' => '0',
									'noreferensi' => $notransaksi,
									'autojurnal' => '1',
									'matauang' => 'IDR',
									'kurs' => '1',
									'revisi' => '0'
								);
								
								## Data Detail
								// $noUrut++;	
								$noUrut=1;	
								$keterangan = "Pemakaian PIC Karyawan ".$optnamakaryawan[$val['karyawanid']]." Atas barang " . $namabarang . " " . $val['jumlah'] . " " . $satuan . "";
								
								if($vKlmpKegiatan == 'SPL'){
									$nodok_x = substr($blok,0,9);
									$kodeblok_x = substr($blok,0,9);
									$kodeasset_x = $kodeasset;
								}elseif($cek_blok[1] == 'SPK'){
									$nodok_x = $kodeasset;
									$kodeblok_x = '';
									$kodeasset_x = '';
								}else{
									$nodok_x = '';
									$kodeblok_x = '';
									$kodeasset_x = $kodeasset;
								}
								

								## Debet
								$dataResPIC['detail'][] = array(
									'nojurnal' => $nojurnal,
									'tanggal' => tanggalsystem($tanggal),
									'nourut' => $noUrut,
									'noakun' => $noakunPIC,
									'keterangan' => $keterangan,
									'jumlah' => ($total_harga_pic_presentase),
									'matauang' => 'IDR',
									'kurs' => '1',
									'kodeorg' => substr($gudang, 0, 4),
									'kodekegiatan' => $kodekegiatan,
									'kodeasset' => $kodeasset_x,
									'kodebarang' => $kodebarang,
									'nik' => $val['karyawanid'],
									'kodecustomer' => '',
									'kodesupplier' => $kdsup,
									'noreferensi' => $notransaksi,
									'noaruskas' => '',
									'kodevhc' => $kodemesin,
									'nodok'=> $nodok_x,
									'kodeblok'=> $kodeblok_x,
									'revisi' => '0',
									'kodesegment' => $segment
								);
								$noUrut++;

								## Kredit
								$dataResPIC['detail'][] = array(
									'nojurnal' => $nojurnal,
									'tanggal' => tanggalsystem($tanggal),
									'nourut' => $noUrut,
									'noakun' => $akunbarang,
									'keterangan' => $keterangan,
									'jumlah' => (-1 * $total_harga_pic_presentase),
									'matauang' => 'IDR',
									'kurs' => '1',
									'kodeorg' => substr($gudang, 0, 4),
									'kodekegiatan' => $kodekegiatan,
									'kodeasset' => $kodeasset_x,
									'kodebarang' => $kodebarang,
									'nik' => $val['karyawanid'],
									'kodecustomer' => '',
									'kodesupplier' => $kdsup,
									'noreferensi' => $notransaksi,
									'noaruskas' => '',
									'kodevhc' => $kodemesin,
									'nodok'=> $nodok_x,
									'kodeblok'=> $kodeblok_x,
									'revisi' => '0',
									'kodesegment' => $segment
								);
								// $noUrut++;		
								
								// exit("warning : ".$total_beban_perusahaan." - ".$jumlah." - ".$jumlahBarang_trxdetail." ");
								// exit("warning : ".$rpkeluar." ");
								// exit("warning : ".$sub_total_harga_pic_presentase." - ".$sub_jumlahpic_hargarata." - ".$sub_total_pic_kary." - ".$jumlahBarang_trxdetail." ");
								## Header and Detail inserted
								## Update Kode Jurnal
								$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='" . $ptpengguna .
								"' and kodekelompok='" . $kodeJurnal . "'  and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'");
								$owlPDO->exec($updJurnal); 
							}

								// beban perusahaan
								$b_perusahaan = ($jumlahBarang_trxdetail - $total_jumlah_pic) * $hargarata;
								$total_beban_perusahaan = $sub_total_pic_kary + $b_perusahaan;
								// hasil rupiah
								$rpkeluar = $total_beban_perusahaan * ($jumlah/$jumlahBarang_trxdetail);

							
					}
					if($total_jurnalPIC <= 0){
						// create jurnal PIC
						if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
							$insHead = insertQuery($dbname, 'keu_jurnalht', $dataResPIC['header']);
							$owlPDO->exec($insHead); 
							
							foreach ($dataResPIC['detail'] as $row) {
								$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
								$owlPDO->exec($insDet); 
							}
						}
					}
				}
				

					
					## Penggunaan internal$ptGudang$ptpengguna
					if ($pengguna == substr($gudang, 0, 4)) {
						$kodeJurnal = 'INVK1';
						
						##======================== Begin Nomor Jurnal =============================
						## Get Journal Counter
						$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'  and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'";
						$tmpKonter = fetchData($str);
						$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

						## Transform No Jurnal dari No Transaksi
						$nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
						##======================== End Nomor Jurnal ============================
						
						## Prep Header
						$dataRes['header'] = array(
							'nojurnal' => $nojurnal,
							'kodejurnal' => $kodeJurnal,
							'tanggal' => tanggalsystem($tanggal),
							'tanggalentry' => date('Ymd'),
							'posting' => 1,
							'totaldebet' => ($rpkeluar),
							'totalkredit' => (-1 * $rpkeluar),
							'amountkoreksi' => '0',
							'noreferensi' => $notransaksi,
							'autojurnal' => '1',
							'matauang' => 'IDR',
							'kurs' => '1',
							'revisi' => '0'
						);
						
						## Data Detail
						$noUrut = 1;
						$keterangan = "Pemakaian barang " . $namabarang . " " . $jumlah . " " . $satuan;

						if($vKlmpKegiatan == 'SPL'){
							$nodok_x = $blok;
							$kodeblok_x = $blok;
							$kodeasset_x = $kodeasset;
						}elseif($cek_blok[1] == 'SPK'){
							$nodok_x = $kodeasset;
							$kodeblok_x = '';
							$kodeasset_x = '';
						}else{
							$nodok_x = '';
							$kodeblok_x = $blok;
							$kodeasset_x = $kodeasset;
						}
						
						## Debet
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $akunpekerjaan,
							'keterangan' => $keterangan,
							'jumlah' => ($rpkeluar),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => substr($gudang, 0, 4),
							'kodekegiatan' => $kodekegiatan,
							'kodeasset' => $kodeasset_x,
							'kodebarang' => $kodebarang,
							'nik' => $karyawanid,
							'kodecustomer' => '',
							'kodesupplier' => $kdsup,
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => $kodemesin,
							'nodok'=> $nodok_x,
							'kodeblok'=> $kodeblok_x,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;

						## Kredit
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $akunbarang,
							'keterangan' => $keterangan,
							'jumlah' => (-1 * $rpkeluar),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => substr($gudang, 0, 4),
							'kodekegiatan' => $kodekegiatan,
							'kodeasset' => $kodeasset_x,
							'kodebarang' => $kodebarang,
							'nik' => $karyawanid,
							'kodecustomer' => '',
							'kodesupplier' => $kdsup,
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => $kodemesin,
							'nodok'=>$nodok_x,
							'kodeblok'=> $kodeblok_x,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;

						if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
							$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
							$owlPDO->exec($insHead); 
							
							foreach ($dataRes['detail'] as $row) {
								$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
								$owlPDO->exec($insDet); 
							}
							
							## Header and Detail inserted
							## Update Kode Jurnal
							$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='" . $ptpengguna .
							"' and kodekelompok='" . $kodeJurnal . "'  and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'");
							$owlPDO->exec($updJurnal); 
							
						}else{
						}
					} else {
						## Jika inter atau intraco 
						## Proses data sisi pemilik
						$kodeJurnal = 'INVK1';
						
						##======================== Begin Nomor Jurnal =============================
						## Get Journal Counter
						$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."'  and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'"; 
						$tmpKonter = fetchData($str);
						$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

						## Transform No Jurnal dari No Transaksi
						$nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
						##======================== End Nomor Jurnal ================================
						
						## No header pemilik
						$header1pemilik = $nojurnal;
						
						## Prep Header
						$dataRes['header'] = array(
							'nojurnal' => $nojurnal,
							'kodejurnal' => $kodeJurnal,
							'tanggal' => tanggalsystem($tanggal),
							'tanggalentry' => date('Ymd'),
							'posting' => 1,
							'totaldebet' => ($rpkeluar),
							'totalkredit' => (-1 * $rpkeluar),
							'amountkoreksi' => '0',
							'noreferensi' => $notransaksi,
							'autojurnal' => '1',
							'matauang' => 'IDR',
							'kurs' => '1',
							'revisi' => '0'
						);

						## Data Detail
						$noUrut = 1;
						$keterangan = "Pemakaian barang " . $namabarang . " " . $jumlah . " " . $satuan;
						$keterangan = substr($keterangan, 0, 150);

						if($cek_blok[1] == 'SPK'){
							$nodok_x = $kodeasset;
							$kodeblok_x = $blok;
						}else{
							$nodok_x = '';
							$kodeblok_x = $blok;
						}

						
						## Debet
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $inter,
							'keterangan' => $keterangan,
							'jumlah' => ($rpkeluar),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => substr($gudang, 0, 4),
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok'=>$nodok_x,
							'kodeblok'=> $kodeblok_x,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;

						## Kredit
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $akunbarang,
							'keterangan' => $keterangan,
							'jumlah' => (-1 * $rpkeluar),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => substr($gudang, 0, 4),
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok'=>$nodok_x,
							'kodeblok'=> $kodeblok_x,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						
						if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != '') {
							$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
							$owlPDO->exec($insHead); 
							
							foreach ($dataRes['detail'] as $row) {
								$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
								$owlPDO->exec($insDet); 
							}
							
							## Header and Detail inserted
							## Update Kode Jurnal
							$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='" . $ptGudang .
											"' and kodekelompok='" . $kodeJurnal . "' and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'");
							$owlPDO->exec($updJurnal);         
						}
						
						## Proses data sisi pengguna
						$kodeJurnal = 'INVK1';
						
						##======================== Begin Nomor Jurnal =============================
						## Ambil tanggal terkecil periode pengguna
						$tanggalsana = '';
						$str = "select tanggalmulai from " . $dbname . ".setup_periodeakuntansi
							   where kodeorg='" . $pengguna . "' and tutupbuku=0";
						$res=fetchdata($str);
						foreach($res as $key=>$val){
							$tanggalsana = str_replace("-","",$val['tanggalmulai']);
						}
						
						if($tanggalsana<=tanggalsystem($tanggal)){
							## Jika periode sama maka biarkan
							$tanggalsana = tanggalsystem($tanggal);
						}else{
							## Rollback header sisi pemilik
							$RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $header1pemilik . "'");
							$owlPDO->exec($RBDet); 
							throw new PDOException("Receivers accounting period not the same as warehouse");
						}
							
						## Get Journal Counter
						$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."' and periode='".$periode."'  and kodeunit='".substr($pengguna, 0, 4)."'"; #exit("error".$str);
						$tmpKonter = fetchData($str);
						$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

						## Transform No Jurnal dari No Transaksi
						$nojurnal = str_replace("-", "", $tanggalsana) . "/" . $pengguna . "/" . $kodeJurnal . "/" . $konter;
						##======================== End Nomor Jurnal =============================
						
						## Prep Header
						## Ganti header
						unset($dataRes['header']);    
						
						$dataRes['header'] = array(
							'nojurnal' => $nojurnal,
							'kodejurnal' => $kodeJurnal,
							'tanggal' => $tanggalsana,
							'tanggalentry' => date('Ymd'),
							'posting' => 1,
							'totaldebet' => ($rpkeluar),
							'totalkredit' => (-1 * $rpkeluar),
							'amountkoreksi' => '0',
							'noreferensi' => $notransaksi,
							'autojurnal' => '1',
							'matauang' => 'IDR',
							'kurs' => '1',
							'revisi' => '0'
						);

						## Data Detail
						$keterangan = "Pemakaian barang " . $namabarang . " " . $jumlah . " " . $satuan;
						$keterangan = substr($keterangan, 0, 150);
						$noUrut = 1;
						
						## Ganti detail 
						unset($dataRes['detail']);

						if($vKlmpKegiatan == 'SPL'){
							$nodok_x = $blok;
							$kodeblok_x = $blok;
							$kodeasset_x = $kodeasset;
						}elseif($cek_blok[1] == 'SPK'){
							$nodok_x = $kodeasset;
							$kodeblok_x = $blok;
							$kodeasset_x = '';
						}else{
							$nodok_x = '';
							$kodeblok_x = $blok;
							$kodeasset_x = $kodeasset;
						}

						
						## Debet
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggalsana,
							'nourut' => $noUrut,
							'noakun' => $akunpekerjaan,
							'keterangan' => $keterangan,
							'jumlah' => ($rpkeluar),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $pengguna,
							'kodekegiatan' => $kodekegiatan,
							'kodeasset' => $kodeasset_x,
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $kdsup,
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => $kodemesin,
							'nodok'=>$nodok_x,
							'kodeblok'=> $kodeblok_x,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;

						## Kredit
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggalsana,
							'nourut' => $noUrut,
							'noakun' => $akunspl,
							'keterangan' => $keterangan,
							'jumlah' => (-1 * $rpkeluar),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $pengguna,
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $kdsup,
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => $kodemesin,
							'nodok'=>$nodok_x,
							'kodeblok'=> $kodeblok_x,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;
						
						## EXECUTE                      
						// if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != '') {
						if ((substr($kodebarang, 0, 3) < '400') and trim($akunbarang) != '') {
							$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
							$owlPDO->exec($insHead); 
							
							foreach ($dataRes['detail'] as $row) {
								$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
								$owlPDO->exec($insDet); 
							}
							
							## Header and Detail inserted
							## Update Kode Jurnal
							$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='" . $ptpengguna .
											"' and kodekelompok='" . $kodeJurnal . "' and periode='".$periode."'  and kodeunit='".substr($pengguna, 0, 4)."'");
							$owlPDO->exec($updJurnal); 
							
						} else {
					
						}
					}
				}
				
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Error, " . addslashes($e->getMessage());
			}
        }
		#################################################
		#### End Pengeluaran/Pemakaian Barang Gudang ####
		#################################################
    } ## End of statussaldo=0   
}## Wnd of if(isTransactionPeriod()) line: 7
else{
	echo " Error: Transaction Period missing";
}


?>