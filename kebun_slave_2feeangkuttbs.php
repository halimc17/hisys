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
			$vwidth="cellspacing=1 cellpadding=3";
		}elseif($tipelaporan=='pdf'){
			$border=1;
			$vwidth="width=100%  cellspacing=0 cellpadding=3";
		}else{
			$border=1;
			$vwidth="cellspacing=1 cellpadding=3";
		}
		
		$arrdata=$jnsfee=array();
		
		$str="select a.jenisfee, a.rupiah as rpfee, a.id, a.kgwb, a.potonganrp,a.blok from ".$dbname.".kebun_rekapangkutantbsdtfee a
		where a.kodeorg='".$kebun."' and (a.tanggal between '".tanggaldb($tgl1)."' and '".tanggaldb($tgl2)."') ";
		$res=fetchdata($str);
		foreach($res as $val){
			$jnsfee[$val['jenisfee']]=$val['jenisfee'];
			$arrblok[$val['blok']]=$val['blok'];
			$feerp[$val['id']][$val['jenisfee']]+=$val['rpfee'];
			$totfeerp[$val['jenisfee']]+=$val['rpfee'];
			$blokk[$val['id']][$val['blok']]=$val['blok'];

			$arrdata[$val['id']]['id']=$val['id'];
			$arrdata[$val['id']]['kgwb']+=$val['kgwb'];
			$arrdata[$val['id']]['rpangkut']+=$val['rupiah'];				
			$arrdata[$val['id']]['rpfee']+=$val['rpfee'];
			$arrdata[$val['id']]['potonganrp']+=$val['potonganrp'];
			
		}
		// echo "<pre>";
		// print_r($blokk);
		// echo "</pre>";
		$tab.="<table class=sortable  border='".$border."' ".$vwidth.">
			<thead>
			<tr class=rowheader style='text-align:center'>
				<th rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2>Penerima FEE</th>
				<th rowspan=2>HASIL (KG)</th>
				<th colspan=".count($jnsfee).">Jenis FEE</th>
				<th rowspan=2>".$_SESSION['lang']['blok']."</th>
				<th rowspan=2>Total Sebelum POT</th>
				<th rowspan=2>POT</th>
				<th rowspan=2 width=100px>TOTAL GAJI YANG HARUS DIBAYAR</th>
				<th rowspan=2>KET</th>
			</tr>
			<tr>
			";

			foreach ($jnsfee as $key => $fee) {
				$tab.="<th>".$fee."</th>";
			}

			$tab.="
			</tr>
			</thead>
			<tbody>";
			
			$no=0;
			foreach($arrdata as $key=>$val){
				$no++;
				$optnmfee=makeOption($dbname,'kebun_5namafee','id,nama');
				$tab.="<tr class=rowcontent style='vertical-align:top'>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$optnmfee[$val['id']]."</td>";
				$tab.="<td align=right>".hidezerodecimal($val['kgwb'],0)."</td>";

				foreach ($jnsfee as $key => $fee) {
					$tab.="<td align=right>".hidezerodecimal($feerp[$val['id']][$fee])."</td>";
				}
				
				$tab.="<td align=right>";
				foreach ($arrblok as $keyblok => $valblok) {
					if($blokk[$val['id']][$valblok]!=''){

					
					$tab.= $blokk[$val['id']][$valblok]."</br>";
				}
				}
				$tab.="</td>";
				$tab.="<td align=right>".hidezerodecimal($val['rpfee'],0)."</td>";
				$tab.="<td align=right>".hidezerodecimal($val['potonganrp'],0)."</td>";
				$tab.="<td align=right>".hidezerodecimal($val['rpfee']-$val['potonganrp'],0)."</td>";
				$tab.="<td></td>";
				$tab.="</tr>";
				
				$totrpfee+=$val['rpfee'];
				$totallrpfee+=$val['rpfee']-$val['potonganrp'];
				$totkgwb+=$val['kgwb'];
				$totpotonganrp+=$val['potonganrp'];
				
			}
			$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
				<td colspan=2 align=center>T O T A L</td>
				<td align=right>".hidezerodecimal($totkgwb,0)."</td>";

				foreach ($jnsfee as $key => $fee) {
					$tab.="<td align=right>".hidezerodecimal($totfeerp[$fee])."</td>";
				}

				$tab.="
				<td align=right></td>
				<td align=right>".hidezerodecimal($totrpfee,0)."</td>
				<td align=right>".hidezerodecimal($totpotonganrp,0)."</td>
				<td align=right>".hidezerodecimal($totallrpfee,0)."</td>
				<td></td>
				
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
					<td style=font-weight:bold;>DAFTAR SLIP GAJI FEE ANGKUT TBS</td>
				</tr>
				<tr>
					<td style=font-weight:bold;>Tanggal : ".$tgl1." s/d ".$tgl2."</td>
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
			$dompdf->stream("DAFTAR SLIP GAJI PREMI ANGKUT TBS(FEE)", array("Attachment" => false));
		}else{
			$titlelaporan="DAFTAR SLIP GAJI PREMI ANGKUT TBS";
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
