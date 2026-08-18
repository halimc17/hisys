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


if($param['kodebarang']==''){
	exit("Warning:Komoditi tidak boleh kosong");
}

switch ($method) {
	case'update':
		#= update
		$str="update ".$dbname.".`pmn_5kontrakpanjang` set keterangan='".$param['keterangan']."',updateby='".$_SESSION['standard']['userid']."' where pasal='".$param['pasal']."' and  kodebarang='".$param['kodebarang']."'   ";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
		}
	break;
	
	case'simpan':
		$str=" INSERT INTO ".$dbname.".`pmn_5kontrakpanjang` (`kodebarang`, `pasal`, `keterangan`, `createdby`, `createtime`, `updateby`)  values ('".$param['kodebarang']."','".$param['pasal']."','".$param['keterangan']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."')"; 
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
		}
		
	break;
	
	case'deletedt':
		$str="delete from ".$dbname.".`pmn_5kontrakpanjang` where pasal='".$param['pasal']."' and  kodebarang='".$param['kodebarang']."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
		}
	break;
	
	case'preview':
	
		$str="select * from ".$dbname.".pmn_5kontrakpanjang where  kodebarang='".$param['kodebarang']."' order by pasal asc ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrpasal[$bar['pasal']]=$bar['pasal'];
			$dtketerangan[$bar['pasal']]=$bar['keterangan'];
		} 
		if($param['tipe']=='html'){
			$border='border=0';
		}else{
			$border='border=1';
		}
			$no=0;
			$stream.="<table class=sortable cellspacing=1 ".$border.">";
			$stream.="<thead>";
				$stream.="<tr class=rowheader>";		
					$stream.="<th align=center>Pasal</th>";
					$stream.="<th align=center>".$_SESSION['lang']['keterangan']."</th>";
					$stream.="<th align=center colspan=2>".$_SESSION['lang']['action']."</th>";
				$stream.="</tr>";
				$stream.="</thead>";
				$stream.="<tbody>";
				foreach($arrpasal as $dtpasal){
					$no++;
					$stream.="<tr class=rowcontent>";	
						$stream.="<td valign=top><input type=text disabled class=myinputtext id=pasal".$no." name=pasal maxlength=20 onkeypress=\"return tanpa_kutip(event)\" style=\"width:40px;\" value=".$dtpasal." /></td>";
						$stream.="<td><textarea name=keterangan id=keterangan".$no." style=\"width:1000px;\" onkeypress=\"return tanpa_kutip(event);\" rows='5' cols='20' >".$dtketerangan[$dtpasal]."</textarea></td>";
						$stream.="<td  valign=top><button class=mybutton onclick=update('".$no."')>".$_SESSION['lang']['save']."</button></td>";
						$stream.="<td valign=top><button class=mybutton onclick=deletedt('".$param['kodebarang']."','".$dtpasal."')>".$_SESSION['lang']['delete']."</button></td>";
					$stream.="</tr>";	
				}
				$no++;
				$stream.="<tr class=rowcontent>";	
					$stream.="<td valign=top><input type=text class=myinputtext style=\"width:40px;\" id=pasal".$no." name=pasal maxlength=20 onkeypress=\"return tanpa_kutip(event)\" /></td>";
					$stream.="<td><textarea name=keterangan style=\"width:1000px;\" id=keterangan".$no." onkeypress=\"return tanpa_kutip(event);\" rows='5' cols='20'></textarea></td>";
					$stream.="<td  valign=top><button class=mybutton onclick=simpan('".$no."')>".$_SESSION['lang']['save']."</button></td>";
					$stream.="<td  valign=top></td>";
				$stream.="</tr>";	
				
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