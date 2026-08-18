<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];

$nopo = $column;
$exnopo = explode('/',$nopo);
$exnopo2 = explode('-',$exnopo[3]);
$jnsnopo = $exnopo2[0];
$kodept = $exnopo[5];

$urlefil=checkPostGet('urlefil','0');

$tab="";

## GET PO HT
$str="select purchaser,tanggal,syaratbayar,kodesupplier,alamatsup,npwpsup,kodeorg,matauang,idFranco,deliverytime,nodph,uraian,subtotal,diskonpersen,nilaidiskon,pbbkb,persenppn,ppn,addcost,waktucetak from ".$dbname.".log_poht where nopo='".$nopo."'";
$res=fetchdata($str);
$purchaser=$res[0]['purchaser'];
$tglpo = tanggalnormal($res[0]['tanggal']);
$syaratbayar = $res[0]['syaratbayar'];
$kodesupplier = $res[0]['kodesupplier'];
$alamatsup = $res[0]['alamatsup'];
$npwpsupplier = $res[0]['npwpsup'];
$kodept = $res[0]['kodeorg'];
$matauang = $res[0]['matauang'];
$idfranco = $res[0]['idFranco'];
$iddeliverytime = $res[0]['deliverytime'];
$nodph=$res[0]['nodph'];
$keterangan=$res[0]['uraian'];
$subtotal=$res[0]['subtotal'];
$diskonpersen=$res[0]['diskonpersen'];
$nilaidiskon=$res[0]['nilaidiskon'];
$pbbkb=$res[0]['pbbkb'];
$persenppn=$res[0]['persenppn'];
$ppn=$res[0]['ppn'];
$addcost=$res[0]['addcost'];
$grandtotal=(($subtotal-$nilaidiskon)+$pbbkb)+$ppn-$pph+$addcost;
$waktucetak = ($res[0]['waktucetak']=='0000-00-00 00:00:00' ? '' : tglnmblnsec($res[0]['waktucetak'],'E',''));

## GET FRANCO
$str="select franco_name,alamat,contact,handphone from ".$dbname.".setup_franco where id_franco='".$idfranco."'";
$res=fetchdata($str);
$franco = $res[0]['franco_name'];
$deliveryto=$res[0]['alamat'].", ".$res[0]['contact']." / ".$res[0]['handphone'];

## GET Delivery TIME
$optdeltime = makeOption($dbname,'log_5delivtime','kode,nama',"kode='".$iddeliverytime."'");
$deliverytime = $optdeltime[$iddeliverytime];

## GET NO REFERENSI
$norefrensi=0;
$refrensi = "";
$str="select nopo from ".$dbname.".log_sorefrensi where noso='".$nopo."' group by nopo";
$res=fetchdata($str);
foreach($res as $val){
	$norefrensi++;
	if($norefrensi==1){
		$refrensi=$val['nopo'];
	}else{
		$refrensi.=', '.$val['nopo'];
	}
}

## GET NO PP
$str="select nopp from ".$dbname.".log_podt where nopo='".$nopo."' group by nopp";
$res=fetchdata($str);
$countpp = 0;
$refpp="";
foreach($res as $val){
	$countpp++;
	if($countpp==1){
		$refpp=$val['nopp'];
	}else{
		$refpp.=', '.$val['nopp'];
	}
}

## GET NO RFQ
$str="select nomor from ".$dbname.".log_permintaanhargadt where norph='".$nodph."'";
$res=fetchdata($str);
$norpq = $res[0]['nomor'];

## CREATE FORMAT DATE PO/SO
$exptglpo=explode("-",$tglpo);
$bulanpo=$exptglpo[1];
switch ($bulanpo) {
	case '01':$bulanpo='Januari';break;
	case '02':$bulanpo='Februari';break;
	case '03':$bulanpo='Maret';break;
	case '04':$bulanpo='April';break;
	case '05':$bulanpo='Mei';break;
	case '06':$bulanpo='Juni';break;
	case '07':$bulanpo='Juli';break;
	case '08':$bulanpo='Agustus';break;
	case '09':$bulanpo='September';break;
	case '10':$bulanpo='Oktober';break;
	case '11':$bulanpo='November';break;
	case '12':$bulanpo='Desember';break;
}
$tglpobaru=$exptglpo[0].' '.$bulanpo.' '.$exptglpo[2];

## KETERANGAN SYARAT BAYAR
$opttop = makeOption($dbname,'log_5syaratbayar','kode,keterangan',"kode='".$syaratbayar."'");
$top = $opttop[$syaratbayar];

## GET NPWP PT
$str="select npwp,alamatnpwp from ".$dbname.".setup_org_npwp where kodeorg='".$kodept."'";
$res=fetchdata($str);
$npwppt = $res[0]['npwp'];
$alamatnpwppt = $res[0]['alamatnpwp'];

