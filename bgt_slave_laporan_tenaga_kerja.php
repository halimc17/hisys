<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kodeOrg = checkPostGet('kdUnit','');
$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$where=" induk='".$kodeOrg."' or kodeorganisasi='".$kodeOrg."'";
$sKodeOrg="select kodeorganisasi from ".$dbname.".organisasi where  ".$where." order by kodeorganisasi asc";
$qKodeOrg=$owlPDO->query($sKodeOrg) or die(print " Gagal: ".PDOException::getMessage());
$qKodeOrg->setFetchMode(PDO::FETCH_ASSOC);
$a=$listBrs=$jmlh=0;
while($rKode=$qKodeOrg->fetch())
{
    $a+=1;
    $dtKode[]=$rKode['kodeorganisasi'];
    $dtKode2[$a]=$rKode['kodeorganisasi'];
    $listBrs+=1;
}
$total=count($dtKode); 
$sTipe="select distinct lokasitugas,tipe from ".$dbname.".bgt_datakaryawan_vw where lokasitugas='".$kodeOrg."' group by tipe";
$qTipe=$owlPDO->query($sTipe) or die(print " Gagal: ".PDOException::getMessage());
$qTipe->setFetchMode(PDO::FETCH_ASSOC);
while($rTipe=$qTipe->fetch())
{
   $jmlh+=1;
    $dataTipeKary[$jmlh]=$rTipe['tipe'];
    
}
    $totalTipe=count($dataTipeKary);
 
   
    
    
	switch($proses)
        {
            case'preview':
                if($kodeOrg=='')
                {
                    exit("Error:Kode Unit Tidak Boleh Kosong");
                }
                
            foreach($dtKode as $brsKd =>$lstKd)
            {
               
                if(strlen($lstKd)<6)
                {   
                    $bgtKary="select * from ".$dbname.".bgt_datakaryawan_vw where lokasitugas='".$lstKd."'";
                    $bgtKaryIstr="select * from ".$dbname.".bgt_istri_karyawan_vw where lokasitugas='".$lstKd."'";
                }
                else
                {
                     $bgtKary="select * from ".$dbname.".bgt_datakaryawan_vw where subbagian='".$lstKd."'";
                     $bgtKaryIstr="select * from ".$dbname.".bgt_istri_karyawan_vw where subbagian='".$lstKd."'";
                }
				$qBgtKary=$owlPDO->query($bgtKary) or die(print " Gagal: ".PDOException::getMessage());
				$qBgtKary->setFetchMode(PDO::FETCH_ASSOC);
                while($rBgtKary=$qBgtKary->fetch())
                {
                    if(is_null($rBgtKary['subbagian'])||$rBgtKary['subbagian']==''||$rBgtKary['subbagian']=='0')
                    {
                        $dataKary[$rBgtKary['lokasitugas']][$rBgtKary['tipe']]=$rBgtKary['karyawan'];
                        $dataTanggugan[$rBgtKary['lokasitugas']][$rBgtKary['tipe']]=$rBgtKary['tanggungan'];
                    }
                    else
                    {
                        $dataKary[$rBgtKary['subbagian']][$rBgtKary['tipe']]=$rBgtKary['karyawan'];
                        $dataTanggugan[$rBgtKary['subbagian']][$rBgtKary['tipe']]=$rBgtKary['tanggungan'];
                    }
                }
				$qBgtKaryIstri=$owlPDO->query($bgtKaryIstr) or die(print " Gagal: ".PDOException::getMessage());
				$qBgtKaryIstri->setFetchMode(PDO::FETCH_ASSOC);
                while($rBgtKaryIstri=$qBgtKaryIstri->fetch())
                {
                    if(is_null($rBgtKaryIstri['subbagian'])||$rBgtKaryIstri['subbagian']==''||$rBgtKaryIstri['subbagian']=='0')
                    {
                        $dataKaryIstri[$rBgtKaryIstri['lokasitugas']][$rBgtKaryIstri['tipe']]=$rBgtKaryIstri['jumlahistri'];
                    }
                    else
                    {
                        $dataKaryIstri[$rBgtKaryIstri['subbagian']][$rBgtKaryIstri['tipe']]=$rBgtKaryIstri['jumlahistri'];
                    }
                }
            }
            $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<th rowspan=2>".substr($_SESSION['lang']['nomor'],0,2)."</th>";
            $tab.="<th  rowspan=2>".$_SESSION['lang']['unit']."</th>";
            //foreach($dtKode as $brsKd =>$lstKd)
            for($dr=1;$dr<=$totalTipe;$dr++)
            {
     
               $tab.="<th  colspan=3 align=center>".$dataTipeKary[$dr]."</th>";
            }
            $tab.="</tr><tr>";
            for($a=1;$a<=$totalTipe;$a++)
            {
            $tab.="<th  align=center>".$_SESSION['lang']['karyawan']."</th><th>".$_SESSION['lang']['jumlahanak']."</th><th>".$_SESSION['lang']['istri']."</th>";
            }
            $tab.="</tr></thead><tbody>";
            foreach($dtKode as $brsKd =>$lstKd)//for($c=1;$c<=$totalTipe;$c++)
            {
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td>".$no."</td>";
                $tab.="<td>".$optNmOrg[$lstKd]."</td>";
                for($c=1;$c<=$totalTipe;$c++)           
                //foreach($dtKode as $brsKd =>$lstKd)
                {
                    if(@$dataKaryIstri[$lstKd][$dataTipeKary[$c]]==''||@$dataKary[$lstKd][$dataTipeKary[$c]]==''||@$dataTanggugan[$lstKd][$dataTipeKary[$c]]=='')
                    {
                        $dataKaryIstri[$lstKd][$dataTipeKary[$c]]=0;
                        $dataKary[$lstKd][$dataTipeKary[$c]]=0;
                        $dataTanggugan[$lstKd][$dataTipeKary[$c]]=0;
                    }
                   $tab.="<td  align=right>".$dataKary[$lstKd][$dataTipeKary[$c]]."</td><td align=right>".$dataTanggugan[$lstKd][$dataTipeKary[$c]]."</td><td align=right>".$dataKaryIstri[$lstKd][$dataTipeKary[$c]]."</td>";
                   @$totKary[$dataTipeKary[$c]]+=$dataKary[$lstKd][$dataTipeKary[$c]];
                   @$totAnak[$dataTipeKary[$c]]+=$dataTanggugan[$lstKd][$dataTipeKary[$c]];
                   @$totIstri[$dataTipeKary[$c]]+=$dataKaryIstri[$lstKd][$dataTipeKary[$c]];
                }
                $tab.="</tr>";
            }
            $tab.="<thead><tr class=rowcontent><td align=center colspan=2><b>".$_SESSION['lang']['total']."<b></td>";
            //foreach($dtKode as $brsKd =>$lstKd)
            for($ri=1;$ri<=$totalTipe;$ri++) 
            {
                $tab.="<td align=right>".$totKary[$dataTipeKary[$ri]]."</td>";
                $tab.="<td align=right>".$totAnak[$dataTipeKary[$ri]]."</td>";
                $tab.="<td align=right>".$totIstri[$dataTipeKary[$ri]]."</td>";
            }
                
            
            $tab.="</tbody></table>";
            echo $tab;
            break;
            case'excel':
               
                if($kodeOrg=='')
                {
                    exit("Error:Kode Unit Tidak Boleh Kosong");
                }
            $tab.="<table cellpadding=1 cellspacing=1 border=0>";
            $tab.="<tr><td colspan=3>".$_SESSION['lang']['lapPersonel']."</td></tr>";
            $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi'].":</td><td colspan=2 align=left>".$kodeOrg."</td></tr>";
            $tab.="</table>";
            foreach($dtKode as $brsKd =>$lstKd)
            {
                if(strlen($lstKd)<6)
                {   
                    $bgtKary="select * from ".$dbname.".bgt_datakaryawan_vw where lokasitugas='".$lstKd."'";
                    $bgtKaryIstr="select * from ".$dbname.".bgt_istri_karyawan_vw where lokasitugas='".$lstKd."'";
                }
                else
                {
                     $bgtKary="select * from ".$dbname.".bgt_datakaryawan_vw where subbagian='".$lstKd."'";
                     $bgtKaryIstr="select * from ".$dbname.".bgt_istri_karyawan_vw where subbagian='".$lstKd."'";
                }
                //get jumlah karyawan dan anak
				$qBgtKary=$owlPDO->query($bgtKary) or die(print " Gagal: ".PDOException::getMessage());
				$qBgtKary->setFetchMode(PDO::FETCH_ASSOC);
                while($rBgtKary=$qBgtKary->fetch())
                {
                    if(is_null($rBgtKary['subbagian'])||$rBgtKary['subbagian']==''||$rBgtKary['subbagian']=='0')
                    {
                        $dataKary[$rBgtKary['lokasitugas']][$rBgtKary['tipe']]=$rBgtKary['karyawan'];
                        $dataTanggugan[$rBgtKary['lokasitugas']][$rBgtKary['tipe']]=$rBgtKary['tanggungan'];
                    }
                    else
                    {
                        $dataKary[$rBgtKary['subbagian']][$rBgtKary['tipe']]=$rBgtKary['karyawan'];
                        $dataTanggugan[$rBgtKary['subbagian']][$rBgtKary['tipe']]=$rBgtKary['tanggungan'];
                    }
                }
                //get jumlah istri
				$qBgtKaryIstri=$owlPDO->query($bgtKaryIstr) or die(print " Gagal: ".PDOException::getMessage());
				$qBgtKaryIstri->setFetchMode(PDO::FETCH_ASSOC);
                while($rBgtKaryIstri=$qBgtKaryIstri->fetch())
                {
                    if(is_null($rBgtKaryIstri['subbagian'])||$rBgtKaryIstri['subbagian']==''||$rBgtKaryIstri['subbagian']=='0')
                    {
                        $dataKaryIstri[$rBgtKaryIstri['lokasitugas']][$rBgtKaryIstri['tipe']]=$rBgtKaryIstri['jumlahistri'];
                    }
                    else
                    {
                        $dataKaryIstri[$rBgtKaryIstri['subbagian']][$rBgtKaryIstri['tipe']]=$rBgtKaryIstri['jumlahistri'];
                    }
                }
            }
            $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<td  rowspan=2 bgcolor=#DEDEDE>".$_SESSION['lang']['unit']."</td>";
            //foreach($dtKode as $brsKd =>$lstKd)
            for($dr=1;$dr<=$totalTipe;$dr++)
            {
               $tab.="<td  colspan=3 align=center bgcolor=#DEDEDE>".$dataTipeKary[$dr]."</td>";
            }
            $tab.="</tr><tr>";
            for($a=1;$a<=$totalTipe;$a++)
            {
            $tab.="<td  align=center bgcolor=#DEDEDE>".$_SESSION['lang']['karyawan']."</td><td align=center bgcolor=#DEDEDE>".$_SESSION['lang']['jumlahanak']."</td><td align=center bgcolor=#DEDEDE>".$_SESSION['lang']['istri']."</td>";
            }
            $tab.="</tr></thead><tbody>";
            //for($c=1;$c<=$totalTipe;$c++)
            foreach($dtKode as $brsKd =>$lstKd)
            {
                $tab.="<tr class=rowcontent>";
                $tab.="<td>".$optNmOrg[$lstKd]."</td>";
                //foreach($dtKode as $brsKd =>$lstKd)
                for($c=1;$c<=$totalTipe;$c++) 
                {
                    if(@$dataKaryIstri[$lstKd][$dataTipeKary[$c]]==''||@$dataKary[$lstKd][$dataTipeKary[$c]]==''||@$dataTanggugan[$lstKd][$dataTipeKary[$c]]=='')
                    {
                        $dataKaryIstri[$lstKd][$dataTipeKary[$c]]=0;
                        $dataKary[$lstKd][$dataTipeKary[$c]]=0;
                        $dataTanggugan[$lstKd][$dataTipeKary[$c]]=0;
                    }
                   $tab.="<td  align=right>".$dataKary[$lstKd][$dataTipeKary[$c]]."</td><td align=right>".$dataTanggugan[$lstKd][$dataTipeKary[$c]]."</td><td align=right>".$dataKaryIstri[$lstKd][$dataTipeKary[$c]]."</td>";
                      @$totKary[$dataTipeKary[$c]]+=$dataKary[$lstKd][$dataTipeKary[$c]];
                @$totAnak[$dataTipeKary[$c]]+=$dataTanggugan[$lstKd][$dataTipeKary[$c]];
                @$totIstri[$dataTipeKary[$c]]+=$dataKaryIstri[$lstKd][$dataTipeKary[$c]];
                }
                $tab.="</tr>";
             
            }
            $tab.="<thead><tr class=rowcontent bgcolor=#DEDEDE><td align=center>".$_SESSION['lang']['total']."</td>";
            //foreach($dtKode as $brsKd =>$lstKd)
            for($rc=1;$rc<=$totalTipe;$rc++) 
            {
    
                $tab.="<td align=right>".$totKary[$dataTipeKary[$rc]]."</td>";
                $tab.="<td align=right>".$totAnak[$dataTipeKary[$rc]]."</td>";
                $tab.="<td align=right>".$totIstri[$dataTipeKary[$rc]]."</td>";
            }
              //  exit();
            $tab.="</tbody></table>";
            $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHis");
            $nop_="laporanPersonel_".$dte;
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
            //closedir($handle);
            }
            break;
            
            case'pdf':
                if($_GET['kdUnit']=='')
            {
                exit("Error:Kode Unit Tidak Boleh Kosong");
            }
            class PDF extends FPDF {
            function Header() {
            global $dataKary;
            global $dataKaryIstri;
            global $dataTanggugan;
            global $dtKode2;
            global $kodeOrg;
            global $total;
            global $dataTipeKary;
            global $totalTipe;
            global $dbname;
            global $optNmOrg;
            global $owlPDO;
            
        $sTipe="select lokasitugas,tipe from ".$dbname.".bgt_datakaryawan_vw where lokasitugas='".$kodeOrg."' group by tipe";
		$qTipe=$owlPDO->query($sTipe) or die(print " Gagal: ".PDOException::getMessage());
		$qTipe->setFetchMode(PDO::FETCH_ASSOC);
		$a=0;
        while($rTipe=$qTipe->fetch())
        {
            $a+=1;
            $dataTipeKary[$a]=$rTipe['tipe'];
        }
    $totalTipe=count($dataTipeKary);
         		$sAlmat="select namaorganisasi,alamat,telepon from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
				$qAlamat=$owlPDO->query($sAlmat) or die(print " Gagal: ".PDOException::getMessage());
				$qAlamat->setFetchMode(PDO::FETCH_ASSOC);
				$rAlamat=$qAlamat->fetch();
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 10;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$rAlamat['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$rAlamat['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$rAlamat['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();	
                $this->Ln();
				$this->Ln();
               
               
                $this->SetFont('Arial','B',12);
                $this->Cell($width,$height,strtoupper($_SESSION['lang']['lapPersonel']),0,1,'C');
                $this->Ln();	
                //$this->Cell(275,5,strtoupper($_SESSION['lang']['rprodksiPabrik']),0,1,'C');
                $this->Cell($width,$height,$_SESSION['lang']['unit'].' : '.$optNmOrg[$kodeOrg],0,1,'C');
                $this->SetFont('Arial','',8);
                $this->Cell(650,$height,$_SESSION['lang']['tanggal'],0,0,'R');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,date('d-m-Y H:i'),0,1,'R');
                $this->Cell(650,$height,$_SESSION['lang']['page'],0,0,'R');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,$this->PageNo(),0,1,'R');
                 $this->Cell(650,$height,$_SESSION['lang']['user'],0,0,'R');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,$_SESSION['standard']['username'],0,1,'R');

                $this->Ln();
                $this->Ln();
                $height = 20;
                $this->SetFont('Arial','B',6);
                $this->Cell(15,$height,substr($_SESSION['lang']['nomor'],0,2),1,0,'C');
                //$this->Cell(25,10,$_SESSION['lang']['kodeorganisasi'],1,0,'C');
                $this->Cell(125,$height,$_SESSION['lang']['unit'],1,1,'C');
//                $total2=count($dtKode2);
//                $tinggiMin=($total2*10)+10;
//               
//                foreach($dtKode2 as $brsKd =>$lstKd)
//                {
//                    $no+=1;
//                    $this->Cell(7,$height,$no,1,0,'C');
//                    $this->Cell(35,$height,$lstKd,1,1,'C');
//                }
               // $this->Cell(100,5,$_SESSION['lang']['kernel'],1,1,'C');
                $yTinggi=$this->GetY();
//                
                
                $this->SetY($yTinggi-20);
                $this->setX(168);
                $b=168;
                $dc=22;
                $tnggi=@$yxti+10;
                for($c=1;$c<=$totalTipe;$c++)
                {
                    $dc+=22;
                    
                   if($c!=$totalTipe)
                   {
                        if($c==1)
                        {
                            $this->Cell(100,10,$dataTipeKary[$c],1,0,'C');
                            $yxti=$yTinggi=$this->GetY();
                            $this->setY($yxti+10);
                            $this->setX($b);
                            $this->SetFont('Arial','',6);
                            $this->Cell(30,10,$_SESSION['lang']['karyawan'],1,0,'C');
                            $this->Cell(40,10,$_SESSION['lang']['jumlahanak'],1,0,'C');
                            $this->Cell(30,10,$_SESSION['lang']['istri'],1,0,'C');
                        }
                        else
                        {
                            
                            $yxti=$yTinggi=$this->GetY();
                            $xyti=$this->GetX();
                            $this->setY($yxti-10);
                            $this->setX($xyti);
                            $this->Cell(100,10,$dataTipeKary[$c],1,0,'C');
                            $yxti=$yTinggi=$this->GetY();
                            $this->setY($yxti+10);
                            $this->setX($xyti);
                            $this->SetFont('Arial','',6);
                            $this->Cell(30,10,$_SESSION['lang']['karyawan'],1,0,'C');
                            $this->Cell(40,10,$_SESSION['lang']['jumlahanak'],1,0,'C');
                            $this->Cell(30,10,$_SESSION['lang']['istri'],1,0,'C');
                        }
                   }
                   else
                   {
                       
                        $yxti=$yTinggi=$this->GetY();
                        $xyti=$this->GetX();
                        $this->setY($yxti-10);
                        $this->setX($xyti);
                        $this->Cell(100,10,$dataTipeKary[$c],1,0,'C');
                        $yxti=$yTinggi=$this->GetY();
                        $this->setY($yxti+10);
                        $this->setX($xyti);
                        $this->SetFont('Arial','',6);
                        $this->Cell(30,10,$_SESSION['lang']['karyawan'],1,0,'C');
                        $this->Cell(40,10,$_SESSION['lang']['jumlahanak'],1,0,'C');
                        $this->Cell(30,10,$_SESSION['lang']['istri'],1,1,'C');
                   
                   }
                   
                   $b+=98;
                   
                }

   
            }
            }
            //================================

            $pdf=new PDF('L','pt','A4');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 10;
            $pdf->AddPage();
            $pdf->SetFont('Arial','',6);

            
            
            foreach($dtKode as $brsKd =>$lstKd)
            {
               
                if(strlen($lstKd)<6)
                {   
                    $bgtKary="select * from ".$dbname.".bgt_datakaryawan_vw where lokasitugas='".$lstKd."'";
                    $bgtKaryIstr="select * from ".$dbname.".bgt_istri_karyawan_vw where lokasitugas='".$lstKd."'";
                }
                else
                {
                     $bgtKary="select * from ".$dbname.".bgt_datakaryawan_vw where subbagian='".$lstKd."'";
                     $bgtKaryIstr="select * from ".$dbname.".bgt_istri_karyawan_vw where subbagian='".$lstKd."'";
                }
                //echo $bgtKary;
                //get jumlah karyawan dan anak
				$qBgtKary=$owlPDO->query($bgtKary) or die(print " Gagal: ".PDOException::getMessage());
				$qBgtKary->setFetchMode(PDO::FETCH_ASSOC);
                while($rBgtKary=$qBgtKary->fetch())
                {
                    if(is_null($rBgtKary['subbagian'])||$rBgtKary['subbagian']==''||$rBgtKary['subbagian']=='0')
                    {
                        $dataKary[$rBgtKary['lokasitugas']][$rBgtKary['tipe']]=$rBgtKary['karyawan'];
                        $dataTanggugan[$rBgtKary['lokasitugas']][$rBgtKary['tipe']]=$rBgtKary['tanggungan'];
                    }
                    else
                    {
                        $dataKary[$rBgtKary['subbagian']][$rBgtKary['tipe']]=$rBgtKary['karyawan'];
                        $dataTanggugan[$rBgtKary['subbagian']][$rBgtKary['tipe']]=$rBgtKary['tanggungan'];
                    }
                }
                  
                //get jumlah istri
				$qBgtKaryIstri=$owlPDO->query($bgtKaryIstr) or die(print " Gagal: ".PDOException::getMessage());
				$qBgtKaryIstri->setFetchMode(PDO::FETCH_ASSOC);
                while($rBgtKaryIstri=$qBgtKaryIstri->fetch())
                {
                    if(is_null($rBgtKaryIstri['subbagian'])||$rBgtKaryIstri['subbagian']==''||$rBgtKaryIstri['subbagian']=='0')
                    {
                        $dataKaryIstri[$rBgtKaryIstri['lokasitugas']][$rBgtKaryIstri['tipe']]=$rBgtKaryIstri['jumlahistri'];
                    }
                    else
                    {
                        $dataKaryIstri[$rBgtKaryIstri['subbagian']][$rBgtKaryIstri['tipe']]=$rBgtKaryIstri['jumlahistri'];
                    }
                }
            }
            
            foreach($dtKode2 as $brsKd =>$lstKd)
            {
                $no+=1;
                if($no==1)
                {
                $pdf->Cell(15,$height,$no,1,0,'C');
                $pdf->Cell(125,$height,$optNmOrg[$lstKd],1,0,'L');
                }
                else
                {
                    $bmilY=$pdf->GetY();
                    $bmilX=$pdf->GetX();
                    $pdf->Cell(15,$height,$no,1,0,'C');
                    $pdf->Cell(125,$height,$optNmOrg[$lstKd],1,0,'L');
                }
                //$yTinggi=$pdf->GetY();

                //$pdf->SetY($yTinggi);
                $pdf->setX(168);
                $b=168;
                $dc=22;
               // $tnggi=$yxti+10;
                for($c=1;$c<=$totalTipe;$c++)
                {
                    $dc+=22;
                    
                   if($c!=$totalTipe)
                   {
                            $yxti=$yTinggi=$pdf->GetY();
                            $xyti=$pdf->GetX();
                            $pdf->setY($yxti);
                            $pdf->setX($xyti);
                            $pdf->Cell(30,$height,@$dataKary[$lstKd][$dataTipeKary[$c]],1,0,'R');
                            $pdf->Cell(40,$height,@$dataTanggugan[$lstKd][$dataTipeKary[$c]],1,0,'R');
                            $pdf->Cell(30,$height,@$dataKaryIstri[$lstKd][$dataTipeKary[$c]],1,0,'R');

                   }
                   else
                   {
                        $yxti=$yTinggi=$pdf->GetY();
                        $xyti=$pdf->GetX();
                        $pdf->setY($yxti);
                        $pdf->setX($xyti);
                        $pdf->Cell(30,$height,@$dataKary[$lstKd][$dataTipeKary[$c]],1,0,'R');
                        $pdf->Cell(40,$height,@$dataTanggugan[$lstKd][$dataTipeKary[$c]],1,0,'R');
                        $pdf->Cell(30,$height,@$dataKaryIstri[$lstKd][$dataTipeKary[$c]],1,1,'R');
                   }
                    @$totKary[$dataTipeKary[$c]]+=$dataKary[$lstKd][$dataTipeKary[$c]];
                    @$totAnak[$dataTipeKary[$c]]+=$dataTanggugan[$lstKd][$dataTipeKary[$c]];
                    @$totIstri[$dataTipeKary[$c]]+=$dataKaryIstri[$lstKd][$dataTipeKary[$c]];
                }
            }
			//
            $pdf->Cell(140,$height,$_SESSION['lang']['total'],1,0,'L');
            $pdf->setX(168);
            $b=168;
            $dc=22;
            for($jm=1;$jm<=$totalTipe;$jm++)
            {
                if($jm!=$totalTipe)
                   {
                            $yxti=$yTinggi=$pdf->GetY();
                            $xyti=$pdf->GetX();
                            $pdf->setY($yxti);
                            $pdf->setX($xyti);
                            $pdf->Cell(30,$height,$totKary[$dataTipeKary[$jm]],1,0,'R');
                            $pdf->Cell(40,$height,$totAnak[$dataTipeKary[$jm]],1,0,'R');
                            $pdf->Cell(30,$height,$totIstri[$dataTipeKary[$jm]],1,0,'R');

                   }
                   else
                   {
                        $yxti=$yTinggi=$pdf->GetY();
                        $xyti=$pdf->GetX();
                        $pdf->setY($yxti);
                        $pdf->setX($xyti);
                        $pdf->Cell(30,$height,$totKary[$dataTipeKary[$jm]],1,0,'R');
                        $pdf->Cell(40,$height,$totAnak[$dataTipeKary[$jm]],1,0,'R');
                        $pdf->Cell(30,$height,$totIstri[$dataTipeKary[$jm]],1,1,'R');
                   }
             }
            
            $pdf->Output();	
                
                
            break;
                
            default:
            break;
        }
	
?>
