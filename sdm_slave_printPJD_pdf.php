<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/terbilang.php');
$notransaksi=$_GET['notransaksi'];
$jeniskar=$_GET['jeniskar'];
$namakar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
//=============

$namadept=  makeOption($dbname, 'sdm_5departemen', 'kode,nama');
$reg=  makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional');
$golongan=  makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
$namajenis=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
		function tanggal_indo($d,$m,$y){
			switch ($m) {
				case '01':$m='Januari';break;
				case '02':$m='Februari';break;
				case '03':$m='Maret';break;
				case '04':$m='April';break;
				case '05':$m='Mei';break;
				case '06':$m='Juni';break;
				case '07':$m='Juli';break;
				case '08':$m='Agustus';break;
				case '09':$m='September';break;
				case '10':$m='Oktober';break;
				case '11':$m='November';break;
				case '12':$m='Desember';break;
				default:
					break;
			}
			$tglbaru=$d.' '.$m.' '.$y;
			return $tglbaru;
		}	
		function getDataByJenis($array,$str){
			$alldata = array();
			if(count($array)>0){
				for($i=0; $i<count($array); $i++){
					if($array[$i]['jenisbiaya'] == $str){
						$data_dt['jenisbiaya'] 		= $array[$i]['jenisbiaya'];
						$data_dt['detail'] 			= $array[$i]['detail'];
						$data_dt['frekuensi']		= $array[$i]['frekuensi'];
						$data_dt['tanggal'] 		= $array[$i]['tanggal'];
						$data_dt['tanggalsampai'] 	= $array[$i]['tanggalsampai'];
						$data_dt['jumlahhrd']	 	= $array[$i]['jumlahhrd'];
						$data_dt['jumlah'] 			= $array[$i]['jumlah'];
						$data_dt['keterangan'] 		= $array[$i]['keterangan'];
						$alldata[] = $data_dt; 
					}
				}
			}
			$result = $alldata;
			return $result;
		}
		function getFrekuensiBytanggal($array){
			$frekQty	= "-";
			if(count($array)>0){
				$frekQty = 0;
				for($i=0; $i<count($array); $i++){
					$date1 = new DateTime($array[$i]['tanggal']);
					$date2 = new DateTime($array[$i]['tanggalsampai']);
					$diff = $date1->diff($date2);
					$diffdays = $diff->days + 1;

					$frekQty = $frekQty + $diffdays;
				}
			}
			$result = $frekQty;
			return $result;
		}
		function getFrekuensi($array){
			$frekQty	= "-";
			if(count($array)>0){
				$frekQty = 0;
				for($i=0; $i<count($array); $i++){
					$qty = $array[$i]['frekuensi'];
					$frekQty = $frekQty+$qty;
				}
			}
			$result = $frekQty;
			
			return $result;
		}
		function getKeterangan($array){
			$ket	= "";
			if(count($array)>0){
				$ket ='';
				for($i=0; $i<count($array); $i++){
					$ket1 = $array[$i]['keterangan'];
				}
			}
			$result = $ket1;
			
			return $result;
		}
		function getharga($array,$jml){
			$harga = 0;
			if(count($array)>0){
				$harga = 0;
				for($i=0; $i<count($array); $i++){
					$jumlah = $array[$i][$jml];
					$harga 	= $harga + $jumlah;
				}
			}
			$result = $harga;
			return $result;
		}
//create Header
class PDF extends FPDF
{

        function Header()
        {

        }

