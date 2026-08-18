<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$str="select compcode,millcode,idwb from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['millcode'];
$compcode=$res[0]['compcode'];
$idwb=$res[0]['idwb'];

if (isset($param['product'])) {
	if($param['product']=='1'){
		$product=$kodeprodukcpo;
	}else{
		$product=$kodeprodukpk;
	}
}

$datetimenow=date('Y-m-d H:i:s');

switch($method){
	
	case 'getso':
        $optso="<option value=''>Silahkan pilih</option>";
        $opttransporter="<option value=''>Silahkan pilih</option>";
        $optkendaraan="<option value=''>Silahkan pilih</option>";
        $nokontrak="";
		$sisaso=0;

		## GET SO
		$str="select * from ".$dbname.".msso where sostatus='1' and compcode='".$compcode."' and custcode='".$param['customer']."' and kodeproduk='".$product."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['so']==$val['noso']){
				$optso.="<option value='".$val['noso']."' selected>".$val['noso']."</option>";				
				$nokontrak=$val['nosoinduk'];
				$sisaso=hidezerodecimal($val['sisaso'],2);
				$optnmtrp=makeOption($dbname,'msvendor','vendorcode,vendorname',"vendorcode='".$val['vendorcode']."' and vendorstatus='1' and transportir='1'");
				
				if(@$optnmtrp[$val['vendorcode']]!=''){
					$opttransporter.="<option value='".$val['vendorcode']."' selected>".$optnmtrp[$val['vendorcode']]."</option>";
					
					if($val['vendorcode']!=''){
						$strx="select vhccode from ".$dbname.".msvhc where vendorcode='".$val['vendorcode']."' and vhcstatus='1'";
						$resx=fetchdata($strx);
						foreach ($resx as $valx) {
							if($param['nokendaraan']==$valx['vhccode']){
								$optkendaraan.="<option value='".$valx['vhccode']."' selected>".$valx['vhccode']."</option>";				
							}else{
								$optkendaraan.="<option value='".$valx['vhccode']."'>".$valx['vhccode']."</option>";				
							}
						}
					}
				}
			}else{
				$optso.="<option value='".$val['noso']."'>".$val['noso']."</option>";				
			}
		}
		
		$arrhasil['listso']=$optso;
		$arrhasil['nokontrak']=$nokontrak;
		$arrhasil['sisaso']=$sisaso;
		$arrhasil['listtransportir']=$opttransporter;
		$arrhasil['listkendaraan']=$optkendaraan;
		
		echo json_encode($arrhasil);
    break;

    case 'getkendaraan':
        $optkendaraan="<option value=''>Silahkan pilih</option>";

		$str="select vhccode from ".$dbname.".msvhc where vendorcode='".$param['transportir']."' and vhcstatus='1'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['nokendaraan']==$val['vhccode']){
				$optkendaraan.="<option value='".$val['vhccode']."' selected>".$val['vhccode']."</option>";				
			}else{
				$optkendaraan.="<option value='".$val['vhccode']."'>".$val['vhccode']."</option>";				
			}
		}
		
		echo $optkendaraan;
    break;
	
	case 'getkualitas':
        $arrhasil=array();

		## GET KUALITAS
		$str="select * from ".$dbname.".mskualitas where kode='".$param['storage']."' and produk='".$product."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$arrhasil['ffa']=$res[0]['ffa'];
			$arrhasil['moist']=$res[0]['moist'];
			$arrhasil['dirt']=$res[0]['dirt'];
			$arrhasil['dobi']=$res[0]['dobi'];
		}
		
		echo json_encode($arrhasil);
    break;
	
	case 'getsambungso':
        $hasil="";

		## GET KUALITAS
		$str="select * from ".$dbname.".msso where noso='".$param['sambungso']."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			if($res[0]['nosoinduk']!=''){
				$hasil.="No. Kontrak : ".$res[0]['nosoinduk']." | ";
			}
			$hasil.="Sisa SO : ".hidezerodecimal($res[0]['sisaso'],3);
		}
		
		echo $hasil;
    break;
	
	case'timbang2':
		try{
			$owlPDO->beginTransaction();

			$where = "notransaksi='".$param['ticketno']."'";

			$str="select FLAG from ".$dbname.".wb where ".$where."";
			$res=fetchdata($str);
			if ($res[0]['FLAG']=='1') {
				$flag_trx='R';
				$flag='0';
			}else{
				$flag_trx='N';
				$flag='0';
			}
			##cek dulu data nya sudah terupload ke erp belum, jika belum maka flag tetap N & 0
			
			$data = array(
				'nokendaraan'=>$param['nokendaraan'],
				'supir'=>$param['supir'],
				'nosim'=>$param['nosim'],
				'notekirim'=>$param['keterangan'],
				'storage'=>$param['storage'],
				'ffa'=>str_replace(',','',$param['ffa']),
				'moist'=>str_replace(',','',$param['moist']),
				'dirt'=>str_replace(',','',$param['dirt']),
				'dobi'=>str_replace(',','',$param['dobi']),
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'updatetime'=>$datetimenow,
				'updateuser'=>$_SESSION['standard']['username'],
				'FLAG_TRX'=>$flag_trx,
				'FLAG'=>$flag,
			);
			$str = updateQuery($dbname,'wb',$data,$where);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			exit("Gagal, ".$e->getMessage());
		}
	break;
	
	case'showedit':
		$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."'";
		$res=fetchdata($str);
		$res[0]['waktumasuk']=tanggalnormald($res[0]['waktumasuk']);
		if($res[0]['kodebarang']==$kodeprodukcpo){
			$res[0]['produk']='1';
		}else{
			$res[0]['produk']='2';
		}
		$optstorage="<option value=''>Silahkan pilih</option>";
		$strx="select * from ".$dbname.".mskualitas where produk='".$res[0]['kodebarang']."' and status='1'";
		$resx=fetchdata($strx);
		foreach($resx as $val){
			$optstorage.="<option value='".$val['kode']."'>Storage ".$val['kode']."</option>";
		}
		$res[0]['storage']=$optstorage;
		
		
		$optso="<option value=''>Silahkan pilih</option>";
		$strx="select * from ".$dbname.".msso where custcode='".$res[0]['customer']."' and kodeproduk='".$res[0]['kodebarang']."' and noso!='".$res[0]['kontrakjual']."' and sostatus='1' and sisaso > '0'";
		$resx=fetchdata($strx);
		foreach($resx as $valx){
			$optso.="<option value='".$valx['noso']."'>".$valx['noso']."</option>";					
		}
		$res[0]['sambungso']=$optso;
		
		$arrhasil=$res[0];
		
		echo json_encode($arrhasil);
	break;
}

function getsisaso($id){
	global $dbname;
	
	$sisaso=0;
	
	$str="select sisaso from ".$dbname.".msso where noso='".$id."'";
    $res=fetchdata($str);
	$sisaso=$res[0]['sisaso'];
	
	return $sisaso;
}
?>
