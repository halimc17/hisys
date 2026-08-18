<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];

//=============
if (!class_exists('PDFPO')){
	//create Header
	class PDFPO extends FPDF {
		function Header() {
			global $conn;
			global $dbname;
			global $userid;
			global $posted;
			global $tanggal;
			global $norek_sup;
			global $cpsn;
			global $npwp_sup;
			global $nm_kary;
			global $nm_pt;
			global $namapt;
			global $kodepos;
			global $nmSupplier;
			global $namasupplier;
			global $almtSupplier;
			global $tlpSupplier;
			global $faxSupplier;
			global $nopo;
			global $tglPo;
			global $tglPobaru;
			global $kdBank;
			global $an;
			global $arrlp;
			global $lokalpusat;
			global $nmlokalpusat;
			global $karyidpurchaser;
				global $optNmkry;
				global $kotasup;
				global $owlPDO;
					
			
		}
		
		function Footer() {
			$this->SetY(-15);
			$this->SetFont('Arial','I',8);
			$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
		}
	}
}

$pdf=new PDFPO('P','mm','A4');
$height=4;
$pdf->AddPage();

##header di pindah kebawah

		$arrlp=array("0"=>" ","1"=>" LOCAL");
		$optNmkry=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

		$str="select kodeorg,kodesupplier,purchaser,nopo,tanggal,lokalpusat,syaratbayar,alamatsup,npwpsup,rekening from ".$dbname.".log_poht  where nopo='".$_GET['column']."'";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$bar=$res->fetch();
		$idalamatsup = $bar->alamatsup;
		$npwpsup = $bar->npwpsup;
		$rekeningsup = $bar->rekening;

		//ambil data T.O.P
		$sSyp="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar where kode='".$bar->syaratbayar."'";
		$qSyp=$owlPDO->query($sSyp) or die(print " Gagal: ".PDOException::getMessage());
		$qSyp->setFetchMode(PDO::FETCH_OBJ);
		$rSyp=$qSyp->fetch();
		$top=@$rSyp->keterangan;

		//ambil data pt
		if($bar->kodeorg=='')
		{
			   $bar->kodeorg=$_SESSION['org']['kodeorganisasi']; 
		}
		$str1="select namaorganisasi,alamat,wilayahkota,telepon,kodepos from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while($bar1=$res1->fetch()){
			$namapt=$bar1->namaorganisasi;
			$alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
			$telp=$bar1->telepon;
			$kodepos=$bar1->kodepos;				 
		} 

		$sNpwp="select npwp,alamatnpwp,alamatdomisili from ".$dbname.".setup_org_npwp where kodeorg='".$bar->kodeorg."'";
		$qNpwp=$owlPDO->query($sNpwp) or die(print " Gagal: ".PDOException::getMessage());
		$qNpwp->setFetchMode(PDO::FETCH_ASSOC);
		$rNpwp=$qNpwp->fetch();
		$npwppt= $rNpwp['npwp'];
		$alamatpt = $rNpwp['alamatnpwp'];
		
		//ambil data supplier
		$sql="select * from ".$dbname.".log_5supplier where supplierid='".$bar->kodesupplier."'"; //echo $sql;
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_OBJ);
		$res=$query->fetch();


		$karyidpurchaser = $bar->purchaser;
		
		
		$strx="select * from ".$dbname.".log_5supalamat where id_alamat='".$idalamatsup."'";
		$resx=fetchData($strx);
		
		// ambil nama purchaser
		$sql2="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->purchaser."'";
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		$res2=$query2->fetch();
		
		//data supplier
		@$namasupplier=$res->namasupplier;
		@$alamatsupplier=$resx[0]['alamat'];
		@$npwp_sup=$npwpsup;
        @$kotasup=$resx[0]['kota'];
		@$norek_sup=$rekeningsup;
		@$kdBank=$res->bank;       
		@$an=$res->an;
		@$almtSupplier=$resx[0]['alamat'];
		@$tlpSupplier=$resx[0]['telepon'];
		@$faxSupplier=$resx[0]['fax'];
        @$cpsn=$resx[0]['kontakperson'];

		//nama purchaser   
		$nm_kary=$res2->namakaryawan;

		//data POHT(PO Header)
		$nopo=$bar->nopo;
        $lokalpusat=$bar->lokalpusat;
        $nmlokalpusat=$arrlp[$lokalpusat];
		$tglPo=$bar->tanggal;
		$exnopo = explode('/',$nopo);
		$exnopo2 = explode('-',$exnopo[3]);
		$jnsnopo = $exnopo2[0];
		
		if($jnsnopo==''){
			$str="select kodebarang from ".$dbname.".log_podt where nopo='".$nopo."' limit 1";
			$res=fetchData($str);
			if(substr($res[0]['kodebarang'],0,1)=='3'){
				$jnsnopo = 'PO';
			}else{
				$jnsnopo = 'SO';
			}
		}

		$tgl=explode("-",$tglPo);
		$bulan=$tgl[1];
		switch ($bulan) {
			case '01':$bulan='Januari';break;
			case '02':$bulan='Februari';break;
			case '03':$bulan='Maret';break;
			case '04':$bulan='April';break;
			case '05':$bulan='Mei';break;
			case '06':$bulan='Juni';break;
			case '07':$bulan='Juli';break;
			case '08':$bulan='Agustus';break;
			case '09':$bulan='September';break;
			case '10':$bulan='Oktober';break;
			case '11':$bulan='November';break;
			case '12':$bulan='Desember';break;
			default:
				break;
		}
		$tanggal=$tgl[2];
		$tahun=$tgl[0];
		$tglPobaru=$tanggal.' '.$bulan.' '.$tahun;

		
		#= ambil unit untuk alamat
		
		#= 
		$explnopo=explode('/',$nopo);
		
		$unitkop=$explnopo[4];
		if($unitkop==''){
			$unitkop=$bar->kodeorg;
		}
		
		
		//header PDF
		$arrHead = setheadreport('',$unitkop);
		$path=$arrHead['logo'];

        $pdf->SetMargins(15,10,0);	
		$pdf->SetFont('Arial','B',9);
		$pdf->SetFillColor(255,255,255);
		$pdf->SetX(55);
		$pdf->Cell(100,5,strtoupper(($jnsnopo=='PO'?'Purchase Order':'Service Order')),0,1,'C');	 
		$pdf->Cell(180,5,strtoupper("(".$jnsnopo.")"),0,1,'C');
		$pdf->Ln(15);	
		$pdf->Image($path,15,22,0,15);
		 $pdf->SetX(28); 
		$pdf->Cell(100,5,'',0,1,'L');
		
		$pdf->SetFont('Arial','',7);
		$pdf->Cell(100,5,$arrHead['nama'],0,0,'L');
		$pdf->Cell(20,5,'PO. NO.',0,0,'L');
		$pdf->Cell(30,5,": ".$nopo,0,1,'L');
		$pdf->SetFont('Arial','',7);	  		 
		$yawal=$pdf->GetY();		
		$pdf->MultiCell(60,5,$arrHead['alamat'],0,1,'L');
		$pdf->Cell(60,5,$arrHead['telepon'],0,1,'L');
		$pdf->SetXY(115,$yawal);
		$pdf->Cell(20,5,"Date ",0,0,'L');	
		$pdf->Cell(30,5,": ".$tglPobaru,0,1,'L');
		$pdf->SetX(115);	
		$pdf->Cell(20,5,'T.O.P',0,0,'L');
		$pdf->Cell(30,5,": ".$top,0,1,'L');




		$pdf->Ln(10);
		$currY = $pdf->GetY();
		$pdf->Line(15,$currY,205,$currY);	
		$pdf->SetFont('Arial','',7); 	
		$pdf->Cell(60,5,'Suplier/Sender :',0,1,'L');
        $pdf->Cell(30,5,$namasupplier,0,1,'L'); 
        $pdf->SetFont('Arial','',7); 		
		$pdf->MultiCell(60,5,$alamatsupplier,0,'L');
		$pdf->Cell(60,5,$kotasup,0,1,'L');
		$pdf->Cell(10,5,'NPWP',0,0,'L');
		$pdf->Cell(30,5,': '.$npwpsup,0,1,'L');
		$pdf->Cell(10,5,'Telp/Fax',0,0,'L');
		$pdf->Cell(30,5,': '.trim($tlpSupplier).' / '.trim($faxSupplier),0,1,'L');
		$pdf->Cell(10,5,'Attn',0,0,'L');
		$pdf->Cell(30,5,': '.$cpsn,0,1,'L');	

		$pdf->SetXY(115,$currY);
        $pdf->Cell(30,5,'Standard Tax Invoice, under the name of : ',0,0,'L');
        $pdf->SetFont('Arial','B',9);
        $ar=$pdf->GetY();
		$pdf->SetY($ar+5);
		$pdf->SetFont('Arial','',7);
        $pdf->SetX(115);
        $pdf->Cell(30,5,$namapt,0,1,'L'); 
        $pdf->SetFont('Arial','',7);
        $pdf->SetX(115); 		
		$pdf->MultiCell(60,5,$alamatpt.', '.$kodepos,0,'L');	
		$pdf->SetFont('Arial','B',7);
		$pdf->SetX(115); 			
		$pdf->Cell(60,5,"NPWP: ".$npwppt,0,1,'L');


