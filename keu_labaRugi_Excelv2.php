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
    $tplData2=    isset($_GET['tplData2'])? $_GET['tplData2']: '';

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
if($periode1=='akhir')$periodmilllalu=$tahunlalu."-12"; else $periodmilllalu=$tahunlalu."-".$bulan;

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
$bgclr="bgcolor=#DEDEDE ";
$styDt="";
$jlhkolom=9;
$jlhkolom2=6;
$jlhkolom3=4;
if($tplData2==1){
    $styDt="style=display:none";
    $jlhkolom=7;
    $jlhkolom2=4;
    $jlhkolom3=2;
}

$stream.="<table class=sortable border=1 cellspacing=1>
    <thead>
        <tr class=rowheader >
        <td rowspan=2 align=center width='395px;'  colspan=5 ".$bgclr.">Keterangan</td>    

        <td rowspan=2 align=center width='200px;' ".$bgclr.">".$captionCUR."</td>
        <td rowspan=2 align=center width='200px;' ".$bgclr.">".$captionPRF."</td>";
    if($tplData2==0){
        $stream.="<td colspan=2 align=center width='200px;' ".$bgclr.">KENAIKAN/PENURUNAN</td>";
    }
        $stream.="</tr>";
    if($tplData2==0){
    $stream.="<tr>
        <td align=center width='155px;'   ".$bgclr.">Rupiah</td>
        <td align=center width='45px;'   ".$bgclr.">%</td>
        </tr>";
    }
$stream.="</thead><tbody>";
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
        $sDataAkn="select noakun from ".$dbname.".keu_5akun where 
                   noakun between '".$bar->noakundari."' and '".$bar->noakunsampai."'
                   and char_length(noakun)=7";
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
    $dzArr[$bar->nourut]['variablejadi']=$bar->variablejadi; // dz 20160120
}
if($revisi!=0){
    $addRev=" and revisi<='".$revisi."'";
}

#nilai akunting bisa jadi akhir bulan thn lalu atau bulan tahun lalu
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

