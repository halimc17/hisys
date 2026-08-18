<?php
require_once('config/connection.php');
require_once('config/connectionstages.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
	
$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}
$datenow=date('Ymd');
$timenow=date('His');
$datetimenow=date('Y-m-d H:i:s');

$str="select compcode,millcode from ".$dbname.".mssystem";
$res=fetchdata($str);
$pt=$res[0]['compcode'];
$millcode=$res[0]['millcode'];
$arrcompmillcode[$res[0]['millcode']]=$res[0]['compcode'];

switch($method){
	case'company': 
		stagescompany();
	break;
	
	case'unit': 
		stagesunit();
	break;
	
	case'divisi':
		stagesdivisi();
	break;
	
	case'produk':
		stagesproduk();
	break;
	
	case'vendor':
		stagesvendor();
	break;
	
	case'customer':
		stagescustomer();
	break;
	
	case'so':
		stagesso();
	break;
	
	case'po':
		stagespo();
	break;

	case'productionorder':
		stagesproductionorder();
	break;
	
	case'grading':
		stagesgrading();
	break;
	
	case'sortasi':
		stagessortasi();
	break;
	
	default:
	break;
}

function stagescompany(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	global $pt;
	
	## NEW
	$str="select * from ".$dbnameerp.".organisasi where tipe='PT'";
	$res=fetchdatax($str);
	if(count($res) > 0){
		foreach($res as $val){
			try{
				$owlPDOERP->beginTransaction();
				$owlPDO->beginTransaction();
				
				$strx="select * from ".$dbname.".mscompany where compcode='".$val['kodeorganisasi']."'";
				$resx=fetchdata($strx);
				if(count($resx) <= 0){
					$data = array(
						'compcode'=>$val['kodeorganisasi'],
						// 'descode1'=>$val['descode1'],
						'compname'=>$val['namaorganisasi'],
						'compaddr'=>$val['alamat'],
						'compaddr2'=>$val['wilayahkota'],
						'compstatus'=>'1',
					);
					$cols = array();	
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$strt = insertQuery($dbname,'mscompany',$data,$cols);
					$owlPDO->exec($strt);
				}else{
					$data = array(
						// 'descode1'=>$val['descode1'],
						'compname'=>$val['namaorganisasi'],
						'compaddr'=>$val['alamat'],
						'compaddr2'=>$val['wilayahkota']
					);
					$strt = updateQuery($dbname,'mscompany',$data,"compcode='".$val['kodeorganisasi']."'");
					$owlPDO->exec($strt);
				}
				
				$owlPDOERP->commit();
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDOERP->rollback();
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
}

function stagesunit(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	global $pt;
	
	## NEW
	$str="select * from ".$dbnameerp.".organisasi where tipe in ('KEBUN','PABRIK')";
	$res=fetchdatax($str);
	if(count($res) > 0){
		foreach($res as $val){
			try{
				$owlPDOERP->beginTransaction();
				$owlPDO->beginTransaction();
				
				if($val['induk']!=''){
					$strx="select * from ".$dbname.".msunit where unitcode='".$val['kodeorganisasi']."'";
					$resx=fetchdata($strx);
					if(count($resx) <= 0){
						$data = array(
							'unitcode'=>$val['kodeorganisasi'],
							'tipe'=>$val['tipe'],
							'tipeunit'=>'INTERNAL',
							'unitname'=>$val['namaorganisasi'],
							'compcode'=>$val['induk'],
							'unitstatus'=>'1',
						);
						$cols = array();	
						foreach($data as $key=>$row) {
							$cols[] = $key;
						}
						$strt = insertQuery($dbname,'msunit',$data,$cols);
						$owlPDO->exec($strt);
					}else{
						$data = array(
							// 'descode1'=>$val['descode1'],
							'tipe'=>$val['tipe'],
							// 'tipeunit'=>$val['tipeunit'],
							'unitname'=>$val['namaorganisasi'],
							'compcode'=>$val['induk']
						);
						$strt = updateQuery($dbname,'msunit',$data,"unitcode='".$val['kodeorganisasi']."'");
						$owlPDO->exec($strt);
					}
				}
				
				$owlPDOERP->commit();
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDOERP->rollback();
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
}

function stagesdivisi(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	global $pt;
	
	## NEW
	$str="select * from ".$dbnameerp.".organisasi where tipe='AFDELING'";
	$res=fetchdatax($str);
	if(count($res) > 0){
		foreach($res as $val){
			try{
				$owlPDOERP->beginTransaction();
				$owlPDO->beginTransaction();
				
				$optpt=makeOption($dbname,'msunit','unitcode,compcode',"unitcode='".$val['induk']."'");
				
				$strx="select * from ".$dbname.".msdivisi where divcode='".$val['kodeorganisasi']."'";
				$resx=fetchdata($strx);
				if(count($resx) <= 0){
					$data = array(
						'compcode'=>$optpt[$val['induk']],
						'unitcode'=>$val['induk'],
						'divcode'=>$val['kodeorganisasi'],
						// 'descode1'=>$val['descode1'],
						'divname'=>$val['namaorganisasi'],
						'divstatus'=>'1',
					);
					$cols = array();	
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$strt = insertQuery($dbname,'msdivisi',$data,$cols);
					$owlPDO->exec($strt);
				}else{
					$data = array(
						'compcode'=>$optpt[$val['induk']],
						'unitcode'=>$val['induk'],
						// 'descode1'=>$val['descode1'],
						'divname'=>$val['namaorganisasi']
					);
					$strt = updateQuery($dbname,'msdivisi',$data,"divcode='".$val['kodeorganisasi']."'");
					$owlPDO->exec($strt);
				}
				
				$owlPDOERP->commit();
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDOERP->rollback();
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
}

function stagesproduk(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	
	## NEW
	$str="select * from ".$dbnameerp.".log_5masterbarang where inactive='0' and (left(kodebarang,1)='3' || left(kodebarang,1)='4')";
	$res=fetchdatax($str);
	$arrdata=$res;
	
	if(count($arrdata) > 0){
		foreach($arrdata as $val){
			try{
				$owlPDO->beginTransaction();

				if ($val['inactive']==0) {
					$status=1;
				}else{
					$status=0;
				}
				
				$strx="select * from ".$dbname.".msproduk where kodeproduk='".$val['kodebarang']."'";
				$resx=fetchdata($strx);
				if(count($resx) <= 0){
					$data = array(
						'kodeproduk'=>$val['kodebarang'],
						'namaproduk'=>addslashes($val['namabarang']),
						'inisial'=>$val['inisial'],
						'satuan'=>$val['satuan'],
						'statusproduk'=>$status,
					);
					$cols = array();	
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$strt = insertQuery($dbname,'msproduk',$data,$cols);
					$owlPDO->exec($strt);
				}else{
					$data = array(
						'namaproduk'=>$val['namabarang'],
						'inisial'=>$val['inisial'],
						'satuan'=>$val['satuan'],
					);
					$strt = updateQuery($dbname,'msproduk',$data,"kodeproduk='".$val['kodebarang']."'");
					$owlPDO->exec($strt);
				}
				
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
}

function stagesvendor(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	
	## NEW
	$str="select a.tipe as jenis, b.* from ".$dbnameerp.".log_5supkelompok a left join ".$dbnameerp.".log_5supplier b on a.supplierid=b.supplierid where a.tipe in ('LAINNYA','SUPPLIER','TBSAFL','TBSEXT','TBSINT','TBSPLS','TRANSPORTIR')";
	$res=fetchdatax($str);
	$arrdata=$res;
	if(count($arrdata) > 0){
		foreach($arrdata as $val){
			try{
				$owlPDO->beginTransaction();
				
				$sup="";
				$trans="";
				if($val['jenis']=='TRANSPORTIR'){
					$trans='1';
				}else{
					$sup='1';
				}
				
				$strx="select * from ".$dbname.".msvendor where vendorcode='".$val['supplierid']."'";
				$resx=fetchdata($strx);
				if(count($resx) <= 0){
					$data = array(
						'vendorcode'=>$val['supplierid'],
						'vendorname'=>$val['namasupplier'],
						'supplier'=>$sup,
						'transportir'=>$trans,
						'vendorstatus'=>$val['status'],
					);
					$cols = array();	
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$strt = insertQuery($dbname,'msvendor',$data,$cols);
					$owlPDO->exec($strt);
				}else{
					$data = array(
						'vendorname'=>$val['namasupplier'],
						'supplier'=>$sup,
						'transportir'=>$trans
					);
					$strt = updateQuery($dbname,'msvendor',$data,"vendorcode='".$val['supplierid']."'");
					$owlPDO->exec($strt);
				}
				
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
}

function stagescustomer(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	
	## NEW
	$str="select * from ".$dbnameerp.".pmn_4customer";
	$res=fetchdatax($str);
	$arrdata=$res;
	if(count($arrdata) > 0){
		foreach($arrdata as $val){
			try{
				$owlPDO->beginTransaction();
				
				$strx="select * from ".$dbname.".mscustomer where custcode='".$val['kodecustomer']."'";
				$resx=fetchdata($strx);
				if(count($resx) <= 0){
					$data = array(
						'custcode'=>$val['kodecustomer'],
						'custname'=>$val['namacustomer'],
						'custstatus'=>$val['status']
					);
					$cols = array();	
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$strt = insertQuery($dbname,'mscustomer',$data,$cols);
					$owlPDO->exec($strt);
				}else{
					$data = array(
						'custname'=>$val['namacustomer']
					);
					$strt = updateQuery($dbname,'mscustomer',$data,"custcode='".$val['kodecustomer']."'");
					$owlPDO->exec($strt);
				}
				
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
}

function stagesso(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	
	$pt="";
	$str="select * from ".$dbname.".mssystem limit 1";
	$res=fetchdata($str);
	$countdata=count($res);
	if($countdata > 0){
		$pt=$res[0]['compcode'];
	}
	
	## NEW
	$str="select *, b.kuantitaskontrak from ".$dbnameerp.".pmn_suratperintahpengiriman a left join pmn_kontrakjual b on a.nokontrak=b.nokontrak where a.pt='".$pt."' and a.posting='1'";
	$res=fetchdatax($str);
	$arrdata=$res;
	if(count($arrdata) > 0){
		foreach($arrdata as $val){
			try{
				$owlPDO->beginTransaction();
				
				$strx="select * from ".$dbname.".msso where noso='".$val['nodo']."'";
				$resx=fetchdata($strx);
				if(count($resx) <= 0){
					$data = array(
						'compcode'=>$val['kodept'],
						'noso'=>$val['nodo'],
						'nosoinduk'=>$val['nokontrak'],
						'kontrakqty'=>$val['kuantitaskontrak'],
						'custcode'=>$val['supplierid'],
						'vendorcode'=>$val['transportir'],
						'kodeproduk'=>$val['kodebarang'],
						'soqty'=>$val['qty'],
						'sisaso'=>$val['qty'],
						'sostatus'=>'1'
					);
					$cols = array();	
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$strt = insertQuery($dbname,'msso',$data,$cols);
					$owlPDO->exec($strt);
				}else{
					$isupdate=0;
					$soqty=$val['qty'];
					$sisaso=$val['qty'];
					$selisihso = $val['qty']-$resx[0]['soqty'];
					if($selisihso >= 0){
						$isupdate=1;
						$soqty=$val['qty'];
						$sisaso=($resx[0]['sisaso']+$selisihso);
					}else{
						$selisihsisaso = ($selisihso + $resx[0]['sisaso']);
						if($selisihsisaso >= 0){
							$isupdate=1;
							$soqty=$val['qty'];
							$sisaso=($selisihso + $resx[0]['sisaso']);
						}
					}
					
					$data = array(
						'compcode'=>$val['kodept'],
						'nosoinduk'=>$val['nokontrak'],
						'kontrakqty'=>$val['kuantitaskontrak'],
						'custcode'=>$val['supplierid'],
						'vendorcode'=>$val['transportir'],
						'kodeproduk'=>$val['kodebarang'],
						'soqty'=>$soqty,
						'sisaso'=>$sisaso
					);
					
					if($isupdate==1){
						$strt = updateQuery($dbname,'msso',$data,"noso='".$val['nodo']."'");
						$owlPDO->exec($strt);							
					}
				}
				
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
	
	// ## NEW
	// $str="select * from ".$dbnameerp.".pmn_kontrakjual where kodept='".$pt."'";
	// $res=fetchdatax($str);
	// exit("error : ".$str);
	// $arrdata=$res;
	// if(count($arrdata) > 0){
		// foreach($arrdata as $val){
			// try{
				// $owlPDO->beginTransaction();
				
				// $strx="select * from ".$dbname.".msso where noso='".$val['nokontrak']."'";
				// $resx=fetchdata($strx);
				// if(count($resx) <= 0){
					// $data = array(
						// 'compcode'=>$val['kodept'],
						// 'noso'=>$val['nokontrak'],
						// 'nosoinduk'=>$val['nokontrakinduk'],
						// 'custcode'=>$val['koderekanan'],
						// 'vendorcode'=>$val['transporter'],
						// 'kodeproduk'=>$val['kodebarang'],
						// 'soqty'=>$val['kuantitas'],
						// 'sisaso'=>$val['kuantitas'],
						// 'sostatus'=>$val['status']
					// );
					// $cols = array();	
					// foreach($data as $key=>$row) {
						// $cols[] = $key;
					// }
					// $strt = insertQuery($dbname,'msso',$data,$cols);
					// $owlPDO->exec($strt);
				// }else{
					// $isupdate=0;
					// $soqty=$val['kuantitas'];
					// $sisaso=$val['kuantitas'];
					// $selisihso = $val['kuantitas']-$resx[0]['soqty'];
					// if($selisihso >= 0){
						// $isupdate=1;
						// $soqty=$val['kuantitas'];
						// $sisaso=($resx[0]['sisaso']+$selisihso);
					// }else{
						// $selisihsisaso = ($selisihso + $resx[0]['sisaso']);
						// if($selisihsisaso >= 0){
							// $isupdate=1;
							// $soqty=$val['kuantitas'];
							// $sisaso=($selisihso + $resx[0]['sisaso']);
						// }
					// }
					
					// $data = array(
						// 'compcode'=>$val['kodept'],
						// 'nosoinduk'=>$val['nokontrakinduk'],
						// 'custcode'=>$val['koderekanan'],
						// 'vendorcode'=>$val['transporter'],
						// 'kodeproduk'=>$val['kodebarang'],
						// 'soqty'=>$soqty,
						// 'sisaso'=>$sisaso
					// );
					
					// if($isupdate==1){
						// $strt = updateQuery($dbname,'msso',$data,"noso='".$val['nokontrak']."'");
						// $owlPDO->exec($strt);							
					// }
				// }
				
				// $owlPDO->commit();
			// }catch (PDOException $e){
				// $owlPDO->rollback();
				// echo $e."##<br>";
				// continue;
			// }
		// }
	// }
}

function stagespo(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	global $pt;
	global $millcode;
	global $kodeproduktbs;
	global $arrcompmillcode;
	
	
	## NEW
	$str="select * from ".$dbnameerp.".pmn_kontrakbeli where unit='".$millcode."' and kodebarang='".$kodeproduktbs."' and posting='1'";
	$res=fetchdatax($str);
	$arrdata=$res;
	if(count($arrdata) > 0){
		foreach($arrdata as $val){
			try{
				$owlPDO->beginTransaction();
				
				$strx="select * from ".$dbname.".mscontractpurchase where ctrno='".$val['notransaksi']."'";
				$resx=fetchdata($strx);
				if(count($resx) <= 0){
					$data = array(
						'compcode'=>$arrcompmillcode[$val['unit']],
						'ctrno'=>$val['notransaksi'],
						'vendorcode'=>$val['kodesupplier'],
						'kodeproduk'=>$val['kodebarang'],
						'jenis'=>$val['jenis'],
						'tanggaldari'=>$val['tanggaldari'],
						'tanggalsampai'=>$val['tanggalsampai'],
						'ctrqty'=>$val['volume'],
						'sisactr'=>$val['volume'],
						'batasatas'=>$val['batasatas'],
						'bataskadaluwarsa'=>$val['bataskadaluwarsa'],
					);
					$cols = array();	
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$strt = insertQuery($dbname,'mscontractpurchase',$data,$cols);
					$owlPDO->exec($strt);
				}else{
					$data = array(
						'jenis'=>$val['jenis'],
						'tanggaldari'=>$val['tanggaldari'],
						'tanggalsampai'=>$val['tanggalsampai'],
						'ctrqty'=>$val['volume'],
						'batasatas'=>$val['batasatas'],
						'bataskadaluwarsa'=>$val['bataskadaluwarsa'],
					);
					$strt = updateQuery($dbname,'mscontractpurchase',$data,"ctrno='".$val['notransaksi']."'");
					$owlPDO->exec($strt);
				}
				
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
}

function stagesproductionorder(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	
	$pt="";
	$str="select * from ".$dbname.".mssystem limit 1";
	$res=fetchdata($str);
	$countdata=count($res);
	if($countdata > 0){
		$pt=$res[0]['compcode'];
		$millcode=$res[0]['millcode'];
	}
	
	## NEW
	$str="select * from ".$dbnameerp.".log_poht where stat_release='1' and closed='0' and tipepo in ('PO','NO') and keteranganclose not like '%Tutup By System%'";
	echo $str;
	$res=fetchdatax($str);
	$arrdata=$res;
	if($arrdata){
		foreach($arrdata as $val){
			try{
				$owlPDO->beginTransaction();
				
				$strx="select * from ".$dbname.".msproductionorder where no='".$val['nopo']."'";
				$resx=fetchdata($strx);
				if(!$resx){
					$data = array(
						'no'=>$val['nopo'],
						'unitcode'=>$val['kodeunit'],
						// 'tanggalmulai'=>$val['tanggalmulai'],
						// 'tanggalselesai'=>$val['tanggalselesai'],
					);
					$cols = array();	
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$strt = insertQuery($dbname,'msproductionorder',$data,$cols);
					$owlPDO->exec($strt);
				}else{
					$data = array(
						'unitcode'=>$val['kodeunit'],
						// 'tanggalmulai'=>$val['tanggalmulai'],
						// 'tanggalselesai'=>$val['tanggalselesai'],
					);
					$strt = updateQuery($dbname,'msproductionorder',$data,"no='".$val['nopo']."'");
					$owlPDO->exec($strt);
				}
				
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
}

function stagesgrading(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	
	## NEW
	$str="select * from ".$dbnameerp.".wb_5grading";
	$res=fetchdatax($str);
	$arrdata=$res;
	if(count($arrdata) > 0){
		foreach($arrdata as $val){
			try{
				$owlPDO->beginTransaction();
				
				$strx="select * from ".$dbname.".msgrading where kode='".$val['kode']."'";
				$resx=fetchdata($strx);
				if(count($resx) <= 0){
					## GET MAX NO URUT
					$strno="select max(nourut) as nourut from ".$dbname.".msgrading";
					$resno=fetchdata($strno);
					$nourut=($resno[0]['nourut']+1);
					
					$data = array(
						'kode'=>$val['kode'],
						'deskripsi'=>$val['deskripsi'],
						'jjg'=>$val['jjg'],
						'persen'=>$val['persen'],
						'kg'=>$val['kg'],
						'nourut'=>$nourut,
						'status'=>$val['status'],
					);
					$cols = array();	
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$strt = insertQuery($dbname,'msgrading',$data,$cols);
					$owlPDO->exec($strt);
				}else{
					$data = array(
						'deskripsi'=>$val['deskripsi'],
						'jjg'=>$val['jjg'],
						'persen'=>$val['persen'],
						'kg'=>$val['kg']
					);
					$strt = updateQuery($dbname,'msgrading',$data,"kode='".$val['kode']."'");
					$owlPDO->exec($strt);
				}
				
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
}

function stagessortasi(){
	global $dbname;
	global $dbnameerp;
	global $owlPDO;
	global $owlPDOERP;
	
	## NEW
	$str="select * from ".$dbnameerp.".wb_5sortasi";
	$res=fetchdatax($str);
	$arrdata=$res;
	if(count($arrdata) > 0){
		foreach($arrdata as $val){
			try{
				$owlPDO->beginTransaction();
				
				$strx="select * from ".$dbname.".mssortasi where kode='".$val['kode']."'";
				$resx=fetchdata($strx);
				if(count($resx) <= 0){
					## GET MAX NO URUT
					$strno="select max(nourut) as nourut from ".$dbname.".mssortasi";
					$resno=fetchdata($strno);
					$nourut=($resno[0]['nourut']+1);
					
					$data = array(
						'kode'=>$val['kode'],
						'deskripsi'=>$val['deskripsi'],
						'persen'=>$val['persen'],
						'kg'=>$val['kg'],
						'nourut'=>$nourut,
						'status'=>$val['status'],
					);
					$cols = array();	
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$strt = insertQuery($dbname,'mssortasi',$data,$cols);
					$owlPDO->exec($strt);
				}else{
					$data = array(
						'deskripsi'=>$val['deskripsi'],
						'persen'=>$val['persen'],
						'kg'=>$val['kg']
					);
					$strt = updateQuery($dbname,'mssortasi',$data,"kode='".$val['kode']."'");
					$owlPDO->exec($strt);
				}
				
				$owlPDO->commit();
			}catch (PDOException $e){
				$owlPDO->rollback();
				echo $e."##<br>";
				continue;
			}
		}
	}
}
?>