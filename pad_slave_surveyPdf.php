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
        $notransaksi=$tmp[0];
        $kodeorg=$tmp[1];

//create Header
class PDF extends FPDF{
    
    function Header()
        {
        global $conn;
        global $dbname;
        global $userid;
        global $kodeorg;
        global $notransaksi;
        global $akhirY;
        global $akhirYline;
        global $owlPDO;

                $sInduk="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
                $qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
                $qInduk->setFetchMode(PDO::FETCH_ASSOC);
                $rInduk=$qInduk->fetch();

                  // $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'"; 
                   $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'"; 
                   $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                   $res1->setFetchMode(PDO::FETCH_OBJ);
                   while($bar1=$res1->fetch()){
                         $nama=$bar1->namaorganisasi;
                         $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
                         $telp=$bar1->telepon;               
                   }    

                  

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
                        $this->Cell(35,5,$_SESSION['lang']['notransaksi'],'',0,'L');
                        $this->Cell(2,5,':','',0,'L');
                        $this->Cell(75,5,$notransaksi,'',0,'L');        
                        $this->Cell(25,5,'','',0,'L');
                        $this->Cell(2,5,':','',0,'L');
                        $this->Cell(35,5,'','',0,'L');  
     $this->Ln();

        }


        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        }

}
            $sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";

            $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
            $qOrg->setFetchMode(PDO::FETCH_ASSOC);
            $rOrg=$qOrg->fetch();

            $sqlht = selectQuery($dbname,"pad_surveyht","notransaksi,kodeorg","notransaksi='".$notransaksi."'","notransaksi asc");
            $resht = fetchdata($sqlht);

            $sqldt = selectQuery($dbname,"pad_surveydt","*","induk='".$notransaksi."'","id asc");
            $resdt = fetchdata($sqldt);

            $sqldtsubi = selectQuery($dbname,"pad_surveydt2","*","induk='".$resdt[0]['id']."' and rincian='-' ","id asc");
            $resdt2sub1 = fetchdata($sqldtsubi);
            $jlhsub1 = count($resdt2sub1);

            $sqldtsubo = selectQuery($dbname,"pad_surveydt2","*","induk='".$resdt[0]['id']."' and rincian !='-' and rincian is not null");
            $resdt2sub2 = fetchdata($sqldtsubo);
            $jlhsub2 = count($resdt2sub2);

            $sqldiscint = selectQuery($dbname,"pad_surveydt2","subjenis","induk='".$resdt[0]['id']."' and rincian !='-' and rincian is not null","id asc",true);
            $ressubjenis2 = fetchdata($sqldiscint);
            $jlhsubjenis2 = count($ressubjenis2);

            

        $pdf=new PDF('L','mm','A4');

            $pdf->AddPage();
            $pdf->Ln();

            $pdf->SetFont('Arial','U',15);
            $pdf->SetY(55);
            $pdf->Cell(190,5,strtoupper('Survey GIS'),0,1,'C');    
            $pdf->Ln(); 
            $pdf->SetFont('Arial','B',8);   
            $pdf->SetFillColor(220,220,220);
            $pdf->Cell(8,5,'No',1,0,'L',1);
            $pdf->Cell(30,5,'Menu',1,0,'L',1);
            $pdf->Cell(30,5,'Sub Menu 1',1,0,'L',1);
            $pdf->Cell(30,5,'Sub Menu 2',1,0,'L',1);
            
            if($jlhsub2==0)
            {
                $pdf->Cell(30,5,'Rincian Sub Menu 2',1,0,'L',1);
                $pdf->Cell(30,5,'Keterangan',1,0,'L',1);
            }
            else
            {
                $pdf->Cell(30,5,'Rincian Sub Menu 2',1,0,'L',1);
                $pdf->Cell(30,5,'Rincian Sub Menu 2.1',1,0,'L',1);
                $pdf->Cell(30,5,'Keterangan Rincian Sub Menu 2.1',1,0,'L',1);
                $pdf->Cell(30,5,'Keterangan',1,0,'L',1);
            }
           
            $pdf->Ln();
           
            
            
            $optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","tipe='PT'",'',true);
            $optTipe = makeOption($dbname,"pad_5typesurvey","kodesurvey,namasurvey",'','',true);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',8);
                    
                      
                    //$no+=1;
                    $datashow=array();
                    if($jlhsub2==0)
                    {
                        $pdf->Cell(8,5,'1',1,0,'C',1);
                        foreach ($resdt2sub1 as $keysub => $valsub) {
                            $datashow['no'][]=1;
                            $datashow['menu'][]=$resdt[0]['tipe'];
                            $datashow['submenu1'][]=$rOrg['namaperusahaan'];
                            $datashow['submenu2'][]=$resdt[0]['jenis'];
                            $datashow['rinciansubmenu2'][]=$valsub['subjenis'];
                            $datashow['keterangan'][]=$valsub['keterangan'];
                        }

                        foreach ($datashow as $keydata => $valdata) {
                            foreach ($valdata as $key => $value) {
                                $pdf->Cell(8,5,$valdata[1],1,0,'C',1);
                                $pdf->Cell(30,5,$valdata[1],1,0,'L',1);
                                $pdf->Cell(30,5,$valdata[1],1,0,'L',1);
                                $pdf->Cell(30,5,$valdata[1],1,0,'L',1);
                                $pdf->Cell(30,5,$valdata[1],1,0,'L',1);
                                $pdf->Cell(30,5,$valdata[1],1,0,'L',1);
                                $pdf->Cell(30,5,$valdata[1],1,0,'L',1);
                                $pdf->Cell(30,5,$valdata[1],1,0,'L',1);
                                $pdf->Cell(30,5,$valdata[1],1,0,'L',1); 
                            }
                            
                                 
                            $pdf->Ln();
                        }
                        

                    }
                    else
                    {
                        foreach ($resdt2sub1 as $keysub => $valsub) {
                            $datashow['no'][]=1;
                            $datashow['menu'][]=$resdt[0]['tipe'];
                            $datashow['submenu1'][]=$rOrg['namaperusahaan'];
                            $datashow['submenu2'][]=$resdt[0]['jenis'];
                            $datashow['rinciansubmenu2'][]=$valsub['subjenis'];
                        }

                        foreach ($ressubjenis2 as $keysub2 => $valsub2) {
                            $datashow['rinciansubmenu21'][]=$valsub2['subjenis'];

                            $sqlsubsub=selectQuery($dbname,"pad_surveydt2","*","induk='".$resdt[0]['id']."' and subjenis='".$valsub2['subjenis']."'","id asc");
                            $ressubsub=fetchdata($sqlsubsub);
                            $jlhsubsub=count($ressubsub);  

                            foreach ($ressubsub as $keysubsub => $valsubsub) {
                                $datashow['keteranganrinciansubmenu21'][]=$valsubsub['rincian'];
                                $datashow['keterangan'][]=$valsubsub['keterangan'];
                            }

                        }

                        foreach ($datashow as $keydata => $valdata) {
                            $pdf->Cell(8,5,$no,1,0,'C',1);
                            $pdf->Cell(30,5,$rOrg['namaorganisasi'],1,0,'L',1);
                            $pdf->Cell(30,5,'Sub Menu 1',1,0,'L',1);
                            $pdf->Cell(30,5,'Sub Menu 2',1,0,'L',1);
                            $pdf->Cell(30,5,'Rincian Sub Menu 2',1,0,'L',1);
                            $pdf->Cell(30,5,'Rincian Sub Menu 2.1',1,0,'L',1);
                            $pdf->Cell(30,5,'Keterangan Rincian Sub Menu 2.1',1,0,'L',1);
                            $pdf->Cell(30,5,'Rincian Sub Menu 2',1,0,'L',1);
                            $pdf->Cell(30,5,'Keterangan',1,0,'L',1); 
                                 
                            $pdf->Ln();
                        }

                    }
                    

                    $pdf->SetFillColor(220,220,220);
                    $pdf->Cell(8,5,'',1,0,'L',1);
                    $pdf->Cell(30,5,'',1,0,'L',1);
                    $pdf->Cell(30,5,'',1,0,'L',1);
                    $pdf->Cell(30,5,'',1,0,'L',1);
                    $pdf->Cell(30,5,'',1,0,'L',1);
                    $pdf->Cell(30,5,'',1,0,'L',1);
                    $pdf->Cell(30,5,'',1,0,'L',1);
                    $pdf->Cell(30,5,'',1,0,'L',1);
                    $pdf->Cell(30,5,'',1,0,'L',1);
        
        $pdf->Output();
?>
