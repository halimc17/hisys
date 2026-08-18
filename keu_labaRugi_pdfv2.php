<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/dzlib.php'); 

	$pt=$_GET['pt'];
	$unit=$_GET['gudang'];
	$periode=$_GET['periode'];
	$periode1=$_GET['periode1'];
	$revisi=$_GET['revisi'];
    $tplData=    isset($_GET['tplData'])? $_GET['tplData']: '';
    $tplData2=    isset($_GET['tplData2'])? $_GET['tplData2']: '';
     $optNmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namapt=strtoupper($bar->namaorganisasi);
}
#++++++++++++++++++++++++++++++++++++++++++
$kodelaporan='LABARUGIV2';

$periodesaldo=str_replace("-", "", $periode);

#lalu
if($periode1=='akhir')$periodPRF=substr($periodesaldo,0,4)."-01"; else $periodPRF=$tahunlalu."-".$bulan;
if($periode1=='akhir')$periodPRF2=substr($periodesaldo,0,4)."01"; else $periodPRF2=$tahunlalu.$bulan;
if($periode1=='akhir')$kolomPRF="awal01"; else $kolomPRF="awal".$bulan; #"awal".substr($periodesaldo,4,2);

#sekarang
$t=mktime(0,0,0,substr($periodesaldo,4,2)+1,15,substr($periodesaldo,0,4));
$periodCUR2=date('Ym',$t);
$periodCUR=substr($periodesaldo,0,4).'-'.substr($periodesaldo,4,2);
$kolomCUR="awal".date('m',$t);

#captionsekarang============================
$t=mktime(0,0,0,substr($periodesaldo,4,2),15,substr($periodesaldo,0,4));
//$captionCUR=date('M-Y',$t);
$captionCUR=numToMonth(substr($periodesaldo,4,2),"I","long")."-".substr($periodesaldo,0,4);

#captionlalu
$t=mktime(0,0,0,12,15,substr($periodesaldo,0,4)-1);
$t1=mktime(0,0,0,$bulan,15,substr($periodesaldo,0,4)-1);
//if($periode1=='akhir')$captionPRF=date('M-Y',$t); else $captionPRF=$captionPRF=date('M-Y',$t1);
if($periode1=='akhir')$captionPRF=numToMonth('12',"I","long")."-".(substr($periodesaldo,0,4)-1); 
    else $captionPRF=numToMonth(substr($periodesaldo,4,2),"I","long")."-".(substr($periodesaldo,0,4)-1); 

//echo "--".$periodPRF."==".$kolomPRF.">>".$captionPRF;
#ambil semua akun
$scek="select noakun from ".$dbname.".keu_5akun where char_length(noakun)=7";
$qcek=mysql_query($scek) or die(mysql_error($conn));
while($rCek=mysql_fetch_assoc($qcek)){
    $dafAkun[$rCek['noakun']]=$rCek['noakun'];
}
#query+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if($unit=='')
    $where=" kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
else 
    $where=" kodeorg='".$unit."'";

$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
    $dzArr[$bar->nourut]['tampil']=$bar->variableoutput;    
    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;
    }
    else{
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    $dzArr[$bar->nourut]['noakundisplay']=$bar->noakundisplay;
}       
        
#==========================create page
class PDF extends FPDF {
    function Header() {
       global $namapt;
       global $periode;
       global $periode1;
       global $revisi;
       global $unit;
       global $captionCUR;
       global $captionPRF;
       global $optNmOrg;
       global $dbname;
       global $dzArr;
       global $dtRpakhir;
       global $dtRpawal;

       $nmUnit='';
       if($unit!=''){
        $nmUnit=$optNmOrg[$unit];
       }
        
        $width = $this->w - $this->lMargin - $this->rMargin;
		$this->SetFont('Arial','B',8); 
		$this->Cell($width,3,$namapt,'',1,'R');
        $this->Cell($width,3,$nmUnit,'',1,'R');
        
        $this->SetFont('Arial','B',12);
        $this->Ln();
		$this->Cell(190,3,strtoupper("Laba Rugi"),0,1,'C');
        $this->SetFont('Arial','',8);
        $this->Ln(); 
        $this->Cell(150,3,' ','',0,'R');
        $this->Cell(15,3,$_SESSION['lang']['tanggal'],'',0,'L');
        $this->Cell(2,3,':','',0,'L');
        $this->Cell(35,3,date('d-m-Y H:i'),0,1,'L');
        $this->Cell(150,3,' ','',0,'R');
        $this->Cell(15,3,$_SESSION['lang']['page'],'',0,'L');
        $this->Cell(2,3,':','',0,'L');
        $this->Cell(35,3,$this->PageNo(),'',1,'L');
        $this->Cell(150,3,' ','',0,'R');
        $this->Cell(15,3,'User','',0,'L');
        $this->Cell(2,3,':','',0,'L');
        $this->Cell(35,3,$_SESSION['standard']['username'],'',1,'L');
        $this->SetFont('Arial','',8);					
        $this->Line(10,36,200,36);
        $this->Ln();
        $this->Cell(110,5,'','',0,'L');
        $this->Cell(30,5,$captionCUR,'B',0,'R');
        $this->Cell(30,5,$captionPRF,'B',1,'R');
        $this->Ln();
    }
 	function Footer()
	{
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',8);
	    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
	}   
}

$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut asc";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
    $dzArr[$bar->nourut]['tampil']=$bar->variableoutput;    
    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;
    }
    else{
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    if(($bar->noakundari!='')&&($bar->noakunsampai!='')){
        $sDataAkn="select noakun from ".$dbname.".keu_5akun where noakun between '".$bar->noakundari."' and '".$bar->noakunsampai."'";
        $qDataAkn=mysql_query($sDataAkn) or die(mysql_error($conn));
        while($rDataAkn=mysql_fetch_assoc($qDataAkn)){
            $dzArr[$bar->nourut][$rDataAkn['noakun']]=$rDataAkn['noakun'];
            $lstAkun[$rDataAkn['noakun']]=$rDataAkn['noakun'];
        }
    }else{
        if($bar->noakundisplay!=''){
            $fld="";
            $whr="";
            $dtAkn=explode(",",$bar->noakundisplay);
            foreach($dtAkn as $rwAkn=>$lstAkndt){
                if(strlen($lstAkndt)==7){
                    $whr="noakun='".$lstAkndt."'";
                    $fld="noakun";
                }else if(strlen($lstAkndt)==5){
                    $whr="left(noakun,5)='".$lstAkndt."'";
                    $fld="left(noakun,5) as noakun";
                }else if(strlen($lstAkndt)==4){
                    $whr="left(noakun,4)='".$lstAkndt."'";
                    $fld="left(noakun,4) as noakun";
                }else if(strlen($lstAkndt)==3){
                    $whr="left(noakun,3)='".$lstAkndt."'";
                    $fld="left(noakun,3) as noakun";
                }
                if($whr==''){
                    continue;
                }
                $scek="select distinct ".$fld." from ".$dbname.".keu_5akun where ".$whr."";
                $qcek=mysql_query($scek) or die(mysql_error($conn));
                if(mysql_num_rows($qcek)!=0){
                    while($rAkun=mysql_fetch_assoc($qcek)){
                        $dzArr[$bar->nourut][$rAkun['noakun']]=$rAkun['noakun'];  
                        
                        $lstAkun[$rAkun['noakun']]=$rAkun['noakun'];
                    }
                }
            }
        }
        
    }
    $dzArr[$bar->nourut]['noakundisplay']=$bar->noakundisplay;
}
if($revisi!=0){
    $addRev=" and revisi<='".$revisi."'";
}#nilai akunting bisa jadi akhir bulan thn lalu atau bulan tahun lalu
$st12="select sum(".$kolomPRF.") as kemarin,noakun
       from ".$dbname.".keu_saldobulanan where  noakun in ('3110400','3110600','3120100') and left(periode,4)='".$tahunlalu."' 
       and ".$where." order by noakun asc";
