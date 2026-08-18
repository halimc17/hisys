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

        $test=explode(',',$_GET['column']);
        $notransaksi=$test[0];
        $kodevhc=$test[1];
        $str="select * from ".$dbname.".".$_GET['table']."  where notransaksi='".$notransaksi."' and kodevhc='".$kodevhc."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);                        
        $bar=$res->fetch();
        $posting=$bar->posting;	

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

           $sql3="select * from ".$dbname.".organisasi where kodeorganisasi='".substr($bar->kodeorg,0,4)."'";
            $res1=$owlPDO->query($sql3) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_OBJ); 
           $res3=$res1->fetch(); 
		   
			$arrHead = setheadreport(getIndukPT(substr($bar->kodeorg,0,4)));
				
			$width = $this->w - $this->lMargin - $this->rMargin;
			$height = 5;
			$path=$arrHead['logo'];
            if(getIndukPT(substr($bar->kodeorg,0,4)) == 'CAR'){
                $this->Image($path,12,3,25);	
            }elseif (getIndukPT(substr($bar->kodeorg,0,4)) == 'MHA') {
                $this->Image($path,11,8,25);
            }else {
                $this->Image($path,7,3,35);
            }
			$this->SetFont('Arial','B',9);
			$this->SetFillColor(255,255,255);	
			$this->SetY(8);
			$this->SetX(40);  
			$this->Cell($width-100,$height,$res3->namaorganisasi,0,1,'L');	 
			$this->SetX(40);		
			$this->Cell($width-100,$height,$res3->alamat,0,1,'L');	
			$this->SetX(40);			
			$this->Cell($width-100,$height,"Tel: ".$res3->telepon,0,1,'L');	
			$this->Line(10,27,200,27);
			$this->Ln();

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
                $this->Cell(190,5,strtoupper($_SESSION['lang']['service']),0,1,'C');		
                $this->SetFont('Arial','',6); 
                $this->SetY(27);
                $this->SetX(163);
                $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
                // $this->Line(10,27,200,27);	
                $this->Ln();
                $this->SetFont('Arial','',9); 
                $this->Cell(35,4,$_SESSION['lang']['notransaksi'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,$bar->notransaksi,0,1,'L'); 				
                $this->Cell(35,4,$_SESSION['lang']['tanggalmasuk'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,tanggalnormal($bar->tanggal),0,1,'L'); 
                $this->Cell(35,4,$_SESSION['lang']['tanggalkeluar'],0,0,'L');
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,tanggalnormal($bar->tanggalkeluar),0,1,'L'); 
                $this->Cell(35,4,$_SESSION['lang']['namaorganisasi'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,getNamaOrg($bar->kodeorg)." [".$bar->kodeorg."]",0,1,'L'); 		  
                $this->Cell(35,4,$_SESSION['lang']['kodevhc'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,$bar->kodevhc." - ".getVhc($bar->kodevhc,'detailvhc'),0,1,'L'); 
                $this->Cell(35,4,$_SESSION['lang']['noreferensi'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,$bar->noreferensi,0,1,'L');
                $this->Cell(35,4,$_SESSION['lang']['downtime'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,$bar->downtime." Jam",0,1,'L'); 

                $this->Cell(35,4,'Km/Hm Masuk',0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,number_format($bar->kmmasuk,2),0,1,'L'); 
                $this->Cell(35,4,'Km/Hm Keluar',0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,number_format($bar->kmkeluar,2),0,1,'L'); 

                $this->Cell(35,4,$_SESSION['lang']['descDamage'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->MultiCell(150,4,$bar->kerusakan,0,1,'J'); 
                $this->Cell(35,4,'Alasan Terlambat',0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->MultiCell(150,4,$bar->terlambat,0,1,'J'); 
                $this->Cell(35,4,$_SESSION['lang']['dbuat_oleh'],0,0,'L'); 
                $this->Cell(5,4,':',0,0,'L'); 
                $this->Cell(40,4,(getNamaKaryawan($bar->updateby))."".($bar->createdtime=='0000-00-00 00:00:00'?'':' - '.waktunormal($bar->createdtime)),0,1,'L');  
                $this->Cell(35,4,$_SESSION['lang']['posted'],0,0,'L');
                $this->Cell(5,4,':',0,0,'L'); 
                // setIt($res5->namakaryawan,'');
                $this->Cell(40,4,@getNamaKaryawan($bar->postingby)."".($bar->postedtime=='0000-00-00 00:00:00'?'':' - '.waktunormal($bar->postedtime)),0,1,'L');  
        }


        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        }

}

        $pdf=new PDF('P','mm','A4');
        $pdf->AddPage();

//ambil kelengkapan

        $pdf->Ln();
        if($posting<1)
        {
                $pdf->SetFont('Arial','B',12);
                $pdf->Cell(190,5,$_SESSION['lang']['belumposting'],0,0,'C');
                $pdf->Ln();
        }

        $str="select * from ".$dbname.".vhc_penggantiandt   where notransaksi='".$notransaksi."'"; //echo $str;exit();
        $res4=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res4->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($res4);
        $rCek=$numrows;
        if($rCek>0)
        {

            $pdf->SetFont('Arial','B',9);	
            $pdf->SetFillColor(220,220,220);
            $pdf->Cell(8,5,'No',1,0,'C',1);
            $pdf->Cell(30,5,$_SESSION['lang']['kodebarang'],1,0,'C',1);
            $pdf->Cell(90,5,$_SESSION['lang']['namabarang'],1,0,'C',1);		
            $pdf->Cell(15,5,$_SESSION['lang']['jumlah'],1,0,'C',1);	
            $pdf->Cell(15,5,$_SESSION['lang']['satuan'],1,0,'C',1);	
            $pdf->Cell(37,5,$_SESSION['lang']['keterangan'],1,1,'C',1);	
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',9);


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
                        $pdf->Cell(90,5,substr($rbrg['namabarang'],0,35),1,0,'L',1);	
                        $pdf->Cell(15,5,number_format($res->jumlah,2),1,0,'R',1);	
                        $pdf->Cell(15,5,$rbrg['satuan'],1,0,'C',1);	
                        $pdf->Cell(37,5,$res->keterangan,1,1,'L',1);

                }
        }


        $pdf->Ln();

        $iKar="select * from ".$dbname.".vhc_penggantiandt_karyawan   where notransaksi='".$notransaksi."'"; //echo $str;exit();
        $res6=$owlPDO->query($iKar) or die(print " Gagal: ".PDOException::getMessage());
        $res6->setFetchMode(PDO::FETCH_ASSOC);
        $numrows=owlBaris($res6);
        $wKar=$numrows;
        if($wKar>0)
        {
            $pdf->SetFont('Arial','B',9);	
            $pdf->SetFillColor(220,220,220);
            $pdf->Cell(8,5,'No',1,0,'L',1);
            $pdf->Cell(30,5,$_SESSION['lang']['nik'],1,0,'C',1);
            $pdf->Cell(90,5,$_SESSION['lang']['namakaryawan'],1,1,'C',1);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',9);
                $no=0;
                while($dKar=  $res6->fetch())
                {
                    $whKar="karyawanid='".$dKar['karyawanid']."'";
                    $no+=1;  
                    $pdf->Cell(8,5,$no,1,0,'C',1);
                    $pdf->Cell(30,5,$nikKar[$dKar['karyawanid']],1,0,'L',1);
                    $pdf->Cell(90,5,$nmKar[$dKar['karyawanid']],1,1,'L',1);

                }
        }
        $pdf->Output();
?>