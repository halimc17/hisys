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


// echo"<pre>";
// print_r($stdtjumlahbulan);


switch ($method) {
	
	case'getunit':
		$optunit="<option value=''>". $_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".organisasi where induk='".$param['kodept']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
		  $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		} 
		echo $optunit;
	break;
	
	
	case'preview':
		
		#= validasi data harus terisi
		if($param['kodept']==''){
			exit("Warningsystem:PT tidak boleh kosong");
		}
		if($param['tanggal1']==''){
			exit("Warningsystem:Tanggal tidak boleh kosong");
		}
		if($param['tanggal2']==''){
			exit("Warningsystem:Tanggal tidak boleh kosong");
		}
		
		#= filter where yang bisa seluruhnya
		$where='';
		
		if($param['kodecustomer']!=''){
			$where.=" and kodecustomer='".$param['kodecustomer']."'";
		}
		if($param['kodeunit']!=''){
			$where.=" and unit='".$param['kodeunit']."'";
		}
		
		
		
		#= array untuk customer
		$str="select * from ".$dbname.".pmn_4customer";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namacustomer[$bar['kodecustomer']]=$bar['namacustomer'];
		}
		
		#= array nama unit
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodept']."' or induk='".$param['kodept']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namaorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
		}

		$stylekolomdt='';
		if($param['tipe']=='html'){
			$stylekolom='border=0 cellspacing=1';
		}else if($param['tipe']=='pdf'){
			$stylekolom='border=0 cellspacing=0 cellpadding=2';
			$stylekolomdt="style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;border-right:0.5px solid #000000;'";
		}else if($param['tipe']=='excel'){
			$stylekolom='border=1 cellspacing=1';
		}
		if($param['tipe']=='pdf'){
			$stream="<style>
				footer .pagenum:before {
				  content: counter(page);
				}

				@page {
					margin-top: 30px;
					margin-left: 20px;
					margin-right: 20px;
					margin-bottom: 40px;
				}
				body {
					font-family: Tahoma, Verdana, Segoe, sans-serif;
					font-size: 9px;
				}
				table.sortable th, table.sortable td {
					font-size: 9px;
					word-wrap: break-word;
				}

				footer {
					position: fixed;
					bottom: -20px;
					left: 0px;
					right: 0px;
					height: 50px;
				}

			</style>";
		}
		
		// $border='border=0';
		$stream.="<table class=sortable ".$stylekolom." width=100%>";
		$stream.="<thead>";
			if($param['tipe']!='html'){
				$stream.="<tr class=rowheader>";	
					$stream.="<th colspan=3 align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$namaorganisasi[$param['kodept']]."</th>";
					$stream.="<th align=center colspan=6 style='font-size:20px;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>Penjualan ".$param['tanggal1']." s/d ".$param['tanggal2']."</th>";
				$stream.="</tr>";	
				$stream.="<tr class=rowheader>";	
					$stream.="<th colspan='3' align=left style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>&nbsp;</th>";
				$stream.="</tr>";	
				$stream.="<tr class=rowheader>";	
					$stream.="<th align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['lokasi']."</th>";
					$stream.="<th colspan='".($param['bulan']+4)."' align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>: ".$namaorganisasi[$param['kodeunit']]."</th>";
				$stream.="</tr>";	
			
				$stream.="<tr class=rowheader>";	
					$stream.="<th align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['customer']."</th>";
					$stream.="<th colspan='".($param['bulan']+4)."' align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>: ".$param['kodecustomer']." - ".$namacustomer[$param['kodecustomer']]."</th>";
				$stream.="</tr>";	
			}
			

			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['notransaksi']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">Tgl SPB</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['nospb']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['noTiket']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['nokendaraan']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['unit']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['nmcust']."</th>";
				$stream.="<th align=center ".$stylekolomdt." width=90>".$_SESSION['lang']['blok']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['berat']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['potongan']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['beratBersih']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['jjg']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['tahuntanam']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['rpkg']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['total']."</th>";
			$stream.="</tr>";	
			
			
			$stream.="</thead>";
			$stream.="<tbody>";
			
			
			#= query ambil data berdasarkan nomor dokumen
			$str="select * from ".$dbname.".kebun_tbsjual where tanggalspb between '".tanggalsystemn($param['tanggal1'])."' and '".tanggalsystemn($param['tanggal2'])."' and unit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."') ".$where." order by tanggalspb asc";
			$arrnodok=array();// dikosongkan arrnodok buat bentuk urutan by tanggal
			$res=fetchdata($str);
			foreach($res as $bar){
				$stream.="<tr class=rowcontent>";		
					$stream.="<td valign=top ".$stylekolomdt.">".$bar['notransaksi']."</td>";
					$stream.="<td valign=top ".$stylekolomdt." align=center>".tanggalnormal($bar['tanggalspb'])."</td>";
					$stream.="<td valign=top ".$stylekolomdt.">".$bar['nospb']."</td>";
					$stream.="<td valign=top ".$stylekolomdt.">".$bar['notiket']."</td>";
					$stream.="<td valign=top ".$stylekolomdt.">".$bar['nokendaraan']."</td>";
					$stream.="<td valign=top ".$stylekolomdt.">".$bar['unit']."</td>";
					$stream.="<td valign=top ".$stylekolomdt.">".$namacustomer[$bar['kodecustomer']]."</td>";
					$stream.="<td valign=top ".$stylekolomdt." width=90>".$bar['blok']."</td>";
					$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($bar['kgbruto'])."</td>";
					$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($bar['kgpotongan'])."</td>";
					$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($bar['kgnetto'])."</td>";
					$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($bar['jjg'])."</td>";
					$stream.="<td valign=top ".$stylekolomdt." align=right>".$bar['tahuntanam']."</td>";
					$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($bar['rpkg'])."</td>";
					$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($bar['totalrp'])."</td>";
				$stream.="</tr>";	
				@$tkgbruto+=$bar['kgbruto'];
				@$tkgpotongan+=$bar['kgpotongan'];
				@$tkgnetto+=$bar['kgnetto'];
				@$tjjg+=$bar['jjg'];
				@$totalrp+=$bar['totalrp'];

				#= akumulasi rekap per tahun tanam
				$tt=$bar['tahuntanam'];
				@$rekapTT[$tt]['kgbruto']+=$bar['kgbruto'];
				@$rekapTT[$tt]['kgpotongan']+=$bar['kgpotongan'];
				@$rekapTT[$tt]['kgnetto']+=$bar['kgnetto'];
				@$rekapTT[$tt]['jjg']+=$bar['jjg'];
				@$rekapTT[$tt]['totalrp']+=$bar['totalrp'];

				#= akumulasi rekap per tanggal spb
				$tglspb=$bar['tanggalspb'];
				@$rekapTGL[$tglspb]['kgbruto']+=$bar['kgbruto'];
				@$rekapTGL[$tglspb]['kgpotongan']+=$bar['kgpotongan'];
				@$rekapTGL[$tglspb]['kgnetto']+=$bar['kgnetto'];
				@$rekapTGL[$tglspb]['jjg']+=$bar['jjg'];
				@$rekapTGL[$tglspb]['totalrp']+=$bar['totalrp'];
			}
		$stream.="<tr class=rowcontent>";
			$stream.="<td valign=top ".$stylekolomdt." colspan=8 align=center>".$_SESSION['lang']['total']."</td>";
			$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($tkgbruto)."</td>";
			$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($tkgpotongan)."</td>";
			$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($tkgnetto)."</td>";
			$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($tjjg)."</td>";
			$stream.="<td valign=top ".$stylekolomdt." align=right></td>";
			$stream.="<td valign=top ".$stylekolomdt." align=right></td>";
			$stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($totalrp)."</td>";
		$stream.="</tr>";
		$stream.="</tbody>";
		$stream.="</table>";

		#= Fungsi kecil untuk bikin 1 tabel rekap (dipakai untuk rekap tahun tanam maupun rekap tanggal)
		#= supaya kedua rekap konsisten strukturnya dan gampang dirawat
		$buatRekap = function($judul, $labelKolomPertama, $dataRekap, $formatTanggal=false) use ($stylekolom, $stylekolomdt) {
			ksort($dataRekap);
			$tbruto = $tpotongan = $tnetto = $tjjg = $ttotalrp = 0;
			$isi='';
			foreach($dataRekap as $key=>$r){
				$labelBaris = $formatTanggal ? tanggalnormal($key) : $key;
				$isi.="<tr class=rowcontent>";
					$isi.="<td valign=top ".$stylekolomdt." align=".($formatTanggal?'center':'right').">".$labelBaris."</td>";
					$isi.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($r['kgbruto'])."</td>";
					$isi.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($r['kgpotongan'])."</td>";
					$isi.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($r['kgnetto'])."</td>";
					$isi.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($r['jjg'])."</td>";
					$isi.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($r['totalrp'])."</td>";
				$isi.="</tr>";
				$tbruto+=$r['kgbruto']; $tpotongan+=$r['kgpotongan']; $tnetto+=$r['kgnetto']; $tjjg+=$r['jjg']; $ttotalrp+=$r['totalrp'];
			}
			$out ="<p><b>".$judul."</b></p>";
			$out.="<table  ".$stylekolom." width=100%>";
			$out.="<thead><tr class=rowheader>";
				$out.="<th align=center ".$stylekolomdt.">".$labelKolomPertama."</th>";
				$out.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['berat']."</th>";
				$out.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['potongan']."</th>";
				$out.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['beratBersih']."</th>";
				$out.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['jjg']."</th>";
				$out.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['total']."</th>";
			$out.="</tr></thead><tbody>";
			$out.=$isi;
			if(!empty($dataRekap)){
				$out.="<tr class=rowcontent style='font-weight:bold;'>";
					$out.="<td valign=top ".$stylekolomdt." align=center>".$_SESSION['lang']['total']."</td>";
					$out.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($tbruto)."</td>";
					$out.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($tpotongan)."</td>";
					$out.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($tnetto)."</td>";
					$out.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($tjjg)."</td>";
					$out.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($ttotalrp)."</td>";
				$out.="</tr>";
			}
			$out.="</tbody></table>";
			return $out;
		};

		#= Ditumpuk (bukan berdampingan) - side-by-side pakai tabel bersarang ternyata berantakan
		#= sekali di export PDF (dompdf tidak sanggup render tabel bersarang dg baik, apalagi kena
		#= page break). Struktur tabel tunggal seperti ini konsisten dan aman untuk HTML/Excel/PDF.
		$stream.="<br>";
		$stream.=$buatRekap("Rekap Per ".$_SESSION['lang']['tahuntanam'], $_SESSION['lang']['tahuntanam'], $rekapTT, false);
		$stream.="<br>";
		$stream.=$buatRekap("Rekap Per Tanggal SPB", "Tgl SPB", $rekapTGL, true);

		#= info siapa yang mencetak/download dan kapan - khusus untuk file yang di-download (pdf/excel),
		#= tidak untuk tampilan preview html biasa
		$namapencetak = $_SESSION['empl']['name'];
		if($namapencetak==''){
			$namapencetak = $_SESSION['standard']['username'];
		}
		$infoCetak = "Dicetak oleh : ".$namapencetak." pada ".date('d-m-Y H:i:s');

		if($param['tipe']=='pdf'){
			$stream.="<p style='font-size:9px;color:#555555;'>".$infoCetak."</p>";
			$stream.="<footer>";
				$stream.="<div class=pagenum-container>Page <span class=pagenum></span></div>";
				$stream.=" ".date('d-m-Y')." ";
			$stream.="</footer>";
		}
		if($param['tipe']=='excel'){
			$stream.="<br>".$infoCetak;
		}

		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "Laporan_Penjualan_TBS_".$param['kodept']."_".$param['tanggal1']."_sd_".$param['tanggal2'].".xls";
				$xls = new HtmlExcel();
				$xls->setCss($css);
				$xls->addSheet("data", $stream);
				$xls->headers($nop);
				echo $xls->buildFile();
			break;
			
			// case'pdf':
				// $dompdf = new Dompdf();
				// $dompdf->loadHtml($stream);
				// $dompdf->setPaper('A3', 'landscape');
				// $dompdf->render();
				// $dompdf->stream("Aging_",array("Attachment"=>0));
			// break;
			
			case'pdf':
				$dompdf = new Dompdf();
				$dompdf->load_html($stream);
				$dompdf->setPaper('A4', 'landscape');
				// $customPaper = array(0,0,2000,1300);
				// $dompdf->set_paper($customPaper,'landscape');
				$dompdf->render();
				$dompdf->stream("Penualan_".$param['kodept']."_",array("Attachment"=>0));
				// $canvas = $dompdf->get_canvas();
				// if (file_exists($filepdf)){
					// unlink($filepdf);
				// // }
				// $filepdf='a.pdf';
				// file_put_contents($filepdf, $dompdf->output());
			break;
		}
	break;
}



?>