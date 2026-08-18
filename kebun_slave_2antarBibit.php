<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$kdUnit = checkPostGet('kdUnit','');
$divisi = checkPostGet('divisi','');
$periodeData = checkPostGet('periodeData','');
$periodeId="--";
$tmpPeriode = explode('-',$periodeData);
$thnBudget = $tmpPeriode[0];
$per=explode("-",$periodeId);
$perod=$per[2]."-".$per[1]."-".$per[0];
$dtPeriod=$per[2]."-".$per[1];
$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optSup=makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optBibit=makeOption($dbname, 'bibitan_batch', 'batch,jenisbibit');
$optNmKary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', '(tanggalkeluar = 0000-00-00 or tanggalkeluar > date("Y-m-d")) and tipekaryawan in (0,7,8)');
$where=" kodeunit='".$kdUnit."' and tahunbudget='".$thnBudget."'";
$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sep","10"=>"Okt","11"=>"Nov","12"=>"Des");
$optNmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$optSatkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');

$where = "";
if($periodeData==''||$kdUnit=='')
{
    exit("Error:Unit Dan Periode Tidak Boleh Kosong!!");
}
if ($divisi != '') {
    $where.=" and afdeling like '".$divisi."%'";
}
$sData="select distinct * from ".$dbname.".bibitan_mutasi where kodetransaksi='PNB'
       and kodeorg like '%MN%' and kodeorg like '".$kdUnit."%' and substr(tanggal,1,7)='".$periodeData."' ".$where."";
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
$row=owlBaris($qData);
if($row==0)
{
    exit("Error:Data Kosong");
}
$brd=0;
$bgcolordt="";
if($proses=='excel')
{
 $bgcolordt="bgcolor=#DEDEDE";
 $brd=1;
}
        $tab="<table cellpadding=1 cellspacing=1 border=".$brd." class=sortable>";
        $tab.="<thead><tr>";
        $tab.="<th rowspan=2 align=center ".$bgcolordt.">".$_SESSION['lang']['batch']."</th>";
        $tab.="<th rowspan=2 align=center ".$bgcolordt.">".$_SESSION['lang']['tanggal']."</th>";
        $tab.="<th rowspan=2 align=center ".$bgcolordt.">".$_SESSION['lang']['blok']."</th>";
        $tab.="<th rowspan=2 align=center ".$bgcolordt.">".$_SESSION['lang']['nopol']."</th>";
        $tab.="<th rowspan=2 ".$bgcolordt.">RIT Ke</th>";
        $tab.="<th rowspan=2 align=center  ".$bgcolordt.">".$_SESSION['lang']['supir']."</th>";
        $tab.="<th rowspan=2 align=center  ".$bgcolordt.">".$_SESSION['lang']['kodeorg']."</th>";
        $tab.="<th rowspan=2 align=center ".$bgcolordt.">".$_SESSION['lang']['kegiatan']."</th>";
        $tab.="<th rowspan=2 align=center ".$bgcolordt.">".$_SESSION['lang']['satuan']."</th>";
        $tab.="<th rowspan=2 align=center ".$bgcolordt.">".$_SESSION['lang']['nosj']."</th>";
        $tab.="<th align=center colspan=2 ".$bgcolordt.">".$_SESSION['lang']['lokasi']."</th>";
        $tab.="<th align=center colspan=2 ".$bgcolordt.">".$_SESSION['lang']['pengiriman']."</th>";
        $tab.="<th rowspan=2 align=center ".$bgcolordt.">".$_SESSION['lang']['detail']." ".$_SESSION['lang']['lokasi']."</th>";
        $tab.="<th align=center rowspan=2 ".$bgcolordt.">".$_SESSION['lang']['asisten']."</th>";
        $tab.="</tr>";
        $tab.="<tr><th ".$bgcolordt.">".$_SESSION['lang']['afdeling']."</th>";
        $tab.="<th ".$bgcolordt.">".$_SESSION['lang']['blok']."</th>";
        $tab.="<th ".$bgcolordt.">".$_SESSION['lang']['jenisbibit']."</th>";
        $tab.="<th ".$bgcolordt.">".$_SESSION['lang']['jumlah']."</th></tr>";
        $tab.="</thead><tbody>";
		$totalan=0;
        while($rData=$qData->fetch())
        {
            if($rData['intex']!=0)
            {
                $nmPelanggan=$optNm[$rData['pelanggan']];
            }
            else
            {
                $nmPelanggan=$optSup[$rData['pelanggan']];
            }
            $afd=substr($rData['afdeling'],0,6);
            $blk=substr($rData['afdeling'],6,4);
            $tab.="<tr class=rowcontent>";
            $tab.="<td>".$rData['batch']."</td>";
            $tab.="<td>".$rData['tanggal']."</td>";
            $tab.="<td>".$rData['kodeorg']."</td>";
            $tab.="<td>".$rData['kodevhc']."</td>";
            $tab.="<td align=right>".$rData['rit']."</td>";
            $tab.="<td>".$rData['sopir']."</td>";
            $tab.="<td>".$nmPelanggan."</td>";
            $tab.="<td>".$rData['jenistanam']."</td>";
            $tab.="<td>Pkk</td>";
            $tab.="<td>".$rData['keterangan']."</td>";
            $tab.="<td>".$afd."</td>";
            $tab.="<td>".$blk."</td>";
            $tab.="<td>".$optBibit[$rData['batch']]."</td>";
            $tab.="<td align=right>".number_format(abs($rData['jumlah']))."</td>";
            $tab.="<td>".$rData['lokasipengiriman']."</td>";
            $tab.="<td>".$optNmKary[$rData['penanggungjawab']]."</td>";
            $tab.="</tr>";
            $totalan+=$rData['jumlah'];
        }
        $tab.="<tr class=rowcontent><td colspan=13>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format(abs($totalan))."</td><td colspan=2></td></tr>";
        $tab.="</tbody></table>";
	switch($proses)
        {
        case'preview':
        echo $tab;
        break;
          case'excel':
          
            $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("Hms");
            $nop_="lapAntarBibit_".$dte;
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
         case'pdf':
        
           class PDF extends FPDF {
           function Header() {
            global $periodeId;
            global $dataAfd;
            global $kdUnit;
            global $optNm;  
            global $dbname;
            global $where;
          
            global $optSatuan;
            global $optNmBrg;
            $width = $this->w - $this->lMargin - $this->rMargin;
            $height = 20;
            
                $this->SetFont('Arial','B',8);
                $this->Cell(250,$height,strtoupper("LAPORAN RESTAN"),0,0,'L');
                $this->Cell(270,$height,$_SESSION['lang']['bulan'].' : '.substr(tanggalnormal($periodeId),1,7),0,1,'R');
                $tinggiAkr=$this->GetY();
                $ksamping=$this->GetX();
                $this->SetY($tinggiAkr);
                $this->SetX($ksamping);
                $this->Cell($width,$height,$_SESSION['lang']['unit'].' : '.$optNm[$kdUnit],0,1,'L');
                
                $this->SetFillColor(220,220,220);
                $this->Cell(55,$height,"Tanggal",TLR,0,'C',1);
                $this->Cell(55,$height,$_SESSION['lang']['blok'],TLR,0,'C',1);
                $this->Cell(90,$height,"Panen",TLR,0,'C',1);
                $this->Cell(90,$height,"Kirim",TLR,0,'C',1);
                $this->Cell(90,$height,"Restan",TLR,1,'C',1);
                
                $this->Cell(55,$height," ",BLR,0,'C',1);
                $this->Cell(55,$height," ",BLR,0,'C',1);
                $this->Cell(45,$height,"Janjang",TBLR,0,'C',1);
                $this->Cell(45,$height,"Kg",TBLR,0,'C',1);
                $this->Cell(45,$height,"Janjang",TBLR,0,'C',1);
                $this->Cell(45,$height,"Kg",TBLR,0,'C',1);
                $this->Cell(45,$height,"Janjang",TBLR,0,'C',1);
                $this->Cell(45,$height,"Kg",TBLR,1,'C',1);

               
          }
              function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
            }
            //================================

            $pdf=new PDF('P','pt','A4');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 15;
           
            $pdf->AddPage();
            
            $pdf->SetFillColor(255,255,255);
             $pdf->SetFont('Arial','',7);
            
             $sData="select distinct * from ".$dbname.".kebun_restan where ".$where." order by tanggal asc";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($qData);
		if($row!=0)
        {
        while($rdata=$qData->fetch())
        {
             $sKg="select distinct bjr from ".$dbname.".kebun_5bjr where kodeorg='".$rdata['kodeorg']."'";
			 $qKg=$owlPDO->query($sKg) or die(print " Gagal: ".PDOException::getMessage());
		     $qKg->setFetchMode(PDO::FETCH_ASSOC);
                    $rKg=$qKg->fetch();
                    $panenKg=$rdata['jjgpanen']*$rKg['bjr'];
                    $KirimKg=$rdata['jjgkirim']*$rKg['bjr'];
                    $resJjg=$rdata['jjgpanen']-$rdata['jjgkirim'];
                    $resKg=$resJjg*$rKg['bjr'];
                    $pdf->Cell(55,$height,tanggalnormal($rdata['tanggal']),TBLR,0,'C',1);
                    $pdf->Cell(55,$height,$rdata['kodeorg'],TBLR,0,'C',1);
                    $pdf->Cell(45,$height,$rdata['jjgpanen'],TBLR,0,'R',1);
                    $pdf->Cell(45,$height,$panenKg,TBLR,0,'R',1);
                    $pdf->Cell(45,$height,$rdata['jjgkirim'],TBLR,0,'R',1);
                    $pdf->Cell(45,$height,$KirimKg,TBLR,0,'R',1);
                    $pdf->Cell(45,$height,$resJjg,TBLR,0,'R',1);
                    $pdf->Cell(45,$height,$resKg,TBLR,1,'R',1);
           
        }
        }
        else
        {
           $pdf->Cell(380,$height,$_SESSION['lang']['dataempty'],TBLR,1,'C',1); 
        }

            $pdf->Output();
        break;
                
            default:
            break;
        }
	
?>
