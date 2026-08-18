<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$periode=checkPostGet('periode','');
$lokasitugas=checkPostGet('lokasitugas','');
$namakaryawan=checkPostGet('namakaryawan','');

$stream="<table class=sortable cellspacing=1 border=0>
		<thead>
		<tr class=rowheader>
			<td align=center>".$_SESSION['lang']['nourut']."</td>
			<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['uangmuka']."</td>
			<td align=center>".$_SESSION['lang']['tanggal']."</td>
			<td align=center>".$_SESSION['lang']['kodebiaya']."</td>
			<td align=center>".$_SESSION['lang']['jenisbiaya']."</td>
			<td align=center>".$_SESSION['lang']['jumlah']."</td>
			<td style='text-align:center;'>".$_SESSION['lang']['sisa']."</td>
		</tr>
		</thead><tbody>";
		

$strList="select c.namakaryawan,a.notransaksi,b.uangmuka,a.tanggal,a.jenisbiaya,d.keterangan,a.jumlah,b.bytiket,b.sisa from ".$dbname.".sdm_pjdinasdt a
		left join ".$dbname.".sdm_pjdinasht b
		on a.notransaksi=b.notransaksi
		left join ".$dbname.".datakaryawan c
		on b.karyawanid=c.karyawanid
		left join ".$dbname.".sdm_5jenisbiayapjdinas d
		on a.jenisbiaya=d.id
		where b.kodeorg like '%".$lokasitugas."%' and c.namakaryawan like '%".$namakaryawan."%' and month(b.tanggalperjalanan) = '".substr($periode,5,2)."' and year(b.tanggalperjalanan) = '".substr($periode,0,4)."' order by a.notransaksi";
$resList=$owlPDO->query($strList) or die(print " Gagal: ".PDOException::getMessage());
$resList->setFetchMode(PDO::FETCH_ASSOC);  
$oldnotransaksi="";
$nourut=1;
while($barList=$resList->fetch()){
	$newnotransaksi=$barList['notransaksi'];
	if($newnotransaksi==$oldnotransaksi){
		$stream.="<tr class=rowcontent>
			<td colspan='4'></td>
			<td>".tanggalnormal($barList['tanggal'])."</td>
			<td  align=center>".$barList['jenisbiaya']."</td>
			<td>".$barList['keterangan']."</td>
			<td style='text-align:right;'>".number_format($barList['jumlah'],2)."</td>
			<td ></td>
		</tr>";
		// $oldnotransaksi=$newnotransaksi;
	}else{
		if($nourut!=1){
			$stream.="<tr class=rowcontent>
						<td colspan='9'>&nbsp;</td>
					</tr>";
		}
		$stream.="<tr class=rowcontent>
			<td style='text-align:right;'>".$nourut++."</td>
				<td>".$barList['namakaryawan']."</td>
				<td>".$barList['notransaksi']."</td>
				<td style='text-align:right;'>".number_format($barList['uangmuka'],2)."</td>
				<td>".tanggalnormal($barList['tanggal'])."</td>
				<td align=center>".$barList['jenisbiaya']."</td>
				<td>".$barList['keterangan']."</td>
				<td style='text-align:right;'>".number_format($barList['jumlah'],2)."</td>
				<td style='text-align:right;'>".number_format($barList['sisa'],2)."</td>
			</tr>";
		$oldnotransaksi=$newnotransaksi;
		$sumUangmuka+=$barList['uangmuka'];
		$sumTiket+=$barList['bytiket'];
		$sumSisa+=$barList['sisa'];
	}
	$sumJumlah+=$barList['jumlah'];
}
$stream.="<tr class=rowcontent>
			<td colspan='9'>&nbsp;</td>
		</tr>
		<tr class=rowcontent>
		<td colspan='3' style='font-weight:bold; text-align:center;'>".$_SESSION['lang']['total']."</td>
		<td style='font-weight:bold; text-align:right;'>".number_format($sumUangmuka,2)."</td>
		<td colspan='3'></td>
		<td style='font-weight:bold; text-align:right;'>".number_format($sumJumlah,2)."</td>
		<td style='font-weight:bold; text-align:right;'>".number_format($sumSisa,2)."</td>
		</tr>";

$stream.="</tbody>
		</table>";

switch($proses)
{
    case'preview':
            echo $stream;    
	break;
       
        case 'excel':
            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_=$_SESSION['lang']['perjalanandinas']." ".$periode."-".date('YmdHis');
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