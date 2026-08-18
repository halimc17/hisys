<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

$proses = $_GET['proses'];
$param = $_GET;
$urlefil=checkPostGet('urlefil','0');

$nmMt=  makeOption($dbname, 'setup_matauang', 'kode,matauang');
$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmakun=  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$nmket=  makeOption($dbname, 'keu_5keterangan', 'id_ket,keterangan');


/** Report Prep **/
$cols = array();

#=============================== Header ======================================= keu_kaskecilht
$whereH = "notransaksi='".$param['notransaksi'].
    "' and unit='".$param['kodeorg'].
    "' and tipe='".$param['tipetransaksi']."'";
$queryH = selectQuery($dbname,'keu_kaskecilht','*',$whereH);
$resH = fetchData($queryH);


# Get Nama Pembuat
$userId = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',
    "karyawanid='".$resH[0]['userid']."'");
# Get Nama Akun Hutang
$namaakunhutang = makeOption($dbname,'keu_5akun','noakun,namaakun',
    "noakun='".$resH[0]['noakunhutang']."'");
#Get tipe Lokasi Tugas
$tipeLokasiTugas = makeOption($dbname,'organisasi','kodeorganisasi,tipe');

#=============================== Detail =======================================
# Data
$col1 = 'noakun,jumlah,noaruskas,keterangan2';
$cols = array('nourut','noakun','namaakun','noaruskas','debet','kredit');
$colshtml = array('nourut','noakun','namaakun','noaruskas','debet','kredit','keterangan');
//$col1 = 'noakun,jumlah,noaruskas,matauang,kode,hutangunit1';
//$cols = array('nomor','noakun','namaakun','matauang','debet','kredit','hutangunit');
$where = "notransaksi='".$param['notransaksi'].
   

    "' ";
$query = selectQuery($dbname,'keu_kaskecildt',$col1,$where);
$res = fetchData($query);

# Data Empty
if(empty($res)) {
    echo 'Data Empty';
    exit;
}

# Options
$whereAkun = "noakun in (";
$whereAkun .= "'".$resH[0]['noakun']."'";
$whereAkun .= ",'".$resH[0]['noakunhutang']."'"; // tambahin kamus nama akun hutangunit 
foreach($res as $key=>$row) {
    $whereAkun .= ",'".$row['noakun']."'";
}
$whereAkun .= ")";
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereAkun);
$optHutangUnit = array('0'=>'Tidak','1'=>'Ya');

# Data Show
$data = array();

#================================ Prep Data ===================================
# Total
$totalDebet = 0;$totalKredit = 0;

# Dari Header
$i=1;
$data[$i] = array(
    'nomor'=>@$i,
    'noakun'=>@$resH[0]['noakun'],
    'namaakun'=>@$optAkun[$resH[0]['noakun']],
    'noaruskas'=>@$resH[0]['noaruskas'],
    'debet'=>0,
    'kredit'=>0,
    'keterangan2'=>$row['keterangan2'],
);

if($param['tipetransaksi']=='M') {
    $data[$i]['debet'] = $resH[0]['jumlah'];

} else {
    $data[$i]['kredit'] = $resH[0]['jumlah'];

}


        if($resH[0]['tipetransaksi']=='K'){
                $title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['keluar'].")");
        }else{
                $title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['masuk'].")");
        }



$i++;

# Dari Detail
foreach($res as $row) {
    $data[$i] = array(
                'nomor'=>$i,
                'noakun'=>$row['noakun'],
                'namaakun'=>$optAkun[$row['noakun']],
                'noaruskas'=>$row['noaruskas'],
                'debet'=>0,
                'kredit'=>0,
        'keterangan2'=>$row['keterangan2'],
    );
//	'hutangunit1'=>$optHutangUnit[$row['hutangunit1']]
    if($param['tipetransaksi']=='M' and $row['jumlah']>0) {
        $data[$i]['kredit'] = $row['jumlah'];
        $totalKredit += $row['jumlah'];
    }
    else if($param['tipetransaksi']=='K' and $row['jumlah']<0){
        $data[$i]['kredit'] = $row['jumlah']*-1;
        $totalKredit += $row['jumlah']*-1;        
    }
    else if($param['tipetransaksi']=='M' and $row['jumlah']<0){
        $data[$i]['debet'] = $row['jumlah']*-1;
        $totalDebet += $row['jumlah']*-1;        
    }    
    else {
        $data[$i]['debet'] = $row['jumlah'];
        $totalDebet += $row['jumlah'];
    }
    $i++;
	@$totaldt+= $row['jumlah'];
}

