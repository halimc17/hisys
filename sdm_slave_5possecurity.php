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
        $query = selectQuery($dbname,"sdm_5possecurity","nopos");
        $id = fetchData($query);
        $maxid=1;
        if(!empty($id)) {
        foreach($id as $row) {
        $row['nopos']>=$maxid ? $maxid=$row['nopos'] : false;
        }
        $maxid++;
        }
        $data['nopos']=$maxid;
        $data['createdby']=$_SESSION['standard']['userid'];
        $data['createdtime']=date("Y-m-d H:i:s");
        #=============== Insert Process
        # Column
        $column = array('nopos','namapos','unit','status','createdby','createdtime','updateby','updatetime');
        # Query
        $query = insertQuery($dbname,'sdm_5possecurity',$data,$column);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        
        echo $data['nopos'];
        break;
    case 'edit':
        $params = $data;
        unset($params['nopos']);
        $query = updateQuery($dbname,'sdm_5possecurity',$params,"nopos='".$_POST['nopos']."'");
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        echo json_encode($data);
            
        break;
     default:
        break;
    }
?>