<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$proses = checkPostGet('proses', '');
$pt = checkPostGet('pt', '');
$kdorg = checkPostGet('kdorg', '');
$tahun1 = checkPostGet('tahun1', '');
$tahunsd1 = checkPostGet('tahunsd1', '');
$tahun2 = checkPostGet('tahun2', '');
$tahunsd2 = checkPostGet('tahunsd2', '');
$tahun3 = checkPostGet('tahun3', '');
$tahunsd3 = checkPostGet('tahunsd3', '');
$bulan1 = checkPostGet('bulan1', '');
$bulansd1 = checkPostGet('bulansd1', '');
$bulan2 = checkPostGet('bulan2', '');
$bulansd2 = checkPostGet('bulansd2', '');
$bulan3 = checkPostGet('bulan3', '');
$bulansd3 = checkPostGet('bulansd3', '');

$tahun4 = checkPostGet('tahun4', '');
$tahunsd4 = checkPostGet('tahunsd4', '');
$bulan4 = checkPostGet('bulan4', '');
$bulansd4 = checkPostGet('bulansd4', '');


$tahunytd = checkPostGet('tahunytd1', '');
$tahunsdytd = checkPostGet('tahunsdytd1', '');
$bulanytd = checkPostGet('bulanytd1', '');
$bulansdytd = checkPostGet('bulansdytd1', '');

$sumjlh1=$sumjlh2=$sumjlh3=$sumjlh4='';
$wheresawal1=$wheresawal2=$wheresawal3=$wheresawal4='';
$wherejurnal1=$wherejurnal2=$wherejurnal3=$wherejurnal4='';
$sumjlhytd=$wheresawalytd=$wherejurnalytd='';

$periode1 = $tahun1."-".$bulan1;
$periode2 = $tahun2."-".$bulan2;
$periode3 = $tahun3."-".$bulan3;
$periode4 = $tahun4."-".$bulan4;

$periodesd1 = $tahunsd1."-".$bulansd1;
$periodesd2 = $tahunsd2."-".$bulansd2;
$periodesd3 = $tahunsd3."-".$bulansd3;
$periodesd4 = $tahunsd4."-".$bulansd4;

$periodeytd = $tahunytd."-".$bulanytd;
$periodesdytd = $tahunsdytd."-".$bulansdytd;


$sumjlh1 = " ,sum(awal".$bulan1.") as jumlah ";
$wheresawal1 = " and periode = '".$tahun1."".$bulan1."'";
$wherejurnal1 = " and periode BETWEEN '".$tahun1."-".$bulan1."' and '".$tahunsd1."-".$bulansd1."'";

$sumjlh2 = " ,sum(awal".$bulan2.") as jumlah ";
$wheresawal2 = " and periode = '".$tahun2."".$bulan2."'";
$wherejurnal2 = " and periode BETWEEN '".$tahun2."-".$bulan2."' and '".$tahunsd2."-".$bulansd2."'";

$sumjlh3 = " ,sum(awal".$bulan3.") as jumlah ";
$wheresawal3 = " and periode = '".$tahun3."".$bulan3."'";
$wherejurnal3 = " and periode BETWEEN '".$tahun3."-".$bulan3."' and '".$tahunsd3."-".$bulansd3."'";

$sumjlh4 = " ,sum(awal".$bulan4.") as jumlah ";
$wheresawal4 = " and periode = '".$tahun4."".$bulan4."'";
$wherejurnal4 = " and periode BETWEEN '".$tahun4."-".$bulan4."' and '".$tahunsd4."-".$bulansd4."'";

$sumjlhytd = " ,sum(awal01) as jumlah ";
$wheresawalytd = " and periode = '".$tahunytd."".$bulanytd."'";
$wherejurnalytd = " and periode BETWEEN '".$tahunytd."-".$bulanytd."' and '".$tahunsdytd."-".$bulansdytd."'";

$where='';
if($pt!=''){
	$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."')";
}
if($kdorg!=''){
	$where.=" and kodeorg='".$kdorg."'";
}

$kodelaporan ='BSL3';
$dataArray = array();

$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $dataArray[$bar->nourut]['nourut']=$bar->nourut;
    $dataArray[$bar->nourut]['tampil']=$bar->variableoutput;    
    $dataArray[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dataArray[$bar->nourut]['keterangan']=$bar->keterangandisplay;
    }else{
        $dataArray[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dataArray[$bar->nourut]['noakundisplay']=$bar->noakundisplay; #= ini buat total
    $dataArray[$bar->nourut]['rubahoperatr']=$bar->rubahoperatr;
    $dataArray[$bar->nourut]['exception']=$bar->exception;
    $dataArray[$bar->nourut]['exceptiondigit']=$bar->exceptiondigit;
}

$daftarakun=array();
$nouruttemp='';
# ambil jumlah
$str="select count(*) as jumlah,nourut from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' group by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$jumlahdaftar[$bar->nourut]=$bar->jumlah;
	$dataArray[$bar->nourut]['jumlahakun']=$bar->jumlah;
}

