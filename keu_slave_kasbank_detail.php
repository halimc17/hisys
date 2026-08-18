<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
require_once('lib/tanaman.php');


$proses = ($_GET['proses']==''?$_POST['proses']:$_GET['proses']);
$param = $_POST;
$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');
$tpkar=makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan');
$arrTipe=makeOption($dbname,'keu_5jenistagihan','kode,namajenis');
$pt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
// $nmsupram=makeOption($dbname,'log_5klsupplier','kode,kelompok');
$nmcust=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmjenis=makeOption($dbname,'pmn_5jenispenghasilan','idpenghasilan,namapenghasilan');
$optketerangan =  makeOption($dbname,'keu_5keterangan','id_ket,keterangan');
$tipeorgunit=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
#check update SVN#

$path   = "fileupload/keu_kasbankx/";

function gantierror($tulisan){
    $hasil=$tulisan;
    $hasil=str_ireplace('error','eror',$hasil);
    $hasil=str_ireplace('warning','wrning',$hasil);
    $hasil=str_ireplace('gagal','ggal',$hasil);
    return $hasil;
}

$str=" select * from ".$dbname.".keu_5aruskas";
$res=fetchdata($str);
foreach($res as $bar){
	$nmaruskas[$bar['noaruskas']]=$bar['nama_aruskas'];
}

switch($proses) {
	
	case'getnoakun':
		$optakun=$optket="<option value=''></option>";
		#= noakun
		// $whereJam=" detail=1 and level=5 and noakun <> '".$param['noakun']."' and (pemilik='".$param['kodeorg']."' or pemilik='GLOBAL')";             
		// $str=" select noakun from ".$dbname.".keu_5aruskas_detail where noaruskas='".$param['noaruskas']."' 
		// and noakun in (select noakun from ".$dbname.".keu_5akun where ".$whereJam.")";
		$str=" select noakun from ".$dbname.".keu_5aruskas_detail where noaruskas='".$param['noaruskas']."'";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if(substr($bar['noakun'],0,3)!='115'){
				@$optakun.="<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " - " . $nmakun[$bar['noakun']] . "</option>";
			}
		}
		
		#= keterangan
		$str=" select id_ket,noaruskas,keterangan from ".$dbname.".keu_5keterangan where noaruskas='".$param['noaruskas']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			@$optket.="<option value='".$bar['id_ket']."'>".$bar['id_ket']." - ".$bar['keterangan']."</option>";
		}
		
		echo @$optakun."####".@$optket;
		
	break;
	
	
    case 'showDetail':
        // Get Header
        $whereHead = "notransaksi='".$param['notransaksi']."' and kodeorg='".$param['kodeorg']."' and noakun='".$param['noakun']."' and tipetransaksi='".
                     $param['tipetransaksi']."'";
					
        $qHead = selectQuery($dbname,'keu_kasbankht','*',$whereHead);
        $resHead = fetchData($qHead);
        if(empty($resHead)) {
            $defMU = 'IDR';
            $defKurs = 1;
        } else {
            $defMU = $resHead[0]['matauang'];
            $defKurs = $resHead[0]['kurs'];
        }

        $whereAKB = "kodeaplikasi='GL' and aktif=1 and jurnalid!= 'M'";
        $queryAKB = selectQuery($dbname,'keu_5parameterjurnal','jurnalid,noakundebet,sampaidebet,noakunkredit,sampaikredit',$whereAKB);
        $optAKB = fetchData($queryAKB);
        $tipe = "";
        foreach($optAKB as $row) {
                if($param['tipetransaksi']=='K') {
                if($param['noakun']>=$row['noakunkredit'] and $param['noakun']<=$row['sampaikredit']) {
                        $tipe = $row['jurnalid'];
                }
                } else {
                if($param['noakun']>=$row['noakundebet'] and $param['noakun']<=$row['sampaidebet']) {
                        $tipe = $row['jurnalid'];
                }
                }
        }

                
                # Cek Kelompok Jurnal
                $whereKel = "kodeorg='".$_SESSION['org']['kodeorganisasi'].
                        "' and kodekelompok='".$tipe."'";
                $optKel = makeOption($dbname,'keu_5kelompokjurnal','kodekelompok,keterangan',$whereKel);
                if(empty($optKel)) {
                        echo "Warning : ".$tipe." ".$_SESSION['lang']['notifgroupjurnal']."\n";
                        echo $_SESSION['lang']['notifcontacitdept'];
                        exit;
                }

                # Options
                if(!isset($_SESSION['org']['period']['start'])) {
                exit("Warning: ".$_SESSION['lang']['notifperiodeakunting']."\n".$_SESSION['lang']['notifcontacitdept']);
        }
        // if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
            // $whereKary = "kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].") and tipekaryawan in ('0','1','2')";
        // }else{
            $kdpt=makeOption($dbname,"organisasi","kodeorganisasi,induk","kodeorganisasi='".$param['kodeorg']."'"); 
          //  echo print_r($kdpt);
            //buatlah array dari table organisasi dimana kodeorganisasi=param['kodeorg'] hasilnya adalah [kodeorganisasi=>'induk']
            $whereKary = "kodeorganisasi='".$kdpt[$param['kodeorg']]."' and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
                //$kodept['TJHO'] = 'TML';
            // }
        $optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan,nik',$whereKary,'4',true);
            // echo $_SESSION['empl']['kodeorganisasi'];
            // echo "<pre>";
            // print_r($optKary);
		
        $whereAsset = "kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kdpt[$param['kodeorg']]."') and posting=0";
        //$optAsset=makeOption($dbname,'project','kode,nama',$whereAsset,'2',true);
		//$optAsset=makeOption($dbname,'pabrikasi_5masterht','kodepabrikasi,namapabrikasi','status=1','2',true);
        $optMataUang = makeOption($dbname,'setup_matauang','kode,matauang');

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
		
		

        $wheresupaktif="status=1";
        $optSupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier',$wheresupaktif,'0',true);


        $optCustomer = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',null,'0',true);
        if($_SESSION['language']=='EN'){
            // $optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan1,satuan,noakun',null,'6',true);
                        $optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan1,satuan,noakun',null,'2',true);
        }else{
            // $optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,satuan,noakun',null,'6',true);
                        $optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan,satuan,noakun',null,'2',true);
        }
        if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
            $whereJam=" detail=1 and aktif=1 and left(noakun,3)!='115' and noakun <> '".$param['noakun']."' and (pemilik in ('KANWIL','HOLDING') or pemilik='GLOBAL' or pemilik='".$_SESSION['empl']['lokasitugas']."')";
        }else{
            $whereJam=" detail=1 and aktif=1 and left(noakun,3)!='115' and noakun <> '".$param['noakun']."' and (pemilik='".$_SESSION['empl']['tipelokasitugas']."' or pemilik='GLOBAL' or pemilik='".$_SESSION['empl']['lokasitugas']."')";            
        }
        
        
        if($_SESSION['language']=='EN'){
            $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereJam,'2',true);
        }else{
            $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereJam,'2',true);
        }
		
		$optketerangan =  makeOption($dbname,'keu_5keterangan','id_ket,keterangan','','2',true);
		

        $optVhc = makeOption($dbname,'vhc_5master','kodevhc,kodeorg','','2',true);
        if($_SESSION['empl']['tipelokasitugas']=='KEBUN')
        {
            $strxz = "select * from ".$dbname.".organisasi a 
            left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg
            where b.kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and b.luasareaproduktif!=0 order by a.kodeorganisasi asc"; //exit('error'.$str);
            $optOrgAl['']='';
            $resxz=$owlPDO->query($strxz) or die(print " Gagal: ".PDOException::getMessage());
            $resxz->setFetchMode(PDO::FETCH_ASSOC);
            while($barxz=$resxz->fetch()){
                $optOrgAl[$barxz['kodeorganisasi']]=$barxz['namaorganisasi'];
            }
        }
        else
        {
            $optOrgAl = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)>6 and induk like '".$_SESSION['empl']['lokasitugas']."%'",'0',true);
       
        }
            $optHutangUnit = array('0'=>$_SESSION['lang']['no'],'1'=>$_SESSION['lang']['yes']);
        
		if($param['tipetransaksi']=='K') {
            $invTab = 'keu_tagihanht';
			
			// if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				 // $optCashFlow = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',
					// "pemilik_aruskas in ('HOLDING','GLOBAL') and (tipetransaksi='".$param['tipetransaksi']."' or akses_rekening='') and level=3",'2',true);
			// }else{
			  // $optCashFlow = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',
					// "pemilik_aruskas in ('UNIT','GLOBAL') and (tipetransaksi='".$param['tipetransaksi']."' or akses_rekening='') and level=3",'2',true);
			// }
			
			
        } else {
            $invTab = 'keu_penagihanht';
			
			// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				 // $optCashFlow = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',
					// "pemilik_aruskas in ('HOLDING','GLOBAL') and (tipetransaksi='".$param['tipetransaksi']."' or akses_rekening='') and level=3",'2',true);
			// }else{
			  // $optCashFlow = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',
					// "pemilik_aruskas in ('UNIT','GLOBAL') and (tipetransaksi='".$param['tipetransaksi']."' or akses_rekening='') and level=3",'2',true);
			// }
			
        }
		
		// $optCashFlow = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',
					// "tipetransaksi='".$param['tipetransaksi']."' and level=3",'2',true);
					
		$optCashFlow = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',
					"level=3",'2',true);			
					
		
        $optInvoice = makeOption($dbname,$invTab,'noinvoice,noinvoice',
            "kodeorg='".$_SESSION['org']['kodeorganisasi']."'",'0',true);

                # Field Aktif
                $firstAkun = key($optAkun);
                $optField = makeOption($dbname,'keu_5akun','noakun,fieldaktif',
                        "noakun='".$firstAkun."'");
                if(empty($firstAkun)) {
                        $fieldAktif = '0000000';
                } else {
                        $fieldAktif = substr($optField[$firstAkun],3,7);
                }
				
				
			

				
				/*
                ## Jika Bank yang dipilih adalah bank pinjaman, maka akun menjadi 2140101
                if ($param['rekening']!='') {
                    $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BANKPINJAM'";
                    $res = $owlPDO->query($str);
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $bar=$res->fetch();
                    $rekpinjam=explode(',',$bar['nilai']);
                    foreach($rekpinjam as $key){
                        $arrpinjam[$key]=$key;
                    }

                    $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKUNPINJAM'";
                    $res = $owlPDO->query($str);
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $bar=$res->fetch();
                    $akpinjam=$bar['nilai'];    
                    
                        if(in_array($param['rekening'],$arrpinjam)){
                            $param['noakun']=$akpinjam;
                        }
                }
				*/

                # Get Data
                $where = "notransaksi='".$param['notransaksi'].
                        "' and kodeorg='".$param['kodeorg'].
                        "' and tipetransaksi='".$param['tipetransaksi'].
                        "' and noakun2a='".$param['noakun']."'";
                // $cols = "kode,keterangan1,noakun,noaruskas,matauang,kurs,keterangan2,jumlah,kodesegment,".
                //         "kodekegiatan,kodeasset,kodebarang,nik,kodecustomer,kodesupplier,kodevhc,orgalokasi,nodok,hutangunit1";
                $cols = "kode,keterangan1,noaruskas,noakun,matauang,kurs,keterangan3,bulan,tahun,jumlah,kodesegment,".
                         "kodekegiatan,kodeasset,nik,kodecustomer,kodesupplier,kodevhc,orgalokasi,nodok,keterangan2,hutangunit1,pemilikhutang1,lainnya";
                $query = selectQuery($dbname,'keu_kasbankdt',$cols,$where);
               // echo $query;
                $data = fetchData($query);
                $dataShow = $data;

                // Masking Segment
                $arrSegment = array();
                foreach($data as $row) {
                        $arrSegment[$row['kodesegment']] = "'".$row['kodesegment']."'";
                }
                if(!empty($arrSegment)) {
                        $whereSegment = "kodesegment in (".implode(',',$arrSegment).")";
                        $optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',
                                                                         $whereSegment,'0',true);
                } else {
                        $optSegment = array();
                }

                // Masking Akun
                $akunMask = "";
                foreach($data as $row) {
                        if(!empty($akunMask)) $akunMask.=',';
                        $akunMask .= "'".$row['noakun']."'";
                }
                if(empty($akunMask)) {
                        $optAkunMask = array();
                } else {
                        $whereMask = "noakun in (".$akunMask.")";
                        if($_SESSION['language']=='EN'){
                                $optAkunMask = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereMask,'2',true);
                        }else{
                                $optAkunMask = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereMask,'2',true);
                        }
                }
				
				$wheredz = " kodeorganisasi != '".$_SESSION['empl']['lokasitugas']."' and length(kodeorganisasi)=4";
				$optPemilikHutang = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$wheredz);
				$optPemilikHutang['']=''; ksort($optPemilikHutang);

                foreach($dataShow as $key=>$row) {
                        $dataShow[$key]['noakun'] = $optAkun[$row['noakun']];
                        $dataShow[$key]['kode'] = $optKel[$row['kode']];
                        $dataShow[$key]['nik'] = $optKary[$row['nik']];
                        $dataShow[$key]['noaruskas'] = isset($optCashFlow[$row['noaruskas']])? $optCashFlow[$row['noaruskas']]: '';
                        $dataShow[$key]['kodekegiatan'] = $optKegiatan[$row['kodekegiatan']];
                        $dataShow[$key]['kodesegment'] = $optSegment[$row['kodesegment']];
                        $dataShow[$key]['kodecustomer'] = $optCustomer[$row['kodecustomer']];
                        $dataShow[$key]['kodesupplier'] = $optSupplier[$row['kodesupplier']];
                        $dataShow[$key]['kodevhc'] = $optVhc[$row['kodevhc']];
                        $dataShow[$key]['matauang'] = $optMataUang[$row['matauang']];
                        $dataShow[$key]['noakun'] = $optAkun[$row['noakun']];
                        $dataShow[$key]['orgalokasi'] = $optOrgAl[$row['orgalokasi']];
                        $dataShow[$key]['hutangunit1'] = $optHutangUnit[$row['hutangunit1']];
                        $dataShow[$key]['bulan'] = numToMonth($row['bulan'],'I','long');
                        // $dataShow[$key]['keterangan2temp'] = $optketerangan[$row['keterangan2temp']];
                        // $dataShow[$key]['keterangan2'] = $optketerangan[$row['keterangan2']];
                        $dataShow[$key]['pemilikhutang1'] = $optPemilikHutang[$row['pemilikhutang1']];
                }

                # Form
                $theForm2 = new uForm('kasbankForm','Form Kas Bank',2);
                $theForm2->addEls('kode',$_SESSION['lang']['kode'],'','select','L',33.5,$optKel);
                $theForm2->addEls('keterangan1',$_SESSION['lang']['noinvoice'],'','text','L',33);
                $theForm2->_elements[1]->_attr['onclick'] = "searchNopo('".$_SESSION['lang']['find']." ".$_SESSION['lang']['noinvoice']."','<div id=formPencariandata></div>',event)";
                
				// $theForm2->addEls('noaruskas',$_SESSION['lang']['noaruskas'],'','select','L',25,$optCashFlow);
				$theForm2->addEls('noaruskas',$_SESSION['lang']['noaruskas'],'','selectsearch','L',31,$optCashFlow);
				$theForm2->_elements[2]->_attr['onchange'] = 'getnoakun()';
				
				$theForm2->addEls('noakun',$_SESSION['lang']['noakun'],'','selectsearch','L',31,$optAkun);
                $theForm2->_elements[3]->_attr['onchange'] = 'updFieldAktif()';
                
                $theForm2->addEls('matauang',$_SESSION['lang']['matauang'],$defMU,'select','L',10.5,$optMataUang);
               
                $theForm2->_elements[4]->_attr['disabled'] = 'disabled';  #permintaan pak rahmad per tanggal 03 june 2015 by email menambahkan dokumentasi, jamhari
                $theForm2->addEls('kurs',$_SESSION['lang']['kurs'],$defKurs,'textnum','L',10);
				$theForm2->_elements[5]->_attr['readonly'] = true;
				
				// $theForm2->addEls('keterangan2temp',$_SESSION['lang']['keterangan2'],'','text','L',33);
				// $theForm2->addEls('keterangan2temp',$_SESSION['lang']['keterangan2'],'','selectsearch','L',31,$optketerangan);
				// $theForm2->addEls('keterangan2',$_SESSION['lang']['keterangan2'],'','selectsearch','L',31,$optketerangan);
				$theForm2->addEls('keterangan3',$_SESSION['lang']['keterangan'],'DPP','text','L',33);
				$theForm2->_elements[6]->_attr['disabled'] = 'disabled';
			
				$optbulan[date('m')]=numToMonth(date('m'),'I','long');
				$opttahun[date('Y')]=date('Y');
				
				// $optbulan['']='';
				for($i=1;$i<=12;$i++){
					if($i<10){
						$i='0'.$i;
					}
					if($i!=date('m')){
						@$optbulan[$i].=numToMonth($i,'I','long');
					}
					
				}
				
				// $opttahun['']='';
				$thnskrg=date('Y');
				$thnskrglima=$thnskrg-5;
				for($i=$thnskrglima;$i<=$thnskrg;$i++){
					if($i!=date('Y')){
						@$opttahun[$i].=$i;
					}
				}
				
				
				$theForm2->addEls('bulan',$_SESSION['lang']['bulan'],'','select','L',20,$optbulan);
				$theForm2->addEls('tahun',$_SESSION['lang']['tahun'],'','select','L',20,$opttahun);
				
                $theForm2->addEls('jumlah',$_SESSION['lang']['jumlah'],number_format(hitungsisa($param['notransaksi']),2),'textnumw-','R',30);
                $theForm2->_elements[9]->_attr['onchange'] = 'this.value=remove_comma(this);this.value = _formatted(this)';
                $theForm2->addEls('kodesegment',$_SESSION['lang']['segment'],'','searchSegment','L',35);
                $theForm2->addEls('kodekegiatan',$_SESSION['lang']['kodekegiatan'],'','selectsearch','L',31,$optKegiatan);
                if(empty($fieldAktif[0])) {
                        $theForm2->_elements[11]->_attr['disabled'] = 'disabled';
                }

                $theForm2->addEls('kodeasset',$_SESSION['lang']['aktivadalam'],'','selectsearch','L',31,$optAsset);
                if(empty($fieldAktif[1])) {
                        $theForm2->_elements[12]->_attr['disabled'] = 'disabled';
                }
					 $theForm2->_elements[12]->_attr['onchange'] = 'getkeg()';
                //$theForm2->addEls('kodebarang',$_SESSION['lang']['kodebarang'],'','searchBarang','L',10);
                //if(empty($fieldAktif[2])) {
                //        $theForm2->_elements[11]->_attr['disabled'] = 'disabled';
                //}
                $theForm2->addEls('nik',$_SESSION['lang']['nik'],'','selectsearch','L',31,$optKary);
                if(empty($fieldAktif[2])) {
                        $theForm2->_elements[13]->_attr['disabled'] = 'disabled';
                }
                $theForm2->addEls('kodecustomer',$_SESSION['lang']['kodecustomer'],'','selectsearch','L',31,$optCustomer);
                if(empty($fieldAktif[3])) {
                        $theForm2->_elements[14]->_attr['disabled'] = 'disabled';
                }
                $theForm2->addEls('kodesupplier',$_SESSION['lang']['kodesupplier'],'','selectsearch','L',31,$optSupplier);
                if(empty($fieldAktif[4])) {
                        $theForm2->_elements[15]->_attr['disabled'] = 'disabled';
                }
                $theForm2->addEls('kodevhc',$_SESSION['lang']['kodevhc'],'','selectsearch','L',31,$optVhc);
                if(empty($fieldAktif[5])) {
                        $theForm2->_elements[16]->_attr['disabled'] = 'disabled';
                }
                $theForm2->addEls('orgalokasi',$_SESSION['lang']['kodeblok'],'','selectsearch','L',31,$optOrgAl);
                if(empty($fieldAktif[6])) {
                        $theForm2->_elements[17]->_attr['disabled'] = 'disabled';
                }
                $theForm2->addEls('nodok',$_SESSION['lang']['nodok'],'','textsearch','L',30.5);
                //$theForm2->_elements[16]->_attr['onclick'] = 'searchDok(event)';
				$theForm2->_elements[18]->_attr['onclick'] = "searchdok('".$_SESSION['lang']['find']." ".$_SESSION['lang']['nodok']."','<div id=formPencariandata></div>',event)";
				
				// echo makeElement('btnMemo','btn','Add From Kasbank',array('onclick'=>"searchkasbank('".$_SESSION['lang']['find']." ".$_SESSION['lang']['nojurnal']."','<div id=formPencariandata></div>',event)")).'&nbsp;';
						
				
				$theForm2->addEls('keterangan2',$_SESSION['lang']['keterangan'].' Tambahan','','text','L',33);
                $theForm2->addEls('hutangunit1',$_SESSION['lang']['hutangunit'],'','select','L',15,$optHutangUnit);
				
				
				
				
				
				$theForm2->addEls('pemilikhutang1',$_SESSION['lang']['pemilikhutang'],'','selectsearch','L',31,$optPemilikHutang);
				$theForm2->addEls('lainnya',$_SESSION['lang']['lain'],'','text','L',33);
				$theForm2->_elements[22]->_attr['disabled'] = 'disabled';
                # Table
                $theTable2 = new uTable('kasbankTable','Tabel Kas Bank',$cols,$data,$dataShow);

                # FormTable
                $formTab2 = new uFormTable('ftPrestasi',$theForm2,$theTable2,null,
                        array('notransaksi','kodeorg','noakun2a','tipetransaksi'));
                $formTab2->_target = "keu_slave_kasbank_detail";
                $formTab2->_noClearField = '##keterangan1'; // dz: buat nambahin exception yang ga di-clear
                //$formTab2->_defValue = '##matauang=IDR##kurs=1##kodesegment=##kodebarang=##keterangan=';
				//$formTab2->_defValue = '##matauang='.$defMU.'##kurs='.$defKurs.'##kodesegment=##kodebarang=##keterangan=##keterangan2=';
				// $formTab2->_defValue = '##matauang='.$defMU.'##kurs='.$defKurs.'##kodesegment=##keterangan=##keterangan2temp=';
				$formTab2->_defValue = '##matauang='.$defMU.'##kurs='.$defKurs.'##kodesegment=##keterangan=##keterangan2=';
				// $formTab2->_defValue = '##matauang='.$defMU.'##kurs='.$defKurs.'##kodesegment=##keterangan=##keterangan2temp=##jumlah='.hitungsisa($param['notransaksi']);
				$formTab2->_numberFormat = '##jumlah';
                //$formTab2->_noEnable = '##kodesegment##kodebarang##matauang##kurs';
                $formTab2->_noEnable = '##kodesegment##matauang##kurs##keterangan3';
                $formTab2->_afterEditMode = "updFieldAktif";
				$formTab2->_afterCrud = "showDetail";

                #== Display View
                # Draw Tab
                echo "<fieldset><legend><b>Tools</b></legend>";
                if($param['tipetransaksi']=='M') {
						if($_SESSION['empl']['tipelokasitugas']=='KANWIL' || $_SESSION['empl']['tipelokasitugas']=='HOLDING') {
							 echo makeElement('btnInvoice1','btn','Add from Invoice AR',array('onclick'=>"searchKontrak('".$_SESSION['lang']['find']." ".$_SESSION['lang']['noinvoice']." AR','<div id=formPencariandata></div>',event)"));
						} 
                        // echo makeElement('btnMemo','btn','Add From Kasbank',array('onclick'=>"searchkasbank('".$_SESSION['lang']['find']." ".$_SESSION['lang']['nojurnal']."','<div id=formPencariandata></div>',event)")).'&nbsp;';
                        // echo makeElement('btnMemo','btn','Add from Memorial',array('onclick'=>"searchMemo('".$_SESSION['lang']['find']." ".$_SESSION['lang']['nojurnal']."','<div id=formPencariandata></div>',event)")).'&nbsp;';
						// echo makeElement('btnMemo','btn','Add from Deposito',array('onclick'=>"searchdeposito('".$_SESSION['lang']['find']." ".$_SESSION['lang']['notransaksi']."','<div id=formPencariandata></div>',event)")).'&nbsp;';
                } else {
                        echo makeElement('btnInvoice','btn','Add from Invoice AP',array('onclick'=>"searchNopo('".$_SESSION['lang']['find']." ".$_SESSION['lang']['noinvoice']." AP','<div id=formPencariandata></div>',event)")).'&nbsp;';
						
						if($_SESSION['empl']['tipelokasitugas']=='KANWIL' || $_SESSION['empl']['tipelokasitugas']=='HOLDING') {
							 echo makeElement('btnInvoice1','btn','Add from Invoice AR (Jika Claim Melebihi Nilai Invoice AR)',array('onclick'=>"searchKontrak('".$_SESSION['lang']['find']." ".$_SESSION['lang']['noinvoice']." AR','<div id=formPencariandata></div>',event)")).'&nbsp;';
						} 
						
						
                        // echo makeElement('btnMemo','btn','Add from Memorial',array('onclick'=>"searchMemo('".$_SESSION['lang']['find']." ".$_SESSION['lang']['nojurnal']."','<div id=formPencariandata></div>',event)")).'&nbsp;';
						// echo makeElement('btnInvoice','btn','Add from Claim Medical',array('onclick'=>"searchclmed('".$_SESSION['lang']['find']." ".$_SESSION['lang']['notransaksi']." Medical','<div id=formPencariandata></div>',event)")).'&nbsp;';                       
                        // echo makeElement('btnInvoice','btn','Add from Angsuran',array('onclick'=>"searchcangsrn('".$_SESSION['lang']['find']." ".$_SESSION['lang']['nik']." ','<div id=formPencariandata></div>',event)")).'&nbsp;';                       
				}
                echo makeElement('btnMemo','btn','Add Data',array('onclick'=>"searchdata('".$_SESSION['lang']['find']." ".$_SESSION['lang']['notransaksi']."','<div id=formPencariandata></div>',event)")).'&nbsp;';
   
        echo "</fieldset>";
                echo "<fieldset><legend><b>Detail</b></legend>";
                $formTab2->render();
                echo "</fieldset>";
                break;

    case 'add':
                cekVendorKasKecil(); // Cek Vendor Kas Kecil
				 $data = $param;
				   // echo"<pre>";
        // print_r($data);
        // echo"</pre>";
        // exit("Error:");
        $data['keterangan2']=gantierror($data['keterangan2']);
      
				 
				 		 
				
		#= cek data jika hutangunit dan tidak ada pilihan unitnya, maka exit
		if ($data['hutangunit1']=='1' and $data['pemilikhutang1']=='') {
			exit("Warning:Jika hutang unit dipilih Ya, maka kolom Pemilik Hutang tidak boleh kosong ");
		}
				
		if ($data['hutangunit1']=='0' and $data['pemilikhutang1']!='') {
			exit("Warning:Jika hutang unit dipilih Tidak, maka kolom Pemilik Hutang harus dikosongkan ");
		}
		
		#= cek data sudah ada atau belum
		$jumlahdata=0;
		$str="select count(*) as jumlahdata from ".$dbname.".keu_kasbankdt where 
		notransaksi='".$data['notransaksi']."' and 
		noakun='".$data['noakun']."' and 
		tipetransaksi='".$data['tipetransaksi']."' and 
		noakun2a='".$data['noakun2a']."' and 
		keterangan1='".$data['keterangan1']."' and 
		keterangan2='".$data['keterangan2']."' and 
		kodeorg='".$data['kodeorg']."' and
		keterangan3='".$data['keterangan3']."' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$jumlahdata=$bar['jumlahdata'];
		}
		
		if($jumlahdata>0){
			exit("Warning:Data Untuk\nNo. Transaksi : ".$data['notransaksi']."\nNo. Akun : ".$data['noakun']."\nInvoice : ".$data['keterangan1']."\nKeterangan : ".$data['keterangan3']."\nSudah Terinput/Sudah Pernah di-input");
		}

		// paksa user mengisi sesuai field
        $str="select fieldaktif from ".$dbname.".keu_5akun where noakun = '".$data['noakun']."' and namaakun like '%piutang%' ";
        $res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $fieldaktif=$bar['fieldaktif']; // 101000100000
        }

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";

