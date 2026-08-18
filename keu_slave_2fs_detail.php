<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

#JANGAN DI COMMENT, DIPAKAI DI BAWAH !!!!!!!!!!!!!!!!!!!!!!!!!!!!
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$proses = checkPostGet('proses', '');
$pt = checkPostGet('pt', '');
$kdorg = checkPostGet('kdorg', '');
$periode1 = checkPostGet('periode1', '');
$periode2 = checkPostGet('periode2', '');
$periode3 = checkPostGet('periode3', '');
$periode4 = checkPostGet('periode4', '');
$periodesd1 = checkPostGet('periodesd1', '');
$periodesd2 = checkPostGet('periodesd2', '');
$periodesd3 = checkPostGet('periodesd3', '');
$periodesd4 = checkPostGet('periodesd4', '');
$periodeytd = checkPostGet('periodeytd', '');
$periodesdytd = checkPostGet('periodesdytd', '');

$jenis = checkPostGet('jenis', '');
$nourut = checkPostGet('nourut', '');
$tipe = checkPostGet('tipe', '');

$arr1=explode("-",$periode1);
$tahun1=$arr1[0]; $bulan1=$arr1[1];

$arr2=explode("-",$periode2);
$tahun2=$arr2[0]; $bulan2=$arr2[1];

$arr3=explode("-",$periode3);
$tahun3=$arr3[0]; $bulan3=$arr3[1];

$arr4=explode("-",$periode4);
$tahun4=$arr4[0]; $bulan4=$arr4[1];

$arrytd=explode("-",$periodeytd);
$tahunytd=$arrytd[0]; $bulanytd=$arrytd[1];


$sumjlh1=$sumjlh2=$sumjlh3=$sumjlh4='';
$wheresawal1=$wheresawal2=$wheresawal3=$wheresawal4='';
$wherejurnal1=$wherejurnal2=$wherejurnal3=$wherejurnal4='';
$sumjlhytd=$wheresawalytd=$wherejurnalytd='';


$sumjlh1 = " ,sum(awal".$bulan1.") as jumlah ";
$wheresawal1 = " and periode = '".$tahun1."".$bulan1."'";
$wherejurnal1 = " and periode BETWEEN '".$periode1."' and '".$periodesd1."'";

$sumjlh2 = " ,sum(awal".$bulan2.") as jumlah ";
$wheresawal2 = " and periode = '".$tahun2."".$bulan2."'";
$wherejurnal2 = " and periode BETWEEN '".$periode2."' and '".$periodesd2."'";

$sumjlh3 = " ,sum(awal".$bulan3.") as jumlah ";
$wheresawal3 = " and periode = '".$tahun3."".$bulan3."'";
$wherejurnal3 = " and periode BETWEEN '".$periode3."' and '".$periodesd3."'";

$sumjlh4 = " ,sum(awal".$bulan4.") as jumlah ";
$wheresawal4 = " and periode = '".$tahun4."".$bulan4."'";
$wherejurnal4 = " and periode BETWEEN '".$periode4."' and '".$periodesd4."'";


$sumjlhytd = " ,sum(awal01) as jumlah ";
$wheresawalytd = " and periode = '".$tahunytd."01'";
$wherejurnalytd = " and periode BETWEEN '".$periodeytd."' and '".$periodesdytd."'";

