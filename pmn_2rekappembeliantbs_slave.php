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

$tahunawal=substr(tanggalsystemn($param['tanggalmulai']),0,4);
$str = "SELECT * FROM ".$dbname.".organisasi WHERE length(kodeorganisasi)=4";
$res = fetchdata($str);
foreach ($res as $bar) {
	$namaorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

switch ($method) {
	/*
	case 'gettipe':
		$opttipetbs = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "SELECT DISTINCT tipetbs FROM ".$dbname.".pmn_5feetbs WHERE kodeunit = '".$param['kodeunit']."' ORDER BY tipetbs ASC";
		// echo $str;exit("Error:A");
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $opttipetbs .= "<option value='" . $bar['tipetbs'] . "'>" . $bar['tipetbs'] . "</option>";
        }
        echo $opttipetbs;
	break;
	*/
	
	case'preview':
	
		$where='';
	
		if($param['kodeunit']==''){
			exit("Warning:Unit tidak boleh kosong");
		}
	
		if($param['tanggalmulai']==''){
			exit("Warning:PT tidak boleh kosong");
		}

		if($param['tanggalsampai']==''){
			exit("Warning:tanggalsampai tidak boleh kosong");
		}
		
		
		
		if ($param['tipetbs'] == 'SUPPLIERTBSAFI') {
			$table = 'kebun_tbsafiliasi';
			$where=" and pemilik='".$param['kodeunit']."'";
			$judullaporan='DAFTAR TAGIHAN PEMBAYARAN TBS AFILIASI<BR>'.$namaorganisasi[$param['kodeunit']].'';
		} else if ($param['tipetbs'] == 'SUPPLIERTBSEXT') {
			$table = 'kebun_tbsexternal';
			$where=" and unit='".$param['kodeunit']."'";
			$judullaporan='DAFTAR TAGIHAN PEMBAYARAN TBS EXTERNAL SWADAYA<BR>'.$namaorganisasi[$param['kodeunit']].'';
		} else if ($param['tipetbs'] == 'SUPPLIERTBSKUD') {
			$table = 'kebun_tbskud';
			$where=" and pemilik='".$param['kodeunit']."'";
			$judullaporan='DAFTAR TAGIHAN PEMBAYARAN TBS PETANI KUD<BR>'.$namaorganisasi[$param['kodeunit']].'';
		} else if ($param['tipetbs'] == 'SUPPLIERTBSINT') {
			$table = 'kebun_tbsinternal';
			$where=" and divisi='".$param['kodeunit']."'";
			$judullaporan='DAFTAR TAGIHAN PEMBAYARAN TBS INTERNAL<BR>'.$namaorganisasi[$param['kodeunit']].'';
		} 
		
		#= jika external pakai unit= ; jika afiliasi dan kud pakai pemilik
		

		#= tbs
		$str="select sum(kgnetto) as kgnetto,sum(totalrp) as totalrp,supplier,tahuntanam,rpkg from ".$dbname.".".$table." where 1=1 ".$where." and tanggaltbs1>='".tanggalsystemn($param['tanggalmulai'])."' and tanggaltbs2<='".tanggalsystemn($param['tanggalsampai'])."' group by supplier,tahuntanam";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrkodesupplier[$bar['supplier']]=$bar['supplier'];
			@$dtkgnetto[$bar['supplier']]+=$bar['kgnetto'];
			@$dtrptbs[$bar['supplier']]+=$bar['totalrp'];
			@$dttahuntanam[$bar['supplier']].=$bar['tahuntanam'].'<br>';
			@$dtumur[$bar['supplier']].=$tahunawal-$bar['tahuntanam'].'<br>';
			@$dtrpkg[$bar['supplier']]=$bar['rpkg'];
		} 
		
		#= coa piutang
		$colspanpotongan=0;
		$str="select * from ".$dbname.".keu_5akun where 1=1 and noakun like '11603%' and detail=1";//group by kodesupplier
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnoakun[$bar['noakun']]=$bar['noakun'];
			$dtnamaakun[$bar['noakun']]=$bar['namaakun'];
			$colspanpotongan++;
		} 
		
		$str="select * from ".$dbname.".keu_jurnaldt_vw where 1=1 and noakun like '11603%' and kodesupplier in ('".implode("','",$arrkodesupplier)."') and tanggal>='".tanggalsystemn($param['tanggalmulai'])."' and tanggal<='".tanggalsystemn($param['tanggalsampai'])."' ";//group by kodesupplier
		$res=fetchdata($str);
		foreach($res as $bar){
			@$dtrppotongan[$bar['kodesupplier']][$bar['noakun']]+=$bar['jumlah'];
		} 
		
		
		
		#= nama supplier
		$str="select * from ".$dbname.".log_5supplier where  supplierid in ('".implode("','",$arrkodesupplier)."')";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namasupplier[$bar['supplierid']]=$bar['namasupplier'];
		}
		
		

		if($param['tipe']=='html'){
			$stylekolom='border=0 cellspacing=1';
		}else if($param['tipe']=='pdf'){
			$stylekolom='border=1 cellspacing=0';
			
			$stream.="<table class=sortable  width=100% border=0>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6><b>".$judullaporan."<b></th>";
			$stream.="</tr>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6><b>PERIODE ".$param['tanggalmulai']." s/d ".$param['tanggalsampai']."<b></th>";
			$stream.="</tr>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6>&nbsp;</th>";
			$stream.="</tr>";
			$stream.="</table>";
			
		}else if($param['tipe']=='excel'){
			$stylekolom='border=1 cellspacing=1';
			
			$stream.="<table class=sortable  width=100% border=0>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6><b>".$judullaporan."<b></th>";
			$stream.="</tr>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6><b>PERIODE ".$param['tanggalmulai']." s/d ".$parram['tanggalsampai']."<b></th>";
			$stream.="</tr>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6>&nbsp;</th>";
			$stream.="</tr>";
			$stream.="</table>";
		}
		
		/*
		$stream.="<th align=center style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['nodok']."</th>";
		*/
		// $border='border=0';
		$stream.="<table class=sortable ".$stylekolom." width=100% style='font-size:12px;'>";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center valign=top rowspan=2>".$_SESSION['lang']['nourut']."</th>";
				$stream.="<th align=center valign=top rowspan=2>".$_SESSION['lang']['supplier']."</th>";
				$stream.="<th align=center valign=top rowspan=2>".$_SESSION['lang']['tahuntanam']."</th>";
				$stream.="<th align=center valign=top rowspan=2>".$_SESSION['lang']['umur']."</th>";
				$stream.="<th align=center valign=top rowspan=2>".$_SESSION['lang']['netto']." (Kg)</th>";
				$stream.="<th align=center valign=top rowspan=2>".$_SESSION['lang']['rpkg']."</th>";
				$stream.="<th align=center valign=top rowspan=2>".$_SESSION['lang']['total']." (Rp)</th>";
				$stream.="<th align=center valign=top colspan='".($colspanpotongan+1)."'>".$_SESSION['lang']['potongan']."</th>";
				$stream.="<th align=center valign=top rowspan=2>".$_SESSION['lang']['total']." ".$_SESSION['lang']['bersih']." (Rp)</th>";
			$stream.="</tr>";
			$stream.="<tr class=rowheader>";		
				foreach($arrnoakun as $dtnoakun){
					$stream.="<th align=center valign=top >".str_replace('FA RECEIV -','',$dtnamaakun[$dtnoakun])."</th>";
				}
				$stream.="<th align=center valign=top>".$_SESSION['lang']['total']." (Rp)</th>";
			$stream.="</tr>";
			$stream.="</thead>";
			$stream.="<tbody>";
			$no=0;
			foreach($arrkodesupplier as $dtkodesupplier){
				$no++;
				$stream.="<tr class=rowcontent>";		
					$stream.="<td align=center valign=top>".$no."</td>";
					$stream.="<td valign=top>".$namasupplier[$dtkodesupplier]."</td>";
					$stream.="<td valign=top align=center>".$dttahuntanam[$dtkodesupplier]."</td>";
					$stream.="<td valign=top align=center>".$dtumur[$dtkodesupplier]."</td>";
					$stream.="<td align=right valign=top>".hidezerodecimal($dtkgnetto[$dtkodesupplier])."</td>";
					$stream.="<td align=right valign=top>".hidezerodecimal($dtrpkg[$dtkodesupplier])."</td>";
					$stream.="<td align=right valign=top>".hidezerodecimal($dtrptbs[$dtkodesupplier])."</td>";
					foreach($arrnoakun as $dtnoakun){
						$stream.="<td align=center valign=top>".hidezerodecimal(abs($dtrppotongan[$dtkodesupplier][$dtnoakun]),2)."</td>";
						@$tdtrppotongan[$dtkodesupplier]+=$dtrppotongan[$dtkodesupplier][$dtnoakun];
						@$stdtrppotongan[$dtnoakun]+=$dtrppotongan[$dtkodesupplier][$dtnoakun];
					}
					$stream.="<td align=center valign=top>".hidezerodecimal(abs($tdtrppotongan[$dtkodesupplier]),2)."</td>";
					
					$trpbersih[$dtkodesupplier]=$dtrptbs[$dtkodesupplier]+$tdtrppotongan[$dtkodesupplier];
					$stream.="<td align=center valign=top>".hidezerodecimal($trpbersih[$dtkodesupplier],2)."</td>";
				$stream.="</tr>";
				@$stdtkgnetto+=$dtkgnetto[$dtkodesupplier];
				@$stdtrptbs+=$dtrptbs[$dtkodesupplier];
			}
			$stream.="<tr class=rowcontent>";	
				for($i=0;$i<=($colspanpotongan+1+7);$i++){
					$stream.="<td></td>";
				}
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td align=right><b>".hidezerodecimal($stdtkgnetto)."</b></td>";
				$stream.="<td></td>";
				$stream.="<td align=right><b>".hidezerodecimal($stdtrptbs)."</b></td>";
				foreach($arrnoakun as $dtnoakun){
					$stream.="<td align=center valign=top><b>".hidezerodecimal(abs($stdtrppotongan[$dtnoakun]),2)."</b></td>";
					@$tstdtrppotongan+=$stdtrppotongan[$dtnoakun];
				}
				$stream.="<td align=center valign=top><b>".hidezerodecimal(abs($tstdtrppotongan),2)."</b></td>";
				$ttrpbersih=$stdtrptbs+$tstdtrppotongan;
				$stream.="<td align=center valign=top><b>".hidezerodecimal(abs($ttrpbersih),2)."</b></td>";
			$stream.="</tr>";
		$stream.="</tbody>";
		$stream.="</table>";
		
		// echo $param['tipe'];exit();
		if ($param['tipetbs'] != 'SUPPLIERTBSINT') {
			$stream.=getketeranganttd('pmn_2rekappembeliantbs',$param['tipe'],$param['kodeunit']);	
		}
	
		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "Rekap_PembelianTBS_".$param['kodeunit']."_".$param['tanggalmulai']."_".$param['tanggalsampai'].".xls";
				$xls = new HtmlExcel();
				$xls->setCss($css);
				$xls->addSheet("data", $stream);
				$xls->headers($nop);
				echo $xls->buildFile();
			break;
			// break;
			case'pdf':
			$dompdf = new Dompdf();
			$dompdf->load_html($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("Rekap_PembelianTBS_".$param['kodeunit']."_".$param['tanggalmulai']."_".$param['tanggalsampai']."_",array("Attachment"=>0));
			break;
		}
	break;
	
}



?>