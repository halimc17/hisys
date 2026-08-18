<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$notransaksi=checkPostGet('notransaksi','');

$prd=checkPostGet('prd','');
$unit=checkPostGet('unit','');

try {
$owlPDO->beginTransaction();
	
#========================= Validasi Data ===========================
#1. Cek Prd Akuntansi
$str="select * from ".$dbname.".setup_periodeakuntansi where periode = '".$prd."' and kodeorg='".$unit."' and tutupbuku='1'";
$res = fetchData($str);
if(count($res)>0){
	throw new PDOException("Periode Akuntansi Sudah di Tutup.");
}

#2. Cek periode vs sesion prd
if($_SESSION['org']['period']['tahun']."-".$_SESSION['org']['period']['bulan']>$prd){
	throw new PDOException("Periode diluar periode aktif !\nPeriode Aktif => ".$_SESSION['org']['period']['tahun']."-".$_SESSION['org']['period']['bulan']."\nPeriode Transaksi => ".$prd."");
}

#3. Cek Prd Gaji
$str="select * from ".$dbname.".sdm_5periodegaji where periode = '".$prd."' and kodeorg='".$unit."'  and sudahproses='1'";
$res = fetchData($str);
if(count($res)>0){
	throw new PDOException("Periode Gaji sudah di Tutup.");
}


#4. Cek apakah transaksi kegiatan panen sudah di posting semua
$str="select * from ".$dbname.".kebun_prestasi_vs_hk where	tanggal like '".$prd."%' and unit='".$unit."' and jurnal='0'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
if($row>0){
	#throw new PDOException("Ada transaksi Kegiatan Panen yang belum di Posting");
}

#5. Cek apakah sudah pernah di posting sebelumnnya
$str="select * from ".$dbname.".keu_jurnalht where	noreferensi='".$notransaksi."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=$res->rowCount();
if($row>0){
	throw new PDOException("Transaksi sudah pernah di posting sebelumnnya.");
}
#========================= End Validasi Data ===========================

# Cek Datakaryawan History
$jumlahkaryhist=0;
$str = "select count(karyawanid) as jlh from ".$dbname.".datakaryawan_hist where 5=5 and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$prd."' "; 
$res = fetchdata($str);
$jumlahkaryhist=$res[0]['jlh'];
$tabel = 'datakaryawan';
$whhist = '';
$whhistx = '';

if($jumlahkaryhist > 0) {
	$whhist = "and b.version_type='B' and b.periodegaji='".$prd."' ";
	$whhistx = "and version_type='B' and periodegaji='".$prd."' ";
	$tabel = 'datakaryawan_hist';
}

# ambil data
	$datakgblokkecil=array();
	$datakgblokinduk=array();

	$str="select * from ".$dbname.".kebun_spbdt_detail a 
	where a.tanggalpanen like '".substr($notransaksi,0,4)."-".substr($notransaksi,4,2)."%'";
	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$datakgblokkecil[$bar['nospb']][$bar['tanggalpanen']][$bar['indukblok']][$bar['blok']]+=($bar['kgwbnetto']+$bar['brondolan']);
		@$datakgblokinduk[$bar['nospb']][$bar['tanggalpanen']][$bar['indukblok']]+=($bar['kgwbnetto']+$bar['brondolan']);
	}


	

	# ambil data
	$str="select tanggalpanen,mandor,kerani,kodeorg,divisi from ".$dbname.".kebun_3premipemanen 
	where notransaksi ='".$notransaksi."' group by tanggalpanen,mandor,kerani,divisi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$dataheader='';
	$datanoperx=array();
	$noxz=0;
	while($bar=$res->fetch()){
		# Generate No Transaksi

		$tanggal = str_replace('-','',$bar['tanggalpanen']);
		$fWhere = "tanggal='".$tanggal."' and kodeorg='".$bar['kodeorg']."' and tipetransaksi='PNN'";
		$fQuery = selectQuery($dbname,'kebun_aktifitas','notransaksi',$fWhere);
		$tmpNo = fetchData($fQuery);
		if(count($tmpNo)==0) {
			$notranbkn = $tanggal."/".$bar['kodeorg']."/PNN/001";
		} else {
			$noxz++;
			# Get Max No Urut
			$maxNo = 1;
			foreach($tmpNo as $row) {
			$tmpRow = explode('/',$row['notransaksi']);
			$noUrut = (int)$tmpRow[3];
			if($noUrut>$maxNo)
				$maxNo = $noUrut;
			}
			$currNo = addZero($maxNo+$noxz,3);
			$notranbkn = $tanggal."/".$bar['kodeorg']."/PNN/".$currNo."";
			//echo $notranbkn.'<br>';
		}
        
		$datanoperx[$bar['tanggalpanen']][$bar['mandor']][$bar['kerani']][$bar['divisi']]=$notranbkn;
		if($dataheader!=''){
			$dataheader.=",";
		}
		$dataheader.="('".$notranbkn."','PNN','".$bar['tanggalpanen']."','".$bar['kodeorg']."','".$bar['divisi']."','".$bar['mandor']."','','".$bar['kerani']."','','1','".$notransaksi."',
			 '".$_SESSION['standard']['userid']."')";


	}

	#========================= delete jurnal ===============================
	$str="delete from ".$dbname.".keu_jurnalht where noreferensi='".$notransaksi."'";
	$owlPDO->exec($str);
