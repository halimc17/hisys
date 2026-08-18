<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$notransaksi=checkPostGet('notransaksi','');
$prd=checkPostGet('prd','');
$unit=checkPostGet('unit','');
$keg=checkPostGet('keg','');
$kontanan=checkPostGet('kontanan','');
$periodecek=str_replace("-","", $prd);
$nmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

#========================= Validasi Data ===========================
#1. Cek Prd Akuntansi
$str="select * from ".$dbname.".setup_periodeakuntansi where periode = '".$prd."' and kodeorg='".$unit."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
if($bar['tutupbuku']=='1'){
	exit('Error : Periode Akuntansi Sudah di Tutup.');
}

#2. Cek periode vs sesion prd
if($_SESSION['org']['period']['tahun'].$_SESSION['org']['period']['bulan']>$periodecek){
	exit('Error : Periode diluar periode aktif');
}

#3. Cek Prd Gaji
// $str="select * from ".$dbname.".sdm_5periodegaji where periode = '".$prd."' and kodeorg='".$unit."'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// $bar=$res->fetch();
// if($bar['sudahproses']=='0'){
	// exit('Error : Periode Gaji belum di Tutup / di Proses.');
// }

#4. Cek apakah transaksi spb sudah di posting semua
$list=array();
$str="select * from ".$dbname.".kebun_spbht where	tanggal like '".$prd."%' and nospb like '%".$afd."%' and kodeorg='".$unit."' and posting='0' and tujuan!='4'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
if($row>0){
	// exit('Error : Ada transaksi SPB yang belum di Posting, silahkan ulangi proses.');

	$str="select * from ".$dbname.".kebun_spbht where	tanggal like '".$prd."%' and nospb like '%".$afd."%' and kodeorg='".$unit."' and posting='0' and tujuan!='4'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$list[$bar['nospb']][$bar['tanggal']] = $bar['nospb'];
	}

	$ambil_nospb = '';
	foreach ($list as $nospbx => $arrTanggal) {
		foreach ($arrTanggal as $tanggalx => $val) {
			if($ambil_nospb == ''){
				$ambil_nospb = $nospbx;
			}else{
				$ambil_nospb .= ",".$nospbx;
			}
		}
	}

	exit('Warning : Ada transaksi SPB yang belum di Posting : '. $ambil_nospb);
}

#========================= End Validasi Data ===========================


#============================ Ambil Data ===============================

	if(getindukPT($unit)=='CAR' or getindukPT($unit)=='LAN'){
		$dataunitx='';
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR' and tipe = 'KEBUN'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' and tipe = 'KEBUN' ";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}
	}

	if(getindukPT($unit)=='DMA' or getindukPT($unit)=='MHA'){
		$dataunitx='';
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA' and tipe = 'KEBUN'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}

		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA' and tipe = 'KEBUN'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($dataunitx==""){
				$dataunitx.="'".$val['kodeorganisasi']."'";				
			}else{
				$dataunitx.=",'".$val['kodeorganisasi']."'";				
			}
		}
	}

$blokx=$kgwb=$tkgwb=array();

$str="select * from ".$dbname.".kebun_spb_vw2 where tanggal like '".$prd."%' and kodeorg in (".$dataunitx.") and posting='1'"; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$blokx[$bar['nospb']][$bar['blok']]=$bar['blok'];
	$kgwb[$bar['nospb']][$bar['blok']]+=$bar['kgwb'];
	$tkgwb[$bar['nospb']]+=$bar['kgwb'];
}

# ambil datakaryawan
# Cek Datakaryawan History
$jumlahkaryhist=0;
$str = "select count(karyawanid) as jlh from ".$dbname.".datakaryawan_hist where 5=5 and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$prd."' "; 
$res = fetchdata($str);
$jumlahkaryhist=$res[0]['jlh'];

if($jumlahkaryhist > 0){
	$db_karyawan = "datakaryawan_hist";
	$where_kar = "and version_type='B' and periodegaji='".$prd."'";

}else{
	$db_karyawan = "datakaryawan";
	$where_kar = "";
}

