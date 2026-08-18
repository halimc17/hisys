<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$kdUnit = checkPostGet('kdUnit','');
$tpKary = checkPostGet('tpKary','');
$periode = checkPostGet('periode','');

$optJabatan=makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optInduk=makeOption($dbname, 'organisasi','kodeorganisasi,induk');


if($kdUnit!='')
{
    $unitId=$optNmOrg[$kdUnit];
    $whr=" lokasitugas='".$kdUnit."'";
}
else
{
    exit("Error:".$_SESSION['lang']['unit']." Tidak boleh kosong");
}
if($tpKary!='')
{
    $whr.=" and tipekaryawan='".$tpKary."'";
}
if($periode!='')
{
    $whr.=" and (substr(tanggalkeluar,1,7)>='".$periode."' or substr(tanggalkeluar,1,7)='0000-00')";
}
else
{
    exit("Error:".$_SESSION['lang']['periode']." Tidak boleh kosong");
}
$thn=explode("-",$periode);
$sData="select distinct karyawanid,namakaryawan,npwp,tanggalmasuk,tanggalkeluar,jeniskelamin,statuspajak,warganegara,
        kodejabatan,alamataktif from ".$dbname.".datakaryawan where ".$whr."";
// echo $sData;
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);

$brdr=0;
$bgcoloraja='';

if($proses=='excel')
{
    $bgcoloraja="bgcolor=#DEDEDE ";
    $brdr=1;
    $tab.="
    <table>
    <tr><td colspan=5 align=left><b>".$_SESSION['org']['namaorganisasi']."</b></td></tr>
    <tr><td colspan=5 align=left>".$_SESSION['lang']['unit']." : ".$optNmOrg[$kdUnit]." </td></tr>
    <tr><td colspan=5 align=left>".$_SESSION['lang']['bulan']." : ".$thn[1]." ".$_SESSION['lang']['tahun']." ".$thn[0]." </td></tr>
    </table>";
}


	$tab.="<table cellspacing=1 cellpadding=7 border=".$brdr." class=sortable width=100%>
	<thead >";
        $tab.="<tr class=rowheader>";
            $tab.="<th align=center>No.</th>";
            $tab.="<th align=center>".$_SESSION['lang']['namakaryawan']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['npwp']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['tanggalmulai']." ".$_SESSION['lang']['npwp']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['tanggalmasuk']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['tanggalkeluar']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['keterangan']." Kerja</th>";
            $tab.="<th align=center>L/P</th>";
            $tab.="<th align=center>".$_SESSION['lang']['statuspajak']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['warganegara']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['jabatan']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['alamat']."</th>";
        $tab.="</tr></thead><tbody>";
		$brsdt=owlBaris($qData);
        if($brsdt!=0){
            while($rData=$qData->fetch()){
                $no+=1;
                if(substr($rData['tanggalkeluar'],0,7)>$periode or $rData['tanggalkeluar']=='0000-00-00'){
                    $ket="Aktif Bekerja";
                }
                $rData['warganegara']!='ID'?$wn="WNA":$wn="WNI";

                $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td>".$rData['namakaryawan']."</td>";
                    $tab.="<td>".$rData['npwp']."</td>";
                    $tab.="<td>&nbsp;</td>";
                    $tab.="<td align='center'>".tanggalnormal($rData['tanggalmasuk'])."</td>";
                    $tab.="<td align='center'>".tanggalnormal($rData['tanggalkeluar'])."</td>";
                    $tab.="<td>".$ket."</td>";
                    $tab.="<td align='center'>".$rData['jeniskelamin']."</td>";
                    $tab.="<td>".$rData['statuspajak']."</td>";
                    $tab.="<td align=center>".$wn."</td>";
                    $tab.="<td>".$optJabatan[$rData['kodejabatan']]."</td>";
                    $tab.="<td>".$rData['alamataktif']."</td>";
                $tab.="</tr>";
            }
        }else{
            $tab.="<tr class=rowcontent>";
                $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
            
        }
        $tab.="</tbody></table>";
       
