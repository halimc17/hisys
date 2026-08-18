<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

$nmunit=makeOption($dbname,'msunit','UNITCODE,UNITNAME');
$nmdivisi=makeOption($dbname,'msdivisi','DIVCODE,DIVNAME');

switch($method){
	case 'loaddata':
		$tab="";
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>No</th>
				<th style='text-align:center;'>No Tiket</th>
				<th style='text-align:center;'>Tanggal</th>
				<th style='text-align:center;'>No QR</th>
				<th style='text-align:center;'>Unit</th>
				<th style='text-align:center;'>Divisi</th>
				<th style='text-align:center;'>No Kendaraan</th>
				<th style='text-align:center;'>Janjang</th>
				<th style='text-align:center;'>Bruto</th>
				<th style='text-align:center;'>Tara</th>
				<th style='text-align:center;'>Netto</th>
				<th style='text-align:center;'>Jam <br> Masuk</th>
				<th style='text-align:center;'>Jam <br> Keluar</th>
				<th style='text-align:center;'>Supir</th>
				<th style='text-align:center;'>Keterangan</th>
			</tr>
		</thead>
		<tbody >";
		
		$arrtipe=array('I'=>'Terima','O'=>'Kirim','II'=>'Terima TO/Blending','OO'=>'Kirim TO/Blending');
		$ttlberatmasuk=$ttlberatkeluar=$ttlnetto=$ttlpotongan=$ttlberatbersih=$ttljanjang=0;
		$tgl=substr($param['tanggal'],6,4).'-'.substr($param['tanggal'],3,2).'-'.substr($param['tanggal'],0,2);
		$tgl2=substr($param['tanggal2'],6,4).'-'.substr($param['tanggal2'],3,2).'-'.substr($param['tanggal2'],0,2);

		$str="select millcode from ".$dbname.".mssystem where millcode like '%m'";
		$res=fetchData($str);
		if ($res) {
			$sumber='PABRIK';
		}else{
			$sumber='KEBUN';
		}

		$str="select compcode from ".$dbname.".mssystem limit 1";
		$res=fetchData($str);
		$pt=$res[0]['compcode'];
		
		$where="";
		if($param['unit']!=''){
			$where.=" and unitcode='".$param['unit']."'";
		}else{
			$where.=" and unitcode in (select unitcode from ".$dbname.".msunit where compcode='".$pt."' and tipeunit='INTERNAL')";
		}
		$tgl2bsk = strtotime('+1 day',strtotime($tgl2));
		$tgl2bsk = date('Y-m-d', $tgl2bsk);
		$where.=" and waktukeluar >= '".$tgl." 07:00:00' and waktukeluar <='".$tgl2bsk." 06:59:59'";
		$str= "select * from ".$dbname.".wb where netto > 0 and kodebarang='".$kodeproduktbs."' and sumber='".$sumber."' ".$where." order by notransaksi asc";
		$res= fetchdata($str);
		$no=0;
		foreach($res as $val){
			$no+=1;
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$val['notransaksi']."</td>";
			$tab.="<td style='text-align:left;'>".tanggalnormal($val['waktukeluar'])."</td>";
			$tab.="<td style='text-align:left;'>".$val['qr']."</td>";
			$tab.="<td style='text-align:left;'>".$nmunit[$val['unitcode']]."</td>";
			$tab.="<td style='text-align:left;'>".@$nmdivisi[$val['divcode']]."</td>";				
			$tab.="<td style='text-align:left;'>".$val['nokendaraan']."</td>";
			$tab.="<td style='text-align:right;'>".number_format($val['janjang'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['beratmasuk'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['beratkeluar'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['netto'])."</td>";
			$tab.="<td style='text-align:center;min-width:70px'>".substr($val['waktumasuk'], 11,5)."</td>";
			$tab.="<td style='text-align:center;min-width:70px'>".substr($val['waktukeluar'], 11,5)."</td>";
			$tab.="<td style='text-align:left;'>".$val['supir']."</td>";
			$tab.="<td style='text-align:left;'>".$val['keterangan']."</td>";
			$tab.="</tr>";
			
			$ttlberatmasuk+=$val['beratmasuk'];
			$ttlberatkeluar+=$val['beratkeluar'];
			$ttlberatbersih+=$val['netto'];
			$ttljanjang+=$val['janjang'];
		}
		$tab.="</tbody>
		<tfoot>";
		
		$tab.="<tr style='font-weight:bold'>";
		$tab.="<td style='text-align:center;' colspan=7>T O T A L</td>";
		$tab.="<td align=right>".hidezerodecimal($ttljanjang)."</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlberatmasuk)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlberatkeluar)."</td>";
		$tab.="<td style='text-align:right;'>".hidezerodecimal($ttlberatbersih)."</td>";
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
