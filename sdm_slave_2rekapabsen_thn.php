<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
//$arrThn="##kdeOrg2##periodThn##periodThnSmp##sistemGaji3";

$proses=checkPostGet('proses','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdOrg=checkPostGet('kdeOrg2','');
$tgl1=checkPostGet('periodThn','');
$tgl2=checkPostGet('periodThnSmp','');
$tipeKary=checkPostGet('tipeKary2','');
$sistemGaji=checkPostGet('sistemGaji3','');
$optTmk=makeOption($dbname, 'datakaryawan', 'karyawanid,tanggalmasuk');
$nik=makeOption($dbname,'datakaryawan','karyawanid,nik');
$optDept=makeOption($dbname, 'sdm_5departemen', 'kode,nama');
$thn=explode("-",$tgl1);
$thn2=explode("-",$tgl2);
if($thn[0]!=$thn2[0])
{
    exit("Error:Tahun tidak boleh beda");
}

//echo $sistemGaji;
$tanggal1=$tgl1."-01";
$tanggal2=$tgl2."-01";
function datediff2($tgl1, $tgl2){
$tgl1 = strtotime($tgl1);
$tgl2 = strtotime($tgl2);
$diff_secs = abs($tgl1 - $tgl2);
$base_year = min(date("Y", $tgl1), date("Y", $tgl2));
$diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
return array( "years" => date("Y", $diff) - $base_year, "months_total" => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1, "months" => date("n", $diff) - 1, "days_total" => floor($diff_secs / (3600 * 24)), "days" => date("j", $diff) - 1, "hours_total" => floor($diff_secs / 3600), "hours" => date("G", $diff), "minutes_total" => floor($diff_secs / 60), "minutes" => (int) date("i", $diff), "seconds_total" => $diff_secs, "seconds" => (int) date("s", $diff) );
}

$totBln=datediff2($tanggal1,$tanggal2);
//exit("error".$totBln[months_total]);
for($ard=1;$ard<=$totBln['months_total'];$ard++)
{
    if(strlen($ard)<2)
    {
        $bar="0".$ard;
    }
    $test[]=@$thn1[0]."-".$bar;
}
//ambil query untuk data karyawan
	if($kdOrg!='')
	{
		$kodeOrg=$kdOrg;
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
		{
			$where="  lokasitugas in ('".$kodeOrg."')";
		}
		else
		{
			if(strlen($kdOrg)>4)
			{			
				$where="  subbagian='".$kdOrg."'";		
			}
			else
			{
				$where="  lokasitugas='".$kdOrg."' and (subbagian='0' or subbagian is null or subbagian='')";
			}
		}
	}
	else
	{
		$kodeOrg=$_SESSION['empl']['lokasitugas'];
		$where="  lokasitugas='".$kodeOrg."'";
	}
        if($tipeKary!='')
        {
            $where.=" and tipekaryawan='".$tipeKary."'";
        }
	if($sistemGaji=='All')$wherez="";        
	if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
	if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";        

$sGetKary="select a.karyawanid,b.namajabatan,a.namakaryawan,c.nama,d.tipe,a.subbagian from ".$dbname.".datakaryawan a 
           left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
           left join ".$dbname.".sdm_5departemen c on a.bagian=c.kode
           left join ".$dbname.".sdm_5tipekaryawan d on a.tipekaryawan=d.id
           where ".$where." ".$wherez." order by namakaryawan asc";    
//echo $sGetKary; exit;
$rGetkary=fetchData($sGetKary);
foreach($rGetkary as $row => $kar)
{
   // $resData[$kar['karyawanid']][]=$kar['karyawanid'];
    $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
    $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
    $nmBagian[$kar['karyawanid']]=$kar['subbagian'];
    $nmTipe[$kar['karyawanid']]=$kar['tipe'];
}  
$bln1=explode("-",$tgl1);
$bln2=explode("-",$tgl2);

	$resData[]=array();
	$hasilAbsn[]=array();
        //get karyawan
	
if(strlen($kodeOrg)>4)
{
    $dimanaPnjng=" kodeorg='".$kodeOrg."'";
}
else
{
    $dimanaPnjng=" substring(kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
}
		
			$sAbsn="select count(absensi) as total,absensi,karyawanid,substr(tanggal,1,4) as periode from ".$dbname.".sdm_absensidt 
                                where substr(tanggal,1,7) between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng." group by absensi,karyawanid";
			//exit("Error".$sAbsn);
			$rAbsn=fetchData($sAbsn);
			foreach ($rAbsn as $absnBrs =>$resAbsn)
			{
				if(!is_null($resAbsn['absensi']))
				{
			            $hasilAbsn[$resAbsn['karyawanid']][$resAbsn['periode']][$resAbsn['absensi']]=$resAbsn['total'];
				    $resData[$resAbsn['karyawanid']][]=$resAbsn['karyawanid'];
				}
			}
			
			$sKehadiran="select count(absensi) as total,absensi,karyawanid,substr(tanggal,1,4) as periode from ".$dbname.".kebun_kehadiran_vw 
                            where jhk>0 and substr(tanggal,1,7) between  '".$tgl1."' and '".$tgl2."' and substring(unit,1,4)='".substr($kodeOrg,0,4)."' group by absensi,karyawanid";
			//exit("Error".$sKehadiran);
			$rkehadiran=fetchData($sKehadiran);
			foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
			{	
				if($resKhdrn['absensi']!='')
				{
					setIt($hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['periode']][$resKhdrn['absensi']],0);
			        $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['periode']][$resKhdrn['absensi']]+=$resKhdrn['total'];
				    $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
				}
			
			}
			$sPrestasi="select substr(b.tanggal,1,4) as periode,a.jumlahhk,a.nik from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                            where upahkerja>0 and b.notransaksi like '%PNN%' and substr(b.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and substr(b.tanggal,1,7) between '".$tgl1."' and '".$tgl2."'";
                       // exit("Error".$sPrestasi);
			$rPrestasi=fetchData($sPrestasi);
			foreach ($rPrestasi as $presBrs =>$resPres)
			{
				setIt($hasilAbsn[$resPres['nik']][$resPres['periode']]['H'],0);
				$hasilAbsn[$resPres['nik']][$resPres['periode']]['H']+=1;
				$resData[$resPres['nik']][]=$resPres['nik'];
			}
			
