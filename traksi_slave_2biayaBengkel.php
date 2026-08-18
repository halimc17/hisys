<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses'])){
	$proses=$_POST['proses'];
}else{
	$proses=$_GET['proses'];
}
//$arr="##thnId##unitId";

$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sept","10"=>"Oct","11"=>"Nov","12"=>"Dec");
$arrBln1=array("1"=>"Januari","2"=>"Februari","3"=>"March","4"=>"April","5"=>"Mei","6"=>"Juni","7"=>"July","8"=>"August","9"=>"September","10"=>"October","11"=>"November","12"=>"December");
$optNmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optInduk=makeOption($dbname, 'organisasi','kodeorganisasi,induk');
$arrDt=array("0"=>"Head Office","1"=>"Local");

$unitId = checkPostGet('unitId','');
$thnId = checkPostGet('thnId','');
$bulanId = checkPostGet('bulanId','');
//

if($proses=='bulanapaaja'){
    //periode akuntansi
    $sPeriode="select distinct periode as bulan from ".$dbname.".setup_periodeakuntansi where periode like '".$thnId."%' order by periode desc";
    $qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
    $qPeriode->setFetchMode(PDO::FETCH_ASSOC);
    while($rPeriode=$qPeriode->fetch()){
        $optPeriode.="<option value=".$rPeriode['bulan'].">".$rPeriode['bulan']."</option>";
    }    
    echo $optPeriode;
}else{
    
if($unitId==''||$thnId==''){
    exit("Error: Fields are required");
}
    
//get data akun
if($_SESSION['language']=='EN'){
    $dd="namaakun1 as namaakun";
}else{
    $dd="namaakun";
}
$sAkun="select distinct noakun ,".$dd." from ".$dbname.".keu_5akun where noakun like '41101%' and noakun != '4110199' and CHAR_LENGTH(noakun)='7' order by noakun asc";
//exit("Error".$sAkun);
$qAkun=$owlPDO->query($sAkun) or die(print " Gagal: ".PDOException::getMessage());
$qAkun->setFetchMode(PDO::FETCH_ASSOC);
while($rAkun=$qAkun->fetch())
{
    $lstAkun[]=$rAkun['noakun'];
    $dtNamAkun[$rAkun['noakun']]=$rAkun['namaakun'];
}

$str = "select * from " . $dbname . ".bgt_kode"; 
$res = fetchdata($str);
foreach($res as $bar){
	$kodebudget[$bar['kodebudget']] = $bar['noakuntrk'];
}

//biaya budget
$sBudget="select noakun,sum(rupiah) as ttlrp, sum(rp01) as rp01,sum(rp02) as rp02,sum(rp03) as rp03,sum(rp04) as rp04,sum(rp05) as rp05,sum(rp06) as rp06,
          sum(rp07) as rp07,sum(rp01) as rp08,sum(rp09) as rp09,sum(rp12) as rp12,sum(rp11) as rp11,sum(rp12) as rp12
          from ".$dbname.".bgt_ws_vw where tahunbudget='".$thnId."' and kodeorg like '".substr($unitId,0,4)."%' group by tahunbudget,noakun";
#exit("Error".$sBudget);
$qBudget=$owlPDO->query($sBudget) or die(print " Gagal: ".PDOException::getMessage());
$qBudget->setFetchMode(PDO::FETCH_ASSOC);
$dtRupBudget=array();
while($rBudget=$qBudget->fetch()){
    $totBudget=0;
    for($bln=1;$bln<13;$bln++){
        $sdt=$bln;
        if($bln<10){
          $sdt="0".$bln;
        }
		
		if($rBudget['noakun']==''){
			$rBudget['noakun'] = $kodebudget[$rBudget['kodebudget']]; 
		}
		
        @$dtRupBudget[$rBudget['noakun']][$bln]=$rBudget['rp'.$sdt];
        @$totBudget[$bln]+=$dtRupBudget[$rBudget['noakun']][$bln];
    }
	@$ttlrpbgtthn[$rBudget['noakun']]+=$rBudget['ttlrp'];
}

// echo "<pre>";
// print_r($ttlrpbgtthn);
// echo "</pre>";
//realisai  atau aktual
for($ngulang=1;$ngulang<=12;$ngulang++){
    $sdt=$ngulang;
    if($ngulang<10){
		$sdt="0".$ngulang;
	}
	$sAktual="select distinct sum(jumlah) as jumlah,noakun,periode from ".$dbname.".keu_jurnaldt_vw 
			  where noakun like '41101%' and noakun != '4110199' and periode='".$thnId."-".$sdt."' and kodeorg like '".substr($unitId,0,4)."%' group by periode,noakun";
	//echo $sAktual;
	$qAktual=$owlPDO->query($sAktual) or die(print " Gagal: ".PDOException::getMessage());
	$qAktual->setFetchMode(PDO::FETCH_ASSOC);
	while($rAktual=$qAktual->fetch()){
		//mengantisipasi jika datanya min yang seharusnya di ambil di kredit
		if($rAktual['jumlah']<1){
			$rAktual['jumlah']=$rAktual['jumlah']*-1;
		}
	   $dtRupAktual[$rAktual['noakun']][$ngulang]=$rAktual['jumlah'];
	   setIt($totAktual[$ngulang],0);
	   $totAktual[$ngulang]+=$dtRupAktual[$rAktual['noakun']][$ngulang];
	}
}

//jam budget
$sJmBudget="select distinct * from ".$dbname.".bgt_ws_jam where tahunbudget='".$thnId."' and kodetraksi like '".substr($unitId,0,4)."%'";
//exit("Error".$sJmBudget);
//
$qJmBudget=$owlPDO->query($sJmBudget) or die(print " Gagal: ".PDOException::getMessage());
$qJmBudget->setFetchMode(PDO::FETCH_ASSOC);
$rJmBudget=$qJmBudget->fetch();
for($ngulanglg=1;$ngulanglg<13;$ngulanglg++){
    $sdt=$ngulanglg;
    if($ngulanglg<10){
        $sdt="0".$ngulanglg;
    }
    $lstJmBudget[$ngulanglg]=$rJmBudget['jam'.$sdt];
	@$ttljambklsetahun+=$rJmBudget['jam'.$sdt];
}
//jam aktual adalah sum downtime grup by kodeorg,left(tanggal,7)
$sJam="select tanggal ,sum(downtime) as jmlhjam from ".$dbname.".vhc_penggantianht where
       kodeorg like '".substr($unitId,0,4)."%' and left(tanggal,4)='".$thnId."' group by kodeorg,tanggal";
$qJam=$owlPDO->query($sJam) or die(print " Gagal: ".PDOException::getMessage());
$qJam->setFetchMode(PDO::FETCH_ASSOC);
while($rJam=$qJam->fetch()){
    $bubulan=intval(substr($rJam['tanggal'],5,2));
    @$dtJmAk[$bubulan]+=$rJam['jmlhjam'];
}

//exit("error:".$bln."__".$rJam['periode']."__".$sJam);

//$brsdt=count($data);
$brsdt=0;
$brdr=0;
$bgcoloraja=$tab='';
//
if($proses=='excel')
{
      $bgcoloraja="bgcolor=#DEDEDE ";
      $brdr=1;
}
        //biaya bengkel
        $tab.="<p><b>".strtoupper($_SESSION['lang']['biayabengkel'])." : ".$unitId.", ".$_SESSION['lang']['tahun']." : ".$thnId."  <i>Dalam Ribuan (000)</i></b></p>";
	$tab.="<table cellspacing=1 cellpadding=5 border=".$brdr." class=sortable>
	<thead class=rowheader>";
        $tab.="<tr>";
        $tab.="<th ".$bgcoloraja."  rowspan=2 align=center>No.</th>";
        $tab.="<th ".$bgcoloraja."  rowspan=2 align=center>".$_SESSION['lang']['noakun']."</th>";
        $tab.="<th ".$bgcoloraja."  rowspan=2 align=center>".$_SESSION['lang']['namaakun']."</th>";
        foreach($arrBln as $listBulan =>$isiBLn){
           $tab.="<th ".$bgcoloraja." colspan=2 align=center >".$isiBLn."</th>";
        }
        $tab.="</tr><tr>";
        foreach($arrBln as $listBulan =>$isiBLn){
            $tab.="<th ".$bgcoloraja."  align=center>Bgt</th>";
            $tab.="<th ".$bgcoloraja."  align=center>Act</th>";
        } 
        $tab.="</tr></thead><tbody>";
        foreach($lstAkun as $dtNoakun){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$dtNoakun."</td>";
			$tab.="<td>".$dtNamAkun[$dtNoakun]."</td>";
			foreach($arrBln as $listBulan =>$isiBLn){
				if($listBulan<10){
					$zz="0".$listBulan;
				}
				else{
					$zz=$listBulan;
				}
				@$dtRupBudget[$dtNoakun][$listBulan]=$dtRupBudget[$dtNoakun][$listBulan]/1000;
				@$dtRupAktual[$dtNoakun][$listBulan]=$dtRupAktual[$dtNoakun][$listBulan]/1000;
				
				#bgt = ttljambulan / totaljamsetahun x rupiahbudget
				@$rpbgtbulanan=(($lstJmBudget[$listBulan]/$ttljambklsetahun)*$ttlrpbgtthn[$dtNoakun])/1000;
				@$gtbgtrp[$listBulan]+=$rpbgtbulanan;
				
				$tab.="<td align=right>".@number_format($rpbgtbulanan,2)."</td>";
				$tab.="<td align=right style='cursor:pointer;'  title='Click for detail' onclick=displayDetail('".$thnId."-".$zz."','".$dtNoakun."','".substr($unitId,0,4)."',event)>".number_format($dtRupAktual[$dtNoakun][$listBulan],2)."</td>";
				
				
				
			}
			$tab.="</tr>";
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=3>".$_SESSION['lang']['total']."</td>";
        foreach($arrBln as $listBulan =>$isiBLn)
        {
            @$totBagiBudget[$listBulan]=$totBudget[$listBulan]/1000;
            @$totBagiAktual[$listBulan]=$totAktual[$listBulan]/1000;
            $tab.="<td align=right>".number_format($gtbgtrp[$listBulan],2)."</td>";
            $tab.="<td align=right>".number_format($totBagiAktual[$listBulan],2)."</td>";
        }
        $tab.="</tr>";
        $tab.="</tbody></table><br />";
        
        //jam bengkel
        $tab.="<p><b>".$_SESSION['lang']['jambengkel']." : ".$unitId.", ".$_SESSION['lang']['tahun']." : ".$thnId."</b></p>";
	$tab.="<table cellspacing=1 cellpadding=5 border=".$brdr." class=sortable>
			<thead class=rowheader>";
        $tab.="<tr><th rowspan=2 align=center>Keterangan</th>";
        $tab.="<th align=center colspan=12>".$_SESSION['lang']['jmlhJam']."</th>";
        $tab.="</tr><tr>";
		
		
        foreach($arrBln as $listBulan =>$isiBLn){
            $tab.="<th ".$bgcoloraja." align=center>".$isiBLn."</th>";
        }
        $tab.="</tr></thead><tbody>";
        $tab.="<tr class=rowcontent>";
        $tab.="<td>".$_SESSION['lang']['anggaran']."</td>";
        foreach($arrBln as $listBulan =>$isiBLn)
        {
			setIt($lstJmBudget[$listBulan],0);
            $tab.="<td  align=right>".number_format($lstJmBudget[$listBulan],1)."</td>";
        }
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>";
        $tab.="<td>".$_SESSION['lang']['realisasi']."</td>";
        foreach($arrBln as $listBulan =>$isiBLn)
        {
			setIt($dtJmAk[$listBulan],0);
            $tab.="<td  align=right>".number_format($dtJmAk[$listBulan],1)."</td>";
        }
        $tab.="</tr>";
        $tab.="</tbody></table>";
        
        //rupiah/jam
        $tab.="<p><b>Cost / Hour  : ".$unitId.", ".$_SESSION['lang']['tahun']." : ".$thnId." <i>Dalam Ribuan (000)</i></b></p>";
		$tab.="<table cellspacing=1 cellpadding=5 border=".$brdr." class=sortable>
		<thead class=rowheader>";
        $tab.="<tr>";
        foreach($arrBln as $listBulan =>$isiBLn){
            $tab.="<th ".$bgcoloraja." colspan=2  align=center>".$isiBLn."</th>";
        }
        $tab.="</tr><tr>";
        foreach($arrBln as $listBulan =>$isiBLn){
            $tab.="<th ".$bgcoloraja."  align=center>Bgt</th>";
            $tab.="<th ".$bgcoloraja."  align=center>Act</th>";
        }
        $tab.="</tr></thead><tbody><tr class=rowcontent>";
        foreach($arrBln as $listBulan =>$isiBLn){
            @$hslBagiBudget[$listBulan]=($gtbgtrp[$listBulan]/$lstJmBudget[$listBulan]);
            @$hslBagiAktual[$listBulan]=($totAktual[$listBulan]/$dtJmAk[$listBulan]/1000);
            $tab.="<td align=right>".number_format($hslBagiBudget[$listBulan],2)."</td>";
            $tab.="<td align=right>".number_format($hslBagiAktual[$listBulan],2)."</td>";
        }
        $tab.="</tr></tbody></table>";
        
        $tab.="<p><b>".$_SESSION['lang']['jambengkel']." : ".$unitId.", ".$_SESSION['lang']['bulan']." : ".$arrBln1[intval($bulanId)]."</b></p>";
		$tab.="<table cellspacing=1 cellpadding=5 border=".$brdr." class=sortable>
		<thead class=rowheader>";
        $tab.="<tr>";
            $tab.="<th ".$bgcoloraja." align=center>".$_SESSION['lang']['nourut']."</th>";
            $tab.="<th ".$bgcoloraja." align=center>".$_SESSION['lang']['kodeabs']." ".$_SESSION['lang']['kendaraan']."</th>";
            $tab.="<th ".$bgcoloraja." align=center>".$_SESSION['lang']['nopol']."</th>";
            $tab.="<th ".$bgcoloraja." align=center>".$_SESSION['lang']['detail']."</th>";
            $tab.="<th ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggal']."</th>";
            $tab.="<th ".$bgcoloraja." width=75px align=center>".$_SESSION['lang']['downtime']."</th>";
            $tab.="<th ".$bgcoloraja." align=center colspan=16>".$_SESSION['lang']['keterangan']."</th>";
        $tab.="</tr></thead><tbody>";
        $sJam="select * from ".$dbname.".vhc_penggantianht where
            kodeorg like '".substr($unitId,0,4)."%' and tanggal like '".$bulanId."%'
                order by tanggal, kodevhc";
        $nonono=0;
        $tototaljam=0;
        $qJam=$owlPDO->query($sJam) or die(print " Gagal: ".PDOException::getMessage());
        $qJam->setFetchMode(PDO::FETCH_ASSOC);
        while($rJam=$qJam->fetch()){
            $nonono+=1;
            $rJam['periode']=isset($rJam['periode'])?$rJam['periode']:'';
            $rJam['jmlhjam']=isset($rJam['jmlhjam'])?$rJam['jmlhjam']:0;
            $bln=substr($rJam['periode'],-2,2);
            @$dtJmAk[$bln]+=$rJam['jmlhjam'];
                $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$nonono."</td>";
                    $tab.="<td>".$rJam['kodevhc']."</td>";
                    $tab.="<td>".getNopol($rJam['kodevhc'])."</td>";
                    $tab.="<td>".getNopol($rJam['kodevhc'],'d')."</td>";
                    $tab.="<td>".$rJam['tanggal']."</td>";
                    $tab.="<td align=right>".number_format($rJam['downtime'],1)."</td>";
                    $tab.="<td  colspan=16>".$rJam['kerusakan']."</td>";
                $tab.="</tr>";
                $tototaljam+=$rJam['downtime'];
        }
                $tab.="<tr class=rowcontent>";
                    $tab.="<td colspan=5 ".$bgcoloraja.">Total</td>";
                    $tab.="<td align=right ".$bgcoloraja.">".number_format($tototaljam,1)."</td>";
                    $tab.="<td  colspan=16 ".$bgcoloraja."></td>";
                $tab.="</tr>";
        $tab.="</tbody></table>";   
        
} // end of else bulan apa aja

        
switch($proses)
{
	case'preview':
	echo $tab;
	break;
        case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("Hms");
        $nop_="biayabengkel_".$dte;
         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
         gzwrite($gztralala, $tab);
         gzclose($gztralala);
         echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>";	
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
            $pdf->SetFillColor(220,220,220);
            //$pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','B',4);
            $i=0;
            $pdf->Cell(15,$height,"No.",TLR,0,'C',1);
            $pdf->Cell(38,$height,$_SESSION['lang']['noakun'],TLR,0,'C',1);
            $pdf->Cell(70,$height,$_SESSION['lang']['noakun'],TLR,0,'C',1);
            foreach($arrBln as $listBulan =>$isiBLn)
            {
               if($listBulan!=12)
               {
                $pdf->Cell(56,$height,$isiBLn,TLR,0,'C',1);
               }
               else
               {
                   $pdf->Cell(56,$height,$isiBLn,TLR,1,'C',1);
               }
            }
            $pdf->Cell(15,$height," ",BLR,0,'C',1);
            $pdf->Cell(38,$height," ",BLR,0,'C',1);
            $pdf->Cell(70,$height," ",BLR,0,'C',1);
             foreach($arrBln as $listBulan =>$isiBLn)
            {
               if($listBulan!=12)
               {
                $pdf->Cell(28,$height,"Budget",TBLR,0,'C',1);
                $pdf->Cell(28,$height,"Aktual",TBLR,0,'C',1);
               }
               else
               {
                $pdf->Cell(28,$height,"Budget",TBLR,0,'C',1);
                $pdf->Cell(28,$height,"Aktual",TBLR,1,'C',1);
               }
            }
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',3.2);
            foreach($lstAkun as $dtNoakun)
            {
                    $no3+=1;
                    $pdf->Cell(15,$height,$no3,TBLR,0,'C',1);
                    $pdf->Cell(38,$height,$dtNoakun,TBLR,0,'L',1);
                    $pdf->Cell(70,$height,$dtNamAkun[$dtNoakun],TBLR,0,'L',1);
                   
                    foreach($arrBln as $listBulan =>$isiBLn)
                    {
                        if($listBulan!=12)
                        {
                        $pdf->Cell(28,$height,number_format($dtRupBudget[$dtNoakun][$listBulan],2),TBLR,0,'R',1);
                        $pdf->Cell(28,$height,number_format($dtRupAktual[$dtNoakun][$listBulan],2),TBLR,0,'R',1);
                        }
                        else
                        {
                        $pdf->Cell(28,$height,number_format($dtRupBudget[$dtNoakun][$listBulan],2),TBLR,0,'R',1);
                        $pdf->Cell(28,$height,number_format($dtRupAktual[$dtNoakun][$listBulan],2),TBLR,1,'R',1);
                        }
                        $totBudget[$listBulan]+=$dtRupBudget[$dtNoakun][$listBulan];
                        $totAktual[$listBulan]+=$dtRupAktual[$dtNoakun][$listBulan];
                    }
                    $tab.="</tr>";
            }

            $pdf->Output();
            break;
	
            
	
	default:
	break;
}
      
?>