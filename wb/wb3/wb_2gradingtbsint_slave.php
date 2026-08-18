<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

$nmunit=makeOption($dbname,'msunit','UNITCODE,UNITNAME');

switch($method){
	case 'loaddata':
		$strz="select kode,deskripsi,persen,kg,jjg from ".$dbname.".msgrading where status='1' AND persen != '' AND kg != '' order by kode";
		$resz=fetchData($strz);
		$baris=0;
		foreach ($resz as $valz){
			$msgrading[$valz['kode']]=$valz['deskripsi'];
			$persenkg[$valz['kode']]['persen']=$valz['persen'];
			$persenkg[$valz['kode']]['jjg']=$valz['jjg'];

			$baris+=1;
		}

		$baris=$baris*4;


		$tab="";
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th rowspan=5 style='text-align:center;'>No</th>
				<th rowspan=5 style='text-align:center;'>No Tiket</th>
				<th rowspan=5 style='text-align:center;'>Tanggal</th>
				<th rowspan=5 style='text-align:center;'>Divisi</th>
				<th rowspan=5 style='text-align:center;'>No Kendaraan</th>
				<th rowspan=5 style='text-align:center;'>Jam Grading</th>
				<th rowspan=2 colspan=4 style='text-align:center;'>TANDAN</th>
				<th rowspan=5 style='text-align:center;'>Berat Netto <br> (Kg)</th>
				<th colspan=$baris style='text-align:center;'>TANDAN DI GRADING</th>
			</tr>";
			$tab.="<tr class=rowheader>";
			foreach ($msgrading as $key => $val) {
				$tab.="<th style='text-align:center;' colspan=4>".$val."</th>";
			}
			$tab.="</tr>";
			
			$tab.="<tr class=rowheader>";
			$tab.="<th rowspan=2 colspan=2 style='text-align:center;'>Dikirim Estate</th>";
			$tab.="<th rowspan=2 colspan=2 style='text-align:center;'>Grading PKS</th>";
			for ($i=0; $i < ($baris/4); $i++) {
				$tab.="<th colspan=4 style='text-align:center;'>Standard __ %</th>";
			}
			$tab.="</tr>";

			$tab.="<tr class=rowheader>";
			foreach ($msgrading as $key => $val) {
				$tab.="<th style='text-align:center;' colspan=2>Grading Kebun</th>";
				$tab.="<th style='text-align:center;' colspan=2>Grading PKS</th>";
			}
			$tab.="</tr>";

			$tab.="<tr class=rowheader>";
			$tab.="<th style='text-align:center;'>JLH</th>";
			$tab.="<th style='text-align:center;'>BJR</th>";
			$tab.="<th style='text-align:center;'>JLH</th>";
			$tab.="<th style='text-align:center;'>BJR</th>";
			foreach ($msgrading as $key => $val) {
				$tab.="<th style='text-align:center;'>JLH</th>";
				$tab.="<th style='text-align:center;'>%</th>";
				$tab.="<th style='text-align:center;'>JLH</th>";
				$tab.="<th style='text-align:center;'>%</th>";
			}
			$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";
		
		$arrtipe=array('I'=>'Terima','O'=>'Kirim','II'=>'Terima TO/Blending','OO'=>'Kirim TO/Blending');
		$ttlberatmasuk=$ttlberatkeluar=$ttlnetto=$ttlpotongan=$ttlberatbersih=0;
		$tgl=substr($param['tanggal'],6,4).'-'.substr($param['tanggal'],3,2).'-'.substr($param['tanggal'],0,2);
		$tgl2=substr($param['tanggal2'],6,4).'-'.substr($param['tanggal2'],3,2).'-'.substr($param['tanggal2'],0,2);

		$strx="select millcode from ".$dbname.".mssystem where millcode like '%m'";
		$resx=fetchData($strx);
		if ($resx) {
			$sumber='PABRIK';
		}else{
			$sumber='KEBUN';
		}

		$stry="select compcode from ".$dbname.".mssystem limit 1";
		$resy=fetchData($stry);
		$pt=$resy[0]['compcode'];
		
		$where="";
		if($param['unit']!=''){
			$where.=" and unitcode='".$param['unit']."'";
		}else{
			$where.=" and unitcode in (select unitcode from ".$dbname.".msunit where compcode='".$pt."' and tipeunit in ('INTERNAL','PLASMA'))";
		}

		$where.=" and waktukeluar >= '".$tgl." 00:00:00' and waktukeluar <='".$tgl2." 23:59:59'";
		$str= "select * from ".$dbname.".wb where netto > 0 and kodebarang='".$kodeproduktbs."' and sumber='".$sumber."' ".$where." order by notransaksi,divcode asc";
		$res= fetchdata($str);
		foreach($res as $val){
			$arrdata[$val['unitcode']][$val['notransaksi']]['waktukeluar'] 	= $val['waktukeluar'];
			$arrdata[$val['unitcode']][$val['notransaksi']]['netto'] 		= $val['netto'];
			$arrdata[$val['unitcode']][$val['notransaksi']]['janjang'] 		= $val['janjang'];
			$arrdata[$val['unitcode']][$val['notransaksi']]['divcode'] 		= $val['divcode'];
			$arrdata[$val['unitcode']][$val['notransaksi']]['nokendaraan'] 	= $val['nokendaraan'];
			
			$str1 = 'SELECT * FROM '.$dbname.'.trxsortasi WHERE notransaksi = "'.$val['notransaksi'].'"';
			$res1 = fetchdata($str1);
			foreach($res1 as $bar){
				$valueSortasi[$bar['notransaksi']][$bar['kode']][$bar['field']] = $bar['value'];
			}
		}

		
		$no=0;
		$colspanunit=($baris+11);
		if ($res) {
			$grandtotaljanjang=0;
			$grandtotalbjr=0;
			$grandtotalnetto=0;
			$grandtotaljjggrading=array();
			$grandtotalpersengrading=array();
			foreach ($arrdata as $unit => $arrnotransaksi) {
				$tab.="<tr class=rowcontent>";
				$tab.="<td colspan=$colspanunit>".$nmunit[$unit]."</td>";
				$tab.="</tr>";

				$totaljanjang=0;
				$totalbjr=0;
				$totalnetto=0;
				$totaljjggrading=array();
				$totalpersengrading=array();
				foreach ($arrnotransaksi as $notransaksi => $value) {
					$bjr=0;
					if ($value['netto']!=0 && $value['janjang']!=0) {
						$bjr=$value['netto']/$value['janjang'];
					}
					$no+=1;
					$tab.="<tr class=rowcontent>";
					$tab.="<td style='text-align:left;'>".$no."</td>";
					$tab.="<td style='text-align:left;'>".$notransaksi."</td>";
					$tab.="<td style='text-align:left;'>".$value['waktukeluar']."</td>";
					$tab.="<td style='text-align:left;'>".$value['divcode']."</td>";
					$tab.="<td style='text-align:left;'>".$value['nokendaraan']."</td>";
					$tab.="<td style='text-align:right;'></td>";
					$tab.="<td style='text-align:right;'></td>";
					$tab.="<td style='text-align:right;'></td>";
					$tab.="<td style='text-align:right;'>".$value['janjang']."</td>";
					$tab.="<td style='text-align:right;'>".number_format($bjr)."</td>";
					$tab.="<td style='text-align:right;'>".number_format($value['netto'])."</td>";

					foreach ($persenkg as $key => $val) {
						$tab.="<td style='text-align:right;'></td>";
						$tab.="<td style='text-align:right;'></td>";
						$tab.="<td style='text-align:right;'>".number_format(@$valueSortasi[$notransaksi][$key][$val['jjg']])."</td>";
						$tab.="<td style='text-align:right;'>".number_format(@$valueSortasi[$notransaksi][$key][$val['persen']])."</td>";
					}
					$tab.="</tr>";

					$totaljanjang+=$value['janjang'];
					$totalbjr+=$bjr;
					$totalnetto+=$value['netto'];

					foreach ($persenkg as $key => $val) {
						@$totaljjggrading[$key][$val['jjg']]+=@$valueSortasi[$notransaksi][$key][$val['jjg']];
						@$totalpersengrading[$key][$val['persen']]+=(@$valueSortasi[$notransaksi][$key][$val['jjg']]/$totaljanjang)*100;
					}
				}
				$tab.="
				<tr class=rowcontent>
					<td colspan=6 style='text-align:center;font-weight:bold;'>Total</td>
					<td style='text-align:right;font-weight:bold;'></td>
					<td style='text-align:right;font-weight:bold;'></td>
					<td style='text-align:right;font-weight:bold;'>".number_format($totaljanjang)."</td>
					<td style='text-align:right;font-weight:bold;'>".$totalbjr."</td>
					<td style='text-align:right;font-weight:bold;'>".number_format($totalnetto)."</td>";

					foreach ($persenkg as $key => $val) {
						$tab.="<td style='text-align:right;'></td>";
						$tab.="<td style='text-align:right;'></td>";
						$tab.="<td style='text-align:right;'>".$totaljjggrading[$key][$val['jjg']]."</td>";
						$tab.="<td style='text-align:right;'>".$totalpersengrading[$key][$val['persen']]."</td>";
					}
				
				$tab.="
				</tr>
				";
			$grandtotaljanjang+=$totaljanjang;
			$grandtotalbjr+=$totalbjr;
			$grandtotalnetto+=$totalnetto;

			foreach ($persenkg as $key => $val) {
						@$grandtotaljjggrading[$key][$val['jjg']]+=@$totaljjggrading[$key][$val['jjg']];
						@$grandtotalpersengrading[$key][$val['persen']]+=(@$totaljjggrading[$key][$val['jjg']]/$grandtotaljanjang)*100;
					}
			}
			$tab.="
			<tr class=rowcontent>
				<td colspan=6 style='text-align:center;font-weight:bold;'>Grand Total</td>
				<td style='text-align:right;font-weight:bold;'></td>
					<td style='text-align:right;font-weight:bold;'></td>
					<td style='text-align:right;font-weight:bold;'>".number_format($grandtotaljanjang)."</td>
					<td style='text-align:right;font-weight:bold;'>".number_format($grandtotalbjr)."</td>
					<td style='text-align:right;font-weight:bold;'>".number_format($grandtotalnetto)."</td>";
				foreach ($persenkg as $key => $val) {
					$tab.="<td style='text-align:right;'></td>";
					$tab.="<td style='text-align:right;'></td>";
					$tab.="<td style='text-align:right;'>".$grandtotaljjggrading[$key][$val['jjg']]."</td>";
					$tab.="<td style='text-align:right;'>".$grandtotalpersengrading[$key][$val['persen']]."</td>";
				}
			$tab.="
			</tr>
			";
		}

		$tab.="
		</tbody>
		</table>";
		echo $tab;
	break;
}
?>
