<?php
require_once('config/connection.php');
require_once('config/connectionstages.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$datenow=date('Ymd');
$timenow=date('His');
$datetimenow=date('Y-m-d H:i:s');
$str="select * from ".$dbname.".wb where FLAG = '0' and netto > 0";
$res=fetchData($str);
$notransaksi = "";
if ($res) {
	foreach ($res as $val) {


		try {
			$owlPDO->beginTransaction();
			$owlPDOERP->beginTransaction();

			if ($val['tipeunit']=='EKSTERNAL') {
				$intex='0';
			}else{
				$intex='1';
			}
			
			if ($val['sumber']=='PABRIK') {
				$data = array(
					'notransaksi'=>$val['notransaksi'],
					'tanggal'=>$val['waktukeluar'],
					'kodeorg'=>$val['unitcode'],
					'divcode'=>$val['divcode'],
					'kodecustomer'=>$val['customer'],
					'kodesupplier'=>$val['supplier'],
					'bjr'=>'',
					'jumlahtandan1'=>$val['janjang'],
					'kodebarang'=>$val['kodebarang'],
					'jammasuk'=>substr($val['waktumasuk'], 11,8),
					'beratmasuk'=>$val['beratmasuk'],
					'jamkeluar'=>substr($val['waktukeluar'], 11,8),
					'nokendaraan'=>$val['nokendaraan'],
					'supir'=>$val['supir'],
					'nospb'=>$val['qr'],
					'petugassortasi'=>'',
					'timbangonoff'=>'1',
					'statussortasi'=>'B0',
					'nokontrak'=>'',
					'nodo'=>$val['kontrakjual'],
					'intex'=>$intex,
					'nosipb'=>'',
					'nosipb'=>'',
					'thntm1'=>'',
					'thntm2'=>'',
					'thntm3'=>'',
					'jumlahtandan2'=>'',
					'jumlahtandan3'=>'',
					'brondolan'=>$val['brondolan'],
					'username'=>$val['krani'],
					'millcode'=>$val['millcode'],
					'beratbersih'=>$val['netto'],
					'trpcode'=>$val['transportir'],
					'jjgsortasi'=>'',
					'kgpotsortasi'=>$val['potongan'],
					'persenBrondolan'=>'',
					'tglpembeli'=>'',
					'kgpembeli'=>'',
					'kriteriabuah'=>'',
					'id_klasifikasi'=>'',
					'sloc'=>$val['storage'],
					'idtimbangan'=>'',
					'moist'=>$val['moist'],
					'dirt'=>$val['dirt'],
					'dobi'=>$val['dobi'],
					'bps'=>$val['ffa'],
					'sipbqty'=>'',
					'nosegel'=>$val['segel'],
					'jlhsegel'=>$val['qtysegel'],
					'norefrensi'=>'',
					'ramp'=>'',
					'pengirim'=>'',
					'keterangan'=>$val['keterangan'],
					'beratmasukpmks'=>'',
					'beratkeluarpmks'=>'',
					'beratbersihpmks'=>'',
					'tanggalpks'=>'',
					'namatransportir'=>'',
					'intiplasma'=>'',
					'kirimbulking'=>'',
					'timbang1'=>$val['waktumasuk'],
					'timbang2'=>$val['waktukeluar'],
					'hapusby'=>'',
					'tglhapus'=>'',
					'keteranganhapus'=>'',
					'reject'=>'',
					'wbcond'=>'',
					'tiketref'=>'',
				);
				$cols = array();	
				foreach($data as $key=>$row) {
					$cols[] = $key;
				}



					$strxdel = deleteQuery($dbnameerp,'pabrik_timbangan',"notransaksi='".$val['notransaksi']."'");
					$owlPDOERP->exec($strxdel);

					$strx = insertQuery($dbnameerp,'pabrik_timbangan',$data,$cols);
					$owlPDOERP->exec($strx);

				/*	$strxdel2 = deleteQuery($dbnameerp,'wb_temp',"notransaksi='".$val['notransaksi']."'");
					$owlPDOERP->exec($strxdel2);*/

					$strxdel2="delete from ".$dbnameerp.".wb_temp where notransaksi='".$val['notransaksi']."'";
					$owlPDO->exec($strxdel2);

					$str2="INSERT INTO ".$dbnameerp.".wb_temp SELECT * FROM ".$dbname.".wb where notransaksi = '".$val['notransaksi']."'";
					$owlPDO->exec($str2);

					if ($val['tipeunit']=='INTERNAL') {
						## INSERT TABLE SORTASI ##
						$strx = deleteQuery($dbnameerp,'pabrik_sortasi',"notiket='".$val['notransaksi']."'");
						$owlPDOERP->exec($strx);

						$strxI="select * from ".$dbname.".trxsortasi where notransaksi='".$val['notransaksi']."'";
						$resxI=fetchdata($strxI);
						if($resxI){
							foreach($resxI as $valxI){
								$strI="select * from ".$dbname.".msgrading where kode='".$valxI['kode']."'";
								$resI=fetchdata($strI);
								if (substr($valxI['field'], -2,2)=='KG') {
									$nilaiI="KG";
								}else{
									$nilaiI="PERSEN";
								}
								$arrsortasiI[$resI[0]['kode']][$nilaiI]=$valxI['value'];
							}

							foreach ($arrsortasiI as $deskripsiI => $arrnilaiI) {
								$dataI = array(
									'notiket'=>$valxI['notransaksi'],
									'kodefraksi'=>$deskripsiI,
									'persen'=>$arrnilaiI['PERSEN'],
									'kg'=>$arrnilaiI['KG'],
									'tipe'=>'grading'
								);
								$colsI = array();	
								foreach($dataI as $keyI=>$rowI) {
									$colsI[] = $keyI;
								}
								$stryI = insertQuery($dbnameerp,'pabrik_sortasi',$dataI,$colsI);
								$owlPDOERP->exec($stryI);
							}
						}
					}
					else
					{
						## INSERT TABLE SORTASI ##

						$strx = deleteQuery($dbnameerp,'pabrik_sortasi',"notiket='".$val['notransaksi']."'");
						$owlPDOERP->exec($strx);

						$strx="select * from ".$dbname.".trxsortasi where notransaksi='".$val['notransaksi']."'";
						$resx=fetchdata($strx);
						if($resx){
							foreach($resx as $valx){
								$str="select * from ".$dbname.".mssortasi where kode='".$valx['kode']."'";
								$res=fetchdata($str);
								if (substr($valx['field'], -2,2)=='KG') {
									$nilai="KG";
								}else{
									$nilai="PERSEN";
								}
								$arrsortasi[$res[0]['kode']][$nilai]=$valx['value'];
							}

							foreach ($arrsortasi as $deskripsi => $arrnilai) {
								$data = array(
									'notiket'=>$valx['notransaksi'],
									'kodefraksi'=>$deskripsi,
									'persen'=>$arrnilai['PERSEN'],
									'kg'=>$arrnilai['KG'],
									'tipe'=>'sortasi'
								);
								$cols = array();	
								foreach($data as $key=>$row) {
									$cols[] = $key;
								}
								$stry = insertQuery($dbnameerp,'pabrik_sortasi',$data,$cols);
								$owlPDOERP->exec($stry);
							}
						}
					}

			
					$strx="update ".$dbname.".wb set submittime='".$datetimenow."',FLAG = '1' where notransaksi = '".$val['notransaksi']."'";
					$owlPDO->exec($strx);
			}
			else
			{
				//timbngan kebun


				$strxdel2="delete from ".$dbnameerp.".wb_temp where notransaksi='".$val['notransaksi']."'";
				$owlPDO->exec($strxdel2);

				$str2="INSERT INTO ".$dbnameerp.".wb_temp SELECT * FROM ".$dbname.".wb where notransaksi = '".$val['notransaksi']."'";
				$owlPDO->exec($str2);

			}
			//if ($val['FLAG_TRX']=='N') {

				

			$owlPDO->commit();
			$owlPDOERP->commit();
		} catch (Exception $e) {
			$owlPDOERP->rollback();
			$owlPDO->rollback();
			echo $e."##<br>";
			continue;
		}
	}

	

	echo $val['notransaksi']." ".$val['FLAG_TRX']."<br>";
}


?>