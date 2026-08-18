<?php
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	if($validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$session_id = '';
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
	$session_id = $_SESSION['standard']['userid'];
}else{
	if(!empty($_POST['namafile']) || !empty($_GET['namafile'])){		
		$str="select legend,ID from ".$dbname.".bahasa order by legend";
		$res=fetchdata($str);
		foreach($res as $bar){
			$_SESSION['lang'][$bar['legend']]=$bar['ID'];
		}
	}
}

require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');



// echo "<pre>";
// print_r($_GET);
// print_r($_POST);
// exit;
# Get Data
$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

#====================== Prepare Data
$ql="select a.unit,a.nopp,a.tanggal,a.dibuat, a.keterangan from ".$dbname.".`log_prapoht` a where a.nopp='".$column."'";
$pq=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
$pq->setFetchMode(PDO::FETCH_ASSOC);
$hsl=$pq->fetch();
$kdr=$hsl['unit'];
$unit=substr($column,15,4);
$keterangan=$hsl['keterangan'];


$sNmKry="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$hsl['dibuat']."'";
$qNmKry=$owlPDO->query($sNmKry) or die(print " Gagal: ".PDOException::getMessage());
$qNmKry->setFetchMode(PDO::FETCH_ASSOC);
$rNmKry=$qNmKry->fetch();
$dibuat=$rNmKry['namakaryawan'];


$sNmkntr="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdr."'";
$qNmkntr=$owlPDO->query($sNmkntr) or die(print " Gagal: ".PDOException::getMessage());
$qNmkntr->setFetchMode(PDO::FETCH_ASSOC);
$rNmkntr=$qNmkntr->fetch();
$nmKntr=$rNmkntr['namaorganisasi'];
$tgl=tanggalnormal($hsl['tanggal']);

$query="select a.*,b.*,c.namabarang,c.satuan,d.spesifikasi from ".$dbname.".".$table." a inner join ".$dbname.".`log_prapodt` b on a.nopp=b.nopp inner join ".$dbname.".`log_5masterbarang` c on b.kodebarang=c.kodebarang  left join ".$dbname.".`log_5photobarang` d on c.kodebarang=d.kodebarang where a.nopp='".$column."'"; //echo $query; exit();
$result = fetchData($query);

$expNopp = explode('/',$column);

if (!class_exists('PDFPP')){
	#====================== Prepare Header PDF
	class PDFPP extends FPDF {
		function Header() {
			global $table;
			global $header;
			global $column;
			global $dbname;
			global $tgl;
			global $nmKntr;
			global $dibuat;
			global $unit;
			global $keterangan;
			global $nmOrg;
			global $column;
			global $owlPDO;
			global $expNopp;
			
			# Panjang, Lebar
			$width = $this->w - $this->lMargin - $this->rMargin;
			$height = 12;
			
			
			$arrHead = setheadreport($unit);
			$path=$arrHead['logo'];
			
			
			$this->Image($path,$this->lMargin,10,0,60);
			
			$this->SetFont('Arial','B',10);
		   
			//$this->Ln();
			$this->SetX(110);
			$this->Cell(40/100*$width,$height,$nmKntr,'',1,'L');
			$this->SetFont('Arial','',10);
			$this->SetX(110);
			$this->Cell(40/100*$width,$height,$nmOrg[$unit],'',1,'L');
			 
			 
			$this->Ln(40); 
			$this->SetFont('Arial','B',12);
			$this->Cell(100/100*$width,$height,strtoupper(($expNopp[3]=='PR'?'Purchase Request':'Service Request')),'',1,'C');
			$this->SetFont('Arial','',10);
			$this->Cell(100/100*$width,$height,$column,'',1,'C');
			$this->Cell(100/100*$width,$height,'Tanggal. '.$tgl,'',1,'C');
					
					
					/*$this->Cell(120,$height,$a,' ',0,'L');
			$this->SetFont('Arial','B',10);
					$this->Cell(40/100*$width,$height,$nmKntr,'',0,'L');
					$this->Cell(40/100*$width,$height,'TO :','',1,'L');
					$this->Cell(120,$height,' ','',0,'L');
					//$this->Cell(22/100*$width,$height,' ','',0,'L');
					$this->SetFont('Arial','B',10);
					$this->Cell(12/100*$width,$height,$_SESSION['lang']['unit'],'',0,'L');
					$this->Cell(2/100*$width,$height,':','',0,'L');
					$this->Cell(1/100*$width,$height,substr($column,15,4),'',0,'L');		
					$this->Cell(25/100*$width,$height,' ','',0,'L');
					$this->SetFont('Arial','B',10);
					$this->Cell(12/100*$width,$height,'PURCHASING DEPARTEMENT','',0,'L');
					$this->Cell(2/100*$width,$height,'','',0,'L');
					$this->Cell(1/100*$width,$height,'','',1,'L');

					//$this->Cell(40/100*$width,$height,strtoupper($_SESSION['org']['namaorganisasi']),'',0,'L');
					$this->Cell(120,$height,' ','',0,'L');
					$this->SetFont('Arial','B',10);
					$this->Cell(12/100*$width,$height,'PP NO','',0,'L');
					$this->Cell(2/100*$width,$height,':','',0,'L');
					$this->Cell(1/100*$width,$height,$column,'',0,'L');		
					$this->Cell(25/100*$width,$height,' ','',0,'L');
					$this->SetFont('Arial','B',10);
					$this->Cell(14/100*$width,$height,$_SESSION['lang']['tanggal'],'',0,'L');
					$this->Cell(2/100*$width,$height,':','',0,'L');
					$this->Cell(1/100*$width,$height,$tgl,'',1,'L');*/


			$this->Ln(20);
		}
	}
}

