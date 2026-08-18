<?php
// if(count($_POST)==0){include('lib/nangkoelib.php');}
// include('lib/zLib.php');
// require_once('config/connection.php');

$folder = "imgbot/pdf/";
if (!file_exists($folder)){
	mkdir($folder, 0777, true);
}

$files = glob($folder.'*'); 
$idtrans=array();
foreach($files as $file) {
	if(is_file($file)){
		$lstfile=explode("/",$file);
		$nmfile =$lstfile[2];
		$lstnm  =explode("_",$nmfile);
		$jenisp =$lstnm[0];
		$notrans=substr($lstnm[1],0,(strlen($lstnm[1])-4));
		
		$where= "REPLACE(REPLACE(notransaksi,'/',''),'-','')='".$notrans."' and jenispersetujuan='".$jenisp."' and status='0'";
		$query= selectQuery($dbname,'approval','*',$where);
		$res  = fetchData($query);
		foreach($res as $bar){
			$idtrans[$bar['nourut']]=$bar['nourut'];
		}
		if(count($res)==0){
			unlink($file);
		}
	}
}

$where="";
if(count($idtrans)>0){
	$where=" and nourut not in ('".implode("','",$idtrans)."')";
}
//$where.=" and (keterangan='' or keterangan='pertanggung')";