## tutup header




$noPp=  makeOption($dbname, 'log_podt', 'nopo,nopp');

$pdf->Ln(10);
$pdf->SetFont('Arial','',7);	
$pdf->SetFillColor(220,220,220);
$pdf->Cell(190,5,'Thank you for not providing any types of gravity to employees of '.$namapt,'TLR',1,'C',0);
$pdf->Cell(190,5,'Failure in doing this will result in the termination of business contract with '.$namapt,'LR',1,'C',0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(8,5,'No',1,0,'L',1);
$pdf->Cell(147,5,'SPECIFICATION',1,0,'C',1);	
$pdf->Cell(35,5,'QUANTITY',1,1,'C',1);
		
// $pdf->Cell(37,5,strtoupper($_SESSION['lang']['unit']),1,0,'C',1);	
// $pdf->Cell(35,5,strtoupper($_SESSION['lang']['amount']),1,1,'C',1);	
//$pdf->Cell(191,50,'',1,1,'C',1);	
$pdf->SetFillColor(255,255,255);
$pdf->SetFont('Arial','',7);

//ambil data PODT(PO Detail)
$no=0;$i=0;
$str="select a.*,b.kodesupplier,b.subtotal,b.diskonpersen,b.tanggal,b.nilaidiskon,b.ppn,b.nilaipo,b.tanggalkirim,b.lokasipengiriman,b.uraian,b.matauang from ".$dbname.".log_podt a inner join ".$dbname.".log_poht b on a.nopo=b.nopo  where a.nopo='".$_GET['column']."'";
//echo $str;exit();
$re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$re->setFetchMode(PDO::FETCH_OBJ);
while($bar=$re->fetch()){	
    $no+=1;
	$kodebarang=$bar->kodebarang;
	$jumlah=$bar->jumlahpesan;
	if($jumlah==0){
		$jumlah=$bar->jmlhstlhclose;
	}
	$harga_sat=$bar->hargasbldiskon;
	$total=$jumlah*$harga_sat;
	$unit=substr($bar->nopp,15,4);
	$namabarang='';

//ambil data spesifikasi
$strv="select b.spesifikasi from  ".$dbname.".log_5photobarang b  where b.kodebarang='".$bar->kodebarang."'"; //echo $strv;exit();	
$resv=$owlPDO->query($strv) or die(print " Gagal: ".PDOException::getMessage());
$resv->setFetchMode(PDO::FETCH_OBJ);
$barv=$resv->fetch();
	if(!empty($barv->spesifikasi)) {
		$spek=$barv->spesifikasi;					
	} else {
		$spek="";
	}

//ambil data barang
$nopp=substr($bar->nopp,0,3);
$sSat="select satuan,namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
$qSat=$owlPDO->query($sSat) or die(print " Gagal: ".PDOException::getMessage());
$qSat->setFetchMode(PDO::FETCH_ASSOC);
$rSat=$qSat->fetch();
	$satuan=$rSat['satuan'];
	$namabarang=$rSat['namabarang'];
    $i++;

    if($no!=1) {
        $pdf->SetY($akhirY);
    }
	$posisiY=$pdf->GetY();
	$pdf->Cell(8,5,$no,'L',0,'L',0);
	$pdf->SetX($pdf->GetX());
	$pdf->Cell(15,5,$bar->kodebarang,'L',0,'C',0);
	
        
        if($spek=='' && $bar->catatan=='')
        {
            $pdf->MultiCell(132,5,$namabarang,0,'J',0);
        }
        else if ($spek!='' && $bar->catatan=='')
        {
            $pdf->MultiCell(132,5,$namabarang."\n".$spek,0,'J',0);
        }
        else if ($spek=='' && $bar->catatan!='')
        {
            $pdf->MultiCell(132,5,$namabarang."\n".$bar->catatan,0,'J',0);
        }
        else
        {
            $pdf->MultiCell(132,5,$namabarang."\n".$spek."\n".$bar->catatan,0,'J',0);
        }

        $akhirY=$pdf->GetY();

        //atur garis samping 
        if ($spek=='' && $bar->catatan=='')
        {	
        	$height=5;
        	$pdf->SetX(1000);
        	$awalygaris=$pdf->GetY();
            $pdf->MultiCell(132,5,$namabarang,0,'J',0);
            $akhirygaris=$pdf->GetY();
            $tinggiygaris=$akhirygaris-$awalygaris;
			$heightgaris=$tinggiygaris;
			$pdf->SetY($akhirygaris-($tinggiygaris*2));
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
				
			if($heightgaris>$height){
			$pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+8, $awalylist+5, $awalxlist+8, $awalylist+$heightgaris);
			// $pdf->Line($awalxlist+83, $awalylist+5, $awalxlist+83, $awalylist+$heightgaris);
			// $pdf->Line($awalxlist+118, $awalylist+5, $awalxlist+118, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+155, $awalylist+5, $awalxlist+155, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+$heightgaris);
			}
        }else if ($spek!='' && $bar->catatan=='')
        {	
        	$height=5;
        	$pdf->SetX(1000);
        	$awalygaris=$pdf->GetY();
            $pdf->MultiCell(132,5,$namabarang."\n".$spek,0,'J',0);
            $akhirygaris=$pdf->GetY();
            $tinggiygaris=$akhirygaris-$awalygaris;
			$heightgaris=$tinggiygaris;
			$pdf->SetY($akhirygaris-($tinggiygaris*2));
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
				
			if($heightgaris>$height){
			$pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+8, $awalylist+5, $awalxlist+8, $awalylist+$heightgaris);
			// $pdf->Line($awalxlist+83, $awalylist+5, $awalxlist+83, $awalylist+$heightgaris);
			// $pdf->Line($awalxlist+118, $awalylist+5, $awalxlist+118, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+155, $awalylist+5, $awalxlist+155, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+$heightgaris);
			}
        }
         else if ($spek!='' && $bar->catatan=='')
        {	
        	$height=5;
        	$pdf->SetX(1000);
        	$awalygaris=$pdf->GetY();
            $pdf->MultiCell(132,5,$namabarang."\n".$spek,0,'J',0);
            $akhirygaris=$pdf->GetY();
            $tinggiygaris=$akhirygaris-$awalygaris;
			$heightgaris=$tinggiygaris;
			$pdf->SetY($akhirygaris-($tinggiygaris*2));
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
				
			if($heightgaris>$height){
			$pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+8, $awalylist+5, $awalxlist+8, $awalylist+$heightgaris);
			// $pdf->Line($awalxlist+83, $awalylist+5, $awalxlist+83, $awalylist+$heightgaris);
			// $pdf->Line($awalxlist+118, $awalylist+5, $awalxlist+118, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+155, $awalylist+5, $awalxlist+155, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+$heightgaris);
			}
        }
         else if ($spek=='' && $bar->catatan!='')
        {
        	$height=5;
        	$pdf->SetX(1000);
        	$awalygaris=$pdf->GetY();
            $pdf->MultiCell(132,5,$namabarang."\n".$bar->catatan,0,'J',0);
            $akhirygaris=$pdf->GetY();
            $tinggiygaris=$akhirygaris-$awalygaris;
			$heightgaris=$tinggiygaris;
			$pdf->SetY($akhirygaris-($tinggiygaris*2));
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
			if($heightgaris>$height){
			$pdf->Line($awalxlist, $awalylist+5 , $awalxlist, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+8, $awalylist+5, $awalxlist+8, $awalylist+$heightgaris);
			// $pdf->Line($awalxlist+83, $awalylist+5, $awalxlist+83, $awalylist+$heightgaris);
			// $pdf->Line($awalxlist+118, $awalylist+5, $awalxlist+118, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+155, $awalylist+5, $awalxlist+155, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+$heightgaris);
			}
        }
        else if ($spek!=='' && $bar->catatan!='')
        {
        	$height=5;
        	$pdf->SetX(1000);
        	$awalygaris=$pdf->GetY();
            $pdf->MultiCell(132,5,$namabarang."\n".$spek."\n".$bar->catatan,0,'J',0);
            $akhirygaris=$pdf->GetY();
            $tinggiygaris=$akhirygaris-$awalygaris;
			$heightgaris=$tinggiygaris;
			$pdf->SetY($akhirygaris-($tinggiygaris*2));
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
				
			if($heightgaris>$height){
			$pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+8, $awalylist+5, $awalxlist+8, $awalylist+$heightgaris);
			// $pdf->Line($awalxlist+83, $awalylist+5, $awalxlist+83, $awalylist+$heightgaris);
			// $pdf->Line($awalxlist+118, $awalylist+5, $awalxlist+118, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+155, $awalylist+5, $awalxlist+155, $awalylist+$heightgaris);
			$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+$heightgaris);
			}
        }

	$pdf->SetY($posisiY);
	$pdf->SetX($pdf->GetX()+155);

	$pdf->Cell(15,5,number_format($jumlah,2,'.',','),'L',0,'R',0);
    $pdf->Cell(20,5,$bar->satuan,'R',1,'C',0);
	// $pdf->Cell(8,5,'  '.$bar->matauang,'L',0,'R',0);
	// $pdf->Cell(29,5,number_format($harga_sat,2,'.',','),0,0,'R',0);
	// $pdf->Cell(9,5,'  '.$bar->matauang,'L',0,'R',0);
	// $pdf->Cell(26,5,number_format($total,2,'.',','),'R',1,'R',0);	

	if($pdf->GetY() > 225) {
	// if($pdf->GetY() > 250) {
		$i=0;
		$pdf->Line(15,$akhirY,205,$akhirY);
		$akhirY=$akhirY-20;
		$akhirY=$pdf->GetY()-$akhirY;
		//$akhirY=$akhirY+70;
		$pdf->AddPage();
		$pdf->Line(15,$akhirY,205,$akhirY);
    }
}

