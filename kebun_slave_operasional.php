<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

$proses = $_GET['proses'];
$param = $_POST;

// Total Summary / Allocation Control
$total = "<fieldset style='height:114px'><legend><b>Total</b></legend>";
$total .= "<table>";
$total .= "<tr>";
$total .= "<td colspan='2'><b>".$_SESSION['lang']['prestasi']."</b></td>";
$total .= "<td colspan='2'><b>".$_SESSION['lang']['absensi']."</b></td>";
$total .= "</tr>";
$total .= "<tr>";
$total .= "<td>".$_SESSION['lang']['jumlahhk']."</td>";
$total .= "<td>".makeElement('totalPresHk','textnum',0,
    array('style'=>'width:70px','disabled'=>'disabled','realValue'=>0))."</td>";
$total .= "<td>".$_SESSION['lang']['jumlahhk']."</td>";
$total .= "<td>".makeElement('totalAbsHk','textnum',0,
    array('style'=>'width:70px','disabled'=>'disabled','realValue'=>0))."</td>";
$total .= "</tr>";
$total .= "<tr>";
$total .= "<td>".$_SESSION['lang']['umr']."</td>";
$total .= "<td>".makeElement('totalPresUmr','textnum',0,
    array('style'=>'width:70px','disabled'=>'disabled','realValue'=>0))."</td>";
$total .= "<td>".$_SESSION['lang']['umr']."</td>";
$total .= "<td>".makeElement('totalAbsUmr','textnum',0,
    array('style'=>'width:70px','disabled'=>'disabled','realValue'=>0))."</td>";
$total .= "</tr>";
$total .= "<tr>";
$total .= "<td>".$_SESSION['lang']['insentif']."</td>";
$total .= "<td>".makeElement('totalPresIns','textnum',0,
    array('style'=>'width:70px','disabled'=>'disabled','realValue'=>0))."</td>";
$total .= "<td>".$_SESSION['lang']['insentif']."</td>";
$total .= "<td>".makeElement('totalAbsIns','textnum',0,
    array('style'=>'width:70px','disabled'=>'disabled','realValue'=>0))."</td>";
$total .= "</tr></table>";
$total .= makeElement('tmpValHk','hidden',0);
$total .= makeElement('tmpValUmr','hidden',0);
$total .= makeElement('tmpValIns','hidden',0);
$total .= "</fieldset>";