# ambil daftar noakun
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
			$dataArray[$bar->nourut]['noakun'].=$bar->noakun.',';
		}else{
			$daftarakun[$bar->nourut].=$bar->noakun;
			$dataArray[$bar->nourut]['noakun'].=$bar->noakun;
		}
	}else{
		if($jumlahdaftar[$bar->nourut]==1){ # hanya 1 akun saja
			@$daftarakun[$bar->nourut].=$bar->noakun;
			@$dataArray[$bar->nourut]['noakun'].=$bar->noakun;
		} else{
			@$daftarakun[$bar->nourut].=$bar->noakun.',';
			@$dataArray[$bar->nourut]['noakun'].=$bar->noakun.',';
		}
	}
	$nouruttemp=$bar->nourut;
}

$stream='';
if($proses=='excel' or $proses=='pdf'){
	$stream.="<div align=center><b>BALANCE SHEET - COMPARATIVE</b></div><br>";
	$stream.="<table class=sortable border=1 cellspacing=0 width=100%>";
}else{
	$stream.="<table class=sortable border=0 cellspacing=1 width='100%;'>";
}
$stream.="<thead>
        <tr class=rowheader>
        <td colspan=6></td>
        <td align=center width='150px;'>".$periode1." s/d ".$periodesd1."</td>
        <td align=center width='150px;'>".$periode2." s/d ".$periodesd2."</td>
        <td align=center width='150px;'>".$periode3." s/d ".$periodesd3."</td>
        <td align=center width='150px;'>".$periode4." s/d ".$periodesd4."</td>
		<td align=center width='150px;'>YTD ".$periodesdytd."</td>   
        </tr>
    </thead>
	<tbody>";
$jlhkolom=7;

#ambil nilai saldo awal
$str="select noakun ".$sumjlh1." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal1." ".$where." group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(!empty($dataArray))foreach($dataArray as $data){
		$arrdata=explode(',',@$data['noakun']);
		foreach($arrdata as $key){
			if($bar->noakun==$key and $bar->noakun!=''){
				@$dataArray[$data['nourut']]['sawalperiode1']+=($bar->jumlah*-1); 
			}
		}
	}
}

$str="select noakun ".$sumjlh2." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal2." ".$where." group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(!empty($dataArray))foreach($dataArray as $data){
		$arrdata=explode(',',@$data['noakun']);
		foreach($arrdata as $key){
			if($bar->noakun==$key and $bar->noakun!=''){
				@$dataArray[$data['nourut']]['sawalperiode2']+=($bar->jumlah*-1); 
			}
		}
	}
}

$str="select noakun ".$sumjlh3." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal3." ".$where." group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(!empty($dataArray))foreach($dataArray as $data){
		$arrdata=explode(',',@$data['noakun']);
		foreach($arrdata as $key){
			if($bar->noakun==$key and $bar->noakun!=''){
				@$dataArray[$data['nourut']]['sawalperiode3']+=($bar->jumlah*-1); 
			}
		}
	}
}

$str="select noakun ".$sumjlh4." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal4." ".$where." group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(!empty($dataArray))foreach($dataArray as $data){
		$arrdata=explode(',',@$data['noakun']);
		foreach($arrdata as $key){
			if($bar->noakun==$key and $bar->noakun!=''){
				@$dataArray[$data['nourut']]['sawalperiode4']+=($bar->jumlah*-1); 
			}
		}
	}
}           

$str="select noakun ".$sumjlhytd." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawalytd." ".$where." group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(!empty($dataArray))foreach($dataArray as $data){
		$arrdata=explode(',',@$data['noakun']);
		foreach($arrdata as $key){
			if($bar->noakun==$key and $bar->noakun!=''){
				@$dataArray[$data['nourut']]['sawalperiodeytd']+=($bar->jumlah*-1); 
			}
		}
	}
}   

#ambil nilai jurnal
$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal1." ".$where." group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(!empty($dataArray))foreach($dataArray as $data){
		$arrdata=explode(',',@$data['noakun']);
		foreach($arrdata as $key){
			if($bar->noakun==$key and $bar->noakun!=''){
				@$dataArray[$data['nourut']]['dataperiode1']+=($bar->jumlah*-1); 
			}
		}
	}        
} 

$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal2." ".$where." group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(!empty($dataArray))foreach($dataArray as $data){
		$arrdata=explode(',',@$data['noakun']);
		foreach($arrdata as $key){
			if($bar->noakun==$key and $bar->noakun!=''){
				@$dataArray[$data['nourut']]['dataperiode2']+=($bar->jumlah*-1); 
			}
		}
	}        
} 

$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal3." ".$where." group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(!empty($dataArray))foreach($dataArray as $data){
		$arrdata=explode(',',@$data['noakun']);
		foreach($arrdata as $key){
			if($bar->noakun==$key and $bar->noakun!=''){
				@$dataArray[$data['nourut']]['dataperiode3']+=($bar->jumlah*-1); 
			}
		}
	}        
} 

