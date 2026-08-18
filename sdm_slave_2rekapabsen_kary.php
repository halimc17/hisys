<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdOrg=checkPostGet('kdeOrg','');
//$tgl1=tanggalsystem(checkPostGet('tgl1',''));
//$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$tgl_1=tanggalsystemn(checkPostGet('tgl_1',''));
$tgl_2=tanggalsystemn(checkPostGet('tgl_2',''));








$periodeGaji=checkPostGet('period','');
$periode=explode('-',checkPostGet('periode','-'));
$idKry=checkPostGet('idKry','');


$a="select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg='".$lksiTgs."' and periode='".$periodeGaji."'";
$b=$owlPDO->query($a) or die(print " Gagal: ".PDOException::getMessage());
$b->setFetchMode(PDO::FETCH_ASSOC);
$c=$b->fetch();
	$tgl_1=$c['tanggalmulai'];
	$tgl_2=$c['tanggalsampai'];


function dates_inbetween($date1, $date2){

    $day = 60*60*24;

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();

    $dates_array[] = date('Y-m-d',$date1);
   
    for($x = 1; $x < $days_diff; $x++){
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }

    $dates_array[] = date('Y-m-d',$date2);
    if($date1==$date2){
        $dates_array = array();
        $dates_array[] = date('Y-m-d',$date1);        
    }

    return $dates_array;
}
switch($proses)
{
	case'preview':
	//ambil query untuk data karyawan
	if($kdOrg!='')
	{
		$kodeOrg=$kdOrg;
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
		{
		
			$where="  lokasitugas in (".$kodeOrg.")";
		}
		else
		{
			if(strlen($kdOrg)>4)
			{
				
				$where="  lokasitugas='".substr($kdOrg,0,4)."'";
			}
			else
			{
				//$where="  lokasitugas='".$kdOrg."'";
				$where="  lokasitugas='".$kdOrg."' and (subbagian='' or subbagian='0' or subbagian is null)";
//                                                                     ni buat apa ya? kalo pake ginian malah ga muncul                                
			}
		}
	}
	else
	{
		echo"warning : Unit Tidak Boleh Kosong";
		exit();
	}
	if(($tgl_1!='')&&($tgl_2!=''))
	{
		$tgl1=$tgl_1;
		$tgl2=$tgl_2;
	}
	
	$test = dates_inbetween($tgl1, $tgl2);
	if(($tgl2=="")&&($tgl1==""))
	{
		echo"warning: Tanggal Mulai dan Tanggal Sampai tidak boleh kosong";
		exit();
	}

	$jmlHari=count($test);
	//cek max hari inputan
	if($jmlHari>31)
	{
		echo"warning : Range tanggal tidak valid";
		exit();
	}
        if($idKry!='')
	{
		$where.=" and karyawanid=".$idKry."";	
	}
        else
        {
            echo"warning : Nama Karyawan Tidak Boleh Kosong";
            exit();
        }
	$sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
	$qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
	$qAbsen->setFetchMode(PDO::FETCH_ASSOC);
	$jmAbsen=owlBaris($qAbsen);
	$colSpan=intval($jmAbsen)+2;
	echo"<table cellspacing='1' border='0' class='sortable' width=100%>
	<thead class=rowheader>
	<tr>
	<td align=center>No</td>
	<td align=center>".$_SESSION['lang']['nik2']."</td>
	<td align=center>".$_SESSION['lang']['nama']."</td>
	<td align=center>".$_SESSION['lang']['jabatan']."</td>";
	$klmpkAbsn=array();
	foreach($test as $ar => $isi)
	{
		//exit("Error".$isi);
		echo"<td width=5px align=center>".substr($isi,8,2)."</td>";
	}
	while($rKet=$qAbsen->fetch())
	{
		$klmpkAbsn[]=$rKet;
		echo"<td width=10px>".$rKet['kodeabsen']."</td>";
	}
	echo"
	<td>Jumlah</td></tr></thead>
	<tbody>";
	
	$nik=makeOption($dbname,'datakaryawan','karyawanid,nik');
	
	$resData[]=array();
	$hasilAbsn[]=array();
	$sGetKary="select a.karyawanid,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where   ".$where." order by namakaryawan asc";
	
	$rGetkary=fetchData($sGetKary);
	foreach($rGetkary as $row => $kar)
        {
            $resData[$kar['karyawanid']][]=$kar['karyawanid'];
            $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
            $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
        }  

	
        
			
			
			$sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                            where b.notransaksi like '%PNN%' and substr(b.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and b.tanggal between '".$tgl1."' and '".$tgl2."' and a.nik=".$idKry."";
			//exit("Error".$sPrestasi);
			$rPrestasi=fetchData($sPrestasi);
			foreach ($rPrestasi as $presBrs =>$resPres)
			{
//				if($rPrestasi['jumlahhk']>0)
//				{
//					$hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
//					'absensi'=>'H');
//					//'tanggal'=>$resPres['tanggal'],
//					//'karyawanid'=>$resPres['nik']);	
//				}
//				else
//				{
                            if($resPres['upahkerja']>0)
                            {
                            
                                $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                'absensi'=>'H');
                            }
                            else
                            {
                                $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
                                'absensi'=>'HK0');
                            }
					//'tanggal'=>$resPres['tanggal'],
					//'karyawanid'=>$resPres['nik']);	
				//}
			}
