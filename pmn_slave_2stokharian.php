<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$proses = checkPostGet('proses', '');
$tanggaldari = checkPostGet('tanggaldari', '');
$tanggalsampai = checkPostGet('tanggalsampai', '');
$unit = checkPostGet('unit', '');

$nmtangki=  makeOption($dbname, 'pabrik_5tangki', 'kodetangki,keterangan');

if ($proses == 'excel' || $proses == 'pdf') 
{
    $border = 1;
    $cellspace = 0;
    $head = '';
} 
else 
{
    $border = 0;
    $cellspace = 1;
    $head = 'hidden';
}

$stream .= "
<table class=sortable border=0 ".$head.">
<tr class=rowheader>
<td style=font-size:20px><b>Daily Sounding - Stok Report</b></td>
</tr>
<tr>
<td>Date : ".tglnmbln(date("y-m-d"),'','1')."</td>
</tr>
</table>";

$stream .= "<table class=sortable cellspacing=".$cellspace." cellpadding=2 border=" . $border . ">


    <thead>
	<tr class=rowheader style=background-color:#d5d7da;>
		<td align=center >NO</td>
        <td align=center >TANGKI</td>
        <td align=center >SOUNDING <br> (CM)</td>
        <td align=center >TABLE MEJA UKUR <br> (CM)</td>    
        <td align=center >SOUNDING KOREKSI <br> (CM)</td>
        <td align=center >VOLUME PERHITUNGAN</td>
        <td align=center >KOREKSI FAKTOR SUHU <br> (C-DEG)</td>
        <td align=center >BERAT JENIS</td>
        <td align=center >STOK AKHIR<br>(Kg)</td>
	</tr>
	</thead>
	<tbody>";

$strList = "select * from ".$dbnmae.". pabrik_masukkeluartangki where kodeorg = '".$unit."' 
and tanggal = '".tanggalsystemn($tanggaldari)."' ";
// and tanggal between '".tanggalsystemn($tanggaldari)."' and '".tanggalsystemn($tanggalsampai)."' ";
$resList = $owlPDO->query($strList) or die(print " Gagal: " . PDOException::getMessage());
$resList->setFetchMode(PDO::FETCH_ASSOC);
while ($barList = $resList->fetch()) 
{
    $tinggi[$barList['kodetangki']]=$barList['tinggi'];
}

////////////
$str1="select nilai,tangki from ".$dbname.".pabrik_5mejaukur where 
        kodeorg='".$unit."'";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_ASSOC);
while ($bar1 = $res1->fetch()) 
{
    $mejaukur[$bar1['tangki']]=$bar1['nilai'];
}

$str1="select kodetangki,tinggi1 from ".$dbname.".pmn_bapengiriman where 
        unit='".$unit."'";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_ASSOC);
while ($bar1 = $res1->fetch()) 
{
    $tinggi1[$bar1['kodetangki']]=$bar1['tinggi1'];



   $sTng="select kodetangki,volume,beda from ".$dbname.".pabrik_5tinggitangki where millcode='".$unit."' 
          and tinggi='".$bar1['tinggi1']."'";
    $qTng=$owlPDO->query($sTng) or die(print " Gagal: ".PDOException::getMessage());
    $qTng->setFetchMode(PDO::FETCH_ASSOC);
    $rTng=$qTng->fetch();

    $vol[$rTng['kodetangki']]+=$rTng['volume'];
    $beda[$rTng['kodetangki']]+=$rTng['beda'];

    @$volTing[$rTng['kodetangki']]=$vol[$rTng['kodetangki']]+round((floatval("0.".$bar1['tinggi1'])*$beda[$rTng['kodetangki']]));


}

$strfk="select nilai,nilaiangka, kodetangki from ".$dbname.".pabrik_5faktorkoreksi where millcode='".$unit."'   ";
$resfk=$owlPDO->query($strfk) or die(print " Gagal: ".PDOException::getMessage());
$resfk->setFetchMode(PDO::FETCH_ASSOC);
while ($barfk = $resfk->fetch()) 
{
    $nilaikoreksi[$barfk['kodetangki']]=$barfk['nilai'];
}

