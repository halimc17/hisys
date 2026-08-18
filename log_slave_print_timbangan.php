<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
use Mpdf\Tag\P;

require_once('lib/terbilang.php');

$method   = checkPostGet('method', '');
$print2   = checkPostGet('print2', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

try{
	$owlPDO->beginTransaction();

	$str="select * from ".$dbname.".pabrik_timbangan where notransaksi='".$param['column']."'";
	$res=fetchdata($str);

	
	$unit=$res[0]['kodeorg'];
	$nospb=$res[0]['nospb'];
	$divisi=$res[0]['divcode'];
	$kodebarang=$res[0]['kodebarang'];
	$kodebarang2=$res[0]['kodebarang'];
	$waktumasuk=$res[0]['jammasuk'];
	$waktukeluar=$res[0]['jamkeluar'];
	$nokendaraan=$res[0]['nokendaraan'];
	$beratmasuk=$res[0]['beratmasuk'];
	$beratkeluar=$res[0]['beratkeluar'];
	$netto=$res[0]['beratbersih'];
	$tahuntanam=$res[0]['tahuntanam'];
	$supir=$res[0]['supir'];
	$krani=$res[0]['username'];
	$keterangan=$res[0]['keterangan'];
	$transportir=$res[0]['trpcode'];
	$supplier=$res[0]['kodesupplier'];
	$customer=$res[0]['kodecustomer'];
	$nopo=$res[0]['nodo'];
	$noso=$res[0]['nodo'];
	$nopo2=$res[0]['kontrakjual2'];
	$segel=$res[0]['nosegel'];
	$jjg=$res[0]['jumlahtandan1'];
	$adjjjg=$res[0]['adjjjg'];
	$adjbrondol=$res[0]['adjbrondol'];
	$brondolan=$res[0]['brondolan'];
	$storage=$res[0]['storage'];
	$sumber=$res[0]['sumber'];
	$tanggal=substr($res[0]['tanggal'],0,10);
	// $opttrs=makeOption($dbname,'msvendor','vendorcode,vendorname',"vendorcode='".$transportir."'");
	$optnamacus=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
	$optnmbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
	$transportir=@$opttrs[$transportir];
	$nokontrak="";
	$nokontrak2="";
	$sdo=0;
	$sdo2=0;
	if($nopo==''){
		$nopo=$res[0]['kontrakbeli'];			
	}
	if($nopo==''){
		$nopo=$res[0]['kontrakbeli2'];			
	}


	// ambil data panen
	
	$str_datapanen="select * from ".$dbname.".pabrik_wb_datapanen where notiket = '".$param['ticketno']."' ";
	$res_datapanen=fetchdata($str_datapanen);


	$tab="";
	$tab.="<style>
		@page {
			margin: 20px 20px 0 20px !important;
			font-family: sans-serif, Helvetica, sans-serif;
			// font-family: Lucida Console, Courier New, monospace;
			// font-family: sans-serif;
			font-size:15px;
		}
	</style>";
	if($divisi != ''){
		$tab.="<table width=100% cellspacing=0 cellpadding=5 border=0 style='font-family:sans-serif' >
		<tr>
			<td style='width:50%;font-weight:bold;transform:translateX(20px);'>
				Perusahaan Perkebunan <br>
				PT. PALMA PRIMA PLANTATION	
			</td>
			<td style='width:50%;font-weight:bold;transform:translateX(100px);'>
				Kepada Yth <br> ".$optnamacus[$customer]."
			</td>
		</tr>
		<tr>
			<td colspan='2' style='width:100%;text-align:center;padding-top:20px;transform:translateX(20px);'>
				 <span style='font-size:26px;font-weight:bold;text-decoration: underline'>
					SURAT PENGANTAR BUAH 
				</span> 
				<br>
				 <span style='font-weight:bold'>
				 PT. PALMA PRIMA PLANTATION	
				</span> 
				<br>
				 <span>
				 NO : ".$nospb."
				</span> 
				<br>
			</td>
		</tr>
		<tr>
			<td colspan='2' style='width:50%;transform:translateX(20px);'>
				Tanggal : ".$tanggal."
			</td>
			<td colspan='2' style='width:50%;transform:translateX(-120px);'>
				No Segel : ".$segel."
			</td>
		</tr>
	</table>
	
	<table width=65% cellspacing=0 cellpadding=5 border=1 style='font-family:sans-serif;transform:translateX(27px);'>
		<thead>
			<tr>
				<th style='text-align:center'>Nopol</th>
				<th style='text-align:center'>Berat Dikirim</th>
				<th style='text-align:center'>Tahun Tanam</th>
				<th style='text-align:center'>Jumlah Janjang</th>
				<th style='text-align:center'>Berat Brondolan</th>
				<th style='text-align:center'>Keterangan</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style='height:100px;text-align:center'>".$nokendaraan."</td>
				<td style='height:100px;text-align:center'>".hidezerodecimal($netto)."</td>
				<td style='height:100px;text-align:center'>".$tahuntanam."</td>
				<td style='height:100px;text-align:center'>".hidezerodecimal($jjg+$adjjjg)."</td>
				<td style='height:100px;text-align:center'>".hidezerodecimal($brondolan+$adjbrondol)."</td>
				<td style='height:100px;text-align:center'>".$keterangan."</td>
			</tr>
		</tbody>
	</table>
	<br>
	<table width=65% cellspacing=0 cellpadding=5 border=0 style='font-family:sans-serif' >
		<tr>
			<td style='width:33%;text-align:center;font-weight:bold'>
				Pengirim
			</td>
			<td style='width:33%;text-align:center;font-weight:bold'>
				Supir
			</td>
			<td style='width:33%;text-align:center;font-weight:bold'>
				Penerima
			</td>
		</tr>
		<tr>
			<td style='width:33%;text-align:center;height:50px;'>
				
			</td>
			<td style='width:33%;text-align:center;height:50px;'>
				
			</td>
			<td style='width:33%;text-align:center;height:50px;'>
				
			</td>
		</tr>
		<tr>
			<td style='width:33%;text-align:center;'>
				( ".$krani." )
			</td>
			<td style='width:33%;text-align:center;'>
				( ".$supir." )
			</td>
			<td style='width:33%;text-align:center;'>
				( .................... )
			</td>
		</tr>
	</table>";	
	}else{
		$tab.="<table width=100% cellspacing=0 cellpadding=5 border=0 style='font-family:sans-serif' >
		<tr>
			<td colspan='2' style='width:100%;text-align:center;padding-top:20px;transform:translateX(-250px);'>
				 <span style='font-size:26px;font-weight:bold'>
					PT.PALMA PRIMA PLANTATION
				</span> 
				<br>
				 <span style='font-weight:bold'>
					DESA SIAYUH KEC.KELUMPANG BARAT
				</span> 
				<br>
				 <span>
					 KAB KOTABARU KALSEL
				</span> 
				<br>
					<span>
						Telp : - , Fax : -
					</span> 
				<br>
			</td>
		</tr>
	
	</table>
	
	<table width=47% border=0 cellspacing=0 cellpadding=2 style='font-family:sans-serif;transform:translateX(27px);'>
		<tbody>
		<tr>
			<td colspan=3><hr></td>
		</tr>
			<tr>
				<td style='width:150px;text-align:center;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black'>ORIGINAL</td>
				<td style='width:10px'></td>
				<td style='text-align:center'>No. Tiket : ".$param['column']."</td>
			</tr>
			<tr>
				<td style='width:150px'>Supplier</td>
				<td style='width:10px'>:</td>
				<td>".$optnmsupplier[$supplier]."</td>
			</tr>
			<tr>
				<td style='width:150px'>Barang</td>
				<td style='width:10px'>:</td>
				<td>".$optnmbarang[$kodebarang]."</td>
			</tr>
			<tr>
				<td style='width:150px'>NO. Polisi</td>
				<td style='width:10px'>:</td>
				<td>".$nokendaraan."</td>
			</tr>
			<tr>
				<td style='width:150px'>NO. PO/DO</td>
				<td style='width:10px'>:</td>
				<td>".$nopo."</td>
			</tr>
			<tr>
				<td style='width:150px'>Waktu In</td>
				<td style='width:10px'>:</td>
				<td>".$waktumasuk."</td>
			</tr>
			<tr>
				<td style='width:150px'>Waktu Out</td>
				<td style='width:10px'>:</td>
				<td>".$waktukeluar."</td>
			</tr>
			<tr>
				<td style='width:150px'>Gross</td>
				<td style='width:10px'>:</td>
				<td>".$beratmasuk." Kg</td>
			</tr>
			<tr>
				<td style='width:150px'>Tara</td>
				<td style='width:10px'>:</td>
				<td>".$beratkeluar." Kg</td>
			</tr>
			<tr>
				<td style='width:150px'>Netto</td>
				<td style='width:10px'>:</td>
				<td>".$netto." Kg</td>
			</tr>
		</tbody>
	</table>
	<br>
	<table width=47% cellspacing=0 cellpadding=5 border=0 style='font-family:sans-serif;transform:translateX(27px);' >
		<tr>
			<td style='width:33%;text-align:center;font-weight:bold'>
				Supir
			</td>
			<td style='width:33%;text-align:center;font-weight:bold'>
				Operator
			</td>
		</tr>
		<tr>
			<td style='width:33%;text-align:center;height:30px;'>
				
			</td>
			<td style='width:33%;text-align:center;height:30px;'>
				
			</td>
		</tr>
		<tr>
			<td style='width:33%;text-align:center;'>
				( ".$supir." )
			</td>
			<td style='width:33%;text-align:center;'>
				( ".$krani." )
			</td>
		</tr>
		<tr>
			<td colspan=2><hr></td>
		</tr>
		<tr style='transform:translateY(-10px);'>
			<td>Keterangan :</td>
			<td style='transform:translateX(-160px);'>".$keterangan."</td>
		</tr>
	</table>";

	}
		
	$dompdf = new Dompdf($options);
	$dompdf->loadHtml($tab);
	if ($sumber=='PABRIK') {
	
		if($print2 != ''){
			$dompdf->setPaper('A4', 'potrait');
		}else{
			// $dompdf->setPaper('A4', 'landscape');
			$dompdf->setPaper('A3', 'potrait');
		}
	}
	else
	{
		// $dompdf->setPaper('A6', 'potrait');
		if($print2 != ''){
			$dompdf->setPaper('A3', 'potrait');
		}else{
			// $dompdf->setPaper('A3', 'landscape');
			$dompdf->setPaper('A3', 'potrait');
		}
	}
	
	$dompdf->render();
	$dompdf->stream("Tiket Timbang", array("Attachment" => false));
	
	$owlPDO->commit();
}catch (PDOException $e) {$owlPDO->rollback(); echo "Gagal, " . addslashes($e->getMessage()); die();}
?>
