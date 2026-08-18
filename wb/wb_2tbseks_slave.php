<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}


$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');
$nmsupplier=makeOption($dbname,'msvendor','vendorcode,vendorname',"supplier='1'");

switch($method){
	case 'loaddata':
		$tab="";
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>No</th>
				<th style='text-align:center;'>No Tiket</th>
				<th style='text-align:center;'>Tanggal</th>
				<th style='text-align:center;'>No Kendaraan</th>
				<th style='text-align:center;'>Supplier</th>
				<th style='text-align:center;'>Bruto</th>
				<th style='text-align:center;'>Tara</th>
				<th style='text-align:center;'>Netto 1</th>
				<th style='text-align:center;'>Potongan <br> Wajib</th>
				<th style='text-align:center;'>Potongan <br> Kualitas</th>
				<th style='text-align:center;'>Netto 2</th>
				<th style='text-align:center;'>Jam <br> Masuk</th>
				<th style='text-align:center;'>Jam <br> Keluar</th>
				<th style='text-align:center;'> % Potongan <br> Wajib</th>
				<th style='text-align:center;'> % Potongan <br> Kualitas</th>
				<th style='text-align:center;'>Keterangan</th>
			</tr>
		</thead>
		<tbody >";

		$potwajib=makeOption($dbname,'mscontractpurchase','ctrno,potongan');
		
		$arrtipe=array('I'=>'Terima','O'=>'Kirim','II'=>'Terima TO/Blending','OO'=>'Kirim TO/Blending');
		$ttlberatmasuk=$ttlberatkeluar=$ttlnetto=$ttlpotongan=$ttlberatbersih=$ttlpotonganwajib=0;
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
		if($param['supplier']!=''){
			$where.=" and supplier='".$param['supplier']."'";
		}
		$tgl2bsk = strtotime('+1 day',strtotime($tgl2));
		$tgl2bsk = date('Y-m-d', $tgl2bsk);
		$where.=" and waktukeluar >= '".$tgl." 00:00:00' and waktukeluar <='".$tgl2bsk." 07:00:00'";
		$str= "select * from ".$dbname.".wb where netto > 0 and kodebarang='".$kodeproduktbs."' and sumber='".$sumber."' and kodebarang='".$kodeproduktbs."' and tipeunit = 'EKSTERNAL' ".$where." order by notransaksi asc";
		$res= fetchdata($str);
		$no=0;
		foreach($res as $val){
			$no+=1;
			
			$netto1=$val['netto']+$val['potongan']+$val['potonganwajib'];

			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$val['notransaksi']."</td>";
			$tab.="<td style='text-align:left;'>".tanggalnormal($val['waktukeluar'])."</td>";
			$tab.="<td style='text-align:left;'>".$val['nokendaraan']."</td>";			
			$tab.="<td style='text-align:left;'>".$nmsupplier[$val['supplier']]."</td>";		
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['beratmasuk'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['beratkeluar'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($netto1)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['potonganwajib'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['potongan'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['netto'])."</td>";
			$tab.="<td style='text-align:center;min-width:70px'>".substr($val['waktumasuk'], 11,5)."</td>";
			$tab.="<td style='text-align:center;min-width:70px'>".substr($val['waktukeluar'], 11,5)."</td>";
			$tab.="<td style='text-align:right;'>".abs($potwajib[$val['kontrakbeli']])."%</td>";
			$tab.="<td style='text-align:right;'>".number_format((($val['potongan']/$netto1)*100),2)."</td>";
			$tab.="<td style='text-align:left;'>".$val['keterangan']."</td>";
			$tab.="</tr>";
			
			$ttlberatmasuk+=$val['beratmasuk'];
			$ttlberatkeluar+=$val['beratkeluar'];
			$ttlnetto+=$netto1;
			$ttlpotongan+=$val['potongan'];
			$ttlpotonganwajib+=$val['potonganwajib'];
			$ttlberatbersih+=$val['netto'];
		}
		$tab.="</tbody>
		<tfoot>";
		
		$tab.="<tr style='font-weight:bold'>";
		$tab.="<td style='text-align:center;' colspan=5>T O T A L</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlberatmasuk)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlberatkeluar)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlnetto)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlpotongan)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlpotonganwajib)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlberatbersih)."</td>";
		$tab.="<td style='text-align:right;'></td>";
		$tab.="<td style='text-align:right;'></td>";
		$tab.="<td style='text-align:right;'></td>";
		$tab.="<td style='text-align:right;'></td>";
		$tab.="<td style='text-align:right;'></td>";
		$tab.="</tr>";
		
		$tab.="</tfoot>
		</table>";
		echo $tab;
	break;
}
?>