$res12=mysql_query($st12) or die(mysql_error($conn));   
while($ba12=mysql_fetch_assoc($res12)){
    $ba12['kemarin']=$ba12['kemarin']*-1;
    $dtRupiah[$ba12['noakun']]['jumlahlalu']+=$ba12['kemarin'];
    $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahlalu']+=$ba12['kemarin'];
    $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahlalu']+=$ba12['kemarin'];
    $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahlalu']+=$ba12['kemarin'];
}

#nilai akunting bisa jadi akhir bulan thn lalu atau bulan tahun lalu biaya tidak langsung
$st12="select sum(jumlah) as kemarin,noakun,kodeorg,right(kodeorg,1) as tipeunit
       from ".$dbname.".keu_jurnaldt_vw where noakun!='' and left(tanggal,4)='".substr($tahunlalu,0,4)."' 
       and left(tanggal,7)<='".$tahunlalu."-12' and ".$where." ".$addRev."  and noakun not in ('3110400','3110600','3120100') 
       group by noakun,kodeorg";
//echo $st12;
$res12=mysql_query($st12) or die(mysql_error($conn));        
$jlhlalu=0;
while($ba12=mysql_fetch_assoc($res12)){
    $optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$ba12['kodeorg']."'");
    $ba12['kemarin']=$ba12['kemarin']*-1;
    if(substr($ba12['noakun'],0,1)=='7'){
            if(substr($ba12['noakun'],0,3)=='715'){
                if($ba12['noakun']=='7150101'){
                    $ba12['noakun']="7140904";
                    if($optTipe[$ba12['kodeorg']]=='PABRIK'){
                        $dtRupiahMill[$ba12['noakun']]['jumlahlalu']+=$ba12['kemarin'];
                        $dtRupiahMill[substr($ba12['noakun'],0,5)]['jumlahlalu']+=$ba12['kemarin'];
                        $dtRupiahMill[substr($ba12['noakun'],0,4)]['jumlahlalu']+=$ba12['kemarin'];
                        $dtRupiahMill[substr($ba12['noakun'],0,3)]['jumlahlalu']+=$ba12['kemarin'];
                    }else if($optTipe[$ba12['kodeorg']]!='PABRIK'){
                        $dtRupiahEstate[$ba12['noakun']]['jumlahlalu']+=$ba12['kemarin'];
                        $dtRupiahEstate[substr($ba12['noakun'],0,5)]['jumlahlalu']+=$ba12['kemarin'];
                        $dtRupiahEstate[substr($ba12['noakun'],0,4)]['jumlahlalu']+=$ba12['kemarin'];
                        $dtRupiahEstate[substr($ba12['noakun'],0,3)]['jumlahlalu']+=$ba12['kemarin'];
                    }
                    continue;
                }
                $dtRupiah[$ba12['noakun']]['jumlahlalu']+=$ba12['kemarin'];
                $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahlalu']+=$ba12['kemarin'];
                $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahlalu']+=$ba12['kemarin'];
                $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahlalu']+=$ba12['kemarin'];    
            }else{
                if($optTipe[$ba12['kodeorg']]=='PABRIK'){
                    $dtRupiahMill[$ba12['noakun']]['jumlahlalu']+=$ba12['kemarin'];
                    $dtRupiahMill[substr($ba12['noakun'],0,5)]['jumlahlalu']+=$ba12['kemarin'];
                    $dtRupiahMill[substr($ba12['noakun'],0,4)]['jumlahlalu']+=$ba12['kemarin'];
                    $dtRupiahMill[substr($ba12['noakun'],0,3)]['jumlahlalu']+=$ba12['kemarin'];
                }else if($optTipe[$ba12['kodeorg']]!='PABRIK'){
                    $dtRupiahEstate[$ba12['noakun']]['jumlahlalu']+=$ba12['kemarin'];
                    $dtRupiahEstate[substr($ba12['noakun'],0,5)]['jumlahlalu']+=$ba12['kemarin'];
                    $dtRupiahEstate[substr($ba12['noakun'],0,4)]['jumlahlalu']+=$ba12['kemarin'];
                    $dtRupiahEstate[substr($ba12['noakun'],0,3)]['jumlahlalu']+=$ba12['kemarin'];
                }   
            }
    }else{
        $dtRupiah[$ba12['noakun']]['jumlahlalu']+=$ba12['kemarin'];
        $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahlalu']+=$ba12['kemarin'];
        $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahlalu']+=$ba12['kemarin'];
        $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahlalu']+=$ba12['kemarin'];
    }
}


#nilai akunting bln sekarang
$st12="select sum(".$kolomCUR.") as sekarang,noakun
       from ".$dbname.".keu_saldobulanan where noakun in ('3110400','3110600','3120100') 
       and left(periode,4)='".$tahun."' and ".$where." 
       order by noakun asc";
//echo $st12;
$res12=mysql_query($st12) or die(mysql_error($conn)); 
while($ba12=mysql_fetch_assoc($res12)){
    $ba12['sekarang']=$ba12['sekarang']*-1;
    $dtRupiah[$ba12['noakun']]['jumlahsekarang']=$ba12['sekarang'];
    $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahsekarang']=$ba12['sekarang'];
    $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahsekarang']=$ba12['sekarang'];
    $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahsekarang']=$ba12['sekarang'];
}
#nilai akunting bln sekarang biaya tidak langsung
$st12="select sum(jumlah) as sekarang,noakun,kodeorg,right(kodeorg,1) as tipeunit
       from ".$dbname.".keu_jurnaldt_vw where noakun!='' and left(tanggal,4)='".substr($periodCUR,0,4)."' 
       and left(tanggal,7)<='".$periodCUR."' and ".$where." ".$addRev."  and noakun not in ('3110400','3110600','3120100')  group by noakun,kodeorg";