// <pre>Array
// (
//     [kode] => KK
//     [keterangan1] => 
//     [noaruskas] => 211001
//     [noakun] => 1130101
//     [matauang] => IDR
//     [kurs] => 1
//     [keterangan2] => 
//     [bulan] => 07
//     [tahun] => 2021
//     [jumlah] => 1,111.00
//     [kodesegment] => 
//     [kodekegiatan] => 
//     [kodeasset] => 
//     [nik] => 
//     [kodecustomer] => 
//     [kodesupplier] => 
//     [kodevhc] => 
//     [orgalokasi] => 
//     [nodok] => 
//     [keterangan3] => tes
//     [hutangunit1] => 0
//     [pemilikhutang1] => 
//     [notransaksi] => 20210712/SNPE/KK/00008
//     [kodeorg] => SNPE
//     [noakun2a] => 1112102
//     [tipetransaksi] => K
//     [numRow] => 2
// )        
            // digit1=kasbank 2=tagihan, 3=jurnalmemo, 4=Kode Kegiatan, 5=Kode Asset, 6=No Induk Karyawan, 7=Kode Pelanggan, 8=Kode Supplier, 9=Kode Kendaraan, 10=Kode Blok, 11=Nota Debet, 12=Nota Kredit

        $length = strlen($fieldaktif);
        $harusisi = array();
        for ($i=0; $i<$length; $i++) {
            $harusisi[$i] = $fieldaktif[$i]; // 1 0 1 0 0 0 1 0 0 0 0 0 error: sini sini: 101000100000 1130101
        }

        $silakanisi=''; // ganti jadi atau / salah satu field harus diisi
        if(($harusisi[3]=='1')and($data['kodekegiatan']=='')){ // 4=Kode Kegiatan
            $silakanisi.='Silakan isi Kode Kegiatan untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Kegiatan wajib terisi.');
        }
        if(($harusisi[4]=='1')and($data['kodeasset']=='')){ // 5=Kode Asset
            $silakanisi.='Silakan isi Kode Asset untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Asset wajib terisi.');
        }
        if(($harusisi[5]=='1')and($data['nik']=='')){ // 6=No Induk Karyawan
            $silakanisi.='Silakan isi No Induk Karyawan untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' No Induk Karyawan wajib terisi.');
        }
        if(($harusisi[6]=='1')and($data['kodecustomer']=='')){ // 7=Kode Pelanggan
            $silakanisi.='Silakan isi Kode Pelanggan untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Pelanggan wajib terisi.');
        }
        if(($harusisi[7]=='1')and($data['kodesupplier']=='')){ // 8=Kode Supplier
            $silakanisi.='Silakan isi Kode Asignment untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Asignment wajib terisi.');
        }
        if(($harusisi[8]=='1')and($data['kodevhc']=='')){ // 9=Kode Kendaraan
            $silakanisi.='Silakan isi Kode Kendaraan untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Kendaraan wajib terisi.');
        }
        if(($harusisi[9]=='1')and($data['orgalokasi']=='')){ // 10=Kode Blok
            $silakanisi.='Silakan isi Kode Blok untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Blok wajib terisi.');
        }
        if($silakanisi!=''){
            exit('warning : '.$silakanisi);
        }
        // exit("error: sini sini: ".$fieldaktif." ".$data['noakun']);
				
				 
                unset($data['numRow']);
                if ($data['keterangan3']=='') {
                    exit('warning : Keterangan Tambahan wajib terisi.');
                } 
				if ($data['noaruskas']=='') {
                    exit('warning : Arus Kas wajib terisi.');
                }

                // $cols = array(
                //         'kode','keterangan1','noakun','noaruskas','matauang','kurs','keterangan2',
                //         'jumlah','kodesegment','kodekegiatan','kodeasset','kodebarang','nik','kodecustomer',
                //         'kodesupplier','kodevhc','orgalokasi','nodok','hutangunit1','notransaksi','kodeorg','noakun2a','tipetransaksi'
                // );
                $cols = array(
                        'kode','keterangan1','noaruskas','noakun','matauang','kurs','keterangan3',
						'bulan','tahun',
                        'jumlah','kodesegment','kodekegiatan','kodeasset','nik','kodecustomer',
                        'kodesupplier','kodevhc','orgalokasi','nodok','keterangan2','hutangunit1','pemilikhutang1','lainnya',
						'notransaksi','kodeorg','noakun2a','tipetransaksi'
                );
               
				/*
                ## Jika Bank yang dipilih adalah bank pinjaman, maka akun menjadi 2140101
                $query = selectQuery($dbname,'keu_kasbankht',"*","notransaksi='".$param['notransaksi']."'");
                $tmpData = fetchData($query);
				$datah = $tmpData[0];

                if ($datah['rekening']!='') {
                    $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BANKPINJAM'";
                    $res = $owlPDO->query($str);
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $bar=$res->fetch();
                    $rekpinjam=explode(',',$bar['nilai']);
                    foreach($rekpinjam as $key){
                        $arrpinjam[$key]=$key;
                    }

                    $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKUNPINJAM'";
                    $res = $owlPDO->query($str);
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $bar=$res->fetch();
                    $akpinjam=$bar['nilai'];

                    if(in_array($datah['rekening'],$arrpinjam)){
                        $data['noakun2a']=$akpinjam;
                    }
                }
				*/
				
                //=====tambahan ginting
                #periksa apakah akun tanaman, dan jika akun tanaman maka harus ada kodeblok
                        if($data['kurs']==0 || $data['kurs'] == ''){
                                exit("warning : ".$_SESSION['lang']['notifcurrency']);
                        }
						if($data['noakun']==''){
							exit("Warning : Please Input Account Number");
						}

                        $blk=str_replace(" ","",$data['orgalokasi']);
                        $nik=str_replace(" ","",$data['nik']);        
                        $sup=str_replace(" ","",$data['kodesupplier']);
                        $vhc=str_replace(" ","",$data['kodevhc']);    
                        $nodok=str_replace(" ","",$data['nodok']);    
                        
                        
                        if(cekAkun($data['noakun']) and $blk==''){
                                exit("warningg : ".$_SESSION['lang']['notifakuntanaman']);
                        }else
                         if(cekAkun($data['noakun']) and $data['kodekegiatan']==''){
                                exit("warning : ".$_SESSION['lang']['notifkodekegiatan']);
                        }else if(cekAkunPiutang($data['noakun']) and ($nik=='' or $sup=='') and $nodok==''){
                                exit("warning : ".$_SESSION['lang']['notifkaryawan']." atau Assignment dan nomor dokumen tidak boleh kosong");
                        }else if(cekAkunHutang($data['noakun']) and $data['noakun'] != '2120300' and ($sup=='' or $data['keterangan1']=='')){
                                exit("warning : ".$_SESSION['lang']['notifkodesupplier']." / noinvoice masih kosong ");
                        }else if(cekAkunTrans($data['noakun']) and $vhc==''){
                                exit("warning : ".$_SESSION['lang']['notifkodevhc']);
                        }
                        //=====end tambahan ginting
                        //
                        //              
                        # Additional Default Data
						
				#= cek kodekegiatan harus sesuai dengan akunnya		
				if($data['kodekegiatan']!=''){
					if(substr($data['kodekegiatan'],0,7)!=$data['noakun']){
						exit("Warning:Kodepekerjaan/Kodekegiatan tidak sesuai dengan nomor akun yang dipilih");
					}
				}
				
				#= cek jika akun sudah interco/intraco
				#= hutang unit tidak perlu diisi
				$str="select * from ".$dbname.".keu_5caco";
				$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar = $res->fetch()){
					$akuncacao[$bar['akunpiutang']]=$bar['akunpiutang'];
					$akuncacao[$bar['akunhutang']]=$bar['akunhutang'];
					$unitcaco[$bar['akunpiutang']]=$bar['kodeorg'];
					$unitcaco[$bar['akunhutang']]=$bar['kodeorg'];
				}

				if(in_array($param['noakun'],$akuncacao)){
					
					if($param['hutangunit1']!='0'){
						exit("Warning:Jika akun intraco/interco tidak perlu mengisikan hutang unit");
					}
					if($param['pemilikhutang1']!=''){
						exit("Warning:Jika akun intraco/interco tidak perlu mengisikan pemilih hutang unit");
					}
				}
				
				
				#= cek transaksi ht
				$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$data['notransaksi']."'";
				$res = $owlPDO->query($str);
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$autokb=$bar['autokb'];
					
				if($autokb==1){
					if($data['noakun']=='1119101' or $data['noakun']=='9290301'){
					}else{
						exit("Warning:Jika auto kas/bank hanya diperbolehkan akun ayat silang dan biaya admin bank");
					}
				}					
				
				
				
                // #cek transaksi hutang unit
				  /*
                $sCekHtg="select * from ".$dbname.".keu_kasbankht where notransaksi='".$data['notransaksi']."'";
                $rCekHtg=fetchData($sCekHtg);
              
                if($data['hutangunit1']==1){
                    if(substr($data['noakun'],0,3)!='121'){
                        exit('warning: '.$_SESSION['lang']['noakun'].'Bukan Akun R/K');
                    }   
                    if($rCekHtg[0]['hutangunit']==0){
                        exit('warning: Pada Header Hutang unit belum tercentang');
                    }
                }
				*/
                // if($data['hutangunit1']==0){
                    // if(substr($data['noakun'],0,3)=='121'){
                        // exit('warning: Hutang Unit Pada Detail Harus Terisi Ya');
                    // }      
                // }
                #jika ada kode asset cek noakunnya
                $sAkn="select akunak from ".$dbname.".sdm_5tipeasset where kodetipe='".substr($param['kodeasset'],3,2)."'";
                $rAkn=fetchdata($sAkn);
                if(count($rAkn)==1){
                    if($rAkn[0]['akunak']!=$param['noakun']){
                        exit('warning: '.$_SESSION['lang']['noakun'].' Salah');
                    }
                }

                $data['jumlah'] = str_replace(',','',$data['jumlah']);
				// $data['keterangan2temp']=$data['keterangan2temp'];
				// $data['keterangan2']=$data['keterangan2'];
                #sementara waktu
                // $noket=0;
                // $sItungKet="select keterangan2temp from ".$dbname.".keu_kasbankdt where notransaksi='".$data['notransaksi']."' and keterangan2temp='".$data['keterangan2temp']."'";
                // $rItungKet=fetchData($sItungKet);
                // if(count($rItungKet)==0){
                    // $noket=1;
                // }else{
                    // $noket=count($rItungKet)+1;
                // }
				// $data['keterangan2']=trim($optketerangan[$data['keterangan2temp']].' '.numToMonth($data['bulan'],'I','long').' '.$data['tahun'].'##'.$noket);
				// $data['keterangan2']=trim($optketerangan[$data['keterangan2temp']].' '.numToMonth($data['bulan'],'I','long').' '.$data['tahun']);
				
				
				
			
                $query = insertQuery($dbname,'keu_kasbankdt',$data,$cols);
				// exit("Error:$query");
                try{
					$owlPDO->exec($query); 
					
				}catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); 
				}

				
				
				
                unset($data['notransaksi']);unset($data['kodeorg']);
                unset($data['noakun2a']);unset($data['tipetransaksi']);unset($data['keterangan2']);

                $res = "";
                foreach($data as $cont) {
                        $res .= "##".$cont;
                }

                $result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
                echo $result;
				
				// echo"<script>hitungsaja()<script>";
				
				// hitungsisa($param['notransaksi']);
				
								
				
				
	break;

    case 'edit':
		
		cekVendorKasKecil(); // Cek Vendor Kas Kecil
		$data = $param;
        $data['keterangan3']=gantierror($data['keterangan3']);
		
		#= cek data jika hutangunit dan tidak ada pilihan unitnya, maka exit
		if ($data['hutangunit1']=='1' and $data['pemilikhutang1']=='') {
			exit("Warning:Jika hutang unit dipilih Ya, maka kolom Pemilik Hutang tidak boleh kosong ");
		}
				
		if ($data['hutangunit1']=='0' and $data['pemilikhutang1']!='') {
			exit("Warning:Jika hutang unit dipilih Tidak, maka kolom Pemilik Hutang harus dikosongkan ");
		}

        // paksa user mengisi sesuai field (khusus piutang)
        $str="select fieldaktif from ".$dbname.".keu_5akun where noakun = '".$data['noakun']."' and namaakun like '%piutang%' ";
        $res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $fieldaktif=$bar['fieldaktif']; // 101000100000
        }

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";

