<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$kdOrg=checkPostGet('kodeorg','');
$kegiatan=checkPostGet('kegiatan','');
$tgl1_=checkPostGet('tgl1','');
$tgl2_=checkPostGet('tgl2','');
$intiplasma=checkPostGet('intiplasma','');
$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$tgl1_=tanggalsystem($tgl1_); $tgl1=substr($tgl1_,0,4).'-'.substr($tgl1_,4,2).'-'.substr($tgl1_,6,2);
$tgl2_=tanggalsystem($tgl2_); $tgl2=substr($tgl2_,0,4).'-'.substr($tgl2_,4,2).'-'.substr($tgl2_,6,2);


if($intiplasma!='')
{
	$whrip=" and kodeblok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$intiplasma."' and kodeorg like '".$kdOrg."%') ";
}


if(($proses=='preview')or($proses=='excel')or($proses=='pdf')){
    if(($tgl1_=='')or($tgl2_=='')){
            echo"Error: Date required."; exit;
    }

    if($tgl1>$tgl2){
            echo"Error: First date must lower than the second."; exit;
    }
	
}
#ambil tahun tanam
$tahuntanam=Array();
$str="select kodeorg,tahuntanam from ".$dbname.".setup_blok where kodeorg like '".$kdOrg."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $tahuntanam[$bar->kodeorg]=$bar->tahuntanam;
}
#ambil namakegiatan
$namakegiatan=Array();
if($_SESSION['language']=='EN'){
    $zz='namaakun1 as namaakun';
}else{
    $zz='namaakun as namaakun';
}
$str="select noakun,".$zz." from ".$dbname.".keu_5akun where length(noakun)=7";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namakegiatan[$bar->noakun]=$bar->namaakun;
}

#ambil satuan kegiatan:
$satuan=Array();
$str="select kodekegiatan,satuan from ".$dbname.".setup_kegiatan order by kodekegiatan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $satuan[$bar->kodekegiatan]=$bar->satuan;
}

#generate SQL
$str="select noakun,sum(debet) as biaya,kodeblok from ".$dbname.".keu_jurnaldt_vw 
      where tanggal between '".$tgl1."' and '".$tgl2."' and noakun like '".substr($kegiatan,0,7)."%' and kodeorg='".$kdOrg."' and kodekegiatan='".$kegiatan."'
	  ".$whrip."
      group by noakun,kodeblok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

#ambil hasil kerja(prestasi)
if(substr($kegiatan,0,7)=='6110101')
        $kegiatanx='0';
else
      $kegiatanx=$kegiatan;
$str1="SELECT a.kodeorg,case a.kodekegiatan
   when '' then ".$kegiatan." 
   when '0' then ".$kegiatan."
   else a.kodekegiatan end as kegiatan,
   sum(a.hasilkerja) as hasil,sum(a.hasilkerjakg) as kg FROM ".$dbname.".kebun_prestasi_detail a left join ".$dbname.".kebun_aktifitas b
   on a.notransaksi=b.notransaksi where tanggal between '".$tgl1."' and '".$tgl2."' and a.notransaksi like '%".$kdOrg."%'
   and kodekegiatan='".$kegiatanx."'   
    group by kodeorg,kegiatan";
// echo $str1;
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_ASSOC);
 while($bar=$res1->fetch()){
     $pres[$bar['kodeorg']][$bar['kegiatan']]=$bar['hasil'];
     $kg[$bar['kodeorg']][$bar['kegiatan']]=$bar['kg'];
 }
 // echo "<pre>";
 // print_r ($pres);
 // echo "</pre>";
 
#+++++++++++++++++++++++process data

                if($proses=='excel')$stream.="<table cellspacing='1' border='1' class='sortable'>";
                else $stream.="<table cellspacing='1' border='0' class='sortable'>";
                $stream.="<thead class=rowheader>
                <tr>
                <td align=center>No</td>
                <td align=center>".$_SESSION['lang']['noakun']."</td>
                <td align=center>".$_SESSION['lang']['kegiatan']."</td>    
                <td align=center>".$_SESSION['lang']['blok']."</td>
                <td align=center>".$_SESSION['lang']['tahuntanam']."</td> 
                <td align=center>".$_SESSION['lang']['satuan']."</td>  
				<td align=center>".$_SESSION['lang']['hasilkerjajumlah']."</td>
                              
                <td align=center>".$_SESSION['lang']['panen']." (Kg)</td>    
                <td align=center>".$_SESSION['lang']['jumlah']." (Rp)</td>
                </tr></thead>
                <tbody>";