## GET DETAIL SUPPLIER/Vendor
$str="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$kodesupplier."'";
$res=fetchdata($str);
$namasupplier = $res[0]['namasupplier'];

$str="select alamat,kota,telepon,fax,kontakperson from ".$dbname.".log_5supalamat where id_alamat='".$alamatsup."'";
$res=fetchData($str);
$alamatsupplier = $res[0]['alamat'];
$kotasupplier = $res[0]['kota'];
$teleponsupplier = $res[0]['telepon']."".($res[0]['fax']=='0'?'':' / '.$res[0]['fax']);
$kontaksupplier = $res[0]['kontakperson'];

## HEADER KOP
$arrHead = setheadreport('',$kodept);

## CREATE TITLE HEADER
$tab.="<table width=100%>
	<tr style='text-align:center;font-weight:bold;font-size:14px'>
		<td>".strtoupper($jnsnopo=='PO'?'Purchase Order':'Service Order')."</td>
	</tr>
	<tr style='text-align:center;font-weight:bold;font-size:14px'>
		<td>(".$jnsnopo.")</td>
	</tr>
	<tr>
		<td>
			<img src='".$arrHead['logo']."' width=50px>
		</td>
	</tr>
</table>

<table width=700px>
	<tr>
		<td width=350px>
			<table style='width:300px;font-size:12px'>
				<tr>
					<td>".$arrHead['nama']."</td>
				</tr>
				<tr>
					<td>".$arrHead['alamat']."</td>
				</tr>
				<tr>
					<td>".$arrHead['telepon']."</td>
				</tr>
			</table>
		</td>
		<td width=350px>
			<table style='font-size:12px'>
				<tr>
					<td width=70px>".$jnsnopo." NO.</td>
					<td width=10px>:</td>
					<td>".$nopo."</td>
				</tr>
				<tr>
					<td>Date</td>
					<td>:</td>
					<td>".$tglpobaru."</td>
				</tr>
				<tr>
					<td>T.O.P</td>
					<td>:</td>
					<td>".$top."</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<hr style='border-width:0.5px;'>

<table width=700px>
	<tr>
		<td width=350px style='vertical-align:top'>
			<table style='width:300px;font-size:11px'>
				<tr>
					<td>Vendor :</td>
				</tr>
				<tr>
					<td colspan=3>".$namasupplier."</td>
				</tr>
				<tr>
					<td colspan=3>".$alamatsupplier."</td>
				</tr>
				<tr>
					<td colspan=3>".$kotasupplier."</td>
				</tr>
				<tr>
					<td width=20px>NPWP</td>
					<td width=1px>:</td>
					<td>".$npwpsupplier."</td>
				</tr>
				<tr>
					<td>Telp/Fax</td>
					<td>:</td>
					<td>".$teleponsupplier."</td>
				</tr>
				<tr>
					<td>Attn</td>
					<td>:</td>
					<td>".$kontaksupplier."</td>
				</tr>
			</table>
		</td>
		<td width=350px style='vertical-align:top'>
			<table style='font-size:12px'>
				<tr>
					<td>Standard Tax Invoice, under the name of : </td>
				</tr>
				<tr>
					<td>".$arrHead['nama']."</td>
				</tr>
				<tr>
					<td>".$alamatnpwppt."".($arrHead['kodepos']==''?'':', '.$arrHead['kodepos'])."</td>
				</tr>
				<tr style='font-weight:bold'>
					<td>NPWP : ".$npwppt."</td>
				</tr>
			</table>
		</td>
	</tr>
</table>";

