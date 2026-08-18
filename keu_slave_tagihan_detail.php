<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
#include_once('lib/zGrid.php');
#include_once('lib/rGrid.php');
include_once('lib/formTable.php');

$proses = $_GET['proses'];
$param = $_POST;

switch($proses) {
    case 'showDetail':
		# Options
    	$optJnsJrn=makeOption($dbname,'keu_5jenistagihan','kode,jurnal');
    	if($optJnsJrn[$param['tipeinvoice']]==0){
    		$whrd="(noakun like '116%' or noakun like '21113%'  or noakun like '212%' or noakun = '8110114' or noakun='1180200' or noakun='1180400') and detail=1 and left(noakun,3)!='115'";		
    	}elseif($optJnsJrn[$param['tipeinvoice']]==1){
    		 $whrpoa="(left(noakun,1) in ('7','8','9') or noakun like '116%' or noakun like '21113%' or noakun like '4110206%' or noakun like '6410400%' 
						or noakun like '6400101%' or noakun like '212%' or noakun like '6400102%'  or noakun like '12101%' or noakun='1180200')";
    		 if($param['tipeinvoice']=='poa'){
    		 	$whrpoa=" noakun like '12813%' or noakun='1180200'";	
    		 }
    		$whrd=" ".$whrpoa." and detail=1 and char_length(noakun)>6 and left(noakun,3)!='115'";		
    	}
    	
		$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whrd,2,true);
		$whrVhc="left(kodetraksi,4)='".$param['unit']."'";
		$optVhc=makeOption($dbname,'vhc_5master','kodevhc,detailvhc',$whrVhc,2,true);
		// array_push($optVhc, "");
		
		$whrPrj="kodeorg='".$param['unit']."'";
		$optPrj=makeOption($dbname,'project','kode,kode',$whrPrj,'0','1');
		
		# Get Data
		$where = "noinvoice='".$param['noinvoice']."'";
		$cols = "kodevhc,kodeasset,noakun,nilai";
		$query = selectQuery($dbname,'keu_tagihandt',$cols,$where);
		$data = fetchData($query);
		$dataShow = $data;
		foreach($data as $key=>$row) {
			$dataShow[$key]['noakun'] = $optAkun[$row['noakun']];
		}
		
		# Form
		$theForm2 = new uForm('transForm',$_SESSION['lang']['form'].' '.$_SESSION['lang']['invoice']);
		$theForm2->addEls('kodevhc',$_SESSION['lang']['kodevhc'],'','selectsearch','L',31,$optVhc);
		$theForm2->addEls('kodeasset',$_SESSION['lang']['aktivadalam'],'','selectsearch','L',31,$optPrj);
		$theForm2->_elements[1]->_attr['onchange'] = 'getAknAsset()';
		$theForm2->addEls('noakun',$_SESSION['lang']['noakun'],'','selectsearch','L',31,$optAkun);
		// $theForm2->_elements[2]->_attr['onchange'] = 'getpajak()';
		// $theForm2->addEls('pajak',$_SESSION['lang']['pajak'],'','textnumw-','R',30);
		$theForm2->addEls('nilai',$_SESSION['lang']['nilai'],'0','textnumw-','R',30);
		$theForm2->_elements[3]->_attr['onchange'] = 'this.value=remove_comma(this);this.value = _formatted(this)';
		#$theForm2->_elements[1]->_attr['disabled'] = 'disabled';
			
		# Table
		$theTable2 = new uTable('transTable',$_SESSION['lang']['tabel'].' '.$_SESSION['lang']['invoice'],$cols,$data,$dataShow);
		
		# FormTable
		// $formTab2 = new uFormTable('transFT',$theForm2,$theTable2,null,array('noinvoice'));
		$formTab2 = new uFormTable('transFT',$theForm2,$theTable2,null,array('noinvoice'));
		$formTab2->_target = "keu_slave_tagihan_detail";
		$formTab2->_numberFormat = '##nilai';
		#$formTab2->_nourut = true;
		
		#== Display View
		# Draw Tab
		echo "<fieldset><legend><b>Detail</b></legend>";
		$formTab2->render();
		echo "</fieldset>";
		break;
    case 'add':
		$cols = array(
			'kodevhc','kodeasset','noakun','nilai','noinvoice'
		);
		$data = $param;
		unset($data['numRow']);
		$data['nilai'] = str_replace(',','',$data['nilai']);
		#jika akun kepala 4 tapi kode vhc kosong tidak dapat disimpan
		if(substr($data['noakun'],0,1)=='4'){
			if($data['kodevhc']==''){
				exit('warning: '.$_SESSION['lang']['kodevhc'].' '.$_SESSION['lang']['kosong']);		
			}
		}else{
			$data['kodevhc']='';
		}
		$whrTp="noinvoice='".$data['noinvoice']."'";
		$optTip=makeOption($dbname,'keu_tagihanht','noinvoice,tipeinvoice',$whrTp);
		if($optTip[$data['noinvoice']]=='poa'){
			if($data['kodeasset']==''){
				exit('warning: '.$_SESSION['lang']['kodeasset'].' '.$_SESSION['lang']['kosong']);			
			}
		}
		if($data['noakun']==''){
			exit('warning'.$_SESSION['lang']['noakun'].' '.$_SESSION['lang']['notifemptyzero']);
		}
		if($data['nilai']==0){
			exit('warning'.$_SESSION['lang']['nilai'].' '.$_SESSION['lang']['notifemptyzero']);
		}
		$kdAplikasi="HPPOLAH";
	    #ambil noakun biaya transit
	    $sAkun="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='".$kdAplikasi."'";
	    $rAkun=fetchData($sAkun);
	    $arrNoakun=explode(",",$rAkun[0]['nilai']);
	    foreach($arrNoakun as $row=>$dtNoakun){
	    	if($dtNoakun==$data['noakun']){
	    		$sTipe="select a.unit,b.tipe from ".$dbname.".keu_tagihanht a left join ".$dbname.".organisasi b
	    		        on a.unit=b.kodeorganisasi where a.noinvoice='".$data['noinvoice']."'";
	    		$rTipe=fetchData($sTipe);
	    		if($rTipe[0]['tipe']!='PABRIK'){
	    			exit('warning: Tipe Organisasi Harus Pabrik');
	    		}
	    	}
	    }
	    #cek unit apakah satu PT
		$rCek=array();
	    $sCek="select * from ".$dbname.".keu_5caco where jenis='intra' and akunpiutang='".$data['noakun']."'";
	    $rCek=fetchData($sCek);
	    
    	$sUnit="select * from ".$dbname.".keu_tagihanht where noinvoice='".$data['noinvoice']."'";
    	$rUnit=fetchData($sUnit);
	    
	    $optCekPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$rCek[0]['kodeorg']."'");
	    if(!empty($rCek)){
	    	if($optCekPt[$rCek[0]['kodeorg']]!=$_SESSION['org']['kodeorganisasi']){
	    	exit('warning: Noakun Yang Dipilih Beda PT');
		    }
			if($rUnit[0]['unit']==$rCek[0]['kodeorg']){
				exit('warning: Noakun Yang Dipilih Harus Beda Unit');
			}	
	    }

	    // $datadt['kodevhc']=$data['kodevhc'];
	    // $datadt['kodeasset']=$data['kodeasset'];
	    // $datadt['noakun']=$data['noakun'];
	    // $datadt['nilai']=$data['nilai'];
	    // $datadt['noinvoice']=$data['noinvoice'];
	    
		$query = insertQuery($dbname,'keu_tagihandt',$data,$cols);
		//exit('warning'.$query);
	      try{
	          $owlPDO->exec($query); 
	      }catch (PDOException $e){
	           echo "r Error :" . $e->getMessage();
	           exit;
	      }
		
		unset($data['noinvoice']);
		
		$res = "";
		foreach($data as $cont) {
			$res .= "##".$cont;
		}
		
		$result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
		echo $result;
		break;
    case 'edit':
		$data = $param;
		//unset($data['noinvoice']);
		$data['nilai'] = str_replace(',','',$data['nilai']);
		foreach($data as $key=>$cont) {
			if(substr($key,0,5)=='cond_') {
			unset($data[$key]);
			}
		}
		if(substr($data['noakun'],0,1)=='4'){
			if($data['kodevhc']==''){
				exit('warning: '.$_SESSION['lang']['kodevhc'].' '.$_SESSION['lang']['kosong']);		
			}
		}else{
			$data['kodevhc']='';
		}
		#cek unit apakah satu PT
		$rCek=array();
	    $sCek="select * from ".$dbname.".keu_5caco where jenis='intra' and akunpiutang='".$data['noakun']."'";
	    $rCek=fetchData($sCek);
	    
    	$sUnit="select * from ".$dbname.".keu_tagihanht where noinvoice='".$data['noinvoice']."'";
    	$rUnit=fetchData($sUnit);
	    
	    $optCekPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$rCek[0]['kodeorg']."'");
	    if(!empty($rCek)){
	    	if($optCekPt[$rCek[0]['kodeorg']]!=$_SESSION['org']['kodeorganisasi']){
	    	exit('warning: Noakun Yang Dipilih Beda PT');
		    }
			if($rUnit[0]['unit']==$rCek[0]['kodeorg']){
				exit('warning: Noakun Yang Dipilih Harus Beda Unit');
			}	
	    }

	    $kdAplikasi="HPPOLAH";
	    #ambil noakun biaya transit
	    $sAkun="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='".$kdAplikasi."'";
	    $rAkun=fetchData($sAkun);
	    $arrNoakun=explode(",",$rAkun[0]['nilai']);
	    foreach($arrNoakun as $row=>$dtNoakun){
	    	if($dtNoakun==$data['noakun']){
	    		$sTipe="select a.unit,b.tipe from ".$dbname.".keu_tagihanht a left join ".$dbname.".organisasi b
	    		        on a.unit=b.kodeorganisasi where a.noinvoice='".$data['noinvoice']."'";
	    		$rTipe=fetchData($sTipe);
	    		if($rTipe[0]['tipe']!='PABRIK'){
	    			exit('warning: Tipe Organisasi Harus Pabrik');
	    		}
	    	}
	    }
		$where = "noinvoice='".$param['noinvoice']."' and noakun='".
			$param['cond_noakun']."'";
		$query = updateQuery($dbname,'keu_tagihandt',$data,$where);
	      try{
	          $owlPDO->exec($query); 
	      }catch (PDOException $e){
	           echo "r Error :" . $e->getMessage();
	           exit;
	      }
		echo json_encode($param);
		break;
    case 'delete':
		$where = "noinvoice='".$param['noinvoice']."' and noakun='".$param['noakun']."' and kodevhc='".$param['kodevhc']."'";
		$query = "delete from `".$dbname."`.`keu_tagihandt` where ".$where;
	      try{
	          $owlPDO->exec($query); 
	      }catch (PDOException $e){
	           echo "r Error :" . $e->getMessage();
	           exit;
	      }
		break;
		case'cekData':
			$sData="select * from ".$dbname.".keu_5jenistagihan where kode='".$param['jnsInvoice']."' and source!=''";
			$rData=fetchData($sData);
			echo count($rData);


		break;
		case'getAknAsset':
			$sAkn="select akunak from ".$dbname.".sdm_5tipeasset where kodetipe='".substr($param['kdasset'],3,2)."'";
			$rAkn=fetchdata($sAkn);
			echo $rAkn[0]['akunak'];
		break;
		case'getpajak':
			$param['nilaiinvoice']=str_replace(",","",$param['nilaiinvoice']);
			$sAkn="select tarif from ".$dbname.".log_5pphsup where noakun='".$param['noakun']."' and status='1'";
			$rAkn=fetchdata($sAkn);
			echo $rAkn[0]['tarif']."##".$param['nilaiinvoice'];
		break;
    default:
	break;
}
?>