switch($proses) {
    # Daftar Header
    case 'showHeadList':
	if(isset($param['where'])) {
	    $tmpW = str_replace('\\','',$param['where']);
	    $arrWhere = json_decode($tmpW,true);
	    $where = "1=1";
	    if(!empty($arrWhere)) {
			foreach($arrWhere as $key=>$r1) {
				// if($key==0) {
					// $where .= $r1[0]." like '%".$r1[1]."%'";
				// } else {
					// $where .= " and ".$r1[0]." like '%".$r1[1]."%'";
				// }
				if($r1[0]=='tanggal'){
					$tanggal1 = $r1[1];
				} 
				elseif($r1[0]=='tanggal2') {
					$tanggal2 = $r1[1];
				}
				elseif($r1[0]=='divisi') {
					$divisi = $r1[1];
				}
				elseif($r1[0]=='tipetransaksi') {
					$where .= " and ".$r1[0]." = '".$r1[1]."'";
				}
				else{
					$where .= " and ".$r1[0]." like '%".$r1[1]."%'";
				}
				if($r1[0]=='tipetransaksi'){
					$param['tipe']=$r1[1];
				}
			}
			
			//exit("Error:$where");
			
			if(!empty($tanggal1) and !empty($tanggal2)) {
					$where.=" and tanggal between '".$tanggal1."' and '".$tanggal2."' ";
			} elseif(!empty($tanggal1)) {
					$where.=" and tanggal >= '".$tanggal1."'";
			} elseif(!empty($tanggal2)) {
					$where.=" and tanggal <= '".$tanggal2."'";
			}
			
			if(!empty($divisi)){
				$where.=" and notransaksi in (select notransaksi from ".$dbname.".kebun_prestasi where kodeorg like '".$divisi."%')";
			}
			
	    } else {
			$where = null;
	    }
	} else {
	    $where = null;
	}
	//exit("Error:$where");
	//echo $where;
	# Header
        if($param['tipe']=='PNN'){
            $header = array(
                $_SESSION['lang']['nomor'],'No. BKM',$_SESSION['lang']['organisasi'],$_SESSION['lang']['tanggal'],$_SESSION['lang']['nikmandor'],$_SESSION['lang']['nikmandor1'],$_SESSION['lang']['keraniproduksi'],$_SESSION['lang']['keranimuat'],'updateby'
            );
        }
        else
        {
            $header = array(
                $_SESSION['lang']['nomor'],'No. BKM',$_SESSION['lang']['organisasi'],$_SESSION['lang']['tanggal'],$_SESSION['lang']['nikmandor'],$_SESSION['lang']['nikmandor1'],$_SESSION['lang']['asisten'],$_SESSION['lang']['kerani'],'updateby'
            );            
        }   
	
	# Content
	if(is_null($where)) {
            //tambahan jamhari
            if($_SESSION['empl']['subbagian']==''){
                $where = "kodeorg='".$_SESSION['empl']['lokasitugas']."'";
            }else if($_SESSION['empl']['subbagian']!='' and $_SESSION['empl']['tipekaryawan']==0){
                $where = "kodeorg='".$_SESSION['empl']['lokasitugas']."' and notransaksi in (select notransaksi from ".$dbname. ".kebun_prestasi where kodeorg like '".$_SESSION['empl']['subbagian']."%')";
            }else{
                 $where = "kodeorg='".$_SESSION['empl']['lokasitugas']."' and updateby='".$_SESSION['standard']['userid']."'";
			}
	} else {
            //tambahan jamhari
            if($_SESSION['empl']['subbagian']==''){
                $where .= " and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
            }else if($_SESSION['empl']['subbagian']!='' and $_SESSION['empl']['tipekaryawan']==0){
                $where.=" and kodeorg='".$_SESSION['empl']['lokasitugas']."' and notransaksi in (select notransaksi from ".$dbname. ".kebun_prestasi where kodeorg like '".$_SESSION['empl']['subbagian']."%')";
			}else{
                $where .= " and kodeorg='".$_SESSION['empl']['lokasitugas']."' and updateby='".$_SESSION['standard']['userid']."'";
            }
	}
	$where .= " and notransaksi not like '%BOR%' ";
	
	$cols = "notransaksi,nobkm,kodeorg,tanggal,nikmandor,nikmandor1,nikasisten,keranimuat,jurnal,updateby";
	$query = selectQuery($dbname,'kebun_aktifitas',$cols,$where,
	    "tanggal desc, notransaksi desc",false,$param['shows'],$param['page']);
	#exit("error".$query);
	$data = fetchData($query);
	$totalRow = getTotalRow($dbname,'kebun_aktifitas',$where);
	if(!empty($data)) {
	    $whereKarRow = "";
	    $notFirst = false;
	    foreach($data as $key=>$row) {
		if($row['jurnal']==1) {
		    $data[$key]['switched']=true;
		}
		$data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
		unset($data[$key]['jurnal']);
		
		if($notFirst==false) {
		    if($row['nikmandor']!='') {
			$whereKarRow .= $row['nikmandor'];
			$notFirst=true;
		    }
		    if($row['nikmandor1']!='') {
			if($notFirst==false) {
			    $whereKarRow .= $row['nikmandor1'];
			    $notFirst=true;
			} else {
			    $whereKarRow .= ",".$row['nikmandor1'];
			}
		    }
		    if($row['nikasisten']!='') {
			if($notFirst==false) {
			    $whereKarRow .= $row['nikasisten'];
			    $notFirst=true;
			} else {
			    $whereKarRow .= ",".$row['nikasisten'];
			}
		    }
		    if($row['keranimuat']!='') {
			if($notFirst==false) {
			    $whereKarRow .= $row['keranimuat'];
			    $notFirst=true;
			} else {
			    $whereKarRow .= ",".$row['keranimuat'];
			}
		    }
                     if($row['updateby']!='') {
                        if($notFirst==false) {
                        $whereKarRow .= $row['updateby'];
                        $notFirst=true;
                        } else {
                        $whereKarRow .= ",".$row['updateby'];
                        }
                    }
		} else {
		    if($row['nikmandor']!='') {
			if($notFirst==false) {
			    $whereKarRow .= $row['nikmandor'];
			    $notFirst=true;
			} else {
			    $whereKarRow .= ",".$row['nikmandor'];
			}
		    }
		    if($row['nikmandor1']!='') {
			if($notFirst==false) {
			    $whereKarRow .= $row['nikmandor1'];
			    $notFirst=true;
			} else {
			    $whereKarRow .= ",".$row['nikmandor1'];
			}
		    }
		    if($row['nikasisten']!='') {
			if($notFirst==false) {
			    $whereKarRow .= $row['nikasisten'];
			    $notFirst=true;
			} else {
			    $whereKarRow .= ",".$row['nikasisten'];
			}
		    }
		    if($row['keranimuat']!='') {
			if($notFirst==false) {
			    $whereKarRow .= $row['keranimuat'];
			    $notFirst=true;
			} else {
			    $whereKarRow .= ",".$row['keranimuat'];
			}
		    }
                    if($row['updateby']!='') {
                        if($notFirst==false) {
                        $whereKarRow .= $row['updateby'];
                        $notFirst=true;
                        } else {
                        $whereKarRow .= ",".$row['updateby'];
                        }
                    }
		}
	    }
	    if(!empty($whereKarRow)) {
			$whereKarRow = "karyawanid in (".$whereKarRow.")";
		}
	} else {
	    $whereKarRow = "";
	}
	$optKarRow = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whereKarRow);
	$optNik = makeOption($dbname,'datakaryawan','karyawanid,nik',$whereKarRow);
	
	# Data Show
	$dataShow = $data;
	foreach($dataShow as $key=>$row) {
	    isset($optKarRow[$row['nikmandor']]) ? $dataShow[$key]['nikmandor'] = $optKarRow[$row['nikmandor']]." (".$optNik[$row['nikmandor']].")":null;
	    isset($optKarRow[$row['nikmandor1']]) ? $dataShow[$key]['nikmandor1'] = $optKarRow[$row['nikmandor1']]." (".$optNik[$row['nikmandor1']].")":null;
	    isset($optKarRow[$row['nikasisten']]) ? $dataShow[$key]['nikasisten'] = $optKarRow[$row['nikasisten']]." (".$optNik[$row['nikasisten']].")":null;
	    isset($optKarRow[$row['keranimuat']]) ? $dataShow[$key]['keranimuat'] = $optKarRow[$row['keranimuat']]." (".$optNik[$row['keranimuat']].")":null;
            isset($optKarRow[$row['updateby']]) ? $dataShow[$key]['updateby'] = $optKarRow[$row['updateby']]." (".$optNik[$row['updateby']].")":null;
	}
	
	# Posting --> Jabatan
	if($param['tipe']=='PNN') {
	    $app = 'panen';
	} else {
	    $app = 'rawatkebun';
	}
	$postJabatan = getPostingJabatan($app);
	
	# Make Table
	$tHeader = new rTable('headTable','headTableBody',$header,$data,$dataShow);
        $tHeader->_printAttr = array($param['tipe']);
	#$tHeader->addAction('showDetail','Detail','images/'.$_SESSION['theme']."/detail.png");
	$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
	$tHeader->_actions[0]->addAttr($param['tipe']);
	
	
	$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");

	

	// if($_SESSION['empl']['bagian']=='IT' || $_SESSION['empl']['kodejabatan']=='98'){
		// $tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
		// //$tHeader->addAction('','','images/'.$_SESSION['theme']."/delete.png");
	// }else{
		// $tHeader->addAction('','','images/'.$_SESSION['theme']."/delete.png");
	// }
	#$tHeader->addAction('approveData','Approve','images/'.$_SESSION['theme']."/approve.png");
	$tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
	$tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
	if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
	    $tHeader->_actions[2]->_name='';
	}
	//if($param['tipe']!='PNN') {
	    $tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
	    $tHeader->_actions[3]->addAttr('event');
	    $tHeader->_actions[3]->addAttr($param['tipe']);
	    
            
		$tHeader->addAction('detailData','Print Data Detail','images/'.$_SESSION['theme']."/zoom.png");
	    $tHeader->_actions[4]->addAttr('event');
	    $tHeader->_actions[4]->addAttr($param['tipe']);
            
		if($param['tipe']=='PNN') {
			$tHeader->addAction('detailExcel','Print Data Detail','images/excel.jpg');
			$tHeader->_actions[5]->addAttr('event');
			$tHeader->_actions[5]->addAttr((isset($tipeVal) ? $tipeVal : ""));
		}
		
		$tHeader->_switchException = array('detailPDF','detailData','detailExcel');
		//$tHeader->_switchException = array();
	//}
	
	$tHeader->pageSetting($param['page'],$totalRow,$param['shows']);
	$tHeader->setWhere($arrWhere);
	
	
	# View
	$tHeader->renderTable();
	break;
	
	
	
    # Form Add Header
    case 'showAdd':
	// View
	echo formHeader('add',$_POST['tipe'],array());
	if($param['tipe']!='PNN') {
	    echo $total;
	}
	echo "<div id='detailField' style='clear:both'></div>";
	break;
    # Form Edit Header
    case 'showEdit':
	$query = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".$param['notransaksi']."'");
	$tmpData = fetchData($query);
	$data = $tmpData[0];
	$data['tanggal'] = tanggalnormal($data['tanggal']);
	setIt($_SESSION['tmp']['kebun']['tipeTrans'],'PNN');
	echo formHeader('edit',$_SESSION['tmp']['kebun']['tipeTrans'],$data);
	if($param['tipe']!='PNN') {
	    echo $total;
	}
	echo "<div id='detailField' style='clear:both'></div>";
	break;
	
    # Proses Add Header
    case 'add':
	# Blank field validation
	$data = $_POST;
	
	
	if($data['tanggal']=='') {
	    echo "Validation Error : Date must not empty";
	    break;
	}
	
	if($data['nobkm']=='') {
	    echo "Validation Error : No. BKM must not empty";
	    break;
	}
	
	#= cek apakah no bkm terdaftar
	$str = "select count(*) as jumlah from " . $dbname . ".kebun_nobkm where nobkm ='".$data['nobkm']."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$cbkm=$bar['jumlah'];
	if($cbkm<1){
		exit("Warning:No. BKM belum terdaftar");
		
	}
	
	
	
	#mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
                    $sekarang=  tanggalsystem($data['tanggal']);
                    if($sekarang<$_SESSION['org']['period']['start']){
	    echo "Validation Error : Date out or range";
	    break;                        
                    }
                  #======================================================        
	# Data Capture & Reform
	$data['tipetransaksi'] = $_GET['tipe'];
	$data['tanggal'] = tanggalsystem($data['tanggal']);
	
	#=== Generate No Transaksi
	# Get Existing Data
	$fWhere = "tanggal='".$data['tanggal']."' and kodeorg='".$data['kodeorg'].
	    "' and tipetransaksi='".$data['tipetransaksi']."'";
	$fQuery = selectQuery($dbname,'kebun_aktifitas','notransaksi',$fWhere);
	$tmpNo = fetchData($fQuery);
	
	# Generate No Transaksi
	if(count($tmpNo)==0) {
	    $data['notransaksi'] = $data['tanggal']."/".$data['kodeorg']."/".
		$data['tipetransaksi']."/001";
	} else {
	    # Get Max No Urut
	    $maxNo = 1;
	    foreach($tmpNo as $row) {
		$tmpRow = explode('/',$row['notransaksi']);
		$noUrut = (int)$tmpRow[3];
		if($noUrut>$maxNo)
		    $maxNo = $noUrut;
	    }
	    $currNo = addZero($maxNo+1,3);
	    $data['notransaksi'] = $data['tanggal']."/".$data['kodeorg']."/".
		$data['tipetransaksi']."/".$currNo;
	}
	$data['updateby']=$_SESSION['standard']['userid'];
	
	##cek apakah sudah diinput di detail BKM belum
	$str1 = "select * from " . $dbname . ".kebun_kehadiran_vw where 
	( karyawanid = '".$data['nikmandor']."' or karyawanid = '".$data['nikmandor1']."' or karyawanid = '".$data['nikasisten']."' or karyawanid = '".$data['keranimuat']."') 
	and tanggal = '".$data['tanggal']."' and (jhk > '0' or umr > '0')";
	
	$wherenamaKary= "( karyawanid = '".$data['nikmandor']."' or karyawanid = '".$data['nikmandor1']."' or karyawanid = '".$data['nikasisten']."' or karyawanid = '".$data['keranimuat']."')";
	$namaKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$wherenamaKary);
	$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0)
    {
        echo "HK Mandor / Mandor 1 / Kerani tidak boleh di input pada detail BKM.\n\nKaryawan tersebut dibawah ini sudah terdaftar pada transaksi :\n";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". ".$namaKary[$bar->karyawanid]." => ".$bar->notransaksi." => ".tanggalnormal($bar->tanggal)."\n"; 
        }
        exit('Warning: silahkan kosongkan HK pada transaksi tersebut.');
    }
	
	$cols = array('notransaksi','kodeorg','tanggal','nikmandor',
	'nikmandor1','nikasisten','keranimuat','nobkm','tipetransaksi','updateby');
	$query = insertQuery($dbname,'kebun_aktifitas',$data,$cols);
                    try{$owlPDO->exec($query); echo $data['notransaksi'];}catch (PDOException $e) {print " DB Error " . $e->getMessage() . "\n"; }
	break;
    # Proses Edit Header
    case 'edit':
	$data = $_POST;
	$where = "notransaksi='".$data['notransaksi']."'";
	unset($data['notransaksi']);
	$data['tanggal'] = tanggalsystem($data['tanggal']);
    $data['updateby']=$_SESSION['standard']['userid'];
	
	##cek apakah sudah diinput di detail BKM belum
	$str2 = "select * from " . $dbname . ".kebun_kehadiran_vw where 
	( karyawanid = '".$data['nikmandor']."' or karyawanid = '".$data['nikmandor1']."' or karyawanid = '".$data['nikasisten']."' or karyawanid = '".$data['keranimuat']."') 
	and tanggal = '".$data['tanggal']."' and (jhk > '0' or umr > '0')";
	$wherenamaKary= "( karyawanid = '".$data['nikmandor']."' or karyawanid = '".$data['nikmandor1']."' or karyawanid = '".$data['nikasisten']."' or karyawanid = '".$data['keranimuat']."')";
	$namaKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$wherenamaKary);
	$res=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlBaris($res);
    if($numrows>0)
    {
        echo "HK Mandor / Mandor 1 / Kerani tidak boleh di input pada detail BKM.\n\nKaryawan tersebut dibawah ini sudah terdaftar pada transaksi :\n";//
        $no=0;
        while($bar=$res->fetch()){
           $no+=1;
            echo $no.". ".$namaKary[$bar->karyawanid]." => ".$bar->notransaksi." => ".tanggalnormal($bar->tanggal)."\n"; 
        }
        exit('Warning: silahkan kosongkan HK pada transaksi tersebut.');
    }

	$query = updateQuery($dbname,'kebun_aktifitas',$data,$where);
	try{$owlPDO->exec($query); echo $data['notransaksi'];}catch (PDOException $e) {print " DB Error " . $e->getMessage() . "\n";}
	break;
    case 'delete':
	$where = "notransaksi='".$param['notransaksi']."'";
	$query = "delete from `".$dbname."`.`kebun_aktifitas` where ".$where;
	try{$owlPDO->exec($query); echo $data['notransaksi'];}catch (PDOException $e) {print " DB Error " . $e->getMessage() . "\n"; die(); }
	break;
    default:
	break;
}