//echo $st12;
$res12=mysql_query($st12) or die(mysql_error($conn)); 
$jlhsekarang=0;
while($ba12=mysql_fetch_assoc($res12)){
    $optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$ba12['kodeorg']."'");
    $ba12['sekarang']=$ba12['sekarang']*-1;
    if(substr($ba12['noakun'],0,1)=='7'){
            if(substr($ba12['noakun'],0,3)=='715'){
                if($ba12['noakun']=='7150101'){
                    $ba12['noakun']="7140904";
                    if($optTipe[$ba12['kodeorg']]=='PABRIK'){
                        $dtRupiahMill[$ba12['noakun']]['jumlahsekarang']+=$ba12['sekarang'];
                        $dtRupiahMill[substr($ba12['noakun'],0,5)]['jumlahsekarang']+=$ba12['sekarang'];
                        $dtRupiahMill[substr($ba12['noakun'],0,4)]['jumlahsekarang']+=$ba12['sekarang'];
                        $dtRupiahMill[substr($ba12['noakun'],0,3)]['jumlahsekarang']+=$ba12['sekarang'];
                    }else if($optTipe[$ba12['kodeorg']]!='PABRIK'){
                        $dtRupiahEstate[$ba12['noakun']]['jumlahsekarang']+=$ba12['sekarang'];
                        $dtRupiahEstate[substr($ba12['noakun'],0,5)]['jumlahsekarang']+=$ba12['sekarang'];
                        $dtRupiahEstate[substr($ba12['noakun'],0,4)]['jumlahsekarang']+=$ba12['sekarang'];
                        $dtRupiahEstate[substr($ba12['noakun'],0,3)]['jumlahsekarang']+=$ba12['sekarang'];
                    }
                    continue;
                }
                $dtRupiah[$ba12['noakun']]['jumlahsekarang']+=$ba12['sekarang'];
                $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahsekarang']+=$ba12['sekarang'];
                $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahsekarang']+=$ba12['sekarang'];
                $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahsekarang']+=$ba12['sekarang'];    
            }else{
                if($optTipe[$ba12['kodeorg']]=='PABRIK'){
                    $dtRupiahMill[$ba12['noakun']]['jumlahsekarang']+=$ba12['sekarang'];
                    $dtRupiahMill[substr($ba12['noakun'],0,5)]['jumlahsekarang']+=$ba12['sekarang'];
                    $dtRupiahMill[substr($ba12['noakun'],0,4)]['jumlahsekarang']+=$ba12['sekarang'];
                    $dtRupiahMill[substr($ba12['noakun'],0,3)]['jumlahsekarang']+=$ba12['sekarang'];
                }else if($optTipe[$ba12['kodeorg']]!='PABRIK'){
                    $dtRupiahEstate[$ba12['noakun']]['jumlahsekarang']+=$ba12['sekarang'];
                    $dtRupiahEstate[substr($ba12['noakun'],0,5)]['jumlahsekarang']+=$ba12['sekarang'];
                    $dtRupiahEstate[substr($ba12['noakun'],0,4)]['jumlahsekarang']+=$ba12['sekarang'];
                    $dtRupiahEstate[substr($ba12['noakun'],0,3)]['jumlahsekarang']+=$ba12['sekarang'];
                }   
            }
    }else{
        $dtRupiah[$ba12['noakun']]['jumlahsekarang']+=$ba12['sekarang'];
        $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahsekarang']+=$ba12['sekarang'];
        $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahsekarang']+=$ba12['sekarang'];
        $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahsekarang']+=$ba12['sekarang'];
    }
}

#sync rupiah dengan array berdasarkan keu_5mesinlaporandt
if(!empty($dzArr)){
    foreach($dzArr as $data){
        if(substr($data['nourut'],0,3)=='210'){
            $row210+=1;
        }
        foreach($lstAkun as $dtAkun){
            if($data[$dtAkun]!=''){
                switch($data['nourut']){
                    case'210003':
                    case'210004':
                        $dtRupiah[$data[$dtAkun]]['jumlahsekarang']=$dtRupiahEstate[$data[$dtAkun]]['jumlahsekarang'];
                        $dtRupiah[$data[$dtAkun]]['jumlahlalu']=$dtRupiahEstate[$data[$dtAkun]]['jumlahlalu'];
                    break;
                    case'210013':
                    //case'210014':
                        $dtRupiah[$data[$dtAkun]]['jumlahsekarang']=$dtRupiahMill[$data[$dtAkun]]['jumlahsekarang'];
                        $dtRupiah[$data[$dtAkun]]['jumlahlalu']=$dtRupiahMill[$data[$dtAkun]]['jumlahlalu'];
                    break;
                }
                 if($tplData=='1'){
                    if(($dtRupiah[$data[$dtAkun]]['jumlahlalu']==0)&&($dtRupiah[$data[$dtAkun]]['jumlahsekarang']==0)){
                        continue;
                    }
                 }
                    
                 $dtRupiah[$data[$dtAkun]]['jumlahlalu']=$dtRupiah[$data[$dtAkun]]['jumlahlalu'];
                 $dtRupiah[$data[$dtAkun]]['jumlahsekarang']=$dtRupiah[$data[$dtAkun]]['jumlahsekarang'];
                 $dzArr[$data['nourut']]['jumlahlalu']+=$dtRupiah[$data[$dtAkun]]['jumlahlalu'];
                 $dzArr[$data['nourut']]['jumlahsekarang']+=$dtRupiah[$data[$dtAkun]]['jumlahsekarang'];
                 $dzArr[$data['nourut'].$data[$dtAkun]]['jumlahlalu']=$dtRupiah[$data[$dtAkun]]['jumlahlalu'];
                 $dzArr[$data['nourut'].$data[$dtAkun]]['jumlahsekarang']=$dtRupiah[$data[$dtAkun]]['jumlahsekarang'];
                 $detAkun[$data['nourut']][]=$data[$dtAkun];
                 $jmlhRow[$data['nourut']]+=1;
            }
        }//foreach kedua
        if(!empty($data['noakundisplay'])){
                $dt=explode(",",$data['noakundisplay']);
                $temPdt=0;
                $temPdtLalu=0;
                switch ($data['nourut']) {
                    case '100009':
                    case '210009':
                    case '210007':
                    case '210005':
                    case '210015':
                    case '214006':
                    case '215005':
                    $rowdt=$dt[1]-$dt[0];
                    $isiAwal=$dt[0];
                    for($awlaj=$dt[0];$awlaj<$data['nourut'];$awlaj++){
                        $temPdt+=$dzArr[$awlaj]['jumlahsekarang'];
                        $temPdtLalu+=$dzArr[$awlaj]['jumlahlalu'];
                    }
                    $dzArr[$data['nourut']]['jumlahlalu']=$temPdtLalu;
                    $dzArr[$data['nourut']]['jumlahsekarang']=$temPdt;
                    break;
                    case '212004':
                    case '210011':
                        $dzArr[$data['nourut']]['jumlahlalu']=$dzArr[$dt[0]]['jumlahlalu']+$dzArr[$dt[1]]['jumlahlalu'];
                        $dzArr[$data['nourut']]['jumlahsekarang']=$dzArr[$dt[0]]['jumlahsekarang']+$dzArr[$dt[1]]['jumlahsekarang'];
                    break;
                    case '210008':
                        $dzArr[$data['nourut']]['jumlahlalu']=$dzArr[$dt[0]]['jumlahlalu']*-1;
                        $dzArr[$data['nourut']]['jumlahsekarang']=$dzArr[$dt[0]]['jumlahsekarang']*-1;
                    break;
                    // case'210011':
                    //     $dzArr[$data['nourut']]['jumlahlalu']=$dzArr[$dt[0]]['jumlahlalu'];
                    //     $dzArr[$data['nourut']]['jumlahsekarang']=$dzArr[$dt[0]]['jumlahsekarang'];
                    // break;

                }

    }
    $dzArr[$data['nourut']]['naikturun']=$dzArr[$data['nourut']]['jumlahsekarang']-$dzArr[$data['nourut']]['jumlahlalu'];
    @$dzArr[$data['nourut']]['persen']=($dzArr[$data['nourut']]['naikturun']/$dzArr[$data['nourut']]['jumlahlalu'])*100;
    }
}//if array tidak kosong
#end sync rupiah dari keu_saldobulanan dengan keu_5mesinlaporandt

