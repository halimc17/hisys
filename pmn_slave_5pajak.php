<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;

switch($proses) {
    
    case 'getpenghasilan' :

        $arrayPenghasilan=makeOption($dbname,"pmn_5jenispenghasilan","idpenghasilan,namapenghasilan","kodepajak='".$data['jenispph']."'");
        $optPenghasilan='';
        foreach ($arrayPenghasilan as $key => $value) {
            $selected = "";
            if($data['value'] == $key){
                $selected = "selected";
            }
            $optPenghasilan.="<option value=".$key." ".$selected.">".$value."</option>";
        }

        echo $optPenghasilan;

    break;
    case 'add':
        
        $query = selectQuery($dbname,"pmn_5pajak","id");
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
        $column = array('id','jenispph','carapembayaran','jenispenghasilan');
        //unset($data['id']);
        # Query
        $query = insertQuery($dbname,'pmn_5pajak',$data,$column);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        
        echo $data['id'];
        break;
    case 'edit':
        $data = $_POST;
        unset($data['id']);
        
        $query = updateQuery($dbname,'pmn_5pajak',$data,"id='".$_POST['id']."'");
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }

        echo json_encode($data);
            
        break;
    case 'delete':
        $query = selectQuery($dbname,'pmn_5pajak','id',"id='".$data['id']."'");
        $res = fetchData($query);
        if(!empty($res)) {
            $qDel = "delete from `".$dbname."`.`pmn_5pajak` where id='".$data['id']."'";
                try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        } else {
            echo "Warning : ".$query." ";
            exit;
        }
        break;
    
    default:
        break;
    }
?>