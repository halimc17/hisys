<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$type=checkPostGet('type','');
$kdpabrik=checkPostGet('kdpabrik','');
$tglawal1=checkPostGet('tglawal1','');
$tglawal2=checkPostGet('tglawal2','');

switch($proses)
{
	case'preview2':
		$arrRange = rangeTanggal($tglawal,$tglakhir);
		$tglawal1=explode('-',$tglawal1);
		$tglawal2=explode('-',$tglawal2);
		$tglakhir=explode('-',$tglakhir);
		
		$tglawal1 = ($tglawal1[2]."-".$tglawal1[1]."-".$tglawal1[0]);
		$tglawal2 = ($tglawal2[2]."-".$tglawal2[1]."-".$tglawal2[0]);
		$tglakhir = ($tglakhir[2]."-".$tglakhir[1]."-".$tglakhir[0]);
		
		$result="";
		
		$optNamaPabrik = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kdpabrik."'");
		$optNamaProduct = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
		if($type=='html')
		{
			$border = 0;
		}
		else
		{
			$border = 1;
			$result.="<table cellspacing=1 border='0' class=sortable>
				<tr>
					<td colspan=6 style='font-weight:bold;text-align:center'>ERP v Timbangan</td>
				</tr>
				<tr>
					<td colspan=6 style='text-align:center'>Pabrik : ".$optNamaPabrik[$kdpabrik]."</td>
				</tr>
				<tr>
					<td colspan=6 style='text-align:center'>Tanggal : ".tanggalnormal($tglawal1)." s/d ".tanggalnormal($tglawal2)."</td>
				</tr>
			</table>";
		}
		$result.="<div style='overflow:auto';>
			<table cellspacing=1 border='".$border."' class=sortable>
				<thead class=rowheader>
				<tr>
					<td style='text-align:center'>No. DO</td>
					<td tyle='text-align:center'>Nama Supplier</td>
					<td style='text-align:center;min-width:80px;'>Tanggal</td>
					<td style='text-align:center'>TBS RAMP</td>
					<td style='text-align:center'>TBS RAMP diterima di pabrik</td>
					<td style='text-align:center'>Selisih</td>
				</tr>
				</thead>
				<tbody>";
					
		$sPabrik="select sum(beratbersih) as beratbersih,tanggal,ramp from ".$dbname.".pabrik_timbangan_vw where 
		          tanggal between '".$tglawal1."' and '".$tglawal2."' and millcode='".$kdpabrik."' and ramp!='' group by tanggal,ramp";
		 $rPabrik=fetchData($sPabrik);
		 foreach($rPabrik as $row=>$lsData){
		 	$dtTgl[$lsData['tanggal']]=$lsData['tanggal'];
		 	$dtRamp[$lsData['ramp']]=$lsData['ramp'];
		 	$dtBrtPabrik[$lsData['tanggal'].$lsData['ramp']]=$lsData['beratbersih'];
		 }

		$str2 = "select sum(netto) as netto,koderamp,left(dateout,10) as tanggal from ".$dbname.".pmn_penerimaantbsramp 
		         where unit='".$kdpabrik."' and left(dateout,10)  between '".$tglawal1."' and '".$tglawal2."' group by left(dateout,10),koderamp";					          			
        $resData=fetchData($str2);
        foreach ($resData as $key => $lsData) {
        	$dtTgl[$lsData['tanggal']]=$lsData['tanggal'];
		 	$dtRamp[$lsData['koderamp']]=$lsData['koderamp'];
		 	$dtBrtRamp[$lsData['tanggal'].$lsData['koderamp']]=$lsData['netto'];
        }

		foreach ($dtTgl as $keytgl) {
			foreach ($dtRamp as $keyramp) {
				$optsup = makeOption($dbname,'log_5klsupplier','kode,kelompok',"kode='".$keyramp."'");
				$optnodo = makeOption($dbname,'log_5klsupplier','kode,nodo',"kode='".$keyramp."'");
				$result.="<tr class=rowcontent>
				<td>".$optnodo[$keyramp]."</td>
				<td>".$optsup[$keyramp]."</td>
				<td style='text-align:center'>".$keytgl."</td>
				<td style='text-align:right'>".number_format($dtBrtRamp[$keytgl.$keyramp])."</td>
				<td style='text-align:right'>".number_format($dtBrtPabrik[$keytgl.$keyramp])."</td>";
				$selisih = ($dtBrtRamp[$keytgl.$keyramp]) - ($dtBrtPabrik[$keytgl.$keyramp]) ;
				$result.="<td style='text-align:right'>".number_format($selisih)."</td>";
				@$totramp+=$dtBrtRamp[$keytgl.$keyramp];
				@$totramppab+=$dtBrtPabrik[$keytgl.$keyramp];
				@$totselisih+=$selisih;
			}
		}
				$result.="</tr><tr class=rowcontent>
				<td style='text-align:right' colspan='3'><b>Total</b></td>
				<td style='text-align:right' ><b>".number_format($totramp)."</b></td>
				<td style='text-align:right'><b>".number_format($totramppab)."</b></td>";
				$result.="<td style='text-align:right'><b>".number_format($totselisih)."</b></td></tr>";
		// $optInduk = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$kdpabrik."'");
		// // $str = "select * from ".$dbname.".log_5klsupplier where tipe='RAMP' and kode like '%".$optInduk[$kdpabrik]."%'";
		// // $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// // $res->setFetchMode(PDO::FETCH_ASSOC); and (left(c.datein,10) between '".$tglawal1."' and '".$tglawal2."')
		// $str = "select a.*, b.beratbersih, c.netto  from (".$dbname.".log_5klsupplier a left join ".$dbname.".pabrik_timbangan b on a.kode=b.ramp) 
		// 		left join ".$dbname.".pmn_penerimaantbsramp c on a.kode=c.koderamp where a.tipe='RAMP' and a.kode like '%".$optInduk[$kdpabrik]."%' and 
		// 		(left(b.tanggal,10) between '".$tglawal1."' and '".$tglawal2."') and (left(c.datein,10) between '".$tglawal1."' and '".$tglawal2."')";
		// echo $str;
		// exit();
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch())
		// {
		// 	$str2 = "select sum(beratbersih) as netto from ".$dbname.".pabrik_timbangan where ramp='".$bar['kode']."' and (tanggal between '".$tglawal1."' and '".$tglawal2."')";
		// 	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res2->setFetchMode(PDO::FETCH_ASSOC);
		// 	$bar2=$res2->fetch();
		// 	$beratpmks = $bar2['netto'];
			
		// 	$str2 = "select sum(netto) as netto from ".$dbname.".pmn_penerimaantbsramp where koderamp='".$bar['kode']."' and (datein  between '".$tglawal1."' and '".$tglawal2."')";
		// 	$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res2->setFetchMode(PDO::FETCH_ASSOC);
		// 	$bar2=$res2->fetch();
		// 	$beraterp = $bar2['netto'];
			
		// 	$selisih = $beratpmks - $beraterp;
			
		// 	$result.="<tr class=rowcontent>
		// 		<td>".$bar['nodo']."</td>
		// 		<td>".$bar['kelompok']."</td>
		// 		<td style='text-align:center'>".tanggalnormal($tglawal1)." s/d ".tanggalnormal($tglawal2)."</td>
		// 		<td style='text-align:right'>".number_format($beratpmks)."</td>
		// 		<td style='text-align:right'>".number_format($beraterp)."</td>
		// 		<td style='text-align:right'>".number_format($selisih)."</td>
		// 	";
		// }
		
		if($type=='html')
		{
			echo $result;
		}
		else
		{
			$result.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			$nop_="Rekap_ERP_v_Timbangan";
			if(strlen($result)>0)
			{
				if ($handle = opendir('tempExcel')) 
				{
					while (false !== ($file = readdir($handle))) 
					{
						if ($file != "." && $file != ".." && $file != "index.html") 
						{
							@unlink('tempExcel/'.$file);
						}
					}
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$result))
				{
					echo "<script language=javascript1.2>
						parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}
				else
				{
					echo "<script language=javascript1.2>
						window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
				fclose($handle);
			}
		}
	break;
}
?>