//		echo"<pre>";
//		print_r($hasilAbsn);
//		echo"</pre>";exit();
                        
                        
                        
                        $sKehadiran="select jhk,absensi,tanggal,karyawanid from ".$dbname.".kebun_kehadiran_vw 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and karyawanid=".$idKry." and unit='".substr($kdOrg,0,4)."'  and notransaksi not like '%BOR%'";
		
                        //exit("Error".$sKehadiran);
			$rkehadiran=fetchData($sKehadiran);
			foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
			{	
				
                            if($resKhdrn['jhk']>0)
                            {
                                $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                              'absensi'=>$resKhdrn['absensi']);
                            }
                            else
                            {
                                    $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                              'absensi'=>'HK0');
                            }
                            
			}
                        
                        
                        
                        
// ambil pengawas                        
$dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(b.kodeorg,1,4) = '".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL
    union select tanggal,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(b.kodeorg,1,4) = '".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
//    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}




// ambil administrasi                       
$dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(b.kodeorg,1,4) = '".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL
    union select tanggal,keranimuat FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(b.kodeorg,1,4) = '".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
//    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}







###ambil absensi  sipil => vhc_splht

//mandor
$str="SELECT tanggal,mandor,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.mandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->mandor][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->mandor][]=$bar->mandor;
}

//mandor1
$str="SELECT tanggal,mandor1,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.mandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor1][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->mandor1][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->mandor1][]=$bar->mandor1;
}

//krani
$str="SELECT tanggal,krani,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.krani=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->krani][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->krani][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->krani][]=$bar->krani;
}

#ambil dari vhc_spl_kehadiran_vw
$sSpl="select notransaksi,tanggal,nik,kodeorg from ".$dbname.".vhc_spl_kehadiran_vw where 
       tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".substr($kodeOrg,0,4)."'";
$qSpl=$owlPDO->query($sSpl) or die(print " Gagal: ".PDOException::getMessage());
$qSpl->setFetchMode(PDO::FETCH_ASSOC);
while($rSpl=$qSpl->fetch()){
    $hasilAbsn[$rSpl['nik']][$rSpl['tanggal']][]=array(
    'absensi'=>'H');
    @$notran[$rSpl['nik']][$rSpl['tanggal']].='SIPIL:'.$rSpl['notransaksi'].'__';
    $resData[$rSpl['nik']][]=$rSpl['nik'];
}


