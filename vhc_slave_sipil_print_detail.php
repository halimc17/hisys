<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

$proses = $_GET['proses'];
$param = $_GET;

$optNamaKary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

/** Report Prep **/
$cols = array();

# Prestasi
$col1 = 'tanggal,kodekegiatan,a.kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$cols[] = explode(',',$col1);
$query="select ".$col1." from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."'";

$data[] = fetchData($query);
$align[] = explode(",","L,L,L,R,R,R,R,R");
$length[] = explode(",","10,10,15,10,10,15,15,15");

# Kehadiran
$col2 = 'nik,absensi,jhk,umr,insentif';
$cols[] = explode(',',$col2);
$query = selectQuery($dbname,'kebun_kehadiran',$col2,
    "notransaksi='".$param['notransaksi']."'");
$data[] = fetchData($query);
$align[] = explode(",","L,L,R,R,R");
$length[] = explode(",","20,20,20,20,20");

# Pakai Material
$col3 = 'kodeorg,kodebarang,kwantitas,kwantitasha,hargasatuan';
$cols[] = explode(',',$col3);
$query = selectQuery($dbname,'kebun_pakaimaterial',$col3,
    "notransaksi='".$param['notransaksi']."'");
$data[] = fetchData($query);
$align[] = explode(",","L,L,R,R,R");
$length[] = explode(",","20,20,20,20,20");

//getNamakaryawan
$sDtKaryawn="select karyawanid,namakaryawan from ".$dbname.".datakaryawan order by namakaryawan asc";
$rData=fetchData($sDtKaryawn);
foreach($rData as $brKary =>$rNamakaryawan)
{
    $RnamaKary[$rNamakaryawan['karyawanid']]=$rNamakaryawan['namakaryawan'];
}
$title = "SIPIL";
$titleDetail = array($_SESSION['lang']['prestasi'],$_SESSION['lang']['absensi'],$_SESSION['lang']['material']);

