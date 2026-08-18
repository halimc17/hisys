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
        $query = selectQuery($dbname,"legal_5jenispreventiveprofile","nourut");
        $id = fetchData($query);
        $maxid=1;
        if(!empty($id)) {
        foreach($id as $row) {
        $row['nourut']>=$maxid ? $maxid=$row['nourut'] : false;
        }
        $maxid++;
        }
        $data['nourut']=$maxid;
        #=============== Insert Process
        # Column
        $column = array('nourut','jenis');
        # Query
        $query = insertQuery($dbname,'legal_5jenispreventiveprofile',$data,$column);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        
        echo $data['nourut'];
        break;
    case 'edit':
        $params = $data;
        unset($params['nourut']);
        $query = updateQuery($dbname,'legal_5jenispreventiveprofile',$params,"nourut='".$_POST['nourut']."'");
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        echo json_encode($data);
            
        break;
     default:
        break;
    }
?>