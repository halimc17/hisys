<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;

switch($proses) {
    
        case'updtjam':
        //exit("jamawal :".$data['jamawal']);
        $jamawal=$data['jamawal'];
        $jamawal=explode(':',$jamawal);
        
        
        $jam1=$jamawal[0];
        $jam2=$jamawal[1];
        
        $jam2=$jam2/60;
        
        $jmbaru=$jam1+$jam2;
        $jmtot=$jmbaru+$Jam;
        
        
        $jmtot=number_format($jmtot,2);
        $jmtot = explode('.',$jmtot);
        
        $jmbr=$jmtot[0];
        $mntbr=$jmtot[1];
        $mntbr=number_format($mntbr/100*60);
        if($jmbr>=24){
            $jmbr=$jmbr-24;
        }
        
        $jmsl=addZero($jmbr,2).':'.addZero($mntbr,2);
        
        echo $jmsl;
        
        break;
        case 'add':
        $query = selectQuery($dbname,"sdm_5shiftsecurity","kodeshift");
        $id = fetchData($query);
        $maxid=1;
        if(!empty($id)) {
        foreach($id as $row) {
        $row['kodeshift']>=$maxid ? $maxid=$row['kodeshift'] : false;
        }
        $maxid++;
        }
        $data['kodeshift']=$maxid;
        $data['createdby']=$_SESSION['standard']['userid'];
        $data['createdtime']=date("Y-m-d H:i:s");
        #=============== Insert Process
        # Column
        $column = array('kodeshift','namashift','jamawal','jamakhir','createdby','createdtime');
        # Query
        $query = insertQuery($dbname,'sdm_5shiftsecurity',$data,$column);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        
        echo $data['kodeshift'];
        break;
    case 'edit':
        $params = $data;
        $data['updatedby']=$_SESSION['standard']['userid'];
        $data['updatedtime']=date("Y-m-d H:i:s");
        unset($params['kodeshift']);
        $query = updateQuery($dbname,'sdm_5shiftsecurity',$params,"kodeshift='".$_POST['kodeshift']."'");
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }

        $arName = getenum($dbname,"sdm_5shiftsecurity","namashift");
        $arShift = array('1'=>"Malam",'2'=>"Malam",'3'=>"Pagi",'4'=>"Libur");
        $optShift=array();
        $no=1;
        foreach ($arName as $key => $value) {
            $optShift[$value]=$value." - ".$arShift[$no];
            $no++;
        }
        $data['namashift']=$optShift[$data['namashift']];
        echo json_encode($data);
            
        break;
     default:
        break;
    }
?>