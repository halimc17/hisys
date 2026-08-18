<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=checkPostGet('proses','');
$lokasitugas=$_SESSION['empl']['lokasitugas'];
$tanggal1=checkPostGet('tanggal1','');
$tanggal2=checkPostGet('tanggal2','');
$karyawanid=checkPostGet('karyawanid','');

function putertanggal($tanggal)
{
    $qwe=explode('-',$tanggal);
    return $qwe[2].'-'.$qwe[1].'-'.$qwe[0];
} 

$tangsys1=putertanggal($tanggal1);
$tangsys2=putertanggal($tanggal2);

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

//ambil query untuk data karyawan
$skaryawan="select a.karyawanid, a.bagian, b.namajabatan, a.namakaryawan, c.nama from ".$dbname.".datakaryawan a 
    left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan 
    left join ".$dbname.".sdm_5departemen c on a.bagian=c.kode 
    where a.lokasitugas like '%HO' and ((a.tanggalkeluar>= '".$tangsys1."' and a.tanggalkeluar< '".$tangsys2."') or a.tanggalkeluar='0000-00-00')
        and a.karyawanid like '%".$karyawanid."%'
    order by namakaryawan asc";    
$rkaryawan=fetchData($skaryawan);
foreach($rkaryawan as $row => $kar)
{
    $karyawan[$kar['karyawanid']]['id']=$kar['karyawanid'];
    $karyawan[$kar['karyawanid']]['nama']=$kar['namakaryawan'];
}  

// cek inputan tanggal
if(($tanggal1=="")||($tanggal2==""))
{
    echo"warning: Please fill all fields.";
    exit();
}

// cek apakah tanggal2 lebih besar
if($tangsys1>$tangsys2)
{
    echo"warning: Lower date first.";
    exit();
}

//cek max hari inputan
$tanggaltanggal = dates_inbetween($tangsys1, $tangsys2);
$jumlahhari=count($tanggaltanggal);

// CHECK HARI LIBUR
$tglLibur = array();
$str="SELECT * FROM ".$dbname.".sdm_5harilibur 
    WHERE tanggal between '".$tangsys1."' and '".$tangsys2."' ORDER BY tanggal DESC";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);    
while($bar=$res->fetch())
{
	$tglLibur[$bar->tanggal] = $bar->tanggal;
}


// KARYAWAN HADIR
$str="SELECT a.tanggalabsen as tanggal, a.jam, a.karyawanid, b.namakaryawan FROM ".$dbname.".upload_absensiho a
    LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid WHERE a.tanggalabsen between '".$tangsys1."' and '".$tangsys2."' and a.karyawanid like '%".$karyawanid."%' ORDER BY tanggalabsen DESC";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);    
while($bar=$res->fetch())
{
    if(isset($bar->karyawanid))
	{
		$karyawan[$bar->karyawanid]['id']=$bar->karyawanid;
        $karyawan[$bar->karyawanid]['nama']=$bar->namakaryawan;
        $presensi[$bar->karyawanid]['h'.$bar->tanggal]=$bar->tanggal;
    }
}


// KARYAWAN DINAS
$str="SELECT a.karyawanid, a.tanggalperjalanan, a.tanggalkembali, a.tujuan1, a.tujuan2, a.tujuan3, c.namakaryawan, a.kodeorg FROM ".$dbname.".sdm_pjdinasht a
    LEFT JOIN ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid        
    WHERE a.tanggalperjalanan <= '".$tangsys2."' and a.tanggalkembali >= '".$tangsys1."' 
        and a.karyawanid like '%".$karyawanid."%'
        and statuspersetujuan='1' and statushrd='1'
    order by a.tanggalperjalanan, a.tanggalkembali";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);    
while($bar=$res->fetch())
{
    if($bar->karyawanid>''){
		if(substr($bar->kodeorg,-2)=='HO'){
			$karyawan[$bar->karyawanid]['id']=$bar->karyawanid;
			$karyawan[$bar->karyawanid]['nama']=$bar->namakaryawan;
		}    
		$presensi[$bar->karyawanid]['dinas1']=$bar->tanggalperjalanan;
		$presensi[$bar->karyawanid]['dinas2']=$bar->tanggalkembali;
    }
}

