<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

$method = checkPostGet('method','');
$namauser = checkPostGet('namauser','');
$org = checkPostGet('org','');

switch($method) 
{
	case'getorg':
		$hasilorg = "";
		$str = "select * from ".$dbname.".user_orgdetail where namauser='".$namauser."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$hasilorg.="####" . $bar['kodeorganisasi'];
		}
		echo $hasilorg;
	break;
	
	case'simpan':
		$str = "delete from ".$dbname.".user_orgdetail where namauser='".$namauser."'";
		try 
		{
			$owlPDO->exec($str);
			
			$exporg = explode("####", $org);
			foreach ($exporg as $key) 
			{
				$str = "insert into ".$dbname.".user_orgdetail (id_orgdetail,namauser,kodeorganisasi) values ('','".$namauser."','".$key."')";
				try 
				{
					$owlPDO->exec($str);
				} 
				catch (PDOException $e) 
				{
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
        } 
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
	
	default:
	break;
}
?>