switch ($proses) {
	case'viewdetail_no':
		$where='';
		if($pt!=''){
			$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."')";
		}
		if($kdorg!=''){
			$where.=" and kodeorg='".$kdorg."'";
		}
		
		/* #periodenya tidak pakai sd jadi di buat ulang disini
		$wherejurnal1 = " and periode = '".$periode1."'";
		$wherejurnal2 = " and periode = '".$periode2."'";
		$wherejurnal3 = " and periode = '".$periode3."'";
		$wherejurnalytd = " and periode BETWEEN '".$tahun3."-01' and '".$periode3."'"; */

		
		$kodelaporan ='NO';
		# ambil daftar noakun
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' and nourut ='".$nourut."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$whrnoakun[$bar['noakun']]=$bar['noakun'];
		}
		if(count(@$whrnoakun)==''){
			exit("Warning : Tidak memiliki data detail !");
		}
		
		$stream='';
		
		if($tipe=='html'){
			$stream.="<img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='View Excel' onclick=\"viewdetail('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','excel','no','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";
			
			$stream.="&nbsp;<img src=images/pdf.jpg class=resicon class=zImgBtn height='30'  title='View PDF' onclick=\"pdf('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','pdf','no','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";			
		}
		
		if($tipe=='excel' or $tipe=='pdf'){
			$stream.="<div align=center><b>NON OPERATING INCOME</b></div><br>";
			$stream.="<table class=sortable border=1 cellspacing=0 width=100%>";
		}else{
			$stream.="<table class=sortable border=0 cellspacing=1 width='100%;'>";
		}
		
		$stream.="<thead>
				<tr class=rowheader>
				<td align=center width=30px>No</td>
				<td align=center width=70px>" . $_SESSION['lang']['noakun'] . "</td>
				<td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
				<td align=center>".$periode1." s/d ".$periodesd1."</td>
				<td align=center>".$periode2." s/d ".$periodesd2."</td>    
				<td align=center>".$periode3." s/d ".$periodesd3."</td>    
				<td align=center>".$periode4." s/d ".$periodesd4."</td>    
				<td align=center>YTD ".$periodesdytd."</td>
				</tr>
			</thead>
			<tbody>";
		$jlhkolom=6;
		$arrTotal=array();
		$arrTotalSawal=array();
		$dataArray=array();
		
		
		#ambil jurnal
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal1." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode1']+=($bar['jumlah']*-1);
			@$arrTotal['periode1']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal2." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode2']+=($bar['jumlah']*-1);
			@$arrTotal['periode2']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal3." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode3']+=($bar['jumlah']*-1);
			@$arrTotal['periode3']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal4." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode4']+=($bar['jumlah']*-1);
			@$arrTotal['periode4']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnalytd." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiodeytd']+=($bar['jumlah']*-1);
			@$arrTotal['periodeytd']+=($bar['jumlah']*-1);
		} 
		