$str="select * from ".$dbname.".approval where status='0' and karyawanid in (select karyawanid from ".$dbname.".setup_notification_dt where kodejenis='APPROVAL' and telegram='1') ".$where." order by tanggal desc limit 1000"; #exit("error".$str);
$res=fetchdata($str);
if(count($res)>0){
	foreach($res as $val){
		$namafile=str_replace("/","",$val['notransaksi']);
		$namafile=str_replace("-","",$namafile);
		$namafile=$folder.$val['jenispersetujuan']."_".$namafile.".pdf";
		
		if($val['jenispersetujuan']=='BAPP'){
			$whereH= "nopengajuan='".$val['notransaksi']."'";
			$queryH= selectQuery($dbname,'log_baspk','*',$whereH);
			$resH  = fetchData($queryH);
			$optorg= makeOption($dbname,'log_spkht','notransaksi,kodeorg',"notransaksi='".$resH[0]['notransaksi']."'");
			
			$_POST=array(
				'par'        =>'owlApp',
				'proses'     =>'preview',
				'tipe'       =>'html',
				'sumber'     =>'approval',
				'notransaksi'=>$resH[0]['notransaksi'],
				'kodeorg'    =>$optorg[$resH[0]['notransaksi']],
				'tanggal'    =>$resH[0]['tanggal'],
				'termin'     =>$resH[0]['termin'],
				'urlefil'    =>$namafile,
				'nopengajuan'=>$val['notransaksi']
			);
			$_GET=$_POST;
			if (!file_exists($namafile)){
				include('log_slave_realisasispk_detail.php');
			}
		}
		if($val['jenispersetujuan']=='PO'){
			$_POST=array(
				'par'    =>'owlApp',
				'table'  =>'log_poht',
				'urlefil'=>$namafile,
				'column' =>$val['notransaksi']
			);
			$_GET=$_POST;
			if (!file_exists($namafile)){
				include('log_slave_print_detail_po.php');
			}
		}
		if($val['jenispersetujuan']=='GR'){
			$_POST=array(
				'par'        =>'owlApp',
				'urlefil'    =>$namafile,
				'notransaksi'=>$val['notransaksi']
			);
			$_GET=$_POST;
			if (!file_exists($namafile)){
				include('log_slave_print_bapb_pdf.php');
			}
		}
		
		if($val['jenispersetujuan']=='PJDSTF' or $val['jenispersetujuan']=='PJDNSTF'){
			$_POST=array(
				'par'        =>'owlApp',
				'method'     =>'previewdata',
				'jenis'      =>'pdf',
				'tampilan'   =>'PDF',
				'namafile'   =>$namafile,
				'notransaksi'=>$val['notransaksi']
			);
			$_GET=$_POST;
			if (!file_exists($namafile)){
				include('sdm_slave_pjdx.php');
			}
		}
		if($val['jenispersetujuan']=='SPK'){
			$_POST=array(
				'par'        =>'owlApp',
				'method'     =>'html',
				'tipe'       =>'html',
				'tampilan'   =>'PDF',
				'namafile'   =>$namafile,
				'notransaksi'=>$val['notransaksi']
			);
			$_GET=$_POST;
			if (!file_exists($namafile)){
				include('lgl_slave_pengajuanspk.php');
			}
		}
		
		if($val['jenispersetujuan']=='IJS'){
			$whereH= "notransaksi='".$val['notransaksi']."'";
			$queryH= selectQuery($dbname,'sdm_ijin','*',$whereH);
			$resH  = fetchData($queryH);
			$_POST=array(
				'par'     =>'owlApp',
				'proses'  =>'prevPdf',
				'tampilan'=>'PDF',
				'namafile'=>$namafile,
				'tglijin' =>tanggalnormal($resH[0]['tanggal']),
				'krywnId' =>$resH[0]['karyawanid']
			);
			$_GET=$_POST;
			if (!file_exists($namafile)){
				include('sdm_slave_laporan_ijin_meninggalkan_kantor.php');
			}
		}
		if($val['jenispersetujuan']=='PR'){
			$_POST=array(
				'par'     =>'owlApp',
				'method'  =>'pdf',
				'tampilan'=>'PDF',
				'namafile'=>$namafile,
				'urlefil' =>$namafile,
				'table'   =>'log_prapoht',
				'column'  =>$val['notransaksi']
			);
			$_GET=$_POST;
			if (!file_exists($namafile)){
				include('log_slave_print_log_pp.php');
			}
		}
		if($val['jenispersetujuan']=='PTBS'){
			$exptipe= explode("/",$val['notransaksi']);
			$tipe   = $exptipe[1];
			
			$_POST=array(
				'par'        =>'owlApp',
				'method'     =>'pdf',
				'tampilan'   =>'PDF',
				'namafile'   =>$namafile,
				'notransaksi'=>$val['notransaksi']
			);
			$_GET=$_POST;
			if (!file_exists($namafile)){
				if($tipe=='TBSKUD'){				
					include('kebun_tbskud_slave.php');
				}
				if($tipe=='TBSEXT'){				
					include('kebun_tbsexternal_slave.php');
				}
				if($tipe=='TBSAFI'){				
					include('kebun_tbsafiliasi_slave.php');
				}
			}
		}
		
		if($val['jenispersetujuan']=='KASBANK'){
			$whereH= "notransaksi='".$val['notransaksi']."'";
			$queryH= selectQuery($dbname,'keu_kasbankht','*',$whereH);
			$resH  = fetchData($queryH);
			$_POST=array(
				'par'          =>'owlApp',
				'proses'       =>'html',
				'tampilan'     =>'PDF',
				'namafile'     =>$namafile,
				'notransaksi'  =>$val['notransaksi'],
				'kodeorg'      =>$resH[0]['kodeorg'],
				'noakun'       =>$resH[0]['noakun'],
				'tipetransaksi'=>$resH[0]['tipetransaksi']
			);
			$_GET=$_POST;
			if (!file_exists($namafile)){			
				include('keu_slave_kasbank_print_detail.php');
			}
		}
		if($val['jenispersetujuan']=='DTK1' or $val['jenispersetujuan']=='DTK2' or $val['jenispersetujuan']=='DTK3'){
			$whereH= "nourut='".$val['notransaksi']."'";
			$queryH= selectQuery($dbname,'datakaryawan_hist','*',$whereH);
			$resH  = fetchData($queryH);
			$_POST=array(
				'par'          =>'owlApp',
				'method'       =>'history',
				'tampilan'     =>'PDF',
				'namafile'     =>$namafile,
				'nourut'       =>$val['notransaksi'],
				'karyawanid'   =>$resH[0]['karyawanid'],
				'namakaryawan' =>$resH[0]['namakaryawan']
			);
			$_GET=$_POST;
			if (!file_exists($namafile)){			
				include('sdm_slave_get_karyawan_preview.php');
			}
		}
	}
}

?>