// nyusun berdasarkan debet dulu, abis itu baru kredit. by dz
if(!empty($data)) foreach($data as $c=>$key) {
    $sort_debet[] = $key['debet'];
    $sort_kredit[] = $key['kredit'];
}

// sort
if(!empty($data))array_multisort($sort_debet, SORT_DESC, $sort_kredit, SORT_ASC, $data);

$align = explode(",","R,R,L,L,R,R,L,L");
$length = explode(",","7,12,35,10,18,18,10");
$titleDetail = 'Detail';

/** Output Format **/
switch($proses) {
	
	
	


    case 'pdf':
		
		if(!class_exists('PDF')){
			class PDF extends FPDF
			{
				function Header()
				{
					global $conn;
					global $dbname;
					global $userid;
					global $notransaksi;
					global $kodevhc;
					global $posting;
					global $kodept;
					global $param;
					global $owlPDO;

					//ambil nama pt
					$arrHead = setheadreport(substr($param['kodeorg'],0,4));
					
					$width = $this->w - $this->lMargin - $this->rMargin;
					$height = 5;
		//             $path=$arrHead['logo'];
		//             $this->Image($path,$this->lMargin,($this->tMargin-8),0,25);
		//             $this->SetFont('Arial','B',9);
		//             $this->SetFillColor(255,255,255);	
		//             $this->SetX(45);   
		//             $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
		//             $this->SetX(45); 		
		//             $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
		//             $this->SetX(45); 			
		//             $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
		//             $this->Line($this->lMargin,$this->tMargin+($height*4),
					// $this->lMargin+$width,$this->tMargin+($height*4));
		//             $this->Ln();
									
				}

				function Footer()
				{
					$width = $this->w - $this->lMargin - $this->rMargin;
					$height = 12;
					$this->SetY(-20);
					$this->SetFont('Arial','I',7);
					$this->Cell(1,$height,'Page '.$this->PageNo(),'T',0,'L');
					$str = "Printed by ".$_SESSION['standard']['username']."[".$_SESSION['empl']['lokasitugas']."]".
							":".@$rPeriode['periode']." at ".date('d-m-Y H:i:s');
					$this->Cell($width-1,$height,$str,'T',0,'R');
				}
			}
		}
	
		
		$pdf=new PDF('P','mm','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 5;

        $pdf->AddPage();
		$norekeninght="";
        $iht=$owlPDO->query("select * from ".$dbname.".keu_kaskecilht where notransaksi='".$param['notransaksi']."' ");
        $iht->setFetchMode(PDO::FETCH_ASSOC);
        $dht=  $iht->fetch();
		$noakunht=$dht['noakun'];
		$norekeninght=$dht['rekening'];
		if($norekeninght!=""){
			$sBankHeader="select a.rekening,a.atasnama,b.namabank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank 
			              where noakun='".$norekeninght."'";
			$rBankHeader=fetchData($sBankHeader);
			$norekheader=$rBankHeader[0]['rekening'];
			$atasnamaheader=$rBankHeader[0]['atasnama'];
			$namabankheader=$rBankHeader[0]['namabank'];
		}

		$catatanht=$dht['keterangan'];
		$notransaksi=$dht['notransaksi'];
		$tglvoucherht=$dht['tanggal'];
		$novoucherht=$dht['novoucher'];
		$hutangunitht=$dht['hutangunit'];
		$noakunhutanght=$dht['noakunhutang'];
		$bayarkepada=$dht['bayarkepada'];
		$dht['jumlah']=$totaldt;
			
			
		if($dht['posting']==1){
			$posting='POSTING';
		}else{
			$posting='NOT POSTING';
		}
		
	
		if($param['tipetransaksi']=='M') {
			$datahdb= $dht['jumlah'];
			$datahkr=0;
		} else {
			$datahkr=$dht['jumlah'];
			$datahdb=0;
		}
		$tdatah=$dht['jumlah'];
	

		#bentuk jatuh tempo dan data supplier untuk pengeluaran 
		$str="select * from ".$dbname.".keu_kaskecildt where notransaksi='".$param['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		@$noinvice=$bar['keterangan1'];
		@$kodecustomer=$bar['kodecustomer'];
		$nmkar="";
		$karidpenerima=array();
		if(intval($bar['nik'])!=0){
			$karidpenerima=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar['nik']."'");
			@$nmkar=$karidpenerima[$bar['nik']];
		}
			
			
		$str="select * from ".$dbname.".pmn_4customer where kodecustomer='".$kodecustomer."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		@$namacus=$bar['namacustomer'];	
			
		$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$noinvice."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		@$kodesupplier=$bar['kodesupplier'];
		@$tgljatuhtempo=tanggalnormal($bar['jatuhtempo']);
		if($tgljatuhtempo=='--'){
			@$tgljatuhtempo='';
		}
		
		$str="select * from ".$dbname.".log_5supplier  where supplierid='".$kodesupplier."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$namasupplier = $bar['namasupplier'];
		/*if(empty($namacus) or $namacus=='' or is_null($namacus)){
			if(empty($bayarkepada) or $bayarkepada == "" or is_null($bayarkepada)){
				@$namasup=$bar['namasupplier'];
			}else{
				@$namasup=$bayarkepada;	
			}
		}else{
			@$namasup=$namacus;
		}*/
		$namasup=$bayarkepada;
		if(empty($bayarkepada) or $bayarkepada == "" or is_null($bayarkepada)){
			if($nmkar!=''){
				$namasup=$nmkar;
			}elseif($namacus!=''){
				@$namasup=$namacus;
			}elseif($namasupplier!=''){
				@$namasup=$namasupplier;
			}
		}
		// @$atasnama=$bar['an'];
		// @$banksup=$bar['bank'];
		// @$rekeningsup=$bar['rekening'];

		$str="select * from ".$dbname.".pmn_4customercontact where kodecustomer='".$kodecustomer."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar2=$res->fetch();
		@$namacpcus=$bar2['nama'];	
		$penerima=$bayarkepada;
		if(empty($bayarkepada) or $bayarkepada == "" or is_null($bayarkepada)){	
			if($nmkar!=''){
				$penerima=$nmkar;
				@$atasnamacp=$nmkar;
			}elseif($namacus!=''){
				@$penerima=$namacus;
				@$atasnamacp=$namacpcus;
			}elseif($namasupplier!=''){
				@$penerima=$namasupplier;
			}
		}

		$idt=$owlPDO->query("select * from ".$dbname.".keu_kaskecildt where notransaksi='".$param['notransaksi']."' ");
        $idt->setFetchMode(PDO::FETCH_ASSOC);
        $ddt=  $idt->fetch();

        if($ddt['nodok']!=''){
        	$nodok=$ddt['nodok'];
        }else{
        	$nodok="-";
        }
		
		$pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);

		#= ke pt
		#= nama pt
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$kdpt=$bar['induk'];	
		
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$kdpt."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$namapt=$bar['namaorganisasi'];
		
        $pdf->Cell($width,$height,$namapt,0,1,'L',1);
		$pdf->Cell($width,$height,'JAKARTA',0,1,'L',1);
		$pdf->SetFillColor(220,220,220);
		
		if($param['tipetransaksi']=='M') {
			$pdf->Cell($width,$height,'VOUCHER PENERIMAAN KAS',1,1,'C',1);
		}else{
			$pdf->Cell($width,$height,'VOUCHER PENGELUARAN KAS',1,1,'C',1);
		}

		$pdf->Ln(2);

		$numformat=2;
		$width=47.5;
		$awalxkop=$pdf->GetX();
		$awalykop=$pdf->GetY();
		
		
		$pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);
		$pdf->Cell($width,$height,'NO. VOUCHER','TL',0,'L',1);
		$pdf->Cell($width,$height,': '.$notransaksi,'T',0,'L',1);
		if($param['tipetransaksi']=='K') {
			$pdf->Cell($width,$height,'DIBAYARKAN KEPADA','T',0,'L',1);
			$pdf->Cell($width,$height,': '.$namasup,'TR',1,'L',1);
		}else{
			$pdf->Cell($width,$height,'DITERIMA DARI','T',0,'L',1);
			$pdf->Cell($width,$height,': '.$penerima,'TR',1,'L',1);
		}
		
		$pdf->Cell($width,$height,'TGL. VOUCHER','LB',0,'L',1);
		$pdf->Cell($width,$height,': '.tanggalnormal($tglvoucherht),'B',0,'L',1);
		
		//indra
		$pdf->Cell($width,$height,'JUMLAH','B',0,'L',1);
		$pdf->Cell($width,$height,': '.number_format($totaldt,$numformat),'BR',1,'L',1);
		

		
		$pdf->Ln();
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $pdf->SetFont('Arial','B',9);
        
 
		
		//No. No. Akun Nama Akun Kode Kas Debet Kredit
        $pdf->SetFillColor(220,220,220);
        $pdf->Cell(10,$height,$_SESSION['lang']['nourut'],1,0,'C',1);
        $pdf->Cell(20,$height,'No. Akun',1,0,'C',1);
        $pdf->Cell(80,$height,$_SESSION['lang']['namaakun'],1,0,'C',1);
        $pdf->Cell(20,$height,'Kode Kas',1,0,'C',1);
        $pdf->Cell(30,$height,$_SESSION['lang']['debet'],1,0,'C',1);
        $pdf->Cell(30,$height,$_SESSION['lang']['kredit'],1,1,'C',1);
		
		$pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
        
		
		$datahdb=$datahkr=0;
		//prepare data
		if($param['tipetransaksi']=='M') {
			$datahdb=$dht['jumlah'];
		} else {
			$datahkr=$dht['jumlah'];
		}
		


		if($datahdb > 0){
			############################### head #################################
			############## buat baris pertama dulu untuk pembanding ##############
			######################################################################
			$height=5;
			$awalynmakun=$pdf->GetY();

			$whrakun=" noakun = '1112102' ";
			$optAk = makeOption($dbname,'keu_5akun','noakun,namaakun',$whrakun);
			
			$pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
			$pdf->MultiCell(80, $height, $optAk['1112102'], '0', 'L');
			$akhirynmakun=$pdf->GetY();
			$tinggiynmakun=$akhirynmakun-$awalynmakun;
			$heightakun=$tinggiynmakun;
			$pdf->SetY($akhirynmakun-$tinggiynmakun);
			
			$no+=1;
			
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
			
			if($heightakun>$height){
				$pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+10);
				$pdf->Line($awalxlist+10, $awalylist+5, $awalxlist+10, $awalylist+10);
				$pdf->Line($awalxlist+130, $awalylist+5, $awalxlist+130, $awalylist+10);
				$pdf->Line($awalxlist+160, $awalylist+5, $awalxlist+160, $awalylist+10);
				$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+10);
			}
			
			$pdf->Cell(10,$height,$no,'TRL',0,'C',1);
			$pdf->Cell(20,$height,'1112102','TRL',0,'R',1);
			$awalxlistnmakun=$pdf->GetX();
			$pdf->MultiCell(80,$height,$optAk['1112102'],'TRL','J');
			$pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
			@$pdf->Cell(20,$height,$dht['noaruskas'],'TRL',0,'C',1);
			@$pdf->Cell(30,$height,number_format($datahdb,2),'TRL',0,'R',1);
			@$pdf->Cell(30,$height,number_format($datahkr,2),'TRL',1,'R',1);
			
			if($heightakun>$height){
				$isi=$pdf->Ln();
			}
			
			########################################
			############## tutup head ##############
			########################################
		}
        
        #########################################
        ############## buat detail ##############
		#########################################
		$idtd=$owlPDO->query("select * from ".$dbname.".keu_kaskecildt where notransaksi='".$param['notransaksi']."' order by jumlah desc");
        $idtd->setFetchMode(PDO::FETCH_ASSOC);
        while($ddtd= $idtd->fetch()){
			// echo $param['tipetransaksi']._.$ddtd['jumlah'];exit("Error:A");
			if($param['tipetransaksi']=='M' and $ddtd['jumlah']>0) {
				$datadkr=$ddtd['jumlah'];
                $dataddb=0;
				#= diakalin kalau masuk lempar ke AS
				$ddtd['noakun']='1110401';
			}else if($param['tipetransaksi']=='K' and $ddtd['jumlah']<0){
				$datadkr=$ddtd['jumlah']*-1;
                $dataddb=0;
			}else if($param['tipetransaksi']=='M' and $ddtd['jumlah']<0){
				$dataddb=$ddtd['jumlah']*-1;
				$datadkr=0;
			}else{
				$dataddb=$ddtd['jumlah'];
				$datadkr=0;
			}
			
				
		
			##buat baris pertama dulu
            $height=5;
            
			$awalynmakun=$pdf->GetY();
            $pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
            @$pdf->MultiCell(80, $height, $optAkun[$ddtd['noakun']], '0', 'L');
            $akhirynmakun=$pdf->GetY();
            $tinggiynmakun=$akhirynmakun-$awalynmakun;
            $heightakun=$tinggiynmakun;
            $pdf->SetY($akhirynmakun-$tinggiynmakun);
			
			$no+=1;
            
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
			
			if($heightakun>$height){
				$pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+10);
				$pdf->Line($awalxlist+10, $awalylist+5, $awalxlist+10, $awalylist+10);
				$pdf->Line($awalxlist+130, $awalylist+5, $awalxlist+130, $awalylist+10);
				$pdf->Line($awalxlist+160, $awalylist+5, $awalxlist+160, $awalylist+10);
				$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+10);
			}
			
			$pdf->Cell(10,$height,$no,'TRL',0,'C',1);
			@$pdf->Cell(20,$height,$ddtd['noakun'],'TRL',0,'R',1);
			@$awalxlistnmakun=$pdf->GetX();
			@$pdf->MultiCell(80,$height,$nmakun[$ddtd['noakun']],'TRL','J');
			@$pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
			$pdf->Cell(20,$height,$ddtd['noaruskas'],'TRL',0,'C',1);
			$pdf->Cell(30,$height,number_format($dataddb,2),'TRL',0,'R',1);
			$pdf->Cell(30,$height,number_format($datadkr,2),'TRL',1,'R',1);
			
			$pdf->Cell(10,$height,'','RL',0,'C',1);
			@$pdf->Cell(20,$height,'','RL',0,'R',1);
			@$awalxlistnmakun=$pdf->GetX();
			$awalynmakun=$pdf->GetY();
			@$pdf->MultiCell(80,$height,$nmket[$ddtd['keterangan']],'RL','J');
			@$pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
			$pdf->Cell(20,$height,'','RL',0,'C',1);
			$pdf->Cell(30,$height,'','RL',0,'R',1);
			$pdf->Cell(30,$height,'','RL',1,'R',1);
			
			
			if($heightakun>$height){
				$isi=$pdf->Ln();
			}
		
			if($ddtd['keterangan2'] != '' || $ddtd['keterangan2'] != null){
				$awalyket=$pdf->GetY();
				$pdf->SetX(100000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
				$pdf->MultiCell(80, $height,$ddtd['keterangan2'], '0', 'L');
				$akhiryket=$pdf->GetY();
				$tinggiyket=$akhiryket-$awalyket;
				$heightket=$tinggiyket;
				$pdf->SetY($akhiryket-$tinggiyket);
				
				
				$pdf->Cell(10,$heightket,'','BRL',0,'C',1);
				$pdf->Cell(20,$heightket,'','BRL',0,'R',1);
				$awalxlistket=$pdf->GetX();
				$pdf->MultiCell(80,$height,$ddtd['keterangan2'],'BRL','J');
				$pdf->SetXY($awalxlistket+80, $awalyket);
				$pdf->Cell(20,$heightket,'','BRL',0,'C',1);
				$pdf->Cell(30,$heightket,'','BRL',0,'R',1);
				$pdf->Cell(30,$heightket,'','BRL',1,'R',1);
			}
			
			if($pdf->GetY() > 250) {
				$akhirY=$akhirY-20;
				$akhirY=$pdf->GetY()-$akhirY;
				$akhirY=$akhirY+35;
				$pdf->AddPage();
			}
			
			@$totdtdb+=$dataddb;
            @$totdtkr+=$datadkr;
		}
		##########################################
        ############## tutup detail ##############
        ##########################################
        
		if($datahdb <= 0){
			############################### head #################################
			############## buat baris pertama dulu untuk pembanding ##############
			######################################################################
			$height=5;
			$awalynmakun=$pdf->GetY();
			
			$pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
			$pdf->MultiCell(80, $height, $nmakun['1112102'], '0', 'L');
			$akhirynmakun=$pdf->GetY();
			$tinggiynmakun=$akhirynmakun-$awalynmakun;
			$heightakun=$tinggiynmakun;
			$pdf->SetY($akhirynmakun-$tinggiynmakun);
			
			$no+=1;
			
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
			
			if($heightakun>$height){
				$pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+10);
				$pdf->Line($awalxlist+10, $awalylist+5, $awalxlist+10, $awalylist+10);
				$pdf->Line($awalxlist+130, $awalylist+5, $awalxlist+130, $awalylist+10);
				$pdf->Line($awalxlist+160, $awalylist+5, $awalxlist+160, $awalylist+10);
				$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+10);
			}
			
			$pdf->Cell(10,$height,$no,'TRL',0,'C',1);
			$pdf->Cell(20,$height,'1112102','TRL',0,'R',1);
			$awalxlistnmakun=$pdf->GetX();
			$pdf->MultiCell(80,$height,$nmakun['1112102'],'TRL','J');
			$pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
			@$pdf->Cell(20,$height,$dht['noaruskas'],'TRL',0,'C',1);
			@$pdf->Cell(30,$height,number_format($datahdb,2),'TRL',0,'R',1);
			@$pdf->Cell(30,$height,number_format($datahkr,2),'TRL',1,'R',1);
			
			if($heightakun>$height){
				$isi=$pdf->Ln();
			}
			
			########################################
			############## tutup head ##############
			########################################
		}
		
		@$gtotdb=$datahdb+$totdtdb;
        @$gtotkr=$datahkr+$totdtkr;
		
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(130,$height,'Total',1,0,'C',1);
		$pdf->Cell(30,$height,number_format($gtotdb,2),1,0,'R',1);
		$pdf->Cell(30,$height,number_format($gtotkr,2),1,1,'R',1);


		
		$pdf->Ln();


		#################################
		############## ttd ##############
		#################################
		
		$pdf->SetFillColor(220,220,220);
		if($dht['tipetransaksi']=='M'){
			$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dibuatoleh'],1,0,'C',1);
			
			if($tipeLokasiTugas[$dht['unit']]=='HOLDING'){
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diperiksaoleh'],1,0,'C',1);
			}else{
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diketahuioleh'],1,0,'C',1);
			}
			
			$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dstujui_oleh'],1,0,'C',1);
			
			if($tipeLokasiTugas[$dht['unit']]=='HOLDING'){
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dipostingoleh'],1,0,'C',1);
			}else{
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diverifikasioleh'],1,0,'C',1);
			}
			
			$pdf->Ln();
			
			$pdf->SetFillColor(255,255,255);
			for($i=0;$i<4;$i++) {
				$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
				$pdf->Ln();
			}
			
			// if(isset($userId[$dht['userid']])){
				// $pdf->Cell(25/100*$width,$height,$userId[$dht['userid']],'BLR',0,'C',1);
			// }else{
				// $pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
			// }
			
			
			
			if($tipeLokasiTugas[$dht['unit']]=='HOLDING'){
				$pdf->Cell(25/100*$width,$height,'Finance Staff','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'FA Dept Head','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'Direktur','BLR',0,'C',1);
			}else{
				$pdf->Cell(25/100*$width,$height,'Finance','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'KTU','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'Manager','BLR',0,'C',1);
			}
			
			$pdf->Cell(25/100*$width,$height,'Accounting','BLR',0,'C',1);
		}else{
			if($tipeLokasiTugas[$dht['unit']]=='HOLDING'){
				
					$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['dibuatoleh'],1,0,'C',1);
					$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['diperiksaoleh'],1,0,'C',1);
					$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['disetujuioleh'],1,0,'C',1);
					$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['dipostingoleh'],1,0,'C',1);
					$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['diterimaoleh'],1,0,'C',1);
					$pdf->Ln();
					
					$pdf->SetFillColor(255,255,255);
					for($i=0;$i<4;$i++){
						$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
						$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
						$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
						$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
						$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
						$pdf->Ln();
					}
					$pdf->Cell(20/100*$width,$height,'Finance Staff','BLR',0,'C',1);
					$pdf->Cell(20/100*$width,$height,'FA Dept Head','BLR',0,'C',1);
					$pdf->Cell(20/100*$width,$height,'Direktur','BLR',0,'C',1);
					$pdf->Cell(20/100*$width,$height,'Accounting','BLR',0,'C',1);
					$pdf->Cell(20/100*$width,$height,'','BLR',0,'C',1);
				
			}else{
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dibuatoleh'],1,0,'C',1);
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diperiksaoleh'],1,0,'C',1);
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dstujui_oleh'],1,0,'C',1);
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diterimaoleh'],1,0,'C',1);
				$pdf->Ln();
				
				$pdf->SetFillColor(255,255,255);
				for($i=0;$i<4;$i++) {
					$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
					$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
					$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
					$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
					$pdf->Ln();
				}
				
				// if(isset($userId[$dht['userid']])){
					// $pdf->Cell(25/100*$width,$height,$userId[$dht['userid']],'BLR',0,'C',1);
				// }else{
					// $pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
				// }
				$pdf->Cell(25/100*$width,$height,'Finance','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'KTU','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'Manager','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
			}
		}
		# Print Out
		if($urlefil=='0'){
			$pdf->Output();
		}else{
			$pdf->Output($urlefil);
		}
        break;
		
	case 'excel':
        break;
    case'html':
        $theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $men='menu.css';
          $gen='generic.css';
        }else if($theme=='red'){
          $men='menuRed.css';
          $gen='genericRed.css';  
        }else{
          $men='menuGray.css';
          $gen='genericGray.css';  
        }  
        $tab="<link rel=stylesheet type=text/css href=style/".$gen.">";
        $tab.="<fieldset><legend>".$title."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$res[0]['kode']."/".$param['notransaksi']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['cgttu']."</td><td> :</td><td> ".$resH[0]['cgttu']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['terbilang']."</td><td> :</td><td> ".terbilang($resH[0]['jumlah'],2).
            ' rupiah'."</td></tr>";
        if($resH[0]['hutangunit']==1){
            $tab.="<tr><td>".$_SESSION['lang']['hutangunit']."</td><td> :</td><td> ".'Unit payable Account '.$resH[0]['pemilikhutang'].' : '.$namaakunhutang[$resH[0]['noakunhutang']]."</td></tr>";            
        }
        $tab.="</tbody></table><br />";

            $tab.="<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><thead><tr class=rowheader>";



            foreach($colshtml as $column) {
                $tab.="<td>".$_SESSION['lang'][$column]."</td>";
            }
            $tab.="</tr></thead><tbody class=rowcontent>";




        // nyusun ulang nomor setelah disort by debet. dz
            $nyomor=0;
            foreach($data as $key=>$row) {    
                $nyomor+=1;
                $tab.="<tr>";
                foreach($row as $key=>$cont) {
                    if($key=='nomor'){
                        $tab.="<td>".$nyomor."</td>";
                    }else{
                        if($key=='debet' or $key=='kredit') {
                            $tab.="<td align=right>".number_format($cont,0)."</td>";
                        } else {
                            $tab.="<td>".$cont."</td>";
                        }                    
                    }
                }
                $tab.="</tr>";
            }
        $tab.="<tr><td colspan=4 align=center>Total</td><td align=right>".number_format($totalDebet,0)."</td>"
                . "<td align=right>".number_format($totalKredit,0)."</td>"
                . "<td></td></tr>";
             $tab.="</tbody></table> <br />";

        echo $tab;

    break;
    default:
    break;
}
?>