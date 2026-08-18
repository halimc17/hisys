<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$kdUnit=checkPostGet('kdUnit','');
$periode=checkPostGet('periode','');

$optNmSat=makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$jnsVhc=  makeOption($dbname, 'vhc_5master', 'kodevhc,jenisvhc');

if($_SESSION['language']=='EN'){
    $dd='namaakun1';
}else{
    $dd='namaakun';
}
$optNmAkun=makeOption($dbname, 'keu_5akun', 'noakun,'.$dd);
if($kdUnit=='')
{
    exit("Error: Organizer code required");
}
if($kdUnit!='')
{
    $where="  and kodetraksi='".$kdUnit."'";
}
if($periode!='')
{
    $where.=" and periode='".$periode."'";
}
$sPeriode="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".substr($kdUnit,0,4)."'";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
$rPeriode=$qPeriode->fetch();

$sData="select distinct noakundebet,sampaidebet  from ".$dbname.".keu_5parameterjurnal where  jurnalid='LPVHC'";
//exit("error".$sData);
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
$rList=$qData->fetch();

$sData2="select sum(debet-kredit) as jumlah, kodevhc,noakun from ".$dbname.".keu_jurnaldt_vw where tanggal>='".$rPeriode['tanggalmulai']."' and tanggal<='".$rPeriode['tanggalsampai']."' and (noakun between '".$rList['noakundebet']."' and '".$rList['sampaidebet']."')
                  and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL)
                  group by kodevhc,noakun  order by kodevhc asc ";
// exit("error".$sData);
//echo $sData2;
$qData2=$owlPDO->query($sData2) or die(print " Gagal: ".PDOException::getMessage());
$qData2->setFetchMode(PDO::FETCH_ASSOC);
while($rData2=$qData2->fetch())
{
    setIt($totJumlah[$rData2['kodevhc']],0);
    $totJumlah[$rData2['kodevhc']]+=$rData2['jumlah'];
    $listNamaakun[$rData2['kodevhc']][$rData2['noakun']]=$rData2['jumlah'];
}

//list data kode vhc
$dtVhc=array();
$sData3="select distinct  kodevhc from ".$dbname.".keu_jurnaldt_vw where
          kodevhc in (select kodevhc from ".$dbname.".vhc_5master where kodetraksi like '%".substr($kdUnit,0,4)."%')
          and tanggal>='".$rPeriode['tanggalmulai']."' and tanggal<='".$rPeriode['tanggalsampai']."' and nojurnal like '%".substr($kdUnit,0,4)."%'
          and (noakun between '".$rList['noakundebet']."' and '".$rList['sampaidebet']."')
                  and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL)
                  group by kodevhc  order by kodevhc asc";
$qData3=$owlPDO->query($sData3) or die(print " Gagal: ".PDOException::getMessage());
$qData3->setFetchMode(PDO::FETCH_ASSOC);
while($rData3=$qData3->fetch()){
    $dtVhc[]=$rData3['kodevhc'];
}
//list data no akun
$sData5="select distinct  noakun from ".$dbname.".keu_jurnaldt_vw where
          kodevhc in (select kodevhc from ".$dbname.".vhc_5master where kodetraksi like '%".substr($kdUnit,0,4)."%')
          and tanggal>='".$rPeriode['tanggalmulai']."' and tanggal<='".$rPeriode['tanggalsampai']."' and nojurnal like '%".substr($kdUnit,0,4)."%'
          and (noakun between '".$rList['noakundebet']."' and '".$rList['sampaidebet']."')
                  and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL)
                  group by noakun,kodevhc  order by kodevhc asc";
$qData5=$owlPDO->query($sData5) or die(print " Gagal: ".PDOException::getMessage());
$qData5->setFetchMode(PDO::FETCH_ASSOC);
while($rData5=$qData5->fetch()){
    $listNoakun[]=$rData5['noakun'];
}
//list BBM
$str="select * from ".$dbname.".log_transaksi_vw where 
          kodemesin in (select kodevhc from ".$dbname.".vhc_5master where kodetraksi like '%".substr($kdUnit,0,4)."%')
          and tanggal>='".$rPeriode['tanggalmulai']."' and tanggal<='".$rPeriode['tanggalsampai']."' and post='1' and kodebarang like '351%' 
          order by kodemesin asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    if($bar['tipetransaksi'] == 5) {
        @$listbbm[$bar['kodemesin']]+=$bar['jumlah'];
    }
    if($bar['tipetransaksi'] == 2) {
        @$listbbm[$bar['kodemesin']]-=$bar['jumlah'];
    }
    @$listrpbbm[$bar['kodemesin']]+=$bar['hartot'];
}
//list KM/HM
$str="select * from ".$dbname.".vhc_rundt_vw where 
          kodevhc in (select kodevhc from ".$dbname.".vhc_5master where kodetraksi like '%".substr($kdUnit,0,4)."%')
          and tanggal>='".$rPeriode['tanggalmulai']."' and tanggal<='".$rPeriode['tanggalsampai']."' 
          order by kodevhc asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$listkm[$bar['kodevhc']]+=$bar['jumlah'];
}

