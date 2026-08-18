<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdOrg=checkPostGet('kdOrg','');
$kdAfd=checkPostGet('kdAfd','');
$periode=checkPostGet('periode','');

$alasansisip=makeOption($dbname,'kebun_5alasanrencanasisip','kodealasanrencanasisip,deskripsi');


if($kdAfd=='')
    $kdAfd=$kdOrg;

if(($proses=='preview')or($proses=='excel')or($proses=='pdf')){
    if($kdOrg==''){
        if(substr($lksiTgs,2,2)=='HO'){
            
        }
        else{
            echo"Error: Estate code and afdeling code required."; exit;
        }
    }

    if(($periode=='')){
            echo"Error: Period required."; exit;
    }

}
$brdr=0;
$bgcoloraja=$stream='';
if($proses=='excel')
{
    //exit("error:".$arrPilMode[$pilMode]."__".$pilMode);
    $bgcoloraja="bgcolor=#DEDEDE align=center";
    $brdr=1;
    $stream.="
    <table>
    <tr><td colspan=15 align=left><b><font size=5>".$_SESSION['lang']['laprencanasisip']."</font></b></td></tr>
    <tr><td colspan=15 align=left>".$_SESSION['lang']['kebun']." : ".$kdOrg."</td></tr>
    <tr><td colspan=15 align=left>".$_SESSION['lang']['periode']." : ".$periode."</td></tr>
    </table>";
}
if ($proses=='excel' or $proses=='preview'){
    ##cek rencana sebelum periode
    $s_cek="select blok,sum(rencanasisip) as rencana
            from ".$dbname.".kebun_rencanasisip
            where blok like '%".$kdOrg."%' and blok like '%".$kdAfd."%' 
            and periode < '".$periode."' and posting=1 and periode like '".substr($periode,0,4)."%'
            group by blok";
	$q_cek=$owlPDO->query($s_cek) or die(print " Gagal: ".PDOException::getMessage());
	$q_cek->setFetchMode(PDO::FETCH_ASSOC);
    while($r_cek=$q_cek->fetch())
    {
        $blok[$r_cek['blok']]=$r_cek['blok'];
        $cek_rencana[$r_cek['blok']]=$r_cek['rencana'];  
    }

    ##cek realisasi sebelum periode
   $s_cekreal="select a.kodeorg as blok,a.kodekegiatan as kodekegiatan,sum(a.hasilkerja) as sudahsisip 
               from ".$dbname.".kebun_prestasi a
               left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
               where substr(b.tanggal,1,7) < '".$periode."' and b.jurnal=1 and substr(b.tanggal,1,4) like '".substr($periode,0,4)."%'
               and b.kodeorg like '%".$kdOrg."%' and a.kodeorg like '%".$kdAfd."%'
               and a.kodekegiatan in (SELECT nilai FROM ".$dbname.".`setup_parameterappl` WHERE `kodeaplikasi` LIKE 'tn' AND `kodeparameter` LIKE 'sisip%')
               group by a.kodeorg";
	$q_cekreal=$owlPDO->query($s_cekreal) or die(print " Gagal: ".PDOException::getMessage());
	$q_cekreal->setFetchMode(PDO::FETCH_ASSOC);
    while($r_cekreal=$q_cekreal->fetch())
    {
        $blok[$r_cekreal['blok']]=$r_cekreal['blok'];
        $cek_sisip[$r_cekreal['blok']]=$r_cekreal['sudahsisip'];
    }
    
    ##rencana sisip
    $str1="select sum(rencanasisip) as rencana,blok,periode,pokok,sph,pokokmati,keterangan
            from ".$dbname.".kebun_rencanasisip 
            where blok like '%".$kdOrg."%' and blok like '%".$kdAfd."%' 
            and periode like '".$periode."%' and posting=1
            group by blok";
	// echo $str1;
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_ASSOC);
    while($bar1=$res1->fetch())
    {
        $blok[$bar1['blok']]=$bar1['blok'];
        $pokok[$bar1['blok']]=$bar1['pokok'];
        $sph[$bar1['blok']]=$bar1['sph'];
        $pokokmati[$bar1['blok']]=$bar1['pokokmati'];
        $rencana[$bar1['blok']]=$bar1['rencana'];
        $ket[$bar1['blok']]=$bar1['keterangan'];
    }
