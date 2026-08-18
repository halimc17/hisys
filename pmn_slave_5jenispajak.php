<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;

switch($proses) {
    
        case 'add':
        $query = selectQuery($dbname,"pmn_5jenispajak","id");
        $id = fetchData($query);
        $maxid=1;
        if(!empty($id)) {
        foreach($id as $row) {
        $row['id']>=$maxid ? $maxid=$row['id'] : false;
        }
        $maxid++;
        }
        $data['id']=$maxid;
        #=============== Insert Process
        # Column
        $column = array('id','kodepajak','namapajak');
        # Query

        $query = insertQuery($dbname,'pmn_5jenispajak',$data,$column);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        
        echo $data['id'];
        break;
    case 'edit':
        unset($data['id']);
        $query = updateQuery($dbname,'pmn_5jenispajak',$data,"id='".$_POST['id']."'");
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        echo json_encode($data);
            
        break;
     default:
        break;
    }
?>