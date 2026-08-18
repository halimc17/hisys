<?php
        require_once('master_validation.php');
        require_once('config/connection.php');
        require_once('lib/nangkoelib.php');
        require_once('lib/fpdf.php');
        include_once('lib/zMysql.php');
        include_once('lib/zLib.php');

        $table  = $_GET['table'];
        $column = $_GET['column'];
        $where  = $_GET['cond'];

        //create Header
        class PDF extends FPDF{
                function Header(){
                        global $conn;
                        global $dbname;
                        global $userid;
                        global $notransaksi;
                        global $kodevhc;
                        global $posting;
                        global $bar;
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
                        $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'"; 
                        $res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        while($bar1=$res->fetch())
                        {
                                $namapt=$bar1->namaorganisasi;
                                $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
                                $telp=$bar1->telepon;				 
                        }    
                        $sql2="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->updateby."'";
                        $res=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);
                        $res2=$res->fetch();

                        $sql5="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->postingby."'";
                        $res=$owlPDO->query($sql5) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);           
                        $res5=$res->fetch();

                        $sql3="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeorg."'";
                        $res=$owlPDO->query($sql3) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_OBJ);     
                        $res3=$res->fetch(); 

                        $sqlJnsVhc="select namajenisvhc from ".$dbname.".vhc_5jenisvhc where jenisvhc='".$bar->jenisvhc ."'";
                        $res=$owlPDO->query($sqlJnsVhc) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);            
                        $rJnsVhc=$res->fetch();

                        $sqlJnsVhc="select nopol from ".$dbname.".vhc_5master where kodevhc='".$bar->kodevhc ."'";
                        $res=$owlPDO->query($sqlJnsVhc) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);            
                        $rNopol=$res->fetch();

                        $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->jenisbbm."'";
                        $res=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);      
                        $rBrg=$res->fetch();

                        $strTotBiayaPekerjaan="select SUM(biaya) as totalBiayaPekerjaan from ".$dbname.".vhc_rundt where notransaksi='".$notransaksi."'";
                        $res=$owlPDO->query($strTotBiayaPekerjaan) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);              
                        $resTotBiayaPekerjaan=$res->fetch();

                        $strTotBiayaOperator="select SUM(upah) as upah, SUM(premi) as premi, SUM(penalty) as penalty from ".$dbname.".vhc_runhk  where notransaksi='".$notransaksi."'";
                        $res=$owlPDO->query($strTotBiayaOperator) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);              
                        $resTotBiayaOperator=$res->fetch();

                        $totalBiaya = $resTotBiayaPekerjaan['totalBiayaPekerjaan']+$resTotBiayaOperator['upah']+$resTotBiayaOperator['premi']+$resTotBiayaOperator['penalty'];

                        $valBiaya = $resTotBiayaPekerjaan['totalBiayaPekerjaan'];
                        $valUpah = $resTotBiayaOperator['upah']+$resTotBiayaOperator['premi']+$resTotBiayaOperator['penalty'];

                        $arrHead = setheadreport(getindukPT($bar->kodeorg));
                        $path = $arrHead['logo'];
                        $this->Image($path,7,5,0,20);	
                                $this->SetFont('Arial','B',10);
                                $this->SetFillColor(255,255,255);	
                                $this->SetXY(40,5);   
                        $this->Cell(60,5,$namapt,0,1,'L');	 
                                $this->SetX(40); 		
                        $this->MultiCell(160,5,$alamatpt,0,'L');	
                                $this->SetX(40); 			
                                $this->Cell(60,5,"Tel: ".$telp,0,1,'L');	
                                $this->Ln();
                                $this->SetFont('Arial','U',15);
                                $this->SetY(35);
                                $this->Cell(190,5,strtoupper($_SESSION['lang']['laporanPekerjaan']),0,1,'C');		
                                $this->SetFont('Arial','',6); 
                                $this->SetY(27);
                                $this->SetX(163);
                        $this->Cell(30,10,'PRINT TIME : '.date('d-m-Y H:i:s'),0,1,'L');		
                                $this->Line(10,27,200,27);	
                                $this->Ln();
                                $this->SetFont('Arial','',9); 
                                $this->Cell(30,4,$_SESSION['lang']['notransaksi'],0,0,'L'); 
                                $this->Cell(40,4,": ".$bar->notransaksi,0,1,'L'); 				
                                $this->Cell(30,4,$_SESSION['lang']['tanggal'],0,0,'L'); 
                                $this->Cell(40,4,": ".tanggalnormal($bar->tanggal),0,1,'L'); 
                                $this->Cell(30,4,$_SESSION['lang']['namaorganisasi'],0,0,'L'); 
                                $this->Cell(40,4,": ".$res3->namaorganisasi." [".$bar->kodeorg."]",0,1,'L'); 
                                $this->Cell(30,4,$_SESSION['lang']['jenisvch'],0,0,'L'); 
                                $this->Cell(40,4,": ".$rJnsVhc['namajenisvhc'],0,1,'L'); 		  
                                $this->Cell(30,4,$_SESSION['lang']['kodevhc'],0,0,'L'); 
                                $this->Cell(40,4,": ".$bar->kodevhc." - ".($rNopol['nopol'] != '' ? $rNopol['nopol']." - ".getVhc($bar->kodevhc,'detailvhc') : getVhc($bar->kodevhc,'detailvhc')),0,1,'L'); 
                                /*$this->Cell(30,4,$_SESSION['lang']['vhc_kmhm_awal'],0,0,'L'); 
                                $this->Cell(40,4,": ".$bar->kmhmawal,0,1,'L'); 
                                $this->Cell(30,4,$_SESSION['lang']['vhc_kmhm_akhir'],0,0,'L'); 
                                $this->Cell(40,4,": ".$bar->kmhmakhir,0,1,'L');
                                $this->Cell(30,4,$_SESSION['lang']['satuan'],0,0,'L'); 
                                $this->Cell(40,4,": ".$bar->satuan,0,1,'L');*/
                                $this->Cell(30,4,$_SESSION['lang']['vhc_jenis_bbm'],0,0,'L'); 
                                $this->Cell(40,4,": ".$rBrg['namabarang'],0,1,'L');
                                $this->Cell(30,4,$_SESSION['lang']['vhc_jumlah_bbm'],0,0,'L'); 
                                $this->Cell(40,4,": ".number_format($bar->jlhbbm),0,1,'L');
                                $this->Cell(30,4,$_SESSION['lang']['kontanan'],0,0,'L'); 
                                $this->Cell(40,4,": ".($bar->kontanan != '' ? 'Kontanan' : 'Tidak Kontanan'),0,1,'L');
                                $this->Cell(30,4,$_SESSION['lang']['dbuat_oleh'],0,0,'L'); 
                                $this->Cell(40,4,": ".$res2->namakaryawan."".($bar->createdtime=='0000-00-00 00:00:00'?'':' - '.waktunormal($bar->createdtime)),0,1,'L');  
                                $this->Cell(30,4,$_SESSION['lang']['posted'],0,0,'L'); 
                                $this->Cell(40,4,": ".(isset($res5->namakaryawan)? $res5->namakaryawan: '')."".($bar->postedtime=='0000-00-00 00:00:00'?'':' - '.waktunormal($bar->postedtime)),0,1,'L');  
                                // $this->Cell(30,4,$_SESSION['lang']['biaya'],0,0,'L'); 
                                // $this->Cell(40,4,": ".(isset($valBiaya)? number_format($valBiaya) : '0'),0,1,'L');  
                                // $this->Cell(30,4,$_SESSION['lang']['upah'],0,0,'L'); 
                                // $this->Cell(40,4,": ".(isset($valUpah)? number_format($valUpah) : '0'),0,1,'L');  

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
                $pdf->SetY(90);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(190,5,$_SESSION['lang']['belumposting'],0,0,'C');
        }
        $pdf->SetFont('Arial','U',10);
        $pdf->SetY(95);
        $pdf->Cell(190,5,$_SESSION['lang']['vhc_detail_pekerjaan'],0,1,'L');	
        $pdf->SetFont('Arial','B',5.5);	
        $pdf->SetFillColor(220,220,220);
        $pdf->Cell(5,5,'No',1,0,'C',1);
        $pdf->Cell(81,5,$_SESSION['lang']['vhc_jenis_pekerjaan'],1,0,'C',1);
        $pdf->Cell(17,5,$_SESSION['lang']['alokasibiaya'],1,0,'C',1);		
        $pdf->Cell(15,5,$_SESSION['lang']['vhc_kmhm_awal'],1,0,'C',1);	
        $pdf->Cell(15,5,$_SESSION['lang']['vhc_kmhm_akhir'],1,0,'C',1);	
        $pdf->Cell(13,5,$_SESSION['lang']['prestasi'],1,0,'C',1);	
        $pdf->Cell(7,5,"Jlh Rit",1,0,'C',1);
        $pdf->Cell(40,5,$_SESSION['lang']['keterangan'],1,1,'C',1);
        //$pdf->Cell(25,5,'Total',1,1,'C',1);

        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',7);

        $str="select * from ".$dbname.".vhc_rundt   where notransaksi='".$notransaksi."' order by kmhmawal asc";
        if(count(fetchdata($str))>0){
                $res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $no=0;
                while($res=$res1->fetch())
                {
                        $cellWidth=40; //lebar sel
                        $cellHeight=5; //tinggi sel satu baris normal
                        
                        //periksa apakah teksnya melibihi kolom?
                        if($pdf->GetStringWidth($res['keterangan']) < $cellWidth){
                                //jika tidak, maka tidak melakukan apa-apa
                                $line=1;
                        }else{
                                //jika ya, maka hitung ketinggian yang dibutuhkan untuk sel akan dirapikan
                                //dengan memisahkan teks agar sesuai dengan lebar sel
                                //lalu hitung berapa banyak baris yang dibutuhkan agar teks pas dengan sel
                                
                                $textLength     =strlen($res['keterangan']);	//total panjang teks
                                $errMargin      =5;		//margin kesalahan lebar sel, untuk jaga-jaga
                                $startChar      =0;		//posisi awal karakter untuk setiap baris
                                $maxChar        =0;		//karakter maksimum dalam satu baris, yang akan ditambahkan nanti
                                $textArray      =array();	//untuk menampung data untuk setiap baris
                                $tmpString      ="";		//untuk menampung teks untuk setiap baris (sementara)
                                
                                while($startChar < $textLength){ //perulangan sampai akhir teks
                                        //perulangan sampai karakter maksimum tercapai
                                        while( 
                                                $pdf->GetStringWidth( $tmpString ) < ($cellWidth-$errMargin) &&
                                                ($startChar+$maxChar) < $textLength ) {
                                                $maxChar++;
                                                $tmpString=substr($res['keterangan'],$startChar,$maxChar);
                                        }
                                        //pindahkan ke baris berikutnya
                                        $startChar=$startChar+$maxChar;
                                        //kemudian tambahkan ke dalam array sehingga kita tahu berapa banyak baris yang dibutuhkan
                                        array_push($textArray,$tmpString);
                                        //reset variabel penampung
                                        $maxChar=0;
                                        $tmpString='';
                                        
                                }
                                //dapatkan jumlah baris
                                $line=count($textArray);
                        }
                        
                        $pdf->SetFillColor(255,255,255);
                        $str="select namakegiatan from ".$dbname.".vhc_kegiatan where kodekegiatan = '".$res['jenispekerjaan']."'";
                        $hsl=fetchData($str);
                        $no+=1;
                        $pdf->Cell(5,($line * $cellHeight),$no,1,0,'C',1);
                        $pdf->Cell(81,($line * $cellHeight),$res['jenispekerjaan']." - ".$hsl[0]['namakegiatan'],1,0,'T',1);
                        $pdf->Cell(17,($line * $cellHeight),(strlen($res['alokasibiaya']) >6 ? getIndukBlok($res['alokasibiaya']) : $res['alokasibiaya']),1,0,'C',1);		
                        $pdf->Cell(15,($line * $cellHeight),number_format($res['kmhmawal'],2),1,0,'R',1);
                        $pdf->Cell(15,($line * $cellHeight),number_format($res['kmhmakhir'],2),1,0,'R',1);	
                        $pdf->Cell(13,($line * $cellHeight),$res['beratmuatan'],1,0,'R',1);	
                        $pdf->Cell(7,($line * $cellHeight),$res['jumlahrit'],1,0,'R',1);
                        //kembalikan posisi untuk sel berikutnya di samping MultiCell 
                        //dan offset x dengan lebar MultiCell
                        //memanfaatkan MultiCell sebagai ganti Cell
                        //atur posisi xy untuk sel berikutnya menjadi di sebelahnya.
                        //ingat posisi x dan y sebelum menulis MultiCell
                        $xPos=$pdf->GetX();
                        $yPos=$pdf->GetY();
                        $pdf->MultiCell($cellWidth,$cellHeight,$res['keterangan'],1);
                }
        }else{
                $pdf->Cell(193,5,$_SESSION['lang']['errdatanotexist'],1,0,'C',1);
        }
        $ary=$pdf->GetY();
        $pdf->SetFont('Arial','U',10);
        $pdf->SetY($ary+5);
        $pdf->Ln();
        $pdf->Cell(240,5,$_SESSION['lang']['vhc_detail_operator'],0,1,'L');	
        $pdf->SetFont('Arial','B',7);	
        $pdf->SetFillColor(220,220,220);
        $pdf->Cell(5,5,$_SESSION['lang']['nourut'],1,0,'C',1);
        $pdf->Cell(80,5,$_SESSION['lang']['namakaryawan'],1,0,'C',1);
        $pdf->Cell(20,5,$_SESSION['lang']['vhc_posisi'],1,0,'C',1);
        $pdf->Cell(20,5,$_SESSION['lang']['premi']." ".$_SESSION['lang']['hi'],1,0,'C',1);
        $pdf->Cell(20,5,$_SESSION['lang']['premi']." ".$_SESSION['lang']['sdhi'],1,0,'C',1);
        $pdf->Cell(20,5,$_SESSION['lang']['upah'],1,0,'C',1);
        $pdf->Cell(20,5,$_SESSION['lang']['denda'],1,1,'C',1);
        //$pdf->Cell(25,5,'Total',1,1,'C',1);

        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',6);
        $arrPos=array("0"=>"Operator/Driver","1"=>"Helper");
        $str="select * from ".$dbname.".vhc_runhk  where notransaksi='".$notransaksi."'";
        if(count(fetchdata($str))>0){
                $res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $no=0;
                while($res=$res1->fetch())
                {
                        $no+=1;
                        $sql5="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$res['idkaryawan']."'";
                        $res3=$owlPDO->query($sql5) or die(print " Gagal: ".PDOException::getMessage());
                        $res3->setFetchMode(PDO::FETCH_OBJ);        
                        $res5=$res3->fetch();
                        $strd="select sum(premi) as premisdi from ".$dbname.". vhc_runhk_vw    
                        where idkaryawan='".$res['idkaryawan']."' and tanggal between '".substr($bar->tanggal,0,7)."-01' and '".$bar->tanggal."' ";
                        $res3=$owlPDO->query($strd) or die(print " Gagal: ".PDOException::getMessage());
                        $res3->setFetchMode(PDO::FETCH_ASSOC); 
                        $rstrd=$res3->fetch();
                        $pdf->Cell(5,5,$no,1,0,'C',1);
                        $pdf->Cell(80,5,$res5->namakaryawan,1,0,'L',1);
                        $pdf->Cell(20,5,$arrPos[$res['posisi']],1,0,'C',1);	
                        $pdf->Cell(20,5,number_format($res['premi'],0),1,0,'R',1);
                        $pdf->Cell(20,5,number_format($rstrd['premisdi'],0),1,0,'R',1);
                        $pdf->Cell(20,5,number_format($res['upah'],0),1,0,'R',1);
                        $pdf->Cell(20,5,number_format($res['penalty'],0),1,1,'R',1);

                }
        }else{
                $pdf->Cell(185,5,$_SESSION['lang']['errdatanotexist'],1,0,'C',1);
        }
        //footer================================
        $pdf->Output();
?>
