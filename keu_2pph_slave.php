<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}



switch($method){
	case 'loaddata':
		$tab="";
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['tanggal']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['unit']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['notransaksi']." Kas/Bank</th>
				<th style='text-align:center;'>".$_SESSION['lang']['sumber']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['nojurnal']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['novoucher']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['noakun']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['supplier']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['bayarkemasukdari']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['noinvoice']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['nilai']." (Rp)</th>
				<th style='text-align:center;'>".$_SESSION['lang']['nodok']."</th>
			</tr>
		</thead>
		<tbody >";
		
		$where="";
		$wherex="";
		$no=0;
		if($param['unit']!=''){
			$where.=" and kodeorg='".$param['unit']."'";
			$wherex.=" and kodeorg='".$param['unit']."'";
		}
		if($param['jenis']!=''){
			$where.=" and noakun='".$param['jenis']."'";
		}
		$wherex.=" and pembayaran='1'";
		
		$nmcustomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
		$nmjurnal=makeOption($dbname,'keu_5parameterjurnal','jurnalid,keterangan');
		
		## GET KASBANK
		$arrkb=array();
		$arrkbdt=array();
		$str="select * from ".$dbname.".keu_kasbankdtht_vw where (tanggal between '".tanggalsystem($param['tanggal'])."' and '".tanggalsystem($param['tanggal2'])."') and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['pt']."') ".$wherex."";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrkb[$val['notransaksi']][$val['noakun']]['notransaksi']=$val['notransaksi'];
			$arrkb[$val['notransaksi']][$val['noakun']]['novoucher']=$val['novoucher'];
			$arrkb[$val['notransaksi']][$val['noakun']]['noinvoice']=$val['keterangan1'];
			$arrkb[$val['notransaksi']][$val['noakun']]['bayarkepada']=$val['bayarkepada'];
			if($val['kodesupplier']!=''){
				$arrkbdt[$val['notransaksi']]['assignment']=(getNamaSupplier($val['kodesupplier'])!=''?getNamaSupplier($val['kodesupplier']):$nmcustomer[$val['kodesupplier']]);				
			}
			if($val['keterangan1']!=''){
				$arrkbdt[$val['notransaksi']]['noinvoice']=$val['keterangan1'];				
			}
		}
		
		## GET JURNAL
		$str="select * from ".$dbname.".keu_jurnaldt_vw where noakun like '213%' and (tanggal between '".tanggalsystem($param['tanggal'])."' and '".tanggalsystem($param['tanggal2'])."') and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['pt']."') ".$where." order by tanggal asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormal($val['tanggal'])."</td>";
			$tab.="<td style='text-align:center;'>".$val['kodeorg']."</td>";
			$tab.="<td style='text-align:center;'>".$arrkb[$val['noreferensi']][$val['noakun']]['notransaksi']."</td>";
			$tab.="<td style='text-align:center;'>".$nmjurnal[$val['kodejurnal']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['nojurnal']."</td>";
			$tab.="<td style='text-align:center;'><label style='cursor:pointer;color:blue' title='Click to Preview Detail' onclick=\"detailkasbank('".$arrkb[$val['noreferensi']][$val['noakun']]['notransaksi']."')\">".$arrkb[$val['noreferensi']][$val['noakun']]['novoucher']."</label></td>";
			$tab.="<td style='text-align:center;'>".$val['noakun']."</td>";
			$tab.="<td style='text-align:left;'>".($arrkbdt[$val['noreferensi']]['assignment']==''?$nmcustomer[$val['kodecustomer']]:$arrkbdt[$val['noreferensi']]['assignment'])."</td>";
			$tab.="<td style='text-align:center;'>".$arrkb[$val['noreferensi']][$val['noakun']]['bayarkepada']."</td>";
			$tab.="<td style='text-align:center;'>".$arrkbdt[$val['noreferensi']]['noinvoice']."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['jumlah'])."</td>";
			$tab.="<td style='text-align:center;'>".$val['nodok']."</td>";
			$tab.="</tr>";
		}
			
		$tab.="</tbody>";
		
		$tab.="<tfoot>";
		// $tab.="<tr style='font-weight:bold'>";
		// $tab.="<td style='text-align:center;' colspan=13>T O T A L</td>";
		// $tab.="<td align=right>".hidezerodecimal($ttlberatmasuk)."</td>";
		// $tab.="<td style='text-align:right;'>".hidezerodecimal($ttlberatkeluar)."</td>";
		// $tab.="<td style='text-align:right;'>".hidezerodecimal($ttlnetto)."</td>";
		// $tab.="<td style='text-align:right;'>".hidezerodecimal($ttlpotongan)."</td>";
		// $tab.="<td style='text-align:right;'>".hidezerodecimal($ttlberatbersih)."</td>";
		// $tab.="</tr>";
		$tab.="</tfoot>";
		
		$tab.="</table>";
		echo $tab;
	break;
	
	case'getunit':
		## GET LIST UNIT
		$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['pt']."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optunit;
	break;
}
?>
