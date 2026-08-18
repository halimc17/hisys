<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdeOrg=checkPostGet('kdeOrg','');
$kdOrg=checkPostGet('kdOrg','');
$tgl1=tanggalsystem(checkPostGet('tgl1',''));
$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$tgl_1=tanggalsystem(checkPostGet('tgl_1',''));
$tgl_2=tanggalsystem(checkPostGet('tgl_2',''));
$periodeGaji=checkPostGet('periode','');
$period=explode('-',checkPostGet('period',''));
$periode=explode('-',checkPostGet('periode',''));
$sistemGaji=checkPostGet('sistemGaji','');
$idKry=checkPostGet('idKry','');

$tipepot=makeOption($dbname,'sdm_ho_component','id,name');

function dates_inbetween($date1, $date2){

    $day = 60*60*24;

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();

    $dates_array[] = date('Y-m-d',$date1);

    for($x = 1; $x < $days_diff; $x++){
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }

    $dates_array[] = date('Y-m-d',$date2);

    return $dates_array;
}
switch($proses)
{
        case'preview':
        if(($tgl_1!='')&&($tgl_2!=''))
        {
                $tgl1=$tgl_1;
                $tgl2=$tgl_2;
        }

        $test = dates_inbetween($tgl1, $tgl2);
        if(($tgl2=="")&&($tgl1==""))
        {
                echo"warning: Tanggal Mulai dan Tanggal Sampai tidak boleh kosong";
                exit();
        }

        $jmlHari=count($test);
        //cek max hari inputan
        if($jmlHari>41)
        {
                echo"warning: invalid period";
                exit();
        }
        if($sistemGaji=='All')$wherez="";        
        if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
        if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";        
        if($kdOrg!='')
        {
            $kodeOrg=$kdOrg;
			if(strlen($kdOrg)>4)
			{
				$where=" and subbagian='".$kdOrg."'";
			}
			else
			{
				$where=" and lokasitugas='".$kdOrg."' and (subbagian='0' or subbagian is null)";
			}
			$where2=" and kodeorg='".substr($kodeOrg,0,4)."'";
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING')$where=" and lokasitugas='".$kdOrg."'";
        }
        elseif($kdOrg=='')
        {
			$where=" and lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
			$where2=" and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
        }

        $resData=array();
        $qwe=array();
        $sGetKary="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where left(kodegolongan,1)<=3  ".$where." ".$wherez."  order by namakaryawan asc";
		$rGetkary=fetchData($sGetKary);
        foreach($rGetkary as $row => $kar)
		{
			$qwe[]=$kar['karyawanid'];	// buat batas user saat nampilkan nanti;
			$resData[$kar['karyawanid']]['id']=$kar['karyawanid'];		
			$resData[$kar['karyawanid']]['nm']=$kar['namakaryawan'];
		}
        $sData="select jumlahpotongan,nik,periodegaji as tanggal,keterangan, tipepotongan from ".$dbname.".sdm_potongandt where periodegaji='".$periodeGaji."' ".$where2." ";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while($rData=$qData->fetch())
        {
			setIt($resData[$rData['nik']]['jm'],0);
			$resData[$rData['nik']]['flag']=1; 
			$resData[$rData['nik']][$rData['tipepotongan']]=$rData['jumlahpotongan'];  
			$resData[$rData['nik']]['jm']+=$rData['jumlahpotongan'];  
        }
        $aPotongan=array();
        $sPotongan="select tipepotongan, keterangan from ".$dbname.".sdm_potongandt where jumlahpotongan>0 and periodegaji='".$periodeGaji."' ".$where2." group by tipepotongan";
		$rPotongan=fetchData($sPotongan);
        foreach($rPotongan as $row => $pot)
		{
			$aPotongan[$pot['tipepotongan']]=$pot['tipepotongan'];		
        }

// PREVIEW HEADER
        $tab.="<table cellspacing='1' border='0' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td align=center>No</td>
        <td align=center>".$_SESSION['lang']['nama']."</td>";
        foreach($aPotongan as $ar => $isi)
        {
			$tab.="<td align=center width=100px>";
			$tab.=$tipepot[$isi]; 
			$tab.="</td>";
        }

        $tab.="<td align=center width=100px>".$_SESSION['lang']['jumlah']."</td></tr><tbody>";
// PREVIEW CONTENT	
        $i=$tot=0;	
        foreach($qwe as $idid)
        {
			if(isset($resData[$idid]['flag']) and $resData[$idid]['flag']==1){
				$no+=1;
				$tab.="<tr class='rowcontent'>";
				$tab .= "<td align=center>".$no."</td>";
				$tab .= "<td>".$resData[$idid]['nm']."</td>";
				foreach($aPotongan as $ar => $isi)
				{
					$tab.="<td align='right'>";
					$tab.=number_format($resData[$idid][$isi]); 
					$tab.="</td>";
				
					$totpot[$ar]+=$resData[$idid][$isi];
				}
								
				$tab .= "<td align='right'>".number_format($resData[$idid]['jm'])."</td>";
				$tab.="</tr>";
				$tot+=$resData[$idid]['jm'];
			}
        }
        $tab.="<tr class='rowcontent'>
        <td align=center></td>
        <td colspan=".(count($aPotongan))." align=center>Total</td>";
		
		foreach($aPotongan as $pott)
        {
			$tab.="<td align=right width=100px>";
			$tab.=number_format($totpot[$pott]); 
			$tab.="</td>";
        }
		
		$tab.="<td align=right>".number_format($tot)."</td></tr>";
        $tab.="</tbody></table>";
        echo $tab;
        break;
	
    case'pdf':
        if(($tgl_1!='')&&($tgl_2!=''))
        {
			$tgl1=$tgl_1;
			$tgl2=$tgl_2;
        }

        $test = dates_inbetween($tgl1, $tgl2);
        if(($tgl2=="")&&($tgl1==""))
        {
			echo"warning: date required";
			exit();
        }

        $jmlHari=count($test);
        //cek max hari inputan
        if($jmlHari>41)
        {
			echo"warning:invalid period";
			exit();
        }
        //ambil query untuk tanggal kehadiran
		
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++
		//create Header
		$subtot=array();
		if($kdOrg!='')
        {
            $kodeOrg=$kdOrg;
			if(strlen($kdOrg)>4)
			{
					$where=" and subbagian='".$kdOrg."'";
			}
			else
			{
					$where=" and lokasitugas='".$kdOrg."' and (subbagian='0' or subbagian is null)";
			}
			$where2=" and kodeorg='".substr($kodeOrg,0,4)."'";
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING')$where=" and lokasitugas='".$kdOrg."'";
        }
        elseif($kdOrg=='')
        {
			$where=" and lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
			$where2=" and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
        }
        if($sistemGaji=='All')$wherez="";        
        if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
        if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";        
        $resData=array();
        $qwe=array();
        $sGetKary="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in (1,2,3,4)  ".$where." ".$wherez."  order by namakaryawan asc";
        $rGetkary=fetchData($sGetKary);
        foreach($rGetkary as $row => $kar)
		{
			$qwe[]=$kar['karyawanid'];	// buat batas user saat nampilkan nanti;
			$resData[$kar['karyawanid']]['id']=$kar['karyawanid'];		
			$resData[$kar['karyawanid']]['nm']=$kar['namakaryawan'];
		}
        $sData="select jumlahpotongan,nik,periodegaji as tanggal,keterangan, tipepotongan from ".$dbname.".sdm_potongandt where periodegaji='".$periodeGaji."' ".$where2." ";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
        while($rData=$qData->fetch())
        {
			setIt($resData[$rData['nik']]['jm'],0);
			$resData[$rData['nik']]['flag']=1;  
			$resData[$rData['nik']][$rData['tipepotongan']]=$rData['jumlahpotongan'];  
			$resData[$rData['nik']]['jm']+=$rData['jumlahpotongan'];
        }
        $aPotongan=array();
        $sPotongan="select tipepotongan, keterangan from ".$dbname.".sdm_potongandt where periodegaji='".$periodeGaji."' ".$where2." group by tipepotongan";	
        $rPotongan=fetchData($sPotongan);
        $kolomPotongan=0;
        foreach($rPotongan as $row => $pot)
		{
			$aPotongan[$pot['tipepotongan']]=$pot['tipepotongan'];
			$kolomPotongan+=1;		
        }
		
        if($kolomPotongan>10)
        {
			echo"warning: Data too large, please use preview button.";
			exit();
        }

		class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				global $period;
				global $periode;
				global $kdOrg;
				global $kdeOrg;
				global $tgl1;
				global $tgl2;
				global $where;
				global $jmlHari;
				global $test;
				global $klmpkAbsn;
				global $baris;
				global $i;
				global $row;
				global $nomor;
				global $sistemGaji;
				global $periodeGaji;
				global $aPotongan;
				global $kolomPotongan;
				
				$jmlHari=$jmlHari*1.5;
				$cols=247.5;
				
                # Alamat & No Telp
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
				$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['lapPotongan']." ".$sistemGaji,'',0,'L');
				$this->Ln();
				
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['lapPotongan']),'',0,'C');
				$this->Ln();
				$this->Cell($width,$height,strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2),'',0,'C');
				$this->Ln();
                $this->SetFont('Arial','B',7);
                $this->SetFillColor(220,220,220);
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(10/100*$width,$height,$_SESSION['lang']['nama'],1,0,'C',1);
				@$lebarPotongan=82/$kolomPotongan;
				$legend=0;		
				foreach($aPotongan as $ar => $isi)
				{
					$legend+=1;
					if(strlen($isi)>15)
					{
						$isinya=substr($isi,0,10);
						$isinya.=substr($isi,-5,5);
					} else $isinya=$isi;
					
					if($lebarPotongan>10)
						$this->Cell($lebarPotongan/100*$width,$height,$isinya,1,0,'C',1);
					else
						$this->Cell($lebarPotongan/100*$width,$height,$legend,1,0,'C',1);
				}
				$this->Cell(6/100*$width,$height,$_SESSION['lang']['jumlah'],1,1,'C',1);
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
        $height = 12;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',7);

