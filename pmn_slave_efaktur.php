<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$pt=checkPostGet('pt','');
$tglawal=checkPostGet('tglawal','');
$tglawal=tanggalsystem($tglawal);
$tglakhir=checkPostGet('tglakhir','');
$tglakhir=tanggalsystem($tglakhir);
$result = "";

if($pt==''){
	exit("warning : Kode Organisasi harus dipilih.");
}

if($tglawal > $tglakhir){
	exit("warning : Tanggal awal harus lebih kecil dari tanggal akhir");
}

//get data
$str = "select * from ".$dbname.".pmn_faktur_vw where kodept='".$pt."' and tanggal between '".$tglawal."' and '".$tglakhir."' order by tanggal asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrow=$res->rowCount();

if($numrow <= 0){
	$result = $_SESSION['lang']['datanotfound'];
}else{
	if($method=='preview'){
		$border = 0;
	}else{
		$border = 1;
	}

	$result .= "<table class=sortable cellspacing=1 border='".$border."'>
		<thead>
		<tr class=rowheader>
			<td align=center style='font-weight:bold;'>Invoice No.</td>
			<td align=center style='font-weight:bold;'>Invoice Date</td>
			<td align=center style='font-weight:bold;'>Invoice Inv. Tax No.</td>
			<td align=center style='font-weight:bold;'>Customer NAMA NPWP</td>
			<td align=center style='font-weight:bold;'>Customer PKP No</td>
			<td align=center style='font-weight:bold;'>Customer Tax Address 1</td>
			<td align=center style='font-weight:bold;'>Item No.</td>
			<td align=center style='font-weight:bold;'>Item Description</td>
			<td align=center style='font-weight:bold;'>Invoice Qty.</td>
			<td align=center style='font-weight:bold;'>Unit Price</td>
			<td align=center style='font-weight:bold;'>Bruto</td>
			<td align=center style='font-weight:bold;'>Total Disc</td>
			<td align=center style='font-weight:bold;'>Amount</td>
			<td align=center style='font-weight:bold;'>PPn</td>
		</tr>
		</head>";
		
	$result .= "<tbody id=containerlist>";
	while($bar=$res->fetch()){
		$tgl = substr($bar['tanggal'],8,2);
		$namabulan = numToMonth(substr($bar['tanggal'],5,2),'I','short');
		$tahun = substr($bar['tanggal'],0,4);
		$result.="<tr class=rowcontent>";
		$result.="<td>".$bar['noinvoice']."</td>";
		$result.="<td>".$tgl." ".$namabulan." ".$tahun."</td>";
		$result.="<td>".$bar['nofaktur']."</td>";
		$result.="<td>".$bar['namacustomer']."</td>";
		$result.="<td>".$bar['npwp']."</td>";
		$result.="<td>".$bar['alamatnpwp']."</td>";
		$result.="<td>".$bar['kodebarang']."</td>";
		$result.="<td>".$bar['namabarang']."</td>";
		$result.="<td style='text-align:right'>".number_format($bar['kuantitas'],2)."</td>";
		$result.="<td style='text-align:right'>".number_format(@($bar['nilaiinvoice']/$bar['kuantitas']),2)."</td>";
		$result.="<td style='text-align:right'>".number_format($bar['nilaiinvoice'],2)."</td>";
		$result.="<td style='text-align:right'>0</td>";
		$result.="<td style='text-align:right'>".number_format($bar['nilaiinvoice'],2)."</td>";
		$result.="<td style='text-align:right'>".number_format($bar['nilaippn'],2)."</td>";
		$result.="</tr>";
	}
	$result.="</tbody>
	</table>";
}


switch($method){
	case'preview':
		echo $result;
		break;
		
    case'excel':
		$result.="<br>Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $nop_ = "E-Faktur";
        if (strlen($result) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $result)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
		break;
		
	default:
        break;
}
?>