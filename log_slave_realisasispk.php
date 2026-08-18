<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses = $_GET['proses'];
$param = $_POST;

switch($proses) {
    # Daftar Header
    case 'showHeadList':
	$kdOrganisasi = $_SESSION['empl']['kodeorganisasi'];
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$where = "length(kodeorg)=4";
	}else if($_SESSION['empl']['tipelokasitugas']=='TRAKSI' or
	    $_SESSION['empl']['tipelokasitugas']=='KANWIL') {
	    $where = "length(kodeorg)=4 and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$kdOrganisasi."') ";
	} else {
	    $where = "kodeorg='".$_SESSION['empl']['lokasitugas']."'";
	}
	if(isset($param['where'])) {
	    $arrWhere =json_decode(str_replace('\\','',$param['where']),true);
	    if(!empty($arrWhere)) {
		foreach($arrWhere as $key=>$r1) {
		    $where .= " and ".$r1[0]." like '%".$r1[1]."%'";
		}
	    }
	}
	
	# Header
	$header = array(
	    $_SESSION['lang']['kebun'],
		$_SESSION['lang']['notransaksi'],
		$_SESSION['lang']['tanggal'],
		$_SESSION['lang']['subunit'],
		$_SESSION['lang']['koderekanan'],
		$_SESSION['lang']['nilaikontrak'],
		$_SESSION['lang']['matauang'],
		$_SESSION['lang']['jumlahrealisasi'],
		$_SESSION['lang']['status']
	);
	
	# Content
	$cols = "kodeorg,notransaksi,tanggal,divisi,koderekanan,nilaikontrak,matauang";
	$query = selectQuery($dbname,'log_spkht',$cols,$where." order by tanggal desc","",false,$param['shows'],$param['page']);
	$data = fetchData($query);
	// print_r($query);
	$totalRow = getTotalRow($dbname,'log_spkht',$where);
	foreach($data as $key=>$row) {
	    $data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
	    $data[$key]['nilaikontrak'] = number_format($row['nilaikontrak']);
            //=================ambil realisasi
            $data[$key]['realisasi'] =0;
            $strx="select sum(jumlahrealisasi) as test from ".$dbname.".log_baspk 
                  where notransaksi='".$data[$key]['notransaksi']."' and statusjurnal = '1'";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);
            while($barx=$resx->fetch())
            {
              @$data[$key]['realisasi']= number_format($barx['test']); 
            }   
            //lihat postingan-=============================
            $data[$key]['status'] ='';
            $strx="select statusjurnal from ".$dbname.".log_baspk 
                  where notransaksi='".$data[$key]['notransaksi']."' and statusjurnal=0";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_OBJ);
			if(owlBaris($resx)>0)
                $data[$key]['status'] ='?';
            else if($data[$key]['realisasi']==0 and $data[$key]['status']=='')
                $data[$key]['status'] ='?';
            else                
               $data[$key]['status'] ='Posted';
            
                
	}
        
        
	# Options
	if(!empty($data)) {
	    $whereSupp = "supplierid in (";
	    foreach($data as $key=>$row) {
	      if($key==0) {
		$whereSupp .= "'".$row['koderekanan']."'";
	      } else {
		$whereSupp .= ",'".$row['koderekanan']."'";
	      }
	    }
	    $whereSupp .= ")";
	} else {
	    $whereSupp = null;
	}
	$optSupp = makeOption($dbname,'log_5supplier','supplierid,namasupplier',
	    $whereSupp);
	
	# Data Show
	$dataShow = $data;
	foreach($dataShow as $key=>$row) {
		$dataShow[$key]['koderekanan'] = isset($optSupp[$row['koderekanan']])? $optSupp[$row['koderekanan']]: '';
		if($dataShow[$key]['divisi'] == ''){
			$dataShow[$key]['divisi'] = "Project";
		}else if($dataShow[$key]['divisi'] == 'S'){
			$dataShow[$key]['divisi'] = "Perumahan";
		}else{
			$dataShow[$key]['divisi'] = $dataShow[$key]['divisi'];
		}
	}
	
	# Make Table
	$tHeader = new rTable('headTable','headTableBody',$header,$data,$dataShow);
	$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
	#$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
	#$tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
	#$tHeader->_actions[1]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
	$tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
	$tHeader->addAction('viewdetail','View','images/'.$_SESSION['theme']."/zoom.png");
	#$tHeader->addAction('UploadFile','Upload','images/upload-2-xxl.png');
	$tHeader->_actions[1]->addAttr('event');
	$tHeader->_switchException = array('detailPDF');
	$tHeader->pageSetting($param['page'],$totalRow,$param['shows']);
	if(isset($param['where'])) {
	    $tHeader->setWhere($arrWhere);
	}
	
	# View
	$tHeader->renderTable();
	break;
    # Form Add Header
    case 'showAdd':
	// View
	echo formHeader('add',array());
	echo "<div id='detailField' style='clear:both'></div>";
	break;
    # Form Edit Header
    case 'showEdit':
        $where = "notransaksi='".$param['notransaksi']."' and kodeorg='".$param['kodeorg']."'";
	$query = selectQuery($dbname,'log_spkht',"*",$where);
        $tmpData = fetchData($query);
	$data = $tmpData[0];
	$data['tanggal'] = tanggalnormal($data['tanggal']);
	echo formHeader('edit',$data);
	echo "<div id='detailField' style='clear:both'></div>";
	break;
	
    # Proses Add Header
    case 'add':
	$data = $_POST;
	
	// Error Trap
	$warning = "";
	if($data['notransaksi']=='') {$warning .= "No SPK harus diisi\n";}
	if($data['tanggal']=='') {$warning .= "Tanggal harus diisi\n";}
	if($warning!=''){echo "Warning :\n".$warning;exit;}
	
	$data['tanggal'] = tanggalsystem($data['tanggal']);
	$cols = array('kodeorg','notransaksi','tanggal','divisi',
	    'koderekanan');
	$query = insertQuery($dbname,'log_spkht',$data,$cols);
	try{
		$owlPDO->exec($query); 
	}catch (PDOException $e){
		echo "Gagal : ".$e->getMessage();
	}
	break;
    # Proses Edit Header
    case 'edit':
	$data = $_POST;
	$where = "nopengolahan='".$data['nopengolahan']."'";
	unset($data['nopengolahan']);
	$data['tanggal'] = tanggalsystem($data['tanggal']);
	$query = updateQuery($dbname,'log_spkht',$data,$where);
	try{
		$owlPDO->exec($query); 
	}catch (PDOException $e){
		echo "Gagal : ".$e->getMessage();
	}
	break;
    case 'delete':
	$where = "notransaksi='".$param['notransaksi']."'";
	$query = "delete from `".$dbname."`.`log_spkht` where ".$where;
	try{
		$owlPDO->exec($query); 
	}catch (PDOException $e){
		echo "Gagal : ".$e->getMessage();
	}
	break;
    default:
	break;
}

