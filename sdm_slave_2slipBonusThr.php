<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$periode = checkPostGet('periodegaji','');
$period = checkPostGet('period','');
$jenis = checkPostGet('jenis','');
$jnsGaji = checkPostGet('jnsGaji','');
$kdOrg = checkPostGet('kdOrg','');

$tahunlalu=$periode-1;
$tgltahunlalu=$tahunlalu.'-12-31';

$re=array('28'=>'THR','26'=>'Bonus');
$optTipe=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$arrBln=array(1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"Mei",6=>"Jun",7=>"Jul",8=>"Agu",9=>"Sep",10=>"Okt",11=>"Nov",12=>"Des");
$sPeriode="select distinct periodegaji from ".$dbname.".sdm_gaji 
           where substr(periodegaji,1,4)='".$periode."' and kodeorg='".$kdOrg."' and idkomponen='".$jenis."'";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
$rPeriode=$qPeriode->fetch();
$dakarbulanan=0;
$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$kdOrg."' and periodegaji='".$periode."' "; 
$res = fetchdata($str);
if(count($res)>0)
{ 
  $dakarbulanan=1;
}
if($dakarbulanan==0){
    $sSlip="select distinct a.*,COALESCE(ROUND(DATEDIFF('".$tgltahunlalu."',b.tanggalmasuk)/365.25,3),0) as masakerja,b.norekeningbank,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,b.subbagian from 
                   ".$dbname.".sdm_gaji_vw a  
                   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
                   left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
                   left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
                   where b.sistemgaji='".$jnsGaji."' and a.periodegaji='".$rPeriode['periodegaji']."' and b.lokasitugas='".$kdOrg."' and idkomponen='".$jenis."' and tanggalkeluar='0000-00-00'";
}else{
    $sSlip="select distinct a.*,COALESCE(ROUND(DATEDIFF('".$tgltahunlalu."',b.tanggalmasuk)/365.25,3),0) as masakerja,b.norekeningbank,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,b.subbagian from 
                   ".$dbname.".sdm_gaji_vw a  
                   left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid 
                   left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
                   left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
                   where b.sistemgaji='".$jnsGaji."' and approval_status='8' and version_type='B' and a.periodegaji='".$rPeriode['periodegaji']."' and b.lokasitugas='".$kdOrg."' and idkomponen='".$jenis."' and tanggalkeluar='0000-00-00'";
}

if($jenis==26){
    $thn=$tahunlalu;
}
else{
    $thn=$periode;
}
// $sGaji2="select karyawanid,idkomponen,sum(jumlah) as jumlah from ".$dbname.".sdm_5gajipokok where
//     idkomponen in (select distinct id from ".$dbname.".sdm_ho_component where plus=1) and tahun='".$thn."'
//         group by karyawanid";
$sGaji2="select karyawanid,idkomponen,sum(jumlah) as jumlah from ".$dbname.".sdm_5gajipokok where
    idkomponen=1 and tahun='".$thn."'
        group by karyawanid";
$rGaji2=$owlPDO->query($sGaji2) or die(print " Gagal: ".PDOException::getMessage());
$rGaji2->setFetchMode(PDO::FETCH_OBJ);
while($bar=$rGaji2->fetch())
{
        $kamusgaji[$bar->karyawanid]=$bar->jumlah;
}    

$sPengali="select kodeorg, karyawanid, pengali from ".$dbname.".sdm_gaji where
    periodegaji='".$rPeriode['periodegaji']."' and idkomponen='".$jenis."' and kodeorg='".$kdOrg."'";
$rPengali=$owlPDO->query($sPengali) or die(print " Gagal: ".PDOException::getMessage());
$rPengali->setFetchMode(PDO::FETCH_OBJ);
while($bar=$rPengali->fetch())
{
        $kamuspengali[$bar->karyawanid]=$bar->pengali;
}    

//echo $sPengali;
//echo "<pre>";
//print_r($kamuspengali);
//echo "</pre>";