// ambil pengawas                        
$dzstr="SELECT substr(a.tanggal,1,4) as periode,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL
    union select substr(a.tanggal,1,4) as periode,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
	setIt($hasilAbsn[$dzbar->nikmandor][$dzbar->periode]['H'],0);
    $hasilAbsn[$dzbar->nikmandor][$dzbar->periode]['H']+=1;
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil administrasi                       
$dzstr="SELECT substr(a.tanggal,1,4) as periode,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL
    union select substr(a.tanggal,1,4) as periode,keranimuat FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    @$hasilAbsn[$dzbar->nikmandor][$dzbar->periode]['H']+=1;
    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
//    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
//    'absensi'=>'H');
//    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}         







###ambil absensi  sipil => vhc_splht

//mandor
$str="SELECT substr(a.tanggal,1,4) as periode,mandor,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.mandor=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    @$hasilAbsn[$bar->mandor][$bar->periode]['H']+=1;
    $resData[$bar->mandor][]=$bar->mandor;
}

//mandor1
$str="SELECT substr(a.tanggal,1,4) as periode,mandor1,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.mandor1=c.karyawanid
    where a.tanggal between '".$tgl1."-01' and LAST_DAY('".$tgl2."-15') and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor1][$bar->periode]['H']+=1;
    $resData[$bar->mandor1][]=$bar->mandor1;
}

//krani
$str="SELECT substr(a.tanggal,1,4) as periode,krani,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.krani=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->krani][$bar->periode]['H']+=1;
    $resData[$bar->krani][]=$bar->krani;
}

#ambil dari vhc_spl_kehadiran_vw
$sSpl="select notransaksi,tanggal,nik,kodeorg from ".$dbname.".vhc_spl_kehadiran_vw where 
        substr(tanggal,1,7) between   '".$tgl1."' and '".$tgl2."' and kodeorg='".substr($kodeOrg,0,4)."'
		 group by nik,tanggal";
