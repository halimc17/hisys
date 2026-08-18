<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kodeOrg = checkPostGet('kdUnit','');
$thnBudget = checkPostGet('thnBudget','');
$kdTraksi = checkPostGet('kdTraksi','');
$kdVhc = checkPostGet('kdVhc','');
$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmbrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid']. "";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$namakar[$bar->karyawanid]=$bar->namakaryawan;
}


$where=" kodetraksi='".$kodeOrg."' and tahunbudget='".$thnBudget."'";
$sKodeOrg="select * from ".$dbname.".bgt_biaya_jam_ken_vs_alokasi where  ".$where." order by tahunbudget asc";
$qKodeOrg=$owlPDO->query($sKodeOrg) or die(print " Gagal: ".PDOException::getMessage());
$qKodeOrg->setFetchMode(PDO::FETCH_ASSOC);
$dtKdtraksi = array();
$dtKdvhc = array();
while($rKode=$qKodeOrg->fetch()){
    $dtKdtraksi[]=$rKode['kodetraksi'];
    $dtKdvhc[]=$rKode['kodevhc'];
    $dtRpSthn[$rKode['tahunbudget']][$rKode['kodetraksi']][$rKode['kodevhc']]=$rKode['rpsetahun'];
    $dtJamSthn[$rKode['tahunbudget']][$rKode['kodetraksi']][$rKode['kodevhc']]=$rKode['jamsetahun'];
    $dtRpJam[$rKode['tahunbudget']][$rKode['kodetraksi']][$rKode['kodevhc']]=$rKode['rpperjam'];
    $dtAlokasi[$rKode['tahunbudget']][$rKode['kodetraksi']][$rKode['kodevhc']]=$rKode['teralokasi'];
}

$cek=count($dtKdtraksi);