#====================== Prepare PDF Setting
$pdf = new PDFPP('P','pt','A4');
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
$height = 12;

$pdf->SetFont('Arial','B',8);
$pdf->AddPage();

		$nocapex="";
		$ket=explode('/',$keterangan);
        if (@$ket[1]=='FRM'){
        	$nocapex=$keterangan;
            $pdf->Cell(100,1.5*$height,'No.Capex : '.$keterangan,0,1,'L');
        }
    
        

        $awalXjudulno=$pdf->GetX();
        $awalYjudulatas=$pdf->GetY();
        $pdf->Cell(20,1.5*$height,'No.','TBLR',0,'C');
        
        $awalXkdbrg=$pdf->GetX();
        $pdf->Cell(60,1.5*$height,$_SESSION['lang']['kodebarang'],'TBLR',0,'L');
        
        $awalXnmbrgjudul=$pdf->GetX();
        $pdf->Cell(165,1.5*$height,$_SESSION['lang']['namabarang'],'TBLR',0,'C');
        
        $awalXjum=$pdf->GetX();
        $pdf->Cell(35,1.5*$height,$_SESSION['lang']['jumlah'],'TBLR',0,'L');
        
        $awalXsat=$pdf->GetX();
        $pdf->Cell(35,1.5*$height,$_SESSION['lang']['satuan'],'TBLR',0,'C');
        
        $awalXreq=$pdf->GetX();
        $pdf->Cell(40,1.5*$height,'Required','TBLR',0,'C');
        
        $awalXket=$pdf->GetX();
        
        $pdf->Cell(190,1.5*$height,$_SESSION['lang']['keterangan'],'TBLR',0,'C');
        $akhirXket=$pdf->GetX();
        
        $pdf->Ln();
        $awalYbanget=$pdf->GetY();
        
        //echo $awalYbanget.__;
        
        $no=0;
        
        foreach($result as $data) {
            $pdf->SetFont('Arial','',7);
            
                $awalXno=$pdf->GetX();
                $no++;
                $pdf->SetY($awalYbanget);
                
                $pdf->Cell(20,$height,$no,0,0,'L');
                $pdf->Cell(60,$height,$data['kodebarang'],0,0,'L');
                
                $awalYnmbrg=$pdf->GetY();
                $awalXnmbrg=$pdf->GetX()+165;
                $pdf->MultiCell(165, $height, printSpecialChar($data['namabarang']), '0', 'L');
                $akhirYnmbrg=$pdf->GetY();
                
                $pdf->SetXY($awalXnmbrg,$awalYnmbrg);
                
                //$pdf->Cell(80,$height,$data['spesifikasi'],'TBLR',0,'L');
                $pdf->Cell(35,$height,number_format($data['jumlah'],2),0,0,'C');
                $pdf->Cell(35,$height,$data['satuan'],'0',0,'C');
                $pdf->Cell(40,$height,tanggalnormal($data['tgl_sdt']),'0',0,'L');
                //$pdf->SetFont('Arial','',6.5);
                //$height=12;
                $akhirXket=$pdf->getX()+190;
                $pdf->MultiCell(190, $height, $data['keterangan'], '0', 'L');
                
                $pdf->SetFont('Arial','I',7);
                if($data['keteranganubah']!='') 
                {
					
                                        $pdf->SetX(385);
                                        $pdf->SetFillColor(240,240,240);
                                        $pdf->MultiCell(150, $height, "- Barang diatas diubah dengan catatan: ".$data['keteranganubah'], '0', 'L');
					//$pdf->Cell(545,$height,"Barang diatas diubah oleh Purchasing dengan catatan: ".$data['keteranganubah'],1,1,'L',1);
                }
                $whKartolak="karyawanid='".$data['ditolakoleh']."'";
                $nmKartolak=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',$whKartolak);
                if($data['status']=='3') 
                {
					
                                        $pdf->SetX(385);
                                        $pdf->SetFillColor(240,240,240);
                                        $pdf->MultiCell(150, $height, '- Barang telah ditolak oleh : '.$nmKartolak[$data['ditolakoleh']], '0', 'L');
										$pdf->SetX(385);
                                        $pdf->SetFillColor(240,240,240);
                                        $pdf->MultiCell(150, $height, 'Dengan Catatan : '.$data['alasanstatus'], '0', 'L');
					//$pdf->Cell(545,$height,"Barang diatas diubah oleh Purchasing dengan catatan: ".$data['keteranganubah'],1,1,'L',1);
                }
                
                $akhirYket=$pdf->GetY();
                
                if($akhirYnmbrg>=$akhirYket)
                {
                    $akhirYbanget=$akhirYnmbrg;
                }
                else
                {
                    $akhirYbanget=$akhirYket;
                }
                
                $pdf->Line($awalXno, $akhirYbanget, $akhirXket, $akhirYbanget);
                
                $awalYbanget=$akhirYbanget;
                
                
				
				
        }
        $akhirYloop=$akhirYbanget;
        
		if(count($result)>0)
		{
			$pdf->Line($awalXjudulno, $awalYjudulatas, $awalXjudulno, $akhirYloop);
			$pdf->Line($awalXkdbrg, $awalYjudulatas, $awalXkdbrg, $akhirYloop);
			$pdf->Line($awalXnmbrgjudul, $awalYjudulatas, $awalXnmbrgjudul, $akhirYloop);
			$pdf->Line($awalXjum, $awalYjudulatas, $awalXjum, $akhirYloop);
			$pdf->Line($awalXsat, $awalYjudulatas, $awalXsat, $akhirYloop);
			
			$pdf->Line($awalXreq, $awalYjudulatas, $awalXreq, $akhirYloop);
			$pdf->Line($awalXket, $awalYjudulatas, $awalXket, $akhirYloop);
			$pdf->Line($akhirXket, $awalYjudulatas, $akhirXket, $akhirYloop);
		
        
        
        
			$pdf->__currentY=$pdf->SetY($akhirYloop);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(120,$height,$_SESSION['lang']['dbuat_oleh'].':'.$dibuat,'',0,'L');
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Cell(120,$height,$_SESSION['lang']['approval_status'].':','',0,'L');
			$pdf->Ln();
			$ko=0;
			
			$pdf->Cell(20,1.5*$height,'No.','TBLR',0,'C');
			$pdf->Cell(120,1.5*$height,$_SESSION['lang']['nama'].' / '.$_SESSION['lang']['kodejabatan'],'TBLR',0,'C');
			$pdf->Cell(70,1.5*$height,$_SESSION['lang']['lokasitugas'],'TBLR',0,'C');
			$pdf->Cell(100,1.5*$height,$_SESSION['lang']['keputusan'],'TBLR',0,'C');
			$pdf->Cell(240,1.5*$height,$_SESSION['lang']['note'],'TBLR',0,'C');
		   
			$pdf->Ln();	

			$jenisapp='PR';
			if ($nocapex!='') {
				$jenisapp='CPX';
			}
			
			$countApp = getCountApproval($jenisapp,$expNopp[4]);
			$arrDetail = array();
			for($i=1;$i<=$countApp;$i++)
			{
				$noapp="";
				$noapp=$column;

				if ($nocapex!='') {
					$noapp=$nocapex;
				}
				$arrDetail = detailApprove($i,$noapp,$jenisapp);
				
				$height=12;
				##ini untuk akalin biar dinamis, jadi kita taro keterangan di atas dahulu agar
				## mendapatkan panjang heightnya, biar rapih
				$awalY2=$pdf->GetY();
				$pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
				$pdf->MultiCell(240, $height, $arrDetail['komentar'], '0', 'L');
				$akhirY2=$pdf->GetY();
				$tinggiKet2=$akhirY2-$awalY2;
				$height2=$tinggiKet2;
				$pdf->SetY($akhirY2-$tinggiKet2);
				### tutupnya disini
				
				$pdf->SetFont('Arial','',7);
				$pdf->Cell(20,$height2,$i,'TLR',0,'C');
				$pdf->Cell(120,$height2,$arrDetail['nama']." (".tanggalnormal($arrDetail['tanggal']).") ",'TLR',0,'L');
				$pdf->Cell(70,$height2,$arrDetail['idlokasitugas'],'TLR',0,'C');
				$pdf->Cell(100,$height2,$arrDetail['namastatus'],'TLR',0,'C');
				$pdf->MultiCell(240,$height,$arrDetail['komentar'],'TLR','J');
				
				$pdf->Cell(20,1.5*$height,'','BLR',0,'C');
				$pdf->Cell(120,1.5*$height,$arrDetail['namajabatan'],'BLR',0,'L');
				$pdf->Cell(70,1.5*$height,'','BLR',0,'C');
				$pdf->Cell(100,1.5*$height,'','BLR',0,'C');
				$pdf->Cell(240,1.5*$height,'','BLR',1,'L');
			}
		}
		
		// $sCek="select nopp from ".$dbname.".log_prapodt where nopp='".$column."'";
		// $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		// $rCek=owlBaris($qCek);
        // if($rCek>0)
        // {
			// $qp="select * from ".$dbname.".`log_prapoht` where `nopp`='".$column."'"; //echo $qp;
            // $qyr=fetchData($qp);
            // foreach($qyr as $hsl)
            // {
				// for($i=1;$i<6;$i++)
                // {
					// if($hsl['hasilpersetujuan'.$i]==1)
                    // {
						// $b['status']=$_SESSION['lang']['disetujui'];
					// }
					// elseif($hsl['hasilpersetujuan'.$i]==3)
					// {
						// $b['status']=$_SESSION['lang']['ditolak'];
					// }
					// elseif($hsl['hasilpersetujuan'.$i]==''||$hsl['hasilpersetujuan'.$i]==0)
					// {
						// $b['status']=$_SESSION['lang']['wait_approve'];
					// }
					
					// if($hsl['persetujuan'.$i]!=0000000000)
                    // {
						// $keterangan=$hsl['komentar'.$i];
						// $tanggal=tanggalnormal($hsl['tglp'.$i]);
						
						// $sql="select * from ".$dbname.".`datakaryawan` where `karyawanid`='".$hsl['persetujuan'.$i]."'"; $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
						// $query->setFetchMode(PDO::FETCH_OBJ);
						// $res3=$query->fetch();
						
						// $sql2="select * from ".$dbname.".`sdm_5jabatan` where kodejabatan='".$res3->kodejabatan."'";
						// $query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
						// $query2->setFetchMode(PDO::FETCH_OBJ);
                        // $res2=$query2->fetch();
						
						// $height=12;
						// ##ini untuk akalin biar dinamis, jadi kita taro keterangan di atas dahulu agar
						// ## mendapatkan panjang heightnya, biar rapih
						// $awalY2=$pdf->GetY();
						// $pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
						// $pdf->MultiCell(240, $height, $keterangan, '0', 'L');
						// $akhirY2=$pdf->GetY();
						// $tinggiKet2=$akhirY2-$awalY2;
						// $height2=$tinggiKet2;
						// $pdf->SetY($akhirY2-$tinggiKet2);
						// ### tutupnya disini
												
                        // $pdf->SetFont('Arial','',7);
                        // $pdf->Cell(20,$height2,$i,'TLR',0,'C');
                        // $pdf->Cell(120,$height2,$res3->namakaryawan." (".$tanggal.") ",'TLR',0,'L');
                        // $pdf->Cell(70,$height2,$res3->lokasitugas,'TLR',0,'C');
                        // $pdf->Cell(100,$height2,$b['status'],'TLR',0,'L');
                        // $pdf->MultiCell(240,$height,$keterangan,'TLR','J');
                        
						// $pdf->Cell(20,1.5*$height,'','BLR',0,'C');
                        // $pdf->Cell(120,1.5*$height,$res2->namajabatan,'BLR',0,'L');
                        // $pdf->Cell(70,1.5*$height,'','BLR',0,'C');
                        // $pdf->Cell(100,1.5*$height,'','BLR',0,'C');
                        // $pdf->Cell(240,1.5*$height,'','BLR',1,'L');
					// }
					// else
					// {
						// break;
					// }
				// }
			// }
        // }
        // else
        // {
			// $pdf->SetFont('Arial','',7);
			// $pdf->Cell(520,1.5*$height,"Not Found",'TBLR',0,'C');
        // }
        // $pdf->Cell(15,$height,'Page '.$pdf->PageNo(),'',1,'L');

# Print Out
$urlefil=checkPostGet('urlefil','0');
if($urlefil=='0'){
	$pdf->Output();
}else{
	$pdf->Output($urlefil);
}

?>