// KARYAWAN IJIN & CUTI
$str="SELECT a.karyawanid, substr(a.darijam,1,10) as daritanggal, substr(a.sampaijam,1,10) as sampaitanggal, a.jenisijin, c.namakaryawan, c.lokasitugas, a.jenisijin 
    FROM ".$dbname.".sdm_ijin a
    LEFT JOIN ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid        
    WHERE substr(a.darijam,1,10) <= '".$tangsys2."' and substr(a.sampaijam,1,10) >= '".$tangsys1."' and stpersetujuan1 = '1' and stpersetujuanhrd = '1'
        and a.karyawanid like '%".$karyawanid."%'
    ORDER BY a.darijam, a.sampaijam";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);    
while($bar=$res->fetch())
{
	if($bar->jenisijin == 'IJINSETENGAHHARI'){
		$presensi[$bar->karyawanid]['ijinsth1']=$bar->daritanggal;
		$presensi[$bar->karyawanid]['ijinsth2']=$bar->sampaitanggal;
	}
	
	if($bar->jenisijin == 'IJINSETENGAHHARISAKIT'){
		$presensi[$bar->karyawanid]['ijinsthskt1']=$bar->daritanggal;
		$presensi[$bar->karyawanid]['ijinsthskt2']=$bar->sampaitanggal;
	}
	
	if($bar->jenisijin == 'SAKIT'){
		$presensi[$bar->karyawanid]['sakit1']=$bar->daritanggal;
		$presensi[$bar->karyawanid]['sakit2']=$bar->sampaitanggal;
	}
	
	if($bar->jenisijin == 'IJINLAIN'){
		$presensi[$bar->karyawanid]['ijin1']=$bar->daritanggal;
		$presensi[$bar->karyawanid]['ijin2']=$bar->sampaitanggal;
	}
	
	if($bar->jenisijin == 'CUTI'){
		$presensi[$bar->karyawanid]['cuti1']=$bar->daritanggal;
		$presensi[$bar->karyawanid]['cuti2']=$bar->sampaitanggal;
	}
}

// sort berdasarkan nama
if(!empty($karyawan)) foreach($karyawan as $c=>$key) {
    $sort_nama[] = $key['nama'];
}
if(!empty($karyawan))array_multisort($sort_nama, SORT_ASC, $karyawan);

if($proses=='excel'){
     $bgcolor=" bgcolor=#DEDEDE";
     $border=1;
}else{
    $bgcolor="";
     $border=0;
}


// BEGIN STREAM
$stream='';
$no=0;
$kolomtanggal=$jumlahhari+10;
$stream.="<table class=sortable cellspacing=1 border=".$border.">";
$stream.="<thead><tr class=rowtitle>";
$stream.="<td rowspan=2 align=center".$bgcolor.">".$_SESSION['lang']['nourut']."</td>";
$stream.="<td rowspan=2 align=center".$bgcolor.">".$_SESSION['lang']['namakaryawan']."</td>";
$stream.="<td colspan=".$kolomtanggal." align=center".$bgcolor.">".$_SESSION['lang']['tanggal']."</td>";
$stream.="</tr>";
$stream.="<tr class=rowtitle>";
if(!empty($tanggaltanggal))foreach($tanggaltanggal as $tang)
{
    $hari=date('D', strtotime($tang));
    if($proses=='excel'){
        $qwe=substr($tang,5,2).'/'.substr($tang,8,2);
    }else{
        $qwe=substr($tang,8,2).'/'.substr($tang,5,2);        
    }
    if($hari=='Sat'||$hari=='Sun'||$tang == $tglLibur[$tang])$qwe="<font color='#FF0000'>".$qwe."</font>";
    $stream.="<td align=center".$bgcolor.">";
    $stream.=$qwe;
    $stream.="</td>";
}    

