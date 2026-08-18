<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/terbilang.php');

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
		function getharga($array){
			$harga = 0;
			if(count($array)>0){
				$harga = 0;
				for($i=0; $i<count($array); $i++){
					$jumlah = $array[$i]['jumlah'];
					$harga 	= $harga + $jumlah;
				}
			}
			$result = $harga;
			return $result;
		}

$notransaksi=$_GET['notransaksi'];
$namakar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
//=============

$namadept=  makeOption($dbname, 'sdm_5departemen', 'kode,nama');
$reg=  makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional');

$namajenis=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');

//create Header
class PDF extends FPDF
{

        function Header()
        {

        }

        function Footer()
        {
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
                 $strc="select a.namakaryawan,a.karyawanid,a.bagian,b.namajabatan,a.nik,a.kodegolongan,a.lokasitugas
                    from ".$dbname.".datakaryawan a left join  ".$dbname.".sdm_5jabatan b
                        on a.kodejabatan=b.kodejabatan
                        where a.karyawanid=".$bar->karyawanid;

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
          }

          //===============================	  
				
                $kodeorg=$bar->kodeorg;
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
        $strf="select a.bagian,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join
               ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                   where karyawanid=".$persetujuan;	  
		$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
		$resf->setFetchMode(PDO::FETCH_OBJ);
        while($barf=$resf->fetch())  {
				
                $perjabatan=$barf->namajabatan;
                $perbagian=$barf->bagian;
                $pernama=$barf->namakaryawan;
        }	 
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
   /*// PT Tujuan
	$qTujuan = selectQuery($dbname,'organisasi','induk',"kodeorganisasi='".$tujuan2."'");
	$resTujuan = fetchData($qTujuan);
	$ptTujuan = $resTujuan[0]['induk'];
	 */
	/*// Regional Tujuan
	$qRegional = selectQuery($dbname,'bgt_regional_assignment','regional',"kodeunit='".$tujuan2."'");
	$resRegional = fetchData($qRegional);
	$reg = $resRegional[0]['regional'];
	
	$waktu			= $resTujuan[0]['waktu'];
	$dari			= $resTujuan[0]['dari'];
	$tujuan			= $resTujuan[0]['tujuan'];
	$transportasi	= $resTujuan[0]['transportasi'];
    */
	
	$qTujuan = selectQuery($dbname,'sdm_pjdinasdt_rute','*',"notransaksi='".$notransaksi."'");
	$route = fetchData($qTujuan); // Array
	
	$qkegiatan = selectQuery($dbname,'sdm_pjdinasdt2','*',"notransaksi='".$notransaksi."'");
	$keg = fetchData($qkegiatan); // Array
	
	
  //printf($strLTgs);
  //Get Lokasi Tugas
  $strLTgs="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$tujuan1."'";
  $resLTgs=$owlPDO->query($strLTgs) or die(print " Gagal: ".PDOException::getMessage());
  $resLTgs->setFetchMode(PDO::FETCH_OBJ);
  while($barLTgs=$resLTgs->fetch()){
	$LTgs=$barLTgs->namaorganisasi;
  }
  
 
  
  
		$pdf=new PDF('P','mm','A4');
        $pdf->SetFont('Arial','B',12);
        //
        //$pdf->Cell(175,5,'NO : '.$notransaksi,0,1,'C');	

		$height=5;
		
		
		##################################
		##biayaa pertanggung jawaban
		##################################
		
		 $pdf->AddPage();
       // $pdf->SetY(40);
	   $pdf->SetFont('Arial','B',12);
	    $pdf->SetFillColor(255,255,255); 
		 $pdf->SetX(15);
		$pdf->Cell(175,$height,'BIMA PALMA GROUP',0,1,'L');
		$pdf->Ln();
       
		$pdf->SetFont('Arial','BU',12);
		$pdf->Cell(175,$height,strtoupper('Pertanggung jawaban perjalanan dinas'),0,1,'C');
        $pdf->Ln(10);
		$pdf->SetFont('Arial','B',8);
		
		$pdf->Cell(10,$height,'No.',1,0,'C');
		$pdf->Cell(40,$height,'Tgl',1,0,'C');
		$pdf->Cell(60,$height,'Penjelasan',1,0,'C');
		$pdf->Cell(20,$height,'Jml (Rp)',1,0,'C');
		$pdf->Cell(20,$height,'Jml. HRD (Rp)',1,0,'C');
		$pdf->Cell(40,$height,'Keterangan',1,1,'C');
		
