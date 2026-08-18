<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method		=checkPostGet('method','');
$pt			=checkPostGet('pt','');
$unit		=checkPostGet('unit','');
$kontraktor	=checkPostGet('kontraktor','');
$notransaksi=checkPostGet('notransaksi','');
$tgl1		=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2		=tanggalsystemn(checkPostGet('tgl2',''));
$tipe		=checkPostGet('tipe','');

$stream="";

switch($method){
	case'preview':
		if($tipe=='excel'){
			$border="border=1";
			$bgColor="background-color:#ccc";
			$stream.="<h2>".getNamaOrg($pt)."</h2>";
			$stream.="<h2>Daftar SPK</h2>";
		}else{
			$border='border=0';
		}
		$sql=selectQuery($dbname,'organisasi','kodeorganisasi',"induk='$pt'");
		$hsl=fetchData($sql);
		foreach ($hsl as $val) {
			$arrunt[$val['kodeorganisasi']]=$val['kodeorganisasi'];
		}

		if($unit == '%%'){
			$whrunt = "and unit in ('".implode("','",$arrunt)."')";
		}else{
			$whrunt = "and unit = '".$unit."'";
		}
		$whrsupp ='';
		if($kontraktor != '%%'){
			$whrsupp = "and koderekanan = '".$kontraktor."'";
		}
		if($notransaksi != ''){
			$whrnot = "and notransaksi like '%".$notransaksi."%'";
		}

		$gtestimasi=$gtrealisasi=$gtppn=$gtpph=0;
		$str="select * from ".$dbname.".lgl_pengajuanspkht 
              where 1=1 ".$whrunt." ".$whrsupp." ".$whrnot." and tanggal between '".$tgl1."' and '".$tgl2."' ";
		$res=fetchData($str);
		
		if(count($res) < 1){
			echo "kosong";
		}else{
			$stream.="<table class=sortable ".$border."  cellspacing=1 cellpading=5 width=100%>";
			$stream.="<thead>";	
			$stream.="<tr class=rowheader>";	 
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['nourut']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['unit']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['notransaksi']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['tanggal']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['divisi']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['kontraktor']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['nilaikontrak']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['ppn']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['pph']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['dari']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['sampai']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>Realisasi</th>";
			$stream.="</tr>";
			$stream.="</thead>";
			
			foreach($res as $b){
				@$no+=1;
				$stream.="<tr class=rowcontent>";	
					$stream.="<td style='border: 0.5 px solid black;' align=center>".$no."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".$b['unit']." - ".getNamaOrg($b['unit'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".$b['notransaksi']."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".tanggalnormal($b['tanggal'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=center>".$b['divisi']." - ".getNamaOrg($b['divisi'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".getNamaSupplier($b['koderekanan'])."</td>";
	
					#ambil total dari pengajuan
					$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$b['notransaksi']."'";
					$res=fetchData($str)[0];
					$ttlestimasi = $res['total'];
					#ambil total dari realisasi
					$str="select sum(jumlahrealisasi) as total from ".$dbname.".log_baspk where notransaksi='".$b['notransaksi']."'";
					$res=fetchData($str)[0];
					$ttlrealisasi = $res['total'];
					#ambil Pajak
					$pph = $ppn =0;
					$ster="select nourut,nilai from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$b['notransaksi']."' and tipe='pajak'";
					$ris=fetchData($ster);
					foreach ($ris as $bi) {
						if(substr($bi['nourut'],0,3) == '212'){
							$pph+=$bi['nilai'];
						}else{
							$ppn+=$bi['nilai'];
						}
					}
	
					$stream.="<td style='border: 0.5 px solid black;' align=right>".($ttlestimasi == 0 || $ttlestimasi == '' ? number_format($ttlrealisasi) : number_format($ttlestimasi))."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=right>".number_format($ppn)."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=right>".number_format($pph)."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".tanggalnormal($b['tanggaldari'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".tanggalnormal($b['tanggalsampai'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=right>".number_format($ttlrealisasi)."</td>";
				$stream.="</tr>";
				$gtestimasi += ($ttlestimasi == 0 || $ttlestimasi == '' ? $ttlrealisasi : $ttlestimasi);
				$gtrealisasi += $ttlrealisasi;
				$gtppn += $ppn;
				$gtpph += $pph;
			}
	
				$stream.="<tr class=rowcontent>";	
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=center colspan=6>".$_SESSION['lang']['grnd_total']."</td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=right>".number_format($gtestimasi)."</td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=right>".number_format($gtppn)."</td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=right>".number_format($gtpph)."</td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=right></td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=right></td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=right>".number_format($gtrealisasi)."</td>";
				$stream.="</tr>";
			$stream.="</table>";
			
			if($tipe=='excel'){
				$tglSkrg=date("YmdHis");
				$nop_="Daftar SPK_".$tgl1."_s.d_".$tgl2;
				$stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];
				if(strlen($stream)>0){
					if ($handle = opendir('tempExcel')) {
						while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != "..") {
								@unlink('tempExcel/'.$file);
							}
						}	
						closedir($handle);
					}
					$handle=fopen("tempExcel/".$nop_.".xls",'w');
					if(!fwrite($handle,$stream)) {
						echo "<script language=javascript1.2>
						parent.window.alert('Can't convert to excel format');
						</script>";
						exit;
					} else {
						echo "<script language=javascript1.2>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
					}
					fclose($handle);
				}     
			} else if($tipe=='pdf'){
				$dompdf = new Dompdf();
				$dompdf->loadHtml($stream);
				$dompdf->setPaper('A4', 'landscape');
				$dompdf->render();
				$dompdf->stream($stream,array("Attachment"=>0));	
			}else{
				echo $stream;
			}
		}
	break;
	
	case 'getunit':
		$optunit="<option value='%%'>".$_SESSION['lang']['all']."</option>";
		$str="SELECT kodeorganisasi,namaorganisasi FROM $dbname.organisasi WHERE LENGTH(kodeorganisasi)='4' AND induk ='$pt' AND kodeorganisasi IN (".getOrgDetail(2).")";
		$res=fetchData($str);
		foreach ($res as $bar) {
			$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
		}
		echo $optunit;
	break;
	
	default:
	break;
}



?>