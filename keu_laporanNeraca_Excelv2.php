<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/dzlib.php'); 

	$pt=$_GET['pt'];
	$unit=$_GET['gudang'];
	$periode=$_GET['periode'];
	$periode1=$_GET['periode1'];
	$revisi=$_GET['revisi'];
    $tplData=    isset($_GET['tplData'])? $_GET['tplData']: '';
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
if($periode1=='akhir')$kolomPRF="awal01"; else $kolomPRF="awal".date('m',$t); #"awal".substr($periodesaldo,4,2);

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
$bgclr="bgcolor=#DEDEDE ";
$bgtotal="bgcolor=#01DFD7";

$stream.="<table class=sortable border=1 cellspacing=1>
    <thead>
        <tr class=rowheader >
        <td ".$bgclr." rowspan=2 align=center width='395px;'  colspan=5 >Keterangan</td>    
        <td ".$bgclr." rowspan=2 align=center width='200px;'>".$captionCUR."</td>
        <td ".$bgclr." rowspan=2 align=center width='200px;'>".$captionPRF."</td>
        <td ".$bgclr." colspan=2 align=center width='200px;'>KENAIKAN/PENURUNAN</td>
        </tr>
       <tr>
        <td ".$bgclr." align=center width='155px;'>Rupiah</td>
        <td ".$bgclr." align=center width='45px;'>%</td>
        </tr>
    </thead><tbody>";
