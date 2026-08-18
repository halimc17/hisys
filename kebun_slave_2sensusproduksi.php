<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$periode= checkPostGet('periode','');
$kodeorg= checkPostGet('kodeorg','');
$pt     = checkPostGet('pt','');
$divisi = checkPostGet('divisi','');

$where="";
if($kodeorg!=''){
	$where.=" and kodeorg like '".$kodeorg."%'";
}
if($divisi!=''){
	$where.=" and kodeorg like '".$divisi."%'";
}
if($pt!=''){
	$where.=" and substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}


$arrsms=array("1"=>"I","2"=>"II");

if($proses=='excel')
{
$bg=" bgcolor=#DEDEDE";
$brdr=1;

}
else
{ 
    $bg="";
    $brdr=0;
}

$tab="";
if($proses=='excel')
{
    $bgcoloraja="bgcolor=#DEDEDE ";
    $brdr=1;
    $tab.="
    <table>
    <tr><td colspan=5 align=left><b>Laporan Sensus Produksi</b></td></tr><tr><td colspan=5 align=left><b>".$_SESSION['lang']['periode']." : ".$periode."</b></td></tr>
    <tr><td colspan=5 align=left>&nbsp;</td></tr>
    </table>";
}

$tab.="<table class=sortable cellspacing=1 border=".$brdr.">
        <thead>
            <tr>
				<th align=center rowspan=3 bgcolor=gray>".$_SESSION['lang']['nourut']."</th>    
				<th align=center rowspan=3 bgcolor=gray>".$_SESSION['lang']['blok']."</th>    
				<th align=center rowspan=3 bgcolor=gray>".$_SESSION['lang']['luas']."</th>
				<th align=center rowspan=3 bgcolor=gray>".$_SESSION['lang']['pokok']."</th> 
				<th align=center rowspan=3 bgcolor=gray>".$_SESSION['lang']['jjg']."</th> 
				<th align=center rowspan=3 bgcolor=gray>".$_SESSION['lang']['kg']."</th>  
				<th align=center rowspan=3 bgcolor=gray >".$_SESSION['lang']['bjr']."</th> 
				<th align=center colspan=30  bgcolor=gray>".$_SESSION['lang']['sebaran']."</th> 
			</tr>";
			
		for($i=1;$i<=12;$i++){
			$tab.="<th align=center colspan=2 bgcolor=gray>".numToMonth($i,'I','long')."</th>";
			if($i==4){$tab.="<th align=center colspan=2 bgcolor=gray>Total</th>";}
			if($i==8){$tab.="<th align=center colspan=2 bgcolor=gray>Total</th>";}
			if($i==12){$tab.="<th align=center colspan=2 bgcolor=gray>Total</th>";}
        }
        $tab.="</tr>";

        $tab.="<tr>";
        for($i=1;$i<=12;$i++){
            $tab.="
				<th align=center bgcolor=gray>".$_SESSION['lang']['jjg']."</th>
				<th align=center bgcolor=gray>".$_SESSION['lang']['kg']."</th>
				";
			if($i==4){$tab.="<th align=center bgcolor=gray>".$_SESSION['lang']['jjg']."</th><th align=center bgcolor=gray>".$_SESSION['lang']['kg']."</th>";}
			if($i==8){$tab.="<th align=center bgcolor=gray>".$_SESSION['lang']['jjg']."</th><th align=center bgcolor=gray>".$_SESSION['lang']['kg']."</th>";}
			if($i==12){$tab.="<th align=center bgcolor=gray>".$_SESSION['lang']['jjg']."</th><th align=center bgcolor=gray>".$_SESSION['lang']['kg']."</th>";}
        }
        $tab.="</tr>	
					
        </thead>
        <tbody id=container>";
		
		$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
		$kddivisi=array();
		$str="select * from ".$dbname.".kebun_rencanapanen where 1=1 ".$where." and tahun='".$periode."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $kdblok[$bar['kodeblok']]=$bar['kodeblok'];
            $kddivisi[substr($bar['kodeorg'],0,6)]=substr($bar['kodeorg'],0,6);
            $listblok[$bar['kodeorg']][$bar['kodeblok']]=$bar['kodeblok'];
            
            @$jumlahjjg[$bar['kodeorg']][$bar['kodeblok']]+=$bar['jumlah'];
            @$kgsensus[$bar['kodeorg']][$bar['kodeblok']]+=$bar['kgsensus'];
            $jjg[$bar['kodeorg']][$bar['kodeblok']][$bar['bulan']]=$bar['jumlah'];
            $kg[$bar['kodeorg']][$bar['kodeblok']][$bar['bulan']]=$bar['kgsensus'];
            $pokok[$bar['kodeorg']][$bar['kodeblok']]=$bar['jumlahpokok'];
            $luas[$bar['kodeorg']][$bar['kodeblok']]=$bar['jumlahha'];
			
			if($bar['bulan']>=1 and $bar['bulan']<=4){
				$jjg[$bar['kodeorg']][$bar['kodeblok']]['i']+=$bar['jumlah'];
				$kg[$bar['kodeorg']][$bar['kodeblok']]['i']+=$bar['kgsensus'];
			}
			if($bar['bulan']>=5 and $bar['bulan']<=8){
				$jjg[$bar['kodeorg']][$bar['kodeblok']]['ii']+=$bar['jumlah'];
				$kg[$bar['kodeorg']][$bar['kodeblok']]['ii']+=$bar['kgsensus'];
			}
			if($bar['bulan']>=9 and $bar['bulan']<=12){
				$jjg[$bar['kodeorg']][$bar['kodeblok']]['iii']+=$bar['jumlah'];
				$kg[$bar['kodeorg']][$bar['kodeblok']]['iii']+=$bar['kgsensus'];
			}
        }
        foreach($kddivisi as $divisi){
            foreach($kdblok as $blok){
                if(@$listblok[$divisi][$blok]!=''){
                    @$no+=1;
                    $tab.="<tr class=rowcontent id=row".$no.">";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td align=center>".$nmorg[$blok]."</td>";
                    $tab.="<td align=right>".@number_format($luas[$divisi][$blok],2)."</td>";
                    $tab.="<td align=right>".@number_format($pokok[$divisi][$blok])."</td>";
                    $tab.="<td align=right>".@number_format($jumlahjjg[$divisi][$blok])."</td>";
                    $tab.="<td align=right>".@number_format($kgsensus[$divisi][$blok])."</td>";
                    $tab.="<td align=right>".@number_format($kgsensus[$divisi][$blok]/$jumlahjjg[$divisi][$blok],2)."</td>";
                    for($i=1;$i<=12;$i++){
                        $tab.="
                            <td align=right>".@number_format($jjg[$divisi][$blok][$i])."</td>
                            <td align=right>".@number_format($kg[$divisi][$blok][$i])."</td>                                           
                        ";
						
						if($i==4){
							$tab.="<td align=right style=background-color:#B4ECFF;>".@number_format($jjg[$divisi][$blok]['i'])."</td>
								   <td align=right style=background-color:#B4ECFF;>".@number_format($kg[$divisi][$blok]['i'])."</td>";
							$stjjg[$divisi]['i']+=$jjg[$divisi][$blok]['i'];	   
							$stkg[$divisi]['i']+=$kg[$divisi][$blok]['i'];	  
							$gtjjg['i']+=$jjg[$divisi][$blok]['i'];	   
							$gtkg['i']+=$kg[$divisi][$blok]['i'];	   
						}
						if($i==8){
							$tab.="<td align=right style=background-color:#B4ECFF;>".@number_format($jjg[$divisi][$blok]['ii'])."</td>
								   <td align=right style=background-color:#B4ECFF;>".@number_format($kg[$divisi][$blok]['ii'])."</td>";
							$stjjg[$divisi]['ii']+=$jjg[$divisi][$blok]['ii'];	   
							$stkg[$divisi]['ii']+=$kg[$divisi][$blok]['ii'];	   	   
							$gtjjg['ii']+=$jjg[$divisi][$blok]['ii'];	   
							$gtkg['ii']+=$kg[$divisi][$blok]['ii'];	   
						}
						if($i==12){
							$tab.="<td align=right style=background-color:#B4ECFF;>".@number_format($jjg[$divisi][$blok]['iii'])."</td>
								   <td align=right style=background-color:#B4ECFF;>".@number_format($kg[$divisi][$blok]['iii'])."</td>";
							$stjjg[$divisi]['iii']+=$jjg[$divisi][$blok]['iii'];	   
							$stkg[$divisi]['iii']+=$kg[$divisi][$blok]['iii'];	
							$gtjjg['iii']+=$jjg[$divisi][$blok]['iii'];	   
							$gtkg['iii']+=$kg[$divisi][$blok]['iii'];	   							
						}
                        @$stjjg[$divisi][$i]+=$jjg[$divisi][$blok][$i];
                        @$stkg[$divisi][$i]+=$kg[$divisi][$blok][$i];
                    }
                    @$stluas[$divisi]+=$luas[$divisi][$blok];
                    @$stpokok[$divisi]+=$pokok[$divisi][$blok];
                    @$stjumlahjjg[$divisi]+=$jumlahjjg[$divisi][$blok];
                    @$stkgsensus[$divisi]+=$kgsensus[$divisi][$blok];
                }
            }
								   
            $tab.="
                <tr>
                    <td  bgcolor=#80FFFE colspan=2 align=center>".$_SESSION['lang']['subtotal']." ".$divisi."</td>
                    <td  bgcolor=#80FFFE align=right>".@number_format($stluas[$divisi],2)."</td>
                    <td  bgcolor=#80FFFE align=right>".@number_format($stpokok[$divisi])."</td>
                    <td  bgcolor=#80FFFE align=right>".@number_format($stjumlahjjg[$divisi])."</td>
                    <td  bgcolor=#80FFFE align=right>".@number_format($stkgsensus[$divisi])."</td>    
                    <td  bgcolor=#80FFFE align=right>".@number_format($stkgsensus[$divisi]/$stjumlahjjg[$divisi],2)."</td>";    
                    for($i=1;$i<=12;$i++){
                        $tab.="<td  bgcolor=#80FFFE align=right>".@number_format($stjjg[$divisi][$i])."</td>";
                        $tab.="<td  bgcolor=#80FFFE align=right>".@number_format($stkg[$divisi][$i])."</td>";
						
						if($i==4){
							$tab.="<td align=right style=background-color:#B4ECFF;>".@number_format($stjjg[$divisi]['i'])."</td>
								   <td align=right style=background-color:#B4ECFF;>".@number_format($stkg[$divisi]['i'])."</td>";
						}
						if($i==8){
							$tab.="<td align=right style=background-color:#B4ECFF;>".@number_format($stjjg[$divisi]['ii'])."</td>
								   <td align=right style=background-color:#B4ECFF;>".@number_format($stkg[$divisi]['ii'])."</td>";
						}
						if($i==12){
							$tab.="<td align=right style=background-color:#B4ECFF;>".@number_format($stjjg[$divisi]['iii'])."</td>
								   <td align=right style=background-color:#B4ECFF;>".@number_format($stkg[$divisi]['iii'])."</td>";
						}
                        @$gtjjg[$i]+=$stjjg[$divisi][$i];
                        @$gtkg[$i]+=$stkg[$divisi][$i];   
                    }
            $tab.="</tr>";
            
            @$gtluas+=$stluas[$divisi];
            @$gtpokok+=$stpokok[$divisi];
            @$gtjumlahjjg+=$stjumlahjjg[$divisi];
            @$gtkgsensus+=$stkgsensus[$divisi];
            
        }
        $tab.="
                <tr>
                    <td colspan=2 align=center  bgcolor=#48D1CC>".$_SESSION['lang']['grnd_total']."</td>
                    <td align=right  bgcolor=#48D1CC>".@number_format($gtluas,2)."</td>
                    <td align=right  bgcolor=#48D1CC>".@number_format($gtpokok)."</td>
                    <td align=right  bgcolor=#48D1CC>".@number_format($gtjumlahjjg)."</td>
                    <td align=right  bgcolor=#48D1CC>".@number_format($gtkgsensus)."</td>    
                    <td align=right  bgcolor=#48D1CC>".@number_format($gtkgsensus/$gtjumlahjjg,2)."</td>";    
                    for($i=1;$i<=12;$i++){
                        $tab.="<td align=right  bgcolor=#48D1CC>".@number_format($gtjjg[$i])."</td>";
                        $tab.="<td align=right  bgcolor=#48D1CC>".@number_format($gtkg[$i])."</td>";
						if($i==4){
							$tab.="<td align=right style=background-color:#B4ECFF;>".@number_format($gtjjg['i'])."</td>
								   <td align=right style=background-color:#B4ECFF;>".@number_format($gtkg['i'])."</td>";
						}
						if($i==8){
							$tab.="<td align=right style=background-color:#B4ECFF;>".@number_format($gtjjg['ii'])."</td>
								   <td align=right style=background-color:#B4ECFF;>".@number_format($gtkg['ii'])."</td>";
						}
						if($i==12){
							$tab.="<td align=right style=background-color:#B4ECFF;>".@number_format($gtjjg['iii'])."</td>
								   <td align=right style=background-color:#B4ECFF;>".@number_format($gtkg['iii'])."</td>";
						}
                    }
            $tab.="</tr>";
        $tab.="</table>"; 

