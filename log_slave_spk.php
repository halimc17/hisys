<?
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
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
			$where = "length(kodeorg)=4 and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$kdOrganisasi."')";
		} else {
			$where = "kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		}
		if(isset($param['where'])) {
			$arrWhere = json_decode(str_replace('\\','',$param['where']),true);
			if(!empty($arrWhere)) {
			foreach($arrWhere as $key=>$r1) {
				$where .= " and ".$r1[0]." like '%".$r1[1]."%'";
			}
			}
		}
		
		
		# Header
		$header = array(
			$_SESSION['lang']['kodeorg'],
			$_SESSION['lang']['notransaksi'],
			$_SESSION['lang']['tanggal'],
			$_SESSION['lang']['subunit'],
			$_SESSION['lang']['koderekanan'],
			$_SESSION['lang']['nilaikontrak'],
			$_SESSION['lang']['dari'],
			$_SESSION['lang']['sampai'],
			$_SESSION['lang']['jumlahrealisasi'],
			$_SESSION['lang']['status']            
		);
		
		# Content
		$cols = "kodeorg,notransaksi,tanggal,divisi,koderekanan,nilaikontrak,dari,sampai";
		$query = selectQuery($dbname,'log_spkht',$cols,$where." order by tanggal desc","",false,$param['shows'],$param['page']);
			//exit("Error:".$query);
			$data = fetchData($query);
		$totalRow = getTotalRow($dbname,'log_spkht',$where);
		
		// Get Ppn Masukan
		$qPpn = selectQuery($dbname,'setup_parameterappl','nilai',"kodeaplikasi='TX' and kodeparameter='PPNINV'");
		$resPpn = fetchData($qPpn);
		$ppn = '%';
		if(!empty($resPpn)) $ppn = $resPpn[0]['nilai'];
		
		foreach($data as $key=>$row) {
			$data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
            $data[$key]['dari'] = tanggalnormal($row['dari']);
            $data[$key]['sampai'] = tanggalnormal($row['sampai']);
            //=================ambil realisasi
            $data[$key]['realisasi'] =0;
            $strx="select sum(jumlahrealisasi) from ".$dbname.".log_baspk 
                  where notransaksi='".$data[$key]['notransaksi']."'";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_NUM);
            while($barx=$resx->fetch())
            {
              $data[$key]['realisasi']= number_format($barx[0]); 
            }
            //lihat postingan-=============================
            $data[$key]['status'] ='';
            $strx="select statusjurnal from ".$dbname.".log_baspk 
                  where notransaksi='".$data[$key]['notransaksi']."'
                  and blokspkdt = '".$data[$key]['divisi']."' and statusjurnal=0";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			if(owlBaris($resx)>0)
                $data[$key]['status'] ='?';
            else if($data[$key]['realisasi']==0 and $data[$key]['status']=='')
                $data[$key]['status'] ='?';
            else                
               $data[$key]['status'] ='Ready for Posting';

            $stru="select posting from ".$dbname.".log_spkht where notransaksi='".$data[$key]['notransaksi']."'";
			$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
			$resu->setFetchMode(PDO::FETCH_NUM);
            $post=0;
            while($baru=$resu->fetch())
            {
                $post=$baru[0];
            }
            if($post==1)
                $data[$key]['status']='Posted';            
			
			// Get Pajak
			$qPajak = selectQuery($dbname,'log_spk_tax',"noakun,nilai","notransaksi='".
								  $row['notransaksi']."' and kodeorg='".$row['kodeorg']."'");
			$resPajak = fetchData($qPajak);
			if(!empty($resPajak)) {
				foreach($resPajak as $r) {
					if($ppn==$r['noakun']) {
						$data[$key]['nilaikontrak'] += $r['nilai'];
					} else {
						$data[$key]['nilaikontrak'] -= $r['nilai'];
					}
				}
			}
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
			$dataShow[$key]['nilaikontrak'] = number_format($row['nilaikontrak'],0);
			if($dataShow[$key]['divisi'] == ''){
				$dataShow[$key]['divisi'] = "Project";
			}else if($dataShow[$key]['divisi'] == 'S'){
				$dataShow[$key]['divisi'] = "Perumahan";
			}else{
				$dataShow[$key]['divisi'] = $dataShow[$key]['divisi'];
			}
		}
		
		$postJabatan = getPostingJabatan('baspk');
		
		# Make Table
		$tHeader = new rTable('headTable','headTableBody',$header,$data,$dataShow);
		$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
		$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
		
		$tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
		$tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
		
		if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
				$tHeader->_actions[2]->_name='';
			}
		}
		$tHeader->addAction('detailPDF','Print Data Detail','images/'.$_SESSION['theme']."/pdf.jpg");
		$tHeader->_actions[3]->addAttr('event');
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
        // Data Header
		$where = "notransaksi='".$param['notransaksi']."' and kodeorg='".$param['kodeorg']."'";
		$query = selectQuery($dbname,'log_spkht',"*",$where);
		$tmpData = fetchData($query);
		$data = $tmpData[0];
		$data['tanggal'] = tanggalnormal($data['tanggal']);
        $data['dari'] = tanggalnormal($data['dari']);
        $data['sampai'] = tanggalnormal($data['sampai']);
		
		// Data Pajak
		$qTax = selectQuery($dbname,'log_spk_tax',"*",$where);
		$resTax = fetchData($qTax);
		$data['tax'] = array();
		foreach($resTax as $row) {
			$data['tax'][$row['noakun']] = $row['nilai'];
		}
		
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
		if($data['kodeorg']=='') {$warning = "Lokasi Tugas harus Kebun\n";}
		if($warning!=''){echo "Warning :\n".$warning;exit;}
		
		$data['tanggal'] = tanggalsystem($data['tanggal']);
		$data['dari'] = tanggalsystem($data['dari']);
		$data['sampai'] = tanggalsystem($data['sampai']);
		$data['nilaikontrak'] = str_replace(',','',$data['nilaikontrak']);
		$data['matauang'] = $data['matauang'];
		$data['ppnnilaikontrak'] = '0';
		unset($data['tax']);
		
		if(empty($data['dari'])) $data['dari'] = '0000-00-00';
		if(empty($data['sampai'])) $data['sampai'] = '0000-00-00';
		
		$cols = array('kodeorg','notransaksi','tanggal','divisi',
			'koderekanan','nilaikontrak','dari','sampai','keterangan','matauang','ppnnilaikontrak');
		$query = insertQuery($dbname,'log_spkht',$data,$cols);
		
		try{
			$owlPDO->exec($query); 
			
			// Insert Detail Pajak
			$dataIns = array();
			if(isset($param['tax']))
			foreach($param['tax'] as $akun=>$nilai) {
				if($nilai > 0) {
					$dataIns[] = array(
						'kodeorg' => $param['kodeorg'],
						'notransaksi' => $param['notransaksi'],
						'noakun' => $akun,
						'nilai' => str_replace(',','',$nilai)
					);
				}
			}
			if(!empty($dataIns)) {
				$qIns = insertQuery($dbname,'log_spk_tax',$dataIns);
				try{
					$owlPDO->exec($qIns); 
				}catch (PDOException $e){
					exit("Insert Tax Error: ".$e->getMessage());
				}
			}
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
		}
		break;
	
    # Proses Edit Header
    case 'edit':
		$data = $_POST;
		$where = "notransaksi='".$data['notransaksi']."'";
		unset($data['notransaksi']);
		$data['tanggal'] = tanggalsystem($data['tanggal']);
			$data['dari'] = tanggalsystem($data['dari']);
			$data['sampai'] = tanggalsystem($data['sampai']);
		$data['nilaikontrak'] = str_replace(',','',$data['nilaikontrak']);
		$data['matauang'] = $data['matauang'];
		$data['ppnnilaikontrak'] = '0';
		unset($data['tax']);
			
		$query = updateQuery($dbname,'log_spkht',$data,$where);
		
		try{
			$owlPDO->exec($query); 
		}catch (PDOException $e){
			// Delete Pajak
			$qDel = deleteQuery($dbname,'log_spk_tax',
								"notransaksi='".$param['notransaksi']."'");
			try{
				$owlPDO->exec($qDel); 
				
				// Insert Detail Pajak
				$dataIns = array();
				foreach($param['tax'] as $akun=>$nilai) {
					if($nilai > 0) {
						$dataIns[] = array(
							'kodeorg' => $param['kodeorg'],
							'notransaksi' => $param['notransaksi'],
							'noakun' => $akun,
							'nilai' => $nilai
						);
					}
				}
				$qIns = insertQuery($dbname,'log_spk_tax',$dataIns);echo $qIns;
				try{
					$owlPDO->exec($qIns); 
				}catch (PDOException $e){
					exit("Insert Tax Error: ".$e->getMessage());
				}
			}catch (PDOException $e){
				exit("Delete Tax Error: ".$e->getMessage());
			}
		}
		break;
    case 'delete':
        //================periksa realisasi
		$m =0;
		$strx="select sum(jumlahrealisasi) from ".$dbname.".log_baspk 
			  where notransaksi='".$param['notransaksi']."' and statusjurnal = '1'";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_NUM);
		while($barx=$resx->fetch())
		{
		  $m= $barx[0]; 
		}   
		//lihat postingan-=============================
		$n ='';
		$strx="select statusjurnal from ".$dbname.".log_baspk 
			  where notransaksi='".$param['notransaksi']."' and statusjurnal=0";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		if(owlBaris($resx)>0)
			$n ='?';
		
		if($n=='' and $m==0)
		{     
		//=================================
			$where = "notransaksi='".$param['notransaksi']."'";
			$query = "delete from `".$dbname."`.`log_spkht` where ".$where;
			try{
				$owlPDO->exec($query); 
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				exit;
			}
		}
		else
		{
			exit('Error:Realisasi sudah terisi');
		}   
		break;
	
    case 'updSub':
		$whereDiv = "induk='".$param['kodeorg']."' or kodeorganisasi='".
			$param['kodeorg']."'";
		$optDiv = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereDiv);
		#tambahkan dari  project=================================================================

		$optDiv[''] = "Project";
		$optDiv['S'] = "Perumahan";
		$optDiv['P'] = "Pabrikasi";
		#===================================================================================         
		echo json_encode($optDiv);
		break;
	
    default:
		break;
}

