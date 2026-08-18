<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses  = checkPostGet('proses','');
$kdUnit  = checkPostGet('kdUnit','');
$kdBatch = checkPostGet('kdBatch','');
$optnmSup= makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optNm   = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$tglSkrng= date('Y-m-d');
$thnSkrng= date('Y');
$total   =0;
switch($proses){
    case'preview':
        if($kdUnit==''){
            exit("Error: Unit code required");
        }
    break;

    default:
    break;
}

$pt1 = checkPostGet('pt1','');
$kebun1 = checkPostGet('kebun1','');
$tanggal1 = checkPostGet('tanggal1','');

if($kebun1=='all')$kebun1='Seluruhnya';
if($proses=='excel1'){
    $hider=$_SESSION['lang']['laporanStockBIbit']."<br>PT: ".$pt1."<br>".$_SESSION['lang']['kebun'].": ".$kebun1."<br>".$_SESSION['lang']['sampai'].": ".$tanggal1;
    $bgcoloz="bgcolor=#dedede";
    $boder=1;
}else{
    $hider="";
    $bgcoloz="width=65px";
    $boder=0;
}
if($kebun1=='Seluruhnya')$kebun1='';

$qwe=explode("-",$tanggal1);
if(count($qwe)>2) $tanggal1=$qwe[2]."-".$qwe[1]."-".$qwe[0];

function getMonths($start, $end) {
    $startqwe=explode("-",$start);
    $endqwe=explode("-",$end);

    $startMonth = $startqwe[1];
    $startYear = $startqwe[0];

    $endMonth = $endqwe[1];
    $endYear = $endqwe[0];

    return ($endYear - $startYear) * 12 + ($endMonth - $startMonth);
}   

$stab=$hider;
$stab.="<table cellpadding=5 cellspacing=1 border=".$boder." class=sortable>
<thead>
<tr class=rowheader>
    <th align=center ".$bgcoloz.">Batch</th>
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['kodeorg']."</th>   
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['namaorganisasi']."</th>   
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['tgltanam']."</th>   
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['jenisbibit']."</th>
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['diterima']."</th>
    <th align=center ".$bgcoloz." width=70px>Seleksi Awal (".$_SESSION['lang']['afkirbibit'].")</th>
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['ditanam']."</th>
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['afkirbibit']." PN</th>
    <th align=center ".$bgcoloz.">Doubletone PN</th>
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['pindahpnmn']."</th>
    <th align=center ".$bgcoloz.">PN ".$_SESSION['lang']['stock']."</th>
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['afkirbibit']." MN</th>
    <th align=center ".$bgcoloz.">Total ".$_SESSION['lang']['afkirbibit']."</th>
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['afkirbibit']." (%)</th>
    <th align=center ".$bgcoloz.">Doubletone MN</th>
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['pengiriman']."</th>
    <th align=center ".$bgcoloz.">MN ".$_SESSION['lang']['stock']."</th>
    <th align=center ".$bgcoloz.">Grand Total</th>
    <th align=center ".$bgcoloz.">".$_SESSION['lang']['umur']."</th>
</tr>
</thead><tbody>";
$stab.="<tr>
    <th align=center ".$bgcoloz."><font size=1 color=blue>a</font></th>
    <th align=center ".$bgcoloz."></th>   
    <th align=center ".$bgcoloz."></th>   
    <th align=center ".$bgcoloz."></th>   
    <th align=center ".$bgcoloz."></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>b</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>c</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>d=(b-c)</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>e</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>x</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>f</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>g=(d+x-e-f)</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>h</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>i=(e+h)</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>j=(i/d*100)</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>k</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>l</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>m=(f-h-l)</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>n=(g+m)</font></th>
    <th align=center ".$bgcoloz."><font size=1 color=blue>o</font></th>
</tr>";

