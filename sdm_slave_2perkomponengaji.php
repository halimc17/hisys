<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');
$tipelaporan = checkPostGet('tipelaporan','');

$unit = checkPostGet('unit','');
$divisi = checkPostGet('divisi','');
$tgl1 = checkPostGet('tgl1','');
$tgl2 = checkPostGet('tgl2','');
$sistemgaji = checkPostGet('sistemgaji','');
$idkomponen = checkPostGet('idkomponen','');

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
		
		if($idkomponen=='64'){
			$arrtgl = rangeTanggalarr(tanggalsystemn($tgl1), tanggalsystemn($tgl2));
			
			$tab.="<table class=sortable  border='".$border."' ".$vwidth.">
			<thead>
			<tr class=rowcontent>
				<th align=center>No</th>
				<th align=center>".$_SESSION['lang']['nik']."</th>
				<th align=center>".$_SESSION['lang']['nama']."</th>
				<th align=center>".$_SESSION['lang']['subbagian']."</th>
				<th align=center>".$_SESSION['lang']['jabatan']."</th>";
				foreach($arrtgl as $key=>$val){
					$namahari=date('D', strtotime($val));
					$tab.="<th align=center>";
					if($namahari=='Sun'){
						$tab.="<font color=red>".substr($val,8,2)."</font>";
					}else{
						$tab.=" ".(substr($val,8,2))." " ;
					}
					$tab.="</th>";
				}
			$tab.="<th align=center><b>".$_SESSION['lang']['total']."</b></th>
			</tr>
			</thead>
			<tbody>";
			
			$arrdata=array();
			$arrkarid=array();
			$arrdtkar=array();
			$where="";
			if($unit!=''){
				$where.=" and b.lokasitugas = '".$unit."'";
			}
			if($divisi!=''){
				if($divisi=='office'){
					$where.=" and b.subbagian = ''";					
				}else{
					$where.=" and b.subbagian = '".$divisi."'";					
				}
			}
			$where.=" and a.tanggal >= '".tanggalsystem($tgl1)."' and a.tanggal <= '".tanggalsystem($tgl2)."'";
			if($sistemgaji!=''){
				$where.=" and b.sistemgaji='".$sistemgaji."'";
			}
			
			## GET DATA KARYAWAN
			$str="select b.karyawanid, b.nik, b.namakaryawan,b.subbagian,b.kodejabatan,a.insentiflibur,a.tanggal from ".$dbname.".sdm_absensidt a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.insentiflibur!='0' ".$where."  order by b.namakaryawan asc";
			$res=fetchdata($str);
			foreach($res as $val){
				$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['subbagian']."'");
				$optjbt=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$val['kodejabatan']."'");
				$arrkarid[$val['karyawanid']]=$val['karyawanid'];
				$arrdtkar[$val['karyawanid']]['nik']=$val['nik'];
				$arrdtkar[$val['karyawanid']]['nama']=$val['namakaryawan'];
				$arrdtkar[$val['karyawanid']]['subbagian']=$optorg[$val['subbagian']];
				$arrdtkar[$val['karyawanid']]['jabatan']=$optjbt[$val['kodejabatan']];
				
				$arrdata[$val['karyawanid']][$val['tanggal']]=$val['insentiflibur'];
			}
			
			$no=0;
			$arrtottgl=array();
			$totalall=0;
			foreach($arrkarid as $key){
				$no++;
				$tab.="<tr class=rowcontent style='vertical-align:top'>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=left>".$arrdtkar[$key]['nik']."</td>";
				$tab.="<td align=left>".$arrdtkar[$key]['nama']."</td>";
				$tab.="<td align=left>".$arrdtkar[$key]['subbagian']."</td>";
				$tab.="<td align=left>".$arrdtkar[$key]['jabatan']."</td>";
				
				$totalperkar=0;
				foreach($arrtgl as $tgl){
					$tab.="<td align=center>".hidezerodecimal($arrdata[$key][$tgl],0)."</td>";	
					$totalperkar+=$arrdata[$key][$tgl];
					$totalall+=$arrdata[$key][$tgl];
					$arrtottgl[$tgl]+=$arrdata[$key][$tgl];
				}
				
				$tab.="<td align=center>".hidezerodecimal($totalperkar,0)."</td>";					
				$tab.="</tr>";
			}
			$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
				<td colspan=5 align=center>T O T A L</td>";

				foreach($arrtgl as $tgl){
					$tab.="<td align=right>".hidezerodecimal($arrtottgl[$tgl])."</td>";
				}

				$tab.="<td align=right>".hidezerodecimal($totalall,0)."</td>
			</tr>";
				
			$tab.="</tbody>
			</table>";
		}
		
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
			$dompdf->stream("Daftar Rupiah per Komponen Gaji", array("Attachment" => false));
		}else{
			$titlelaporan="Daftar Rupiah per Komponen Gaji";
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
