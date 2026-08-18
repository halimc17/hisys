<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$kdOrg='';
$sistemGaji='';

$proses = checkPostGet('proses','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdOrg = checkPostGet('kdOrg','');
$periodeGaji = checkPostGet('periode','');
$thn=explode("-",$periodeGaji);
$tipeKary = checkPostGet('tipeKary','');
$sistemGaji = checkPostGet('sistemGaji','');
$namakar=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmTipe=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$tglLahir=makeOption($dbname, 'datakaryawan', 'karyawanid,tanggallahir');
$dNik=makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$dJamsos=makeOption($dbname, 'datakaryawan', 'karyawanid,jms');



if($kdOrg=='')
{
    $orgnya=$lksiTgs;
}
else
{
    $orgnya=$kdOrg;
}

$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
      $tipeOrg=$optTipe[$orgnya];
      
if($tipeOrg=='PABRIK')
{ 
    $bpjsOrg='PABRIK';
} 
else 
{
    $bpjsOrg='KEBUN';
}
    
    
    $iKerja="select * from ".$dbname.".sdm_5bpjs where lokasibpjs='".$bpjsOrg."' and jenisbpjs='ketanagakerjaan' ";
	$nKerja=$owlPDO->query($iKerja) or die(print " Gagal: ".PDOException::getMessage());
	$nKerja->setFetchMode(PDO::FETCH_ASSOC);
    $dKerja=  $nKerja->fetch();
        $bpjsKerja=$dKerja['bebanperusahaan'];

        
if($kdOrg=='')$kdOrg=$lksiTgs;

        if($tipeKary!='')
        {
            $where.="  and tipekaryawan='".$tipeKary."'";
        }
        else
        {
             $where.=" and tipekaryawan NOT IN ('5','6')";
        }
        if($sistemGaji!='')
        {
            $where.=" and sistemgaji='".$sistemGaji."'";
            $addTmbh=" and sistemgaji='".$sistemGaji."'";
        }

$sGapok="select sum(jumlah) as gapok,karyawanid from ".$dbname.".sdm_5gajipokok 
         WHERE  idkomponen in (1,2,30,31) and tahun='".$thn[0]."' group by karyawanid order by karyawanid asc";
$qGapok=$owlPDO->query($sGapok) or die(print " Gagal: ".PDOException::getMessage());
$qGapok->setFetchMode(PDO::FETCH_ASSOC);
while($rGapok=$qGapok->fetch())
{
    $dtGapok[$rGapok['karyawanid']]=$rGapok['gapok'];
    
}
$sJams="select distinct a.karyawanid,jumlah, b.lokasitugas from ".$dbname.".sdm_gaji a 
        left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
        where a.idkomponen='3' and a.kodeorg like '%".$kdOrg."%' and periodegaji='".$periodeGaji."'  ".$where."";
$qJams=$owlPDO->query($sJams) or die(print " Gagal: ".PDOException::getMessage());
$qJams->setFetchMode(PDO::FETCH_ASSOC);
$data = array();
while($rJams=$qJams->fetch())
{
    $dtJams[$rJams['karyawanid']]=$rJams['jumlah'];
    $data[$rJams['karyawanid']]=$rJams['karyawanid'];
    $datalok[$rJams['karyawanid']]=$rJams['lokasitugas'];
}
 $cekDt=count($data);
if($cekDt==0)
{
 exit("Error : Data Not Found");
}
switch($proses)
{
	case'preview':

            if($periodeGaji=='')
            {
                exit("Error : Period required");
            }
        
	$tab.="<table cellspacing='1' border='0' class='sortable'>
	<thead class=rowheader>
	<tr>
	<td align=center>No</td>
	<td align=center>".$_SESSION['lang']['lokasitugas']."</td>
	<td align=center>".$_SESSION['lang']['nik']."</td>
	<td align=center>".$_SESSION['lang']['nama']."</td>
	<td align=center>".$_SESSION['lang']['tanggallahir']."</td>
	<td align=center>".$_SESSION['lang']['tipekaryawan']."</td>
	<td align=center>".$_SESSION['lang']['nokpj']."</td>
	<td align=center>".$_SESSION['lang']['gaji']."</td>
	<td align=center>".$_SESSION['lang']['potongan']." ".$_SESSION['lang']['karyawan']."</td>
	<td align=center>".$_SESSION['lang']['perusahaan']."</td></tr></thead>
	<tbody>";
        
        
        
        
	foreach($data as $brsData)
        {
            $no+=1;
           
                $sDtip="select distinct tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$brsData."'";
				$qDtip=$owlPDO->query($sDtip) or die(print " Gagal: ".PDOException::getMessage());
				$qDtip->setFetchMode(PDO::FETCH_ASSOC);
                $rDtip=$qDtip->fetch();
            $tab.="<tr class=rowcontent>
            <td align=center>".$no."</td>
            <td>".$datalok[$brsData]."</td>
			<td>".$dNik[$brsData]."</td>
			<td>".$namakar[$brsData]."</td>
            
            <td>".$tglLahir[$brsData]."</td>  
            <td>".$nmTipe[$rDtip['tipekaryawan']]."</td>
            
            <td>".$dJamsos[$brsData]."</td>
            <td align=right>".number_format(@$dtGapok[$brsData],2)."</td>
            <td align=right>".number_format(@$dtJams[$brsData],2)."</td>
            <td align=right>".number_format(@($dtGapok[$brsData]*$bpjsKerja)/100,2)."</td>
            </tr>";
			@$totalpotkarya +=$dtJams[$brsData];	
			@$totalpotpt +=($dtGapok[$brsData]*$bpjsKerja)/100;
        }
	$tab.=" <tr>
			<td colspan=8><b>TOTAL</b></td>
			<td align=right><b>".number_format(@$totalpotkarya,2)."</b></td>
			<td align=right><b>".number_format(@$totalpotpt,2)."</b></td>
			
			</tr>";
	
	$tab.="</tbody></table>";
        echo $tab;
	break;
        
        
        
        
        
	case'pdf':
//        
//	$kdeOrg=$_GET['kdeOrg'];
//	$kdOrg=$_GET['kdOrg'];
//	$periodeGaji=$_GET['periode'];
//        $tipeKary=$_GET['tipeKary'];
       
	if($periodeGaji=='')
        {
            exit("Error:Period required");
        }
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
        {
            if($kdOrg!='')
            {
               // $where=" lokasitugas='".$kdOrg."'";
                $where=" lokasitugas!=''";
            }
            else
            {
                exit("Error : Working unit required");
            }
        }	
        else
        {
            $kdOrg=$_SESSION['empl']['lokasitugas'];
           // $where=" lokasitugas='".$kdOrg."'";
            $where=" lokasitugas!=''";
        }
        if($tipeKary!='')
        {
            $where.=" and tipekaryawan='".$tipeKary."'";
        }
        if($sistemGaji!='')
        {
            $where.=" and sistemgaji='".$sistemGaji."'";
            $addTmbh=" and sistemgaji='".$sistemGaji."'";
        }
	
	
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++
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
				global $periodeGaji;
				global $kdOrg;
				global $tglLahir;
				global $jmlHari;
				global $namakar;
                                global $tipeKary;
				global $sistemGaji;
                                global $nmTipe;
                                global $dNik;
                                global $dJamsos;
                                global $addTmbh;
			
				$cols=247.5;
			    # Alamat & No Telp
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
                
                $this->SetFont('Arial','B',10);
				// $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['dafJams']." ".$sistemGaji,'',0,'L');
				// $this->Ln();
				$this->Ln();
				
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['dafJams']),'',0,'C');
				$this->Ln();
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." :".$periodeGaji,'',0,'C');
				$this->Ln();
				$this->Ln();
              	$this->SetFont('Arial','B',7);
                $this->SetFillColor(220,220,220);
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['nik'],1,0,'C',1);
				$this->Cell(20/100*$width,$height,$_SESSION['lang']['namakaryawan'],1,0,'C',1);	
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['tanggallahir'],1,0,'C',1);	
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['tipekaryawan'],1,0,'C',1);	
					
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['nokpj'],1,0,'C',1);	
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['gaji'],1,0,'C',1);	
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['potongan']." ".$_SESSION['lang']['karyawan'],1,0,'C',1);	
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['perusahaan'],1,1,'C',1);
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',7);
		$subtot=array();
	
                foreach($data as $brsData)
                {
                    $no+=1;
                    
                        $sDtip="select distinct tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$brsData."'";
						$qDtip=$owlPDO->query($sDtip) or die(print " Gagal: ".PDOException::getMessage());
						$qDtip->setFetchMode(PDO::FETCH_ASSOC);
                        $rDtip=$qDtip->fetch();
                        $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                        $pdf->Cell(8/100*$width,$height,$dNik[$brsData],1,0,'L',1);
						$pdf->Cell(20/100*$width,$height,$namakar[$brsData],1,0,'L',1);	
                        $pdf->Cell(10/100*$width,$height,tanggalnormal($tglLahir[$brsData]),1,0,'C',1);	
                        $pdf->Cell(10/100*$width,$height,$nmTipe[$rDtip['tipekaryawan']],1,0,'L',1);	
                        	
                        $pdf->Cell(15/100*$width,$height,$dJamsos[$brsData],1,0,'L',1);	
                        $pdf->Cell(10/100*$width,$height,number_format(@$dtGapok[$brsData],2),1,0,'R',1);	
                        $pdf->Cell(15/100*$width,$height,number_format(@$dtJams[$brsData],2),1,0,'R',1);	
                        $pdf->Cell(10/100*$width,$height,number_format(@($dtGapok[$brsData]*$bpjsKerja)/100,2),1,1,'R',1);
						
						@$totalpotkarya +=$dtJams[$brsData];	
						@$totalpotpt +=($dtGapok[$brsData]*$bpjsKerja)/100;
					
                }

						$pdf->Cell(76/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
						$pdf->Cell(15/100*$width,$height,number_format($totalpotkarya,2),1,0,'R',1);
						$pdf->Cell(10/100*$width,$height,number_format($totalpotpt,2),1,1,'R',1);
						
        $pdf->Output();

	break;
	case'excel':
		if($cekDt <= 0){
			echo $_SESSION['lang']['datanotfound'];
			exit();
		}
        $periodeGaji=$_GET['periode'];
	
        $tipeKary=$_GET['tipeKary'];
        $sistemGaji=$_GET['sistemGaji'];
        if($periodeGaji=='')
        {
            exit("Error:Period required");
        }
          
	$tab.="<table cellspacing='1' border='1' class='sortable'>
	<thead class=rowheader>
	<tr>
	<td bgcolor=#DEDEDE align=center>No</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['lokasitugas']."</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nik']."</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nama']."</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggallahir']."</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tipekaryawan']."</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nokpj']."</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['gaji']."</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['potongan']." ".$_SESSION['lang']['karyawan']."</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['perusahaan']."</td></tr></thead>
	<tbody>";
	foreach($data as $brsData)
        {
            $no+=1;
                $sDtip="select distinct tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$brsData."'";
				$qDtip=$owlPDO->query($sDtip) or die(print " Gagal: ".PDOException::getMessage());
				$qDtip->setFetchMode(PDO::FETCH_ASSOC);
                $rDtip=$qDtip->fetch();
            $tab.="<tr class=rowcontent>
            <td>".$no."</td>
			<td>".$datalok[$brsData]."</td>
            <td>".$dNik[$brsData]."</td>
			<td>".$namakar[$brsData]."</td>
            <td>".$tglLahir[$brsData]."</td>  
            <td>".$nmTipe[$rDtip['tipekaryawan']]."</td>
            
            <td>".$dJamsos[$brsData]."</td>
            <td align=right>".number_format(@$dtGapok[$brsData],2)."</td>
            <td align=right>".number_format(@$dtJams[$brsData],2)."</td>
            <td align=right>".number_format(@($dtGapok[$brsData]*$bpjsKerja)/100,2)."</td>
            </tr>";
			@$totalpotkarya +=$dtJams[$brsData];	
			@$totalpotpt +=($dtGapok[$brsData]*$bpjsKerja)/100;
            
        }
		$tab.=" <tr>
			<td colspan=8><b>TOTAL</b></td>
			<td align=right><b>".number_format(@$totalpotkarya,2)."</b></td>
			<td align=right><b>".number_format(@$totalpotpt,2)."</b></td>
			
			</tr>";
        
	
	$tab.="</tbody></table>";
			//echo "warning:".$strx;
			//=================================================

			
			$tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
			
			$nop_="daftar_jamsostek_".$kdOrg;
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
			fclose($handle);
			}
	break;
	case'getTgl':
	if($periode!='')
	{
		$tgl=$periode;
		$tanggal=$tgl[0]."-".$tgl[1];
	}
	elseif($period!='')
	{
		$tgl=$period;
		$tanggal=$tgl[0]."-".$tgl[1];
	}
        if($kdUnit=='')
        {
            $kdUnit=$_SESSION['empl']['lokasitugas'];
        }
	$sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($kdUnit,0,4)."' and periode='".$tanggal."' ";
	$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
	$qTgl->setFetchMode(PDO::FETCH_ASSOC);
	$rTgl=$qTgl->fetch();
	echo tanggalnormal($rTgl['tanggalmulai'])."###".tanggalnormal($rTgl['tanggalsampai']);
	break;
	case'getKry':
	$optKry="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	if(strlen($kdeOrg)>4)
	{
		$where=" subbagian='".$kdeOrg."'";
	}
	else
	{
		$where=" lokasitugas='".$kdeOrg."' and (subbagian='0' or subbagian is null)";
	}
	$sKry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where ".$where." order by namakaryawan asc";
	$qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
	$qKry->setFetchMode(PDO::FETCH_ASSOC);
	while($rKry=$qKry->fetch())
	{
		$optKry.="<option value=".$rKry['karyawanid'].">".$rKry['namakaryawan']."</option>";
	}
	$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$kdeOrg."'";
	$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
	$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
	while($rPeriode=$qPeriode->fetch())
	{
		$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
	}
	//echo $optPeriode;
	echo $optKry."###".$optPeriode;
	break;
	case'getPeriode':
	$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$kdUnit."'";
	$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
	$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
	while($rPeriode=$qPeriode->fetch())
	{
		$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
	}
	echo $optPeriode;
	break;
	default:
	break;
}
?>