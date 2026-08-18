<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
// $pt=		isset($_POST['pt'])? $_POST['pt']: '';
// $unit=		isset($_POST['unit'])? $_POST['unit']: '';//kebun
// $periode=	isset($_POST['periode'])? $_POST['periode']: '';
// $periode1=	isset($_POST['periode1'])? $_POST['periode1']: '';
// $gudang=	isset($_POST['gudang'])? $_POST['gudang']: '';
// $revisi=	isset($_POST['revisi'])? $_POST['revisi']: '';


$pt = checkPostGet('pt', '');
$regional = checkPostGet('regional', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');
$periodepembanding = checkPostGet('periodepembanding', '');
$periode1 = checkPostGet('periode1', '');
$revisi = checkPostGet('revisi', '');
$gudang = checkPostGet('gudang', '');
$tipe = checkPostGet('tipe', '');



$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namapt=strtoupper($bar->namaorganisasi);
}
#++++++++++++++++++++++++++++++++++++++++++
$kodelaporan='BALANCE SHEET';
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

$t=mktime(0,0,0,substr($periodesaldo,4,2),15,substr($periodesaldo,0,4));
$captionCUR=date('M-Y',$t);

#captionlalu (tahun sebelumnya desember)
$t=mktime(0,0,0,12,15,substr($periodesaldo,0,4)-1);
$t1=mktime(0,0,0,$bulan,15,substr($periodesaldo,0,4)-1);
if($periode1=='akhir')$captionPRF=date('M-Y',$t); else $captionPRF=$captionPRF=date('M-Y',$t1);






#= periode pembanding
$periodepembandingtampung=$periodepembanding;
$periodesaldo=str_replace("-", "", $periodepembanding);
$t=mktime(0,0,0,substr($periodesaldo,4,2)+1,15,substr($periodesaldo,0,4));
$periodepembanding=date('Ym',$t);
$kolompembanding="awal".date('m',$t);
$t=mktime(0,0,0,substr($periodesaldo,4,2),15,substr($periodesaldo,0,4));
$captionperiodepembanding=date('M-Y',$t);

// echo $periodepembanding;




// echo $periodtahunlalu._.$captionperiodepembanding;
#query+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

/*
if($unit=='')
    $where=" kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
else 
    $where=" kodeorg='".$unit."'";
*/

// echo $pt._.$regional._.$unit;exit();
if($regional=='' && $unit==''){
    $where="  kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
} else if($regional!='' && $unit=='') {
    $where="  kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
} else {
    $where="  kodeorg='".$unit."'";
}



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

#= ambil daftar noakun
$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' order by nourut asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	
	if($nouruttemp==$bar->nourut){
		$no++;	
	}else{
		$no=1;
	}
	
	if($nouruttemp==$bar->nourut){
		if($no<$jumlahdaftar[$bar->nourut]){
			$daftarakun[$bar->nourut].=$bar->noakun.',';
			$dzArr[$bar->nourut]['noakun'].=$bar->noakun.',';
		}else{
			$daftarakun[$bar->nourut].=$bar->noakun;
			$dzArr[$bar->nourut]['noakun'].=$bar->noakun;
		}
	}else{
		
		if($jumlahdaftar[$bar->nourut]==1){ #= hanya 1 akun saja
			$daftarakun[$bar->nourut].=$bar->noakun;
			$dzArr[$bar->nourut]['noakun'].=$bar->noakun;
		} else{
			$daftarakun[$bar->nourut].=$bar->noakun.',';
			$dzArr[$bar->nourut]['noakun'].=$bar->noakun.',';
		}
	}
	$nouruttemp=$bar->nourut;
}

//$dzArr[$bar->nourut]['noakun']

// echo"<pre>";
// print_r($dzArr);
// echo"</pre>";
// exit();
$jlhkolom=8;

