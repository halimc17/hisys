<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
include_once('lib/zMysql.php');
$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
//=============



 class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                global $arrIntex;
                global $arrPks;
                global $arrOrg;
                global $noSpb;
				global $owlPDO;
				global $namakary;
				
				
				
                $noSpb=$_GET['column'];
                $sH="select * from ".$dbname.".kebun_spbht where nospb='".$noSpb."'";

				$qh=$owlPDO->query($sH) or die(print " Gagal: ".PDOException::getMessage());
				$qh->setFetchMode(PDO::FETCH_ASSOC);
				$rH=$qh->fetch();
                
                
                if($rH['tujuan']=='3'){
                   $arrOrg=  makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
                }
                else{
                    $arrOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
                }
                
                
                $arrIntex=array("0"=>"Internal","1"=>"Afiliasi","3"=>"External");
                //$arrIntex=array("1"=>"Internal","2"=>"Afiliasi","0"=>"External");
				
				$where=" karyawanid ='".$rH['kerani']."'";
				$namakary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$where);
				
				$pt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
				$kodept="".$pt[$rH['kodeorg']]."";
				
                $sN="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$rH['updateby']."'";

				$qN=$owlPDO->query($sN) or die(print " Gagal: ".PDOException::getMessage());
				$qN->setFetchMode(PDO::FETCH_ASSOC);
				$rN=$qN->fetch();
                $nospb=substr($noSpb,8,6);

                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
				
				$arrHead = setheadreport($kodept);
				$path=$arrHead['logo'];
          
                $this->Image($path,$this->lMargin,$this->tMargin,50);	
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
                $this->Ln(30);
              
                $this->SetFont('Arial','BU',10);
                                $this->Cell($width,$height,  strtoupper($_SESSION['lang']['suratPengantarBuah']),'',0,'C');
                                $this->Ln(20);
                $this->SetFont('Arial','',8);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodeorg'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$nospb,'',0,'L');
                $this->Cell((10/100*$width)-5,$height,$_SESSION['lang']['nospb'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height,$_GET['column'],0,0,'L');
                $this->Ln();

                $query2 = selectQuery($dbname,'organisasi','namaorganisasi',
                "kodeorganisasi='".$nospb."'");
                $orgData2 = fetchData($query2);

                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['namaorganisasi'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$orgData2[0]['namaorganisasi'],'',0,'L');
                $this->Cell((10/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height,tanggalnormal($rH['tanggal']),'',1,'L');
                
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['tujuan'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$arrIntex[$rH['tujuan']].', '.$arrOrg[$rH['penerimatbs']],'',0,'L');
                
				
				$str=" select * from ".$dbname.".pabrik_timbangan where nospb='".$noSpb."' ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$supir=$bar['supir'];
					$nopol=$bar['nokendaraan'];
					
				
				$this->Cell((10/100*$width)-5,$height,$_SESSION['lang']['nopol'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height,$nopol,'',1,'L');
                
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['dbuat_oleh'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$rN['namakaryawan'],0,0,'L');		
				   
				$this->Cell((10/100*$width)-5,$height,$_SESSION['lang']['supir'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/100*$width,$height,$supir,'',1,'L');

				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kerani'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$namakary[$rH['kerani']],0,0,'L');		
				
                $this->Ln();
                $this->Ln();	
                $this->SetFont('Arial','B',9);	
                $this->SetFillColor(220,220,220);
                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                $this->Cell(15/100*$width,$height,$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['panen'],1,0,'C',1);	
                $this->Cell(15/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);	
				// $this->Cell(15/100*$width,$height,$_SESSION['lang']['tahuntanam'],1,0,'C',1);	
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['janjang'],1,0,'C',1);						
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['bjr'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,'Kg Kebun',1,0,'C',1);	
                $this->Cell(15/100*$width,$height,$_SESSION['lang']['brondolan'],1,0,'C',1);
				$this->Cell(10/100*$width,$height,'Kg PKS',1,1,'C',1);	

            }

			//No	Blok	Tahun Tanam	Janjang	BJR	Kg Kebun	Brondolan (Kg)	Kg PKS

			
			
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
                $pdf->SetFont('Arial','',9);
//kebun_spbdt
				$no=0;
                $str="select * from ".$dbname.".kebun_spbdt_detail   where nospb='".$noSpb."'"; //echo $str;exit();
                $qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$qry->setFetchMode(PDO::FETCH_ASSOC);
				while($res=$qry->fetch()){
					$no+=1;
					$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
					$pdf->Cell(15/100*$width,$height,tanggalnormal($res['tanggalpanen']),1,0,'L',1);	
					$pdf->Cell(15/100*$width,$height,$res['blok'],1,0,'L',1);	
					// $pdf->Cell(15/100*$width,$height,$res['tahuntanam'],1,0,'C',1);
					$pdf->Cell(10/100*$width,$height,number_format($res['jjg']),1,0,'R',1);
					$pdf->Cell(10/100*$width,$height,number_format($res['bjr'],2),1,0,'R',1);
					$pdf->Cell(10/100*$width,$height,number_format($res['kgbjr'],2),1,0,'R',1);
					$pdf->Cell(15/100*$width,$height,number_format($res['brondolan'],2),1,0,'R',1);
					$pdf->Cell(10/100*$width,$height,number_format($res['kgwb'],2),1,1,'R',1);
					
					@$tjjg+=$res['jjg'];
					@$tkgbjr+=$res['kgbjr'];
					@$tbron+=$res['brondolan'];
					@$tkgwb+=$res['kgwb'];
                }
				$pdf->Cell(33/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
				$pdf->Cell(10/100*$width,$height,number_format($tjjg),1,0,'R',1);
				$pdf->Cell(10/100*$width,$height,number_format(fixnan($tkgbjr/$tjjg),2),1,0,'R',1);
				$pdf->Cell(10/100*$width,$height,number_format($tkgbjr,2),1,0,'R',1);
				$pdf->Cell(15/100*$width,$height,number_format($tbron,2),1,0,'R',1);
				$pdf->Cell(10/100*$width,$height,number_format($tkgwb,2),1,0,'R',1);

        $pdf->Output();
?>