$sData="select distinct b.kodeorg, a.*,
    COALESCE(ROUND(DATEDIFF('".$tanggal1."',a.tanggaltanam)/365.25,2),0)*12 as umurbulan
    from ".$dbname.".bibitan_batch a
    left join ".$dbname.".bibitan_mutasi b on a.batch = b.batch
    where substr(b.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$pt1."') 
        and substr(b.kodeorg,1,4) like '".$kebun1."%' and a.tanggal<='".$tanggal1."' and post=1
           
    ";
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qData->fetch())
{
    $batches[$rData['batch']]=$rData['batch'];
    $dzArr[$rData['batch']]['batch']=$rData['batch'];
    $dzArr[$rData['batch']]['tanggaltanam']=$rData['tanggaltanam'];
    $dzArr[$rData['batch']]['jenisbibit']=$rData['jenisbibit'];
    //$dzArr[$rData['batch']]['kecambahterima']=$rData['jumlahterima']+$rData['jumlahafkir'];
    $dzArr[$rData['batch']]['kecambahterima']=$rData['jumlahterima'];
    $dzArr[$rData['batch']]['seleksiawal']=$rData['jumlahafkir'];
//    $dzArr[$rData['batch']]['kecambahtanam']=$rData['jumlahterima'];
    $dzArr[$rData['batch']]['umurbibit']=$rData['umurbulan'];
//    $dzArr[$rData['batch']]['umurbibit']=getMonths($rData['tanggaltanam'],$tanggal1);
}

$sData = "select * from ".$dbname.".bibitan_mutasi where substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$pt1."') and substr(kodeorg,1,4) like '".$kebun1."%' and tanggal<='".$tanggal1."' and post='1'";
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qData->fetch()){
    $batches[$rData['batch']]=$rData['batch'];
    $dzArr[$rData['batch']]['batch']=$rData['batch'];
    $dzArr[$rData['batch']]['kodeorg']=$rData['kodeorg'];
	setIt($dzArr[$rData['batch']]['kecambahtanam'],0);
	setIt($dzArr[$rData['batch']]['seleksibibitpn'],0);
	setIt($dzArr[$rData['batch']]['seleksibibitmn'],0);
	setIt($dzArr[$rData['batch']]['pindahbibitpnmn'],0);
	setIt($dzArr[$rData['batch']]['bibitdoubletone'],0);
	setIt($dzArr[$rData['batch']]['bibitdoubletonemn'],0);
	setIt($dzArr[$rData['batch']]['kirimbibit'],0);
    if($rData['kodetransaksi']=='TMB'){
        $dzArr[$rData['batch']]['kecambahtanam']+=$rData['jumlah'];    
    }
    if($rData['kodetransaksi']=='TPB'){
        if(substr($rData['kodeorg'],6,2)=='PN')
            $dzArr[$rData['batch']]['kecambahtanam']+=$rData['jumlah'];    
    }
    if($rData['kodetransaksi']=='AFB'){
        if(substr($rData['kodeorg'],6,2)=='PN')
            $dzArr[$rData['batch']]['seleksibibitpn']+=$rData['jumlah'];    
        if(substr($rData['kodeorg'],6,2)=='MN')
            $dzArr[$rData['batch']]['seleksibibitmn']+=$rData['jumlah'];    
    }
//    if($rData['kodetransaksi']=='TPB'){
//        if(substr($rData['kodeorg'],6,2)=='PN')
//            $dzArr[$rData['batch']]['pindahbibitpnmn']+=$rData['jumlah'];    
//    }

    if($rData['kodetransaksi']=='TMB'){
        if(substr($rData['kodeorg'],6,2)=='MN')
            $dzArr[$rData['batch']]['pindahbibitpnmn']+=$rData['jumlah']*-1;    
    }
    if($rData['kodetransaksi']=='DBT'){
		if(strpos($rData['kodeorg'],'MN')!=''){
			$dzArr[$rData['batch']]['bibitdoubletonemn']+=$rData['jumlah'];    
		}else{			
			$dzArr[$rData['batch']]['bibitdoubletone']+=$rData['jumlah'];    
		}
    }
    if($rData['kodetransaksi']=='PNB'){
        $dzArr[$rData['batch']]['kirimbibit']+=$rData['jumlah'];    
    }
}

// echo "<pre>";
// print_r($dzArr);

