<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

$nmunit=makeOption($dbname,'msunit','UNITCODE,UNITNAME');
$tgl=tanggalsystemn($param['tanggal']);

$tglbesok = strtotime('+1 day',strtotime($tgl));
$tglbesok = date('Y-m-d', $tglbesok);

switch($method){
	case 'loaddata':
		$tab="";
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=80%>
		<thead>
			<tr class=rowheader>
				<th colspan=5 style='text-align:left;'>HASIL PENIMBANGAN : TBS,CPO,PK</th>
			</tr>
			<tr class=rowheader>
				<th colspan=5 style='text-align:left;'>HARI : </th>
			</tr>
			<tr class=rowheader>
				<th colspan=5 style='text-align:left;'>TANGGAL : ".$param['tanggal']."</th>
			</tr>
			<tr class=rowheader>
				<th rowspan=2 style='text-align:center;'>Diterima</th>
				<th colspan=3 style='text-align:center;'>JUMLAH</th>
				<th rowspan=2 style='text-align:center;'>AVG/MOBIL</th>
			</tr>
			<tr class=rowheader>
				<th style='text-align:center;'>BERAT</th>
				<th style='text-align:center;'>SELISIH</th>
				<th style='text-align:center;'>TRIP</th>
			</tr>
			";
		$tab.="</thead>";
		$tab.="<tbody>";

		$str="select * from ".$dbname.".msunit where tipeunit in ('INTERNAL','PLASMA') and tipe='KEBUN' and unitstatus='1'";
		$res=fetchData($str);
		foreach ($res as $val){
			$arrunit[$val['tipeunit']][$val['unitcode']]=$val['unitname'];
		}

		$str="select unitcode,tipeunit, sum(netto) as netto, count(notransaksi) as rit, avg(netto) as avgmobil from ".$dbname.".wb where waktukeluar >= '".$tgl." 07:00:00' and waktukeluar <='".$tglbesok." 06:59:59' and netto > 0 and kodebarang='".$kodeproduktbs."' and tipeunit in ('INTERNAL','PLASMA') group by unitcode";
		$res=fetchData($str);
		foreach ($res as $val){
			$nettotbs[$val['unitcode']]=$val['netto'];
			$triptbs[$val['unitcode']]=$val['rit'];
			$avgtbs[$val['unitcode']]=$val['avgmobil'];

			$totalnettotbs+=$val['netto'];
			$totaltriptbs+=$val['rit'];
			$totalavgtbs+=$val['avgmobil'];
		}

		
		$str="select * from ".$dbname.".msvendor where supplier='1' and vendorstatus='1'";
		$res=fetchData($str);
		foreach ($res as $val){
			$arrsupplier['EKSTERNAL'][$val['vendorcode']]=$val['vendorname'];
		}

		$str="select supplier,tipeunit, sum(netto) as netto, count(notransaksi) as rit, avg(netto) as avgmobil from ".$dbname.".wb where waktukeluar >= '".$tgl." 07:00:00' and waktukeluar <='".$tglbesok." 06:59:59' and netto > 0 and kodebarang='".$kodeproduktbs."' and tipeunit in ('EKSTERNAL') group by supplier";
		$res=fetchData($str);
		foreach ($res as $val){
			$nettotbseksternal[$val['supplier']]=$val['netto'];
			$triptbseksternal[$val['supplier']]=$val['rit'];
			$avgtbseksternal[$val['supplier']]=$val['avgmobil'];

			$totalnettotbseksternal+=$val['netto'];
			$totaltriptbseksternal+=$val['rit'];
			$totalavgtbseksternal+=$val['avgmobil'];
		}

		$arrcpopk = array($kodeprodukcpo => $kodeprodukcpo, $kodeprodukpk => $kodeprodukpk);

		$str="select * from ".$dbname.".wb where waktukeluar like '".$tgl."%' and netto > 0 and kodebarang in ('".$kodeprodukcpo."','".$kodeprodukpk."') and wbcond='Normal'";
		$res=fetchData($str);
		foreach ($res as $val){
			$nettocpopk[$val['kodebarang']]+=$val['netto'];
			$tripcpopk[$val['kodebarang']]+=1;
		}

		foreach ($arrunit as $tipeunit => $arrunitcode) {
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=5 style='font-weight:bold;'>".$tipeunit."</td>";
			$tab.="</tr>";
			$subjumlahnetto=0;
			$subjumlahtrip=0;
			$subjumlahavg=0;
			foreach ($arrunitcode as $unitcode => $unitname) {
				$tab.="<tr class=rowcontent>";
				$tab.="<td>".$unitname."</td>";
				$tab.="<td style='text-align:right'>".number_format($nettotbs[$unitcode])."</td>";
				$tab.="<td style='text-align:right'></td>";
				$tab.="<td style='text-align:right'>".number_format($triptbs[$unitcode])."</td>";
				$tab.="<td style='text-align:right'>".number_format($avgtbs[$unitcode])."</td>";
				$tab.="</tr>";

				$subjumlahnetto+=$nettotbs[$unitcode];
				$subjumlahtrip+=$triptbs[$unitcode];
				$subjumlahavg+=$avgtbs[$unitcode];
			}
			$tab.="<tr class=rowcontent>
			<td style='font-weight:bold;'>Sub Jumlah</td>
			<td style='font-weight:bold;text-align:right'>".number_format($subjumlahnetto)."</td>
			<td style='font-weight:bold;text-align:right'></td>
			<td style='font-weight:bold;text-align:right'>".number_format($subjumlahtrip)."</td>
			<td style='font-weight:bold;text-align:right'>".number_format($subjumlahavg)."</td>
			</tr>";
		}

		foreach ($arrsupplier as $tipesupplier => $arrsuppliercode) {
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=5 style='font-weight:bold;'>".$tipesupplier."</td>";
			$tab.="</tr>";
			$subjumlahnettoeksternal=0;
			$subjumlahtripeksternal=0;
			$subjumlahavgeksternal=0;
			foreach ($arrsuppliercode as $suppliercode => $suppliername) {
				$tab.="<tr class=rowcontent>";
				$tab.="<td>".$suppliername."</td>";
				$tab.="<td style='text-align:right'>".number_format($nettotbseksternal[$suppliercode])."</td>";
				$tab.="<td style='text-align:right'></td>";
				$tab.="<td style='text-align:right'>".number_format($triptbseksternal[$suppliercode])."</td>";
				$tab.="<td style='text-align:right'>".number_format($avgtbseksternal[$suppliercode])."</td>";
				$tab.="</tr>";

				$subjumlahnettoeksternal+=$nettotbseksternal[$suppliercode];
				$subjumlahtripeksternal+=$triptbseksternal[$suppliercode];
				$subjumlahavgeksternal+=$avgtbseksternal[$suppliercode];
			}
			$tab.="<tr class=rowcontent>
			<td style='font-weight:bold;'>Sub Jumlah</td>
			<td style='font-weight:bold;text-align:right'>".number_format($subjumlahnettoeksternal)."</td>
			<td style='font-weight:bold;text-align:right'></td>
			<td style='font-weight:bold;text-align:right'>".number_format($subjumlahtripeksternal)."</td>
			<td style='font-weight:bold;text-align:right'>".number_format($subjumlahavgeksternal)."</td>
			</tr>";
		}

		$tab.="<tr class=rowcontent>
		<td style='font-weight:bold;'>TOTAL TBS DITERIMA</td>
		<td style='font-weight:bold;text-align:right'>".number_format($totalnettotbs+$totalnettotbseksternal)."</td>
		<td style='font-weight:bold;text-align:right'></td>
		<td style='font-weight:bold;text-align:right'>".number_format($totaltriptbs+$totaltriptbseksternal)."</td>
		<td style='font-weight:bold;text-align:right'>".number_format($totalavgtbs+$totalavgtbseksternal)."</td>
		</tr>";

		foreach ($arrcpopk as $kodebarang) {
			$tab.="<tr class=rowcontent>
			<td>Total Pengiriman ".getNamaBrg($kodebarang)."</td>
			<td style='font-weight:bold;text-align:right'>".number_format($nettocpopk[$kodebarang])."</td>
			<td style='font-weight:bold;text-align:right'></td>
			<td style='font-weight:bold;text-align:right'>".number_format($tripcpopk[$kodebarang])."</td>
			<td style='font-weight:bold;text-align:right'></td>
			</tr>";
		}


		$tab.="
		</tbody>
		</table>";
		echo $tab;
	break;
}
?>
