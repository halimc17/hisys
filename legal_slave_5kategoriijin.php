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
        
        #=============== Insert Process
        # Column
        $column = array('kodekategori','namakategori');
        # Query
        $query = insertQuery($dbname,'legal_5kategoriijin',$data,$column);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        
        echo $data['kodekategori'];
        break;
    case 'edit':
        unset($data['kodekategori']);
        $query = updateQuery($dbname,'legal_5kategoriijin',$data,"kodekategori='".$_POST['kodekategori']."'");
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        echo json_encode($data);
            
        break;
     default:
        break;
    }
?>