if($rPeriode['tanggalmulai']==''){
    exit("Error: Silahkan tutup buku terlebih dahulu.");
}

$dataSemua=count($dtVhc);
if($dataSemua==0){
    exit("Error: No data found");
}
$brd=0;
$bgBelakang=$tab='';
if($proses=='excel'){
    $brd=1;
    $bgBelakang="bgcolor=#00FF40 align=center";
    $tab="<table>
            <tr><th colspan=5 align=center>".$_SESSION['lang']['laporanByRinciPerKend']."</th></tr>
            <tr><th colspan=5 align=left>".$_SESSION['lang']['kodetraksi']." : ".$kdUnit." [".$optNm[$kdUnit]."]</th></tr>
            <tr><th colspan=5 align=left>".$_SESSION['lang']['periode']." : ".$periode."</th></tr>
            <tr><th colspan=5></th><th></th></tr>
            </table>";
}
$tab.="<table cellpadding=5 cellspacing=1 border=".$brd." class=sortable>
<thead>
<tr class=rowheader>   
<th align=center style=width:50px ".$bgBelakang.">".$_SESSION['lang']['jenisvch']."</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['kodevhc']."</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['nopol']."</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['detail']."</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['bbm']."<br>(Ltr)</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['jumlahhmkm']."</th>
<th align=center ".$bgBelakang.">BBM<br>(KM/HM)/Ltr</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['kmperthn']."</th>
<th align=center ".$bgBelakang.">".$_SESSION['lang']['total']."</th>";
foreach($listNoakun as $dafNoakun){
   $tab.=" <th align=center style=width:100px ".$bgBelakang.">".$optNmAkun[$dafNoakun]."</th> ";
}
  
$tab.="</tr>
</thead><tbody id=containDataStock>";
$totalbiaya=0;
$totalperakun[]=0;
foreach($dtVhc as $listVhc){
	$optnopol = makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$listVhc."'");
	$optnmvhc = makeOption($dbname,'vhc_5master','kodevhc,detailvhc',"kodevhc='".$listVhc."'");
	
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>".$jnsVhc[$listVhc]."</td>";        
	$tab.="<td>".$listVhc."</td>";
	$tab.="<td>".$optnopol[$listVhc]."</td>";
	$tab.="<td>".getNopol($listVhc,'d')."</td>";
	$tab.="<td align=right>".@number_format($listbbm[$listVhc],2)."</td>";
	$tab.="<td align=right>".@number_format($listkm[$listVhc],2)."</td>";
	if($listbbm[$listVhc]>'0'){
		$tab.="<td align=right>".@number_format($listkm[$listVhc]/$listbbm[$listVhc],2)."</td>";
	}else{
		$tab.="<td align=right>".@number_format(0,2)."</td>";
	}
	
	if($listkm[$listVhc]>'0'){
		$tab.="<td align=right>".@number_format($totJumlah[$listVhc]/$listkm[$listVhc],2)."</td>";
	}else{
		$tab.="<td align=right>".@number_format(0,2)."</td>";
	}
	$tab.="<td align=right>".number_format($totJumlah[$listVhc],0)."</td>";
	foreach($listNoakun as $dafNoakun){
		setIt($listNamaakun[$listVhc][$dafNoakun],0);
		@$totalperakun[$dafNoakun]+=$listNamaakun[$listVhc][$dafNoakun];
		$tab.=" <td align=right>".number_format((float)$listNamaakun[$listVhc][$dafNoakun],0)."</td> ";
	}
	$tab.="</tr>";
	$totalbiaya+=$totJumlah[$listVhc];
	@$ttlbbm+=$listbbm[$listVhc];
	@$ttlkm+=$listkm[$listVhc];
}


