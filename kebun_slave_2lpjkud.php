<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$periode=checkPostGet('periode','');
$namakud=checkPostGet('namakud','');

$nmSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');

$strList="select * from ".$dbname.".kebun_lpjkud 
		where periode like '%".$periode."%' and namakud like '%".$namakud."%' 
		order by periode desc";
$resList=$owlPDO->query($strList) or die(print " Gagal: ".PDOException::getMessage());
$resList->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($resList);
if($numrows <= 0){
	$stream=$_SESSION['lang']['datanotfound'];
}else{

	if($proses=='excel'){
		$border=1;
	}else{
		$border=0;
	}

	$stream="<table class=sortable cellspacing=1 border='".$border."'>
			<thead>";
	if($proses=='excel'){
		$stream.="<tr>
			<td colspan=8><b>Laporan PertanggungJawaban KUD</b></td>
		</tr>";
	}
	$stream.="<tr class=rowheader>
				<td style='text-align:center;'>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>".$_SESSION['lang']['namakud']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['upah']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['material']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['transport']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['lain']."</td>
				<td style='text-align:center;'>".$_SESSION['lang']['total']."</td>
			</tr>
			</thead><tbody>";
			


	$nourut=1;
	while($barList=$resList->fetch()){
		$stream.="<tr class=rowcontent>
			<td style='text-align:right;'>".$nourut++."</td>
				<td>".$barList['periode']."</td>
				<td>".$nmSup[$barList['namakud']]."</td>
				<td style='text-align:right;'>".number_format($barList['upah'],2)."</td>
				<td style='text-align:right;'>".number_format($barList['material'],2)."</td>
				<td style='text-align:right;'>".number_format($barList['transport'],2)."</td>
				<td style='text-align:right;'>".number_format($barList['lainnya'],2)."</td>
				<td style='text-align:right;'>".number_format(($barList['upah']+$barList['material']+$barList['transport']+$barList['lainnya']),2)."</td>
			</tr>";
	}
	$stream.="</tbody>";
}

switch($proses)
{
    case'preview':
            echo $stream;    
	break;
       
        case 'excel':
            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_=$_SESSION['lang']['laporan']."_LPJ_KUD_".$periode."-".date('YmdHis');
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