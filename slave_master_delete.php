<?php
require_once('master_validation.php');
require_once('config/connection.php');

# Get Parameter
$tableName = $_POST['tableName'];
$protected_table=Array(
	'setup_periodeakuntansi',
	'user',
	'auth',
	'setup_kegiatan',
	'setup_approval',
	'tipeakses',
	'organisasi',
	'menu',
	'bahasa',
	'datakaryawan',
	'keu_5akun',
	'sdm_daftarasset',
	'sdm_gaji',
	'setup_blok'
	);
foreach ($protected_table as $tab =>$tabl){	
	if($tableName==$tabl){
	require_once('lib/admin_validation.php');
	}
}
$data = $_POST;
unset($data['tableName']);

# Generate Condition
$where = "";
$i=0;
foreach($data as $key=>$row) {
	if($i==0){
		if(is_string($row)) {
			$where .= $key." = '".$row."'";
		} else {
			$where .= $key." = ".$row;
		}
	} else {
		if(is_string($row)) {
			$where .= " and ".$key." = '".$row."'";
		} else {
			$where .= " and ".$key." = ".$row;
		}
	}
	$i++;
}

# Generate Query
$query = "delete from ".$dbname.".".$tableName." where ".$where;
try{
          $owlPDO->exec($query);     
  }
  catch (PDOException $e) {
             print " Gagal  !: " . $e->getMessage() . "<br/>";
             die();
      }
?>