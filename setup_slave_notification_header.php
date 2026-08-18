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
		if($data['kodejenis']==''){
			 exit("Warning:kode jenis tidak boleh kosong");
		}

                // perubahan yang terjadi jika tipe jenis berbeda
                if($data['tipejenis']!='Default') {

                }


        #=============== Insert Process
        # Column
        $column = array('kodejenis','tipejenis','namajenis','sumberjenis',
            'status');

        # Query
        $query = insertQuery($dbname,'setup_notification_ht',$data,$column);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        
        echo $data['kodejenis'];
        break;
    case 'edit':
        $data = $_POST;
        unset($data['kodejenis']);
        
        $query = updateQuery($dbname,'setup_notification_ht',$data,"kodejenis='".$_POST['kodejenis']."'");
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
            
        break;
    case 'delete':
        $query = selectQuery($dbname,'setup_notification_dt','kodejenis',"kodejenis='".$data['kodejenis']."'");
        $res = fetchData($query);
        if(empty($res)) {
            $qDel = "delete from `".$dbname."`.`setup_notification_ht` where kodejenis='".$data['kodejenis']."'";
                try{$owlPDO->exec($qDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        } else {
            echo "Warning : Please delete detail notification in the first place";
            exit;
        }
        case 'loadHeader':
                #== Get Journal Header

                
                $query = selectQuery($dbname,"setup_notification_ht","*","","kodejenis asc");
                $resTab = fetchData($query);
                $table = "";
                foreach($resTab as $key=>$row) {
                        $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
                        $table .= "<td id='delHead_".$key."' colspan='2'>";
                        $table .= "<img src='images/".$_SESSION['theme']."/delete.png' ";
                        $table .= "class='zImgBtn' onclick='delHead(".$key.")'></td>";
                        foreach($row as $col=>$dat) {
                                if($col=='status') {
                                        if($dat == 1){
                                            $dat = "Aktif";
                                        }
                                        else{
                                            $dat = "Tidak Aktif";
                                        }
                                }
                        $table .= "<td id='".$col."_".$key."' onclick='passEditHeader(".$key.")'>".$dat."</td>";        
                        }
                        $table .= "</tr>";
                }
                echo $table;
                break;
    default:
        break;
}
?>