setIt($dzTot['kecambahterima'],0);
setIt($dzTot['seleksiawal'],0);
setIt($dzTot['kecambahtanam'],0);
setIt($dzTot['seleksibibitpn'],0);
setIt($dzTot['pindahbibitpnmn'],0);
setIt($dzTot['saldobibitpn'],0);
setIt($dzTot['seleksibibitmn'],0);
setIt($dzTot['totalseleksi'],0);
setIt($dzTot['bibitdoubletone'],0);
setIt($dzTot['bibitdoubletonemn'],0);
setIt($dzTot['kirimbibit'],0);
setIt($dzTot['saldobibitmn'],0);
setIt($dzTot['saldobibit'],0);
if(!empty($batches))foreach($batches as $bat){
    $stab.="<tr class=rowcontent>";
    $stab.="<td align=center>".$dzArr[$bat]['batch']."</td>";
    $stab.="<td align=center>".$dzArr[$bat]['kodeorg']."</td>";
    $stab.="<td align=center>".getNamaOrg($dzArr[$bat]['kodeorg'])."</td>";
    $stab.="<td align=center nowrap>".$dzArr[$bat]['tanggaltanam']."</td>";
    $stab.="<td align=left>".$dzArr[$bat]['jenisbibit']."</td>";
    $stab.="<td align=right>".number_format($dzArr[$bat]['kecambahterima'])."</td>";//b
    $stab.="<td align=right>".number_format($dzArr[$bat]['seleksiawal'])."</td>";//c
    $stab.="<td align=right>".number_format($dzArr[$bat]['kecambahtanam'])."</td>";//d=b-c
    $stab.="<td align=right>".number_format($dzArr[$bat]['seleksibibitpn']*-1)."</td>";//e
    $stab.="<td align=right>".number_format($dzArr[$bat]['bibitdoubletone'])."</td>";//x
    $stab.="<td align=right>".number_format($dzArr[$bat]['pindahbibitpnmn']*-1)."</td>";//f
    $saldobibitpn=$dzArr[$bat]['kecambahtanam']+$dzArr[$bat]['seleksibibitpn']+$dzArr[$bat]['pindahbibitpnmn']+$dzArr[$bat]['bibitdoubletone'];
    $stab.="<td align=right>".number_format($saldobibitpn)."</td>";//g=d-e-f
    $stab.="<td align=right>".number_format($dzArr[$bat]['seleksibibitmn']*-1)."</td>";//h
    $totalseleksi=$dzArr[$bat]['seleksibibitpn']+$dzArr[$bat]['seleksibibitmn'];
    $stab.="<td align=right>".number_format($totalseleksi*-1)."</td>";//i=e+h
    @$persenseleksi=$totalseleksi*-1/$dzArr[$bat]['kecambahtanam']*100;
    $stab.="<td align=right>".number_format($persenseleksi,2)."</td>";//j=i/d*100
    $stab.="<td align=right>".number_format($dzArr[$bat]['bibitdoubletonemn'])."</td>";//k
    $stab.="<td align=right>".number_format($dzArr[$bat]['kirimbibit']*-1)."</td>";//l
    $saldobibitmn=($dzArr[$bat]['pindahbibitpnmn']*-1)+$dzArr[$bat]['seleksibibitmn']+$dzArr[$bat]['kirimbibit']+$dzArr[$bat]['bibitdoubletonemn'];
    $stab.="<td align=right>".number_format($saldobibitmn)."</td>";//m=f-h+k-l
    $saldobibit=$saldobibitpn+$saldobibitmn;
    $stab.="<td align=right>".number_format($saldobibit)."</td>";//n=g+m
    $stab.="<td align=right>".number_format($dzArr[$bat]['umurbibit'],2)."</td>";
    $stab.="</tr>";

    $dzTot['kecambahterima']+=$dzArr[$bat]['kecambahterima'];
    $dzTot['seleksiawal']+=$dzArr[$bat]['seleksiawal'];
    $dzTot['kecambahtanam']+=$dzArr[$bat]['kecambahtanam'];
    $dzTot['seleksibibitpn']+=$dzArr[$bat]['seleksibibitpn'];
    $dzTot['pindahbibitpnmn']+=$dzArr[$bat]['pindahbibitpnmn'];
    $dzTot['saldobibitpn']+=$saldobibitpn;
    $dzTot['seleksibibitmn']+=$dzArr[$bat]['seleksibibitmn'];
    $dzTot['totalseleksi']+=$totalseleksi;
    $dzTot['bibitdoubletone']+=$dzArr[$bat]['bibitdoubletone'];
    $dzTot['bibitdoubletonemn']+=$dzArr[$bat]['bibitdoubletonemn'];
    $dzTot['kirimbibit']+=$dzArr[$bat]['kirimbibit'];
    $dzTot['saldobibitmn']+=$saldobibitmn;
    $dzTot['saldobibit']+=$saldobibit;
}
$stab.="<tr class=rowcontent>";
$stab.="<td align=center colspan=5 ".$bgcoloz.">Total</td>";
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['kecambahterima'])."</td>";//b
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['seleksiawal'])."</td>";//c
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['kecambahtanam'])."</td>";//d=b-c
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['seleksibibitpn']*-1)."</td>";//e
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['bibitdoubletone'])."</td>";//k
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['pindahbibitpnmn']*-1)."</td>";//f
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['saldobibitpn'])."</td>";//g=d-e-f
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['seleksibibitmn']*-1)."</td>";//h
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['totalseleksi']*-1)."</td>";//i=e+h
@$persenseleksiTot=$dzTot['totalseleksi']*-1/$dzTot['kecambahtanam']*100;
$stab.="<td align=right ".$bgcoloz.">".number_format($persenseleksiTot,2)."</td>";//j=i/d*100
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['bibitdoubletonemn'])."</td>";//k
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['kirimbibit']*-1)."</td>";//l
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['saldobibitmn'])."</td>";//m=f-h+k-l
$stab.="<td align=right ".$bgcoloz.">".number_format($dzTot['saldobibit'])."</td>";//n=g+m
$stab.="<td ".$bgcoloz."></td>";
$stab.="</tr>";

