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
$kodelaporan='LK-NERACAV2';

$periodesaldo=str_replace("-", "", $periode);

#lalu
if($periode1=='akhir')$periodPRF=substr($periodesaldo,0,4)."01"; else $periodPRF=$tahunlalu.$bulan;
if($periode1=='akhir')$periodPRF2=substr($periodesaldo,0,4)."-01"; else $periodPRF2=$tahunlalu."-".$bulan;
if($periode1=='akhir')$kolomPRF="awal01"; else $kolomPRF="awal".$bulan; #"awal".substr($periodesaldo,4,2);

#sekarang
$t=mktime(0,0,0,substr($periodesaldo,4,2)+1,15,substr($periodesaldo,0,4));
$periodCUR=date('Ym',$t);
$periodCUR2=substr($periodesaldo,0,4).'-'.substr($periodesaldo,4,2);
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
    $dzArr[$bar->nourut]['variablejadi']=$bar->variablejadi; // dz 20160120    
    
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
		$this->Cell(190,3,strtoupper($_SESSION['lang']['neraca']),0,1,'C');
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
            $jmlhRow[$bar->nourut]+=1;
        }
    }else{
        if($bar->noakundisplay!=''){
            $dtAkn=explode(",",$bar->noakundisplay);
        if(!empty($dtAkn))foreach($dtAkn as $rwAkn=>$lstAkn){
            if(strlen($lstAkn)==7){
                $whr="noakun='".$lstAkn."'";
                $fld="noakun";
            }else if(strlen($lstAkn)==5){
                $whr="left(noakun,5)='".$lstAkn."'";
                $fld="left(noakun,5) as noakun";
            }else if(strlen($lstAkn)==4){
                $whr="left(noakun,4)='".$lstAkn."'";
                $fld="left(noakun,4) as noakun";
            }else if(strlen($lstAkn)==3){
                $whr="left(noakun,3)='".$lstAkn."'";
                $fld="left(noakun,3) as noakun";
            }
            $scek="select distinct ".$fld." from ".$dbname.".keu_5akun where ".$whr."";
            $qcek=mysql_query($scek) or die(mysql_error($conn));
            if(mysql_num_rows($qcek)!=0){
                while($rAkun=mysql_fetch_assoc($qcek)){
                    if(($rAkun['noakun']=='12801')||($rAkun['noakun']=='12802')){
                        $rAkun['noakun']='128';
                    }
                    if($lstAkn=='22103'){
                        $rAkun['noakun']='22102';  
                    }
                    $dzArr[$bar->nourut][$rAkun['noakun']]=$rAkun['noakun'];  
                    $jmlhRow[$bar->nourut]+=1;
                    $lstAkun[$rAkun['noakun']]=$rAkun['noakun'];
                }
            }
            switch ($lstAkn) {
                case '1279':
                    $rAkun['noakun']=1279;
                    $dzArr[$bar->nourut][$rAkun['noakun']]=$rAkun['noakun'];  
                    $lstAkun[$rAkun['noakun']]=$rAkun['noakun'];
                    $jmlhRow[$bar->nourut]+=1;
                break;
            }
        }
        }
        
    }
    $dzArr[$bar->nourut]['noakundisplay']=$bar->noakundisplay;
}
#untuk ambil data piutang usaha menjadi hutang usaha jika nilainya minus
$dtNourutaja="330003";
#untuk ambil data piutang hubungan istimewa menjadi hutang hubungan istimewa jika nilainya minus (dz 20160323)
$dtNourutaja2="440002";
#sampai sini piutang usaha
$totPersediaLalu=0;
$totPersediaLaluPeng=0;

#ambil data dari keu_4hppp karena ada perbedaan antara nilai pada jurnal dengan pencatatan pengakuan timbangan
$sData="select kodebarang,(qtyawal*rpawal) as awal,lababersih from ".$dbname.".keu_4hpp where periode='".$periodPRF2."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$pt."')
        group by kodebarang";   
$qData=mysql_query($sData) or die(mysql_error($conn));
while($rData=mysql_fetch_assoc($qData)){
    if($rData['kodebarang']=='40000001'){
        $cpo=$rData['awal'];
    }else{
        $ker=$rData['awal'];
    }
    $totPersediaLaluPeng+=$rData['awal'];
    $labaBersihLalu=$rData['lababersih']*-1;
}
#sampai sini 