$pdf->Line(15,$akhirY,205,$akhirY);
//$akhirSubtot=$pdf->GetY();
//$pdf->SetY($akhirY);
$slopoht="select * from ".$dbname.".log_poht where nopo='".$_GET['column']."'";
	$qlopoht=$owlPDO->query($slopoht) or die(print " Gagal: ".PDOException::getMessage());
	$qlopoht->setFetchMode(PDO::FETCH_OBJ);
	$rlopoht=$qlopoht->fetch();
		$nodph=$rlopoht->nodph;
		$mtuang=$rlopoht->matauang;	
		$sb_tot=$rlopoht->subtotal;
		$nil_diskon=$rlopoht->nilaidiskon;
		$npbbkb=$rlopoht->pbbkb;
		$npph=$rlopoht->pph;
		$nppn=$rlopoht->ppn;
		$pppn=$rlopoht->persenppn;
		$stat_release=$rlopoht->stat_release ;
		$user_release=$rlopoht->useridreleasae;
		$addcost=$rlopoht->addcost;
		$noref=$rlopoht->norefrensi;
		$gr_total=(($sb_tot-$nil_diskon)+$npbbkb)+$nppn-$npph+$addcost;

$srph="select nomor from ".$dbname.".log_permintaanhargadt where norph='".$nodph."'";
$qrph=$owlPDO->query($srph) or die(print " Gagal: ".PDOException::getMessage());
$qrph->setFetchMode(PDO::FETCH_OBJ);
$rrph=$qrph->fetch();
$norph=$rrph->nomor;