switch($proses){
	case'preview':
		if($kodeOrg==''||$thnBudget==''){
			exit("Error:Field Tidak Boleh Kosong");
		}
		if($cek==0){
			exit("Error: Data Kosong");
		}
		$tab="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
		$tab.="<tr class=rowheader>";
		$tab.="<th align=center>No.</th>";
		$tab.="<th align=center>".$_SESSION['lang']['kodetraksi']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['kodevhc']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['nopol']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['detail']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['kmhm']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['rupiah']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['rpsat']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['alokasi']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['sisa']."</th>";
		//$tab.="<th align=center>".$_SESSION['lang']['alokasirp']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['action']."</th>";
		$tab.="</tr></thead><tbody>";
		
		foreach($dtKdvhc as $lisTraksi){
			 @$terAlokasi[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]]=$dtAlokasi[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]]*$dtRpJam[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]];
			$no+=1;
			 $tab.="<tr class=rowcontent>";
			 $tab.="<td align=center>".$no."</td>";
			 $tab.="<td align=center>".getNamaOrg($kodeOrg)."</td>";
			 $tab.="<td align=center>".$lisTraksi."</td>";
			 $tab.="<td align=left>".getNopol($lisTraksi)."</td>";
			 $tab.="<td align=left>".getNopol($lisTraksi,'d')."</td>";
			 $tab.="<td align=right>".number_format($dtJamSthn[$thnBudget][$kodeOrg][$lisTraksi])."</td>";
			 $tab.="<td align=right>".number_format($dtRpSthn[$thnBudget][$kodeOrg][$lisTraksi])."</td>";
			 $tab.="<td align=right>".number_format($dtRpJam[$thnBudget][$kodeOrg][$lisTraksi])."</td>";
			 $tab.="<td align=right>".number_format($dtAlokasi[$thnBudget][$kodeOrg][$lisTraksi])."</td>";
			 $tab.="<td align=right>".number_format($dtJamSthn[$thnBudget][$kodeOrg][$lisTraksi]-$dtAlokasi[$thnBudget][$kodeOrg][$lisTraksi])."</td>";
			 //$tab.="<td align=right>".number_format($terAlokasi[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
			   $tab.="<td align=center>
				   <button class=\"mybutton\" name=\"preview\" id=\"preview\" onclick=\"getAlokasi('".$kodeOrg."','".$lisTraksi."','".$thnBudget."')\">".$_SESSION['lang']['alokasi']."</button>
					   <button class=\"mybutton\" name=\"preview\" id=\"preview\" onclick=\"getBiaya('".$kodeOrg."','".$lisTraksi."','".$thnBudget."')\">".$_SESSION['lang']['biayaRinci']."</button>
				   </td>";
			 $tab.="</tr>";
			 @$totJam+=$dtJamSthn[$thnBudget][$kodeOrg][$lisTraksi];
			 @$totRup+=$dtRpSthn[$thnBudget][$kodeOrg][$lisTraksi];
			 @$totKmThn+=$dtRpJam[$thnBudget][$kodeOrg][$lisTraksi];
			 @$totAlokasiJam+=$dtAlokasi[$thnBudget][$kodeOrg][$lisTraksi];
			 
			 //$totAlokasiRp+=$terAlokasi[$thnBudget][$kodeOrg][$lisTraksi];
		}
		//$no!=0?$rataRupi=$totRup/$no:$rataRupi=0;
		//$totJam!=0?$totRpkm=$rataRupi/$totJam:$totRpkm=0;
		
		$tab.="</tbody><thead><tr class=rowheader>";
		$tab.="<td align=center  colspan=5 align=center>".$_SESSION['lang']['total']."</td>";
		$tab.="<td align=right>".number_format($totJam)."</td>";
		$tab.="<td align=right>".number_format($totRup)."</td>";
		$tab.="<td align=right>".number_format($totKmThn)."</td>";
		$tab.="<td align=right>".number_format($totAlokasiJam)."</td>";
		$tab.="<td align=right>".number_format($totJam-$totAlokasiJam)."</td>";
		//$tab.="<td align=right>".number_format($totAlokasiRp,2)."</td>";
		$tab.="<td align=right>&nbsp</td>";
		$tab.="</tr>";
		$tab.="</thead></table>";
		echo $tab;
	break;
	case 'excel':		
		if($thnBudget==''){
			echo "warning : Tahun masih kosong";
			exit();	
		}else if($kodeOrg==''){
			echo "warning : Kode organisasi masih kosong";
			exit();	
		}
			
		$tab2="Laporan Rp/Jam per Kendaraan <br>";
		$tab2.=" ".$optNm[$kodeOrg]."  tahun ".$thnBudget." ";
		$tab2.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
		$tab2.="<tr class=rowheader bgcolor=#CCCCCC>";
		$tab2.="<td align=center>No.</td>";
		$tab2.="<td align=center>".$_SESSION['lang']['kodetraksi']."</td>";
		$tab2.="<td align=center>".$_SESSION['lang']['kodevhc']."</td>";
		$tab2.="<td align=center>".$_SESSION['lang']['jamperthn']."</td>";
		$tab2.="<td align=center>".$_SESSION['lang']['rpperthn']."</td>";
		$tab2.="<td align=center>".$_SESSION['lang']['kmperthn']."</td>";
		$tab2.="<td align=center>".$_SESSION['lang']['alokasijam']."</td>";
		//$tab.="<td align=center>".$_SESSION['lang']['alokasirp']."</td>";
		$tab2.="</tr></thead><tbody>";
                
		@$terAlokasi[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]]=$dtAlokasi[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]]*$dtRpJam[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]];
		foreach($dtKdvhc as $lisTraksi){
			$no+=1;
			 $tab2.="<tr class=rowcontent>";
			 $tab2.="<td align=center>".$no."</td>";
			 $tab2.="<td align=center>".$kodeOrg."</td>";
	   
			 $tab2.="<td align=center>".$lisTraksi."</td>";
			 $tab2.="<td align=right>".number_format($dtJamSthn[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
			 $tab2.="<td align=right>".number_format($dtRpSthn[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
			 $tab2.="<td align=right>".number_format($dtRpJam[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
			 $tab2.="<td align=right>".number_format($dtAlokasi[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
			 //$tab.="<td align=right>".number_format($terAlokasi[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
			 $tab2.="</tr>";
			 @$totJam+=$dtJamSthn[$thnBudget][$kodeOrg][$lisTraksi];
			 @$totRup+=$dtRpSthn[$thnBudget][$kodeOrg][$lisTraksi];
			 @$totKmThn+=$dtRpJam[$thnBudget][$kodeOrg][$lisTraksi];
			 @$totAlokasiJam+=$dtAlokasi[$thnBudget][$kodeOrg][$lisTraksi];
			 
			 //$totAlokasiRp+=$terAlokasi[$thnBudget][$kodeOrg][$lisTraksi];
		}
		//$no!=0?$rataRupi=$totRup/$no:$rataRupi=0;
		//$totJam!=0?$totRpkm=$rataRupi/$totJam:$totRpkm=0;
		
		$tab2.="</tbody><thead><tr class=rowheader bgcolor=#CCCCCC>";
		$tab2.="<td align=center  colspan=3 align=center>".$_SESSION['lang']['total']."</td>";
		$tab2.="<td align=right>".@number_format($totJam,2)."</td>";
		$tab2.="<td align=right>".@number_format($totRup,2)."</td>";
		$tab2.="<td align=right>".@number_format($totKmThn,2)."</td>";
		$tab2.="<td align=right>".@number_format($totAlokasiJam,2)."</td>";
		//$tab.="<td align=right>".number_format($totAlokasiRp,2)."</td>";
		$tab2.="</tr>";
		$tab2.="</thead></table>";
		
		$tglSkrg=date("Ymd");
		$nop_="Laporan_Exel_".$tglSkrg;
		//$nop_"Laporan Daftar Asset ".$nmOrg."_".$nmAst;
		//$nop_="Daftar Asset : ".$nmOrg." ".$nmAst;
		if(strlen($tab2)>0){
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/'.$file);
					}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$tab2)){
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}
			else
			{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			//closedir($handle);
		}           
		break;			
		// tutup tampilakn panggil exel //
		
		
		
		
	case'pdf':
	
		if($thnBudget=='')
			{
				echo "warning : Tahun masih kosong";
				exit();	
			}
			else if($kodeOrg=='')
			{
				echo "warning : Kode organisasi masih kosong";
				exit();	
			}
		
		//buat header pdf
		class PDF extends FPDF
		{
            function Header() 
			{
				global $nmOrg;
				global $optNm;
				global $thnBudget;
				global $kodeOrg;
				global $kdUnit;
				global $totRp;
				global $conn;
				global $dbname;
				global $align;
				global $length;
				global $colArr;
				global $title;
				global $total;
				global $namakar;
				
				//total
				global $totJam;
                global $totRup;
                global $totKmThn;
				global $totAlokasiJam;

				/*global $dataKary;
				global $dataKaryIstri;
				global $dataTanggugan;
				global $dtKode2;
				global $kodeOrg;
				
				global $dataTipeKary;
				global $totalTipe;
				global $dbname;*/
            
        //alamat PT minanga dan logo
						$query = selectQuery($dbname,'organisasi','alamat,telepon',
							"kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
						$orgData = fetchData($query);
						
						$width = $this->w - $this->lMargin - $this->rMargin;
						$height = 20;
						$path='images/logo.jpg';
						$this->Image($path,$this->lMargin,$this->tMargin,0,55);
						$this->SetFont('Arial','B',9);
						$this->SetFillColor(255,255,255);	
						$this->SetX(100);   
						$this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
						$this->SetX(100); 		
						$this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
						$this->SetX(100); 			
						$this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
						$this->Line($this->lMargin,$this->tMargin+($height*4),
						$this->lMargin+$width,$this->tMargin+($height*4));
						$this->Ln();
						//tutup logo dan alamat
						
						//untuk sub judul
						$this->SetFont('Arial','B',10);
						$this->Cell((20/100*$width)-5,$height,"Biaya Kendaraan",'',0,'L');
						$this->Ln();
						$this->SetFont('Arial','',10);
						$this->Cell((100/100*$width)-5,$height,"Printed By : ".$namakar[$_SESSION['standard']['userid']],'',0,'R');
						$this->Ln();
						$this->Cell((100/100*$width)-5,$height,"Date : ".date('d-m-Y'),'',0,'R');
						$this->Ln();
						$this->Cell((100/100*$width)-5,$height,"Time : ".date('h:i:s'),'',0,'R');
						$this->Ln();
						$this->Ln();
						//tutup sub judul
						
						//judul tengah
						$this->SetFont('Arial','B',12);
						$this->Cell($width,$height,strtoupper("Biaya Kendaraan ".$optNm[$kodeOrg]),'',0,'C');
						$this->Ln();
						$this->Cell($width,$height,strtoupper("Tahun " .$thnBudget),'',0,'C');
						$this->Ln();
						$this->Ln();
						//tutup judul tengah
						
						//isi atas tabel
						$this->SetFont('Arial','B',10);
						$this->SetFillColor(220,220,220);
						$this->Cell(2/100*$width,$height,"No",1,0,'C',1);
						$this->Cell(13/100*$width,$height,$_SESSION['lang']['kodetraksi'],1,0,'C',1);
						$this->Cell(15/100*$width,$height,$_SESSION['lang']['kodevhc'],1,0,'C',1);
						$this->Cell(15/100*$width,$height,$_SESSION['lang']['jamperthn'],1,0,'C',1);
						$this->Cell(15/100*$width,$height,$_SESSION['lang']['rpperthn'],1,0,'C',1);
						$this->Cell(10/100*$width,$height,$_SESSION['lang']['kmperthn'],1,0,'C',1);
						$this->Cell(13/100*$width,$height,$_SESSION['lang']['alokasijam'],1,1,'C',1);
						//$this->Cell(15/100*$width,$height,$_SESSION['lang']['alokasirp'],1,1,'C',1);
						//tutup isi tabel
					}//tutup header pdfnya
					function Footer()
					{
						$this->SetY(-15);
						$this->SetFont('Arial','I',8);
						$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
					}
				}
				//untuk tampilan setting pdf
				$pdf=new PDF('L','pt','Legal');//untuk kertas L=len p=pot
				$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
				$height = 20;
				$pdf->AddPage();
				$pdf->SetFillColor(255,255,255);
				$pdf->SetFont('Arial','',10);//ukuran tulisan
				//tutup tampilan setting
/*		SELECT *
FROM `bgt_biaya_jam_ken_vs_alokasi`
WHERE `tahunbudget` =2011
AND `kodetraksi` LIKE 'SSRO31'
LIMIT 0 , 30*/
		
				//isi tabel dan tabelnya
				//$no=0;
				$sql="select * from ".$dbname.".bgt_biaya_jam_ken_vs_alokasi where tahunbudget='".$thnBudget."' and kodetraksi='".$kodeOrg."' ";
				$qDet=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
				$qDet->setFetchMode(PDO::FETCH_ASSOC);
				while($res=$qDet->fetch())
				{
					$no+=1;
					$pdf->Cell(2/100*$width,$height,$no,1,0,'C',1);
					$pdf->Cell(13/100*$width,$height,$res['kodetraksi'],1,0,'L',1);	
					$pdf->Cell(15/100*$width,$height,$res['kodevhc'],1,0,'L',1);	
					$pdf->Cell(15/100*$width,$height,number_format($res['jamsetahun'],2),1,0,'R',1);	
					$pdf->Cell(15/100*$width,$height,number_format($res['rpsetahun'],2),1,0,'R',1); 
					$pdf->Cell(10/100*$width,$height,number_format($res['rpperjam'],2),1,0,'R',1);
					$pdf->Cell(13/100*$width,$height,number_format($res['teralokasi'],2),1,0,'R',1);	
					//$pdf->Cell(15/100*$width,$height,$res[''],1,0,'R',1);	            
					$pdf->Ln();	
					
					$totJam+=$res['jamsetahun'];
					$totRup+=$res['rpsetahun'];
					$totKmThn+=$res['rpperjam'];
					$totAlokasiJam+=$res['teralokasi'];
					
				}
				$pdf->SetFont('Arial','B',12);
				$pdf->SetFillColor(220,220,220);
				$pdf->Cell(30/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);	
				//$pdf->SetFontSize(9);
				
				$pdf->SetFont('Arial','',10);
				$pdf->SetFontSize(10);
				$pdf->Cell(15/100*$width,$height,number_format($totJam,2),1,0,'R',1);	
				$pdf->Cell(15/100*$width,$height,number_format($totRup,2),1,0,'R',1);	
				$pdf->Cell(10/100*$width,$height,number_format($totKmThn,2),1,0,'R',1);	
				$pdf->Cell(13/100*$width,$height,number_format($totAlokasiJam,2),1,0,'R',1);	
					
			$pdf->Output();
	##### Tutup PDF #####

	break;
	case'getAlokasi':
	case'excelAlokasi':
		if($proses=='excelAlokasi'){
			$tab.="<table cellpadding=5 cellspacing=1 border=1 class=sortable><thead>";
		}else{			
			$tab="<fieldset style=float:left>";
			$tab.="<img title=\"MS.Excel\" class=\"resicon\" src=\"images/excel.jpg\" onclick=\"dataKeExcelAlokasi(event,'".$kdTraksi."','".$kdVhc."','".$thnBudget."')\">
		   <!--<img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"dataKePdfAlokasi(event,'".$kdTraksi."','".$kdVhc."','".$thnBudget."');\">--> 
		   </fieldset><div style=clear:both></div>
		   ";
			$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
		}
		$tab.="<tr class=rowheader>";
		$tab.="<th align=center>No</th>";
		$tab.="<th align=center>".$_SESSION['lang']['unit']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['akun']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['kegiatan']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['kodeorg']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['jam']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['rp']."</th></tr></thead><tbody>";
		
		$str="select a.*,jumlah AS jumlahjam from ".$dbname.".bgt_budget a where kodevhc is not null and kodevhc !='' and kodevhc!='0' and (kodebudget = 'VHC' or kodebudget ='UMUM') and tipebudget!='TRK' and kodevhc='".$kdVhc."' and tahunbudget='".$thnBudget."' order by substr(kodeorg,1,4) asc, kodebudget";
		$qDetail=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qDetail->setFetchMode(PDO::FETCH_ASSOC);
		while($rDetail=$qDetail->fetch()){
			$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$rDetail['noakun']."'");
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$rDetail['kegiatan']."'");
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".substr($rDetail['kodeorg'],0,4)."</td>";
			$tab.="<td>".$rDetail['noakun']." - ".$nmakun[$rDetail['noakun']]."</td>";
			$tab.="<td>".$rDetail['kegiatan']." - ".$nmkeg[$rDetail['kegiatan']]."</td>";
			$tab.="<td>".getNamaOrg($rDetail['kodeorg'])."</td>";
			$tab.="<td align=right>".number_format($rDetail['jumlahjam'])."</td>";
			$tab.="<td align=right>".number_format($rDetail['rupiah'])."</td>";
			$tab.="</tr>";
			@$totRupiahDet+=$rDetail['rupiah'];
			@$totJamDet+=$rDetail['jumlahjam'];
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=5>Total</td>";
		$tab.="<td align=right>".number_format($totJamDet)."</td>";
		$tab.="<td  align=right>".number_format($totRupiahDet)."</td>";
		$tab.="</tbody></table>";
		
		if($proses=='excelAlokasi'){
			$tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHis");
            $nop_="detailAlokasi";
            if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$tab)){
					echo "<script language=javascript1.2>
						parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
            }
			
		}else{			
			echo $tab;
		}
		
	break;
		
			
			/*TRAKSI WILAYAH SUMATERA SELATAN			
Alokasi 10KVAGNT01 Tahun Budget: 2011	*/		
	/* case'excelAlokasi':
            
             $tab="<fieldset><legend>".$_SESSION['lang']['alokasi']." ".$kdVhc." ".$_SESSION['lang']['budgetyear'].": ".$thnBudget."</legend>";
            $tab.="<img title=\"MS.Excel\" class=\"resicon\" src=\"images/excel.jpg\" onclick=\"dataKeExcelAlokasi(event,'".$kdTraksi."','".$kdVhc."','".$thnBudget."')\">
				   <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"dataKePdfAlokasi(event,'".$kdTraksi."','".$kdVhc."','".$thnBudget."');\"> ";				
			$tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<td>No</td>";
            $tab.="<td>".$_SESSION['lang']['kodeorg']."</td>";
            $tab.="<td>".$_SESSION['lang']['jam']."</td>";
            $tab.="<td>".$_SESSION['lang']['rp']."</td></tr></thead><tbody>";
            $str="select a.*,jumlah AS jumlahjam from ".$dbname.".bgt_budget a where kodevhc is not null and kodevhc !='' and kodevhc!='0' and (kodebudget = 'VHC' or kodebudget ='UMUM') and tipebudget!='TRK' and kodevhc='".$kdVhc."' and tahunbudget='".$thnBudget."' order by substr(kodeorg,1,4) asc, kodebudget";
			$qDetail=$owlPDO->query($sDetail) or die(print " Gagal: ".PDOException::getMessage());
			$qDetail->setFetchMode(PDO::FETCH_ASSOC);
            while($rDetail=$qDetail->fetch())
            {
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td>".$no."</td>";
                $tab.="<td>".$rDetail['kodeorg']."</td>";
                $tab.="<td align=right>".number_format($rDetail['jumlah']+$rDetail['volume'],2)."</td>";
                $tab.="<td align=right>".number_format($rDetail['rupiah'],2)."</td>";
                $tab.="</tr>";
                @$totRupiahDet+=$rDetail['rupiah'];
                @$totJamDet+=$rDetail['jumlah']+$rDetail['volume'];
            }
            $tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=2>Total</td>";
            $tab.="<td align=right>".number_format($totJamDet,2)."</td>";
            $tab.="<td  align=right>".number_format($totRupiahDet,2)."</td>";
            $tab.="</tbody></table></fieldset";
            
            $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHis");
            $nop_="detailAlokasi";
            if(strlen($tab)>0)
            {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
               closedir($handle);
            }
             $handle=fopen("tempExcel/".$nop_.".xls",'w');
             if(!fwrite($handle,$tab))
             {
              echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
               exit;
             }
             else
             {
              echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls';
                    </script>";
             }
            //closedir($handle);
            }
            break; */
            
			
			
			
case'pdfAlokasi':

//create Header

		class PDF extends FPDF
        {
            function Header() {
                
				global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
	
				global $kodeTraksi;
				global $kdTraksi;
				global $kdVhc;
				global $kdkend;
				global $thnBudget;
				global $thnbdget;
       
				

				
                //alamat PT

                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 20;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
                
				//untuk sub judul
                $this->SetFont('Arial','B',10);
				$this->Ln();
				$this->Cell((20/100*$width)-5,$height,"Detail Laporan Rp Jam/Kendaraan",'',0,'L');
				$this->Ln();
				$this->Ln();
				
				//judul tengah
				$this->Cell($width,$height,strtoupper("Detail Laporan Rp Jam/Kendaraan "."$kdVhc"),'',0,'C');
				$this->Ln();
				$this->Cell($width,$height,strtoupper("Tahun "."$thnBudget"),'',0,'C');
				$this->Ln();
				$this->Ln();
				
				//isi atas tabel
              	$this->SetFont('Arial','B',8);
                $this->SetFillColor(220,220,220);
				$this->Cell(5/100*$width,$height,"No",1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['kodeorg'],1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['jam'],1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['rp'],1,1,'C',1);	
            }
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
		
		//untuk kertas L=len p=potraid
        $pdf=new PDF('P','pt','Legal');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 20;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',7);


		//isi tabel dan tabelnya
		$no=0;
		$sql="select jumlah,kodeorg,rupiah from ".$dbname.".bgt_budget where tipebudget!='TRK' and kodevhc='".$kdVhc."' and tahunbudget='".$thnBudget."'";
		// != identik dengan <>
		//exit ("Error:$sql");
		//$sql="select * from ".$dbname.".bgt_biaya_ws_per_jam a, ".$dbname.".bgt_budget b where a.tahunbudget = b.tahunbudget and kodews='".$kdWs."' and a.tahunbudget='".$thnbudget."' ";
		$qDet=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$qDet->fetch())
		{
			$no+=1;
			$pdf->Cell(5/100*$width,$height,$no,1,0,'C',1);
			$pdf->Cell(15/100*$width,$height,$res['kodeorg'],1,0,'L',1);	
			$pdf->Cell(15/100*$width,$height,$res['jumlah'],1,0,'R',1);	
			$pdf->Cell(15/100*$width,$height,number_format($res['rupiah'],2),1,0,'R',1);	 						                   
			$pdf->Ln();	
			
			@$totDetailPdfJam+=$res['jumlah'];
			@$totDetailPdfRp+=$res['rupiah'];
		}
		
		$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);	
		$pdf->Cell(15/100*$width,$height,number_format($totDetailPdfJam,2),1,0,'R',1);
		$pdf->Cell(15/100*$width,$height,number_format($totDetailPdfRp,2),1,0,'R',1);
	$pdf->Output();
	
	break;			
			
			

			
	case'getBiaya':
            $tab="<fieldset style=float:left>";
            $tab.="<img title=\"MS.Excel\" class=\"resicon\" src=\"images/excel.jpg\" onclick=\"dataKeExcel(event,'".$kdTraksi."','".$kdVhc."','".$thnBudget."')\">
			 	   <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"dataKePdfBiaya(event,'".$kdTraksi."','".$kdVhc."','".$thnBudget."');\"> </fieldset><div style=clear:both></div>";
			
			$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
			$tab.="<tr class=rowheader style=text-align:center>";
            $tab.="<th>No</th>";
            $tab.="<th>".$_SESSION['lang']['kodeorg']."</th>";
            $tab.="<th>".$_SESSION['lang']['kodeanggaran']."</th>";
            $tab.="<th>".$_SESSION['lang']['kodebarang']."</th>";
            $tab.="<th>".$_SESSION['lang']['namabarang']."</th>";
            $tab.="<th>".$_SESSION['lang']['volume']."</th>";
            $tab.="<th>".$_SESSION['lang']['satuan']."</th>";
            $tab.="<th>".$_SESSION['lang']['jumlah']."</th>";
            $tab.="<th>".$_SESSION['lang']['satuan']."</th>";
            $tab.="<th>".$_SESSION['lang']['rp']."</th></tr></thead><tbody>";
            $sDetail="select kodeorg,kodebudget,kodebarang,volume,satuanv,jumlah,satuanj ,rupiah from ".$dbname.".bgt_budget where tipebudget='TRK' and kodevhc='".$kdVhc."' and tahunbudget='".$thnBudget."'";
            $qDetail=$owlPDO->query($sDetail) or die(print " Gagal: ".PDOException::getMessage());
			$qDetail->setFetchMode(PDO::FETCH_ASSOC);
            while($rDetail=$qDetail->fetch())
            {
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td>".$no."</td>";
                $tab.="<td>".getNamaOrg($rDetail['kodeorg'])."</td>";
                $tab.="<td>".getBgtKode($rDetail['kodebudget'])."</td>";
                $tab.="<td align=center>".$rDetail['kodebarang']."</td>";
                $tab.="<td>".@$optNmbrg[$rDetail['kodebarang']]."</td>";
                $tab.="<td align=right>".number_format($rDetail['volume'],2)."</td>";
                $tab.="<td>".$rDetail['satuanv']."</td>";
                $tab.="<td align=right>".number_format($rDetail['jumlah'],2)."</td>";
                $tab.="<td>".$rDetail['satuanj']."</td>";
                $tab.="<td align=right>".number_format($rDetail['rupiah'])."</td>";
                $tab.="</tr>";
                @$totVol+=$rDetail['volume'];
                @$totJum+=$rDetail['jumlah'];
                @$totRp+=$rDetail['rupiah'];
            }
            $tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=5>Total</td>";
            $tab.="<td  align=right>".number_format($totVol,2)."</td>";
            $tab.="<td  align=right>&nbsp;</td>";
            $tab.="<td  align=right>".number_format($totJum,2)."</td>";
            $tab.="<td  align=right>&nbsp;</td>";
            $tab.="<td  align=right>".number_format($totRp)."</td>";
            $tab.="</tbody></table>";
            echo $tab;
            break;
			
			
			
	 case'excelBiaya':
                $tab.="<table>
             <tr><td colspan=4 align=left>".$optNm[$kdTraksi]."</td></tr>   
             <tr><td colspan=4>".$_SESSION['lang']['biayaRinci']." ".$kdVhc." ".$_SESSION['lang']['budgetyear'].": ".$thnBudget."</td></tr>   
             </table>";
            $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<td bgcolor=#DEDEDE align=center>No</td>";
            $tab.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodeorg']."</td>";
            $tab.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodeanggaran']."</td>";
            $tab.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodebarang']."</td>";
            $tab.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namabarang']."</td>";
            $tab.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['volume']."</td>";
            $tab.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['satuan']."</td>";
            $tab.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']."</td>";
            $tab.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['satuan']."</td>";
            $tab.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['rp']."</td></tr></thead><tbody>";
            $sDetail="select kodeorg,kodebudget,kodebarang,volume,satuanv,jumlah,satuanj ,rupiah from ".$dbname.".bgt_budget where tipebudget='TRK' and kodevhc='".$kdVhc."' and tahunbudget='".$thnBudget."'";
            $qDetail=$owlPDO->query($sDetail) or die(print " Gagal: ".PDOException::getMessage());
			$qDetail->setFetchMode(PDO::FETCH_ASSOC);
            while($rDetail=$qDetail->fetch())
            {
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td>".$no."</td>";
                $tab.="<td>".$rDetail['kodeorg']."</td>";
                $tab.="<td>".$rDetail['kodebudget']."</td>";
                $tab.="<td>".$rDetail['kodebarang']."</td>";
                $tab.="<td>".@$optNmbrg[$rDetail['kodebarang']]."</td>";
                $tab.="<td align=right>".number_format($rDetail['volume'],2)."</td>";
                $tab.="<td>".$rDetail['satuanv']."</td>";
                $tab.="<td align=right>".number_format($rDetail['jumlah'],2)."</td>";
                $tab.="<td>".$rDetail['satuanj']."</td>";
                $tab.="<td align=right>".number_format($rDetail['rupiah'],2)."</td>";
                $tab.="</tr>";
                @$totVol+=$rDetail['volume'];
                @$totJum+=$rDetail['jumlah'];
                @$totRp+=$rDetail['rupiah'];
            }
            $tab.="<tr class=rowcontent bgcolor=#CCCCCC>";
			$tab.="<td align=center colspan=5>Total</td>";
            $tab.="<td  align=right>".number_format($totVol,2)."</td>";
            $tab.="<td  align=right>&nbsp;</td>";
            $tab.="<td  align=right>".number_format($totJum,2)."</td>";
            $tab.="<td  align=right>&nbsp;</td>";
            $tab.="<td  align=right>".number_format($totRp,2)."</td>";
            $tab.="</tbody></table>";
            $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHis");
            $nop_="detailRincianBiaya";
            if(strlen($tab)>0)
            {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
               closedir($handle);
            }
             $handle=fopen("tempExcel/".$nop_.".xls",'w');
             if(!fwrite($handle,$tab))
             {
              echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
               exit;
             }
             else
             {
              echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls';
                    </script>";
             }
            //closedir($handle);
            }
            break;
			
			
			
			
			
case'pdfBiaya':

//create Header

		class PDF extends FPDF
        {
            function Header() {
                
				global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
	
				global $kodeTraksi;
				global $kdTraksi;
				global $kdVhc;
				global $kdkend;
				global $thnBudget;
				global $thnbdget;
       
				

				
                //alamat PT

                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 20;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
                
				//untuk sub judul
                $this->SetFont('Arial','B',10);
				$this->Ln();
				$this->Cell((20/100*$width)-5,$height,"Biaya Rinci Kendaraan",'',0,'L');
				$this->Ln();
				$this->Ln();
				
				//judul tengah
				$this->Cell($width,$height,strtoupper("Biaya Rinci Kendaraan "."$kdVhc"),'',0,'C');
				$this->Ln();
				$this->Cell($width,$height,strtoupper("Tahun "."$thnBudget"),'',0,'C');
				$this->Ln();
				$this->Ln();
				
				//isi atas tabel
              	$this->SetFont('Arial','B',8);
                $this->SetFillColor(220,220,220);
				$this->Cell(5/100*$width,$height,"No",1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['kodeorg'],1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['kodeanggaran'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['namabarang'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['volume'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['satuan'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['jumlah'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['satuan'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['rp'],1,1,'C',1);	
				
            }
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
		
		//untuk kertas L=len p=potraid
        $pdf=new PDF('L','pt','Legal');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 20;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',7);


		//isi tabel dan tabelnya
		$no=0;
		$sql="select kodeorg,kodebudget,kodebarang,volume,satuanv,jumlah,satuanj ,rupiah from ".$dbname.".bgt_budget where tipebudget='TRK' and kodevhc='".$kdVhc."' and tahunbudget='".$thnBudget."'";
		// != identik dengan <>
		//exit ("Error:$sql");
		//$sql="select * from ".$dbname.".bgt_biaya_ws_per_jam a, ".$dbname.".bgt_budget b where a.tahunbudget = b.tahunbudget and kodews='".$kdWs."' and a.tahunbudget='".$thnbudget."' ";
		$qDet=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$qDet->fetch())
		{
			$no+=1;
			$pdf->Cell(5/100*$width,$height,$no,1,0,'C',1);
			$pdf->Cell(15/100*$width,$height,$res['kodeorg'],1,0,'L',1);	
			$pdf->Cell(15/100*$width,$height,$res['kodebudget'],1,0,'L',1);	
			$pdf->Cell(10/100*$width,$height,$res['kodebarang'],1,0,'L',1);	
			
			$pdf->Cell(10/100*$width,$height,number_format($res['volume'],2),1,0,'R',1);	
			$pdf->Cell(8/100*$width,$height,$res['satuanv'],1,0,'L',1);	
			$pdf->Cell(10/100*$width,$height,number_format($res['jumlah'],2),1,0,'R',1);	
			$pdf->Cell(8/100*$width,$height,$res['satuanj'],1,0,'L',1);	
			$pdf->Cell(10/100*$width,$height,number_format($res['rupiah'],2),1,0,'R',1);	
			

			 						                   
			$pdf->Ln();	
			
			//$totDetailPdfJam+=$res['jumlah'];
			//$totDetailPdfRp+=$res['rupiah'];
			
			@$tota+=$res['volume'];
			@$totb+=$res['jumlah'];
			@$totc+=$res['rupiah'];
			
		}
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(35/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
		
		$pdf->SetFont('Arial','',7);
		$pdf->Cell(10/100*$width,$height,"",1,0,'R',1);
		$pdf->Cell(10/100*$width,$height,number_format($tota,2),1,0,'R',1);
		$pdf->Cell(8/100*$width,$height,"",1,0,'R',1);
		$pdf->Cell(10/100*$width,$height,number_format($totb,2),1,0,'R',1);
		$pdf->Cell(8/100*$width,$height,"",1,0,'R',1);
		$pdf->Cell(10/100*$width,$height,number_format($totc,2),1,0,'R',1);
	$pdf->Output();
	
	break;						
	
                
            default:
            break;
        }
	
?>