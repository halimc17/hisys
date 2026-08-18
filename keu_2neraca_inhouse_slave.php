<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;
error_reporting(0);
$method = checkPostGet('method','');
if($_GET['tipe']!=''){
    $param=$_GET;
}else{
    $param=$_POST;
}
$periode=$param['periode'];
$pt=$param['pt'];
$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $namapt=strtoupper($bar->namaorganisasi);
}
#++++++++++++++++++++++++++++++++++++++++++
$kodelaporan='LABARUGIKONSOL';

$periodesaldo=str_replace("-", "", $periode);

#lalu
 
$periodPRF2=$tahun."-".$bulan;
#sekarang
$t=mktime(0,0,0,substr($periodesaldo,4,2),15,substr($periodesaldo,0,4));
$periodCUR=date('Ym',$t);
$periodCUR2=substr($periodesaldo,0,4).'-'.substr($periodesaldo,4,2);
$kolomCUR="awal".date('m',$t);

#captionsekarang============================
$t=mktime(0,0,0,substr($periodesaldo,4,2),15,substr($periodesaldo,0,4));
$captionCUR=date('M-Y',$t);

#captionlalu
$t=mktime(0,0,0,12,15,substr($periodesaldo,0,4)-1);
$t1=mktime(0,0,0,$bulan,15,substr($periodesaldo,0,4)-1);
if($periode1=='akhir')$captionPRF=date('M-Y',$t); else $captionPRF=$captionPRF=date('M-Y',$t1);


$whradd=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
$whradd3=" and unit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
$whradd2=" and substr(kodeorg,1,4)  in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
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
    $dzArr[$bar->nourut]['noakundisplay']=$bar->noakundisplay; #= ini buat total
    $dzArr[$bar->nourut]['rubahoperatr']=$bar->rubahoperatr;
    $dzArr[$bar->nourut]['exception']=$bar->exception;
    $dzArr[$bar->nourut]['exceptiondigit']=$bar->exceptiondigit;
    $dzArr[$bar->nourut]['operator']=$bar->operator;
}



$daftarakun=array();
$nouruttemp='';
#= ambil jumlah
$str="select count(*) as jumlah,nourut from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' group by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $jumlahdaftar[$bar->nourut]=$bar->jumlah;
    $dzArr[$bar->nourut]['jumlahakun']=$bar->jumlah;
}
$arrListAkun=array();
#= ambil daftar noakun
$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' order by nourut asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $arrListAkun[$bar->noakun]=$bar->noakun;
    $daftarakun[$bar->noakun]=$bar->nourut;
  
}
$arrRupiah=array();
#awal
$sawal="select noakun,sum(awal01) as awal from ".$dbname.".keu_saldobulanan where periode='".$tahun."01' and noakun in ('".implode("','",$arrListAkun)."') ".$whradd." group by noakun order by noakun asc";// 
$rawal=fetchData($sawal);
if(count($rawal)>0){
    foreach ($rawal as $key => $val) {
        $nourut=$daftarakun[$val['noakun']];
        $arrRupiah[$tahun][$nourut]+=$val['awal'];
    }
}

$periodeawal=$tahun."-01";

$periodeawallalu=$tahunlalu."-01";
$periodelalu = $tahunlalu."-".substr($periode,5,2);

$optopr  = makeOption($dbname,'keu_5mesinlaporandt','nourut,operator',"namalaporan='LABARUGIKONSOL'");

$sRupiah="select periode,noakun,sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where left(periode,4)='".$tahun."' and periode between '".$periodeawal."' and  '".$periode."' ".$whradd."  and noakun in ('".implode("','",$arrListAkun)."')  group by periode,noakun order by periode,noakun asc";
$rRupiah=fetchData($sRupiah);
if(count($rRupiah)>0){
    foreach ($rRupiah as $key => $val) {
        
        $nourut=$daftarakun[$val['noakun']];
           
        if (@$optopr[$nourut]=='-') {
            @$arrRupiah[$val['periode']][$nourut]+=$val['jumlah']*-1;
            @$actytd[$nourut]+=$val['jumlah']*-1;
        }
        else
        {
            @$arrRupiah[$val['periode']][$nourut]+=$val['jumlah'];
            @$actytd[$nourut]+=$val['jumlah'];
        }
      
        
    }
}