		$dtdet=$subtot=$subtothrd=array();
		
		
		$str="select * from ".$dbname.".sdm_5jenisbiayapjdinas ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$dtkel[$bar['id']]=$bar['id'];
			$nmtipe[$bar['id']]=$bar['keterangan'];
		}
		$nojudul=0;
		$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber=1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			//$dtkel[$bar['jenisbiaya']]=$bar['jenisbiaya'];
			$dtdet[$bar['detail']]=$bar['detail'];
			$listdetail[$bar['jenisbiaya']][$bar['detail']]=$bar['detail'];
			if($bar['tanggalsampai']=='0000-00-00'){
				$tgl[$bar['jenisbiaya']][$bar['detail']]=tanggalnormal($bar['tanggal']);
			}else{
				$tgl[$bar['jenisbiaya']][$bar['detail']]=tanggalnormal($bar['tanggal']).' s/d '.tanggalnormal($bar['tanggalsampai']);
			}
			$rphrd[$bar['jenisbiaya']][$bar['detail']]=$bar['jumlahhrd'];
			$rp[$bar['jenisbiaya']][$bar['detail']]=$bar['jumlah'];
			$ket[$bar['jenisbiaya']][$bar['detail']]=$bar['keterangan'];
		}
		$gtot=$gtothrd=0;
		$pdf->SetFont('Arial','',8);
		foreach($dtkel as $kel){
			
			$nojudul+=1;
			$nourut=0;
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(10,$height*1.5,romawi($nojudul),1,0,'C');
			//$pdf->Cell(40,$height*1.5,'',1,0,'L');
			$pdf->Cell(180,$height*1.5,$nmtipe[$kel],1,1,'C');
			//$pdf->Cell(40,$height*1.5,'',1,0,'R');
			//$pdf->Cell(40,$height*1.5,'',1,1,'C');
			$pdf->SetFont('Arial','',8);
			foreach($dtdet as $det){
				if(@$listdetail[$kel][$det]!=''){
					$nourut+=1;
					$pdf->Cell(10,$height,$nourut,1,0,'C');
					$pdf->Cell(40,$height,$tgl[$kel][$det],1,0,'L');
					$pdf->Cell(60,$height,$det,1,0,'L');
					$pdf->Cell(20,$height,number_format($rp[$kel][$det]),1,0,'R');
					$pdf->Cell(20,$height,number_format($rphrd[$kel][$det]),1,0,'R');
					$pdf->Cell(40,$height,$ket[$kel][$det],1,1,'C');
					@$subtot[$kel]+=$rp[$kel][$det];
					@$subtothrd[$kel]+=$rphrd[$kel][$det];
				}
			}
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(10,$height*1.5,'',1,0,'C');
			$pdf->Cell(40,$height*1.5,'',1,0,'L');
			$pdf->Cell(60,$height*1.5,'Sub Total',1,0,'R');
			$pdf->Cell(20,$height*1.5,number_format(@$subtot[$kel]),1,0,'R');
			$pdf->Cell(20,$height*1.5,number_format(@$subtothrd[$kel]),1,0,'R');
			$pdf->Cell(40,$height*1.5,'',1,1,'C');
			$pdf->SetFont('Arial','',8);
			@$gtot+=$subtot[$kel];
			@$gtothrd+=$subtothrd[$kel];
		}
		$pdf->SetFont('Arial','B',8);
			$pdf->Cell(10,$height*1.5,'',1,0,'C');
			$pdf->Cell(40,$height*1.5,'',1,0,'L');
			$pdf->Cell(60,$height*1.5,'Grand Total',1,0,'R');
			$pdf->Cell(20,$height*1.5,number_format($gtot),1,0,'R');
			$pdf->Cell(20,$height*1.5,number_format($gtothrd),1,0,'R');
			$pdf->Cell(40,$height*1.5,'',1,1,'C');
			$pdf->SetFont('Arial','',8);
		$pdf->Ln();	
		
		
		$sisa=$uangmuka-$gtothrd;
		
		$pdf->Cell(30,$height,'Uang Muka',0,0,'L');
		$pdf->Cell(5,$height,':',0,0,'L');
		$pdf->Cell(20,$height,number_format($uangmuka),0,1,'R');
		$pdf->Cell(30,$height,'Pemakaian Disetujui',0,0,'L');
		$pdf->Cell(5,$height,':',0,0,'L');
		$pdf->Cell(20,$height,number_format($gtothrd),0,1,'R');
		$pdf->Cell(30,$height,'Sisa',0,0,'L');
		$pdf->Cell(5,$height,':',0,0,'L');
		$pdf->Cell(20,$height,number_format($sisa),0,1,'R');
		$pdf->Ln();
			
			$pdf->Cell(40,$height,'Diajukan Oleh','',0,'C');
			$pdf->Cell(55,$height,'Disetujui Oleh','',0,'C');
			$pdf->Cell(55,$height,'Diperiksa Oleh','',0,'C');
			$pdf->Cell(50,$height,'Disetujui Oleh','',0,'C');
	   $pdf->Ln();	
	   $pdf->Ln();	
	   $pdf->Ln();	
	   $pdf->Ln();
			// $pdf->Cell(40,$height,$namakaryawan,'',0,'C');
			// $pdf->Cell(55,$height,$namakar[$persetujuan],'',0,'C');			
			// $pdf->Cell(55,$height,$namakar[$hrd],'',0,'C');			
			// $pdf->Cell(50,$height,$namakar[$persetujuan2],'',1,'C');	
			
			
			$pdf->Cell(40,$height,$namakaryawan,'',0,'C');
			$pdf->Cell(55,$height,'Pimpinan','',0,'C');			
			$pdf->Cell(55,$height,'HRD','',0,'C');
			$pdf->Cell(50,$height,'Finance','',0,'C');
			$yakhir=$pdf->GetY();
	   //$pdf->Line(20,$yakhir,200,$yakhir);	
		
			// $pdf->Cell(40,$height,'Karyawan','',0,'C');
			// $pdf->Cell(55,$height,'Pimpinan','',0,'C');			
			// $pdf->Cell(55,$height,'HRD','',0,'C');
			// $pdf->Cell(50,$height,'Div. Head / Direktur','',0,'C');
		// $pdf->Ln();
		
		
		
		
		
		
		
		
		
		
        $pdf->Output();

?>