// echo"<pre>";
// print_r($dataArray);
// print_r($arrTotal);
// echo"</pre>";
// exit();
		$no='';
		$namaakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		if(!empty($dataArray)){
			foreach($dataArray as $noakun => $data){
				$no++;
				$stream.="
					<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$noakun."</td>
						<td colspan=0>".$namaakun[$noakun]."</td>
						<td align=right>".@number_format($data['dataperiode1']+$data['sawalperiode1'],0)."</td>
						<td align=right>".@number_format($data['dataperiode2']+$data['sawalperiode2'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode3']+$data['sawalperiode3'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode4']+$data['sawalperiode4'],0)."</td>    
						<td align=right>".@number_format($data['dataperiodeytd']+$data['sawalperiodeytd'],0)."</td>    
					</tr>";      		
			}
		$stream.="
			<tr class=rowcontent>
				<td align=center></td>
				<td align=center></td>
				<td><b>Total</b></td>
				<td align=right><b>".@number_format($arrTotal['periode1']+$arrTotalSawal['periode1'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode2']+$arrTotalSawal['periode2'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode3']+$arrTotalSawal['periode3'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode4']+$arrTotalSawal['periode4'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periodeytd']+$arrTotalSawal['periodeytd'],0)."</b></td>
				
			</tr>";
		}
		$stream.= "</tbody></tfoot></tfoot></table>";
		$date=date('d-m-Y H:i:s');	
		$stream.= "<i>Print by : ".$_SESSION['standard']['username']." ".$date."</i>";
				
		if($tipe=='html'){
			echo $stream;
		}elseif($tipe=='pdf'){
			
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("detail_NO",array("Attachment"=>0));
		}else{
			$nop_ = "Detail_NO";
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
		}
	break;
	case'viewdetail_ga':
		$where='';
		if($pt!=''){
			$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."')";
		}
		if($kdorg!=''){
			$where.=" and kodeorg='".$kdorg."'";
		}
		
		/* #periodenya tidak pakai sd jadi di buat ulang disini
		$wherejurnal1 = " and periode = '".$periode1."'";
		$wherejurnal2 = " and periode = '".$periode2."'";
		$wherejurnal3 = " and periode = '".$periode3."'";
		$wherejurnalytd = " and periode BETWEEN '".$tahun3."-01' and '".$periode3."'"; */

		
		$kodelaporan ='GENADM';
		# ambil daftar noakun
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' and nourut ='".$nourut."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$whrnoakun[$bar['noakun']]=$bar['noakun'];
		}
		if(count(@$whrnoakun)==''){
			exit("Warning : Tidak memiliki data detail !");
		}
		
		$stream='';
		
		if($tipe=='html'){
			$stream.="<img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='View Excel' onclick=\"viewdetail('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','excel','ga','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";
			
			$stream.="&nbsp;<img src=images/pdf.jpg class=resicon class=zImgBtn height='30'  title='View PDF' onclick=\"pdf('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','pdf','ga','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";			
		}
		
		if($tipe=='excel' or $tipe=='pdf'){
			$stream.="<div align=center><b>SELLING AND GENERAL ADMINISTRATIVE EXPENSE</b></div><br>";
			$stream.="<table class=sortable border=1 cellspacing=0 width=100%>";
		}else{
			$stream.="<table class=sortable border=0 cellspacing=1 width='100%;'>";
		}
		
		$stream.="<thead>
				<tr class=rowheader>
				<td align=center width=30px>No</td>
				<td align=center width=70px>" . $_SESSION['lang']['noakun'] . "</td>
				<td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
				<td align=center>".$periode1." s/d ".$periodesd1."</td>
				<td align=center>".$periode2." s/d ".$periodesd2."</td>    
				<td align=center>".$periode3." s/d ".$periodesd3."</td>    
				<td align=center>".$periode4." s/d ".$periodesd4."</td>    
				<td align=center>YTD ".$periodesdytd."</td>
				</tr>
			</thead>
			<tbody>";
		$jlhkolom=6;
		$arrTotal=array();
		$arrTotalSawal=array();
		$dataArray=array();
		
		
		
		
		#ambil jurnal
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal1." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode1']+=($bar['jumlah']*-1);
			@$arrTotal['periode1']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal2." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode2']+=($bar['jumlah']*-1);
			@$arrTotal['periode2']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal3." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode3']+=($bar['jumlah']*-1);
			@$arrTotal['periode3']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal4." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode4']+=($bar['jumlah']*-1);
			@$arrTotal['periode4']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnalytd." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiodeytd']+=($bar['jumlah']*-1);
			@$arrTotal['periodeytd']+=($bar['jumlah']*-1);
		} 
		