$sSh="select berat_jenis,varian,kodetangki from ".$dbname.".pabrik_5suhu where millcode='".$unit."'   ";
$ressh=$owlPDO->query($sSh) or die(print " Gagal: ".PDOException::getMessage());
$ressh->setFetchMode(PDO::FETCH_ASSOC);
while ($barsh = $ressh->fetch()) 
{
    $brtjns[$barsh['kodetangki']]=$barsh['berat_jenis'];
}

$sStok="select kodetangki, kuantitas,kernelquantity from ".$dbname.".pabrik_masukkeluartangki 
	where kodeorg='".$unit."' and tanggal = '".tanggalsystemn($tanggaldari)."'   ";
$resstok=$owlPDO->query($sStok) or die(print " Gagal: ".PDOException::getMessage());
$resstok->setFetchMode(PDO::FETCH_ASSOC);
while ($barstok = $resstok->fetch()) {
    if ($barstok['kuantitas'] != 0) {
        $stok[$barstok['kodetangki']]+=$barstok['kuantitas'];
    }else{
        $stok[$barstok['kodetangki']]+=$barstok['kernelquantity'];
    }
}


// echo "<pre>";
// print_r($stok);
// echo "</pre>";








///////////////


$strList = "select * from ".$dbnmae.". pabrik_5tangki where kodeorg = '".$unit."' ";
$resList = $owlPDO->query($strList) or die(print " Gagal: " . PDOException::getMessage());
$resList->setFetchMode(PDO::FETCH_ASSOC);
while ($barList = $resList->fetch()) 
{
    $tangki[$barList['kodetangki']]=$barList['kodetangki'];
}


    foreach ($tangki as $valtangki) {
	$no+=1;
	$stream.="<tr class=rowcontent>
		<td style='text-align:center;'>".$no."</td>
        <td width=200px>".$nmtangki[$valtangki]."</td>
        <td align=right>".number_format($tinggi[$valtangki])."</td>
        <td align=right>".number_format($mejaukur[$valtangki])."</td>
        <td align=right>".number_format($tinggi1[$valtangki])."</td>
        <td align=right>".number_format($volTing[$valtangki])."</td>
        <td align=right>".$nilaikoreksi[$valtangki]."</td>
        <td align=right>".$brtjns[$valtangki]."</td>
        <td align=right>".number_format($stok[$valtangki])."</td>
        

        ";
    $stream.="</tr>";
    }

$stream.="</tbody></table>";


$stream .= "
<table class=sortable border=0 ".$head.">
<tr><td>&nbsp;</td></tr>
</table>
";

$stream .= "
<table class=sortable border=0 ".$head." width=100%>
<tr>
<td align=center>Approved by,</td>
<td align=center>Acknowledge by,</td>
<td align=center>PK Checked by,</td>
<td align=center>CPO Sounding by,</td>
</tr>
<tr>
<td align=center height=80px>&nbsp;</td>
<td align=center>&nbsp;</td>
<td align=center>&nbsp;</td>
<td align=center>&nbsp;</td>
</tr>
<tr>
<td align=center><hr style=margin-left:50px;margin-right:50px;></td>
<td align=center><hr style=margin-left:50px;margin-right:50px;></td>
<td align=center><hr style=margin-left:50px;margin-right:50px;></td>
<td align=center><hr style=margin-left:50px;margin-right:50px;></td>

</tr>
<tr>
<td align=center>Branch Manager</td>
<td align=center>Ai Suan</td>
<td align=center>Matius</td>
<td align=center>Heldi. S</td>
</tr>
</table>";



switch ($proses) 
{
    case'preview':
        // if ($tanggaldari == '' || $tanggalsampai == '') {
        if ($tanggaldari == '') {
            echo 'Gagal, Periksa kembali tanggal.';
        } else {
            // if (tanggalsystem($tanggalsampai) < tanggalsystem($tanggaldari)) {
                // echo 'Gagal, Periksa kembali periode tanggal.';
            // } else {
                echo $stream;
            // }
        }
        break;

    case 'excel':
       $nop_="LAPORAN_STOK_".date('m-d-Y');
            header("Cache-Control: must-revalidate");
            header("Pragma: must-revalidate");
            header("Content-type: application/vnd.ms-excel");
            header("Content-disposition: attachment; filename=".$nop_.".xls");
            echo $stream;
        break;

        case 'pdf':
            $dompdf = new Dompdf();
            $dompdf->loadHtml($stream);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("Laporan_Stok_Harian",array("Attachment"=>0));
        break;

    default:
        break;
}
?>