$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal4." ".$where." group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(!empty($dataArray))foreach($dataArray as $data){
		$arrdata=explode(',',@$data['noakun']);
		foreach($arrdata as $key){
			if($bar->noakun==$key and $bar->noakun!=''){
				@$dataArray[$data['nourut']]['dataperiode4']+=($bar->jumlah*-1); 
			}
		}
	}        
}

$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnalytd." ".$where." group by noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if(!empty($dataArray))foreach($dataArray as $data){
		$arrdata=explode(',',@$data['noakun']);
		foreach($arrdata as $key){
			if($bar->noakun==$key and $bar->noakun!=''){
				@$dataArray[$data['nourut']]['dataperiodeytd']+=($bar->jumlah*-1); 
			}
		}
	}        
} 

#= buat total disini
if(!empty($dataArray))foreach($dataArray as $data){
	if($data['tipe']=='Total'){
		#= explode data
		$arrdata=explode(',',$data['noakundisplay']);
		foreach($arrdata as $key){
			@$dataArray[$data['nourut']]['totalperiode1']+=($dataArray[$key]['dataperiode1']+$dataArray[$key]['sawalperiode1']);
			@$dataArray[$data['nourut']]['totalperiode2']+=($dataArray[$key]['dataperiode2']+$dataArray[$key]['sawalperiode2']);
			@$dataArray[$data['nourut']]['totalperiode3']+=($dataArray[$key]['dataperiode3']+$dataArray[$key]['sawalperiode3']);
			@$dataArray[$data['nourut']]['totalperiode4']+=($dataArray[$key]['dataperiode4']+$dataArray[$key]['sawalperiode4']);
			@$dataArray[$data['nourut']]['totalperiodeytd']+=($dataArray[$key]['dataperiodeytd']+$dataArray[$key]['sawalperiodeytd']);
		}
	}
}

// echo"<pre>";
// print_r($arrTotal);
// echo"</pre>";
//exit();
#ambil format mesinlaporan
if(!empty($dataArray))foreach($dataArray as $data){
    if($data['tipe']=='Header'){
		$stream.="<tr class=rowcontent>
					<td colspan=".strlen($data['nourut']).">&nbsp;</td>
					<td colspan=".(11-strlen($data['nourut']))."><b>".$data['keterangan']."</b></td>
				 </tr>";  
    }else if($data['tipe']=='Total'){
		$stream.="<tr class=rowcontent>
			<td colspan=6></td>
			<td colspan=5></td>
			</tr>
		<tr class=rowcontent>
			<td colspan=".strlen($data['nourut']).">&nbsp;</td>
			<td colspan=".(6-strlen($data['nourut']))."><b>".$data['keterangan']."</b></td>";
			$stream.="<td align=right><b>".@number_format($data['totalperiode1'],0)."</b></td>";    
			$stream.="<td align=right><b>".@number_format($data['totalperiode2'],0)."</b></td>";    
			$stream.="<td align=right><b>".@number_format($data['totalperiode3'],0)."</b></td>";    
			$stream.="<td align=right><b>".@number_format($data['totalperiode4'],0)."</b></td>";    
			$stream.="<td align=right><b>".@number_format($data['totalperiodeytd'],0)."</b></td>";    
		$stream.="</tr>
		<tr class=rowcontent>
			<td colspan=11></td>
		</tr>
		"; 
    }else{ #ini buat detail isi
		$stream.="
		<tr class=rowcontent style=cursor:pointer title='Click untuk melihat detail' onclick=\"viewdetail('".$data['nourut']."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','html','bsl3','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\">
			<td colspan=".strlen($data['nourut']).">&nbsp;</td>
			<td colspan=".(6-strlen($data['nourut'])).">".@$data['keterangan']."</td>
			<td align=right width='150px;'>".@number_format($data['dataperiode1']+$data['sawalperiode1'],0)."</td>
			<td align=right width='150px;'>".@number_format($data['dataperiode2']+$data['sawalperiode2'],0)."</td>    
			<td align=right width='150px;'>".@number_format($data['dataperiode3']+$data['sawalperiode3'],0)."</td>    
			<td align=right width='150px;'>".@number_format($data['dataperiode4']+$data['sawalperiode4'],0)."</td>    
			<td align=right width='150px;'>".@number_format($data['dataperiodeytd']+$data['sawalperiodeytd'],0)."</td>    
		</tr>";      		
	}
}

$stream.= "</tbody></tfoot></tfoot></table>";
$date=date('d-m-Y H:i:s');	
$stream.= "<i>Print by : ".$_SESSION['standard']['username']." ".$date."</i>";
	 
switch ($proses) {
######PREVIEW
    case 'preview':
        echo $stream;
        break;
	case'pdf':
		$dompdf = new Dompdf();
		$dompdf->loadHtml($stream);
		$dompdf->setPaper('A3', 'landscape');
		$dompdf->render();
		$dompdf->stream("BSL3",array("Attachment"=>0));
	break;
######EXCEL	
    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "BSL3" . $kdorg;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
        break;		
}
?>