$stream.="<td align=center".$bgcolor.">&nbsp;</td>";
$stream.="<td align=center".$bgcolor.">Hadir</td>";
$stream.="<td align=center".$bgcolor.">Dinas Luar</td>";
$stream.="<td align=center".$bgcolor.">1/2 Hari Ijin</td>";
$stream.="<td align=center".$bgcolor.">1/2 Hari Ijin Sakit</td>";
$stream.="<td align=center".$bgcolor.">Sakit</td>";
$stream.="<td align=center".$bgcolor.">Ijin</td>";
$stream.="<td align=center".$bgcolor.">Cuti</td>";
$stream.="<td align=center".$bgcolor.">Libur</td>";
$stream.="<td align=center".$bgcolor.">Alpa</td>";

$stream.="</tr></thead>";
$stream.="<tbody>";
if(!empty($karyawan))
	foreach($karyawan as $kar)
	{
		$no+=1;
		
		$hadir=0;
		$dinasluar=0;
		$ijinsth=0;
		$ijinsthskt=0;
		$sakit=0;
		$ijin=0;
		$cuti=0;		
		$libur=0;
		$alpa=0;
		$stream.="<tr class=rowcontent>";
		$stream.="<td align=right>".number_format($no).".</td>";    
		$stream.="<td>".$kar['nama']."</td>";    
		
		if(!empty($tanggaltanggal))
			foreach($tanggaltanggal as $tang)
			{
				$hari=date('D', strtotime($tang));
				$pres='A';
				
				//Absen Finger Print
				if(isset($presensi[$kar['id']]['h'.$tang])){
					$pres = "H";
				}
				
				//Perjalanan Dinas
				if(isset($presensi[$kar['id']]['dinas1']))
				{
					if(($presensi[$kar['id']]['dinas1']<=$tang)&&($presensi[$kar['id']]['dinas2']>=$tang))
					{
						$pres='DL';
					}
				}
				
				//Ijin Setengah Hari
				if(isset($presensi[$kar['id']]['ijinsth1']))
				{
					if(($presensi[$kar['id']]['ijinsth1']<=$tang)&&($presensi[$kar['id']]['ijinsth2']>=$tang))
					{
						$pres='1/2i';
					}
				}
				
				//Ijin Setengah Hari Sakit
				if(isset($presensi[$kar['id']]['ijinsthskt1']))
				{
					if(($presensi[$kar['id']]['ijinsthskt1']<=$tang)&&($presensi[$kar['id']]['ijinsthskt2']>=$tang))
					{
						$pres='1/2s';
					}
				}
				
				//Sakit
				if(isset($presensi[$kar['id']]['sakit1']))
				{
					if(($presensi[$kar['id']]['sakit1']<=$tang)&&($presensi[$kar['id']]['sakit2']>=$tang))
					{
						$pres='S';
					}
				}
				
				//Ijin
				if(isset($presensi[$kar['id']]['ijin1']))
				{
					if(($presensi[$kar['id']]['ijin1']<=$tang)&&($presensi[$kar['id']]['ijin2']>=$tang))
					{
						$pres='I';
					}
				}
				
				//Cuti
				if(isset($presensi[$kar['id']]['cuti1']))
				{
					if(($presensi[$kar['id']]['cuti1']<=$tang)&&($presensi[$kar['id']]['cuti2']>=$tang))
					{
						$pres='CT';
					}
				}
				
				if($hari=='Sat'||$hari=='Sun'||$tang==$tglLibur[$tang])
				{
					$bgcolor=" bgcolor='#FFCCCC'";
					if($pres=='A'){
						$pres='Lbr';
					}
					if(isset($presensi[$kar['id']]['dinas1']))
					{
						if(($presensi[$kar['id']]['dinas1']<=$tang)&&($presensi[$kar['id']]['dinas2']>=$tang))
						{
							$pres='Lbr';
						}
					}
					//Ijin Setengah Hari
					if(isset($presensi[$kar['id']]['ijinsth1']))
					{
						if(($presensi[$kar['id']]['ijinsth1']<=$tang)&&($presensi[$kar['id']]['ijinsth2']>=$tang))
						{
							$pres='Lbr';
						}
					}
					
					//Ijin Setengah Hari Sakit
					if(isset($presensi[$kar['id']]['ijinsthskt1']))
					{
						if(($presensi[$kar['id']]['ijinsthskt1']<=$tang)&&($presensi[$kar['id']]['ijinsthskt2']>=$tang))
						{
							$pres='Lbr';
						}
					}
					
					//Sakit
					if(isset($presensi[$kar['id']]['sakit1']))
					{
						if(($presensi[$kar['id']]['sakit1']<=$tang)&&($presensi[$kar['id']]['sakit2']>=$tang))
						{
							$pres='Lbr';
						}
					}
					
					//Ijin
					if(isset($presensi[$kar['id']]['ijin1']))
					{
						if(($presensi[$kar['id']]['ijin1']<=$tang)&&($presensi[$kar['id']]['ijin2']>=$tang))
						{
							$pres='Lbr';
						}
					}
					
					//Cuti
					if(isset($presensi[$kar['id']]['cuti1']))
					{
						if(($presensi[$kar['id']]['cuti1']<=$tang)&&($presensi[$kar['id']]['cuti2']>=$tang))
						{
							$pres='Lbr';
						}
					}
				}
				else
				{
					$bgcolor="";
				}
				
				if($pres=='H')$hadir+=1;
				if($pres=='DL')$dinasluar+=1;
				if($pres=='1/2i')$ijinsth+=1;
				if($pres=='1/2s')$ijinsthskt+=1;
				if($pres=='S')$sakit+=1;
				if($pres=='I')$ijin+=1;
				if($pres=='CT')$cuti+=1;
				if($pres=='Lbr')$libur+=1;
				if($pres=='A')$alpa+=1;
				
				$stream.="<td valign=top align=center".$bgcolor.">".$pres."</td>";    
			}
			
			$stream.="<td align=right>&nbsp;</td>";
			$stream.="<td align=right>".$hadir."</td>";
			$stream.="<td align=right>".$dinasluar."</td>";
			$stream.="<td align=right>".$ijinsth."</td>";
			$stream.="<td align=right>".$ijinsthskt."</td>";
			$stream.="<td align=right>".$sakit."</td>";
			$stream.="<td align=right>".$ijin."</td>";
			$stream.="<td align=right>".$cuti."</td>";
			$stream.="<td align=right>".$libur."</td>";
			$stream.="<td align=right>".$alpa."</td>";
			$stream.="</tr>";    
	} 
	