$sfranco="select * from ".$dbname.".setup_franco where id_franco='".$rlopoht->idFranco."'";
$qfranco=$owlPDO->query($sfranco) or die(print " Gagal: ".PDOException::getMessage());
$qfranco->setFetchMode(PDO::FETCH_OBJ);
$rfranco=$qfranco->fetch();
$franco=@$rfranco->franco_name;
$kontak=@$rfranco->contact;
$hp=@$rfranco->handphone;
$alamat=@$rfranco->alamat;
$deliveryto=$alamat.", ".$kontak."/".$hp;
$logistic=$kontak."/".$hp;
$keterangan= $kontak."/".$hp;

$sSyp="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar where kode='".$rlopoht->syaratbayar."'";
$qSyp=$owlPDO->query($sSyp) or die(print " Gagal: ".PDOException::getMessage());
$qSyp->setFetchMode(PDO::FETCH_OBJ);
$rSyp=$qSyp->fetch();

$sref="select nopp from ".$dbname.".log_podt where nopo='".$_GET['column']."'";
$rref=$owlPDO->query($sref) or die(print " Gagal: ".PDOException::getMessage());
$rref->setFetchMode(PDO::FETCH_OBJ);
$countpp = 0;
$tempref='';
while($bref=$rref->fetch())
{	
	$ref=$bref->nopp;
	if($countpp==0){
		$refspb=$ref;
	}else{
		if($tempref!=$ref){
			$refspb=$refspb.', '.$ref;
		}
	}
	$tempref=$ref;
	$countpp++;
}