// PDF CONTENT	

        $i=$lebarPotongan=0;	
		$pdf->SetFont('Arial','',6);
        foreach($qwe as $idid)
        {
			if(isset($resData[$idid]['flag']) and $resData[$idid]['flag']==1){
				$no+=1;
				$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
				$pdf->Cell(10/100*$width,$height,$resData[$idid]['nm'],1,0,'L',1);
				$lebarPotongan=82/$kolomPotongan;		
				foreach($aPotongan as $ar => $isi)
				{
				$pdf->Cell($lebarPotongan/100*$width,$height,number_format($resData[$idid][$isi]),1,0,'R',1);	
				}
				$pdf->Cell(6/100*$width,$height,number_format($resData[$idid]['jm']),1,1,'R',1);
			}
        }
        if($lebarPotongan<=10){
                $legend=0;		
                foreach($aPotongan as $ar => $isi)
                {
                        $legend+=1;
                $pdf->Cell($width,$height,$legend.". ".$isi,'',1,'L');
                }
        }
        $pdf->Output();
        break;
	
    case'excel':
		if(($tgl_1!='')&&($tgl_2!=''))
        {
                $tgl1=$tgl_1;
                $tgl2=$tgl_2;
        }

        $test = dates_inbetween($tgl1, $tgl2);
        if(($tgl2=="")&&($tgl1==""))
        {
                echo"warning: date required";
                exit();
        }

        $jmlHari=count($test);
        //cek max hari inputan
        if($jmlHari>41)
        {
                echo"warning: invalid period";
                exit();
        }
        if($kdOrg!='')
        {
                        $kodeOrg=$kdOrg;
                        if(strlen($kdOrg)>4)
                        {
                                $where=" and subbagian='".$kdOrg."'";
                        }
                        else
                        {
                                $where=" and lokasitugas='".$kdOrg."' and (subbagian='0' or subbagian is null)";
                        }
                        $where2=" and kodeorg='".substr($kodeOrg,0,4)."'";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')$where=" and lokasitugas='".$kdOrg."'";

        }
        elseif($kdOrg=='')
        {
//		echo"warning:Pilih Unit yang di Inginkan";
//                exit();
$where=" and lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
$where2=" and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
        }
        if($sistemGaji=='All')$wherez="";        
        if($sistemGaji=='Bulanan')$wherez=" and sistemgaji = 'Bulanan'";        
        if($sistemGaji=='Harian')$wherez=" and sistemgaji = 'Harian'";   
        $resData=array();
        $qwe=array();
        $sGetKary="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in (1,2,3,4)  ".$where." ".$wherez."  order by namakaryawan asc";
        $rGetkary=fetchData($sGetKary);
        foreach($rGetkary as $row => $kar)
    {
		$qwe[]=$kar['karyawanid'];	// buat batas user saat nampilkan nanti;
        $resData[$kar['karyawanid']]['id']=$kar['karyawanid'];		
		$resData[$kar['karyawanid']]['nm']=$kar['namakaryawan'];
    }
        $sData="select jumlahpotongan,nik,periodegaji as tanggal,keterangan, tipepotongan from ".$dbname.".sdm_potongandt where periodegaji='".$periodeGaji."' ".$where2." ";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
        while($rData=$qData->fetch())
        {
			setIt($resData[$rData['nik']]['jm'],0);
			$resData[$rData['nik']]['flag']=1;
			$resData[$rData['nik']][$rData['tipepotongan']]=$rData['jumlahpotongan'];
			$resData[$rData['nik']]['jm']+=$rData['jumlahpotongan'];
        }
	$aPotongan=array();
        $sPotongan="select tipepotongan, keterangan from ".$dbname.".sdm_potongandt where periodegaji='".$periodeGaji."' ".$where2." group by tipepotongan";	
        $rPotongan=fetchData($sPotongan);
        $kolomPotongan=0;
        foreach($rPotongan as $row => $pot)
		{
          $aPotongan[$pot['tipepotongan']]=$pot['tipepotongan'];		
          $kolomPotongan+=1;
        }

