<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses = checkPostGet('proses', '');
$instiket = checkPostGet('instiket', '');
$tiket = checkPostGet('tiket', '');
$notiket = checkPostGet('notiket', '');
$nospb = checkPostGet('nospb', '');
$nokendaraan = checkPostGet('nokendaraan', '');
$tanggalmasuk = checkPostGet('tanggalmasuk', '');
$jammasuk = checkPostGet('jammasuk', '');
$menitmasuk = checkPostGet('menitmasuk', '');
$beratmasuk = str_replace(',','',checkPostGet('beratmasuk', ''));
$potongan = str_replace(',','',checkPostGet('potongan', ''));
$tanggalkeluar = checkPostGet('tanggalkeluar', '');
$jamkeluar = checkPostGet('jamkeluar', '');
$menitkeluar = checkPostGet('menitkeluar', '');
$beratkeluar = str_replace(',','',checkPostGet('beratkeluar', ''));
$netto = str_replace(',','',checkPostGet('netto', ''));
$totalrupiah = str_replace(',','',checkPostGet('totalrupiah', ''));
$bbnPajak = str_replace(',','',checkPostGet('bbnPajak', ''));
$totalrupiahpph = str_replace(',','',checkPostGet('totalrupiahpph', ''));
$prsnAll = str_replace(',','',checkPostGet('prsnAll', ''));
$totalpembayaran = str_replace(',','',checkPostGet('totalpembayaran', ''));
$pt = checkPostGet('pt', '');
$kodepabrik = checkPostGet('kodepabrik', '');
$koderamp = checkPostGet('koderamp', '');
$tanggal = tanggalsystem(checkPostGet('tanggal', ''));
$jjg = str_replace(',','',checkPostGet('jjg', ''));
$supplier = checkPostGet('supplier', '');
$kg = checkPostGet('kg', '');
$harga = str_replace(',','',checkPostGet('harga', ''));
$pages = checkPostGet('page', '');
$idtrans = checkPostGet('idtrans', '');

$caript = checkPostGet('caript', '');
$cariunit = checkPostGet('cariunit', '');
$carikoderamp = checkPostGet('carikoderamp', '');
$carisupplier = checkPostGet('carisupplier', '');
$caritanggal = checkPostGet('caritanggal', '');

$tmplkodepabrik = checkPostGet('tmplkodepabrik', '');
$tmplpt = checkPostGet('tmplpt', '');
$tmplkoderamp = checkPostGet('tmplkoderamp', '');
$tmplsupplier = checkPostGet('tmplsupplier', '');
$tmpltanggal = tanggalsystem(checkPostGet('tmpltanggal', ''));

