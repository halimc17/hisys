<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

$nmsup=makeOption($dbname,'msvendor','vendorcode,vendorname',"supplier='1'");

switch($method){
	case 'loaddata':
		$str="select kode,deskripsi,persen,kg from ".$dbname.".mssortasi where status='1' AND persen != '' AND kg != '' order by kode";
		$res=fetchData($str);
		$baris=0;
		foreach ($res as $val){
			$msgrading[$val['kode']] = $val['deskripsi'];
			$persenkg[$val['kode']]['persen'] = $val['persen'];
			$persenkg[$val['kode']]['kg'] = $val['kg'];

			$baris+=1;
		}

		$baris=$baris*2;
		$tab="";
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th rowspan=3 style='text-align:center;'>No</th>
				<th rowspan=3 style='text-align:center;'>No Tiket</th>
				<th rowspan=3 style='text-align:center;'>Tanggal</th>
				<th rowspan=3 style='text-align:center;'>Supplier</th>
				<th rowspan=3 style='text-align:center;'>No Kendaraan</th>
				<th rowspan=3 style='text-align:center;'>Janjang</th>
				<th rowspan=3 style='text-align:center;'>Berat Netto (Kg)</th>
				<th colspan=$baris style='text-align:center;'>Tandan di Grading</th>
			</tr>";
			$tab.="<tr class=rowheader>";
			foreach ($msgrading as $key => $val) {
				$tab.="<th style='text-align:center;' colspan=2>".$val."</th>";
			}
			$tab.="</tr>";
			$tab.="<tr class=rowheader>";
			foreach ($msgrading as $key => $val) {
				$tab.="<th style='text-align:center;'>%</th>";
				$tab.="<th style='text-align:center;'>Jumlah</th>";
			}
			$tab.="</tr>";
		$tab.="</thead>
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

		$str="select compcode from ".$dbname.".mssystem limit 1";
		$res=fetchData($str);
		$pt=$res[0]['compcode'];
		
		$where="";
		if($param['supplier']!=''){
			$where.=" and supplier='".$param['supplier']."'";
		}

		$where.=" and waktukeluar >= '".$tgl." 00:00:00' and waktukeluar <='".$tgl2." 23:59:59'";
		$str= "select * from ".$dbname.".wb where netto > 0 and kodebarang='".$kodeproduktbs."' and sumber='".$sumber."' and tipeunit='EKSTERNAL' ".$where." order by notransaksi asc";
		$res= fetchdata($str);
		foreach($res as $val){
			$arrdata[$val['supplier']][$val['notransaksi']]['waktukeluar'] 	= $val['waktukeluar'];
			$arrdata[$val['supplier']][$val['notransaksi']]['netto'] 		= $val['netto'];
			$arrdata[$val['supplier']][$val['notransaksi']]['janjang'] 		= $val['janjang'];
			$arrdata[$val['supplier']][$val['notransaksi']]['nokendaraan'] 	= $val['nokendaraan'];
			$arrdata[$val['supplier']][$val['notransaksi']]['supplier'] 	= $val['supplier'];
			
			$str1 = 'SELECT * FROM '.$dbname.'.trxsortasi WHERE notransaksi = "'.$val['notransaksi'].'"';
			$res1 = fetchdata($str1);
			foreach($res1 as $bar){
				$valueSortasi[$bar['notransaksi']][$bar['kode']][$bar['field']] = $bar['value'];
			}
		}

		
		$no=0;
		$colspanunit=($baris+7);
		if ($res) {
			foreach ($arrdata as $supplier => $arrnotransaksi) {
				// $tab.="<tr class=rowcontent>";
				// $tab.="<td colspan=$colspanunit>".$nmsup[$supplier]."</td>";
				// $tab.="</tr>";
				foreach ($arrnotransaksi as $notransaksi => $value) {
					$no+=1;
					$tab.="<tr class=rowcontent>";
					$tab.="<td style='text-align:left;'>".$no."</td>";
					$tab.="<td style='text-align:left;'>".$notransaksi."</td>";
					$tab.="<td style='text-align:left;'>".$value['waktukeluar']."</td>";
					$tab.="<td style='text-align:left;'>".$nmsup[$value['supplier']]."</td>";
					$tab.="<td style='text-align:left;'>".$value['nokendaraan']."</td>";
					$tab.="<td style='text-align:left;'>".$value['janjang']."</td>";
					$tab.="<td style='text-align:left;'>".$value['netto']."</td>";

					foreach ($persenkg as $key => $val) {
						$tab.="<td style='text-align:right;'>".number_format(@$valueSortasi[$notransaksi][$key][$val['persen']])."</td>";
						$tab.="<td style='text-align:right;'>".number_format(@$valueSortasi[$notransaksi][$key][$val['kg']])."</td>";
					}

					$tab.="</tr>";
				}
			}
		}
		

		$tab.="
		</table>";
		echo $tab;
	break;
}
?>
