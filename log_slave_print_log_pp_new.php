<?php
error_reporting(0);
$mobileValid = false;
if(isset($_POST['par']) or isset($_GET['par'])){
	$validasiPostMobile = explode(" ",$_POST['par']);
	$validasiGetMobile = explode(" ",$_GET['par']);
	if($validasiGetMobile[0] == "owlApp" or $validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php'); 
}
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');
require_once('lib/zLib.php');
include_once('lib/zPdfMaster.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$urlefil=checkPostGet('urlefil','0');

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
// $inisialisasiorganisasi=  makeOption($dbname, 'organisasi', 'kodeorganisasi,inisialisasiorganisasi');
$nmBrg=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$prioritas=  makeOption($dbname, 'log_prapodt', 'nopp,prioritas');
$tipepp=  makeOption($dbname, 'log_prapoht', 'nopp,tipepp');
$user_jp=  makeOption($dbname, 'user', 'karyawanid,namauser');


$updateby=  makeOption($dbname, 'log_prapodt', 'nopp,updateby');
$nopp = $column;

$kary_id=  makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
$jabatan=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$arrTipe = array('PR'=>'Purchase Request (INV)','SR'=>'Service Request (JASA)','CP'=>'Capex Request (ASET)','NR'=>'Non-Inventory Requset (NON-INV)');

#====================== Prepare Data
$ql="select a.unit,a.nopp,a.tanggal,a.dibuat, a.keterangan from ".$dbname.".`log_prapoht` a where a.nopp='".$column."'";
$pq=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
$pq->setFetchMode(PDO::FETCH_ASSOC);
$hsl=$pq->fetch();
$kdr=$hsl['unit'];
// $unit=substr($column,4,4);
// $unit_jp=substr($column,4,4);
$unit=$kdr;
$unit_jp=$kdr;
$keterangan=$hsl['keterangan'];

$sNmKry="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$hsl['dibuat']."'";
$qNmKry=$owlPDO->query($sNmKry) or die(print " Gagal: ".PDOException::getMessage());
$qNmKry->setFetchMode(PDO::FETCH_ASSOC);
$rNmKry=$qNmKry->fetch();
$dibuat=$rNmKry['namakaryawan'];


$sNmkntr="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdr."'";
$qNmkntr=$owlPDO->query($sNmkntr) or die(print " Gagal: ".PDOException::getMessage());
$qNmkntr->setFetchMode(PDO::FETCH_ASSOC);
$rNmkntr=$qNmkntr->fetch();
$nmKntr=$rNmkntr['namaorganisasi'];
$tgl=tanggalnormal($hsl['tanggal']);
$tgl_2=$hsl['tanggal'];

$query="select a.*,b.*,c.namabarang,c.satuan,d.spesifikasi from ".$dbname.".".$table." a inner join ".$dbname.".`log_prapodt` b on a.nopp=b.nopp inner join ".$dbname.".`log_5masterbarang` c on b.kodebarang=c.kodebarang  left join ".$dbname.".`log_5photobarang` d on c.kodebarang=d.kodebarang where a.nopp='".$column."'"; //echo $query; exit();
$result = fetchData($query);

$aprvkar=array();
$aprvtgl="";
$jlhaprv=0;



	// cek status PR
	$str="select * from ".$dbname.".approval where notransaksi='".$nopp."' order by level asc";
	$res=fetchdata($str);
	foreach ($res as $val) {
		$aprvkar[$val['karyawanid']] = $val['level'];
		$tglaprv[$val['karyawanid']] = '';
		$jlhaprv+=1;
	}
	
	$expNopp = explode('/',$column);
	$kodept = $expNopp[0];
	$kodeunit = $expNopp[1];
$arrHead = setheadreport('',$kodept);
$arrHeadUnit = setheadreport('',$kodeunit);
//@page { margin: 20; }
$tab.="<html>
<style type='text/css'>
	@page {
		margin-top: 20px;
		margin-left: 20px;
		margin-right: 20px;
		margin-bottom: 50px;
		
	}
	footer {
		position: fixed; 
		bottom: -10px; 
		left: 0px; 
		right: 0px;
		height: 50px; 
	}
	header {
		position: fixed; 
		top: 0px;
		left: 0px; 
		right: 0px;
		margin-top: 157px;
		font-size:11px;
		font-weight:bold;
	}
</style>";



## CREATE TITLE HEADER
$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$tab.="<body><header><table width=100% cellpadding=0 cellspacing=0 style='text-align:center'>
	<thead>
	<tr style='font-weight:bold;font-size:26px;'>
		<td style='transform:translate(0,10);color:green;font-weight:bold' colspan=2>".$nmOrg[getindukPT(explode('/',$nopp)[4])]."</td>
	</tr>
	<tr style='font-weight:bold;font-size:20px;text-decoration: underline'>
		<td style='transform:translate(0,10)' colspan=2>Surat Permintaan Pembelian</td>
	</tr>
	<tr style='font-weight:bold;font-size:14px;font-style: italic'>
		<td style='transform:translate(0,10)' colspan=2>Purchase Request Form</td>
	</tr>
	</table>
	<br>
	<br>

	<table width=100% cellpadding=2 cellspacing=2 style='font-size:14px'>
		<tr>
			<td style='width:50%;' valign=top>
				<table>
					<tr>
						<td>NO. S-PP</td>
						<td>:</td>
						<td>".$nopp."</td>
					</tr>
					<tr>
						<td>NO. KBB</td>
						<td>:</td>
						<td></td>
					</tr>
					<tr>
						<td>Tanggal</td>
						<td>:</td>
						<td>".$tgl."</td>
					</tr>
				</table>
			</td>
			<td style='width:50%;padding-left:100px' valign=top>
				<table>
					<tr>
						<td>Dari</td>
						<td>:</td>
						<td>Gudang</td>
					</tr>
					<tr>
						<td>Ke</td>
						<td>:</td>
						<td>Pengadaan</td>
					</tr>
				</table>
			</td>
		
		</tr>
	</thead>
	</table></header>";

$tab.="<table width='100%' cellpadding=1 border=1 cellspacing=0 style='padding-top:10px;font-size:11px;'>
			<tr style='font-size:12px;font-weight:bold;background-color:green;color:white'>
				<th rowspan=2 style='text-align:center;border:1px solid black'>Kode Barang</th>
				<th rowspan=2 style='text-align:center;border:1px solid black'>Nama Barang</th>
				<th rowspan=2 style='text-align:center;border:1px solid black'>Satuan</th>
				<th rowspan=1 colspan=2 style='text-align:center;border:1px solid black'>Posisi Stok</th>
				<th rowspan=2 style='text-align:center;border:1px solid black'>Fisik Diminta</th>
			</tr>
			<tr style='font-size:10px;font-weight:bold;background-color:green;color:white'>
				<th style='text-align:center;border:1px solid black'>Tanggal</th>
				<th style='text-align:center;border:1px solid black'>Jumlah</th>
			</tr>
		";

		foreach($result as $val){
			$tab.="
			<tr style='text-align:center;font-weight:normal'>
				<td>".$val['kodebarang']."</td>
				<td>".$nmBrg[$val['kodebarang']]."</td>
				<td>".$val['satuan']."</td>
				<td style='text-align:center'>".tanggalnormal($val['tgl_sdt'])."</td>
				<td style='text-align:right'>".number_format($val['stock'],2)."</td>
				<td style='text-align:right'>".number_format($val['jumlah'],2)."</td>
			</tr>
		";
	}
	$tab.="
		</table>

	<table width='100%' cellpadding=1 border=0 cellspacing=0 style='padding-top:100px;font-size:11px'>
	<tr>
	";

		// $countApp = getCountApproval('PR',$expNopp[1]);
		$tab.="<td style='text-align:center;'>Keterangan :</td>";

		$tab.="<td style='text-align:center;border:1px solid black'>Diajukan oleh</td>";

		// Membalikkan array $aprvkar agar teks "Disetujui oleh" muncul pertama
		// $aprvkar = array_reverse($aprvkar);
		$t_1 = count($aprvkar);
		// exit("warning : ".$t_1." ");
		// looping
		
		
		$no=1;
		if ($aprvkar != 0) {
			// exit("warning : ".$t_1." ");
			foreach ($aprvkar as $karid) {
				if($no == $t_1){
					$tab.="<td style='text-align:center;border:1px solid black'>Disetujui oleh</td>";
				}else{
					$tab.="<td style='text-align:center;border:1px solid black'>Diketahui oleh</td>";
				}
				$no++;
			}
			// exit("warning : ".$a." ");
		}
		else{
			$tab.="<td style='text-align:center;border:1px solid black'></td>";
		}
		// looping
		$tab.="</tr>
	<tr>";
	$optnmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$tab.="<td rowspan=2 style='text-align:center;vertical-align: bottom;width:50%;'></td>";

		$tab.="<td style='text-align:center;vertical-align: bottom;height:70px;width:50%;border:1px solid black'>
		
		<p style='opacity:0.4'>ELECTRONICALLY SIGNED BY</p>
		
		<span style='opacity:1'>
			".$optnmkar[$updateby[$nopp]]."
		</span> 
		
		</td>";
		// looping
		if ($aprvkar != 0) {
			foreach ($aprvkar as $karid => $key) {
				$tab.="<td style='text-align:center;vertical-align: bottom;height:70px;width:50%;border:1px solid black'>";


				$str_l_cekApproval="select status from ".$dbname.".approval where notransaksi='".$nopp."' and karyawanid = '".$karid."' and status='1' order by level desc limit 1";
				$res_l_cekApproval=fetchdata($str_l_cekApproval);
				$status_l_cekApproval = $res_l_cekApproval[0]['status'];

				if($status_l_cekApproval != ''){
					$tab.="<p style='opacity:0.4'>ELECTRONICALLY SIGNED BY</p>";
				}


				$tab.="
				<span style='opacity:1'>
				".$optnmkar[$karid]."
				</span>
				</td>";
			}
		}else{
			$tab.="<td style='text-align:center;border:1px solid black'></td>";
		}
		// looping
	$tab.="</tr>
	<tr>";
	$tab.="<td style='text-align:center;border:1px solid black'>".$jabatan[$kary_id[$updateby[$nopp]]]."</td>";
	// looping
		if ($aprvkar != 0) {
			foreach ($aprvkar as $karid => $key) {
				$tab.="<td style='text-align:center;border:1px solid black'>".$jabatan[$kary_id[$karid]]."</td>";
			}
		}else{
			$tab.="<td style='text-align:center;border:1px solid black'></td>";
		}
	// looping
	$tab.="
	</table>

		
		";

$dompdf = new Dompdf();
$dompdf->loadHtml($tab);
// $dompdf->setPaper('A4', 'portrait');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$sizefont=12;
$font = $dompdf->getFontMetrics()->get_font("Times-Roman", "");
	
		$dompdf->getCanvas()->page_text('480','15',"Cetak : " . date('d-m-Y H:i:s'), $font, ($sizefont-4), array(0,0,0),0,0,0);
		$dompdf->getCanvas()->page_text('480','25',"Page : {PAGE_NUM} / {PAGE_COUNT} ", $font, ($sizefont-4), array(0,0,0),0,0,0);
// $dompdf->getCanvas()->text(56, 20, 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', $font, 12);

## Print Out
if($urlefil=='0'){
	$dompdf->stream("PrintPOSO_".$column,array("Attachment"=>0));
}else{
	file_put_contents($urlefil, $dompdf->output());
}

// $dompdf = new Dompdf();
// $options = $dompdf->getOptions();
// $options->set(array('isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true));
// $dompdf->loadHtml($dokumen);
// $dompdf->setPaper('A4', 'portrait');
// $dompdf->setOptions($options);
// $dompdf->render();
// $dompdf->stream('Dayoff_Nonstaff.pdf', array('Attachment' => 0));
?>