#ambil data dari kebun_3premibmtbs
$trpplb=$trphk=0;
$Listkeg=$listkar=$rpplb=$rphk=$rplb=array();
$listkar_lokasitugas=$listkar_perusahaan=array();
$str="select a.*,b.lokasitugas,b.kodeorganisasi from ".$dbname.".kebun_3premibmtbs a left join ".$dbname.".".$db_karyawan." b on a.karyawanid=b.karyawanid where a.notransaksi = '".$notransaksi."' and a.kegiatan='".$keg."' and a.kontanan='".$kontanan."' ".$where_kar." order by a.nospb ASC, a.karyawanid ASC";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$trpplb+=$bar['rppremi'];
	$trphk+=$bar['rphk'];
	$listkar[$bar['nospb']][$bar['karyawanid']]=$bar['karyawanid'];
	$rpplb[$bar['nospb']][$bar['karyawanid']]+=$bar['rppremi'];
	$rphk[$bar['nospb']][$bar['karyawanid']]+=$bar['rphk'];
	$rplb[$bar['nospb']][$bar['karyawanid']]+=$bar['rphk']+$bar['rppremi'];

	$listkar_lokasitugas[$bar['nospb']][$bar['karyawanid']]=$bar['lokasitugas'];
	$listkar_perusahaan[$bar['nospb']][$bar['karyawanid']]=$bar['kodeorganisasi'];
}

$trplb=$trpplb+$trphk;
#========================= End Ambil Data ==============================

#=========================== Nomor Jurnal ==============================
$tglEntry = date('Ymd');
$kodeJurnal = 'BM01';
$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='PNN' and jurnalid='".$kodeJurnal."'");
$resParam = fetchData($queryParam);

# Get Journal Counter
$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeunit='".$unit."' and kodekelompok='".$kodeJurnal."' and periode='".$prd."'");
$tmpKonter = fetchData($queryJ);
$konter = addZero($tmpKonter[0]['nokounter']+1,3);

# Validasi jika nomor konter sudah 1000
if($konter>999){
	exit('Error : Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = '.$_SESSION['org']['kodeorganisasi'].', Kode Kelompok = BM01');
}

# Transform No Jurnal dari No Transaksi
$tmpNoJurnal = explode('/',$notransaksi);
$tmpKodeOrg = substr($tmpNoJurnal[1],0,4);
$nojurnal = $tmpNoJurnal[0]."/".$tmpKodeOrg."/".$kodeJurnal."/".$konter;


#======================== End Nomor Jurnal =============================

#=========================== Insert Data ===============================
$errorDB='';


# insert Header => keu_jurnalht
$str="insert into ".$dbname.".keu_jurnalht 
(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)
values 
('".$nojurnal."','".$kodeJurnal."','".$tmpNoJurnal[0]."','".$tglEntry."','0','".$trplb."',
'".$trplb*(-1)."','0','".$notransaksi."','1','IDR','1','0')";
try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= "Detail Header 1 : ".$e->getMessage()."\n";}
//echo 'TOTAL.'.$trplb.'<br>';

$no=$nork=$rupiahKr=0;
$all_organisasi_rk= array();

