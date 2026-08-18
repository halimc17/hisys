<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$param = $_POST;if(count($param)==0){$param = $_GET;}

try{
	$owlPDO->beginTransaction();
	
	$str="select notransaksi from ".$dbname.".wb where notransaksi='".$param['ticketno']."' and netto > '0'";
	$res=fetchdata($str);
	$jlhtiket=count($res);
	
	if($jlhtiket<=0){
		throw new PDOException("No. Tiket ".$param['notransaksi']." tidak terdaftar di sistem.");
	}
	
	$data = array(
		'deletecomment'=>$param['catatan'],
		'deletetime'=>date('Y-m-d H:i:s'),
		'deleteuser'=>$_SESSION['standard']['username']
	);
	$where = "notransaksi='".$param['ticketno']."'";
	$str = updateQuery($dbname,'wb',$data,$where);
	$owlPDO->exec($str);
	
	$str="insert into ".$dbname.".wb_log select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."'";
	$owlPDO->exec($str);

	
	##cek tiket punya kontrak jual atau tidak, jika punya balikin sisaqty nya sesuai netto
	$stry="select kontrakjual,kontrakjual2,netto,nettosplit,nettosplit2 from ".$dbname.".wb where notransaksi='".$param['ticketno']."' and kontrakjual!=''";
	$resy=fetchdata($stry);
	if ($resy) {
		if ($resy[0]['kontrakjual2']=='') {
			$str="update ".$dbname.".msso set sisaso=(sisaso+'".$resy[0]['netto']."') where noso='".$resy[0]['kontrakjual']."'";
			$owlPDO->exec($str);
		}else{
			$str="update ".$dbname.".msso set sisaso=(sisaso+'".$resy[0]['nettosplit']."') where noso='".$resy[0]['kontrakjual']."'";
			$owlPDO->exec($str);

			$str="update ".$dbname.".msso set sisaso=(sisaso+'".$resy[0]['nettosplit2']."') where noso='".$resy[0]['kontrakjual2']."'";
			$owlPDO->exec($str);
		}
	}
	##cek tiket punya kontrak jual atau tidak, jika punya balikin sisaqty nya sesuai netto


	##cek tiket sudah di upload ke server owl atau belum, jika sudah flag_trx rubah ke R
	$strx="select notransaksi from ".$dbname.".wb where notransaksi='".$param['ticketno']."' and FLAG='1'";
	$resx=fetchdata($strx);
	if ($resx) {
		$data = array(
			'waktukeluar'=>'',
			'netto'=>'0',
			'deletecomment'=>'',
			'deletetime'=>'',
			'deleteuser'=>'',
			'FLAG_TRX'=>'R'
		);
	}else{
		$data = array(
			'waktukeluar'=>'',
			'netto'=>'0',
			'deletecomment'=>'',
			'deletetime'=>'',
			'deleteuser'=>''
		);
	}
	##cek tiket sudah di upload ke server owl atau belum, jika sudah flag_trx rubah ke R


	$where = "notransaksi='".$param['ticketno']."'";
	$str = updateQuery($dbname,'wb',$data,$where);
	$owlPDO->exec($str);

	$str = deleteQuery($dbname,'trxsortasi',$where);
	$owlPDO->exec($str);

	$owlPDO->commit();
}catch (PDOException $e){
	$owlPDO->rollback();
	exit("Gagal, ".$e->getMessage());
}
?>