$jlhkolom=7;
$addEx="";

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
        foreach($dtAkn as $rwAkn=>$lstAkn){
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
            }
            if(substr($ba12['noakun'],0,5)=='11502'){
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
$aknA1='12601';
$aknB2='12610';
$aknB3='12699';
$lst128=array($aknA=>$aknA,$aknB=>$aknB);
$lst126=array($aknA1=>$aknA1,$aknB2=>$aknB2,$aknB3=>$aknB3);

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

#menghitung jumlah row bentuk detail 2,3 dan 4
if(!empty($dzArr)){
        foreach($dzArr as $data){
            if($data['tipe']=='Detail'){
                if(!empty($detAkun[$data['nourut']])){
                    foreach($detAkun[$data['nourut']] as $rowData=>$lstAkun2){
                        if($data['nourut']=='330003'){
                            if(strlen($lstAkun2)>3){
                                $lstAkun2=substr($lstAkun2,0,5);    
                            }
                        }
                        if(strlen($lstAkun2)==5){
                            foreach($dafAkun as $detAkun2){
                                if(substr($detAkun2,0,5)=='12620'){
                                    if($detAkun2=='1262002'){
                                        continue;
                                    }
                                }
                                if($lstAkun2==substr($detAkun2,0,5)){
                                    if($tempData!=$lstAkun2){
                                        $awalnya=0;  
                                        $tempData=$lstAkun2;  
                                    }
                                    if($data['nourut']=='330003'){
                                            $detAkun2=$detAkun2."01";
                                    }
                                    if($tplData=='1'){
                                        if(($dtRupiah[$detAkun2]['jumlahlalu']==0)&&($dtRupiah[$detAkun2]['jumlahsekarang']==0)){
                                            continue;
                                        }else{
                                            $awalnya+=1;    
                                        }   
                                    }else{
                                        $awalnya+=1;    
                                    } 
                                    $jmlhRowlvl3[$data['nourut'].$lstAkun2]=$awalnya;
                                }
                            }
                        }
                    if($lstAkun2=='128'){    
                            $awalnya=1;
                        foreach($lst128 as $dtLst128){ 
                                   $awalnya+=1;
                                   $jmlhRowlvl3[$data['nourut'].$lstAkun2]=$awalnya;
                                    foreach($dafAkun as $detAkun2){
                                        if($dtLst128==substr($detAkun2,0,5)){
                                            if($tmpde!=$dtLst128){
                                                $awalnya2=0;
                                                $tmpde=$dtLst128;
                                            }    
                                            if($tplData=='1'){
                                                if(($dtRupiah[$dtLst128]['jumlahlalu']==0)&&($dtRupiah[$dtLst128]['jumlahsekarang']==0)){
                                                    continue;
                                                }else{
                                                    $awalnya2+=1;
                                                }   
                                            }else{
                                                $awalnya2+=1;
                                            } 
                                            
                                            $jmlhRowlvl4[$data['nourut'].$dtLst128]=$awalnya2;
                                        }else{
                                            continue;
                                        }
                                    }
                        }
                    }
                    if($lstAkun2=='126'){
                            $awalnyadert=0;
                            foreach($lst126 as $dtLst126){ 
                                    foreach($dafAkun as $detAkun2){
                                        if(substr($dtLst126,0,4)==substr($detAkun2,0,4)){                                  
                                            if($akuncoba!=substr($detAkun2,0,5)){
                                                $akuncoba=substr($detAkun2,0,5);
                                                if($tplData=='1'){
                                                    if(($dtRupiah[$lstAkun2]['jumlahlalu']==0)&&($dtRupiah[$lstAkun2]['jumlahsekarang']==0)){
                                                        continue;
                                                    }else{
                                                        $awalnyadert+=1;       
                                                    }   
                                                }else{
                                                    $awalnyadert+=1;
                                                }
                                                $jmlhRowlvl3[$data['nourut'].$lstAkun2]=$awalnyadert;
                                                foreach($dafAkun as $detAkun2){
                                                    if($akuncoba==substr($detAkun2,0,5)){
                                                        if($tempde!=$akuncoba){
                                                            $awalnya=0;
                                                            $tempde=$akuncoba;
                                                        }
                                                        if($tplData=='1'){
                                                            if(($dtRupiah[$detAkun2]['jumlahlalu']==0)&&($dtRupiah[$detAkun2]['jumlahsekarang']==0)){
                                                                continue;
                                                            }else{
                                                                $awalnya+=1;
                                                            }   
                                                        }else{
                                                            $awalnya+=1;
                                                        }
                                                        
                                                        $jmlhRowlvl4[$data['nourut'].$akuncoba]=$awalnya;
                                                    }else{
                                                                continue;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                        }
                        if(strlen($lstAkun2)==3){#buat yang panjang lebar akunnya 3
                            if($lstAkun2=='128'){
                                continue;
                            }
                            foreach($dafAkun as $detAkun2){
                                if($lstAkun2==substr($detAkun2,0,3)){
                                    if($tempData!=$lstAkun2){
                                        $awalnya=0;  
                                        $tempData=$lstAkun2;  
                                    }
                                    if($tplData=='1'){
                                            if(($dtRupiah[$lstAkun2]['jumlahlalu']==0)&&($dtRupiah[$lstAkun2]['jumlahsekarang']==0)){
                                                continue;
                                            }else{
                                                $awalnya+=1;            
                                            }   
                                    }else{
                                        $awalnya+=1;
                                    }
                                    $jmlhRowlvl3[$data['nourut'].$lstAkun2]=$awalnya;
                                }
                                
                            }
                        }
                        if(strlen($lstAkun2)==4){
                            foreach($dafAkun as $detAkun2){
                                if($lstAkun2==substr($detAkun2,0,4)){
                                    if($akuncoba!=substr($detAkun2,0,5)){
                                        $akuncoba=substr($detAkun2,0,5);
                                        if($tplData=='1'){
                                            if(($dtRupiah[$lstAkun2]['jumlahlalu']==0)&&($dtRupiah[$lstAkun2]['jumlahsekarang']==0)){
                                                continue;
                                            }else{
                                                $awalnyadert+=1;       
                                            }   
                                        }else{
                                            $awalnyadert+=1;
                                        }
                                        $jmlhRowlvl3[$data['nourut'].$lstAkun2]=$awalnyadert;
                                        foreach($dafAkun as $detAkun2){
                                            if($akuncoba==substr($detAkun2,0,5)){
                                                if($tempde!=$akuncoba){
                                                    $awalnya=0;
                                                    $tempde=$akuncoba;
                                                }
                                                if(substr($detAkun2,0,4)=='1270'){
                                                    if(substr($detAkun2,-2,2)=='99'){
                                                      continue;
                                                    }    
                                                }
                                                if($tplData=='1'){
                                                    if(($dtRupiah[$detAkun2]['jumlahlalu']==0)&&($dtRupiah[$detAkun2]['jumlahsekarang']==0)){
                                                        continue;
                                                    }else{
                                                        $awalnya+=1;
                                                    }   
                                                }else{
                                                    $awalnya+=1;
                                                }
                                                
                                                $jmlhRowlvl4[$data['nourut'].$akuncoba]=$awalnya;
                                            }else{
                                                        continue;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }//end foreach
                }
            }
        }
}
#end menghitung row


#ambil format mesinlaporan==========
if(!empty($dzArr)){
        foreach($dzArr as $data){
        // dz: 20160120
        $kalimin=1;
        if($data['variablejadi']=='1')$kalimin=(-1);
        //
        
        if($data['tipe']=='Header')
        {
            if($data['tampil']==0)
                $stream.="<tr class=rowcontent><td colspan=9><b>".$data['keterangan']."</b></td></tr>";  
            else{
                $stream.="<tr class=rowcontent>
                    <td colspan=".$data['tampil']."></td>
                    <td colspan=".($jlhkolom-$data['tampil'])."><b>".$data['keterangan']."</b> sdsd</td>
                </tr>"; 
            }
        }
        else if($data['tipe']=='Total'){
            if($data['tampil']==0){
                $stream.="
                <tr class=rowcontent>
                    <td colspan=9>&nbsp;</td>
                </tr>
                <tr class=rowcontent>
                    <td colspan=5   ".$bgtotal."><b>".$data['keterangan']."</b></td>
                    <td align=right ".$bgtotal."><b>".kalominuskurung($kalimin*$data['jumlahsekarang'],2)."</b></td>
                    <td align=right ".$bgtotal."><b>".kalominuskurung($kalimin*$data['jumlahlalu'],2)."</b></td>
                    <td align=right ".$bgtotal."><b>".kalominuskurung($data['naikturun'],2)."</b></td>
                    <td align=right ".$bgtotal."><b>".kalominuskurung($data['persen'],2)."</b></td>    
                </tr>
                <tr class=rowcontent>
                    <td colspan=9>&nbsp;</td>
                </tr>
                "; 
            }
            else
            {
                $stream.="
                <tr class=rowcontent>
                    <td colspan=9>&nbsp;</td>
                </tr>
                <tr class=rowcontent>
                    <td colspan=".$data['tampil']."></td>
                    <td  ".$bgtotal."><b>".$data['keterangan']."</b></td>
                    <td  ".$bgtotal." align=right width='200px;'><b>".kalominuskurung($kalimin*$data['jumlahsekarang'],2)."</b></td>
                    <td  ".$bgtotal." align=right width='200px;'><b>".kalominuskurung($kalimin*$data['jumlahlalu'],2)."</b></td>
                    <td  ".$bgtotal." align=right width='155px;'><b>".kalominuskurung($data['naikturun'],2)."</b></td>    
                    <td  ".$bgtotal." align=right width='45px;'><b>".kalominuskurung($data['persen'],2)."</b></td>    
                </tr>
                <tr class=rowcontent><td colspan=9>&nbsp;</td></tr>
                ";                
            }   
        }
        else if($data['tipe']=='Detail'){
            if($tplData=='1'){
                if(($data['jumlahsekarang']==0)&&($data['jumlahlalu']==0)){
                    continue;
                }
            }
            if($data['nourut']=='330003'){
                   $jmlhRow[$data['nourut']]=count($detAkun[$data['nourut']]); 
            }
            $stream.="
            <tr class=rowcontent title='Click untuk melihat detail' onclick=\"lihatDetailNeraca('".$data['nourut']."###','".$jmlhRow[$data['nourut']]."','2');\" style=cursor:pointer;>
                <td colspan=".($data['tampil'])."></td>
                <td colspan=".(5-$data['tampil']).">".$data['keterangan']."</td>
                <td align=right width='200px;'>".kalominuskurung($kalimin*$data['jumlahsekarang'],2)."</td>
                <td align=right width='200px;'>".kalominuskurung($kalimin*$data['jumlahlalu'],2)."</td>
                <td align=right width='155px;'>".kalominuskurung($data['naikturun'],2)."</td>    
                <td align=right width='45px;'>".kalominuskurung($data['persen'],2)."</td>      
            </tr>";   
            continue;
            if(!empty($detAkun[$data['nourut']])){
             foreach($detAkun[$data['nourut']] as $rowData=>$lstAkun2){
                if($data['nourut']=='330003'){
                    if(strlen($lstAkun2)>3){
                        $lstAkun2=substr($lstAkun2,0,5);  
                    }
                }
                $nmKeterangan="";
                if($tempUrut!=$data['nourut']){
                    $tempUrut=$data['nourut'];
                    $urut2an=0;
                }
                $whr="noakun='".$lstAkun2."'";
                $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                switch ($lstAkun2) {
                    case '118':
                        $nmKeterangan="UANG MUKA";
                    break;
                    case '1270':
                        $nmKeterangan="PEROLEHAN ASET TETAP";
                    break;
                    case '1279':
                        $nmKeterangan="AKUMULASI PENYUSUTAN";
                    break;
                    case'128':
                        $nmKeterangan="PEMBIBITAN";
                        $akn0='128';
                        $aknC='1280298';
                        $dtRupiah[$lstAkun2]['jumlahsekarang']=$dtRupiah[$aknA]['jumlahsekarang']+$dtRupiah[$aknB]['jumlahsekarang']+$dtRupiah[$aknC]['jumlahsekarang'];
                        $dtRupiah[$lstAkun2]['jumlahlalu']=$dtRupiah[$aknA]['jumlahlalu']+$dtRupiah[$aknB]['jumlahlalu']+$dtRupiah[$aknC]['jumlahlalu'];
                        
                    break;
                    default:
                    $nmKeterangan=$optNmAkun[$lstAkun2];
                    break;                
                }
                $clid="";
                $urut2an+=1;
                     
                    $stream.="<tr class=rowcontent id='".$data['nourut']."_2_".$urut2an."' ".$addlink."  ><td colspan=1>&nbsp;</td>";
                    $stream.="<td colspan=".(5-$data['tampil'])." width='360px;'><b>".strtoupper($nmKeterangan)."</b></td>";
                    if($data['nourut']=='330003'){
                        if(strlen($lstAkun2)>3){
                            $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$lstAkun2."01"]['jumlahsekarang'],2)."</td>
                              <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$lstAkun2."01"]['jumlahlalu'],2)."</td>";
                            $dtRupiah[$lstAkun2]['naikturun']=$dtRupiah[$lstAkun2."01"]['jumlahsekarang']-$dtRupiah[$lstAkun2."01"]['jumlahlalu'];
                            @$dtRupiah[$lstAkun2]['persen']=($dtRupiah[$lstAkun2."01"]['naikturun']/$dtRupiah[$lstAkun2."01"]['jumlahlalu'])*100;   
                        }else{
                            $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$lstAkun2]['jumlahsekarang'],2)."</td>
                              <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$lstAkun2]['jumlahlalu'],2)."</td>";
                            $dtRupiah[$lstAkun2]['naikturun']=$dtRupiah[$lstAkun2]['jumlahsekarang']-$dtRupiah[$lstAkun2]['jumlahlalu'];
                            @$dtRupiah[$lstAkun2]['persen']=($dtRupiah[$lstAkun2]['naikturun']/$dtRupiah[$lstAkun2]['jumlahlalu'])*100;
                        }
                    }else{
                        $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$lstAkun2]['jumlahsekarang'],2)."</td>
                              <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$lstAkun2]['jumlahlalu'],2)."</td>";
                    $dtRupiah[$lstAkun2]['naikturun']=$dtRupiah[$lstAkun2]['jumlahsekarang']-$dtRupiah[$lstAkun2]['jumlahlalu'];
                    @$dtRupiah[$lstAkun2]['persen']=($dtRupiah[$lstAkun2]['naikturun']/$dtRupiah[$lstAkun2]['jumlahlalu'])*100;
                    }
                    $stream.="<td align=right width='155px;'>".kalominuskurung($dtRupiah[$lstAkun2]['naikturun'],2)."</td>    
                          <td align=right>".kalominuskurung($dtRupiah[$lstAkun2]['persen'],2)."</td>      
                          </tr>"; 
               
                
                if(strlen($lstAkun2)==5){
                    foreach($dafAkun as $detAkun2){
                        if(substr($detAkun2,0,5)=='12620'){
                            if($detAkun2=='1262002'){
                                continue;
                            }
                        }
                        if($lstAkun2==substr($detAkun2,0,5)){
                            if($tempData!=$lstAkun2){
                                $awalnya=0;  
                                $tempData=$lstAkun2;  
                            }
                            if($data['nourut']=='330003'){
                                    $detAkun2=$detAkun2."01";
                            }
                            if($tplData=='1'){
                                if(($dtRupiah[$detAkun2]['jumlahlalu']==0)&&($dtRupiah[$detAkun2]['jumlahsekarang']==0)){
                                    continue;
                                }else{
                                    $awalnya+=1;
                                }   
                            }else{
                                $awalnya+=1;
                            }
                            if($data['nourut']=='330003'){
                                $whr="noakun='".substr($detAkun2,0,7)."'";
                            }else{
                                $whr="noakun='".$detAkun2."'";    
                            }
                            $whr="noakun='".$detAkun2."'";
                            $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                            $stream.="<tr class=rowcontent id='".$data['nourut']."_".$lstAkun2."_3_".$awalnya."'><td colspan=2>&nbsp;</td>";
                            if($data['nourut']=='330003'){
                                $stream.="<td colspan=".(4-$data['tampil'])." width='260px;'>".strtoupper($optNmAkun[substr($detAkun2,0,7)])."</b></td>";
                            }else{
                                $stream.="<td colspan=".(4-$data['tampil'])." width='260px;'>".strtoupper($optNmAkun[$detAkun2])."</b></td>";
                            }
                            $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahsekarang'],2)."</td>
                                      <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahlalu'],2)."</td>";
                            $dtRupiah[$detAkun2]['naikturun']=$dtRupiah[$detAkun2]['jumlahsekarang']-$dtRupiah[$detAkun2]['jumlahlalu'];
                            if(intval($dtRupiah[$detAkun2]['jumlahlalu'])==0){
                                $dtRupiah[$detAkun2]['persen']=0;
                            }else{
                                @$dtRupiah[$detAkun2]['persen']=($dtRupiah[$detAkun2]['naikturun']/$dtRupiah[$detAkun2]['jumlahlalu'])*100;
                            }
                            
                            $stream.="<td align=right width='155px;'>".kalominuskurung($dtRupiah[$detAkun2]['naikturun'],2)."</td>    
                                      <td align=right>".kalominuskurung($dtRupiah[$detAkun2]['persen'],2)."</td>      
                                      </tr>";
                        }
                    }
                }
                if($lstAkun2=='128'){    
                    $awalnya=0;
                    foreach($lst128 as $dtLst128){ 
                        if($tplData=='1'){
                                if(($dtRupiah[$dtLst128]['jumlahlalu']==0)&&($dtRupiah[$dtLst128]['jumlahsekarang']==0)){
                                    continue;
                                }else{
                                    $awalnya+=1;
                                }   
                        }else{
                            $awalnya+=1;
                        }
                       
                       $whr="noakun='".$dtLst128."'";
                       $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                       $stream.="<tr class=rowcontent cursor:pointer   title='Klik untuk detail ".strtoupper($optNmAkun[$dtLst128])."'  id='".$data['nourut']."_".$lstAkun2."_3_".$awalnya."' ><td colspan=2>&nbsp;</td>";
                       $stream.="<td colspan=".(4-$data['tampil'])." width='260px;'>".strtoupper($optNmAkun[$dtLst128])."</td>";
                       $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$dtLst128]['jumlahsekarang'],2)."</td>
                                  <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$dtLst128]['jumlahlalu'],2)."</td>";
                        $dtRupiah[$dtLst128]['naikturun']=$dtRupiah[$dtLst128]['jumlahsekarang']-$dtRupiah[$dtLst128]['jumlahlalu'];
                        if(intval($dtRupiah[$dtLst128]['jumlahlalu'])==0){
                            $dtRupiah[$dtLst128]['persen']=0;
                        }else{
                            @$dtRupiah[$dtLst128]['persen']=($dtRupiah[$dtLst128]['naikturun']/$dtRupiah[$dtLst128]['jumlahlalu'])*100;
                        }
                        $stream.="<td align=right width='155px;'>".kalominuskurung($dtRupiah[$dtLst128]['naikturun'],2)."</td>    
                                  <td align=right>".kalominuskurung($dtRupiah[$dtLst128]['persen'],2)."</td>      
                                  </tr>";
                          foreach($dafAkun as $detAkun2){
                            if($dtLst128==substr($detAkun2,0,5)){
                                if($tmpde!=$dtLst128){
                                    $awalnya2=0;
                                    $tmpde=$dtLst128;
                                }
                                if($tplData=='1'){
                                    if(($dtRupiah[$detAkun2]['jumlahlalu']==0)&&($dtRupiah[$detAkun2]['jumlahsekarang']==0)){
                                        continue;
                                    }else{
                                        $awalnya2+=1;        
                                    }   
                                }else{
                                    $awalnya2+=1;
                                }
                                
                                $whr="noakun='".$detAkun2."'";
                                $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                                $stream.="<tr class=rowcontent  id='".$data['nourut']."_".$dtLst128."_4_".$awalnya2."'><td colspan=2>&nbsp;</td>";
                                $stream.="<td colspan=".(4-$data['tampil'])." width='160px;'>".strtoupper($optNmAkun[$detAkun2])."</td>";
                                $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahsekarang'],2)."</td>
                                          <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahlalu'],2)."</td>";
                                $dtRupiah[$detAkun2]['naikturun']=$dtRupiah[$detAkun2]['jumlahsekarang']-$dtRupiah[$detAkun2]['jumlahlalu'];
                                if(intval($dtRupiah[$detAkun2]['jumlahlalu'])==0){
                                    $dtRupiah[$detAkun2]['persen']=0;
                                }else{
                                    @$dtRupiah[$detAkun2]['persen']=($dtRupiah[$detAkun2]['naikturun']/$dtRupiah[$detAkun2]['jumlahlalu'])*100;
                                }   
                                $stream.="<td align=right width='155px;'>".kalominuskurung($dtRupiah[$detAkun2]['naikturun'],2)."</td>    
                                          <td align=right>".kalominuskurung($dtRupiah[$detAkun2]['persen'],2)."</td>      
                                          </tr>";
                            }else{
                                        continue;
                            }
                        }
                    }
                       $whr="noakun='".$aknC."'";
                       $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                       $stream.="<tr class=rowcontent  id='".$data['nourut']."_".$lstAkun2."_3_3'><td colspan=2>&nbsp;</td>";
                       $stream.="<td colspan=".(4-$data['tampil'])." width='260px;'>".strtoupper($optNmAkun[$aknC])."</td>";
                       $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$aknC]['jumlahsekarang'],2)."</td>
                                  <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$aknC]['jumlahlalu'],2)."</td>";
                        $dtRupiah[$aknC]['naikturun']=$dtRupiah[$aknC]['jumlahsekarang']-$dtRupiah[$aknC]['jumlahlalu'];
                        if(intval($dtRupiah[$aknC]['jumlahlalu'])==0){
                            $dtRupiah[$aknC]['persen']=0;
                        }else{
                            @$dtRupiah[$aknC]['persen']=($dtRupiah[$aknC]['naikturun']/$dtRupiah[$aknC]['jumlahlalu'])*100;
                        }
                        $stream.="<td align=right width='155px;'>".kalominuskurung($dtRupiah[$aknC]['naikturun'],2)."</td>    
                                  <td align=right>".kalominuskurung($dtRupiah[$aknC]['persen'],2)."</td>      
                                  </tr>";
                }
                $awalnyadt=0;
                if($lstAkun2=='126'){ 
                    foreach($lst126 as $dtLst126){ 
                        foreach($dafAkun as $detAkun2){
                            if(substr($dtLst126,0,4)==substr($detAkun2,0,4)){
                                if($akuncoba!=substr($detAkun2,0,5)){
                                    $akuncoba=substr($detAkun2,0,5);
                                    if($tplData=='1'){
                                        if(($dtRupiah[$detAkun2]['jumlahlalu']==0)&&($dtRupiah[$detAkun2]['jumlahsekarang']==0)){
                                            continue;
                                        }else{
                                            $awalnyadt+=1;
                                        }   
                                    }else{
                                        $awalnyadt+=1;
                                    }
                                    $whr="noakun='".$akuncoba."'";
                                    $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                                    $stream.="<tr class=rowcontent id='".$data['nourut']."_".$akuncoba."_3_".$awalnyadt."' title='Klik untuk detail ".strtoupper($optNmAkun[$akuncoba])."' style=display:none;cursor:pointer;  onclick=\"lihatDetailNeraca('".$data['nourut']."###".$akuncoba."','".$jmlhRowlvl4[$data['nourut'].$akuncoba]."','4');\"><td colspan=2>&nbsp;</td>";
                                    $stream.="<td colspan=".(4-$data['tampil'])." width='260px;'><b>".strtoupper($optNmAkun[$akuncoba])."</b></td>";
                                    $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$akuncoba]['jumlahsekarang'],2)."</td>
                                              <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$akuncoba]['jumlahlalu'],2)."</td>";
                                    $dtRupiah[$akuncoba]['naikturun']=$dtRupiah[$akuncoba]['jumlahsekarang']-$dtRupiah[$akuncoba]['jumlahlalu'];
                                    if(intval($dtRupiah[$akuncoba]['jumlahlalu'])==0){
                                        $dtRupiah[$akuncoba]['persen']=0;
                                    }else{
                                        @$dtRupiah[$akuncoba]['persen']=($dtRupiah[$akuncoba]['naikturun']/$dtRupiah[$akuncoba]['jumlahlalu'])*100;
                                    }   
                                    $stream.="<td align=right width='155px;'>".kalominuskurung($dtRupiah[$akuncoba]['naikturun'],2)."</td>    
                                              <td align=right>".kalominuskurung($dtRupiah[$akuncoba]['persen'],2)."</td>      
                                              </tr>";
                                    foreach($dafAkun as $detAkun2){
                                        
                                        if($akuncoba==substr($detAkun2,0,5)){
                                            if($tmpdedt!=$akuncoba){
                                                $tmpdedt=$akuncoba;
                                                $awalnya=0;
                                            }
                                            
                                            if($tplData=='1'){
                                                if(($dtRupiah[$detAkun2]['jumlahlalu']==0)&&($dtRupiah[$detAkun2]['jumlahsekarang']==0)){
                                                    continue;
                                                }else{
                                                    $awalnya+=1;
                                                }   
                                            }else{
                                                $awalnya+=1;
                                            }
                                            
                                            $whr="noakun='".$detAkun2."'";
                                            $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                                            $stream.="<tr class=rowcontent id='".$data['nourut']."_".$akuncoba."_4_".$awalnya."' style=display:none;><td colspan=2>&nbsp;</td>";
                                            $stream.="<td colspan=".(4-$data['tampil'])." width='160px;'>".strtoupper($optNmAkun[$detAkun2])."</td>";
                                            $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahsekarang'],2)."</td>
                                                      <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahlalu'],2)."</td>";
                                            $dtRupiah[$detAkun2]['naikturun']=$dtRupiah[$detAkun2]['jumlahsekarang']-$dtRupiah[$detAkun2]['jumlahlalu'];
                                            if(intval($dtRupiah[$detAkun2]['jumlahlalu'])==0){
                                                $dtRupiah[$detAkun2]['persen']=0;
                                            }else{
                                                @$dtRupiah[$detAkun2]['persen']=($dtRupiah[$detAkun2]['naikturun']/$dtRupiah[$detAkun2]['jumlahlalu'])*100;
                                            }   
                                            $stream.="<td align=right width='155px;'>".kalominuskurung($dtRupiah[$detAkun2]['naikturun'],2)."</td>    
                                                      <td align=right>".kalominuskurung($dtRupiah[$detAkun2]['persen'],2)."</td>      
                                                      </tr>";
                                        }else{
                                                    continue;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if(strlen($lstAkun2)==3){
                    if(($lstAkun2=='128')||($lstAkun2=='126')){
                        continue;
                    }
                    foreach($dafAkun as $detAkun2){
                        if($lstAkun2==substr($detAkun2,0,3)){
                            if($tempData!=$lstAkun2){
                                $awalnya=0;  
                                $tempData=$lstAkun2;  
                            }
                            if($tplData=='1'){
                                    if(($dtRupiah[$detAkun2]['jumlahlalu']==0)&&($dtRupiah[$detAkun2]['jumlahsekarang']==0)){
                                        continue;
                                    }else{
                                        $awalnya+=1;            
                                    }   
                            }else{
                                $awalnya+=1;
                            }
                            
                                $whr="noakun='".$detAkun2."'";
                                $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                                $stream.="<tr class=rowcontent   title='Klik untuk detail ".strtoupper($optNmAkun[$detAkun2])."'   id='".$data['nourut']."_".$lstAkun2."_3_".$awalnya."'><td colspan=2>&nbsp;</td>";
                                $stream.="<td colspan=".(4-$data['tampil'])." width='260px;'>".strtoupper($optNmAkun[$detAkun2])."</td>";
                                $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahsekarang'],2)."</td>
                                          <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahlalu'],2)."</td>";
                                $dtRupiah[$detAkun2]['naikturun']=$dtRupiah[$detAkun2]['jumlahsekarang']-$dtRupiah[$detAkun2]['jumlahlalu'];
                                if(intval($dtRupiah[$detAkun2]['jumlahlalu'])==0){
                                    $dtRupiah[$detAkun2]['persen']=0;
                                }else{
                                    @$dtRupiah[$detAkun2]['persen']=($dtRupiah[$detAkun2]['naikturun']/$dtRupiah[$detAkun2]['jumlahlalu'])*100;
                                }
                                
                                $stream.="<td align=right width='155px;'>".kalominuskurung($dtRupiah[$detAkun2]['naikturun'],2)."</td>    
                                          <td align=right>".kalominuskurung($dtRupiah[$detAkun2]['persen'],2)."</td>      
                                          </tr>";
                            }
                        
                    }
                }
                $awalnyadt=0;
                if(strlen($lstAkun2)==4){
                    foreach($dafAkun as $detAkun2){
                        if($lstAkun2==substr($detAkun2,0,4)){
                            if($akuncoba!=substr($detAkun2,0,5)){
                                $akuncoba=substr($detAkun2,0,5);
                                if($tplData=='1'){
                                    if(($dtRupiah[$detAkun2]['jumlahlalu']==0)&&($dtRupiah[$detAkun2]['jumlahsekarang']==0)){
                                        continue;
                                    }else{
                                        $awalnyadt+=1;
                                    }   
                                }else{
                                    $awalnyadt+=1;
                                }
                                $whr="noakun='".$akuncoba."'";
                                $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                                $stream.="<tr class=rowcontent id='".$data['nourut']."_".$akuncoba."_3_".$awalnyadt."'><td colspan=2>&nbsp;</td>";
                                $stream.="<td colspan=".(4-$data['tampil'])." width='260px;'><b>".strtoupper($optNmAkun[$akuncoba])."</b></td>";
                                $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$akuncoba]['jumlahsekarang'],2)."</td>
                                          <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$akuncoba]['jumlahlalu'],2)."</td>";
                                $dtRupiah[$akuncoba]['naikturun']=$dtRupiah[$akuncoba]['jumlahsekarang']-$dtRupiah[$akuncoba]['jumlahlalu'];
                                if(intval($dtRupiah[$akuncoba]['jumlahlalu'])==0){
                                    $dtRupiah[$akuncoba]['persen']=0;
                                }else{
                                    @$dtRupiah[$akuncoba]['persen']=($dtRupiah[$akuncoba]['naikturun']/$dtRupiah[$akuncoba]['jumlahlalu'])*100;
                                }   
                                $stream.="<td align=right width='155px;'>".kalominuskurung($dtRupiah[$akuncoba]['naikturun'],2)."</td>    
                                          <td align=right>".kalominuskurung($dtRupiah[$akuncoba]['persen'],2)."</td>      
                                          </tr>";
                                foreach($dafAkun as $detAkun2){
                                    if($akuncoba==substr($detAkun2,0,5)){
                                        if($tmpdedt!=$akuncoba){
                                            $tmpdedt=$akuncoba;
                                            $awalnya=0;
                                        }
                                        if(substr($detAkun2,0,4)=='1270'){
                                            if(substr($detAkun2,-2,2)=='99'){
                                              continue;
                                            }    
                                        }
                                        if($tplData=='1'){
                                            if(($dtRupiah[$detAkun2]['jumlahlalu']==0)&&($dtRupiah[$detAkun2]['jumlahsekarang']==0)){
                                                continue;
                                            }else{
                                                $awalnya+=1;
                                            }   
                                        }else{
                                            $awalnya+=1;
                                        }
                                        
                                        $whr="noakun='".$detAkun2."'";
                                        $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                                        $stream.="<tr class=rowcontent id='".$data['nourut']."_".$akuncoba."_4_".$awalnya."'><td colspan=2>&nbsp;</td>";
                                        $stream.="<td colspan=".(4-$data['tampil'])." width='160px;'>".strtoupper($optNmAkun[$detAkun2])."</td>";
                                        $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahsekarang'],2)."</td>
                                                  <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahlalu'],2)."</td>";
                                        $dtRupiah[$detAkun2]['naikturun']=$dtRupiah[$detAkun2]['jumlahsekarang']-$dtRupiah[$detAkun2]['jumlahlalu'];
                                        if(intval($dtRupiah[$detAkun2]['jumlahlalu'])==0){
                                            $dtRupiah[$detAkun2]['persen']=0;
                                        }else{
                                            @$dtRupiah[$detAkun2]['persen']=($dtRupiah[$detAkun2]['naikturun']/$dtRupiah[$detAkun2]['jumlahlalu'])*100;
                                        }   
                                        $stream.="<td align=right width='155px;'>".kalominuskurung($dtRupiah[$detAkun2]['naikturun'],2)."</td>    
                                                  <td align=right>".kalominuskurung($dtRupiah[$detAkun2]['persen'],2)."</td>      
                                                  </tr>";
                                    }else{
                                                continue;
                                    }
                                }
                            }
                        }
                    }
                }
             }
            }
        }
        
    }
}
$stream.= "</tbody></table>";  
$nop_="Neraca-".$pt."-".$unit."-".$periodesaldo;
if(strlen($stream)>0)
{
if ($handle = opendir('tempExcel')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            @unlink('tempExcel/'.$file);
        }
    }	
   closedir($handle);
}
 $handle=fopen("tempExcel/".$nop_.".xls",'w');
 if(!fwrite($handle,$stream))
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
?>