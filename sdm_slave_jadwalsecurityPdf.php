<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
require_once('lib/zLib.php');

//=============
        $tmp=explode(',',$_GET['column']);
        $kodeorg=$tmp[0];
        $periode=$tmp[1];
        $pos=$tmp[2];
        $minggu=$tmp[3];

//create Header
class PDF extends FPDF{
    
    function Header()
        {
        global $conn;
        global $dbname;
        global $userid;
        global $kodeorg;
        global $periode;
        global $minggu;
        global $pos;
        global $akhirY;
        global $akhirYline;
        global $owlPDO;

                $sInduk="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
                $qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
                $qInduk->setFetchMode(PDO::FETCH_ASSOC);
                $rInduk=$qInduk->fetch();

                  // $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'"; 
                $idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);
                   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'"; 
                   $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                   $res1->setFetchMode(PDO::FETCH_OBJ);
                   while($bar1=$res1->fetch()){
                         $nama=$bar1->namaorganisasi;
                         $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
                         $telp=$bar1->telepon;               
                   }    

                    $optPos = makeOption($dbname,"sdm_5possecurity", "nopos,namapos","unit='".$idOrg."'",'',true);

                    $sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
                        // echo $sOrg;
                    $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
                    $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                    $rOrg=$qOrg->fetch();

                // $path='images/logo.jpg';
            $arrHead = setheadreport('',$_SESSION['org']['kodeorganisasi']);
            $path=$arrHead['logo'];
            $this->Image($path,15,10,20);   
                $this->SetFont('Arial','B',10);
                $this->SetFillColor(255,255,255);   
                $this->SetX(40);   
            $this->Cell(60,5,$nama,0,1,'L');     
                $this->SetX(40); 
                
                $this->MultiCell(150, 5, $alamatpt, 0);
                
            //$this->Cell(60,5,$alamatpt,0,1,'L');  
                $this->SetX(40);            
                $this->Cell(60,5,"Tel: ".$telp,0,1,'L');    
                $this->Ln();
                $this->SetFont('Arial','B',8); 
                $this->Cell(20,5,$nama,'',1,'L');
        $this->SetFont('Arial','',8);
        
        $akhirY=$this->GetY()-5;
        
        
        
                $this->Line(10,$akhirY,200,$akhirY);    
                
                $akhirYline=$this->GetY();
                
                $this->SetY($akhirYline);

                        $this->Cell(35,5,$_SESSION['lang']['kodeorg'],'',0,'L');
                        $this->Cell(2,5,':','',0,'L');
                        $this->Cell(75,5,$kodeorg,'',0,'L');        
                        $this->Cell(25,5,$_SESSION['lang']['nm_perusahaan'],'',0,'L');
                        $this->Cell(2,5,':','',0,'L');
                        $this->Cell(35,5,$rOrg['namaorganisasi'],0,1,'L');
                        $this->Cell(35,5,$_SESSION['lang']['periode'],'',0,'L');
                        $this->Cell(2,5,':','',0,'L');
                        $this->Cell(75,5,$periode,'',0,'L');        
                        $this->Cell(25,5,'POS ','',0,'L');
                        $this->Cell(2,5,':','',0,'L');
                        $this->Cell(35,5,$optPos[$pos],'',0,'L');          
                        
     $this->Ln();
     $this->Cell(35,5,'Minggu Ke ','',0,'L');
                        $this->Cell(2,5,':','',0,'L');
                        $this->Cell(75,5,$minggu,'',0,'L');  

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
            $pdf->Ln();

            $pdf->SetFont('Arial','U',15);
            $pdf->SetY(55);
            $pdf->Cell(190,10,strtoupper('Jadwal Security'),0,1,'C');    
            $pdf->Ln(); 
            $pdf->SetFont('Arial','B',8);   
            $pdf->SetFillColor(220,220,220);
            $pdf->Cell(8,5,'No',1,0,'L',1);
            $pdf->Cell(30,5,$_SESSION['lang']['namakaryawan'],1,0,'L',1);
            $sIsi="select distinct tanggal from ".$dbname.".sdm_jadwalsecuritydt where notransaksi='".$kodeorg."/".$periode."/".$pos."/".$minggu."' order by tanggal asc";
            $qIsi=$owlPDO->query($sIsi) or die(print " Gagal: ".PDOException::getMessage());
            $qIsi->setFetchMode(PDO::FETCH_ASSOC);
            while($rIsi=$qIsi->fetch())
            {  
                $pdf->Cell(20,5,tanggalnormal($rIsi['tanggal']),1,0,'L',1);
            }
            $pdf->Ln();
           
            $kwhere = " (lokasitugas='" . $kodeorg . "' or subbagian='".$kodeorg."') and (tanggalkeluar>='" . $periode . "' or tanggalkeluar='0000-00-00')";
            $kwhere.=" and kodejabatan in ('95','131','16','45','58','135') ";
            $optKaryawan = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan",$kwhere);
            $optShift = makeOption($dbname,"sdm_5shiftsecurity","kodeshift,namashift",'','',true);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',8);
                    $str="select distinct distinct karyawanid from ".$dbname.".sdm_jadwalsecuritydt where notransaksi='".$kodeorg."/".$periode."/".$pos."/".$minggu."' order by tanggal asc"; //echo $str;exit();
                    $re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $re->setFetchMode(PDO::FETCH_ASSOC);
                    $no=0;
                    while($res=$re->fetch())
                    {
                        $no+=1;
                        $pdf->Cell(8,5,$no,1,0,'C',1);
                        $pdf->Cell(30,5,$optKaryawan[$res['karyawanid']],1,0,'L',1); 
                        $sKry="select distinct tanggal from ".$dbname.".sdm_jadwalsecuritydt where notransaksi='".$kodeorg."/".$periode."/".$pos."/".$minggu."' and karyawanid='".$res['karyawanid']."' order by tanggal asc";
                        $qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
                        $qKry->setFetchMode(PDO::FETCH_ASSOC);
                        while($rKry=$qKry->fetch())
                        {
                                
                            $dstr="select kodeshift from ".$dbname.".sdm_jadwalsecuritydt where notransaksi='".$kodeorg."/".$periode."/".$pos."/".$minggu."' and karyawanid='".$res['karyawanid']."' and tanggal='".$rKry['tanggal']."' order by tanggal asc"; //echo $str;exit();
                            $dre=$owlPDO->query($dstr) or die(print " Gagal: ".PDOException::getMessage());
                            $dre->setFetchMode(PDO::FETCH_ASSOC);  
                            while($drKry=$dre->fetch())
                            {
                                    $pdf->Cell(20,5,$optShift[$drKry['kodeshift']],1,0,'C',1);
                            }
                        }

                        $pdf->Ln();
                                                    
                    }
                    $pdf->SetFillColor(220,220,220);
                    $sIsi="select distinct tanggal from ".$dbname.".sdm_jadwalsecuritydt where notransaksi='".$kodeorg."/".$periode."/".$pos."/".$minggu."' order by tanggal asc";
                    $qIsi=$owlPDO->query($sIsi) or die(print " Gagal: ".PDOException::getMessage());
                    $qIsi->setFetchMode(PDO::FETCH_ASSOC);
                    $pdf->Cell(8,5,'',1,0,'L',1);
                    $pdf->Cell(30,5,'',1,0,'L',1);
                    while($rIsi=$qIsi->fetch())
                    {  
                        $pdf->Cell(20,5,'',1,0,'L',1);
                    }
        

        $pdf->Output();
?>