function formHeader($mode,$data) {
    global $dbname;
    //print_r($data);
    //exit("Error");
    
    # Default Value
    if(empty($data)) {
	$data['kodeorg'] = '';
	$data['notransaksi'] = '0';
	$data['tanggal'] = '';
	$data['divisi'] = '';
	$data['koderekanan'] = '';
	$data['matauang'] = '';
    }
    
    # Disabled Primary
    if($mode=='edit') {
	$disabled = 'disabled';
    } else {
	$disabled = '';
    }
    
    # Options
    $whereOrg = "kodeorganisasi='".$data['kodeorg']."'";
    $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg);
    $whereDiv = "kodeorganisasi='".$data['divisi']."'";
    $optDiv = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereDiv,'0',true);
	$optDiv[''] = 'Project';
	$optDiv['S'] = 'Perumahan';
	$optDiv['P'] = 'Pabrikasi';
    $optSup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$data['koderekanan']."'");

         #khusus jika project
            if(substr($data['divisi'],0,2)=='AK' or substr($data['divisi'],0,2)=='PB'){
                $optDiv = makeOption($dbname,'project','kode,nama',"kode='".$data['divisi']."' and posting=0");
            }
            
    $els = array();
    $els[] = array(
	makeElement('kodeorg','label',$_SESSION['lang']['kebun']),
	makeElement('kodeorg','select',$data['kodeorg'],
	    array('style'=>'width:150px','disabled'=>'disabled'),$optOrg)
    );
    $els[] = array(
	makeElement('notransaksi','label',$_SESSION['lang']['notransaksi']),
	makeElement('notransaksi','text',$data['notransaksi'],
	    array('style'=>'width:150px','disabled'=>'disabled'))
    );
    $els[] = array(
	makeElement('tanggal','label',$_SESSION['lang']['tanggal']),
	makeElement('tanggal','text',$data['tanggal'],array('style'=>'width:150px',
	'disabled'=>'disabled'))
    );
    $els[] = array(
	makeElement('divisi','label',$_SESSION['lang']['subunit']),
	makeElement('divisi','select',$data['divisi'],
	    array('style'=>'width:150px','disabled'=>'disabled'),$optDiv)
    );
    $els[] = array(
	makeElement('koderekanan','label',$_SESSION['lang']['koderekanan']),
	makeElement('koderekanan','select',$data['koderekanan'],
            array('style'=>'width:150px','disabled'=>'disabled'),$optSup)
    );
	
	$els[] = array(
	makeElement('matauang','label',''),
	makeElement('matauang','hidden',$data['matauang'],
	    array('style'=>'width:150px','disabled'=>'disabled'))
    );
    
    return genElementMultiDim($_SESSION['lang']['header'],$els,2);
}
?>