<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');

$karyawanid=$_GET['karyawanid'];
//$namakaryawan=$_GET['namakaryawan'];

//=============


$str="select *,
      case jeniskelamin when 'L' then 'Laki-Laki'
          else  'Wanita'
          end as jk
          from ".$dbname.".datakaryawan where karyawanid=".$karyawanid ." limit 1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$defaulsrc='images/user.jpg';
while($bar=$res->fetch())
{
        //get pendidikan
         $pendidikan='';
         $str1="select kelompok from ".$dbname.".sdm_5pendidikan where levelpendidikan=".$bar->levelpendidikan;
         $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
         $res1->setFetchMode(PDO::FETCH_OBJ);
         while($bar1=$res1->fetch())
           {$pendidikan=$bar1->kelompok;}

       	 $departemen='';
       	 //$pelatihan='';
		 $str5="select * from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_jobdescription b
		 on a.karyawanid=b.karyawanid where a.karyawanid=".$bar->karyawanid;
         $res5=$owlPDO->query($str5) or die(print " Gagal: ".PDOException::getMessage());
         $res5->setFetchMode(PDO::FETCH_OBJ);
         while($bar5=$res5->fetch())
           {$departemen=$bar5->departemen;
           	$pelatihan=$bar5->pelatihan;
           	$unit=$bar5->unit;
           	$tanggalefektif=$bar5->tanggalefektif;
           	$atasan=$bar5->atasan;
           	$rekan=$bar5->rekan;
           	$kompetensi=$bar5->kompetensi;
           	$pengalamankerja=$bar5->pengalamankerja;
           	$namakaryawan=$bar5->namakaryawan;}
       	//exit('error'.$str5);

       	$str7="select x.atasan,y.namakaryawan namaatasan from
		(select b.atasan,a.karyawanid from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_jobdescription b on a.karyawanid=b.karyawanid where a.karyawanid=".$bar->karyawanid.")x,
		(select namakaryawan,karyawanid from ".$dbname.".datakaryawan)y
		where x.atasan=y.karyawanid";
		$res7=$owlPDO->query($str7) or die(print " Gagal: ".PDOException::getMessage());
         $res7->setFetchMode(PDO::FETCH_OBJ);
         while($bar7=$res7->fetch())
         {
         	$namaatasan=$bar7->namaatasan;
         }

         $str8="select x.rekan,y.namakaryawan rekan from
		(select b.rekan,a.karyawanid from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_jobdescription b on a.karyawanid=b.karyawanid where a.karyawanid=".$bar->karyawanid.")x,
		(select namakaryawan,karyawanid from ".$dbname.".datakaryawan)y
		where x.rekan=y.karyawanid";
		$res8=$owlPDO->query($str8) or die(print " Gagal: ".PDOException::getMessage());
         $res8->setFetchMode(PDO::FETCH_OBJ);
         while($bar8=$res8->fetch())
         {
         	$rekan=$bar8->rekan;
         }
//exit('eror'.$str7);
         $str9="select deskripsi1,deskripsi2 from ".$dbname.".sdm_jobdescriptiondt where karyawanid='".$karyawanid."' and subdesciption='bawahan'";
	
		$res9=$owlPDO->query($str9) or die(print " Gagal: ".PDOException::getMessage());
         $res9->setFetchMode(PDO::FETCH_OBJ);
         while($bar9=$res9->fetch())
         {
         	$bawahan=$bar9->deskripsi1;
         	$bawahan1=$bar9->deskripsi2;
         }

           	$deskripsi1='';
           		$str6="select deskripsi1 from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_jobdescriptiondt b
		 on a.karyawanid=b.karyawanid where subdesciption='tujuanjabatan' and  a.karyawanid=".$bar->karyawanid;
         $res6=$owlPDO->query($str6) or die(print " Gagal: ".PDOException::getMessage());
         $res6->setFetchMode(PDO::FETCH_OBJ);
         while($bar6=$res6->fetch())
           {$deskripsi1=$bar6->deskripsi1;
           	}
           	//exit('error'.$deskripsi1);

        //jabatan
        $jabatan='';
        $str3="select * from ".$dbname.".sdm_5jabatan where kodejabatan=".$bar->kodejabatan." and namajabatan not like '%available' order by kodejabatan";
        $res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
        $res3->setFetchMode(PDO::FETCH_OBJ);
        while($bar3=$res3->fetch())
        {$jabatan=$bar3->namajabatan;}
                $jabatanku=$bar->kodejabatan;
                //exit('error'.$jabatan);
    


                
//create Header
class PDF extends FPDF{}
		
		$pdf=new PDF('P','mm','A4');
		$pdf->AddPage();
		
		
		$arrHead = setheadreport($unit);
		$path=$arrHead['logo'];
		$pdf->Image($path,20,7,0,22);
		
		$pdf->Ln();
		$pdf->SetY(35);
		$pdf->SetFont('Arial','B',12);
		$pdf->SetFillColor(0,37,124);
		$pdf->Cell(190,5,'','LRT',1,'C',1);
		$pdf->Cell(190,5,strtoupper("deskripsi jabatan"),'LR',1,'C',1);
		$pdf->Cell(190,5,strtoupper("(job description)"),'LR',1,'C',1);
		$pdf->Cell(190,5,'','LRB',1,'C',1);
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,'','LRB',1,'C');
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("I. identitas jabatan (job identity)"),'TLRB',1,'L',1);
		
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(190,5,'','LR',1,'L');
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Nama Jabatan',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$jabatan,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Departemen',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$departemen,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Unit Usaha / Seksi',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$unit,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Tanggal Efektif',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,tanggalnormal($tanggalefektif),'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Pemegang Jabatan',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$namakaryawan,'R',1,'L');
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,'','LRB',1,'C');
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("II. hubungan pelaporan kerja (reporting relationship)"),'TLRB',1,'L',1);
		
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where karyawanid='".$karyawanid."' and subdesciption='bawahan'";
		$res=fetchData($str);
		$countba = count($res);
		$no=0;
		foreach($res as $key=>$val)
		{
			$no++;
			$optBa = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
			if($no==$countba)
			{
				$bawahan .= $optBa[$val['deskripsi1']];
			}
			else
			{
				$bawahan .= $optBa[$val['deskripsi2']].", ";
			}
		}
		
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(190,5,'Struktur Organisasi','RL',1,'C');
		
		$getY=$pdf->GetY();
		
		$pdf->Image("images/qrcode/xxx.jpg",40,$getY,0,70);
		
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(190,60,'','LR',1,'C');
		
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(45,5,'Atasan Langsung','L',0,'L');
		$pdf->Cell(5,5,':',0,0,'L');
		$pdf->Cell(140,5,$namaatasan,'R',1,'L');
		
		$pdf->Cell(45,5,'Rekan Sederajat (Buddy)','L',0,'L');
		$pdf->Cell(5,5,':',0,0,'L');
		$pdf->Cell(140,5,$rekan,'R',1,'L');
		
		$pdf->Cell(45,5,'Bawahan Langsung','L',0,'L');
		$pdf->Cell(5,5,':',0,0,'L');
		$pdf->Cell(140,5,$bawahan,'R',1,'L');
		
		//$pdf->Cell(5,5,':',0,0);
		//$pdf->Cell(140,5,$bawahan1,'R',1,'L');
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,'','LRB',1,'C');
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("III. tujuan jabatan (job purpose)"),'TLRB',1,'L',1);
		
		
			$pdf->Cell(10,5,'','L',0,'L');
			$pdf->Cell(5,5,chr(127),0,0,'L');
			$pdf->Cell(175,5,$deskripsi1,'R',1,'L');
		
		
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,'','LRB',1,'C');
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("IV. tanggung jawab (key responsibilities)"),'TLRB',1,'L',1);
		$pdf->SetFont('Arial','BU',10);
		$pdf->Cell(80,3,'','LR',0,'L');
		$pdf->Cell(80,3,'','R',0,'L');
		$pdf->Cell(30,3,'','R',1,'L');
		$pdf->Cell(80,5,strtoupper("tugas"),'LR',0,'C');
		$pdf->Cell(80,5,strtoupper("indikator kinerja"),'R',0,'C');		
		$pdf->Cell(30,5,strtoupper("batas waktu"),'R',1,'C');		
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where karyawanid='".$karyawanid."' and subdesciption='tanggungjawab' and tipe='1'";
		$res=fetchData($str);
		//exit('error'.$str);
		if(count($res>0))
		{			
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(80,5,strtoupper("rutin :"),'LR',0,'L');
			$pdf->Cell(80,5,'','R',0,'C');
			$pdf->Cell(30,5,'','R',1,'C');
			
			$pdf->SetFont('Arial','',10);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				$pdf->Cell(5,5,$no.".",'L',0,'L');
				$pdf->Cell(75,5,$val['deskripsi1'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(75,5,$val['deskripsi2'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(25,5,tanggalnormal($val['deadline']),'R',1,'L');
			}
		}
		
		$pdf->Cell(80,3,'','LR',0,'L');
		$pdf->Cell(80,3,'','R',0,'L');
		$pdf->Cell(30,3,'','R',1,'L');
		
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where karyawanid='".$karyawanid."' and subdesciption='tanggungjawab' and tipe='2'";
		$res=fetchData($str);
		if(count($res>0))
		{			
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(80,5,strtoupper("berkala :"),'LR',0,'L');
			$pdf->Cell(80,5,'','R',0,'C');
			$pdf->Cell(30,5,'','R',1,'C');
			
			$pdf->SetFont('Arial','',10);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				$pdf->Cell(5,5,$no.".",'L',0,'L');
				$pdf->Cell(75,5,$val['deskripsi1'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(75,5,$val['deskripsi2'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(25,5,tanggalnormal($val['deadline']),'R',1,'L');
			}
		}
		
		$pdf->Cell(80,3,'','LR',0,'L');
		$pdf->Cell(80,3,'','R',0,'L');
		$pdf->Cell(30,3,'','R',1,'L');
		
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where karyawanid='".$karyawanid."' and subdesciption='tanggungjawab' and tipe='3'";
		$res=fetchData($str);
		if(count($res>0))
		{			
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(80,5,strtoupper("insidentil :"),'LR',0,'L');
			$pdf->Cell(80,5,'','R',0,'C');
			$pdf->Cell(30,5,'','R',1,'C');
			
			$pdf->SetFont('Arial','',10);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				$pdf->Cell(5,5,$no.".",'L',0,'L');
				$pdf->Cell(75,5,$val['deskripsi1'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(75,5,$val['deskripsi2'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(25,5,tanggalnormal($val['deadline']),'R',1,'L');
			}
		}
		
		$pdf->Cell(80,3,'','LR',0,'L');
		$pdf->Cell(80,3,'','R',0,'L');
		$pdf->Cell(30,3,'','R',1,'L');
		
		$pdf->SetFont('Arial','',12);
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("V. wewenang (authority)"),'TLRB',1,'L',1);
		$pdf->SetFont('Arial','',10);
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where karyawanid='".$karyawanid."' and subdesciption='wewenang'";
		$res=fetchData($str);
		$pdf->Cell(190,2,'','LR',1,'L');
		foreach($res as $key=>$val)
		{
			$pdf->Cell(10,5,'','L',0,'L');
			$pdf->Cell(5,5,chr(127),0,0,'L');
			$pdf->Cell(175,5,$val['deskripsi1'],'R',1,'L');
		}
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,'','LRB',1,'C');
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("VI. hubungan kerja (work relations)"),'TLRB',1,'L',1);
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where karyawanid='".$karyawanid."' and subdesciption='hubungankerja' and tipe='1'";
		$res=fetchData($str);
		if(count($res>0))
		{
			$pdf->SetFont('Arial','BU',10);
			$pdf->Cell(95,3,'','LR',0,'L');
			$pdf->Cell(95,3,'','R',1,'L');
			$pdf->Cell(95,5,strtoupper("pihak internal"),'LR',0,'C');
			$pdf->Cell(95,5,strtoupper("kegiatan"),'R',1,'C');
			
			$pdf->SetFont('Arial','',10);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				$pdf->Cell(5,5,$no.".",'L',0,'L');
				$pdf->Cell(90,5,$val['deskripsi1'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(90,5,$val['deskripsi2'],'R',1,'L');
			}
		}
		
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where karyawanid='".$karyawanid."' and subdesciption='hubungankerja' and tipe='2'";
		$res=fetchData($str);
		if(count($res>0))
		{
			$pdf->SetFont('Arial','BU',10);
			$pdf->Cell(95,3,'','LR',0,'L');
			$pdf->Cell(95,3,'','R',1,'L');
			$pdf->Cell(95,5,strtoupper("pihak eksternal"),'LR',0,'C');
			$pdf->Cell(95,5,strtoupper("kegiatan"),'R',1,'C');
			
			$pdf->SetFont('Arial','',10);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				$pdf->Cell(5,5,$no.".",'L',0,'L');
				$pdf->Cell(90,5,$val['deskripsi1'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(90,5,$val['deskripsi2'],'R',1,'L');
			}
		}
		
		$pdf->Cell(95,3,'','LR',0,'L');
		$pdf->Cell(95,3,'','R',1,'L');
		
		$pdf->SetFont('Arial','',12);
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("VII. persyaratan jabatan (job qualifications)"),'TLRB',1,'L',1);
		
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(190,5,'','LR',1,'L');
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Pendidikan',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$pendidikan,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Pengalaman Kerja',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$pengalamankerja,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Pelatihan',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$pelatihan,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Kompetensi',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$kompetensi,'R',1,'L');
		
		$pdf->Cell(190,5,'','BLR',1,'L');
		
		##======================##
		
		$pdf->SetLineWidth(1);
		$pdf->Line(205,5,5,5);
		$pdf->Line(5,293,5,5);
		$pdf->Line(205,5,205,293);
		$pdf->Line(205,293,5,293);
		
		$pdf->Output();
		}	
?>