$stab.="</tbody></table>";

switch($proses)
{
    case'getkebun':
        $optkebun1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        //$optkebun1.="<option value='all'>".$_SESSION['lang']['all']."</option>";
        $sData="select distinct kodeorganisasi,namaorganisasi 
          from ".$dbname.".bibitan_mutasi c inner join ".$dbname.".organisasi a on left(c.kodeorg,4)=a.kodeorganisasi
          where induk = '".$pt1."' and tipe='KEBUN' order by namaorganisasi asc";
          /*select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi 
            where induk = '".$pt1."' and tipe = 'kebun'
            order by namaorganisasi*/
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
        while($rData=$qData->fetch())
        {
            $optkebun1.="<option value=".$rData['kodeorganisasi'].">".$rData['kodeorganisasi']." - ".$rData['namaorganisasi']."</option>";
        }
        echo $optkebun1;
    break;    
    case'preview1':
        echo $stab;
    break;
    case'excel1':
        $stab.="Print Time:".date('Y-m-d H:i:s')." By:".$_SESSION['empl']['name'];	
        $nop_="RekapStokBibit_".$pt1."_".$kebun1."_sd_".$tanggal1;
        if(strlen($stab)>0)
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
            if(!fwrite($handle,$stab))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can not convert to excel format');
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
        case'preview':
          $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td align=center >".substr($_SESSION['lang']['nomor'],0,2)."</td>
            <td align=center >".$_SESSION['lang']['batch']."</td>
            <td align=center >".$_SESSION['lang']['kodeorg']."</td>
            <td align=center >".$_SESSION['lang']['saldo']."</td>
            <td align=center >".$_SESSION['lang']['jenisbibit']."</td>
            <td align=center >".$_SESSION['lang']['tgltanam']."</td>   
            <td align=center >".$_SESSION['lang']['umur']." ".substr($_SESSION['lang']['afkirbibit'],5)."</td>
            </tr>
            </thead><tbody id=containDataStock>";
            if($kdUnit!='')
            {
                $where="  kodeorg like '%".$kdUnit."%'";
            }
            if($kdBatch!='')
            {
                $where.=" and batch='".$kdBatch."'";
            }
                $sData="select distinct batch,kodeorg,sum(jumlah) as jumlah from ".$dbname.".bibitan_mutasi where ".$where." and post = 1 group by batch,kodeorg order by kodeorg desc ";
               // exit("error".$sData);
				$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
				$qData->setFetchMode(PDO::FETCH_ASSOC);
				$total=0;
                while($rData=$qData->fetch())
                {
                    $data='';
                    $sDatabatch="select distinct tanggaltanam,supplerid,jenisbibit,tanggalproduksi from ".$dbname.".bibitan_batch where batch='".$rData['batch']."' ";
					$qDataBatch=$owlPDO->query($sDatabatch) or die(print " Gagal: ".PDOException::getMessage());
					$qDataBatch->setFetchMode(PDO::FETCH_ASSOC);
                    $thnData=substr($rDataBatch['tanggaltanam'],0,4);
                    $starttime=strtotime($rDataBatch['tanggaltanam']);//time();// tanggal sekarang
                    $endtime=time();//tanggal pembuatan dokumen
                    /*
                    $timediffSecond = abs($endtime-$starttime);
                    $base_year = min(date("Y", $thnData), date("Y", $thnSkrng));
                    $diff = mktime(0, 0, $timediffSecond, 1, 1, $base_year);
                    $jmlHari=date("j", $diff) - 1;
                    */

                    $jmlHari=($endtime-$starttime)/(60*60*24*30);

                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center >".$no."</td>";
                    $tab.="<td>".$rData['batch']."</td>";
                    $tab.="<td>".$optNm[$rData['kodeorg']]."</td>";
                    $tab.="<td align=right>".number_format($rData['jumlah'],0)."</td>";
                    $tab.="<td>".$rDataBatch['jenisbibit']."</td>";
                    $tab.="<td>".tanggalnormal($rDataBatch['tanggaltanam'])."</td>";
                    $tab.="<td align=right>".number_format($jmlHari,2)."</td>";
                    $tab.="</tr>";
                    $total+=$rData['jumlah'];
                }
                $tab.="<tr class=rowcontent><td colspan=3>".$_SESSION['lang']['total']."</td>";
                $tab.="<td align=right>".number_format($total)."</td><td colspan=3></td></tr>";
                $tab.="</tbody></table>";
                echo $tab;
        break;
        case'pdf':
		
		if($kdUnit=='')
		{
			echo "warning : Unit harus dipilih";
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
                global $kdUnit;
                global $kdBatch;
                global $rData;
                global $optNm;
				
				
				// # Alamat & No Telp
				// $optPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".substr($kdUnit,0,4)."'");
                // $query = selectQuery($dbname,'organisasi','alamat,telepon,namaorganisasi',"kodeorganisasi='".$optPt[$kdUnit]."'");
                // $orgData = fetchData($query);
				
				$arrHead = setheadreport(substr($kdUnit,0,4));
				
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

                $this->SetFont('Arial','B',12);
                        //	$this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanKendAb'],'',0,'L');
                        //	$this->Ln();
                                $this->SetFont('Arial','',8);

                                        $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['unit'],'',0,'L');
                                        $this->Cell(5,$height,':','',0,'L');
                                        $this->Cell(45/100*$width,$height,$optNm[$kdUnit],'',0,'L');
                                        $this->Ln();
                                        if($kdBatch=='')
                                        {
                                            $kdBatchdt=$_SESSION['lang']['all'];
                                        }
                                        else
                                        {
                                            $kdBatchdt=$kdBatch;
                                        }
                                        $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['batch'],'',0,'L');
                                        $this->Cell(5,$height,':','',0,'L');
                                        $this->Cell(45/100*$width,$height,$kdBatchdt,'',0,'L');
                                        $this->Ln();					



                $this->SetFont('Arial','U',12);
                $this->Cell($width,$height, $_SESSION['lang']['laporanStockBIbit'],0,1,'C');	
                $this->Ln();	

                $this->SetFont('Arial','B',7);	
                $this->SetFillColor(220,220,220);
                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['batch'],1,0,'C',1);	
                $this->Cell(17/100*$width,$height,$_SESSION['lang']['kodeorg'],1,0,'C',1);		
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['saldo'],1,0,'C',1);		
                $this->Cell(11/100*$width,$height,$_SESSION['lang']['jenisbibit'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['tgltanam']." ".substr($_SESSION['lang']['afkirbibit'],5),1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['umur'],1,1,'C',1);	

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
				$where="";
                if($kdBatch!='')
                {
                    $where=" and batch='".$kdBatch."'";
                }
                $sData="select distinct batch,kodeorg,sum(jumlah) as jumlah from ".$dbname.".bibitan_mutasi where kodeorg like '%".$kdUnit."%'  ".$where." and post = 1 group by batch,kodeorg order by tanggal desc ";
				
				$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
				$qData->setFetchMode(PDO::FETCH_ASSOC);
                while($rData=$qData->fetch())
                {
                    $data='';
                    $sDatabatch="select distinct tanggaltanam,supplerid,jenisbibit,tanggalproduksi from ".$dbname.".bibitan_batch where batch='".$rData['batch']."' ";
					$qDataBatch=$owlPDO->query($sDatabatch) or die(print " Gagal: ".PDOException::getMessage());
					$qDataBatch->setFetchMode(PDO::FETCH_ASSOC);
                    $rDataBatch=$qDataBatch->fetch();
                    $thnData=substr($rDataBatch['tanggaltanam'],0,4);
                    $starttime=strtotime($rDataBatch['tanggaltanam']);//time();// tanggal sekarang
                    $endtime=strtotime($tglSkrng);//tanggal pembuatan dokumen
                    $timediffSecond = abs($endtime-$starttime);
                    $base_year = min(date("Y", $thnData), date("Y", $thnSkrng));
                    $diff = mktime(0, 0, $timediffSecond, 1, 1, $base_year);
                    $jmlHari=date("j", $diff) - 1;
                    $no+=1;
                        $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);		
                        $pdf->Cell(8/100*$width,$height,$rData['batch'],1,0,'C',1);		
                        $pdf->Cell(17/100*$width,$height,$optNm[$rData['kodeorg']],1,0,'C',1);		
                        $pdf->Cell(8/100*$width,$height,number_format($rData['jumlah'],0),1,0,'R',1);
                        $pdf->Cell(11/100*$width,$height,$rDataBatch['jenisbibit'],1,0,'C',1);
                        $pdf->Cell(8/100*$width,$height,tanggalnormal($rDataBatch['tanggaltanam']),1,0,'C',1);	
                        $pdf->Cell(8/100*$width,$height,$jmlHari,1,1,'C',1);
                        $total+=$rData['jumlah'];
                }
                $pdf->Cell(28/100*$width,$height,$_SESSION['lang']['total'],1,0,'C',1);
                $pdf->Cell(8/100*$width,$height,number_format($total),1,0,'R',1);
                $pdf->Cell(27/100*$width,$height,"",1,1,'R',1);

        $pdf->Output();
        break;
        case'excel':
           $tab="
            <table>
            <tr><td colspan=7 align=center>".$_SESSION['lang']['laporanStockBIbit']."</td></tr>
            <tr><td colspan=7></td><td></td></tr>
            </table>
            <table cellpadding=1 cellspacing=1 border=1 class=sortable>
            <thead>
            <tr class=rowheader>
            <td bgcolor=#DEDEDE align=center>".substr($_SESSION['lang']['nomor'],0,2)."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['batch']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodeorg']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldo']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jenisbibit']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tgltanam']."</td>   
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['umur']." ".substr($_SESSION['lang']['afkirbibit'],5)."</td>
            </tr>
            </thead><tbody id=containDataStock>";
			$where = "";
            if($kdBatch!='')
            {
                $where=" and batch='".$kdBatch."'";
            }
                $sData="select distinct batch,kodeorg,sum(jumlah) as jumlah from ".$dbname.".bibitan_mutasi where kodeorg like '%".$kdUnit."%'  ".$where." and post = 1 group by batch,kodeorg order by tanggal desc ";
				$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
				$qData->setFetchMode(PDO::FETCH_ASSOC);
                while($rData=$qData->fetch())
                {
                    $data='';
                    $sDatabatch="select distinct tanggaltanam,supplerid,jenisbibit,tanggalproduksi from ".$dbname.".bibitan_batch where batch='".$rData['batch']."' ";
					$qDataBatch=$owlPDO->query($sDatabatch) or die(print " Gagal: ".PDOException::getMessage());
					$qDataBatch->setFetchMode(PDO::FETCH_ASSOC);
                    $rDataBatch=$qDataBatch->fetch();
                    $thnData=substr($rDataBatch['tanggaltanam'],0,4);
                    $starttime=strtotime($rDataBatch['tanggaltanam']);//time();// tanggal sekarang
                    $endtime=strtotime($tglSkrng);//tanggal pembuatan dokumen
                    $timediffSecond = abs($endtime-$starttime);
                    $base_year = min(date("Y", $thnData), date("Y", $thnSkrng));
                    $diff = mktime(0, 0, $timediffSecond, 1, 1, $base_year);
                    $jmlHari=date("j", $diff) - 1;
                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td>".$no."</td>";
                    $tab.="<td>".$rData['batch']."</td>";
                    $tab.="<td>".$optNm[$rData['kodeorg']]."</td>";
                    $tab.="<td align=right>".number_format($rData['jumlah'],0)."</td>";
                    $tab.="<td>".$rDataBatch['jenisbibit']."</td>";
                    $tab.="<td>".$rDataBatch['tanggaltanam']."</td>";
                    $tab.="<td align=right>".$jmlHari."</td>";
                    $tab.="</tr>";
                    $total+=$rData['jumlah'];
                }
                 $tab.="<tr class=rowcontent><td colspan=3>".$_SESSION['lang']['total']."</td>";
                $tab.="<td align=right>".number_format($total)."</td><td colspan=3></td></tr>";
                $tab.="</tbody></table>";



                        //echo "warning:".$strx;
                        //=================================================
                $tab.="Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	

                        $nop_="laporanStock_".$kdUnit;
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
                       // closedir($handle);
                        }
        break;

        default:
        break;
}

?>