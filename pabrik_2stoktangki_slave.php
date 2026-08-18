<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;
$stream='';

$method = checkPostGet('method','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}

switch ($method) {
	
	case'detail':
		
		#= pengiriman
		// print_r($param);
		
		#pengiriman dan mutasi
		// echo"<pre>";
		// print_r($param);
		$pengiriman=$penerimaan=$writeoff=$transferin=$transferout=0;
		$str="select * from ".$dbname.".pabrik_bamutasi where kodetangki='".$param['kodetangki']."' and unit='".$param['unit']."' and tanggalbongkar2 like '".tanggalsystemn($param['tanggal'])."%' and tipe='OUT'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$pengiriman+=$bar['jumlah'];
		}
		
		$str="select * from ".$dbname.".pmn_bapengiriman where kodetangki='".$param['kodetangki']."' and unit='".$param['unit']."' and tanggalmuat2 like '".tanggalsystemn($param['tanggal'])."%' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$pengiriman+=$bar['jumlah'];
		}
		
		$str="select * from ".$dbname.".pabrik_bamutasi where kodetangki='".$param['kodetangki']."' and unit='".$param['unit']."' and tanggal='".tanggalsystemn($param['tanggal'])."'  and tipe='IN'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$penerimaan+=$bar['jumlah'];
		}
		
		$str="select * from ".$dbname.".pabrik_pembersihantangki where kodetangki='".$param['kodetangki']."' and kodeorg='".$param['unit']."' and tanggalba='".tanggalsystemn($param['tanggal'])."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$writeoff+=$bar['jumlah'];
		}
		
		$str="select * from ".$dbname.".pabrik_transferproduk where kodetangki='".$param['kodetangki']."' and unit='".$param['unit']."' and tanggal='".tanggalsystemn($param['tanggal'])."'  and tipe='IN'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$transferin+=$bar['jumlah'];
		}
		
		$str="select * from ".$dbname.".pabrik_transferproduk where kodetangki='".$param['kodetangki']."' and unit='".$param['unit']."' and tanggal='".tanggalsystemn($param['tanggal'])."'  and tipe='OUT'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$transferout+=$bar['jumlah'];
		}
	
		$str="select * from ".$dbname.".pabrik_bakoreksistok where kodetangki='".$param['kodetangki']."' and unit='".$param['unit']."' and tanggal='".tanggalsystemn($param['tanggal'])."'  and tipe='IN'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$adjustin+=$bar['jumlah'];
		}
		
		$str="select * from ".$dbname.".pabrik_bakoreksistok where kodetangki='".$param['kodetangki']."' and unit='".$param['unit']."' and tanggal='".tanggalsystemn($param['tanggal'])."'  and tipe='OUT'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$adjustout+=$bar['jumlah'];
		}
		$stream.=" ".$param['unit']."<br>";
		$stream.=" ".$param['kodetangki']."<br>";
		$stream.=" ".$param['tanggal']."<br><br>";
		$stream.="<table class=sortable cellspacing=1 ".$border." width=100%>";
			$stream.="<thead><tr class=rowheader>";		
				$stream.="<td align=left>".$_SESSION['lang']['keterangan']."</td>";
				$stream.="<td align=left>".$_SESSION['lang']['tipe']."</td>";
				$stream.="<td align=left>".$_SESSION['lang']['sumber']."</td>";
				$stream.="<td align=left>".$_SESSION['lang']['nilai']."</td>";
			$stream.="</tr></thead>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td align=left>".$_SESSION['lang']['produksi']."</td>";
				$stream.="<td align=center>+</td>";
				$stream.="<td align=left>Transaksi Produksi</td>";
				$stream.="<td align=right>".hidezerodecimal(0)."</td>";
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td align=left>".$_SESSION['lang']['pengiriman']."</td>";
				$stream.="<td align=center>-</td>";
				$stream.="<td align=left>Transaksi Berita Acara Mutasi Pengiriman (ke Bulking) dan Berita Acara Pengiriman (Buyer)</td>";
				$stream.="<td align=right>".hidezerodecimal($pengiriman)."</td>";
			$stream.="</tr>";	
			$stream.="<tr class=rowcontent>";		
				$stream.="<td align=left>".$_SESSION['lang']['penerimaan']."</td>";
				$stream.="<td align=center>+</td>";
				$stream.="<td align=left>Transaksi Berita Acara Mutasi Penerimaan</td>";
				$stream.="<td align=right>".hidezerodecimal($penerimaan)."</td>";
			$stream.="</tr>";	
			$stream.="<tr class=rowcontent>";		
				$stream.="<td align=left>Write Off</td>";
				$stream.="<td align=center>+</td>";
				$stream.="<td align=left>Transaksi Berita Acara Pencucian Tangki</td>";
				$stream.="<td align=right>".hidezerodecimal($writeoff)."</td>";
			$stream.="</tr>";	
			$stream.="<tr class=rowcontent>";		
				$stream.="<td align=left>Transfer In</td>";
				$stream.="<td align=center>+</td>";
				$stream.="<td align=left>Transaksi Berita Acara Mutasi Stok Antar Tangki</td>";
				$stream.="<td align=right>".hidezerodecimal($transferin)."</td>";
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td align=left>Transfer Out</td>";
				$stream.="<td align=center>-</td>";
				$stream.="<td align=left>Transaksi Berita Acara Mutasi Stok Antar Tangki</td>";
				$stream.="<td align=right>".hidezerodecimal($transferout)."</td>";
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td align=left>Adjustment</td>";
				$stream.="<td align=center>+</td>";
				$stream.="<td align=left>Transaksi Berita Acara Adjustment Stok</td>";
				$stream.="<td align=right>".hidezerodecimal($adjustin)."</td>";
			$stream.="</tr>";	
			$stream.="<tr class=rowcontent>";		
				$stream.="<td align=left>Adjustment</td>";
				$stream.="<td align=center>-</td>";
				$stream.="<td align=left>Transaksi Berita Acara Adjustment Stok</td>";
				$stream.="<td align=right>".hidezerodecimal($adjustout)."</td>";
			$stream.="</tr>";			
		$stream.="</table>";
		
		// $stream="Masih proses development sistem";
		
		echo $stream;
	break;
	
	
	case'preview':
	
		
		if($param['unit']==''){
			// exit("Warning:Unit tidak boleh kosong");
		}

		if($param['kodebarang']==''){
			exit("Warning:Komoditi tidak boleh kosong");
		}

		if($param['tanggal1']=='' || $param['tanggal2']==''){
			exit("Warning:Tanggal tidak boleh kosong");
		}

		$arrtanggal=rangeTanggalarr(tanggalsystemn($param['tanggal1']),tanggalsystemn($param['tanggal2']));
		$tanggalkemarin=tglkemarin(tanggalsystemn($param['tanggal1']));
		// echo $tanggalkemarin;
		if($param['kodebarang']=='40000002'){
			$str="select * from ".$dbname.".pabrik_5tangki where komoditi='KER' and kodeorg like '".$param['unit']."%' order by kodeorg, kodetangki";
		}else{
			$str="select * from ".$dbname.".pabrik_5tangki where komoditi='CPO' and kodeorg like '".$param['unit']."%' order by kodeorg, kodetangki";
		}
		$res=fetchdata($str);
		foreach($res as $bar){
			$kuncitangki=$bar['kodeorg'].$bar['kodetangki'];
			$arrkodetangki[$kuncitangki]=$kuncitangki;
			$arrkodetangki2[$bar['kodetangki']]=$bar['kodetangki'];
			$nmtangki[$kuncitangki]=$bar['kodeorg'].' '.$bar['keterangan'];
			@$cspantangki+=1;
			$dtkodetangki[$kuncitangki]=$bar['kodetangki'];
		} 


		#= query stok tangki
		$str="select * from ".$dbname.".pabrik_masukkeluartangki where  kodeorg like '".$param['unit']."%' and kodetangki in ('".implode("','",$arrkodetangki2)."') and tanggal between '".tglkemarin(tanggalsystemn($param['tanggal1']))."' and  '".tanggalsystemn($param['tanggal2'])."' order by tanggal ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kuncitangki=$bar['kodeorg'].$bar['kodetangki'];
			if($param['kodebarang']=='40000002'){
				$kgawal[$bar['tanggal']][$kuncitangki]=$bar['kernelquantity'];
				$kgakhir[$bar['tanggal']][$kuncitangki]=$bar['kernelquantity'];
			}else{
				$kgawal[$bar['tanggal']][$kuncitangki]=$bar['kuantitas'];
				$kgakhir[$bar['tanggal']][$kuncitangki]=$bar['kuantitas'];
			}
		} 

			if($param['tipe']=='html'){
				$border='border=0';
			}else{
				$border='border=1';
			}

			$stream.="<table class=sortable cellspacing=1 ".$border." width=100%>";
			$stream.="<thead>";
				$stream.="<tr class=rowheader>";		
					$stream.="<th rowspan=3 align=center>".$_SESSION['lang']['tanggal']."</th>";
					$stream.="<th colspan=".($cspantangki*3)." align=center>".$_SESSION['lang']['tangki']."</th>";
					$stream.="<th rowspan=2 colspan=3 align=center>".$_SESSION['lang']['total']."</th>";
				$stream.="</tr>";
				$stream.="<tr class=rowheader>";
					foreach($arrkodetangki as $dttangki){
						$stream.="<th colspan=3  align=center>".$nmtangki[$dttangki]."</th>";
					}
				$stream.="</tr>";
				$stream.="<tr class=rowheader>";
					foreach($arrkodetangki as $dttangki){
						$stream.="<th align=center>".$_SESSION['lang']['saldoawal']."</th>";
						$stream.="<th align=center>Mutasi Stok</th>";
						$stream.="<th align=center>".$_SESSION['lang']['saldoakhir']."</th>";
					}
					$stream.="<th align=center>".$_SESSION['lang']['saldoawal']."</th>";
					$stream.="<th align=center>Mutasi Stok</th>";
					$stream.="<th align=center>".$_SESSION['lang']['saldoakhir']."</th>";
				$stream.="</tr>";
				$stream.="</thead>";
				$stream.="<tbody>";
				foreach($arrtanggal as $dttgl){
					$stream.="<tr class=rowcontent>";		
						$stream.="<td>".tanggalnormal($dttgl)."</td>";
						foreach($arrkodetangki as $dttangki){
							$view=" style='cursor:pointer' title='Lihat Data Detail'
								onclick=\"detail('".substr($dttangki,0,4)."','".tanggalnormal($dttgl)."','".$dtkodetangki[$dttangki]."','html');\" ";
							$stream.="<td align=right ".$view.">".number_format($kgawal[tglkemarin($dttgl)][$dttangki])."</td>";
								@$kgmutasi[$dttgl][$dttangki]=$kgakhir[$dttgl][$dttangki]-$kgawal[tglkemarin($dttgl)][$dttangki];
							$stream.="<td align=right ".$view.">".number_format($kgmutasi[$dttgl][$dttangki])."</td>";
							$stream.="<td align=right ".$view.">".number_format($kgakhir[$dttgl][$dttangki])."</td>";
							
							@$tkgawal[$dttgl]+=$kgawal[tglkemarin($dttgl)][$dttangki];
							@$tkgmutasi[$dttgl]+=$kgmutasi[$dttgl][$dttangki];
							@$tkgakhir[$dttgl]+=$kgakhir[$dttgl][$dttangki];
						}
						$stream.="<td align=right>".number_format($tkgawal[$dttgl])."</td>";
						$stream.="<td align=right>".number_format($tkgmutasi[$dttgl])."</td>";
						$stream.="<td align=right>".number_format($tkgakhir[$dttgl])."</td>";
					$stream.="</tr>";
				}
			$stream.="</tbody>";
			$stream.="</table>";
	
		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "STOK_".$param['tanggal1']."_sampai_".$param['tanggal2'].".xls";
				$xls = new HtmlExcel();
				$xls->setCss($css);
				$xls->addSheet("data", $stream);
				$xls->headers($nop);
				echo $xls->buildFile();
			break;
			case'pdf':
				$dompdf = new Dompdf();
				$dompdf->loadHtml($stream);
				$dompdf->setPaper('A4', 'landscape');
				$dompdf->render();
				$dompdf->stream("Stok",array("Attachment"=>0));
			break;
		}
	break;
	
	
	
}



?>