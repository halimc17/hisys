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
$datetimenow=date('Y-m-d H:i:s');

switch($method){
	case 'getkontrak':
        $optkontrak="<option value=''>Silahkan pilih</option>";

		## GET KONTRAK
		$str="select * from ".$dbname.".mscontractpurchase where vendorcode='".$param['supplier']."' and ctrstatus='1'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['so']==$val['ctrno']){
				$optkontrak.="<option value='".$val['ctrno']."' selected>".$val['ctrno']."</option>";				
			}else{
				$optkontrak.="<option value='".$val['ctrno']."'>".$val['ctrno']."</option>";				
			}
		}
        
        echo $optkontrak;
    break;
	
	case'timbang2':
		try{
			$owlPDO->beginTransaction();

			$where = "notransaksi='".$param['ticketno']."'";

			##cek dulu data nya sudah terupload ke erp belum, jika belum maka flag tetap N & 0
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
				'qr'=>$param['qrcode'],
				'kodebarang'=>$param['produk'],
				'supplier'=>$param['supplier'],
				'kontrakbeli'=>$param['so'],
				'transportir'=>$param['transportir'],
				'pemilik'=>$param['pemilik'],
				'nokendaraan'=>$param['nokendaraan'],
				'supir'=>$param['supir'],
				'nosim'=>$param['nosim'],
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'keterangan'=>$param['keterangan'],
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
		$arrhasil=$res[0];
		
		echo json_encode($arrhasil);
	break;
}
?>