switch($proses){
    case'preview':
   
    echo $tab;
    break;

    case'excel':
    if($kodeorg=='' && $periode==''){
        exit("Error:Field Tidak Boleh Kosong");
    }

    $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
    $dte=date("YmdHis");
    $nop_="Laporan_Sensus_Produksi_".$kodeorg."_".$periode;
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
		if($kodeorg=='' && $periode=='')
		{
			exit("Error:Field Tidak Boleh Kosong");
		}
	
				$cols=247.5;
				$wkiri=50;
				$wlain=11;
	
		class PDF extends FPDF {
			function Header() {
				global $kodeorg;
				global $periode;
				global $dbname;
				global $wkiri, $wlain;
                $width = $this->w - $this->lMargin - $this->rMargin;
				$height = 10;

                $this->SetFont('Arial','B',8);
                $this->Cell($width,$height,strtoupper("Laporan Sensus Produksi"),0,1,'L');
                $this->Cell($width,$height,$_SESSION['lang']['periode'].' : '.$periode,0,1,'R');
                $tinggiAkr=$this->GetY();
                $ksamping=$this->GetX();
                $this->SetY($tinggiAkr+20);
                $this->SetX($ksamping);
                $this->Cell(790,$height,' ',0,1,'R');
                
                $height = 15;
                $this->SetFillColor(220,220,220);
                $this->SetFont('Arial','B',8);
                
                $tinggiAkr=$this->GetY();
                $ksamping=$this->GetX();
                $this->SetY($tinggiAkr+20);
                $this->SetX($ksamping);
               
                $this->Cell(15,$height,"No.",'TLR',0,'C',1);
                $this->Cell(80,$height,"Kode Blok",'TLR',0,'C',1);
                $this->Cell(60,$height,"Tahun Tanam",'TLR',0,'C',1);
                $this->Cell(70,$height,"Tahun Produksi",'TLR',0,'C',1);
                $this->Cell(30,$height,"Bulan",'TLR',0,'C',1);
                $this->Cell(90,$height,"JJG",'TLR',0,'C',1);
                $this->Cell(90,$height,"BJR",'TLR',0,'C',1);
                $this->Cell(90,$height,"Kg. Sensus",'TLR',0,'C',1);
                $this->Cell(90,$height,"Jumlah Pokok",'TLR',0,'C',1);
                $this->Cell(90,$height,"Luas",'TLR',0,'C',1);
                $this->Cell(90,$height,"Estimasi Premi",'TLR',1,'C',1);
                 
			}
			function Footer()
			{
				$this->SetY(-15);
				$this->SetFont('Arial','I',11);
				$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
			}
		}
		//================================

    $pdf=new PDF('L','pt','A4');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 10;
            $pdf->AddPage();
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',7);
            $i=0;
    if(!empty($kodeblok)){
        foreach($kodeblok as $blok=>$lsblok)
            {
                $i++;

                    $pdf->Cell(15,$height,$i,1,0,'L',1);
                    $pdf->Cell(80,$height,$lsblok,1,0,'L',1);
                    $pdf->Cell(60,$height,$tt[$lsblok],1,0,'C',1);
                    $pdf->Cell(70,$height,$thnprd[$lsblok],1,0,'C',1);
                    $pdf->Cell(30,$height,$bln[$lsblok],1,0,'C',1);
                    $pdf->Cell(90,$height,number_format($jjg[$lsblok],2),1,0,'R',1);
                    $pdf->Cell(90,$height,number_format($bjr[$lsblok],2),1,0,'R',1);
                    $pdf->Cell(90,$height,number_format($kgsensus[$lsblok],2),1,0,'R',1);
                    $pdf->Cell(90,$height,number_format($jmlpkk[$lsblok],2),1,0,'R',1);
                    $pdf->Cell(90,$height,number_format($luas[$lsblok],2),1,0,'R',1);
                    $pdf->Cell(90,$height,number_format($espremi[$lsblok],2),1,1,'R',1);
             }
    }
                $totalbjr+=$bjr[$lsblok]-$bjr[$lsblok];
                @$x=$totalbjr/$i;
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','B',7);
                $pdf->Cell(345,$height,"Total",1,0,'C',1);
                $pdf->Cell(90,$height,number_format($x,2),1,0,'R',1);
                $pdf->Cell(360,$height,"",1,0,'C',1);
                $pdf->Output();
        break;

    default:
    break;
}
	
?>