function formHeader($mode,$data) {
    global $dbname;
    
    # Default Value
    if(empty($data)) {
	$data['kodeorg'] = '';
	$data['notransaksi'] = '0';
	$data['tanggal'] = '';
	$data['divisi'] = '';
	$data['koderekanan'] = '';
	$data['matauang'] = 'IDR';
	$data['nilaikontrak'] = '0';
        $data['keterangan'] = '';
        $data['dari'] = '';
        $data['sampai'] = '';
    } else {
	$data['nilaikontrak'] = number_format($data['nilaikontrak']);
    }
    
    # Disabled Primary
    if($mode=='edit') {
	$disabled = 'disabled';
    } else {
	$disabled = '';
    }
    
    # Options
    if($_SESSION['empl']['tipelokasitugas']=='TRAKSI' or
	$_SESSION['empl']['tipelokasitugas']=='HOLDING' or
	$_SESSION['empl']['tipelokasitugas']=='KANWIL') {
	$whereOrg = "length(kodeorganisasi)=4";
    } else {
		$whereOrg = "kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
		//$optDiv[$_SESSION['empl']['lokasitugas']]=$_SESSION['empl']['lokasitugas'];
    }
	
    $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg);
    if($data['divisi']=='') {
	if($_SESSION['empl']['tipelokasitugas']=='TRAKSI') {
	    $whereDiv = "induk='".getFirstKey($optOrg)."'";
	} else {
	    $whereDiv = "induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
	}
//	$whereDiv .= " or kodeorganisasi='".getFirstKey($optOrg)."'";
    } else {
	$whereDiv = "kodeorganisasi='".$data['divisi']."'";
        
    }
    $optDiv= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereDiv);
	
    #tambahkan dari  project=================================================================
	// if(){
	// }
	
	$optDiv[''] = "Project";
	$optDiv['S'] = "Perumahan";
	$optDiv['P'] = "Pabrikasi";
	if(@$data['ppnnilaikontrak']=='1'){
		$valueChk = 'checked';
	}else{
		$valueChk = '';
	}
	
	#=================================================================================== 
    //$optSup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"left(kodekelompok,1)='K' OR left(kodekelompok,1)='T'");
    $optSup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='KONTRAKTOR')");
    $optmatauang = makeOption($dbname,'setup_matauang','kode,kode');
	
    $els = array();
    if($_SESSION['empl']['tipelokasitugas']=='TRAKSI' or
	$_SESSION['empl']['tipelokasitugas']=='HOLDING' or
	$_SESSION['empl']['tipelokasitugas']=='KANWIL') {
	$els[] = array(
	    makeElement('kodeorg','label',$_SESSION['lang']['kodeorg']),
	    makeElement('kodeorg','select',$data['kodeorg'],
		array('style'=>'width:150px',$disabled=>$disabled,'onchange'=>'updSub()'),$optOrg)
	);
    } else {
	$els[] = array(
	    makeElement('kodeorg','label',$_SESSION['lang']['kebun']),
	    makeElement('kodeorg','select',$data['kodeorg'],
		array('style'=>'width:150px',$disabled=>$disabled),$optOrg)
	);
    }
    $els[] = array(
	makeElement('notransaksi','label',$_SESSION['lang']['notransaksi']),
	makeElement('notransaksi','text',$data['notransaksi'],
	    array('style'=>'width:150px','maxlength'=>'25',$disabled=>$disabled))
    );
    $els[] = array(
	makeElement('tanggal','label',$_SESSION['lang']['tanggal']),
	makeElement('tanggal','text',$data['tanggal'],array('style'=>'width:150px',
	'readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)'))
    );
    $els[] = array(
	makeElement('divisi','label',$_SESSION['lang']['subunit']),
	makeElement('divisi','select',$data['divisi'],
	    array('style'=>'width:150px',$disabled=>$disabled),$optDiv)
    );
	$els[] = array(
	makeElement('koderekanan','label',$_SESSION['lang']['koderekanan']),
	makeElement('koderekanan','select',$data['koderekanan'],
            array('style'=>'width:150px'),$optSup)
    );
	$els[] = array(
	makeElement('matauang','label',$_SESSION['lang']['matauang']),
	makeElement('matauang','select',$data['matauang'],'',$optmatauang)
    );
    $els[] = array(
	makeElement('nilaikontrak','label',$_SESSION['lang']['nilaikontrak']),
	makeElement('nilaikontrak','textnum',$data['nilaikontrak'],
	    array('style'=>'width:150px','onkeyup'=>"z.numberFormat('nilaikontrak',2)",'maxlength'=>'15'))
    );