$sref="select nosj from ".$dbname.".log_po_sj where nopo='".$_GET['column']."'";
$rref=$owlPDO->query($sref) or die(print " Gagal: ".PDOException::getMessage());
$rref->setFetchMode(PDO::FETCH_ASSOC);
$countsj = 0;
while($bref=$rref->fetch())
{	
	if($countsj==0){
		$countsj+=1;
		$noref=$bref['nosj'];
	}
	else
	{
		$noref.=', '.$bref['nosj'];
	}
}

$sdeliv="select nama from ".$dbname.".log_5delivtime where kode='".$rlopoht->deliverytime."'";
$qdeliv=$owlPDO->query($sdeliv) or die(print " Gagal: ".PDOException::getMessage());
$qdeliv->setFetchMode(PDO::FETCH_OBJ);
$rdeliv=$qdeliv->fetch();
$delivtime=@$rdeliv->nama;


## BEGIN KETERANGAN ##

$pdf->SetY($akhirY+2);
$pageselesaidetail=$akhirY+2;
$height=4;
$awalxterm=$pdf->Getx();

//if($pdf->GetY() > (250-30)) 
$penambahy=2;
if($pageselesaidetail>=163)	
{
	$currY=$currY-20;
	$currY=$pdf->GetY()-$currY;
	$currY=$currY+100;
	$pdf->AddPage();
	$akhirYpagebaru=$pdf->GetY();
	$akhirY=$akhirYpagebaru;
	$penambahy=0;
}


//$a=$pdf->GetY();echo $a;

