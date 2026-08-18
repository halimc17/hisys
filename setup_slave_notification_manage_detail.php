<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = $_GET['proses'];
$param = $_POST;


switch($proses) {
    case 'add':
	# Search No urut
	$selQuery = selectQuery($dbname,'setup_notification_dt','nourut');
	$id = fetchData($selQuery);
	$maxid = 1;
	if(!empty($id)) {
	    foreach($id as $row) {
		$row['nourut']>=$maxid ? $maxid=$row['nourut'] : false;
	    }
	    $maxid++;
	}

	
//	$cols = array('nourut','noakun','keterangan','jumlah','matauang','kurs','noaruskas',
//	    'kodekegiatan','kodeasset','kodebarang','nik','kodecustomer',
//	    'kodesupplier','kodevhc','nodok','nojurnal','tanggal','kodeorg');


	$data = $param;
	
	$data['nourut'] = $maxid;
        
       
	#periksa apakah tipe global atau tidak
       
        if($data['tipe']== 1)
        {
			$data['karyawanid']="";
        	if($data['kodedepartement']=='' and $data['kodetipekaryawan']=='' and $data['kodejabatan']== '')
        	{
            	exit("[ Error ]: Salah Satu dari Departement, Tipe Karyawan atau Jabatan harus dilengkapi");
        	}
        }
        else {
			$data['kodedepartement']="";
			$data['kodetipekaryawan']="";
			$data['kodejabatan']="";
        	if($data['karyawanid']=='')
        	{
        		exit("[ Error ]: Karyawan harus dilengkapi.");
        	}
        }
       

    $cols = array('nourut','kodejenis','karyawanid','kodedepartement','kodetipekaryawan','kodejabatan','tipe');
	$dis=array($data['nourut'],$data['kodejenis'],$data['karyawanid'],$data['kodedepartement'],$data['kodetipekaryawan'],$data['kodejabatan'],$data['tipe']);
	#cek apakah header status aktif atau tidak
	//exit('error :'.$data['kodejenis']);
	$str="select status from ".$dbname.".setup_notification_ht where kodejenis='".$data['kodejenis']."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$status=$bar['status'];
		
	if($status!=1){
		exit("Warning:Data Notification header tidak diaktifkan, silahkan aktifkan terlebih dahulu");
	}
		 
        
	$query = insertQuery($dbname,'setup_notification_dt',$dis,$cols);

	//exit("Error:".$query);
	try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  ! Insert: " . $e->getMessage() . "<br/>"; die(); }
	

	$res = "";
	foreach($dis as $cont) {
	    $res .= "##".$cont;
    //print_r("masuk");
	}
	//exit ("res :".$res);
	$result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
	echo $result;
	break;
	
	
	
	
	
	
    case 'edit':
        
	$data = $param;
	// echo"<pre>";
	// print_r($data);
	// echo"</pre>";
	// exit("Error:A");
        
    #periksa apakah tipe global atau tidak
       
        if($data['tipe']== 1)
        {
			$data['karyawanid'] = "";
        	if($data['kodedepartement']=='' and $data['kodetipekaryawan']=='' and $data['kodejabatan']== '')
        	{
            	exit("[ Error ]: Salah Satu dari Departement, Tipe Karyawan atau Jabatan harus dilengkapi");
        	}
        }
        else {
			$data['kodedepartement']="";
			$data['kodetipekaryawan']="";
			$data['kodejabatan']="";
        	if($data['karyawanid']=='')
        	{
        		exit("[ Error ]: Karyawan harus dilengkapi.");
        	}
        }
       
         
		
	#cek apakah header status aktif atau tidak
	//exit('error :'.$data['kodejenis']);
	$str="select status from ".$dbname.".setup_notification_ht where kodejenis='".$data['kodejenis']."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$status=$bar['status'];
		
	if($status!=1){
		exit("Warning:Data Notification header tidak diaktifkan, silahkan aktifkan terlebih dahulu");
	}

	$where = "nourut='".$param['nourut']."' and kodejenis='".$param['kodejenis']."'";
	$cols = array('nourut','kodejenis','karyawanid','kodedepartement','kodetipekaryawan','kodejabatan','tipe');
	$dis=array('nourut' => $data['nourut'],'kodejenis' => $data['kodejenis'], 'karyawanid' => $data['karyawanid'], 'kodedepartement' => $data['kodedepartement'], 'kodetipekaryawan' => $data['kodetipekaryawan'], 'kodejabatan' => $data['kodejabatan'], 'tipe' => $data['tipe']);
	$query = updateQuery($dbname,'setup_notification_dt',$dis,$where);
	try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
	echo json_encode($param);
	break;


    case 'delete':
	$where = "nourut='".$param['nourut']."' and kodejenis='".$param['kodejenis']."'";
	$query = "delete from `".$dbname."`.`setup_notification_dt` where ".$where;
	//exit("Error : ".$query);
	try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
	break;
    default:
    break;
}
?>