// ambil traksi                       
$dzstr="SELECT tanggal,idkaryawan FROM ".$dbname.".vhc_runhk
    where tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".$kodeOrg."%'
     and idkaryawan='".$idKry."'";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
}


                $sAbsn="select absensi,tanggal,karyawanid from ".$dbname.".sdm_absensidt where tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".$kodeOrg."' and karyawanid=".$idKry."";
			//exit("Error".$sAbsn);
			$rAbsn=fetchData($sAbsn);
			foreach ($rAbsn as $absnBrs =>$resAbsn)
			{
				$hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array(
                'absensi'=>$resAbsn['absensi']);
                //'tanggal'=>$resAbsn['tanggal'],
              //  'karyawanid'=>$resAbsn['karyawanid']); 
			}

          
	$brt=array();
	$lmit=count($klmpkAbsn);
	$a=0;
	foreach($resData as $hslBrs => $hslAkhir)
	{	
			if(!empty($hslAkhir[0]))
			{   setIt($namakar[$hslAkhir[0]],'');setIt($nmJabatan[$hslAkhir[0]],'');
				$no+=1;
				echo"<tr class=rowcontent><td>".$no."</td>";
				echo"
				<td>".$nik[$hslAkhir[0]]."</td>
				<td>".$namakar[$hslAkhir[0]]."</td>
				<td>".$nmJabatan[$hslAkhir[0]]."</td>
				";
				foreach($test as $barisTgl =>$isiTgl)
				{
                                    setIt($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],0);
                                    setIt($brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']],0);
					if($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']!='H')
					{
                                            if($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']=='HK0')
                                            {
                                                echo"<td bgcolor=pink>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
                                            }
                                            else
                                            {
                                                echo"<td><font color=red>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</font></td>";
                                            }
						
					}
					else
					{
                                            if($hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']=='0')
                                            {
                                                echo"<td></td>";
                                            }
                                            else
                                            {
                                                echo"<td>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
                                            }
						
					}
					$brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
				}
				
				foreach($klmpkAbsn as $brsKet =>$hslKet)
				{setIt($subtot[$hslAkhir[0]]['total'],0);setIt($brt[$hslAkhir[0]][$hslKet['kodeabsen']],0);
					if($hslKet['kodeabsen']!='H')
					{
						echo"<td width=5px align=right><font color=red>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</font></td>";	
					}
					else
					{
						echo"<td width=5px align=right>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</td>";	
					}
					$subtot[$hslAkhir[0]]['total']+=$brt[$hslAkhir[0]][$hslKet['kodeabsen']];
				}	
				echo"<td width=5px align=right>".$subtot[$hslAkhir[0]]['total']."</td>";
				$subtot['total']=0;
				echo"</tr>";
			}	
	}
	
	//echo"warning:";
/*	echo"<pre>";
	print_r($hasilAbsn);
	echo"</pre>";
*/	echo"</tbody></table>";
	break;
        
        

        
	case'pdf':
	
	//$kdOrg=$_GET['kdeOrg'];
	//$tgl1=tanggalsystem($_GET['tgl1']);
	//$tgl2=tanggalsystem($_GET['tgl2']);
	//$tgl_1=tanggalsystem($_GET['tgl_1']);
	//$tgl_2=tanggalsystem($_GET['tgl_2']);
	//$period=explode('-',$_GET['period']);
	//$periode=explode('-',$_GET['periode']);
	//$idKry=$_GET['idKry'];
	if(($tgl_1!='')&&($tgl_2!=''))
	{
		$tgl1=$tgl_1;
		$tgl2=$tgl_2;
	}
	
	$test = dates_inbetween($tgl1, $tgl2);
	if(($tgl2=="")&&($tgl1==""))
	{
		echo"warning: Tanggal Mulai dan Tanggal Sampai tidak boleh kosong";
		exit();
	}

	$jmlHari=count($test);
	//cek max hari inputan
	if($jmlHari>31)
	{
		echo"warning:Range tanggal tidak valid";
		exit();
	}
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
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['rkpAbsen'],'',0,'L');
				$this->Ln();
				$this->Ln();
				
				$this->Cell($width,$height,strtoupper("Rekapitulasi Absensi Karyawan"),'',0,'C');
				$this->Ln();
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2),'',0,'C');
				$this->Ln();
				$this->Ln();
              	$this->SetFont('Arial','B',7);
                $this->SetFillColor(220,220,220);
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(13/100*$width,$height,$_SESSION['lang']['nama'],1,0,'C',1);		
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['jabatan'],1,0,'C',1);	
			
				//$this->Cell($jmlHari/100*$width,$height-10,$_SESSION['lang']['tanggal'],1,0,'C',1);
				$this->GetX();
				$this->SetY($this->GetY());
				
				$this->SetX($this->GetX()+$cols);

				foreach($test as $ar => $isi)
				{
					$this->Cell(1.5/100*$width,$height,substr($isi,8,2),1,0,'C',1);	
					//$cols+=1.5;
					//$this->SetX($this->GetX()+$cols);
					//$akhir=$this->SetX($this->GetX()+$cols);
					$akhirX=$this->GetX();
				}	
				$this->SetY($this->GetY());
				$this->SetX($akhirX);
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
       if($kdOrg!='')
        {
		$kodeOrg=$kdOrg;
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
		{
			$where="  lokasitugas in (".$kodeOrg.")";
		}
		else
		{
			if(strlen($kdOrg)>4)
			{
				
				$where="  lokasitugas='".substr($kdOrg,0,4)."'";
			}
			else
			{
				$where="  lokasitugas='".$kdOrg."' and (subbagian='0' or subbagian is null or subbagian='')";
			}
		}
	}
	else
	{
		echo"warning:Unit Tidak Boleh Kosong";
		exit();
	}
	if($idKry!='')
	{
		$where.=" and karyawanid=".$idKry."";	
	}
	$resData[]=array();
	$hasilAbsn[]=array();
	$sGetKary="select a.karyawanid,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 and  ".$where." order by namakaryawan asc";
	
	$rGetkary=fetchData($sGetKary);
	foreach($rGetkary as $row => $kar)
        {
          $resData[$kar['karyawanid']][]=$kar['karyawanid'];
          $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
              $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
        }  

		
			
			
			
			$sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.notransaksi like '%PNN%' and substr(b.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and b.tanggal between '".$tgl1."' and '".$tgl2."' and  a.nik=".$idKry."";
			//exit("Error".$sPrestasi);
			$rPrestasi=fetchData($sPrestasi);
			foreach ($rPrestasi as $presBrs =>$resPres)
			{
//				if($rPrestasi['jumlahhk']>0)
//				{
//					$hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
//					'absensi'=>'H');
//					//'tanggal'=>$resPres['tanggal'],
//					//'karyawanid'=>$resPres['nik']);	
//				}
//				else
//				{
                            
                            
                            if($resPres['upahkerja']>0)
                            {
                                $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
					'absensi'=>'H');
                            }
                            else
                            {
                                $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
					'absensi'=>'HK0');
                            }
					
					//'tanggal'=>$resPres['tanggal'],
					//'karyawanid'=>$resPres['nik']);	
				//}
			}