$sRupiah="select periode,noakun,sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where left(periode,4)='".$tahunlalu."' and periode between '".$periodeawallalu."' and  '".$periodelalu."' ".$whradd."  and noakun in ('".implode("','",$arrListAkun)."')  group by periode,noakun order by periode,noakun asc";
$rRupiah=fetchData($sRupiah);
if(count($rRupiah)>0){
    foreach ($rRupiah as $key => $val) {
        
        $nourut=$daftarakun[$val['noakun']];
           
        if ($optopr[$nourut]=='-') {
            $arrRupiah[$periodelalu][$nourut]+=$val['jumlah']*-1;
            $actytdlalu[$nourut]+=$val['jumlah']*-1;
        }
        else
        {
            $arrRupiah[$periodelalu][$nourut]+=$val['jumlah'];
            $actytdlalu[$nourut]+=$val['jumlah']*-1;
        }
      
        
    }
}


 $sRupiah="select substr(tanggal,1,7) as periode,sum(kgwbnetto) as jumlah from ".$dbname.".kebun_spb_vw where substr(tanggal,1,7) between '".$periodeawal."' and  '".$param['periode']."' ".$whradd."  group by periode order by periode asc";
            $rRupiah=fetchData($sRupiah);
            if(count($rRupiah)>0){
                foreach ($rRupiah as $key => $val) {
                    $data['nourut']='16202';
                    if ($optopr[$data['nourut']]=='-') {
                        @$arrtonase[$val['periode']][$data['nourut']]+=$val['jumlah']*-1;
                        @$actytdtonase[$data['nourut']]+=$val['jumlah']*-1;
                    }
                    else
                    {
                        @$arrtonase[$val['periode']][$data['nourut']]+=$val['jumlah'];
                        @$actytdtonase[$data['nourut']]+=$val['jumlah'];
                    }


                }
            }

 $sRupiah="select substr(tanggal,1,7) as periode,sum(oer/1000) as netcpo,sum(oerpk/1000) as netpk,sum(tbsmasuknetto/1000) as ffbpros from ".$dbname.".pabrik_produksi where substr(tanggal,1,7) between '".$periodeawal."' and  '".$param['periode']."' ".$whradd."  group by periode order by periode asc";
            $rRupiah=fetchData($sRupiah);
            if(count($rRupiah)>0){
                foreach ($rRupiah as $key => $val) {
                    $data['nourut']='18002';
                    if ($optopr[$data['nourut']]=='-') {
                        @$arrnetcpo[$val['periode']]+=$val['netcpo']*-1;
                        @$actytdnetcpo['18001']+=$val['netcpo']*-1;

                        @$arrnetpk[$val['periode']]+=$val['netpk']*-1;
                        @$actytdnetpk['18002']+=$val['netpk']*-1;

                        @$arrnetffbpros[$val['periode']]+=$val['ffbpros']*-1;
                        @$actytdnetffbpros['18006']+=$val['ffbpros']*-1;
                    }
                    else
                    {
                        @$arrnetcpo[$val['periode']]+=$val['netcpo'];
                        @$actytdnetcpo['18001']+=$val['netcpo'];

                        @$arrnetpk[$val['periode']]+=$val['netpk'];
                        @$actytdnetpk['18002']=$val['netpk'];

                        @$arrnetffbpros[$val['periode']]+=$val['ffbpros'];
                        @$actytdnetffbpros['18006']+=$val['ffbpros'];
                    }


                }
            }