// echo"<pre>";
// print_r($dataArray);
// print_r($arrTotal);
// echo"</pre>";
// exit();
		$no='';
		$namaakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		if(!empty($dataArray)){
			foreach($dataArray as $noakun => $data){
				$no++;
				$stream.="
					<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$noakun."</td>
						<td colspan=0>".$namaakun[$noakun]."</td>
						<td align=right>".@number_format($data['dataperiode1']+$data['sawalperiode1'],0)."</td>
						<td align=right>".@number_format($data['dataperiode2']+$data['sawalperiode2'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode3']+$data['sawalperiode3'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode4']+$data['sawalperiode4'],0)."</td>    
						<td align=right>".@number_format($data['dataperiodeytd']+$data['sawalperiodeytd'],0)."</td>    
					</tr>";      		
			}
		$stream.="
			<tr class=rowcontent>
				<td align=center></td>
				<td align=center></td>
				<td><b>Total</b></td>
				<td align=right><b>".@number_format($arrTotal['periode1']+$arrTotalSawal['periode1'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode2']+$arrTotalSawal['periode2'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode3']+$arrTotalSawal['periode3'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode4']+$arrTotalSawal['periode4'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periodeytd']+$arrTotalSawal['periodeytd'],0)."</b></td>
				
			</tr>";
		}
		$stream.= "</tbody></tfoot></tfoot></table>";
		$date=date('d-m-Y H:i:s');	
		$stream.= "<i>Print by : ".$_SESSION['standard']['username']." ".$date."</i>";
				
		if($tipe=='html'){
			echo $stream;
		}elseif($tipe=='pdf'){
			
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("detail_ga",array("Attachment"=>0));
		}else{
			$nop_ = "Detail_GA";
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
		}
	break;
	case'viewdetail_cogs':
		$where='';
		if($pt!=''){
			$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."')";
		}
		if($kdorg!=''){
			$where.=" and kodeorg='".$kdorg."'";
		}
		
		#periodenya tidak pakai sd jadi di buat ulang disini
		#$wherejurnal1 = " and periode = '".$periode1."'";
		#$wherejurnal2 = " and periode = '".$periode2."'";
		#$wherejurnal3 = " and periode = '".$periode3."'";
		#$wherejurnalytd = " and periode BETWEEN '".$tahun4."-01' and '".$periodesd4."'";
		
		$kodelaporan ='COGS';
		# ambil daftar noakun
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' and nourut ='".$nourut."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$whrnoakun[$bar['noakun']]=$bar['noakun'];
		}
		if(count(@$whrnoakun)==''){
			exit("Warning : Tidak memiliki data detail !");
		}
		
		$stream='';
		
		if($tipe=='html'){
			$stream.="<img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='View Excel' onclick=\"viewdetail('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','excel','cogs','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";
			
			$stream.="&nbsp;<img src=images/pdf.jpg class=resicon class=zImgBtn height='30'  title='View PDF' onclick=\"pdf('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','pdf','cogs','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";			
		}
		
		if($tipe=='excel' or $tipe=='pdf'){
			$stream.="<div align=center><b>COST OF GOODS SOLD</b></div><br>";
			$stream.="<table class=sortable border=1 cellspacing=0 width=100%>";
		}else{
			$stream.="<table class=sortable border=0 cellspacing=1 width='100%;'>";
		}
		
		$stream.="<thead>
				<tr class=rowheader>
				<td align=center width=30px>No</td>
				<td align=center width=70px>" . $_SESSION['lang']['noakun'] . "</td>
				<td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
				<td align=center>".$periode1." s/d ".$periodesd1."</td>
				<td align=center>".$periode2." s/d ".$periodesd2."</td>    
				<td align=center>".$periode3." s/d ".$periodesd3."</td>    
				<td align=center>".$periode4." s/d ".$periodesd4."</td>    
				<td align=center>YTD ".$periodesdytd."</td>
				</tr>
			</thead>
			<tbody>";
		$jlhkolom=6;
		$arrTotal=array();
		$arrTotalSawal=array();
		$dataArray=array();
		
		#ambil jurnal
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal1." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode1']+=($bar['jumlah']*-1);
			@$arrTotal['periode1']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal2." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode2']+=($bar['jumlah']*-1);
			@$arrTotal['periode2']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal3." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode3']+=($bar['jumlah']*-1);
			@$arrTotal['periode3']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal4." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode4']+=($bar['jumlah']*-1);
			@$arrTotal['periode4']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnalytd." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiodeytd']+=($bar['jumlah']*-1);
			@$arrTotal['periodeytd']+=($bar['jumlah']*-1);
		} 
		