$qSpl=$owlPDO->query($sSpl) or die(print " Gagal: ".PDOException::getMessage());
$qSpl->setFetchMode(PDO::FETCH_ASSOC);
while($rSpl=$qSpl->fetch())
{
	$hasilAbsn[$rSpl['nik']][substr($rSpl['tanggal'],0,4)]['H']+=1;
	$resData[$rSpl['nik']][]=$rSpl['nik'];
}

/*
$sKehadiran="select count(absensi) as total,absensi,karyawanid,substr(tanggal,1,4) as periode from ".$dbname.".kebun_kehadiran_vw 
		where jhk>0 and substr(tanggal,1,7) between  '".$tgl1."' and '".$tgl2."' and substring(unit,1,4)='".substr($kodeOrg,0,4)."' 
		group by absensi,karyawanid";
		echo $sKehadiran;
			//exit("Error".$sKehadiran);
			$rkehadiran=fetchData($sKehadiran);
			foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
			{	
				if($resKhdrn['absensi']!='')
				{
					setIt($hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['periode']][$resKhdrn['absensi']],0);
			        $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['periode']][$resKhdrn['absensi']]+=$resKhdrn['total'];
				    $resData[$resKhdrn['karyawanid']][]=$resKhdrn['karyawanid'];
				}
			
			}

*/

// ambil traksi                       
$dzstr="SELECT tanggal,idkaryawan FROM ".$dbname.".vhc_runhk
    where substr(tanggal,1,7) between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".$kodeOrg."%'
    ";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
}
      

/*
echo"<pre>";
print_r($hasilAbsn);
echo"</pre>";
*/
	  
switch($proses)
{
	case'preview':
      
	$sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
	$qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
	$qAbsen->setFetchMode(PDO::FETCH_ASSOC);
	$jmAbsen=owlBaris($qAbsen);
	$colSpan=intval($jmAbsen)+2;
	echo"<table cellspacing='1' border='0' class='sortable' width=100%>
	<thead class=rowheader>
	<tr>
	<td align=center>No</td>
	<td align=center>".$_SESSION['lang']['subbagian']."</td>
	<td align=center>".$_SESSION['lang']['nik2']."</td>
	<td align=center>".$_SESSION['lang']['nama']."</td>
        <td align=center>".$_SESSION['lang']['tipekaryawan']."</td>
        
	<td align=center>".$_SESSION['lang']['jabatan']."</td>
        <td align=center>".$_SESSION['lang']['tmk']."</td>";
	$klmpkAbsn=array();
	while($rKet=$qAbsen->fetch())
	{
		$klmpkAbsn[]=$rKet;
		echo"<td width=20px align=center>".$rKet['kodeabsen']."</td>";
	}
	echo"
	<td>".$_SESSION['lang']['total']."</td></tr></thead>
	<tbody>";
	
       
	$brt=array();
	$lmit=count($klmpkAbsn);
	$a=0;
	foreach($resData as $hslBrs => $hslAkhir)
	{	
			if(!empty($hslAkhir[0]) and !empty($namakar[$hslAkhir[0]]))
			{

				$no+=1;
				echo"<tr class=rowcontent><td>".$no."</td>";
				echo"
				<td>".$nmBagian[$hslAkhir[0]]."</td>
				<td>".$nik[$hslAkhir[0]]."</td>
				<td>".$namakar[$hslAkhir[0]]."</td>
				<td>".$nmTipe[$hslAkhir[0]]."</td>
                                
				<td>".$nmJabatan[$hslAkhir[0]]."</td>
				<td>".$optTmk[$hslAkhir[0]]."</td>
				";
				foreach($klmpkAbsn as $brsKet =>$hslKet)
				{
					setIt($hasilAbsn[$hslAkhir[0]][$thn[0]][$hslKet['kodeabsen']],0);
					setIt($subtot[$hslAkhir[0]]['total'],0);
                    echo"<td align=right>".$hasilAbsn[$hslAkhir[0]][$thn[0]][$hslKet['kodeabsen']]."</td>";
					$subtot[$hslAkhir[0]]['total']+=$hasilAbsn[$hslAkhir[0]][$thn[0]][$hslKet['kodeabsen']];
				}
				echo"<td width=5px align=right>".$subtot[$hslAkhir[0]]['total']."</td>";
				
				echo"</tr>";
			}	
	}
	

	echo"</tbody></table>";
	break;
	case'pdf':
        
	//ambil query untuk tanggal kehadiran
	
	$sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
	$qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
	$jmAbsen=owlBaris($qAbsen);
	$colSpan=intval($jmAbsen)+2;
	
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
                global $period;
                global $periode;
                global $kdOrg;
                global $kdeOrg;
                global $tgl1;
                global $tgl2;
                global $where;
                global $jmlHari;
                global $test;
                global $klmpkAbsn;
                global $tipeKary;
                global $sistemGaji;
                global $dimanaPnjng;
                global $hasilAbsn;
                global $thn;
                global $nmBagian;
                global $owlPDO;
				
				
				$jmlHari=$jmlHari*1.5;
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
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['rkpAbsen']." ".$sistemGaji,'',0,'L');
				$this->Ln();
				$this->Ln();
				
				$this->Cell($width,$height,strtoupper("Rekapitulasi Absensi Karyawan"),'',0,'C');
				$this->Ln();
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." : ". substr(tanggalnormal($tgl1),1,7)." s.d. ".substr(tanggalnormal($tgl2),1,7),'',0,'C');
				$this->Ln();
				$this->Ln();
                                $this->SetFont('Arial','B',7);
                                $this->SetFillColor(220,220,220);
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['nama'],1,0,'C',1);	
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['bagian'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['jabatan'],1,0,'C',1);	
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['tmk'],1,0,'C',1);
				$sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
				$qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
				$qAbsen->setFetchMode(PDO::FETCH_ASSOC);
				while($rAbsen=$qAbsen->fetch())
				{
					$klmpkAbsn[]=$rAbsen;
					$this->Cell(2/100*$width,$height,$rAbsen['kodeabsen'],1,0,'C',1);
				}
				$this->Cell(5/100*$width,$height,$_SESSION['lang']['jumlah'],1,1,'C',1);
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('L','pt','Legal');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',7);
		$subtot=array();