$sRupiah="select substr(tanggal,1,7) as periode,sum(kgnetto/1000) as kgnet from ".$dbname.".kebun_tbskud where substr(tanggal,1,7) between '".$periodeawal."' and  '".$param['periode']."' ".$whradd3."  group by periode order by periode asc";
            $rRupiah=fetchData($sRupiah);
            if(count($rRupiah)>0){
                foreach ($rRupiah as $key => $val) {;
                    if ($optopr[$data['nourut']]=='-') {
                        @$arrnetffbpur[$val['periode']]+=$val['kgnet']*-1;
                        @$actytdnetffbpur['18005']+=$val['kgnet']*-1;
                    }
                    else
                    {
                        @$arrnetffbpur[$val['periode']]+=$val['kgnet'];
                        @$actytdnetffbpur['18005']+=$val['kgnet'];

                    }


                }
            }


$sRupiah="select substr(tanggal,1,7) as periode,sum(kgwbnetto/1000) as jumlah from ".$dbname.".kebun_spb_vw  where substr(tanggal,1,7) between '".$periodeawal."' and  '".$param['periode']."' ".$whradd." and intiplasma='I' and posting=1 group by periode order by periode asc";
            $rRupiah=fetchData($sRupiah);
            if(count($rRupiah)>0){
                foreach ($rRupiah as $key => $val) {

                    if ($optopr[$data['nourut']]=='-') {
                        @$arrffb[$val['periode']]+=$val['jumlah']*-1;
                        @$actytdffb+=$val['jumlah']*-1;
   
                    }
                    else
                    {
                        @$arrffb[$val['periode']]+=$val['jumlah'];
                        @$actytdffb[$data['nourut']]+=$val['jumlah'];

                     
                    }


                }
            }
$arrdate=explode('-', $periodeawal);
$arrdate2=explode('-', $param['periode']);
$sRupiah="select tahun as periode,sum(luasareaproduktif) as jumlah,statusblok from ".$dbname.".setup_blok_tahunan where tahun between '".$arrdate[0].$arrdate[1]."' and  '".$arrdate2[0].$arrdate2[1]."' ".$whradd2." group by periode,statusblok order by periode asc";
            $rRupiah=fetchData($sRupiah);
            if(count($rRupiah)>0){
                foreach ($rRupiah as $key => $val) {

                       
                        $val['periode']=substr($val['periode'],0,4).'-'.substr($val['periode'],4,2);
                        if ($val['statusblok']=='TM') {
                            @$arrmt[$val['periode']]+=$val['jumlah'];
                            @$actytdmt['21000']+=$val['jumlah'];
                            if ($val['periode']==$periode) {
                                @$actytdmt['21000']=$val['jumlah'];
                            }
                        }
                        else if ($val['statusblok']=='TBM') {
                            @$arrimt[$val['periode']]+=$val['jumlah'];
                            @$actytdimt['22000']+=$val['jumlah'];
                            if ($val['periode']==$periode) {
                                @$actytdmt['22000']=$val['jumlah'];
                            }
                        }

                }
            }
//cari avg age
$sRupiah="select tahun as periode ,(".$tahun."-tahuntanam) as umur,count(kodeorg) as jumlahblok,sum((2023-tahuntanam))/count(kodeorg) as avg_age from ".$dbname.".setup_blok_tahunan where tahun between '".$arrdate[0].$arrdate[1]."' and  '".$arrdate2[0].$arrdate2[1]."' ".$whradd2." group by tahun order by periode asc";
            $rRupiah=fetchData($sRupiah);
            if(count($rRupiah)>0){
                foreach ($rRupiah as $key => $val) {

                       
                        $val['periode']=substr($val['periode'],0,4).'-'.substr($val['periode'],4,2);
                        @$arrage[$val['periode']]=$val['avg_age'];
                        @$actytdage['23000']+=$val['avg_age'];

                }
            }

