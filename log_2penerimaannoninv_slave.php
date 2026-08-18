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

if($param['unit']==''){
	exit("Warning:Unit tidak boleh kosong");
}

if($param['posting']!=''){
	$where.=" and posting='".$param['posting']."'";
}
if($param['tipe']!=''){
	$where.=" and tipe='".$param['tipe']."'";
}

if($param['nopo']!=''){
	$where.=" and nopo like '%".$param['nopo']."%'";
}

if($param['tanggal1']=='' || $param['tanggal2']==''){
	exit("Warning:Tanggal tidak boleh kosong");
}

$arrposting=array("0"=>"Belum Posting","1"=>"Posting");
$path   = "fileupload/log_penerimaanx/";
$emodul = "GRN";

if($param['tipelaporan']=='html'){
	$border='border=0';
}else{
	$border='border=1';
}
	$stream.="<table class=sortable cellspacing=1 ".$border." width=100%>";
	$stream.="<thead>";
		$stream.="<tr class=rowheader>";	
			$stream.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['nopo']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['tipe']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['supplier']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['kodebarang']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['kuantitas']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['satuan']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['hargasatuan']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['total']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['subunit']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['subunit']." ".$_SESSION['lang']['detail']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['noakun']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['kodekegiatan']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['posting']."</th>";
			$stream.="<th align=center colspan=2>".$_SESSION['lang']['print']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['file']."</th>";
		$stream.="</tr>";
		$stream.="</thead>";
		$stream.="<tbody>";
		$str="select * from ".$dbname.".log_noninventorydt_vw where  unit='".$param['unit']."' and tanggal between '".tglkemarin(tanggalsystemn($param['tanggal1']))."' and  '".tanggalsystemn($param['tanggal2'])."' ".$where." ";
		$res=fetchdata($str);
		foreach($res as $bar){
			
			#= query supplier
			$strdt="select * from ".$dbname.".log_5supplier where supplierid='".$bar['supplierid']."'";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namasupplier=$bardt['namasupplier'];
			}
			
			#= query kodebarang
			$strdt="select * from ".$dbname.".log_5masterbarang where kodebarang='".$bar['kodebarang']."'";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namabarang=$bardt['namabarang'];
			}
			
			#= query kodebarang
			if($bar['subunit']!='PROJECT'){
				$strdt="select * from ".$dbname.".organisasi where kodeorganisasi in ('".$bar['subunit']."','".$bar['subunitdt']."')";
				$resdt=fetchdata($strdt);
				foreach($resdt as $bardt){
					$namaorganisasi[$bardt['kodeorganisasi']]=$bardt['namaorganisasi'];
				}
				
				$strdt="select * from ".$dbname.".vhc_5master where kodevhc='".$bar['subunitdt']."'";
				$resdt=fetchdata($strdt);
				foreach($resdt as $bardt){
					$namaorganisasi[$bardt['kodevhc']]=$bardt['detailvhc'];
				}
			}else{
				$strdt="select * from ".$dbname.".project where kode='".$bar['subunitdt']."'";
				$resdt=fetchdata($strdt);
				foreach($resdt as $bardt){
					$namaorganisasi[$bardt['kode']]=$bardt['nama'];
				}
			}
			
			
			#= query coa
			$strdt="select * from ".$dbname.".keu_5akun where noakun='".$bar['noakun']."'";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namaakun=$bardt['namaakun'];
			}
			
			#= query coa
			$strdt="select * from ".$dbname.".setup_kegiatan where kodekegiatan='".$bar['kodekegiatan']."'";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namakegiatan=$bardt['namakegiatan'];
			}
			
			$stream.="<tr class=rowcontent>";		
				$stream.="<td align=left>".$bar['notransaksi']."</td>";
				if($param['tipelaporan']=='html'){
					$stream.="<td align=left>".tanggalnormal($bar['tanggal'])."</td>";
				}else{
					$stream.="<td align=left>".$bar['tanggal']."</td>";
				}
				$stream.="<td align=left>".$bar['nopo']."</td>";
				$stream.="<td align=left>".$bar['tipe']."</td>";
				$stream.="<td align=left>".$bar['supplierid']." - ".$namasupplier."</td>";
				$stream.="<td align=left>".$bar['kodebarang']." - ".$namabarang."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['jumlah'])."</td>";
				$stream.="<td align=left>".$bar['satuan']."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['hargasatuan'])."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['hartot'])."</td>";
				$stream.="<td align=left>".$bar['subunit']." - ".$namaorganisasi[$bar['subunit']]."</td>";
				$stream.="<td align=left>".$bar['subunitdt']." - ".$namaorganisasi[$bar['subunitdt']]."</td>";
				$stream.="<td align=left>".$bar['noakun']." -  ".$namaakun."</td>";
				$stream.="<td align=left>".$bar['kodekegiatan']." - ".$namakegiatan."</td>";
				$stream.="<td align=left>".$arrposting[$bar['posting']]."</td>";
				$stream.="<td><img src=images/pdf.jpg class=resicon title='Print PDF' onclick=\"previewpdfgr(event,'".$bar['notransaksi']."');\"></td>";
				
				if($bar['tipe']=='SO'){
					$stream.="<td align=center valign=top><img src='images/skyblue/pdf.jpg' class='resicon' title='Print PDF BAPP'' onclick=\"previewpdfgrba(event,'".$bar['notransaksi']."');\"></td>";
				}else{
					$stream.="<td align=center valign=top></td>";
				}

				// $stream.="<td align=center valign=top>";
					$filedata=$no='';
					$strdt="select * from ".$dbname.".listfile_log_penerimaan where notransaksi='".$bar['notransaksi']."'";
					$resdt=$owlPDO->query($strdt) or die(print " Gagal: ".PDOException::getMessage());
			        $resdt->setFetchMode(PDO::FETCH_ASSOC);
			        while($bardt=$resdt->fetch()){
						$no++;
						$filedata.="<a href='".$path.$bardt['namafile']."' target='_blank'>".$_SESSION['lang']['file']."-".$no."</a><br>";
					}
				// $stream.="</td>";
				$stream.="<td align=center valign=top>".$filedata."</td>";
				
			
			$stream.="</tr>";
			@$thartot+=$bar['hartot'];
		}
		$stream.="<tr class=rowcontent>";	
			$stream.="<td align=center colspan=9>".$_SESSION['lang']['total']."</td>";
			$stream.="<td align=right>".hidezerodecimal($thartot)."</td>";
			$stream.="<td align=center colspan=8></td>";
		$stream.="</tr>";
	$stream.="</tbody>";
	$stream.="</table>";




switch ($method) {
	
	case'preview':
		switch($param['tipelaporan']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "Laporan_non_Inventory_".$param['unit']."_".$param['tanggal1']."_sampai_".$param['tanggal2'].".xls";
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