// EXCEL HEADER
        $jumlahkolom=3+$kolomPotongan;
        $tab.="<table border='0'><tr><td colspan='".$jumlahkolom."' align=center>".strtoupper($_SESSION['lang']['lapPotongan'])." ".$sistemGaji."</td></tr>
        <tr><td colspan='".$jumlahkolom."' align=center>".strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d. ". tanggalnormal($tgl2)."</td></tr><tr><td colspan='".$kolomPotongan."'>&nbsp;</td></tr></table>";
        $tab.="<table cellspacing='1' border='1' class='sortable'>
        <thead class=rowheader>
        <tr>
        <td bgcolor=#DEDEDE align=center>No</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nama']."</td>";
        foreach($aPotongan as $ar => $isi)
        {
                $tab.="<td bgcolor=#DEDEDE align=center>";
                $tab.=$tipepot[$isi]; 
                $tab.="</td>";
        }

        $tab.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']."</td></tr><tbody>";
// EXCEL CONTENT	
        $i=0;	
        foreach($qwe as $idid)
        {
                if(isset($resData[$idid]['flag']) and $resData[$idid]['flag']==1){
                        $no+=1;
                        $tab.="<tr class='rowcontent'>";
                                $tab .= "<td>".$no."</td>";
                                $tab .= "<td>".$resData[$idid]['nm']."</td>";
                                foreach($aPotongan as $ar => $isi)
                                {
                                        $tab.="<td align='right'>";
                                        $tab.=number_format($resData[$idid][$isi]); 
                                        $tab.="</td>";
										$totpot[$ar]+=$resData[$idid][$isi];
                                }
                                $tab .= "<td align='right'>".number_format($resData[$idid]['jm'])."</td>";
								$tot+=$resData[$idid]['jm'];
                        $tab.="</tr>";
                }

        }
			$tab.="<tr class='rowcontent'><td colspan=".(count($aPotongan))." align=center>Total</td>";
		
			foreach($aPotongan as $pott)
			{
				$tab.="<td align=right width=100px>";
				$tab.=number_format($totpot[$pott]); 
				$tab.="</td>";
			}
			
			$tab.="<td align=right>".number_format($tot)."</td></tr>";
		
        $tab.="</tbody></table>";
                        //echo "warning:".$strx;
                        //=================================================


                        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
                        if($period!='')
                        {
							$art=$period;
							$art=count($art)>1? $art[1].$art[0]: '';
                        }
                        if($periode!='')
                        {
							$art=$periode;
							$art=$art[1].$art[0];
                        }
                        if($kdeOrg!='')
                        {
                            $kodeOrg=$kdeOrg;
                        }
                        if($kdOrg!='')
                        {
                            $kodeOrg=$kdOrg;
                        }
                        $nop_="RekapPotongan".$art."__".$kodeOrg;