// echo"<pre>";
// print_r($dataArray);
// print_r($arrTotal);
// echo"</pre>";
// exit();
		$no='';
		$namaakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		if(!empty($dataArray)){
			foreach($dataArray as $noakun => $data){
				$no++;
				$stream.="
					<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$noakun."</td>
						<td colspan=0>".$namaakun[$noakun]."</td>
						<td align=right>".@number_format($data['dataperiode1']+$data['sawalperiode1'],0)."</td>
						<td align=right>".@number_format($data['dataperiode2']+$data['sawalperiode2'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode3']+$data['sawalperiode3'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode4']+$data['sawalperiode4'],0)."</td>    
						<td align=right>".@number_format($data['dataperiodeytd']+$data['sawalperiodeytd'],0)."</td>    
					</tr>";      		
			}
		$stream.="
			<tr class=rowcontent>
				<td align=center></td>
				<td align=center></td>
				<td><b>Total</b></td>
				<td align=right><b>".@number_format($arrTotal['periode1']+$arrTotalSawal['periode1'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode2']+$arrTotalSawal['periode2'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode3']+$arrTotalSawal['periode3'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode4']+$arrTotalSawal['periode4'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periodeytd']+$arrTotalSawal['periodeytd'],0)."</b></td>
				
			</tr>";
		}
		$stream.= "</tbody></tfoot></tfoot></table>";
		$date=date('d-m-Y H:i:s');	
		$stream.= "<i>Print by : ".$_SESSION['standard']['username']." ".$date."</i>";
				
		if($tipe=='html'){
			echo $stream;
		}elseif($tipe=='pdf'){
			
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("detail_cogs",array("Attachment"=>0));
		}else{
			$nop_ = "Detail_COGS";
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
		}
	break;
	case'viewdetail_pl':
		$where='';
		if($pt!=''){
			$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."')";
		}
		if($kdorg!=''){
			$where.=" and kodeorg='".$kdorg."'";
		}
		
		#periodenya tidak pakai sd jadi di buat ulang disini khusus untuk PL
		#$wherejurnal1 = " and periode = '".$periode1."'";
		#$wherejurnal2 = " and periode = '".$periode2."'";
		#$wherejurnal3 = " and periode = '".$periode3."'";
		#$wherejurnalytd = " and periode BETWEEN '".$tahun4."-01' and '".$periodesd4."'";

		
		$kodelaporan ='PL';
		# ambil daftar noakun
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' and nourut ='".$nourut."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$whrnoakun[$bar['noakun']]=$bar['noakun'];
		}
		if(count(@$whrnoakun)==''){
			exit("Warning : Tidak memiliki data detail !");
		}
		
		$stream='';
		
		if($tipe=='html'){
			$stream.="<img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='View Excel' onclick=\"viewdetail('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','excel','pl','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";
			
			$stream.="&nbsp;<img src=images/pdf.jpg class=resicon class=zImgBtn height='30'  title='View PDF' onclick=\"pdf('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','pdf','pl','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";			
		}
		
		if($tipe=='excel' or $tipe=='pdf'){
			$stream.="<div align=center><b>PROFIT LOSS</b></div><br>";
			$stream.="<table class=sortable border=1 cellspacing=0 width=100%>";
		}else{
			$stream.="<table class=sortable border=0 cellspacing=1 width='100%;'>";
		}
		
		$stream.="<thead>
				<tr class=rowheader>
				<td align=center width=30px>No</td>
				<td align=center width=70px>" . $_SESSION['lang']['noakun'] . "</td>
				<td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
				<td align=center>".$periode1." s/d ".$periodesd1."</td>
				<td align=center>".$periode2." s/d ".$periodesd2."</td>    
				<td align=center>".$periode3." s/d ".$periodesd3."</td>    
				<td align=center>".$periode4." s/d ".$periodesd4."</td>    
				<td align=center>YTD ".$periodesdytd."</td>
				</tr>
			</thead>
			<tbody>";
		$jlhkolom=6;
		$arrTotal=array();
		$arrTotalSawal=array();
		$dataArray=array();
		
		
		
		
		#ambil jurnal
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal1." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode1']+=($bar['jumlah']*-1);
			@$arrTotal['periode1']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal2." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode2']+=($bar['jumlah']*-1);
			@$arrTotal['periode2']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal3." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode3']+=($bar['jumlah']*-1);
			@$arrTotal['periode3']+=($bar['jumlah']*-1);
		} 
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal4." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode4']+=($bar['jumlah']*-1);
			@$arrTotal['periode4']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnalytd." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiodeytd']+=($bar['jumlah']*-1);
			@$arrTotal['periodeytd']+=($bar['jumlah']*-1);
		} 
		