## CREATE LIST ITEM PO/SO
$tab.="<table style='font-size:11px' cellpadding=2 cellspacing=0>
	<tr>
		<td colspan=9 style='text-align:center;border-left:0.5px solid #000000;border-top:0.5px solid #000000;border-right:0.5px solid #000000'>Thank you for not providing any types of gravity to employees of ".$arrHead['nama']."</td>
	</tr>
	<tr>
		<td colspan=9 style='text-align:center;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;border-right:0.5px solid #000000'>Failure in doing this will result in the termination of business contract with ".$arrHead['nama']."</td>
	</tr>
	<tr style='font-weight:bold;background-color:#CDCDCD'>
		<td style='text-align:center;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>No</td>
		<td colspan=2 style='text-align:center;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>SPECIFICATION</td>
		<td colspan=2 style='text-align:center;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>QUANTITY</td>
		<td colspan=2 style='text-align:center;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".strtoupper($_SESSION['lang']['unit'])."</td>
		<td colspan=2 style='text-align:center;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".strtoupper($_SESSION['lang']['amount'])."</td>
	</tr>";
	
	## LIST ITEM PO/SO
	$no=0;
	$str="select kodebarang,satuan,jumlahpesan,jmlhstlhclose,matauang,hargasbldiskon,catatan from ".$dbname.".log_podt where nopo='".$nopo."'";
	$res=fetchdata($str);
	foreach($res as $val){
		$no++;
		$spek='';
		$catatan='';
		$optnamabrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
		$optspek = makeOption($dbname,'log_5photobarang','kodebarang,spesifikasi',"kodebarang='".$val['kodebarang']."'");
		$spek = $optspek[$val['kodebarang']];
		$catatan = $val['catatan'];
		
		$namabarang = $optnamabrg[$val['kodebarang']];
		$jumlah=$val['jumlahpesan'];
		if($jumlah==0){
			$jumlah=$val['jmlhstlhclose'];
		}
		$hargasatuan=$val['hargasbldiskon'];
		$total=$jumlah*$hargasatuan;
		
		$tab.="<tr style='vertical-align:top'>
			<td style='text-align:center;width:30px;border-left:0.5px solid #000000;'>".$no."</td>
			<td style='text-align:center;width:80px;border-left:0.5px solid #000000;'>".$val['kodebarang']."</td>
			<td style='text-align:left;width:200px'>".$namabarang."".($spek==''?'':' '.$spek)."".($catatan==''?'':'<br>'.$catatan)."</td>
			<td style='text-align:center;width:80px;border-left:0.5px solid #000000;'>".hidezerodecimal($jumlah,3)."</td>
			<td style='text-align:center;width:40px'>".$val['satuan']."</td>
			<td style='text-align:center;width:40px;border-left:0.5px solid #000000;'>".$val['matauang']."</td>
			<td style='text-align:right;width:80px'>".number_format($hargasatuan,2)."</td>
			<td style='text-align:center;width:40px;border-left:0.5px solid #000000;'>".$val['matauang']."</td>
			<td style='text-align:right;width:80px;border-right:0.5px solid #000000;'>".number_format($total,2)."</td>
		</tr>";
	}
	
	## LIST MATERIAL SO
	$str="select namabarang,jumlah,harga from ".$dbname.".log_somaterial where nopo='".$nopo."'";
	$res=fetchdata($str);
	if(count($res) > 0){
		foreach($res as $val){
			$no++;
			$total = $val['jumlah'] * $val['harga'];
			$tab.="<tr style='vertical-align:top'>
			<td style='text-align:center;border-left:0.5px solid #000000;'>".$no."</td>
			<td style='text-align:center;border-left:0.5px solid #000000;'></td>
			<td style='text-align:left'>".$val['namabarang']."</td>
			<td style='text-align:center;border-left:0.5px solid #000000;'>".hidezerodecimal($val['jumlah'],3)."</td>
			<td style='text-align:center'></td>
			<td style='text-align:center;border-left:0.5px solid #000000;'>".$matauang."</td>
			<td style='text-align:right'>".number_format($val['harga'],2)."</td>
			<td style='text-align:center;border-left:0.5px solid #000000;'>".$matauang."</td>
			<td style='text-align:right;border-right:0.5px solid #000000;'>".number_format($total,2)."</td>
		</tr>";
		}
	}
	
	$tab.="<tr>
		<td colspan=9 style='border-top:0.5px solid #000000'></td>
	</tr>
</table>";

