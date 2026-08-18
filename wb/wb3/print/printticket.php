<?php
// exit('Error');
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

try{
	$owlPDO->beginTransaction();
	
	$str="select * from ".$dbname.".wb_parameter where status='1' limit 1";
	$res=fetchdata($str);
	$ktu=$res[0]['ktuname'];
	$lab=$res[0]['labname'];
	$manager=$res[0]['managername'];
	$millcode=$res[0]['millcode'];
	$pt=$res[0]['compname'];

	$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."' and millcode='".$millcode."'";
	$res=fetchdata($str);
	
	if(count($res) <= 0){
		throw new PDOException('Tidak ada transaksi untuk no ticket '.$param['ticketno']);
	}
	
	$inout=$res[0]['inout'];
	$kodebarang=$res[0]['kodebarang'];
	$waktumasuk=$res[0]['waktumasuk'];
	$waktukeluar=$res[0]['waktukeluar'];
	$nokendaraan=$res[0]['nokendaraan'];
	$beratmasuk=$res[0]['beratmasuk'];
	$beratkeluar=$res[0]['beratkeluar'];
	$netto=$res[0]['netto'];
	$potongan=$res[0]['potongan'];
	$potongan=$res[0]['potongan'];
	$supir=$res[0]['supir'];
	$nosim=$res[0]['nosim'];
	$krani=$res[0]['krani'];
	$transportir=$res[0]['transportir'];
	$supplier=$res[0]['supplier'];
	$customer=$res[0]['customer'];
	$ffa=$res[0]['ffa'];
	$moist=$res[0]['moist'];
	$dirt=$res[0]['dirt'];
	$dobi=$res[0]['dobi'];
	$nopo=$res[0]['nopo'];
	$nopo2=$res[0]['kontrakjual2'];
	$segel=$res[0]['segel'];
	$notekirim=$res[0]['notekirim'];
	$printversion=$res[0]['printversion'];
	$opttrs=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$transportir."'");
	$transportir=$opttrs[$transportir];
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
	if($nopo==''){
		$nopo=$res[0]['kontrakjual'];
		$optctr=makeOption($dbname,'pmn_kontrakjual','nokontrak,nokontrakinduk',"nokontrak='".$nopo."'");
		$optctr2=makeOption($dbname,'pmn_kontrakjual','nokontrak,nokontrakinduk',"nokontrak='".$nopo2."'");
		$optsdo=makeOption($dbname,'pmn_kontrakjual','nokontrak,kuantitas',"nokontrak='".$nopo."'");
		$optsdo2=makeOption($dbname,'pmn_kontrakjual','nokontrak,kuantitas',"nokontrak='".$nopo2."'");
		$nokontrak=$optctr[$nopo];
		$nokontrak2=$optctr2[$nopo2];
		$sdo=$optsdo[$nopo];
		$sdo2=$optsdo2[$nopo];
	}
	if($nopo==''){
		$nopo=$res[0]['qr'];			
	}
	
	#######################
	$doc="Doc";
	if($inout=='I' || $inout=='II'){
		$textheader="PENERIMAAN";
		if($inout=='II'){
			$doc="PO";
		}
	}else{
		$textheader="PENGIRIMAN";
		$doc="SO";
	}
	$optnmbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
	$tanggal=substr($waktukeluar,0,10);
	$jammasuk=substr($waktumasuk,11,8);
	$jamkeluar=substr($waktukeluar,11,8);
	$optnmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier."'");
	$supcon=$optnmsupplier[$supplier];
	if($supplier==''){
		$optnmcustomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$customer."'");
		$supcon=$optnmcustomer[$customer];
	}

	$tab.="<style>
		@page {
			margin: 10px 20px 0 20px !important;
		}
	</style>";
	$tab.="<table width=100% cellspacing=0 border='0'>
		<tr>
			<td rowspan=3 style='border-bottom:0.1px solid #000'>
				<img src='images/logo_bsp.jpg' width=50px>
			</td>
			<td style='text-align:center;font-size:20px;'>BUKTI ".$textheader." ".$optnmbarang[$kodebarang]."</td>
		</tr>
		<tr>
			<td style='text-align:right;font-size:10px'>Form : BSPMS-0-FR-02</td>
		</tr>
		<tr>
			<td style='text-align:right;font-size:10px;border-bottom:0.1px solid #000'>REV. : ".addzero($printversion,2)."</td>
		</tr>
	</table>";
	if($kodebarang=='90100001' || $kodebarang=='90100002'){
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:left;font-size:12px;border-right:1px dashed #000;width:35%;padding-top:10px'>".$pt."</td>
				<td style='text-align:left;font-size:12px;border-right:1px dashed #000;width:35%;padding-top:10px;padding-left:10px'>Ticket No : ".$param['ticketno']."</td>
				<td style='text-align:left;font-size:12px;padding-left:10px;padding-top:10px'>Date</td>
				<td style='text-align:center;font-size:12px;padding-top:10px;'>:</td>
				<td style='text-align:right;font-size:12px;padding-top:10px;'>".tglstrip($tanggal)."</td>
			</tr>
			<tr>
				<td style='text-align:left;font-size:10px;border-right:1px dashed #000'>Desa Sungai Aus, Sungai Aur N Sungai Aur, Sungai Aur</td>
				<td style='text-align:left;font-size:12px;border-right:1px dashed #000;padding-left:10px;border-bottom:0.1px solid #000;vertical-align:top;padding-top:10px' rowspan=2>".$doc." No. : ".$nopo."".($nopo2!=''?' , '.$nopo2:'')."</td>
				<td style='text-align:left;font-size:12px;padding-left:5px;padding-left:10px'>Time In</td>
				<td style='text-align:center;font-size:12px;'>:</td>
				<td style='text-align:right;font-size:12px;'>".$jammasuk."</td>
			</tr>
			<tr>
				<td style='text-align:left;font-size:10px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:0.1px solid #000'>Pasaman Barat Sumatera Barat</td>
				<td style='text-align:left;font-size:12px;padding-left:5px;padding-left:10px;padding-bottom:20px;border-bottom:0.1px solid #000'>Time Out</td>
				<td style='text-align:center;font-size:12px;padding-bottom:20px;border-bottom:0.1px solid #000'>:</td>
				<td style='text-align:right;font-size:12px;padding-bottom:20px;border-bottom:0.1px solid #000'>".$jamkeluar."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:17%;font-size:12px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
				<td style='text-align:center;width:23%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
				<td style='text-align:center;width:12%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Tarra</td>
				<td style='text-align:center;width:12%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Bruto</td>
				<td style='text-align:center;width:12%;font-size:12px;padding-top:5px;'>Netto</td>
			</tr>
			<tr>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:0.1px solid #000'>".$nokendaraan."</td>
				<td style='text-align:center;padding-top:0px;font-size:12px;border-right:1px dashed #000;border-bottom:0.1px solid #000;vertical-align:top'>".$optnmbarang[$kodebarang]."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:0.1px solid #000'>".hidezerodecimal($beratmasuk)."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:0.1px solid #000'>".hidezerodecimal($beratkeluar)."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;padding-top:5px;padding-bottom:20px;border-bottom:0.1px solid #000'>".hidezerodecimal($netto)."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:30%;font-size:12px;border-right:1px dashed #000;padding-top:5px'>Supplier / Customer</td>
				<td style='text-align:center;width:32%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Transporter</td>
				<td style='text-align:center;width:32%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Driver</td>
				<td style='text-align:center;width:20%;font-size:12px;padding-top:5px;'>License No</td>
			</tr>
			<tr>
				<td style='text-align:center;padding-top:-5px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:0.1px solid #000'>".$supcon."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:0.1px solid #000'>".$transportir."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:0.1px solid #000'>".$supir."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;padding-top:5px;padding-bottom:20px;border-bottom:0.1px solid #000'>".$nosim."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:left;width:25%;font-size:12px;border-right:1px dashed #000'>
					<table>
						<tr>
							<td>FFA</td>
							<td>:</td>
							<td>".hidezerodecimal($ffa,3)." %</td>
						</tr>
						<tr>
							<td>Moisture</td>
							<td>:</td>
							<td>".hidezerodecimal($moist,3)." %</td>
						</tr>
						<tr>
							<td>Dirty</td>
							<td>:</td>
							<td>".hidezerodecimal($dirt,3)." %</td>
						</tr>
						<tr>
							<td>Dobi</td>
							<td>:</td>
							<td>".hidezerodecimal($dobi,3)." %</td>
						</tr>
					</table>
				</td>
				<td style='text-align:center;width:50%;font-size:12px;border-right:1px dashed #000;'>
					<table>
						<tr>
							<td>Contract No</td>
							<td>:</td>
							<td>".$nokontrak."".($nokontrak2!=''?' , '.$nokontrak2:'')."</td>
						</tr>
						<tr>
							<td>Contract Qty</td>
							<td>:</td>
							<td>".hidezerodecimal($sdo,0)."".($sdo2!=0?' , '.hidezerodecimal($sdo2,0):'')."</td>
						</tr>
						<tr>
							<td>Outstanding Qty</td>
							<td>:</td>
							<td>0</td>
						</tr>
						<tr>
							<td>Remark</td>
							<td>:</td>
							<td>".$notekirim."</td>
						</tr>
					</table>
				</td>
				<td style='text-align:center;width:25%;font-size:12px;vertical-align:top'>
					<table>
						<tr>
							<td>Seal No</td>
							<td>:</td>
						</tr>
						<tr>
							<td colspan=2 style='padding-left:10px'>".$segel."</td>
						</tr>
					</table>
					<table style='vertical-align:bottom'>
						<tr>
							<td>Suhu</td>
							<td>:</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:16%;font-size:12px;border-right:1px dashed #000;padding-top:5px;border-top:0.1px solid #000;border-bottom:0.1px solid #000'>Ditimbang</td>
				<td style='text-align:center;width:16%;font-size:12px;border-right:1px dashed #000;padding-top:5px;border-top:0.1px solid #000;border-bottom:0.1px solid #000'>Diperiksa</td>
				<td style='text-align:center;width:16%;font-size:12px;border-right:1px dashed #000;padding-top:5px;border-top:0.1px solid #000;border-bottom:0.1px solid #000'>Dibuat</td>
				<td style='text-align:center;width:20%;font-size:12px;border-right:1px dashed #000;padding-top:5px;border-top:0.1px solid #000;border-bottom:0.1px solid #000'>Diketahui/Disetujui</td>
				<td style='text-align:center;width:16%;font-size:12px;border-right:1px dashed #000;padding-top:5px;border-top:0.1px solid #000;border-bottom:0.1px solid #000'>Disaksikan</td>
				<td style='text-align:center;width:16%;font-size:12px;padding-top:5px;border-top:0.1px solid #000;border-bottom:0.1px solid #000'>Diterima</td>
			</tr>
			<tr>
				<td style='height:50px;text-align:center;border-right:1px dashed #000;padding-top:5px'>&nbsp;</td>
				<td style='height:50px;text-align:center;border-right:1px dashed #000;padding-top:5px'>&nbsp;</td>
				<td style='height:50px;text-align:center;border-right:1px dashed #000;padding-top:5px'>&nbsp;</td>
				<td style='height:50px;text-align:center;border-right:1px dashed #000;padding-top:5px'>&nbsp;</td>
				<td style='height:50px;text-align:center;border-right:1px dashed #000;padding-top:5px'>&nbsp;</td>
				<td style='text-align:center;padding-top:5px'>&nbsp;</td>
			</tr>
			<tr>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000'>".$krani."</td>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000'>".$ktu."</td>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000'>".$lab."</td>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000'>".$manager."</td>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000'>".$driver."</td>
				<td style='text-align:center;font-size:12px;'></td>
			</tr>
			<tr>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000;border-top:0.1px solid #000;border-bottom:0.1px solid #000''>Kerani Timbang</td>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000;border-top:0.1px solid #000;border-bottom:0.1px solid #000''>Kord Sec PMKS</td>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000;border-top:0.1px solid #000;border-bottom:0.1px solid #000''>Laboratorium</td>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000;border-top:0.1px solid #000;border-bottom:0.1px solid #000''>Mill Manager</td>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000;border-top:0.1px solid #000;border-bottom:0.1px solid #000''>Driver</td>
				<td style='text-align:center;font-size:12px;border-top:0.1px solid #000;border-bottom:0.1px solid #000''>Buyer</td>
			</tr>
		</table>";
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;font-size:10px;'>Setelah keluar dari ".$pt." isi dan muatan bukan menjadi tanggung jawab</td>
			</tr>
			<tr>
				<td style='text-align:center;font-size:10px;'>".$pt."</td>
			</tr>
		</table>";
	}else if($kodebarang=='90100000'){
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:left;font-size:12px;border-right:1px dashed #000;width:35%;padding-top:10px'>".$pt."</td>
				<td style='text-align:left;font-size:12px;border-right:1px dashed #000;width:35%;padding-top:10px;padding-left:10px'>Ticket No : ".$param['ticketno']."</td>
				<td style='text-align:left;font-size:12px;padding-left:10px;padding-top:10px'>Date</td>
				<td style='text-align:center;font-size:12px;padding-top:10px;'>:</td>
				<td style='text-align:right;font-size:12px;padding-top:10px;'>".tglstrip($tanggal)."</td>
			</tr>
			<tr>
				<td style='text-align:left;font-size:10px;border-right:1px dashed #000'>Desa Sungai Aus, Sungai Aur N Sungai Aur, Sungai Aur</td>
				<td style='text-align:left;font-size:12px;border-right:1px dashed #000;padding-left:10px;border-bottom:1px dashed #000;vertical-align:top;padding-top:10px' rowspan=2>Doc Num : ".$nopo."</td>
				<td style='text-align:left;font-size:12px;padding-left:5px;padding-left:10px'>Time In</td>
				<td style='text-align:center;font-size:12px;'>:</td>
				<td style='text-align:right;font-size:12px;'>".$jammasuk."</td>
			</tr>
			<tr>
				<td style='text-align:left;font-size:10px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>Pasaman Barat Sumatera Barat</td>
				<td style='text-align:left;font-size:12px;padding-left:5px;padding-left:10px;padding-bottom:20px;border-bottom:1px dashed #000'>Time Out</td>
				<td style='text-align:center;font-size:12px;padding-bottom:20px;border-bottom:1px dashed #000'>:</td>
				<td style='text-align:right;font-size:12px;padding-bottom:20px;border-bottom:1px dashed #000'>".$jamkeluar."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:17%;font-size:12px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
				<td style='text-align:center;width:23%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
				<td style='text-align:center;width:12%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>1st Weight</td>
				<td style='text-align:center;width:12%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>2nd Weight</td>
				<td style='text-align:center;width:12%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Gross</td>
				<td style='text-align:center;width:12%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Sorting</td>
				<td style='text-align:center;width:12%;font-size:12px;padding-top:5px;'>Nett</td>
			</tr>
			<tr>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$nokendaraan."</td>
				<td style='text-align:center;padding-top:0px;font-size:12px;border-right:1px dashed #000;border-bottom:1px dashed #000;vertical-align:top'>".$optnmbarang[$kodebarang]."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratmasuk)."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratkeluar)."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto+$potongan)."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($potongan)."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto)."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:30%;font-size:12px;border-right:1px dashed #000;padding-top:5px'>Supplier / Customer</td>
				<td style='text-align:center;width:18%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Supplier Weight</td>
				<td style='text-align:center;width:32%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Transporter</td>
				<td style='text-align:center;width:20%;font-size:12px;padding-top:5px;'>Driver</td>
			</tr>
			<tr>
				<td style='text-align:center;padding-top:-5px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$supcon."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>0</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$transportir."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".$supir."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100%>
			<tr>
				<td height=60px>&nbsp;</td>
			</tr>
		</table>";
		
		// $tab.="<table width=100% cellspacing=0>
			// <tr>
				// <td style='text-align:center;width:25%;font-size:12px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
				// <td style='text-align:center;width:25%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
				// <td style='text-align:center;width:25%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>1st Weight</td>
				// <td style='text-align:center;width:25%;font-size:12px;padding-top:5px;'>Nett</td>
			// </tr>
			// <tr>
				// <td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$nokendaraan."</td>
				// <td style='text-align:center;padding-top:0px;font-size:12px;border-right:1px dashed #000;border-bottom:1px dashed #000;vertical-align:top'>".$optnmbarang[$kodebarang]."</td>
				// <td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratmasuk)."</td>
				// <td style='text-align:center;padding-top:10px;font-size:12px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto)."</td>
			// </tr>
		// </table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:50%;font-size:12px;border-right:1px dashed #000;padding-top:5px;border-top:1px dashed #000'>Diterima</td>
				<td style='text-align:center;width:50%;font-size:12px;padding-top:5px;border-top:1px dashed #000'>Disetujui / Disaksikan</td>
			</tr>
			<tr>
				<td style='height:50px;text-align:center;border-right:1px dashed #000;padding-top:5px'>&nbsp;</td>
				<td style='text-align:center;padding-top:5px'>&nbsp;</td>
			</tr>
			<tr>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000'><u>&nbsp;&nbsp;".$krani."&nbsp;&nbsp;</u></td>
				<td style='text-align:center;font-size:12px;'><u>&nbsp;&nbsp;".$supir."&nbsp;&nbsp;</u></td>
			</tr>
			<tr>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000'>Krani Timbang</td>
				<td style='text-align:center;font-size:12px;'>Driver</td>
			</tr>
		</table>";
	}else{
		
	}
	
	$tab.="<table width=100% cellspacing=0>
		<tr>
			<td style='text-align:left;width:50%;font-size:12px;padding-top:20px'>Print By ".$krani." ".date('d/m/Y H:i:s')."</td>
			<td style='text-align:right;width:50%;font-size:12px;padding-top:20px'>".($printversion==0?'ORIGINAL PRINT':'COPY PRINT '.$printversion)."</td>
		</tr>
	</table>";
	
	$str="update ".$dbname.".wb set printversion=printversion+1 where notransaksi='".$param['ticketno']."'";
	$owlPDO->exec($str);
	
	$dompdf = new Dompdf();
	$dompdf->loadHtml($tab);
	$dompdf->setPaper('A5', 'landscape');
	$dompdf->render();
	$dompdf->stream("Purchase Request", array("Attachment" => false));
	
	$owlPDO->commit();
}catch (PDOException $e) {$owlPDO->rollback(); echo "Gagal, " . addslashes($e->getMessage()); die();}
?>
