<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
//====================================
//default setting on database 
//1=Masuk,2=Pengembalian pengeluaran, 3=penerimaan mutasi,
//5=Pengeluaran,6=Pengembalian penerimaan,7 pengeluaran mutasi 
$tipetransaksi=5;
//=============================================

$kelkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok');

if(isTransactionPeriod())//check if transaction period is normal
{
	$nodok        = $_POST['nodok'];
	@$norequest   = $_POST['norequest'];
	$tanggal      = isset($_POST['tanggal'])?  tanggalsystem($_POST['tanggal']): '';
	$kodebarang   = isset($_POST['kodebarang'])? $_POST['kodebarang']: '';
	$penerima     = isset($_POST['penerima'])? $_POST['penerima']: '';
	$satuan       = isset($_POST['satuan'])?  $_POST['satuan']: '';
	$qty          = isset($_POST['qty'])?   $_POST['qty']: '';
	$blok         = isset($_POST['blok'])?  $_POST['blok']: '';
	$segment      = isset($_POST['segment'])?  $_POST['segment']: '';
	$mesin        = isset($_POST['mesin'])?  $_POST['mesin']: '';
	$kodemesin    = isset($_POST['kodemesin'])?  $_POST['kodemesin']: '';
	$norequest    = isset($_POST['norequest'])?  $_POST['norequest']: '';
	$untukunit    = isset($_POST['untukunit'])? $_POST['untukunit']: '';
	$subunit      = isset($_POST['subunit'])?  $_POST['subunit']: '';
	$gudang       = isset($_POST['gudang'])?  $_POST['gudang']: '';
	$catatan      = isset($_POST['catatan'])?  $_POST['catatan']: '';
	$kegiatan     = isset($_POST['kegiatan'])? $_POST['kegiatan']: '';
	$method       = isset($_POST['method'])?  $_POST['method']: '';
	$pemilikbarang= isset($_POST['pemilikbarang'])? $_POST['pemilikbarang']: '';        
	$departemen   = isset($_POST['departemen'])? $_POST['departemen']: '';        
	$karyawanid   = isset($_POST['karyawanid'])? $_POST['karyawanid']: '';        
	$kmhm         = checkPostGet('kmhm', '');
	$dept         = checkPostGet('dept', '');
	$statusblok         = checkPostGet('statusblok', '');
	$periode=substr(tanggalsystemn($_POST['tanggal']),0,7);
	
	validasiInput(substr($gudang,0,4),$gudang,'LOG',tanggalsystemn(tanggalnormal($tanggal)),$exit='1');
	
	$user		=$_SESSION['standard']['userid'];
	$post=0;


	
	if(getNamaOrg($subunit,'tipe')=='BIBITAN' and $blok==''){		
		exit("errorcode : Blok wajib diisi");
	}
	
	//pastikan kodeblok terisi
	if($blok=='')
	   $blok=$subunit;
	if($blok=='')
	   $blok=$untukunit;
	    			
        
        // $traksimesin=  makeOption($dbname, 'vhc_5master', 'kodevhc,kodetraksi');
        
        #periksa apakah kendaraan    
        #= cek kode traksi  baru
		$traksimesin='';
		$str = "select count(*) as jumlah from ".$dbname.".vhc_5master_hist where kodetraksi='".$subunit."' and status='1' and periode='".$periode."'"; 
		// exit("Warningsistem:".$str);
		$res = fetchData($str);
		foreach($res as $bar) {
			$jumlah=$bar['jumlah'];
		}
		if($jumlah>0){
			$str = "select * from ".$dbname.".vhc_5master_hist where kodevhc='".$mesin."'  and periode='".$periode."'"; 
			$res = fetchData($str);
			foreach($res as $bar) {
				$traksimesin=$bar['kodetraksi'];
			}
		}else{
			$str = "select * from ".$dbname.".vhc_5master where kodevhc='".$mesin."'"; 
			$res = fetchData($str);
			foreach($res as $bar) {
				$traksimesin=$bar['kodetraksi'];
			}
		}
		
		 
        if($mesin!='')
        {
            if($traksimesin!=$subunit)
            {
                exit("Error:Lokasi traksi Kend/Alat Berat/Mesin untuk ".$mesin." tidak sesuai dengan subunit yang dipilih, cek di Traksi->Laporan->Daftar Kend/AB/Mesin");
            }
        }
        if($kegiatan=='114050001')
        {	
        	$optnama= makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan = '".$kegiatan."'");
            $str="select tipe from ".$dbname.".log_5supkelompok where supplierid='".$subunit."'"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			if ($bar['tipe']!='KONTRAKTOR') {
				exit('warning : Jika kegiatan adalah '.$optnama[$kegiatan].' ('.$kegiatan.'), Subunit harus kontraktor.');
			}

        }
        
        

	//1 cek apakah sudah terekan di header
	//status=0 belum ada apa2
	//status=1 ada header
	//status=2 ada detail dan header
	//status=3 sudah di posting
	//status=4 kode pt penerima barang tidak ada
	//status=5 delete item
	//status=6 display only
	//status=7 sudah ada yang diposting pada tanggal yang lebih besar dengan barang yang sama dan pt yang sama

	//======================================================= pengganti line 48-54 by pak ginting mar 5, 2014
	$status=0;
    $user1=$_SESSION['standard']['userid'];
	$str="select user from ".$dbname.".log_transaksiht where notransaksi='".$nodok."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res);
	if($numrows==1)
	{
		while($bar=$res->fetch()){
			$user1=$bar->user;                  
		}
        If($_SESSION['standard']['userid']==$user1){
            $status=1;
        } else {
            exit('Error: This transaction belongs to other user, please reload and start over');
        }            
	}
	//=======================================================

	if($method=='update') $status=2;
//	 }	 
	if(isset($_POST['delete'])) $status=5;	
	
	$str="select * from ".$dbname.".log_transaksiht where notransaksi='".$nodok."'
	    and post=1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$numrows=owlBaris($res);
	if($numrows>0) $status=3;	
	//===================================	 

	//==========================================
    
    ##update pengunciannya

       
        
	//==========================================
	//ambil PT peminta barang
	$ptpemintabarang='';
	$stre=" select induk from ".$dbname.".organisasi where kodeorganisasi='".$untukunit."'";
	$rese=$owlPDO->query($stre) or die(print " Gagal: ".PDOException::getMessage());
	$rese->setFetchMode(PDO::FETCH_OBJ);
	while($bare=$rese->fetch()) {
		//cek if tipe=PT
		$strf="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$bare->induk."'";
		$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
		$resf->setFetchMode(PDO::FETCH_OBJ);
		while($barf=$resf->fetch()) {
			if($barf->tipe=='PT') $ptpemintabarang=$bare->induk;//ini memang bare
		}
	}
	//if $ptpemintabarang=='', ambil dari default alokasi pada holding;
    if($ptpemintabarang=='') {
		$strf="select alokasi from ".$dbname.".organisasi where kodeorganisasi='".$untukunit."' and alokasi<>''";
		$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
		$resf->setFetchMode(PDO::FETCH_OBJ);
		while($barf=$resf->fetch()) {
		    $ptpemintabarang=$barf->alokasi;
		}
	    if($ptpemintabarang=='') $status=4;
	} 
	if(isset($_POST['displayonly'])) $status=6;
	
	//==================ambil jumlah lalu====================
	$jumlahlalu=0;
	$str="select a.jumlah as jumlah,b.nopo as nopo,a.notransaksi as notransaksi,a.waktutransaksi 
	    from ".$dbname.".log_transaksidt a,
	         ".$dbname.".log_transaksiht b
		where a.notransaksi=b.notransaksi 
		and a.kodebarang='".$kodebarang."'
		and a.notransaksi<='".$nodok."'
		and b.tipetransaksi>4 
		and b.kodegudang='".$gudang."'
		order by notransaksi desc, waktutransaksi desc limit 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()) {
		$jumlahlalu=$bar->jumlah;
	}
	//ambil pemasukan barang yang belum di posting
	$qtynotpostedin=0;
	$str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt
		   b on a.notransaksi=b.notransaksi where kodept='".$pemilikbarang."' and b.kodebarang='".$kodebarang."' 
		   and a.tipetransaksi<5
		   and a.kodegudang='".$gudang."'
		   and a.post=1			   
		   group by kodebarang";
	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_OBJ);
	while($bar2=$res2->fetch()) {
		$qtynotpostedin=$bar2->jumlah;
	}
	if($qtynotpostedin=='') $qtynotpostedin=0;
	
	//ambil pengeluaran barang yang belum di posting
	$qtynotposted=0;
	$str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt
           b on a.notransaksi=b.notransaksi where kodept='".$pemilikbarang."' and b.kodebarang='".$kodebarang."' 
		   and a.tipetransaksi>4
		   and a.kodegudang='".$gudang."'
		   and a.post=0 and a.notransaksi!='".$nodok."'		   
		   group by kodebarang";
	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_OBJ);
	while($bar2=$res2->fetch())
	{
		$qtynotposted=$bar2->jumlah;
	}
	if($qtynotposted=='') $qtynotposted=0;
	   
	//ambil saldo qty===============================================
	$saldoqty=0;
	$strs="select saldoakhirqty as saldoqty from ".$dbname.".log_5saldobulanan where kodebarang='".$kodebarang."'
          and kodeorg='".$pemilikbarang."'
		  and kodegudang='".$gudang."'
		  and periode='".$periode."'"; #exit("error".$strs);
	$ress=$owlPDO->query($strs) or die(print " Gagal: ".PDOException::getMessage());
	$ress->setFetchMode(PDO::FETCH_OBJ);
	while($bars=$ress->fetch()){
		$saldoqty=$bars->saldoqty;
	}

	//==================periksa kecukupan saldo
	if($status==0 or $status==1) {
		// if(($qty+$qtynotposted)>($saldoqty+$qtynotpostedin)) {
		if(($qty+$qtynotposted)>($saldoqty)) {
			echo " Error: ".$_SESSION['lang']['saldo']." ".$_SESSION['lang']['tidakcukup']."";
			$status=6;//status ngeles
			exit(0);		
		}
	} else if($status==2) {
		//ambil jumlah lama dan bandingkan dengan qty kemudian bandingkan dengan saldo
		$jlhlama=0;
		$strt="select jumlah from ".$dbname.".log_transaksidt where notransaksi='".$nodok."'
			   and kodebarang='".$kodebarang."' and kodeblok='".$blok."'";
		$rest=$owlPDO->query($strt) or die(print " Gagal: ".PDOException::getMessage());
		$rest->setFetchMode(PDO::FETCH_OBJ);
		while($bart=$rest->fetch()) {
			$jlhlama=$bart->jumlah;
		}
		
		// if(($saldoqty+$jlhlama+$qtynotpostedin)<($qty+$qtynotposted)) {
		if(($saldoqty+$jlhlama)<($qty+$qtynotposted)) {
			echo " Error: ".$_SESSION['lang']['saldo']." ".$_SESSION['lang']['tidakcukup'];
			$status=6;//status ngeles
			exit(0);
		}
	}

	//periksa apakah sudah ada status 7
	if($status==0 or $status==1 or $status==2) {
		$stro="select a.post from ".$dbname.".log_transaksiht a
			   left join ".$dbname.".log_transaksidt b
			   on a.notransaksi=b.notransaksi
			   where a.tanggal>".$tanggal." and a.kodept='".$pemilikbarang."'
			   and b.kodebarang='".$kodebarang."' and kodegudang='".$gudang."'
			   and a.post=1";
		$reso=$owlPDO->query($stro) or die(print " Gagal: ".PDOException::getMessage());
		$reso->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($reso);
		if($numrows>0) {
			$status=7;
			echo " Error :".$_SESSION['lang']['tanggaltutup'];
			exit(0);
		}
	}

	//=============================start input/update	
	//status=0
	
	//exit("Error:$mesin._.$kelkeg[$kegiatan]");
	
	if(@$kelkeg[$kegiatan]=='TRK' and $mesin==''){
		exit("Warning:Jika kegiatan traksi maka kendaraan tidak boleh kosong");
	}

	$sum_qty_pic=0;
	// Pastikan array $_SESSION['pic2'] ada
	if (isset($_SESSION['pic2'])) {
		// Inisialisasi array $_SESSION['pic'] sebagai array kosong
		$_SESSION['pic'] = [];

		// Iterasi melalui array $_SESSION['pic2']
		foreach ($_SESSION['pic2'] as $item) {
			// Format data sesuai kebutuhan
			$new_item = [
				'kodebarang' => $item['kodebarang'],
				'qty' => $item['qty'],
				'picpic' => $item['picpic'],
				'departemenpic' => '', 
				'qtypic' => $item['jumlah']
			];

			$sum_qty_pic +=$item['jumlah'];

			// Masukkan item yang telah diformat ke dalam $_SESSION['pic']
			$_SESSION['pic'][] = $new_item;
		}
	}

	if($sum_qty_pic > 0){
		if($sum_qty_pic > $qty){
			exit("warning : Jumlah QTY PIC melebih jumlah pemakaian... ");
		}
	}


	// cek apakah blok atau bukan
	$str_blok = "select * from ".$dbname.".setup_blok where indukblok = '".$blok."' ";
	@$res_blok = fetchData($str_blok);
	// jika blok
	if(count($res_blok)>0){
		$iniblok = '1';
	}else{
		$iniblok = '0';
	}
	
	
	#BBM
	$strap = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter in ('HRPBBM')";
	@$resap = fetchData($strap);
	@$nilaix=0;
	$kdkeg=array();
	if(count($resap)>0){
		@$kdkeg= explode(',', $resap[0]['nilai']);
		foreach (@$kdkeg as $keykeg => $valkeg) {
			if($kegiatan==$valkeg){
				if($_SESSION['pic'] != array()){
					foreach($_SESSION['pic'] as $key=>$row){
						if($row['picpic']==''){
							exit("Warning : Untuk kegiatan POTONGAN BBM KARYAWAN kolom PIC wajib diisi oleh nama karyawan.");
						}
					}
				}else{
					exit("Warning : Untuk kegiatan POTONGAN BBM KARYAWAN kolom PIC wajib diisi.");
				}
			}

			$ttlpic=0;
			if($kegiatan==$valkeg){
				if($_SESSION['pic'] != array()){
					foreach($_SESSION['pic'] as $key=>$row){
						$ttlpic+=$row['qtypic'];
					}
				}			
				if($qty!=$ttlpic){
					exit("Warning : Jumlah total vs total per karyawan tidak sama, Jumlah Total = ".$qty.", Jumlah PIC = ".$ttlpic.", Jumlah Selisih = ".($qty-$ttlpic)."");
				}
			}
		}
	}
	
	#ALAT PANEN
	$strap = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter in ('HRPMAT')";
	@$resap = fetchData($strap);
	
	@$nilaix=0;
	$kdkeg=array();
	if(count($resap)>0){
		@$kdkeg= explode(',', $resap[0]['nilai']);
		foreach (@$kdkeg as $keykeg => $valkeg) {
			if($kegiatan==$valkeg){
				if($_SESSION['pic'] != array()){
					foreach($_SESSION['pic'] as $key=>$row){
						if($row['picpic']==''){
							exit("Warning : Untuk kegiatan POTONGAN MATERIAL KARYAWAN kolom PIC wajib diisi oleh nama karyawan.");
						}
					}
				}else{
					exit("Warning : Untuk kegiatan POTONGAN MATERIAL KARYAWAN kolom PIC wajib diisi.");
				}
			}
			
			$ttlpic=0;
			if($kegiatan==$valkeg){
				if($_SESSION['pic'] != array()){
					foreach($_SESSION['pic'] as $key=>$row){
						$ttlpic+=$row['qtypic'];
					}
				}			
				if($qty!=$ttlpic){
					exit("Warning : Jumlah total vs total per karyawan tidak sama, Jumlah Total = ".$qty.", Jumlah PIC = ".$ttlpic.", Jumlah Selisih = ".($qty-$ttlpic)."");
				}
			}
		}
	}
		
	
	if(substr($kegiatan,0,1)=='7' and $dept==''){
		exit("Warning:Nomor akun 7xxx wajib mengisi kolom Dept.");
	}
	if(substr($kegiatan,0,1)=='8' and $dept==''){
		exit("Warning:Nomor akun 8xxx wajib mengisi kolom Dept.");
	}
	
	
	if($status==0) {
			
		$str="insert into ".$dbname.".log_transaksiht (
  			  `tipetransaksi`,`notransaksi`,
			  `tanggal`,`kodept`,
			  `untukpt`,`keterangan`,
			  `kodegudang`,`user`,
			  `namapenerima`,`untukunit`,`subunit`,`post`,
			  departemen,karyawanid,norequest)
		values(".$tipetransaksi.",'".$nodok."',
		       ".$tanggal.",'".$pemilikbarang."',
			  '".$ptpemintabarang."','".$catatan."',
			  '".$gudang."',".$user.",
			  '".$penerima."','".$untukunit."','".$subunit."',".$post.",
			  '".$departemen."','".$karyawanid."','".$norequest."'
		)";	
		try{
			$owlPDO->exec($str); //insert hedaer
			
			$strbrg=substr($kodebarang, 0,1);

			if ($strbrg=='9'){
				$strj="select kode from ".$dbname.".project where kode='".$blok."' ";
				$resj=$owlPDO->query($strj) or die(print " Gagal: ".PDOException::getMessage());
				$resj->setFetchMode(PDO::FETCH_OBJ);
				$barj=$resj->fetch();
				$kode=$barj->kode;

				if ($kode!=$blok){
					exit('Warning : Silahkan daftarkan kode project untuk input kode barang :'.$kodebarang);
				}
			}

				$str="insert into ".$dbname.".log_transaksidt (
				`notransaksi`,`kodebarang`,
				`satuan`,`jumlah`,`jumlahlalu`,
				`kodeblok`,`updateby`,`kodekegiatan`,
				`kodemesin`,`kodesegment`,`kmhm`,`kodedptrmn`,`statusblok`)
				values('".$nodok."','".$kodebarang."',
				'".$satuan."',".$qty.",".$jumlahlalu.",
				'".$blok."','".$user."','".$kegiatan."',
				'".$mesin."','".$segment."','".$kmhm."','".$dept."','".$statusblok."')";
				try{
					$owlPDO->exec($str); //insert detail

						// insert detail proporsi pemakaian barang
						$jumlah_blok = 0;
						$round_nilaiproporsi_beforelast=0;
						$jumlah_area=0;
						// cek kegiatan
						$str = "select kelompok from ".$dbname.".setup_kegiatan where kodekegiatan='".$kegiatan."'";
						$res=fetchdata($str);
						if($statusblok == ''){
							$statusblok = $res[0]['kelompok'];
						}
						// ambil jumlah  data blok kecil
						$str0 = "select count(*) as jumlah from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and statusblok = '".$statusblok."' "; 
						$res0 = fetchData($str0);
						$jumlah_blok = $res0[0]['jumlah'];
						// jumlah luas area
						$str1 = "select sum(luasareaproduktif) as t_luasareaproduktif from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and statusblok = '".$statusblok."' and luasareaproduktif > 0 "; 
						$res1 = fetchData($str1);
						$jumlah_area = $res1[0]['t_luasareaproduktif'];
						// end jumlah luas area
						if($jumlah_blok > 0){
							// delete insert
							$str="delete from ".$dbname.".log_transaksidt_detail where notransaksi='".$nodok."' and kodebarang='".$kodebarang."' and kodeblok like '".$blok."%' ";
							try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
							// insert
							$str2="select * from ".$dbname.".setup_blok where kodeorg like '".$blok."%'  and statusblok = '".$statusblok."' and luasareaproduktif > 0 group by kodeorg";
							$res2 = fetchData($str2);
							$no_p = 1;
							foreach($res2 as $key=>$val){
								if($no_p == $jumlah_blok){
									$round_nilaiproporsi = $qty - $round_nilaiproporsi_beforelast;
								}else{
									$nilai_proporsi = ($val['luasareaproduktif'] / $jumlah_area ) * $qty;
									if($iniblok == '1'){
										$round_nilaiproporsi = round($nilai_proporsi,2);
										$round_nilaiproporsi_beforelast += round($nilai_proporsi,2);
									}else{
										$round_nilaiproporsi = round($nilai_proporsi,5);
										$round_nilaiproporsi_beforelast += round($nilai_proporsi,5);
									}
								}

								if($round_nilaiproporsi <= 0){
									$str="delete from ".$dbname.".log_transaksidt where kodebarang='".$kodebarang."' and notransaksi='".$nodok."' and kodeblok = '".$blok."' ";
									try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}

									$str_d="delete from ".$dbname.".log_transaksidt_detail where notransaksi='".$nodok."' and kodebarang='".$kodebarang."' and kodeblok like '".$blok."%' ";
									try{$owlPDO->exec($str_d);}catch(PDOException $e){print $e->getMessage();die();exit(0);}

									exit("warning : Nilai proporsi [".$round_nilaiproporsi."] untuk blok ".$val['kodeorg']." tidak boleh kurang dari sama dengan 0... ");
								}

								// $str="insert into ".$dbname.".log_transaksidt_detail (notransaksi,kodebarang,kodeblok,jumlah) values ('".$nodok."','".$kodebarang."','".$val['kodeorg']."','".$nilai_proporsi."')";
								$str="insert into ".$dbname.".log_transaksidt_detail (
									`notransaksi`,`kodebarang`,
									`satuan`,`jumlah`,`jumlahlalu`,
									`kodeblok`,`updateby`,`kodekegiatan`,
									`kodemesin`,`kodesegment`,`kmhm`,`kodedptrmn`,`statusblok`)
									values('".$nodok."','".$kodebarang."',
									'".$satuan."',".$round_nilaiproporsi.",".$jumlahlalu.",
									'".$val['kodeorg']."','".$user."','".$kegiatan."',
									'".$mesin."','".$segment."','".$kmhm."','".$dept."','".$statusblok."')";
								try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}

								$no_p = $no_p+1;
							}
						}else{
							$str="insert into ".$dbname.".log_transaksidt_detail (
								`notransaksi`,`kodebarang`,
								`satuan`,`jumlah`,`jumlahlalu`,
								`kodeblok`,`updateby`,`kodekegiatan`,
								`kodemesin`,`kodesegment`,`kmhm`,`kodedptrmn`,`statusblok`)
								values('".$nodok."','".$kodebarang."',
								'".$satuan."',".$qty.",".$jumlahlalu.",
								'".$blok."','".$user."','".$kegiatan."',
								'".$mesin."','".$segment."','".$kmhm."','".$dept."','".$statusblok."')";
								try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
						}
						// end insert detail proporsi pemakaian barang


					// cek ada ga pic2
					if($_SESSION['pic2'] != array())
					{
						foreach($_SESSION['pic2'] as $key=>$row)
						{

							// jumlah dari presentase 
							// $qty_presentase = $row['qtypresentase'] * $qty / 100;
							$qty_presentase = $row['jumlah'];

							if($norequest=='')
							{
								$str="insert into ".$dbname.".log_pemakaianpresentase (notransaksi,kodebarang,karyawanid,presentase,jumlah,divisi,kodeblok,kodemesin,kodekegiatan) values ('".$nodok."','".$kodebarang."','".$row['picpic']."','".$row['qtypresentase']."','".$qty_presentase."','".$subunit."','".$blok."','".$mesin."','".$kegiatan."')";
								try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
							}
							else
							{
								$str="update ".$dbname.".log_pemakaianpresentase set presentase='".$row['qtypresentase']."' and jumlah='".$qty_presentase."' where notransaksi='".$norequest."' and kodebarang='".$kodebarang."' and karyawanid='".$row['picpic']."'";
								// exit("error : ".$str);
								try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
							}
						}
					}

					
					if($_SESSION['pic'] != array())
					{
						foreach($_SESSION['pic'] as $key=>$row)
						{
							if($norequest=='')
							{
								$str="insert into ".$dbname.".log_permintaanpicdt (notransaksi,kodebarang,karyawanid,dept,qty,realisasi) values ('".$nodok."','".$kodebarang."','".$row['picpic']."','".$row['departemenpic']."','0','".$row['qtypic']."')";
								try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
							}
							else
							{
								$str="update ".$dbname.".log_permintaanpicdt set realisasi='".$row['qtypic']."' where notransaksi='".$norequest."' and kodebarang='".$kodebarang."' and karyawanid='".$row['picpic']."' and dept='".$row['departemenpic']."'";
								// exit("error : ".$str);
								try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
							}
						}
					}
				}catch(PDOException $e){
					print " Gagal (insert detail on status 0) !: " . $e->getMessage() . "\n"; 
					die(); 
					exit(0);
				}

		}catch(PDOException $e){
			print " Gagal (insert header on status 0) !: " . $e->getMessage() . "\n"; 
			die(); 
			exit(0);
		}
	}		
	//============================
	//status=1
	if($status==1) {
		// cek apakah divisi
		$str_afd = "select * from ".$dbname.".organisasi where kodeorganisasi = '".$subunit."' and tipe='AFDELING' ";
		@$res_afd = fetchData($str_afd);
		if(count($res_afd)>0){
		// apakah status blok kosong
		if($statusblok != ''){
			// cek blok
			if($blok != ''){
				$str_blok = "select * from ".$dbname.".setup_blok where indukblok = '".$blok."' ";
				@$res_blok = fetchData($str_blok);
				// jika blok
				if(count($res_blok)<=0){
					if($kodebarang != ''){
						$stra_ = "select * from ".$dbname.".setup_barangstatusblok where kodebarang = ".$kodebarang." and kodeorg = '".$untukunit."' and status ='1' ";
						@$resa_ = fetchData($stra_);
						if(count($resa_)<=0){
							exit("warning : Jika Sub unit nya divisi dan mengisi statusblok maka wajib mengisi blok... ");
						}
					}
				}else{
					if(strlen($blok) == '4'){
						if($kodebarang != ''){
							$stra_ = "select * from ".$dbname.".setup_barangstatusblok where kodebarang = ".$kodebarang." and kodeorg = '".$untukunit."' and status ='1' ";
							@$resa_ = fetchData($stra_);
							if(count($resa_)<=0){
								exit("warning : Jika Sub unit nya divisi dan mengisi statusblok maka wajib mengisi blok... ");
							}
						}
					}

				}
		}
		
	}
}



		$strbrg=substr($kodebarang, 0,1);

		if ($strbrg=='9'){
			$strj="select kode from ".$dbname.".project where kode='".$blok."' ";
			$resj=$owlPDO->query($strj) or die(print " Gagal: ".PDOException::getMessage());
			$resj->setFetchMode(PDO::FETCH_OBJ);
			$barj=$resj->fetch();
			$kode=$barj->kode;

			if ($kode!=$blok){
				exit('Warning : Silahkan daftarkan kode project untuk input kode barang :'.$kodebarang);
			}
		}
		
		$str="insert into ".$dbname.".log_transaksidt (
		  `notransaksi`,`kodebarang`,
		  `satuan`,`jumlah`,`jumlahlalu`,
		  `kodeblok`,`updateby`,`kodekegiatan`,
		  `kodemesin`,`kodesegment`,`kmhm`,`kodedptrmn`,`statusblok`)
		  values('".$nodok."','".$kodebarang."',
		  '".$satuan."',".$qty.",".$jumlahlalu.",
		  '".$blok."','".$user."','".$kegiatan."',
		  '".$mesin."','".$segment."','".$kmhm."','".$dept."','".$statusblok."')";
		try{
			$owlPDO->exec($str); //insert detail	

						// insert detail proporsi pemakaian barang
						$jumlah_blok = 0;
						$round_nilaiproporsi_beforelast=0;
						$jumlah_area=0;
						// cek kegiatan
						$str = "select kelompok from ".$dbname.".setup_kegiatan where kodekegiatan='".$kegiatan."'";
						$res=fetchdata($str);
						if($statusblok == ''){
							$statusblok = $res[0]['kelompok'];
						}
						// ambil jumlah  data blok kecil
						$str0 = "select count(*) as jumlah from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and statusblok = '".$statusblok."' "; 
						$res0 = fetchData($str0);
						$jumlah_blok = $res0[0]['jumlah'];
						// jumlah luas area
						$str1 = "select sum(luasareaproduktif) as t_luasareaproduktif from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and statusblok = '".$statusblok."' and luasareaproduktif > 0"; 
						$res1 = fetchData($str1);
						$jumlah_area = $res1[0]['t_luasareaproduktif'];
						// end jumlah luas area
						if($jumlah_blok > 0){
							// delete insert
							$str="delete from ".$dbname.".log_transaksidt_detail where notransaksi='".$nodok."' and kodebarang='".$kodebarang."' and kodeblok like '".$blok."%' ";
							try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
							// insert
							$str2="select * from ".$dbname.".setup_blok where kodeorg like '".$blok."%'  and statusblok = '".$statusblok."' and luasareaproduktif > 0 group by kodeorg";
							$res2 = fetchData($str2);
							$no_p = 1;
							foreach($res2 as $key=>$val){
								if($no_p == $jumlah_blok){
									$round_nilaiproporsi = $qty - $round_nilaiproporsi_beforelast;
								}else{
									$nilai_proporsi = ($val['luasareaproduktif'] / $jumlah_area ) * $qty;
									if($iniblok == '1'){
										$round_nilaiproporsi = round($nilai_proporsi,2);
										$round_nilaiproporsi_beforelast += round($nilai_proporsi,2);
									}else{
										$round_nilaiproporsi = round($nilai_proporsi,5);
										$round_nilaiproporsi_beforelast += round($nilai_proporsi,5);
									}
								}

								if($round_nilaiproporsi <= 0){
									$str="delete from ".$dbname.".log_transaksidt where kodebarang='".$kodebarang."' and notransaksi='".$nodok."' and kodeblok = '".$blok."' ";
									try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}

									$str_d="delete from ".$dbname.".log_transaksidt_detail where notransaksi='".$nodok."' and kodebarang='".$kodebarang."' and kodeblok like '".$blok."%' ";
									try{$owlPDO->exec($str_d);}catch(PDOException $e){print $e->getMessage();die();exit(0);}

									exit("warning : Nilai proporsi [".$round_nilaiproporsi."] untuk blok ".$val['kodeorg']." tidak boleh kurang dari sama dengan 0... ");
								}

								// $str="insert into ".$dbname.".log_transaksidt_detail (notransaksi,kodebarang,kodeblok,jumlah) values ('".$nodok."','".$kodebarang."','".$val['kodeorg']."','".$nilai_proporsi."')";
								$str="insert into ".$dbname.".log_transaksidt_detail (
									`notransaksi`,`kodebarang`,
									`satuan`,`jumlah`,`jumlahlalu`,
									`kodeblok`,`updateby`,`kodekegiatan`,
									`kodemesin`,`kodesegment`,`kmhm`,`kodedptrmn`,`statusblok`)
									values('".$nodok."','".$kodebarang."',
									'".$satuan."',".$round_nilaiproporsi.",".$jumlahlalu.",
									'".$val['kodeorg']."','".$user."','".$kegiatan."',
									'".$mesin."','".$segment."','".$kmhm."','".$dept."','".$statusblok."')";
								try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}

								$no_p = $no_p+1;
							}
						}else{
							$str="insert into ".$dbname.".log_transaksidt_detail (
								`notransaksi`,`kodebarang`,
								`satuan`,`jumlah`,`jumlahlalu`,
								`kodeblok`,`updateby`,`kodekegiatan`,
								`kodemesin`,`kodesegment`,`kmhm`,`kodedptrmn`,`statusblok`)
								values('".$nodok."','".$kodebarang."',
								'".$satuan."',".$qty.",".$jumlahlalu.",
								'".$blok."','".$user."','".$kegiatan."',
								'".$mesin."','".$segment."','".$kmhm."','".$dept."','".$statusblok."')";
								try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
						}
						// end insert detail proporsi pemakaian barang
	
			
			// cek ada ga pic2
			if($_SESSION['pic2'] != array())
			{
				foreach($_SESSION['pic2'] as $key=>$row)
				{

					// jumlah dari presentase 							
					// $qty_presentase = $row['qtypresentase'] * $qty / 100;
					$qty_presentase = $row['jumlah'];

					if($norequest=='')
					{
						$str="insert into ".$dbname.".log_pemakaianpresentase (notransaksi,kodebarang,karyawanid,presentase,jumlah,divisi,kodeblok,kodemesin,kodekegiatan) values ('".$nodok."','".$kodebarang."','".$row['picpic']."','".$row['qtypresentase']."','".$qty_presentase."','".$subunit."','".$blok."','".$mesin."','".$kegiatan."')";
						try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
					}
					else
					{
						$str="update ".$dbname.".log_pemakaianpresentase set presentase='".$row['qtypresentase']."' and jumlah='".$qty_presentase."' where notransaksi='".$norequest."' and kodebarang='".$kodebarang."' and karyawanid='".$row['picpic']."'";
						// exit("error : ".$str);
						try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
					}
				}
			}

			
			if($_SESSION['pic'] != array())
			{
				foreach($_SESSION['pic'] as $key=>$row)
				{
					if($norequest=='')
					{
						$str="insert into ".$dbname.".log_permintaanpicdt (notransaksi,kodebarang,karyawanid,dept,qty,realisasi) values ('".$nodok."','".$kodebarang."','".$row['picpic']."','".$row['departemenpic']."','0','".$row['qtypic']."')";
						try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
					}
					else
					{
						$str="update ".$dbname.".log_permintaanpicdt set realisasi='".$row['qtypic']."' where notransaksi='".$norequest."' and kodebarang='".$kodebarang."' and karyawanid='".$row['picpic']."' and dept='".$row['departemenpic']."'";
						try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
					}
				}
			}
		}catch(PDOException $e){
			print " Gagal (insert detail on status 1) !: " . $e->getMessage() . "\n"; 
			die(); 
			exit(0);
		}
	}
	//============================update detail
	//status=2
	if($status==2) { 

		$strbrg=substr($kodebarang, 0,1);

		if ($strbrg=='9'){
			$strj="select kode from ".$dbname.".project where kode='".$blok."' ";
			$resj=$owlPDO->query($strj) or die(print " Gagal: ".PDOException::getMessage());
			$resj->setFetchMode(PDO::FETCH_OBJ);
			$barj=$resj->fetch();
			$kode=$barj->kode;

			if ($kode!=$blok){
				exit('Warning : Silahkan daftarkan kode project untuk input kode barang :'.$kodebarang);
			}
		}
		
		
		
		$str="update ".$dbname.".log_transaksidt set
			  `jumlah`=".$qty.",
			  `updateby`=".$user.",
			  `kodekegiatan`='".$kegiatan."',
			  `kodemesin`='".$mesin."',
			  `kodesegment`='".$segment."',
			  `kodeblok`='".$blok."',
			  `kmhm`='".$kmhm."',
			  `kodedptrmn`='".$dept."',
			  `statusblok`='".$statusblok."'
			  where `notransaksi`='".$nodok."'
			  and `kodebarang`='".$kodebarang."'
			  and `kodeblok`='".$_POST['olbBlok']."'
			  and `kodemesin`='".$_POST['oldmesin']."'";
		//exit('Warning'.$str);
		// $affected_rows=$owlPDO->exec($str);//insert detail
		// if($affected_rows<1){
			// print " Gagal (update detail on status 2) !: " . $e->getMessage() . "\n"; 
			// die(); 
			// exit(0);
		// }
		try{
			$owlPDO->exec($str); //insert detail	
			
						// insert detail proporsi pemakaian barang
						$jumlah_blok = 0;
						$round_nilaiproporsi_beforelast=0;
						$jumlah_area=0;
						// cek kegiatan
						$str = "select kelompok from ".$dbname.".setup_kegiatan where kodekegiatan='".$kegiatan."'";
						$res=fetchdata($str);
						if($statusblok == ''){
							$statusblok = $res[0]['kelompok'];
						}
						// ambil jumlah  data blok kecil
						$str0 = "select count(*) as jumlah from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and statusblok = '".$statusblok."' "; 
						$res0 = fetchData($str0);
						$jumlah_blok = $res0[0]['jumlah'];
						// jumlah luas area
						$str1 = "select sum(luasareaproduktif) as t_luasareaproduktif from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and statusblok = '".$statusblok."' and luasareaproduktif > 0 "; 
						$res1 = fetchData($str1);
						$jumlah_area = $res1[0]['t_luasareaproduktif'];
						// end jumlah luas area
						if($jumlah_blok > 0){
							// delete insert
							$str="delete from ".$dbname.".log_transaksidt_detail where notransaksi='".$nodok."' and kodebarang='".$kodebarang."' and kodeblok like '".$blok."%' ";
							try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
							// insert
							$str2="select * from ".$dbname.".setup_blok where kodeorg like '".$blok."%'  and statusblok = '".$statusblok."' and luasareaproduktif > 0 group by kodeorg";
							$res2 = fetchData($str2);
							$no_p = 1;
							foreach($res2 as $key=>$val){
								if($no_p == $jumlah_blok){
									$round_nilaiproporsi = $qty - $round_nilaiproporsi_beforelast;
								}else{
									$nilai_proporsi = ($val['luasareaproduktif'] / $jumlah_area ) * $qty;
									if($iniblok == '1'){
										$round_nilaiproporsi = round($nilai_proporsi,2);
										$round_nilaiproporsi_beforelast += round($nilai_proporsi,2);
									}else{
										$round_nilaiproporsi = round($nilai_proporsi,5);
										$round_nilaiproporsi_beforelast += round($nilai_proporsi,5);
									}
								}

								if($round_nilaiproporsi <= 0){
									$str="delete from ".$dbname.".log_transaksidt where kodebarang='".$kodebarang."' and notransaksi='".$nodok."' and kodeblok = '".$blok."' ";
									try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}

									$str_d="delete from ".$dbname.".log_transaksidt_detail where notransaksi='".$nodok."' and kodebarang='".$kodebarang."' and kodeblok like '".$blok."%' ";
									try{$owlPDO->exec($str_d);}catch(PDOException $e){print $e->getMessage();die();exit(0);}

									exit("warning : Nilai proporsi [".$round_nilaiproporsi."] untuk blok ".$val['kodeorg']." tidak boleh kurang dari sama dengan 0... ");
								}
								
								// $str="insert into ".$dbname.".log_transaksidt_detail (notransaksi,kodebarang,kodeblok,jumlah) values ('".$nodok."','".$kodebarang."','".$val['kodeorg']."','".$nilai_proporsi."')";
								$str="insert into ".$dbname.".log_transaksidt_detail (
									`notransaksi`,`kodebarang`,
									`satuan`,`jumlah`,`jumlahlalu`,
									`kodeblok`,`updateby`,`kodekegiatan`,
									`kodemesin`,`kodesegment`,`kmhm`,`kodedptrmn`,`statusblok`)
									values('".$nodok."','".$kodebarang."',
									'".$satuan."',".$round_nilaiproporsi.",".$jumlahlalu.",
									'".$val['kodeorg']."','".$user."','".$kegiatan."',
									'".$mesin."','".$segment."','".$kmhm."','".$dept."','".$statusblok."')";
								try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}

								$no_p = $no_p+1;
							}
						}else{
							$str="insert into ".$dbname.".log_transaksidt_detail (
								`notransaksi`,`kodebarang`,
								`satuan`,`jumlah`,`jumlahlalu`,
								`kodeblok`,`updateby`,`kodekegiatan`,
								`kodemesin`,`kodesegment`,`kmhm`,`kodedptrmn`,`statusblok`)
								values('".$nodok."','".$kodebarang."',
								'".$satuan."',".$qty.",".$jumlahlalu.",
								'".$blok."','".$user."','".$kegiatan."',
								'".$mesin."','".$segment."','".$kmhm."','".$dept."','".$statusblok."')";
								try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
						}
						// end insert detail proporsi pemakaian barang

			
			
			// cek ada ga pic2
			if($_SESSION['pic2'] != array())
			{
				$str="delete from ".$dbname.".log_pemakaianpresentase where notransaksi='".$nodok."' and kodebarang='".$kodebarang."'";
				try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
				foreach($_SESSION['pic2'] as $key=>$row)
				{

					// jumlah dari presentase 
					// $qty_presentase = $row['qtypresentase'] * $qty / 100;
					$qty_presentase = $row['jumlah'];

					if($norequest=='')
					{
						$str="insert into ".$dbname.".log_pemakaianpresentase (notransaksi,kodebarang,karyawanid,presentase,jumlah,divisi,kodeblok,kodemesin,kodekegiatan) values ('".$nodok."','".$kodebarang."','".$row['picpic']."','".$row['qtypresentase']."','".$qty_presentase."','".$subunit."','".$blok."','".$mesin."','".$kegiatan."')";
						try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
					}
					else
					{
						$str="update ".$dbname.".log_pemakaianpresentase set presentase='".$row['qtypresentase']."' and jumlah='".$qty_presentase."' where notransaksi='".$norequest."' and kodebarang='".$kodebarang."' and karyawanid='".$row['picpic']."'";
						// exit("error : ".$str);
						try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
					}
				}
			}
			
			if($_SESSION['pic'] != array())
			{
				$str="delete from ".$dbname.".log_permintaanpicdt where notransaksi='".$nodok."' and kodebarang='".$kodebarang."'";
				try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
				foreach($_SESSION['pic'] as $key=>$row)
				{
					if($norequest=='')
					{
						$str="insert into ".$dbname.".log_permintaanpicdt (notransaksi,kodebarang,karyawanid,dept,qty,realisasi) values ('".$nodok."','".$kodebarang."','".$row['picpic']."','".$row['departemenpic']."','0','".$row['qtypic']."')";
						try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
					}
					else
					{
						$str="update ".$dbname.".log_permintaanpicdt set realisasi='".$row['qtypic']."' where notransaksi='".$norequest."' and kodebarang='".$kodebarang."' and karyawanid='".$row['picpic']."'";
						try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
					}
				}
			}
		}catch(PDOException $e){
			print " Gagal (insert detail on status 1) !: " . $e->getMessage() . "\n"; 
			die(); 
			exit(0);
		}
	}
	//============================return message
	//status=3
	if($status==3) {	
	   echo " Gagal: Data has been posted";
	   exit(0);
	}
	//============================return message
	//status=4
	if($status==4) {	
		echo " Gagal: Company code of the Recipient is not defined";
		exit(0);
	}
	//===========delete ==========================
	//status=5
	if($status==5) { //delete item not header		   	 
		$str="delete from ".$dbname.".log_transaksidt where kodebarang='".$kodebarang."'
			  and notransaksi='".$nodok."' and kodeblok='".$blok."' and kodemesin='".$kodemesin."'"; #exit("error".$str);
		$affected_rows=$owlPDO->exec($str);
		if($affected_rows>0){
			$str="delete from ".$dbname.".log_transaksidt_detail where notransaksi='".$nodok."' and kodebarang='".$kodebarang."'  and kodeblok like '".$blok."%'  ";
			$affected_rows=$owlPDO->exec($str);
		}
		
		if($norequest=='')
		{
			$str_0="delete from ".$dbname.".log_pemakaianpresentase where notransaksi='".$nodok."' and kodebarang='".$kodebarang."'";
			$str="delete from ".$dbname.".log_permintaanpicdt where notransaksi='".$nodok."' and kodebarang='".$kodebarang."'";
		}
		else
		{
			$str="update ".$dbname.".log_permintaanpicdt set realisasi='0' where notransaksi='".$norequest."' and kodebarang='".$kodebarang."'";
		}
		try{$owlPDO->exec($str);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
		try{$owlPDO->exec($str_0);}catch(PDOException $e){print $e->getMessage();die();exit(0);}
	}
	
	//ambil data untuk ditampilkan
	$strj="select a.*,b.untukpt as pt,b.norequest as norequest,
		   b.untukunit as unit from ".$dbname.".log_transaksidt a 
		   left join  ".$dbname.".log_transaksiht b
		   on a.notransaksi=b.notransaksi
		   where a.notransaksi='".$nodok."' order by waktutransaksi asc ";
	
	$resj=$owlPDO->query($strj) or die(print " Gagal: ".PDOException::getMessage());
	$resj->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($barj=$resj->fetch()) {
		$no+=1;
		//ambil namabarang
		$namabarangk='';
		$strk="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$barj->kodebarang."'";
		$resk=$owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
		$resk->setFetchMode(PDO::FETCH_OBJ);
		while($bark=$resk->fetch()) {
			$namabarangk=$bark->namabarang;
		}
		//ambil kegiatan
		$namakegiatan='';
		$strk="select namakegiatan from ".$dbname.".setup_kegiatan where kodekegiatan='".$barj->kodekegiatan."'";
		$resk=$owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
		$resk->setFetchMode(PDO::FETCH_OBJ);
		while($bark=$resk->fetch())
		{
			$namakegiatan=$bark->namakegiatan;
		}
		$dataspk=makeOption($dbname,'lgl_pengajuanspkht','notransaksi,jenissupplier',"notransaksi='".$barj->kodeblok."'");
		if($dataspk[$blok]!=''){
			$namakegiatan=$barj->kodekegiatan;
		}
		$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',"kodesegment='".$barj->kodesegment."'");
		// $nmblok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$barj->kodeblok."'");
		$nmblok = makeOption($dbname,'organisasi','indukblok,namaindukblok',"indukblok='".$barj->kodeblok."'");
		$nmpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$barj->pt."'");
		$nmunit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$barj->unit."'");
		@$nopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$barj->kodemesin."'");
		if(@$nopol[$barj->kodemesin]!=''){
			$kodemesinx=$barj->kodemesin." - ".$nopol[$barj->kodemesin];
		}else{
			$kodemesinx=$barj->kodemesin;
		}
		echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td>".$barj->kodebarang."</td>
			<td>".$namabarangk."</td>
			<td>".$barj->satuan."</td>
			<td align=right>".number_format($barj->jumlah,2,'.',',')."</td>
			<td hidden align=center>".$barj->pt."</td>
			<td>".$barj->unit." - ".$nmunit[$barj->unit]."</td>
			<td>".$barj->kodeblok." - ".$nmblok[$barj->kodeblok]."</td>
			<td hidden>".$optSegment[$barj->kodesegment]."</td>
			<td>".$kodemesinx."<br>".getNopol($kodemesinx)."</td>
			<td align=right>".$barj->kmhm."</td>
			<td>".$namakegiatan."</td>
			<td>".$barj->kodedptrmn."</td>
			<td>
				<table>";
					if($barj->norequest == ''){
						$where = " notransaksi='".$nodok."' and kodebarang='".$barj->kodebarang."'";
					}else{
						$where = " notransaksi='".$barj->norequest."' and kodebarang='".$barj->kodebarang."' and realisasi!='0'";
					}
					$nopic = 0;
					$str="select * from ".$dbname.".log_permintaanpicdt where ".$where."";
					$res = fetchData($str);
					foreach($res as $key=>$val){
						$nopic++;
						$optNmKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
						echo"<tr>
							<td>".$nopic.".</td>
							<td>".$optNmKary[$val['karyawanid']]."</td>
						</tr>";
					}
				echo"</table>
			</td>";
                
			if($barj->statussaldo==0){
			echo"
				<td align=center>
					<!--<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editBast('".$barj->kodebarang."','".$namabarangk."','".$barj->satuan."','".$barj->jumlah."','".$barj->kodeblok."','".$barj->kodekegiatan."','".$barj->kodemesin."','".$barj->kodesegment."','".$barj->kmhm."');\">
					&nbsp -->
					<img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delBast('".$nodok."','".$barj->kodebarang."','".$barj->kodeblok."','".$barj->norequest."','".$barj->kodemesin."');\">
				</td>";
			}else{
				echo"<td>Posted</td>";
			}
                echo"</tr>";
                
	}
} else {
	echo " Error: Transaction Period missing";
}

$_SESSION['pic']=array();
$_SESSION['pic2']=array();