$pdf->Cell(190,4,strtoupper('Term & Condition'),'TLR',0,'L'); 
$pdf->Ln();
$pdf->Cell(5,4,'1. ','L',0,'L'); 
$pdf->Cell(25,4,'Delivery Type',0,0,'L'); 
$pdf->Cell(160,4,": ".$franco,'R',1,'L');			
$pdf->Cell(5,4,'2. ','L',0,'L'); 
$pdf->Cell(25,4,'Term of Payment',0,0,'L'); 
$pdf->Cell(160,4,": ".@$rSyp->keterangan,'R',1,'L');
$pdf->Cell(5,4,'3.','L',0,'L'); 
$pdf->Cell(25,4,'Price',0,0,'L'); 
$pdf->Cell(160,4,": ".$mtuang,'R',1,'L');
$pdf->Cell(5,4,'4.','L',0,'L'); 
$pdf->Cell(25,4,'Delivery Time',0,0,'L'); 
$pdf->Cell(160,4,": ".$delivtime,'R',1,'L');
$pdf->Cell(5,4,'5.','L',0,'L'); 
$pdf->Cell(25,4,'Norefrensi',0,0,'L'); 
$pdf->Cell(160,4,": ".$noref,'R',1,'L');

$awalygaris=$pdf->GetY();
$pdf->SetX(10000);
$pdf->Cell(5,4,'6.',0,0,'L'); 
$pdf->Cell(25,4,'Ref. SPB',0,0,'L'); 
$pdf->Cell(2,4,": ",0,0,'L'); 
$pdf->MultiCell(158,4,$refspb,'R',1,'L');
$akhirygaris=$pdf->GetY();
$tinggiygaris=$akhirygaris-$awalygaris;
$heightgaris=$tinggiygaris;
$pdf->SetY($akhirygaris-$tinggiygaris);
$awalxlist=$pdf->GetX();
$awalylist=$pdf->GetY();
if($heightgaris>$height){
	$pdf->Line($awalxlist, $awalylist+4, $awalxlist, $awalylist+$heightgaris);
}

$pdf->Cell(5,4,'6.','L',0,'L'); 
$pdf->Cell(25,4,"Ref. ".($jnsnopo=='PO'?'PR':'SR'),0,0,'L'); 
$pdf->Cell(2,4,": ",0,0,'L'); 
$pdf->MultiCell(158,4,$refspb,'R',1,'L');
$pdf->Cell(5,4,'7.','L',0,'L'); 
$pdf->Cell(25,4,"Ref Quotation",0,0,'L'); 
$pdf->Cell(160,4,": ".$norph,'R',1,'L');

$awalygaris=$pdf->GetY();
// $pdf->SetX(1000);
// $pdf->MultiCell(125,4,'Late penalty in charge : Seller shall pay to the buyer 0,1 % of Total Transaction price for every single/one
// day delay of the Goods, provided that the total  penalty is not more than 5% of the transaction.','R',1,'L');
// $pdf->Cell(5,4,'7.',0,0,'L');
// $akhirygaris=$pdf->GetY();
// $tinggiygaris=$akhirygaris-$awalygaris;
// $heightgaris=$tinggiygaris;
// $pdf->SetY($akhirygaris-$tinggiygaris);
// $awalxlist=$pdf->GetX();
// $awalylist=$pdf->GetY();
// if($heightgaris>$height){
	// $pdf->Line($awalxlist, $awalylist, $awalxlist, $awalylist+$heightgaris);
// }

// $pdf->SetX(20);
// $pdf->MultiCell(125,4,'Late penalty in charge : Seller shall pay to the buyer 0,1 % of Total Transaction price for every single/one
// day delay of the Goods, provided that the total  penalty is not more than 5% of the transaction.','R',1,'L');
// $pdf->Cell(5,4,'7.',0,0,'L');  


// $awalygaris=$pdf->GetY();
// $pdf->SetX(1000);
// $pdf->MultiCell(125,4,'Insofar it is not contrary with the provisions here in, the Terms and Conditions stipulated in Offering
// Letter Quotation.............Month/date/year, is prevail and binding the Seller and Buyer.','R',1,'L');
// $akhirygaris=$pdf->GetY();
// $tinggiygaris=$akhirygaris-$awalygaris;
// $heightgaris=$tinggiygaris;
// $pdf->SetY($akhirygaris-$tinggiygaris);
// $awalxlist=$pdf->GetX();
// $awalylist=$pdf->GetY();
// if($heightgaris>$height){
	// $pdf->Line($awalxlist, $awalylist, $awalxlist, $awalylist+$heightgaris);
// }


// $pdf->SetX(20);
// $pdf->MultiCell(125,4,'Insofar it is not contrary with the provisions here in, the Terms and Conditions stipulated in Offering
	// Letter Quotation.............Month/date/year, is prevail and binding the Seller and Buyer.','R',1,'L');
$pdf->Cell(5,4,'8.','L',0,'L'); 
$pdf->MultiCell(185,4,'Kindly liase with logistic Officer '.$logistic,'R',1,'L');