$tab.="<tr class=rowcontent>";
$tab.="<td colspan=4 align=center><b>".$_SESSION['lang']['total']."</b></td>";
$tab.="<td align=right><b>".number_format($ttlbbm,2)."</b></td>";
$tab.="<td align=right><b>".number_format($ttlkm,2)."</b></td>";
$tab.="<td align=right><b>".number_format($ttlkm/$ttlbbm,2)."</b></td>";
$tab.="<td align=right><b>".number_format($totalbiaya/$ttlkm,2)."</b></td>";
$tab.="<td align=right><b>".number_format($totalbiaya)."</b></td>";
foreach($listNoakun as $dafNoakun){
    $tab.="<td align=right ><b>".number_format($totalperakun[$dafNoakun])."</b></td>";
}
$tab.="</tr>";
$tab.="</tbody></table>";
switch($proses){
	case'preview':
          
        echo $tab;
	break;
	case'pdf':
        class PDF extends FPDF{
           function Header() {
               global $conn;
               global $dbname;
               global $align;
               global $length;
               global $colArr;
               global $title;
               global $kdUnit;
               global $periode;
               global $dtVhc;
               global $optNm;
               global $listNoakun;
               global $jmlhCols;
               global $optNmAkun;

                           # Alamat & No Telp
               $query = selectQuery($dbname,'organisasi','alamat,telepon',
                   "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
               $orgData = fetchData($query);

               $width = $this->w - $this->lMargin - $this->rMargin;
               $height = 15;
               $path='images/logo.jpg';
               $this->Image($path,$this->lMargin,$this->tMargin,0,55);
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
               $this->Ln();

               $this->SetFont('Arial','B',12);
                       //	$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanKendAb'],'',0,'L');
                       //	$this->Ln();
                               $this->SetFont('Arial','',8);
                               $this->SetFont('Arial','U',12);
                               $this->Cell($width,$height, $_SESSION['lang']['laporanByRinciPerKend'],0,1,'C');	
                               $this->Ln();	
                                $this->SetFont('Arial','B',6);
                                       $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodetraksi'],'',0,'L');
                                       $this->Cell(5,$height,':','',0,'L');
                                       $this->Cell(25/100*$width,$height,$kdUnit." [".$optNm[$kdUnit]."]",'',0,'L');
                                       $this->Ln();

                                       $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
                                       $this->Cell(5,$height,':','',0,'L');
                                       $this->Cell(25/100*$width,$height,$periode,'',0,'L');
                                       $this->Ln();					
               $this->SetFont('Arial','B',7);	
               $this->SetFillColor(220,220,220);
               $this->Cell(8/100*$width,$height,$_SESSION['lang']['jenisvch'],1,0,'C',1);
               $this->Cell(13/100*$width,$height,$_SESSION['lang']['kodevhc'],1,0,'C',1);	
               $this->Cell(10/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);	
               $totalBaris=count($listNoakun);
               $jmlhCols=$totalBaris*10;
               $jmlhCols=18+$jmlhCols;
			   $are=0;
               foreach($listNoakun as $dafNoakun)
               {
                  $are++;
                  if($are<$totalBaris)
                  {
                   $this->Cell(10/100*$width,$height,$optNmAkun[$dafNoakun],1,0,'L',1);
                  }
                  else
                  {
                      $this->Cell(10/100*$width,$height,$optNmAkun[$dafNoakun],1,1,'L',1);
                  }
               }

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
               $pdf->SetFont('Arial','',7);
               $no=1;
               foreach($dtVhc as $listDataVhc)
               {            

                   if($no==1)
                   {
                       $pdf->Cell(8/100*$width,$height,$jnsVhc[$listDataVhc],1,0,'L',1);		
                       $pdf->Cell(13/100*$width,$height,$listDataVhc,1,0,'L',1);		
                       $pdf->Cell(10/100*$width,$height,number_format($totJumlah[$listDataVhc],2),1,0,'R',1);	
                   }
                   else
                   {
                      $akhiry=$pdf->GetY();
                      $akhirx=$pdf->GetX();
                      $pdf->SetY($akhiry+12);
                      
                      //$pdf->SetX($akhirx-($jmlhCols/100*$width));
                      
                      $pdf->Cell(8/100*$width,$height,$jnsVhc[$listDataVhc],1,0,'L',1);	
                      $pdf->Cell(13/100*$width,$height,$listDataVhc,1,0,'L',1);		
                      $pdf->Cell(10/100*$width,$height,number_format($totJumlah[$listDataVhc],2),1,0,'R',1);	
                   }
//                    $akhiry2=$pdf->GetY();
//                    $akhirx2=$pdf->GetX();
//                    $pdf->SetY($akhiry2+12);
//                    $pdf->SetX($akhirx2);   
                   foreach($listNoakun as $dafNoakun)
                   {
                        if($no==1)
                        {
                           $akhiry2=$pdf->GetY();
                           $akhirx2=$pdf->GetX();
                           $pdf->SetY($akhiry2);
                           $pdf->SetX($akhirx2); 
                            $pdf->Cell(10/100*$width,$height,number_format($listNamaakun[$listDataVhc][$dafNoakun],0),1,0,'R',1);
                        }
                        else
                        {
                           $akhiry2=$pdf->GetY();
                           $akhirx2=$pdf->GetX();
                           $pdf->SetY($akhiry2);
                           $pdf->SetX($akhirx2);
                           $pdf->Cell(10/100*$width,$height,number_format($listNamaakun[$listDataVhc][$dafNoakun],0),1,0,'R',1);
                        }
                   }
                   $no+=1;

               }

       $pdf->Output();
       break;
       case'excel':


                       //echo "warning:".$strx;
                       //=================================================
               $tab.="Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	

                       $nop_="laporan_penggunaan_bahan_".$kdUnit;
                       if(strlen($tab)>0)
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
                       if(!fwrite($handle,$tab))
                       {
                       echo "<script language=javascript1.2>
                       parent.window.alert('Can't convert to excel format');
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
       break;

       default:
       break;
}

?>