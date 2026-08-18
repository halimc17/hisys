<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
require_once('lib/terbilang.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

try{
	$owlPDO->beginTransaction();
	
	$str="select * from ".$dbname.".mssystem limit 1";
	$res=fetchdata($str);
	$ktu=$res[0]['ktuname'];
	$lab=$res[0]['labname'];
	$alamat1=$res[0]['alamat1'];
	$alamat2=$res[0]['alamat2'];
	$manager=$res[0]['managername'];
	$millcode=$res[0]['millcode'];
	$millname=$res[0]['millname'];
	$pt=$res[0]['compname'];

	$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."' and millcode='".$millcode."' and netto > '0'";
	$res=fetchdata($str);
	
	if(count($res) <= 0){
		throw new PDOException('Tidak ada transaksi untuk no ticket '.$param['ticketno']);
	}
	
	$inout=$res[0]['in_out'];
	$tipeunit=$res[0]['tipeunit'];
	$unit=$res[0]['unitcode'];
	$nospb=$res[0]['qr'];
	$divisi=$res[0]['divcode'];
	$kodebarang=$res[0]['kodebarang'];
	$kodebarang2=$res[0]['kodebarang'];
	$waktumasuk=$res[0]['waktumasuk'];
	$waktukeluar=$res[0]['waktukeluar'];
	$nokendaraan=$res[0]['nokendaraan'];
	$beratmasuk=$res[0]['beratmasuk'];
	$beratkeluar=$res[0]['beratkeluar'];
	$netto=$res[0]['netto'];
	$nettosplit=$res[0]['nettosplit'];
	$nettosplit2=$res[0]['nettosplit2'];
	$potongan=$res[0]['potongan'];
	$potonganwajib=$res[0]['potonganwajib'];
	$supir=$res[0]['supir'];
	$kernet1=$res[0]['kernet1'];
	$kernet2=$res[0]['kernet2'];
	$nosim=$res[0]['nosim'];
	$krani=$res[0]['krani'];
	$keterangan=$res[0]['keterangan'];
	$transportir=$res[0]['transportir'];
	$supplier=$res[0]['supplier'];
	$customer=$res[0]['customer'];
	$ffa=$res[0]['ffa'];
	$moist=$res[0]['moist'];
	$dirt=$res[0]['dirt'];
	$dobi=$res[0]['dobi'];
	$nopo=$res[0]['nopo'];
	$noso=$res[0]['kontrakjual'];
	$nopo2=$res[0]['kontrakjual2'];
	$segel=$res[0]['segel'];
	$notekirim=$res[0]['notekirim'];
	$printversion=$res[0]['printversion'];
	$jjg=$res[0]['janjang'];
	$brondolan=$res[0]['brondolan'];
	$storage=$res[0]['storage'];
	$sumber=$res[0]['sumber'];
	$opttrs=makeOption($dbname,'msvendor','vendorcode,vendorname',"vendorcode='".$transportir."'");
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



	if($nopo==''){
		$nopo=$res[0]['kontrakjual'];
		$optctr=makeOption($dbname,'msso','noso,nosoinduk',"noso='".$nopo."'");
		$optctr2=makeOption($dbname,'msso','noso,nosoinduk',"noso='".$nopo2."'");
		$optsdo=makeOption($dbname,'msso','noso,soqty',"noso='".$nopo."'");
		$optsdo2=makeOption($dbname,'msso','noso,soqty',"noso='".$nopo2."'");
		$nokontrak=@$optctr[$nopo];
		$nokontrak2=@$optctr2[$nopo2];
		$sdo=@$optsdo[$nopo];
		$sdo2=@$optsdo2[$nopo2];
	}
	if($nopo==''){
		$nopo=$res[0]['qr'];			
	}
	
	$optsisaso=makeOption($dbname,'msso','noso,sisaso',"noso='".$nopo."'");
	#######################
	$doc="Doc";
	if($inout=='I' || $inout=='II'){
		$textheader="PENERIMAAN";
		if($inout=='II'){
			$doc="PO";
		}
	}else{
		$textheader="PENGIRIMAN";
		$doc="PDO";
	}
	$optnmbarang=makeOption($dbname,'msproduk','kodeproduk,namaproduk',"kodeproduk='".$kodebarang."'");
	$tanggal=substr($waktukeluar,0,10);
	$jammasuk=substr($waktumasuk,11,8);
	$jamkeluar=substr($waktukeluar,11,8);
	$optnmsupplier=makeOption($dbname,'msvendor','vendorcode,vendorname',"vendorcode='".$supplier."'");
	$supcon=@$optnmsupplier[$supplier];
	if($supplier==''){
		$optnmcustomer=makeOption($dbname,'mscustomer','custcode,custname',"custcode='".$customer."'");
		$supcon=@$optnmcustomer[$customer];
	}

	if ($tipeunit=='EKSTERNAL') {
		$potwajibdisplay='';
	}else{
		$potwajibdisplay='display:none';
	}

	if ($kodebarang != $kodeproduktbs) {
		if ($kodebarang != $kodeprodukcpo) {
			if ($kodebarang != $kodeprodukpk) {
				$kodebarang='produklain';
			}
		}
	}

	$nmdivisi=makeOption($dbname,'msdivisi','DIVCODE,DIVNAME');


	$tab="";
	$tab.="<style>
		@page {
			margin: 20px 20px 0 20px !important;
			// font-family: Arial, Helvetica, sans-serif;
			font-family: Lucida Console, Courier New, monospace;
			font-size:15px;
		}
	</style>";

	
	switch($inout){
		case'I':
			switch($kodebarang){
				case $kodeproduktbs:
					if ($tipeunit=='INTERNAL') {
						if ($sumber=='PABRIK') {
							$tab.="<table width=100% cellspacing=0 border=0>
							<tr>
							<td style='text-align:left;font-size:18px;'><b>".$pt."</b></td>
							</tr>
							<tr>
							<td><br></td>
							</tr>
							</table>";

							$tab.="<table width=100% cellspacing=0 border=0>
							<tr>
							<td width=65% valign=top>
							<table border=0 cellspacing=0 cellpadding=2>
							<tr>
							<td style='border-left:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;'>Nama Barang</td>
							<td style='border-top:1px solid #000;border-right:1px solid #000;'>No Ticket</td>
							<td style='border-top:1px solid #000;border-right:1px solid #000;'>Tanggal dan Jam</td>
							</tr>
							<tr>
							<td style='border-left:1px solid #000;border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;'>TBS</td>
							<td style='border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;'>".$param['ticketno']."</td>
							<td style='border-top:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;'>".waktunormal($waktumasuk)."<br>".waktunormal($waktukeluar)."</td>
							</tr>

							<tr>
							<td>No SPB</td>
							<td colspan=2>: ".$nospb."</td>
							</tr>
							<tr>
							<td>Pelanggan</td>
							<td colspan=2>: ".$unit."</td>
							</tr>
							<tr>
							<td>Nama Supir</td>
							<td colspan=2>: ".$supir."</td>
							</tr>
							<tr>
							<td>Nama Kernet 1</td>
							<td colspan=2>: ".$kernet1."</td>
							</tr>
							<tr>
							<td>Nama Kernet 2</td>
							<td colspan=2>: ".$kernet2."</td>
							</tr>
							<tr>
							<td>Catatan</td>
							<td colspan=2>: ".$keterangan."</td>
							</tr>
							</table>
							</td>
							<td valign=top>
							<table width=100% border=1 cellspacing=0 cellpadding=2>
							<tr>
							<td>No. Polisi</td>
							<td align=center>".$nokendaraan."</td>
							</tr>
							<tr>
							<td>Bruto</td>
							<td align=right>".number_format($beratmasuk)." Kg</td>
							</tr>
							<tr>
							<td>Tara</td>
							<td align=right>".number_format($beratkeluar)." Kg</td>
							</tr>
							<tr>
							<td>Netto</td>
							<td align=right>".number_format(($netto+$potongan))." Kg</td>
							</tr>
							<tr>
							<td>Potongan</td>
							<td align=right>".number_format($potongan)." Kg</td>
							</tr>
							<tr>
							<td>Berat Bersih</td>
							<td align=right>".number_format($netto)." Kg</td>
							</tr>
							</table>

							</td>
							</tr>
							</table>";
							$tab.="&nbsp;";
							$tab.="<table width=100% cellspacing=0 border=0>
							<tr>
							<td></td>
							<td width=20%></td>
							<td align=right>Bukit Mas, ".substr(waktunormal($waktukeluar),0,10)."</td>
							</tr>
							<tr>
							<td align=center>Krani Timbang</td>
							<td></td>
							<td align=center>Diketahui</td>
							</tr>
							<tr>
							<td colspan=3 height=70px></td>
							</tr>
							<tr>
							<td align=center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
							<td></td>
							<td align=center>Asisten</td>
							</tr>
							</table>";
						}
						else
						{
							$tab.="<table width=100% cellspacing=0 cellpadding=5 border=0>

							<tr >
							<td width=10%></td>
							<td width=50%>No Tiket</td>
							<td width=1%>:</td>
							<td align=left width=80%>".$param['ticketno']."</td>
							
							</tr>
							<tr>
							<td width=10%></td>
							<td >Tgl Jam Masuk</td>
							<td >:</td>
							<td >".$waktumasuk."</td>
							
							</tr>
							<tr>
							<td width=10%></td>
							<td >Tgl Jam Keluar</td>
							<td >:</td>
							<td >".$waktukeluar."</td>
							
							</tr>
							<tr>
							<td width=10%></td>
							<td >No Kendaraan</td>
							<td >:</td>
							<td >".$nokendaraan."</td>
							
							</tr>
							<tr>
							<td width=10%></td>
							<td >Nama Customer</td>
							<td >:</td>
							<td >".$transportir."</td>
							
							</tr>
							<tr>
							<td width=10%></td>
							<td >Nama Barang</td>
							<td >:</td>
							<td >".$optnmbarang[$kodebarang]."</td>
							
							</tr>
							<tr>
							<td width=10%></td>
							<td >Nama Supir</td>
							<td >:</td>
							<td >".$supir."</td>
							
							</tr>
							<tr>
							<td width=10%></td>
							<td >No Refersi</td>
							<td >:</td>
							
							</tr>
							<tr>
							<td ></td>
							
							</tr>
							<tr>
							<td width=10%></td>
							<td ><b>Berat Bruto</b></td>
							<td >:</td>
							<td><b>".hidezerodecimal($beratmasuk)."</b></td>
							
							</tr>
							<tr>
							<td width=10%></td>
							<td ><b>Berat Tarra</b></td>
							<td >:</td>
							<td><b>".hidezerodecimal($beratkeluar)."</b></td>
							
							</tr>
							<tr>
							<td width=10%></td>
							<td ><b>Berat Netto</b></td>
							<td >:</td>
							<td><b>".hidezerodecimal($netto+$potongan)."</b></td>
							
							</tr>


							</table><br>";


							$tab.="<table width=100% cellspacing=0>
							<tr align=center>
							<td ></td>
							<td >Di Timbang</td>
							<td >Di Ketahui</td>
							</tr>
							<tr>
							<td >&nbsp;</td>
							<td >&nbsp;</td>
							<td >&nbsp;</td>
							</tr>
							<tr>
							<td >&nbsp;</td>
							<td >&nbsp;</td>
							<td >&nbsp;</td>
							</tr>


							<tr align=center>
							<td >&nbsp;</td>
							<td ><u>&nbsp;&nbsp;".strtoupper($krani)."&nbsp;&nbsp;</u></td>
							<td ><u>&nbsp;&nbsp;&nbsp;&nbsp;</u></td>
							</tr>

							</table>";
						}
						
					}else{
						##EKSTERNAL
						$tab.="<table width=100% cellspacing=0 border=0>
							<tr>
								<td style='text-align:center;font-size:20px;'><b>".$pt."</b></td>
							</tr>
							<tr>
								<td style='text-align:center;font-size:14px;'><b>SLIP TIMBANGAN - PENERIMAAN TBS</b></td>
							</tr>

						</table>";

						$tab.="<table border=0>
							<tr>
								<td>NO SLIP TIMBANGAN</td>
								<td>:</td>
								<td>".$param['ticketno']."</td>
							</tr>
							<tr>
								<td>HARI, TANGGAL</td>
								<td>:</td>
								<td>".hari($waktukeluar).", ".substr(waktunormal($waktukeluar),0,10)."</td>
							</tr>
						</table>";

						$tab.="<table width=100% cellspacing=0 border=1>
							<tr>
								<td align=center><b>DATA SUPPLIER</b></td>
								<td align=center><b>DATA TIMBANGAN</b></td>
							</tr>
							<tr>
								<td width=50% valign=top>
									<table>
										<tr>
											<td>NO.SP</td>
											<td>:</td>
											<td>".$nospb."</td>
										</tr>
										<tr>
											<td>KODE SUPPLIER</td>
											<td>:</td>
											<td>".$supplier."</td>
										</tr>
										<tr>
											<td>NAMA SUPPLIER</td>
											<td>:</td>
											<td>".$optnmsupplier[$supplier]."</td>
										</tr>
										<tr>
											<td>JENIS BARANG</td>
											<td>:</td>
											<td>TBS</td>
										</tr>
										<tr>
											<td>NO. KONTRAK</td>
											<td>:</td>
											<td>".$nopo."</td>
										</tr>
										<tr>
											<td>NO. POL KEND</td>
											<td>:</td>
											<td>".$nokendaraan."</td>
										</tr>
										<tr>
											<td>JAM MASUK</td>
											<td>:</td>
											<td>".substr($waktumasuk, 11,10)."</td>
										</tr>
										<tr>
											<td>JAM KELUAR</td>
											<td>:</td>
											<td>".substr($waktukeluar, 11,10)."</td>
										</tr>
										<tr>
											<td colspan=2></br></td>
										</tr>
										<tr>
											<td>Ket :</td>
											<td colspan=2>".$keterangan."</td>
										</tr>
									</table>
								</td>
								<td valign=top>
									<table width=100% border=0>
										<tr>
											<td>BRUTO</td>
											<td align=center>:</td>
											<td align=right colspan=2>".number_format($beratmasuk)." Kg</td>
										</tr>
										<tr>
											<td>TARRA</td>
											<td align=center>:</td>
											<td align=right colspan=2>".number_format($beratkeluar)." Kg</td>
										</tr>
										<tr>
											<td colspan=4><hr></td>
										</tr>
										<tr>
											<td>NETTO</td>
											<td align=center>:</td>
											<td align=right colspan=2>".number_format(($netto+$potongan))." Kg</td>
										</tr>
										<tr>
											<td>POTONGAN WAJIB</td>
											<td align=center>:</td>
											<td align=right colspan=2>".number_format($potonganwajib)." Kg</td>
										</tr>";

										$str="select * from ".$dbname.".mssortasi where status='1'";
										$res=fetchdata($str);
										foreach ($res as $val) {
											$arrmastersort[$val['kode']]=$val['deskripsi'];
											$arrsortasi[$val['kode']][$val['persen']]=$val['persen'];
											$arrsortasi[$val['kode']][$val['kg']]=$val['kg'];
										}

										$str="select * from ".$dbname.".trxsortasi where notransaksi='".$param['ticketno']."'";
										$res=fetchdata($str);
										foreach ($res as $val) {
											$valsortasi[$val['kode']][$val['field']]=$val['value'];
										}

										foreach ($arrsortasi as $kode => $arrfield) {
											$tab.="
												<tr>
													<td>".$arrmastersort[$kode]."</td>
													<td align=center>:</td>";
													foreach ($arrfield as $field) {
														if (substr($field,-1)=='P') {
															$tab.="<td align=right>".number_format($valsortasi[$kode][$field])." %</td>";
														}else{
															$tab.="<td align=right>".number_format($valsortasi[$kode][$field])." Kg</td>";
														}
													}
													$tab.="
												</tr>
											";
										}

										$tab.="
										<tr>
											<td colspan=4><hr></td>
										</tr>
										<tr>
											<td>Total Potongan</td>
											<td>:</td>
											<td align=right colspan=2>".number_format($potongan)." Kg</td>
										</tr>
										<tr>
											<td>NETTO</td>
											<td>:</td>
											<td align=right colspan=2>".number_format($netto)." Kg</td>
										</tr>
										<tr>
											<td>Bjr</td>
											<td>:</td>
											<td align=right colspan=2>".number_format($netto/$jjg,2)." Kg</td>
										</tr>
										<tr>
											<td>Jumlah Tandan</td>
											<td>:</td>
											<td align=right colspan=2>".$jjg." Kg</td>
										</tr>
										";

									$tab.="</table>
								</td>
							</tr>
						</table>";
						$tab.="&nbsp;";
						$tab.="<table width=100% cellspacing=0 border=0>
							<tr>
								<td align=center>Ditimbang Oleh, <br> Adm Timbangan PKS</td>
								
								<td align=center>Diperiksa Oleh, <br> Asisten</td>
							</tr>
							<tr>
								<td colspan=2 height=40px></td>
							</tr>
							<tr>
								<td align=center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
								
								<td align=center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
							</tr>
						</table>";

					}
				break;
				
				default:
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:left;font-size:14px;border-right:1px dashed #000;width:38%;padding-top:10px'>".$pt."</td>
							<td style='text-align:left;font-size:14px;border-right:1px dashed #000;width:32%;padding-top:10px;padding-left:10px'>Ticket No : ".$param['ticketno']."</td>
							<td style='text-align:left;font-size:14px;padding-left:10px;padding-top:10px'>Date</td>
							<td style='text-align:center;font-size:14px;padding-top:10px;'>:</td>
							<td style='text-align:right;font-size:14px;padding-top:10px;'>".tglstrip($tanggal)."</td>
						</tr>
						<tr>
							<td style='text-align:left;font-size:11px;border-right:1px dashed #000'>".$alamat1."</td>
							<td style='text-align:left;font-size:14px;border-right:1px dashed #000;padding-left:10px;border-bottom:1px dashed #000;vertical-align:top;padding-top:10px' rowspan=2>Doc Num : ".$nopo."</td>
							<td style='text-align:left;font-size:14px;padding-left:5px;padding-left:10px'>Time In</td>
							<td style='text-align:center;font-size:14px;'>:</td>
							<td style='text-align:right;font-size:14px;'>".$jammasuk."</td>
						</tr>
						<tr>
							<td style='text-align:left;font-size:11px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$alamat2."</td>
							<td style='text-align:left;font-size:14px;padding-left:5px;padding-left:10px;padding-bottom:20px;border-bottom:1px dashed #000'>Time Out</td>
							<td style='text-align:center;font-size:14px;padding-bottom:20px;border-bottom:1px dashed #000'>:</td>
							<td style='text-align:right;font-size:14px;padding-bottom:20px;border-bottom:1px dashed #000'>".$jamkeluar."</td>
						</tr>
					</table>";
					
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:center;width:17%;font-size:14px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
							<td style='text-align:center;width:23%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
							<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>1st Weight</td>
							<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>2nd Weight</td>
							<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Gross</td>
							<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Sorting</td>
							<td style='text-align:center;width:12%;font-size:14px;padding-top:5px;'>Nett</td>
						</tr>
						<tr>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$nokendaraan."</td>
							<td style='text-align:center;padding-top:0px;font-size:14px;border-right:1px dashed #000;border-bottom:1px dashed #000;vertical-align:top'>".$optnmbarang[$kodebarang2]."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratmasuk)."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratkeluar)."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto+$potongan)."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($potongan)."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto)."</td>
						</tr>
					</table>";
					
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:center;width:30%;font-size:14px;border-right:1px dashed #000;padding-top:5px'>Supplier / Customer</td>
							<td style='text-align:center;width:18%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Supplier Weight</td>
							<td style='text-align:center;width:32%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Transporter</td>
							<td style='text-align:center;width:20%;font-size:14px;padding-top:5px;'>Driver</td>
						</tr>
						<tr>
							<td style='text-align:center;padding-top:-5px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$supcon."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>0</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$transportir."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".$supir."</td>
						</tr>
					</table>";
					
					$tab.="<table width=100%>
						<tr>
							<td height=60px>&nbsp;</td>
						</tr>
					</table>";
					
					// $tab.="<table width=100% cellspacing=0>
						// <tr>
							// <td style='text-align:center;width:25%;font-size:14px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
							// <td style='text-align:center;width:25%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
							// <td style='text-align:center;width:25%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>1st Weight</td>
							// <td style='text-align:center;width:25%;font-size:14px;padding-top:5px;'>Nett</td>
						// </tr>
						// <tr>
							// <td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$nokendaraan."</td>
							// <td style='text-align:center;padding-top:0px;font-size:14px;border-right:1px dashed #000;border-bottom:1px dashed #000;vertical-align:top'>".$optnmbarang[$kodebarang]."</td>
							// <td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratmasuk)."</td>
							// <td style='text-align:center;padding-top:10px;font-size:14px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto)."</td>
						// </tr>
					// </table>";
					
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:center;width:50%;font-size:14px;border-right:1px dashed #000;padding-top:5px;border-top:1px dashed #000'>Diterima</td>
							<td style='text-align:center;width:50%;font-size:14px;padding-top:5px;border-top:1px dashed #000'>Disetujui / Disaksikan</td>
						</tr>
						<tr>
							<td style='height:20px;text-align:center;border-right:1px dashed #000;padding-top:5px'>&nbsp;</td>
							<td style='text-align:center;padding-top:5px'>&nbsp;</td>
						</tr>
						<tr>
							<td style='text-align:center;font-size:14px;border-right:1px dashed #000'><u>&nbsp;&nbsp;".strtoupper($krani)."&nbsp;&nbsp;</u></td>
							<td style='text-align:center;font-size:14px;'><u>&nbsp;&nbsp;".$supir."&nbsp;&nbsp;</u></td>
						</tr>
						<tr>
							<td style='text-align:center;font-size:14px;border-right:1px dashed #000'>Krani Timbang</td>
							<td style='text-align:center;font-size:14px;'>Driver</td>
						</tr>
					</table>";
				break;
			}
		break;
		case'O':
			switch($kodebarang){
				case $kodeproduktbs:
				if ($sumber=='PABRIK') {
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:left;font-size:14px;border-right:1px dashed #000;width:38%;padding-top:10px'>".$pt."</td>
							<td style='text-align:left;font-size:14px;border-right:1px dashed #000;width:32%;padding-top:10px;padding-left:10px'>Ticket No : ".$param['ticketno']."</td>
							<td style='text-align:left;font-size:14px;padding-left:10px;padding-top:10px'>Date ".$kodebarang."</td>
							<td style='text-align:center;font-size:14px;padding-top:10px;'>:</td>
							<td style='text-align:right;font-size:14px;padding-top:10px;'>".tglstrip($tanggal)."</td>
						</tr>
						<tr>
							<td style='text-align:left;font-size:11px;border-right:1px dashed #000'>".$alamat1."</td>
							<td style='text-align:left;font-size:14px;border-right:1px dashed #000;padding-left:10px;border-bottom:1px dashed #000;vertical-align:top;padding-top:10px' rowspan=2>Doc Num : ".$nopo."</td>
							<td style='text-align:left;font-size:14px;padding-left:5px;padding-left:10px'>Time In</td>
							<td style='text-align:center;font-size:14px;'>:</td>
							<td style='text-align:right;font-size:14px;'>".$jammasuk."</td>
						</tr>
						<tr>
							<td style='text-align:left;font-size:11px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$alamat2."</td>
							<td style='text-align:left;font-size:14px;padding-left:5px;padding-left:10px;padding-bottom:20px;border-bottom:1px dashed #000'>Time Out</td>
							<td style='text-align:center;font-size:14px;padding-bottom:20px;border-bottom:1px dashed #000'>:</td>
							<td style='text-align:right;font-size:14px;padding-bottom:20px;border-bottom:1px dashed #000'>".$jamkeluar."</td>
						</tr>
					</table>";
					
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:center;width:17%;font-size:14px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
							<td style='text-align:center;width:23%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
							<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>1st Weight</td>
							<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>2nd Weight</td>
							<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Gross</td>
							<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Sorting</td>
							<td style='text-align:center;width:12%;font-size:14px;padding-top:5px;'>Nett</td>
						</tr>
						<tr>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$nokendaraan."</td>
							<td style='text-align:center;padding-top:0px;font-size:14px;border-right:1px dashed #000;border-bottom:1px dashed #000;vertical-align:top'>".$optnmbarang[$kodebarang]."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratmasuk)."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratkeluar)."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto+$potongan)."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($potongan)."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto)."</td>
						</tr>
					</table>";
					
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:center;width:30%;font-size:14px;border-right:1px dashed #000;padding-top:5px'>Jumlah Janjang</td>
							<td style='text-align:center;width:18%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Brondolan</td>
							<td style='text-align:center;width:32%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Transporter</td>
							<td style='text-align:center;width:20%;font-size:14px;padding-top:5px;'>Driver</td>
						</tr>
						<tr>
							<td style='text-align:center;padding-top:-5px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$jjg."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$brondolan."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$transportir."</td>
							<td style='text-align:center;padding-top:10px;font-size:14px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".$supir."</td>
						</tr>
					</table>";
					
					$tab.="<table width=100%>
						<tr>
							<td height=60px>&nbsp;</td>
						</tr>
					</table>";
					
					// $tab.="<table width=100% cellspacing=0>
						// <tr>
							// <td style='text-align:center;width:25%;font-size:14px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
							// <td style='text-align:center;width:25%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
							// <td style='text-align:center;width:25%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>1st Weight</td>
							// <td style='text-align:center;width:25%;font-size:14px;padding-top:5px;'>Nett</td>
						// </tr>
						// <tr>
							// <td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$nokendaraan."</td>
							// <td style='text-align:center;padding-top:0px;font-size:14px;border-right:1px dashed #000;border-bottom:1px dashed #000;vertical-align:top'>".$optnmbarang[$kodebarang]."</td>
							// <td style='text-align:center;padding-top:10px;font-size:14px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratmasuk)."</td>
							// <td style='text-align:center;padding-top:10px;font-size:14px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto)."</td>
						// </tr>
					// </table>";
					
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:center;width:50%;font-size:14px;border-right:1px dashed #000;padding-top:5px;border-top:1px dashed #000'>Diterima</td>
							<td style='text-align:center;width:50%;font-size:14px;padding-top:5px;border-top:1px dashed #000'>Disetujui / Disaksikan</td>
						</tr>
						<tr>
							<td style='height:20px;text-align:center;border-right:1px dashed #000;padding-top:5px'>&nbsp;</td>
							<td style='text-align:center;padding-top:5px'>&nbsp;</td>
						</tr>
						<tr>
							<td style='text-align:center;font-size:14px;border-right:1px dashed #000'><u>&nbsp;&nbsp;".strtoupper($krani)."&nbsp;&nbsp;</u></td>
							<td style='text-align:center;font-size:14px;'><u>&nbsp;&nbsp;".$supir."&nbsp;&nbsp;</u></td>
						</tr>
						<tr>
							<td style='text-align:center;font-size:14px;border-right:1px dashed #000'>Krani Timbang</td>
							<td style='text-align:center;font-size:14px;'>Driver</td>
						</tr>
					</table>";
				}
				else
				{
					$tab.="<table width=100% cellspacing=0 cellpadding=5 border=0>

					<tr >
					<td width=10%></td>
					<td width=50%>No Tiket</td>
					<td width=1%>:</td>
					<td align=left width=80%>".$param['ticketno']."</td>

					</tr>
					<tr>
					<td width=10%></td>
					<td >Tgl Jam Masuk</td>
					<td >:</td>
					<td >".$waktumasuk."</td>

					</tr>
					<tr>
					<td width=10%></td>
					<td >Tgl Jam Keluar</td>
					<td >:</td>
					<td >".$waktukeluar."</td>

					</tr>
					<tr>
					<td width=10%></td>
					<td >No Kendaraan</td>
					<td >:</td>
					<td >".$nokendaraan."</td>

					</tr>
					<tr>
					<td width=10%></td>
					<td >Nama Customer</td>
					<td >:</td>
					<td >".$transportir."</td>

					</tr>
					<tr>
					<td width=10%></td>
					<td >Nama Barang</td>
					<td >:</td>
					<td >".$optnmbarang[$kodebarang]."</td>

					</tr>
					<tr>
					<td width=10%></td>
					<td >Nama Supir</td>
					<td >:</td>
					<td >".$supir."</td>

					</tr>
					<tr>
					<td width=10%></td>
					<td >No Refersi</td>
					<td >:</td>

					</tr>
					<tr>
					<td ></td>

					</tr>
					<tr>
					<td width=10%></td>
					<td ><b>Berat Bruto</b></td>
					<td >:</td>
					<td><b>".hidezerodecimal($beratmasuk)."</b></td>

					</tr>
					<tr>
					<td width=10%></td>
					<td ><b>Berat Tarra</b></td>
					<td >:</td>
					<td><b>".hidezerodecimal($beratkeluar)."</b></td>

					</tr>
					<tr>
					<td width=10%></td>
					<td ><b>Berat Netto</b></td>
					<td >:</td>
					<td><b>".hidezerodecimal($netto+$potongan)."</b></td>

					</tr>


					</table><br>";


					$tab.="<table width=100% cellspacing=0>
					<tr align=center>
					<td ></td>
					<td >Di Timbang</td>
					<td >Di Ketahui</td>
					</tr>
					<tr>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					</tr>
					<tr>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					</tr>


					<tr align=center>
					<td >&nbsp;</td>
					<td ><u>&nbsp;&nbsp;".strtoupper($krani)."&nbsp;&nbsp;</u></td>
					<td ><u>&nbsp;&nbsp;&nbsp;&nbsp;</u></td>
					</tr>

					</table>";
				}
					
					
				break;
				case $kodeprodukcpo:
				case $kodeprodukpk:

					$tab.="<style>
						@page {
							font-size:13px;
						}
					</style>";

					$optnmcustomer=makeOption($dbname,'mscustomer','custcode,custname',"custcode='".$customer."'");
					$optnokontrak=makeOption($dbname,'msso','noso,nosoinduk',"noso='".$noso."'");
					$tonasekontrak=makeOption($dbname,'msso','noso,soqty',"noso='".$noso."'");

					$sudahdiangkut=0;

					$str="select sum(netto) as sudahdiangkut from ".$dbname.".wb where kontrakjual='".$noso."' and (wbcond='Normal' or wbcond='') and notransaksi not in (select tiketref from wb where kontrakjual = '".$noso."' and wbcond='Return') and waktukeluar <= '".$waktukeluar."'";
					$res=fetchdata($str);
					$sudahdiangkut+=$res[0]['sudahdiangkut'];

					$str="select sum(nettosplit2) as sudahdiangkut from ".$dbname.".wb where kontrakjual2='".$noso."' and (wbcond='Normal' or wbcond='') and notransaksi not in (select tiketref from wb where kontrakjual = '".$noso."' and wbcond='Return') and waktukeluar <= '".$waktukeluar."'";
					$res=fetchdata($str);
					$sudahdiangkut+=$res[0]['sudahdiangkut'];

					$sisakontrak=$tonasekontrak[$noso]-$sudahdiangkut;
					if ($sisakontrak<0) {
						$sisakontrak=0;
					}

					if ($sudahdiangkut>$tonasekontrak[$noso]) {
						$sudahdiangkut=$tonasekontrak[$noso];
					}

					$tab.="<table width=100% cellspacing=0 border=0>
							<tr>
								<td style='text-align:center;font-size:20px;'><b>".$pt."</b></td>
							</tr>
							<tr>
								<td style='text-align:center;font-size:14px;'><b>SLIP TIMBANGAN - PENGIRIMAN PRODUK</b></td>
							</tr>

						</table>";

						$tab.="<table border=0 width=100%>
							<tr>
								<td width=25%>NO SLIP TIMBANGAN</td>
								<td>: ".$param['ticketno']."</td>
								<td>Storage Tank : ".$storage."</td>
							</tr>
							<tr>
								<td>HARI, TANGGAL</td>
								<td>: ".hari($waktukeluar).", ".substr(waktunormal($waktukeluar),0,10)."</td>
								<td></td>
							</tr>
						</table>";

						$tab.="<table width=100% cellspacing=0 border=1>
							<tr>
								<td align=center><b>DATA CUSTOMER</b></td>
								<td align=center><b>DATA TIMBANGAN</b></td>
							</tr>
							<tr>
								<td width=50% valign=top>
									<table border=0 cellspacing=0 width=100%>
										<tr>
											<td>NAMA CUSTOMER</td>
											<td>:</td>
											<td>".$supcon."</td>
										</tr>
										<tr>
											<td>JENIS PRODUK</td>
											<td>:</td>
											<td>".$optnmbarang[$kodebarang]."</td>
										</tr>
										<tr>
											<td>LOKASI PENGIRIMAN</td>
											<td>:</td>
										</tr>
									</table>
								</td>
								<td valign=top>
									<table border=0 width=100%>
										<tr>
											<td></td>
											<td>BRUTO</td>
											<td>:</td>
											<td align=right>".number_format($beratmasuk)." Kg</td>
										</tr>
										<tr>
											<td></td>
											<td>TARA</td>
											<td>:</td>
											<td align=right>".number_format($beratkeluar)." Kg</td>
										</tr>
										<tr>
											<td></td>
											<td colspan=3><hr></td>
										</tr>
										<tr>
											<td>TONASE KIRIM KONTRAK</td>
											<td>:</td>
											<td></td>
											<td align=right>".number_format($netto)." Kg</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td rowspan=6 valign=top>
									<table>
										<tr>
											<td colspan=3 align=center><b>DATA KONTRAK</b></td>
										</tr>
										<tr>
											<td>NO DO</td>
											<td>:</td>
											<td>".$noso."</td>
										</tr>
										<tr>
											<td>NO KONTRAK</td>
											<td>:</td>
											<td>".$optctr[$noso]."</td>
										</tr>
										<tr>
											<td>VOLUME KONTRAK</td>
											<td>:</td>
											<td>".number_format($optsdo[$noso])."</td>
										</tr>
										<tr>
											<td>TONASE PENGIRIMAN</td>
											<td>:</td>
											<td>".number_format($netto)."</td>
										</tr>
										<tr>
											<td>SISA KONTRAK</td>
											<td>:</td>
											<td>".number_format($sisakontrak)."</td>
										</tr>
									</table>

								</td>
								<td align=center><b>DATA ANALISA LAB</b></td>
							</tr>
							<tr>
								<td rowspan=2 valign=top>
									<table width=100%>
										<tr>
											<td>FFA</td>
											<td>:</td>
											<td>".$ffa." %</td>
											<td>KADAR AIR</td>
											<td>:</td>
											<td>".$moist." %</td>
										</tr>
										<tr>
											<td></td>
											<td></td>
											<td></td>
											<td>KADAR KOTORAN</td>
											<td>:</td>
											<td>".$dirt." %</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr></tr>
							<tr>
								<td align=center><b>DATA TRANSPORTASI</b></td>
							</tr>
							<tr>
								<td rowspan=2>
									<table width=100% border=0 cellspacing=0>
										<tr>
											<td nowrap>NAMA PENGANGKUTAN</td>
											<td>:</td>
											<td>".$transportir."</td>
										</tr>
										<tr>
											<td>NO DO KECIL / SP</td>
											<td>:</td>
											<td></td>
										</tr>
										<tr>
											<td>NO POL KEND</td>
											<td>:</td>
											<td>".$nokendaraan."</td>
										</tr>
										<tr>
											<td>NAMA SUPIR / SIM</td>
											<td>:</td>
											<td nowrap>".$supir." / ".$nosim."</td>
										</tr>
										<tr>
											<td nowrap>JAM MASUK : ".substr($waktumasuk, 11,10)."</td>
											<td></td>
											<td>JAM KELUAR : ".substr($waktukeluar, 11,10)."</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr></tr>

						</table>";
						$tab.="&nbsp;";
						$tab.="<table width=100% cellspacing=0 border=0>
							<tr>
								<td align=center>Diperiksa Oleh, <br> Asisten</td>
								<td align=center>Ditimbang Oleh, <br> Timbangan</td>
								<td align=center>Diserahkan Kepada, <br> Supir</td>
							</tr>
							<tr>
								<td colspan=3 height=60px></td>
							</tr>
							<tr>
								<td align=center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
								<td align=center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
								
								<td align=center>(".$supir.")</td>
							</tr>
						</table>";

						/*$tab.="<style>
							.page_break { page-break-after: always; }
							@page {
								margin: 20px 20px 0 20px !important;
								font-size:10px;
							}
						</style>";
						$tab.="<div class='page_break'></div>";

						$tab.="<table width=100% cellspacing=0 cellpadding=5 border=1>
							<tr>
								<td rowspan=2>LOGO</td>
								<td colspan=3 nowrap style='text-align:left;font-size:12px;'><b>".$pt."</b></td>
								<td colspan=4 style='text-align:center;font-size:12px;'><b>TANDA PENYERAHAN</b></td>
								<td colspan=5>Nomor :</td>
							</tr>
							<tr>
								<td colspan=3>PKS : ".$millcode."</td>
								<td colspan=4 align=center nowrap>() Ekspor &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; () Lokal</td>
								<td nowrap colspan=5>Tanggal : ".tanggalnormal(substr($waktukeluar, 0,10))."</td>
							</tr>
							<tr>
								<td colspan=6>Dari : ".$millname."</td>
								<td colspan=7>Kepada : ".$optnmcustomer[$customer]."</td>
							</tr>
							<tr>
								<td style='text-align:center;font-size:11px;padding-top:1px' colspan=4 align=center>REFERENSI</td>
								<td style='text-align:center;font-size:11px;padding-top:1px' colspan=4 align=center>Dikirim <br> Bal / Tangki / Truk / Karung</td>
								<td style='text-align:center;font-size:11px;padding-top:1px' colspan=5 nowrap>Diterima <br> Bal / Tangki / Truk / Karung</td>
							</tr>
							<tr>
								<td style='text-align:center;padding-top:1px'>No. Kontrak</td>
								<td style='text-align:center;padding-top:1px'>No. Order Penyerahan</td>
								<td style='text-align:center;padding-top:1px'>No. Intruksi</td>
								<td style='text-align:center;padding-top:1px'>Banyaknya (Kg)</td>
								<td style='text-align:center;padding-top:1px'>Banyaknya</td>
								<td style='text-align:center;padding-top:1px'>Kotor</td>
								<td style='text-align:center;padding-top:1px'>Tarra</td>
								<td style='text-align:center;padding-top:1px'>*) Bersih</td>
								<td style='text-align:center;padding-top:1px'>S/d</td>
								<td style='text-align:center;padding-top:1px'>Banyaknya</td>
								<td style='text-align:center;padding-top:1px'>Kotor</td>
								<td style='text-align:center;padding-top:1px'>Tarra</td>
								<td style='text-align:center;padding-top:1px'>*) Bersih</td>
							</tr>
							<tr>
								<td style='text-align:center;padding:1px;height:70px;'>".$optctr[$noso]."</td>
								<td style='text-align:center;padding:1px;height:70px;'>".$noso."</td>
								<td style='text-align:center;padding-top:1px;height:70px;'></td>
								<td style='text-align:center;padding-top:1px;height:70px;'>".number_format($optsdo[$noso])."</td>
								<td style='text-align:center;padding-top:1px;height:70px;'>TRUCK</td>
								<td style='text-align:center;padding-top:1px;height:70px;'>".number_format($beratkeluar)."</td>
								<td style='text-align:center;padding-top:1px;height:70px;'>".number_format($beratmasuk)."</td>
								<td style='text-align:center;padding-top:1px;height:70px;'>".number_format($netto)."</td>
								<td style='text-align:center;padding-top:1px;height:70px;'>".number_format($sudahdiangkut)."</td>
								<td style='text-align:center;padding-top:1px;height:70px;'></td>
								<td style='text-align:center;padding-top:1px;height:70px;'></td>
								<td style='text-align:center;padding-top:1px;height:70px;'></td>
								<td style='text-align:center;padding-top:1px;height:70px;'></td>
							</tr>
							<tr>
								<td colspan=4 style='text-align:left;padding-top:1px' nowrap>*) Dengan Huruf ".terbilang($optsdo[$noso],1)." KILO GRAM</td>
								<td colspan=4 style='text-align:left;padding-top:1px' nowrap>*) ".terbilang($netto,1)." KILO GRAM</td>
								<td colspan=5></td>
							</tr>
							<tr>
								<td colspan=13 style='text-align:left;padding-top:1px'>Keterangan Lainnya : SISA KONTRAK S/D ".number_format($sisakontrak)." &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; NO LOCIS ATAS ".$segel."</td>
							</tr>
							<tr>
								<td colspan=13 style='text-align:left;padding-top:1px'></td>
							</tr>

							<tr>
								<td colspan=13 style='padding:1px'>
									<table width=100% border=0 cellspacing=0>
										<tr>
											<td width=40%>
												<table border=0 cellspacing=0 width=100%>
													<tr>
														<td style='text-align:left;padding-top:1px'>No. Tangki/Truk</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'>".$nokendaraan."</td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>Jam Berangkat</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'>".substr($waktukeluar, 10,11)."</td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>Jam Pulang</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>Tanggal Kembali</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>Nama Supir</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'>".$supir."</td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>Pengangkutan</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'>".$pt."</td>
													</tr>
												</table>
											</td>
											<td valign=top width=30%>
												<table border=0 cellspacing=0 cellpadding=6 width=100%>
													<tr>
														<td style='text-align:left;padding-top:1px'>Ullage (Cm)</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>Suhu (C)</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>F.F.A</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>% Air</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>% Kotoran</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
												</table>
											</td>
											<td valign=top width=30%>
												<table border=0 cellspacing=0 width=100%>
													<tr>
														<td style='text-align:left;padding-top:1px'>Jam Diterima</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>Tgl. Diterima</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>Ullage (Cm)</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>Suhu (C)</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>F.F.A</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>% Air</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
													<tr>
														<td style='text-align:left;padding-top:1px'>% Kotoran</td>
														<td style='text-align:left;padding-top:1px;width:10px'>:</td>
														<td style='text-align:left;padding-top:1px'></td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan=2 style='text-align:center;padding-top:1px'>Disetujui Oleh :</td>
								<td colspan=2 style='text-align:center;padding-top:1px'>Dibuat Oleh :</td>
								<td colspan=2 style='text-align:center;padding-top:1px'>Diperiksa Oleh :</td>
								<td colspan=2 style='text-align:center;padding-top:1px'>Dibukukan Oleh :</td>
								<td colspan=5 style='text-align:center;padding-top:1px'>Diterima Oleh :</td>
							</tr>
							<tr>
								<td colspan=2 style='text-align:center;padding-top:1px;height:50px;'></td>
								<td colspan=2 style='text-align:center;padding-top:1px;height:50px;'></td>
								<td colspan=2 style='text-align:center;padding-top:1px;height:50px;'></td>
								<td colspan=2 style='text-align:center;padding-top:1px;height:50px;'></td>
								<td colspan=2 style='text-align:center;padding-top:1px;height:50px;'></td>
								<td colspan=3 style='text-align:center;padding-top:1px;height:50px;'></td>
							</tr>
							<tr>
								<td colspan=2 style='text-align:center;padding-top:1px;'>Manager</td>
								<td colspan=2 style='text-align:center;padding-top:1px;'>Asst. Lab</td>
								<td colspan=2 style='text-align:center;padding-top:1px;'>K.T.U</td>
								<td colspan=2 style='text-align:center;padding-top:1px;'>Krani Produksi</td>
								<td colspan=2 style='text-align:center;padding-top:1px;'>Pengangkutan</td>
								<td colspan=3 style='text-align:center;padding-top:1px;'>Pembeli / Pelabuhan</td>
							</tr>
						</table>";*/


						
				break;

				case 'produklain':
					$tab.="<table width=100% cellspacing=0 border=0 cellpadding=-2>
						<tr>
							<td style='text-align:left;font-size:14px;border-right:1px dashed #000;width:35%;padding-top:10px'>".$pt."</td>
							<td style='text-align:left;font-size:14px;border-right:1px dashed #000;width:35%;padding-top:10px;padding-left:10px'>Ticket No : ".$param['ticketno']."</td>
							<td style='text-align:left;font-size:14px;padding-left:10px;padding-top:10px'>Date</td>
							<td style='text-align:center;font-size:14px;padding-top:10px;'>:</td>
							<td style='text-align:right;font-size:14px;padding-top:10px;'>".tglstrip($tanggal)."</td>
						</tr>
						<tr>
							<td style='text-align:left;font-size:11px;border-right:1px dashed #000'>".$alamat1."</td>

							<td style='text-align:left;font-size:14px;border-right:1px dashed #000;padding-left:10px;vertical-align:top;padding-top:10px'>".$doc." No : ".$nopo."</td>
							
							<td style='text-align:left;font-size:14px;padding-left:5px;padding-left:10px'>Time In</td>
							<td style='text-align:center;font-size:14px;'>:</td>
							<td style='text-align:right;font-size:14px;'>".$jammasuk."</td>
						</tr>
						<tr>
							<td style='text-align:left;font-size:11px;border-right:1px dashed #000;padding-bottom:15px;border-bottom:0.1px solid #000'>".$alamat2."</td>
							
							<td style='text-align:left;font-size:14px;border-right:1px dashed #000;padding-left:10px;border-bottom:0.1px solid #000;vertical-align:top;padding-top:10px'>".($nopo2!=""?$doc." No : ".$nopo2.", Netto : ".number_format($nettosplit2):"")."</td>

							<td style='text-align:left;font-size:14px;padding-left:5px;padding-left:10px;padding-bottom:15px;border-bottom:0.1px solid #000'>Time Out</td>
							<td style='text-align:center;font-size:14px;padding-bottom:15px;border-bottom:0.1px solid #000'>:</td>
							<td style='text-align:right;font-size:14px;padding-bottom:15px;border-bottom:0.1px solid #000'>".$jamkeluar."</td>
						</tr>
					</table>";
					
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:center;width:17%;font-size:14px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
							<td style='text-align:center;width:23%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
							<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Tarra</td>
							<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Bruto</td>
							<td style='text-align:center;width:12%;font-size:14px;padding-top:5px;'>Netto</td>
						</tr>
						<tr>
							<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:5px;border-bottom:0.1px solid #000'>".$nokendaraan."</td>
							<td style='text-align:center;padding-top:0px;font-size:14px;border-right:1px dashed #000;border-bottom:0.1px solid #000;vertical-align:top'>".$optnmbarang[$kodebarang2]."</td>
							<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:5px;border-bottom:0.1px solid #000'>".hidezerodecimal($beratmasuk)."</td>
							<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:5px;border-bottom:0.1px solid #000'>".hidezerodecimal($beratkeluar)."</td>
							<td style='text-align:center;padding-top:5px;font-size:14px;padding-top:5px;padding-bottom:5px;border-bottom:0.1px solid #000'>".hidezerodecimal($netto)."</td>
						</tr>
					</table>";
					
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:center;width:30%;font-size:14px;border-right:1px dashed #000;padding-top:5px'>Supplier / Customer</td>
							<td style='text-align:center;width:32%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Transporter</td>
							<td style='text-align:center;width:32%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Driver</td>
							<td style='text-align:center;width:20%;font-size:14px;padding-top:5px;'>License No</td>
						</tr>
						<tr>
							<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:10px;border-bottom:0.1px solid #000'>".$supcon." <br> ".@$nmdivisi[$divisi]."</td>
							<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:10px;border-bottom:0.1px solid #000'>".$transportir."</td>
							<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:10px;border-bottom:0.1px solid #000'>".$supir."</td>
							<td style='text-align:center;padding-top:5px;font-size:14px;padding-top:5px;padding-bottom:10px;border-bottom:0.1px solid #000'>".$nosim."</td>
						</tr>
					</table>";
					
					$tab.="<table width=100% cellspacing=0 cellpadding=-1>
						<tr>
							
							<td style='text-align:center;width:50%;font-size:14px;border-right:1px dashed #000;'>
								<table>
									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>Contract No</td>
										<td>:</td>
										<td>".$nokontrak."".($nokontrak2!=''?' , '.$nokontrak2:'')."</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>Contract Qty</td>
										<td>:</td>
										<td>".hidezerodecimal($sdo,0)."".($sdo2!=0?' , '.hidezerodecimal($sdo2,0):'')."</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>Outstanding Qty</td>
										<td>:</td>
										<td>".number_format(@$optsisaso[$nopo])."</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>Remark</td>
										<td>:</td>
										<td>".$notekirim."</td>
									</tr>
								</table>
							</td>
							<td style='text-align:center;width:50%;font-size:14px;vertical-align:top'>
								<table>
									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>Seal No</td>
										<td>:</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td colspan=2 style='padding-left:10px'>".$segel."</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>";
					
					$tab.="<table width=100% cellspacing=0>
						<tr>
							<td style='text-align:center;width:50%;font-size:14px;border-right:1px dashed #000;padding-top:5px;border-top:1px dashed #000'>Diterima</td>
							<td style='text-align:center;width:50%;font-size:14px;padding-top:5px;border-top:1px dashed #000'>Disetujui / Disaksikan</td>
						</tr>
						<tr>
							<td style='height:20px;text-align:center;border-right:1px dashed #000;padding-top:5px'>&nbsp;</td>
							<td style='text-align:center;padding-top:5px'>&nbsp;</td>
						</tr>
						<tr>
							<td style='text-align:center;font-size:14px;border-right:1px dashed #000'><u>&nbsp;&nbsp;".strtoupper($krani)."&nbsp;&nbsp;</u></td>
							<td style='text-align:center;font-size:14px;'><u>&nbsp;&nbsp;".$supir."&nbsp;&nbsp;</u></td>
						</tr>
						<tr>
							<td style='text-align:center;font-size:14px;border-right:1px dashed #000'>Krani Timbang</td>
							<td style='text-align:center;font-size:14px;'>Driver</td>
						</tr>
					</table>";
				break;
			}
			
			default:
			
			break;
		
		break;
		
	}
	
	$tab.="<table width=100% cellspacing=0>
		<tr>
			<td style='text-align:left;width:50%;font-size:14px;padding-top:15px'>Print By ".$krani." ".date('d/m/Y H:i:s')."</td>
			<td style='text-align:right;width:50%;font-size:14px;padding-top:15px'>".($printversion==0?'ORIGINAL PRINT':'COPY PRINT '.$printversion)."</td>
		</tr>
	</table>";
	
	if($nopo2!=''){
		$tab.="<style>
			.page_break { page-break-after: always; }
			@page {
				margin: 5px 20px 0 40px !important;
			}
		</style>";
		$tab.="<div class='page_break'></div>";
		$tab.="<table width=100% cellspacing=0 border='0' cellpadding=-1>
			<tr>
				<td rowspan=3 style='border-bottom:0.1px solid #000'>
					<img src='images/logo_bsp.jpg' width=50px>
				</td>
				<td style='text-align:center;font-size:20px;'>BUKTI ".$textheader." ".$optnmbarang[$kodebarang2]."</td>
			</tr>
			<tr>
				<td style='text-align:right;font-size:10px'>Form : BSPMS-0-FR-02</td>
			</tr>
			<tr>
				<td style='text-align:right;font-size:10px;border-bottom:0.1px solid #000'>REV. : ".addzero($printversion,2)."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0 border=0 cellpadding=-2>
			<tr>
				<td style='text-align:left;font-size:14px;border-right:1px dashed #000;width:38%;padding-top:10px'>".$pt."</td>
				<td rowspan=3 style='text-align:left;font-size:14px;border-right:1px dashed #000;width:32%;padding-left:10px;border-bottom:0.1px solid #000''>Ticket No : ".$param['ticketno']."</td>
				<td style='text-align:left;font-size:14px;padding-left:10px;padding-top:10px'>Date</td>
				<td style='text-align:center;font-size:14px;padding-top:10px;'>:</td>
				<td style='text-align:right;font-size:14px;padding-top:10px;'>".tglstrip($tanggal)."</td>
			</tr>
			<tr>
				<td style='text-align:left;font-size:11px;border-right:1px dashed #000'>".$alamat1."</td>

				<td style='text-align:left;font-size:14px;padding-top:5px;padding-left:10px'>Time In</td>
				<td style='text-align:center;font-size:14px;'>:</td>
				<td style='text-align:right;font-size:14px;'>".$jammasuk."</td>
			</tr>
			<tr>
				<td style='text-align:left;font-size:11px;border-right:1px dashed #000;padding-bottom:5px;border-bottom:0.1px solid #000'>".$alamat2."</td>
				
				<td style='text-align:left;font-size:14px;padding-top:5px;padding-left:10px;padding-bottom:5px;border-bottom:0.1px solid #000'>Time Out</td>
				<td style='text-align:center;font-size:14px;padding-bottom:5px;border-bottom:0.1px solid #000'>:</td>
				<td style='text-align:right;font-size:14px;padding-bottom:5px;border-bottom:0.1px solid #000'>".$jamkeluar."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:17%;font-size:14px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
				<td style='text-align:center;width:23%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
				<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Tarra</td>
				<td style='text-align:center;width:12%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Bruto</td>
				<td style='text-align:center;width:12%;font-size:14px;padding-top:5px;'>Netto</td>
			</tr>
			<tr>
				<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:5px;border-bottom:0.1px solid #000'>".$nokendaraan."</td>
				<td style='text-align:center;padding-top:0px;font-size:14px;border-right:1px dashed #000;border-bottom:0.1px solid #000;vertical-align:top'>".$optnmbarang[$kodebarang]."</td>
				<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:5px;border-bottom:0.1px solid #000'>".hidezerodecimal($beratmasuk)."</td>
				<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:5px;border-bottom:0.1px solid #000'>".hidezerodecimal($beratkeluar)."</td>
				<td style='text-align:center;padding-top:5px;font-size:14px;padding-top:5px;padding-bottom:5px;border-bottom:0.1px solid #000'>".hidezerodecimal($netto)."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:30%;font-size:14px;border-right:1px dashed #000;padding-top:5px'>Supplier / Customer</td>
				<td style='text-align:center;width:32%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Transporter</td>
				<td style='text-align:center;width:32%;font-size:14px;border-right:1px dashed #000;padding-top:5px;'>Driver</td>
				<td style='text-align:center;width:20%;font-size:14px;padding-top:5px;'>License No</td>
			</tr>
			<tr>
				<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:10px;border-bottom:0.1px solid #000'>".$supcon."</td>
				<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:10px;border-bottom:0.1px solid #000'>".$transportir."</td>
				<td style='text-align:center;padding-top:5px;font-size:14px;border-right:1px dashed #000;padding-bottom:10px;border-bottom:0.1px solid #000'>".$supir."</td>
				<td style='text-align:center;padding-top:5px;font-size:14px;padding-top:5px;padding-bottom:10px;border-bottom:0.1px solid #000'>".$nosim."</td>
			</tr>
		</table><br>";
		
		$tab.="<table cellspacing=0>
			<tr style='font-weight:bold'>
				<td colspan=7 style='padding-bottom:10px'>SPLIT TICKET INFORMATION :</td>
			</tr>
			<tr>
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px;padding-left:15px'>".$doc." No</td>
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px'>:</td>
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px'>".$nopo."</td>
				
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px;padding-left:100px;'>Netto</td>
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px'>:</td>
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px;text-align:right'>".number_format($nettosplit)."</td>
				
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px;text-align:right;width:50px;'>&nbsp;</td>
			</tr>
			<tr>
				<td colspan=7>&nbsp;</td>
			</tr>
			<tr>
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px;padding-left:15px'>".$doc." No</td>
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px'>:</td>
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px'>".$nopo2."</td>
				
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px;padding-left:100px'>Netto</td>
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px'>:</td>
				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px;text-align:right'>".number_format($nettosplit2)."</td>

				<td style='border-top:1px dashed #000;border-bottom:1px dashed #000;padding-top:5px;padding-bottom:5px;text-align:right;width:50px;'>&nbsp;</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:left;width:50%;font-size:14px;padding-top:15px'>Print By ".$krani." ".date('d/m/Y H:i:s')."</td>
				<td style='text-align:right;width:50%;font-size:14px;padding-top:15px'>".($printversion==0?'ORIGINAL PRINT':'COPY PRINT '.$printversion)."</td>
			</tr>
		</table>";
	}
	
	$str="update ".$dbname.".wb set printversion=printversion+1 where notransaksi='".$param['ticketno']."'";
	$owlPDO->exec($str);
	
	$dompdf = new Dompdf();
	$dompdf->loadHtml($tab);
	if ($sumber=='PABRIK') {
	
		$dompdf->setPaper('A5', 'landscape');
	}
	else
	{
		$dompdf->setPaper('A6', 'potrait');
	}
	
	$dompdf->render();
	$dompdf->stream("Tiket Timbang", array("Attachment" => false));
	
	$owlPDO->commit();
}catch (PDOException $e) {$owlPDO->rollback(); echo "Gagal, " . addslashes($e->getMessage()); die();}
?>