if(count(@$dzArr)>0){
    foreach(@$dzArr as $data){

       

            if($data['noakundisplay']!=''){
                $isinya=explode(",",$data['noakundisplay']);
                if(count($isinya)>0){
                    foreach ($isinya as $key => $urutannya) {
                                $amin=substr($urutannya,0,1);
                                $urutannya=str_replace('-','', $urutannya);
                                 for ($i=1; $i < 13; $i++) { 
                                    $periode=$tahun."-".addzero($i,2);
                                    $periodelalu=$tahunlalu."-".addzero($i,2);
                                        if ($amin=='-') {
                                            //$arrRupiah[$data['nourut']][$tahunlalu]-=$arrRupiah[$urutannya][$tahunlalu];
                                            @$arrRupiah[$periode][$data['nourut']]-=$arrRupiah[$periode][$urutannya];
                                            @$actytd[$data['nourut']]-=$arrRupiah[$periode][$urutannya];
                                            @$actytdlalu[$data['nourut']]-=$arrRupiah[$periodelalu][$urutannya];
                                        }
                                        else
                                        {

                                            //$arrRupiah[$data['nourut']][$tahunlalu]+=$arrRupiah[$urutannya][$tahunlalu];
                                            @$arrRupiah[$periode][$data['nourut']]+=$arrRupiah[$periode][$urutannya];
                                            @$actytd[$data['nourut']]+=$arrRupiah[$periode][$urutannya];
                                            @$actytdlalu[$data['nourut']]+=$arrRupiah[$periodelalu][$urutannya];


                                        }
                                }

                               

                     
                                
                                
                    }
                }
            }

    /*        echo "<pre>";
            print_r($arrRupiah);
            echo "</pre>";*/
        
    }
}

$tahunlalu=$tahun-1;
$tahun2lalu=$tahun-2;
$captionCUR='This Month';
$captionPRF='Year To Date';
$stream="<table class=sortable border=0 cellspacing=1 height=15px width=100%>
    <thead>
        <tr class=rowheader>
        <td align=center width='59%;' rowspan=3>DESCRIPTIONS</td>
        <td align=center rowspan=2 colspan=2>FY ".$tahun2lalu."</td>
        <td align=center rowspan=2 colspan=2>FY ".$tahunlalu."</td>
        <td align=center colspan=24>Monthly ".$tahun."</td>
        <td align=center rowspan=2 colspan=2>Actual<br>YTD ".$tahun."</td>
        <td align=center rowspan=2 colspan=2>AOP<br>YTD ".$tahun."</td>
        <td align=center rowspan=2 colspan=2>Actual<br>YTD ".$tahunlalu."</td>
        <td align=center  rowspan=2 colspan=2>Variance</td>
        <tr>";
        for ($i=1; $i <13; $i++) { 
            $stream.="<td align=center colspan=2>".numToMonth(addzero($i,2),'I','short')."</td>";
        }
       
        $stream.="</tr>

         <tr>";
         
        for ($i=1; $i <18; $i++) { 
            $stream.="<td align=center>Rp</td>";
            $stream.="<td align=center>%</td>";
        }
        $stream.="<td align=center>Act ".$tahun." <br>vs AOP</td>";
        $stream.="<td align=center>Act ".$tahun." <br>vs LY</td>";
        
       
        $stream.="</tr>
        </tr>
    </thead> 
 <tbody>";