//	$els[] = array(
//	makeElement('lblppn','label','Include Ppn'),
//	makeElement('ppnnilaikontrak','checkbox','',array($valueChk=>''))
//    );
    $els[] = array(
	makeElement('keterangan','label',$_SESSION['lang']['project']),
	makeElement('keterangan','text',$data['keterangan'],
	    array('style'=>'width:150px','maxlength'=>'50'))
    );
    $els[] = array(
	makeElement('dari','label',$_SESSION['lang']['dari']),
	makeElement('dari','text',$data['dari'],array('style'=>'width:150px',
	'readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)'))
    );
    $els[] = array(
	makeElement('sampai','label',$_SESSION['lang']['sampai']),
	makeElement('sampai','text',$data['sampai'],array('style'=>'width:150px',
	'readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)'))
    );
    if($mode=='add') {
	$els['btn'] = array(
	    makeElement('addHead','btn',$_SESSION['lang']['save'],
		array('onclick'=>"addDataTable()"))
	);
    } elseif($mode=='edit') {
	$els['btn'] = array(
	    makeElement('editHead','btn',$_SESSION['lang']['save'],
		array('onclick'=>"editDataTable()"))
	);
    }
    
    if($mode=='add') {
		$content = genElementMultiDim($_SESSION['lang']['addheader'],$els,2);
    } elseif($mode=='edit') {
		$content = genElementMultiDim($_SESSION['lang']['editheader'],$els,2);
    }
	
	// Ambil Akun PPn dan Pph
	$optPajak = makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',
						   "kodeaplikasi = 'TX' and kodeparameter in ('PPNINV','PPHSPK')");
	$pph = explode(',',$optPajak['PPHSPK']);
	$whereAkun = "";
	$whereAkun .= "noakun in ('".$optPajak['PPNINV']."','".implode("','",$pph)."')";
	$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',
						  "(".$whereAkun.") and detail=1",'2');
	
	// Pajak
	$content .= "<fieldset><legend>".$_SESSION['lang']['detail']." ".
		$_SESSION['lang']['pajak']."</legend>";
	$content .= "<div><table>";
	$listPpn = array();
	foreach($optAkun as $noakun => $namaakun) {
		$listPpn[] = $noakun;
		setIt($data['tax'][$noakun],0);
		$content .= "<tr>";
		$content .= "<td>".$namaakun."</td>";
		$content .= "<td>".makeElement('tax'.$noakun,'textnum',number_format($data['tax'][$noakun]),array('onkeyup'=>"z.numberFormat('tax".$noakun."',2)"))."&nbsp;(Rp)</td>";
		$content .= "</tr>";
	}
	$content .= "</table></div>";
	$content .= "</fieldset>";
	
	$content .= makeElement('listPpn','hidden',json_encode($listPpn));
	
	return $content;
}
?>