$stream="";



	
if($tipe=='html'){
	$stream.="<div style='position:fixed;'><table class=sortable border=0 cellspacing=1>";
    $stream.="<thead>";
        $stream.="<tr class=rowheader>";
        $stream.="<td width='395px;'></td>";
        $stream.="<td align=center width='200px;'>".$captionCUR."</td>";
        $stream.="<td align=center width='200px;'>".$captionperiodepembanding."</td>";
        $stream.="<td align=center width='200px;'>".$captionPRF."</td>   "; 
        $stream.="</tr>";
    $stream.="</thead><tbody></tbody>";
    $stream.="</table>";
	$stream.="</div><br>";
	$stream.="<table class=sortable border=0 cellspacing=1><thead><tr><td colspan=7 width='800px;'></td></tr></thead><tbody>";
}else{
		$stream.="<table class=sortable border=0 cellspacing=1><tbody>";
	$stream.="<tr class=rowheader>";
        $stream.="<td colspan=5></td>";
        $stream.="<td align=center width='200px;'>".$captionCUR."</td>";
        $stream.="<td align=center width='200px;'>".$captionperiodepembanding."</td>";
        $stream.="<td align=center width='200px;'>".$captionPRF."</td>   "; 
	$stream.="</tr>";
}
	
if(!empty($dzArr))foreach($dzArr as $data){
   
   $addbetween='';
	if(($data['jumlahakun']>0 or $data['jumlahakun']!='') and $data['tipe']=='Detail'){
	   $addbetween=" and noakun in (".$data['noakun'].")";

		
		$st12="select sum(".$kolomPRF.") as kemarin
			from ".$dbname.".keu_saldobulanan where 1=1 ".$addbetween."  and periode='".$periodPRF."' and ".$where;
		
		$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
		$res12->setFetchMode(PDO::FETCH_OBJ);
		$jlhlalu=0;
		while($ba12=$res12->fetch())
		{
			$jlhlalu=$ba12->kemarin;
		}     
		$dzArr[$data['nourut']]['jumlahlalu']=$jlhlalu;
		
		if($revisi==0){ // kalo revisi 0, ambil data dari saldo bulanan
			$st12="select sum(".$kolomCUR.") as sekarang
				from ".$dbname.".keu_saldobulanan where 1=1 ".$addbetween."  and (periode='".$periodCUR."') and ".$where;
					// echo $st12;
			$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
			$res12->setFetchMode(PDO::FETCH_OBJ);
			$jlhsekarang=0;
			while($ba12=$res12->fetch())
			{
				$jlhsekarang=$ba12->sekarang;
			}      
			$dzArr[$data['nourut']]['jumlahsekarang']=$jlhsekarang; 
		}
		
		
		#= periode pembanding
		$st12="select sum(".$kolompembanding.") as sekarang
			from ".$dbname.".keu_saldobulanan where 1=1 ".$addbetween."  and (periode='".$periodepembanding."') and ".$where;
				// echo $st12;
		$res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
		$res12->setFetchMode(PDO::FETCH_OBJ);
		$jlhsekarang=0;
		while($ba12=$res12->fetch())
		{
			$jlhsekarang=$ba12->sekarang;
		}      
		$dzArr[$data['nourut']]['periodepembanding']=$jlhsekarang; 
	
		
	} 
}

if($revisi>0){ // kalo revisi > 0, ambil data dari jurnal
    $st12="select noakun, sum(jumlah) as jumlah
        from ".$dbname.".keu_jurnaldt_vw where periode between '".$periodPRF2."' 
        and '".$periodCUR2."' and ".$where." ".$addEx." and revisi <= '".$revisi."' group by noakun";  
    $res12=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
	$res12->setFetchMode(PDO::FETCH_OBJ);
    $jlhsekarang=0;
    while($ba12=$res12->fetch())
    {
        if(!empty($dzArr))foreach($dzArr as $data){
            if(($ba12->noakun>=$data['noakundari'])&&($ba12->noakun<=$data['noakunsampai'])){
				if(!isset($dzArr[$data['nourut']]['jumlahtemp'])) $dzArr[$data['nourut']]['jumlahtemp']=0;
                $dzArr[$data['nourut']]['jumlahtemp']+=$ba12->jumlah; 
                $dzArr[$data['nourut']]['jumlahsekarang']=$dzArr[$data['nourut']]['jumlahlalu']+$dzArr[$data['nourut']]['jumlahtemp'];
            } else {
                $dzArr[$data['nourut']]['jumlahsekarang']=0;    
			}
        }        
    }                 
}