switch($proses)
{
	// case'getnotiket':
		// $str = "select *"
	// break;
	
	case'getramp':
		### Get Pabrik
		$optpabrik = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		### Get Ramp
		$optramp = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($pt!='')
		{
			// $str="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='RAMP' and status=1)";
			$str="select kode,kelompok from ".$dbname.".log_5klsupplier where tipe='RAMP' and kode like 'R".$pt."%' order by kelompok";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				if($bar['kode']==$koderamp)
				{
					$optramp.="<option value='".$bar['kode']."' selected>".$bar['kode']."-".$bar['kelompok']."</option>";	
				}
				else
				{
					$optramp.="<option value='".$bar['kode']."'>".$bar['kode']."-".$bar['kelompok']."</option>";	
				}
			}
			
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk like '".$pt."%' order by namaorganisasi";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				if($bar['kodeorganisasi']==$kodepabrik)
				{
					$optpabrik.="<option value='".$bar['kodeorganisasi']."' selected>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
				}
				else
				{
					$optpabrik.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
				}
			}
		}
		
		echo $optramp."##".$optpabrik;
	break;
	
	case'getcariramp':
		### Get Pabrik
		$optpabrik = "<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk like '".$pt."%' order by namaorganisasi";
		// $str="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='RAMP' and status=1)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optpabrik.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
		}
	
		### Get Ramp
		$optramp = "<option value=''>".$_SESSION['lang']['all']."</option>";
		// $str="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='RAMP' and status=1)";
		$str="select kode,kelompok from ".$dbname.".log_5klsupplier where tipe='RAMP' and kode like 'R".$pt."%' order by kelompok";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optramp.="<option value='".$bar['kode']."'>".$bar['kode']."-".$bar['kelompok']."</option>";
		}
		
		echo $optramp."##".$optpabrik;
	break;
	
	case'getsupplier':
		### Get Ramp
		$optsupplier = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>	";
		if($koderamp!='')
		{
			$str="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='RAMP' and status=1)";
			// $str="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid like '".$koderamp."%' order by namasupplier";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				if($bar['supplierid']==$supplier)
				{
					$optsupplier.="<option value='".$bar['supplierid']."' selected>".$bar['supplierid']."-".$bar['namasupplier']."</option>";
				}
				else
				{
					$optsupplier.="<option value='".$bar['supplierid']."'>".$bar['supplierid']."-".$bar['namasupplier']."</option>";	
				}
			}
		}
		
		echo $optsupplier;
	break;
	
	case'getsupplier2':
		$str = "select kodebarang,namabarang from " . $dbname . ".log_5masterbarang where kelompokbarang=400";
		//$qry=mysql_query($str) or die(mysql_error());
		//while($res=mysql_fetch_assoc($qry)){
		$qry = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($res = $qry->fetch()) {
			
		}
	
		### Get Supplier
		$sql="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='RAMP' and status=1)";
		// $str="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid like '".$koderamp."%' order by namasupplier";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		echo "<table style='border:1px solid gray;'><tr><td>";
		while($bar=$res->fetch())
		{
			echo "<li style='float:left;width:200px;list-style-type:none'><input type='checkbox' id='tmplsupplier' name='tmplsupplier[]' value='".$bar['supplierid']."' checked />".$bar['namasupplier']."</li>";
		}
		echo "</td></tr></table>";
	break;
	
	case'download':
		header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=PENERIMAANTBSRAMP.csv");

		echo $_SESSION['lang']['perusahaan'].",".$_SESSION['lang']['unit']."/".$_SESSION['lang']['pabrik'].",".$_SESSION['lang']['koderamp'].",".$_SESSION['lang']['kdsupplierramo'].",".$_SESSION['lang']['nokendaraan'].",Tanggal Masuk,Jam Masuk,Menit Masuk,Tanggal Keluar,Jam Keluar,Menit Keluar,Berat Masuk,Berat Keluar,Potongan(KG),Beban Pajak(1=ditanggung;0=tidak ditanggung),Persen Pajak,Harga per Kg\n";
		
		$arrSup = explode(',',$tmplsupplier);
		foreach($arrSup as $key)
		{
			echo $tmplpt.",".$tmplkodepabrik.",".$tmplkoderamp.",".$key.",BH1541SKS,".$tmpltanggal.",07,45,".$tmpltanggal.",08,11,13476,3421,254,1,0.5,1800\n";
		}
	break;
	
	case'getcarisupplier':
		### Get Ramp
		$optsupplier = "<option value=''>".$_SESSION['lang']['all']."</option>	";
		$str="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='RAMP' and status=1) order by namasupplier";
		// $str="select supplierid,namasupplier from ".$dbname.".log_5supplier where (supplierid like '".$koderamp."%' and supplierid like 'R%' and supplierid like '%".$pt."%') order by namasupplier";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optsupplier.="<option value='".$bar['supplierid']."'>".$bar['supplierid']."-".$bar['namasupplier']."</option>";	
		}
		
		echo $optsupplier;
	break;
	
    case'insert':
		$waktumasuk = tanggalsystem($tanggalmasuk).$jammasuk.$menitmasuk;
		$waktukeluar = tanggalsystem($tanggalkeluar).$jamkeluar.$menitkeluar;
		
		$waktumasukx = tanggalsystemx($waktumasuk);
		$waktukeluarx = tanggalsystemx($waktukeluar);
		
		if($pt == '' || $kodepabrik == '' || $koderamp == '' || $supplier == '')
		{
			exit('warning: Lengkapi isian form untuk menyimpan data.');
		}
		
		if($waktumasuk>$waktukeluar)
		{
			exit('warning: Waktu masuk harus lebih kecil dari waktu keluar.');
		}
		
		if($netto <= 0)
		{
			exit("warning : Periksa kembali netto, masih lebih kecil atau sama dengan 0.");
		}
		
		##Get No Tiket
		$str = "select * from ".$dbname.".pmn_penerimaantbsramp where notiket like '".$instiket."%' order by notiket desc limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$notiket = $bar['notiket'];
		if($notiket=='')
		{
			$notiket = $instiket."000001";
		}
		else
		{
			$notiket = str_replace($instiket,'',$notiket);
			$notiket = $instiket.addZero($notiket+1,6);
		}
		
		$str = "insert into ".$dbname.".pmn_penerimaantbsramp 
				(notiket,kodeorg,unit,koderamp,kodesupplier,nospb,nokendaraan,datein,dateout,beratmasuk,beratkeluar,potongan,netto,jjg,harga,beban_pajak,persenpajak,totalrupiah,rupiahpajak,posted,updateby) values 
				('".$notiket."','".$pt."','".$kodepabrik."','".$koderamp."','".$supplier."','".$nospb."','".$nokendaraan."','".$waktumasukx."','".$waktukeluarx."','".$beratmasuk."','".$beratkeluar."','".$potongan."','".$netto."','".$jjg."','".$harga."','".$bbnPajak."','".$prsnAll."','".$totalpembayaran."','".$totalrupiahpph."','0','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;	 
	
	case'update':
		$waktumasuk = tanggalsystem($tanggalmasuk).$jammasuk.$menitmasuk;
		$waktukeluar = tanggalsystem($tanggalkeluar).$jamkeluar.$menitkeluar;
		
		$waktumasukx = tanggalsystemx($waktumasuk);
		$waktukeluarx = tanggalsystemx($waktukeluar);
		
		if($pt == '' || $kodepabrik == '' || $koderamp == '' || $supplier == '')
		{
			exit('warning: Lengkapi isian form untuk menyimpan data.');
		}
		
		if($waktumasuk>$waktukeluar)
		{
			exit('warning: Waktu masuk harus lebih kecil dari waktu keluar.');
		}
		
		if($netto <= 0)
		{
			exit("warning : Periksa kembali netto, masih lebih kecil atau sama dengan 0.");
		}
	
		$str = "update ".$dbname.".pmn_penerimaantbsramp set 
			kodeorg='".$pt."',
			unit='".$kodepabrik."',
			koderamp='".$koderamp."',
			kodesupplier='".$supplier."',
			nokendaraan='".$nokendaraan."',
			datein='".$waktumasukx."',
			dateout='".$waktukeluarx."',
			beratmasuk='".$beratmasuk."',
			beratkeluar='".$beratkeluar."',
			potongan='".$potongan."',
			netto='".$netto."',
			harga='".$harga."',
			beban_pajak='".$bbnPajak."',
			persenpajak='".$prsnAll."',
			totalrupiah='".$totalpembayaran."',
			rupiahpajak='".$totalrupiahpph."',
			updateby='".$_SESSION['standard']['userid']."'
			where notiket='".$instiket."".$tiket."'";
			
		try
		{
			$owlPDO->exec($str); 
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;	
	break;
	
	case'delData':
		$str = "delete from ".$dbname.".pmn_penerimaantbsramp 
				where notiket='".$pt."'";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

	// case'posting':
	// 	$sData="select * from ".$dbname.".pmn_penerimaantbsramp where notiket='".$notiket."'";
	// 	$dataH = fetchData($sData);
		
	// 	#tanggal lalu
	// 	$tglLalu=strtotime('-1 day',strtotime($dataH[0]['datein'])) ;
	// 	$tglLalu=date("Y-m-d",$tglLalu);
		
	// 	#cek periode unit
	// 	$sPeriodeAk="select * from ".$dbname.".setup_periodeakuntansi 
	// 	             where tanggalmulai>='".$dataH[0]['datein']."' and tanggalsampai<='".$dataH[0]['datein']."' 
	// 	             and tutupbuku=0 and kodeorg='".$dataH[0]['unit']."'";
	// 	$rPeriodeAk=fetchdata($sPeriodeAk);
		
	// 	if(count($rPeriodeAk)!=0){
	// 		exit('warning :'.$_SESSION['lang']['notifperiode']);
	// 	}
		
	// 	#cek apakah ada transaksi yang belum posting di tanggal sebelumnya
	// 	$scek="select * from ".$dbname.".pmn_penerimaantbsramp where datein < '".$tanggal."' and posted = '0'";
	// 	$rCek=fetchData($scek);
	// 	if($rCek[0]['kodesupplier']!='')
	// 	{
	// 		// exit("Warning:\nHarap posting transaksi dahulu untuk tanggal : ".tanggalnormal($rCek[0]['datein']));	
	// 	}
		
	// 	#====cek periode
	// 	$error0 = "";
	// 	$optTglAcc=makeOption($dbname,'setup_periodeakuntansi','kodeorg,tanggalmulai',"kodeorg='".$dataH[0]['unit']."'");
	// 	$tgl = str_replace("-","",substr($dataH[0]['datein'],0,10));
		
	// 	if(tanggalsystem(tanggalnormal($optTglAcc[$dataH[0]['unit']])) > $tgl)
	// 	{
	// 		exit('Error:Date beyond active period'.tanggalsystem(tanggalnormal($optTglAcc[$dataH[0]['kodeorg']])));
	// 	}
		
	// 	#====notransaksi jurnal akun debet serta kredit dari parameter jurnal
	// 	$kodejurnal="INVTR";
	// 	$optInduk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$dataH[0]['unit']."'");
	// 	$whereNoindukph = "kodekelompok='".$kodejurnal."' and kodeorg='".$optInduk[$dataH[0]['unit']]."'";
	//     $query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',$whereNoindukph);
	//     $noKon = fetchData($query);
	//     $tmpC = $noKon[0]['nokounter'];
	//     $tmpC++;
	//     $counterjurnal = addZero($tmpC,3);
	//     $nojurnal = $tgl."/".$dataH[0]['unit']."/".$kodejurnal."/".$counterjurnal;
	// 	$noreferensi = $dataH[0]['notiket'];

	//     #akun debet serta krdit
	//     $query2 = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',"jurnalid='".$kodejurnal."' and aktif=1");
	//     $dtnoakun = fetchData($query2);
		
	// 	$rpotongan = round($dataH[0]['potongan']);
	// 	$rnetto = $dataH[0]['beratmasuk'] - $dataH[0]['beratkeluar'] - $rpotongan;
		
	// 	$totalpersediaan = $rnetto * $dataH[0]['harga'];
	// 	$pph = ($dataH[0]['persenpajak'] * ($totalpersediaan)) / 100;
	// 	if($dataH[0]['beban_pajak']==0)
	// 	{
	// 		$totaljuranl = $totalpersediaan;
	// 		$totPersediaandt = ($totalpersediaan) - $pph;
	// 	}
	// 	else
	// 	{
	// 		$totaljuranl = $totalpersediaan + $pph;
	// 		$totPersediaandt = $totalpersediaan;
	// 	}
	//     #=== Transform Data ===
	// 	$dataRes['header'] = array();
	// 	$dataRes['detail'] = array();

	// 	# Prep Header
	// 	$dataRes['header'] = array(
	// 	    'nojurnal'=>$nojurnal,
	// 	    'kodejurnal'=>$kodejurnal,
	// 	    'tanggal'=>substr($dataH[0]['datein'],0,10),
	// 	    'tanggalentry'=>date('Ymd'),
	// 	    'posting'=>'0',
	// 	    'totaldebet'=>$totaljuranl,
	// 	    'totalkredit'=>$totaljuranl*(-1),
	// 	    'amountkoreksi'=>'0',
	// 	    'noreferensi'=>$noreferensi,
	// 	    'autojurnal'=>'1',
	// 	    'matauang'=>'IDR',
	// 	    'kurs'=>'1',
	// 	    'revisi'=>'0'
	// 	);
	// 	$noUrut=1;
	// 	$dataRes['detail'][] = array(
	// 		'nojurnal'=>$nojurnal,
	// 		'tanggal'=>substr($dataH[0]['datein'],0,10),
	// 		'nourut'=>$noUrut,
	// 		'noakun'=>$dtnoakun[0]['noakundebet'],
	// 		'keterangan'=>'Persediaan TBS kode unit :'.$dataH[0]['unit'].', kode ramp : '.$dataH[0]['koderamp'].', kode supplier : '.$dataH[0]['kodesupplier'].' pada tanggal '.tanggalnormal(substr($dataH[0]['datein'],0,10)),
	// 		'jumlah'=>$totPersediaandt,
	// 		'matauang'=>'IDR',
	// 		'kurs'=>'1',
	// 		'kodeorg'=>$dataH[0]['unit'],
	// 		'kodekegiatan'=>'',
	// 		'kodeasset'=>'',
	// 		'kodebarang'=>'',
	// 		'nik'=>'',
	// 		'kodecustomer'=>'',
	// 		'kodesupplier'=>$dataH[0]['kodesupplier'],
	// 		'noreferensi'=>$noreferensi,
	// 		'noaruskas'=>'',
	// 		'kodevhc'=>'',
	// 		'nodok'=>'',
	// 		'kodeblok'=>'',
	// 		'revisi'=>'0',
	// 		'kodesegment' => '0000000001');
	// 	// $noUrut=2;
	// 	// if($dataH[0]['beban_pajak']==1)
	// 	// {
	// 		// $dataRes['detail'][] = array(
	// 			// 'nojurnal'=>$nojurnal,
	// 			// 'tanggal'=>substr($dataH[0]['datein'],0,10),
	// 			// 'nourut'=>$noUrut,
	// 			// 'noakun'=>$dtnoakun[0]['noakundebet'],
	// 			// 'keterangan'=>'Beban Pajak 22 dari pengakuan penerimaan TBS  pada tanggal '.tanggalnormal(substr($dataH[0]['datein'],0,10)),
	// 			// 'jumlah'=>$pph,
	// 			// 'matauang'=>'IDR',
	// 			// 'kurs'=>'1',
	// 			// 'kodeorg'=>$dataH[0]['unit'],
	// 			// 'kodekegiatan'=>'',
	// 			// 'kodeasset'=>'',
	// 			// 'kodebarang'=>'',
	// 			// 'nik'=>'',
	// 			// 'kodecustomer'=>'',
	// 			// 'kodesupplier'=>$dataH[0]['kodesupplier'],
	// 			// 'noreferensi'=>$noreferensi,
	// 			// 'noaruskas'=>'',
	// 			// 'kodevhc'=>'',
	// 			// 'nodok'=>'',
	// 			// 'kodeblok'=>'',
	// 			// 'revisi'=>'0',
	// 			// 'kodesegment' => '0000000001');
	// 	// }
	// 	// foreach($lstSupp as $dtSupp){
	// 		// foreach($lstKlasifika as $dtKlasifikasi){
	// 			// if($rpSupplier[$dtSupp.$dtKlasifikasi]){
	// 				$noUrut++;
	// 				$whr="supplierid='".$dataH[0]['kodesupplier']."'";
	// 				$optSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whr);
	// 				$dataRes['detail'][] = array(
	// 					'nojurnal'=>$nojurnal,
	// 					'tanggal'=>substr($dataH[0]['datein'],0,10),
	// 					'nourut'=>$noUrut,
	// 					'noakun'=>$dtnoakun[0]['noakunkredit'],
	// 					'keterangan'=>'Penerimaan TBS dari supplier '.$optSupp[$dataH[0]['kodesupplier']].',pada tanggal :    '.tanggalnormal(substr($dataH[0]['datein'],0,10)),
	// 					'jumlah'=>$totPersediaandt*-1,
	// 					'matauang'=>'IDR',
	// 					'kurs'=>'1',
	// 					'kodeorg'=>$dataH[0]['unit'],
	// 					'kodekegiatan'=>'',
	// 					'kodeasset'=>'',
	// 					'kodebarang'=>'',
	// 					'nik'=>'',
	// 					'kodecustomer'=>'',
	// 					'kodesupplier'=>$dataH[0]['kodesupplier'],
	// 					'noreferensi'=>$noreferensi,
	// 					'noaruskas'=>'',
	// 					'kodevhc'=>'',
	// 					'nodok'=>'',
	// 					'kodeblok'=>'',
	// 					'revisi'=>'0',
	// 					'kodesegment' => '0000000001'
	// 					);
	// 			// }
	// 		// }
	// 	// }
	// 				$noUrut++;
	// 				$dataRes['detail'][] = array(
	// 						'nojurnal'=>$nojurnal,
	// 						'tanggal'=>substr($dataH[0]['datein'],0,10),
	// 						'nourut'=>$noUrut,
	// 						'noakun'=>'2120200',
	// 						'keterangan'=>'Hutang Pajak 22 dari pengakuan penerimaan TBS  pada tanggal '.tanggalnormal(substr($dataH[0]['datein'],0,10)),
	// 						'jumlah'=>$pph*(-1),
	// 						'matauang'=>'IDR',
	// 						'kurs'=>'1',
	// 						'kodeorg'=>$dataH[0]['unit'],
	// 						'kodekegiatan'=>'',
	// 						'kodeasset'=>'',
	// 						'kodebarang'=>'',
	// 						'nik'=>'',
	// 						'kodecustomer'=>'',
	// 						'kodesupplier'=>$dataH[0]['kodesupplier'],
	// 						'noreferensi'=>$noreferensi,
	// 						'noaruskas'=>'',
	// 						'kodevhc'=>'',
	// 						'nodok'=>'',
	// 						'kodeblok'=>'',
	// 						'revisi'=>'0',
	// 						'kodesegment' => '0000000001'
	// 						);
		
		
	// 	#=== Insert Data ===
	// 	$errorDB = "";
	// 	# Header
	// 	$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
	// 	try{$owlPDO->exec($queryH); }catch (PDOException $e) {$errorDB .= "Header :". $e->getMessage() ; }
	// 	# Detail
	// 	if($errorDB=='') {
	// 	    foreach($dataRes['detail'] as $key=>$dataDet) {
	// 	        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
	// 	        try{$owlPDO->exec($queryD); }catch (PDOException $e) {$errorDB .= "Detail: ".$key." ". $e->getMessage() ; }
	// 		}
	// 	}
		
	// 	//============================================================
		
	// 	#Update saldo tbs ramp
	// 	$totalkg = 0;
	// 	$totalrp = 0;
	// 	$reshargarata = 0;
	// 	$countdata = 0;
	// 	$tmpkg = 0;
	// 	$tmpharga = 0;
	// 	$str = "select beratmasuk,beratkeluar,potongan, harga from ".$dbname.".pmn_penerimaantbsramp where kodeorg='".$pt."' and unit='".$kodepabrik."' and koderamp='".$koderamp."' and datein like '".(substr($tanggal,0,4))."-".substr($tanggal,4,2)."-".substr($tanggal,6,2)."%' and posted='1'";
	// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// 	$res->setFetchMode(PDO::FETCH_ASSOC);
	// 	while($bar=$res->fetch())
	// 	{
	// 		$rtmppotongan = round($bar['potongan']);
	// 		$tmpkg = $bar['beratmasuk'] - $bar['beratmasuk'] - $rtmppotongan;
	// 		$tmpharga = $bar['hargatotal'];
	// 	}
		
	// 	$totalkg = $tmpkg + $rnetto;
	// 	$totalrp = $tmpharga + ($rnetto*$dataH[0]['harga']);
	// 	$reshargarata = $totalrp/$totalkg;
		
	// 	if($tmpkg==0)
	// 	{
	// 		$str = "insert into ".$dbname.".keu_5saldotbsramp (kodeorg,unit,koderamp,tanggal,fisik,hargarata,updateby) values ('".$pt."','".$kodepabrik."','".$koderamp."','".$tanggal."','".$totalkg."','".$reshargarata."','".$_SESSION['standard']['userid']."')";
	// 	}
	// 	else
	// 	{
	// 		$str = "update ".$dbname.".keu_5saldotbsramp set fisik='".$totalkg."',hargarata='".$reshargarata."' where kodeorg='".$pt."' and unit='".$kodepabrik."' and koderamp='".$koderamp."' and tanggal='".$tanggal."'";
	// 	}
		
	// 	try{
	// 		$owlPDO->exec($str); 
	// 	}catch(PDOException $e){
	// 		echo " Gagal," . addslashes($e->getMessage());
	// 	}
		
	// 	#Update flag posting
	// 	$str = "update ".$dbname.".pmn_penerimaantbsramp set posted='1', postedby='".$_SESSION['standard']['userid']."', posteddate='".tanggalsystem(date('d-m-Y'))."' where notiket='".$notiket."'";
	// 	try
	// 	{
	// 		$owlPDO->exec($str); 
	// 		$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpC+1),$whereNoindukph);
	// 		$errCounter = "";
	// 		try
	// 		{
	// 			$owlPDO->exec($queryJ); 
	// 		}
	// 		catch (PDOException $e) 
	// 		{ 
	// 			$errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; 
	// 		}
			
	// 		if($errCounter!="") 
	// 		{
	// 			$queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']),$whereNoindukph);
	// 			$errCounter = "";
	// 			try
	// 			{
	// 				$owlPDO->exec($queryJRB); 
	// 			}
	// 			catch (PDOException $e) 
	// 			{ 
	// 				$errorJRB .= "Rollback Parameter Jurnal Error :". $e->getMessage() ; 
	// 			}
	// 			echo "DB Error :\n".$errorJRB;
	// 			exit;
	// 		}
	// 	}catch(PDOException $e){
	// 		echo " Gagal," . addslashes($e->getMessage());
	// 	}
	// break;
	
    case'loadData':
		
		$sCek="select * from ".$dbname.".setup_posting where kodeaplikasi='ramp' and jabatan='".$_SESSION['empl']['jabatan']."'";
        //echo "$sCek";
        $optPosting=fetchData($sCek);
		//Inisialisasi Search
		$where = "";
		if($caript!='')
		{
            $where.=" and kodeorg = '".$caript."'";
        }
		if($cariunit!='')
		{
            $where.=" and unit = '".$cariunit."'";
        }
		if($carikoderamp!='')
		{
            $where.=" and koderamp = '".$carikoderamp."'";
        }
		if($carisupplier!='')
		{
            $where.=" and kodesupplier = '".$carisupplier."'";
        }
		if($caritanggal!='')
		{
			$caritanggal = substr($caritanggal,6,4)."-".substr($caritanggal,3,2)."-".substr($caritanggal,0,2);
			$where.=" and datein like '".$caritanggal."%'";
        }
	
		$limit=20;
        $page=0;
        if(isset($pages))
		{
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
        
		$str="select count(*) jmlhrow from ".$dbname.".pmn_penerimaantbsramp where 1=1 ".$where."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$jlhbrs= $bar['jmlhrow'];	
		}
		
		$tab='';
		$nor=0;
		
		$str="select * from ".$dbname.".pmn_penerimaantbsramp where 1=1 ".$where." order by datein desc limit ".$offset.",".$limit." ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$optpabrik = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['unit']."'");
			$optramp = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='RAMP')");
			$optdo = makeOption($dbname,'log_5klsupplier','kode,nodo',"kode='".$bar['koderamp']."'");
			$optsupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['kodesupplier']."'");
			$optkaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			$optposted = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['postedby']."'");
			
			$nor+=1;
			$tab.="<tr class=rowcontent>
				<td id='nor_".$nor."' align=right value='".$nor."'>".$nor."</td>
				<td>".$bar['notiket']."</td>
				<td>".$optpt[$bar['kodeorg']]."</td>
				<td>".$optpabrik[$bar['unit']]."</td>
				<td>".$bar['koderamp']."-".$optdo[$bar['koderamp']]."</td>
				<td>".$bar['koderamp']."-".$optramp[$bar['koderamp']]."</td>
				<td>".$bar['kodesupplier']."-".$optsupplier[$bar['kodesupplier']]."</td>
				<td style='text-align:center'>".tanggalnormal($bar['datein'])."</td>
				<td style='text-align:right'>".number_format($bar['netto'])."</td>
				<td style='text-align:right'>".number_format($bar['harga'])."</td>
				<td style='text-align:center'>".$optkaryawan[$bar['updateby']]."</td>
				<td style='text-align:center'>".($bar['posteddate']=='0000-00-00'?'-':tanggalnormal($bar['posteddate']))."</td>
				<td style='text-align:center'>".$optposted[$bar['postedby']]."</td>";
			if($bar['posted']==0)
			{
				$tglmasuk = tanggalnormal($bar['datein']);
				$jammasuk = substr($bar['datein'],11,2);
				$menitmasuk = substr($bar['datein'],14,2);
				$tglkeluar = tanggalnormal($bar['dateout']);
				$jamkeluar = substr($bar['dateout'],11,2);
				$menitkeluar = substr($bar['dateout'],14,2);
				$tab.="<td style='text-align:center'>
					<img src=images/application/application_edit.png class=zImgBtn title='Edit' onclick=\"edit('".str_replace('X','',$bar['notiket'])."','".$bar['kodeorg']."','".$bar['unit']."','".$bar['koderamp']."','".$bar['kodesupplier']."','".$bar['nokendaraan']."','".$tglmasuk."','".$jammasuk."','".$menitmasuk."','".$tglkeluar."','".$jamkeluar."','".$menitkeluar."','".number_format($bar['beratmasuk'])."','".number_format($bar['beratkeluar'])."','".number_format($bar['potongan'])."','".number_format($bar['harga'])."','".$bar['beban_pajak']."','".$bar['persenpajak']."');\">
				</td>";            
                $tab.="<td style='text-align:center'>
					<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$bar['notiket']."');\" >
				</td>";
				if(count($optPosting)==0)
				{
					$tab.="<td align=center>
						<img src='images/".$_SESSION['theme']."/posting.png' class=zImgBtn title='Posting RAMP' style='cursor:default'>
					</td>";
				}
				else
				{
					// $tab.="<td align=center>
					// 	<img src='images/".$_SESSION['theme']."/posting.png' class=zImgBtn  title='Posting RAMP'   onclick=\"posting('".$bar['notiket']."','".$bar['kodeorg']."','".$bar['unit']."','".$bar['koderamp']."','".$bar['kodesupplier']."','".tanggalnormal($bar['datein'])."')\">
					// </td>";
					$tab.="<td align=center>
					 	<img src='images/".$_SESSION['theme']."/posting.png' class=zImgBtn  title='Posting RAMP'>
					 </td>";
					
				}
			}
			else
			{
				$tab.="<td align=center colspan=3>
					<img src='images/".$_SESSION['theme']."/posted.png' class=zImgBtn  title='Posted' style='cursor:default'>
				</td>";
			}
		}
		
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0)
		{
			$totrows=1;
		}
		
		$isiRow='';
		for($er=1;$er<=$totrows;$er++)
		{
			$sel = ($page==$er-1)? 'selected': '';
			$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}
		
		$footd.="</tr>
            <tr><td colspan=20 align=center>";
		
		if($page=='0')
		{
			$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['pref']."</button>";
		}
		else
		{
			$footd.="<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
		}
		
		$footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";
		
		if(($page+1) == $totrows)
		{
			$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['lanjut']."</button>";
		}
		else
		{
			$footd.="<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
		}
        $footd.="</td></tr>";
		echo $tab."####".$footd;
	break;
	
    case'getData':
		//Mencari noasset yang sedang dalam proses Mutasi.
		$sCountPenempatan = "select count(noasset) as count from ".$dbname.".asset_penempatan where noasset = '".$param['noasset']."' and status != '1'";
		$qCountPenempatan = mysql_query($sCountPenempatan) or die(mysql_error($conn));
		$rCountPenempatan = mysql_fetch_assoc($qCountPenempatan);
		
		//Mencari noasset yang sedang dalam proses Mutasi.
		$sCountMutasi = "select count(noasset_dari) as count FROM ".$dbname.".asset_mutasi where noasset_dari = '".$param['noasset']."' AND status_mutasi != '1'";
		$qCountMutasi = mysql_query($sCountMutasi) or die(mysql_error($conn));
		$rCountMutasi = mysql_fetch_assoc($qCountMutasi);
		
		//Mencari noasset yang sedang dalam proses Write Off.
		$sCountWriteOff = "select count(noasset) as count from ".$dbname.".asset_writeoff where noasset = '".$param['noasset']."' and status_app in ('0','2')";
		$qCountWriteOff = mysql_query($sCountWriteOff) or die(mysql_error($conn));
		$rCountWriteOff = mysql_fetch_assoc($qCountWriteOff);
				
		if($rCountPenempatan['count'] > 0){
			exit('Gagal, Asset masih dalam proses penempatan.');
		}
		
		if($rCountMutasi['count'] > 0){
			exit('Gagal, Asset masih dalam proses mutasi.');
		}
		
		if($rCountWriteOff['count'] > 0){
			exit('Gagal, Asset masih dalam proses write off');
		}
	
		$sdata="select distinct * from ".$dbname.".asset_mgt 
				where noaset='".$param['noasset']."'";
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		$rdata=mysql_fetch_assoc($qdata);
		$optNmbrg="";
		if($rdata['kodebarang']!=''){
			$whrBrg="kodebarang='".$rdata['kodebarang']."'";
			$optNmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whrBrg);
		}
		$optNmkry="";
		if($rdata['karyawanid']!=''){
			$whrKry="karyawanid='".$rdata['karyawanid']."'";
			$optNmkry=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKry);
		}
		
		$noaset = explode(".", $rdata['noaset']);
		if($noaset[1]=='V'){
			$skga = "SELECT namagroup FROM ".$dbname.".asset_5group WHERE group_id = '".$noaset[1]."' AND group_kode = '".$noaset[3]."'";
			$qkga = mysql_query($skga) or die(mysql_error($conn));
			$rkga = mysql_fetch_array($qkga);
			$vkodegrouptasset = $rkga['namagroup'];
		}else{
			$skga = "SELECT namagroup FROM ".$dbname.".asset_5group WHERE group_id = '".$noaset[1]."' AND group_kode = '".$noaset[2]."'";
			$qkga = mysql_query($skga) or die(mysql_error($conn));
			$rkga = mysql_fetch_array($qkga);
			$vkodegrouptasset = $rkga['namagroup'];
		}
		
		echo $rdata['noaset']."###".$rdata['noaset_lama']."###".$rdata['nopo']."###".$rdata['kodebarang']."###".$rdata['namabarang']."###".$rdata['model']."###".$rdata['spesifikasi']."###".$rdata['serialnumber'];
		echo "###".tanggalnormal($rdata['tgl_daftar'])."###".$rdata['kodept']."###".$rdata['status']."###".$rdata['keterangan']."###".$rdata['lokasi']."###".$rdata['subbagian']."###".$rdata['karyawanid']."###".$optNmkry[$rdata['karyawanid']]."###".$rdata['nama_aset']."###".tanggalnormal($rdata['tgl_pembelian'])."###".$rdata['namatempat']."###".$vkodegrouptasset."###".number_format($rdata['hargaasset'],2);
		break;
	 
    case'getFormBrg':
        $form="<fieldset style=float: left;>
               <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['kodebarang']."</legend>
			   <table>
			   <tr><td>".$_SESSION['lang']['kodebarang']."/".$_SESSION['lang']['namabarang']."</td><td><input type=text class=myinputtext id=nosipbcr onkeypress='return tanpa_kutip(event)' style='width:100px' /></td></tr>
			   <tr><td colspan=2><button class=mybutton onclick=findBrg()>".$_SESSION['lang']['find']."</button></td></tr></table></fieldset>
               <fieldset><legend>".$_SESSION['lang']['result']."</legend><div id=container2 style=overflow:auto;width:100%;height:430px;></fieldset></div>";
        echo $form;
		break;
		
	case'getFormPo':
		 $form="<fieldset style=float: left;height:450>
               <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['nopo']."</legend>
			   <table>
			   <tr><td>".$_SESSION['lang']['nopp']."/".$_SESSION['lang']['nopo']."</td><td><input type=text class=myinputtext id=nosipbcr onkeypress='return tanpa_kutip(event)' style='width:100px' /></td></tr>
			   <tr><td colspan=2><button class=mybutton onclick=findPo()>".$_SESSION['lang']['find']."</button></td></tr></table></fieldset>
               <fieldset><legend>".$_SESSION['lang']['result']."</legend><div id=container2 style=overflow:auto;width:520px;height:420px;></fieldset></div>";
        echo $form;
	break;
	case'getFormUser':
		 $form="<fieldset style=float: left;>
               <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['user']."</legend>
			   <table>
			   <tr><td>".$_SESSION['lang']['nik']."/".$_SESSION['lang']['namakaryawan']."</td><td><input type=text class=myinputtext id=nosipbcr onkeypress='return tanpa_kutip(event)' style='width:100px' /></td></tr>
			   <tr><td colspan=2><button class=mybutton onclick=findUser()>".$_SESSION['lang']['find']."</button></td></tr></table></fieldset>
               <fieldset><legend>".$_SESSION['lang']['result']."</legend><div id=container2 style=overflow:auto;width:100%;height:430px;></fieldset></div>";
        echo $form;
	break;
    case'getBrg':
        //txtfind
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead>";
		$tab.="<tr><td>No.</td><td>".$_SESSION['lang']['kodebarang']."</td>";
		$tab.="<td>".$_SESSION['lang']['namabarang']."</td>";
		$tab.="</tr></thead><tbody>";
		$whr = '';
		if($param['txtfind']!=''){
			$whr.=" and kodebarang like '%".$param['txtfind']."%' or namabarang like '%".$param['txtfind']."%'";
		}
		$sdata="select kodebarang,namabarang from ".$dbname.".log_5masterbarang  where kodebarang!='' ".$whr."";
		 
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		$no = 0;
		while($rdata=  mysql_fetch_assoc($qdata)){
			if(substr($rdata['kodebarang'],0,1)!='9'){
				continue;
			}
			$no+=1;
			$brt="onclick=\"setData(".$rdata['kodebarang'].",'".$rdata['namabarang']."')\"";
            $tab.="<tr ".$brt." class=rowcontent><td>".$no."</td>";
            $tab.="<td>".$rdata['kodebarang']."</td>";
            $tab.="<td>".$rdata['namabarang']."</td></tr>";
		}
		$tab.="</tbody></table>";
		echo $tab;
	break;
	case'getPo':
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
		$tab.="<thead>";
		$tab.="<tr><td>No.</td>";
		$tab.="<td>".$_SESSION['lang']['nopp']."</td>";
		$tab.="<td>".$_SESSION['lang']['nopo']."</td>";
		$tab.="<td>".$_SESSION['lang']['kodebarang']."</td>";
		$tab.="<td>".$_SESSION['lang']['namabarang']."</td>";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$tab.="<td>".$_SESSION['lang']['hargaperolehan']."</td>";
		}
		$tab.="</tr></thead><tbody>";
		$whr = '';
		if($param['txtfind']!=''){
			$whr.=" and (nopo like '%".$param['txtfind']."%' or nopp like '%".$param['txtfind']."%')";
		}
		$sdata="select nopp,nopo,kodebarang,namabarang,hargasatuan,kurs from ".$dbname.".log_po_vw  where kodept='".$param['ptId']."' and kodebarang='".$param['kdBrg']."' ".$whr."";
		// echo $data;
		 
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		$no = 0;
		while($rdata=  mysql_fetch_assoc($qdata)){
			if(substr($rdata['kodebarang'],0,1)!='9'){
				continue;
			}
			$no+=1;
			$brt="onclick=\"setDataPo('".$rdata['nopo']."','".number_format($rdata['hargasatuan']*$rdata['kurs'],2)."')\"";
            $tab.="<tr ".$brt." class=rowcontent><td>".$no."</td>";
            $tab.="<td>".$rdata['nopo']."</td>";
            $tab.="<td>".$rdata['nopp']."</td>";
            $tab.="<td>".$rdata['kodebarang']."</td>";
            $tab.="<td>".$rdata['namabarang']."</td>";
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$tab.="<td style='text-align:right'>".number_format($rdata['hargasatuan']*$rdata['kurs'],2)."</td>";
			}
            $tab.="</tr>";
		}
		$tab.="</tbody></table>";
		echo $tab;
	break;
	case'getUser':
        //txtfind
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead>";
		$tab.="<tr><td>No.</td><td>".$_SESSION['lang']['nik']."</td>";
		$tab.="<td>".$_SESSION['lang']['namakaryawan']."</td>";
		$tab.="</tr></thead><tbody>";
		$whr = '';
		if($param['txtfind']!=''){
			$whr.=" AND lokasitugas = '".$param['unitKerja']."' and (nik like '%".$param['txtfind']."%' or namakaryawan like '%".$param['txtfind']."%')";
		}
		$sdata="select nik,karyawanid,namakaryawan from ".$dbname.".datakaryawan  where karyawanid!='' ".$whr."";
		$no = 0;
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		while($rdata=  mysql_fetch_assoc($qdata)){
			$no+=1;
			$brt="onclick=\"setNikData('".$rdata['karyawanid']."','".$rdata['namakaryawan']."')\"";
            $tab.="<tr ".$brt." class=rowcontent><td>".$no."</td>";
            $tab.="<td>".$rdata['nik']."</td>";
            $tab.="<td>".$rdata['namakaryawan']."</td></tr>";
		}
		$tab.="</tbody></table>";
		echo $tab;
	break; 
	
    
	case'getSubId':
		$optDt=$optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sData="select groupsub_id from ".$dbname.".asset_5groupid where group_id='".$param['groupId']."' and groupsub_id!='' order by abs(groupsub_id) asc";
		$qData=mysql_query($sData) or die(mysql_error($conn));
		while($rData=mysql_fetch_assoc($qData)){
			if($param['groupsubId']==$rData['groupsub_id']){
				$optkegiatan.="<option value='".$rData['groupsub_id']."' selected>".$rData['groupsub_id']."</option>";
			}else{
				$optkegiatan.="<option value='".$rData['groupsub_id']."'>".$rData['groupsub_id']."</option>";	
			}
			
		}
		$whdt="group_id='".$param['groupId']."'";
		if($param['groupId']=='V'){
			$whdt="groupsub_id='".$param['groupsubId']."'";
		}
		$sData="select group_kode,namagroup from ".$dbname.".asset_5group where ".$whdt." order by abs(group_kode) asc";
		$qData=mysql_query($sData) or die(mysql_error($conn));
		while($rData=mysql_fetch_assoc($qData)){
			if($param['groupkdastId']==$rData['group_kode']){
				$optDt.="<option value='".$rData['group_kode']."' selected>".$rData['group_kode']."-".$rData['namagroup']."</option>";
			}else{
				$optDt.="<option value='".$rData['group_kode']."'>".$rData['group_kode']."-".$rData['namagroup']."</option>";
			}
		}
		echo $optkegiatan."####".$optDt;
	break;
	case'getLokasi':
		$optkegiatan='';
		if($param['ptId']!=''){
			$optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			if($param['ptId']=='GDA'){
				if($param['unitKerja']=='GDAS'){
					$optkegiatan.="<option value='GDAS' selected>GDAS-Gudang Asset</option>";
				}else{
					$optkegiatan.="<option value='GDAS'>GDAS-Gudang Asset</option>";
				}
			}else{
				$sData="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['ptId']."' order by kodeorganisasi asc";
				$qData=mysql_query($sData) or die(mysql_error($conn));
				while($rData=mysql_fetch_assoc($qData)){
					if($param['unitKerja']==$rData['kodeorganisasi']){
						$optkegiatan.="<option value='".$rData['kodeorganisasi']."' selected>".$rData['kodeorganisasi']."-".$rData['namaorganisasi']."</option>";
					}else{
						$optkegiatan.="<option value='".$rData['kodeorganisasi']."'>".$rData['kodeorganisasi']."-".$rData['namaorganisasi']."</option>";	
					}
					
				}
			}
		}else{
			$optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}
		echo $optkegiatan;
	break;
	
	case'getSubagian':
		$optkegiatan='';
		if($param['unitKerja']!=''){
			$optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			if($param['unitKerja']=='GDAS'){
				if($param['sbbagian']=='GDASHO'){
					$optkegiatan.="<option value='GDASHO' selected>GDASHO-Gudang Asset HO</option>";
				}else{
					$optkegiatan.="<option value='GDASHO'>GDASHO-Gudang Asset HO</option>";
				}
			}else{
				$optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$sData="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['unitKerja']."' order by kodeorganisasi asc";
				$qData=mysql_query($sData) or die(mysql_error($conn));
				while($rData=mysql_fetch_assoc($qData)){
					if($param['sbbagian']==$rData['kodeorganisasi']){
						$optkegiatan.="<option value='".$rData['kodeorganisasi']."' selected>".$rData['kodeorganisasi']."-".$rData['namaorganisasi']."</option>";
					}else{
						$optkegiatan.="<option value='".$rData['kodeorganisasi']."'>".$rData['kodeorganisasi']."-".$rData['namaorganisasi']."</option>";	
					}
					
				}
			}
		}else{
			$optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}
		echo $optkegiatan;
	break;
	
	case'getUnitKerja':
		$optUnitkerja = '';
		if($param['cariperusahaan'] != ''){
			$optUnitkerja="<option value=''>".$_SESSION['lang']['all']."</option>";
			if($param['cariperusahaan']=='GDA'){
				$optUnitkerja.="<option value='GDAS'>GDAS-Gudang Asset</option>";
			}else{
				$sData="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['cariperusahaan']."' order by kodeorganisasi asc";
				$qData=mysql_query($sData) or die(mysql_error($conn));
				while($rData=mysql_fetch_assoc($qData)){
					$optUnitkerja.="<option value='".$rData['kodeorganisasi']."'>".$rData['kodeorganisasi']."-".$rData['namaorganisasi']."</option>";
				}
			}
		}else{
			$optUnitkerja="<option value=''>".$_SESSION['lang']['all']."</option>";
		}
		echo $optUnitkerja;
	break;
	
	case 'getSubbagian':
		$optSubbagian = '';
		if($param['cariunitkerja'] != ''){
			$optSubbagian="<option value=''>".$_SESSION['lang']['all']."</option>";
			if($param['cariunitkerja'] == 'GDAS'){
				$optSubbagian.="<option value='GDASHO'>GDASHO-Gudang Asset HO</option>";
			}else{
				$sData="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['cariunitkerja']."' order by kodeorganisasi asc";
				$qData=mysql_query($sData) or die(mysql_error($conn));
				while($rData=mysql_fetch_assoc($qData)){
					$optSubbagian.="<option value='".$rData['kodeorganisasi']."'>".$rData['kodeorganisasi']."-".$rData['namaorganisasi']."</option>";
				}
			}
		}else{
			$optSubbagian="<option value=''>".$_SESSION['lang']['all']."</option>";
		}
		echo $optSubbagian;
	break;
	
	case 'formDuplicateNumber':
		//Mencari noasset yang sedang dalam proses Mutasi.
		$sCountPenempatan = "select count(noasset) as count from ".$dbname.".asset_penempatan where noasset = '".$param['noasset']."' and status != '1'";
		$qCountPenempatan = mysql_query($sCountPenempatan) or die(mysql_error($conn));
		$rCountPenempatan = mysql_fetch_assoc($qCountPenempatan);
		
		//Mencari noasset yang sedang dalam proses Mutasi.
		$sCountMutasi = "select count(noasset_dari) as count FROM ".$dbname.".asset_mutasi where noasset_dari = '".$param['noasset']."' AND status_mutasi != '1'";
		$qCountMutasi = mysql_query($sCountMutasi) or die(mysql_error($conn));
		$rCountMutasi = mysql_fetch_assoc($qCountMutasi);
		
		//Mencari noasset yang sedang dalam proses Write Off.
		$sCountWriteOff = "select count(noasset) as count from ".$dbname.".asset_writeoff where noasset = '".$param['noasset']."' and status_app in ('0','2')";
		$qCountWriteOff = mysql_query($sCountWriteOff) or die(mysql_error($conn));
		$rCountWriteOff = mysql_fetch_assoc($qCountWriteOff);
				
		if($rCountPenempatan['count'] > 0){
			echo 'Gagal, Asset masih dalam proses penempatan.';
			exit(0);
		}
		
		if($rCountMutasi['count'] > 0){
			echo 'Gagal, Asset masih dalam proses mutasi.';
			exit(0);
		}
		
		if($rCountWriteOff['count'] > 0){
			echo 'Gagal, Asset masih dalam proses write off';
			exit(0);
		}
	
		$str = "SELECT kodept FROM ".$dbname.".asset_mgt where noaset='".$param['noasset']."'";
		$qry = mysql_query($str);
		$res = mysql_fetch_assoc($qry);
		$tab .="<link rel=stylesheet type=text/css href=style/generic.css>";
		$tab .= "<fieldset><legend>Form</legend>";
		$tab .= "<table>
			<tr>
				<td>No. Asset</td>
				<td>:</td>
				<td>".$param['noasset']."</td>
			</tr>
			<tr>
				<td>Jumlah Duplikat</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtextnumber id=noduplicate style=width:50px; onkeypress='return angka_doang(event)' value=0 />
				</td>
			</tr>
			<tr>
				<td colspan='2'></td>
				<td>
					<button class=mybutton id='saveDup' onclick=saveDuplicate('".$param['noasset']."','".$res['kodept']."')>".$_SESSION['lang']['save']."</button>
				</td>
			</tr>
		</table></fieldset>";
		
		$tab .= "<div id='listdataduplicate' style='display:none;'></div>";
		
		
		
		echo $tab;
	break;
	
	case'saveDuplicate':
		$tab.="<fieldset><legend>List Data</legend>";
		$tab.="<div style='overflow: scroll; width:770px; height:280px'>";
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead>";
		$tab.="<tr><td>No.</td>";
		$tab.="<td>".$_SESSION['lang']['noasset']."</td>";
		$tab.="<td>".$_SESSION['lang']['namaasset']."</td>";
		$tab.="<td>".$_SESSION['lang']['noassetlm']."</td>";
		$tab.="<td>".$_SESSION['lang']['snnumber']."</td>";
		$tab.="<td colspan=2>".$_SESSION['lang']['user']."/".$_SESSION['lang']['lokasi']."</td>";
		$tab.="<td>".$_SESSION['lang']['spesifikasi']."</td>";
		$tab.="<td colspan=2>".$_SESSION['lang']['action']."</td>";
		$tab.="</tr></thead><tbody>";
		
		$optNm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$str = "SELECT * FROM ".$dbname.".asset_mgt WHERE noaset='".$param['noasset']."'";
		$qry = mysql_query($str);
		$res = mysql_fetch_assoc($qry);
		for($i=1;$i<=$param['noduplicate'];$i++){
			$noAsset = generateNoAsset();
			$nourutdt=explode(".",$noAsset);
			$nourut=intval($nourutdt[3]);
			if($param['groupId'] == 'V'){
				$nourut=intval($nourutdt[4]);
			}
			
			if($res['namatempat']!=''){
				$vnmKaryDup = $res['namatempat'];
				$vkaryIdDup = '';
			}else{
				$vnmKaryDup = $optNm[$res['karyawanid']];
				$vkaryIdDup = $res['karyawanid'];
			}
			
			//BEGIN SAVE 
			$sinser="insert into ".$dbname.".asset_mgt 			(noaset,nama_aset,noaset_lama,nopo,hargaasset,kodebarang,namabarang,model,spesifikasi,serialnumber,tgl_pembelian,tgl_daftar,kodept,lokasi,subbagian,karyawanid,namatempat,status,keterangan,nourut,updateby) values 
			('".$noAsset."','".$res['nama_aset']."','".$res['noaset_lama']."','".$res['nopo']."','".$res['hargaasset']."','".$res['kodebarang']."','".$res['namabarang']."','".$res['model']."','".$res['spesifikasi']."','".$res['serialnumber']."','".$res['tgl_pembelian']."','".$res['tgl_daftar']."','".$res['kodept']."','".$res['lokasi']."','".$res['subbagian']."','".$res['karyawanid']."','".$res['namatempat']."','".$res['status']."','".$res['keterangan']."','".$nourut."','".$_SESSION['standard']['userid']."')";

			if(mysql_query($sinser)){
				#prepare no urut di his
				$imax = "select nourut from ".$dbname.".asset_mgthis "
						. " where noaset='".$noAsset."' order by nourut desc limit 1 ";
				$nmax = mysql_query($imax) or die (mysql_error($conn));
				$dmax = mysql_fetch_assoc($nmax);
				$nourutmax = $dmax['nourut']+1;
				
				$isave="INSERT INTO ".$dbname.".asset_mgthis (noaset, lokasi_terakhir, kodept_terakhir, subbagian_terakhir, karyawanid_terakhir, namatempat_terakhir, status_his, status_terakhir, tgl_update, nourut, keterangan_his, nodokumen, updateby)  
				values (
					'".$noAsset."', 
					'".$res['lokasi']."', 
					'".$res['kodept']."', 
					'".$res['subbagian']."', 
					'".$res['karyawanid']."', 
					'".$res['namatempat']."', 
					'0', 
					'".$res['status']."', 
					'".$res['tgl_daftar']."', 
					'".$nourutmax."', 
					'".$res['keterangan']."', 
					'', 
					'".$_SESSION['standard']['userid']."')";
				if(mysql_query($isave)){				
				}else{
					exit("error: code 1125\n ".  mysql_error($conn)."___".$isave);
				}
			}else{
				exit("error: code 1125\n ".  mysql_error($conn)."___".$sinser);
			}
			//END SAVE
			
			$tab.="<tr ".$i." class=rowcontent>
			<td style='text-align:right'>".$i."</td>
			<td>".$noAsset."</td>
			<td><input type=text id=namaassetDup_".$i." class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)' value='".$res['nama_aset']."' /></td>
			<td><input type=text id=noassetlamaDup_".$i." class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)' value='".$res['noaset_lama']."' /></td>
			<td><input type=text id=snnumberDup_".$i." class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)' value='".$res['serialnumber']."' /></td>
			<td><input type=text id=nmKaryDup_".$i." class=myinputtext style=width:150px; value='".$vnmKaryDup."' onchange='clearKaryawanIdDup('".$i."')' /></td>
			<td>
				<img src=images/zoom.png title=Cari id=zoom_".$i." class=resicon onclick=\"searchUserDup('".$_SESSION['lang']['find']." ".$_SESSION['lang']['user']."','<div id=formPencariandataDup></div>',event,'".$res['lokasi']."','".$i."')\" />
				<input type=hidden id=karyIdDup_".$i." value='".$vkaryIdDup."' />
			</td>
			<td><input type=text id=spesifikasiDup_".$i." class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)' value='".$res['spesifikasi']."' /></td>
			<td style='text-align:center'>
				<img src=images/skyblue/save.png id=imgsave_".$i." class=resicon  title='Duplicate ".$rstr['noaset']."' onclick=\"saveDuplicateAsset('".$noAsset."','".$i."');\" >
			</td>
			<td style='text-align:center'>
				<img src=images/skyblue/edit.png id=imgedit_".$i." class=resicon  title='Edit ".$rstr['noaset']."' onclick=\"editDuplicateAsset('".$noAsset."','".$i."');\" >
			</td>";
			$tab.="</tr>";
		}
		$tab.="</tbody></table></div>";
		$tab.="</fieldset>";
		echo $tab;
	break;
	
	case'getFormUserDup':
		 $form="<fieldset style=float: left;>
               <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['user']."</legend>
			   <table>
			   <tr><td>".$_SESSION['lang']['nik']."/".$_SESSION['lang']['namakaryawan']."</td><td><input type=text class=myinputtext id=nosipbcrDup onkeypress='return tanpa_kutip(event)' style='width:100px' /></td></tr>
			   <tr><td colspan=2><button class=mybutton onclick=findUserDup('".$param['lokasi']."','".$param['urut']."')>".$_SESSION['lang']['find']."</button></td></tr></table></fieldset>
               <fieldset><legend>".$_SESSION['lang']['result']."</legend><div id=container2 style=overflow:auto;width:100%;height:430px;></fieldset></div>";
        echo $form;
		break;
		
	case'getUserDup':
        //txtfind
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead>";
		$tab.="<tr><td>No.</td><td>".$_SESSION['lang']['nik']."</td>";
		$tab.="<td>".$_SESSION['lang']['namakaryawan']."</td>";
		$tab.="</tr></thead><tbody>";
		$whr = '';
		if($param['txtfind']!=''){
			$whr.="  and (nik like '%".$param['txtfind']."%' or namakaryawan like '%".$param['txtfind']."%')";
		}
		$sdata="select nik,karyawanid,namakaryawan from ".$dbname.".datakaryawan  where karyawanid!='' AND lokasitugas = '".$param['unitKerja']."' ".$whr."";
		$no = 0;
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		while($rdata=  mysql_fetch_assoc($qdata)){
			$no+=1;
			$brt="onclick=\"setNikDataDup('".$rdata['karyawanid']."','".$rdata['namakaryawan']."','".$param['urut']."')\"";
            $tab.="<tr ".$brt." class=rowcontent><td>".$no."</td>";
            $tab.="<td>".$rdata['nik']."</td>";
            $tab.="<td>".$rdata['namakaryawan']."</td></tr>";
		}
		$tab.="</tbody></table>";
		echo $tab;
	break; 
	
	case'saveDuplicateAsset':
        $sinser="update ".$dbname.".asset_mgt set 
		noaset_lama='".$param['noassetlamaDup']."',spesifikasi='".$param['spesifikasiDup']."',serialnumber='".$param['snnumberDup']."',karyawanid='".$param['karyIdDup']."',namatempat='".$param['nmKaryDup']."',updateby='".$_SESSION['standard']['userid']."', nama_aset = '".$param['namaassetDup']."' 
		where noaset='".$param['noasset']."'";
		if(!mysql_query($sinser)){
			exit("error: code 1125\n ".  mysql_error($conn)."___".$sinser);
		}else{
			$str = "select * from ".$dbname.".asset_mgt where noaset='".$param['noasset']."'";
			$qry = mysql_query($str);
			$res = mysql_fetch_assoc($qry);
			
			#prepare no urut di his
			$imax = "select nourut from ".$dbname.".asset_mgthis "
					. " where noaset='".$param['noasset']."' order by nourut desc limit 1 ";
			$nmax = mysql_query($imax) or die (mysql_error($conn));
			$dmax = mysql_fetch_assoc($nmax);
			$nourut = $dmax['nourut']+1;
			
			$isave="INSERT INTO ".$dbname.".asset_mgthis (noaset, lokasi_terakhir, kodept_terakhir, subbagian_terakhir, karyawanid_terakhir, namatempat_terakhir, status_his, status_terakhir, tgl_update, nourut, keterangan_his, nodokumen, updateby)  
			values (
				'".$param['noasset']."', 
				'".$res['lokasi']."', 
				'".$res['kodept']."', 
				'".$res['subbagian']."', 
				'".$param['karyIdDup']."', 
				'".$param['nmKaryDup']."', 
				'5', 
				'".$res['status']."', 
				'".$res['tgl_daftar']."', 
				'".$nourut."', 
				'".$res['keterangan']."', 
				'', 
				'".$_SESSION['standard']['userid']."')";
			if(mysql_query($isave)){				
			}else{
				exit("error: code 1125\n ".  mysql_error($conn)."___".$isave);
			}
		}
		echo $param['noasset'];
	break; 
}