## FOOTER PO/SO
$tab.="<table style='font-size:11px' cellpadding=0 cellspacing=0>
	<tr style='vertical-align:top'>
		<td style='width:490px;border:0.5px solid #000000'>
			<table cellpadding=2>
				<tr style='vertical-align:top'>
					<td colspan=4>".strtoupper('Term & Condition')."</td>
				</tr>
				<tr style='vertical-align:top'>
					<td style='width:10px'>1.</td>
					<td style='width:80px'>Delivery Type</td>
					<td style='width:3px'>:</td>
					<td style='width:358px'>".$franco."</td>
				</tr>
				<tr style='vertical-align:top'>
					<td>2.</td>
					<td>Term of Payment</td>
					<td>:</td>
					<td>".$top."</td>
				</tr>
				<tr style='vertical-align:top'>
					<td>3.</td>
					<td>Price</td>
					<td>:</td>
					<td>".$matauang."</td>
				</tr>
				<tr style='vertical-align:top'>
					<td>4.</td>
					<td>Delivery Time</td>
					<td>:</td>
					<td>".$deliverytime."</td>
				</tr>
				<tr style='vertical-align:top'>
					<td>5.</td>
					<td>Norefrensi</td>
					<td>:</td>
					<td>".$refrensi."</td>
				</tr>
				<tr style='vertical-align:top'>
					<td>6.</td>
					<td>Ref. ".($jnsnopo=='PO'?'PR':'SR')."</td>
					<td>:</td>
					<td>".$refpp."</td>
				</tr>
				<tr style='vertical-align:top'>
					<td>7.</td>
					<td>Ref Quotation</td>
					<td>:</td>
					<td>".$norpq."</td>
				</tr>
				<tr style='vertical-align:top'>
					<td>8.</td>
					<td colspan=3>Kindly liase with logistic Officer</td>
				</tr>
				<tr style='vertical-align:top'>
					<td style='font-weight:bold'>9.</td>
					<td style='font-weight:bold'>Delivery To</td>
					<td>:</td>
					<td>".$deliveryto."</td>
				</tr>
				<tr style='vertical-align:top'>
					<td colspan=2>Keterangan</td>
					<td>:</td>
					<td>".$keterangan."</td>
				</tr>
			</table>
		</td>
		<td style='width:3.7px'>&nbsp;</td>
		<td style='width:200px;border:0.5px solid #000000'>
			<table>
				<tr>
					<td style='width:80px;text-align:left'>".$_SESSION['lang']['subtotal']."</td>
					<td style='width:39px;text-align:center'>".$matauang."</td>
					<td style='width:80px;text-align:right'>".number_format($subtotal,2)."</td>
				</tr>
				<tr>
					<td style='width:80px;text-align:left'>Diskon ".(($diskonpersen==''||$diskonpersen==0)?'':'('.hidezerodecimal($diskonpersen).')')."</td>
					<td style='width:39px;text-align:center'>".$matauang."</td>
					<td style='width:80px;text-align:right;border-bottom:0.5px solid #5F5F5F''>".number_format($nilaidiskon,2)."</td>
				</tr>
				<tr>
					<td style='width:80px;text-align:left'>".$_SESSION['lang']['subtotal']."</td>
					<td style='width:39px;text-align:center'>".$matauang."</td>
					<td style='width:80px;text-align:right'>".number_format(($subtotal-$nilaidiskon),2)."</td>
				</tr>
				<tr>
					<td style='width:80px;text-align:left'>PBBKB</td>
					<td style='width:39px;text-align:center'>".$matauang."</td>
					<td style='width:80px;text-align:right'>".number_format($pbbkb,2)."</td>
				</tr>
				<tr>
					<td style='width:80px;text-align:left'>PPn ".(($persenppn==''||$persenppn==0)?'':'('.hidezerodecimal($persenppn).'%)')."</td>
					<td style='width:39px;text-align:center'>".$matauang."</td>
					<td style='width:80px;text-align:right'>".number_format($ppn,2)."</td>
				</tr>
				<tr>
					<td style='width:80px;text-align:left'>Add Cost</td>
					<td style='width:39px;text-align:center'>".$matauang."</td>
					<td style='width:80px;text-align:right'>".number_format($addcost,2)."</td>
				</tr>
				<tr>
					<td colspan=2>&nbsp;</td>
					<td style='border-bottom:0.1px solid #A0A0A0'>&nbsp;</td>
				</tr>
				<tr style='font-weight:bold'>
					<td style='width:80px;text-align:left'>".$_SESSION['lang']['grnd_total']."</td>
					<td style='width:39px;text-align:center'>".$matauang."</td>
					<td style='width:80px;text-align:right;border-bottom:0.5px solid #000000;'>".number_format($grandtotal,2)."</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br>";

## CREATE SIGNATURE
$str="select b.namakaryawan,a.tanggal,c.namajabatan from ".$dbname.".approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan where notransaksi='".$nopo."' order by level desc limit 1";
$res=fetchdata($str);
$namaapp = $res[0]['namakaryawan'];
$jabatanapp = $res[0]['namajabatan'];
$tanggalapp = ($res[0]['tanggal']=='0000-00-00 00:00:00'?'':tglnmblnsec($res[0]['tanggal'],'E',''));

$str="select a.namakaryawan,b.namajabatan from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where a.karyawanid='".$purchaser."'";
$res=fetchdata($str);
$namapurchaser = $res[0]['namakaryawan'];
$jabatanpurchaser = $res[0]['namajabatan'];

$tab.="<table width=709px style='font-size:11px;text-align:center' cellpadding=0 cellspacing=0>
	<tr>
		<td width=50%>Issued by</td>
		<td width=50%>Approved</td>
	</tr>
	<tr>
		<td style='height:50px'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>".strtoupper($namapurchaser)."</td>
		<td>".strtoupper($namaapp)."</td>
	</tr>
	<tr>
		<td>".$jabatanpurchaser."</td>
		<td>".$jabatanapp."</td>
	</tr>
	<tr>
		<td>".$waktucetak."</td>
		<td>".($tanggalapp==''?'':'Release : '.$tanggalapp)."</td>
	</tr>
</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($tab);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

## Print Out
if($urlefil=='0'){
	$dompdf->stream("PrintPOSO_".$column,array("Attachment"=>0));
}else{
	file_put_contents($urlefil, $dompdf->output());
}
?>