#menghitung jumlah row bentuk detail 2,3 dan 4
if(!empty($dzArr)){
        foreach($dzArr as $data){
            if($data['tipe']=='Detail'){
                if(!empty($detAkun[$data['nourut']])){
                    foreach($detAkun[$data['nourut']] as $rowData=>$lstAkun2){
                        if(strlen($lstAkun2)==5){
                            foreach($dafAkun as $detAkun2){
                                if($lstAkun2==substr($detAkun2,0,5)){
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
                                    $jmlhRowlvl3[$data['nourut'].$lstAkun2]=$awalnya;
                                }
                            }
                        }

                        if(strlen($lstAkun2)==3){#buat yang panjang lebar akunnya 3
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
$ar210=0;
#DAFTAR NOURUT UNTUK TIPE TOTAL YANG DIBEDAIN DIKIT
$arrTampilanTotal=array("210015"=>"210015","210019"=>"210019","210023"=>"210023","212002"=>"212002","212003"=>"212003","212004"=>"212004");
#ambil format mesinlaporan==========
if(!empty($dzArr)){
        foreach($dzArr as $data){
            
        // dz: 20160120
        $kalimin=1; 
        if($data['variablejadi']=='1')$kalimin=(-1);
        //            
            
        $stDet="";
        $addStylg="";
         
        if($data['tipe']=='Header')
        {
            if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
            }
            $linkdet="";
            
            if($data['nourut']=='212001'){
                $linkdet=" style='cursor:pointer;'onclick=\"lihatDetailNeraca('200000###','".$ar210."','2');\" title='Click Detail BEBAN POKOK PENJUALAN' ";
            }

            if($data['tampil']==0)
                $stream.="<tr class=rowcontent ".$linkdet." ".$stDet."><td colspan=".$jlhkolom."><b>".$data['keterangan']."</b></td></tr>";  
            else{
                $stream.="<tr class=rowcontent  ".$stDet.">
                    <td colspan=".$data['tampil']."></td>
                    <td colspan=".($jlhkolom-$data['tampil'])."><b>".$data['keterangan']."</b></td>
                </tr>"; 
            }
        }
        else if($data['tipe']=='Total'){
            if($data['tampil']==0){
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
                    if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                    }
                    $stream.="<tr class=rowcontent  ".$stDet."><td colspan=5><b>".$data['keterangan']."</b></td><td align=right><b>".kalominuskurung($kalimin*$data['jumlahsekarang'],2)."</b></td>
                                 <td align=right><b>".kalominuskurung($kalimin*$data['jumlahlalu'],2)."</b></td>";
                    $stream.="<td align=right ".$styDt."><b>".kalominuskurung($data['naikturun'],2)."</b></td>
                                 <td align=right ".$styDt."><b>".kalominuskurung($data['persen'],2)."</b></td></tr>"; 
                    if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                    }
                    $stream.="<tr class=rowcontent  ".$stDet." >
                                  <td colspan=".($data['tampil'])."></td>
                                  <td colspan=4>".$ketDet1."</td>
                                  <td align=right width='200px;'>".kalominuskurung($kalimin*$rpCpoSkrg,2)."</td>
                                  <td align=right width='200px;'>".kalominuskurung($kalimin*$rpCpoLalu,2)."</td>";
                    $stream.="<td align=right width='155px;' ".$styDt.">".kalominuskurung($dtCpo['naikturun'],2)."</b></td>    
                              <td align=right width='45px;' ".$styDt.">".kalominuskurung($dtCpo['persen'],2)."</td>      
                              </tr>"; 
                    if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                    }
                    $stream.="<tr class=rowcontent ".$stDet." >
                              <td colspan=".($data['tampil'])."></td>
                              <td colspan=4>".$ketDet2."</td>
                              <td align=right width='200px;'>".kalominuskurung($kalimin*$rpKerSkrg,2)."</td>
                              <td align=right width='200px;'>".kalominuskurung($kalimin*$rpKerLalu,2)."</td>";
                    $stream.="<td align=right width='155px;' ".$styDt.">".kalominuskurung($dtKer['naikturun'],2)."</b></td>    
                              <td align=right width='45px;' ".$styDt.">".kalominuskurung($dtKer['persen'],2)."</td>      
                              </tr>"; 
                }else{
                    if($data['nourut']=='212005'){
                        $scek="select count(tanggal) as rowdt from ".$dbname.".pabrik_pengolahan 
                                where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')
                                and tanggal like '".$periode."%'";
                        $qcek=mysql_query($scek) or die(mysql_error($conn));
                        $rcek=mysql_fetch_assoc($qcek);
                        if($rcek['rowdt']!=0){
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
                        }else{
                            $data['jumlahlalu']=$dzArr['210015']['jumlahlalu'];
                            $data['jumlahsekarang']=$dzArr['210015']['jumlahsekarang'];
                            $rpBaru[$data['nourut']]['jumlahsekarang']=$dzArr['210015']['jumlahsekarang'];
                            $rpBaru[$data['nourut']]['jumlahlalu']=$dzArr['210015']['jumlahlalu'];
                            $data['naikturun']= $data['jumlahsekarang']-$data['jumlahlalu'];
                            @$dtCpo['persen']=($dtCpoSkrg['naikturun']/$data['jumlahlalu'])*100;
                        }
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
                         $temPdtLalu=$rpBaru[$dt[1]]['jumlahlalu']+$dzArr[$dt[0]]['jumlahlalu'];
                         $temPdt=$rpBaru[$dt[1]]['jumlahsekarang']+$dzArr[$dt[0]]['jumlahsekarang'];
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
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                    }
                    $stream.="<tr class=rowcontent ".$stDet.">
                              <td colspan=5>&nbsp;</td>
                              <td colspan=".$jlhkolom3."></td>
                              </tr>";
                    if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                    }
                    $stream.="<tr class=rowcontent  ".$stDet.">
                              <td colspan=5><b>".$data['keterangan']."</b></td>
                              <td align=right><b>".kalominuskurung($kalimin*$data['jumlahsekarang'],2)."</b></td>
                              <td align=right><b>".kalominuskurung($kalimin*$data['jumlahlalu'],2)."</b></td>";
                    $stream.="<td align=right ".$styDt."><b>".kalominuskurung($data['naikturun'],2)."</b></td>
                              <td align=right ".$styDt."><b>".kalominuskurung($data['persen'],2)."</b></td>    
                              </tr>";
                    if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                    }
                    $stream.="<tr class=rowcontent ".$stDet.">
                              <td colspan=5>&nbsp;</td>
                              <td colspan=".$jlhkolom3."></td>
                              </tr>";
                    if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                    }
                    $stream.=" <tr class=rowcontent ".$stDet.">
                    <td style='width:30px'></td>
                    <td style='width:30px'></td>
                    <td style='width:30px'></td>
                    <td colspan=".$jlhkolom2."></td></tr>";
                }
                
            }
            else
            { 
                if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                }
                $stream.="<tr class=rowcontent ".$stDet.">
                    <td colspan=5></td>
                    <td colspan=".($jlhkolom-5)."></td>
                </tr>";
                if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                }
                $stream.="<tr class=rowcontent ".$stDet.">
                    <td colspan=".$data['tampil']."></td>
                    <td colspan=".$jlhkolom2."><b>".$data['keterangan']."</b></td>
                    <td align=right width='200px;'><b>".kalominuskurung($kalimin*$data['jumlahsekarang'],2)."</b></td>
                    <td align=right width='200px;'><b>".kalominuskurung($kalimin*$data['jumlahlalu'],2)."</b></td>";
         $stream.="<td align=right width='155px;' ".$styDt."><b>".kalominuskurung($data['naikturun'],2)."</b></td>    
                    <td align=right width='45px;' ".$styDt."><b>".kalominuskurung($data['persen'],2)."</b></td>    
                </tr>";
                if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                }
        $stream.="<tr class=rowcontent><td colspan=".$jlhkolom." ".$stDet.">.</td></tr>
                ";                
            }
             
        }
        else if($data['tipe']=='Detail'){
            
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
                
                $dtCpo['naikturun']=$rpCpoSkrg-$rpCpoLalu;
                @$dtCpo['persen']=($dtCpoSkrg['naikturun']/$rpCpoLalu)*100;
                if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                }
                $stream.="<tr class=rowcontent  ".$stDet." >
                                      <td colspan=".($data['tampil'])."></td>
                                      <td colspan=4>".$data['keterangan']."</td>
                                      <td align=right width='200px;'>".kalominuskurung($kalimin*$rpCpoSkrg,2)."</td>
                                      <td align=right width='200px;'>".kalominuskurung($kalimin*$rpCpoLalu,2)."</td>";
                $stream.="<td align=right width='155px;' ".$styDt.">".kalominuskurung($dtCpo['naikturun'],2)."</b></td>    
                                  <td align=right width='45px;' ".$styDt.">".kalominuskurung($dtCpo['persen'],2)."</td>      
                                  </tr>"; 

            }else{
                if($tplData=='1'){
                    if(($data['jumlahsekarang']==0)&&($data['jumlahlalu']==0)){
                        continue;
                    }
                }
                $clkdetail="";
                if(!empty($detAkun[$data['nourut']])){  
                    $clkdetail="onclick=\"lihatDetailNeraca('".$data['nourut']."###','".$jmlhRow[$data['nourut']]."','2');\" style='cursor:pointer;".$addStylg."'";
                }
                if((substr($data['nourut'],0,3)=='210')||(substr($data['nourut'],0,3)=='200')){
                        $ar210+=1;
                        $stDet=" id='200000_2_".$ar210."' style=display:none";
                }
                $stream.="
                <tr class=rowcontent title='Click untuk melihat detail'  ".$clkdetail."  ".$stDet.">
                    <td colspan=".($data['tampil'])."></td>
                    <td colspan=".(5-$data['tampil']).">".$data['keterangan']."</td>
                    <td align=right width='200px;'>".kalominuskurung($kalimin*$data['jumlahsekarang'],2)."</td>
                    <td align=right width='200px;'>".kalominuskurung($kalimin*$data['jumlahlalu'],2)."</td>";
                $stream.="<td align=right width='155px;' ".$styDt.">".kalominuskurung($data['naikturun'],2)."</b></td>    
                    <td align=right width='45px;' ".$styDt.">".kalominuskurung($data['persen'],2)."</td>      
                </tr>";  
            }
             
            if(!empty($detAkun[$data['nourut']])){
             foreach($detAkun[$data['nourut']] as $rowData=>$lstAkun2){
                $nmKeterangan="";
                if($tempUrut!=$data['nourut']){
                    $tempUrut=$data['nourut'];
                    $urut2an=0;
                }
                $whr="noakun='".$lstAkun2."'";
                $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whr);
                $nmKeterangan=$optNmAkun[$lstAkun2];
                $clid="";
                $urut2an+=1;
                $addlink="";
                $crs="";
                if(strlen($lstAkun2)==5){
                    if($jmlhRowlvl3[$data['nourut'].$lstAkun2]!=''){
                        $addlink=" title='Klik untuk detail ".strtoupper($nmKeterangan)."' onclick=\"lihatDetailNeraca('".$data['nourut']."###".$lstAkun2."','".$jmlhRowlvl3[$data['nourut'].$lstAkun2]."','3');\"";
                        $crs="cursor:pointer;";

                    }
                }
                if(strlen($lstAkun2)==3){
                        if($jmlhRowlvl3[$data['nourut'].$lstAkun2]!=''){
                            $addlink=" title='Klik untuk detail ".strtoupper($nmKeterangan)."' onclick=\"lihatDetailNeraca('".$data['nourut']."###".$lstAkun2."','".$jmlhRowlvl3[$data['nourut'].$lstAkun2]."','3');\"";
                            $crs="cursor:pointer;";
                        }    
                }
                if(strlen($lstAkun2)==4){
                        if($jmlhRowlvl3[$data['nourut'].$lstAkun2]!=''){
                            $addlink=" title='Klik untuk detail ".strtoupper($nmKeterangan)."' onclick=\"lihatDetailNeraca('".$data['nourut']."###".$lstAkun2."','".$jmlhRowlvl3[$data['nourut'].$lstAkun2]."','3');\"";
                            $crs="cursor:pointer;";
                        }    
                }
                    switch($data['nourut']){
                        case'210003':
                        case'210004':
                             $dtRupiah[$lstAkun2]['jumlahsekarang']=$dtRupiahEstate[$lstAkun2]['jumlahsekarang'];
                             $dtRupiah[$lstAkun2]['jumlahlalu']=$dtRupiahEstate[$lstAkun2]['jumlahlalu'];
                        break;
                        case'210013':
                        //case'210014':
                            $dtRupiah[$lstAkun2]['jumlahsekarang']=$dtRupiahMill[$lstAkun2]['jumlahsekarang'];
                            $dtRupiah[$lstAkun2]['jumlahlalu']=$dtRupiahMill[$lstAkun2]['jumlahlalu'];
                        break;
                    }
                    if($tplData=='1'){
                        if(($dtRupiah[$lstAkun2]['jumlahsekarang']==0)&&($dtRupiah[$lstAkun2]['jumlahlalu']==0)){
                            continue;
                        }
                    }
                    $stream.="<tr class=rowcontent id='".$data['nourut']."_2_".$urut2an."' ".$addlink."><td colspan=1>&nbsp;</td>";
                    $stream.="<td colspan=".(5-$data['tampil'])." width='360px;'><b>".strtoupper($nmKeterangan)."</b></td>";
                    $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$lstAkun2]['jumlahsekarang'],2)."</td>
                          <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$lstAkun2]['jumlahlalu'],2)."</td>";
                    $dtRupiah[$lstAkun2]['naikturun']=$dtRupiah[$lstAkun2]['jumlahsekarang']-$dtRupiah[$lstAkun2]['jumlahlalu'];
                    @$dtRupiah[$lstAkun2]['persen']=($dtRupiah[$lstAkun2]['naikturun']/$dtRupiah[$lstAkun2]['jumlahlalu'])*100;
                    $stream.="<td align=right width='155px;' ".$styDt.">".kalominuskurung($dtRupiah[$lstAkun2]['naikturun'],2)."</td>    
                          <td align=right ".$styDt.">".kalominuskurung($dtRupiah[$lstAkun2]['persen'],2)."</td>      
                          </tr>"; 
               
                
                if(strlen($lstAkun2)==5){
                    foreach($dafAkun as $detAkun2){
                        if($lstAkun2==substr($detAkun2,0,5)){
                            if($tempData!=$lstAkun2){
                                $awalnya=0;  
                                $tempData=$lstAkun2;  
                            }
                            if(($data['nourut']=='210003')||($data['nourut']=='210004')){
                                    if(substr($lstAkun2,0,1)=='7'){
                                    $dtRupiah[$detAkun2]['jumlahlalu']=$dtRupiahEstate[$detAkun2]['jumlahlalu'];
                                    $dtRupiah[$detAkun2]['jumlahsekarang']=$dtRupiahEstate[$detAkun2]['jumlahsekarang'];
                                    }
                            }
                            if($data['nourut']=='210013'){
                                if(substr($lstAkun2,0,1)=='7'){
                                    $dtRupiah[$detAkun2]['jumlahlalu']=$dtRupiahMill[$detAkun2]['jumlahlalu'];
                                    $dtRupiah[$detAkun2]['jumlahsekarang']=$dtRupiahMill[$detAkun2]['jumlahsekarang'];
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
                            $stream.="<tr class=rowcontent  id='".$data['nourut']."_".$lstAkun2."_3_".$awalnya."'><td colspan=2>&nbsp;</td>";
                            $stream.="<td colspan=".(4-$data['tampil'])." width='260px;'>".strtoupper($optNmAkun[$detAkun2])."</b></td>";
                            $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahsekarang'],2)."</td>
                                      <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahlalu'],2)."</td>";
                            $dtRupiah[$detAkun2]['naikturun']=$dtRupiah[$detAkun2]['jumlahsekarang']-$dtRupiah[$detAkun2]['jumlahlalu'];
                            if(intval($dtRupiah[$detAkun2]['jumlahlalu'])==0){
                                $dtRupiah[$detAkun2]['persen']=0;
                            }else{
                                @$dtRupiah[$detAkun2]['persen']=($dtRupiah[$detAkun2]['naikturun']/$dtRupiah[$detAkun2]['jumlahlalu'])*100;
                            }
                            
                            $stream.="<td align=right width='155px;' ".$styDt.">".kalominuskurung($dtRupiah[$detAkun2]['naikturun'],2)."</td>    
                                      <td align=right ".$styDt.">".kalominuskurung($dtRupiah[$detAkun2]['persen'],2)."</td>      
                                      </tr>";
                        }
                    }
                }
                
                
                if(strlen($lstAkun2)==3){
                    foreach($dafAkun as $detAkun2){
                        if($lstAkun2==substr($detAkun2,0,3)){
                            if($tempData!=$lstAkun2){
                                $awalnya=0;  
                                $tempData=$lstAkun2;  
                            }
                            if(($data['nourut']=='210003')||($data['nourut']=='210004')){
                                    if(substr($lstAkun2,0,1)=='7'){
                                    $dtRupiah[$detAkun2]['jumlahlalu']=$dtRupiahEstate[$detAkun2]['jumlahlalu'];
                                    $dtRupiah[$detAkun2]['jumlahsekarang']=$dtRupiahEstate[$detAkun2]['jumlahsekarang'];
                                    }
                            }
                            if($data['nourut']=='210013'){
                                if(substr($lstAkun2,0,1)=='7'){
                                    $dtRupiah[$detAkun2]['jumlahlalu']=$dtRupiahMill[$detAkun2]['jumlahlalu'];
                                    $dtRupiah[$detAkun2]['jumlahsekarang']=$dtRupiahMill[$detAkun2]['jumlahsekarang'];
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
                                $stream.="<tr class=rowcontent   title='Klik untuk detail ".strtoupper($optNmAkun[$detAkun2])."'   id='".$data['nourut']."_".$lstAkun2."_3_".$awalnya."'><td colspan=2>&nbsp;</td>";
                                $stream.="<td colspan=".(4-$data['tampil'])." width='260px;'>".strtoupper($optNmAkun[$detAkun2])."</b></td>";
                                $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahsekarang'],2)."</td>
                                          <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$detAkun2]['jumlahlalu'],2)."</td>";
                                $dtRupiah[$detAkun2]['naikturun']=$dtRupiah[$detAkun2]['jumlahsekarang']-$dtRupiah[$detAkun2]['jumlahlalu'];
                                if(intval($dtRupiah[$detAkun2]['jumlahlalu'])==0){
                                    $dtRupiah[$detAkun2]['persen']=0;
                                }else{
                                    @$dtRupiah[$detAkun2]['persen']=($dtRupiah[$detAkun2]['naikturun']/$dtRupiah[$detAkun2]['jumlahlalu'])*100;
                                }
                                
                                $stream.="<td align=right width='155px;' ".$styDt.">".kalominuskurung($dtRupiah[$detAkun2]['naikturun'],2)."</td>    
                                          <td align=right ".$styDt.">".kalominuskurung($dtRupiah[$detAkun2]['persen'],2)."</td>      
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
                                if(($data['nourut']=='210003')||($data['nourut']=='210004')){
                                    if(substr($lstAkun2,0,1)=='7'){
                                    $dtRupiah[$detAkun2]['jumlahlalu']=$dtRupiahEstate[$detAkun2]['jumlahlalu'];
                                    $dtRupiah[$detAkun2]['jumlahsekarang']=$dtRupiahEstate[$detAkun2]['jumlahsekarang'];
                                    }
                                }
                                if($data['nourut']=='210013'){
                                    if(substr($lstAkun2,0,1)=='7'){
                                        $dtRupiah[$detAkun2]['jumlahlalu']=$dtRupiahMill[$detAkun2]['jumlahlalu'];
                                        $dtRupiah[$detAkun2]['jumlahsekarang']=$dtRupiahMill[$detAkun2]['jumlahsekarang'];
                                    }
                                }
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
                                $stream.="<tr class=rowcontent id='".$data['nourut']."_".$akuncoba."_3_".$awalnyadt."' title='Klik untuk detail ".strtoupper($optNmAkun[$akuncoba])."'><td colspan=2>&nbsp;</td>";
                                $stream.="<td colspan=".(4-$data['tampil'])." width='260px;'>".strtoupper($optNmAkun[$akuncoba])."</td>";
                                $stream.="<td align=right width='195px;'>".kalominuskurung($kalimin*$dtRupiah[$akuncoba]['jumlahsekarang'],2)."</td>
                                          <td align=right width='200px;'>".kalominuskurung($kalimin*$dtRupiah[$akuncoba]['jumlahlalu'],2)."</td>";
                                $dtRupiah[$akuncoba]['naikturun']=$dtRupiah[$akuncoba]['jumlahsekarang']-$dtRupiah[$akuncoba]['jumlahlalu'];
                                if(intval($dtRupiah[$akuncoba]['jumlahlalu'])==0){
                                    $dtRupiah[$akuncoba]['persen']=0;
                                }else{
                                    @$dtRupiah[$akuncoba]['persen']=($dtRupiah[$akuncoba]['naikturun']/$dtRupiah[$akuncoba]['jumlahlalu'])*100;
                                }   
                                $stream.="<td align=right width='155px;' ".$styDt.">".kalominuskurung($dtRupiah[$akuncoba]['naikturun'],2)."</td>    
                                          <td align=right ".$styDt.">".kalominuskurung($dtRupiah[$akuncoba]['persen'],2)."</td>      
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
                                        $stream.="<td align=right width='155px;' ".$styDt.">".kalominuskurung($dtRupiah[$detAkun2]['naikturun'],2)."</td>    
                                                  <td align=right ".$styDt.">".kalominuskurung($dtRupiah[$detAkun2]['persen'],2)."</td>      
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