// echo"<pre>";
// print_r($dataArray);
// print_r($arrTotal);
// echo"</pre>";
// exit();
		$no='';
		$namaakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		if(!empty($dataArray)){
			foreach($dataArray as $noakun => $data){
				$no++;
				$stream.="
					<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$noakun."</td>
						<td colspan=0>".$namaakun[$noakun]."</td>
						<td align=right>".@number_format($data['dataperiode1']+$data['sawalperiode1'],0)."</td>
						<td align=right>".@number_format($data['dataperiode2']+$data['sawalperiode2'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode3']+$data['sawalperiode3'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode4']+$data['sawalperiode4'],0)."</td>    
						<td align=right>".@number_format($data['dataperiodeytd']+$data['sawalperiodeytd'],0)."</td>    
					</tr>";      		
			}
		$stream.="
			<tr class=rowcontent>
				<td align=center></td>
				<td align=center></td>
				<td><b>Total</b></td>
				<td align=right><b>".@number_format($arrTotal['periode1']+$arrTotalSawal['periode1'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode2']+$arrTotalSawal['periode2'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode3']+$arrTotalSawal['periode3'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode4']+$arrTotalSawal['periode4'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periodeytd']+$arrTotalSawal['periodeytd'],0)."</b></td>
				
			</tr>";
		}
		$stream.= "</tbody></tfoot></tfoot></table>";
		$date=date('d-m-Y H:i:s');	
		$stream.= "<i>Print by : ".$_SESSION['standard']['username']." ".$date."</i>";
				
		if($tipe=='html'){
			echo $stream;
		}elseif($tipe=='pdf'){
			
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("detail_pl",array("Attachment"=>0));
		}else{
			$nop_ = "Detail_PL";
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
		}
	break;
	case'viewdetail_bsl4':
		$where='';
		if($pt!=''){
			$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."')";
		}
		if($kdorg!=''){
			$where.=" and kodeorg='".$kdorg."'";
		}

		$kodelaporan ='BSL4';
		# ambil daftar noakun
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' and nourut ='".$nourut."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$whrnoakun[$bar['noakun']]=$bar['noakun'];
		}
		if(count(@$whrnoakun)==''){
			exit("Warning : Tidak memiliki data detail !");
		}
		$stream='';
		
		if($tipe=='html'){
			$stream.="<img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='View Excel' onclick=\"viewdetail('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','excel','bsl4','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";
			
			$stream.="&nbsp;<img src=images/pdf.jpg class=resicon class=zImgBtn height='30'  title='View PDF' onclick=\"pdf('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','pdf','bsl4','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";			
		}
		
		if($tipe=='excel' or $tipe=='pdf'){
			$stream.="<div align=center><b>BALANCE SHEET - DETAILS</b></div><br>";
			$stream.="<table class=sortable border=1 cellspacing=0 width=100%>";
		}else{
			$stream.="<table class=sortable border=0 cellspacing=1 width='100%;'>";
		}
		
		$stream.="<thead>
				<tr class=rowheader>
				<td align=center width=30px>No</td>
				<td align=center width=70px>" . $_SESSION['lang']['noakun'] . "</td>
				<td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
				<td align=center>".$periode1." s/d ".$periodesd1."</td>
				<td align=center>".$periode2." s/d ".$periodesd2."</td>
				<td align=center>".$periode3." s/d ".$periodesd3."</td>
				<td align=center>".$periode4." s/d ".$periodesd4."</td>
				<td align=center>YTD ".$periodesdytd."</td>
				</tr>
			</thead>
			<tbody>";
		$jlhkolom=6;
		$arrTotal=array();
		$arrTotalSawal=array();
		$dataArray=array();
		
		#ambil sawal
		$str="select noakun ".$sumjlh1." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal1." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['sawalperiode1']+=($bar['jumlah']*-1);
			@$arrTotalSawal['periode1']+=($bar['jumlah']*-1);
		}
		$str="select noakun ".$sumjlh2." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal2." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['sawalperiode2']+=($bar['jumlah']*-1);
			@$arrTotalSawal['periode2']+=($bar['jumlah']*-1);
		}
		$str="select noakun ".$sumjlh3." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal3." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['sawalperiode3']+=($bar['jumlah']*-1);
			@$arrTotalSawal['periode3']+=($bar['jumlah']*-1);
		}
		$str="select noakun ".$sumjlh4." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal4." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['sawalperiode4']+=($bar['jumlah']*-1);
			@$arrTotalSawal['periode4']+=($bar['jumlah']*-1);
		}
		
		$str="select noakun ".$sumjlhytd." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawalytd." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['sawalperiodeytd']+=($bar['jumlah']*-1);
			@$arrTotalSawal['periodeytd']+=($bar['jumlah']*-1);
		}

		#ambil jurnal
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal1." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode1']+=($bar['jumlah']*-1);
			@$arrTotal['periode1']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal2." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode2']+=($bar['jumlah']*-1);
			@$arrTotal['periode2']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal3." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode3']+=($bar['jumlah']*-1);
			@$arrTotal['periode3']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal4." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode4']+=($bar['jumlah']*-1);
			@$arrTotal['periode4']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnalytd." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiodeytd']+=($bar['jumlah']*-1);
			@$arrTotal['periodeytd']+=($bar['jumlah']*-1);
		} 