// #ambil format mesinlaporan==========
if(!empty(@$dzArr))foreach($dzArr as $data){
    
    if(@$data['tipe']=='Header'){
        if(@$data['tampil']==0 and @$data['nourut']==1000){
            @$stream.="<tr class=rowcontent><td colspan=4><b>".@$data['keterangan']."</b></td></tr>";  
        } else if (@$data['tampil']==0 and @$data['nourut']>1000){
         
            @$stream.="<tr class=rowcontent><td><b>".@$data['keterangan']."</b></td>
                 <td colspan=36>&nbsp;</td>
            </tr>";  
        } else{
            @$stream.="<tr class=rowcontent>
                <td ><b>".@$data['keterangan']."</b></td>
                 <td colspan=36>&nbsp;</td>
                  
            </tr>"; 
        }
    }  else if(@$data['tipe']=='Total'){
        if(@$data['tampil']==0){
            @$stream.="
            <tr class=rowcontent>
            <td><b>".@$data['keterangan']."</b></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>";
            for ($i=1; $i < 13; $i++) { 
                 @$periode=@$tahun."-".addzero($i,2);

                 


                 @$stream.="<td align=right><b>".number_format(@$arrRupiah[@$periode][@$data['nourut']])."</b></td>";
                 @$stream.="<td align=right><b></b></td>";

            }
           
            @$stream.=" </tr>"; 
        } else {
            @$stream.="
            <tr class=rowcontent>
            <td><b>".@$data['keterangan']."</b></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>";
            for ($i=1; $i < 13; $i++) { 
                 @$periode=@$tahun."-".addzero($i,2);
                 if (@$data['nourut']=='16998') {
                    @$arrRupiah[@$periode][@$data['nourut']]=@$arrtonase[@$periode]['16202'];
                   //@$actytd[@$data['nourut']]=@$actytdtonase[@$data['nourut']];

               }

                 @$stream.="<td align=right><b>".number_format(@$arrRupiah[@$periode][@$data['nourut']])."</b></td>";
                 $persen=(@$arrRupiah[@$periode][@$data['nourut']]/@$arrRupiah[@$periode]['11001'])*100;
                 if (substr($data['nourut'],0,2)=='15') {
                         $persen=(@$arrRupiah[@$periode][@$data['nourut']]/@$arrRupiah[@$periode]['15101'])*100;
                         //$persen=@$arrRupiah[@$periode]['15101'];
                     }
                 if (substr($data['nourut'],0,2)=='16') {
                         $persen=(@$arrRupiah[@$periode][@$data['nourut']]/@$arrRupiah[@$periode]['16998'])*100;
                         //$persen=@$arrRupiah[@$periode]['15101'];
                     }
                 @$stream.="<td align=right><b>".number_format($persen)."</b></td>";

            }
            $stream.="<td align=right><b>".number_format(@$actytd[$data['nourut']])."</b></td>";
            $stream.="<td align=right><b>0</b></td>";
            $stream.="<td align=right><b>0</b></td>";
            $stream.="<td align=right><b>0</b></td>";
            $stream.="<td align=right><b>".number_format(@$actytdlalu[$data['nourut']])."</b></td>";
            $stream.="<td align=right><b>0</b></td>";
            $stream.="<td align=right><b>0</b></td>";
            if (@$actytdlalu[$data['nourut']]==0) {
                @$actvsly[$data['nourut']]=0;
            }
            else
            {
            @$actvsly[$data['nourut']]=((@$actytd[$data['nourut']]-@$actytdlalu[$data['nourut']])/@$actytdlalu[$data['nourut']])*100;
            }
            @$stream.="<td align=right><b>".number_format(@$actvsly[@$data['nourut']])."</b></td>";

            @$stream.="</tr>";                
        }   
        } else {
            
            @$stream.="
            <tr class=rowcontent >
            <td>".@$data['keterangan']."</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>";
                for ($i=1; $i < 13; $i++) { 
                     @$periode=@$tahun."-".addzero($i,2);
                     if (@$data['nourut']=='16202') {
                         @$arrRupiah[@$periode][@$data['nourut']]=@$arrtonase[@$periode][@$data['nourut']];
                         @$actytd[@$data['nourut']]=@$actytdtonase[@$data['nourut']];
                         
                     }

                     if (@$data['nourut']=='17003') {
                       // $arrRupiah[$periode][$data['nourut']]=$arrRupiah[$periode]['15202']/$arrtonase[$periode]['16202'];
                        @$arrRupiah[@$periode][@$data['nourut']]=@$arrRupiah[@$periode]['15202']/@$arrtonase[@$periode]['16202'];
                     }

                     if (@$data['nourut']=='18001') {
                       // $arrRupiah[$periode][$data['nourut']]=$arrRupiah[$periode]['15202']/$arrtonase[$periode]['16202'];
                        @$arrRupiah[@$periode][@$data['nourut']]=$arrnetcpo[$periode];
                        @$actytd[@$data['nourut']]=@$actytdnetcpo[@$data['nourut']];
                     }

                     if (@$data['nourut']=='18002') {
                       
                        @$arrRupiah[@$periode][@$data['nourut']]=$arrnetpk[$periode];
                         @$actytd[@$data['nourut']]=@$actytdnetpk[@$data['nourut']];
                     }

                     if (@$data['nourut']=='18003') {
                       // $arrRupiah[$periode][$data['nourut']]=$arrRupiah[$periode]['15202']/$arrtonase[$periode]['16202'];
                        @$arrRupiah[@$periode][@$data['nourut']]=$arrffb[$periode];
                     }

                    if (@$data['nourut']=='18005') {
                      
                        @$arrRupiah[@$periode][@$data['nourut']]=$arrnetffbpur[$periode];
                         @$actytd[@$data['nourut']]=@$actytdnetffbpur[@$data['nourut']];
                     }

                    if (@$data['nourut']=='18006') {
                      
                        @$arrRupiah[@$periode][@$data['nourut']]=$arrnetffbpros[$periode];
                         @$actytd[@$data['nourut']]=@$actytdnetffbpros[@$data['nourut']];
                     }


                     if (@$data['nourut']=='21000') {
                      
                        @$arrRupiah[@$periode][@$data['nourut']]=$arrmt[$periode];
                        @$actytd[@$data['nourut']]=@$actytdmt[@$data['nourut']];
                     }

                    if (@$data['nourut']=='22000') {
                      
                        @$arrRupiah[@$periode][@$data['nourut']]=$arrimt[$periode];
                        @$actytd[@$data['nourut']]=@$actytdimt[@$data['nourut']];
                     }

                     if (@$data['nourut']=='23000') {
                      
                        @$arrRupiah[@$periode][@$data['nourut']]=$arrage[$periode];
                        @$actytd[@$data['nourut']]=@$actytdage[@$data['nourut']];
                     }

                     $persen=(@$arrRupiah[@$periode][@$data['nourut']]/@$arrRupiah[@$periode]['11001'])*100;
                     if (substr($data['nourut'],0,2)=='15') {
                         $persen=(@$arrRupiah[@$periode][@$data['nourut']]/@$arrRupiah[@$periode]['15101'])*100;
                         //$persen=@$arrRupiah[@$periode]['15101'];
                     }

                     if (substr($data['nourut'],0,2)=='16') {
                         $persen=(@$arrRupiah[@$periode][@$data['nourut']]/@$arrRupiah[@$periode]['16998'])*100;
                         //$persen=@$arrRupiah[@$periode]['15101'];
                     }
                     $stream.="<td align=right>".number_format(@$arrRupiah[@$periode][@$data['nourut']])."</td>";
                     $stream.="<td align=right>".number_format($persen)."</td>";

                }
                 $stream.="<td align=right>".number_format(@$actytd[$data['nourut']])."</td>";
                 

                 $stream.="<td align=right>0</td>";
                 $stream.="<td align=right>0</td>";
                 $stream.="<td align=right>0</td>";
                 $stream.="<td align=right>".number_format(@$actytdlalu[$data['nourut']])."</td>";
                 $stream.="<td align=right>0</td>";
                 $stream.="<td align=right>0</td>";
                 if (@$actytdlalu[$data['nourut']]==0) {
                    @$actvsly[$data['nourut']]=0;
                }
                else
                {
                @$actvsly[$data['nourut']]=((@$actytd[$data['nourut']]-@$actytdlalu[$data['nourut']])/@$actytdlalu[$data['nourut']])*100;
                }

                $stream.="<td align=right>".number_format(@$actvsly[$data['nourut']])."</td>";
                $stream.="</tr>";       
        }  
}

$stream.= "</tbody></table>";



if($param['tipe']=='excel'){
    $nop=$kodelaporan."_".$param['kodept']."_".$param['periode'].".xls";
    $xls = new HtmlExcel();
    $xls->setCss($css);
    $xls->addSheet($namalaporan, $stream);
    // $xls->addSheet("Report", $tab2);
    $xls->headers($nop);
    echo $xls->buildFile();
} else if ($param['tipe']=='pdf') {
    $dompdf = new Dompdf();
    $dompdf->loadHtml($stream);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream($namalaporan.$kodept,array("Attachment"=>0));
} else {
    echo $stream;
}


?>