<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

# Get Parameter
$tableName = $_POST['tableName'];
$IDs = $_POST['IDs'];
$id = explode("##",$IDs);
$data = $_POST;
unset($data['tableName']);
unset($data['IDs']);
unset($data['tahuntanamCurr']);
unset($data['kelaspohon']);
unset($data['opt']);

# Create Condition
$where = "kodeorg='".$_POST['kodeorg'].
    "' and tahuntanam=".$_POST['tahuntanamCurr'];

# Update Query
$query = "update `".$dbname."`.`".$tableName."` set ";
$i=0;
foreach($data as $key=>$row) {
    # Transform Tanggal
    $tmpStr = explode("-",$row);
    if(count($tmpStr)==3) {
	$row = tanggalsystem($row);
    }
    
    $int = (int)$row;
    if($i==0) {
        if((string)$int==$row and strlen((string)$int)==strlen($row)) {
	    $query .= "`".$tableName."`.`".$key."`=".$row;
	} elseif(is_string($row)) {
            $query .= "`".$tableName."`.`".$key."`='".$row."'";
        } else {
            $query .= "`".$tableName."`.`".$key."`=".$row;
        }
    } else {
        if((string)$int==$row and strlen((string)$int)==strlen($row)) {
	   $query .= ","."`".$tableName."`.`".$key."`=".$row;
	} elseif(is_string($row)) {
            $query .= ","."`".$tableName."`.`".$key."`='".$row."'";
        } else {
            $query .= ","."`".$tableName."`.`".$key."`=".$row;
        }
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
		if($key=='luasareanonproduktif'){
			echo "document.getElementById('".$key."_'+currRow).innerHTML = '".number_format($row,2)."';";
			echo "document.getElementById('".$key."_'+currRow).setAttribute('value','".$row."');";
		}else if($key=='jumlahpokok') {
			echo "document.getElementById('".$key."_'+currRow).innerHTML = '".number_format($row,0)."';";
			echo "document.getElementById('".$key."_'+currRow).setAttribute('value','".$row."');";
		}else if($key=='bulanmulaipanen'){
			echo "document.getElementById('".$key."_'+currRow).innerHTML = '".numToMonth($row,$lang='E',$format='short')."';";
			echo "document.getElementById('".$key."_'+currRow).setAttribute('value','".$row."');";
		}else{
			echo "document.getElementById('".$key."_'+currRow).innerHTML = '".$row."';";
			echo "document.getElementById('".$key."_'+currRow).setAttribute('value','".$row."');";
		}
    }
}
 catch (PDOException $ex) {
             print " Gagal  !: " . $ex->getMessage() . "<br/>";
              die();
      }
?>