$awalygaris=$pdf->GetY();
//$pdf->SetX(10000);
$pdf->Cell(5,4,'9.','L',0,'L'); 
$pdf->Cell(25,4,'Delivery To',0,0,'L'); 
$pdf->Cell(2,4,": ",0,0,'L');
$pdf->MultiCell(158,4,$deliveryto,'R',1,'L');

$akhirygaris=$pdf->GetY();
$tinggiygaris=$akhirygaris-$awalygaris;
$heightgaris=$tinggiygaris;
$pdf->SetY($akhirygaris-$tinggiygaris);
$awalxlist=$pdf->GetX();
$awalylist=$pdf->GetY();
			
if($heightgaris>$height){
	$pdf->Line($awalxlist, $awalylist+4, $awalxlist, $awalylist+$heightgaris);
}

$pdf->Cell(5,4,'9.','L',0,'L'); 
$pdf->Cell(25,4,'Delivery To',0,0,'L'); 
$pdf->Cell(2,4,": ",0,0,'L');
$pdf->MultiCell(158,4,$deliveryto,'R',1,'L');


$qp="select * from ".$dbname.".`log_poht` where `nopo`='".$column."'"; 
$qyr=fetchData($qp);
$qPo=$owlPDO->query($qp) or die(print " Gagal: ".PDOException::getMessage());
$qPo->setFetchMode(PDO::FETCH_ASSOC);
$rPo=$qPo->fetch();

$keterangan = $rPo['uraian'];
$waktucetak = ($rPo['waktucetak']=='0000-00-00 00:00:00' ? '' : tglnmblnsec($rPo['waktucetak'],'E',''));

$xyangdipake=$pdf->GetX();
$pdf->Cell(30,4,'Keterangan','L',0,'L'); 
$awalymahe=$pdf->GetY();
$pdf->Cell(2,4,": ",0,0,'L');
$pdf->MultiCell(158,4,$keterangan,'BR',1,'L');
$akhirymahe=$pdf->GetY();
$pdf->Line($xyangdipake,$awalymahe,$xyangdipake,$akhirymahe);

$currY = $pdf->GetY();
$pdf->Line(15,$currY,47,$currY);


## END KETERANGAN ##


// ## BEGIN GRANDTOTAL ##
// // $pdf->SetY($akhirY+2);
// $pdf->SetY($akhirY+$penambahy);
// $pdf->SetX($awalxterm+131);
// $pdf->Cell(27,5,$_SESSION['lang']['subtotal'],'TL',0,'L',1);	
// $pdf->Cell(7,5,''.$mtuang,'T',0,'L',0);
// $pdf->Cell(25,5,number_format($rlopoht->subtotal,2,'.',','),'TR',1,'R',1);

// $pdf->SetY($pdf->GetY());
// $pdf->SetX($pdf->GetX()+131);
// $pdf->Cell(27,5,'Diskon ('.$rlopoht->diskonpersen.'%)','L',0,'L',1);	
// $pdf->Cell(7,5,''.$mtuang,0,0,'L',0);
// $pdf->Cell(25,5,number_format($rlopoht->nilaidiskon,2,'.',','),'RB',1,'R',1);
// $garisy=$pdf->GetY();
// // $pdf->Line(180,$garisy,205,$garisy);

// $pdf->SetY($pdf->GetY());
// $pdf->SetX($pdf->GetX()+131);
// // $pdf->Cell(27,5,'','L',0,'L',1);	
// // $pdf->Cell(7,5,'',0,0,'L',0);
// // $pdf->Cell(25,5,'','R',1,'R',1);

// $pdf->SetY($pdf->GetY());
// $pdf->SetX($pdf->GetX()+131);
// $pdf->Cell(27,5,$_SESSION['lang']['subtotal'],'L',0,'L',1);
// $pdf->Cell(7,5,''.$mtuang,0,0,'L',0);	
// $pdf->Cell(25,5,number_format(($rlopoht->subtotal-$rlopoht->nilaidiskon),2,'.',','),'R',1,'R',1);

// $pdf->SetY($pdf->GetY());
// $pdf->SetX($pdf->GetX()+131);
// $pdf->Cell(27,5,'PBBKB','L',0,'L',1);	
// $pdf->Cell(7,5,''.$mtuang,0,0,'L',0);
// $pdf->Cell(25,5,number_format($rlopoht->pbbkb,2,'.',','),'R',1,'R',1);

// $pdf->SetY($pdf->GetY());
// $pdf->SetX($pdf->GetX()+131);
// $pdf->Cell(27,5,($pppn<=0?"PPn":"PPn (".$pppn."%)"),'L',0,'L',1);	
// $pdf->Cell(7,5,''.$mtuang,0,0,'L',0);
// $pdf->Cell(25,5,number_format($rlopoht->ppn,2,'.',','),'R',1,'R',1);