//		
	$brt=array();
	$lmit=count($klmpkAbsn);
	$a=0;
	foreach($resData as $hslBrs => $hslAkhir)
	{	
			if(!empty($hslAkhir[0]) and !empty($namakar[$hslAkhir[0]]))
			{
				$no+=1;
				$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
				$pdf->Cell(15/100*$width,$height,strtoupper($namakar[$hslAkhir[0]]),1,0,'L',1);	
                                $pdf->Cell(10/100*$width,$height,$nmBagian[$hslAkhir[0]],1,0,'L',1);
				$pdf->Cell(10/100*$width,$height,strtoupper($nmJabatan[$hslAkhir[0]]),1,0,'L',1);
                                $pdf->Cell(8/100*$width,$height,$optTmk[$hslAkhir[0]],1,0,'L',1);
				foreach($klmpkAbsn as $brsKet =>$hslKet)
				{
					setIt($hasilAbsn[$hslAkhir[0]][$thn[0]][$hslKet['kodeabsen']],0);
					setIt($subtot[$hslAkhir[0]]['total'],0);
					$pdf->Cell(2/100*$width,$height,$hasilAbsn[$hslAkhir[0]][$thn[0]][$hslKet['kodeabsen']],1,0,'C',1);	
					$subtot[$hslAkhir[0]]['total']+=$hasilAbsn[$hslAkhir[0]][$thn[0]][$hslKet['kodeabsen']];
				}
				$pdf->Cell(5/100*$width,$height,$subtot[$hslAkhir[0]]['total'],1,1,'R',1);
				$subtot[$hslAkhir[0]]['total']=0;
			}	
			
	}
		
			
        $pdf->Output();

	break;
	case'excel':
	
	$sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
	$qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
	$qAbsen->setFetchMode(PDO::FETCH_ASSOC);
	$jmAbsen=owlBaris($qAbsen);
	$colSpan=intval($jmAbsen)+2;
	setIt($jmlHari,0);
	$colatas=$jmlHari+$colSpan+3;
	$stream.="<table border='0'><tr><td colspan='".$colatas."' align=center>".strtoupper("Rekapitulasi Absensi Karyawan")." ".$sistemGaji."</td></tr>
	<tr><td colspan='".$colatas."' align=center>".strtoupper($_SESSION['lang']['periode'])." :". substr(tanggalnormal($tgl1),1,7)." s.d. ". substr(tanggalnormal($tgl2),1,7)."</td></tr><tr><td colspan='".$colatas."'>&nbsp;</td></tr></table>";
	$stream.="<table cellspacing='1' border='1' class='sortable'>
	<thead class=rowheader>
	<tr>
	<td bgcolor=#DEDEDE align=center>No</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nama']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tipekaryawan']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['bagian']."</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jabatan']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tmk']."</td>";
	$klmpkAbsn=array();
	
	while($rKet=$qAbsen->fetch())
	{
		$klmpkAbsn[]=$rKet;
		$stream.="<td bgcolor=#DEDEDE align=center width=10px>".$rKet['kodeabsen']."</td>";
	}
	$stream.="
	<td bgcolor=#DEDEDE align=center>Jumlah</td></tr></thead>
	<tbody>";
	
       
	$brt=array();
	$lmit=count($klmpkAbsn);
	$a=0;
	foreach($resData as $hslBrs => $hslAkhir)
	{	
			if(!empty($hslAkhir[0]) and !empty($namakar[$hslAkhir[0]]))
			{

				$no+=1;
				$stream.="<tr class=rowcontent><td>".$no."</td>";
				$stream.="
				<td>".$namakar[$hslAkhir[0]]."</td>
                                <td>".$nmTipe[$hslAkhir[0]]."</td>
                                <td>".$nmBagian[$hslAkhir[0]]."</td>
				<td>".$nmJabatan[$hslAkhir[0]]."</td>
				<td>".$optTmk[$hslAkhir[0]]."</td>";
				foreach($klmpkAbsn as $brsKet =>$hslKet)
				{
					setIt($hasilAbsn[$hslAkhir[0]][$thn[0]][$hslKet['kodeabsen']],0);
					setIt($subtot[$hslAkhir[0]]['total'],0);
                    $stream.="<td align=right>".$hasilAbsn[$hslAkhir[0]][$thn[0]][$hslKet['kodeabsen']]."</td>";
					$subtot[$hslAkhir[0]]['total']+=$hasilAbsn[$hslAkhir[0]][$thn[0]][$hslKet['kodeabsen']];
				}
				$stream.="<td width=5px align=right>".$subtot[$hslAkhir[0]]['total']."</td>";
				
				$stream.="</tr>";
			}	
	}
        $stream.="</tbody></table>";
	
	
	
	
			//echo "warning:".$strx;
			//=================================================

			
			$stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];
			$art="";
			if(!empty($period))
			{
				$art=$period;
				$art=$art[1].$art[0];
			}
			if(!empty($periode))
			{
				$art=$periode;
				$art=$art[1].$art[0];
			}
			if(!empty($kdeOrg))
			{
				$kodeOrg=$kdeOrg;
			}
			if(!empty($kdOrg))
			{
				$kodeOrg=$kdOrg;
			}
			$nop_="RekapAbsen".$art."__".$kodeOrg;
			if(strlen($stream)>0)
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
			if(!fwrite($handle,$stream))
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
		$dmna.=" and periode='".$tanggal."'";
	}
	elseif($period!='')
	{
		$tgl=$period;
		$tanggal=$tgl[0]."-".$tgl[1];
		$dmna.=" and periode='".$tanggal."'";
	}
	if($sistemGaji!='')
	{
		$dmna.=" and jenisgaji='".substr($sistemGaji,0,1)."'";
	}
        if($kdUnit=='')
        {
            $kdUnit=$_SESSION['empl']['lokasitugas'];
        }
	$sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($kdUnit,0,4)."' ".$dmna." ";
	//echo"warning".$sTgl;
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
		$where=" lokasitugas='".$kdeOrg."' and (subbagian='0' or subbagian is null or subbagian='')";
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
	if($periodeGaji!='')
	{
		$were=" kodeorg='".$kdUnit."' and periode='".$periodeGaji."' and jenisgaji='".$sistemGaji."'";
	}
	else
	{
		$were=" kodeorg='".$kdUnit."'";
	}
	$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where ".$were."";
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