#========================= delete jurnal ===============================

	#=========================== Nomor Jurnal ==============================
	#PNN01 untuk UPAH , PNN02 untuk PREMI HANYA AMBIL AKUN DEBETNYA SAJA, PNN03 untuk Kutip Brondolan
	$tglEntry = date('Ymd');
	$tgl2=tglakhir($prd.'-01');
	$kodeJurnal = 'PNN01';
	$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',
	    "kodeaplikasi='PNN' and jurnalid='".$kodeJurnal."'");
	$resParam = fetchData($queryParam);

	$queryParam2 = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',
	    "kodeaplikasi='PNN' and jurnalid='PNN02'");
	$resParam2 = fetchData($queryParam2);

	$queryParam3 = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',
	    "kodeaplikasi='PNN' and jurnalid='PNN03'");
	$resParam3 = fetchData($queryParam3);



	

	# Get Journal Counter
	$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
	    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$unit."' and periode='".$prd."'");
	$tmpKonter = fetchData($queryJ);
	$konter = addZero($tmpKonter[0]['nokounter']+1,3);

	# Validasi jika nomor konter sudah 1000
	if($konter>999){
		throw new PDOException("Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = ".$_SESSION['org']['kodeorganisasi'].", Kode Kelompok = PNN01");
	}

	# Transform No Jurnal dari No Transaksi
	$tmpNoJurnal = explode('/',$notransaksi);
	// print_r($tmpNoJurnal);exit("Error:A");
	$tmpKodeOrg = substr($tmpNoJurnal[1],0,4);
	$nojurnal = $tmpNoJurnal[0]."/".$tmpKodeOrg."/".$kodeJurnal."/".$konter;
	#======================== End Nomor Jurnal =============================

	# Get Journal Counter
	$queryJ2 = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
	    "kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='PNN03' and kodeunit='".$unit."' and periode='".$prd."'");
	$tmpKonter2 = fetchData($queryJ2);
	$konter2 = addZero($tmpKonter2[0]['nokounter']+1,3);

	# Validasi jika nomor konter sudah 1000
	if($konter2>999){
		throw new PDOException("Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = ".$_SESSION['org']['kodeorganisasi'].", Kode Kelompok = PNN03");
	}

	# Transform No Jurnal dari No Transaksi
	$tmpNoJurnal2 = explode('/',$notransaksi);
	// print_r($tmpNoJurnal);exit("Error:A");
	$tmpKodeOrg2 = substr($tmpNoJurnal2[1],0,4);
	$nojurnal2 = $tmpNoJurnal2[0]."/".$tmpKodeOrg2."/PNN03/".$konter2;
	#======================== End Nomor Jurnal =============================


	# ambil data
	$str="select a.*, b.namakaryawan,b.nik from ".$dbname.".kebun_3premipemanen a 
	left join ".$tabel." b on a.karyawanid=b.karyawanid 
	where a.notransaksi ='".$notransaksi."' ".$whhist." order by a.mandor asc, a.tahuntanam asc, b.namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no=0;
	$datadetail1='';
	$datadetail2='';
	$rupiahHT=0;
	$rupiahHT2=0;
	$datajurnal1=array();
	$datajurnal2=array();
	$ttx=0;
	while($bar=$res->fetch()){

			if($datadetail1!=''){
				$datadetail1.=",";
			}
			$datadetail1.="('".$datanoperx[$bar['tanggalpanen']][$bar['mandor']][$bar['kerani']][$bar['divisi']]."','".$notransaksi."','".$bar['karyawanid']."','".$bar['blok']."','".($bar['jjgbuahbesar']+$bar['jjgbuahkecil'])."','".($bar['kgbuahbesar']+$bar['kgbuahkecil'])."','".($bar['hkbuahbesar']+$bar['hkbuahkecil'])."','".($bar['rphkbuahkecil']+$bar['rphkbuahbesar'])."','".($bar['rphkbuahkecilpot']+$bar['rphkbuahbesarpot'])."','".($bar['rplbbuahkecil']+$bar['rplbbuahbesar'])."','".$bar['dendapanen']."','".$bar['hapanen']."','".$bar['brondolan']."','".$bar['rpbrondolan']."','".$bar['nospb']."','".$_SESSION['standard']['userid']."')";
		
			$rupiahHT+=($bar['rphkbuahkecil']+$bar['rphkbuahbesar'])-($bar['rphkbuahkecilpot']+$bar['rphkbuahbesarpot'])+($bar['rplbbuahkecil']+$bar['rplbbuahbesar'])-$bar['dendapanen'];
			$rupiahHT2+=$bar['rpbrondolan'];

			$totaldapatbersih=$rupiahHT+$rupiahHT2;

			$sbttlhapanen=0;
			$sbttljjg=0;
			$sbttlkg=0;
			$sbttlhk=0;
			$sbttlupah=0;
			$sbttllbbs=0;
			$sbttlrplbs=0;
			$sbttlbrdl=0;
			$sbttlrpbrd=0;
			$sbttldendal=0;
			$countblok=count($datakgblokkecil[$bar['nospb']][$bar['tanggalpanen']][$bar['blok']]);
			$xupah=($bar['rphkbuahkecil']+$bar['rphkbuahbesar']);
			$xupahpot=($bar['rphkbuahkecilpot']+$bar['rphkbuahbesarpot']);
			$xpremi=($bar['rplbbuahkecil']+$bar['rplbbuahbesar']);
			$xdenda=$bar['dendapanen'];
			$xbrd=$bar['rpbrondolan'];
		foreach ($datakgblokkecil[$bar['nospb']][$bar['tanggalpanen']][$bar['blok']] as $blokkecil => $kgwbnetto) {
			$datatotalkg=$datakgblokinduk[$bar['nospb']][$bar['tanggalpanen']][$bar['blok']];
			$persentasekg=$kgwbnetto/$datatotalkg;
			
			$tahuntanam=getBlok($blokkecil,'tahuntanam');
			$sbttlhapanen=$persentasekg*$bar['hapanen'];
			$sbttljjg=$persentasekg*($bar['jjgbuahbesar']+$bar['jjgbuahkecil']);
			$sbttlkg=$persentasekg*($bar['kgbuahbesar']+$bar['kgbuahkecil']);
			$sbttlhk=$persentasekg*($bar['hkbuahbesar']+$bar['hkbuahkecil']);
			$sbttlupah=$persentasekg*($bar['rphkbuahkecil']+$bar['rphkbuahbesar']);
			$sbttlhkpot=$persentasekg*($bar['hkbuahbesarpot']+$bar['hkbuahkecilpot']);
			$sbttlupahpot=$persentasekg*($bar['rphkbuahkecilpot']+$bar['rphkbuahbesarpot']);
			$sbttllbbs=$persentasekg*($bar['lbbuahkecil']+$bar['lbbuahbesar']);
			$sbttlrplbs=$persentasekg*($bar['rplbbuahkecil']+$bar['rplbbuahbesar']);
			$sbttlbrdl=$persentasekg*$bar['brondolan'];
			$sbttlrpbrd=$persentasekg*$bar['rpbrondolan'];
			$sbttldenda=$persentasekg*$bar['dendapanen'];
			$countblok=$countblok-1;
			if($countblok>0){
				if(($xupah-$sbttlupah)>0){
					$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['upah']+=$sbttlupah;
					$xupah=($xupah-$sbttlupah);
				}else{
					$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['upah']+=$xupah;
					$xupah=0;
				}

				if(($xupahpot-$sbttlupahpot)>0){
					$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['upahpot']+=$sbttlupahpot;
					$xupahpot=($xupahpot-$sbttlupahpot);
				}else{
					$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['upahpot']+=$xupahpot;
					$xupahpot=0;
				}

				if(($xpremi-$sbttlrplbs)>0){
					$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['premi']+=$sbttlrplbs;
					$xpremi=($xpremi-$sbttlrplbs);
				}else{
					$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['premi']+=$xpremi;
					$xpremi=0;
				}


				if(($xdenda-$sbttldenda)>0){
					$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['denda']+=$sbttldenda;
					$xdenda=($xdenda-$sbttldenda);
				}else{
					$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['denda']+=$xdenda;
					$xdenda=0;
				}

				if(($xbrd-$sbttlrpbrd)>0){
					$datajurnal2[$nojurnal2][$bar['karyawanid']][$blokkecil]['premibrondol']+=$sbttlrpbrd;
					$xbrd=($xbrd-$sbttlrpbrd);
				}else{
					$datajurnal2[$nojurnal2][$bar['karyawanid']][$blokkecil]['premibrondol']+=$xbrd;
					$xbrd=0;
				}	
			}else{
				$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['upah']+=$xupah;
				$xupah=0;
				$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['upahpot']+=$xupahpot;
				$xupahpot=0;
				$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['premi']+=$xpremi;
				$xpremi=0;
				$datajurnal1[$nojurnal][$bar['karyawanid']][$blokkecil]['denda']+=$xdenda;
				$xdenda=0;
				$datajurnal2[$nojurnal2][$bar['karyawanid']][$blokkecil]['premibrondol']+=$xbrd;
				$xbrd=0;
			}
			
			
			
			
			

			 // $ttx+=$sbttlupah+$sbttlupahpot;
			 // $ttx-=$sbttlupahpot;
			 // $ttx+=$sbttlrplbs;
			 // $ttx-=$sbttldenda;			
			 // $ttx+=$sbttlrpbrd;

			
			

			if($datadetail2!=''){
				$datadetail2.=",";
			}
			$datadetail2.="('".$datanoperx[$bar['tanggalpanen']][$bar['mandor']][$bar['kerani']][$bar['divisi']]."','".$notransaksi."','".$bar['karyawanid']."','".$blokkecil."','".$tahuntanam."','".$sbttljjg."','".$sbttlkg."','".$sbttlhk."','".($sbttlupah)."','".$sbttlupahpot."','".$sbttlrplbs."','".$sbttldenda."','".$sbttlhapanen."','".$sbttlbrdl."','".$sbttlrpbrd."','".$bar['nospb']."','".$_SESSION['standard']['userid']."')";
			
		}
	}
	//echo $datadetail2;
