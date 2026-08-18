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



/*
$str="select * from ".$dbname.".setup_parameterappl where kodeaplikasi='GD' and kodeparameter='GDPOSTING'"; 
$res=fetchdata($str);
foreach($res as $bar){
	$dataunit=$bar['nilai'];
}

$explodedata=explode(',',$dataunit);
foreach($explodedata as $data){
	$arrunit[$data]=$data;
}
	
if(!in_array($kodeunit,$arrunit)){
	exit("Warning:UNIT ".$kodeunit." DILARANG POSTING SAMPAI INFORMASI LEBIH LANJUT");
}
*/


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
			try{
				$owlPDO->beginTransaction();
				
				## Periksa harga satuan
				// exit("Error".$hargasatuan._.$nopo._.$supplier);
				/*
				if (intval($hargasatuan) == 0 or $nopo == '' or $supplier == ''){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Harga/No. PO/Supplier tidak ditemukan.");
					}else{
						throw new PDOException("Price/No. PO/Supplier not found.");
					}
				}
				*/
				
				
				
				#= update tambahan adanya GIT
				#= jika penerimaan di RO, maka terbentuk 
				#= persediaannya diganti persediian git (kepala 11504)
				/*
					PENERIMAAN BARANG 					DB			CR
					GOOD IN TRANSIT (GIT) (Inv)			1,000 	
					GR / IR			 								1,000 
				*/
				
				// jika pengiriman kebun Tagihan HO
				// $optloktagihanflag=makeOption($dbname,'log_poht','nopo,idFrancoinvc');
				// $optloktagihan=makeOption($dbname,'setup_franco','id_franco,kodeunit');
				// $hubrk=0;
				// if (substr($gudang,0,4)!=$optloktagihan[$optloktagihanflag[$nopo]]) {
				// 	$hubrk=1;
				// }
				if ($nopo == '' or $supplier == ''){
					if(substr($kodebarang,0,1)=='9' || substr($kodebarang,0,1)=='8'){
						if($_SESSION['language']=='ID'){
							// throw new PDOException("No. PO/Supplier tidak ditemukan.");
							throw new PDOException("PO jasa dan asset tidak diperbolehkan masuk gudang, lakukan penerimaan melalui non inventory");
						}else{
							throw new PDOException("No. PO/Supplier not found.");
						}
					}else{
						if(intval($hargasatuan) == 0){
							if($_SESSION['language']=='ID'){
								throw new PDOException("Harga/No. PO/Supplier tidak ditemukan.");
							}else{
								throw new PDOException("Price/No. PO/Supplier not found.");
							}
						}
					}
				}

				$str3="select * from ".$dbname.".log_sorefrensi where 
				nopo ='".$nopo."' and nopp = '".$nopp."' and kodebarang='".$kodebarang."'";
				$res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
				$res3->setFetchMode(PDO::FETCH_ASSOC);
				while($rsd=$res3->fetch()){
					$hargaongkos=$rsd['nilai_proporsi']/$rsd['jumlah'];
					$nomorSonya=$rsd['noso'];
					$nilaipro=$rsd['nilai_proporsi'];
				}
				$supplier_nama=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
				if($nomorSonya != ''){
					$str = "select kodesupplier from ".$dbname.".log_poht where nopo='".$nomorSonya."'";
					$res=fetchdata($str);
					$kodesupplierSO = $res[0]['kodesupplier'];
				}


				
				## Generate saldo updater
				## Ambil saldo saat ini 
				// ROUND2021
				// $nilaitotal = $jumlah * $hargasatuan;
				$nilaitotal = round($jumlah * $hargasatuan,2);
				// @$hargasatuan=$nilaitotal/$jumlah;
				if ($hargaongkos!='') {
					$hargasatuan=$hargasatuan/*+$hargaongkos*/;
					//$nilaitotal=$nilaitotal+$nilaipro;
				}
				else
				{
					@$hargasatuan=$nilaitotal/$jumlah;
				}
				$cursaldo = 0;
				$nilaisaldo = 0;
				$qtymasuk = 0;
				$qtymasukxharga = 0;
				$saldoakhirqty = 0;
				$nilaisaldoakhir = 0;
				$hargarata = 0;
				
				$klbarang = substr($kodebarang, 0, 3);
				$brgaset = substr($kodebarang, 0, 1);
				
				$str = "select saldoakhirqty, hargarata, nilaisaldoakhir, qtymasuk, qtymasukxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				$res=fetchdata($str);
				$numrows=count($res);
				
				if(($klbarang < '400' or $brgaset == '9')){
					if ($numrows < 1){
						## Jika belum ada penerimaan sebelumnya
						$newhargarata = $hargasatuan;
						$newqtymasuk = $jumlah;
						$newqtymasukxharga = $nilaitotal;
						$newsaldoakhirqty = $jumlah;
						$newnilaisaldoakhir = $nilaitotal;
						if($brgaset == '9'){
							$newhargarata = '0';
							$newqtymasukxharga = '0';
							$newnilaisaldoakhir = '0';
						}
						$strupdate = "insert into ".$dbname.".log_5saldobulanan (kodeorg, kodebarang, saldoakhirqty, hargarata, lastuser, periode, nilaisaldoakhir, kodegudang, qtymasuk, qtykeluar, qtymasukxharga, qtykeluarxharga, saldoawalqty, hargaratasaldoawal, nilaisaldoawal) values ('".$kodept."','".$kodebarang."','".$newqtymasuk."','".$newhargarata."','".$user."','".$periode."','".$newqtymasukxharga."','".$gudang."','".$newsaldoakhirqty."','0','".$newnilaisaldoakhir."','0','0','0','0')";
					}else{
						## Bentuk harga baru
						foreach($res as $key=>$val){
							$cursaldo = $val['saldoakhirqty'];
							$nilaisaldo = $val['nilaisaldoakhir'];
							$qtymasuk = $val['qtymasuk'];
							$qtymasukxharga = $val['qtymasukxharga'];
							$hargarata = $val['hargarata'];
						}
						
						$newhargarata       = @(($nilaitotal + $nilaisaldo) / ($jumlah + $cursaldo));
						$newqtymasuk         = $qtymasuk + $jumlah;
						$newqtymasukxharga  = $qtymasukxharga + $nilaitotal;
						$newsaldoakhirqty    = $jumlah + $cursaldo;
						$newnilaisaldoakhir  = $nilaisaldo + $nilaitotal;

						if (($newsaldoakhirqty < 0)or($newnilaisaldoakhir < 0)){
							if($_SESSION['language']=='ID'){
								throw new PDOException("Saldo/Nilai tidak mencukupi (transaksi:".$jumlah." saldo:".$cursaldo.")");
							}else{
								throw new PDOException("Amount/Value not sufficient (transaction:" . $jumlah . " volume:" . $cursaldo.")");
							}
						}
						
						if($newhargarata <= 0 and $klbarang < '400'){
							if($_SESSION['language']=='ID'){
								throw new PDOException("Hargarata tidak dapat dibentuk pada ".$notransaksi." kodebarang :".$kodebarang);
							}else{
								throw new PDOException("Average price cannot be formed for " . $notransaksi . " material code :" . $kodebarang);
							}
						}else{
							if($brgaset == '9'){
								$newhargarata = '0';
								$newqtymasukxharga = '0';
								$newnilaisaldoakhir = '0';
							}
							$strupdate = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$newsaldoakhirqty."', hargarata='".$newhargarata."', nilaisaldoakhir='".$newnilaisaldoakhir."', lastuser='".$user."', qtymasuk='".$newqtymasuk."', qtymasukxharga='".$newqtymasukxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
						}
					}
				}
				
				## Prepare rollback penerimaan
				$strrollback = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$cursaldo."',  hargarata='".$hargarata."', nilaisaldoakhir='".$nilaisaldo."', lastuser='".$user."', qtymasuk='".$qtymasuk."', qtymasukxharga='".$qtymasukxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				## Prepare update masterbarangdt
				$instmaster = "insert into ".$dbname.".log_5masterbarangdt (kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, stockbataspesan, stockminimum, lastuser,kodegudang) values(
				'".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','".$newhargarata."','0','0','0','".$user."','".$gudang."')";
				
				// $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastin='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				// MISAL TIDAK ADA PERUBAHAN MAKA ROW 0, MAKA MENJALANKAN INSERT, BIAR GA DUPLICATE MAKA DI TAMBAHKAN UPDATE LASTUPDATE BIAR ROW 1, MAKA TIDAK DI JALANKAN INSERT
				$updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastin='".$newhargarata."',lastupdate='".date('Y-m-d H:i:s')."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				## Prepare jurnal
				## Ambil noakun supplier
				$akunspl = '';
				$kodekl = "SUPPLIER";
				$str = "select noakun from ".$dbname.".log_5supkelompok where noakun!=''";
				$res=fetchdata($str);
				$akunspl = $res[0]['noakun'];

				// cek apakah ada di klsup
				$str0 = "select tipesub from ".$dbname.".log_poht where nopo='".$nopo."' ";				
				$res0 = fetchData($str0);
				$tipesub = $res0[0]['tipesub'];	
				if($tipesub != ''){
					$str1 = "select noakun from ".$dbname.".log_5klsupplier where noakun!='' and tipe='".$tipesub."' ";
					$res1=fetchdata($str1);
					if(count($res1) > 0){
						// $akunspl = $res1[0]['noakun'];
					}
				}
				
				## Ambil noakun barang
				$klbarang = substr($kodebarang, 0, 3);
				$str = "select noakun,noakungit from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
				$res=fetchdata($str);
				$akunbarang = $res[0]['noakun'];
				
				
								
				
				$str = "select count(nopo) as jlhtransit from ".$dbname.".log_transit where nopo='".$nopo."'";
				// $str = "select count(nopo) as jlhtransit from ".$dbname.".log_transaksiht where nopo='".$nopo."' and kodegudang NOT LIKE '%HO%' ";

				$res=fetchdata($str);
				$jlhtransit = $res[0]['jlhtransit'];
				// // GRIR 2021
				if ((substr($kodebarang, 0, 3) < '400') and trim($akunbarang) != '') {
					// $noakungrir='2110501';
					if ($jlhtransit>0) {
						$akunspl=$noakunPDP;
					}
					else
					{
						$akunspl=$noakungrir;
					}
					
				}	
				// exit("warning : ".$akunspl." - ".$akunbarang." ");
				// end GRIR 2021

				if (($akunbarang == '' or $akunspl == '') and ( $klbarang < '400' or substr($kodebarang, 0, 1) == '9')){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Noakun  Noakun barang atau supplier  belum ada untuk transaksi ".$notransaksi."-".$akunbarang."-".$akunspl."-".$klbarang."-".$kodebarang);
					}else{
						throw new PDOException("Account no. for material or supplier not available yet for " . $notransaksi."-".$akunbarang."-".$akunspl."-".$klbarang."-".$kodebarang);
					}
				}
				
				if(($klbarang=='400')||(substr($klbarang,0,1)=='8')){
					throw new PDOException($_SESSION['lang']['kelompokbarang']." : ".$klbarang." Tidak Bisa Diterimakan");
				}
				
			
				
				## Cek Nilai Ppn di PO => diubah jadi ada data po-nya atau tidak, ppn sudah dipindah di tagihan
				$str = "select * from ".$dbname.".log_poht where nopo='".$nopo."'";
				$res = fetchData($str);
				if(count($res) <= 0){
					throw new PDOException("PO " . $nopo . " tidak terdaftar");
				}
				
				## Proses data
				$kodeJurnal = 'INVM1';
				// ambil RO-nya GRIR 2021
				// exit("error: ".$ronya." ".$akuncacoro."/".$akuncacoes." ".$close);
				
					##======================== Begin Nomor Jurnal =============================
					## Get Journal Counter
					$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' and periode='".$periode."' and kodeunit='".substr($gudang, 0, 4)."'";				
					$tmpKonter = fetchData($str);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);				
					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
					## Get Journal Counter GRIR 2021
					##======================== End Nomor Jurnal ===============================
					
					## Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => tanggalsystem($tanggal),
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $nilaitotal,
						'totalkredit' => -1 * $nilaitotal,
						'amountkoreksi' => '0',
						'noreferensi' => $notransaksi,
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);
					//  GRIR 2021
					
					
					## Data Detail
					$noUrut = 1;

				if ($hargaongkos!='') { // jika ada oongkos angkut
						## Debet
						$str = "select namasupplier from ".$dbname.".log_5supplier where supplierid='".$supplier."'";
						$res=fetchdata($str);
						$namasupplier = $res[0]['namasupplier'];
					// 'keterangan' => 'Pembelian barang ' . $namabarang . ' ' . $jumlah . " " . $satuan,
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $akunbarang,
							'keterangan' => 'barang: '.$kodebarang.', jumlah: '.$jumlah.", PO: ".$nopo.", vendor: ".$namasupplier,
							'jumlah' => $nilaitotal-$nilaipro,
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => substr($gudang, 0, 4),
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $supplier,
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => $nopo,
							'kodeblok' => $nopp,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;

					## Debet
						$str = "select namasupplier from ".$dbname.".log_5supplier where supplierid='".$supplier."'";
						$res=fetchdata($str);
						$namasupplier = $res[0]['namasupplier'];
					// 'keterangan' => 'Pembelian barang ' . $namabarang . ' ' . $jumlah . " " . $satuan,
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $akunbarang,
							'keterangan' => 'Penerimaan PO atas Jasa Ongkos Kirim barang: '.$kodebarang.', jumlah: '.$jumlah.", PO: ".$nopo.", vendor: ".$supplier_nama[$kodesupplierSO],
							'jumlah' => $nilaipro,
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => substr($gudang, 0, 4),
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $kodesupplierSO,
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => $nomorSonya,
							'kodeblok' => $nopp,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;

					## Kredit
					// kalo ini RO, langsung ke GRIR 2021
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $akunspl,
							'keterangan' => 'barang: '.$kodebarang.', jumlah: '.$jumlah.", PO: ".$nopo.", vendor: ".$namasupplier,
							'jumlah' => (-1) * ($nilaitotal - $nilaipro),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => substr($gudang, 0, 4),
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $supplier,
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => $nopo,
							'kodeblok' => $nopp,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;

					## Kredit
					// kalo ini RO, langsung ke GRIR 2021
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $akunspl,
							'keterangan' => 'Penerimaan PO atas Jasa Ongkos Kirim barang: '.$kodebarang.', jumlah: '.$jumlah.", PO: ".$nopo.", vendor: ".$supplier_nama[$kodesupplierSO],
							'jumlah' => (-1) * $nilaipro,
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => substr($gudang, 0, 4),
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $kodesupplierSO,
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => $nomorSonya,
							'kodeblok' => $nopp,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;
			
				}else{
					## Debet
					$str = "select namasupplier from ".$dbname.".log_5supplier where supplierid='".$supplier."'";
					$res=fetchdata($str);
					$namasupplier = $res[0]['namasupplier'];
						// 'keterangan' => 'Pembelian barang ' . $namabarang . ' ' . $jumlah . " " . $satuan,
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => tanggalsystem($tanggal),
						'nourut' => $noUrut,
						'noakun' => $akunbarang,
						'keterangan' => 'barang: '.$kodebarang.', jumlah: '.$jumlah.", PO: ".$nopo.", vendor: ".$namasupplier,
						'jumlah' => $nilaitotal,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => substr($gudang, 0, 4),
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => $kodebarang,
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $supplier,
						'noreferensi' => $notransaksi,
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => $nopo,
						'kodeblok' => $nopp,
						'revisi' => '0',
						'kodesegment' => $segment
					);
					$noUrut++;
					
					
					
					## Kredit
					// kalo ini RO, langsung ke GRIR 2021
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => tanggalsystem($tanggal),
						'nourut' => $noUrut,
						'noakun' => $akunspl,
						'keterangan' => 'barang: '.$kodebarang.', jumlah: '.$jumlah.", PO: ".$nopo.", vendor: ".$namasupplier,
						'jumlah' => (-1) * $nilaitotal,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => substr($gudang, 0, 4),
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => $kodebarang,
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $supplier,
						'noreferensi' => $notransaksi,
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => $nopo,
						'kodeblok' => $nopp,
						'revisi' => '0',
						'kodesegment' => $segment
					);
					$noUrut++;
				
				// baru sampe sini $dataResro['detail'][]

				// echo "<pre>";
				// print_r($dataRes);
				// print_r($dataResro);
				// echo "</pre>";
				// exit("error");
			}
				
				#========================================= 
				$updflagststussaldo = "update ".$dbname.".log_transaksidt set statussaldo='1',hargarata='".$newhargarata."', jumlahlalu='".$cursaldo."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."' and nopp='".$nopp."'";
				
				## execute
				// if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != '') {
				if ((substr($kodebarang, 0, 3) < '400') and trim($akunbarang) != '') {
					
					
						$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
						$owlPDO->exec($insHead); 					
						foreach ($dataRes['detail'] as $row) {
							$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
							$owlPDO->exec($insDet); 
						}
						// GRIR 2021
						## Header and Detail inserted
						## Update Kode Jurnal
						$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' and periode='".$periode."' and kodeunit='".substr($gudang, 0, 4)."'");
						$owlPDO->exec($updJurnal);
						// GRIR 2021
						// }
						
						## Berhasil di jurnal
					## Proses gudang
					$owlPDO->exec($strupdate);
					
					## Update masterbarangdt
					$affected_rows=$owlPDO->exec($updmaster);
					if (($affected_rows == 0)and($jumlah>0)){ // jumlah diterima 0, ga ngupdate masterbarangdt, affected=0, insert jadi error
						@$owlPDO->exec($instmaster); 
					}
					
					$owlPDO->exec($updflagststussaldo);
				}else{
					## Jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
					## Proses gudang
					$owlPDO->exec($strupdate);
					
					## Update masterbarangdt
					$affected_rows=$owlPDO->exec($updmaster);
					if (($affected_rows == 0)and($jumlah>0)){ // jumlah diterima 0, ga ngupdate masterbarangdt, affected=0, insert jadi error
						@$owlPDO->exec($instmaster); 
					}
					
					$owlPDO->exec($updflagststussaldo); 
				}
				
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Error, " . addslashes($e->getMessage());
			}
        }
		######################################
		#### End Penerimaan dari Supplier ####
		######################################
		
		
		#################################
		#### Begin Retur ke Supplier ####
		#################################
        if($tipetransaksi == '6'){
			try{
				$owlPDO->beginTransaction();
				
				## Periksa harga satuan
				if (intval($hargasatuan) == 0 or $nopo == '' or $supplier == '') {
					exit(" Error: price/PO/supplier not found");
				}
				
				## Generate saldo updater
				## Ambil saldo saat ini 
				// ROUND2021
				// $nilaitotal = $jumlah * $hargasatuan;
				$nilaitotal = round($jumlah * $hargasatuan);
				@$hargasatuan=$nilaitotal/$jumlah;
				$cursaldo = 0;
				$nilaisaldo = 0;
				$qtymasuk = 0;
				$qtymasukxharga = 0;
				$saldoakhirqty = 0;
				$nilaisaldoakhir = 0;
				$hargarata = 0;
				
				$str = "select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				$res=fetchdata($str);
				$numrows=count($res);
				if ($numrows < 1) {
					## Jika belum ada penerimaan sebelumnya
					$newhargarata = $hargasatuan;
					$newqtykeluar = $jumlah;
					$newqtykeluarxharga = $nilaitotal;
					$newsaldoakhirqty = $jumlah;
					$newnilaisaldoakhir = $nilaitotal;
				}
				
				## Bentuk harga baru
				foreach($res as $key=>$val){
					$cursaldo = $val['saldoakhirqty'];
					$nilaisaldo = $val['nilaisaldoakhir'];
					$qtykeluar = $val['qtykeluar'];
					$qtykeluarxharga = $val['qtykeluarxharga'];
					$hargarata = $val['hargarata'];
				}
				
				if (($cursaldo - $jumlah) <= 0){
					$newhargarata = $hargasatuan;
				}else{
					$newhargarata = @(($nilaisaldo - $nilaitotal) / ($cursaldo - $jumlah));
				}
				
				$newqtykeluar = $qtykeluar + $jumlah;
				$newqtykeluarxharga = $qtykeluarxharga + $nilaitotal;
				$newsaldoakhirqty = $cursaldo - $jumlah;
				$newnilaisaldoakhir = $nilaisaldo - $nilaitotal;
				
				if (($newsaldoakhirqty < 0)or($newnilaisaldoakhir < 0)){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Saldo/Nilai tidak mencukupi (retur:".$jumlah." saldo:".$cursaldo.")");
					}else{
						throw new PDOException("Amount/Value not sufficient (retur:" . $jumlah . " volume:" . $cursaldo.")");
					}
				}
				
				if ($newhargarata <= 0){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Hargarata tidak dapat dibentuk pada ".$notransaksi." kodebarang :".$kodebarang);
					}else{
						throw new PDOException("Average price can not be formed on " . $notransaksi . " material code :" . $kodebarang);
					}
				}else{
					$strupdate = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$newsaldoakhirqty."', hargarata='".$newhargarata."',nilaisaldoakhir='".$newnilaisaldoakhir."', lastuser='".$user."',qtykeluar='".$newqtykeluar."',qtykeluarxharga='".$newqtykeluarxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				}
				
				## Prepare rollback pengembalian
				$strrollback = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$cursaldo."', hargarata='".$hargarata."', nilaisaldoakhir='".$nilaisaldo."', lastuser='".$user."', qtykeluar='".$qtykeluar."', qtykeluarxharga='".$qtykeluarxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				## Prepare rollback pengembalian
				$instmaster = "insert into ".$dbname.".log_5masterbarangdt(kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, stockbataspesan, stockminimum, lastuser,kodegudang) values ('".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','".$newhargarata."','0','0','0','".$user."','".$gudang."')";
				
				// MISAL TIDAK ADA PERUBAHAN MAKA ROW 0, MAKA MENJALANKAN INSERT, BIAR GA DUPLICATE MAKA DI TAMBAHKAN UPDATE LASTUPDATE BIAR ROW 1, MAKA TIDAK DI JALANKAN INSERT
				// $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastout='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				$updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastout='".$newhargarata."',lastupdate='".date('Y-m-d H:i:s')."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				## Prepare jurnal
				$akunspl = '';

				## Ambil noakun supplier
				$kodekl = "SUPPLIER";
				$str = "select noakun from ".$dbname.".log_5supkelompok where tipe in ('SUPPLIER','PUPUK') and supplierid='".$supplier."'";
				$res=fetchdata($str);
				$akunspl = $res[0]['noakun'];
				
				## Ambil noakun barang
				$akunbarang = '';
				$klbarang = substr($kodebarang, 0, 3);
				$str = "select noakun,noakungit from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
				$res=fetchdata($str);
				$akunbarang = $res[0]['noakun'];

				// GRIR 2021
				if ((substr($kodebarang, 0, 3) < '400') and trim($akunbarang) != '') {
					// $noakungrir='2110501';
					// cek apakah ada di klsup
					$akunspl=$noakungrir;
					$str0 = "select tipesub from ".$dbname.".log_poht where nopo='".$nopo."' ";				
					$res0 = fetchData($str0);
					$tipesub = $res0[0]['tipesub'];	
					if($tipesub != ''){
						$str1 = "select noakun from ".$dbname.".log_5klsupplier where noakun!='' and tipe='".$tipesub."' ";
						$res1=fetchdata($str1);
						if(count($res1) > 0){
							// $akunspl = $res1[0]['noakun'];
						}
					}
				}	
				// end GRIR 2021				
				
				if (($akunbarang == '' or $akunspl == '') and ( $klbarang < '400' or substr($kodebarang, 0, 1) == '9')){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Noakun barang atau supplier  belum ada untuk transaksi ".$notransaksi);
					}else{
						throw new PDOException("Account number for material or supplier not available yet on " . $notransaksi);
					}
				}
				
				## Cek Nilai Ppn di PO => diubah jadi ada data po-nya atau tidak, ppn sudah dipindah di tagihan
				$str = "select * from ".$dbname.".log_poht where nopo='".$nopo."'";
				$res = fetchData($str);
				if (count($res) <= 0){
					throw new PDOException("PO " . $nopo . " tidak terdaftar");
				}
				// $nilaiPpn = $resPO[0]['ppn'] * $resPO[0]['kurs'] * ($nilaitotal / ($resPO[0]['kurs'] * ($resPO[0]['subtotal'] - $resPO[0]['nilaidiskon'])));
            
				## Proses data
				$kodeJurnal = 'INVK1';

				// ambil RO-nya GRIR 2021
				
					##======================== Begin Nomor Jurnal =============================
					## Get Journal Counter
					$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'";
					$tmpKonter = fetchData($str);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
					
					## Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
					## Get Journal Counter GRIR 2021
					
					##======================== End Nomor Jurnal ==============================
				
					## Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => tanggalsystem($tanggal),
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $nilaitotal,
						'totalkredit' => -1 * $nilaitotal,
						'amountkoreksi' => '0',
						'noreferensi' => $notransaksi,
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);
					

					## Data Detail
					$noUrut = 1;

					## Debet
					// GRIR 2021
					
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => tanggalsystem($tanggal),
						'nourut' => $noUrut,
						'noakun' => $akunspl,
						'keterangan' => 'ReturSupplier ' . $namabarang . ' ' . $jumlah . " " . $satuan,
						'jumlah' => $nilaitotal,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => substr($gudang, 0, 4),
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => $kodebarang,
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $supplier,
						'noreferensi' => $notransaksi,
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => $nopo,
						'kodeblok' => '',
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
						'keterangan' => 'ReturSupplier ' . $namabarang . ' ' . $jumlah . " " . $satuan,
						'jumlah' => -1 * $nilaitotal,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => substr($gudang, 0, 4),
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => $kodebarang,
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $supplier,
						'noreferensi' => $notransaksi,
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => $nopo,
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $segment
					);
					$noUrut++;

				// exit("error: ".$ronya." ".$akuncacoro."/".$akuncacoes." ".$close);
				

				##=========================================
				// ROUND2021
				// $updflagststussaldo = "update ".$dbname.".log_transaksidt set statussaldo='1',hargarata='".$newhargarata."',jumlahlalu='".$cursaldo."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."'";
				$updflagststussaldo = "update ".$dbname.".log_transaksidt set statussaldo='1',hargarata='".$hargasatuan."',jumlahlalu='".$cursaldo."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."'";
				
				## Execute
				// THIS IS
				if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
					$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($insHead); 
					
					foreach ($dataRes['detail'] as $row) {
						$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
						$owlPDO->exec($insDet); 
					}
					// GRIR 2021
					## Header and Detail inserted
					## Update Kode Jurnal
					$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'");
					$owlPDO->exec($updJurnal); 
					// GRIR 2021
						
					## Berhasil di jurnal
					## Proses gudang
					$owlPDO->exec($strupdate); 
					
					## Update masterbarangdt
					$affected_rows=$owlPDO->exec($updmaster);
					if ($affected_rows == 0){
						@$owlPDO->exec($instmaster); 
					}
					
					$owlPDO->exec($updflagststussaldo);
				}else{
					## Jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
					## Proses gudang
					$owlPDO->exec($strupdate); 
					
					## Update masterbarangdt
					$affected_rows=$owlPDO->exec($updmaster);
					if ($affected_rows == 0){
						@$owlPDO->exec($instmaster); 
					}
					
					$owlPDO->exec($updflagststussaldo); 
				}
				
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Error, " . addslashes($e->getMessage());
			}
        }
		###############################
		#### End Retur ke Supplier ####
		###############################
		
		
		######################################
		#### Begin Retur Barang ke Gudang ####
		######################################
        if ($tipetransaksi == '2') {
			try{
				$owlPDO->beginTransaction();
				
				## Ambil harga satuan dan saldo
				$hargarata = 0;
				$saldoakhirqty = 0;
				$nilaisaldoakhir = 0;
				$qtymasukxharga = 0;
				$qtymasuk = 0;
				
				$str = "select saldoakhirqty,hargarata,nilaisaldoakhir,qtymasuk,qtymasukxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				$res=fetchdata($str);
				foreach($res as $key=>$val){
					$oldhargarata = $val['hargarata'];
					$saldoakhirqty = $val['saldoakhirqty'];
					$nilaisaldoakhir = $val['nilaisaldoakhir'];
					$qtymasukxharga = $val['qtymasukxharga'];
					$qtymasuk = $val['qtymasuk'];
				}
				
				## Ambil trasaksi gudang
				$str = "select jumlah,hargasatuan as hargarata from ".$dbname.".log_transaksidt where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."'";
				$res=fetchdata($str);
				// ROUND2021
				$rpkembali = round($res[0]['hargarata'] * $res[0]['jumlah']);
				@$hargaround = $rpkembali/$jumlah;
				
				$newsaldoakhirqty = $saldoakhirqty + $jumlah;
				$hargarata = @(($nilaisaldoakhir + $rpkembali) / $newsaldoakhirqty);
				// ROUND2021
				$newhargarata = $hargaround; // $hargarata;
				$newnilaisaldoakhir = ($nilaisaldoakhir + $rpkembali);
				$newqtymasuk = $qtymasuk + $jumlah;
				$newqtymasukxharga = $qtymasukxharga + $rpkembali;

				if (($newsaldoakhirqty < 0)or($newnilaisaldoakhir < 0)){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Saldo/Nilai tidak mencukupi (retur:".$jumlah." saldo:".$cursaldo.")");
					}else{
						throw new PDOException("Amount/Value not sufficient (retur:" . $jumlah . " volume:" . $cursaldo.")");
					}
				}
				
				if ($hargarata <= 0){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Harga rata-rata belum ada");
					}else{
						throw new PDOException("Average price not available.");
					}
				}
				
				$strupdate = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$newsaldoakhirqty."',hargarata='".$hargarata."', nilaisaldoakhir='".$newnilaisaldoakhir."', lastuser='".$user."',qtymasuk='".$newqtymasuk."',qtymasukxharga='".$newqtymasukxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				## Prepare rollback penerimaan
				$strrollback = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$saldoakhirqty."',nilaisaldoakhir='".$nilaisaldoakhir."',lastuser='".$user."',qtymasuk='".$qtymasuk."',qtymasukxharga='".$qtymasukxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				## Prepare update masterbarangdt
				$instmaster = "insert into ".$dbname.".log_5masterbarangdt(kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, stockbataspesan, stockminimum, lastuser,kodegudang) values('".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','0','".$newhargarata."','0','0','".$user."','".$gudang."')";
				
				// MISAL TIDAK ADA PERUBAHAN MAKA ROW 0, MAKA MENJALANKAN INSERT, BIAR GA DUPLICATE MAKA DI TAMBAHKAN UPDATE LASTUPDATE BIAR ROW 1, MAKA TIDAK DI JALANKAN INSERT
				// $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastout='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				$updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastout='".$newhargarata."',lastupdate='".date('Y-m-d H:i:s')."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				if ($newhargarata <= 0) {
					if($_SESSION['language']=='ID'){
						throw new PDOException("Harga rata-rata belum ada");
					}else{
						throw new PDOException("Average price not available.");
					}
				}
				
				## Periksa apakah dari satu PT
				$pengguna = substr($_POST['untukunit'], 0, 4);
				
				$ptpengguna = '';
				$str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
				$res=fetchdata($str);
				
				$ptpengguna = $res[0]['induk'];
				
				$intraco = '';
				$interco = '';
				$str = "select akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$pengguna."'";
				$res=fetchdata($str);
				foreach($res as $key=>$val){
					if ($val['jenis'] == 'intra'){
						$intraco = $val['akunhutang'];
					}else{
						$interco = $val['akunhutang'];
					}
				}
				
				if ($intraco=='' || $interco=='') {
					throw new PDOException("Account intraco or interco not available for ".$pengguna);
				}
				
				$ptGudang = '';
				$str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudang, 0, 4)."'";
				$res=fetchdata($str);
				$ptGudang = $res[0]['induk'];
				
				## Jika pt tidak sama maka pakai akun interco
				$akunspl = '';
				if ($ptGudang != $ptpengguna) {
					## Ambil akun interco
					$akunspl = '';
					$str = "select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang, 0, 4)."' and jenis='inter'";
					$res=fetchdata($str);
					$akunspl = $res[0]['akunpiutang'];
					
					$inter = $interco;
					if ($akunspl == ''){
						if($_SESSION['language']=='ID'){
							throw new PDOException("Akun intraco  atau interco belum ada untuk unit ".$pengguna);
						}else{
							throw new PDOException("Account for intraco or interco not available yet for " . $pengguna);
						}
					}
				}else if ($pengguna != substr($gudang, 0, 4)){
					## Jika satu pt beda kebun
					## Ambil akun intraco
					$akunspl = '';
					$str = "select akunpiutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudang, 0, 4)."' and jenis='intra'";
					$res=fetchdata($str);
					$akunspl = $res[0]['akunpiutang'];
					
					$inter = $intraco;
					if ($akunspl == ''){
						if($_SESSION['language']=='ID'){
							throw new PDOException("Akun intraco  atau interco belum ada untuk unit ".$pengguna);
						}else{
							throw new PDOException("Account for intraco or interco not available yet for " . $pengguna);
						}
					}
				}
				
				## Ambil akun pekerjaan atau kendaraan atau ab
				## Periksa ke table setup blok
				$statustm = '';
				$str = "select statusblok from " . $dbname . ".setup_blok where kodeorg='" . $blok . "'";
				$res=fetchdata($str);
				$statustm = $res[0]['statusblok'];
				
				$akunpekerjaan = '';
				$str = "select noakun from " . $dbname . ".setup_kegiatan where kodekegiatan='" . $kodekegiatan . "'";
				$res=fetchdata($str);
				$akunpekerjaan = $res[0]['noakun'];
				
				## Jika akun kegiatan tidak ada maka exit
				if ($akunpekerjaan == ''){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Akun pekerjaan belum ada untuk kegiatan ".$kodekegiatan);
					}else{
						throw new PDOException("Account not available yet for activity " . $kodekegiatan);
					}
				}


				$kdsupblok='';
				$str = "select supplierid from " . $dbname . ".log_5supplier where supplierid='" . $blok . "'";
				$res=fetchdata($str);
				$kdsupblok = $res[0]['supplierid'];

				if($kdsupblok!=''){
					$kdsup=$blok;
					$kodebblok = '';
				}else{
					$kdsup='';
					$kodebblok = $blok;
					$blok=$blok;
				}

				
				## Ambil noakun barang
				$klbarang = substr($kodebarang, 0, 3);
				$str = "select noakun from " . $dbname . ".log_5klbarang where kode='" . $klbarang . "'";
				$res=fetchdata($str);
				$akunbarang = $res[0]['noakun'];
				
				if ($akunbarang == ''){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Noakun barang belum ada untuk transaksi".$notransaksi);
					}else{
						throw new PDOException("Material account not available yet on " . $notransaksi);
					}
				}else{
					$updflagststussaldo = "update " . $dbname . ". log_transaksidt set statussaldo='1',jumlahlalu='" . $saldoakhirqty . "', hargarata='" . $newhargarata . "' where notransaksi='" . $notransaksi . "' and kodebarang='" . $kodebarang . "' and kodeblok='" . $blok . "'";
					
					## Penggunaan internal
					if ($pengguna == substr($gudang, 0, 4)) {
						$kodeJurnal = 'INVM1';
						##======================== Begin Nomor Jurnal =============================
						## Get Journal Counter
						$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."' and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'";
						$tmpKonter = fetchData($str);
						$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
						
						## Transform No Jurnal dari No Transaksi
						$nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
						#======================== End Nomor Jurnal ================================
						
						## Prep Header
						$dataRes['header'] = array(
							'nojurnal' => $nojurnal,
							'kodejurnal' => $kodeJurnal,
							'tanggal' => tanggalsystem($tanggal),
							'tanggalentry' => date('Ymd'),
							'posting' => 1,
							'totaldebet' => ($rpkembali),
							'totalkredit' => (-1 * $rpkembali),
							'amountkoreksi' => '0',
							'noreferensi' => $notransaksi,
							'autojurnal' => '1',
							'matauang' => 'IDR',
							'kurs' => '1',
							'revisi' => '0'
						);

						## Data Detail
						$noUrut = 1;
						$keterangan = "ReturGudang barang " . $namabarang . " " . $jumlah . " " . $satuan;
						
						## Debet
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $akunbarang,
							'keterangan' => $keterangan,
							'jumlah' => ($rpkembali),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => substr($gudang, 0, 4),
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $kdsup,
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => $kodemesin,
							'nodok' => '',
							'kodeblok' => $kodebblok,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;

						## Kredit
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $akunpekerjaan,
							'keterangan' => $keterangan,
							'jumlah' => (-1 * $rpkembali),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => substr($gudang, 0, 4),
							'kodekegiatan' => $kodekegiatan,
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $kdsup,
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => $kodemesin,
							'nodok' => '',
							'kodeblok' => $kodebblok,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;
						
						
						if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
							$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
							$owlPDO->exec($insHead); 
						
							foreach ($dataRes['detail'] as $row){
                                $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
									$owlPDO->exec($insDet); 
                            }
							
							## Header and Detail inserted
							## Update Kode Jurnal
                            $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptpengguna."' and kodekelompok='" . $kodeJurnal . "' and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'");
							$owlPDO->exec($updJurnal); 
							
							## Berhasil di jurnal
							## Proses gudang
							$owlPDO->exec($strupdate); 
							
							## Update masterbarangdt
							$affected_rows=$owlPDO->exec($updmaster);
							
							if ($affected_rows == 0){
								$owlPDO->exec($instmaster); 
							}
							$owlPDO->exec($updflagststussaldo);
						}else{
							## Jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
							## Proses gudang
							$owlPDO->exec($strupdate);
							
                            ## Update masterbarangdt
							$affected_rows=$owlPDO->exec($updmaster);
							if ($affected_rows == 0) {
								$owlPDO->exec($instmaster); 
							}
							
							$owlPDO->exec($updflagststussaldo); 
						}
					}else{
						## Jika inter atau intraco 
						## Proses data sisi pemilik
						$kodeJurnal = 'INVM1';
						
						##======================== Begin Nomor Jurnal =============================
						## Get Journal Counter
						$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."' and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'";
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
							'totaldebet' => ($rpkembali),
							'totalkredit' => (-1 * $rpkembali),
							'amountkoreksi' => '0',
							'noreferensi' => $notransaksi,
							'autojurnal' => '1',
							'matauang' => 'IDR',
							'kurs' => '1',
							'revisi' => '0'
						);

						## Data Detail
						$noUrut = 1;
						$keterangan = "ReturGudang barang " . $namabarang . " " . $jumlah . " " . $satuan;
						$keterangan = substr($keterangan, 0, 150);
						
						## Debet
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $akunbarang,
							'keterangan' => $keterangan,
							'jumlah' => ($rpkembali),
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
							'nodok' => '',
							'kodeblok' => '',
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;

						## Kredit
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $inter,
							'keterangan' => $keterangan,
							'jumlah' => (-1 * $rpkembali),
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
							'nodok' => '',
							'kodeblok' => '',
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
							$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptGudang."' and kodekelompok='".$kodeJurnal."'  and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'");
							$owlPDO->exec($updJurnal);
							
						}
						
						## Proses data sisi pengguna
						$kodeJurnal = 'INVM1';
						
						##======================== Begin Nomor Jurnal =============================
						## Ambil tanggal terkecil periode pengguna
						$tanggalsana = '';
						$stri = "select tanggalmulai from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $pengguna . "' and tutupbuku=0";
						$res=fetchdata($stri);
						foreach($res as $key=>$val){
							$tanggalsana = str_replace("-","",$val['tanggalmulai']);
						}
					
						if($tanggalsana<=tanggalsystem($tanggal)){
							$tanggalsana = tanggalsystem($tanggal);							
						}else{
							## Rollback header sisi pemilik
							$RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $header1pemilik . "'");
							$owlPDO->exec($RBDet); 
							throw new PDOException("Receivers accounting period not the same as warehouse.");
						}
						
						## Get Journal Counter
						$str = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'  and periode='".$periode."'  and kodeunit='".substr($pengguna, 0, 4)."'");
						$tmpKonter = fetchData($str);
						$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
						
						## Transform No Jurnal dari No Transaksi
						$nojurnal = str_replace("-", "", $tanggalsana) . "/" . $pengguna . "/" . $kodeJurnal . "/" . $konter;
						##======================== End Nomor Jurnal ============================
						
						## Prep Header
						
						## Ganti header
						unset($dataRes['header']);
						$dataRes['header'] = array(
							'nojurnal' => $nojurnal,
							'kodejurnal' => $kodeJurnal,
							'tanggal' => $tanggalsana,
							'tanggalentry' => date('Ymd'),
							'posting' => 1,
							'totaldebet' => ($rpkembali),
							'totalkredit' => (-1 * $rpkembali),
							'amountkoreksi' => '0',
							'noreferensi' => $notransaksi,
							'autojurnal' => '1',
							'matauang' => 'IDR',
							'kurs' => '1',
							'revisi' => '0'
						);

						## Data Detail
						$keterangan = "ReturGudang barang " . $namabarang . " " . $jumlah . " " . $satuan;
						$keterangan = substr($keterangan, 0, 150);
						$noUrut = 1;
						unset($dataRes['detail']);
						
						## Debet
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggalsana,
							'nourut' => $noUrut,
							'noakun' => $akunspl,
							'keterangan' => $keterangan,
							'jumlah' => ($rpkembali),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $pengguna,
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => $kodemesin,
							'nodok' => '',
							'kodeblok' => $blok,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;

						## Kredit
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggalsana,
							'nourut' => $noUrut,
							'noakun' => $akunpekerjaan,
							'keterangan' => $keterangan,
							'jumlah' => (-1 * $rpkembali),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $pengguna,
							'kodekegiatan' => $kodekegiatan,
							'kodeasset' => '',
							'kodebarang' => $kodebarang,
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => $notransaksi,
							'noaruskas' => '',
							'kodevhc' => $kodemesin,
							'nodok' => '',
							'kodeblok' => $blok,
							'revisi' => '0',
							'kodesegment' => $segment
						);
						$noUrut++;
						
						## EXECUTE
						if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
							$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
							$owlPDO->exec($insHead); 
						
							foreach ($dataRes['detail'] as $row) {
                                $insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
								$owlPDO->exec($insDet); 
                            }
							
							## Header and Detail inserted
							## Update Kode Jurnal
							// $updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptpengguna."' and kodekelompok='" . $kodeJurnal . "'  and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'");
							$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptpengguna."' and kodekelompok='" . $kodeJurnal . "'  and periode='".$periode."'  and kodeunit='".substr($pengguna, 0, 4)."'");
							$owlPDO->exec($updJurnal); 
							
							## Berhasil di jurnal
							## Proses gudang
							$owlPDO->exec($strupdate); 
							
							## Update masterbarangdt
							$affected_rows=$owlPDO->exec($updmaster);
							if ($affected_rows == 0) {
								@$owlPDO->exec($instmaster); 
							}
							
							$owlPDO->exec($updflagststussaldo); 
						}else{
							## Jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
							## Proses gudang
							$owlPDO->exec($strupdate); 
							
							## Update masterbarangdt
							$affected_rows=$owlPDO->exec($updmaster);
							if ($affected_rows == 0){
								@$owlPDO->exec($instmaster); 
							}
							
							$owlPDO->exec($updflagststussaldo); 
						}
					}
				}
				
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Error, " . addslashes($e->getMessage());
			}
        }
		####################################
		#### End Retur Barang ke Gudang ####
		####################################
		
		
		########################################
		#### Begin Penerimaan Mutasi Gudang ####
		########################################
		// ROUND 2021 penerimaan mutasi tidak bisa diround karena ada hubungan hutang unit. seharusnya sudah round, tapi untuk transaksi lama masih mungkin koma
		if ($tipetransaksi == '3'){
			try{
				$owlPDO->beginTransaction();
				
				## Ambil harga satuan dan saldo
				$hargarata = 0;
				$saldoakhirqty = 0;
				$nilaisaldoakhir = 0;
				$qtymasukxharga = 0;
				$qtymasuk = 0;
				// ROUND2021 khusus penerimaan mutasi memang harusnya tidak dibulatkan tapi karena mengambil harga pengirim, saat harga 17766.666666667 x 3 = 53300.000000001. untuk itu, digunakanlah round(angka,5)
				$nilaitotal = round($jumlah * $hargasatuan,5);
				
				$str = "select saldoakhirqty,hargarata,nilaisaldoakhir,qtymasuk,qtymasukxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				$res=fetchdata($str);
				$numrows=count($res);
				if($numrows < 1){
					## Jika belum ada penerimaan sebelumnya
					$newhargarata = $hargasatuan;
					$newqtymasuk = $jumlah;
					$newqtymasukxharga = $nilaitotal;
					$newsaldoakhirqty = $jumlah;
					$newnilaisaldoakhir = $nilaitotal;
                
					$strupdate = "insert into ".$dbname.".log_5saldobulanan (kodeorg, kodebarang, saldoakhirqty, hargarata, lastuser,periode, nilaisaldoakhir, kodegudang, qtymasuk, qtykeluar, qtymasukxharga, qtykeluarxharga, saldoawalqty, hargaratasaldoawal, nilaisaldoawal) values ('".$kodept."','".$kodebarang."','".$newqtymasuk."','".$newhargarata."','".$user."','".$periode."','".$newqtymasukxharga."','".$gudang."','".$newsaldoakhirqty."','0','".$newnilaisaldoakhir."','0','0','0','0')";
				}else{
					foreach($res as $key=>$val){
						$hargarata = $val['hargarata'];
						$saldoakhirqty = $val['saldoakhirqty'];
						$nilaisaldoakhir = $val['nilaisaldoakhir'];
						$qtymasukxharga = $val['qtymasukxharga'];
						$qtymasuk = $val['qtymasuk'];
					}
					
					$newsaldoakhirqty    = $saldoakhirqty + $jumlah;
					$newhargarata       = @(($nilaitotal + $nilaisaldoakhir) / ($newsaldoakhirqty));
					$newnilaisaldoakhir  = $nilaitotal + $nilaisaldoakhir;
					$newqtymasuk         = $qtymasuk + $jumlah;
					$newqtymasukxharga   = $qtymasukxharga + $nilaitotal;

					if (($newsaldoakhirqty < 0)or($newnilaisaldoakhir < 0)){
						if($_SESSION['language']=='ID'){
							throw new PDOException("Saldo/Nilai tidak mencukupi (transaksi:".$jumlah." saldo:".$saldoakhirqty.")");
						}else{
							throw new PDOException("Amount/Value not sufficient (transaction:" . $jumlah . " volume:" . $saldoakhirqty.")");
						}
					}
					
					## Menggunakan harga rata-rata pada saat itu, bukan harga pada saat dikeluarkan 
					if($newhargarata == 0 or $newhargarata == ''){
						if($_SESSION['language']=='ID'){
							throw new PDOException("Hargarata tidak dapat dibentuk pada ".$notransaksi." kodebarang :".$kodebarang);
						}else{
							throw new PDOException("Average price cannot be formed on " . $notransaksi . " material code :" . $kodebarang);
						}
					}else{
						$strupdate = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='". $newsaldoakhirqty."', hargarata='".$newhargarata."',nilaisaldoakhir='".$newnilaisaldoakhir."', lastuser='".$user."', qtymasuk='".$newqtymasuk."', qtymasukxharga='".$newqtymasukxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
					}
				}
				
				if ($newhargarata == '0') {
					if($_SESSION['language']=='ID'){
						throw new PDOException("Harga rata-rata tidak dapat dibentuk.");
					}else{
						throw new PDOException("Average price cannot be created.");
					}
				}
				
				## Prepare rollback penerimaan
				$strrollback = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$saldoakhirqty."', nilaisaldoakhir='".$nilaisaldoakhir."', lastuser='".$user."', qtymasuk='".$qtymasuk."', qtymasukxharga='".$qtymasukxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				## Prepare update masterbarangdt
				$instmaster = "insert into ".$dbname.".log_5masterbarangdt(kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, stockbataspesan, stockminimum, lastuser,kodegudang) values ('".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','0','".$newhargarata."','0','0','".$user."','".$gudang."')";
				
				// MISAL TIDAK ADA PERUBAHAN MAKA ROW 0, MAKA MENJALANKAN INSERT, BIAR GA DUPLICATE MAKA DI TAMBAHKAN UPDATE LASTUPDATE BIAR ROW 1, MAKA TIDAK DI JALANKAN INSERT
				$updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastin='".$newhargarata."',lastupdate='".date('Y-m-d H:i:s')."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				// $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastin='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				## Periksa apakah dari satu PT
				$pengguna = substr($gudang, 0, 4);
				$ptpengguna = '';
				$str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$pengguna."'";
				$res=fetchdata($str);
				$ptpengguna = $res[0]['induk']; ## Gudang ASAL
				
				$ptGudang = '';
				$str = "select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($gudangx,0,4)."'";
				$res=fetchdata($str);
				$ptGudang = $res[0]['induk']; ## Gudang TUJUAN
				
				$akunspl = '';
				
				## Jika pt tidak sama maka pakai akun interco
				if($ptGudang != $ptpengguna){
					## Ambil akun interco
					$str = "select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudangx,0,4)."' and jenis='inter'";
					$res=fetchdata($str);
					$akunspl = $res[0]['akunhutang'];
					
					if ($akunspl == ''){
						if($_SESSION['language']=='ID'){
							throw new PDOException("Akun intraco  atau interco belum ada untuk unit ".substr($gudangx,0,4));
						}else{
							throw new PDOException("Account intraco or interco not available for " . substr($gudangx, 0, 4));
						}
					}
				}else if ($pengguna != substr($gudangx, 0, 4)){
					## Jika satu pt beda Unit
					## Ambil akun intraco
					$str = "select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($gudangx,0,4)."' and jenis='intra'";
					$res=fetchdata($str);
					$akunspl = $res[0]['akunhutang'];
					
					if ($akunspl == ''){
						if($_SESSION['language']=='ID'){
							throw new PDOException("Akun intraco  atau interco belum ada untuk unit ".substr($gudangx,0,4));
						}else{
							throw new PDOException("Account intraco or interco not available for " . substr($gudangx, 0, 4));
						}
					}
				}
				
				## Ambil noakun barang
				$klbarang = substr($kodebarang, 0, 3);
				$str = "select noakun,noakungit from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
				$res=fetchdata($str);
				// $akunbarang = $res[0]['noakun'];
				if($tipeorganisasi[$kodeunit]=='KANWIL'){
					$akunbarang = $res[0]['noakungit'];
				}else{
					$akunbarang = $res[0]['noakun'];
				}
				
				if ($akunbarang == ''){
					if($_SESSION['language']=='ID'){
						throw new PDOException("No Akun barang belum ada untuk transaksi ".$notransaksi);
					}else{
						throw new PDOException("Account for material not available for ".$notransaksi);
					}
				}else{
					$updflagststussaldo = "update ".$dbname.".log_transaksidt set statussaldo='1', jumlahlalu='".$saldoakhirqty."', hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."'";
					
					## Proses data sisi pengguna
					$kodeJurnal = 'INVM1';
					
					##======================== Begin Nomor Jurnal =============================
					## Get Journal Counter
					$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'  and periode='".$periode."'  and kodeunit='".substr($pengguna, 0, 4)."'";
					$tmpKonter = fetchData($str);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
					
					## Transform No Jurnal dari No Transaksi
					$nojurnal = tanggalsystem($tanggal) . "/" . $pengguna . "/" . $kodeJurnal . "/" . $konter;
					##======================== End Nomor Jurnal ===============================
					
					
					## Ganti header
					unset($dataRes['header']); 
					
					## Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => tanggalsystem($tanggal),
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $nilaitotal,
						'totalkredit' => (-1 * $nilaitotal),
						'amountkoreksi' => '0',
						'noreferensi' => $notransaksi,
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);
					
					## Data Detail
					$keterangan = "Terima Mutasi barang " . $namabarang . " " . $jumlah . " " . $satuan;
					$keterangan = substr($keterangan, 0, 150);
					$noUrut = 1;
					
					## Ganti detail 
					unset($dataRes['detail']);
					
					## Debet
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => tanggalsystem($tanggal),
						'nourut' => $noUrut,
						'noakun' => $akunbarang,
						'keterangan' => $keterangan,
						'jumlah' => $nilaitotal,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $pengguna,
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => $kodebarang,
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => $notransaksi,
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $segment
					);
					$noUrut++;
					
					## Kredit
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => tanggalsystem($tanggal),
						'nourut' => $noUrut,
						'noakun' => $akunspl,
						'keterangan' => $keterangan,
						'jumlah' => (-1 * $nilaitotal),
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $pengguna,
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => $kodebarang,
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => $notransaksi,
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $segment
					);
					$noUrut++;
					
					
					if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != '' and ( substr($pengguna, 0, 4) != substr($gudangx, 0, 4))){
						## Hanya barang stok yang dijurnal dan mutasi keluar kebun
						$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
						$owlPDO->exec($insHead);
						
						foreach ($dataRes['detail'] as $row){
							$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
							$owlPDO->exec($insDet); 
						}
						
						## Header and Detail inserted
						## Update Kode Jurnal
						$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptpengguna."' and kodekelompok='".$kodeJurnal."'  and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'");
						$owlPDO->exec($updJurnal);
						
						## Jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
						## Proses gudang
						$owlPDO->exec($strupdate);
						
						## Update masterbarangdt
						$affected_rows=$owlPDO->exec($updmaster);	
						if ($affected_rows == 0){
							@$owlPDO->exec($instmaster); 
						}
						
						$owlPDO->exec($updflagststussaldo);
					}else{
						## Jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
						## Proses gudang
						$owlPDO->exec($strupdate); 
						
						## Update masterbarangdt
						$affected_rows=$owlPDO->exec($updmaster);
						if ($affected_rows == 0) {
							@$owlPDO->exec($instmaster); 
						}
						
						$owlPDO->exec($updflagststussaldo);
					}
                }
			
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Error, " . addslashes($e->getMessage());
			}
        } 
		######################################
		#### End Penerimaan Mutasi Gudang ####
		######################################
		
		
		#########################################
		#### Begin Pengeluaran Mutasi Gudang ####
		#########################################
		if ($tipetransaksi == '7'){
			try{
				$owlPDO->beginTransaction();
				
				## Ambil harga satuan dan saldo
				$hargarata = 0;
				$saldoakhirqty = 0;
				$nilaisaldoakhir = 0;
				$qtykeluarxharga = 0;
				$qtykeluar = 0;
				
				$str = "select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from " . $dbname . ".log_5saldobulanan where periode='" . $periode . "' and kodegudang='" . $gudang . "' and kodebarang='" . $kodebarang . "' and kodeorg='" . $kodept . "'";
				$res=fetchdata($str);
				foreach($res as $key=>$val){
					$hargarata      = $val['hargarata'];
					$saldoakhirqty  = $val['saldoakhirqty'];
					$nilaisaldoakhir= $val['nilaisaldoakhir'];
					$qtykeluarxharga= $val['qtykeluarxharga'];
					$qtykeluar      = $val['qtykeluar'];
					$hargarata2     = $nilaisaldoakhir/$saldoakhirqty;				}

				// ROUND2021
				$rpmutasi=round($jumlah * $hargarata);
				if($jumlah==$saldoakhirqty){ // kalo barang terakhir rupiah difloor aja biar ga minus nilai akhirnya
					$hargarata=$hargarata2;
					$rpmutasi=floor($jumlah * $hargarata);
				}				
				@$hargaround = $rpmutasi/$jumlah;
				
				if ($hargarata <= 0) {
					if($_SESSION['language']=='ID'){
						throw new PDOException("Harga rata-rata belum ada");
					}else{
						throw new PDOException("Average price not available");
					}
				}
				
				
				$newsaldoakhirqty    = $saldoakhirqty - $jumlah;
				$newhargarata        = $hargaround; // $hargarata;
				$newnilaisaldoakhir  = $nilaisaldoakhir - ($rpmutasi);
				$newqtykeluar        = $qtykeluar + $jumlah;
				$newqtykeluarxharga  = $qtykeluarxharga + ($rpmutasi);
				
				#jika harga rata-rata baru == 0 sementara fisik masih ada maka pakai harga sebelumnya
				if($hargaround==0 and $newnilaisaldoakhir>0 and $newsaldoakhirqty>0){
					$newhargarata    = $hargarata; // $hargarata;
				}
				
				
				if (($newsaldoakhirqty < 0)or($newnilaisaldoakhir < 0)){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Saldo/Nilai tidak mencukupi (transaksi:".$jumlah." saldo:".$saldoakhirqty.")");
					}else{
						throw new PDOException("Amount/Value not sufficient (transaction:" . $jumlah . " volume:" . $saldoakhirqty.")");
					}
				}
				
				if (($newhargarata < 0)){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Harga rata-rata tidak dapat dibentuk.");
					}else{
						throw new PDOException("Average price cannot be created.");
					}
				}

				$strupdate = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$newsaldoakhirqty."',nilaisaldoakhir='".$newnilaisaldoakhir."',lastuser='".$user."',qtykeluar='".$newqtykeluar."',qtykeluarxharga='".$newqtykeluarxharga."',hargarata='".$newhargarata."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				## Prepare rollback penerimaan
				$strrollback = "update ".$dbname. ".log_5saldobulanan set saldoakhirqty='".$saldoakhirqty."',nilaisaldoakhir='".$nilaisaldoakhir."',lastuser='".$user."',qtykeluar='".$qtykeluar."',qtykeluarxharga='".$qtykeluarxharga."',hargarata='".$hargarata."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";

				##prepare update masterbarangdt
				$instmaster = "insert into ".$dbname.".log_5masterbarangdt(kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, stockbataspesan, stockminimum, lastuser,kodegudang) values('".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','0','".$newhargarata."','0','0','".$user."','".$gudang."')";
				
				// $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."',hargalastout='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				// MISAL TIDAK ADA PERUBAHAN MAKA ROW 0, MAKA MENJALANKAN INSERT, BIAR GA DUPLICATE MAKA DI TAMBAHKAN UPDATE LASTUPDATE BIAR ROW 1, MAKA TIDAK DI JALANKAN INSERT
				$updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastout='".$newhargarata."',lastupdate='".date('Y-m-d H:i:s')."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";

				## Periksa apakah dari satu PT
				## Gudang tujuan
				$pengguna = substr($gudangx, 0, 4);
				
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
					throw new PDOException("Account intraco or interco not available for ".$pengguna);
				}   
				
				$ptGudang = '';
				$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . substr($gudang, 0, 4) . "'";
				$res=fetchdata($str);
				$ptGudang = $res[0]['induk'];
				
				## Jika pt tidak sama maka pakai akun interco
				$akunspl = '';
				if ($ptGudang != $ptpengguna) {
					## Ambil akun interco
					$str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . substr($gudang, 0, 4) . "' and jenis='inter'";
					$res=fetchdata($str);
					$akunspl = $res[0]['akunhutang'];
					$inter = $interco;
					
					if ($akunspl == ''){
						if($_SESSION['language']=='ID'){
							throw new PDOException("Akun intraco  atau interco belum ada untuk unit ".substr($gudang, 0, 4));
						}else{
							throw new PDOException("Account intraco or interco not available for " . substr($gudang, 0, 4));
						}
					}
				}else if ($pengguna != substr($gudang, 0, 4)) { 
					## Jika satu pt beda kebun
					## Ambil akun intraco
					$str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . substr($gudang, 0, 4) . "' and jenis='intra'";
					$res=fetchdata($str);
					$akunspl = $res[0]['akunhutang'];
					$inter = $intraco;
					if ($akunspl == ''){
						if($_SESSION['language']=='ID'){
							throw new PDOException("Akun intraco  atau interco belum ada untuk unit ".substr($gudang, 0, 4));
						}else{
							throw new PDOException("Account intraco or interco not available for " . substr($gudang, 0, 4));
						}
					}
				}
				
				
				## Ambil noakun barang
				$akunbarang = '';
				$klbarang = substr($kodebarang, 0, 3);
				$str = "select noakun,noakungit from " . $dbname . ".log_5klbarang where kode='" . $klbarang . "'";
				$res=fetchdata($str);
				// $akunbarang = $res[0]['noakun'];
				if($tipeorganisasi[$kodeunit]=='KANWIL'){
					$akunbarang = $res[0]['noakungit'];
				}else{
					$akunbarang = $res[0]['noakun'];
				}
				
				if ($akunbarang == ''){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Noakun barang belum ada untuk transaksi".$notransaksi);
					}else{
						throw new PDOException("Account for material not available for " . $notransaksi);
					}					
				}else{
					$updflagststussaldo = "update ".$dbname.".log_transaksidt set statussaldo='1',jumlahlalu='".$saldoakhirqty."',hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."'";
					## Mutasi antar gudang internal tidak menggunakan jurnal
					if ($pengguna == substr($gudang, 0, 4)) {
						## Proses gudang
						$owlPDO->exec($strupdate);
						
						## Update masterbarangdt
						$affected_rows=$owlPDO->exec($updmaster);
						if ($affected_rows == 0){
							@$owlPDO->exec($instmaster); 
						}
						
						$owlPDO->exec($updflagststussaldo); 
					}else{
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
							'totaldebet' => ($rpmutasi),
							'totalkredit' => (-1 * $rpmutasi),
							'amountkoreksi' => '0',
							'noreferensi' => $notransaksi,
							'autojurnal' => '1',
							'matauang' => 'IDR',
							'kurs' => '1',
							'revisi' => '0'
						);

						## Data Detail
						$noUrut = 1;
						$keterangan = "Mutasi barang " . $namabarang . " " . $jumlah . " " . $satuan;
						$keterangan = substr($keterangan, 0, 150);
						
						## Debet
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => tanggalsystem($tanggal),
							'nourut' => $noUrut,
							'noakun' => $inter,
							'keterangan' => $keterangan,
							'jumlah' => ($rpmutasi),
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
							'nodok' => '',
							'kodeblok' => '',
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
							'jumlah' => (-1 * $rpmutasi),
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
							'nodok' => '',
							'kodeblok' => '',
							'revisi' => '0',
							'kodesegment' => $segment
						);

						
						if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != ''){
							$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
							$owlPDO->exec($insHead); 
							
							foreach ($dataRes['detail'] as $row) {
								$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
								$owlPDO->exec($insDet); 
							}
							
							## Header and Detail inserted
							## Update Kode Jurnal
							$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$ptGudang."' and kodekelompok='" . $kodeJurnal . "'  and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'");
							$owlPDO->exec($updJurnal);
							
							## Berhasil di jurnal
							## Proses gudang
							$owlPDO->exec($strupdate);
							## Update masterbarangdt
							$affected_rows=$owlPDO->exec($updmaster);
							if ($affected_rows == 0){
								@$owlPDO->exec($instmaster); 
							}
							
							$owlPDO->exec($updflagststussaldo); 
							// if ($affected_rows == 0){
							// 	@$owlPDO->exec($instmaster); 
							// }
							
							// $owlPDO->exec($updflagststussaldo); 
						}
					}
				}
				
				
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Error, " . addslashes($e->getMessage());
			}
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
				
				## Ambil harga satuan dan saldo
				$hargarata = 0;
				$saldoakhirqty = 0;
				$nilaisaldoakhir = 0;
				$qtykeluarxharga = 0;
				$qtykeluar = 0;
				
				$str = "select saldoakhirqty,hargarata,nilaisaldoakhir,qtykeluar,qtykeluarxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				$res=fetchdata($str);
				foreach($res as $key=>$val){
					$hargarata = $val['hargarata'];
					$saldoakhirqty = $val['saldoakhirqty'];
					$nilaisaldoakhir = $val['nilaisaldoakhir'];
					$qtykeluarxharga = $val['qtykeluarxharga'];
					$qtykeluar = $val['qtykeluar'];
					$hargarata2 = $val['nilaisaldoakhir']/$val['saldoakhirqty'];
				}
				
				// ROUND2021

				// cek apakah barang tersebut ada di setup_barangstatusblok, karena banyak terproporsi, sehingga nilai jadi koma, agar tidak 0 nilai tersebut maka di buat kondisi
				$sData_a="select * from ".$dbname.".setup_barangstatusblok where kodebarang='".$kodebarang."' and status='1' ";
				$rData_a=fetchdata($sData_a);
				if(count($rData_a)>0){
					$rpkeluar=$jumlah * $hargarata;
				}else{
					$rpkeluar=round($jumlah * $hargarata);
				}


				if($jumlah==$saldoakhirqty){ // kalo barang terakhir rupiah difloor aja biar ga minus nilai akhirnya
					// kalo barang terakhir update harga ratanya
					$hargarata=$hargarata2;
					// cek apakah barang tersebut ada di setup_barangstatusblok, karena banyak terproporsi, sehingga nilai jadi koma, agar tidak 0 nilai tersebut maka di buat kondisi
					$sData_a="select * from ".$dbname.".setup_barangstatusblok where kodebarang='".$kodebarang."' and status='1' ";
					$rData_a=fetchdata($sData_a);
					if(count($rData_a)>0){
						$rpkeluar=$jumlah * $hargarata;
					}else{
						$rpkeluar=floor($jumlah * $hargarata);
					}
				}
				@$hargaround = $rpkeluar/$jumlah;

				$klbarang = substr($kodebarang, 0, 3);
				if ($hargarata <= 0 and $klbarang < '400') {
					throw new PDOException("harga rata-rata belum ada");
				}
				
				$newsaldoakhirqty    = $saldoakhirqty - $jumlah;
				// ROUND2021
				$newhargarata        = $hargaround; // $hargarata;
				$newnilaisaldoakhir  = $nilaisaldoakhir - ($rpkeluar);
				$newqtykeluar        = $qtykeluar + $jumlah;
				$newqtykeluarxharga  = $qtykeluarxharga + ($rpkeluar);
				// $newhargarata        = $newnilaisaldoakhir/$newsaldoakhirqty; // $hargarata;
				
				// if ($newsaldoakhirqty < 0){
				// 	throw new PDOException("Amount not sufficient\n\nSaldo tidak cukup");
				// }
				if (($newsaldoakhirqty < 0)or($newnilaisaldoakhir < 0)){
					if($_SESSION['language']=='ID'){
						throw new PDOException("Saldo/Nilai tidak mencukupi (transaksi:".$jumlah."(".$rpkeluar.") saldo:".$saldoakhirqty." (".$nilaisaldoakhir.") )");
					}else{
						throw new PDOException("Amount/Value not sufficient (transaction:" . $jumlah . " volume:" . $saldoakhirqty.")");
					}
				}

				if (($newhargarata < 0)){ 
					if($_SESSION['language']=='ID'){
						throw new PDOException("Harga rata-rata tidak dapat dibentuk.");
					}else{
						throw new PDOException("Average price cannot be created.");
					}
				}

				if($rpkeluar <= 0){
					throw new PDOException("Rupiah Tidak Boleh 0");
				}
				if($jumlah <= 0){
					throw new PDOException("Jumlah Tidak Boleh 0");
				}

				// ROUND2021 update hargarata
				$strupdate = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$newsaldoakhirqty."',nilaisaldoakhir='".$newnilaisaldoakhir."',lastuser='".$user."',qtykeluar='".$newqtykeluar."',qtykeluarxharga='".$newqtykeluarxharga."',hargarata='".$newhargarata."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
				## Prepare rollback penerimaan
				$strrollback = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$saldoakhirqty."',nilaisaldoakhir='".$nilaisaldoakhir."',lastuser='".$user."',qtykeluar='".$qtykeluar."',qtykeluarxharga='".$qtykeluarxharga."',hargarata='".$hargarata."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";

				## Prepare update masterbarangdt
				$instmaster = "insert into ".$dbname.".log_5masterbarangdt(kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, stockbataspesan, stockminimum, lastuser,kodegudang) values ('".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','0','".$newhargarata."','0','0','".$user."','".$gudang."')";
				
				// $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastout='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				// MISAL TIDAK ADA PERUBAHAN MAKA ROW 0, MAKA MENJALANKAN INSERT, BIAR GA DUPLICATE MAKA DI TAMBAHKAN UPDATE LASTUPDATE BIAR ROW 1, MAKA TIDAK DI JALANKAN INSERT
				$updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastout='".$newhargarata."',lastupdate='".date('Y-m-d H:i:s')."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
				
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
					if (substr($kodeasset, 0, 2) == 'AK' or substr($kodeasset, 0, 2) == 'PB' or $cek_blok[1] == 'SPK') {
						// $updflagststussaldo = "update ".$dbname.".log_transaksidt set statussaldo='1', jumlahlalu='".$saldoakhirqty."', hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$kodeasset."' and kodemesin ='".$kodemesin."' and kodekegiatan ='".$kodekegiatan."'";
						$updflagststussaldo = "update ".$dbname.".log_transaksidt_detail set statussaldo='1', jumlahlalu='".$saldoakhirqty."', hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$kodeasset."' and kodemesin ='".$kodemesin."' and kodekegiatan ='".$kodekegiatan."'";
						$updflagststussaldo_dt = "update ".$dbname.".log_transaksidt set statussaldo='1', jumlahlalu='".$saldoakhirqty."', hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$kodeasset."' and kodemesin ='".$kodemesin."' and kodekegiatan ='".$kodekegiatan."'";
					}else{
						// $updflagststussaldo = "update ".$dbname.".log_transaksidt set statussaldo='1', jumlahlalu='".$saldoakhirqty."', hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."' and kodemesin ='".$kodemesin."' and kodekegiatan ='".$kodekegiatan."'";

						// cek apakah blok atau bukan
						$str_c_blok = "select distinct tipe from ".$dbname.".organisasi where kodeorganisasi='".$blok."' order by kodeorganisasi limit 1";
						$res_c_blok=fetchdata($str_c_blok);
						$tipe_cek = $res_c_blok[0]['tipe'];
						if($tipe_cek == 'BLOK'){

							$updflagststussaldo = "update ".$dbname.".log_transaksidt_detail set statussaldo='1', jumlahlalu='".$saldoakhirqty."', hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."' and kodemesin ='".$kodemesin."' and kodekegiatan ='".$kodekegiatan."'";
							
							$str_c_adaUpdate = "select * from ".$dbname.".log_transaksidt where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".substr($blok,0,9)."' and kodemesin ='".$kodemesin."' and kodekegiatan ='".$kodekegiatan."' limit 1";
							$res_c_adaUpdate = fetchdata($str_c_adaUpdate);
							if(count($res_c_adaUpdate) > 0){
								$updflagststussaldo_dt = "update ".$dbname.".log_transaksidt set statussaldo='1', jumlahlalu='".$saldoakhirqty."', hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".substr($blok,0,9)."' and kodemesin ='".$kodemesin."' and kodekegiatan ='".$kodekegiatan."'";
							}else{
								$updflagststussaldo_dt = "update ".$dbname.".log_transaksidt set statussaldo='1', jumlahlalu='".$saldoakhirqty."', hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".substr($blok,0,6)."' and kodemesin ='".$kodemesin."' and kodekegiatan ='".$kodekegiatan."'";
							}
							
						}else{
							$updflagststussaldo = "update ".$dbname.".log_transaksidt_detail set statussaldo='1', jumlahlalu='".$saldoakhirqty."', hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."' and kodemesin ='".$kodemesin."' and kodekegiatan ='".$kodekegiatan."'";
							$updflagststussaldo_dt = "update ".$dbname.".log_transaksidt set statussaldo='1', jumlahlalu='".$saldoakhirqty."', hargarata='".$newhargarata."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."' and kodeblok='".$blok."' and kodemesin ='".$kodemesin."' and kodekegiatan ='".$kodekegiatan."'";
						}
					}
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
							
							## Berhasil di jurnal
							## Proses gudang							
							$owlPDO->exec($strupdate); 
							
							## Update masterbarangdt
							$affected_rows=$owlPDO->exec($updmaster);
							if ($affected_rows == 0){
								@$owlPDO->exec($instmaster); 
							}
							$owlPDO->exec($updflagststussaldo);
							$owlPDO->exec($updflagststussaldo_dt);
						}else{
							## Jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
							## Proses gudang
							$owlPDO->exec($strupdate); 
							
							## Update masterbarangdt
							$affected_rows=$owlPDO->exec($updmaster);
							if ($affected_rows == 0){
								@$owlPDO->exec($instmaster); 
							}
							$owlPDO->exec($updflagststussaldo);
							$owlPDO->exec($updflagststussaldo_dt);
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
							
							## Berhasil di jurnal
							## Proses gudang
							$owlPDO->exec($strupdate);
							## Update masterbarangdt
							$affected_rows=$owlPDO->exec($updmaster);
							if ($affected_rows == 0){
								@$owlPDO->exec($instmaster); 
							}
							$owlPDO->exec($updflagststussaldo);
							$owlPDO->exec($updflagststussaldo_dt);
						} else {
							## Jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
							## Proses gudang
							$owlPDO->exec($strupdate); 
							
							## Update masterbarangdt
							$affected_rows=$owlPDO->exec($updmaster);
							if ($affected_rows == 0){
								@$owlPDO->exec($instmaster); 
							}
							$owlPDO->exec($updflagststussaldo); 
							$owlPDO->exec($updflagststussaldo_dt);
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

#########################################
#### BEGIN PENERIMAAN DARI PABRIKASI ####
#########################################
if($tipetransaksi == '0'){
	try{
		$owlPDO->beginTransaction();
		
		## Periksa harga satuan
		if (intval($hargasatuan) == 0 or $kdpabrikasi == '') {
            throw new PDOException("Price/Kode Fabrication not found.");
		}
		
		## Generate saldo updater
		## Ambil saldo saat ini
		$nilaitotal = $jumlah * $hargasatuan;
		$cursaldo = 0;
		$nilaisaldo = 0;
		$qtymasuk = 0;
		$qtymasukxharga = 0;
		$saldoakhirqty = 0;
		$nilaisaldoakhir = 0;
		$hargarata = 0;
		
		$str = "select saldoakhirqty,hargarata,nilaisaldoakhir,qtymasuk,qtymasukxharga from ".$dbname.".log_5saldobulanan where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
		$res=fetchdata($str);
		$numrows=count($res);
		if ($numrows < 1) {
			## Jika belum ada penerimaan sebelumnya
			$newhargarata = $hargasatuan;
			$newqtymasuk = $jumlah;
			$newqtymasukxharga = $nilaitotal;
			$newsaldoakhirqty = $jumlah;
			$newnilaisaldoakhir = $nilaitotal;
			$strupdate = "insert into ".$dbname.".log_5saldobulanan (kodeorg, kodebarang, saldoakhirqty, hargarata, lastuser,periode, nilaisaldoakhir, kodegudang, qtymasuk, qtykeluar, qtymasukxharga, qtykeluarxharga, saldoawalqty, hargaratasaldoawal, nilaisaldoawal) values ('".$kodept."','".$kodebarang."','".$newqtymasuk."','".$newhargarata."','".$user."','".$periode."','".$newqtymasukxharga."','".$gudang."','".$newsaldoakhirqty."','0','".$newnilaisaldoakhir."','0','0','0','0')";
		}else{
			## Bentuk harga baru
			foreach ($res as $key=>$val){
				$cursaldo = $val['saldoakhirqty'];
				$nilaisaldo = $val['nilaisaldoakhir'];
				$qtymasuk = $val['qtymasuk'];
				$qtymasukxharga = $val['qtymasukxharga'];
				$hargarata = $val['hargarata'];
			}
			
			$newhargarata = @(($nilaitotal + $nilaisaldo) / ($jumlah + $cursaldo));
			$newqtymasuk = $qtymasuk + $jumlah;
            $newqtymasukxharga = $qtymasukxharga + $nilaitotal;
			$newsaldoakhirqty = $jumlah + $cursaldo;
			$newnilaisaldoakhir = ($nilaitotal + $nilaisaldo);
			
			if($newhargarata <= 0){
				throw new PDOException("Average price cannot be formed for " . $notransaksi . " material code :" . $kodebarang);
			}else{
				$strupdate = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$newsaldoakhirqty."', hargarata='".$newhargarata."',nilaisaldoakhir='".$newnilaisaldoakhir."', lastuser='".$user."',qtymasuk='".$newqtymasuk."',qtymasukxharga='".$newqtymasukxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
			}
		}
		
		## Prepare update masterbarangdt
		$strrollback = "update ".$dbname.".log_5saldobulanan set saldoakhirqty='".$cursaldo."', hargarata='". $hargarata."',nilaisaldoakhir='".$nilaisaldo."',lastuser='".$user."',qtymasuk='".$qtymasuk."',qtymasukxharga='".$qtymasukxharga."' where periode='".$periode."' and kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
		
		## Prepare update masterbarangdt
		$instmaster = "insert into ".$dbname.".log_5masterbarangdt(kodeorg, kodebarang, saldoqty, hargalastin, hargalastout, stockbataspesan, stockminimum, lastuser,kodegudang) values ('".$kodept."','".$kodebarang."','".$newsaldoakhirqty."','".$newhargarata."','0','0','0','".$user."','".$gudang."')";
		
		// MISAL TIDAK ADA PERUBAHAN MAKA ROW 0, MAKA MENJALANKAN INSERT, BIAR GA DUPLICATE MAKA DI TAMBAHKAN UPDATE LASTUPDATE BIAR ROW 1, MAKA TIDAK DI JALANKAN INSERT
		$updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastin='".$newhargarata."',lastupdate='".date('Y-m-d H:i:s')."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
		// $updmaster = "update ".$dbname.".log_5masterbarangdt set saldoqty='".$newsaldoakhirqty."', hargalastin='".$newhargarata."' where kodegudang='".$gudang."' and kodebarang='".$kodebarang."' and kodeorg='".$kodept."'";
		
		## Prepare jurnal
		## Ambil noakun supplier
		$akunspl = '';
		$kodekl = substr($supplier, 0, 4);
		$str = "select noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='PBR2'";
        $res=fetchdata($str);
		$akunspl = $res[0]['noakunkredit'];
		
		## Ambil noakun barang
		$akunbarang = '';
		$klbarang = substr($kodebarang, 0, 3);
		$str = "select noakun from ".$dbname.".log_5klbarang where kode='".$klbarang."'";
        $res=fetchdata($str);
		$akunbarang = $res[0]['noakun'];
		
		if (($akunbarang == '' or $akunspl == '') and ( $klbarang < '400' or substr($kodebarang, 0, 1) == '9')){
			throw new PDOException("Account no. for material or supplier not available yet for " . $notransaksi);
		}
		
		## Cek Nilai Ppn di PO
		$str = "select * from ".$dbname.".pabrikasi_5masterht where kodepabrikasi='".$kdpabrikasi."'";
		$res = fetchdata($str);
		if(count($res) <= 0){
			throw new PDOException("PO ".$kdpabrikasi." tidak terdaftar");
		}
		
		## Proses data
		$kodeJurnal = 'INVM0';
		
		#======================== Begin Nomor Jurnal =============================#
        ## Get Journal Counter
		$str="select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'";
		$res = fetchData($str);
		$konter = addZero($res[0]['nokounter'] + 1, 3);
		
		## Transform No Jurnal dari No Transaksi
		$nojurnal = str_replace("-", "", tanggalsystem($tanggal)) . "/" . substr($gudang, 0, 4) . "/" . $kodeJurnal . "/" . $konter;
		#======================== End Nomor Jurnal ============================
		
		## Prep Header
		$dataRes['header'] = array(
			'nojurnal' => $nojurnal,
			'kodejurnal' => $kodeJurnal,
			'tanggal' => tanggalsystem($tanggal),
			'tanggalentry' => date('Ymd'),
			'posting' => 1,
			'totaldebet' => $nilaitotal,
			'totalkredit' => -1 * $nilaitotal,
			'amountkoreksi' => '0',
			'noreferensi' => $notransaksi,
			'autojurnal' => '1',
			'matauang' => 'IDR',
			'kurs' => '1',
			'revisi' => '0'
		);
		
		## Data Detail
		$noUrut = 1;
		
		## Debet
		$dataRes['detail'][] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => tanggalsystem($tanggal),
			'nourut' => $noUrut,
			'noakun' => $akunbarang,
			'keterangan' => 'Peneriaman barang pabrikasi ' . $namabarang . ' ' . $jumlah . " " . $satuan,
			'jumlah' => $nilaitotal,
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
			'nodok' => '',
			'kodeblok' => $kdpabrikasi,
			'revisi' => '0',
			'kodesegment' => $segment
		);
		$noUrut++;


		## Kredit
		$dataRes['detail'][] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => tanggalsystem($tanggal),
			'nourut' => $noUrut,
			'noakun' => $akunspl,
			'keterangan' => 'Peneriaman barang pabrikasi ' . $namabarang . ' ' . $jumlah . " " . $satuan,
			'jumlah' => (-1 * $nilaitotal),
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
			'nodok' => '',
			'kodeblok' => $kdpabrikasi,
			'revisi' => '0',
			'kodesegment' => $segment
		);
		$noUrut++;

		#=========================================
		$updflagststussaldo = "update ".$dbname.".log_transaksidt set statussaldo='1',hargarata='".$newhargarata."',jumlahlalu='".$cursaldo."' where notransaksi='".$notransaksi."' and kodebarang='".$kodebarang."'";
		#==================================execute
		
		if ((substr($kodebarang, 0, 3) < '400' or substr($kodebarang, 0, 1) == '9') and trim($akunbarang) != '') {
			$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
            $owlPDO->exec($insHead); 
			
			foreach ($dataRes['detail'] as $row) {
				$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);
				$owlPDO->exec($insDet); 
			}
			
			## Header and Detail inserted
			## Update Kode Jurnal
			$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' and periode='".$periode."'  and kodeunit='".substr($gudang, 0, 4)."'");
			$owlPDO->exec($updJurnal); 
			
			#berhasil di jurnal
			#proses gudang
			$owlPDO->exec($strupdate);
			
			#update masterbarangdt
			$affected_rows=$owlPDO->exec($updmaster);
			if($affected_rows == 0){
				@$owlPDO->exec($instmaster); 
			}        
		}else{
			## Jika aktiva hanya proses data gudang saja tanpa masuk ke jurnal
			## Proses gudang
			$owlPDO->exec($strupdate);
            
			## Update masterbarangdt        
            $affected_rows=$owlPDO->exec($updmaster);
			if ($affected_rows == 0){
				@$owlPDO->exec($instmaster); 
			}
			
			$owlPDO->exec($updflagststussaldo); 
		}
		$owlPDO->commit();
	}catch(PDOException $e){
		$owlPDO->rollback();
		echo "Error, ".addslashes($e->getMessage());
		die();
	}
} 
#######################################
#### END PENERIMAAN DARI PABRIKASI ####
#######################################
?>