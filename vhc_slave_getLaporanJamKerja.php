<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');
require_once('lib/nangkoelib.php');

$param=$_POST;
$apa=checkPostGet('apa','');
$tgl1=explode("-",checkPostGet('tgl1',''));
$tgl2=explode("-",checkPostGet('tgl2',''));
$kodetraksi=checkPostGet('kodetraksi','');

$stream='';

@$tanggal1=$tgl1[2]."-".$tgl1[1]."-".$tgl1[0];
@$tanggal2=$tgl2[2]."-".$tgl2[1]."-".$tgl2[0];
@$tanggal1_=$tgl1[0]."-".$tgl1[1]."-".$tgl1[2];
@$tanggal2_=$tgl2[0]."-".$tgl2[1]."-".$tgl2[2];

$str="select tanggal, sum(jumlah) as jumlah,kodevhc from ".$dbname.".vhc_rundt a 
      left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
      where tanggal >='".$tanggal1."' and tanggal <='".$tanggal2."' and kodevhc in (select kodevhc from ".$dbname.".vhc_5master
      where kodetraksi='".$kodetraksi."')
      group by tanggal, kodevhc";
if($apa=='')$stream.= "<table class=sortable cellpadding=5 cellspacing=1 border=0><thead>";
else $stream.= "<table class=sortable cellspacing=1 border=1><thead>";
      $stream.="<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['kodetraksi']."</th>
				<th align=center>".$_SESSION['lang']['kodevhc']."</th>
				<th align=center>".$_SESSION['lang']['nopol']."</th>
				<th align=center>".$_SESSION['lang']['detail']."</th>
				<th align=center width=100px>".$_SESSION['lang']['jmljamkerja']."</th>
			  </tr>
			  </thead>
			  <tbody>";
$no=0;
$resz=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$resz->setFetchMode(PDO::FETCH_OBJ);
while($bar=$resz->fetch())
{
   $no+=1;
    $stream.="<tr class=rowcontent title='Click to see detail' style='cursor:pointer;' onclick=\"loadjamDetail('".$bar->kodevhc."','".$bar->tanggal."',event);\">
          <td align=center>".$no."</td>
          <td>".$bar->tanggal."</td>     
          <td>".getNamaOrg($kodetraksi)."</td>
          <td>".$bar->kodevhc."</td>
          <td>".getNopol($bar->kodevhc)."</td>
          <td>".getNopol($bar->kodevhc,'d')."</td>
          <td align=right>".number_format($bar->jumlah,2)."</td>
      </tr>";    
}
$stream.="</tbody><tfoot></tfoot></table>";

if ($apa==''){
    echo $stream;
}
if ($apa=='excel'){
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
$nop_="TotalJamKerjaKendAB ".$kodetraksi." ".$tanggal1_." sd ".$tanggal2_;
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
 if(!fwrite($handle,$stream))
 {
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
fclose($handle);
}

}

$lebar1=10; // nomor
$lebar2=10; // tanggal
$lebar3=10; // kotrak
$lebar4=15; // koken
$lebar5=15; // jam

if($apa=='pdf'){
        class PDF extends FPDF
        {
            function Header() {
                global $kodeorg;
                global $thnbudget;
                global $lebar1;
                global $lebar2;
                global $lebar3;
                global $lebar4;
                global $lebar5;
                global $dbname;
                global $kodetraksi;
                global $tanggal1_;
                global $tanggal2_;
                
                # Bulan
               // $optBulan = 
                
                # Alamat & No Telp
                $query2 = selectQuery($dbname,'organisasi','namaorganisasi,induk',
                    "kodeorganisasi='".$kodetraksi."'");
                $orgData2 = fetchData($query2);
                $namatraksi=$orgData2[0]['namaorganisasi'];
                $query1 = selectQuery($dbname,'organisasi','induk',
                    "kodeorganisasi='".$orgData2[0]['induk']."'");
                $orgData1 = fetchData($query1);
                $query = selectQuery($dbname,'organisasi','namaorganisasi,alamat,telepon',
                    "kodeorganisasi='".$orgData1[0]['induk']."'");
                $orgData = fetchData($query);
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$orgData[0]['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
                $this->SetFont('Arial','',8);
                
              	$this->Cell((10/100*$width)-5,$height,$_SESSION['lang']['kodetraksi'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
               	$this->Cell(12/100*$width,$height,$namatraksi,'',0,'L');		
               	$this->Cell(52/100*$width,$height,'','',0,'L');		
              	$this->Cell((10/100*$width)-5,$height,'Printed By','',0,'L');
                $this->Cell(5,$height,':','',0,'L');
               	$this->Cell(15/100*$width,$height,$_SESSION['empl']['name'],'',1,'L');
                
              	$this->Cell((10/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
               	$this->Cell(12/100*$width,$height,$tanggal1_.' s/d '.$tanggal2_,'',0,'L');		
               	$this->Cell(52/100*$width,'','',0,'L');		
              	$this->Cell((10/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
               	$this->Cell(15/100*$width,$height,date('d-m-Y H:i:s'),'',1,'L');
                
                $title=$_SESSION['lang']['jmljamkerja'];		
                $this->Ln();
                $this->SetFont('Arial','U',10);
                $this->Cell($width,$height,strtoupper($title),0,1,'C');	
                $this->Ln();	
                $this->SetFont('Arial','',9);
                $this->SetFillColor(220,220,220);
                $this->Cell($lebar1/100*$width,$height,$_SESSION['lang']['nomor'],1,0,'C',1);
                $this->Cell($lebar2/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
                $this->Cell($lebar3/100*$width,$height,$_SESSION['lang']['kodetraksi'],1,0,'C',1);
                $this->Cell($lebar4/100*$width,$height,$_SESSION['lang']['kodevhc'],1,0,'C',1);
                $this->Cell($lebar5/100*$width,$height,$_SESSION['lang']['jmljamkerja'],1,1,'C',1);
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('L','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();
		
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',9);
$str="select tanggal, sum(jumlah) as jumlah,kodevhc from ".$dbname.".vhc_rundt a 
      left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
      where tanggal >='".$tanggal1."' and tanggal <='".$tanggal2."' and kodevhc in (select kodevhc from ".$dbname.".vhc_5master
      where kodetraksi='".$kodetraksi."')
      group by tanggal, kodevhc";
//echo $str; exit;
$resz=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$resz->setFetchMode(PDO::FETCH_OBJ);
while($bar=$resz->fetch())
{
   $no+=1;
    $pdf->Cell($lebar1/100*$width,$height,$no,1,0,'R',1);
    $pdf->Cell($lebar2/100*$width,$height,$bar->tanggal,1,0,'C',1);
    $pdf->Cell($lebar3/100*$width,$height,$kodetraksi,1,0,'C',1);
    $pdf->Cell($lebar4/100*$width,$height,$bar->kodevhc,1,0,'L',1);
    $pdf->Cell($lebar5/100*$width,$height,number_format($bar->jumlah,0),1,1,'R',1);
}
    
$pdf->Output();		
    
    }
?>