foreach ($listkar as $spb => $ky1){
	foreach ($ky1 as $kary => $vl) {
		foreach($blokx[$spb] as $blok =>$val){
			$jrplb[$spb][$blok]=($kgwb[$spb][$blok]/$tkgwb[$spb])*$rplb[$spb][$kary];
			// echo 'karyawanid: '.$kary.' === nospb: '.$spb.' === blok: '.$blok.' === Rupiah Lebih Basis: '.$jrplb[$spb][$blok];
			// echo $jrplb[$spb][$blok];
			// echo "<br>";
			
			## Kumpulan Organisasi RK
			if($listkar_lokasitugas[$spb][$kary] != $tmpKodeOrg){
				$all_organisasi_rk[$listkar_lokasitugas[$spb][$kary]]=$listkar_lokasitugas[$spb][$kary];
			}

			$no+=1;
			# insert debet => keu_jurnaldt
			$str="insert into ".$dbname.".keu_jurnaldt 
				(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
				`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
				`kodeblok`,`revisi`,`kodesegment`)
				values ('".$nojurnal."','".$tmpNoJurnal[0]."','".$no."','".substr($keg,0,7)."',
				'Upah/Premi Muat Buah ".$blok." no spb ".$spb." ','".$jrplb[$spb][$blok]."','IDR','1','".$tmpKodeOrg."',
				'".$keg."','','','".$kary."','','','".$notransaksi."','','','','".$blok."','0','0000000001')";
			try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= "Detail Debet 1 : ".$e->getMessage()."\n";}
			$rupiahKr+=$jrplb[$spb][$blok];
			//echo 'KR.'.$rupiahKr.'<br>';
		}
	}
}

## RK
$rupiahKr_RK = 0;
if(count($all_organisasi_rk) > 0){
	foreach ($all_organisasi_rk as $organisasi) {
		$rupiahKr_RK = 0;

		## HT
		## Jurnal RK
			$kodeJurnalRK = 'M';
			# Get Journal Counter
			$queryRK = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeunit='".$organisasi."' and kodekelompok='".$kodeJurnalRK."' and periode='".$prd."'");
			$tmpKonterRK = fetchData($queryRK);
			$konterRK = addZero($tmpKonterRK[0]['nokounter']+1,3);

			# Validasi jika nomor konter sudah 1000
			if($konterRK>999){
				exit('Error : Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = '.$_SESSION['org']['kodeorganisasi'].', Kode Kelompok = M');
			}

			# Transform No Jurnal dari No Transaksi
			$tmpNoJurnalRK = explode('/',$notransaksi);
			$tmpKodeOrgRK = $organisasi;
			$nojurnalRK = $tmpNoJurnal[0]."/".$tmpKodeOrgRK."/".$kodeJurnalRK."/".$konterRK;

			# insert Header => keu_jurnalht
			$str="insert into ".$dbname.".keu_jurnalht 
				(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
				`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)
			values 
				('".$nojurnalRK."','".$kodeJurnalRK."','".$tmpNoJurnalRK[0]."','".$tglEntry."','0','".$trplb."',
				'".$trplb*(-1)."','0','".$notransaksi."','1','IDR','1','0')";
			try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= "Detail Header 2 : ".$e->getMessage()."\n";}

			foreach ($listkar as $spb => $ky1){
				foreach ($ky1 as $kary => $vl) {
					foreach($blokx[$spb] as $blok =>$val){
						$jrplb[$spb][$blok]=($kgwb[$spb][$blok]/$tkgwb[$spb])*$rplb[$spb][$kary];

						if($listkar_lokasitugas[$spb][$kary] == $organisasi){
							$nork++;
							# insert debet => RK DEBET
							$str="insert into ".$dbname.".keu_jurnaldt 
								(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
								`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
								`kodeblok`,`revisi`,`kodesegment`)
								values ('".$nojurnalRK."','".$tmpNoJurnalRK[0]."','".$nork."','".$resParam[0]['noakunkredit']."',
								'RK Upah/Premi Muat Buah ".$blok." no spb ".$spb." ','".$jrplb[$spb][$blok]*(-1)."','IDR','1','".$listkar_lokasitugas[$spb][$kary]."',
								'".$keg."','','','".$kary."','','','".$notransaksi."','','','','".$blok."','0','0000000001')";
							try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= "Detail Debet 2 : ".$e->getMessage()."\n";}
							$rupiahKr_RK+=$jrplb[$spb][$blok];
						}
					}
				}
			}


			if(getindukPT($organisasi)!=getindukPT($tmpKodeOrg)){
				$jenis="inter";
			}else if(getindukPT($organisasi)==getindukPT($tmpKodeOrg)){
				$jenis="intra";    
			}

			$aknPt=makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',"kodeorg='".$tmpKodeOrg."' and jenis='".$jenis."'");
			$aknHtg=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$organisasi."' and jenis='".$jenis."'");   


			$no++;
			# insert kredit => KREDIT RK
			$str="insert into ".$dbname.".keu_jurnaldt 
				(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
				`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
				`kodeblok`,`revisi`,`kodesegment`)
				values ('".$nojurnal."','".$tmpNoJurnal[0]."','".$no."','".$aknPt[$tmpKodeOrg]."',
				'RK Upah/Premi Muat Buah ".$unit." prd ".$prd."','".($rupiahKr_RK*(-1))."','IDR','1','".$tmpKodeOrg."',
				'".$keg."','','','','','','".$notransaksi."','','','','','0','0000000001')";
			try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= "Detail Kredit RK 1".$tmpKodeOrg." : ".$e->getMessage()."\n";}


			$nork++;
			# insert Debit => DEBIT RK
			$str="insert into ".$dbname.".keu_jurnaldt 
				(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
				`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
				`kodeblok`,`revisi`,`kodesegment`)
				values ('".$nojurnalRK."','".$tmpNoJurnal[0]."','".$nork."','".$aknHtg[$organisasi]."',
				'RK Upah/Premi Muat Buah ".$organisasi." prd ".$prd."','".$rupiahKr_RK."','IDR','1','".$organisasi."',
				'".$keg."','','','','','','".$notransaksi."','','','','','0','0000000001')";
			try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= "Detail Debit RK 1 ".$organisasi." : ".$e->getMessage()."\n";}
			$rupiahKr_RK2+=$rupiahKr_RK;

			# Update Counter
			$str="update ".$dbname.".keu_5kelompokjurnal set nokounter = '".$konterRK."' where kodeunit='".$organisasi."' and kodekelompok='".$kodeJurnalRK."' and periode = '".$prd."'";
			try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Update Konter !: " . $e->getMessage() . "\n"; die();}
	}

	
	$no++;
	# insert kredit => GAK RK
	$str="insert into ".$dbname.".keu_jurnaldt 
		(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
		`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
		`kodeblok`,`revisi`,`kodesegment`)
		values ('".$nojurnal."','".$tmpNoJurnal[0]."','".$no."','".$resParam[0]['noakunkredit']."',
		'Upah/Premi Muat Buah ".$unit." prd ".$prd."','".(($rupiahKr - $rupiahKr_RK2) *(-1))."','IDR','1','".$tmpKodeOrg."',
		'".$keg."','','','','','','".$notransaksi."','','','','','0','0000000001')";
	try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= "Detail Kredit 1 : ".$e->getMessage()."\n";}
	
}else{

	$no++;
	# insert kredit => GAK RK
	$str="insert into ".$dbname.".keu_jurnaldt 
		(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
		`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
		`kodeblok`,`revisi`,`kodesegment`)
		values ('".$nojurnal."','".$tmpNoJurnal[0]."','".$no."','".$resParam[0]['noakunkredit']."',
		'Upah/Premi Muat Buah ".$unit." prd ".$prd."','".$rupiahKr*(-1)."','IDR','1','".$tmpKodeOrg."',
		'".$keg."','','','','','','".$notransaksi."','','','','','0','0000000001')";
	try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= "Detail Kredit 2 : ".$e->getMessage()."\n";}
	//echo $str;
}

//exit('error');

#======================== End Insert Data =============================
#======================== Cek data jurnal =============================
$str="select sum(debet) as rpj from ".$dbname.".keu_jurnaldt_vw where	nojurnal = '".$nojurnal."' and noreferensi='".$notransaksi."'"; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$rpj=$bar['rpj'];
$varian=($rpj-$trplb);
if($varian > 2 or $varian < (-2)){
	$errorDB.="Nilai Jurnal tidak sama dengan nilai transaksi , nilai jurnal : ".$rpj." dan nilai trans : ".$trplb;
}

#======================== End data jurnal =============================
#=========================== Roll Back ================================
if($errorDB!=''){
	$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."' and noreferensi = '".$notransaksi."' ";
	try{$owlPDO->exec($str); } catch(PDOException $e){print " Roll Back !: " . $e->getMessage() . "\n"; die();}

	$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnalRK."' and noreferensi = '".$notransaksi."'";
	try{$owlPDO->exec($str); } catch(PDOException $e){print " Roll Back !: " . $e->getMessage() . "\n"; die();}

	exit('ERROR : Posting gagal di lakukan => '.$errorDB);	
}

#========================= End Roll Back ===============================

#============================= Update ==================================
# Update Counter
$str="update ".$dbname.".keu_5kelompokjurnal set nokounter = '".$konter."' where kodeunit='".$unit."' and kodekelompok='".$kodeJurnal."' and periode = '".$prd."'";
try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Update Konter !: " . $e->getMessage() . "\n"; die();}

# Update flag transaksi
$str="update ".$dbname.".kebun_3premibmtbs set posting='1', jurnal = '".$nojurnal."', postingby ='".$_SESSION['standard']['userid']."', postingdate='".$tglEntry."' where notransaksi='".$notransaksi."' and kegiatan='".$keg."' and kontanan='".$kontanan."'";
try{$owlPDO->exec($str); } catch(PDOException $e){$errorDB.= " Update Flag !: " . $e->getMessage() . "\n"; die();}

#=========================== End Update ===============================
?>