<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/zGrid.php');
require_once('lib/formTable.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;


switch($proses) {
    case 'show':
	$ids = $_POST;
    //exit('error :');
	
	$optDepartement= makeOption($dbname,'sdm_5departemen','kode,nama',
	    "",'2',true);
	$optTipeKaryawan= makeOption($dbname,'sdm_5tipekaryawan','id,tipe',
	    "",'2',true);
	$optJabatan= makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',
	    "",'2',true);
	$optTipe = array("1"=>"Global","0"=>"Non Global");
	#dialihkan ke aktiva dalam konstruksi
	#$optAsset = makeOption($dbname,'sdm_daftarasset','kodeasset,namasset',$whereAsset,'2',true);
	//$optAsset = makeOption($dbname,'project','kode,nama',$whereAsset,'2',true);
	
	$optKaryawan['']='';
	$str=" select a.karyawanid,b.namakaryawan,b.nik from ".$dbname.".user a left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid where status= '1' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optKaryawan[$bar['karyawanid']]=$bar['nik'].' - '.$bar['namakaryawan'];	
	}
	
	
	
	
	# Get Data
	$cols = array('nourut','kodejenis','karyawanid','kodedepartement','kodetipekaryawan','kodejabatan','tipe');
	$where = "kodejenis='".$ids['kodejenis']."'";
	$query = selectQuery($dbname,'setup_notification_dt',$cols,$where,"nourut asc");
	$data = fetchData($query);
	
	
	# Replace Code with Name
	$dataShow = $data;
	// echo"<pre>";
	// print_r($dataShow);
	// echo"</pre>";
	$no=0;
	foreach($dataShow as $key=>$row) {
		$no++;
	    $dataShow[$key]['nourut'] = $no;
	    $dataShow[$key]['kodejenis'] = $row['kodejenis'];
	    $dataShow[$key]['karyawanid'] = $optKaryawan[$row['karyawanid']];
	    $dataShow[$key]['kodedepartement'] = $optDepartement[$row['kodedepartement']];
	    $dataShow[$key]['kodetipekaryawan'] = $optTipeKaryawan[$row['kodetipekaryawan']];
	    $dataShow[$key]['kodejabatan'] = $optJabatan[$row['kodejabatan']];
	    $dataShow[$key]['tipe'] = $optTipe[$row['tipe']];
	}
	
	# Form
	$theForm = new uForm('notificationForm','Form Notification Detail',2);
	$theForm->addEls('nourut',$_SESSION['lang']['nourut'],'0','textnum','R',10);
	$theForm->_elements[0]->_attr['disabled'] = 'disabled';
	$theForm->addEls('kodejenis',$_SESSION['lang']['kode'].' '.$_SESSION['lang']['jenis'],$ids['kodejenis'],'text','L',10);
	$theForm->_elements[1]->_attr['disabled'] = 'disabled';
	$theForm->addEls('karyawanid',$_SESSION['lang']['nama'].' '.$_SESSION['lang']['karyawan'],'','selectsearch','L',30,$optKaryawan);
	$theForm->addEls('kodedepartement','Departement','','selectsearch','L',30,$optDepartement);
	$theForm->addEls('kodetipekaryawan',$_SESSION['lang']['tipe'].' '.$_SESSION['lang']['karyawan'],'','selectsearch','L',30,$optTipeKaryawan);
	$theForm->addEls('kodejabatan',$_SESSION['lang']['jabatan'],'','selectsearch','L',30,$optJabatan);
	$theForm->addEls('tipe',$_SESSION['lang']['tipe'],'','select','L',30,$optTipe);
	
	
	# Table
	$theTable = new uTable('notificationTable','Tabel Notification Detail',"",$data,$dataShow);
	
	# FormTable
	@$formTab = new uFormTable('ftNotificationDt',$theForm,$theTable,null,'nourut');
	@$formTab->_target = "setup_slave_notification_manage_detail";
	@$formTab->_defValue = '##nourut=0';
	@$formTab->_noEnable = '##nourut##kodejenis';
	@$formTab->_afterCrud = "loadHeader";
	@$formTab->_noClearField = '##kodejenis';
	@$formTab->render();
	break;
    default:
	break;
}
?>