$stream.="</tbody></table>";
if($_SESSION['language']=='ID')
{
	$stream.="Bila karyawan tertentu tidak/muncul, harap dipastikan data Lokasi Tugas-nya dan telah terdaftar PIN Fingerprint-nya.</br>";
	$stream.="Hanya Ijin/Cuti yang telah disetujui oleh atasan dan HRD yang ditampilkan. Cuti Sabtu/Minggu tidak dihitung.</br>";
}
else
{
	$stream.="If any employee not listed, please make sure duty location of the employee and fingerprint has been registred.</br>";
	$stream.="For leave data, only approved leave are displayed. Leave on Saturday and Sunday are not counted.</br>";
}


switch($proses)
{
	case'preview':
		echo $stream;
	break;
	
	case'pdf':
	
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
				global $tanggal1;				
				global $tanggal2;				
				global $karyawanid;				
				global $tangsys1;				
				global $tangsys2;				
				global $tanggaltanggal;				
				global $jumlahhari;				
				$cols=247.5;

				# Alamat & No Telp
				$arrHead = setheadreport('');
						
				$width = $this->w - $this->lMargin - $this->rMargin;
				$height = 15;
				$path=$arrHead['logo'];
				$this->Image($path,$this->lMargin,($this->tMargin-12),0,55);
				$this->SetFont('Arial','B',9);
				$this->SetFillColor(255,255,255);	
				$this->SetX(110);   
				$this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
				$this->SetX(110); 		
				$this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
				$this->SetX(110); 			
				$this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
				$this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
				$this->Ln();

				$this->SetFont('Arial','B',10);
				$this->Cell(($width)-5,$height,$_SESSION['lang']['rkpAbsen'].' HO','',0,'C');
				$this->Ln();
				$this->Cell(($width)-5,$height,$_SESSION['lang']['periode']." : ". $tanggal1." s.d. ". $tanggal2,'',0,'C');
				$this->Ln();
				$this->Ln();
				$this->SetFont('Arial','B',7);
				$this->SetFillColor(220,220,220);

				$this->Cell(2/100*$width,$height,'No','TRL',0,'C',1);
				$this->Cell(7.3/100*$width,$height,$_SESSION['lang']['namakaryawan'],'TRL',0,'C',1);		
				$this->Cell(2.7/100*$width*$jumlahhari,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
				if($_SESSION['language']=='ID'){
					$this->Cell(2.7/100*$width,$height,'Hadir','TRL',0,'C',1);	
					$this->Cell(2.7/100*$width,$height,'Telat','TRL',0,'C',1);	
					$this->Cell(2.7/100*$width,$height,'Dinas','TRL',0,'C',1);
				}else{

					$this->Cell(2.7/100*$width,$height,'Present','TRL',0,'C',1);	
					$this->Cell(2.7/100*$width,$height,'Late','TRL',0,'C',1);	
					$this->Cell(2.7/100*$width,$height,'Duty','TRL',0,'C',1);
				 }

			
				$this->Ln();
				$this->Cell(2/100*$width,$height,'','BRL',0,'C',1);
				$this->Cell(7.3/100*$width,$height,'','BRL',0,'C',1);		
				if(!empty($tanggaltanggal))foreach($tanggaltanggal as $tang)
				{
					$hari=date('D', strtotime($tang));
					$qwe=substr($tang,8,2);
					if($hari=='Sat'||$hari=='Sun')$this->SetTextColor(255,0,0);
					else $this->SetTextColor(0,0,0);
					$this->Cell(2.7/100*$width,$height,$qwe,1,0,'C',1);	
				}
				$this->Cell(2.7/100*$width,$height,'','BRL',0,'C',1);	
				$this->Cell(2.7/100*$width,$height,'','BRL',0,'C',1);	
				$this->Cell(2.7/100*$width,$height,'','BRL',0,'C',1);	
				$this->Ln();
			}

			function Footer()
			{
				$this->SetY(-15);
				$this->SetFont('Arial','I',8);
				$this->Cell(10,10,'Page '.$this->PageNo()." Print Time:".date('Y-m-d H:i:s')." By:".$_SESSION['empl']['name'],0,0,'L');
			}
		}