// // $pdf->SetY($pdf->GetY());
// // $pdf->SetX($pdf->GetX()+131);
// // $pdf->Cell(27,5,'PPh','L',0,'L',1);	
// // $pdf->Cell(7,5,''.$mtuang,0,0,'L',0);
// // $pdf->Cell(25,5,number_format($rlopoht->pph,2,'.',','),'R',1,'R',1);

// $pdf->SetY($pdf->GetY());
// $pdf->SetX($pdf->GetX()+131);
// $pdf->Cell(27,5,'Add Cost','L',0,'L',1);	
// $pdf->Cell(7,5,''.$mtuang,0,0,'L',0);
// $pdf->Cell(25,5,number_format($addcost,2,'.',','),'R',1,'R',1);
// $garisy=$pdf->GetY();
// $pdf->Line(180,$garisy,205,$garisy);

// $pdf->SetFont('Arial','B',7);
// $pdf->SetY($pdf->GetY());
// $pdf->SetX($pdf->GetX()+131);
// $pdf->Cell(27,5,'','L',0,'L',1);
// $pdf->Cell(7,5,'','',0,'L',0);
// $pdf->Cell(25,5,'','R',1,'R',1);

// $pdf->SetFont('Arial','B',7);
// $pdf->SetY($pdf->GetY());
// $pdf->SetX($pdf->GetX()+131);
// $pdf->Cell(27,5,$_SESSION['lang']['grnd_total'],'L',0,'L',1);
// $pdf->Cell(7,5,''.$mtuang,0,0,'L',0);
// $garisy=$pdf->GetY();
// $pdf->Line(180,$garisy,205,$garisy);
// $pdf->Cell(25,5,number_format($gr_total,2,'.',','),'R',1,'R',1);
// $garisx=$pdf->GetX();
// $pdf->Line(180,$garisy+5,205,$garisy+5);
// $pdf->Line(146,$currY,205,$currY);
// $pdf->Line($garisx+131,$garisy+5,$garisx+131,$currY);
// $pdf->Line($garisx+190,$garisy+5,$garisx+190,$currY);

$pdf->Ln();
$pdf->SetFont('Arial','',8);
$ko=0;

// if($pdf->GetY() > (250-30)) 
// {
	// $currY=$currY-20;
	// $currY=$pdf->GetY()-$currY;
	// $currY=$currY+100;
	// $pdf->AddPage();
// }
## END GRANDTOTAL ##

## BEGIN TTD ##
$pdf->SetY($akhirymahe+5);

$countListApproval = getCountApproval('PO',$exnopo[4]);
$pmbagi = $countListApproval + 1;
$widthkolom = 190 / $pmbagi;
$locimg = ($widthkolom/2) - 5;

$pdf->Cell($widthkolom,4,'Issued by',0,0,'C');
// for($i=1;$i<=$countListApproval;$i++)
// {
	$pdf->Cell($widthkolom,4,'Approved','',0,'C');
// }

$pdf->Ln();
$y = $pdf->GetY();
$pdf->Ln(20);

$pdf->Cell($widthkolom,4,strtoupper($nm_kary),'',0,'C');
// for($i=1;$i<=$countListApproval;$i++)
// {
	// $arrDetail = detailApprove($i,$nopo,'PO');
	// $pdf->Cell($widthkolom,4,$arrDetail['nama'],'',0,'C');
// }

// $optTtdp = makeOption($dbname,'setup_ttd','karyawanid,file',"karyawanid='".$karyidpurchaser."'");
// if(isset($optTtdp[$karyidpurchaser]) && file_exists($optTtdp[$karyidpurchaser]))
	// $pdf->Image($optTtdp[$karyidpurchaser], $locimg, $y, 0, 20);

// for($i=1;$i<=$countListApproval;$i++)
// {
	// $locimg = $locimg + $widthkolom;
	// $arrDetail = detailApprove($i,$nopo,'PO');
	// $optTtdp = makeOption($dbname,'setup_ttd','karyawanid,file',"karyawanid='".$arrDetail['karyawanid']."'");
	
	// if($arrDetail['status']==1)
	// {
		// if(isset($optTtdp[$arrDetail['karyawanid']]) && file_exists($optTtdp[$arrDetail['karyawanid']]))
			// $pdf->Image($optTtdp[$arrDetail['karyawanid']], ($locimg), $y, 0, 20);
	// }
// }
$pdf->Ln();

$pdf->Cell($widthkolom,4,'Staff','',0,'C');
// for($i=1;$i<=$countListApproval;$i++)
// {
	// $arrDetail = detailApprove($i,$nopo,'PO');
	// $pdf->Cell($widthkolom,4,$arrDetail['namajabatan'],'',0,'C');
// }
$pdf->Cell($widthkolom,4,'Manager','',1,'C');
$pdf->Cell($widthkolom,4,$waktucetak,0,1,'C');
## END TTD ##

$urlefil=checkPostGet('urlefil','0');
if($urlefil=='0'){
	$pdf->Output();
}else{
	$pdf->Output($urlefil);
}
?>