function generateNoAsset(){
	global $dbname;
	global $conn;
	global $param;
	if($param['tanggalBeli']==''){
		exit('warning: '.$_SESSION['lang']['tanggaldaftar']." tidak boleh kosong");
	}
	#no invoice
	$notrn="";
	$wheredt="";
	if($param['groupId']=='V'){
		if($param['sbbagian']!=''){
			$notrn="%".$param['sbbagian']."%";
		}
		// if($param['unitKerja']!=''){
			// $notrn.=$param['unitKerja']."%";
		// }
			$notrn.=$param['groupId']."%";
		if($param['groupsbId']!=''){
			$notrn.=$param['groupsbId']."%";
		}
		if($param['groupkdastId']!=''){
			$notrn.=$param['groupkdastId']."%";
		}
		$wheredt=" noaset like '".$notrn."'";
	}
	if($param['groupId']!='V'){
		$wheredt=" noaset like '%".$param['groupkdastId']."%'";
		if($param['ptId']!=''){
			$wheredt.=" and kodept='".$param['ptId']."'";
		}
	}
		$tglaset=explode("-",$param['tanggalBeli']);
		$sData="select nourut from ".$dbname.".asset_mgt where ".$wheredt." order by nourut desc";
// <<<<<<< .mine
		// EXIT("Error ".$sData);
// =======

// >>>>>>> .r51
		$qData=mysql_query($sData) or die(mysql_error($conn));
		$rData=mysql_fetch_assoc($qData);
		$nourut=addZero(intval($rData['nourut']+1),4);
		//exit('warning:'.$nourut."___".$sData);
		if($param['groupId']=='V'){
			$noasset=$param['sbbagian'].".".$param['groupId'].".".$param['groupsbId'].".".$param['groupkdastId'].".".$nourut.".".$tglaset[1].".".substr($tglaset[2],2,2);
		}
		if($param['groupId']!='V'){
			$noasset=$param['sbbagian'].".".$param['groupId'].".".$param['groupkdastId'].".".$nourut.".".$tglaset[1].".".substr($tglaset[2],2,2);
		}
	return $noasset;
}