//echo "<pre>";    
//print_r($blok);
//echo "</pre>";  
//exit();
    
    
    $str2="select a.kodeorg,a.kodekegiatan as kodekegiatan,sum(a.hasilkerja) as sudahsisip 
           from ".$dbname.".kebun_prestasi a
           left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
           where b.tanggal like '".$periode."%' and b.jurnal=1
           and b.kodeorg like '%".$kdOrg."%' and a.kodeorg like '%".$kdAfd."%'
           and a.kodekegiatan in (SELECT nilai FROM ".$dbname.".`setup_parameterappl` WHERE `kodeaplikasi` LIKE 'tn' AND `kodeparameter` LIKE 'sisip%')
           group by a.kodeorg";
		   // echo $str2;
	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_ASSOC);
    while($bar2=$res2->fetch()){
        $blok[$bar2['kodeorg']]=$bar2['kodeorg'];
        $sudahsisip[$bar2['kodeorg']]=$bar2['sudahsisip'];
    }
    
    $stream="<table class=sortable cellspacing=1 border=$brdr>
         <thead>
         <tr class=rowheader>
            <th align=center>No.</th>
            <th align=center>".$_SESSION['lang']['blok']."</th>
            <th align=center>".$_SESSION['lang']['pokok']."</th>
            <th align=center>".$_SESSION['lang']['sph']."</th>
            <th align=center>".$_SESSION['lang']['pokokmati']."</th>
            <!--<th align=center>".$_SESSION['lang']['rencanasisip']." sd B-1</th>
            <th align=center>".$_SESSION['lang']['selesai']." ".$_SESSION['lang']['sisip']." sd B-1</th>-->
            <th align=center>".$_SESSION['lang']['rencanasisip']."</th>
            <th align=center>".$_SESSION['lang']['keterangan']."</th>
            <th align=center>".$_SESSION['lang']['selesai']." ".$_SESSION['lang']['sisip']." ".$_SESSION['lang']['bi']."</th>
            
         </tr></thead>
         <tbody>";
    $no=0;
    if(!empty($blok)) 
       {
        foreach($blok as $blk)
        {
            $no++;
			setIt($cek_rencana[$blk],0);
			setIt($cek_sisip[$blk],0);
			setIt($rencana[$blk],0);
			setIt($rencanalalu[$blk],0);
			setIt($sudahsisip[$blk],0);
            $rencanalalu[$blk]=$cek_rencana[$blk]-$cek_sisip[$blk];
            $totalrencana[$blk]=$rencana[$blk]+$rencanalalu[$blk];
//            echo "<pre>";
//            print_r($blk."=>".$rencanalalu[$blk]);
//            echo "</pre>";
            
//            $stream.="<tr class=rowcontent onclick=detailsisip('".$blk."','".$periode."')>";
            $stream.="<tr class=rowcontent>";
                        $stream.="<td align=center>".$no."</td>
                        <td align=center>".$blk."</td>
                        <td align=right>".number_format($pokok[$blk],0)."</td>
                        <td align=right>".number_format($sph[$blk],0)."</td>
                        <td align=right>".number_format($pokokmati[$blk],0)."</td>
                        <!--<td align=right>".number_format($cek_rencana[$blk],0)."</td>
                        <td align=right>".number_format($cek_sisip[$blk],0)."</td>-->    
                        <td align=right>".number_format($rencana[$blk],0)."</td>
                        <td align=left>".$alasansisip[$ket[$blk]]."</td>  
                        <td align=right>".number_format($sudahsisip[$blk],0)."</td>  
                        </tr>";
						@$t1+=$pokok[$blk];
						@$t2+=$pokokmati[$blk];
						
						@$t3+=$rencana[$blk];
						@$t4+=$sudahsisip[$blk];
						
						
        }
       }
    
    $stream.="</tbody>
        <tfoot>
		<tr>
		<td colspan=2 align=center>Total</td>
		<td align=right>".number_format($t1)."</td>
		<td align=right></td>
		<td align=right>".number_format($t2)."</td>
		<td align=right>".number_format($t3)."</td>
		<td align=right></td>
		<td align=right>".number_format($t4)."</td>
		</tr>
        </tfoot>
        </table>";

}  
switch($proses)
{
      case 'getAfdAll':
          $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                where kodeorganisasi like '".$kdAfd."%' and length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') order by namaorganisasi
                ";
          $op="<option value=''>".$_SESSION['lang']['all']."</option>";
          $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		  $res->setFetchMode(PDO::FETCH_OBJ);
		  while($bar=$res->fetch()) 
          {
              $op.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
          }
          echo $op;
          exit();
        break; 
       case'preview':
            echo $stream;    
	break;
       
        case 'excel':
            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_=$_SESSION['lang']['sisip'].$kdAfd.$periode."-".date('YmdHis');
             $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
             gzwrite($gztralala, $stream);
             gzclose($gztralala);
             echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls.gz';
                </script>";            
        break;
    
    case'pdf':
	$kdOrg=$_GET['kdOrg'];
        $kdAfd=$_GET['kdAfd'];
        $periode=$_GET['periode'];
//        $periode2=$_GET['periode2'];
       
	class PDF extends FPDF
        {
            function Header() {
                global $owlPDO;
                global $dbname;
                global $kdOrg;
                global $kdAfd;
                global $periode;
//                global $periode2;
                    
                $sAlmat="select * from ".$dbname.".organisasi where kodeorganisasi='".$kdOrg."'";
                $qAlamat=$owlPDO->query($sAlmat) or die(print " Gagal: ".PDOException::getMessage());
				$qAlamat->setFetchMode(PDO::FETCH_ASSOC);
                $rAlamat=$qAlamat->fetch();

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Ln();	
                		
                $this->SetFont('Arial','',9);
                $this->Cell(70/100*$width,$height, strtoupper($_SESSION['lang']['laprencanasisip']),'',0,'C');
                $this->Ln();$this->Ln();
                $this->SetFont('Arial','',7);
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$periode,'',0,'L');
                $this->Ln();
                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['kodeorg'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/100*$width,$height,$kdOrg,'',1,'L');
                $this->Ln();	
				
                $this->SetFont('Arial','B',6);	
                $this->SetFillColor(220,220,220);
                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);		
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['pokok'],1,0,'C',1);			
                $this->Cell(15/100*$width,$height,$_SESSION['lang']['sph'],1,0,'C',1);		
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['pokokmati'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['rencanasisip'],1,0,'C',1);		
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['keterangan'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['selesai']." ".$_SESSION['lang']['sisip'],1,1,'C',1);
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        
        $pdf=new PDF('P','pt','A4');
        $pdf->lMargin=10;
        $pdf->rMargin=10;
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 10;
        $pdf->AddPage();
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',6);
        
        $s_cek="select blok,sum(rencanasisip) as rencana
            from ".$dbname.".kebun_rencanasisip
            where blok like '%".$kdOrg."%' and blok like '%".$kdAfd."%' 
            and periode < '".$periode."' and posting=1 and periode like '".substr($periode,0,4)."%'
            group by blok";
	$q_cek=$owlPDO->query($s_cek) or die(print " Gagal: ".PDOException::getMessage());
	$q_cek->setFetchMode(PDO::FETCH_ASSOC);
    while($r_cek=$q_cek->fetch())
    {
        $blok[$r_cek['blok']]=$r_cek['blok'];
        $cek_rencana[$r_cek['blok']]=$r_cek['rencana'];  
    }
    ##cek realisasi sebelum periode
   $s_cekreal="select a.kodeorg as blok,a.kodekegiatan as kodekegiatan,sum(a.hasilkerja) as sudahsisip 
               from ".$dbname.".kebun_prestasi a
               left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
               where substr(b.tanggal,1,7) < '".$periode."' and b.jurnal=1 and substr(b.tanggal,1,4) like '".substr($periode,0,4)."%'
               and b.kodeorg like '%".$kdOrg."%' and a.kodeorg like '%".$kdAfd."%'
               and a.kodekegiatan in (SELECT nilai FROM ".$dbname.".`setup_parameterappl` WHERE `kodeaplikasi` LIKE 'tn' AND `kodeparameter` LIKE 'sisip%')
               group by a.kodeorg";
	$q_cekreal=$owlPDO->query($s_cekreal) or die(print " Gagal: ".PDOException::getMessage());
	$q_cekreal->setFetchMode(PDO::FETCH_ASSOC);
    while($r_cekreal=$q_cekreal->fetch())
    {
        $blok[$r_cekreal['blok']]=$r_cekreal['blok'];
        $cek_sisip[$r_cekreal['blok']]=$r_cekreal['sudahsisip'];
    }
    
    ##rencana sisip
    $str1="select sum(rencanasisip) as rencana,blok,periode,pokok,sph,pokokmati,keterangan
            from ".$dbname.".kebun_rencanasisip 
            where blok like '%".$kdOrg."%' and blok like '%".$kdAfd."%' 
            and periode like '".$periode."%' and posting=1
            group by blok";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_ASSOC);
    while($bar1=$res1->fetch())
    {
        $blok[$bar1['blok']]=$bar1['blok'];
        $pokok[$bar1['blok']]=$bar1['pokok'];
        $sph[$bar1['blok']]=$bar1['sph'];
        $pokokmati[$bar1['blok']]=$bar1['pokokmati'];
        $rencana[$bar1['blok']]=$bar1['rencana'];
        $ket[$bar1['blok']]=$bar1['keterangan'];
    }
    
    $str2="select a.kodeorg as blok,a.kodekegiatan as kodekegiatan,sum(a.hasilkerja) as sudahsisip 
           from ".$dbname.".kebun_prestasi a
           left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
           where b.tanggal like '".$periode."%' and b.jurnal=1
           and b.kodeorg like '%".$kdOrg."%' and a.kodeorg like '%".$kdAfd."%'
           and a.kodekegiatan in (SELECT nilai FROM ".$dbname.".`setup_parameterappl` WHERE `kodeaplikasi` LIKE 'tn' AND `kodeparameter` LIKE 'sisip%')
           group by a.kodeorg";
	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$res2->setFetchMode(PDO::FETCH_ASSOC);
    while($bar2=$res2->fetch()){
        $blok[$bar2['blok']]=$bar2['blok'];
        $sudahsisip[$bar2['blok']]=$bar2['sudahsisip'];
    }
    
    $no=0;
    if(!empty($blok)) 
       {
        foreach($blok as $blk)
        {
            $no++;
			setIt($cek_rencana[$blk],0);
			setIt($cek_sisip[$blk],0);
			setIt($rencana[$blk],0);
			setIt($rencanalalu[$blk],0);
			setIt($sudahsisip[$blk],0);
			setIt($pokok[$blk],0);
			setIt($sph[$blk],0);
			setIt($pokokmati[$blk],0);
			setIt($totalrencana[$blk],0);
			setIt($ket[$blk],0);
            $rencanalalu[$blk]=$cek_rencana[$blk]-$cek_sisip[$blk];
            $totalrencana[$blk]=$rencana[$blk]+$rencanalalu[$blk];
//            echo "<pre>";
//            print_r($blk."=>".$rencanalalu[$blk]);
//            echo "</pre>";
            
            $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
            $pdf->Cell(8/100*$width,$height,$blk,1,0,'C',1);		
            $pdf->Cell(8/100*$width,$height,number_format($pokok[$blk],0),1,0,'R',1);			
            $pdf->Cell(15/100*$width,$height,number_format($sph[$blk],0),1,0,'R',1);		
            $pdf->Cell(8/100*$width,$height,number_format($pokokmati[$blk],0),1,0,'R',1);
            $pdf->Cell(8/100*$width,$height,number_format($totalrencana[$blk],0),1,0,'R',1);		
            $pdf->Cell(8/100*$width,$height,$ket[$blk],1,0,'L',1);
            $pdf->Cell(8/100*$width,$height,number_format($sudahsisip[$blk],0),1,1,'R',1);
        }
       }
       
        $pdf->Output();
	break;
    default:
        break;
}

?>