switch($proses)
{
	case'preview':
	$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr><td>No</td><td>".$_SESSION['lang']['namakaryawan']."</td>";
        $tab.="<td>".$_SESSION['lang']['norekeningbank']."</td>";
        $tab.="<td>".$_SESSION['lang']['subbagian']."</td>";
        $tab.="<td>".$_SESSION['lang']['nik']."</td>";  
        $tab.="<td>".$_SESSION['lang']['tmk']."</td>";
        $tab.="<td>".$_SESSION['lang']['masakerja']."</td>";
        $tab.="<td>".$_SESSION['lang']['bagian']."</td>";
        $tab.="<td>".$_SESSION['lang']['tipekaryawan']."</td>";
        $tab.="<td>".$_SESSION['lang']['statuspajak']."</td>";
        $tab.="<td>".$_SESSION['lang']['functionname']."</td>";
        $tab.="<td>".$_SESSION['lang']['gaji']."</td>";
        $tab.="<td>".$_SESSION['lang']['pengali']."</td>";
        $tab.="<td>".$_SESSION['lang']['jumlah']." Bonus/THR</td></tr></thead><tbody>";
        $qSlip=$owlPDO->query($sSlip) or die(print " Gagal: ".PDOException::getMessage());
		$qSlip->setFetchMode(PDO::FETCH_ASSOC);
		$rRow=owlBaris($qSlip);
		if($rRow==0)
        {
            exit("Error:Not found");
        }
        while($rSlip=$qSlip->fetch())
        {
            $no+=1;
            $tab.="<tr class=rowcontent><td>".$no."</td><td>".$rSlip['namakaryawan']."</td>";
            $tab.="<td>".$rSlip['norekeningbank']."</td>";
            $tab.="<td>".$rSlip['nik']."</td>";
            $tab.="<td>".$rSlip['subbagian']."</td>";
            $tab.="<td>".$rSlip['tanggalmasuk']."</td>";
            $tab.="<td align=right>".number_format($rSlip['masakerja'],3)."</td>";
            $tab.="<td>".$rSlip['bagian']."</td>";
            $tab.="<td>".$optTipe[$rSlip['tipekaryawan']]."</td>";
            $tab.="<td>".$rSlip['statuspajak']."</td>";
            $tab.="<td>".$rSlip['namajabatan']."</td>";
            $tab.="<td align=right>".number_format($kamusgaji[$rSlip['karyawanid']],0)."</td>";
            $tab.="<td align=right>".number_format($kamuspengali[$rSlip['karyawanid']],3)."</td>";
            $tab.="<td align=right>".number_format($rSlip['jumlah'],0)."</td></tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
	break;
        case'pdf':
	

	//+++++++++++++++++++++++++++++++++++++++++++++++++++++
//create Header

class PDF extends FPDF
{
var $col=0;
var $dbname;

function SetCol($col)
	{
	    //Move position to a column
	    $this->col=$col;
	    $x=10+$col*100;
	    $this->SetLeftMargin($x);
	    $this->SetX($x);
	}

function AcceptPageBreak()
	{ 
			if($this->col<1)
		    {
		        //Go to next column
		        $this->SetCol($this->col+1);
		        $this->SetY(10);
		        return false;
		    }
		    else
		    {
		        //Go back to first column and issue page break
				$this->SetCol(0);
		        return true;
		    }
	}

	function Header()
	{    
		// $this->lMargin=5;  
	}
	
	function Footer()
	{
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',5);
	    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}
}
	$pdf=new PDF('P','mm','A4');
	$pdf->AddPage();
	$pdf->SetFont('Arial','',5);
        //periode gaji
        $bln=explode('-',$period);
        $idBln=count($bln)>1? intval($bln[1]): 0;
	$qSlip=$owlPDO->query($sSlip) or die(print " Gagal: ".PDOException::getMessage());
	$qSlip->setFetchMode(PDO::FETCH_ASSOC);
	$rRow=owlBaris($qSlip);
	    if($rRow==0)
        {
            exit("Error:Not found");
        }
        while($rSlip=$qSlip->fetch())
        {
            if($rSlip['subbagian']==''||is_null($rSlip['subbagian']))
            {
                $rSlip['subbagian']=$rSlip['kodeorg'];
            }
			
			// $arrHead = setheadreport(substr($rSlip['subbagian'],0,4));
				
			// $pdf->Image($arrHead['logo'],$pdf->GetX(),$pdf->GetY()-4,10);
			// $pdf->SetX($pdf->getX()+10);
			// $pdf->SetFont('Arial','B',8);	
			// $pdf->Cell(70,5,$arrHead['nama'],0,1,'L');
			
			$pdf->SetFont('Arial','',7);
			$pdf->Cell(56,4,$_SESSION['lang']['slipGaji'].': '.$rPeriode['periodegaji'],'T',0,'L');
			$pdf->SetFont('Arial','',6);
				$pdf->Cell(40,4,'Printed on : '.date('d-m-Y: H:i:s'),'T',1,'L');
			$pdf->SetFont('Arial','',6);		
			$pdf->Cell(24,4,$_SESSION['lang']['nik']."/".$_SESSION['lang']['tmk'],0,0,'L');
				$pdf->Cell(32,4,": ".$rSlip['nik']."/".tanggalnormal($rSlip['tanggalmasuk']),0,0,'L');
			$pdf->Cell(11,4,$_SESSION['lang']['unit']."/".$_SESSION['lang']['bagian'],0,0,'L');	
				$pdf->Cell(28,4,': '.$rSlip['subbagian']." / ".$rSlip['bagian'],0,1,'L');		
			$pdf->Cell(24,4,$_SESSION['lang']['namakaryawan'].":",0,0,'L');
				$pdf->Cell(32,4,': '.$rSlip['namakaryawan'],0,0,'L');	
			$pdf->Cell(11,3,$_SESSION['lang']['jabatan'],0,0,'L');
				$pdf->Cell(28,4,': '.$rSlip['namajabatan'],0,1,'L');	
			
			$pdf->SetFont('Arial','B',7);
			$pdf->Cell(24,4,$re[$jenis],0,0,'L');
			$pdf->Cell(3,4,":Rp.",0,0,'L');
				$pdf->Cell(18,4,number_format($rSlip['jumlah'],2,'.',','),0,0,'R');
				$pdf->Cell(47,4,"",0,1,'L');
				$terbilang=$rSlip['jumlah'];
				$blng=terbilang($terbilang,2)." rupiah";
			$pdf->SetFont('Arial','',6);	
			$pdf->Cell(24,4,'Terbilang',0,0,'L');
			$pdf->Cell(1,4,": ",0,0,'L');
				$pdf->MultiCell(70,4,$blng,0,'L');
			$pdf->SetFont('Arial','I',5);
			$pdf->Cell(95,4,'Note: This is computer generated system, signature is not required','T',1,'L');	
			$pdf->SetFont('Arial','',6);	
			$pdf->Ln(10);	
			if($pdf->GetY()>235 and $pdf->col<1)
				$pdf->AcceptPageBreak();
			if ($pdf->GetY()>235 and $pdf->col>0)
			   {
				//$pdf->lewat=true;
				// $pdf->AcceptPageBreak();
				//$pdf->SetY(277-$pdf->GetY());
				$r=275-$pdf->GetY();
				$pdf->Cell(80,$r,'',0,1,'L');
				
				//$pdf->ln();
			   }
			//else   
			//$pdf->lewat=false; 	
					   
			$pdf->cell(-1,3,'',0,0,'L');	
		}

	$pdf->Output();

	break;
	 
	case'excel':
	$tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
        $tab.="<tr><td  bgcolor=#DEDEDE align=center>No</td><td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namakaryawan']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['norekeningbank']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['subbagian']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nik']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tmk']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['masakerja']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['bagian']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tipekaryawan']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['statuspajak']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['functionname']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['gaji']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['pengali']."</td>";
        $tab.="<td  bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']." Bonus/THR</td></tr></thead><tbody>";
//        $sSlip="select distinct a.*,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,b.subbagian from 
//               ".$dbname.".sdm_gaji_vw a  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
//               left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
//               left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
//               where b.sistemgaji='".$jnsGaji."' and a.periodegaji='".$rPeriode['periodegaji']."' and b.lokasitugas='".$kdOrg."' and idkomponen='".$jenis."'";
if($dakarbulanan==0){
    $sSlip="select distinct a.*,COALESCE(ROUND(DATEDIFF('".$tgltahunlalu."',b.tanggalmasuk)/365.25,3),0) as masakerja,b.norekeningbank,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,b.subbagian from 
                   ".$dbname.".sdm_gaji_vw a  
                   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
                   left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
                   left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
                   where b.sistemgaji='".$jnsGaji."' and a.periodegaji='".$rPeriode['periodegaji']."' and b.lokasitugas='".$kdOrg."' and idkomponen='".$jenis."'";
}else{
    $sSlip="select distinct a.*,COALESCE(ROUND(DATEDIFF('".$tgltahunlalu."',b.tanggalmasuk)/365.25,3),0) as masakerja,b.norekeningbank,b.tipekaryawan,b.statuspajak,b.tanggalmasuk,b.nik,b.namakaryawan,b.bagian,c.namajabatan,d.nama,b.subbagian from 
                   ".$dbname.".sdm_gaji_vw a  
                   left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid 
                   left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan 
                   left join ".$dbname.".sdm_5departemen d on b.bagian=d.kode
                   where b.sistemgaji='".$jnsGaji."' and  approval_status='8' and version_type='B' and a.periodegaji='".$rPeriode['periodegaji']."' and b.lokasitugas='".$kdOrg."' and idkomponen='".$jenis."'";
}
        $qSlip=$owlPDO->query($sSlip) or die(print " Gagal: ".PDOException::getMessage());
		$qSlip->setFetchMode(PDO::FETCH_ASSOC);
        $rRow=owlBaris($qSlip);
		if($rRow==0)
        {
            exit("Error:Not Found");
        }
        while($rSlip=$qSlip->fetch())
        {
            $no+=1;
            $tab.="<tr class=rowcontent><td>".$no."</td><td>".$rSlip['namakaryawan']."</td>";
            $tab.="<td>".$rSlip['norekeningbank']."</td>";
            $tab.="<td>".$rSlip['nik']."</td>";
            $tab.="<td>".$rSlip['subbagian']."</td>";
            $tab.="<td>".$rSlip['tanggalmasuk']."</td>";
            $tab.="<td align=right>".number_format($rSlip['masakerja'],3)."</td>";
            $tab.="<td>".$rSlip['bagian']."</td>";
            $tab.="<td>".$optTipe[$rSlip['tipekaryawan']]."</td>";
            $tab.="<td>".$rSlip['statuspajak']."</td>";
            $tab.="<td>".$rSlip['namajabatan']."</td>";
            $tab.="<td align=right align=right>".number_format($kamusgaji[$rSlip['karyawanid']],0)."</td>";
            $tab.="<td align=right align=right>".number_format($kamuspengali[$rSlip['karyawanid']],3)."</td>";
            $tab.="<td align=right  bgcolor=#DEDEDE align=right>".number_format($rSlip['jumlah'],0)."</td></tr>";
        }
        $tab.="</tbody></table>";

			//echo "warning:".$strx;
			//=================================================

			// echo $tab;exit();
			$tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
			$dte=date("Hms");
                        $nop_="daftarBonusThr_".$dte;
                         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                         gzwrite($gztralala, $tab);
                         gzclose($gztralala);
                         echo "<script language=javascript1.2>
                            window.location='tempExcel/".$nop_.".xls.gz';
                            </script>";

	break;
         case'getPeriode':
            $optPeriode="<option value''>".$_SESSION['lang']['pilihdata']."</option>";
            $sPeriode="select periode from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($idAfd,1,4)."' and jenisgaji='B'";
			$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
			$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
            while($rPeriode=$qPeriode->fetch())
            {
                $optPeriode.="<option value=".$rPeriode['periode'].">".$rPeriode['periode']."</option>";
            }
            echo $optPeriode;
        break;
	default:
	break;
}
?>