#nilai akunting bisa jadi akhir bulan thn lalu atau bulan tahun lalu
$st12="select sum(".$kolomPRF.") as kemarin,noakun
       from ".$dbname.".keu_saldobulanan where  noakun in ('3110400','3110600') and left(periode,4)='".$tahunlalu."' 
       and ".$where." order by noakun asc";
$res12=mysql_query($st12) or die(mysql_error($conn));   
while($ba12=mysql_fetch_assoc($res12)){
    $ba12['kemarin']=$ba12['kemarin'];
    $dtRupiah[$ba12['noakun']]['jumlahlalu']+=$ba12['kemarin'];
    $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahlalu']+=$ba12['kemarin'];
    $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahlalu']+=$ba12['kemarin'];
    $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahlalu']+=$ba12['kemarin'];
}

$st12="select sum(".$kolomPRF.") as kemarin,noakun
       from ".$dbname.".keu_saldobulanan where noakun!='' and (periode='".$periodPRF."') and ".$where." 
       and noakun not in ('3110400','3110600')  group by noakun";
$res12=mysql_query($st12) or die(mysql_error($conn));        
$jlhlalu=0;
while($ba12=mysql_fetch_assoc($res12)){
    if($dzArr['110004'][substr($ba12['noakun'],0,5)]!=''){
        if($ba12['kemarin']<0){
            $dzArr[$dtNourutaja][substr($ba12['noakun'],0,3)]=substr($ba12['noakun'],0,3);
            $dzArr[$dtNourutaja][substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
            $lstAkun[substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
            $lstAkun[$ba12['noakun']."01"]=$ba12['noakun']."01";
            $dtRupiah[$ba12['noakun']."01"]['jumlahlalu']=$ba12['kemarin'];
            $dtRupiah[substr($ba12['noakun'],0,5)."01"]['jumlahlalu']+=$ba12['kemarin'];
            continue;
        }
    }
    if($dzArr['220002'][substr($ba12['noakun'],0,5)]!=''){
        if($ba12['kemarin']<0){
            $dzArr[$dtNourutaja2][substr($ba12['noakun'],0,3)]=substr($ba12['noakun'],0,3);
            $dzArr[$dtNourutaja2][substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
            $lstAkun[substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
            $lstAkun[$ba12['noakun']."01"]=$ba12['noakun']."01";
            $dtRupiah[$ba12['noakun']."01"]['jumlahlalu']=$ba12['kemarin'];
            $dtRupiah[substr($ba12['noakun'],0,5)."01"]['jumlahlalu']+=$ba12['kemarin'];
            continue;
        }
    }
    if(substr($ba12['noakun'],0,5)=='11502'){
        $totPersediaLaluPeng+=$ba12['kemarin'];
        if($ba12['noakun']=='1150201'){
                $ba12['kemarin']=$cpo;    
        }else if($ba12['noakun']=='1150202'){
            $ba12['kemarin']=$ker;                
        }
    }
    if($ba12['noakun']=='3110700'){
        $ba12['kemarin']=$labaBersihLalu;    
    }
    $dtRupiah[$ba12['noakun']]['jumlahlalu']=$ba12['kemarin'];
    $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahlalu']+=$ba12['kemarin'];
    switch(substr($ba12['noakun'],0,4)){
        case'1261':
        $ba12['noakun']='1260';
        break;
        case'1270':
            if(substr($ba12['noakun'],-2,2)=='99'){
              $ba12['noakun']='1279';  
            }
        break;
    }
    $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahlalu']+=$ba12['kemarin'];
        #pengecualian sum data berdasarkan noakundisplay yang tersimpan
        if(substr($ba12['noakun'],0,5)=='12620'){
            if($ba12['noakun']=='1262002'){
                continue;
            }
        }
        if(substr($ba12['noakun'],0,5)=='12802'){
            if($ba12['noakun']==='1280298'){
                continue;
            }
        }
        if(substr($ba12['noakun'],0,5)=='22103'){
            $ba12['noakun']='22102';  
        }
        if(substr($ba12['noakun'],0,4)=='1279'){
              continue;
        }
    $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahlalu']+=$ba12['kemarin'];
}
$totPersediaSkrg=0;
$totPersediaSkrgPeng=0;
#ambil data dari keu_4hppp karena ada perbedaan antara nilai pada jurnal dengan pencatatan pengakuan timbangan
$sData="select kodebarang,(qtyawal*rpawal) as awal,lababersih from ".$dbname.".keu_4hpp where periode='".substr($periodCUR,0,4)."-".substr($periodCUR,4,2)."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$pt."')
        group by kodebarang";   
$qData=mysql_query($sData) or die(mysql_error($conn));
while($rData=mysql_fetch_assoc($qData)){
    if($rData['kodebarang']=='40000001'){
        $cpo=$rData['awal'];
    }else{
        $ker=$rData['awal'];
    }
    $totPersediaSkrg+=$rData['awal'];
    $labaBersihSkrg=$rData['lababersih']*-1;
}
#sampai sini keu_4hpp
if($periode=='2014-12'){
    if($pt=='PMO'){
        $sData="select kodebarang,(qtyawal*rpawal) as awal,lababersih from ".$dbname.".keu_4hpp where periode='2015-01' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$pt."')
                group by kodebarang";   
        $qData=mysql_query($sData) or die(mysql_error($conn));
        while($rData=mysql_fetch_assoc($qData)){
            if($rData['kodebarang']=='40000001'){
                $cpo=$rData['awal'];
            }else{
                $ker=$rData['awal'];
            }
            //$totPersediaSkrg+=$rData['awal'];
            $labaBersihSkrg=$rData['lababersih']*-1;
        }    
    }
        $st12="select sum(awal01) as sekarang,noakun from ".$dbname.".keu_saldobulanan where 
               noakun in ('3110400','3110600') and left(periode,4)='2014' and 
               kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') 
               order by noakun asc";
        $res12=mysql_query($st12) or die(mysql_error($conn));   
        while($ba12=mysql_fetch_assoc($res12)){
            $dtRupiah[$ba12['noakun']]['jumlahsekarang']+=$ba12['sekarang'];
            $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahsekarang']+=$ba12['sekarang'];
            $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahsekarang']+=$ba12['sekarang'];
            $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahsekarang']+=$ba12['sekarang'];
        }
        $st12="select sum(awal01) as sekarang,noakun from ".$dbname.".keu_saldobulanan where noakun!='' 
               and (periode='201501') and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') 
               and noakun not in ('3110400','3110600') group by noakun";
        $res12=mysql_query($st12) or die(mysql_error($conn));        
        $jlhlalu=0;
        while($ba12=mysql_fetch_assoc($res12)){
            if($dzArr['110004'][substr($ba12['noakun'],0,5)]!=''){
                if($ba12['sekarang']<0){
                    $dzArr[$dtNourutaja][substr($ba12['noakun'],0,3)]=substr($ba12['noakun'],0,3);
                    $dzArr[$dtNourutaja][substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
                    $lstAkun[substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
                    $lstAkun[$ba12['noakun']."01"]=$ba12['noakun']."01";
                    $dtRupiah[$ba12['noakun']."01"]['jumlahsekarang']=$ba12['sekarang'];
                    $dtRupiah[substr($ba12['noakun'],0,5)."01"]['jumlahsekarang']+=$ba12['sekarang'];
                    continue;
                }
            }
            if($dzArr['220002'][substr($ba12['noakun'],0,5)]!=''){
                if($ba12['sekarang']<0){
                    $dzArr[$dtNourutaja2][substr($ba12['noakun'],0,3)]=substr($ba12['noakun'],0,3);
                    $dzArr[$dtNourutaja2][substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
                    $lstAkun[substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
                    $lstAkun[$ba12['noakun']."01"]=$ba12['noakun']."01";
                    $dtRupiah[$ba12['noakun']."01"]['jumlahsekarang']=$ba12['sekarang'];
                    $dtRupiah[substr($ba12['noakun'],0,5)."01"]['jumlahsekarang']+=$ba12['sekarang'];
                    continue;
                }
            }            if(substr($ba12['noakun'],0,5)=='11502'){
                $totPersediaLaluPeng+=$ba12['sekarang'];
                if($ba12['noakun']=='1150201'){
                        $ba12['sekarang']=$cpo;    
                }else if($ba12['noakun']=='1150202'){
                    $ba12['sekarang']=$ker;                
                }
            }
            if($ba12['noakun']=='3110700'){
                $ba12['sekarang']=$labaBersihSkrg;    
            }
            $dtRupiah[$ba12['noakun']]['jumlahsekarang']=$ba12['sekarang'];
            $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahsekarang']+=$ba12['sekarang'];
            switch(substr($ba12['noakun'],0,4)){
                // case'1261':
                // $ba12['noakun']='1260';
                // break;
                case'1270':
                    if(substr($ba12['noakun'],-2,2)=='99'){
                      $ba12['noakun']='1279';  
                    }
                break;
            }
            $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahsekarang']+=$ba12['sekarang'];
                #pengecualian sum data berdasarkan noakundisplay yang tersimpan
                if(substr($ba12['noakun'],0,5)=='12620'){
                    if($ba12['noakun']=='1262002'){
                        continue;
                    }
                }
                if(substr($ba12['noakun'],0,5)=='12802'){
                    if($ba12['noakun']==='1280298'){
                        continue;
                    }
                }
                if(substr($ba12['noakun'],0,5)=='22103'){
                    $ba12['noakun']='22102';  
                }
                if(substr($ba12['noakun'],0,4)=='1279'){
                      continue;
                }
            $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahsekarang']+=$ba12['sekarang'];
        }
}else{
        #nilai akunting bln sekarang
        $st12="select sum(".$kolomCUR.") as sekarang,noakun
                    from ".$dbname.".keu_saldobulanan where noakun!='' and (periode='".$periodCUR."') and ".$where."  group by noakun";
        $res12=mysql_query($st12) or die(mysql_error($conn)); 
        $jlhsekarang=0;
        while($ba12=mysql_fetch_assoc($res12)){
            if($dzArr['110004'][substr($ba12['noakun'],0,5)]!=''){
                if($ba12['sekarang']<0){
                    $dzArr[$dtNourutaja][substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
                    $lstAkun[substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
                    $lstAkun[$ba12['noakun']."01"]=$ba12['noakun']."01";
                    $dtRupiah[$ba12['noakun']."01"]['jumlahsekarang']=$ba12['sekarang'];
                    $dtRupiah[substr($ba12['noakun'],0,5)."01"]['jumlahsekarang']+=$ba12['sekarang'];
                    continue;
                }
            }
            if($dzArr['220002'][substr($ba12['noakun'],0,5)]!=''){
                if($ba12['sekarang']<0){
                    $dzArr[$dtNourutaja2][substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
                    $lstAkun[substr($ba12['noakun'],0,5)."01"]=substr($ba12['noakun'],0,5)."01";
                    $lstAkun[$ba12['noakun']."01"]=$ba12['noakun']."01";
                    $dtRupiah[$ba12['noakun']."01"]['jumlahsekarang']=$ba12['sekarang'];
                    $dtRupiah[substr($ba12['noakun'],0,5)."01"]['jumlahsekarang']+=$ba12['sekarang'];
                    continue;
                }
            }
            if(substr($ba12['noakun'],0,5)=='11502'){
                $totPersediaSkrgPeng+=$ba12['sekarang'];
                if($ba12['noakun']=='1150201'){
                        $ba12['sekarang']=$cpo;    
                }else if($ba12['noakun']=='1150202'){
                    $ba12['sekarang']=$ker;                
                }
            }
            if($ba12['noakun']=='3110700'){
                $ba12['sekarang']=$labaBersihSkrg;    
            }
            
            $dtRupiah[$ba12['noakun']]['jumlahsekarang']=$ba12['sekarang'];
            $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahsekarang']+=$ba12['sekarang'];
            switch(substr($ba12['noakun'],0,4)){
                case'1261':
                    $ba12['noakun']='1260';
                break;
                case'1270':
                    if(substr($ba12['noakun'],-2,2)=='99'){
                      $ba12['noakun']='1279';  
                    }
                break;
            }
            $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahsekarang']+=$ba12['sekarang'];
                #pengecualian sum data berdasarkan noakundisplay yang tersimpan
                if(substr($ba12['noakun'],0,5)=='12620'){
                    if($ba12['noakun']=='1262002'){
                        continue;
                    }
                }
                if(substr($ba12['noakun'],0,5)=='12802'){
                    if($ba12['noakun']==='1280298'){
                        continue;
                    }
                }
                if(substr($ba12['noakun'],0,5)=='22103'){
                    $ba12['noakun']='22102';  
                }
                if(substr($ba12['noakun'],0,4)=='1279'){
                      continue;
                }
                $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahsekarang']+=$ba12['sekarang'];
        }
}


// tambahan dhyaz, untuk counter akhir tahun yang selisih di 3110600
//echo "</br>".$periode;
if( (substr($periode,5,2)=='12') and (substr($periode,0,4)>'2014') ){
//    echo "ok";
    #nilai akunting bln sekarang
    $st12="select sum(awal01) as sekarang,noakun
           from ".$dbname.".keu_saldobulanan where noakun = '3110600' and (periode='".$tahun."01') and ".$where."  group by noakun";
//        echo "</br>".$st12;
    $res12=mysql_query($st12) or die(mysql_error($conn)); 
    $jlhsekarang=0;
    while($ba12=mysql_fetch_assoc($res12)){
        //$dafAkun[$ba12['noakun']]=$ba12['noakun'];
            $dtRupiah[$ba12['noakun']]['jumlahsekarang']=$ba12['sekarang'];
            $dtRupiah[substr($ba12['noakun'],0,3)]['jumlahsekarang']+=$ba12['sekarang'];
            $dtRupiah[substr($ba12['noakun'],0,4)]['jumlahsekarang']+=$ba12['sekarang'];
            $dtRupiah[substr($ba12['noakun'],0,5)]['jumlahsekarang']+=$ba12['sekarang'];
//dz        echo "</br>".$ba12['noakun']." ".kalominuskurung($ba12['sekarang']);
    }            
}
// tambahan dhyaz 3110600

$aknA='12801';
$aknB='12802';
$lst128=array($aknA=>$aknA,$aknB=>$aknB);


if($revisi>0){ // kalo revisi > 0, ambil data dari jurnal
    $st12="select noakun, sum(jumlah) as jumlah
        from ".$dbname.".keu_jurnaldt_vw where periode between '".$periodPRF2."' 
        and '".$periodCUR2."' and ".$where."   ".$addEx." and revisi <= '".$revisi."' group by noakun";  
    $res12=mysql_query($st12);
    $jlhsekarang=0;
    while($ba12=mysql_fetch_object($res12))
    {
        if(!empty($dzArr))foreach($dzArr as $data){
            if(($ba12->noakun>=$data['noakundari'])&&($ba12->noakun<=$data['noakunsampai'])) {
				if(!isset($dzArr[$data['nourut']]['jumlahtemp'])) $dzArr[$data['nourut']]['jumlahtemp']=0;
                $dzArr[$data['nourut']]['jumlahtemp']+=$ba12->jumlah; 
                $dzArr[$data['nourut']]['jumlahsekarang']=$dzArr[$data['nourut']]['jumlahlalu']+$dzArr[$data['nourut']]['jumlahtemp'];
            } else {
				$dzArr[$data['nourut']]['jumlahsekarang']=0;
			}
        }        
    }                 
}
#sync rupiah dengan array berdasarkan keu_5mesinlaporandt
if(!empty($dzArr)){
    foreach($dzArr as $data){
        foreach($lstAkun as $dtAkun){
            if($data[$dtAkun]!=''){
                if($tplData=='1'){
                    if(($dtRupiah[$data[$dtAkun]]['jumlahlalu']==0)&&($dtRupiah[$data[$dtAkun]]['jumlahsekarang']==0)){
                        continue;
                    }   
                }
                $detAkun[$data['nourut']][]=$data[$dtAkun];
                if(($dtAkun=='12803')||($dtAkun=='12804')||($dtAkun=='12805')||($dtAkun=='12806')||($dtAkun=='12807')){
                    continue;
                }
                if(($dtAkun=='12620')||($dtAkun=='1262002')){
                    continue;
                }
                $dzArr[$data['nourut']]['jumlahlalu']+=$dtRupiah[$data[$dtAkun]]['jumlahlalu'];
                $dzArr[$data['nourut']]['jumlahsekarang']+=$dtRupiah[$data[$dtAkun]]['jumlahsekarang'];
                $dzArr[$data['nourut'].$data[$dtAkun]]['jumlahlalu']=$dtRupiah[$data[$dtAkun]]['jumlahlalu'];
                $dzArr[$data['nourut'].$data[$dtAkun]]['jumlahsekarang']=$dtRupiah[$data[$dtAkun]]['jumlahsekarang'];
            }
        }//foreach kedua
        if(!empty($data['noakundisplay'])){
                $dt=explode(",",$data['noakundisplay']);
                $temPdt=0;
                $temPdtLalu=0;
                switch ($data['nourut']) {
                    case '110009':
                    case '220008':
                    case '330010':
                    case '440007':
                    case '550006':
                    $rowdt=$dt[1]-$dt[0];
                    $isiAwal=$dt[0];
                    for($awlaj=$dt[0];$awlaj<$data['nourut'];$awlaj++){
                        $temPdt+=$dzArr[$awlaj]['jumlahsekarang'];
                        $temPdtLalu+=$dzArr[$awlaj]['jumlahlalu'];
                    }
                    $dzArr[$data['nourut']]['jumlahlalu']=$temPdtLalu;
                    $dzArr[$data['nourut']]['jumlahsekarang']=$temPdt;
                    break;
                    case'220009':
                    case'440008':
                    case'550007':
                        $dzArr[$data['nourut']]['jumlahlalu']=$dzArr[$dt[0]]['jumlahlalu']+$dzArr[$dt[1]]['jumlahlalu'];
                        $dzArr[$data['nourut']]['jumlahsekarang']=$dzArr[$dt[0]]['jumlahsekarang']+$dzArr[$dt[1]]['jumlahsekarang'];
                    break;

                }

    }
    $dzArr[$data['nourut']]['naikturun']=$dzArr[$data['nourut']]['jumlahsekarang']-$dzArr[$data['nourut']]['jumlahlalu'];
    @$dzArr[$data['nourut']]['persen']=($dzArr[$data['nourut']]['naikturun']/$dzArr[$data['nourut']]['jumlahlalu'])*100;
    }
}//if array tidak kosong
#end sync rupiah dari keu_saldobulanan dengan keu_5mesinlaporandt

$pdf=new PDF('P','mm','A4');
$pdf->AddPage();

if(!empty($dzArr))foreach($dzArr as $data){
        // dz: 20160120
        $kalimin=1;
        if($data['variablejadi']=='1')$kalimin=(-1);
        //
    
    if($data['nourut']!=''){
        if($data['tipe']=='Header')
        {
            $pdf->SetFont('Arial','B',8);
            $pdf->Cell(10,5,'','',0,'C');
            $pdf->Cell(100,5,$data['keterangan'],'',0,'L');
            $pdf->Cell(30,5,'','',0,'C');
            $pdf->Cell(30,5,'','',1,'C');         
        }
        else{
            if($data['tipe']=='Total'){   
                $pdf->Ln();    
                $pdf->SetFont('Arial','B',8);
                $pdf->Cell(10,5,'','',0,'C');
                $pdf->Cell(5,5,'','',0,'L');
                $pdf->Cell(95,5,$data['keterangan'],'',0,'L');
                $pdf->Cell(30,5,kalominuskurung($kalimin*$data['jumlahsekarang']),'T',0,'R');
                $pdf->Cell(30,5,kalominuskurung($kalimin*$data['jumlahlalu']),'T',1,'R'); 
                $pdf->Ln();
            }
            else
            {
                if($tplData==1){
                    if(($data['jumlahsekarang']==0) and ($data['jumlahlalu']==0)){
                        continue;    
                    }
                }
                    #escape yang nilainya nol
                    $pdf->Cell(10,5,'','',0,'C');
                    $pdf->SetFont('Arial','',8);
                    $pdf->Cell(10,5,'','',0,'L');
                    $pdf->Cell(90,5,$data['keterangan'],'',0,'L');
                    $pdf->Cell(30,5,kalominuskurung($kalimin*$data['jumlahsekarang']),'',0,'R');
                    $pdf->Cell(30,5,kalominuskurung($kalimin*$data['jumlahlalu']),'',1,'R'); 
            }         
        }    
        
    }
}

$pdf->Output();		
?>