$no=0;
$ttl=$tths=$ttkg=0;
while($bar=$res->fetch())
{
    $no+=1;
    $stream.="<tr class=rowcontent>
                <td align=center>".$no."</td>
                <td>".$bar->noakun."</td>    
                <td>".$namakegiatan[$bar->noakun]."</td>  
                <td>".$namaOrg[$bar->kodeblok]."</td>
                <td>".$tahuntanam[$bar->kodeblok]."</td>
                <td>".$satuan[$kegiatan]."</td>
				<td align=right>".number_format($pres[$bar->kodeblok][$kegiatan])."</td>
                
                <td align=right>".number_format($kg[$bar->kodeblok][$kegiatan])."</td>    
                <td align=right>".number_format($bar->biaya)."</td>
              </tr>";
    $ttl+=$bar->biaya;
    $tths+=$pres[$bar->kodeblok][$kegiatan];
    $ttkg+=$kg[$bar->kodeblok][$kegiatan];
}
$stream.="<tr class=rowcontent>
                <td colspan=5>Total</td>
                <td></td>    
				<td align=right>".number_format($tths)."</td>
                
                <td align=right>".number_format($ttkg)."</td>    
                <td align=right>".number_format($ttl)."</td>
              </tr>";
$stream.="</tbody><tfoot></tfoot></table>";
 #+++++++++++++++++++++++++++++++++++++++++++++++++            
switch($proses)
{
        case 'preview':
                     
		    echo $stream;
        break;
       case 'excel':
            $qwe=date("YmdHms");
            $nop_="Laporan Biaya Kegiatan Per Blok ".$kdOrg."_".$kegiatan."_".$qwe;
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
                if(!fwrite($handle,$stream)){
                    echo "<script language=javascript1.2>
                        parent.window.alert('Cant convert to excel format');
                        </script>";
                    exit;
                }
                else
                {
                    echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls';
                    </script>";
                }
                closedir($handle);
                //  $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                //  gzwrite($gztralala, $stream);
                //  gzclose($gztralala);
                //  echo "<script language=javascript1.2>
                //     window.location='tempExcel/".$nop_.".xls.gz';
                //     </script>";
            } 
	    break;
	case'pdf':
            class PDF extends FPDF
                    {
                        function Header() {
                            global $conn;
                            global $dbname;
                            global $align;
                            global $length;
                            global $colArr;
                            global $title;
							global $kdOrg;
							global $kdAfd;
							global $tgl1;
							global $tgl2;
							global $where;
							global $nmOrg;
							global $lok;
							global $kegiatan;
							global $owlPDO;

                                            $cols=247.5;
											
                            $arrHead = setheadreport(substr($kdOrg,0,4));
				
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

                                            $this->Cell($width,$height,"Laporan Biaya Kegiatan Per Blok ".$kdOrg." ".$kegiatan,'',0,'C');
                                            $this->Ln();
                                            $this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2),'',0,'C');
                                            $this->Ln();
                            $this->SetFont('Arial','B',10);
                            $this->SetFillColor(220,220,220);
                                            $this->Cell(8/100*$width,$height,$_SESSION['lang']['nomor'],1,0,'C',1);		
                                            $this->Cell(12/100*$width,$height,$_SESSION['lang']['noakun'],1,0,'C',1);		
                                            $this->Cell(30/100*$width,$height,$_SESSION['lang']['kegiatan'],1,0,'C',1);		
                                            $this->Cell(15/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);		
                                            $this->Cell(15/100*$width,$height,$_SESSION['lang']['tahuntanam'],1,0,'C',1);		
                                            $this->Cell(15/100*$width,$height,$_SESSION['lang']['jumlah'],1,1,'C',1);		
                       }

                        function Footer()
                        {
                            $this->SetY(-15);
                            $this->SetFont('Arial','I',8);
                            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
                        }
                    }
                    $pdf=new PDF('P','pt','Legal');
                    $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
                    $height = 13;
                            $pdf->AddPage();
                            $pdf->SetFillColor(255,255,255);
                            $pdf->SetFont('Arial','',10);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
$ttl=0;
while($bar=$res->fetch())
{
    $no+=1;
    $pdf->Cell(8/100*$width,$height,$no,1,0,'C',1);		
    $pdf->Cell(12/100*$width,$height,$bar->noakun,1,0,'C',1);		
    $pdf->Cell(30/100*$width,$height,$namakegiatan[$bar->noakun],1,0,'L',1);		
    $pdf->Cell(15/100*$width,$height,$namaOrg[$bar->kodeblok],1,0,'L',1);		
    $pdf->Cell(15/100*$width,$height,$tahuntanam[$bar->kodeblok],1,0,'C',1);		
    $pdf->Cell(15/100*$width,$height,number_format($bar->biaya),1,1,'R',1);		
    $ttl+=$bar->biaya;
}
    $pdf->Cell(80/100*$width,$height,'Total',1,0,'C',1);		
    $pdf->Cell(15/100*$width,$height,number_format($ttl),1,1,'R',1);		

                    $pdf->Output();
            
	break;
	default:
	break;
}

?>