if($kodeOrg=='') $nop_="RekapPotongan".$art."__".$_SESSION['empl']['lokasitugas'];
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
        case'getTgl':
        if($periode!='')
        {
                $tgl=$periode;
                $tanggal=$tgl[0]."-".$tgl[1];
        }
        elseif($period!='')
        {
                $tgl=$period;
                $tanggal=$tgl[0]."-".$tgl[1];
        }
$kdUnit=$_GET['kdUnit'];
$periode=$_GET['periode'];
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')	$sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg = '".$kdUnit."' and  periode='".$periode."' ";
else
        $sTgl="select distinct tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".$tanggal."' ";
        $qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
		$qTgl->setFetchMode(PDO::FETCH_ASSOC);
        $rTgl=$qTgl->fetch();
        echo tanggalnormal($rTgl['tanggalmulai'])."###".tanggalnormal($rTgl['tanggalsampai']);
        break;
        case'getKry':
        if(strlen($kdeOrg)>4)
        {
                $where=" subbagian='".$kdeOrg."'";
        }
        else
        {
                $where=" lokasitugas='".$kdeOrg."'";
        }
        $sKry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where ".$where." order by namakaryawan asc";
        $qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
		$qKry->setFetchMode(PDO::FETCH_ASSOC);
        while($rKry=$qKry->fetch())
        {
                $optKry.="<option value=".$rKry['karyawanid'].">".$rKry['namakaryawan']."</option>";
        }
        echo $optKry;
        break;
case'getPeriode':
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$kdeOrg."'";
        $qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
        while($rPeriode=$qPeriode->fetch())
        {
                $optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
        }
        echo $optPeriode;
        break;

        default:
        break;
}

?>