$pdf=new PDF('L','pt','Legal');
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
$height = 12;
$pdf->AddPage();
$pdf->SetFillColor(255,255,255);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',7);

$no=0;

if(!empty($karyawan))foreach($karyawan as $kar)
{
    $hadir=0;
    $telat=0;
    $dinas=0;
//    $cuti=0;
    $no+=1;

    // LINE PERTAMA
    $pdf->Cell(2/100*$width,$height,$no,'TRL',0,'R',1);
    $pdf->Cell(7.3/100*$width,$height,$kar['nama'],'TRL',0,'L',1);
    if(!empty($tanggaltanggal))foreach($tanggaltanggal as $tang)
    {    
        $hari=date('D', strtotime($tang));
        $pres='';
        if(isset($presensi[$kar['id']]['ijin1'])){
            if(($presensi[$kar['id']]['ijin1']<=$tang)&&($presensi[$kar['id']]['ijin2']>=$tang)){
                if($hari!='Sat'&&$hari!='Sun')$pres=$presensi[$kar['id']]['x'.$presensi[$kar['id']]['ijin1']];
                if($hari!='Sat'&&$hari!='Sun')$cuti+=1;
            }
        }

        if(isset($presensi[$kar['id']]['dinas1'])){
            if(($presensi[$kar['id']]['dinas1']<=$tang)&&($presensi[$kar['id']]['dinas2']>=$tang)){
                $pres='DINAS';
            }
        }

        if(isset($presensi[$kar['id']]['m'.$tang])||isset($presensi[$kar['id']]['k'.$tang])){
            $ontime=true; // buat deteksi allday telat/nggak
            $ontime2=true; // buat deteksi dateng telat/nggak
            if(isset($presensi[$kar['id']]['m'.$tang])){
                $pres=substr($presensi[$kar['id']]['m'.$tang],0,5);                
                if(($tang>='2013-07-09')and($tang<='2013-08-08')){              // puasa 2013
                    if(substr($presensi[$kar['id']]['m'.$tang],0,5)<='07:30'){ // masuk ontime

                    }else{
                        $ontime=false;
                        $ontime2=false;
                    }
                }else
                if(($tang>='2014-06-30')and($tang<='2014-07-25')){              // puasa 2014
                    if(substr($presensi[$kar['id']]['m'.$tang],0,5)<='07:30'){ // masuk ontime

                    }else{
                        $ontime=false;
                        $ontime2=false;
                    }
                }else
                {
                    if(substr($presensi[$kar['id']]['m'.$tang],0,5)<='08:00'){ // masuk ontime

                    }else{
                        $ontime=false;
                        $ontime2=false;
                    }
                }    
            } else $ontime=false;
            if(isset($presensi[$kar['id']]['k'.$tang])){
                if(($tang>='2013-07-09')and($tang<='2013-08-08')){              // puasa 2013
                    if(substr($presensi[$kar['id']]['k'.$tang],0,5)>='16:00'){ // pulang ontime

                    }else{
                        $ontime=false;
                    }            
                }else
                if(($tang>='2014-06-30')and($tang<='2014-07-25')){              // puasa 2014
                    if(substr($presensi[$kar['id']]['k'.$tang],0,5)>='16:00'){ // pulang ontime

                    }else{
                        $ontime=false;
                    }            
                }else
                if($tang=='2013-10-14'){                                        // idul adha 2013 -1
                    if(substr($presensi[$kar['id']]['k'.$tang],0,5)>='15:00'){ // pulang ontime

                    }else{
                        $ontime=false;
                    }            
                }else{
                    if(substr($presensi[$kar['id']]['k'.$tang],0,5)>='17:00'){ // pulang ontime
                }else{
                    $ontime=false;
                } 
                }
            } else $ontime=false;


            if($ontime)$hadir+=1; else $telat+=1;
            if($ontime2)$pdf->SetTextColor(0,0,0); else $pdf->SetTextColor(255,0,0);
        }

        if($hari=='Sat'||$hari=='Sun'){
            $pdf->SetFillColor(255,224,224);
            if($pres=='')$pres=' ';
        }else{
            $pdf->SetFillColor(255,255,255);
        }

        if($pres=='DINAS')$dinas+=1;

        if($pres=='')$mangkir+=1;

        $pdf->Cell(2.7/100*$width,$height,$pres,'TRL',0,'L',1);
    }
    $pdf->SetFillColor(255,255,255);
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(2.7/100*$width,$height,$hadir,'TRL',0,'R',1);
    $pdf->Cell(2.7/100*$width,$height,$telat,'TRL',0,'R',1);
    $pdf->Cell(2.7/100*$width,$height,$dinas,'TRL',0,'R',1);
//    $stream.="<td align=right>".$cuti."</td>";
//    $stream.="<td align=right>".$dinas."</td>";
    $pdf->Ln();

    // LINE KEDUA
    $pdf->Cell(2/100*$width,$height,'','BRL',0,'R',1);
    $pdf->Cell(7.3/100*$width,$height,$jabakar[$kar['id']],'BRL',0,'L',1);
    if(!empty($tanggaltanggal))foreach($tanggaltanggal as $tang)
    {            
        $pres='';
        if(isset($presensi[$kar['id']]['k'.$tang])){
            $ontime=true;
            $pres.=substr($presensi[$kar['id']]['k'.$tang],0,5);
            if(($tang>='2013-07-09')and($tang<='2013-08-08')){              // puasa 2013
                if(substr($presensi[$kar['id']]['k'.$tang],0,5)>='16:00'){ // pulang ontime

                }else{
                    $ontime=false;
                }
            }else
            if(($tang>='2014-06-30')and($tang<='2014-07-25')){              // puasa 2014
                if(substr($presensi[$kar['id']]['k'.$tang],0,5)>='16:00'){ // pulang ontime

                }else{
                    $ontime=false;
                }
            }else
            {
                if(substr($presensi[$kar['id']]['k'.$tang],0,5)>='17:00'){ // pulang ontime

                }else{
                    $ontime=false;
                }
            }
            if($ontime)$pdf->SetTextColor(0,0,0); else $pdf->SetTextColor(255,0,0);
        }

        $hari=date('D', strtotime($tang));
        if($hari=='Sat'||$hari=='Sun')$pdf->SetFillColor(255,224,224); else $pdf->SetFillColor(255,255,255);
        $pdf->Cell(2.7/100*$width,$height,$pres,'BRL',0,'L',1);
    }
    $pdf->SetFillColor(255,255,255);
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(2.7/100*$width,$height,'','BRL',0,'R',1);
    $pdf->Cell(2.7/100*$width,$height,'','BRL',0,'R',1);
    $pdf->Cell(2.7/100*$width,$height,'','BRL',0,'R',1);
    $pdf->Ln();        
}    
$stream.=".</br>";
$stream.=".</br>";
$stream.="</br>";
$stream.=".</br>";   