// echo"<pre>";
// print_r($dataArray);
// print_r($arrTotal);
// echo"</pre>";
// exit();
		$no='';
		$namaakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		if(!empty($dataArray)){
			foreach($dataArray as $noakun => $data){
				$no++;
				$stream.="
					<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$noakun."</td>
						<td colspan=0>".$namaakun[$noakun]."</td>
						<td align=right>".@number_format($data['dataperiode1']+$data['sawalperiode1'],0)."</td>
						<td align=right>".@number_format($data['dataperiode2']+$data['sawalperiode2'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode3']+$data['sawalperiode3'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode4']+$data['sawalperiode4'],0)."</td>    
						<td align=right>".@number_format($data['dataperiodeytd']+$data['sawalperiodeytd'],0)."</td>    
					</tr>";      		
			}
		$stream.="
			<tr class=rowcontent>
				<td align=center></td>
				<td align=center></td>
				<td><b>Total</b></td>
				<td align=right><b>".@number_format($arrTotal['periode1']+$arrTotalSawal['periode1'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode2']+$arrTotalSawal['periode2'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode3']+$arrTotalSawal['periode3'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode4']+$arrTotalSawal['periode4'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periodeytd']+$arrTotalSawal['periodeytd'],0)."</b></td>
				
			</tr>";
		}
		$stream.= "</tbody></tfoot></tfoot></table>";
		$date=date('d-m-Y H:i:s');	
		$stream.= "<i>Print by : ".$_SESSION['standard']['username']." ".$date."</i>";
				
		if($tipe=='html'){
			echo $stream;
		}elseif($tipe=='pdf'){
			
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("detail_bsl4",array("Attachment"=>0));
		}else{
			$nop_ = "Detail_BSL4";
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
		}
	break;
	case'viewdetail_bsl3':
		$where='';
		if($pt!=''){
			$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."')";
		}
		if($kdorg!=''){
			$where.=" and kodeorg='".$kdorg."'";
		}
		
		$kodelaporan ='BSL3';
		# ambil daftar noakun
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' and nourut ='".$nourut."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$whrnoakun[$bar['noakun']]=$bar['noakun'];
		}
		if(count(@$whrnoakun)==''){
			exit("Warning : Tidak memiliki data detail !");
		}
		$stream='';
		
		if($tipe=='html'){
			$stream.="<img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='View Excel' onclick=\"viewdetail('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','excel','bsl3','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";
			
			$stream.="&nbsp;<img src=images/pdf.jpg class=resicon class=zImgBtn height='30'  title='View PDF' onclick=\"pdf('".$nourut."','".$pt."','".$kdorg."','".$periode1."','".$periode2."','".$periode3."','".$periodesd1."','".$periodesd2."','".$periodesd3."','pdf','bsl3','".$periode4."','".$periodesd4."','".$periodeytd."','".$periodesdytd."');\" >";			
		}
		
		if($tipe=='excel' or $tipe=='pdf'){
			$stream.="<div align=center><b>BALANCE SHEET - COMPARATIVE</b></div><br>";
			$stream.="<table class=sortable border=1 cellspacing=0 width=100%>";
		}else{
			$stream.="<table class=sortable border=0 cellspacing=1 width='100%;'>";
		}
		
		$stream.="<thead>
				<tr class=rowheader>
				<td align=center width=30px>No</td>
				<td align=center width=70px>" . $_SESSION['lang']['noakun'] . "</td>
				<td align=center>" . $_SESSION['lang']['namaakun'] . "</td>
				<td align=center>".$periode1." s/d ".$periodesd1."</td>
				<td align=center>".$periode2." s/d ".$periodesd2."</td>
				<td align=center>".$periode3." s/d ".$periodesd3."</td>
				<td align=center>".$periode4." s/d ".$periodesd4."</td>
				<td align=center>YTD ".$periodesdytd."</td>
				</tr>
			</thead>
			<tbody>";
		$jlhkolom=6;
		$arrTotal=array();
		$arrTotalSawal=array();
		$dataArray=array();
		
		#ambil sawal
		$str="select noakun ".$sumjlh1." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal1." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['sawalperiode1']+=($bar['jumlah']*-1);
			@$arrTotalSawal['periode1']+=($bar['jumlah']*-1);
		}
		$str="select noakun ".$sumjlh2." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal2." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['sawalperiode2']+=($bar['jumlah']*-1);
			@$arrTotalSawal['periode2']+=($bar['jumlah']*-1);
		}
		$str="select noakun ".$sumjlh3." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal3." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['sawalperiode3']+=($bar['jumlah']*-1);
			@$arrTotalSawal['periode3']+=($bar['jumlah']*-1);
		}
		$str="select noakun ".$sumjlh4." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawal4." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['sawalperiode4']+=($bar['jumlah']*-1);
			@$arrTotalSawal['periode4']+=($bar['jumlah']*-1);
		}
		
		$str="select noakun ".$sumjlhytd." from ".$dbname.".keu_saldobulanan where 1=1 ".$wheresawalytd." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['sawalperiodeytd']+=($bar['jumlah']*-1);
			@$arrTotalSawal['periodeytd']+=($bar['jumlah']*-1);
		}
		
		
		#ambil jurnal
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal1." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode1']+=($bar['jumlah']*-1);
			@$arrTotal['periode1']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal2." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode2']+=($bar['jumlah']*-1);
			@$arrTotal['periode2']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal3." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode3']+=($bar['jumlah']*-1);
			@$arrTotal['periode3']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnal4." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiode4']+=($bar['jumlah']*-1);
			@$arrTotal['periode4']+=($bar['jumlah']*-1);
		} 
		
		$str="select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$wherejurnalytd." and noakun in ('".implode("','",$whrnoakun)."') ".$where." group by noakun order by noakun";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dataArray[$bar['noakun']]['dataperiodeytd']+=($bar['jumlah']*-1);
			@$arrTotal['periodeytd']+=($bar['jumlah']*-1);
		} 