//		echo"<pre>";
//		print_r($hasilAbsn);
//		echo"</pre>";exit();
                        
                        
                        $sKehadiran="select jhk,absensi,tanggal,karyawanid from ".$dbname.".kebun_kehadiran_vw 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and  karyawanid=".$idKry." and unit='".substr($kdOrg,0,4)."'  and notransaksi not like '%BOR%'";
			//exit("Error".$sKehadiran);
			$rkehadiran=fetchData($sKehadiran);
			foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
			{	
                            
                            
                            if($resKhdrn['jhk']>0)
                            {
                                    $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                              'absensi'=>$resKhdrn['absensi']);
                            }
                            else
                            {
                                $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
                              'absensi'=>'HK0');
                            }
			//	'tanggal'=>$resKhdrn['tanggal'],
			//	'karyawanid'=>$resKhdrn['karyawanid']);
			}
                        
                        
                        
// ambil pengawas                        
$dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL
    union select tanggal,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
//    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil administrasi                       
$dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL
    union select tanggal,keranimuat FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
//    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}       





###ambil absensi  sipil => vhc_splht

//mandor
$str="SELECT tanggal,mandor,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.mandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->mandor][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->mandor][]=$bar->mandor;
}

//mandor1
$str="SELECT tanggal,mandor1,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.mandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor1][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->mandor1][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->mandor1][]=$bar->mandor1;
}

