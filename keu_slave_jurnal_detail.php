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
$tmpNoJ = explode('/',$data['nojurnal']);
@$org = $tmpNoJ[1];

switch($proses) {
    case 'show':
	$ids = $_POST;
	
	

	
	#= jgn ambil lokasitugas
	$explunit=explode('/',$ids['nojurnal']);
	$unit=$explunit[1];
	$periode=substr(tanggalsystemn($ids['tanggal']),0,7);
	// echo $periode;
	#tipeorg
	$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$unit."'";
	$res=fetchdata($str);
	foreach($res as $bar){
		$tipeunit=$bar['tipe'];
	}

	
	# Options
	// $whereAsset = "kodeorg='".$_SESSION['empl']['lokasitugas']."' and posting=0";
	// $whereKary = "lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
	$whereAsset = "kodeorg='".$unit."' and posting=0";
	$whereKary = "lokasitugas='".$unit."'";
	$whereKaryhist = "lokasitugas='".$unit."' and periodegaji='".$periode."' and version_type='B' ";
	$whereVhc = "kodeorg='".$unit."' OR kodetraksi LIKE '%$unit%'";
    if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    	$whereJam="  detail=1 and jurnalmemorial=1 and
			(pemilik in ('KANWIL','HOLDING') or pemilik='GLOBAL' or pemilik='".$unit."')";
			// (pemilik in ('KANWIL','HOLDING') or pemilik='GLOBAL' or pemilik='".$_SESSION['empl']['lokasitugas']."')";
    }else{
    	$whereJam="  detail=1 and jurnalmemorial=1 and
			(pemilik='".$tipeunit."' or pemilik='GLOBAL' or pemilik='".$unit."')";	
    }
    
	$optCashFlow = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',
	    "pemilik_aruskas='UNIT' and tipetransaksi='K' and level='3'",'2',true);
	$optMatauang = makeOption($dbname,'setup_matauang','kode,matauang');
	#dialihkan ke aktiva dalam konstruksi
	#$optAsset = makeOption($dbname,'sdm_daftarasset','kodeasset,namasset',$whereAsset,'2',true);
	//$optAsset = makeOption($dbname,'project','kode,nama',$whereAsset,'2',true);
	
	$optAsset['']='';
	$str=" select kode,nama from ".$dbname.".project where ".$whereAsset." ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optAsset[$bar['kode']]=$bar['kode'].' - '.$bar['nama'];	
	}
	$str=" select kodepabrikasi,namapabrikasi from ".$dbname.".pabrikasi_5masterht where status=1 ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optAsset[$bar['kodepabrikasi']]=$bar['kodepabrikasi'].' - '.$bar['namapabrikasi'];	
	}
	$optKary['']='';	
	$str=" select * from ".$dbname.".datakaryawan where ".$whereKary." ";
	// echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optKary[$bar['karyawanid']]=$bar['nik'].' - '.$bar['namakaryawan'];	
	}
	
	$str=" select * from ".$dbname.".datakaryawan_hist where ".$whereKaryhist." ";
	// echo $str;exit("Error:A");
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optKary[$bar['karyawanid']]=$bar['nik'].' - '.$bar['namakaryawan'];	
	}
	
	
	$optBlok['']='';
	$optSupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier','status=1','2',true);
	$optCustomer = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',null,'0',true);
	// $optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whereKary,'0',true);
	// $optKary= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan,nik',$whereKary,5,true);
	// $optKary.= makeOption($dbname,'datakaryawan_hist','karyawanid,namakaryawan,nik',$whereKaryhist,5,true);
	// print_r($optKary);exit("Error:A");
	if($_SESSION['language']=='EN'){
		$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereJam,'2',true);
	}else{
		$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereJam,'2',true);
	}
	$optVhc = makeOption($dbname,'vhc_5master_hist','kodevhc,kodeorg',$whereVhc,'2',true);
	
	$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi like '".$unit."%' and length(kodeorganisasi)>4",'2',true);
	
	/*
	if($_SESSION['empl']['tipelokasitugas']=='KEBUN') {
		$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%' and tipe = 'blok' or tipe = 'bibitan'",'',true);
	} else if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
		$str = "select * from ".$dbname.".organisasi a 
			left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg
			where a.tipe='BLOK'  order by a.kodeorganisasi asc"; //exit('error'.$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optBlok[$bar['kodeorganisasi']]=$bar['namaorganisasi']." ".$bar['statusblok'];
			}
		//$optBlok = makeOption($dbname,'setup_blok','kodeorg,statusblok','','2',true);   
	} else if($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
		$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,kodeorganisasi',"kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'",'0',true);
	} else if($_SESSION['empl']['tipelokasitugas']=='TRAKSI') {
		$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,kodeorganisasi',"kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'",'0',true);
	} else {
        $optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)>6 and induk like '".$_SESSION['empl']['lokasitugas']."%'",'0',true);
    }
	*/

	# Kegiatan
	if($_SESSION['language']=='EN'){
		$optKlpKeg = makeOption($dbname,'setup_klpkegiatan','kodeklp,namakelompok1',null,'0',true);
		$qKegiatan = selectQuery($dbname,'setup_kegiatan','kodekegiatan,namakegiatan1 as namakegiatan,kelompok').' order by noakun';
	}else{
		$optKlpKeg = makeOption($dbname,'setup_klpkegiatan','kodeklp,namakelompok',null,'0',true);
		$qKegiatan = selectQuery($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,kelompok').' order by noakun';
	}
	$tmpKeg = fetchData($qKegiatan);
	$optKegiatan = array(''=>'');
	foreach($tmpKeg as $row) {
	    $optKegiatan[$row['kodekegiatan']] = $row['kodekegiatan']."-".$row['namakegiatan']." (".$optKlpKeg[$row['kelompok']].")";
	}

	#$optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',null,'0',true);
	
	$tmpKlp = makeOption($dbname,'setup_klpkegiatan','noakun,namakelompok');
	
	// Validasi Kurs
	$kurs = 1;
	if($data['matauang']!='IDR') {
		$qKurs = selectQuery($dbname,'setup_matauangrate','kurs',
							 "kode='".$ids['matauang']."' and daritanggal='".
							 tanggalsystem($ids['tanggal'])."'");
		$resKurs = fetchData($qKurs);
		if(empty($resKurs)) exit("Warning: Kurs ".$ids['matauang']." di tanggal ".
								 $ids['tanggal']." belum ada");
		else
			$kurs = $resKurs[0]['kurs'];
	}


	//unit kerja
	$optunit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)=4");

	// echo "<pre>";
	// print_r($optunit);
	// echo "</pre>";
	
	# Get Data
	$cols = array('nourut','noakun','keterangan','jumlah','matauang','kurs',
	    'kodekegiatan','kodesegment','kodecustomer','kodeasset','nik','kodesupplier','kodevhc','nodok','kodeblok');
	$where = "nojurnal='".$ids['nojurnal']."'";
	$query = selectQuery($dbname,'keu_jurnaldt',$cols,$where,"nojurnal desc");
	$data = fetchData($query);
    // exit('error:masuk');
	
	# Masking Nama Barang
	// $arrSegment = array();
	// if(!empty($data)) {
	//     $whereBarang = "";
	// 	$i=0;
	//     foreach($data as $row) {
	// 		$arrSegment[$row['kodesegment']] = "'".$row['kodesegment']."'";
	// 		if($i==0) {
	// 			$whereBarang .= "kodebarang='".$row['kodebarang']."'";
	// 		} else {
	// 			$whereBarang .= " or kodebarang='".$row['kodebarang']."'";
	// 		}
	// 		$i++;
	//     }
	//     $optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whereBarang);
	// } else {
	//     $optBarang = array();
	// }
	
	// Masking Segment
	if(!empty($arrSegment)) {
		$whereSegment = "kodesegment in (".implode(',',$arrSegment).")";
		$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',$whereSegment);
	} else {
		$optSegment = array();
	}
	
	# Replace Code with Name
	$dataShow = $data;
	// echo"<pre>";
	// print_r($dataShow);
	// echo"</pre>";
	foreach($dataShow as $key=>$row) {
		setIt($optSegment[$row['kodesegment']],'');
	    $dataShow[$key]['nik'] = $optKary[$row['nik']];
	    // $dataShow[$key]['noaruskas'] = $optCashFlow[$row['noaruskas']];
	    $dataShow[$key]['kodekegiatan'] = $optKegiatan[$row['kodekegiatan']];
	    $dataShow[$key]['kodecustomer'] = $optCustomer[$row['kodecustomer']];
	    $dataShow[$key]['kodesupplier'] = $optSupplier[$row['kodesupplier']];
	    $dataShow[$key]['kodevhc'] = $optVhc[$row['kodevhc']];
	    $dataShow[$key]['matauang'] = $optMatauang[$row['matauang']];
	    $dataShow[$key]['noakun'] = $optAkun[$row['noakun']];
	    @$dataShow[$key]['kodeorg'] = $optunit[$row['kodeorg']];	    
	  //   if($row['kodebarang']!='' and $row['kodebarang']!='0') {
			// $dataShow[$key]['kodebarang'] = $optBarang[$row['kodebarang']];
	  //   }
		$dataShow[$key]['kodesegment'] = $optSegment[$row['kodesegment']];
		
		if(substr($dataShow[$key]['kodeblok'],0,2)=='PB'){
			$dataShow[$key]['kodeblok'] = $optAsset[$row['kodeblok']];
		}else{
			$dataShow[$key]['kodeblok'] = $optBlok[$row['kodeblok']];
		}
	   
		$dataShow[$key]['kurs'] = number_format($row['kurs'],2);


	}
	
	## INSERT IMAGE TO SESSION
	$_SESSION['imgjurnalm']=array();
	$strx="select * from ".$dbname.".listfileupload where notransaksi='".$ids['nojurnal']."'";
	$resx=fetchData($strx);
	foreach($resx as $valx){
		$newdata = array(
			'namafile'=>$valx['namafile'],
			'filetype'=>$valx['formaticon']
		);
		array_push($_SESSION['imgjurnalm'],$newdata);
	}
	# Form
	$theForm = new uForm('jurnalForm','Form Jurnal Detail',2);
	$theForm->addEls('nourut',$_SESSION['lang']['nourut'],'0','textnum','R',5);
	$theForm->_elements[0]->_attr['disabled'] = 'disabled';
	// $theForm->addEls('noaruskas',$_SESSION['lang']['noaruskas'],'','selectsearch','L',30,$optCashFlow);
	// $theForm->_elements[1]->_attr['onchange'] = 'getnoakun()';
	$theForm->addEls('noakun',$_SESSION['lang']['noakun'],'','selectsearch','L',30,$optAkun);
	 $theForm->_elements[1]->_attr['onchange'] = 'getkeg()';
	$theForm->addEls('keterangan',$_SESSION['lang']['keterangan'],'','text','L',29.4);
	$theForm->addEls('jumlah',$_SESSION['lang']['jumlah'],'0','dk','R',18.6);
	$theForm->_elements[3]->_attr['onchange'] = 'this.value=remove_comma(this);this.value = _formatted(this)';
	$theForm->addEls('matauang',$_SESSION['lang']['matauang'],$ids['matauang'],'select','L',11,$optMatauang);
	$theForm->_elements[4]->_attr['disabled'] = 'disabled';
	$theForm->addEls('kurs',$_SESSION['lang']['kurs'],$kurs,'textnum','R',10);
	$theForm->_elements[5]->_attr['disabled'] = 'disabled';
	$theForm->addEls('kodekegiatan',$_SESSION['lang']['kodekegiatan'],'','selectsearch','L',30,$optKegiatan);
	$theForm->addEls('kodesegment',$_SESSION['lang']['segment'],'','searchSegment','L',25);
	$theForm->addEls('kodeasset',$_SESSION['lang']['aktivadalam'],'','selectsearch','L',32.5,$optAsset);
	 // $theForm->_elements[8]->_attr['onchange'] = 'getkeg()';
	// $theForm->addEls('kodebarang',$_SESSION['lang']['kodebarang'],'','searchBarang','L',10);
	$theForm->addEls('nik',$_SESSION['lang']['nik'],'','selectsearch','L',32.5,$optKary);
	$theForm->addEls('kodesupplier',$_SESSION['lang']['kodesupplier'],'','selectsearch','L',32.5,$optSupplier);	
	$theForm->addEls('kodevhc',$_SESSION['lang']['kodevhc'],'','selectsearch','L',32.5,$optVhc);
	$theForm->addEls('nodok',$_SESSION['lang']['nodok'],'','text','L',32);
	$theForm->addEls('kodeblok',$_SESSION['lang']['kodeblok'],'','selectsearch','L',32.5,$optBlok);
	$theForm->addEls('kodecustomer',$_SESSION['lang']['koderekanan'],'','selectsearch','L',32.5,$optCustomer);
	// $theForm->addEls('kodeorg',$_SESSION['lang']['unit'],'','selectsearch','L',32.5,$optunit);
	
	
	# Table
	$theTable = new uTable('jurnalTable','Tabel Jurnal Detail',"",$data,$dataShow);
	
	# FormTable
	$formTab = new uFormTable('ftJurnalDt',$theForm,$theTable,null,
	    array('nojurnal','kodejurnal','tanggal','matauang'));
	$formTab->_target = "keu_slave_jurnal_manage_detail";
	$formTab->_defValue = '##matauang='.$ids['matauang'].'##kurs='.$kurs.'##kodesegment=##keterangan=##kodeorg=';
	$formTab->_numberFormat = '##jumlah';
	$formTab->_noEnable = '##kodesegment##matauang##kurs##kodeorg';
	$formTab->_afterCrud = "loadHeader";
	$formTab->render();
	break;
    default:
	break;
}
?>