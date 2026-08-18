<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');

//=============
        $tmp=explode(',',$_GET['column']);
        $kdOrg=$tmp[0];
        $tgl=$tmp[1];
        $notransaksi=$tmp[2];


//create Header
class PDF extends FPDF{
    
    function Header()
        {
        global $conn;
        global $dbname;
        global $userid;
        global $kdOrg;
        global $tgl;
        global $akhirY;
        global $akhirYline;
        global $owlPDO;
        global $tabledt;

                if($_GET['table']=='sdm_splemburht'){
                    $tabledt='sdm_splemburdt';
                }else{
                    $tabledt='sdm_lemburdt';
                }

                $sInduk="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kdOrg."'";
                $qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
                $qInduk->setFetchMode(PDO::FETCH_ASSOC);
                $rInduk=$qInduk->fetch();

                  // $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'"; 
                   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'"; 
                   $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                   $res1->setFetchMode(PDO::FETCH_OBJ);
                   while($bar1=$res1->fetch()){
                         $nama=$bar1->namaorganisasi;
                         $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
                         $telp=$bar1->telepon;               
                   }    

                   $sIsi="select * from ".$dbname.".".$_GET['table']." where notransaksi='".$notransaksi."'";
                   // echo $sIsi;
                   $qIsi=$owlPDO->query($sIsi) or die(print " Gagal: ".PDOException::getMessage());
                   $qIsi->setFetchMode(PDO::FETCH_ASSOC);
                   $rIsi=$qIsi->fetch();

                        $sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdOrg."'";
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
                        $this->Cell(75,5,$kdOrg,'',0,'L');        
                        $this->Cell(25,5,$_SESSION['lang']['nm_perusahaan'],'',0,'L');
                        $this->Cell(2,5,':','',0,'L');
                        $this->Cell(35,5,$rOrg['namaorganisasi'],0,1,'L');
                        $this->Cell(35,5,$_SESSION['lang']['tanggal'],'',0,'L');
                        $this->Cell(2,5,':','',0,'L');
                        $this->Cell(75,5,$tgl,'',0,'L');        
                        $this->Cell(25,5,$_SESSION['lang']['periode'],'',0,'L');
                        $this->Cell(2,5,':','',0,'L');
                        $this->Cell(35,5,substr($tgl,3,7),0,1,'L');  
     $this->Ln();

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
        $pdf->Cell(190,5,strtoupper($_SESSION['lang']['list']." Surat Pengantar Lembur"),0,1,'C');    
        $pdf->Ln(); 
        $pdf->SetFont('Arial','B',8);   
        $pdf->SetFillColor(220,220,220);
        $pdf->Cell(8,5,'No',1,0,'L',1);
        $pdf->Cell(30,5,$_SESSION['lang']['namakaryawan'],1,0,'L',1);   
        $pdf->Cell(20,5,$_SESSION['lang']['tipelembur'],1,0,'L',1);
        $pdf->Cell(28,5,$_SESSION['lang']['jamaktual'],1,0,'C',1);
        // $pdf->Cell(30,5,$_SESSION['lang']['uangmakan'],1,0,'C',1);
        // $pdf->Cell(38,5,$_SESSION['lang']['penggantiantransport'],1,0,'C',1);
        $pdf->Cell(25,5,$_SESSION['lang']['uangkelebihanjam'],1,0,'C',1);       
        $pdf->Cell(18,5,$_SESSION['lang']['jam'].' '.$_SESSION['lang']['mulai'],1,0,'C',1);
        $pdf->Cell(18,5,$_SESSION['lang']['jamselesai'],1,0,'C',1);
        $pdf->Cell(50,5,$_SESSION['lang']['keterangan'],1,1,'C',1);
        

        //$pdf->Cell(25,5,'Total',1,1,'C',1);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);
                $str="select * from ".$dbname.".".$tabledt." where notransaksi='".$notransaksi."' order by tanggal asc"; //echo $str;exit();
                // echo $str;
                $re=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $re->setFetchMode(PDO::FETCH_ASSOC);
                $no=0;
                while($res=$re->fetch())
                {
                        $sKry="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$res['karyawanid']."'";
                        $qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
                        $qKry->setFetchMode(PDO::FETCH_ASSOC);
                        $rKry=$qKry->fetch();

                        $arrTipeLembur=array($_SESSION['lang']['haribiasa'],$_SESSION['lang']['hariminggu'],$_SESSION['lang']['harilibur'],$_SESSION['lang']['hariraya']);


                        $no+=1;
                        $pdf->Cell(8,5,$no,1,0,'C',1);
                        $pdf->Cell(30,5,$rKry['namakaryawan'],1,0,'L',1);   
                        $pdf->Cell(20,5,$arrTipeLembur[$res['tipelembur']],1,0,'L',1);
                        $pdf->Cell(28,5,number_format($res['jamaktual'],2),1,0,'C',1);
                        // $pdf->Cell(30,5,number_format($res['uangmakan'],2),1,0,'C',1);
                        // $pdf->Cell(38,5,number_format($res['uangtransport'],2),1,0,'C',1);
                        $pdf->Cell(25,5,number_format($res['uangkelebihanjam'],2),1,0,'R',1);
                        $pdf->Cell(18,5,$res['jammulai'],1,0,'C',1);
                        $pdf->Cell(18,5,$res['jamselesai'],1,0,'C',1);
                        $pdf->Cell(50,5,$res['ket'],1,1,'L',1);
                        
                        $ttljam+=$res['jamaktual'];
                        $ttlrp+=$res['uangkelebihanjam'];
                                                
                }
                $pdf->SetFillColor(220,220,220);
                $pdf->Cell(58,5,$_SESSION['lang']['total'],1,0,'C',1);
                $pdf->Cell(28,5,number_format($ttljam,2),1,0,'C',1);
                $pdf->Cell(25,5,number_format($ttlrp,2),1,0,'R',1);
                $pdf->Cell(18,5,'',1,0,'L',1);
                $pdf->Cell(18,5,'',1,0,'L',1);
                $pdf->Cell(50,5,'',1,1,'L',1);
        $pdf->Output();
?>