//krani
$str="SELECT tanggal,krani,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.krani=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->krani][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->krani][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->krani][]=$bar->krani;
}

#ambil dari vhc_spl_kehadiran_vw
$sSpl="select notransaksi,tanggal,nik,kodeorg from ".$dbname.".vhc_spl_kehadiran_vw where 
       tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".substr($kodeOrg,0,4)."'";
$qSpl=$owlPDO->query($sSpl) or die(print " Gagal: ".PDOException::getMessage());
$qSpl->setFetchMode(PDO::FETCH_ASSOC);
while($rSpl=$qSpl->fetch()){
    $hasilAbsn[$rSpl['nik']][$rSpl['tanggal']][]=array(
    'absensi'=>'H');
    @$notran[$rSpl['nik']][$rSpl['tanggal']].='SIPIL:'.$rSpl['notransaksi'].'__';
    $resData[$rSpl['nik']][]=$rSpl['nik'];
}


        

// ambil traksi                       
$dzstr="SELECT tanggal,idkaryawan FROM ".$dbname.".vhc_runhk
    where tanggal between '".$tgl1."' and '".$tgl2."' and notransaksi like '%".$kodeOrg."%'
     and idkaryawan='".$idKry."'";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->idkaryawan][$dzbar->tanggal][]=array(
    'absensi'=>'H');
    $resData[$dzbar->idkaryawan][]=$dzbar->idkaryawan;
}