// <pre>Array
// (
//     [kode] => KK
//     [keterangan1] => 
//     [noaruskas] => 211001
//     [noakun] => 1130101
//     [matauang] => IDR
//     [kurs] => 1
//     [keterangan2] => 
//     [bulan] => 07
//     [tahun] => 2021
//     [jumlah] => 1,111.00
//     [kodesegment] => 
//     [kodekegiatan] => 
//     [kodeasset] => 
//     [nik] => 
//     [kodecustomer] => 
//     [kodesupplier] => 
//     [kodevhc] => 
//     [orgalokasi] => 
//     [nodok] => 
//     [keterangan3] => tes
//     [hutangunit1] => 0
//     [pemilikhutang1] => 
//     [notransaksi] => 20210712/SNPE/KK/00008
//     [kodeorg] => SNPE
//     [noakun2a] => 1112102
//     [tipetransaksi] => K
//     [numRow] => 2
// )        
            // digit1=kasbank 2=tagihan, 3=jurnalmemo, 4=Kode Kegiatan, 5=Kode Asset, 6=No Induk Karyawan, 7=Kode Pelanggan, 8=Kode Supplier, 9=Kode Kendaraan, 10=Kode Blok, 11=Nota Debet, 12=Nota Kredit

        $length = strlen($fieldaktif);
        $harusisi = array();
        for ($i=0; $i<$length; $i++) {
            $harusisi[$i] = $fieldaktif[$i]; // 1 0 1 0 0 0 1 0 0 0 0 0 error: sini sini: 101000100000 1130101
        }

        $silakanisi=''; // ganti jadi atau / salah satu field harus diisi
        if(($harusisi[3]=='1')and($data['kodekegiatan']=='')){ // 4=Kode Kegiatan
            $silakanisi.='Silakan isi Kode Kegiatan untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Kegiatan wajib terisi.');
        }
        if(($harusisi[4]=='1')and($data['kodeasset']=='')){ // 5=Kode Asset
            $silakanisi.='Silakan isi Kode Asset untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Asset wajib terisi.');
        }
        if(($harusisi[5]=='1')and($data['nik']=='')){ // 6=No Induk Karyawan
            $silakanisi.='Silakan isi No Induk Karyawan untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' No Induk Karyawan wajib terisi.');
        }
        if(($harusisi[6]=='1')and($data['kodecustomer']=='')){ // 7=Kode Pelanggan
            $silakanisi.='Silakan isi Kode Pelanggan untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Pelanggan wajib terisi.');
        }
        if(($harusisi[7]=='1')and($data['kodesupplier']=='')){ // 8=Kode Supplier
            $silakanisi.='Silakan isi Kode Asignment untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Asignment wajib terisi.');
        }
        if(($harusisi[8]=='1')and($data['kodevhc']=='')){ // 9=Kode Kendaraan
            $silakanisi.='Silakan isi Kode Kendaraan untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Kendaraan wajib terisi.');
        }
        if(($harusisi[9]=='1')and($data['orgalokasi']=='')){ // 10=Kode Blok
            $silakanisi.='Silakan isi Kode Blok untuk akun '.$data['noakun'].', ';
            // exit('warning : Untuk akun '.$data['noakun'].' Kode Blok wajib terisi.');
        }
        if($silakanisi!=''){
            exit('warning : '.$silakanisi);
        }

        // exit("error: sini sini: ".$fieldaktif." ".$data['noakun']);		
		
		#= cek jika akun kepala 8 hanya boleh dipakai di tipe unit : KANWIL,HOLDING,TC,RND,BULKING
		#= cek jika akun kepala 8 hanya boleh dipakai di tipe unit : KANWIL,HOLDING,TC,RND,BULKING, tidak boleh untuk KEBUN, PABRIK
		#= algoritma, jika hutang unit dicentang, maka validasi memakai hutang unit
		#= contoh jika header KSRD dan hutang unit terisi KSPE, maka akun kepala 8 tidak boleh tersimpan
		
		if($data['hutangunit1']=='1'){
			$unitdipakai=$data['pemilikhutang1'];
		}else{
			$unitdipakai=$data['kodeorg'];	
		}
		
		if($tipeorgunit[$unitdipakai]=='KEBUN' || $tipeorgunit[$unitdipakai]=='PABRIK'){
			#= tidak ada boleh kepala 8
			if(substr($data['noakun'],0,1)=='8'){
				exit("Warning: Untuk Tipe unit kebun dan pabrik, tidak diperbolehkan akun kepala 8");
			}
			
		}
		
		
		if($data['kurs']==0 || $data['kurs'] == ''){
				exit("warning : ".$_SESSION['lang']['notifcurrency']);
		}
		if($data['noakun']==''){
					exit("Warning : Please Input Account Number");
		}
		

		// exit("Error:".$data['nodok']);
		$blk=str_replace(" ","",$data['orgalokasi']);
		$nik=str_replace(" ","",$data['nik']);        
		$sup=str_replace(" ","",$data['kodesupplier']);
		$vhc=str_replace(" ","",$data['kodevhc']);             
		$nodok=str_replace(" ","",$data['nodok']);             
		if(cekAkun($data['noakun']) and $blk==''){
				exit("warning : ".$_SESSION['lang']['notifakuntanaman']);
		} else if(cekAkun($data['noakun']) and $data['kodekegiatan']==''){
				exit("warning : ".$_SESSION['lang']['notifkodekegiatan']);
				
		}else if(cekAkunPiutang($data['noakun']) and ($nik=='' or $sup=='') and $nodok==''){
				exit("warning : ".$_SESSION['lang']['notifkaryawan']." atau Assignment dan nomor dokumen tidak boleh kosong");
		}else if(cekAkunHutang($data['noakun']) and $data['noakun']!='2120300' and $sup=='' and $data['keterangan1']==''){
				exit("warning : ".$_SESSION['lang']['notifkodesupplier']." / noinvoice masih kosong ");
		}else if(cekAkunTrans($data['noakun']) and $vhc==''){
				exit("warning : ".$_SESSION['lang']['notifkodevhc']);
		}
		// #cek transaksi hutang unit
		$sCekHtg="select * from ".$dbname.".keu_kasbankht where notransaksi='".$data['notransaksi']."'";
		$rCekHtg=fetchData($sCekHtg);
		
		
		/*
		if($data['hutangunit1']==1){
			if(substr($data['noakun'],0,3)!='121'){
				exit('warning: '.$_SESSION['lang']['noakun'].'Bukan Akun R/K');
			}   
			if($rCekHtg[0]['hutangunit']==0){
				exit('warning: Pada Header Hutang unit belum tercentang');
			}
		}
		*/
		
		
		
		
		// if($data['hutangunit1']==0){
			// if(substr($data['noakun'],0,3)=='121'){
				// exit('warning: Hutang Unit Pada Detail Harus Terisi Ya');
			// }      
		// }
		
		
		#= cek kodekegiatan harus sesuai dengan akunnya		
		if($data['kodekegiatan']!=''){
			if(substr($data['kodekegiatan'],0,7)!=$data['noakun']){
				exit("Warning:Kodepekerjaan/Kodekegiatan tidak sesuai dengan nomor akun yang dipilih");
			}
		}
		
		#= cek jika akun sudah interco/intraco
		#= hutang unit tidak perlu diisi
		$str="select * from ".$dbname.".keu_5caco";
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$akuncacao[$bar['akunpiutang']]=$bar['akunpiutang'];
			$akuncacao[$bar['akunhutang']]=$bar['akunhutang'];
			$unitcaco[$bar['akunpiutang']]=$bar['kodeorg'];
			$unitcaco[$bar['akunhutang']]=$bar['kodeorg'];
		}

		if(in_array($param['noakun'],$akuncacao)){
					
			if($param['hutangunit1']!='0'){
				exit("Warning:Jika akun intraco/interco tidak perlu mengisikan hutang unit");
			}
			if($param['pemilikhutang1']!=''){
				exit("Warning:Jika akun intraco/interco tidak perlu mengisikan pemilih hutang unit");
			}
		}
				
		#= cek transaksi ht
		$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$data['notransaksi']."'";
		$res = $owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$autokb=$bar['autokb'];
			
		if($autokb==1){
			if($data['noakun']=='1119101' or $data['noakun']=='9290301'){
			}else{
				exit("Warning:Jika auto kas/bank hanya diperbolehkan akun ayat silang dan biaya admin bank");
			}
		}	
		
		
		#jika ada kode asset cek noakunnya
		$sAkn="select akunak from ".$dbname.".sdm_5tipeasset where kodetipe='".substr($param['kodeasset'],3,2)."'";
		$rAkn=fetchdata($sAkn);
		if(count($rAkn)==1){
			if($rAkn[0]['akunak']!=$param['noakun']){
				exit('warning: '.$_SESSION['lang']['noakun'].' Salah');
			}
		}

		unset($data['notransaksi']);
		foreach($data as $key=>$cont) {
				if(substr($key,0,5)=='cond_') {
				unset($data[$key]);
				}
		}

		
		// $sItungKet="select keterangan2temp from ".$dbname.".keu_kasbankdt where notransaksi='".$data['notransaksi']."' and keterangan2='".$data['keterangan2']."'";
		// $rItungKet=fetchData($sItungKet);
		
		// $data['keterangan2']='';
		// if($data['keterangan2temp']!=$rItungKet[0]['keterangan2temp']){
			// #sementara waktu
			// $noket=0;
			// $sItungKet="select keterangan2temp from ".$dbname.".keu_kasbankdt where notransaksi='".$data['notransaksi']."' and keterangan2temp='".$data['keterangan2temp']."'";
			// $rItungKet=fetchData($sItungKet);
			// if(count($rItungKet)==0){
				// $noket=1;
			// }else{
				// $noket=count($rItungKet)+1;
			// }    
			// // $data['keterangan2']=trim($optketerangan[$data['keterangan2temp']].' '.numToMonth($data['bulan'],'I','long').' '.$data['tahun']."##".$noket);
			// $data['keterangan2']=trim($optketerangan[$data['keterangan2temp']].' '.numToMonth($data['bulan'],'I','long').' '.$data['tahun']);
			
		// }else{
			// $data['keterangan2']=$data['keterangan2'];    
		// }

		## Jika Bank yang dipilih adalah bank pinjaman, maka akun menjadi 2140101
		/*
		$query = selectQuery($dbname,'keu_kasbankht',"*","notransaksi='".$param['notransaksi']."'");
		$tmpData = fetchData($query);
		$datah = $tmpData[0];

		if ($datah['rekening']!='') {
			$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BANKPINJAM'";
			$res = $owlPDO->query($str);
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$rekpinjam=explode(',',$bar['nilai']);
			foreach($rekpinjam as $key){
				$arrpinjam[$key]=$key;
			}

			$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKUNPINJAM'";
			$res = $owlPDO->query($str);
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$akpinjam=$bar['nilai'];

			if(in_array($datah['rekening'],$arrpinjam)){
				$param['noakun2a']=$akpinjam;
				$data['noakun2a']=$akpinjam;
			}
		}
		*/
		
		$data['jumlah'] = str_replace(',','',$data['jumlah']);
		// $data['keterangan2']=trim($optketerangan[$data['keterangan2temp']].' '.numToMonth($data['bulan'],'I','long').' '.$data['tahun']);

		$where = "notransaksi='".$param['notransaksi'].
				"' and noakun='".$param['cond_noakun'].
				"' and tipetransaksi='".$param['tipetransaksi'].
				"' and noakun2a='".$param['noakun2a'].
				"' and keterangan1='".$param['cond_keterangan1'].
				"' and keterangan2='".$param['cond_keterangan2'].
				"' and kodeorg='".$param['kodeorg'].
				"' and keterangan3='".$param['cond_keterangan3']."'";
		$query = updateQuery($dbname,'keu_kasbankdt',$data,$where);
		// exit("Error:".$query);
		try{
			$owlPDO->exec($query); 

			if (substr($param['cond_keterangan1'],0,3)=='MOD' || substr($param['cond_keterangan1'],0,3)=='DIV') {
				//sum jumlah modal yg telah diambil di kasbank
				$strak="select sum(jumlah) as jumlahtot from ".$dbname.".keu_kasbankdt where keterangan1='".$param['cond_keterangan1']."' and tipetransaksi='".$param['tipetransaksi']."'";
				$resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
				$resak->setFetchMode(PDO::FETCH_ASSOC);
				$barak=$resak->fetch();
				$jumlahkasbank=$barak['jumlahtot'];

				//ambil jumlah nilai modal 
				$strak="select nilai,unit1 from ".$dbname.".keu_dividen where notransaksi='".$param['cond_keterangan1']."'";
				$resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
				$resak->setFetchMode(PDO::FETCH_ASSOC);
				$barak=$resak->fetch();
				$nilaimodal=$barak['nilai'];

				if ($barak['unit1']==$param['kodeorg']) {
					if ($jumlahkasbank<$nilaimodal) {
						$set=" statusunit1=0 ";
					}
					if ($jumlahkasbank==$nilaimodal) {
						$set=" statusunit1=1 ";
					}
				}else{
					if ($jumlahkasbank<$nilaimodal) {
						$set=" statusunit2=0 ";
					}
					if ($jumlahkasbank==$nilaimodal){
						$set=" statusunit2=1 ";
					}
				}

				if ($jumlahkasbank<$nilaimodal || $jumlahkasbank==$nilaimodal) {

					$strht = "update ".$dbname.".keu_dividen set ".$set." where notransaksi='".$param['cond_keterangan1']."'";             
					try
					{
						$owlPDO->exec($strht);
					}
					catch (PDOException $e)
					{
						print " Gagal  !: " . $e->getMessage() . "\n";
						die();
					}
				}
			}
			
		}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
		echo json_encode($param);
	break;

				
    case 'delete':
						// echo"<pre>";
						// print_r($param);
						// echo"</pre>";
						// exit("Error:");
					
						
						
					
					#= update fee	
					
					if($param['nodok']!=''){
						$dataexpl=explode("#",$param['nodok']);
						if($dataexpl[0]=='pf'){	
							$tipedata=$dataexpl[0];
							$id=$dataexpl[1];
							$per=$dataexpl[2];
							$jenisfee=$dataexpl[3];
							$strupdate = "update ".$dbname.".kebun_rekapangkutantbsdtfee set bayar=0 where 
							id='".$id."' and jenisfee='".$jenisfee."' and id='".$id."' and periode='".$per."'";             
							try {
								$owlPDO->exec($strupdate);
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "\n";
								die();
							}
						}
					}

                $where = "notransaksi='".$param['notransaksi']."' 
						and kodeorg='".$param['kodeorg']."' 
						and noakun='".$param['noakun']."' 
						and tipetransaksi='".$param['tipetransaksi']."' 
						and keterangan1='".$param['keterangan1']."' 
						and keterangan3='".$param['keterangan3']."'
						and keterangan2='".$param['keterangan2']."'";
						   
                $query = "delete from `".$dbname."`.`keu_kasbankdt` where ".$where;
				// exit("Error:".$query);
                try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
                break;
    case 'updField':
	
	
				getnoakundata();
                $optField = makeOption($dbname,'keu_5akun','noakun,fieldaktif',
                        "noakun='".$param['noakun']."'");
						
					
                // echo $optField[$param['noakun']];
				$data=trim(substr($optField[$param['noakun']],3,8));
				echo $data;
				
					// echo"<pre>";
						// print_r($optField);
						// echo"</pre>";
						// exit("Error:");
				
    break;
	
	case'getformdok':
		$hide="hidden";
		if($param['noakun']=='1180300'){ //no akun uang muka perjalanan dinas
			$hide='';
		}
		$form="";
		$form = "<fieldset  style=width:94%><legend>".$_SESSION['lang']['find']."</legend>";
		$form.= "<table>";
        $form.= "<tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=notran></td></tr>";

        $form.= "<tr ".$hide."><td>".$_SESSION['lang']['tipe']."</td><td>:</td>";
        $form.= "<td><input type=radio id=r1 name=gender value=um checked=checked title='Pengambilan Uang Muka Perjalanan Dinas'>UM &nbsp;
					<input type=radio id=r2 name=gender value=pjd title='Pertanggung Jawaban Uang Muka Perjalanan Dinas'>PJD<br></td></tr>";
        $form.= "</table>";
        $form.= "<button class=mybutton onclick=finddok(0)>Find</button></fieldset>
				 <div id=container2></div>";
        echo $form;
	break;	
	
	case'getdatadok':
	
		if($param['noakun']=='1180300'){
			$data="";
			$data.= "<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
			$data.="<table cellpadding=0 cellspacing=1 width=100% class=sortable>";
			$data.= "<thead><tr>";
			$data.= "<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
			$data.= "<td align=center>".$_SESSION['lang']['nik2']."</td>";
			$data.= "<td align=center>".$_SESSION['lang']['namakaryawan']."</td>";
			$data.= "<td align=center>".$_SESSION['lang']['rupiah']."</td>";
			$data.= "</tr></thead>";
			
			if($param['notran']!=''){
				$where.=" and b.notransaksi like '%".$param['notran']."%'  ";
			}
			
			$where.=" and b.notransaksi not in (select nodok from ".$dbname.".keu_kasbankdt)";
			
			
			//and b.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$param['unit']]."')
			
			// and   b.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$param['unit']]."')
			if($param['tipe']=='um'){
				$str="select b.notransaksi,b.karyawanid,b.uangmuka as rupiah from ".$dbname.".sdm_pjdinasht b where 1=1
					 ".$where." ";
			}else if($param['tipe']=='pjd'){
				$where = "a.noakun='".$param['noakun']."' and a.notransaksi != '".
                        $param['notran']."' and a.tipetransaksi='K' and b.posting=1 ";
				$str="SELECT a.jumlah as rupiah,a.nik as karyawanid,a.notransaksi as notransaksi,b.posting from ".$dbname.".keu_kasbankdt a
                        LEFT JOIN ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
                        WHERE ".$where;
				
				// $str="select sum(a.jumlahhrd) as rupiah,a.notransaksi,b.karyawanid 
					// from ".$dbname.".sdm_pjdinasdt a left join ".$dbname.".sdm_pjdinasht b on a.notransaksi=b.notransaksi 
					// where 1=1 and b.kodeorg='".$param['unit']."' ".$where." group by a.notransaksi ";
					//	exit("Error:$str");
			}
			
			#data
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getdok('".$bar['notransaksi']."','".$bar['karyawanid']."','".$bar['rupiah']."')>";
				$data.= "<td>".$bar['notransaksi']."</td>";
				$data.= "<td>".@$nikkar[$bar['karyawanid']]."</td>";
				$data.= "<td>".@$nmkar[$bar['karyawanid']]."</td>";
				$data.= "<td align=right>".number_format($bar['rupiah'])."</td>";
				$data.= "</tr>";	
			}
			$data.= "</table></fieldset>";
			echo $data;
		}else{
				$where = "a.noakun='".$param['noakun']."' and a.notransaksi != '".
                        $param['notran']."' and a.tipetransaksi='K' and b.posting=1 and b.kodeorg='".$param['unit']."' ";
                if(!empty($param['nik'])) $where .= " and a.nik='".$param['nik']."'";
                $query1 = "SELECT a.*,b.posting from ".$dbname.".keu_kasbankdt a
                        LEFT JOIN ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
                        WHERE ".$where;
                $res1 = fetchData($query1);
			

                // Get Transaksi yang sudah dipertanggungjawabkan
                $where2 = "nodok is not null and nodok <> '' and tipetransaksi='K'";
                $res2 = makeOption($dbname,'keu_kasbankdt','notransaksi,notransaksi',$where2);

                // Filter
                $res3 = array();
                $listKary = array();
                foreach($res1 as $row) {
                        $listKary[$row['nik']] = $row['nik'];
                        if(!in_array($row['notransaksi'],$res2)) {
                                $res3[] = $row;
                        }
                }

                if(!empty($listKary)) {
                        $optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid in ('".implode("','",$listKary)."')");
                } else {
                        $optKary = array();
                }
				
                $res = "<div style='max-height:300px;max-width:500px;overflow:auto'><table style=width:100%><thead>";
                $res .= "<tr class=rowheader>";
                $res .= "<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
                $res .= "<td align=center>".$_SESSION['lang']['namakaryawan']."</td>";
                $res .= "<td align=center>".$_SESSION['lang']['jumlah']."</td>";
                $res .= "</tr>";
                $res .= "</thead><tbody>";
                if(empty($res3)) {
                        $res .= "<tr class=rowcontent><td colspan=3>Data Kosong</td></tr>";
                } else {
                        foreach($res3 as $row) {
                                $res .= "<tr class=rowcontent onclick='getdok(\"".$row['notransaksi']."\",\"".
                                        $row['nik']."\",\"".number_format($row['jumlah'],2)."\")'>";
                                $res .= "<td style=cursor:pointer>".$row['notransaksi']."</td>";
                                $res .= "<td style=cursor:pointer>".@$optKary[$row['nik']]."</td>";
                                $res .= "<td  style=cursor:pointer align=right>".number_format($row['jumlah'],2)."</td>";
                                $res .= "</tr>";
                        }
                }
                $res .= "</tbody></table></div>";

                echo $res;
		}
	break;
	
	case'getformclmed':

        $optbulan="<option value=''>".$_SESSION['lang']['bulan']."</option>";
        $opttahun="<option value=''>".$_SESSION['lang']['tahun']."</option>";
        for($i=1;$i<=12;$i++){
            if($i<10){
                $i='0'.$i;
            }
            $optbulan.="<option value='".$i."'>".numToMonth($i,'I','long')."</option>";
        }

        $thnskrg=date('Y');
        $thnskrglima=$thnskrg-5;
        for($i=$thnskrglima;$i<=$thnskrg;$i++){
            $opttahun.="<option value='".$i."'>".$i."</option>";
        }


		$form = "<fieldset style=width:94%><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=notran></td>";
        $form.= "<td><select id=bulanclmed style='width:100px;' placeholder='Bulan'>".$optbulan."</select></td>";
        $form.= "<td><select id=tahunclmed style='width:100px;' placeholder='Tahun'>".$opttahun."</select></td>";
        $form.= "</table>";
        $form.= "<button class=mybutton onclick=findclmed(0)>Find</button></fieldset>
				 <div id=container2></div>";
        echo $form;
	break;	

    case'getfilex':
		$form='';
        #data
        $str="select * from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' ";
        // echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
        @$icon = seticonfile($bar['formaticon']);
        $form.= "<tr>";
		$form.="<td align='center'><img src=".$icon." class=resicon></a></td>";
        $form.= "<td>".getcriterianame($bar['kriteriaefil'])."</td>";
        $form.= "<td><a href='".$path.$bar['namafile']."' download>".$bar['namafile']."</td>";
        $form.= "<td><a href='".$path.$bar['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delete_filex('".$bar['notransaksi']."','".$bar['namafile']."');\" ></td>";
        $form.= "<tr>";

        }
		
		
		#= ambil data noinvoice	
		$path   = "fileupload/keu_tagihan/";
		// $path   = "filegis/";
		$tempnamafile='';
		$strinv = "select keterangan1,tipetransaksi from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
		$resinv = $owlPDO->query($strinv) or die(print " Gagal: " . PDOException::getMessage());
		$resinv->setFetchMode(PDO::FETCH_ASSOC);
		while($barinv=$resinv->fetch()){
			$tipetransaksi=$barinv['tipetransaksi'];
			$arrnoinvoice[$barinv['keterangan1']]=$barinv['keterangan1'];
		}
		
		
		@$carrnoinvoice=count($arrnoinvoice);
		
		if($carrnoinvoice>0 and $tipetransaksi=='K'){	
			$str="select * from ".$dbname.".listfileupload where notransaksi in ('".implode("','",$arrnoinvoice)."')";
			// echo $str;
			$res=fetchdata($str);
			foreach($res as $bar){
				$lsformaticon[$bar['namafile']]=$bar['formaticon'];
				$lskriteriaefil[$bar['namafile']]=$bar['kriteriaefil'];
				$arrnamafile[$bar['namafile']]=$bar['namafile'];
				$lsnoinvoice[$bar['namafile']]=$bar['notransaksi'];
			}
			
			// if(){
				
			foreach($arrnamafile as $lsnamafile){
				@$icon = seticonfile($lsformaticon[$lsnamafile]);
				$no++;
				 $form.= "<tr>";
				$form.="<td align='center'><img src=".$icon." class=resicon></a></td>";
				$form.="<td style='text-align:left'>".getcriterianame($lskriteriaefil[$lsnamafile])."</td>";
				
				  $form.= "<td><a href='".$path.$lsnamafile."' download>".$lsnamafile."</td>";
				  $form.= "<td><a href='".$path.$bar['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a> ".$lsnoinvoice[$lsnamafile]."</td>";
					// $form.= "<td><a href='".$path.$bar['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delete_filex('".$bar['notransaksi']."','".$bar['namafile']."');\" ></td>";
					// $form.= "<tr>";
				
				// $form.="<td style='text-align:center'>".$lsnoinvoice[$lsnamafile]."</td>";
					// <td style='text-align:left'>".$lsnamafile."</td>
					// <td align=center>
						// <a href='".$path.$lsnamafile."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				// $form."	</td>
				 $form.="</tr>";
			}
		}

		
		

        echo $form;
    break;  

	
	case'getdataclmed':
		$data="";
		$data.= "<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
		$data.="<table cellpadding=0 cellspacing=1 width=100% class=sortable>";
        $data.= "<thead><tr>";
		$data.= "<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
		$data.= "<td align=center>".$_SESSION['lang']['unit']."</td>";
		$data.= "<td align=center>".$_SESSION['lang']['namakaryawan']."</td>";
		$data.= "<td align=center>".$_SESSION['lang']['jumlah']."</td>";
		$data.= "</tr></thead>";
		
		$pt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
		
		if($param['notran']!=''){
			$where.=" and notransaksi like '%".$param['notran']."%'";
		}
		
		#data
		$str="select * from ".$dbname.".sdm_pengobatanht where 1=1 and jlhbayar>0 and
				notransaksi not in (select nodok from ".$dbname.".keu_kasbankdt) and
				tanggalbayar != '0000-00-00' and klaimoleh!=1 and kodeorg in 
				(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$param['unit']]."') ".$where." ";
		// echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
			$data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getclmed('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['karyawanid']."','".$bar['jlhbayar']."')>";
			$data.= "<td>".$bar['notransaksi']."</td>";
			$data.= "<td>".$bar['kodeorg']."</td>";
			$data.= "<td>".$nmkar[$bar['karyawanid']]."</td>";
			$data.= "<td align=right>".number_format($bar['jlhbayar'])."</td>";
			$data.= "</tr>";
        }
		$data.= "</table></fieldset>";
		echo $data;
	break;
	
	case'getformkasbank':
		$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
        }
		$form="";
		$form = "<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=notran></td>";

        $form.= "<td>".$_SESSION['lang']['unit']."</td><td>:</td>";
        $form.= "<td><select id=unit style=width:145px>".$optunit."</select></td></tr>";
		
        $form.= "</table>";
        $form.= "<button class=mybutton onclick=findkasbank(0)>Find</button></fieldset>
				 <div id=container2></div>";
        echo $form;
	break;	
	
	case'getdatakasbank':
		$data="";
		$data.= "<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
		$data.="<table cellpadding=0 cellspacing=1 width=100% class=sortable>";
        $data.= "<thead><tr>";
		$data.= "<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
		$data.= "<td align=center>".$_SESSION['lang']['unit']."</td>";
		$data.= "<td align=center>".$_SESSION['lang']['noakun']."</td>";
		$data.= "<td align=center>".$_SESSION['lang']['keterangan']."</td>";
		$data.= "<td align=center>".$_SESSION['lang']['jumlah']."</td>";
		$data.= "</tr></thead>";
		
		if($param['unit']!=''){
			$where.=" and kodeorg='".$param['unit']."'";
		}
		if($param['notran']!=''){
			$where.=" and notransaksi like '%".$param['notran']."%'";
		}
		
		#data
		$str="select notransaksi,kodeorg,jumlah,noakun from ".$dbname.".keu_kasbankdt 
		where tipetransaksi='K' and (noakun like '12101%' or noakun like '11104%') ".$where." ";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
			#cek data tersimpan
			// $strc="select notransaksi,kodeorg,jumlah,noakun from ".$dbname.".keu_kasbankdt where 
					// keterangan2='".$bar['notransaksi']."' and noakun='".$bar['noakun']."' ";
					$strc="select notransaksi,kodeorg,jumlah,noakun,keterangan2 from ".$dbname.".keu_kasbankdt where 
					 noakun='".$bar['noakun']."' ";
			$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
			$resc->setFetchMode(PDO::FETCH_ASSOC);
			$barc=$resc->fetch();
				if($bar['notransaksi']!=$barc['notransaksi']  && $bar['noakun']!=$barc['noakun']){
					$data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getkasbank('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['noakun']."','".$bar['jumlah']."')>";
					$data.= "<td>".$bar['notransaksi']."</td>";
					$data.= "<td>".$bar['kodeorg']."</td>";
					$data.= "<td align=center>".$bar['noakun']."</td>";
					$data.= "<td align=center>".$bar['keterangan2']."</td>";
					$data.= "<td align=right>".number_format($bar['jumlah'])."</td>";
					$data.= "</tr>";
				}
        }
		$data.= "</table></fieldset>";
		echo $data;
	break;
    case'getformangsrn':

        // $optbulan="<option value=''>".$_SESSION['lang']['bulan']."</option>";
        // $opttahun="<option value=''>".$_SESSION['lang']['tahun']."</option>";
        // for($i=1;$i<=12;$i++){
        //     if($i<10){
        //         $i='0'.$i;
        //     }
        //     $optbulan.="<option value='".$i."'>".numToMonth($i,'I','long')."</option>";
        // }

        // $thnskrg=date('Y');
        // $thnskrglima=$thnskrg-5;
        // for($i=$thnskrglima;$i<=$thnskrg;$i++){
        //     $opttahun.="<option value='".$i."'>".$i."</option>";
        // }


        $optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
        $optDt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sOpt="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and char_length(kodeorganisasi)=4  order by namaorganisasi asc";
        $rOpt=fetchdata($sOpt);
        $lstKodeorg='';
        foreach ($rOpt as $key => $value) {
            $optUnit.="<option value='".$value['kodeorganisasi']."'>".$value['kodeorganisasi']."-".$value['namaorganisasi']."</option>";
            if($_SESSION['empl']['lokasitugas']!=$value['kodeorganisasi']){
                if(!empty($lstKodeorg)){
                    $lstKodeorg.=",";    
                }
                $lstKodeorg.="'".$value['kodeorganisasi']."'";
            }
        }
        
        $data="";
        $data.= "<fieldset style=width:630px><legend>".$_SESSION['lang']['form']."</legend>";
        $data.="<table cellspacing=1 cellpadding=1 border=0>";
        $data.="<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select id=unitSrc style=width:150px onchange=findangsuran()>".$optUnit."</select></td></tr>";
        $data.="<tr><td colspan=2>&nbsp;</td><td><button class=mybutton onclick=findangsuran()>Find</button></td></tr>";
        $data.="</table></fieldset>";
        $data.="<fieldset style=width:630px;float:left>";
        $data.="<legend>".$_SESSION['lang']['result']."</legend>";
        $data.="<div id=container2 style=width:630px;height:355px;overflow:auto>";
        $data.="</div></fieldset><input type=hidden id=tanggal2 value='".tanggalsystemn($_POST['tanggal'])."'>";
        echo $data;
    break;
    case'getAngsuran':
        //noakun piutang karyawan
        $kdptg='AKNPIUTANG';
        $aknptg=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',"kodeparameter='".$kdptg."'");
        #ambil akun-akun piutang dan R/K
        $sAkn="select noakun from ".$dbname.".keu_5akun where noakun='".$aknptg[$kdptg]."'
                union
               select a.akunpiutang as noakun from ".$dbname.".keu_5caco a left join ".$dbname.".keu_5akun b 
               on a.akunpiutang=b.noakun where a.jenis='intra' and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."')";
        $rAkn=fetchdata($sAkn);
        foreach($rAkn as $row=>$dtAkun){
            if($row==0){
                $lstAkun=$dtAkun['noakun'];
            }else{
                $lstAkun.=",".$dtAkun['noakun'];
            }
        }
        $sJAngsrn=makeOption($dbname,'sdm_ho_component','id,name');
        $tab.= makeElement('btnAdd2Detail','btn',$_SESSION['lang']['addtodetail'],array('onclick'=>'add2detilangsrn()')).'<br>';
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr class='rowheader'><td>";
        $tab.= makeElement('btnAllInvoice','checkbox',$_SESSION['lang']['all'],array('onclick'=>'checkAll()'));
        $tab.="</td>";
        $tab.="<td align=center>".$_SESSION['lang']['nik']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['namakaryawan']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['unit']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['angsuran']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['bayarke']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['jumlahpotongan']."</td>";
        $tab.="</tr></thead><tbody id='invTbody'>";
        $whr="b.lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."')";
        if($param['unit']!=''){
            $whr="b.lokasitugas='".$param['unit']."'";
        }
        $sData="select a.*,b.nik,b.namakaryawan,b.lokasitugas from ".$dbname.".sdm_angsuran a 
                left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
                where ".$whr." and tipekaryawan=0 and active=1";
        //echo $sData;
        $tgltrans=explode("-",$param['tanggal']);
        $rData=fetchdata($sData);
        foreach($rData as $row=>$lstDat){
            #cek jika pernah terinput maka gak muncul

            $sCkKas="select * from ".$dbname.".keu_kasbankdt where noakun in (".$lstAkun.") and nik='".$lstDat['karyawanid']."'
                     and tanggal like '".$tgltrans[0]."-".$tgltrans[1]."%'";
            $rCkKas=fetchdata($sCkKas);
            if(count($rCkKas)==0){
                $blnawal=explode("-",$lstDat['start']);
                $blnakhir=mktime(0,0,0,intval($blnawal[1]),0,$blnawal[0]);
                $blnberjalan=mktime(0,0,0,intval($tgltrans[1]),0,$tgltrans[0]);//$tgltrans[1].$tgltrans[0];
                $byrke=round((($blnberjalan-$blnakhir) / 60 / 60 / 24 / 30)+1);
                
                if($byrke<=$lstDat['jlhbln']){
                    $tab.="<tr class='rowcontent'>";
                    $textket="pembayaran ".romawi($byrke).", ".$sJAngsrn[$lstDat['jenis']].", a/n  [".$lstDat['nik']."] ".$lstDat['namakaryawan'];
                    $tab.="<td align=center>".makeElement('inv_'.$row,'checkbox','',array('class'=>'inv-chk','karyDt'=>$lstDat['karyawanid'],'bulanan'=>$lstDat['bulanan'],'jenis'=>$lstDat['jenis'],'unit'=>$lstDat['lokasitugas']))."</td>";       
                    $tab.="<td>".$lstDat['nik']."</td>";
                    $tab.="<td>".$lstDat['namakaryawan']."</td>";
                    $tab.="<td>".$lstDat['lokasitugas']."</td>";
                    $tab.="<td>".$sJAngsrn[$lstDat['jenis']]."</td>";
                    $tab.="<td align=right>".$byrke."</td>";
                    $tab.="<td align=right>".number_format($lstDat['bulanan'],2)."<input type=hidden id=ketdet_".$row." value='".$textket."'></td>";
                    $tab.="</tr>";
                }
            }
            
        }
        $tab.="</tbody></table>";
        echo $tab;//invTbody
    break;
	case 'addFromAngsuran':
                $param = $_POST;
                // Default Segment
                $defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
                //noakun piutang karyawan
                $kdptg='AKNPIUTANG';
                $aknptg=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',"kodeparameter='".$kdptg."'");
                $data = array();
                $rowdata=0;
                foreach($param['karyDt'] as $rowdt=>$lstKary) {
                        $param['noakun2']=$aknptg[$kdptg];
                        #noakun data
                        if($param['kodeorg']!=$param['unit'][$rowdt]){
                            $sAkn="select a.akunpiutang as noakun,b.namaakun from ".$dbname.".keu_5caco a left join ".$dbname.".keu_5akun b 
                                    on a.akunpiutang=b.noakun where a.jenis='intra' and a.kodeorg='".$param['unit'][$rowdt]."'";
                            $rAkn=fetchdata($sAkn);
                            $param['noakun2']=$rAkn[0]['noakun'];

                            if ($rAkn[0]['noakun']=='') {
                                exit("Warning : Account intraco or interco not available for ".$param['unit'][$rowdt].". Please setting on menu Finance > setup > COA for Intra/Interco.");
                            } 
                        }
                        
                        // Piutang Penjualan
                        $data[] = array(
                                'notransaksi' => $param['notransaksi'],
                                'noakun' => $param['noakun2'],
                                'tipetransaksi' => $param['tipetransaksi'],
                                'tanggal' => tanggalsystem($param['tanggal']),
                                'jumlah' => $param['bulanan'][$rowdt],
                                'noakun2a' => $param['noakun'],
                                'kode' => $param['kode'],
                                'keterangan1' => '',
                                'keterangan2' => $param['ketdet'][$rowdt],
                                'matauang' => $param['matauang'],
                                'kurs' => $param['kurs'],
                                'kurs2' => 1,
                                'noaruskas' => '0',
                                'kodeorg' => $param['kodeorg'],
                                'kodekegiatan' => '',
                                'kodeasset' => '',
                                'kodebarang' => '',
                                'nik' => $lstKary,
                                'kodecustomer' => '',
                                'kodesupplier' => '',
                                'kodevhc' => '',
                                'orgalokasi' => '',
                                'nodok' =>'' ,
                                'hutangunit1' => '',
                                'bulan' => '',
                                'tahun' => '',
                                'lain' => '',
                                'keterangan3' => '',
								'lainnya' => '',
                                'kodesegment' => $defSegment
                        );
                    $rowdata++;
                }
                //print_r($data);exit('error');
                $qIns = insertQuery($dbname,'keu_kasbankdt',$data);
                try{$owlPDO->exec($qIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>".$qIns; die(); }
    break;
	case'addclmed':
		$param = $_POST;
		if($tpkar[$param['karid']]>='1' && $tpkar[$param['karid']]<='6'){
			$akundt='7110203';
		}else{
			$akundt='7110104';
		}
		$str="insert into ".$dbname.".keu_kasbankdt (`notransaksi`, `noakun`, `tipetransaksi`, `tanggal`,
				`jumlah`, `noakun2a`, `kode`,
				`keterangan2`, `matauang`, `kurs`,`kurs2`,
				`kodeorg`,`nodok`,nik,bulan,tahun,
				kodekegiatan,kodeasset,kodebarang,kodecustomer,kodesupplier,kodevhc,orgalokasi) 
		values ('".$param['notransaksi']."','".$akundt."','".$param['tipetransaksi']."','".tanggalsystemn($param['tanggal'])."',
				'".$param['jumlah']."','".$param['noakun']."','".$param['kode']."',
				'Claim Pengobatan untuk nomor transaksi ".$param['notran']." a/n ".$nmkar[$param['karid']]."','".$param['matauang']."','".$param['kurs']."','1',
				'".$param['kodeorg']."','".$param['notran']."','".$param['karid']."','".$param['bulan']."','".$param['tahun']."',
				'','','','','','','')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e)  {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	break;	
	
	case'addkasbank':
		$param = $_POST;
		//print_r($param);exit("Error:A");
		$str="insert into ".$dbname.".keu_kasbankdt (`notransaksi`, `noakun`, `tipetransaksi`, `tanggal`,
				`jumlah`, `noakun2a`, `kode`,
				`keterangan2`, `matauang`, `kurs`,`kurs2`,
				`kodeorg`,`nodok`) 
		values ('".$param['notransaksi']."','".$param['noakundt']."','".$param['tipetransaksi']."','".tanggalsystemn($param['tanggal'])."',
				'".$param['jumlah']."','".$param['noakun']."','".$param['kode']."',
				'".$param['notran']."','".$param['matauang']."','".$param['kurs']."','1',
				'".$param['kodeorg']."','".$param['notran']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e)  {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	break;	
	

    case'getForminvoice':
		
        // $optbulan="<option value=''>".$_SESSION['lang']['bulan']."</option>";
        // $opttahun="<option value=''>".$_SESSION['lang']['tahun']."</option>"; 
		$optbulan=$opttahun="";
		$blnskrg=date('m');
        for($i=1;$i<=12;$i++){
            if($i<10){
                $i='0'.$i;
            }
			
			if($i==$blnskrg){
				$optbulan.="<option value='".$i."' selected>".numToMonth($i,'I','long')."</option>";
			} else {
				$optbulan.="<option value='".$i."'>".numToMonth($i,'I','long')."</option>";
			}
			
            
        }

        $thnskrg=date('Y');
        $thnskrglima=$thnskrg-5;
        for($i=$thnskrglima;$i<=$thnskrg;$i++){
			if($i==$thnskrg){
				$opttahun.="<option value='".$i."' selected>".$i."</option>";
			}else{
				$opttahun.="<option value='".$i."'>".$i."</option>";
			}
            
        }
		
		
		  $optSupplierCr=$optJeniscr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select distinct(tipeinvoice) as tipeinvoice from ".$dbname.".keu_tagihanht where posting=1";
		$res=fetchData($str);
		foreach($res as $bar) {
			$optJeniscr.="<option value='".$bar['tipeinvoice']."'>".$bar['tipeinvoice']." - ".$arrTipe[$bar['tipeinvoice']]."</option>";
		}
		

      
        // $sSuplier=$owlPDO->query("select distinct supplierid,namasupplier,substr(kodekelompok,1,1) as status from ".$dbname.".log_5supplier order by namasupplier asc");
        $sSuplier=$owlPDO->query("select a.supplierid, b.namasupplier, a.tipe as status from ".$dbname.".log_5supkelompok a
			left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid order by b.namasupplier asc,a.tipe asc");
        $sSuplier->setFetchMode(PDO::FETCH_ASSOC);
        while($rSupplier=$sSuplier->fetch())
        {
            $optSupplierCr.="<option value='".$rSupplier['supplierid']."'>".$rSupplier['namasupplier']." [".$rSupplier['status']."]</option>";
        }
        $form = "<fieldset style=width:830px><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr><td>".$_SESSION['lang']['noinvoice']."</td><td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=no_brg value=".date('Y')."></td>";

		/*
        $form.= "<td>".$_SESSION['lang']['namasupplier']."</td><td>:</td>";
        $form.= "<td><select id=supplierIdcr style=width:145px>".$optSupplierCr."</select></td>";
		*/
		
		 $form.= "<td>".$_SESSION['lang']['namasupplier']."</td><td>:</td>";
        $form.= "<td><select style=\"width:150px;\" id=supplierIdcr>" . $optSupplierCr . "</select>
				<img id='supplierIdcr' onclick=z.elSearch('supplierIdcr',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
				</td>";
				
		#= bulan
        $form.= "<td>".$_SESSION['lang']['bulan']."</td><td>:</td>";
        $form.= "<td><select id=bulanap style='width:150px;'>".$optbulan."</select></td></tr>";

        $form.= "<tr><td>".$_SESSION['lang']['nopo']."</td><td>:</td>";
        $form.= "<td>".makeElement('sNopo','text','',array('placeholder'=>$_SESSION['lang']['all']))."</td>";

        $form.= "<td>No. Invoice Supplier</td><td>:</td>";
        $form.= "<td>".makeElement('sInvSupp','text','',array('placeholder'=>$_SESSION['lang']['all']))."</td>";

		#= tahun
        $form.= "<td>".$_SESSION['lang']['tahun']."</td><td>:</td>";
        $form.= "<td><select id=tahunap style='width:150px;' >".$opttahun."</select></td></tr>";

        $form.= "<tr><td>".$_SESSION['lang']['nilaiinvoice']."</td><td>:</td>";
        $form.= "<td>".makeElement('sNilai','textnum','',array('placeholder'=>$_SESSION['lang']['all']))."</td>";

        $form.= "<td>".$_SESSION['lang']['tahun'].'-'.$_SESSION['lang']['bulan']."</td><td>:</td>";
        $form.= "<td>".makeElement('sYm','text','',array('placeholder'=>$_SESSION['lang']['all']))."</td>";

		$form.= "<td>".$_SESSION['lang']['tipeinvoice']."</td><td>:</td>";
        $form.= "<td><select id=tipeinvoiceap style='width:150px;' >".$optJeniscr."</select></td></tr>";



        $form.= "</table>";
        $form.= "<button class=mybutton onclick=findNoinvoice(0)>Find</button></fieldset>
				 <div id=container2><fieldset><legend>".$_SESSION['lang']['result']."</legend></fieldset></div>";
        echo $form;
        break;

        case'getFormInvoiceAR':

        $optbulan=$opttahun="";
		$blnskrg=date('m');
        for($i=1;$i<=12;$i++){
            if($i<10){
                $i='0'.$i;
            }
			
			if($i==$blnskrg){
				$optbulan.="<option value='".$i."' selected>".numToMonth($i,'I','long')."</option>";
			} else {
				$optbulan.="<option value='".$i."'>".numToMonth($i,'I','long')."</option>";
			}
        }

        $thnskrg=date('Y');
        $thnskrglima=$thnskrg-5;
        for($i=$thnskrglima;$i<=$thnskrg;$i++){
			if($i==$thnskrg){
				$opttahun.="<option value='".$i."' selected>".$i."</option>";
			}else{
				$opttahun.="<option value='".$i."'>".$i."</option>";
			}
        }


        $optSupplierCr="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sSuplier=$owlPDO->query("select distinct kodecustomer,namacustomer from ".$dbname.".pmn_4customer  order by namacustomer asc");
        $sSuplier->setFetchMode(PDO::FETCH_ASSOC);
        while($rSupplier=$sSuplier->fetch())
        {
            $optSupplierCr.="<option value='".$rSupplier['kodecustomer']."'>".$rSupplier['namacustomer']."</option>";
        }
        $form = "<fieldset style=width:830px><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr><td>".$_SESSION['lang']['noinvoice']." AR</td>";
        $form.= "<td><input type=text class=myinputtext id=no_brg value=".date('Y')."></td>";

        $form.= "<td>".$_SESSION['lang']['namacust']."</td>";
        $form.= "<td><select id=supplierIdcr style=width:145px>".$optSupplierCr."</select></td>";

        $form.= "<td>".$_SESSION['lang']['bulan']."</td>";
        $form.= "<td><select id=bulanar style='width:100px;'>".$optbulan."</select></td></tr>";

        $form.= "<tr><td>".$_SESSION['lang']['NoKontrak']."</td>";
        $form.= "<td>".makeElement('sNopo','text','',array('placeholder'=>$_SESSION['lang']['all']))."</td>";

        $form.= "<td>".$_SESSION['lang']['nilaiinvoice']."</td>";
        $form.= "<td>".makeElement('sNilai','textnum','',array('placeholder'=>$_SESSION['lang']['all']))."</td>";

        $form.= "<td>".$_SESSION['lang']['tahun']."</td>";
        $form.= "<td><select id=tahunar style='width:100px;' >".$opttahun."</select></td></tr>";

        $form.= "<tr><td>".$_SESSION['lang']['tahun'].'-'.$_SESSION['lang']['bulan']."</td>";
        $form.= "<td>".makeElement('sYm','text','',array('placeholder'=>$_SESSION['lang']['all']))."</td></tr>";

        $form.= "</table>";
        $form.= "<button class=mybutton onclick=findNoinvoice(1)>Find</button></fieldset><div id=container2><fieldset><legend>".$_SESSION['lang']['result']."</legend></fieldset></div>";
        echo $form;
        break;

        case'getFormMemo':

                $defPeriod = $_SESSION['org']['period']['tahun'].'-'.
                        str_pad($_SESSION['org']['period']['bulan'],2,'0',STR_PAD_LEFT);

        $form = "<fieldset style=width:95%><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table border=0>";
        $form.= "<tr><td>".$_SESSION['lang']['nojurnal']."</td><td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=sNojurnal value=''></td>";
        $form.= "<td>".$_SESSION['lang']['tahun'].'-'.$_SESSION['lang']['bulan']."</td><td>:</td>";
        $form.= "<td>".makeElement('sYm','textnumw-',$defPeriod,array('placeholder'=>'YYYY-mm','style'=>'width:50px'))."</td></tr>";

        $form.= "</table>";
                $form.= "<button class=mybutton onclick=findMemo()>Find</button>";
                $form.= "</fieldset><div id=container2><fieldset><legend>".$_SESSION['lang']['result']."</legend></fieldset></div>";
        echo $form;
        break;

    case'getInvoice':
        $optNmsupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
		//$optNmramp=makeOption($dbname, 'log_5klsupplier','kode,kelompok');
      
        $dat="<fieldset style=width:830px><legend>".$_SESSION['lang']['result']."</legend>";
        $dat.="<div style=overflow:auto;width:826px;height:350px;>";
        $dat.= makeElement('btnAdd2Detail','btn',$_SESSION['lang']['addtodetail'],array('onclick'=>'add2detail()')).'<br>';
        $dat.="<table width=100% cellpadding=1 cellspacing=1 border=0 class='sortable'><thead>";
        $dat.="<tr class='rowheader'><td>";
        $dat.= makeElement('btnAllInvoice','checkbox',$_SESSION['lang']['all'],array('onclick'=>'checkAll()'));
        $dat.="</td>";
        $dat.="<td align=center>".$_SESSION['lang']['noinvoice']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['nopo']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['tipeinvoice']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['nilaidpp']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['nilaippn']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['pph']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['notadebet']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['terbayar']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['sisa']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['noakun']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namaakun']."</td>";
        // $dat.="<td align=center>".$_SESSION['lang']['noaruskas']."</td>";
        // $dat.="<td align=center>".$_SESSION['lang']['keterangan']."</td>";
        $dat.="</tr></thead><tbody id='invTbody'>";
		
		
		
		
        $str=$owlPDO->query("select distinct noinvoice from ".$dbname.".aging_sch_vw where (((dibayar<nilaipo)or(dibayar<nilaikontrak)or(dibayar<(nilaiinvoice+nilaippn)))or(dibayar is null or dibayar=0)) and noinvoice like '".$param['txtfind']."%'");
        $str->setFetchMode(PDO::FETCH_ASSOC);
        while($rstr=$str->fetch()) {
            $belumlunas[$rstr['noinvoice']]=$rstr['noinvoice'];
        }
		

        if(empty($param['idSupplier'])) {
                $kdsup=" ";
        } else {
                $kdsup=" and kodesupplier='".$param['idSupplier']."'  ";
        }

        $strinduk="select induk from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
        $ninduk=$owlPDO->query($strinduk);
        $ninduk->setFetchMode(PDO::FETCH_ASSOC);
        $qinduk=$ninduk->fetch();
        $induk=$qinduk['induk'];
        #ada uang muka
		/*
        $adaUangMuka=array();
        $sPo="select distinct a.kodesupplier,noinvoice,a.nopo,tipeinvoice,nilaiinvoice,nilaidpp,nilaippn,a.noakun,a.keterangan,a.posting,b.namakaryawan,d.tipesupplier as tipe
            from ".$dbname.".keu_tagihanht a
                        left join ".$dbname.".datakaryawan b on a.postingby=b.karyawanid ".
                        //left join ".$dbname.".log_poht c on a.nopo=c.nopo
                        "left join ".$dbname.".keu_5jenistagihan d on a.tipeinvoice=d.kode
            where noinvoice like '".$param['txtfind']."%' and a.kodeorg='".$induk."' and a.tipeinvoice in ('p','k')  ".$kdsup;
         $rPo=fetchData($sPo);
         foreach ($rPo as $key => $val) {
                $sDt="select * from ".$dbname.".keu_tagihandt where noinvoice='".$val['noinvoice']."' and left(noakun,5)='11802'";
                $rDt=fetchData($sDt);
                if(count($rDt)==1){
                    $adaUangMuka[$val['noinvoice']]=$val['noinvoice'];
                }
         }   
		 */


		#= hanya orang ro dan ho yang dimunculkan data FFB Afiliasi /  pembelian tbs afiliasi
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL') {		
			      $sPo="select distinct a.kodesupplier,noinvoice,a.nilaidpp,a.nopo,tipeinvoice,nilaiinvoice,nilaippn,a.noakun,
				a.keterangan,a.posting,b.namakaryawan,d.tipesupplier as tipe,d.jurnal as jurnalinvoice
            from ".$dbname.".keu_tagihanht a
                        left join ".$dbname.".datakaryawan b on a.postingby=b.karyawanid ".
                        //left join ".$dbname.".log_poht c on a.nopo=c.nopo
                        "left join ".$dbname.".keu_5jenistagihan d on a.tipeinvoice=d.kode
            where noinvoice like '".$param['txtfind']."%' and a.kodeorg='".$induk."'  ".$kdsup;
		}else{
			
			      $sPo="select distinct a.kodesupplier,noinvoice,a.nilaidpp,a.nopo,tipeinvoice,nilaiinvoice,nilaippn,a.noakun,
				a.keterangan,a.posting,b.namakaryawan,d.tipesupplier as tipe,d.jurnal as jurnalinvoice
            from ".$dbname.".keu_tagihanht a
                        left join ".$dbname.".datakaryawan b on a.postingby=b.karyawanid ".
                        //left join ".$dbname.".log_poht c on a.nopo=c.nopo
                        "left join ".$dbname.".keu_5jenistagihan d on a.tipeinvoice=d.kode
            where noinvoice like '".$param['txtfind']."%' and a.kodeorg='".$induk."' and a.tipeinvoice!='ffba'  ".$kdsup;
		}

		
  
            // and kodeorg='".$_SESSION['org']['kodeorganisasi']."'
        if(!empty($param['sNopo'])) {
            $sPo.= " and a.nopo like '%".$param['sNopo']."%'";
        }
        if(!empty($param['sInvSupp'])) {
            $sPo.= " and a.noinvoicesupplier like '%".$param['sInvSupp']."%'";
        }
        if(!empty($param['sNilai'])) {
            $sPo.= " and a.nilaiinvoice=".$param['sNilai'];
        }
        if(!empty($param['sYm'])) {
            $sPo.= " and a.tanggal like '%".$param['sYm']."%'";
        }
		if(!empty($param['tipeinvoice'])) {
            $sPo.= " and a.tipeinvoice='".$param['tipeinvoice']."'";
        }
        $sPo.=" order by noinvoice asc,a.posting desc,a.tanggal asc";
		
		// echo $sPo;exit();
	
		
        $qPo=$owlPDO->query($sPo);
        $qPo->setFetchMode(PDO::FETCH_ASSOC);
        $key=$no=0;
        while($rPo=$qPo->fetch()) {
			
			##cek apakah tagihan tersebut ada di transaksi nota debet atau tidak
			$strnd="select sum(nilai_detail) as nilainota from ".$dbname.".keu_notadebet_vw 
					where noinvoice_referensi='".$rPo['noinvoice']."'";
			$resnd=$owlPDO->query($strnd) or die(print " Gagal: ".PDOException::getMessage());
			$resnd->setFetchMode(PDO::FETCH_ASSOC);
			$barnd=$resnd->fetch();
			@$nilainota=floatval($barnd['nilainota']);
			
			
            #ambil noakun 
			/*
			$whr="tipe='".$rPo['tipe']."'";
            $optAkunGet=makeOption($dbname,'log_5klsupplier','tipe,noakun',$whr);
			if($rPo['noakun']==''){
				$rPo['noakun']=$optAkunGet[$rPo['tipe']];
			}else{
				$rPo['noakun']=$rPo['noakun'];
			}
			*/
			#= update 27/04/21, nomor akun sudah sesuai dari AP (kolom noakun)
			
            // if(isset($belumlunas[$rPo['noinvoice']]) and $rPo['noinvoice']==$belumlunas[$rPo['noinvoice']]){
                $sJmlh="select distinct sum(jumlah) as jmlhKas from ".$dbname.".keu_kasbankdt where keterangan1='".$rPo['noinvoice']."'";
                $qJmlh=$owlPDO->query($sJmlh);
                $qJmlh->setFetchMode(PDO::FETCH_ASSOC);
                $rJmlh=$qJmlh->fetch();

                $jmlhumuka=0;
				/*
                if(count($adaUangMuka[$rPo['noinvoice']])!=0){
                    #kesini klo detailnya ada noakun uang mukanya
                    if ($rPo['tipeinvoice']=='p') {
                    $sJmlhumuka="select distinct sum(jumlah) as jmlhumuka from ".$dbname.".keu_kasbankdt a left join ".$dbname.".keu_tagihanht b on a.keterangan1=b.noinvoice where nodok='".$rPo['nopo']."' and keterangan1!='".$rPo['noinvoice']."' and tipeinvoice='um'";
                    // $sJmlhumuka="select distinct sum(jumlah) as jmlhumuka from ".$dbname.".keu_kasbankdt where nodok='".$rPo['nopo']."' and keterangan1!='".$rPo['noinvoice']."'";
                    $qJmlhumuka=$owlPDO->query($sJmlhumuka);
                    $qJmlhumuka->setFetchMode(PDO::FETCH_ASSOC);
                    $rJmlhumuka=$qJmlhumuka->fetch();
                    $jmlhumuka=$rJmlhumuka['jmlhumuka'];
                    }
                    if ($rPo['tipeinvoice']=='k') {
                        $sJmlhumuka="select distinct sum(jumlah) as jmlhumuka from ".$dbname.".keu_kasbankdt a left join ".$dbname.".keu_tagihanht b on a.keterangan1=b.noinvoice where nodok='".$rPo['nopo']."' and keterangan1!='".$rPo['noinvoice']."' and tipeinvoice='um' and b.noinvoice not in (select noinvoiceum from ".$dbname.".keu_tagihanht where noinvoiceum!='')";
                        // $sJmlhumuka="select distinct sum(jumlah) as jmlhumuka from ".$dbname.".keu_kasbankdt where nodok='".$rPo['nopo']."' and keterangan1!='".$rPo['noinvoice']."'";
                        $qJmlhumuka=$owlPDO->query($sJmlhumuka);
                        $qJmlhumuka->setFetchMode(PDO::FETCH_ASSOC);
                        $rJmlhumuka=$qJmlhumuka->fetch();
                        $jmlhumuka=$rJmlhumuka['jmlhumuka'];
                    }
                }
                */

                $sCek="select distinct sum(nilaiinvoice) as jmlhinvoice from ".$dbname.".keu_tagihanht where  noinvoice='".$rPo['noinvoice']."'";
                $qCek=$owlPDO->query($sCek);
                $qCek->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=$qCek->fetch();

				/*
                $iDt="select sum(nilai) as nilai from ".$dbname.".keu_tagihandt where noakun in "
                        . "(select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='TX' and kodeparameter='PPNINV')"
                        . " and noinvoice='".$rPo['noinvoice']."'  ";
                $nDt=  $owlPDO->query($iDt);
                $nDt->setFetchMode(PDO::FETCH_ASSOC);
                $dDt=  $nDt->fetch();
				
                $ipph="select nilai from ".$dbname.".keu_tagihandt where left(noakun,3)='213' and noinvoice='".$rPo['noinvoice']."'";
                $npph=$owlPDO->query($ipph);
                $npph->setFetchMode(PDO::FETCH_ASSOC);
                $dpph=$npph->fetch();

                $sisa=$rPo['nilaiinvoice']+$dpph['nilai']-$rJmlh['jmlhKas']-$jmlhumuka;
                if ($rPo['tipeinvoice']=='um') {
                    $sisa=$rPo['nilaiinvoice']+$dpph['nilai']+$dDt['nilai']-$rJmlh['jmlhKas']-$jmlhumuka;
                }
                if ($jmlhumuka!=0) {
                    if ($rPo['tipeinvoice']=='p' || $rPo['tipeinvoice']=='k') {
                        $sisa=$rPo['nilaiinvoice']+$dpph['nilai']+$dDt['nilai']-$rJmlh['jmlhKas']-$jmlhumuka;
                    }
                }

                if ($rPo['tipeinvoice']=='ffb') {
                    $sisa=$rPo['nilaiinvoice']+$dpph['nilai']+$dDt['nilai']-$rJmlh['jmlhKas'];
                }
				*/

				/***************************
				bentuk sisa baru
				****************************/
				
				$nilppn=$nilpph=$nilaipajak=0;
				$strpajak="select sum(nilai) as nilai,noakun from ".$dbname.".keu_tagihandt where noinvoice='".$rPo['noinvoice']."' 
					and (noakun like '117%' or noakun like '213%') group by noakun";
                $respajak=  $owlPDO->query($strpajak);
                $respajak->setFetchMode(PDO::FETCH_ASSOC);
                while($barpajak=  $respajak->fetch()){
					if(substr($barpajak['noakun'],0,3)=='117'){
						@$nilppn+=$barpajak['nilai'];
					}
					if(substr($barpajak['noakun'],0,3)=='213'){
						@$nilpph+=$barpajak['nilai'];
					}
					
					@$nilaipajak+=$barpajak['nilai'];
					
				}
					
					
				if($rPo['tipeinvoice']=='p21' || $rPo['tipeinvoice']=='p22' ||  $rPo['tipeinvoice']=='p23' ||  $rPo['tipeinvoice']=='p25'){
					$sisa=$rPo['nilaiinvoice'];
					$nilpph=0;
				} else {
					#= buat nilai invoice  agar ditampilan add ap sesuai
					#= pemisah adalah nvm / vm 
					#= jika nvm nilai dpp kurangi nilai pajaknya
					// if($rPo['jurnalinvoice']=='1'){
						// $rPo['nilaidpp']=$rPo['nilaidpp']-$nilaipajak;
					// }
					
					$sisa=$rPo['nilaiinvoice']-$rJmlh['jmlhKas']-$jmlhumuka-$nilainota;
				}				
				// echo $rPo['nilaiinvoice']._.$rJmlh['jmlhKas']._.$jmlhumuka._.$nilainota;
				
			
				
				
				// echo $rPo['nilaiinvoice']._.$nilaipajak;
				
                $no+=1;
				
				
				//if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL') {	
				
				
				
                if(empty($rPo['kodesupplier'])) {
                        $dat.="<tr class='rowcontent' title='PO still not valid, No Supplier'>
						<td></td>"; 
                        $dat.="<td style='background-color:red;'>".$rPo['noinvoice']."</td>";
                } else if($rPo['posting']==0) {
                    $dat.="<tr class='rowcontent' title='Invoice not posted yet'>
							<td></td>";
                    $dat.="<td style='background-color:red;'>".$rPo['noinvoice']."</td>"; 
                } else {
                    if($sisa<=0) {
                        continue;
                    }
                    $dat.="<tr class='rowcontent'>";
                    $dat.="<td align=center>".makeElement('inv_'.$key,'checkbox','',array('class'=>'inv-chk','invNo'=>$rPo['noinvoice'],'sisa'=>$sisa,'noakundet'=>$rPo['noakun']))."</td>";
                    $dat.="<td>".$rPo['noinvoice']."</td>";             
                }
				
              
                if($sisa>0) {
                    $dat.="<td>".$rPo['nopo']."</td>";
                    $dat.="<td>".$optNmsupp[$rPo['kodesupplier']]."</td>";
                    $dat.="<td>".$rPo['tipeinvoice']."<br>".$arrTipe[$rPo['tipeinvoice']]."</td>";
                    $dat.="<td align=right>".number_format($rPo['nilaidpp'],2)."</td>";
                    $dat.="<td align=right>".number_format($nilppn,2)."</td>";
                    $dat.="<td align=right>".number_format($nilpph,2)."</td>";
					
                    $dat.="<td align=right>".number_format($nilainota,2)."</td>";
                    $dat.="<td align=right>".number_format($rJmlh['jmlhKas']+$jmlhumuka,2)."</td>";
                    $dat.="<td align=right>".number_format($sisa,2)."</td>";
                    $dat.="<td>".$rPo['noakun']."</td>";
                    $dat.="<td>".$nmakun[$rPo['noakun']]."</td>";
					
					$optketerangan=$optaruskas='';
					
                    $key++;
                }
				$dat.="</tr>";
            // }
        }// while
                setIt($rJmlh['jmlhKas'],0);
                setIt($rCek['jmlhinvoice'],0);
        $dat.="</tbody></table></div>#Status S atau K, refer To S=Supplier,K=Contractor</fieldset>";
        // echo $dat."__".$rJmlh['jmlhKas']."_____".$rCek['jmlhinvoice'];
                echo $dat;
        break;

        case'getInvoiceAR':
            $optNmsupp = makeOption($dbname, 'pmn_4customer','kodecustomer,namacustomer');

            $dat="<fieldset style=width:830px><legend>".$_SESSION['lang']['result']."</legend>";
            $dat.="<div style=overflow:auto;width:826px;height:350px;>";
            $dat.= makeElement('btnAdd2Detail','btn',$_SESSION['lang']['addtodetail'],array('onclick'=>'add2detailAR()')).'<br>';
            $dat.="<table width=100% cellpadding=1 cellspacing=1 border=0 class='sortable'><thead>";
            $dat.="<tr class='rowheader'><td>";
            $dat.= makeElement('btnAllInvoice','checkbox',$_SESSION['lang']['all'],array('onclick'=>'checkAll()'));
            $dat.="</td>";
            $dat.="<td align=center>".$_SESSION['lang']['noinvoice']." AR</td>";
            $dat.="<td align=center>".$_SESSION['lang']['NoKontrak']."</td>";
            $dat.="<td align=center>".$_SESSION['lang']['namacust']."</td>";
    		$dat.="<td align=center>".$_SESSION['lang']['komoditi']."</td>";
            $dat.="<td align=center>".$_SESSION['lang']['nilaiinvoice']."</td>";
            $dat.="<td align=center>".$_SESSION['lang']['nilaippn']."</td>";
			$dat.="<td align=center>".$_SESSION['lang']['klaim']."</td>";
            $dat.="<td align=center>".$_SESSION['lang']['nilaippn']."</td>";
            $dat.="<td align=center>".$_SESSION['lang']['terbayar']."</td>";
            $dat.="<td align=center>".$_SESSION['lang']['sisa']."</td>";
            $dat.="</tr></thead><tbody id='invTbody'>";

            if(empty($param['idSupplier'])) {
                $kdsup=" ";
            } else {
                $kdsup=" and kodecustomer='".$param['idSupplier']."'  ";
            }

            $strinduk="select induk from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
            $ninduk=$owlPDO->query($strinduk);
            $ninduk->setFetchMode(PDO::FETCH_ASSOC);
            $qinduk=$ninduk->fetch();
            $induk=$qinduk['induk'];
    		

            $sPo="select distinct kodecustomer,noinvoice,nilaiinvoice,nilaippn,nokontrak,b.namabarang,
			rupiah1,rupiah2,rupiah3,rupiah4,rupiah5,rupiah6,rupiah7,rupiah8
			from ".$dbname.".keu_penagihanht a 
                  left join ".$dbname.".log_5masterbarang b on a.kodebarang = b.kodebarang
                  where noinvoice like '%".$param['txtfind']."%' and posting=1 and a.matauang='".$param['matauang']."' 
                  and kodept='".$induk."' ".$kdsup;
            // echo $sPo;
            if(!empty($param['sNilai'])) {
                $sPo.= " and a.nilaiinvoice=".$param['sNilai'];
            }
            if(!empty($param['sYm'])) {
                $sPo.= " and a.tanggal like '%".$param['sYm']."%'";
            }
            $sPo.=" order by a.tanggal asc";
            $qPo=$owlPDO->query($sPo);
            $qPo->setFetchMode(PDO::FETCH_ASSOC);
            $key=$no=0;
            while($rPo=$qPo->fetch()) {
				$totalpinalti=$ppntotalpinalti=0;
				
				$rupiahpinalti1=$rPo['rupiah1']*-1;
				$rupiahpinalti2=$rPo['rupiah2']*-1;
				$rupiahpinalti3=$rPo['rupiah3']*-1;
				$rupiahpinalti4=$rPo['rupiah4']*-1;
				$rupiahpinalti5=$rPo['rupiah5']*-1;
				$rupiahpinalti6=$rPo['rupiah6']*-1;
				$rupiahpinalti7=$rPo['rupiah7']*-1;
				$rupiahpinalti8=$rPo['rupiah8'];
				
				$totalpinalti=$rupiahpinalti1+$rupiahpinalti2+$rupiahpinalti3+$rupiahpinalti4+$rupiahpinalti5+$rupiahpinalti6+$rupiahpinalti7+$rupiahpinalti8;
				$ppntotalpinalti=0.1*$totalpinalti;
				
				
				
                // Cek Track Pelunasan
                $sBayar = "select distinct sum(jumlah) as jmlhKas from ".$dbname.".keu_kasbankdt where keterangan1='".$rPo['noinvoice']."'";
                // echo $sBayar;
                $resBayar = fetchData($sBayar);
                $sisa = ($rPo['nilaiinvoice']+$rPo['nilaippn']+$totalpinalti+$ppntotalpinalti) - abs($resBayar[0]['jmlhKas']);
                if($sisa > 0){
                    $no+=1;
                    $dat.="<tr class='rowcontent'>";
                    $dat.="<td align=center>".makeElement('inv_'.$key,'checkbox','',array('class'=>'inv-chk','invNo'=>$rPo['noinvoice'],'sisa'=>$sisa))."</td>";
                    $dat.="<td>".$rPo['noinvoice']."</td>";
                    $dat.="<td>".$rPo['nokontrak']."</td>";
                    $dat.="<td>".$optNmsupp[$rPo['kodecustomer']]."</td>";
                    $dat.="<td>".$rPo['namabarang']."</td>";
                    $dat.="<td align=right>".number_format($rPo['nilaiinvoice'],2)."</td>";
                    $dat.="<td align=right>".number_format($rPo['nilaippn'],2)."</td>";
                    $dat.="<td align=right>".number_format($totalpinalti,2)."</td>";
                    $dat.="<td align=right>".number_format($ppntotalpinalti,2)."</td>";
                    $dat.="<td align=right>".number_format($resBayar[0]['jmlhKas'],2)."</td>";
                    $dat.="<td align=right>".number_format($sisa,2)."</td>";
                    $key++;
                }
            }// while

            setIt($rJmlh['jmlhKas'],0);
            setIt($rCek['jmlhinvoice'],0);
            $dat.="</tbody></table></div></fieldset>";
            // echo $dat."__".$rJmlh['jmlhKas']."_____".$rCek['jmlhinvoice'];
            echo $dat;
        break;

        case 'getMemo':
                $tgl = explode('-',$param['periode']);
                if(empty($param['periode'])) {
                        exit("Warning: ".$_SESSION['lang']['notifperiodeformat']);
                }
                if($param['hutangunit']==0){
                        #jika hutang unit tidak di check maka masuk di query ini
                        #jamhari 04-april-2015
                        $qData = selectQuery($dbname,'keu_jurnalht','*',
                                                         "nojurnal like '%".$param['nojurnal']."%' and
                                                         tanggal like '%".$param['periode']."%' and
                                                         kodejurnal = 'M' and posting=0");
                        $resData = fetchData($qData);	
                }else{
                        $inQuery="select distinct nojurnal from ".$dbname.".keu_jurnaldt where noakun='".$param['noakunhutang']."' and kodeorg='".$param['pemilikhutang']."' and tanggal like  '%".$param['periode']."%' and nojurnal like  '%/M/%'";
                        $sData="select * from ".$dbname.".keu_jurnalht where  nojurnal in (".$inQuery.") ";                      
                        $qData=$owlPDO->query($sData);
                        $qData->setFetchMode(PDO::FETCH_ASSOC);
                        while($rData=$qData->fetch()){
                                $resData[]=$rData;
                        }

                }
                $dat = "<fieldset>";
                $dat .= "<legend>".$_SESSION['lang']['hasil']."</legend>";
                $dat .= "<div style='max-height:350px;overflow:auto'>";
                $dat .= "<table cellpadding=0 cellspacing=1 width=100% class=sortable><thead><tr class=rowheader>";
                $dat .= "<td align=center>".$_SESSION['lang']['nojurnal']."</td>";
                $dat .= "<td align=center>".$_SESSION['lang']['tanggal']."</td>";
                $dat .= "<td align=center>".$_SESSION['lang']['jumlah']."</td>";
                $dat .= "</tr></thead><tbody>";
                if(!empty($resData)){
                        foreach($resData as $row) {
                        if($row['totaldebet']==0){
                                $addSum="sum(debet) as totaldebet";
                                if($param['hutangunit']==1){
                                        $addDet=" and noakun='".$param['noakunhutang']."'";
                                        $addSum="sum(jumlah) as totaldebet";
                                }
                                @$sRp="select ".$addSum." from ".$dbname.".keu_jurnaldt_vw where nojurnal='".$row['nojurnal']."' ".$addDet."";
                                $qRp=$owlPDO->query($sRp);
                                $qRp->setFetchMode(PDO::FETCH_ASSOC);
                                $rRp=$qRp->fetch();
                                if($rRp['totaldebet']<0){
                                        $rRp['totaldebet']=$rRp['totaldebet']*(-1);
                                }
                                $row['totaldebet']=$rRp['totaldebet'];
                        }
                        $dat .= "<tr class=rowcontent style='cursor:pointer'";
                        $dat .= "onclick=\"getMemo('".$row['nojurnal']."')\">";
                        $dat .= "<td>".$row['nojurnal']."</td>";
                        $dat .= "<td align=center>".$row['tanggal']."</td>";
                        $dat .= "<td align=right>".number_format($row['totaldebet'],2)."</td>";
                        $dat .= "</tr>";
                        }	
                }else{
                        $dat .= "<tr class=rowcontent>";
                        $dat .= "<td colspan=3>".$_SESSION['lang']['dataempty']."</td>";
                        $dat .= "</tr>";
                }

                $dat .= "</tbody></table></div></fieldset>";
                echo $dat;
                break;

        /* Deposito */
        case'getformdeposito':

            $optbulan="<option value=''>".$_SESSION['lang']['bulan']."</option>";
            $opttahun="<option value=''>".$_SESSION['lang']['tahun']."</option>";
            for($i=1;$i<=12;$i++){
                if($i<10){
                    $i='0'.$i;
                }
                $optbulan.="<option value='".$i."'>".numToMonth($i,'I','long')."</option>";
            }

            $thnskrg=date('Y');
            $thnskrglima=$thnskrg-5;
            for($i=$thnskrglima;$i<=$thnskrg;$i++){
                $opttahun.="<option value='".$i."'>".$i."</option>";
            }
            
            $form="";
            $form = "<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
            $form.= "<table>";
            $form.= "<tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>";
            $form.= "<td><input type=text class=myinputtext id=notran></td>";
            $form.= "<td><select id=bulandeposito style='width:100px;' placeholder='Bulan'>".$optbulan."</select>";
            $form.= "<td><select id=tahundeposito style='width:100px;' placeholder='Tahun'>".$opttahun."</select>";
            $form.= "</tr>";
            $form.= "</table>";
            $form.= "<button class=mybutton onclick=finddeposito()>Find</button></fieldset>
                     <div id=container2></div>";
            echo $form;
        break;  

        case'getdatadeposito':

            if ($param['tahun']=='' || $param['bulan']=='') {
                exit('warning : tahun dan bulan tidak boleh kosong.');
            }

            $data="";
            $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
            $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
            $data.="<thead>";
            $data.="<tr align=center>";
            $data.="<td>".$_SESSION['lang']['notransaksi']."</td>";
            $data.="<td>".$_SESSION['lang']['nourut']." Bilyet</td>";
            $data.="<td>".$_SESSION['lang']['nourut']." Deposito</td>";
            $data.="<td>".$_SESSION['lang']['tanggal']." valuta</td>";
            $data.="<td>".$_SESSION['lang']['tanggal']." Jatuh Tempo</td>";
            $data.="<td>".$_SESSION['lang']['sukubunga']."</td>";
            $data.="<td>".$_SESSION['lang']['jumlah']." Deposito</td>";
            $data.="<td>".$_SESSION['lang']['jumlah']." Bunga</td>";
            $data.="<td>".$_SESSION['lang']['jumlah']." Pajak</td>";
            $data.="<td>".$_SESSION['lang']['jumlah']." Penalti</td>";
            $data.="<td>".$_SESSION['lang']['total']."</td>";
            $data.="</tr></thead>";
            
            if($param['notran']!=''){
                $where.=" and a.notransaksi like '%".$param['notran']."%'";
            }

            $tahunbulan=$param['tahun']."-".$param['bulan'];
            
            #data
            $str="select a.notransaksi, a.nobilyet, a.nodeposito, a.tglvaluta, a.tgljatuhtempo, a.sukubunga, a.jmlhdeposito, b.jumlahbunga, b.jumlahpajak, b.jumlahpenalti
                 from ".$dbname.".keu_depositoht a left join ".$dbname.".keu_depositodt b on a.notransaksi=b.notransaksi 
                 where a.noakun='".$param['rekening']."' and left(tglcair,7)='".$tahunbulan."' ".$where." and a.posting=1 and b.posting=1 and a.statusclose=0";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){

                $str1="select count(keterangan1) as jmlhdep from keu_kasbankdt where keterangan1='".$bar['notransaksi']."' and tahun='".$param['tahun']."' and bulan='".$param['bulan']."' ";
                $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $bar1=$res1->fetch();
                $jumlahdep=$bar1['jmlhdep'];

                if ($jumlahdep>0) {
                    continue;
                }

                $strak="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='BD'";
                $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
                $resak->setFetchMode(PDO::FETCH_ASSOC);
                $barak=$resak->fetch();

                $str1="select noaruskas from keu_5aruskas_detail where noakun='".$barak['nilai']."'";
                $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $bar1=$res1->fetch();
                $noaruskasdt=$bar1['noaruskas'];

                $str1="select id_ket from keu_5keterangan where noaruskas='".$noaruskasdt."'";
                $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $bar1=$res1->fetch();
                $keterangandt=$bar1['id_ket'];

                $total=$bar['jumlahbunga']-$bar['jumlahpajak']-$bar['jumlahpenalti'];
                $data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getdeposito('".$bar['notransaksi']."','','".$barak['nilai']."','".$total."','".$noaruskasdt."','".$keterangandt."')>";
                $data.= "<td>".$bar['notransaksi']."</td>";
                $data.= "<td>".$bar['nobilyet']."</td>";
                $data.= "<td>".$bar['nodeposito']."</td>";
                $data.= "<td>".tanggalnormal($bar['tglvaluta'])."</td>";
                $data.= "<td>".tanggalnormal($bar['tgljatuhtempo'])."</td>";
                $data.= "<td align=center>".$bar['sukubunga']." %</td>";
                $data.= "<td align=right>".number_format($bar['jmlhdeposito'])."</td>";
                $data.= "<td align=right>".number_format($bar['jumlahbunga'])."</td>";
                $data.= "<td align=right>".number_format($bar['jumlahpajak'])."</td>";
                $data.= "<td align=right>".number_format($bar['jumlahpenalti'])."</td>";
                $data.= "<td align=right>".number_format($total)."</td>";
                $data.= "</tr>";
            }
            $data.= "</table></fieldset>";
            echo $data;
        break;

        case'adddeposito':
            $param = $_POST;

            $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BANKPINJAM'";
            $res = $owlPDO->query($str);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            $rekpinjam=explode(',',$bar['nilai']);
            foreach($rekpinjam as $key){
                $arrpinjam[$key]=$key;
            }

            $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKUNPINJAM'";
            $res = $owlPDO->query($str);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            $akpinjam=$bar['nilai'];    
            
            if(in_array($param['rekening'],$arrpinjam)){
                $param['noakun']=$akpinjam;
            }

           
            $str="insert into ".$dbname.".keu_kasbankdt (`notransaksi`, `noakun`, `tipetransaksi`, `tanggal`,
                    `jumlah`, `noakun2a`, `kode`,`keterangan1`,`keterangan2`, `matauang`, `kurs`,`kurs2`,`noaruskas`,`kodeorg`,`nodok`,`bulan`,`tahun`) 
            values ('".$param['notransaksi']."','".$param['noakundt']."','".$param['tipetransaksi']."','".tanggalsystemn($param['tanggal'])."',
                    '".$param['jumlah']."','".$param['noakun']."','".$param['kode']."',
                    '".$param['notran']."','".$keterangan2."','".$param['matauang']."','".$param['kurs']."','1','".$param['noaruskas']."',
                    '".$param['kodeorg']."','".$param['notran']."','".$param['bulandeposito']."','".$param['tahundeposito']."')";
            try{
                $owlPDO->exec($str);
            }
            catch (PDOException $e)  {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }

        break;
        /*end of deposito*/

        /* All Data */
        case'getformdata':

			$optbulan=$opttahun="";
			$blnskrg=date('m');
			for($i=1;$i<=12;$i++){
				if($i<10){
					$i='0'.$i;
				}
				
				if($i==$blnskrg){
					$optbulan.="<option value='".$i."' selected>".numToMonth($i,'I','long')."</option>";
				} else {
					$optbulan.="<option value='".$i."'>".numToMonth($i,'I','long')."</option>";
				}
				
				
			}

			$thnskrg=date('Y');
			$thnskrglima=$thnskrg-5;
			for($i=$thnskrglima;$i<=$thnskrg;$i++){
				if($i==$thnskrg){
					$opttahun.="<option value='".$i."' selected>".$i."</option>";
				}else{
					$opttahun.="<option value='".$i."'>".$i."</option>";
				}
				
			}

            $optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            // $optjenis.="<option value='bansos'>Bansos</option>";
            // $optjenis.="<option value='pp' >Pengajuan Pembayaran</option>";
            // $optjenis.="<option value='modal' >Modal</option>";
            // $optjenis.="<option value='dividen' >Dividen</option>";
            $optjenis.="<option value='inout'>Tax Vat In Vat Out</option>";
            // $optjenis.="<option value='grl'>Ganti Rugi Tanam Tumbuh</option>";
            // $optjenis.="<option value='UM'>Uang Muka</option>";
            $optjenis.="<option value='notadebet'>Nota Debet</option>";
            $optjenis.="<option value='feepanen'>Fee Panen</option>";
            $optjenis.="<option value='umpjdinas'>Pemby Uang Muka Pj. Dinas</option>";
            $optjenis.="<option value='realpjdinas'>Pemby Pj. Dinas (tiket pesawat dll)</option>";
            $optjenis.="<option value='claimpjdinas'>Pemby Klaim Pj. Dinas</option>";
            $optjenis.="<option value='batalpjd'>Pengembalian UM Pj. Dinas (BATAL DINAS)</option>";
            
            $form="";
            $form = "<fieldset style='width:96%'><legend>".$_SESSION['lang']['find']."</legend>";
            $form.= "<table>";
            $form.= "<tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>";
            $form.= "<td><input type=text class=myinputtext id=notran style=width:150px;></td>";
            $form.= "<td>".$_SESSION['lang']['jenis']."</td><td>:</td>";
            $form.= "<td><select id=jenisdata style=width:150px;>".$optjenis."</select></td></tr>";
            $form.= "<tr hidden><td>".$_SESSION['lang']['bulan']."</td><td>:</td>";
            $form.= "<td><select id=bulandata style='width:150px;' placeholder='Bulan'>".$optbulan."</select>";
            $form.= "<td>".$_SESSION['lang']['tahun']."</td><td>:</td>";
            $form.= "<td><select id=tahundata style='width:150px;' placeholder='Tahun'>".$opttahun."</select>";
            $form.= "</tr>";
            $form.= "<tr>";
            $form.= "<td></td><td></td>";
            $form.= "<td><button class=mybutton onclick=finddata()>Find</button></td>";
            $form.= "</tr>";
            $form.= "</table>";
            $form.= "</fieldset>
                     <div style=clear:both></div>
                     <div id=container2></div>";
            echo $form;
        break;  

        case'getdata':
			$data=$where="";

			switch($param['jenisdata']){
				// case'modal':
				// 	$data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
				// 	$data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
				// 	$data.="<thead>";
				// 	$data.="<tr align=center>";
				// 	$data.="<td>".$_SESSION['lang']['notransaksi']."</td>";
				// 	$data.="<td>".$_SESSION['lang']['tanggal']."</td>";
				// 	$data.="<td> Unit Pemberi Modal</td>";
				// 	$data.="<td> Unit Penerima Modal</td>";
				// 	$data.="<td>Total Modal</td>";
				// 	$data.="</tr></thead>";

				// 	if ($param['tipetransaksi']=='K') {   
				// 		$where.=" unit_pemberimodal='".$param['kodeorg']."' and norekening_pemberimodal='".$param['rekening']."' and status=1";
				// 	}

				// 	if ($param['tipetransaksi']=='M') {   
				// 		$where.=" unit_penerimamodal='".$param['kodeorg']."' and norekening_penerimamodal='".$param['rekening']."' and status in (1,2)";
				// 	}
				
				// 	if($param['notran']!=''){
				// 		$where.=" and notransaksi like '%".$param['notran']."%'";
				// 	}

				// 	#data
				// 	$str="select * from ".$dbname.".keu_modal where ".$where." ";
				// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				// 	$res->setFetchMode(PDO::FETCH_ASSOC);
				// 	while($bar=$res->fetch()){

				// 		$whrorg="kodeorganisasi='".$bar['unit_pemberimodal']."'";
				// 		$optpemberi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg);
				// 		$whrorg2="kodeorganisasi='".$bar['unit_penerimamodal']."'";
				// 		$optpenerima=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg2);

				// 		if ($param['tipetransaksi']=='K') {   
				// 			$whrcaco=" kodeorg='".$bar['unit_penerimamodal']."'";
				// 			$noakun=" akunpiutang as noakun ";
				// 			$whrarus=" left(noaruskas,1)='1' ";
				// 		}

				// 		if ($param['tipetransaksi']=='M') {   
				// 			$whrcaco=" kodeorg='".$bar['unit_pemberimodal']."'";
				// 			$noakun=" akunhutang as noakun ";
				// 			$whrarus=" left(noaruskas,1)='2' ";
				// 		}

				// 		$sisamodal=0;
				// 		//sum jumlah modal yg telah diambil di kasbank
				// 		$strak="select sum(jumlah) as jumlahtot from ".$dbname.".keu_kasbankdt where keterangan1='".$bar['notransaksi']."' and tipetransaksi='".$param['tipetransaksi']."'";
				// 		$resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
				// 		$resak->setFetchMode(PDO::FETCH_ASSOC);
				// 		$barak=$resak->fetch();
				// 		$jumlahkasbank=$barak['jumlahtot'];
				// 		$sisamodal=$bar['nilai_modal']-$jumlahkasbank;

				// 		$strak="select ".$noakun." from ".$dbname.".keu_5caco where ".$whrcaco." and jenis='inter'";
				// 		$resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
				// 		$resak->setFetchMode(PDO::FETCH_ASSOC);
				// 		$barak=$resak->fetch();

				// 		$str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$barak['noakun']."' and ".$whrarus." ";
				// 		$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
				// 		$res1->setFetchMode(PDO::FETCH_ASSOC);
				// 		$bar1=$res1->fetch();
				// 		$noaruskasdt=$bar1['noaruskas'];

				// 		$str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt."'";
				// 		$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
				// 		$res1->setFetchMode(PDO::FETCH_ASSOC);
				// 		$bar1=$res1->fetch();
				// 		$keterangandt=$bar1['id_ket'];

				// 		$data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$barak['noakun']."','".$sisamodal."','".$noaruskasdt."','".$keterangandt."')>";
				// 		$data.= "<td>".$bar['notransaksi']."</td>";
				// 		$data.= "<td>".tanggalnormal($bar['tanggal'])."</td>";
				// 		$data.= "<td>".$optpemberi[$bar['unit_pemberimodal']]."</td>";
				// 		$data.= "<td>".$optpenerima[$bar['unit_penerimamodal']]."</td>";
				// 		$data.= "<td align=right>".number_format($sisamodal)."</td>";
				// 		$data.= "</tr>";
				// 	}
				// 	$data.= "</table></fieldset>";
				// break;
				
				// case'dividen':
				// 	$data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
				// 	$data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
				// 	$data.="<thead>";
				// 	$data.="<tr align=center>";
				// 	$data.="<td>".$_SESSION['lang']['notransaksi']."</td>";
				// 	$data.="<td>".$_SESSION['lang']['tanggal']."</td>";
				// 	$data.="<td>".$_SESSION['lang']['unit']." Penerima</td>";
				// 	$data.="<td>".$_SESSION['lang']['status']."</td>";
				// 	$data.="<td>".$_SESSION['lang']['total']."</td>";
				// 	$data.="</tr></thead>";

				// 	// if ($param['tipetransaksi']=='K') {   
				// 	// 	$where.=" unit='".$param['kodeorg']."' and status='Issuer'";
				// 	// }

				// 	// if ($param['tipetransaksi']=='M') {   
				// 	// 	$where.=" unit='".$param['kodeorg']."' and status='Receiver'";
				// 	// }
				
				// 	if($param['notran']!=''){
				// 		$where.=" notransaksi like '%".$param['notran']."%'";
				// 	}

				// 	#data
				// 	$str="select * from ".$dbname.".keu_dividen where ".$where." and statusaktif=1 and (unit1='".$param['kodeorg']."' or unit2='".$param['kodeorg']."') ";
				// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				// 	$res->setFetchMode(PDO::FETCH_ASSOC);
				// 	while($bar=$res->fetch()){

    //                     if ($bar['tipetransaksi']=='Modal') {
    //                         $jurnalid='MOD';
    //                     }else{
    //                         $jurnalid='DIV';
    //                     }

    //                     if ($param['tipetransaksi']=='K') {
                            
    //                         $fieldnoakun=' noakunkredit as noakun ';

    //                         if ($bar['status']=='Issuer') {
    //                             $unit=$bar['unit1'];
    //                             $statusunit=$bar['statusunit1'];
    //                         }else{
    //                             $unit=$bar['unit2'];
    //                             $statusunit=$bar['statusunit2'];
    //                         }
    //                     }

    //                     if ($param['tipetransaksi']=='M') {

    //                         $fieldnoakun=' sampaidebet as noakun ';

    //                         if ($bar['status']=='Receiver') {
    //                             $unit=$bar['unit1'];
    //                             $statusunit=$bar['statusunit1'];
    //                         }else{
    //                             $unit=$bar['unit2'];
    //                             $statusunit=$bar['statusunit2'];
    //                         }
    //                     }

    //                     if ($unit=='' || $statusunit==1) {
    //                         continue;
    //                     }


    //                     $whrorg2="kodeorganisasi='".$unit."'";
    //                     $optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg2);

				// 		// //check data sudah ada di kasbank atau belum
				// 		// $strak="select notransaksi from ".$dbname.".keu_kasbankdt where keterangan1='".$bar['notransaksi']."'";
				// 		// $resak=fetchData($strak);
				// 		// if (count($resak)!=0) {
				// 		// 	continue;
				// 		// }

				// 		// $strak="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKDIV'";
    //                     // $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
    //                     // $resak->setFetchMode(PDO::FETCH_ASSOC);
    //                     // $barak=$resak->fetch();

    //                     $strak="select ".$fieldnoakun." from ".$dbname.".keu_5parameterjurnal where jurnalid='".$jurnalid."'";
				// 		$resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
				// 		$resak->setFetchMode(PDO::FETCH_ASSOC);
				// 		$barak=$resak->fetch()    ;

				// 		$str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$barak['noakun']."'";
				// 		$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
				// 		$res1->setFetchMode(PDO::FETCH_ASSOC);
				// 		$bar1=$res1->fetch();
				// 		$noaruskasdt=$bar1['noaruskas'];

				// 		$str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt."'";
				// 		$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
				// 		$res1->setFetchMode(PDO::FETCH_ASSOC);
				// 		$bar1=$res1->fetch();
				// 		$keterangandt=$bar1['id_ket'];

				// 		$data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$barak['noakun']."','".$bar['nilai']."','".$noaruskasdt."','".$keterangandt."')>";
				// 		$data.= "<td>".$bar['notransaksi']."</td>";
				// 		$data.= "<td>".tanggalnormal($bar['tanggal'])."</td>";
				// 		$data.= "<td>".$optunit[$unit]."</td>";
				// 		$data.= "<td>".$bar['status']."</td>";
				// 		$data.= "<td align=right>".number_format($bar['nilai'])."</td>";
				// 		$data.= "</tr>";
				// 	}
				// 	$data.= "</table></fieldset>";
				// break;
				case'claimpjdinas':
				
					if($param['tipetransaksi']=='M'){
						#exit("Warning : Tipe transaksi yang diperbolehkan hanya keluar");
					}
					if($param['notran']!=''){
						$where.=" and a.notransaksi like '%".$param['notran']."%'";
					}
					
					$data.="<fieldset style=overflow:auto;width:500;height:250px;><legend>".$_SESSION['lang']['result']."</legend>";
					$data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
					$data.="<thead><tr class=rowheader>
							
					<td align=center>No</td>
					<td align=center>".$_SESSION['lang']['notransaksi']."</td>
					<td align=center>".$_SESSION['lang']['jenis']."</td>
					<td align=center>".$_SESSION['lang']['nama']."</td>
					<td align=center>".$_SESSION['lang']['noakun']."</td>
					<td align=center>".$_SESSION['lang']['aruskas']."</td>
					<td align=center>".$_SESSION['lang']['uangmuka']."</td>
					<td align=center>".$_SESSION['lang']['nilai']."</td>
					</tr>
					</thead>";
					
					$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
					$no=0;	$dataxx=array();
					
					$str = "SELECT *  FROM ".$dbname.".sdm_pjdinasht a  left join ".$dbname.".sdm_pjdinasdt b 
					on a.notransaksi=b.notransaksi where 1=1 " . $where . " and b.sumber='1' and b.tanggungan='1' and a.statuspengajuan='1' and a.statusrealisasi='1' and b.jumlahhrd>'0' and b.statusverifikasihrd='1'
					order by a.notransaksi desc";
					$res = fetchdata($str);
					foreach ($res as $bar){
						#staffataunonstaff
						if($bar['tipekary']=='0'){
							$tipekar='staff';
						}else{
							$tipekar='nonstaff';
						}
						$level=$bar['level'];
						$dataxx[$bar['notransaksi']]=$bar['notransaksi'];
						$jlhrp[$bar['notransaksi']]+=$bar['jumlahhrd'];
						$kary[$bar['notransaksi']]=$bar['karyawanid'];
						$kdorg[$bar['notransaksi']]=$bar['kodeorg'];
						$jenisbyy[$bar['notransaksi']]=$bar['jenisbiaya'];
					}
					
					foreach($dataxx as $notransaksi){
						
						$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$kary[$notransaksi]."'");
						$namaid=$nmkar[$kary[$notransaksi]];
						
						$opttipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kdorg[$notransaksi]."'");
						$kodeorg=$kdorg[$notransaksi];
						$tipeorg=$opttipeorg[$kdorg[$notransaksi]];
					
						if($tipeorg=='HOLDING' or $tipeorg=='KANWIL'){
							if($tipekar=='staff'){
								if($level=='0'){
									#ini staff
									$noakun='8212101';
								}else{
									#ini level manager keatas
									$noakun='8122101';
								}
							}else{
								#ini nonstaff
								$noakun='8212101';
							}
						}else{
							#ini unit kebun dan pks
							$noakun='7111501';
						}
						
						#info uang muka
						$umdiambil=0;
						$str="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2='umpjd#".$notransaksi."' and nik='".$kary[$notransaksi]."'";
						$res = fetchdata($str);
						$umdiambil=$res[0]['jumlah'];
						
						$n="";
						if($umdiambil>$jlhrp[$notransaksi]){
							$n="style=color:red;";
						}
						
						$str1="select distinct a.noaruskas,nama_aruskas from ".$dbname.".keu_5aruskas_detail a left join ".$dbname.".keu_5aruskas b 
						on a.noaruskas=b.noaruskas where a.noakun='".$noakun."' and b.tipetransaksi='K'";
						$res1 = fetchdata($str1);
						$noaruskasdt=$res1[0]['noaruskas'];
						$optaruskas="";
						foreach($res1 as $bar1){
							$optaruskas.="<option value=".$bar1['noaruskas'].">".$bar1['noaruskas']." - ".$bar1['nama_aruskas']."</option>";
						}
						$optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$noakun."'");
						
						$keterangandata="Pemby klaim pjd:".$namaid.";Nomor:".$notransaksi;
						$keterangan="claimpjd#".$notransaksi;
						
						$strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan2='".$keterangan."'";
						$resk = fetchdata($strk);
						$jumdata=$resk[0]['jumlah'];
						#= indra
						$adddata="";
						if($jumdata==0){
							$no++;
							$adddata=" style='cursor:pointer' onclick=\"getdatadt(
											'".$notransaksi."','".$kodeorg."','".$noakun."',
											'".$jlhrp[$notransaksi]."','".$noaruskasdt."',
											'".$keterangandata."','','".$kary[$notransaksi]."',
											'".$keterangan."');\"";
							$data.= "<tr class=rowcontent>";
							$data.= "<td align=center title='add detail' ".$adddata.">".$no."</td>";
							$data.= "<td align=center title='add detail' ".$adddata.">".$notransaksi."</td>";
							$data.= "<td align=left title='add detail' ".$adddata.">Claim</td>";
							$data.= "<td align=left title='add detail' ".$adddata.">".$namaid."</td>";
							$data.= "<td align=right title='add detail' ".$adddata.">".$optnmakun[$noakun]."</td>";
							$data.= "<td align=left title='add detail'><select id=aruskaspjd style=width:150px>".$optaruskas."</select></td>";
							$data.= "<td align=right title='add detail' ".$adddata.">".number_format($umdiambil)."</td>";
							$data.= "<td align=right title='add detail' ".$n.">".number_format($jlhrp[$notransaksi])."</td>";
							$data.= "</tr>";
						}
					}
					$data.= "</table>";
				break;
				
				case'realpjdinas':
					if($param['tipetransaksi']=='M'){
						exit("Warning : Tipe transaksi yang diperbolehkan hanya keluar");
					}
					
					if($param['notran']!=''){
						$where.=" and a.notransaksi like '%".$param['notran']."%'";
					}
					
					$data.="<fieldset style=overflow:auto;width:500;height:250px;><legend>".$_SESSION['lang']['result']."</legend>";
					$data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
					$data.="<thead><tr class=rowheader>
							
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['notransaksi']."</td>
						<td align=center>".$_SESSION['lang']['jenis']."</td>
						<td align=center>".$_SESSION['lang']['nama']."</td>
						<td align=center>".$_SESSION['lang']['noakun']."</td>
						<td align=center>".$_SESSION['lang']['aruskas']."</td>
						<td align=center>".$_SESSION['lang']['nilai']."</td>
						</tr>
						</thead>";
						
						$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
						$no=0;	$dataxx=array();
						$str = "SELECT *  FROM ".$dbname.".sdm_pjdinasht a  left join ".$dbname.".sdm_pjdinasdt b 
						on a.notransaksi=b.notransaksi where 1=1 " . $where . " and b.sumber='1' and b.tanggungan='0' and a.statuspengajuan='1' and b.jumlah>'0' and a.statusconfirm='1' order by a.notransaksi desc";
						$res = fetchdata($str);
						foreach ($res as $bar){
							#staffataunonstaff
							if($bar['tipekary']=='0'){
								$tipekar='staff';
							}else{
								$tipekar='nonstaff';
							}
							
							$level=$bar['level'];
							
							$dataxx[$bar['notransaksi']][$bar['jenisbiaya']]=$bar['jenisbiaya'];
							$jlhrp[$bar['notransaksi']][$bar['jenisbiaya']]+=$bar['jumlah'];
							$kary[$bar['notransaksi']][$bar['jenisbiaya']]=$bar['karyawanid'];
							$kdorg[$bar['notransaksi']][$bar['jenisbiaya']]=$bar['kodeorg'];
							
						}
						
						foreach($dataxx as $notransaksi => $valjenisbyy){
							foreach($valjenisbyy as $jenisbyy){
								$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$kary[$notransaksi][$jenisbyy]."'");
								$namaid=$nmkar[$kary[$notransaksi][$jenisbyy]];
								
								
								$opttipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kdorg[$notransaksi][$jenisbyy]."'");
								$kodeorg=$kdorg[$notransaksi][$jenisbyy];
								$tipeorg=$opttipeorg[$kdorg[$notransaksi][$jenisbyy]];
								
								if($tipeorg=='HOLDING' or $tipeorg=='KANWIL'){
									if($tipekar=='staff'){
										if($level=='0'){
											#ini staff
											$noakun='8212101';
										}else{
											#ini level manager keatas
											$noakun='8122101';
										}
									}else{
										#ini nonstaff
										$noakun='8212101';
									}
								}else{
									#ini unit kebun dan pks
									$noakun='7111501';
								}
								
								
								$str1="select distinct a.noaruskas,nama_aruskas from ".$dbname.".keu_5aruskas_detail a left join ".$dbname.".keu_5aruskas b 
								on a.noaruskas=b.noaruskas where a.noakun='".$noakun."' and b.tipetransaksi='K'";
								$res1 = fetchdata($str1);
								$noaruskasdt=$res1[0]['noaruskas'];
								$optaruskas="";
								foreach($res1 as $bar1){
									$optaruskas.="<option value=".$bar1['noaruskas'].">".$bar1['noaruskas']." - ".$bar1['nama_aruskas']."</option>";
								}
								$optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$noakun."'");
								
								$keterangandata="Pemby:".$optjns[$jenisbyy].";pjd an:".$namaid;
								$keterangan="realpjd#".$notransaksi."#".$jenisbyy;
								
								$strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan2='".$keterangan."'";
								$resk = fetchdata($strk);
								$jumdata=$resk[0]['jumlah'];
								#= indra
								$adddata="";
								if($jumdata==0){
									$no++;
									$adddata=" style='cursor:pointer' onclick=\"getdatadt(
													'".$notransaksi."','".$kodeorg."','".$noakun."',
													'".$jlhrp[$notransaksi][$jenisbyy]."','".$noaruskasdt."',
													'".$keterangandata."','','".$kary[$notransaksi][$jenisbyy]."',
													'".$keterangan."');\"";
									$data.= "<tr  class=rowcontent>";
									$data.= "<td align=center title='add detail' ".$adddata.">".$no."</td>";
									$data.= "<td align=center title='add detail' ".$adddata.">".$notransaksi."</td>";
									$data.= "<td align=left title='add detail' ".$adddata.">".$optjns[$jenisbyy]."</td>";
									$data.= "<td align=left title='add detail' ".$adddata.">".$namaid."</td>";
									$data.= "<td align=right title='add detail' ".$adddata.">".$optnmakun[$noakun]."</td>";
									$data.= "<td align=left title='add detail'><select id=aruskaspjd style=width:150px>".$optaruskas."</select></td>";
									$data.= "<td align=right title='add detail' ".$adddata.">".number_format($jlhrp[$notransaksi][$jenisbyy])."</td>";
									$data.= "</tr>";
								}
							}
						}
					$data.= "</table>";
				break;
				
				case'umpjdinas':
					if($param['tipetransaksi']=='M'){
						exit("Warning : Tipe transaksi yang diperbolehkan hanya keluar");
					}
				
					if($param['notran']!=''){
						$where.=" and a.notransaksi like '%".$param['notran']."%'";
					}
					
					$data.="<fieldset style=overflow:auto;width:500;height:250px;><legend>".$_SESSION['lang']['result']."</legend>";
					$data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
					$data.="<thead><tr class=rowheader>
							
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['notransaksi']."</td>
						<td align=center>".$_SESSION['lang']['jenis']."</td>
						<td align=center>".$_SESSION['lang']['nama']."</td>
						<td align=center>".$_SESSION['lang']['noakun']."</td>
						<td align=center>".$_SESSION['lang']['aruskas']."</td>
						<td align=center>".$_SESSION['lang']['nilai']."</td>
						</tr>
						</thead>";
						
						$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
						$no=0;	$dataxx=array();
						$str = "SELECT *  FROM ".$dbname.".sdm_pjdinasht a  left join ".$dbname.".sdm_pjdinasdt b 
						on a.notransaksi=b.notransaksi where 1=1 " . $where . " and b.sumber='0' and a.statuspengajuan='1' 
						and b.jumlah>'0' order by a.notransaksi desc";
						$res = fetchdata($str);
						foreach ($res as $bar){
							#staffataunonstaff
							if($bar['tipekary']=='0'){
								$tipekar='staff';
							}else{
								$tipekar='nonstaff';
							}
							
							$dataxx[$bar['notransaksi']]=$bar['notransaksi'];
							$jlhrp[$bar['notransaksi']]+=$bar['jumlah'];
							$kary[$bar['notransaksi']]=$bar['karyawanid'];
							$kdorg[$bar['notransaksi']]=$bar['kodeorg'];
							$jenisbyy[$bar['notransaksi']]=$bar['jenisbiaya'];
							
						}
						
						foreach($dataxx as $notransaksi){
							$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$kary[$notransaksi]."'");
							$namaid=$nmkar[$kary[$notransaksi]];
							
							
							$opttipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kdorg[$notransaksi]."'");
							$kodeorg=$kdorg[$notransaksi];
							$tipeorg=$opttipeorg[$kdorg[$notransaksi]];
						
							$noakun='1180305';
							$str1="select distinct a.noaruskas,nama_aruskas from ".$dbname.".keu_5aruskas_detail a left join ".$dbname.".keu_5aruskas b 
							on a.noaruskas=b.noaruskas where a.noakun='".$noakun."' and b.tipetransaksi='K'";
							$res1 = fetchdata($str1);
							$noaruskasdt=$res1[0]['noaruskas'];
							$optaruskas="";
							foreach($res1 as $bar1){
								$optaruskas.="<option value=".$bar1['noaruskas'].">".$bar1['noaruskas']." - ".$bar1['nama_aruskas']."</option>";
							}
							
							
							$optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$noakun."'");
							
							$keterangandata="Pemby um pjd:".$namaid.";Nomor:".$notransaksi;
							$keterangan="umpjd#".$notransaksi;
							
							$strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan2='".$keterangan."'";
							$resk = fetchdata($strk);
							$jumdata=$resk[0]['jumlah'];
							#= indra
							$adddata="";
							if($jumdata==0){
								$no++;
								$adddata="style='cursor:pointer' onclick=\"getdatadt(
												'".$notransaksi."','".$kodeorg."','".$noakun."',
												'".$jlhrp[$notransaksi]."','".$noaruskasdt."',
												'".$keterangandata."','','".$kary[$notransaksi]."',
												'".$keterangan."');\"";
								$data.= "<tr  class=rowcontent >";
								$data.= "<td align=center title='add detail' ".$adddata.">".$no."</td>";
								$data.= "<td align=center title='add detail' ".$adddata.">".$notransaksi."</td>";
								$data.= "<td align=left title='add detail' ".$adddata.">UM</td>";
								$data.= "<td align=left title='add detail' ".$adddata.">".$namaid."</td>";
								$data.= "<td align=right title='add detail' ".$adddata.">".$optnmakun[$noakun]."</td>";
								$data.= "<td align=left title='add detail'><select id=aruskaspjd style=width:150px>".$optaruskas."</select></td>";
								$data.= "<td align=right title='add detail' ".$adddata.">".number_format($jlhrp[$notransaksi])."</td>";
								$data.= "</tr>";
							}
						}
					$data.= "</table>";
				break;
				
				case'batalpjd':
					if($param['tipetransaksi']=='K'){
						exit("Warning : Tipe transaksi yang diperbolehkan hanya masuk");
					}
				
					if($param['notran']!=''){
						$where.=" and a.notransaksi like '%".$param['notran']."%'";
					}
					
					$data.="<fieldset style=overflow:auto;width:500;height:250px;><legend>".$_SESSION['lang']['result']."</legend>";
					$data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
					$data.="<thead><tr class=rowheader>
							
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['notransaksi']."</td>
						<td align=center>".$_SESSION['lang']['jenis']."</td>
						<td align=center>".$_SESSION['lang']['nama']."</td>
						<td align=center>".$_SESSION['lang']['noakun']."</td>
						<td align=center>".$_SESSION['lang']['aruskas']."</td>
						<td align=center>".$_SESSION['lang']['nilai']."</td>
						</tr>
						</thead>";
						
						$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
						$no=0;	$dataxx=array();
						$str = "SELECT *  FROM ".$dbname.".sdm_pjdinasht a  left join ".$dbname.".sdm_pjdinasdt b 
						on a.notransaksi=b.notransaksi where 1=1 " . $where . " and a.statuspengajuan='3' 
						and b.jumlah>'0' order by a.notransaksi desc";
						$res = fetchdata($str);
						foreach ($res as $bar){
							#staffataunonstaff
							if($bar['tipekary']=='0'){
								$tipekar='staff';
							}else{
								$tipekar='nonstaff';
							}
							
							$dataxx[$bar['notransaksi']]=$bar['notransaksi'];
							$kary[$bar['notransaksi']]=$bar['karyawanid'];
							$kdorg[$bar['notransaksi']]=$bar['kodeorg'];
							$jenisbyy[$bar['notransaksi']]=$bar['jenisbiaya'];
						}
						
						foreach($dataxx as $notransaksi){
							$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$kary[$notransaksi]."'");
							$namaid=$nmkar[$kary[$notransaksi]];
							
							
							$opttipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kdorg[$notransaksi]."'");
							$kodeorg=$kdorg[$notransaksi];
							$tipeorg=$opttipeorg[$kdorg[$notransaksi]];
						
							$noakun='1180305';
							$str1="select distinct a.noaruskas,nama_aruskas from ".$dbname.".keu_5aruskas_detail a left join ".$dbname.".keu_5aruskas b 
							on a.noaruskas=b.noaruskas where a.noakun='".$noakun."' and b.tipetransaksi='K'";
							$res1 = fetchdata($str1);
							$noaruskasdt=$res1[0]['noaruskas'];
							$optaruskas="";
							foreach($res1 as $bar1){
								$optaruskas.="<option value=".$bar1['noaruskas'].">".$bar1['noaruskas']." - ".$bar1['nama_aruskas']."</option>";
							}
							
							$optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$noakun."'");
							
							$keterangandata="Pengembalian um pjd (Batal Dinas):".$namaid.";Nomor:".$notransaksi;
							$wh="umpjd#".$notransaksi;
							$keterangan="pjdbatal#".$notransaksi;
							$strk="select sum(jumlah) as jumlah from ".$dbname.".keu_kasbankdt where keterangan2='".$wh."'";
							$resk = fetchdata($strk);
							$rupiah=$resk[0]['jumlah'];
							
							
							$strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan2='".$keterangan."'";
							$resk = fetchdata($strk);
							$jumdata=$resk[0]['jumlah'];
							$adddata="";
							if($jumdata==0 and $rupiah>0){ 
								$no++;
								$adddata=" style='cursor:pointer' onclick=\"getdatadt(
												'".$notransaksi."','".$kodeorg."','".$noakun."',
												'".$rupiah."','".$noaruskasdt."',
												'".$keterangandata."','','".$kary[$notransaksi]."',
												'".$keterangan."');\"";
								$data.= "<tr  class=rowcontent>";
								$data.= "<td align=center title='add detail' ".$adddata.">".$no."</td>";
								$data.= "<td align=center title='add detail' ".$adddata.">".$notransaksi."</td>";
								$data.= "<td align=left title='add detail' ".$adddata.">Batal UM</td>";
								$data.= "<td align=left title='add detail' ".$adddata.">".$namaid."</td>";
								$data.= "<td align=right title='add detail' ".$adddata.">".$optnmakun[$noakun]."</td>";
								$data.= "<td align=left title='add detail'><select id=aruskaspjd style=width:150px>".$optaruskas."</select></td>";
								$data.= "<td align=right title='add detail' ".$adddata.">".number_format($rupiah)."</td>";
								$data.= "</tr>";
							}
						}
					$data.= "</table>";
				break;
				
				
				case'feepanen':
					if($param['tipetransaksi']=='M'){
							exit("Warning : Tipe transaksi yang diperbolehkan hanya keluar");
					}
					if($param['notran']!=''){
						$where.=" and nospb like '%".$param['notran']."%'";
					}
					
					// print_r($param);exit("Error:A");
					
					$data.="<fieldset style=overflow:auto;width:500;height:250px;><legend>".$_SESSION['lang']['result']."</legend>";
					$data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
					$data.="<thead><tr class=rowheader>
							
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['nama']."</td>
						<td align=center width=20px>".$_SESSION['lang']['jenis']."</td>
						<td align=center width=20px>".$_SESSION['lang']['periode']."</td>
						<td align=center width=20px>".$_SESSION['lang']['noakun']."</td>
						<td align=center width=20px>".$_SESSION['lang']['kg']."</td>
						<td align=center width=20px>".$_SESSION['lang']['nilai']."</td>
						</tr>
						</thead>";
						// kebun_rekapangkutantbsdtfee
						

						 
						// $str="select * from ".$dbname.".kebun_rekapangkutantbsdtfee_vw 
							// where kodeorg='".$param['kodeorg']."' ".$where.""; 
							// echo $str;
							
						$str="SELECT sum(rupiah) as rupiah,sum(kgtotal) as kgtotal,id,jenisfee,substr(tanggal,1,7) as periode,noakun
							FROM ".$dbname.".`kebun_rekapangkutantbsdtfee_vw`
							WHERE bayar=0 and kodeorg='".$param['kodeorg']."' and posting=1 ".$where." 
							group by id,jenisfee,periode";
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						while($bar=$res->fetch()){
							
							$str1="select nama,alamat,kodeorg from ".$dbname.".kebun_5namafee where id='".$bar['id']."'";
							// echo $str1;
							$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
							$res1->setFetchMode(PDO::FETCH_ASSOC);
							$bar1=$res1->fetch();
							$namaid=$bar1['nama']."[".$bar1['kodeorg']."]";
							
						
							$str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".substr($bar['noakun'],0,7)."'";
							// echo $str1;
							$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
							$res1->setFetchMode(PDO::FETCH_ASSOC);
							$bar1=$res1->fetch();
							$noaruskasdt=$bar1['noaruskas'];
							
							$keterangandata="Penerima:".$namaid.";Periode:".$bar['periode'].";Jenis:".$bar['jenisfee'];
							$keterangan="pf#".$bar['id']."#".$bar['periode']."#".$bar['jenisfee'];
							
							
							$strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where 
							nodok='".$keterangan."'";
							$resk=$owlPDO->query($strk)or die(print " Gagal: ".PDOException::getMessage());
							$resk->setFetchMode(PDO::FETCH_ASSOC);
							$bark=$resk->fetch();
							$jumdata=$bark['jumlah'];
							// echo $jumdata;
							#= indra
							if($jumdata==0){
								$no++;
								$data.= "<tr  class=rowcontent style='cursor:pointer' 
										onclick=\"getdatadt('".$keterangan."','','2169998','".$bar['rupiah']."','".$noaruskasdt."','".$keterangandata."','','".$bar['id']."','".$keterangan."');\">";
														
									$data.= "<td align=center title='add detail'>".$no."</td>";
									$data.= "<td align=left title='add detail'>".$namaid."</td>";
									$data.= "<td align=center title='add detail'>".$bar['jenisfee']."</td>";
									$data.= "<td align=center title='add detail'>".$bar['periode']."</td>";
									$data.= "<td align=right title='add detail'>2169998</td>";
									$data.= "<td align=right title='add detail'>".number_format($bar['kgtotal'],2)."</td>";
									$data.= "<td align=right title='add detail'>".number_format($bar['rupiah'],2)."</td>";
								$data.= "</tr>";
							}
						}
						$data.= "</table>";
				break;
				
				case'notadebet':
					if($param['tipetransaksi']=='K'){
                        exit("Warning : Tipe transaksi yang diperbolehkan hanya Masuk");
                    }
                    if($param['notran']!=''){
                        $where.=" and notadebet like '%".$param['notran']."%'";
                    }
                    
                    $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
                    $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
                    $data.="<thead><tr class=rowheader>
							
							<td align=center>No</td>
							<td align=center width=20px>".$_SESSION['lang']['notransaksi']."</td>
							<td align=center width=20px>".$_SESSION['lang']['tanggal']."</td>
							<td align=center width=20px>".$_SESSION['lang']['kodesupplier']."</td>
							<td align=center width=20px>".$_SESSION['lang']['noakun']."</td>
							<td align=center width=20px>".$_SESSION['lang']['nilai']."</td>
							<td align=center width=20px>".$_SESSION['lang']['noreferensi']."</td>
							</tr>
							</thead>";

                   
					
                    # data
                    $str = "select * from " . $dbname . ".keu_notadebet_vw where tipeinvoice=''";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
					$nmkode=$nmtantum=$nama=$nmalamat=$no='';
                    while($bar=$res->fetch()){
						
                        $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$bar['noakun_detail']."'";
						// echo $str1;
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $noaruskasdt=$bar1['noaruskas'];

                        $str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $keterangandt=$bar1['id_ket'];

                        $whrorg2="kodeorganisasi='".$bar['kodeorg']."'";
                        $nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg2);

                        #= cek sudah ada data / belum
                        $strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where nodok='".$bar['noreferensi_transaksi']."' and keterangan1='".$bar['notransaksi']."'";
                        $resk=$owlPDO->query($strk)or die(print " Gagal: ".PDOException::getMessage());
                        $resk->setFetchMode(PDO::FETCH_ASSOC);
                        $bark=$resk->fetch();
                        $jumdata=$bark['jumlah'];
                       
							$no++;
                            $data.= "<tr  class=rowcontent style='cursor:pointer' 
									onclick=\"getdatadt('".$bar['notadebet']."','',
													'".$bar['noakun']."','".$bar['nilai_detail']."',
													'".$noaruskasdt."','".$keterangandt."',
													'','".$bar['kodesupplier']."','".$bar['keterangan']."');\">";
                            $data.= "<td align=center title='add detail'>".$no."</td>";
                            $data.= "<td align=center title='add detail'>".$bar['notadebet']."</td>";
                            $data.= "<td align=center title='add detail'>".$bar['tanggal']."</td>";
                            $data.= "<td align=center title='add detail'>".$bar['kodesupplier']."</td>";
                            $data.= "<td align=center title='add detail'>".$bar['noakun']."</td>";
                            $data.= "<td align=center title='add detail'>".number_format($bar['nilai_detail'])."</td>";
                            $data.= "<td align=center title='add detail'>".$bar['noreferensi_transaksi']."</td>";
                            
                            $data.= "</tr>";
                      
                        
                    }
					 $data.= "</table>";
				break;
				
				
                case'grl':
					if($param['tipetransaksi']=='M'){
                        exit("Warning : Tipe transaksi yang diperbolehkan hanya keluar");
                    }
                    if($param['notran']!=''){
                        $where.=" and notransaksi like '%".$param['notran']."%'";
                    }
                    
                    $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
                    $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
                    $data.="<thead><tr class=rowheader>
							<td align=center rowspan=2 width=20px>No</td>
							<td align=center rowspan=2>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center rowspan=2 width=50px>" . $_SESSION['lang']['jenis'] . "</td>
							<td align=center rowspan=2 width=150px>" . $_SESSION['lang']['nama'] . "</td>
							<td align=center colspan=1>SPPT/SHM</td>
							<td align=center rowspan=2>" . $_SESSION['lang']['lokasi'] . "</td>
							<td align=center colspan=2>" . $_SESSION['lang']['luas'] . "</td>
							<td align=center colspan=2 width=50px>" . $_SESSION['lang']['harga'] . "</td>
							<td align=center colspan=4 width=50px>" . $_SESSION['lang']['rupiah'] . "</td>
						</tr>
						<tr>
							<td align=center>No</td>
							<td hidden align=center width=20px>Jlh</td>
							<td align=center width=20px>".$_SESSION['lang']['lahan']."</td>
							<td align=center width=20px>TanTum</td>
							<td align=center width=50px>Lahan/Ha<br>Rp</td>
							<td align=center width=50px>TanTum/Ha<br>Rp</td>
							<td align=center width=50px>Pembayaran<br>Lahan Rp</td>
							<td align=center width=50px>Pembayaran<br>TanTum Rp</td>
							<td align=center width=50px>Biaya<br>Pembuatan Surat Rp</td>
							<td align=center width=50px>Total di Bayar</td>
							</tr>
							</thead>";

                    $whrbansos='';
                    if ($_SESSION['empl']['tipelokasitugas']!='HOLDING') {
                        $whrbansos=" and kodeorg='".$param['kodeorg']."'";
                    }
					
                    # data
                    $str = "select * from " . $dbname . ".lgl_pembebasanlahan where 1=1 ".$where." ".$whrbansos." and statuspersetujuan='1' and ajukanbayar='1' order by notransaksi, nourut";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
					$nmkode=$nmtantum=$nama=$nmalamat=$no='';
                    while($bar=$res->fetch()){
						
						#jika jenis GRLTT ambil akun dari parameter
                        $sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='kasgrltt'";
                        $rappl = fetchData($sappl);
                        if(count($rappl)==0){
							exit("Warning : No akun untuk GRTT belum ada, silahkan tambah terlebih dahulu melalui menu :\nSetup - Parameter Aplikasi\nKode Aplikasi = KB\nKode Parameter = kasgrltt\nNilai = Isikan No Akun");
						}
						$noakunbansos=$rappl[0]['nilai'];
						
                        $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$noakunbansos."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $noaruskasdt=$bar1['noaruskas'];

                        $str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $keterangandt=$bar1['id_ket'];

                        $whrorg2="kodeorganisasi='".$bar['kodeorg']."'";
                        $nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg2);

                        #= cek sudah ada data / belum
                        $strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan3='".$bar['nourut']."' and nodok='".$bar['notransaksi']."' and keterangan1='".$bar['nosppt']."'";
                        $resk=$owlPDO->query($strk)or die(print " Gagal: ".PDOException::getMessage());
                        $resk->setFetchMode(PDO::FETCH_ASSOC);
                        $bark=$resk->fetch();
                        $jumdata=$bark['jumlah'];
                        if($jumdata<1){
							$nmkode=makeOption($dbname,'lgl_kodepemby','kode,nama',"kode='".$bar['nama']."'");
							if($bar['jenis']=='GRLTT'||$bar['jenis']=='SHM'){
								$strtantum = " select  b.padid,b.nama from ".$dbname.".pad_lahan a 
											left join ".$dbname.".pad_5masyarakat b on a.pemilik=b.padid 
											where idlahan='".$bar['nama']."'";
								$restantum = $owlPDO->query($strtantum) or die(print " Gagal: " . PDOException::getMessage());
								$restantum->setFetchMode(PDO::FETCH_ASSOC);
								$bartantum = $restantum->fetch();
								$nama=$bartantum['nama'];
								$idnama=$bartantum['padid'];
							}else{
								$nama=$nmkode[$bar['nama']];
							}
							$no++;
                            $data.= "<tr  class=rowcontent style='cursor:pointer'>";
                            $data.= "<td align=center title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>".$no."</td>";
                            $data.= "<td title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>".$bar['notransaksi']."</td>";
							$data.="<td align=left>" . strtoupper($bar['jenis']) . "</td>";
							$data.="<td align=left style=cursor:pointer onclick=getdaftarmasy('".$bar['nama']."',event)><font color=blue>" . $nama. "</font></td>";
							$data.="<td align=center style=cursor:pointer onclick=getstatuslahan('".$bar['nama']."',event)><font color=blue>" . $bar['nosppt'] . "</font></td>";
							$data.="<td hidden align=center title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>" . $bar['jlhsppt'] . "</td>";
							$data.="<td align=left title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>" . $bar['lokasi'] . "</td>";
							$data.="<td align=right title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>" . hidezerodecimal($bar['luaslahan'],2) . "</td>";
							$data.="<td align=right title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>" . hidezerodecimal($bar['luastantum'],2) . "</td>";
							$data.="<td align=right title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>" . hidezerodecimal($bar['harga'],2) . "</td>";
							$data.="<td align=right title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>" . hidezerodecimal($bar['hargatantum'],2) . "</td>";
							$data.="<td align=right title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>" . hidezerodecimal($bar['rupiah'],2) . "</td>";
							$data.="<td align=right title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>".hidezerodecimal($bar['rupiahtantum'],2)."</td>";
							$data.="<td align=right title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>" . hidezerodecimal($bar['rpsppt'],2) . "</td>";
							$data.="<td align=right title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['totalrp']."','".$noaruskasdt."','".$keterangandt."','".$bar['nourut']."')>" . hidezerodecimal($bar['totalrp'],2) . "</td>";
							
							
                            $data.= "</tr>";
                        }
                        
                    }
				break;
                case'bansos':
                    if($param['tipetransaksi']=='M'){
                        exit("Warning : Tipe transaksi yang diperbolehkan hanya keluar");
                    }
                    if($param['notran']!=''){
                        $where.=" and notransaksi like '%".$param['notran']."%'";
                    }

                    if ($param['bulan']=='' || $param['tahun']=='') {
                        exit('warning : Bulan dan tahun wajib diisi.');
                    }
                    
                    $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
                    $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
                    $data.="<thead>";
                    $data.="<tr align=center>";
                    $data.="<td>".$_SESSION['lang']['notransaksi']."</td>";
                    $data.="<td>".$_SESSION['lang']['kodeorg']."</td>";
                    $data.="<td>".$_SESSION['lang']['kategori']."</td>";
                    $data.="<td>Nama Pemesan</td>";
                    $data.="<td>Lokasi Pemesan</td>";
                    $data.="<td>".$_SESSION['lang']['deskripsi']."</td>";
                    $data.="<td>".$_SESSION['lang']['jumlah']."</td>";
                    $data.="</tr></thead>";

                    $whrbansos='';
                    //if (substr($param['kodeorg'],2,2)=='HO') {
                    if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
                        #$whrbansos=" and kodeorg='".$param['kodeorg']."'";
                    }else{
                        $whrbansos=" and lokasipemesan='".$param['kodeorg']."'";
                    }
                    
                    #= data
                    $str="select sum(rupiah) as rupiah,notransaksi,kodeorg,kategori,namapemesan,lokasipemesan,deskripsi from ".$dbname.".lgl_bansos where 1=1 ".$where." and statuspersetujuan=1 ".$whrbansos." and jenis='BANSOS' group by notransaksi,kodeorg";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while($bar=$res->fetch()){

                        ##$sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='kasbansos'";
                        $sappl="select akun as noakun from ".$dbname.".lgl_kategoribansos where kode='".$bar['kategori']."'";
                        $rappl = fetchData($sappl);
                        $noakunbansos=$rappl[0]['noakun'];
                        
                        $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$noakunbansos."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $noaruskasdt=$bar1['noaruskas'];

                        $str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $keterangandt=$bar1['id_ket'];

                        $whrorg2="kodeorganisasi='".$bar['kodeorg']."'";
                        $nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg2);

                        $whrlokasi="kodeorganisasi='".$bar['lokasipemesan']."'";
                        $nmlokasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrlokasi);
                        
                        #= cek sudah ada data / belum
                        $strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan1='".$bar['notransaksi']."' ";
                        $resk=$owlPDO->query($strk)or die(print " Gagal: ".PDOException::getMessage());
                        $resk->setFetchMode(PDO::FETCH_ASSOC);
                        $bark=$resk->fetch();
                        $jumdata=$bark['jumlah'];
                        if($jumdata<1){
                            $data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' 
							onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['rupiah']."','".$noaruskasdt."','".$keterangandt."')>";
                            $data.= "<td>".$bar['notransaksi']."</td>";
                            $data.= "<td>".$nmorg[$bar['kodeorg']]."</td>";
                            $data.= "<td>".$bar['kategori']."</td>";
                            $data.= "<td>".$bar['namapemesan']."</td>";
                            $data.= "<td>".$nmlokasi[$bar['lokasipemesan']]."</td>";
                            $data.= "<td>".$bar['deskripsi']."</td>";
                            $data.= "<td align='right'>".number_format($bar['rupiah'])."</td>";
                            $data.= "</tr>";
                        }
                        
                    }
                break;

                case'pp':
                    if($param['tipetransaksi']=='M'){
                        exit("Warning : Tipe transaksi yang diperbolehkan hanya keluar");
                    }
                    if($param['notran']!=''){
                        $where.=" and notransaksi like '%".$param['notran']."%'";
                    }

                    if ($param['bulan']=='' || $param['tahun']=='') {
                        exit('warning : Bulan dan tahun wajib diisi.');
                    }
                    
                    $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
                    $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
                    $data.="<thead>";
                    $data.="<tr align=center>";
                    $data.="<td>".$_SESSION['lang']['notransaksi']."</td>";
                    $data.="<td>".$_SESSION['lang']['kodeorg']."</td>";
                    $data.="<td>".$_SESSION['lang']['kategori']."</td>";
                    $data.="<td>Nama Pemesan</td>";
                    $data.="<td>Lokasi Pemesan</td>";
                    $data.="<td>".$_SESSION['lang']['deskripsi']."</td>";
                    $data.="<td>".$_SESSION['lang']['jumlah']."</td>";
                    $data.="</tr></thead>";

                    $whrbansos='';
                    //if (substr($param['kodeorg'],2,2)=='HO') {
                    if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
                        #$whrbansos=" and kodeorg='".$param['kodeorg']."'";
                    }else{
                        $whrbansos=" and lokasipemesan='".$param['kodeorg']."'";
                    }
					
                    #= data
                    $str="select sum(rupiah) as rupiah,notransaksi,kodeorg,kategori,namapemesan,lokasipemesan,deskripsi from ".$dbname.".lgl_bansos where 1=1 ".$where." and statuspersetujuan=1 ".$whrbansos." and jenis='PP' group by notransaksi,kodeorg";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while($bar=$res->fetch()){

                        ##$sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='kasbansos'";
                        $sappl="select akun as noakun from ".$dbname.".lgl_kategoribansos where kode='".$bar['kategori']."'";
                        $rappl = fetchData($sappl);
                        $noakunbansos=$rappl[0]['noakun'];
                        
                        $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$noakunbansos."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $noaruskasdt=$bar1['noaruskas'];

                        $str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $keterangandt=$bar1['id_ket'];

                        $whrorg2="kodeorganisasi='".$bar['kodeorg']."'";
                        $nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg2);

                        $whrlokasi="kodeorganisasi='".$bar['lokasipemesan']."'";
                        $nmlokasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrlokasi);
                        
                        #= cek sudah ada data / belum
                        $strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan1='".$bar['notransaksi']."' ";
                        $resk=$owlPDO->query($strk)or die(print " Gagal: ".PDOException::getMessage());
                        $resk->setFetchMode(PDO::FETCH_ASSOC);
                        $bark=$resk->fetch();
                        $jumdata=$bark['jumlah'];
                        if($jumdata<1){
                            $data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakunbansos."','".$bar['rupiah']."','".$noaruskasdt."','".$keterangandt."')>";
                            $data.= "<td>".$bar['notransaksi']."</td>";
                            $data.= "<td>".$nmorg[$bar['kodeorg']]."</td>";
                            $data.= "<td>".$bar['kategori']."</td>";
                            $data.= "<td>".$bar['namapemesan']."</td>";
                            $data.= "<td>".$nmlokasi[$bar['lokasipemesan']]."</td>";
                            $data.= "<td>".$bar['deskripsi']."</td>";
                            $data.= "<td align='right'>".number_format($bar['rupiah'])."</td>";
                            $data.= "</tr>";
                        }
                        
                    }
                break;
				
				case'tax':
					if($param['tipetransaksi']=='M'){
						exit("Warning:Tipe transaksi yang diperbolehkan hanya keluar");
					}
					
					if($param['notran']!=''){
						$where.=" and no_buktipotong like '%".$param['notran']."%'";
					}
					
					$data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
					$data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
					$data.="<thead>";
					$data.="<tr align=center>";
					$data.="<td>No Bukti<br>Potong</td>";
					$data.="<td>Periode</td>";
					$data.="<td>No<br>Akun</td>";
					$data.="<td>".$_SESSION['lang']['supplier']."</td>";
					$data.="<td>".$_SESSION['lang']['jenis']."</td>";
					$data.="<td>".$_SESSION['lang']['jumlah']."</td>";
					$data.="</tr></thead>";
				
					#= data
					$str="select * from ".$dbname.".tax_buktipotongpajak where 1=1 ".$where." and posting=1 and kodeorg='".$param['kodeorg']."'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						
						$str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$bar['noakun']."'";
						$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
						$res1->setFetchMode(PDO::FETCH_ASSOC);
						$bar1=$res1->fetch();
						$noaruskasdt=$bar1['noaruskas'];

						$str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt."'";
						$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
						$res1->setFetchMode(PDO::FETCH_ASSOC);
						$bar1=$res1->fetch();
						$keterangandt=$bar1['id_ket'];
						
						#= cek sudah ada data / belum
						$strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where 
								(keterangan1='".$bar['no_buktipotong']."' or nodok='".$bar['no_buktipotong']."') ";
						$resk=$owlPDO->query($strk)or die(print " Gagal: ".PDOException::getMessage());
						$resk->setFetchMode(PDO::FETCH_ASSOC);
						$bark=$resk->fetch();
							$jumdata=$bark['jumlah'];
						if($jumdata<1){
							$data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getdatadt('".$bar['no_buktipotong']."','','".$bar['noakun']."','".$bar['nilai']."','".$noaruskasdt."','".$keterangandt."')>";
							$data.= "<td>".$bar['no_buktipotong']."</td>";
							$data.= "<td>".$bar['periode']."</td>";
							$data.= "<td>".$nmakun[$bar['noakun']]."</td>";
							$data.= "<td>".$nmsup[$bar['kodesupplier']]."</td>";
							$data.= "<td>".$nmjenis[$bar['jenis_penghasilan']]."</td>";
							$data.= "<td>".$bar['nilai']."</td>";
							$data.= "</tr>";
						}
						
					}
				break;

                case 'inout':

                    if($param['tipetransaksi']=='M'){
                        exit("Warning:Tipe transaksi yang diperbolehkan hanya keluar");
                    }
                    
                    if($param['notran']!=''){
                        $where.=" and notransaksi like '%".$param['notran']."%'";
                    }
                    
                    $arrIsiDt=array();
                    $sData="select sum(nilai) as nilai,notransaksi,noakun from ".$dbname.".tax_vatin_vatout where status!='3' group by notransaksi,noakun";
                    $rData=fetchData($sData);
                    foreach ($rData as $key => $val) {
                        $arrIsiDt[$val['notransaksi'].$val['noakun']]=$val['nilai'];
                    }

                    $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
                    $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
                    $data.="<thead>";
                    $data.="<tr align=center>";
                    $data.="<td>".$_SESSION['lang']['notransaksi']."</td>";
                    $data.="<td>".$_SESSION['lang']['unit']."</td>";
                    $data.="<td>".$_SESSION['lang']['jumlah']."</td>";
                    $data.="</tr></thead>";
                
                    #= data
                    $str="select distinct notransaksi,periode,unit from ".$dbname.".tax_vatin_vatout where 1=1 ".$where." and posting=1 and unit='".$param['kodeorg']."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while($bar=$res->fetch()){

                        $sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='VIVO'";
                        $rappl = fetchData($sappl);
                        $noakunvv=$rappl[0]['nilai'];
                        $noakunvv=explode(',', $noakunvv);

                        $noakunIn=$noakunvv[0];
                        $noakunOut=$noakunvv[1];
                        $kodejurnal="VIVO";
                        $selisih=$arrIsiDt[$bar['notransaksi'].$noakunOut]-$arrIsiDt[$bar['notransaksi'].$noakunIn];
                        //$selisih=-($selisih);
                        $whrorg2="kodeorganisasi='".$bar['unit']."'";
                        $optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg2);

                        //get noakun bank
                        $str1="select sampaikredit from ".$dbname.".keu_5parameterjurnal where jurnalid='".$kodejurnal."'";
                        $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
                        $qtr->setFetchMode(PDO::FETCH_ASSOC);
                        $rtr=$qtr->fetch();
                        $noakun=$rtr['sampaikredit'];
                        
                        $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$noakun."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $noaruskasdt=$bar1['noaruskas'];

                        $str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $keterangandt=$bar1['id_ket'];
                        
                        #= cek sudah ada data / belum
                        $strk="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where (keterangan1='".$bar['notransaksi']."' or nodok='".$bar['notransaksi']."') ";
                        $resk=$owlPDO->query($strk)or die(print " Gagal: ".PDOException::getMessage());
                        $resk->setFetchMode(PDO::FETCH_ASSOC);
                        $bark=$resk->fetch();
                        $jumdata=$bark['jumlah'];

                        if($jumdata<1){
                            $data.= "<tr class=rowcontent style='cursor:pointer' title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$noakun."','".$selisih."','".$noaruskasdt."','".$keterangandt."')>";
                            $data.= "<td>".$bar['notransaksi']."</td>";
                            $data.= "<td>".$optunit[$bar['unit']]."</td>";
                            $data.= "<td>".number_format($selisih)."</td>";
                            $data.= "</tr>";
                        }
                    }

                break;

                case 'UM':

                //    // exit("Error ".$sql);
                //    echo "<pre>";
                //    print_r($query);
                $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
                $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
                $data.="<thead>";
                $data.="<tr align=center>";
                $data.="<td>".$_SESSION['lang']['notransaksi']."</td>";
                $data.="<td>".$_SESSION['lang']['unit']."</td>";
                $data.="<td>".$_SESSION['lang']['noreferensi']."</td>";
                $data.="<td>".$_SESSION['lang']['jumlah']."</td>";
                $data.="<td>".$_SESSION['lang']['keterangan']."</td>";
                $data.="</tr></thead>";

                if($param['tipetransaksi']=='M'){
                    exit("Warning:Tipe transaksi yang diperbolehkan hanya keluar");
                }
                
                if($param['notran']!=''){
                    $where.=" and notransaksi like '%".$param['notran']."%'";
                }

                $whrorg2="kodeorganisasi='".$bar['unit']."'";
                $optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg2);

                $where .= "and notransaksi not in(select keterangan1 from ".$dbname.".keu_kasbankdt)";

                $sql = "select a.notransaksi,a.unit,a.nilaiuangmuka,a.no_transaksi_ref,a.keterangan,a.id_master_uangmuka,a.penerima_id,b.nik from ".$dbname.".keu_uangmuka a 
                        left join ".$dbname.".datakaryawan b on (a.penerima_id=b.karyawanid) where posting='1' ".$where." and unit='".$param['kodeorg']."'   ";
               //exit("Error ".$sql);
                
                $query = fetchData($sql);
                foreach ($query as $key=>$bar){

                $sql = "select a.noakun,b.noaruskas from ".$dbname.".keu_5jenisuangmuka a inner join keu_5aruskas_detail b on (a.noakun=b.noakun)
                        inner join keu_5aruskas c on(b.noaruskas=c.noaruskas) where a.kode='".$bar['id_master_uangmuka']."' and c.tipetransaksi='K'";
                     //   exit("Error ".$sql);
                $noakun = fetchData($sql);
                $noakundt = $noakun[0]['noakun'];
                $noaruskas = $noakun[0]['noaruskas'];
                $penerima = $bar['penerima_id'];

                    $data.= "<tr class=rowcontent style='cursor:pointer' title='add detail' 
                            onclick=getdatadt('".$bar['notransaksi']."','".$bar['unit']."','".$noakundt."','".$bar['nilaiuangmuka']."','".$noaruskas."','".$keterangandt."','','".$penerima."')
                                                                                            >";
                    $data.= "<td>".$bar['notransaksi']."</td>";
                    $data.= "<td>".$bar['unit']."</td>";
                    $data.= "<td>".$bar['no_transaksi_ref']."</td>";
                    $data.= "<td align=right>".number_format($bar['nilaiuangmuka'])."</td>";
                    $data.= "<td>".$bar['keterangan']."</td>";
                    $data.= "</tr>";
                }

                break;
                    
                default:
                    $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
                    $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
                    $data.="<thead>";
                    $data.="<tr align=center>";
                    $data.="<td>".$_SESSION['lang']['notransaksi']."</td>";
                    $data.="<td>".$_SESSION['lang']['tanggal']."</td>";
                    $data.="<td>".$_SESSION['lang']['unit']." Penerima</td>";
                    $data.="<td>".$_SESSION['lang']['status']."</td>";
                    $data.="<td>".$_SESSION['lang']['total']."</td>";
                    $data.="</tr></thead>";
                
                    if($param['notran']!=''){
                        $where.=" and notransaksi like '%".$param['notran']."%'";
                    }

                    #data
                    $str="select * from ".$dbname.".keu_dividen where 1=1 ".$where." and tipetransaksi='".$param['jenisdata']."' and statusaktif=1 and (unit1='".$param['kodeorg']."' or unit2='".$param['kodeorg']."') ";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while($bar=$res->fetch()){

                        if ($bar['tipetransaksi']=='Modal') {
                            $jurnalid='MOD';
                        }else{
                            $jurnalid='DIV';
                        }

                        if ($param['tipetransaksi']=='K') {
                            
                            $fieldnoakun=' noakunkredit as noakun ';

                            if ($bar['status']=='Issuer') {
                                $unit=$bar['unit1'];
                                $statusunit=$bar['statusunit1'];
                            }else{
                                $unit=$bar['unit2'];
                                $statusunit=$bar['statusunit2'];
                            }
                        }

                        if ($param['tipetransaksi']=='M') {

                            $fieldnoakun=' sampaidebet as noakun ';

                            if ($bar['status']=='Receiver') {
                                $unit=$bar['unit1'];
                                $statusunit=$bar['statusunit1'];
                            }else{
                                $unit=$bar['unit2'];
                                $statusunit=$bar['statusunit2'];
                            }
                        }

                        if ($unit!=$param['kodeorg']) {
                            continue;
                        }

                        if ($statusunit==1) {
                            continue;
                        }

                        $whrorg2="kodeorganisasi='".$unit."'";
                        $optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrorg2);

                        $strak="select ".$fieldnoakun." from ".$dbname.".keu_5parameterjurnal where jurnalid='".$jurnalid."'";
                        $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
                        $resak->setFetchMode(PDO::FETCH_ASSOC);
                        $barak=$resak->fetch();

                        if (($bar['status']=='Receiver') && ($bar['transaksi']=='Eksternal')) {
                            $barak['noakun']=$bar['akunpiutang'];
                        }

                        $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$barak['noakun']."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $noaruskasdt=$bar1['noaruskas'];

                        if ($param['tipetransaksi']=='K') {
                            $sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKDIV'";
                            $rappl = fetchData($sappl);
                            $noarus=$rappl[0]['nilai'];
                            $noarus=explode(',', $noarus);
                            
                            $whrorg2="kodeorganisasi='".$unit."'";
                            $optPt=makeOption($dbname,"organisasi","kodeorganisasi,induk",$whrorg2);
                            switch ($optPt[$unit]) {
                                case 'KAL':$noaruskasdt=$noarus[0];break;
                                case 'TML':$noaruskasdt=$noarus[1];break;
                                case 'ALM':$noaruskasdt=$noarus[2];break;
                            }
                        }

                        $str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt."'";
                        $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1=$res1->fetch();
                        $keterangandt=$bar1['id_ket'];

                        $data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getdatadt('".$bar['notransaksi']."','','".$barak['noakun']."','".$bar['nilai']."','".$noaruskasdt."','".$keterangandt."')>";
                        $data.= "<td>".$bar['notransaksi']."</td>";
                        $data.= "<td>".tanggalnormal($bar['tanggal'])."</td>";
                        $data.= "<td>".$optunit[$unit]."</td>";
                        $data.= "<td>".$bar['status']."</td>";
                        $data.= "<td align=right>".number_format($bar['nilai'])."</td>";
                        $data.= "</tr>";
                    }
                    $data.= "</table></fieldset>";
                break;
				
			}
			

            echo $data;
        break;
		

        case'adddatadt':
		
		//notadebetind
			
            $param = $_POST;
			// print_r($param);
			// exit("Error:A");
			$fieldPenerima = 'kodesupplier';
			if($param['jenisdata']=='grl'){
				$nmsppt=makeOption($dbname,'lgl_pembebasanlahan','nourut,nosppt',"notransaksi='".$param['notran']."' and nourut='".$param['nourut']."'");
				$ket1=$nmsppt[$param['nourut']];
				$ket3=$param['nourut'];
			}else{
				$ket3="";
				$ket1=$param['notran'];
            }
            if($param['jenisdata']=="UM"){
                $sql = "select keterangan,no_transaksi_ref,id_master_uangmuka from ".$dbname.".keu_uangmuka where notransaksi='".$param['notran']."' ";
                //exit("Error ".$sql);
                $query = fetchData($sql);

                $ket3=$query[0]['keterangan'];
                //$ket1="";
                $param['notran']=$query[0]['no_transaksi_ref'];

                if ($query[0]['id_master_uangmuka']=='PJD'){
                    $fieldPenerima = 'nik';

                }else {
                    $fieldPenerima = 'kodesupplier';
                }
            }
			
			if($param['jenisdata']=="umpjdinas" or $param['jenisdata']=="realpjdinas" or $param['jenisdata']=="batalpjd"){
				$ket3=$param['keterangan'];
				$ket1= $param['notran'];
				$fieldPenerima = 'nik';
			}
			if($param['jenisdata']=="claimpjdinas"){
				$ket3=$param['keterangan'];
				$ket1= $param['notran'];
				$fieldPenerima = 'nik';
				
				if($param['tipetransaksi']=='K'){
					$param['jumlah']=$param['jumlah'];
				}else{
					$param['jumlah']=$param['jumlah']*(-1);
				}
				
				#ambil data um jika ada
				$str="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$param['notran']."' and lainnya='umpjd#".$param['notran']."' and nik='".$param['penerima']."' group by noakun";
				// echo $str;exit("Error:A");
				$res = fetchdata($str);
				$jlhum=0;$info="";
				if(count($res)>0){
					foreach($res as $bar){
						$jlhum+=$bar['jumlah'];
						$noakunum=$bar['noakun'];
						$notranum=$bar['notransaksi'];
						$aruskasum='226004';
						$ketum="Ptj um pjdinas, no:".$notranum;
						$kodeorgum=$bar['kodeorg'];
						if($kodeorgum!=$param['kodeorg']){
							$info.="Pengambilan uang muka PJD berbeda unit\nUnit uang muka :".$kodeorgum."\nUnit pertanggung jawaban :".$param['kodeorg']."\nJumlah uang muka diambil : ".$jlhum."\nNotransaksi uang muka :".$notranum."";
						}else{
							#ini jurnal balasan uang muka
							if($param['tipetransaksi']=='K'){
								$jlhum=$jlhum;
							}else{
								$jlhum=$jlhum*(-1);
							}
							
							// if($param['tipetransaksi']=='K' and abs($jlhum) > abs($param['jumlah'])){
								// exit("Warning : Gunakan tipe transaksi Masuk.");
							// }elseif($param['tipetransaksi']=='M' and abs($param['jumlah']) > abs($jlhum)){
								// exit("Warning : Gunakan tipe transaksi Keluar.");
							// }
							
							
							$str="insert into ".$dbname.".keu_kasbankdt (`notransaksi`, `noakun`, `tipetransaksi`, 
							`tanggal`,`jumlah`, `noakun2a`, `kode`,`keterangan1`,`keterangan2`, `matauang`, `kurs`,
							`kurs2`,`noaruskas`,`kodeorg`,`nodok`,`bulan`,`tahun`,`keterangan3`,".$fieldPenerima.",`lainnya`) 
							values ('".$param['notransaksi']."','".$noakunum."','".$param['tipetransaksi']."',
							'".tanggalsystemn($param['tanggal'])."','".$jlhum*(-1)."','".$param['noakun']."',
							'".$param['kode']."','".$ket1."','".$param['keterangan2']."','".$param['matauang']."',
							'".$param['kurs']."','1','".$aruskasum."','".$param['kodeorg']."','".$param['notran']."',
							'".$param['bulan']."','".$param['tahun']."','DPP','".$param['penerima']."','".$param['lainnya']."')";
							#exit("error".$str);
							try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
						}
					}
				}
			}
			
			
			
			if($param['jenisdata']=="notadebet"){
				$ket3=$param['keterangan'];
				$ket1= $param['notran'];
			}
			
			if($param['jenisdata']=="feepanen"){
				$ket1='';
				$ket3=$param['keterangan'];
			}
            $str="insert into ".$dbname.".keu_kasbankdt (`notransaksi`, `noakun`, `tipetransaksi`, `tanggal`,`jumlah`, 
					`noakun2a`, `kode`,`keterangan1`,`keterangan2`, `matauang`, 
					`kurs`,`kurs2`,`noaruskas`,`kodeorg`,`nodok`,
					`bulan`,`tahun`,`keterangan3`,".$fieldPenerima.",lainnya) 
            values ('".$param['notransaksi']."','".$param['noakundt']."','".$param['tipetransaksi']."','".tanggalsystemn($param['tanggal'])."','".$param['jumlah']."','".$param['noakun']."','".$param['kode']."','".$ket1."','".$param['keterangan2']."','".$param['matauang']."','".$param['kurs']."','1','".$param['noaruskas']."','".$param['kodeorg']."','".$param['notran']."','".$param['bulan']."','".$param['tahun']."','DPP','".$param['penerima']."','".$param['lainnya']."')";
            // exit("Error ".$str);
            try{

                $owlPDO->exec($str);
				
				
				if ($param['jenisdata']=='feepanen') {
					
					#= update data
					
					$dataexpl=explode("#",$param['notran']);
					
					$tipedata=$dataexpl[0];
					$id=$dataexpl[1];
					$per=$dataexpl[2];
					$jenisfee=$dataexpl[3];
					
					$strupdate = "update ".$dbname.".kebun_rekapangkutantbsdtfee set bayar=1 where 
					id='".$id."' and jenisfee='".$jenisfee."' and id='".$id."' and periode='".$per."'";             
                    try {
                        $owlPDO->exec($strupdate);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
					
				}
				
				

                if ($param['jenisdata']=='dividen') {

                    //sum jumlah modal yg telah diambil di kasbank
                    $strak="select sum(jumlah) as jumlahtot from ".$dbname.".keu_kasbankdt where keterangan1='".$param['notran']."' and tipetransaksi='".$param['tipetransaksi']."'";
                    $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
                    $resak->setFetchMode(PDO::FETCH_ASSOC);
                    $barak=$resak->fetch();
                    $jumlahkasbank=$barak['jumlahtot'];

                    //ambil jumlah nilai modal 
                    $strak="select nilai,unit1 from ".$dbname.".keu_dividen where notransaksi='".$param['notran']."'";
                    $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
                    $resak->setFetchMode(PDO::FETCH_ASSOC);
                    $barak=$resak->fetch();
                    $nilaimodal=$barak['nilai'];

                    if ($barak['unit1']==$param['kodeorg']) {
                        $set=" statusunit1=1 ";
                    }else{
                        $set=" statusunit2=1 ";
                    }

                    if ($jumlahkasbank==$nilaimodal) {
                        $strht = "update ".$dbname.".keu_dividen set ".$set." where notransaksi='".$param['notran']."'";             
                        try
                        {
                            $owlPDO->exec($strht);
                        }
                        catch (PDOException $e)
                        {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                }

                if ($param['jenisdata']=='inout') {
                   $strht = "update ".$dbname.".tax_vatin_vatout set status=2 where notransaksi='".$param['notran']."'";             
                    try
                    {
                        $owlPDO->exec($strht);
                    }
                    catch (PDOException $e)
                    {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }

            } catch (PDOException $e)  {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }
		if($info!=''){
			exit("Warning : ".$info);
		}
		
        break;
        /*end of data*/
		
		
		case'addFromInvoice':
			$param = $_POST;
			$sisa = array();
			$noakundt=array();
			$data = array();
			
			// echo"<pre>";			
			// print_r($param);
			// exit("Error:A");
			
			/*
			[notransaksi] => 20210304/SDKM/BK/00001
		[kodeorg] => SDKM
		[noakun] => 1110101
		[tipetransaksi] => K
		[kode] => BK
		[matauang] => IDR
		[kurs] => 1
		[hutangunit] => 0
		[pemilikhutang] => 
		[tipeinv] => false
		[bulan] => 03
		[tahun] => 2021
    [invoice] => Array
        (
            [0] => 20210304023105
        )

    [sisa] => Array
        (
            [0] => 6523457.2
        )

    [tgltk] => Array
        (
            [0] => null
        )

    [kdsup] => Array
        (
            [0] => null
        )

    [noakundet] => Array
        (
            [0] => 2110401
        )
			*/
			$namasupplier='';
			foreach($param['invoice'] as $key=>$row) {
				$sisa[$row] = $param['sisa'][$key];
				// $noakundt[$row] = $param['noakundet'][$key];
				
				
				#= query data
				$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$row."'";
				// exit("error:$str");
				$res=fetchdata($str);
				foreach($res as $bar){
					
					$namasupplier=$nmsup[$bar['kodesupplier']];
					
					$param['hutangunit1']=0;
					$param['pemilikhutang1']='';
                // GRIR 2021 kalo p/pon, ga pake hutang unit
                if(($bar['tipeinvoice']=='p')||($bar['tipeinvoice']=='pon')){

                }else{
					if($param['kodeorg']!=$bar['unit']){
						$param['hutangunit1']=1;
						$param['pemilikhutang1']=$bar['unit'];
					}
                }
					
					#= aruskas ambil dari transaksi dtnya
					#= jika detail ada 2, utamakan selain PPN / PPH
					$str1="select * from ".$dbname.".keu_tagihandt where noinvoice='".$bar['noinvoice']."' and (noakun not like '117%' and noakun not like '213%') ";
					$res1=fetchdata($str1);
					foreach($res1 as $bar1){
						$param['noaruskas']=$bar1['noaruskas'];
					}
				
					#= jika detailnya tidak ada akun selain akun ppn/pph, maka pakai query ke data aruskas
					if($param['noaruskas']==''){
						$str1="select * from ".$dbname.".keu_5aruskas_detail where noakun='".$bar['noakun']."' and noaruskas like '1%' limit 1";
						$res1=fetchdata($str1);
						foreach($res1 as $bar1){
							$param['noaruskas']=$bar1['noaruskas'];
						}
					}
						// exit("Error:".$param['noaruskas']);
					
					
					$strdt="select * from ".$dbname.".keu_tagihandt where noinvoice='".$row."'";
					$resdt=fetchdata($strdt);
					foreach($resdt as $bardt){
						$adakasbank=0;
						$strkas="select count(*) as jumlah from ".$dbname.".keu_kasbankdt where keterangan1='".$row."' and noakun='".$bar['noakun']."' and jumlah='".$bardt['nilai']."' and noaruskas='".$bardt['noaruskas']."'";
						$reskas=fetchdata($strkas);
						$adakasbank = $reskas[0]['jumlah'];
						// exit("error:".$adakasbank._.$strkas);
						if($adakasbank>0){
							continue;
						}
						if(substr($bardt['noakun'],0,5)=='11803' and $bar['tipeinvoice']!='um'){
							continue;
						}
						$keterangan2='';
						$keterangan3='DPP';
						if(substr($bardt['noakun'],0,3)=='117'){
							$keterangan3='PAJAKPPN';
							$keterangan2='PPN';
						}else if( substr($bardt['noakun'],0,3)=='213'){
							$keterangan3='PAJAKPPH';
							$keterangan2='PPH';
						}else{
							$keterangan3='DPP';
							$keterangan2='';
						}							


						
						#= query insert ke DT
						
                        // if(strlen($nmaruskas[$bardt['noaruskas']])>15){
                            // $tulisanaruskas=substr($nmaruskas[$bardt['noaruskas']],0,12).'...';
                        // }else{
                            // $tulisanaruskas=$nmaruskas[$bardt['noaruskas']];
                        // }
						if($bardt['nilai']!=0){
							@$nodt+=1;
							$data[] = array(
								'notransaksi' => $param['notransaksi'],
								'nourut' =>'',
								'noakun' => $bar['noakun'],
								'tipetransaksi' => $param['tipetransaksi'],
								'tanggal' => $bar['tanggal'],
								'jumlah' => $bardt['nilai'],
								'noakun2a' => $param['noakun'],
								'kode' => $param['kode'],
								'keterangan1' => $bar['noinvoice'],             
								// 'keterangan2' => $tulisanaruskas.' a/n: '.$nmsup[$bar['kodesupplier']].', PO/SO/Dok: '.$bar['nopo'].', Inv: '.$bar['noinvoice'].', Trx: '.$bardt['notransaksi'].' ['.$nodt.']',   
								'keterangan2' =>$nodt.'. '.$keterangan2.' '.$bar['keterangan'].', Assignment '.$nmsup[$bar['kodesupplier']].' No. Dok : '.$bar['nopo'],		
								'matauang' => $param['matauang'],
								'kurs' => $param['kurs'],
								'kurs2' => 1,
								'noaruskas' =>$bardt['noaruskas'],
								'kodeorg' => $param['kodeorg'],
								'kodekegiatan' => '',
								'kodeasset' => '',
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $bar['kodesupplier'],
								'kodevhc' => '',
								'orgalokasi' => '',
								'nodok' => $bar['nopo'],
								'hutangunit1' => $param['hutangunit1'],
								'kodesegment' => '',
								'tahun' =>$param['tahun'],
								'bulan' =>$param['bulan'], 
								'keterangan3' =>$keterangan3,
								'pemilikhutang1' => $param['pemilikhutang1'],
                                'lainnya' => ''
							);
						}
                            // 'keterangan3' => ' '.$nodt.'. Pembayaran '.$nmaruskas[$bardt['noaruskas']].' a/n '.$nmsup[$bar['kodesupplier']].';No. Invoice Internal:'.$bar['noinvoice'].';Supplier/Kontraktor:'.$bar['noinvoicesupplier'].';No. Dokumen:'.$bar['nopo'],
						
					}
					
				}

			}
			
			// print_r($data);
			// exit("Error:$query");
			foreach($data as $row) {
				$query = insertQuery($dbname,'keu_kasbankdt',$row);
				
				try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>".$query; die(); }
			}
			
			// print_r($param['invoice']);
			// // print_r($noakundt);
			// exit("Error:A");
			
			/*
			foreach($param['invoice'] as $key=>$row) {
				$pathtagihan   = "filegis/";
				// $pathkb   = "fileupload/keu_kasbankx/";
				$str = "select * from ".$dbname.".listfileupload where notransaksi='".$row."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$strins = "insert into ".$dbname.".listfileupload values ('','".$param['notransaksi']."','EAP_".$bar['namafile']."','".$bar['formaticon']."','EAP','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
					try{
						$owlPDO->exec($strins);
						#= move file
						$source=$pathtagihan.$bar['namafile'];
						$destination=$path.'EAP_'.$bar['namafile'];;
						if( !copy($source, $destination) ) { 
							exit("Warningsistem:gagal upload otomatis dari AP");
						} 
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}

			}
			*/
			
			
			#= ganti keteranganht 
			#= keterangan bayar / masuk ambil dari kodesupplier
			#= berdasarkan CRF dari RO, hasil kunjungan awal maret 2021
			#= point 14
			
			
			$str = "select bayarkepada from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";      
			$res=fetchdata($str);
			foreach($res as $bar){
				$bayarkepada=$bar['bayarkepada'];
			}

			if($bayarkepada=='' || $bayarkepada=='-'){
				$str = "update ".$dbname.".keu_kasbankht set bayarkepada='".$namasupplier."' where notransaksi='".$param['notransaksi']."'";             
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
			
			echo $namasupplier;
			// exit("Error:".$namasupplier.'----'.$bayarkepada);
			
			
		break;
		
		
		
	
        case 'addFromInvoiceLAMA':
			$optJenis=makeOption($dbname,'keu_5jenistagihan','kode,source');
			$param = $_POST;
			$sisa = array();
			$noakundt=array();
					
			foreach($param['invoice'] as $key=>$row) {
				$sisa[$row] = $param['sisa'][$key];
				$noakundt[$row] = $param['noakundet'][$key];
			}
			
			
			// print_r($sisa);exit("Error:A");

			if ($param['bulan']=='' || $param['tahun']=='') {
				exit('warning : Bulan dan tahun wajib diisi.');
			}

			$optAkun=makeOption($dbname,'log_5klsupplier','tipe,noakun');
			$listInvoice = $_POST['invoice'];

			$invStr = '';
			foreach($listInvoice as $inv) {
				if(!empty($invStr)) {
					$invStr .= ',';
				}
				$invStr .= "'".$inv."'";
			}
			 
			// Data Header
			if(!empty($invStr)) {
				$qHead = selectQuery($dbname,'keu_tagihanht','*',"noinvoice in (".$invStr.")");
				$resHead = fetchData($qHead);
			} else {
				$resHead = array();
				$resSupp = array();
			}
			
			$data = array();
			$optHead = array();
			
			#= cek apakah data tersebut ada detailnya atau tidak
			
			$str="select noinvoice,count(*) as jumlah from ".$dbname.".keu_tagihandt where noinvoice in (".$invStr.") group by noinvoice";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$jinvdt[$bar['noinvoice']]=$bar['jumlah'];
			}
			$datacek='';
			foreach($resHead as $row) {
				if($jinvdt[$row['noinvoice']]=='0'){
					$datacek.=$row['noinvoice']."\n";
				}
			}
			if($datacek!=''){
				echo"Nomor AP ini belum ada detailnya, cek kembali data AP-nya\n";
				echo $datacek;
				exit("Warning");
			}
			#= tutup cek detail
			
			
			// From Header
			$noInv = "";
			// $totRp=$countdt=$countdtbrg=$nilaippnperbrg=$nilainotaperbrg=$nilainota=0;
			foreach($resHead as $row) {
				
				$totRp=$countdt=$countdtbrg=$nilaippnperbrg=$nilainotaperbrg=$nilainota=0;
				
				$jenisInvoice = $row['tipeinvoice'];
				#cek jenis tagihan
				$sJnsTag="select * from ".$dbname.".keu_5jenistagihan where kode='".$row['tipeinvoice']."'";
				$rJnsTag=fetchData($sJnsTag);

				// Data Detail
				$resDet = array();
				// $qDet = selectQuery($dbname,'keu_tagihandt','*',"noinvoice='".$row['noinvoice']."'");
				$qDet = "select sum(nilai) as nilai,noaruskas,noakun from ".$dbname.".keu_tagihandt where 
						noinvoice='".$row['noinvoice']."' group by noaruskas";
				//indrauji
				$resDet = fetchData($qDet); 
				
					


				$setelum=0;
				foreach($resDet as $rowDt=>$dtakun){
					if (substr($dtakun['noakun'],0,3)!='117' && substr($dtakun['noakun'],0,3)!='213'){
						if (substr($dtakun['noakun'],0,5)=='11802' && $dtakun['nilai']<0) { 
							$setelum=$dtakun['nilai'];
							continue;
						}
						$countdtbrg+=1;
					}
					
					if($jenisInvoice=='ffb'){
						if($dtakun['notransaksi']!=''){
							$row['nopo']=$dtakun['notransaksi'];
						}
					}
				}
				// exit("Error:".$row['nopo']);
				
				
				if ($row['tipeinvoice']!='um'){
					foreach($resDet as $rowDt=>$dtakun){
						if ($dtakun['noakun']=='1170111'){
							@$nilaippnperbrg+=$dtakun['nilai']/$countdtbrg;
						}
					}
					
				
					
					foreach($resDet as $rowDt=>$dtakun){
						if (substr($dtakun['noakun'],0,3)=='213'){
							@$nilaippnperbrg+=$dtakun['nilai']/$countdtbrg;
						}
					}
					
				}
				
					
							

				##cek apakah tagihan tersebut ada di transaksi nota debet atau tidak
				$strnd="select sum(nilai_detail) as nilainota from ".$dbname.".keu_notadebet_vw where noinvoice_referensi='".$row['noinvoice']."'";
				$resnd=$owlPDO->query($strnd) or die(print " Gagal: ".PDOException::getMessage());
				$resnd->setFetchMode(PDO::FETCH_ASSOC);
				$barnd=$resnd->fetch();
				@$nilainota=floatval($barnd['nilainota']);
				if ($nilainota!=0) {
					if ($countdtbrg>0) {
                        @$nilainotaperbrg=floatval($nilainota/$countdtbrg);
                    }else{
                        @$nilainotaperbrg=$nilainota;
                    }
				}

				##akun header dimasukkan ke variabel
				$akunHead=$row['noakun'];
				##cek apakah kodeorg sumber hutang dengan yang bayar sama atau tidak
				if ($row['tipeinvoice']!='um'){
					/*
					switch ($optJenis[$jenisInvoice]) {
						case 'ffb':
							$skode="select distinct unit as unit from ".$dbname.".kebun_tbskud where notransaksi='".$row['nopo']."'";
						break;
						case 'bfb':
							$skode="select distinct kodeunit as unit from ".$dbname.".keu_persediaantbs_vw where notransaksi='".$row['nopo']."'";
						break;
						case 'po':
							$skode="select distinct left(kodegudang,4) as unit from ".$dbname.".log_transaksi_vw  
									where nopo='".$row['nopo']."' and notransaksi='".$row['notransaksi_gr']."' ";
						break;
						case'kt':
							$skode="select distinct kodeorg as unit from ".$dbname.".log_spkht where notransaksi='".$row['nopo']."'";
						break;
						case'poa':
							$skode="select unit from ".$dbname.".keu_tagihanht where noinvoice='".$row['noinvoice']."'";
						break;
						default:
							$skode="select unit from ".$dbname.".keu_tagihanht where noinvoice='".$row['noinvoice']."'";
								// exit("Error:$skode");
						break;
					}
					*/
					$skode="select unit from ".$dbname.".keu_tagihanht where noinvoice='".$row['noinvoice']."'";
					$rkode=fetchData($skode);
					$param['hutangunit']=0;
					
					
					$unitSumber=$rkode[0];
					$optPt=makeOption($dbname,"organisasi","kodeorganisasi,induk","kodeorganisasi='".$unitSumber['unit']."'");
					$optPtkasbank=makeOption($dbname,"organisasi","kodeorganisasi,induk","kodeorganisasi='".$param['kodeorg']."'");
					$statCaco='';
					if($optPt[$unitSumber['unit']]!=$optPtkasbank[$param['kodeorg']]){#inter
						$statCaco='inter';
						$sCaco="select akunpiutang from ".$dbname.".keu_5caco where jenis='inter' and kodeorg='".$unitSumber['unit']."'";
					}else if($unitSumber['unit']!=$param['kodeorg']){
						$sCaco="select akunpiutang from ".$dbname.".keu_5caco where jenis='intra' and kodeorg='".$unitSumber['unit']."'";
						$statCaco='intra';
					}
					

					//exit("ERROR:".$sCaco);
					$rCaco = array();
					if($sCaco != ""){
						$rCaco=fetchData($sCaco);
					}

					if(count($rCaco)!=0){
						if ($rCaco[0]['akunpiutang']=='') {
							exit("Warning : Account intraco or interco not available for ".$unitSumber['unit'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
						}
						$param['hutangunit']=1;
					}
				}
				
				
				
				
				##ambil noakun uang muka
				if ($row['tipeinvoice']=='um'){
					$sC="select b.noakun from ".$dbname.".keu_tagihanht a left join ".$dbname.".keu_tagihandt b on a.noinvoice=b.noinvoice where a.noinvoice='".$row['noinvoice']."' and a.tipeinvoice='".$row['tipeinvoice']."' and b.noakun like '11802%'";
					$tC=$owlPDO->query($sC);
					$tC->setFetchMode(PDO::FETCH_ASSOC);
					$rC=$tC->fetch();
					if($rC['noakun']!=''){
						$akunHead=$rC['noakun'];
					}
				}

				##ambil noakun provisi
				if (substr($row['tipeinvoice'],0,2) == 'as' || substr($row['tipeinvoice'],0,2) == 'sw') {
					$sC="select b.noakun from ".$dbname.".keu_tagihanht a left join ".$dbname.".keu_tagihandt b on a.noinvoice=b.noinvoice where a.noinvoice='".$row['noinvoice']."' and a.tipeinvoice='".$row['tipeinvoice']."' and b.noakun like '21699%'";
					$tC=$owlPDO->query($sC);
					$tC->setFetchMode(PDO::FETCH_ASSOC);
					$rC=$tC->fetch();

					if($rC['noakun']!=''){
						$akunHead=$rC['noakun'];
					}
				}
				
				
				// print_r($resDet);exit("Error:");
	
				
				foreach($resDet as $rowDt=>$dtakun){
				
					if($dtakun['noakun']=='1170111'){
						continue;
					}
					
					
					if($jenisInvoice=='p21' || $jenisInvoice=='p22' ||  $jenisInvoice=='p23' ||  jenisInvoice=='p25'){
						 $akunHead=$dtakun['noakun'];
					} else {
						if(substr($dtakun['noakun'],0,3)=='213'){
							continue;
						}
					}
					
					
					// exit("Error:$akunHead");
					
					// $dtakun['keterangan']=array();
					// if (($row['tipeinvoice']=='ffb' && $dtakun['noakun']=='1170111') || ($row['tipeinvoice']=='bfb' && $dtakun['noakun']=='1170111')) {
					if (($row['tipeinvoice']=='ffb' && $dtakun['noakun']!='1170111') || ($row['tipeinvoice']=='bfb' && $dtakun['noakun']!='1170111')) {
					
						$str1="select a.notransaksi as notransaksi,a.noakun as noakunffb, a.noaruskas as aruskasffb, a.keterangan as ketffb from ".$dbname.".keu_tagihandt a 
							   left join ".$dbname.".keu_tagihanht b 
							   on a.noinvoice=b.noinvoice where tipeinvoice='".$row['tipeinvoice']."' and a.noinvoice='".$row['noinvoice']."'
						
							   and (left(a.noakun,1)='6' or a.noakun='2110401')";
							   // exit("Error:$str1");    and a.notransaksi='".$dtakun['notransaksi']."'
						$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
						$res1->setFetchMode(PDO::FETCH_ASSOC);
						$bar1=$res1->fetch();
						$dtakun['noakun']=$bar1['noakunffb'];
						$dtakun['keterangan']=$bar1['ketffb'];
						$dtakun['noaruskas']=$bar1['aruskasffb'];
						$row['nopo']=$bar1['notransaksi'];
					}
					
					// $a.=$row['nopo'].__.$bar1['notransaksi'].__________;
					

				
					// exit("Error:".$dtakun['keterangan']);
					
					if ($row['tipeinvoice']=='ffb' || $row['tipeinvoice']=='bfb') {
						if (substr($dtakun['noakun'],0,3)!='117' && substr($dtakun['noakun'],0,3)!='213'){
							$jumlahinvoice=$dtakun['nilai']+$nilaippnperbrg-$nilainotaperbrg+$setelum;
						}else{
							$jumlahinvoice=$dtakun['nilai'];
						}
						// exit("Error:".$dtakun['nilai']);
					}else{
						if (substr($dtakun['noakun'],0,3)!='117' && substr($dtakun['noakun'],0,3)!='213'){
							
							$jumlahinvoice=$dtakun['nilai']+$nilaippnperbrg-$nilainotaperbrg+$setelum;
							// exit("Error zzz:".$jumlahinvoice._.$dtakun['nilai']._.$nilaippnperbrg._.$nilainotaperbrg);
							
						}else{
							$jumlahinvoice=$dtakun['nilai'];
						}
					}

					if ((substr($dtakun['noakun'],0,5)=='11802' && $dtakun['nilai']<0)) {
					   continue;
					}

					// $sJmlh="select distinct sum(jumlah) as jmlhKas from ".$dbname.".keu_kasbankdt where 
							// keterangan1='".$row['noinvoice']."' and jumlah>0 ";
					$sJmlh="select distinct sum(jumlah) as jmlhKas from ".$dbname.".keu_kasbankdt where 
							keterangan1='".$row['noinvoice']."' and noaruskas='".$dtakun['noaruskas']."' and noakun='".$row['noakun']."' ";
							//indrauji		 $row['noakun'] / $akunHead
							
					$qJmlh=$owlPDO->query($sJmlh);
					$qJmlh->setFetchMode(PDO::FETCH_ASSOC);
					$rJmlh=$qJmlh->fetch();
					$jmlhKas=floatval($rJmlh['jmlhKas']);
					if ($jumlahinvoice>=$jmlhKas) {
					   $jumlahinvoice=$jumlahinvoice-$jmlhKas;
					}
					
					$str1="select * from ".$dbname.".keu_5keterangan where id_ket='".$dtakun['keterangan']."'";
					$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
					$res1->setFetchMode(PDO::FETCH_ASSOC);
					$bar1=$res1->fetch();
				
					// exit("Error:".$jumlahinvoice._.$dtakun['nilai']._.$nilaippnperbrg._.$nilainotaperbrg._.$setelum);
					if ($row['tipeinvoice']=='upd') {
						$akunHead=$dtakun['noakun'];
					}

					if ($row['tipeinvoice']=='pjd') {
						##cek apakah ada pengambilan uang muka perjalanan dinas sebelumnya
						$strkas="select * from ".$dbname.".keu_kasbankdt where nodok='".$row['nopo']."'";
						$reskas=fetchData($strkas);

						##jika tidak mengambil uang muka
						if (count($reskas)==0){
							$akunHead=$dtakun['noakun'];
						}else{
							##Parameter jurnal noakun debet dan kredit
							$str="select noakundebet,noakunkredit,sampaidebet,sampaikredit from ".$dbname.".keu_5parameterjurnal 
							where jurnalid='PJPD' and kodeaplikasi='PJDSK'";
							$res=fetchData($str);
							$bar=$res[0];
							$noakundebet=$bar['noakundebet'];
							$noakunkredit=$bar['noakunkredit'];
							$jumlahinvoice=$jumlahinvoice*-1;

							if($dtakun['noakun']==$noakundebet || $dtakun['noakun']==$noakunkredit){
								continue;
							}
						}
					}

					
					
					
					@$ketdtsup=($nmsup[$row['kodesupplier']]==''?$nmsupram[$row['kodesupplier']]:$nmsup[$row['kodesupplier']]);
					@$ketnoinvsup=$row['noinvoicesupplier'];
					@$ketnoinvsupint=$row['noinvoice'];
					
			//'keterangan2' => $dtakun['keterangan'],   
					if ($jumlahinvoice!=0) {
						@$nodt+=1;
						$data[] = array(
							'notransaksi' => $param['notransaksi'],
							'nourut' =>'',
							'noakun' => $akunHead,
							'tipetransaksi' => $param['tipetransaksi'],
							'tanggal' => $row['tanggal'],
							'jumlah' => $jumlahinvoice,
							'noakun2a' => $param['noakun'],
							'kode' => $param['kode'],
							'keterangan1' => $row['noinvoice'],             
							'keterangan2' => '',                
							'matauang' => $param['matauang'],
							'kurs' => $param['kurs'],
							'kurs2' => 1,
							'noaruskas' =>$dtakun['noaruskas'],
							'kodeorg' => $param['kodeorg'],
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $row['kodesupplier'],
							'kodevhc' => '',
							'orgalokasi' => '',
							'nodok' => $row['nopo'],
							'hutangunit1' => $param['hutangunit'],
							'kodesegment' => '',
							'tahun' =>$param['tahun'],
							'bulan' =>$param['bulan'], 
							'keterangan3' => ' '.$nodt.'. Pembayaran a/n '.$ketdtsup.';No. Invoice Internal:'.$ketnoinvsupint.';Supplier/Kontraktor:'.$ketnoinvsup.';No. Transaksi:'.$dtakun['notransaksi'],	
							'lainnya' => '',							
							'pemilikhutang1' => $unitSumber['unit']                     
						);
					}   
				}
				
				##tutup insert Detail

				#ambil yng ppn aja untuk uang muka
				foreach($resDet as $rowDt=>$dtakun){
					if($row['tipeinvoice']=='um' && substr($dtakun['noakun'],0,3)=='117'){

						$sJmlh="select distinct sum(jumlah) as jmlhKas from ".$dbname.".keu_kasbankdt 
								where keterangan1='".$row['noinvoice']."' and noakun='".$dtakun['noakun']."' ";
						$qJmlh=$owlPDO->query($sJmlh);
						$qJmlh->setFetchMode(PDO::FETCH_ASSOC);
						$rJmlh=$qJmlh->fetch();
						$jmlhKas=floatval($rJmlh['jmlhKas']);
						$dtakun['nilai']=$dtakun['nilai']-$jmlhKas;
						$dataket2=getSetupKeterangan($dtakun['noaruskas'],$param['bulan'],$param['tahun']);
						@$dataket2=explode('##', $dataket2);

						if ($dtakun['nilai']!=0) {
							$param['hutangunit']=0;
							$data[] = array(
								'notransaksi' => $param['notransaksi'],
								'nourut' =>'',
								'noakun' => $row['noakun'],
								'tipetransaksi' => $param['tipetransaksi'],
								'tanggal' => $row['tanggal'],
								'jumlah' => $dtakun['nilai'],
								'noakun2a' => $param['noakun'],
								'kode' => $param['kode'],
								'keterangan1' => $row['noinvoice'],              
								'keterangan2' => '',                
								'matauang' => $param['matauang'],
								'kurs' => $param['kurs'],
								'kurs2' => 1,
								'noaruskas' =>$dtakun['noaruskas'],
								'kodeorg' => $param['kodeorg'],
								'kodekegiatan' => '',
								'kodeasset' => '',
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $row['kodesupplier'],
								'kodevhc' => '',
								'orgalokasi' => '',
								'nodok' => $row['nopo'],
								'hutangunit1' => $param['hutangunit'],
								'kodesegment' => '',
								'tahun' =>$param['tahun'],
								'bulan' =>$param['bulan'], 
								'keterangan3' => '',               
								'lainnya' => '',		
								'pemilikhutang1' => $unitSumber['unit']                  
							);
						}
						
					
						
						
					}
				}
				##tutup insert PPN Uang Muka
			}
        ##tutup header tagihan

			  // echo"<pre>";
			  // print_r($data);
			  // echo"</pre>";
			  // exit("Error:Azzzzzz");
			  
			  //20190614031041
			  
			  
                foreach($data as $row) {
                    $query = insertQuery($dbname,'keu_kasbankdt',$row);
						
				// exit("Error:".$query);
				
                    try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>".$query; die(); }
                }

        break;

        case 'addFromInvoiceAR':
		
		
				// echo"<pre>";
				// print_r($param);
				// echo"</pre>";
				// exit("Error:A");
                if ($param['bulan']=='' || $param['tahun']=='') {
                    exit('warning : Bulan dan tahun wajib diisi.');
                }
		
                $param = $_POST;
                // Default Segment
                $defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

                $nilaiHeader = str_replace(',','',$param['jumlah']);

                // Parameter Piutang
                $qParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',
                        "kodeaplikasi='PIU' and jurnalid='PIU'");
                $resParam = fetchData($qParam);
                if(empty($resParam)) exit("Warning: ".$kodeApp." ".$_SESSION['lang']['notifparameterjurnal']."\n".$_SESSION['lang']['notifcontacitdept']);

                $qParam2 = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit',
                        "kodeaplikasi='PIU2' and jurnalid='PIU2'");

                $resParam2 = fetchData($qParam2);
                if(empty($resParam2)) exit("Warning: PIU2 ".$_SESSION['lang']['notifparameterjurnal']."\n".$_SESSION['lang']['notifcontacitdept']);
                $noakunPPn=$resParam2[0]['noakunkredit'];
                
                $data = array();
                foreach($param['invoice'] as $noInv) {
					
                    $rpInv=0;
                    $rpPpn=0;
                    #get data pemasukan dikasban
                    // $sKasbank="select noakun,sum(jumlah) as jumlah from ".$dbname.".keu_kasbankdt where keterangan1='".$noInv."' and jumlah>0 group by noakun";
                    $sKasbank="select noakun,sum(jumlah) as jumlah from ".$dbname.".keu_kasbankdt where keterangan1='".$noInv."' and jumlah>0";
                    $rKasbank=fetchData($sKasbank);
                    foreach($rKasbank as $row=>$rpMsk){
                        // if(substr($rpMsk['noakun'],0,3)=='113'){
                        //     $rpInv=$rpMsk['jumlah'];    
                        // }else if(substr($rpMsk['noakun'],0,5)=='21306'){
                        //     $rpPpn=$rpMsk['jumlah'];
                        // }
                        $rpInv=$rpMsk['jumlah'];
                    }
                    // $rpKlaim=0;
                    // $rpPpnKlaim=0;
                    #get data pemasukan dikasban
                    // $sKasbank="select noakun,sum(jumlah) as jumlah from ".$dbname.".keu_kasbankdt where keterangan1='".$noInv."' and jumlah<0 group by noakun";
                    // $rKasbank=fetchData($sKasbank);
                    // foreach($rKasbank as $row=>$rpMsk){
                        // if(substr($rpMsk['noakun'],0,5)=='21306'){
                        //     $rpPpnKlaim=$rpMsk['jumlah'];
                        // }else if(substr($rpMsk['noakun'],0,3)=='511'){
                        //     $rpKlaim=$rpMsk['jumlah'];
                        // }
                    //     $rpKlaim=$rpMsk['jumlah'];
                    // }
                    
                    // Get Header Penagihan
                    $qHead = selectQuery($dbname,'keu_penagihanht','*',"noinvoice='".$noInv."'");
                    $resHead = fetchData($qHead);
					
					
					
					#= cek hutang unit atau bukan
					$param['hutangunit']=0;
					$param['pemilikhutang']='';
					if($resHead[0]['kodeorg']!=$param['kodeorg']){
						$param['hutangunit']=1;
						$param['pemilikhutang']=$resHead[0]['kodeorg'];
						
					}
					#=====
					
					
					
                    
                    $skontrak="select kodebarang from ".$dbname.".pmn_kontrakjual where nokontrak='".$resHead[0]['nokontrak']."'";
                    if ($resHead[0]['jenis']=='DS') {
                        $skontrak="select kodeasset from ".$dbname.".keu_disposalasset where notransaksi='".$resHead[0]['nokontrak']."'";
                    }
                    $reskontrak = fetchData($skontrak);

                    if ($resHead[0]['jenis']=='DS') {
                        $tipeasset=substr($reskontrak[0]['kodeasset'],4,2);

                        $sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='NADIS'";
                        $rappl = fetchData($sappl);
                        $noarus=$rappl[0]['nilai'];
                        $noarus=explode(',', $noarus);
                        
                        switch ($tipeasset) {
                            case 'BG':$noaruskas=$noarus[0];break;
                            case 'IS':$noaruskas=$noarus[1];break;
                            case 'MS':$noaruskas=$noarus[2];break;
                            case 'KD':$noaruskas=$noarus[3];break;
                            case 'AB':$noaruskas=$noarus[4];break;
                            case 'KP':$noaruskas=$noarus[5];break;
                            case 'PR':$noaruskas=$noarus[6];break;
                            case 'TM':$noaruskas=$noarus[7];break;
                        }
                    }else if ($resHead[0]['jenis']=='PJD') {

                        // Parameter Piutang
                        $qParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='PIUPJ' and jurnalid='PIUPJ'");
                        $resParam = fetchData($qParam);

                        $noaruskas='';
                        ##get noaruskas pjd
                        $sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='NAPJD'";
                        $rappl = fetchData($sappl);
                        $noaruskas=$rappl[0]['nilai'];
                        
                    }else{
                        $kdbrg=$reskontrak[0]['kodebarang'];

                        //noaruskas kontrak
                        $sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='NAKON'";
                        $rappl = fetchData($sappl);
                        $noarus=$rappl[0]['nilai'];
                        $noarus=explode(',', $noarus);

                        //noaruskas PPN
                        $sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='NAPPN'";
                        $rappl = fetchData($sappl);
                        $noarusppn=$rappl[0]['nilai'];
                        $noarusppn=explode(',', $noarusppn);
                        
                        switch ($kdbrg){
                            case '40000001':$noaruskas=$noarus[0];$noaruskasppn=$noarusppn[0];break;
                            case '40000002':$noaruskas=$noarus[1];$noaruskasppn=$noarusppn[1];break;
                            case '40000003':$noaruskas=$noarus[2];$noaruskasppn=$noarusppn[2];break;
                            case '40000005':$noaruskas=$noarus[3];$noaruskasppn=$noarusppn[3];break;
                            case '40000004':$noaruskas=$noarus[4];$noaruskasppn=$noarusppn[4];break;
                            case '40000016':$noaruskas=$noarus[5];$noaruskasppn=$noarusppn[5];break;
                        }

                        // vienny 007/KBP-1/TBS/IV/21 tidak termasuk jenis/qualifikasi di atas... kode barangnya TBS, arus kas 228001 - hub subsidiary
                        if($noaruskas==''){ // tidak ada juga di pmn_kontrakjual
                            if($resHead[0]['kodebarang']=='40000003'){
                                $noaruskas='228001';
                            }
                        }
                    }

                    //keterangan 
                    $ketdt=ketArusKas($noaruskas,$param['bulan'],$param['tahun']);
                    $ketdt=explode('##', $ketdt);
                    // $keterangan2=$ketdt[0];
                    // $keterangan2temp=$ketdt[1];
                    $keterangan2=$ketdt[1];

                    //keterangan PPN
                    $ketdt=ketArusKas($noaruskasppn,$param['bulan'],$param['tahun']);
                    $ketdt=explode('##', $ketdt);
                    // $keterangan2ppn=$ketdt[0];
                    // $keterangan2tempppn=$ketdt[1];
                    $keterangan2=$ketdt[1];
                        
                    // Potongan
                    $nilaiKlaimPengurang=0;
                    $jumlahUM=0;
                    $jumlahPpn=0;
					$ppnKlaim=0;
                    $resHead = $resHead[0];
                    $rdata = $resHead;
                    $nilaiKlaimPengurang=floatval($rdata['rupiah1']+$rdata['rupiah2']+$rdata['rupiah3']
                                         +$rdata['rupiah4']+$rdata['rupiah5']+$rdata['rupiah6']+$rdata['rupiah7']-$rdata['rupiah8']);
                    
					
                    if($rdata['nilaippn']>0) {
                        if($nilaiKlaimPengurang!=0){
                            $ppnKlaim=10/100*$nilaiKlaimPengurang;
                        }
						$nilaiKlaimPengurang+=$ppnKlaim;
                    }
                    // if($rpKlaim!=0){
                    //     $nilaiKlaimPengurang=$nilaiKlaimPengurang+$rpKlaim;
                    // }
                    // if($rpPpnKlaim!=0){
                    //     $ppnKlaim=$ppnKlaim+$rpPpnKlaim;
                    // }
                    // $piutangKurang = $nilaiKlaimPengurang + $rpPpnKlaim;
// exit("Error:$nilaiKlaimPengurang");
                    // Nilai
                    // $jumlahUM = ($rdata['nilaiinvoice']-$rpInv);
                    $jumlahUM = ($rdata['nilaiinvoice']+$rdata['nilaippn']-$rpInv);
                    // $jumlahPpn = ($rdata['nilaippn']-$rpPpn);
                    // $jumlahPiutang = ($rdata['nilaiinvoice']+$rdata['nilaippn']) - $piutangKurang;
                    $jumlahPiutang = ($rdata['nilaiinvoice']+$rdata['nilaippn']-$rpInv) - $nilaiKlaimPengurang;
                    //exit('warning'.$jumlahUM._.$jumlahPpn);
						
								
					@$ketdtsup=$nmcust[$resHead['kodecustomer']];	
					@$ketnofp=$resHead['nofakturpajak'];	
					@$ketnoinv=$resHead['keterangan'];	
					if($ketnoinv!=''){
						$ketnoinv=$ketnoinv;
					}else{
						$ketnoinv='';
					}
					

					
					
					#### tutup tambahan untuk penjualan TBS
					
						
					if($jumlahPiutang!=0){
						
						if($param['tipetransaksi']=='K'){
							$jumlahPiutang=$jumlahPiutang*-1;
						}
						
                        // Piutang Penjualan
                        $data[] = array(
                                'notransaksi' => $param['notransaksi'],
								'nourut' =>'',
                                'noakun' => $resParam[0]['noakunkredit'],
                                'tipetransaksi' => $param['tipetransaksi'],
                                'tanggal' => tanggalsystemn($param['tanggal']),
                                'jumlah' => $jumlahPiutang,
                                'noakun2a' => $param['noakun'],
                                'kode' => $param['kode'],
                                'keterangan1' => $noInv,
                                // 'keterangan2' => 'Penerimaan dana dari kontrak '.$noInv,    
								'keterangan2' =>$bar['keterangan'].', Customer : '.$nmcust[$resHead['kodecustomer']].' No. Dok. : '.$resHead['nokontrak'],		
                                'matauang' => $param['matauang'],
                                'kurs' => $param['kurs'],
                                'kurs2' => 1,
                                'noaruskas' => $noaruskas,
                                'kodeorg' => $param['kodeorg'],
                                'kodekegiatan' => '',
                                'kodeasset' => '',
                                'kodebarang' => '',
                                'nik' => '',
                                'kodecustomer' => $resHead['kodecustomer'],
                                'kodesupplier' => '',
                                'kodevhc' => '',
                                'orgalokasi' => '',
                                'nodok' => $resHead['nokontrak'],
                                'hutangunit1' => $param['hutangunit'],
                                'kodesegment' => '',
                                'tahun' =>$param['tahun'],
                                'bulan' =>$param['bulan'],
								'keterangan3' =>'DPP',            
								'lainnya' => '',
								'pemilikhutang' => $param['pemilikhutang']          
                        );    
                    }
                    
					/*
                    //noaruskas PPN    
                    if($jumlahPpn!=0) {
                        $optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$noakunPPn."'");
                        $data[] = array(
                                'notransaksi' => $param['notransaksi'],
                                'noakun' => $noakunPPn,
                                'tipetransaksi' => $param['tipetransaksi'],
                                'tanggal' => tanggalsystemn($param['tanggal']),
                                'jumlah' => $rdata['nilaippn'],
                                'noakun2a' => $param['noakun'],
                                'kode' => $param['kode'],
                                'keterangan1' => $noInv,
                                'keterangan2' => $keterangan2ppn,                                
                                'matauang' => $param['matauang'],
                                'kurs' => $param['kurs'],
                                'kurs2' => 1,
                                'noaruskas' => $noaruskasppn,
                                'kodeorg' => $param['kodeorg'],
                                'kodekegiatan' => '',
                                'kodeasset' => '',
                                'kodebarang' => '',
                                'nik' => '',
                                'kodecustomer' => $resHead['kodecustomer'],
                                'kodesupplier' => '',
                                'kodevhc' => '',
                                'orgalokasi' => '',
                                'nodok' => $resHead['nokontrak'],
                                'hutangunit1' => $param['hutangunit'],
                                'kodesegment' => '',
                                'tahun' =>$param['tahun'],
                                'bulan' =>$param['bulan'],
								'keterangan3' => '',            
								'pemilikhutang' => ''
                        );
                    }
					*/
                    //noaruskas klaim
					/*
                    if($nilaiKlaimPengurang!=0){  
                        $optakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$resParam[0]['noakundebet']."'");
                        $data[] = array(
                                'notransaksi' => $param['notransaksi'],
                                'noakun' => $resParam[0]['noakundebet'],
                                'tipetransaksi' => $param['tipetransaksi'],
                                'tanggal' => tanggalsystemn($param['tanggal']),
                                'jumlah' => -1*$nilaiKlaimPengurang,
                                'noakun2a' => $param['noakun'],
                                'kode' => $param['kode'],
                                'keterangan1' => $noInv,
                                'keterangan2' => $keterangan2,                                  
                                'matauang' => $param['matauang'],
                                'kurs' => $param['kurs'],
                                'kurs2' => 1,
                                'noaruskas' => $noaruskas,
                                'kodeorg' => $param['kodeorg'],
                                'kodekegiatan' => '',
                                'kodeasset' => '',
                                'kodebarang' => '',
                                'nik' => '',
                                'kodecustomer' => $resHead['kodecustomer'],
                                'kodesupplier' => '',
                                'kodevhc' => '',
                                'orgalokasi' => '',
                                'nodok' => $resHead['nokontrak'],
                                'hutangunit1' => $param['hutangunit'],
                                'kodesegment' => '',
                                'tahun' =>$param['tahun'],
                                'bulan' =>$param['bulan'],
								'keterangan3' =>'Klaim Mutu untuk kontrak '.$noInv,            
								'pemilikhutang' => ''
                        );
                    }
					*/
					/*
                    if ($ppnKlaim!=0) {
                        $data[] = array(
                                'notransaksi' => $param['notransaksi'],
                                'noakun' =>$noakunPPn,
                                'tipetransaksi' => $param['tipetransaksi'],
                                'tanggal' => tanggalsystemn($param['tanggal']),
                                'jumlah' => -1*$ppnKlaim,
                                'noakun2a' => $param['noakun'],
                                'kode' => $param['kode'],
                                'keterangan1' => $noInv,
                                'keterangan2' => $keterangan2ppn,                                 
                                'matauang' => $param['matauang'],
                                'kurs' => $param['kurs'],
                                'kurs2' => 1,
                                'noaruskas' => $noaruskasppn,
                                'kodeorg' => $param['kodeorg'],
                                'kodekegiatan' => '',
                                'kodeasset' => '',
                                'kodebarang' => '',
                                'nik' => '',
                                'kodecustomer' => $resHead['kodecustomer'],
                                'kodesupplier' => '',
                                'kodevhc' => '',
                                'orgalokasi' => '',
                                'nodok' => $resHead['nokontrak'],
                                'hutangunit1' => $param['hutangunit'],
                                'kodesegment' => '',
                                'tahun' =>$param['tahun'],
                                'bulan' =>$param['bulan'],
								'keterangan3' => '',            
								'pemilikhutang' => ''
                        );
                    }
					*/
                }
                // echo "<pre>";
                // print_r($data);
                // echo "error</pre>";
                // exit();
               // print_r($data);exit('error');
                $qIns = insertQuery($dbname,'keu_kasbankdt',$data);
                // exit('warning : '.$qIns);
                try{$owlPDO->exec($qIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>".$qIns; die(); }
				
				$str = "update ".$dbname.".keu_kasbankht set bayarkepada='".$nmcust[$resHead['kodecustomer']]."' where notransaksi='".$param['notransaksi']."'";             
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
				
				echo $nmcust[$resHead['kodecustomer']];
				
				
		break;

				
        case 'addFromMemo':
        $param = $_POST;

        if($param['hutangunit']==1){
                #cek apakah hutang unit sudah tersimpan di data header/belum
                $sCek="select hutangunit from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."' and hutangunit='".$param['hutangunit']."' 
                       and pemilikhutang='".$param['pemilikhutang']."'";
                $qCek=$owlPDO->query($sCek);
                $qCek->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=owlBaris($qCek);
                if($rCek==0){
                        exit('warning: '.$_SESSION['lang']['notifunithutang']);
                }
                 #=============== Get Induk Pemilik Hutang
                     $whereNomilhut = "kodeorganisasi='".$param['pemilikhutang']."'";
                     $query = selectQuery($dbname,'organisasi','induk',$whereNomilhut);
                     $noKon = fetchData($query);
                     $indukpemilikhutang = $noKon[0]['induk'];

                    #=============== Get Induk Pembayar Hutang
                    $whereNoyarhut = "kodeorganisasi='".$param['kodeorg']."'";
                    $query = selectQuery($dbname,'organisasi','induk',$whereNoyarhut);
                    $noKon = fetchData($query);
                    $indukpembayarhutang = $noKon[0]['induk'];

                    if($indukpemilikhutang==$indukpembayarhutang)$jenisinduk='intra'; else $jenisinduk='inter';
                $whereNocaco = "jenis='".$jenisinduk."' and kodeorg='".$param['pemilikhutang']."'";
                        $query = selectQuery($dbname,'keu_5caco','akunpiutang',$whereNocaco);
                        $noKon = fetchData($query);
                        $noakuncaco = $noKon[0]['akunpiutang'];

                        if ($noakuncaco=='') {
                            exit("Warning : Account intraco or interco not available for ".$param['pemilikhutang'].". Please setting on menu Finance > setup > COA for Intra/Interco.");
                        } 
        }	
                // Get Data
                if($param['tipetransaksi']=='M') {
                        $whereJ = " and jumlah >= 0";
                } else {
                        $whereJ = " and jumlah < 0";
                }
                $qData = selectQuery($dbname,'keu_jurnaldt',"*","nojurnal='".$param['nojurnal']."'".$whereJ);
                $resData = fetchData($qData);

                // Default Segment
                $defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

                // Rearrange Data
                $data = array();
                $num=0;
        foreach($resData as $row) {
                if($param['hutangunit']==1){
                        if($row['noakun']==$param['noakunhutang']){
                                $row['noakun']=$noakuncaco;
                        }else{
                                continue;
                        }
                }
				if($row['jumlah']<0){
					$row['jumlah'] = $row['jumlah'] * (-1);
				}
				$row['jumlah'] = $row['jumlah'];
				$num++;
            $data[] = array(
                'notransaksi' => $param['notransaksi'],
				'nourut' =>'',
                'noakun' => $row['noakun'],
                'tipetransaksi' => $param['tipetransaksi'],
                'tanggal' => tanggalsystem($param['tanggal']),
                'jumlah' => $row['jumlah'],
                'noakun2a' => $param['noakun'],
                'kode' => $param['kode'],
                'keterangan1' => $row['nodok'],
                'keterangan2' => $row['keterangan'],
				
                'matauang' => $param['matauang'],
                'kurs' => $param['kurs'],
                'kurs2' => 1,
                'noaruskas' => $row['noaruskas'],
                'kodeorg' => $param['kodeorg'],
                'kodekegiatan' => $row['kodekegiatan'],
                'kodeasset' => $row['kodeasset'],
                'kodebarang' => $row['kodebarang'],
                'nik' => $row['nik'],
                'kodecustomer' => $row['kodecustomer'],
                'kodesupplier' => $row['kodesupplier'],
                'kodevhc' => $row['kodevhc'],
                'orgalokasi' => '',
                'nodok' => $param['nojurnal'],
                'hutangunit1' => $param['hutangunit'],
                                'kodesegment' => $defSegment,
								'bulan' =>'',
                                    'tahun' =>'',
									'lainnya' => '',
								'keterangan3' => ''
								
            );
        }

        foreach($data as $row) {
            $query = insertQuery($dbname,'keu_kasbankdt',$row);
            try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        }
        break;
    case'getkeg':
        $sAkn="select akunak from ".$dbname.".sdm_5tipeasset where kodetipe='".substr($param['kodeasset'],3,2)."'";
        $rAkn=fetchdata($sAkn);
        echo $rAkn[0]['akunak'];
    break;
	
	case 'getakunkasbank':
		# Options
        $arrakunbank .= "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$whereJam=" kasbank=1 and detail=1 and left(noakun,3)!='115' and (pemilik='".$_POST['kdorg']."' or pemilik='GLOBAL' or pemilik='".$_POST['kdorg']."')";
		$str = "select noakun,namaakun,namaakun1 from ".$dbname.".keu_5akun where ".$whereJam."";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()) 
		{
			if($_SESSION['language']=='EN')
			{
				$arrakunbank .= "<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun1']."</option>";
			}
			else
			{
				$arrakunbank .= "<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
			}
			
		}
		
		
		$optunit= "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$optunit.= "<option value='".$_POST['kdorg']."'>".$nmorg[$_POST['kdorg']]."</option>";
		
		
		echo $arrakunbank."####".$optunit;
	break;

    case 'getbank':
        $optbank.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        if($_POST['noakun2a']=='1110101' or $_POST['noakun2a']=='1111101') {  
            $whr=""; 
            if ($_POST['noakun2a']=='1111101') {
                $whr=" and matauang!='IDR'";
            }else{
                $whr=" and matauang='IDR'";
            }
            // $optbank="";
            $str = "select * from ".$dbname.".keu_5akunbank where pemilik='".$_POST['kodeorg']."' ".$whr;
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $wheredz =" kodebank='".$bar['namabank']."'";
                $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
                $optbank.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
            }
        }

        echo $optbank;
        
    break;
	
	 case 'getbank2':
	 
		if($_POST['notransaksi']!=''){
			$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$_POST['notransaksi']."'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
			$norek=$bar['norekpenerima'];
		}
	 
	 
        $optbank.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        if($_POST['noakun2a']=='1110101' or $_POST['noakun2a']=='1111101'){  
            $whr=""; 
            if ($_POST['noakun2a']=='1111101') {
                $whr=" and matauang!='IDR'";
            }else{
                $whr=" and matauang='IDR'";
            }
            // $optbank="";
            $str = "select * from ".$dbname.".keu_5akunbank where pemilik='".$_POST['kodeorg']."' ".$whr;
			// exit("Error:$str");
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $wheredz =" kodebank='".$bar['namabank']."'";
                $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
				if($bar['noakun']==$norek){
					$optbank.="<option selected value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
				}else{
					$optbank.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
				}
                
            }
        }

        echo $optbank;
        
    break;
	

    case 'getbuktibayar':
        # Options
        $str = "select * from ".$dbname.".keu_bukucekht where noakun='".$_POST['rekening']."' 
				and status='1' and tipe_buku='".$_POST['cgttu']."' order by right(notrans_cek,3) desc";
				// echo $str;exit();
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
			$notrans_cek=$bar['notrans_cek'];
			$nocek_awal=$bar['nocek_awal'];
			$jumlahangka=strlen($nocek_awal);
			$nocek_akhir=$bar['nocek_akhir'];

			$angkaawal=preg_replace("/[^0-9]/",'',$nocek_awal);
			$angkaakhir=preg_replace("/[^0-9]/",'',$nocek_akhir);
			
			$selisih=$angkaakhir-$angkaawal+1;
			
			$nocek='';
			for ($i=1; $i <=$selisih ; $i++) { 
				// $str = "select nocek from ".$dbname.".keu_bukucekdt where notrans_cek='".$notrans_cek."' and nocek='".$nocek_awal."' order by nocek asc";
				// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				// $res->setFetchMode(PDO::FETCH_ASSOC);
				// $bar=$res->fetch();
				
				$str1 = "select nocek from ".$dbname.".keu_kasbankht where nocek='".$nocek_awal."'";
				$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_ASSOC);
				$bar1=$res1->fetch();
				
				if($bar1['nocek']!=$nocek_awal){
					$optcek.="<option value='".$nocek_awal."'>".$nocek_awal."</option>";
				}
				$nocek_awal++;
			}
        }
		
        echo $optcek;
		// exit("Error:".$optcek);
    break;
	
	
	
	case 'getbuktibayarkasir':
        # Options
        $str = "select * from ".$dbname.".keu_bukucekht where noakun='".$_POST['rekening']."' 
				and status='1' and tipe_buku='".$_POST['cgttu']."' order by right(notrans_cek,3) desc";
				// echo $str;exit();
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
			$notrans_cek=$bar['notrans_cek'];
			$nocek_awal=$bar['nocek_awal'];
			$jumlahangka=strlen($nocek_awal);
			$nocek_akhir=$bar['nocek_akhir'];

			$angkaawal=preg_replace("/[^0-9]/",'',$nocek_awal);
			$angkaakhir=preg_replace("/[^0-9]/",'',$nocek_akhir);
			
			$selisih=$angkaakhir-$angkaawal+1;
			
			$nocek='';
			for ($i=1; $i <=$selisih ; $i++) { 
				// $str = "select nocek from ".$dbname.".keu_bukucekdt where notrans_cek='".$notrans_cek."' and nocek='".$nocek_awal."' order by nocek asc";
				// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				// $res->setFetchMode(PDO::FETCH_ASSOC);
				// $bar=$res->fetch();
				
				$str1 = "select nocek from ".$dbname.".keu_kasbankht where nocek='".$nocek_awal."'";
				$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_ASSOC);
				$bar1=$res1->fetch();
				
				if($bar1['nocek']!=$nocek_awal){
					$optcek.="<option value='".$nocek_awal."'>".$nocek_awal."</option>";
				}
				$nocek_awal++;
			}
        }
		
		#= nomor cek yang sudah dipakai, bisa muncul jika tanggal, rekening, dan tipe pembayaran yang sama
		#= perubahan meeting 12 maret 2021 di HO ithaca, karna bu insan bisa memakai 1 nomor cek untuk beberapa nomor voucher
		$str = "select nocek from ".$dbname.".keu_kasbankht where cgttu='".$_POST['cgttu']."' and rekening='".$_POST['rekening']."' and tanggal='".tanggalsystemn($_POST['tglbayar'])."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
			$optcek.="<option value='".$bar['nocek']."'>".$bar['nocek']."</option>";
		}
		
		
        echo $optcek;
		// exit("Error:".$optcek);
    break;
	

    case 'getmatauang':
        $str = "select matauang from ".$dbname.".keu_5akunbank where noakun='".$param['rekening']."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $matauang=$bar['matauang'];
        if ($param['rekening']=='') {
            $matauang='IDR';
        }

        $str = "select * from ".$dbname.".setup_matauang";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            if ($bar['kode']==$matauang) {
                $optbank.="<option value='".$bar['kode']."' selected>".$bar['matauang']."</option>";
            }else{
                $optbank.="<option value='".$bar['kode']."'>".$bar['matauang']."</option>";
            }
        }

        if ($matauang!='') {
            $iKurs="select kurs from ".$dbname.".setup_matauangrate where kode='".$matauang."' and daritanggal<='".tanggalsystemn($param['tanggal'])."' order by daritanggal desc, jam desc  limit 1";
            $nKurs=$owlPDO->query($iKurs);
            $nKurs->setFetchMode(PDO::FETCH_ASSOC);
            $dKurs=$nKurs->fetch();
            $kurs=$dKurs['kurs'];
        }else{
            $kurs=1;
        }
        

        echo $optbank."####".$kurs;
    break;
	
    default:
        break;
}

