<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

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
$kodelaporan='NERACA_INHOUSE';

$periodesaldo=str_replace("-", "", $periode);

#lalu
 
$periodPRF2=$tahunlalu."-".$bulan;
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

//echo "--".$periodPRF."==".$kolomPRF.">>".$captionPRF;


#query+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
$whradd=" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by cast(nourut as int) asc";
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
	$dafAkunnya[$bar->nourut][$bar->noakun]=$bar->noakun;
    // $dzArr[$bar->nourut]=$bar->noakun;
}
$arrRupiah=array();
$isiDet=array();
#awal
$sawal="select noakun,sum(awal01) as awal from ".$dbname.".keu_saldobulanan where periode='".$tahun."01' and noakun in ('".implode("','",$arrListAkun)."') ".$whradd." group by noakun order by noakun asc";// 
$rawal=fetchData($sawal);
if(count($rawal)>0){
    foreach ($rawal as $key => $val) {
        $nourut=$daftarakun[$val['noakun']];
        $arrRupiah[$nourut][$tahun]+=$val['awal'];
        $isiDet[$tahun][$val['noakun']]=$val['awal'];
    }
}
// echo"<pre>";
// print_r($arrRupiah);
// echo"</pre>";
$sawal="select noakun,sum(awal01) as awal from ".$dbname.".keu_saldobulanan where periode='".$tahunlalu."01' and noakun in ('".implode("','",$arrListAkun)."') ".$whradd." group by noakun order by noakun asc";// 
$rawal=fetchData($sawal);
if(count($rawal)>0){
    foreach ($rawal as $key => $val) {
        $nourut=$daftarakun[$val['noakun']];
        $arrRupiah[$nourut][$tahunlalu]=$val['awal'];
		$isiDet[$tahunlalu][$val['noakun']]=$val['awal'];
		 
    }
}


$sRupiah="select noakun,sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where left(periode,4)='".$tahun."' and periode<='".$periode."' ".$whradd."  and noakun in ('".implode("','",$arrListAkun)."')  group by noakun order by noakun asc";
$rRupiah=fetchData($sRupiah);
if(count($rRupiah)>0){
    foreach ($rRupiah as $key => $val) {
        $nourut=$daftarakun[$val['noakun']];
        $arrRupiah[$nourut][$tahun]+=$val['jumlah'];
		$isiDet[$tahun][$val['noakun']]+=$val['jumlah'];
		 
    }
}

// echo"<pre>";
// print_r($arrRupiah);
// echo"</pre>";
// echo"<pre>";
// print_r($dafAkunnya);
// echo"</pre>";
$sRupiah="select noakun,sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where left(periode,4)='".$tahunlalu."' and periode<='".$periodPRF2."' ".$whradd."   and noakun in ('".implode("','",$arrListAkun)."')  group by noakun order by noakun asc";
$rRupiah=fetchData($sRupiah);
if(count($rRupiah)>0){
    foreach ($rRupiah as $key => $val) {
        $nourut=$daftarakun[$val['noakun']];
        $arrRupiah[$nourut][$tahunlalu]+=$val['jumlah'];
		$isiDet[$tahunlalu][$val['noakun']]+=$val['jumlah'];
		 
    }
}

$sRupiah="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where left(periode,4)='".$tahun."' and periode<='".$periode."' ".$whradd." and left(noakun,1)>4  order by noakun asc";
$rRupiah=fetchData($sRupiah);
if(count($rRupiah)>0){
    $nourutnya='43';
    $arrRupiah[$nourutnya][$tahun]=0;
    foreach ($rRupiah as $key => $val) {
        $arrRupiah[$nourutnya][$tahun]+=$val['jumlah'];
    }
}

$sRupiah="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where left(periode,4)='".$tahunlalu."' and periode<='".$periodPRF2."' ".$whradd."  and left(noakun,1)>4  order by noakun asc";
$rRupiah=fetchData($sRupiah);
if(count($rRupiah)>0){
    $nourutnya='43';
    $arrRupiah[$nourutnya][$tahunlalu]=0;
    foreach ($rRupiah as $key => $val) {
        $arrRupiah[$nourutnya][$tahunlalu]+=$val['jumlah'];
    }
}



if(count($dzArr)>0){
    foreach($dzArr as $data){
        if($data['tipe']=='Total'){
            if($data['noakundisplay']!=''){
                $isinya=explode(",",$data['noakundisplay']);
                if(count($isinya)>0){
                    foreach ($isinya as $key => $urutannya) {
                        $arrRupiah[$data['nourut']][$tahunlalu]+=$arrRupiah[$urutannya][$tahunlalu];
                        $arrRupiah[$data['nourut']][$tahun]+=$arrRupiah[$urutannya][$tahun];
                    }
                }
            }
        }
    }
}


