<?php
require_once('config/connection.php');
require_once('config/connectionstages.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
	
$datenow=date('Ymd');
$timenow=date('His');
$datetimenow=date('Y-m-d H:i:s');
$wheren=$wherer=$whered=" 1=1 ";
$wheren.=" and FLAG_TRX='N' and FLAG='0'";
$wherer.=" and FLAG_TRX='R' and FLAG='0'";
$whered.=" and FLAG_TRX='D' and FLAG='0'";
$bnsgroupcode="BSG";

## NEW
$str="select * from ".$dbnamebsp.".log_5masterbarang where flag='0'";
$arrdata=fetchdata($str);
if(count($arrdata) > 0){
	foreach($arrdata as $key=>$val){
		try{
			$owlPDO->beginTransaction();
			$owlPDOBSP->beginTransaction();
			
			$data[$no] = array(
				'kodeproduk'=>$val['kodebarang'],
				'namaproduk'=>$val['namabarang'],
				'satuan'=>$val['satuan'],
				'statusproduk'=> '1'
			);
				
			$cols = array();
			foreach($data[$no] as $key=>$row) {
				$cols[] = $key;
			}
			$str = insertQuery($dbname,'msproduk',$data[$no],$cols);
			$owlPDO->exec($str);
			
			$str="update ".$dbnamebsp.".log_5masterbarang set flag='1' where kodebarang='".$val['kodebarang']."'";
			$owlPDOBSP->exec($str);
			
			$owlPDO->commit();
			$owlPDOBSP->commit();
		}catch (PDOException $e){
			$owlPDOBSP->rollback();
			$owlPDO->rollback();
			echo $e."##<br>";
			continue;
		}
	}
}


?>