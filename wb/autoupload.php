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

			if ($val['tipeunit']=='EXTERNAL' || $val['tipeunit']=='PLASMA') {
				$intex='0';
			}else{
				$intex='1';
			}
			
			


			if ($val['sumber']=='KEBUN') {
				$data = array(
					'notransaksi'=>$val['notransaksi'],
					'tanggal'=> ($val['waktukeluar'] != '0000-00-00 00:00:00') ? $val['waktukeluar'] : $val['waktumasuk'],
					'kodeorg'=>$val['unitcode'],
					'divcode'=>$val['divcode'],
					'kodecustomer'=>$val['customer'],
					'kodesupplier'=>$val['supplier'],
					'bjr'=>'',
					'jumlahtandan1'=>$val['janjang'],
					'kodebarang'=>$val['kodebarang'],
					'jammasuk'=>substr($val['waktumasuk'], 11,8),
					'beratmasuk'=>$val['beratmasuk'],
					'beratkeluar'=>$val['beratkeluar'],
					'jamkeluar'=> (substr($val['waktukeluar'], 11,8) != '00:00:00') ? substr($val['waktukeluar'], 11,8) : substr($val['waktumasuk'], 11,8),
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
					'tahuntanam'=>$val['tahuntanam'],
					'tipeangkut'=>$val['tipeangkut'],
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
					$owlPDOERP->exec($strxdel2);

					$strx="SELECT * FROM ".$dbname.".wb where notransaksi = '".$val['notransaksi']."'";
					$resx=fetchdata($strx);

					$str2="INSERT INTO ".$dbnameerp.".wb_temp values ('".$resx[0]['notransaksi']."','".$resx[0]['in_out']."','".$resx[0]['waktumasuk']."','".$resx[0]['waktukeluar']."','".$resx[0]['beratmasuk']."','".$resx[0]['beratkeluar']."','".$resx[0]['netto']."','".$resx[0]['nettosplit']."','".$resx[0]['nettosplit2']."','".$resx[0]['potongan']."','".$resx[0]['potonganwajib']."','".$resx[0]['satuan']."','".$resx[0]['millcode']."','".$resx[0]['kodebarang']."','".$resx[0]['nopo']."','".$resx[0]['multi']."','".$resx[0]['kontrakbeli']."','".$resx[0]['kontrakbeli2']."','".$resx[0]['kontrakjual']."','".$resx[0]['kontrakjual2']."','".$resx[0]['notekirim']."','".$resx[0]['supir']."','".$resx[0]['kernet1']."','".$resx[0]['kernet2']."','".$resx[0]['nosim']."','".$resx[0]['spb']."','".$resx[0]['qr']."','".$resx[0]['nokendaraan']."','".$resx[0]['qtysegel']."','".$resx[0]['segel']."','".$resx[0]['janjang']."','".$resx[0]['brondolan']."','".$resx[0]['keterangan']."','".$resx[0]['transportir']."','".$resx[0]['supplier']."','".$resx[0]['customer']."','".$resx[0]['storage']."','".$resx[0]['unitcode']."','".$resx[0]['divcode']."','".$resx[0]['tipeunit']."','".$resx[0]['pemilik']."','".$resx[0]['estorigin']."','".$resx[0]['batch']."','".$resx[0]['receivedate']."','".$resx[0]['receiveqty']."','".$resx[0]['loses']."','".$resx[0]['gainloses']."','".$resx[0]['ffa']."','".$resx[0]['moist']."','".$resx[0]['dirt']."','".$resx[0]['dobi']."','".$resx[0]['printversion']."','".$resx[0]['krani']."','".$resx[0]['sumber']."','".$resx[0]['tiketref']."','".$resx[0]['blendsendqty']."','".$resx[0]['wbcond']."','".$resx[0]['submittime']."','".$resx[0]['FLAG_TRX']."','".$resx[0]['FLAG']."','".$resx[0]['deletecomment']."','".$resx[0]['deletetime']."','".$resx[0]['deleteuser']."','".$resx[0]['updatetime']."','".$resx[0]['updateuser']."','".$resx[0]['postingdate']."')";
					$owlPDOERP->exec($str2);

					if ($val['tipeunit']=='INTERNAL') {

						// INSERT SPBHT
						$strx = deleteQuery($dbnameerp,'kebun_spbht',"nospb='".$val['qr']."'");
						$owlPDOERP->exec($strx);

						$tglll = (substr($val['waktukeluar'], 0,10) != '0000-00-00') ? substr($val['waktukeluar'], 0,10) : substr($val['waktumasuk'], 0,10);

						$str2="INSERT INTO ".$dbnameerp.".kebun_spbht values ('".$val['qr']."','','".$val['unitcode']."','0','".$val['customer']."','".$tglll."','0','','','','','KERJA','".$val['tahuntanam']."')";
						$owlPDOERP->exec($str2);
						// INSERT SPBDT
						$strxI_spbDt="select * from ".$dbname.".wb_datapanen where notiket='".$val['notransaksi']."'";
						$resxI_spbDt=fetchdata($strxI_spbDt);
						if($resxI_spbDt){
							foreach($resxI_spbDt as $valxI_spbDt){

								// ambil BJR
								// $strx_brj="SELECT bjr FROM ".$dbname.".kebun_5bjr where kodeorg = '".$valxI_spbDt['blok']."' and periode = '".substr($valxI_spbDt['tanggal'],0,7)."' order by periode desc limit 1";
								// $resx_bjr=fetchdata($strx_brj);
								// $bjr_n = $resx_bjr[0]['bjr'];

								// $kgbjr_n = $valxI_spbDt['jjg']*$resx_bjr[0]['bjr'];

								$dataI_spbDt = array(
									'nospb'=>$val['qr'],
									'qrcode'=>$valxI_spbDt['qrcode'],
									'tanggalpanen'=>$valxI_spbDt['tanggal'],
									'tph'=>$valxI_spbDt['tph'],
									'blok'=>$valxI_spbDt['blok'],
									'sesi'=>$valxI_spbDt['sesi'],
									'pemanen'=>$valxI_spbDt['pemanen'],
									'jjg'=>$valxI_spbDt['jjg'],
									'kgwb'=>0,
									'kgwbnetto'=>0,
									'bjr'=>0,
									'brondolan'=>$valxI_spbDt['brondolan'],
									'totalkg'=>0,
									'kgbjr'=>0
								);
								
								$colsI_spbDt = array();	
								foreach($dataI_spbDt as $keyI=>$rowI) {
									$colsI_spbDt[] = $keyI;
								}
								$stryI_spbDt = insertQuery($dbnameerp,'kebun_spbdt',$dataI_spbDt,$colsI_spbDt);
								$owlPDOERP->exec($stryI_spbDt);
							}

						}


						// INSERT TIKET PANEN
						$strx = deleteQuery($dbnameerp,'pabrik_wb_datapanen',"notiket='".$val['notransaksi']."'");
						$owlPDOERP->exec($strx);
						

						$strxI_dp="select * from ".$dbname.".wb_datapanen where notiket='".$val['notransaksi']."'";
						$resxI_dp=fetchdata($strxI_dp);
						if($resxI_dp){
							foreach($resxI_dp as $valxI_dp){
								$dataI_dp = array(
									'notiket'=>$valxI_dp['notiket'],
									'qrcode'=>$valxI_dp['qrcode'],
									'tanggal'=>$valxI_dp['tanggal'],
									'tph'=>$valxI_dp['tph'],
									'blok'=>$valxI_dp['blok'],
									'sesi'=>$valxI_dp['sesi'],
									'pemanen'=>$valxI_dp['pemanen'],
									'jjg'=>$valxI_dp['jjg'],
									'brondolan'=>$valxI_dp['brondolan'],
									'createby'=>$valxI_dp['createby'],
									'createtime'=>$valxI_dp['createtime'],
								);
								
								$colsI_dp = array();	
								foreach($dataI_dp as $keyI=>$rowI) {
									$colsI_dp[] = $keyI;
								}
								$stryI_dp = insertQuery($dbnameerp,'pabrik_wb_datapanen',$dataI_dp,$colsI_dp);
								$owlPDOERP->exec($stryI_dp);
							}

						}

						



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

						// INSERT SPBHT
						$strx = deleteQuery($dbnameerp,'kebun_spbht',"nospb='".$val['qr']."'");
						$owlPDOERP->exec($strx);

						$tglll = (substr($val['waktukeluar'], 0,10) != '0000-00-00') ? substr($val['waktukeluar'], 0,10) : substr($val['waktumasuk'], 0,10);

						$str2="INSERT INTO ".$dbnameerp.".kebun_spbht values ('".$val['qr']."','','".$val['unitcode']."','3','".$val['customer']."','".$tglll."','0','','','','','KERJA','".$val['tahuntanam']."')";
						$owlPDOERP->exec($str2);
						// INSERT SPBDT
						$strxI_spbDt="select * from ".$dbname.".wb_datapanen where notiket='".$val['notransaksi']."'";
						$resxI_spbDt=fetchdata($strxI_spbDt);
						if($resxI_spbDt){
							foreach($resxI_spbDt as $valxI_spbDt){

								// ambil BJR
								// $strx_brj="SELECT bjr FROM ".$dbname.".kebun_5bjr where kodeorg = '".$valxI_spbDt['blok']."' and periode = '".substr($valxI_spbDt['tanggal'],0,7)."' order by periode desc limit 1";
								// $resx_bjr=fetchdata($strx_brj);
								// $bjr_n = $resx_bjr[0]['bjr'];

								// $kgbjr_n = $valxI_spbDt['jjg']*$resx_bjr[0]['bjr'];

								$dataI_spbDt = array(
									'nospb'=>$val['qr'],
									'qrcode'=>$valxI_spbDt['qrcode'],
									'tanggalpanen'=>$valxI_spbDt['tanggal'],
									'tph'=>$valxI_spbDt['tph'],
									'blok'=>$valxI_spbDt['blok'],
									'sesi'=>$valxI_spbDt['sesi'],
									'pemanen'=>$valxI_spbDt['pemanen'],
									'jjg'=>$valxI_spbDt['jjg'],
									'kgwb'=>0,
									'kgwbnetto'=>0,
									'bjr'=>0,
									'brondolan'=>$valxI_spbDt['brondolan'],
									'totalkg'=>0,
									'kgbjr'=>0
								);
								
								$colsI_spbDt = array();	
								foreach($dataI_spbDt as $keyI=>$rowI) {
									$colsI_spbDt[] = $keyI;
								}
								$stryI_spbDt = insertQuery($dbnameerp,'kebun_spbdt',$dataI_spbDt,$colsI_spbDt);
								$owlPDOERP->exec($stryI_spbDt);
							}

						}


						// INSERT TIKET PANEN
						$strx = deleteQuery($dbnameerp,'pabrik_wb_datapanen',"notiket='".$val['notransaksi']."'");
						$owlPDOERP->exec($strx);
						

						$strxI_dp="select * from ".$dbname.".wb_datapanen where notiket='".$val['notransaksi']."'";
						$resxI_dp=fetchdata($strxI_dp);
						if($resxI_dp){
							foreach($resxI_dp as $valxI_dp){
								$dataI_dp = array(
									'notiket'=>$valxI_dp['notiket'],
									'qrcode'=>$valxI_dp['qrcode'],
									'tanggal'=>$valxI_dp['tanggal'],
									'tph'=>$valxI_dp['tph'],
									'blok'=>$valxI_dp['blok'],
									'sesi'=>$valxI_dp['sesi'],
									'pemanen'=>$valxI_dp['pemanen'],
									'jjg'=>$valxI_dp['jjg'],
									'brondolan'=>$valxI_dp['brondolan'],
									'createby'=>$valxI_dp['createby'],
									'createtime'=>$valxI_dp['createtime'],
								);
								
								$colsI_dp = array();	
								foreach($dataI_dp as $keyI=>$rowI) {
									$colsI_dp[] = $keyI;
								}
								$stryI_dp = insertQuery($dbnameerp,'pabrik_wb_datapanen',$dataI_dp,$colsI_dp);
								$owlPDOERP->exec($stryI_dp);
							}

						}

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
				$owlPDOERP->exec($strxdel2);

				$strx="SELECT * FROM ".$dbname.".wb where notransaksi = '".$val['notransaksi']."'";
				$resx=fetchdata($strx);

				$str2="INSERT INTO ".$dbnameerp.".wb_temp values ('".$resx[0]['notransaksi']."','".$resx[0]['in_out']."','".$resx[0]['waktumasuk']."','".$resx[0]['waktukeluar']."','".$resx[0]['beratmasuk']."','".$resx[0]['beratkeluar']."','".$resx[0]['netto']."','".$resx[0]['nettosplit']."','".$resx[0]['nettosplit2']."','".$resx[0]['potongan']."','".$resx[0]['potonganwajib']."','".$resx[0]['satuan']."','".$resx[0]['millcode']."','".$resx[0]['kodebarang']."','".$resx[0]['nopo']."','".$resx[0]['multi']."','".$resx[0]['kontrakbeli']."','".$resx[0]['kontrakbeli2']."','".$resx[0]['kontrakjual']."','".$resx[0]['kontrakjual2']."','".$resx[0]['notekirim']."','".$resx[0]['supir']."','".$resx[0]['kernet1']."','".$resx[0]['kernet2']."','".$resx[0]['nosim']."','".$resx[0]['spb']."','".$resx[0]['qr']."','".$resx[0]['nokendaraan']."','".$resx[0]['qtysegel']."','".$resx[0]['segel']."','".$resx[0]['janjang']."','".$resx[0]['brondolan']."','".$resx[0]['keterangan']."','".$resx[0]['transportir']."','".$resx[0]['supplier']."','".$resx[0]['customer']."','".$resx[0]['storage']."','".$resx[0]['unitcode']."','".$resx[0]['divcode']."','".$resx[0]['tipeunit']."','".$resx[0]['pemilik']."','".$resx[0]['estorigin']."','".$resx[0]['batch']."','".$resx[0]['receivedate']."','".$resx[0]['receiveqty']."','".$resx[0]['loses']."','".$resx[0]['gainloses']."','".$resx[0]['ffa']."','".$resx[0]['moist']."','".$resx[0]['dirt']."','".$resx[0]['dobi']."','".$resx[0]['printversion']."','".$resx[0]['krani']."','".$resx[0]['sumber']."','".$resx[0]['tiketref']."','".$resx[0]['blendsendqty']."','".$resx[0]['wbcond']."','".$resx[0]['submittime']."','".$resx[0]['FLAG_TRX']."','".$resx[0]['FLAG']."','".$resx[0]['deletecomment']."','".$resx[0]['deletetime']."','".$resx[0]['deleteuser']."','".$resx[0]['updatetime']."','".$resx[0]['updateuser']."','".$resx[0]['postingdate']."')";
				$owlPDOERP->exec($str2);

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