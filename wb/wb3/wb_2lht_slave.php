<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}



switch($method){
	case 'loaddata':
		$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');
		$nmunit=makeOption($dbname,'msunit','UNITCODE,UNITNAME');
		$nmdivisi=makeOption($dbname,'msdivisi','DIVCODE,DIVNAME');
		$nmsupplier=makeOption($dbname,'msvendor','vendorcode,vendorname',"supplier='1'");
		$nmcustomer=makeOption($dbname,'mscustomer','custcode,custname');

		$tab="";
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>No</th>
				<th style='text-align:center;'>No Tiket</th>
				<th style='text-align:center;'>WB Condition</th>
				<th style='text-align:center;'>Tipe</th>
				<th style='text-align:center;'>Produk</th>
				<th style='text-align:center;'>Customer</th>
				<th style='text-align:center;'>Sales Order</th>
				<th style='text-align:center;'>Qty Split</th>
				<th style='text-align:center;'>Supplier</th>
				<th style='text-align:center;'>Unit</th>
				<th style='text-align:center;'>Divisi</th>
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
		
		$arrtipe=array('I'=>'Terima','O'=>'Kirim','II'=>'Terima TO/Blending','OO'=>'Kirim TO/Blending');
		$ttlberatmasuk=$ttlberatkeluar=$ttlnetto=$ttlpotongan=$ttlberatbersih=0;
		$tgl=substr($param['tanggal'],6,4).'-'.substr($param['tanggal'],3,2).'-'.substr($param['tanggal'],0,2);
		$tgl2=substr($param['tanggal2'],6,4).'-'.substr($param['tanggal2'],3,2).'-'.substr($param['tanggal2'],0,2);

		$str="select millcode from ".$dbname.".mssystem where millcode like '%m'";
		$res=fetchData($str);
		if ($res) {
			$sumber='PABRIK';
		}else{
			$sumber='KEBUN';
		}
		
		
		$where="";
		if($param['produk']!=''){
			$where.=" and kodebarang='".$param['produk']."'";
		}

		if ($param['produk']=='90100000') {
			$tgl2bsk = strtotime('+1 day',strtotime($tgl2));
			$tgl2bsk = date('Y-m-d', $tgl2bsk);
			$where.=" and waktukeluar >= '".$tgl." 07:00:00' and waktukeluar <='".$tgl2bsk." 07:00:00'";
		}else{
			$where.=" and waktukeluar >= '".$tgl." 00:00:00' and waktukeluar <='".$tgl2." 23:59:59'";
		}

		$str= "select * from ".$dbname.".wb where netto > 0 and sumber='".$sumber."' ".$where." order by notransaksi, waktukeluar asc";
		$res= fetchdata($str);
		$no=0;
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
			$tab.="<td style='text-align:left;'>".getNamaProduk($val['kodebarang'])."</td>";				
			$tab.="<td style='text-align:left;'>".@$nmcustomer[$val['customer']]."</td>";
			$tab.="<td style='text-align:left;'>".$val['kontrakjual']."</br>".$val['kontrakjual2']."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['nettosplit'])."</br>".hidezerodecimal($val['nettosplit2'])."</td>";
			$tab.="<td style='text-align:left;'>".@$nmsupplier[$val['supplier']]."</td>";
			$tab.="<td style='text-align:left;'>".@$nmunit[$val['unitcode']]."</td>";
			$tab.="<td style='text-align:left;'>".@$nmdivisi[$val['divcode']]."</td>";
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
		$tab.="<td style='text-align:center;' colspan=16>T O T A L</td>";
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