$arrTampilanTotal=array("210015"=>"210015","210019"=>"210019","210023"=>"210023","212002"=>"212002","212003"=>"212003","212004"=>"212004");

$pdf=new PDF('P','mm','A4');
$pdf->AddPage();

if(!empty($dzArr))foreach($dzArr as $data){
    if($data['nourut']!=''){
        if($data['tipe']=='Header')
        {
            if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        continue;
            }
            $pdf->SetFont('Arial','B',8);
            $pdf->Cell(10,5,'','',0,'C');
            $pdf->Cell(100,5,$data['keterangan'],'',0,'L');
            $pdf->Cell(30,5,'','',0,'C');
            $pdf->Cell(30,5,'','',1,'C');         
        }
        else{
            if($data['tipe']=='Total'){   
                if(!empty($arrTampilanTotal[$data['nourut']])){
                                            $rpCpoSkrg=0;
                        $rpKerSkrg=0;
                        $rpCpoLalu=0;
                        $rpKerLalu=0;
                        switch ($data['nourut']){
                        case '210015':
                        ##BEBAN PRODUKSI MINYAK DAN INTI SAWIT
                        $ketDet1="CRUDE PALM OIL";
                        $ketDet2="KERNEL";
                        #jika data dari system hilangkan dokumentasi pada script di bawah ini
                        #start data dari pabrik_timbangan dan pabrik_produksi
                        #pengiriman skrng
                        $strdt="select kodebarang,sum(beratbersih) as jmlhKirim from ".$dbname.".pabrik_timbangan where 
                                      millcode in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$pt."')
                                      and left(tanggal,7)<='".$periode."' and left(tanggal,4)='".substr($periode,0,4)."' and kodebarang in ('40000001','40000002') group by kodebarang";
                        $qstrdt=mysql_query($strdt) or die(mysql_error($conn));
                        while($rstrdt=mysql_fetch_assoc($qstrdt)){
                            if($rstrdt['kodebarang']=='40000001'){
                                $kirimCpo=$rstrdt['jmlhKirim'];    
                            }else{
                                $kirimKer=$rstrdt['jmlhKirim'];    
                            }
                        }
                        #produksi skrng
                        
                        $strdt="select sum(oer) as jmlhProdcpo,sum(oerpk) as jmlhProdker from ".$dbname.".pabrik_produksi where 
                                      kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$pt."')
                                      and left(tanggal,7)<='".$periode."' and left(tanggal,4)='".substr($periode,0,4)."'";

                        $qstrdt=mysql_query($strdt) or die(mysql_error($conn));
                        $rstrdt=mysql_fetch_assoc($qstrdt);

                        @$rpPerkgCpoSkrg=$dzArr['100001']['jumlahsekarang']/$kirimCpo;
                        @$rpPerkgKerSkrg=$dzArr['100002']['jumlahsekarang']/$kirimKer;
                        #end data dari pabrik_timbangan dan pabrik_produksi
                        $hslProdCpoSkrg=$rstrdt['jmlhProdcpo'];
                        $hslProdKerSkrg=$rstrdt['jmlhProdker'];
                        $dtKirim['cpo']['jumlahsekarang']=$kirimCpo;
                        $dtKirim['ker']['jumlahsekarang']=$kirimKer;
                        $dtFisik['cpo']['jumlahsekarang']=$rstrdt['jmlhProdcpo'];
                        $dtFisik['ker']['jumlahsekarang']=$rstrdt['jmlhProdker'];

                        if($periode=='2014-12'){
                            $rpPerkgCpoSkrg='';
                            $rpPerkgKerSkrg='';
                            $hslProdKerSkrg='';
                            $hslProdCpoSkrg='';
                            ##start data ambil dari table keu_4hpp
                            ##jika data dari inputan table keu_4hpp
                            $strdt="select * from ".$dbname.".keu_4hpp where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$pt."')
                                    and periode='".$periode."'";
                            $qstrdt=mysql_query($strdt) or die(mysql_error($conn));
                            while($rstrdt=mysql_fetch_assoc($qstrdt)){
                                if($rstrdt['kodebarang']=='40000001'){
                                    $dtFisik['cpo']['jumlahsekarang']=$rstrdt['prodblnini'];
                                    $hslProdCpoSkrg=$rstrdt['prodblnini'];  
                                    $rpPerkgCpoSkrg=$rstrdt['rpkirim'];  
                                    $dtKirim['cpo']['jumlahsekarang']=$rstrdt['kirimblnini'];  
                                }else{
                                    $hslProdKerSkrg=$rstrdt['prodblnini'];  
                                    $dtKirim['ker']['jumlahsekarang']=$rstrdt['kirimblnini'];
                                    $rpPerkgKerSkrg=$rstrdt['rpkirim']; 
                                    $dtFisik['ker']['jumlahsekarang']=$rstrdt['prodblnini'];
                                }
                            }
                            ##end data ambil dari table keu_4hpp
                        }
                        @$totalRpKirim['cpo']['jumlahsekarang']=($dzArr['100001']['jumlahsekarang']/$dtKirim['cpo']['jumlahsekarang'])*$dtFisik['cpo']['jumlahsekarang'];
                        @$totalRpKirim['ker']['jumlahsekarang']=($dzArr['100002']['jumlahsekarang']/$dtKirim['ker']['jumlahsekarang'])*$dtFisik['ker']['jumlahsekarang'];
                        
                        $kirimCpo=0;
                        $kirimKer=0;

                        #start data dari system
                        #pengiriman lalu
                        $strdt="select kodebarang,sum(beratbersih) as jmlhKirim from ".$dbname.".pabrik_timbangan where 
                                      millcode in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$pt."')
                                      and left(tanggal,7)<='".$periodmilllalu."' and left(tanggal,4)='".substr($periodmilllalu,0,4)."' and kodebarang in ('40000001','40000002') group by kodebarang";
                        $qstrdt=mysql_query($strdt) or die(mysql_error($conn));
                        while($rstrdt=mysql_fetch_assoc($qstrdt)){
                            if($rstrdt['kodebarang']=='40000001'){
                                $kirimCpo=$rstrdt['jmlhKirim'];    
                            }else{
                                $kirimKer=$rstrdt['jmlhKirim'];    
                            }
                        }
                        #produksi lalu
                        $strdt="select sum(oer) as jmlhProdcpo,sum(oerpk) as jmlhProdker from ".$dbname.".pabrik_produksi where 
                                      kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$pt."')
                                      and left(tanggal,7)<='".$periodmilllalu."' and left(tanggal,4)='".substr($periodmilllalu,0,4)."'";
                        $qstrdt=mysql_query($strdt) or die(mysql_error($conn));
                        $rstrdt=mysql_fetch_assoc($qstrdt);

                        @$rpPerkgCpoLalu=$dzArr['100001']['jumlahlalu']/$kirimCpo;
                        @$rpPerkgKerLalu=$dzArr['100002']['jumlahlalu']/$kirimKer;
                        #end data dari system
                        $hslProdCpoLalu=$rstrdt['jmlhProdcpo'];
                        $hslProdKerLalu=$rstrdt['jmlhProdker'];
                        $dtKirim['cpo']['jumlahlalu']=$kirimCpo;
                        $dtKirim['ker']['jumlahlalu']=$kirimKer;
                        $dtFisik['cpo']['jumlahlalu']=$rstrdt['jmlhProdcpo'];
                        $dtFisik['ker']['jumlahlalu']=$rstrdt['jmlhProdker'];
                        
                        if(substr($periodmilllalu,0,4)=='2014'){
                            $rpPerkgCpoLalu='';
                            $rpPerkgKerLalu='';
                            ##start data ambil dari table keu_4hpp
                            $strdt="select * from ".$dbname.".keu_4hpp where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$pt."')
                                    and periode='".$periodmilllalu."'";
                            $qstrdt=mysql_query($strdt) or die(mysql_error($conn));
                            while($rstrdt=mysql_fetch_assoc($qstrdt)){
                                if($rstrdt['kodebarang']=='40000001'){
                                    $hslProdCpoLalu=$rstrdt['prodblnini'];  
                                    $dtKirim['cpo']['jumlahlalu']=$rstrdt['kirimblnini'];
                                    $kirimCpo=$rstrdt['kirimblnini'];
                                    $dtFisik['cpo']['jumlahlalu']=$rstrdt['prodblnini'];  
                                    $rpPerkgCpoLalu=$rstrdt['rpkirim'];  
                                }else{
                                    $hslProdKerLalu=$rstrdt['prodblnini'];  
                                    $rpPerkgKerLalu=$rstrdt['rpkirim'];  
                                    $dtKirim['ker']['jumlahlalu']=$rstrdt['kirimblnini'];
                                    $dtFisik['ker']['jumlahlalu']=$rstrdt['prodblnini'];  
                                    $kirimKer=$rstrdt['kirimblnini'];
                                }
                            }
                            ##end data ambil dari table keu_4hpp
                        }   
                        @$totalRpKirim['cpo']['jumlahlalu']=($dzArr['100001']['jumlahlalu']/$dtKirim['cpo']['jumlahlalu'])*$dtFisik['cpo']['jumlahlalu'];
                        @$totalRpKirim['ker']['jumlahlalu']=($dzArr['100002']['jumlahlalu']/$dtKirim['ker']['jumlahlalu'])*$dtFisik['ker']['jumlahlalu'];
                        //exit('warning:'.$dzArr['100002']['jumlahlalu']."/".$dtKirim['ker']['jumlahlalu']."*".$dtFisik['ker']['jumlahlalu']);
                        #(hasil produksi cpo*rupiah per kg/((hasil produksi cpo*rp per kg)+(hasil produksi ker*rp per kg)))*rupiah BEBAN PRODUKSI MINYAK DAN INTI SAWIT
                        #=(((L367*L8)/((L367*L8)+(L369*L10))*D367))
                        @$rpCpoSkrg=(($hslProdCpoSkrg*$rpPerkgCpoSkrg)/($totalRpKirim['cpo']['jumlahsekarang']+$totalRpKirim['ker']['jumlahsekarang']))*$data['jumlahsekarang'];
                        @$rpKerSkrg=(($hslProdKerSkrg*$rpPerkgKerSkrg)/($totalRpKirim['cpo']['jumlahsekarang']+$totalRpKirim['ker']['jumlahsekarang']))*$data['jumlahsekarang'];
                        @$rpCpoLalu=(($hslProdCpoLalu*$rpPerkgCpoLalu)/(($dtFisik['cpo']['jumlahlalu']*$rpPerkgCpoLalu)+($dtFisik['ker']['jumlahlalu']*$rpPerkgKerLalu)))*$data['jumlahlalu'];
                        //exit('warning'.($hslProdCpoLalu*$rpPerkgCpoLalu)."*".$totalRpKirim['cpo']['jumlahlalu']."___".$totalRpKirim['ker']['jumlahlalu']."*".$data['jumlahlalu']);
                        @$rpKerLalu=(($hslProdKerLalu*$rpPerkgKerLalu)/(($dtFisik['cpo']['jumlahlalu']*$rpPerkgCpoLalu)+($dtFisik['ker']['jumlahlalu']*$rpPerkgKerLalu)))*$data['jumlahlalu'];
                        
                        $dtRp['cpo']['jumlahsekarang']=$rpCpoSkrg;
                        $dtRp['cpo']['jumlahlalu']=$rpCpoLalu;

                        $dtRp['ker']['jumlahsekarang']=$rpKerSkrg;
                        $dtRp['ker']['jumlahlalu']=$rpKerLalu;
                        break;
                        case'210019':
                            $fskAwalCpo=0;
                            $fskAwalKer=0;
                            $rpAwalCpo=0;
                            $rpAwalKer=0;
                            $ketDet1="PERSEDIAAN AWAL CPO";
                            $ketDet2="PERSEDIAAN AWAL KERNEL";
                            if($periode=='2014-12'){
                                $perawal=$periode;
                            }else{
                                $perawal=substr($periode,0,4)."-01";
                            }
                            if(substr($periodmilllalu,0,4)=='2014'){
                                $perawallalu=$periodmilllalu;   
                            }else{
                                $perawallalu=substr($periodmilllalu,0,4)."-01";;   
                            }
                            $sAwl="select * from ".$dbname.".keu_4hpp where 
                               kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') 
                               and periode='".$perawal."' and kodebarang in ('40000001','40000002')";
                            $qAwl=mysql_query($sAwl) or die(mysql_error($conn));
                            while($rAwl=mysql_fetch_assoc($qAwl)){
                                if($rAwl['kodebarang']=='40000001'){
                                    $fskAwalCpo=$rAwl['qtyawal'];
                                    $rpAwalCpo=$rAwl['rpawal'];
                                    $adjstAwlCpo=$rAwl['adjuststock'];
                                    $rpAdjustCpo=$rAwl['adjuststockrp'];
                                }else{
                                    $fskAwalKer=$rAwl['qtyawal'];
                                    $rpAwalKer=$rAwl['rpawal'];
                                    $adjstAwlKer=$rAwl['adjuststock'];
                                    $rpAdjustKer=$rAwl['adjuststockrp'];
                                }
                            }
                            $rpCpoSkrg=($fskAwalCpo*$rpAwalCpo)*-1;
                            $rpKerSkrg=($fskAwalKer*$rpAwalKer)*-1;
                            $dtFisikAwl['cpo']['jumlahsekarang']=$fskAwalCpo;
                            $dtFisikAwl['ker']['jumlahsekarang']=$fskAwalKer;
                            $dtFisikAdjst['cpo']['jumlahsekarang']=$adjstAwlCpo;
                            $dtFisikAdjst['ker']['jumlahsekarang']=$adjstAwlKer;
                            $fskAwalCpo=0;
                            $fskAwalKer=0;
                            $rpAwalCpo=0;
                            $rpAwalCpo=0;
                            $sAwl="select * from ".$dbname.".keu_4hpp where 
                                   kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') 
                                   and periode='".$perawallalu."' and kodebarang in ('40000001','40000002')";
                            $qAwl=mysql_query($sAwl) or die(mysql_error($conn));
                            while($rAwl=mysql_fetch_assoc($qAwl)){
                                if($rAwl['kodebarang']=='40000001'){
                                    $fskAwalCpo=$rAwl['qtyawal'];
                                    $rpAwalCpo=$rAwl['rpawal'];
                                    $adjstAwlCpo=$rAwl['adjuststock'];
                                    $rpAdjustCpoLalu=$rAwl['adjuststockrp'];
                                }else{
                                    $fskAwalKer=$rAwl['qtyawal'];
                                    $rpAwalKer=$rAwl['rpawal'];
                                    $adjstAwlKer=$rAwl['adjuststock'];
                                    $rpAdjustKerLalu=$rAwl['adjuststockrp'];
                                }
                            }
                            $rpCpoLalu=($fskAwalCpo*$rpAwalCpo)*-1;
                            $rpKerLalu=($fskAwalKer*$rpAwalKer)*-1;
                            $dtFisikAwl['cpo']['jumlahlalu']=$fskAwalCpo;
                            $dtFisikAwl['ker']['jumlahlalu']=$fskAwalKer;
                            $dtFisikAdjst['cpo']['jumlahlalu']=$adjstAwlCpo;
                            $dtFisikAdjst['ker']['jumlahlalu']=$adjstAwlKer;
                            
                            $data['jumlahsekarang']=$rpCpoSkrg+$rpKerSkrg;
                            $data['jumlahlalu']=$rpCpoLalu+$rpKerLalu;
                            $data['naikturun']=$data['jumlahsekarang']-$data['jumlahlalu'];
                            @$data['persen']=($data['naikturun']/$data['jumlahlalu'])*100;
                            $dtRpawal['cpo']['jumlahsekarang']=$rpCpoSkrg;
                            $dtRpawal['cpo']['jumlahlalu']=$rpCpoLalu;

                            $dtRpawal['ker']['jumlahsekarang']=$rpKerSkrg;
                            $dtRpawal['ker']['jumlahlalu']=$rpKerLalu;
                        break;
                        case'210023':
                        //exit('warning:'.$dtRp['cpo']['jumlahlalu']."___".$dtRp['ker']['jumlahlalu']."___".$dtRp['cpo']['jumlahsekarang']."__".$dtRp['ker']['jumlahsekarang']);
                            $ketDet1="PERSEDIAAN AKHIR CPO";
                            $ketDet2="PERSEDIAAN AKHIR KERNEL";
                            $fisikAkhirCpoSkrg=$dtFisikAwl['cpo']['jumlahsekarang']+$dtFisik['cpo']['jumlahsekarang']-($dtKirim['cpo']['jumlahsekarang']+$dtFisikAdjst['cpo']['jumlahsekarang']);
                            $fisikAkhirCpoLalu=$dtFisikAwl['cpo']['jumlahlalu']+$dtFisik['cpo']['jumlahlalu']-($dtKirim['cpo']['jumlahlalu']+$dtFisikAdjst['cpo']['jumlahlalu']);
                            @$rpCpoSkrg=((($dtRp['cpo']['jumlahsekarang']+$dtRpawal['cpo']['jumlahsekarang'])*-1)/($dtFisikAwl['cpo']['jumlahsekarang']+$dtFisik['cpo']['jumlahsekarang']))*$fisikAkhirCpoSkrg;
                            //exit('warning: '.$dtRp['cpo']['jumlahsekarang']."___".$dtRpawal['cpo']['jumlahsekarang']."___".$dtFisikAwl['cpo']['jumlahsekarang']."__".$dtFisik['cpo']['jumlahsekarang']."___".$fisikAkhirCpoSkrg);
                            @$rpCpoLalu=((($dtRp['cpo']['jumlahlalu']+$dtRpawal['cpo']['jumlahlalu'])*-1)/($dtFisikAwl['cpo']['jumlahlalu']+$dtFisik['cpo']['jumlahlalu']))*$fisikAkhirCpoLalu;
                            //exit('warning: '.$dtRp['cpo']['jumlahlalu']."___".$dtRpawal['cpo']['jumlahlalu']."___".$dtFisikAwl['cpo']['jumlahlalu']."__".$dtFisik['cpo']['jumlahlalu']."___".$fisikAkhirCpoLalu);
                            @$fisikAkhirKerSkrg=$dtFisikAwl['ker']['jumlahsekarang']+$dtFisik['ker']['jumlahsekarang']-($dtKirim['ker']['jumlahsekarang']+$dtFisikAdjst['ker']['jumlahsekarang']);
                            @$fisikAkhirKerLalu=$dtFisikAwl['ker']['jumlahlalu']+$dtFisik['ker']['jumlahlalu']-($dtKirim['ker']['jumlahlalu']+$dtFisikAdjst['ker']['jumlahlalu']);
                            //exit('warning: '.$dtFisikAwl['ker']['jumlahsekarang']."___".$dtFisik['ker']['jumlahsekarang']."___".$dtKirim['ker']['jumlahsekarang']."+".$dtFisikAdjst['ker']['jumlahsekarang']);
                            @$rpKerSkrg=((($dtRp['ker']['jumlahsekarang']+$dtRpawal['ker']['jumlahsekarang'])*-1)/($dtFisikAwl['ker']['jumlahsekarang']+$dtFisik['ker']['jumlahsekarang']))*$fisikAkhirKerSkrg;
                            //exit('warning: '.$dtRp['ker']['jumlahsekarang']."___".$dtRpawal['ker']['jumlahsekarang']."___".$dtFisikAwl['ker']['jumlahsekarang']."__".$dtFisik['ker']['jumlahsekarang']."___".$fisikAkhirKerSkrg);
                            @$rpKerLalu=((($dtRp['ker']['jumlahlalu']+$dtRpawal['ker']['jumlahlalu'])*-1)/($dtFisikAwl['ker']['jumlahlalu']+$dtFisik['ker']['jumlahlalu']))*$fisikAkhirKerLalu;
                            //exit('warning: '.$dtRp['ker']['jumlahlalu']."___".$dtRpawal['ker']['jumlahlalu']."___".$dtFisikAwl['ker']['jumlahlalu']."__".$dtFisik['ker']['jumlahlalu']."___".$fisikAkhirKerLalu);
                            $data['jumlahsekarang']=$rpCpoSkrg+$rpKerSkrg+$rpAdjustCpo+$rpAdjustKer;
                            $data['jumlahlalu']=$rpCpoLalu+$rpKerLalu+$rpAdjustCpoLalu+$rpAdjustKerLalu;
                            $data['naikturun']=$data['jumlahsekarang']-$data['jumlahlalu'];
                            @$data['persen']=($data['naikturun']/$data['jumlahlalu'])*100;
                            $dtRpakhir['cpo']['jumlahsekarang']=$rpCpoSkrg+$rpAdjustCpo;
                            $dtRpakhir['cpo']['jumlahlalu']=$rpCpoLalu+$rpAdjustCpoLalu;
                            $dtRpakhir['ker']['jumlahsekarang']=$rpKerSkrg+$rpAdjustKer;
                            $dtRpakhir['ker']['jumlahlalu']=$rpKerLalu+$rpAdjustKerLalu;
                            $rpCpoLalu=$rpCpoLalu+$rpAdjustCpoLalu;
                            $rpKerLalu=$rpKerLalu+$rpAdjustKerLalu;
                            $rpCpoSkrg=$rpCpoSkrg+$rpAdjustCpo;
                            $rpKerSkrg=$rpKerSkrg+$rpAdjustKer;
                            @$rpawalcpo=($rpCpoSkrg/$fisikAkhirCpoSkrg);
                            @$rpawalker=($rpKerSkrg/$fisikAkhirKerSkrg);

//                            if($unit==''){
//                                $sKd="select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='HOLDING'";
//                                $qKd=mysql_query($sKd) or die(mysql_error($conn));
//                                $rKd=mysql_fetch_assoc($qKd);
//                                $unit=$rKd['kodeorganisasi'];
//                            }
//                            $scek="delete from ".$dbname.".keu_4hpp where kodeorg='".$unit."' and periode='".$periodeDpn."'";
//                            if(mysql_query($scek)){
//                                if($rpawalker==''){
//                                    $rpawalker=0;
//                                }
//                                if($rpawalcpo==''){
//                                    $rpawalcpo=0;
//                                }
//                                $sinsert="insert into ".$dbname.".keu_4hpp (kodebarang,kodeorg,periode,qtyawal,rpawal) values";
//                                $sinsert.="('40000001','".$unit."','".$periodeDpn."','".$fisikAkhirCpoSkrg."','".$rpawalcpo."'),";
//                                $sinsert.="('40000002','".$unit."','".$periodeDpn."','".$fisikAkhirKerSkrg."','".$rpawalker."');";
//                                if(!mysql_query($sinsert)){
//                                 exit('gagal :'.$sinsert."__".mysql_error($conn));
//                                }
//                            }else{
//                                exit('gagal :'.$scek."__".mysql_error($conn));
//                            }
                        break;
                    }
                    $dtCpo['naikturun']=$rpCpoSkrg-$rpCpoLalu;
                    @$dtCpo['persen']=($dtCpoSkrg['naikturun']/$rpCpoLalu)*100;
                    $dtKer['naikturun']=$rpKerSkrg-$rpKerLalu;
                    @$dtKer['persen']=($dtKerSkrg['naikturun']/$rpKerLalu)*100;


                    // $pdf->Ln();    
                    // $pdf->SetFont('Arial','B',8);
                    // $pdf->Cell(10,5,'','',0,'C');
                    // $pdf->Cell(5,5,'','',0,'L');
                    // $pdf->Cell(95,5,$data['keterangan'],'',0,'L');
                    // $pdf->Cell(30,5,kalominuskurung($data['jumlahsekarang']),'T',0,'R');
                    // $pdf->Cell(30,5,kalominuskurung($data['jumlahlalu']),'T',1,'R'); 
                    // #escape yang nilainya nol
                    // $pdf->Cell(10,5,'','',0,'C');
                    // $pdf->SetFont('Arial','',8);
                    // $pdf->Cell(10,5,'','',0,'L');
                    // $pdf->Cell(90,5,$ketDet1,'',0,'L');
                    // $pdf->Cell(30,5,kalominuskurung($rpCpoSkrg),'',0,'R');
                    // $pdf->Cell(30,5,kalominuskurung($rpCpoLalu),'',1,'R'); 
                    // $pdf->Cell(10,5,'','',0,'C');
                    // $pdf->SetFont('Arial','',8);
                    // $pdf->Cell(10,5,'','',0,'L');
                    // $pdf->Cell(90,5,$ketDet2,'',0,'L');
                    // $pdf->Cell(30,5,kalominuskurung($rpKerSkrg),'',0,'R');
                    // $pdf->Cell(30,5,kalominuskurung($rpKerLalu),'',1,'R'); 
                }else{
                    if($data['nourut']=='212005'){
                         $dt=explode(",",$data['noakundisplay']);
                         for($awlaj=$dt[0];$awlaj<$data['nourut'];$awlaj++){
                            $temPdt+=$rpBaru[$awlaj]['jumlahsekarang'];
                            $temPdtLalu+=$rpBaru[$awlaj]['jumlahlalu'];
                         }
                         $data['jumlahlalu']=$temPdtLalu;
                         $data['jumlahsekarang']=$temPdt;
                         $rpBaru[$data['nourut']]['jumlahsekarang']=$temPdt;
                         $rpBaru[$data['nourut']]['jumlahlalu']=$temPdtLalu;
                         $data['naikturun']= $data['jumlahsekarang']-$data['jumlahlalu'];
                         @$dtCpo['persen']=($dtCpoSkrg['naikturun']/$data['jumlahlalu'])*100;
                    }
                    if(($data['nourut']=='213999')||($data['nourut']=='214999')||($data['nourut']=='215999')){
                         $dt=explode(",",$data['noakundisplay']);
                         $temPdtLalu=$dzArr[$dt[0]]['jumlahlalu']+$rpBaru[$dt[1]]['jumlahlalu'];
                         $temPdt=$dzArr[$dt[0]]['jumlahsekarang']+$rpBaru[$dt[1]]['jumlahsekarang'];
                         $data['jumlahlalu']=$temPdtLalu;
                         $data['jumlahsekarang']=$temPdt;
                         $rpBaru[$data['nourut']]['jumlahsekarang']=$temPdt;
                         $rpBaru[$data['nourut']]['jumlahlalu']=$temPdtLalu;
                         $data['naikturun']= $data['jumlahsekarang']-$data['jumlahlalu'];
                         @$dtCpo['persen']=($dtCpoSkrg['naikturun']/$data['jumlahlalu'])*100;
                    }
                    if($data['nourut']=='216999'){
                         $dt=explode(",",$data['noakundisplay']);
                         $temPdtLalu=$rpBaru[$dt[1]]['jumlahlalu']-$dzArr[$dt[0]]['jumlahlalu'];
                         $temPdt=$rpBaru[$dt[1]]['jumlahsekarang']-$dzArr[$dt[0]]['jumlahsekarang'];
                         $data['jumlahlalu']=$temPdtLalu;
                         $data['jumlahsekarang']=$temPdt;
                         $rpBaru[$data['nourut']]['jumlahsekarang']=$temPdt;
                         $rpBaru[$data['nourut']]['jumlahlalu']=$temPdtLalu;
                         $data['naikturun']= $data['jumlahsekarang']-$data['jumlahlalu'];
                         @$dtCpo['persen']=($dtCpoSkrg['naikturun']/$data['jumlahlalu'])*100;
                    }
                    if($data['nourut']=='217002'){
                         $dt=explode(",",$data['noakundisplay']);
                         $temPdtLalu=$rpBaru[$dt[0]]['jumlahlalu']+$dzArr[$dt[1]]['jumlahlalu']+$rpBaru[$dt[2]]['jumlahsekarang'];
                         $temPdt=$rpBaru[$dt[0]]['jumlahsekarang']+$dzArr[$dt[1]]['jumlahsekarang']+$rpBaru[$dt[2]]['jumlahsekarang'];
                         $data['jumlahlalu']=$temPdtLalu;
                         $data['jumlahsekarang']=$temPdt;
                         $rpBaru[$data['nourut']]['jumlahsekarang']=$temPdt;
                         $rpBaru[$data['nourut']]['jumlahlalu']=$temPdtLalu;
                         $data['naikturun']= $data['jumlahsekarang']-$data['jumlahlalu'];
                         @$dtCpo['persen']=($dtCpoSkrg['naikturun']/$data['jumlahlalu'])*100;
                    }
                    if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        continue;
                    }
                    $pdf->Ln();    
                    $pdf->SetFont('Arial','B',8);
                    $pdf->Cell(10,5,'','',0,'C');
                    $pdf->Cell(5,5,'','',0,'L');
                    $pdf->Cell(95,5,$data['keterangan'],'',0,'L');
                    $pdf->Cell(30,5,kalominuskurung($data['jumlahsekarang']),'T',0,'R');
                    $pdf->Cell(30,5,kalominuskurung($data['jumlahlalu']),'T',1,'R'); 
                    $pdf->Ln();    
                }
                
            }
            else
            {
                if(!empty($arrTampilanTotal[$data['nourut']])){
                    $rpCpoSkrg=0;
                    $rpCpoLalu=0;
                    switch ($data['nourut']) {
                        case '212002':
                           $rpCpoSkrg=$dtRpakhir['cpo']['jumlahsekarang']+$dtRpawal['cpo']['jumlahsekarang']+$dtRp['cpo']['jumlahsekarang'];
                           $rpCpoLalu=$dtRpakhir['cpo']['jumlahlalu']+$dtRpawal['cpo']['jumlahlalu']+$dtRp['cpo']['jumlahlalu'];
                           $rpBaru[$data['nourut']]['jumlahlalu']=$rpCpoLalu;
                           $rpBaru[$data['nourut']]['jumlahsekarang']=$rpCpoSkrg;
                        break;
                        case '212003':
                           $rpCpoSkrg=$dtRpakhir['ker']['jumlahsekarang']+$dtRpawal['ker']['jumlahsekarang']+$dtRp['ker']['jumlahsekarang'];
                           $rpCpoLalu=$dtRpakhir['ker']['jumlahlalu']+$dtRpawal['ker']['jumlahlalu']+$dtRp['ker']['jumlahlalu'];
                           $rpBaru[$data['nourut']]['jumlahlalu']=$rpCpoLalu;
                           $rpBaru[$data['nourut']]['jumlahsekarang']=$rpCpoSkrg;
                        break;
                    }
                    $data['jumlahsekarang']=$rpBaru[$data['nourut']]['jumlahsekarang'];
                    $data['jumlahlalu']=$rpBaru[$data['nourut']]['jumlahlalu'];
                }
                if($tplData==1){
                    if(($data['jumlahsekarang']==0) and ($data['jumlahlalu']==0)){
                        continue;    
                    }
                }
                if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        continue;
                }

                    #escape yang nilainya nol
                    $pdf->Cell(10,5,'','',0,'C');
                    $pdf->SetFont('Arial','',8);
                    $pdf->Cell(10,5,'','',0,'L');
                    $pdf->Cell(90,5,$data['keterangan'],'',0,'L');
                    $pdf->Cell(30,5,kalominuskurung($data['jumlahsekarang']),'',0,'R');
                    $pdf->Cell(30,5,kalominuskurung($data['jumlahlalu']),'',1,'R'); 
            }         
        }    
        
    }
}

$pdf->Output();		
?>