        function Footer()
        {
        	$this->SetY(-20);
			$this->SetFont('Arial','I',8);
			$this->Cell(10,5,'NOTE : ',0,1,'L');
			$this->Cell(10,5,'1. ',0,0,'L');
			$this->Cell(10,5,'Verifikasi benefit wajib dilakukan oleh HCGA untuk wilayah Kantor Pusat',0,1,'L');
			$this->Cell(10,5,'2. ',0,0,'L');
			$this->Cell(10,5,'Verifikasi benefit wajib dilakukan oleh KTU untuk wilayah Operasional / PIC yang sudah ditunjuk unttuk unit operasional.',0,1,'L');
        }

}
	// print_r($_SESSION['org']);
	$str="select * from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{

	    $jabatan='';
	    $namakaryawan='';
	    $bagian='';	
	    $karyawanid='';
		$nikkaryawan='';
		$kdgolongan='';
		$loktugas='';

	    $strc="select a.namakaryawan,a.karyawanid,a.bagian,b.namajabatan,a.nik,a.kodegolongan,a.lokasitugas,a.norekeningbank,a.namabank
				from ".$dbname.".datakaryawan a left join  ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 
				a.karyawanid=".$bar->karyawanid;
		$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
		$resc->setFetchMode(PDO::FETCH_OBJ);
			while($barc=$resc->fetch())
	          	{
					$kdgolongan=$barc->kodegolongan;
	                $jabatan=$barc->namajabatan;
	                $namakaryawan=$barc->namakaryawan;
	                $bagian=$barc->bagian;
	                $karyawanid=$barc->karyawanid;
					$nikkaryawan=$barc->nik;
					$loktugas=$barc->lokasitugas;
					$namabank=$barc->namabank;
					$norek=$barc->norekeningbank;
	          	}

	    //===============================	  
				
        $kodeorg=$bar->kodeorg;
        $namatamu=$bar->namatamu;
        $persetujuan=$bar->persetujuan;
        $hrd=$bar->hrd; 
        $tujuan3=$bar->tujuan3;
        $tujuan2=$bar->tujuan2;	
        $tujuan1=$bar->tujuan1;
        $tanggalperjalanan=tanggalnormal($bar->tanggalperjalanan);
        $tanggalkembali=tanggalnormal($bar->tanggalkembali);
        $uangmuka=$bar->uangmuka;
        $tugas1=$bar->tugas1;
        $tugas2=$bar->tugas2;
        $tugas3=$bar->tugas3;
        $tujuanlain=$bar->tujuanlain;
        $tugaslain=$bar->tugaslain;
        $pesawat=$bar->pesawat;
        $darat=$bar->darat;
        $laut=$bar->laut;
        $dibayar=$bar->dibayar;
        $mess=$bar->mess;
        $hotel=$bar->hotel;	
        $statushrd=$bar->statushrd;
		
		$kendaraandinas=$bar->kendaraandinas;
		$kendaraanpribadi=$bar->kendaraanpribadi;
		$kendaraanumum=$bar->kendaraanumum;
		$tempatlain=$bar->tempatlain;
		$persetujuan2=$bar->persetujuan2;
		if ($namatamu!='') {
			$namakaryawan=$namatamu;
		}

			if ($pesawat==1){
				$trans='Pesawat';
			} else if ($darat==1){
				$trans='Bus/Kereta Api';
			} else if ($laut==1){
				$trans='Kapal Laut';
			} else if ($kendaraandinas==1){
				$trans='Kendaraan Dinas';
			} else if ($kendaraanpribadi==1){
				$trans='Kendaraan Pribadi';
			} else if ($kendaraanumum==1){
				$trans='Kendaraan Umum';
			}
			
			$tgl=explode("-",$tanggalkembali);
			
		
			$tanggal=$tgl[0];
			$bulan=$tgl[1];
			$tahun=$tgl[2];
			$tglbaru= tanggal_indo($tanggal,$bulan,$tahun);//exp: 12 Januari 2016
				
				
	        if($statushrd==0)
	            $statushrd=$_SESSION['lang']['wait_approval'];
			else if($statushrd==1)
	            $statushrd=$_SESSION['lang']['disetujui'];
			else 
	            $statushrd=$_SESSION['lang']['ditolak'];

	        $statuspersetujuan=$bar->statuspersetujuan;
	        if($statuspersetujuan==0)
	            $perstatus=$_SESSION['lang']['wait_approval'];
			else if($statuspersetujuan==1)
	            $perstatus=$_SESSION['lang']['disetujui'];
			else 
	            $perstatus=$_SESSION['lang']['ditolak'];

    		//ambil bagian,jabatan persetujuan
            $perjabatan='';
            $perbagian='';
            $pernama='';
    		$strf="select a.bagian,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
               		where karyawanid=".$persetujuan;	  
			$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
			$resf->setFetchMode(PDO::FETCH_OBJ);
	        while($barf=$resf->fetch())  {
                $perjabatan=$barf->namajabatan;
                $perbagian=$barf->bagian;
                $pernama=$barf->namakaryawan;
	        }

			//ambil jabatan, hrd
			$sorg="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";	
			$rorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
			$rorg->setFetchMode(PDO::FETCH_OBJ);
			$borg=$rorg->fetch();
			$induk=$borg->induk;

			$whrkary="kodeorganisasi='".$induk."'";
			$optorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrkary);


			//ambil jabatan, hrd
	        $hjabatan='';
	        $hbagian='';
	        $hnama='';
	        $strf="select a.bagian,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join
	               ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
	                   where karyawanid=".$hrd;
			$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
			$resf->setFetchMode(PDO::FETCH_OBJ);
	        while($barf=$resf->fetch())
	        {
	                $hjabatan=$barf->namajabatan;
	                $hbagian=$barf->bagian;
	                $hnama=$barf->namakaryawan;
	        }


  }
  
	//Get Lokasi Tugas
	$strLTgs="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$tujuan1."'";
	$resLTgs=$owlPDO->query($strLTgs) or die(print " Gagal: ".PDOException::getMessage());
	$resLTgs->setFetchMode(PDO::FETCH_OBJ);
	while($barLTgs=$resLTgs->fetch()){
	$LTgs=$barLTgs->namaorganisasi;
	}
  
	// PT Tujuan
	$qTujuan = selectQuery($dbname,'organisasi','induk',"kodeorganisasi='".$tujuan2."'");
	$resTujuan = fetchData($qTujuan);
	@$ptTujuan = $resTujuan[0]['induk'];
	
	// Regional Tujuan
	$qRegional = selectQuery($dbname,'bgt_regional_assignment','regional',"kodeunit='".$tujuan2."'");
	$resRegional = fetchData($qRegional);
	@$reg = $resRegional[0]['regional'];

	if ($jeniskar==1) {
		$jenisapp="PJDINASNS";
	}

	if ($jeniskar==0) {
		$jenisapp="PJDINAS";
	}

	$strap="select level,karyawanid from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisapp."'";	
	$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
	$resap->setFetchMode(PDO::FETCH_ASSOC);
	while($barap=$resap->fetch()){
		$per['persetujuan'.$barap['level']]=$barap['karyawanid'];
	}
  
	//printf($strLTgs);
  
		$pdf=new PDF('P','mm','A4');
        $pdf->SetFont('Arial','B',12);
        $pdf->AddPage();
       // $pdf->SetY(40);
	    $pdf->SetFillColor(255,255,255); 
		 $pdf->SetX(10);
		//$pdf->Cell(175,5,'',0,1,'L');
		$pdf->Ln();
       
		$pdf->SetFont('Arial','BU',14);
		$pdf->Cell(190,5,strtoupper($_SESSION['lang']['spdinas'])." (SPD)",0,1,'C');
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(190,5,$notransaksi,0,1,'C');
        
		$pdf->SetFont('Arial','B',14);
       
        //$pdf->Cell(175,5,'NO : '.$notransaksi,0,1,'C');	

		$height=5;
      	
      	$pdf->Ln(5); 
        $pdf->SetFont('Arial','',9);    
        $pdf->Cell(30, 5,"Nama", 0, 'J');
        $pdf->Cell(5, 5,":", 0,0, 'J');
        $pdf->Cell(65, 5,$namakaryawan, 1,0, 'J');
        $pdf->Cell(10, 5,'', 0,0, 'J');
        $pdf->Cell(20, 5,"No. SPD", 0, 'J');
        $pdf->Cell(5, 5,":", 0,0, 'J');
        $pdf->Cell(55, 5,$notransaksi, 1,1, 'J');
        $pdf->Cell(50, 1,'', 0,1, 'J');
        

        if ($namatamu=='') {
			$pdf->Cell(30, 5,"No. Induk Karyawan", 0, 'J');
	        $pdf->Cell(5, 5,":", 0,0, 'J');
	        $pdf->Cell(65, 5,$nikkaryawan, 1,0, 'J');
	        $pdf->Cell(10, 5,'', 0,0, 'J');
	        $pdf->Cell(20, 5,"Nama Atasan", 0, 'J');
	        $pdf->Cell(5, 5,":", 0,0, 'J');
	        $pdf->Cell(55, 5,$namakar[$persetujuan], 1,1, 'J');
	        $pdf->Cell(50, 1,'', 0,1, 'J');
	        
	        $pdf->Cell(30, 5,"Jabatan", 0, 'J');
	        $pdf->Cell(5, 5,":", 0,0, 'J');
	        $pdf->Cell(65, 5,$jabatan, 1,0, 'J');
	        $pdf->Cell(10, 5,'', 0,0, 'J');
	        $pdf->Cell(20, 5,"Departemen", 0, 'J');
	        $pdf->Cell(5, 5,":", 0,0, 'J');
	        $pdf->Cell(55, 5,$namadept[$bagian], 1,1, 'J');
	        $pdf->Cell(50, 1,'', 0,1, 'J');
	        
	        $pdf->Cell(30, 5,"Golongan", 0, 'J');
	        $pdf->Cell(5, 5,":", 0,0, 'J');
	        $pdf->Cell(65, 5,$golongan[$kdgolongan], 1,0, 'J');
	        $pdf->Cell(10, 5,'', 0,0, 'J');
	        $pdf->Cell(20, 5,"Nama PT", 0, 'J');
	        $pdf->Cell(5, 5,":", 0,0, 'J');
	        $pdf->Cell(55, 5,$optorg[$induk], 1,1, 'J');
	        $pdf->Cell(50, 1,'', 0,1, 'J');
        }else{
        	$pdf->Cell(50, 1,'', 0,1, 'J');
        }

		// $pdf->Ln();	
		// $pdf->SetFont('Arial','',8);
		// $pdf->Cell(50,$height,"Diberikan kepada:",0,1,'L');

		// $pdf->Cell(30,$height,"Nama",0,0,'L');
		// $pdf->Cell(10,$height,":",0,0,'C');
		// $pdf->Cell(50,$height,$namakaryawan,0,1,'L');	
		
		// $pdf->Cell(30,$height,"Jabatan",0,0,'L');
		// $pdf->Cell(10,$height,":",0,0,'C');
		// $pdf->Cell(50,$height,$jabatan,0,1,'L');	
		
		// $pdf->Cell(30,$height,"Dept/Div",0,0,'L');
		// $pdf->Cell(10,$height,":",0,0,'C');
		// $pdf->Cell(50,$height,$namadept[$bagian],0,1,'L');	
		
		
	
		
		// $pdf->Cell(30,$height,"Tempat Tujuan",0,0,'L');
		// $pdf->Cell(10,$height,":",0,0,'C');
		// $pdf->Cell(50,$height,$tujuan2.' '.$tujuan3.' '.$tujuanlain,0,1,'L');
		
		
		// $pdf->Cell(30,$height,"Keperluan",0,0,'L');
		// $pdf->Cell(10,$height,":",0,0,'C');
		// $pdf->Cell(50,$height,$tugas2.' '.$tugas3.' '.$tugaslain,0,1,'L');

		
		
		// $pdf->Cell(30,$height,"Akomodasi",0,0,'L');
		// $pdf->Cell(10,$height,":",0,0,'C');
		
		// $heightkotak='4';
		
		// if($hotel==1){
		// 	$pdf->Cell(5,$heightkotak,'V',1,0,'L');
		// }else{
		// 	$pdf->Cell(5,$heightkotak,'',1,0,'L');
		// }
		// $pdf->Cell(30,$height,'Hotel',0,0,'L');
		
		// if($mess==1){
		// 	$pdf->Cell(5,$heightkotak,'V',1,0,'L');
		// }else{
		// 	$pdf->Cell(5,$heightkotak,'',1,0,'L');
		// }
		// $pdf->Cell(30,$height,'Mess',0,0,'L');
		
		// if($tempatlain==''){
		// 	$pdf->Cell(5,$heightkotak,'',1,0,'L');
		// }else{
		// 	$pdf->Cell(5,$heightkotak,'V',1,0,'L');
		// }
		// $pdf->Cell(12,$height,'Lain-lain',0,0,'L');
		// $pdf->SetFont('Arial','U',8);
		// $pdf->Cell(15,$height,$tempatlain,0,1,'L');
			
			
		// $pdf->SetFont('Arial','',8);	
		// $pdf->Cell(30,$height,"Transportasi",0,0,'L');
		// $pdf->Cell(10,$height,":",0,0,'C');
		
		// if($pesawat==1){
		// 	$pdf->Cell(5,$heightkotak,'V',1,0,'L');
		// }else{
		// 	$pdf->Cell(5,$heightkotak,'',1,0,'L');
		// }
		// $pdf->Cell(30,$height,'Pesawat Udara',0,0,'L');
		
		// if($kendaraandinas==1){
		// 	$pdf->Cell(5,$heightkotak,'V',1,0,'L');
		// }else{
		// 	$pdf->Cell(5,$heightkotak,'',1,0,'L');
		// }
		// $pdf->Cell(30,$height,'Kendaraan Dinas',0,0,'L');
		
		// if($darat==1){
		// 	$pdf->Cell(5,$heightkotak,'V',1,0,'L');
		// }else{
		// 	$pdf->Cell(5,$heightkotak,'',1,0,'L');
		// }
		// $pdf->Cell(30,$height,'Kereta Api',0,1,'L');
		
		
		
		// $pdf->Cell(40,$height,'',0,'L');
		// if($laut==1){
		// 	$pdf->Cell(5,$heightkotak,'V',1,0,'L');
		// }else{
		// 	$pdf->Cell(5,$heightkotak,'',1,0,'L');
		// }
		// $pdf->Cell(30,$height,'Kapal Laut',0,0,'L');
		
		// if($kendaraanpribadi==1){
		// 	$pdf->Cell(5,$heightkotak,'V',1,0,'L');
		// }else{
		// 	$pdf->Cell(5,$heightkotak,'',1,0,'L');
		// }
		// $pdf->Cell(30,$height,'Kendaraan Pribadi',0,0,'L');
		
		// if($kendaraanumum==1){
		// 	$pdf->Cell(5,$heightkotak,'V',1,0,'L');
		// }else{
		// 	$pdf->Cell(5,$heightkotak,'',1,0,'L');
		// }
		// $pdf->Cell(30,$height,'Kendaraan Umum Lain',0,1,'L');
	
		
		
		// $pdf->Cell(30,$height,"Tanggal",0,0,'L');
		// $pdf->Cell(10,$height,":",0,0,'C');
		// $pdf->Cell(40,$height,'Berangkat',0,0,'L');
		// $pdf->Cell(10,$height,'',0,0,'L');
		// $pdf->Cell(40,$height,'Kembali',0,1,'L');
		
		// $pdf->Cell(40,$height,'',0,0,'L');
		// $pdf->Cell(40,$height,$tanggalperjalanan,1,0,'L');
		// $pdf->Cell(10,$height,'s/d',0,0,'L');
		// $pdf->Cell(40,$height,$tanggalkembali,1,1,'L');
		
		
		// $pdf->Cell(30,$height,"Mengambil UMPD Rp",0,0,'L');
		// $pdf->Cell(10,$height,":",0,0,'C');
		// $pdf->Cell(50,$height,'Rp '.@number_format($dibayar),0,1,'L');
		
		// $pdf->Cell(30,$height,"Terbilang",0,0,'L');
		// $pdf->Cell(10,$height,":",0,0,'C');
		// $pdf->Cell(50,$height,terbilang($dibayar),0,1,'L');
		
		// $pdf->Cell(30,$height,"Sisa UMPD segera dikembalikan paling lambat 30 hari setelah kembali / penugasan",0,1,'L');
	