$sAbsn="select absensi,tanggal,karyawanid from ".$dbname.".sdm_absensidt 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".$kodeOrg."' and  karyawanid=".$idKry." ";
			//exit("Error".$sAbsn);
			$rAbsn=fetchData($sAbsn);
			foreach ($rAbsn as $absnBrs =>$resAbsn)
			{
				$hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array(
                'absensi'=>$resAbsn['absensi']);
                //'tanggal'=>$resAbsn['tanggal'],
              //  'karyawanid'=>$resAbsn['karyawanid']); 
			}

                    
                        

                        
	$brt=array();
	$lmit=count($klmpkAbsn);
	$a=0;
	foreach($resData as $hslBrs => $hslAkhir)
	{	
			if(!empty($hslAkhir[0]))
			{
				$no+=1;
				$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
				$pdf->Cell(13/100*$width,$height,strtoupper($namakar[$hslAkhir[0]]),1,0,'L',1);		
				$pdf->Cell(10/100*$width,$height,strtoupper($nmJabatan[$hslAkhir[0]]),1,0,'L',1);	
				foreach($test as $barisTgl =>$isiTgl)
				{
					$pdf->Cell(1.5/100*$width,$height,$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi'],1,0,'C',1);	
					$akhirX=$pdf->GetX();
					@$brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
				}
				$a=0;
				for(;$a<$lmit;$a++)
				{
					$pdf->Cell(2/100*$width,$height,@$brt[$hslAkhir[0]][$klmpkAbsn[$a]['kodeabsen']],1,0,'C',1);	
					@$subtot[$hslAkhir[0]]['total']+=$brt[$hslAkhir[0]][$klmpkAbsn[$a]['kodeabsen']];
				}	
				$pdf->Cell(5/100*$width,$height,$subtot[$hslAkhir[0]]['total'],1,1,'R',1);
				@$subtot[$hslAkhir[0]]['total']=0;
			}	
			
	}
		
			
        $pdf->Output();

	break;
	case'excel':
	
	//$kdOrg=$_GET['kdeOrg'];
	//$tgl1=tanggalsystem($_GET['tgl1']);
	//$tgl2=tanggalsystem($_GET['tgl2']);
	//$tgl_1=tanggalsystem($_GET['tgl_1']);
	//$tgl_2=tanggalsystem($_GET['tgl_2']);
	//$period=explode('-',$_GET['period']);
	//$periode=explode('-',$_GET['periode']);
	//$idKry=$_GET['idKry'];
	//ambil query untuk data karyawan
	if($kdOrg!='')
	{
		$kodeOrg=$kdOrg;
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
		{
			
			$where="  lokasitugas in (".$kodeOrg.")";
		}
		else
		{
			if(strlen($kdOrg)>4)
			{
				
				$where="  lokasitugas='".substr($kdOrg,0,4)."'";
			}
			else
			{
				$where="  lokasitugas='".$kdOrg."' and (subbagian='' or subbagian='0' or subbagian is null)";
			}
		}
	}
	else
	{
		echo"warning:Unit Tidak Boleh Kosong";
		exit();
	}
	if(($tgl_1!='')&&($tgl_2!=''))
	{
		$tgl1=$tgl_1;
		$tgl2=$tgl_2;
	}
	
	$test = dates_inbetween($tgl1, $tgl2);
	if(($tgl2=="")&&($tgl1==""))
	{
		echo"warning: Tanggal Mulai dan Tanggal Sampai tidak boleh kosong";
		exit();
	}

	$jmlHari=count($test);
	//cek max hari inputan
	if($jmlHari>31)
	{
		echo"warning:Range tanggal tidak valid";
		exit();
	}
	$sAbsen="select kodeabsen from ".$dbname.".sdm_5absensi order by kodeabsen";
	$qAbsen=$owlPDO->query($sAbsen) or die(print " Gagal: ".PDOException::getMessage());
	$qAbsen->setFetchMode(PDO::FETCH_ASSOC);
	$jmAbsen=owlBaris($qAbsen);
	$colSpan=intval($jmAbsen)+2;
	$colatas=$jmlHari+$colSpan+3;
	$stream.="<table border='0'><tr><td colspan='".$colatas."' align=center>".strtoupper("Rekapitulasi Absensi Karyawan")."</td></tr>
	<tr><td colspan='".$colatas."' align=center>".strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2)."</td></tr><tr><td colspan='".$colatas."'>&nbsp;</td></tr></table>";
	$stream.="<table cellspacing='1' border='1' class='sortable'>
	<thead class=rowheader>
	<tr>
	<td bgcolor=#DEDEDE align=center>No</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nama']."</td>
	<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jabatan']."</td>";
	$klmpkAbsn=array();
	foreach($test as $ar => $isi)
	{
		//exit("Error".$isi);
		$stream.="<td bgcolor=#DEDEDE align=center width=5px align=center>".substr($isi,8,2)."</td>";
	}
	while($rKet=$qAbsen->fetch())
	{
		$klmpkAbsn[]=$rKet;
		$stream.="<td bgcolor=#DEDEDE align=center width=10px>".$rKet['kodeabsen']."</td>";
	}
	$stream.="
	<td bgcolor=#DEDEDE align=center>Jumlah</td></tr></thead>
	<tbody>";
	if($idKry!='')
	{
		$where.=" and karyawanid=".$idKry."";	
	}
	
	
	$resData[]=array();
	$hasilAbsn[]=array();
	$sGetKary="select a.karyawanid,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where  ".$where." order by namakaryawan asc";
	
	$rGetkary=fetchData($sGetKary);
	foreach($rGetkary as $row => $kar)
    {
      $resData[$kar['karyawanid']][]=$kar['karyawanid'];
      $namakar[$kar['karyawanid']]=$kar['namakaryawan'];
	  $nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
    }  

		
			$sAbsn="select absensi,tanggal,karyawanid from ".$dbname.".sdm_absensidt 
                            where tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".$kodeOrg."' and karyawanid=".$idKry."";
			//exit("Error".$sAbsn);
			$rAbsn=fetchData($sAbsn);
			foreach ($rAbsn as $absnBrs =>$resAbsn)
			{
				$hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']][]=array(
                'absensi'=>$resAbsn['absensi']);
                //'tanggal'=>$resAbsn['tanggal'],
              //  'karyawanid'=>$resAbsn['karyawanid']); 
			}
			
			
			$sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.notransaksi like '%PNN%' and substr(b.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and b.tanggal between '".$tgl1."' and '".$tgl2."' and a.nik=".$idKry."";
			//exit("Error".$sPrestasi);
			$rPrestasi=fetchData($sPrestasi);
			foreach ($rPrestasi as $presBrs =>$resPres)
			{
//				if($rPrestasi['jumlahhk']>0)
//				{
//					$hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
//					'absensi'=>'H');
//					//'tanggal'=>$resPres['tanggal'],
//					//'karyawanid'=>$resPres['nik']);	
//				}
//				else
//				{
                                if($resPres['upahkerja']>0)
                                {
                                    $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
					'absensi'=>'H');   
                                }
                                else
                                {
                                    $hasilAbsn[$resPres['nik']][$resPres['tanggal']][]=array(
					'absensi'=>'HK0');
                                }
					
					//'tanggal'=>$resPres['tanggal'],
					//'karyawanid'=>$resPres['nik']);	
				//}
			}
                        
                        $sKehadiran="select jhk,absensi,tanggal,karyawanid from ".$dbname.".kebun_kehadiran_vw 
                            where tanggal between  '".$tgl1."' and '".$tgl2."'and karyawanid=".$idKry." and unit='".substr($kdOrg,0,4)."'  and notransaksi not like '%BOR%'";
			//exit("Error".$sKehadiran);
			$rkehadiran=fetchData($sKehadiran);
			foreach ($rkehadiran as $khdrnBrs =>$resKhdrn)
			{	
                            
                            if($resKhdrn['jhk']>0)
                            {
                                $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
			  'absensi'=>$resKhdrn['absensi']);
                            }
                            else
                            {
                                $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']][]=array(
			  'absensi'=>'HK0');
                            }
                            
				
			//	'tanggal'=>$resKhdrn['tanggal'],
			//	'karyawanid'=>$resKhdrn['karyawanid']);
			}
                        
