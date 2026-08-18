<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$periode=checkPostGet('periode','');
$komoditi=checkPostGet('komoditi','');

if($komoditi==''){
	$judul='CPO & PK';
}else if($komoditi=='40000001'){
	$judul='CPO';
}else{
	$judul='PK';
}

if($proses=='excel'){
	$border=1;
	$stream="<table><tr>
			<td>".$_SESSION['lang']['rekap']." DO ".$judul."</td>
			</tr></table>";
}else{
	$border=0;
	$stream='';
}

$stream.="<fieldset style='clear:both;float:left'><legend><b>Print Area</b></legend>
		<div style='overflow:auto;height:350px;width:auto;'>
		<table class=sortable cellspacing=1 border='".$border."'>
		<thead>
		<tr class=rowheader>
			<td align=center>".$_SESSION['lang']['nourut']."</td>
			<td align=center>".$_SESSION['lang']['NoKontrak']."</td>
			<td align=center>".$_SESSION['lang']['nodo']."</td>
			<td align=center width=75px>".$_SESSION['lang']['tanggal']." (Mulai pengiriman)</td>
			<td align=center>".$_SESSION['lang']['kuantitas']."</td>
			<td align=center>".$_SESSION['lang']['kualitas']." (FFA%)</td>
			<td align=center>".$_SESSION['lang']['keterangan']."</td>
		</tr>
		</thead><tbody>";
		if($komoditi!=''){
			$whrd=" and b.kodebarang ='".$komoditi."'";
		}else{
			$whrd=" and b.kodebarang in ('40000001','40000002')";
		}
$nourut=1;
$strList="select a.*,c.namacustomer,d.namabarang,b.kuantitaskontrak,b.tanggalkirim,b.catatanlain, b.ffa, b.dobi, b.mdani, b.moist, b.dirt from ".$dbname.".pmn_suratperintahpengiriman a
		left join ".$dbname.".pmn_kontrakjual b
		on a.nokontrak = b.nokontrak
		left join ".$dbname.".pmn_4customer c
		on b.koderekanan = c.kodecustomer
		left join ".$dbname.".log_5masterbarang d
		on b.kodebarang = d.kodebarang 
		where left(a.tanggaldo,7) = '".substr($periode,0,7)."' ".$whrd." 
		order by a.tanggaldo desc";
		//echo $strList;

$resList=$owlPDO->query($strList) or die(print " Gagal: ".PDOException::getMessage());
$resList->setFetchMode(PDO::FETCH_ASSOC);
while($barList=$resList->fetch())
{

	$stream.="<tr class=rowcontent>
		<td style='text-align:right; vertical-align:top'>".$nourut++."</td>
			<td style='vertical-align:top'>".$barList['nokontrak']."</td>
			<td style='vertical-align:top'>".$barList['nodo']."</td>
			<td style='vertical-align:top'>".tanggalnormal($barList['tanggalkirim'])."</td>
			<td style='vertical-align:top' align=right>".number_format($barList['kuantitaskontrak'],2)."</td>
			<td style='vertical-align:top; text-align:right;'>".$barList['ffa']."</td>
			<td style='vertical-align:top'>".$barList['keterangan']."</td>
		</tr>";
}
$stream.="</tbody>";

switch($proses)
{
    case'preview':
            echo $stream;    
	break;
       
        case 'excel':
            $stream.="</table></fieldset>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_=$_SESSION['lang']['suratperintahpengiriman']."_".$periode."-".date('YmdHis');
             $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
             gzwrite($gztralala, $stream);
             gzclose($gztralala);
             echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls.gz';
                </script>";            
        break;

    default:
        break;
}

?>