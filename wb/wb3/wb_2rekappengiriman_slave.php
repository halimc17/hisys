<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$str="select compcode,millcode,idwb from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['millcode'];
$compcode=$res[0]['compcode'];
$idwb=$res[0]['idwb'];

switch($method){
	case 'getso':
        $optso="<option value=''>Silahkan pilih</option>";
		## GET SO
		$str="select * from ".$dbname.".msso where sostatus='1' and compcode='".$compcode."' and kodeproduk='".$param['product']."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$optso.="<option value='".$val['noso']."'>".$val['noso']."</option>";
		}
		
		echo $optso;
    break;

	case 'loaddata':
		$nmcustomer=makeOption($dbname,'mscustomer','custcode,custname');

		$arrtipe=array('I'=>'Terima','O'=>'Kirim','II'=>'Terima TO/Blending','OO'=>'Kirim TO/Blending');
		$tab="";
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>No</th>
				<th style='text-align:center;'>No Tiket</th>
				<th style='text-align:center;'>WB Condition</th>
				<th style='text-align:center;'>Tipe</th>
				<th style='text-align:center;'>Tiket Ref</th>
				<th style='text-align:center;'>Produk</th>
				<th style='text-align:center;'>Customer</th>
				<th style='text-align:center;'>No SO</th>
				<th style='text-align:center;'>Supir</th>
				<th style='text-align:center;'>No Kendaraan</th>
				<th style='text-align:center;'>Transportir</th>
				<th style='text-align:center;'>Tanggal Masuk</th>
				<th style='text-align:center;'>Tanggal Keluar</th>
				<th style='text-align:center;'>Timbang 1</th>
				<th style='text-align:center;'>Timbang 2</th>
				<th style='text-align:center;'>Berat Kotor</th>
				<th style='text-align:center;'>Potongan</th>
				<th style='text-align:center;'>Berat Bersih</th>
			</tr>
		</thead>
		<tbody >";
		
		
		$str= "select * from ".$dbname.".wb where netto > 0 and kontrakjual='".$param['noso']."' order by notransaksi asc";
		$res= fetchdata($str);
		$no=0;
		$ttlberatmasuk=0;
		$ttlberatkeluar=0;
		$ttlnetto=0;
		$ttlpotongan=0;
		$ttlberatbersih=0;
		foreach($res as $val){
			$no+=1;

			$beratkotor=$val['netto']+$val['potongan']+$val['potonganwajib'];
			$beratbersih=$val['netto'];
			
			if ($val['wbcond']=='Return') {
				$beratkotor="-".$beratkotor;
				$beratbersih="-".$val['netto'];
			}
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$val['notransaksi']."</td>";
			$tab.="<td style='text-align:left;'>".$val['wbcond']."</td>";
			$tab.="<td style='text-align:left;'>".$arrtipe[$val['in_out']]."</td>";
			$tab.="<td style='text-align:left;'>".$val['tiketref']."</td>";
			$tab.="<td style='text-align:left;'>".getNamaProduk($val['kodebarang'])."</td>";				
			$tab.="<td style='text-align:left;'>".$nmcustomer[$val['customer']]."</td>";
			$tab.="<td style='text-align:left;'>".$val['kontrakjual']."</td>";
			$tab.="<td style='text-align:left;'>".$val['supir']."</td>";
			$tab.="<td style='text-align:left;'>".$val['nokendaraan']."</td>";
			$tab.="<td style='text-align:left;'>".getNamaSupplier($val['transportir'])."</td>";				
			$tab.="<td style='text-align:center;min-width:70px'>".tanggalnormald($val['waktumasuk'])."</td>";
			$tab.="<td style='text-align:center;min-width:70px'>".tanggalnormald($val['waktukeluar'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['beratmasuk'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['beratkeluar'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($beratkotor)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['potongan']+$val['potonganwajib'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($beratbersih)."</td>";
			$tab.="</tr>";
			
			$ttlberatmasuk+=$val['beratmasuk'];
			$ttlberatkeluar+=$val['beratkeluar'];
			$ttlnetto+=$beratkotor;
			$ttlpotongan+=($val['potongan']+$val['potonganwajib']);
			$ttlberatbersih+=$beratbersih;
		}
		$tab.="</tbody>
		<tfoot>";
		
		$tab.="<tr style='font-weight:bold'>";
		$tab.="<td style='text-align:center;' colspan=13>T O T A L</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlberatmasuk)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlberatkeluar)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlnetto)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlpotongan)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlberatbersih)."</td>";
		$tab.="</tr>";
		
		$tab.="</tfoot>
		</table>";
		echo $tab;
	break;
}
?>