/** Output Format **/
switch($proses) {
    case 'pdf':
        
        $pdf=new zPdfMaster('P','pt','A4');
        $pdf->_noThead=true;
        $pdf->setAttr1($title,$align,$length,array());
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',9);
        $pdf->Ln();
        $pdf->Cell($width,$height,$_SESSION['lang']['notransaksi']." : ".$param['notransaksi'],0,1,'L',1);
       
		$sPres="select t1.tanggal as tanggal, t2.kodekegiatan as kodekegiatan, t2.alokasi as alokasi, t2.total_hasilkerja as hasilkerja, t2.total_premi as upahpremi 
		from ".$dbname.".vhc_splht t1
		left join ".$dbname.".vhc_spl_prestasi t2 on t1.notransaksi = t2.notransaksi
		where t1.notransaksi = '".$param['notransaksi']."'
		order by t2.nourut DESC";
		
		// distinct sum(a.insentif) as upahpremi, sum(a.umr) as umr,sum(a.jhk) as jumlahhk,kodekegiatan,
                // tanggal,b.kodeorg,b.hasilkerja from ".$dbname.".kebun_kehadiran a left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
                // left join ".$dbname.".kebun_aktifitas c on a.notransaksi=c.notransaksi where a.notransaksi='".$param['notransaksi']."' group by a.notransaksi";
       
        $pdf->Ln();
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell($width,$height,$titleDetail[0],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
        $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['vhc_jenis_pekerjaan'],1,0,'C',1);
        $pdf->Cell(13/100*$width,$height,$_SESSION['lang']['alokasibiaya'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['hasilkerjad'],1,0,'C',1);
        $pdf->Cell(6/100*$width,$height,$_SESSION['lang']['satuan'],1,0,'C',1);
        $pdf->Cell(15/100*$width,$height,$_SESSION['lang']['upahpremi'],1,0,'C',1);
        $pdf->Cell(15/100*$width,$height,$_SESSION['lang']['umr'],1,1,'C',1);
        
        $pdf->SetFont('Arial','',7);
        $pdf->SetFillColor(255,255,255);
        $qPres=$owlPDO->query($sPres) or die(print " Gagal: ".PDOException::getMessage());
        $qPres->setFetchMode(PDO::FETCH_ASSOC);
        while($rPres=$qPres->fetch()){
			$optKegiatan = makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$rPres['kodekegiatan']."'");
			$optSatuan = makeOption($dbname,'vhc_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$rPres['kodekegiatan']."'");
			$pdf->Cell(10/100*$width,$height,tanggalnormal($rPres['tanggal']),1,0,'C',1);
			$pdf->Cell(25/100*$width,$height,@$optKegiatan[$rPres['kodekegiatan']],1,0,'L',1);
			$pdf->Cell(13/100*$width,$height,$rPres['alokasi'],1,0,'L',1);
			$pdf->Cell(10/100*$width,$height,$rPres['hasilkerja'],1,0,'R',1);
			$pdf->Cell(6/100*$width,$height,@$optSatuan[$rPres['kodekegiatan']],1,0,'C',1);
			$pdf->Cell(15/100*$width,$height,number_format($rPres['upahpremi'],0),1,0,'R',1);
			$pdf->Cell(15/100*$width,$height,0,1,1,'R',1);
		}
        
        
        $sKhdrn="select * from ".$dbname.".vhc_spl_absen where notransaksi = '".$param['notransaksi']."' order by nourut ASC";
        $qKhdrn=$owlPDO->query($sKhdrn) or die(print " Gagal: ".PDOException::getMessage());
        $qKhdrn->setFetchMode(PDO::FETCH_ASSOC);
        $pdf->Ln(30);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$titleDetail[1],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $pdf->SetFont('Arial','B',8);
        
        $pdf->Cell(5/100*$width,$height,"No.",1,0,'C',1);		
		$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['vhc_jenis_pekerjaan'],1,0,'C',1);
        $pdf->Cell(20/100*$width,$height,$_SESSION['lang']['nik'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['absensi'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['jhk'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['umr'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['insentif'],1,1,'C',1);
        $pdf->SetFont('Arial','',7);
        $pdf->SetFillColor(255,255,255);
		$totHk=$totUmr=$totIns=0;
		$no=0;
        while($rKhdrn=$qKhdrn->fetch()){
			$str2 = "select t1.kodekegiatan, t2.namakegiatan from ".$dbname.".vhc_spl_prestasi t1 
					left join ".$dbname.".vhc_kegiatan t2 on t1.kodekegiatan = t2.kodekegiatan 
					where t1.notransaksi = '".$param['notransaksi']."' and t1.nourut = '".$rKhdrn['nourut']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
            $res2->setFetchMode(PDO::FETCH_ASSOC);
            $bar2=$res2->fetch();
			$vKegiatan = $bar2['kodekegiatan']." - ".$bar2['namakegiatan'];
			
            $no++;
            $pdf->Cell(5/100*$width,$height,$no,1,0,'C',1);
            $pdf->Cell(25/100*$width,$height,$vKegiatan,1,0,'L',1);
            $pdf->Cell(20/100*$width,$height,$optNamaKary[$rKhdrn['nik']],1,0,'L',1);
            $pdf->Cell(10/100*$width,$height,"HADIR",1,0,'C',1);
            $pdf->Cell(10/100*$width,$height,$rKhdrn['jhk'],1,0,'C',1);
            $pdf->Cell(10/100*$width,$height,number_format($rKhdrn['umr'],0),1,0,'R',1);
            $pdf->Cell(10/100*$width,$height,number_format($rKhdrn['premi'],0),1,1,'R',1);
            $totHk+=$rKhdrn['jhk'];
            $totUmr+=$rKhdrn['umr'];
            $totIns+=$rKhdrn['premi'];
        }
        
		$pdf->Cell(60/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
		$pdf->Cell(10/100*$width,$height,$totHk,1,0,'C',1);
		$pdf->Cell(10/100*$width,$height,number_format($totUmr,0),1,0,'R',1);
		$pdf->Cell(10/100*$width,$height,number_format($totIns,0),1,1,'R',1);
        
        $pdf->Ln(30);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell($width,$height,$titleDetail[2],0,1,'L',1);
        $pdf->SetFillColor(220,220,220);
        $pdf->SetFont('Arial','B',8);
        $sMat="select * from ".$dbname.".vhc_spl_material where notransaksi = '".$param['notransaksi']."' order by nourut ASC";
        $qMat=$owlPDO->query($sMat) or die(print " Gagal: ".PDOException::getMessage());
        $qMat->setFetchMode(PDO::FETCH_ASSOC);
        
        $pdf->Cell(5/100*$width,$height,"No.",1,0,'C',1);
        $pdf->Cell(25/100*$width,$height,$_SESSION['lang']['vhc_jenis_pekerjaan'],1,0,'C',1);
        $pdf->Cell(15/100*$width,$height,$_SESSION['lang']['gudang'],1,0,'C',1);
        $pdf->Cell(30/100*$width,$height,$_SESSION['lang']['namabarang'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['kwantitas'],1,0,'C',1);
        $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['satuan'],1,1,'C',1);
        $pdf->SetFont('Arial','',7);
        $pdf->SetFillColor(255,255,255);
		$no3 = 0;
        while($rMat=$qMat->fetch()){
			$optGudang = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$rMat['kodegudang']."'");
			
			$str2 = "select t1.kodekegiatan, t2.namakegiatan from ".$dbname.".vhc_spl_prestasi t1 
					left join ".$dbname.".vhc_kegiatan t2 on t1.kodekegiatan = t2.kodekegiatan 
					where t1.notransaksi = '".$param['notransaksi']."' and t1.nourut = '".$rMat['nourut']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
            $res2->setFetchMode(PDO::FETCH_ASSOC);
            $bar2=$res2->fetch();
			$vKegiatan = $bar2['namakegiatan']." (".$bar2['kodekegiatan'].")";
			
			$str2 = "select kodebarang, namabarang, satuan from ".$dbname.".log_5masterbarang where kodebarang = '".$rMat['kodebarang']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
            $res2->setFetchMode(PDO::FETCH_ASSOC);
            $bar2=$res2->fetch();
			$vNamaBarang = $bar2['namabarang']." (".$bar2['kodebarang'].")";
			$vSatuan = $bar2['satuan'];
			
            $no3++;
            $pdf->Cell(5/100*$width,$height,$no3,1,0,'C',1);
            $pdf->Cell(25/100*$width,$height,$vKegiatan,1,0,'L',1);
            $pdf->Cell(15/100*$width,$height,$rMat['kodegudang'],1,0,'L',1);
            $pdf->Cell(30/100*$width,$height,$vNamaBarang,1,0,'L',1);
            $pdf->Cell(10/100*$width,$height,$rMat['jumlah'],1,0,'R',1);
            $pdf->Cell(10/100*$width,$height,$vSatuan,1,1,'L',1);
        }
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',8);
        $sAsis="select distinct mandor as nikmandor,mandor1 as nikmandor1,assisten as nikasisten,krani as keranimuat,tanggal,kodeorg from ".$dbname.".vhc_splht where notransaksi='".$param['notransaksi']."'";
        $qAsis=$owlPDO->query($sAsis) or die(print " Gagal: ".PDOException::getMessage());
        $qAsis->setFetchMode(PDO::FETCH_ASSOC);
        $rAsis=$qAsis->fetch();
		
        setIt($RnamaKary[$rAsis['nikasisten']],'');
		setIt($RnamaKary[$rAsis['nikmandor1']],'');
		setIt($RnamaKary[$rAsis['nikmandor']],'');
        $pdf->ln(35);
        $pdf->Cell(85/100*$width,$height,$rAsis['kodeorg'].",".tanggalnormal($rAsis['tanggal']),0,1,'R',0);
        $pdf->ln(35);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['dstujui_oleh'],0,0,'C',0);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['diperiksa'],0,0,'C',0);
        $pdf->Cell(29/100*$width,$height,$_SESSION['lang']['dibuatoleh'],0,1,'C',0);
        $pdf->ln(65);
        $pdf->SetFont('Arial','U',8);
        $pdf->Cell(28/100*$width,$height,$RnamaKary[$rAsis['nikasisten']],0,0,'C',0);
        $pdf->Cell(28/100*$width,$height,$RnamaKary[$rAsis['nikmandor1']],0,0,'C',0);
        $pdf->Cell(29/100*$width,$height,$RnamaKary[$rAsis['nikmandor']],0,1,'C',0);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['asisten'],0,0,'C',0);
        $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['nikmandor1'],0,0,'C',0);
        $pdf->Cell(29/100*$width,$height,$_SESSION['lang']['nikmandor'],0,1,'C',0);
	
        $pdf->Output();
        break;
		
    default:
		break;
}
?>