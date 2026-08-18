<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');
$tipelaporan = checkPostGet('tipelaporan','');

$kebun = checkPostGet('kebun','');
$periodebayar = checkPostGet('periodebayar','');
$periode = checkPostGet('periode','');
$kontraktor = checkPostGet('kontraktor','');

switch ($method) {
	case'getlaporan':
		$tab="";
		if($tipelaporan=='html'){
			$border=0;
			$vwidth="";
		}elseif($tipelaporan=='pdf'){
			$border=0;
			$vwidth="width=100%";
		}else{
			$border=1;
			$vwidth="";
		}
		
		if($periodebayar!=''){
			$where=" and periodebyr='".$periodebayar."'";
			
		}
		
		$arrdata=array();
		$str="select * from ".$dbname.".kebun_rekapangkutantbsvw where kodeorg='".$kebun."' and tanggal like '".$periode."%' ".$where." ";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrdata[$val['koderekanan']]['kdsup']=$val['koderekanan'];
			$arrdata[$val['koderekanan']]['nokendaraan']=$val['nokendaraan'];
			$arrdata[$val['koderekanan']]['supir']=$val['supir'];
			if($val['jenis']=='angkut'){
				$arrdata[$val['koderekanan']]['kgwb']+=$val['kgwb'];
				$arrdata[$val['koderekanan']]['rpangkut']+=$val['rupiah'];				
			}else{
				$arrdata[$val['koderekanan']]['rpmuat']+=$val['rupiah'];				
			}
			$arrdata[$val['koderekanan']]['potonganrp']+=$val['potonganrp'];						
		}
		
		$tab.="<table class=sortable cellspacing=1 cellpadding=3 border='".$border."' ".$vwidth.">
			<thead>
			<tr class=rowheader style='text-align:center'>
				<th>".$_SESSION['lang']['nourut']."</th>
				<th>".$_SESSION['lang']['periode']."</th>
				<th>".$_SESSION['lang']['kontraktor']."</th>
				<th>".$_SESSION['lang']['beratBersih']."</th>
				<th>Jumlah Biaya Angkut dan Muat</th>
				<th colspan=3>".$_SESSION['lang']['print']."</th>
			</tr>
			</thead>
			<tbody>";
			
			$no=0;
			if(count($arrdata) > 0){
				foreach($arrdata as $key=>$val){
					$no++;
					$optkontraktor=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['kdsup']."'");
					$totalbayar=$val['rpmuat']+$val['rpangkut']-$val['potonganrp'];
					$tab.="<tr class=rowcontent style='vertical-align:top'>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td align=center>".$periode."</td>";
					$tab.="<td>".$optkontraktor[$val['kdsup']]."</td>";
					$tab.="<td align=right>".hidezerodecimal($val['kgwb'],0)."</td>";
					$tab.="<td align=right>".hidezerodecimal($totalbayar,0)."</td>";
					$tab.="<td align=center>
						<img src='images/skyblue/zoom.png' class='resicon' title='View' onclick=\"printreport(event,'".$periode."','".$kebun."','".$val['kdsup']."','html')\">
					</td>";
					$tab.="<td align=center>
						<img src='images/pdf.jpg' class='resicon' title='PDF' onclick=\"printreport(event,'".$periode."','".$kebun."','".$val['kdsup']."','pdf')\">
					</td>";
					$tab.="<td align=center>
						<img src='images/excel.jpg' class='resicon' title='Excel' onclick=\"printreport(event,'".$periode."','".$kebun."','".$val['kdsup']."','excel')\">
					</td>";
					$tab.="</tr>";
					
					$totkgwb+=$val['kgwb'];
					$totrpmuat+=$val['rpmuat'];
					$totrpangkut+=$val['rpangkut'];
					$potonganrp+=$val['potonganrp'];
					$total+=$totalbayar;
				}
				$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
					<td colspan=3 align=center>T O T A L</td>
					<td align=right>".hidezerodecimal($totkgwb,0)."</td>
					<td align=right>".hidezerodecimal($total,0)."</td>
					<td colspan=3 align=center></td>
				</tr>";
			}else{
				$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>";
				$tab.="<td colspan=8 align=center>".$_SESSION['lang']['datanotfound']."</td>";
				$tab.="</tr>";
			}
			
		$tab.="</tbody>
		</table>";
		
		if($tipelaporan=='html'){
			echo $tab;
		}elseif($tipelaporan=='pdf'){
			$arrHead = setheadreport('',$kebun);
			$path=$arrHead['logo'];
			$header="<div>
				<table cellspacing=0 border=0 width=100% align=center>
					<tr>
						<td rowspan=3 style='font-weight:bold;width:100px'><img src='".$path."' height='80' /></td>
						<td style=font-weight:bold;>".$arrHead['nama']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['alamat']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['telepon']."</td>
					</tr>
				</table>
			<hr>
			<table cellspacing=0 border=0 width=100% style='text-align:center'>
				<tr>
					<td style=font-weight:bold;>REKAPITULASI KONTRAKTOR ANGKUTAN TBS</td>
				</tr>
				<tr>
					<td style=font-weight:bold;>MULAI TANGGAL : ".$tgl1." s/d ".$tgl2."</td>
				</tr>
			</table>";
			
			$footer="<br><table cellspacing=0 border=0 width=100% style='font-weight:bold;text-align:center'>
				<tr>
					<td>Disetujui Oleh</td>
					<td>Diketahui Oleh</td>
					<td>Diperiksa Oleh</td>
					<td>Dibuat Oleh</td>
				</tr>
				<tr>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
				</tr>
			</table>";
			
			$hasil=$header;
			$hasil.=$tab;
			$hasil.=$footer;
			$dompdf = new Dompdf();
			$dompdf->loadHtml($hasil);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			$dompdf->stream("Rekaptulasi Kontraktor Angkutan TBS", array("Attachment" => false));
		}else{
			$titlelaporan="Rekaptulasi Kontraktor Angkutan TBS";
			if($handle = opendir('tempExcel')){
				while(false !== ($file = readdir($handle))){
					if($file != "." && $file != ".." && $file != "index.html"){
						@unlink('tempExcel/' . $file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/".$titlelaporan.".xls",'w');
			if(!fwrite($handle, $tab)){
				echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
					window.location='tempExcel/".$titlelaporan.".xls';
					</script>";
			}
			closedir($handle); 
		}
	break;
	
	case'printreport':
		$tab="";
		if($tipelaporan=='html'){
			$border=0;
			$vwidth="";
			$cellspacing='cellspacing=1';
			$cellpadding='cellpadding=5';
		}elseif($tipelaporan=='pdf'){
			$border=1;
			$vwidth="width=100%";
			$cellspacing='cellspacing=0';
			$cellpadding='cellpadding=5';
		}else{
			$border=1;
			$vwidth="";
			$cellspacing='cellspacing=1';
			$cellpadding='cellpadding=5';
		}
		
		$arrdata=array();
		$str="select *, nospb as notiket from ".$dbname.".kebun_rekapangkutantbsvw where kodeorg='".$kebun."' and tanggal like '".$periode."%' and koderekanan='".$kontraktor."' group by notiket order by tanggal asc";
		// echo $str;
		// select *, nospb as notiket from ksp.kebun_rekapangkutantbsvw where kodeorg='SD1E' and tanggal like '2019-11%' and koderekanan='S201911137' group by notiket order by tanggal asc
		// select a.*,b.jenis,b.tujuan,b.rupiah from ksp.kebun_rekapangkutantbsht a left join ksp.kebun_rekapangkutantbsdt b on a.nospb=b.nospb where kodeorg = 'SD1E' and periode='2019-11' and spk='021/SPK/SDK/SD1E/I/2019' and periodebyr='0'
		$str="select a.*,b.blok, b.jenis,b.tujuan,b.rupiah,b.potonganrp, d.notransaksi as notiket, d.nokendaraan,b.kgtotal,b.kgwb, d.beratbersih, d.kgpotsortasi from ".$dbname.".kebun_rekapangkutantbsht a left join ".$dbname.".kebun_rekapangkutantbsdt b on a.nospb=b.nospb left join ".$dbname.".log_spkht c on a.spk=c.notransaksi left join ".$dbname.".pabrik_timbangan d on a.nospb=d.nospb where a.kodeorg = '".$kebun."' and a.periode like '".$periode."%' and c.koderekanan='".$kontraktor."' order by a.tanggal";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrdata[$val['notiket']][$val['blok']]['notiket']=$val['notiket'];
			$arrdata[$val['notiket']][$val['blok']]['nospb']=$val['nospb'];
			$arrdata[$val['notiket']][$val['blok']]['nokendaraan']=$val['nokendaraan'];
			$arrdata[$val['notiket']][$val['blok']]['tanggal']=$val['tanggal'];
			$arrdata[$val['notiket']][$val['blok']]['blok']=$val['blok'];
			$arrdata[$val['notiket']][$val['blok']]['divisi']=$val['divisi'];
			$arrdata[$val['notiket']][$val['blok']]['kgsebelum']=$val['kgtotal'];
			$arrdata[$val['notiket']][$val['blok']]['kgsetelah']=$val['kgwb'];
			$arrdata[$val['notiket']][$val['blok']]['rupiah']+=$val['rupiah'];
			$arrdata[$val['notiket']][$val['blok']]['potongan']+=$val['potonganrp'];
		}
		
		$tab.="<table class=sortable ".$cellspacing." ".$cellpadding." border='".$border."' ".$vwidth.">
			<thead>
			<tr class=rowheader style='text-align:center'>
				<th rowspan='2' style='width:80px'>".$_SESSION['lang']['tanggal']."</th>
				<th rowspan='2'>".$_SESSION['lang']['ticket']."</th>
				<th rowspan='2'>".$_SESSION['lang']['nospb']."</th>
				<th rowspan='2'>".$_SESSION['lang']['nokendaraan']."</th>
				<th rowspan='2'>".$_SESSION['lang']['blok']."</th>
				<th rowspan='2'>".$_SESSION['lang']['divisi']."</th>
				<th colspan='2'>".$_SESSION['lang']['berat']." TBS</th>
				<th rowspan='2'>".$_SESSION['lang']['harga']." / KG</th>
				<th rowspan='2'>".$_SESSION['lang']['potongan']." (Rp)</th>
				<th rowspan='2'>".$_SESSION['lang']['jumlah']." (Rp)</th>
				<th rowspan='2'>".$_SESSION['lang']['keterangan']."</th>
			</tr>
			<tr class=rowheader style='text-align:center'>
				<th>Sebelum Grading (KG)</th>
				<th>Setelah Grading (KG)</th>
			</tr>
			</thead>
			<tbody>";
			
			
			if(count($arrdata) > 0){
				foreach($arrdata as $notiket => $val1){
					foreach($val1 as $key1 => $val){
						
						$tab.="<tr class=rowcontent style='vertical-align:top'>";
						$tab.="<td align=center>".tanggalnormal($val['tanggal'])."</td>";
						$tab.="<td align=center>".$val['notiket']."</td>";
						$tab.="<td align=center>".$val['nospb']."</td>";
						$tab.="<td align=center>".$val['nokendaraan']."</td>";
						$tab.="<td align=center>".getNamaOrg($val['blok'])."</td>";
						$tab.="<td align=center>".$val['divisi']."</td>";
						$tab.="<td align=right>".hidezerodecimal($val['kgsebelum'],0)."</td>";
						$tab.="<td align=right>".hidezerodecimal($val['kgsetelah'],0)."</td>";
						$tab.="<td align=right>".hidezerodecimal(($val['rupiah']/$val['kgsebelum']),2)."</td>";
						$tab.="<td align=right>".hidezerodecimal($val['potongan'],0)."</td>";
						$tab.="<td align=right>".hidezerodecimal($val['rupiah']-$val['potongan'],0)."</td>";
						$tab.="<td align=center></td>";
						$tab.="</tr>";
						
						$totkgsebelum+=$val['kgsebelum'];
						$totkgsetelah+=$val['kgsetelah'];
						$totrupiah+=$val['rupiah']-$val['potongan'];
						$potongan+=$val['potongan'];
					}
				}
				$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
					<td colspan=6 align=center>T O T A L</td>
					<td align=right>".hidezerodecimal($totkgsebelum,0)."</td>
					<td align=right>".hidezerodecimal($totkgsetelah,0)."</td>
					<td align=center></td>
					<td align=right>".hidezerodecimal($potongan,0)."</td>
					<td align=right>".hidezerodecimal($totrupiah,0)."</td>
					<td align=center></td>
				</tr>";
			}else{
				$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>";
				$tab.="<td colspan=11 align=center>".$_SESSION['lang']['datanotfound']."</td>";
				$tab.="</tr>";
			}
			
		$tab.="</tbody>
		</table>";
		
		if($tipelaporan=='html'){
			echo $tab;
		}elseif($tipelaporan=='pdf'){
			$optkontraktor=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$kontraktor."'");
			$arrHead = setheadreport('',$kebun);
			$path=$arrHead['logo'];
			$header="<div>
				<table cellspacing=0 border=0 width=100% align=center>
					<tr>
						<td rowspan=3 style='font-weight:bold;width:100px'><img src='".$path."' height='80' /></td>
						<td style=font-weight:bold;>".$arrHead['nama']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['alamat']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['telepon']."</td>
					</tr>
				</table>
			<hr>
			<table cellspacing=0 border=0 width=100% style='text-align:center'>
				<tr>
					<td style=font-weight:bold;>PEMBAYARAN ANGKUTAN TBS INTI</td>
				</tr>
				<tr>
					<td style=font-weight:bold;>BULAN : ".tanggalbulan($periode)."</td>
				</tr>
			</table>
			<table cellspacing=0 border=0 style='text-align:left'>
				<tr>
					<td style=font-weight:bold;>Kontraktor</td>
					<td style=font-weight:bold;>:</td>
					<td style=font-weight:bold;>".$optkontraktor[$kontraktor]."</td>
				</tr>
			</table>";
			
			$footer="<br><table cellspacing=0 border=0 width=100% style='font-weight:bold;text-align:center'>
				<tr>
					<td>Disetujui Oleh</td>
					<td>Diketahui Oleh</td>
					<td>Diperiksa Oleh</td>
					<td>Dibuat Oleh</td>
					<td>Diterima Oleh</td>
				</tr>
				<tr>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
				</tr>
			</table>";
			
			$hasil=$header;
			$hasil.=$tab;
			$hasil.=$footer;
			$dompdf = new Dompdf();
			$dompdf->loadHtml($hasil);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("Pembayaran Angkutan TBS Inti", array("Attachment" => false));
		}else{
			$optkontraktor=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$kontraktor."'");
			$arrHead = setheadreport('',$kebun);
			$path=$arrHead['logo'];
			$header="<div>
				<table cellspacing=0 border=0 width=100% align=center>
					<tr>
						<td rowspan=3 style='font-weight:bold;width:100px'></td>
						<td colspan=7 style=font-weight:bold;>".$arrHead['nama']."</td>
					</tr>
					<tr>
						<td colspan=7 style=font-weight:bold;>".$arrHead['alamat']."</td>
					</tr>
					<tr>
						<td colspan=7 style=font-weight:bold;>".$arrHead['telepon']."</td>
					</tr>
				</table>
			<hr>
			<table cellspacing=0 border=0 width=100% align=center>
				<tr>
					<td colspan=10 align=center style=font-weight:bold;>PEMBAYARAN ANGKUTAN TBS INTI</td>
				</tr>
				<tr>
					<td colspan=10 align=center style=font-weight:bold;>BULAN : ".tanggalbulan($periode)."</td>
				</tr>
			</table>
			<table cellspacing=0 border=0 style='text-align:left'>
				<tr>
					<td colspan=10 style=font-weight:bold;>Kontraktor : ".$optkontraktor[$kontraktor]."</td>
				</tr>
			</table>";
			
			$footer="<br><table cellspacing=0 border=0 width=100% style='font-weight:bold;text-align:center'>
				<tr>
					<td colspan=2 align=center>Disetujui Oleh</td>
					<td colspan=2 align=center>Diketahui Oleh</td>
					<td colspan=2 align=center>Diperiksa Oleh</td>
					<td colspan=2 align=center>Dibuat Oleh</td>
					<td colspan=2 align=center>Diterima Oleh</td>
				</tr>
				<tr>
					<td colspan=2 align=center style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td colspan=2 align=center style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td colspan=2 align=center style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td colspan=2 align=center style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td colspan=2 align=center style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
				</tr>
			</table>";
			
			$hasil=$header;
			$hasil.=$tab;
			$hasil.=$footer;
			
			$titlelaporan="Pembayaran Angkutan TBS Inti";
			if($handle = opendir('tempExcel')){
				while(false !== ($file = readdir($handle))){
					if($file != "." && $file != ".." && $file != "index.html"){
						@unlink('tempExcel/' . $file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/".$titlelaporan.".xls",'w');
			if(!fwrite($handle, $hasil)){
				echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
					window.location='tempExcel/".$titlelaporan.".xls';
					</script>";
			}
			closedir($handle); 
		}
	break;
}
?>