//		echo"<pre>";
//		print_r($hasilAbsn);
//		echo"</pre>";exit();
                        
// ambil pengawas                        
$dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL
    union select tanggal,nikmandor1 FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
//    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor;
}

// ambil administrasi                       
$dzstr="SELECT tanggal,nikmandor FROM ".$dbname.".kebun_aktifitas a
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL
    union select tanggal,keranimuat FROM ".$dbname.".kebun_aktifitas a 
    left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
    left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg like '".$kodeOrg."%' and c.namakaryawan is not NULL";
$dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
$dzres->setFetchMode(PDO::FETCH_OBJ);
while($dzbar=$dzres->fetch())
{
    $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal][]=array(
    'absensi'=>'H');
//    $resData[$dzbar->nikmandor][]=$dzbar->nikmandor; 
}
                    





###ambil absensi  sipil => vhc_splht

//mandor
$str="SELECT tanggal,mandor,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.mandor=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->mandor][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->mandor][]=$bar->mandor;
}

//mandor1
$str="SELECT tanggal,mandor1,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.mandor1=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->mandor1][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->mandor1][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->mandor1][]=$bar->mandor1;
}

//krani
$str="SELECT tanggal,krani,a.notransaksi FROM ".$dbname.".vhc_splht a
    left join ".$dbname.".datakaryawan c on a.krani=c.karyawanid
    where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $hasilAbsn[$bar->krani][$bar->tanggal][]=array(
    'absensi'=>'H');
    @$notran[$bar->krani][$bar->tanggal].='BKM:'.$bar->notransaksi.'__';
    $resData[$bar->krani][]=$bar->krani;
}

