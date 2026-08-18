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

$arrposting=array("0"=>"Belum Diajukan","1"=>"Sudah disetujui","9"=>"Proses Persetujuan");
$arrproses=array("0"=>$_SESSION['lang']['belumproses'],"1"=>$_SESSION['lang']['sudahproses']);

switch ($method) {
	
	
	case'preview':
	
		if($param['nodokumenlama']==''){
		exit("Warning:No. Dokumen lama tidak boleh kosong");
		}

		if($param['nodokumenbaru']==''){
			exit("Warning:No. Dokumen baru tidak boleh kosong");
		}

		$no=0;		
		// #= transaksi tagihan
		// $str="select * from ".$dbname.".keu_tagihanht where  nopo='".$param['nodokumenlama']."'";
		// $res=fetchdata($str);
		// foreach($res as $bar){
			// $no+=1;
			// $arrsumber['tagihan']='tagihan';
			// $arrnotransaksi[$bar['noinvoice']]=$bar['noinvoice'];
			// $listnotransaksi['tagihan'][$bar['noinvoice']]=$bar['noinvoice'];
			// $tanggal['tagihan'][$bar['noinvoice']]=$bar['tanggal'];
			// $keterangan['tagihan'][$bar['noinvoice']]=$bar['keterangan'];
		// } 
		
		// #= kasbank
		// $str="select * from ".$dbname.".keu_kasbankdt where  nodok='".$param['nodokumenlama']."'";
		// $res=fetchdata($str);
		// foreach($res as $bar){
			// $no+=1;
			// $arrsumber['kasbank']='kasbank';
			// $arrnotransaksi[$bar['notransaksi']]=$bar['notransaksi'];
			// $listnotransaksi['kasbank'][$bar['notransaksi']]=$bar['notransaksi'];
			// $tanggal['kasbank'][$bar['notransaksi']]=$bar['tanggal'];
			// $keterangan['kasbank'][$bar['notransaksi']]=$bar['keterangan2'];
		// } 

		#= keu_jurnaldt
		$str="select * from ".$dbname.".keu_jurnaldt where  nodok='".$param['nodokumenlama']."' and noreferensi in ('".implode("','",$arrnotransaksi)."') ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no+=1;
			$arrsumber['jurnal']='jurnal';
			$arrnotransaksi[$bar['nojurnal']]=$bar['nojurnal'];
			$listnotransaksi['jurnal'][$bar['nojurnal']]=$bar['nojurnal'];
			$tanggal['jurnal'][$bar['nojurnal']]=$bar['tanggal'];
			$keterangan['jurnal'][$bar['nojurnal']]=$bar['keterangan'];
		} 
		
		if($no==0){
			exit("Warningsistem:No. Dokumen tidak ada ditransaksi");
		}
		
		$stream.="<table class=sortable cellspacing=1 ".$border." width=100%>";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center>".$_SESSION['lang']['tipetransaksi']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['keterangan']." ".$_SESSION['lang']['lama']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['keterangan']." ".$_SESSION['lang']['baru']."</th>";
				$stream.="<th align=center>*</th>";
			$stream.="</tr>";
		$stream.="</thead>";
		$no=0;
		foreach($arrsumber as $dtsumber){
			foreach($arrnotransaksi as $dtnotransaksi){
				if($listnotransaksi[$dtsumber][$dtnotransaksi]!=''){
					$no++;
					$stream.="<tr class=rowheader>";
						$stream.="<td id=sumber".$no.">".$dtsumber."</td>";
						$stream.="<td id=notransaksidokumen".$no.">".$dtnotransaksi."</td>";
						$stream.="<td id=tanggal".$no.">".$tanggal[$dtsumber][$dtnotransaksi]."</td>";
						$stream.="<td id=keteranganlama".$no.">".$keterangan[$dtsumber][$dtnotransaksi]."</td>";
						$stream.="<td id=keteranganbaru".$no.">".str_replace($param['nodokumenlama'],$param['nodokumenbaru'],$keterangan[$dtsumber][$dtnotransaksi])."</td>";
						$stream.="<td></td>";
					$stream.="</tr>";	
				}
			}
		}
		$stream.="<tr class=rowheader>";
			$stream.="<td colspan=6><button class=mybutton onclick=saveht('".$no."')>".$_SESSION['lang']['proses']."</button></td>";
		$stream.="</tr>";	
		$stream.="</table>";	

		echo $stream;
	break;
	case'saveht':
	
		try {
			$owlPDO->beginTransaction();
	
		for($i=1;$i<=$param['maxrow'];$i++){
			switch($param['sumber'][$i]){
				case'tagihan':
					$str="update ".$dbname.".keu_tagihanht set nopo='".$param['nodokumenbaru']."',keterangan='".$param['keteranganbaru']."' where nopo='".$param['nodokumenlama']."'";
					$owlPDO->exec($str);
					
				break;
				
				case'kasbank':
					$str="update ".$dbname.".keu_kasbankdt set nodok='".$param['nodokumenbaru']."',keterangan2='".$param['keteranganbaru'][$i]."' where nodok='".$param['nodokumenlama']."'";
					$owlPDO->exec($str);
				break;
				
				case'jurnal':
					$str="update ".$dbname.".keu_jurnaldt set nodok='".$param['nodokumenbaru']."',keterangan='".$param['keteranganbaru'][$i]."' where nodok='".$param['nodokumenlama']."' and nojurnal='".$param['notransaksidokumen'][$i]."'";
					$owlPDO->exec($str);
				break;
			}
		}
		
		#= update 
		$str = "insert into ".$dbname.".keu_gantidokumen
		(notransaksi,nodokumenlama,nodokumenbaru,tanggal,keterangan,createby,createtime,posting,proses,sumber) 
		values 
		('".$param['notransaksi']."','".$param['nodokumenlama']."','".$param['nodokumenbaru']."','".date('Y-m-d')."','OTOMATIS',
		'".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','1','1','ACT')";
		$owlPDO->exec($str);
		
		$owlPDO->commit();
			
		} catch(PDOException $e) {
		
		$owlPDO->rollback();
			echo "Warning: Gagal melakukan penyimpanan data \n" . addslashes($e->getMessage());

		}
	break;
	
	
}



?>