if($_SESSION['language']=='ID'){
    $pdf->Cell($width,$height,'Bila karyawan tertentu tidak/muncul, harap dipastikan data Lokasi Tugas-nya dan telah terdaftar PIN Fingerprint-nya.','T',0,'L',1);
$pdf->Ln();                
$pdf->Cell($width,$height,'Hanya Ijin/Cuti yang telah disetujui oleh atasan dan HRD yang ditampilkan. Cuti Sabtu/Minggu tidak dihitung.',0,0,'L',1);
$pdf->Ln();                
$pdf->Cell($width,$height,'Bila karyawan tidak absen masuk/pulang maka dianggap telat.',0,0,'L',1);
$pdf->Ln();                
$pdf->Cell($width,$height,'Absen masuk 00:00 - 11:59. Absen pulang 12:00 - 23:59.',0,0,'L',1);
}else{
$pdf->Cell($width,$height,'If any employee not listed, please make sure duty location of the employee and fingerprint has been registred.',T,0,'L',1);
$pdf->Ln();                
$pdf->Cell($width,$height,'For leave data, only approved leave are displayed. Leave on Saturday and Sunday are not counted.',0,0,'L',1);
$pdf->Ln();                
$pdf->Cell($width,$height,'If employee out earlier, then system will recognize it as late.',0,0,'L',1);
$pdf->Ln();                
$pdf->Cell($width,$height,'In between 00:00 - 11:59. Out between 12:00 - 23:59.',0,0,'L',1);
$pdf->Ln();                
}
        $pdf->Output();

        break;
        case'excel':

                        $stream.="<br><br>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
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
                        $nop_="RekapAbsen_HO_".$tangsys1."_".$tangsys2;
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
        while($rPeriode=$qPeriode->fetch()){
                $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
        }
        $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sSub="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kdUnit."'  order by namaorganisasi asc";
        $qSub=$owlPDO->query($sSub) or die(print " Gagal: ".PDOException::getMessage());
        $qSub->setFetchMode(PDO::FETCH_ASSOC);         
        while($rSub=$qSub->fetch())
        {
             $optAfd.="<option value='".$rSub['kodeorganisasi']."'>".$rSub['namaorganisasi']."</option>";
        }
        echo $optAfd."####".$optPeriode;
        break;

        default:
        break;
}
?>