function cekVendorKasKecil() {
		/*
        global $dbname;
        global $param;

        // Get Parameter Aplikasi
        $qParam = selectQuery($dbname,'setup_parameterappl',"nilai",
                                                  "kodeaplikasi='KB' and kodeparameter='VENCASH'");
        $resParam = fetchData($qParam);
        $jml = str_replace(',','',$param['jumlah']);
        if(!empty($resParam)) {
                if($param['kodesupplier']==$resParam[0]['nilai'] and $jml>20000000) {
                        exit("Warning: ".$_SESSION['lang']['notifpettycash']);
                }
        }
		*/
}
function ketArusKas($idArus,$bulan,$tahun){
    global $dbname;
    global $conn;
    global $owlPDO;

    $str1="select keterangan,id_ket from keu_5keterangan where noaruskas='".$idArus."'";
    $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_ASSOC);
    $bar1=$res1->fetch();

    // $keterangan2=trim($bar1['keterangan'].' '.numToMonth($bulan,'I','long').' '.$tahun);
    // $keterangan2temp=$bar1['id_ket'];

    $keterangan2=$bar1['id_ket'];

    // return $keterangan2."##".$keterangan2temp;
    return $keterangan2."##".$keterangan2;
}

function getnoakundata(){
	
	
}


function hitungsisa($notransaksi){
	    global $dbname;
		global $conn;
		global $owlPDO;
	
		#= mengambil sisa transaksi
		$str = "select sum(jumlah) as jumlah from ".$dbname.".keu_kasbankht where notransaksi='".$notransaksi."'";
		// exit("Error:$str");
		$res = $owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$nilht=$bar['jumlah'];
			
		$str = "select sum(jumlah) as jumlah from ".$dbname.".keu_kasbankdt where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$nildt=$bar['jumlah'];
		
		$sisa=$nilht-$nildt;
		
		return $sisa;
	
	// alert('masuk');
	
	
}