#= buat total disini
if(!empty($dzArr))foreach($dzArr as $data){
	if($data['tipe']=='Total'){
		#= explode data
		$arrdata=explode(',',$data['noakundisplay']);
		foreach($arrdata as $key){
			// $arrlist[]=$key;
			$dzArr[$data['nourut']]['jumlahsekarang']+=$dzArr[$key]['jumlahsekarang']; 	
			$dzArr[$data['nourut']]['jumlahlalu']+=$dzArr[$key]['jumlahlalu'];
			$dzArr[$data['nourut']]['periodepembanding']+=$dzArr[$key]['periodepembanding'];
		}
	}
}






#ambil format mesinlaporan==========
if(!empty($dzArr))foreach($dzArr as $data){
    
    if($data['tipe']=='Header'){
        if($data['tampil']==0){
            $stream.="<tr class=rowcontent><td colspan=".$jlhkolom.">&nbsp;</td></tr>";  
            $stream.="<tr class=rowcontent><td colspan=".$jlhkolom."><b>".$data['keterangan']."</b></td></tr>";  
        }else{
            $stream.="<tr class=rowcontent>
                <td colspan=".$data['tampil']."></td>
                <td colspan=".($jlhkolom-$data['tampil'])."><b>".$data['keterangan']."</b></td>
            </tr>"; 
        }
    }  else if($data['tipe']=='Total'){
        if($data['tampil']==0){
            $stream.="<tr class=rowcontent>
                <td colspan=5></td>
                <td colspan=3>------------------------------------------------------------------------------------------------------------------------</td>
                </tr>
            <tr class=rowcontent>
                <td colspan=5><b>".$data['keterangan']."</b></td>
                <td align=right><b>".number_format($data['jumlahsekarang'],2)."</b></td>
                <td align=right><b>".number_format($data['periodepembanding'],2)."</b></td>
                <td align=right><b>".number_format($data['jumlahlalu'],2)."</b></td>    
            </tr>
           
            "; 
        } else {
            $stream.="<tr class=rowcontent>
                <td colspan=5></td>
                <td colspan=".($jlhkolom-5)." align>------------------------------------------------------------------------------------------------------------------------</td>
            </tr>
            <tr class=rowcontent>
                <td colspan=".$data['tampil'].">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td colspan=".(5-$data['tampil'])."><b>".$data['keterangan']."</b></td>
                <td align=right width='200px;'><b>".number_format($data['jumlahsekarang'],2)."</b></td>
                <td align=right width='200px;'><b>".number_format($data['periodepembanding'],2)."</b></td>
                <td align=right width='200px;'><b>".number_format($data['jumlahlalu'],2)."</b></td>    
            </tr>
            <tr class=rowcontent><td colspan=".$jlhkolom.">&nbsp;</td></tr>
            ";                
        }   
    } else {
		$stream.="
		<tr class=rowcontent title='Click untuk melihat detail' onclick=\"lihatDetailNeraca('".$data['noakundari']."','".$data['noakunsampai']."','".$periode."','".$periode1."','".$pt."','".$unit."',event,'".$data['nourut']."','".$kodelaporan."','".$periodepembandingtampung."');\">
			<td colspan=".($data['tampil'])."></td>
			<td colspan=".(5-$data['tampil']).">&nbsp;&nbsp;&nbsp; ".$data['keterangan']."</td>
			<td align=right width='200px;'>".number_format($data['jumlahsekarang'],2)."</td>
			<td align=right width='200px;'>".number_format($data['periodepembanding'],2)."</td>
			<td align=right width='200px;'>".number_format($data['jumlahlalu'],2)."</td>    
		</tr>"; 
	}	
    if($unit==''){
            $sKd="select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='HOLDING'";
            $qKd=$owlPDO->query($sKd) or die(print " Gagal: ".PDOException::getMessage());
			$qKd->setFetchMode(PDO::FETCH_ASSOC);
            $rKd=$qKd->fetch();
            $unit=$rKd['kodeorganisasi'];
            $sCek="select * from ".$dbname.".keu_4rasio where periode='".$periode."' and kodeorg='".$unit."'";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$rCek=owlBaris($qCek);
            if($rCek!=0){
                $sUpdate="update ".$dbname.".keu_4rasio set 
                          total_ekuitas='".floatval(@$dzArr['3062']['jumlahsekarang'])."',
                          total_asset='".floatval(@$dzArr['1395']['jumlahsekarang'])."',
                          aset_lancar='".floatval(@$dzArr['1060']['jumlahsekarang'])."',
                          piutang_lancar='".floatval(@$dzArr['1900']['jumlahsekarang'])."',
                          persediaan='".floatval((@$dzArr['1040']['jumlahsekarang']+@$dzArr['1041']['jumlahsekarang']))."',
                          liabilitas_pendek='".floatval(@$dzArr['1900']['jumlahsekarang'])."',
                          hutang_lancar='".floatval(@$dzArr['1430']['jumlahsekarang'])."',
                          liabilitas_panjang='".floatval(@$dzArr['2090']['jumlahsekarang'])."',
                          total_liabilitas='".floatval(@$dzArr['2120']['jumlahsekarang'])."',
                          hutangjksthun='".floatval(@$dzArr['1470']['jumlahsekarang'])."'
                          where periode='".$periode."' and kodeorg='".$unit."'";
            }else{
                $sUpdate="insert into ".$dbname.".keu_4rasio (`kodeorg`,`periode`,`total_ekuitas`,`total_asset`,`aset_lancar`,`piutang_lancar`,`persediaan`,`liabilitas_pendek`,`hutang_lancar`,`liabilitas_panjang`,`hutangjksthun`,`total_liabilitas`) values 
                         ('".$unit."','".$periode."','".floatval($dzArr['3062']['jumlahsekarang'])."','".floatval($dzArr['1395']['jumlahsekarang'])."','".floatval($dzArr['1060']['jumlahsekarang'])."','".floatval($dzArr['1900']['jumlahsekarang'])."',
                          '".floatval(($dzArr['1040']['jumlahsekarang']+$dzArr['1041']['jumlahsekarang']))."','".floatval($dzArr['1900']['jumlahsekarang'])."','".floatval($dzArr['1430']['jumlahsekarang'])."','".floatval($dzArr['2090']['jumlahsekarang'])."'
                          ,'".floatval($dzArr['2120']['jumlahsekarang'])."','".floatval($dzArr['1470']['jumlahsekarang'])."')";
            }
			try{
				$owlPDO->exec($sUpdate);
			}catch (PDOException $e){
				exit('warning :'.$sUpdate."___".$e->getMessage());
			}
            $unit="";
    }    
}

$stream.= "</tbody></tfoot></tfoot></table>";

if($tipe=='excel'){
	$nop_="Neraca-".$pt."_".$periodesaldo;
	if(strlen($stream)>0)
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
		 if(!fwrite($handle,$stream))
		 {
		  echo "<script language=javascript>
				parent.window.alert('Can't convert to excel format');
				</script>";
		   exit;
		 }
		 else
		 {
		  echo "<script language=javascript>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
		 }
		fclose($handle);
	}
} else if ($tipe=='pdf') {
	$dompdf = new Dompdf();
	$dompdf->loadHtml($stream);
	$dompdf->setPaper('A4', 'landscape');
	$dompdf->render();
	$dompdf->stream("Neraca",array("Attachment"=>0));
} else {
	echo $stream;
}


?>