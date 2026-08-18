<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');

/*echo "<pre>";
print_r($_GET);
echo "</pre>";*/
$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];

$whKar="";
$nmKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',$whKar);
$nikKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,nik',$whKar);


//=============

//create Header
class PDF extends FPDF
{

        function Header()
        {
        global $conn;
        global $dbname;
        global $userid;
        global $notransaksi;
        global $kodevhc;
        global $posting;
        global $owlPDO;

        $notransaksi=$_GET['column'];
        $kodevhc=$_GET['kodevhc'];
        $str="select * from ".$dbname.".".$_GET['table']."  where nopengajuan='".$notransaksi."' and kodevhc='".$kodevhc."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);                        
        $bar=$res->fetch();
        $posting=$bar->statuspersetujuan;	

        //ambil nama pt
           $str1="select * from ".$dbname.".organisasi where induk='MHO' and tipe='PT' and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'"; 
            $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_OBJ);   
           while($bar1=$res1->fetch())
           {
                 $namapt=$bar1->namaorganisasi;
                 $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
                 $telp=$bar1->telepon;				 
           }    
           $sql2="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->updateby."'";
            $res1=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_OBJ);   
           $res2=$res1->fetch();

           $sql5="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->postingby."'";
            $res1=$owlPDO->query($sql5) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_OBJ);   
           $res5=$res1->fetch();

           $sql3="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
            $res1=$owlPDO->query($sql3) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_OBJ); 
           $res3=$res1->fetch(); 
		   
           $smnu="select id,caption,caption2,caption3 from ".$dbname.".menu where id='2289'";//Silahkan diganti kalau urutan id di tabel menunya berubah
           $res=$owlPDO->query($smnu) or die(print " Gagal: ".PDOException::getMessage());
           $res->setFetchMode(PDO::FETCH_ASSOC);      
           $menunya=$res->fetch();

			$arrHead = setheadreport(getindukPT(substr($bar->kodeorg,0,4)),getindukPT(substr($bar->kodeorg,0,4)));
				
			$width = $this->w - $this->lMargin - $this->rMargin;
			$height = 5;
			$path=$arrHead['logo'];
			$this->Image($path,12,3,25);	
			$this->SetFillColor(255,255,255);	
			$this->SetY(5);
			$this->SetX(37);  
			$this->SetFont('Arial','B',12);
			$this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
			$this->SetFont('Arial','B',8.8);
			$this->SetX(37);		
			$this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
			$this->SetX(37);			
			$this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
			$this->Ln();
			$this->Ln();
			$this->SetY(100);
			$this->Line(10,30,200,30);

                // $path='images/logo.jpg';
            // $this->Image($path,15,3,20);	
                // $this->SetFont('Arial','B',10);
                // $this->SetFillColor(255,255,255);
                // $this->SetY(5);
                // $this->SetX(40);   
            // $this->Cell(60,5,$namapt,0,1,'L');	 
                // $this->SetX(40); 		
            // $this->MultiCell(150,5,$alamatpt,0,1,'L');	
                // $this->SetX(40); 			
                // $this->Cell(60,5,"Tel: ".$telp,0,1,'L');
                // $this->Ln();
                $this->SetFont('Arial','U',15);
                $this->SetY(40);
                if($_SESSION['language'] == 'ID'){
                        $this->Cell(190,5,strtoupper($_SESSION['lang']['laporan']." ".$menunya['caption']),0,1,'C');
                }elseif($_SESSION['language'] == 'EN'){
                        $this->Cell(190,5,strtoupper($_SESSION['lang']['laporan']." ".$menunya['caption2']),0,1,'C');
                }elseif($_SESSION['language'] == 'MY'){
                        $this->Cell(190,5,strtoupper($_SESSION['lang']['laporan']." ".$menunya['caption3']),0,1,'C');
                }	
                $this->SetFont('Arial','',6); 	
                // $this->Line(10,27,200,27);	
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial','',9); 
                $this->Cell(35,4,$_SESSION['lang']['nopengajuan'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,$bar->nopengajuan,0,1,'L'); 				
                $this->Cell(35,4,$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['pengajuan'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,tanggalnormal($bar->tanggalpengajuan),0,1,'L'); 
                $this->Cell(35,4,$_SESSION['lang']['nama']." ".$_SESSION['lang']['pemohon'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,getNamaKaryawan($bar->karyawanidpemohon),0,1,'L');
                $this->Cell(35,4,$_SESSION['lang']['tanggalmasuk'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,tanggalnormal($bar->tglmasuk),0,1,'L'); 
                $this->Cell(35,4,$_SESSION['lang']['tanggalkeluar'],0,0,'L');
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,tanggalnormal($bar->tglkeluar),0,1,'L'); 
                $this->Cell(35,4,$_SESSION['lang']['namaorganisasi'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,$res3->namaorganisasi." [".$bar->kodeorg."]",0,1,'L'); 		  
                $this->Cell(35,4,$_SESSION['lang']['kodevhc'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,$bar->kodevhc." - ".getVhc($bar->kodevhc,'detailvhc'),0,1,'L'); 
                $this->Cell(35,4,$_SESSION['lang']['downtime'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,$bar->downtimejam." ".$_SESSION['lang']['jam']."",0,1,'L'); 

                $this->Cell(35,4,'KM / HM '.$_SESSION['lang']['masuk'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,number_format($bar->kmmasuk,2),0,1,'L'); 
                $this->Cell(35,4,'KM / HM '.$_SESSION['lang']['keluar'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,number_format($bar->kmkeluar,2),0,1,'L'); 

                $this->Cell(35,4,$_SESSION['lang']['descDamage'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->MultiCell(150,4,$bar->kerusakan,0,1,'J'); 
                $this->Cell(35,4,$_SESSION['lang']['alasan'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->MultiCell(150,4,$bar->alasan,0,1,'J'); 
                $this->Cell(35,4,$_SESSION['lang']['dbuat_oleh'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,(getNamaKaryawan($bar->createdby))."".($bar->createdtime=='0000-00-00 00:00:00'?'':' - '.waktunormal($bar->createdtime)),0,1,'L'); 
        }


        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            $this->SetY(-15);
            $this->SetX(155);
            $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');	
        }

}

        $pdf=new PDF('P','mm','A4');
        $pdf->AddPage();

//ambil kelengkapan

        $pdf->Ln();
        if($posting == 0)
        {
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(190,5,$_SESSION['lang']['belumposting'],0,0,'C');
            $pdf->Ln();
            $pdf->Ln();
        }elseif($posting == 1){
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(190,5,$_SESSION['lang']['post'],0,0,'C');
            $pdf->Ln();
            $pdf->Ln();
        }elseif($posting == 2){
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(190,5,$_SESSION['lang']['ditolak'],0,0,'C');
            $pdf->Ln();
            $pdf->Ln();
        }elseif($posting == 9){
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(190,5,$_SESSION['lang']['proses'] ." ".$_SESSION['lang']['persetujuan'],0,0,'C');
            $pdf->Ln();
            $pdf->Ln();
        }

        $str="select * from ".$dbname.".vhc_pengajuanservicedt where nopengajuan='".$notransaksi."'"; //echo $str;exit();
        $res4=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res4->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($res4);
        $rCek=$numrows;
        $pdf->SetFont('Arial','B',9);	
        $pdf->SetFillColor(220,220,100);
        $pdf->Cell(190,5,$_SESSION['lang']['daftarbarang'],1,1,'C',1);	
        $pdf->SetFillColor(220,220,220);
        $pdf->Cell(8,5,'No',1,0,'L',1);
        $pdf->Cell(30,5,$_SESSION['lang']['kodebarang'],1,0,'C',1);
        $pdf->Cell(65,5,$_SESSION['lang']['namabarang'],1,0,'C',1);		
        $pdf->Cell(15,5,$_SESSION['lang']['satuan'],1,0,'C',1);	
        $pdf->Cell(15,5,$_SESSION['lang']['jumlah'],1,0,'C',1);	
        $pdf->Cell(57,5,$_SESSION['lang']['keterangan'],1,1,'C',1);	
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
        if($rCek>0)
        {

            $no=0;
            while($res=$res4->fetch())
            {
                    $no+=1;
                    $kodebarang=$res->kodebarang;
                    $sbrg="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$kodebarang."' ";
                    $res5=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
                    $res5->setFetchMode(PDO::FETCH_ASSOC);
                    $rbrg=$res5->fetch();

                    $pdf->Cell(8,5,$no,1,0,'C',1);
                    $pdf->Cell(30,5,$kodebarang,1,0,'L',1);
                    $pdf->Cell(65,5,substr($rbrg['namabarang'],0,35),1,0,'L',1);	
                    $pdf->Cell(15,5,$rbrg['satuan'],1,0,'C',1);	
                    $pdf->Cell(15,5,number_format($res->jumlah,2),1,0,'R',1);	
                    $pdf->Cell(57,5,$res->keterangan,1,1,'L',1);

            }
        }else{
            $pdf->Cell(190,5,$_SESSION['lang']['errdatanotexist'],1,1,'C',1);	
        }


        $pdf->Ln();

        $iKar="select * from ".$dbname.".vhc_pengajuanservicedt_karyawan  where nopengajuan='".$notransaksi."'"; //echo $str;exit();
        $res6=$owlPDO->query($iKar) or die(print " Gagal: ".PDOException::getMessage());
        $res6->setFetchMode(PDO::FETCH_ASSOC);
        $numrows=owlBaris($res6);
        $wKar=$numrows;
        $pdf->SetFont('Arial','B',9);	
        $pdf->SetFillColor(220,220,100);
        $pdf->Cell(103,5,$_SESSION['lang']['karyawan'],1,1,'C',1);	
        $pdf->SetFillColor(220,220,220);
        $pdf->Cell(8,5,'No',1,0,'L',1);
        $pdf->Cell(30,5,$_SESSION['lang']['nik'],1,0,'C',1);
        $pdf->Cell(65,5,$_SESSION['lang']['namakaryawan'],1,1,'C',1);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
        if($wKar>0)
        {
            $no=0;
            while($dKar=  $res6->fetch())
            {
                $whKar="karyawanid='".$dKar['karyawanid']."'";
                $no+=1;  
                $pdf->Cell(8,5,$no,1,0,'C',1);
                $pdf->Cell(30,5,$nikKar[$dKar['karyawanid']],1,0,'L',1);
                $pdf->Cell(65,5,$nmKar[$dKar['karyawanid']],1,1,'L',1);

            }
        }else{
            $pdf->Cell(103,5,$_SESSION['lang']['errdatanotexist'],1,1,'C',1);	
        }

        $pdf->Ln();

        $ibrg="select * from ".$dbname.".vhc_pengajuanservicedt_pengembalian  where nopengajuan='".$notransaksi."'"; //echo $str;exit();
        $rese=$owlPDO->query($ibrg) or die(print " Gagal: ".PDOException::getMessage());
        $rese->setFetchMode(PDO::FETCH_ASSOC);
        $numrows=owlBaris($rese);
        $wKar=$numrows;
        $pdf->SetFont('Arial','B',9);	
        $pdf->SetFillColor(220,220,100);
        $pdf->Cell(190,5,$_SESSION['lang']['bulkreturn'],1,1,'C',1);	
        $pdf->SetFillColor(220,220,220);
        $pdf->Cell(8,5,'No',1,0,'L',1);
        $pdf->Cell(30,5,$_SESSION['lang']['kodebarang'],1,0,'C',1);
        $pdf->Cell(65,5,$_SESSION['lang']['namabarang'],1,0,'C',1);
        $pdf->Cell(15,5,$_SESSION['lang']['satuan'],1,0,'C',1);
        $pdf->Cell(15,5,$_SESSION['lang']['jumlah'],1,0,'C',1);
        $pdf->Cell(57,5,$_SESSION['lang']['keterangan'],1,1,'C',1);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
        if($wKar>0)
        {
            $no=0;
            while($brg=  $rese->fetch())
            {
                $no+=1;  
                $pdf->Cell(8,5,$no,1,0,'C',1);
                $pdf->Cell(30,5,$brg['kodebarang'],1,0,'L',1);
                $pdf->Cell(65,5,getNamaBrg($brg['kodebarang']),1,0,'L',1);
                $pdf->Cell(15,5,getSatBrg($brg['kodebarang']),1,0,'C',1);
                $pdf->Cell(15,5,number_format($brg['jumlah'],2),1,0,'R',1);
                $pdf->Cell(57,5,$nmKar[$brg['karyawanid']],1,1,'L',1);
                
            }
        }else{
            $pdf->Cell(190,5,$_SESSION['lang']['errdatanotexist'],1,1,'C',1);	
        }
        $pdf->Output();
?>