// exit();
 $brdnya=1;
if($param['tipe']=='html'){
    $brdnya=0;
}
$stream="<table class=sortable border=".$brdnya." cellspacing=1 height=15px >
    <thead>
        <tr class=rowheader>
        <td colspan=2 >&nbsp;</td>
       
        <td width='2%;'>&nbsp;</td>
        <td align=center>".$captionCUR."</td>
        <td align=center>".$captionPRF."</td>    
        </tr>
    </thead> 
 <tbody>";

// #ambil format mesinlaporan==========
if(!empty($dzArr))foreach($dzArr as $data){
    
    if($data['tipe']=='Header'){
        if($data['tampil']==0 and $data['nourut']==1000){
            $stream.="<tr class=rowcontent><td colspan=4><b>".$data['keterangan']."</b></td></tr>";  
        } else if ($data['tampil']==0 and $data['nourut']>1000){
		 
            $stream.="<tr class=rowcontent><td><b>".$data['keterangan']."</b></td>
            
             <td>&nbsp;</td>
                 <td colspan=2>&nbsp;</td>
            </tr>";  
		} else{
            $stream.="<tr class=rowcontent>
                <td  colspan=2><b>".$data['keterangan']."</b></td>
                 <td>&nbsp;</td>
                 <td colspan=2>&nbsp;</td>
                  
            </tr>"; 
        }
    }  else if($data['tipe']=='Total'){
        if($data['tampil']==0){
            $stream.="
            <tr class=rowcontent>
            <td  colspan=2><b>".$data['keterangan']."</b></td>
            <td >&nbsp;</td>
			 
            <td align=right><b>".number_format(abs($arrRupiah[$data['nourut']][$tahun]))."</b></td>
            <td align=right><b>".number_format(abs($arrRupiah[$data['nourut']][$tahunlalu]))."</b></td>
            </tr>
           
            "; 
        } else {
            $stream.="
            <tr class=rowcontent>
            <td colspan=2><b>".$data['keterangan']."</b></td>
            <td>&nbsp;</td>
			 
            <td align=right><b>".number_format(abs($arrRupiah[$data['nourut']][$tahun]))."</b></td>
            <td align=right><b>".number_format(abs($arrRupiah[$data['nourut']][$tahunlalu]))."</b></td>
            </tr>
           
            ";                
        }   
    } else {
        //title='Click untuk melihat detail' onclick=\"lihatDetailNeraca('".$data['noakundari']."','".$data['noakunsampai']."','".$periode."','".$periode1."','".$pt."','".$unit."',event,'".$data['nourut']."','".$kodelaporan."');\"
		$stream.="
		<tr class=rowcontent >
		 
        <td colspan=2><b>".$data['keterangan']."</b></td>
        <td>&nbsp;</td>
		 
        <td align=right><b>".number_format(abs($arrRupiah[$data['nourut']][$tahun]))."</b></td>
        <td align=right><b>".number_format(abs($arrRupiah[$data['nourut']][$tahunlalu]))."</b></td>
		</tr>"; 
		if(count($dafAkunnya[$data['nourut']])>0){
			foreach ($dafAkunnya[$data['nourut']] as $urutnya => $akunnya) {
				$optnmakun=makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$akunnya."'");
				$stream.="
					<tr class=rowcontent >
					
					<td>".$akunnya."</td>
					<td>".$optnmakun[$akunnya]."</td>
					<td>&nbsp;</td>	
					<td align=right>".number_format($isiDet[$tahun][$akunnya])."</td>
					<td align=right>".number_format($isiDet[$tahunlalu][$akunnya])."</td>
					</tr>";  
			}
		}
	}	
}

$stream.= "</tbody></table>";
if($param['tipe']=='html'){
    echo $stream;
}
if($param['tipe']=='pdf'){
    $dompdf = new Dompdf();
    $dompdf->loadHtml($stream);
    $dompdf->setPaper('A4', 'potrait');
    $dompdf->render();
    $dompdf->stream("laporan",array("Attachment"=>0));
}
if($param['tipe']=='excel'){
    $tglSkrg=date("His");
    $nop_="neraca_inhouse_".$periode."_".$pt."_".$tglSkrg;
    if(strlen($stream)>0){
        if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                        @unlink('tempExcel/'.$file);
                }
            }	
            closedir($handle);
        }
        $handle=fopen("tempExcel/".$nop_.".xls",'w');
        if(!fwrite($handle,$stream)) {
            echo "<script language=javascript1.2>
            parent.window.alert('Can't convert to excel format');
            </script>";
            exit;
        } else {
            echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls';
            </script>";
        }
        fclose($handle);
    }  
}
?>