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
	
	case'savedt':
	
		try {
			$owlPDO->beginTransaction();
			
			#= cek periode akuntansi sudah close atau belum
			$str = "select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".substr($param['kodetraksi'],0,4)."' and periode='".$param['periode']."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$tutupbuku=$bar['tutupbuku'];
			}
			
			if($tutupbuku=='1'){
				throw new PDOException("Periode ".$param['periode']." untuk ".substr($param['kodetraksi'],0,4)." sudah diclose ");
			}
			
			#= delete 
			$str = "delete from ".$dbname.".vhc_5master_hist where kodetraksi='".$param['kodetraksi']."' and periode='".$param['periode']."'";
			$owlPDO->exec($str);
	
			#= insert
			$str = "select * from ".$dbname.".vhc_5master where kodetraksi='".$param['kodetraksi']."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$strins="INSERT INTO ".$dbname.".`vhc_5master_hist` (`kodevhc`, `nopol`, `nobpkb`, `kodeorg`, `jenisvhc`, `tahunperolehan`, `tahunproduksi`, `warna`, `noakun`, `beratkosong`, `nomorrangka`, `nomormesin`, `detailvhc`, `kelompokvhc`, `kodebarang`, `kepemilikan`, `kodetraksi`, `tglakhirstnk`, `tglakhirkir`, `tglakhirijinbm`, `tglakhirijinang`, `tglakhirleasing`, `tglakhirasuransi`, `status`, `kodeasset`, `createdby`, `createdtime`, `updateby`, `updatetime`,`periode`)
				VALUES ('".$bar['kodevhc']."','".$bar['nopol']."','".$bar['nobpkb']."','".$bar['kodeorg']."','".$bar['jenisvhc']."','".$bar['tahunperolehan']."','".$bar['tahunproduksi']."','".$bar['warna']."','".$bar['noakun']."','".$bar['beratkosong']."','".$bar['nomorrangka']."','".$bar['nomormesin']."','".$bar['detailvhc']."','".$bar['kelompokvhc']."','".$bar['kodebarang']."','".$bar['kepemilikan']."','".$bar['kodetraksi']."','".$bar['tglakhirstnk']."','".$bar['tglakhirkir']."','".$bar['tglakhirijinbm']."','".$bar['tglakhirijinang']."','".$bar['tglakhirleasing']."','".$bar['tglakhirasuransi']."','".$bar['status']."','".$bar['kodeasset']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$param['periode']."')";
				
				#jika kendaraan sudah pernah diproses di traksi yang berbeda, maka jangan di exe jadi error duplicate
				$str = "select * from ".$dbname.".vhc_5master_hist where kodevhc='".$bar['kodevhc']."' and periode='".$param['periode']."' and kodetraksi!='".$param['kodetraksi']."'";
				$res = fetchdata($str);
				if(count($res)==0){					
					$owlPDO->exec($strins);
				}
			}
			$owlPDO->commit();			
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data  \n" . addslashes($e->getMessage());
		}
	
	break;
	
	case'preview':
		
		#= validasi data harus terisi
		if($param['kodetraksi']==''){
			exit("Warningsystem:PT tidak boleh kosong");
		}
		
		#= array untuk customer
		$str="select * from ".$dbname.".pmn_4customer";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namacustomer[$bar['kodecustomer']]=$bar['namacustomer'];
		}
		
		#= array nama unit
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodetraksi']."' or induk='".$param['kodetraksi']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namaorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
		}

	
		$stylekolom='border=0 cellspacing=1';
		
		// $border='border=0';
		$stream.="<table class=sortable ".$stylekolom." cellpadding=5>";
		$stream.="<thead>";
			
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['kodevhc']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['nopol']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['detail']."</th>";
			$stream.="</tr>";	
			
			$stream.="</thead>";
			$stream.="<tbody>";
			
			#= query ambil data berdasarkan nomor dokumen
			$str="select * from ".$dbname.".vhc_5master where kodetraksi='".$param['kodetraksi']."' order by kodevhc asc";
			// echo $str;
			$res=fetchdata($str);
			foreach($res as $bar){
				@$no++;
				$stream.="<tr class=rowcontent id=row".$no.">";		
					$stream.="<td valign=top align=center>".$no."</td>";
					$stream.="<td valign=top  id=kodevhc".$no.">".$bar['kodevhc']."</td>";
					$stream.="<td valign=top>".$bar['nopol']."</td>";
					$stream.="<td valign=top>".$bar['detailvhc']."</td>";
				$stream.="</tr>";	
			}
			
			$stream.="<tr class=rowcontent>";		
				$stream.="<td colspan=4><button class=mybutton onclick=savedt('".$no."')>".$_SESSION['lang']['proses']."</button></td>";
			$stream.="</tr>";	
			
			
		$stream.="</tbody>";
		$stream.="</table>";

		echo $stream;
	break;
}



?>