// echo "<pre>";
// echo($ttx);
// echo "</pre>";
$indukunit=makeOption($dbname,'organisasi','kodeorganisasi,induk',"tipe='KEBUN'");
$noxjur=array();
$str="insert into ".$dbname.".keu_jurnalht
	(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
	`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)values
	('".$nojurnal."','".$kodeJurnal."','".$tgl2."','".$tglEntry."','0','".$rupiahHT."','".$rupiahHT."','0','".$notransaksi."','1','IDR','1','0')";

$owlPDO->exec($str);
		$no=0;
		$totalxxx=0;
		foreach ($datajurnal1 as $nojurnal => $key1) {
			foreach ($key1 as $karyd => $key2) {
				foreach ($key2 as $blokkecil => $val) {
					$no+=1;

					$nmkary=makeOption($dbname,$tabel,'karyawanid,namakaryawan',"karyawanid='".$karyd."'");
					$lokasitugasx=makeOption($dbname,$tabel,'karyawanid,lokasitugas',"karyawanid='".$karyd."' ".$whhistx." ");
					$statusblok=makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$blokkecil."'");

					$akundebet=$resParam[0]['noakundebet'];
					$akundebet2=$resParam2[0]['noakundebet'];
					if($statusblok[$blokkecil]!='TM'){
						$akundebet='1261004';
						$akundebet2='1261004';
					}

					# insert debet => keu_jurnaldt
					$str="insert into ".$dbname.".keu_jurnaldt
						(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
						`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
						`kodeblok`,`revisi`,`kodesegment`)
						values ('".$nojurnal."','".$tgl2."','".$no."','".$akundebet."',
						'Potong Buah : ".$nmkary[$karyd]."','".($val['upah'])."','IDR','1','".$tmpKodeOrg."',
						'".$resParam[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
					$owlPDO->exec($str);

						//$totalxxx+=($val['upah']-$val['upahpot']);

					
					
					if($val['premi']>0){

						$no+=1;
						$str="insert into ".$dbname.".keu_jurnaldt
						(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
						`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
						`kodeblok`,`revisi`,`kodesegment`)
						values ('".$nojurnal."','".$tgl2."','".$no."','".$akundebet2."',
						'Premi Panen : ".$nmkary[$karyd]."','".$val['premi']."','IDR','1','".$tmpKodeOrg."',
						'".$resParam2[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
						$owlPDO->exec($str);

					}

					$no+=1;
					$rupiahKr=($val['upah']+$val['premi']);
					# insert kredit => keu_jurnaldt

					if($lokasitugasx[$karyd]!=$tmpKodeOrg){
						$noxzx=0;
						if(!isset($noxjur[$lokasitugasx[$karyd]])){
							$noxjur[$lokasitugasx[$karyd]]=1;
						}
						if($indukunit[$lokasitugasx[$karyd]]!=$indukunit[$tmpKodeOrg]){
							$jenis="inter";
						}else if($indukunit[$lokasitugasx[$karyd]]==$indukunit[$tmpKodeOrg]){
							$jenis="intra";    
						}

						$aknPt=makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',"kodeorg='".$lokasitugasx[$karyd]."' and jenis='".$jenis."'");
						$aknHtg=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$tmpKodeOrg."' and jenis='".$jenis."'"); 


						$str="insert into ".$dbname.".keu_jurnaldt
							(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
							`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
							`kodeblok`,`revisi`,`kodesegment`)
							values ('".$nojurnal."','".$tgl2."','".$no."','".$aknHtg[$tmpKodeOrg]."',
							'Potong Buah dan Premi Panen : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$tmpKodeOrg."',
							'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
						$owlPDO->exec($str);


						# Get Journal Counter
						$queryJxzz = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
						    "kodeorg='".$indukunit[$lokasitugasx[$karyd]]."' and kodekelompok='M' and kodeunit='".$lokasitugasx[$karyd]."' and periode='".$prd."'");
						$tmpKonterxzz = fetchData($queryJxzz);
						$konterxzz = addZero($tmpKonterxzz[0]['nokounter']+$noxjur[$lokasitugasx[$karyd]],3);
						//echo $konterxzz.'<br>';

						# Validasi jika nomor konter sudah 1000
						if($konter>999){
							throw new PDOException("Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = ".$_SESSION['org']['kodeorganisasi'].", Kode Kelompok = M");
						}

						# Transform No Jurnal dari No Transaksi
						$tmpNoJurnalxzz = explode('/',$notransaksi);

						$tmpKodeOrgxzz = $lokasitugasx[$karyd];
						$nojurnalxzz = $tmpNoJurnalxzz[0]."/".$tmpKodeOrgxzz."/M/".$konterxzz;

						$str="insert into ".$dbname.".keu_jurnalht
							(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
							`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)values
							('".$nojurnalxzz."','M','".$tgl2."','".$tglEntry."','0','".$rupiahKr."','".$rupiahKr."','0','".$notransaksi."','1','IDR','1','0')";

						$owlPDO->exec($str);

						$noxzx+=1;
						$str="insert into ".$dbname.".keu_jurnaldt
							(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
							`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
							`kodeblok`,`revisi`,`kodesegment`)
							values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$aknPt[$lokasitugasx[$karyd]]."',
							'Potong Buah dan Premi Panen : ".$nmkary[$karyd]."','".$rupiahKr."','IDR','1','".$lokasitugasx[$karyd]."',
							'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
						//echo $str.'<br>';
						$owlPDO->exec($str);

						$noxzx+=1;
						$str="insert into ".$dbname.".keu_jurnaldt
							(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
							`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
							`kodeblok`,`revisi`,`kodesegment`)
							values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$resParam[0]['noakunkredit']."',
							'Potong Buah dan Premi Panen : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$lokasitugasx[$karyd]."',
							'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
						$owlPDO->exec($str);
					
						if(($val['upahpot']+$val['denda'])>0){
							$no+=1;
							# insert debet => keu_jurnaldt
							$str="insert into ".$dbname.".keu_jurnaldt
								(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
								`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
								`kodeblok`,`revisi`,`kodesegment`)
								values ('".$nojurnal."','".$tgl2."','".$no."','".$aknHtg[$tmpKodeOrg]."',
								'DENDA Potong Buah : ".$nmkary[$karyd]."','".($val['upahpot']+$val['denda'])."','IDR','1','".$tmpKodeOrg."',
								'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
							$owlPDO->exec($str);

							$noxzx+=1;
							$str="insert into ".$dbname.".keu_jurnaldt
								(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
								`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
								`kodeblok`,`revisi`,`kodesegment`)
								values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$aknPt[$lokasitugasx[$karyd]]."',
								'DENDA Potong Buah : ".$nmkary[$karyd]."','".($val['upahpot']+$val['denda'])*(-1)."','IDR','1','".$lokasitugasx[$karyd]."',
								'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
							//echo $str.'<br>';
							$owlPDO->exec($str);

							$noxzx+=1;
							$str="insert into ".$dbname.".keu_jurnaldt
								(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
								`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
								`kodeblok`,`revisi`,`kodesegment`)
								values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$resParam[0]['noakunkredit']."',
								'DENDA Potong Buah : ".$nmkary[$karyd]."','".($val['upahpot']+$val['denda'])."','IDR','1','".$lokasitugasx[$karyd]."',
								'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
							$owlPDO->exec($str);
							
							if($val['upahpot']>0){
								$no+=1;
								$rupiahKr=$val['upahpot'];
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal."','".$tgl2."','".$no."','".$akundebet."',
									'DENDA Potong Buah : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$tmpKodeOrg."',
									'".$resParam[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

							}

							if($val['denda']>0){
								$no+=1;
								$rupiahKr=$val['denda'];
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal."','".$tgl2."','".$no."','".$akundebet2."',
									'DENDA Potong Buah : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$tmpKodeOrg."',
									'".$resParam2[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

							}

						}

						$noxjur[$lokasitugasx[$karyd]]+=1;

					}else{
						$str="insert into ".$dbname.".keu_jurnaldt
							(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
							`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
							`kodeblok`,`revisi`,`kodesegment`)
							values ('".$nojurnal."','".$tgl2."','".$no."','".$resParam[0]['noakunkredit']."',
							'Potong Buah dan Premi Panen : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$tmpKodeOrg."',
							'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
						$owlPDO->exec($str);

					    if(($val['upahpot']+$val['denda'])>0){
							$no+=1;
							# insert debet => keu_jurnaldt
							$str="insert into ".$dbname.".keu_jurnaldt
								(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
								`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
								`kodeblok`,`revisi`,`kodesegment`)
								values ('".$nojurnal."','".$tgl2."','".$no."','".$resParam[0]['noakunkredit']."',
								'DENDA Potong Buah : ".$nmkary[$karyd]."','".($val['upahpot']+$val['denda'])."','IDR','1','".$tmpKodeOrg."',
								'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
							$owlPDO->exec($str);
							
							if($val['upahpot']>0){
								$no+=1;
								$rupiahKr=$val['upahpot'];
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal."','".$tgl2."','".$no."','".$akundebet."',
									'DENDA Potong Buah : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$tmpKodeOrg."',
									'".$resParam[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

							}

							if($val['denda']>0){
								$no+=1;
								$rupiahKr=$val['denda'];
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal."','".$tgl2."','".$no."','".$akundebet2."',
									'DENDA Potong Buah : ".$nmkary[$karyd]."','".$rupiahKr*(-1)."','IDR','1','".$tmpKodeOrg."',
									'".$resParam2[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

							}

						}
					}

						
					



				}
			}
		}

