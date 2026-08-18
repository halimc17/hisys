<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/tanaman.php');
require_once('lib/cekakun.php');
// $kodeunit = checkPostGet('kodeunit','');
// $proses = checkPostGet('proses','');

$proses = $_GET['proses'];
$param = $_POST;
switch($proses) {
    case 'add':
    $kodeunit=explode('/', $param['nojurnal']);
    $kdunit=$kodeunit[1];
	
	
	$str="select noreferensi from ".$dbname.".keu_jurnalht where nojurnal='".$param['nojurnal']."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$noreferensi=$bar['noreferensi'];
	
	
    // echo $kodeunit;
	# Search No urut
	$selQuery = selectQuery($dbname,'keu_jurnaldt','nourut',"nojurnal='".$param['nojurnal']."'");
	$nourut = fetchData($selQuery);
	$maxNoUrut = 1;
	if(!empty($nourut)) {
	    foreach($nourut as $row) {
		$row['nourut']>=$maxNoUrut ? $maxNoUrut=$row['nourut'] : false;
	    }
	    $maxNoUrut++;
	}

	$cols = array('nourut','noakun','keterangan','jumlah','matauang','kurs',
	    'kodekegiatan','kodesegment','kodeasset','nik','kodesupplier','kodevhc','nodok','kodeblok','kodecustomer','nojurnal','tanggal','kodeorg','noreferensi');
//	$cols = array('nourut','noakun','keterangan','jumlah','matauang','kurs','noaruskas',
//	    'kodekegiatan','kodeasset','kodebarang','nik','kodecustomer',
//	    'kodesupplier','kodevhc','nodok','nojurnal','tanggal','kodeorg');


	$data = $param;
	
	// cek apakah sudah closing:
	$periodez=substr($data['tanggal'],6,4).'-'.substr($data['tanggal'],3,2); // 31-01-2021
	$unitz=substr($data['nojurnal'],9,4); // 20210131/SDRO/M/041
	$tutupbukuz='0';
	$str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unitz."' and periode = '".$periodez."'";
	$res=fetchdata($str);
	foreach($res as $bar){
		$tutupbukuz=$bar['tutupbuku'];
	}
	if($tutupbukuz=='1'){
		exit("error: ".$unitz." sudah closing periode ".$periodez." ");
	}
	
	
	
	if(substr($data['kodeasset'],0,2)=='PB'){
		$data['kodeblok']=$data['kodeasset'];
		//$data['kodeasset']='';
	}
	
	// if($data['noaruskas']=='')
	// {
	// 	exit("gagal : Arus Kas belum dipilih.");
	// }
	// exit("error : ".$data['noaruskas']);
	
	$data['nourut'] = $maxNoUrut;
	$data['kodeorg'] = $kdunit;
	$data['tanggal'] = tanggalsystem($data['tanggal']);
	$data['jumlah'] = str_replace(',','',$data['jumlah']);
	$data['noreferensi'] = $noreferensi;
	unset($data['numRow']);
	unset($data['kodejurnal']);
        
		
		
		/*
        //=====tambahan ginting
	#periksa apakah akun tanaman, dan jika akun tanaman maka harus ada kodeblok
        $blk=str_replace(" ","",$param['kodeblok']);
        // $nik=str_replace(" ","",$param['nik']);
        $sup=str_replace(" ","",$param['kodesupplier']);
        $vhc=str_replace(" ","",$param['kodevhc']);
        if(cekAkun($param['noakun']) and $blk=='')
        {
            exit("[ Error ]: Akun tanaman harus dilengkapi dengan kode blok.");
        }else if(cekAkun($data['noakun']) and $data['kodekegiatan']==''){
            exit("[ Error ]: Kode kegiatan harus dilengkapi.");
        }else if(cekAkunTrans($data['noakun']) and $vhc=='')
        {
            exit("[ Error ]: Akun  harus dilengkapi dengan Kode Alat/Kend.");
        }
        //=====end tambahan ginting
         */
		
		
		$blk=str_replace(" ","",$param['kodeblok']);    
		$sup=str_replace(" ","",$param['kodesupplier']);
		$vhc=str_replace(" ","",$param['kodevhc']);    
		$nik=str_replace(" ","",$param['nik']);
		$nodok=str_replace(" ","",$param['nodok']);    
		$data['nodok']=trim($data['nodok']);

		// print_r($param);exit("Error:A");
		/*
		if(cekAkun($data['noakun']) and $blk==''){
				exit("warningg : ".$_SESSION['lang']['notifakuntanaman']);
		}else  if(cekAkun($data['noakun']) and $data['kodekegiatan']==''){
				exit("warning : ".$_SESSION['lang']['notifkodekegiatan']);
		}else if(cekAkunPiutang($data['noakun']) and ($nik=='' or $sup=='') and $nodok==''){
				exit("warning : Assignment dan nomor dokumen tidak boleh kosong");
		}else if(cekAkunHutang($data['noakun']) and ($sup=='' or $nodok=='')){
				exit("warning : ".$_SESSION['lang']['notifkodesupplier']." / nomor dokumen masih kosong ");
		}else if(cekAkunTrans($data['noakun']) and $vhc==''){
				exit("warning : ".$_SESSION['lang']['notifkodevhc']);
		}
		*/

		cekakunjm($data['noakun'],$data['kodekegiatan'],$data['kodeasset'],$data['nik'],$data['kodecustomer'],$data['kodesupplier'],$data['kodevhc'],$data['kodeblok'],'',$data['nodok']);
		
		
		
	#cek parameter aps untuk akun piutang karyawan
	$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='JM' and kodeparameter='JMPIU1' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$aktif=$bar['nilai'];
		
	if($data['noakun']=='1140400' && $aktif!=1){
		exit("Warning:Piutang karyawan tidak diaktifkan, hubungin IT");
	}
		 
	$query = insertQuery($dbname,'keu_jurnaldt',$data,$cols);
	// exit("Error:$query");
	try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  ! Insert: " . $e->getMessage() . "<br/>"; die(); }
		
	#= cek apakah nomor dukumen tersebut ada ditransaksi berulang
	$str="select count(*) as datarutin from ".$dbname.".keu_transaksi_rutin where notransaksi='".$param['nodok']."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$datarutin=$bar['datarutin'];
		
	if($datarutin>0){
		$strht = "update " . $dbname . ".keu_transaksi_rutin set 
			posting=2 , tanggalstop='".$data['tanggal']."' , useridstop='".$_SESSION['standard']['userid']."' 
			where notransaksi='" . $param['nodok'] . "'";
		try {
			$owlPDO->exec($strht);
		} catch (PDOException $e) {
			print " Gagal: " . $e->getMessage() . "\n";
			die();
		}
	}		
	
	unset($data['nojurnal']);
	unset($data['kodejurnal']);
	unset($data['tanggal']);
	unset($data['kodeorg']);
	unset($data['noreferensi']);
	$res = "";
	foreach($data as $cont) {
	    $res .= "##".$cont;
	}
	
	$result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
	echo $result;
	break;
	
	
	
	
	
	
    case 'edit':
        
	$data = $param;
	// echo"<pre>";
	// print_r($data);
	// echo"</pre>";
	// exit("Error:A");

	// cek apakah sudah closing:
	$periodez=substr($data['tanggal'],6,4).'-'.substr($data['tanggal'],3,2); // 31-01-2021
	$unitz=substr($data['nojurnal'],9,4); // 20210131/SDRO/M/041
	$tutupbukuz='0';
	$str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unitz."' and periode = '".$periodez."'";
	$res=fetchdata($str);
	foreach($res as $bar){
		$tutupbukuz=$bar['tutupbuku'];
	}
	if($tutupbukuz=='1'){
		exit("error: ".$unitz." sudah closing periode ".$periodez." ");
	}
// exit("error: ".$unitz." ".$periodez." ".$tutupbukuz);
        
        //=====tambahan ginting
	#periksa apakah akun tanaman, dan jika akun tanaman maka harus ada kodeblok
	/*
        $blk=str_replace(" ","",$param['kodeblok']);
        $nik=str_replace(" ","",$param['nik']);
        $sup=str_replace(" ","",$param['kodesupplier']);
        $vhc=str_replace(" ","",$param['kodevhc']);
        if(cekAkun($param['noakun']) and $blk=='')
        {
            exit("[ Error ]: Akun tanaman harus dilengkapi dengan kode blok.");
        }else if(cekAkun($data['noakun']) and $data['kodekegiatan']==''){
            exit("[ Error ]: Kode kegiatan harus dilengkapi.");
        // }else  if(cekAkunPiutang($data['noakun']) and $nik=='')
        // {
        //     exit("[ Error ]: Akun  harus dilengkapi dengan ID Karyawan.");
        }else if(cekAkunHutang($data['noakun']) and $sup=='')
        {
            exit("[ Error ]: Akun  harus dilengkapi dengan Kode Supplier.");
        }else if(cekAkunTrans($data['noakun']) and $vhc=='')
        {
            exit("[ Error ]: Akun  harus dilengkapi dengan Kode Alat/Kend.");
        }
        //=====end tambahan ginting
		*/
		
		$blk=str_replace(" ","",$param['kodeblok']);    
		$sup=str_replace(" ","",$param['kodesupplier']);
		$vhc=str_replace(" ","",$param['kodevhc']);    
		$nik=str_replace(" ","",$param['nik']);
		$data['nodok']=trim($data['nodok']);
		
		/*
		if(cekAkun($data['noakun']) and $blk==''){
				exit("warningg : ".$_SESSION['lang']['notifakuntanaman']);
		}else  if(cekAkun($data['noakun']) and $data['kodekegiatan']==''){
				exit("warning : ".$_SESSION['lang']['notifkodekegiatan']);
		}else if(cekAkunPiutang($data['noakun']) and ($nik=='' or $sup=='') and $nodok==''){
				exit("warning : Assignment dan nomor dokumen tidak boleh kosong");
		}else if(cekAkunHutang($data['noakun']) and ($sup=='' or $nodok=='')){
				exit("warning : ".$_SESSION['lang']['notifkodesupplier']." / noinvoice masih kosong ");
		}else if(cekAkunTrans($data['noakun']) and $vhc==''){
				exit("warning : ".$_SESSION['lang']['notifkodevhc']);
		}
		*/
		
		cekakunjm($data['noakun'],$data['kodekegiatan'],$data['kodeasset'],$data['nik'],$data['kodecustomer'],$data['kodesupplier'],$data['kodevhc'],$data['kodeblok'],'',$data['nodok']);
		
		
	#cek parameter aps untuk akun piutang karyawan
	$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='JM' and kodeparameter='JMPIU1' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$aktif=$bar['nilai'];
		
	if($data['noakun']=='1140400' && $aktif!=1){
		exit("Warning:Piutang karyawan tidak diaktifkan, hubungin IT");
	}
		

	unset($data['nojurnal']);
	unset($data['kodejurnal']);
	unset($data['nourut']);
	$data['tanggal'] = tanggalsystem($data['tanggal']);
	$data['jumlah'] = str_replace(',','',$data['jumlah']);
	foreach($data as $key=>$cont) {
	    if(substr($key,0,5)=='cond_') {
		unset($data[$key]);
	    }
	}	

	$where = "nojurnal='".$param['nojurnal']."' and nourut='".$param['nourut']."'";
	$query = updateQuery($dbname,'keu_jurnaldt',$data,$where);
	try{

		$owlPDO->exec($query); 
		
		/*
		$query = selectQuery($dbname,'keu_jurnalht','autojurnal',"nojurnal='".$param['nojurnal']."'");
	    $res = fetchData($query);
	    $bar =$res[0];
	    $autojurnal=$bar['autojurnal'];

	    if ($autojurnal==2) {
		    $strsp[]="update ".$dbname.".keu_jurnalht set autojurnal='9' where nojurnal='".$param['nojurnal']."'";
		    $strsp[]="update ".$dbname.".approval set status='0',komentar='' where notransaksi='".$param['nojurnal']."'";
		    
		    for($i=0; $i<count($strsp); $i++){
				try{
			        $owlPDO->exec($strsp[$i]); 
			    }catch (PDOException $e){
			        echo "Gagal : ".$e->getMessage();
			        die();
			    }
			}
	    }
		*/

	}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
	echo json_encode($param);
	break;

    case 'delete':
	
	// print_r($param);exit("Error:A");
$data=$param;
	// cek apakah sudah closing:
	$periodez=substr($data['tanggal'],6,4).'-'.substr($data['tanggal'],3,2); // 31-01-2021
	$unitz=substr($data['nojurnal'],9,4); // 20210131/SDRO/M/041
	$tutupbukuz='0';
	$str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unitz."' and periode = '".$periodez."'";
	$res=fetchdata($str);
	foreach($res as $bar){
		$tutupbukuz=$bar['tutupbuku'];
	}
	if($tutupbukuz=='1'){
		exit("error: ".$unitz." sudah closing periode ".$periodez." ");
	}

	
	$where = "nojurnal='".$param['nojurnal']."' and nourut='".$param['nourut']."'";
	$query = "delete from `".$dbname."`.`keu_jurnaldt` where ".$where;
	try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
	
	#= cek apakah ada ditransaksi rutin
	#= cek apakah nomor dukumen tersebut ada ditransaksi berulang
	$str="select count(*) as datarutin from ".$dbname.".keu_transaksi_rutin where notransaksi='".$param['nodok']."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$datarutin=$bar['datarutin'];
		
	if($datarutin>0){
		$strht = "update " . $dbname . ".keu_transaksi_rutin set 
			posting=1 , tanggalstop='0000-00-00' , useridstop='0' 
			where notransaksi='" . $param['nodok'] . "'";
		try {
			$owlPDO->exec($strht);
		} catch (PDOException $e) {
			print " Gagal: " . $e->getMessage() . "\n";
			die();
		}
	}		
	
	
	break;
    default:
    break;
}
?>