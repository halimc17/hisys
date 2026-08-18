<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

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
// foreach ($protected_table as $tab =>$tabl){	
	// if($tableName==$tabl){
	// require_once('lib/admin_validation.php');
	// }
// }
$IDs = $_POST['IDs'];
$id = explode("##",$IDs);
$data = $_POST;
$opt = json_decode(str_replace('##','"',$_POST['opt']),true);
unset($data['tableName']);
unset($data['IDs']);
unset($data['opt']);

# Create Condition
$where = "";

for($i=1;$i<count($id);$i++) {
    $tmpId = explode(",",$id[$i]);
    # Transform Tanggal
    $tmpStr = explode("-",$tmpId[1]);
    if(count($tmpStr)==3) {
	$tmpId[1] = tanggalsystemn($tmpId[1]);
    }
    $int = (int)$tmpId[1];
    if($i>1) {
		$where .= " AND ";
    }
	
	if((string)$int==$tmpId[1] and strlen((string)$int)==strlen($tmpId[1])) {
		$where .= "`".$tableName."`.`".$tmpId[0]."`=".$tmpId[1];
	} elseif(is_string($tmpId[1])) {
		$where .= "`".$tableName."`.`".$tmpId[0]."`='".$tmpId[1]."'";
	} else {
		$where .= "`".$tableName."`.`".$tmpId[0]."`=".$tmpId[1];
	}
}

# Update Query
$query = "update `".$dbname."`.`".$tableName."` set ";

$i=$int=0;
foreach($data as $key=>$row) {
    # Transform Tanggal
    $tmpStr = explode("-",$row);
	if(strlen($row)==10 and count($tmpStr)==3) $row = tanggalsystemn($row);
	
    $int = (int)$row;
	if($i>0) {
		$query .= ",";
	}
	
	if((string)$int==$row and strlen((string)$int)==strlen($row)) {
		$query .= "`".$tableName."`.`".$key."`=".$row;
	} elseif(is_string($row)) {
		$query .= "`".$tableName."`.`".$key."`='".$row."'";
	} else {
		$query .= "`".$tableName."`.`".$key."`=".$row;
	}
	
    $i++;
}

# Appy Condition
$query .= " where ".$where;


try{
          $owlPDO->exec($query);       
    # Update to Table
    echo "var currRow = document.getElementById('currRow').value;";
    foreach($data as $key=>$row) {
	if(isset($opt[$key])) {
	    $tmpCont = $opt[$key][$row];
	} else {
	    $tmpCont = $row;
	}
        echo "document.getElementById('".$key."_'+currRow).innerHTML = '".$tmpCont."';";
	echo "document.getElementById('".$key."_'+currRow).setAttribute('value','".$row."');";
    }
  }
  catch (PDOException $e) {
             print " Gagal  !: " . $e->getMessage() . "<br/>";
             die();
      }
?>