//echo '<br>'.$totalxxx.'<br>';
if($rupiahHT2>0){
	$str="insert into ".$dbname.".keu_jurnalht
		(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
		`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)values
		('".$nojurnal2."','PNN03','".$tgl2."','".$tglEntry."','0','".$rupiahHT2."','".$rupiahHT2."','0','".$notransaksi."','1','IDR','1','0')";

	$owlPDO->exec($str);

			$nox=0;
			foreach ($datajurnal2 as $nojurnal2 => $key1) {
				foreach ($key1 as $karyd => $key2) {
					foreach ($key2 as $blokkecil => $val) {
						$noxzx=0;
						
						$nmkary=makeOption($dbname,$tabel,'karyawanid,namakaryawan',"karyawanid='".$karyd."'");
						$lokasitugasx=makeOption($dbname,$tabel,'karyawanid,lokasitugas',"karyawanid='".$karyd."' ".$whhistx." ");
						$statusblok=makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$blokkecil."'");

						$akundebet3=$resParam3[0]['noakundebet'];
						if($statusblok[$blokkecil]!='TM'){
							$akundebet3='1261004';
						}

						if($lokasitugasx[$karyd]!=$tmpKodeOrg2){
							if(!isset($noxjur[$lokasitugasx[$karyd]])){
								$noxjur[$lokasitugasx[$karyd]]=1;
							}
							if($indukunit[$lokasitugasx[$karyd]]!=$indukunit[$tmpKodeOrg]){
								$jenis="inter";
							}else if($indukunit[$lokasitugasx[$karyd]]==$indukunit[$tmpKodeOrg]){
								$jenis="intra";    
							}

							$aknPt=makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',"kodeorg='".$lokasitugasx[$karyd]."' and jenis='".$jenis."'");
							$aknHtg=makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$tmpKodeOrg."' and jenis='".$jenis."'"); 


							# Get Journal Counter
							$queryJxzz = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
							    "kodeorg='".$indukunit[$lokasitugasx[$karyd]]."' and kodekelompok='M' and kodeunit='".$lokasitugasx[$karyd]."' and periode='".$prd."'");
							$tmpKonterxzz = fetchData($queryJxzz);
							$konterxzz = addZero($tmpKonterxzz[0]['nokounter']+$noxjur[$lokasitugasx[$karyd]],3);
							//echo $konterxzz.'<br>';

							# Validasi jika nomor konter sudah 1000
							if($konter>999){
								throw new PDOException("Nomor transaksi Jurnal sudah melebihi batas, silahkan reset nomor melalui menu Keuangan - Setup - Kelompok Jurnal, Kode Org = ".$_SESSION['org']['kodeorganisasi'].", Kode Kelompok = M");
							}

							# Transform No Jurnal dari No Transaksi
							$tmpNoJurnalxzz = explode('/',$notransaksi);

							$tmpKodeOrgxzz = $lokasitugasx[$karyd];
							$nojurnalxzz = $tmpNoJurnalxzz[0]."/".$tmpKodeOrgxzz."/M/".$konterxzz;


							if($val['premibrondol']>0){
								$str="insert into ".$dbname.".keu_jurnalht
									(`nojurnal`,`kodejurnal`,`tanggal`,`tanggalentry`,`posting`,`totaldebet`,`totalkredit`,`amountkoreksi`,
									`noreferensi`,`autojurnal`,`matauang`,`kurs`,`revisi`)values
									('".$nojurnalxzz."','M','".$tgl2."','".$tglEntry."','0','".$val['premibrondol']."','".$val['premibrondol']."','0','".$notransaksi."','1','IDR','1','0')";

								$owlPDO->exec($str);

								$noxzx+=1;
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$aknPt[$lokasitugasx[$karyd]]."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".($val['premibrondol'])."','IDR','1','".$lokasitugasx[$karyd]."',
									'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								//echo $str.'<br>';
								$owlPDO->exec($str);

								$noxzx+=1;
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnalxzz."','".$tgl2."','".$noxzx."','".$resParam[0]['noakunkredit']."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".($val['premibrondol'])*(-1)."','IDR','1','".$lokasitugasx[$karyd]."',
									'','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);


								$nox+=1;
								$nmkary=makeOption($dbname,$tabel,'karyawanid,namakaryawan',"karyawanid='".$karyd."'");
								# insert debet => keu_jurnaldt
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal2."','".$tgl2."','".$nox."','".$akundebet3."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".$val['premibrondol']."','IDR','1','".$tmpKodeOrg2."',
									'".$resParam3[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);
								//$totalxxx+=$val['premibrondol'];
								
								$nox+=1;
								# insert kredit => keu_jurnaldt
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal2."','".$tgl2."','".$nox."','".$aknHtg[$tmpKodeOrg2]."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".$val['premibrondol']*(-1)."','IDR','1','".$tmpKodeOrg2."',
									'".$resParam3[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

								$noxjur[$lokasitugasx[$karyd]]+=1;


							}
						}else{
							if($val['premibrondol']>0){
								$nox+=1;
								$nmkary=makeOption($dbname,$tabel,'karyawanid,namakaryawan',"karyawanid='".$karyd."'");
								# insert debet => keu_jurnaldt
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal2."','".$tgl2."','".$nox."','".$akundebet3."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".$val['premibrondol']."','IDR','1','".$tmpKodeOrg2."',
									'".$resParam3[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);
								//$totalxxx+=$val['premibrondol'];
								
								$nox+=1;
								# insert kredit => keu_jurnaldt
								$str="insert into ".$dbname.".keu_jurnaldt
									(`nojurnal`,`tanggal`,`nourut`,`noakun`,`keterangan`,`jumlah`,`matauang`,`kurs`,`kodeorg`,`kodekegiatan`,
									`kodeasset`,`kodebarang`,`nik`,`kodecustomer`,`kodesupplier`,`noreferensi`,`noaruskas`,`kodevhc`,`nodok`,
									`kodeblok`,`revisi`,`kodesegment`)
									values ('".$nojurnal2."','".$tgl2."','".$nox."','".$resParam3[0]['noakunkredit']."',
									'Kutip Brondolan : ".$nmkary[$karyd]."','".$val['premibrondol']*(-1)."','IDR','1','".$tmpKodeOrg2."',
									'".$resParam3[0]['noakundebet']."01','','','".$karyd."','','','".$notransaksi."','','','','".$blokkecil."','0','0000000001')";
								$owlPDO->exec($str);

							}
						}

						

					}
				}
			}

}

				



#========================= delete kebun_aktifitas ===============================
	$str="delete from ".$dbname.".kebun_aktifitas where noreferensi='".$notransaksi."'";
	$owlPDO->exec($str);
#========================= delete kebun_aktifitas ===============================

#========================= insert kebun_prestasi ===============================
$str="insert into ".$dbname.".kebun_aktifitas
			(`notransaksi`,`tipetransaksi`,`tanggal`,`kodeorg`,`divisi`,`nikmandor`,`nikmandor1`,`nikasisten`,`keranimuat`
			,`jurnal`,`noreferensi`,`updateby`)
		values ".$dataheader.";";
$owlPDO->exec($str);

$str="insert into ".$dbname.".kebun_prestasi
	(`notransaksi`,`noreferensi`,`nik`,`kodeorg`,`hasilkerja`,`hasilkerjakg`,`jumlahhk`,`upahkerja`,`upahpenalty`,`upahpremilebihbasis`,`rupiahpenalty`,`luaspanen`,`brondolan`,`premibrondol`,`nospb`,`updateby`)
values ".$datadetail1.";";
$owlPDO->exec($str);

$str="insert into ".$dbname.".kebun_prestasi_detail
	(`notransaksi`,`noreferensi`,`nik`,`kodeorg`,`tahuntanam`,`hasilkerja`,`hasilkerjakg`,`jumlahhk`,`upahkerja`,`upahpenalty`,`upahpremilebihbasis`,`rupiahpenalty`,`luaspanen`,`brondolan`,`premibrondol`,`nospb`,`updateby`)
values ".$datadetail2.";";
// echo $datadetail2.'<br>';
// echo $str;
$owlPDO->exec($str);

foreach ($noxjur as $unitz => $urutxz) {
	$str="update ".$dbname.".keu_5kelompokjurnal set nokounter = '".$urutxz."' where kodeorg='".$indukunit[$unitz]."' and kodekelompok='M' and kodeunit='".$unitz."' and periode='".$prd."'";
	$owlPDO->exec($str);
}
#============= End Insert Data ke Table Kebun Aktifitas ===============


#============= End Insert Data ke Table Kebun Aktifitas ===============
#======================== Cek data jurnal =============================

$str="select sum(debet-kredit) as rpj from ".$dbname.".keu_jurnaldt_vw where	nojurnal in ('".$nojurnal."','".$nojurnal2."') and noreferensi='".$notransaksi."' and noakun in ('".$resParam[0]['noakundebet']."','".$resParam2[0]['noakundebet']."','".$resParam3[0]['noakundebet']."','1261004')"; 
//echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$rpj=$bar['rpj'];

$varian=($rpj-$totaldapatbersih);
if($varian > 2 or $varian < (-2)){
	throw new PDOException("Nilai Jurnal : ".$rpj." tidak sama dengan nilai transaksi : ".$totaldapatbersih);
}

$str="select sum(upahpremilebihbasis+premibrondol-rupiahpenalty+upahkerja-upahpenalty) as prelb from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.noreferensi='".$notransaksi."'"; 
//echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$prelb=$bar['prelb'];
 
$varianx=($prelb-$totaldapatbersih);
if($varianx > 2 or $varianx < (-2)){
	throw new PDOException("Nilai Kegiatan Panen : ".$prelb." tidak sama dengan nilai transaksi : ".$totaldapatbersih);
}

#======================== End data jurnal =============================

#============================= Update ==================================
# Update Counter
$str="update ".$dbname.".keu_5kelompokjurnal set nokounter = '".$konter."' where kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$unit."' and periode='".$prd."'";
$owlPDO->exec($str);

$str="update ".$dbname.".keu_5kelompokjurnal set nokounter = '".$konter2."' where kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='PNN03' and kodeunit='".$unit."' and periode='".$prd."'";
$owlPDO->exec($str);

# Update flag transaksi
$str="update ".$dbname.".kebun_3premipemanen set posting='1', jurnal = '".$nojurnal.",".$nojurnal2."', postingby ='".$_SESSION['standard']['userid']."',
	  postingdate='".$tglEntry."' where notransaksi='".$notransaksi."'";
$owlPDO->exec($str);

#=========================== End Update ===============================
$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
}

?>