switch($proses)
{
	case'preview':
	echo $tab;
	break;
        case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("Hms");
        $nop_="daftarKary_".$dte;
        if(strlen($tab)>0){
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
              echo "<script language=javascript>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
               exit;
             }
             else
             {
              echo "<script language=javascript>
                    window.location='tempExcel/".$nop_.".xls';
                    </script>";
             }
            fclose($handle);
        }
	break;
        case'pdf':
      
           class PDF extends FPDF {
           function Header() {
            global $periode;
            global $kdUnit;
            global $optNmOrg;  
            global $dbname;
            global $thn;
            global $tot;

   
                $this->SetFont('Arial','B',8);
                $this->Cell($width,$height,strtoupper("25.3 TRANSIT KENDARAAN"),0,1,'L');
                $this->Cell($width,$height,$_SESSION['lang']['bulan'].' : '.substr(tanggalnormal($periode),1,7),0,1,'R');
                $tinggiAkr=$this->GetY();
                $ksamping=$this->GetX();
                $this->SetY($tinggiAkr+20);
                $this->SetX($ksamping);
                $this->Cell($width,$height,$_SESSION['lang']['unit'].' : '.$optNmOrg[$kdUnit],0,1,'L');
                $this->Cell(790,$height,' ',0,1,'R');
                
                $height = 10;
                $this->SetFillColor(220,220,220);
                $this->SetFont('Arial','B',5);
                
                $tinggiAkr=$this->GetY();
                $ksamping=$this->GetX();
                $this->SetY($tinggiAkr+20);
                $this->SetX($ksamping);
               
                $this->Cell(15,$height,"No.",TLR,0,'C',1);
                $this->Cell(35,$height,"Jenis",TLR,0,'C',1);
                $this->Cell(80,$height,"Nama KEND",TLR,0,'C',1);
                $this->Cell(50,$height,"KODE MESIN",TLR,0,'C',1);
                $this->Cell(25,$height,"THN ",TLR,0,'C',1);
                $this->Cell(45,$height,"ANGGARAN ",TLR,0,'C',1);
                $this->Cell(80,$height,"REALISASI  PEMAKAIAN  FISIK",TLR,0,'C',1);
                $this->Cell(50,$height,"RASIO ",TLR,0,'C',1);
                $this->Cell(50,$height,"ANGGARAN ",TLR,0,'C',1);
                $this->Cell(280,$height,"Realisasi Biaya Operasi (000) ",TLR,0,'C',1);
                $this->Cell(80,$height,"COST / UNIT",TLR,1,'C',1);
                
                $this->Cell(15,$height," ",LR,0,'C',1);
                $this->Cell(35,$height," ",LR,0,'C',1);
                $this->Cell(80,$height," ",LR,0,'C',1);
                $this->Cell(50,$height,"MESIN/KEND ",LR,0,'C',1);
                $this->Cell(25,$height,"PER",LR,0,'C',1);
                $this->Cell(45,$height,"SETAHUN ",LR,0,'C',1);
                $this->Cell(40,$height,"KM",TLR,0,'C',1);
                $this->Cell(40,$height,"LTR",TLR,0,'C',1);
                $this->Cell(50,$height,"S/D BLN INI",LR,0,'C',1);
                $this->Cell(50,$height,"SETAHUN",LR,0,'C',1);
                $this->Cell(140,$height,"BI",TLR,0,'C',1);
                $this->Cell(140,$height,"SBI",TLR,0,'C',1);
                $this->Cell(40,$height,"ANGGARAN",TLR,0,'C',1);
                $this->Cell(40,$height,"REALISASI",TLR,1,'C',1);
                
                $this->Cell(15,$height," ",BLR,0,'C',1);
                $this->Cell(35,$height," ",BLR,0,'C',1);
                $this->Cell(80,$height," ",BLR,0,'C',1);
                $this->Cell(50,$height,"",BLR,0,'C',1);
                $this->Cell(25,$height,"OLEHAN",BLR,0,'C',1);
                $this->Cell(45,$height,"(KM)",BLR,0,'C',1);
                $this->Cell(20,$height,"BI",TBLR,0,'C',1);
                $this->Cell(20,$height,"SBI",TBLR,0,'C',1);
                $this->Cell(20,$height,"BI",TBLR,0,'C',1);
                $this->Cell(20,$height,"SBI",TBLR,0,'C',1);
                $this->Cell(50,$height,"(LTR/KM)",BLR,0,'C',1);
                $this->Cell(50,$height," (Rp.000,-) ",BLR,0,'C',1);
                $this->SetFont('Arial','B',4);
                $this->Cell(20,$height,"Gaji",TBLR,0,'C',1);
                $this->SetFont('Arial','B',3.5);
                $this->Cell(20,$height,"Pre/Lembur",TBLR,0,'L',1);
                $this->Cell(20,$height,"BBM/Plumas",TBLR,0,'L',1);
                $this->Cell(20,$height,"S.Cadang",TBLR,0,'L',1);
                $this->SetFont('Arial','B',4);
                $this->Cell(20,$height,"Reprasi",TBLR,0,'C',1);
                $this->Cell(20,$height,"Asuransi",TBLR,0,'C',1);
                $this->Cell(20,$height,"Total",TBLR,0,'C',1);
                $this->SetFont('Arial','B',4);
                $this->Cell(20,$height,"Gaji",TBLR,0,'C',1);
                $this->SetFont('Arial','B',3.5);
                $this->Cell(20,$height,"Pre/Lembur",TBLR,0,'L',1);
                $this->Cell(20,$height,"BBM/Plumas",TBLR,0,'L',1);
                $this->Cell(20,$height,"S.Cadang",TBLR,0,'L',1);
                $this->SetFont('Arial','B',4);
                $this->Cell(20,$height,"Reprasi",TBLR,0,'C',1);
                $this->Cell(20,$height,"Asuransi",TBLR,0,'C',1);
                $this->Cell(20,$height,"Total",TBLR,0,'C',1);
                $this->SetFont('Arial','B',5);
                $this->Cell(40,$height,"SETAHUN ",BLR,0,'C',1);
                $this->Cell(20,$height,"BI",TBLR,0,'C',1);
                $this->Cell(20,$height,"SBI",TBLR,1,'C',1);         
                
          }
              function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
            }
            //================================

            $pdf=new PDF('L','pt','A4');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 10;
            $tnggi=$jmlHari*$height;
            $pdf->AddPage();
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','B',5);
            $i=0;
 foreach($dtKend as $lstKend)
        {
            $i+=1;
            
                $pdf->Cell(15,$height,$i,TBLR,0,'C',1);
                $pdf->Cell(35,$height,$lsJenis[$lstKend],TBLR,0,'C',1);
                $pdf->Cell(80,$height,$lsNama[$lstKend],TBLR,0,'L',1);
                $pdf->Cell(50,$height,$lstKend,TBLR,0,'L',1);
                $pdf->Cell(25,$height,$lsThnPerolehan[$lstKend],TBLR,0,'C',1);
                $pdf->Cell(45,$height,number_format($lsKm[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($dtBi[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($dtSbi[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($jmlhBbm[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($jmlhBbmSbi[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(50,$height,number_format($rasioDt[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(50,$height,number_format($dtAnggrn[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($biGaji[$lstKend],0),TBLR,0,'R',1);
               
                $pdf->Cell(20,$height,number_format($biLembur[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($biBbm[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($biSukuCdng[$lstKend],0),TBLR,0,'R',1);
               
                $pdf->Cell(20,$height,number_format($biReparasi[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($biAsuransi[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($rTotal[$lstKend],0),TBLR,0,'R',1);
               
                $pdf->Cell(20,$height,number_format($sBiGaji[$lstKend],0),TBLR,0,'R',1);
               
                $pdf->Cell(20,$height,number_format($sBiLembur[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($sBiBbm[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($sBiSukuCdng[$lstKend],0),TBLR,0,'R',1);
               
                $pdf->Cell(20,$height,number_format($sBiReparasi[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($sBiAsuransi[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($rTotalSbi[$lstKend],0),TBLR,0,'R',1);
               
                $pdf->Cell(40,$height,number_format($zData[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($aaDta[$lstKend],0),TBLR,0,'R',1);
                $pdf->Cell(20,$height,number_format($abDta[$lstKend],0),TBLR,1,'R',1);  
        }
            $pdf->Output();
            break;
	
            
	
	default:
	break;
}
      
?>