#ambil dari vhc_spl_kehadiran_vw
$sSpl="select notransaksi,tanggal,nik,kodeorg from ".$dbname.".vhc_spl_kehadiran_vw where 
       tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".substr($kodeOrg,0,4)."'";
$qSpl=$owlPDO->query($sSpl) or die(print " Gagal: ".PDOException::getMessage());
$qSpl->setFetchMode(PDO::FETCH_ASSOC);
while($rSpl=$qSpl->fetch()){
    $hasilAbsn[$rSpl['nik']][$rSpl['tanggal']][]=array(
    'absensi'=>'H');
    @$notran[$rSpl['nik']][$rSpl['tanggal']].='SIPIL:'.$rSpl['notransaksi'].'__';
    $resData[$rSpl['nik']][]=$rSpl['nik'];
}




					
	$brt=array();
	$lmit=count($klmpkAbsn);
	$a=0;
	foreach($resData as $hslBrs => $hslAkhir)
	{	
			if(!empty($hslAkhir[0]))
			{
				$no+=1;
				$stream.="<tr><td>".$no."</td>";
				$stream.="
				<td>".$namakar[$hslAkhir[0]]."</td>
				<td>".$nmJabatan[$hslAkhir[0]]."</td>
				";
				foreach($test as $barisTgl =>$isiTgl)
				{
					$stream.="<td>".$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']."</td>";
					$brt[$hslAkhir[0]][$hasilAbsn[$hslAkhir[0]][$isiTgl][0]['absensi']]+=1;
				}
				
				foreach($klmpkAbsn as $brsKet =>$hslKet)
				{
					$stream.="<td width=5px>".$brt[$hslAkhir[0]][$hslKet['kodeabsen']]."</td>";	
					$subtot[$hslAkhir[0]]['total']+=$brt[$hslAkhir[0]][$hslKet['kodeabsen']];
				}	
				$stream.="<td width=5px>".$subtot[$hslAkhir[0]]['total']."</td>";
				$subtot['total']=0;
				$stream.="</tr>";
			}	
	}
	
	//echo"warning:";
/*	echo"<pre>";
	print_r($hasilAbsn);
	echo"</pre>";
*/	$stream.="</tbody></table>";
	
	
	
	
			//echo "warning:".$strx;
			//=================================================

			
			$stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
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
	}
	elseif($period!='')
	{
		$tgl=$period;
		$tanggal=$tgl[0]."-".$tgl[1];
	}
	$sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".$tanggal."' ";
	$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
	$qTgl->setFetchMode(PDO::FETCH_ASSOC);
	$rTgl=$qTgl->fetch();
	echo tanggalnormal($rTgl['tanggalmulai'])."###".tanggalnormal($rTgl['tanggalsampai']);
	break;
	case'getKry':
	if(strlen($kdeOrg)>4)
	{
		$where=" lokasitugas='".substr($kdeOrg,0,4)."'";
	}
	else
	{
		$where=" lokasitugas='".$kdeOrg."'";
	}
	$sKry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where ".$where." order by namakaryawan asc";
	$qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
	$qKry->setFetchMode(PDO::FETCH_ASSOC);
	while($rKry=$qKry->fetch())
	{
		$optKry.="<option value=".$rKry['karyawanid'].">".$rKry['namakaryawan']."</option>";
	}
	echo $optKry;
	break;
	default:
	break;
}
?>