/*
		$pdf->Cell(55,$height,$namakar[$persetujuan],'',0,'C');			
			$pdf->Cell(55,$height,$namakar[$hrd],'',0,'C');			
			$pdf->Cell(55,$height,$namakar[$persetujuan2],'',0,'C');	
*/	
				
	  //  $pdf->Ln();	
			
			// $pdf->Cell(40,$height,'Diajukan Oleh','',0,'C');
			// $pdf->Cell(55,$height,'Disetujui Oleh','',0,'C');
			// $pdf->Cell(55,$height,'Diperiksa Oleh','',0,'C');
			// $pdf->Cell(50,$height,'Disetujui Oleh','',0,'C');
	  //  $pdf->Ln();	
	  //  $pdf->Ln();	
	  //  $pdf->Ln();	
	  //  $pdf->Ln();
			// $pdf->Cell(40,$height,$namakaryawan,'',0,'C');
			// $pdf->Cell(55,$height,$namakar[$persetujuan],'',0,'C');			
			// $pdf->Cell(55,$height,$namakar[$hrd],'',0,'C');			
			// $pdf->Cell(50,$height,$namakar[$persetujuan2],'',1,'C');	
			
			
			// $pdf->Cell(40,$height,$namakaryawan,'',0,'C');
			// $pdf->Cell(55,$height,'Pimpinan','',0,'C');			
			// $pdf->Cell(55,$height,'HRD','',0,'C');
			// $pdf->Cell(50,$height,$namakar[$persetujuan2],'',0,'C');
	   $yakhir=$pdf->GetY();
	  // $pdf->Line(20,$yakhir,200,$yakhir);	
		
			// $pdf->Cell(40,$height,'Karyawan','',0,'C');
			// $pdf->Cell(55,$height,'Pimpinan','',0,'C');			
			// $pdf->Cell(55,$height,'HRD','',0,'C');
			// $pdf->Cell(50,$height,'Div. Head / Direktur','',0,'C');
		// $pdf->Ln();
		$pdf->Ln(10);	
		$pdf->Cell(50,$height,'Tanggal',1,0,'L');
		$pdf->Cell(140,$height,'RENCANA KEGIATAN / TUGAS YANG AKAN DILAKUKAN',1,1,'L');
		/*//diganti tujuanya dengan (#)
		$pdf->Cell(50,$height,$tujuan2,1,0,'L');
		$pdf->Cell(140,$height,$tugas2,1,1,'L');
		if ($tujuan3!=''){
			$pdf->Cell(50,$height,$tujuan3,1,0,'L');
			$pdf->Cell(140,$height,$tugas3,1,1,'L');	
		}
		if ($tujuanlain!=''){
			$pdf->Cell(50,$height,$tujuanlain,1,0,'L');
			$pdf->Cell(140,$height,$tugaslain,1,1,'L');
		}*/ 
		
		//(# Author - Atwal
		$qkgt="select * from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."'";
		$rkgt=$owlPDO->query($qkgt) or die(print " Gagal: ".PDOException::getMessage());
		$rkgt->setFetchMode(PDO::FETCH_OBJ);
		while($r=$rkgt->fetch())
		{
			$pdf->Cell(50,$height,tanggalnormal($r->tanggal),1,0,'L');
			$pdf->Cell(140,$height,$r->keterangan,1,1,'L');		
		}
		// END:
		
		$pdf->Ln();	
		$pdf->SetX(20);
		$pdf->SetFont('Arial','B',7);
		$pdf->Cell(100,$height,'JADWAL DAN RUTE TRANSPORTASI',0,1,'L');
		
		$pdf->SetX(20);
		$pdf->Cell(35,$height,'TANGGAL',1,0,'L');
		$pdf->Cell(10,$height,'JAM',1,0,'L');
		$pdf->Cell(35,$height,'DARI',1,0,'L');
		$pdf->Cell(35,$height,'TUJUAN',1,0,'L');
		$pdf->Cell(55,$height,'TRANSPORTASI',1,1,'L');

		
		/* //Diganti dengan (#1)
		$pdf->SetX(20);
		$pdf->Cell(35,$height,substr($tanggalperjalanan, 0,2)." - ".$tglbaru,1,0,'L');
		// $pdf->Cell(25,$height,'',1,0,'L');
		$pdf->Cell(40,$height,$tujuan1,1,0,'L');
		$pdf->Cell(40,$height,$tujuan2,1,0,'L');
		$pdf->Cell(55,$height,@$trans,1,1,'L');*/
		
		// Author - Atwal
		//(#1
		$date_time = "";
		$qrute="select * from ".$dbname.".sdm_pjdinasdt_rute where notransaksi='".$notransaksi."'";
		$rrute=$owlPDO->query($qrute) or die(print " Gagal: ".PDOException::getMessage());
		$rrute->setFetchMode(PDO::FETCH_OBJ);
		while($r=$rrute->fetch())
		{
			$date_time = explode(" ",$r->waktu);
			$date_rute = $date_time[0];
			$jam_rute = $date_time[1];
			list($y,$m,$d) = explode("-",$date_rute);
			list($H,$i,$s) = explode(":",$jam_rute);
			$time_rute = $date_time[1];
			$pdf->SetX(20);
			$pdf->Cell(35,$height,tanggal_indo($d,$m,$y),1,0,'L');
			$pdf->Cell(10,$height,$H.":".$i,1,0,'L');
			$pdf->Cell(35,$height,$r->dari,1,0,'L');
			$pdf->Cell(35,$height,$r->tujuan,1,0,'L');
			$pdf->Cell(55,$height,$r->transportasi,1,1,'L');	
		}
		// End:
		
		// $pdf->Cell(100,$height,'Diisi oleh Devisi/Job site/Perwakilan di Tempat Tujuan',1,0,'L');
		// $pdf->Cell(90,$height,'',1,1,'L');

		// $pdf->Ln(10);	
		// $pdf->Cell(100,$height,'Diisi oleh Devisi/Job site/Perwakilan di Tempat Tujuan',1,0,'L');
		// $pdf->Cell(90,$height,'',1,1,'L');
		
		// $pdf->Cell(45,$height,'Tanggal Tiba',1,0,'L');
		// $pdf->Cell(45,$height,'',1,0,'L');
		// $pdf->Cell(45,$height,'Tanggal Kembali',1,0,'L');
		// $pdf->Cell(55,$height,'Ttd Perwakilan Setempat',1,1,'L');
		// 	$pdf->Cell(190,$height,'Catatan Lain','TLR',1,'L');
		// 	$pdf->Cell(190,$height*4,'','BLR',1,'L');
		
		
		// ///
		// $pdf->Cell(45,$height,'Tanggal Tiba',1,0,'L');
		// $pdf->Cell(45,$height,'',1,0,'L');
		// $pdf->Cell(45,$height,'Tanggal Kembali',1,0,'L');
		// $pdf->Cell(55,$height,'Ttd Perwakilan Setempat',1,1,'L');
		// 	$pdf->Cell(190,$height,'Catatan Lain','TLR',1,'L');
		// 	$pdf->Cell(190,$height*4,'','BLR',1,'L');
		
		// //
		// $pdf->Cell(45,$height,'Tanggal Tiba',1,0,'L');
		// $pdf->Cell(45,$height,'',1,0,'L');
		// $pdf->Cell(45,$height,'Tanggal Kembali',1,0,'L');
		// $pdf->Cell(55,$height,'Ttd Perwakilan Setempat',1,1,'L');
		// 	$pdf->Cell(190,$height,'Catatan Lain','TLR',1,'L');
		// 	$pdf->Cell(190,$height*4,'','BLR',1,'L');
		
		
	
		
		##################################
		##biayaa uang muka
		##################################
		
		 // $pdf->AddPage();
       // $pdf->SetY(40);
		//$pdf->SetFont('Arial','B',12);
	    //$pdf->SetFillColor(255,255,255); 
		//$pdf->SetX(15);
		//$pdf->Cell(175,$height,'BIMA PALMA GROUP',0,1,'L');
		//$pdf->SetX(15);
		//$pdf->Cell(175,$height,'',0,1,'L');
		$pdf->Ln();
       
		//$pdf->SetFont('Arial','BU',12);
		// $pdf->Cell(190,$height,strtoupper('pengajuan uang muka perjalanan dinas'),0,1,'C');
		//$pdf->SetFont('Arial','B',14);
		// $pdf->Cell(190,$height,$notransaksi,0,1,'C');
		$pdf->SetFont('Arial','B',7);
		$pdf->SetX(20);
		if ($namatamu=='') {
			$pdf->Cell(100,$height,'ESTIMASI PEMAKAIAN BIAYA (KAS BON)',0,1,'L');
			
			$pdf->SetX(20);
			$pdf->Cell(35,$height,'PENGAJUAN DANA',1,0,'L');
			$pdf->Cell(35,$height,'Plafon/Harga',1,0,'C');
			$pdf->Cell(35,$height,'TOTAL',1,0,'C');
			$pdf->Cell(65,$height,strtoupper($_SESSION['lang']['keterangan']),1,1,'C');
			
			// $pdf->Cell(6,$height,'No.',1,0,'C');
			// $pdf->Cell(32,$height,'Tgl',1,0,'C');
			// $pdf->Cell(65,$height,'Penjelasan',1,0,'C');
			// $pdf->Cell(15,$height,'Jml (Rp)',1,0,'C');
			// $pdf->Cell(15,$height,'HRD (Rp)',1,0,'C');
			// $pdf->Cell(65,$height,'Keterangan',1,1,'C');
			
			
			/*//Diganti 
			$str="select * from ".$dbname.".sdm_5jenisbiayapjdinas ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$dtkel[$bar['id']]=$bar['id'];
				$nmtipe[$bar['id']]=$bar['keterangan'];
			}
			
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber=0  ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				//$dtkel[$bar['jenisbiaya']]=$bar['jenisbiaya'];
				$dtdet[$bar['detail']] = $bar['detail'];
				$listdetail[$bar['jenisbiaya']][$bar['detail']]=$bar['detail'];
				// if($bar['tanggalsampai']=='0000-00-00'){
				// 	$tgl[$bar['jenisbiaya']][$bar['detail']]=tanggalnormal($bar['tanggal']);
				// }else{
				// 	$tgl[$bar['jenisbiaya']][$bar['detail']]=tanggalnormal($bar['tanggal']).' s/d '.tanggalnormal($bar['tanggalsampai']);
				// }
				$tgldari[$bar['jenisbiaya']][$bar['detail']]=$bar['tanggal'];
				$tglsampai[$bar['jenisbiaya']][$bar['detail']]=$bar['tanggalsampai'];
				$rphrd[$bar['jenisbiaya']][$bar['detail']]=$bar['jumlahhrd'];
				$rp[$bar['jenisbiaya']][$bar['detail']]=$bar['jumlah'];
				$ket[$bar['jenisbiaya']][$bar['detail']]=$bar['keterangan'];
			}
			$gtotharga=0;
			$pdf->SetFont('Arial','',7);
			foreach($dtkel as $kel){
				@$nojudul+=1;
				$nourut=0;

				// $pdf->Cell(6,$height*1.5,romawi($nojudul),1,0,'C');
				// //$pdf->Cell(40,$height*1.5,'',1,0,'L');
				// $pdf->Cell(192,$height*1.5,$nmtipe[$kel],1,1,'C');
				// //$pdf->Cell(40,$height*1.5,'',1,0,'R');
				// //$pdf->Cell(40,$height*1.5,'',1,1,'C');
				$pdf->SetFont('Arial','',7);
				if(!empty($dtdet)){
					foreach($dtdet as $det){
						if(@$listdetail[$kel][$det]!=''){
							// $nourut+=1;
							// $pdf->Cell(6,$height,$nourut,1,0,'C');
							// $pdf->Cell(32,$height,$tgl[$kel][$det],1,0,'L');
							// $pdf->Cell(65,$height,$det,1,0,'L');
							// $pdf->Cell(15,$height,@number_format($rp[$kel][$det]),1,0,'R');
							// $pdf->Cell(15,$height,@number_format($rphrd[$kel][$det]),1,0,'R');
							// $pdf->Cell(65,$height,$ket[$kel][$det],1,1,'L');

							$starttime[$kel]=strtotime($tgldari[$kel][$det]);// tanggal pengajuan
				            $endtime[$kel]=strtotime($tglsampai[$kel][$det]);//tanggal sampai
				            $timediff[$kel] = $endtime[$kel]-$starttime[$kel];
				            $days[$kel]=intval($timediff[$kel]/86400);
							if($days[$kel] == 0){
								 $days[$kel] = 1;	
							}
							@$subtot[$kel]+=$rp[$kel][$det];
							@$subtothrd[$kel]+=$rphrd[$kel][$det];

							$totharga[$kel]=$days[$kel]*$rp[$kel][$det];
							@$subtotharga[$kel]+=$totharga[$kel];
									
						}
					}
				}
				*/
					// $pdf->SetFont('Arial','B',7);
				// $pdf->Cell(6,$height*1.5,'',1,0,'C');
				// $pdf->Cell(32,$height*1.5,'',1,0,'L');
				// $pdf->Cell(65,$height*1.5,'Sub Total',1,0,'R');
				// $pdf->Cell(15,$height*1.5,@number_format($subtot[$kel]),1,0,'R');
				// $pdf->Cell(15,$height*1.5,@number_format($subtothrd[$kel]),1,0,'R');
				// $pdf->Cell(65,$height*1.5,'',1,1,'C');
				// $pdf->SetFont('Arial','',7);
				
				
				
				/*
				while($bar=$res->fetch()){
					$data_dt[$bar['jenisbiaya']]['jenisbiaya'] = $bar['jenisbiaya'];
					$data_dt[$bar['jenisbiaya']]['detail'] 	= $bar['detail'];
					$data_dt[$bar['jenisbiaya']]['tanggal'] = $bar['tanggal'];
					$data_dt[$bar['jenisbiaya']]['tanggalsampai'] = $bar['tanggalsampai'];
					$data_dt[$bar['jenisbiaya']]['jumlahhrd'] = $bar['jumlahhrd'];
					$data_dt[$bar['jenisbiaya']]['jumlah'] = $bar['jumlah'];
					$data_dt[$bar['jenisbiaya']]['keterangan'] = $bar['keterangan'];
					$datadtgroup[] = $data_dt;
				}*/

			$yawal=$pdf->GetY();
			$gtotharga=0;
			$pdf->SetFont('Arial','',7);
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber=0  ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$datadetail = array();
			while($r=$res->fetch()){
				$r['frekuensi']=1;
				$data_dt['jenisbiaya'] 		= $r['jenisbiaya'];
				$data_dt['detail'] 			= $r['detail'];
				$data_dt['frekuensi']		= $r['frekuensi'];
				$data_dt['tanggal'] 		= $r['tanggal'];
				$data_dt['tanggalsampai'] 	= $r['tanggalsampai'];
				$data_dt['jumlahhrd']	 	= $r['jumlahhrd'];
				$data_dt['jumlah'] 			= $r['jumlah'];
				$data_dt['keterangan'] 		= $r['keterangan'];
				$datadetail[] = $data_dt;
			}
			$str="select * from ".$dbname.".sdm_5jenisbiayapjdinas order by id";
			$rjenisbiaya=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$rjenisbiaya->setFetchMode(PDO::FETCH_ASSOC);
			$gtotharga = 0;
			while($r=$rjenisbiaya->fetch()){
				$id = $r['id'];
				$ket = $r['keterangan'];
				$rByjenis = "";
				$frekuensi= 1;
				$harga	  = "";
				
				$rByjenis 	= getDataByJenis($datadetail,$id);
				$frekuensi 	= getFrekuensi($rByjenis); 
				$keterangan 	= getKeterangan($rByjenis); 
				//print_r($frekuensi); 
				//exit();

				$harga 		= getharga($rByjenis,'jumlahhrd'); 
				
				$subtotal 		= (int)$frekuensi * $harga; 
				
				$pdf->SetFont('Arial','',7);
				$pdf->SetX(20);
				
				$pdf->Cell(35,$height,$r['keterangan'],'TLR',0,'L');
				// $pdf->Cell(35,$height,$frekuensi,1,0,'C');
				$pdf->Cell(10,$height,'Rp','T',0,'L');
				$pdf->Cell(25,$height,number_format($harga),'TR',0,'R');
				$pdf->Cell(10,$height,'Rp','T',0,'L');
				$pdf->Cell(25,$height,number_format($subtotal),'TR',0,'R'); //@number_format(@$subtotharga[$kel])
				$pdf->MultiCell(65,$height,$keterangan,'TR',1,'J'); //@number_format(@$subtotharga[$kel])

				$gtotharga += $subtotal;
			}


			$yakhir=$pdf->GetY();

			$pdf->Line(20, $yawal, 20, $yakhir);
			$pdf->Line(55, $yawal,55, $yakhir);
			$pdf->Line(90, $yawal, 90, $yakhir);
			$pdf->Line(125, $yawal, 125, $yakhir);


				$pdf->SetFont('Arial','B',7);
				$pdf->SetX(20);
				$pdf->Cell(70,$height,'JUMLAH PENGAJUAN UANG MUKA',1,0,'C');
				$pdf->Cell(35,$height,number_format($gtotharga),'TBR',0,'R');
				$pdf->Cell(65,$height,'','TBR',1,'R');
				$pdf->SetFont('Arial','',7);
				$pdf->SetX(20);
				$pdf->Cell(170,$height,'Terbilang : ','TLR',1,'L');
				$pdf->SetX(20);
				if(@$gtotharga!=''){
					$pdf->MultiCell(170,$height,terbilang($gtotharga,1),'LBR',1,'L');
				}else{
					$pdf->MultiCell(170,$height,'','LBR',1,'L');
				}
		}
		

		// $pdf->SetFont('Arial','B',7);
		// 	$pdf->Cell(6,$height*1.5,'',1,0,'C');
		// 	$pdf->Cell(32,$height*1.5,'',1,0,'L');
		// 	$pdf->Cell(65,$height*1.5,'Grand Total',1,0,'R');
		// 	$pdf->Cell(15,$height*1.5,@number_format($gtot),1,0,'R');
		// 	$pdf->Cell(15,$height*1.5,@number_format($gtothrd),1,0,'R');
		// 	$pdf->Cell(65,$height*1.5,'',1,1,'C');
		// 	$pdf->SetFont('Arial','',7);
			
		// $terpakaihrd=$gtothrd;
		
		
		$pdf->Ln();	

		$pdf->SetX(20);
		$pdf->Cell(45,$height,'Verifikasi Benefit,',1,0,'C');
		$pdf->Cell(80,$height,'Disetujui oleh,',1,0,'C');
		$pdf->Cell(45,$height,'Dibuat oleh,',1,1,'C');
		$yawal=$pdf->GetY();
		
		// $pdf->Cell(40,$height,'Diajukan Oleh','',0,'C');
		// $pdf->Cell(55,$height,'Disetujui Oleh','',0,'C');
		// $pdf->Cell(55,$height,'Diperiksa Oleh','',0,'C');
		// $pdf->Cell(50,$height,'Disetujui Oleh','',0,'C');
		$pdf->Ln();	
		$pdf->Ln();	
		$pdf->Ln();	
		$pdf->Ln();
		// $pdf->Cell(40,$height,$namakaryawan,'',0,'C');
		// $pdf->Cell(55,$height,$namakar[$persetujuan],'',0,'C');			
		// $pdf->Cell(55,$height,$namakar[$hrd],'',0,'C');			
		// $pdf->Cell(50,$height,$namakar[$persetujuan2],'',1,'C');	
		$yakhirg=$pdf->GetY();
		$pdf->SetX(20);
		$pdf->Cell(45,$height,$namakar[$per['persetujuan3']],1,0,'C');
		$pdf->Cell(40,$height,$namakar[$per['persetujuan2']],1,0,'C');
		$pdf->Cell(40,$height,$namakar[$per['persetujuan1']],1,0,'C');
		$pdf->Cell(45,$height,$namakaryawan,1,1,'C');
		$yakhir=$pdf->GetY();

		$pdf->Line(20, $yawal, 20, $yakhir);
		$pdf->Line(65, $yawal, 65, $yakhir);
		$pdf->Line(105, $yawal, 105, $yakhir);
		$pdf->Line(145, $yawal, 145, $yakhir);
		$pdf->Line(190, $yawal, 190, $yakhir);
		
		$pdf->SetY($yakhir);
		$pdf->Ln(5);
		$pdf->SetX(20);
		$pdf->Cell(15,$height,'',0,0,'L');
		$pdf->Ln(5);
		$pdf->SetX(20);
		$pdf->Cell(60,$height,'Disetujui Oleh',1,1,'C');

		$pdf->SetX(20);
		$pdf->Cell(60,20,"",1,1,'C');

		$pdf->SetX(20);
		$pdf->Cell(60,$height,$namakar[$per['persetujuan4']],1,1,'C');

		
		$pdf->SetY($yakhir);
		$pdf->Ln(10);
		$pdf->SetX(110);
		$pdf->Cell(25,$height,'Nama Bank',1,0,'C');		
		$pdf->Cell(25,$height,'Cabang Bank',1,0,'C');		
		$pdf->Cell(30,$height,'Nomor Rekening',1,1,'C');

		$pdf->SetX(110);
		$pdf->Cell(25,20,$namabank,1,0,'C');	
		$pdf->Cell(25,20,"",1,0,'C');
		$pdf->Cell(30,20,$norek,1,1,'C');

		$pdf->SetX(110);
		$pdf->Cell(80,$height,$namakaryawan,1,1,'C');


		$pdf->AddPage();
       	// $pdf->SetY(40);
       	$height=20;
	   	$pdf->SetFont('Arial','',9);
	    $pdf->SetFillColor(255,255,255); 
		$pdf->Ln();
		$pdf->Cell(120,5,'DIISI OLEH PEJABAT DITEMPAT TUJUAN : ','B',1,'L');
		$pdf->Cell(62,$height,'Tanggal  s/d  ',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');			
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,1,'L');

		// $height=7;
		$pdf->Cell(62,$height,'Tugas Yang Dilakukan',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');			
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,1,'L');

		$pdf->Cell(62,$height,'TTD atau cap Pejabat Yang Dituju',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');			
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,1,'L');

		// $height=5;
		$pdf->Cell(120,2,'','B',1,'L');
		$pdf->Cell(62,$height,'Tanggal  s/d  ',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');			
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,1,'L');

		// $height=7;
		$pdf->Cell(62,$height,'Tugas Yang Dilakukan',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');			
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,1,'L');

		$pdf->Cell(62,$height,'TTD atau cap Pejabat Yang Dituju',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');			
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,0,'L');
		$pdf->Cell(32,$height,'',1,1,'L');
			
	   		
	   		//$pdf->Line(20,$yakhir,200,$yakhir);	
		
			// $pdf->Cell(40,$height,'Karyawan','',0,'C');
			// $pdf->Cell(55,$height,'Pimpinan','',0,'C');			
			// $pdf->Cell(55,$height,'HRD','',0,'C');
			// $pdf->Cell(50,$height,'Div. Head / Direktur','',0,'C');
			// $pdf->Ln();
		
		
		
		
		
		
		
		
		##################################
		##biayaa pertanggung jawaban
		##################################
		
		//  $pdf->AddPage();
  //      	// $pdf->SetY(40);
	 //   	$pdf->SetFont('Arial','B',12);
	 //    $pdf->SetFillColor(255,255,255); 
		// $pdf->SetX(15);
		// //$pdf->Cell(175,$height,'BIMA PALMA GROUP',0,1,'L');
		// $pdf->Cell(175,$height,'',0,1,'L');
		// $pdf->Ln();
       
		// $pdf->SetFont('Arial','BU',12);
		// $pdf->Cell(190,$height,strtoupper('Pertanggung jawaban perjalanan dinas'),0,1,'C');
		// $pdf->SetFont('Arial','B',14);
		// $pdf->Cell(190,$height,$notransaksi,0,1,'C');
  //       $pdf->Ln(10);
		// $pdf->SetFont('Arial','B',7);
		
		// $pdf->Cell(6,$height,'No.',1,0,'C');
		// $pdf->Cell(32,$height,'Tgl',1,0,'C');
		// $pdf->Cell(65,$height,'Penjelasan',1,0,'C');
		// $pdf->Cell(15,$height,'Jml (Rp)',1,0,'C');
		// $pdf->Cell(15,$height,'HRD (Rp)',1,0,'C');
		// $pdf->Cell(65,$height,'Keterangan',1,1,'C');
		
		// $dtdet=$subtot=$subtothrd=array();
		
		
		// $str="select * from ".$dbname.".sdm_5jenisbiayapjdinas ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$dtkel[$bar['id']]=$bar['id'];
		// 	$nmtipe[$bar['id']]=$bar['keterangan'];
		// }
		// $nojudul=0;
		// $str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber=1";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	//$dtkel[$bar['jenisbiaya']]=$bar['jenisbiaya'];
		// 	$dtdet[$bar['detail']]=$bar['detail'];
		// 	$listdetail[$bar['jenisbiaya']][$bar['detail']]=$bar['detail'];
		// 	if($bar['tanggalsampai']=='0000-00-00'){
		// 		$tgl[$bar['jenisbiaya']][$bar['detail']]=tanggalnormal($bar['tanggal']);
		// 	}else{
		// 		$tgl[$bar['jenisbiaya']][$bar['detail']]=tanggalnormal($bar['tanggal']).' s/d '.tanggalnormal($bar['tanggalsampai']);
		// 	}
		// 	$rphrd[$bar['jenisbiaya']][$bar['detail']]=$bar['jumlahhrd'];
		// 	$rp[$bar['jenisbiaya']][$bar['detail']]=$bar['jumlah'];
		// 	$ket[$bar['jenisbiaya']][$bar['detail']]=$bar['keterangan'];
		// }
		// $gtot=$gtothrd=0;
		// $pdf->SetFont('Arial','',7);
		// foreach($dtkel as $kel){
			
		// 	$nojudul+=1;
		// 	$nourut=0;
		// 	$pdf->SetFont('Arial','B',7);
		// 	$pdf->Cell(6,$height*1.5,romawi($nojudul),1,0,'C');
		// 	//$pdf->Cell(40,$height*1.5,'',1,0,'L');
		// 	$pdf->Cell(192,$height*1.5,$nmtipe[$kel],1,1,'C');
		// 	//$pdf->Cell(40,$height*1.5,'',1,0,'R');
		// 	//$pdf->Cell(40,$height*1.5,'',1,1,'C');
		// 	$pdf->SetFont('Arial','',7);
		// 	foreach($dtdet as $det){
		// 		if(@$listdetail[$kel][$det]!=''){
		// 			$nourut+=1;
		// 			$pdf->Cell(6,$height,$nourut,1,0,'C');
		// 			$pdf->Cell(32,$height,$tgl[$kel][$det],1,0,'L');
		// 			$pdf->Cell(65,$height,$det,1,0,'L');
		// 			$pdf->Cell(15,$height,@number_format($rp[$kel][$det]),1,0,'R');
		// 			$pdf->Cell(15,$height,@number_format($rphrd[$kel][$det]),1,0,'R');
		// 			$pdf->Cell(65,$height,$ket[$kel][$det],1,1,'L');
		// 			@$subtot[$kel]+=$rp[$kel][$det];
		// 			@$subtothrd[$kel]+=$rphrd[$kel][$det];
		// 		}
		// 	}
		// 	$pdf->SetFont('Arial','B',7);
		// 	$pdf->Cell(6,$height*1.5,'',1,0,'C');
		// 	$pdf->Cell(32,$height*1.5,'',1,0,'L');
		// 	$pdf->Cell(65,$height*1.5,'Sub Total',1,0,'R');
		// 	$pdf->Cell(15,$height*1.5,@number_format($subtot[$kel]),1,0,'R');
		// 	$pdf->Cell(15,$height*1.5,@number_format($subtothrd[$kel]),1,0,'R');
		// 	$pdf->Cell(65,$height*1.5,'',1,1,'C');
		// 	$pdf->SetFont('Arial','',7);
		// 	@$gtot+=$subtot[$kel];
		// 	@$gtothrd+=$subtothrd[$kel];
		// }
		// $pdf->SetFont('Arial','B',7);
		// 	$pdf->Cell(6,$height*1.5,'',1,0,'C');
		// 	$pdf->Cell(32,$height*1.5,'',1,0,'L');
		// 	$pdf->Cell(65,$height*1.5,'Grand Total',1,0,'R');
		// 	$pdf->Cell(15,$height*1.5,@number_format($gtot),1,0,'R');
		// 	$pdf->Cell(15,$height*1.5,@number_format($gtothrd),1,0,'R');
		// 	$pdf->Cell(65,$height*1.5,'',1,1,'C');
		// 	$pdf->SetFont('Arial','',8);
		// $pdf->Ln();	
		
		
		// $sisa=$dibayar-$gtothrd;
		
		// $pdf->Cell(30,$height,'Uang Muka',0,0,'L');
		// $pdf->Cell(5,$height,':',0,0,'L');
		// $pdf->Cell(20,$height,@number_format($dibayar),0,1,'R');
		// $pdf->Cell(30,$height,'Pemakaian Disetujui',0,0,'L');
		// $pdf->Cell(5,$height,':',0,0,'L');
		// $pdf->Cell(20,$height,@number_format($gtothrd),0,1,'R');
		// $pdf->Cell(30,$height,'Sisa',0,0,'L');
		// $pdf->Cell(5,$height,':',0,0,'L');
		// $pdf->Cell(20,$height,@number_format($sisa),0,1,'R');
		// $pdf->Ln();
			
		// 	$pdf->Cell(40,$height,'Diajukan Oleh','',0,'C');
		// 	$pdf->Cell(55,$height,'Disetujui Oleh','',0,'C');
		// 	$pdf->Cell(55,$height,'Diperiksa Oleh','',0,'C');
		// 	$pdf->Cell(50,$height,'Disetujui Oleh','',0,'C');
	 //   $pdf->Ln();	
	 //   $pdf->Ln();	
	 //   $pdf->Ln();	
	 //   $pdf->Ln();
			// $pdf->Cell(40,$height,$namakaryawan,'',0,'C');
			// $pdf->Cell(55,$height,$namakar[$persetujuan],'',0,'C');			
			// $pdf->Cell(55,$height,$namakar[$hrd],'',0,'C');			
			// $pdf->Cell(50,$height,$namakar[$persetujuan2],'',1,'C');	
			
			
			// $pdf->Cell(40,$height,$namakaryawan,'',0,'C');
			// $pdf->Cell(55,$height,'Pimpinan','',0,'C');			
			// $pdf->Cell(55,$height,'HRD','',0,'C');
			// $pdf->Cell(50,$height,'Finance','',0,'C');
			// $yakhir=$pdf->GetY();


	   //$pdf->Line(20,$yakhir,200,$yakhir);	
		
			// $pdf->Cell(40,$height,'Karyawan','',0,'C');
			// $pdf->Cell(55,$height,'Pimpinan','',0,'C');			
			// $pdf->Cell(55,$height,'HRD','',0,'C');
			// $pdf->Cell(50,$height,'Div. Head / Direktur','',0,'C');
		// $pdf->Ln();
		
		
		
		
		
		
		
		
		
		
        $pdf->Output();

?>
