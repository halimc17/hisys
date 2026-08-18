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
$periode = checkPostGet('periode','');
$tgl1 = checkPostGet('tgl1','');
$tgl2 = checkPostGet('tgl2','');

switch ($method) {
	case'getlaporan':
		if(substr($tgl1,3,2)!=substr($tgl2,3,2)){
			exit("Warning : Periode harus dalam bulan dan tahun yang sama");
		}
	
		$tab="";
		if($tipelaporan=='html'){
			$border=0;
			$vwidth="cellspacing=1 cellpadding=5";
		}elseif($tipelaporan=='pdf'){
			$border=1;
			$vwidth="width=100% cellspacing=0 cellpadding=3";
		}else{
			$border=1;
			$vwidth="cellspacing=1 cellpadding=3";
		}
		
		$arrdata=array();
		$str="select * from ".$dbname.".kebun_rekapangkutantbsvw where kodeorg='".$kebun."' and (tanggal between '".tanggaldb($tgl1)."' and '".tanggaldb($tgl2)."')";
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
		
		$tab.="<table class=sortable border='".$border."' ".$vwidth.">
			<thead>
			<tr class=rowheader style='text-align:center'>
				<th rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2>".$_SESSION['lang']['kontraktor']."</th>
				<th colspan=2>".$_SESSION['lang']['spk']."</th>
				<th rowspan=2>".$_SESSION['lang']['upahmuat']."</th>
				<th rowspan=2>".$_SESSION['lang']['upahangkut']."</th>
				<th rowspan=2>".$_SESSION['lang']['totalsebelumpotongan']."</th>
				<th rowspan=2>".$_SESSION['lang']['potongan']."</th>
				<th rowspan=2>".$_SESSION['lang']['total']."</th>
			</tr>
			<tr class=rowheader style='text-align:center'>
				<th>".$_SESSION['lang']['beratbruto']."</th>
				<th>".$_SESSION['lang']['rpkg']."</th>
			</tr>
			</thead>
			<tbody>";
			/*
			<th>".$_SESSION['lang']['kendaraan']."</th>
				<th>".$_SESSION['lang']['supir']."</th>
			*/
			$no=0;
			foreach($arrdata as $key=>$val){
				$no++;
				$optkontraktor=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['kdsup']."'");
				$totalbayar=$val['rpmuat']+$val['rpangkut']-$val['potonganrp'];
				$totalsebelumpotongan=$val['rpmuat']+$val['rpangkut'];
				$tab.="<tr class=rowcontent style='vertical-align:top'>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$optkontraktor[$val['kdsup']]."</td>";
				// $tab.="<td>".$val['nokendaraan']."</td>";
				// $tab.="<td>".$val['supir']."</td>";
				$tab.="<td align=right>".hidezerodecimal($val['kgwb'],0)."</td>";
				$tab.="<td align=right>".hidezerodecimal($val['rpangkut']/$val['kgwb'],0)."</td>";
				$tab.="<td align=right>".hidezerodecimal($val['rpmuat'],0)."</td>";
				$tab.="<td align=right>".hidezerodecimal($val['rpangkut'],0)."</td>";
				$tab.="<td align=right>".hidezerodecimal($totalsebelumpotongan,0)."</td>";
				$tab.="<td align=right>".hidezerodecimal($val['potonganrp'],0)."</td>";
				$tab.="<td align=right>".hidezerodecimal($totalbayar,0)."</td>";
				$tab.="</tr>";
				
				$totkgwb+=$val['kgwb'];
				$totrpmuat+=$val['rpmuat'];
				$totrpangkut+=$val['rpangkut'];
				$potonganrp+=$val['potonganrp'];
				$total+=$totalbayar;
				@$tottotalsebelumpotongan+=$totalsebelumpotongan;
			}
			$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
				<td colspan=2 align=center>".$_SESSION['lang']['total']."</td>
				<td align=right>".hidezerodecimal($totkgwb,0)."</td>
				<td align=right>".hidezerodecimal($totrpangkut/$totkgwb,0)."</td>
				<td align=right>".hidezerodecimal($totrpmuat,0)."</td>
				<td align=right>".hidezerodecimal($totrpangkut,0)."</td>
				<td align=right>".hidezerodecimal($tottotalsebelumpotongan,0)."</td>
				<td align=right>".hidezerodecimal($potonganrp,0)."</td>
				<td align=right>".hidezerodecimal($total,0)."</td>
			</tr>";
			
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
}
?>