// echo"<pre>";
// print_r($dataArray);
// print_r($arrTotal);
// echo"</pre>";
// exit();
		$no='';
		$namaakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		if(!empty($dataArray)){
			foreach($dataArray as $noakun => $data){
				$no++;
				$stream.="
					<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$noakun."</td>
						<td colspan=0>".$namaakun[$noakun]."</td>
						<td align=right>".@number_format($data['dataperiode1']+$data['sawalperiode1'],0)."</td>
						<td align=right>".@number_format($data['dataperiode2']+$data['sawalperiode2'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode3']+$data['sawalperiode3'],0)."</td>    
						<td align=right>".@number_format($data['dataperiode4']+$data['sawalperiode4'],0)."</td>    
						<td align=right>".@number_format($data['dataperiodeytd']+$data['sawalperiodeytd'],0)."</td>    
					</tr>";      		
			}
		$stream.="
			<tr class=rowcontent>
				<td align=center></td>
				<td align=center></td>
				<td><b>Total</b></td>
				<td align=right><b>".@number_format($arrTotal['periode1']+$arrTotalSawal['periode1'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode2']+$arrTotalSawal['periode2'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode3']+$arrTotalSawal['periode3'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periode4']+$arrTotalSawal['periode4'],0)."</b></td>
				<td align=right><b>".@number_format($arrTotal['periodeytd']+$arrTotalSawal['periodeytd'],0)."</b></td>
				
			</tr>";
		}
		$stream.= "</tbody></tfoot></tfoot></table>";
		$date=date('d-m-Y H:i:s');	
		$stream.= "<i>Print by : ".$_SESSION['standard']['username']." ".$date."</i>";
			
	if($tipe=='html'){
		echo $stream;
	}elseif($tipe=='pdf'){
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($stream);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		$dompdf->stream("detail_bsl3",array("Attachment"=>0));
	}else{
        $nop_ = "Detail_BSL3";
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
	}
	break;
}
?>