function formHeader($mode,$tipe,$data) {
    global $dbname;
    global $param;
    
    # Default Value
    if(empty($data)) {
	$data['notransaksi'] = '';
	$data['kodeorg'] = '';
	$data['tanggal'] = '';
	$data['bkm'] = '';
	$data['nikmandor'] = '';
	$data['nikmandor1'] = '';
	$data['nikasisten'] = '';
	$data['keranimuat'] = '';
    }
    
    # Disabled Primary
    if($mode=='edit') {
	$disabled = 'disabled';
    } else {
	$disabled = '';
    }
	
    
    # Options
 //  $whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan<>1";
 	$whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
	$whereKary .= " and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
	$whereKaryMandor = $whereKary;
	if($mode=='edit') {
	$whereOrg = "kodeorganisasi='".$data['kodeorg']."' and tipe<>'BLOK'";
    } else {
	$whereOrg = "left(kodeorganisasi,4)='".substr($_SESSION['empl']['lokasitugas'],0,4)."' and tipe='KEBUN'";
    }
	
		$wherekarypt = "lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."')";
	$wherekarypt .= " and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
	
    $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg);
    #$optOrg = getOrgBelow($dbname,$_SESSION['org']['kodeorganisasi'],false,'afdeling');
    $optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whereKaryMandor);
    //$optKary1 = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whereKary,'0',true);
	$divisix='';
	if($_SESSION['empl']['subbagian']!=''){
		$divisix=" and a.subbagian='".$_SESSION['empl']['subbagian']."'";
	}
	//mandor
    $iKary = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.lokasitugas from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where b.namajabatan like '%mandor%' and (b.namajabatan like '%rawat%' or b.namajabatan like '%panen%') 
		and  ".$wherekarypt." ".$divisix." order by a.namakaryawan asc";
    $optKary1 = array(''=>'');
    $nKary = fetchData($iKary);
    foreach($nKary as $row) {
        $optKary1[$row['karyawanid']] = $row['namakaryawan']." [".$row['nik']."]  ".$row['namajabatan']."  [".$row['lokasitugas']."]";
    }
	
	//mandor1
    $iKary2 = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan 
		where (b.namajabatan like '%mandor i%' or b.namajabatan like '%mandor 1%') and ".$whereKary.
		"  order by a.namakaryawan asc";
    $optKary2 = array(''=>'');
    $nKary2 = fetchData($iKary2);
    foreach($nKary2 as $row) {
        $optKary2[$row['karyawanid']] = $row['namakaryawan']." [".$row['nik']."]  ".$row['namajabatan'];
    }
	
	//asst
    $iKary3 = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan, a.lokasitugas from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where (b.namajabatan like '%asst%' or ".
		"b.namajabatan like '%asist%' or b.namajabatan like '%ASIS%') or b.namajabatan like '%tanaman%' and ".$wherekarypt.
		"  ".$divisix." order by a.namakaryawan asc";
    $optKary3 = array(''=>'');
    $nKary3 = fetchData($iKary3);
    foreach($nKary3 as $row) {
        $optKary3[$row['karyawanid']] = $row['namakaryawan']." [".$row['nik']."]  ".$row['namajabatan'];
    }
	
	//krani
    $iKary4 = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan from ".$dbname.".datakaryawan a ".
		"left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where (b.namajabatan like '%krani%' or ".
		"b.namajabatan like '%kerani%' or b.namajabatan like '%clerk%') and  b.namajabatan like '%divisi%'
		and a.lokasitugas not like '%M' and ".$whereKary.
		"  ".$divisix." order by a.namakaryawan asc";
    $optKary4 = array(''=>'');
    $nKary4 = fetchData($iKary4);
    foreach($nKary4 as $row) {
        $optKary4[$row['karyawanid']] = $row['namakaryawan']." [".$row['nik']."]  ".$row['namajabatan'];
    }
	
	$qMandor = "select a.karyawanid,a.namakaryawan from ".$dbname.".datakaryawan a
		left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		where a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and statuskaryawan != 'Keluar' and
			(a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].") and
			b.namajabatan like '%Mandor%'";
	$resMandor = fetchData($qMandor);
	$optMandor = array();
	foreach($resMandor as $row) {
		$optMandor[$row['karyawanid']] = $row['namakaryawan'];
	}
    
    $els = array();
    $els[] = array(
	makeElement('notransaksi','label',$_SESSION['lang']['notransaksi']),
	makeElement('notransaksi','text',$data['notransaksi'],
	    array('style'=>'width:150px','disabled'=>'disabled'))
    );
    $els[] = array(
	makeElement('kodeorg','label',$_SESSION['lang']['kodeorg']),
	makeElement('kodeorg','select',$data['kodeorg'],
	    array('style'=>'width:155px',$disabled=>$disabled),$optOrg)
    );
    $els[] = array(
	makeElement('tanggal','label',$_SESSION['lang']['tanggal']),
	makeElement('tanggal','text',$data['tanggal'],array('style'=>'width:150px',
	'readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)',$disabled=>$disabled))
    );
	
	$els[] = array(
		makeElement('nobkm','label',"No. BKM"),
		makeElement('nobkm','text',$data['nobkm'],array('style'=>'width:150px'))
	);
	
    $els[] = array(
	makeElement('nikmandor','label',$_SESSION['lang']['nikmandor']),
	makeElement('nikmandor','selectsearch',$data['nikmandor'],array('style'=>'width:150px'),$optKary1)
    );
    $els[] = array(
	makeElement('nikmandor1','label',$_SESSION['lang']['nikmandor1']),
	makeElement('nikmandor1','selectsearch',$data['nikmandor1'],array('style'=>'width:150px'),$optKary2)
    );

    if($param['tipe']=='PNN') {
        $els[] = array(
            makeElement('nikasisten','label',$_SESSION['lang']['keranipanen']),
            makeElement('nikasisten','selectsearch',$data['nikasisten'],array('style'=>'width:150px'),$optKary4)
            );        
	$els[] = array(
	    makeElement('keranimuat','label',$_SESSION['lang']['keranimuat'],array('style'=>'display:none')),
	    makeElement('keranimuat','text',$data['keranimuat'],array('style'=>'width:150px;display:none'),$optKary4)
	);
    } else {
        $els[] = array(
            makeElement('nikasisten','label',$_SESSION['lang']['nikasisten']),
            makeElement('nikasisten','selectsearch',$data['nikasisten'],array('style'=>'width:150px'),$optKary3)
            );        
	$els[] = array(
	    makeElement('keranimuat','label',$_SESSION['lang']['keraniafdeling']),
	    makeElement('keranimuat','selectsearch',$data['keranimuat'],array('style'=>'width:150px'),$optKary4)
            );
    }
    if($mode=='add') {
	$els['btn'] = array(
	    makeElement('addHead','btn',$_SESSION['lang']['save'],
		array('onclick'=>"addDataTable('".$tipe."')"))
	);
    } elseif($mode=='edit') {
	$els['btn'] = array(
	    makeElement('editHead','btn',$_SESSION['lang']['save'],
		array('onclick'=>"editDataTable('".$tipe."')"))
	);
    }
    
    if($mode=='add') {
	return genElementMultiDim($_SESSION['lang']['addheader'],$els,2);
    } elseif($mode=='edit') {
	return genElementMultiDim($_SESSION['lang']['editheader'],$els,2);
    }
}
?>